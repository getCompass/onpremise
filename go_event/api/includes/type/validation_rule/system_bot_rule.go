package validationRule

import (
	"encoding/json"
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_event/api/conf"
)

// структура, которая описывает json описание правила
type SendingRuleDescription struct {
	RuleFor string          `json:"rule_for"`
	Type    string          `json:"type"`
	Data    json.RawMessage `json:"data"`
}

// структура правила
type SendingRule struct {
	RuleFor  string
	Callback RuleFunc
}

// набор текущих действующих правил
var RuleSet = map[string][]SendingRule{}

// обновляет текущий действующий набор правил на основе конфига
func Init() error {

	// временный набор правил, чтобы не сломать текущий
	ruleSet := map[string][]SendingRule{}

	// перебираем все правила и наполняем массив
	for messageType, ruleDescriptionList := range conf.GetMessageRuleConf().EventMessageRuleList {

		for _, ruleDescription := range ruleDescriptionList {

			sendingRule := SendingRuleDescription{}
			err := go_base_frame.Json.Unmarshal(ruleDescription, &sendingRule)

			if err != nil {
				return fmt.Errorf("can't decode rule set: %s", err)
			}

			callback, err := makeRule(sendingRule.Type, sendingRule.Data)

			if err != nil {
				return fmt.Errorf("can't decode rule: %s", err)
			}

			// добавляем правило валидации
			ruleSet[messageType] = append(ruleSet[messageType], SendingRule{
				RuleFor:  sendingRule.RuleFor,
				Callback: callback,
			})
		}
	}

	// если все правила добавлены верно, перезаписываем текущий рабочий вариант
	RuleSet = ruleSet
	return nil
}

// создает правило для проверки возможности отправить сообщение
func makeRule(ruleType string, data json.RawMessage) (RuleFunc, error) {

	switch ruleType {
	case "equal":

		return Equal{}.Make(data)
	case "white_list":

		return WhiteList{}.Make(data)
	case "black_list":

		return BlackList{}.Make(data)
	default:

		return nil, errors.New("passed invalid rule")
	}
}

// выполняет проверку на возможность отправки сообщения
func CheckSendingRule(event string) bool {

	_, exists := RuleSet[event]

	if !exists {

		// правил для события не задано
		// в таком случае, считаем, что проверка не пройдена
		log.Infof("there is no rule for event %s", event)
		return false
	}

	return true
}
