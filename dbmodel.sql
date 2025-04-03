-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Hutan implementation : © Timothée (Tisaac) Pecatte <tim.pecatte@gmail.com>, Pavel Kulagin (KuWizard) <kuzwiz@mail.ru>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql


CREATE TABLE IF NOT EXISTS `cards`
(
    `card_id`       smallint unsigned NOT NULL AUTO_INCREMENT,
    `flower_a`      varchar(1)  DEFAULT NULL,
    `flower_b`      varchar(1)  DEFAULT NULL,
    `flower_c`      varchar(1)  DEFAULT NULL,
    -- TODO: Remove card_location and card_state defaults, add NOT NULL and define those fields in the setup of Cards.php
    `card_location` varchar(32) DEFAULT '',
    `card_state`    tinyint     DEFAULT 0,
    PRIMARY KEY (`card_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `global_variables`
(
    `name`  varchar(255) NOT NULL,
    `value` JSON,
    PRIMARY KEY (`name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


CREATE TABLE IF NOT EXISTS `log`
(
    `id`       int(10) unsigned NOT NULL AUTO_INCREMENT,
    `move_id`  int(10)          NOT NULL,
    `table`    varchar(32)      NOT NULL,
    `primary`  varchar(32)      NOT NULL,
    `type`     varchar(32)      NOT NULL,
    `affected` JSON,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
