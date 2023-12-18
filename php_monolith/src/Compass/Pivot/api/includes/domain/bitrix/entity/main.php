<?php

namespace Compass\Pivot;

/**
 * класс для основного/общего функционала домена Битрикс
 */
class Domain_Bitrix_Entity_Main {

	/**
	 * Зарепортить проваленную задачу по актуализации пользовательской инфы
	 */
	public static function reportFailedUserInfoTask(int $task_id, int $user_id):void {

		Gateway_Db_PivotBusiness_BitrixUserInfoFailedTaskList::insert($task_id, $user_id, time());
	}

	/**
	 * Пометить решенной проваленную задачу по актуализации пользовательской инфы
	 */
	public static function solveFailedUserInfoTask(int $task_id):void {

		Gateway_Db_PivotBusiness_BitrixUserInfoFailedTaskList::deleteList([$task_id]);
	}

	/**
	 * Конвертируем флаг "is_direct_reg" 0/1 в формат привычный Битриксу
	 *
	 * @return string
	 */
	public static function convertIsDirectRegToBitrixValueFormat(int $is_direct_reg):string {

		return $is_direct_reg == 1 ? BITRIX_DEAL_REG_TYPE_VALUE__PRIMARY : BITRIX_DEAL_REG_TYPE_VALUE__SECONDARY;
	}
}