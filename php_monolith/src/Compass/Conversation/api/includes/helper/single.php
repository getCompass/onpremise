<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * хелпер для single диалогов
 */
class Helper_Single {

	/**
	 * Создает сингл диалог, если не создан, возвращает мету диалога
	 *
	 * @param int  $user_id
	 * @param int  $opponent_user_id
	 * @param bool $is_hidden_for_user
	 * @param bool $is_hidden_for_opponent
	 * @param bool $is_enable_antispam
	 * @param int  $method_version
	 *
	 * @long Много проверок
	 * @return array
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws Domain_Conversation_Exception_Guest_AttemptInitialConversation
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \returnException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 */
	public static function createIfNotExist(int $user_id, int $opponent_user_id, bool $is_hidden_for_user = false, bool $is_hidden_for_opponent = true, bool $is_enable_antispam = false, int $method_version = 1, bool $is_forcing_creation = false):array {

		// получаем информацию об участниках диалога
		/** @var \CompassApp\Domain\Member\Struct\Main[] $users_info_list */
		[$users_info_list, $initiator_npc_type] = self::_tryGetUsersInfoOnCreateSingle($user_id, $opponent_user_id);

		// проверяем существование диалога, создаем если нет
		$conversation_map = Type_Conversation_Single::getMapByUsers($user_id, $opponent_user_id);

		// создаем диалог, если его не было
		if ($conversation_map === false) {

			if ($is_enable_antispam) {
				Type_Antispam_User::throwIfBlocked(
					$user_id, Type_Antispam_User::CONVERSATIONS_ADDSINGLE, "conversations", "row450");
			}

			// выбрасываем исключение, если гость пытается инициализировать диалог
			if (!$is_hidden_for_user) {
				Helper_Conversations::throwIfGuestAttemptToInitialConversation($initiator_npc_type, $user_id, $opponent_user_id, $users_info_list);
			}

			if ($method_version >= 2) {

				// выполняем проверку только если не требуется профорсировать создание диалога
				!$is_forcing_creation && Domain_Member_Entity_Permission::check($user_id, Permission::IS_ADD_SINGLE_ENABLED);
			}

			// если инициатор диалога - гость, или оппонент диалога покинул группу или удалил аккаунт
			// то allow_status необходимо проверить
			// в остальных случаях зелёный свет
			$is_initiator_guest  = $users_info_list[$user_id]->role == Member::ROLE_GUEST;
			$is_opponent_left    = $users_info_list[$opponent_user_id]->role == Member::ROLE_LEFT;
			$is_opponent_deleted = \CompassApp\Domain\Member\Entity\Extra::getIsDeleted($users_info_list[$opponent_user_id]->extra);

			$allow_status = $is_initiator_guest || $is_opponent_left || $is_opponent_deleted ? ALLOW_STATUS_NEED_CHECK : ALLOW_STATUS_GREEN_LIGHT;

			// создаем диалог
			$meta_row = self::_createMeta(
				CONVERSATION_TYPE_SINGLE_DEFAULT, $user_id, $initiator_npc_type, $users_info_list[$opponent_user_id], $allow_status
			);
		} else {

			$meta_row = Type_Conversation_Meta::get($conversation_map);

			if ($meta_row["allow_status"] == ALLOW_STATUS_NEED_CHECK) {
				Helper_Conversations::throwIfGuestAttemptToInitialConversation($initiator_npc_type, $user_id, $opponent_user_id, $users_info_list);
			}

			// выполняем проверку только если не требуется профорсировать создание диалога
			!$is_forcing_creation && Domain_Member_Entity_Permission::checkSingle($user_id, $method_version, $conversation_map);
		}

		// всегда привязываем обоих пользователей, но с разными флагами
		$meta_row["users"] = Type_Conversation_Single::attachUser($meta_row["conversation_map"], $user_id, $opponent_user_id, $meta_row, $is_hidden_for_user);
		$meta_row["users"] = Type_Conversation_Single::attachUser($meta_row["conversation_map"], $opponent_user_id, $user_id, $meta_row, $is_hidden_for_opponent);

		// если чата не было - логируем создание
		if ($conversation_map === false) {
			Type_User_ActionAnalytics::send($user_id, Type_User_ActionAnalytics::ADD_SINGLE);
		}
		return $meta_row;
	}

	/**
	 * создает диалог с системным ботом
	 * диалог с системным ботом должен попасть в избранное пользователя
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 */
	public static function createSingleWithSystemBotIfNotExist(int $bot_user_id, int $opponent_user_id):array {

		// указываем тип будущего диалога
		$conversation_type = CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT;

		// получаем информацию об участниках диалога
		[$users_info_list, $initiator_npc_type] = self::_tryGetUsersInfoOnCreateSingle($bot_user_id, $opponent_user_id, $conversation_type);

		// проверяем существование диалога
		$conversation_map = Type_Conversation_Single::getMapByUsers($bot_user_id, $opponent_user_id);

		// если диалог уже существует, то просто возвращаем его
		if ($conversation_map !== false) {

			$meta_row = Type_Conversation_Meta::get($conversation_map);
			Helper_Conversations::checkIsAllowed($meta_row["conversation_map"], $meta_row, $bot_user_id);
			$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getOne($opponent_user_id, $meta_row["conversation_map"]);
			if (!isset($left_menu_row["is_favorite"])) {

				Type_System_Admin::log(
					"conversation_with_bot_not_found",
					"user id: $opponent_user_id, bot id: $bot_user_id, conversation map: $conversation_map"
				);
			} else {

				if (!$left_menu_row["is_favorite"]) {
					Helper_Conversations::addToFavorite($opponent_user_id, $left_menu_row);
				}
			}
			return $meta_row;
		}

		// создаем новый, отмечаем как диалог с ботом
		$meta_row = self::_createMeta($conversation_type, $bot_user_id, $initiator_npc_type, $users_info_list[$opponent_user_id]);

		// привязываем только собеседника системного бота
		// для системного бота в левом меню записи не нужны
		$meta_row["users"] = Type_Conversation_Single::attachUser($meta_row["conversation_map"], $opponent_user_id, $bot_user_id, $meta_row, false);

		// цепляем пользователя и добавляем в избранное пользователю
		Helper_Conversations::checkIsAllowed($meta_row["conversation_map"], $meta_row, $bot_user_id);

		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getOne($opponent_user_id, $meta_row["conversation_map"]);
		if (isset($left_menu_row["user_id"])) {
			Helper_Conversations::addToFavorite($opponent_user_id, $left_menu_row);
		} else {

			Type_System_Admin::log(
				"left_menu_row_is_empty",
				"user id: $opponent_user_id, bot id: $bot_user_id, conversation map: $meta_row[conversation_map]"
			);
		}
		return $meta_row;
	}

	// удаляет single диалог из левого меню пользователя
	public static function remove(int $user_id, string $conversation_map, int $opponent_user_id, bool $is_need_unfollow_threads = false):void {

		// помечаем диалог скрытым
		$clear_until = time();
		Type_Conversation_LeftMenu::hideAndClearForUser($user_id, $conversation_map, $clear_until);

		// пересчитываем total_unread_count
		Type_Conversation_LeftMenu::recountTotalUnread($user_id);

		// если нужно отписывать от тредов
		if ($is_need_unfollow_threads) {
			Type_Phphooker_Main::doUnfollowThreadListByConversationMap($user_id, $conversation_map);
		}

		// обновляем бадж пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, [$conversation_map], true);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);

		// обновляем время очистки диалога
		self::_updateClearDate($user_id, $conversation_map, $clear_until);

		// отправляем событие пользователю
		Gateway_Bus_Sender::conversationSingleRemoved($user_id, $conversation_map);

		// завершаем звонок между пользователями, если таковой имелся
		self::_doFinishSingleCallIfExist($user_id, $opponent_user_id);

		// удаляем данные диалога для пользователя из поиска
		Domain_Search_Entity_Conversation_Task_Clear::queue($conversation_map, [$user_id]);
	}

	// обновляем время, когда был очищен диалог
	protected static function _updateClearDate(int $user_id, string $conversation_map, int $clear_until):void {

		// обновляем user_clear_info для пользователя
		Domain_Conversation_Entity_Dynamic::setClearUntil($conversation_map, $user_id, $clear_until);

		// обновляем conversation_clear_info для пользователя
		Domain_Conversation_Entity_Dynamic::setClearUntilConversationForUserIdList($conversation_map, [$user_id], $clear_until, false);
	}

	// завершаем звонок между пользователями, если таковой имелся
	protected static function _doFinishSingleCallIfExist(int $user_id, int $opponent_user_id):void {

		return;

		// отправляем сокет-запрос на php_speaker для завершения звонка
		$ar_post = [
			"opponent_user_id" => $opponent_user_id,
		];
		[$status] = Gateway_Socket_Speaker::doCall("calls.doFinishSingleCall", $ar_post, $user_id);

		// если вернулся не ok
		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": socket calls.doFinishSingleCall call returns bad response");
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем информацию об участниках диалога
	 *
	 * @return array
	 * @throws ParamException если один из участников не будет найден
	 */
	protected static function _tryGetUsersInfoOnCreateSingle(int $user_id, int $opponent_user_id, int $conversation_type = CONVERSATION_TYPE_SINGLE_DEFAULT):array {

		// если тип диалога с системным ботом, то не лезем в company_cache
		$user_id_list = [$opponent_user_id];
		if (!Type_Conversation_Meta::isSystemBotConversationType($conversation_type)) {
			$user_id_list[] = $user_id;
		}

		// получаем user_info собеседника, проверяем что он существует
		$users_info_list = self::_tryGetUserInfo($user_id_list);

		// если системный бот, то получаем рандомный тип для нашего пользователя, иначе достаем из информации о пользователе
		if (Type_Conversation_Meta::isSystemBotConversationType($conversation_type)) {
			$initiator_npc_type = Type_User_Main::getSystemBotNpcType();
		} else {
			$initiator_npc_type = $users_info_list[$user_id]->npc_type;
		}

		return [$users_info_list, $initiator_npc_type];
	}

	/**
	 * пробуем получить информацию о пользователе
	 *
	 * @param array $user_id_list
	 *
	 * @return \CompassApp\Domain\Member\Struct\Main[]
	 */
	protected static function _tryGetUserInfo(array $user_id_list):array {

		$user_info_list = Gateway_Bus_CompanyCache::getMemberList($user_id_list);

		foreach ($user_id_list as $user_id) {

			if (!isset($user_info_list[$user_id])) {
				throw new ParamException(__METHOD__ . ": user not found");
			}
		}
		return $user_info_list;
	}

	// создаем сингл диалог если его никогда не существовало
	protected static function _createMeta(int $conversation_type, int $user_id, int $user_npc, \CompassApp\Domain\Member\Struct\Main $opponent_user_info, int $allow_status = ALLOW_STATUS_GREEN_LIGHT):array {

		// проверяем, что можно создать сингл с указанным юзером
		self::_throwIfOpponentIsNotValidForAction($opponent_user_info, Type_User_Action::CREATE_SINGLE);

		try {

			// пытаемся создать single диалога
			$meta_row = Type_Conversation_Single::add(
				$conversation_type, $user_id, $user_npc, $opponent_user_info->user_id, $opponent_user_info->npc_type, $allow_status
			);
		} catch (cs_Conversation_SingleIsExist $e) {

			// отлавливаем тот случай, когда в один момент времени было отправлено несколько API запросов на создание диалога
			// между одними и теми же пользователями
			// в таком случае получаем meta_row уже существующего single диалога
			$conversation_map = $e->getConversationMap();
			$meta_row         = Type_Conversation_Meta::get($conversation_map);
		}

		return $meta_row;
	}

	// --------------
	// PROTECTED
	// --------------

	// проверяет, подходит ли пользователь для выполнения указанного действия НАД ним
	protected static function _throwIfOpponentIsNotValidForAction(\CompassApp\Domain\Member\Struct\Main $opponent_info, string $action):void {

		// проверяем тип пользователя и возможность выполения действия НАД ним
		if (!Type_User_Action::isValidForAction($opponent_info->npc_type, $action)) {
			throw new cs_RequestedActionIsNotAble("opponent is not valid for this action " . Type_User_Action::getIsValidForActionErrorCode($action));
		}
	}
}
