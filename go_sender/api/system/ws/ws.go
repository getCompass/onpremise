package ws

import (
	"crypto/tls"
	"crypto/x509"
	"encoding/pem"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	GlobalIsolation "go_sender/api/includes/type/global_isolation"
	Isolation "go_sender/api/includes/type/isolation"
	"go_sender/api/includes/type/ws"
	wsController "go_sender/api/includes/ws_controller"
	"io/ioutil"
	"net/http"
)

// стартуем вебсокет
func Start(globalIsolation *GlobalIsolation.GlobalIsolation, companyEnvList *Isolation.CompanyEnvList) {

	callback := func(connection *ws.ConnectionStruct, requestBytes []byte) error {
		return wsController.DoStart(companyEnvList, connection, requestBytes)
	}

	// инициализируем мультиплеексор
	mux := http.NewServeMux()
	mux.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {

		ws.Listen(w, r, callback)
	})

	// инициализируем объект сервера
	serverItem := &http.Server{
		Addr:    fmt.Sprintf(":%v", globalIsolation.GetConfig().WebsocketPort),
		Handler: mux,
	}

	// если сертифицированное подключение - обслуживаем серцифицированный сервер
	if globalIsolation.GetConfig().WebsocketIsTls {

		listenAndServeTLS(globalIsolation, serverItem)
		return
	}

	// иначе - обычный
	listenAndServe(serverItem)
}

// начинаем слушать и обслуживать серцифицированный сервер
func listenAndServeTLS(globalIsolation *GlobalIsolation.GlobalIsolation, serverItem *http.Server) {

	// настраиваем конфиг
	serverItem.TLSConfig = &tls.Config{
		MinVersion:               tls.VersionTLS12,
		CurvePreferences:         []tls.CurveID{tls.CurveP521, tls.CurveP384, tls.CurveP256},
		PreferServerCipherSuites: true,
		CipherSuites: []uint16{
			tls.TLS_ECDHE_RSA_WITH_AES_256_GCM_SHA384, // openssl s_client -connect 127.0.0.1:8000 -cipher ECDHE-RSA-AES256-GCM-SHA384
			tls.TLS_ECDHE_RSA_WITH_AES_256_CBC_SHA,    // openssl s_client -connect 127.0.0.1:8000 -cipher AES256-SHA
			tls.TLS_RSA_WITH_AES_256_GCM_SHA384,       // openssl s_client -connect 127.0.0.1:8000 -cipher AES256-GCM-SHA384
			tls.TLS_RSA_WITH_AES_256_CBC_SHA,          // openssl s_client -connect 127.0.0.1:8000 -cipher ECDHE-RSA-AES256-SHA
		},
		Certificates: getCertificates(globalIsolation),
	}

	// начинаем слушать и обслуживать сервер
	serverItem.TLSNextProto = make(map[string]func(*http.Server, *tls.Conn, http.Handler))
	err := serverItem.ListenAndServeTLS("", "")
	if err != nil {
		log.Errorf("ListenAndServe: %s", err.Error())
	}
}

// получаем сертификаты
func getCertificates(globalIsolation *GlobalIsolation.GlobalIsolation) []tls.Certificate {

	// получаем содержимое файла с сертификатом
	certBytes, err := ioutil.ReadFile(globalIsolation.GetConfig().WebsocketCertFilePath)
	if err != nil {
		log.Errorf(err.Error())
	}

	// получаем содержимое файла с закрытым ключом
	keyBytes, err := ioutil.ReadFile(globalIsolation.GetConfig().WebsocketKeyFilePath)
	if err != nil {
		log.Errorf(err.Error())
	}

	// связываем пару сертификата и закрытого ключа
	certificateItem, err := associateCertificatePair(certBytes, keyBytes, globalIsolation.GetConfig().WebsocketPassPhrase)
	if err != nil {
		log.Errorf(err.Error())
	}

	return []tls.Certificate{certificateItem}
}

// связываем пару сертификата и закрытого ключа
func associateCertificatePair(certBytes, keyBytes []byte, passphrase string) (tls.Certificate, error) {

	// переводим закрытый ключ в pem-блок
	keyPemBlock, _ := pem.Decode(keyBytes)
	if keyPemBlock == nil {
		return tls.Certificate{}, fmt.Errorf("no valid private key found")
	}

	// расшифровываем ключ
	var err error
	if x509.IsEncryptedPEMBlock(keyPemBlock) {

		keyBytes, err = x509.DecryptPEMBlock(keyPemBlock, []byte(passphrase))
		if err != nil {
			return tls.Certificate{}, err
		}
		keyBytes = pem.EncodeToMemory(&pem.Block{Type: keyPemBlock.Type, Bytes: keyBytes})
	}

	// связываем пару и получаем tls-сертификат
	certificate, err := tls.X509KeyPair(certBytes, keyBytes)
	if err != nil {
		return tls.Certificate{}, err
	}
	return certificate, nil
}

// начинаем слушать и обслуживать серцифицированный сервер
func listenAndServe(serverItem *http.Server) {

	// начинаем слушать и обслуживать сервер
	err := serverItem.ListenAndServe()
	if err != nil {
		log.Errorf("ListenAndServe: %s", err.Error())
	}
}
