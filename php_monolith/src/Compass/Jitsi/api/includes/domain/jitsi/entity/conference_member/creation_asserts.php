<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Request\ParamException;

/**
 * класс описывающий проверки при создании конференции
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceMember_CreationAsserts {

	/**
	 * создатель конференции не является участником пространства
	 *
	 * @throws Domain_Jitsi_Exception_Conference_CreatorIsNotMemberOfSpace
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function assertCreatorMemberOfSpace(int $creator_user_id, int $space_id):void {

		if (!Domain_User_Entity_SpaceMember::isMember($space_id, $creator_user_id)) {
			throw new Domain_Jitsi_Exception_Conference_CreatorIsNotMemberOfSpace("creator is not member of space");
		}
	}

	/**
	 * участники конференции не являются участниками пространства
	 *
	 * @param array $user_id_list
	 * @param int   $space_id
	 *
	 * @throws Domain_Jitsi_Exception_Conference_UsersAreNotMembersOfSpace
	 * @throws ParseFatalException
	 */
	public static function assertUserListMembersOfSpace(array $user_id_list, int $space_id):void {

		if (!Domain_User_Entity_SpaceMember::areMembers($space_id, $user_id_list)) {
			throw new Domain_Jitsi_Exception_Conference_UsersAreNotMembersOfSpace();
		}
	}

	/**
	 * Проверяет наличие прав для создания конференции.
	 *
	 * @throws Domain_Jitsi_Exception_Conference_GuestAccessDenied
	 * @throws Domain_Jitsi_Exception_Conference_NoCreatePermissions
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function assertPermissions(int $user_id, int $space_id):void {

		[$has_permission, $ecode] = Gateway_Socket_Pivot::isMediaConferenceCreatingAllowed($user_id, $space_id);

		if ($has_permission) {
			return;
		}

		throw match ($ecode) {
			2419010 => new Domain_Jitsi_Exception_Conference_NoCreatePermissions("user has no permission"),
			2419011 => new Domain_Jitsi_Exception_Conference_GuestAccessDenied("guest has no permission"),
			default => new ParamException("bad data")
		};
	}

	/**
	 * Проверяет наличие прав для создания конференции.
	 *
	 * @throws Domain_Jitsi_Exception_Conference_GuestAccessDenied
	 * @throws Domain_Jitsi_Exception_Conference_NoCreatePermissions
	 * @throws ParamException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function assertSinglePermissions(int $user_id, int $opponent_user_id, int $space_id):string {

		[$ecode, $conversation_map] = Gateway_Socket_Pivot::checkIsAllowedForCall($user_id, $opponent_user_id, $space_id);

		return match ($ecode) {
			0 => $conversation_map,
			2419010 => throw new Domain_Jitsi_Exception_Conference_NoCreatePermissions("user has no permission"),
			2419011 => throw new Domain_Jitsi_Exception_Conference_GuestAccessDenied("guest has no permission"),
			default => throw new ParamException("bad data")
		};
	}
}