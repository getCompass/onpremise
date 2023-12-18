package pivot_user

import (
	"fmt"
	"math"
)

const dbKey = "pivot_user"

// получаем shardKey на основе userID
func getShardKey(userID int64) string {

	return fmt.Sprintf("%s_%d0m", dbKey, int64(math.Ceil(float64(userID)/10000000)))
}

// получаем групированный по userIdList список шардов
func getGroupedShardKey(userIdList []int64) map[string][]int64 {

	groupedUserIdList := map[string][]int64{}

	for _, value := range userIdList {

		groupedUserIdList[getShardKey(value)] = append(groupedUserIdList[getShardKey(value)], value)
	}

	return groupedUserIdList
}
