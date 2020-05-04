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
use Nmc9\SyncProof\Proof\Remote;
use Nmc9\SyncProof\Proof\RemoteCollection;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Artisan;
use Illuminate\Support\Facades\DB;
use Nmc9\SyncProof\Tests\TestCase;

class WorkorderUninteruptedTest extends TestCase
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
     * Local Update > Push > Done
     *
     * @return void
     */
    public function test_local_updates_record_causes_updates_to_remote_database_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        //update Local
        $local = Local::first(LocalWorkorder::class);

        //Send to remote (Assume Success)
        ApiUpdate::update(RemoteWorkorder::class,$local->record()->WorkOrderID,["UserName" => "wAndreson"]);
        //Update Local
        $local->update(["UserName" => "wAndreson"]);

        //Test
        $record = $local->record();
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $record->WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
            'UserName' => "wAndreson",
        ]);
        $this->assertCountBoth(1,RemoteWorkorder::all(),LocalWorkorder::all());
    }

    /**
     * Local Insert > Push > Done
     *
     * @return void
     */
    public function test_local_insert_causes_insert_to_remote_database_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        //Send insert to remote (ASSUME SUCCESS)
        $inserted = ApiWrite::insert(RemoteWorkorder::class,["UserName" => "John Hammond"]);
        //insert locally
        Local::insert(LocalWorkorder::class,$inserted->record()->toLocal());

        //Test
        $record = $inserted->record();
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $record->WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
            'UserName' => "John Hammond",
        ]);
        $this->assertCountBoth(2,RemoteWorkorder::all(),LocalWorkorder::all());
    }

    /**
     * Local Delete > Push > Done
     *
     * @return void
     */
    public function test_local_deletes_causes_deletes_to_remote_database_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $local = Local::first(LocalWorkorder::class);

        //Send delete to remote (ASSUME SUCCESS)
        ApiWrite::delete(RemoteWorkorder::class,$local->record()->WorkOrderID);
        //delete Local
        $local->forceDelete();

        $deleted_at = Carbon::now();

        //Test
        $record = $local->record();
        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
        ],$deleted_at);
        $this->assertCount(0,RemoteWorkorder::all(),LocalWorkorder::all());
    }

    /**
     * Remote Insert > Pull > Done
     *
     * @return void
     */
    public function test_download_workorder_from_remote_to_local_uninterupted()
    {
        $companyno = $this->remote->companyno;
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);

        //Select Inventorys
        $workorder = Api::select(RemoteWorkorder::class,$companyno);

        //Insert Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        //Test
        $record = $remote->record();
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $record->WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
            'UserName' => $record->UserName,
        ]);
        $this->assertCountBoth(1,LocalWorkorder::all(),RemoteWorkorder::all());
    }

    /**
     * Remote Insert > Remote Update > Pull > Done
     *
     * @return void
     */
    public function test_download_workorder_that_was_updated_from_remote_to_local_uninterupted()
    {
        $companyno = $this->remote->companyno;
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $remote->update(["UserName" => "wAndreson"]);

        //Select Inventorys
        $workorder = Api::select(RemoteWorkorder::class,$companyno);

        //Insert Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        //Test
        $record = $remote->record();
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $record->WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
            'UserName' => "wAndreson",
        ]);
        $this->assertCountBoth(1,LocalWorkorder::all(),RemoteWorkorder::all());
    }

    /**
     * Remote Insert > Remote Delete > Pull > Done
     *
     * @return void
     */
    public function test_download_workorder_that_was_deleted_from_remote_to_local_does_nothing_uninterupted()
    {
        $companyno = $this->remote->companyno;
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $remote->delete();
        $deleted_at = Carbon::now();

        //Select Inventorys
        $workorder = Api::select(RemoteWorkorder::class,$companyno);

        //Insert Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        //Test
        $record = $remote->record();
        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
        ],$deleted_at);
        $this->assertCountBoth(0,LocalWorkorder::all(),RemoteWorkorder::all());
    }

    /**
     * Remote Insert > Remote Update > Remote Delete > Pull > Done
     *
     * @return void
     */
    public function test_download_workorder_that_was_updated_and_deleted_from_remote_to_local_does_nothing_uninterupted()
    {
        $companyno = $this->remote->companyno;
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $remote->update(["UserName" => "wAndreson"]);

        $remote->delete();
        $deleted_at = Carbon::now();

        //Select Inventorys
        $workorder = Api::select(RemoteWorkorder::class,$companyno);

        //Insert Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        //Test
        $record = $remote->record();
        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
        ],$deleted_at);
        $this->assertCountBoth(0,LocalWorkorder::all(),RemoteWorkorder::all());
    }

    /**
     * Remote Update > Pull > Done
     *
     * @return void
     */
    public function test_download_workorder_updates_from_remote_to_local_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->update(["UserName" => "Wes Anderson"]);

        //Select Inventorys
        $workorder = Api::select(RemoteWorkorder::class,$companyno,$lastSync);

        //Update Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        //Test
        $record = $remote->record();
        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
            'companyno' => $record->companyno,
            'WorkOrderNo' => $record->WorkOrderNo,
            'CustomerNo' => $record->CustomerNo,
            "UserName" => "Wes Anderson",
        ]);
        $this->assertCountBoth(1,LocalWorkorder::all(),RemoteWorkorder::all());
    }

    /**
     * Remote Update > Remote Delete > Pull > Done
     *
     * @return void
     */
    public function test_download_workorder_was_updated_and_deleted_from_remote_to_local_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->update(["UserName" => "Wes Anderson"]);
        $remote->delete();
        $deleted_at = Carbon::now();

        //Select Inventorys
        $workorder = Api::select(RemoteWorkorder::class,$companyno,$lastSync);

        //Update Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        //Test
        $record = $remote->record();
        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
        ],$deleted_at);
        $this->assertCountBoth(0,LocalWorkorder::all(),RemoteWorkorder::all());
    }

    /**
     * Remote Delete > Pull > Done
     *
     * @return void
     */
    public function test_download_workorder_deletes_from_remote_to_local_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insertClone(RemoteWorkorder::class,LocalWorkorder::class,[
            "companyno" => $companyno
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->delete();
        $deleted_at = Carbon::now();
        //Select Inventorys
        $workorder = Api::select(RemoteWorkorder::class,$companyno,$lastSync);
        //Update Local
        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        //Test
        $record = $remote->record();
        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $record->WorkOrderID,
        ],$deleted_at);
        $this->assertCountBoth(0,LocalWorkorder::all(),RemoteWorkorder::all());
    }

    /**
     * 12. Remote Update > Remote Delete > Local Update > Push > Pull > Done
     *
     * @return void
     */
    public function test_local_update_and_remote_update_and_delete_causes_remote_to_remain_deleted_uninterupted()
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
        $remote->delete();
        $deleted_at = Carbon::now();

        $this->timePasses();

        $http = ApiUpdate::update(RemoteWorkorder::class,$local->record()->WorkOrderID,["UserName" => "John Hammond"]);
        if($http === 404){
            $local->forceDelete();
        }


        $this->assertEquals($http,404);
        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
        ],$deleted_at);
    }

    /**
     * 13.Remote Delete > Local Update > Push > Pull > Done
     *
     * @return void
     */
    public function test_local_update_and_remote_delete_causes_local_to_be_marked_deleted_uninterupted()
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

        $http = ApiUpdate::update(RemoteWorkorder::class,$local->record()->WorkOrderID,["UserName" => "John Hammond"]);
        if($http === 404){
            $local->forceDelete();
        }

        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "deleted_at" => $deleted_at
        ],$deleted_at);

    }

    /**
     * 14. Remote Update >  Local Delete > Push > Pull > Done
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

        $http = ApiWrite::delete(RemoteWorkorder::class,$local->record()->WorkOrderID);
        $local->forceDelete();
        $deleted_at = Carbon::now();

        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
        ],$deleted_at);
    }

    /**
     * 15. Remote Update > Remote Delete > Local Delete > Push > Pull > Done
     *
     * @return void
     */
    public function test_local_delete_and_remote_update_and_delete_causes_normal_deletes_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $local = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->update(["UserName" => "wAndreson"]);

        $remote->delete();
        $deleted_at = Carbon::now();

        $this->timePasses();

        $http = ApiWrite::delete(RemoteWorkorder::class,$local->record()->WorkOrderID);
        $this->assertEquals($http,404);
        $local->forceDelete();

        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
        ],$deleted_at);

    }

    /**
     * 16. Remote Delete > Local Delete > Push > Pull > Done
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

        $http = ApiWrite::delete(RemoteWorkorder::class,$local->record()->WorkOrderID);
        $local->forceDelete();

        $this->assertEquals($http,404);
        $this->assertDatabaseDeleteBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
        ],$deleted_at);

    }



}
