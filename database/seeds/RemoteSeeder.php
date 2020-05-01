<?php

use App\Models\Remote\RemoteCompany;
use App\Models\Remote\RemoteCustomer;
use App\Models\Remote\RemoteDevice;
use App\Models\Remote\RemoteImage;
use App\Models\Remote\RemoteInventory;
use App\Models\Remote\RemoteInventoryNew;
use App\Models\Remote\RemoteInventoryUsed;
use App\Models\Remote\RemoteMeter;
use App\Models\Remote\RemoteOperation;
use App\Models\Remote\RemoteUser;
use App\Models\Remote\RemoteWorkorder;
use App\Models\Remote\RemoteWorkordersNew;
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
