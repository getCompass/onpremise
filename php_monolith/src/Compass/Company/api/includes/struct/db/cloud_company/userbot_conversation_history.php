<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Класс-структура для таблицы cloud_company.userbot_conversation_history
 */
class Struct_Db_CloudCompany_UserbotConversationHistory {

	/**
	 * Struct_Db_CloudCompany_UserbotConversationHistory constructor.
	 */
	public function __construct(
		public int    $row_id,
		public string $userbot_id,
		public int    $action_type,
		public int    $created_at,
		public int    $updated_at,
		public string $conversation_map,

	) {

	}
}