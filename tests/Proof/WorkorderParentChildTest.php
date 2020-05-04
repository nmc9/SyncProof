<?php

namespace Tests\Proof;

use Nmc9\SyncProof\Tests\Models\Local\LocalCompany;
use Nmc9\SyncProof\Tests\Models\Local\LocalInventory;
use Nmc9\SyncProof\Tests\Models\Local\LocalMeter;
use Nmc9\SyncProof\Tests\Models\Local\LocalTempMeter;
use Nmc9\SyncProof\Tests\Models\Local\LocalTempWorkorder;
use Nmc9\SyncProof\Tests\Models\Local\LocalWorkorder;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteCompany;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteInventory;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteMeter;
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

class WorkorderParentChildTest extends TestCase
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
     * A basic feature test example.
     *
     * @return void
     */
    public function test_download_workorder_group_from_remote_to_local()
    {

        $companyno = $this->remote->companyno;

        //setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $meter1 = Remote::insert(RemoteMeter::class,[
            "workorderno" => $remote->record()->WorkOrderID,
            "companyno" => $companyno
        ]);
        Remote::insert(RemoteMeter::class,[
            "workorderno" => $remote->record()->WorkOrderID,
            "companyno" => $companyno
        ]);

        //Select Workorders Join
        $workorder = Api::select(RemoteWorkorder::class,$companyno);
        $meters = Api::selectAll(RemoteMeter::class,$companyno);

        //Insert Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        $localMeters = new LocalCollection($meters);
        $localMeters->download(LocalMeter::class);


        //Test
        $record = $remote->record();
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $record->WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
            'UserName' => $record->UserName,
        ]);

        $this->assertDatabaseHasBoth('meters',[
            "meterid" => $meter1->record()->meterid,
            "companyno" => $companyno,
            "workorderno" => $meter1->record()->workorderno,
            "meterno" => $meter1->record()->meterno,
            "MXU" => $meter1->record()->MXU,
            "prevread" => $meter1->record()->prevread,
        ]);
        $this->assertCountBoth(1,RemoteWorkorder::all(),LocalWorkorder::all());
        $this->assertCountBoth(2,RemoteMeter::all(),LocalMeter::all());
    }

    /**
     * 1. Local Parent Exists > Insert Child > Push
     *
     * @return void
     */
    public function test_existing_parent_gets_inserted_child_causes_inserts_pushed_to_remote_causes_child_insert()
    {
        $companyno = $this->remote->companyno;

        //setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Javascript"
        ]);
        $remote->cloneTo(LocalWorkorder::class);
        $meter1 = Remote::insertClone(RemoteMeter::class,LocalMeter::class,[
            "workorderno" => $remote->record()->WorkOrderID,
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        //update Local
        $local = Local::first(LocalWorkorder::class);

        $tempMeter = Local::insert(LocalTempMeter::class,[
            "workorderno" => $local->record()->WorkOrderNo,
            'companyno' => $companyno
        ]);

        //Send to remote (Assume Success)
        $localMeter = ApiWrite::insertOnly(RemoteMeter::class,$tempMeter);

        //Select Updated from Remote
        $workorder = Api::select(RemoteWorkorder::class,$companyno,$lastSync);
        $meters = Api::selectAll(RemoteMeter::class,$companyno,$lastSync);

        //Update Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        $localMeterPull = new LocalCollection($meters);
        $localMeterPull->download(LocalMeter::class);

        //Test
        $record = $remote->record();
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $record->WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
        ]);

        $this->assertCountBoth(1,RemoteWorkorder::all(),LocalWorkorder::all());

        $record = $tempMeter->record();
        $this->assertDatabaseHasBoth('meters',[
            "meterid" => $localMeter->record()->meterid,
            "meterno" => $record->meterno,
            "MXU" => $record->MXU,
        ]);
        $this->assertCountBoth(2,RemoteMeter::all(),LocalMeter::all());
    }

    /**
     * 2. Local Parent Inserted > Insert Child > Push
     *
     * @return void
     */
    public function test_inserted_local_parent_has_inserted_local_children_causes_parent_and_child_inserts()
    {
        $companyno = $this->remote->companyno;

        //setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Javascript"
        ]);
        $meter1 = Remote::insertClone(RemoteMeter::class,LocalMeter::class,[
            "workorderno" => $remote->record()->WorkOrderID,
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        //insert Local
        $tempLocalWorkorder = Local::insert(LocalTempWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "franky",
            "local_WorkOrderNo" => null
        ]);
        //Set the no as the id to siluate autoincrement.
        $tempLocalWorkorder->update(["local_WorkOrderNo" => $tempLocalWorkorder->record()->local_WorkOrderID]);

        $tempLocalMeters = LocalCollection::insert(LocalTempMeter::class,2,[
            "local_workorderno" => $tempLocalWorkorder->record()->local_WorkOrderNo,
            'workorderno' => null,
            'companyno' => $companyno
        ]);

        //Send to remote (Assume Success)
        $returnedWorkorder = ApiWrite::insertOnly(RemoteWorkorder::class,$tempLocalWorkorder);

        $tempLocalMeters->updateAll(["workorderno" => $returnedWorkorder->record()->WorkOrderNo]);
        $returnedMeters = ApiWrite::insertOnlyBulk(RemoteMeter::class,$tempLocalMeters);

        //Select Inserted from Remote
        $workorder = Api::select(RemoteWorkorder::class,$companyno,$lastSync);
        $meters = Api::selectAll(RemoteMeter::class,$companyno,$lastSync);

        //Update Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        $localMeterPull = new LocalCollection($meters);
        $localMeterPull->download(LocalMeter::class);

        //Test
        $record = $tempLocalWorkorder->record();
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $returnedWorkorder->record()->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $record->local_WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
        ]);

        $this->assertCountBoth(2,RemoteWorkorder::all(),LocalWorkorder::all());

        $record = $tempLocalMeters->firstRecord();
        $this->assertDatabaseHasBoth('meters',[
            "meterid" => $returnedMeters->index(0)->meterid,
            "meterno" => $record->meterno,
            "MXU" => $record->MXU,
        ]);
        $record = $tempLocalMeters->index(1);
        $this->assertDatabaseHasBoth('meters',[
            "meterid" => $returnedMeters->index(1)->meterid,
            "meterno" => $record->meterno,
            "MXU" => $record->MXU,
        ]);
        $this->assertCountBoth(3,RemoteMeter::all(),LocalMeter::all());
    }

    /**
     * 3. Local Parent Exists > Insert Child > Delete Child > Push
     *
     * @return void
     */
    public function test_existing_local_parent_has_inserted_local_children_that_are_deleted_causes_nothing_uploaded()
    {
        $companyno = $this->remote->companyno;

        //setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Javascript"
        ]);
        $meter1 = Remote::insertClone(RemoteMeter::class,LocalMeter::class,[
            "workorderno" => $remote->record()->WorkOrderID,
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        //insert Local

        $tempLocalMeters = LocalCollection::insert(LocalTempMeter::class,2,[
            "local_workorderno" => $remote->record()->WorkOrderNo,
            'companyno' => $companyno
        ]);

        $tempLocalMeters->delete(0);
        $tempLocalMeters->delete(1);
        $tempLocalMeters->deleteAll();


        //Send to remote (Assume Success)
        $returnedMeters = ApiWrite::insertOnlyBulk(RemoteMeter::class,$tempLocalMeters);

        //Select Inserted from Remote
        $workorder = Api::select(RemoteWorkorder::class,$companyno,$lastSync);
        $meters = Api::selectAll(RemoteMeter::class,$companyno,$lastSync);

        //Update Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        $localMeterPull = new LocalCollection($meters);
        $localMeterPull->download(LocalMeter::class);

        //Test
        $record = $remote->record();
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $record->WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
        ]);

        $this->assertCountBoth(1,RemoteWorkorder::all(),LocalWorkorder::all());

        $this->assertCount(0,LocalTempMeter::all());
        $this->assertCountBoth(1,RemoteMeter::all(),LocalMeter::all());
    }
    /**
     * 3a. Local Parent Inserted > Insert Child > Delete Child > Push
     *
     * @return void
     */
    public function test_inserted_local_parent_has_inserted_local_children_that_are_deleted_causes_nothing_uploaded()
    {
        $companyno = $this->remote->companyno;

        //setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Javascript"
        ]);
        $meter1 = Remote::insertClone(RemoteMeter::class,LocalMeter::class,[
            "workorderno" => $remote->record()->WorkOrderID,
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        //insert Local
        $tempLocalWorkorder = Local::insert(LocalTempWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "franky",
            "local_WorkOrderNo" => null
        ]);
        //Set the no as the id to siluate autoincrement.
        $tempLocalWorkorder->update(["local_WorkOrderNo" => $tempLocalWorkorder->record()->local_WorkOrderID]);

        $tempLocalMeters = LocalCollection::insert(LocalTempMeter::class,2,[
            "local_workorderno" => $tempLocalWorkorder->record()->local_WorkOrderNo,
            'companyno' => $companyno
        ]);

        $tempLocalMeters->delete(0);
        $tempLocalMeters->delete(1);
        $tempLocalMeters->deleteAll();


        //Send to remote (Assume Success)
        $returnedWorkorder = ApiWrite::insertOnly(RemoteWorkorder::class,$tempLocalWorkorder);

        $tempLocalMeters->updateAll(["workorderno" => $returnedWorkorder->record()->WorkOrderNo]);
        $returnedMeters = ApiWrite::insertOnlyBulk(RemoteMeter::class,$tempLocalMeters);

        //Select Inserted from Remote
        $workorder = Api::select(RemoteWorkorder::class,$companyno,$lastSync);
        $meters = Api::selectAll(RemoteMeter::class,$companyno,$lastSync);

        //Update Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        $localMeterPull = new LocalCollection($meters);
        $localMeterPull->download(LocalMeter::class);

        //Test
        $record = $tempLocalWorkorder->record();
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $returnedWorkorder->record()->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $record->local_WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
        ]);

        $this->assertCountBoth(2,RemoteWorkorder::all(),LocalWorkorder::all());

        $this->assertCount(0,LocalTempMeter::all());
        $this->assertCountBoth(1,RemoteMeter::all(),LocalMeter::all());
    }

    /**
     * 4. Local Parent Exists > Insert Child > Delete Parent (Cascade) > Push
     *
     * @return void
     */
    public function test_existing_local_parent_with_inserted_children_that_gets_deleted_can_cascade_delete_and_upload()
    {
        $companyno = $this->remote->companyno;

        //setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Javascript"
        ]);
        $LocalWorkorder = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        //insert Local child
        $tempLocalMeters = LocalCollection::insert(LocalTempMeter::class,2,[
            "local_workorderno" => $LocalWorkorder->record()->local_WorkOrderNo,
            'companyno' => $companyno
        ]);

        $LocalWorkorder->delete();
        //Casacde
        $tempLocalMeters->deleteAll();

        $this->timePasses();

        //Send to remote (Assume Success)
        // $returnedWorkorder = ApiWrite::insertOnly(RemoteWorkorder::class,$tempLocalWorkorder);
        $returned = ApiWrite::delete(RemoteWorkorder::class,$LocalWorkorder->record()->WorkOrderID);
        $LocalWorkorder->forceDelete();
        $deleted_at = Carbon::now();

        $returnedMeters = ApiWrite::insertOnlyBulk(RemoteMeter::class,$tempLocalMeters);

        //Select Inserted from Remote
        $workorder = Api::select(RemoteWorkorder::class,$companyno,$lastSync);
        $meters = Api::selectAll(RemoteMeter::class,$companyno,$lastSync);

        //Update Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        $localMeterPull = new LocalCollection($meters);
        $localMeterPull->download(LocalMeter::class);

        //Test
        $record = $LocalWorkorder->record();
        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
        ],$deleted_at);

        $this->assertCountBoth(0,RemoteWorkorder::all(),LocalWorkorder::all());

        $this->assertCount(0,LocalTempMeter::all());

        $this->assertCountBoth(0,RemoteMeter::all(),LocalMeter::all());
    }

    /**
     * 4a. Local Parent Inserted > Insert Child > Delete Parent (Cascade) > Push
     *
     * @return void
     */
    public function test_inserted_local_parent_with_inserted_children_that_gets_deleted_can_cascade_delete_and_upload()
    {
        $companyno = $this->remote->companyno;

        //setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Javascript"
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();


        //insert Local Parent
        $tempLocalWorkorder = Local::insert(LocalTempWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "robin",
            "local_WorkOrderNo" => null
        ]);
        //Set the no as the id to siluate autoincrement.
        $tempLocalWorkorder->update(["local_WorkOrderNo" => $tempLocalWorkorder->record()->local_WorkOrderID]);

        //insert Local child
        $tempLocalMeters = LocalCollection::insert(LocalTempMeter::class,2,[
            "local_workorderno" => $tempLocalWorkorder->record()->local_WorkOrderNo,
            'workorderno' => null,
            'companyno' => $companyno
        ]);

        $tempLocalWorkorder->delete();
        //Casacde
        $tempLocalMeters->deleteAll();

        $this->timePasses();

        //Send to remote (Assume Success) [This probably shouldnt even run]
        $returned = ApiWrite::delete(RemoteWorkorder::class,$tempLocalWorkorder->record()->WorkOrderID);
        $this->assertSame($returned,404);

        $tempLocalWorkorder->forceDelete();
        $deleted_at = Carbon::now();

        $returnedWorkorders = ApiWrite::insertOnlyBulk(RemoteWorkorder::class,new LocalCollection(LocalTempWorkorder::all()));
        $returnedMeters = ApiWrite::insertOnlyBulk(RemoteMeter::class,$tempLocalMeters);

        //Select Inserted from Remote
        $workorder = Api::select(RemoteWorkorder::class,$companyno,$lastSync);
        $meters = Api::selectAll(RemoteMeter::class,$companyno,$lastSync);

        //Update Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        $localMeterPull = new LocalCollection($meters);
        $localMeterPull->download(LocalMeter::class);

        //Test
        $record = $tempLocalWorkorder->record();

        $this->assertCountBoth(1,RemoteWorkorder::all(),LocalWorkorder::all());

        $this->assertCount(0,LocalTempMeter::all());

        $this->assertCountBoth(0,RemoteMeter::all(),LocalMeter::all());
    }






    /**
     * 8. Local Parent Delete > Local Orphans > Push
     *
     * @return void
     */
    public function test_local_deletes_with_orphans_causes_deletes_to_parents()
    {
        $companyno = $this->remote->companyno;

        //setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno
        ]);
        $meter1 = Remote::insertClone(RemoteMeter::class,LocalMeter::class,[
            "workorderno" => $remote->record()->WorkOrderID,
            "companyno" => $companyno
        ]);

        $meter2 = Remote::insertClone(RemoteMeter::class,LocalMeter::class,[
            "workorderno" => $remote->record()->WorkOrderID,
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $local = Local::find(LocalWorkorder::class,$remote->record()->WorkOrderID);


        //Send delete to remote (ASSUME SUCCESS)
        ApiWrite::delete(RemoteWorkorder::class,$local->record()->WorkOrderID);

        //delete Local
        $local->forceDelete();
        $deleted_at = Carbon::now();

        //Select Deleted from Remote
        $workorder = Api::select(RemoteWorkorder::class,$companyno,$lastSync);
        $meters = Api::selectAll(RemoteMeter::class,$companyno,$lastSync);

        //Remote Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        $localMeterPull = new LocalCollection($meters);
        $localMeterPull->download(LocalMeter::class);

        //Test
        $record = $remote->record();
        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
        ],$deleted_at);
        $this->assertCountBoth(0,RemoteWorkorder::all(),LocalWorkorder::all());

        $record = $meter1->record();
        $this->assertDatabaseHasBoth('meters',[
            "meterid" => $record->meterid,
            "meterno" => $record->meterno,
            "MXU" => $record->MXU,
        ]);
        $this->assertCountBoth(2,RemoteMeter::all(),LocalMeter::all());

    }

    /**
     * Local Parent Delete > Local Child Delete > Push
     *
     * @return void
     */
    public function test_local_deletes_cascade_causes_deletes_to_remote_database_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $remote->cloneTo(LocalWorkorder::class);
        $meter1 = Remote::insert(RemoteMeter::class,[
            "workorderno" => $remote->record()->WorkOrderID,
            "companyno" => $companyno
        ]);
        $meter1->cloneTo(LocalMeter::class);

        $meter2 = Remote::insert(RemoteMeter::class,[
            "workorderno" => $remote->record()->WorkOrderID,
            "companyno" => $companyno
        ]);
        $meter2->cloneTo(LocalMeter::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $local = Local::first(LocalWorkorder::class);
        $localMeter = Local::first(LocalMeter::class);


        //Send delete to remote (ASSUME SUCCESS)
        ApiWrite::delete(RemoteWorkorder::class,$local->record()->WorkOrderID);
        ApiWrite::delete(RemoteMeter::class,$localMeter->record()->meterid);

        //delete Local
        $local->forceDelete();
        $localMeter->forceDelete();
        $deleted_at = Carbon::now();

        //Select Deleted from Remote
        $workorder = Api::select(RemoteWorkorder::class,$companyno,$lastSync);
        $meters = Api::selectAll(RemoteMeter::class,$companyno,$lastSync);

        //Remote Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        $localMeterPull = new LocalCollection($meters);
        $localMeterPull->download(LocalMeter::class);

        //Test
        $record = $remote->record();
        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
        ],$deleted_at);
        $this->assertCountBoth(0,RemoteWorkorder::all(),LocalWorkorder::all());

        $record = $meter1->record();
        $this->assertDatabaseDeleteBoth('meters',[
            "meterid" => $record->meterid,
        ],$deleted_at);
        $this->assertCountBoth(1,RemoteMeter::all(),LocalMeter::all());

    }


}
