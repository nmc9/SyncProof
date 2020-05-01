<?php

namespace App\Proof;

use App\Models\Local\LocalInventoryNew;
use App\Models\Local\LocalWorkordersNew;
use App\Proof\ApiTrait;
use App\Proof\Local;
use App\Proof\LocalCollection;
use App\Proof\ProofTrait;
use App\Proof\Remote;
use App\Proof\RemoteCollection;

class ApiWrite{

	use ApiTrait;
	public static function insert($remoteClass,$data = []){
		return new Remote(factory($remoteClass)->create($data));
	}

	public static function insertBulk($remoteClass,$data = [[]]){
		$remote = [];
		foreach ($data as $_data) {
			// dd($_data);
			$remote[] = factory($remoteClass)->create($_data);
		}
		return new RemoteCollection(collect($remote));
	}

	public static function insertOnly($remoteClass,Local $local,$otherServerData = [], $keep = [],$remove = []){
		$create = array_merge($local->record()->insertFeilds(),$otherServerData);
		$create = array_diff_key($create,$remove);

		$specialCase = self::specialCase($remoteClass,$create);
		if($specialCase){
			return $specialCase;
		}

		$remote = factory($remoteClass)->create($create);

		$remote = self::setRemoteLocalId($remote,$local->record());
		$remote = self::keep($remote,$local->record(),$keep);


		$local->record()->delete();
		return new Remote($remote);
	}

	public static function specialCase($remoteClass,$create){
		if(isset($create[self::toModel($remoteClass)->getKeyName()])){
			//do update instead
			$remote = $remoteClass::withTrashed()->find($create[self::toModel($remoteClass)->getKeyName()]);
			// dd($remote);
			$remote->restore();
			$remote->update($create);
			$remote->save();
			return $remote;
		}
		return false;
	}

	public static function insertOnlyBulk($remoteClass,LocalCollection $local,$otherServerData = [],$keep = []){
		$news = [];
		foreach($local->records() as $_local){
			$create = array_merge($_local->insertFeilds(),$otherServerData);

			$remote = factory($remoteClass)->create($_local->insertFeilds());
			$remote = self::setRemoteLocalId($remote,$_local);;
			$news[] = self::keep($remote,$_local,$keep);

			$_local->delete();
		}
		return new RemoteCollection(collect($news));
	}

	public static function insertOnlyBulkDistinct($remoteClass,array $local,$keep = []){
		$news = [];
		foreach($local as $_local){
			$remote = factory($remoteClass)->create($_local->record()->insertFeilds());
			$remote = self::setRemoteLocalId($remote,$_local->record());
			$news[] = self::keep($remote,$_local->record(),$keep);

			$_local->delete();
		}
		return new RemoteCollection(collect($news));
	}

	private static function keep($remote,$local,$keep = []){
		foreach ($keep as $value) {
			$remote->$value = $local->$value;
		}
		return $remote;
	}

	private static function setRemoteLocalId($remote,$local){
		$remote->{$local->primaryKey} = $local->{$local->primaryKey};
		return $remote;
	}

	public static function delete($remote,$id){
		self::toModel($remote);
		$model = $remote::find($id);
		return $model ? $model->delete($id) : 404;
	}

	public static function deleteBulk($remote,array $ids){
		self::toModel($remote);
		$result = [];
		foreach ($ids as $id) {
			$model = $remote::find($id);
			$result[] = $model ? $model->delete($id) : 404;
		}
	}

}
