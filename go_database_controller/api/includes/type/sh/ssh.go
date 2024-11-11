package sh

import (
	"fmt"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"golang.org/x/crypto/ssh"
)

// ssh порт по умолчанию
const DefaultSshPort = 22

// sshTunnel структура данных для импровизированного shh-туннеля
type sshTunnel struct {
	config *ssh.ClientConfig
	host   string
	port   int32
}

// MakeSshTunnel создает новый ssh туннель
func MakeSshTunnel(host string, port int32, config *ssh.ClientConfig) *sshTunnel {

	return &sshTunnel{
		config: config,
		host:   host,
		port:   port,
	}
}

// выводит данные в лог
func (sTunnel *sshTunnel) say(message string) {

	log.Info(message)
}

// выводит данные в лог ошибок
func (sTunnel *sshTunnel) yell(message string) {

	log.Errorf("pipe: ", message)
}

// устанавливает подключение и создает сессию для ssh
// функция возвращает closeCallback, который обязательно должен быть вызван вызывающей функцией
func (sTunnel *sshTunnel) dial() (*ssh.Client, *ssh.Session, func(), error) {

	// дозваниваемся до удаленной машины
	client, err := ssh.Dial("tcp", fmt.Sprintf("%s:%d", sTunnel.host, sTunnel.port), sTunnel.config)
	if err != nil {
		return nil, nil, nil, err
	}

	// создаем новую сессию
	session, err := client.NewSession()
	if err != nil {

		_ = client.Close()
		return nil, nil, nil, err
	}

	// callback завершения работы
	closeCallback := func() {

		// если EOF в ошибке, то сессия была уже закрыта кем-то ранее
		if err := session.Close(); err != nil && err.Error() != "EOF" {
			sTunnel.say(fmt.Sprintf("can't close session: %s", err.Error()))
		}

		if err := client.Close(); err != nil {
			sTunnel.say(fmt.Sprintf("can't close client: %s", err.Error()))
		}
	}

	return client, session, closeCallback, nil
}
