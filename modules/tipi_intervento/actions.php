<?php

include_once __DIR__.'/../../core.php';

switch (post('op')) {
    case 'update':
        $descrizione = post('descrizione');
        $costo_orario = post('costo_orario');
        $costo_km = post('costo_km');
        $costo_diritto_chiamata = post('costo_diritto_chiamata');

        $costo_orario_tecnico = post('costo_orario_tecnico');
        $costo_km_tecnico = post('costo_km_tecnico');
        $costo_diritto_chiamata_tecnico = post('costo_diritto_chiamata_tecnico');

        $tempo_standard = empty(post('tempo_standard')) ? null : round((force_decimal($_POST['tempo_standard']) / 2.5), 1) * 2.5;

        $dbo->update('in_tipiintervento', [
            'descrizione' => $descrizione,
            'costo_orario' => $costo_orario,
            'costo_km' => $costo_km,
            'costo_diritto_chiamata' => $costo_diritto_chiamata,
            'costo_orario_tecnico' => $costo_orario_tecnico,
            'costo_km_tecnico' => $costo_km_tecnico,
            'costo_diritto_chiamata_tecnico' => $costo_diritto_chiamata_tecnico,
            'tempo_standard' => $tempo_standard,
        ], [
            'id' => post('id_record'),
        ]);

        $_SESSION['infos'][] = tr('Informazioni tipo intervento salvate correttamente!');

        break;

    case 'add':
        $idtipointervento = post('idtipointervento');
        $descrizione = post('descrizione');

        $tempo_standard = empty(post('tempo_standard')) ? null : round((force_decimal($_POST['tempo_standard']) / 2.5), 1) * 2.5;

        $dbo->insert('in_tipiintervento', [
            'id' => $idtipointervento,
            'descrizione' => $descrizione,
            'costo_orario' => '0.00',
            'costo_km' => '0.00',
            'tempo_standard' => $tempo_standard,
        ]);

        $id_record = $idtipointervento;

        $_SESSION['infos'][] = tr('Nuovo tipo di intervento aggiunto!');

        break;

    case 'delete':
        $query = 'DELETE FROM in_tipiintervento WHERE id='.prepare($id_record);
        $dbo->query($query);

        // Elimino anche le tariffe collegate ai vari tecnici
        $query = 'DELETE FROM in_tariffe WHERE idtipointervento='.prepare($id_record);
        $dbo->query($query);

        $_SESSION['infos'][] = tr('Tipo di intervento eliminato!');
        break;
}
