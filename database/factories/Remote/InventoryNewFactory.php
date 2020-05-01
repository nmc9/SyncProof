<?php
use App\Models\Remote\RemoteCompany;
use App\Models\Remote\RemoteWorkorder;
use Illuminate\Support\Str;

$factory->define(App\Models\Remote\RemoteInventoryNew::class, function (Faker\Generator $faker, $attr) {
	$companyno = isset($attr['companyno']) ?: factory(RemoteCompany::class)->create()->companyno;
	$create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
	return [
		'id_local' => $faker->unique()->randomNumber(7),
		'WorkOrderNo' => isset($attr['WorkOrderNo']) ?: factory(RemoteWorkorder::class)->create($create)->WorkOrderNo,
		'companyno' => $companyno,
		'quantity' => $faker->randomNumber(),
		'name' => $faker->name,
		'description' => $faker->word,
	];
});
