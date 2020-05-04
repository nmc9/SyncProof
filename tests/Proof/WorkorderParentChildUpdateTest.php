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

class WorkorderParentChildUpdateTest extends TestCase
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
     * 5. Local Parent Inserted > Insert Child > Update Child to Temp Parent > Push
     *
     * @return void
     */
    public function test_inserted_parent_has_child_updated_to_another_inserted_parent(){
        $companyno = $this->remote->companyno;

        //setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Javascript"
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        //insert Local Parent
        $tempLocalWorkorders = LocalCollection::insert(LocalTempWorkorder::class,2,[
            "companyno" => $companyno,
            "UserName" => "zoro",
            "local_WorkOrderNo" => null
        ]);
        //Set the no as the id to siluate autoincrement.
        $tempLocalWorkorders->update(0,["local_WorkOrderNo" => $tempLocalWorkorders->index(0)->local_WorkOrderID]);
        $tempLocalWorkorders->update(1,["local_WorkOrderNo" => $tempLocalWorkorders->index(1)->local_WorkOrderID]);

        //insert Local child
        $tempLocalMeter = Local::insert(LocalTempMeter::class,[
            "local_workorderno" => $tempLocalWorkorders->index(0)->local_WorkOrderNo,
            'workorderno' => null,
            'companyno' => $companyno
        ]);

        //update Child to new Parent
        $tempLocalMeter->update(["local_workorderno" => $tempLocalWorkorders->index(1)->local_WorkOrderNo]);

        //insert parents
        $returnedWO = ApiWrite::insertOnlyBulk(RemoteWorkorder::class,$tempLocalWorkorders,[],["local_WorkOrderNo"]);

        $returnedWorkorderNumber = $returnedWO->records()->where("local_WorkOrderNo",$tempLocalMeter->record()->local_workorderno)->first()->WorkOrderNo;

        $tempLocalMeter->update(["workorderno" => $returnedWorkorderNumber]);

        //Select everything
        $returnedMeter = ApiWrite::insertOnly(RemoteMeter::class,$tempLocalMeter);

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno);
        $meter = Api::select(RemoteMeter::class,$companyno);

        //Insert Local
        $local = new LocalCollection($workorders);
        $local->download(LocalWorkorder::class);

        $localMeter = new Local($meter);
        $localMeter->download(LocalMeter::class);


        //Test
        $record = $tempLocalWorkorders->index(0);
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $returnedWO->index(0)->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $returnedWO->index(0)->WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
            'UserName' => $record->UserName,
        ]);

        $record = $tempLocalMeter->record();
        $this->assertDatabaseHasBoth('meters',[
            "meterid" => $meter->meterid,
            "companyno" => $companyno,
            "workorderno" => $returnedWorkorderNumber,
            "meterno" => $record->meterno,
            "MXU" => $record->MXU,
            "prevread" => $record->prevread,
        ]);
    }

    /**
     * 6. Local Parent Inserted > Insert Child > Update Child to Real Parent > Push
     *
     * @return void
     */
    public function test_inserted_parent_has_child_updated_to_a_real_parent(){
        $companyno = $this->remote->companyno;

        //setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Javascript"
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        //insert Local Parent
        $tempLocalWorkorders = LocalCollection::insert(LocalTempWorkorder::class,2,[
            "companyno" => $companyno,
            "UserName" => "sanji",
            "local_WorkOrderNo" => null
        ]);
        //Set the no as the id to siluate autoincrement.
        $tempLocalWorkorders->update(0,["local_WorkOrderNo" => $tempLocalWorkorders->index(0)->local_WorkOrderID]);
        $tempLocalWorkorders->update(1,["local_WorkOrderNo" => $tempLocalWorkorders->index(1)->local_WorkOrderID]);

        //insert Local child
        $tempLocalMeter = Local::insert(LocalTempMeter::class,[
            "local_workorderno" => $tempLocalWorkorders->index(0)->local_WorkOrderNo,
            'workorderno' => null,
            'companyno' => $companyno
        ]);

        //update Child to new Parent
        $tempLocalMeter->update(["local_workorderno" => null]);
        $tempLocalMeter->update(["workorderno" => $remote->record()->WorkOrderNo]);

        //insert parents
        $returnedWO = ApiWrite::insertOnlyBulk(RemoteWorkorder::class,$tempLocalWorkorders,[],["local_WorkOrderNo"]);


        //Select everything
        $returnedMeter = ApiWrite::insertOnly(RemoteMeter::class,$tempLocalMeter);

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno);
        $meter = Api::select(RemoteMeter::class,$companyno);

        //Insert Local
        $local = new LocalCollection($workorders);
        $local->download(LocalWorkorder::class);

        $localMeter = new Local($meter);
        $localMeter->download(LocalMeter::class);


        //Test
        $record = $tempLocalWorkorders->index(0);
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $returnedWO->index(0)->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $returnedWO->index(0)->WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
            'UserName' => $record->UserName,
        ]);

        $record = $tempLocalMeter->record();
        $this->assertDatabaseHasBoth('meters',[
            "meterid" => $meter->meterid,
            "companyno" => $companyno,
            "workorderno" => $remote->record()->WorkOrderNo,
            "meterno" => $record->meterno,
            "MXU" => $record->MXU,
            "prevread" => $record->prevread,
        ]);
    }

    /**
     * 7. Local Parent Exists > Child Exists > Update Child to Temp Parent > Push
     *
     * @return void
     */
    public function test_existing_child_can_be_updated_to_using_a_temp_parent(){
        $companyno = $this->remote->companyno;

        //setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno
        ]);
        $meter1 = Remote::insert(RemoteMeter::class,[
            "workorderno" => $remote->record()->WorkOrderID,
            "companyno" => $companyno
        ]);
        $localMeter1 = $meter1->cloneTo(LocalMeter::class);
        Remote::insertClone(RemoteMeter::class,LocalMeter::class,[
            "workorderno" => $remote->record()->WorkOrderID,
            "companyno" => $companyno
        ]);

        $this->timePasses();

        //insert Local Parents
        $tempLocalWorkorders = LocalCollection::insert(LocalTempWorkorder::class,2,[
            "companyno" => $companyno,
            "UserName" => "sanji",
            "local_WorkOrderNo" => null
        ]);
        //Set the no as the id to siluate autoincrement.
        $tempLocalWorkorders->update(0,["local_WorkOrderNo" => $tempLocalWorkorders->index(0)->local_WorkOrderID]);
        $tempLocalWorkorders->update(1,["local_WorkOrderNo" => $tempLocalWorkorders->index(1)->local_WorkOrderID]);


        //update Child to new Temp Parent
        //create insert temp record
        $data = $localMeter1->record()->toArray();
        unset($data["workorderno"]);
        $data["local_workorderno"] = $tempLocalWorkorders->index(0)->local_WorkOrderNo;

        $tempLocalMeter = Local::insert(LocalTempMeter::class,$data);

        //delete old record
        $localMeter1->delete();




        //insert parents
        $returnedWO = ApiWrite::insertOnlyBulk(RemoteWorkorder::class,$tempLocalWorkorders,[],["local_WorkOrderNo"]);

        $returnedWorkorderNumber = $returnedWO->records()->where("local_WorkOrderNo",$tempLocalMeter->record()->local_workorderno)->first()->WorkOrderNo;


        $tempLocalMeter->update(["workorderno" => $returnedWorkorderNumber]);

        $http = ApiWrite::delete(RemoteMeter::class,$localMeter1->record()->meterid);
        $localMeter1->forceDelete();


        //Select everything
        $returnedMeter = ApiWrite::insertOnly(RemoteMeter::class,$tempLocalMeter,["meterid" => $tempLocalMeter->record()->meterid],[],["workorderno" => null]);

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno);
        $meter = Api::select(RemoteMeter::class,$companyno);

        //Insert Local
        $local = new LocalCollection($workorders);
        $local->download(LocalWorkorder::class);

        $localMeter = new Local($meter);
        $localMeter->download(LocalMeter::class);

        //Test
        $record = $tempLocalWorkorders->index(0);
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $returnedWO->index(0)->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $returnedWO->index(0)->WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
            'UserName' => $record->UserName,
        ]);

        $record = $tempLocalMeter->record();
        $this->assertDatabaseHasBoth('meters',[
            "meterid" => $meter->meterid,
            "companyno" => $companyno,
            "workorderno" => $returnedWorkorderNumber,
            "meterno" => $record->meterno,
            "MXU" => $record->MXU,
            "prevread" => $record->prevread,
        ]);
    }



}
