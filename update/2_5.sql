-- Strutture per la gestione Slim e conversione plugins
ALTER TABLE `zz_modules` ADD `class` varchar(255) NOT NULL;
ALTER TABLE `zz_modules` ADD `type` ENUM('module', 'record_plugin', 'module_plugin') NOT NULL DEFAULT 'module';
INSERT INTO `zz_modules` (`id`, `name`, `title`, `type`, `enabled`, `default`, `options`, `options2`, `directory`, `parent`) SELECT NULL, `name`, `title`, IF(`position` = 'tab', 'record_plugin', 'module_plugin'), `enabled`, `default`, `options`, `options2`, `directory`, `idmodule_to` FROM `zz_plugins`;
UPDATE `zz_modules` SET `name` = 'Statistiche anagrafiche', `default` = 1 WHERE `directory` = 'statistiche_anagrafiche';
UPDATE `zz_modules` SET `name` = 'Statistiche articoli' WHERE `directory` = 'statistiche_articoli';
UPDATE `zz_modules` SET `name` = 'Componenti impianto', `directory` = 'componenti_impianto', `default` = 1 WHERE `name` = 'Componenti';
UPDATE `zz_modules` SET `directory` = 'giacenze', `options` = 'custom', `default` = 1 WHERE `name` = 'Giacenze';
UPDATE `zz_modules` SET `name` = 'Revisioni preventivi', `default` = 1 WHERE `name` = 'Revisioni';
UPDATE `zz_modules` SET `name` = 'Importazione FE', `default` = 1 WHERE `directory` = 'importFE';
UPDATE `zz_modules` SET `name` = 'Esportazione FE', `default` = 1 WHERE `directory` = 'exportFE';
UPDATE `zz_modules` SET `name` = 'Rinnovi contratto', `default` = 1, `directory` = 'rinnovi_contratti' WHERE `name` = 'Rinnovi';
UPDATE `zz_modules` SET `name` = 'Consuntivo contratto', `directory` = 'consuntivo_contratto', `default` = 1 WHERE `name` = 'Consuntivo' LIMIT 1;
UPDATE `zz_modules` SET `name` = 'Consuntivo preventivo', `directory` = 'consuntivo_preventivo', `default` = 1 WHERE `name` = 'Consuntivo' LIMIT 1;
UPDATE `zz_modules` SET `name` = 'Impianti dell''intervento', `directory` = 'impianti_intervento', `default` = 1 WHERE `name` = 'Impianti';
UPDATE `zz_modules` SET `name` = 'Interventi svolti per l''impianto', `directory` = 'interventi_svolti_impianto', `default` = 1 WHERE `name` = 'Interventi svolti';
UPDATE `zz_modules` SET `name` = 'Seriali', `directory` = 'seriali', `default` = 1 WHERE `name` = 'Serial';
UPDATE `zz_modules` SET `directory` = 'movimenti', `default` = 1 WHERE `name` = 'Movimenti';

UPDATE `zz_modules` SET `class` = 'Modules\\Retro\\Manager';

-- Aggiornamento allegati
UPDATE `zz_files` SET `id_module` = (SELECT `id` FROM `zz_modules` WHERE `title` = (SELECT `title` FROM `zz_plugins` WHERE `id` = `zz_files`.`id_plugin`) AND `parent` = (SELECT `idmodule_to` FROM `zz_plugins` WHERE `id` = `zz_files`.`id_plugin`))  WHERE `id_plugin` IS NOT NULL;

ALTER TABLE `zz_prints` ADD `class` varchar(255) NOT NULL;
ALTER TABLE `zz_widgets` DROP `class`, ADD `class` varchar(255) NOT NULL;

UPDATE `zz_widgets` SET `class` = 'Widgets\\Retro\\ModalWidget' WHERE `more_link_type` = 'popup';
UPDATE `zz_widgets` SET `class` = 'Widgets\\Retro\\LinkWidget' WHERE `more_link_type` = 'link';
UPDATE `zz_widgets` SET `class` = 'Widgets\\Retro\\StatsWidget' WHERE `more_link_type` = 'javascript';
UPDATE `zz_widgets` SET `class` = 'Widgets\\Retro\\StatsWidget' WHERE `type` = 'print';
UPDATE `zz_widgets` SET `class` = 'Widgets\\Retro\\StatsWidget' WHERE `class` = '';
UPDATE `zz_widgets` SET `more_link` = `php_include` WHERE `more_link` = '';
UPDATE `zz_widgets` SET `class` = 'Widgets\\Retro\\ModalWidget' WHERE `name` = 'Stampa calendario';

UPDATE `zz_widgets` SET `more_link` = REPLACE(`more_link`, 'plugins/', 'modules/');

ALTER TABLE `zz_widgets` DROP `print_link`, DROP `more_link_type`, DROP `php_include`;

UPDATE `zz_widgets` SET `more_link` = REPLACE(`more_link`, './', '/');

UPDATE `zz_widgets` SET `class` = 'Modules\\Dashboard\\NotificheWidget', `more_link` = '' WHERE `zz_widgets`.`name` = 'Note interne';

-- Aggiornamento stampe
UPDATE `zz_prints` SET `class` = 'Prints\\Retro\\Manager';
