<?php

// config/whatsapp.php

return [
    
    /*
    |--------------------------------------------------------------------------
    | WhatsApp API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WhatsApp API integration
    |
    */

    'base_url' => env('WHATSAPP_BASE_URL', 'https://your-api-domain.com'),
    
    'api_version' => env('WHATSAPP_API_VERSION', 'v1'),
    
    'session_id' => env('WHATSAPP_SESSION_ID', 'your_session_id'),
    
    'access_token' => env('WHATSAPP_ACCESS_TOKEN', 'your_access_token'),
    
    // مدة انتهاء صلاحية كود التحقق بالدقائق
    'verification_code_expiry' => env('WHATSAPP_CODE_EXPIRY', 10),
    
    // عدد المحاولات المسموحة لإرسال الكود
    'max_send_attempts' => env('WHATSAPP_MAX_SEND_ATTEMPTS', 3),
    
    // الفترة الزمنية بين المحاولات (بالثواني)
    'send_attempt_cooldown' => env('WHATSAPP_SEND_COOLDOWN', 120),

];