package keeper

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/port_registry"
	"go_database_controller/api/includes/type/sh"
	"net"
	"os/exec"
	"time"
)

// Start стартуем mysql
func Start(portValue int32) error {

	log.Infof("пытаюсь поднять контейнер на порте %d...", portValue)

	// читаем из базы, чтобы данные были актуальны,
	// а то там все может посыпаться из-за ошибки
	port, err := port_registry.GetByPort(portValue)
	if err != nil {

		log.Infof("контейнер на порте %d не запустился: %s", portValue, err.Error())
		return err
	} else if !port.NeedDaemon() {

		log.Infof("контейнер на порте %d не запустился — не годится для запуска, статус %d", portValue, port.Status)
		return fmt.Errorf("контейнер на порте %d не запустился — не годится для запуска, статус %d", portValue, port.Status)
	}

	time.Sleep(500 * time.Millisecond)

	if err = GenerateComposeConfig(); err != nil {
		return err
	}

	if err = Update(); err != nil {
		return err
	}

	log.Infof("контейнер на порте %d запущен!", portValue)
	return nil
}

// Stop останавливаем mysql
func Stop(portValue int32) error {

	log.Infof("пытаюсь остановить контейнер на порте %d...", portValue)

	// удаляем контейнер
	if err := GenerateComposeConfig(); err != nil {
		return err
	}

	if err := Update(); err != nil {
		return err
	}

	log.Infof("команда остановки контейнера на порте %d успешно выполнена, ждем ответа...", portValue)
	if !waitMysqlNotAlive(portValue, 5) {

		log.Infof("прошло %d секунд, но порт %d все еще отвечает", portValue)
		return fmt.Errorf("прошло %d секунд, но порт %d все еще отвечает", 5, portValue)
	}

	log.Infof("контейнер на порте %d остановлен!", portValue)
	return nil
}

func Update() error {

	cmdStr := "/usr/bin/docker stack deploy " +
		"--with-registry-auth " +
		"--resolve-image=changed " +
		"--prune " +
		"--compose-file " + conf.GetConfig().ComposeFilePath + " " +
		fmt.Sprintf("%s-%s-company", conf.GetConfig().StackNamePrefix, conf.GetConfig().DominoId)

	// запускаем команду старта контейнера
	startCmd := exec.Command("sh", "-c", cmdStr)
	startPipe := sh.MakePipe(sh.MakeCommand(startCmd, 10*time.Second))
	if err := startPipe.Exec(); err != nil {

		log.Infof("не смог подняться стак с mysql")
		return err
	}

	return nil
}

// Report получаем статус mysql
func Report(port int32) bool {

	return isMysqlAlive(port)
}

// ждем пока mysql оживет
func waitMysqlAlive(port int32, timeout int64) bool {

	endAt := functions.GetCurrentTimeStamp() + timeout

	for {

		if isMysqlAlive(port) {
			return true
		}

		if endAt < functions.GetCurrentTimeStamp() {
			return false
		}

		time.Sleep(100 * time.Millisecond)
	}
}

// ждем пока mysql умрет
func waitMysqlNotAlive(port int32, timeout int64) bool {

	endAt := functions.GetCurrentTimeStamp() + timeout

	for true {

		if !isMysqlAlive(port) {
			return true
		}

		if endAt < functions.GetCurrentTimeStamp() {
			return false
		}

		time.Sleep(100 * time.Millisecond)
	}

	return false
}

// проверяем жив ли mysql
func isMysqlAlive(port int32) bool {

	conn, err := net.DialTimeout("tcp", fmt.Sprintf("%s:%d", conf.GetConfig().MysqlCompanyHost, port), 3*time.Second)
	if err != nil {
		return false
	}

	defer conn.Close()
	if conn != nil {

		return true
	}
	return false
}
