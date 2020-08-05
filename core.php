<?php

// Controllo di permessi per retrocompatibilità
if (!empty($id_module)) {
    Permissions::addModule($id_module);
}

Permissions::check();

return;
