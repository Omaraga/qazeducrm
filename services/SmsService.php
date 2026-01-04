<?php

namespace app\services;

use app\models\Organizations;
use app\models\SmsLog;
use app\models\SmsTemplate;
use Yii;
use yii\base\Component;

/**
 * Сервис для отправки SMS
 *
 * Поддерживаемые провайдеры:
 * - mobizon (mobizon.kz)
 * - smskz (sms.kz)
 * - test (для тестирования)
 */
class SmsService extends Component
{
    const PROVIDER_MOBIZON = 'mobizon';
    const PROVIDER_SMSKZ = 'smskz';
    const PROVIDER_TEST = 'test';

    /**
     * Отправить SMS
     *
     * @param string $phone Номер телефона
     * @param string $message Текст сообщения
     * @param int|null $templateId ID шаблона
     * @param string|null $recipientType Тип получателя
     * @param int|null $recipientId ID получателя
     * @return SmsLog
     */
    public function send($phone, $message, $templateId = null, $recipientType = null, $recipientId = null)
    {
        // Нормализуем номер
        $phone = $this->normalizePhone($phone);

        // Создаём запись в логе
        $log = SmsLog::log($phone, $message, $templateId, $recipientType, $recipientId);

        // Получаем настройки организации
        $org = Organizations::findOne(Organizations::getCurrentOrganizationId());

        if (!$org || !$org->sms_provider || !$org->sms_api_key) {
            $log->markAsFailed('SMS провайдер не настроен');
            return $log;
        }

        try {
            $result = $this->sendViaProvider($org->sms_provider, [
                'api_key' => $org->sms_api_key,
                'sender' => $org->sms_sender,
                'phone' => $phone,
                'message' => $message,
            ]);

            if ($result['success']) {
                $log->markAsSent($result['message_id'] ?? null, json_encode($result));
            } else {
                $log->markAsFailed($result['error'] ?? 'Неизвестная ошибка', json_encode($result));
            }
        } catch (\Exception $e) {
            $log->markAsFailed($e->getMessage());
        }

        return $log;
    }

    /**
     * Отправить по шаблону
     *
     * @param string $templateCode Код шаблона
     * @param string $phone Номер телефона
     * @param array $data Данные для подстановки
     * @param string|null $recipientType Тип получателя
     * @param int|null $recipientId ID получателя
     * @return SmsLog|null
     */
    public function sendByTemplate($templateCode, $phone, array $data = [], $recipientType = null, $recipientId = null)
    {
        $template = SmsTemplate::findByCode($templateCode);

        if (!$template) {
            Yii::warning("SMS шаблон не найден: {$templateCode}");
            return null;
        }

        // Добавляем название организации
        $org = Organizations::findOne(Organizations::getCurrentOrganizationId());
        if ($org && !isset($data['org_name'])) {
            $data['org_name'] = $org->name;
        }

        $message = $template->render($data);

        return $this->send($phone, $message, $template->id, $recipientType, $recipientId);
    }

    /**
     * Массовая отправка
     *
     * @param array $recipients [[phone => ..., data => [...]], ...]
     * @param string $templateCode Код шаблона
     * @return array Массив SmsLog
     */
    public function sendBulk(array $recipients, $templateCode)
    {
        $results = [];
        foreach ($recipients as $recipient) {
            $results[] = $this->sendByTemplate(
                $templateCode,
                $recipient['phone'],
                $recipient['data'] ?? [],
                $recipient['type'] ?? null,
                $recipient['id'] ?? null
            );
        }
        return $results;
    }

    /**
     * Нормализация номера телефона
     */
    protected function normalizePhone($phone)
    {
        // Убираем всё кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Убираем ведущую 8 для Казахстана
        if (strlen($phone) === 11 && $phone[0] === '8') {
            $phone = '7' . substr($phone, 1);
        }

        // Добавляем +
        if (strlen($phone) === 11 && $phone[0] === '7') {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Отправка через провайдера
     */
    protected function sendViaProvider($provider, array $params)
    {
        switch ($provider) {
            case self::PROVIDER_MOBIZON:
                return $this->sendViaMobizon($params);
            case self::PROVIDER_SMSKZ:
                return $this->sendViaSmsKz($params);
            case self::PROVIDER_TEST:
                return $this->sendViaTest($params);
            default:
                return ['success' => false, 'error' => 'Неизвестный провайдер: ' . $provider];
        }
    }

    /**
     * Отправка через Mobizon
     * Документация: https://mobizon.kz/api/
     */
    protected function sendViaMobizon(array $params)
    {
        $url = 'https://api.mobizon.kz/service/message/sendSmsMessage';

        $data = [
            'recipient' => $params['phone'],
            'text' => $params['message'],
            'apiKey' => $params['api_key'],
        ];

        if (!empty($params['sender'])) {
            $data['from'] = $params['sender'];
        }

        $response = $this->httpPost($url, $data);

        if (!$response) {
            return ['success' => false, 'error' => 'Нет ответа от сервера'];
        }

        $result = json_decode($response, true);

        if (isset($result['code']) && $result['code'] == 0) {
            return [
                'success' => true,
                'message_id' => $result['data']['messageId'] ?? null,
                'response' => $result,
            ];
        }

        return [
            'success' => false,
            'error' => $result['message'] ?? 'Ошибка отправки',
            'response' => $result,
        ];
    }

    /**
     * Отправка через SMS.kz
     * Документация: https://sms.kz/api/
     */
    protected function sendViaSmsKz(array $params)
    {
        $url = 'https://api.sms.kz/v1/send';

        $data = [
            'phone' => $params['phone'],
            'message' => $params['message'],
        ];

        $headers = [
            'Authorization: Bearer ' . $params['api_key'],
            'Content-Type: application/json',
        ];

        $response = $this->httpPost($url, json_encode($data), $headers);

        if (!$response) {
            return ['success' => false, 'error' => 'Нет ответа от сервера'];
        }

        $result = json_decode($response, true);

        if (isset($result['status']) && $result['status'] === 'success') {
            return [
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'response' => $result,
            ];
        }

        return [
            'success' => false,
            'error' => $result['error'] ?? $result['message'] ?? 'Ошибка отправки',
            'response' => $result,
        ];
    }

    /**
     * Тестовый провайдер (не отправляет SMS)
     */
    protected function sendViaTest(array $params)
    {
        Yii::info("TEST SMS to {$params['phone']}: {$params['message']}", 'sms');

        return [
            'success' => true,
            'message_id' => 'test_' . time(),
            'response' => ['test' => true],
        ];
    }

    /**
     * HTTP POST запрос
     */
    protected function httpPost($url, $data, array $headers = [])
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if (is_array($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Yii::error("SMS HTTP Error: {$error}", 'sms');
            return null;
        }

        return $response;
    }

    /**
     * Получить список провайдеров
     */
    public static function getProviderList()
    {
        return [
            self::PROVIDER_MOBIZON => 'Mobizon (mobizon.kz)',
            self::PROVIDER_SMSKZ => 'SMS.kz',
            self::PROVIDER_TEST => 'Тестовый (не отправляет)',
        ];
    }
}
