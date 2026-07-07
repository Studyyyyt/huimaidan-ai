-- 开发环境测试数据：请仅在本地或测试库手动导入。
-- 依赖：已导入 `惠买单_AI推荐大脑表结构.sql`，并已存在 merchant/category/city 等基础表。

INSERT INTO `eb_huimaidan_ai_tag`
(`tag_type`,`tag_value`,`tag_label`,`synonyms`,`tag_weight`,`sort`,`status`)
VALUES
('category','火锅','火锅','["涮肉","锅底","麻辣锅"]',80,10,1),
('category','川菜','川菜','["四川菜","水煮鱼","辣子鸡"]',75,20,1),
('category','烧烤','烧烤','["烤串","撸串","烤肉"]',75,30,1),
('category','奶茶','奶茶','["茶饮","饮品","下午茶"]',65,40,1),
('category','快餐','快餐','["简餐","工作餐","便餐"]',60,50,1),
('category','日料','日料','["寿司","刺身","日本料理"]',70,60,1),
('category','亲子餐厅','亲子餐厅','["带娃","儿童","宝宝"]',70,70,1),
('scene','聚餐','聚餐','["朋友聚会","多人","团建"]',70,10,1),
('scene','约会','约会','["情侣","浪漫","安静"]',70,20,1),
('scene','亲子','亲子','["带娃","孩子","宝宝"]',70,30,1),
('scene','商务','商务','["请客","谈事","接待"]',60,40,1),
('scene','日常','日常','["随便吃","一个人","简单"]',50,50,1),
('taste','辣','辣','["麻辣","重口","香辣"]',70,10,1),
('taste','清淡','清淡','["不辣","养生","少油"]',60,20,1),
('taste','甜','甜','["甜品","蛋糕","奶茶"]',60,30,1),
('facility','包间','包间','["私密","包厢","独立房间"]',70,10,1),
('facility','大桌','大桌','["多人桌","圆桌","聚餐桌"]',65,20,1),
('facility','宝宝椅','宝宝椅','["儿童椅","带娃"]',65,30,1),
('facility','无烟','无烟','["不抽烟","空气好"]',55,40,1),
('price','0-30','人均30以内','["便宜","低价","实惠"]',60,10,1),
('price','30-60','人均30-60','["不贵","实惠"]',60,20,1),
('price','60-100','人均60-100','["适中","中等"]',55,30,1),
('price','100-150','人均100-150','["品质","请客"]',50,40,1),
('price','150+','人均150以上','["高端","精致"]',45,50,1),
('feature','高评分','高评分','["口碑好","评价好"]',55,10,1),
('feature','高销量','高销量','["热门","人气"]',55,20,1),
('promotion','大折扣','大折扣','["划算","优惠大","折扣大"]',70,10,1)
ON DUPLICATE KEY UPDATE
`tag_label` = VALUES(`tag_label`),
`synonyms` = VALUES(`synonyms`),
`tag_weight` = VALUES(`tag_weight`),
`sort` = VALUES(`sort`),
`status` = VALUES(`status`);

-- 以下开发商户使用 2001-2020 高 ID，避免和真实数据冲突。
-- 生产环境不要导入本文件；仅用于本地/测试环境验证 AI 推荐效果。
DELETE FROM `eb_huimaidan_member_discount` WHERE `mer_id` BETWEEN 2001 AND 2020;
DELETE FROM `eb_huimaidan_merchant_discount` WHERE `mer_id` BETWEEN 2001 AND 2020;

INSERT INTO `eb_merchant`
(`mer_id`,`category_id`,`city_id`,`type_id`,`mer_name`,`real_name`,`mer_phone`,`mer_address`,`mer_keyword`,`mer_avatar`,`mer_banner`,`mini_banner`,`sales`,`product_score`,`service_score`,`postage_score`,`mark`,`reg_admin_id`,`sort`,`status`,`commission_rate`,`commission_switch`,`long`,`lat`,`is_del`,`is_audit`,`is_bro_room`,`is_bro_goods`,`is_best`,`is_trader`,`mer_state`,`mer_info`,`service_phone`,`care_count`,`copy_product_num`,`export_dump_num`,`mer_money`,`huimaidan_settlement_mode`,`huimaidan_withdraw_rate`,`huimaidan_min_extract_money`,`financial_type`,`sub_mchid`,`delivery_way`,`delivery_balance`,`margin`,`ot_margin`,`is_margin`,`offline_switch`,`care_ficti`,`region_id`,`business_id`,`applyment_id`,`applyment_switch`)
VALUES
(2001,4,2,3,'AI测麻辣火锅','AI测麻辣火锅','13000002001','测试商圈A座1层','火锅 麻辣 聚餐','/uploads/def/merchant.png','','',860,4.9,4.8,5.0,'AI推荐开发测试',0,90,1,0.00,0,'110.798800','32.629200',0,0,1,1,1,0,1,'想吃辣又要划算，就来这里','13000002001',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2002,4,2,3,'AI测川菜小馆','AI测川菜小馆','13000002002','测试商圈B座2层','川菜 辣 便宜','/uploads/def/merchant.png','','',520,4.7,4.8,5.0,'AI推荐开发测试',0,88,1,0.00,0,'110.800100','32.630000',0,0,1,1,1,0,1,'人均不高的下饭川菜','13000002002',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2003,4,2,3,'AI测夜宵烧烤','AI测夜宵烧烤','13000002003','测试夜市1号','烧烤 夜宵 聚餐','/uploads/def/merchant.png','','',980,4.8,4.7,5.0,'AI推荐开发测试',0,86,1,0.00,0,'110.802000','32.628000',0,0,1,1,1,0,1,'夜宵撸串首选','13000002003',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2004,4,2,3,'AI测奶茶轻食','AI测奶茶轻食','13000002004','测试商圈C座1层','奶茶 下午茶 甜','/uploads/def/merchant.png','','',430,4.8,4.8,5.0,'AI推荐开发测试',0,84,1,0.00,0,'110.799500','32.631100',0,0,1,1,1,0,1,'下午茶和轻食好去处','13000002004',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2005,4,2,3,'AI测快餐便当','AI测快餐便当','13000002005','测试写字楼负1层','快餐 工作餐 便宜','/uploads/def/merchant.png','','',760,4.6,4.6,5.0,'AI推荐开发测试',0,82,1,0.00,0,'110.797800','32.629900',0,0,1,1,0,0,1,'工作日午餐快一点','13000002005',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2006,4,2,3,'AI测日料寿司','AI测日料寿司','13000002006','测试街区D座3层','日料 约会 高评分','/uploads/def/merchant.png','','',310,4.9,4.9,5.0,'AI推荐开发测试',0,80,1,0.00,0,'110.795900','32.627900',0,0,1,1,1,0,1,'安静精致的寿司小店','13000002006',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2007,4,2,3,'AI测亲子餐厅','AI测亲子餐厅','13000002007','测试亲子中心2层','亲子 宝宝椅 无烟','/uploads/def/merchant.png','','',260,4.7,4.8,5.0,'AI推荐开发测试',0,78,1,0.00,0,'110.803200','32.631900',0,0,1,1,1,0,1,'带娃吃饭更省心','13000002007',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2008,4,2,3,'AI测包间火锅','AI测包间火锅','13000002008','测试商圈A座4层','火锅 包间 聚餐','/uploads/def/merchant.png','','',690,4.8,4.7,5.0,'AI推荐开发测试',0,76,1,0.00,0,'110.801300','32.626900',0,0,1,1,1,0,1,'有包间，适合多人聚餐','13000002008',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2009,4,2,3,'AI测商务川菜','AI测商务川菜','13000002009','测试商务中心5层','川菜 商务 包间','/uploads/def/merchant.png','','',410,4.8,4.9,5.0,'AI推荐开发测试',0,74,1,0.00,0,'110.796600','32.632500',0,0,1,1,1,0,1,'商务请客和包间都方便','13000002009',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2010,4,2,3,'AI测深夜烤肉','AI测深夜烤肉','13000002010','测试夜市2号','烧烤 夜宵 便宜','/uploads/def/merchant.png','','',850,4.6,4.7,5.0,'AI推荐开发测试',0,72,1,0.00,0,'110.804100','32.627200',0,0,1,1,0,0,1,'深夜也有折扣的烤肉店','13000002010',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2011,4,2,3,'AI测酸菜鱼','AI测酸菜鱼','13000002011','测试美食街11号','川菜 清淡 日常','/uploads/def/merchant.png','','',390,4.7,4.6,5.0,'AI推荐开发测试',0,70,1,0.00,0,'110.805000','32.630900',0,0,1,1,0,0,1,'想吃鱼又不想太油','13000002011',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2012,4,2,3,'AI测早餐简餐','AI测早餐简餐','13000002012','测试街角12号','快餐 早餐 便宜','/uploads/def/merchant.png','','',620,4.5,4.6,5.0,'AI推荐开发测试',0,68,1,0.00,0,'110.794900','32.629100',0,0,1,1,0,0,1,'早餐午餐都实惠','13000002012',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2013,4,2,3,'AI测甜品茶屋','AI测甜品茶屋','13000002013','测试商场13号','奶茶 甜品 约会','/uploads/def/merchant.png','','',280,4.9,4.8,5.0,'AI推荐开发测试',0,66,1,0.00,0,'110.800900','32.633000',0,0,1,1,1,0,1,'适合约会的甜品茶屋','13000002013',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2014,4,2,3,'AI测大桌湘菜','AI测大桌湘菜','13000002014','测试聚餐楼14号','辣 聚餐 大桌','/uploads/def/merchant.png','','',560,4.7,4.7,5.0,'AI推荐开发测试',0,64,1,0.00,0,'110.806200','32.628600',0,0,1,1,1,0,1,'大桌多人聚餐更合适','13000002014',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2015,4,2,3,'AI测清淡粥铺','AI测清淡粥铺','13000002015','测试社区15号','清淡 快餐 早餐','/uploads/def/merchant.png','','',340,4.6,4.7,5.0,'AI推荐开发测试',0,62,1,0.00,0,'110.797100','32.626400',0,0,1,1,0,0,1,'清淡不贵，早餐友好','13000002015',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2016,4,2,3,'AI测高端日料','AI测高端日料','13000002016','测试商场16号','日料 商务 高端','/uploads/def/merchant.png','','',210,5.0,4.9,5.0,'AI推荐开发测试',0,60,1,0.00,0,'110.802900','32.634000',0,0,1,1,1,0,1,'请客和商务接待更稳','13000002016',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2017,4,2,3,'AI测儿童披萨','AI测儿童披萨','13000002017','测试亲子中心17号','亲子 宝宝椅 无烟','/uploads/def/merchant.png','','',300,4.7,4.8,5.0,'AI推荐开发测试',0,58,1,0.00,0,'110.804600','32.632800',0,0,1,1,1,0,1,'宝宝椅和无烟环境齐全','13000002017',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2018,4,2,3,'AI测串串香','AI测串串香','13000002018','测试美食街18号','火锅 辣 便宜','/uploads/def/merchant.png','','',740,4.6,4.6,5.0,'AI推荐开发测试',0,56,1,0.00,0,'110.799000','32.625900',0,0,1,1,0,0,1,'低人均麻辣串串','13000002018',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2019,4,2,3,'AI测轻食沙拉','AI测轻食沙拉','13000002019','测试写字楼19号','快餐 清淡 日常','/uploads/def/merchant.png','','',220,4.8,4.7,5.0,'AI推荐开发测试',0,54,1,0.00,0,'110.796000','32.630700',0,0,1,1,0,0,1,'清淡轻食，工作餐友好','13000002019',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1),
(2020,4,2,3,'AI测夜宵砂锅','AI测夜宵砂锅','13000002020','测试夜市20号','夜宵 快餐 便宜','/uploads/def/merchant.png','','',500,4.5,4.6,5.0,'AI推荐开发测试',0,52,1,0.00,0,'110.803700','32.625700',0,0,1,1,0,0,1,'深夜砂锅，便宜管饱','13000002020',0,0,0,'0.00',2,'0.00','500.00',1,'','','0.00','0.00','0.00',0,0,0,0,0,'',1)
ON DUPLICATE KEY UPDATE
`mer_name`=VALUES(`mer_name`),`real_name`=VALUES(`real_name`),`mer_phone`=VALUES(`mer_phone`),`mer_address`=VALUES(`mer_address`),`mer_keyword`=VALUES(`mer_keyword`),`mer_avatar`=VALUES(`mer_avatar`),`sales`=VALUES(`sales`),`product_score`=VALUES(`product_score`),`service_score`=VALUES(`service_score`),`sort`=VALUES(`sort`),`status`=VALUES(`status`),`long`=VALUES(`long`),`lat`=VALUES(`lat`),`is_del`=VALUES(`is_del`),`is_best`=VALUES(`is_best`),`mer_state`=VALUES(`mer_state`),`mer_info`=VALUES(`mer_info`),`service_phone`=VALUES(`service_phone`),`huimaidan_settlement_mode`=VALUES(`huimaidan_settlement_mode`);

INSERT INTO `eb_huimaidan_merchant_profile`
(`mer_id`,`branch_name`,`configured_sales`,`per_capita`,`business_hours`,`facilities`,`promo_image`,`slogan`)
VALUES
(2001,'测试商圈店',860,88,'["10:00-23:30"]','{"has_large_table":true,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":true,"is_non_smoking":false}','/uploads/def/merchant.png','想吃辣又要划算，就来这里'),
(2002,'测试商圈店',520,45,'["10:00-22:00"]','{"has_large_table":true,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":true,"is_non_smoking":false}','/uploads/def/merchant.png','人均不高的下饭川菜'),
(2003,'夜市店',980,62,'["17:00-02:00"]','{"has_large_table":true,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":false,"is_non_smoking":false}','/uploads/def/merchant.png','夜宵撸串首选'),
(2004,'商场店',430,24,'["09:00-22:00"]','{"has_large_table":false,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":false,"is_non_smoking":true}','/uploads/def/merchant.png','下午茶和轻食好去处'),
(2005,'写字楼店',760,28,'["07:30-20:30"]','{"has_large_table":false,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":false,"is_non_smoking":true}','/uploads/def/merchant.png','工作日午餐快一点'),
(2006,'街区店',310,128,'["11:00-22:00"]','{"has_large_table":false,"has_baby_chair":false,"has_private_room":true,"can_phone_reserve":true,"is_non_smoking":true}','/uploads/def/merchant.png','安静精致的寿司小店'),
(2007,'亲子中心店',260,79,'["10:00-21:30"]','{"has_large_table":true,"has_baby_chair":true,"has_private_room":false,"can_phone_reserve":true,"is_non_smoking":true}','/uploads/def/merchant.png','带娃吃饭更省心'),
(2008,'包间店',690,118,'["10:30-23:30"]','{"has_large_table":true,"has_baby_chair":false,"has_private_room":true,"can_phone_reserve":true,"is_non_smoking":false}','/uploads/def/merchant.png','有包间，适合多人聚餐'),
(2009,'商务中心店',410,128,'["10:00-22:30"]','{"has_large_table":true,"has_baby_chair":false,"has_private_room":true,"can_phone_reserve":true,"is_non_smoking":true}','/uploads/def/merchant.png','商务请客和包间都方便'),
(2010,'夜市店',850,55,'["17:00-03:00"]','{"has_large_table":true,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":false,"is_non_smoking":false}','/uploads/def/merchant.png','深夜也有折扣的烤肉店'),
(2011,'美食街店',390,58,'["10:00-22:00"]','{"has_large_table":true,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":true,"is_non_smoking":true}','/uploads/def/merchant.png','想吃鱼又不想太油'),
(2012,'街角店',620,18,'["06:30-20:30"]','{"has_large_table":false,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":false,"is_non_smoking":true}','/uploads/def/merchant.png','早餐午餐都实惠'),
(2013,'商场店',280,32,'["10:00-22:00"]','{"has_large_table":false,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":false,"is_non_smoking":true}','/uploads/def/merchant.png','适合约会的甜品茶屋'),
(2014,'聚餐楼店',560,76,'["10:00-23:00"]','{"has_large_table":true,"has_baby_chair":false,"has_private_room":true,"can_phone_reserve":true,"is_non_smoking":false}','/uploads/def/merchant.png','大桌多人聚餐更合适'),
(2015,'社区店',340,22,'["06:30-21:00"]','{"has_large_table":false,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":false,"is_non_smoking":true}','/uploads/def/merchant.png','清淡不贵，早餐友好'),
(2016,'商场店',210,188,'["11:00-22:30"]','{"has_large_table":false,"has_baby_chair":false,"has_private_room":true,"can_phone_reserve":true,"is_non_smoking":true}','/uploads/def/merchant.png','请客和商务接待更稳'),
(2017,'亲子中心店',300,68,'["10:00-21:00"]','{"has_large_table":true,"has_baby_chair":true,"has_private_room":false,"can_phone_reserve":true,"is_non_smoking":true}','/uploads/def/merchant.png','宝宝椅和无烟环境齐全'),
(2018,'美食街店',740,38,'["10:30-23:30"]','{"has_large_table":true,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":false,"is_non_smoking":false}','/uploads/def/merchant.png','低人均麻辣串串'),
(2019,'写字楼店',220,39,'["09:00-20:30"]','{"has_large_table":false,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":false,"is_non_smoking":true}','/uploads/def/merchant.png','清淡轻食，工作餐友好'),
(2020,'夜市店',500,35,'["17:00-02:00"]','{"has_large_table":false,"has_baby_chair":false,"has_private_room":false,"can_phone_reserve":false,"is_non_smoking":false}','/uploads/def/merchant.png','深夜砂锅，便宜管饱')
ON DUPLICATE KEY UPDATE
`branch_name`=VALUES(`branch_name`),`configured_sales`=VALUES(`configured_sales`),`per_capita`=VALUES(`per_capita`),`business_hours`=VALUES(`business_hours`),`facilities`=VALUES(`facilities`),`promo_image`=VALUES(`promo_image`),`slogan`=VALUES(`slogan`);

INSERT INTO `eb_huimaidan_merchant_discount`
(`discount_id`,`mer_id`,`pool_id`,`merchant_discount`,`status`,`start_time`,`end_time`,`sort`,`remark`)
VALUES
(90001,2001,0,0.82,1,NULL,NULL,90,'AI推荐开发测试'),
(90002,2002,0,0.78,1,NULL,NULL,88,'AI推荐开发测试'),
(90003,2003,0,0.88,1,NULL,NULL,86,'AI推荐开发测试'),
(90004,2004,0,0.90,1,NULL,NULL,84,'AI推荐开发测试'),
(90005,2005,0,0.85,1,NULL,NULL,82,'AI推荐开发测试'),
(90006,2006,0,0.92,1,NULL,NULL,80,'AI推荐开发测试'),
(90007,2007,0,0.88,1,NULL,NULL,78,'AI推荐开发测试'),
(90008,2008,0,0.86,1,NULL,NULL,76,'AI推荐开发测试'),
(90009,2009,0,0.90,1,NULL,NULL,74,'AI推荐开发测试'),
(90010,2010,0,0.80,1,NULL,NULL,72,'AI推荐开发测试'),
(90011,2011,0,0.88,1,NULL,NULL,70,'AI推荐开发测试'),
(90012,2012,0,0.83,1,NULL,NULL,68,'AI推荐开发测试'),
(90013,2013,0,0.90,1,NULL,NULL,66,'AI推荐开发测试'),
(90014,2014,0,0.86,1,NULL,NULL,64,'AI推荐开发测试'),
(90015,2015,0,0.84,1,NULL,NULL,62,'AI推荐开发测试'),
(90016,2016,0,0.93,1,NULL,NULL,60,'AI推荐开发测试'),
(90017,2017,0,0.88,1,NULL,NULL,58,'AI推荐开发测试'),
(90018,2018,0,0.79,1,NULL,NULL,56,'AI推荐开发测试'),
(90019,2019,0,0.89,1,NULL,NULL,54,'AI推荐开发测试'),
(90020,2020,0,0.81,1,NULL,NULL,52,'AI推荐开发测试');

INSERT INTO `eb_huimaidan_member_discount`
(`discount_id`,`mer_id`,`member_level`,`member_discount`,`status`)
VALUES
(90001,2001,1,0.82,1),(90002,2002,1,0.78,1),(90003,2003,1,0.88,1),(90004,2004,1,0.90,1),(90005,2005,1,0.85,1),
(90006,2006,1,0.92,1),(90007,2007,1,0.88,1),(90008,2008,1,0.86,1),(90009,2009,1,0.90,1),(90010,2010,1,0.80,1),
(90011,2011,1,0.88,1),(90012,2012,1,0.83,1),(90013,2013,1,0.90,1),(90014,2014,1,0.86,1),(90015,2015,1,0.84,1),
(90016,2016,1,0.93,1),(90017,2017,1,0.88,1),(90018,2018,1,0.79,1),(90019,2019,1,0.89,1),(90020,2020,1,0.81,1);

INSERT INTO `eb_huimaidan_merchant_tag`
(`mer_id`,`tag_type`,`tag_value`,`tag_weight`,`is_auto`)
VALUES
(1001,'category','火锅',90,0),(1001,'taste','辣',80,0),(1001,'scene','聚餐',75,0),(1001,'price','60-100',60,0),(1001,'facility','大桌',70,0),(1001,'promotion','大折扣',80,0),
(1002,'category','川菜',90,0),(1002,'taste','辣',85,0),(1002,'scene','日常',55,0),(1002,'price','30-60',75,0),(1002,'promotion','大折扣',70,0),
(1003,'category','烧烤',90,0),(1003,'scene','聚餐',75,0),(1003,'meal','supper',80,0),(1003,'price','60-100',55,0),
(1004,'category','奶茶',90,0),(1004,'taste','甜',80,0),(1004,'scene','约会',65,0),(1004,'meal','tea',80,0),(1004,'price','0-30',75,0),
(1005,'category','快餐',85,0),(1005,'scene','日常',70,0),(1005,'price','0-30',80,0),
(1006,'category','日料',90,0),(1006,'scene','约会',80,0),(1006,'price','100-150',65,0),(1006,'feature','高评分',70,0),
(1007,'category','亲子餐厅',90,0),(1007,'scene','亲子',85,0),(1007,'facility','宝宝椅',85,0),(1007,'facility','无烟',70,0),(1007,'price','60-100',60,0),
(1008,'category','火锅',90,0),(1008,'taste','辣',80,0),(1008,'scene','聚餐',75,0),(1008,'facility','包间',80,0),(1008,'price','100-150',55,0),
(1009,'category','川菜',85,0),(1009,'taste','辣',80,0),(1009,'facility','包间',75,0),(1009,'scene','商务',70,0),(1009,'price','100-150',60,0),
(1010,'category','烧烤',90,0),(1010,'meal','late_night',80,0),(1010,'scene','聚餐',70,0),(1010,'price','30-60',75,0),
(2001,'category','火锅',90,0),(2001,'taste','辣',85,0),(2001,'scene','聚餐',80,0),(2001,'price','60-100',75,0),(2001,'facility','大桌',75,0),(2001,'promotion','大折扣',85,0),(2001,'meal','dinner',70,0),
(2002,'category','川菜',90,0),(2002,'taste','辣',85,0),(2002,'scene','日常',70,0),(2002,'price','30-60',85,0),(2002,'promotion','大折扣',80,0),(2002,'meal','lunch',70,0),
(2003,'category','烧烤',90,0),(2003,'scene','聚餐',80,0),(2003,'meal','supper',90,0),(2003,'meal','late_night',90,0),(2003,'price','60-100',70,0),
(2004,'category','奶茶',90,0),(2004,'taste','甜',80,0),(2004,'scene','约会',70,0),(2004,'meal','tea',90,0),(2004,'price','0-30',90,0),
(2005,'category','快餐',90,0),(2005,'scene','日常',80,0),(2005,'price','0-30',90,0),(2005,'meal','lunch',80,0),(2005,'meal','breakfast',65,0),
(2006,'category','日料',90,0),(2006,'scene','约会',85,0),(2006,'price','100-150',75,0),(2006,'feature','高评分',80,0),
(2007,'category','亲子餐厅',90,0),(2007,'scene','亲子',90,0),(2007,'facility','宝宝椅',90,0),(2007,'facility','无烟',80,0),(2007,'price','60-100',75,0),
(2008,'category','火锅',90,0),(2008,'taste','辣',85,0),(2008,'scene','聚餐',90,0),(2008,'facility','包间',90,0),(2008,'facility','大桌',85,0),(2008,'price','100-150',75,0),
(2009,'category','川菜',85,0),(2009,'taste','辣',80,0),(2009,'facility','包间',85,0),(2009,'scene','商务',85,0),(2009,'price','100-150',75,0),
(2010,'category','烧烤',90,0),(2010,'meal','supper',90,0),(2010,'meal','late_night',90,0),(2010,'scene','聚餐',75,0),(2010,'price','30-60',85,0),(2010,'promotion','大折扣',85,0),
(2011,'category','川菜',80,0),(2011,'taste','清淡',75,0),(2011,'scene','日常',70,0),(2011,'price','30-60',80,0),
(2012,'category','快餐',90,0),(2012,'meal','breakfast',90,0),(2012,'meal','lunch',75,0),(2012,'scene','日常',80,0),(2012,'price','0-30',90,0),
(2013,'category','奶茶',85,0),(2013,'taste','甜',85,0),(2013,'scene','约会',80,0),(2013,'meal','tea',90,0),(2013,'price','30-60',70,0),(2013,'feature','高评分',75,0),
(2014,'taste','辣',80,0),(2014,'scene','聚餐',90,0),(2014,'facility','大桌',90,0),(2014,'facility','包间',75,0),(2014,'price','60-100',80,0),
(2015,'category','快餐',80,0),(2015,'taste','清淡',85,0),(2015,'meal','breakfast',90,0),(2015,'scene','日常',75,0),(2015,'price','0-30',90,0),
(2016,'category','日料',90,0),(2016,'scene','商务',85,0),(2016,'facility','包间',80,0),(2016,'price','150+',85,0),(2016,'feature','高评分',90,0),
(2017,'category','亲子餐厅',85,0),(2017,'scene','亲子',90,0),(2017,'facility','宝宝椅',90,0),(2017,'facility','无烟',85,0),(2017,'price','60-100',75,0),
(2018,'category','火锅',80,0),(2018,'taste','辣',90,0),(2018,'scene','日常',75,0),(2018,'price','30-60',90,0),(2018,'promotion','大折扣',90,0),
(2019,'category','快餐',80,0),(2019,'taste','清淡',90,0),(2019,'scene','日常',75,0),(2019,'price','30-60',75,0),
(2020,'category','快餐',75,0),(2020,'meal','supper',85,0),(2020,'meal','late_night',90,0),(2020,'scene','日常',75,0),(2020,'price','30-60',90,0),(2020,'promotion','大折扣',85,0)
ON DUPLICATE KEY UPDATE
`tag_weight` = VALUES(`tag_weight`),
`is_auto` = VALUES(`is_auto`);

UPDATE `eb_merchant`
SET `mer_avatar` = '/static/images/default-avatar.png'
WHERE `mer_id` BETWEEN 2001 AND 2020;

UPDATE `eb_huimaidan_merchant_profile`
SET `promo_image` = '/static/images/default-avatar.png'
WHERE `mer_id` BETWEEN 2001 AND 2020;
