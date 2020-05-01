<?php
use App\Models\Remote\RemoteCompany;
use App\Models\Remote\RemoteWorkorder;
use Illuminate\Support\Str;

$factory->define(App\Models\Remote\RemoteMeter::class, function (Faker\Generator $faker, $attr) {
    $companyno = isset($attr['companyno']) ?: factory(RemoteCompany::class)->create()->companyno;
    $create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
    return [
        'companyno' => $companyno,
        'workorderno' => isset($attr['workorderno']) ?: factory(RemoteWorkorder::class)->create($create)->WorkOrderNo,
        'meterno' => $faker->unique()->randomNumber(7),
        'MXU' => $faker->word,
        'prevread' => $faker->randomNumber(),
        'prevreaddt' => $faker->dateTimeBetween(),
        'currread' => $faker->randomNumber(),
        'currreaddt' => $faker->dateTimeBetween(),
        'notes' => $faker->word,
        'swapmeter' => $faker->word,
        'new_MXU' => $faker->word,
        'new_serial_num' => $faker->word,
        'updated' => $faker->boolean,
        'meter_digits' => $faker->randomNumber(),
        'multiplier' => $faker->randomNumber(),
    ];
});
