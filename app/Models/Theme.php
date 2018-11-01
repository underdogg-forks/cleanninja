<?php

namespace App\Models;

use Eloquent;

/**
 * Class Theme.
 */
class Theme extends Eloquent
{
	protected $table = 'core__themes';

    /**
     * @var bool
     */
    public $timestamps = false;
}
