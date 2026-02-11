-- 创建数据库
CREATE DATABASE IF NOT EXISTS wizard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 创建用户
CREATE USER IF NOT EXISTS 'wizard'@'%' IDENTIFIED BY 'password';

-- 授予权限
GRANT ALL PRIVILEGES ON wizard.* TO 'wizard'@'%';

-- 刷新权限
FLUSH PRIVILEGES;