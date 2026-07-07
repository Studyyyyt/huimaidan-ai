# 管理员端「今日收款」和「今日订单」接口需求文档

> 创建时间：2026-06-17
> 前端页面：`/pages/admin/profile.vue`

---

## 一、需求背景

管理员端个人中心页面需要展示「今日收款」和「今日订单」统计数据，目前前端代码写死为 0，需要后端提供对应接口。

**展示位置**：个人中心顶部紫色渐变卡片（今日收款金额 + 今日订单数量）

---

## 二、现有相关接口

### 2.1 财务概览接口（已对接）

**接口地址**：`GET /mer/huimaidan/finance/overview`

**返回字段**：
```typescript
{
  totalReceived: number      // 累计收款
  yesterdayReceived: number  // 昨日收款
  monthReceived: number      // 本月收款
  totalRefund: number        // 累计退款
  yesterdayRefund: number    // 昨日退款
  monthRefund: number        // 本月退款
}
```

**问题**：缺少 `todayReceived`（今日收款）字段

---

## 三、新增接口需求

### 方案 A：扩展现有财务概览接口（推荐）

在 `/mer/huimaidan/finance/overview` 接口返回中新增以下字段：

**新增字段**：
```typescript
{
  // ... 现有字段保持不变 ...
  todayReceived: number    // 今日收款金额（新增）
  todayRefund: number      // 今日退款金额（新增，可选）
}
```

**接口地址**：`GET /mer/huimaidan/finance/overview`

**请求参数**：无

**请求示例**：
```
GET /mer/huimaidan/finance/overview HTTP/1.1
Host: api.example.com
Authorization: Bearer <token>
```

**响应示例**：
```json
{
  "code": 1,
  "msg": "success",
  "data": {
    "totalReceived": 12580.50,
    "yesterdayReceived": 680.00,
    "monthReceived": 3200.00,
    "totalRefund": 120.00,
    "yesterdayRefund": 0.00,
    "monthRefund": 50.00,
    "todayReceived": 520.00,
    "todayRefund": 0.00
  }
}
```

---

### 方案 B：新增独立的今日统计接口

如果不想修改现有接口，可以新增一个独立接口。

**接口地址**：`GET /mer/huimaidan/stats/today`

**请求参数**：无

**请求示例**：
```
GET /mer/huimaidan/stats/today HTTP/1.1
Host: api.example.com
Authorization: Bearer <token>
```

**响应示例**：
```json
{
  "code": 1,
  "msg": "success",
  "data": {
    "todayReceived": 520.00,
    "todayOrderCount": 8,
    "todayRefund": 0.00,
    "todayRefundCount": 0
  }
}
```

**响应字段说明**：

| 字段 | 类型 | 说明 |
|------|------|------|
| `todayReceived` | number | 今日收款金额（单位：元） |
| `todayOrderCount` | number | 今日订单数量 |
| `todayRefund` | number | 今日退款金额（可选） |
| `todayRefundCount` | number | 今日退款订单数（可选） |

---

### 方案 C：新增商户订单统计接口

如果后端已有商户维度的订单统计，可以复用或扩展。

**接口地址**：`GET /mer/huimaidan/order/statistics`

**请求参数**：

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `date` | string | 否 | 日期，格式 `Y-m-d`，默认今日 |

**请求示例**：
```
GET /mer/huimaidan/order/statistics?date=2026-06-17 HTTP/1.1
Host: api.example.com
Authorization: Bearer <token>
```

**响应示例**：
```json
{
  "code": 1,
  "msg": "success",
  "data": {
    "todayReceived": 520.00,
    "todayOrderCount": 8,
    "allOrderCount": 156,
    "completedOrderCount": 150,
    "refundOrderCount": 6
  }
}
```

**响应字段说明**：

| 字段 | 类型 | 说明 |
|------|------|------|
| `todayReceived` | number | 今日收款金额（单位：元） |
| `todayOrderCount` | number | 今日订单数量 |
| `allOrderCount` | number | 全部订单数量（可选） |
| `completedOrderCount` | number | 已完成订单数量（可选） |
| `refundOrderCount` | number | 退款订单数量（可选） |

---

## 四、前端对接说明

### 对接后的前端代码修改

**文件**：`/pages/admin/profile.vue`

**修改 loadTodayData 函数**：

```typescript
// 方案 A：扩展现有接口
async function loadTodayData() {
  try {
    const res = await getFinanceOverview()
    todayData.value = {
      todayReceived: res.todayReceived || 0,  // 使用新增字段
      todayOrderCount: res.todayOrderCount || 0, // 需要同时扩展
    }
  } catch (error) {
    console.error('获取今日数据失败:', error)
  }
}

// 方案 B/C：使用独立接口
async function loadTodayData() {
  try {
    const res = await getTodayStats()  // 新增接口
    todayData.value = {
      todayReceived: res.todayReceived || 0,
      todayOrderCount: res.todayOrderCount || 0,
    }
  } catch (error) {
    console.error('获取今日数据失败:', error)
  }
}
```

**修改 orderStats 对象**（可选，用于下方买单记录统计）：

```typescript
async function loadOrderStats() {
  try {
    const res = await getTodayStats()
    orderStats.value = {
      todayOrder: res.todayOrderCount || 0,
      todayReceived: res.todayReceived || 0,
      refundOrder: res.todayRefundCount || 0,
      allOrder: res.allOrderCount || 0,
    }
  } catch (error) {
    console.error('获取订单统计失败:', error)
  }
}
```

---

## 五、数据定义（供后端参考）

"今日"的定义：**当天 00:00:00 ~ 23:59:59**（按服务器时间）

**今日收款** = 当天所有已支付订单的金额总计（不含退款）

**今日订单** = 当天所有已支付订单的数量（状态为已支付/已完成）

---

## 六、推荐方案

**推荐方案 A**（扩展现有接口），理由：
1. 减少接口数量，降低前后端对接成本
2. 现有接口已经返回类似数据，只需补充字段
3. 前端改动最小，只需修改字段名

---

## 七、接口优先级

| 优先级 | 接口 | 说明 |
|--------|------|------|
| P0 | 今日收款 | 核心需求，必须支持 |
| P0 | 今日订单数 | 核心需求，必须支持 |
| P1 | 今日退款 | 可选，提升用户体验 |
| P2 | 今日退款订单数 | 可选，用于买单记录统计 |

---

*文档结束*
