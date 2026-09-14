/* ═══════════════════════════════════════════════════════════════
   PRODUCT CONFIG — The Full Case File  ($27, was $297, today only)
   Upsell checkout for Fix Your Store Challenge participants. The sales
   page (challenge.simplydigitalshops.com/thank-you-upgrade/) already
   does the selling, so this checkout stays deliberately minimal:
   header + a short hero (with price) + one "what's included" list.
   No Introducing / About / bonus / deadline sections.
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

  /* Paid $27 — address fields left visible. */
  hideAddress: false,

  /* hero band (checkout page) */
  hero: {
    eyebrow: 'Fix Your Store Challenge — today only',
    title: 'The Full <span style="color:#B01226">Case File</span>',
    subtitle: '$27 today <span style="text-decoration:line-through;opacity:0.6;">$297</span> — keep every recording, the workbook, and the tools for good.',
    meta: [
      'Lifetime recording access',
      'Complete challenge workbook',
      '+ $49 Section Booster coupon'
    ],
    ctaLabel: 'Get the Full Case File'
  }
};
