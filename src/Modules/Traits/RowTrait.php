<?php

namespace Modules\Traits;

/**
 * Trait dedicato alla gestione delle operazioni di visualizzazione per i template di modifica e aggiunta righe.
 *
 * @since 2.5
 */
trait RowTrait
{
    protected function rowAdd($request, $response, $args)
    {
        $type = 'riga';
        if (get('is_descrizione') !== null) {
            $type = 'descrizione';
        } elseif (get('is_articolo') !== null) {
            $type = 'articolo';
        } elseif (get('is_sconto') !== null) {
            $type = 'sconto';
        }

        $documento = $this->getDocument($args);
        $options = $this->getOptions($type, $documento, $args);
        $options['action'] = 'add';

        $result = $this->prepareAdd($documento, $options);
        $result['id_ritenuta_acconto'] = $options['id_ritenuta_acconto_anagrafica'] ?: $result['id_ritenuta_acconto'];

        // Aggiunta sconto di default da listino per le vendite
        if ($documento->direzione == 'entrata' && $type == 'articolo') {
            $listino = database()->fetchOne('SELECT prc_guadagno FROM an_anagrafiche INNER JOIN mg_listini ON an_anagrafiche.idlistino_vendite=mg_listini.id WHERE idanagrafica='.prepare($documento['idanagrafica']));

            if (!empty($listino['prc_guadagno'])) {
                $result['sconto_unitario'] = $listino['prc_guadagno'];
                $result['tipo_sconto'] = 'PRC';
            }
        }

        return $this->render($type, $options, $result, $response, $args);
    }

    protected function rowEdit($request, $response, $args)
    {
        $documento = $this->getDocument($args);

        // Dati della riga
        $riga = $documento->getRighe()->find($args['params']);

        $type = 'riga';
        if ($riga->isDescrizione()) {
            $type = 'descrizione';
        } elseif ($riga->isArticolo()) {
            $type = 'articolo';
        } elseif ($riga->isSconto()) {
            $type = 'sconto';
        }

        $result = $riga->toArray();
        $result['prezzo'] = $riga->prezzo_unitario_vendita;

        $options = $this->getOptions($type, $documento, $args);
        $options['action'] = 'edit';

        return $this->render($type, $options, $result, $response, $args);
    }

    private function getDocument($args)
    {
        $class = $this->getMainClass();
        $documento = $class::find($args['id_record']);

        return $documento;
    }

    private function getOptions($type, $documento, $args)
    {
        // Lettura della ritenuta d'acconto predefinita per l'anagrafica
        $ritenuta_acconto = $this->database->fetchOne('SELECT id_ritenuta_acconto_'.($documento->direzione == 'uscita' ? 'acquisti' : 'vendite').' AS id_ritenuta_acconto FROM an_anagrafiche WHERE idanagrafica='.prepare($documento['idanagrafica']));
        $id_ritenuta_acconto_anagrafica = $ritenuta_acconto['id_ritenuta_acconto'];

        // Impostazioni predefinite per la gestione
        $options = [
            'op' => 'manage_'.$type,
            'dir' => $documento->direzione,
            'conti' => $documento->direzione == 'entrata' ? 'conti-vendite' : 'conti-acquisti',
            'idanagrafica' => $documento['idanagrafica'],
            'totale_imponibile' => $documento->totale_imponibile,
            'show-ritenuta-contributi' => !empty($documento['id_ritenuta_contributi']),
            'show-conto' => false,
            'id_ritenuta_acconto_anagrafica' => $id_ritenuta_acconto_anagrafica,
        ];

        // Informazioni aggiuntive per Fatture
        $module = $args['module'];
        if (in_array($module['name'], ['Fatture di acquisto', 'Fatture di vendita'])) {
            $show_rivalsa = 1;
            $show_ritenuta_acconto = 1;
            if ($options['dir'] == 'entrata') {
                $show_rivalsa = !empty(setting('Percentuale rivalsa')) || !empty($result['idrivalsainps']);
                $show_ritenuta_acconto = !empty(setting("Percentuale ritenuta d'acconto")) || !empty($result['idritenutaacconto']);
                $show_ritenuta_acconto |= !empty($id_ritenuta_acconto_anagrafica);
            }

            $options = array_merge($options, [
                'show-rivalsa-inps' => $show_rivalsa,
                'show-ritenuta-acconto' => $show_ritenuta_acconto,
                'show-calcolo-ritenuta-acconto' => $show_ritenuta_acconto,
            ]);
        }

        return array_merge($options, $this->rowOptions);
    }

    private function render($type, $options, $result, $response, $args)
    {
        // Impostazioni specifiche per il modulo
        $args['options'] = array_merge($options, $this->rowOptions);
        $args['result'] = $result;

        return $this->twig->render($response, 'components/'.$type.'.twig', $args);
    }

    /**
     * Definisce le caratteristiche di base della nuova riga.
     *
     * @param $documento
     * @param $options
     *
     * @return array
     */
    private function prepareAdd($documento, $options)
    {
        $dir = $documento->direzione;
        $idconto = $documento->idconto;

        // Conto dalle impostazioni
        if (empty($idconto)) {
            $idconto = $dir == 'entrata' ? setting('Conto predefinito fatture di vendita') : setting('Conto predefinito fatture di acquisto');
        }

        // Leggo l'iva predefinita per l'anagrafica e se non c'è leggo quella predefinita generica
        $iva = $this->database->fetchOne('SELECT idiva_'.($dir == 'uscita' ? 'acquisti' : 'vendite').' AS idiva FROM an_anagrafiche WHERE idanagrafica='.prepare($documento['idanagrafica']));
        $result['idiva'] = $iva['idiva'] ?: setting('Iva predefinita');

        // Aggiunta sconto di default da listino per le vendite
        $listino = $this->database->fetchOne('SELECT prc_guadagno FROM an_anagrafiche INNER JOIN mg_listini ON an_anagrafiche.idlistino_'.($dir == 'uscita' ? 'acquisti' : 'vendite').'=mg_listini.id WHERE idanagrafica='.prepare($documento['idanagrafica']));

        if ($listino['prc_guadagno'] > 0) {
            $result['sconto_unitario'] = $listino['prc_guadagno'];
            $result['tipo_sconto'] = 'PRC';
        }

        // Fattura di acquisto
        if ($dir == 'uscita') {
            // TODO: Luca S. questi campi non dovrebbero essere definiti all'interno della scheda fornitore?
            $id_rivalsa_inps = '';
            $id_ritenuta_acconto = '';
        }

        // Fattura di vendita
        elseif ($dir == 'entrata') {
            // Caso particolare per aggiunta articolo
            $id_rivalsa_inps = $options['op'] == 'manage_articolo' ? '' : setting('Percentuale rivalsa');

            $id_ritenuta_acconto = setting("Percentuale ritenuta d'acconto");
        }

        // Dati di default
        return [
            'descrizione' => '',
            'qta' => 1,
            'um' => '',
            'prezzo' => 0,
            'sconto_unitario' => 0,
            'tipo_sconto' => '',
            'idiva' => '',
            'idconto' => $idconto,
            'ritenuta_contributi' => true,
            'id_rivalsa_inps' => $id_rivalsa_inps,
            'id_ritenuta_acconto' => $id_ritenuta_acconto,
            'calcolo_ritenuta_acconto' => setting("Metodologia calcolo ritenuta d'acconto predefinito"),
        ];
    }
}
