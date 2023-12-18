package pivotuser

import (
	"fmt"
	"math"
)

// получаем ключ базы данных
func getShardKey(userId int64) string {

	return fmt.Sprintf("pivot_user_%d0m", int64(math.Ceil(float64(userId)/10000000)))
}

// получаем групированный по userIdList список шардов
func getGroupedShardKey(userIdList []int64) map[string][]int64 {

	groupedUserIdList := map[string][]int64{}

	for _, value := range userIdList {

		groupedUserIdList[getShardKey(value)] = append(groupedUserIdList[getShardKey(value)], value)
	}

	return groupedUserIdList
}
