package handlerTcp

import (
	"github.com/getCompassUtils/go_base_frame/tests/tester"
	"testing"
)

// проверяем удачный вариант исполнения метода system.status
func TestSystemStatus(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("метод system.status возвращает успешный статус")

	// вызываем метод
	response := systemHandler{}.status([]byte("{}"))

	// сравниваем
	I.AssertEqual("ok", response.Status)
}
