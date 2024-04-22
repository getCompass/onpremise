<?php declare(strict_types = 1);

namespace Compass\Premise;

/**
 * Class Struct_Task_Response
 * Ответ для метода обработки задачи.
 */
#[\JetBrains\PhpStorm\Immutable]
class Type_Task_Struct_Response extends Struct_Event_Default {

	/** @var int с каким статусом завершилась задача */
	public int $status;

	/** @var int когда задачу нужно будет взять в работу в следующий раз */
	public int $need_work_at;

	/** @var string сообщение, если есть */
	public string $message;

	/**
	 * Статический конструктор.
	 *
	 * @param int    $status
	 * @param int    $need_work_at
	 * @param string $message
	 *
	 * @return static
	 * @throws \parseException
	 */
	public static function build(int $status, int $need_work_at = 0, string $message = ""):static {

		return new static([
			"status"       => $status,
			"need_work_at" => $need_work_at,
			"message"      => $message,
		]);
	}
}
