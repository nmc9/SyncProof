<?php
use Nmc9\SyncProof\Tests\Models\Local\LocalCompany;
use Illuminate\Support\Str;

$factory->define(Nmc9\SyncProof\Tests\Models\Local\LocalUser::class, function (Faker\Generator $faker, $attr) {
    return [
        'username' => $faker->userName,
        'password' => $faker->password,
        'companyno' => isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno,
        'name' => $faker->name,
        'Last_Sync' => $faker->dateTimeBetween(),
    ];
});
