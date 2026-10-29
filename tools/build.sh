#!/usr/bin/env bash
# Reproducible asset build: verified sources -> pinned venv -> verified model
# -> upscale -> pinned fonts -> assets. Details: docs/build.md
# Usage: tools/build.sh            (upscale is cached in tools/.cache/)
#        tools/build.sh --clean    (drop the cache and upscale again, ~30 min CPU)
set -euo pipefail
cd "$(dirname "$0")/.."

VENV=tools/.venv
MODEL=tools/models/EDSR_x2.pb
MODEL_URL=https://raw.githubusercontent.com/Saafke/EDSR_Tensorflow/master/models/EDSR_x2.pb
MODEL_SHA256=585623221baa070279a0d1e7e113a4c3faba0f318ca7fdd9a65d9afc0763d9b4

# Poster sources are binaries and not in git (repo rule). Pixel coordinates in
# build_assets.py are measured on exactly these files.
SOURCES=(
  "72c42533bd90b9d4ed05000cd50c94aaaf2b94eca099d69e66c629e9b017b571  quelle/ruhr-acro-convention-2027.jpg"
)

# Self-hosted webfonts (SIL OFL 1.1), fetched at build time only.
FONTS=(
  "252063af6ade8b9a744cde4ddad0fc21ea53b8ba711eed121a0c2e8610ea9c93 kalam-700-latin.woff2 https://cdn.jsdelivr.net/npm/@fontsource/kalam@5.3.0/files/kalam-latin-700-normal.woff2"
  "ba344451eab25b217a165363b1982048a5e5830a0daf36577973955a04cac793 nunito-latin.woff2 https://cdn.jsdelivr.net/npm/@fontsource-variable/nunito@5.3.0/files/nunito-latin-wght-normal.woff2"
)

[[ "${1:-}" == "--clean" ]] && rm -rf tools/.cache

for line in "${SOURCES[@]}"; do
  file=${line#*  }
  [[ -f $file ]] || { echo "missing source: $file (see docs/build.md)" >&2; exit 1; }
done
printf '%s\n' "${SOURCES[@]}" | sha256sum --check --quiet

[[ -x $VENV/bin/python ]] || uv venv -q --python 3.12 "$VENV"
uv pip install -q --python "$VENV/bin/python" -r tools/requirements.txt

mkdir -p "$(dirname "$MODEL")"
[[ -f $MODEL ]] || curl -fsSL -o "$MODEL" "$MODEL_URL"
echo "$MODEL_SHA256  $MODEL" | sha256sum --check --quiet

mkdir -p web/public/fonts
for line in "${FONTS[@]}"; do
  read -r sha name url <<<"$line"
  dest=web/public/fonts/$name
  [[ -f $dest ]] || curl -fsSL -o "$dest" "$url"
  echo "$sha  $dest" | sha256sum --check --quiet
done

"$VENV/bin/python" tools/upscale.py "$MODEL"
"$VENV/bin/python" tools/build_assets.py
