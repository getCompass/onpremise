<?php declare(strict_types = 1);

namespace Compass\Thread;

use BaseFrame\Search\Exception\ExecutionException;

require_once "/app/src/Compass/Thread/api/includes/type/script/input_parser.php";
require_once "/app/src/Compass/Thread/api/includes/type/script/input_helper.php";
require_once "/app/src/Modules/vendor/autoload.php";

$local_manticore_host = _getLocalManticoreHost();
$local_manticore_port = _getLocalManticorePort();
$company_url          = _getCompanyUrl();
$space_id             = _getSpaceId();
$is_dry               = _getDry();

putenv("COMPANY_ID=$space_id");
$_SERVER["HTTP_HOST"] = $company_url;

require_once __DIR__ . "/../../../../../../start.php";
set_time_limit(0);
ini_set("memory_limit", "4096M");
ini_set("display_errors", "1");

/**
 * Пишем сообщение.
 */
class Migration_Import_Comments {

	protected const  _RAW_MESSAGES_TABLE   = "raw_comment";
	protected const  _BOUND_THREADS_TABLE  = "bound_thread";
	protected const  _BOUND_MESSAGES_TABLE = "bound_thread_message";
	protected const  _MESSAGES_COUNT       = 100000;
	protected const  _THREADS_COUNT        = 1000000;
	protected string $_local_manticore_host;
	protected int    $_local_manticore_port;

	/**
	 * @long
	 */
	public function run(string $local_manticore_host, int $local_manticore_port, bool $is_dry):void {

		$this->_local_manticore_host = $local_manticore_host;
		$this->_local_manticore_port = $local_manticore_port;

		$thread_list   = self::_getBoundThreads();
		$threads_count = count($thread_list);
		console("Всего тредов для импорта: {$threads_count}");
		if ($threads_count == self::_THREADS_COUNT) {
			console(yellowText("тредов для импорта может превышать лимит"));
		}

		$threads_counter = 0;
		foreach ($thread_list as $thread) {

			$thread_map = $thread["thread_map"];
			$meta_row   = Type_Thread_Meta::getOne($thread_map);

			// получаем сообщения с треда
			$raw_message_list = self::_getRawMessages($thread["thread_map"]);

			$messages_count = count($raw_message_list);
			console("Добавляем комментарии для треда: {$thread_map}");
			console("Всего комментариев в треде: {$messages_count}");

			// формируем сообщение, которое создаём
			$raw_message_list = self::_prepareMessageList($raw_message_list);
			$message_list     = self::_prepareRawMessageList($raw_message_list, $meta_row);

			// шлем обычные сообщения
			$chunked_message_list = array_chunk($message_list, 50);

			foreach ($chunked_message_list as $message_list) {

				if ($is_dry) {

					console("DRY-RUN - добавляем " . count($message_list) . "в чат {$thread_map}");
					continue;
				}

				$added_message_list = [];
				foreach ($message_list as $message) {

					$data                 = Domain_Thread_Action_Message_AddList::do($thread_map, $meta_row, [$message], is_silent: true);
					$added_message_list[] = $data["message_list"];
				}

				$raw_bound_message_rel_list = self::_generateRawBoundMessageRelList($raw_message_list, $added_message_list);
				self::_insertBoundThreadMessages($raw_bound_message_rel_list);
			}

			$threads_counter++;
			console("Добавили комментарии для {$threads_counter}/{$threads_count} тредов");
		}
	}

	/**
	 * Получаем список тредов с bound таблицы
	 *
	 * @throws ExecutionException
	 */
	protected function _getBoundThreads():array {

		$query = "SELECT * FROM ?t WHERE `id` > ?i LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::_search()->select($query, [self::_BOUND_THREADS_TABLE, 0, self::_THREADS_COUNT, 0, self::_THREADS_COUNT]);
	}

	/**
	 * Получаем сообщения с raw таблицы
	 *
	 * @throws ExecutionException
	 */
	protected function _getRawMessages(string $thread_map):array {

		$query = "SELECT * FROM ?t WHERE `thread_map` = ?s ORDER BY `id` ASC LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::_search()->select($query, [self::_RAW_MESSAGES_TABLE, $thread_map, self::_MESSAGES_COUNT, 0, self::_MESSAGES_COUNT]);
	}

	/**
	 * Формируем временный массив сообщений
	 *
	 * @param array $client_message_list
	 * @param array $meta_row
	 *
	 * @return array
	 * @throws \parseException
	 * @throws cs_PlatformNotFound
	 */
	protected function _prepareRawMessageList(array $client_message_list, array $meta_row):array {

		$raw_message_list = [];
		$platform         = Type_Api_Platform::getPlatform();

		foreach ($client_message_list as $v) {

			// получаем упомянутых пользователей
			$mention_user_id_list = Helper_Threads::getMentionUserIdListFromText($meta_row, $v["text"]);
			$mentioned_users[]    = $mention_user_id_list;

			$handler_class = Type_Thread_Message_Main::getLastVersionHandler();

			// если передали файл, то создаем сообщение типа "файл", иначе - тип "текст"
			if ($v["file_map"] !== false) {
				$message = $handler_class::makeFile($v["sender_user_id"], $v["text"], $v["client_message_id"], $v["file_map"], $v["file_name"], $platform);
			} else {
				$message = $handler_class::makeText($v["sender_user_id"], $v["text"], $v["client_message_id"], [], $platform);
			}

			// добавляем список упомянутых к сообщению
			$message["created_at"] = $v["created_at"];
			$raw_message_list[]    = Type_Thread_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);
		}

		return $raw_message_list;
	}

	/**
	 * Форматируем массив сообщений
	 * @long
	 */
	protected function _prepareMessageList(array $raw_message_list):array {

		$message_list = [];
		foreach ($raw_message_list as $v) {

			if ($v["sender_user_id"] < 1) {

				console(redText("пропустили сообщение id: " . $v["id"] . "так как sender_user_id < 1"));
				continue;
			}

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
				"client_message_id" => generateUUID(),
				"text"              => $text,
				"sender_user_id"    => $v["sender_user_id"],
				"created_at"        => $v["created_at"],
				"file_map"          => $file_map,
				"file_name"         => $file_name,
				"uniq"              => $v["uniq"],
			];
		}

		return $message_list;
	}

	/**
	 * Форматируем массив для записи связи отправленных сообщений с raw
	 */
	protected function _generateRawBoundMessageRelList(array $raw_message_list, array $added_message_list):array {

		$raw_bound_message_rel_list = [];
		foreach ($raw_message_list as $raw_message) {

			foreach ($added_message_list as $added_messages) {

				foreach ($added_messages as $added_message) {

					if ($raw_message["client_message_id"] == $added_message["client_message_id"]) {

						$block_id                     = \CompassApp\Pack\Message\Thread::getBlockId($added_message["message_map"]);
						$raw_bound_message_rel_list[] = [
							"uniq"        => $raw_message["uniq"],
							"message_map" => $added_message["message_map"],
							"block_id"    => $block_id,
						];
					}
				}
			}
		}

		return $raw_bound_message_rel_list;
	}

	/**
	 * Пишем в bound таблицу все отравленные сообщения в тред
	 */
	protected function _insertBoundThreadMessages(array $raw_bound_message_rel_list):void {

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

(new Migration_Import_Comments())->run($local_manticore_host, $local_manticore_port, $is_dry);