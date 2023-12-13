<?php

namespace App\Controllers\Responses;

abstract class BaseResponse
{
    public function format($notifications, array $excludeFields = []): array
    {
        // Check if input is a single notification (associative array) or multiple (sequential array)
        $isSingleNotification = isset($notifications['id']);

        if ($isSingleNotification) {
            return $this->formatNotification($notifications, $excludeFields);
        } else {
            return array_map(function ($notification) use ($excludeFields) {
                return $this->formatNotification($notification, $excludeFields);
            }, $notifications);
        }
    }

    abstract protected function formatNotification(array $data, array $excludeFields);
}
