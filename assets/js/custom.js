document.addEventListener("DOMContentLoaded", function() {
    const wowElements = document.querySelectorAll('.wow');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.visibility = 'visible';
                entry.target.style.animationName = ''; 
                const delay = entry.target.getAttribute('data-wow-delay');
                const duration = entry.target.getAttribute('data-wow-duration');
                if (delay) entry.target.style.animationDelay = delay;
                if (duration) entry.target.style.animationDuration = duration;

                entry.target.classList.add('animated');
            } else {
                entry.target.classList.remove('animated');
                entry.target.style.animationName = 'none'; 
                entry.target.style.visibility = 'hidden';  
            }
        });
    }, { 
        threshold: 0.1 
    });

    wowElements.forEach(el => {
        el.style.visibility = 'hidden'; 
        observer.observe(el);
    });
});


function setActiveMenu() {
    // දැනට ඉන්න පිටුවේ URL එක ගන්නවා
    let currentUrl = window.location.href.split('#')[0].split('?')[0]; 
    
    // Menu එකේ තියෙන links ඔක්කොම ගන්නවා
    let navLinks = document.querySelectorAll(".mainmenu ul li a");
    
    // මුලින්ම තියෙන active classes ඔක්කොම අයින් කරනවා
    document.querySelectorAll(".mainmenu ul li").forEach(li => {
        li.classList.remove("current-menu-ancestor", "active");
    });

    let isMatched = false;

    navLinks.forEach(link => {
        // බ්‍රව්සර් එකේ URL එකයි, link එකේ URL එකයි සමානද බලනවා
        if (link.href === currentUrl) {
            link.parentElement.classList.add("current-menu-ancestor");
            isMatched = true;
        }
    });

    // Home page (index.html) එක නමක් නැතුව (උදා: www.site.com/) load වුණොත්
    if (!isMatched && (currentUrl.endsWith('/') || currentUrl.endsWith('.com') || currentUrl.endsWith('.lk') || currentUrl.includes('localhost'))) {
        navLinks.forEach(link => {
            if (link.getAttribute("href") === "index.html") {
                link.parentElement.classList.add("current-menu-ancestor");
            }
        });
    }
}

// පිටුව load වුණාට පස්සේ කේතය run කරන්න
document.addEventListener("DOMContentLoaded", setActiveMenu);
// Nav bar එක වෙනම load වෙනවා නම් (dynamic) කෙලින්ම run වෙන්න
setTimeout(setActiveMenu, 10);