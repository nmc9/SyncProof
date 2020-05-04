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

class WorkorderUpdateCollectionTest extends TestCase
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

    public function test_conflicting_updates_by_timestamp_causes_in_sync_databases()
    {

        $companyno = $this->remote->companyno;

        //Setup World
        $remoteCollection = RemoteCollection::insert(RemoteWorkorder::class,3,[
            "companyno" => $companyno
        ]);
        $localCollection = $remoteCollection->cloneAllTo(LocalWorkorder::class);
        $lastSync = Carbon::now();

        $this->timePasses();

        $localCollection->update(1,["UserName" => "Nurse"]);

        $this->timePasses();

        $remoteCollection->update(1,["UserName" => "Garfeild"]);
        $remoteCollection->update(2,["UserName" => "John"]);

        $this->timePasses();

        $localCollection->update(0,["UserName" => "Nermal"]);
        $localCollection->update(2,["UserName" => "Odie"]);

        $records = [];
        foreach ($localCollection->records() as $key => $value) {
            $records[] = [
                "id" => $value->WorkOrderID,
                "localUpdate" => $value->updated_at,
                "update" => $value->updateFields(),
            ];
        }
        $http = ApiUpdate::updateUseLatestBulk(RemoteWorkorder::class,$records,$lastSync);
        foreach ($http as $htt) {
            LocalWorkorder::find($htt->WorkOrderID)->update($htt->toLocal());
        }

        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $remoteCollection->index(0)->WorkOrderID,
            "UserName" => "Nermal",
        ]);

        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $remoteCollection->index(1)->WorkOrderID,
            "UserName" => "Garfeild",
        ]);

        $this->assertDatabaseHasBoth('workorders',[
            'WorkOrderID' => $remoteCollection->index(2)->WorkOrderID,
            "UserName" => "Odie",
        ]);
    }
}
