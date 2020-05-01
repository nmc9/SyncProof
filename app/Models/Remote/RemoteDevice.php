<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 19 Dec 2018 16:21:44 +0000.
 */

namespace App\Models\Remote;

use App\Models\utils\WorkorderModelTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * Class Device
 *
 * @property int $deviceid
 * @property string $androidid
 * @property int $companyno
 * @property string $description
 * @property bool $Pending
 *
 * @package App\Models
 */
class RemoteDevice extends Authenticatable
{
	use WorkorderModelTrait;
	use HasApiTokens;

	protected $connection = 'remote';
	protected $table = 'device';
	protected $primaryKey = 'deviceid';
	public $timestamps = false;

	protected $casts = [
		'companyno' => 'int',
		'Pending' => 'bool'
	];

	protected $fillable = [
		'androidid',
		'password',
		'companyno',
		'description',
		'Pending'
	];

	protected $hidden = [
		'password'
	];

	protected $writeable = null;

	public function findForPassport($username) {
		return $this->where('androidid', $username)->first();
	}
}
