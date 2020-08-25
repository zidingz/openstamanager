<?php

namespace Traits;

trait HierarchyTrait
{
    public function children()
    {
        return $this->hasMany(static::class, static::$parent_identifier);
    }

    public function parent()
    {
        return $this->belongsTo(static::class, static::$parent_identifier);
    }

    public function ascendants()
    {
        return $this->parent()->with('ascendants');
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    public static function firstGeneration()
    {
        return static::whereNull(self::$parent_identifier);
    }
}
