<?php

use App\Models\Local\LocalCompany;
use App\Models\Local\LocalCustomer;
use App\Models\Local\LocalDevice;
use App\Models\Local\LocalImage;
use App\Models\Local\LocalInventory;
use App\Models\Local\LocalInventoryNew;
use App\Models\Local\LocalInventoryUsed;
use App\Models\Local\LocalMeter;
use App\Models\Local\LocalOperation;
use App\Models\Local\LocalUser;
use App\Models\Local\LocalWorkorder;
use App\Models\Local\LocalWorkordersNew;
use Illuminate\Database\Seeder;
use Laracasts\TestDummy\Factory as TestDummy;

class LocalSeeder extends Seeder
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
    	$company = factory(LocalCompany::class)->create();
    	factory(LocalDevice::class,3)->create(["companyno" => $company->companyno]);
    	factory(LocalOperation::class,3)->create(["companyno" => $company->companyno]);
    	factory(LocalWorkorder::class,3)->create(["companyno" => $company->companyno]);
    	factory(LocalCustomer::class,3)->create(["companyno" => $company->companyno]);

    	factory(LocalMeter::class,3)->create(["companyno" => $company->companyno]);
    	factory(LocalWorkordersNew::class,3)->create(["companyno" => $company->companyno]);
    	factory(LocalUser::class,3)->create(["companyno" => $company->companyno]);
    	factory(LocalImage::class,3)->create(["companyno" => $company->companyno]);
    	$this->inventory($company);
    	return $company;
    }

    public function inventory($company){
    	factory(LocalInventory::class,3)->create(["companyno" => $company->companyno]);
    	factory(LocalInventoryUsed::class,3)->create(["companyno" => $company->companyno]);
    	factory(LocalInventoryNew::class,3)->create(["companyno" => $company->companyno]);
    }
}
