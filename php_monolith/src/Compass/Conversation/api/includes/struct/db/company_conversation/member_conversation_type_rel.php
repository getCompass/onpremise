<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура для диалога пользователя
 */
class Struct_Db_CompanyConversation_MemberConversationTypeRel {

	public int    $row_id;
	public int    $user_id;
	public int    $type;
	public string $conversation_map;
	public int    $created_at;

	/**
	 * Struct_Db_CompanyConversation_MemberConversationTypeRel constructor.
	 */
	public function __construct(int $row_id, int $user_id, int $type, string $conversation_map, int $created_at) {

		$this->row_id           = $row_id;
		$this->user_id          = $user_id;
		$this->type             = $type;
		$this->conversation_map = $conversation_map;
		$this->created_at       = $created_at;
	}
}