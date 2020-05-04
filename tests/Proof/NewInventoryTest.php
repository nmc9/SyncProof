<?php

namespace Tests\Proof;

use Nmc9\SyncProof\Tests\Models\Local\LocalCompany;
use Nmc9\SyncProof\Tests\Models\Local\LocalInventoryNew;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteCompany;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteInventoryNew;
use Nmc9\SyncProof\Proof\Api;
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

class NewInventoryTest extends TestCase
{
    use RefreshDatabase;

    private $remote;
    private $local;

    private $localInventory;

    public function setup(): void{
        parent::setup();
        Carbon::setTestNow();
        $this->remote = factory(RemoteCompany::class)->create();
        $this->local = factory(LocalCompany::class)->create($this->remote->toLocal());

        $this->localInventory = [
            [
                'WorkOrderNo' => "1234",
                'companyno' => $this->remote->companyno,
                'quantity' => "17",
                'name' => "John",
                'description' => "Nicks NewInventoryTest"
            ],
            [
                'WorkOrderNo' => "1235",
                'companyno' => $this->remote->companyno,
                'quantity' => "17",
                'name' => "Odie",
                'description' => "Odie Bone"
            ],
            [
                'WorkOrderNo' => "1236",
                'companyno' => $this->remote->companyno,
                'quantity' => "17",
                'name' => "Garfield",
                'description' => "Garfield NewInventoryTest"
            ],
        ];
    }

    public function test_insert_causes_an_insert_uninterupted()
    {

        $companyno  = $this->remote->companyno;

        //Setup state from last sync
        Remote::insert(RemoteInventoryNew::class,[
            "companyno" => $companyno,
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        //Insert New Inventory
        $remote = ApiWrite::insert(RemoteInventoryNew::class,$this->localInventory[0]);


        //Test
        $record = $remote->record();
        $this->assertDatabaseHas('inventory_new',[
            "id" => $record->id,
            'WorkOrderNo' => "1234",
            'companyno' => $this->remote->companyno,
            'quantity' => "17",
        ],'remote');

        $this->assertCount(0,LocalInventoryNew::all());
    }

    public function test_insert_bulk_causes_an_insert_bulk_uninterupted()
    {

        $companyno  = $this->remote->companyno;

        //Setup state from last sync
        Remote::insert(RemoteInventoryNew::class,[
            "companyno" => $companyno,
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        //Insert New Inventory
        $remotes = ApiWrite::insertBulk(RemoteInventoryNew::class,$this->localInventory);

        //Test
        $record = $remotes->firstRecord();
        $this->assertDatabaseHas('inventory_new',[
            "id" => $record->id,
            'WorkOrderNo' => "1234",
            'companyno' => $companyno,
            'quantity' => "17",
        ],'remote');

        $this->assertCount(0,LocalInventoryNew::all());
    }

    public function test_stored_insert_causes_an_insert()
    {

        $companyno  = $this->remote->companyno;

        //Setup state from last sync
        Remote::insert(RemoteInventoryNew::class,[
            "companyno" => $companyno,
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $local = Local::insert(LocalInventoryNew::class,$this->localInventory[0]);
        //Insert New Inventory
        $remote = ApiWrite::insertOnly(RemoteInventoryNew::class,$local);


        //Test
        $record = $remote->record();
        $this->assertDatabaseHas('inventory_new',[
            "id" => $record->id,
            'WorkOrderNo' => "1234",
            'companyno' => $this->remote->companyno,
            'quantity' => "17",
        ],'remote');

        $this->assertCount(0,LocalInventoryNew::all());
    }

    public function test_stored_insert_bulk_causes_a_bulk_insert()
    {

        $companyno = $this->remote->companyno;

        //Setup state from last sync
        Remote::insert(RemoteInventoryNew::class,[
            "companyno" => $companyno,
        ]);
        $lastSync = Carbon::now();

        $this->timePasses();

        $localCollection = LocalCollection::insertBulk(LocalInventoryNew::class,$this->localInventory);
        //Insert New Inventory
        $remote = ApiWrite::insertOnlyBulk(RemoteInventoryNew::class,$localCollection);

        //Test
        $record = $remote->firstRecord();
        $this->assertDatabaseHas('inventory_new',[
            "id" => $record->id,
            'WorkOrderNo' => "1234",
            'companyno' => $companyno,
            'quantity' => "17",
        ],'remote');

        $this->assertCount(0,LocalInventoryNew::all());
    }


}
