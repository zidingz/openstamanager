<?php

switch (filter('op')) {
    case 'updatecomponente':
        $idcomponente = get('id');
        $data = post('data_componente', true);

        // Ricavo il valore di contenuto leggendolo dal db
        $query = 'SELECT * FROM my_impianto_componenti WHERE idimpianto='.prepare($id_record).' AND id='.prepare($idcomponente);
        $rs = $dbo->fetchArray($query);
        $contenuto = $rs[0]['contenuto'];

        $contenuto = \Util\Ini::write($contenuto, $post);

        $query = 'UPDATE my_impianto_componenti SET data='.prepare($data).', contenuto='.prepare($contenuto).' WHERE idimpianto='.prepare($id_record).' AND id='.prepare($idcomponente);
        $dbo->query($query);

        flash()->info(tr('Informazioni componente aggiornate correttamente!'));

        $_SESSION['idcomponente'] = $idcomponente;
        break;

    case 'linkcomponente':
        $filename = get('filename');

        if (!empty($filename)) {
            $contenuto = file_get_contents(DOCROOT.'/files/my_impianti/'.$filename);
            $nome = \Util\Ini::getValue(\Util\Ini::readFile(DOCROOT.'/files/my_impianti/'.$filename), 'Nome');

            $query = 'INSERT INTO my_impianto_componenti(filename, idimpianto, contenuto, nome, data) VALUES('.prepare($filename).', '.prepare($id_record).', '.prepare($contenuto).', '.prepare($nome).', NOW())';
            $dbo->query($query);

            $idcomponente = $dbo->lastInsertedID();
            $_SESSION['idcomponente'] = $idcomponente;

            flash()->info(tr("Aggiunto un nuovo componente all'impianto!"));
        }
        break;

    case 'sostituiscicomponente':
        $filename = get('filename');
        $id = get('id');

        $nome = \Util\Ini::getValue(\Util\Ini::readFile(DOCROOT.'/files/my_impianti/'.$filename), 'Nome');
        $contenuto = file_get_contents(DOCROOT.'/files/my_impianti/'.$filename);

        // Verifico che questo componente non sia già stato sostituito
        $query = 'SELECT * FROM my_impianto_componenti WHERE idsostituto = '.prepare($id);
        $rs = $dbo->fetchArray($query);

        if (empty($rs)) {
            // Inserisco il nuovo componente in sostituzione
            $query = 'INSERT INTO my_impianto_componenti(idsostituto, filename, idimpianto, contenuto, nome, data) VALUES('.prepare($id).', '.prepare($filename).', '.prepare($id_record).', '.prepare($contenuto).', '.prepare($nome).', NOW())';
            $dbo->query($query);

            $idcomponente = $dbo->lastInsertedID();
            $_SESSION['idcomponente'] = $idcomponente;

            // Aggiorno la data di sostituzione del componente precedente
            $query = 'UPDATE my_impianto_componenti SET data_sostituzione = NOW() WHERE idimpianto = '.prepare($id_record).' AND id = '.prepare($id);
            $dbo->query($query);

            flash()->info(tr('Aggiunto un nuovo componente in sostituzione al precedente!'));
        } else {
            flash()->error(tr('Questo componente è già stato sostituito!').' '.('Nessuna modifica applicata'));
        }
        break;

    case 'unlinkcomponente':
        $idcomponente = filter('id');

        $query = 'DELETE FROM my_impianto_componenti WHERE id='.prepare($idcomponente).' AND idimpianto='.prepare($id_record);
        $dbo->query($query);

        flash()->info(tr("Rimosso componente dall'impianto!"));
        break;
}
