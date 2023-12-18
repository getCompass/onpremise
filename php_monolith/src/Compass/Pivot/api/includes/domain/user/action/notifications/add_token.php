<?php

namespace Compass\Pivot;

/**
 * Добавляем токен в базу
 */
class Domain_User_Action_Notifications_AddToken {

	/**
	 * Выполнить действие принятия токена
	 *
	 * @throws cs_NotificationsInvalidToken
	 * @throws cs_NotificationsUnsupportedTokenType
	 * @throws \queryException
	 * @throws \returnException|\cs_RowIsEmpty
	 */
	public static function do(int $user_id, string $token, int $token_type):void {

		// получаем id устройства пользователя
		$device_id = getDeviceId();

		// выбрасываем ошибку, если пришел некорректный токен
		if (!Type_User_Notifications::isTokenTypeAllowed($token_type)) {
			throw new cs_NotificationsUnsupportedTokenType();
		}

		// валидация токена
		if (!Type_Api_Validator::isNotificationToken($token)) {
			throw new cs_NotificationsInvalidToken();
		}

		// если есть ios девайс с таким же voip токеном - удаляем его
		if ($token_type == Type_User_Notifications::TOKEN_TYPE_APNS_VIOP) {
			Type_User_Notifications::deleteDuplicateTokenDevice($token, $user_id, $device_id, getUa());
		}

		// добавляем токен push-уведомлений
		Type_User_Notifications::addToken($user_id, $token_type, $token, $device_id, getUa());
	}
}