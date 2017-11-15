-- task #14746
UPDATE `core_setting` SET `regroup` = '8' WHERE `core_setting`.`param_name` = 'google_stat_in_lms'
UPDATE `core_setting` SET `regroup` = '8' WHERE `core_setting`.`param_name` = 'google_stat_code'

-- task #14734
UPDATE `core_setting` SET `regroup` = '4' WHERE `core_setting`.`param_name` = 'paypal_mail';
UPDATE `core_setting` SET `regroup` = '4' WHERE `core_setting`.`param_name` = 'paypal_currency';
UPDATE `core_setting` SET `regroup` = '4' WHERE `core_setting`.`param_name` = 'currency_symbol';
UPDATE `core_setting` SET `regroup` = '4' WHERE `core_setting`.`param_name` = 'paypal_sandbox';

UPDATE `core_setting` SET `sequence` = '14' WHERE `core_setting`.`param_name` = 'paypal_mail';
UPDATE `core_setting` SET `sequence` = '15' WHERE `core_setting`.`param_name` = 'paypal_currency';
UPDATE `core_setting` SET `sequence` = '16' WHERE `core_setting`.`param_name` = 'currency_symbol';
UPDATE `core_setting` SET `sequence` = '17' WHERE `core_setting`.`param_name` = 'paypal_sandbox';

-- task #14736
DELETE FROM `core_lang_text` WHERE `core_lang_text`.`id_text` = 393;

-- task #14734
UPDATE `core_setting` SET `regroup` = '1' WHERE `core_setting`.`param_name` = 'mail_sender';
UPDATE `core_setting` SET `regroup` = '1' WHERE `core_setting`.`param_name` = 'customer_help_email';
UPDATE `core_setting` SET `regroup` = '1' WHERE `core_setting`.`param_name` = 'customer_help_subj_pfx';

UPDATE `core_setting` SET `sequence` = '9' WHERE `core_setting`.`param_name` = 'mail_sender'; UPDATE `core_setting` SET `sequence` = '10' WHERE `core_setting`.`param_name` = 'customer_help_email'; UPDATE `core_setting` SET `param_load` = '11' WHERE `core_setting`.`param_name` = 'customer_help_subj_pfx';

-- task #14733
UPDATE `core_setting` SET `regroup` = '8' WHERE `core_setting`.`param_name` = 'ttlSession';

-- task #14747
UPDATE `core_setting` SET `regroup` = '8' WHERE `core_setting`.`param_name` = 'Clear_Twig_Cache';
UPDATE `core_setting` SET `sequence` = '27' WHERE `core_setting`.`param_name` = 'Clear_Twig_Cache';
UPDATE `core_setting` SET `pack` = 'twig_cache' WHERE `core_setting`.`param_name` = 'Clear_Twig_Cache';
INSERT INTO `core_lang_text` (`id_text`, `text_key`, `text_module`, `text_attributes`) VALUES (NULL, '_CLEAR_TWIG_CACHE', 'configuration', '');

INSERT INTO `core_lang_translation` (`id_text`, `lang_code`, `translation_text`, `save_date`) VALUES ('2683', 'english', 'Clear twig cache', '2017-10-16 17:49:29'), ('2683', 'italian', 'Elimina twig cache', '2017-10-16 17:49:29');