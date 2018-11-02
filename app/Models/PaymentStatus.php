<?php

namespace App\Models;

use Eloquent;

/**
 * Class PaymentStatus.
 */
class PaymentStatus extends Eloquent
{

    protected $table = 'payments__statuses';

    /**
     * @var bool
     */
    public $timestamps = false;
}
