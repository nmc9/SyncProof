<?php

use Nmc9\SyncProof\Tests\Models\Local\LocalCompany;
use Nmc9\SyncProof\Tests\Models\Local\LocalCustomer;
use Nmc9\SyncProof\Tests\Models\Local\LocalDevice;
use Nmc9\SyncProof\Tests\Models\Local\LocalImage;
use Nmc9\SyncProof\Tests\Models\Local\LocalInventory;
use Nmc9\SyncProof\Tests\Models\Local\LocalInventoryNew;
use Nmc9\SyncProof\Tests\Models\Local\LocalInventoryUsed;
use Nmc9\SyncProof\Tests\Models\Local\LocalMeter;
use Nmc9\SyncProof\Tests\Models\Local\LocalOperation;
use Nmc9\SyncProof\Tests\Models\Local\LocalUser;
use Nmc9\SyncProof\Tests\Models\Local\LocalWorkorder;
use Nmc9\SyncProof\Tests\Models\Local\LocalWorkordersNew;
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
