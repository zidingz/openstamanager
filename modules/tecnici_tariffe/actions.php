<?php

include_once __DIR__.'/../../core.php';

$module_name = 'Tecnici e tariffe';

switch (post('op')) {
    case 'update':
        $n_errors = 0;

        foreach (post('costo_ore') as $idtecnico => $arr2) {
            foreach ($arr2 as $idtipointervento => $value) {
                $costo_ore = post('costo_ore')[$idtecnico][$idtipointervento];
                $costo_km = post('costo_km')[$idtecnico][$idtipointervento];
                $costo_dirittochiamata = post('costo_dirittochiamata')[$idtecnico][$idtipointervento];

                $costo_ore_tecnico = post('costo_ore_tecnico')[$idtecnico][$idtipointervento];
                $costo_km_tecnico = post('costo_km_tecnico')[$idtecnico][$idtipointervento];
                $costo_dirittochiamata_tecnico = post('costo_dirittochiamata_tecnico')[$idtecnico][$idtipointervento];

                // Se c'è già un record idtecnico-idtipointervento lo aggiorno, altrimenti lo creo (retrocompatibilità quanto i costi erano legati ai tipi di intervento)
                $rs = $dbo->fetchArray('SELECT id FROM in_tariffe WHERE idtecnico='.prepare($idtecnico).' AND idtipointervento='.prepare($idtipointervento));

                // Aggiorno il record
                if (count($rs) == 1) {
                    $query = 'UPDATE in_tariffe SET '
                    .' costo_ore='.prepare($costo_ore).', '
                    .' costo_km='.prepare($costo_km).', '
                    .' costo_dirittochiamata='.prepare($costo_dirittochiamata).', '
                    .' costo_ore_tecnico='.prepare($costo_ore_tecnico).', '
                    .' costo_km_tecnico='.prepare($costo_km_tecnico).', '
                    .' costo_dirittochiamata_tecnico='.prepare($costo_dirittochiamata_tecnico)
                    .' WHERE idtipointervento='.prepare($idtipointervento).' AND idtecnico='.prepare($idtecnico);
                }

                // Nuovo record
                else {
                    $query = 'INSERT INTO in_tariffe(idtecnico, idtipointervento, costo_ore, costo_km, costo_dirittochiamata, costo_ore_tecnico, costo_km_tecnico, costo_dirittochiamata_tecnico) VALUES ('.prepare($idtecnico).', '.prepare($idtipointervento).', '.prepare($costo_ore).', '.prepare($costo_km).', '.prepare($costo_dirittochiamata).', '.prepare($costo_ore_tecnico).', '.prepare($costo_km_tecnico).', '.prepare($costo_dirittochiamata_tecnico).')';
                }

                if (!$dbo->query($query)) {
                    ++$n_errors;
                }
            }
        }

        if ($n_errors == 0) {
            $_SESSION['infos'][] = tr('Informazioni salvate correttamente!');
        } else {
            $_SESSION['errors'][] = tr('Errore durante il salvataggio delle tariffe!');
        }

        break;

    case 'import':
        $rs = $dbo->fetchArray('SELECT id FROM in_tariffe WHERE idtecnico = '.prepare(post('idtecnico')).' AND idtipointervento='.prepare(post('idtipointervento')));

        // Se la riga delle tariffe esiste, la aggiorno...
        if (!empty($rs)) {
            $costi = $dbo->select('in_tipiintervento', '*', [
                'id' => post('idtipointervento'),
            ])[0];

            $result = $dbo->update('in_tariffe', [
                'costo_ore' => $costi['costo_orario'],
                'costo_km' => $costi['costo_km'],
                'costo_dirittochiamata' => $costi['costo_diritto_chiamata'],
                'costo_ore_tecnico' => $costi['costo_orario_tecnico'],
                'costo_km_tecnico' => $costi['costo_km_tecnico'],
                'costo_dirittochiamata_tecnico' => $costi['costo_diritto_chiamata_tecnico'],
            ], [
                'idtecnico' => post('idtecnico'),
                'idtipointervento' => post('idtipointervento'),
            ]);

            if ($result) {
                $_SESSION['infos'][] = tr('Informazioni salvate correttamente!');
            } else {
                $_SESSION['errors'][] = tr("Errore durante l'importazione tariffe!");
            }
        }

        // ...altrimenti la creo
        else {
            $costi = $dbo->select('in_tipiintervento', '*', [
                'id' => post('idtipointervento'),
            ])[0];

            $result = $dbo->insert('in_tariffe', [
                'idtecnico' => post('idtecnico'),
                'idtipointervento' => post('idtipointervento'),
                'costo_ore' => $costi['costo_orario'],
                'costo_km' => $costi['costo_km'],
                'costo_dirittochiamata' => $costi['costo_diritto_chiamata'],
                'costo_ore_tecnico' => $costi['costo_orario_tecnico'],
                'costo_km_tecnico' => $costi['costo_km_tecnico'],
                'costo_dirittochiamata_tecnico' => $costi['costo_diritto_chiamata_tecnico'],
            ]);

            if ($result) {
                $_SESSION['infos'][] = tr('Informazioni salvate correttamente!');
            } else {
                $_SESSION['errors'][] = tr("Errore durante l'importazione tariffe!");
            }
        }

        break;
}
