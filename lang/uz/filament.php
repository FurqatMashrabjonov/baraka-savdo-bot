<?php

return [
    // Navigation Labels
    'parcels' => 'Pochtalar',
    'clients' => 'Klientlar',
    
    // Model Labels
    'parcel' => 'Pochta',
    'client' => 'Klient',
    
    // Common Fields
    'id' => 'ID',
    'status' => 'Holati',
    'created_at' => 'Yaratilgan',
    'updated_at' => 'Yangilangan',
    
    // Parcel Fields
    'track_number' => 'Trek raqami',
    'weight' => 'Og\'irligi (kg)',
    'is_banned' => 'Ta\'qiqlangan',
    'china_uploaded_at' => 'Xitoyga kelgan vaqti',
    'uzb_uploaded_at' => 'O\'zbekistonga kelgan vaqti',
    'client_name' => 'Mijoz',
    
    // Client Fields
    'full_name' => 'To\'liq ismi',
    'phone' => 'Telefon raqami',
    'telegram_id' => 'Telegram ID',
    'uid' => 'UID',
    'lang' => 'Til',
    'first_name' => 'Ismi',
    'last_name' => 'Familiyasi',
    'username' => 'Foydalanuvchi nomi',
    'address' => 'Manzil',
    'registration_date' => 'Ro\'yxatga olingan vaqti',
    
    // Status Labels
    'status_created' => 'Yaratilgan',
    'status_arrived_china' => 'Xitoyga kelgan',
    'status_arrived_uzb' => 'O\'zbekistonga kelgan',
    'status_delivered' => 'Yetkazilgan',
    
    // Stats Labels
    'total_parcels' => 'Jami posilkalar',
    'total_clients' => 'Jami mijozlar',
    'banned_parcels' => 'Ta\'qiqlangan posilkalar',
    'today_clients' => 'Bugungi mijozlar',
    'weekly_clients' => 'Haftalik mijozlar',
    'monthly_clients' => 'Oylik mijozlar',
    'telegram_users' => 'Telegram foydalanuvchilar',
    'active_clients' => 'Faol mijozlar',
    'active_rate' => 'faollik darajasi',
    'vs_yesterday' => 'kechaga nisbatan',
    'vs_last_week' => 'o\'tgan haftaga nisbatan',
    'vs_last_month' => 'o\'tgan oyga nisbatan',
    'telegram_integration' => 'Telegram integratsiyasi',
    'clients_with_parcels' => 'posilkalari bor mijozlar',
    
    // Placeholders
    'unassigned' => 'Tayinlanmagan',
    'not_set' => '—',
    
    // Actions
    'edit' => 'Tahrirlash',
    'delete' => 'O\'chirish',
    'view' => 'Ko\'rish',
    'create' => 'Yaratish',
    'save' => 'Saqlash',
    'cancel' => 'Bekor qilish',
    'import' => 'Import qilish',
    'export' => 'Export qilish',
    
    // Messages
    'track_number_copied' => 'Trek raqami nusxalandi',
    'import_success' => 'Ma\'lumotlar muvaffaqiyatli import qilindi',
    'export_success' => 'Ma\'lumotlar muvaffaqiyatli export qilindi',
    
    // Filters
    'filter_status' => 'Holati',
    'filter_unassigned' => 'Tayinlanmagan mijozlar',
    'filter_all' => 'Barchasi',
    
    // Bulk Actions
    'bulk_delete' => 'Tanlangan elementlarni o\'chirish',
    'bulk_export' => 'Tanlangan elementlarni export qilish',
    
    // Import/Export
    'select_file' => 'Faylni tanlang',
    'upload_file' => 'Faylni yuklash',
    'download_template' => 'Shablonni yuklab olish',
    'import_description' => 'Excel yoki CSV fayl yuklang',
    'export_description' => 'Ma\'lumotlarni Excel formatida yuklab oling',
    
    // Import Actions
    'import_china' => 'Xitoydan import',
    'import_uzbekistan' => 'O\'zbekistondan import',
    'import_china_modal_title' => 'Xitoydan Excel import qilish',
    'import_uzbekistan_modal_title' => 'O\'zbekistondan Excel import qilish',
    'china_excel_file' => 'Excel fayli (Xitoy)',
    'uzbekistan_excel_file' => 'Excel fayli (O\'zbekiston)',
    'china_excel_help' => 'Excel fayli: track_number, weight(KG), is_banned ustunlari bo\'lishi kerak',
    'uzbekistan_excel_help' => 'Excel fayli: track_number ustuni bo\'lishi kerak',
    'import_success_china' => 'Xitoy Excel fayli muvaffaqiyatli import qilindi!',
    'import_success_uzbekistan' => 'O\'zbekiston Excel fayli muvaffaqiyatli import qilindi!',
    'import_error' => 'Import xatoligi',
    'import_action' => 'Import qilish',
    
    // Widget titles
    'activity_chart_title' => 'So\'nggi 14 kun faoliyati',
    'status_distribution_title' => 'Holat bo\'yicha taqsimot',
    'recent_parcels_title' => 'So\'nggi posilkalar',
    
    // Additional stats labels
    'last_7_days' => 'so\'nggi 7 kun',
    'no_new_parcels' => 'yangi posilkalar yo\'q',
    'new_parcels' => 'yangi posilkalar',
    'in_china' => 'Xitoyda',
    'in_uzbekistan' => 'O\'zbekistonda',
    'delivery_rate' => 'yetkazish foizi',
    'active_clients' => 'faol mijozlar',
    'restricted_items' => 'ta\'qiqlangan tovarlar',
    'last_updated' => 'oxirgi yangilanish',
    'unassigned' => 'tayinlanmagan',
    
    // Filters
    'filter_banned' => 'Taqiqlangan tovarlar',
    'filter_older_than_3_days' => '3 kundan oshganlar',
];