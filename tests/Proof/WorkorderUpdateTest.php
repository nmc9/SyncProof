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

class WorkorderUpdateTest extends TestCase
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
    public function test_conflicting_updates_remote_is_source_of_truth_causes_remote_kept()
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

        $local->update(["UserName" => "Odie"]);

        $localRecord = $local->record();
        if($localRecord->updated_at >= $lastSync){
            $http = ApiUpdate::updateRemoteTruth(RemoteWorkorder::class,$localRecord->WorkOrderID,$localRecord->updateFields(),$lastSync);
        }

        $local->update($http->toLocal());

        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "UserName" => "Garfeild",
        ]);
    }

    /**
     * Remote Update > Local Update > Push (No Result) > Select > Done
     *
     * @return void
     */
    public function test_conflicting_updates_remote_is_source_of_truth_causes_remote_kept_without_getting_update_result()
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

        $local->update(["UserName" => "Odie"]);

        $localRecord = $local->record();
        if($localRecord->updated_at >= $lastSync){
            ApiUpdate::updateRemoteTruth(RemoteWorkorder::class,$localRecord->WorkOrderID,$localRecord->updateFields(),$lastSync);
        }

        $workorder = Api::select(RemoteWorkorder::class,$companyno,$lastSync);

        $local = new Local($workorder);
        $local->download(LocalWorkorder::class);

        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "UserName" => "Garfeild",
        ]);
    }

    public function test_conflicting_updates_local_is_source_of_truth_causes_local_kept()
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

        $local->update(["UserName" => "Odie"]);

        $this->timePasses();

        $localRecord = $local->record();
        if($localRecord->updated_at >= $lastSync){
            $http = ApiUpdate::updateLocalTruth(RemoteWorkorder::class,$localRecord->WorkOrderID,$localRecord->updateFields(),$lastSync);
        }

        //Uncessary
        $local->update($http->toLocal());

        $this->assertDatabaseHas('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "UserName" => "Odie",
            "deleted_at" => null
        ],'remote');

        $this->assertDatabaseHas('workorders',[
            'WorkOrderID' => $local->record()->WorkOrderID,
            "UserName" => "Odie",
        ],'local');
    }


    public function test_conflicting_updates_alogirthm_causes_in_sync_databases()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $local = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $remote->update(["CustomerNo" => "Slim Jim"]);

        $this->timePasses();

        $local->update(["NewMeterNo" => 123]);

        $localRecord = $local->record();
        if($localRecord->updated_at >= $lastSync){
            $http = ApiUpdate::updateAlgorithm(RemoteWorkorder::class,$localRecord->WorkOrderID,$localRecord->updateFields(),$lastSync,function($remote,$local){
                $update["NewMeterNo"] = $local["NewMeterNo"];
                if(isset($local["NewMeterSerial"])){
                    $update["NewMeterSerial"] = $local["NewMeterSerial"];
                }
                return $update;
            });
        }

        $local->update($http->toLocal());

        $this->assertDatabaseHas('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "CustomerNo" => "Slim Jim",
            "NewMeterNo" => 123,
            "deleted_at" => null
        ],'remote');

        $this->assertDatabaseHas('workorders',[
            'WorkOrderID' => $local->record()->WorkOrderID,
            "CustomerNo" => "Slim Jim",
            "NewMeterNo" => 123,
        ],'local');
    }



    public function test_conflicting_updates_by_timestamp_causes_in_sync_databases()
    {
        $companyno = $this->remote->companyno;

        //Setup World
        $remote = Remote::insert(RemoteWorkorder::class,[
            "companyno" => $companyno
        ]);
        $local = $remote->cloneTo(LocalWorkorder::class);
        $lastSync = Carbon::now();


        $this->timePasses();

        $local->update(["UserName" => "Heavy Jim"]);

        $this->timePasses();

        $remote->update(["UserName" => "Slim Jim"]);

        $this->timePasses();

        $localRecord = $local->record();
        if($localRecord->updated_at >= $lastSync){
            $http = ApiUpdate::updateUseLatest(RemoteWorkorder::class,$localRecord->WorkOrderID,$localRecord->updateFields(),$lastSync,$localRecord->updated_at);

        }
        $local->update($http->toLocal());

        $this->assertDatabaseHas('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "UserName" => "Slim Jim",
            "deleted_at" => null
        ],'remote');

        $this->assertDatabaseHas('workorders',[
            'WorkOrderID' => $local->record()->WorkOrderID,
            "UserName" => "Slim Jim",
        ],'local');
    }

    public function test_conflicting_updates_by_timestamp_same_timestamp_causes_in_sync_databases_with_local_truth()
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
        $local->update(["UserName" => "Heavy Jim"]);

        $localRecord = $local->record();

        if($localRecord->updated_at >= $lastSync){
            $http = ApiUpdate::updateUseLatest(RemoteWorkorder::class,$localRecord->WorkOrderID,$localRecord->updateFields(),$lastSync,$localRecord->updated_at);

        }
        $local->update($http->toLocal());

        $this->assertDatabaseHas('workorders',[
            'WorkOrderID' => $remote->record()->WorkOrderID,
            "UserName" => "Heavy Jim",
            "deleted_at" => null
        ],'remote');

        $this->assertDatabaseHas('workorders',[
            'WorkOrderID' => $local->record()->WorkOrderID,
            "UserName" => "Heavy Jim",
        ],'local');
    }

}
