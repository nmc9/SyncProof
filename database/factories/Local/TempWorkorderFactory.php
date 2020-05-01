<?php
use App\Models\Local\LocalCompany;
use App\Models\Local\LocalCustomer;
use App\Models\Local\LocalOperation;
use Illuminate\Support\Str;

$factory->define(App\Models\Local\LocalTempWorkorder::class, function (Faker\Generator $faker,$attr) {
    $companyno = isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno;
    $create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
    return [
        'companyno' => $companyno,
        'local_WorkOrderNo' => $faker->unique()->randomNumber(7),
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
