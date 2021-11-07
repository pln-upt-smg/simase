<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DataImportRequested extends Notification implements ShouldQueue
{
    use Queueable;

    private string $title, $description;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $name)
    {
        $this->title = "Permintaan impor $name dijadwalkan";
        $this->description = "Sistem telah menerima permintaan impor data $name, mohon tunggu selama proses impor berjalan di latar belakang.";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description
        ];
    }
}
