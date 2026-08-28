/* ═══════════════════════════════════════════════════════════════
   PRODUCT CONFIG — Conversion Roadmap: Challenge Bundle  ($97)
   New, separate page (duplicate of the Conversion Roadmap product).
   Single $97 line item; Loom walkthrough + Black Friday Priority List
   bonuses baked in; hard close on 16 Oct.
   ═══════════════════════════════════════════════════════════════ */
var SDS_CONFIG = {
  /* shared brand */
  logoUrl: 'https://spark.thrivecart.com/0x0/user_assets%2FLOTL3TGY%2Fuploads%2Fimages%2Fsds-light-logo-1784845132.png',
  tagline: 'Designed to stand out. Built to convert.',
  footer: {
    brand: 'Simply Digital Design',
    links: [
      { label: 'Privacy Policy',   url: 'https://simplydigitalshops.com/policies/privacy-policy' },
      { label: 'Terms of Service', url: 'https://simplydigitalshops.com/policies/terms-of-service' },
      { label: 'Our Main Website', url: 'https://simplydigitalshops.com/' }
    ]
  },

  /* Paid $97 — address fields left visible. */
  hideAddress: false,

  /* HARD DEADLINE — 16 Oct 11:59pm Pacific (PDT, UTC-7 in October).
     Once passed, the checkout redirects to the standard Roadmap page
     (which sells the Roadmap without the challenge bonus). */
  deadline: '2026-10-16T23:59:00-07:00',
  deadlineRedirect: 'https://simplydigitaldesign.thrivecart.com/shopify-conversion-roadmap/',

  /* hero band (checkout page) */
  hero: {
    eyebrow: 'This week only — challenge bundle',
    title: 'The Shopify Conversion Roadmap',
    subtitle: 'Your next step, now that you know where the leaks are.<br><br>You just spent five days finding what’s costing you sales. Now let’s fix it, together — with a strategist’s eyes on your actual store.',
    meta: [
      'Personal review by Anchen',
      'Loom walkthrough included',
      '+ Black Friday Priority List bonus'
    ],
    ctaLabel: 'Get my roadmap'
  }
};
