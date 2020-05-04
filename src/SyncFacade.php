<?php

namespace Nmc9\SyncProof;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nmc9\SyncProof\Skeleton\SkeletonClass
 */
class SyncFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sync';
    }
}
