<?php

namespace Compass\Speaker;

/**
 * socket-класс для работы с жалобами в звонках
 */
class Socket_Reports extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. регистр не имеет значение */
	public const ALLOW_METHODS = [
		"getList",
		"setStatus",
	];

	// переопределяем функцию ok, добавляем замену всех map в key
	public function ok(array $output = []):array {

		$output = Type_Pack_Main::replaceMapWithKeys($output);
		return parent::ok($output);
	}

	/**
	 * получить список жалоб
	 *
	 * @return array
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getList() {

		$from_created_at = $this->post("?i", "from_created_at", time());
		$status          = $this->post("?i", "status", false);
		$count           = $this->post("?i", "count");
		$offset          = $this->post("?i", "offset", 0);

		// получаем список жалоб
		$report_list = Type_Call_Report::getList($this->user_id, $status, $from_created_at, $count, $offset);

		return $this->ok([
			"report_list" => (array) $report_list,
			"has_next"    => (int) count($report_list) == $count ? 1 : 0,
		]);
	}

	/**
	 * установить новый статус жалобы
	 *
	 * @return array
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function setStatus() {

		$report_id = $this->post("?i", "report_id");
		$status    = $this->post("?i", "status");
		$solution  = $this->post("?s", "solution", false);

		// проверяем на корректность статус
		$this->_throwIfIncorrectReportStatus($status);

		// проверяем решение жалобы
		if ($solution !== false) {
			$this->_throwIfIncorrectReportSolution($solution);
		}

		// проверяем есть ли такой репорт
		$report_row = Type_Call_Report::getByCallId($report_id);
		$this->_throwIfReportNotExist($report_row);

		// добавляем в extra id админа
		$extra             = fromJson($report_row["extra"]);
		$extra["admin_id"] = $this->user_id;
		$extra["solution"] = $solution ? $solution : "";

		// обновляем статус жалобы
		$set = [
			"status"     => $status,
			"updated_at" => time(),
			"extra"      => $extra,
		];
		Type_Call_Report::set($report_id, $set);

		return $this->ok();
	}

	// проверяем, корректен ли статус жалобы
	protected function _throwIfIncorrectReportStatus(int $status):void {

		if (!in_array($status, Type_Call_Report::STATUS_LIST)) {
			throw new \paramException("incorrect status of report");
		}
	}

	// проверяем, корректно ли решение репорта
	protected function _throwIfIncorrectReportSolution(string $solution):void {

		if (mb_strlen($solution) >= 2048) {
			throw new \paramException("solution too long");
		}
	}

	// проверяем, существует ли жалоба
	protected function _throwIfReportNotExist(array $report_row):void {

		if (!isset($report_row["report_id"])) {
			throw new \paramException("report not found");
		}
	}
}