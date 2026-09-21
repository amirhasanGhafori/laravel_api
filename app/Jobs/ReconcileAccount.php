<?php

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Modules\User\Models\User;

class ReconcileAccount implements ShouldQueue
{
    use Queueable;

    public User $user;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger("Reconcile Account User :)". $this->user->firstName . ' ' . $this->user->lastName);
    }
}
