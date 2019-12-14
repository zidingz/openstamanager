<?php

include_once __DIR__.'/../../core.php';

echo '
<hr>
<div class="card card-outline card-warning collapsed-card">
    <div class="card-header">
        <h4 class="card-title">
            '.tr('Periodi temporali').'
        </h4>
        <div class="card-tools float-right">
            <button class="btn btn-warning btn-sm" onclick="add_calendar()">
                <i class="fa fa-plus"></i> '.tr('Aggiungi periodo').'
            </button>
            <button type="button" class="btn btn-card-tool" data-card-widget="collapse">
                <i class="fa fa-minus"></i>
            </button>
        </div>
    </div>

    <div class="card-body" id="calendars">

    </div>
</div>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">'.tr('Prezzo medio acquisto').'</h3>
    </div>

    <div class="card-body">
        <table class="table table-striped table-condensed table-bordered">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th>'.tr('Periodo').'</th>
                    <th>'.tr('Prezzo minimo').'</th>
                    <th>'.tr('Prezzo medio').'</th>
                    <th>'.tr('Prezzo massimo').'</th>
                    <th>'.tr('Oscillazione').'</th>
                    <th>'.tr('Oscillazione in %').'</th>
                    <th>'.tr('Andamento prezzo').'</th>
                </tr>
            </thead>
            <tbody id="prezzi_acquisto">

            </tbody>
        </table>
    </div>
</div>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">'.tr('Prezzo medio vendita').'</h3>
    </div>

    <div class="card-body">
        <table class="table table-striped table-condensed table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>'.tr('Periodo').'</th>
                    <th>'.tr('Prezzo minimo').'</th>
                    <th>'.tr('Prezzo medio').'</th>
                    <th>'.tr('Prezzo massimo').'</th>
                    <th>'.tr('Oscillazione').'</th>
                    <th>'.tr('Oscillazione in %').'</th>
                    <th>'.tr('Andamento prezzo').'</th>
                </tr>
            </thead>
            <tbody id="prezzi_vendita">

            </tbody>
        </table>
    </div>
</div>';

$statistiche = \Modules\Module::get('Statistiche');

echo '
<script src="'.$statistiche->fileurl('js/functions.js').'"></script>
<script src="'.$statistiche->fileurl('js/manager.js').'"></script>
<script src="'.$statistiche->fileurl('js/calendar.js').'"></script>
<script src="'.$statistiche->fileurl('js/stat.js').'"></script>
<script src="'.$statistiche->fileurl('js/stats/table.js').'"></script>

<script src="'.$structure->fileurl('js/prezzo.js').'"></script>

<script>
var local_url = "'.str_replace('edit.php', '', $structure->fileurl('edit.php')).'";

function init_calendar(calendar) {
    var prezzo_acquisto = new Prezzo(calendar, "#prezzi_acquisto", "uscita");
    var prezzo_vendita = new Prezzo(calendar, "#prezzi_vendita", "entrata");

    calendar.addElement(prezzo_acquisto);
    calendar.addElement(prezzo_vendita);
}
</script>

<script src="'.$statistiche->fileurl('js/init.js').'"></script>';
