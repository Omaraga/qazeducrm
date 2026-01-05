/**
 * QazScheduleCalendar - Alpine.js компонент расписания
 *
 * Использование:
 *   <div x-data="scheduleCalendar(config)">
 *
 * config = {
 *   urls: { events, filters, create, update, delete, move, details, teachers }
 * }
 */
function scheduleCalendar(config) {
    return {
        // ========== STATE ==========
        currentDate: new Date(),
        viewMode: 'week',  // 'day' | 'week' | 'month'
        dayViewMode: 'timeline', // 'timeline' | 'rooms' - режим Day View
        events: [],
        loading: false,

        // Фильтры
        filters: {
            groups: [],      // выбранные group_id
            teachers: [],    // выбранные teacher_id
            rooms: [],       // выбранные room_id
        },
        filterOptions: {
            groups: [],      // [{id, code, name, color}]
            teachers: [],    // [{id, fio}]
            rooms: [],       // [{id, name, code, color}]
        },

        // Модальные окна
        selectedEvent: null,
        selectedDate: null,
        selectedHour: null,

        // Drag & Drop
        dragging: null,
        dragOver: null,

        // URLs (передаются из PHP)
        urls: config.urls || {},

        // Константы
        hoursRange: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21],
        daysOfWeek: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
        monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],

        // ========== LIFECYCLE ==========
        init() {
            this.loadFiltersFromStorage();
            this.fetchFilters();
            this.fetchEvents();
        },

        // ========== NAVIGATION ==========
        goToToday() {
            this.currentDate = new Date();
            this.fetchEvents();
        },

        goToPrev() {
            const d = new Date(this.currentDate);
            if (this.viewMode === 'day') {
                d.setDate(d.getDate() - 1);
            } else if (this.viewMode === 'week') {
                d.setDate(d.getDate() - 7);
            } else {
                d.setMonth(d.getMonth() - 1);
            }
            this.currentDate = d;
            this.fetchEvents();
        },

        goToNext() {
            const d = new Date(this.currentDate);
            if (this.viewMode === 'day') {
                d.setDate(d.getDate() + 1);
            } else if (this.viewMode === 'week') {
                d.setDate(d.getDate() + 7);
            } else {
                d.setMonth(d.getMonth() + 1);
            }
            this.currentDate = d;
            this.fetchEvents();
        },

        setViewMode(mode) {
            this.viewMode = mode;
            this.fetchEvents();
        },

        goToDay(date) {
            this.currentDate = new Date(date);
            this.viewMode = 'day';
            this.fetchEvents();
        },

        // ========== DATE HELPERS ==========
        formatDate(date) {
            const d = new Date(date);
            return d.toISOString().split('T')[0];
        },

        formatDateDisplay(date) {
            const d = new Date(date);
            return d.getDate() + ' ' + this.monthNamesShort[d.getMonth()] + ' ' + d.getFullYear();
        },

        formatTime(timestamp) {
            const d = new Date(timestamp * 1000);
            return d.toTimeString().slice(0, 5);
        },

        getWeekStart(date) {
            const d = new Date(date);
            const day = d.getDay();
            const diff = d.getDate() - day + (day === 0 ? -6 : 1);
            return new Date(d.setDate(diff));
        },

        getWeekEnd(date) {
            const start = this.getWeekStart(date);
            return new Date(start.getTime() + 6 * 24 * 60 * 60 * 1000);
        },

        getMonthStart(date) {
            const d = new Date(date);
            return new Date(d.getFullYear(), d.getMonth(), 1);
        },

        getMonthEnd(date) {
            const d = new Date(date);
            return new Date(d.getFullYear(), d.getMonth() + 1, 0);
        },

        getDateRange() {
            if (this.viewMode === 'day') {
                const d = new Date(this.currentDate);
                return {
                    start: this.formatDate(d),
                    end: this.formatDate(d)
                };
            } else if (this.viewMode === 'week') {
                return {
                    start: this.formatDate(this.getWeekStart(this.currentDate)),
                    end: this.formatDate(this.getWeekEnd(this.currentDate))
                };
            } else {
                // Месяц - берём с запасом для отображения соседних дней
                const monthStart = this.getMonthStart(this.currentDate);
                const monthEnd = this.getMonthEnd(this.currentDate);
                const start = this.getWeekStart(monthStart);
                const end = this.getWeekEnd(monthEnd);
                return {
                    start: this.formatDate(start),
                    end: this.formatDate(end)
                };
            }
        },

        // ========== COMPUTED PROPERTIES ==========
        get title() {
            if (this.viewMode === 'day') {
                return this.formatDateDisplay(this.currentDate);
            } else if (this.viewMode === 'week') {
                const start = this.getWeekStart(this.currentDate);
                const end = this.getWeekEnd(this.currentDate);
                return start.getDate() + ' ' + this.monthNamesShort[start.getMonth()] + ' - ' +
                       end.getDate() + ' ' + this.monthNamesShort[end.getMonth()] + ' ' + end.getFullYear();
            } else {
                return this.monthNames[this.currentDate.getMonth()] + ' ' + this.currentDate.getFullYear();
            }
        },

        get weekDays() {
            const start = this.getWeekStart(this.currentDate);
            const days = [];
            for (let i = 0; i < 7; i++) {
                const d = new Date(start);
                d.setDate(start.getDate() + i);
                days.push({
                    date: d,
                    dateStr: this.formatDate(d),
                    dayName: this.daysOfWeek[i],
                    dayNum: d.getDate(),
                    isToday: this.formatDate(d) === this.formatDate(new Date())
                });
            }
            return days;
        },

        get monthWeeks() {
            const monthStart = this.getMonthStart(this.currentDate);
            const monthEnd = this.getMonthEnd(this.currentDate);
            const start = this.getWeekStart(monthStart);

            const weeks = [];
            let current = new Date(start);

            while (current <= monthEnd || weeks.length < 6) {
                const week = [];
                for (let i = 0; i < 7; i++) {
                    const d = new Date(current);
                    week.push({
                        date: d,
                        dateStr: this.formatDate(d),
                        dayNum: d.getDate(),
                        isToday: this.formatDate(d) === this.formatDate(new Date()),
                        isCurrentMonth: d.getMonth() === this.currentDate.getMonth()
                    });
                    current.setDate(current.getDate() + 1);
                }
                weeks.push(week);
                if (weeks.length >= 6) break;
            }
            return weeks;
        },

        get activeFiltersCount() {
            return this.filters.groups.length + this.filters.teachers.length + this.filters.rooms.length;
        },

        // ========== DATA FETCHING ==========
        async fetchEvents() {
            this.loading = true;
            const range = this.getDateRange();

            try {
                const response = await QazFetch.post(this.urls.events, {
                    start: range.start,
                    end: range.end,
                    groups: this.filters.groups,
                    teachers: this.filters.teachers
                });
                this.events = response || [];
            } catch (error) {
                console.error('Error loading events:', error);
                this.events = [];
            } finally {
                this.loading = false;
            }
        },

        async fetchFilters() {
            try {
                const response = await QazFetch.get(this.urls.filters);
                if (response) {
                    this.filterOptions.groups = response.groups || [];
                    this.filterOptions.teachers = response.teachers || [];
                    this.filterOptions.rooms = response.rooms || [];
                }
            } catch (error) {
                console.error('Error loading filters:', error);
            }
        },

        async fetchLessonDetails(id) {
            try {
                const response = await QazFetch.get(this.urls.details + '?id=' + id);
                if (response && response.success) {
                    return response.data;
                }
            } catch (error) {
                console.error('Error loading lesson details:', error);
            }
            return null;
        },

        // ========== FILTERS ==========
        toggleGroupFilter(groupId) {
            const idx = this.filters.groups.indexOf(groupId);
            if (idx > -1) {
                this.filters.groups.splice(idx, 1);
            } else {
                this.filters.groups.push(groupId);
            }
            this.saveFiltersToStorage();
            this.fetchEvents();
        },

        toggleTeacherFilter(teacherId) {
            const idx = this.filters.teachers.indexOf(teacherId);
            if (idx > -1) {
                this.filters.teachers.splice(idx, 1);
            } else {
                this.filters.teachers.push(teacherId);
            }
            this.saveFiltersToStorage();
            this.fetchEvents();
        },

        isGroupSelected(groupId) {
            return this.filters.groups.includes(groupId);
        },

        isTeacherSelected(teacherId) {
            return this.filters.teachers.includes(teacherId);
        },

        toggleRoomFilter(roomId) {
            const idx = this.filters.rooms.indexOf(roomId);
            if (idx > -1) {
                this.filters.rooms.splice(idx, 1);
            } else {
                this.filters.rooms.push(roomId);
            }
            this.saveFiltersToStorage();
            this.fetchEvents();
        },

        isRoomSelected(roomId) {
            return this.filters.rooms.includes(roomId);
        },

        clearFilters() {
            this.filters.groups = [];
            this.filters.teachers = [];
            this.filters.rooms = [];
            this.saveFiltersToStorage();
            this.fetchEvents();
        },

        saveFiltersToStorage() {
            try {
                localStorage.setItem('schedule_filters', JSON.stringify(this.filters));
            } catch (e) {}
        },

        loadFiltersFromStorage() {
            try {
                const saved = localStorage.getItem('schedule_filters');
                if (saved) {
                    const parsed = JSON.parse(saved);
                    this.filters.groups = parsed.groups || [];
                    this.filters.teachers = parsed.teachers || [];
                    this.filters.rooms = parsed.rooms || [];
                }
            } catch (e) {}
        },

        // ========== EVENT HELPERS ==========
        getEventsForDateHour(dateStr, hour) {
            return this.events.filter(e => {
                // Используем start_time напрямую (формат "HH:mm")
                const eventHour = parseInt(e.start_time.split(':')[0], 10);
                return e.date === dateStr && eventHour === hour;
            });
        },

        getEventsForDate(dateStr) {
            return this.events.filter(e => e.date === dateStr);
        },

        getEventsForRoomHour(dateStr, roomId, hour) {
            return this.events.filter(e => {
                // Используем start_time напрямую
                const eventHour = parseInt(e.start_time.split(':')[0], 10);
                return e.date === dateStr && e.room_id === roomId && eventHour === hour;
            });
        },

        getEventsWithoutRoomHour(dateStr, hour) {
            return this.events.filter(e => {
                // Используем start_time напрямую
                const eventHour = parseInt(e.start_time.split(':')[0], 10);
                return e.date === dateStr && !e.room_id && eventHour === hour;
            });
        },

        setDayViewMode(mode) {
            this.dayViewMode = mode;
        },

        getEventDuration(event) {
            return Math.round((event.end - event.start) / 60);
        },

        // ========== MODAL HELPERS ==========
        openCreateModal(dateStr, hour) {
            this.selectedDate = dateStr;
            this.selectedHour = hour;
            this.selectedEvent = null;
            this.$dispatch('open-modal', 'create-lesson-modal');
        },

        async openViewModal(eventId) {
            const details = await this.fetchLessonDetails(eventId);
            if (details) {
                this.selectedEvent = details;
                this.$dispatch('open-modal', 'view-lesson-modal');
            }
        },

        openEditModal(eventId) {
            this.selectedEvent = this.events.find(e => e.id === eventId);
            this.$dispatch('open-modal', 'edit-lesson-modal');
        },

        openDeleteModal(eventId) {
            this.selectedEvent = this.events.find(e => e.id === eventId);
            this.$dispatch('open-modal', 'delete-lesson-modal');
        },

        closeAllModals() {
            this.$dispatch('close-modal', 'create-lesson-modal');
            this.$dispatch('close-modal', 'view-lesson-modal');
            this.$dispatch('close-modal', 'edit-lesson-modal');
            this.$dispatch('close-modal', 'delete-lesson-modal');
        },

        // ========== CRUD OPERATIONS ==========
        async createEvent(form) {
            const formData = new FormData(form);

            try {
                const response = await QazFetch.postForm(this.urls.create, formData);
                if (response && response.success) {
                    QazToast.success(response.message || 'Занятие создано');
                    this.closeAllModals();
                    this.fetchEvents();
                    return true;
                } else {
                    QazToast.error(response.message || 'Ошибка при создании');
                }
            } catch (error) {
                QazToast.error('Ошибка при создании занятия');
            }
            return false;
        },

        async updateEvent(form, id) {
            const formData = new FormData(form);

            try {
                const response = await QazFetch.postForm(this.urls.update + '?id=' + id, formData);
                if (response && response.success) {
                    QazToast.success(response.message || 'Занятие обновлено');
                    this.closeAllModals();
                    this.fetchEvents();
                    return true;
                } else {
                    QazToast.error(response.message || 'Ошибка при обновлении');
                }
            } catch (error) {
                QazToast.error('Ошибка при обновлении занятия');
            }
            return false;
        },

        async deleteEvent(id) {
            try {
                const response = await QazFetch.post(this.urls.delete + '?id=' + id, {});
                if (response && response.success) {
                    QazToast.success(response.message || 'Занятие удалено');
                    this.closeAllModals();
                    this.fetchEvents();
                    return true;
                } else {
                    QazToast.error(response.message || 'Ошибка при удалении');
                }
            } catch (error) {
                QazToast.error('Ошибка при удалении занятия');
            }
            return false;
        },

        async moveEvent(id, newDate, newStartTime) {
            try {
                const response = await QazFetch.post(this.urls.move, {
                    id: id,
                    newDate: newDate,
                    newStartTime: newStartTime
                });
                if (response && response.success) {
                    QazToast.success(response.message || 'Занятие перемещено');
                    this.fetchEvents();
                    return true;
                } else {
                    QazToast.error(response.message || 'Ошибка при перемещении');
                }
            } catch (error) {
                QazToast.error('Ошибка при перемещении занятия');
            }
            return false;
        },

        // ========== DRAG & DROP ==========
        onDragStart(event, lessonId) {
            this.dragging = lessonId;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', lessonId);
        },

        onDragOver(event, dateStr, hour) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            this.dragOver = { date: dateStr, hour: hour };
        },

        onDragLeave() {
            this.dragOver = null;
        },

        onDragEnd() {
            this.dragging = null;
            this.dragOver = null;
        },

        async onDrop(event, dateStr, hour) {
            event.preventDefault();
            const lessonId = parseInt(event.dataTransfer.getData('text/plain'));

            if (lessonId && dateStr && hour !== undefined) {
                const newStartTime = hour.toString().padStart(2, '0') + ':00';
                await this.moveEvent(lessonId, dateStr, newStartTime);
            }

            this.dragging = null;
            this.dragOver = null;
        },

        isDragging(eventId) {
            return this.dragging === eventId;
        },

        isDropTarget(dateStr, hour) {
            return this.dragOver && this.dragOver.date === dateStr && this.dragOver.hour === hour;
        },

        // ========== TEACHERS FOR GROUP ==========
        async loadTeachersForGroup(groupId, selectElement) {
            if (!groupId) {
                selectElement.innerHTML = '<option value="">Выберите преподавателя</option>';
                selectElement.disabled = true;
                return;
            }

            try {
                const response = await QazFetch.post(this.urls.teachers, { id: groupId });
                selectElement.innerHTML = '<option value="">Выберите преподавателя</option>';
                if (response && Array.isArray(response)) {
                    response.forEach(teacher => {
                        const option = document.createElement('option');
                        option.value = teacher.id;
                        option.textContent = teacher.fio;
                        selectElement.appendChild(option);
                    });
                }
                selectElement.disabled = false;
            } catch (error) {
                console.error('Error loading teachers:', error);
            }
        }
    }
}

// Регистрация компонента
window.scheduleCalendar = scheduleCalendar;
