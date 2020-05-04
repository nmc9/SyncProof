<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestEnvRemote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(app()->environment('testing')){
            Schema::connection('remote')->create('device', function(Blueprint $table)
            {
                $table->integer('deviceid', true);
                $table->string('androidid', 32)->unique('androidid');
                $table->string('password')->default("");
                $table->integer('companyno');
                $table->string('description', 200);
                $table->boolean('Pending');
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection('remote')->create('company', function(Blueprint $table)
            {
                $table->integer('companyno',true);
                $table->string('name', 60);
                $table->boolean('isDev')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection('remote')->create('customers', function(Blueprint $table)
            {
                $table->integer('customerid', true);
                $table->string('customerno', 12)->index();
                $table->string('name', 35);
                $table->string('serviceaddr', 35);
                $table->string('city')->nullable();
                $table->string('zipcode', 20)->nullable();
                $table->string('state', 2)->nullable();
                $table->string('phoneno', 15);
                $table->string('cellno', 15);
                $table->decimal('Latitude', 15, 7)->nullable();
                $table->decimal('Longitude', 15, 7)->nullable();
                $table->tinyInteger('GPSGenerated')->default(0);
                $table->string('email')->nullable();
                $table->integer('companyno')->index();
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection('remote')->create('image', function(Blueprint $table)
            {
                $table->integer('imageid', true);
                $table->integer('imageid_local')->index()->comment('The imageid from the device. Should be used as the image id from the device');
                $table->string('filename', 225);
                $table->string('uri', 225)->nullable();
                $table->integer('companyno');
                $table->integer('WorkOrderNo');
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection('remote')->create('inventory', function(Blueprint $table)
            {
                $table->integer('id', true);
                $table->string('name');
                $table->string('description')->nullable();
                $table->integer('companyno');
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection('remote')->create('inventory_used', function(Blueprint $table)
            {
                $table->integer('id', true);
                $table->integer('id_local');
                $table->integer('inventory_id');
                $table->integer('WorkOrderNo');
                $table->integer('companyno');
                $table->integer('quantity');
                $table->string('unit')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection('remote')->create('inventory_new', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('id_local');
                $table->integer('WorkOrderNo');
                $table->integer('companyno');
                $table->integer('quantity');
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('unit')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection('remote')->create('meters', function(Blueprint $table)
            {
                $table->integer('meterid', true);
                $table->integer('companyno')->index();
                $table->integer('workorderno')->index();
                $table->string('meterno', 17)->index();
                $table->string('MXU', 17)->nullable();
                $table->integer('prevread');
                $table->date('prevreaddt');
                $table->integer('currread')->nullable();
                $table->date('currreaddt')->nullable();
                $table->string('notes', 60);
                $table->string('swapmeter', 17);
                $table->string('new_MXU', 17)->nullable();
                $table->string('new_serial_num', 17)->nullable();
                $table->boolean('updated');
                $table->integer('meter_digits');
                $table->integer('multiplier');
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection('remote')->create('operations', function(Blueprint $table)
            {
                $table->integer('operationid', true);
                $table->string('operation', 10)->index();
                $table->integer('type');
                $table->string('description', 60);
                $table->integer('companyno')->index();
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection('remote')->create('users', function(Blueprint $table)
            {
                $table->integer('userid', true);
                $table->string('username', 20)->index();
                $table->string('password', 20);
                $table->integer('companyno')->index();
                $table->string('name', 60);
                $table->dateTime('Last_Sync')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });


            Schema::connection('remote')->create('workorders_new', function(Blueprint $table)
            {
                $table->integer('remote_id', true);
                $table->integer('WorkOrderID');
                $table->integer('companyno')->index();
                $table->integer('WorkOrderNo')->index();
                $table->string('temp_customer_name', 35)->nullable();
                $table->string('temp_service_address', 35)->nullable();
                $table->dateTime('CreatedAt')->nullable();
                $table->string('UserName', 20)->index();
                $table->string('Operation', 10);
                $table->text('Comments', 65535);
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection('remote')->create('workorders', function(Blueprint $table)
            {
                $table->integer('WorkOrderID', true);
                $table->integer('companyno')->index();
                $table->integer('WorkOrderNo')->index();
                $table->string('CustomerNo', 12)->index();
                $table->dateTime('CreatedAt')->nullable();
                $table->dateTime('Appointment')->nullable();
                $table->string('UserName', 20)->index();
                $table->string('Operation', 10);
                $table->text('Comments', 65535);
                $table->text('Action', 65535)->nullable();
                $table->boolean('Completed')->nullable()->default(0)->index();
                $table->integer('Priority')->default(4);
                $table->integer('DisplayOrder')->default(999);
                $table->string('NewMeterNo', 17)->nullable();
                $table->string('NewMeterSerial', 17)->nullable();
                $table->string('NewMeterMXU', 17)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(app()->environment('testing')){

            Schema::connection('remote')->drop('device');

            Schema::connection('remote')->drop('company');

            Schema::connection('remote')->drop('customers');

            Schema::connection('remote')->drop('image');

            Schema::connection('remote')->drop('inventory');

            Schema::connection('remote')->drop('inventory_used');

            Schema::connection('remote')->drop('inventory_new');

            Schema::connection('remote')->drop('meters');

            Schema::connection('remote')->drop('operations');

            Schema::connection('remote')->drop('users');

            Schema::connection('remote')->drop('workorders_new');

            Schema::connection('remote')->drop('workorders');
        }
    }
}
