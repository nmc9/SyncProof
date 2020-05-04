<?php

namespace Nmc9\SyncProof\Proof;

use Nmc9\SyncProof\ProofProofTrait;
use Illuminate\Support\Collection;

class LocalCollection{

	use ProofTrait;

	protected $resultCollection;
	public function __construct(Collection $resultCollection){
		$this->resultCollection = $resultCollection;
	}

	public function download($model){
		self::toModel($model);
		foreach ($this->resultCollection as $result) {
			$this->returnedLogic($model,$result);
		}
	}

	public function downloadIndex($index,$model){
		self::toModel($model);
		$this->returnedLogic($model,$this->resultCollection[$index]);
	}

	public static function insert($modelClass,$count,$data){
		$result = factory($modelClass,$count)->create($data);
		return new self($result);
	}

	public static function insertBulk($modelClass,array $data){
		$result = [];
		foreach ($data as $_data) {
			$result[] = factory($modelClass)->create($_data);
		}
		return new self(collect($result));
	}

	public function add($modelClass,$data){
		$newResult = factory($modelClass)->create($data);
		$this->resultCollection->put($newResult);
		return $newResult;
	}

	public function update($index,array $update){
		foreach($update as $key => $value) {
			$this->resultCollection[$index][$key] = $value;
		}
		$this->resultCollection[$index]->save();
		return $this;
	}



	public function updateAll($update){
		foreach ($this->resultCollection as $result) {
			foreach ($update as $key => $value) {
				$result[$key] = $value;
			}
			$result->save();
		}
	}

	public function delete($index){
		$this->resultCollection[$index]->delete();
		unset($this->resultCollection[$index]);
	}

	public function deleteAll(){
		$count = count($this->resultCollection);
		for($i = 0; $i < $count; $i++) {
			$this->resultCollection[$i]->delete();
			unset($this->resultCollection[$i]);
		}
	}


	public function records()
	{
		return $this->resultCollection;
	}

	public function firstRecord(){
		return $this->resultCollection->first();
	}

	public function index($index){
		return $this->resultCollection[$index];
	}

	public function indexObject($index){
		return new Remote($this->resultCollection[$index]);
	}

	public function firstObject(){
		return new Remote($this->resultCollection[0]);
	}


}
