/**
 * Future of Design Summit — shared behaviour.
 * Identical logic to assets/showcase.js (the AI Systems Showcase page) —
 * kept as its own file only because each summit page loads its own
 * config/styles/behaviour trio. Modal (accessible, focus-trapped),
 * coupon copy-to-clipboard, and a minimal analytics shim.
 *
 * The opt-in form itself is ActiveCampaign's own hosted-form embed
 * (see the modal in future-of-design-summit.html) — its submission
 * handling lives in the <script> that comes with that embed, not here.
 */
(function () {
  "use strict";

  /* ─── ANALYTICS SHIM ─────────────────────────────────
     Pushes to window.dataLayer if present (GTM-style), otherwise no-ops.
     Never throws, never blocks the calling action. */
  function trackEvent(name, data) {
    try {
      if (window.dataLayer && typeof window.dataLayer.push === "function") {
        window.dataLayer.push(Object.assign({ event: name }, data || {}));
      }
    } catch (err) {
      /* analytics must never break the page */
    }
  }
  window.showcaseTrackEvent = trackEvent;

  /* ─── MODAL ──────────────────────────────────────────── */
  var modal = document.getElementById("optin-modal");
  var lastFocusedEl = null;

  function getFocusableEls() {
    if (!modal) return [];
    return Array.prototype.slice.call(
      modal.querySelectorAll(
        'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
      )
    );
  }

  function openModal(triggerEl) {
    if (!modal) return;
    lastFocusedEl = triggerEl || document.activeElement;
    modal.classList.add("open");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";

    var focusable = getFocusableEls();
    if (focusable.length) focusable[0].focus();

    trackEvent("showcase_modal_open", {});
  }

  function closeModal() {
    if (!modal) return;
    modal.classList.remove("open");
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    if (lastFocusedEl && typeof lastFocusedEl.focus === "function") {
      lastFocusedEl.focus();
    }
  }

  function trapFocus(e) {
    if (!modal || !modal.classList.contains("open")) return;
    if (e.key !== "Tab") return;

    var focusable = getFocusableEls();
    if (!focusable.length) return;

    var first = focusable[0];
    var last = focusable[focusable.length - 1];

    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }

  document.querySelectorAll("[data-modal-open]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      trackEvent("showcase_cta_click", { cta_id: btn.id || btn.textContent.trim() });
      openModal(btn);
    });
  });

  document.querySelectorAll("[data-modal-close]").forEach(function (btn) {
    btn.addEventListener("click", closeModal);
  });

  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === modal) closeModal();
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && modal && modal.classList.contains("open")) {
      closeModal();
    }
    trapFocus(e);
  });

  /* ─── COPY TO CLIPBOARD (coupon code) ────────────────── */
  document.querySelectorAll("[data-copy-target]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var targetId = btn.getAttribute("data-copy-target");
      var targetEl = document.getElementById(targetId);
      if (!targetEl) return;

      var text = targetEl.textContent.trim();
      var originalLabel = btn.textContent;

      function showCopied() {
        btn.textContent = "Code copied";
        btn.classList.add("copied");
        trackEvent("showcase_coupon_copy", {});
        setTimeout(function () {
          btn.textContent = originalLabel;
          btn.classList.remove("copied");
        }, 2200);
      }

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(showCopied);
      } else {
        var tmp = document.createElement("textarea");
        tmp.value = text;
        tmp.style.position = "fixed";
        tmp.style.opacity = "0";
        document.body.appendChild(tmp);
        tmp.select();
        document.execCommand("copy");
        document.body.removeChild(tmp);
        showCopied();
      }
    });
  });

  /* ─── GENERIC CTA TRACKING ───────────────────────────── */
  document.querySelectorAll("[data-track]").forEach(function (el) {
    el.addEventListener("click", function () {
      trackEvent(el.getAttribute("data-track"), {});
    });
  });
})();
