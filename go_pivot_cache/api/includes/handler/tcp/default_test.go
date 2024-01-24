package handlerTcp

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/tests/tester"
	"testing"
)

// проверяем что метод Ok возвращает нужную структуру
func TestOkWithStruct(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("метод Ok возвращает нужную структуру")

	// формируем структуру ответа
	type responseStruct struct {
		userId  int64
		message string
	}

	// получаем объект ответа
	responseItem := responseStruct{
		userId:  1,
		message: "this is message",
	}

	// вызываем метод
	actual := Ok(responseItem)

	// формируем ожидаемый результат
	expected := ResponseStruct{
		Status:   "ok",
		Response: responseItem,
	}

	// сравниваем
	I.AssertEqual(expected, actual)
}

// проверяем что метод Ok возвращает пустую структуру, если ничего не передали
func TestOkEmpty(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("метод Ok возвращает пустую структуру, если ничего не передали")

	// вызываем метод
	actual := Ok()

	// формируем ожидаемый результат
	expected := ResponseStruct{
		Status:   "ok",
		Response: struct{}{},
	}

	// сравниваем
	I.AssertEqual(expected, actual)
}

// проверяем что метод Error возвращает нужную структуру
func TestError(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("метод Error возвращает нужную структуру")

	// вызываем метод
	actual := Error(1, "this is error message")

	// формируем ожидаемый результат
	expected := ResponseStruct{
		Status: "error",
		Response: ErrorStruct{
			ErrorCode: 1,
			Message:   "this is error message",
		},
	}

	// сравниваем
	I.AssertEqual(expected, actual)
}

// проверяем что метод work исполняет существующий контроллер
func TestWorkExistController(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("метод work исполняет существующий контроллер")

	// вызываем существующий метод
	response := work("system.status", []byte("{}"))

	// проверяем статус
	I.AssertEqual("ok", response.Status)
}

// проверяем что метод work ругается на несуществующий контроллер
func TestWorkNotExistController(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("метод work ругается на несуществующий контроллер")

	// вызываем несуществующий метод
	actual := work("example", []byte("{}"))

	// формируем ожидаемый результат
	expected := Error(103, "method in controller is not available")

	// сравниваем
	I.AssertEqual(expected, actual)
}

// проверяем что метод splitMethod разделяет метод на контроллер и название
func TestSplitMethod(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("метод splitMethod разделяет метод на контроллер и название")

	// формируем ожидаемый метод
	expectedController := "test"
	expectedMethod := "method"
	method := fmt.Sprintf("%s.%s", expectedController, expectedMethod)

	// разделяем метод на контроллер и название
	actualController, actualMethod := splitMethod(method)

	// проверяем контроллер
	I.AssertEqual(expectedController, actualController)

	// проверяем метод
	I.AssertEqual(expectedMethod, actualMethod)
}
