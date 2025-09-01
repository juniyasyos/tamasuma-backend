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
     * Notify recipients when a student requests enrollment.
     * Recipients resolved via permissions (Gate) with optional role fallback.
     */
    public function notifyRequested(Enrollment $enrollment): void
    {
        try {
            $perms = (array) config('app-notifications.recipients.enrollment_requested_permissions', []);
            $fallbackRoles = array_filter((array) config('app-notifications.recipients.enrollment_requested_roles', []));

            $byPermission = collect();
            if (! empty($perms)) {
                $byPermission = User::permission($perms)->get();
            }

            $byRole = collect();
            if (! empty($fallbackRoles)) {
                $byRole = User::role($fallbackRoles)->get();
            }

            $recipients = $byPermission->merge($byRole)->unique('id');

            if (Schema::hasColumn('users', 'mute_program_enrollment_notifications')) {
                $recipients = $recipients->where('mute_program_enrollment_notifications', false);
            }
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
