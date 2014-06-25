-- 初期設定
SET NAMES utf8;

-- データベース設定
DROP DATABASE IF EXISTS `hoge`;
CREATE DATABASE IF NOT EXISTS `hoge` DEFAULT CHARSET utf8 COLLATE utf8_bin;
GRANT SELECT,INSERT,DELETE,UPDATE ON `hoge`.* TO 'hoge'@localhost IDENTIFIED BY 'hoge';
FLUSH PRIVILEGES;
USE `hoge`;

-- 管理権限
CREATE TABLE IF NOT EXISTS `auth` (
    `id`         int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `strong`     int(3) unsigned  NOT NULL                COMMENT '権限の強さ',
    `name`       varchar(30)      NOT NULL                COMMENT '権限名',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_bin COMMENT '管理権限';

-- 管理者
CREATE TABLE IF NOT EXISTS `admin` (
    `id`         int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `name`       varchar(30)      NOT NULL                COMMENT '名前',
    `auth_id`    int(10) unsigned NOT NULL                COMMENT '権限ID',
    `login_id`   varchar(36)      NOT NULL                COMMENT 'ログインID'  ,
    `login_pass` varchar(36)      NOT NULL                COMMENT 'ログインPASS',
    `created_at` datetime         NOT NULL                COMMENT '作成日',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (name),
    UNIQUE KEY `uk_lodin_id` (login_id),
    CONSTRAINT `fk_admin_auth_id`
        FOREIGN KEY (`auth_id`) REFERENCES `auth` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_bin COMMENT '管理者';

-- 仮登録管理者
CREATE TABLE IF NOT EXISTS `interim_admin` (
    `id`         int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `mail`       varchar(255)     NOT NULL                COMMENT 'メール',
    `auth_id`    int(10) unsigned NOT NULL                COMMENT '権限ID',
    `token`      varchar(255)     NOT NULL                COMMENT 'トークン',
    `created_at` datetime         NOT NULL                COMMENT '作成日',
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_interim_admin_auth_id`
        FOREIGN KEY (`auth_id`) REFERENCES `auth` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_bin COMMENT '仮登録管理者';

-- 役職
CREATE TABLE IF NOT EXISTS `grade` (
    `id`         int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `strong`     int(3) unsigned  NOT NULL                COMMENT '役職の強さ',
    `name`       varchar(30)      NOT NULL                COMMENT '役職名',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE=utf8_bin COMMENT '役職';

-- 部署
CREATE TABLE IF NOT EXISTS `unit` (
    `id`         int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `name`       varchar(30)      NOT NULL                COMMENT '部署名',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_bin COMMENT '部署';

-- ユーザー
CREATE TABLE IF NOT EXISTS `user` (
    `id`         int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `grade_id`   int(10) unsigned NOT NULL                COMMENT '役職ID',
    `unit_id`    int(10) unsigned NOT NULL                COMMENT '部署ID',
    `login_id`   varchar(36)      NOT NULL                COMMENT 'ログインID'  ,
    `login_pass` varchar(36)      NOT NULL                COMMENT 'ログインPASS',
    `name`       varchar(30)      NOT NULL                COMMENT '名前',
    `mail`       varchar(255)     NOT NULL                COMMENT 'メール',
    `product`    text             NOT NULL DEFAULT ''     COMMENT '紹介文',
    `updated_at` timestamp        NOT NULL                COMMENT '更新日',
    `created_at` date             NOT NULL                COMMENT '作成日',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`),
    CONSTRAINT `fk_user_grade_id`
        FOREIGN KEY (`grade_id`) REFERENCES `grade` (`id`),
    CONSTRAINT `fk_user_unit_id`
        FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_bin COMMENT 'ユーザー';

-- 仮登録ユーザー
CREATE TABLE IF NOT EXISTS `interim_user` (
    `id`         int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `mail`       varchar(255)     NOT NULL                COMMENT 'メール',
    `token`      varchar(255)     NOT NULL                COMMENT 'トークン',
    `created_at` datetime         NOT NULL                COMMENT '作成日',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_bin COMMENT '仮登録ユーザー';


-- お気に入りグループ
CREATE TABLE IF NOT EXISTS `favorite_group` (
    `id`          int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `name`        varchar(50)      NOT NULL                COMMENT '名前',
    `user_id`     int(10) unsigned NOT NULL                COMMENT 'ユーザーID',
    `created_at`  datetime         NOT NULL                COMMENT '作成日',
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_favorite_group_user_id`
        FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_bin COMMENT 'お気に入りグループ';

-- お気に入りユーザー
CREATE TABLE IF NOT EXISTS `favorite_user` (
    `id`                int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `user_id`           int(10) unsigned NOT NULL                COMMENT 'ユーザーID',
    `favorite_group_id` int(10) unsigned NOT NULL                COMMENT 'お気に入りID',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_id_favorite_group_id` (`user_id`, `favorite_group_id`),
    CONSTRAINT `fk_favorite_user_favorite_group_id`
        FOREIGN KEY (`favorite_group_id`) REFERENCES `favorite_group` (`id`),
    CONSTRAINT `fk_favorite_user_user_id`
        FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_bin COMMENT 'お気に入りユーザー';

-- レポート
CREATE TABLE IF NOT EXISTS `report` (
    `id`         int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `user_id`    int(10) unsigned NOT NULL                COMMENT 'ユーザID',
    `title`      varchar(100)     NOT NULL                COMMENT 'タイトル',
    `content`    text             NOT NULL                COMMENT '内容',
    `created_at` int(10)          NOT NULL                COMMENT '作成日(UNIX_TIME)',
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_report_user_id`
        FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_bin COMMENT 'レポート';

-- テンプレート
CREATE TABLE IF NOT EXISTS `template` (
    `id`         int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `user_id`    int(10) unsigned NOT NULL                COMMENT 'ユーザID',
    `title`      varchar(100)     NOT NULL                COMMENT 'タイトル',
    `content`    text             NOT NULL                COMMENT '内容',
    `created_at` datetime         NOT NULL                COMMENT '作成日',
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_template_user_id`
        FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_bin COMMENT 'テンプレート';

-- コメント
CREATE TABLE IF NOT EXISTS `comment` (
    `id`         int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `report_id`  int(10) unsigned NOT NULL                COMMENT 'レポートID',
    `user_id`    int(10) unsigned NOT NULL                COMMENT 'ユーザID',
    `content`    text             NOT NULL DEFAULT ''     COMMENT '内容',
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_comment_report_id`
        FOREIGN KEY (`report_id`) REFERENCES `report` (`id`),
    CONSTRAINT `fk_comment_user_id`
        FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_bin COMMENT 'コメント';

-- 既読
CREATE TABLE IF NOT EXISTS `read` (
    `id`         int(10) unsigned NOT NULL auto_increment COMMENT 'ID',
    `report_id`  int(10) unsigned NOT NULL                COMMENT 'レポートID',
    `user_id`    int(10) unsigned NOT NULL                COMMENT 'ユーザID',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_read` (`report_id`, `user_id`),
    CONSTRAINT `fk_read_report_id`
        FOREIGN KEY (`report_id`) REFERENCES `report` (`id`),
    CONSTRAINT `fk_read_user_id`
        FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET utf8 COLLATE utf8_bin COMMENT '既読';
