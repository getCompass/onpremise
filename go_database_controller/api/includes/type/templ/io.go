package templ

import (
	"bytes"
	"os"
	"text/template"
)

// MissingKeyPolicy тип — политика работы с несуществующими ключами
type MissingKeyPolicy string

// MissingKeyPolicyError политика работы с пропущенными ключами
const MissingKeyPolicyError MissingKeyPolicy = "missingkey=error"

// MakeFromBytes возвращает шаблон для дальнейшей подстановки из файла
func MakeFromBytes(bytes []byte, missingKeyPolicy MissingKeyPolicy) (*template.Template, error) {

	t := template.New("template").Option(string(missingKeyPolicy))

	// парсим шаблон
	if _, err := t.Parse(string(bytes)); err != nil {
		return nil, err
	}

	return t, nil
}

// WriteToFile на основе переданного шаблона и данных подстановки создает файл с готовыми данными
func WriteToFile(file *os.File, tpl *template.Template, data interface{}) error {

	// записываем новый конфиг
	buf := new(bytes.Buffer)

	// заполняем конфиг
	if err := tpl.Execute(buf, data); err != nil {
		return err
	}

	// очищаем файл
	if err := file.Truncate(0); err != nil {
		return err
	}

	// записываем конфиг
	if _, err := file.Write(buf.Bytes()); err != nil {
		return err
	}

	return nil
}
