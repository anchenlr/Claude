# Shopify SEO, Simplified — ThriveCart Checkout Redesign

A redesign of the ThriveCart checkout at
`simplydigitaldesign.thrivecart.com/shopify-seo-simplified/` in the
Simply Digital Shops design language (burgundy / cream / gold,
Seriously Nostalgic + Bodoni Moda display type, Inter body type — the
same system as the Section Booster Playbook template in this repo).

All copy is taken from the live checkout page: the hero tagline, the
"Introducing" section, the learn-how-to list, the "What You'll Get
Inside" bullets, Anchen's bio, the $147 price, and the "Get your
Training!" button label.

## What's in this folder

| File | What it is | Where it goes |
|---|---|---|
| `custom-css-paste-ready.html` | The checkout skin (in `<style>`) **plus** the band auto-inject script (header + hero + footer), ready to paste | ThriveCart → Checkout → **Tracking** → "All pages" box |
| `inject.html` | Source of the band auto-inject script (already bundled into the paste-ready file) | — (edit here, then re-bundle) |
| `custom-css.css` | The same skin as raw CSS (source of the file above) | Only if your editor has a dedicated Custom CSS box |
| `checkout-sections.html` | Seven copy-paste HTML blocks (header, hero, introducing, pre-launch note, what you'll get, about Anchen, footer) plus three clearly-marked optional extras | ThriveCart Custom HTML elements |
| `preview.html` | Browser preview of the whole page with a mock order form — open it locally to see the design | Nowhere — reference only |

## Install steps

1. **Paste the CSS.** In ThriveCart go to **Products** → edit
   **Shopify SEO, Simplified** → **Checkout** tab → **Tracking**. Paste
   the entire contents of `custom-css-paste-ready.html` (including the
   `<style>` and `</style>` lines) into the **"All pages"** custom
   scripts box, then save. This is ThriveCart's documented way to add
   custom styling to checkout pages.

   *Alternative if you prefer to keep everything in the editor:* open
   the checkout editor (**Checkout** tab → **Design**), drag an **HTML
   element** to the top of the page, and paste
   `custom-css-paste-ready.html` into it — a `<style>` block inside an
   HTML element styles the whole page. (Some ThriveCart editor versions
   also have a dedicated Custom CSS box; if yours does, paste
   `custom-css.css` there instead. Yours apparently doesn't — hence the
   Tracking route.)

   The paste above ALSO auto-injects the three full-width bands —
   **header, hero and footer** — so you do **not** place those by hand.
   (See "Full-width bands" below for why.)

2. **Add the middle HTML blocks.** Only the non-band sections go in by
   hand. Drag a **Custom HTML** element to where you want each and paste
   one block from `checkout-sections.html` (each is fenced with
   `── BLOCK n ──` comments):

   - *(injected header + hero sit at the very top)*
   - What's inside sidebar (Block 1b) — left column of the cart
   - Introducing (Block 3) → Pre-launch note (Block 4) — above the form
   - What you'll get (Block 5) → About Anchen (Block 6) — above the form
     too, if your template won't let you place them below it
   - *(injected footer sits at the very bottom)*

   Do **not** hand-place Block 1 (header), Block 2 (hero) or Block 7
   (footer) — the script injects those. If you already placed them,
   delete them so they don't show twice.

3. **Preview on mobile.** Everything is responsive (cards stack, tagline
   hides, the about card stacks below 600 px), but check ThriveCart's
   mobile preview after placing the blocks.

## Optional extras

`checkout-sections.html` ends with three optional blocks — testimonial,
guarantee, FAQ — whose copy is **placeholder, not from your live page**.
Rewrite them before using (the guarantee in particular is a real
commitment — only publish it if you honour it), or ignore them entirely.

## Notes

- **No JavaScript anywhere.** The optional FAQ accordion uses native
  `<details>/<summary>`, so it works even if ThriveCart strips scripts
  from custom HTML.
- **Fonts.** Body/display fonts load from Google Fonts. The signature
  *Seriously Nostalgic* font loads from
  `offers.simplydigitalshops.com/fonts/…`. If that host doesn't send CORS
  headers for the thrivecart.com domain, browsers skip it and fall back
  to Bodoni Moda, which is the intended fallback — the page still looks
  right.
- **Generic selectors on purpose.** The skin styles ThriveCart's native
  form fields and buttons through element-level selectors
  (`input[type=email]`, `button[type=submit]`, …) rather than ThriveCart's
  internal class names, so it keeps working across ThriveCart templates
  and template updates. Use the editor's own color settings for anything
  the CSS doesn't catch (e.g. section background colors behind the cart
  form — set those to cream `#f5f2e8` or white).
- **Branding.** The header/footer say “Simply Digital Design” to match
  the checkout's domain; the tagline is borrowed from the Simply Digital
  Shops template. Both are plain text in Blocks 1 and 7 — edit freely.
- **Logo + tagline alignment.** For the logo and the tagline to line up,
  they must live in the *same* element. Block 1 now holds both — an
  `<img class="sds-logo-img">` on the left and the tagline on the right,
  vertically centred via `align-items: center`. Paste your uploaded
  logo's URL into that `src=""`. **If you use Block 1's logo, turn OFF
  ThriveCart's built-in logo** — otherwise you get two logos that won't
  align (which is what caused the earlier misalignment). Prefer a
  transparent-background PNG/SVG so it sits cleanly on the burgundy bar.
- **Full-width bands are auto-injected.** ThriveCart drops custom-HTML
  blocks into a padded, *non*-full-width content box, so a burgundy band
  placed by hand ends up framed by a grey gutter; and some templates
  won't let you place anything below the cart at all. To sidestep both,
  the script in `custom-css-paste-ready.html` attaches the three bands —
  **header + hero** to the top of the page `<body>` and the **footer** to
  the bottom — so they run true edge-to-edge and always land in the right
  spot, no matter what the editor's layout allows. That's why you don't
  hand-place Blocks 1, 2 and 7.
  - To edit the injected copy/links (logo URL, hero text, footer links),
    change the strings in `inject.html` (or the `<script>` at the bottom
    of the paste-ready file) and re-paste.
  - A `window.__sdsBands` guard + per-band id checks stop duplicates if
    the script runs twice.
  - (An earlier `calc(50% - 50vw)` CSS trick was dropped: ThriveCart's
    columns aren't always centred, so it shoved bands sideways over other
    content. Injecting into the body is the robust fix.)
- **Middle sections stay as editor blocks.** Introducing, the What's-
  inside sidebar, What you'll get and About all use a centred 780px inner
  container, so they look right inside ThriveCart's normal content box —
  no full-width treatment needed. Place them above the cart if your
  template won't allow blocks below it.
- **Address fields hidden ($0 offer).** The country/state/postcode row
  (`.ui-countryzip`) is hidden via CSS since this is a free offer that
  needs no address and ThriveCart won't remove it in settings. The
  country keeps its default value in the DOM so the form still submits.
  If ThriveCart ever flags state/zip as required on submit, they'd need
  their `required` flag cleared too — ask and I'll add that.
- **ThriveCart's own footer is hidden.** ThriveCart renders a built-in
  footer (`.thrivecart-footer`) with "Powered by ThriveCart", a copyright
  and the hCaptcha legal notice, inside the width-capped body. The CSS
  hides it entirely (`.region-footer, .thrivecart-footer { display:none }`)
  so our injected footer stands alone. Note this also removes the hCaptcha
  notice — hCaptcha's terms ask that either their badge or that notice
  stay visible, so it's a deliberate choice. To restore just the notice
  (hiding only the branding), revert this rule to the branding-only
  version in git history.
- **Compact sidebar list.** Block 1b (`.sds-mini`) is a narrow, gold-
  accented “What's inside” bullet list for the left column of the cart
  — drop it under the product image or above the coupon field.
- **Button & footer colours.** The order button is gold with burgundy
  text; the footer is dark red (burgundy) with gold links. The `!important`
  flags on headings, the hero subtitle, and footer links exist to beat
  ThriveCart's default dark-blue theme colour, which otherwise bleeds
  through.
- **Brand tokens** (for tweaking in the editor UI):
  - Burgundy `#5a002a` · Red `#950d20` · Gold `#c7a35a`
  - Cream `#e8e4d3` · Cream light `#f5f2e8` · Ink `#1a1410`
