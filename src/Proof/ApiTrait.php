<?php

namespace Nmc9\SyncProof\Proof;

trait ApiTrait{

	public static function toModel(&$model){
		if(class_exists($model)){
			$model = (new $model);
		}
		return $model;
	}

	public static function notChangedSince($model,$date){
		return $model->updated_at <= $date;
	}

	public static function _update($model,$update){
		if($model){
			$model->update($update);
			return $model;
		}
		return 404;
	}
}
