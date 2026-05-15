<?php
/**
 * Антикоррупционный комплекс — официальные документы
 */
$pageTitle = 'Антикоррупционный комплекс — ' . SITE_NAME;
$metaDescription = 'Официальные документы антикоррупционного комплекса Карагандинского высшего колледжа инжиниринга.';
$breadcrumbTitles = [['title' => 'Главная', 'slug' => ''], ['title' => 'Антикоррупционный комплекс', 'slug' => 'antikorruptsionnyy-kompleks']];

$documents = [];
if ($db = Database::tryGetInstance()) {
    try {
        $rows = $db->query('SELECT title, file_path FROM anticor_documents WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
        foreach ($rows as $row) {
            $filePath = trim((string)($row['file_path'] ?? ''));
            if ($filePath === '') {
                continue;
            }
            $url = str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')
                ? $filePath
                : BASE_URL . '/' . ltrim(str_replace('\\', '/', $filePath), '/');
            $documents[] = [
                'title' => (string)($row['title'] ?? ''),
                'url' => $url,
            ];
        }
    } catch (PDOException $e) {
    }
}

require ROOT_PATH . '/templates/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
    .doc-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .doc-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.12);
        border-color: #10b981;
    }
    .pdf-icon { transition: all 0.3s ease; }
    .doc-card:hover .pdf-icon {
        transform: scale(1.12) rotate(12deg);
    }
    .modal {
        animation: modalFade 0.3s ease forwards;
    }
    @keyframes modalFade {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<main class="flex-1">
    <div class="relative overflow-hidden rounded-2xl bg-white border border-cream-200 shadow-[0_10px_24px_rgba(15,23,42,0.08)] p-8 lg:p-10 mb-10 mx-4 lg:mx-auto max-w-7xl mt-8 lg:mt-10">
        <div class="relative z-10">
            <h1 class="text-3xl lg:text-4xl font-extrabold text-ink-800 tracking-tight mb-3">
                Антикоррупционный комплекс
            </h1>
            <p class="text-ink-600 text-lg max-w-2xl leading-relaxed">
                Система противодействия коррупции и обеспечения комплаенс-стандартов в <strong>Карагандинский высший колледж инжиниринга</strong>.
            </p>
        </div>
        <div class="absolute top-0 right-0 w-72 h-72 bg-sage-400/5 rounded-full -translate-y-1/2 translate-x-1/2" aria-hidden="true"></div>
    </div>

    <section class="max-w-7xl mx-auto px-4 lg:px-8 pb-16">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-ink-800">Официальные документы</h2>
                <p class="text-ink-600 mt-1">Просмотр и скачивание</p>
            </div>
            <input type="text" id="searchInput"
                   class="w-full sm:w-80 px-5 py-4 rounded-2xl border border-cream-200 focus:border-emerald-300 focus:outline-none text-base"
                   placeholder="Поиск по названию...">
        </div>

        <ul id="docList" class="space-y-6">
            <?php if (empty($documents)): ?>
            <li class="rounded-2xl border border-dashed border-cream-200 bg-white px-6 py-16 text-center text-ink-600">
                Документы скоро появятся.
            </li>
            <?php else: ?>
                <?php foreach ($documents as $document): ?>
                <li class="doc-card bg-white border border-cream-200 rounded-3xl p-6 lg:p-8 flex flex-col lg:flex-row gap-6 lg:items-center group">
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center flex-shrink-0 pdf-icon">
                        <i class="fa-solid fa-file-pdf text-3xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xl font-semibold text-ink-800 leading-tight group-hover:text-emerald-700 transition-colors">
                            <?= htmlspecialchars($document['title']) ?>
                        </h3>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 lg:w-auto w-full">
                        <button type="button" data-preview-url="<?= htmlspecialchars($document['url'], ENT_QUOTES) ?>" class="btn flex-1 lg:flex-none px-8 py-5 rounded-2xl border border-cream-300 hover:border-emerald-400 hover:text-emerald-700 font-medium flex items-center justify-center gap-2">
                            <i class="fa-solid fa-eye"></i><span>Просмотр</span>
                        </button>
                        <a href="<?= htmlspecialchars($document['url']) ?>" download class="btn flex-1 lg:flex-none px-8 py-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-semibold flex items-center justify-center gap-2">
                            <i class="fa-solid fa-download"></i><span>СКАЧАТЬ</span>
                        </a>
                    </div>
                </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <p class="text-center text-ink-500 text-sm mt-12">
            Все документы актуальны на 2026 год и размещены в соответствии с антикоррупционным законодательством Республики Казахстан
        </p>
    </section>
</main>

<div id="previewModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70">
    <div class="modal bg-white w-full max-w-5xl mx-4 rounded-3xl shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between border-b px-6 py-5">
            <h3 id="modalTitle" class="font-semibold text-lg text-ink-800">Просмотр документа</h3>
            <button type="button" id="closePreviewBtn" class="text-3xl text-ink-400 hover:text-ink-600 transition-colors" aria-label="Закрыть">×</button>
        </div>
        <div class="relative bg-zinc-100" style="height: 82vh;">
            <iframe id="pdfIframe" class="w-full h-full" title="Просмотр документа" frameborder="0"></iframe>
        </div>
        <div class="flex items-center justify-end gap-4 px-6 py-4 border-t bg-white">
            <button type="button" id="closePreviewFooterBtn" class="px-8 py-3 text-ink-600 hover:bg-zinc-100 rounded-2xl font-medium">Закрыть</button>
            <a id="modalDownloadBtn" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-semibold flex items-center gap-2">
                <i class="fa-solid fa-download"></i> Скачать
            </a>
        </div>
    </div>
</div>

<script>
function openPreview(url) {
    document.getElementById('pdfIframe').src = url;
    document.getElementById('modalDownloadBtn').href = url;
    const modal = document.getElementById('previewModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePreview() {
    const modal = document.getElementById('previewModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('pdfIframe').src = '';
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-preview-url]').forEach(function (button) {
        button.addEventListener('click', function () {
            openPreview(button.getAttribute('data-preview-url') || '');
        });
    });

    document.getElementById('closePreviewBtn').addEventListener('click', closePreview);
    document.getElementById('closePreviewFooterBtn').addEventListener('click', closePreview);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closePreview();
        }
    });

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const term = this.value.toLowerCase();
            document.querySelectorAll('#docList li.doc-card').forEach(function (card) {
                const title = card.querySelector('h3').textContent.toLowerCase();
                card.style.display = title.includes(term) ? '' : 'none';
            });
        });
    }
});
</script>

<?php require ROOT_PATH . '/templates/footer.php'; ?>
