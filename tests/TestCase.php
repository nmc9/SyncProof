<?php

namespace Nmc9\SyncProof\Tests;


use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{


    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->withFactories(__DIR__ . '/../database/factories');
    }

    protected function getEnvironmentSetUp($app){
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('database.connections.remote', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => 'remote',
        ]);
        $app['config']->set('database.connections.local', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => 'local',
        ]);
    }


    protected function selectAll($companyno,$RemoteModel,$lastSync = null){
        $query = (new $RemoteModel)->withTrashed()->where("companyno",$companyno);
        if($lastSync != null){
            $query->where('updated_at',">",$lastSync);
        }
        return $query->get();
    }

    protected function timePasses(){
        Carbon::setTestNow(Carbon::now()->addDays(1));
    }

    protected function assertDatabaseHasBoth($table,$compare){
        $compare1 = array_merge($compare,["deleted_at" => null]);
        $this->assertDatabaseHas($table,$compare1,'remote');

        unset($compare["deleted_at"]);
        $this->assertDatabaseHas($table,$compare,'local');
    }

    protected function assertDatabaseDeleteBoth($table,$compare,$deleted_at){
        $compare1 = array_merge($compare,["deleted_at" => $deleted_at]);
        $this->assertDatabaseHas($table,$compare1,'remote');

        unset($compare["deleted_at"]);
        $this->assertDatabaseMissing($table,$compare,'local');
    }

    public function assertCountBoth($count,$remote,$local){
        $this->assertCount($count,$remote);
        $this->assertCount($count,$local);
    }

}
