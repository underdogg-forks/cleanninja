<?php

namespace App\Models;

use Eloquent;

/**
 * Class ExpenseCategory.
 */
class LookupAccount extends LookupModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'lookup_plan_id',
        'account_key',
        'support_email_local_part',
    ];

    public function lookupPlan()
    {
        return $this->belongsTo('App\Models\LookupPlan');
    }

    public static function createAccount($accountKey, $planId)
    {
        if (! config('ninja.multi_db_enabled'))
            return;


        $current = config('database.default');
        config(['database.default' => DB_NINJA_LOOKUP]);

        $server = DbServer::whereName($current)->firstOrFail();
        $lookupPlan = LookupPlan::whereDbServerId($server->id)
                            ->wherePlanId($planId)->first();

        if (! $lookupPlan) {
            $lookupPlan = LookupPlan::create([
                'db_server_id' => $server->id,
                'plan_id' => $planId,
            ]);
        }

        LookupAccount::create([
            'lookup_plan_id' => $lookupPlan->id,
            'account_key' => $accountKey,
        ]);

        static::setDbServer($current);
    }

    public function getDbServer()
    {
        return $this->lookupPlan->dbServer->name;
    }

    public static function updateAccount($accountKey, $account)
    {
        if (! config('ninja.multi_db_enabled'))
            return;


        $current = config('database.default');
        config(['database.default' => DB_NINJA_LOOKUP]);

        $lookupAccount = LookupAccount::whereAccountKey($accountKey)
                            ->firstOrFail();

        $lookupAccount->subdomain = $account->subdomain ?: null;
        $lookupAccount->save();

        config(['database.default' => $current]);
    }

    public static function updateSupportLocalPart($accountKey, $support_email_local_part)
    {
        if (! config('ninja.multi_db_enabled'))
            return;


        $current = config('database.default');
        config(['database.default' => DB_NINJA_LOOKUP]);

        $lookupAccount = LookupAccount::whereAccountKey($accountKey)
            ->firstOrFail();

        $lookupAccount->support_email_local_part = $support_email_local_part ?: null;
        $lookupAccount->save();

        config(['database.default' => $current]);
    }


    public static function validateField($field, $value, $account = false)
    {
        if (! config('ninja.multi_db_enabled'))
            return true;


        $current = config('database.default');

        config(['database.default' => DB_NINJA_LOOKUP]);

        $lookupAccount = LookupAccount::where($field, '=', $value)->first();

        if ($account) {
            $isValid = ! $lookupAccount || ($lookupAccount->account_key == $account->account_key);
        } else {
            $isValid = ! $lookupAccount;
        }

        config(['database.default' => $current]);

        return $isValid;
    }

}
