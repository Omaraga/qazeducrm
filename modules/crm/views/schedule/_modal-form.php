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
?>

<form @submit.prevent="<?= $isEdit ? "updateEvent(\$event.target, selectedEvent?.id)" : "createEvent(\$event.target)" ?>"
      x-data="{
          groupId: <?= $isEdit ? "selectedEvent?.group_id || ''" : "''" ?>,
          teacherId: <?= $isEdit ? "selectedEvent?.teacher?.id || ''" : "''" ?>,
          roomId: <?= $isEdit ? "selectedEvent?.room_id || ''" : "''" ?>,
          date: <?= $isEdit ? "selectedEvent?.date_raw || ''" : "selectedDate || ''" ?>,
          startTime: <?= $isEdit ? "selectedEvent?.start_time || ''" : "selectedHour ? (selectedHour.toString().padStart(2, '0') + ':00') : '09:00'" ?>,
          endTime: <?= $isEdit ? "selectedEvent?.end_time || ''" : "selectedHour ? ((selectedHour + 1).toString().padStart(2, '0') + ':00') : '10:00'" ?>,
          conflicts: [],
          checkingConflicts: false,

          init() {
              <?php if ($isEdit): ?>
              this.$watch('selectedEvent', (value) => {
                  if (value) {
                      this.groupId = value.group_id || '';
                      this.teacherId = value.teacher?.id || '';
                      this.roomId = value.room_id || '';
                      this.date = value.date_raw || '';
                      this.startTime = value.start_time || '';
                      this.endTime = value.end_time || '';
                      if (value.group_id) {
                          loadTeachersForGroup(value.group_id, this.$refs.teacherSelect);
                          setTimeout(() => {
                              if (this.$refs.teacherSelect && value.teacher) {
                                  this.$refs.teacherSelect.value = value.teacher.id;
                                  this.teacherId = value.teacher.id;
                              }
                          }, 500);
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
                      this.startTime = value.toString().padStart(2, '0') + ':00';
                      this.endTime = (value + 1).toString().padStart(2, '0') + ':00';
                      this.checkConflicts();
                  }
              });
              <?php endif; ?>
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
      }">

    <!-- Предупреждения о конфликтах -->
    <div x-show="conflicts.length > 0" class="mb-4 p-4 bg-warning-50 border border-warning-200 rounded-lg">
        <div class="flex items-start gap-3">
            <?= Icon::show('exclamation-triangle', 'w-5 h-5 text-warning-600 flex-shrink-0 mt-0.5') ?>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-warning-800 mb-1">Обнаружены пересечения</h4>
                <ul class="text-sm text-warning-700 space-y-1">
                    <template x-for="conflict in conflicts" :key="conflict.type">
                        <li class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-warning-500"></span>
                            <span x-text="conflict.message"></span>
                        </li>
                    </template>
                </ul>
                <p class="text-xs text-warning-600 mt-2">Занятие можно создать, но учитывайте возможные накладки.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Группа -->
        <div class="form-group">
            <label class="form-label form-label-required">Группа</label>
            <select name="Lesson[group_id]"
                    class="form-select"
                    required
                    x-model="groupId"
                    @change="loadTeachersForGroup($event.target.value, $refs.teacherSelect); teacherId = ''; onFieldChange()">
                <option value="">Выберите группу</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= $group['id'] ?>">
                        <?= Html::encode($group['code'] . ' - ' . $group['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
        </div>

        <!-- Кабинет -->
        <div class="form-group md:col-span-2">
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
            <p class="text-xs text-gray-500 mt-1">Необязательно. Если указать, система проверит занятость.</p>
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
        </div>

        <!-- Время начала -->
        <div class="form-group">
            <label class="form-label form-label-required">Время начала</label>
            <input type="time"
                   name="Lesson[start_time]"
                   class="form-input"
                   required
                   x-model="startTime"
                   @change="onFieldChange()">
        </div>

        <!-- Время окончания -->
        <div class="form-group md:col-span-2">
            <label class="form-label form-label-required">Время окончания</label>
            <input type="time"
                   name="Lesson[end_time]"
                   class="form-input"
                   required
                   x-model="endTime"
                   @change="onFieldChange()">
        </div>
    </div>

    <!-- Footer с кнопками -->
    <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
        <div>
            <span x-show="checkingConflicts" class="text-sm text-gray-500 flex items-center gap-2">
                <span class="spinner spinner-sm"></span>
                Проверка...
            </span>
        </div>
        <div class="flex gap-3">
            <button type="button"
                    @click="$dispatch('close-modal', '<?= $isEdit ? 'edit-lesson-modal' : 'create-lesson-modal' ?>')"
                    class="btn btn-secondary">
                Отмена
            </button>
            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? 'Сохранить' : 'Создать' ?>
            </button>
        </div>
    </div>
</form>
