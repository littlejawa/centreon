-- Change version of Centreon
UPDATE `informations` SET `value` = '18.10.2' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.1' LIMIT 1;

-- Changing contact_lang default value to 'browser' and each of its value from NULL to 'browser'
ALTER TABLE contact CHANGE `contact_lang` `contact_lang` varchar(255) DEFAULT 'browser';
UPDATE contact SET `contact_lang` = 'browser' WHERE `contact_lang` IS NULL;
