package validationRule

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/tests/tester"
	"testing"
)

// проверяет правило равенства
func TestOkEqualRule(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("корректно работает правило equal")

	// проверяем для целочисленных
	var intValue int64 = 1
	raw := fmt.Sprintf("{\"value\": %d}", intValue)

	encoded := json.RawMessage{}
	_ = go_base_frame.Json.Unmarshal([]byte(raw), &encoded)

	callback, _ := Equal{}.Make(encoded)

	I.AssertEqual(true, callback(json.Number(functions.Int64ToString(intValue))))
	I.AssertEqual(false, callback(json.Number(functions.Int64ToString(9))))

	// проверяем для строк
	var stringValue = "test"

	raw = fmt.Sprintf("{\"value\": \"%s\"}", stringValue)

	encoded = json.RawMessage{}
	_ = go_base_frame.Json.Unmarshal([]byte(raw), &encoded)

	callback, _ = Equal{}.Make(encoded)

	I.AssertEqual(true, callback(stringValue))
	I.AssertEqual(false, callback(functions.GenerateUuid()))
}

// проверяет правило белого списка
func TestOkWhiteListRule(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("корректно работает правило white list")

	var intValueOne int64 = 1
	var intValueTwo int64 = 2
	raw := fmt.Sprintf("{\"list\": [%d, %d]}", intValueOne, intValueTwo)

	encoded := json.RawMessage{}
	_ = go_base_frame.Json.Unmarshal([]byte(raw), &encoded)

	callback, _ := WhiteList{}.Make(encoded)

	I.AssertEqual(true, callback(json.Number(functions.Int64ToString(intValueOne))))
	I.AssertEqual(false, callback(json.Number(functions.Int64ToString(9))))
}

// проверяет правило черного списка
func TestOkBlackListRule(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("корректно работает правило black list")

	var intValueOne int64 = 1
	var intValueTwo int64 = 2
	raw := fmt.Sprintf("{\"list\": [%d, %d]}", intValueOne, intValueTwo)

	encoded := json.RawMessage{}
	_ = go_base_frame.Json.Unmarshal([]byte(raw), &encoded)

	callback, _ := BlackList{}.Make(encoded)

	I.AssertEqual(false, callback(json.Number(functions.Int64ToString(intValueOne))))
	I.AssertEqual(true, callback(json.Number(functions.Int64ToString(9))))
}
