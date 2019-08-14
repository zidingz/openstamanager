<?php

namespace Modules\Checklists;

use Common\Model;
use Traits\HierarchyTrait;

class ChecklistItem extends Model
{
    use HierarchyTrait;

    protected static $parent_identifier = 'id_parent';
    protected $table = 'zz_checklist_items';

    /**
     * Crea un nuovo elemento della checklist.
     *
     * @param Checklist $checklist
     * @param string    $contenuto
     * @param int       $reference_id
     *
     * @return self
     */
    public static function build(Checklist $checklist, $contenuto, $reference_id = null)
    {
        $model = parent::build();

        $model->checklist()->associate($checklist);
        $model->id_parent = $reference_id;
        $model->content = $contenuto;

        $model->findOrder();

        $model->save();

        return $model;
    }

    /* Relazioni Eloquent */

    public function checklist()
    {
        return $this->belongsTo(Checklist::class, 'id_checklist');
    }

    protected function findOrder()
    {
        $this->order = orderValue($this->table, 'id_checklist', $this->id_checklist);
    }
}
