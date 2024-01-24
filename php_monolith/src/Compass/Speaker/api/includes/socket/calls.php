<?php

namespace Compass\Speaker;

/**
 * группа сокет-методов - предназначена для работы со звонками
 */
class Socket_Calls extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"getInfo",
		"doFinishSingleCall",
		"getBatchingInfo",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * получаем информацию о звонке
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getInfo():array {

		$call_map = $this->post("?s", "call_map");

		// получаем инфо звонке
		$meta_row = Type_Call_Meta::get($call_map);

		// форматируем объект call
		$temp           = Type_Call_Utils::prepareCallForFormat($meta_row, $this->user_id);
		$formatted_call = Apiv1_Format::call($temp);

		return $this->ok([
			"call" => (object) $formatted_call,
		]);
	}

	/**
	 * завершаем вызов
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function doFinishSingleCall():array {

		$opponent_user_id = $this->post("?i", "opponent_user_id");

		// получаем последний звонок пользователя, который блокирует собеседника
		$last_call_row = Type_Call_Main::getUserLastCall($this->user_id);

		// если отсутствует последний звонок у пользователя или он уже завершен, то возвращаем ок
		if ($last_call_row === false || $last_call_row->is_finished == 1) {
			return $this->ok();
		}

		// отправляем задачу хукера на завершение звонка
		Type_Phphooker_Main::doFinishSingleCall($last_call_row->call_map, $this->user_id, $opponent_user_id);

		return $this->ok();
	}

	/**
	 * получаем информацию по звонкам
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getBatchingInfo():array {

		$call_map_list = $this->post("?a", "call_map_list");

		// получаем звонки из базы
		$call_meta_list = Type_Call_Meta::getAll($call_map_list);

		// собираем ответ
		$output = [];
		foreach ($call_meta_list as $v) {

			// если пользователь никогда не был участником звонка
			if (!isset($v["users"][$this->user_id])) {
				throw new \parseException(__METHOD__ . ": user was not member of call");
			}

			// получаем необходимые поля
			$started_at  = Type_Call_Users::getStartedAt($v["users"][$this->user_id]);
			$started_at  = $started_at == 0 ? time() : $started_at;
			$finished_at = Type_Call_Users::getFinishedAt($v["users"][$this->user_id]);
			$finished_at = $finished_at == 0 ? time() : $finished_at;
			$output[]    = [
				"call_map"       => $v["call_map"],
				"call_report_id" => Gateway_Db_CompanyCall_CallMeta::getReportCallId($v["extra"]),
				"duration"       => $finished_at - $started_at,
			];
		}

		return $this->ok([
			"call_info_list" => (array) $output,
		]);
	}
}
