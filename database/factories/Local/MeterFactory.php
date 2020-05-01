<?php
use App\Models\Local\LocalCompany;
use App\Models\Local\LocalWorkorder;
use Illuminate\Support\Str;

$factory->define(App\Models\Local\LocalMeter::class, function (Faker\Generator $faker, $attr) {
    $companyno = isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno;
    $create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
    return [
        'companyno' => $companyno,
        'workorderno' => isset($attr['workorderno']) ?: factory(LocalWorkorder::class)->create($create)->WorkOrderNo,
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
