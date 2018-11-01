<?php

namespace App\Models;

use Eloquent;

/**
 * Class Size.
 */
class Size extends Eloquent
{
	protected $table = 'core__sizes';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}
