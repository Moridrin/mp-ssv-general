<?php

namespace mp_ssv_general\custom_fields\input_fields;

use Exception;
use mp_ssv_general\custom_fields\InputField;
use mp_ssv_general\Message;
use mp_ssv_general\SSV_General;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 10-1-17
 * Time: 12:03
 */
class CustomInputField extends InputField
{
    public $disabled;
    public $required;
    public $defaultValue;
    public $placeholder;

    protected function __construct(int $id, string $name, string $title, string $type, int $order = null, array $classes = [], array $styles = [], array $overrideRights = [], bool $disabled = false, bool $required = false, bool $defaultChecked = false)
    {
        parent::__construct($id, $name, $title, $type, $order, $classes, $styles, $overrideRights);
        $this->disabled     = filter_var($disabled, FILTER_VALIDATE_BOOLEAN);
        $this->required     = filter_var($required, FILTER_VALIDATE_BOOLEAN);
        $this->defaultValue = $defaultValue;
        $this->placeholder  = $placeholder;
    }

    /**
     * @param string $json
     *
     * @return CustomInputField
     * @throws Exception
     */
    public static function fromJSON($json)
    {
        $values = json_decode($json);
        return new CustomInputField(
            $values->container_id,
            $values->order,
            $values->title,
            $values->input_type,
            $values->name,
            $values->disabled,
            $values->required,
            $values->default_value,
            $values->placeholder,
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
            'container_id'   => $this->containerID,
            'order'          => $this->order,
            'title'          => $this->title,
            'field_type'     => $this->fieldType,
            'input_type'     => $this->inputType,
            'name'           => $this->name,
            'disabled'       => $this->disabled,
            'required'       => $this->required,
            'default_value'  => $this->defaultValue,
            'placeholder'    => $this->placeholder,
            'class'          => $this->classes,
            'style'          => $this->styles,
            'override_right' => $this->overrideRights,
        );
        $values = json_encode($values);
        return $values;
    }

    /**
     * @return string the field as HTML object.
     */
    public function getHTML()
    {
        $value       = !empty($this->value) ? $this->value : $this->defaultValue;
        $inputType   = 'type="' . esc_html($this->inputType) . '"';
        $name        = 'name="' . esc_html($this->name) . '"';
        $class       = !empty($this->classes) ? 'class="' . esc_html($this->classes) . '"' : '';
        $style       = !empty($this->styles) ? 'style="' . esc_html($this->styles) . '"' : '';
        $placeholder = !empty($this->placeholder) ? 'placeholder="' . esc_html($this->placeholder) . '"' : '';
        $value       = !empty($value) ? 'value="' . esc_html($value) . '"' : '';
        $disabled    = disabled($this->disabled, true, false);
        $required    = $this->required ? 'required="required"' : '';

        if (!empty($this->overrideRights) && current_user_can($this->overrideRights)) {
            $disabled = '';
            $required = '';
        }

        ob_start();
        if (current_theme_supports('materialize')) {
            ?>
            <div>
                <label for="<?= esc_html($this->order) ?>"><?= esc_html($this->title) ?><?= $this->required ? '*' : '' ?></label>
                <input <?= $inputType ?> id="<?= esc_html($this->order) ?>" <?= $name ?> <?= $class ?> <?= $style ?> <?= $value ?> <?= $disabled ?> <?= $placeholder ?> <?= $required ?>/>
            </div>
            <?php
        } else {
            ?>
            <div>
                <label for="<?= esc_html($this->order) ?>"><?= esc_html($this->title) ?><?= $this->required ? '*' : '' ?></label><br/>
                <input <?= $inputType ?> id="<?= esc_html($this->order) ?>" <?= $name ?> <?= $class ?> <?= $style ?> <?= $value ?> <?= $disabled ?> <?= $placeholder ?> <?= $required ?>/><br/>
            </div>
            <?php
        }

        return trim(preg_replace('/\s\s+/', ' ', ob_get_clean()));
    }

    /**
     * @return string the filter for this field as HTML object.
     */
    public function getFilterRow()
    {
        ob_start();
        ?><input id="<?= esc_html($this->order) ?>" type="text" name="<?= esc_html($this->name) ?>" title="<?= esc_html($this->title) ?>"/><?php
        return $this->getFilterRowBase(ob_get_clean());
    }

    /**
     * @return Message[]|bool array of errors or true if no errors.
     */
    public function isValid()
    {
        $errors = array();
        if (($this->required && !$this->disabled) && empty($this->value)) {
            $errors[] = new Message($this->title . ' field is required but not set.', current_user_can($this->overrideRights) ? Message::SOFT_ERROR_MESSAGE : Message::ERROR_MESSAGE);
        }
        switch (strtolower($this->inputType)) {
            case 'iban':
                $this->value = str_replace(' ', '', strtoupper($this->value));
                if (!empty($this->value) && !SSV_General::isValidIBAN($this->value)) {
                    $errors[] = new Message($this->title . ' field is not a valid IBAN.', current_user_can($this->overrideRights) ? Message::SOFT_ERROR_MESSAGE : Message::ERROR_MESSAGE);
                }
                break;
            case 'email':
                if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = new Message($this->title . ' field is not a valid email.', current_user_can($this->overrideRights) ? Message::SOFT_ERROR_MESSAGE : Message::ERROR_MESSAGE);
                }
                break;
            case 'url':
                if (!filter_var($this->value, FILTER_VALIDATE_URL)) {
                    $errors[] = new Message($this->title . ' field is not a valid url.', current_user_can($this->overrideRights) ? Message::SOFT_ERROR_MESSAGE : Message::ERROR_MESSAGE);
                }
                break;
        }
        return empty($errors) ? true : $errors;
    }
}
