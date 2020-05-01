<?php
use Illuminate\Support\Str;

$factory->define(App\Models\Remote\RemoteCompany::class, function (Faker\Generator $faker) {
	return [
		'companyno' => $faker->unique()->randomNumber(7),
		'name' => $faker->name,
		'isDev' => $faker->boolean,
	];
});
