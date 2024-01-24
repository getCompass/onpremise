package analyticsWs

const (

	// статус ws
	OnOpenWsConnect  = 1 // Открыли ws соединение
	OnEventSend      = 2 // Отправили ивент
	OnEventIsNotSend = 3 // Не отправили ивент
	OnCloseWsConnect = 4 // Закрыли соединение ws
)
