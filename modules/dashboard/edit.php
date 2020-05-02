<?php

// Impostazione filtri di default a tutte le selezioni la prima volta
if (!isset($_SESSION['dashboard']['idtecnici'])) {
    $rs = $dbo->fetchArray("SELECT an_anagrafiche.idanagrafica AS id FROM an_anagrafiche INNER JOIN (an_tipianagrafiche_anagrafiche INNER JOIN an_tipianagrafiche ON an_tipianagrafiche_anagrafiche.id_tipo_anagrafica=an_tipianagrafiche.id) ON an_anagrafiche.idanagrafica=an_tipianagrafiche_anagrafiche.idanagrafica WHERE deleted_at IS NULL AND descrizione='Tecnico'");

    $_SESSION['dashboard']['idtecnici'] = ["'-1'"];

    for ($i = 0; $i < count($rs); ++$i) {
        $_SESSION['dashboard']['idtecnici'][] = "'".$rs[$i]['id']."'";
    }
}

if (!isset($_SESSION['dashboard']['idstatiintervento'])) {
    $rs = $dbo->fetchArray('SELECT id, descrizione FROM in_statiintervento WHERE deleted_at IS NULL');

    $_SESSION['dashboard']['idstatiintervento'] = ["'-1'"];

    for ($i = 0; $i < count($rs); ++$i) {
        $_SESSION['dashboard']['idstatiintervento'][] = "'".$rs[$i]['id']."'";
    }
}

if (!isset($_SESSION['dashboard']['idtipiintervento'])) {
    $rs = $dbo->fetchArray('SELECT id, descrizione FROM in_tipiintervento');

    $_SESSION['dashboard']['idtipiintervento'] = ["'-1'"];

    for ($i = 0; $i < count($rs); ++$i) {
        $_SESSION['dashboard']['idtipiintervento'][] = "'".$rs[$i]['id']."'";
    }
}

if (!isset($_SESSION['dashboard']['idzone'])) {
    $rs = $dbo->fetchArray('SELECT id, descrizione FROM an_zone');

    $_SESSION['dashboard']['idzone'] = ["'-1'"];

    // "Nessuna zona" di default
    $_SESSION['dashboard']['idzone'][] = "'0'";

    for ($i = 0; $i < count($rs); ++$i) {
        $_SESSION['dashboard']['idzone'][] = "'".$rs[$i]['id']."'";
    }
}

echo '
<!-- Filtri -->
<div class="row">';

echo '
	<!-- STATI INTERVENTO -->
	<div class="dropdown col-md-3" id="dashboard_stati">
		<button type="button" class="btn btn-block counter_object" data-toggle="dropdown">
            <i class="fa fa-filter"></i> '.tr('Stati attività').'
            (<span class="selected_counter"></span>/<span class="total_counter"></span>) <i class="caret"></i>
        </button>

		<ul class="dropdown-menu" role="menu">';

// Stati intervento
$stati_intervento = $dbo->fetchArray('SELECT id, descrizione, colore FROM in_statiintervento WHERE deleted_at IS NULL ORDER BY descrizione ASC');
foreach ($stati_intervento as $stato) {
    $attr = '';
    if (in_array("'".$stato['id']."'", $_SESSION['dashboard']['idstatiintervento'])) {
        $attr = 'checked="checked"';
    }

    echo '
            <li>
                <input type="checkbox" id="id_stato_'.$stato['id'].'" class="dashboard_stato" value="'.$stato['id'].'" '.$attr.'>
                <label for="id_stato_'.$stato['id'].'">
                    <span class="badge" style="color:'.color_inverse($stato['colore']).'; background:'.$stato['colore'].';">'.$stato['descrizione'].'</span>
                </label>
            </li>';
}

echo '
			<div class="btn-group float-right">
				<button type="button" id="seleziona_stati" class="btn btn-primary btn-sm">
                    '.tr('Tutti').'
                </button>
				<button type="button" id="deseleziona_stati" class="btn btn-danger btn-sm">
                    <i class="fa fa-times"></i>
                </button>
			</div>
		</ul>
	</div>';

// Tipi intervento
$checks = '';
$count = 0;
$total = 0;

$rs = $dbo->fetchArray('SELECT id, descrizione FROM in_tipiintervento ORDER BY descrizione ASC');
$total = count($rs);

$allcheckstipi = '';
for ($i = 0; $i < count($rs); ++$i) {
    $attr = '';

    foreach ($_SESSION['dashboard']['idtipiintervento'] as $idx => $val) {
        if ($val == "'".$rs[$i]['id']."'") {
            $attr = 'checked="checked"';
            ++$count;
        }
    }

    $checks .= "<li><input type='checkbox' id='idtipo_".$rs[$i]['id']."' value=\"".$rs[$i]['id'].'" '.$attr." onclick=\"$.when ( session_set_array( 'dashboard,idtipiintervento', '".$rs[$i]['id']."' ) ).promise().then(function( ){ $('#calendar').fullCalendar('refetchEvents');  }); update_counter( 'idtipi_count', $('#idtipi_ul').find('input:checked').length ); \"> <label for='idtipo_".$rs[$i]['id']."'> ".$rs[$i]['descrizione']."</label></li>\n";

    $allcheckstipi .= "session_set_array( 'dashboard,idtipiintervento', '".$rs[$i]['id']."', 0 ); ";
}

if ($count == $total) {
    $class = 'btn-success';
} elseif ($count == 0) {
    $class = 'btn-danger';
} else {
    $class = 'btn-warning';
}

if ($total == 0) {
    $class = 'btn-primary disabled';
}
?>
	<!-- TIPI DI INTERVENTO -->
	<div class="dropdown col-md-3">
		<a class="btn <?php echo $class; ?> btn-block" data-toggle="dropdown" href="javascript:;" id="idtipi_count"><i class="fa fa-filter"></i> <?php echo tr('Tipi attività'); ?> (<?php echo $count.'/'.$total; ?>) <i class="caret"></i></a>

		<ul class="dropdown-menu" role="menu" id="idtipi_ul">
			<?php echo $checks; ?>
			<div class="btn-group float-right">
				<button  id="selectalltipi" onclick="<?php echo $allcheckstipi; ?>" class="btn btn-primary btn-sm" type="button"><?php echo tr('Tutti'); ?></button>
				<button id="deselectalltipi" class="btn btn-danger btn-sm" type="button"><i class="fa fa-times"></i></button>
			</div>

		</ul>

	</div>

<?php
// Tecnici
$checks = '';
$count = 0;
$total = 0;
$totale_tecnici = 0; // conteggia tecnici eliminati e non

$rs = $dbo->fetchArray("SELECT an_anagrafiche.idanagrafica AS id, ragione_sociale, colore FROM an_anagrafiche INNER JOIN (an_tipianagrafiche_anagrafiche INNER JOIN an_tipianagrafiche ON an_tipianagrafiche_anagrafiche.id_tipo_anagrafica=an_tipianagrafiche.id) ON an_anagrafiche.idanagrafica=an_tipianagrafiche_anagrafiche.idanagrafica
LEFT OUTER JOIN in_interventi_tecnici ON  in_interventi_tecnici.idtecnico = an_anagrafiche.idanagrafica  INNER JOIN in_interventi ON in_interventi_tecnici.idintervento=in_interventi.id
WHERE an_anagrafiche.deleted_at IS NULL AND an_tipianagrafiche.descrizione='Tecnico' ".module('Interventi')->getAdditionalsQuery().' GROUP BY an_anagrafiche.idanagrafica ORDER BY ragione_sociale ASC');
$total = count($rs);

$totale_tecnici += $total;

$allchecktecnici = '';
for ($i = 0; $i < count($rs); ++$i) {
    $attr = '';

    foreach ($_SESSION['dashboard']['idtecnici'] as $idx => $val) {
        if ($val == "'".$rs[$i]['id']."'") {
            $attr = 'checked="checked"';
            ++$count;
        }
    }

    $checks .= "<li><input type='checkbox' id='tech_".$rs[$i]['id']."' value=\"".$rs[$i]['id'].'" '.$attr." onclick=\"$.when ( session_set_array( 'dashboard,idtecnici', '".$rs[$i]['id']."' ) ).promise().then(function( ){ $('#calendar').fullCalendar('refetchEvents'); }); update_counter( 'idtecnici_count', $('#idtecnici_ul').find('input:checked').length );  \"> <label for='tech_".$rs[$i]['id']."'><span class='badge' style=\"color:#000; background:transparent; border: 1px solid ".$rs[$i]['colore'].';">'.$rs[$i]['ragione_sociale']."</span></label></li>\n";

    $allchecktecnici .= "session_set_array( 'dashboard,idtecnici', '".$rs[$i]['id']."', 0 ); ";
}

// TECNICI ELIMINATI CON ALMENO 1 INTERVENTO
$rs = $dbo->fetchArray("SELECT an_anagrafiche.idanagrafica AS id, ragione_sociale FROM an_anagrafiche INNER JOIN (an_tipianagrafiche_anagrafiche INNER JOIN an_tipianagrafiche ON an_tipianagrafiche_anagrafiche.id_tipo_anagrafica=an_tipianagrafiche.id) ON an_anagrafiche.idanagrafica=an_tipianagrafiche_anagrafiche.idanagrafica INNER JOIN in_interventi_tecnici ON in_interventi_tecnici.idtecnico = an_anagrafiche.idanagrafica WHERE deleted_at IS NOT NULL AND descrizione='Tecnico' GROUP BY an_anagrafiche.idanagrafica ORDER BY ragione_sociale ASC");
$total = count($rs);

$totale_tecnici += $total;

if ($total > 0) {
    $checks .= "<li><hr>Tecnici eliminati:</li>\n";
    for ($i = 0; $i < count($rs); ++$i) {
        $attr = '';

        foreach ($_SESSION['dashboard']['idtecnici'] as $idx => $val) {
            if ($val == "'".$rs[$i]['id']."'") {
                $attr = 'checked="checked"';
                ++$count;
            }
        }

        $checks .= "<li><input type='checkbox' id='tech_".$rs[$i]['id']."' value=\"".$rs[$i]['id'].'" '.$attr." onclick=\"$.when ( session_set_array( 'dashboard,idtecnici', '".$rs[$i]['id']."' ) ).promise().then(function( ){ $('#calendar').fullCalendar('refetchEvents');  }); update_counter( 'idtecnici_count', $('#idtecnici_ul').find('input:checked').length ); \"> <label for='tech_".$rs[$i]['id']."'> ".$rs[$i]['ragione_sociale']."</label></li>\n";

        $allchecktecnici .= "session_set_array( 'dashboard,idtecnici', '".$rs[$i]['id']."', 0 ); ";
    } // end for
} // end if

if ($count == $totale_tecnici) {
    $class = 'btn-success';
} elseif ($count == 0) {
    $class = 'btn-danger';
} else {
    $class = 'btn-warning';
}

if ($totale_tecnici == 0) {
    $class = 'btn-primary disabled';
}

?>
	<!-- TECNICI -->
	<div class="dropdown col-md-3">
		<a class="btn <?php echo $class; ?> btn-block" data-toggle="dropdown" href="javascript:;" id="idtecnici_count"><i class="fa fa-filter"></i> <?php echo tr('Tecnici'); ?> (<?php echo $count.'/'.$totale_tecnici; ?>) <i class="caret"></i></a>

		<ul class="dropdown-menu" role="menu" id="idtecnici_ul">
			<?php echo $checks; ?>
			<div class="btn-group float-right">
				<button id="selectalltecnici" onclick="<?php echo $allchecktecnici; ?>" class="btn btn-primary btn-sm" type="button"><?php echo tr('Tutti'); ?></button>
				<button id="deselectalltecnici" class="btn btn-danger btn-sm" type="button"><i class="fa fa-times"></i></button>
			</div>
		</ul>
	</div>


<?php
// Zone
$allcheckzone = null;

$checks = '';
$count = 0;
$total = 0;

$rs = $dbo->fetchArray('(SELECT 0 AS ordine, \'0\' AS id, \'Nessuna zona\' AS descrizione) UNION (SELECT 1 AS ordine, id, descrizione FROM an_zone) ORDER BY ordine, descrizione ASC');
$total = count($rs);

for ($i = 0; $i < count($rs); ++$i) {
    $attr = '';

    foreach ($_SESSION['dashboard']['idzone'] as $idx => $val) {
        if ($val == "'".$rs[$i]['id']."'") {
            $attr = 'checked="checked"';
            ++$count;
        }
    }

    $checks .= "<li><input type='checkbox' id='idzone_".$rs[$i]['id']."' value=\"".$rs[$i]['id'].'" '.$attr." 	onclick=\"$.when ( session_set_array( 'dashboard,idzone', '".$rs[$i]['id']."' ) ).promise().then(function( ){ $('#calendar').fullCalendar('refetchEvents'); update_counter( 'idzone_count', $('#idzone_ul').find('input:checked').length ); }); \"> <label for='idzone_".$rs[$i]['id']."'> ".$rs[$i]['descrizione']."</label></li>\n";

    $allcheckzone = "session_set_array( 'dashboard,idzone', '".$rs[$i]['id']."', 0 ); ";
}

if ($count == $total) {
    $class = 'btn-success';
} elseif ($count == 0) {
    $class = 'btn-danger';
} else {
    $class = 'btn-warning';
}

if ($total == 0) {
    $class = 'btn-primary disabled';
}
?>
	<!-- ZONE -->
	<div class="dropdown col-md-3">
		<a class="btn <?php echo $class; ?> btn-block" data-toggle="dropdown" href="javascript:;" id="idzone_count"><i class="fa fa-filter"></i> <?php echo tr('Zone'); ?> (<?php echo $count.'/'.$total; ?>) <i class="caret"></i></a>

		<ul class="dropdown-menu" role="menu" id="idzone_ul">
			<?php echo $checks; ?>
			<div class="btn-group float-right">
				<button id="selectallzone" onclick="<?php echo $allcheckzone; ?>" class="btn btn-primary btn-sm" type="button"><?php echo tr('Tutti'); ?></button>
				<button id="deselectallzone" class="btn btn-danger btn-sm" type="button"><i class="fa fa-times"></i></button>
			</div>
		</ul>
	</div>
</div>
<br>
<?php
$qp = 'SELECT MONTH(data_richiesta) AS mese, YEAR(data_richiesta) AS anno FROM (co_promemoria INNER JOIN co_contratti ON co_promemoria.idcontratto=co_contratti.id) INNER JOIN an_anagrafiche ON co_contratti.idanagrafica=an_anagrafiche.idanagrafica WHERE idcontratto IN( SELECT id FROM co_contratti WHERE id_stato IN(SELECT id FROM co_staticontratti WHERE is_pianificabile = 1) ) AND idintervento IS NULL

UNION SELECT MONTH(data_scadenza) AS mese, YEAR(data_scadenza) AS anno FROM (co_ordiniservizio INNER JOIN co_contratti ON co_ordiniservizio.idcontratto=co_contratti.id) INNER JOIN an_anagrafiche ON co_contratti.idanagrafica=an_anagrafiche.idanagrafica WHERE idcontratto IN( SELECT id FROM co_contratti WHERE id_stato IN(SELECT id FROM co_staticontratti WHERE is_pianificabile = 1) ) AND idintervento IS NULL

UNION SELECT MONTH(data_richiesta) AS mese, YEAR(data_richiesta) AS anno FROM in_interventi INNER JOIN an_anagrafiche ON in_interventi.idanagrafica=an_anagrafiche.idanagrafica WHERE (SELECT COUNT(*) FROM in_interventi_tecnici WHERE in_interventi_tecnici.idintervento = in_interventi.id) = 0 ORDER BY anno,mese';
$rsp = $dbo->fetchArray($qp);

if (!empty($rsp)) {
    echo '
<div class="row">
    <div class="col-md-10">';
}

echo '
<div id="calendar"></div>';

if (!empty($rsp)) {
    echo '
    </div>

    <div id="external-events" class="d-none-xs d-none-sm col-md-2">
        <h4>'.tr('Promemoria da pianificare').'</h4>';

    // Controllo pianificazioni mesi precedenti
    $qp_old = 'SELECT co_promemoria.id FROM co_promemoria INNER JOIN co_contratti ON co_promemoria.idcontratto=co_contratti.id WHERE id_stato IN(SELECT id FROM co_staticontratti WHERE is_pianificabile = 1) AND idintervento IS NULL AND DATE_ADD(co_promemoria.data_richiesta, INTERVAL 1 DAY) <= NOW()

    UNION SELECT co_ordiniservizio.id FROM co_ordiniservizio INNER JOIN co_contratti ON co_ordiniservizio.idcontratto=co_contratti.id WHERE id_stato IN(SELECT id FROM co_staticontratti WHERE is_pianificabile = 1) AND idintervento IS NULL AND DATE_ADD(co_ordiniservizio.data_scadenza, INTERVAL 1 DAY) <= NOW()

    UNION SELECT in_interventi.id FROM in_interventi INNER JOIN an_anagrafiche ON in_interventi.idanagrafica=an_anagrafiche.idanagrafica WHERE (SELECT COUNT(*) FROM in_interventi_tecnici WHERE in_interventi_tecnici.idintervento = in_interventi.id) = 0 AND DATE_ADD(in_interventi.data_richiesta, INTERVAL 1 DAY) <= NOW()';
    $rsp_old = $dbo->fetchNum($qp_old);

    if ($rsp_old > 0) {
        echo '<div class="alert alert-warning alert-dismissible text-sm" role="alert"><i class="fa fa-exclamation-triangle"></i><button type="button" class="close" data-dismiss="alert" aria-d-none="true">&times;</button> '.tr('Ci sono '.$rsp_old.' attività scadute.').'</div>';
    }

    $mesi = months();

    // Creo un array con tutti i mesi che contengono interventi
    $mesi_interventi = [];
    for ($i = 0; $i < sizeof($rsp); ++$i) {
        $mese_n = $rsp[$i]['mese'].$rsp[$i]['anno'];
        $mese_t = $mesi[intval($rsp[$i]['mese'])].' '.$rsp[$i]['anno'];
        $mesi_interventi[$mese_n] = $mese_t;
    }

    // Aggiungo anche il mese corrente
    $mesi_interventi[date('m').date('Y')] = $mesi[intval(date('m'))].' '.date('Y');

    // Rimuovo i mesi doppi
    array_unique($mesi_interventi);

    // Ordino l'array per anno
    foreach ($mesi_interventi as $key => &$data) {
        ksort($data);
    }

    echo '<select class="superselect" id="select-interventi-pianificare">';

    foreach ($mesi_interventi as $key => $mese_intervento) {
        echo '<option value="'.$key.'">'.$mese_intervento.'</option>';
    }

    echo '</select>';

    echo '<div id="interventi-pianificare"></div>';

    echo '
    </div>
</div>';
}

$vista = setting('Vista dashboard');
if ($vista == 'mese') {
    $def = 'dayGridMonth';
} elseif ($vista == 'giorno') {
    $def = 'timeGridDay';
} else {
    $def = 'timeGridWeek';
}
$domenica = setting('Visualizzare la domenica sul calendario');

$modulo_interventi = module('Interventi');
echo '
<script type="text/javascript">
    globals.dashboard = {
        load_url: "'.urlFor('module-action', [
            'module_id' => $id_module,
            'action' => 'action',
        ]).'",
        style: "'.$def.'",
        show_sunday: "'.setting('Visualizzare la domenica sul calendario').'",
        start_time: "'.setting('Inizio orario lavorativo').'",
        end_time: "'.((setting('Fine orario lavorativo') == '00:00') ?: '23:59:59').'",
        write_permission: "'.intval($modulo_interventi->permission == 'rw').'",
        tooltip: "'.setting('Utilizzare i tooltip sul calendario').'",
        select: {
            title: "'.tr('Aggiungi intervento').'",
            url: "'.urlFor('module-add', [
                'module_id' => $modulo_interventi->id,
            ]).'",
        },
        drop: {
            title: "'.tr('Pianifica intervento').'",
            url: "'.urlFor('module-add', [
                'module_id' => $modulo_interventi->id,
            ]).'",
        },
        error: "'.tr('Errore durante la creazione degli eventi').'",
    };
</script>';
