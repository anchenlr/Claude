/**
 * AI Systems Showcase — shared behaviour.
 * Modal (accessible, focus-trapped), opt-in form submission, coupon
 * copy-to-clipboard, and a minimal analytics shim.
 *
 * Form submission security note: this deliberately POSTs to
 * SHOWCASE_CONFIG.formEndpoint (a same-origin serverless function or
 * webhook) and never talks to ActiveCampaign's REST API from the
 * browser. Do not add an AC API key to this file or to any HTML page.
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

  /* ─── OPT-IN FORM ────────────────────────────────────── */
  var form = document.getElementById("optin-form");

  function setFieldError(input, message) {
    var errorEl = document.getElementById(input.id + "-error");
    if (message) {
      input.setAttribute("aria-invalid", "true");
      if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.add("visible");
      }
    } else {
      input.removeAttribute("aria-invalid");
      if (errorEl) {
        errorEl.textContent = "";
        errorEl.classList.remove("visible");
      }
    }
  }

  function validateForm(nameInput, emailInput) {
    var valid = true;

    if (!nameInput.value.trim()) {
      setFieldError(nameInput, "First name is required.");
      valid = false;
    } else {
      setFieldError(nameInput, "");
    }

    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailInput.value.trim()) {
      setFieldError(emailInput, "Email address is required.");
      valid = false;
    } else if (!emailPattern.test(emailInput.value.trim())) {
      setFieldError(emailInput, "Enter a valid email address.");
      valid = false;
    } else {
      setFieldError(emailInput, "");
    }

    return valid;
  }

  if (form) {
    var nameInput = document.getElementById("optin-name");
    var emailInput = document.getElementById("optin-email");
    var honeypot = document.getElementById("optin-company");
    var submitBtn = document.getElementById("optin-submit");
    var statusEl = document.getElementById("optin-status");

    // This form's action posts directly to an ActiveCampaign hosted form
    // (see the form's action attribute / SHOWCASE_CONFIG.formEndpoint) —
    // a plain browser POST, not a fetch/JSON call. ActiveCampaign handles
    // creating/tagging the contact and redirecting to the thank-you page
    // itself (configured inside that form's own settings in AC), so there
    // is no success/error response for this script to react to here.
    form.addEventListener("submit", function (e) {
      if (!validateForm(nameInput, emailInput)) {
        e.preventDefault();
        statusEl.textContent = "Please fix the highlighted fields and try again.";
        statusEl.className = "form-status error visible";
        return;
      }

      // Honeypot: if a bot filled the hidden field, silently drop the
      // submission instead of letting it reach ActiveCampaign.
      if (honeypot && honeypot.value) {
        e.preventDefault();
        return;
      }

      // Best-effort UTM capture: only actually reaches ActiveCampaign if
      // this form has matching custom fields configured for them there.
      // Harmless (ignored by AC) if it doesn't.
      var params = new URLSearchParams(window.location.search);
      ["utm_source", "utm_medium", "utm_campaign", "utm_content", "utm_term"].forEach(function (key) {
        if (!params.has(key)) return;
        var hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = key;
        hidden.value = params.get(key);
        form.appendChild(hidden);
      });

      trackEvent("showcase_form_submit", {});
      submitBtn.disabled = true;
      submitBtn.textContent = "Sending…";
      // No preventDefault: let the browser submit natively to ActiveCampaign.
    });
  }

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
