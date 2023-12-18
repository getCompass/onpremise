<?php

namespace Compass\Pivot;

/**
 * Структура document
 */
class Struct_Config_Lang_Document {

	public string $name;
	public string $title;
	public string $description;
	public string $file_url;
	public string $file_key;
	public int    $version;

	/**
	 * Struct_Config_Lang_Document constructor.
	 *
	 */
	public function __construct(

		string $name,
		string $title,
		string $description,
		string $file_url,
		string $file_key,
		int    $version,
	) {

		$this->name        = $name;
		$this->title       = $title;
		$this->description = $description;
		$this->file_url    = $file_url;
		$this->file_key    = $file_key;
		$this->version     = $version;
	}
}
