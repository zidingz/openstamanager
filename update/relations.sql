-- OGNI TABELLA IMPOSTA LE RELAZIONI PER LE PROPRIE PRIMARY KEYS

-- Nomi differenti per idstato: co_contratti (idstato), co_preventivi (idstato), dt_ddt (idstatoddt), co_documenti (idstatodocumento), in_interventi (idstatointervento), or_ordini (idstatoordine)
-- Duplicazione: predefined - default - can_delete
-- Riferimenti non chiari: idimpianti, idmastrino

-- Cambiamenti nelle chiavi primarie:
-- an_anagrafiche - idanagrafica -> id
-- an_tipianagrafiche - idtipoanagrafica -> id [fatto]
-- in_statiintervento - idstatointervento -> id [fatto]
-- in_tipiintervento - idtipointervento -> id
-- zz_settings - idimpostazione -> id

-- ATTENZIONE: tutti i documenti per cui l'anagrafica principale non esiste vengono rimossi

-- Foreign keys an_anagrafiche
ALTER TABLE `an_tipianagrafiche_anagrafiche` DROP FOREIGN KEY `an_tipianagrafiche_anagrafiche_ibfk_2`;
ALTER TABLE `in_interventi` DROP FOREIGN KEY `in_interventi_ibfk_1`;
ALTER TABLE `in_interventi_tecnici` DROP FOREIGN KEY `in_interventi_tecnici_ibfk_2`;

ALTER TABLE `an_anagrafiche` CHANGE `idanagrafica` `id` int(11) NOT NULL AUTO_INCREMENT;

DELETE FROM `an_anagrafiche_agenti` WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`) OR `idagente` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `an_anagrafiche_agenti` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE,
  ADD FOREIGN KEY (`idagente`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE;

DELETE FROM `an_referenti` WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `an_referenti` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE;

DELETE FROM `an_sedi` WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `an_sedi` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE;

DELETE FROM `an_tipianagrafiche_anagrafiche` WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `an_tipianagrafiche_anagrafiche` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_contratti` CHANGE `idagente` `idagente` int(11);
DELETE FROM `co_contratti` WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`);
UPDATE `co_contratti` SET `idagente` = NULL WHERE `idagente` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `co_contratti` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE,
  ADD FOREIGN KEY (`idagente`) REFERENCES `an_anagrafiche`(`id`) ON DELETE SET NULL;

ALTER TABLE `co_documenti` CHANGE `idagente` `idagente` int(11), CHANGE `idvettore` `idvettore` int(11);
DELETE FROM `co_documenti` WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`);
UPDATE `co_documenti` SET `idagente` = NULL WHERE `idagente` NOT IN (SELECT `id` FROM `an_anagrafiche`);
UPDATE `co_documenti` SET `idvettore` = NULL WHERE `idvettore` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE,
  ADD FOREIGN KEY (`idagente`) REFERENCES `an_anagrafiche`(`id`) ON DELETE SET NULL,
  ADD FOREIGN KEY (`idvettore`) REFERENCES `an_anagrafiche`(`id`) ON DELETE SET NULL;

DELETE FROM `co_movimenti` WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `co_movimenti` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_preventivi` CHANGE `idagente` `idagente` int(11);
DELETE FROM `co_preventivi` WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`);
UPDATE `co_preventivi` SET `idagente` = NULL WHERE `idagente` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `co_preventivi` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE,
  ADD FOREIGN KEY (`idagente`) REFERENCES `an_anagrafiche`(`id`) ON DELETE SET NULL;

ALTER TABLE `dt_ddt` CHANGE `idagente` `idagente` int(11), CHANGE `idvettore` `idvettore` int(11);
DELETE FROM `dt_ddt` WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`);
UPDATE `dt_ddt` SET `idagente` = NULL WHERE `idagente` NOT IN (SELECT `id` FROM `an_anagrafiche`);
UPDATE `dt_ddt` SET `idvettore` = NULL WHERE `idvettore` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE,
  ADD FOREIGN KEY (`idagente`) REFERENCES `an_anagrafiche`(`id`) ON DELETE SET NULL,
  ADD FOREIGN KEY (`idvettore`) REFERENCES `an_anagrafiche`(`id`) ON DELETE SET NULL;

ALTER TABLE `in_interventi` CHANGE `idclientefinale` `idclientefinale` int(11);
DELETE FROM `in_interventi` WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`);
UPDATE `in_interventi` SET `idclientefinale` = NULL WHERE `idclientefinale` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `in_interventi` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE,
  ADD FOREIGN KEY (`idclientefinale`) REFERENCES `an_anagrafiche`(`id`) ON DELETE SET NULL;

ALTER TABLE `my_impianti` CHANGE `idtecnico` `idtecnico` int(11);
DELETE FROM `my_impianti` WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`);
UPDATE `my_impianti` SET `idtecnico` = NULL WHERE `idtecnico` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `my_impianti` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE,
  ADD FOREIGN KEY (`idtecnico`) REFERENCES `an_anagrafiche`(`id`) ON DELETE SET NULL;

ALTER TABLE `or_ordini` CHANGE `idagente` `idagente` int(11);
DELETE FROM `or_ordini` WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`);
UPDATE `or_ordini` SET `idagente` = NULL WHERE `idagente` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `or_ordini` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE,
  ADD FOREIGN KEY (`idagente`) REFERENCES `an_anagrafiche`(`id`) ON DELETE SET NULL;

ALTER TABLE `zz_users` CHANGE `idanagrafica` `idanagrafica` int(11);
UPDATE `zz_users` SET `idanagrafica` = NULL WHERE `idanagrafica` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `zz_users` ADD FOREIGN KEY (`idanagrafica`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_documenti` CHANGE `idtecnico` `idtecnico` int(11), CHANGE `idagente` `idagente` int(11);
UPDATE `co_righe_documenti` SET `idtecnico` = NULL WHERE `idtecnico` NOT IN (SELECT `id` FROM `an_anagrafiche`);
UPDATE `co_righe_documenti` SET `idagente` = NULL WHERE `idagente` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`idtecnico`) REFERENCES `an_anagrafiche`(`id`) ON DELETE SET NULL,
  ADD FOREIGN KEY (`idagente`) REFERENCES `an_anagrafiche`(`id`) ON DELETE SET NULL;

DELETE FROM `in_interventi_tecnici` WHERE `idtecnico` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `in_interventi_tecnici` ADD FOREIGN KEY (`idtecnico`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE;

DELETE FROM `in_tariffe` WHERE `idtecnico` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `in_tariffe` ADD FOREIGN KEY (`idtecnico`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE;

DELETE FROM `dt_automezzi_tecnici` WHERE `idtecnico` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `dt_automezzi_tecnici` ADD FOREIGN KEY (`idtecnico`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE;

ALTER TABLE `an_anagrafiche` CHANGE `idagente` `idagente` int(11);
UPDATE `an_anagrafiche` `t1` LEFT JOIN `an_anagrafiche` `t2` ON `t1`.`idagente` = `t2`.`id` SET `t1`.`idagente` = NULL WHERE `t2`.`id` IS NULL;
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idagente`) REFERENCES `an_anagrafiche`(`id`) ON DELETE CASCADE;

ALTER TABLE `or_righe_ordini` CHANGE `idagente` `idagente` int(11);
UPDATE `or_righe_ordini` SET `idagente` = NULL WHERE `idagente` NOT IN (SELECT `id` FROM `an_anagrafiche`);
ALTER TABLE `or_righe_ordini` ADD FOREIGN KEY (`idagente`) REFERENCES `an_anagrafiche`(`id`) ON DELETE SET NULL;

-- Foreign keys an_referenti
ALTER TABLE `in_interventi` CHANGE `idreferente` `idreferente` int(11);
UPDATE `in_interventi` SET `idreferente` = NULL WHERE `idreferente` NOT IN (SELECT `id` FROM `an_referenti`);
ALTER TABLE `in_interventi` ADD FOREIGN KEY (`idreferente`) REFERENCES `an_referenti`(`id`) ON DELETE SET NULL;

ALTER TABLE `co_contratti` CHANGE `idreferente` `idreferente` int(11);
UPDATE `co_contratti` SET `idreferente` = NULL WHERE `idreferente` NOT IN (SELECT `id` FROM `an_referenti`);
ALTER TABLE `co_contratti` ADD FOREIGN KEY (`idreferente`) REFERENCES `an_referenti`(`id`) ON DELETE SET NULL;

ALTER TABLE `co_preventivi` CHANGE `idreferente` `idreferente` int(11);
UPDATE `co_preventivi` SET `idreferente` = NULL WHERE `idreferente` NOT IN (SELECT `id` FROM `an_referenti`);
ALTER TABLE `co_preventivi` ADD FOREIGN KEY (`idreferente`) REFERENCES `an_referenti`(`id`) ON DELETE SET NULL;

-- Foreign keys an_relazioni
ALTER TABLE `an_anagrafiche` CHANGE `idrelazione` `idrelazione` int(11);
UPDATE `an_anagrafiche` SET `idrelazione` = NULL WHERE `idrelazione` NOT IN (SELECT `id` FROM `an_relazioni`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idrelazione`) REFERENCES `an_relazioni`(`id`) ON DELETE SET NULL;

-- Foreign keys an_sedi
ALTER TABLE `co_documenti` CHANGE `idsede` `idsede` int(11);
UPDATE `co_documenti` SET `idsede` = NULL WHERE `idsede` NOT IN (SELECT `id` FROM `an_sedi`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idsede`) REFERENCES `an_sedi`(`id`) ON DELETE SET NULL;

ALTER TABLE `dt_ddt` CHANGE `idsede` `idsede` int(11);
UPDATE `dt_ddt` SET `idsede` = NULL WHERE `idsede` NOT IN (SELECT `id` FROM `an_sedi`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idsede`) REFERENCES `an_sedi`(`id`) ON DELETE SET NULL;

ALTER TABLE `co_righe_contratti` CHANGE `idsede` `idsede` int(11);
UPDATE `co_righe_contratti` SET `idsede` = NULL WHERE `idsede` NOT IN (SELECT `id` FROM `an_sedi`);
ALTER TABLE `co_righe_contratti` ADD FOREIGN KEY (`idsede`) REFERENCES `an_sedi`(`id`) ON DELETE SET NULL;

ALTER TABLE `or_ordini` CHANGE `idsede` `idsede` int(11);
UPDATE `or_ordini` SET `idsede` = NULL WHERE `idsede` NOT IN (SELECT `id` FROM `an_sedi`);
ALTER TABLE `or_ordini` ADD FOREIGN KEY (`idsede`) REFERENCES `an_sedi`(`id`) ON DELETE SET NULL;

ALTER TABLE `in_interventi` CHANGE `idsede` `idsede` int(11);
UPDATE `in_interventi` SET `idsede` = NULL WHERE `idsede` NOT IN (SELECT `id` FROM `an_sedi`);
ALTER TABLE `in_interventi` ADD FOREIGN KEY (`idsede`) REFERENCES `an_sedi`(`id`) ON DELETE SET NULL;

ALTER TABLE `my_impianti` CHANGE `idsede` `idsede` int(11);
UPDATE `my_impianti` SET `idsede` = NULL WHERE `idsede` NOT IN (SELECT `id` FROM `an_sedi`);
ALTER TABLE `my_impianti` ADD FOREIGN KEY (`idsede`) REFERENCES `an_sedi`(`id`) ON DELETE SET NULL;

ALTER TABLE `an_referenti` CHANGE `idsede` `idsede` int(11);
UPDATE `an_referenti` SET `idsede` = NULL WHERE `idsede` NOT IN (SELECT `id` FROM `an_sedi`);
ALTER TABLE `an_referenti` ADD FOREIGN KEY (`idsede`) REFERENCES `an_sedi`(`id`) ON DELETE SET NULL;

ALTER TABLE `co_contratti` CHANGE `idsede` `idsede` int(11);
UPDATE `co_contratti` SET `idsede` = NULL WHERE `idsede` NOT IN (SELECT `id` FROM `an_sedi`);
ALTER TABLE `co_contratti` ADD FOREIGN KEY (`idsede`) REFERENCES `an_sedi`(`id`) ON DELETE SET NULL;

ALTER TABLE `an_anagrafiche` CHANGE `idsede_fatturazione` `idsede_fatturazione` int(11);
UPDATE `an_anagrafiche` SET `idsede_fatturazione` = NULL WHERE `idsede_fatturazione` NOT IN (SELECT `id` FROM `an_sedi`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idsede_fatturazione`) REFERENCES `an_sedi`(`id`) ON DELETE SET NULL;

-- Foreign keys an_tipianagrafiche
ALTER TABLE `an_tipianagrafiche_anagrafiche` DROP FOREIGN KEY `an_tipianagrafiche_anagrafiche_ibfk_1`;

ALTER TABLE `an_tipianagrafiche` CHANGE `idtipoanagrafica` `id` int(11) NOT NULL AUTO_INCREMENT;

DELETE FROM `an_tipianagrafiche_anagrafiche` WHERE `idtipoanagrafica` NOT IN (SELECT `id` FROM `an_tipianagrafiche`);
ALTER TABLE `an_tipianagrafiche_anagrafiche` ADD FOREIGN KEY (`idtipoanagrafica`) REFERENCES `an_tipianagrafiche`(`id`) ON DELETE CASCADE;

ALTER TABLE `zz_users` CHANGE `idtipoanagrafica` `idtipoanagrafica` int(11);
UPDATE `zz_users` SET `idtipoanagrafica` = NULL WHERE `idtipoanagrafica` NOT IN (SELECT `id` FROM `an_tipianagrafiche`);
ALTER TABLE `zz_users` ADD FOREIGN KEY (`idtipoanagrafica`) REFERENCES `an_tipianagrafiche`(`id`) ON DELETE SET NULL;

-- Foreign keys an_zone
ALTER TABLE `an_anagrafiche` CHANGE `idzona` `idzona` int(11);
UPDATE `an_anagrafiche` SET `idzona` = NULL WHERE `idzona` NOT IN (SELECT `id` FROM `an_zone`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idzona`) REFERENCES `an_zone`(`id`) ON DELETE SET NULL;

ALTER TABLE `co_ordiniservizio_pianificazionefatture` CHANGE `idzona` `idzona` int(11);
UPDATE `co_ordiniservizio_pianificazionefatture` SET `idzona` = NULL WHERE `idzona` NOT IN (SELECT `id` FROM `an_zone`);
ALTER TABLE `co_ordiniservizio_pianificazionefatture` ADD FOREIGN KEY (`idzona`) REFERENCES `an_zone`(`id`) ON DELETE SET NULL;

ALTER TABLE `an_sedi` CHANGE `idzona` `idzona` int(11);
UPDATE `an_sedi` SET `idzona` = NULL WHERE `idzona` NOT IN (SELECT `id` FROM `an_zone`);
ALTER TABLE `an_sedi` ADD FOREIGN KEY (`idzona`) REFERENCES `an_zone`(`id`) ON DELETE SET NULL;

-- TODO: da qui in poi individuare la necessità di NULL e la relazione ON DELETE. Bisogna anche aggiungere la maggior parte degli UPDATE/DELETE per permettere la corretta creazione delle chiavi.

-- Foreign keys in_interventi
UPDATE `co_righe_documenti` SET `idintervento` = NULL WHERE `idintervento` NOT IN (SELECT `id` FROM `in_interventi`);
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`idintervento`) REFERENCES `in_interventi`(`id`) ON DELETE CASCADE;

-- Foreign keys in_statiintervento
ALTER TABLE `in_statiintervento` CHANGE `idstatointervento` `id` VARCHAR(10) NOT NULL;

ALTER TABLE `in_interventi` ADD FOREIGN KEY (`idstatointervento`) REFERENCES `in_statiintervento`(`id`) ON DELETE CASCADE;

-- Foreign keys in_tipiintervento
ALTER TABLE `in_interventi` DROP FOREIGN KEY `in_interventi_ibfk_2`;

ALTER TABLE `in_tipiintervento` CHANGE `idtipointervento` `id` VARCHAR(25) NOT NULL;

ALTER TABLE `an_anagrafiche` CHANGE `idtipointervento_default` `idtipointervento_default` VARCHAR(25);
UPDATE `an_anagrafiche` SET `idtipointervento_default` = NULL WHERE `idtipointervento_default` NOT IN (SELECT `id` FROM `in_tipiintervento`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idtipointervento_default`) REFERENCES `in_tipiintervento`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_contratti` CHANGE `idtipointervento` `idtipointervento` VARCHAR(25);
UPDATE `co_contratti` SET `idtipointervento` = NULL WHERE `idtipointervento` NOT IN (SELECT `id` FROM `in_tipiintervento`);
ALTER TABLE `co_contratti` ADD FOREIGN KEY (`idtipointervento`) REFERENCES `in_tipiintervento`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_preventivi` CHANGE `idtipointervento` `idtipointervento` VARCHAR(25);
UPDATE `co_preventivi` SET `idtipointervento` = NULL WHERE `idtipointervento` NOT IN (SELECT `id` FROM `in_tipiintervento`);
ALTER TABLE `co_preventivi` ADD FOREIGN KEY (`idtipointervento`) REFERENCES `in_tipiintervento`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_contratti` CHANGE `idtipointervento` `idtipointervento` VARCHAR(25);
UPDATE `co_righe_contratti` SET `idtipointervento` = NULL WHERE `idtipointervento` NOT IN (SELECT `id` FROM `in_tipiintervento`);
ALTER TABLE `co_righe_contratti` ADD FOREIGN KEY (`idtipointervento`) REFERENCES `in_tipiintervento`(`id`) ON DELETE CASCADE;

ALTER TABLE `in_interventi` CHANGE `idtipointervento` `idtipointervento` VARCHAR(25);
UPDATE `in_interventi` SET `idtipointervento` = NULL WHERE `idtipointervento` NOT IN (SELECT `id` FROM `in_tipiintervento`);
ALTER TABLE `in_interventi` ADD FOREIGN KEY (`idtipointervento`) REFERENCES `in_tipiintervento`(`id`) ON DELETE CASCADE;

ALTER TABLE `in_interventi_tecnici` CHANGE `idtipointervento` `idtipointervento` VARCHAR(25);
UPDATE `in_interventi_tecnici` SET `idtipointervento` = NULL WHERE `idtipointervento` NOT IN (SELECT `id` FROM `in_tipiintervento`);
ALTER TABLE `in_interventi_tecnici` ADD FOREIGN KEY (`idtipointervento`) REFERENCES `in_tipiintervento`(`id`) ON DELETE CASCADE;

DELETE FROM `in_tariffe` WHERE `idtipointervento` NOT IN (SELECT `id` FROM `in_tipiintervento`);
ALTER TABLE `in_tariffe` ADD FOREIGN KEY (`idtipointervento`) REFERENCES `in_tipiintervento`(`id`) ON DELETE CASCADE;

DELETE FROM `co_contratti_tipiintervento` WHERE `idtipointervento` NOT IN (SELECT `id` FROM `in_tipiintervento`);
ALTER TABLE `co_contratti_tipiintervento` ADD FOREIGN KEY (`idtipointervento`) REFERENCES `in_tipiintervento`(`id`) ON DELETE CASCADE;

-- Foreign keys or_ordini
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`idordine`) REFERENCES `or_ordini`(`id`) ON DELETE CASCADE;
ALTER TABLE `or_righe_ordini` ADD FOREIGN KEY (`idordine`) REFERENCES `or_ordini`(`id`) ON DELETE CASCADE;
ALTER TABLE `dt_righe_ddt` ADD FOREIGN KEY (`idordine`) REFERENCES `or_ordini`(`id`) ON DELETE CASCADE;

-- Foreign keys or_statiordine
ALTER TABLE `or_ordini` ADD FOREIGN KEY (`idstatoordine`) REFERENCES `or_statiordine`(`id`) ON DELETE CASCADE;

-- Foreign keys or_statiordine
ALTER TABLE `or_ordini` ADD FOREIGN KEY (`idtipoordine`) REFERENCES `or_tipiordine`(`id`) ON DELETE CASCADE;

-- Foreign keys my_impianti
-- DA MIGLIORARE idimpianti di co_righe_contratti
ALTER TABLE `co_righe_contratti_articoli` ADD FOREIGN KEY (`idimpianto`) REFERENCES `my_impianti`(`id`) ON DELETE CASCADE;


-- TODO: da qui in poi le chiavi dovrebbero crearsi senza problemi, ma bisogna sistemare NULL e ON DELETE.

-- Foreign keys dt_aspettobeni
ALTER TABLE `dt_aspettobeni` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `co_documenti` CHANGE `idaspettobeni` `idaspettobeni` int(11);
UPDATE `co_documenti` SET `idaspettobeni` = NULL WHERE `idaspettobeni` NOT IN (SELECT `id` FROM `dt_aspettobeni`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idaspettobeni`) REFERENCES `dt_aspettobeni`(`id`) ON DELETE CASCADE;

ALTER TABLE `dt_ddt` CHANGE `idaspettobeni` `idaspettobeni` int(11);
UPDATE `dt_ddt` SET `idaspettobeni` = NULL WHERE `idaspettobeni` NOT IN (SELECT `id` FROM `dt_aspettobeni`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idaspettobeni`) REFERENCES `dt_aspettobeni`(`id`) ON DELETE CASCADE;

-- Foreign keys dt_automezzi
ALTER TABLE `co_righe_documenti` CHANGE `idautomezzo` `idautomezzo` int(11);
UPDATE `co_righe_documenti` SET `idautomezzo` = NULL WHERE `idautomezzo` NOT IN (SELECT `id` FROM `dt_automezzi`);
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`idautomezzo`) REFERENCES `dt_automezzi`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_contratti_articoli` CHANGE `idautomezzo` `idautomezzo` int(11);
UPDATE `co_righe_contratti_articoli` SET `idautomezzo` = NULL WHERE `idautomezzo` NOT IN (SELECT `id` FROM `dt_automezzi`);
ALTER TABLE `co_righe_contratti_articoli` ADD FOREIGN KEY (`idautomezzo`) REFERENCES `dt_automezzi`(`id`) ON DELETE CASCADE;

ALTER TABLE `dt_automezzi_tecnici` CHANGE `idautomezzo` `idautomezzo` int(11);
UPDATE `dt_automezzi_tecnici` SET `idautomezzo` = NULL WHERE `idautomezzo` NOT IN (SELECT `id` FROM `dt_automezzi`);
ALTER TABLE `dt_automezzi_tecnici` ADD FOREIGN KEY (`idautomezzo`) REFERENCES `dt_automezzi`(`id`) ON DELETE CASCADE;

ALTER TABLE `mg_movimenti` CHANGE `idautomezzo` `idautomezzo` int(11);
UPDATE `mg_movimenti` SET `idautomezzo` = NULL WHERE `idautomezzo` NOT IN (SELECT `id` FROM `dt_automezzi`);
ALTER TABLE `mg_movimenti` ADD FOREIGN KEY (`idautomezzo`) REFERENCES `dt_automezzi`(`id`) ON DELETE CASCADE;

ALTER TABLE `in_interventi` CHANGE `idautomezzo` `idautomezzo` int(11);
UPDATE `in_interventi` SET `idautomezzo` = NULL WHERE `idautomezzo` NOT IN (SELECT `id` FROM `dt_automezzi`);
ALTER TABLE `in_interventi` ADD FOREIGN KEY (`idautomezzo`) REFERENCES `dt_automezzi`(`id`) ON DELETE CASCADE;

ALTER TABLE `mg_articoli_interventi` CHANGE `idautomezzo` `idautomezzo` int(11);
UPDATE `mg_articoli_interventi` SET `idautomezzo` = NULL WHERE `idautomezzo` NOT IN (SELECT `id` FROM `dt_automezzi`);
ALTER TABLE `mg_articoli_interventi` ADD FOREIGN KEY (`idautomezzo`) REFERENCES `dt_automezzi`(`id`) ON DELETE CASCADE;

ALTER TABLE `mg_articoli_automezzi` CHANGE `idautomezzo` `idautomezzo` int(11);
UPDATE `mg_articoli_automezzi` SET `idautomezzo` = NULL WHERE `idautomezzo` NOT IN (SELECT `id` FROM `dt_automezzi`);
ALTER TABLE `mg_articoli_automezzi` ADD FOREIGN KEY (`idautomezzo`) REFERENCES `dt_automezzi`(`id`) ON DELETE CASCADE;

-- Foreign keys dt_causalet
ALTER TABLE `dt_causalet` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dt_ddt` CHANGE `idcausalet` `idcausalet` int(11);
UPDATE `dt_ddt` SET `idcausalet` = NULL WHERE `idcausalet` NOT IN (SELECT `id` FROM `dt_causalet`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idcausalet`) REFERENCES `dt_causalet`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_documenti` CHANGE `idcausalet` `idcausalet` int(11);
UPDATE `co_documenti` SET `idcausalet` = NULL WHERE `idcausalet` NOT IN (SELECT `id` FROM `dt_causalet`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idcausalet`) REFERENCES `dt_causalet`(`id`) ON DELETE CASCADE;

-- Foreign keys dt_ddt
ALTER TABLE `co_righe_documenti` CHANGE `idddt` `idddt` int(11);
UPDATE `co_righe_documenti` SET `idddt` = NULL WHERE `idddt` NOT IN (SELECT `id` FROM `dt_ddt`);
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`idddt`) REFERENCES `dt_ddt`(`id`) ON DELETE CASCADE;

ALTER TABLE `mg_movimenti` CHANGE `idddt` `idddt` int(11);
UPDATE `mg_movimenti` SET `idddt` = NULL WHERE `idddt` NOT IN (SELECT `id` FROM `dt_ddt`);
ALTER TABLE `mg_movimenti` ADD FOREIGN KEY (`idddt`) REFERENCES `dt_ddt`(`id`) ON DELETE CASCADE;

ALTER TABLE `dt_righe_ddt` CHANGE `idddt` `idddt` int(11);
UPDATE `dt_righe_ddt` SET `idddt` = NULL WHERE `idddt` NOT IN (SELECT `id` FROM `dt_ddt`);
ALTER TABLE `dt_righe_ddt` ADD FOREIGN KEY (`idddt`) REFERENCES `dt_ddt`(`id`) ON DELETE CASCADE;

-- Foreign keys dt_porto
ALTER TABLE `dt_porto` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `co_preventivi` CHANGE `idporto` `idporto` int(11);
UPDATE `co_preventivi` SET `idporto` = NULL WHERE `idporto` NOT IN (SELECT `id` FROM `dt_porto`);
ALTER TABLE `co_preventivi` ADD FOREIGN KEY (`idporto`) REFERENCES `dt_porto`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_documenti` CHANGE `idporto` `idporto` int(11);
UPDATE `co_documenti` SET `idporto` = NULL WHERE `idporto` NOT IN (SELECT `id` FROM `dt_porto`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idporto`) REFERENCES `dt_porto`(`id`) ON DELETE CASCADE;

ALTER TABLE `dt_ddt` CHANGE `idporto` `idporto` int(11);
UPDATE `dt_ddt` SET `idporto` = NULL WHERE `idporto` NOT IN (SELECT `id` FROM `dt_porto`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idporto`) REFERENCES `dt_porto`(`id`) ON DELETE CASCADE;

-- Foreign keys dt_spedizione
ALTER TABLE `dt_spedizione` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dt_ddt` CHANGE `idspedizione` `idspedizione` int(11);
UPDATE `dt_ddt` SET `idspedizione` = NULL WHERE `idspedizione` NOT IN (SELECT `id` FROM `dt_spedizione`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idspedizione`) REFERENCES `dt_spedizione`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_documenti` CHANGE `idspedizione` `idspedizione` int(11);
UPDATE `co_documenti` SET `idspedizione` = NULL WHERE `idspedizione` NOT IN (SELECT `id` FROM `dt_spedizione`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idspedizione`) REFERENCES `dt_spedizione`(`id`) ON DELETE CASCADE;

-- Foreign keys dt_statiddt
ALTER TABLE `dt_statiddt` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dt_ddt` CHANGE `idstatoddt` `idstatoddt` int(11);
UPDATE `dt_ddt` SET `idstatoddt` = NULL WHERE `idstatoddt` NOT IN (SELECT `id` FROM `dt_statiddt`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idstatoddt`) REFERENCES `dt_statiddt`(`id`) ON DELETE CASCADE;

-- Foreign keys dt_tipiddt
ALTER TABLE `dt_tipiddt` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dt_ddt` CHANGE `idtipoddt` `idtipoddt` int(11);
UPDATE `dt_ddt` SET `idtipoddt` = NULL WHERE `idtipoddt` NOT IN (SELECT `id` FROM `dt_tipiddt`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idtipoddt`) REFERENCES `dt_tipiddt`(`id`) ON DELETE CASCADE;

-- Foreign keys mg_articoli
ALTER TABLE `mg_articoli_interventi` CHANGE `idarticolo` `idarticolo` int(11);
UPDATE `mg_articoli_interventi` SET `idarticolo` = NULL WHERE `idarticolo` NOT IN (SELECT `id` FROM `mg_articoli`);
ALTER TABLE `mg_articoli_interventi` ADD FOREIGN KEY (`idarticolo`) REFERENCES `mg_articoli`(`id`) ON DELETE CASCADE;

ALTER TABLE `dt_righe_ddt` CHANGE `idarticolo` `idarticolo` int(11);
UPDATE `dt_righe_ddt` SET `idarticolo` = NULL WHERE `idarticolo` NOT IN (SELECT `id` FROM `mg_articoli`);
ALTER TABLE `dt_righe_ddt` ADD FOREIGN KEY (`idarticolo`) REFERENCES `mg_articoli`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe2_contratti` CHANGE `idarticolo` `idarticolo` int(11);
UPDATE `co_righe2_contratti` SET `idarticolo` = NULL WHERE `idarticolo` NOT IN (SELECT `id` FROM `mg_articoli`);
ALTER TABLE `co_righe2_contratti` ADD FOREIGN KEY (`idarticolo`) REFERENCES `mg_articoli`(`id`) ON DELETE CASCADE;

ALTER TABLE `mg_articoli_automezzi` CHANGE `idarticolo` `idarticolo` int(11);
UPDATE `mg_articoli_automezzi` SET `idarticolo` = NULL WHERE `idarticolo` NOT IN (SELECT `id` FROM `mg_articoli`);
ALTER TABLE `mg_articoli_automezzi` ADD FOREIGN KEY (`idarticolo`) REFERENCES `mg_articoli`(`id`) ON DELETE CASCADE;

ALTER TABLE `or_righe_ordini` CHANGE `idarticolo` `idarticolo` int(11);
UPDATE `or_righe_ordini` SET `idarticolo` = NULL WHERE `idarticolo` NOT IN (SELECT `id` FROM `mg_articoli`);
ALTER TABLE `or_righe_ordini` ADD FOREIGN KEY (`idarticolo`) REFERENCES `mg_articoli`(`id`) ON DELETE CASCADE;

ALTER TABLE `mg_movimenti` CHANGE `idarticolo` `idarticolo` int(11);
UPDATE `mg_movimenti` SET `idarticolo` = NULL WHERE `idarticolo` NOT IN (SELECT `id` FROM `mg_articoli`);
ALTER TABLE `mg_movimenti` ADD FOREIGN KEY (`idarticolo`) REFERENCES `mg_articoli`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_preventivi` CHANGE `idarticolo` `idarticolo` int(11);
UPDATE `co_righe_preventivi` SET `idarticolo` = NULL WHERE `idarticolo` NOT IN (SELECT `id` FROM `mg_articoli`);
ALTER TABLE `co_righe_preventivi` ADD FOREIGN KEY (`idarticolo`) REFERENCES `mg_articoli`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_documenti` CHANGE `idarticolo` `idarticolo` int(11);
UPDATE `co_righe_documenti` SET `idarticolo` = NULL WHERE `idarticolo` NOT IN (SELECT `id` FROM `mg_articoli`);
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`idarticolo`) REFERENCES `mg_articoli`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_contratti_articoli` CHANGE `idarticolo` `idarticolo` int(11);
UPDATE `co_righe_contratti_articoli` SET `idarticolo` = NULL WHERE `idarticolo` NOT IN (SELECT `id` FROM `mg_articoli`);
ALTER TABLE `co_righe_contratti_articoli` ADD FOREIGN KEY (`idarticolo`) REFERENCES `mg_articoli`(`id`) ON DELETE CASCADE;

-- Foreign keys mg_categorie
ALTER TABLE `mg_articoli` CHANGE `id_categoria` `id_categoria` int(11);
UPDATE `mg_articoli` SET `id_categoria` = NULL WHERE `id_categoria` NOT IN (SELECT `id` FROM `mg_categorie`);
ALTER TABLE `mg_articoli` ADD FOREIGN KEY (`id_categoria`) REFERENCES `mg_categorie`(`id`) ON DELETE CASCADE;

ALTER TABLE `mg_articoli` CHANGE `id_sottocategoria` `id_sottocategoria` int(11);
UPDATE `mg_articoli` SET `id_sottocategoria` = NULL WHERE `id_sottocategoria` NOT IN (SELECT `id` FROM `mg_categorie`);
ALTER TABLE `mg_articoli` ADD FOREIGN KEY (`id_sottocategoria`) REFERENCES `mg_categorie`(`id`) ON DELETE CASCADE;

-- Foreign keys mg_listini
ALTER TABLE `an_anagrafiche` CHANGE `idlistino_acquisti` `idlistino_acquisti` int(11);
UPDATE `an_anagrafiche` SET `idlistino_acquisti` = NULL WHERE `idlistino_acquisti` NOT IN (SELECT `id` FROM `mg_listini`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idlistino_acquisti`) REFERENCES `mg_listini`(`id`) ON DELETE CASCADE;

ALTER TABLE `an_anagrafiche` CHANGE `idlistino_vendite` `idlistino_vendite` int(11);
UPDATE `an_anagrafiche` SET `idlistino_vendite` = NULL WHERE `idlistino_vendite` NOT IN (SELECT `id` FROM `mg_listini`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idlistino_vendite`) REFERENCES `mg_listini`(`id`) ON DELETE CASCADE;

-- Foreign keys mg_unitamisura
ALTER TABLE `mg_unitamisura` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;
-- NO RELAZIONI PER ID

-- Foreign keys zz_plugins
ALTER TABLE `zz_files` ADD FOREIGN KEY (`id_module`) REFERENCES `zz_modules`(`id`) ON DELETE CASCADE;
ALTER TABLE `zz_segments` ADD FOREIGN KEY (`id_module`) REFERENCES `zz_modules`(`id`) ON DELETE CASCADE;

-- Foreign keys zz_plugins
ALTER TABLE `zz_files` ADD FOREIGN KEY (`id_plugin`) REFERENCES `zz_plugins`(`id`) ON DELETE CASCADE;

-- Foreign keys zz_segments
ALTER TABLE `co_documenti` CHANGE `id_segment` `id_segment` int(11);
UPDATE `co_documenti` SET `id_segment` = NULL WHERE `id_segment` NOT IN (SELECT `id` FROM `zz_segments`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`id_segment`) REFERENCES `zz_segments`(`id`) ON DELETE CASCADE;

-- Foreign keys zz_settings
ALTER TABLE `zz_settings` CHANGE `idimpostazione` `id` int(11) NOT NULL AUTO_INCREMENT;

-- Foreign keys co_banche
ALTER TABLE `co_documenti` CHANGE `idbanca` `idbanca` int(11);
UPDATE `co_documenti` SET `idbanca` = NULL WHERE `idbanca` NOT IN (SELECT `id` FROM `co_banche`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idbanca`) REFERENCES `co_banche`(`id`) ON DELETE CASCADE;

ALTER TABLE `an_anagrafiche` CHANGE `idbanca_acquisti` `idbanca_acquisti` int(11);
UPDATE `an_anagrafiche` SET `idbanca_acquisti` = NULL WHERE `idbanca_acquisti` NOT IN (SELECT `id` FROM `co_banche`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idbanca_acquisti`) REFERENCES `co_banche`(`id`) ON DELETE CASCADE;

ALTER TABLE `an_anagrafiche` CHANGE `idbanca_vendite` `idbanca_vendite` int(11);
UPDATE `an_anagrafiche` SET `idbanca_vendite` = NULL WHERE `idbanca_vendite` NOT IN (SELECT `id` FROM `co_banche`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idbanca_vendite`) REFERENCES `co_banche`(`id`) ON DELETE CASCADE;

-- Foreign keys co_contratti
ALTER TABLE `co_contratti_tipiintervento` CHANGE `idcontratto` `idcontratto` int(11);
UPDATE `co_contratti_tipiintervento` SET `idcontratto` = NULL WHERE `idcontratto` NOT IN (SELECT `id` FROM `co_contratti`);
ALTER TABLE `co_contratti_tipiintervento` ADD FOREIGN KEY (`idcontratto`) REFERENCES `co_contratti`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe2_contratti` CHANGE `idcontratto` `idcontratto` int(11);
UPDATE `co_righe2_contratti` SET `idcontratto` = NULL WHERE `idcontratto` NOT IN (SELECT `id` FROM `co_contratti`);
ALTER TABLE `co_righe2_contratti` ADD FOREIGN KEY (`idcontratto`) REFERENCES `co_contratti`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_ordiniservizio_pianificazionefatture` CHANGE `idcontratto` `idcontratto` int(11);
UPDATE `co_ordiniservizio_pianificazionefatture` SET `idcontratto` = NULL WHERE `idcontratto` NOT IN (SELECT `id` FROM `co_contratti`);
ALTER TABLE `co_ordiniservizio_pianificazionefatture` ADD FOREIGN KEY (`idcontratto`) REFERENCES `co_contratti`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_documenti` CHANGE `idcontratto` `idcontratto` int(11);
UPDATE `co_righe_documenti` SET `idcontratto` = NULL WHERE `idcontratto` NOT IN (SELECT `id` FROM `co_contratti`);
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`idcontratto`) REFERENCES `co_contratti`(`id`) ON DELETE CASCADE;

UPDATE `my_impianti_contratti` SET `idcontratto` = 0 WHERE `idcontratto` = '';
ALTER TABLE `my_impianti_contratti` CHANGE `idcontratto` `idcontratto` int(11);
UPDATE `my_impianti_contratti` SET `idcontratto` = NULL WHERE `idcontratto` NOT IN (SELECT `id` FROM `co_contratti`);
ALTER TABLE `my_impianti_contratti` ADD FOREIGN KEY (`idcontratto`) REFERENCES `co_contratti`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_ordiniservizio` CHANGE `idcontratto` `idcontratto` int(11);
UPDATE `co_ordiniservizio` SET `idcontratto` = NULL WHERE `idcontratto` NOT IN (SELECT `id` FROM `co_contratti`);
ALTER TABLE `co_ordiniservizio` ADD FOREIGN KEY (`idcontratto`) REFERENCES `co_contratti`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_contratti` CHANGE `idcontratto` `idcontratto` int(11);
UPDATE `co_righe_contratti` SET `idcontratto` = NULL WHERE `idcontratto` NOT IN (SELECT `id` FROM `co_contratti`);
ALTER TABLE `co_righe_contratti` ADD FOREIGN KEY (`idcontratto`) REFERENCES `co_contratti`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_contratti` CHANGE `idcontratto_prev` `idcontratto_prev` int(11);
UPDATE `co_contratti` `t1` LEFT JOIN `co_contratti` `t2` ON `t1`.`idcontratto_prev` = `t2`.`id` SET `t1`.`idcontratto_prev` = NULL WHERE `t2`.`id` IS NULL;
ALTER TABLE `co_contratti` ADD FOREIGN KEY (`idcontratto_prev`) REFERENCES `co_contratti`(`id`) ON DELETE CASCADE;

-- Foreign keys co_righe_contratti
ALTER TABLE `co_righe_contratti_materiali` ADD FOREIGN KEY (`id_riga_contratto`) REFERENCES `co_righe_contratti`(`id`) ON DELETE CASCADE;
ALTER TABLE `co_righe_contratti_articoli` ADD FOREIGN KEY (`id_riga_contratto`) REFERENCES `co_righe_contratti`(`id`) ON DELETE CASCADE;

-- Foreign keys co_documenti
ALTER TABLE `co_movimenti` CHANGE `iddocumento` `iddocumento` int(11);
UPDATE `co_movimenti` SET `iddocumento` = NULL WHERE `iddocumento` NOT IN (SELECT `id` FROM `co_documenti`);
ALTER TABLE `co_movimenti` ADD FOREIGN KEY (`iddocumento`) REFERENCES `co_documenti`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_ordiniservizio_pianificazionefatture` CHANGE `iddocumento` `iddocumento` int(11);
UPDATE `co_ordiniservizio_pianificazionefatture` SET `iddocumento` = NULL WHERE `iddocumento` NOT IN (SELECT `id` FROM `co_documenti`);
ALTER TABLE `co_ordiniservizio_pianificazionefatture` ADD FOREIGN KEY (`iddocumento`) REFERENCES `co_documenti`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_scadenziario` CHANGE `iddocumento` `iddocumento` int(11);
UPDATE `co_scadenziario` SET `iddocumento` = NULL WHERE `iddocumento` NOT IN (SELECT `id` FROM `co_documenti`);
ALTER TABLE `co_scadenziario` ADD FOREIGN KEY (`iddocumento`) REFERENCES `co_documenti`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_documenti` CHANGE `iddocumento` `iddocumento` int(11);
UPDATE `co_righe_documenti` SET `iddocumento` = NULL WHERE `iddocumento` NOT IN (SELECT `id` FROM `co_documenti`);
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`iddocumento`) REFERENCES `co_documenti`(`id`) ON DELETE CASCADE;

ALTER TABLE `mg_movimenti` CHANGE `iddocumento` `iddocumento` int(11);
UPDATE `mg_movimenti` SET `iddocumento` = NULL WHERE `iddocumento` NOT IN (SELECT `id` FROM `co_documenti`);
ALTER TABLE `mg_movimenti` ADD FOREIGN KEY (`iddocumento`) REFERENCES `co_documenti`(`id`) ON DELETE CASCADE;

-- Foreign keys co_iva
ALTER TABLE `co_righe_contratti_articoli` CHANGE `idiva` `idiva` int(11);
UPDATE `co_righe_contratti_articoli` SET `idiva` = NULL WHERE `idiva` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `co_righe_contratti_articoli` ADD FOREIGN KEY (`idiva`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_preventivi` CHANGE `idiva` `idiva` int(11);
UPDATE `co_righe_preventivi` SET `idiva` = NULL WHERE `idiva` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `co_righe_preventivi` ADD FOREIGN KEY (`idiva`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_contratti_materiali` CHANGE `idiva` `idiva` int(11);
UPDATE `co_righe_contratti_materiali` SET `idiva` = NULL WHERE `idiva` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `co_righe_contratti_materiali` ADD FOREIGN KEY (`idiva`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `dt_ddt` CHANGE `idiva` `idiva` int(11);
UPDATE `dt_ddt` SET `idiva` = NULL WHERE `idiva` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idiva`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `mg_articoli_interventi` CHANGE `idiva` `idiva` int(11);
UPDATE `mg_articoli_interventi` SET `idiva` = NULL WHERE `idiva` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `mg_articoli_interventi` ADD FOREIGN KEY (`idiva`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `or_righe_ordini` CHANGE `idiva` `idiva` int(11);
UPDATE `or_righe_ordini` SET `idiva` = NULL WHERE `idiva` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `or_righe_ordini` ADD FOREIGN KEY (`idiva`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_preventivi` CHANGE `idiva` `idiva` int(11);
UPDATE `co_preventivi` SET `idiva` = NULL WHERE `idiva` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `co_preventivi` ADD FOREIGN KEY (`idiva`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `dt_righe_ddt` CHANGE `idiva` `idiva` int(11);
UPDATE `dt_righe_ddt` SET `idiva` = NULL WHERE `idiva` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `dt_righe_ddt` ADD FOREIGN KEY (`idiva`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_documenti` CHANGE `idiva` `idiva` int(11);
UPDATE `co_righe_documenti` SET `idiva` = NULL WHERE `idiva` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`idiva`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe2_contratti` CHANGE `idiva` `idiva` int(11);
UPDATE `co_righe2_contratti` SET `idiva` = NULL WHERE `idiva` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `co_righe2_contratti` ADD FOREIGN KEY (`idiva`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `in_righe_interventi` CHANGE `idiva` `idiva` int(11);
UPDATE `in_righe_interventi` SET `idiva` = NULL WHERE `idiva` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `in_righe_interventi` ADD FOREIGN KEY (`idiva`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `an_anagrafiche` CHANGE `idiva_acquisti` `idiva_acquisti` int(11);
UPDATE `an_anagrafiche` SET `idiva_acquisti` = NULL WHERE `idiva_acquisti` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idiva_acquisti`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `an_anagrafiche` CHANGE `idiva_vendite` `idiva_vendite` int(11);
UPDATE `an_anagrafiche` SET `idiva_vendite` = NULL WHERE `idiva_vendite` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idiva_vendite`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

ALTER TABLE `mg_articoli` CHANGE `idiva_vendita` `idiva_vendita` int(11);
UPDATE `mg_articoli` SET `idiva_vendita` = NULL WHERE `idiva_vendita` NOT IN (SELECT `id` FROM `co_iva`);
ALTER TABLE `mg_articoli` ADD FOREIGN KEY (`idiva_vendita`) REFERENCES `co_iva`(`id`) ON DELETE CASCADE;

-- Foreign keys co_ordiniservizio
ALTER TABLE `co_ordiniservizio_vociservizio` ADD FOREIGN KEY (`idordineservizio`) REFERENCES `co_ordiniservizio`(`id`) ON DELETE CASCADE;

-- Foreign keys co_pagamenti
ALTER TABLE `co_documenti` CHANGE `idpagamento` `idpagamento` int(11);
UPDATE `co_documenti` SET `idpagamento` = NULL WHERE `idpagamento` NOT IN (SELECT `id` FROM `co_pagamenti`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idpagamento`) REFERENCES `co_pagamenti`(`id`) ON DELETE CASCADE;

ALTER TABLE `dt_ddt` CHANGE `idpagamento` `idpagamento` int(11);
UPDATE `dt_ddt` SET `idpagamento` = NULL WHERE `idpagamento` NOT IN (SELECT `id` FROM `co_pagamenti`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idpagamento`) REFERENCES `co_pagamenti`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_preventivi` CHANGE `idpagamento` `idpagamento` int(11);
UPDATE `co_preventivi` SET `idpagamento` = NULL WHERE `idpagamento` NOT IN (SELECT `id` FROM `co_pagamenti`);
ALTER TABLE `co_preventivi` ADD FOREIGN KEY (`idpagamento`) REFERENCES `co_pagamenti`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_contratti` CHANGE `idpagamento` `idpagamento` int(11);
UPDATE `co_contratti` SET `idpagamento` = NULL WHERE `idpagamento` NOT IN (SELECT `id` FROM `co_pagamenti`);
ALTER TABLE `co_contratti` ADD FOREIGN KEY (`idpagamento`) REFERENCES `co_pagamenti`(`id`) ON DELETE CASCADE;

ALTER TABLE `or_ordini` CHANGE `idpagamento` `idpagamento` int(11);
UPDATE `or_ordini` SET `idpagamento` = NULL WHERE `idpagamento` NOT IN (SELECT `id` FROM `co_pagamenti`);
ALTER TABLE `or_ordini` ADD FOREIGN KEY (`idpagamento`) REFERENCES `co_pagamenti`(`id`) ON DELETE CASCADE;

UPDATE `an_anagrafiche` SET `idpagamento_acquisti` = NULL WHERE `idpagamento_acquisti` NOT IN (SELECT `id` FROM `co_pagamenti`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idpagamento_acquisti`) REFERENCES `co_pagamenti`(`id`) ON DELETE CASCADE;

UPDATE `an_anagrafiche` SET `idpagamento_vendite` = NULL WHERE `idpagamento_vendite` NOT IN (SELECT `id` FROM `co_pagamenti`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idpagamento_vendite`) REFERENCES `co_pagamenti`(`id`) ON DELETE CASCADE;

-- Foreign keys co_pianodeiconti1
ALTER TABLE `co_pianodeiconti2` ADD FOREIGN KEY (`idpianodeiconti1`) REFERENCES `co_pianodeiconti1`(`id`) ON DELETE CASCADE;

-- Foreign keys co_pianodeiconti2
ALTER TABLE `co_pianodeiconti3` ADD FOREIGN KEY (`idpianodeiconti2`) REFERENCES `co_pianodeiconti2`(`id`) ON DELETE CASCADE;

-- Foreign keys co_pianodeiconti3
ALTER TABLE `dt_ddt` CHANGE `idconto` `idconto` int(11);
UPDATE `dt_ddt` SET `idconto` = NULL WHERE `idconto` NOT IN (SELECT `id` FROM `co_pianodeiconti3`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idconto`) REFERENCES `co_pianodeiconti3`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_documenti` CHANGE `idconto` `idconto` int(11);
UPDATE `co_righe_documenti` SET `idconto` = NULL WHERE `idconto` NOT IN (SELECT `id` FROM `co_pianodeiconti3`);
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`idconto`) REFERENCES `co_pianodeiconti3`(`id`) ON DELETE CASCADE;

ALTER TABLE `or_ordini` CHANGE `idconto` `idconto` int(11);
UPDATE `or_ordini` SET `idconto` = NULL WHERE `idconto` NOT IN (SELECT `id` FROM `co_pianodeiconti3`);
ALTER TABLE `or_ordini` ADD FOREIGN KEY (`idconto`) REFERENCES `co_pianodeiconti3`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_movimenti_modelli` CHANGE `idconto` `idconto` int(11);
UPDATE `co_movimenti_modelli` SET `idconto` = NULL WHERE `idconto` NOT IN (SELECT `id` FROM `co_pianodeiconti3`);
ALTER TABLE `co_movimenti_modelli` ADD FOREIGN KEY (`idconto`) REFERENCES `co_pianodeiconti3`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_documenti` CHANGE `idconto` `idconto` int(11);
UPDATE `co_documenti` SET `idconto` = NULL WHERE `idconto` NOT IN (SELECT `id` FROM `co_pianodeiconti3`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idconto`) REFERENCES `co_pianodeiconti3`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_movimenti` CHANGE `idconto` `idconto` int(11);
UPDATE `co_movimenti` SET `idconto` = NULL WHERE `idconto` NOT IN (SELECT `id` FROM `co_pianodeiconti3`);
ALTER TABLE `co_movimenti` ADD FOREIGN KEY (`idconto`) REFERENCES `co_pianodeiconti3`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_pagamenti` CHANGE `idconto_acquisti` `idconto_acquisti` int(11);
UPDATE `co_pagamenti` SET `idconto_acquisti` = NULL WHERE `idconto_acquisti` NOT IN (SELECT `id` FROM `co_pianodeiconti3`);
ALTER TABLE `co_pagamenti` ADD FOREIGN KEY (`idconto_acquisti`) REFERENCES `co_pianodeiconti3`(`id`) ON DELETE CASCADE;

ALTER TABLE `an_anagrafiche` CHANGE `idconto_cliente` `idconto_cliente` int(11);
UPDATE `an_anagrafiche` SET `idconto_cliente` = NULL WHERE `idconto_cliente` NOT IN (SELECT `id` FROM `co_pianodeiconti3`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idconto_cliente`) REFERENCES `co_pianodeiconti3`(`id`) ON DELETE CASCADE;

ALTER TABLE `an_anagrafiche` CHANGE `idconto_fornitore` `idconto_fornitore` int(11);
UPDATE `an_anagrafiche` SET `idconto_fornitore` = NULL WHERE `idconto_fornitore` NOT IN (SELECT `id` FROM `co_pianodeiconti3`);
ALTER TABLE `an_anagrafiche` ADD FOREIGN KEY (`idconto_fornitore`) REFERENCES `co_pianodeiconti3`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_pagamenti` CHANGE `idconto_vendite` `idconto_vendite` int(11);
UPDATE `co_pagamenti` SET `idconto_vendite` = NULL WHERE `idconto_vendite` NOT IN (SELECT `id` FROM `co_pianodeiconti3`);
ALTER TABLE `co_pagamenti` ADD FOREIGN KEY (`idconto_vendite`) REFERENCES `co_pianodeiconti3`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_banche` CHANGE `id_pianodeiconti3` `id_pianodeiconti3` int(11);
UPDATE `co_banche` SET `id_pianodeiconti3` = NULL WHERE `id_pianodeiconti3` NOT IN (SELECT `id` FROM `co_pianodeiconti3`);
ALTER TABLE `co_banche` ADD FOREIGN KEY (`id_pianodeiconti3`) REFERENCES `co_pianodeiconti3`(`id`) ON DELETE CASCADE;

-- Foreign keys co_preventivi
ALTER TABLE `or_righe_ordini` CHANGE `idpreventivo` `idpreventivo` int(11);
UPDATE `or_righe_ordini` SET `idpreventivo` = NULL WHERE `idpreventivo` NOT IN (SELECT `id` FROM `co_preventivi`);
ALTER TABLE `or_righe_ordini` ADD FOREIGN KEY (`idpreventivo`) REFERENCES `co_preventivi`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_preventivi_interventi` CHANGE `idpreventivo` `idpreventivo` int(11);
UPDATE `co_preventivi_interventi` SET `idpreventivo` = NULL WHERE `idpreventivo` NOT IN (SELECT `id` FROM `co_preventivi`);
ALTER TABLE `co_preventivi_interventi` ADD FOREIGN KEY (`idpreventivo`) REFERENCES `co_preventivi`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_documenti` CHANGE `idpreventivo` `idpreventivo` int(11);
UPDATE `co_righe_documenti` SET `idpreventivo` = NULL WHERE `idpreventivo` NOT IN (SELECT `id` FROM `co_preventivi`);
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`idpreventivo`) REFERENCES `co_preventivi`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_preventivi` CHANGE `idpreventivo` `idpreventivo` int(11);
UPDATE `co_righe_preventivi` SET `idpreventivo` = NULL WHERE `idpreventivo` NOT IN (SELECT `id` FROM `co_preventivi`);
ALTER TABLE `co_righe_preventivi` ADD FOREIGN KEY (`idpreventivo`) REFERENCES `co_preventivi`(`id`) ON DELETE CASCADE;

-- Foreign keys co_ritenutaacconto
ALTER TABLE `or_ordini` CHANGE `idritenutaacconto` `idritenutaacconto` int(11);
UPDATE `or_ordini` SET `idritenutaacconto` = NULL WHERE `idritenutaacconto` NOT IN (SELECT `id` FROM `co_ritenutaacconto`);
ALTER TABLE `or_ordini` ADD FOREIGN KEY (`idritenutaacconto`) REFERENCES `co_ritenutaacconto`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_documenti` CHANGE `idritenutaacconto` `idritenutaacconto` int(11);
UPDATE `co_righe_documenti` SET `idritenutaacconto` = NULL WHERE `idritenutaacconto` NOT IN (SELECT `id` FROM `co_ritenutaacconto`);
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`idritenutaacconto`) REFERENCES `co_ritenutaacconto`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_documenti` CHANGE `idritenutaacconto` `idritenutaacconto` int(11);
UPDATE `co_documenti` SET `idritenutaacconto` = NULL WHERE `idritenutaacconto` NOT IN (SELECT `id` FROM `co_ritenutaacconto`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idritenutaacconto`) REFERENCES `co_ritenutaacconto`(`id`) ON DELETE CASCADE;

ALTER TABLE `dt_ddt` CHANGE `idritenutaacconto` `idritenutaacconto` int(11);
UPDATE `dt_ddt` SET `idritenutaacconto` = NULL WHERE `idritenutaacconto` NOT IN (SELECT `id` FROM `co_ritenutaacconto`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idritenutaacconto`) REFERENCES `co_ritenutaacconto`(`id`) ON DELETE CASCADE;

-- Foreign keys co_rivalsainps
ALTER TABLE `or_ordini` CHANGE `idrivalsainps` `idrivalsainps` int(11);
UPDATE `or_ordini` SET `idrivalsainps` = NULL WHERE `idrivalsainps` NOT IN (SELECT `id` FROM `co_rivalsainps`);
ALTER TABLE `or_ordini` ADD FOREIGN KEY (`idrivalsainps`) REFERENCES `co_rivalsainps`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_righe_documenti` CHANGE `idrivalsainps` `idrivalsainps` int(11);
UPDATE `co_righe_documenti` SET `idrivalsainps` = NULL WHERE `idrivalsainps` NOT IN (SELECT `id` FROM `co_rivalsainps`);
ALTER TABLE `co_righe_documenti` ADD FOREIGN KEY (`idrivalsainps`) REFERENCES `co_rivalsainps`(`id`) ON DELETE CASCADE;

ALTER TABLE `co_documenti` CHANGE `idrivalsainps` `idrivalsainps` int(11);
UPDATE `co_documenti` SET `idrivalsainps` = NULL WHERE `idrivalsainps` NOT IN (SELECT `id` FROM `co_rivalsainps`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idrivalsainps`) REFERENCES `co_rivalsainps`(`id`) ON DELETE CASCADE;

ALTER TABLE `dt_ddt` CHANGE `idrivalsainps` `idrivalsainps` int(11);
UPDATE `dt_ddt` SET `idrivalsainps` = NULL WHERE `idrivalsainps` NOT IN (SELECT `id` FROM `co_rivalsainps`);
ALTER TABLE `dt_ddt` ADD FOREIGN KEY (`idrivalsainps`) REFERENCES `co_rivalsainps`(`id`) ON DELETE CASCADE;

-- Foreign keys co_staticontratti
ALTER TABLE `co_staticontratti` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `co_contratti` CHANGE `idstato` `idstato` int(11) NOT NULL;
UPDATE `co_contratti` SET `idstato` = NULL WHERE `idstato` NOT IN (SELECT `id` FROM `co_staticontratti`);
ALTER TABLE `co_contratti` ADD FOREIGN KEY (`idstato`) REFERENCES `co_staticontratti`(`id`) ON DELETE CASCADE;

-- Foreign keys co_statidocumento
ALTER TABLE `co_statidocumento` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `co_documenti` CHANGE `idstatodocumento` `idstatodocumento` int(11) NOT NULL;
UPDATE `co_documenti` SET `idstatodocumento` = NULL WHERE `idstatodocumento` NOT IN (SELECT `id` FROM `co_statidocumento`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idstatodocumento`) REFERENCES `co_statidocumento`(`id`) ON DELETE CASCADE;

-- Foreign keys co_statipreventivi
ALTER TABLE `co_statipreventivi` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `co_preventivi` CHANGE `idstato` `idstato` int(11) NOT NULL;
UPDATE `co_preventivi` SET `idstato` = NULL WHERE `idstato` NOT IN (SELECT `id` FROM `co_statipreventivi`);
ALTER TABLE `co_preventivi` ADD FOREIGN KEY (`idstato`) REFERENCES `co_statipreventivi`(`id`) ON DELETE CASCADE;

-- Foreign keys co_tipidocumento
ALTER TABLE `co_tipidocumento` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `co_documenti` CHANGE `idtipodocumento` `idtipodocumento` int(11) NOT NULL;
UPDATE `co_documenti` SET `idtipodocumento` = NULL WHERE `idtipodocumento` NOT IN (SELECT `id` FROM `co_tipidocumento`);
ALTER TABLE `co_documenti` ADD FOREIGN KEY (`idtipodocumento`) REFERENCES `co_tipidocumento`(`id`) ON DELETE CASCADE;

-- TODO: correggere le 45 chiavi esterne create negli aggiornamenti precedenti
-- Le seguenti sono state create a partire da zero:
-- an_tipianagrafiche_anagrafiche - idanagrafica, idtipoanagrafica
-- in_interventi - idtipointervento, idanagrafica
-- in_interventi_tecnici - idtecnico
