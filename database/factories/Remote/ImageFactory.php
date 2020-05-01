<?php
use App\Models\Remote\RemoteCompany;
use App\Models\Remote\RemoteWorkorder;
use Illuminate\Support\Str;


$factory->define(App\Models\Remote\RemoteImage::class, function (Faker\Generator $faker,$attr) {
	$companyno = isset($attr['companyno']) ?: factory(RemoteCompany::class)->create()->companyno;
	$create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
	return [
		'imageid_local' => $faker->unique()->randomNumber(7),
		'filename' => $faker->word,
		'uri' => $faker->word,
		'companyno' => $companyno,
		'WorkOrderNo' => isset($attr['WorkOrderNo']) ?: factory(RemoteWorkorder::class)->create($create)->WorkOrderNo,
	];
});
