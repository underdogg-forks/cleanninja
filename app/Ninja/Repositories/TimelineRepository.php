<?php

namespace App\Ninja\Repositories;

use App\Models\Timeline;
use App\Models\Client;
use App\Models\Invitation;
use Auth;
use DB;
use Request;
use Utils;
use App;

class TimelineRepository
{
    public function create($entity, $timelineTypeId, $balanceChange = 0, $paidToDateChange = 0, $altEntity = null, $notes = false)
    {
        if ($entity instanceof Client) {
            $client = $entity;
        } elseif ($entity instanceof Invitation) {
            $client = $entity->invoice->client;
        } else {
            $client = $entity->client;
        }

        // init timeline and copy over context
        $timeline = self::getBlank($altEntity ?: ($client ?: $entity));
        $timeline = Utils::copyContext($timeline, $entity);
        $timeline = Utils::copyContext($timeline, $altEntity);

        $timeline->timeline_type_id = $timelineTypeId;
        $timeline->adjustment = $balanceChange;
        $timeline->client_id = $client ? $client->id : null;
        $timeline->balance = $client ? ($client->balance + $balanceChange) : 0;
        $timeline->notes = $notes ?: '';

        $keyField = $entity->getKeyField();
        $timeline->$keyField = $entity->id;

        $timeline->ip = Request::getClientIp();
        $timeline->save();

        if ($client) {
            $client->updateBalances($balanceChange, $paidToDateChange);
        }

        return $timeline;
    }

    private function getBlank($entity)
    {
        $timeline = new Timeline();

        if (Auth::check() && Auth::user()->account_id == $entity->account_id) {
            $timeline->user_id = Auth::user()->id;
            $timeline->account_id = Auth::user()->account_id;
        } else {
            $timeline->user_id = $entity->user_id;
            $timeline->account_id = $entity->account_id;
        }

        $timeline->is_system = App::runningInConsole();
        $timeline->token_id = session('token_id');

        return $timeline;
    }

    public function findByClientId($clientId)
    {
        return DB::table('core__timeline')
                    ->join('accounts', 'accounts.id', '=', 'core__timeline.account_id')
                    ->join('users', 'users.id', '=', 'core__timeline.user_id')
                    ->join('clients', 'clients.id', '=', 'core__timeline.client_id')
                    ->leftJoin('contacts', 'contacts.client_id', '=', 'clients.id')
                    ->leftJoin('invoices', 'invoices.id', '=', 'core__timeline.invoice_id')
                    ->leftJoin('payments', 'payments.id', '=', 'core__timeline.payment_id')
                    ->leftJoin('credits', 'credits.id', '=', 'core__timeline.credit_id')
                    ->leftJoin('tasks', 'tasks.id', '=', 'core__timeline.task_id')
                    ->leftJoin('expenses', 'expenses.id', '=', 'core__timeline.expense_id')
                    ->leftJoin('tickets', 'tickets.id', '=', 'core__timeline.ticket_id')
                    ->where('clients.id', '=', $clientId)
                    ->where('contacts.is_primary', '=', 1)
                    ->whereNull('contacts.deleted_at')
                    ->select(
                        DB::raw('COALESCE(clients.currency_id, accounts.currency_id) currency_id'),
                        DB::raw('COALESCE(clients.country_id, accounts.country_id) country_id'),
                        'core__timeline.id',
                        'core__timeline.created_at',
                        'core__timeline.contact_id',
                        'core__timeline.timeline_type_id',
                        'core__timeline.balance',
                        'core__timeline.adjustment',
                        'core__timeline.notes',
                        'core__timeline.ip',
                        'core__timeline.is_system',
                        'users.first_name as user_first_name',
                        'users.last_name as user_last_name',
                        'users.email as user_email',
                        'invoices.invoice_number as invoice',
                        'invoices.public_id as invoice_public_id',
                        'invoices.is_recurring',
                        'clients.name as client_name',
                        'accounts.name as account_name',
                        'clients.public_id as client_public_id',
                        'contacts.id as contact',
                        'contacts.first_name as first_name',
                        'contacts.last_name as last_name',
                        'contacts.email as email',
                        'payments.transaction_reference as payment',
                        'payments.amount as payment_amount',
                        'credits.amount as credit',
                        'tasks.description as task_description',
                        'tasks.public_id as task_public_id',
                        'expenses.public_notes as expense_public_notes',
                        'expenses.public_id as expense_public_id',
                        'tickets.public_id as ticket_public_id'
                    );
    }
}
