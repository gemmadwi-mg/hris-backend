<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Gunakan Now agar instan
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaveRequested implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($employeeName)
    {
        $this->message = "{$employeeName} mengajukan cuti baru.";
    }

    public function broadcastOn(): array
    {
        // Untuk kemudahan demo, kita gunakan Channel publik. 
        // (Di level lanjut, Anda bisa mengubahnya menjadi PrivateChannel)
        return [
            new Channel('hris-notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'leave.requested'; // Nama event yang akan didengarkan oleh Vue
    }
}
