<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 19 Dec 2018 16:21:44 +0000.
 */

namespace Nmc9\SyncProof\Tests\Models\Remote;

 use Illuminate\Database\Eloquent\Model as Eloquent;
/**
 * Class Image
 *
 * @property int $imageid
 * @property int $imageid_local
 * @property string $filename
 * @property string $uri
 * @property int $companyno
 * @property int $WorkOrderNo
 *
 * @package  Nmc9\SyncProof\Tests\Models
 */
class RemoteImage extends Eloquent
{

	protected $connection = 'remote';
	protected $table = 'image';
	protected $primaryKey = 'imageid';
	public $timestamps = false;

	protected $casts = [
		'imageid_local' => 'int',
		'companyno' => 'int',
		'WorkOrderNo' => 'int'
	];

	protected $fillable = [
		'imageid_local',
		'filename',
		'uri',
		'companyno',
		'WorkOrderNo'
	];

	protected $writeable = "*";
}
