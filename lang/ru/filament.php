<?php

return [
    // Navigation Labels
    'parcels' => 'Посылки',
    'clients' => 'Клиенты',
    
    // Model Labels
    'parcel' => 'Посылка',
    'client' => 'Клиент',
    
    // Common Fields
    'id' => 'ID',
    'status' => 'Статус',
    'created_at' => 'Создано',
    'updated_at' => 'Обновлено',
    
    // Parcel Fields
    'track_number' => 'Трек-номер',
    'weight' => 'Вес (кг)',
    'is_banned' => 'Заблокировано',
    'china_uploaded_at' => 'Время прибытия в Китай',
    'uzb_uploaded_at' => 'Время прибытия в Узбекистан',
    'client_name' => 'Клиент',
    
    // Client Fields
    'full_name' => 'Полное имя',
    'phone' => 'Номер телефона',
    'telegram_id' => 'Telegram ID',
    'uid' => 'UID',
    'lang' => 'Язык',
    'first_name' => 'Имя',
    'last_name' => 'Фамилия',
    'username' => 'Имя пользователя',
    'address' => 'Адрес',
    'registration_date' => 'Дата регистрации',
    
    // Status Labels
    'status_created' => 'Создано',
    'status_arrived_china' => 'Прибыло в Китай',
    'status_arrived_uzb' => 'Прибыло в Узбекистан',
    'status_delivered' => 'Доставлено',
    
    // Stats Labels
    'total_parcels' => 'Всего посылок',
    'total_clients' => 'Всего клиентов',
    'banned_parcels' => 'Заблокированные посылки',
    'today_clients' => 'Клиенты сегодня',
    'weekly_clients' => 'Клиенты за неделю',
    'monthly_clients' => 'Клиенты за месяц',
    'telegram_users' => 'Пользователи Telegram',
    'active_clients' => 'Активные клиенты',
    'active_rate' => 'уровень активности',
    'vs_yesterday' => 'по сравнению со вчера',
    'vs_last_week' => 'по сравнению с прошлой неделей',
    'vs_last_month' => 'по сравнению с прошлым месяцем',
    'telegram_integration' => 'Интеграция Telegram',
    'clients_with_parcels' => 'клиенты с посылками',
    
    // Placeholders
    'unassigned' => 'Не назначено',
    'not_set' => '—',
    
    // Actions
    'edit' => 'Редактировать',
    'delete' => 'Удалить',
    'view' => 'Просмотр',
    'create' => 'Создать',
    'save' => 'Сохранить',
    'cancel' => 'Отменить',
    'import' => 'Импорт',
    'export' => 'Экспорт',
    
    // Messages
    'track_number_copied' => 'Трек-номер скопирован',
    'import_success' => 'Данные успешно импортированы',
    'export_success' => 'Данные успешно экспортированы',
    
    // Filters
    'filter_status' => 'Статус',
    'filter_unassigned' => 'Неназначенные клиенты',
    'filter_all' => 'Все',
    
    // Bulk Actions
    'bulk_delete' => 'Удалить выбранные элементы',
    'bulk_export' => 'Экспортировать выбранные элементы',
    
    // Import/Export
    'select_file' => 'Выберите файл',
    'upload_file' => 'Загрузить файл',
    'download_template' => 'Скачать шаблон',
    'import_description' => 'Загрузите файл Excel или CSV',
    'export_description' => 'Скачать данные в формате Excel',
    
    // Import Actions
    'import_china' => 'Импорт из Китая',
    'import_uzbekistan' => 'Импорт из Узбекистана',
    'import_china_modal_title' => 'Импорт Excel из Китая',
    'import_uzbekistan_modal_title' => 'Импорт Excel из Узбекистана',
    'china_excel_file' => 'Excel файл (Китай)',
    'uzbekistan_excel_file' => 'Excel файл (Узбекистан)',
    'china_excel_help' => 'Excel файл должен содержать столбцы: track_number, weight(KG), is_banned',
    'uzbekistan_excel_help' => 'Excel файл должен содержать столбец: track_number',
    'import_success_china' => 'Excel файл из Китая успешно импортирован!',
    'import_success_uzbekistan' => 'Excel файл из Узбекистана успешно импортирован!',
    'import_error' => 'Ошибка импорта',
    'import_action' => 'Импортировать',
    
    // Widget titles
    'activity_chart_title' => 'Активность за последние 14 дней',
    'status_distribution_title' => 'Распределение по статусам',
    'recent_parcels_title' => 'Последние посылки',
    
    // Additional stats labels
    'last_7_days' => 'последние 7 дней',
    'no_new_parcels' => 'новых посылок нет',
    'new_parcels' => 'новые посылки',
    'in_china' => 'в Китае',
    'in_uzbekistan' => 'в Узбекистане',
    'delivery_rate' => 'процент доставки',
    'active_clients' => 'активные клиенты',
    'restricted_items' => 'заблокированные товары',
    'last_updated' => 'последнее обновление',
    'unassigned' => 'не назначен',
    
    // Filters
    'filter_banned' => 'Заблокированные товары',
    'filter_older_than_3_days' => 'Старше 3 дней',
];