/**
 * Women Of Web Summit — shared configuration.
 * Single source of truth for every link/ID referenced across
 * wow.html and wow-thank-you.html.
 *
 * Same pattern as assets/showcase-config.js (the AI Systems Showcase
 * page), assets/design-summit-config.js (Future of Design Summit) and
 * assets/page-builder-summit-config.js (Page Builder Summit), kept as
 * its own file because this summit has its own resource and form.
 */
window.SHOWCASE_CONFIG = {
  // Core URLs
  optinUrl: "https://offers.simplydigitalshops.com/summits/wow.html",
  thankYouUrl: "https://offers.simplydigitalshops.com/summits/wow-thank-you.html",
  privacyUrl: "https://simplydigitalshops.com/policies/privacy-policy",
  supportUrl: "https://simplydigitalshops.com/pages/contact",

  // NOTE: the opt-in form's action posts directly to the ActiveCampaign
  // hosted form (see the <form> tag in wow.html) instead of reading a
  // URL from here — a form's submission target belongs in its own
  // action attribute, not injected by JS. thankYouUrl above is for
  // reference only: the actual post-submit redirect must be set inside
  // that AC form's own settings ("after user subscribes" -> redirect
  // to URL).

  // The 20-Point Ecommerce Conversion Checklist — the only gated
  // resource/offer on this summit page (no second gated gift, same
  // pattern as the Page Builder Summit page). Delivered in two formats.
  checklistDocUrl: "https://docs.google.com/document/d/12Mp0YKhVAYmZ8PoOxCIh_FeAmVnkDi8H9q5evFgpuQ4/copy",
  checklistPdfUrl: "https://drive.google.com/file/d/1ShD_boD6chTvKpn4ZCOYKk3mkquoOT30/view?usp=sharing",

  // The Site Audit Engine — free inside the Summit Swag Bag, but also
  // linked directly here for anyone who wants it without digging for
  // the Swag Bag. No coupon code for this one.
  siteAuditEngineUrl: "https://simplydigitaldesign.thrivecart.com/the-site-audit-engine/",
};

/**
 * Applies SHOWCASE_CONFIG values to any element in the document carrying
 * a data-config attribute, e.g. <a data-config="checklistDocUrl" href="#">.
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
