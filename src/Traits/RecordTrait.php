<?php

namespace Traits;

use Modules\Module;
use Uploads\UploadInterface;

trait RecordTrait
{
    public function getModule()
    {
        return !empty($this->module) ? Module::get($this->module) : null;
    }

    public function uploads()
    {
        $module = $this->getModule();

        if (!empty($module) && $module instanceof UploadInterface) {
            return $module->uploads($this->id);
        }

        return collect();
    }
}
