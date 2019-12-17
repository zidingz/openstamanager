<?php

echo '

<div class="alert alert-warning">
    <i class="fa fa-warning"></i> <b>'.tr('Attenzione', [], ['upper']).':</b> '.tr('le suddette stampe contabili non sono da considerarsi valide ai fini fiscali').'.
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">'.tr('Registri iva dal _START_ al _END_', [
                    '_START_' => dateFormat($_SESSION['period_start']),
                    '_END_' => dateFormat($_SESSION['period_end']),
                ]).'</h3>
            </div>

            <div class="card-body">
                '.Prints::getLink('Registro IVA', $id_record, 'btn-primary', '<br>'.tr('Stampa registro').'<br>'.tr('IVA vendite'), '|default| fa-2x', 'dir=entrata').'

                '.Prints::getLink('Registro IVA', $id_record, 'btn-primary', '<br>'.tr('Stampa registro').'<br>'.tr('IVA acquisti'), '|default| fa-2x', 'dir=uscita').'
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">'.tr('Comunicazione dati fatture (ex-spesometro) dal _START_ al _END_', [
                    '_START_' => dateFormat($_SESSION['period_start']),
                    '_END_' => dateFormat($_SESSION['period_end']),
                ]).'</h3>
            </div>

            <div class="card-body">
                '.Prints::getLink('Spesometro', $id_record, 'btn-primary', '<br>'.tr('Stampa').'<br>'.tr('dati fatture'), '|default| fa-2x', 'dir=uscita').'
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">'.tr('Fatturato dal _START_ al _END_', [
                    '_START_' => dateFormat($_SESSION['period_start']),
                    '_END_' => dateFormat($_SESSION['period_end']),
                ]).'</h3>
            </div>

            <div class="card-body">
                '.Prints::getLink('Fatturato', $id_record, 'btn-primary', '<br>'.tr('Stampa fatturato').'<br>'.tr('in entrata'), '|default| fa-2x', 'dir=entrata').'

                '.Prints::getLink('Fatturato', $id_record, 'btn-primary', '<br>'.tr('Stampa fatturato').'<br>'.tr('in uscita'), '|default| fa-2x', 'dir=uscita').'
            </div>
        </div>
    </div>
</div>';
