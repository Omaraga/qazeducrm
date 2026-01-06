<?php
/**
 * Колокольчик уведомлений в header
 */

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;

$getNotificationsUrl = OrganizationUrl::to(['notification/get-notifications']);
$markReadUrl = OrganizationUrl::to(['notification/mark-read']);
$markAllReadUrl = OrganizationUrl::to(['notification/mark-all-read']);
$allNotificationsUrl = OrganizationUrl::to(['notification/index']);
?>

<div x-data="{
        open: false,
        notifications: [],
        unreadCount: 0,
        loading: false,
        loaded: false,

        async loadNotifications() {
            if (this.loading) return;
            this.loading = true;

            try {
                const response = await fetch('<?= $getNotificationsUrl ?>');
                const data = await response.json();
                if (data.success) {
                    this.notifications = data.notifications;
                    this.unreadCount = data.unread_count;
                    this.loaded = true;
                }
            } catch (e) {
                console.error('Error loading notifications:', e);
            } finally {
                this.loading = false;
            }
        },

        async markAsRead(id) {
            try {
                const response = await fetch('<?= $markReadUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                    },
                    body: `id=${id}`
                });
                const data = await response.json();
                if (data.success) {
                    this.unreadCount = data.unread_count;
                    const notif = this.notifications.find(n => n.id === id);
                    if (notif) notif.is_read = true;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch('<?= $markAllReadUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.unreadCount = 0;
                    this.notifications.forEach(n => n.is_read = true);
                }
            } catch (e) {
                console.error(e);
            }
        },

        openNotification(notif) {
            if (!notif.is_read) {
                this.markAsRead(notif.id);
            }
            if (notif.link) {
                window.location.href = notif.link;
            }
            this.open = false;
        },

        getIcon(type) {
            const icons = {
                1: 'information-circle',
                2: 'exclamation-triangle',
                3: 'check-circle',
                4: 'exclamation-circle',
                5: 'bell'
            };
            return icons[type] || 'bell';
        },

        init() {
            // Загружаем при открытии страницы
            this.loadNotifications();
            // Обновляем каждые 60 секунд
            setInterval(() => this.loadNotifications(), 60000);
        }
     }"
     x-init="init()"
     class="relative">

    <!-- Bell Button -->
    <button @click="open = !open; if (!loaded) loadNotifications()"
            type="button"
            class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
        <?= Icon::show('bell', 'w-5 h-5') ?>

        <!-- Unread Badge -->
        <span x-show="unreadCount > 0"
              x-text="unreadCount > 9 ? '9+' : unreadCount"
              x-transition
              class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-danger-500 rounded-full">
        </span>
    </button>

    <!-- Dropdown -->
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-[100]"
         style="display: none;">

        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Уведомления</h3>
            <button x-show="unreadCount > 0"
                    @click="markAllAsRead()"
                    class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                Прочитать все
            </button>
        </div>

        <!-- Notifications List -->
        <div class="max-h-80 overflow-y-auto">
            <!-- Loading -->
            <div x-show="loading && !loaded" class="p-4 text-center text-gray-400">
                <svg class="w-6 h-6 animate-spin mx-auto text-primary-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>

            <!-- Empty state -->
            <div x-show="loaded && notifications.length === 0" class="p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <?= Icon::show('bell-slash', 'w-6 h-6 text-gray-400') ?>
                </div>
                <p class="text-sm text-gray-500">Нет уведомлений</p>
            </div>

            <!-- Notifications -->
            <template x-for="notif in notifications" :key="notif.id">
                <button @click="openNotification(notif)"
                        class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-start gap-3 border-b border-gray-50"
                        :class="!notif.is_read && 'bg-primary-50/50'">
                    <!-- Icon -->
                    <div class="flex-shrink-0 mt-0.5">
                        <span :class="notif.type_class">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <template x-if="notif.type == 1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </template>
                                <template x-if="notif.type == 2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </template>
                                <template x-if="notif.type == 3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </template>
                                <template x-if="notif.type == 4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </template>
                                <template x-if="notif.type == 5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </template>
                            </svg>
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate" x-text="notif.title"></p>
                        <p x-show="notif.message" class="text-xs text-gray-500 truncate mt-0.5" x-text="notif.message"></p>
                        <p class="text-[10px] text-gray-400 mt-1" x-text="notif.time_ago"></p>
                    </div>

                    <!-- Unread dot -->
                    <div x-show="!notif.is_read" class="w-2 h-2 rounded-full bg-primary-500 flex-shrink-0 mt-2"></div>
                </button>
            </template>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-100">
            <a href="<?= $allNotificationsUrl ?>"
               class="block text-center text-sm text-primary-600 hover:text-primary-700 font-medium">
                Все уведомления
            </a>
        </div>
    </div>
</div>
