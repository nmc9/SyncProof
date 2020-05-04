<?php

namespace Nmc9\SyncProof\Tests\Models\Local;

use Illuminate\Database\Eloquent\Model as Eloquent;
/**
 * Class Inventory
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $companyno
 *
 * @package  Nmc9\SyncProof\Tests\Models
 */
class LocalInventory extends Eloquent
{

	protected $connection = 'local';
	protected $table = 'inventory';

		public $timestamps = false;


	protected $casts = [
		'companyno' => 'int'
	];

	protected $fillable = [
		'name',
		'description',
		'companyno'
	];
	public static function fromRemote($remote){
		return new self($remote->toLocal());
	}
}
