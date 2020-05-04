<?php
use Nmc9\SyncProof\Tests\Models\Local\LocalCompany;
use Nmc9\SyncProof\Tests\Models\Local\LocalCustomer;
use Nmc9\SyncProof\Tests\Models\Local\LocalOperation;
use Illuminate\Support\Str;

$factory->define(Nmc9\SyncProof\Tests\Models\Local\LocalWorkorder::class, function (Faker\Generator $faker,$attr) {
    $companyno = isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno;
    $create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
    return [
        'companyno' => $companyno,
        'WorkOrderNo' => $faker->unique()->randomNumber(7),
        'CustomerNo' => isset($attr['CustomerNo']) ?: factory(LocalCustomer::class)->create($create)->customerno,
        'CreatedAt' => $faker->dateTimeBetween(),
        'Appointment' => $faker->dateTimeBetween(),
        'UserName' => $faker->word,
        'Operation' => isset($attr['Operation']) ?: factory(LocalOperation::class)->create($create)->operation,
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
