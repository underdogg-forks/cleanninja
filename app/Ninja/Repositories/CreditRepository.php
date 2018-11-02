<?php

namespace App\Ninja\Repositories;

use App\Models\Client;
use App\Models\Credit;
use DB;
use Utils;

class CreditRepository extends BaseRepository
{
    public function getClassName()
    {
        return 'App\Models\Credit';
    }

    public function find($clientPublicId = null, $filter = null)
    {
        $query = DB::table('bookkeeping__credits')
                    ->join('accounts', 'accounts.id', '=', 'bookkeeping__credits.account_id')
                    ->join('clients', 'clients.id', '=', 'bookkeeping__credits.client_id')
                    ->join('contacts', 'contacts.client_id', '=', 'clients.id')
                    ->where('clients.account_id', '=', \Auth::user()->account_id)
                    ->where('contacts.deleted_at', '=', null)
                    ->where('contacts.is_primary', '=', true)
                    ->select(
                        DB::raw('COALESCE(clients.currency_id, accounts.currency_id) currency_id'),
                        DB::raw('COALESCE(clients.country_id, accounts.country_id) country_id'),
                        'bookkeeping__credits.public_id',
                        DB::raw("COALESCE(NULLIF(clients.name,''), NULLIF(CONCAT(contacts.first_name, ' ', contacts.last_name),''), NULLIF(contacts.email,'')) client_name"),
                        'clients.public_id as client_public_id',
                        'clients.user_id as client_user_id',
                        'bookkeeping__credits.amount',
                        'bookkeeping__credits.balance',
                        'bookkeeping__credits.credit_date as credit_date_sql',
                        DB::raw("CONCAT(bookkeeping__credits.credit_date, bookkeeping__credits.created_at) as credit_date"),
                        'contacts.first_name',
                        'contacts.last_name',
                        'contacts.email',
                        'bookkeeping__credits.private_notes',
                        'bookkeeping__credits.public_notes',
                        'bookkeeping__credits.deleted_at',
                        'bookkeeping__credits.is_deleted',
                        'bookkeeping__credits.user_id'
                    );

        /*
        if ($clientPublicId) {
            $query->where('clients.public_id', '=', $clientPublicId);
        } else {
            $query->whereNull('clients.deleted_at');
        }
        */

        //$query, $entityType, $table = false
        //$this->applyFilters($query, ENTITY_CREDIT, $table = 'bookkeeping__credits');

        if ($filter) {
            $query->where(function ($query) use ($filter) {
                $query->where('clients.name', 'like', '%'.$filter.'%');
            });
        }



        return $query;
    }

    public function getClientDatatable($clientId)
    {
        $query = DB::table('bookkeeping__credits')
                    ->join('accounts', 'accounts.id', '=', 'bookkeeping__credits.account_id')
                    ->join('clients', 'clients.id', '=', 'bookkeeping__credits.client_id')
                    ->where('bookkeeping__credits.client_id', '=', $clientId)
                    ->where('clients.deleted_at', '=', null)->select(
                             DB::raw('COALESCE(clients.currency_id, accounts.currency_id) currency_id'),
                             DB::raw('COALESCE(clients.country_id, accounts.country_id) country_id'),
                             'bookkeeping__credits.amount',
                             'bookkeeping__credits.balance',
                             'bookkeeping__credits.credit_date',
                             'bookkeeping__credits.public_notes')
                    ->select(
                             DB::raw('COALESCE(clients.currency_id, accounts.currency_id) currency_id'),
                             DB::raw('COALESCE(clients.country_id, accounts.country_id) country_id'),
                             'bookkeeping__credits.amount',
                             'bookkeeping__credits.balance',
                             'bookkeeping__credits.credit_date',
                             'bookkeeping__credits.public_notes'
                    );

/**
 * ->select(
                    //     DB::raw('COALESCE(clients.currency_id, accounts.currency_id) currency_id'),
                    //     DB::raw('COALESCE(clients.country_id, accounts.country_id) country_id'),
                    //     'bookkeeping__credits.amount',
                    //     'bookkeeping__credits.balance',
                    //     'bookkeeping__credits.credit_date',
                    //     'bookkeeping__credits.public_notes'
                    // )
 * 
 * 
*/



        $table = \Datatable::query($query)
            ->addColumn('credit_date', function ($model) {
                return Utils::fromSqlDate($model->credit_date);
            })
            ->addColumn('amount', function ($model) {
                return Utils::formatMoney($model->amount, $model->currency_id, $model->country_id);
            })
            ->addColumn('balance', function ($model) {
                return Utils::formatMoney($model->balance, $model->currency_id, $model->country_id);
            })
            ->addColumn('public_notes', function ($model) {
                return e($model->public_notes);
            })
            ->make();



dd($table);



        return $table;
    }

    public function save($input, $credit = null)
    {
        $publicId = isset($data['public_id']) ? $data['public_id'] : false;

        if ($credit) {
            // do nothing
        } elseif ($publicId) {
            $credit = Credit::scope($publicId)->firstOrFail();
            \Log::warning('Entity not set in credit repo save');
        } else {
            $credit = Credit::createNew();
            $credit->balance = Utils::parseFloat($input['amount']);
            $credit->client_id = Client::getPrivateId($input['client_id']);
            $credit->credit_date = date('Y-m-d');
        }

        $credit->fill($input);

        if (isset($input['credit_date'])) {
            $credit->credit_date = Utils::toSqlDate($input['credit_date']);
        }
        if (isset($input['amount'])) {
            $credit->amount = Utils::parseFloat($input['amount']);
        }
        if (isset($input['balance'])) {
            $credit->balance = Utils::parseFloat($input['balance']);
        }

        $credit->save();

        return $credit;
    }
}
