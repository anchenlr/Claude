/**
 * Future of Design Summit — shared configuration.
 * Single source of truth for every link/ID referenced across
 * future-of-design-summit.html and future-of-design-summit-thank-you.html.
 *
 * Same pattern as assets/showcase-config.js (the AI Systems Showcase page),
 * kept as its own file because this summit has its own offer, gift and
 * form.
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

  // Shopify Homepage Scorecard — Anchen's existing lead magnet (the quick
  // self-assessment she opens the "AI Show & Tell" session with, briefly).
  // It's a separate, lighter tool from The Conversion Roadmap below, and
  // funnels into it. Used here as the free, opt-in-gated gift for this
  // summit (replaces the Sticky Add to Cart Booster used on the AI
  // Systems Showcase page). No coupon code needed: it's free to access,
  // no purchase/checkout step. Only ever shown on the thank-you page,
  // after someone has opted in.
  scorecardUrl: "https://offers.simplydigitalshops.com/shopify-scorecard",

  // The Conversion Roadmap — the paid, deeper audit product demonstrated
  // live in the session (sold via ThriveCart). Public discount teaser
  // shown on the opt-in page itself (no opt-in required) AND repeated on
  // the thank-you page, same pattern as the AI Systems Showcase's
  // "Special Showcase Offer" section. conversionRoadmapUrl (footer link)
  // is the same product page without the coupon param — confirm/replace
  // if a dedicated non-discount landing page exists instead.
  conversionRoadmapUrl: "https://simplydigitaldesign.thrivecart.com/shopify-conversion-roadmap/",
  conversionRoadmapDiscountUrl: "https://simplydigitaldesign.thrivecart.com/shopify-conversion-roadmap/?coupon=DESIGNERBOSS26",
  conversionRoadmapDiscountCode: "DESIGNERBOSS26",

  // AI Project Consultation — a no-pressure chat about the attendee's own
  // AI project, offered alongside the Conversion Roadmap rather than
  // pitching a paid VIP Day upsell directly.
  aiConsultationUrl: "https://clients.simplydigitaldesign.com/public/ai-project-consultation",
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
