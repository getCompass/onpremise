<?php

namespace Compass\Jitsi;

use BaseFrame\Domain\User\Avatar;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Exception\Request\ParamException;

/**
 * класс описывающий сценарии api-методов api/v2/jitsi/
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Scenario_Api {

	/**
	 * Создаем конференцию
	 *
	 * @throws BusFatalException
	 * @throws Domain_Jitsi_Exception_Conference_ConferenceIdDuplication
	 * @throws Domain_Jitsi_Exception_Conference_CreatorIsNotMemberOfSpace
	 * @throws Domain_Jitsi_Exception_Conference_GuestAccessDenied
	 * @throws Domain_Jitsi_Exception_Conference_NoCreatePermissions
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws Domain_Jitsi_Exception_UserActiveConference_UserHaveActiveConference
	 * @throws Domain_Space_Exception_NotFound
	 * @throws Domain_Space_Exception_UnexpectedStatus
	 * @throws EndpointAccessDeniedException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws RowNotFoundException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_UserNotFound
	 */
	public static function createConference(int $user_id, int $space_id, bool $is_private, bool $is_lobby, ?string $custom_conference_id = null):array {

		// проверяем наличие активной конференции
		Domain_Jitsi_Entity_UserActiveConference::throwIfUserHaveActiveConference($user_id);

		// проверяем пространство
		Domain_Jitsi_Entity_ConferenceMember_CreationAsserts::assertCreatorMemberOfSpace($user_id, $space_id);

		$space = Domain_Space_Entity_Main::get($space_id);
		Domain_Space_Entity_Status::assertActive($space);

		// проверим, что пользователь имеет права на создание конференции
		Domain_Jitsi_Entity_ConferenceMember_CreationAsserts::assertPermissions($user_id, $space_id);

		// получаем инстанс доступного jitsi сервера, для создания конференции
		$jitsi_node_config = Domain_Jitsi_Entity_Node::getRandomNode();

		// создаем конференцию
		$conference = Domain_Jitsi_Action_Conference_Create::do($user_id, $space_id, $is_private, $is_lobby, $jitsi_node_config, $custom_conference_id);

		// подготавливаем все к подключению участника к конференции
		[$conference_joining_data, $conference_member_data] = self::prepareConferenceJoiningData($user_id, $space_id, $conference);

		// помечаем конференцию активной для создателя
		Domain_Jitsi_Entity_UserActiveConference::set($user_id, $conference->conference_id);

		// получаем информацию о создателе конференции
		$conference_creator      = Gateway_Bus_PivotCache::getUserInfo($conference->creator_user_id);
		$conference_creator_data = Struct_Api_Conference_CreatorData::buildFromCache($conference_creator);

		// формируем ответ
		return [Struct_Api_Conference_Data::buildFromDB($conference), $conference_joining_data, $conference_member_data, $conference_creator_data];
	}

	/**
	 * Присоединяем участника в конференцию, если можно
	 *
	 * @throws BusFatalException
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_WrongPassword
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceIsDeleted
	 * @throws Domain_Jitsi_Exception_UserActiveConference_UserHaveActiveConference
	 * @throws EndpointAccessDeniedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws RowNotFoundException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_UserNotFound
	 */
	public static function joinConference(int $user_id, int $space_id, string $link):array {

		// парсим ссылку
		$parsed_link = Domain_Jitsi_Entity_ConferenceLink_Main::getHandlerProvider()::getByLink($link)::parse($link);

		try {

			// проверяем наличие активной конференции
			Domain_Jitsi_Entity_UserActiveConference::throwIfUserHaveActiveConference($user_id);
		} catch (Domain_Jitsi_Exception_UserActiveConference_UserHaveActiveConference $e) {

			// если пользователь имеет активную конференцию, которая отличается от той к которой пытается присоединиться
			// то пробрасываем исключение дальше
			if ($e->active_conference_id !== $parsed_link->conference_id) {
				throw $e;
			}
			// иначе не мешаем подключению
		}

		// верифицируем ссылку на конференцию
		$conference = Domain_Jitsi_Entity_Conference::verifyConferenceLink($parsed_link);

		// подготавливаем все к подключению участника к конференции
		[$conference_joining_data, $conference_member_data, $conference_creator_data] = self::prepareConferenceJoiningData($user_id, $space_id, $conference);

		// пересоздаем комнату в jitsi для постоянной конференции, если пользователь подключается к конференции первым
		if (Domain_Jitsi_Entity_Conference::isPermanent($conference) && Domain_Jitsi_Entity_Conference::STATUS_NEW == $conference->status) {
			Domain_Jitsi_Action_Conference_RecreateJitsiConference::do($conference);
		}

		// если участник модератор или в комнате не включен зал ожидания
		if ($conference_member_data->is_moderator || !$conference->is_lobby) {

			// устанавливаем ID активной конференции
			Domain_Jitsi_Entity_UserActiveConference::set($user_id, $conference->conference_id);
		}

		$conference_type  = Domain_Jitsi_Entity_Conference_Data::getConferenceType($conference->data);
		$opponent_user_id = Domain_Jitsi_Entity_Conference_Data::getOpponentUserId($conference->data);

		// если в сингл зашел участник, которого нет в изначальном списке - апгрейдим сингл до обычной конференции
		if ($conference_type === Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_SINGLE
			&& !in_array($user_id, [$conference->creator_user_id, $opponent_user_id])) {

			$conference_member_list = Domain_Jitsi_Entity_UserActiveConference::getByActiveConferenceId($conference->conference_id);
			$conference             = Domain_Jitsi_Action_Conference_UpgradeSingle::do($conference, $conference_member_list);
		}

		// формируем ответ
		return [
			Struct_Api_Conference_Data::buildFromDB($conference),
			$conference_joining_data,
			$conference_member_data,
			$conference_creator_data,
		];
	}

	/**
	 * подготавливаем все к подключению участника к конференции
	 *
	 * @param int                            $user_id
	 * @param int                            $space_id
	 * @param Struct_Db_JitsiData_Conference $conference
	 * @param bool                           $is_moderator
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws EndpointAccessDeniedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws RowNotFoundException
	 * @throws \queryException
	 * @throws cs_UserNotFound
	 */
	public static function prepareConferenceJoiningData(int $user_id, int $space_id, Struct_Db_JitsiData_Conference $conference, bool $is_moderator = false):array {

		// определяем наличие прав модератора
		$is_moderator = $is_moderator ?: Domain_Jitsi_Entity_ConferenceMember_Behavior_CompassUser::resolveModeratorFlag($user_id, $conference);

		$member_context = Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts::createMemberContextForCompassUser($user_id, getIp(), getUa(), $is_moderator, $space_id);
		/** @noinspection PhpParamsInspection */
		Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts::check($member_context, $conference, [
			Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts_ConferenceState::class,
			Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts_PrivateConference::class,
		]);

		// запускаем участника в конференцию
		$conference_member = Domain_Jitsi_Entity_ConferenceMember::join($member_context, $conference);

		// создаем jwt токен для авторизованного пользователя
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		$jwt_token = Domain_Jitsi_Action_Conference_JoinAsCompassUser::do($user_info, $conference, $conference_member->is_moderator);

		// получаем информацию о создателе конференции
		$conference_creator = Gateway_Bus_PivotCache::getUserInfo($conference->creator_user_id);

		return [
			Struct_Api_Conference_JoiningData::build($conference, $jwt_token),
			Struct_Api_Conference_MemberData::buildFromDB($conference_member),
			Struct_Api_Conference_CreatorData::buildFromCache($conference_creator),
		];
	}

	/**
	 * подготавливаем все к подключению участника к активной конференции
	 *
	 * @param int                            $user_id
	 * @param Struct_Db_JitsiData_Conference $conference
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws EndpointAccessDeniedException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws RowNotFoundException
	 * @throws \queryException
	 * @throws cs_UserNotFound
	 */
	public static function prepareJoiningDataForActiveConference(int $user_id, Struct_Db_JitsiData_Conference $conference):array {

		try {
			return self::prepareConferenceJoiningData($user_id, $conference->space_id, $conference);
		} catch (Domain_Jitsi_Exception_ConferenceMember_AttemptJoinToPrivateConference) {

			// если вдруг пользователь перестал быть участников пространства в активной конференции, то выходим из этой конференции
			Domain_Jitsi_Scenario_Event::onConferenceMemberLeft(
				$conference->conference_id,
				Domain_Jitsi_Entity_ConferenceMember_MemberId::prepareId(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $user_id)
			);
		}

		// выбрасываем ошибку
		throw new ParamException("unexpected behaviour");
	}

	/**
	 * получаем активную конференцию
	 *
	 * @param int $user_id
	 *
	 * @long
	 * @return array
	 * @throws BusFatalException
	 * @throws Domain_Jitsi_Exception_ConferenceMember_AttemptJoinToPrivateConference
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_UserActiveConference_NotFound
	 * @throws EndpointAccessDeniedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws RowNotFoundException
	 * @throws \queryException
	 * @throws cs_UserNotFound
	 */
	public static function getActiveConference(int $user_id):array {

		$user_active_conference = Domain_Jitsi_Entity_UserActiveConference::get($user_id);

		// если активной конференции нет
		if ($user_active_conference->active_conference_id === "") {
			throw new Domain_Jitsi_Exception_UserActiveConference_NotFound();
		}

		// получаем информацию о конференции
		$conference = Domain_Jitsi_Entity_Conference::get($user_active_conference->active_conference_id);

		// получаем запись участника в конференции
		try {
			$conference_member = Domain_Jitsi_Entity_ConferenceMember::getForCompassUser($conference->conference_id, $user_id);
		} catch (Domain_Jitsi_Exception_ConferenceMember_NotFound) {
			$conference_member = null;
		}

		// если участник в данный момент не разговаривает в конференции
		if (is_null($conference_member) || $conference_member->status !== Domain_Jitsi_Entity_ConferenceMember_Status::SPEAKING) {

			try {

				// подготавливаем все к подключению участника к конференции
				[$conference_joining_data, $conference_member_data, $conference_creator_data] = self::prepareConferenceJoiningData($user_id, $conference->space_id, $conference);
			} catch (Domain_Jitsi_Exception_ConferenceMember_AttemptJoinToPrivateConference $e) {

				// если пользователь перестал быть участником пространства конференции, то завершаем его участие в конференции
				Domain_Jitsi_Scenario_Event::onConferenceMemberLeft(
					$conference->conference_id,
					Domain_Jitsi_Entity_ConferenceMember_MemberId::prepareId(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $user_id)
				);
				throw $e;
			}
		} else {

			// получаем информацию о создателе конференции
			$conference_creator      = Gateway_Bus_PivotCache::getUserInfo($conference->creator_user_id);
			$conference_creator_data = Struct_Api_Conference_CreatorData::buildFromCache($conference_creator);

			// определяем, нужно ли выдать права модератора участнику
			$conference_member->is_moderator = $conference_member->is_moderator ?: Domain_Jitsi_Entity_ConferenceMember_Behavior_CompassUser::resolveModeratorFlag($user_id, $conference);

			// иначе делаем по чуть упрощенному флоу, чтобы не обновлять статус участника в конференции:
			// создаем jwt токен для авторизованного пользователя
			$user_info               = Gateway_Bus_PivotCache::getUserInfo($user_id);
			$jwt_token               = Domain_Jitsi_Action_Conference_JoinAsCompassUser::do($user_info, $conference, $conference_member->is_moderator);
			$conference_joining_data = Struct_Api_Conference_JoiningData::build($conference, $jwt_token);
			$conference_member_data  = Struct_Api_Conference_MemberData::buildFromDB($conference_member);
		}

		$conference_active_at = 0;
		if ($conference->status == Domain_Jitsi_Entity_Conference::STATUS_ACTIVE) {
			$conference_active_at = $user_active_conference->updated_at > 0 ? $user_active_conference->updated_at : $user_active_conference->created_at;
		}

		// формируем ответ
		return [
			Struct_Api_Conference_Data::buildFromDB($conference),
			$conference_joining_data,
			$conference_member_data,
			$conference_creator_data,
			$conference_active_at,
		];
	}

	/**
	 * Устанавливаем параметры конференции
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NoModeratorRights
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_IsFinished
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws Domain_jitsi_Exception_ConferenceMember_UnexpectedStatus
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 */
	public static function setConferenceOptions(int $user_id, string $conference_id, bool $is_private, bool $is_lobby):Struct_Api_Conference_Data {

		// получаем данные о конференции
		$conference = Domain_Jitsi_Entity_Conference::get($conference_id);

		// проверяем, что состояние конференции позволяет менять ее опции
		Domain_Jitsi_Entity_Conference_Asserts::init($conference)
			->assertNotFinished();

		// если пользователь не создатель конференции, то проверяем его
		if ($conference->creator_user_id !== $user_id) {

			// получаем данне об участии текущего пользователя в конференции
			$conference_member = Domain_Jitsi_Entity_ConferenceMember::getForCompassUser($conference_id, $user_id);

			// проверяем что участник может изменять опции конференции
			Domain_Jitsi_Entity_ConferenceMember_Asserts::init($conference_member)
				->assertActiveMember()
				->assertModeratorRights();
		}

		// обновляем конференцию
		$conference = Domain_Jitsi_Action_Conference_SetOptions::do($conference, $is_private, $is_lobby);

		// возвращаем обновленные данные о конференции
		return Struct_Api_Conference_Data::buildFromDB($conference);
	}

	/**
	 * завершаем конференцию
	 *
	 * @param int    $user_id
	 * @param string $conference_id
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NoModeratorRights
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_IsFinished
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws Domain_jitsi_Exception_ConferenceMember_UnexpectedStatus
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 */
	public static function finishConference(int $user_id, string $conference_id):void {

		// получаем данные о конференции
		$conference = Domain_Jitsi_Entity_Conference::get($conference_id);

		// проверяем, что состояние конференции позволяет завершить ее
		Domain_Jitsi_Entity_Conference_Asserts::init($conference)->assertNotFinished();

		// если пользователь не создатель конференции, то проверяем его
		if ($conference->creator_user_id !== $user_id ||
			Domain_Jitsi_Entity_Conference_Data::getConferenceType($conference->data) == Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_SINGLE) {

			// получаем данне об участии текущего пользователя в конференции
			$conference_member = Domain_Jitsi_Entity_ConferenceMember::getForCompassUser($conference_id, $user_id);

			// проверяем что участник может изменять опции конференции
			Domain_Jitsi_Entity_ConferenceMember_Asserts::init($conference_member)
				->assertJoinOrSpeakStatus()
				->assertModeratorRights();
		}

		// отправляем запрос на завершение конференции
		Domain_Jitsi_Action_Conference_FinishConference::do($user_id, $conference, true);

		// если сингл-звонок, то отправляем сообщение о звонке в чат
		if (Domain_Jitsi_Entity_Conference_Data::getConferenceType($conference->data) == Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_SINGLE &&
			$conference->status != Domain_Jitsi_Entity_Conference::STATUS_ACTIVE) {

			$conference_member         = Domain_Jitsi_Entity_ConferenceMember::getForCompassUser($conference_id, $user_id);
			$conference_member->status = Domain_Jitsi_Entity_ConferenceMember_Status::REJECTED;
			$conference->status        = Domain_Jitsi_Entity_Conference::STATUS_FINISHED;
			$opponent_user_id          = Domain_Jitsi_Entity_Conference_Data::getOpponentUserId($conference->data);
			Domain_Pivot_Entity_Event_AddMediaConferenceMessage::create($conference, $conference_member, $user_id == $opponent_user_id ? 0 : $opponent_user_id);
		}
	}

	/**
	 * создаем сингл
	 *
	 * @param int $creator_user_id
	 * @param int $opponent_user_id
	 * @param int $space_id
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IsSpeaking
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_ConferenceIdDuplication
	 * @throws Domain_Jitsi_Exception_Conference_GuestAccessDenied
	 * @throws Domain_Jitsi_Exception_Conference_IsFinished
	 * @throws Domain_Jitsi_Exception_Conference_NoCreatePermissions
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_UnexpectedStatus
	 * @throws Domain_Jitsi_Exception_Conference_UsersAreNotMembersOfSpace
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws Domain_Jitsi_Exception_UserActiveConference_OpponentUserHaveActiveConference
	 * @throws Domain_Jitsi_Exception_UserActiveConference_UserHaveActiveConference
	 * @throws Domain_Space_Exception_NotFound
	 * @throws Domain_Space_Exception_UnexpectedStatus
	 * @throws EndpointAccessDeniedException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws RowNotFoundException
	 * @throws \busException
	 * @throws \cs_CurlError
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_UserNotFound
	 * @long
	 */
	public static function createSingle(int $creator_user_id, int $opponent_user_id, int $space_id):array {

		// проверяем пространство
		Domain_Jitsi_Entity_ConferenceMember_CreationAsserts::assertUserListMembersOfSpace([$creator_user_id, $opponent_user_id], $space_id);

		$space = Domain_Space_Entity_Main::get($space_id);
		Domain_Space_Entity_Status::assertActive($space);
		$is_space_creator = $creator_user_id === $space->created_by_user_id;

		// проверим, что пользователь имеет права на создание конференции
		$conversation_map = Domain_Jitsi_Entity_ConferenceMember_CreationAsserts::assertSinglePermissions($creator_user_id, $opponent_user_id, $space_id);

		// получаем инстанс доступного jitsi сервера, для создания конференции
		$jitsi_node_config = Domain_Jitsi_Entity_Node::getRandomNode();

		// проверяем наличие активной конференции
		try {
			Domain_Jitsi_Entity_UserActiveConference::throwIfUserListHaveActiveConference($creator_user_id, $opponent_user_id);
		} catch (Domain_Jitsi_Exception_UserActiveConference_OpponentUserHaveActiveConference $e) {

			// в случае если у оппонента уже имеется активный звонок
			// создаём конференцию
			$conference     = Domain_Jitsi_Action_Conference_CreateSingle::do($creator_user_id, $opponent_user_id, $space_id, $conversation_map, $jitsi_node_config);
			$member_context = Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts::createMemberContextForCompassUser($opponent_user_id, getIp(), getUa(), true, $space_id);
			Domain_Jitsi_Entity_ConferenceMember::join($member_context, $conference);

			// отклоняем её от лица собеседника
			self::rejectSingle($opponent_user_id, $conference->conference_id, true);
			throw $e;
		}

		// создаем конференцию
		$conference = Domain_Jitsi_Action_Conference_CreateSingle::do($creator_user_id, $opponent_user_id, $space_id, $conversation_map, $jitsi_node_config);

		// формирую данные о конференции
		$conference_data = Struct_Api_Conference_Data::buildFromDB($conference);

		// подготавливаем все к подключению участника к конференции
		[$conference_joining_data, $conference_member_data] = self::prepareConferenceJoiningData($creator_user_id, $space_id, $conference, true);

		// помечаем конференцию активной для создателя
		Domain_Jitsi_Entity_UserActiveConference::set($creator_user_id, $conference->conference_id);

		// подключаем оппонента
		// подготавливаем все к подключению оппонента к конференции
		[$conference_opponent_joining_data, $conference_opponent_member_data] = self::prepareConferenceJoiningData($opponent_user_id, $space_id, $conference, true);

		// устанавливаем ID активной конференции оппоненту
		Domain_Jitsi_Entity_UserActiveConference::set($opponent_user_id, $conference->conference_id);

		// получаем информацию о создателе конференции
		// и меняем цвет
		$conference_creator                     = Gateway_Bus_PivotCache::getUserInfo($creator_user_id);
		$conference_creator_data                = Struct_Api_Conference_CreatorData::buildFromCache($conference_creator);
		$conference_creator_data->avatar->color = !$is_space_creator ?: Avatar::getSpaceCreatorColor();

		// отправляем оппоненту информацию о том, что сингл создан
		$push_data = Gateway_Bus_Pusher::makeVoIPPushData(
			Gateway_Bus_Pusher::getVoIPPushBody($conference_data->status, $creator_user_id),
			$conference_data->format(),
			$conference_opponent_joining_data->format(),
			$conference_opponent_member_data->format(),
			$conference_creator_data->format(),
		);
		Gateway_Bus_SenderBalancer::conferenceCreated($opponent_user_id, $conference_data, $conference_opponent_joining_data, $conference_opponent_member_data,
			$conference_creator_data, $push_data, [$creator_user_id]);

		// отправляем в go_event таск на проверку состояния соединения через определенное время
		Domain_PhpJitsi_Entity_Event_NeedCheckSingleConference::create($conference->conference_id);

		// формируем ответ
		return [$conference_data, $conference_joining_data, $conference_member_data, $conference_creator_data];
	}

	/**
	 * Отклонить звонок
	 *
	 * @param int    $user_id
	 * @param string $conference_id
	 * @param bool   $is_opponent_user_have_active_conference
	 *
	 * @return void
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IsSpeaking
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_IsFinished
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_UnexpectedStatus
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws ParseFatalException
	 * @throws \busException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 * @long
	 */
	public static function rejectSingle(int $user_id, string $conference_id, bool $is_opponent_user_have_active_conference = false):void {

		// проверяем, что пытаемся отклонить сингл звонок
		$conference = Domain_Jitsi_Entity_Conference::get($conference_id);
		Domain_Jitsi_Entity_Conference_Asserts::init($conference)->assertSingle()->assertNotFinished();

		$opponent_user_id        = Domain_Jitsi_Entity_Conference_Data::getOpponentUserId($conference->data);
		$conference_user_id_list = [$conference->creator_user_id, $opponent_user_id];

		if (!in_array($user_id, $conference_user_id_list, true)) {
			throw new Domain_Jitsi_Exception_ConferenceMember_NotFound();
		}

		// проверяем, что в сингл еще никто не зашел
		$conference_member_list = Domain_Jitsi_Entity_ConferenceMember::getConferenceMemberList($conference_id);

		$rejected_conference_member = null;
		foreach ($conference_member_list as $conference_member) {

			$conference_member_user_id = (int) Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveId($conference_member->member_id);
			if ($user_id === $conference_member_user_id) {
				$rejected_conference_member = $conference_member;
			}
		}

		// завершаем конференцию
		Domain_Jitsi_Action_Conference_FinishConference::do($conference->creator_user_id, $conference);

		// меняем пользователю статус, что он отменил звонок
		if ($rejected_conference_member !== null) {

			$rejected_conference_member->status = Domain_Jitsi_Entity_ConferenceMember::updateOnRejected(
				$rejected_conference_member->member_type, $rejected_conference_member->member_id, $conference->conference_id);
			$opponent_user_id                   = ($is_opponent_user_have_active_conference || $conference->creator_user_id == $user_id)
				? $opponent_user_id : 0;
			Domain_Pivot_Entity_Event_AddMediaConferenceMessage::create($conference, $rejected_conference_member, $opponent_user_id);

			// отправляем ws о том, что изменился статус принятия звонка у участника конференции
			$conference_user_id_list = $is_opponent_user_have_active_conference ? array_diff($conference_user_id_list, [$user_id]) : $conference_user_id_list;
			Gateway_Bus_SenderBalancer::conferenceAcceptStatusUpdated(
				$conference_id,
				$rejected_conference_member->status->getAcceptStatusOutput(),
				$user_id,
				$conference_user_id_list);
		}
	}

	/**
	 * Принять звонок
	 *
	 * @param int    $user_id
	 * @param string $conference_id
	 *
	 * @return void
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_IsFinished
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_NotSingleOpponent
	 * @throws Domain_Jitsi_Exception_Conference_UnexpectedStatus
	 * @throws ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function acceptSingle(int $user_id, string $conference_id):void {

		// проверяем, что пытаемся принять сингл звонок
		$conference = Domain_Jitsi_Entity_Conference::get($conference_id);
		Domain_Jitsi_Entity_Conference_Asserts::init($conference)->assertSingle()->assertNotFinished();

		$conference_member = Domain_Jitsi_Entity_ConferenceMember::getForCompassUser(
			$conference->conference_id, $user_id);

		// принять сингл может только оппонент
		if (Domain_Jitsi_Entity_Conference_Data::getOpponentUserId($conference->data) !== $user_id) {
			throw new Domain_Jitsi_Exception_Conference_NotSingleOpponent("not single opponent");
		}
		Domain_Jitsi_Action_Conference_AcceptSingle::do($conference, $conference_member);
	}

	/**
	 * Метод для покидания активной конференции
	 *
	 * @param int $user_id
	 *
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 */
	public static function leaveActiveConference(int $user_id):void {

		// пытаемся получить активную конференцию
		try {
			$user_active_conference = Domain_Jitsi_Entity_UserActiveConference::get($user_id);
		} catch (Domain_Jitsi_Exception_UserActiveConference_NotFound) {
			return;
		}

		// если активной конференции нет, то ничего не делаем
		if ($user_active_conference->active_conference_id === "") {
			return;
		}

		// получаем сущность конференции
		$conference = Domain_Jitsi_Entity_Conference::get($user_active_conference->active_conference_id);

		// исключаем участника из конференции
		try {

			Domain_Jitsi_Entity_Node_Request::init(Domain_Jitsi_Entity_Node::getConfig($conference->jitsi_instance_domain))->kickMember(
				$conference->conference_id,
				Domain_Jitsi_Entity_ConferenceMember_MemberId::prepareId(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $user_id)
			);
		} catch (Domain_Jitsi_Exception_Node_RequestFailed $e) {

			// 404 возвращается в случае если конференция уже удалена, не сыпем логами просто так
			if ($e->getResponseHttpCode() !== 404) {

				// логируем ошибку и больше ничего не делаем
				$exception_message = \BaseFrame\Exception\ExceptionUtils::makeMessage($e, HTTP_CODE_500);
				\BaseFrame\Exception\ExceptionUtils::writeExceptionToLogs($e, $exception_message);
			}
		}

		// покидаем конференцию
		Domain_Jitsi_Scenario_Event::onConferenceMemberLeft(
			$user_active_conference->active_conference_id,
			Domain_Jitsi_Entity_ConferenceMember_MemberId::prepareId(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $user_id)
		);
	}
}