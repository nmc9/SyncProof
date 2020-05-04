<?php

use Nmc9\SyncProof\Tests\Models\Remote\RemoteCompany;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteCustomer;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteDevice;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteImage;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteInventory;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteInventoryNew;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteInventoryUsed;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteMeter;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteOperation;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteUser;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteWorkorder;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteWorkordersNew;
use Illuminate\Database\Seeder;
use Laracasts\TestDummy\Factory as TestDummy;

class RemoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	return $this->workorder();
    }

    public function workorder(){
    	$company = factory(RemoteCompany::class)->create();
    	factory(RemoteDevice::class,3)->create(["companyno" => $company->companyno]);
    	factory(RemoteOperation::class,3)->create(["companyno" => $company->companyno]);
    	factory(RemoteWorkorder::class,3)->create(["companyno" => $company->companyno]);
    	factory(RemoteCustomer::class,3)->create(["companyno" => $company->companyno]);

    	factory(RemoteMeter::class,3)->create(["companyno" => $company->companyno]);
    	factory(RemoteWorkordersNew::class,3)->create(["companyno" => $company->companyno]);
    	factory(RemoteUser::class,3)->create(["companyno" => $company->companyno]);
    	factory(RemoteImage::class,3)->create(["companyno" => $company->companyno]);
    	$this->inventory($company);
    	return $company;
    }

    public function inventory($company){
    	factory(RemoteInventory::class,3)->create(["companyno" => $company->companyno]);
    	factory(RemoteInventoryUsed::class,3)->create(["companyno" => $company->companyno]);
    	factory(RemoteInventoryNew::class,3)->create(["companyno" => $company->companyno]);
    }
}
