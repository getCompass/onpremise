<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Дата создания скрипта: 05/04/2023
 * Скрипт может быть переиспользован повторно
 *
 * Задача - обновить код страны
 */
class Update_Country_Code {

	protected const _MAX_USER_COUNT = 1000;

	/**
	 * Запускаем работу скрипта
	 */
	public static function run():void {

		if (Type_Script_InputHelper::isDry()) {
			self::debug(yellowText("Скрипт запущен в режиме dry-run"));
		}

		self::_work();
	}

	/**
	 * Работаем
	 *
	 * @return void
	 * @throws \parseException
	 */
	protected static function _work():void {

		$offset = 0;

		do {

			$user_list = Gateway_Db_PivotUser_UserList::getAll(self::_MAX_USER_COUNT, $offset);
			$offset    += self::_MAX_USER_COUNT;

			// оставляем только людей и берем их телефоны
			$filtered_user_list = array_filter($user_list, static fn(Struct_Db_PivotUser_User $user) => $user->npc_type === Type_User_Main::NPC_TYPE_HUMAN);
			$user_security_list = Gateway_Db_PivotUser_UserSecurity::getAllByList(array_column($filtered_user_list, "user_id"));

			foreach ($filtered_user_list as $user) {

				// пытаемся получить номер телефона
				$user_security = $user_security_list[$user->user_id] ?? null;

				// если не нашли - логируем ошибку и идем дальше
				if (is_null($user_security)) {

					self::logImportant("У пользователя $user->user_id нет телефона!!!");
					continue;
				}

				self::_processUser($user, $user_security);
			}
		} while (count($user_list) === self::_MAX_USER_COUNT);

		if (!Type_Script_InputHelper::isDry()) {
			Gateway_Bus_PivotCache::resetUserCache();
		}
	}

	/**
	 * Работаем с пользователем
	 *
	 * @param Struct_Db_PivotUser_User         $user
	 * @param Struct_Db_PivotUser_UserSecurity $user_security
	 *
	 * @return void
	 * @throws \parseException
	 */
	protected static function _processUser(Struct_Db_PivotUser_User $user, Struct_Db_PivotUser_UserSecurity $user_security):void {

		// валидируем номер
		try {
			$phone_number_obj = new \BaseFrame\System\PhoneNumber($user_security->phone_number);
		} catch (\BaseFrame\Exception\Domain\InvalidPhoneNumber) {

			self::logImportant("У пользователя $user->user_id не валидный номер телефона!");
			return;
		}

		// получаем код страны
		$country_code = $phone_number_obj->countryCode();

		// если код страны не нашли - логируем
		if ($country_code === "") {

			self::logImportant("У пользователя $user->user_id по номеру телефона невозможно определить код страны!");
			return;
		}

		self::debug("Меняю пользователю $user->user_id код страны $user->country_code на $country_code");

		// если не dry-run пишем в базу
		if (!Type_Script_InputHelper::isDry()) {

			Gateway_Db_PivotUser_UserList::set($user->user_id, [
				"country_code" => $country_code,
				"updated_at"   => time(),
			]);
		}
	}

	/**
	 * Функция для дебага – только если запущен dry
	 * чтобы не захламлять вывод в момент реального запуска
	 */
	protected static function debug(string $text):void {

		// если не dry, то не дебажим
		if (!Type_Script_InputHelper::isDry()) {
			return;
		}

		console($text);
	}

	/**
	 * Это уже 100% должно быть отображено в любом случае
	 */
	protected static function logImportant(string $text):void {

		Type_System_Admin::log("update_country_code", $text);
		console($text);
	}
}

// запускаем скрипт
Update_Country_Code::run();

if (Type_Script_InputHelper::isDry()) {

	console(greenText("====================================="));
	console(greenText("Скрипт был выполнен в режиме dry-run!"));
	console(greenText("====================================="));
}