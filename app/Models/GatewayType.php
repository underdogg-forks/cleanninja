<?php

namespace App\Models;

use Cache;
use Eloquent;
use Utils;

/**
 * Class GatewayType.
 */
class GatewayType extends Eloquent
{
	protected $table = 'core__gatewaytypes';

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

    public static function getAliasFromId($id)
    {
        return Utils::getFromCache($id, 'gatewayTypes')->alias;
    }

    public static function getIdFromAlias($alias)
    {
        return Cache::get('gatewayTypes')->where('alias', $alias)->first()->id;
    }
}
