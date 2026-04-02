            </main>
        </div>
    </div>
    <script>
        (function() {
            var sidebar = document.getElementById('admin-sidebar');
            var overlay = document.getElementById('admin-sidebar-overlay');
            var toggle = document.getElementById('admin-sidebar-toggle');
            var closeBtn = document.getElementById('admin-sidebar-close');
            function openSidebar() {
                if (sidebar) sidebar.classList.remove('-translate-x-full');
                if (overlay) { overlay.classList.remove('opacity-0', 'pointer-events-none'); overlay.setAttribute('aria-hidden', 'false'); }
                document.body.style.overflow = 'hidden';
            }
            function closeSidebar() {
                if (sidebar) sidebar.classList.add('-translate-x-full');
                if (overlay) { overlay.classList.add('opacity-0', 'pointer-events-none'); overlay.setAttribute('aria-hidden', 'true'); }
                document.body.style.overflow = '';
            }
            if (toggle) toggle.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (overlay) overlay.addEventListener('click', closeSidebar);
            window.addEventListener('resize', function() { if (window.innerWidth >= 1024) closeSidebar(); });
        })();
    </script>
</body>
</html>
