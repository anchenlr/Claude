/* ═══════════════════════════════════════════════════════════════
   SIMPLY DIGITAL — Shared ThriveCart engine (product-agnostic)

   FRAGMENT: build.sh wraps this (with the product's config) in ONE
   self-contained IIFE — do not add an outer function here, and don't
   run this file on its own. It reads the `SDS_CONFIG` declared just
   above it in the same IIFE, and:
     • injects the full-width header + hero + footer bands to <html>
       (hero only when SDS_CONFIG.page === 'checkout')
     • fills + hides the address fields when SDS_CONFIG.hideAddress
     • smooth-scrolls the .sds-cta buttons to the checkout
   Edit only the product's config, never this file.
   ═══════════════════════════════════════════════════════════════ */
if (window.__sdsBands) return;            // guard against double-load
window.__sdsBands = true;

var C = (typeof SDS_CONFIG !== 'undefined') ? SDS_CONFIG : {};
var page = C.page || 'checkout';
var brand = (C.footer && C.footer.brand) || '';

/* ── hard deadline: once the offer closes, redirect the checkout ──
   Only active when the product config sets `deadline` + `deadlineRedirect`
   (other products are unaffected). Success page is never redirected. */
if (page === 'checkout' && C.deadline && C.deadlineRedirect) {
  var __dl = new Date(C.deadline).getTime();
  if (isFinite(__dl) && Date.now() > __dl) {
    window.location.replace(C.deadlineRedirect);
    return;
  }
}

function headerHTML() {
  var logo = C.logoUrl
    ? '<img class="sds-logo-img" src="' + C.logoUrl + '" alt="' + brand + '" />'
    : '<span class="sds-logo">' + brand + '</span>';
  return '<div class="sds-block sds-header"><div class="sds-container">' +
           logo +
           '<span class="sds-tagline">' + (C.tagline || '') + '</span>' +
         '</div></div>';
}

function heroHTML() {
  var h = C.hero || {};
  var meta = (h.meta || []).map(function (m) {
    return '<span>&#10003; <strong>' + m + '</strong></span>';
  }).join('');
  return '<div class="sds-block sds-hero"><div class="sds-container">' +
           (h.eyebrow ? '<div class="sds-hero-eyebrow">' + h.eyebrow + '</div>' : '') +
           '<h1 class="sds-display">' + (h.title || '') + '</h1>' +
           (h.subtitle ? '<p class="sds-hero-subtitle">' + h.subtitle + '</p>' : '') +
           (meta ? '<div class="sds-hero-meta">' + meta + '</div>' : '') +
           (h.ctaLabel ? '<div class="sds-cta-wrap"><a href="#sds-checkout" class="sds-cta">' + h.ctaLabel + ' &#8595;</a></div>' : '') +
         '</div></div>';
}

function footerHTML() {
  var f = C.footer || {};
  var links = (f.links || []).map(function (l) {
    return '<a href="' + l.url + '" target="_blank" rel="noopener">' + l.label + '</a>';
  }).join(' &nbsp;&middot;&nbsp; ');
  return '<div class="sds-container">' +
           '<p>&copy; ' + brand + (links ? ' &nbsp;&middot;&nbsp; ' + links : '') + '</p>' +
           '<p class="sds-captcha">This site is protected by hCaptcha and its ' +
             '<a href="https://hcaptcha.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and ' +
             '<a href="https://hcaptcha.com/terms" target="_blank" rel="noopener">Terms of Service</a> apply.</p>' +
         '</div>';
}

function build() {
  var root = document.documentElement, body = document.body;
  if (!document.getElementById('sds-top-injected')) {
    var top = document.createElement('div');
    top.id = 'sds-top-injected';
    top.innerHTML = headerHTML() + (page === 'checkout' ? heroHTML() : '');
    root.insertBefore(top, body);
  }
  if (!document.getElementById('sds-footer-injected')) {
    var f = document.createElement('div');
    f.id = 'sds-footer-injected';
    f.className = 'sds-block sds-footer';
    f.innerHTML = footerHTML();
    root.appendChild(f);
  }
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', build);
else build();

/* ── address fill-then-hide (checkout only, when enabled) ────────── */
if (page === 'checkout' && C.hideAddress) {
  var sdsHideAddress = function () {
    var row = document.querySelector('.ui-countryzip');
    var country = document.querySelector('[name="customer.address.country"]');
    var state = document.querySelector('[name="customer.address.state"]');
    var zip = document.querySelector('[name="customer.address.zip"]');
    var fill = function (el, val) {
      if (el && (el.value == null || el.value === '')) {
        el.value = val;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
      }
    };
    fill(country, 'NZ');
    fill(state, 'N/A');
    fill(zip, '0000');
    if (row) row.style.setProperty('display', 'none', 'important');
  };
  sdsHideAddress();
  [400, 1200, 2500].forEach(function (t) { setTimeout(sdsHideAddress, t); });
  document.addEventListener('click', sdsHideAddress, true);
}

/* ── smooth-scroll the CTA buttons to the checkout ───────────────── */
function findCheckout() {
  return document.getElementById('sds-checkout')
    || document.querySelector('.coupon-wrap')
    || document.querySelector('.builder-v2-block-core_pricing_breakdown')
    || document.querySelector('input[type="email"]');
}
document.addEventListener('click', function (e) {
  var btn = e.target.closest ? e.target.closest('.sds-cta') : null;
  if (!btn) return;
  var target = findCheckout();
  if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
});
