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
 * Покидаем чаты
 */
class Migration_Leave_From_Conversations {

	protected const  _BOUND_TABLE_NAME    = "bound_conversation";
	protected const  _CONVERSATIONS_COUNT = 1000000;
	protected string $_local_manticore_host;
	protected int    $_local_manticore_port;

	/**
	 * Покидаем всеми пользователями с переданных чатов
	 *
	 * @long
	 */
	public function run(string $local_manticore_host, int $local_manticore_port, bool $is_dry):void {

		$this->_local_manticore_host = $local_manticore_host;
		$this->_local_manticore_port = $local_manticore_port;

		// получаем чаты из таблицы битых чатов
		$conversation_list = self::_getConversationsForDelete();

		if ($is_dry) {

			console("DRY-RUN - кикаем пользователей из " . count($conversation_list) . " чатов");
			return;
		}

		self::_leaveFromGroup($conversation_list);
	}

	/**
	 * Получаем чаты, что импортнули в поломанной таблице
	 *
	 * @param int $offset
	 *
	 * @return array
	 * @throws ExecutionException
	 */
	protected function _getConversationsForDelete():array {

		$query = "SELECT * FROM ?t WHERE `id` > ?i ORDER BY `id` ASC LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::search()->select($query, [self::_BOUND_TABLE_NAME, 0, self::_CONVERSATIONS_COUNT, 0, self::_CONVERSATIONS_COUNT]);
	}

	/**
	 * Покидаем группы
	 *
	 * @throws cs_OwnerTryToLeaveGeneralConversation
	 * @throws cs_OwnerTryToLeaveRespectConversation
	 * @throws ParamException
	 */
	protected function _leaveFromGroup(array $conversation_list):void {

		// создаём диалоги в php_world
		foreach ($conversation_list as $conversation) {

			$conversation_map = $conversation["conversation_map"];

			// получаем мета информацию о диалоге
			$meta_row = Type_Conversation_Meta::get($conversation_map);

			foreach ($meta_row["users"] as $user_id => $user) {

				Helper_Groups::doLeave($conversation_map, $user_id, $meta_row, false, true);
			}
		}
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

(new Migration_Leave_From_Conversations())->run($local_manticore_host, $local_manticore_port, $is_dry);