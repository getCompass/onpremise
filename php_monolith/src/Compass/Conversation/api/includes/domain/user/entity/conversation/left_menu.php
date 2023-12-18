<?php

namespace Compass\Conversation;

/**
 * Класс-сущность для левого меню
 */
class Domain_User_Entity_Conversation_LeftMenu {

	// начальная временная метка 01.10.2021 00:00:00. Точность - 0.1 секунда
	protected const _START_VERSION_TIMESTAMP = 16330320000;

	/**
	 * Сгенерировать версию
	 */
	public static function generateVersion(int $previous_version):int {

		$version = self::getVersionByCurrentTime();

		// проверяем чтобы при генерации версии она не оказалась меньше либо равна прошлой версии
		if ($version <= $previous_version) {
			$version = $previous_version + 1;
		}

		return $version;
	}

	/**
	 * получить версию по текущему времени (не использовать на прямую для генерации version, для этого использовать функцию generateVersion)
	 */
	public static function getVersionByCurrentTime():int {

		return ((int) (microtime(true) * 10)) - self::_START_VERSION_TIMESTAMP;
	}

	/**
	 * непрочитан ли итем левого меню
	 */
	public static function isUnread(array $left_menu_item):bool {

		if ($left_menu_item["unread_count"] > 0 || $left_menu_item["is_have_notice"] == 1) {
			return true;
		}

		return false;
	}
}