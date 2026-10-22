document.addEventListener("DOMContentLoaded", function () {
  const wowElements = document.querySelectorAll(".wow");

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.style.visibility = "visible";
          entry.target.style.animationName = "";
          const delay = entry.target.getAttribute("data-wow-delay");
          const duration = entry.target.getAttribute("data-wow-duration");
          if (delay) entry.target.style.animationDelay = delay;
          if (duration) entry.target.style.animationDuration = duration;

          entry.target.classList.add("animated");
        } else {
          entry.target.classList.remove("animated");
          entry.target.style.animationName = "none";
          entry.target.style.visibility = "hidden";
        }
      });
    },
    {
      threshold: 0.1,
    },
  );

  wowElements.forEach((el) => {
    el.style.visibility = "hidden";
    observer.observe(el);
  });
});

function setActiveMenu() {
  // 1. Get clean current page filename in lowercase (handles http, https, and file:///)
  let path = window.location.pathname || "";
  let currentPage = path.substring(path.lastIndexOf("/") + 1).toLowerCase();
  currentPage = currentPage.split("?")[0].split("#")[0];

  // Helper to normalize filenames: strip extension .php, .html, .htm
  function normalizePage(name) {
    if (!name) return "";
    let clean = name.toLowerCase().split("?")[0].split("#")[0];
    let file = clean.substring(clean.lastIndexOf("/") + 1);
    return file.replace(/\.(php|html|htm)$/, "");
  }

  // Default to index if root, empty or index variations
  let currentBase = normalizePage(currentPage);
  if (!currentBase || currentBase === "index") {
    currentBase = "index";
  }

  // 2. Map all 10 consultancy sub-service pages to consultancy-service
  const consultancySubPages = [
    "consultancy-service",
    "business-health-check",
    "profit-improvement",
    "manufacturing-process-development",
    "domestic-market-development",
    "export-market-development",
    "sales-marketing-development",
    "product-development-pricing",
    "business-process-systems",
    "business-transformation-kpi",
    "strategic-business-advisory",
  ];

  let targetBase = currentBase;
  if (consultancySubPages.includes(currentBase)) {
    targetBase = "consultancy-service";
  }

  // 3. Target all navigation lists (Desktop bottom nav, Sticky nav, Mobile source, Meanmenu drawer)
  const navContainerSelectors = [
    ".ib-bottom-nav .mainmenu",
    ".header-sticky .menu-area .mainmenu",
    "#mobile-menu",
    ".mobile_menu",
    ".hamburger_menu",
    ".mean-container .mean-nav",
  ];

  navContainerSelectors.forEach((selector) => {
    const containers = document.querySelectorAll(selector);
    containers.forEach((container) => {
      const items = container.querySelectorAll("li");
      if (!items || items.length === 0) return;

      // Remove previous active classes in this container
      items.forEach((li) => {
        li.classList.remove("current-menu-ancestor", "current-menu-item", "active");
        const a = li.querySelector(":scope > a");
        if (a) a.classList.remove("active");
      });

      let matched = false;

      // Try matching filename base
      items.forEach((li) => {
        const link = li.querySelector(":scope > a");
        if (!link) return;

        const href = (link.getAttribute("href") || "").trim();
        const linkBase = normalizePage(href);

        if (linkBase === targetBase) {
          li.classList.add("current-menu-ancestor", "current-menu-item", "active");
          link.classList.add("active");
          matched = true;
        }
      });

      // Fallback for home page if not matched by filename
      if (!matched && (targetBase === "index" || targetBase === "")) {
        items.forEach((li) => {
          const link = li.querySelector(":scope > a");
          if (!link) return;
          const href = (link.getAttribute("href") || "").trim().toLowerCase();
          const base = normalizePage(href);
          if (base === "index" || href === "./" || href === "/" || href === "") {
            li.classList.add("current-menu-ancestor", "current-menu-item", "active");
            link.classList.add("active");
          }
        });
      }
    });
  });
}

// Global execution hooks across lifecycle
document.addEventListener("DOMContentLoaded", setActiveMenu);
window.addEventListener("load", setActiveMenu);
setActiveMenu();

// Retries at intervals to ensure dynamic AJAX nav and meanmenu cloning are handled
[50, 150, 300, 600, 1000, 1500, 2500].forEach((delay) => {
  setTimeout(setActiveMenu, delay);
});

// React whenever jQuery AJAX completes
if (window.jQuery) {
  $(document).ajaxComplete(function (event, xhr, settings) {
    if (
      settings &&
      settings.url &&
      (settings.url.indexOf("nav.html") !== -1 ||
        settings.url.indexOf("meanmenu") !== -1 ||
        settings.url.indexOf("main.js") !== -1 ||
        settings.url.indexOf("footer.html") !== -1)
    ) {
      setActiveMenu();
      setTimeout(setActiveMenu, 50);
      setTimeout(setActiveMenu, 200);
      setTimeout(setActiveMenu, 500);
    }
  });
}

// MutationObserver for dynamic injection in #nav-placeholder and mobile menu
(function setupNavObserver() {
  function observeNode(node) {
    if (!node || !window.MutationObserver) return;
    const observer = new MutationObserver(function () {
      setActiveMenu();
    });
    observer.observe(node, { childList: true, subtree: true });
  }

  function init() {
    const navPlaceholder = document.getElementById("nav-placeholder");
    if (navPlaceholder) observeNode(navPlaceholder);

    const hamburgerArea = document.querySelector(".hamburger-area");
    if (hamburgerArea) observeNode(hamburgerArea);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();

/* ==========================================================================
   FAQ Accordion Active State Synchronization
   ========================================================================== */
(function setupAccordionActiveSync() {
  function syncAccordionItems() {
    document.querySelectorAll(".accordion .accordion-item").forEach(function (item) {
      var collapse = item.querySelector(".collapse, .collapsing");
      var btn = item.querySelector(".faq-title, [data-bs-toggle='collapse']");
      var isOpen = (collapse && (collapse.classList.contains("show") || collapse.classList.contains("collapsing")))
                   || (btn && btn.getAttribute("aria-expanded") === "true")
                   || (btn && !btn.classList.contains("collapsed"));
      if (isOpen) {
        item.classList.add("active");
      } else {
        item.classList.remove("active");
      }
    });
  }

  // 1. Initial sync on DOM ready and window load
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", syncAccordionItems);
  } else {
    syncAccordionItems();
  }
  window.addEventListener("load", syncAccordionItems);

  // 2. Native Capture Phase Listeners (Catches all Bootstrap 5 events globally)
  document.addEventListener("show.bs.collapse", function (e) {
    var item = e.target.closest(".accordion-item");
    if (!item) return;
    item.classList.add("active");
    if (item.parentElement) {
      item.parentElement.querySelectorAll(".accordion-item").forEach(function (sib) {
        if (sib !== item) sib.classList.remove("active");
      });
    }
  }, true);

  document.addEventListener("hide.bs.collapse", function (e) {
    var item = e.target.closest(".accordion-item");
    if (item) {
      item.classList.remove("active");
    }
  }, true);

  // 3. User Click Delegation for Instant Response
  document.addEventListener("click", function (e) {
    var btn = e.target.closest(".faq-title, [data-bs-toggle='collapse']");
    if (!btn) return;
    var item = btn.closest(".accordion-item");
    if (!item) return;

    // Direct toggle on click
    setTimeout(function () {
      var collapse = item.querySelector(".collapse, .collapsing");
      var isExpanded = (collapse && (collapse.classList.contains("show") || collapse.classList.contains("collapsing")))
                       || btn.getAttribute("aria-expanded") === "true"
                       || !btn.classList.contains("collapsed");
      if (isExpanded) {
        item.classList.add("active");
        if (item.parentElement) {
          item.parentElement.querySelectorAll(".accordion-item").forEach(function (sib) {
            if (sib !== item) sib.classList.remove("active");
          });
        }
      } else {
        item.classList.remove("active");
      }
    }, 20);
  });
})();


/* ==========================================================================
   16 Core Areas: Modern Tabbed Interactive Deck Switcher
   ========================================================================== */
function initDeckSwitcher() {
  const tabs = document.querySelectorAll(".ib-deck-tab");
  const panels = document.querySelectorAll(".ib-deck-panel");

  if (!tabs.length || !panels.length) return;

  tabs.forEach(function (tab) {
    tab.addEventListener("click", function () {
      const targetId = this.getAttribute("data-deck-target");
      if (!targetId) return;

      // Update active tab
      tabs.forEach(function (t) { t.classList.remove("active"); });
      this.classList.add("active");

      // Update active panel
      panels.forEach(function (panel) {
        if (panel.id === targetId) {
          panel.classList.add("active");
        } else {
          panel.classList.remove("active");
        }
      });
    });
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initDeckSwitcher);
} else {
  initDeckSwitcher();
}

$(document).ready(function () {
  initDeckSwitcher();
});

/* ==========================================================================
   10 Practical Business Workshops: Accordion Toggle & Category Filtering
   ========================================================================== */
function initWorkshopAccordion() {
  const container = document.querySelector(".ib-workshops-section");
  if (!container) return;

  const drawers = container.querySelectorAll(".ib-workshop-drawer");
  const filterPills = container.querySelectorAll(".ib-filter-pill");

  if (!drawers.length) return;

  // Drawer click toggle
  drawers.forEach(function (drawer) {
    const header = drawer.querySelector(".ib-drawer-header");
    if (!header || header.dataset.bound) return;
    header.dataset.bound = "true";

    function toggleDrawer() {
      const isAlreadyActive = drawer.classList.contains("active");

      // Close all drawers for a clean, single-expanded executive view
      drawers.forEach(function (d) {
        d.classList.remove("active");
      });

      // If it wasn't already active, open it
      if (!isAlreadyActive) {
        drawer.classList.add("active");
      }
    }

    header.addEventListener("click", toggleDrawer);

    // Keyboard navigation (Enter or Space)
    header.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        toggleDrawer();
      }
    });
  });

  // Filter pills
  filterPills.forEach(function (pill) {
    if (pill.dataset.bound) return;
    pill.dataset.bound = "true";

    pill.addEventListener("click", function () {
      const filterValue = this.getAttribute("data-filter") || "all";

      // Update active pill
      filterPills.forEach(function (p) {
        p.classList.remove("active");
      });
      this.classList.add("active");

      let firstVisibleDrawer = null;
      let hasActiveVisible = false;

      drawers.forEach(function (drawer) {
        const category = drawer.getAttribute("data-category") || "";
        const matches = filterValue === "all" || category === filterValue;

        if (matches) {
          drawer.style.display = "";
          if (!firstVisibleDrawer) firstVisibleDrawer = drawer;
          if (drawer.classList.contains("active")) {
            hasActiveVisible = true;
          }
        } else {
          drawer.style.display = "none";
          drawer.classList.remove("active");
        }
      });

      // If the currently open workshop was filtered out, open the first visible one
      if (!hasActiveVisible && firstVisibleDrawer) {
        firstVisibleDrawer.classList.add("active");
      }
    });
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initWorkshopAccordion);
} else {
  initWorkshopAccordion();
}

window.addEventListener("load", initWorkshopAccordion);

$(document).ready(function () {
  initWorkshopAccordion();
});

/* ==========================================================================
   Global Floating WhatsApp Button Initializer (Bottom Left - All Pages)
   ========================================================================== */
function ensureFloatingWhatsApp() {
  if (document.querySelector(".ib-floating-whatsapp")) return;

  const waBtn = document.createElement("a");
  waBtn.href = "https://wa.me/94742112767?text=Hello%20IB%20Consultants%2C%0A%0AI%20am%20interested%20in%20learning%20more%20about%20your%20consultancy%20services%20and%20would%20appreciate%20further%20information%20on%20how%20you%20could%20support%20my%20business%20in%20achieving%20its%20goals%20and%20improving%20overall%20performance.%0A%0AThank%20you.";
  waBtn.target = "_blank";
  waBtn.rel = "noopener noreferrer";
  waBtn.className = "ib-floating-whatsapp";
  waBtn.setAttribute("aria-label", "Chat with IB Consultants on WhatsApp Business");
  waBtn.innerHTML = '<span class="ib-wa-pulse"></span>' +
    '<svg class="ib-wa-icon" viewBox="0 0 175.216 175.552" width="32" height="32" aria-hidden="true" focusable="false">' +
    '<path fill="currentColor" fill-rule="evenodd" d="M12.966 161.238l10.439-38.114a73.42 73.42 0 0 1-9.821-36.772c.017-40.556 33.021-73.55 73.578-73.55 19.681.01 38.154 7.669 52.047 21.572s21.537 32.383 21.53 52.037c-.018 40.553-33.027 73.553-73.578 73.553h-.032c-12.313-.005-24.412-3.094-35.159-8.954zM87.184 25.227c-33.733 0-61.166 27.423-61.178 61.13a60.98 60.98 0 0 0 9.349 32.535l1.455 2.313-6.179 22.558 23.146-6.069 2.235 1.324c9.387 5.571 20.15 8.517 31.126 8.523h.023c33.707 0 61.14-27.426 61.153-61.135a60.75 60.75 0 0 0-17.895-43.251 60.75 60.75 0 0 0-43.235-17.928z"/>' +
    '<path fill="currentColor" fill-rule="evenodd" d="M67 52 L98 52 C106.5 52 113 55.5 116.5 61 C119.5 66 119.5 73.5 116 79 C114 82.5 111 85.5 107 87.5 C112 89.5 116.5 93.5 119 99 C121.5 105 121.5 112.5 118 118 C114 123.5 107.5 126 98 126 L67 126 C64 126 62.5 124.5 62.5 121.5 L62.5 56.5 C62.5 53.5 64 52 67 52 Z M75.5 63.5 L96.5 63.5 C102 63.5 105.5 67 105.5 72.5 C105.5 78 102 81.5 96.5 81.5 L75.5 81.5 Z M75.5 94.5 L97.5 94.5 C103 94.5 107 98.5 107 104 C107 109.5 103 113.5 97.5 113.5 L75.5 113.5 Z"/>' +
    '</svg>' +
    '<span class="ib-wa-tooltip">Chat with us</span>';

  document.body.appendChild(waBtn);
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", ensureFloatingWhatsApp);
} else {
  ensureFloatingWhatsApp();
}

window.addEventListener("load", ensureFloatingWhatsApp);

$(document).ready(function () {
  ensureFloatingWhatsApp();
});