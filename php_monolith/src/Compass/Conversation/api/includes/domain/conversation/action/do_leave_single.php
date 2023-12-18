<?php

namespace Compass\Conversation;

/**
 * Action для выхода из диалога
 */
class Domain_Conversation_Action_DoLeaveSingle {

	/**
	 * выполняем экшн
	 */
	public static function do(string $conversation_map, int $user_id, int $opponent_user_id):void {

		// получаем далог со стороны опонента
		$opponent_left_menu_row = Type_Conversation_LeftMenu::get($opponent_user_id, $conversation_map);

		// очищаем кэш треда
		Type_Phphooker_Main::sendClearThreadMetaCache($conversation_map);

		// обновляем is_allowed диалога, ставим статус — ALLOW_STATUS_NEED_CHECK, а не ALLOW_STATUS_MEMBER_DISABLED
		// чтобы система сама обновила диалог до статуса ALLOW_STATUS_MEMBER_DISABLED и произвела все необходимые действия
		// для блокировки диалога и скрываем single диалог
		// записываем allow_status_alias в левоем меню собеседнику уволенного сотрудника
		Gateway_Db_CompanyConversation_ConversationMeta::set($conversation_map, [
			"allow_status" => ALLOW_STATUS_NEED_CHECK,
			"updated_at"   => time(),
		]);

		// если со стороны оппонента диалог был замьючен и он существует
		$set = self::_setUnmutedIfNeed($opponent_user_id, $conversation_map, $opponent_left_menu_row);

		// со стороны оппонента - записываем в левое меню о том что пользователь кикнут и обнуляем время мьюта
		$set["allow_status_alias"] = Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DISABLED;
		Domain_User_Action_Conversation_UpdateLeftMenu::do($opponent_user_id, $conversation_map, $set);

		// со стороны кикнутого - покидаем диалог
		Helper_Single::remove($user_id, $conversation_map, $opponent_user_id);
	}

	/**
	 * если чат нужно размьютить
	 *
	 * @throws \parseException
	 */
	protected static function _setUnmutedIfNeed(int $opponent_user_id, string $conversation_map, array $opponent_left_menu_row):array {

		// если со стороны оппонента диалог был замьючен и что он существует
		$set = [];
		if (isset($opponent_left_menu_row["user_id"]) &&
			($opponent_left_menu_row["muted_until"] > 0 || $opponent_left_menu_row["is_muted"] > 0)) {

			$set = [
				"is_muted"    => 0,
				"muted_until" => 0,
			];

			// со стороны оппонента - размьючиваем
			Domain_Conversation_Entity_Dynamic::setUnmuted($conversation_map, $opponent_user_id);

			// шлем эвент что размьютитили чат
			Gateway_Bus_Sender::conversationMutedChanged($opponent_user_id, $conversation_map, 0, 0);
		}

		return $set;
	}
}
