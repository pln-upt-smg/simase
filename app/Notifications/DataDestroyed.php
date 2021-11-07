<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DataDestroyed extends Notification implements ShouldQueue
{
    use Queueable;

    private string $title, $description;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $name, ?string $data = null)
    {
        $name = trim($name);
        $data = is_null($data) ? '' : ' ' . trim($data);
        $this->title = "$name berhasil dihapus";
        $this->description = "Anda telah berhasil menghapus data $name$data.";
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
