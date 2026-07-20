package log

import (
	"fmt"
	"log"
	"os"
	"runtime"
	"strings"
	"syscall"

	"github.com/getCompassUtils/go_base_frame/api/system/functions"
)

// -------------------------------------------------------
// пакет логирования
// -------------------------------------------------------

const (
	startupMessage = "start logging" // начальное сообщение в лог

	// уровни логирования
	loggingLevelLow    = 1 // запись только ошибок
	loggingLevelMedium = 2 // запись успешных сообщений, ошибок и предупреждений
	loggingLevelHigh   = 3 // запись всех логов

	// уровни логов
	levelDebug   = 0 // дебаг
	levelInfo    = 1 // информация
	levelSuccess = 2 // успех
	levelWarning = 3 // предупреждение
	levelError   = 4 // ошибка
	levelSystem  = 5 // системные логи
)

var (
	mainLogFileName = "main.log" // основной файл лога
	loggingLevel    = 3
)

// инициализируем пакет
func UpdateLogDir(logsDir string) {

	// создаем файл лога, если он не был создан
	_ = os.Mkdir(logsDir, os.ModePerm)
	logsFile := logsDir + "/" + mainLogFileName
	err := functions.CreateFileIfNotExist(logsFile)
	if err != nil {
		panic(fmt.Errorf("unable to create log file, error: %v", err))
	}

	// открываем файл для записи
	file, err := os.OpenFile(logsFile, os.O_APPEND|os.O_CREATE|os.O_RDWR, 0666)
	if err != nil {
		panic(fmt.Errorf("unable to open log file for writing, error: %v", err))
	}

	// задаем файл для логов
	log.SetOutput(file)
	setStderrLog(file)

	Success(startupMessage + ": " + logsFile)
}

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// выполняем дебаг
func Debug(interfaceList ...interface{}) {

	// форматируем сообщение
	message := fmt.Sprint(interfaceList...)

	// выполняем запись в лог
	write(message, levelDebug)
}

// запись информации в лог
func Info(message string) {

	// выполняем запись в лог
	write(message, levelInfo)
}

// запись форматированной информации в лог
func Infof(formatMessage string, formatArgs ...interface{}) {

	// форматируем сообщение
	message := fmt.Sprintf(formatMessage, formatArgs...)

	// выполняем запись в лог
	write(message, levelInfo)
}

// запись успешного сообщения в лог
func Success(message string) {

	// выполняем запись в лог
	write(message, levelSuccess)
}

// запись форматированного успешного сообщения в лог
func Successf(formatMessage string, formatArgs ...interface{}) {

	// форматируем сообщение
	message := fmt.Sprintf(formatMessage, formatArgs...)

	// выполняем запись в лог
	write(message, levelSuccess)
}

// запись предупреждения в лог
func Warning(message string) {

	// выполняем запись в лог
	write(message, levelWarning)
}

// запись форматированного предупреждения в лог
func Warningf(formatMessage string, formatArgs ...interface{}) {

	// форматируем сообщение
	message := fmt.Sprintf(formatMessage, formatArgs...)

	// выполняем запись в лог
	write(message, levelWarning)
}

// запись ошибки в лог
func Error(message string) {

	// выполняем запись в лог
	write(message, levelError)
}

// запись форматированной ошибки в лог
func Errorf(formatMessage string, formatArgs ...interface{}) {

	// форматируем сообщение
	message := fmt.Sprintf(formatMessage, formatArgs...)

	// выполняем запись в лог
	write(message, levelError)
}

// запись системного лога
func Systemf(formatMessage string, formatArgs ...interface{}) {

	// форматируем сообщение
	message := fmt.Sprintf(formatMessage, formatArgs...)

	// выполняем запись в лог
	write(message, levelSystem)
}

// определяем нужно ли записывать лог
func SetLoggingLevel(level int) {

	loggingLevel = level
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// задаем файл, в который будет происходить запись критических ошибок
func setStderrLog(file *os.File) {

	// задаем файл
	err := syscall.Dup2(int(file.Fd()), int(os.Stderr.Fd()))
	if err != nil {
		panic(fmt.Errorf("unable set stderr log file, error: %v", err))
	}
}

// выполняем запись в лог
func write(message string, level int) {

	// определяем нужно ли записывать лог
	if !isNeedWrite(level) {
		return
	}

	// получаем префикс
	prefix := getPrefix(level)

	// выводим
	message = prefix + message + "\n"
	log.Print(message)
}

// определяем нужно ли записывать лог
func isNeedWrite(level int) bool {

	// в зависимости от уровня логирования определяем нужно ли записывать
	switch level {

	case levelDebug:
		return loggingLevel >= loggingLevelHigh

	case levelInfo:
		return loggingLevel >= loggingLevelHigh

	case levelSuccess:
		return loggingLevel >= loggingLevelMedium

	case levelWarning:
		return loggingLevel >= loggingLevelMedium

	case levelError:
		return loggingLevel >= loggingLevelLow

	case levelSystem:
		return true
	}

	return false
}

// получаем префикс
func getPrefix(level int) string {

	// получаем префикс уровня логирования
	levelPrefix := getLevelPrefix(level)

	// получаем префикс вызвавшего метода
	functionPrefix := getFunctionPrefix()

	return levelPrefix + functionPrefix + " "
}

// получаем префикс уровня логирования
func getLevelPrefix(level int) string {

	switch level {

	case levelDebug:
		return "\033[35;22m[DEBUG]\033[39;22m"

	case levelInfo:
		return "[INFO]"

	case levelSuccess:
		return "\033[32;22m[SUCCESS]\033[39;22m"

	case levelWarning:
		return "\033[33;22m[WARNING]\033[39;22m"

	case levelError:
		return "\033[31;22m[ERROR]\033[39;22m"

	case levelSystem:
		return "[SYSTEM]"
	}

	return ""
}

// получаем префикс вызвавшего метода
func getFunctionPrefix() string {

	// получаем информацию о вызвавшей функции
	pc, fileName, functionLine, isSuccess := runtime.Caller(4)
	if !isSuccess {
		return ""
	}

	// режем название файла
	tempList := strings.Split(fileName, "/")
	fileName = tempList[len(tempList)-1]

	// получаем название функции из которой вызвали лог
	functionName := runtime.FuncForPC(pc).Name()
	tempList = strings.Split(functionName, "/")
	functionName = tempList[len(tempList)-1]

	// генерируем префикс вызвавшего метода
	return fmt.Sprintf("[%s:%d][%s]", fileName, functionLine, functionName)
}
