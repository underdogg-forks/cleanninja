<?php

namespace App\Models;

use Eloquent;

/**
 * Class Language.
 */
class Language extends Eloquent
{
	protected $table = 'core__languages';

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
