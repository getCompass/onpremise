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
 * Импортируем треды
 */
class Migration_Import_Threads {

	protected const _RAW_TABLE_NAME   = "raw_thread";
	protected const _BOUND_TABLE_NAME = "bound_thread";
	protected const _THREADS_COUNT    = 900;
	protected string $_local_manticore_host;
	protected int    $_local_manticore_port;

	/**
	 * @long
	 */
	public function run(string $local_manticore_host, int $local_manticore_port, bool $is_dry):void {

		$this->_local_manticore_host = $local_manticore_host;
		$this->_local_manticore_port = $local_manticore_port;

		console("импорт тредов запущен");

		$offset = 0;
		do {

			// получаем треды из таблицы raw
			$slack_thread_list = self::_getThreads($offset);
			if (count($slack_thread_list) == 0) {
				break;
			}
			console("импортируем " . count($slack_thread_list) . " тред");

			$offset += self::_THREADS_COUNT;

			if ($is_dry) {

				console("DRY-RUN - будет создано " . count($slack_thread_list) . " тредов");
				continue;
			}

			// создаём треды
			$thread_list_by_uniq = self::_createThreadList($slack_thread_list);
			console("создали " . count($thread_list_by_uniq) . " тред");

			// добавляем связь тредов из слака и мапу созданного треда
			self::_createThreadMapUniqRel($thread_list_by_uniq);
		} while (count($slack_thread_list) > 0);

		console(greenText("импорт тредов выполнен"));
	}

	/**
	 * Получаем треды слака
	 *
	 * @param int $offset
	 *
	 * @return array
	 * @throws ExecutionException
	 */
	protected function _getThreads(int $offset):array {

		$query = "SELECT * FROM ?t WHERE `creator_user_id` > ?i ORDER BY `id` ASC LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::search()->select($query, [self::_RAW_TABLE_NAME, 0, self::_THREADS_COUNT, $offset, self::_THREADS_COUNT + $offset]);
	}

	/**
	 * Создать тред в php_world
	 *
	 * @param array $slack_thread_list
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_ConversationIsLocked
	 */
	protected function _createThreadList(array $slack_thread_list):array {

		$thread_info_list = [];
		foreach ($slack_thread_list as $slack_thread) {

			// создаём треды
			try {

				$thread_map = Domain_Thread_Action_AddByMigration::do(
					$slack_thread["creator_user_id"], $slack_thread["parent_map"], $slack_thread["conversation_map"]
				);
			} catch (cs_Message_IsDeleted | cs_Thread_ParentEntityNotFound) {

				console(yellowText("пропустили импорт треда с id: " . $slack_thread["id"] . "так как сообщение удалено или диалог не найден"));
				continue;
			}

			$thread_info_list[$slack_thread["uniq"]] = [
				"id"               => $slack_thread["id"],
				"parent_map"       => $slack_thread["parent_map"],
				"conversation_map" => $slack_thread["conversation_map"],
				"thread_map"       => $thread_map,
			];
		}

		return $thread_info_list;
	}

	/**
	 * Создать связь id треда слака и thread_map треда нашего мира
	 *
	 * @param array $thread_list_by_uniq
	 *
	 * @throws ExecutionException
	 */
	protected function _createThreadMapUniqRel(array $thread_list_by_uniq):void {

		$insert_array = [];
		foreach ($thread_list_by_uniq as $uniq => $thread) {

			$insert_array[] = [
				"id"               => $thread["id"],
				"uniq"             => $uniq,
				"thread_map"       => $thread["thread_map"],
				"parent_map"       => $thread["parent_map"],
				"conversation_map" => $thread["conversation_map"],
			];
		}

		if (count($insert_array) < 1) {
			return;
		}

		self::search()->insert(self::_BOUND_TABLE_NAME, $insert_array);
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

(new Migration_Import_Threads())->run($local_manticore_host, $local_manticore_port, $is_dry);