<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Search\Exception\ExecutionException;

require_once "/app/src/Compass/Conversation/api/includes/type/script/input_parser.php";
require_once "/app/src/Compass/Conversation/api/includes/type/script/input_helper.php";
require_once "/app/src/Modules/vendor/autoload.php";

$local_manticore_host = _getLocalManticoreHost();
$local_manticore_port = _getLocalManticorePort();
$company_url          = _getCompanyUrl();
$space_id             = _getSpaceId();

putenv("COMPANY_ID=$space_id");
$_SERVER["HTTP_HOST"] = $company_url;

require_once __DIR__ . "/../../../../../../start.php";
set_time_limit(0);
ini_set("memory_limit", "4096M");
ini_set("display_errors", "1");

/**
 * Пишем сообщения в компас и в таблицу связи
 */
class Migration_Import_Messages {

	protected const  _RAW_MESSAGES_TABLE        = "raw_message";
	protected const  _BOUND_CONVERSATIONS_TABLE = "bound_conversation";
	protected const  _BOUND_MESSAGES_TABLE      = "bound_conversation_message";
	protected const  _MESSAGES_COUNT            = 1000000;
	protected const  _CONVERSATIONS_COUNT       = 1000000;
	protected string $_local_manticore_host;
	protected int    $_local_manticore_port;

	/**
	 * Запускаем скрипт
	 */
	public function run(string $local_manticore_host, int $local_manticore_port):void {

		$this->_local_manticore_host = $local_manticore_host;
		$this->_local_manticore_port = $local_manticore_port;

		$conversation_map_list = self::_getBoundConversations();
		$conversations_count   = count($conversation_map_list);
		console("Всего чатов для импорта: {$conversations_count}");

		$conversations_counter = 0;
		foreach ($conversation_map_list as $conversation) {

			$conversation_map = $conversation["conversation_map"];

			try {
				$meta_row = Type_Conversation_Meta::get($conversation_map);
			} catch (ParamException) {
				console("Не смогли найти чат: {$conversation_map} - (проверьте был ли импорт)");
				Type_System_Admin::log("migration-conversation-error", "uniq: {$conversation["uniq"]} conversation_map: {$conversation["conversation_map"]}");
				continue;
			}

			// получаем сообщения с чата
			$raw_message_list = self::_getRawMessages($conversation_map);

			$messages_count = count($raw_message_list);
			console("Добавляем сообщения для чата: {$conversation_map}");
			console("Всего сообщений в чате: {$messages_count}");

			// формируем
			$raw_message_list = self::_prepareMessageList($raw_message_list);

			// тут все сообщения будут от лица пользователя 1
			$message_list = self::_prepareRawMessageList($raw_message_list, $meta_row);

			// отправляем сообщения
			$chunked_message_list = array_chunk($message_list, 50);

			foreach ($chunked_message_list as $message_list) {

				try {
					$added_message_list = Helper_Conversations::addMessageListByMigration(
						$conversation_map,
						$message_list,
						$meta_row["users"],
					);
				} catch (\Exception) {

					$count = count($message_list);
					console("Не смогли добавить {$count} сообщения чат {$conversation_map}");
					$message_list_json = toJson($message_list);
					Type_System_Admin::log("migration-conversation-message-error", "conversation uniq: {$conversation["uniq"]} message_list: {$message_list_json}");

					continue;
				}

				$raw_bound_message_rel_list = self::_generateRawBoundMessageRelList($raw_message_list, $added_message_list);
				self::_insertBoundConversationMessages($raw_bound_message_rel_list);
			}

			$conversations_counter++;
			console("Добавили сообщения для {$conversations_counter}/{$conversations_count} чатов");
		}
	}

	/**
	 * Получаем список чатов с bound таблицы
	 *
	 * @throws ExecutionException
	 */
	protected function _getBoundConversations():array {

		$query = "SELECT * FROM ?t WHERE `id`>?i LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::_search()->select($query, [self::_BOUND_CONVERSATIONS_TABLE, 0, self::_CONVERSATIONS_COUNT, 0, self::_CONVERSATIONS_COUNT]);
	}

	/**
	 * Получаем сообщения с raw таблицы
	 *
	 * @throws ExecutionException
	 */
	protected function _getRawMessages(string $group):array {

		$query = "SELECT * FROM ?t WHERE `group`=?s ORDER BY `id` ASC LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::_search()->select($query, [self::_RAW_MESSAGES_TABLE, $group, self::_MESSAGES_COUNT, 0, self::_MESSAGES_COUNT]);
	}

	/**
	 * Формируем временный массив сообщений
	 *
	 * @throws ParseFatalException
	 * @throws cs_PlatformNotFound
	 */
	protected function _prepareRawMessageList(array $client_message_list, array $meta_row):array {

		$raw_message_list = [];
		$platform         = Type_Api_Platform::getPlatform();

		foreach ($client_message_list as $v) {

			$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $v["text"]);

			if ($v["file_map"] !== false) {

				$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeFile(
					$v["sender_user_id"], $v["text"], $v["client_message_id"], $v["file_map"], $v["file_name"], $platform
				);
			} else {
				$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeText($v["sender_user_id"], $v["text"], $v["client_message_id"], $platform);
			}

			$message["created_at"] = $v["created_at"];
			$raw_message_list[]    = Type_Conversation_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);
		}

		return $raw_message_list;
	}

	/**
	 * Форматируем массив сообщений
	 */
	protected function _prepareMessageList(array $raw_message_list):array {

		$message_list = [];
		foreach ($raw_message_list as $v) {

			$data      = fromJson($v["data"]);
			$file_map  = false;
			$file_name = false;

			if (isset($data["text"])) {

				$text = $data["text"];
			} else {

				$text      = "";
				$file_map  = $data["file_map"];
				$file_name = $data["file_name"];
			}

			$message_list[] = [
				"client_message_id" => generateUUID(), // пока беру свйо рандомный
				"text"              => $text,
				"sender_user_id"    => $v["sender_user_id"],
				"created_at"        => $v["created_at"],
				"file_map"          => $file_map, // сюда чет докидываем если файл
				"file_name"         => $file_name, // сюда чет докидываем если файл
				"uniq"              => $v["uniq"], // сюда чет докидываем если файл
			];
		}

		return $message_list;
	}

	/**
	 * Форматируем массив для записи связи отправленных сообщений с raw
	 */
	protected function _generateRawBoundMessageRelList($raw_message_list, $added_message_list):array {

		$raw_bound_message_rel_list = [];
		foreach ($raw_message_list as $raw_message) {

			foreach ($added_message_list as $added_message) {

				if ($raw_message["client_message_id"] == $added_message["client_message_id"]) {

					$block_id                     = \CompassApp\Pack\Message\Conversation::getBlockId($added_message["message_map"]);
					$raw_bound_message_rel_list[] = [
						"uniq"        => $raw_message["uniq"],
						"message_map" => $added_message["message_map"],
						"block_id"    => $block_id,
					];
				}
			}
		}

		return $raw_bound_message_rel_list;
	}

	/**
	 * Пишем в bound таблицу все отравленные сообщения в чат
	 */
	protected function _insertBoundConversationMessages(array $raw_bound_message_rel_list):void {

		self::_search()->insert(self::_BOUND_MESSAGES_TABLE, $raw_bound_message_rel_list);
	}

	/**
	 * Розетка для временного поднятого контейнера manticore
	 */
	public function _search():\BaseFrame\Search\Manticore {

		$conf = [
			"host" => $this->_local_manticore_host,
			"port" => $this->_local_manticore_port,
		];

		// получаем конфиг с базой данных
		return \BaseFrame\Search\Provider::instance()->connect(new \BaseFrame\Search\Config\Connection(...$conf));
	}
}

/**
 * Получаем хост для manticore
 */
function _getLocalManticoreHost():string {

	try {

		$local_manticore_host = Type_Script_InputParser::getArgumentValue("--local_manticore_host");
	} catch (\Exception) {

		console("Передайте корректный адрес хоста временного manticore контейнера, например: --local_manticore_host='82.148.27.130'");
		exit;
	}

	return $local_manticore_host;
}

/**
 * Получаем порт для manticore
 */
function _getLocalManticorePort():int {

	try {

		$local_manticore_port = Type_Script_InputParser::getArgumentValue("--local_manticore_port", Type_Script_InputParser::TYPE_INT);
	} catch (\Exception) {

		console("Передайте корректный адрес порта временного manticore контейнера, например: --local_manticore_port=9306");
		exit;
	}

	return $local_manticore_port;
}

/**
 * Получаем url компании
 */
function _getCompanyUrl():string {

	try {

		$company_url = Type_Script_InputParser::getArgumentValue("--company_url");
	} catch (\Exception) {

		console("Передайте корректный url компании в которую обращаемся, например: --company_url='c1-bob.nikitak.backend-local.apitest.team'");
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

(new Migration_Import_Messages())->run($local_manticore_host, $local_manticore_port);