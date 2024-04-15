<?php

namespace Compass\FileNode;

/** некорректное имя файла */
class Domain_File_Exception_IncorrectFileName extends \DomainException {

	public function __construct(string $message = "incorrect file name", int $code = 0, ?Throwable $previous = null) {

		parent::__construct($message, $code, $previous);
	}
}