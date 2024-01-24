<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Action для отписки пользователя от треда
 */
class Domain_Thread_Action_Follower_Unfollow {

	/**
	 * Убираем тред из избранного
	 */
	public static function do(int $user_id, string $thread_map, bool $is_need_hide = false):array {

		Type_Thread_Followers::doUnfollowUser($user_id, $thread_map);

		if ($is_need_hide === true) {
			Type_Thread_Followers::doClearFollowUser($user_id, $thread_map);
		}
		Type_Thread_Menu::setUnfollow($user_id, $thread_map, $is_need_hide);
		$total_unread_count = Domain_Thread_Action_GetTotalUnreadCount::do($user_id);

		// обновляем badge с непрочитанными для пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, [$thread_map], true);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, (string) $user_id, [], $extra);

		// отправляем ws событие об отписке пользователя от треда
		Gateway_Bus_Sender::threadUnfollow($user_id, $thread_map, $total_unread_count);

		return $total_unread_count;
	}
}