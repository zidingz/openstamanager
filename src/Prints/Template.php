<?php

namespace Prints;

use Auth\Group;
use Common\Model;
use Illuminate\Database\Eloquent\Builder;
use Modules\Module;
use Psr\Container\ContainerInterface;
use Traits\PathTrait;
use Traits\StoreTrait;

class Template extends Model
{
    use PathTrait;
    use StoreTrait;

    protected $table = 'zz_prints';
    protected $main_folder = 'templates';

    /**
     * Restituisce l'instanza dedicata alla gestione della stampa per il record indicato.
     *
     * @param ContainerInterface $container
     * @param int                $record_id
     *
     * @return Manager
     */
    public function getManager(ContainerInterface $container, ?int $record_id = null): Manager
    {
        $class = $this->class;
        $manager = new $class($container, $this, $record_id);

        return $manager;
    }

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
