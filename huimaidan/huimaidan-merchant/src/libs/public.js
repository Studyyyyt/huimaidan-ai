// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
export function modalSure(title) {
  return new Promise((resolve, reject) => {
    this.$confirm(`确定${title || '删除该条数据吗'}?`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(() => {
      resolve()
    }).catch(() => {
      this.$message({
        type: 'info',
        message: '已取消'
      })
    })
  })
}
export function modalSureDelete(title) {
  return new Promise((resolve, reject) => {
    this.$confirm(`${title || '该记录删除后不可恢复，您确认删除吗'}?`, '提示', {
      confirmButtonText: '删除',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(() => {
      resolve()
    }).catch(action => {
      this.$message({
        type: 'info',
        message: '已取消'
      })
    })
  })
}
export function returnSure(title) {
    return new Promise((resolve, reject) => {
      this.$confirm(`确定${title || '删除该条数据吗'}?`, '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        resolve()
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消'
        })
      })
    })
  }
  /**
 * @description 表格列表中删除最后一页中的唯一一个数据的操作
 */
export function handleDeleteTable(length, tableFrom) {
  if (length === 1 && tableFrom.page > 1) return (tableFrom.page = tableFrom.page - 1);
}

function parseTime(time) {
	const [hours, minutes] = time.split(':').map(Number);
	return new Date(0, 0, 0, hours, minutes).getTime();
}

function parseTimeM(timeStr) {
  const [hours, minutes] = timeStr.split(':').map(Number);
  return hours * 60 + minutes; // 返回分钟数
}

// 时间分段计算方法；
export function splitTimeRange(timeRange, intervalMinutes){

	const [startTime, endTime] = timeRange;
	const start = parseTime(startTime);
	const end = parseTime(endTime);
	const interval = intervalMinutes * 60 * 1000; // 转换为毫秒

	const result = [];
	let currentStart = start;

	while (currentStart + interval <= end) {
	  const currentEnd = currentStart + interval;
	  let data = {
		  start:formatTime(currentStart),
		  end:formatTime(currentEnd),
		  stock:0
	  }
	  result.push(data);
	  currentStart = currentEnd;
	}

	// 处理最后一个时间段
	if (currentStart < end) {
		 const remainingTime = end - currentStart;
		 if (remainingTime >= interval) {
		   let data = {
			   start:formatTime(currentStart),
			   end:formatTime(end),
			   stock:0
		   }
		   result.push(data);
		 }
	}
	return result;
}
function formatTime(time) {
	const date = new Date(time);
	const hours = date.getHours().toString().padStart(2, '0');
	const minutes = date.getMinutes().toString().padStart(2, '0');
	return `${hours}:${minutes}`;
}

// 判断是否有交集
export function hasIntersection(timeRanges) {

  for (let i = 0; i < timeRanges.length; i++) {
      for (let j = i + 1; j < timeRanges.length; j++) {
        console.log(timeRanges[i].map(parseTimeM),timeRanges[j].map(parseTimeM))
          const [start1, end1] = timeRanges[i].map(parseTimeM);
          const [start2, end2] = timeRanges[j].map(parseTimeM);
          if (start1 <= end2 && end1 >= start2) {
              return true;
          }
      }
  }
  return false;
}
// 判断是否递增
export function isTimeRangesIncreasing(timeRanges) {
  for (let i = 1; i < timeRanges.length; i++) {
      const [start1, end1] = timeRanges[i - 1].map(parseTimeM);
      const [start2, end2] = timeRanges[i].map(parseTimeM);
      if (end1 > start2) {
          return false;
      }
  }
  return true;
}