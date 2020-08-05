<?php

namespace Auth;

use Common\Model;
use Modules\Module;

class Clause extends Model
{
    protected $table = 'zz_group_module';

    /* Relazioni Eloquent */

    public function groups()
    {
        return $this->hasMany(Group::class, 'idgruppo');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'idmodule');
    }

    public function getClauseAttribute($value)
    {
        return Util\Query::replacePlaceholder($value);
    }
}
