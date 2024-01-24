<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Класс-структура для таблицы cloud_company.userbot_conversation_rel
 */
class Struct_Db_CloudCompany_UserbotConversationRel {

	/**
	 * Struct_Db_CloudCompany_UserbotConversationRel constructor.
	 */
	public function __construct(
		public int    $row_id,
		public string $userbot_id,
		public int    $conversation_type,
		public int    $created_at,
		public string $conversation_map,

	) {

	}
}