/**
 * AI Systems Showcase — shared configuration.
 * Single source of truth for every link/ID referenced across
 * ai-systems-showcase.html and ai-systems-showcase-thank-you.html.
 *
 * Every {{TOKEN}} value below is a placeholder. Search this file for "{{"
 * to find everything that still needs a real value before launch.
 * The site is not launch-ready while any {{TOKEN}} remains.
 */
window.SHOWCASE_CONFIG = {
  // Core URLs
  optinUrl: "https://offers.simplydigitalshops.com/summits/ai-systems-showcase.html",
  thankYouUrl: "https://offers.simplydigitalshops.com/summits/ai-systems-showcase-thank-you.html",
  privacyUrl: "https://simplydigitalshops.com/policies/privacy-policy",
  supportUrl: "https://simplydigitalshops.com/pages/contact",

  // NOTE: the opt-in form's action now posts directly to the
  // ActiveCampaign hosted form (see the <form> tag in
  // ai-systems-showcase.html) instead of reading a URL from here — a
  // form's submission target belongs in its own action attribute, not
  // injected by JS. thankYouUrl above is for reference only: the actual
  // post-submit redirect must be set inside that AC form's own settings
  // ("after user subscribes" -> redirect to URL).

  // Resource downloads
  blueprintDownloadUrl: "https://offers.simplydigitalshops.com/summits/downloads/Shopify%20Section%20Booster%20Playbook%20Delivery.blueprint.json",
  walkthroughUrl: "https://docs.google.com/document/d/1YGJfeu2C6jq8f6pVTdhWwgQalK_QMu2agKQ_PAYi-wM/copy",

  // Sticky Add to Cart Booster — the free, opt-in-gated gift.
  // Only ever shown on the thank-you page, after someone has opted in.
  boosterProductUrl: "https://simplydigitalshops.com/products/sticky-add-to-cart-custom-section-booster",
  boosterDiscountUrl: "https://simplydigitalshops.com/discount/AISHOWCASE?redirect=%2Fproducts%2Fsticky-add-to-cart-custom-section-booster",
  couponCode: "AISHOWCASE",

  // Custom Section Boosters — the public 30%-off teaser shown on the
  // opt-in page itself (no opt-in required), same pattern as the WOW
  // Summit page's "Special WOW Offer" section.
  boostersCollectionUrl: "https://simplydigitalshops.com/collections/shopify-custom-section-boosters",
  boostersThirtyOffUrl: "https://simplydigitalshops.com/discount/AISHOWCASE30?redirect=%2Fcollections%2Fshopify-custom-section-boosters",
  boostersThirtyOffCode: "AISHOWCASE30",
};

/**
 * Applies SHOWCASE_CONFIG values to any element in the document carrying
 * a data-config attribute, e.g. <a data-config="boostersCollectionUrl" href="#">.
 * Keeps repeated links/IDs defined once in this file instead of scattered
 * through the markup.
 */
(function applyShowcaseConfig() {
  function run() {
    var config = window.SHOWCASE_CONFIG || {};
    document.querySelectorAll("[data-config]").forEach(function (el) {
      var key = el.getAttribute("data-config");
      var value = config[key];
      if (value === undefined) return;

      if (el.hasAttribute("href")) {
        el.setAttribute("href", value);
      } else if (el.hasAttribute("data-config-text")) {
        el.textContent = value;
      } else {
        el.textContent = value;
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", run);
  } else {
    run();
  }
})();
