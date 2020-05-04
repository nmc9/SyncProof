<?php
use Nmc9\SyncProof\Tests\Models\Remote\RemoteCompany;
use Illuminate\Support\Str;


$factory->define(Nmc9\SyncProof\Tests\Models\Remote\RemoteOperation::class, function (Faker\Generator $faker, $attr) {
    return [
        'operation' => $faker->unique()->text(9),
        'type' => $faker->randomNumber(),
        'description' => $faker->word,
        'companyno' => isset($attr['companyno']) ?: factory(RemoteCompany::class)->create()->companyno,
    ];
});
