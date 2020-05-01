<?php
use App\Models\Local\LocalCompany;
use App\Models\Local\LocalTempWorkorder;
use App\Models\Local\LocalWorkorder;
use Illuminate\Support\Str;

$factory->define(App\Models\Local\LocalTempMeter::class, function (Faker\Generator $faker, $attr) {
    $companyno = isset($attr['companyno']) ?: factory(LocalCompany::class)->create()->companyno;
    $create = isset($attr['companyno']) ? ["companyno" => $attr['companyno']] : [];
    return [
        'companyno' => $companyno,
        'local_workorderno' => isset($attr['local_workorderno']) ?: factory(LocalTempWorkorder::class)->create($create)->local_WorkOrderNo,
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
