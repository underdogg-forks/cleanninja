<?php

namespace App\Console\Commands;

use Carbon;
use App\Libraries\CurlUtils;
use DB;
use App;
use Exception;
use Illuminate\Console\Command;
use Mail;
use Symfony\Component\Console\Input\InputOption;
use Utils;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Invitation;

/*

##################################################################
WARNING: Please backup your database before running this script
##################################################################

Since the application was released a number of bugs have inevitably been found.
Although the bugs have always been fixed in some cases they've caused the client's
balance, paid to date and/or timeline records to become inaccurate. This script will
check for errors and correct the data.

If you have any questions please email us at contact@invoiceninja.com

Usage:

php artisan ninja:check-data

Options:

--client_id:<value>

    Limits the script to a single client

--fix=true

    By default the script only checks for errors, adding this option
    makes the script apply the fixes.

--fast=true

    Skip using phantomjs

*/

/**
 * Class CheckData.
 */
class CheckData extends Command
{
    /**
     * @var string
     */
    protected $name = 'ninja:check-data';

    /**
     * @var string
     */
    protected $description = 'Check/fix data';

    protected $log = '';
    protected $isValid = true;

    public function fire()
    {
        $this->logMessage(date('Y-m-d h:i:s') . ' Running CheckData...');

        if ($database = $this->option('database')) {
            config(['database.default' => $database]);
        }

        if (! $this->option('client_id')) {
            $this->checkBlankInvoiceHistory();
            $this->checkPaidToDate();
            $this->checkDraftSentInvoices();
        }

        //$this->checkInvoices();
        $this->checkTranslations();
        $this->checkInvoiceBalances();
        $this->checkClientBalances();
        $this->checkContacts();
        $this->checkUserAccounts();
        //$this->checkLogoFiles();

        if (! $this->option('client_id')) {
            $this->checkOAuth();
            $this->checkInvitations();
            $this->checkAccountData();
            $this->checkLookupData();
            $this->checkFailedJobs();
        }

        $this->logMessage('Done: ' . strtoupper($this->isValid ? RESULT_SUCCESS : RESULT_FAILURE));
        $errorEmail = env('ERROR_EMAIL');

        if ($errorEmail) {
            Mail::raw($this->log, function ($message) use ($errorEmail, $database) {
                $message->to($errorEmail)
                        ->from(CONTACT_EMAIL)
                        ->subject("Check-Data: " . strtoupper($this->isValid ? RESULT_SUCCESS : RESULT_FAILURE) . " [{$database}]");
            });
        } elseif (! $this->isValid) {
            throw new Exception("Check data failed!!\n" . $this->log);
        }
    }

    private function logMessage($str)
    {
        $str = date('Y-m-d h:i:s') . ' ' . $str;
        $this->info($str);
        $this->log .= $str . "\n";
    }

    private function checkTranslations()
    {
        $invalid = 0;

        foreach (cache('languages') as $language) {
            App::setLocale($language->locale);
            foreach (trans('texts') as $text) {
                if (strpos($text, '=') !== false) {
                    $invalid++;
                    $this->logMessage($language->locale . ' is invalid: ' . $text);
                }

                preg_match('/(.script)/', strtolower($text), $matches);
                if (count($matches)) {
                    foreach ($matches as $match) {
                        if (in_array($match, ['escript', 'bscript', 'nscript'])) {
                            continue;
                        }
                        $invalid++;
                        $this->logMessage(sprintf('%s is invalid: %s', $language->locale, $text));
                        break;
                    }
                }
            }
        }

        if ($invalid > 0) {
            $this->isValid = false;
        }

        App::setLocale('en');
        $this->logMessage($invalid . ' invalid text strings');
    }

    private function checkDraftSentInvoices()
    {
        $invoices = Invoice::whereInvoiceStatusId(INVOICE_STATUS_SENT)
                        ->whereIsPublic(false)
                        ->withTrashed()
                        ->get();

        $this->logMessage($invoices->count() . ' draft sent invoices');

        if ($invoices->count() > 0) {
            $this->isValid = false;
        }

        if ($this->option('fix') == 'true') {
            foreach ($invoices as $invoice) {
                $dispatcher = $invoice->getEventDispatcher();
                if ($invoice->is_deleted) {
                    $invoice->unsetEventDispatcher();
                }
                $invoice->is_public = true;
                $invoice->save();
                $invoice->markInvitationsSent();
                $invoice->setEventDispatcher($dispatcher);
            }
        }
    }

    private function checkInvoices()
    {
        if (! env('PHANTOMJS_BIN_PATH') || ! Utils::isNinjaProd()) {
            return;
        }

        if ($this->option('fix') == 'true' || $this->option('fast') == 'true') {
            return;
        }

        $isValid = true;
        $date = new Carbon();
        $date = $date->subDays(1)->format('Y-m-d');

        $invoices = Invoice::with('invitations')
            ->where('created_at', '>',  $date)
            ->orderBy('id')
            ->get();

        foreach ($invoices as $invoice) {
            $link = $invoice->getInvitationLink('view', true, true);
            $result = CurlUtils::phantom('GET', $link . '?phantomjs=true&phantomjs_balances=true&phantomjs_secret=' . env('PHANTOMJS_SECRET'));
            $result = floatval(strip_tags($result));
            $invoice = $invoice->fresh();

            //$this->logMessage('Checking invoice: ' . $invoice->id . ' - ' . $invoice->balance);
            //$this->logMessage('Result: ' . $result);

            if ($result && $result != $invoice->balance) {
                $this->logMessage("PHP/JS amounts do not match {$link}?silent=true | PHP: {$invoice->balance}, JS: {$result}");
                $this->isValid = $isValid = false;
            }
        }

        if ($isValid) {
            $this->logMessage('0 invoices with mismatched PHP/JS balances');
        }
    }

    private function checkOAuth()
    {
        // check for duplicate oauth ids
        $users = DB::table('users')
                    ->whereNotNull('oauth_user_id')
                    ->groupBy('users.oauth_user_id')
                    ->havingRaw('count(users.id) > 1')
                    ->get(['users.oauth_user_id']);

        $this->logMessage($users->count() . ' users with duplicate oauth ids');

        if ($users->count() > 0) {
            $this->isValid = false;
        }

        if ($this->option('fix') == 'true') {
            foreach ($users as $user) {
                $first = true;
                $this->logMessage('checking ' . $user->oauth_user_id);
                $matches = DB::table('users')
                            ->where('oauth_user_id', '=', $user->oauth_user_id)
                            ->orderBy('id')
                            ->get(['id']);

                foreach ($matches as $match) {
                    if ($first) {
                        $this->logMessage('skipping ' . $match->id);
                        $first = false;
                        continue;
                    }
                    $this->logMessage('updating ' . $match->id);

                    DB::table('users')
                        ->where('id', '=', $match->id)
                        ->where('oauth_user_id', '=', $user->oauth_user_id)
                        ->update([
                            'oauth_user_id' => null,
                            'oauth_provider_id' => null,
                        ]);
                }
            }
        }
    }

    private function checkLookupData()
    {
        $tables = [
            'account_tokens',
            'accounts',
            'core__plans',
            'contacts',
            'invitations',
            'users',
        ];

        foreach ($tables as $table) {
            $count = DB::table('lookup_' . $table)->count();
            if ($count > 0) {
                $this->logMessage("Lookup table {$table} has {$count} records");
                $this->isValid = false;
            }
        }
    }

    private function checkUserAccounts()
    {
        $userAccounts = DB::table('user_accounts')
                        ->leftJoin('users as u1', 'u1.id', '=', 'user_accounts.user_id1')
                        ->leftJoin('accounts as a1', 'a1.id', '=', 'u1.account_id')
                        ->leftJoin('users as u2', 'u2.id', '=', 'user_accounts.user_id2')
                        ->leftJoin('accounts as a2', 'a2.id', '=', 'u2.account_id')
                        ->leftJoin('users as u3', 'u3.id', '=', 'user_accounts.user_id3')
                        ->leftJoin('accounts as a3', 'a3.id', '=', 'u3.account_id')
                        ->leftJoin('users as u4', 'u4.id', '=', 'user_accounts.user_id4')
                        ->leftJoin('accounts as a4', 'a4.id', '=', 'u4.account_id')
                        ->leftJoin('users as u5', 'u5.id', '=', 'user_accounts.user_id5')
                        ->leftJoin('accounts as a5', 'a5.id', '=', 'u5.account_id')
                        ->get([
                            'user_accounts.id',
                            'a1.plan_id as a1_plan_id',
                            'a2.plan_id as a2_plan_id',
                            'a3.plan_id as a3_plan_id',
                            'a4.plan_id as a4_plan_id',
                            'a5.plan_id as a5_plan_id',
                        ]);

        $countInvalid = 0;

        foreach ($userAccounts as $userAccount) {
            $ids = [];

            if ($planId1 = $userAccount->a1_plan_id) {
                $ids[$planId1] = true;
            }
            if ($planId2 = $userAccount->a2_plan_id) {
                $ids[$planId2] = true;
            }
            if ($planId3 = $userAccount->a3_plan_id) {
                $ids[$planId3] = true;
            }
            if ($planId4 = $userAccount->a4_plan_id) {
                $ids[$planId4] = true;
            }
            if ($planId5 = $userAccount->a5_plan_id) {
                $ids[$planId5] = true;
            }

            if (count($ids) > 1) {
                $this->info('user_account: ' . $userAccount->id);
                $countInvalid++;
            }
        }

        $this->logMessage($countInvalid . ' user accounts with multiple plans');

        if ($countInvalid > 0) {
            $this->isValid = false;
        }
    }

    private function checkContacts()
    {
        // check for contacts with the contact_key value set
        $contacts = DB::table('contacts')
                        ->whereNull('contact_key')
                        ->orderBy('id')
                        ->get(['id']);
        $this->logMessage($contacts->count() . ' contacts without a contact_key');

        if ($contacts->count() > 0) {
            $this->isValid = false;
        }

        if ($this->option('fix') == 'true') {
            foreach ($contacts as $contact) {
                DB::table('contacts')
                    ->where('id', '=', $contact->id)
                    ->whereNull('contact_key')
                    ->update([
                        'contact_key' => strtolower(str_random(RANDOM_KEY_LENGTH)),
                    ]);
            }
        }

        // check for missing contacts
        $clients = DB::table('clients')
                    ->leftJoin('contacts', function($join) {
                        $join->on('contacts.client_id', '=', 'clients.id')
                            ->whereNull('contacts.deleted_at');
                    })
                    ->groupBy('clients.id', 'clients.user_id', 'clients.account_id')
                    ->havingRaw('count(contacts.id) = 0');

        if ($this->option('client_id')) {
            $clients->where('clients.id', '=', $this->option('client_id'));
        }

        $clients = $clients->get(['clients.id', 'clients.user_id', 'clients.account_id']);
        $this->logMessage($clients->count() . ' clients without any contacts');

        if ($clients->count() > 0) {
            $this->isValid = false;
        }

        if ($this->option('fix') == 'true') {
            foreach ($clients as $client) {
                $contact = new Contact();
                $contact->account_id = $client->account_id;
                $contact->user_id = $client->user_id;
                $contact->client_id = $client->id;
                $contact->is_primary = true;
                $contact->send_invoice = true;
                $contact->contact_key = strtolower(str_random(RANDOM_KEY_LENGTH));
                $contact->public_id = Contact::whereAccountId($client->account_id)->withTrashed()->max('public_id') + 1;
                $contact->save();
            }
        }

        // check for more than one primary contact
        $clients = DB::table('clients')
                    ->leftJoin('contacts', function($join) {
                        $join->on('contacts.client_id', '=', 'clients.id')
                            ->where('contacts.is_primary', '=', true)
                            ->whereNull('contacts.deleted_at');
                    })
                    ->groupBy('clients.id')
                    ->havingRaw('count(contacts.id) != 1');

        if ($this->option('client_id')) {
            $clients->where('clients.id', '=', $this->option('client_id'));
        }

        $clients = $clients->get(['clients.id', DB::raw('count(contacts.id)')]);
        $this->logMessage($clients->count() . ' clients without a single primary contact');

        if ($clients->count() > 0) {
            $this->isValid = false;
        }
    }

    private function checkFailedJobs()
    {
        if (Utils::isTravis()) {
            return;
        }

        $queueDB = config('queue.connections.database.connection');
        $count = DB::connection($queueDB)->table('core__failedjobs')->count();

        if ($count > 0) {
            $this->isValid = false;
        }

        $this->logMessage($count . ' failed jobs');
    }

    private function checkBlankInvoiceHistory()
    {
        $count = DB::table('core__timeline')
                    ->where('timeline_type_id', '=', 5)
                    ->where('json_backup', '=', '')
                    ->where('id', '>', 858720)
                    ->count();

        if ($count > 0) {
            $this->isValid = false;
        }

        $this->logMessage($count . ' timeline with blank invoice backup');
    }

    private function checkInvitations()
    {
        $invoices = DB::table('invoices')
                    ->leftJoin('invitations', function ($join) {
                        $join->on('invitations.invoice_id', '=', 'invoices.id')
                             ->whereNull('invitations.deleted_at');
                    })
                    ->groupBy('invoices.id', 'invoices.user_id', 'invoices.account_id', 'invoices.client_id')
                    ->havingRaw('count(invitations.id) = 0')
                    ->get(['invoices.id', 'invoices.user_id', 'invoices.account_id', 'invoices.client_id']);

        $this->logMessage($invoices->count() . ' invoices without any invitations');

        if ($invoices->count() > 0) {
            $this->isValid = false;
        }

        if ($this->option('fix') == 'true') {
            foreach ($invoices as $invoice) {
                $invitation = new Invitation();
                $invitation->account_id = $invoice->account_id;
                $invitation->user_id = $invoice->user_id;
                $invitation->invoice_id = $invoice->id;
                $invitation->contact_id = Contact::whereClientId($invoice->client_id)->whereIsPrimary(true)->first()->id;
                $invitation->invitation_key = strtolower(str_random(RANDOM_KEY_LENGTH));
                $invitation->public_id = Invitation::whereAccountId($invoice->account_id)->withTrashed()->max('public_id') + 1;
                $invitation->save();
            }
        }
    }

    private function checkAccountData()
    {
        $tables = [
            'timeline' => [
                ENTITY_INVOICE,
                ENTITY_CLIENT,
                ENTITY_CONTACT,
                ENTITY_PAYMENT,
                ENTITY_INVITATION,
                ENTITY_USER,
            ],
            'invoices' => [
                ENTITY_CLIENT,
                ENTITY_USER,
            ],
            'payments' => [
                ENTITY_INVOICE,
                ENTITY_CLIENT,
                ENTITY_USER,
                ENTITY_INVITATION,
                ENTITY_CONTACT,
            ],
            'tasks' => [
                ENTITY_INVOICE,
                ENTITY_CLIENT,
                ENTITY_USER,
                ENTITY_TASK_STATUS,
            ],
            'task_statuses' => [
                ENTITY_USER,
            ],
            'bookkeeping__credits' => [
                ENTITY_CLIENT,
                ENTITY_USER,
            ],
            'expenses' => [
                ENTITY_CLIENT,
                ENTITY_VENDOR,
                ENTITY_INVOICE,
                ENTITY_USER,
            ],
            'products' => [
                ENTITY_USER,
            ],
            'vendors' => [
                ENTITY_USER,
            ],
            'expenses__categories' => [
                ENTITY_USER,
            ],
            'payment_terms' => [
                ENTITY_USER,
            ],
            'projects' => [
                ENTITY_USER,
                ENTITY_CLIENT,
            ],
            'proposals' => [
                ENTITY_USER,
                ENTITY_INVOICE,
                ENTITY_PROPOSAL_TEMPLATE,
            ],
            'proposals__categories' => [
                ENTITY_USER,
            ],
            'proposals__templates' => [
                ENTITY_USER,
            ],
            'proposals__snippets' => [
                ENTITY_USER,
                ENTITY_PROPOSAL_CATEGORY,
            ],
            'proposals__invitations' => [
                ENTITY_USER,
                ENTITY_PROPOSAL,
            ],
        ];

        foreach ($tables as $table => $entityTypes) {
            foreach ($entityTypes as $entityType) {
                $tableName = Utils::pluralizeEntityType($entityType);
                $field = $entityType;
                if ($table == 'accounts') {
                    $accountId = 'id';
                } else {
                    $accountId = 'account_id';
                }
                $records = DB::table($table)
                                ->join($tableName, "{$tableName}.id", '=', "{$table}.{$field}_id")
                                ->where("{$table}.{$accountId}", '!=', DB::raw("{$tableName}.account_id"))
                                ->get(["{$table}.id"]);

                if ($records->count()) {
                    $this->isValid = false;
                    $this->logMessage($records->count() . " {$table} records with incorrect {$entityType} account id");

                    if ($this->option('fix') == 'true') {
                        foreach ($records as $record) {
                            DB::table($table)
                                ->where('id', $record->id)
                                ->update([
                                    'account_id' => $record->account_id,
                                    'user_id' => $record->user_id,
                                ]);
                        }
                    }
                }
            }
        }
    }

    private function checkPaidToDate()
    {
        // update client paid_to_date value
        $clients = DB::table('clients')
                    ->leftJoin('invoices', function($join) {
                        $join->on('invoices.client_id', '=', 'clients.id')
                            ->where('invoices.is_deleted', '=', 0);
                    })
                    ->leftJoin('payments', function($join) {
                        $join->on('payments.invoice_id', '=', 'invoices.id')
                            ->where('payments.payment_status_id', '!=', 2)
                            ->where('payments.payment_status_id', '!=', 3)
                            ->where('payments.is_deleted', '=', 0);
                    })
                    ->where('clients.updated_at', '>', '2017-10-01')
                    ->groupBy('clients.id')
                    ->havingRaw('clients.paid_to_date != sum(coalesce(payments.amount - payments.refunded, 0)) and clients.paid_to_date != 999999999.9999')
                    ->get(['clients.id', 'clients.paid_to_date', DB::raw('sum(coalesce(payments.amount - payments.refunded, 0)) as amount')]);
        $this->logMessage($clients->count() . ' clients with incorrect paid to date');

        if ($clients->count() > 0) {
            $this->isValid = false;
        }

        /*
        if ($this->option('fix') == 'true') {
            foreach ($clients as $client) {
                DB::table('clients')
                    ->where('id', $client->id)
                    ->update(['paid_to_date' => $client->amount]);
            }
        }
        */
    }

    private function checkInvoiceBalances()
    {
        $invoices = DB::table('invoices')
                    ->leftJoin('payments', function($join) {
                        $join->on('payments.invoice_id', '=', 'invoices.id')
                            ->where('payments.payment_status_id', '!=', 2)
                            ->where('payments.payment_status_id', '!=', 3)
                            ->where('payments.is_deleted', '=', 0);
                    })
                    ->where('invoices.updated_at', '>', '2017-10-01')
                    ->groupBy('invoices.id')
                    ->havingRaw('(invoices.amount - invoices.balance) != coalesce(sum(payments.amount - payments.refunded), 0)')
                    ->get(['invoices.id', 'invoices.amount', 'invoices.balance', DB::raw('coalesce(sum(payments.amount - payments.refunded), 0)')]);

        $this->logMessage($invoices->count() . ' invoices with incorrect balances');

        if ($invoices->count() > 0) {
            $this->isValid = false;
        }
    }

    private function checkClientBalances()
    {
        // find all clients where the balance doesn't equal the sum of the outstanding invoices
        $clients = DB::table('clients')
                    ->join('invoices', 'invoices.client_id', '=', 'clients.id')
                    ->join('accounts', 'accounts.id', '=', 'clients.account_id')
                    ->where('accounts.id', '!=', 20432)
                    ->where('clients.is_deleted', '=', 0)
                    ->where('invoices.is_deleted', '=', 0)
                    ->where('invoices.is_public', '=', 1)
                    ->where('invoices.invoice_type_id', '=', INVOICE_TYPE_STANDARD)
                    ->where('invoices.is_recurring', '=', 0)
                    ->havingRaw('abs(clients.balance - sum(invoices.balance)) > .01 and clients.balance != 999999999.9999');

        if ($this->option('client_id')) {
            $clients->where('clients.id', '=', $this->option('client_id'));
        }

        $clients = $clients->groupBy('clients.id', 'clients.balance')
                ->orderBy('accounts.plan_id', 'DESC')
                ->get(['accounts.plan_id', 'clients.account_id', 'clients.id', 'clients.balance', 'clients.paid_to_date', DB::raw('sum(invoices.balance) actual_balance')]);
        $this->logMessage($clients->count() . ' clients with incorrect balance/timeline');

        if ($clients->count() > 0) {
            $this->isValid = false;
        }

        foreach ($clients as $client) {
            $this->logMessage("=== Plan: {$client->plan_id} Account:{$client->account_id} Client:{$client->id} Balance:{$client->balance} Actual Balance:{$client->actual_balance} ===");
            $foundProblem = false;
            $lastBalance = 0;
            $lastAdjustment = 0;
            $lastCreatedAt = null;
            $clientFix = false;
            $timeline = DB::table('core__timeline')
                        ->where('client_id', '=', $client->id)
                        ->orderBy('core__timeline.id')
                        ->get(['core__timeline.id', 'core__timeline.created_at', 'core__timeline.timeline_type_id', 'core__timeline.adjustment', 'core__timeline.balance', 'core__timeline.invoice_id']);
            //$this->logMessage(var_dump($timeline));

            foreach ($timeline as $timeline) {
                $timelineFix = false;

                if ($timeline->invoice_id) {
                    $invoice = DB::table('invoices')
                                ->where('id', '=', $timeline->invoice_id)
                                ->first(['invoices.amount', 'invoices.is_recurring', 'invoices.invoice_type_id', 'invoices.deleted_at', 'invoices.id', 'invoices.is_deleted']);

                    // Check if this invoice was once set as recurring invoice
                    if ($invoice && ! $invoice->is_recurring && DB::table('invoices')
                            ->where('recurring_invoice_id', '=', $timeline->invoice_id)
                            ->first(['invoices.id'])) {
                        $invoice->is_recurring = 1;

                        // **Fix for enabling a recurring invoice to be set as non-recurring**
                        if ($this->option('fix') == 'true') {
                            DB::table('invoices')
                                ->where('id', $invoice->id)
                                ->update(['is_recurring' => 1]);
                        }
                    }
                }

                if ($timeline->timeline_type_id == TIMELINE_TYPE_CREATE_INVOICE
                    || $timeline->timeline_type_id == TIMELINE_TYPE_CREATE_QUOTE) {

                    // Get original invoice amount
                    $update = DB::table('core__timeline')
                                ->where('invoice_id', '=', $timeline->invoice_id)
                                ->where('timeline_type_id', '=', TIMELINE_TYPE_UPDATE_INVOICE)
                                ->orderBy('id')
                                ->first(['json_backup']);
                    if ($update) {
                        $backup = json_decode($update->json_backup);
                        $invoice->amount = floatval($backup->amount);
                    }

                    $noAdjustment = $timeline->timeline_type_id == TIMELINE_TYPE_CREATE_INVOICE
                        && $timeline->adjustment == 0
                        && $invoice->amount > 0;

                    // **Fix for ninja invoices which didn't have the invoice_type_id value set
                    if ($noAdjustment && $client->account_id == 20432) {
                        $this->logMessage('No adjustment for ninja invoice');
                        $foundProblem = true;
                        $clientFix += $invoice->amount;
                        $timelineFix = $invoice->amount;
                    // **Fix for allowing converting a recurring invoice to a normal one without updating the balance**
                    } elseif ($noAdjustment && $invoice->invoice_type_id == INVOICE_TYPE_STANDARD && ! $invoice->is_recurring) {
                        $this->logMessage("No adjustment for new invoice:{$timeline->invoice_id} amount:{$invoice->amount} invoiceTypeId:{$invoice->invoice_type_id} isRecurring:{$invoice->is_recurring}");
                        $foundProblem = true;
                        $clientFix += $invoice->amount;
                        $timelineFix = $invoice->amount;
                    // **Fix for updating balance when creating a quote or recurring invoice**
                    } elseif ($timeline->adjustment != 0 && ($invoice->invoice_type_id == INVOICE_TYPE_QUOTE || $invoice->is_recurring)) {
                        $this->logMessage("Incorrect adjustment for new invoice:{$timeline->invoice_id} adjustment:{$timeline->adjustment} invoiceTypeId:{$invoice->invoice_type_id} isRecurring:{$invoice->is_recurring}");
                        $foundProblem = true;
                        $clientFix -= $timeline->adjustment;
                        $timelineFix = 0;
                    }
                } elseif ($timeline->timeline_type_id == TIMELINE_TYPE_DELETE_INVOICE) {
                    // **Fix for updating balance when deleting a recurring invoice**
                    if ($timeline->adjustment != 0 && $invoice->is_recurring) {
                        $this->logMessage("Incorrect adjustment for deleted invoice adjustment:{$timeline->adjustment}");
                        $foundProblem = true;
                        if ($timeline->balance != $lastBalance) {
                            $clientFix -= $timeline->adjustment;
                        }
                        $timelineFix = 0;
                    }
                } elseif ($timeline->timeline_type_id == TIMELINE_TYPE_ARCHIVE_INVOICE) {
                    // **Fix for updating balance when archiving an invoice**
                    if ($timeline->adjustment != 0 && ! $invoice->is_recurring) {
                        $this->logMessage("Incorrect adjustment for archiving invoice adjustment:{$timeline->adjustment}");
                        $foundProblem = true;
                        $timelineFix = 0;
                        $clientFix += $timeline->adjustment;
                    }
                } elseif ($timeline->timeline_type_id == TIMELINE_TYPE_UPDATE_INVOICE) {
                    // **Fix for updating balance when updating recurring invoice**
                    if ($timeline->adjustment != 0 && $invoice->is_recurring) {
                        $this->logMessage("Incorrect adjustment for updated recurring invoice adjustment:{$timeline->adjustment}");
                        $foundProblem = true;
                        $clientFix -= $timeline->adjustment;
                        $timelineFix = 0;
                    } elseif ((strtotime($timeline->created_at) - strtotime($lastCreatedAt) <= 1) && $timeline->adjustment > 0 && $timeline->adjustment == $lastAdjustment) {
                        $this->logMessage("Duplicate adjustment for updated invoice adjustment:{$timeline->adjustment}");
                        $foundProblem = true;
                        $clientFix -= $timeline->adjustment;
                        $timelineFix = 0;
                    }
                } elseif ($timeline->timeline_type_id == TIMELINE_TYPE_UPDATE_QUOTE) {
                    // **Fix for updating balance when updating a quote**
                    if ($timeline->balance != $lastBalance) {
                        $this->logMessage("Incorrect adjustment for updated quote adjustment:{$timeline->adjustment}");
                        $foundProblem = true;
                        $clientFix += $lastBalance - $timeline->balance;
                        $timelineFix = 0;
                    }
                } elseif ($timeline->timeline_type_id == TIMELINE_TYPE_DELETE_PAYMENT) {
                    // **Fix for deleting payment after deleting invoice**
                    if ($timeline->adjustment != 0 && $invoice->is_deleted && $timeline->created_at > $invoice->deleted_at) {
                        $this->logMessage("Incorrect adjustment for deleted payment adjustment:{$timeline->adjustment}");
                        $foundProblem = true;
                        $timelineFix = 0;
                        $clientFix -= $timeline->adjustment;
                    }
                }

                if ($timelineFix !== false || $clientFix !== false) {
                    $data = [
                        'balance' => $timeline->balance + $clientFix,
                    ];

                    if ($timelineFix !== false) {
                        $data['adjustment'] = $timelineFix;
                    }

                    if ($this->option('fix') == 'true') {
                        DB::table('core__timeline')
                            ->where('id', $timeline->id)
                            ->update($data);
                    }
                }

                $lastBalance = $timeline->balance;
                $lastAdjustment = $timeline->adjustment;
                $lastCreatedAt = $timeline->created_at;
            }

            if ($timeline->balance + $clientFix != $client->actual_balance) {
                $this->logMessage("** Creating 'recovered update' timeline **");
                if ($this->option('fix') == 'true') {
                    DB::table('core__timeline')->insert([
                            'created_at' => new Carbon(),
                            'updated_at' => new Carbon(),
                            'account_id' => $client->account_id,
                            'client_id' => $client->id,
                            'adjustment' => $client->actual_balance - $timeline->balance,
                            'balance' => $client->actual_balance,
                    ]);
                }
            }

            $data = ['balance' => $client->actual_balance];
            $this->logMessage("Corrected balance:{$client->actual_balance}");
            if ($this->option('fix') == 'true') {
                DB::table('clients')
                    ->where('id', $client->id)
                    ->update($data);
            }
        }
    }

    private function checkLogoFiles()
    {
        $accounts = DB::table('accounts')
                    ->where('logo', '!=', '')
                    ->orderBy('id')
                    ->get(['logo']);

        $countMissing = 0;

        foreach ($accounts as $account) {
            $path = public_path('logo/' . $account->logo);
            if (! file_exists($path)) {
                $this->logMessage('Missing file: ' . $account->logo);
                $countMissing++;
            }
        }

        if ($countMissing > 0) {
            $this->isValid = false;
        }

        $this->logMessage($countMissing . ' missing logo files');
    }

    /**
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['fix', null, InputOption::VALUE_OPTIONAL, 'Fix data', null],
            ['fast', null, InputOption::VALUE_OPTIONAL, 'Fast', null],
            ['client_id', null, InputOption::VALUE_OPTIONAL, 'Client id', null],
            ['database', null, InputOption::VALUE_OPTIONAL, 'Database', null],
        ];
    }
}
