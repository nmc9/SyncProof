<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 19 Dec 2018 16:21:44 +0000.
 */

namespace App\Models\Local;

use App\Models\utils\WorkorderModelTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;
/**
 * Class Operation
 *
 * @property int $operationid
 * @property string $operation
 * @property int $type
 * @property string $description
 * @property int $companyno
 *
 * @package App\Models
 */
class LocalOperation extends Eloquent
{
	use WorkorderModelTrait;

	protected $connection = 'local';
	protected $primaryKey = 'operationid';
	protected $table = 'operations';

	public $timestamps = false;

	protected $casts = [
		'type' => 'int',
		'companyno' => 'int'
	];

	protected $fillable = [
		'operation',
		'type',
		'description',
		'companyno'
	];
	protected $writeable = null;
}
