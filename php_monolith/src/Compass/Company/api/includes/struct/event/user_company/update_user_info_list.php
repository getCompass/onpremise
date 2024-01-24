<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура события «обновления информации списка пользователей»
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_UserCompany_UpdateUserInfoList extends Struct_Default {

	/** @var array */
	public array $user_info_update_list;

	/**
	 * Статический конструктор.
	 *
	 * @param array $user_info_update_list
	 *
	 * @return Struct_Event_UserCompany_UpdateUserInfoList
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function build(array $user_info_update_list):static {

		return new static([
			"user_info_update_list" => $user_info_update_list,
		]);
	}
}
