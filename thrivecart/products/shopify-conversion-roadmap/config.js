/* ═══════════════════════════════════════════════════════════════
   PRODUCT CONFIG — Shopify Conversion Roadmap  ($37, digital)
   Copy taken from the live checkout.
   ═══════════════════════════════════════════════════════════════ */
var SDS_CONFIG = {
  /* shared brand (same across all Simply Digital products) */
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

  /* Paid $37 product — address fields left visible (set true only if this
     offer must not collect an address; test a purchase after enabling). */
  hideAddress: false,

  /* hero band (checkout page) */
  hero: {
    eyebrow: 'A personalised, strategy-first audit',
    title: 'Shopify Conversion Roadmap',
    subtitle: 'Get a clear picture of exactly what’s holding your store back — and a prioritised plan to fix it.',
    meta: [
      '20 conversion categories scored',
      'Quick wins + 30 &amp; 90-day plan',
      'Delivered in 24–48 hours'
    ],
    ctaLabel: 'Get my roadmap'
  }
};
