<?php

include_once __DIR__.'/core.php';

// Lettura parametri iniziali
if (!empty($id_plugin)) {
    $info = Plugins::get($id_plugin);

    $directory = '/plugins/'.$info['directory'];
    $permesso = $info['idmodule_to'];
    $id_module = $info['idmodule_to'];
} else {
    $info = Modules::get($id_module);

    $directory = '/modules/'.$info['directory'];
    $permesso = $id_module;
}

$upload_dir = DOCROOT.'/'.Uploads::getDirectory($id_module, $id_plugin);

$dbo->query('START TRANSACTION');

// GESTIONE UPLOAD
if (filter('op') == 'link_file' || filter('op') == 'unlink_file') {
    // Controllo sui permessi di scrittura per il modulo
    if (Modules::getPermission($id_module) != 'rw') {
        App::flash()->error(tr('Non hai permessi di scrittura per il modulo _MODULE_', [
            '_MODULE_' => '"'.Modules::get($id_module)['name'].'"',
        ]));
    }

    // Controllo sui permessi di scrittura per il file system
    elseif (!directory($upload_dir)) {
        App::flash()->error(tr('Non hai i permessi di scrittura nella cartella _DIR_!', [
            '_DIR_' => '"files"',
        ]));
    }

    // Gestione delle operazioni
    else {
        // UPLOAD
        if (filter('op') == 'link_file' && !empty($_FILES) && !empty($_FILES['blob']['name'])) {
            $upload = Uploads::upload($_FILES['blob'], [
                'name' => filter('nome_allegato'),
                'category' => filter('categoria'),
                'id_module' => $id_module,
                'id_plugin' => $id_plugin,
                'id_record' => $id_record,
            ]);

            // Creazione file fisico
            if (!empty($upload)) {
                App::flash()->info(tr('File caricato correttamente!'));
            } else {
                App::flash()->error(tr('Errore durante il caricamento del file!'));
            }
        }

        // DELETE
        elseif (filter('op') == 'unlink_file' && filter('filename') !== null) {
            $name = Uploads::delete(filter('filename'), [
                'id_module' => $id_module,
                'id_plugin' => $id_plugin,
                'id_record' => $id_record,
            ]);

            if (!empty($name)) {
                App::flash()->info(tr('File _FILE_ eliminato!', [
                    '_FILE_' => '"'.$name.'"',
                ]));
            } else {
                App::flash()->error(tr("Errore durante l'eliminazione del file!"));
            }
        }

        redirect(ROOTDIR.'/editor.php?id_module='.$id_module.'&id_record='.$id_record.((!empty($options['id_plugin'])) ? '#tab_'.$options['id_plugin'] : ''));
    }
} elseif (filter('op') == 'download_file') {
    $rs = $dbo->fetchArray('SELECT * FROM zz_files WHERE id_module='.prepare($id_module).' AND id='.prepare(filter('id')).' AND filename='.prepare(filter('filename')));

    download($upload_dir.'/'.$rs[0]['filename'], $rs[0]['original']);
} elseif (post('op') == 'send-email') {
    $template = Mail::getTemplate($post['template']);
    $id_account = $template['id_smtp'];

    // Informazioni di log
    Filter::set('get', 'id_email', $template['id']);

    $mail = new Mail($id_account);

    // Conferma di lettura
    if (!empty($post['read_notify'])) {
        $mail->ConfirmReadingTo = $mail->From;
    }

    // Reply To
    if (!empty($template['reply_to'])) {
        $mail->AddReplyTo($template['reply_to']);
    }

    // CC
    if (!empty($template['cc'])) {
        $mail->AddCC($template['cc']);
    }

    // BCC
    if (!empty($template['bcc'])) {
        $mail->AddBCC($template['bcc']);
    }

    // Destinatari
    $mail->addReceivers($post['destinatari'], $post['tipo_destinatari']);

    // Oggetto
    $mail->Subject = $post['subject'];

    // Allegati
    $mail->attach($post['prints'], $post['attachments']);

    // Contenuto
    $mail->Body = $post['body'];

    // Invio mail
    if (!$mail->send()) {
        App::flash()->error(tr("Errore durante l'invio dell'email").': '.$mail->ErrorInfo);
    } else {
        App::flash()->info(tr('Email inviata correttamente!'));
    }
}

if (Modules::getPermission($permesso) == 'r' || Modules::getPermission($permesso) == 'rw') {
    // Inclusione di eventuale plugin personalizzato
    if (!empty($info['script'])) {
        include App::filepath('modules/'.$info['module_dir'].'/plugins|custom|', $info['script']);

        $dbo->query('COMMIT');

        return;
    }

    // Caricamento helper modulo (verifico se ci sono helper personalizzati)
    include_once App::filepath($directory.'|custom|', 'modutil.php');

    // Lettura risultato query del modulo
    include App::filepath($directory.'|custom|', 'init.php');

    if (Modules::getPermission($permesso) == 'rw') {
        // Esecuzione delle operazioni di gruppo
        $id_records = post('id_records');
        $id_records = is_array($id_records) ? $id_records : explode(';', $id_records);
        $id_records = array_filter($id_records, function ($var) {return !empty($var); });
        $id_records = array_unique($id_records);

        $bulk = include App::filepath($directory.'|custom|', 'bulk.php');
        $bulk = empty($bulk) ? [] : $bulk;

        if (in_array(post('op'), array_keys($bulk))) {
            redirect(ROOTDIR.'/controller.php?id_module='.$id_module, 'js');
        } else {
            // Esecuzione delle operazioni del modulo
            include App::filepath($directory.'|custom|', 'actions.php');

            // Operazioni generiche per i campi personalizzati
            if (post('op') != null) {
                $query = 'SELECT `id`, `name` FROM `zz_fields` WHERE ';
                if (!empty($id_plugin)) {
                    $query .= '`id_plugin` = '.prepare($id_plugin);
                } else {
                    $query .= '`id_module` = '.prepare($id_module);
                }
                $customs = $dbo->fetchArray($query);

                if (!starts_with(post('op'), 'delete')) {
                    $values = [];
                    foreach ($customs as $custom) {
                        if (isset($post[$custom['name']])) {
                            $values[$custom['id']] = $post[$custom['name']];
                        }
                    }

                    // Inserimento iniziale
                    if (starts_with(post('op'), 'add')) {
                        // Informazioni di log
                        Filter::set('get', 'id_record', $id_record);

                        foreach ($values as $key => $value) {
                            $dbo->insert('zz_field_record', [
                                'id_record' => $id_record,
                                'id_field' => $key,
                                'value' => $value,
                            ]);
                        }
                    }

                    // Aggiornamento
                    elseif (starts_with(post('op'), 'update')) {
                        foreach ($values as $key => $value) {
                            $dbo->update('zz_field_record', [
                            'value' => $value,
                        ], [
                            'id_record' => $id_record,
                            'id_field' => $key,
                        ]);
                        }
                    }
                }

                // Eliminazione
                elseif (!empty($customs)) {
                    $dbo->query('DELETE FROM `zz_field_record` WHERE `id_record` = '.prepare($id_record).' AND `id_field` IN ('.implode(array_column($customs, 'id')).')');
                }
            }
        }
    }
}

$dbo->query('COMMIT');
