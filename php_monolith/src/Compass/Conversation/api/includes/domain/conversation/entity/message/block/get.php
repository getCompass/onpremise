<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для получение блоков сообщений
 */
class Domain_Conversation_Entity_Message_Block_Get {

	protected const _GLOBALS_KEY = __CLASS__ . "_message_block"; // Важно, для класса предусмотрено кэширование так как блоки тяжело получать

	// проверям существует ли блок
	public static function getBlockRow(string $conversation_map, string $message_map, array $dynamic_row, bool $is_only_active = false):mixed {

		$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);

		// проверяем что блок существует, если вдруг сообщение было почищено при удалении архивного сервера
		if (!Domain_Conversation_Entity_Message_Block_Main::isExist($dynamic_row, $block_id)) {
			throw new cs_Message_IsNotExist("Message is not exist");
		}
		if (isset($GLOBALS[self::_GLOBALS_KEY . "_" . $conversation_map][$block_id])) {
			return $GLOBALS[self::_GLOBALS_KEY . "_" . $conversation_map][$block_id];
		}

		// если имеющийся у нас блок не тот который нужен
		$block_row                                                        = self::_getBlockRow($block_id, $conversation_map, $dynamic_row, $is_only_active);
		$GLOBALS[self::_GLOBALS_KEY . "_" . $conversation_map][$block_id] = $block_row;

		if (!isset($block_row["block_id"])) {
			return false;
		}

		return $block_row;
	}

	// получаем блок
	protected static function _getBlockRow(int $block_id, string $conversation_map, array $dynamic_row, bool $is_only_active):array {

		$is_active = Domain_Conversation_Entity_Message_Block_Main::isActive($dynamic_row, $block_id);

		if ($is_active) {
			return Gateway_Db_CompanyConversation_MessageBlock::getOne($conversation_map, $block_id);
		}

		throw new ParseFatalException("Unsupported function");
	}

	// получаем блоки с сообщениями совершая запросы к базе и архивному серверу
	// @long сидел думал как сделать и понятно и коротко, но ток длинно вышло, зато в целом сразу видно по функции какие блоки откуда берутся :)
	public static function getBlockListRowByIdList(string $conversation_map, array $block_id_list):array {

		$need_block_id_list   = [];
		$exist_block_row_list = [];
		foreach ($block_id_list as $block_id) {

			if (isset($GLOBALS[self::_GLOBALS_KEY . "_" . $conversation_map][$block_id])) {

				$exist_block_row_list[$block_id] = $GLOBALS[self::_GLOBALS_KEY . "_" . $conversation_map][$block_id];
				continue;
			}

			$need_block_id_list[] = $block_id;
		}

		if (count($need_block_id_list) < 1) {
			return $exist_block_row_list;
		}

		// идем в базу за горячими
		$output = Gateway_Db_CompanyConversation_MessageBlock::getList($conversation_map, $need_block_id_list, true);
		foreach ($output as $block_id => $block_row) {

			$exist_block_row_list[$block_id]                                  = $block_row;
			$GLOBALS[self::_GLOBALS_KEY . "_" . $conversation_map][$block_id] = $block_row;
		}

		return $exist_block_row_list;
	}
	// получаем блоки с сообщениями совершая запросы к базе и архивному серверу
	// @long сидел думал как сделать и понятно и коротко, но ток длинно вышло, зато в целом сразу видно по функции какие блоки откуда берутся :)
	public static function getBlockListRowByMessageMapList(string $conversation_map, array $dynamic_row, array $message_map_list):array {

		if (isTestServer() && !isset($dynamic_row["start_block_id"])) {
			throw new \cs_RowIsEmpty();
		}

		$active_block_id_list       = [];
		$exist_block_row_list       = [];
		$not_exist_message_map_list = [];
		foreach ($message_map_list as $v) {

			$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($v);
			if (!Domain_Conversation_Entity_Message_Block_Main::isExist($dynamic_row, $block_id)) {

				$not_exist_message_map_list[] = $v;
				continue;
			}

			if (isset($GLOBALS[self::_GLOBALS_KEY . "_" . $conversation_map][$block_id])) {

				$exist_block_row_list[$block_id] = $GLOBALS[self::_GLOBALS_KEY . "_" . $conversation_map][$block_id];
				continue;
			}

			// разделяем блоки на горячие и архивные
			if (Domain_Conversation_Entity_Message_Block_Main::isActive($dynamic_row, $block_id)) {

				$active_block_id_list[] = $block_id;
			}
		}

		// идем в базу за горячими
		if (count($active_block_id_list) > 0) {

			$output = Gateway_Db_CompanyConversation_MessageBlock::getList($conversation_map, array_unique($active_block_id_list), true);
			foreach ($output as $block_id => $block_row) {

				$exist_block_row_list[$block_id]                                  = $block_row;
				$GLOBALS[self::_GLOBALS_KEY . "_" . $conversation_map][$block_id] = $block_row;
			}
		}
		return [$exist_block_row_list, $not_exist_message_map_list];
	}
}