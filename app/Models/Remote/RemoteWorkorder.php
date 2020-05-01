<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 19 Dec 2018 16:21:44 +0000.
 */

namespace App\Models\Remote;

use App\Models\Remote\RemoteCustomer;
use App\Models\Remote\RemoteMeter;
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
class RemoteWorkorder extends Eloquent
{
	use SoftDeletes;

	protected $connection = 'remote';
	protected $primaryKey = 'WorkOrderID';
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
		'UpdatedAt'
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

	public function toLocal(){
		return [
			'WorkOrderID' => $this->WorkOrderID,
			'companyno' => $this->companyno,
			'WorkOrderNo' => $this->WorkOrderNo,
			'CustomerNo' => $this->CustomerNo,
			'CreatedAt' => $this->CreatedAt,
			'Appointment' => $this->Appointment,
			'UserName' => $this->UserName,
			'Operation' => $this->Operation,
			'Comments' => $this->Comments,
			'Action' => $this->Action,
			'Completed' => $this->Completed,
			'Priority' => $this->Priority,
			'DisplayOrder' => $this->DisplayOrder,
			'NewMeterNo' => $this->NewMeterNo,
			'NewMeterSerial' => $this->NewMeterSerial,
			'NewMeterMXU' => $this->NewMeterMXU,
		];
	}

	public function meters(){
		return $this->hasMany(RemoteMeter::class,"workorderno","WorkOrderID");
	}
}
