<?php

namespace App\Models\Local;

use App\Models\Local\LocalMeter;
use App\Models\utils\WorkorderModelTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Class Workorder
 *
 * @property int $WorkOrderID
 * @property int $companyno
 * @property int $WorkOrderNo
 * @property string $CustomerNo
 * @property \Carbon\Carbon $CreatedAt
 * @property \Carbon\Carbon $Appointment
 * @property string $UserName
 * @property string $Operation
 * @property string $Comments
 * @property string $Action
 * @property bool $Completed
 * @property int $Priority
 * @property int $DisplayOrder
 * @property string $NewMeterNo
 * @property string $NewMeterSerial
 * @property string $NewMeterMXU
 * @property \Carbon\Carbon $UpdatedAt
 *
 * @package App\Models
 */
class LocalWorkorder extends Eloquent
{
	use SoftDeletes;
	protected $connection = 'local';
	public $primaryKey = 'WorkOrderID';
	protected $table = 'workorders';


	protected $casts = [
		'companyno' => 'int',
		'WorkOrderNo' => 'int',
		'Completed' => 'bool',
		'Priority' => 'int',
		'DisplayOrder' => 'int'
	];

	protected $dates = [
		'CreatedAt',
		'Appointment',
	];

	protected $fillable = [
		'companyno',
		'WorkOrderNo',
		'CustomerNo',
		'CreatedAt',
		'Appointment',
		'UserName',
		'Operation',
		'Comments',
		'Action',
		'Completed',
		'Priority',
		'DisplayOrder',
		'NewMeterNo',
		'NewMeterSerial',
		'NewMeterMXU',
	];


	public function meters(){
		return $this->hasMany(LocalMeter::class,"workorderno","WorkOrderID");
	}

	public static function fromRemote($remote){
		return new self($remote->toLocal());
	}

	public function updateFields(){
		return [
			'UserName' => $this->UserName,
			'Operation' => $this->Operation,
			'Comments' => $this->Comments,
			'Action' => $this->Action,
			'Completed' => $this->Completed,
			'Priority' => $this->Priority,
			'DisplayOrder' => $this->DisplayOrder,
			'NewMeterNo' => $this->NewMeterNo,
			'NewMeterSerial' => $this->NewMeterSerial,
			'NewMeterMXU' => $this->NewMeterMXU
		];
	}

}
