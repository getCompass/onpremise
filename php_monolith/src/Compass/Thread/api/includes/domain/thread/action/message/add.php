<?php

namespace Compass\Thread;

use JetBrains\PhpStorm\ArrayShape;


/**
 * добавляет одно сообщение
 */
class Domain_Thread_Action_Message_Add {

	/**
	 * @param string $thread_map
	 * @param array  $meta_row
	 * @param array  $message
	 *
	 * @return array[]
	 *
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	#[ArrayShape(["meta_row" => "array", "message" => "false|mixed"])]
	public static function do(string $thread_map, array $meta_row, array $message):array {

		$output = Domain_Thread_Action_Message_AddList::do($thread_map, $meta_row, [$message]);
		return [
			"meta_row" => $output["meta_row"],
			"message"  => reset($output["message_list"]),
		];
	}
}