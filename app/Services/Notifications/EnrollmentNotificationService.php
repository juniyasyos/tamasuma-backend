<?php

namespace App\Services\Notifications;

use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\ProgramEnrollmentRequested;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EnrollmentNotificationService
{
    /**
     * Notify only Admin and Super Admin when a student requests enrollment.
     */
    public function notifyRequested(Enrollment $enrollment): void
    {
        try {
            $query = User::query()->role(['Admin', 'super_admin']);

            if (Schema::hasColumn('users', 'mute_program_enrollment_notifications')) {
                $query->where('mute_program_enrollment_notifications', false);
            }

            $recipients = $query->get();
            if ($recipients->isEmpty()) return;

            $notification = new ProgramEnrollmentRequested($enrollment);

            $delivery = config('app-notifications.delivery', 'immediate');

            foreach ($recipients as $recipient) {
                if ($recipient->id === $enrollment->user_id) {
                    continue; // do not notify the requester
                }
                if ($delivery === 'queued') {
                    // Respect queue pipeline (requires worker running)
                    $recipient->notify($notification);
                } else {
                    // Immediate database insert for shared hosting environments
                    $recipient->notifyNow($notification, ['database']);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to notify enrollment request: '.$e->getMessage());
        }
    }
}
