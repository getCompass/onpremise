<?php

namespace Compass\Conversation;

/**
 * Action для получения общих групп с пользователем
 */
class Domain_Conversation_Action_GetShared {

	/**
	 * Получить общие группы с пользователем
	 */
	public static function do(int $user_id, int $opponent_user_id):array {

		$user_groups          = Gateway_Db_CompanyConversation_UserLeftMenu::getGroupsByUser($user_id);
		$opponent_user_groups = Gateway_Db_CompanyConversation_UserLeftMenu::getGroupsByUser($opponent_user_id);

		$result_groups = array_intersect_key($user_groups, $opponent_user_groups);

		usort($result_groups, function(array $a, array $b) {

			return $b["is_favorite"] <=> $a["is_favorite"] ?:
				$b["is_mentioned"] <=> $a["is_mentioned"] ?:
					$b["updated_at"] <=> $a["updated_at"];
		});

		// убираем legacy типы
		$result_groups = Domain_Conversation_Entity_LegacyTypes::filterLeftMenu($result_groups);

		return array_values($result_groups);
	}
}
