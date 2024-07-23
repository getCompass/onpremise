<?php declare(strict_types = 1);

namespace Compass\Conversation;

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
 * Пишем реакции сообщений в компас.
 */
class Migration_Import_Reactions {

	protected const  _RAW_MESSAGES_REACTION_TABLE = "raw_message_reaction";
	protected const  _BOUND_CONVERSATIONS_TABLE   = "bound_conversation";
	protected const  _MESSAGES_BLOCK_COUNT        = 1000000;
	protected const  _CONVERSATIONS_COUNT         = 1000000;
	protected const  _MESSAGES_COUNT              = 1000000;
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
		console("Всего чатов для импорта реакций: {$conversations_count}");

		$conversations_counter = 0;
		foreach ($conversation_map_list as $conversation) {

			$conversation_map = $conversation["conversation_map"];

			// получаем список блоков для чата
			$block_id_list = $this->_getBlockIdList($conversation_map);

			// для каждого блока этого чата
			foreach ($block_id_list as $block_id) {
				$block_id_list_count = count($block_id_list);

				// получаем все сообщения с этого блока
				$message_list = self::_getMessageList($conversation_map, $block_id);

				// мержим все сообщения в один блок
				$insert_reaction_block = self::_prepareBlock($conversation_map, $block_id, $message_list);

				// пишем блок в базу
				Gateway_Db_CompanyConversation_MessageBlockReactionList::insert($insert_reaction_block);

				console("Block_id {$block_id}/{$block_id_list_count}");
			}

			$conversations_counter++;
			console("Добавили реакции для {$conversations_counter}/{$conversations_count} чатов");
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
	 * Получаем список блоков
	 *
	 * @throws ExecutionException
	 */
	protected function _getBlockIdList(string $conversation_map):array {

		$query        = "SELECT `block_id` FROM ?t WHERE `conversation_map`=?s LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		$query_result = self::_search()->select($query, [self::_RAW_MESSAGES_REACTION_TABLE, $conversation_map, self::_MESSAGES_BLOCK_COUNT, 0, self::_MESSAGES_BLOCK_COUNT]);

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
	protected function _getMessageList(string $conversation_map, int $block_id):array {

		$query = "SELECT * FROM ?t WHERE `conversation_map`=?s AND `block_id`=?i LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::_search()->select($query, [self::_RAW_MESSAGES_REACTION_TABLE, $conversation_map, $block_id, self::_MESSAGES_COUNT, 0, self::_MESSAGES_COUNT,]);
	}

	/**
	 * Формируем блок
	 */
	protected function _prepareBlock(string $conversation_map, int $block_id, array $message_list):array {

		// проходимся по каждому сообщению
		$message_reaction_list = [];
		foreach ($message_list as $message) {

			$reactions_list = fromJson($message["reactions"]);

			// проходимся по каждой реакции
			$prepared_reaction_list = [];
			foreach ($reactions_list as $reaction) {

				// проверяем что реакция существует
				$reaction_name = Type_Conversation_Reaction_Main::getReactionNameIfExist($reaction["short_name"]);
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
			"conversation_map" => $conversation_map,
			"block_id"         => $block_id,
			"created_at"       => time(),
			"updated_at"       => time(),
			"reaction_data"    => json_encode($reaction_data),
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