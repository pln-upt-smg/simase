<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DataStored extends Notification implements ShouldQueue
{
    use Queueable;

    private string $title, $description, $time;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $name, ?string $data = null)
    {
        $name = trim($name);
        $data = is_null($data) ? 'baru' : trim($data);
        $this->title = "$name berhasil ditambahkan";
        $this->description = "Anda telah berhasil menambahkan data $name $data.";
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
