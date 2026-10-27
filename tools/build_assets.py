"""Cut the poster into seamless web assets.

Inputs : tools/.cache/poster45@2x.png, tools/.cache/poster916@2x.png
         (EDSR 2x of quelle/ and exporte/, see upscale.py)
         web/public/fonts/*.woff2 (text on the social image; fetched by build.sh)
Outputs: web/public/img/*

All coordinates below are in 1x source pixels and scaled internally.
"""
import io
from pathlib import Path

import cv2
import numpy as np
from fontTools.ttLib import TTFont
from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parent.parent
CACHE = ROOT / "tools/.cache"
OUT = ROOT / "web/public/img"
FONTS = ROOT / "web/public/fonts"
BG = np.array([0, 25, 19], np.float32)  # panel green, measured: #001913


# ---------------------------------------------------------------- helpers
def smooth(t):
    t = np.clip(t, 0, 1)
    return t * t * (3 - 2 * t)


def lum(a):
    a = a.astype(np.float32)
    return 0.299 * a[..., 0] + 0.587 * a[..., 1] + 0.114 * a[..., 2]


def content_components(img, zone, tol, min_area):
    """Label foreground components inside zone that don't touch its border."""
    x0, y0, x1, y1 = zone
    sub = img[y0:y1, x0:x1].astype(np.float32)
    fg = (np.abs(sub - BG).sum(-1) > tol).astype(np.uint8)
    n, lab, st, _ = cv2.connectedComponentsWithStats(fg, 8)
    h, w = fg.shape
    keep = np.zeros(n, bool)
    for i in range(1, n):
        bx, by, bw, bh, area = st[i]
        inner = bx > 0 and by > 0 and bx + bw < w and by + bh < h
        keep[i] = inner and area >= min_area
    mask = np.zeros(img.shape[:2], np.uint8)
    mask[y0:y1, x0:x1] = keep[lab]
    return mask


def stage_mask(img, zone, s, pad=20, feather=10, tol=40):
    m = content_components(img, zone, tol, int(20 * s * s))
    k = int(2 * pad * s + 1)
    m = cv2.dilate(m, cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (k, k)))
    k = int(61 * s) | 1
    m = cv2.morphologyEx(m, cv2.MORPH_CLOSE, cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (k, k)))
    return np.clip(cv2.GaussianBlur(m.astype(np.float32), (0, 0), feather * s), 0, 1)


def rounded_rect(shape, box, r, feather):
    x0, y0, x1, y1 = box
    m = np.zeros(shape, np.uint8)
    cv2.rectangle(m, (x0 + r, y0), (x1 - r, y1), 1, -1)
    cv2.rectangle(m, (x0, y0 + r), (x1, y1 - r), 1, -1)
    for cx, cy in ((x0 + r, y0 + r), (x1 - r, y0 + r), (x0 + r, y1 - r), (x1 - r, y1 - r)):
        cv2.circle(m, (cx, cy), r, 1, -1)
    return np.clip(cv2.GaussianBlur(m.astype(np.float32), (0, 0), feather), 0, 1)


def poster_art(img, s, brushes, venue, stage_top, zone, stage, fade, stage_feather=18):
    """Turn the poster into a backdrop for live HTML text.

    - lettering inside the place/date brushes is inpainted; the empty strokes
      stay painted on the grass, HTML text is set on top of them
    - the venue line is erased
    - everything below `stage_top` inside `zone`/`stage` becomes flat panel
      green so HTML content can flow there; the leaf frame stays untouched
    - the bottom edge fades into panel green so the page continues seamlessly
    """
    out = img.copy()
    for box, ref in brushes:
        out = cv2.inpaint(out, lettering_mask(out, s, box, ref) * 255, int(7 * s), cv2.INPAINT_TELEA)
    x0, y0, x1, y1 = [int(v * s) for v in venue]
    sub = out[y0:y1, x0:x1]
    vm = np.zeros(out.shape[:2], np.uint8)
    vm[y0:y1, x0:x1] = (lum(sub) > 70) & ((sub.max(-1).astype(int) - sub.min(-1)) < 90)
    k = int(5 * s) | 1
    out[cv2.dilate(vm, np.ones((k, k), np.uint8)) > 0] = BG.astype(np.uint8)
    m = stage_mask(out, [int(v * s) for v in zone], s, pad=16, tol=22)
    sx0, sy0, sx1, sy1, sr = [int(v * s) for v in stage]
    m = np.maximum(m, rounded_rect(m.shape, (sx0, sy0, sx1, sy1), sr, stage_feather * s))
    y = np.arange(img.shape[0])[:, None] / s
    m = m * smooth((y - stage_top) / 4)  # never touch the brushes above
    f = out.astype(np.float32) * (1 - m[..., None]) + BG * m[..., None]
    t = smooth((y - fade[0]) / (fade[1] - fade[0]))[..., None]
    return np.clip(f * (1 - t) + BG * t, 0, 255).astype(np.uint8)


def lettering_mask(img, s, box, ref_box, tol=40):
    """Dark lettering enclosed by a painted stroke of the reference colour."""
    x0, y0, x1, y1 = [int(v * s) for v in box]
    c = img[y0:y1, x0:x1]
    rx0, ry0, rx1, ry1 = [int(v * s) for v in ref_box]
    ref_rgb = np.median(img[ry0:ry1, rx0:rx1].reshape(-1, 3), 0)
    ref = cv2.cvtColor(ref_rgb.reshape(1, 1, 3).astype(np.uint8), cv2.COLOR_RGB2LAB).reshape(3)
    d = np.linalg.norm(cv2.cvtColor(c, cv2.COLOR_RGB2LAB).astype(np.float32) - ref, axis=-1)
    paint = d < tol
    enclosed = (reach(paint, 0, False, 50 * s) & reach(paint, 0, True, 50 * s)
                & reach(paint, 1, False, 260 * s) & reach(paint, 1, True, 260 * s))
    text = (~paint & enclosed).astype(np.uint8)
    n, lab, st, _ = cv2.connectedComponentsWithStats(text, 8)
    h, w = text.shape
    for i in range(1, n):
        bx, by, bw, bh, _ = st[i]
        if bx == 0 or by == 0 or bx + bw == w or by + bh == h:
            text[lab == i] = 0
    k = int(7 * s) | 1
    full = np.zeros(img.shape[:2], np.uint8)
    full[y0:y1, x0:x1] = cv2.dilate(text, np.ones((k, k), np.uint8))
    return full


def reach(mask, axis, reverse, dist):
    """True where a mask pixel lies within `dist` px along axis/direction."""
    m = np.flip(mask, axis) if reverse else mask
    n = m.shape[axis]
    shape = [1, 1]
    shape[axis] = n
    idx = np.arange(n, dtype=np.float32).reshape(shape)
    last = np.maximum.accumulate(np.where(m, idx, -1e9), axis=axis)
    res = (idx - last) <= dist
    return np.flip(res, axis) if reverse else res


def medallion(img, s, box, rim=6):
    """Cut a round icon medallion.

    The labels below each medallion cut into its lower rim. The lower 140° of
    the rim band is replaced by the vertically mirrored upper rim (feathered in
    angle and radius), so the dashed outline stays continuous."""
    x0, y0, x1, y1 = [int(v * s) for v in box]
    c = img[y0:y1, x0:x1].astype(np.float32)
    lc = lum(c)
    k = int(9 * s) | 1
    fill = cv2.morphologyEx((lc > 140).astype(np.uint8), cv2.MORPH_CLOSE, np.ones((k, k), np.uint8))
    n, lab, st, _ = cv2.connectedComponentsWithStats(fill, 8)
    big = (lab == 1 + np.argmax(st[1:, 4])).astype(np.uint8)
    cnts, _ = cv2.findContours(big, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_NONE)
    (cx, cy), (ew, eh), _ = cv2.fitEllipse(max(cnts, key=cv2.contourArea))
    rf = (ew + eh) / 4
    r = rf + rim * s
    yy, xx = np.mgrid[0:c.shape[0], 0:c.shape[1]]
    dist = np.hypot(xx - cx, yy - cy)
    ang = np.degrees(np.arctan2(yy - cy, xx - cx))  # +90 = straight down
    w = (smooth((70 - np.abs(ang - 90)) / 12) * smooth((dist - (rf - 9 * s)) / (2 * s)))[..., None]
    my = np.clip(np.round(2 * cy - yy).astype(int), 0, c.shape[0] - 1)
    c = c * (1 - w) + c[my, xx] * w
    a = np.clip((r - dist) / (1.2 * s) + 0.5, 0, 1)
    b = int(np.ceil(r + 2))
    cxi, cyi = int(round(cx)), int(round(cy))
    out = np.dstack([c, a * 255])[max(cyi - b, 0):cyi + b, max(cxi - b, 0):cxi + b]
    return out.astype(np.uint8)


def on_bg(img, s, box, tol=30, soft=26):
    """Key a line ornament off the flat panel green (un-premultiplied)."""
    x0, y0, x1, y1 = [int(v * s) for v in box]
    c = img[y0:y1, x0:x1].astype(np.float32)
    a = np.clip((np.abs(c - BG).sum(-1) - tol) / soft, 0, 1)
    f = np.clip((c - (1 - a[..., None]) * BG) / np.maximum(a[..., None], 1e-3), 0, 255)
    return np.dstack([f, a * 255]).astype(np.uint8)


def font(name, size, weight=None):
    """Load a woff2 webfont for Pillow (decompressed via fontTools)."""
    tt = TTFont(FONTS / name)
    tt.flavor = None
    buf = io.BytesIO()
    tt.save(buf)
    buf.seek(0)
    f = ImageFont.truetype(buf, size)
    if weight is not None:
        f.set_variation_by_axes([weight])
    return f


def social_image(poster):
    """1200x630 share image: the complete original poster plus the key facts."""
    W, H = 1200, 630
    card = Image.new("RGB", (W, H), tuple(int(v) for v in BG))
    p = Image.fromarray(poster)
    p = p.resize((round(p.width * H / p.height), H), Image.LANCZOS)
    card.paste(p, (56, 0))
    d = ImageDraw.Draw(card)
    x = 56 + p.width + 64
    gold, sky, dim = (253, 198, 113), (253, 248, 236), (201, 211, 198)
    d.text((x, 150), "5.–7. März 2027", font=font("kalam-700-latin.woff2", 64), fill=gold)
    d.text((x, 236), "Essen", font=font("kalam-700-latin.woff2", 64), fill=gold)
    body = font("nunito-latin.woff2", 30, 700)
    d.text((x, 340), "Acroyoga & Akrobatik Festival", font=body, fill=sky)
    d.text((x, 384), "Workshops · Jams · alle Level", font=font("nunito-latin.woff2", 30, 400), fill=dim)
    d.text((x, 470), "Move together. Grow together", font=font("kalam-700-latin.woff2", 34), fill=gold)
    return card


def letterbox(poster, W, H):
    """Whole poster centred on panel green at W x H (no crop, nothing hidden)."""
    card = Image.new("RGB", (W, H), tuple(int(v) for v in BG))
    p = Image.fromarray(poster)
    p = p.resize((round(p.width * H / p.height), H), Image.LANCZOS)
    card.paste(p, ((W - p.width) // 2, 0))
    return card


def save(arr, name, width=None, quality=86, lossless=False):
    im = Image.fromarray(arr) if isinstance(arr, np.ndarray) else arr
    if width and width < im.width:
        im = im.resize((width, round(im.height * width / im.width)), Image.LANCZOS)
    path = OUT / name
    if path.suffix == ".jpg":
        im.convert("RGB").save(path, "JPEG", quality=quality, optimize=True, progressive=True)
    else:
        im.save(path, "WEBP", quality=quality, method=6, lossless=lossless)
    return {"file": name, "w": im.width, "h": im.height, "kb": path.stat().st_size // 1024}


# ---------------------------------------------------------------- build
def main():
    OUT.mkdir(exist_ok=True)
    for old in OUT.glob("*"):
        old.unlink()
    p45 = np.array(Image.open(CACHE / "poster45@2x.png").convert("RGB"))
    p916 = np.array(Image.open(CACHE / "poster916@2x.png").convert("RGB"))
    s = 2.0
    report = []

    # Art direction: 4:5 for landscape and tablets (sides fade via CSS mask),
    # 9:16 for phones.
    art45 = poster_art(
        p45, s,
        brushes=[((440, 1040, 975, 1160), (850, 1060, 900, 1080)),
                 ((430, 1156, 980, 1240), (450, 1190, 470, 1205))],
        venue=(470, 1240, 935, 1278), stage_top=1240,
        zone=(290, 1238, 1115, 1745), stage=(300, 1240, 1105, 1700, 110), fade=(1600, 1753),
        stage_feather=22)
    art916 = poster_art(
        p916, s,
        brushes=[((170, 936, 730, 1033), (600, 955, 630, 970)),
                 ((170, 1033, 730, 1100), (250, 1060, 270, 1072))],
        venue=(195, 1102, 705, 1135), stage_top=1100,
        zone=(50, 1098, 850, 1595), stage=(118, 1100, 782, 1545, 70), fade=(1450, 1600))
    for w in (2806, 2000, 1400):
        report.append(save(art45, f"art-4x5-{w}.webp", w))
    for w in (1800, 1200, 800):
        report.append(save(art916, f"art-9x16-{w}.webp", w))

    # Icon medallions and lotus from the 4:5 poster.
    for name, box in {"workshops": (312, 1272, 492, 1446),
                      "community": (604, 1272, 792, 1446),
                      "jams": (900, 1272, 1080, 1446)}.items():
        report.append(save(medallion(p45, s, box), f"medal-{name}.webp", 240))
    report.append(save(on_bg(p45, s, (644, 1665, 759, 1731)), "lotus.webp", 160))

    # Share/structured-data images use the untouched original (all text baked
    # in). JPEG, because some social crawlers still ignore WebP.
    original = np.array(Image.open(ROOT / "quelle/ruhr-acro-convention-2027.jpg").convert("RGB"))
    report.append(save(social_image(original), "social-1200x630.jpg", quality=88))
    # Event rich results: Google asks for 1:1, 4:3 and 16:9, >= 720 px wide.
    for name, (w, h) in {"1x1": (1200, 1200), "4x3": (1600, 1200), "16x9": (1920, 1080)}.items():
        report.append(save(letterbox(original, w, h), f"event-{name}.jpg", quality=85))

    # Icons: the poster's lotus on panel green.
    lotus = Image.fromarray(on_bg(p45, s, (644, 1665, 759, 1731)))
    for name, size in (("favicon.png", 64), ("touch-icon.png", 180)):
        icon = Image.new("RGBA", (size, size), tuple(int(v) for v in BG) + (255,))
        mark = lotus.copy()
        mark.thumbnail((int(size * 0.8), int(size * 0.8)), Image.LANCZOS)
        icon.alpha_composite(mark, ((size - mark.width) // 2, (size - mark.height) // 2))
        icon.convert("RGB").save(OUT / name, optimize=True)
        report.append({"file": name, "w": size, "h": size, "kb": (OUT / name).stat().st_size // 1024})

    for r in report:
        print(f"{r['file']:24s} {r['w']:5d}x{r['h']:<5d} {r['kb']:5d} KB")


if __name__ == "__main__":
    main()
