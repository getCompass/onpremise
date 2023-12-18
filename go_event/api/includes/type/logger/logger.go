package logger

import (
	"fmt"
	clog "github.com/getCompassUtils/go_base_frame/api/system/log"
	"log"
)

// функция логгера
type LogFn func(message string, v ...interface{})

// интерфейс логгера
type CompassLogger interface {
	Success(string, ...interface{})
	Error(string, ...interface{})
	Warning(string, ...interface{})
	Info(string, ...interface{})
}

type DefaultCompassLogger struct {
	CompassLogger
	logger  log.Logger
	success LogFn
	error   LogFn
	warning LogFn
	info    LogFn
}

// создает новый логгер
func MakeLogger(fileName, successPrefix, errorPrefix, warningPrefix, infoPrefix string) *DefaultCompassLogger {

	// var file, err = os.OpenFile("logs/"+fileName+".log", os.O_CREATE|os.O_WRONLY|os.O_APPEND, 0666)

	// if err != nil {
	// 	log.Fatalf("Failed to open log file %s: %s", fileName, err.Error())
	// }

	// logger := log.New(file, "", log.Ldate|log.Ltime)
	cLogger := DefaultCompassLogger{}

	cLogger.success = clog.Successf // prepareLoggerFunction(logger, successPrefix)
	cLogger.error = clog.Errorf     // prepareLoggerFunction(logger, errorPrefix)
	cLogger.warning = clog.Warningf // prepareLoggerFunction(logger, warningPrefix)
	cLogger.info = clog.Infof       // prepareLoggerFunction(logger, infoPrefix)

	return &cLogger
}

// выводит сообщение об успехе
func (l *DefaultCompassLogger) Success(str string, val ...interface{}) {

	l.success(str, val...)
}

// выводит сообщение об ошибке
func (l *DefaultCompassLogger) Error(str string, val ...interface{}) {

	l.error(str, val...)
}

// выводит warning
func (l *DefaultCompassLogger) Warning(str string, val ...interface{}) {

	l.warning(str, val...)
}

// выводит информационное сообщение
func (l *DefaultCompassLogger) Info(str string, val ...interface{}) {

	l.info(str, val...)
}

// подготавливает функцию логгер
func prepareLoggerFunction(logger *log.Logger, prefix string) LogFn {

	return func(message string, v ...interface{}) {

		if len(v) == 0 {

			logger.Printf("%s: %s", prefix, message)
			return
		}

		logger.Printf("%s: %s", prefix, fmt.Sprintf(message, v...))
	}
}
