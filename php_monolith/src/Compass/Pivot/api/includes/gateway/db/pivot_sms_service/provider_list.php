<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для работы с таблицей pivot_sms_service.provider_pool
 */
class Gateway_Db_PivotSmsService_ProviderList extends Gateway_Db_PivotSmsService_Main {

	protected const _TABLE_KEY = "provider_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Получить список записей по их идентификаторам
	 *
	 * @return Struct_PivotSmsService_Provider[]
	 */
	public static function getListById(array $provider_id_list):array {

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `provider_id` IN (?a) AND `is_deleted` = ?i LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $provider_id_list, 0, count($provider_id_list));
		// бежимся по полученным результатам и собираем объекты
		$output = [];
		foreach ($list as $row) {

			$output[$row["provider_id"]] = new Struct_PivotSmsService_Provider(
				$row["provider_id"],
				$row["is_available"],
				$row["is_deleted"],
				$row["created_at"],
				$row["updated_at"],
				fromJson($row["extra"])
			);
		}

		return $output;
	}

	/**
	 * Получаем провайдер по id
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getById(string $provider_id):Struct_PivotSmsService_Provider {

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `provider_id` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $provider_id, 1);

		if (!isset($row["provider_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return new Struct_PivotSmsService_Provider(
			$row["provider_id"],
			$row["is_available"],
			$row["is_deleted"],
			$row["created_at"],
			$row["updated_at"],
			fromJson($row["extra"])
		);
	}

	/**
	 * Создаем запись провайдера
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_PivotSmsService_Provider $provider):void {

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, [
			"provider_id"  => $provider->provider_id,
			"is_available" => $provider->is_available,
			"is_deleted"   => $provider->is_deleted,
			"created_at"   => $provider->created_at,
			"updated_at"   => $provider->updated_at,
			"extra"        => $provider->extra,
		]);
	}

	/**
	 * Обновляем запись
	 *
	 */
	public static function update(string $provider_id, array $set):int {

		$query = "UPDATE `?p` SET ?u WHERE `provider_id` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $provider_id, 1);
	}
}