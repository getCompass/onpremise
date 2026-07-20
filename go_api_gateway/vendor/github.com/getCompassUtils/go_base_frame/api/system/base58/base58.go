package base58

import (
	"errors"
	"math/big"
)

// Алфавит Base58 (исключены 0, O, I, l)
const alphabet = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz"

var (
	// Алфавит в виде байтового слайса для быстрого доступа
	alphabetBytes = []byte(alphabet)
	// Обратное отображение символов для быстрого декодирования
	reverseAlphabet [256]int8
)

// Инициализация обратного алфавита
func init() {

	// Заполняем -1 (недопустимый символ)
	for i := range reverseAlphabet {
		reverseAlphabet[i] = -1
	}
	// Заполняем допустимые символы
	for i, char := range alphabetBytes {
		reverseAlphabet[char] = int8(i)
	}
}

// Base58Encode кодирует байтовый слайс в строку Base58
func Base58Encode(input []byte) []byte {

	// Конвертируем входные данные в big.Int
	x := new(big.Int).SetBytes(input)

	// Основание системы счисления (58)
	base := big.NewInt(58)
	zero := big.NewInt(0)
	mod := new(big.Int)

	// Кодируем в Base58
	var result []byte
	for x.Cmp(zero) > 0 {
		x.DivMod(x, base, mod)
		result = append(result, alphabetBytes[mod.Int64()])
	}

	// Добавляем ведущие '1' для каждого ведущего нулевого байта
	for _, b := range input {
		if b != 0 {
			break
		}
		result = append(result, alphabetBytes[0])
	}

	// Реверс результата
	reverseBytes(result)

	return result
}

// Base58Decode декодирует строку Base58 в байтовый слайс
func Base58Decode(input []byte) ([]byte, error) {
	result := big.NewInt(0)
	base := big.NewInt(58)

	// Обрабатываем каждый символ
	for _, char := range input {
		// Получаем числовое значение символа
		index := reverseAlphabet[char]
		if index == -1 {
			return nil, errors.New("invalid base58 character: " + string(char))
		}

		// Умножаем текущий результат на основание и добавляем новый символ
		result.Mul(result, base)
		result.Add(result, big.NewInt(int64(index)))
	}

	// Конвертируем в байты
	decoded := result.Bytes()

	// Добавляем ведущие нули (обработка ведущих символов '1' в Base58)
	leadingZeros := 0
	for _, char := range []byte(input) {
		if char == alphabetBytes[0] {
			leadingZeros++
		} else {
			break
		}
	}

	// Создаем финальный результат с ведущими нулями
	finalResult := make([]byte, leadingZeros+len(decoded))
	copy(finalResult[leadingZeros:], decoded)

	return finalResult, nil
}

// reverseBytes реверсирует байтовый слайс на месте
func reverseBytes(data []byte) {
	for i, j := 0, len(data)-1; i < j; i, j = i+1, j-1 {
		data[i], data[j] = data[j], data[i]
	}
}
