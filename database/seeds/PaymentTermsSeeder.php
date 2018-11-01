<?php

use App\Models\PaymentTerm;

class PaymentTermsSeeder extends Seeder
{
    public function run()
    {
        Eloquent::unguard();

        $paymentTerms = [
            ['num_days' => -1, 'name' => 'Net 0'],
        ];

        foreach ($paymentTerms as $paymentTerm) {
            if (! DB::table('core__paymentterms')->where('name', '=', $paymentTerm['name'])->first()) {
                PaymentTerm::create($paymentTerm);
            }
        }
    }
}
