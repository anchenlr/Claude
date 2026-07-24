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
| `custom-css-paste-ready.html` | The checkout skin wrapped in `<style>` tags, ready to paste | ThriveCart → Checkout → **Tracking** → "All pages" box |
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

2. **Add the HTML blocks.** Drag a **Custom HTML** element to where you
   want each section and paste one block from `checkout-sections.html`
   into it (each block is fenced with `── BLOCK n ──` comments), in this
   order:

   - Header bar — very top of the page
   - Hero → Introducing → Pre-launch note — above ThriveCart's form
   - *(ThriveCart's own contact/payment form sits here)*
   - What you'll get → About Anchen — below the form
   - Footer — very bottom

3. **Fill in the TODOs.** Search `checkout-sections.html` for `TODO`:
   - Anchen's photo URL (Block 6)
   - Privacy Policy / Terms of Service / Main Website links (Block 7)
   - Optionally swap the burgundy text panel in Block 5 for the iMac
     product mockup image from the current page

4. **Remove padding around the blocks.** If the editor wraps custom HTML
   elements in padded containers, set that element's padding to 0 so the
   burgundy header/hero and dark footer run edge-to-edge.

5. **Preview on mobile.** Everything is responsive (cards stack, tagline
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
- **Full-width bands = placement, not CSS.** The header, hero and footer
  are full-width colour bands, and the hero's gold bottom border rides
  the hero's width. They fill 100% of whatever container they're dropped
  into, so **put each of these three blocks in its own FULL-WIDTH ROW**
  in the builder — not inside the two-column cart area. In ThriveCart's
  Enhanced editor: add a new full-width Row/Block for each band and drop
  the HTML element in there. (Earlier a viewport `calc(50% - 50vw)` trick
  was used to force this, but ThriveCart's columns aren't always centred,
  so it shoved the footer sideways over the summary box — hence the
  placement approach instead.) If a band still looks inset, set that HTML
  element's own left/right padding to 0.
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
