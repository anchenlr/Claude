/* ═══════════════════════════════════════════════════════════════
   PRODUCT CONFIG — Shopify SEO, Simplified
   Edit ONLY this file per product. The shared framework.css + engine.js
   turn these values into the header/hero/footer bands.
   (HTML entities like &amp; and &#8595; are fine — values go into innerHTML.)
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

  /* free / digital offer → hide the address fields (fill-then-hide) */
  hideAddress: true,

  /* hero band (checkout page) */
  hero: {
    eyebrow: 'A step-by-step Shopify SEO course',
    title: 'Shopify SEO, Simplified',
    subtitle: 'A simple, practical approach to optimising Shopify stores for real results.',
    meta: [
      'Step-by-step training',
      'Workbook &amp; checklist included',
      'For store owners &amp; service providers'
    ],
    ctaLabel: 'Get access to the course'
  }
};
