<?php

namespace Compass\Company;

/**
 * Класс для работы с логикой разрешений ссылок-приглашений
 */
class Domain_JoinLink_Entity_Permission {

	/**
	 * доступно ли создание запрошенного типа ссылки
	 *
	 * @throws cs_IncorrectType
	 */
	public static function assertAllowedTypeForCreate(int $type):void {

		switch ($type) {

			case Domain_JoinLink_Entity_Main::TYPE_MAIN:
			case Domain_JoinLink_Entity_Main::TYPE_REGULAR:
			case Domain_JoinLink_Entity_Main::TYPE_SINGLE:
				return;

			default:
				throw new cs_IncorrectType();
		}
	}

	/**
	 * доступно ли удаление
	 *
	 * @throws cs_IncorrectType
	 */
	public static function assertAllowedTypeForDelete(int $type):void {

		switch ($type) {

			case Domain_JoinLink_Entity_Main::TYPE_MAIN:
			case Domain_JoinLink_Entity_Main::TYPE_REGULAR:
			case Domain_JoinLink_Entity_Main::TYPE_SINGLE:
				return;

			default:
				throw new cs_IncorrectType();
		}
	}

	/**
	 * доступно ли редактирование
	 *
	 * @throws cs_IncorrectType
	 */
	public static function assertAllowedTypeForEdit(int $type):void {

		switch ($type) {

			case Domain_JoinLink_Entity_Main::TYPE_MAIN:
			case Domain_JoinLink_Entity_Main::TYPE_REGULAR:
			case Domain_JoinLink_Entity_Main::TYPE_SINGLE:
				return;

			default:
				throw new cs_IncorrectType();
		}
	}
}
