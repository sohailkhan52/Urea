<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Event Service Provider
 * 
 * Register event-listener mappings for the application.
 * This service provider maps application events to their listeners.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Sales Events
        \App\Events\SaleConfirmed::class => [
            \App\Listeners\SendSaleConfirmationToCustomer::class,
            \App\Listeners\SendSaleConfirmationToAdmin::class,
            \App\Listeners\LogNotificationActivity::class,
        ],

        // Purchase Events
        \App\Events\PurchaseConfirmed::class => [
            \App\Listeners\SendPurchaseConfirmationToSupplier::class,
            \App\Listeners\SendPurchaseConfirmationToAdmin::class,
            \App\Listeners\LogNotificationActivity::class,
        ],

        // Payment Events
        \App\Events\PaymentReceived::class => [
            \App\Listeners\SendPaymentReceiptToCustomer::class,
            \App\Listeners\SendPaymentNotificationToAdmin::class,
            \App\Listeners\LogNotificationActivity::class,
        ],

        \App\Events\SupplierPaymentRecorded::class => [
            \App\Listeners\SendSupplierPaymentConfirmation::class,
            \App\Listeners\SendSupplierPaymentToAdmin::class,
            \App\Listeners\LogNotificationActivity::class,
        ],

        // Customer Events
        \App\Events\CustomerCreated::class => [
            \App\Listeners\SendCustomerWelcomeEmail::class,
            \App\Listeners\LogNotificationActivity::class,
        ],

        // Supplier Events
        \App\Events\SupplierCreated::class => [
            \App\Listeners\SendSupplierWelcomeEmail::class,
            \App\Listeners\LogNotificationActivity::class,
        ],

        // Notification Events
        \App\Events\NotificationCreated::class => [
            \App\Listeners\SendNotificationEmail::class,
            \App\Listeners\LogNotificationActivity::class,
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
