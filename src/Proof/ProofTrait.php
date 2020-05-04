<?php

namespace Nmc9\SyncProof\Proof;

use Illuminate\Database\Eloquent\SoftDeletes;

trait ProofTrait{


	protected function _delete($model,$result){
		$primaryKey = $model->getKeyName();
		if(in_array(SoftDeletes::class, class_uses($model))){
			$record = $model->withTrashed()->find($result->$primaryKey);
		}else{
			$record = $model->find($result->$primaryKey);
		}
		if($record === null){
			return null;
		}
		return $record->forceDelete();
	}


	protected function _updateOrCreate($model,$result){
		$primaryKey = $model->getKeyName();
		$local = $model->find($result->$primaryKey);
		if($local){
			$local->update($result->toLocal());
			$local->save();
		}else{
			$local = new $model($result->toLocal());
			//Force the primary key to be updated. Not set fillable //
			$local->$primaryKey = $result->toLocal()[$primaryKey];
			$local->save();
		}
		return $local;
	}

	protected function returnedLogic($model,$result){
		if($result == null){
			return null;
		}
		if($result->deleted_at){
			// dd($result);
			return $this->_delete($model,$result);
		}
		return $this->_updateOrCreate($model,$result);
	}

	public static function toModel(&$model){
		if(class_exists($model)){
			$model = (new $model);
		}
		return $model;
	}
}
