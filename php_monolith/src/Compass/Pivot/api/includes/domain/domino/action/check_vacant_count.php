<?php

namespace Compass\Pivot;

/**
 * Action для проверки количества вакантных компаний
 */
class Domain_Domino_Action_CheckVacantCount {

	// сколько должно быть вакантных компаний
	protected const _NEED_VACANT_COUNT = 100;

	/**
	 * Проверяем количество вакантных компаний
	 *
	 * @return void
	 */
	public static function do():void {

		$vacant_count = Gateway_Db_PivotCompanyService_CompanyInitRegistry::countVacant();

		if ($vacant_count < self::_NEED_VACANT_COUNT) {

			$text = self::_prepareText($vacant_count);
			self::_notify($text);
		}
	}

	/**
	 * Готовим текст
	 *
	 * @param int $vacant_count
	 *
	 * @return string
	 */
	protected static function _prepareText(int $vacant_count):string {

		// header
		return ":warning:" . SERVER_NAME . " has {$vacant_count} vacant companies\n";
	}

	/**
	 * Уведомляем
	 *
	 * @param string $text
	 *
	 * @return void
	 */
	protected static function _notify(string $text):void {

		Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, $text);
	}

}