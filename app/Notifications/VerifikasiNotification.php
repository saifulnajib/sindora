<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VerifikasiNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $entityType,
        public int|string $entityId,
        public string $entityName,
        public string $action,
        public string $message,
        public ?string $actorName = null,
        public ?string $catatan = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'entity_name' => $this->entityName,
            'action' => $this->action,
            'message' => $this->message,
            'actor_name' => $this->actorName,
            'catatan' => $this->catatan,
            'url' => $this->resolveUrl(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    private function resolveUrl(): ?string
    {
        $map = [
            'klub' => 'klubs.show',
            'atlet' => 'atlets.show',
            'sdm' => 'sdms.show',
            'sarpras' => 'sarpras.show',
            'kejuaraan' => 'kejuaraans.show',
            'prestasi' => 'prestasis.show',
            'pembinaan' => 'pembinaans.show',
        ];

        $route = $map[$this->entityType] ?? null;
        if ($route) {
            try {
                return route($route, $this->entityId);
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }
}
