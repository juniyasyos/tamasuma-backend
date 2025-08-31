<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProgramEnrollmentRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Enrollment $enrollment)
    {
        //
    }

    public function via(object $notifiable): array
    {
        // Simpan sebagai database notification (ditampilkan di bell Filament)
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $p = $this->enrollment->program;
        $u = $this->enrollment->user;
        $url = \App\Filament\Resources\ProgramResource::getUrl('view', ['record' => $p?->id]);
        return [
            'type' => 'program_enrollment',
            'title' => 'Pendaftaran Program Baru',
            'body' => sprintf('%s mendaftar ke "%s"', $u?->name ?? 'Pengguna', $p?->title ?? 'Program'),
            'program_id' => $p?->id,
            'enrollment_id' => $this->enrollment->id,
            'url' => $url,
            'status' => $this->enrollment->status,
            'requested_at' => optional($this->enrollment->requested_at)->toIso8601String(),
        ];
    }
}

