<?php

namespace Compass\Jitsi;

/**
 * класс для работы с идентификатором участника конференции, который пробрасывается непосредственно в jitsi
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceMember_MemberId {

	protected const _MEMBER_TYPE_COMPASS_USER = "compass_user";
	protected const _MEMBER_TYPE_GUEST        = "guest";

	protected const _DELIMETER_CHAR = ":";

	/**
	 * подготавливаем ID участника
	 *
	 * @return string
	 */
	public static function prepareId(Domain_Jitsi_Entity_ConferenceMember_Type $member_type, mixed $id):string {

		$prefix = self::_resovlePrefix($member_type);
		return sprintf("%s%s%s", $prefix, self::_DELIMETER_CHAR, $id);
	}

	/**
	 * определяем ID участника
	 *
	 * @return string
	 */
	public static function resolveId(string $member_id):string {

		[$stringify_member_type, $id] = self::_explodeMemberId($member_id);

		return $id;
	}

	/**
	 * определяем тип участника по ID участника из Jitsi
	 *
	 * @return Domain_Jitsi_Entity_ConferenceMember_Type
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 */
	public static function resolveMemberType(string $member_id):Domain_Jitsi_Entity_ConferenceMember_Type {

		[$stringify_member_type, $id] = self::_explodeMemberId($member_id);

		return match ($stringify_member_type) {
			self::_MEMBER_TYPE_COMPASS_USER => Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER,
			self::_MEMBER_TYPE_GUEST        => Domain_Jitsi_Entity_ConferenceMember_Type::GUEST,
		};
	}

	/**
	 * разбиваем member_id на $stringify_member_type и $id
	 *
	 * @return array
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 */
	protected static function _explodeMemberId(string $member_id):array {

		// проверяем, что пришел корректный id участника
		$temp = explode(self::_DELIMETER_CHAR, $member_id);
		if (count($temp) !== 2) {
			throw new Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId();
		}

		[$stringify_member_type, $id] = $temp;

		return [$stringify_member_type, $id];
	}

	/**
	 * получаем префикс, который добавим в ID участника конференции в jitsi
	 *
	 * @return string
	 */
	protected static function _resovlePrefix(Domain_Jitsi_Entity_ConferenceMember_Type $member_type):string {

		return match ($member_type) {
			Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER => self::_MEMBER_TYPE_COMPASS_USER,
			Domain_Jitsi_Entity_ConferenceMember_Type::GUEST        => self::_MEMBER_TYPE_GUEST,
		};
	}
}