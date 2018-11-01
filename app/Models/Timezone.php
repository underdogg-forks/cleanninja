<?php

namespace App\Models;

use Eloquent;

/**
 * Class Timezone.
 */
class Timezone extends Eloquent
{
	protected $table = 'core__timezones';
    /**
     * @var bool
     */
    public $timestamps = false;
}
