<?php

namespace Compass\Pivot;

/**
 * класс для хранения связей «пользователь» <-> «сущности Bitrix»
 */
class Domain_Bitrix_Entity_UserRel {

	/**
	 * Создаем связь
	 *
	 * @return Struct_Db_PivotBusiness_BitrixUserEntityRel
	 * @throws \queryException
	 */
	public static function create(int $user_id, array $bitrix_entity_list):Struct_Db_PivotBusiness_BitrixUserEntityRel {

		$struct = new Struct_Db_PivotBusiness_BitrixUserEntityRel(
			$user_id, time(), 0, $bitrix_entity_list
		);
		Gateway_Db_PivotBusiness_BitrixUserEntityRel::insert($struct);

		return $struct;
	}

	/**
	 * Получаем сущность
	 *
	 * @return Struct_Db_PivotBusiness_BitrixUserEntityRel
	 */
	public static function get(int $user_id):Struct_Db_PivotBusiness_BitrixUserEntityRel {

		try {

			$output = Gateway_Db_PivotBusiness_BitrixUserEntityRel::getOne($user_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new Domain_Bitrix_Exception_UserRelNotFound("not found");
		}

		return $output;
	}

	/**
	 * Возвращаем айтем сохраненной сущности, которую создали в Битриксе для пользователя
	 *
	 * @return array|null
	 */
	public static function getEntityItemByType(Struct_Db_PivotBusiness_BitrixUserEntityRel $bitrix_user_entity_rel, string $entity_type):null|array {

		// пробегаемся по всем сохраненным сущностям
		foreach ($bitrix_user_entity_rel->bitrix_entity_list as $entity_item) {

			// если не та сущность которую ищем
			if ($entity_item["entity_type"] !== $entity_type) {
				continue;
			}

			// нашли – возвращаем
			return $entity_item;
		}

		// возвращаем это, если не нашли
		return null;
	}
}