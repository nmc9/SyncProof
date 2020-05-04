<?php

namespace Nmc9\SyncProof\Tests\Models\Local;


use Illuminate\Database\Eloquent\Model as Eloquent;
/**
 * Class Company
 *
 * @property int $companyid
 * @property int $companyno
 * @property string $name
 *
 * @package  Nmc9\SyncProof\Tests\Models
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
