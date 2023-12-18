package ChatMessage

/** Пакет работы с сообщениями */
/** В файле описаны базовые типы сообщений текст и файл */

// структура поддерживаемых сообщений общего типа
type CommonMessageTypeListStruct struct {
	TextMessage string // обычное текстовое
	FileMessage string // обычный файл
}

// структура текстового сообщения
type TextMessage struct {
	Type string `json:"type"`
	Text string `json:"text"`
}

// структура файлового сообщения
type FileMessage struct {
	Type     string `json:"type"`
	FileMap  string `json:"file_map"`
	FileName string `json:"file_name"`
}

// список поддерживаемых сообщений общего типа и их названия
var CommonMessageTypeList = CommonMessageTypeListStruct{
	TextMessage: "text",
	FileMessage: "file",
}

// генерирует сообщение с текстом
func MakeTextMessage(text string) TextMessage {

	return TextMessage{
		Type: MessageTypeList.Common.TextMessage,
		Text: text,
	}
}

// генерирует сообщение с файлом
func MakeFileMessage(fileMap string, fileName string) FileMessage {

	return FileMessage{
		Type:     MessageTypeList.Common.FileMessage,
		FileMap:  fileMap,
		FileName: fileName,
	}
}
