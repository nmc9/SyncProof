<?php
use App\Models\Local\LocalCompany;
use App\Models\Local\LocalInventory;
use App\Models\Local\LocalWorkorder;
use Illuminate\Support\Str;

$factory->define(App\Models\Local\LocalInventoryUsed::class, function (Faker\Generator $faker, $attr) {
	$companyno = isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno;
	$create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
	return [
		'id_local' => $faker->unique()->randomNumber(7),
		'inventory_id' => isset($attr['inventory_id']) ?: factory(LocalInventory::class)->create($create)->id,
		'WorkOrderNo' => isset($attr['WorkOrderNo']) ?: factory(LocalWorkorder::class)->create($create)->WorkOrderNo,
		'companyno' => $companyno,
		'quantity' => $faker->randomNumber(),
	];
});
