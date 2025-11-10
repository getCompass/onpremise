<?php

declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\User\Exception\NotAllowedType;
use JetBrains\PhpStorm\ArrayShape;
use BaseFrame\Exception\Request\ParamException;

/**
 * API-сценарии домена «диалоги».
 */
class Domain_Conversation_Scenario_Api {

	/** @var int Время после которого не нужно отдавать уволенных пользователей (при необходимости) */
	protected const _TIME_NOT_ADDED_DISMISSED_USER = DAY7;

	/** @var int Максимальное количество диалогов которые можем проверить на allowed за раз */
	protected const _MAX_GET_ALLOWED_CONVERSATIONS_FOR_ADD_MESSAGE_COUNT = 30;

	/** @var int Максимальное количество контактов пользователей отправляемое за раз */
	protected const _MAX_SEND_CONTACT_USER_ID_COUNT = 30;

	/**
	 * Получить левое меню
	 *
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws NotAllowedType
	 * @throws \parseException
	 * @long
	 */
	public static function getLeftMenu(
		int    $user_id,
		int    $limit,
		int    $offset,
		string $search_query = "",
		int    $filter_favorite = 0,
		int    $filter_unread = 0,
		int    $filter_single = 0,
		int    $filter_unblocked = 0,
		int    $filter_owner = 0,
		int    $filter_system = 0,
		int    $is_mentioned_first = 0,
		int    $filter_blocked_time = 0,
		int    $filter_support = 0,
		array  $filter_npc_type = []
	):array {

		// проверяем что переданы правильные типы пользователей
		Type_User_Main::assertUserTypeList($filter_npc_type);

		$left_menu_list = Domain_Conversation_Action_GetLeftMenu::do(
			$user_id,
			$limit,
			$offset,
			$search_query,
			$filter_favorite,
			$filter_unread,
			$filter_single,
			$filter_unblocked,
			$filter_owner,
			$filter_system,
			$filter_support,
			$is_mentioned_first,
		);

		// получаем значение has_next
		$has_next = (int) count($left_menu_list) == $limit ? 1 : 0;

		// убираем legacy типы
		$left_menu_list = Domain_Conversation_Entity_LegacyTypes::filterLeftMenu($left_menu_list);

		// получаем dynamic-данные диалогов левого меню для получения версий обновления
		$dynamic_list = Gateway_Db_CompanyConversation_ConversationDynamic::getAll(array_column($left_menu_list, "conversation_map"), true);

		// получаем пользователей с которыми у нас сингл диалоги
		$single_user_id_list  = [];
		$single_left_menu_row = [];
		foreach ($left_menu_list as $left_menu_row) {

			if ((int) $left_menu_row["type"] === CONVERSATION_TYPE_SINGLE_DEFAULT) {

				$single_user_id_list[]                                    = $left_menu_row["opponent_user_id"];
				$single_left_menu_row[$left_menu_row["opponent_user_id"]] = $left_menu_row;
			}
		}
		$user_list = Gateway_Bus_CompanyCache::getMemberList($single_user_id_list);

		// убираем уволенных в зависимости от времени их увольнения, правим диалоги с удалившими аккаунт пользователями
		if ($filter_blocked_time) {
			$left_menu_list = self::_prepareDismissedUserForTime($left_menu_list, $user_list);
		}
		$left_menu_list = self::_updateSingleDialogWithAccountDeleteUser($user_id, $single_left_menu_row, $left_menu_list, $user_list);

		return [$left_menu_list, $has_next, $dynamic_list];
	}

	/**
	 * обновляем сингл диалоги с удалившими аккаунт пользователями
	 *
	 * @long много неразрывной логики
	 * @throws \parseException
	 */
	protected static function _updateSingleDialogWithAccountDeleteUser(int $user_id, array $single_left_menu_row, array $left_menu_list, array $user_list):array {

		// собираем список пользователей из single-диалогов из записей левого меню
		$deleted_conversation_map_list = [];
		foreach ($user_list as $user) {

			// если пользователь удалил аккаунт
			if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user->extra)) {

				// со стороны оппонента - записываем в левое меню о том что пользователь удалил аккаунт и обнуляем время мьюта
				$left_menu_row    = $single_left_menu_row[$user->user_id];
				$conversation_map = $left_menu_row["conversation_map"];

				// выходим если уже обновляли диалог
				if ($left_menu_row["allow_status_alias"] == Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DELETED ||
					$left_menu_row["allow_status_alias"] == Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DISABLED) {
					continue;
				}

				self::_setUnmutedIfNeed($user_id, $conversation_map, $left_menu_row);
				$deleted_conversation_map_list[] = $conversation_map;

				// обновляем is_allowed диалога, ставим статус — ALLOW_STATUS_NEED_CHECK, а не ALLOW_STATUS_MEMBER_DISABLED
				// чтобы система сама обновила диалог до статуса ALLOW_STATUS_MEMBER_DISABLED и произвела все необходимые действия
				// для блокировки диалога и скрываем single диалог
				// записываем allow_status_alias в левоем меню собеседнику уволенного сотрудника
				Gateway_Db_CompanyConversation_ConversationMeta::set($conversation_map, [
					"allow_status" => ALLOW_STATUS_MEMBER_DELETED,
					"updated_at"   => time(),
				]);
				Type_Phphooker_Main::sendClearThreadMetaCache($conversation_map);

				$set["allow_status_alias"] = Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DELETED;
				Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
			}
		}

		// обновляем сразу левое меню
		foreach ($left_menu_list as $k1 => $v1) {

			foreach ($deleted_conversation_map_list as $v2) {

				if ($v1["conversation_map"] == $v2) {
					$left_menu_list[$k1]["allow_status_alias"] = Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DELETED;
				}
			}
		}

		return $left_menu_list;
	}

	/**
	 * убираем уволенных в зависимости от времени их увольнения
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main[] $user_list
	 * @param array                                   $left_menu_list
	 *
	 * @return array
	 */
	protected static function _prepareDismissedUserForTime(array $left_menu_list, array $user_list):array {

		// получаем временную метку увольнения, менее которой не нужно отдавать уволенных
		$not_added_dismissed_user_timestamp = time() - self::_TIME_NOT_ADDED_DISMISSED_USER;

		// получаем список уволенных пользователей, которые не должны быть в ответе
		$not_added_dismissed_user_id_list = [];
		foreach ($user_list as $user) {

			$dismissed_at = $user->left_at;

			if (
				Member::isDisabledProfile($user->role)
				&& $dismissed_at < $not_added_dismissed_user_timestamp && $dismissed_at !== 0
			) {

				$not_added_dismissed_user_id_list[] = $user->user_id;
			}
		}

		// убираем записи левого меню с пользователями, время увольнение которых больше необходимого
		foreach ($left_menu_list as $key => $left_menu_row) {

			foreach ($not_added_dismissed_user_id_list as $not_added_dismissed_user_id) {

				if ((int) $left_menu_row["opponent_user_id"] === $not_added_dismissed_user_id) {
					unset($left_menu_list[$key]);
				}
			}
		}

		// убираем пропуски по ключам в массиве
		return array_values($left_menu_list);
	}

	/**
	 * помечаем прочитанными все диалоги пользователя
	 *
	 * @param int    $user_id
	 * @param string $local_date
	 * @param string $local_time
	 * @param int    $filter_favorites
	 *
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws BlockException
	 * @throws \parseException
	 */
	public static function doReadAll(int $user_id, string $local_date, string $local_time, int $filter_favorites = 0):void {

		Domain_Conversation_Action_DoReadAll::do($user_id, $local_date, $local_time, $filter_favorites);
	}

	/**
	 *
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public static function getAllowedConversationsForAddMessage(array $conversation_key_list, int $user_id):array {

		$conversation_map_list = Domain_Conversation_Action_GetMapFromConversationKey::do($conversation_key_list);

		// проверяем присланный список диалогов
		if (count($conversation_map_list) < 1 || count($conversation_map_list) > self::_MAX_GET_ALLOWED_CONVERSATIONS_FOR_ADD_MESSAGE_COUNT) {

			Gateway_Bus_Statholder::inc("conversations", "row590");
			throw new ParamException("passed invalid conversation list");
		}

		// получаем список мет запрошенных диалогов
		$meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		// получаем всех пользователей одним походом в кэш и групируем их по id
		$opponent_user_info_list               = Domain_Conversation_Action_GetOpponentUserInfoListFromConversationList::do($user_id, $meta_list);
		$opponent_user_info_list_grouped_by_id = [];
		foreach ($opponent_user_info_list as $v) {
			$opponent_user_info_list_grouped_by_id[$v->user_id] = $v;
		}

		$output = [
			"allowed_list"             => (array) [],
			"not_member_list"          => (array) [],
			"disabled_list"            => (array) [],
			"account_deleted_list"     => (array) [],
			"blocked_by_opponent_list" => (array) [],
			"blocked_by_me_list"       => (array) [],
		];

		// проходимся по всей мете
		foreach ($meta_list as $v) {
			$output = self::_addToOutputForGetAllowedConversationsForAddMessage($user_id, $output, $v, $opponent_user_info_list_grouped_by_id);
		}
		return $output;
	}

	/**
	 * получаем ответ для getAllowedConversationsForAddMessage
	 *
	 * @throws \parseException
	 * @long - switch..case
	 */
	protected static function _addToOutputForGetAllowedConversationsForAddMessage(int   $user_id, array $output, array $meta_row,
														array $opponent_user_info_list_grouped_by_id):array {

		switch ((int) $meta_row["type"]) {

			case CONVERSATION_TYPE_SINGLE_DEFAULT:

				$opponent_user_id = Type_Conversation_Meta_Users::getOpponentId($user_id, $meta_row["users"]);
				$opponent_info    = $opponent_user_info_list_grouped_by_id[$opponent_user_id];
				$allow_status     = Type_Conversation_Utils::getAllowStatusByUserInfo((int) $meta_row["allow_status"], $meta_row["extra"], $opponent_info);
				switch ($allow_status) {

					case Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DISABLED:

						$output["disabled_list"][] = (string) \CompassApp\Pack\Conversation::doEncrypt($meta_row["conversation_map"]);
						break;
					case Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DELETED:

						$output["account_deleted_list"][] = (string) \CompassApp\Pack\Conversation::doEncrypt($meta_row["conversation_map"]);
						break;
					default:
						$output["allowed_list"][] = (string) \CompassApp\Pack\Conversation::doEncrypt($meta_row["conversation_map"]);
				}
				break;

			case CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT:
			case CONVERSATION_TYPE_SINGLE_NOTES:
				$output["allowed_list"][] = (string) \CompassApp\Pack\Conversation::doEncrypt($meta_row["conversation_map"]);
				break;

			case CONVERSATION_TYPE_GROUP_DEFAULT:
			case CONVERSATION_TYPE_GROUP_RESPECT:
			case CONVERSATION_TYPE_GROUP_HIRING:
			case CONVERSATION_TYPE_GROUP_GENERAL:
			case CONVERSATION_TYPE_GROUP_SUPPORT:

				if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {

					$output["not_member_list"][] = (string) \CompassApp\Pack\Conversation::doEncrypt($meta_row["conversation_map"]);
					break;
				}
				$output["allowed_list"][] = (string) \CompassApp\Pack\Conversation::doEncrypt($meta_row["conversation_map"]);
		}

		return $output;
	}

	/**
	 * Прочитать чат
	 *
	 * @param int    $user_id
	 * @param int    $member_role
	 * @param int    $member_permissions
	 * @param string $conversation_map
	 * @param string $message_map
	 * @param string $local_date
	 * @param string $local_time
	 *
	 * @throws BusFatalException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \busException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws cs_LeftMenuRowIsNotExist
	 */
	public static function doRead(int $user_id, int $member_role, int $member_permissions, string $conversation_map, string $message_map, string $local_date, string $local_time):void {

		$message = [];

		if ($message_map !== "") {

			$block_id  = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);
			$block_row = Gateway_Db_CompanyConversation_MessageBlock::getOne($conversation_map, $block_id);
			$message   = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

			$message_type = Type_Conversation_Message_Main::getHandler($message)::getType($message);

			if ($message_type == CONVERSATION_MESSAGE_TYPE_SYSTEM) {
				throw new ParamException("try to read system message");
			}
		}

		[$left_menu_row, $was_unread] = Domain_Conversation_Action_SetAsRead::do($user_id, $member_role, $member_permissions, $conversation_map, $message);

		if ($was_unread) {

			// обновляем badge с непрочитанными для пользователя
			$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, [$conversation_map], true);
			Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, (string) $user_id, [], $extra);

			// приводим левое меню к формату под клиентов
			$temp                   = Type_Conversation_Utils::prepareLeftMenuForFormat($left_menu_row);
			$prepared_left_menu_row = Apiv1_Format::leftMenu($temp);

			// получаем мету левого меню, откуда мы получае количество непрочитанных чатов и сообщений
			$left_menu_meta = Domain_User_Action_Conversation_GetLeftMenuMeta::do($user_id);

			// отправляем ws ивент о прочтении
			Gateway_Bus_Sender::conversationRead($user_id, $message_map, $prepared_left_menu_row, $left_menu_meta);

			// инкрементим действия
			Domain_User_Action_IncActionCount::incConversationRead($user_id, $conversation_map);
		}

		// добавляем пользователю экранное время
		Domain_User_Action_AddScreenTime::do($user_id, $local_date, $local_time);
	}

	/**
	 * Пометить чат непрочитанным
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 *
	 * @return int
	 * @throws cs_LeftMenuRowIsNotExist
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 */
	public static function setAsUnread(int $user_id, string $conversation_map):int {

		Domain_Conversation_Action_SetAsUnread::do($user_id, $conversation_map);

		// обновляем badge с непрочитанными для пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, (string) $user_id, [], $extra);

		// получаем общее количество непрочитанных
		$meta = Domain_User_Action_Conversation_GetLeftMenuMeta::do($user_id);

		// отправляем ws ивент о пометке непрочитанным
		Gateway_Bus_Sender::conversationMarkedAsUnread($user_id, $conversation_map, $meta);

		return $meta["left_menu_version"];
	}

	/**
	 * Получить приглашенных в группу
	 *
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 */
	public static function getInvited(int $user_id, int $user_role, int $method_version, string $conversation_map):array {

		Member::assertUserNotGuest($user_role);

		// получаем мету диалога
		$meta_row = Domain_Conversation_Action_GetMeta::do($conversation_map);

		// выбрасываем ошибку, если диалог не является групповым
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::GET_INVITED_FROM_CONVERSATION);

		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			throw new ParamException("User is not group member");
		}

		if ($method_version >= 2 && !Type_Conversation_Meta_Users::isOwnerMember($user_id, $meta_row["users"])) {
			Domain_Member_Entity_Permission::check($user_id, Permission::IS_SHOW_GROUP_MEMBER_ENABLED);
		}

		return Domain_Conversation_Action_GetInvitedList::do($conversation_map);
	}

	/**
	 * Получить общие группы
	 *
	 * @param int $user_id
	 * @param int $opponent_user_id
	 *
	 * @return array
	 * @throws cs_IncorrectUserId
	 */
	public static function getShared(int $user_id, int $opponent_user_id):array {

		// проверяем opponent_user_id
		if (!Type_Api_Validator::isCorrectUserId($opponent_user_id)) {
			throw new cs_IncorrectUserId();
		}

		// получаем список общих с пользователем групповых диалогов
		return Domain_Conversation_Action_GetShared::do($user_id, $opponent_user_id);
	}

	/**
	 * отправить собеседнику контакты на пользователей
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param array  $share_user_id_list
	 *
	 * @return array
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws cs_IncorrectUserIdList
	 * @throws cs_PlatformNotFound
	 * @throws cs_UserIdListIsNotCompanyMember
	 * @throws cs_UserIsNotCompanyMember
	 * @throws cs_UserIsNotMember
	 */
	public static function shareMember(int $user_id, string $conversation_map, array $share_user_id_list):array {

		//проверяем присланный список контактов
		if (count($share_user_id_list) < 1 || count($share_user_id_list) > self::_MAX_SEND_CONTACT_USER_ID_COUNT) {
			throw new cs_IncorrectUserIdList();
		}

		// выбрасываем ошибку, если диалог не позволяет совершать данные действие
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::SHARE_MEMBER_FROM_CONVERSATION);

		// проверяем наш пользователь состоит в данном сингл диалоге
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			throw new cs_UserIsNotMember();
		}

		// получаем user_id пользователей
		$opponent_user_id = Type_Conversation_Meta_Users::getOpponentId($user_id, $meta_row["users"]);
		$user_id_list     = $share_user_id_list;
		$user_id_list[]   = $opponent_user_id;
		$user_info_list   = Gateway_Bus_CompanyCache::getShortMemberList($user_id_list);
		if (!isset($user_info_list[$opponent_user_id]) || $user_info_list[$opponent_user_id]->role === Member::ROLE_LEFT) {
			throw new cs_UserIsNotCompanyMember();
		}

		// проверяем что себя и собеседника нету в списке контактов
		if (in_array($user_id, $share_user_id_list) || in_array($opponent_user_id, $share_user_id_list)) {
			throw new cs_IncorrectUserIdList();
		}

		// проверяем список пользователей которыми хотим поделиться контактами
		self::_checkShareUserIdList($share_user_id_list, $user_info_list);

		// отправляем сообщение с контактами в диалог
		$message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSharedMember($user_id, $share_user_id_list, Type_Api_Platform::getPlatform());
		$message = Helper_Conversations::addMessage(
			$meta_row["conversation_map"],
			$message,
			$meta_row["users"],
			(int) $meta_row["type"],
			$meta_row["conversation_name"],
			$meta_row["extra"]
		);

		return Type_Conversation_Message_Main::getHandler($message)::prepareForFormatLegacy($message, $user_id);
	}

	/**
	 * Проверяем список пользователей-контактов кем хотим поделиться
	 *
	 * @param array $share_user_id_list
	 * @param array $user_info_list
	 *
	 * @throws cs_IncorrectUserIdList
	 * @throws cs_UserIdListIsNotCompanyMember
	 */
	protected static function _checkShareUserIdList(array $share_user_id_list, array $user_info_list):void {

		// проверяем значения user_id
		if (min($share_user_id_list) < 1) {
			throw new cs_IncorrectUserIdList();
		}

		// проверяем что нет повторящихся user_id в массиве
		if (count(array_unique($share_user_id_list)) != count($share_user_id_list)) {
			throw new cs_IncorrectUserIdList();
		}

		// проверяем каждого пользователя из списка которым хотим поделиться контактом
		$not_member_user_id_list = [];
		foreach ($share_user_id_list as $share_user_id) {

			if (!isset($user_info_list[$share_user_id]) || $user_info_list[$share_user_id]->role === Member::ROLE_LEFT) {
				$not_member_user_id_list[] = $share_user_id;
			}
		}

		// если есть пользователи не состоящие в компании
		if (count($not_member_user_id_list) > 0) {
			throw new cs_UserIdListIsNotCompanyMember($not_member_user_id_list);
		}
	}

	/**
	 * Добавляем реакцию к сообщению
	 */
	public static function addReaction(string $message_map, string $reaction_name, int $user_id, bool $is_restricted_access):void {

		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			throw new ParamException("The message is not from conversation");
		}

		$reaction_name = Type_Conversation_Reaction_Main::getReactionNameIfExist($reaction_name);
		if (mb_strlen($reaction_name) < 1) {
			throw new ParamException(__CLASS__ . ": reaction does not exist");
		}

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);

		// если к пространству ограничен доступ, и пытаемся взаимодействовать не с чатом поддержки - выкидываем ошибку
		if ($is_restricted_access && !Type_Conversation_Meta::isGroupSupportConversationType((int) $meta_row["type"])) {
			throw new Domain_Conversation_Exception_TariffUnpaid("tariff_unpaid");
		}

		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::REACTION_TO_MESSAGE_FROM_CONVERSATION);

		// если пользователь не является участником группы
		Type_Conversation_Meta_Users::assertIsMember($user_id, $meta_row["users"]);

		Helper_Conversations::checkIsAllowed($meta_row["conversation_map"], $meta_row, $user_id);
		Domain_Conversation_Action_Message_AddReaction::do($message_map, $meta_row["conversation_map"], $meta_row, $reaction_name, $user_id);
	}

	/**
	 * Удаляем реакцию с сообщения
	 */
	public static function removeReaction(string $message_map, string $reaction_name, int $user_id, bool $is_restricted_access):void {

		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			throw new ParamException("The message is not from conversation");
		}
		$reaction_name = Type_Conversation_Reaction_Main::getReactionNameIfExist($reaction_name);
		if (mb_strlen($reaction_name) < 1) {
			throw new ParamException(__CLASS__ . ": reaction does not exist");
		}

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);

		// если к пространству ограничен доступ, и пытаемся взаимодействовать не с чатом поддержки - выкидываем ошибку
		if ($is_restricted_access && !Type_Conversation_Meta::isGroupSupportConversationType((int) $meta_row["type"])) {
			throw new Domain_Conversation_Exception_TariffUnpaid("tariff_unpaid");
		}

		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::REACTION_TO_MESSAGE_FROM_CONVERSATION);
		Type_Conversation_Meta_Users::assertIsMember($user_id, $meta_row["users"]);

		Helper_Conversations::checkIsAllowed($meta_row["conversation_map"], $meta_row, $user_id);

		Domain_Conversation_Action_Message_RemoveReaction::do($message_map, $meta_row["conversation_map"], $reaction_name, $user_id, $meta_row["users"]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * если чат нужно размьютить
	 *
	 * @throws \parseException
	 */
	protected static function _setUnmutedIfNeed(int $user_id, string $conversation_map, array $left_menu_row):array {

		// если мьютили диалог
		$set = [];
		if (isset($left_menu_row["user_id"]) &&
			($left_menu_row["muted_until"] > 0 || $left_menu_row["is_muted"] > 0)) {

			$set = [
				"is_muted"    => 0,
				"muted_until" => 0,
			];

			// со стороны оппонента - размьючиваем
			Domain_Conversation_Entity_Dynamic::setUnmuted($conversation_map, $user_id);

			// шлем эвент что размьютитили чат
			Gateway_Bus_Sender::conversationMutedChanged($user_id, $conversation_map, 0, 0);
		}

		return $set;
	}
}
