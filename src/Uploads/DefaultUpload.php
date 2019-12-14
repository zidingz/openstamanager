<?php

namespace Uploads;

use ArrayAccess;
use Auth\User;
use Carbon\Carbon;
use Common\Model;
use Modules\Module;
use Psr\Http\Message\ResponseInterface;

class DefaultUpload extends Model implements UploadAdapter
{
    protected $table = 'zz_files';

    protected $file_info;

    public function getCategoryAttribute()
    {
        return $this->attributes['category'] ?: 'Generale';
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

    public function save(array $options = [])
    {
        if ($this->isImage()) {
            //self::generateThumbnails($this);
        }

        return parent::save($options);
    }

    public static function getInfo($file): array
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

    /* Interfaccia pubblica */

    /**
     * {@inheritdoc}
     */
    public function getExtension(): string
    {
        $info = self::getInfo($this->getFullPath());

        return strtolower($info['extension']);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(): bool
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

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getMime(): string
    {
        return mime_content_type($this->getFullPath());
    }

    /**
     * {@inheritdoc}
     */
    public function move(string $path): bool
    {
        $filename = basename($path);
        $result = $this->getPath().'/'.$filename;

        rename($this->getFullPath(), $result);

        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function copy(string $path, Module $module, Model $record = null): bool
    {
        $contents = $this->getContents();

        return self::put($path, $contents, $module, $record);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->module->upload_directory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullPath(): string
    {
        return $this->getPath().'/'.$this->filename;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp(): Carbon
    {
        $time = filemtime($this->getFullPath());

        return new Carbon($time);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        return file_get_contents($this->getFullPath());
    }

    /**
     * {@inheritdoc}
     */
    public static function put(string $path, string $content, Module $module, Model $record = null): bool
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

    /**
     * {@inheritdoc}
     */
    public static function register(Module $module, Model $record = null, array $options = []): void
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public static function remove(Module $module, Model $record = null): void
    {
        $uploads = self::locate($module, $record);

        foreach ($uploads as $upload) {
            $upload->delete();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function locate(Module $module, Model $record = null): ArrayAccess
    {
        return self::where('id_module', $module->id)->where('id_record', $record->id)->get();
    }

    /**
     * {@inheritdoc}
     */
    public static function render(Module $module, Model $record = null): ResponseInterface
    {
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
}
