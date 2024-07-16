<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс описывающий действие по генерации jwt токена для пользователя Compass
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Action_Conference_JoinAsCompassUser {

	/**
	 * выполняем действие
	 *
	 * @param Struct_Db_PivotUser_User       $user_info
	 * @param Struct_Db_JitsiData_Conference $conference
	 * @param bool                           $grant_moderator_role
	 *
	 * @return string
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function do(Struct_Db_PivotUser_User $user_info, Struct_Db_JitsiData_Conference $conference, bool $grant_moderator_role):string {

		// получаем ссылку на аватар пользователя
		$avatar_url = "";
		if ($user_info->avatar_file_map !== "") {

			$avatar_link_by_file_map = Domain_File_Action_GetLinkForAvatarList::do([$user_info->avatar_file_map]);
			$avatar_url              = $avatar_link_by_file_map[$user_info->avatar_file_map] ?? "";
		}

		// формируем контекст пользователя для токена
		$user_context = Domain_Jitsi_Entity_Authentication_Jwt_UserContext::createForCompasssUser($user_info, $avatar_url);

		return Domain_Jitsi_Entity_Authentication_Jwt::create(
			$conference,
			$user_context,
			$grant_moderator_role,
			Domain_Jitsi_Entity_Node::getConfig($conference->jitsi_instance_domain)
		);
	}
}