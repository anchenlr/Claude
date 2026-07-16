# Shopify SEO Simplified — ThriveCart Checkout Redesign

A Simply Digital Shops-branded skin for the ThriveCart checkout at
`simplydigitaldesign.thrivecart.com/shopify-seo-simplified/`, matching the
brand system used in the Section Booster Playbook template (burgundy /
cream / gold, Seriously Nostalgic + Bodoni Moda display type, Inter body
type).

## What's in this folder

| File | What it is | Where it goes |
|---|---|---|
| `custom-css.css` | The full checkout skin — fonts, colors, form fields, buttons, and all styles for the section blocks | ThriveCart Custom CSS box |
| `checkout-sections.html` | Seven copy-paste HTML blocks: header, hero, what's inside, testimonial, guarantee, FAQ, footer | ThriveCart Custom HTML elements |
| `preview.html` | Browser preview of the whole page with a mock order form — open it locally to see the design | Nowhere — reference only |

## Install steps

1. **Open the checkout editor.** In ThriveCart go to your **Shopify SEO
   Simplified** product → **Checkout** tab → **Design** → **Customize**.
   (A one-column "sales page"-style template works best with these
   full-width blocks.)

2. **Paste the CSS.** Find the **Custom CSS** area in the editor's
   settings and paste the entire contents of `custom-css.css`. Save. The
   page background, fonts, input fields and the order button restyle
   immediately.

3. **Add the HTML blocks.** In the editor, drag a **Custom HTML** element
   to where you want each section and paste one block from
   `checkout-sections.html` into it (each block is fenced with
   `── BLOCK n ──` comments). Suggested order:

   - Header bar — very top of the page
   - Hero — directly above ThriveCart's cart/order form
   - *(ThriveCart's own cart form sits here)*
   - What's inside → Testimonial → Guarantee → FAQ — below the form
   - Footer — very bottom

4. **Remove padding around the blocks.** If the editor wraps custom HTML
   elements in padded containers, set that element's padding to 0 so the
   burgundy header/hero and dark footer run edge-to-edge.

5. **Swap the placeholder copy.** The testimonial quote/attribution, the
   guarantee length (currently 14 days), the price references, and the
   "what's inside" bullets are all placeholders — edit them right in the
   HTML blocks to match the real offer.

6. **Preview on mobile.** Everything is responsive (cards stack, tagline
   hides, guarantee stacks below 600px), but check ThriveCart's mobile
   preview after placing the blocks.

## Notes

- **No JavaScript anywhere.** The FAQ accordion uses native
  `<details>/<summary>`, so it works even if ThriveCart strips scripts
  from custom HTML.
- **Fonts.** Body/display fonts load from Google Fonts. The signature
  *Seriously Nostalgic* font loads from
  `offers.simplydigitalshops.com/fonts/…`. If that host doesn't send CORS
  headers (`Access-Control-Allow-Origin`) for the thrivecart.com domain,
  browsers will skip the font and fall back to Bodoni Moda, which is the
  intended fallback — the page still looks right.
- **Generic selectors on purpose.** The skin styles ThriveCart's native
  form fields and buttons through element-level selectors
  (`input[type=email]`, `button[type=submit]`, …) rather than ThriveCart's
  internal class names, so it keeps working across ThriveCart templates
  and template updates. Use the editor's own color settings for anything
  the CSS doesn't catch (e.g. section background colors behind the cart
  form — set those to cream `#f5f2e8` or white).
- **Brand tokens** (for tweaking in the editor UI):
  - Burgundy `#5a002a` · Red `#950d20` · Gold `#c7a35a`
  - Cream `#e8e4d3` · Cream light `#f5f2e8` · Ink `#1a1410`
