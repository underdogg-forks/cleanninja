<?php

namespace App\Ninja\Repositories;

use App\Models\Account;

class NinjaRepository
{
    public function updatePlanDetails($clientPublicId, $data)
    {
        $account = Account::whereId($clientPublicId)->first();

        if (! $account) {
            return;
        }

        $plan = $account->plan;
        $plan->fill($data);
        $plan->plan_expires = $plan->plan_expires ?: null;
        $plan->save();
    }
}
