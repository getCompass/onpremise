<?php

namespace Compass\Userbot;

/**
 * Действие добавления запроса для бота
 *
 * Class Domain_Userbot_Action_AddRequest
 */
class Domain_Userbot_Action_AddRequest {

	/**
	 * выполняем
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do(string $token, array $request_data, array $result_data, int $status = Domain_Request_Entity_Request::STATUS_NEED_WORK):Struct_Db_UserbotMain_Request {

		// генерируем id для запроса
		$request_id = generateUUID();

		try {

			$request = new Struct_Db_UserbotMain_Request($request_id, $token, $status, 0, 0, time(), 0, $request_data, $result_data);
			Gateway_Db_UserbotMain_RequestList::insert($request);
		} catch (\cs_RowDuplication) {

			// в случае если такая запись существует, то перегенерируем request_id и попытаемся заново
			return self::do($token, $request_data, $result_data, $status);
		}

		return $request;
	}
}