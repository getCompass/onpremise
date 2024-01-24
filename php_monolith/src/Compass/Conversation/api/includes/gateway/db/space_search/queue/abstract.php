<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Абстрактный класс-интерфейс для работы с таблицей очереди
 * Полностью реализует функционал для работы с таблицей. Класс должен наследоваться для работы с конкретной таблицей
 */
abstract class Gateway_Db_SpaceSearch_Queue_Abstract extends Gateway_Db_SpaceSearch_Main {

	/**
	 * Название таблицы
	 */
	protected const _TABLE_KEY = null;

	/**
	 * Вставляет пачку задач в очередь.
	 *
	 * @param Struct_Domain_Search_Task[] $task_list
	 */
	final public static function insert(array $task_list):void {

		$insert = array_map(static fn(Struct_Domain_Search_Task $el):array => [
			"type"        => $el->type,
			"error_count" => $el->error_count,
			"created_at"  => $el->created_at,
			"updated_at"  => $el->updated_at,
			"data"        => (array) $el->data,
		], $task_list);

		static::_connect()->insertArray(static::_getTableName(), $insert);
	}

	/**
	 * Обновляет данные задачи.
	 */
	final public static function update(array $task_id_list, array $set):void {

		// EXPLAIN: INDEX PRIMARY
		$queue = "UPDATE `?p` SET ?u WHERE `task_id` IN (?a) LIMIT ?i";
		static::_connect()->update($queue, static::_getTableName(), $set, $task_id_list, count($task_id_list));
	}

	/**
	 * Возвращает список записей для работы.
	 * @return Struct_Domain_Search_Task[]
	 */
	final public static function getForWork(int $count):array {

		// EXPLAIN: INDEX PRIMARY
		$queue  = "SELECT * FROM `?p` WHERE TRUE ORDER BY `task_id` ASC LIMIT ?i";
		$result = static::_connect()->getAll($queue, static::_getTableName(), $count);

		usort($result, static fn(array $a, array $b) => (int) $a["task_id"] <=> (int) $b["task_id"]);
		return array_map(static fn(array $row) => Struct_Domain_Search_Task::fromRow($row), $result);
	}

	/**
	 * Удаляет записи из очереди индексации.
	 */
	final public static function delete(array $task_id_list):void {

		// EXPLAIN: INDEX PRIMARY
		$queue = "DELETE FROM `?p` WHERE `task_id` IN (?a) LIMIT ?i";
		static::_connect()->delete($queue, static::_getTableName(), $task_id_list, count($task_id_list));
	}

	/**
	 * Возвращает число задач в очереди.
	 */
	final public static function count():int {

		// EXPLAIN: PRIMARY
		$queue  = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$result = static::_connect()->getOne($queue, static::_getTableName(), 1);

		return (int) $result["count"];
	}

	/**
	 * true если есть еще задачи в очереди
	 */
	final public static function isHasMoreTasks():bool {

		// EXPLAIN: PRIMARY
		// используем именно так, а не count - т.к count медленнее в несколько раз
		$queue = "SELECT `task_id` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = static::_connect()->getOne($queue, static::_getTableName(), 1);
		return (bool) isset($row["task_id"]);
	}

	/**
	 * Получаем название таблицы
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	final protected static function _getTableName():string {

		if (is_null(static::_TABLE_KEY) || mb_strlen(static::_TABLE_KEY) < 1) {
			throw new ParseFatalException("unexpected queue table name");
		}

		return static::_TABLE_KEY;
	}
}
