// Browser checks for all pages in Chromium, Firefox and WebKit (Safari engine).
//   cd web/tests/browser && npm ci && npx playwright install chromium firefox webkit
//   BASE_URL=http://127.0.0.1:8932 node check.mjs        (exit 1 on any failure)
//   BASE_URL=http://127.0.0.1:8934/acro node check.mjs   (sub-folder bundle)
// Details and last results: docs/qualitaet.md
import fs from 'node:fs';
import { chromium, firefox, webkit, devices } from 'playwright';

const BASE = (process.env.BASE_URL ?? 'http://127.0.0.1:8932').replace(/\/$/, '');
const HOST = new URL(BASE).host;
const PREFIX = new URL(BASE).pathname.replace(/\/$/, ''); // '' or '/acro'
const PAGES = ['/', '/programm', '/tickets', '/anreise', '/impressum', '/datenschutz',
  '/en', '/en/programme', '/en/tickets', '/en/directions', '/en/legal-notice', '/en/privacy'];
let failures = 0;
const fail = msg => { failures++; console.log('FAIL ', msg); };

async function measure(page) {
  return page.evaluate(() => {
    const r = s => document.querySelector(s)?.getBoundingClientRect();
    const small = [...document.querySelectorAll('a, button, summary')]
      .filter(el => el.offsetParent !== null && !el.closest('p, address, li:not(.nav li)'))
      .map(el => [el, el.getBoundingClientRect()])
      .filter(([, b]) => b.width > 0 && (b.height < 44 || b.width < 44))
      .map(([el, b]) => `${el.textContent.trim().slice(0, 20)} ${Math.round(b.width)}x${Math.round(b.height)}`);
    const band = s => document.querySelector(`.band--${s} .band__core`);
    const nav = r('.nav');
    return {
      overflow: document.documentElement.scrollWidth - innerWidth,
      fonts: [...document.fonts].filter(f => f.status === 'loaded').map(f => f.family.replace(/"/g, '')).sort().join('+'),
      sticky: getComputedStyle(document.querySelector('.nav')).position,
      broken: [...document.images].filter(i => i.complete && !i.naturalWidth).length,
      // brand, flags, button and links: one row on wide screens, two on narrow
      navRows: new Set([...document.querySelectorAll('.nav__brand, .lang, .btn--nav, .nav__links')]
        .map(el => Math.round(el.getBoundingClientRect().top / 20))).size,
      small,
      // header band right below the navigation, at most half the viewport
      // high, centred; footer band ends the page
      head: band('head') && {
        gap: Math.round(band('head').getBoundingClientRect().top + scrollY - (nav.bottom + scrollY)),
        height: band('head').getBoundingClientRect().height,
        offCentre: Math.abs(band('head').getBoundingClientRect().left + band('head').getBoundingClientRect().width / 2 - document.documentElement.clientWidth / 2),
      },
      footEnd: band('foot') && Math.round(document.documentElement.scrollHeight - (band('foot').getBoundingClientRect().bottom + scrollY)),
    };
  });
}

// 1. engines x viewports x pages
const engines = { chromium, firefox, webkit };
const viewports = {
  'desktop 1440x900': { width: 1440, height: 900 },
  'iPhone 13': devices['iPhone 13'].viewport,
  'phone 320x640': { width: 320, height: 640 },
  'landscape 844x390': { width: 844, height: 390 },
};
for (const [name, engine] of Object.entries(engines)) {
  const browser = await engine.launch();
  for (const [vp, viewport] of Object.entries(viewports)) {
    const phone = viewport.width < 500;
    const ctx = await browser.newContext({ viewport, isMobile: phone && name !== 'firefox', hasTouch: phone });
    const page = await ctx.newPage();
    const errors = [];
    const external = new Set();
    page.on('pageerror', e => errors.push(e.message));
    page.on('console', m => m.type() === 'error' && errors.push(m.text()));
    page.on('request', r => { const u = new URL(r.url()); if (u.host !== HOST) external.add(u.host); });
    for (const p of PAGES) {
      await page.goto(BASE + p, { waitUntil: 'networkidle' });
      const m = await measure(page);
      const where = `${name} ${vp} ${p}`;
      if (m.overflow > 0) fail(`${where}: horizontal overflow ${m.overflow}px`);
      if (m.fonts !== 'Kalam+Nunito') fail(`${where}: fonts loaded = ${m.fonts}`);
      if (m.sticky !== 'sticky') fail(`${where}: nav not sticky`);
      if (m.broken) fail(`${where}: ${m.broken} broken images`);
      if (m.small.length) fail(`${where}: tap targets < 44px: ${m.small.join(', ')}`);
      if (!m.head || m.head.gap !== 0) fail(`${where}: header band not directly below the navigation (${m.head?.gap})`);
      else if (m.head.height > viewport.height / 2 + 1) fail(`${where}: header band ${m.head.height}px > half the viewport`);
      else if (m.head.offCentre > 1) fail(`${where}: header band off centre by ${m.head.offCentre}px`);
      if (Math.abs(m.footEnd) > 1) fail(`${where}: footer band does not end the page (${m.footEnd}px)`); // 1px: subpixel rounding of scrollHeight
      if (m.navRows > 2) fail(`${where}: navigation wraps into ${m.navRows} rows`);
    }
    // the flag toggle switches language and keeps the page
    await page.goto(BASE + '/programm', { waitUntil: 'networkidle' });
    await page.click('.lang a[hreflang="en"]');
    await page.waitForLoadState('networkidle');
    if (new URL(page.url()).pathname !== PREFIX + '/en/programme' || await page.getAttribute('html', 'lang') !== 'en') fail(`${name} ${vp}: toggle DE -> EN`);
    await page.click('.lang a[hreflang="de"]');
    await page.waitForLoadState('networkidle');
    if (new URL(page.url()).pathname !== PREFIX + '/programm' || await page.getAttribute('html', 'lang') !== 'de') fail(`${name} ${vp}: toggle EN -> DE`);
    if (errors.length) fail(`${name} ${vp}: JS/console errors: ${errors.join(' | ')}`);
    if (external.size) fail(`${name} ${vp}: third-party requests: ${[...external].join(', ')}`);
    console.log(`done ${name.padEnd(8)} ${vp}`);
    await ctx.close();
  }
  await browser.close();
}

// 2. old engines without container queries (Safari 15, Chrome < 105):
// serve the stylesheet as such an engine parses it
const css = fs.readFileSync(new URL('../../public/assets/site.css', import.meta.url), 'utf8')
  .replace(/@supports not \(container-type: inline-size\) \{/g, '@supports (display: block) {')
  .replace(/@container[^{]+\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}/g, '')
  .replace(/container-type: inline-size;|container: content \/ inline-size;/g, '');
const browser = await chromium.launch();
for (const viewport of [{ width: 390, height: 844 }, { width: 820, height: 1180 }, { width: 1440, height: 900 }]) {
  const page = await browser.newPage({ viewport });
  await page.route('**/assets/site.css*', r => r.fulfill({ contentType: 'text/css', body: css }));
  await page.goto(BASE + '/', { waitUntil: 'networkidle' });
  const m = await measure(page);
  if (m.overflow > 0) fail(`fallback ${viewport.width}: overflow ${m.overflow}px`);
  console.log(`done fallback ${viewport.width}px`);
  await page.close();
}
await browser.close();
console.log(failures ? `${failures} failed` : 'all browser checks passed');
process.exit(failures ? 1 : 0);
