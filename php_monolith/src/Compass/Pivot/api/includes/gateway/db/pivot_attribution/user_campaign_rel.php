<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с таблицей pivot_attribution . user_campaign_rel
 * @package Compass\Pivot
 */
class Gateway_Db_PivotAttribution_UserCampaignRel extends Gateway_Db_PivotAttribution_Main {

	protected const _TABLE_KEY = "user_campaign_rel";

	/**
	 * Создаем запись
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotAttribution_UserCampaignRel $user_campaign_rel):void {

		$insert = [
			"user_id"       => $user_campaign_rel->user_id,
			"visit_id"      => $user_campaign_rel->visit_id,
			"utm_tag"       => $user_campaign_rel->utm_tag,
			"source_id"     => $user_campaign_rel->source_id,
			"link"          => $user_campaign_rel->link,
			"is_direct_reg" => $user_campaign_rel->is_direct_reg,
			"created_at"    => $user_campaign_rel->created_at,
		];
		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert);
	}

	/**
	 * Получаем запись по PK
	 *
	 * @return Struct_Db_PivotAttribution_UserCampaignRel
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function get(int $user_id):Struct_Db_PivotAttribution_UserCampaignRel {

		// запрос проверен на explain (PRIMARY_KEY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_PivotAttribution_UserCampaignRel::rowToStruct($row);
	}

	/**
	 * Получаем список записей по списку visit_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getListByVisitIdList(array $visit_id_list):array {

		// запрос проверен на explain (visit_id)
		$query = "SELECT * FROM `?p` WHERE `visit_id` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $visit_id_list, count($visit_id_list));

		return array_map(static fn(array $row) => Struct_Db_PivotAttribution_UserCampaignRel::rowToStruct($row), $list);
	}

}