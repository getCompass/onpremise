<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Search\Exception\ExecutionException;

require_once "/app/src/Compass/Conversation/api/includes/type/script/input_parser.php";
require_once "/app/src/Compass/Conversation/api/includes/type/script/input_helper.php";
require_once "/app/src/Modules/vendor/service/php_base_frame/system/functions.php";

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
 * Импортируем чаты
 */
class Migration_Import_Conversations {

	protected const  _RAW_TABLE_NAME      = "raw_conversation";
	protected const  _BOUND_TABLE_NAME    = "bound_conversation";
	protected const  _CONVERSATIONS_COUNT = 900;
	protected string $_local_manticore_host;
	protected int    $_local_manticore_port;

	/**
	 * @long
	 */
	public function run(string $local_manticore_host, int $local_manticore_port, bool $is_dry):void {

		$this->_local_manticore_host = $local_manticore_host;
		$this->_local_manticore_port = $local_manticore_port;

		$offset = 0;
		console("создаём чаты в Compass");
		do {

			// получааем чаты из таблицы raw
			$slack_conversation_list = self::_getConversations($offset);

			$offset += self::_CONVERSATIONS_COUNT;

			if ($is_dry) {

				console("DRY-RUN - будет создано " . count($slack_conversation_list) . " чатов");
				continue;
			}

			// создаём диалоги в php_world
			$conversation_map_list_by_uniq = self::_createConversationMetaList($slack_conversation_list);

			// добавляем связь чатов из слака и мапу созданного диалога
			self::_createConversationMapUniqRel($conversation_map_list_by_uniq);
			console(greenText("создано " . count($conversation_map_list_by_uniq) . " чатов"));
		} while (count($slack_conversation_list) > 0);
	}

	/**
	 * Получаем чаты со слака
	 *
	 * @param int $offset
	 *
	 * @return array
	 * @throws ExecutionException
	 */
	protected function _getConversations(int $offset):array {

		$query = "SELECT * FROM ?t WHERE `creator_user_id` > ?i ORDER BY `id` ASC LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::search()->select($query, [self::_RAW_TABLE_NAME, 0, self::_CONVERSATIONS_COUNT, $offset, self::_CONVERSATIONS_COUNT + $offset]);
	}

	/**
	 * Создать диалог в php_world
	 *
	 * @param array $slack_conversation_list
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected function _createConversationMetaList(array $slack_conversation_list):array {

		// создаём диалоги в php_world
		$conversation_map_list = [];
		foreach ($slack_conversation_list as $slack_conversation) {

			if (mb_strlen($slack_conversation["name"]) == 0) {
				$slack_conversation["name"] = "Группа";
			}

			$conversation = Type_Conversation_Group::addByMigration(
				$slack_conversation["creator_user_id"], $slack_conversation["name"], $slack_conversation["description"]
			);

			$conversation_map_list[$slack_conversation["uniq"]] = [
				"id"                => $slack_conversation["id"],
				"conversation_map"  => $conversation["conversation_map"],
				"conversation_name" => $conversation["conversation_name"],
			];
		}

		return $conversation_map_list;
	}

	/**
	 * Создать связь id чата слака и conversation_map диалога нашего мира
	 *
	 * @param array $conversation_map_list_by_uniq
	 *
	 * @throws ExecutionException
	 */
	protected function _createConversationMapUniqRel(array $conversation_map_list_by_uniq):void {

		$insert_array = [];
		foreach ($conversation_map_list_by_uniq as $uniq => $conversation) {

			$insert_array[] = [
				"id"                => $conversation["id"],
				"uniq"              => $uniq,
				"conversation_map"  => $conversation["conversation_map"],
				"conversation_name" => $conversation["conversation_name"],
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

(new Migration_Import_Conversations())->run($local_manticore_host, $local_manticore_port, $is_dry);