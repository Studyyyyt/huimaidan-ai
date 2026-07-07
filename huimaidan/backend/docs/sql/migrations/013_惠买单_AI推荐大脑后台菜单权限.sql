-- 惠买单 AI 推荐大脑后台管理菜单
-- 平台后台使用 20200 段菜单 ID；商户后台惠买单菜单保留 20018 段，避免互相覆盖。
-- 菜单 route 指向 CRMEB 后台中的独立页跳转路由，最终打开 /huimaidan_ai_admin.html。
-- 旧的"惠买单"平台后台菜单缺少对应 Vue 页面，先隐藏，避免进入 /admin/404。

SET NAMES utf8mb4;

-- 隐藏旧的独立 AI 顶级菜单，避免侧边栏出现突兀的 AI 图标。
UPDATE `eb_system_menu`
SET `is_show` = 0,
    `update_time` = NOW()
WHERE `menu_id` = 20120 OR `menu_name` = 'AI 推荐大脑';

-- 隐藏平台后台中尚未实现页面的惠买单菜单；保留商户后台惠买单菜单
UPDATE `eb_system_menu`
SET `is_show` = 0
WHERE `is_mer` = 0 AND (`menu_id` = 20000 OR `pid` = 20000);

-- 添加平台后台惠买单 AI 管理入口。
INSERT INTO `eb_system_menu`
(`menu_id`, `pid`, `path`, `icon`, `menu_name`, `route`, `params`, `sort`, `is_show`, `is_mer`, `is_menu`, `is_agent`)
VALUES
(20200, 0, '/', 's-shop', '惠买单', '/huimaidan/import', '[]', 94, 1, 0, 1, 0),
(20201, 20200, '/20200/', '', '商户导入/维护', '/huimaidan/import', '[]', 1, 1, 0, 1, 0),
(20202, 20200, '/20200/', '', '优惠规则', '/huimaidan/discount', '[]', 2, 1, 0, 1, 0),
(20203, 20200, '/20200/', '', '商家标签', '/huimaidan/merchant-tags', '[]', 3, 1, 0, 1, 0),
(20204, 20200, '/20200/', '', 'AI标签库', '/huimaidan/ai-tags', '[]', 4, 1, 0, 1, 0),
(20205, 20200, '/20200/', '', 'Banner配置', '/huimaidan/banner', '[]', 5, 1, 0, 1, 0),
(20206, 20200, '/20200/', '', 'AI参数配置', '/huimaidan/config', '[]', 6, 1, 0, 1, 0),
(20207, 20200, '/20200/', '', '推荐日志', '/huimaidan/logs', '[]', 7, 1, 0, 1, 0)
ON DUPLICATE KEY UPDATE
`pid` = VALUES(`pid`),
`path` = VALUES(`path`),
`icon` = VALUES(`icon`),
`menu_name` = VALUES(`menu_name`),
`route` = VALUES(`route`),
`params` = VALUES(`params`),
`sort` = VALUES(`sort`),
`is_show` = VALUES(`is_show`),
`is_mer` = VALUES(`is_mer`),
`is_menu` = VALUES(`is_menu`),
`update_time` = NOW();

-- 为超级管理员角色（role_id=1）补充权限规则
UPDATE `eb_system_role`
SET `rules` = CONCAT(`rules`, ',20200,20201,20202,20203,20204,20205,20206,20207')
WHERE `role_id` = 1 AND `rules` NOT LIKE '%20200%';

-- 如果存在基于 eb_relevance 的权限关联，也写入一条
INSERT INTO `eb_relevance` (`left_id`, `right_id`, `type`)
SELECT 1, ids.menu_id, 'admin'
FROM (
    SELECT 20200 AS menu_id
    UNION ALL SELECT 20201
    UNION ALL SELECT 20202
    UNION ALL SELECT 20203
    UNION ALL SELECT 20204
    UNION ALL SELECT 20205
    UNION ALL SELECT 20206
    UNION ALL SELECT 20207
) ids
WHERE NOT EXISTS (
    SELECT 1 FROM `eb_relevance` r WHERE r.`left_id` = 1 AND r.`right_id` = ids.menu_id AND r.`type` = 'admin'
);
