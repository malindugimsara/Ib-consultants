document.addEventListener("DOMContentLoaded", function() {
    // Main Service Cards (Consultancy, Training etc.)
    const serviceCards = document.querySelectorAll('.main-service-card');
    
    // Headers, Content Containers & Sub Services Main Container
    const dynamicHeaders = document.querySelectorAll('.dynamic-header');
    const dynamicContents = document.querySelectorAll('.dynamic-content');
    const subContainer = document.getElementById('sub-services-container');

    // 1. Main Card Click Event
    serviceCards.forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();

            // මුලින්ම Sub Services section එක show කිරීම
            subContainer.style.display = 'block';

            // Remove active states from all main cards
            serviceCards.forEach(c => {
                c.classList.remove('active-card');
                c.setAttribute('aria-expanded', 'false');
            });

            // Add active state to the clicked card
            this.classList.add('active-card');
            this.setAttribute('aria-expanded', 'true');

            // Get the target section (e.g., 'consultancy' or 'training')
            const target = this.getAttribute('data-target');

            // Hide all headers and show the target header
            dynamicHeaders.forEach(header => {
                if (header.id === `header-${target}`) {
                    header.style.display = 'block';
                    header.classList.add('active-header');
                } else {
                    header.style.display = 'none';
                    header.classList.remove('active-header');
                }
            });

            // Hide all content sections and show the target content
            dynamicContents.forEach(content => {
                if (content.id === `content-${target}`) {
                    content.style.display = 'block'; 
                    content.classList.add('active-content');
                } else {
                    content.style.display = 'none';
                    content.classList.remove('active-content');
                }
            });

            // ==========================================
            // FIX: DOM එක Update වීමට මිලි තත්පර 150ක් දී Scroll කිරීම
            // ==========================================
            setTimeout(() => {
                // 1. Animation library එක refresh කිරීම
                window.dispatchEvent(new Event('resize'));
                window.dispatchEvent(new Event('scroll'));

                // 2. නිවැරදිව Smooth Scroll කිරීම
                if (subContainer) {
                    const headerOffset = 80; 
                    const elementPosition = subContainer.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                    
                    window.scrollTo({
                         top: offsetPosition,
                         behavior: "smooth"
                    });
                }
            }, 150); 
            // ==========================================
        });
    });

    // 2. Hide Sub Services when clicking outside (Auto Hide Feature)
    document.addEventListener('click', function(e) {
        if (subContainer && subContainer.style.display === 'block') {
            
            const isClickInsideSubContainer = subContainer.contains(e.target);
            const isClickInsideMainCard = Array.from(serviceCards).some(card => card.contains(e.target));

            if (!isClickInsideSubContainer && !isClickInsideMainCard) {
                subContainer.style.display = 'none';
                
                serviceCards.forEach(c => {
                    c.classList.remove('active-card');
                    c.setAttribute('aria-expanded', 'false');
                });
            }
        }
    });
});