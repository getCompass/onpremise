package EventBroker

type listener struct {
	subscriber string
	group      string
	trigger    TriggerFn
}

// MakeListener создает нового слушателя
func MakeListener(subscriber, group string, trigger TriggerFn) *listener {

	return &listener{
		subscriber: subscriber,
		group:      group,
		trigger:    trigger,
	}
}

// сравнивает двух слушателей
func (listener *listener) isEqual(compareTo *listener) bool {

	return listener.subscriber == compareTo.subscriber && listener.group == compareTo.group
}
