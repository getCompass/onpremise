package senderEvents

type EventVersionedInterface interface {
	GetData() interface{}
	GetVersion() int
}
