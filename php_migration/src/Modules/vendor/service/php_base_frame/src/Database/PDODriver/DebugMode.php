<?php

namespace BaseFrame\Database\PDODriver;

/**
 * Режим отладки драйвера PDO.
 */
enum DebugMode {

	case NONE;
	case CLI;
	case FILE;
}
