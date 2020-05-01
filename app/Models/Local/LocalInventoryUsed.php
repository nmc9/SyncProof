<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 19 Dec 2018 16:21:44 +0000.
 */

namespace App\Models\Local;

use App\Models\utils\WorkorderModelTrait;

use Illuminate\Database\Eloquent\Model as Eloquent;
/**
 * Class InventoryUsed
 *
 * @property int $id
 * @property int $id_local
 * @property int $inventory_id
 * @property int $WorkOrderNo
 * @property int $companyno
 * @property int $quantity
 *
 * @package App\Models
 */
class LocalInventoryUsed extends Eloquent
{
	use WorkorderModelTrait;

	protected $connection = 'local';
	protected $table = 'inventory_used';
	public $timestamps = false;

	protected $casts = [
		'id_local' => 'int',
		'inventory_id' => 'int',
		'WorkOrderNo' => 'int',
		'companyno' => 'int',
		'quantity' => 'int'

	];

	protected $fillable = [
		'id_local',
		'inventory_id',
		'WorkOrderNo',
		'companyno',
		'quantity',
		'unit',
	];

	protected $writeable = "*";


}
