<?php

namespace Modules;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    protected $table = 'zz_segments';

    protected $appends = [
        'permission',
    ];

    /* Relazioni Eloquent */

    public function module()
    {
        return $this->belongsTo(Module::class, 'id_module');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('permission', function (Builder $builder) {
            // $builder->with('groups');
        });
    }
}
