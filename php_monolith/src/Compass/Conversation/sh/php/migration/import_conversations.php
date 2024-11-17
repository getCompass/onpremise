<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Search\Exception\ExecutionException;
use BaseFrame\System\Locale;

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
	protected const  _BOUND_FILE_TABLE    = "bound_file";
	protected const  _BOUND_USERS_TABLE   = "bound_user";
	protected const  _USERS_COUNT         = 1000000;
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

			$conversation_meta = match ((int) $slack_conversation["type"]) {
				CONVERSATION_TYPE_SINGLE_DEFAULT => $this->_createSingleConversationMeta($slack_conversation),
				default => $this->_createGroupConversationMeta($slack_conversation),
			};

			if ($conversation_meta === false) {
				continue;
			}

			$conversation_map_list[$slack_conversation["uniq"]] = [
				"id"                => $slack_conversation["id"],
				"conversation_map"  => $conversation_meta["conversation_map"],
				"conversation_name" => $conversation_meta["conversation_name"],
			];
		}

		return $conversation_map_list;
	}

	/**
	 * Создает новый личный диалог и возвращает мету диалога.
	 * Если чат уже существует, новый создан не будет.
	 * @long
	 */
	protected function _createSingleConversationMeta(array $slack_conversation):array|false {

		$member_list = fromJson($slack_conversation["members"]);

		if (count($member_list) !== 2) {

			console("некорректный список пользователей для личного чата {$slack_conversation["uniq"]}, пропускаю");
			return false;
		}

		$user_id_1 = (int) $member_list[0];
		$user_id_2 = (int) $member_list[1];

		if ($user_id_1 === $user_id_2) {

			console("некорректный список пользователей для личного чата {$slack_conversation["uniq"]}, пропускаю");
			return false;
		}

		$compass_extra        = isset($slack_conversation["compass_extra"]) ? fromJson($slack_conversation["compass_extra"]) : [];
		$conversation_type    = $compass_extra["meta_type"] ?? null;
		$conversation_extra   = $compass_extra["meta_extra"] ?? null;
		$conversation_dynamic = $compass_extra["conversation_dynamic"] ?? null;

		if (!is_null($conversation_dynamic)) {
			$conversation_dynamic = self::_prepareConversationDynamic($conversation_dynamic, [$user_id_1, $user_id_2]);
		}

		$conversation_meta                      = Type_Conversation_Single::addFromMigration(
			$user_id_1, $user_id_2, $conversation_type, $conversation_extra, $conversation_dynamic
		);
		$conversation_meta["conversation_name"] = "Личный чат";

		return $conversation_meta;
	}

	/**
	 * Создает новый групповой диалога и возвращает мету диалога.
	 * Всегда создает новый диалог.
	 * @long
	 */
	protected function _createGroupConversationMeta(array $slack_conversation):array|false {

		if (mb_strlen($slack_conversation["name"]) === 0) {
			$slack_conversation["name"] = "Группа";
		}

		$compass_extra        = isset($slack_conversation["compass_extra"]) ? fromJson($slack_conversation["compass_extra"]) : [];
		$conversation_type    = $compass_extra["meta_type"] ?? null;
		$conversation_extra   = $compass_extra["meta_extra"] ?? null;
		$conversation_dynamic = $compass_extra["conversation_dynamic"] ?? null;
		$avatar_file_key      = $compass_extra["avatar_file_key"] ?? null;

		$avatar_file_map = "";
		if (!is_null($avatar_file_key) && mb_strlen($avatar_file_key) > 0) {

			$file_bound      = self::_getBoundFile($avatar_file_key);
			$avatar_file_map = $file_bound[0]["file_map"] ?? "";
		}

		// импортируем чат заметок как обычную группу
		if (!is_null($conversation_type) && $conversation_type == CONVERSATION_TYPE_SINGLE_NOTES) {
			$conversation_type = CONVERSATION_TYPE_GROUP_DEFAULT;
		}

		// для Главного чата возвращаем текущий чат ГЧ
		if (!is_null($conversation_type) && $conversation_type == CONVERSATION_TYPE_GROUP_GENERAL) {

			$value = Domain_Conversation_Action_Config_Get::do(Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME);
			if (isset($value["value"]) && mb_strlen($value["value"]) > 0) {
				return (array) Gateway_Db_CompanyConversation_ConversationMeta::getOne($value["value"]);
			}
		}

		if (!is_null($conversation_dynamic)) {

			$member_list          = fromJson($slack_conversation["members"]);
			$conversation_dynamic = self::_prepareConversationDynamic($conversation_dynamic, array_map(static fn($user_id) => (int) $user_id, $member_list));
		}

		return Type_Conversation_Group::addByMigration(
			$slack_conversation["creator_user_id"],
			$slack_conversation["name"],
			$slack_conversation["description"],
			$conversation_type,
			$conversation_extra,
			$conversation_dynamic,
			$avatar_file_map,
		);
	}

	/**
	 * Получаем записи файлов из bound табли
	 *
	 * @param string $uniq
	 *
	 * @return array
	 * @throws ExecutionException
	 */
	protected function _getBoundFile(string $uniq):array {

		$query = "SELECT * FROM ?t WHERE `uniq` = ?s LIMIT ?i OPTION max_matches=?i";
		return self::search()->select($query, [self::_BOUND_FILE_TABLE, $uniq, 1, 100000]);
	}

	/**
	 * Получаем список пользователей с bound таблицы
	 *
	 * @throws ExecutionException
	 */
	protected function _getBoundUsers(array $user_id_list):array {

		$query = "SELECT * FROM ?t WHERE `user_id` IN (?an) LIMIT ?i OPTION max_matches=?i";
		return self::search()->select($query, [self::_BOUND_USERS_TABLE, $user_id_list, count($user_id_list), self::_USERS_COUNT]);
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
	 * Подготавливаем dynamic диалога для миграции
	 * @throws ExecutionException
	 */
	protected function _prepareConversationDynamic(array $dynamic, array $users_id_list):array {

		// получаем связь id участников группы
		$bound_user_list = self::_getBoundUsers($users_id_list);

		// uniq - id c Compass; user_id - id в новом мире.
		foreach ($bound_user_list as $bound_user) {

			$saas_user_id = $bound_user["uniq"];
			if (isset($dynamic["user_mute_info"][$saas_user_id])) {

				$dynamic["user_mute_info"][$bound_user["user_id"]] = $dynamic["user_mute_info"][$saas_user_id];
				unset($dynamic["user_mute_info"][$saas_user_id]);
			}

			if (isset($dynamic["user_clear_info"][$saas_user_id])) {

				$dynamic["user_clear_info"][$bound_user["user_id"]] = $dynamic["user_clear_info"][$saas_user_id];
				unset($dynamic["user_clear_info"][$saas_user_id]);
			}

			if (isset($dynamic["user_file_clear_info"][$saas_user_id])) {

				$dynamic["user_file_clear_info"][$bound_user["user_id"]] = $dynamic["user_file_clear_info"][$saas_user_id];
				unset($dynamic["user_file_clear_info"][$saas_user_id]);
			}

			if (isset($dynamic["conversation_clear_info"][$saas_user_id])) {

				$dynamic["conversation_clear_info"][$bound_user["user_id"]] = $dynamic["conversation_clear_info"][$saas_user_id];
				unset($dynamic["conversation_clear_info"][$saas_user_id]);
			}
		}

		return $dynamic;
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

(new Migration_Import_Conversations())->run($local_manticore_host, $local_manticore_port, $is_dry);