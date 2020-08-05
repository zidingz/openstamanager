<?php

namespace Auth;

use Common\Model;
use Modules\Module;
use Modules\Segment;
use Modules\View;
use Prints\Template;
use Widgets\Widget;

class Group extends Model
{
    protected $table = 'zz_groups';

    /* Relazioni Eloquent */

    public function users()
    {
        return $this->hasMany(User::class, 'idgruppo');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'zz_permissions', 'idgruppo', 'idmodule')->withPivot('permessi');
    }

    /*
    public function widgets()
    {
        return $this->morphedByMany(Widget::class, 'permission', 'zz_permissions', 'group_id', 'external_id')->where('permission_level', '!=', '-')->withPivot('permission_level');
    }

    public function segments()
    {
        return $this->morphedByMany(Segment::class, 'permission', 'zz_permissions', 'group_id', 'external_id')->where('permission_level', '!=', '-')->withPivot('permission_level');
    }

    public function prints()
    {
        return $this->belongsToMany(Template::class, 'zz_permissions', 'idgruppo', 'idmodule')->withPivot('permessi');
    }*/

    public function views()
    {
        return $this->belongsToMany(View::class, 'zz_group_view', 'id_gruppo', 'id_vista');
    }
}
