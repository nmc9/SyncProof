<?php
use Nmc9\SyncProof\Tests\Models\Local\LocalCompany;
use Illuminate\Support\Str;


$factory->define(Nmc9\SyncProof\Tests\Models\Local\LocalOperation::class, function (Faker\Generator $faker, $attr) {
    return [
        'operation' => $faker->unique()->text(9),
        'type' => $faker->randomNumber(),
        'description' => $faker->word,
        'companyno' => isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno,
    ];
});
