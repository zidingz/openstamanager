<?php

namespace HTMLBuilder\Manager;

/**
 * @since 2.4
 */
class FieldManager implements ManagerInterface
{
    public function manage($options)
    {
        $info = $this->getInfo($options);

        return $this->generate($info);
    }

    public function getInfo($options)
    {
        $database = database();

        $query = 'SELECT `zz_fields`.*'.(isset($options['id_record']) ? ', `zz_field_record`.`value`' : '').' FROM `zz_fields`';

        if (isset($options['id_record'])) {
            $query .= ' LEFT JOIN `zz_field_record` ON `zz_fields`.`id` = `zz_field_record`.`id_field`  AND `zz_field_record`.`id_record` = '.prepare($options['id_record']);
        }

        $query .= ' WHERE `id_module` = '.prepare($options['id_module']);

        if (isset($options['place']) && $options['place'] == 'add') {
            $query .= ' AND `on_add` = 1';
        }

        $query .= ' AND `top` = '.((isset($options['position']) && $options['position'] == 'top') ? 1 : 0).' ORDER BY `order`';

        $results = $database->fetchArray($query);

        return $results;
    }

    public function generate($fields)
    {
        // Spazio per evitare problemi con la sostituzione del tag
        $result = ' ';

        if (!empty($fields)) {
            $result .= '
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">'.tr('Campi aggiuntivi').'</h3>
    </div>
    <div class="card-body">';

            // Costruzione dei campi
            foreach ($fields as $key => $field) {
                if ($key % 3 == 0) {
                    $result .= '
        <div class="row">';
                }

                $field['value'] = isset($field['value']) ? $field['value'] : '';

                $replace = [
                    'value' => $field['value'],
                    'label' => $field['name'],
                    'name' => $field['html_name'],
                ];

                foreach ($replace as $name => $value) {
                    $field['content'] = str_replace('|'.$name.'|', $value, $field['content']);
                }

                $result .= '
            <div class="col-4">
                '.$field['content'].'
            </div>';

                if (($key + 1) % 3 == 0) {
                    $result .= '
        </div>';
                }
            }

            if (($key + 1) % 3 != 0) {
                $result .= '
        </div>';
            }

            $result .= '
    </div>
</div>';
        }

        return $result;
    }
}
