<?php

namespace mp_ssv_general\custom_fields\input_fields;

use Exception;
use mp_ssv_general\custom_fields\InputField;
use mp_ssv_general\Message;
use mp_ssv_general\User;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 10-1-17
 * Time: 12:03
 */
class CheckboxInputField extends InputField
{
    const INPUT_TYPE = 'checkbox';

    /** @var bool $disabled */
    public $disabled;
    /** @var bool $required */
    public $required;
    /** @var bool $defaultChecked */
    public $defaultChecked;

    /**
     * CheckboxInputField constructor.
     *
     * @param int    $order
     * @param string $title
     * @param string $name
     * @param bool   $disabled
     * @param string $required
     * @param string $defaultChecked
     * @param string $class
     * @param string $style
     * @param string $overrideRight
     */
    protected function __construct($containerID, $order, $title, $name, $disabled, $required, $defaultChecked, $class, $style, $overrideRight)
    {
        parent::__construct($containerID, $order, $title, self::INPUT_TYPE, $name, $class, $style, $overrideRight);
        $this->disabled       = filter_var($disabled, FILTER_VALIDATE_BOOLEAN);
        $this->required       = filter_var($required, FILTER_VALIDATE_BOOLEAN);
        $this->defaultChecked = filter_var($defaultChecked, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param string $json
     *
     * @return CheckboxInputField
     * @throws Exception
     */
    public static function fromJSON($json)
    {
        $values = json_decode($json);
        if ($values->input_type != self::INPUT_TYPE) {
            throw new Exception('Incorrect input type');
        }
        return new CheckboxInputField(
            $values->container_id,
            $values->order,
            $values->title,
            $values->name,
            $values->disabled,
            $values->required,
            $values->default_checked,
            $values->class,
            $values->style,
            $values->override_right
        );
    }

    /**
     * @return string the class as JSON object.
     */
    public function toJSON()
    {
        $values = array(
            'container_id'    => $this->containerID,
            'order'           => $this->order,
            'title'           => $this->title,
            'field_type'      => $this->fieldType,
            'input_type'      => $this->inputType,
            'name'            => $this->name,
            'disabled'        => $this->disabled,
            'required'        => $this->required,
            'default_checked' => $this->defaultChecked,
            'class'           => $this->class,
            'style'           => $this->style,
            'override_right'  => $this->overrideRight,
        );
        $values = json_encode($values);
        return $values;
    }

    /**
     * @return string the field as HTML object.
     */
    public function getHTML()
    {
        $isChecked = is_bool($this->value) ? $this->value : $this->defaultChecked;
        $name      = 'name="' . esc_html($this->name) . '"';
        $class     = !empty($this->class) ? 'class="' . esc_html($this->class) . '"' : 'class="validate filled-in"';
        $style     = !empty($this->style) ? 'style="' . esc_html($this->style) . '"' : '';
        $required  = $this->required ? 'required="required"' : '';
        $disabled  = disabled($this->disabled, true, false);
        $checked   = checked($isChecked, true, false);

        if (isset($overrideRight) && current_user_can($overrideRight)) {
            $disabled = '';
            $required = '';
        }

        ob_start();
        ?>
        <div <?= $style ?>>
            <input type="hidden" id="<?= esc_html($this->order) ?>_reset" <?= $name ?> value="false"/>
            <input type="checkbox" id="<?= esc_html($this->order) ?>" <?= $name ?> value="true" <?= $class ?> <?= $checked ?> <?= $disabled ?> <?= $required ?>/>
            <label for="<?= esc_html($this->order) ?>"><?= esc_html($this->title) ?><?= $this->required ? '*' : '' ?></label>
        </div>
        <?php

        return trim(preg_replace('/\s\s+/', ' ', ob_get_clean()));
    }

    /**
     * @return string the filter for this field as HTML object.
     */
    public function getFilterRow()
    {
        ob_start();
        ?>
        <select id="<?= esc_html($this->order) ?>" name="<?= esc_html($this->name) ?>" title="<?= esc_html($this->title) ?>">
            <option value="false">Not Checked</option>
            <option value="true">Checked</option>
        </select>
        <?php
        return $this->getFilterRowBase(ob_get_clean());
    }

    /**
     * @return Message[]|bool array of errors or true if no errors.
     */
    public function isValid()
    {
        $errors = array();
        if (($this->required && !$this->disabled) && (empty($this->value) || !is_bool($this->value) || !$this->value)) {
            $errors[] = new Message($this->title . ' is required but not set.', current_user_can($this->overrideRight) ? Message::SOFT_ERROR_MESSAGE : Message::ERROR_MESSAGE);
        }
        return empty($errors) ? true : $errors;
    }

    /**
     * @param string|array|User|mixed $value
     */
    public function setValue($value)
    {
        parent::setValue($value);
        $this->value = filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
    }
}
