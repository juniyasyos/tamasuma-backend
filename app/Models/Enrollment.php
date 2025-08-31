<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'program_id', 'status', 'requested_at', 'approved_at', 'rejected_at', 'enrolled_at', 'completed_at', 'notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'enrolled_at' => 'date',
        'completed_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    protected static function booted(): void
    {
        static::created(function (Enrollment $enrollment) {
            // Trigger notifikasi pendaftaran program untuk non-pelajar.
            // Dikirim saat entri dibuat dengan status apa pun (umumnya 'requested' atau 'active').
            try {
                $program = $enrollment->program()->first();
                if (! $program) return;

                // Ambil target penerima: Admin, Super Admin, Pengajar (kecuali yang mematikan notifikasi)
                $userModel = \App\Models\User::query()
                    ->when(true, function ($q) {
                        if (method_exists(\App\Models\User::class, 'role')) {
                            // Spatie Permission scope
                            $q->role(['Admin', 'super_admin', 'Pengajar']);
                        }
                    })
                    ->where(function ($q) {
                        // pengguna yang tidak mematikan notifikasi
                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'mute_program_enrollment_notifications')) {
                            $q->where('mute_program_enrollment_notifications', false);
                        }
                    })
                    ->get();

                if ($userModel->isEmpty()) return;

                $notification = new \App\Notifications\ProgramEnrollmentRequested($enrollment);
                foreach ($userModel as $recipient) {
                    // Jangan kirim ke pelamar sendiri
                    if ($recipient->id === $enrollment->user_id) continue;
                    $recipient->notify($notification);
                }
            } catch (\Throwable $e) {
                // swallow to avoid breaking flow
                \Log::warning('Program enrollment notify failed: '.$e->getMessage());
            }
        });
    }
}
