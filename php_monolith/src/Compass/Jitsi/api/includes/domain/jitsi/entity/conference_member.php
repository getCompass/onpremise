<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowDuplicationException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с участниками конференции
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceMember {

	/** @var int максимальная длина user-agent, сохраняемая в базу */
	protected const _USER_AGENT_MAX_LENGTH = 512;

	/**
	 * добавляем нового участника в конференцию
	 *
	 * @param Struct_Jitsi_ConferenceMember_MemberContext $member_context
	 * @param Struct_Db_JitsiData_Conference              $conference
	 *
	 * @return Struct_Db_JitsiData_ConferenceMember
	 * @throws \queryException
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 * @long создаем/обновляем большие структуры
	 */
	public static function join(Struct_Jitsi_ConferenceMember_MemberContext $member_context, Struct_Db_JitsiData_Conference $conference):Struct_Db_JitsiData_ConferenceMember {

		try {

			// создаем новую запись
			$conference_member = new Struct_Db_JitsiData_ConferenceMember(
				conference_id: $conference->conference_id,
				member_type: $member_context->member_type,
				member_id: $member_context->member_id,
				is_moderator: $member_context->is_moderator,
				status: Domain_Jitsi_Entity_ConferenceMember_Status::JOINING,
				ip_address: $member_context->ip_address,
				user_agent: self::_prepareUserAgent($member_context->user_agent),
				created_at: time(),
				updated_at: 0,
				data: Domain_Jitsi_Entity_ConferenceMember_ExtraData::init($member_context->space_id),
			);
			Gateway_Db_JitsiData_ConferenceMemberList::insert($conference_member);
		} catch (RowDuplicationException) {

			// получаем существующую запись
			$conference_member = Gateway_Db_JitsiData_ConferenceMemberList::getOne($conference->conference_id, $member_context->member_type->value, $member_context->member_id);

			// обновляем данные в существующей записи
			$conference_member->status       = Domain_Jitsi_Entity_ConferenceMember_Status::JOINING;
			$conference_member->is_moderator = $member_context->is_moderator;
			$conference_member->ip_address   = $member_context->ip_address;
			$conference_member->user_agent   = self::_prepareUserAgent($member_context->user_agent);
			$conference_member->created_at   = time();
			$conference_member->updated_at   = 0;

			// обновляем запись
			Gateway_Db_JitsiData_ConferenceMemberList::set($conference->conference_id, $member_context->member_type->value, $member_context->member_id, [
				"status"       => $conference_member->status,
				"is_moderator" => $conference_member->is_moderator,
				"ip_address"   => $conference_member->ip_address,
				"user_agent"   => $conference_member->user_agent,
				"created_at"   => $conference_member->created_at,
				"updated_at"   => $conference_member->updated_at,
			]);
		}

		return $conference_member;
	}

	/**
	 * подготавливаем user-agent перед сохранением в базу
	 *
	 * @return string
	 */
	protected static function _prepareUserAgent(string $user_agent):string {

		return mb_strcut($user_agent, 0, self::_USER_AGENT_MAX_LENGTH);
	}

	/**
	 * обновляем запись при вступлении участника
	 *
	 * @throws ParseFatalException
	 */
	public static function updateOnJoin(Domain_Jitsi_Entity_ConferenceMember_Type $member_type, string $member_id, string $conference_id, ?array $data = null):Domain_Jitsi_Entity_ConferenceMember_Status {

		// формируем массив для обновления
		$status = Domain_Jitsi_Entity_ConferenceMember_Status::SPEAKING;
		$set    = [
			"status"     => Domain_Jitsi_Entity_ConferenceMember_Status::SPEAKING->value,
			"updated_at" => time(),
		];
		if (!is_null($data)) {
			$set["data"] = $data;
		}
		Gateway_Db_JitsiData_ConferenceMemberList::set($conference_id, $member_type->value, $member_id, $set);

		return $status;
	}

	/**
	 * обновляем запись при покидании конференции участником
	 *
	 * @throws ParseFatalException
	 */
	public static function updateOnLeft(Domain_Jitsi_Entity_ConferenceMember_Type $member_type, string $member_id, string $conference_id):void {

		Gateway_Db_JitsiData_ConferenceMemberList::set($conference_id, $member_type->value, $member_id, [
			"is_moderator" => 0,
			"status"       => Domain_Jitsi_Entity_ConferenceMember_Status::LEFT->value,
			"updated_at"   => time(),
		]);
	}

	/**
	 * получаем объект участника для пользователя Compass
	 *
	 * @return Struct_Db_JitsiData_ConferenceMember
	 * @throws ParseFatalException
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NotFound
	 */
	public static function getForCompassUser(string $conference_id, int $user_id):Struct_Db_JitsiData_ConferenceMember {

		try {

			$member_id = Domain_Jitsi_Entity_ConferenceMember_MemberId::prepareId(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $user_id);
			return Gateway_Db_JitsiData_ConferenceMemberList::getOne($conference_id, Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER->value, $member_id);
		} catch (RowNotFoundException) {
			throw new Domain_Jitsi_Exception_ConferenceMember_NotFound();
		}
	}

	/**
	 * получаем всех участников конференции
	 *
	 * @return Struct_Db_JitsiData_ConferenceMember[]
	 * @throws ParseFatalException
	 */
	public static function getConferenceMemberList(string $conference_id):array {

		return Gateway_Db_JitsiData_ConferenceMemberList::getList($conference_id);

	}

	/**
	 * получаем всех модераторов конференции
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function getConferenceModeratorList(string $conference_id):array {

		$list = Gateway_Db_JitsiData_ConferenceMemberList::getList($conference_id);

		return array_filter($list, static fn(Struct_Db_JitsiData_ConferenceMember $conference_member) => $conference_member->is_moderator == 1);
	}

	/**
	 * обновляем запись при изменении флага is_moderator
	 *
	 * @throws ParseFatalException
	 */
	public static function updateIsModerator(Domain_Jitsi_Entity_ConferenceMember_Type $member_type, string $member_id, string $conference_id, bool $is_moderator):void {

		Gateway_Db_JitsiData_ConferenceMemberList::set($conference_id, $member_type->value, $member_id, [
			"is_moderator" => intval($is_moderator),
			"updated_at"   => time(),
		]);
	}

	/**
	 * фильтруем список участников конференции по переданному типу
	 *
	 * @return Struct_Db_JitsiData_ConferenceMember[]
	 */
	public static function filterByMemberType(array $conference_member_list, Domain_Jitsi_Entity_ConferenceMember_Type $member_type):array {

		return array_filter($conference_member_list, static fn(Struct_Db_JitsiData_ConferenceMember $conference_member) => $conference_member->member_type == $member_type);
	}

	/**
	 * обновляем запись при игнорировании звонка участником
	 *
	 * @throws ParseFatalException
	 */
	public static function updateOnIgnored(Domain_Jitsi_Entity_ConferenceMember_Type $member_type, string $member_id, string $conference_id):Domain_Jitsi_Entity_ConferenceMember_Status {

		$status = Domain_Jitsi_Entity_ConferenceMember_Status::IGNORED;

		Gateway_Db_JitsiData_ConferenceMemberList::set($conference_id, $member_type->value, $member_id, [
			"is_moderator" => 0,
			"status"       => $status->value,
			"updated_at"   => time(),
		]);

		return $status;
	}

	/**
	 * обновляем запись при отказа от звонка участником
	 *
	 * @throws ParseFatalException
	 */
	public static function updateOnRejected(Domain_Jitsi_Entity_ConferenceMember_Type $member_type, string $member_id, string $conference_id):Domain_Jitsi_Entity_ConferenceMember_Status {

		$status = Domain_Jitsi_Entity_ConferenceMember_Status::REJECTED;
		Gateway_Db_JitsiData_ConferenceMemberList::set($conference_id, $member_type->value, $member_id, [
			"is_moderator" => 0,
			"status"       => $status->value,
			"updated_at"   => time(),
		]);

		return $status;
	}
}