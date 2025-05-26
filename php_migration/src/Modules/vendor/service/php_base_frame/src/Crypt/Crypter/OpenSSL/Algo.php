<?php declare(strict_types=1);

namespace BaseFrame\Crypt\Crypter\OpenSSL;

/**
 * Поддерживаемые OpenSSL алгоритмы шифрования.
 */
enum Algo : string {

	case AES256CBC = "AES-256-CBC";
}