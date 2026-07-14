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
  optinUrl: "{{OPTIN_URL}}",
  thankYouUrl: "{{THANK_YOU_URL}}",
  privacyUrl: "{{PRIVACY_URL}}",
  supportUrl: "{{SUPPORT_URL}}",

  // Form submission (must be a same-origin serverless/webhook endpoint —
  // never call the ActiveCampaign REST API directly from the browser)
  formEndpoint: "{{FORM_ENDPOINT}}",
  acSourceTag: "{{AC_SOURCE_TAG}}",

  // Resource downloads
  blueprintDownloadUrl: "{{BLUEPRINT_DOWNLOAD_URL}}",
  walkthroughUrl: "{{WALKTHROUGH_URL}}",
  checklistUrl: "{{CHECKLIST_URL}}",

  // Shopify Site Booster / offer links
  boosterProductUrl: "{{BOOSTER_PRODUCT_URL}}",
  boosterDiscountUrl: "{{BOOSTER_DISCOUNT_URL}}",
  boostersCollectionUrl: "{{BOOSTERS_COLLECTION_URL}}",

  // Offer details
  couponCode: "AIShowcase",

  // Hidden form fields sent with every opt-in
  campaign: "AIShowcase2026",
  resourceName: "Premium Post-Purchase Blueprint",
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
