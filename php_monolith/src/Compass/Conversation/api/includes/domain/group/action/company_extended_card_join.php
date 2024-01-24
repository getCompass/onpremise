<?php

namespace Compass\Conversation;

/**
 * Action для добавления пользователей в группы расширенной карточки
 */
class Domain_Group_Action_CompanyExtendedCardJoin {

	/**
	 * Добавляем пользователей в группы расширенной карточки
	 *
	 * @throws \parseException
	 */
	public static function do(array $user_id_list, int $creator_user_id):void {

		$group_list = Domain_Group_Entity_Company::EXTENDED_GROUP_LIST;

		// если пока не надо автоматически добавлять в чат спасибо - возвращаем в дефолтной как было
		if (!IS_NEED_CREATE_RESPECT_CONVERSATION) {
			$group_list[] = Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME;
		}

		// добавляем пользователя в каждую группу из списка
		foreach ($group_list as $group_key_name) {

			// получаем map для необходимого диалога
			$conversation_map = Type_Company_Default::getCompanyGroupConversationMapByKey($group_key_name);

			// если вдруг не существует, то скипаем диалог
			if (mb_strlen($conversation_map) < 1) {
				continue;
			}

			try {

				// получаем мету;
				// дальше мы еще раз получим мету, но уже для блокировки
				// в целом мета там будет получена n раз в зависимости от числа юзеров в компании,
				$meta_row = Gateway_Db_CompanyConversation_ConversationMetaLegacy::getOne($conversation_map);
			} catch (\cs_RowIsEmpty) {
				continue;
			}

			// пытаемся добавить всех пользователей в диалог
			foreach (array_unique($user_id_list) as $user_id) {

				// если пользователь уже является участником диалога, то пропускаем
				if (Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
					continue;
				}

				// определяем роль для пользователя, если это тот, кто затриггерил обновление типа карточки,
				// то задаем ему роль владельца группы, иначе — обычный участник
				$role = $user_id === $creator_user_id ? Type_Conversation_Meta_Users::ROLE_OWNER : Type_Conversation_Meta_Users::ROLE_DEFAULT;

				// вступаем в диалог
				Helper_Groups::doJoin($conversation_map, $user_id, role: $role);
			}
		}
	}
}
