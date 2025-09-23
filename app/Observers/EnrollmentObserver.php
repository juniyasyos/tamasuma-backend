<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Services\Notifications\EnrollmentNotificationService;

class EnrollmentObserver
{
    public function created(Enrollment $enrollment): void
    {
        // Notify only when a request is made
        if ($enrollment->status === 'requested') {
            app(EnrollmentNotificationService::class)->notifyRequested($enrollment);
        }
    }

    public function updated(Enrollment $enrollment): void
    {
        // If status transitions to requested, notify admins
        if ($enrollment->wasChanged('status') && $enrollment->status === 'requested') {
            app(EnrollmentNotificationService::class)->notifyRequested($enrollment);
        }

        // If status transitions to completed, award achievement (idempotent)
        if ($enrollment->wasChanged('status') && $enrollment->status === 'completed') {
            try {
                $enrollment->awardCompletionAchievement();
            } catch (\Throwable $e) {
                // Silent fail to avoid breaking user flows; consider logging if desired
                // \Log::warning('Auto-award failed: '.$e->getMessage());
            }
        }
    }
}
