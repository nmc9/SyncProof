<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 19 Dec 2018 16:21:44 +0000.
 */

namespace Nmc9\SyncProof\Tests\Models\Local;

use Nmc9\SyncProof\Tests\Models\Local\LocalWorkorder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Class Meter
 *
 * @property int $meterid
 * @property int $companyno
 * @property int $workorderno
 * @property string $meterno
 * @property string $MXU
 * @property int $prevread
 * @property \Carbon\Carbon $prevreaddt
 * @property int $currread
 * @property \Carbon\Carbon $currreaddt
 * @property string $notes
 * @property string $swapmeter
 * @property string $new_MXU
 * @property string $new_serial_num
 * @property bool $updated
 * @property int $meter_digits
 * @property int $multiplier
 *
 * @package  Nmc9\SyncProof\Tests\Models
 */
class LocalTempMeter extends Eloquent
{
	use SoftDeletes;
	protected $connection = 'local';
	public $primaryKey = 'local_meterid';
	protected $table = 'temp_meters';

	protected $casts = [
		'companyno' => 'int',
		'workorderno' => 'int',
		'prevread' => 'int',
		'currread' => 'int',
		'updated' => 'bool',
		'meter_digits' => 'int',
		'multiplier' => 'int'
	];

	protected $dates = [
		'prevreaddt',
		'currreaddt'
	];

	protected $fillable = [
		'companyno',
		'local_workorderno',
		'local_workorderno',
		'meterno',
		'MXU',
		'prevread',
		'prevreaddt',
		'currread',
		'currreaddt',
		'notes',
		'swapmeter',
		'new_MXU',
		'new_serial_num',
		'updated',
		'meter_digits',
		'multiplier'
	];

	public function insertFeilds(){
		return [
			'companyno' => $this->companyno,
			'workorderno' => $this->workorderno,
			'meterno' => $this->meterno,
			'MXU' => $this->MXU,
			'prevread' => $this->prevread,
			'prevreaddt' => $this->prevreaddt,
			'currread' => $this->currread,
			'currreaddt' => $this->currreaddt,
			'notes' => $this->notes,
			'swapmeter' => $this->swapmeter,
			'new_MXU' => $this->new_MXU,
			'new_serial_num' => $this->new_serial_num,
			'updated' => $this->updated,
			'meter_digits' => $this->meter_digits,
			'multiplier' => $this->multiplier
		];
	}


	public function workorder(){
		return $this->belongsTo(LocalWorkorder::class,"workorderno","WorkOrderID");
	}

	public static function fromRemote($remote){
		return new self($remote->toLocal());
	}

	public function getNotesAttribute($value)
	{
		return trim($value);
	}

	public function getMeternoAttribute($value)
	{
		return trim($value);
	}

	public function getMXUAttribute($value)
	{
		//TODO: This doesn't fire for some reason
		return trim($value);
	}

	public function getSwapmeterAttribute($value)
	{
		return trim($value);
	}

	public function getNewSerialNumAttribute($value)
	{
		return trim($value);
	}

}
