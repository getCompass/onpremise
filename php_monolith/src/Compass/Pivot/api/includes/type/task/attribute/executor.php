<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Атрибут для методов-подписчиков на системные события.
 */
#[\Attribute]
class Type_Task_Attribute_Executor {

	/** @var string тип задачи, которое ожидает метод */
	public string $task_type;

	/** @var string класс данных для задачи */
	public string $struct_data_class;

	/**
	 * EventListener constructor.
	 *
	 * @param string $task_type
	 * @param string $struct_data_class
	 *
	 * @throws \parseException
	 */
	public function __construct(string $task_type, string $struct_data_class) {

		if (!class_exists($struct_data_class)) {
			throw new \parseException("passed non-existing class");
		}

		if (!is_subclass_of($struct_data_class, Struct_Event_Default::class)) {
			throw new \parseException("passed class is not struct");
		}

		$this->task_type         = $task_type;
		$this->struct_data_class = $struct_data_class;
	}

	/**
	 * Проверяет, является ли метод исполнителем таска.
	 *
	 * @param string $task_type
	 *
	 * @return bool
	 */
	public function isTaskExecutor(string $task_type):bool {

		return $this->task_type === $task_type;
	}

	/**
	 * Конвертирует данные из таска в данные задачи
	 *
	 * @param array $raw_data
	 *
	 * @return mixed
	 */
	public function convertDataToStruct(array $raw_data):mixed {

		if (method_exists($this->struct_data_class, "build")) {

			// часть структур живет на build
			return $this->struct_data_class::build(...$raw_data);
		}

		// а другая на new
		return new $this->struct_data_class(...$raw_data);
	}
}