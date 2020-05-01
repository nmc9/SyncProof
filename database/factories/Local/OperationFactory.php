<?php
use App\Models\Local\LocalCompany;
use Illuminate\Support\Str;


$factory->define(App\Models\Local\LocalOperation::class, function (Faker\Generator $faker, $attr) {
    return [
        'operation' => $faker->unique()->text(9),
        'type' => $faker->randomNumber(),
        'description' => $faker->word,
        'companyno' => isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno,
    ];
});
