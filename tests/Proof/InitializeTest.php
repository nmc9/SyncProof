<?php

namespace Tests\Proof;

use Nmc9\SyncProof\Tests\Models\Local\LocalCompany;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteCompany;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteMeter;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Artisan;
use Illuminate\Support\Facades\DB;
use Nmc9\SyncProof\Tests\TestCase;

class InitializeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_download_company_from_remote_to_local()
    {
        $remoteCompany = factory(RemoteCompany::class)->create();

        $localCompany = new LocalCompany();

        $localCompany = LocalCompany::fromRemote($remoteCompany);
        $localCompany->save();

        $this->assertDatabaseHas('company',[
            "companyno" => $remoteCompany->companyno,
            "name" => $remoteCompany->name,
            "isDev" => $remoteCompany->isDev,
        ],'local');
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_download_company_from_remote_to_local_overwrite()
    {
        //Remote Database
        $remoteCompany = factory(RemoteCompany::class)->create();

        //Simulate the delete
        $local = factory(LocalCompany::class)->create(["companyno" => $remoteCompany->companyno]);
        $localCompanyNo = $local->companyno;
        $local->delete();

        //Simulate the Select
        $localCompany = LocalCompany::fromRemote($remoteCompany);
        $localCompany->save();


        //Test Local matches
        $this->assertDatabaseHas('company',[
            "companyno" => $localCompanyNo,
            "name" => $remoteCompany->name,
            "isDev" => $remoteCompany->isDev,
        ],'local');

        $this->assertCount(1,LocalCompany::all());
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_download_specific_company_from_remote_to_local()
    {
        //Remote Database
        $remoteCompany = factory(RemoteCompany::class)->create();
        factory(RemoteCompany::class,8)->create();

        //Simulate the Select
        $localCompany = LocalCompany::fromRemote($remoteCompany);
        $localCompany->save();


        //Test Local matches
        $this->assertDatabaseHas('company',[
            "companyno" => $remoteCompany->companyno,
            "name" => $remoteCompany->name,
            "isDev" => $remoteCompany->isDev,
        ],'local');

        $this->assertCount(1,LocalCompany::all());
    }
}
