<?php

namespace Tests\Proof;

use Nmc9\SyncProof\Tests\Models\Local\LocalCompany;
use Nmc9\SyncProof\Tests\Models\Local\LocalInventory;
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

class WorkorderCollectionUninteruptedTest extends TestCase
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
     * Remote Operations > Local Nothing >  Pull > Done
     *
     * @return void
     */
    public function test_remote_operations_and_nothing_local_causes_database_in_sync_uninterupted()
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

        $inserted = ApiWrite::insert(RemoteWorkorder::class);

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);

        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote();
    }

    /**
     * Remote Operations > Local Insert >  Pull > Done
     *
     * @return void
     */
    public function test_remote_operations_and_local_insert_causes_database_in_sync_uninterupted()
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

        $inserted = ApiWrite::insert(RemoteWorkorder::class,[
            "companyno" => $companyno,
            "UserName" => "Local Inserted"
        ]);

        // Local::insert(LocalWorkorder::class,$inserted->record()->toLocal());

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote();

        $this->assertDatabaseHasBoth('workorders',[
            "WorkOrderID" => $inserted->record()->WorkOrderID,
            "UserName" => $inserted->record()->UserName
        ]);
    }

    /**
     * Remote Operations > Local Update (Nothing) >  Pull > Done
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

        $updated = ApiUpdate::update(RemoteWorkorder::class,$this->nothing1->record()->WorkOrderID,[
            "companyno" => $companyno,
            "UserName" => "Mt T."
        ]);

        // Local::insert(LocalWorkorder::class,$inserted->record()->toLocal());

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote([0]);

        $this->assertDatabaseHasBoth('workorders',[
            "WorkOrderID" => $this->nothing1->record()->WorkOrderID,
            "UserName" => "Mt T."
        ]);
    }

    /**
     * Remote Operations > Local Update (Updated) >  Pull > Done
     *
     * @return void
     */
    public function test_remote_operations_and_local_update_on_updated_causes_database_in_sync_uninterupted()
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

        $updated = ApiUpdate::update(RemoteWorkorder::class,$this->update1->record()->WorkOrderID,[
            "companyno" => $companyno,
            "UserName" => "Mt T."
        ]);

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote([1]);

        $this->assertDatabaseHasBoth('workorders',[
            "WorkOrderID" => $this->update1->record()->WorkOrderID,
            "UserName" => "Mt T."
        ]);
    }

    /**
     * Remote Operations > Local Update (Deleted) > Pull > Done
     *
     * @return void
     */
    public function test_remote_operations_and_local_update_on_deleted_causes_database_in_sync_uninterupted()
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

        $updated = ApiUpdate::update(RemoteWorkorder::class,$this->delete1->record()->WorkOrderID,[
            "companyno" => $companyno,
            "UserName" => "Mt T."
        ]);

        $this->assertSame($updated,404);


        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote([2]);

        $this->assertDatabaseDeleteBoth('workorders',[
            "WorkOrderID" => $this->delete1->record()->WorkOrderID,
        ],$deleted_at);
    }

    /**
     * Remote Operations > Local Delete (Nothing) > Pull > Done
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

        $this->timePasses();

        ApiWrite::delete(RemoteWorkorder::class,$this->nothing1->record()->WorkOrderID);
        $deleted_at = Carbon::now();

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote([0]);

        $this->assertDatabaseDeleteBoth('workorders',[
            "WorkOrderID" => $this->nothing1->record()->WorkOrderID,
        ],$deleted_at);
    }

    /**
     * Remote Operations > Local Delete (Updated) > Pull > Done
     *
     * @return void
     */
    public function test_remote_operations_and_local_delete_on_updated_causes_database_in_sync_uninterupted()
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

        ApiWrite::delete(RemoteWorkorder::class,$this->update1->record()->WorkOrderID);
        $deleted_at = Carbon::now();

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote([1]);

        $this->assertDatabaseDeleteBoth('workorders',[
            "WorkOrderID" => $this->update1->record()->WorkOrderID,
        ],$deleted_at);
    }

    /**
     * Remote Operations > Local Delete (Deleted)> Pull > Done
     *
     * @return void
     */
    public function test_remote_operations_and_local_delete_on_deleted_causes_database_in_sync_uninterupted()
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

        $http = ApiWrite::delete(RemoteWorkorder::class,$this->delete1->record()->WorkOrderID);
        $this->assertSame($http,404);

        $workorders = Api::selectAll(RemoteWorkorder::class,$companyno,$lastSync);
        $local = (new LocalCollection($workorders));
        $local->download(LocalWorkorder::class);

        $this->assertRemote([0]);

        $this->assertDatabaseDeleteBoth('workorders',[
            "WorkOrderID" => $this->delete1->record()->WorkOrderID,
        ],$deleted_at);
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
