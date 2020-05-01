<?php
use App\Models\Local\LocalCompany;
use Illuminate\Support\Str;

$factory->define(App\Models\Local\LocalInventory::class, function (Faker\Generator $faker, $attr) {
    return [
        'name' => $faker->name,
        'description' => $faker->word,
        'companyno' => isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno,
    ];
});
