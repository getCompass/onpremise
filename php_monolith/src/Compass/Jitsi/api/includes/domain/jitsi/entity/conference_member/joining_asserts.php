<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс содержит логику проверок участников, вступаемых в конференцию
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts {

	/**
	 * совершаем проверки возможности вступить участника в конференцию
	 *
	 * @param Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts_Interface[] $assert_class_list
	 */
	public static function check(Struct_Jitsi_ConferenceMember_MemberContext $member_context, Struct_Db_JitsiData_Conference $conference, array $assert_class_list) {

		// если передали пустой массив с классами для проверок
		if (count($assert_class_list) === 0) {
			throw new ParseFatalException("unexpected value");
		}

		foreach ($assert_class_list as $class) {
			$class::assert($member_context, $conference);
		}
	}

	/**
	 * создаем структуру member_context для пользователя Compass
	 *
	 * @return Struct_Jitsi_ConferenceMember_MemberContext
	 */
	public static function createMemberContextForCompassUser(int $user_id, string $ip_address, string $user_agent, bool $is_moderator, int $space_id):Struct_Jitsi_ConferenceMember_MemberContext {

		return new Struct_Jitsi_ConferenceMember_MemberContext(
			member_type: Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER,
			member_id: Domain_Jitsi_Entity_ConferenceMember_MemberId::prepareId(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $user_id),
			ip_address: $ip_address,
			user_agent: $user_agent,
			is_moderator: $is_moderator,
			space_id: $space_id,
		);
	}

	/**
	 * создаем структуру member_context для гостя
	 *
	 * @return Struct_Jitsi_ConferenceMember_MemberContext
	 */
	public static function createMemberContextForGuest(string $guest_id, string $ip_address, string $user_agent, bool $is_moderator):Struct_Jitsi_ConferenceMember_MemberContext {

		return new Struct_Jitsi_ConferenceMember_MemberContext(
			member_type: Domain_Jitsi_Entity_ConferenceMember_Type::GUEST,
			member_id: Domain_Jitsi_Entity_ConferenceMember_MemberId::prepareId(Domain_Jitsi_Entity_ConferenceMember_Type::GUEST, $guest_id),
			ip_address: $ip_address,
			user_agent: $user_agent,
			is_moderator: $is_moderator,
			space_id: 0,
		);
	}
}