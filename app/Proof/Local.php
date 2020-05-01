<?php

namespace App\Proof;

use App\Proof\ProofTrait;
use Illuminate\Support\Collection;

class Local{

	use ProofTrait;
	protected $result;
	public function __construct($result){
		$this->result = $result;
	}

	public static function find($model,$index){
		self::toModel($model);
		return new self($model->find($index));
	}

	public static function first($model){
		self::toModel($model);
		return new self($model->first());
	}

	public function downloadJoin($model,$join,$joinClass){
		if($this->result instanceof Collection){
			throw new \Exception("Use Local Collection", 1);
		}
		return $this->downloadJoinSingle($model,$join,$joinClass);
	}

	public function download($model){
		if($this->result instanceof Collection){
			throw new \Exception("Use Local Collection");
		}
		return $this->downloadSingle($model);
	}

	// public function downloadJoinSingle($model,$join,$joinClass){
	// 	self::toModel($model);
	// 	self::toModel($joinClass);

	// 	$parent = $this->result;
	// 	if($parent->deleted_at){
	// 		$this->_delete($model,$parent);
	// 	}else{
	// 		$this->_updateOrCreate($model,$parent);
	// 	}
	// 	foreach ($parent->$join()->get() as $child) {
	// 		if($child->deleted_at){
	// 			$this->_delete($joinClass,$child);
	// 		}else{
	// 			$this->_updateOrCreate($joinClass,$child);
	// 		}
	// 	}
	// }

	public function downloadSingle($model){
		self::toModel($model);
		return $this->returnedLogic($model,$this->result);
	}

	public function record(){
		return $this->result;
	}

	public function update($update){
		foreach ($update as $key => $value) {
			$this->result[$key] = $value;
		}
		$this->result->save();
		return $this;
	}

	public function delete(){
		$this->result->delete();
		return $this;
	}

	public function forceDelete(){
		$this->result->forceDelete();
		return $this;
	}

	public static function insert($modelClass,$data = []){
		$record = factory($modelClass)->create($data);
		return new self($record);
	}




}
