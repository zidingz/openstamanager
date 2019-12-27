<?php

namespace Prints;

use Auth\Group;
use Common\Model;
use Components\BootableInterface;
use Components\BootrableTrait;
use Illuminate\Database\Eloquent\Builder;
use Modules\Module;
use Traits\PathTrait;
use Traits\StoreTrait;

class Template extends Model implements BootableInterface
{
    use PathTrait;
    use StoreTrait;
    use BootrableTrait;

    protected $table = 'zz_prints';
    protected $main_folder = 'templates';

    // Attributi Eloquent

    /**
     * Restituisce un array associativo dalla codifica JSON delle opzioni di stampa.
     *
     * @param string $string
     *
     * @return array
     */
    public function getOptionsAttribute()
    {
        // Fix per contenuti con newline integrate
        $string = str_replace(["\n", "\r"], ['\\n', '\\r'], $this->options);

        $result = (array) json_decode($string, true);

        return $result;
    }

    /* Relazioni Eloquent */

    public function module()
    {
        return $this->belongsTo(Module::class, 'id_module');
    }

    public function groups()
    {
        return $this->morphToMany(Group::class, 'permission', 'zz_permissions', 'external_id', 'group_id')->where('permission_level', '!=', '-')->withPivot('permission_level');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('enabled', function (Builder $builder) {
            $builder->where('enabled', true);
        });

        static::addGlobalScope('permission', function (Builder $builder) {
            $builder->with('groups');
        });
    }
}
