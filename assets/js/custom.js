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
