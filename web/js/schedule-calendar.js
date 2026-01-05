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
        selectedMinute: 0,

        // Drag & Drop
        dragging: null,
        dragOver: null,

        // URLs (передаются из PHP)
        urls: config.urls || {},

        // Настройки сетки времени
        gridInterval: 60,  // минуты: 10, 15, 30, 60
        workStart: 8,      // начало рабочего дня (час)
        workEnd: 21,       // конец рабочего дня (час)

        // Константы (для обратной совместимости)
        hoursRange: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21],
        daysOfWeek: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
        monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],

        // ========== LIFECYCLE ==========
        async init() {
            await this.loadSettings();
            this.loadFiltersFromStorage();
            this.fetchFilters();
            this.fetchEvents();
        },

        // ========== SETTINGS ==========
        async loadSettings() {
            try {
                const response = await QazFetch.get(this.urls.settings);
                if (response) {
                    if (response.grid_interval) {
                        this.gridInterval = parseInt(response.grid_interval) || 60;
                    }
                    if (response.view_mode) {
                        this.viewMode = response.view_mode;
                    }
                }
            } catch (e) {
                this.gridInterval = 60;
                this.viewMode = 'week';
            }
        },

        async saveGridInterval() {
            try {
                const response = await QazFetch.post(this.urls.saveSettings, {
                    grid_interval: this.gridInterval
                });
                if (response && response.success) {
                    QazToast.success('Настройка сохранена');
                } else {
                    QazToast.error(response.message || 'Ошибка сохранения');
                }
            } catch (e) {
                QazToast.error('Ошибка сохранения настроек');
            }
        },

        // Getter для временных слотов
        get timeSlots() {
            const slots = [];
            for (let h = this.workStart; h <= this.workEnd; h++) {
                for (let m = 0; m < 60; m += this.gridInterval) {
                    // Не добавляем слоты после workEnd:00
                    if (h === this.workEnd && m > 0) break;
                    slots.push({
                        hour: h,
                        minute: m,
                        label: h + ':' + m.toString().padStart(2, '0'),
                        key: h + '-' + m
                    });
                }
            }
            return slots;
        },

        // Высота слота в зависимости от интервала
        get slotHeight() {
            switch (this.gridInterval) {
                case 10: return 24;
                case 15: return 30;
                case 30: return 40;
                default: return 60;
            }
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
            this.saveViewMode(mode);
            this.fetchEvents();
        },

        async saveViewMode(mode) {
            try {
                await QazFetch.post(this.urls.saveSettings, { view_mode: mode });
            } catch (e) {
                // Тихо игнорируем ошибку сохранения
            }
        },

        goToDay(date) {
            this.currentDate = new Date(date);
            this.viewMode = 'day';
            this.saveViewMode('day');
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
        // Получить события для слота (поддержка интервалов меньше часа)
        getEventsForSlot(dateStr, hour, minute) {
            return this.events.filter(e => {
                const [eHour, eMin] = e.start_time.split(':').map(Number);
                const slotStart = hour * 60 + minute;
                const slotEnd = slotStart + this.gridInterval;
                const eventStart = eHour * 60 + eMin;
                return e.date === dateStr && eventStart >= slotStart && eventStart < slotEnd;
            });
        },

        // Для обратной совместимости (используется при gridInterval = 60)
        getEventsForDateHour(dateStr, hour) {
            return this.getEventsForSlot(dateStr, hour, 0);
        },

        getEventsForDate(dateStr) {
            return this.events.filter(e => e.date === dateStr);
        },

        getEventsForRoomSlot(dateStr, roomId, hour, minute) {
            return this.events.filter(e => {
                const [eHour, eMin] = e.start_time.split(':').map(Number);
                const slotStart = hour * 60 + minute;
                const slotEnd = slotStart + this.gridInterval;
                const eventStart = eHour * 60 + eMin;
                return e.date === dateStr && e.room_id === roomId && eventStart >= slotStart && eventStart < slotEnd;
            });
        },

        // Получить события для комнаты, начинающиеся в этом слоте (для отображения длительности)
        getEventsStartingInRoomSlot(dateStr, roomId, hour, minute) {
            return this.events.filter(e => {
                if (e.date !== dateStr || e.room_id !== roomId) return false;
                const [eHour, eMin] = e.start_time.split(':').map(Number);
                const slotStart = hour * 60 + minute;
                const slotEnd = slotStart + this.gridInterval;
                const eventStart = eHour * 60 + eMin;
                return eventStart >= slotStart && eventStart < slotEnd;
            });
        },

        // Для обратной совместимости
        getEventsForRoomHour(dateStr, roomId, hour) {
            return this.getEventsForRoomSlot(dateStr, roomId, hour, 0);
        },

        getEventsWithoutRoomSlot(dateStr, hour, minute) {
            return this.events.filter(e => {
                const [eHour, eMin] = e.start_time.split(':').map(Number);
                const slotStart = hour * 60 + minute;
                const slotEnd = slotStart + this.gridInterval;
                const eventStart = eHour * 60 + eMin;
                return e.date === dateStr && !e.room_id && eventStart >= slotStart && eventStart < slotEnd;
            });
        },

        // Получить события без комнаты, начинающиеся в этом слоте
        getEventsStartingWithoutRoom(dateStr, hour, minute) {
            return this.events.filter(e => {
                if (e.date !== dateStr || e.room_id) return false;
                const [eHour, eMin] = e.start_time.split(':').map(Number);
                const slotStart = hour * 60 + minute;
                const slotEnd = slotStart + this.gridInterval;
                const eventStart = eHour * 60 + eMin;
                return eventStart >= slotStart && eventStart < slotEnd;
            });
        },

        // Для обратной совместимости
        getEventsWithoutRoomHour(dateStr, hour) {
            return this.getEventsWithoutRoomSlot(dateStr, hour, 0);
        },

        setDayViewMode(mode) {
            this.dayViewMode = mode;
        },

        // ========== EVENT DURATION & POSITIONING ==========

        // Получить длительность события в минутах
        getEventDurationMinutes(event) {
            if (!event.start_time || !event.end_time) return this.gridInterval;
            const [startH, startM] = event.start_time.split(':').map(Number);
            const [endH, endM] = event.end_time.split(':').map(Number);
            return (endH * 60 + endM) - (startH * 60 + startM);
        },

        // Получить высоту события в пикселях
        getEventHeightPx(event) {
            const durationMinutes = this.getEventDurationMinutes(event);
            // Высота = (длительность / интервал) * высота_слота
            const heightPx = (durationMinutes / this.gridInterval) * this.slotHeight;
            return Math.max(heightPx, this.slotHeight * 0.8); // минимальная высота
        },

        // Получить смещение события от начала слота (если начало события не совпадает с началом слота)
        getEventTopOffsetPx(event, slotHour, slotMinute) {
            if (!event.start_time) return 0;
            const [eventH, eventM] = event.start_time.split(':').map(Number);
            const eventStart = eventH * 60 + eventM;
            const slotStart = slotHour * 60 + slotMinute;
            const offsetMinutes = eventStart - slotStart;
            return (offsetMinutes / this.gridInterval) * this.slotHeight;
        },

        // Проверить, пересекается ли событие с конкретным временным слотом
        eventIntersectsSlot(event, slotHour, slotMinute) {
            if (!event.start_time || !event.end_time) return false;
            const [startH, startM] = event.start_time.split(':').map(Number);
            const [endH, endM] = event.end_time.split(':').map(Number);
            const eventStart = startH * 60 + startM;
            const eventEnd = endH * 60 + endM;
            const slotStart = slotHour * 60 + slotMinute;
            const slotEnd = slotStart + this.gridInterval;
            // Событие пересекается если его интервал перекрывается со слотом
            return eventStart < slotEnd && eventEnd > slotStart;
        },

        // Получить события для слота - показываем только те, что НАЧИНАЮТСЯ в этом слоте
        // (они будут визуально растянуты на следующие слоты)
        getEventsStartingInSlot(dateStr, hour, minute) {
            return this.events.filter(e => {
                if (e.date !== dateStr) return false;
                const [eHour, eMin] = e.start_time.split(':').map(Number);
                const slotStart = hour * 60 + minute;
                const slotEnd = slotStart + this.gridInterval;
                const eventStart = eHour * 60 + eMin;
                return eventStart >= slotStart && eventStart < slotEnd;
            });
        },

        // ========== OVERLAPPING EVENTS LAYOUT ==========

        // Проверить, пересекаются ли два события по времени
        eventsOverlap(event1, event2) {
            if (!event1.start_time || !event1.end_time || !event2.start_time || !event2.end_time) return false;
            const [s1H, s1M] = event1.start_time.split(':').map(Number);
            const [e1H, e1M] = event1.end_time.split(':').map(Number);
            const [s2H, s2M] = event2.start_time.split(':').map(Number);
            const [e2H, e2M] = event2.end_time.split(':').map(Number);
            const start1 = s1H * 60 + s1M;
            const end1 = e1H * 60 + e1M;
            const start2 = s2H * 60 + s2M;
            const end2 = e2H * 60 + e2M;
            return start1 < end2 && end1 > start2;
        },

        // Получить все события, пересекающиеся с данным событием
        getOverlappingEvents(event, dateStr) {
            return this.events.filter(e => {
                if (e.date !== dateStr) return false;
                return this.eventsOverlap(event, e);
            });
        },

        // Рассчитать layout для пересекающихся событий (колонки)
        // Возвращает { column: номер колонки, totalColumns: всего колонок }
        getEventLayout(event, dateStr) {
            const overlapping = this.getOverlappingEvents(event, dateStr);
            if (overlapping.length <= 1) {
                return { column: 0, totalColumns: 1 };
            }

            // Сортируем по времени начала, затем по id для стабильности
            overlapping.sort((a, b) => {
                const [aH, aM] = a.start_time.split(':').map(Number);
                const [bH, bM] = b.start_time.split(':').map(Number);
                const aStart = aH * 60 + aM;
                const bStart = bH * 60 + bM;
                if (aStart !== bStart) return aStart - bStart;
                return a.id - b.id;
            });

            // Алгоритм назначения колонок
            const columns = [];
            for (const e of overlapping) {
                // Найти первую свободную колонку
                let col = 0;
                while (true) {
                    // Проверить, занята ли колонка каким-либо пересекающимся событием
                    const occupied = columns.some(c => c.column === col && this.eventsOverlap(c.event, e));
                    if (!occupied) break;
                    col++;
                }
                columns.push({ event: e, column: col });
            }

            const eventData = columns.find(c => c.event.id === event.id);
            const totalColumns = Math.max(...columns.map(c => c.column)) + 1;

            return {
                column: eventData ? eventData.column : 0,
                totalColumns: totalColumns
            };
        },

        // Получить left позицию события в процентах
        getEventLeftPercent(event, dateStr) {
            const layout = this.getEventLayout(event, dateStr);
            return (layout.column / layout.totalColumns) * 100;
        },

        // Получить ширину события в процентах
        getEventWidthPercent(event, dateStr) {
            const layout = this.getEventLayout(event, dateStr);
            // Небольшой отступ между событиями
            return (100 / layout.totalColumns) - 1;
        },

        // Получить z-index события (короткие события сверху)
        getEventZIndex(event) {
            const duration = this.getEventDurationMinutes(event);
            // Короткие (30 мин) = z-index 85, длинные (120 мин) = z-index 40
            return Math.max(10, 100 - Math.floor(duration / 2));
        },

        // Для совместимости - старый метод теперь использует новый
        getEventDuration(event) {
            return Math.round(this.getEventDurationMinutes(event) / 60);
        },

        // ========== MODAL HELPERS ==========
        openCreateModal(dateStr, hour, minute = 0) {
            this.selectedDate = dateStr;
            this.selectedHour = hour;
            this.selectedMinute = minute;
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

        onDragOver(event, dateStr, hour, minute = 0) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            this.dragOver = { date: dateStr, hour: hour, minute: minute };
        },

        onDragLeave() {
            this.dragOver = null;
        },

        onDragEnd() {
            this.dragging = null;
            this.dragOver = null;
        },

        async onDrop(event, dateStr, hour, minute = 0) {
            event.preventDefault();
            const lessonId = parseInt(event.dataTransfer.getData('text/plain'));

            if (lessonId && dateStr && hour !== undefined) {
                const newStartTime = hour.toString().padStart(2, '0') + ':' + minute.toString().padStart(2, '0');
                await this.moveEvent(lessonId, dateStr, newStartTime);
            }

            this.dragging = null;
            this.dragOver = null;
        },

        isDragging(eventId) {
            return this.dragging === eventId;
        },

        isDropTarget(dateStr, hour, minute = 0) {
            return this.dragOver && this.dragOver.date === dateStr && this.dragOver.hour === hour && this.dragOver.minute === minute;
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

// Глобальная функция для загрузки учителей группы (используется в модальных формах)
window.loadTeachersForGroup = async function(groupId, selectElement, teachersUrl) {
    if (!selectElement) return;

    if (!groupId) {
        selectElement.innerHTML = '<option value="">Выберите преподавателя</option>';
        selectElement.disabled = true;
        return Promise.resolve([]);
    }

    try {
        const response = await QazFetch.post(teachersUrl, { id: groupId });
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
        return response || [];
    } catch (error) {
        console.error('Error loading teachers:', error);
        return [];
    }
};
