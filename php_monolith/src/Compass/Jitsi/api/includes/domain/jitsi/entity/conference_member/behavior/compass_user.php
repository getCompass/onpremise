<?php

namespace Compass\Jitsi;

/**
 * класс описывающий поведение пользователя Compass в конференции
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceMember_Behavior_CompassUser implements Domain_Jitsi_Entity_ConferenceMember_Behavior_Interface {

	/**
	 * обработка события о присоединении в конференцию
	 * @long
	 */
	public function onJoinConference(string $conference_id, string $member_id):void {

		// получаем запись с информацией о конференции
		$conference = Domain_Jitsi_Entity_Conference::get($conference_id);

		// если конференция уже завершена, то ничего не делаем
		if ($conference->status === Domain_Jitsi_Entity_Conference::STATUS_FINISHED) {
			return;
		}

		// получаем id пользователя
		$user_id = Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveId($member_id);

		// получаем запись с информацией об участнике конференции
		$conference_member = Domain_Jitsi_Entity_ConferenceMember::getForCompassUser($conference_id, $user_id);
		$old_status        = $conference_member->status;

		// проверяем, инкрементили ли ранее статистику участия в конференциях и получаем space_id откуда присоединился участник
		$was_stat_inc = Domain_Jitsi_Entity_ConferenceMember_ExtraData::wasConferenceMembershipRatingIncremented($conference_member->data);
		$space_id     = Domain_Jitsi_Entity_ConferenceMember_ExtraData::getJoiningSpaceId($conference_member->data);

		// если не инкрементили статистику, то пометим что инкрементили и увеличим ее в конце функции
		if (!$was_stat_inc) {
			$conference_member->data = Domain_Jitsi_Entity_ConferenceMember_ExtraData::setConferenceMembershipRatingIncrementedFlag($conference_member->data, true);
		}

		// обновляем запись участника конференции
		$conference_member->status = Domain_Jitsi_Entity_ConferenceMember::updateOnJoin(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $member_id, $conference_id, $conference_member->data);

		$conference_type  = Domain_Jitsi_Entity_Conference_Data::getConferenceType($conference->data);
		$opponent_user_id = Domain_Jitsi_Entity_Conference_Data::getOpponentUserId($conference->data);

		// отправляем вску и сообщение, что приняли звонок
		// если это сингл-конференция, конференция ранее не была активна,
		// у входящего пользователя статус dialing и он является оппонентом создателя звонка
		if ($conference_type === Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_SINGLE &&
			$conference->status !== Domain_Jitsi_Entity_Conference::STATUS_ACTIVE &&
			$old_status->getAcceptStatusOutput() === Domain_Jitsi_Entity_ConferenceMember_Status::ACCEPT_STATUS_DIALING &&
			(int) $user_id === $opponent_user_id) {

			Domain_Pivot_Entity_Event_AddMediaConferenceMessage::create($conference, $conference_member);

			Gateway_Bus_SenderBalancer::conferenceAcceptStatusUpdated(
				$conference->conference_id,
				$conference_member->status->getAcceptStatusOutput(),
				$opponent_user_id,
				[$opponent_user_id, $conference->creator_user_id]
			);
		}

		if ($conference->status === Domain_Jitsi_Entity_Conference::STATUS_WAITING) {

			$conference->status = Domain_Jitsi_Entity_Conference::STATUS_ACTIVE;
			Domain_Jitsi_Entity_Conference::updateStatus($conference->conference_id, $conference->status);
		}

		// перезаписываем ID активной конференции, не смотря на то что в api-методе /jitsi/joinConference это уже сделали
		$user_active_conference = Domain_Jitsi_Entity_UserActiveConference::set($user_id, $conference_id);

		// получаем запись с информацией об участнике конференции
		$conference_member = Domain_Jitsi_Entity_ConferenceMember::getForCompassUser($conference_id, $user_id);

		// создаем jwt токен для авторизованного пользователя
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		$jwt_token = Domain_Jitsi_Action_Conference_JoinAsCompassUser::do($user_info, $conference, $conference_member->is_moderator);

		// отправляем событие
		Gateway_Bus_SenderBalancer::activeConferenceUpdated(
			[$user_id],
			Gateway_Bus_SenderBalancer_Event_ActiveConferenceUpdated_V1::ACTION_JOIN_CONFERENCE,
			Struct_Api_Conference_Data::buildFromDB($conference),
			Struct_Api_Conference_MemberData::buildFromDB($conference_member),
			Struct_Api_Conference_JoiningData::build($conference, $jwt_token),
			$user_active_conference->updated_at > 0 ? $user_active_conference->updated_at : $user_active_conference->created_at,
		);

		// отправляем запрос на увеличение статистики
		Gateway_Socket_Pivot::incConferenceMembershipRating($user_id, $space_id);
	}

	/**
	 * обработка события о покидании конференции
	 */
	public function onLeftConference(string $conference_id, string $member_id):void {

		// обновляем статус участника конференции
		Domain_Jitsi_Entity_ConferenceMember::updateOnLeft(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $member_id, $conference_id);

		// получаем id пользователя
		$user_id = Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveId($member_id);

		// очищаем ID активной конференции
		Domain_Jitsi_Entity_UserActiveConference::set($user_id, "");

		// получаем запись с информацией о конференции
		$conference = Domain_Jitsi_Entity_Conference::get($conference_id);

		// получаем запись с информацией об участнике конференции
		$conference_member = Domain_Jitsi_Entity_ConferenceMember::getForCompassUser($conference_id, $user_id);

		// отправляем событие
		Gateway_Bus_SenderBalancer::activeConferenceUpdated(
			[$user_id],
			Gateway_Bus_SenderBalancer_Event_ActiveConferenceUpdated_V1::ACTION_LEFT_CONFERENCE,
			Struct_Api_Conference_Data::buildFromDB($conference),
			Struct_Api_Conference_MemberData::buildFromDB($conference_member),
			null,
			null,
		);
	}

	/**
	 * обработка события о выдаче прав модератора
	 */
	public function onConferenceModeratorRightsGranted(string $conference_id, string $member_id):void {

		// обновляем статус модератора
		Domain_Jitsi_Entity_ConferenceMember::updateIsModerator(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $member_id, $conference_id, true);

		// получаем id пользователя
		$user_id = Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveId($member_id);

		// получаем запись с информацией о конференции
		$conference = Domain_Jitsi_Entity_Conference::get($conference_id);

		// получаем запись с информацией об участнике конференции
		$conference_member = Domain_Jitsi_Entity_ConferenceMember::getForCompassUser($conference_id, $user_id);

		// отправляем событие
		Gateway_Bus_SenderBalancer::activeConferenceUpdated(
			[$user_id],
			Gateway_Bus_SenderBalancer_Event_ActiveConferenceUpdated_V1::ACTION_UPDATED_MEMBER_DATA,
			Struct_Api_Conference_Data::buildFromDB($conference),
			Struct_Api_Conference_MemberData::buildFromDB($conference_member),
			null,
			null,
		);
	}
}