package Generator

import (
	"fmt"
)

// Channel структура канала
type Channel struct {
	ch         chan interface{}
	isReleased bool
}

// Channel получить канал
func (ch *Channel) Channel() chan interface{} {

	return ch.ch
}

// ReleaseChannel освободить канал
func (ch *Channel) Release() {

	// помечаем канал, как свободный
	ch.isReleased = true
}

// занять канал
func (ch *Channel) occupy() {

	// помечаем канал, как занятый
	ch.isReleased = false
}

// закрыть канал
func (ch *Channel) close() {

	ch.isReleased = false
	close(ch.ch)
}

// занять канал
func (ch *Channel) push(data interface{}) error {

	// если канал занят, возврашаем ошибку
	if !ch.isReleased {
		return fmt.Errorf("channel is occupied")
	}

	// помечаем канал, как занятый
	ch.isReleased = false
	ch.ch <- data

	return nil
}
