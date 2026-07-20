package apitoken

import (
	"encoding/json"
	"errors"
	"fmt"
)

type ScopeList map[string]string
type ScopeListInt map[int64]int64

// зоны ответственности
// должны дублироваться в php_base_frame
const (
	SCOPE_UNDEFINED = -1
	SCOPE_GLOBAL    = 0

	SCOPE_CONFERENCE       = 1
	SCOPE_SPACE            = 2
	SCOPE_FILE             = 3
	SCOPE_SPACE_MANAGEMENT = 4
	SCOPE_SPACE_MEMBER     = 5
	SCOPE_SPACE_RATING     = 6
	SCOPE_SPACE_PROFILE    = 7
	SCOPE_SPACE_JOINLINK   = 8
	SCOPE_USERBOT          = 9
	SCOPE_SMARTAPP         = 10
	SCOPE_CONVERSATION     = 11
	SCOPE_THREAD           = 12
	SCOPE_SEARCH           = 13
	SCOPE_PROFILE          = 14
	SCOPE_NOTIFICATIONS    = 15
)

// права к зоне ответственности
// должны дублироваться в php_base_frame
const (
	PERMISSION_NONE  = 0
	PERMISSION_READ  = 1 << 0
	PERMISSION_WRITE = (1 << 0) | (1 << 1)
)

// строковые значения для зон ответственности
var ScopeNames = map[int64]string{
	SCOPE_CONFERENCE:       "conference",
	SCOPE_SPACE:            "space",
	SCOPE_FILE:             "file",
	SCOPE_SPACE_MANAGEMENT: "space_management",
	SCOPE_SPACE_MEMBER:     "space_member",
	SCOPE_SPACE_RATING:     "space_rating",
	SCOPE_SPACE_PROFILE:    "space_profile",
	SCOPE_SPACE_JOINLINK:   "space_joinlink",
	SCOPE_USERBOT:          "userbot",
	SCOPE_SMARTAPP:         "smartapp",
	SCOPE_CONVERSATION:     "conversation",
	SCOPE_THREAD:           "thread",
	SCOPE_SEARCH:           "search",
	SCOPE_PROFILE:          "profile",
	SCOPE_NOTIFICATIONS:    "notifications",
}

// строковые значения для прав
var permissionNames = map[int64]string{
	PERMISSION_NONE:  "none",
	PERMISSION_READ:  "read",
	PERMISSION_WRITE: "write",
}

// маршалинг json для строковых зон ответственности
func (sl ScopeList) MarshalJSON() ([]byte, error) {

	if sl == nil {
		return []byte("{}"), nil
	}

	return json.Marshal(map[string]string(sl))
}

// анмаршалинг json для строковых зон ответственности
func (sl *ScopeList) UnmarshalJSON(data []byte) error {

	if sl == nil {
		return errors.New("UnmarshalJSON on nil pointer")
	}
	if *sl == nil {
		*sl = make(ScopeList)
	}

	var temp map[string]string

	if err := json.Unmarshal(data, &temp); err != nil {
		return err
	}

	for k, v := range temp {
		var key string

		if _, err := fmt.Sscanf(k, "%s", &key); err != nil {
			continue
		}

		(*sl)[key] = v
	}

	return nil
}

// фильтрация none значения
func (sli ScopeListInt) FilterNone() ScopeListInt {

	filtered := make(ScopeListInt)

	for scope, permission := range sli {

		if permission == PERMISSION_NONE {
			continue
		}
		filtered[scope] = permission
	}

	return filtered
}

// перевод строковых зон ответственности в цифровое значение
func (sl ScopeList) ToInt() ScopeListInt {

	sli := ScopeListInt{}

	fsn := FlipMap(ScopeNames)
	fpn := FlipMap(permissionNames)

	for scope, permission := range sl {

		if _, exists := fsn[scope]; !exists {
			continue
		}

		if _, exists := fpn[permission]; !exists {
			continue
		}

		sli[fsn[scope]] = fpn[permission]
	}

	return sli
}

// маршалинг json для цифровых зон ответственности
func (sli ScopeListInt) MarshalJSON() ([]byte, error) {

	if sli == nil {
		return []byte("{}"), nil
	}

	return json.Marshal(map[int64]int64(sli))
}

// анмаршалинг json для цифровых зон ответственности
func (sli *ScopeListInt) UnmarshalJSON(data []byte) error {

	if sli == nil {
		return errors.New("UnmarshalJSON on nil pointer")
	}

	if *sli == nil {
		*sli = make(ScopeListInt)
	}

	var temp map[string]int64

	if err := json.Unmarshal(data, &temp); err != nil {
		return err
	}

	for k, v := range temp {
		var key int64

		if _, err := fmt.Sscanf(k, "%d", &key); err != nil {
			continue
		}

		(*sli)[key] = v
	}

	return nil
}

// перевод цифровых зон ответственности в строковые
func (sli ScopeListInt) ToString() ScopeList {

	sl := ScopeList{}

	for scope, permission := range sli {

		if _, exists := ScopeNames[scope]; !exists {
			continue
		}

		if _, exists := permissionNames[permission]; !exists {
			continue
		}

		sl[ScopeNames[scope]] = permissionNames[permission]
	}

	return sl
}

// поменять ключи и значения местами в мапе
func FlipMap[M ~map[K]V, K comparable, V comparable](m M) map[V]K {
	r := make(map[V]K, len(m))
	for k, v := range m {
		r[v] = k
	}
	return r
}
