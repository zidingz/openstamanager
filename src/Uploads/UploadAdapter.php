<?php

namespace Uploads;

use Common\Model;

interface UploadAdapter
{
    public function getName();

    public function getExtension();

    public function getMime();

    public function getContents();

    public function getPath();

    public function getFullPath();

    public function getSize();

    public function getTimestamp();

    public function delete();

    public function move($path);

    public function copy($path, Module $module, Model $record = null);

    public static function put($path, $content, Module $module, Model $record = null);

    public static function register(Module $module, Model $record = null, array $options);

    public static function remove(Module $module, Model $record = null);

    public static function locate(Module $module, Model $record = null);
}
