<?php

namespace Uploads;

use Common\Model;
use Modules\Module;
use Auth\User;

class DefaultUpload extends Model implements UploadAdapter {

    protected $table = 'zz_files';

    protected $file_info;

    public function getCategoryAttribute()
    {
        return $this->attributes['category'] ?: 'Generale';
    }

    /**
     * @return string|null
     */
    public function getExtension()
    {
        $info = self::getInfo($this->getFullPath());

        return strtolower($info['extension']);
    }

    /**
     * @return string
     */
    public function getOriginalNameAttribute()
    {
        return $this->attributes['original'];
    }

    public function setOriginalNameAttribute($value)
    {
        $this->attributes['original'] = $value;
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        $list = ['jpg', 'png', 'gif', 'jpeg', 'bmp'];

        return in_array($this->getExtension(), $list);
    }

    /**
     * @return bool
     */
    public function isFatturaElettronica()
    {
        return $this->getExtension() == 'xml' && strtolower($this->category) == 'fattura elettronica';
    }

    /**
     * @return bool
     */
    public function isPDF()
    {
        return $this->getExtension() == 'pdf';
    }

    /**
     * @return bool
     */
    public function hasPreview()
    {
        return $this->isImage() || $this->isFatturaElettronica() || $this->isPDF();
    }

    public function delete()
    {
        $info = self::getInfo($this->getFullPath());
        $directory = DOCROOT.'/'.$this->directory;

        $files = [
            $directory.'/'.$info['basename'],
            $directory.'/'.$info['filename'].'_thumb600.'.$info['extension'],
            $directory.'/'.$info['filename'].'_thumb100.'.$info['extension'],
            $directory.'/'.$info['filename'].'_thumb250.'.$info['extension'],
        ];

        delete($files);

        return parent::delete();
    }

    public function save(array $options = [])
    {
        if ($this->isImage()) {
            //self::generateThumbnails($this);
        }

        return parent::save($options);
    }

    public static function getInfo($file)
    {
        return pathinfo($file);
    }

    /* Relazioni Eloquent */

    public function module()
    {
        return $this->belongsTo(Module::class, 'id_module');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Genera casualmente il nome fisico per il file.
     *
     * @return string
     */
    protected static function getNextFilename($file, $directory)
    {
        $extension = self::getInfo($file)['extension'];
        $extension = strtolower($extension);

        do {
            $filename = random_string().'.'.$extension;
        } while (file_exists($directory.'/'.$filename));

        return $filename;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMime()
    {
        return mime_content_type($this->getFullPath());
    }

    public static function put($path, $content, Module $module, Model $record = null)
    {
        // Nome fisico del file
        $directory = DOCROOT.'/'.$module->upload_directory;
        $filename = self::getNextFilename($path, $directory);

        // Creazione file fisico
        directory($directory);
        file_put_contents($directory.'/'.$filename, $content);

        // Registrazione
        $model = self::build();
        $model->original_name = basename($path);
        $model->filename = $filename;
        $model->id_module = $module->id;
        $model->id_record = $record->id;
        $model->size = \Util\FileSystem::fileSize($directory.'/'.$filename);
        $model->user()->associate(auth()->getUser());

        $model->save();

        return $model;
    }

    public function move($path)
    {
        $filename = basename($path);
        $result = $this->getPath().'/'.$filename;

        rename($this->getFullPath(), $result);

        $this->filename = $filename;
    }

    public function copy($path, Module $module, Model $record = null)
    {
        $contents = $this->getContents();

        return self::put($path, $contents, $module, $record);
    }

    public function getPath()
    {
        return $this->module->upload_directory;
    }

    public function getFullPath()
    {
        return $this->getPath().'/'.$this->filename;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getTimestamp()
    {
        // TODO: Implement getTimestamp() method.
    }

    public static function register(Module $module, Model $record = null, array $options)
    {
        // TODO: Implement registerRecord() method.
    }

    public static function remove(Module $module, Model $record = null)
    {
        // TODO: Implement deleteRecord() method.
    }

    public static function locate(Module $module, Model $record = null)
    {
        // TODO: Implement listRecord() method.
    }

    public function getContents()
    {
        return file_get_contents($this->getFullPath());
    }
}
