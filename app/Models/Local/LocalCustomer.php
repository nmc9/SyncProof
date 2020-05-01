<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 19 Dec 2018 16:21:44 +0000.
 */

namespace App\Models\Local;

use App\Models\utils\WorkorderModelTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;
/**
 * Class Customer
 *
 * @property int $customerid
 * @property string $customerno
 * @property string $name
 * @property string $serviceaddr
 * @property string $city
 * @property string $zipcode
 * @property string $state
 * @property string $phoneno
 * @property string $cellno
 * @property float $Latitude
 * @property float $Longitude
 * @property string $email
 * @property int $companyno
 *
 * @package App\Models
 */
class LocalCustomer extends Eloquent
{
	protected $connection = 'local';
	protected $primaryKey = 'customerid';
	protected $table = 'customers';

	public $timestamps = false;

	protected $casts = [
		// 'Latitude' => 'float',
		// 'Longitude' => 'float',
		'companyno' => 'int'
	];

	protected $fillable = [
		'customerno',
		'name',
		'serviceaddr',
		'city',
		'zipcode',
		'state',
		'phoneno',
		'cellno',
		'Latitude',
		'Longitude',
		'GPSGenerated',
		'email',
		'companyno'
	];

	public function getNameAttribute($value){
		return rtrim($value);
	}
	public function getServiceaddrAttribute($value){
		return rtrim($value);
	}
	public function getCityAttribute($value){
		return rtrim($value);
	}
	public function getZipcodeAttribute($value){
		return rtrim($value);
	}
	public function getEmailAttribute($value){
		return rtrim($value);
	}

	public function getCellnoAttribute($value){
		return trim($value);
	}
	public function getPhonenoAttribute($value){
		return trim($value);
	}

	public static function fromRemote($remote){
		return new self($remote->toLocal());
	}
}
