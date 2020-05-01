<?php
use App\Models\Local\LocalCompany;
use Illuminate\Support\Str;


$factory->define(App\Models\Local\LocalCustomer::class, function (Faker\Generator $faker,$attr) {

    return [
        'customerno' => $faker->unique()->randomNumber(7),
        'name' => $faker->name,
        'serviceaddr' => $faker->word,
        'city' => $faker->city,
        'zipcode' => $faker->word,
        'state' => $faker->stateAbbr,
        'phoneno' => $faker->word,
        'cellno' => $faker->word,
        'Latitude' => $faker->randomFloat(5,null,200),
        'Longitude' => $faker->randomFloat(5,null,200),
        'email' => $faker->safeEmail,
        'companyno' => isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno,
    ];
});
