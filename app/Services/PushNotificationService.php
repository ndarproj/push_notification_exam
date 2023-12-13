<?php

namespace App\Services;

use App\Constants\PushNotificationStatuses;
use App\Models\Device;
use App\Models\PushNotification;
use Exception;

class PushNotificationService extends BaseModelService
{
    public function __construct()
    {
        parent::__construct(PushNotification::class);
    }

    public function sendByCountryId(string $title, string $message, int $countryId): ?int
    {
        $notificationId = $this->model->create([
            'title' => $title,
            'message' => $message,
            'country_id' => $countryId
        ]);

        return $notificationId;
    }

    public function getById(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function getByIds(array $ids): ?array
    {
        return $this->model->findIds($ids);
    }

    public function sendPushNotification(): ?array
    {
        $queuedNotifications = $this->model->getAll(['status' => PushNotificationStatuses::STATUS_IN_QUEUE]);
        if (empty($queuedNotifications)) {
            return [];
        }

        $notificationIds = array_column($queuedNotifications, 'id');
        $this->model->updateMultiple($notificationIds, ['status' => PushNotificationStatuses::STATUS_IN_PROGRESS]);

        $userDevices = (new Device($this->pdo))->getAll(['expired' => false]);
        $batchSize = 1000; // Adjust this batch size as needed
        $totalDeviceCount = count($userDevices);
        $deviceBatches = array_chunk($userDevices, $batchSize);

        foreach ($queuedNotifications as $queueNotification) {
            $this->processNotificationBatches($queueNotification, $deviceBatches, $totalDeviceCount);
        }

        return $notificationIds;
    }

    private function processNotificationBatches($notification, $batches, $totalDeviceCount): void
    {
        try {
            $successCount = 0;
            $failedCount = 0;
            $remainingDevices = $totalDeviceCount;

            foreach ($batches as $batch) {
                $batchSize = count($batch);
                $remainingDevices -= $batchSize;

                $this->model->update($notification['id'], ['in_progress' => $batchSize, 'in_queue' => $remainingDevices]);
                $batchResult = $this->sendNotificationBatch($notification, $batch);

                $successCount += $batchResult['success'];
                $failedCount += $batchResult['failed'];
            }

            $finalStatus = $this->determineFinalStatus($successCount, $failedCount, $totalDeviceCount);
            $this->model->update($notification['id'], [
                'sent' => $successCount,
                'failed' => $failedCount,
                'in_progress' => 0,
                'status' => $finalStatus
            ]);
        } catch (Exception $ex) {
            $this->handleNotificationException($notification['id'], $successCount, $failedCount);
        }
    }

    private function sendNotificationBatch($notification, $batch): array
    {
        $successCount = 0;
        $failedCount = 0;

        foreach ($batch as $user) {
            try {
                $sendNotification = $this->model->send($notification['title'], $notification['message'], $user['token']);

                if ($sendNotification === true) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
            } catch (Exception $ex) {
                $failedCount++;
            }
        }

        return ['success' => $successCount, 'failed' => $failedCount];
    }

    private function determineFinalStatus($successCount, $failedCount, $totalDeviceCount): string
    {
        if ($successCount == $totalDeviceCount && $failedCount == 0) {
            return PushNotificationStatuses::STATUS_SUCCESS;
        } elseif ($failedCount > 0) {
            return PushNotificationStatuses::STATUS_PARTIAL;
        }
        return PushNotificationStatuses::STATUS_FAILED;
    }

    private function handleNotificationException($notificationId, $successCount): void
    {
        $status = ($successCount > 0) ? PushNotificationStatuses::STATUS_PARTIAL : PushNotificationStatuses::STATUS_FAILED;
        $this->model->update($notificationId, ['status' => $status]);
    }
}
