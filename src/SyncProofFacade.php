<?php

namespace Nmc9\SyncProof;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nmc9\SyncProof\Skeleton\SkeletonClass
 */
class SyncProofFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'syncproof';
    }
}
