<?php

namespace Models;

use Common\Model;

class OperationLog extends Model
{
    protected $table = 'zz_operations';

    /* Relazioni Eloquent */

    public function user()
    {
        return $this->belongsTo(User::class, 'id_utente');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'id_module');
    }
}
