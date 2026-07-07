-- 在"移动端设置"(tab_h5, classify_id=72)下添加"惠买单使用说明视频"配置项
INSERT IGNORE INTO `eb_system_config` (`config_classify_id`, `config_name`, `config_key`, `config_type`, `config_rule`, `config_props`, `required`, `info`, `sort`, `user_type`, `status`, `create_time`, `linked_status`, `linked_id`, `linked_value`)
VALUES (72, '惠买单使用说明视频', 'coupon_tutorial_video', 'input', '', '', 0, '上传视频后将视频地址填入此处，支持mp4格式，用于小程序优惠券页面展示使用说明视频', 0, 0, 1, NOW(), 0, 0, 0);
