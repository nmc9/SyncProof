<?php

namespace Tests\Proof;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use Tests\Proof\TestCaseN;
use Tests\TestProofCase;

class WorkorderUninteruptedTest  extends TestCase
{

    // use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '../database/migrations');

    }

    protected function getEnvironmentSetUp($app){
        $app['config']->set('database.default', 'remote');
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
    /**
     * Local Update > Push > Done
     *
     * @return void
     */
    public function test_local_updates_record_causes_updates_to_remote_database_uninterupted(){
        echo "AS";
        $this->assertEquals("AS","AS");

        $tables = \DB::select('tables');
        foreach($tables as $table)
        {
          echo $table->Tables_in_db_name;
      }
      die();
      $this->assertDatabaseHas('workorders',[

      ]);
      $x = \DB::connection('remote')->raw("SHOW TABLES;");
  }

}
