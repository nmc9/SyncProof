<?php
use App\Models\Remote\RemoteCompany;
use Illuminate\Support\Str;

$factory->define(App\Models\Remote\RemoteUser::class, function (Faker\Generator $faker, $attr) {
    return [
        'username' => $faker->userName,
        'password' => $faker->password,
        'companyno' => isset($attr['companyno']) ?: factory(RemoteCompany::class)->create()->companyno,
        'name' => $faker->name,
        'Last_Sync' => $faker->dateTimeBetween(),
    ];
});
