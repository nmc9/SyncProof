<?php

namespace App\Models\Remote;

use App\Models\utils\WorkorderModelTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
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
class RemoteUser extends Eloquent
{
	use SoftDeletes;

	protected $connection = 'remote';
	protected $primaryKey = 'userid';
	protected $table = 'users';

	protected $casts = [
		'companyno' => 'int'
	];

	protected $dates = [
		'Last_Sync'
	];

	public function getNameAttribute($value)
	{
		return trim($value);
	}

	public function toLocal(){
		return [
			'userid' => $this->userid,
			'username' => $this->username,
			'password' => $this->password,
			'companyno' => $this->companyno,
			'name' => $this->name,
			'Last_Sync' => $this->Last_Sync,
			// "created_at" => $this->created_at,
			// "updated_at" => $this->updated_at,
			// "deleted_at" => $this->deleted_at,
		];
	}

}
