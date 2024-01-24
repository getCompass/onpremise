package validationRule

import (
	"encoding/json"
	"errors"
	"github.com/getCompassUtils/go_base_frame"
	"strings"
)

// файл с описанием правил для проверки возможности отправки сообщения

type RuleFunc func(value interface{}) bool

// равенство
type Equal struct {
	Value interface{}
}

// декодит правило равенства
func (rule Equal) Make(data json.RawMessage) (RuleFunc, error) {

	decoder := go_base_frame.Json.NewDecoder(strings.NewReader(string(data)))
	decoder.UseNumber()

	if err := decoder.Decode(&rule); err != nil {
		return nil, errors.New("can't decode equal rule")
	}

	callback := func(valueToCheck interface{}) bool {
		return rule.Value == valueToCheck
	}

	return callback, nil
}

// белый список
type WhiteList struct {
	List []interface{} `json:"list"`
}

// декодит правило белого списка
func (rule WhiteList) Make(data json.RawMessage) (RuleFunc, error) {

	decoder := go_base_frame.Json.NewDecoder(strings.NewReader(string(data)))
	decoder.UseNumber()

	if err := decoder.Decode(&rule); err != nil {
		return nil, errors.New("can't decode white list rule")
	}

	callback := func(valueToCheck interface{}) bool {

		for _, val := range rule.List {

			if val == valueToCheck {
				return true
			}
		}

		return false
	}

	return callback, nil
}

// черный список
type BlackList struct {
	List []interface{} `json:"list"`
}

// декодит правило белого списка
func (rule BlackList) Make(data json.RawMessage) (RuleFunc, error) {

	decoder := go_base_frame.Json.NewDecoder(strings.NewReader(string(data)))
	decoder.UseNumber()

	if err := decoder.Decode(&rule); err != nil {
		return nil, errors.New("can't decode black list rule")
	}

	callback := func(valueToCheck interface{}) bool {

		for _, val := range rule.List {

			if val == valueToCheck {
				return false
			}
		}

		return true
	}

	return callback, nil
}
