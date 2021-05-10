<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.r.l.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Models;

use Auth;
use Common\SimpleModelTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Checklists\Traits\ChecklistTrait;
use Modules\Emails\Template;
use Traits\Components\NoteTrait;
use Traits\Components\UploadTrait;
use Traits\LocalPoolTrait;
use Traits\ManagerTrait;

class Module extends Model
{
    use SimpleModelTrait;
    use ManagerTrait;
    use UploadTrait;
    use LocalPoolTrait;
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

    /**
     * Costruisce un link HTML per il modulo e il record indicati.
     */
    public function link(?int $id_record = null, ?string $testo = null, ?string $alternativo = null, ?string $extra = null, bool $blank = true, ?string $anchor = null): string
    {
        $testo = isset($testo) ? nl2br($testo) : tr('Visualizza scheda');
        $alternativo = is_bool($alternativo) && $alternativo ? $testo : $alternativo;

        // Aggiunta automatica dell'icona di riferimento
        if (!string_contains($testo, '<i ')) {
            $testo = $testo.' <i class="fa fa-external-link"></i>';
        }

        $extra .= !empty($blank) ? ' target="_blank"' : '';

        if (in_array($this->permission, ['r', 'rw'])) {
            $link = !empty($id_record) ? 'editor.php?id_module='.$this->id.'&id_record='.$id_record : 'controller.php?id_module='.$this->id;

            return '<a href="'.base_url().'/'.$link.'#'.$anchor.'" '.$extra.'>'.$testo.'</a>';
        } else {
            return $alternativo;
        }
    }

    public function replacePlaceholders($id_record, $value)
    {
        $replaces = $this->getPlaceholders($id_record);

        $value = str_replace(array_keys($replaces), array_values($replaces), $value);

        return $value;
    }

    public function getPlaceholders($id_record)
    {
        if (!isset($this->variables[$id_record])) {
            $dbo = $database = database();
            $variables = [];

            // Lettura delle variabili nei singoli moduli
            $path = $this->filepath('variables.php');
            if (!empty($path)) {
                $variables = include $path;
            }

            // Sostituzione delle variabili di base
            $replaces = [];
            foreach ($variables as $key => $value) {
                $replaces['{'.$key.'}'] = $value;
            }

            $this->variables[$id_record] = $replaces;
        }

        return $this->variables[$id_record];
    }

    /**
     * Restituisce i permessi relativi all'account in utilizzo.
     *
     * @return string
     */
    public function getPermissionAttribute()
    {
        if (auth()->user()->is_admin) {
            return 'rw';
        }

        $group = auth()->user()->group->id;

        $pivot = $this->pivot ?: $this->groups->first(function ($item) use ($group) {
            return $item->id == $group;
        })->pivot;

        return $pivot->permessi ?: '-';
    }

    /**
     * Restituisce i permessi relativi all'account in utilizzo.
     *
     * @return string
     */
    public function getViewsAttribute()
    {
        $user = auth()->user();

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

    /* Relazioni Eloquent */

    public function plugins()
    {
        return $this->hasMany(Plugin::class, 'idmodule_to');
    }

    public function prints()
    {
        return $this->hasMany(PrintTemplate::class, 'id_module');
    }

    public function Templates()
    {
        return $this->hasMany(Template::class, 'id_module');
    }

    public function views()
    {
        return $this->hasMany(View::class, 'id_module');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'zz_permissions', 'idmodule', 'idgruppo')->withPivot('permessi');
    }

    public function clauses()
    {
        return $this->hasMany(Clause::class, 'idmodule');
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
