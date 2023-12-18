<?php

namespace Compass\Thread;

/**
 *
 * Подготавливаем dynamic диалога для работы с тредами
 */
class Domain_Thread_Action_FilterMetaListIfAccessParent {

	/**
	 * выполняем
	 */
	public static function do(int $user_id, array $meta_list, Struct_SourceParentRel_Dynamic $source_parent_rel_dynamic):array {

		$allowed_meta_list = [];

		foreach ($meta_list as $meta_row) {

			try {

				$access_data = Helper_Threads::getAccessDataIfParentConversation($meta_row, $source_parent_rel_dynamic, $user_id);

				// получаем флаг имеет ли пользователь доступ к сообщению-родителю
				if ($access_data["is_user_have_access"]) {
					$allowed_meta_list[] = $meta_row;
				}
			} catch (cs_Message_IsDeleted) {
				continue;
			}
		}

		return $allowed_meta_list;
	}
}