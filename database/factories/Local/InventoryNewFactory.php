<?php
use Nmc9\SyncProof\Tests\Models\Local\LocalCompany;
use Nmc9\SyncProof\Tests\Models\Local\LocalWorkorder;
use Illuminate\Support\Str;

$factory->define(Nmc9\SyncProof\Tests\Models\Local\LocalInventoryNew::class, function (Faker\Generator $faker, $attr) {
	$companyno = isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno;
	$create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
	return [
		'id_local' => $faker->unique()->randomNumber(7),
		'WorkOrderNo' => isset($attr['WorkOrderNo']) ?: factory(LocalWorkorder::class)->create($create)->WorkOrderNo,
		'companyno' => $companyno,
		'quantity' => $faker->randomNumber(),
		'name' => $faker->name,
		'description' => $faker->word,
	];
});
