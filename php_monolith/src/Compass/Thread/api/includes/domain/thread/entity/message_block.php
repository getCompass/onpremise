<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы c блоками сообщений тредов.
 */
class Domain_Thread_Entity_MessageBlock {

	// максимальное число блоков, доступное в одном запросе
	public const MAX_GET_MESSAGES_BLOCK_COUNT = 5;

	/**
	 * Пытается сформировать корректный список блоков
	 * для дальнейшего чтения на основе переданного списка блоков.
	 */
	public static function resolveCorrectBlockIdList(Struct_Db_CompanyThread_ThreadDynamic $thread_dynamic, array $block_id_list):array {

		// если список блоков пришел пустой, то пытаемся выставить последние три блока
		if (count($block_id_list) === 0) {
			$block_id_list = range($thread_dynamic->last_block_id - 2, $thread_dynamic->last_block_id);
		}

		// оставляем блоки с корректными идентификаторами
		return array_filter($block_id_list, static fn(int $e) => $e > 0 && $e <= $thread_dynamic->last_block_id);
	}

	/**
	 * Возвращает указанный список блоков для указанного треда.
	 */
	public static function getList(string $thread_map, array $block_id_list):array {

		$block_list        = Gateway_Db_CompanyThread_MessageBlock::getList($thread_map, $block_id_list);
		$format_block_list = [];

		foreach ($block_list as $v) {
			$format_block_list[$v["block_id"]] = $v;
		}

		return $format_block_list;
	}

	/**
	 * Возвращает N блоков вокруг переданного списка блоков.
	 */
	public static function getAroundNBlocks(Struct_Db_CompanyThread_ThreadDynamic $thread_dynamic, array $block_id_list, int $count = self::MAX_GET_MESSAGES_BLOCK_COUNT):array {

		$previous_block_id_list = [];
		$next_block_id_list     = [];

		// если блоков нет, то и окружающих блоков тоже нет
		if ($thread_dynamic->last_block_id === 0) {
			return [$previous_block_id_list, $next_block_id_list];
		}

		$max_block_id = max($block_id_list);
		$min_block_id = min($block_id_list);

		if ($max_block_id <= 0 || $min_block_id <= 0 || $max_block_id > $thread_dynamic->last_block_id) {
			throw new ReturnFatalException("passed bad block id range");
		}

		// последний блок из списка предыдущих
		$last_previous_block_id = $min_block_id - 1;

		// если есть последний блок из списка предыдущих,
		// то формируем список предыдущих блоков в худшем случае тут будет range(1, 1)
		if ($last_previous_block_id >= 1) {

			$first_previous_block_id = max(1, $min_block_id - $count);
			$previous_block_id_list  = range($first_previous_block_id, $last_previous_block_id);
		}

		$first_next_block_id = $max_block_id + 1;

		// если есть первый блок из списка следующих, то формируем
		// список следующих блоков, худший вариант range(last_block_id, last_block_id)
		if ($first_next_block_id <= $thread_dynamic->last_block_id) {

			$last_next_block_id = min($thread_dynamic->last_block_id, $max_block_id + $count);
			$next_block_id_list = range($first_next_block_id, $last_next_block_id);
		}

		return [$previous_block_id_list, $next_block_id_list];
	}
}