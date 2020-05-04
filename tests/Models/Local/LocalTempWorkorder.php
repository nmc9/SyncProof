<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 19 Dec 2018 16:21:44 +0000.
 */

namespace Nmc9\SyncProof\Tests\Models\Local;

use Nmc9\SyncProof\Tests\Models\Local\LocalMeter;
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
 * @package  Nmc9\SyncProof\Tests\Models
 */
class LocalTempWorkorder extends Eloquent
{
	protected $connection = 'local';
	public $primaryKey = 'local_WorkOrderID';
	protected $table = 'temp_workorders';

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
		'local_WorkOrderNo',
		'local_CustomerNo',
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

	public function insertFeilds(){
		return [
			'companyno' => $this->companyno,
			'WorkOrderNo' => $this->local_WorkOrderNo,
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
}
