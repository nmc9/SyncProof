<?php

namespace App\Proof;

use App\Proof\LocalCollection;
use App\Proof\ProofTrait;
use App\Proof\Remote;
use Illuminate\Support\Collection;

class RemoteCollection{

	use ProofTrait;
	private $record;
	public function __construct(Collection $record)
	{
		$this->recordCollection = $record;
	}

	public function cloneAllTo($model){
		self::toModel($model);
		$local = [];
		foreach ($this->recordCollection as $record) {
			$localRecord = $model::fromRemote($record);
			$localRecord->save();
			$local[] = $localRecord;
		}
		return new LocalCollection(collect($local));
	}

	public function cloneTo($model,$index){
		self::toModel($model);
		$localRecord = $model::fromRemote($this->recordCollection[$index]);
		$localRecord->save();
		return $localRecord;
	}

	public static function insertClone($remoteClass,$localClass,$count,$data){
		$record = factory($remoteClass,$count)->create($data);
		$RemoteCollection = new self(collect($record));
		$RemoteCollection->cloneAllTo($localClass);
		return $RemoteCollection;
	}

	public static function insert($modelClass,$count,$data){
		$record = factory($modelClass,$count)->create($data);
		return new self(collect($record));
	}

	public function add($modelClass,$data){
		$newRecord = factory($modelClass)->create($data);
		$this->recordCollection->put($newRecord);
		return $newRecord;
	}

	public function update($index,array $update){
		foreach($update as $key => $value) {
			$this->recordCollection[$index][$key] = $value;
		}
		$this->recordCollection[$index]->save();
		return $this->recordCollection;
	}

	public function updateAll($update){
		foreach ($this->recordCollection as $record) {
			foreach ($update as $key => $value) {
				$record[$key] = $value;
			}
			$record->save();
		}
	}

	public function delete($index){
		$this->recordCollection[$index]->delete();
	}

	public function deleteAll(){
		foreach ($this->recordCollection as $record) {
			$record->delete();
		}
	}

	public function records()
	{
		return $this->recordCollection;
	}

	public function firstRecord(){
		return $this->recordCollection->first();
	}

	public function index($index){
		return $this->recordCollection[$index];
	}

	public function indexObject($index){
		return new Remote($this->recordCollection[$index]);
	}

	public function firstObject(){
		return new Remote($this->recordCollection[0]);
	}
}
