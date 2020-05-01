<?php

namespace App\Models\Local;

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
class LocalCompany extends Eloquent
{
	protected $connection = 'local';
	protected $table = 'company';
	protected $primaryKey = 'companyno';
	public $timestamps = false;

	public $incrementing = false;

	protected $guarded = [];


	protected $writeable = null;

	public static function fromRemote($remote){
		return new self($remote->toLocal());
	}
}
