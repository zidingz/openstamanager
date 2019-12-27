<?php

namespace Modules\Anagrafiche;

use Common\Model;

class Tipo extends Model
{
    protected $table = 'an_tipianagrafiche';

    public function anagrafiche()
    {
        return $this->hasMany(Anagrafica::class, 'an_tipianagrafiche_anagrafiche', 'id_tipo_anagrafica', 'idanagrafica');
    }
}
