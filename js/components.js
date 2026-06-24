/* ════════════════════════════════════════════════════════════
   Sween Travels, shared components & site behaviours
   Loads header.html / footer.html into every page (root or
   subfolder), fixes relative URLs, and wires up shared UI/forms.
════════════════════════════════════════════════════════════ */

// Compute the path prefix ("" at root, "../" inside Visa/ or
// Solutions/) from this script tag's own src attribute.
const SITE_PREFIX = (() => {
  const tag = document.querySelector('script[src*="components.js"]');
  if (!tag) return "";
  return tag.getAttribute("src").replace(/js\/components\.js.*$/, "");
})();

const isValidEmail = (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value).trim());

const setFormMessage = (element, message, show = true) => {
  if (!element) return;
  if (message) element.textContent = message;
  element.classList.toggle("hidden", !show);
};

const submitLeadForm = async (form, { successText, errorText }) => {
  const btn = form.querySelector('button[type="submit"]');
  const errorEl = form.querySelector("#form-error");
  const successEl = form.querySelector("#form-success");
  const originalText = btn ? btn.textContent : "";

  setFormMessage(errorEl, "", false);
  setFormMessage(successEl, "", false);

  if (!form.checkValidity()) {
    setFormMessage(errorEl, "Please complete all required fields correctly.");
    form.reportValidity();
    return;
  }

  const email = form.querySelector('[name="email"]')?.value || "";
  if (!isValidEmail(email)) {
    setFormMessage(errorEl, "Please enter a valid email address.");
    return;
  }

  if (btn) {
    btn.textContent = "Sending…";
    btn.disabled = true;
  }
  form.setAttribute("aria-busy", "true");

  try {
    const response = await fetch(SITE_PREFIX + "send-lead.php", {
      method: "POST",
      headers: { "Accept": "application/json" },
      body: new FormData(form),
    });

    const result = await response.json().catch(() => ({ ok: response.ok }));
    if (!response.ok || !result || result.ok === false) {
      throw new Error(result?.error || "send failed");
    }

    setFormMessage(successEl, successText || "Thank you! We'll be in touch shortly.");
    form.reset();
    setTimeout(() => setFormMessage(successEl, "", false), 6000);
  } catch (error) {
    setFormMessage(errorEl, errorText || "Sorry, something went wrong. Please email sweentravelslimited@gmail.com or call us directly.");
  } finally {
    if (btn) {
      btn.textContent = originalText;
      btn.disabled = false;
    }
    form.removeAttribute("aria-busy");
  }
};

document.addEventListener("DOMContentLoaded", async () => {

  // ─────────────────────────────────────────────
  // Component Loader
  // ─────────────────────────────────────────────
  const loadComponent = async (selector, file) => {
    const mount = document.querySelector(selector);
    if (!mount) return;
    try {
      const res = await fetch(SITE_PREFIX + file);
      if (!res.ok) throw new Error(res.status);
      mount.innerHTML = await res.text();
      fixRelativeUrls(mount);
    } catch (err) {
      console.error(`Could not load ${file}:`, err);
    }
  };

  // Prepend the prefix to relative href/src values inside
  // injected components so links work from subfolders too.
  const fixRelativeUrls = (root) => {
    if (!SITE_PREFIX) return;
    root.querySelectorAll("[href], [src]").forEach((el) => {
      ["href", "src"].forEach((attr) => {
        const v = el.getAttribute(attr);
        if (!v) return;
        if (/^(https?:|mailto:|tel:|#|\/|data:)/i.test(v)) return;
        el.setAttribute(attr, SITE_PREFIX + v);
      });
    });
  };

  await loadComponent("#header", "header.html");
  await loadComponent("#footer", "footer.html");

  // ─────────────────────────────────────────────
  // Active nav link (current page / section)
  // ─────────────────────────────────────────────
  const path = window.location.pathname;
  let current = path.split("/").pop() || "index.html";
  if (/\/Visa\//i.test(path)) current = "visa.html";
  if (/\/Solutions\//i.test(path)) current = "immigration.html";

  document.querySelectorAll("#header a.nav-link").forEach((a) => {
    const target = (a.getAttribute("href") || "").split("/").pop();
    if (target === current) a.classList.add("active");
  });

  // ─────────────────────────────────────────────
  // Sticky Navbar + responsive mobile menu
  // ─────────────────────────────────────────────
  const navbar = document.getElementById("navbar");

  if (navbar) {
    const mobileToggle = navbar.querySelector(".st-menu-toggle");
    const mobileMenu = navbar.querySelector("#mobile-menu");
    const toggleLabel = navbar.querySelector(".st-toggle-label");
    const closeTriggers = navbar.querySelectorAll("[data-mobile-menu-close]");
    const desktopQuery = window.matchMedia("(min-width: 1024px)");

    const setMobileMenu = (open) => {
      navbar.classList.toggle("menu-open", open);
      document.body.classList.toggle("st-menu-lock", open);

      if (mobileToggle) {
        mobileToggle.classList.toggle("is-open", open);
        mobileToggle.setAttribute("aria-expanded", String(open));
      }

      if (mobileMenu) {
        mobileMenu.setAttribute("aria-hidden", String(!open));
      }

      if (toggleLabel) {
        toggleLabel.textContent = open ? "Close menu" : "Open menu";
      }
    };

    const closeMobileMenu = () => setMobileMenu(false);

    if (mobileToggle && mobileMenu) {
      mobileToggle.addEventListener("click", () => {
        setMobileMenu(!navbar.classList.contains("menu-open"));
      });

      closeTriggers.forEach((trigger) => {
        trigger.addEventListener("click", closeMobileMenu);
      });

      mobileMenu.querySelectorAll("a").forEach((link) => {
        link.addEventListener("click", closeMobileMenu);
      });

      document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && navbar.classList.contains("menu-open")) {
          closeMobileMenu();
          mobileToggle.focus();
        }
      });

      if (desktopQuery.addEventListener) {
        desktopQuery.addEventListener("change", (event) => {
          if (event.matches) closeMobileMenu();
        });
      } else if (desktopQuery.addListener) {
        desktopQuery.addListener((event) => {
          if (event.matches) closeMobileMenu();
        });
      }
    }

    const onScroll = () => {
      if (window.scrollY > 40) {
        navbar.classList.add("scrolled");
      } else {
        navbar.classList.remove("scrolled");
      }
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
  }

  // ─────────────────────────────────────────────
  // Scroll Reveal Animation
  // ─────────────────────────────────────────────
  const revealEls = document.querySelectorAll(".reveal");

  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
          revealObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
  );

  revealEls.forEach((el) => revealObserver.observe(el));

  // Trigger reveal immediately for above-fold
  setTimeout(() => {
    document.querySelectorAll(".reveal").forEach((el) => {
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight) {
        el.classList.add("visible");
      }
    });
  }, 100);

  // ─────────────────────────────────────────────
  // Footer Dynamic Year
  // ─────────────────────────────────────────────
  const year = document.getElementById("year");
  if (year) {
    year.textContent = new Date().getFullYear();
  }

});

// ─────────────────────────────────────────────
// Newsletter subscribe → PHP lead handler
// ─────────────────────────────────────────────
function handleNewsletter(e) {
  e.preventDefault();
  const form = e.target;
  const btn = form.querySelector('button[type="submit"]');
  const originalText = btn ? btn.textContent : "Subscribe";

  if (!form.checkValidity()) {
    form.reportValidity();
    return false;
  }

  if (btn) {
    btn.textContent = "Sending…";
    btn.disabled = true;
  }

  fetch(SITE_PREFIX + "send-lead.php", {
    method: "POST",
    headers: { "Accept": "application/json" },
    body: new FormData(form),
  })
    .then((res) => res.json().catch(() => ({ ok: res.ok })))
    .then((result) => {
      if (!result || result.ok === false) throw new Error("send failed");
      if (btn) btn.textContent = "Subscribed ✓";
      form.reset();
    })
    .catch(() => {
      if (btn) btn.textContent = "Try again";
    })
    .finally(() => {
      setTimeout(() => {
        if (btn) {
          btn.textContent = originalText;
          btn.disabled = false;
        }
      }, 4000);
    });

  return false;
}

// ─────────────────────────────────────────────
// Visa detail enquiry forms → PHP lead handler
// ─────────────────────────────────────────────
function handleForm(e) {
  e.preventDefault();
  submitLeadForm(e.target, {
    successText: "✓ Thank you! We'll be in touch shortly.",
    errorText: "Sorry, we could not send your enquiry. Please email sweentravelslimited@gmail.com or call us directly.",
  });
  return false;
}

window.handleNewsletter = handleNewsletter;
window.handleForm = handleForm;
