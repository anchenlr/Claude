/* ═══════════════════════════════════════════════════════════════
   PRODUCT CONFIG — The Site Audit Engine  ($97, digital/membership)
   A Make automation + Claude skills kit for designers, developers,
   freelancers and agencies to run branded site audits on demand.
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

  /* Paid $97 product — address fields left visible. */
  hideAddress: false,

  /* No deadline — plain evergreen page, no countdown or scarcity. */

  /* hero band (checkout page) */
  hero: {
    eyebrow: 'A ready-to-import Make automation',
    title: 'Turn any URL into a branded audit in minutes.',
    subtitle: 'A ready-to-import Make automation that gathers the page and speed data and drafts the audit, based on your own checks and in your own brand.',
    meta: [
      'Ready-to-import Make automation',
      'Your own criteria, your own brand',
      'Video walkthrough included'
    ],
    ctaLabel: 'Get the Site Audit Engine'
  }
};
