<?php

namespace Nmc9\SyncProof\Tests\Models\Remote;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
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
class RemoteInventory extends Eloquent
{
	use SoftDeletes;

	protected $connection = 'remote';
	protected $table = 'inventory';

	protected $casts = [
		'companyno' => 'int'
	];

	protected $fillable = [
		'name',
		'description',
		'companyno'
	];

	public function toLocal(){
		return [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'companyno' => $this->companyno,
			// "created_at" => $this->created_at,
			// "updated_at" => $this->updated_at,
			// "deleted_at" => $this->deleted_at,
		];
	}
}
