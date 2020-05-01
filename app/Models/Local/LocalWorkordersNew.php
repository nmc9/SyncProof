<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 19 Dec 2018 16:21:44 +0000.
 */

namespace App\Models\Local;

use App\Models\utils\WorkorderModelTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;
/**
 * Class WorkordersNew
 *
 * @property int $remote_id
 * @property int $WorkOrderID
 * @property int $companyno
 * @property int $WorkOrderNo
 * @property string $temp_customer_name
 * @property string $temp_service_address
 * @property \Carbon\Carbon $CreatedAt
 * @property string $UserName
 * @property string $Operation
 * @property string $Comments
 *
 * @package App\Models
 */
class LocalWorkordersNew extends Eloquent
{
	use WorkorderModelTrait;

	protected $connection = 'local';
	protected $table = 'workorders_new';
	public $primaryKey = 'remote_id';
	public $timestamps = false;

	protected $casts = [
		'WorkOrderID' => 'int',
		'companyno' => 'int',
		'WorkOrderNo' => 'int'
	];

	protected $dates = [
		'CreatedAt'
	];

	protected $fillable = [
		'WorkOrderID',
		'companyno',
		'WorkOrderNo',
		'temp_customer_name',
		'temp_service_address',
		'CreatedAt',
		'UserName',
		'Operation',
		'Comments'
	];

	protected $writeable = "*";
}
