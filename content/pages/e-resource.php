<?php
/**
 * e-Resource — единое окно входа в приложения КТСК College
 * Дизайн в стиле основного сайта (Tailwind, cream/sage/ink)
 */
$pageTitle = 'e-Resource — ' . SITE_NAME;
$metaDescription = 'Единое окно входа в приложения КТСК College: Besaspap, Hr, Inventory, ktsk и другие сервисы.';
$breadcrumbTitles = [['title' => 'Главная', 'slug' => ''], ['title' => 'e-Resource', 'slug' => 'e-resource']];

$apps = [];
if ($db = Database::tryGetInstance()) {
    try {
        $rows = $db->query('SELECT name, `description`, url, tag, status FROM e_resource_apps ORDER BY sort_order, id')->fetchAll();
        foreach ($rows as $r) {
            $apps[] = [
                'name' => $r['name'],
                'desc' => $r['description'] ?? '',
                'url' => ($r['url'] && $r['url'] !== '#') ? $r['url'] : null,
                'tag' => $r['tag'] ?: null,
                'status' => $r['status'] ?? 'active',
            ];
        }
    } catch (PDOException $e) {}
}
if (empty($apps)) {
    $apps = [
        ['name' => 'Besaspap.app', 'desc' => 'Система входа для Besasap', 'url' => 'http://besaspap.ktsk.kz/', 'tag' => '1.0', 'status' => 'active'],
        ['name' => 'Hr.app', 'desc' => 'Портал вакансий Hr', 'url' => 'https://enbek.ktsk.kz/', 'tag' => '1.0', 'status' => 'active'],
        ['name' => 'Students.app', 'desc' => 'Студенты', 'url' => null, 'tag' => 'В разработке', 'status' => 'dev'],
        ['name' => 'Inventory.app', 'desc' => 'Вход в инвентарь', 'url' => 'http://inventory.ktsk.kz/', 'tag' => '2.0', 'status' => 'active'],
        ['name' => 'Museum.app', 'desc' => 'Система для входа в Музей', 'url' => null, 'tag' => '2.0', 'status' => 'disabled'],
        ['name' => 'ktsk.app', 'desc' => 'Наш колледж ktsk', 'url' => 'https://college.ktsk.kz/', 'tag' => null, 'status' => 'active'],
        ['name' => 'cloud.app', 'desc' => 'Вход в облако архива', 'url' => null, 'tag' => 'В разработке', 'status' => 'dev'],
        ['name' => 'Soon', 'desc' => 'Скоро', 'url' => null, 'tag' => null, 'status' => 'dev'],
    ];
}

require ROOT_PATH . '/templates/header.php';
?>

<main class="flex-1">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20 e-resource-page">
        <!-- Заголовок -->
        <div class="mb-10 lg:mb-12">
            <h1 class="text-2xl sm:text-4xl font-extrabold text-ink-800 mb-2 sm:mb-3">e-Resource</h1>
            <p class="text-base sm:text-lg text-ink-600">Единое окно входа в приложения <strong class="text-sage-700">КТСК College!</strong></p>
        </div>

        <!-- Праздничное сообщение (скрыто по умолчанию) -->
        <section id="holiday-section" class="mb-10 lg:mb-12 p-5 sm:p-6 rounded-2xl bg-cream-200/50 border border-cream-200 text-center" style="display: none;">
            <h2 id="holiday-title" class="text-lg sm:text-xl font-bold text-sage-700 mb-2"></h2>
            <p id="holiday-subtitle" class="text-ink-600 text-sm sm:text-base mb-4"></p>
            <div class="flex flex-wrap justify-center gap-2">
                <button type="button" onclick="changeLanguage('ru')" class="min-h-[44px] px-5 py-2.5 rounded-xl text-sm font-medium bg-cream-50 border border-cream-200 hover:bg-sage-500/10 hover:border-sage-400 hover:text-sage-700 transition-colors active:scale-[0.98]">🇷🇺 Рус</button>
                <button type="button" onclick="changeLanguage('kk')" class="min-h-[44px] px-5 py-2.5 rounded-xl text-sm font-medium bg-cream-50 border border-cream-200 hover:bg-sage-500/10 hover:border-sage-400 hover:text-sage-700 transition-colors active:scale-[0.98]">🇰🇿 Қаз</button>
                <button type="button" onclick="changeLanguage('en')" class="min-h-[44px] px-5 py-2.5 rounded-xl text-sm font-medium bg-cream-50 border border-cream-200 hover:bg-sage-500/10 hover:border-sage-400 hover:text-sage-700 transition-colors active:scale-[0.98]">🇺🇸 Eng</button>
            </div>
        </section>

        <!-- Карточки приложений: 2 колонки на мобильных (как иконки приложений), 4 на десктопе -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 e-resource-grid">
            <?php foreach ($apps as $app): ?>
            <div class="e-resource-card p-4 sm:p-6 transition-all group flex flex-col">
                <div class="flex items-start justify-between gap-2 mb-1 sm:mb-2 min-h-[2.25rem]">
                    <h3 class="font-bold text-ink-800 text-sm sm:text-lg group-hover:text-sage-700 transition-colors leading-tight line-clamp-2"><?= htmlspecialchars($app['name']) ?></h3>
                    <?php if ($app['tag']): ?>
                    <span class="inline-flex px-1.5 py-0.5 text-[10px] sm:text-xs font-semibold rounded shrink-0 <?= $app['status'] === 'active' ? 'bg-sage-500/20 text-sage-700' : ($app['status'] === 'disabled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800') ?>"><?= htmlspecialchars($app['tag']) ?></span>
                    <?php endif; ?>
                </div>
                <p class="text-ink-600 text-xs sm:text-sm mb-3 sm:mb-4 flex-1 line-clamp-2 sm:line-clamp-none"><?= htmlspecialchars($app['desc']) ?></p>
                <?php if ($app['url'] && $app['status'] === 'active'): ?>
                <a href="<?= htmlspecialchars($app['url']) ?>" <?= str_starts_with($app['url'], 'http') ? 'target="_blank" rel="noopener noreferrer"' : '' ?> class="e-resource-btn inline-flex items-center justify-center gap-1.5 min-h-[44px] px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl text-xs sm:text-sm font-semibold text-sage-700 bg-sage-500/10 hover:bg-sage-500/20 hover:text-sage-800 transition-colors active:scale-[0.98] w-full sm:w-auto">
                    Перейти
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                <?php else: ?>
                <span class="inline-flex items-center justify-center gap-2 min-h-[44px] px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl text-xs sm:text-sm font-medium text-ink-400 bg-cream-200/50 cursor-not-allowed w-full sm:w-auto">Скоро</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Футер страницы -->
        <p class="mt-12 sm:mt-16 text-center text-sm text-ink-500">Developer: @shotayev c 💗 КТСК</p>
    </div>
</main>

<script>
const holidays = [
  { date: "01-01", messages: [
    { lang: "ru", title: "🎉 С Новым Годом! 🎉", subtitle: "Поздравляем с Новым Годом! Пусть этот год принесет счастье, успех и благополучие! 🎊✨" },
    { lang: "kk", title: "🎉 Жаңа жылыңызбен! 🎉", subtitle: "Жаңа жыл құтты болсын! Бұл жыл сізге бақыт, табыс және амандық әкелсін! 🎊✨" },
    { lang: "en", title: "🎉 Happy New Year! 🎉", subtitle: "Happy New Year! May this year bring you happiness, success, and prosperity! 🎊✨" }
  ]},
  { date: "01-07", messages: [
    { lang: "ru", title: "🎄 Рождество Христово! 🎄", subtitle: "Поздравляем с Рождеством Христовым! Пусть ваш дом будет наполнен теплом и радостью! ✨🙏" },
    { lang: "kk", title: "🎄 Рождество Мейрамы! 🎄", subtitle: "Рождество құтты болсын! Үйлеріңіз бақыт пен жылулыққа толы болсын! ✨🙏" },
    { lang: "en", title: "🎄 Merry Christmas! 🎄", subtitle: "Merry Christmas! May your home be filled with warmth and joy! ✨🙏" }
  ]},
  { date: "03-08", messages: [
    { lang: "ru", title: "🌸 С 8 Марта! 🌸", subtitle: "Поздравляем всех женщин с Международным женским днем! Пусть этот день принесет радость и весеннее настроение! 💐✨" },
    { lang: "kk", title: "🌸 8 Наурыз мейрамы құтты болсын! 🌸", subtitle: "Барша аруларды Халықаралық әйелдер күнімен құттықтаймыз! Бұл күн сіздерге қуаныш, бақыт және көктемгі көңіл-күй сыйласын! 💐✨" },
    { lang: "en", title: "🌸 Happy International Women's Day! 🌸", subtitle: "We congratulate all women on International Women's Day! May this day bring you joy, happiness, and a spring mood! 💐✨" }
  ]},
  { date: "03-22", messages: [
    { lang: "ru", title: "🌿 С праздником Наурыз! 🌿", subtitle: "Поздравляем с праздником весны и обновления! Пусть Наурыз принесет счастье, мир и достаток! 🎊" },
    { lang: "kk", title: "🌿 Наурыз мейрамы құтты болсын! 🌿", subtitle: "Наурыз мейрамы құтты болсын! Бұл мереке сізге қуаныш, бейбітшілік пен молшылық әкелсін! 🎊" },
    { lang: "en", title: "🌿 Happy Nauryz! 🌿", subtitle: "Happy Nauryz! May this holiday bring you joy, peace, and prosperity! 🎊" }
  ]},
  { date: "05-01", messages: [
    { lang: "ru", title: "🤝 День единства народа Казахстана! 🤝", subtitle: "Поздравляем с праздником дружбы и согласия! Пусть в нашей стране всегда будет мир и единство! 🇰🇿" },
    { lang: "kk", title: "🤝 Қазақстан халықтарының бірлігі күні! 🤝", subtitle: "Достық пен келісім мерекесі құтты болсын! Елімізде әрдайым бейбітшілік пен бірлік болсын! 🇰🇿" },
    { lang: "en", title: "🤝 Unity Day of the People of Kazakhstan! 🤝", subtitle: "Congratulations on the holiday of friendship and harmony! May there always be peace and unity in our country! 🇰🇿" }
  ]}
];

function getCurrentDate() {
  const today = new Date();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  return `${month}-${day}`;
}

function showHolidayMessage() {
  const todayDate = getCurrentDate();
  const holiday = holidays.find(h => h.date === todayDate);
  let lang = localStorage.getItem("lang") || "ru";

  if (holiday) {
    const message = holiday.messages.find(m => m.lang === lang) || holiday.messages[0];
    document.getElementById("holiday-title").textContent = message.title;
    document.getElementById("holiday-subtitle").textContent = message.subtitle;
    document.getElementById("holiday-section").style.display = "block";
  }
}

function changeLanguage(newLang) {
  localStorage.setItem("lang", newLang);
  showHolidayMessage();
}

document.addEventListener("DOMContentLoaded", showHolidayMessage);
</script>

<?php require ROOT_PATH . '/templates/footer.php'; ?>
