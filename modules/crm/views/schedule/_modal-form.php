<?php
/**
 * Модальная форма создания/редактирования занятия
 *
 * @var bool $isEdit Режим редактирования
 */

use yii\helpers\Html;
use app\models\Group;
use app\models\Room;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;

$groups = Group::find()
    ->select(['id', 'code', 'name'])
    ->byOrganization()
    ->notDeleted()
    ->orderBy('code')
    ->asArray()
    ->all();

$rooms = Room::getList();
$checkConflictsUrl = OrganizationUrl::to(['schedule/check-conflicts']);
$teachersUrl = OrganizationUrl::to(['schedule/teachers']);
?>

<form @submit.prevent="<?= $isEdit ? "updateEvent(\$event.target, selectedEvent?.id)" : "createEvent(\$event.target)" ?>"
      x-data="{
          groupId: <?= $isEdit ? "selectedEvent?.group_id || ''" : "''" ?>,
          teacherId: <?= $isEdit ? "selectedEvent?.teacher?.id || ''" : "''" ?>,
          roomId: <?= $isEdit ? "selectedEvent?.room_id || ''" : "selectedRoomId || ''" ?>,
          date: <?= $isEdit ? "selectedEvent?.date_raw || ''" : "selectedDate || ''" ?>,
          startTime: <?= $isEdit ? "selectedEvent?.start_time || ''" : "selectedHour ? (selectedHour.toString().padStart(2, '0') + ':' + (selectedMinute || 0).toString().padStart(2, '0')) : '09:00'" ?>,
          endTime: <?= $isEdit ? "selectedEvent?.end_time || ''" : "selectedHour ? ((selectedHour + 1).toString().padStart(2, '0') + ':' + (selectedMinute || 0).toString().padStart(2, '0')) : '10:00'" ?>,
          conflicts: [],
          checkingConflicts: false,
          initialized: false,
          teachersUrl: '<?= $teachersUrl ?>',

          init() {
              <?php if ($isEdit): ?>
              this.$watch('selectedEvent', async (value) => {
                  if (value && !this.initialized) {
                      this.initialized = true;
                      this.groupId = value.group_id || '';
                      this.roomId = value.room_id || '';
                      this.date = value.date_raw || '';
                      this.startTime = value.start_time || '';
                      this.endTime = value.end_time || '';

                      // Загружаем учителей и ждём завершения
                      if (value.group_id) {
                          await loadTeachersForGroup(value.group_id, this.$refs.teacherSelect, this.teachersUrl);
                          if (this.$refs.teacherSelect && value.teacher) {
                              this.$refs.teacherSelect.value = value.teacher.id;
                              this.teacherId = value.teacher.id;
                          }
                      }

                      this.checkConflicts();
                  }
              });
              <?php else: ?>
              this.$watch('selectedDate', (value) => {
                  this.date = value || '';
                  this.checkConflicts();
              });
              this.$watch('selectedHour', (value) => {
                  if (value !== null) {
                      const minute = typeof selectedMinute !== 'undefined' ? selectedMinute : 0;
                      this.startTime = value.toString().padStart(2, '0') + ':' + minute.toString().padStart(2, '0');
                      this.endTime = (value + 1).toString().padStart(2, '0') + ':' + minute.toString().padStart(2, '0');
                      this.checkConflicts();
                  }
              });
              this.$watch('selectedRoomId', (value) => {
                  this.roomId = value ? String(value) : '';
              });
              <?php endif; ?>
          },

          async onGroupChange(groupId) {
              this.teacherId = '';
              if (groupId) {
                  await loadTeachersForGroup(groupId, this.$refs.teacherSelect, this.teachersUrl);
              }
              this.onFieldChange();
          },

          async checkConflicts() {
              if (!this.date || !this.startTime || !this.endTime) return;
              if (!this.teacherId && !this.groupId && !this.roomId) {
                  this.conflicts = [];
                  return;
              }

              this.checkingConflicts = true;
              try {
                  const response = await fetch('<?= $checkConflictsUrl ?>', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/x-www-form-urlencoded',
                          'X-Requested-With': 'XMLHttpRequest',
                          'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                      },
                      body: new URLSearchParams({
                          teacher_id: this.teacherId || '',
                          group_id: this.groupId || '',
                          room_id: this.roomId || '',
                          date: this.date,
                          start_time: this.startTime,
                          end_time: this.endTime,
                          exclude_id: <?= $isEdit ? "selectedEvent?.id || ''" : "''" ?>
                      })
                  });
                  const data = await response.json();
                  this.conflicts = data.conflicts || [];
              } catch (e) {
                  console.error('Error checking conflicts:', e);
                  this.conflicts = [];
              } finally {
                  this.checkingConflicts = false;
              }
          },

          onFieldChange() {
              clearTimeout(this._debounce);
              this._debounce = setTimeout(() => this.checkConflicts(), 300);
          }
      }"
      @close-modal.window="if ($event.detail === '<?= $isEdit ? 'edit-lesson-modal' : 'create-lesson-modal' ?>') { initialized = false; }"<?php if (!$isEdit): ?>

      @open-modal.window="if ($event.detail === 'create-lesson-modal') { $nextTick(() => { roomId = selectedRoomId ? String(selectedRoomId) : ''; date = selectedDate || ''; if (selectedHour !== null) { const m = selectedMinute || 0; startTime = selectedHour.toString().padStart(2,'0') + ':' + m.toString().padStart(2,'0'); endTime = (selectedHour+1).toString().padStart(2,'0') + ':' + m.toString().padStart(2,'0'); } }); }"<?php endif; ?>>

    <!-- Предупреждения о конфликтах -->
    <div x-show="conflicts.length > 0" class="mb-4 p-3 bg-warning-50 border border-warning-200 rounded-lg">
        <div class="flex items-start gap-2">
            <?= Icon::show('exclamation-triangle', 'w-5 h-5 text-warning-600 flex-shrink-0') ?>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-warning-800">Обнаружены пересечения</h4>
                <ul class="text-sm text-warning-700 mt-1">
                    <template x-for="conflict in conflicts" :key="conflict.type">
                        <li x-text="'• ' + conflict.message"></li>
                    </template>
                </ul>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <!-- Группа -->
        <div class="form-group">
            <label class="form-label form-label-required">Группа</label>
            <select name="Lesson[group_id]"
                    class="form-select"
                    required
                    x-model="groupId"
                    @change="onGroupChange($event.target.value)">
                <option value="">Выберите группу</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= $group['id'] ?>">
                        <?= Html::encode($group['code'] . ' - ' . $group['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="form-hint">Выберите группу, для которой планируется занятие</p>
        </div>

        <!-- Преподаватель -->
        <div class="form-group">
            <label class="form-label form-label-required">Преподаватель</label>
            <select name="Lesson[teacher_id]"
                    class="form-select"
                    required
                    x-ref="teacherSelect"
                    x-model="teacherId"
                    @change="onFieldChange()"
                    :disabled="!groupId">
                <option value="">Выберите преподавателя</option>
            </select>
            <p class="form-hint">Загружается автоматически при выборе группы</p>
        </div>

        <!-- Кабинет -->
        <div class="form-group col-span-2">
            <label class="form-label">Кабинет</label>
            <select name="Lesson[room_id]"
                    class="form-select"
                    x-model="roomId"
                    @change="onFieldChange()">
                <option value="">Без кабинета</option>
                <?php foreach ($rooms as $room): ?>
                    <option value="<?= $room['id'] ?>">
                        <?= Html::encode($room['code'] ? $room['code'] . ' - ' . $room['name'] : $room['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="form-hint">Опционально. При выборе проверяется занятость кабинета</p>
        </div>

        <!-- Дата -->
        <div class="form-group">
            <label class="form-label form-label-required">Дата</label>
            <input type="date"
                   name="Lesson[date]"
                   class="form-input"
                   required
                   x-model="date"
                   @change="onFieldChange()">
            <p class="form-hint">Дата проведения занятия</p>
        </div>

        <!-- Время начала и окончания в одной строке -->
        <div class="form-group">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label form-label-required">Начало</label>
                    <input type="time"
                           name="Lesson[start_time]"
                           class="form-input"
                           required
                           x-model="startTime"
                           @change="onFieldChange()">
                </div>
                <div>
                    <label class="form-label form-label-required">Окончание</label>
                    <input type="time"
                           name="Lesson[end_time]"
                           class="form-input"
                           required
                           x-model="endTime"
                           @change="onFieldChange()">
                </div>
            </div>
            <p class="form-hint">Время начала и окончания занятия (ЧЧ:ММ)</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
        <div>
            <span x-show="checkingConflicts" class="text-sm text-gray-500">
                Проверка...
            </span>
        </div>
        <div class="flex gap-3">
            <button type="button"
                    @click="$dispatch('close-modal', '<?= $isEdit ? 'edit-lesson-modal' : 'create-lesson-modal' ?>'); initialized = false;"
                    class="btn btn-secondary">
                Отмена
            </button>
            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? 'Сохранить' : 'Создать' ?>
            </button>
        </div>
    </div>
</form>
