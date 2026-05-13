    <div id="announcement-modal" class="fixed inset-0 z-[120] hidden" role="dialog" aria-modal="true" aria-labelledby="announcement-modal-title" aria-hidden="true">
        <div class="absolute inset-0 bg-ink-900/65 backdrop-blur-sm" data-announcement-modal-close></div>
        <div class="fixed inset-0 p-3 sm:p-4 overflow-y-auto flex items-start sm:items-center justify-center">
            <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-cream-200 overflow-hidden my-4 sm:my-8">
                <div class="flex items-start justify-between gap-4 px-5 sm:px-6 py-4 border-b border-cream-200 bg-cream-50/80">
                    <h2 id="announcement-modal-title" class="text-lg sm:text-xl font-bold text-ink-800 leading-snug pr-2">Объявление</h2>
                    <button type="button" class="shrink-0 w-10 h-10 rounded-xl border border-cream-200 text-ink-500 hover:bg-cream-100 hover:text-ink-700 transition-colors" data-announcement-modal-close aria-label="Закрыть">
                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div id="announcement-modal-body" class="px-5 sm:px-6 py-5 sm:py-6 max-h-[min(72vh,720px)] overflow-y-auto text-ink-700"></div>
                <div id="announcement-modal-status" class="hidden px-5 sm:px-6 py-8 text-center text-ink-500" aria-live="polite">Загрузка…</div>
            </div>
        </div>
    </div>
