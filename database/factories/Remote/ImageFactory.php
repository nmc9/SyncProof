<?php
use Nmc9\SyncProof\Tests\Models\Remote\RemoteCompany;
use Nmc9\SyncProof\Tests\Models\Remote\RemoteWorkorder;
use Illuminate\Support\Str;


$factory->define(Nmc9\SyncProof\Tests\Models\Remote\RemoteImage::class, function (Faker\Generator $faker,$attr) {
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
