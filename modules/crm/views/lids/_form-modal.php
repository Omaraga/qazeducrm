<?php
/**
 * Модальная форма создания/редактирования лида
 *
 * @var bool $isEdit Режим редактирования
 */

use app\helpers\Lists;
use app\helpers\OrganizationUrl;
use app\models\Lids;
use app\models\User;
use app\components\ActiveRecord;
use app\widgets\tailwind\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$managers = User::find()
    ->innerJoinWith(['currentUserOrganizations' => function($q) {
        $q->andWhere(['<>', 'user_organization.is_deleted', ActiveRecord::DELETED]);
    }])
    ->all();

$createUrl = OrganizationUrl::to(['lids/create-ajax']);
$updateUrl = OrganizationUrl::to(['lids/update-ajax']);
$checkDuplicatesUrl = OrganizationUrl::to(['lids/check-duplicates']);
?>

<form @submit.prevent="<?= $isEdit ? 'updateLid($event.target)' : 'createLid($event.target)' ?>"
      x-data="{
          status: <?= $isEdit ? '$store.lids.editingLid?.status || ' . Lids::STATUS_NEW : Lids::STATUS_NEW ?>,
          contactPerson: <?= $isEdit ? "\$store.lids.editingLid?.contact_person || 'parent'" : "'parent'" ?>,
          isSubmitting: false,
          lostStatus: <?= Lids::STATUS_LOST ?>,

          // Duplicate detection
          duplicates: [],
          checkingDuplicates: false,
          duplicateTimeout: null,

          init() {
              <?php if ($isEdit): ?>
              this.$watch('$store.lids.editingLid', (val) => {
                  if (val) {
                      this.status = val.status || <?= Lids::STATUS_NEW ?>;
                      this.contactPerson = val.contact_person || 'parent';
                  }
              });
              <?php endif; ?>
          },

          // Check for duplicates with debounce
          checkDuplicates(phone) {
              if (!phone || phone.length < 10) {
                  this.duplicates = [];
                  return;
              }

              clearTimeout(this.duplicateTimeout);
              this.duplicateTimeout = setTimeout(async () => {
                  this.checkingDuplicates = true;
                  try {
                      const excludeId = <?= $isEdit ? "\$store.lids.editingLid?.id || ''" : "''" ?>;
                      const response = await fetch(`<?= $checkDuplicatesUrl ?>?phone=${encodeURIComponent(phone)}&exclude_id=${excludeId}`);
                      const data = await response.json();
                      if (data.success) {
                          this.duplicates = data.duplicates;
                      }
                  } catch (e) {
                      console.error(e);
                  } finally {
                      this.checkingDuplicates = false;
                  }
              }, 500);
          },

          async createLid(form) {
              if (this.isSubmitting) return;
              this.isSubmitting = true;

              try {
                  const formData = new FormData(form);
                  const response = await fetch('<?= $createUrl ?>', {
                      method: 'POST',
                      body: formData,
                      headers: {
                          'X-Requested-With': 'XMLHttpRequest'
                      }
                  });

                  const data = await response.json();

                  if (data.success) {
                      $dispatch('close-modal', 'lids-form-modal');
                      if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                          Alpine.store('toast').success(data.message || 'Лид создан');
                      }
                      location.reload();
                  } else {
                      if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                          Alpine.store('toast').error(data.message || 'Ошибка сохранения');
                      }
                  }
              } catch (e) {
                  console.error(e);
                  if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                      Alpine.store('toast').error('Ошибка сети');
                  }
              } finally {
                  this.isSubmitting = false;
              }
          },

          async updateLid(form) {
              if (this.isSubmitting || !$store.lids.editingLid?.id) return;
              this.isSubmitting = true;

              try {
                  const formData = new FormData(form);
                  const response = await fetch('<?= $updateUrl ?>?id=' + $store.lids.editingLid.id, {
                      method: 'POST',
                      body: formData,
                      headers: {
                          'X-Requested-With': 'XMLHttpRequest'
                      }
                  });

                  const data = await response.json();

                  if (data.success) {
                      $dispatch('close-modal', 'lids-edit-modal');
                      if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                          Alpine.store('toast').success(data.message || 'Лид обновлён');
                      }
                      location.reload();
                  } else {
                      if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                          Alpine.store('toast').error(data.message || 'Ошибка сохранения');
                      }
                  }
              } catch (e) {
                  console.error(e);
                  if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                      Alpine.store('toast').error('Ошибка сети');
                  }
              } finally {
                  this.isSubmitting = false;
              }
          }
      }">

    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- ФИО ребёнка -->
        <div class="form-group">
            <label class="form-label">ФИО ребёнка</label>
            <input type="text" name="Lids[fio]" class="form-input"
                   placeholder="Иванов Иван"
                   <?= $isEdit ? ':value="$store.lids.editingLid?.fio || \'\'"' : '' ?>>
        </div>

        <!-- Телефон ребёнка -->
        <div class="form-group">
            <label class="form-label">Телефон ребёнка</label>
            <input type="tel" name="Lids[phone]" class="form-input"
                   placeholder="+7 (XXX) XXX-XX-XX"
                   @input="checkDuplicates($event.target.value)"
                   <?= $isEdit ? ':value="$store.lids.editingLid?.phone || \'\'"' : '' ?>>
        </div>

        <!-- ФИО родителя -->
        <div class="form-group">
            <label class="form-label">ФИО родителя</label>
            <input type="text" name="Lids[parent_fio]" class="form-input"
                   placeholder="Иванова Мария"
                   <?= $isEdit ? ':value="$store.lids.editingLid?.parent_fio || \'\'"' : '' ?>>
        </div>

        <!-- Телефон родителя -->
        <div class="form-group">
            <label class="form-label">Телефон родителя</label>
            <input type="tel" name="Lids[parent_phone]" class="form-input"
                   placeholder="+7 (XXX) XXX-XX-XX"
                   @input="checkDuplicates($event.target.value)"
                   <?= $isEdit ? ':value="$store.lids.editingLid?.parent_phone || \'\'"' : '' ?>>
        </div>

        <!-- Предупреждение о дубликатах -->
        <div class="col-span-full" x-show="duplicates.length > 0" x-cloak>
            <div class="bg-warning-50 border border-warning-200 rounded-lg p-3">
                <div class="flex items-start gap-2">
                    <div class="flex-shrink-0 mt-0.5">
                        <?= Icon::show('exclamation-triangle', 'sm', 'text-warning-500') ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-warning-800">
                            Возможные дубликаты
                        </p>
                        <p class="text-xs text-warning-600 mt-1">
                            Найдены лиды с похожим номером телефона:
                        </p>
                        <ul class="mt-2 space-y-1">
                            <template x-for="dup in duplicates" :key="dup.id">
                                <li class="flex items-center justify-between text-xs">
                                    <span class="text-gray-700">
                                        <span x-text="dup.fio" class="font-medium"></span>
                                        <span class="text-gray-400 mx-1">•</span>
                                        <span x-text="dup.phone || dup.parent_phone"></span>
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium"
                                          :class="`bg-${dup.status_color}-100 text-${dup.status_color}-700`"
                                          x-text="dup.status"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Контактное лицо -->
        <div class="form-group col-span-full">
            <label class="form-label">Контактное лицо</label>
            <div class="flex gap-2 mt-1">
                <label class="inline-flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg transition-colors border"
                       :class="contactPerson === 'parent' ? 'bg-primary-50 border-primary-300 text-primary-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'">
                    <input type="radio" name="Lids[contact_person]" value="parent" x-model="contactPerson" class="sr-only">
                    <?= Icon::show('user', 'sm') ?>
                    <span class="text-sm font-medium">Родитель</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg transition-colors border"
                       :class="contactPerson === 'pupil' ? 'bg-primary-50 border-primary-300 text-primary-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'">
                    <input type="radio" name="Lids[contact_person]" value="pupil" x-model="contactPerson" class="sr-only">
                    <?= Icon::show('academic-cap', 'sm') ?>
                    <span class="text-sm font-medium">Ребёнок</span>
                </label>
            </div>
        </div>

        <!-- Школа -->
        <div class="form-group">
            <label class="form-label">Школа</label>
            <input type="text" name="Lids[school]" class="form-input"
                   placeholder="Школа/лицей"
                   <?= $isEdit ? ':value="$store.lids.editingLid?.school || \'\'"' : '' ?>>
        </div>

        <!-- Класс -->
        <div class="form-group">
            <label class="form-label">Класс</label>
            <select name="Lids[class_id]" class="form-select"
                    <?= $isEdit ? ':value="$store.lids.editingLid?.class_id || \'\'"' : '' ?>>
                <option value="">Выберите класс</option>
                <?php foreach (Lists::getGrades() as $id => $name): ?>
                    <option value="<?= $id ?>">
                        <?= Html::encode($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Статус -->
        <div class="form-group">
            <label class="form-label form-label-required">Статус</label>
            <select name="Lids[status]" class="form-select" x-model="status" required>
                <?php foreach (Lids::getStatusList() as $value => $label): ?>
                    <option value="<?= $value ?>"><?= Html::encode($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Источник -->
        <div class="form-group">
            <label class="form-label">Источник</label>
            <select name="Lids[source]" class="form-select"
                    <?= $isEdit ? ':value="$store.lids.editingLid?.source || \'\'"' : '' ?>>
                <option value="">Выберите источник</option>
                <?php foreach (Lids::getSourceList() as $value => $label): ?>
                    <option value="<?= $value ?>">
                        <?= Html::encode($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Ответственный -->
        <div class="form-group">
            <label class="form-label">Ответственный</label>
            <select name="Lids[manager_id]" class="form-select"
                    <?= $isEdit ? ':value="$store.lids.editingLid?.manager_id || \'\'"' : '' ?>>
                <option value="">Выберите</option>
                <?php foreach ($managers as $manager): ?>
                    <option value="<?= $manager->id ?>">
                        <?= Html::encode($manager->fio) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Следующий контакт -->
        <div class="form-group">
            <label class="form-label">Следующий контакт</label>
            <input type="date" name="Lids[next_contact_date]" class="form-input"
                   <?= $isEdit ? ':value="$store.lids.editingLid?.next_contact_date || \'\'"' : '' ?>>
        </div>

        <!-- Причина потери -->
        <div class="form-group col-span-full" x-show="status == lostStatus" x-cloak>
            <label class="form-label">Причина потери</label>
            <select name="Lids[lost_reason]" class="form-select"
                    <?= $isEdit ? ':value="$store.lids.editingLid?.lost_reason || \'\'"' : '' ?>>
                <option value="">Выберите причину</option>
                <option value="Дорого">Дорого</option>
                <option value="Далеко">Далеко</option>
                <option value="Не устроило расписание">Не устроило расписание</option>
                <option value="Выбрали конкурента">Выбрали конкурента</option>
                <option value="Передумали">Передумали</option>
                <option value="Не дозвонились">Не дозвонились</option>
                <option value="Другое">Другое</option>
            </select>
        </div>

        <!-- Комментарий -->
        <div class="form-group col-span-full">
            <label class="form-label">Комментарий</label>
            <textarea name="Lids[comment]" class="form-input" rows="2"
                      placeholder="Дополнительная информация"
                      <?= $isEdit ? 'x-text="$store.lids.editingLid?.comment || \'\'"' : '' ?>></textarea>
        </div>
    </div>

    <!-- Footer -->
    <div class="flex justify-end items-center gap-3 mt-6 pt-4 border-t border-gray-200">
        <button type="button"
                @click="$dispatch('close-modal', '<?= $isEdit ? 'lids-edit-modal' : 'lids-form-modal' ?>')"
                class="btn btn-secondary">
            Отмена
        </button>
        <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
            <template x-if="isSubmitting">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </template>
            <span x-text="isSubmitting ? 'Сохранение...' : '<?= $isEdit ? 'Сохранить' : 'Создать' ?>'"></span>
        </button>
    </div>
</form>
