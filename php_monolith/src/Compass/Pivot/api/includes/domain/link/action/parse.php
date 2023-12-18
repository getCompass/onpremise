<?php

namespace Compass\Pivot;

/**
 * Класс для парсинга ссылок приглашений
 */
class Domain_Link_Action_Parse {

	/**
	 * Выполняем
	 * @throws Domain_Link_Exception_LinkNotFound
	 */
	public static function do(string $link):array {

		// убираем пробелы по краям
		$link = Domain_Link_Entity_Parser::trimLink($link);

		// парсим ссылку
		[$link_list] = Domain_Link_Entity_Parser::doFindAllLinks($link);

		// если больше одной ссылки роняем ошибку, такое не обрабатываем
		if (count($link_list) > 1) {
			throw new Domain_Link_Exception_LinkNotFound();
		}

		// если нашли только одну ссылку пробуем проверить откуда она
		if (count($link_list) == 1) {

			// достаем ссылку
			$parsed_link = array_pop($link_list);

			// если это join ссылка
			if (Domain_Company_Entity_JoinLink_Main::isJoinLink($parsed_link)) {

				return ["join_link", $parsed_link, ""];
			}
		}

		return ["", [], ""];
	}
}