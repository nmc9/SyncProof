<?php
use Nmc9\SyncProof\Tests\Models\Local\LocalCompany;
use Nmc9\SyncProof\Tests\Models\Local\LocalOperation;
use Illuminate\Support\Str;

$factory->define(Nmc9\SyncProof\Tests\Models\Local\LocalWorkordersNew::class, function (Faker\Generator $faker, $attr) {
    $companyno = isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno;
    $create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
    return [
        'WorkOrderID' => $faker->randomNumber(),
        'companyno' => $companyno,
        'WorkOrderNo' => $faker->unique()->randomNumber(7),
        'temp_customer_name' => $faker->word,
        'temp_service_address' => $faker->word,
        'CreatedAt' => $faker->dateTimeBetween(),
        'UserName' => $faker->word,
        'Operation' => isset($attr['Operation']) ?: factory(LocalOperation::class)->create($create)->operation,
        'Comments' => $faker->text,
    ];
});

