<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для функционала, связанного только с одиночными диалогами
 */
class Type_Conversation_Single extends Type_Conversation_Default {

	// создает записи во всех таблицах ДИАЛОГА
	public static function add(int $conversation_type, int $creator_user_id, int $creator_user_npc, int $opponent_user_id, int $opponent_user_npc, int $allow_status):array {

		self::_throwIfCreatorEqualOpponent($creator_user_id, $opponent_user_id);

		// создаем основную мету
		$users = self::_makeUsersForCreateSingle($creator_user_id, $opponent_user_id);
		$extra = self::_makeExtraForCreateSingle($creator_user_id, $creator_user_npc, $opponent_user_id, $opponent_user_npc);
		return self::_createSingleMeta($conversation_type, $creator_user_id, $opponent_user_id, $users, $extra, $allow_status);
	}

	// если оппонент и есть создатель диалога
	protected static function _throwIfCreatorEqualOpponent(int $creator_user_id, int $opponent_user_id):void {

		if ($creator_user_id == $opponent_user_id) {
			throw new ReturnFatalException("Trying to create single conversation where creator_user_id ($creator_user_id) == opponent_user_id ($opponent_user_id)");
		}
	}

	// получаем users
	protected static function _makeUsersForCreateSingle(int $creator_user_id, int $opponent_user_id):array {

		return [
			$creator_user_id  => Type_Conversation_Meta_Users::initUserSchema(Type_Conversation_Meta_Users::ROLE_DEFAULT),
			$opponent_user_id => Type_Conversation_Meta_Users::initUserSchema(Type_Conversation_Meta_Users::ROLE_NOT_ATTACHED),
		];
	}

	// получаем extra
	protected static function _makeExtraForCreateSingle(int $creator_user_id, int $creator_user_npc, int $opponent_id, int $opponent_npc_type):array {

		// инициализируем структуру extra
		$extra = Type_Conversation_Meta_Extra::initExtra();

		// если пользователь является ботом
		if (Type_User_Action::isValidForAction($creator_user_npc, Type_User_Action::ATTACH_TO_BOT_LIST)) {
			$extra = Type_Conversation_Meta_Extra::addBot($extra, $creator_user_id);
		}

		// если оппонент является ботом
		if (Type_User_Action::isValidForAction($opponent_npc_type, Type_User_Action::ATTACH_TO_BOT_LIST)) {
			$extra = Type_Conversation_Meta_Extra::addBot($extra, $opponent_id);
		}

		// если пользователь является пользовательским ботом
		if (Type_User_Action::isValidForAction($creator_user_npc, Type_User_Action::ATTACH_TO_USERBOT_LIST)) {

			$userbot_id = Gateway_Socket_Company::getUserbotIdByUserId($creator_user_id);
			$extra      = Type_Conversation_Meta_Extra::addUserbot($extra, $userbot_id);
		}

		// если оппонент является пользовательским ботом
		if (Type_User_Action::isValidForAction($opponent_npc_type, Type_User_Action::ATTACH_TO_USERBOT_LIST)) {

			$userbot_id = Gateway_Socket_Company::getUserbotIdByUserId($opponent_id);
			$extra      = Type_Conversation_Meta_Extra::addUserbot($extra, $userbot_id);
		}

		return $extra;
	}

	// мето для создания single меты
	protected static function _createSingleMeta(int $conversation_type, int $creator_user_id, int $opponent_user_id, array $users, array $extra, int $allow_status):array {

		// проверяем, что указанный тип диалога является валидным подтипом синглов
		if (!Type_Conversation_Meta::isSubtypeOfSingle($conversation_type)) {
			throw new ParseFatalException("given conversation type is not single subtype");
		}

		$created_at = time();

		[$shard_id, $table_id] = \CompassApp\Pack\Conversation::getShardByTime($created_at);

		$meta_id = Type_Autoincrement_Main::getNextId(Type_Autoincrement_Main::CONVERSATION_META);

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::beginTransaction();

		// делаем insert в таблицу company_conversation.meta и company_conversation.dynamic
		$meta_row = self::_insertClusterConversationMeta($meta_id, $shard_id, $table_id, $created_at, $conversation_type, $allow_status, $creator_user_id, $users, $extra);
		self::_insertCloudConversationDynamic($meta_row["conversation_map"], $created_at);

		// вставляем запись в cluster_user_conversation_uniq.user_single и проверяем, conversation_map только созданного совпадает с conversation_map из user_single_{ceil}
		Gateway_Db_CompanyConversation_UserSingleUniq::insert($creator_user_id, $opponent_user_id, $meta_row["conversation_map"]);
		$user_single_row = Gateway_Db_CompanyConversation_UserSingleUniq::getOne($creator_user_id, $opponent_user_id);

		if ($user_single_row["conversation_map"] != $meta_row["conversation_map"]) {

			// делаем rollback и выбрасываем custom exception cs_Conversation_SingleIsExist и передаем в него conversation_map существующего диалога
			Gateway_Db_CompanyConversation_ConversationMetaLegacy::rollback();

			throw new cs_Conversation_SingleIsExist($user_single_row["conversation_map"]);
		}

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::commitTransaction();

		return $meta_row;
	}

	// создает записи во всех таблицах ПОЛЬЗОВАТЕЛЯ
	public static function attachUser(string $conversation_map, int $user_id, int $opponent_user_id, array $meta_row, bool $is_hidden):array {

		self::_throwIfConversationIsNotSingle($meta_row);

		// если пользователь не участник диалога то обновляем добавляя его
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			$meta_row["users"] = Type_Conversation_Meta::setUserRole($conversation_map, $user_id, Type_Conversation_Meta_Users::ROLE_DEFAULT);
		}

		// получаем запись из left_menu, создаем allow_status_alias для сингл диалога и передаем его
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getOne($user_id, $conversation_map);
		if (!isset($left_menu_row["user_id"])) {

			$allow_status_alias = Type_Conversation_Utils::getAllowStatus($meta_row["allow_status"], $meta_row["extra"], $opponent_user_id);
			self::_createUserCloudData(
				$user_id, $conversation_map, Type_Conversation_Meta_Users::ROLE_DEFAULT, $meta_row["type"],
				$allow_status_alias, 2, "", "", false, false, $opponent_user_id, $is_hidden
			);
		} else {

			$set = [
				"role" => Type_Conversation_Meta_Users::ROLE_DEFAULT,
			];

			if (!$is_hidden) {

				$set["version"]    = Domain_User_Entity_Conversation_LeftMenu::generateVersion($left_menu_row["version"]);
				$set["is_hidden"]  = 0;
				$set["updated_at"] = time();
			}
			Gateway_Db_CompanyConversation_UserLeftMenu::set($user_id, $conversation_map, $set);
		}

		Gateway_Event_Dispatcher::dispatch(Type_Event_UserConversation_UserJoinedConversation::create($user_id, $conversation_map, 1, time(), $is_hidden));

		return $meta_row["users"];
	}

	// обновить записи в таблицах пользователя (при очередном прикреплении)
	protected static function _throwIfConversationIsNotSingle(array $meta_row):void {

		if (!Type_Conversation_Meta::isSubtypeOfSingle($meta_row["type"])) {
			throw new ParseFatalException("Trying to use method on conversation, which type is not single");
		}
	}

	/**
	 * возвращает conversation_map по id участников single диалога, если не нашлось - false
	 *
	 * @return false|string
	 */
	public static function getMapByUsers(int $user1_id, int $user2_id):bool|string {

		$user_single_row = Gateway_Db_CompanyConversation_UserSingleUniq::getOne($user1_id, $user2_id);

		return isset($user_single_row["user1_id"]) ? $user_single_row["conversation_map"] : false;
	}

	/**
	 * Получить cluster_user_conversation_uniq записи списком по парам пользователей
	 *
	 */
	public static function getMapListByUserPairList(array $user_pair_list):array {

		return Gateway_Db_CompanyConversation_UserSingleUniq::getList($user_pair_list);
	}

	// обновить необходимый статус диалога allow_status, а также записать в левое меню изменение allow_status_alias
	public static function setIsAllowedInMetaAndLeftMenu(string $conversation_map, int $allow_status, int $user_id, int $opponent_user_id, array $extra = null):void {

		$set = [
			"allow_status" => $allow_status,
			"updated_at"   => time(),
		];

		// если экстру передали то ее тоже обновляем
		if (!is_null($extra)) {
			$set["extra"] = $extra;
		}

		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, $set);

		// обновляем allow_status в мете и в левом меню
		Type_Phphooker_Main::updateAllowStatusAliasInLeftMenu($allow_status, $extra, $conversation_map, $user_id, $opponent_user_id);
	}
}