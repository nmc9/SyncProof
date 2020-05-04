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

class WorkorderCollectionInteruptedTest extends TestCase
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
     * 1. Remote Operations > Local Insert > Connection > Push >>  Pull > Done
     *
     * @return void
     */
    public function test_remote_operations_and_local_inserts_causes_database_in_sync()
    {

        $companyno = $this->remote->companyno;

        //Setup World
        $remoteCollection = RemoteCollection::insertClone(RemoteWorkorder::class,LocalWorkorder::class,7,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $this->remoteOperations($remoteCollection,$companyno);

        $this->timePasses();

        $inserted = Local::insert(LocalTempWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Local Inserted"
        ]);
        $inserted2 = Local::insert(LocalTempWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Local Inserted 2"
        ]);

        $this->timePasses();

        $writeCollection = ApiWrite::insertOnlyBulkDistinct(RemoteWorkorder::class,LocalTempWorkorder::all()->map(
            function($item){ return new Local($item); }
        )->toArray());

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote();

        $this->assertDatabaseHasBoth('workorders',[
            "WorkOrderID" => $writeCollection->index(0)->WorkOrderID,
            "UserName" => $inserted->record()->UserName
        ]);
        $this->assertDatabaseHasBoth('workorders',[
            "WorkOrderID" => $writeCollection->index(1)->WorkOrderID,
            "UserName" => $inserted2->record()->UserName
        ]);
    }

    /**
     * 2. Remote Operations > Local Updates > Connection > Push >>  Pull > Done
     *
     * @return void
     */
    public function test_remote_operations_and_local_update_causes_database_in_sync_uninterupted()
    {

        $companyno = $this->remote->companyno;

        //Setup World
        $remoteCollection = RemoteCollection::insertClone(RemoteWorkorder::class,LocalWorkorder::class,7,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $this->remoteOperations($remoteCollection,$companyno);

        $this->timePasses();

        $updateLocal1 = Local::find(LocalWorkorder::class,$this->nothing1->record()->WorkOrderID);
        $updateLocal1->update(["UserName" => "Frown"]);
        $updateLocal2 = Local::find(LocalWorkorder::class,$this->update1->record()->WorkOrderID);
        $updateLocal2->update(["UserName" => "Smile"]);

        $records = [
            [
                "id" => $updateLocal1->record()->WorkOrderID,
                "update" => $updateLocal1->record()->updateFields()
            ],
            [
                "id" => $updateLocal2->record()->WorkOrderID,
                "update" => $updateLocal2->record()->updateFields()
            ]
        ];
        $this->timePasses();


        $updated = ApiUpdate::updateLocalTruthBulk(RemoteWorkorder::class,$records,$lastSync);

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote([0,1]);

        $this->assertDatabaseHasBoth('workorders',[
            "WorkOrderID" => $updateLocal1->record()->WorkOrderID,
            "UserName" => "Frown"
        ]);

        $this->assertDatabaseHasBoth('workorders',[
            "WorkOrderID" => $updateLocal2->record()->WorkOrderID,
            "UserName" => "Smile"
        ]);
    }

    /**
     * Remote Operations > Local Deletes > Connection > Push > Pull > Done
     *
     * @return void
     */
    public function test_remote_operations_and_local_delete_causes_database_in_sync_uninterupted()
    {

        $companyno = $this->remote->companyno;

        //Setup World
        $remoteCollection = RemoteCollection::insertClone(RemoteWorkorder::class,LocalWorkorder::class,7,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $this->remoteOperations($remoteCollection,$companyno);
        $deleted_at = Carbon::now();


        $this->timePasses();

        $deleteLocal1 = Local::find(LocalWorkorder::class,$this->nothing1->record()->WorkOrderID);
        $deleteLocal1->delete();
        $deleteLocal2 = Local::find(LocalWorkorder::class,$this->delete1->record()->WorkOrderID);
        $deleteLocal2->delete();


        $this->timePasses();

        ApiWrite::deleteBulk(RemoteWorkorder::class,[$deleteLocal1->record()->WorkOrderID,$deleteLocal2->record()->WorkOrderID]);
        $deleted_at_2 = Carbon::now();

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);

        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote([0,2]);

        $this->assertDatabaseDeleteBoth('workorders',[
            "WorkOrderID" => $deleteLocal1->record()->WorkOrderID,
        ],$deleted_at_2);

        $this->assertDatabaseDeleteBoth('workorders',[
            "WorkOrderID" => $deleteLocal2->record()->WorkOrderID,
        ],$deleted_at);
    }

    /**
     * 4. Remote Operations > Local Nothing > Connection > Push >>  Pull > Done
     *
     * @return void
     */
    public function test_remote_operations_and_local_nothing_causes_database_in_sync()
    {

        $companyno = $this->remote->companyno;

        //Setup World
        $remoteCollection = RemoteCollection::insertClone(RemoteWorkorder::class,LocalWorkorder::class,7,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $this->remoteOperations($remoteCollection,$companyno);

        $this->timePasses();

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote();

    }
    /**
     * 5. Remote Operations > Local Insert > Local Delete > Connection > Push >  Pull > Done
     *
     * @return void
     */
    public function test_remote_operations_and_local_insert_deletes_causes_database_in_sync()
    {

        $companyno = $this->remote->companyno;

        //Setup World
        $remoteCollection = RemoteCollection::insertClone(RemoteWorkorder::class,LocalWorkorder::class,7,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $this->remoteOperations($remoteCollection,$companyno);

        $this->timePasses();

        $inserted = Local::insert(LocalTempWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Local Inserted"
        ]);
        $inserted2 = Local::insert(LocalTempWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Local Inserted 2"
        ]);

        $this->timePasses();

        $inserted->delete();
        $inserted2->delete();

        $writeCollection = ApiWrite::insertOnlyBulkDistinct(RemoteWorkorder::class,LocalTempWorkorder::all()->map(
            function($item){ return new Local($item); }
        )->toArray());

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote();
    }

    /**
     * 5. Remote Operations > Local Combo > Connection > Push >  Pull > Done
     *
     * @return void
     */
    public function test_remote_operations_and_multiple_local_operations_causes_database_in_sync()
    {

        $companyno = $this->remote->companyno;

        //Setup World
        $remoteCollection = RemoteCollection::insertClone(RemoteWorkorder::class,LocalWorkorder::class,7,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $this->remoteOperations($remoteCollection,$companyno);
        $delete_at_remote_time = Carbon::now();

        $updateDeleteLocal = Local::find(LocalWorkorder::class,$this->nothing1->record()->WorkOrderID);
        $updateDeleteLocal->update(["UserName" => "Frown"]);


        $this->timePasses();

        $insertedDelete = Local::insert(LocalTempWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Local Inserted Deleted"
        ]);


        $deleteLocal2 = Local::find(LocalWorkorder::class,$this->delete1->record()->WorkOrderID);
        $deleteLocal2->delete();

        $this->timePasses();


        $this->timePasses();

        $updateLocal1 = Local::find(LocalWorkorder::class,$this->update1->record()->WorkOrderID);
        $updateLocal1->update(["UserName" => "Smile"]);

        $this->timePasses();

        $updateDeleteLocal->delete();


        $inserted = Local::insert(LocalTempWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Local Inserted"
        ]);


        $this->timePasses();

        $insertedDelete->delete();

        $this->timePasses();

        $writeCollection = ApiWrite::insertOnlyBulkDistinct(RemoteWorkorder::class,LocalTempWorkorder::all()->map(
            function($item){ return new Local($item); }
        )->toArray());

        $records = [[
            "id" => $updateDeleteLocal->record()->WorkOrderID,
            "update" => $updateDeleteLocal->record()->updateFields()
        ],[
            "id" => $updateLocal1->record()->WorkOrderID,
            "update" => $updateLocal1->record()->updateFields()
        ]];
        $updated = ApiUpdate::updateLocalTruthBulk(RemoteWorkorder::class,$records,$lastSync);

        ApiWrite::deleteBulk(RemoteWorkorder::class,[$updateDeleteLocal->record()->WorkOrderID,$deleteLocal2->record()->WorkOrderID]);
        $deleted_at = Carbon::now();

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote([0,1,2]);

        $this->assertDatabaseDeleteBoth('workorders',[
            "WorkOrderID" => $updateDeleteLocal->record()->WorkOrderID,
        ],$deleted_at);

        $this->assertDatabaseHasBoth('workorders',[
            "WorkOrderID" => $updateLocal1->record()->WorkOrderID,
            "UserName" => "Smile"
        ]);

        $this->assertDatabaseDeleteBoth('workorders',[
            "WorkOrderID" => $deleteLocal2->record()->WorkOrderID,
        ],$delete_at_remote_time);

        $this->assertDatabaseHasBoth('workorders',[
            "WorkOrderID" => $writeCollection->index(0)->WorkOrderID,
            "UserName" => $inserted->record()->UserName
        ]);

    }

    private function remoteOperations($remoteCollection,$companyno){
        $this->nothing1 = $remoteCollection->indexObject(0);
        $this->nothing2 = $remoteCollection->indexObject(6);

        $this->update1 = $remoteCollection->indexObject(1);
        $this->update2 = $remoteCollection->indexObject(3);

        $this->delete1 = $remoteCollection->indexObject(2);
        $this->delete2 = $remoteCollection->indexObject(4);

        $this->updateDelete = $remoteCollection->indexObject(5);

        $this->insert1 = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "INSERT1"
        ]);
        $this->insertDelete = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "DELETED"
        ]);


        $this->update1->update(["UserName" => "Odie"]);
        $this->update2->update(["UserName" => "John"]);
        $this->updateDelete->update(["UserName" => "Garfield"]);

        $this->delete1->delete();
        $this->delete2->delete();
        $this->updateDelete->delete();
        $this->insertDelete->delete();

        $this->deleted_at = Carbon::now();
    }

    private function assertRemote(array $skip = [], $skipInsertDelete = false,$skipInsert = false){
        if(!in_array(0,$skip)){
            $this->assertDatabaseHasBoth('workorders',[
                "WorkOrderID" => $this->nothing1->record()->WorkOrderID,
                "UserName" => $this->nothing1->record()->UserName
            ]);
        }

        if(!in_array(6,$skip)){
            $this->assertDatabaseHasBoth('workorders',[
                "WorkOrderID" => $this->nothing2->record()->WorkOrderID,
                "UserName" => $this->nothing2->record()->UserName
            ]);
        }

        if(!in_array(1,$skip)){
            $this->assertDatabaseHasBoth('workorders',[
                "WorkOrderID" => $this->update1->record()->WorkOrderID,
                "UserName" => "Odie"
            ]);
        }

        if(!in_array(3,$skip)){
            $this->assertDatabaseHasBoth('workorders',[
                "WorkOrderID" => $this->update2->record()->WorkOrderID,
                "UserName" => "John"
            ]);
        }

        if(!in_array(2,$skip)){
            $this->assertDatabaseDeleteBoth('workorders',[
                "WorkOrderID" => $this->delete1->record()->WorkOrderID,
            ],$this->deleted_at);
        }


        if(!in_array(4,$skip)){
            $this->assertDatabaseDeleteBoth('workorders',[
                "WorkOrderID" => $this->delete2->record()->WorkOrderID,
            ],$this->deleted_at);
        }

        if(!in_array(5,$skip)){
            $this->assertDatabaseDeleteBoth('workorders',[
                "WorkOrderID" => $this->updateDelete->record()->WorkOrderID,
            ],$this->deleted_at);
        }

        if(!$skipInsertDelete){
            $this->assertDatabaseDeleteBoth('workorders',[
                "WorkOrderID" => $this->insertDelete->record()->WorkOrderID,
            ],$this->deleted_at);
        }

        if(!$skipInsert){
            $this->assertDatabaseHasBoth('workorders',[
                "WorkOrderID" => $this->insert1->record()->WorkOrderID,
                "UserName" => $this->insert1->record()->UserName
            ]);
        }
    }
}
