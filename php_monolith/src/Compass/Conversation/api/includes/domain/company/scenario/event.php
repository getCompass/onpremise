<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Server\ServerProvider;
use BaseFrame\System\Locale;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Класс обработки сценариев событий.
 */
class Domain_Company_Scenario_Event {

	/**
	 * Компания пробудилась ото сна
	 *
	 * @param Struct_Event_Company_OnWakeUp $event_data
	 *
	 * @return Type_Task_Struct_Response
	 * @throws ParseFatalException
	 * @throws \returnException
	 * @noinspection PhpUnusedParameterInspection
	 */
	#[Type_Attribute_EventListener(Type_Event_Company_OnWakeUp::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::DEFAULT_GROUP])]
	public static function onWakeUp(Struct_Event_Company_OnWakeUp $event_data):Type_Task_Struct_Response {

		// получаем участников компании
		$user_role_list = Gateway_Socket_Company::getUserRoleList([Member::ROLE_MEMBER, Member::ROLE_ADMINISTRATOR]);

		$need_user_id_list = [];
		foreach ($user_role_list as $role => $user_id_list) {

			if (!in_array($role, [Member::ROLE_MEMBER, Member::ROLE_ADMINISTRATOR])) {
				continue;
			}

			// проверяем, есть ли у пользователя уже диалог со службой поддержки
			foreach ($user_id_list as $user_id) {

				try {
					Type_Conversation_LeftMenu::getSupportGroupByUser($user_id);
				} catch (RowNotFoundException) {
					$need_user_id_list[] = $user_id;
				}
			}
		}

		$avatar_file_map = Domain_Group_Action_GetSupportDefaultAvatarFileMap::do();

		// создаём диалог со службой поддержки у кого он отсутствовал
		foreach ($need_user_id_list as $user_id) {
			!ServerProvider::isOnPremise() && Type_Conversation_Support::create($user_id, $avatar_file_map, Locale::LOCALE_RUSSIAN);
		}

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}
}
