package localizer

import (
	"github.com/getCompassUtils/go_base_frame/tests/tester"
	"testing"
)

/* Пакет, содержащий в себе текстовый контент сообщений и функции их формирования
   которые будут отправлены от лица ботов */

// проверяем, что подстановки проводятся правильно
func TestOkStringSubstitution(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("текстовые алиасы корректно заменяются на строки")

	var str = "%cup_count% %drink%"

	I.AssertEqual("2 чашки руссиано", ProcessSubstitutions(str, map[string]string{
		"cup_count": "2 чашки",
		"drink":     "руссиано",
	}))

	I.AssertEqual("An americano", ProcessSubstitutions(str, map[string]string{
		"cup_count": "An",
		"drink":     "americano",
	}))
}

// проверяем, что правило склонения по числа русского языка работает
func TestOkStringRules(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("правило plural::ru::numeric корректно склоняет существительные")

	var str0 = "0 <<plural::ru::numeric||0||чашка//чашки//чашек>> руссиано"
	var str1 = "1 <<plural::ru::numeric||1||чашка//чашки//чашек>> руссиано"
	var str2 = "2 <<plural::ru::numeric||2||чашка//чашки//чашек>> руссиано"
	var str5 = "5 <<plural::ru::numeric||5||чашка//чашки//чашек>> руссиано"
	var str10 = "10 <<plural::ru::numeric||10||чашка//чашки//чашек>> руссиано"
	var str21 = "21 <<plural::ru::numeric||21||чашка//чашки//чашек>> руссиано"

	I.AssertEqual("0 чашек руссиано", ProcessRules(str0, ruleSet))
	I.AssertEqual("1 чашка руссиано", ProcessRules(str1, ruleSet))
	I.AssertEqual("2 чашки руссиано", ProcessRules(str2, ruleSet))
	I.AssertEqual("5 чашек руссиано", ProcessRules(str5, ruleSet))
	I.AssertEqual("10 чашек руссиано", ProcessRules(str10, ruleSet))
	I.AssertEqual("21 чашка руссиано", ProcessRules(str21, ruleSet))
}

// проверяет, что комплексные правила не отваливаются
func TestComplexRules(testItem *testing.T) {

	// начинаем тест
	I := tester.StartTest(testItem)
	I.WantToTest("сложные подстановки не ломаются")

	// сложная подстановка
	var str = "%cup_count% <<plural::ru::numeric||%cup_count%||чашка//чашки//чашек>> руссиано"

	// проверяем, что произошла подстановка и склонение по числу
	I.AssertEqual("5 чашек руссиано", ProcessRules(ProcessSubstitutions(str, map[string]string{
		"cup_count": "5",
	}), ruleSet))
}
