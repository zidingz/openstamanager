<?php

use Modules\DDT\DDT;

$documento = DDT::find($id_record);

$id_cliente = $documento['idanagrafica'];
$id_sede = $record['idsede_partenza'];

$pagamento = $dbo->fetchOne('SELECT * FROM co_pagamenti WHERE id = '.prepare($documento['idpagamento']));
$causale = $dbo->fetchOne('SELECT * FROM dt_causalet WHERE id = '.prepare($documento['idcausalet']));
$porto = $dbo->fetchOne('SELECT * FROM dt_porto WHERE id = '.prepare($documento['idporto']));
$aspetto_beni = $dbo->fetchOne('SELECT * FROM dt_aspettobeni WHERE id = '.prepare($documento['idaspettobeni']));
$spedizione = $dbo->fetchOne('SELECT * FROM dt_spedizione WHERE id = '.prepare($documento['idspedizione']));

$vettore = $dbo->fetchOne('SELECT ragione_sociale FROM an_anagrafiche WHERE idanagrafica = '.prepare($documento['idvettore']));

$tipo_doc = $documento->tipo->descrizione;
if (empty($documento['numero_esterno'])) {
    $numero = 'pro-forma '.$numero;
    $tipo_doc = tr('DDT pro-forma', [], ['upper' => true]);
} else {
    $numero = !empty($documento['numero_esterno']) ? $documento['numero_esterno'] : $documento['numero'];
}

// Leggo i dati della destinazione (se 0=sede legale, se!=altra sede da leggere da tabella an_sedi)
$destinazione = '';
if (!empty($documento['idsede_destinazione'])) {
    $rsd = $dbo->fetchArray('SELECT (SELECT codice FROM an_anagrafiche WHERE idanagrafica=an_sedi.idanagrafica) AS codice, (SELECT ragione_sociale FROM an_anagrafiche WHERE idanagrafica=an_sedi.idanagrafica) AS ragione_sociale, nomesede, indirizzo, indirizzo2, cap, citta, provincia, piva, codice_fiscale FROM an_sedi WHERE idanagrafica='.prepare($id_cliente).' AND id='.prepare($documento['idsede_destinazione']));

    if (!empty($sede['nomesede'])) {
        $destinazione .= $sede['nomesede'].'<br/>';
    }
    if (!empty($sede['indirizzo'])) {
        $destinazione .= $sede['indirizzo'].'<br/>';
    }
    if (!empty($sede['indirizzo2'])) {
        $destinazione .= $sede['indirizzo2'].'<br/>';
    }
    if (!empty($sede['cap'])) {
        $destinazione .= $sede['cap'].' ';
    }
    if (!empty($sede['citta'])) {
        $destinazione .= $sede['citta'];
    }
    if (!empty($sede['provincia'])) {
        $destinazione .= ' ('.$sede['provincia'].')';
    }
}

// Sostituzioni specifiche
$custom = [
    'tipo_doc' => $tipo_doc,
    'numero' => $numero,
    'data' => dateFormat($documento['data']),
    'pagamento' => $pagamento['descrizione'],
    'c_destinazione' => $destinazione,
    'aspettobeni' => $aspetto_beni['descrizione'],
    'causalet' => $causale['descrizione'],
    'porto' => $porto['descrizione'],
    'n_colli' => !empty($documento['n_colli']) ? $documento['n_colli'] : '',
    'spedizione' => $spedizione['descrizione'],
    'vettore' => $vettore['ragione_sociale'],
];

// Accesso solo a:
// - cliente se è impostato l'idanagrafica di un Cliente
// - utente qualsiasi con permessi almeno in lettura sul modulo
// - admin
if ((Auth::user()['gruppo'] == 'Clienti' && $id_cliente != Auth::user()['idanagrafica'] && !Auth::admin()) || $documento->module->permission == '-') {
    die(tr('Non hai i permessi per questa stampa!'));
}
