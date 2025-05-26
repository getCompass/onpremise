<?php

/**
 * класс служит для хранения конфигов архивных серверов, нод для быстрого доступа к ним из любого скрипта
 * ВАЖНО НЕ ХРАНИТЬ В SharedMemory ПЕРСОНАЛЬНУЮ ИНФОРМАЦИЮ ПОЛЬЗОВАТЕЛЯ так как доступ к разделяемой памяти имеет любая программа
 *
 * полезная информация для работы с разделяемой памятью из терминала
 * ipcs -p для просмотра всех занятых ячеек памяти
 * ipcrm -m <shmid> для очистки ячейки памяти из консоли
 */
class SharedMemory {

	/**
	 * получение переменной из памяти
	 *
	 * @param int   $shm_key
	 * @param false $default
	 *
	 * @return string
	 * @mixed
	 */
	public static function get(int $shm_key, $default = false):string {

		$shm_id = @shmop_open($shm_key, "a", 0666, 0);
		if (!$shm_id) {

			// отдаем пустую строку если ошибка
			return $default;
		}

		// получаем значение из памяти
		$shm_data = shmop_read($shm_id, 0, shmop_size($shm_id));

		// возвращаем значение переменной, нужно делать trim, чтобы удалить пустые байты в конце строки
		return trim($shm_data);
	}

	// устанавливаем значение переменной
	public static function set(int $shm_key, string $value):void {

		// если область занята то очищаем ее
		$shm_id = @shmop_open($shm_key, "a", 0666, 0);
		if ($shm_id) {

			// ВАЖНО удалять содержимое ячейки памяти если она не пуста
			// так как в памяти может остаться не нужная нам переменная
			shmop_delete($shm_id);
		}

		// убираем пустые байты
		$value = trim($value);

		// открываем область памяти для записи пишем и закрываем
		$shm_id = @shmop_open($shm_key, "n", 0666, strlen($value));
		if ($shm_id) {

			shmop_write($shm_id, $value, 0);
		}
	}

	// удаляем значение из памяти
	public static function delete(int $shm_key):void {

		$shm_id = @shmop_open($shm_key, "a", 0, 0);
		@shmop_delete($shm_id);
	}
}
