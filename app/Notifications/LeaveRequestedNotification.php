<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\Channel;

class LeaveRequestedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $employeeName;

    public function __construct($employeeName)
    {
        $this->employeeName = $employeeName;
    }

    // 1. Tentukan tujuan pengiriman (Database & WebSocket)
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    // 2. Data yang akan disimpan ke tabel Database
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "{$this->employeeName} mengajukan cuti baru.",
        ];
    }

    // 3. Data yang akan disiarkan ke WebSocket (Reverb)
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => "{$this->employeeName} mengajukan cuti baru.",
        ]);
    }

    // 4. Saluran WebSocket yang dituju (sesuaikan dengan yang kita buat di Vue)
    public function broadcastOn()
    {
        return [new Channel('hris-notifications')];
    }

    // 5. Nama Event yang didengarkan oleh Vue
    public function broadcastType()
    {
        return 'leave.requested'; 
    }
}
