"""One-shot: convert cowork-pipeline social-card fonts (.otf/.ttf) → .woff2.

Source : C:\\Users\\Georg\\Claude-cowork\\Cyber-Aspis\\cowork-pipeline\\fonts\\
Target : ./  (this directory: cyber-aspis-website/assets/fonts/social/)

Usage  : python convert.py
"""
from pathlib import Path
from fontTools.ttLib import TTFont

SRC = Path(r"C:\Users\Georg\Claude-cowork\Cyber-Aspis\cowork-pipeline\fonts")
DST = Path(__file__).parent

FILES = [
    "BebasNeue-Regular.otf",
    "Manrope-Regular.ttf",
    "Manrope-Medium.ttf",
    "Manrope-SemiBold.ttf",
    "Manrope-Bold.ttf",
    "JetBrainsMono-Regular.ttf",
    "JetBrainsMono-Medium.ttf",
    "JetBrainsMono-SemiBold.ttf",
]

for name in FILES:
    src = SRC / name
    if not src.exists():
        print(f"SKIP {name} (not found)")
        continue
    dst = DST / (src.stem.lower().replace("-", "-") + ".woff2")
    font = TTFont(str(src))
    font.flavor = "woff2"
    font.save(str(dst))
    src_kb = src.stat().st_size / 1024
    dst_kb = dst.stat().st_size / 1024
    print(f"OK   {name} ({src_kb:.1f}KB) -> {dst.name} ({dst_kb:.1f}KB)")
