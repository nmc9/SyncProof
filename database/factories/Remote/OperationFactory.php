<?php
use App\Models\Remote\RemoteCompany;
use Illuminate\Support\Str;


$factory->define(App\Models\Remote\RemoteOperation::class, function (Faker\Generator $faker, $attr) {
    return [
        'operation' => $faker->unique()->text(9),
        'type' => $faker->randomNumber(),
        'description' => $faker->word,
        'companyno' => isset($attr['companyno']) ?: factory(RemoteCompany::class)->create()->companyno,
    ];
});
