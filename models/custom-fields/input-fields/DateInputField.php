<?php

namespace mp_ssv_general\custom_fields\input_fields;

use DateTime;
use Exception;
use mp_ssv_general\custom_fields\InputField;
use mp_ssv_general\Message;
use mp_ssv_general\SSV_General;
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
class DateInputField extends InputField
{
    const INPUT_TYPE = 'datetime';

    /** @var bool $disabled */
    public $disabled;
    /** @var array $required */
    public $required;
    /** @var string $defaultValue */
    public $defaultValue;
    /** @var string $placeholder */
    public $placeholder;

    /**
     * DateTimeInputField constructor.
     *
     * @param int    $id
     * @param string $title
     * @param string $inputType
     * @param string $name
     * @param bool   $disabled
     * @param string $required
     * @param string $defaultValue
     * @param string $placeholder
     * @param string $class
     * @param string $style
     * @param string $overrideRight
     */
    protected function __construct($id, $title, $name, $disabled, $required, $defaultValue, $placeholder, $class, $style, $overrideRight)
    {
        parent::__construct($id, $title, self::INPUT_TYPE, $name, $class, $style, $overrideRight);
        $this->disabled     = filter_var($disabled, FILTER_VALIDATE_BOOLEAN);
        $this->required     = filter_var($required, FILTER_VALIDATE_BOOLEAN);
        $this->defaultValue = $defaultValue;
        $this->placeholder  = $placeholder;
    }

    /**
     * @param string $json
     *
     * @return DateInputField
     * @throws Exception
     */
    public static function fromJSON($json)
    {
        $values = json_decode($json);
        if ($values->input_type != self::INPUT_TYPE) {
            throw new Exception('Incorrect input type');
        }
        return new DateInputField(
            $values->id,
            $values->title,
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
     * @param bool $encode
     *
     * @return string the class as JSON object.
     */
    public function toJSON($encode = true)
    {
        $values = array(
            'id'             => $this->id,
            'title'          => $this->title,
            'field_type'     => $this->fieldType,
            'input_type'     => $this->inputType,
            'name'           => $this->name,
            'disabled'       => $this->disabled,
            'required'       => $this->required,
            'default_value'  => $this->defaultValue,
            'placeholder'    => $this->placeholder,
            'class'          => $this->class,
            'style'          => $this->style,
            'override_right' => $this->overrideRight,
        );
        if ($encode) {
            $values = json_encode($values);
        }
        return $values;
    }

    /**
     * @return string the field as HTML object.
     */
    public function getHTML($overrideRight)
    {
        if ($this->defaultValue == 'NOW') {
            $this->defaultValue = (new DateTime('NOW'))->format('Y-m-d');
        }
        $value       = isset($this->value) ? $this->value : $this->defaultValue;
        $name        = 'name="' . esc_html($this->name) . '"';
        $class       = !empty($this->class) ? 'class="' . esc_html($this->class) . '"' : '';
        $style       = !empty($this->style) ? 'style="' . esc_html($this->style) . '"' : '';
        $placeholder = !empty($this->placeholder) ? 'placeholder="' . esc_html($this->placeholder) . '"' : '';
        $value       = !empty($value) ? 'value="' . esc_html($value) . '"' : '';
        $disabled    = disabled($this->disabled, true, false);
        $required    = $this->required ? 'required="required"' : '';

        if (isset($overrideRight) && current_user_can($overrideRight)) {
            $disabled = '';
            $required = '';
        }

        ob_start();
        if (current_theme_supports('materialize')) {
            ?>
            <div>
                <label for="<?= esc_html($this->id) ?>"><?= esc_html($this->title) ?><?= $this->required ? '*' : '' ?></label>
                <input type="date" id="<?= esc_html($this->id) ?>" <?= $name ?> <?= $class ?> <?= $style ?> <?= $value ?> <?= $disabled ?> <?= $placeholder ?> <?= $required ?>/>
            </div>
            <?php
            if ($this->required) {
                ?>
                <script>
                    jQuery(function ($) {
                        var dateField = $('#<?= esc_html($this->id) ?>');
                        dateField.change(function () {
                            if (dateField.val() === '') {
                                dateField.addClass('invalid')
                            } else {
                                dateField.removeClass('invalid')
                            }
                        });
                    });
                </script>
                <?php
            }
        }

        return trim(preg_replace('/\s\s+/', ' ', ob_get_clean()));
    }

    /**
     * @return string the filter for this field as HTML object.
     */
    public function getFilterRow()
    {
        ob_start();
        ?><input id="<?= esc_html($this->id) ?>" type="date" name="<?= esc_html($this->name) ?>" title="<?= esc_html($this->title) ?>"/><?php
        return $this->getFilterRowBase(ob_get_clean());
    }

    /**
     * @return Message[]|bool array of errors or true if no errors.
     */
    public function isValid()
    {
        $errors = array();
        if (($this->required && !$this->disabled) && empty($this->value)) {
            $errors[] = new Message($this->title . ' field is required but not set.', current_user_can($this->overrideRight) ? Message::SOFT_ERROR_MESSAGE : Message::ERROR_MESSAGE);
        }

        $date = DateTime::createFromFormat('Y-m-d', $this->value);
        if (!empty($this->value) && (!$date || $date->format('Y-m-d') !== $this->value)) {
            $errors[] = new Message($this->value . ' field is not a valid date.', Message::ERROR_MESSAGE);
        }

        return empty($errors) ? true : $errors;
    }
}
