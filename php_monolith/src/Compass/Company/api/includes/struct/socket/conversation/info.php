<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для info диалога
 */
class Struct_Socket_Conversation_Info {

	public string $conversation_key;
	public string $name;
	public int    $member_count;
	public string $avatar_file_map;

	/**
	 * Struct_Socket_Conversation_Meta constructor.
	 */
	public function __construct(string $conversation_key, string $name, int $member_count, string $avatar_file_map) {

		$this->conversation_key = $conversation_key;
		$this->name             = $name;
		$this->member_count     = $member_count;
		$this->avatar_file_map  = $avatar_file_map;
	}
}