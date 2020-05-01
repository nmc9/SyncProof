<?php

namespace Nmc9\SyncProof\Tests;

use Orchestra\Testbench\TestCase;
use Nmc9\SyncProof\SyncProofServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [SyncProofServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
