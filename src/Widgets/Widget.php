<?php

namespace Widgets;

use Auth\Group;
use Components\BootableInterface;
use Components\BootrableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Module;

/**
 * Modello Eloquent per i widget del gestionale.
 *
 * @since 2.5
 */
class Widget extends Model implements BootableInterface
{
    use BootrableTrait;

    protected $table = 'zz_widgets';

    protected $appends = [
        'permission',
    ];

    public function render(array $args = [])
    {
        return $this->getManager()->render($args);
    }

    /* Relazioni Eloquent */

    public function module()
    {
        return $this->belongsTo(Module::class, 'id_module');
    }

    /*
    public function groups()
    {
        return $this->morphToMany(Group::class, 'permission', 'zz_permissions', 'external_id', 'group_id')->where('permission_level', '!=', '-')->withPivot('permission_level');
    }
    */

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('enabled', function (Builder $builder) {
            $builder->where('enabled', true);
        });

        static::addGlobalScope('permission', function (Builder $builder) {
            //$builder->with('groups');
        });
    }
}
