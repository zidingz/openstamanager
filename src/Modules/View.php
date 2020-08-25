<?php

namespace Modules;

use Auth\Group;
use Common\Model;
use Util\Query;

class View extends Model
{
    protected $table = 'zz_views';

    public function getQueryAttribute($value)
    {
        return Query::replacePlaceholder($value);
    }

    /* Relazioni Eloquent */

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'zz_group_view', 'id_vista', 'id_gruppo');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'id_module');
    }
}
