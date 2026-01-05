<?php

namespace app\widgets\tailwind;

use yii\base\Widget;

/**
 * ConfirmModal - модальное окно подтверждения действия
 *
 * Использование в view:
 *   <?= ConfirmModal::widget() ?>
 *
 * Использование в JavaScript:
 *   QazConfirm.show('Удалить запись?', {
 *       title: 'Подтверждение удаления',
 *       type: 'danger', // danger, warning, info
 *       confirmText: 'Удалить',
 *       cancelText: 'Отмена',
 *       onConfirm: () => { // действие при подтверждении }
 *   });
 */
class ConfirmModal extends Widget
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this->renderModal();
    }

    /**
     * Рендерит модальное окно подтверждения
     */
    protected function renderModal(): string
    {
        return <<<'HTML'
<div x-data x-show="$store.confirm.open"
     x-on:keydown.escape.window="$store.confirm.hide()"
     class="fixed inset-0 z-[80]"
     style="display: none;">

    <!-- Backdrop -->
    <div x-show="$store.confirm.open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50"
         @click="$store.confirm.hide()"></div>

    <!-- Modal -->
    <div x-show="$store.confirm.open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full" @click.stop>
            <!-- Header with icon -->
            <div class="p-6 pb-0">
                <div class="flex items-start gap-4">
                    <!-- Icon -->
                    <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
                         :class="{
                             'bg-danger-100': $store.confirm.type === 'danger',
                             'bg-warning-100': $store.confirm.type === 'warning',
                             'bg-primary-100': $store.confirm.type === 'info'
                         }">
                        <!-- Danger icon -->
                        <template x-if="$store.confirm.type === 'danger'">
                            <svg class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </template>
                        <!-- Warning icon -->
                        <template x-if="$store.confirm.type === 'warning'">
                            <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </template>
                        <!-- Info icon -->
                        <template x-if="$store.confirm.type === 'info'">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900" x-text="$store.confirm.title"></h3>
                        <p class="mt-2 text-sm text-gray-600" x-text="$store.confirm.message"></p>
                    </div>

                    <!-- Close button -->
                    <button type="button" @click="$store.confirm.hide()"
                            class="flex-shrink-0 text-gray-400 hover:text-gray-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 pt-6 flex justify-end gap-3">
                <button type="button"
                        @click="$store.confirm.hide()"
                        class="btn btn-secondary"
                        x-text="$store.confirm.cancelText">
                </button>
                <button type="button"
                        @click="$store.confirm.confirm()"
                        class="btn"
                        :class="{
                            'btn-danger': $store.confirm.type === 'danger',
                            'btn-warning': $store.confirm.type === 'warning',
                            'btn-primary': $store.confirm.type === 'info'
                        }"
                        x-text="$store.confirm.confirmText">
                </button>
            </div>
        </div>
    </div>
</div>
HTML;
    }
}
