<?php

namespace Nmc9\SyncProof\Proof;

use Nmc9\SyncProof\ProofApiTrait;

class Api{

	use ApiTrait;

	public static function select($remote,$companyno,$lastSync = null){
		return self::selectAll($remote,$companyno,$lastSync)->first();
	}

	public static function selectAll($remote,$companyno,$lastSync = null){
		self::toModel($remote);

		$query = $remote->withTrashed()->where("companyno",$companyno);
		if($lastSync != null){
			$query->where('updated_at',">",$lastSync);
		}
		return $query->get();
	}

	public static function selectAllNotTrashed($remote,$companyno,$lastSync = null){
		self::toModel($remote);
		$query = $remote->where("companyno",$companyno);
		if($lastSync != null){
			$query->where('updated_at',">",$lastSync);
		}
		return $query->get();
	}

	// public static function selectAllJoin($remote,$join,$companyno,$lastSync = null){
	// 	self::toModel($remote);
	// 	$query = $remote->with([$join => function ($query) {
	// 		$query->withTrashed();
	// 	}])
	// 	->withTrashed()->where("companyno",$companyno);
	// 	if($lastSync != null){
	// 		$query->where('updated_at',">",$lastSync);
	// 	}
	// 	return $query->get();
	// }

	public static function selectNoCompany($remote,$lastSync = null){
		return self::selectAllNoCompany($remote,$lastSync)->first();
	}

	public static function selectAllNoCompany($remote,$lastSync = null){
		self::toModel($remote);

		$query = $remote->withTrashed();
		if($lastSync != null){
			$query->where('updated_at',">",$lastSync);
		}
		return $query->get();
	}

	// public static function selectAllJoinNoCompany($remote,$join,$lastSync = null){
	// 	self::toModel($remote);
	// 	$query = $remote->with([$join => function ($query) {
	// 		$query->withTrashed();
	// 	}])
	// 	->withTrashed();
	// 	if($lastSync != null){
	// 		$query->where('updated_at',">",$lastSync);
	// 	}
	// 	return $query->get();
	// }

	public static function selectAllNotTrashedNoCompany($remote,$companyno,$lastSync = null){
		self::toModel($remote);
		$query = $remote;
		if($lastSync != null){
			$query->where('updated_at',">",$lastSync);
		}
		return $query->get();
	}

}
