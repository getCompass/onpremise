package logger

import (
	"fmt"
	"time"
)

type Log struct {
	logText string
}

// читаем скопившие логи из рутины
func (logItem *Log) ReadLog() string {

	output := logItem.logText
	logItem.logText = ""

	return output
}

// добавляем логи в рутину
func (logItem *Log) AddLog(appendString string) {

	timeNow := time.Now()
	datePrefix := fmt.Sprintf("[%d/%d/%d %d:%d:%d] | ", timeNow.Day(), timeNow.Month(), timeNow.Year(), timeNow.Hour(), timeNow.Minute(), timeNow.Second())
	logItem.logText += datePrefix + appendString + "\n"
}
