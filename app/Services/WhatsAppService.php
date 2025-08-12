<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private $baseUrl;
    private $apiVersion;
    private $sessionId;
    private $accessToken;

    public function __construct()
    {
        $this->baseUrl = config('whatsapp.base_url');
        $this->apiVersion = config('whatsapp.api_version', 'v1');
        $this->sessionId = config('whatsapp.session_id');
        $this->accessToken = config('whatsapp.access_token');
    }

    /**
     * إرسال رسالة نصية عبر WhatsApp
     */
    public function sendMessage($phoneNumber, $message)
    {
        try {

            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            
            $url = "{$this->baseUrl}/{$this->apiVersion}/message/text/send";
            
            $payload = [
                'session_id' => $this->sessionId,
                'receiver' => $formattedPhone,
                'text' => $message
            ];

            // بناء headers المصادقة
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            // إضافة المصادقة حسب النوع المحدد في التكوين
            if ($this->accessToken) {
                $headers['Authorization'] = 'Bearer ' . $this->accessToken;
            }

            // تسجيل تفاصيل الطلب للتشخيص
            Log::info('WhatsApp API Request Details', [
                'url' => $url,
                'payload' => $payload,
                'headers' => array_merge($headers, [
                    'Authorization' => 'Bearer ***' // إخفاء التوكن الحقيقي
                ])
            ]);

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($url, $payload);

            // تسجيل تفاصيل الاستجابة
            Log::info('WhatsApp API Response Details', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'response_headers' => $response->headers()
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'phone' => $formattedPhone,
                    'response' => $response->json()
                ]);
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                Log::error('Failed to send WhatsApp message', [
                    'phone' => $formattedPhone,
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'url' => $url,
                    'payload' => $payload
                ]);
                
                // إرجاع تفاصيل أكثر عن الخطأ
                return [
                    'success' => false,
                    'error' => 'Failed to send message',
                    'status_code' => $response->status(),
                    'details' => $response->json() ?? $response->body(),
                    'url' => $url // للتشخيص
                ];
            }

        } catch (\Exception $e) {
            Log::error('WhatsApp service error: ' . $e->getMessage(), [
                'phone' => $phoneNumber,
                'trace' => $e->getTraceAsString(),
                'url' => $url ?? 'URL not constructed',
                'config_check' => [
                    'base_url' => $this->baseUrl,
                    'api_version' => $this->apiVersion,
                    'session_id' => $this->sessionId ? 'SET' : 'NOT SET',
                    'access_token' => $this->accessToken ? 'SET' : 'NOT SET'
                ]
            ]);
            
            return [
                'success' => false,
                'error' => 'Service temporarily unavailable',
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * إرسال كود التحقق
     */
    public function sendVerificationCode($phoneNumber, $code, $userName = null)
    {
        $message = $this->buildVerificationMessage($code, $userName);
        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * بناء رسالة كود التحقق
     */
    private function buildVerificationMessage($code, $userName = null)
    {
        $greeting = $userName ? "مرحباً {$userName}،" : "مرحباً،";
        
        return "{$greeting}\n\n" .
               "كود التحقق الخاص بك هو: *{$code}*\n\n" .
               " يرجى إدخال هذا الكود لإكمال عملية التسجيل في تطبيق سكر سيرفيس.\n" .
               "ملاحظة: الكود صالح لمدة 10 دقائق فقط.\n\n" .
               "إذا لم تطلب هذا الكود، يرجى تجاهل هذه الرسالة.";
    }

    /**
     * تنسيق رقم الهاتف
     */
private function formatPhoneNumber($phoneNumber)
{
    // إزالة أي رموز غير رقمية
    $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

   
    if (strlen($cleaned) === 10 && str_starts_with($cleaned, '0')) {
        $cleaned = substr($cleaned, 1);
    }


    if (strlen($cleaned) === 9 && str_starts_with($cleaned, '9')) {
        $cleaned = '963' . $cleaned;
    }

    // إذا كان الرقم يبدأ بـ 963 وطوله 12 رقم (963943989776)
    elseif (strlen($cleaned) === 12 && str_starts_with($cleaned, '963')) {
        // لا شيء، الرقم جاهز
    }

    // في حال كانت هناك حالات شاذة أخرى، افترض سوريا
    elseif (!str_starts_with($cleaned, '963')) {
        $cleaned = '963' . $cleaned;
    }

    // أضف + في البداية
    $formatted = '+' . $cleaned;

    // تسجيل لغرض التتبع
    Log::info('Phone number formatted', [
        'original' => $phoneNumber,
        'formatted' => $formatted,
        'length' => strlen($formatted)
    ]);

    return $formatted;
}

    /**
     * التحقق من حالة الجلسة
     */
    public function checkSessionStatus()
    {
        try {
            $url = "{$this->baseUrl}/{$this->apiVersion}/session/status";
            
            $headers = ['Accept' => 'application/json'];
            if ($this->accessToken) {
                $headers['Authorization'] = 'Bearer ' . $this->accessToken;
            }

            Log::info('Checking session status', [
                'url' => $url,
                'session_id' => $this->sessionId
            ]);

            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->get($url, ['session_id' => $this->sessionId]);

            Log::info('Session status response', [
                'status_code' => $response->status(),
                'response' => $response->body()
            ]);

            return $response->successful() ? $response->json() : null;
            
        } catch (\Exception $e) {
            Log::error('Failed to check WhatsApp session status: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * التحقق من صحة إعدادات المصادقة
     */
    public function validateCredentials()
    {
        Log::info('Validating WhatsApp credentials', [
            'base_url' => $this->baseUrl,
            'api_version' => $this->apiVersion,
            'session_id' => $this->sessionId ? 'SET' : 'NOT SET',
            'access_token' => $this->accessToken ? 'SET (length: ' . strlen($this->accessToken) . ')' : 'NOT SET'
        ]);

        if (empty($this->baseUrl)) {
            return ['valid' => false, 'error' => 'Base URL is not configured'];
        }

        if (empty($this->sessionId)) {
            return ['valid' => false, 'error' => 'Session ID is not configured'];
        }

        if (empty($this->accessToken)) {
            return ['valid' => false, 'error' => 'Access Token is not configured'];
        }

        // اختبار الاتصال
        $status = $this->checkSessionStatus();
        if ($status === null) {
            return ['valid' => false, 'error' => 'Cannot connect to WhatsApp API'];
        }

        return ['valid' => true, 'data' => $status];
    }


   
}