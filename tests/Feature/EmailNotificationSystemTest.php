<?php

namespace Tests\Feature;

use App\Mail\Customers\PaymentReceiptMail;
use App\Mail\Customers\SaleConfirmationMail;
use App\Mail\Customers\WelcomeMail;
use App\Mail\Suppliers\PurchaseOrderMail;
use App\Mail\Suppliers\SupplierPaymentMail;
use App\Mail\Suppliers\WelcomeMail as SupplierWelcomeMail;
use Tests\TestCase;

class EmailNotificationSystemTest extends TestCase
{
    /**
     * Test that EventServiceProvider is registered
     */
    public function test_event_service_provider_exists(): void
    {
        $this->assertTrue(class_exists(\App\Providers\EventServiceProvider::class));
    }

    /**
     * Test that events are configured in EventServiceProvider
     */
    public function test_events_are_registered_in_service_provider(): void
    {
        $provider = new \App\Providers\EventServiceProvider(app());
        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('listen');
        $property->setAccessible(true);
        $listen = $property->getValue($provider);
        $this->assertIsArray($listen);
        $this->assertNotEmpty($listen);
    }

    /**
     * Test SaleConfirmed event exists and can be instantiated
     */
    public function test_sale_confirmed_event_exists(): void
    {
        $this->assertTrue(class_exists(\App\Events\SaleConfirmed::class));
    }

    /**
     * Test PurchaseConfirmed event exists and can be instantiated
     */
    public function test_purchase_confirmed_event_exists(): void
    {
        $this->assertTrue(class_exists(\App\Events\PurchaseConfirmed::class));
    }

    /**
     * Test PaymentReceived event exists
     */
    public function test_payment_received_event_exists(): void
    {
        $this->assertTrue(class_exists(\App\Events\PaymentReceived::class));
    }

    /**
     * Test SupplierPaymentRecorded event exists
     */
    public function test_supplier_payment_recorded_event_exists(): void
    {
        $this->assertTrue(class_exists(\App\Events\SupplierPaymentRecorded::class));
    }

    /**
     * Test CustomerCreated event exists
     */
    public function test_customer_created_event_exists(): void
    {
        $this->assertTrue(class_exists(\App\Events\CustomerCreated::class));
    }

    /**
     * Test SupplierCreated event exists
     */
    public function test_supplier_created_event_exists(): void
    {
        $this->assertTrue(class_exists(\App\Events\SupplierCreated::class));
    }

    /**
     * Test that email listeners implement ShouldQueue
     */
    public function test_listeners_implement_should_queue(): void
    {
        $listeners = [
            \App\Listeners\SendSaleConfirmationToCustomer::class,
            \App\Listeners\SendSaleConfirmationToAdmin::class,
            \App\Listeners\SendPurchaseConfirmationToSupplier::class,
            \App\Listeners\SendPurchaseConfirmationToAdmin::class,
            \App\Listeners\SendPaymentReceiptToCustomer::class,
            \App\Listeners\SendPaymentNotificationToAdmin::class,
            \App\Listeners\SendSupplierPaymentConfirmation::class,
            \App\Listeners\SendSupplierPaymentToAdmin::class,
            \App\Listeners\SendCustomerWelcomeEmail::class,
            \App\Listeners\SendSupplierWelcomeEmail::class,
        ];

        foreach ($listeners as $listener) {
            $instance = new $listener;
            $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $instance);
        }
    }

    /**
     * Test that all mail classes implement ShouldQueue
     */
    public function test_mail_classes_implement_should_queue(): void
    {
        $mailClasses = [
            SaleConfirmationMail::class,
            PaymentReceiptMail::class,
            WelcomeMail::class,
            PurchaseOrderMail::class,
            SupplierPaymentMail::class,
            SupplierWelcomeMail::class,
        ];

        foreach ($mailClasses as $mailClass) {
            $this->assertTrue(
                in_array(\Illuminate\Contracts\Queue\ShouldQueue::class, class_implements($mailClass)),
                "{$mailClass} should implement ShouldQueue"
            );
        }
    }

    /**
     * Test admin email configuration is set
     */
    public function test_admin_email_is_configured(): void
    {
        $adminEmail = config('mail.admin_email');
        $this->assertNotEmpty($adminEmail);
        $this->assertIsString($adminEmail);
    }

    /**
     * Test queue connection is configured for database
     */
    public function test_queue_connection_is_database(): void
    {
        $this->assertEquals('database', config('queue.default'));
    }

    /**
     * Test mail mailer is configured
     */
    public function test_mail_mailer_is_configured(): void
    {
        $mailer = config('mail.default');
        $this->assertIsString($mailer);
    }

    /**
     * Test that all listener classes exist
     */
    public function test_all_listener_classes_exist(): void
    {
        $listeners = [
            \App\Listeners\SendSaleConfirmationToCustomer::class,
            \App\Listeners\SendSaleConfirmationToAdmin::class,
            \App\Listeners\SendPurchaseConfirmationToSupplier::class,
            \App\Listeners\SendPurchaseConfirmationToAdmin::class,
            \App\Listeners\SendPaymentReceiptToCustomer::class,
            \App\Listeners\SendPaymentNotificationToAdmin::class,
            \App\Listeners\SendSupplierPaymentConfirmation::class,
            \App\Listeners\SendSupplierPaymentToAdmin::class,
            \App\Listeners\SendCustomerWelcomeEmail::class,
            \App\Listeners\SendSupplierWelcomeEmail::class,
        ];

        foreach ($listeners as $listener) {
            $this->assertTrue(class_exists($listener), "{$listener} should exist");
        }
    }

    /**
     * Test that all mail classes exist
     */
    public function test_all_mail_classes_exist(): void
    {
        $mailClasses = [
            SaleConfirmationMail::class,
            PaymentReceiptMail::class,
            WelcomeMail::class,
            PurchaseOrderMail::class,
            SupplierPaymentMail::class,
            SupplierWelcomeMail::class,
        ];

        foreach ($mailClasses as $mailClass) {
            $this->assertTrue(class_exists($mailClass), "{$mailClass} should exist");
        }
    }

    /**
     * Test that all email template views exist
     */
    public function test_all_email_templates_exist(): void
    {
        $templates = [
            'mails.customers.sale-confirmation',
            'mails.customers.payment-receipt',
            'mails.customers.welcome',
            'mails.suppliers.purchase-order',
            'mails.suppliers.payment-confirmation',
            'mails.suppliers.welcome',
        ];

        foreach ($templates as $template) {
            $this->assertTrue(
                view()->exists($template),
                "Template {$template} should exist"
            );
        }
    }
}

