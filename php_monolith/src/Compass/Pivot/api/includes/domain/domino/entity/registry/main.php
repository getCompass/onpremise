<?php

namespace Compass\Pivot;

/**
 * сущность реестра доминошек
 */
class Domain_Domino_Entity_Registry_Main {

	public const TIER_FREE      = 10; // тир домино с бесплатными компаниями
	public const TIER_PREPAYING = 20; // тир домино с предплатящими компаниями
	public const TIER_PAYING    = 30; // тир домино с платящими компаниями

	/**
	 * Возвращает строку с адресом,
	 * по которому можно будет достучаться до компании извне.
	 */
	public static function makeCompanyUrl(int $company_id, Struct_Db_PivotCompanyService_DominoRegistry $domino_row):string {

		$url = Domain_Domino_Entity_Registry_Extra::getUrl($domino_row->extra);
		return "c{$company_id}-{$url}";
	}
}