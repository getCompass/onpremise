<?php

namespace Compass\Pivot;

/**
 * класс чистит каждую ночь сырую статистику рейтинга
 */
class Domain_Rating_Action_ClearRawRating {

	protected const _LIMIT = 1000; // лимит кол-ва записей удаляемых за раз

	/**
	 * Запускаем
	 */
	public static function run():void {

		self::_doForScreenTime();
		self::_doForActionCount();
		self::_doForMessageAnswerTime();
	}

	/**
	 * Рейтинг экранного времени
	 */
	protected static function _doForScreenTime():void {

		$min_created_at        = dayStart() - DAY14;
		$deleted_shard_id_list = [];
		foreach (range(1, 10_000_000, 1_000_000) as $shard_id) {

			do {

				// очищаем таблицу
				$deleted_count = Gateway_Db_PivotRating_ScreenTimeRawList::deleteListByCreatedAt($shard_id, $min_created_at, self::_LIMIT);
				if ($deleted_count > 0 && !in_array($shard_id, $deleted_shard_id_list)) {
					$deleted_shard_id_list[] = $shard_id;
				}
			} while ($deleted_count == self::_LIMIT);
		}

		// если записи не трогали, то и оптимизация таблицы не нужна
		if (count($deleted_shard_id_list) < 1) {
			return;
		}

		// выполняем optimize table после очистки
		foreach ($deleted_shard_id_list as $shard_id) {
			Gateway_Db_PivotRating_ScreenTimeRawList::optimize($shard_id);
		}
	}

	/**
	 * Рейтинг количества действий
	 */
	protected static function _doForActionCount():void {

		$min_created_at        = dayStart() - DAY14;
		$deleted_shard_id_list = [];
		foreach (range(1, 10_000_000, 1_000_000) as $shard_id) {

			do {

				// очищаем таблицу
				$deleted_count = Gateway_Db_PivotRating_ActionRawList::deleteListByCreatedAt($shard_id, $min_created_at, self::_LIMIT);
				if ($deleted_count > 0 && !in_array($shard_id, $deleted_shard_id_list)) {
					$deleted_shard_id_list[] = $shard_id;
				}
			} while ($deleted_count == self::_LIMIT);
		}

		// если записи не трогали, то и оптимизация таблицы не нужна
		if (count($deleted_shard_id_list) < 1) {
			return;
		}

		// выполняем optimize table после очистки
		foreach ($deleted_shard_id_list as $shard_id) {
			Gateway_Db_PivotRating_ActionRawList::optimize($shard_id);
		}
	}

	/**
	 * Рейтинг времени ответа на сообщения
	 */
	protected static function _doForMessageAnswerTime():void {

		$min_created_at        = dayStart() - DAY7;
		$deleted_shard_id_list = [];
		foreach (range(1, 10_000_000, 1_000_000) as $shard_id) {

			do {

				// очищаем таблицу
				$deleted_count = Gateway_Db_PivotRating_MessageAnswerTimeRawList::deleteListByCreatedAt($shard_id, $min_created_at, self::_LIMIT);
				if ($deleted_count > 0 && !in_array($shard_id, $deleted_shard_id_list)) {
					$deleted_shard_id_list[] = $shard_id;
				}
			} while ($deleted_count == self::_LIMIT);
		}

		// если записи не трогали, то и оптимизация таблицы не нужна
		if (count($deleted_shard_id_list) < 1) {
			return;
		}

		// выполняем optimize table после очистки
		foreach ($deleted_shard_id_list as $shard_id) {
			Gateway_Db_PivotRating_MessageAnswerTimeRawList::optimize($shard_id);
		}
	}
}