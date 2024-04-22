<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для сущности прав пользователя в онпреме
 */
class Domain_User_Entity_Permissions {

	/**
	 * Права пользователя в онпреме
	 */
	public const DEFAULT              = 0;
	public const SERVER_ADMINISTRATOR = 1 << 0; // администратор сервера
	public const ACCOUNTANT           = 1 << 1; // бухгалтер

	// разрешенные права для установки и получения
	public const ALLOWED_PERMISSION_LIST = [
		self::SERVER_ADMINISTRATOR,
		self::ACCOUNTANT,
	];

	public const PERMISSIONS_OUTPUT_SCHEMA = [
		self::SERVER_ADMINISTRATOR => "premise_administrator",
		self::ACCOUNTANT           => "premise_accountant",
	];

	/**
	 * Добавить права в маску
	 *
	 * @param int   $permission_mask
	 * @param array $permission_list
	 *
	 * @return int
	 */
	public static function addPermissionListToMask(int $permission_mask, array $permission_list):int {

		foreach ($permission_list as $permission) {

			// выполняем побитовое ИЛИ для включения новой группы в маску групп
			$permission_mask = $permission_mask | $permission;
		}

		return $permission_mask;
	}

	/**
	 * Удалить список прав из маски
	 *
	 * @param int   $permission_mask
	 * @param array $permission_list
	 *
	 * @return int
	 */
	public static function removePermissionListFromMask(int $permission_mask, array $permission_list):int {

		foreach ($permission_list as $permission) {

			if (self::hasPermission($permission_mask, $permission)) {

				// Выполняем исключающее ИЛИ для исключения группы из маски групп
				$permission_mask ^= $permission;
			}
		}

		return $permission_mask;
	}

	/**
	 * Получить список групп пользователя
	 *
	 * @param int $permission_mask
	 *
	 * @return array
	 */
	public static function getPermissionList(int $permission_mask):array {

		$permission_list = [];

		// превращаем двоичное число в строку, чтобы узнать группы пользователя
		$binary = strrev(decbin($permission_mask));

		for ($i = 0; $i < strlen($binary); $i++) {

			if ((int) $binary[$i]) {

				$permission_list[] = self::transformForMask($i);
			}
		}

		return $permission_list;
	}

	/**
	 * Проверить, есть ли у участника права
	 */
	public static function hasPermission(int $permission_mask, int $permission):bool {

		return $permission_mask & $permission;
	}

	/**
	 * Трансформировать id группы, чтобы его можно было использовать в маске
	 *
	 * @param int $permission
	 *
	 * @return int
	 */
	public static function transformForMask(int $permission):int {

		return 1 << $permission;
	}

	/**
	 * Форматируем в список из запроса клиента
	 *
	 * @param array $permissions
	 *
	 * @return array
	 */
	public static function formatToList(array $permissions):array {

		$enabled_permission_list  = [];
		$disabled_permission_list = [];

		// переворачиваем массив со схемой прав, чтобы получить значения для маски
		$flipped_permission_schema = array_flip(self::PERMISSIONS_OUTPUT_SCHEMA);

		foreach ($permissions as $permission => $value) {

			// если такого права в схеме нет, пропускаем
			if (!isset($flipped_permission_schema[$permission])) {
				continue;
			}

			// если право включено - добавляем в маску
			if ($value === 1) {

				$enabled_permission_list[] = $flipped_permission_schema[$permission];
				continue;
			}

			if ($value === 0) {
				$disabled_permission_list[] = $flipped_permission_schema[$permission];
			}
		}

		return [$enabled_permission_list, $disabled_permission_list];
	}

	/**
	 * Получить список прав участника для клиента
	 *
	 * @throws ParseFatalException
	 */
	public static function formatToOutput(int $permission_mask):array {

		$permissions     = [];
		$permission_list = self::getPermissionList($permission_mask);

		// включаем разрешенные права
		foreach ($permission_list as $permission) {

			if (!isset(self::PERMISSIONS_OUTPUT_SCHEMA[$permission]) && $permission) {
				throw new ParseFatalException("there is no format output for permission {$permission}");
			}

			$permissions[] = self::PERMISSIONS_OUTPUT_SCHEMA[$permission];
		}

		return $permissions;
	}

	/**
	 * Проверяем, что у пользователя имеются права
	 *
	 * @throws Domain_User_Exception_UserHaveNotPermissions
	 */
	public static function assertIfNotHavePermissions(bool $has_premise_permissions):void {

		if (!$has_premise_permissions) {
			throw new Domain_User_Exception_UserHaveNotPermissions("user have not permissions");
		}
	}

	/**
	 * Проверяем, что у пользователя есть права администратора сервера
	 *
	 * @throws Domain_User_Exception_UserHaveNotPermissions
	 */
	public static function assertIfNotAdministrator(bool $has_premise_permissions, int $permissions):void {

		self::assertIfNotHavePermissions($has_premise_permissions);

		if (!self::hasPermission($permissions, self::SERVER_ADMINISTRATOR)) {
			throw new Domain_User_Exception_UserHaveNotPermissions("user have not permissions");
		}
	}
}