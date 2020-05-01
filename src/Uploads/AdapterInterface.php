<?php

namespace Uploads;

use ArrayAccess;
use Carbon\Carbon;
use Common\Model;
use Modules\Module;
use Psr\Http\Message\ResponseInterface;

interface AdapterInterface
{
    public function getName(): string;

    public function getDownloadName(): string;

    public function getExtension(): string;

    public function getMime(): string;

    public function getContents(): string;

    public function getPath(): string;

    public function getFullPath(): string;

    public function getSize(): int;

    public function getTimestamp(): Carbon;

    public function delete(): bool;

    public function move(string $path): bool;

    public function copy(string $path, Module $module, Model $record = null): bool;

    public static function put(string $path, string $content, Module $module, Model $record = null): bool;

    public static function register(Module $module, Model $record = null, array $options = []): void;

    public static function remove(Module $module, Model $record = null): bool;

    public static function locate(Module $module, Model $record = null): ArrayAccess;

    public static function render(Module $module, Model $record = null): ResponseInterface;
}
