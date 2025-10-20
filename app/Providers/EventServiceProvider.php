<?php

namespace App\Providers;

use App\Events\ApplicationStatusChanged;
use App\Events\ParticipantAssignedToProgram;
use App\Events\PaymentVerified;
use App\Events\UserCreated;
use App\Listeners\SendApplicationStatusEmail;
use App\Listeners\SendPaymentVerifiedEmail;
use App\Listeners\SendProgramAssignmentEmail;
use App\Listeners\SendWelcomeEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserCreated::class => [
            SendWelcomeEmail::class,
        ],
        ParticipantAssignedToProgram::class => [
            SendProgramAssignmentEmail::class,
        ],
        ApplicationStatusChanged::class => [
            SendApplicationStatusEmail::class,
        ],
        PaymentVerified::class => [
            SendPaymentVerifiedEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
