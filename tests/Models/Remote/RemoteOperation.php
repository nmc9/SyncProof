<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 19 Dec 2018 16:21:44 +0000.
 */

namespace Nmc9\SyncProof\Tests\Models\Remote;

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
 * @package  Nmc9\SyncProof\Tests\Models
 */
class RemoteOperation extends Eloquent
{

	protected $connection = 'remote';
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
