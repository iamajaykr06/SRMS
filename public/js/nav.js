// script.js
function loadNavbar() {
    fetch('views/navbar.html')
        .then(response => response.text())
        .then(data => {
            document.getElementById('navbar-placeholder').innerHTML = data;

            // Initialize navbar functionality after loading
            initializeNavbar();
        });
}

function initializeNavbar() {
    // Get current page from URL
    const currentPage = window.location.pathname.split('/').pop() || 'index';
    const navLinks = document.querySelectorAll('nav a');
    
    // Set active state for current page
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        const pageName = href.split('/').pop() || 'index';
        
        // Remove all active states
        link.classList.remove('bg-yellow-400/20', 'text-yellow-300');
        const overlay = link.querySelector('.absolute');
        if (overlay) {
            overlay.classList.remove('bg-yellow-400/20');
        }
        
        // Add active state to current page
        if (pageName === currentPage) {
            link.classList.add('bg-yellow-400/20', 'text-yellow-300');
            const overlay = link.querySelector('.absolute');
            if (overlay) {
                overlay.classList.add('bg-yellow-400/20');
            }
        }
    });

    // Mobile menu functionality
    const btn = document.getElementById("menuBtn");
    const menu = document.getElementById("mobileMenu");
    let isOpen = false;
    
    if (btn && menu) {
        btn.addEventListener("click", () => {
            isOpen = !isOpen;
            
            // Animate hamburger menu
            if (isOpen) {
                btn.classList.add('open');
                menu.classList.remove('hidden');
                menu.classList.add('animate-fadeIn');
            } else {
                btn.classList.remove('open');
                menu.classList.add('hidden');
                menu.classList.remove('animate-fadeIn');
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener("click", (e) => {
            if (isOpen && !btn.contains(e.target) && !menu.contains(e.target)) {
                isOpen = false;
                btn.classList.remove('open');
                menu.classList.add('hidden');
                menu.classList.remove('animate-fadeIn');
            }
        });
    }

    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        .animate-fadeIn {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        #menuBtn.open span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        
        #menuBtn.open span:nth-child(2) {
            opacity: 0;
        }
        
        #menuBtn.open span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }
    `;
    document.head.appendChild(style);
}

// Run the function when the page loads
window.onload = loadNavbar;