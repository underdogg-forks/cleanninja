<?php

namespace App\Models;

use Eloquent;

/**
 * Class ExpenseCategory.
 */
class LookupPlan extends LookupModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'db_server_id',
        'plan_id',
    ];

    public function dbServer()
    {
        return $this->belongsTo('App\Models\DbServer');
    }

    public function getDbServer()
    {
        return $this->dbServer->name;
    }

}
