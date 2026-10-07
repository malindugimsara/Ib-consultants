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