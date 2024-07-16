<?php

namespace Compass\Jitsi;

/**
 * класс для формирования структуры Struct_Jitsi_Authentication_Jwt_UserContext
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_Authentication_Jwt_UserContext {

	/**
	 * создаем структуру для пользователя Compass
	 *
	 * @return Struct_Jitsi_Authentication_Jwt_UserContext
	 */
	public static function createForCompasssUser(Struct_Db_PivotUser_User $user_info, string $avatar_url):Struct_Jitsi_Authentication_Jwt_UserContext {

		return new Struct_Jitsi_Authentication_Jwt_UserContext(
			name: $user_info->full_name,
			id: Domain_Jitsi_Entity_ConferenceMember_MemberId::prepareId(Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $user_info->user_id),
			email: "",
			avatar: $avatar_url,
			type: "compass_user"
		);
	}

	/**
	 * создаем структуру для гостя
	 *
	 * @return Struct_Jitsi_Authentication_Jwt_UserContext
	 */
	public static function createForGuest(string $guest_name, string $guest_id):Struct_Jitsi_Authentication_Jwt_UserContext {

		return new Struct_Jitsi_Authentication_Jwt_UserContext(
			name: $guest_name,
			id: Domain_Jitsi_Entity_ConferenceMember_MemberId::prepareId(Domain_Jitsi_Entity_ConferenceMember_Type::GUEST, $guest_id),
			email: "",
			avatar: "",
			type: "guest",
		);
	}
}