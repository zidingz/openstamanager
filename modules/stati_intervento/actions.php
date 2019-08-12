<?php

switch (post('op')) {
    case 'update':
        $dbo->update('in_statiintervento', [
            'descrizione' => post('descrizione'),
            'colore' => post('colore'),
            'completato' => post('completato'),
            'notifica' => post('notifica'),
            'id_email' => post('email') ?: null,
            'destinatari' => post('destinatari'),
        ], ['id' => $id_record]);

        flash()->info(tr('Informazioni salvate correttamente.'));

        break;

    case 'add':
        $codice = post('codice');
        $descrizione = post('descrizione');
        $colore = post('colore');

        //controllo id_stato che non sia duplicato
        if (count($dbo->fetchArray('SELECT id FROM in_statiintervento WHERE id='.prepare($id_stato))) > 0) {
            flash()->error(tr('Stato di intervento già esistente.'));
        } else {
            $query = 'INSERT INTO in_statiintervento(codice, descrizione, colore) VALUES ('.prepare($codice).', '.prepare($descrizione).', '.prepare($colore).')';
            $dbo->query($query);
            $id_record = $database->lastInsertedID();
            flash()->info(tr('Nuovo stato di intervento aggiunto.'));
        }

        break;

    case 'delete':

        //scelgo se settare come eliminato o cancellare direttamente la riga se non è stato utilizzato negli interventi
        if (count($dbo->fetchArray('SELECT id FROM in_interventi WHERE id_stato='.prepare($id_record))) > 0) {
            $query = 'UPDATE in_statiintervento SET deleted_at = NOW() WHERE id_stato='.prepare($id_record).' AND `can_delete`=1';
        } else {
            $query = 'DELETE FROM in_statiintervento  WHERE id_stato='.prepare($id_record).' AND `can_delete`=1';
        }

        $dbo->query($query);

        flash()->info(tr('Stato di intervento eliminato.'));

        break;
}
