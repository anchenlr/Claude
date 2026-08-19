/**
 * Future of Design Summit — shared configuration.
 * Single source of truth for every link/ID referenced across
 * future-of-design-summit.html and future-of-design-summit-thank-you.html.
 *
 * Same pattern as assets/showcase-config.js (the AI Systems Showcase page),
 * kept as its own file because this summit has its own offer, gift and
 * form. Every {{TOKEN}} value below is a placeholder. Search this file for
 * "{{" to find everything that still needs a real value before launch.
 * The site is not launch-ready while any {{TOKEN}} remains.
 */
window.SHOWCASE_CONFIG = {
  // Core URLs
  optinUrl: "https://offers.simplydigitalshops.com/summits/future-of-design-summit.html",
  thankYouUrl: "https://offers.simplydigitalshops.com/summits/future-of-design-summit-thank-you.html",
  privacyUrl: "https://simplydigitalshops.com/policies/privacy-policy",
  supportUrl: "https://simplydigitalshops.com/pages/contact",

  // NOTE: the opt-in form's action posts directly to the ActiveCampaign
  // hosted form (see the <form> tag in future-of-design-summit.html)
  // instead of reading a URL from here — a form's submission target
  // belongs in its own action attribute, not injected by JS. thankYouUrl
  // above is for reference only: the actual post-submit redirect must be
  // set inside that AC form's own settings ("after user subscribes" ->
  // redirect to URL).

  // Resource: the walkthrough behind The Conversion Roadmap Delivery
  // System, demonstrated live in the "AI Show & Tell" session. No Make
  // blueprint this time — this is a document walkthrough only.
  walkthroughUrl: "https://docs.google.com/document/d/1T-vfMtgy4lYWZZeLsDMPCR82cEhsDbTgooMoz2XxuVE/copy",

  // Shopify Homepage Scorecard — the free, opt-in-gated gift for this
  // summit (replaces the Sticky Add to Cart Booster used on the AI
  // Systems Showcase page). No coupon code needed: it's free to access,
  // no purchase/checkout step. Only ever shown on the thank-you page,
  // after someone has opted in.
  scorecardUrl: "https://offers.simplydigitalshops.com/shopify-scorecard",

  // The Conversion Roadmap — the paid product demonstrated in the
  // session. Public discount teaser shown on the opt-in page itself (no
  // opt-in required) AND repeated on the thank-you page, same pattern as
  // the AI Systems Showcase's "Special Showcase Offer" section.
  conversionRoadmapUrl: "{{CONVERSION_ROADMAP_URL}}",
  conversionRoadmapDiscountUrl: "{{CONVERSION_ROADMAP_DISCOUNT_URL}}",
  conversionRoadmapDiscountCode: "{{CONVERSION_ROADMAP_DISCOUNT_CODE}}",

  // VIP Day — the "I'll build it for you" upsell CTA alongside the
  // Conversion Roadmap offer.
  vipDayUrl: "{{VIP_DAY_URL}}",
};

/**
 * Applies SHOWCASE_CONFIG values to any element in the document carrying
 * a data-config attribute, e.g. <a data-config="scorecardUrl" href="#">.
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
