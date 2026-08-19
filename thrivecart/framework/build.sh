#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════
# Build each product's paste-ready ThriveCart files from the shared
# framework (framework.css + engine.js) and the product's config.js
# and success-card.html.
#
# Usage:  bash framework/build.sh            # builds every product
#         bash framework/build.sh <product>  # builds one product folder
#
# Outputs (per product, in products/<name>/dist/):
#   checkout-tracking.html  → paste into Checkout → Tracking → "All pages"
#   success-block.html      → paste into a Custom HTML element on the
#                             SUCCESS page (carries its own style+script+card)
# The checkout content blocks live in products/<name>/sections.html and
# are pasted by hand as Custom HTML elements in the checkout editor.
# ═══════════════════════════════════════════════════════════════
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FW="$ROOT/framework"
CSS="$FW/framework.css"
ENGINE="$FW/engine.js"

build_one() {
  local dir="$1"
  local name; name="$(basename "$dir")"
  [ -f "$dir/config.js" ] || { echo "skip $name (no config.js)"; return; }
  mkdir -p "$dir/dist"

  # ── checkout tracking paste ──
  {
    echo "<!-- Shopify checkout skin + bands for: $name"
    echo "     Paste into ThriveCart → Checkout → Tracking → \"All pages\"."
    echo "     REPLACE any previous version so only ONE copy runs. -->"
    echo "<style>"; cat "$CSS"; echo "</style>"
    echo ""
    echo "<script>"
    cat "$dir/config.js"
    echo "SDS_CONFIG.page = 'checkout';"
    cat "$ENGINE"
    echo "</script>"
  } > "$dir/dist/checkout-tracking.html"

  # ── success page block (style + script + confirmation card) ──
  {
    echo "<!-- Branded SUCCESS page block for: $name"
    echo "     Paste into a Custom HTML element on the ThriveCart success page. -->"
    echo "<style>"; cat "$CSS"; echo "</style>"
    echo ""
    echo "<script>"
    cat "$dir/config.js"
    echo "SDS_CONFIG.page = 'success';"
    cat "$ENGINE"
    echo "</script>"
    echo ""
    if [ -f "$dir/success-card.html" ]; then cat "$dir/success-card.html"; fi
  } > "$dir/dist/success-block.html"

  echo "built $name → dist/checkout-tracking.html, dist/success-block.html"
}

if [ "${1:-}" != "" ]; then
  build_one "$ROOT/products/$1"
else
  for d in "$ROOT"/products/*/; do build_one "$d"; done
fi
