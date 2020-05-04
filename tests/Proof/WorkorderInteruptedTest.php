<?php

namespace Tests\Proof;

use Nmc9\SyncProof\Tests\Models\Local\LocalCompany;
use Nmc9\SyncProof\Tests\Models\Local\LocalInventory;
use Nmc9\SyncProof\Tests\Models\Local\LocalTempWorkorder;
use Nmc9\SyncProof\Tests\Models\Local\LocalWorkorder;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteCompany;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteInventory;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteWorkorder;
use Nmc9\SyncProof\Proof\Api;
use Nmc9\SyncProof\Proof\ApiUpdate;
use Nmc9\SyncProof\Proof\ApiWrite;
use Nmc9\SyncProof\Proof\Local;
use Nmc9\SyncProof\Proof\LocalCollection;
use Nmc9\SyncProof\Proof\Remote;
use Nmc9\SyncProof\Proof\RemoteCollection;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Artisan;
use Illuminate\Support\Facades\DB;
use Nmc9\SyncProof\Tests\TestCase;

class WorkorderInteruptedTest extends TestCase
{
    use RefreshDatabase;

    private $remote;
    private $local;

    public function setup(): void{
        parent::setup();
        Carbon::setTestNow();
        $this->remote = factory(RemoteCompany::class)->create();
        $this->local = factory(LocalCompany::class)->create($this->remote->toLocal());
    }

    /**
     * 1. Local Insert > Connection > Push > Done
     *
     * @return void
     */
    public function test_local_insert_causes_a_catch_up_to_remote_database()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $local = Local::insert(LocalTempWorkorder::class,[
            "companyno" => $companyno,
            "local_WorkOrderNo" => null
        ]);
        $local->record()->local_WorkOrderNo = $local->record()->local_WorkOrderID;
        $local->record()->save();

        $this->timePasses();
        //Send insert to remote (ASSUME SUCCESS)
        $write = ApiWrite::insertOnly(RemoteWorkorder::class,$local);
        // dd($write);

        $localPull = new Local($write->record());
        $localPull->download(LocalWorkorder::class);
        //Select Inserted from Remote


        //Test
        $record = $local->record();
        $this->assertDatabaseHas('workorders',[
            'WorkOrderID' => $write->record()->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $record->local_WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
        ],'remote');
        $this->assertCountBoth(2,LocalWorkorder::all(),RemoteWorkorder::all());

        //Show that select doesnt change anything
        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $localPull = new LocalCollection($workorders);
        $localPull->download(LocalWorkorder::class);

        $record = $local->record();
        $this->assertDatabaseHas('workorders',[
            'WorkOrderID' => $write->record()->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $record->local_WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
        ],'remote');
        $this->assertCountBoth(2,LocalWorkorder::all(),RemoteWorkorder::all());

    }

    /**
     * 2. Local Update > Conenction > Push > Done
     *
     * @return void
     */
    public function test_update_on_local_gets_pushed_to_remote()
    {

        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $local = Local::first(LocalWorkorder::class);
        $local->update(["UserName" => "Odie"]);

        $localRecord = $local->record();
        if($localRecord->updated_at >= $lastSync){
            $http = ApiUpdate::update(RemoteWorkorder::class,$localRecord->WorkOrderID,$localRecord->updateFields(),$lastSync);
        }

        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "UserName" => "Odie",
        ]);
    }
    /**
     * 3. Local Delete > Conenction > Push > Done
     *
     * @return void
     */
    public function test_local_deletes_causes_deletes_to_remote_database()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $local = Local::first(LocalWorkorder::class);
        $local->delete();
        $deleted_at = Carbon::now();

        //Send delete to remote (ASSUME SUCCESS)
        if($local->record()->deleted_at !== null){
            $http = ApiWrite::delete(RemoteWorkorder::class,$local->record()->WorkOrderID);
        }

        //delete Local
        $local->forceDelete();

        //Test
        $record = $local->record();
        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
        ],$deleted_at);
        $this->assertCountBoth(0,LocalWorkorder::all(),RemoteWorkorder::all());

    }

    /**
     * 3a. Local Insert > Local Delete > Conenction > Push > Done
     *
     * @return void
     */
    public function test_local_insert_and_deletes_causes_non_existant_deletes_to_remote_database()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $local = Local::insert(LocalWorkorder::class);
        $this->timePasses();

        $local->delete();
        $deleted_at = Carbon::now();

        //Send delete to remote (ASSUME SUCCESS)
        if($local->record()->deleted_at !== null){
            $http = ApiWrite::delete(RemoteWorkorder::class,$local->record()->WorkOrderID);
        }
        //delete Local
        $local->forceDelete();

        //Test (These Are right)
        $record = $local->record();
        $this->assertDatabaseMissing('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
        ],'remote');

        $this->assertDatabaseMissing('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
        ],'local');
        $this->assertCountBoth(1,LocalWorkorder::all(),RemoteWorkorder::all());

    }

    // 4-6a already done

    /**
     * 8. Remote Update > Local Delete > Conenction > Push > Pull > Done
     *
     * @return void
     */
    public function test_local_delete_and_remote_update_causes_remote_to_be_marked_deleted_uninterupted()
    {

        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $local = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->update(["UserName" => "John Hammond"]);

        $this->timePasses();

        $local->delete();

        $this->timePasses();

        if($local->record()->deleted_at !== null){
            $http = ApiWrite::delete(RemoteWorkorder::class,$local->record()->WorkOrderID);
        }
        $local->forceDelete();
        $deleted_at = Carbon::now();

        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
        ],$deleted_at);
    }

    /**
     * 9. Remote Delete > Local Update > Conenction > Push > Pull > Done
     *
     * @return void
     */
    public function test_local_update_and_remote_delete_causes_local_to_be_marked_deleted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $local = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->delete();
        $deleted_at = Carbon::now();

        $this->timePasses();

        $local->update(["UserName" => "John Hammond"]);

        $http = ApiUpdate::update(RemoteWorkorder::class,$local->record()->WorkOrderID,$local->record()->updateFields(),$lastSync);
        if($http === 404){
            $local->forceDelete();
        }

        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
        ],$deleted_at);

    }

    /**
     * 10. Remote Delete > Local Delete > Conenction > Push > Pull > Done
     *
     * @return void
     */
    public function test_local_delete_and_remote_delete_causes_normal_deletes_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $local = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->delete();
        $deleted_at = Carbon::now();

        $this->timePasses();

        $local->delete();

        if($local->record()->deleted_at !== null){
            $http = ApiWrite::delete(RemoteWorkorder::class,$local->record()->WorkOrderID);
        }
        $this->assertSame($http,404);

        $local->forceDelete();

        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
        ],$deleted_at);
    }



}
