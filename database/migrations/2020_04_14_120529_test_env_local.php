<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestEnvLocal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(app()->environment('testing')){

            Schema::connection('local')->create('device', function(Blueprint $table)
            {
                $table->integer('deviceid', true);
                $table->string('androidid', 32)->unique('androidid');
                $table->string('password')->default("");
                $table->integer('companyno');
                $table->string('description', 200);
                $table->boolean('Pending');
            });

            Schema::connection('local')->create('company', function(Blueprint $table)
            {
                $table->integer('companyno',true);
                $table->string('name', 60);
                $table->boolean('isDev')->default(false);
            });

            Schema::connection('local')->create('customers', function(Blueprint $table)
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

            });

            Schema::connection('local')->create('image', function(Blueprint $table)
            {
                $table->integer('imageid', true);
                $table->integer('imageid_local')->index()->comment('The imageid from the device. Should be used as the image id from the device');
                $table->string('filename', 225);
                $table->string('uri', 225)->nullable();
                $table->integer('companyno');
                $table->integer('WorkOrderNo');
            });

            Schema::connection('local')->create('inventory', function(Blueprint $table)
            {
                $table->integer('id', true);
                $table->string('name');
                $table->string('description')->nullable();
                $table->integer('companyno');
            });

            Schema::connection('local')->create('inventory_used', function(Blueprint $table)
            {
                $table->integer('id', true);
                $table->integer('id_local');
                $table->integer('inventory_id');
                $table->integer('WorkOrderNo');
                $table->integer('companyno');
                $table->integer('quantity');
                $table->string('unit')->nullable();
            });

            Schema::connection('local')->create('inventory_new', function (Blueprint $table) {
                $table->integer('id_local',true);
                $table->integer('WorkOrderNo');
                $table->integer('companyno');
                $table->integer('quantity');
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('unit')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::connection('local')->create('meters', function(Blueprint $table)
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

            Schema::connection('local')->create('operations', function(Blueprint $table)
            {
                $table->integer('operationid', true);
                $table->string('operation', 10)->index();
                $table->integer('type');
                $table->string('description', 60);
                $table->integer('companyno')->index();
            });

            Schema::connection('local')->create('users', function(Blueprint $table)
            {
                $table->integer('userid', true);
                $table->string('username', 20)->index();
                $table->string('password', 20);
                $table->integer('companyno')->index();
                $table->string('name', 60);
                $table->dateTime('Last_Sync')->nullable();
            });


            Schema::connection('local')->create('workorders_new', function(Blueprint $table)
            {
                $table->integer('local_id', true);
                $table->integer('WorkOrderID');
                $table->integer('companyno')->index();
                $table->integer('WorkOrderNo')->index();
                $table->string('temp_customer_name', 35)->nullable();
                $table->string('temp_service_address', 35)->nullable();
                $table->dateTime('CreatedAt')->nullable();
                $table->string('UserName', 20)->index();
                $table->string('Operation', 10);
                $table->text('Comments', 65535);
            });

            Schema::connection('local')->create('workorders', function(Blueprint $table)
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

            Schema::connection('local')->create('temp_workorders', function(Blueprint $table)
            {
                $table->integer('local_WorkOrderID', true);
                $table->integer('WorkOrderID')->nullable();
                $table->integer('companyno')->index();
                $table->integer('local_WorkOrderNo')->index()->nullable();
                $table->string('CustomerNo', 12)->index()->nullable();
                $table->string('local_CustomerNo', 12)->index()->nullable();
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
            });

            Schema::connection('local')->create('temp_meters', function(Blueprint $table)
            {
                $table->integer('local_meterid', true);
                $table->integer('meterid')->nullable();
                $table->integer('companyno')->index();
                $table->integer('workorderno')->index()->nullable();
                $table->integer('local_workorderno')->index()->nullable();
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

            Schema::connection('local')->drop('device');

            Schema::connection('local')->drop('company');

            Schema::connection('local')->drop('customers');

            Schema::connection('local')->drop('image');

            Schema::connection('local')->drop('inventory');

            Schema::connection('local')->drop('inventory_used');

            Schema::connection('local')->drop('inventory_new');

            Schema::connection('local')->drop('meters');

            Schema::connection('local')->drop('operations');

            Schema::connection('local')->drop('users');

            Schema::connection('local')->drop('workorders_new');

            Schema::connection('local')->drop('workorders');
        }
    }
}
