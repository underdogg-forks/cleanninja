<?php

namespace App\Models;

use Eloquent;


class TicketCategory extends Eloquent
{
    protected $table = 'tickets__categories';

    /**
     * @return mixed
     */
    public function getEntityType()
    {
        return ENTITY_TICKET_CATEGORY;
    }
}
