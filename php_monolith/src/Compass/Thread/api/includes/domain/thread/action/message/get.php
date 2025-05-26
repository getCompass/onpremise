<?php

namespace Compass\Thread;

use CompassApp\Pack\Message\Thread;

/**
 * Действие получения сообщения по его message_map
 */
class Domain_Thread_Action_Message_Get {

	/**
	 * Получить сообщение из блока
	 *
	 * @param string $message_map
	 *
	 * @return array
	 */
	public static function do(string $message_map):array {

		// получаем id блока, откуда этот message_map
		$block_id   = Thread::getBlockId($message_map);
		$thread_map = Thread::getThreadMap($message_map);

		$message_block = Gateway_Db_CompanyThread_MessageBlock::getOne($thread_map, $block_id);

		// достаем сообщение из блока сообщений
		return Domain_Thread_Entity_MessageBlock_Message::get($message_map, $message_block);
	}

}