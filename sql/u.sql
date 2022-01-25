

ALTER TABLE `mou`.`mdl_monit_rating_listforms` ADD COLUMN `douid` INT(10) UNSIGNED DEFAULT 0 AFTER `collegeid`;

CREATE TABLE  `mou`.`mdl_monit_rating_dou` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `yearid` int(10) NOT NULL DEFAULT '0',
  `rayonid` int(10) NOT NULL DEFAULT '0',
  `douid` int(10) NOT NULL DEFAULT '0',
  `ratingcategoryid` int(10) NOT NULL DEFAULT '0',
  `criteriaid` int(10) NOT NULL DEFAULT '0',
  `mark` float DEFAULT NULL,
  `rationum` smallint(6) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `yearid_idx` (`yearid`,`douid`,`criteriaid`),
  KEY `rayonid_idx` (`rayonid`,`yearid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


insert into mdl_config_plugins(plugin, name, `value`) 
VALUES ('rating2012', 'avgzpregion', '20000#Среднемесячная заработная плата в общеобразовательных организациях региона');

insert into mdl_config_plugins(plugin, name, `value`) 
VALUES ('rating2012', 'healthlevel', '1#Областной показатель заболеваемости');


insert into mdl_config_plugins(plugin, name, `value`) 
VALUES ('rating2013', 'avgzpregion', '20000#Среднемесячная заработная плата в общеобразовательных организациях региона');

insert into mdl_config_plugins(plugin, name, `value`) 
VALUES ('rating2013', 'healthlevel', '1#Областной показатель заболеваемости');
