package jitsiVoipPushQueue

import "sync"

// объявляем указатели и иницализируем семафор
var (
	cachePointer  *queueStorage
	updatePointer *queueStorage
	mu            sync.Mutex
)

// объявляем флаг-переключатель, сигнализирующий на каком хранилище сейчас установлен указатель cachePointer
var (
	cachePointerOnFirstSwitcher = true
)

// функция для инициализации указателей
func InitVariables() {

	// устанавливаем указатели на хранилища
	// ЗДЕСЬ ОБЯЗАТЕЛЬНО ПРИ ИНИЦИАЛИЗАЦИИ УКАЗАТЕЛЬ CACHE POINTER СТОИТ НА FIRST ХРАНИЛИЩЕ
	cachePointer = &first
	updatePointer = &second
}

// функция для переключения указателей
func swapPointers() {

	// блокируем указатели
	mu.Lock()

	// если cachePointerOnFirst = true, то переключаем указатель кэша на второе храналище
	// а указатель updatePointer на первое
	if cachePointerOnFirstSwitcher {

		cachePointer = &second
		updatePointer = &first

		cachePointerOnFirstSwitcher = false
	} else {

		// и наоборот
		cachePointer = &first
		updatePointer = &second

		cachePointerOnFirstSwitcher = true
	}

	// снимаем блокировку указателей
	mu.Unlock()
}
