package conf_test // '_test' необходим для того чтобы избежать cycle import

import (
	"github.com/getCompassUtils/go_base_frame/tests/tester"
	"go_pivot_cache/api/conf"
	"testing"
)

// проверяем что обновление конфигурации происходит без ошибок
func TestUpdateConfig(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("обновление конфигурации происходит без ошибок")

	// обновляем конфигурацию
	err := conf.UpdateConfig()
	I.AssertEqual(nil, err)
}

// проверяем что можно получить конфигурацию
func TestGetConfig(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("можно получить конфигурацию")

	// получаем конфигурацию
	conf.GetConfig()
}

// проверяем что обновление sharding конфигурации происходит без ошибок
func TestUpdateShardingConfig(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("обновление sharding конфигурации происходит без ошибок")

	// обновляем конфигурацию
	err := conf.UpdateShardingConfig()
	I.AssertEqual(nil, err)
}

// проверяем что можно получить sharding конфигурацию
func TestGetShardingConfig(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("можно получить sharding конфигурацию")

	// получаем конфигурацию
	conf.GetShardingConfig()
}
