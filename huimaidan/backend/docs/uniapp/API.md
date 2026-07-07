# 惠买单 - 后端接口需求文档

## 项目概述

惠买单是一个本地生活优惠买单平台，用户可以浏览周边商户、领取优惠券、在线买单享受折扣。本文档详细列出前端所有页面所需的后端接口。

**Base URL**: `https://ukw0y1.laf.run`（可在 `.env` 中配置 `VITE_SERVER_BASEURL`）

**认证方式**: 请求头 `Authorization: Bearer {token}`

---

## 目录

1. [用户认证模块](#1-用户认证模块)
2. [用户信息模块](#2-用户信息模块)
3. [菜单配置模块](#3-菜单配置模块)
4. [首页模块](#4-首页模块)
5. [商户模块](#5-商户模块)
6. [订单模块](#6-订单模块)
7. [支付模块](#7-支付模块)
8. [优惠券模块](#8-优惠券模块)
9. [积分模块](#9-积分模块)
10. [收藏模块](#10-收藏模块)
11. [浏览足迹模块](#11-浏览足迹模块)
12. [文件上传模块](#12-文件上传模块)

---

## 1. 用户认证模块

> **重要说明**: 本项目以微信小程序为主要平台，登录采用**微信小程序官方登录流程**。
>
> 当前已对接流程：
> 1. 小程序端调用 `wx.login()` 获取登录 `code`
> 2. 调用 `POST /api/auth`，小程序场景传 `auth.type = routine`
> 3. 若后端返回 `data.status = 201`，前端展示手机号快速验证按钮
> 4. 手机号组件回调后，将 `detail.code` 作为 `phone_code` 提交到 `POST /api/auth/mp_phone`
> 5. 后端返回自定义登录态 `token`
> 6. 前端保存 `token`，后续请求通过 `Authorization: Bearer {token}` 认证
>
> 参考文档: https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-login/code2Session.html
>
> 登录字段和响应层级以 `docs/uniapp/微信小程序登录对接文档.md` 为准。

### 1.1 微信小程序登录（主要登录方式）

- **接口**: `POST /api/auth`
- **是否需要登录**: 否
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| auth.type | string | 是 | 固定传 `routine` |
| auth.auth.code | string | 是 | 微信登录凭证，通过 `uni.login()` 获取 |
| auth.auth.spread | number | 否 | 邀请人 ID |

- **后端处理逻辑**:
  1. 使用 `auth.auth.code` 获取小程序 `openid`
  2. 根据 `openid` 查询用户
  3. 若用户已绑定手机号，返回 token
  4. 若用户未绑定手机号，返回 `data.status = 201` 和临时 `key`
  5. 前端继续调用 `/api/auth/mp_phone` 完成手机号绑定

- **响应数据** (`IAuthLoginRes`):

登录成功:
```json
{
  "status": 200,
  "message": "success",
  "data": {
    "status": 200,
    "result": {
      "token": "xxx",
      "exp": 31536000,
      "expires_time": 1812619651,
      "user": {}
    }
  }
}
```

需要绑定手机号:
```json
{
  "status": 200,
  "message": "success",
  "data": {
    "status": 201,
    "result": {
      "key": "Uxxxxxxxx",
      "wechat_phone_switch": "1"
    }
  }
}
```

### 1.1.1 微信手机号快速验证绑定

- **接口**: `POST /api/auth/mp_phone`
- **是否需要登录**: 否

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| auth_token | string | 是 | `/api/auth` 返回的临时 `key` |
| phone_code | string | 是 | 手机号快速验证组件回调 `detail.code` |

```json
{
  "auth_token": "Uxxxxxxxx",
  "phone_code": "getPhoneNumber 回调 detail.code"
}
```

说明：`phone_code` 不是 `wx.login()` 返回的登录 `code`，新版前端不需要额外传登录 `code` 到 `/api/auth/mp_phone`。

### 1.2 商户管理员登录

- **接口**: `POST /auth/admin/login`
- **是否需要登录**: 否
- **适用场景**: 商户管理员通过账号密码登录，管理自己门店的订单、优惠等
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| username | string | 是 | 商户管理员账号 |
| password | string | 是 | 商户管理员密码 |

- **响应数据**:

```json
{
  "status": 200,
  "message": "success",
  "data": {
    "token": "xxx",
    "exp": 31536000,
    "expires_time": 1812619651,
    "user": {}
  }
}
```

- **说明**: 商户管理员账号由平台后台创建，每个商户管理员绑定对应门店，普通用户无需此接口

### 1.3 刷新 Token

- **接口**: `POST /auth/refreshToken`
- **当前状态**: 后端未提供该接口
- **处理方式**: token 失效后重新执行微信小程序登录流程

### 1.4 退出登录

- **接口**: `POST /api/logout`
- **是否需要登录**: 是
- **请求参数**: 无
- **响应数据**:

```json
{
  "status": 200,
  "message": "退出登录"
}
```

### 1.5 获取验证码

- **接口**: `GET /user/getCode`
- **是否需要登录**: 否
- **请求参数**: 无
- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "captchaEnabled": true,
    "uuid": "唯一标识",
    "image": "base64图片"
  }
}
```

### 1.6 用户注册

- **接口**: `POST /auth/register`
- **是否需要登录**: 否
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| username | string | 是 | 用户名/手机号 |
| password | string | 是 | 密码 |
| confirmPassword | string | 是 | 确认密码 |
| code | string | 否 | 验证码（如启用验证码） |
| uuid | string | 否 | 验证码唯一标识 |
| inviteCode | string | 否 | 邀请码（推荐人） |

- **响应数据**: 无（注册成功后跳转登录页）

---

## 2. 用户信息模块

### 2.1 获取用户信息

- **接口**: `GET /user/info`
- **是否需要登录**: 是
- **请求参数**: 无
- **响应数据** (`IUserInfoRes`):

```json
{
  "code": 0,
  "data": {
    "userId": 1,
    "username": "user001",
    "nickname": "张三",
    "avatar": "https://xxx/avatar.jpg",
    "phone": "13800138000",
    "vipLevel": 1,
    "role": "user"
  }
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| userId | number | 用户ID |
| username | string | 用户名 |
| nickname | string | 昵称 |
| avatar | string | 头像URL |
| phone | string | 手机号 |
| vipLevel | number | VIP等级 (0:普通用户, 1:VIP) |
| role | string | 角色 |

### 2.2 修改用户信息

- **接口**: `POST /user/updateInfo`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| nickname | string | 否 | 昵称 |
| avatar | string | 否 | 头像URL |

- **响应数据**: 无

### 2.3 获取用户资产信息

- **接口**: `GET /user/assets`
- **是否需要登录**: 是
- **请求参数**: 无
- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "commission": 0.00,
    "points": 100,
    "couponCount": 3,
    "vipLevel": 1
  }
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| commission | number | 佣金余额 |
| points | number | 积分 |
| couponCount | number | 可用优惠券数量 |
| vipLevel | number | VIP等级 |

---

## 3. 菜单配置模块

> 本项目中所有菜单、分类、筛选条件均由后端动态配置，前端不硬编码。包括：
> - 首页/收藏页分类标签
> - 子分类面板（如美食下的火锅、烧烤等）
> - 筛选条件（附近、评分、销量等）
> - "我的"页面常用功能菜单

### 3.2 获取商户分类列表（首页/收藏页/浏览足迹通用）

- **接口**: `GET /config/categories`
- **是否需要登录**: 否
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| type | string | 否 | 使用场景: home/collection/history，默认返回全部 |

- **响应数据**:

```json
{
  "code": 0,
  "data": [
    {
      "id": 1,
      "name": "推荐",
      "key": "recommend",
      "sort": 1,
      "subCategories": []
    },
    {
      "id": 2,
      "name": "美食餐饮",
      "key": "food",
      "sort": 2,
      "subCategories": [
        { "id": 201, "name": "火锅", "key": "hotpot", "sort": 1 },
        { "id": 202, "name": "烧烤烤肉", "key": "bbq", "sort": 2 },
        { "id": 203, "name": "自助餐", "key": "buffet", "sort": 3 },
        { "id": 204, "name": "川菜", "key": "sichuan", "sort": 4 },
        { "id": 205, "name": "东北菜", "key": "dongbei", "sort": 5 }
      ]
    },
    {
      "id": 3,
      "name": "KTV/酒吧",
      "key": "ktv",
      "sort": 3,
      "subCategories": []
    },
    {
      "id": 4,
      "name": "洗浴按摩",
      "key": "spa",
      "sort": 4,
      "subCategories": []
    },
    {
      "id": 5,
      "name": "酒店民宿",
      "key": "hotel",
      "sort": 5,
      "subCategories": []
    },
    {
      "id": 6,
      "name": "休闲娱乐",
      "key": "leisure",
      "sort": 6,
      "subCategories": []
    }
  ]
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| id | number | 分类ID |
| name | string | 分类名称 |
| key | string | 分类标识（用于筛选） |
| sort | number | 排序值 |
| subCategories | array | 子分类列表 |

### 3.3 获取筛选条件列表

- **接口**: `GET /config/filters`
- **是否需要登录**: 否
- **请求参数**: 无
- **响应数据**:

```json
{
  "code": 0,
  "data": [
    {
      "id": 1,
      "name": "附近",
      "key": "nearby",
      "sort": 1,
      "options": [
        { "id": 1, "name": "附近1km", "value": 1 },
        { "id": 2, "name": "附近3km", "value": 3 },
        { "id": 3, "name": "附近5km", "value": 5 }
      ]
    },
    {
      "id": 2,
      "name": "评分",
      "key": "rating",
      "sort": 2,
      "options": []
    },
    {
      "id": 3,
      "name": "销量",
      "key": "sales",
      "sort": 3,
      "options": []
    }
  ]
}
```

### 3.4 获取"我的"页面功能菜单

- **接口**: `GET /config/my-menus`
- **是否需要登录**: 否
- **请求参数**: 无
- **响应数据**:

```json
{
  "code": 0,
  "data": [
    {
      "id": 1,
      "name": "买单记录",
      "icon": "i-carbon-document",
      "pagePath": "/pages/orders/orders",
      "sort": 1,
      "visible": true
    },
    {
      "id": 2,
      "name": "浏览足迹",
      "icon": "i-carbon-time",
      "pagePath": "/pages/browsing-history/browsing-history",
      "sort": 2,
      "visible": true
    },
    {
      "id": 3,
      "name": "分享海报",
      "icon": "i-carbon-share",
      "pagePath": "",
      "sort": 3,
      "visible": true
    },
    {
      "id": 4,
      "name": "我的团队",
      "icon": "i-carbon-group",
      "pagePath": "",
      "sort": 4,
      "visible": true
    },
    {
      "id": 5,
      "name": "商户管理",
      "icon": "i-carbon-user-avatar-filled-alt",
      "pagePath": "/pages/admin/login",
      "sort": 5,
      "visible": true
    }
  ]
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| id | number | 菜单ID |
| name | string | 菜单名称 |
| icon | string | 图标 |
| pagePath | string | 跳转页面路径，为空则显示"功能开发中" |
| sort | number | 排序值 |
| visible | boolean | 是否显示 |

---

## 4. 首页模块

### 4.1 获取首页推荐商户列表（AI智能精选）

- **接口**: `GET /home/recommend`
- **是否需要登录**: 否
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| longitude | number | 否 | 经度 |
| latitude | number | 否 | 纬度 |
| city | string | 否 | 城市 |
| limit | number | 否 | 返回数量，默认3 |

- **响应数据**:

```json
{
  "code": 0,
  "data": [
    {
      "id": 1,
      "name": "蜀大侠火锅",
      "image": "https://xxx.jpg",
      "discount": 8.0,
      "discountUnit": "折",
      "tag": "热门",
      "rating": 4.8
    }
  ]
}
```

### 4.2 获取商户分类列表

- **接口**: `GET /categories`
- **是否需要登录**: 否
- **请求参数**: 无
- **响应数据**:

```json
{
  "code": 0,
  "data": [
    {
      "id": 1,
      "name": "美食餐饮",
      "icon": "icon_url",
      "subCategories": [
        { "id": 101, "name": "火锅" },
        { "id": 102, "name": "烧烤" }
      ]
    }
  ]
}
```

### 4.3 获取商户列表（分页）

- **接口**: `GET /shops`
- **是否需要登录**: 否
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| categoryId | number | 否 | 分类ID |
| subCategoryId | number | 否 | 子分类ID |
| keyword | string | 否 | 搜索关键词 |
| longitude | number | 否 | 经度 |
| latitude | number | 否 | 纬度 |
| distance | number | 否 | 距离范围(km) |
| sortBy | string | 否 | 排序方式: distance/rating/sales |
| page | number | 否 | 页码，默认1 |
| pageSize | number | 否 | 每页数量，默认10 |

- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "total": 100,
    "list": [
      {
        "id": 1,
        "name": "蜀大侠火锅",
        "branchName": "摩尔城店",
        "image": "https://xxx.jpg",
        "category": "美食餐饮",
        "subCategory": "中餐",
        "rating": 4.8,
        "sales": "半年售35万+",
        "phone": "15780282354",
        "distance": "129m",
        "discount": 8.0,
        "discountUnit": "折",
        "pricePerPerson": 40
      }
    ]
  }
}
```

### 4.4 AI 智能搜索

- **接口**: `POST /home/aiSearch`
- **是否需要登录**: 否
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| keyword | string | 是 | 自然语言搜索词，如"想吃火锅，人均80，有老人" |
| longitude | number | 否 | 经度 |
| latitude | number | 否 | 纬度 |

- **响应数据**: 同 4.3 商户列表

---

## 5. 商户模块

### 5.1 获取商户详情

- **接口**: `GET /shops/{id}`
- **是否需要登录**: 否
- **请求参数**: 路径参数 `id`
- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "id": 1,
    "name": "蜜雪冰城",
    "slogan": "六一欢聚 大佬同款蜜桃四季春",
    "images": ["https://xxx1.jpg", "https://xxx2.jpg"],
    "category": "美食餐饮",
    "subCategory": "中餐",
    "sales": "35万+",
    "years": "收录8年",
    "rating": 5.0,
    "phone": "15780282354",
    "distance": "128m",
    "longitude": 116.4074,
    "latitude": 39.9042,
    "address": "北京市东城区王府井大街",
    "discount": 8.0,
    "discountUnit": "折",
    "pricePerPerson": 40,
    "tags": ["有大桌", "有宝宝椅", "可电话预订", "无烟餐厅"],
    "promoImage": "https://xxx.jpg",
    "stores": [
      { "id": 1, "name": "SM城市广场店" },
      { "id": 2, "name": "万达广场店" }
    ],
    "isCollected": false
  }
}
```

---

## 6. 订单模块

### 6.1 创建订单（买单）

- **接口**: `POST /orders`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| shopId | number | 是 | 商户ID |
| storeId | number | 否 | 门店ID |
| amount | number | 是 | 原始金额 |
| discountType | string | 是 | 优惠类型: discount/noDiscount |
| couponId | number | 否 | 优惠券ID |
| usePoints | boolean | 否 | 是否使用积分 |
| pointsAmount | number | 否 | 积分抵扣金额 |
| remark | string | 否 | 备注 |

- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "orderId": 123456,
    "orderNo": "260404205248537934",
    "payAmount": 8.00
  }
}
```

### 6.2 获取订单列表

- **接口**: `GET /orders`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| status | string | 否 | 订单状态: unpaid/completed/refund |
| keyword | string | 否 | 搜索关键词 |
| page | number | 否 | 页码 |
| pageSize | number | 否 | 每页数量 |

- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "total": 20,
    "list": [
      {
        "id": 1,
        "orderNo": "260404205248537934",
        "shopName": "蜀大侠火锅",
        "branchName": "摩尔城店",
        "payMethod": "余额支付",
        "amount": 10.00,
        "discount": 2.00,
        "actualAmount": 8.00,
        "status": "completed",
        "statusText": "已完成",
        "createTime": "2026-06-01 12:30"
      }
    ]
  }
}
```

### 6.3 获取订单详情

- **接口**: `GET /orders/{id}`
- **是否需要登录**: 是
- **请求参数**: 路径参数 `id`
- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "id": 1,
    "orderNo": "260404205248537934",
    "shopName": "蜀大侠火锅",
    "branchName": "摩尔城店",
    "payMethod": "零钱支付",
    "payStatus": "已支付",
    "payAmount": 10.00,
    "discount": 2.00,
    "actualAmount": 8.00,
    "payTime": "2026-06-01 12:30",
    "remark": "无",
    "status": "completed"
  }
}
```

### 6.4 获取订单状态统计

- **接口**: `GET /orders/statistics`
- **是否需要登录**: 是
- **请求参数**: 无
- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "unpaid": 0,
    "completed": 5,
    "refund": 0
  }
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| unpaid | number | 待付款订单数 |
| completed | number | 已完成订单数 |
| refund | number | 退款/售后订单数 |

### 6.5 申请退款/售后

- **接口**: `POST /orders/{id}/refund`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| reason | string | 是 | 退款原因 |
| description | string | 否 | 详细描述 |
| images | string[] | 否 | 凭证图片URL数组 |

- **响应数据**: 无

---

## 7. 支付模块

### 7.1 获取支付参数

- **接口**: `POST /pay/create`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| orderId | number | 是 | 订单ID |
| payMethod | string | 是 | 支付方式: wechat/balance |

- **响应数据** (微信支付):

```json
{
  "code": 0,
  "data": {
    "appId": "wx_xxx",
    "timeStamp": "1717200000",
    "nonceStr": "xxx",
    "package": "prepay_id=xxx",
    "signType": "RSA",
    "paySign": "xxx"
  }
}
```

- **说明**: 当前前端支付页只接入微信支付，后端仅需返回微信支付所需参数。

### 7.2 查询支付结果

- **接口**: `GET /pay/result/{orderId}`
- **是否需要登录**: 是
- **请求参数**: 路径参数 `orderId`
- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "paid": true,
    "orderId": 123456,
    "payTime": "2026-06-01 12:30"
  }
}
```

### 7.3 获取用户余额

- **接口**: `GET /pay/balance`
- **是否需要登录**: 是
- **请求参数**: 无
- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "balance": 100.00
  }
}
```

---

## 8. 优惠券模块

### 8.1 获取可领取的优惠券列表

- **接口**: `GET /coupons/available`
- **是否需要登录**: 否
- **请求参数**: 无
- **响应数据**:

```json
{
  "code": 0,
  "data": [
    {
      "id": 1,
      "name": "新人代金券",
      "amount": 10,
      "threshold": 100,
      "condition": "满100元可用",
      "expireTime": "2027-05-20 00:00:00",
      "type": "cash",
      "status": "available"
    }
  ]
}
```

### 8.2 领取优惠券

- **接口**: `POST /coupons/{id}/claim`
- **是否需要登录**: 是
- **请求参数**: 路径参数 `id`
- **响应数据**: 无

### 8.3 获取我的优惠券列表

- **接口**: `GET /coupons/mine`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| status | string | 否 | 状态: unused/used/expired |
| page | number | 否 | 页码 |
| pageSize | number | 否 | 每页数量 |

- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "total": 10,
    "list": [
      {
        "id": 1,
        "name": "新人代金券",
        "amount": 8,
        "threshold": 299,
        "condition": "满299元可用",
        "expireTime": "2027.5.20 00:00",
        "usedTime": "",
        "status": "unused"
      }
    ]
  }
}
```

### 8.4 获取可用优惠券（下单时选择）

- **接口**: `GET /coupons/usable`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| shopId | number | 是 | 商户ID |
| amount | number | 是 | 订单金额 |

- **响应数据**: 同 8.3

---

## 9. 积分模块

### 9.1 获取积分信息

- **接口**: `GET /points/info`
- **是否需要登录**: 是
- **请求参数**: 无
- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "total": 100,
    "convertRate": 1,
    "maxDeductPercent": 100,
    "convertAmount": 100.00
  }
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| total | number | 可用积分 |
| convertRate | number | 积分兑换比例 (1积分=?元) |
| maxDeductPercent | number | 最多可抵扣订单金额百分比 |
| convertAmount | number | 可抵扣金额 |

### 9.2 获取积分明细

- **接口**: `GET /points/records`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| page | number | 否 | 页码 |
| pageSize | number | 否 | 每页数量 |

- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "total": 50,
    "list": [
      {
        "id": 1,
        "type": "earn",
        "amount": 5,
        "description": "推荐新用户奖励",
        "createTime": "2026-06-01 12:30"
      }
    ]
  }
}
```

---

## 10. 收藏模块

### 10.1 获取收藏列表

- **接口**: `GET /collections`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| categoryId | number | 否 | 分类ID |
| keyword | string | 否 | 搜索关键词 |
| page | number | 否 | 页码 |
| pageSize | number | 否 | 每页数量 |

- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "total": 10,
    "list": [
      {
        "id": 1,
        "shopId": 100,
        "name": "TECHNO酒吧",
        "image": "https://xxx.jpg",
        "category": "美食餐饮",
        "rating": 4.0,
        "phone": "15780282354",
        "distance": "128m",
        "discount": 8.0,
        "discountUnit": "折",
        "sales": "半年售35万+"
      }
    ]
  }
}
```

### 10.2 添加收藏

- **接口**: `POST /collections`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| shopId | number | 是 | 商户ID |

- **响应数据**: 无

### 10.3 取消收藏

- **接口**: `DELETE /collections/{shopId}`
- **是否需要登录**: 是
- **请求参数**: 路径参数 `shopId`
- **响应数据**: 无

### 10.4 检查是否已收藏

- **接口**: `GET /collections/check/{shopId}`
- **是否需要登录**: 是
- **请求参数**: 路径参数 `shopId`
- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "isCollected": true
  }
}
```

---

## 11. 浏览足迹模块

### 11.1 获取浏览足迹列表

- **接口**: `GET /browsing-history`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| categoryId | string | 否 | 分类: recommend/food/ktv/spa/hotel/leisure |
| page | number | 否 | 页码 |
| pageSize | number | 否 | 每页数量 |

- **响应数据**:

```json
{
  "code": 0,
  "data": {
    "total": 20,
    "list": [
      {
        "id": 1,
        "shopId": 100,
        "name": "TECHNO酒吧",
        "image": "https://xxx.jpg",
        "category": "美食餐饮",
        "subCategory": "中餐",
        "rating": 4.0,
        "phone": "15780282354",
        "distance": "128m",
        "discount": "8.0折",
        "sales": "半年售35万+",
        "browseTime": "2026-06-01 12:30"
      }
    ]
  }
}
```

### 11.2 记录浏览足迹

- **接口**: `POST /browsing-history`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| shopId | number | 是 | 商户ID |

- **响应数据**: 无

### 11.3 清除浏览足迹

- **接口**: `DELETE /browsing-history`
- **是否需要登录**: 是
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| ids | number[] | 否 | 要删除的足迹ID数组，不传则清空全部 |

- **响应数据**: 无

---

## 12. 文件上传模块

### 12.1 上传图片

- **接口**: `POST /upload/image`
- **是否需要登录**: 是
- **Content-Type**: `multipart/form-data`
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| file | File | 是 | 图片文件 |

- **响应数据** (`IUploadSuccessInfo`):

```json
{
  "code": 0,
  "data": {
    "fileId": 1,
    "originalName": "avatar.jpg",
    "fileName": "20260601_abc123.jpg",
    "storagePath": "https://cdn.xxx.com/images/20260601_abc123.jpg",
    "fileHash": "abc123",
    "fileType": "image/jpeg",
    "fileBusinessType": "avatar",
    "fileSize": 102400
  }
}
```

### 12.2 上传用户头像

> 说明: 前端头像上传入口当前实际配置在 `src/utils/uploadFile.ts`，地址为 `/user/avatar`。

- **接口**: `POST /user/avatar`
- **是否需要登录**: 是
- **Content-Type**: `multipart/form-data`
- **请求参数**:

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| file | File | 是 | 头像图片文件 |

- **响应数据**: 同 12.1，建议直接返回 `IUploadSuccessInfo`

---

## 附录

### A. 通用响应格式

```json
{
  "code": 0,
  "msg": "success",
  "data": {}
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| code | number | 状态码，0 或 200 表示成功 |
| msg | string | 提示信息 |
| data | any | 响应数据 |

### B. 错误码约定

| 错误码 | 说明 |
|--------|------|
| 0 / 200 | 成功 |
| 401 | 未登录或 Token 过期 |
| 403 | 无权限 |
| 404 | 资源不存在 |
| 500 | 服务器内部错误 |

### C. 分页参数约定

| 字段 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| page | number | 1 | 页码 |
| pageSize | number | 10 | 每页数量 |

分页响应格式:

```json
{
  "total": 100,
  "list": []
}
```

### D. 已有接口汇总（已实现）

| 接口 | 方法 | 说明 |
|------|------|------|
| `/api/auth` | POST | 微信小程序登录（主要） |
| `/api/auth/mp_login_type` | POST | 查询是否需要绑定手机号 |
| `/api/auth/mp_phone` | POST | 手机号快速验证组件绑定登录 |
| `/auth/admin/login` | POST | 商户管理员登录 |
| `/api/logout` | POST | 退出登录 |
| `/user/info` | GET | 获取用户信息 |
| `/user/updateInfo` | POST | 修改用户信息 |
| `/user/avatar` | POST | 头像上传 |

### E. 待实现接口汇总

| 模块 | 接口数 | 优先级 |
|------|--------|--------|
| 菜单配置 | 4 | 高 |
| 用户认证（注册） | 1 | 高 |
| 用户资产 | 1 | 中 |
| 首页/商户列表 | 4 | 高 |
| 商户详情 | 2 | 高 |
| 订单 | 5 | 高 |
| 支付 | 3 | 高 |
| 优惠券 | 4 | 中 |
| 积分 | 2 | 中 |
| 收藏 | 4 | 中 |
| 浏览足迹 | 3 | 低 |
| 文件上传 | 1 | 中 |

### F. 仍需后端确认的接口点

1. `分享海报` 与 `我的团队` 目前只有前端入口，没有对应接口定义，建议后端补充路由、返回字段和分页规则后再落文档。
2. 当前前端仅接入微信支付，`/pay/create` 只需覆盖微信支付参数返回，不需要单独设计余额支付返回结构。
