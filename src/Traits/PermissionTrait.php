<?php

namespace Traits;

trait PermissionTrait
{
    /**
     * Restituisce i permessi relativi all'account in utilizzo.
     *
     * @return string
     */
    public function getPermissionAttribute()
    {
        $user = auth()->user();
        if (empty($user)) {
            return '-';
        }

        if ($user->is_admin) {
            return 'rw';
        }

        $group = $user->group->id;

        $pivot = $this->pivot ?: $this->groups->first(function ($item) use ($group) {
            return $item->id == $group;
        })->pivot;

        return $pivot->permessi ?: '-';
    }
}
