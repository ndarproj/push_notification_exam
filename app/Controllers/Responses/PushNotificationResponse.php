<?php

namespace App\Controllers\Responses;

class PushNotificationResponse extends BaseResponse
{
    protected function formatNotification(array $notification, array $excludeFields): array
    {
        $formattedNotification = [
            'notification_id' => $notification['id'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'sent' => $notification['sent'],
            'failed' => $notification['failed'],
            'in_progress' => $notification['in_progress'],
            'in_queue' => $notification['in_queue']
        ];

        foreach ($excludeFields as $field) {
            unset($formattedNotification[$field]);
        }

        return $formattedNotification;
    }
}
