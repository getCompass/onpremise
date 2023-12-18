<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Pack\Conversation;

/**
 * Класс обработки сценариев событий.
 */
class Domain_Member_Scenario_Event {

	/**
	 * Пользователь вступил в компанию
	 *
	 * @param Struct_Event_UserCompany_UserJoinedCompany $event_data
	 *
	 * @return Type_Task_Struct_Response
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws LocaleTextNotFound
	 * @throws ParamException
	 * @throws \queryException
	 */
	#[Type_Attribute_EventListener(Type_Event_UserCompany_UserJoinedCompany::EVENT_TYPE, Type_Attribute_EventListener::TASK_QUEUE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	#[Type_Task_Attribute_Executor(Type_Event_UserCompany_UserJoinedCompany::EVENT_TYPE, Struct_Event_UserCompany_UserJoinedCompany::class)]
	public static function onUserJoinedCompany(Struct_Event_UserCompany_UserJoinedCompany $event_data):Type_Task_Struct_Response {

		try {
			$member_info = Gateway_Bus_CompanyCache::getMember($event_data->user_id);
		} catch (\cs_RowIsEmpty) {

			// если информации по пользователю нет, то значит его нет в компании - его не добавляем
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		// если это:
		// – не реальный человек
		// – уже успел покинуть пространство
		// – уже успел удалить аккаунт
		if (!\CompassApp\Domain\User\Main::isHuman($member_info->npc_type)
			|| Member::isDisabledProfile($member_info->role)
			|| \CompassApp\Domain\Member\Entity\Extra::getIsDeleted($member_info->extra)) {

			// то дальше не выполняем
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		$is_admin           = $member_info->role == Member::ROLE_ADMINISTRATOR;
		$is_company_creator = $event_data->entry_type == \CompassApp\Domain\Member\Entity\Entry::ENTRY_CREATOR_TYPE;

		// создаем диалог с отделом поддержки
		$support_conversation_map = Domain_Conversation_Action_SupportConversationCreate::do($event_data->user_id, $event_data->locale);

		// востанавливаем статус сингл диалогов на случай повторного найма
		Helper_Conversations::updateConversationListAfterUnblock($event_data->user_id);

		// добавляем пользователя в необходимые группы компании
		self::_addUserToGroupsAndSingleDialogIfNeeded($event_data);

		// если нужно добавляем пользователя в диалога найма и увольнения
		if ($is_admin) {
			Domain_Group_Action_CompanyHiringAdd::do($event_data->user_id, Type_Conversation_Meta_Users::ROLE_OWNER, $is_company_creator);
		}

		// создаем диалог в интеркоме если нужно
		self::_createConversationInIntercomIfNeed($event_data->user_id, $support_conversation_map,
			$is_company_creator, $event_data->is_need_create_intercom_conversation, $event_data->ip, $event_data->user_agent);

		// создаем для пользователя его личный чат Heroes
		Domain_Conversation_Action_PublicCreate::do($event_data->user_id);

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}

	/**
	 * добавляем пользователей в группы и диалоги
	 *
	 * @param Struct_Event_UserCompany_UserJoinedCompany $event_data
	 *
	 * @throws ParseFatalException
	 */
	protected static function _addUserToGroupsAndSingleDialogIfNeeded(Struct_Event_UserCompany_UserJoinedCompany $event_data):void {

		if ($event_data->entry_type == \CompassApp\Domain\Member\Entity\Entry::ENTRY_INVITE_LINK_TYPE) {

			// добавляем пользователя в группы из заявки
			Domain_Group_Action_CompanyRequestAdd::do(
				$event_data->user_id,
				$event_data->hiring_request["data"]["autojoin"]["group_conversation_autojoin_item_list"]
			);

			$user_list_to_create_single = $event_data->hiring_request["data"]["autojoin"]["single_conversation_autojoin_item_list"];

			// добавляем пользователя в синглы из заявки и в сингл с пригласившим
			Domain_User_Action_CompanyRequestAdd::do(
				$event_data->user_id,
				$user_list_to_create_single,
				$event_data->company_inviter_user_id
			);
		}
	}

	/**
	 * Создаем диалог в интеркоме если нужно
	 *
	 * @param int    $user_id
	 * @param string $support_conversation_map
	 * @param bool   $is_company_creator
	 * @param bool   $is_need_create_intercom_conversation
	 * @param string $ip
	 * @param string $user_agent
	 *
	 * @return void
	 */
	protected static function _createConversationInIntercomIfNeed(int    $user_id, string $support_conversation_map,
											  bool   $is_company_creator, bool $is_need_create_intercom_conversation,
											  string $ip, string $user_agent):void {

		// если автоматическое создание отключено по константе
		if (!IS_NEED_CREATE_CONVERSATION_IN_INTERCOM_ON_COMPANY_CREATE) {
			return;
		}

		// если НЕ создали чат поддержки
		// или это его не первое пространство (не нужно создавать диалог)
		if (mb_strlen($support_conversation_map) < 1 || !$is_need_create_intercom_conversation) {
			return;
		}

		// если пользователь не создатель пространства, то просто создаем контакт
		if (!$is_company_creator) {

			// создаем контакт в интеркоме
			Gateway_Socket_Intercom::createContact($user_id);
			return;
		}

		// создаем диалог в интеркоме
		Gateway_Socket_Intercom::createConversation(
			$user_id,
			COMPANY_ID,
			Conversation::doEncrypt($support_conversation_map),
			$ip,
			$user_agent
		);
	}

	/**
	 * У пользователя сменился permissions в компании
	 *
	 * @param Struct_Event_Member_PermissionsChanged $event_data
	 *
	 * @throws ParseFatalException
	 */
	#[Type_Attribute_EventListener(Type_Event_Member_PermissionsChanged::EVENT_TYPE)]
	public static function onMemberPermissionsChanged(Struct_Event_Member_PermissionsChanged $event_data):void {

		// если нужно добавляем пользователя в диалога найма и увольнения
		if (\CompassApp\Domain\Member\Entity\Permission::isJoinHiringConversationByPermissionMask(
			$event_data->role, $event_data->permissions, $event_data->before_permissions)) {

			Domain_Group_Action_CompanyHiringAdd::do($event_data->user_id, Type_Conversation_Meta_Users::ROLE_OWNER);
		}

		// если нужно убираем пользователя из диалога найма и увольнения
		if (\CompassApp\Domain\Member\Entity\Permission::isKickedHiringConversationByPermissionMask(
			$event_data->permissions, $event_data->before_permissions)) {

			Domain_Group_Action_CompanyHiringKicked::do($event_data->user_id);
		}
	}

	/**
	 * У пользователя сменилось имя в компании
	 *
	 * @param Struct_Event_Member_NameChanged $event_data
	 *
	 * @return void
	 */
	#[Type_Attribute_EventListener(Type_Event_Member_NameChanged::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onMemberChangedName(Struct_Event_Member_NameChanged $event_data):void {

		$user_id   = $event_data->user_id;
		$full_name = $event_data->full_name;

		Domain_Conversation_Action_UpdateSingleNames::do($user_id, $full_name);
	}
}
