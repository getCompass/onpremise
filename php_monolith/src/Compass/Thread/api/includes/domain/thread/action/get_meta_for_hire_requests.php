<?php

namespace Compass\Thread;

use CompassApp\Domain\Member\Entity\Permission;

/**
 *
 * Получить меты тредов заявок найма/увольнения
 */
class Domain_Thread_Action_GetMetaForHireRequests {

	/**
	 * выполняем
	 */
	public static function do(int $user_id, array $meta_list_for_hire_requests, array $not_allowed_thread_map_list = []):array {

		$user_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
		$user      = $user_list[$user_id];

		if (Permission::canInviteMember($user->role, $user->permissions) || Permission::canKickMember($user->role, $user->permissions)) {
			return [$meta_list_for_hire_requests, $not_allowed_thread_map_list];
		}

		foreach ($meta_list_for_hire_requests as $meta) {
			$not_allowed_thread_map_list[] = $meta["thread_map"];
		}

		return [[], $not_allowed_thread_map_list];
	}
}