<?php

namespace Nmc9\SyncProof\Proof;

use Nmc9\SyncProof\ProofLocal;
use Nmc9\SyncProof\ProofProofTrait;

class Remote{

	use ProofTrait;
	private $record;
	public function __construct($record)
	{
		$this->record = $record;
	}

	public function cloneTo($model){
		self::toModel($model);
		$localRecord = $model::fromRemote($this->record);
		$localRecord->save();
		return new Local($localRecord);
	}

	public static function insertClone($modelRemote,$modelLocal,$data){
		$record = factory($modelRemote)->create($data);
		$remote = new self($record);
		$remote->cloneTo($modelLocal);
		return $remote;
	}

	public static function insert($modelClass,$data = []){
		$record = factory($modelClass)->create($data);
		return new self($record);
	}

	public function update($update){
		foreach ($update as $key => $value) {
			$this->record[$key] = $value;
		}
		$this->record->save();
		return $this;
	}

	public function delete(){
		$this->record->delete();
		return $this;
	}

	public function record()
	{
		return $this->record;
	}
}
