<?php
use Illuminate\Support\Str;

$factory->define(Nmc9\SyncProof\Tests\Models\Local\LocalCompany::class, function (Faker\Generator $faker) {
	return [
		'companyno' => $faker->unique()->randomNumber(7),
		'name' => $faker->name,
		'isDev' => $faker->boolean,
	];
});
