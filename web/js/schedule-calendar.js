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
        dayViewMode: 'rooms', // 'timeline' | 'rooms' - режим Day View (по умолчанию кабинеты)
        events: [],
        loading: false,

        // Мини-календарь
        miniCalendarDate: new Date(),  // месяц, отображаемый в мини-календаре

        // Кэш для оптимизации производительности
        _layoutCache: {},           // {eventId: {column, totalColumns}}
        _eventsByDate: {},          // {dateStr: [events]}
        _eventsBySlot: {},          // {dateStr-hour-minute: [events]} - предгруппированные по слотам
        _eventsByRoomSlot: {},      // {dateStr-roomId-hour-minute: [events]}
        _eventsNoRoomSlot: {},      // {dateStr-hour-minute: [events]} - без комнаты
        _timeSlotsCache: null,      // кэш для timeSlots
        _timeSlotsInterval: null,   // интервал для которого был рассчитан кэш
        _weekDaysCache: null,       // кэш для weekDays
        _weekDaysDate: null,        // дата для которой был рассчитан кэш weekDays

        // Предвычисленная текущая дата для шаблона (избегаем повторных вызовов formatDate)
        get currentDateStr() {
            return this.formatDate(this.currentDate);
        },

        // Сегодняшняя дата в формате YYYY-MM-DD (кэшируется автоматически - один вызов formatDate)
        _todayDateStr: null,
        _todayDateStrDay: null,
        get todayDateStr() {
            const today = new Date();
            const todayDay = today.toDateString();
            // Пересчитываем только если день изменился (раз в сутки)
            if (this._todayDateStrDay !== todayDay) {
                this._todayDateStrDay = todayDay;
                this._todayDateStr = this.formatDate(today);
            }
            return this._todayDateStr;
        },

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
        // Связи преподаватель-группа для зависимых фильтров
        teacherGroups: [],   // [{teacher_id, group_id}]

        // Модальные окна
        selectedEvent: null,
        selectedDate: null,
        selectedHour: null,
        selectedMinute: 0,
        selectedRoomId: null,

        // Посещаемость
        savingAttendance: null,

        // Drag & Drop
        dragging: null,
        dragOver: null,

        // Hover колонка кабинета
        hoveredRoomId: null,

        // Линия текущего времени
        currentTime: new Date(),
        timeLineTimer: null,

        // Save as template
        saveTemplateForm: {
            name: '',
            description: ''
        },
        savingTemplate: false,

        // URLs (передаются из PHP)
        urls: config.urls || {},

        // Начальные данные (settings, filters) - передаются из PHP для ускорения загрузки
        initialData: config.initialData || null,

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
            this.loadFiltersFromStorage();

            // Если есть initialData из PHP - используем его (без AJAX)
            if (this.initialData) {
                // Settings
                if (this.initialData.settings) {
                    this.gridInterval = parseInt(this.initialData.settings.grid_interval) || 60;
                    this.viewMode = this.initialData.settings.view_mode || 'week';
                }
                // Filters
                if (this.initialData.filters) {
                    this.filterOptions.groups = this.initialData.filters.groups || [];
                    this.filterOptions.teachers = this.initialData.filters.teachers || [];
                    this.filterOptions.rooms = this.initialData.filters.rooms || [];
                    this.teacherGroups = this.initialData.filters.teacherGroups || [];
                }
            } else {
                // Fallback: загружаем через AJAX
                await Promise.all([
                    this.loadSettings(),
                    this.fetchFilters()
                ]);
            }

            // События всегда загружаем через AJAX (динамические данные)
            await this.fetchEvents();
            this.startTimeLineTimer();
        },

        destroy() {
            this.stopTimeLineTimer();
        },

        startTimeLineTimer() {
            this.currentTime = new Date();
            this.timeLineTimer = setInterval(() => {
                this.currentTime = new Date();
            }, 60000); // обновлять каждую минуту
        },

        stopTimeLineTimer() {
            if (this.timeLineTimer) {
                clearInterval(this.timeLineTimer);
                this.timeLineTimer = null;
            }
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
                // Пересчитываем стили при смене интервала (высота зависит от gridInterval)
                this._precomputeEventStyles();

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

        // Getter для временных слотов (ОПТИМИЗИРОВАНО с кэшированием)
        get timeSlots() {
            // Возвращаем кэш если интервал не изменился
            if (this._timeSlotsCache && this._timeSlotsInterval === this.gridInterval) {
                return this._timeSlotsCache;
            }

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

            // Сохраняем в кэш
            this._timeSlotsCache = slots;
            this._timeSlotsInterval = this.gridInterval;
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

        // ========== ЛИНИЯ ТЕКУЩЕГО ВРЕМЕНИ ==========

        // Позиция линии текущего времени (в пикселях от начала сетки)
        get timeLinePosition() {
            const hours = this.currentTime.getHours();
            const minutes = this.currentTime.getMinutes();
            const totalMinutes = (hours - this.workStart) * 60 + minutes;
            const pixelsPerMinute = this.slotHeight / this.gridInterval;
            return totalMinutes * pixelsPerMinute;
        },

        // Текущее время в формате HH:MM
        get currentTimeFormatted() {
            const hours = this.currentTime.getHours().toString().padStart(2, '0');
            const minutes = this.currentTime.getMinutes().toString().padStart(2, '0');
            return hours + ':' + minutes;
        },

        // Видна ли линия времени (текущее время в рабочих часах)
        get isTimeLineVisible() {
            const hours = this.currentTime.getHours();
            const minutes = this.currentTime.getMinutes();
            return hours >= this.workStart && (hours < this.workEnd || (hours === this.workEnd && minutes === 0));
        },

        // Является ли текущий просмотр сегодняшним днём (для Day view)
        get isTodayInView() {
            const todayStr = this.todayDateStr;
            if (this.viewMode === 'day') {
                return this.currentDateStr === todayStr;
            }
            if (this.viewMode === 'week') {
                // weekDays уже содержит dateStr для каждого дня
                return this.weekDays.some(d => d.dateStr === todayStr);
            }
            return false;
        },

        // Индекс сегодняшнего дня в неделе (0-6) или -1 если не в текущей неделе
        get todayIndexInWeek() {
            const todayStr = this.todayDateStr;
            return this.weekDays.findIndex(d => d.dateStr === todayStr);
        },

        // Получить массив дат текущей недели
        getWeekDays() {
            const start = this.getWeekStart(this.currentDate);
            const days = [];
            for (let i = 0; i < 7; i++) {
                const d = new Date(start);
                d.setDate(start.getDate() + i);
                days.push(d);
            }
            return days;
        },

        // ========== NAVIGATION ==========
        goToToday() {
            this.currentDate = new Date();
            this.miniCalendarDate = new Date();  // синхронизируем мини-календарь
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
            this.miniCalendarDate = new Date(d);  // синхронизируем мини-календарь
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
            this.miniCalendarDate = new Date(d);  // синхронизируем мини-календарь
            this.fetchEvents();
        },

        setViewMode(mode) {
            if (this.viewMode === mode) return;

            // Показываем loading сразу для отзывчивости UI
            this.loading = true;
            this.viewMode = mode;

            // Сбрасываем кэш weekDays при смене режима
            this._weekDaysCache = null;

            // Используем requestAnimationFrame для плавности
            requestAnimationFrame(() => {
                this.saveViewMode(mode);
                this.fetchEvents();
            });
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
            this.miniCalendarDate = new Date(date);  // синхронизируем мини-календарь
            this.viewMode = 'day';
            this.saveViewMode('day');
            this.fetchEvents();
        },

        goToWeekStart() {
            const start = this.getWeekStart(new Date());
            this.currentDate = start;
            this.miniCalendarDate = new Date(start);
            this.fetchEvents();
        },

        // ========== MINI CALENDAR ==========
        get miniCalendarTitle() {
            const month = this.miniCalendarDate.getMonth();
            const year = this.miniCalendarDate.getFullYear();
            return this.monthNames[month] + ' ' + year;
        },

        // Плоский массив дней для мини-календаря (42 дня = 6 недель)
        get miniCalendarDays() {
            const date = this.miniCalendarDate;
            const monthStart = new Date(date.getFullYear(), date.getMonth(), 1);
            const monthEnd = new Date(date.getFullYear(), date.getMonth() + 1, 0);
            const start = this.getWeekStart(monthStart);
            const todayStr = this.todayDateStr;
            const currentMonth = date.getMonth();

            const days = [];
            let current = new Date(start);

            // Генерируем 42 дня (6 недель)
            for (let i = 0; i < 42; i++) {
                const d = new Date(current);
                const dateStr = this.formatDate(d);
                days.push({
                    date: d,
                    dateStr: dateStr,
                    dayNum: d.getDate(),
                    isToday: dateStr === todayStr,
                    isCurrentMonth: d.getMonth() === currentMonth
                });
                current.setDate(current.getDate() + 1);
            }
            return days;
        },

        miniCalendarPrev() {
            const d = new Date(this.miniCalendarDate);
            d.setMonth(d.getMonth() - 1);
            this.miniCalendarDate = d;
        },

        miniCalendarNext() {
            const d = new Date(this.miniCalendarDate);
            d.setMonth(d.getMonth() + 1);
            this.miniCalendarDate = d;
        },

        miniCalendarHasEvents(dateStr) {
            return this._eventsByDate[dateStr] && this._eventsByDate[dateStr].length > 0;
        },

        // ========== DATE HELPERS ==========
        formatDate(date) {
            const d = new Date(date);
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
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

        // ОПТИМИЗИРОВАНО: кэширование weekDays
        get weekDays() {
            const currentDateStrVal = this.currentDateStr;
            if (this._weekDaysCache && this._weekDaysDate === currentDateStrVal) {
                return this._weekDaysCache;
            }

            const start = this.getWeekStart(this.currentDate);
            const todayStr = this.todayDateStr;
            const days = [];
            for (let i = 0; i < 7; i++) {
                const d = new Date(start);
                d.setDate(start.getDate() + i);
                const dateStr = this.formatDate(d);
                days.push({
                    date: d,
                    dateStr: dateStr,
                    dayName: this.daysOfWeek[i],
                    dayNum: d.getDate(),
                    isToday: dateStr === todayStr
                });
            }

            this._weekDaysCache = days;
            this._weekDaysDate = currentDateStrVal;
            return days;
        },

        get monthWeeks() {
            const monthStart = this.getMonthStart(this.currentDate);
            const monthEnd = this.getMonthEnd(this.currentDate);
            const start = this.getWeekStart(monthStart);
            const todayStr = this.todayDateStr;
            const currentMonth = this.currentDate.getMonth();

            const weeks = [];
            let current = new Date(start);

            while (current <= monthEnd || weeks.length < 6) {
                const week = [];
                for (let i = 0; i < 7; i++) {
                    const d = new Date(current);
                    const dateStr = this.formatDate(d);
                    week.push({
                        date: d,
                        dateStr: dateStr,
                        dayNum: d.getDate(),
                        isToday: dateStr === todayStr,
                        isCurrentMonth: d.getMonth() === currentMonth
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

        // Кабинеты для отображения в режиме "День" (с учётом фильтра)
        get displayedRooms() {
            if (this.filters.rooms.length === 0) {
                return this.filterOptions.rooms;
            }
            return this.filterOptions.rooms.filter(room => this.filters.rooms.includes(room.id));
        },

        // Отфильтрованные группы (показываем все доступные для фильтрации)
        get filteredGroups() {
            let groups = [...this.filterOptions.groups];

            // Если выбраны преподаватели - дополнительно фильтруем по связям
            if (this.filters.teachers.length > 0) {
                const groupIdsByTeacher = new Set();
                this.teacherGroups.forEach(tg => {
                    if (this.filters.teachers.includes(tg.teacher_id)) {
                        groupIdsByTeacher.add(tg.group_id);
                    }
                });
                groups = groups.filter(g => groupIdsByTeacher.has(g.id));
            }

            return groups;
        },

        // Отфильтрованные преподаватели (показываем всех доступных для фильтрации)
        get filteredTeachers() {
            let teachers = [...this.filterOptions.teachers];

            // Если выбраны группы - дополнительно фильтруем по связям
            if (this.filters.groups.length > 0) {
                const teacherIdsByGroup = new Set();
                this.teacherGroups.forEach(tg => {
                    if (this.filters.groups.includes(tg.group_id)) {
                        teacherIdsByGroup.add(tg.teacher_id);
                    }
                });
                teachers = teachers.filter(t => teacherIdsByGroup.has(t.id));
            }

            return teachers;
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
                // Предрасчёт кэшей для производительности
                this._buildEventCaches();
            } catch (error) {
                console.error('Error loading events:', error);
                this.events = [];
            } finally {
                this.loading = false;
            }
        },

        // Предрасчёт кэшей событий для оптимизации рендеринга
        _buildEventCaches() {
            // Очищаем кэши
            this._layoutCache = {};
            this._eventsByDate = {};
            this._eventsBySlot = {};
            this._eventsByRoomSlot = {};
            this._eventsNoRoomSlot = {};

            // Группируем события по дате и слотам
            for (const event of this.events) {
                const dateStr = event.date;

                // По дате
                if (!this._eventsByDate[dateStr]) {
                    this._eventsByDate[dateStr] = [];
                }
                this._eventsByDate[dateStr].push(event);

                // Определяем слот начала события
                const [eHour, eMin] = event.start_time.split(':').map(Number);
                const slotMinute = Math.floor(eMin / this.gridInterval) * this.gridInterval;
                const slotKey = `${dateStr}-${eHour}-${slotMinute}`;

                // По слотам (для Week/Day Timeline)
                if (!this._eventsBySlot[slotKey]) {
                    this._eventsBySlot[slotKey] = [];
                }
                this._eventsBySlot[slotKey].push(event);

                // По комнатам и слотам (для Day Rooms)
                if (event.room_id) {
                    const roomSlotKey = `${dateStr}-${event.room_id}-${eHour}-${slotMinute}`;
                    if (!this._eventsByRoomSlot[roomSlotKey]) {
                        this._eventsByRoomSlot[roomSlotKey] = [];
                    }
                    this._eventsByRoomSlot[roomSlotKey].push(event);
                } else {
                    // Без комнаты
                    if (!this._eventsNoRoomSlot[slotKey]) {
                        this._eventsNoRoomSlot[slotKey] = [];
                    }
                    this._eventsNoRoomSlot[slotKey].push(event);
                }
            }

            // Предрасчёт layouts для всех событий (избегаем O(n²) при рендере)
            for (const dateStr in this._eventsByDate) {
                const dayEvents = this._eventsByDate[dateStr];
                this._calculateDayLayouts(dayEvents, dateStr);
            }

            // Предрасчёт стилей для каждого события (избегаем вычислений в шаблоне)
            this._precomputeEventStyles();
        },

        // Предрасчёт стилей для всех событий
        _precomputeEventStyles() {
            for (const event of this.events) {
                const layout = this._layoutCache[event.id] || { column: 0, totalColumns: 1 };

                // Вычисляем длительность в минутах
                const [startH, startM] = event.start_time.split(':').map(Number);
                const [endH, endM] = event.end_time.split(':').map(Number);
                const durationMinutes = (endH * 60 + endM) - (startH * 60 + startM);

                // Высота события
                const heightPx = Math.max((durationMinutes / this.gridInterval) * this.slotHeight, this.slotHeight * 0.8);

                // Позиция и ширина для пересекающихся событий
                const leftPercent = (layout.column / layout.totalColumns) * 100;
                const widthPercent = (100 / layout.totalColumns) - 1;

                // z-index (короткие события сверху)
                const zIndex = Math.max(10, 100 - Math.floor(durationMinutes / 2));

                // Сохраняем предрасчитанные значения прямо в событии
                event._style = {
                    height: heightPx,
                    left: leftPercent,
                    width: widthPercent,
                    zIndex: zIndex,
                    startMinutes: startH * 60 + startM  // для расчёта top offset
                };
            }
        },

        // Рассчитать layouts для всех событий одного дня
        _calculateDayLayouts(dayEvents, dateStr) {
            if (!dayEvents || dayEvents.length === 0) return;

            // Сортируем события по времени начала
            const sorted = [...dayEvents].sort((a, b) => {
                const [aH, aM] = a.start_time.split(':').map(Number);
                const [bH, bM] = b.start_time.split(':').map(Number);
                const aStart = aH * 60 + aM;
                const bStart = bH * 60 + bM;
                if (aStart !== bStart) return aStart - bStart;
                return a.id - b.id;
            });

            // Для каждого события находим пересечения и назначаем колонки
            const eventColumns = new Map(); // eventId -> column
            const eventTotals = new Map();  // eventId -> totalColumns

            for (let i = 0; i < sorted.length; i++) {
                const event = sorted[i];
                const overlapping = this._findOverlapping(event, sorted);

                if (overlapping.length <= 1) {
                    eventColumns.set(event.id, 0);
                    eventTotals.set(event.id, 1);
                    continue;
                }

                // Назначаем колонки для пересекающихся событий
                const columns = [];
                for (const e of overlapping) {
                    let col = 0;
                    while (true) {
                        const occupied = columns.some(c =>
                            c.column === col && this._eventsOverlapFast(c.event, e)
                        );
                        if (!occupied) break;
                        col++;
                    }
                    columns.push({ event: e, column: col });
                }

                const totalColumns = Math.max(...columns.map(c => c.column)) + 1;

                // Сохраняем в кэш
                for (const c of columns) {
                    eventColumns.set(c.event.id, c.column);
                    eventTotals.set(c.event.id, totalColumns);
                }
            }

            // Сохраняем в глобальный кэш
            for (const event of dayEvents) {
                this._layoutCache[event.id] = {
                    column: eventColumns.get(event.id) || 0,
                    totalColumns: eventTotals.get(event.id) || 1
                };
            }
        },

        // Быстрая проверка пересечения двух событий
        _eventsOverlapFast(event1, event2) {
            if (event1.id === event2.id) return false;
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

        // Найти все пересекающиеся события (оптимизированный)
        _findOverlapping(event, sortedEvents) {
            const result = [];
            const [eH, eM] = event.start_time.split(':').map(Number);
            const [endH, endM] = event.end_time.split(':').map(Number);
            const eventStart = eH * 60 + eM;
            const eventEnd = endH * 60 + endM;

            for (const e of sortedEvents) {
                const [sH, sM] = e.start_time.split(':').map(Number);
                const [seH, seM] = e.end_time.split(':').map(Number);
                const start = sH * 60 + sM;
                const end = seH * 60 + seM;

                // Если событие начинается после окончания нашего - можно остановиться
                // (события отсортированы по времени начала)
                if (start >= eventEnd) continue;

                // Проверяем пересечение
                if (start < eventEnd && end > eventStart) {
                    result.push(e);
                }
            }
            return result;
        },

        async fetchFilters() {
            try {
                const response = await QazFetch.get(this.urls.filters);
                if (response) {
                    this.filterOptions.groups = response.groups || [];
                    this.filterOptions.teachers = response.teachers || [];
                    this.filterOptions.rooms = response.rooms || [];
                    this.teacherGroups = response.teacherGroups || [];
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
        // ОПТИМИЗИРОВАНО: использует кэш по датам
        getEventsForSlot(dateStr, hour, minute) {
            const dayEvents = this._eventsByDate[dateStr];
            if (!dayEvents || dayEvents.length === 0) return [];

            const slotStart = hour * 60 + minute;
            const slotEnd = slotStart + this.gridInterval;

            return dayEvents.filter(e => {
                const [eHour, eMin] = e.start_time.split(':').map(Number);
                const eventStart = eHour * 60 + eMin;
                return eventStart >= slotStart && eventStart < slotEnd;
            });
        },

        // Для обратной совместимости (используется при gridInterval = 60)
        getEventsForDateHour(dateStr, hour) {
            return this.getEventsForSlot(dateStr, hour, 0);
        },

        // ОПТИМИЗИРОВАНО: использует кэш по датам
        getEventsForDate(dateStr) {
            return this._eventsByDate[dateStr] || [];
        },

        // ОПТИМИЗИРОВАНО: использует кэш по датам
        getEventsForRoomSlot(dateStr, roomId, hour, minute) {
            const dayEvents = this._eventsByDate[dateStr];
            if (!dayEvents || dayEvents.length === 0) return [];

            const slotStart = hour * 60 + minute;
            const slotEnd = slotStart + this.gridInterval;

            return dayEvents.filter(e => {
                if (e.room_id !== roomId) return false;
                const [eHour, eMin] = e.start_time.split(':').map(Number);
                const eventStart = eHour * 60 + eMin;
                return eventStart >= slotStart && eventStart < slotEnd;
            });
        },

        // Получить события для комнаты, начинающиеся в этом слоте (для отображения длительности)
        // ОПТИМИЗИРОВАНО: O(1) поиск по предгруппированному кэшу
        getEventsStartingInRoomSlot(dateStr, roomId, hour, minute) {
            const roomSlotKey = `${dateStr}-${roomId}-${hour}-${minute}`;
            return this._eventsByRoomSlot[roomSlotKey] || [];
        },

        // Для обратной совместимости
        getEventsForRoomHour(dateStr, roomId, hour) {
            return this.getEventsForRoomSlot(dateStr, roomId, hour, 0);
        },

        // ОПТИМИЗИРОВАНО: использует кэш по датам
        getEventsWithoutRoomSlot(dateStr, hour, minute) {
            const dayEvents = this._eventsByDate[dateStr];
            if (!dayEvents || dayEvents.length === 0) return [];

            const slotStart = hour * 60 + minute;
            const slotEnd = slotStart + this.gridInterval;

            return dayEvents.filter(e => {
                if (e.room_id) return false;
                const [eHour, eMin] = e.start_time.split(':').map(Number);
                const eventStart = eHour * 60 + eMin;
                return eventStart >= slotStart && eventStart < slotEnd;
            });
        },

        // Получить события без комнаты, начинающиеся в этом слоте
        // ОПТИМИЗИРОВАНО: O(1) поиск по предгруппированному кэшу
        getEventsStartingWithoutRoom(dateStr, hour, minute) {
            const slotKey = `${dateStr}-${hour}-${minute}`;
            return this._eventsNoRoomSlot[slotKey] || [];
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
        // ОПТИМИЗИРОВАНО: использует предрасчитанные startMinutes
        getEventTopOffsetPx(event, slotHour, slotMinute) {
            const eventStart = event._style ? event._style.startMinutes : 0;
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
        // ОПТИМИЗИРОВАНО: O(1) поиск по предгруппированному кэшу
        getEventsStartingInSlot(dateStr, hour, minute) {
            const slotKey = `${dateStr}-${hour}-${minute}`;
            return this._eventsBySlot[slotKey] || [];
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
        // ОПТИМИЗИРОВАНО: использует предрасчитанный кэш
        getEventLayout(event, dateStr) {
            // Используем кэш если доступен
            if (this._layoutCache[event.id]) {
                return this._layoutCache[event.id];
            }
            // Fallback для событий без кэша (не должно происходить)
            return { column: 0, totalColumns: 1 };
        },

        // Получить left позицию события в процентах
        // ОПТИМИЗИРОВАНО: использует кэш
        getEventLeftPercent(event, dateStr) {
            const layout = this._layoutCache[event.id] || { column: 0, totalColumns: 1 };
            return (layout.column / layout.totalColumns) * 100;
        },

        // Получить ширину события в процентах
        // ОПТИМИЗИРОВАНО: использует кэш
        getEventWidthPercent(event, dateStr) {
            const layout = this._layoutCache[event.id] || { column: 0, totalColumns: 1 };
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
        openCreateModal(dateStr, hour, minute = 0, roomId = null) {
            this.selectedDate = dateStr;
            this.selectedHour = hour;
            this.selectedMinute = minute;
            this.selectedRoomId = roomId;
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

        // ========== ATTENDANCE (inline) ==========
        async savePupilStatus(pupilId, status) {
            if (this.savingAttendance) return;
            if (!this.selectedEvent) return;

            this.savingAttendance = pupilId;

            try {
                const response = await QazFetch.post(this.urls.saveAttendance, {
                    lesson_id: this.selectedEvent.id,
                    pupil_id: pupilId,
                    status: status
                });

                if (response && response.success) {
                    // Обновляем статус в selectedEvent (используем == для сравнения с разными типами)
                    const pupil = this.selectedEvent.pupils.find(p => p.id == pupilId);
                    if (pupil) {
                        pupil.status = parseInt(response.status);
                        pupil.status_label = response.status_label;
                    }
                } else {
                    QazToast.error(response?.message || 'Ошибка сохранения');
                }
            } catch (error) {
                console.error('Attendance save error:', error);
                QazToast.error('Ошибка сохранения');
            } finally {
                this.savingAttendance = null;
            }
        },

        async setAllPupilsStatus(status) {
            if (this.savingAttendance) return;
            if (!this.selectedEvent?.pupils?.length) return;

            this.savingAttendance = 'all';

            try {
                for (const pupil of this.selectedEvent.pupils) {
                    // Используем parseInt для корректного сравнения
                    if (parseInt(pupil.status) !== status) {
                        const response = await QazFetch.post(this.urls.saveAttendance, {
                            lesson_id: this.selectedEvent.id,
                            pupil_id: pupil.id,
                            status: status
                        });

                        if (response && response.success) {
                            pupil.status = parseInt(response.status);
                            pupil.status_label = response.status_label;
                        }
                    }
                }
                QazToast.success('Посещаемость сохранена');
            } catch (error) {
                console.error('Attendance save error:', error);
                QazToast.error('Ошибка сохранения');
            } finally {
                this.savingAttendance = null;
            }
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

        async moveEvent(id, newDate, newStartTime, roomId = null) {
            try {
                const response = await QazFetch.post(this.urls.move, {
                    id: id,
                    newDate: newDate,
                    newStartTime: newStartTime,
                    roomId: roomId
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

        onDragOver(event, dateStr, hour, minute = 0, roomId = null) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            this.dragOver = { date: dateStr, hour: hour, minute: minute, roomId: roomId };
        },

        onDragLeave() {
            this.dragOver = null;
        },

        onDragEnd() {
            this.dragging = null;
            this.dragOver = null;
        },

        async onDrop(event, dateStr, hour, minute = 0, roomId = null) {
            event.preventDefault();
            const lessonId = parseInt(event.dataTransfer.getData('text/plain'));

            if (lessonId && dateStr && hour !== undefined) {
                const newStartTime = hour.toString().padStart(2, '0') + ':' + minute.toString().padStart(2, '0');
                await this.moveEvent(lessonId, dateStr, newStartTime, roomId);
            }

            this.dragging = null;
            this.dragOver = null;
        },

        isDragging(eventId) {
            return this.dragging === eventId;
        },

        isDropTarget(dateStr, hour, minute = 0, roomId = null) {
            if (!this.dragOver) return false;
            return this.dragOver.date === dateStr
                && this.dragOver.hour === hour
                && this.dragOver.minute === minute
                && this.dragOver.roomId === roomId;
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
        },

        // ========== SAVE AS TEMPLATE ==========
        openSaveAsTemplateModal() {
            // Получаем начало и конец текущей недели
            const weekStart = this.getWeekStart(this.currentDate);
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekEnd.getDate() + 6);

            // Формируем название по умолчанию
            const startStr = weekStart.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' });
            const endStr = weekEnd.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' });

            this.saveTemplateForm = {
                name: `Расписание ${startStr} - ${endStr}`,
                description: ''
            };
            this.$dispatch('open-modal', 'save-template-modal');
        },

        getWeekRangeText() {
            const weekStart = this.getWeekStart(this.currentDate);
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekEnd.getDate() + 6);
            const startStr = weekStart.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long' });
            const endStr = weekEnd.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long' });
            return `${startStr} — ${endStr}`;
        },

        getWeekEventsCount() {
            const weekStart = this.getWeekStart(this.currentDate);
            let count = 0;
            for (let i = 0; i < 7; i++) {
                const d = new Date(weekStart);
                d.setDate(d.getDate() + i);
                const dateStr = this.formatDate(d);
                const dayEvents = this._eventsByDate[dateStr] || [];
                count += dayEvents.length;
            }
            return count;
        },

        async saveAsTemplate() {
            if (!this.saveTemplateForm.name) {
                QazToast.error('Введите название шаблона');
                return;
            }

            const weekEventsCount = this.getWeekEventsCount();
            if (weekEventsCount === 0) {
                QazToast.error('В текущей неделе нет занятий для сохранения');
                return;
            }

            this.savingTemplate = true;
            try {
                const weekStart = this.getWeekStart(this.currentDate);
                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekEnd.getDate() + 6);

                const response = await QazFetch.post(this.urls.createTemplateFromSchedule, {
                    name: this.saveTemplateForm.name,
                    description: this.saveTemplateForm.description,
                    date_start: this.formatDate(weekStart),
                    date_end: this.formatDate(weekEnd)
                });

                if (response.success) {
                    QazToast.success(response.message || 'Шаблон создан');
                    this.$dispatch('close-modal', 'save-template-modal');
                    if (response.redirect) {
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1000);
                    }
                } else {
                    QazToast.error(response.message || 'Ошибка создания шаблона');
                }
            } catch (error) {
                console.error('Error saving template:', error);
                QazToast.error('Ошибка сети');
            } finally {
                this.savingTemplate = false;
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
