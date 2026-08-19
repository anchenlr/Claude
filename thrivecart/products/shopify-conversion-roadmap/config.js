/* ═══════════════════════════════════════════════════════════════
   PRODUCT CONFIG — Shopify Conversion Roadmap  ($37, digital)
   DRAFT — hero copy below is a first pass from the product description;
   review/replace the lines marked EDIT. Everything else is inherited
   from the shared brand.
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

  /* Paid $37 digital product. Set true only if you also need the address
     fields hidden here (the free opt-in did for legal reasons). Test a
     purchase after enabling, since ThriveCart may require them. */
  hideAddress: false,

  /* hero band (checkout page) — EDIT the copy below */
  hero: {
    eyebrow: 'A strategy-first Shopify audit',                                   /* EDIT */
    title: 'Shopify Conversion Roadmap',                                         /* EDIT */
    subtitle: 'A personalised, strategy-first audit of your store — see exactly what’s holding it back and get a prioritised plan to fix it.', /* EDIT */
    meta: [                                                                      /* EDIT */
      'Personalised audit',
      'Prioritised action plan',
      'For Shopify store owners'
    ],
    ctaLabel: 'Get my roadmap'                                                   /* EDIT */
  }
};
