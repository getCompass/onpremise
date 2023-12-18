<?php

namespace Compass\Pivot;

/**
 * Class Struct_Db_PivotSystem_DefaultFile
 */
class Struct_Db_PivotSystem_DefaultFile {

	public string $dictionary_key;
	public string $file_key;
	public string $file_hash;
	public array  $extra;

	/**
	 * Struct_Db_PivotSystem_DefaultFile constructor.
	 *
	 */
	public function __construct(string $dictionary_key, string $file_key, string $file_hash, array $extra) {

		$this->dictionary_key = $dictionary_key;
		$this->file_key       = $file_key;
		$this->file_hash      = $file_hash;
		$this->extra          = $extra;
	}

	/**
	 * конвертируем в массив
	 *
	 */
	public function convertToArray():array {

		return [
			"dictionary_key" => (string) $this->dictionary_key,
			"file_key"       => (string) $this->file_key,
			"file_hash"      => (string) $this->file_hash,
			"extra"          => (array) $this->extra,
		];
	}
}