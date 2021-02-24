-- Rimozione campo formattabile su "Causale" e "Sede destinazione" dei Ddt
UPDATE `zz_views` INNER JOIN `zz_modules` ON `zz_views`.`id_module` = `zz_modules`.`id` SET `zz_views`.`format` = 0 WHERE `zz_views`.`name` IN('Causale', 'Sede partenza') AND `zz_modules`.`name` IN('Ddt di vendita', 'Ddt di acquisto');

-- Aggiunta colonna reversed
ALTER TABLE `dt_causalet` ADD `reversed` TINYINT(1) NOT NULL AFTER `is_importabile`;
UPDATE `dt_causalet` SET `reversed`=1 WHERE `descrizione`='Reso';

-- Ottimizzazione per ricerca articoli da ajax select
ALTER TABLE `mg_movimenti` ADD INDEX(`idarticolo`);

-- Aggiunta possibilità di scegliere uno stato dopo la firma anche se non ha il flag completato
UPDATE `zz_settings` SET `tipo`='query=SELECT idstatointervento AS id, descrizione AS text FROM in_statiintervento' WHERE `nome`='Stato dell''attività dopo la firma';

-- Aggiunto filtro N3.% nella scelta aliquota per le dichiarazioni d'intento
UPDATE `zz_settings` SET `tipo` = 'query=SELECT id, descrizione FROM `co_iva` WHERE codice_natura_fe LIKE ''N3.%'' AND deleted_at IS NULL ORDER BY descrizione ASC' WHERE `zz_settings`.`nome` = 'Iva per lettere d''intento';


-- Aggiunte descrizioni aliquote IVA con codice natura 2.1
UPDATE `co_iva` SET `descrizione`='Art.7 bis DPR 633/1972 (cessione di beni extra-UE)' WHERE `descrizione`='Non soggetta ad IVA ai sensi degli artt. Da 7 a 7-septies del DPR 633/72' AND `codice_natura_fe`='N2.1';
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.7 ter  DPR 633/1972 prestazione servizi UE (vendite)', '0.00', '0.00', '1', NULL, 'N2.1', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.7 ter  DPR 633/1972 prestazione servizi extra-UE', '0.00', '0.00', '1', NULL, 'N2.1', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.7 quater DPR 633/1972 prestazione servizi UE (vendite)', '0.00', '0.00', '1', NULL, 'N2.1', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.7 quater DPR 633/1972 prestazione servizi extra-UE', '0.00', '0.00', '1', NULL, 'N2.1', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.7 quinquies DPR 633/1972 (prestazione servizi)', '0.00', '0.00', '1', NULL, 'N2.1', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.7 sexies, septies DPR 633/1972 (prestazione servizi)', '0.00', '0.00', '1', NULL, 'N2.1', NULL, NULL, 'I', '1');

-- Aggiunte descrizioni aliquote IVA con codice natura 2.2
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Fuori campo Iva art. 2 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Fuori campo Iva art. 3 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Fuori campo Iva art. 4 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Fuori campo Iva art. 5 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art. 38 c.5 DL  331/1993', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.17 c.3 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.19 c.3 lett. b DPR 633/1972', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art. 50 bis c.4 DL 331/1993', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.74 cc.1 e 2 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.19 c.3 lett. e DPR 633/1972', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.13 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art. 27 c.1 e 2 DL 98/2011 (contribuenti minimi)', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.1 c.54-89 L. 190/2014 e succ. modifiche (regime forfettario)', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.26 c.3 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'DM 9/4/1993', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.26 bis L.196/1997', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.8 c.35 L. 67/1988', '0.00', '0.00', '1', NULL, 'N2.2', NULL, NULL, 'I', '1');

-- Aggiunte descrizioni aliquote IVA con codice natura 3.()
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.8 c.1 lett.a DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.1', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.8 c.1 lett.b DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.1', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Non imp. art. 8 c.1 lett. b-bis  DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.1', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Non imp. art.9 c.2 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.1', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Non imp.art.72 c.1 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.1', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Non imp. art.50 bis c.4 lett. g DL 331/93', '0.00', '0.00', '1', NULL, 'N3.1', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Non imp. art.14 legge n. 49/1987', '0.00', '0.00', '1', NULL, 'N3.1', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Non imp. art.50 bis c.4 lett. f DL 331/93', '0.00', '0.00', '1', NULL, 'N3.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Non imp. art.41 DL 331/93', '0.00', '0.00', '1', NULL, 'N3.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Non imp. art.58 c.1 DL 331/93', '0.00', '0.00', '1', NULL, 'N3.2', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art. 8 bis DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.4', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Non imp. art. 8 bis c.2 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.4', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Non imp. art. 8 c.1 lett. c DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.5', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art.9 c.1 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.6', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Non imp. art.72 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.6', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art. 71 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.6', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Art. 2 c. 2, n. 4 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.6', NULL, NULL, 'I', '1');
INSERT INTO `co_iva` (`id`, `descrizione`, `percentuale`, `indetraibile`, `esente`, `dicitura`, `codice_natura_fe`, `deleted_at`, `codice`, `esigibilita`, `default`) VALUES (NULL, 'Non imp. art.38 quater c.1 DPR 633/1972', '0.00', '0.00', '1', NULL, 'N3.6', NULL, NULL, 'I', '1');

-- Aggiunto ckeditor Condizioni generali di fornitura in impostazioni preventivi
INSERT INTO `zz_settings` (`id`, `nome`, `valore`, `tipo`, `editable`, `sezione`, `order`, `help`) VALUES (NULL, 'Condizioni generali di fornitura', '', 'ckeditor', '1', 'Preventivi', NULL, NULL);

-- Aggiunta colonna condizioni_fornitura in co_preventivi
ALTER TABLE `co_preventivi` ADD `condizioni_fornitura` TEXT NOT NULL AFTER `numero_revision`;

-- Aggiunta colonna totale in modelli prima nota
ALTER TABLE `co_movimenti_modelli` ADD `totale` DECIMAL(15,6) NOT NULL AFTER `idconto`;

-- Aggiunto colonna garanzie in co_preventivi
ALTER TABLE `co_preventivi` ADD `garanzia` TEXT NOT NULL AFTER `condizioni_fornitura`;

-- Modificata lunghezza campo Partita iva
ALTER TABLE `an_anagrafiche` CHANGE `piva` `piva` VARCHAR(16) NOT NULL;

-- Aggiunta stampa bilancio
INSERT INTO `zz_prints` (`id`, `id_module`, `is_record`, `name`, `title`, `filename`, `directory`, `previous`, `options`, `icon`, `version`, `compatibility`, `order`, `predefined`, `default`, `enabled`) VALUES (NULL, (SELECT `id` FROM `zz_modules` WHERE name='Piano dei conti'), '1', 'Bilancio', 'Bilancio', 'Bilancio', 'bilancio', '', '', 'fa fa-print', '', '', '0', '0', '1', '1');

-- Aggiunta flag notifica cliente e tecnici in in_statiintervento
ALTER TABLE `in_statiintervento` ADD `notifica_cliente` TINYINT NOT NULL AFTER `notifica`, ADD `notifica_tecnici` TINYINT NOT NULL AFTER `notifica_cliente`;

-- Api creazione anagrafica da app
INSERT INTO `zz_api_resources` (`id`, `version`, `type`, `resource`, `class`, `enabled`) VALUES (NULL, 'app-v1', 'create', 'cliente', 'API\\App\\v1\\Clienti', '1'), (NULL, 'app-v1', 'update', 'cliente', 'API\\App\\v1\\Clienti', '1'), (NULL, 'app-v1', 'delete', 'cliente', 'API\\App\\v1\\Clienti', '1');

-- Aggiunto flag per il pagamento della ritenuta nelle fatture passive
ALTER TABLE `co_documenti` ADD `is_ritenuta_pagata` BOOLEAN NOT NULL AFTER `id_ricevuta_principale`;

-- Modificato options modulo scadenzario
UPDATE `zz_modules` SET `options` = 'SELECT |select| FROM `co_scadenziario`
LEFT JOIN `co_documenti` ON `co_scadenziario`.`iddocumento` = `co_documenti`.`id`
LEFT JOIN `an_anagrafiche` ON `co_documenti`.`idanagrafica` = `an_anagrafiche`.`idanagrafica`
LEFT JOIN `co_pagamenti` ON `co_documenti`.`idpagamento` = `co_pagamenti`.`id`
LEFT JOIN `co_tipidocumento` ON `co_documenti`.`idtipodocumento` = `co_tipidocumento`.`id`
LEFT JOIN `co_statidocumento` ON `co_documenti`.`idstatodocumento` = `co_statidocumento`.`id`
WHERE 1=1 AND
(`co_statidocumento`.`descrizione` IS NULL OR `co_statidocumento`.`descrizione` IN(''Emessa'',''Parzialmente pagato'',''Pagato''))
HAVING 2=2
ORDER BY `scadenza` ASC' WHERE `zz_modules`.`name`='Scadenzario';

-- Modificato nome segmento
UPDATE `zz_segments` SET `name` = 'Scadenzario completo per periodo' WHERE `zz_segments`.`name` = 'Scadenzario completo';

-- Fix stampe predefinite Preventivi e Contratti
UPDATE `zz_prints` SET `predefined` = '0' WHERE `zz_prints`.`id_module` = (SELECT `id` FROM `zz_modules` WHERE `name` = 'Preventivi') AND `zz_prints`.`name` = 'Consuntivo preventivo';
UPDATE `zz_prints` SET `predefined` = '0' WHERE `zz_prints`.`id_module` = (SELECT `id` FROM `zz_modules` WHERE `name` = 'Contratti') AND `zz_prints`.`name` = 'Consuntivo contratto';
UPDATE `zz_prints` SET `predefined` = '0' WHERE `zz_prints`.`id_module` = (SELECT `id` FROM `zz_modules` WHERE `name` = 'Contratti') AND `zz_prints`.`name` = 'Ordine di servizio';

-- Eliminata colonna idsede_controparte e rinominata idsede_azienda in idsede
ALTER TABLE `mg_movimenti` CHANGE `idsede_azienda` `idsede` INT(11) NOT NULL;
ALTER TABLE `mg_movimenti` DROP `idsede_controparte`;
UPDATE `zz_modules` SET `options` = 'SELECT |select| FROM `mg_movimenti` JOIN `mg_articoli` ON `mg_articoli`.id = `mg_movimenti`.`idarticolo` LEFT JOIN `an_sedi` ON `mg_movimenti`.`idsede` = `an_sedi`.`id` WHERE 1=1 HAVING 2=2 ORDER BY mg_movimenti.data DESC, mg_movimenti.created_at DESC' WHERE `zz_modules`.`name` = 'Movimenti';
UPDATE `zz_views` SET `query` = 'IF( mg_movimenti.idsede=0, ''Sede legale'', an_sedi.nomesede )' WHERE `zz_views`.`name` = 'Sede' 'Movimenti' AND `zz_views`.`id_module`= (SELECT `id` FROM `zz_modules` WHERE `name`='Movimenti');
UPDATE `zz_modules` SET `options` = 'SELECT |select| FROM `mg_articoli`
LEFT OUTER JOIN an_anagrafiche ON mg_articoli.id_fornitore = an_anagrafiche.idanagrafica
LEFT OUTER JOIN co_iva ON mg_articoli.idiva_vendita = co_iva.id
LEFT OUTER JOIN (
SELECT SUM(qta - qta_evasa) AS qta_impegnata, idarticolo FROM or_righe_ordini
INNER JOIN or_ordini ON or_righe_ordini.idordine = or_ordini.id
WHERE idstatoordine IN (SELECT id FROM or_statiordine WHERE completato = 0)
GROUP BY idarticolo
) ordini ON ordini.idarticolo = mg_articoli.id
LEFT OUTER JOIN (SELECT `idarticolo`, `idsede`, SUM(`qta`) AS `qta` FROM `mg_movimenti` WHERE `idsede` = |giacenze_sedi_idsede| GROUP BY `idarticolo`, `idsede`) movimenti ON `mg_articoli`.`id` = `movimenti`.`idarticolo`
WHERE 1=1 AND `mg_articoli`.`deleted_at` IS NULL HAVING 2=2 AND `Q.tà` > 0 ORDER BY `descrizione`' WHERE `zz_modules`.`name` = 'Giacenze sedi';

-- Rimozione campo idsede_azienda
UPDATE `zz_views` INNER JOIN `zz_modules` ON `zz_views`.`id_module` = `zz_modules`.`id` SET `zz_views`.`query` = 'IF( mg_movimenti.idsede=0, ''Sede legale'', an_sedi.nomesede )' WHERE `zz_views`.`name` = 'Sede' AND `zz_modules`.`name` = 'Movimenti';

-- Aggiunto metodo di pagamento PagoPA
INSERT INTO `fe_modalita_pagamento` (`codice`, `descrizione`) VALUES
('MP23','PagoPA');

INSERT INTO `co_pagamenti` (`id`, `descrizione`, `giorno`, `num_giorni`, `prc`, `codice_modalita_pagamento_fe`) VALUES
(NULL, 'PagoPA', '0', '1', '100', 'MP23');

-- Aggiunti referenti ai documenti
ALTER TABLE `or_ordini` ADD `idreferente` INT NULL DEFAULT NULL AFTER `idanagrafica`;
ALTER TABLE `co_documenti` ADD `idreferente` INT NULL DEFAULT NULL AFTER `idanagrafica`;
ALTER TABLE `dt_ddt` ADD `idreferente` INT NULL DEFAULT NULL AFTER `idanagrafica`;

-- Colorazione riga fatture di acquisto con stesso numero e fornitore
UPDATE `zz_modules` SET `options` = 'SELECT |select| FROM `co_documenti`
LEFT JOIN `an_anagrafiche` ON `co_documenti`.`idanagrafica` = `an_anagrafiche`.`idanagrafica`
LEFT JOIN `co_tipidocumento` ON `co_documenti`.`idtipodocumento` = `co_tipidocumento`.`id`
LEFT JOIN `co_statidocumento` ON `co_documenti`.`idstatodocumento` = `co_statidocumento`.`id`
LEFT JOIN (
    SELECT `iddocumento`,
    SUM(`subtotale` - `sconto`) AS `totale_imponibile`,
    SUM(`subtotale` - `sconto` + `iva`) AS `totale`
    FROM `co_righe_documenti`
    GROUP BY `iddocumento`
) AS righe ON `co_documenti`.`id` = `righe`.`iddocumento`
LEFT JOIN (
    SELECT COUNT(`d`.`id`) AS `conteggio`,
        IF(`d`.`numero_esterno`='''', `d`.`numero`, `d`.`numero_esterno`) AS `numero_documento`
    FROM `co_documenti` AS `d`
    LEFT JOIN `co_tipidocumento` AS `d_tipo` ON `d`.`idtipodocumento` = `d_tipo`.`id`
    WHERE 1=1
        AND `d_tipo`.`dir` = ''uscita''
        AND (''|period_start|'' <= `d`.`data` AND ''|period_end|'' >= `d`.`data` OR ''|period_start|'' <= `d`.`data_competenza` AND ''|period_end|'' >= `d`.`data_competenza`)
        GROUP BY `numero_documento`, `d`.`idanagrafica`
) AS `d` ON `d`.`numero_documento` = IF(`co_documenti`.`numero_esterno`='''', `co_documenti`.`numero`, `co_documenti`.`numero_esterno`)
WHERE 1=1 AND `dir` = ''uscita'' |segment(`co_documenti`.`id_segment`)||date_period(custom, ''|period_start|'' <= `co_documenti`.`data` AND ''|period_end|'' >= `co_documenti`.`data`, ''|period_start|'' <= `co_documenti`.`data_competenza` AND ''|period_end|'' >= `co_documenti`.`data_competenza` )|
HAVING 2=2
ORDER BY `co_documenti`.`data` DESC, CAST(IF(`co_documenti`.`numero` = '''', `co_documenti`.`numero_esterno`, `co_documenti`.`numero`) AS UNSIGNED) DESC' WHERE `zz_modules`.`name`='Fatture di acquisto';


INSERT INTO `zz_views` (`id`, `id_module`, `name`, `query`, `order`, `search`, `slow`, `format`, `search_inside`, `order_by`, `visible`, `summable`, `default`) VALUES (NULL, (SELECT `zz_modules`.`id` FROM `zz_modules` WHERE `zz_modules`.`name`='Fatture di acquisto'), '_bg_', 'IF(`d`.`conteggio`>1, ''red'', '''')', '1', '0', '0', '0', '', '', '0', '0', '0');

INSERT INTO `zz_group_view` (`id_gruppo`, `id_vista`) (SELECT `zz_groups`.`id`, `zz_views`.`id` FROM `zz_groups`, `zz_views` INNER JOIN `zz_modules` ON `zz_views`.`id_module` = `zz_modules`.`id` WHERE `zz_modules`.`name` = 'Fatture di acquisto' AND `zz_views`.`name` = '_bg_');


INSERT INTO `zz_settings` (`id`, `nome`, `valore`, `tipo`, `editable`, `sezione`) VALUES (NULL, 'Descrizione fattura pianificata', 'Canone {rata} del contratto numero {numero}', 'text', '1', 'Fatturazione');

ALTER TABLE `co_righe_contratti` ADD `idpianificazione` INT NULL DEFAULT NULL AFTER `idarticolo`;
INSERT INTO `zz_prints` (`id`, `id_module`, `is_record`, `name`, `title`, `filename`, `directory`, `previous`, `options`, `icon`, `version`, `compatibility`, `order`, `predefined`, `default`, `enabled`) VALUES (NULL, (SELECT `id` FROM `zz_modules` WHERE `name`='Fatture di vendita'), '1', 'Fattura elettronica di vendita', 'Fattura elettronica di vendita', 'Fattura elettronica {numero} del {data}', 'fatture_elettroniche', 'iddocumento', '{\"hide-header\": true, \"hide-footer\": true}', 'fa fa-print', '', '', '0', '1', '1', '1');

INSERT INTO `zz_prints` (`id`, `id_module`, `is_record`, `name`, `title`, `filename`, `directory`, `previous`, `options`, `icon`, `version`, `compatibility`, `order`, `predefined`, `default`, `enabled`) VALUES (NULL, (SELECT `id` FROM `zz_modules` WHERE `name`='Fatture di acquisto'), '1', 'Fattura elettronica di acquisto', 'Fattura elettronica di acquisto', 'Fattura elettronica {numero} del {data}', 'fatture_elettroniche', 'iddocumento', '{\"hide-header\": true, \"hide-footer\": true}', 'fa fa-print', '', '', '0', '1', '1', '1');

INSERT INTO `zz_settings` (`id`, `nome`, `valore`, `tipo`, `editable`, `sezione`, `order`, `help`) VALUES
(NULL, 'Numero massimo di tentativi', '10', 'integer', 1, 'Newsletter', 1, 'Numero massimo di tentativi da effettuare per cercare di inviare una mail');

-- Aggiunta colonna codice commessa convenzione
ALTER TABLE `or_ordini` ADD `codice_commessa` VARCHAR(100) NULL AFTER `updated_at`;

-- Copiato in or_ordini id_documento_fe in numero_cliente dove è presente
UPDATE `or_ordini` SET `numero_cliente`= `id_documento_fe` WHERE `id_documento_fe`!='' AND `id_documento_fe` IS NOT NULL;

-- Fix nome file con il tipo documento di vendita
UPDATE `zz_prints` SET `filename` = '{tipo_documento} num. {numero} del {data}' WHERE `zz_prints`.`name` = 'Fattura di vendita'; 
-- Risorsa API per sincronizzazione rapida di un singolo intervento
INSERT INTO `zz_api_resources` (`id`, `version`, `type`, `resource`, `class`, `enabled`) VALUES
(NULL, 'app-v1', 'update', 'intervento-flash', 'API\\App\\v1\\Flash\\Intervento', '1');
