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

use AppLegacy;
use Common\SimpleModelTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Checklists\Traits\ChecklistTrait;
use Traits\Components\NoteTrait;
use Traits\Components\UploadTrait;
use Traits\LocalPoolTrait;
use Traits\ManagerTrait;

class Plugin extends Model
{
    use SimpleModelTrait;
    use ManagerTrait;
    use LocalPoolTrait;
    use UploadTrait {
        getUploadDirectoryAttribute as protected defaultUploadDirectory;
    }
    use NoteTrait;
    use ChecklistTrait;

    protected $table = 'zz_plugins';
    protected $main_folder = 'plugins';
    protected $component_identifier = 'id_plugin';

    protected $appends = [
        'permission',
        'option',
    ];

    protected $hidden = [
        'options',
        'options2',
    ];

    /**
     * Restituisce i permessi relativi all'account in utilizzo.
     *
     * @return string
     */
    public function getPermissionAttribute()
    {
        return $this->originalModule->permission;
    }

    public function getOptionAttribute()
    {
        return !empty($this->options2) ? $this->options2 : $this->options;
    }

    /* Metodi personalizzati */

    /**
     * Restituisce l'eventuale percorso personalizzato per il file di creazione dei record.
     *
     * @return string
     */
    public function getCustomAddFile()
    {
        if (empty($this->script)) {
            return;
        }

        $directory = 'modules/'.$this->originalModule->directory.'|custom|/plugins';

        return AppLegacy::filepath($directory, $this->script);
    }

    /**
     * Restituisce l'eventuale percorso personalizzato per il file di modifica dei record.
     *
     * @return string
     */
    public function getCustomEditFile()
    {
        if (empty($this->script)) {
            return;
        }

        return $this->getAddFile();
    }

    /**
     * Restituisce il percorso per il salvataggio degli upload.
     *
     * @return string
     */
    public function getUploadDirectoryAttribute()
    {
        if (!empty($this->script)) {
            return $this->uploads_directory.'/'.basename($this->script, '.php');
        }

        return $this->defaultUploadDirectory();
    }

    /* Relazioni Eloquent */

    public function originalModule()
    {
        return $this->belongsTo(Module::class, 'idmodule_from');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'idmodule_to');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('enabled', function (Builder $builder) {
            $builder->where('enabled', true);
        });
    }
}
