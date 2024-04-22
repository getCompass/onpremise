<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Search\Query\MatchBuilder;

/**
 * класс для работы с поиском пользователей по full_name
 * @package Compass\Crm
 */
class Domain_User_Entity_Search extends MatchBuilder {

	protected const _MIN_QUERY_LEN = 1;
	protected const _MAX_QUERY_LEN = 255;

	/**
	 * Валидируем поисковый запрос
	 *
	 * @throws ParamException
	 */
	public static function validateSearchQuery(string $full_name):void {

		$query_len = mb_strlen(trim($full_name));
		if ($query_len < self::_MIN_QUERY_LEN || $query_len > self::_MAX_QUERY_LEN) {
			throw new ParamException("incorrect search query");
		}
	}

	/**
	 * Находим совпадения
	 *
	 * @param string $full_name
	 * @param int    $limit
	 *
	 * @return Struct_Db_PivotUser_User[]
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function find(string $full_name, int $limit):array {

		$search_word_list = [];

		// подготавливаем запрос
		$search_query = trim($full_name);

		// чистим всю строку от мусора
		$search_query = self::_clearQuery($search_query);
		$search_query = self::_escapeQuery($search_query);

		// подготавливаем поисковый запрос именно для fulltext mysql
		// отрезаем спецсимволы, на которые ругается mysql, подготовливаем слова для поиска
		$phrase_mod           = trim(preg_replace("/[><()~*:\"&|@+-]/", "", $search_query, -1));
		$raw_search_word_list = preg_split("/[\s,.]/", $phrase_mod, -1, PREG_SPLIT_NO_EMPTY);

		// убираем слишком мелкие куски
		foreach ($raw_search_word_list as $word) {

			if (mb_strlen($word) < self::_MIN_QUERY_LEN) {
				continue;
			}
			$search_word_list[] = $word;
		}

		if ($search_word_list === []) {
			return [];
		}

		return Gateway_Db_PivotUser_UserList::findByFullName($search_word_list, $limit);
	}
}