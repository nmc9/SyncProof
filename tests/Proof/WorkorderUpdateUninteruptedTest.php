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
use Nmc9\SyncProof\Proof\Local;
use Nmc9\SyncProof\Proof\Remote;
use Nmc9\SyncProof\Proof\RemoteCollection;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Artisan;
use Illuminate\Support\Facades\DB;
use Nmc9\SyncProof\Tests\TestCase;

class WorkorderUpdateUninteruptedTest extends TestCase
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
     * Remote Update > Local Update > Push > Pull > Done
     *
     * @return void
     */
    public function test_conflicting_updates_remote_is_source_of_truth_casues_remote_kept_uninterupted()
    {

        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $local = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->update(["UserName" => "Garfeild"]);

        $this->timePasses();

        $http = ApiUpdate::updateRemoteTruth(RemoteWorkorder::class,$local->record()->WorkOrderID,["UserName" => "Odie"],$lastSync);

        $local->update($http->toLocal());



        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "UserName" => "Garfeild",
        ]);
        $this->assertCountBoth(1,RemoteWorkorder::all(),LocalWorkorder::all());

    }

    /**
     * Remote Update > Local Update > Push (No result) > Pull > Done
     *
     * @return void
     */
    public function test_conflicting_updates_remote_is_source_of_truth_casues_remote_kept_without_result_uninterupted()
    {

        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $local = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->update(["UserName" => "Garfeild"]);

        $this->timePasses();

        $http = ApiUpdate::updateRemoteTruth(RemoteWorkorder::class,$local->record()->WorkOrderID,["UserName" => "Odie"],$lastSync);

        $forshow = Api::select(RemoteWorkorder::class,$companyno,$lastSync);
        $localPull = new Local($forshow);
        $localPull->download(LocalWorkorder::class);

        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "UserName" => "Garfeild",
        ]);
        $this->assertCountBoth(1,RemoteWorkorder::all(),LocalWorkorder::all());
    }

    public function test_conflicting_updates_local_is_source_of_truth_casues_local_kept_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $local = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->update(["UserName" => "Garfeild"]);

        $this->timePasses();

        $http = ApiUpdate::updateLocalTruth(RemoteWorkorder::class,$local->record()->WorkOrderID,["UserName" => "Odie"],$lastSync);

        $local->update($http->toLocal());

        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "UserName" => "Odie",
        ]);
    }


    public function test_conflicting_updates_alogirthm_casues_in_sync_databases_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $local = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->update(["UserName" => "Slim Jim"]);

        $this->timePasses();

        $http = ApiUpdate::updateAlgorithm(RemoteWorkorder::class,$local->record()->WorkOrderID,["NewMeterNo" => 123],$lastSync,function($remote,$local){
            $update["NewMeterNo"] = $local["NewMeterNo"];
            if(isset($local["NewMeterSerial"])){
                $update["NewMeterSerial"] = $local["NewMeterSerial"];
            }
            return $update;
        });

        $local->update($http->toLocal());

        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "UserName" => "Slim Jim",
            "NewMeterNo" => 123,
        ]);
    }



    public function test_conflicting_updates_by_timestamp_casues_in_sync_databases_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $local = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->update(["UserName" => "Slim Jim"]);

        $this->timePasses();

        $http = ApiUpdate::updateUseLatest(RemoteWorkorder::class,$local->record()->WorkOrderID,["UserName" => "Heavy Jim"],$lastSync,Carbon::now());

        $local->update($http->toLocal());

        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "UserName" => "Heavy Jim",
        ]);
    }

    public function test_conflicting_updates_by_timestamp_same_timestamp_casues_in_sync_databases_with_local_truth_uninterupted()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $local = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $remote->update(["UserName" => "Slim Jim"]);

        $http = ApiUpdate::updateUseLatest(RemoteWorkorder::class,$local->record()->WorkOrderID,["UserName" => "Heavy Jim"],$lastSync,Carbon::now());

        $local->update($http->toLocal());

        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "UserName" => "Heavy Jim",
        ]);
    }

}
