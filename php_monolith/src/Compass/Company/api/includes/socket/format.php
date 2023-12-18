<?php

namespace Compass\Company;

/**
 * Класс для форматирование сущностей
 */
class Socket_Format {

	/**
	 * Форматируем данные о списке пользователей, сгрупированном по ролям
	 */
	public static function memberRoleList(array $member_list):array {

		$output = [];

		foreach (\CompassApp\Domain\Member\Entity\Member::ALLOWED_FOR_GET_LIST as $role_name) {
			$output[$role_name] = [];
		}

		foreach ($member_list as $member) {
			$output[$member->role][] = (int) $member->user_id;
		}

		return $output;
	}
}