<?php

namespace Nmc9\SyncProof\Tests\Models\Local;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
class LocalInventoryNew extends Eloquent
{

	protected $connection = 'local';
	protected $table = 'inventory_new';
	public $primaryKey = "id_local";

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
	public static function fromRemote($remote){
		return new self($remote->toLocal());
	}

	public function insertFeilds(){
		return [
			'id_local' => $this->id_local,
			'WorkOrderNo' => $this->WorkOrderNo,
			'companyno' => $this->companyno,
			'quantity' => $this->quantity,
			'name' => $this->name,
			'description' => $this->description
		];
	}

}
