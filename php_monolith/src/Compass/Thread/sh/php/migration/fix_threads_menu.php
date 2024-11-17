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
 * Фиксим треды из слака
 */
class Migration_Fix_Threads_Menu {

	protected const _BOUND_THREAD_TABLE_NAME = "bound_thread";
	protected const _RAW_COMMENT_TABLE_NAME  = "raw_comment";
	protected const _THREADS_COUNT           = 10000;
	protected string $_local_manticore_host;
	protected int    $_local_manticore_port;

	/**
	 * @long
	 */
	public function run(string $local_manticore_host, int $local_manticore_port, bool $is_dry):void {

		$this->_local_manticore_host = $local_manticore_host;
		$this->_local_manticore_port = $local_manticore_port;

		console(greenText("Исправление тредов запущено"));

		$offset = 0;
		do {

			// получаем треды из таблицы bound_thread
			$slack_thread_list = self::_getBoundThreads($offset);
			if (count($slack_thread_list) == 0) {
				break;
			}

			$offset += self::_THREADS_COUNT;

			// получаем треды из баз Compass
			$thread_list = self::_getThreads($slack_thread_list);

			if ($is_dry) {

				console("DRY-RUN - будет исправлено " . count($thread_list) . " тредов");
				continue;
			}

			console("Исправляем " . count($thread_list) . " тредов");

			// исправляем меню тредов для импортируемых тредов
			self::_fixThreadMenu($thread_list);
		} while (count($slack_thread_list) > 0);

		console(greenText("Исправление тредов закончено"));
	}

	/**
	 * Получить треды из bound_thread.
	 *
	 * @param int $offset
	 *
	 * @return array
	 * @throws ExecutionException
	 */
	protected function _getBoundThreads(int $offset):array {

		$query = "SELECT * FROM ?t WHERE `id` > ?i ORDER BY `id` ASC LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::search()->select($query, [self::_BOUND_THREAD_TABLE_NAME, 0, self::_THREADS_COUNT, $offset, self::_THREADS_COUNT + $offset]);
	}

	/**
	 * Получить комментарии тредов из raw таблицы.
	 *
	 * @param int $offset
	 *
	 * @return array
	 * @throws ExecutionException
	 */
	protected function _getRawThreadComments(string $thread_map, int $offset):array {

		$query = "SELECT * FROM ?t WHERE `id` > ?i AND `thread_map` = ?s ORDER BY `id` ASC LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::search()->select($query, [self::_RAW_COMMENT_TABLE_NAME, 0, $thread_map, self::_THREADS_COUNT, $offset, self::_THREADS_COUNT + $offset]);
	}

	/**
	 * Получить треды.
	 *
	 * @param array $slack_thread_list
	 *
	 * @return array
	 */
	protected function _getThreads(array $slack_thread_list):array {

		return Type_Thread_Meta::getAll(array_column($slack_thread_list, "thread_map"));
	}

	/**
	 * Поправить треды.
	 *
	 * @param array $thread_list
	 *
	 * @throws ExecutionException
	 * @throws \cs_UnpackHasFailed
	 * @long
	 */
	protected function _fixThreadMenu(array $thread_list):void {

		foreach ($thread_list as $thread_meta_row) {

			$message_map = Type_Thread_ParentRel::getMap($thread_meta_row["parent_rel"]);

			$source_parent_map  = "";
			$source_parent_type = Type_Thread_ParentRel::getType($thread_meta_row["parent_rel"]);
			if (Type_Thread_Utils::isConversationMessageParent($source_parent_type)) {
				$source_parent_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
			}

			// добавляем запись для создателя треда
			self::_insertMenu($thread_meta_row["creator_user_id"], $thread_meta_row, $source_parent_map, $source_parent_type);

			// добавляем записи для тех, кто отправлял комментарии, взяв комментарии из raw_thread_message
			$offset = 0;
			do {

				// получаем отправленные сообщения в этот тред
				$raw_comments = self::_getRawThreadComments($thread_meta_row["thread_map"], $offset);

				if (count($raw_comments) == 0) {
					break;
				}

				$offset += self::_THREADS_COUNT;

				console("Добавляем записи меню тредов для " . count($raw_comments) . " комментариев");

				foreach ($raw_comments as $comment) {

					// ищем упомянутых в тексте комментария
					$mention_user_id_list = [];
					$comment["data"]      = fromJson($comment["data"]);
					if (isset($comment["data"]["text"])) {
						$mention_user_id_list = Helper_Threads::getMentionUserIdListFromText($thread_meta_row, $comment["data"]["text"]);
					}
					$user_id_list = array_merge([$comment["sender_user_id"]], $mention_user_id_list);

					foreach (array_unique($user_id_list) as $user_id) {
						self::_insertMenu($user_id, $thread_meta_row, $source_parent_map, $source_parent_type);
					}
				}
			} while (count($raw_comments) > 0);
		}
	}

	/**
	 * Создать запись меню тредов.
	 *
	 * @param int    $user_id
	 * @param array  $thread_meta_row
	 * @param string $source_parent_map
	 * @param int    $source_parent_type
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected function _insertMenu(int $user_id, array $thread_meta_row, string $source_parent_map, int $source_parent_type):void {

		$insert = [
			"user_id"               => $user_id,
			"thread_map"            => $thread_meta_row["thread_map"],
			"source_parent_map"     => $source_parent_map,
			"source_parent_type"    => $source_parent_type,
			"is_hidden"             => 1,
			"is_follow"             => 0,
			"is_muted"              => 0,
			"unread_count"          => 0,
			"created_at"            => $thread_meta_row["created_at"],
			"updated_at"            => time(),
			"last_read_message_map" => "",
			"parent_rel"            => $thread_meta_row["parent_rel"],
		];
		ShardingGateway::database("company_thread")->insertArray("user_thread_menu", [$insert]);

		$insert = [
			"user_id"              => $user_id,
			"thread_unread_count"  => 0,
			"message_unread_count" => 0,
			"created_at"           => $thread_meta_row["created_at"],
			"updated_at"           => 0,
		];
		ShardingGateway::database("company_thread")->insertArray("user_inbox", [$insert]);
	}

	/**
	 * Розетка для временного поднятого контейнера manticore
	 */
	public function search():\BaseFrame\Search\Manticore {

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

(new Migration_Fix_Threads_Menu())->run($local_manticore_host, $local_manticore_port, $is_dry);