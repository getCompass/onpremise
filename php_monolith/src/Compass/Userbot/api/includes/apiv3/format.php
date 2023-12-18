<?php

namespace Compass\Userbot;

/**
 * класс для форматирование сущностей под формат API
 *
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго отдачей результата в API
 * для форматирования стандартных сущностей
 *
 */
class Apiv3_Format {

	public const SINGLE_CONVERSATION_TYPE = "single";
	public const GROUP_CONVERSATION_TYPE  = "group";

	protected const _FILE_UPLOAD_POSTFIX = "/api/userbot/files/upload";

	/**
	 * форматируем данные пользователя
	 */
	public static function userInfo(array $user_info):array {

		return [
			"user_id"         => (int) $user_info["user_id"],
			"user_name"       => (string) $user_info["full_name"],
			"avatar_file_url" => (string) $user_info["avatar_file_url"],
		];
	}

	/**
	 * форматируем список данных по пользователям
	 */
	public static function userInfoList(array $user_info_list):array {

		$formatted_user_info_list = [];
		foreach ($user_info_list as $user_info) {
			$formatted_user_info_list[] = (object) self::userInfo($user_info);
		}

		return $formatted_user_info_list;
	}

	/**
	 * форматируем данные группы
	 */
	public static function groupInfo(array $group_info):array {

		return [
			"group_id"        => (string) $group_info["conversation_key"],
			"name"            => (string) $group_info["group_name"],
			"avatar_file_url" => (string) $group_info["avatar_file_url"],
		];
	}

	/**
	 * форматируем список данных групп
	 */
	public static function groupInfoList(array $group_info_list):array {

		$formatted_group_info_list = [];
		foreach ($group_info_list as $group_info) {
			$formatted_group_info_list[] = (object) self::groupInfo($group_info);
		}

		return $formatted_group_info_list;
	}

	/**
	 * форматируем URL файловой ноды
	 */
	public static function fileNodeUrl(string $node_url):string {

		$node_url = rtrim($node_url, "/");
		$node_url .= self::_FILE_UPLOAD_POSTFIX;

		return $node_url;
	}

	/**
	 * форматируем команды бота
	 */
	public static function userbotCommandList(array $command_list):array {

		return (array) array_map(
			static fn(string $command) => (string) $command,
			$command_list
		);
	}
}
