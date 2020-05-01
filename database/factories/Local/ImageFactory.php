<?php
use App\Models\Local\LocalCompany;
use App\Models\Local\LocalWorkorder;
use Illuminate\Support\Str;


$factory->define(App\Models\Local\LocalImage::class, function (Faker\Generator $faker,$attr) {
	$companyno = isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno;
	$create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
	return [
		'imageid_local' => $faker->unique()->randomNumber(7),
		'filename' => $faker->word,
		'uri' => $faker->word,
		'companyno' => $companyno,
		'WorkOrderNo' => isset($attr['WorkOrderNo']) ?: factory(LocalWorkorder::class)->create($create)->WorkOrderNo,
	];
});
