<?php

namespace BaseFrame\Database\Hook;

/**
 * Режим работы хука для БД.
 */
enum Action: string {

	case READ = "read";
	case WRITE = "write";
}
