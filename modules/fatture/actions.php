<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.r.l.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

include_once __DIR__.'/../../core.php';

use Modules\Anagrafiche\Anagrafica;
use Modules\Articoli\Articolo as ArticoloOriginale;
use Modules\DDT\DDT;
use Modules\Fatture\Components\Articolo;
use Modules\Fatture\Components\Descrizione;
use Modules\Fatture\Components\Riga;
use Modules\Fatture\Components\Sconto;
use Modules\Fatture\Fattura;
use Modules\Fatture\Stato;
use Modules\Fatture\Tipo;
use Plugins\ExportFE\FatturaElettronica;
use Util\XML;

$module = Modules::get($id_module);

if ($module['name'] == 'Fatture di vendita') {
    $dir = 'entrata';
} else {
    $dir = 'uscita';
}

switch (post('op')) {
    case 'add':
        $idanagrafica = post('idanagrafica');
        $data = post('data');
        $idtipodocumento = post('idtipodocumento');
        $id_segment = post('id_segment');

        if ($dir == 'uscita') {
            $numero_esterno = post('numero_esterno');
        }

        $anagrafica = Anagrafica::find($idanagrafica);
        $tipo = Tipo::find($idtipodocumento);

        $fattura = Fattura::build($anagrafica, $tipo, $data, $id_segment, $numero_esterno);

        $id_record = $fattura->id;

        flash()->info(tr('Aggiunta fattura numero _NUM_!', [
            '_NUM_' => $fattura->numero,
        ]));

        break;

    case 'update':
        $stato_precedente = $fattura->stato;

        $stato = Stato::find(post('idstatodocumento'));
        $fattura->stato()->associate($stato);

        $tipo = Tipo::find(post('idtipodocumento'));
        $fattura->tipo()->associate($tipo);

        $fattura->data = post('data');

        if ($dir == 'entrata') {
            $fattura->data_registrazione = post('data');
        } else {
            $fattura->data_registrazione = post('data_registrazione');
        }

        $fattura->data_competenza = post('data_competenza');

        $fattura->numero_esterno = post('numero_esterno');
        $fattura->note = post('note');
        $fattura->note_aggiuntive = post('note_aggiuntive');

        $fattura->idanagrafica = post('idanagrafica');
        $fattura->idagente = post('idagente');
        $fattura->idreferente = post('idreferente');
        $fattura->idpagamento = post('idpagamento');
        $fattura->id_banca_azienda = post('id_banca_azienda');
        $fattura->id_banca_controparte = post('id_banca_controparte');
        $fattura->idcausalet = post('idcausalet');
        $fattura->idspedizione = post('idspedizione');
        $fattura->idporto = post('idporto');
        $fattura->idaspettobeni = post('idaspettobeni');
        $fattura->idvettore = post('idvettore');
        $fattura->idsede_partenza = post('idsede_partenza');
        $fattura->idsede_destinazione = post('idsede_destinazione');
        $fattura->idconto = post('idconto');
        $fattura->split_payment = post('split_payment') ?: 0;
        $fattura->is_fattura_conto_terzi = post('is_fattura_conto_terzi') ?: 0;
        $fattura->n_colli = post('n_colli');
        $fattura->tipo_resa = post('tipo_resa');

        $fattura->peso = post('peso');
        $fattura->volume = post('volume');
        $fattura->peso_manuale = post('peso_manuale');
        $fattura->volume_manuale = post('volume_manuale');

        $fattura->rivalsainps = 0;
        $fattura->ritenutaacconto = 0;
        $fattura->iva_rivalsainps = 0;
        $fattura->id_ritenuta_contributi = post('id_ritenuta_contributi') ?: null;

        $fattura->codice_stato_fe = post('codice_stato_fe') ?: null;

        // Informazioni per le fatture di acquisto
        if ($dir == 'uscita') {
            $fattura->numero = post('numero');
            $fattura->numero_esterno = post('numero_esterno');
            $fattura->idrivalsainps = post('id_rivalsa_inps');
            $fattura->idritenutaacconto = post('id_ritenuta_acconto');
        }

        // Operazioni sul bollo
        if ($dir == 'entrata') {
            $fattura->addebita_bollo = post('addebita_bollo');
            $bollo_automatico = post('bollo_automatico');
            if (empty($bollo_automatico)) {
                $fattura->bollo = post('bollo');
            } else {
                $fattura->bollo = null;
            }
        }

        // Operazioni sulla dichiarazione d'intento
        $dichiarazione_precedente = $fattura->dichiarazione;
        $fattura->id_dichiarazione_intento = post('id_dichiarazione_intento') ?: null;

        // Flag pagamento ritenuta
        $fattura->is_ritenuta_pagata = post('is_ritenuta_pagata') ?: 0;

        $fattura->setScontoFinale(post('sconto_finale'), post('tipo_sconto_finale'));

        $fattura->save();

        // Operazioni automatiche per le Fatture Elettroniche
        if ($fattura->direzione == 'entrata' && $stato_precedente->descrizione == 'Bozza' && $stato['descrizione'] == 'Emessa') {
            $stato_fe = $database->fetchOne('SELECT * FROM fe_stati_documento WHERE codice = '.prepare($fattura->codice_stato_fe));
            $abilita_genera = empty($fattura->codice_stato_fe) || intval($stato_fe['is_generabile']);

            // Generazione automatica della Fattura Elettronica
            $checks = FatturaElettronica::controllaFattura($fattura);
            $fattura_elettronica = new FatturaElettronica($id_record);
            if ($abilita_genera && empty($checks)) {
                $file = $fattura_elettronica->save(base_dir().'/'.FatturaElettronica::getDirectory());

                flash()->info(tr('Fattura elettronica generata correttamente!'));

                if (!$fattura_elettronica->isValid()) {
                    $errors = $fattura_elettronica->getErrors();

                    flash()->warning(tr('La fattura elettronica potrebbe avere delle irregolarità!').' '.tr('Controllare i seguenti campi: _LIST_', [
                            '_LIST_' => implode(', ', $errors),
                        ]).'.');
                }
            }
            // Visualizzazione degli errori
            elseif (!empty($checks)) {
                // Rimozione eventuale fattura generata erronamente
                // Fix per la modifica di dati interni su fattura già generata
                if ($abilita_genera) {
                    $fattura_elettronica->delete();
                }

                // Messaggi informativi sulle problematiche
                $message = tr('La fattura elettronica non è stata generata a causa di alcune informazioni mancanti').':';

                foreach ($checks as $check) {
                    $message .= '
<p><b>'.$check['name'].' '.$check['link'].'</b></p>
<ul>';

                    foreach ($check['errors'] as $error) {
                        if (!empty($error)) {
                            $message .= '
<li>'.$error.'</li>';
                        }
                    }

                    $message .= '
</ul>';
                }

                flash()->warning($message);
            }
        }

        aggiorna_sedi_movimenti('documenti', $id_record);

        // Controllo sulla presenza di fattura di acquisto con lo stesso numero secondario nello stesso periodo
        $direzione = $fattura->direzione;
        if ($direzione == 'uscita') {
            $count = Fattura::where('numero_esterno', $fattura->numero_esterno)
                ->where('id', '!=', $id_record)
                ->where('idanagrafica', '=', $fattura->anagrafica->id)
                ->where('data', '>=', session('period_start'))
                ->where('data', '<=', session('period_end'))
                ->whereHas('tipo', function ($query) use ($direzione) {
                    $query->where('dir', '=', $direzione);
                })->count();
            if (!empty($count)) {
                flash()->warning(tr('Esiste già una fattura con lo stesso numero secondario e la stessa anagrafica collegata!'));
            }
        }

        flash()->info(tr('Fattura modificata correttamente!'));

        break;

    // Ricalcolo scadenze
    case 'ricalcola_scadenze':
        $fattura->registraScadenze(false, true);

        break;

    // Ricalcolo scadenze
    case 'controlla_totali':
        $totale_documento = null;

        try {
            $xml = XML::read($fattura->getXML());

            // Totale basato sul campo ImportoTotaleDocumento
            $dati_generali = $xml['FatturaElettronicaBody']['DatiGenerali']['DatiGeneraliDocumento'];
            if (isset($dati_generali['ImportoTotaleDocumento'])) {
                $totale_documento_indicato = abs(floatval($dati_generali['ImportoTotaleDocumento']));

                // Calcolo del totale basato sui DatiRiepilogo
                if (empty($totale_documento) && empty($dati_generali['ScontoMaggiorazione'])) {
                    $totale_documento = 0;

                    $riepiloghi = $xml['FatturaElettronicaBody']['DatiBeniServizi']['DatiRiepilogo'];
                    if (!empty($riepiloghi) && !isset($riepiloghi[0])) {
                        $riepiloghi = [$riepiloghi];
                    }

                    foreach ($riepiloghi as $riepilogo) {
                        $totale_documento = sum([$totale_documento, $riepilogo['ImponibileImporto'], $riepilogo['Imposta'], -$riepilogo['Arrotondamento']]);
                    }

                    $totale_documento = abs($totale_documento);
                } else {
                    $totale_documento = $totale_documento_indicato;
                }
            }
        } catch (Exception $e) {
        }

        echo json_encode([
            'stored' => $totale_documento,
            'calculated' => $fattura->totale,
        ]);

        break;

    // Elenco fatture in stato Bozza per il cliente
    case 'fatture_bozza':
        $id_anagrafica = post('id_anagrafica');
        $stato = Stato::where('descrizione', 'Bozza')->first();

        $fatture = Fattura::vendita()
            ->where('idanagrafica', $id_anagrafica)
            ->where('idstatodocumento', $stato->id)
            ->get();

        $results = [];
        foreach ($fatture as $result) {
            $results[] = Modules::link('Fatture di vendita', $result->id, reference($result));
        }

        echo json_encode($results);

        break;

    // eliminazione documento
    case 'delete':
        try {
            $fattura->delete();

            $dbo->query('DELETE FROM co_scadenziario WHERE iddocumento='.prepare($id_record));
            $dbo->query('DELETE FROM co_movimenti WHERE iddocumento='.prepare($id_record));

            // Azzeramento collegamento della rata contrattuale alla pianificazione
            $dbo->query('UPDATE co_fatturazione_contratti SET iddocumento=0 WHERE iddocumento='.prepare($id_record));

            flash()->info(tr('Fattura eliminata!'));
        } catch (InvalidArgumentException $e) {
            flash()->error(tr('Sono stati utilizzati alcuni serial number nel documento: impossibile procedere!'));
        }

        break;

    // Duplicazione fattura
    case 'copy':
        $new = $fattura->replicate();
        $new->save();

        $id_record = $new->id;

        $righe = $fattura->getRighe()->where('id', '!=', $fattura->id_riga_bollo);
        foreach ($righe as $riga) {
            $new_riga = $riga->replicate();
            $new_riga->setDocument($new);

            // Rimozione riferimenti (deprecati)
            $new_riga->idpreventivo = 0;
            $new_riga->idcontratto = 0;
            $new_riga->idintervento = 0;
            $new_riga->idddt = 0;
            $new_riga->idordine = 0;
            $new_riga->save();

            if ($new_riga->isArticolo()) {
                $new_riga->movimenta($new_riga->qta);
            }
        }

        flash()->info(tr('Fattura duplicata correttamente!'));

        break;

    case 'reopen':
        if (!empty($id_record)) {
            $dbo->query("UPDATE co_documenti SET idstatodocumento=(SELECT id FROM co_statidocumento WHERE descrizione='Bozza') WHERE id=".prepare($id_record));
            elimina_movimenti($id_record, 1);
            elimina_scadenze($id_record);
            ricalcola_costiagg_fattura($id_record);
            flash()->info(tr('Fattura riaperta!'));
        }

        break;

    case 'add_intervento':
        $id_intervento = post('idintervento');

        if (!empty($id_record) && $id_intervento !== null) {
            $copia_descrizione = post('copia_descrizione');
            $intervento = $dbo->fetchOne('SELECT descrizione FROM in_interventi WHERE id = '.prepare($id_intervento));
            if (!empty($copia_descrizione) && !empty($intervento['descrizione'])) {
                $riga = Descrizione::build($fattura);
                $riga->descrizione = $intervento['descrizione'];
                $riga->idintervento = $id_intervento;
                $riga->save();
            }

            aggiungi_intervento_in_fattura($id_intervento, $id_record, post('descrizione'), post('idiva'), post('idconto'), post('id_rivalsa_inps'), post('id_ritenuta_acconto'), post('calcolo_ritenuta_acconto'));

            flash()->info(tr('Intervento _NUM_ aggiunto!', [
                '_NUM_' => $idintervento,
            ]));
        }
        break;

    case 'manage_documento_fe':
        $data = Filter::getPOST();

        $ignore = [
            'id_plugin',
            'id_module',
            'id_record',
            'backto',
            'hash',
            'op',
            'idriga',
            'dir',
        ];
        foreach ($ignore as $name) {
            unset($data[$name]);
        }

        $fattura->dati_aggiuntivi_fe = $data;
        $fattura->save();

        flash()->info(tr('Dati FE aggiornati correttamente!'));

        break;

    case 'manage_riga_fe':
        $id_riga = post('id_riga');
        if ($id_riga != null) {
            $riga = Articolo::find($id_riga) ?: Riga::find($id_riga);
            $riga = $riga ?: Descrizione::find($id_riga);
            $riga = $riga ?: Sconto::find($id_riga);

            $data = Filter::getPOST();

            $ignore = [
                'id_plugin',
                'id_module',
                'id_record',
                'backto',
                'hash',
                'op',
                'idriga',
                'dir',
            ];
            foreach ($ignore as $name) {
                unset($data[$name]);
            }

            $riga->dati_aggiuntivi_fe = $data;
            $riga->save();

            flash()->info(tr('Dati FE aggiornati correttamente!'));
        }

        break;

    case 'manage_barcode':
        foreach (post('qta') as $id_articolo => $qta) {
            if ($id_articolo == '-id-') {
                continue;
            }

            // Dati di input
            $sconto = post('sconto')[$id_articolo];
            $tipo_sconto = post('tipo_sconto')[$id_articolo];
            $prezzo_unitario = post('prezzo_unitario')[$id_articolo];
            $id_dettaglio_fornitore = post('id_dettaglio_fornitore')[$id_articolo];
            $id_iva = $originale->idiva_vendita ? $originale->idiva_vendita : setting('Iva predefinita');

            $id_conto = ($fattura->direzione == 'entrata') ? setting('Conto predefinito fatture di vendita') : setting('Conto predefinito fatture di acquisto');
            if ($fattura->direzione == 'entrata' && !empty($originale->idconto_vendita)) {
                $id_conto = $originale->idconto_vendita;
            } elseif ($fattura->direzione == 'uscita' && !empty($originale->idconto_acquisto)) {
                $id_conto = $originale->idconto_acquisto;
            }

            // Creazione articolo
            $originale = ArticoloOriginale::find($id_articolo);
            $articolo = Articolo::build($fattura, $originale);
            $articolo->id_dettaglio_fornitore = $id_dettaglio_fornitore ?: null;

            $articolo->setPrezzoUnitario($prezzo_unitario, $id_iva);
            if ($dir == 'entrata') {
                $articolo->costo_unitario = $originale->prezzo_acquisto;
            }
            $articolo->setSconto($sconto, $tipo_sconto);
            $articolo->qta = $qta;
            $articolo->idconto = $id_conto;

            $articolo->save();
        }

        flash()->info(tr('Articoli aggiunti!'));

        break;

    case 'manage_articolo':
        if (post('idriga') != null) {
            $articolo = Articolo::find(post('idriga'));
        } else {
            $originale = ArticoloOriginale::find(post('idarticolo'));
            $articolo = Articolo::build($fattura, $originale);
            $articolo->id_dettaglio_fornitore = post('id_dettaglio_fornitore') ?: null;
        }

        $qta = post('qta');

        $articolo->descrizione = post('descrizione');
        $articolo->um = post('um') ?: null;

        $articolo->id_iva = post('idiva');
        $articolo->idconto = post('idconto');

        $articolo->calcolo_ritenuta_acconto = post('calcolo_ritenuta_acconto') ?: null;
        $articolo->id_ritenuta_acconto = post('id_ritenuta_acconto') ?: null;
        $articolo->ritenuta_contributi = boolval(post('ritenuta_contributi'));
        $articolo->id_rivalsa_inps = post('id_rivalsa_inps') ?: null;

        $articolo->costo_unitario = post('costo_unitario') ?: 0;
        $articolo->setPrezzoUnitario(post('prezzo_unitario'), post('idiva'));
        $articolo->setSconto(post('sconto'), post('tipo_sconto'));

        try {
            $articolo->qta = $qta;
        } catch (UnexpectedValueException $e) {
            flash()->error(tr('Alcuni serial number sono già stati utilizzati!'));
        }

        $articolo->save();

        if (post('idriga') != null) {
            flash()->info(tr('Articolo modificato!'));
        } else {
            flash()->info(tr('Articolo aggiunto!'));
        }

        // Ricalcolo inps, ritenuta e bollo
        ricalcola_costiagg_fattura($id_record);

        break;

    case 'manage_sconto':
        if (post('idriga') != null) {
            $sconto = Sconto::find(post('idriga'));
        } else {
            $sconto = Sconto::build($fattura);
        }

        $sconto->idconto = post('idconto');

        $sconto->calcolo_ritenuta_acconto = post('calcolo_ritenuta_acconto') ?: null;
        $sconto->id_ritenuta_acconto = post('id_ritenuta_acconto') ?: null;
        $sconto->ritenuta_contributi = boolval(post('ritenuta_contributi'));
        $sconto->id_rivalsa_inps = post('id_rivalsa_inps') ?: null;

        $sconto->descrizione = post('descrizione');
        $sconto->setScontoUnitario(post('sconto_unitario'), post('idiva'));

        $sconto->save();

        if (post('idriga') != null) {
            flash()->info(tr('Sconto/maggiorazione modificato!'));
        } else {
            flash()->info(tr('Sconto/maggiorazione aggiunto!'));
        }

        // Ricalcolo inps, ritenuta e bollo
        ricalcola_costiagg_fattura($id_record);

        break;

    case 'manage_riga':
        if (post('idriga') != null) {
            $riga = Riga::find(post('idriga'));
        } else {
            $riga = Riga::build($fattura);
        }

        $qta = post('qta');

        $riga->descrizione = post('descrizione');
        $riga->um = post('um') ?: null;

        $riga->id_iva = post('idiva');
        $riga->idconto = post('idconto');

        $riga->calcolo_ritenuta_acconto = post('calcolo_ritenuta_acconto') ?: null;
        $riga->id_ritenuta_acconto = post('id_ritenuta_acconto') ?: null;
        $riga->ritenuta_contributi = boolval(post('ritenuta_contributi'));
        $riga->id_rivalsa_inps = post('id_rivalsa_inps') ?: null;

        $riga->costo_unitario = post('costo_unitario') ?: 0;
        $riga->setPrezzoUnitario(post('prezzo_unitario'), post('idiva'));
        $riga->setSconto(post('sconto'), post('tipo_sconto'));

        $riga->qta = $qta;

        $riga->save();

        if (post('idriga') != null) {
            flash()->info(tr('Riga modificata!'));
        } else {
            flash()->info(tr('Riga aggiunta!'));
        }

        // Ricalcolo inps, ritenuta e bollo
        ricalcola_costiagg_fattura($id_record);

        break;

    case 'manage_descrizione':
        if (post('idriga') != null) {
            $riga = Descrizione::find(post('idriga'));
        } else {
            $riga = Descrizione::build($fattura);
        }

        $riga->descrizione = post('descrizione');

        $riga->save();

        if (post('idriga') != null) {
            flash()->info(tr('Riga descrittiva modificata!'));
        } else {
            flash()->info(tr('Riga descrittiva aggiunta!'));
        }

        break;

    // Scollegamento intervento da documento
    case 'unlink_intervento':
        if (!empty($id_record) && post('idriga') !== null) {
            $id_riga = post('idriga');
            $type = post('type');
            $riga = $fattura->getRiga($type, $id_riga);

            if (!empty($riga)) {
                try {
                    $riga->delete();

                    flash()->info(tr('Intervento _NUM_ rimosso!', [
                        '_NUM_' => $idintervento,
                    ]));
                } catch (InvalidArgumentException $e) {
                    flash()->error(tr('Errore durante l\'eliminazione della riga!'));
                }
            }
        }
        break;

    // Scollegamento riga generica da documento
    case 'delete_riga':
        $id_riga = post('riga_id');
        $type = post('riga_type');
        $riga = $fattura->getRiga($type, $id_riga);

        if (!empty($riga)) {
            try {
                $riga->delete();

                // Ricalcolo inps, ritenuta e bollo
                ricalcola_costiagg_fattura($id_record);

                flash()->info(tr('Riga rimossa!'));
            } catch (InvalidArgumentException $e) {
                flash()->error(tr('Alcuni serial number sono già stati utilizzati!'));
            }
        }
        break;

    case 'add_serial':
        $articolo = Articolo::find(post('idriga'));

        $serials = (array) post('serial');
        $articolo->serials = $serials;

        break;

    case 'update_position':
        $order = explode(',', post('order', true));

        foreach ($order as $i => $id_riga) {
            $dbo->query('UPDATE `co_righe_documenti` SET `order` = '.prepare($i + 1).' WHERE id='.prepare($id_riga));
        }

        break;

    // Aggiunta di un documento esterno
    case 'add_documento':
        $class = post('class');
        $id_documento = post('id_documento');
        $reversed = post('reversed');

        // Individuazione del documento originale
        if (!is_subclass_of($class, \Common\Document::class)) {
            return;
        }
        $documento = $class::find($id_documento);

        // Individuazione sede
        $id_sede = ($documento->direzione == 'entrata') ? $documento->idsede_destinazione : $documento->idsede_partenza;
        $id_sede = $id_sede ?: $documento->idsede;
        $id_sede = $id_sede ?: 0;

        // Creazione della fattura al volo
        if (post('create_document') == 'on') {
            $descrizione = ($documento->direzione == 'entrata') ? 'Fattura immediata di vendita' : 'Fattura immediata di acquisto';

            // Fattura differita in caso di importazione da DDT
            if ($documento instanceof DDT) {
                $descrizione = ($documento->direzione == 'entrata') ? 'Fattura differita di vendita' : 'Fattura differita di acquisto';
            }

            if ($reversed) {
                $tipo = Tipo::where('descrizione', 'Nota di credito')->where('dir', '!=', $documento->direzione)->first();
            } else {
                $tipo = Tipo::where('descrizione', $descrizione)->first();
            }

            $fattura = Fattura::build($documento->anagrafica, $tipo, post('data'), post('id_segment'));

            if (!empty($documento->idpagamento)) {
                $fattura->idpagamento = $documento->idpagamento;
            } else {
                $fattura->idpagamento = setting('Tipo di pagamento predefinito');
            }

            $fattura->idsede_destinazione = $documento->idsede;
            $fattura->id_ritenuta_contributi = post('id_ritenuta_contributi') ?: null;
            $fattura->idreferente = $documento->idreferente;

            $fattura->save();

            $id_record = $fattura->id;
        }

        if (!empty($documento->sconto_finale)) {
            $fattura->sconto_finale = $documento->sconto_finale;
        } elseif (!empty($documento->sconto_finale_percentuale)) {
            $fattura->sconto_finale_percentuale = $documento->sconto_finale_percentuale;
        }

        $fattura->save();

        $calcolo_ritenuta_acconto = post('calcolo_ritenuta_acconto') ?: null;
        $id_ritenuta_acconto = post('id_ritenuta_acconto') ?: null;
        $ritenuta_contributi = boolval(post('ritenuta_contributi'));
        $id_rivalsa_inps = post('id_rivalsa_inps') ?: null;
        $id_conto = post('id_conto');

        $righe = $documento->getRighe();
        foreach ($righe as $riga) {
            if (post('evadere')[$riga->id] == 'on') {
                $qta = post('qta_da_evadere')[$riga->id];
                $articolo = ArticoloOriginale::find($riga->idarticolo);

                $copia = $riga->copiaIn($fattura, $qta);

                $copia->id_conto = ($documento->direzione == 'entrata' ? ($articolo->idconto_vendita ?: $id_conto) : ($articolo->idconto_acquisto ?: $id_conto));
                $copia->calcolo_ritenuta_acconto = $calcolo_ritenuta_acconto;
                $copia->id_ritenuta_acconto = $id_ritenuta_acconto;
                $copia->id_rivalsa_inps = $id_rivalsa_inps;
                $copia->ritenuta_contributi = $ritenuta_contributi;

                // Aggiornamento seriali dalla riga dell'ordine
                if ($copia->isArticolo()) {
                    $serials = is_array(post('serial')[$riga->id]) ? post('serial')[$riga->id] : [];

                    $copia->serials = $serials;
                }

                $copia->save();
            }
        }

        // Modifica finale dello stato
        if (post('create_document') == 'on') {
            $fattura->idstatodocumento = post('id_stato');
            $fattura->save();
        }

        ricalcola_costiagg_fattura($id_record);

        // Messaggio informativo
        $message = tr('_DOC_ aggiunto!', [
            '_DOC_' => $documento->getReference(),
        ]);
        flash()->info($message);

        break;

    // Nota di credito
    case 'nota_credito':
        $id_documento = post('id_documento');
        $fattura = Fattura::find($id_documento);

        $id_segment = post('id_segment');
        $data = post('data');

        $anagrafica = $fattura->anagrafica;
        $tipo = Tipo::where('descrizione', 'Nota di credito')->where('dir', 'entrata')->first();

        $nota = Fattura::build($anagrafica, $tipo, $data, $id_segment);
        $nota->ref_documento = $fattura->id;
        $nota->idconto = $fattura->idconto;
        $nota->idpagamento = $fattura->idpagamento;
        $nota->id_banca_azienda = $fattura->id_banca_azienda;
        $nota->id_banca_controparte = $fattura->id_banca_controparte;
        $nota->idsede_partenza = $fattura->idsede_partenza;
        $nota->idsede_destinazione = $fattura->idsede_destinazione;
        $nota->split_payment = $fattura->split_payment;
        $nota->save();

        $righe = $fattura->getRighe();
        foreach ($righe as $riga) {
            if (post('evadere')[$riga->id] == 'on' and !empty(post('qta_da_evadere')[$riga->id])) {
                $qta = post('qta_da_evadere')[$riga->id];

                $copia = $riga->copiaIn($nota, $qta);
                $copia->ref_riga_documento = $riga->id;

                // Aggiornamento seriali dalla riga dell'ordine
                if ($copia->isArticolo()) {
                    $serials = is_array(post('serial')[$riga->id]) ? post('serial')[$riga->id] : [];

                    $copia->serials = $serials;
                }

                $copia->save();
            }
        }

        $id_record = $nota->id;
        aggiorna_sedi_movimenti('documenti', $id_record);

        break;

    case 'transform':
        $fattura->id_segment = post('id_segment');
        $fattura->data = post('data');
        $fattura->save();

        break;
}

// Nota di debito
if (get('op') == 'nota_addebito') {
    $rs_segment = $dbo->fetchArray("SELECT * FROM zz_segments WHERE predefined_addebito='1'");
    if (!empty($rs_segment)) {
        $id_segment = $rs_segment[0]['id'];
    } else {
        $id_segment = $record['id_segment'];
    }

    $anagrafica = $fattura->anagrafica;
    $tipo = Tipo::where('descrizione', 'Nota di debito')->where('dir', 'entrata')->first();
    $data = $fattura->data;

    $nota = Fattura::build($anagrafica, $tipo, $data, $id_segment);
    $nota->ref_documento = $fattura->id;
    $nota->idconto = $fattura->idconto;
    $nota->idpagamento = $fattura->idpagamento;
    $nota->id_banca_azienda = $fattura->id_banca_azienda;
    $nota->id_banca_controparte = $fattura->id_banca_controparte;
    $nota->idsede_partenza = $fattura->idsede_partenza;
    $nota->idsede_destinazione = $fattura->idsede_destinazione;
    $nota->save();

    $id_record = $nota->id;
    aggiorna_sedi_movimenti('documenti', $id_record);
}
