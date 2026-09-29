<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportAttendanceProgress implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $progress;
    public $message;

    public function __construct($progress, $message)
    {
        $this->progress = $progress;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Menggunakan channel notifikasi yang sudah Anda siapkan sebelumnya
        return new Channel('hris-notifications');
    }

    public function broadcastAs()
    {
        return 'import.progress';
    }
}