<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

// получаем id пространства с которого достаем пользователей
$space_id = _getSpaceId();

// получаем путь до файла куда сохраняем пользователей
$file_path = _getFilePath();

/**
 * Скрипт для экспорта пользователей в файл
 */
class Migration_Export_Users {

	protected const _USER_COUNT_PER_CHUNK = 100;

	/**
	 * выполняем работу
	 */
	public static function doWork(int $space_id, string $file_path):void {

		// создаем файл если его нет
		if (!file_exists($file_path)) {
			file_put_contents($file_path, json_encode([], JSON_THROW_ON_ERROR));
		}

		// получаем список участников пространства
		$space_user_id_list = Gateway_Db_PivotCompany_CompanyUserList::getFullUserIdList($space_id);

		// разбиваем на чанки
		$space_user_id_list = array_chunk($space_user_id_list, self::_USER_COUNT_PER_CHUNK);

		// получаем пространство
		$company = Domain_Company_Entity_Company::get($space_id);

		// выход, если пространство не активно
		if (!Domain_Company_Entity_Company::isCompanyActive($company)) {
			return;
		}
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		// проходимся по всем чанкам
		foreach ($space_user_id_list as $user_id_list) {

			// получаем список пользователей
			$user_list = Gateway_Db_PivotUser_UserList::getByUserIdList($user_id_list);

			// проходимся по каждому пользователю
			$formatted_user_list = [];
			foreach ($user_list as $user) {

				// работаем с пользователем
				$formatted_user_list[] = self::_doWorkWithUser($user, $space_id, $company->domino_id, $private_key);
			}

			self::_writeToFile($file_path, $formatted_user_list);
		}

		$user = Gateway_Db_PivotUser_UserList::getOne(REMIND_BOT_USER_ID);

		$file_url = "";
		$file_key = "";
		if ($user->avatar_file_map !== "") {

			$file_list = Domain_Partner_Scenario_Socket::getFileByKeyList([Type_Pack_File::doEncrypt($user->avatar_file_map)]);
			$file_url  = $file_list[0]["data"]["image_version_list"][0]["url"];
			$file_key  = Type_Pack_File::doEncrypt($user->avatar_file_map);
		}

		$email  = "";
		$status = "";

		$formatted_user_list = [self::_prepareSlackFormat($user, $space_id, $file_key, $file_url, $email, $status)];
		self::_writeToFile($file_path, $formatted_user_list);
	}

	/**
	 * Запись данных в файл
	 *
	 * @throws \JsonException
	 */
	protected static function _writeToFile(string $file_path, array $formatted_user_list):void {

		// получаем содержимое файла
		$file_content = file_get_contents($file_path);

		// докидываем в конец файла массив пользователей
		$file_content = json_decode($file_content, true, 512, JSON_THROW_ON_ERROR);
		$file_content = array_merge($file_content, $formatted_user_list);

		// дописываем в файл
		file_put_contents($file_path, json_encode($file_content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
	}

	/**
	 * Работаем с пользователем
	 *
	 * @throws ParseFatalException
	 * @throws \paramException
	 */
	protected static function _doWorkWithUser(Struct_Db_PivotUser_User $user, int $space_id, string $domino_id, string $private_key):array {

		// получаем информацию по аватарке
		$file_url = "";
		$file_key = "";

		if ($user->avatar_file_map !== "") {
			$file_list = Domain_Partner_Scenario_Socket::getFileByKeyList([Type_Pack_File::doEncrypt($user->avatar_file_map)]);
			$file_url  = $file_list[0]["data"]["image_version_list"][0]["url"];
			$file_key  = Type_Pack_File::doEncrypt($user->avatar_file_map);
		}

		// проверяем что почта установлена
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user->user_id);
			$email         = $user_security->mail;
		} catch (\cs_RowIsEmpty) {
			$email = "";
		}

		try {
			[$_, $_, $_, $_, $status] = Gateway_Socket_Company::getUserInfo($user->user_id, $space_id, $domino_id, $private_key);
		} catch (cs_UserNotFound) {
			return [];
		}

		// приводим к нужному формату как в слаке
		return self::_prepareSlackFormat($user, $space_id, $file_key, $file_url, $email, $status);
	}

	/**
	 * приводим к нужному формату как в слаке
	 */
	protected static function _prepareSlackFormat(Struct_Db_PivotUser_User $user, int $space_id, string $file_key, string $file_url, string $email, string $status):array {

		return [
			"id"            => $user->user_id,
			"team_id"       => $space_id,
			"name"          => $user->full_name,
			"deleted"       => Type_User_Main::isDisabledProfile($user->extra),
			"real_name"     => $user->full_name,
			"profile"       => [
				"real_name"      => $user->full_name,
				"display_name"   => $user->full_name,
				"avatar_hash"    => $file_key,
				"image_original" => $file_url,
				"email"          => $email,
				"team"           => $space_id,

				// это не номер телефона, это id пользователя в пространстве куда идет импорт
				// но называем его "phone" для мигратора
				"phone"          => $status,
			],
			"is_bot"        => !Type_User_Main::isHuman($user->npc_type),
			"updated"       => $user->updated_at,
			"compass_extra" => [
				"is_remind_bot" => $user->user_id == REMIND_BOT_USER_ID,
			],
		];
	}
}

/**
 * Получаем id пространства
 */
function _getSpaceId():int {

	try {

		$space_id = Type_Script_InputParser::getArgumentValue("--space_id", Type_Script_InputParser::TYPE_INT);
	} catch (\Exception) {

		console("Передайте корректный id пространства, например: --space_id=1");
		exit;
	}

	return $space_id;
}

/**
 * Получаем путь до файла куда сохраняем пользователей
 */
function _getFilePath():string {

	try {

		$save_file_path = Type_Script_InputParser::getArgumentValue("--save_file_path", Type_Script_InputParser::TYPE_STRING, __DIR__ . "/users.json");

		// Если передан путь к директории
		if (is_dir($save_file_path)) {
			$save_file_path = rtrim($save_file_path, "/") . "/users.json";
		}

		// Создаем директорию, если она не существует
		$dir = dirname($save_file_path);
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
	} catch (\Exception) {

		console("Передайте путь до файла, например: --save_file_path=app/users.json");
		exit;
	}

	return $save_file_path;
}

// запускаем скрипт
Migration_Export_Users::doWork($space_id, $file_path);
