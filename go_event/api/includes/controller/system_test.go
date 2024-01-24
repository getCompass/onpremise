package controller

import (
	"github.com/getCompassUtils/go_base_frame/tests/tester"
	"go_event/api/includes/type/request"
	"testing"
)

// проверяем удачный вариант исполнения метода system.status
func TestSystemStatus(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("метод system.status возвращает успешный статус")

	// вызываем метод
	response := systemController{}.status(request.Data{
		CompanyId:   1,
		RequestData: []byte("{}"),
	})

	// сравниваем
	I.AssertEqual("ok", response.Status)
}
