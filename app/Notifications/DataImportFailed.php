<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Maatwebsite\Excel\Events\ImportFailed;

class DataImportFailed extends Notification implements ShouldQueue
{
    use Queueable;

    private string $title, $description;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $name, ImportFailed $event)
    {
        report($event->getException());
        $this->title = "$name gagal di-impor";
        $this->description = "Terjadi kesalahan saat mengimpor data $name, mohon periksa ulang format data.";
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
