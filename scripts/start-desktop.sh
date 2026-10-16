#!/usr/bin/env bash
# Launch Darzi Baba Desktop (real PHP ERP UI in Electron).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT/desktop"
if [[ ! -d node_modules/electron ]]; then
  npm install
fi
exec npm start
