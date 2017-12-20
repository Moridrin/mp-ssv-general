<?php

namespace mp_ssv_general\custom_fields;

use Exception;
use mp_ssv_general\custom_fields\input_fields\CheckboxInputField;
use mp_ssv_general\custom_fields\input_fields\CustomInputField;
use mp_ssv_general\custom_fields\input_fields\DateInputField;
use mp_ssv_general\custom_fields\input_fields\HiddenInputField;
use mp_ssv_general\custom_fields\input_fields\ImageInputField;
use mp_ssv_general\custom_fields\input_fields\RoleCheckboxInputField;
use mp_ssv_general\custom_fields\input_fields\RoleSelectInputField;
use mp_ssv_general\custom_fields\input_fields\SelectInputField;
use mp_ssv_general\custom_fields\input_fields\TextInputField;
use mp_ssv_general\Message;
use mp_ssv_general\SSV_General;
use mp_ssv_general\User;

if (!defined('ABSPATH')) {
    exit;
}

require_once 'input-fields/TextInputField.php';
require_once 'input-fields/CheckboxInputField.php';
require_once 'input-fields/SelectInputField.php';
require_once 'input-fields/ImageInputField.php';
require_once 'input-fields/HiddenInputField.php';
require_once 'input-fields/CustomInputField.php';
require_once 'input-fields/DateInputField.php';
require_once 'input-fields/RoleCheckboxInputField.php';
require_once 'input-fields/RoleSelectInputField.php';

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 6-1-17
 * Time: 6:38
 */
abstract class InputField extends Field
{
    const FIELD_TYPE = 'input';

    public $inputType;
    private $overrideRights;
    private $value;

    protected function __construct(int $id, string $name, string $title, string $inputType, int $order = null, array $classes = [], array $styles = [], array $overrideRights = [])
    {
        parent::__construct($id, $title, self::FIELD_TYPE, $name, $order, $classes, $styles);
        $this->inputType     = $inputType;
        $this->overrideRights = $overrideRights;
    }

    public static function fromJSON(string $json): Field
    {
        $values = json_decode($json);
        switch ($values->input_type) {
            case TextInputField::INPUT_TYPE:
                return new TextInputField(...json_decode($json, true));
            case SelectInputField::INPUT_TYPE:
                return new SelectInputField(...json_decode($json, true));
            case CheckboxInputField::INPUT_TYPE:
                return new CheckboxInputField(...json_decode($json, true));
            case DateInputField::INPUT_TYPE:
                return new DateInputField(...json_decode($json, true));
            case RoleCheckboxInputField::INPUT_TYPE:
                return new RoleCheckboxInputField(...json_decode($json, true));
            case RoleSelectInputField::INPUT_TYPE:
                return new RoleSelectInputField(...json_decode($json, true));
            case ImageInputField::INPUT_TYPE:
                return new ImageInputField(...json_decode($json, true));
            case HiddenInputField::INPUT_TYPE:
                return new HiddenInputField(...json_decode($json, true));
            default:
                return new CustomInputField(...json_decode($json, true));
        }
    }

    public abstract function getHTML(): string;

    public abstract function getFilterRow(): string;

    public function getFilterRowBase(string $filter): string
    {
        ob_start();
        ?>
        <td>
            <label for="<?= esc_html($this->order) ?>"><?= esc_html($this->title) ?></label>
        </td>
        <td>
            <label>
                Filter
                <input id="filter_<?= esc_html($this->order) ?>" type="checkbox" name="filter_<?= esc_html($this->name) ?>">
            </label>
        </td>
        <td>
            <?= $filter ?>
        </td>
        <?php
        return ob_get_clean();
    }

    /**
     * @return Message[]|bool array of errors or true if no errors.
     */
    public abstract function isValid();

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        if ($this instanceof HiddenInputField) {
            return; //Can't change the value of hidden fields.
        }
        if ($value instanceof User) { //User values can always be set (even if isDisabled())
            $this->value = $value->getMeta($this->name);
        } elseif (is_array($value)) {
            if (isset($value[$this->name])) {
                $this->value = SSV_General::sanitize($value[$this->name], $this->inputType);
            }
        } else {
            $this->value = SSV_General::sanitize($value, $this->inputType);
        }
    }

    public function isDisabled(): bool
    {
        return isset($this->disabled) ? $this->disabled : false;
    }

    public function updateName($id, $postID)
    {
        global $wpdb;
        $table = SSV_General::CUSTOM_FORM_FIELDS_TABLE;
        $sql   = "SELECT customField FROM $table WHERE ID = $id AND postID = $postID";
        $json  = $wpdb->get_var($sql);
        if (empty($json)) {
            return;
        }
        $field = Field::fromJSON($json);
        if (!$field instanceof InputField) {
            return;
        }
        $wpdb->update(
            $wpdb->usermeta,
            array(
                'meta_key' => $this->name,
            ),
            array(
                'meta_key' => $field->name,
            ),
            array(
                '%s',
            ),
            array(
                '%s',
            )
        );
    }

    public function currentUserCanOcerride(): bool
    {
        foreach ($this->overrideRights as $overrideRight) {
            if (current_user_can($overrideRight)) {
                return true;
            }
        }
        return false;
    }

    function __toString(): string
    {
        return $this->name;
    }

}
