<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 19 Dec 2018 16:21:44 +0000.
 */

namespace App\Models\Local;

use App\Models\utils\WorkorderModelTrait;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as Eloquent;
/**
 * Class User
 *
 * @property int $userid
 * @property string $username
 * @property string $password
 * @property int $companyno
 * @property string $name
 * @property \Carbon\Carbon $Last_Sync
 *
 * @package App\Models
 */
class LocalUser extends Eloquent
{

	protected $connection = 'local';
	protected $primaryKey = 'userid';
	protected $table = 'users';

	public $timestamps = false;

	protected $casts = [
		'companyno' => 'int'
	];

	protected $dates = [
		'Last_Sync'
	];

	protected $hidden = [
		// 'password'
	];

	protected $guarded = [];

	public function getNameAttribute($value)
	{
		return trim($value);
	}

	public static function fromRemote($remote){
		return new self($remote->toLocal());
	}

}
