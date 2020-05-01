<?php

namespace App\Models\Remote;

use App\Models\utils\WorkorderModelTrait;

use Illuminate\Database\Eloquent\Model as Eloquent;
/**
 * Class Company
 *
 * @property int $companyid
 * @property int $companyno
 * @property string $name
 *
 * @package App\Models
 */
class RemoteCompany extends Eloquent
{
	// use WorkorderModelTrait;
	protected $connection = 'remote';
	protected $table = 'company';
	protected $primaryKey = 'companyno';
	public $timestamps = false;

	public $incrementing = false;
	// protected $casts = [
	// 	'companyno' => 'int',
	// 	'isDev' => 'boolean'
	// ];

	// protected $fillable = [
	// 	'companyno',
	// 	'name'
	// ];

	protected $guarded = [];


	protected $writeable = null;

	public function toLocal(){
		return [
			"companyno" => $this->companyno,
			"name" => $this->name,
			"isDev" => $this->isDev,
		];
	}

}
