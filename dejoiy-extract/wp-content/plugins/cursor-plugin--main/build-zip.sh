#!/usr/bin/env bash
# Build a WordPress-ready plugin zip (correct folder name + plugin header at root).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
SLUG="dejoiy-ai-control-bridge"
OUT="${ROOT}/${SLUG}.zip"
STAGE="${ROOT}/.build/${SLUG}"

rm -rf "${ROOT}/.build" "${OUT}"
mkdir -p "${STAGE}"

shopt -s dotglob nullglob
for item in "${ROOT}"/*; do
  base="$(basename "${item}")"
  case "${base}" in
    .git | .build | "${SLUG}.zip" | build-zip.sh)
      continue
      ;;
  esac
  cp -a "${item}" "${STAGE}/"
done

cd "${ROOT}/.build"
zip -r "${OUT}" "${SLUG}" -q
rm -rf "${ROOT}/.build"

echo "Created: ${OUT}"
echo "Upload this file in WordPress: Plugins → Add New → Upload Plugin"
