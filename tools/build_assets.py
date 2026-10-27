"""Cut the poster into seamless web assets for both designs.

Inputs : tools/.cache/poster45@2x.png, tools/.cache/poster916@2x.png
         (EDSR 2x of quelle/ and exporte/, see upscale.py)
         quelle/ruhr-acro-convention-2027.jpg (share and event images)
         web/public/fonts/*.woff2 (text on the social image; fetched by build.sh)
Outputs: web/public/img/*  (docs/build.md)

All coordinates are 1x source pixels; px() scales them to the 2x cache.
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
S = 2.0  # scale of the upscaled cache
BG = np.array([0, 25, 19], np.float32)  # panel green, measured: #001913
BG_RGB = tuple(int(v) for v in BG)

# Design scene, header band: scene from just above the sun (top edge y~385),
# fading into panel green right under the acrobats; the "Essen" brush that
# starts at y=1043 becomes a soft shadow. Footer band: the bottom plants with
# their full sprays (from below the venue line, y=1278), rising out of panel
# green over a long fade; centre medallions, labels, brushes and lotus removed.
HEAD = (0, 376, 1403, 1095)
HEAD_FADE = (1035, 1095)
HEAD_BRUSH = (380, 1036, 1030, 1162)
HEAD_KEEP = 1028  # nothing above this row is darkened (the acrobats)
FOOT = (0, 1260, 1403, 1753)
FOOT_FADE = (1260, 1460)
FOOT_ZONE = (250, 1245, 1150, 1747)
# Side bleed per side relative to the core. Both band cores share one CSS
# column: min(100% - 2 x min(200px, 14%), --head-max x core ratio); 0.6 x core
# per side covers viewports up to 2.2 x the column width.
BLEED = 0.6
BAND_WIDTHS = (6173, 4000, 2600, 1600)  # = BAND_WIDTHS in web/lib/render.php
LOTUS = (644, 1665, 759, 1731)


# ---------------------------------------------------------------- helpers
def px(box):
    """1x source coordinates -> integer pixels of the 2x cache."""
    return [int(v * S) for v in box]


def crop(img, box):
    x0, y0, x1, y1 = px(box)
    return img[y0:y1, x0:x1]


def u8(a):
    return np.clip(a, 0, 255).astype(np.uint8)


def to_bg(img, m):
    """Blend towards panel green by weight m (0 = image, 1 = green)."""
    a = img if img.dtype.kind == "f" else img.astype(np.float32)
    return a * (1 - m) + BG * m


def smooth(t):
    t = np.clip(t, 0, 1)
    return t * t * (3 - 2 * t)


def lum(a):
    a = a.astype(np.float32)
    return 0.299 * a[..., 0] + 0.587 * a[..., 1] + 0.114 * a[..., 2]


def ellipse(k):
    k = int(k) | 1
    return cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (k, k))


def off_border(stats, shape):
    """Per connected-component label: True if it does not touch the border.
    Label 0 (background) is always False."""
    h, w = shape
    bx, by, bw, bh = stats[:, 0], stats[:, 1], stats[:, 2], stats[:, 3]
    inner = (bx > 0) & (by > 0) & (bx + bw < w) & (by + bh < h)
    inner[0] = False
    return inner


# ---------------------------------------------------------------- masks
def content_components(img, zone, tol, min_area):
    """Foreground components inside zone (2x px) that don't touch its border."""
    x0, y0, x1, y1 = zone
    sub = img[y0:y1, x0:x1].astype(np.float32)
    fg = (np.abs(sub - BG).sum(-1) > tol).astype(np.uint8)
    _, lab, st, _ = cv2.connectedComponentsWithStats(fg, 8)
    keep = off_border(st, fg.shape) & (st[:, 4] >= min_area)
    mask = np.zeros(img.shape[:2], np.uint8)
    mask[y0:y1, x0:x1] = keep[lab]
    return mask


def stage_mask(img, zone, pad=20, feather=10, tol=40):
    """Soft mask over all lettering/ornaments inside zone (1x px)."""
    m = content_components(img, px(zone), tol, int(20 * S * S))
    m = cv2.dilate(m, ellipse(2 * pad * S + 1))
    m = cv2.morphologyEx(m, cv2.MORPH_CLOSE, ellipse(61 * S))
    return np.clip(cv2.GaussianBlur(m.astype(np.float32), (0, 0), feather * S), 0, 1)


def rounded_rect(shape, box, r, feather):
    x0, y0, x1, y1 = box
    m = np.zeros(shape, np.uint8)
    cv2.rectangle(m, (x0 + r, y0), (x1 - r, y1), 1, -1)
    cv2.rectangle(m, (x0, y0 + r), (x1, y1 - r), 1, -1)
    for cx, cy in ((x0 + r, y0 + r), (x1 - r, y0 + r), (x0 + r, y1 - r), (x1 - r, y1 - r)):
        cv2.circle(m, (cx, cy), r, 1, -1)
    return np.clip(cv2.GaussianBlur(m.astype(np.float32), (0, 0), feather), 0, 1)


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


def lettering_mask(img, box, ref_box, tol=40):
    """Dark lettering enclosed by a painted stroke of the reference colour."""
    x0, y0, x1, y1 = px(box)
    c = img[y0:y1, x0:x1]
    ref_rgb = np.median(crop(img, ref_box).reshape(-1, 3), 0)
    ref = cv2.cvtColor(ref_rgb.reshape(1, 1, 3).astype(np.uint8), cv2.COLOR_RGB2LAB).reshape(3)
    d = np.linalg.norm(cv2.cvtColor(c, cv2.COLOR_RGB2LAB).astype(np.float32) - ref, axis=-1)
    paint = d < tol
    enclosed = (reach(paint, 0, False, 50 * S) & reach(paint, 0, True, 50 * S)
                & reach(paint, 1, False, 260 * S) & reach(paint, 1, True, 260 * S))
    text = (~paint & enclosed).astype(np.uint8)
    _, lab, st, _ = cv2.connectedComponentsWithStats(text, 8)
    text[~off_border(st, text.shape)[lab]] = 0
    k = int(7 * S) | 1
    full = np.zeros(img.shape[:2], np.uint8)
    full[y0:y1, x0:x1] = cv2.dilate(text, np.ones((k, k), np.uint8))
    return full


# ---------------------------------------------------------------- design classic
def poster_art(img, brushes, venue, stage_top, zone, stage, fade, stage_feather=18):
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
        out = cv2.inpaint(out, lettering_mask(out, box, ref) * 255, int(7 * S), cv2.INPAINT_TELEA)
    x0, y0, x1, y1 = px(venue)
    sub = out[y0:y1, x0:x1]
    vm = np.zeros(out.shape[:2], np.uint8)
    vm[y0:y1, x0:x1] = (lum(sub) > 70) & ((sub.max(-1).astype(int) - sub.min(-1)) < 90)
    k = int(5 * S) | 1
    out[cv2.dilate(vm, np.ones((k, k), np.uint8)) > 0] = BG.astype(np.uint8)
    m = stage_mask(out, zone, pad=16, tol=22)
    sx0, sy0, sx1, sy1, sr = px(stage)
    m = np.maximum(m, rounded_rect(m.shape, (sx0, sy0, sx1, sy1), sr, stage_feather * S))
    y = np.arange(img.shape[0])[:, None] / S
    m = m * smooth((y - stage_top) / 4)  # never touch the brushes above
    f = to_bg(out, m[..., None])
    t = smooth((y - fade[0]) / (fade[1] - fade[0]))[..., None]
    return u8(to_bg(f, t))


# ---------------------------------------------------------------- design scene
def vfade(img, box, fade, top, lift=0.0):
    """Fade a cropped band into panel green towards its bottom (or top) edge.
    `lift` (per column, 1x px) starts the fade earlier, e.g. in the bleed."""
    y = np.arange(int(box[1] * S), int(box[3] * S))[:, None] / S
    start = fade[0] - lift if not top else fade[0]
    end = fade[1] if not top else fade[1] + lift
    t = smooth((y - start) / (end - start))
    return to_bg(img, (1 - t if top else t)[..., None])


def bleed_distance(w, r, ramp=0.2):
    """Per column of a bled band: 0 inside the core .. 1 at ramp*w outside."""
    e = round(r * w)
    x = np.arange(w + 2 * e, dtype=np.float32)
    return (np.maximum(e - x, x - (e + w - 1)).clip(0) / (ramp * w)).clip(0, 1)[None, :]


def bleed(core, r=BLEED, mirror=0.2, inner=0.06, ramp=0.2, sigma_max=240):
    """Extend a band left/right by r*width. Up to `mirror`*width the edge
    content is mirrored, beyond that its outermost column runs on (no mirrored
    acrobats in wide bleeds). Blur starts `inner`*width inside the core and
    reaches 120 px (2x) at `ramp`*width outside, then keeps growing: no visible
    line between sharp and blurred; the centre stays untouched."""
    h, w = core.shape[:2]
    e, m, n = round(r * w), round(min(mirror, r) * w), round(inner * w)
    ext = cv2.copyMakeBorder(core.astype(np.float32), 0, 0, m, m, cv2.BORDER_REFLECT)
    ext = cv2.copyMakeBorder(ext, 0, 0, e - m, e - m, cv2.BORDER_REPLICATE)
    sig = np.array([0, 6, 16, 36, 70, 120, 180, sigma_max], np.float32)
    layers = [ext] + [cv2.GaussianBlur(ext, (0, 0), float(v), borderType=cv2.BORDER_REPLICATE) for v in sig[1:]]
    x = np.arange(w + 2 * e, dtype=np.float32)
    d = np.maximum(e + n - x, x - (e + w - 1 - n)).clip(0) / (ramp * w + n)
    want = np.minimum(d ** 1.5 * 120, sigma_max)
    i = (np.searchsorted(sig, want, side="right") - 1).clip(0, len(sig) - 2)
    t = ((want - sig[i]) / (sig[i + 1] - sig[i])).clip(0, 1)[None, :, None]
    out = np.zeros_like(ext)
    for k in range(len(sig) - 1):
        sel = i == k
        out[:, sel] = layers[k][:, sel] * (1 - t[:, sel]) + layers[k + 1][:, sel] * t[:, sel]
    return u8(out)


def head_band(img):
    """Scene without lettering; the brush becomes shadow, bottom turns panel green.
    The fade runs after the side bleed so the bottom edge is panel green across
    the full width."""
    x0, y0, x1, y1 = px(HEAD_BRUSH)
    sub = img[y0:y1, x0:x1].astype(np.float32)
    warm = ((lum(sub) > 140) & (sub[..., 0] > sub[..., 2] + 40)).astype(np.uint8)
    warm = cv2.morphologyEx(warm, cv2.MORPH_CLOSE, ellipse(5 * S))
    _, lab, st, _ = cv2.connectedComponentsWithStats(warm, 8)
    big = st[:, 4] > 5000 * S * S
    big[0] = False
    brush = big[lab].astype(np.uint8)
    # Fill enclosed holes (lettering, pin icon).
    _, lab, st, _ = cv2.connectedComponentsWithStats(1 - brush, 4)
    brush[off_border(st, brush.shape)[lab]] = 1
    m = np.zeros(img.shape[:2], np.float32)
    m[y0:y1, x0:x1] = brush
    m = cv2.GaussianBlur(cv2.dilate(m, ellipse(10 * S)), (0, 0), 14 * S)
    y = np.arange(img.shape[0])[:, None] / S
    m = (np.clip(m * 1.6, 0, 1) * smooth((y - HEAD_KEEP) / 14))[..., None]
    core = u8(crop(to_bg(img, m), HEAD))
    # The blurred bleed carries sky and water light down to the fade; start
    # fading it up to 120 rows earlier so the sides darken as softly as the core.
    lift = 120 * smooth(bleed_distance(core.shape[1], BLEED))
    return u8(vfade(bleed(core), HEAD, HEAD_FADE, top=False, lift=lift))


def foot_band(img):
    """Bottom plants; centre lettering and lotus become panel green, top fades in."""
    m = stage_mask(img, FOOT_ZONE, pad=12, feather=8, tol=22)[..., None]
    core = u8(crop(to_bg(img, m), FOOT))
    return u8(vfade(bleed(core), FOOT, FOOT_FADE, top=True))


# ---------------------------------------------------------------- shared
def medallion(img, box, rim=6):
    """Cut a round icon medallion.

    The labels below each medallion cut into its lower rim. The lower 140° of
    the rim band is replaced by the vertically mirrored upper rim (feathered in
    angle and radius), so the dashed outline stays continuous."""
    c = crop(img, box).astype(np.float32)
    lc = lum(c)
    k = int(9 * S) | 1
    fill = cv2.morphologyEx((lc > 140).astype(np.uint8), cv2.MORPH_CLOSE, np.ones((k, k), np.uint8))
    _, lab, st, _ = cv2.connectedComponentsWithStats(fill, 8)
    big = (lab == 1 + np.argmax(st[1:, 4])).astype(np.uint8)
    cnts, _ = cv2.findContours(big, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_NONE)
    (cx, cy), (ew, eh), _ = cv2.fitEllipse(max(cnts, key=cv2.contourArea))
    rf = (ew + eh) / 4
    r = rf + rim * S
    yy, xx = np.mgrid[0:c.shape[0], 0:c.shape[1]]
    dist = np.hypot(xx - cx, yy - cy)
    ang = np.degrees(np.arctan2(yy - cy, xx - cx))  # +90 = straight down
    w = (smooth((70 - np.abs(ang - 90)) / 12) * smooth((dist - (rf - 9 * S)) / (2 * S)))[..., None]
    my = np.clip(np.round(2 * cy - yy).astype(int), 0, c.shape[0] - 1)
    c = c * (1 - w) + c[my, xx] * w
    a = np.clip((r - dist) / (1.2 * S) + 0.5, 0, 1)
    b = int(np.ceil(r + 2))
    cxi, cyi = int(round(cx)), int(round(cy))
    out = np.dstack([c, a * 255])[max(cyi - b, 0):cyi + b, max(cxi - b, 0):cxi + b]
    return out.astype(np.uint8)


def on_bg(img, box, tol=30, soft=26):
    """Key a line ornament off the flat panel green (un-premultiplied)."""
    c = crop(img, box).astype(np.float32)
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


def fit_height(poster, h):
    p = Image.fromarray(poster)
    return p.resize((round(p.width * h / p.height), h), Image.LANCZOS)


def social_image(poster):
    """1200x630 share image: the complete original poster plus the key facts."""
    W, H = 1200, 630
    card = Image.new("RGB", (W, H), BG_RGB)
    p = fit_height(poster, H)
    card.paste(p, (56, 0))
    d = ImageDraw.Draw(card)
    x = 56 + p.width + 64
    gold, sky, dim = (253, 198, 113), (253, 248, 236), (201, 211, 198)
    d.text((x, 150), "5.–7. März 2027", font=font("kalam-700-latin.woff2", 64), fill=gold)
    d.text((x, 236), "Essen", font=font("kalam-700-latin.woff2", 64), fill=gold)
    d.text((x, 340), "Acroyoga & Akrobatik Festival", font=font("nunito-latin.woff2", 30, 700), fill=sky)
    d.text((x, 384), "Workshops · Jams · alle Level", font=font("nunito-latin.woff2", 30, 400), fill=dim)
    d.text((x, 470), "Move together. Grow together", font=font("kalam-700-latin.woff2", 34), fill=gold)
    return card


def letterbox(poster, W, H):
    """Whole poster centred on panel green at W x H (no crop, nothing hidden)."""
    card = Image.new("RGB", (W, H), BG_RGB)
    p = fit_height(poster, H)
    card.paste(p, ((W - p.width) // 2, 0))
    return card


def app_icon(lotus, size):
    """The poster's lotus centred on panel green."""
    icon = Image.new("RGBA", (size, size), BG_RGB + (255,))
    mark = lotus.copy()
    mark.thumbnail((int(size * 0.8), int(size * 0.8)), Image.LANCZOS)
    icon.alpha_composite(mark, ((size - mark.width) // 2, (size - mark.height) // 2))
    return icon.convert("RGB")


def save(img, name, width=None, quality=86):
    im = Image.fromarray(img) if isinstance(img, np.ndarray) else img
    if width and width < im.width:
        im = im.resize((width, round(im.height * width / im.width)), Image.LANCZOS)
    path = OUT / name
    if path.suffix == ".jpg":
        im.convert("RGB").save(path, "JPEG", quality=quality, optimize=True, progressive=True)
    elif path.suffix == ".png":
        im.save(path, optimize=True)
    elif path.suffix == ".avif":
        # 4:4:4 keeps the thin gold rim lights on the dark figures crisp.
        im.save(path, "AVIF", quality=quality, subsampling="4:4:4", speed=4)
    else:
        im.save(path, "WEBP", quality=quality, method=6)
    return f"{name:24s} {im.width:5d}x{im.height:<5d} {path.stat().st_size // 1024:5d} KB"


# ---------------------------------------------------------------- build
def main():
    OUT.mkdir(exist_ok=True)
    for old in OUT.glob("*"):
        old.unlink()
    p45 = np.array(Image.open(CACHE / "poster45@2x.png").convert("RGB"))
    p916 = np.array(Image.open(CACHE / "poster916@2x.png").convert("RGB"))
    report = []

    # Design classic, art direction: 4:5 for landscape and tablets (sides fade
    # via CSS mask), 9:16 for phones.
    art45 = poster_art(
        p45,
        brushes=[((440, 1040, 975, 1160), (850, 1060, 900, 1080)),
                 ((430, 1156, 980, 1240), (450, 1190, 470, 1205))],
        venue=(470, 1240, 935, 1278), stage_top=1240,
        zone=(290, 1238, 1115, 1745), stage=(300, 1240, 1105, 1700, 110), fade=(1600, 1753),
        stage_feather=22)
    art916 = poster_art(
        p916,
        brushes=[((170, 936, 730, 1033), (600, 955, 630, 970)),
                 ((170, 1033, 730, 1100), (250, 1060, 270, 1072))],
        venue=(195, 1102, 705, 1135), stage_top=1100,
        zone=(50, 1098, 850, 1595), stage=(118, 1100, 782, 1545, 70), fade=(1450, 1600))
    report += [save(art45, f"art-4x5-{w}.webp", w) for w in (2806, 2000, 1400)]
    report += [save(art916, f"art-9x16-{w}.webp", w) for w in (1800, 1200, 800)]

    # Design scene: header and footer bands with baked side bleed; widths are
    # the full band incl. bleed.
    for name, band in (("head", head_band(p45)), ("foot", foot_band(p45))):
        for w in BAND_WIDTHS:
            report.append(save(band, f"{name}-{w}.avif", w, quality=75))
            report.append(save(band, f"{name}-{w}.webp", w, quality=90))

    # Both designs: icon medallions and lotus from the 4:5 poster.
    for name, box in {"workshops": (312, 1272, 492, 1446),
                      "community": (604, 1272, 792, 1446),
                      "jams": (900, 1272, 1080, 1446)}.items():
        report.append(save(medallion(p45, box), f"medal-{name}.webp", 240))
    lotus = on_bg(p45, LOTUS)
    report.append(save(lotus, "lotus.webp", 160))

    # Share/structured-data images use the untouched original (all text baked
    # in). JPEG, because some social crawlers still ignore WebP.
    original = np.array(Image.open(ROOT / "quelle/ruhr-acro-convention-2027.jpg").convert("RGB"))
    report.append(save(social_image(original), "social-1200x630.jpg", quality=88))
    # Event rich results: Google asks for 1:1, 4:3 and 16:9, >= 720 px wide.
    for name, (w, h) in {"1x1": (1200, 1200), "4x3": (1600, 1200), "16x9": (1920, 1080)}.items():
        report.append(save(letterbox(original, w, h), f"event-{name}.jpg", quality=85))

    for name, size in (("favicon.png", 64), ("touch-icon.png", 180)):
        report.append(save(app_icon(Image.fromarray(lotus), size), name))

    print("\n".join(report))


if __name__ == "__main__":
    main()
