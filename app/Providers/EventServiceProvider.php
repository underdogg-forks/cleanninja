<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [

        // Clients
        'App\Events\ClientWasCreated' => [
            'App\Listeners\TimelineListener@createdClient',
            'App\Listeners\SubscriptionListener@createdClient',
        ],
        'App\Events\ClientWasArchived' => [
            'App\Listeners\TimelineListener@archivedClient',
        ],
        'App\Events\ClientWasUpdated' => [
            'App\Listeners\SubscriptionListener@updatedClient',
        ],
        'App\Events\ClientWasDeleted' => [
            'App\Listeners\TimelineListener@deletedClient',
            'App\Listeners\SubscriptionListener@deletedClient',
            'App\Listeners\HistoryListener@deletedClient',
        ],
        'App\Events\ClientWasRestored' => [
            'App\Listeners\TimelineListener@restoredClient',
        ],

        // Invoices
        'App\Events\InvoiceWasCreated' => [
            'App\Listeners\TimelineListener@createdInvoice',
            'App\Listeners\InvoiceListener@createdInvoice',
        ],
        'App\Events\InvoiceWasUpdated' => [
            'App\Listeners\TimelineListener@updatedInvoice',
            'App\Listeners\InvoiceListener@updatedInvoice',
        ],
        'App\Events\InvoiceItemsWereCreated' => [
            'App\Listeners\SubscriptionListener@createdInvoice',
        ],
        'App\Events\InvoiceItemsWereUpdated' => [
            'App\Listeners\SubscriptionListener@updatedInvoice',
        ],
        'App\Events\InvoiceWasArchived' => [
            'App\Listeners\TimelineListener@archivedInvoice',
        ],
        'App\Events\InvoiceWasDeleted' => [
            'App\Listeners\TimelineListener@deletedInvoice',
            'App\Listeners\TaskListener@deletedInvoice',
            'App\Listeners\ExpenseListener@deletedInvoice',
            'App\Listeners\HistoryListener@deletedInvoice',
            'App\Listeners\SubscriptionListener@deletedInvoice',
        ],
        'App\Events\InvoiceWasRestored' => [
            'App\Listeners\TimelineListener@restoredInvoice',
        ],
        'App\Events\InvoiceWasEmailed' => [
            'App\Listeners\InvoiceListener@emailedInvoice',
            'App\Listeners\NotificationListener@emailedInvoice',
        ],
        'App\Events\InvoiceInvitationWasEmailed' => [
            'App\Listeners\TimelineListener@emailedInvoice',
        ],
        'App\Events\InvoiceInvitationWasViewed' => [
            'App\Listeners\TimelineListener@viewedInvoice',
            'App\Listeners\NotificationListener@viewedInvoice',
            'App\Listeners\InvoiceListener@viewedInvoice',
        ],

        // Quotes
        'App\Events\QuoteWasCreated' => [
            'App\Listeners\TimelineListener@createdQuote',
        ],
        'App\Events\QuoteWasUpdated' => [
            'App\Listeners\TimelineListener@updatedQuote',
        ],
        'App\Events\QuoteItemsWereCreated' => [
            'App\Listeners\SubscriptionListener@createdQuote',
        ],
        'App\Events\QuoteItemsWereUpdated' => [
            'App\Listeners\SubscriptionListener@updatedQuote',
        ],
        'App\Events\QuoteWasArchived' => [
            'App\Listeners\TimelineListener@archivedQuote',
        ],
        'App\Events\QuoteWasDeleted' => [
            'App\Listeners\TimelineListener@deletedQuote',
            'App\Listeners\HistoryListener@deletedQuote',
            'App\Listeners\SubscriptionListener@deletedQuote',
        ],
        'App\Events\QuoteWasRestored' => [
            'App\Listeners\TimelineListener@restoredQuote',
        ],
        'App\Events\QuoteWasEmailed' => [
            'App\Listeners\QuoteListener@emailedQuote',
            'App\Listeners\NotificationListener@emailedQuote',
        ],
        'App\Events\QuoteInvitationWasEmailed' => [
            'App\Listeners\TimelineListener@emailedQuote',
        ],
        'App\Events\QuoteInvitationWasViewed' => [
            'App\Listeners\TimelineListener@viewedQuote',
            'App\Listeners\NotificationListener@viewedQuote',
            'App\Listeners\QuoteListener@viewedQuote',
        ],
        'App\Events\QuoteInvitationWasApproved' => [
            'App\Listeners\TimelineListener@approvedQuote',
            'App\Listeners\NotificationListener@approvedQuote',
            'App\Listeners\SubscriptionListener@approvedQuote',
        ],

        // Payments
        'App\Events\PaymentWasCreated' => [
            'App\Listeners\TimelineListener@createdPayment',
            'App\Listeners\SubscriptionListener@createdPayment',
            'App\Listeners\InvoiceListener@createdPayment',
            'App\Listeners\NotificationListener@createdPayment',
            'App\Listeners\AnalyticsListener@trackRevenue',
        ],
        'App\Events\PaymentWasArchived' => [
            'App\Listeners\TimelineListener@archivedPayment',
        ],
        'App\Events\PaymentWasDeleted' => [
            'App\Listeners\TimelineListener@deletedPayment',
            'App\Listeners\InvoiceListener@deletedPayment',
            'App\Listeners\CreditListener@deletedPayment',
            'App\Listeners\SubscriptionListener@deletedPayment',
        ],
        'App\Events\PaymentWasRefunded' => [
            'App\Listeners\TimelineListener@refundedPayment',
            'App\Listeners\InvoiceListener@refundedPayment',
        ],
        'App\Events\PaymentWasVoided' => [
            'App\Listeners\TimelineListener@voidedPayment',
            'App\Listeners\InvoiceListener@voidedPayment',
        ],
        'App\Events\PaymentFailed' => [
            'App\Listeners\TimelineListener@failedPayment',
            'App\Listeners\InvoiceListener@failedPayment',
        ],
        'App\Events\PaymentWasRestored' => [
            'App\Listeners\TimelineListener@restoredPayment',
            'App\Listeners\InvoiceListener@restoredPayment',
        ],

        // Credits
        'App\Events\CreditWasCreated' => [
            'App\Listeners\TimelineListener@createdCredit',
        ],
        'App\Events\CreditWasArchived' => [
            'App\Listeners\TimelineListener@archivedCredit',
        ],
        'App\Events\CreditWasDeleted' => [
            'App\Listeners\TimelineListener@deletedCredit',
        ],
        'App\Events\CreditWasRestored' => [
            'App\Listeners\TimelineListener@restoredCredit',
        ],

        // User events
        'App\Events\UserSignedUp' => [
            'App\Listeners\HandleUserSignedUp',
        ],
        'App\Events\UserLoggedIn' => [
            'App\Listeners\HandleUserLoggedIn',
        ],
        'App\Events\UserSettingsChanged' => [
            'App\Listeners\HandleUserSettingsChanged',
        ],

        // Task events
        'App\Events\TaskWasCreated' => [
            'App\Listeners\TimelineListener@createdTask',
            'App\Listeners\SubscriptionListener@createdTask',
        ],
        'App\Events\TaskWasUpdated' => [
            'App\Listeners\TimelineListener@updatedTask',
            'App\Listeners\SubscriptionListener@updatedTask',
        ],
        'App\Events\TaskWasRestored' => [
            'App\Listeners\TimelineListener@restoredTask',
        ],
        'App\Events\TaskWasArchived' => [
            'App\Listeners\TimelineListener@archivedTask',
        ],
        'App\Events\TaskWasDeleted' => [
            'App\Listeners\TimelineListener@deletedTask',
            'App\Listeners\SubscriptionListener@deletedTask',
            'App\Listeners\HistoryListener@deletedTask',
        ],

        // Vendor events
        'App\Events\VendorWasCreated' => [
            'App\Listeners\SubscriptionListener@createdVendor',
        ],
        'App\Events\VendorWasUpdated' => [
            'App\Listeners\SubscriptionListener@updatedVendor',
        ],
        'App\Events\VendorWasDeleted' => [
            'App\Listeners\SubscriptionListener@deletedVendor',
        ],

        // Expense events
        'App\Events\ExpenseWasCreated' => [
            'App\Listeners\TimelineListener@createdExpense',
            'App\Listeners\SubscriptionListener@createdExpense',
        ],
        'App\Events\ExpenseWasUpdated' => [
            'App\Listeners\TimelineListener@updatedExpense',
            'App\Listeners\SubscriptionListener@updatedExpense',
        ],
        'App\Events\ExpenseWasRestored' => [
            'App\Listeners\TimelineListener@restoredExpense',
        ],
        'App\Events\ExpenseWasArchived' => [
            'App\Listeners\TimelineListener@archivedExpense',
        ],
        'App\Events\ExpenseWasDeleted' => [
            'App\Listeners\TimelineListener@deletedExpense',
            'App\Listeners\SubscriptionListener@deletedExpense',
            'App\Listeners\HistoryListener@deletedExpense',
        ],

        // Project events
        'App\Events\ProjectWasDeleted' => [
            'App\Listeners\HistoryListener@deletedProject',
        ],

        // Proposal events
        'App\Events\ProposalWasDeleted' => [
            'App\Listeners\HistoryListener@deletedProposal',
        ],

        'Illuminate\Queue\Events\JobExceptionOccurred' => [
            'App\Listeners\InvoiceListener@jobFailed'
        ],

        //DNS Add A record to Cloudflare
        'App\Events\SubdomainWasUpdated' => [
            'App\Listeners\DNSListener@addDNSRecord'
        ],

        //DNS Remove A record from Cloudflare
        'App\Events\SubdomainWasRemoved' => [
            'App\Listeners\DNSListener@removeDNSRecord'
        ],
        'App\Events\TicketUserViewed' => [
            'App\Listeners\TimelineListener@userViewedTicket'
        ],

        /*
        // Update events
        \Codedge\Updater\Events\UpdateAvailable::class => [
            \Codedge\Updater\Listeners\SendUpdateAvailableNotification::class,
        ],
        */
    ];

    /**
     * Register any other events for your application.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
