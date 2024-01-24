<?php

namespace Compass\Conversation;

use CompassApp\Domain\Member\Entity\Member;

/**
 * Action для добавления пользователя в диалоги из заявки
 */
class Domain_User_Action_CompanyRequestAdd {

	/**
	 * добавляем пользователя в диалоги из заявки
	 *
	 */
	public static function do(int $joined_user_id, array $single_conversation_autojoin_item_list, int $company_inviter_user_id):void {

		// если список пустой, выходим
		if (count($single_conversation_autojoin_item_list) < 1 && $company_inviter_user_id <= 0) {
			return;
		}

		$user_id_list_to_create = self::_resolveSingleListToCreate($single_conversation_autojoin_item_list, $company_inviter_user_id);
		$user_id_list_to_create = array_unique($user_id_list_to_create);

		// создаем диалог с каждым пользователем
		foreach ($user_id_list_to_create as $user_id) {

			try {
				$meta_row = Helper_Single::createIfNotExist($user_id, $joined_user_id, is_hidden_for_opponent: false);
			} catch (\Exception) {

				// ничего не делаем, пропускаем
				continue;
			}

			// если после инициализации диалога выставился нужный allow_status, то ничего не делаем
			if ($meta_row["allow_status"] == ALLOW_STATUS_GREEN_LIGHT) {
				continue;
			}

			// иначе подкручиваем нужный статус:

			// получаем информацию о пользователе, с которым создаем диалог
			$member_info = Gateway_Bus_CompanyCache::getMember($user_id);

			// если пользователь с которым создали диалог – не участник пространства или удалил аккаунт, то пропускаем его
			if (Member::isDisabledProfile($member_info->role) || \CompassApp\Domain\Member\Entity\Extra::getIsDeleted($member_info->extra)) {
				continue;
			}

			// устанавливаем allow_status зеленый свет
			Type_Conversation_Single::setIsAllowedInMetaAndLeftMenu($meta_row["conversation_map"], ALLOW_STATUS_GREEN_LIGHT, $user_id, $joined_user_id, $meta_row["extra"]);
		}
	}

	/**
	 * Составляет список пользователей, с которыми нужно нарисовать сингл-диалог
	 *
	 */
	protected static function _resolveSingleListToCreate(array $join_to_single, int $company_inviter_user_id):array {

		$output = [];

		foreach ($join_to_single as $item) {

			if ((int) $item["status"] === 1) {
				$output[] = $item["user_id"];
			}
		}

		// при вступлении пригласившим в компанию может оказаться другой пользователь
		// если изначально пригласивший покинул компанию
		if ($company_inviter_user_id !== 0 && !in_array($company_inviter_user_id, $join_to_single)) {
			$output[] = $company_inviter_user_id;
		}

		return $output;
	}
}
