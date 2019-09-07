<?php

echo '
<!-- Intestazione fornitore -->
<div class="row">
    <div class="col-xs-6">
        <p><b>$f_ragione_sociale$</b></p>
        <p>$f_indirizzo$ $f_citta_full$</p>
        <p>'.(!empty($f_piva) ? tr('P.Iva').': ' : '').'$f_piva$</p>
        <p>'.(!empty($f_codice_fiscale) ? tr('C.F.').': ' : '').'$f_codice_fiscale$</p>
        <p>'.(!empty($f_capsoc) ? tr('Cap.Soc.').': ' : '').'$f_capsoc$</p>
        <p>'.(!empty($f_telefono) ? tr('Tel').': ' : '').'$f_telefono$</p>
    </div>
    <div class="col-xs-6 text-right">
        <img src="$logo$" alt="Logo" border="0"/>
    </div>
</div>';
