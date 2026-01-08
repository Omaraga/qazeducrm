<?php

/** @var yii\web\View $this */

use app\helpers\SettingsHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Контакты';

// Получаем данные из настроек
$email = SettingsHelper::getEmail();
$phone = SettingsHelper::getMainPhone();
$phoneClean = preg_replace('/[^\d+]/', '', $phone);
$address = SettingsHelper::getAddress();
$workingHours = SettingsHelper::getWorkingHours();
$telegram = SettingsHelper::getTelegram();
$telegramUsername = SettingsHelper::getTelegramUsername();
$social = SettingsHelper::getSocialLinks();
?>

<!-- Hero -->
<section class="bg-gradient-to-br from-gray-900 to-gray-800 py-20 text-center">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4">Свяжитесь с нами</h1>
        <p class="text-xl text-white/70">Мы всегда рады помочь и ответить на ваши вопросы</p>
    </div>
</section>

<!-- Contact Cards -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <!-- Email -->
            <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="w-16 h-16 bg-orange-100 rounded-xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-envelope text-2xl text-orange-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Email</h3>
                <p class="text-gray-500 mb-4">Напишите нам, мы ответим в течение 24 часов</p>
                <a href="mailto:<?= Html::encode($email) ?>" class="text-orange-500 font-semibold hover:text-orange-600 transition-colors">
                    <?= Html::encode($email) ?>
                </a>
            </div>

            <!-- Phone -->
            <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="w-16 h-16 bg-orange-100 rounded-xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-phone text-2xl text-orange-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Телефон</h3>
                <p class="text-gray-500 mb-4">Пн-Пт с 9:00 до 18:00 по времени Астаны</p>
                <a href="tel:<?= Html::encode($phoneClean) ?>" class="text-orange-500 font-semibold hover:text-orange-600 transition-colors">
                    <?= Html::encode($phone) ?>
                </a>
            </div>

            <!-- Telegram -->
            <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="w-16 h-16 bg-orange-100 rounded-xl flex items-center justify-center mx-auto mb-6">
                    <i class="fab fa-telegram text-2xl text-orange-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Telegram</h3>
                <p class="text-gray-500 mb-4">Быстрые ответы на ваши вопросы</p>
                <a href="<?= Html::encode($telegram) ?>" class="text-orange-500 font-semibold hover:text-orange-600 transition-colors">
                    <?= Html::encode($telegramUsername) ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-10">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Оставить заявку</h2>
                    <p class="text-gray-500">Заполните форму и мы свяжемся с вами</p>
                </div>

                <form class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Имя *</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="Ваше имя" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Телефон *</label>
                            <input type="tel" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="+7 777 123 4567" required>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="email@example.kz">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Название организации</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="Ваш учебный центр">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Сообщение</label>
                        <textarea rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors resize-none" placeholder="Расскажите о вашем учебном центре и задачах..."></textarea>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="px-8 py-4 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50">
                            Отправить заявку
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Office Info -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-5 gap-8 max-w-6xl mx-auto">
            <!-- Info -->
            <div class="lg:col-span-2">
                <div class="bg-gray-900 rounded-2xl p-8 h-full">
                    <h3 class="text-2xl font-bold text-white mb-8">Наш офис</h3>

                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="w-6 flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-orange-500"></i>
                            </div>
                            <div>
                                <div class="text-white font-medium mb-1">Адрес</div>
                                <div class="text-gray-400"><?= Html::encode($address) ?></div>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-6 flex-shrink-0">
                                <i class="fas fa-clock text-orange-500"></i>
                            </div>
                            <div>
                                <div class="text-white font-medium mb-1">Время работы</div>
                                <div class="text-gray-400"><?= nl2br(Html::encode($workingHours)) ?></div>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-6 flex-shrink-0">
                                <i class="fas fa-phone text-orange-500"></i>
                            </div>
                            <div>
                                <div class="text-white font-medium mb-1">Телефон</div>
                                <div class="text-gray-400"><?= Html::encode($phone) ?></div>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-6 flex-shrink-0">
                                <i class="fas fa-envelope text-orange-500"></i>
                            </div>
                            <div>
                                <div class="text-white font-medium mb-1">Email</div>
                                <div class="text-gray-400"><?= Html::encode($email) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Links -->
                    <div class="flex gap-3 mt-8 pt-8 border-t border-gray-800">
                        <?php if ($social['telegram'] !== '#'): ?>
                        <a href="<?= Html::encode($social['telegram']) ?>" class="w-11 h-11 bg-gray-800 hover:bg-orange-500 rounded-lg flex items-center justify-center text-gray-400 hover:text-white transition-all" aria-label="Telegram">
                            <i class="fab fa-telegram text-lg"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($social['instagram'] !== '#'): ?>
                        <a href="<?= Html::encode($social['instagram']) ?>" class="w-11 h-11 bg-gray-800 hover:bg-orange-500 rounded-lg flex items-center justify-center text-gray-400 hover:text-white transition-all" aria-label="Instagram">
                            <i class="fab fa-instagram text-lg"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($social['whatsapp'] !== '#'): ?>
                        <a href="<?= Html::encode($social['whatsapp']) ?>" class="w-11 h-11 bg-gray-800 hover:bg-orange-500 rounded-lg flex items-center justify-center text-gray-400 hover:text-white transition-all" aria-label="WhatsApp">
                            <i class="fab fa-whatsapp text-lg"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($social['youtube'] !== '#'): ?>
                        <a href="<?= Html::encode($social['youtube']) ?>" class="w-11 h-11 bg-gray-800 hover:bg-orange-500 rounded-lg flex items-center justify-center text-gray-400 hover:text-white transition-all" aria-label="YouTube">
                            <i class="fab fa-youtube text-lg"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div class="lg:col-span-3">
                <div class="bg-gray-100 rounded-2xl h-full min-h-[400px] flex items-center justify-center">
                    <div class="text-center text-gray-400">
                        <i class="fas fa-map-marked-alt text-5xl mb-4"></i>
                        <p>Карта будет добавлена</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
