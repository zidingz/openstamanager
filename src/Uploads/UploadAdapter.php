<?php

namespace Uploads;

use ArrayAccess;
use Carbon\Carbon;
use Common\Model;
use Modules\Module;
use Psr\Http\Message\ResponseInterface;

interface UploadAdapter
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getExtension(): string;

    /**
     * @return string
     */
    public function getMime(): string;

    /**
     * @return string
     */
    public function getContents(): string;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string
     */
    public function getFullPath(): string;

    /**
     * @return int
     */
    public function getSize(): int;

    /**
     * @return Carbon
     */
    public function getTimestamp(): Carbon;

    /**
     * @return bool
     */
    public function delete(): bool;

    /**
     * @param string $path
     *
     * @return bool
     */
    public function move(string $path): bool;

    /**
     * @param string     $path
     * @param Module     $module
     * @param Model|null $record
     *
     * @return bool
     */
    public function copy(string $path, Module $module, Model $record = null): bool;

    /**
     * @param string     $path
     * @param string     $content
     * @param Module     $module
     * @param Model|null $record
     *
     * @return bool
     */
    public static function put(string $path, string $content, Module $module, Model $record = null): bool;

    /**
     * @param Module     $module
     * @param Model|null $record
     * @param array      $options
     */
    public static function register(Module $module, Model $record = null, array $options = []): void;

    /**
     * @param Module     $module
     * @param Model|null $record
     */
    public static function remove(Module $module, Model $record = null): void;

    /**
     * @param Module     $module
     * @param Model|null $record
     *
     * @return ArrayAccess
     */
    public static function locate(Module $module, Model $record = null): ArrayAccess;

    /**
     * @param Module     $module
     * @param Model|null $record
     *
     * @return ResponseInterface
     */
    public static function render(Module $module, Model $record = null): ResponseInterface;
}
