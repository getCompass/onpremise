package sh

// пакет обертки для вызова консольных команд
// в этом файле реализован последовательный возов консольных команд

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"os"
	"os/exec"
	"time"
)

// обертка для команды с таймаутом
type command struct {
	cmd       *exec.Cmd
	timeout   time.Duration
	shortName string
}

// MakeCommand создает новый pipe для выполнения
func MakeCommand(cmd *exec.Cmd, timeout time.Duration) *command {

	shortName := cmd.String() // [0:20] + "..."
	return &command{cmd, timeout, shortName}
}

// структура последовательного исполнения команд
type pipe struct {
	commandList []*command
	output      *os.File
}

// MakePipe создает новый pipe для выполнения
func MakePipe(commandList ...*command) *pipe {

	return &pipe{
		commandList: commandList,
		output:      os.Stdout,
	}
}

// выводит данные в лог
func (p *pipe) say(message string) {

	log.Infof("pipe: ", message)
}

// выводит данные в лог ошибок
func (p *pipe) yell(message string) {

	log.Errorf("pipe: ", message)
}

// SetOutput устанавливает файл для вывода данных результата исполнения
func (p *pipe) SetOutput(output *os.File) {

	p.output = output
}

// PushCommand добавляет команды в конце цепочки
func (p *pipe) PushCommand(commandList ...*command) {

	p.commandList = append(p.commandList, commandList...)
}

// ShiftCommand добавляет команды в начало цепочки
func (p *pipe) ShiftCommand(commandList ...*command) {

	p.commandList = append(commandList, p.commandList...)
}

// Exec выполняет всю цепочку команд по очереди
func (p *pipe) Exec() error {

	// склеиваем команды в один массив
	var err error

	// для последней команды указываем stdout, чтобы забрать результат
	p.commandList[len(p.commandList)-1].cmd.Stdout = p.output

	// начинаем формировать pipe, первая команда не попадает в него
	// поскольку является точкой входа и началом pipe вызова
	for i := 1; i < len(p.commandList); i++ {

		if p.commandList[i].cmd.Stdin, err = p.commandList[i-1].cmd.StdoutPipe(); err != nil {
			return err
		}
	}

	// ждем исполнения всех команд
	for i := len(p.commandList) - 1; i >= 0; i-- {

		p.say(fmt.Sprintf("start command %s", p.commandList[i].shortName))

		if err = p.commandList[i].cmd.Start(); err != nil {
			return fmt.Errorf("command %s not started: %s", p.commandList[i].shortName, err.Error())
		}
	}

	for i := 0; i < len(p.commandList); i++ {

		// устанавливаем таймаут для консольной команды
		timeoutTimer := time.AfterFunc(p.commandList[i].timeout, func() {

			_ = p.commandList[i].cmd.Process.Kill()
		})

		err = p.commandList[i].cmd.Wait()
		timeoutTimer.Stop()

		if err != nil {
			return fmt.Errorf("command %s execution failed: %s", p.commandList[i].shortName, err.Error())
		}
	}

	return nil
}
