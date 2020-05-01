<?php

namespace App\Models\Remote;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
class RemoteInventoryNew extends Eloquent
{
	use SoftDeletes;

	protected $connection = 'remote';
	protected $table = 'inventory_new';

	protected $casts = [
		'id_local' => 'int',
		'WorkOrderNo' => 'int',
		'companyno' => 'int',
		'quantity' => 'int'
	];

	protected $fillable = [
		'id_local',
		'WorkOrderNo',
		'companyno',
		'quantity',
		'name',
		'description'

	];

	public function toLocal(){
		return [
			'id' => $this->id,
			'id_local' => $this->id_local,
			'WorkOrderNo' => $this->WorkOrderNo,
			'companyno' => $this->companyno,
			'quantity' => $this->quantity,
			'name' => $this->name,
			'description' => $this->description,
		];
	}
}
