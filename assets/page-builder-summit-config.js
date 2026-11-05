/**
 * Page Builder Summit — shared configuration.
 * Single source of truth for every link/ID referenced across
 * page-builder-summit.html and page-builder-summit-thank-you.html.
 *
 * Same pattern as assets/showcase-config.js (the AI Systems Showcase page)
 * and assets/design-summit-config.js (the Future of Design Summit page),
 * kept as its own file because this summit has its own resource and form.
 * Every {{TOKEN}} value below is a placeholder. Search this file for
 * "{{" to find everything that still needs a real value before launch.
 */
window.SHOWCASE_CONFIG = {
  // Core URLs
  optinUrl: "https://offers.simplydigitalshops.com/summits/page-builder-summit.html",
  thankYouUrl: "https://offers.simplydigitalshops.com/summits/page-builder-summit-thank-you.html",
  privacyUrl: "https://simplydigitalshops.com/policies/privacy-policy",
  supportUrl: "https://simplydigitalshops.com/pages/contact",

  // NOTE: the opt-in form's action posts directly to the ActiveCampaign
  // hosted form (see the <form> tag in page-builder-summit.html) instead
  // of reading a URL from here — a form's submission target belongs in
  // its own action attribute, not injected by JS. thankYouUrl above is
  // for reference only: the actual post-submit redirect must be set
  // inside that AC form's own settings ("after user subscribes" ->
  // redirect to URL).

  // The Steal-This Automation Cheat Sheet — the only resource/offer on
  // this summit page (no gated second gift, no discount-coded upsell,
  // unlike the other two summit pages).
  cheatSheetUrl: "{{CHEAT_SHEET_URL}}",

  // The Site Audit Engine, part of Anchen's PowerPack — mentioned as a
  // light teaser/CTA only (no coupon code for this one).
  powerPackUrl: "{{POWERPACK_URL}}",
};

/**
 * Applies SHOWCASE_CONFIG values to any element in the document carrying
 * a data-config attribute, e.g. <a data-config="cheatSheetUrl" href="#">.
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
