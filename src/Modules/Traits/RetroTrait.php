<?php

namespace Modules\Traits;

use App;

trait RetroTrait
{
    /**
     * Restituisce il percorso per i contenuti della struttura.
     *
     * @return string
     */
    public function getPathAttribute()
    {
        return $this->main_folder.'/'.$this->directory;
    }

    /**
     * Restituisce il percorso completo per il file indicato della struttura.
     *
     * @param $file
     *
     * @return string|null
     */
    public function filepath($file)
    {
        return App::filepath($this->path.'|custom|', $file);
    }

    /**
     * Restituisce l'URL completa per il file indicato della struttura.
     *
     * @param $file
     *
     * @return string|null
     */
    public function fileurl($file)
    {
        $filepath = $this->filepath($file);

        $result = str_replace(DOCROOT, ROOTDIR, $filepath);
        $result = str_replace('\\', '/', $result);

        return $result;
    }

    /**
     * Restituisce il percorso per il file di crezione dei record.
     *
     * @return string
     */
    public function getAddFile()
    {
        if (method_exists($this, 'getCustomAddFile')) {
            $result = $this->getCustomAddFile();

            if (!empty($result)) {
                return $result;
            }
        }

        $php = $this->filepath('add.php');
        $html = $this->filepath('add.html');

        return !empty($php) ? $php : $html;
    }

    /**
     * Controlla l'esistenza del file di crezione dei record.
     *
     * @return bool
     */
    public function hasAddFile()
    {
        return !empty($this->getAddFile());
    }

    /**
     * Restituisce il percorso per il file di modifica dei record.
     *
     * @return string
     */
    public function getEditFile()
    {
        if (method_exists($this, 'getCustomEditFile')) {
            $result = $this->getCustomEditFile();

            if (!empty($result)) {
                return $result;
            }
        }

        $php = $this->filepath('edit.php');
        $html = $this->filepath('edit.html');

        return !empty($php) ? $php : $html;
    }
}
