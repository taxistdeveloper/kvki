(function () {
    const modal = document.getElementById('announcement-modal');
    if (!modal) return;

    const titleEl = document.getElementById('announcement-modal-title');
    const bodyEl = document.getElementById('announcement-modal-body');
    const statusEl = document.getElementById('announcement-modal-status');
    const closeSelectors = '[data-announcement-modal-close]';
    const baseUrl = (window.BASE_URL || '').replace(/\/$/, '');
    const announcementsSlug = window.KVKI_ANNOUNCEMENTS_SLUG || 'obyavleniya';
    let lastFocus = null;

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatMultiline(value) {
        return escapeHtml(value).replace(/\r?\n/g, '<br>');
    }

    function setLoading(isLoading) {
        if (!statusEl || !bodyEl) return;
        statusEl.classList.toggle('hidden', !isLoading);
        bodyEl.classList.toggle('hidden', isLoading);
        if (isLoading) {
            statusEl.textContent = 'Загрузка…';
        }
    }

    function openModal(title) {
        lastFocus = document.activeElement;
        if (titleEl) {
            titleEl.textContent = title || 'Объявление';
        }
        if (bodyEl) {
            bodyEl.innerHTML = '';
            bodyEl.classList.remove('hidden');
        }
        setLoading(false);
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        modal.querySelector(closeSelectors + ', button')?.focus();
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (bodyEl) {
            bodyEl.innerHTML = '';
        }
        if (lastFocus && typeof lastFocus.focus === 'function') {
            lastFocus.focus();
        }
    }

    function isExternalUrl(url) {
        return /^https?:\/\//i.test(url) && !url.startsWith(window.location.origin);
    }

    function isInternalUrl(url) {
        if (!url || url.startsWith('#') || url.startsWith('javascript:')) return false;
        if (isExternalUrl(url)) return false;
        if (url.startsWith('mailto:') || url.startsWith('tel:')) return false;
        try {
            const parsed = new URL(url, window.location.origin);
            return parsed.origin === window.location.origin;
        } catch (error) {
            return false;
        }
    }

    function renderAnnouncementCard(data) {
        const views = data.views ? '<span class="text-sm text-ink-500">' + escapeHtml(data.views) + ' просмотров</span>' : '';
        const important = data.important === '1' || data.important === 'true'
            ? '<span class="inline-flex px-2 py-0.5 text-[11px] font-semibold rounded-full bg-sage-600 text-white">Важно</span>'
            : '';
        const excerpt = data.excerpt
            ? '<div class="prose prose-lg max-w-none text-ink-600">' + formatMultiline(data.excerpt) + '</div>'
            : '';

        return '<article class="announcement-single">'
            + '<div class="flex flex-wrap items-center gap-4 mb-4">'
            + (data.date ? '<time class="text-sm text-sage-600 font-medium">' + escapeHtml(data.date) + '</time>' : '')
            + views
            + '</div>'
            + (important ? '<div class="mb-3">' + important + '</div>' : '')
            + (data.title ? '<h3 class="text-2xl font-bold text-ink-800 mb-4">' + escapeHtml(data.title) + '</h3>' : '')
            + excerpt
            + '</article>';
    }

    function extractPageContent(doc) {
        const selectors = [
            '.announcement-single',
            '.page-article-card .prose',
            'main .page-article-card .prose',
            'main .prose',
            'main'
        ];

        for (const selector of selectors) {
            const node = doc.querySelector(selector);
            if (node && node.innerHTML.trim()) {
                return node.innerHTML;
            }
        }

        return '';
    }

    async function loadPageContent(url, fallbackTitle) {
        setLoading(true);
        openModal(fallbackTitle || 'Объявление');

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const pageTitle = doc.querySelector('title')?.textContent?.trim() || 'Объявление';
            const content = extractPageContent(doc);

            if (!content) {
                throw new Error('empty');
            }

            if (titleEl) {
                titleEl.textContent = (fallbackTitle || pageTitle).replace(/\s+—\s+.*$/, '');
            }
            if (bodyEl) {
                bodyEl.innerHTML = content;
            }
            setLoading(false);
        } catch (error) {
            if (statusEl) {
                statusEl.textContent = 'Не удалось загрузить объявление. Попробуйте ещё раз.';
            }
            setLoading(true);
        }
    }

    function isAnnouncementsPath(url) {
        try {
            const path = new URL(url, window.location.origin).pathname.replace(/\/$/, '');
            const prefix = (baseUrl + '/' + announcementsSlug).replace(/\/$/, '');
            return path === prefix || path.startsWith(prefix + '/');
        } catch (error) {
            return false;
        }
    }

    function openFromTrigger(trigger) {
        const dataset = trigger.dataset;
        const href = trigger.getAttribute('href') || '';

        if (href && isInternalUrl(href) && !isAnnouncementsPath(href)) {
            loadPageContent(href, dataset.announcementTitle || undefined);
            return;
        }

        if (dataset.announcementTitle || dataset.announcementDate || dataset.announcementExcerpt) {
            openModal(dataset.announcementTitle || 'Объявление');
            if (bodyEl) {
                bodyEl.innerHTML = renderAnnouncementCard({
                    title: dataset.announcementTitle || '',
                    date: dataset.announcementDate || '',
                    excerpt: dataset.announcementExcerpt || '',
                    views: dataset.announcementViews || '',
                    important: dataset.announcementImportant || ''
                });
            }
            return;
        }

        if (href) {
            loadPageContent(href);
        }
    }

    function openAnnouncementsList(trigger) {
        const href = trigger.getAttribute('href') || (baseUrl + '/' + announcementsSlug);
        loadPageContent(href, 'Объявления');
    }

    modal.querySelectorAll(closeSelectors).forEach(function (button) {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function (event) {
        const link = event.target.closest('#announcement-modal-body a[href]');
        if (!link || link.hasAttribute('data-announcements-nav')) return;
        const href = link.getAttribute('href') || '';
        if (!isInternalUrl(href)) return;
        event.preventDefault();
        loadPageContent(href);
    });

    document.addEventListener('click', function (event) {
        const listTrigger = event.target.closest('[data-announcements-nav]');
        if (listTrigger) {
            event.preventDefault();
            openAnnouncementsList(listTrigger);
            return;
        }

        const trigger = event.target.closest('[data-announcement-modal]');
        if (!trigger) return;

        const href = trigger.getAttribute('href') || '';
        if (href && !isInternalUrl(href)) {
            return;
        }

        event.preventDefault();
        openFromTrigger(trigger);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
})();
