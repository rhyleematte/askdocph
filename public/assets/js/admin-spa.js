document.addEventListener('DOMContentLoaded', () => {
    // Only run this script if we're in the admin panel
    if (!document.querySelector('.admin-badge')) return;

    const mainContent = document.querySelector('main.dash');
    if (!mainContent) return;

    // Helper: fetch and replace content
    async function loadPage(url) {
        try {
            // Show loading state (opacity fade)
            mainContent.style.opacity = '0.5';
            mainContent.style.pointerEvents = 'none';

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest', // Tell Laravel we expect partial if we ever configure it, but for now we'll parse the full HTML
                    'Accept': 'text/html'
                }
            });

            if (!response.ok) throw new Error('Network response was not ok');

            const html = await response.text();
            
            // Parse the returned HTML
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newMain = doc.querySelector('main.dash');

            if (newMain) {
                // Replace content
                mainContent.innerHTML = newMain.innerHTML;
                
                // Keep the class list (in case it changes)
                mainContent.className = newMain.className;

                // Update document title
                document.title = doc.title;

                // Re-initialize Lucide icons for new content
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                // Update active state in sidebar/dropdowns if needed
                updateActiveLinks(url);
            } else {
                // Fallback for non-dashboard pages (like login/logout)
                window.location.href = url;
            }
        } catch (error) {
            console.error('SPA Load Error:', error);
            // Fallback to normal navigation on error
            window.location.href = url;
        } finally {
            // Restore visual state
            mainContent.style.opacity = '1';
            mainContent.style.pointerEvents = 'auto';
        }
    }

    // Intercept clicks on internal links
    document.body.addEventListener('click', (e) => {
        // Find closest anchor tag
        const link = e.target.closest('a');
        
        // Conditions to ignore:
        // No link, modifier keys pressed, external link, download link, or explicitly ignored
        if (!link) return;
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
        if (link.hasAttribute('download')) return;
        if (link.target === '_blank') return;
        if (link.classList.contains('no-spa')) return;

        const url = link.href;
        
        // Only intercept same-origin routes starting with /admin
        if (url.startsWith(window.location.origin + '/admin')) {
            e.preventDefault();
            
            // Push state to browser history
            window.history.pushState({ url: url }, '', url);
            
            loadPage(url);
        }
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', (e) => {
        if (e.state && e.state.url) {
            loadPage(e.state.url);
        } else {
            // Fallback if no state
            loadPage(window.location.href);
        }
    });

    // Replace current state so the first page load is in history
    window.history.replaceState({ url: window.location.href }, '', window.location.href);

    // Minor helper to manage active states in menus if needed
    function updateActiveLinks(url) {
        document.querySelectorAll('a').forEach(link => {
            if (link.href === url) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }
});
