<?php

namespace App\Ninja\Repositories;

use App\Models\BankAccount;
use App\Models\BankSubaccount;
use Crypt;
use DB;

class BankAccountRepository extends BaseRepository
{
    public function getClassName()
    {
        return 'App\Models\BankAccount';
    }

    public function find($accountId)
    {
        return DB::table('banking__bankaccounts')
                    ->join('banking__banks', 'banks.id', '=', 'banking__bankaccounts.bank_id')
                    ->where('banking__bankaccounts.deleted_at', '=', null)
                    ->where('banking__bankaccounts.account_id', '=', $accountId)
                    ->select(
                        'banking__bankaccounts.public_id',
                        'banks.name as bank_name',
                        'banking__bankaccounts.deleted_at',
                        'banks.bank_library_id'
                    );
    }

    public function save($input)
    {
        $bankAccount = BankAccount::createNew();
        $bankAccount->username = Crypt::encrypt(trim($input['bank_username']));
        $bankAccount->fill($input);

        $account = \Auth::user()->account;
        $account->banking__bankaccounts()->save($bankAccount);

        foreach ($input['banking__bankaccounts'] as $data) {
            if (! isset($data['include']) || ! filter_var($data['include'], FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            $subaccount = BankSubaccount::createNew();
            $subaccount->account_name = trim($data['account_name']);
            $subaccount->account_number = trim($data['hashed_account_number']);
            $bankAccount->bank_subaccounts()->save($subaccount);
        }

        return $bankAccount;
    }
}
