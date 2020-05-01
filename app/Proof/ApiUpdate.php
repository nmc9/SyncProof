<?php

namespace App\Proof;

use App\Proof\ApiTrait;
use App\Proof\Local;
use App\Proof\LocalCollection;
use App\Proof\ProofTrait;
use App\Proof\Remote;
use App\Proof\RemoteCollection;

class ApiUpdate{

	use ApiTrait;

	public static function update($remote,$id,$update,$lastSync = null){
		return self::updateLocalTruth($remote,$id,$update,$lastSync);
	}

	public static function updateRemoteTruth($remote,$id,$update,$lastSync = null)
	{
		self::toModel($remote);
		$model = $remote::find($id);

		if(self::notChangedSince($model,$lastSync)){
			return self::_update($model,$update);
		}
		//If the remote has been updated then throw away local
		return $model;
	}

	public static function updateLocalTruth($remote,$id,$update,$lastSync = null){
		self::toModel($remote);
		$model = $remote::find($id);

		return self::_update($model,$update);
	}

	public static function updateAlgorithm($remote,$id,$update,$lastSync,$algorithm)
	{
		self::toModel($remote);
		$model = $remote::find($id);

		if(self::notChangedSince($model,$lastSync)){
			$update = $algorithm($model->toLocal(),$update);
		}
		return self::_update($model,$update);
	}

	public static function updateUseLatest($remote,$id,$update,$lastSync,$localUpdate){
		self::toModel($remote);

		$model = $remote::find($id);

		if(self::notChangedSince($model,$lastSync) || $model->updated_at <= $localUpdate){
			return self::_update($model,$update);
		}
		return $model;
	}

	public static function updateUseLatestBulk($remote,$records,$lastSync){
		self::toModel($remote);

		$returns = [];
		foreach ($records as $record) {
			$id = $record["id"];
			$localUpdate = $record["localUpdate"];
			$update = $record["update"];
			$returns[] = self::updateUseLatest($remote,$id,$update,$lastSync,$localUpdate);
		}
		return $returns;
	}

	public static function updateRemoteTruthBulk($remote,$records,$lastSync){
		self::toModel($remote);

		$returns = [];
		foreach ($records as $record) {
			$id = $record["id"];
			$update = $record["update"];
			$returns[] = self::updateRemoteTruth($remote,$id,$update,$lastSync);
		}
		return $returns;
	}

	public static function updateLocalTruthBulk($remote,$records,$lastSync){
		self::toModel($remote);

		$returns = [];
		foreach ($records as $record) {
			$id = $record["id"];
			$update = $record["update"];
			$returns[] = self::updateLocalTruth($remote,$id,$update,$lastSync);
		}
		return $returns;
	}

	public static function updateAlgorithmBulk($remote,$records,$lastSync){
		self::toModel($remote);

		$returns = [];
		foreach ($records as $record) {
			$id = $record["id"];
			$update = $record["update"];
			$algorithm = $record["algorithm"];
			$returns[] = self::updateAlgorithm($remote,$id,$update,$lastSync,$algorithm);
		}
		return $returns;
	}

}
