<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для валидации данных вводимых пользователем о компании
 */
class Domain_Company_Entity_Validator {

	public const MAX_NAME_LENGTH = 40; // максимальн длина имени компании

	public const AVATAR_COLOR_GREEN_ID  = 1;
	public const AVATAR_COLOR_SEA_ID    = 2;
	public const AVATAR_COLOR_BLUE_ID   = 3;
	public const AVATAR_COLOR_YELLOW_ID = 4;
	public const AVATAR_COLOR_ORANGE_ID = 5;
	public const AVATAR_COLOR_RED_ID    = 6;
	public const AVATAR_COLOR_METAL_ID  = 7;
	public const AVATAR_COLOR_BLACK_ID  = 8;

	// список доступных цветов аватара
	public const ALLOW_AVATAR_COLOR_ID_LIST = [
		self::AVATAR_COLOR_GREEN_ID,
		self::AVATAR_COLOR_SEA_ID,
		self::AVATAR_COLOR_BLUE_ID,
		self::AVATAR_COLOR_YELLOW_ID,
		self::AVATAR_COLOR_ORANGE_ID,
		self::AVATAR_COLOR_RED_ID,
		self::AVATAR_COLOR_METAL_ID,
		self::AVATAR_COLOR_BLACK_ID,
	];

	/**
	 * Выбрасываем исключение если передано неккоректный имя компании
	 *
	 * @throws cs_CompanyIncorrectName
	 */
	public static function assertIncorrectName(string $name):void {

		if (mb_strlen($name) < 1 || mb_strlen($name) > self::MAX_NAME_LENGTH) {
			throw new cs_CompanyIncorrectName();
		}
	}

	/**
	 * Выбрасываем исключение если передан неккоректный avatar_color_id
	 *
	 * @throws cs_CompanyIncorrectAvatarColorId
	 */
	public static function assertIncorrectAvatarColorId(int $avatar_color_id):void {

		if (!in_array($avatar_color_id, Domain_Company_Entity_Validator::ALLOW_AVATAR_COLOR_ID_LIST)) {
			throw new cs_CompanyIncorrectAvatarColorId();
		}
	}

	/**
	 * выбрасывает исключение, если компании не существует
	 *
	 * @throws cs_CompanyIsNotExist|\parseException
	 */
	public static function assertCompanyExist():void {

		// пробуем по сокету пропинговать компанию и узнаем работает ли она
		try {
			$exist = Domain_Company_Scenario_Socket::checkIfExistCurrent();
		} catch (ReturnFatalException) {
			$exist = false;
		}

		if (!$exist) {
			throw new cs_CompanyIsNotExist();
		}
	}
}
