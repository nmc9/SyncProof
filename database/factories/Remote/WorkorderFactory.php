<?php
use App\Models\Remote\RemoteCompany;
use App\Models\Remote\RemoteCustomer;
use App\Models\Remote\RemoteOperation;
use Illuminate\Support\Str;

$factory->define(App\Models\Remote\RemoteWorkorder::class, function (Faker\Generator $faker,$attr) {
    $companyno = isset($attr['companyno']) ?: factory(RemoteCompany::class)->create()->companyno;
    $create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
    return [
        'companyno' => $companyno,
        'WorkOrderNo' => $faker->unique()->randomNumber(7),
        'CustomerNo' => isset($attr['CustomerNo']) ?: factory(RemoteCustomer::class)->create($create)->customerno,
        'CreatedAt' => $faker->dateTimeBetween(),
        'Appointment' => $faker->dateTimeBetween(),
        'UserName' => $faker->word,
        'Operation' => isset($attr['Operation']) ?: factory(RemoteOperation::class)->create($create)->operation,
        'Comments' => $faker->text,
        'Action' => $faker->text,
        'Completed' => $faker->boolean,
        'Priority' => $faker->randomNumber(),
        'DisplayOrder' => $faker->randomNumber(),
        'NewMeterNo' => $faker->word,
        'NewMeterSerial' => $faker->word,
        'NewMeterMXU' => $faker->word,
    ];
});
