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

  // Default to index.html if root, empty or index variations
  if (
    !currentPage ||
    currentPage === "" ||
    currentPage === "index.htm" ||
    currentPage === "index.php"
  ) {
    currentPage = "index.html";
  }

  // 2. Map all 10 consultancy sub-service pages to consultancy-service.html
  const consultancySubPages = [
    "consultancy-service.html",
    "business-health-check.html",
    "profit-improvement.html",
    "manufacturing-process-development.html",
    "domestic-market-development.html",
    "export-market-development.html",
    "sales-marketing-development.html",
    "product-development-pricing.html",
    "business-process-systems.html",
    "business-transformation-kpi.html",
    "strategic-business-advisory.html",
  ];

  let targetPage = currentPage;
  if (consultancySubPages.includes(currentPage)) {
    targetPage = "consultancy-service.html";
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

      // Try matching filename
      items.forEach((li) => {
        const link = li.querySelector(":scope > a");
        if (!link) return;

        const href = (link.getAttribute("href") || "").trim();
        let linkFile = href.substring(href.lastIndexOf("/") + 1).toLowerCase();
        linkFile = linkFile.split("?")[0].split("#")[0];

        if (linkFile === targetPage) {
          li.classList.add("current-menu-ancestor", "current-menu-item", "active");
          link.classList.add("active");
          matched = true;
        }
      });

      // Fallback for home page if not matched by filename
      if (!matched && targetPage === "index.html") {
        items.forEach((li) => {
          const link = li.querySelector(":scope > a");
          if (!link) return;
          const href = (link.getAttribute("href") || "").trim().toLowerCase();
          if (href === "index.html" || href === "./" || href === "/") {
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

$(document).ready(function () {
  // FAQ එකක් Open වෙද්දි ඒකට active class එක දැමීම
  $(".accordion").on("show.bs.collapse", function (e) {
    $(e.target).closest(".accordion-item").addClass("active");
  });

  // FAQ එකක් Close වෙද්දි ඒකෙන් active class එක ඉවත් කිරීම
  $(".accordion").on("hide.bs.collapse", function (e) {
    $(e.target).closest(".accordion-item").removeClass("active");
  });
});

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
  waBtn.href = "https://wa.me/94742112767?text=Hello%20IB%20Consultants,%20I%20would%20like%20to%20inquire%20about%20your%20services.";
  waBtn.target = "_blank";
  waBtn.rel = "noopener noreferrer";
  waBtn.className = "ib-floating-whatsapp";
  waBtn.setAttribute("aria-label", "Chat with IB Consultants on WhatsApp");
  waBtn.innerHTML = '<span class="ib-wa-pulse"></span><i class="fa-brands fa-whatsapp"></i><span class="ib-wa-tooltip">Chat with us</span>';

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