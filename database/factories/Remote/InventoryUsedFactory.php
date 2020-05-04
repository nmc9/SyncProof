<?php
use Nmc9\SyncProof\Tests\Models\Remote\RemoteCompany;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteInventory;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteWorkorder;
use Illuminate\Support\Str;

$factory->define(Nmc9\SyncProof\Tests\Models\Remote\RemoteInventoryUsed::class, function (Faker\Generator $faker, $attr) {
	$companyno = isset($attr['companyno']) ?: factory(RemoteCompany::class)->create()->companyno;
	$create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
	return [
		'id_local' => $faker->unique()->randomNumber(7),
		'inventory_id' => isset($attr['inventory_id']) ?: factory(RemoteInventory::class)->create($create)->id,
		'WorkOrderNo' => isset($attr['WorkOrderNo']) ?: factory(RemoteWorkorder::class)->create($create)->WorkOrderNo,
		'companyno' => $companyno,
		'quantity' => $faker->randomNumber(),
	];
});
