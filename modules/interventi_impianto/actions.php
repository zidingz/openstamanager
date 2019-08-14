<?php


$matricole = (array) post('matricole');

// Salvo gli impianti selezionati
if (filter('op') == 'link_myimpianti') {
    $matricole_old = $dbo->fetchArray('SELECT * FROM my_impianti_interventi WHERE idintervento='.prepare($id_record));
    $matricole_old = array_column($matricole_old, 'idimpianto');

    // Individuazione delle matricole mancanti
    foreach ($matricole_old as $matricola) {
        if (!in_array($matricola, $matricole)) {
            $dbo->query('DELETE FROM my_impianti_interventi WHERE idintervento='.prepare($id_record).' AND idimpianto = '.prepare($matricola));

            $components = $dbo->fetchArray('SELECT * FROM my_impianto_componenti WHERE idimpianto = '.prepare($matricola));
            if (!empty($components)) {
                foreach ($components as $component) {
                    $dbo->query('DELETE FROM my_componenti_interventi WHERE id_componente = '.prepare($component['id']).' AND id_intervento = '.prepare($id_record));
                }
            }
        }
    }

    foreach ($matricole as $matricola) {
        if (!in_array($matricola, $matricole_old)) {
            $dbo->query('INSERT INTO my_impianti_interventi(idimpianto, idintervento) VALUES('.prepare($matricola).', '.prepare($id_record).')');
        }
    }

    flash()->info(tr('Informazioni impianti salvate!'));
} elseif (filter('op') == 'link_componenti') {
    $components = (array) post('componenti');
    $id_impianto = post('id_impianto');

    $dbo->query('DELETE FROM my_componenti_interventi WHERE id_componente IN (SELECT id FROM my_impianto_componenti WHERE idimpianto = '.prepare($id_impianto).') AND id_intervento = '.prepare($id_record));

    foreach ($components as $component) {
        $dbo->query('INSERT INTO my_componenti_interventi(id_componente, id_intervento) VALUES ('.prepare($component).', '.prepare($id_record).')');
    }

    flash()->info(tr('Informazioni componenti salvate!'));
}
