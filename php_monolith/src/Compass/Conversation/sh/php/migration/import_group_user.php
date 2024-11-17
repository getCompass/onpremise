<?php declare(strict_types = 1);

namespace Compass\Conversation;

require_once "/app/src/Compass/Conversation/api/includes/type/script/input_parser.php";
require_once "/app/src/Compass/Conversation/api/includes/type/script/input_helper.php";
require_once "/app/src/Modules/vendor/autoload.php";

$local_manticore_host = Type_Script_InputParser::getArgumentValue("--local_manticore_host", Type_Script_InputParser::TYPE_STRING, "82.148.27.130");
$local_manticore_port = Type_Script_InputParser::getArgumentValue("--local_manticore_port", Type_Script_InputParser::TYPE_INT, 9306);
$company_id           = Type_Script_InputParser::getArgumentValue("--company_id", Type_Script_InputParser::TYPE_INT);
$company_url          = Type_Script_InputParser::getArgumentValue("--company_url", Type_Script_InputParser::TYPE_STRING, "");
$is_dry               = Type_Script_InputHelper::isDry();

putenv("COMPANY_ID=$company_id");
$_SERVER["HTTP_HOST"] = $company_url;

require_once __DIR__ . "/../../../../../../start.php";
set_time_limit(0);
ini_set("memory_limit", "4096M");
ini_set("display_errors", "1");

use BaseFrame\Search\Exception\ExecutionException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Добавляем пользователей в группы
 */
class Migration_Import_Group_User {

	protected const  _RAW_CONVERSATION_TABLE   = "raw_conversation";
	protected const  _BOUND_CONVERSATION_TABLE = "bound_conversation";
	protected const  _BOUND_USERS_TABLE        = "bound_user";
	protected const  _USERS_COUNT              = 1000000;
	protected string $_local_manticore_host;
	protected int    $_local_manticore_port;

	/**
	 * Запускаем скрипт
	 * @long
	 */
	public function run(string $local_manticore_host, int $local_manticore_port, bool $is_dry):void {

		$this->_local_manticore_host = $local_manticore_host;
		$this->_local_manticore_port = $local_manticore_port;

		$conversation_raw_list = self::_getRawConversationList();

		// проходим по каждому диалогу
		foreach ($conversation_raw_list as $conversation_raw) {

			// проверяем у диалога нужный тип
			if ($conversation_raw["type"] != CONVERSATION_TYPE_GROUP_DEFAULT) {

				if ($is_dry) {
					console("DRY-RUN!!! Пропустили диалог с type = " . $conversation_raw["type"] . " для uniq = " . $conversation_raw["uniq"]);
				}

				Type_System_Admin::log("import_group_user", "Пропустили диалог с type = " . $conversation_raw["type"] . " для uniq = " . $conversation_raw["uniq"]);
				console("Пропустили диалог с type = " . $conversation_raw["type"] . " для uniq = " . $conversation_raw["uniq"]);
				continue;
			}

			$compass_extra     = isset($conversation_raw["compass_extra"]) ? fromJson($conversation_raw["compass_extra"]) : [];
			$conversation_type = $compass_extra["meta_type"] ?? null;
			if (!is_null($conversation_type) && $conversation_type == CONVERSATION_TYPE_GROUP_HIRING) {
				continue;
			}

			$members              = fromJson($conversation_raw["members"]);
			$dynamic              = $compass_extra["conversation_dynamic"] ?? null;
			$meta_owners          = $compass_extra["meta_owners"] ?? [];
			$meta_users_joined_at = $compass_extra["meta_users_joined_at"] ?? [];

			// делаем запрос в bound_conversation
			$conversation_bound = self::_getBoundConversation($conversation_raw["uniq"])[0];

			// получаем мету диалога
			$meta_row = Type_Conversation_Meta::get($conversation_bound["conversation_map"]);

			if (!is_null($dynamic)) {
				[$dynamic, $meta_users_joined_at] = self::_prepareConversationData($dynamic, $meta_users_joined_at, $members);
			}

			// добавляем пользователей в группу, если не состоят в ней
			foreach ($members as $user_id) {

				// проверяем что пользователь состоит в компании - иначе не надо добавлять в группу
				$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
				if (!isset($user_info_list[$user_id]) || $user_info_list[$user_id]->role === Member::ROLE_LEFT) {
					continue;
				}

				// добавляем в группу
				if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {

					if ($is_dry) {

						console("DRY-RUN!!! Добавили пользователя user_id = {$user_id} в группу c uniq = " . $conversation_raw["uniq"] . " и conversation_map = " . $meta_row["conversation_map"]);
						continue;
					}

					$role = $user_id == $conversation_raw["creator_user_id"]
						? Type_Conversation_Meta_Users::ROLE_OWNER
						: Type_Conversation_Meta_Users::ROLE_DEFAULT;

					if ($role == Type_Conversation_Meta_Users::ROLE_DEFAULT && in_array($user_id, $meta_owners)) {
						$role = Type_Conversation_Meta_Users::ROLE_OWNER;
					}

					$is_muted    = false;
					$clear_until = 0;
					if (!is_null($dynamic)) {

						$user_mute_info = $dynamic["user_mute_info"] ?? [];
						$is_muted       = Domain_Conversation_Entity_Dynamic::isMuted($user_mute_info, $user_id, time());
						$clear_until    = Domain_Conversation_Entity_Dynamic::getClearUntil($dynamic["user_clear_info"], $dynamic["conversation_clear_info"], $user_id);

						if ($clear_until == 0) {
							$clear_until = $meta_users_joined_at[$user_id] ?? 0;
						}
					}

					Helper_Groups::doJoin(
						$meta_row["conversation_map"], $user_id, role: $role, is_need_silent: true, migration_clear_until: $clear_until, is_migration_muted: $is_muted
					);

					Type_System_Admin::log("import_group_user", "Добавили пользователя user_id = {$user_id} в группу c uniq = " . $conversation_raw["uniq"] . " и conversation_map = " . $meta_row["conversation_map"]);
					console("Добавили пользователя user_id = {$user_id} в группу c uniq = " . $conversation_raw["uniq"] . " и conversation_map = " . $meta_row["conversation_map"]);
				}
			}
		}
	}

	/**
	 * Получаем список с raw таблицами
	 *
	 * @throws ExecutionException
	 */
	protected function _getRawConversationList():array {

		$query = "SELECT * FROM ?t WHERE ?i=?i ORDER BY `id` ASC LIMIT ?i OFFSET ?i OPTION max_matches=?i";
		return self::_manticore()->select($query, [self::_RAW_CONVERSATION_TABLE, 0, 0, 500000, 0, 500000]);
	}

	/**
	 * Получаем список с bound таблицами
	 *
	 * @throws ExecutionException
	 */
	protected function _getBoundConversation(string $uniq):array {

		$query = "SELECT * FROM ?t WHERE uniq=?s ORDER BY `id` ASC LIMIT ?i OFFSET ?i";
		return self::_manticore()->select($query, [self::_BOUND_CONVERSATION_TABLE, $uniq, 1, 0]);
	}

	/**
	 * Получаем список пользователей с bound таблицы
	 *
	 * @throws ExecutionException
	 */
	protected function _getBoundUsers(array $user_id_list):array {

		$query = "SELECT * FROM ?t WHERE `user_id` IN (?an) LIMIT ?i OPTION max_matches=?i";
		return self::_manticore()->select($query, [self::_BOUND_USERS_TABLE, $user_id_list, count($user_id_list), self::_USERS_COUNT]);
	}

	/**
	 * Подготавливаем dynamic диалога для миграции
	 * @throws ExecutionException
	 */
	protected function _prepareConversationData(array $dynamic, array $meta_users_joined_at, array $users_id_list):array {

		// получаем связь id участников группы
		$bound_user_list = self::_getBoundUsers($users_id_list);

		// uniq - id c Compass; user_id - id в новом мире.
		foreach ($bound_user_list as $bound_user) {

			$saas_user_id = $bound_user["uniq"];

			if (isset($meta_users_joined_at[$saas_user_id])) {

				$meta_users_joined_at[$bound_user["user_id"]] = $meta_users_joined_at[$saas_user_id];
				unset($meta_users_joined_at[$saas_user_id]);
			}

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

		return [$dynamic, $meta_users_joined_at];
	}

	/**
	 * Розетка для временного поднятого контейнера manticore
	 */
	public function _manticore():\BaseFrame\Search\Manticore {

		$conf = [
			"host" => $this->_local_manticore_host,
			"port" => $this->_local_manticore_port,
		];

		// получаем конфиг с базой данных
		return \BaseFrame\Search\Provider::instance()->connect(new \BaseFrame\Search\Config\Connection(...$conf));
	}
}

// запускаем
(new Migration_Import_Group_User())->run($local_manticore_host, $local_manticore_port, $is_dry);