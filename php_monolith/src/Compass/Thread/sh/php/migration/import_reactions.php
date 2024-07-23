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

putenv("COMPANY_ID=$space_id");
$_SERVER["HTTP_HOST"] = $company_url;

require_once __DIR__ . "/../../../../../../start.php";
set_time_limit(0);
ini_set("memory_limit", "4096M");
ini_set("display_errors", "1");

/**
 * Пишем реакции комментариев в компас
 */
class Migration_Import_Reactions {

	protected const  _RAW_COMMENT_REACTION_TABLE = "raw_comment_reaction";
	protected const  _BOUND_THREAD_TABLE         = "bound_thread";
	protected const  _MESSAGES_BLOCK_COUNT       = 1000000;
	protected const  _THREAD_COUNT               = 1000000;
	protected const  _MESSAGES_COUNT             = 1000000;
	protected string $_local_manticore_host;
	protected int    $_local_manticore_port;

	/**
	 * Запускаем скрипт
	 */
	public function run(string $local_manticore_host, int $local_manticore_port):void {

		$this->_local_manticore_host = $local_manticore_host;
		$this->_local_manticore_port = $local_manticore_port;

		$export_bound_thread_list = self::_getBoundThreads();
		$thread_count             = count($export_bound_thread_list);
		console("Всего тредов для импорта реакций: {$thread_count}");

		// разбиваем по 50 тредов
		$chunked_bound_thread_list   = array_chunk($export_bound_thread_list, 50);
		$chunked_bound_thread_list[] = $chunked_bound_thread_list[0];

		// проходимся по одной пачке из 50 тредов
		$thread_counter = 0;
		foreach ($chunked_bound_thread_list as $thread_list) {

			// получаем массив мапов
			$thread_map_list = [];
			foreach ($thread_list as $thread) {

				$thread_map_list[] = $thread["thread_map"];
			}

			// получаем список блоков для треда
			$block_id_list = $this->_getBlockIdList($thread_map_list);

			if (count($block_id_list) < 1) {

				console("Не нашли реакции в таблице экспорта");
				continue;
			}

			// получаем все сообщения для этих тредов и блоков
			$comment_list = self::_getMessageList($thread_map_list, $block_id_list);

			// группируем все сообщения по тредам и блокам
			$grouped_comment_list = self::_groupCommentMessageList($comment_list);

			// проходимся по всем тредам
			foreach ($grouped_comment_list as $thread_map => $block_id_list) {

				// проходимся по всем блокам в треде
				$insert_reaction_block_list = [];
				foreach ($block_id_list as $block_id => $message_list) {

					// готовим массив реакций для каждого блока и всех сообщений в нем
					$insert_reaction_block_list[] = $this->_prepareBlock($thread_map, $block_id, $message_list);
				}

				// пишем в бд пачкой все блоки треда
				Gateway_Db_CompanyThread_MessageBlockReactionList::insertList($insert_reaction_block_list);

				$thread_counter++;
				console("Тред {$thread_counter}/{$thread_count}");
			}
		}
	}

	/**
	 * Получаем список тредов с bound таблицы
	 *
	 * @throws ExecutionException
	 */
	protected function _getBoundThreads():array {

		$query = "SELECT * FROM ?t WHERE `id`>?i LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::_search()->select($query, [self::_BOUND_THREAD_TABLE, 0, self::_THREAD_COUNT, 0, self::_THREAD_COUNT]);
	}

	/**
	 * Получаем список блоков
	 *
	 * @throws ExecutionException
	 */
	protected function _getBlockIdList(array $thread_map_list):array {

		$query        = "SELECT `block_id` FROM ?t WHERE `thread_map` IN (?as) LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		$query_result = self::_search()->select($query, [self::_RAW_COMMENT_REACTION_TABLE, $thread_map_list, self::_MESSAGES_BLOCK_COUNT, 0, self::_MESSAGES_BLOCK_COUNT]);

		$block_id_list = [];
		foreach ($query_result as $v) {
			$block_id_list[] = $v["block_id"];
		}

		return array_unique($block_id_list);
	}

	/**
	 * Получаем все сообщения с этого блока
	 *
	 * @throws ExecutionException
	 */
	protected function _getMessageList(array $thread_map_list, array $block_id_list):array {

		$query = "SELECT * FROM ?t WHERE `thread_map` IN (?as) AND `block_id` IN (?an) LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::_search()->select($query, [self::_RAW_COMMENT_REACTION_TABLE, $thread_map_list, $block_id_list, self::_MESSAGES_COUNT, 0, self::_MESSAGES_COUNT]);
	}

	/**
	 * Группируем по мапе треда
	 */
	protected function _groupCommentMessageList(array $message_list):array {

		$grouped_by_map = [];
		foreach ($message_list as $message) {
			$grouped_by_map[$message["thread_map"]][] = $message;
		}

		$grouped_by_map_and_block_id = [];
		foreach ($grouped_by_map as $map => $grouped_message_list) {

			$output = [];
			foreach ($grouped_message_list as $message) {
				$output[$message["block_id"]][] = $message;
			}
			$grouped_by_map_and_block_id[$map] = $output;
		}

		return $grouped_by_map_and_block_id;
	}

	/**
	 * Формируем блок
	 */
	protected function _prepareBlock(string $thread_map, int $block_id, array $message_list):array {

		// проходимся по каждому сообщению
		$message_reaction_list = [];
		foreach ($message_list as $message) {

			$reactions_list = fromJson($message["reactions"]);

			// проходимся по каждой реакции
			$prepared_reaction_list = [];
			foreach ($reactions_list as $reaction) {

				// проверяем что реакция существует
				$reaction_name = Type_Thread_Reaction_Main::getReactionNameIfExist($reaction["short_name"]);
				if (mb_strlen($reaction_name) < 1) {
					continue;
				}

				// проходим по каждому пользователю
				$user_reaction_list = [];
				foreach ($reaction["user_id_list"] as $user_id) {
					$user_reaction_list[$user_id] = timeMs();
				}

				$prepared_reaction_list[$reaction["short_name"]] = $user_reaction_list;
			}

			if (count($prepared_reaction_list) < 1) {

				$message_reaction_list[$message["message_map"]] = (object) [];
				continue;
			}

			$message_reaction_list[$message["message_map"]] = $prepared_reaction_list;
		}

		$reaction_data = [
			"version"               => 1,
			"message_reaction_list" => $message_reaction_list,
		];

		return [
			"thread_map"    => $thread_map,
			"block_id"      => $block_id,
			"created_at"    => time(),
			"updated_at"    => time(),
			"reaction_data" => json_encode($reaction_data),
		];
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

(new Migration_Import_Reactions())->run($local_manticore_host, $local_manticore_port);