<?php
use Nmc9\SyncProof\Tests\Models\Local\LocalCompany;
use Illuminate\Support\Str;

$factory->define(Nmc9\SyncProof\Tests\Models\Local\LocalInventory::class, function (Faker\Generator $faker, $attr) {
    return [
        'name' => $faker->name,
        'description' => $faker->word,
        'companyno' => isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno,
    ];
});
