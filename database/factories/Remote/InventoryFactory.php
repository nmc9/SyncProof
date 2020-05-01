<?php
use App\Models\Remote\RemoteCompany;
use Illuminate\Support\Str;

$factory->define(App\Models\Remote\RemoteInventory::class, function (Faker\Generator $faker, $attr) {
    return [
        'name' => $faker->name,
        'description' => $faker->word,
        'companyno' => isset($attr['companyno']) ?: factory(RemoteCompany::class)->create()->companyno,
    ];
});
