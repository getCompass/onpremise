<?php

/** @noinspection PhpIncludeInspection */

declare(strict_types = 1);

namespace Compass\Conversation;

require_once "/app/src/Compass/Conversation/api/includes/type/script/input_parser.php";
require_once "/app/src/Compass/Conversation/api/includes/type/script/input_helper.php";
require_once "/app/src/Modules/vendor/service/php_base_frame/system/functions.php";

$company_url = _getCompanyUrl();
$space_id    = _getSpaceId();
$is_dry      = _getDry();
$file_path   = _getFilePath();

putenv("COMPANY_ID=$space_id");
$_SERVER["HTTP_HOST"] = $company_url;

require_once __DIR__ . "/../../../../../../start.php";
set_time_limit(0);
ini_set("memory_limit", "4096M");
ini_set("display_errors", "1");

/**
 * Экспортируем групповые чаты в файл
 */
class Migration_Export_Groups {

	protected const _CONVERSATIONS_COUNT = 10000;

	protected string $_file_path;

	protected const _GROUP_CONVERSATION_TYPES = [
		CONVERSATION_TYPE_GROUP_DEFAULT,
		CONVERSATION_TYPE_GROUP_GENERAL,
		CONVERSATION_TYPE_SINGLE_NOTES, // заметки будут импортированы как группа
	];

	/**
	 * @long
	 */
	public function run(bool $is_dry, string $file_path):void {

		$this->_file_path = $file_path;
		$file_dir         = dirname($file_path);

		// если директория не существует, то создаём
		if (!is_dir($file_dir)) {
			mkdir($file_dir, recursive: true);
		}

		$offset = 0;
		console("получаем групповые чаты из Compass Saas");
		do {

			// получаем список мет чатов из Compass
			$dynamic_list           = self::_getDynamicConversations($offset);
			$saas_conversation_list = self::_getMetaConversations(array_column($dynamic_list, "conversation_map"));

			$offset += self::_CONVERSATIONS_COUNT;

			if ($is_dry) {

				console("DRY-RUN - будет экспортировано " . count($saas_conversation_list) . " групповых чатов");
				continue;
			}

			// сохраняем чаты в файл
			self::_saveConversationList($saas_conversation_list, $dynamic_list);
			console(greenText("экспортировано " . count($saas_conversation_list) . " групповых чатов"));
		} while (count($dynamic_list) == self::_CONVERSATIONS_COUNT);

		console(greenText("экспортирование групповых чатов завершено"));
	}

	/**
	 * Получаем чаты из Compass
	 *
	 * @param array $conversation_map_list
	 *
	 * @return Struct_Db_CompanyConversation_ConversationMeta[]
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 */
	protected function _getMetaConversations(array $conversation_map_list):array {

		return Gateway_Db_CompanyConversation_ConversationMeta::getFromMigration(
			$conversation_map_list, self::_GROUP_CONVERSATION_TYPES, count($conversation_map_list), true
		);
	}

	/**
	 * Получаем dynamic данные чатов из Compass
	 *
	 * @param int $offset
	 *
	 * @return Struct_Db_CompanyConversation_ConversationDynamic[]
	 */
	protected function _getDynamicConversations(int $offset):array {

		return Gateway_Db_CompanyConversation_ConversationDynamic::getOrdered(self::_CONVERSATIONS_COUNT, $offset);
	}

	/**
	 * Сохраняем список мет диалогов
	 *
	 * @param array                                               $conversation_list
	 * @param Struct_Db_CompanyConversation_ConversationDynamic[] $dynamic_list
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \JsonException
	 * @long
	 */
	protected function _saveConversationList(array $conversation_list, array $dynamic_list):void {

		$file_dir = dirname($this->_file_path);

		$formatted_conversation_list = [];
		foreach ($dynamic_list as $dynamic) {

			if (!isset($conversation_list[$dynamic->conversation_map])) {
				continue;
			}

			/** @var Struct_Db_CompanyConversation_ConversationMeta $meta */
			$meta = $conversation_list[$dynamic->conversation_map];

			// если директория уже существует - пропускам
			if (is_dir($file_dir . "/" . $meta->conversation_map)) {
				continue;
			}

			// для каждого чата создаём директорию для файлов экспорта сообщений
			mkdir($file_dir . "/" . $meta->conversation_map);

			$avatar_file = mb_strlen($meta->avatar_file_map) > 0 ? self::_getAvatarFile($meta->avatar_file_map, $meta->creator_user_id) : null;

			$formatted_conversation_list[] = self::_prepareConversation($meta, $dynamic, $meta->conversation_map, $avatar_file);
		}

		if (count($formatted_conversation_list) < 1) {
			return;
		}

		self::_writeToFile($formatted_conversation_list);
	}

	/**
	 * Получить данные файла-аватара группы
	 *
	 * @return array
	 * @long
	 */
	protected function _getAvatarFile(string $avatar_file_map, int $user_id):array|null {

		$file_key  = \CompassApp\Pack\File::doEncrypt($avatar_file_map);
		$file_list = Gateway_Socket_FileBalancer::getFileList([$avatar_file_map]);

		if (!isset($file_list[0])) {
			return null;
		}

		$file = $file_list[0];

		return [
			"id"                   => $file_key,
			"created"              => $file["created_at"],
			"timestamp"            => $file["created_at"],
			"name"                 => $file["file_name"],
			"title"                => $file["file_name"],
			"mimetype"             => $file["type"],
			"filetype"             => $file["file_extension"],
			"pretty_type"          => "Файл",
			"user"                 => $user_id,
			"size"                 => $file["size_kb"] ?? 0,
			"mode"                 => "hosted",
			"is_external"          => false,
			"external_type"        => "",
			"is_public"            => true,
			"public_url_shared"    => false,
			"display_as_bot"       => false,
			"username"             => "",
			"url_private"          => $file["url"] ?? "",
			"url_private_download" => $file["url"] ?? "",
			"is_avatar"            => true,
		];
	}

	/**
	 * Приводим чат к формату
	 *
	 * @param Struct_Db_CompanyConversation_ConversationMeta    $meta
	 * @param Struct_Db_CompanyConversation_ConversationDynamic $dynamic
	 * @param string                                            $conversation_export_name
	 * @param array|null                                        $avatar_file
	 *
	 * @return array
	 * @long
	 */
	protected function _prepareConversation(Struct_Db_CompanyConversation_ConversationMeta $meta,
							    Struct_Db_CompanyConversation_ConversationDynamic $dynamic,
							    string $conversation_export_name, ?array $avatar_file = null):array {

		$meta_users_joined_at = [];
		foreach ($meta->users as $user_id => $user_item) {
			$meta_users_joined_at[$user_id] = Type_Conversation_Meta_Users::getCreatedAt($user_item);
		}

		$output = [
			"id"            => $meta->conversation_map,
			"name"          => $meta->conversation_name,
			"created"       => $meta->created_at,
			"creator"       => $meta->creator_user_id,
			"is_archived"   => false,
			"is_general"    => $meta->type == CONVERSATION_TYPE_GROUP_GENERAL,
			"members"       => array_map(static fn($user_id) => (string) $user_id, array_keys($meta->users)),
			"topic"         => [],
			"purpose"       => [
				"value"    => Type_Conversation_Meta_Extra::getDescription($meta->extra),
				"creator"  => "",
				"last_set" => 0,
			],
			"compass_extra" => [
				"meta_type"            => $meta->type,
				"meta_owners"          => Type_Conversation_Meta_Users::getOwners($meta->users),
				"allow_status"         => $meta->allow_status,
				"avatar_file_key"      => mb_strlen($meta->avatar_file_map) > 0 ? \CompassApp\Pack\File::doEncrypt($meta->avatar_file_map) : "",
				"meta_extra"           => $meta->extra,
				"conversation_dynamic" => (array) $dynamic,
				"export_file_name"     => $conversation_export_name,
				"meta_users_joined_at" => $meta_users_joined_at,
			],
		];

		if (!is_null($avatar_file)) {
			$output["files"][] = $avatar_file;
		}

		return $output;
	}

	/**
	 * Записываем в файл.
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \JsonException
	 */
	protected function _writeToFile(array $write_content):void {

		if (!file_exists($this->_file_path) && !touch($this->_file_path, 0)) {
			throw new \BaseFrame\Exception\Domain\ReturnFatalException("can't create export file: {$this->_file_path}");
		}

		// получаем содержимое файла
		$file_content = file_get_contents($this->_file_path);

		// докидываем в конец файла массив данных
		if (mb_strlen($file_content) > 0) {
			$file_content = json_decode($file_content, true, 512, JSON_THROW_ON_ERROR);
		} else {
			$file_content = [];
		}

		$file_content = array_merge($file_content, $write_content);

		// дописываем в файл
		file_put_contents($this->_file_path, json_encode($file_content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
	}
}

/**
 * Получаем url компании
 */
function _getCompanyUrl():string {

	try {

		$company_url = Type_Script_InputParser::getArgumentValue("--company_url");
	} catch (\Exception) {

		console("Передайте корректный url компании в которую обращаемся, например: --company_url='c1-d1.company.com'");
		exit;
	}

	return $company_url;
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
 * Получаем флаг is_dry
 */
function _getDry():bool {

	try {

		$is_dry = Type_Script_InputHelper::isDry();
	} catch (\Exception) {

		console("Передайте корректный флаг is_dry");
		exit;
	}

	return $is_dry;
}

/**
 * Получаем путь до файла куда сохраняем данные
 */
function _getFilePath():string {

	try {

		$save_file_path = Type_Script_InputParser::getArgumentValue("--save_file_path", Type_Script_InputParser::TYPE_STRING, __DIR__ . "/groups.json");
	} catch (\Exception) {

		console("Передайте корректный путь для файла экспорта, например: --save_file_path=/app/groups.json");
		exit;
	}

	return $save_file_path;
}

(new Migration_Export_Groups())->run($is_dry, $file_path);