package apitoken

import (
	"fmt"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/sanitizer"
)

// MaxNameLength максимальная длина имени токена
const MaxNameLength = 80

// MaxUnsignedInt32 максимальный unsigned int32
const MaxUnsignedInt32 = 4_294_967_295

// SanitazeTokenName очистить от запрещенных символов название токена
func SanitazeTokenName(name string) (string, error) {

	apiTokenName, err := sanitizer.SanitizeString(
		name,
		[]string{
			sanitizer.EMOJI_REGEX,
			sanitizer.COMMON_FORBIDDEN_CHARACTER_REGEX,
			sanitizer.SPECIAL_CHARACTER_REGEX,
			sanitizer.ANGLE_BRACKET_REGEX,
			sanitizer.FANCY_TEXT_REGEX,
			sanitizer.DOUBLE_SPACE_REGEX,
			sanitizer.NEWLINE_REGEX,
		},
		[]string{
			"",
			"",
			"",
			"",
			"",
			" ",
			"",
		},
	)

	if err != nil {
		return "", err
	}

	if apiTokenName == "" {
		return "", fmt.Errorf("invalid token name")
	}

	return apiTokenName[:min(len(apiTokenName), MaxNameLength)], nil
}

// ValidateTokenExpiresAt проверить корректность expiresAt
func ValidateTokenExpiresAt(expiresAt int64) error {

	if expiresAt < time.Now().Unix() || expiresAt > MaxUnsignedInt32 {
		return fmt.Errorf("invalid expires_at")
	}

	return nil
}
