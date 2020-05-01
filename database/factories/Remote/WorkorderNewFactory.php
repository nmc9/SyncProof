<?php
use App\Models\Remote\RemoteCompany;
use App\Models\Remote\RemoteOperation;
use Illuminate\Support\Str;

$factory->define(App\Models\Remote\RemoteWorkordersNew::class, function (Faker\Generator $faker, $attr) {
    $companyno = isset($attr['companyno']) ?: factory(RemoteCompany::class)->create()->companyno;
    $create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
    return [
        'WorkOrderID' => $faker->randomNumber(),
        'companyno' => $companyno,
        'WorkOrderNo' => $faker->unique()->randomNumber(7),
        'temp_customer_name' => $faker->word,
        'temp_service_address' => $faker->word,
        'CreatedAt' => $faker->dateTimeBetween(),
        'UserName' => $faker->word,
        'Operation' => isset($attr['Operation']) ?: factory(RemoteOperation::class)->create($create)->operation,
        'Comments' => $faker->text,
    ];
});

