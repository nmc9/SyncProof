<?php

$factory->define(Nmc9\SyncProof\Tests\Models\Remote\RemoteDevice::class, function (Faker\Generator $faker,$attr) {
    return [
        'androidid' => $faker->unique()->randomNumber(7),
        'password' => bcrypt($faker->password),
        'companyno' => isset($attr['companyno']) ?: factory(RemoteCompany::class)->create()->companyno,
        'description' => $faker->word,
        'Pending' => $faker->boolean,
    ];
});
