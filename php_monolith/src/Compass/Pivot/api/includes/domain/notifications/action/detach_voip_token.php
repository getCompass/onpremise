<?php

namespace Compass\Pivot;

/**
 * Экшн для открепления voip токена
 */
class Domain_Notifications_Action_DetachVoipToken {

	/**
	 * Открепляем токен
	 *
	 * @param string $token
	 *
	 * @return void
	 * @throws \returnException
	 */
	public static function do(string $token):void {

		// вычисляем хэш токена
		$token_hash = sha1($token);

		/** начало транзакции */
		Gateway_Db_PivotData_Main::beginTransaction();

		// проверяем, есть ли такой воип токен
		try {
			$voip_token_row = Gateway_Db_PivotData_DeviceTokenVoipList::getForUpdate($token_hash);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			return;
		}

		// если токен никому не принадлежит, то возвращаемся
		if ($voip_token_row->device_id === "") {
			return;
		}

		// обнуляем user_id и device_id у токена
		Gateway_Db_PivotData_DeviceTokenVoipList::set($token_hash, [
			"device_id"  => "",
			"user_id"    => 0,
			"updated_at" => time(),
		]);

		Gateway_Db_PivotData_Main::commitTransaction();
		/** конец транзакции */

		// удаляем токен у девайса
		self::_deleteTokenFromDevice($token, $voip_token_row);
	}

	/**
	 * Удаляем токен у девайса
	 *
	 * @param string                                  $token
	 * @param Struct_Db_PivotData_DeviceTokenVoipList $voip_token_row
	 *
	 * @return void
	 * @throws \returnException
	 */
	protected static function _deleteTokenFromDevice(string $token, Struct_Db_PivotData_DeviceTokenVoipList $voip_token_row):void {

		/** начало транзакции */
		Gateway_Db_PivotData_Main::beginTransaction();

		try {
			$device = Gateway_Db_PivotData_DeviceList::getForUpdate($voip_token_row->device_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			return;
		}

		$token_list = Type_User_Notifications_DeviceExtra::getTokenList($device["extra"]);

		// ищем в списке токенов нужный нам
		foreach ($token_list as $key => $token_item) {

			// если нашли токен, удаляем и обновляем экстру
			if ($token_item["token"] === $token) {

				unset($token_list[$key]);
				$extra = Type_User_Notifications_DeviceExtra::setTokenList($device["extra"], array_values($token_list));
				Gateway_Db_PivotData_DeviceList::set($device["device_id"], [
					"extra"      => $extra,
					"updated_at" => time(),
				]);
				break;
			}
		}

		Gateway_Db_PivotData_Main::commitTransaction();
		/** конец транзакции */
	}

}