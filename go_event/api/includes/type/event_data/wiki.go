package EventData

/* Пакет для работы с данными события.
   Важно — имя каждого типа должно начинаться с категории, к оторой принаждлежит событие */
/* В этом файле описаны структуры данных событий категории wiki */

// данные события пользователь превысил число попыток логина по паролю в заметку
type WikiPagePasswordAttemptLimitExceeded struct {
	UserId    int64  `json:"user_id"`
	ThreadMap string `json:"thread_map"`
}
