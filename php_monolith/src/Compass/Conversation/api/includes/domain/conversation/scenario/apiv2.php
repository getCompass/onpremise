<?php

declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Extra;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\AccountDeleted;
use CompassApp\Domain\Member\Exception\IsLeft;
use CompassApp\Domain\Member\Struct\Short;

/**
 * API-сценарии домена «диалоги».
 */
class Domain_Conversation_Scenario_Apiv2 {

	/**
	 * метод для получения информации о диалогах
	 *
	 * @param int   $user_id
	 * @param array $conversation_map_list
	 * @param bool  $is_restricted_access
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_TariffUnpaid
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_IncorrectConversationMapList
	 * @long разделение диалогов по разрешенным/не разрешенным
	 */
	public static function get(int $user_id, array $conversation_map_list, bool $is_restricted_access, int $user_role, int $user_permissions):array {

		$conversation_map_list = Domain_Conversation_Entity_Sanitizer::sanitizeConversationMapList($conversation_map_list);

		/** @var Struct_Db_CompanyConversation_ConversationMeta[] $conversation_meta_list */
		$conversation_meta_list = Gateway_Db_CompanyConversation_ConversationMeta::getAll($conversation_map_list, true);

		$users                                         = [];
		$allowed_conversation_map_list                 = [];
		$temp_allowed_conversation_list                = [];
		$not_allowed_conversation_map_list             = [];
		$need_grant_admin_rights_conversation_map_list = [];

		// сначала проверяем все диалоги по isMember
		$is_have_group_administrator_rights = Permission::isGroupAdministrator($user_role, $user_permissions);
		foreach ($conversation_meta_list as $item) {

			// запросить можно только группу поддержки, если доступ ограничен
			if ($is_restricted_access && !Type_Conversation_Meta::isGroupSupportConversationType($item->type)) {

				$not_allowed_conversation_map_list[] = $item->conversation_map;
				continue;
			}

			if (!Type_Conversation_Meta::isSubtypeOfPublicGroup($item->type) && !Type_Conversation_Meta_Users::isMember($user_id, $item->users)) {

				$not_allowed_conversation_map_list[] = $item->conversation_map;
				continue;
			}

			// если есть права "админ во всех группах"
			// и это группа и пользователь не является админом в ней, то добавляем в массив на выдачу прав админа в этой группе
			if ($is_have_group_administrator_rights
				&& Type_Conversation_Meta::isSubtypeOfGroup($item->type) && !Type_Conversation_Meta_Users::isGroupAdmin($user_id, $item->users)) {

				$need_grant_admin_rights_conversation_map_list[] = $item->conversation_map;
			}
			$temp_allowed_conversation_list[$item->conversation_map] = $item;
		}

		if ($is_restricted_access && $not_allowed_conversation_map_list !== [] && $temp_allowed_conversation_list === []) {
			throw new Domain_Conversation_Exception_TariffUnpaid("tariff unpaid");
		}

		// затем из разрешенных чекаем, нет ли среди них скрытых синглов
		$temp_allowed_conversation_map_list = array_keys($temp_allowed_conversation_list);
		$left_menu_list                     = Gateway_Db_CompanyConversation_UserLeftMenu::getList($user_id, $temp_allowed_conversation_map_list, true);

		foreach ($left_menu_list as $item) {

			$is_hidden = $item["is_hidden"] ?? 0;
			$is_leaved = $item["is_leaved"] ?? 0;
			if ($is_hidden == 1 || $is_leaved == 1) {

				$not_allowed_conversation_map_list[] = $item["conversation_map"];
				unset($temp_allowed_conversation_list[$item["conversation_map"]]);
			}
		}

		// оставшиеся разрешенные добавляем в финальный массив
		foreach ($temp_allowed_conversation_list as $item) {

			$users                           = array_merge($users, array_keys($item->users));
			$allowed_conversation_map_list[] = $item->conversation_map;
		}

		$prepared_conversation_meta_list = [];
		$dynamic_list                    = Gateway_Db_CompanyConversation_ConversationDynamic::getAll($allowed_conversation_map_list, true);

		// готовим последние сообщения
		$last_read_messages = Domain_Conversation_Action_PrepareLastReadMessages::do($dynamic_list);

		foreach ($allowed_conversation_map_list as $conversation_map) {

			$meta = $conversation_meta_list[$conversation_map];

			$prepared_conversation_meta = Domain_Conversation_Entity_ConversationMeta::prepareForFrontend(
				$user_id, $meta, $dynamic_list[$conversation_map], $last_read_messages[$conversation_map]
			);

			// последних просмотревших надо также приклеить в users, они вполне могли выйти из чата
			$users                             = array_merge($users, $last_read_messages[$conversation_map]->first_read_participant_list);
			$prepared_conversation_meta_list[] = $prepared_conversation_meta;
		}

		// делаем пользователя админом в тех группах, в которых у него нет админ прав
		if (count($need_grant_admin_rights_conversation_map_list) > 0) {
			Type_Phphooker_Main::grantAdminRightsForConversationList($user_id, $user_role, $user_permissions, $need_grant_admin_rights_conversation_map_list);
		}

		return [$prepared_conversation_meta_list, $not_allowed_conversation_map_list, $users];
	}

	/**
	 * Получаем общие с пользователем групповые диалоги
	 *
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
	 * добавляем пользователя в группы
	 *
	 * @param int   $inviter_user_id
	 * @param int   $inviter_role
	 * @param int   $inviter_permissions
	 * @param int   $participant_user_id
	 * @param array $conversation_key_list
	 *
	 * @return array
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws ParamException
	 * @throws BusFatalException
	 * @throws AccountDeleted
	 * @throws IsLeft
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws cs_IncorrectConversationMapList
	 * @throws cs_IncorrectUserId
	 * @long
	 */
	public static function addParticipant(int $inviter_user_id, int $inviter_role, int $inviter_permissions, int $participant_user_id, array $conversation_key_list):array {

		// проверяем opponent_user_id
		if (!Type_Api_Validator::isCorrectUserId($participant_user_id)) {
			throw new cs_IncorrectUserId();
		}

		// проверяем права нашего пользователя
		Permission::assertCanEditMemberProfile($inviter_role, $inviter_permissions);

		// проверяем, что участник в компании
		$participant = Gateway_Bus_CompanyCache::getMember($participant_user_id);
		Extra::assertIsNotDeleted($participant->extra);
		Member::assertIsNotLeftRole($participant->role);

		if (count($conversation_key_list) > 100) {
			throw new ParamException("Passed conversation_key_list biggest than max");
		}

		$conversation_map_list = [];
		foreach ($conversation_key_list as $conversation_key) {
			$conversation_map_list[] = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);
		}

		$conversation_map_list  = Domain_Conversation_Entity_Sanitizer::sanitizeConversationMapList($conversation_map_list);
		$conversation_meta_list = Gateway_Db_CompanyConversation_ConversationMeta::getAll($conversation_map_list, true);

		$not_allowed_conversation_map_list = [];
		$not_group_conversation_list       = [];
		$temp_allowed_conversation_list    = [];

		// проверяем диалоги
		foreach ($conversation_meta_list as $item) {

			// если тип диалога не относится к группе
			if (!Type_Conversation_Meta::isSubtypeOfGroup($item->type)) {

				$not_group_conversation_list[] = $item->conversation_map;
				continue;
			}

			// если приглашающей не имеет доступа к группе
			if (!Type_Conversation_Meta_Users::isMember($inviter_user_id, $item->users)) {

				$not_allowed_conversation_map_list[] = $item->conversation_map;
				continue;
			}

			// если это группа поддержки
			if (Type_Conversation_Meta::isGroupSupportConversationType($item->type)) {

				$not_allowed_conversation_map_list[] = $item->conversation_map;
				continue;
			}

			// если приглашаемый уже участник группы
			if (Type_Conversation_Meta_Users::isMember($participant_user_id, $item->users)) {
				continue;
			}

			// приглашающий может пригласить пользователей в диалог?
			if (!Type_Conversation_Meta_Users::isGroupAdmin($inviter_user_id, $item->users)) {

				$not_allowed_conversation_map_list[] = $item->conversation_map;
				continue;
			}

			$temp_allowed_conversation_list[$item->conversation_map] = $item;
		}

		$temp_allowed_conversation_map_list = array_keys($temp_allowed_conversation_list);
		$left_menu_list                     = Gateway_Db_CompanyConversation_UserLeftMenu::getList($inviter_user_id, $temp_allowed_conversation_map_list, true);
		foreach ($left_menu_list as $item) {

			$is_hidden = $item["is_hidden"] ?? 0;
			$is_leaved = $item["is_leaved"] ?? 0;
			if ($is_hidden == 1 || $is_leaved == 1) {

				$not_allowed_conversation_map_list[] = $item["conversation_map"];
				unset($temp_allowed_conversation_list[$item["conversation_map"]]);
			}
		}

		$allowed_conversation_map_list = array_keys($temp_allowed_conversation_list);

		Gateway_Event_Dispatcher::dispatch(Type_Event_Conversation_JoinToGroupList::create($participant_user_id, $allowed_conversation_map_list));

		return [$not_allowed_conversation_map_list, $not_group_conversation_list];
	}

	/**
	 * Получить список файлов в чате
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param int    $count
	 * @param int    $below_id
	 * @param array  $client_type_list
	 * @param bool   $filter_self_only
	 * @param array  $parent_type_list
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_UserIsNotMember
	 */
	public static function getFiles(int $user_id, string $conversation_map, int $count, int $below_id, array $client_type_list, bool $filter_self_only, array $parent_type_list):array {

		$type_list                  = [];
		$flipped_file_client_schema = array_flip(Domain_Conversation_Entity_File_Main::FILE_CLIENT_SCHEMA);

		foreach ($client_type_list as $type) {

			if (!isset($flipped_file_client_schema[$type])) {
				throw new ParamException("invalid type in type_list");
			}

			$type_list[] = $flipped_file_client_schema[$type];
		}

		$parent_type_list = self::_prepareParentTypeList($parent_type_list);

		// получаем мету диалога и проверяем что пользователь участник диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::GET_FILES_FROM_CONVERSATION);
		Type_Conversation_Meta_Users::assertIsMember($user_id, $meta_row["users"]);

		// получаем dynamic и время после которого файлы очищены
		$dynamic_row         = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		$user_clear_until_at = Domain_Conversation_Entity_Dynamic::getClearUntil($dynamic_row["user_clear_info"], $dynamic_row["conversation_clear_info"], $user_id);

		return Domain_Conversation_Entity_File_Main::getFilesListWitIdSort($user_id, $conversation_map, $count, $below_id,
			$filter_self_only, $type_list, $user_clear_until_at, $parent_type_list);
	}

	/**
	 * Подготовить parent_type_list, пришедшего от клиента
	 *
	 * @param array $parent_type_list
	 *
	 * @return array
	 * @throws ParamException
	 */
	protected static function _prepareParentTypeList(array $parent_type_list):array {

		$output                     = [];
		$flipped_parent_type_schema = array_flip(Domain_Conversation_Entity_File_Main::PARENT_TYPE_TO_STRING_SCHEMA);

		foreach ($parent_type_list as $type) {

			if (!isset($flipped_parent_type_schema[$type])) {
				throw new ParamException("invalid type in parent_type_list");
			}

			$output[] = $flipped_parent_type_schema[$type];
		}

		return $output;
	}

	/**
	 * Получить список превью в чате
	 *
	 * @param int    $method_version
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param int    $count
	 * @param int    $offset
	 * @param bool   $filter_self_only
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_UserIsNotMember
	 */
	public static function getPreviews(int $method_version, int $user_id, string $conversation_map, int $count, int $offset, bool $filter_self_only):array {

		// получаем мету диалога и проверяем что пользователь участник диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::GET_PREVIEWS_FROM_CONVERSATION);
		Type_Conversation_Meta_Users::assertIsMember($user_id, $meta_row["users"]);

		// получаем dynamic и время после которого превью очищены
		$dynamic_row         = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		$user_clear_until_at = Domain_Conversation_Entity_Dynamic::getClearUntil($dynamic_row["user_clear_info"], $dynamic_row["conversation_clear_info"], $user_id);

		[$sorted_list, $has_next] = Domain_Conversation_Entity_Preview_Main::getSortedList(
			$user_id, $conversation_map, $user_clear_until_at, $count, $offset, $filter_self_only);

		// если версия метода равна 1 - убираем простые превью из ответа, клиенты не умеют их обрабатывать
		if ($method_version > 1) {
			return [$sorted_list, $has_next];
		}

		$preview_map_list        = array_column($sorted_list, "preview_map");
		$preview_list            = Type_Preview_Main::getAll($preview_map_list);
		$simple_preview_list     = array_filter($preview_list, static fn(array $preview) => $preview["data"]["type"] === PREVIEW_TYPE_SIMPLE);
		$simple_preview_map_list = array_column($simple_preview_list, "preview_map", "preview_map");

		foreach ($sorted_list as $index => $conversation_preview) {

			if (isset($simple_preview_map_list[$conversation_preview->preview_map])) {
				unset($sorted_list[$index]);
			}
		}

		return [array_values($sorted_list), $has_next];
	}

	/**
	 * Отключаем уведомления
	 *
	 * @long
	 * @throws AccountDeleted
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws Domain_Userbot_Exception_DeletedStatus
	 * @throws Domain_Userbot_Exception_DisabledStatus
	 * @throws IsLeft
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \returnException
	 */
	public static function mute(int $user_id, int $opponent_user_id):void {

		if (($user_id == $opponent_user_id) || $user_id < 1 || $opponent_user_id < 1) {
			throw new ParamException("invalid user_id");
		}

		try {
			$opponent_user = Gateway_Bus_CompanyCache::getMember($opponent_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("user not found");
		}

		Extra::assertIsNotDeleted($opponent_user->extra);
		Member::assertIsNotLeftRole($opponent_user->role);
		self::_checkIfBot($opponent_user);

		// проверяем существование диалога
		$single_conversation_map = Type_Conversation_Single::getMapByUsers($user_id, $opponent_user_id);

		// создаем диалог, если его не было, иначе просто получаем существующий
		if ($single_conversation_map === false) {

			$meta_row         = Helper_Single::createIfNotExist($user_id, $opponent_user_id, true);
			$conversation_map = $meta_row["conversation_map"];
		} else {

			$conversation_map = (string) $single_conversation_map;
		}

		$is_muted       = 1;
		$time_at        = time(); // записываем текущее время
		$max_time_limit = $time_at + DAY1 * 99 + (DAY1 - 1); // максимальное значение таймера

		// выполняем действия для mute
		$new_muted_until = Domain_Conversation_Entity_Dynamic::setMuted($conversation_map, $user_id, $is_muted, 0, $max_time_limit, $time_at);

		// обновляем muted_until в left_menu пользователя
		Type_Conversation_LeftMenu::doMute($user_id, $conversation_map, (bool) $is_muted, $new_muted_until);

		// отправляем события пользователю
		Gateway_Bus_Sender::conversationMutedChanged($user_id, $conversation_map, $is_muted, $new_muted_until);
	}

	/**
	 * Проверяем, что бот рабочий
	 *
	 * @throws Domain_Userbot_Exception_DeletedStatus
	 * @throws Domain_Userbot_Exception_DisabledStatus
	 * @throws \returnException
	 */
	protected static function _checkIfBot(\CompassApp\Domain\Member\Struct\Main $opponent_user):void {

		// если это бот, то получаем его статус
		if (Type_User_Main::isUserbot($opponent_user->npc_type)) {

			$status = Gateway_Socket_Company::getUserbotStatusByUserId($opponent_user->user_id);
			switch ($status) {

				case Domain_Userbot_Entity_Userbot::STATUS_DISABLE:
					throw new Domain_Userbot_Exception_DisabledStatus("userbot is not enabled");
				case Domain_Userbot_Entity_Userbot::STATUS_DELETE:
					throw new Domain_Userbot_Exception_DeletedStatus("userbot is deleted");
			}
		}
	}

	/**
	 * Включаем уведомления
	 *
	 * @throws BusFatalException
	 * @throws ParamException
	 */
	public static function unmute(int $user_id, int $opponent_user_id):void {

		if (($user_id == $opponent_user_id) || $user_id < 1 || $opponent_user_id < 1) {
			throw new ParamException("invalid user_id");
		}

		try {
			Gateway_Bus_CompanyCache::getMember($opponent_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("user not found");
		}

		// проверяем существование диалога
		$conversation_map = Type_Conversation_Single::getMapByUsers($user_id, $opponent_user_id);
		if ($conversation_map === false) {
			return;
		}

		// включаем уведомления
		Helper_Conversations::doUnmute($user_id, $conversation_map);
	}

	/**
	 * Очищаем диалог
	 *
	 * @throws BusFatalException
	 * @throws ParamException
	 */
	public static function clearMessages(int $user_id, int $opponent_user_id):void {

		if (($user_id == $opponent_user_id) || $user_id < 1 || $opponent_user_id < 1) {
			throw new ParamException("invalid user_id");
		}

		try {
			Gateway_Bus_CompanyCache::getMember($opponent_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("user not found");
		}

		// проверяем существование диалога
		$conversation_map = Type_Conversation_Single::getMapByUsers($user_id, $opponent_user_id);
		if ($conversation_map === false) {
			return;
		}

		// получаем запись из левого меню
		$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $conversation_map);

		// чистим сообщения
		Helper_Conversations::clearMessages($user_id, $conversation_map, $left_menu_row, true, time());
	}

	/**
	 * Удаляем диалог
	 *
	 * @throws BusFatalException
	 * @throws ParamException
	 */
	public static function leave(int $user_id, int $opponent_user_id):void {

		if (($user_id == $opponent_user_id) || $user_id < 1 || $opponent_user_id < 1) {
			throw new ParamException("invalid user_id");
		}

		try {
			Gateway_Bus_CompanyCache::getMember($opponent_user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("user not found");
		}

		// проверяем существование диалога
		$conversation_map = Type_Conversation_Single::getMapByUsers($user_id, $opponent_user_id);
		if ($conversation_map === false) {
			return;
		}

		// получаем запись из левого меню, проверяем что у нас есть такой диалог
		$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $conversation_map);

		// выбрасываем ошибку, если диалог не подходит для этого действия
		Type_Conversation_Action::assertAction((int) $left_menu_row["type"], Type_Conversation_Action::REMOVE_CONVERSATION);

		// удаляем диалог
		Helper_Single::remove($user_id, $conversation_map, $opponent_user_id, true);
	}
}
