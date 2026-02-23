// script.js
function loadNavbar() {
    fetch('navbar.html')
        .then(response => response.text())
        .then(data => {
            document.getElementById('navbar-placeholder').innerHTML = data;

            // Get current page name (e.g., "index.html")
            let currentPage = window.location.pathname.split("/").pop();

            // If the URL ends in "/" (homepage), set it to "index.html"
            if (currentPage === "") { currentPage = "index.html"; }

            // Find all links in the newly loaded navbar
            const links = document.querySelectorAll('.nav-link');

            links.forEach(link => {
                // Get the href attribute (e.g., "index.html")
                const linkHref = link.getAttribute('href');

                if (linkHref === currentPage) {
                    // Styles for the active page
                    link.classList.add('text-yellow-400', 'border-b-2', 'border-yellow-400', 'pb-1');
                    link.classList.remove('text-gray-400');
                }
            });
        });
}

// Run the function when the page loads
window.onload = loadNavbar;