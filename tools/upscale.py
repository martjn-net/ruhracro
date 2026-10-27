"""Upscale the poster exports 2x with EDSR (OpenCV dnn_superres).

Needs opencv-contrib (cv2.dnn_superres) and the EDSR_x2.pb model; run via
tools/build.sh (docs/build.md). Output goes to tools/.cache/ and is consumed
by build_assets.py.
"""
import sys
import time
from pathlib import Path

import cv2
import numpy as np

ROOT = Path(__file__).resolve().parent.parent
MODEL = Path(sys.argv[1]) if len(sys.argv) > 1 else ROOT / "tools/models/EDSR_x2.pb"
CACHE = ROOT / "tools/.cache"
SOURCES = {
    # 4:5 composition: the original (1403x1753) beats the 1122x1402 export.
    "poster45": ROOT / "quelle/ruhr-acro-convention-2027.jpg",
    "poster916": ROOT / "exporte/style1/WhatsApp Image 2026-10-27 at 12.48.16.jpeg",
}
TILE = 192
PAD = 12


def upscale(sr, img: np.ndarray) -> np.ndarray:
    """Tiled 2x upscale with overlap to keep memory flat and avoid seams."""
    h, w = img.shape[:2]
    out = np.zeros((h * 2, w * 2, 3), np.uint8)
    for y in range(0, h, TILE):
        for x in range(0, w, TILE):
            y0, x0 = max(y - PAD, 0), max(x - PAD, 0)
            y1, x1 = min(y + TILE + PAD, h), min(x + TILE + PAD, w)
            up = sr.upsample(img[y0:y1, x0:x1])
            ty, tx = (y - y0) * 2, (x - x0) * 2
            th, tw = min(TILE, h - y) * 2, min(TILE, w - x) * 2
            out[y * 2:y * 2 + th, x * 2:x * 2 + tw] = up[ty:ty + th, tx:tx + tw]
    return out


def main() -> None:
    CACHE.mkdir(parents=True, exist_ok=True)
    sr = cv2.dnn_superres.DnnSuperResImpl_create()
    sr.readModel(str(MODEL))
    sr.setModel("edsr", 2)
    for name, src in SOURCES.items():
        dst = CACHE / f"{name}@2x.png"
        if dst.exists():
            print(f"{name}: cached")
            continue
        t = time.time()
        img = cv2.imread(str(src), cv2.IMREAD_COLOR)
        cv2.imwrite(str(dst), upscale(sr, img))
        print(f"{name}: {img.shape[1]}x{img.shape[0]} -> 2x in {time.time() - t:.0f}s")


if __name__ == "__main__":
    main()
