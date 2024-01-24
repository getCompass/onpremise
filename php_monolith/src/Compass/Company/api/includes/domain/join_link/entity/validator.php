<?php

namespace Compass\Company;

/**
 * Класс для валидации сущностей инвайтов-ссылок
 */
class Domain_JoinLink_Entity_Validator {

	public const METHOD_VERSION_PARAMS = [
		METHOD_VERSION_1 => [
			"lives_day_count" => 30,
			"can_use_count"   => 100,
		],
		METHOD_VERSION_2 => [
			"lives_day_count"  => 14,
			"lives_hour_count" => 12,
			"can_use_count"    => 100,
		],
		METHOD_VERSION_3 => [
			"lives_day_count"  => 30,
			"lives_hour_count" => 1,
			"can_use_count"    => 200,
		],
		METHOD_VERSION_4 => [
			"lives_day_count"  => 30,
			"lives_hour_count" => 1,
			"can_use_count"    => 5000,
		],
	];

	/**.
	 * Выбрасываем исключение если передан некорректный link_id
	 *
	 * @throws cs_IncorrectJoinLinkUniq
	 */
	public static function assertJoinLinkUniq(string $join_link_uniq):void {

		if (isEmptyString($join_link_uniq)) {
			throw new cs_IncorrectJoinLinkUniq();
		}
	}

	/**
	 * проверяем параметр "сколько дней может жить инвайт" для разовой ссылки
	 *
	 * @throws cs_IncorrectLivesDayCount
	 */
	public static function assertValidLivesDayCount(int|false $lives_day_count, int $method_version):void {

		if ($lives_day_count === false) {
			return;
		}

		if ($lives_day_count < 0 || $lives_day_count > self::METHOD_VERSION_PARAMS[$method_version]["lives_day_count"]) {
			throw new cs_IncorrectLivesDayCount();
		}
	}

	/**
	 * проверяем параметр "сколько часов может жить инвайт"
	 *
	 * @throws cs_IncorrectLivesHourCount
	 */
	public static function assertValidLivesHourCount(int|false $lives_hour_count, int $method_version):void {

		if ($lives_hour_count === false) {
			return;
		}

		if ($lives_hour_count < 1 || $lives_hour_count > self::METHOD_VERSION_PARAMS[$method_version]["lives_hour_count"]) {
			throw new cs_IncorrectLivesHourCount();
		}
	}

	/**
	 * проверяем параметр "количество использования ссылки"
	 *
	 * @throws cs_IncorrectCanUseCount
	 */
	public static function assertValidCanUseCount(int $can_use_count, int $method_version):void {

		if ($can_use_count < 0 || $can_use_count > self::METHOD_VERSION_PARAMS[$method_version]["can_use_count"]) {
			throw new cs_IncorrectCanUseCount();
		}
	}

	/**
	 * проверяем, что передали корректное значение entry_option
	 *
	 * @throws Domain_JoinLink_Exception_IncorrectEntryOption
	 */
	public static function assertEntryOption(int $entry_option):void {

		if (!in_array($entry_option, Domain_JoinLink_Entity_Main::AVAILABLE_ENTRY_OPTION_LIST)) {
			throw new Domain_JoinLink_Exception_IncorrectEntryOption();
		}
	}
}
