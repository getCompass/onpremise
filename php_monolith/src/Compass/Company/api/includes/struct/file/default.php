<?php

namespace Compass\Company;

/**
 * Class Struct_File_Default
 */
class Struct_File_Default {

	public string $hiring_conversation_avatar_file_key;
	public string $notes_conversation_avatar_file_key;
	public string $support_conversation_avatar_file_key;
	public string $respect_conversation_avatar_file_key;

	/**
	 * Struct_File_Default constructor.
	 *
	 * @param string $hiring_conversation_avatar_file_key
	 * @param string $notes_conversation_avatar_file_key
	 * @param string $support_conversation_avatar_file_key
	 * @param string $respect_conversation_avatar_file_key
	 */
	public function __construct(string $hiring_conversation_avatar_file_key, string $notes_conversation_avatar_file_key,
					    string $support_conversation_avatar_file_key, string $respect_conversation_avatar_file_key) {

		$this->hiring_conversation_avatar_file_key  = $hiring_conversation_avatar_file_key;
		$this->notes_conversation_avatar_file_key   = $notes_conversation_avatar_file_key;
		$this->support_conversation_avatar_file_key = $support_conversation_avatar_file_key;
		$this->respect_conversation_avatar_file_key = $respect_conversation_avatar_file_key;
	}

	/**
	 * конвертируем в массив
	 */
	public function convertToArray():array {

		return [
			"hiring_conversation_avatar_file_key"  => (string) $this->hiring_conversation_avatar_file_key,
			"notes_conversation_avatar_file_key"   => (string) $this->notes_conversation_avatar_file_key,
			"support_conversation_avatar_file_key" => (string) $this->support_conversation_avatar_file_key,
			"respect_conversation_avatar_file_key" => (string) $this->respect_conversation_avatar_file_key,
		];
	}
}