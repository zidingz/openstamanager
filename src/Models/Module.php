<?php

namespace Models;

use Auth;
use Common\Model;
use Illuminate\Database\Eloquent\Builder;
use Modules\Checklists\Traits\ChecklistTrait;
use Traits\Components\NoteTrait;
use Traits\Components\UploadTrait;
use Traits\ManagerTrait;
use Traits\PermissionTrait;
use Traits\StoreTrait;
use Util\Query;

class Module extends Model
{
    use ManagerTrait, UploadTrait, StoreTrait, PermissionTrait;
    use NoteTrait;
    use ChecklistTrait;

    protected $table = 'zz_modules';
    protected $main_folder = 'modules';
    protected $component_identifier = 'id_module';

    protected $variables = [];

    protected $appends = [
        'permission',
        'option',
    ];

    protected $hidden = [
        'options',
        'options2',
    ];

    public function replacePlaceholders($id_record, $value)
    {
        $replaces = $this->getPlaceholders($id_record);

        $value = str_replace(array_keys($replaces), array_values($replaces), $value);

        return $value;
    }

    public function getPlaceholders($id_record)
    {
        if (!isset($variables[$id_record])) {
            $dbo = $database = database();

            // Lettura delle variabili nei singoli moduli
            $variables = include $this->filepath('variables.php');

            // Sostituzione delle variabili di base
            $replaces = [];
            foreach ($variables as $key => $value) {
                $replaces['{'.$key.'}'] = $value;
            }

            $this->variables[$id_record] = $replaces;
        }

        return $this->variables[$id_record];
    }

    public function render(array $args = [])
    {
        return $this->class->render($args);
    }

    public function getPlugins(string $type = 'module_plugin')
    {
        return $this->plugins()
            ->where('type', $type)
            ->orderBy('order')
            ->get();
    }

    // Attributi Eloquent

    public function getClassAttribute()
    {
        $class = $this->attributes['class'];
        $result = new $class($this);

        return $result;
    }

    /**
     * Restituisce i permessi relativi all'account in utilizzo.
     *
     * @return string
     */
    public function getViewsAttribute()
    {
        $user = Auth::user();

        $views = database()->fetchArray('SELECT * FROM `zz_views` WHERE `id_module` = :module_id AND
        `id` IN (
            SELECT `id_vista` FROM `zz_group_view` WHERE `id_gruppo` = (
                SELECT `idgruppo` FROM `zz_users` WHERE `id` = :user_id
            ))
        ORDER BY `order` ASC', [
            'module_id' => $this->id,
            'user_id' => $user->id,
        ]);

        return $views;
    }

    public function getOptionAttribute()
    {
        return !empty($this->options2) ? $this->options2 : $this->options;
    }

    public function hasRecordAccess($record_id)
    {
        Query::setSegments(false);
        $query = Query::getQuery($this, [
            'id' => $record_id,
        ]);
        Query::setSegments(true);

        // Fix per la visione degli elementi eliminati (per permettere il rispristino)
        $query = str_replace(['AND `deleted_at` IS NULL', '`deleted_at` IS NULL', 'AND deleted_at IS NULL', 'deleted_at IS NULL'], '', $query);

        $result = !empty($query) ? database()->fetchNum($query) !== 0 : true;

        return $result;
    }

    /* Relazioni Eloquent */

    public function plugins()
    {
        return $this->hasMany(Module::class, 'parent')->where('type', '<>', 'module');
    }

    public function prints()
    {
        return $this->hasMany(PrintTemplate::class, 'id_module');
    }

    public function templates()
    {
        return $this->hasMany(Template::class, 'id_module');
    }

    public function views()
    {
        return $this->hasMany(View::class, 'id_module');
    }

    public function groups()
    {
        return $this->morphToMany(Group::class, 'permission', 'zz_permissions', 'external_id', 'group_id')->where('permission_level', '!=', '-')->withPivot('permission_level');
    }

    public function clauses()
    {
        return $this->hasMany(Clause::class, 'idmodule');
    }

    public function segments()
    {
        return $this->morphToMany(Segment::class, 'permission', 'zz_permissions', 'external_id', 'group_id')->where('permission_level', '!=', '-')->withPivot('permission_level');
    }

    /* Gerarchia */

    public function children()
    {
        return $this->hasMany(self::class, 'parent')->withoutGlobalScope('enabled')
            ->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent')->withoutGlobalScope('enabled');
    }

    public function allParents()
    {
        return $this->parent()->with('allParents');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public static function getHierarchy()
    {
        return self::with('allChildren')
            ->withoutGlobalScope('enabled')
            ->whereNull('parent')
            ->orderBy('order')
            ->get();
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
