<?php

namespace mp_ssv_general\custom_fields\input_fields;

use Exception;
use mp_ssv_general\BaseFunctions;
use mp_ssv_general\custom_fields\InputField;
use mp_ssv_general\Message;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 10-1-17
 * Time: 12:03
 */
class ImageInputField extends InputField
{
    const INPUT_TYPE = 'image';

    /** @var string $preview */
    public $preview;
    /** @var array $required */
    public $required;

    /**
     * ImageInputField constructor.
     *
     * @param int    $order
     * @param string $title
     * @param string $name
     * @param bool   $preview
     * @param string $required
     * @param string $class
     * @param string $style
     * @param string $overrideRight
     */
    protected function __construct($containerID, $order, $title, $name, $preview, $required, $class, $style, $overrideRight)
    {
        parent::__construct($containerID, $order, $title, self::INPUT_TYPE, $name, $class, $style, $overrideRight);
        $this->required = filter_var($required, FILTER_VALIDATE_BOOLEAN);
        $this->preview  = $preview;
    }

    /**
     * @param string $json
     *
     * @return ImageInputField
     * @throws Exception
     */
    public static function fromJSON($json)
    {
        $values = json_decode($json);
        if ($values->input_type != self::INPUT_TYPE) {
            throw new Exception('Incorrect input type');
        }
        return new ImageInputField(
            $values->container_id,
            $values->order,
            $values->title,
            $values->name,
            $values->preview,
            $values->required,
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
            'preview'        => $this->preview,
            'required'       => $this->required,
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
        $name     = 'name="' . esc_html($this->name) . '"';
        $class    = !empty($this->classes) ? 'class="' . esc_html($this->classes) . '"' : 'class="validate"';
        $style    = !empty($this->styles) ? 'style="' . esc_html($this->styles) . '"' : '';
        $required = $this->required && !empty($this->value) ? 'required="required"' : '';

        if (!empty($this->overrideRights) && current_user_can($this->overrideRights)) {
            $required = '';
        }

        ob_start();
        if (current_theme_supports('materialize')) {
            ?>
            <div style="padding-top: 10px;">
                <label for="<?= esc_html($this->order) ?>"><?= esc_html($this->title) ?><?= $this->required ? '*' : '' ?></label><br/>
                <?php if ($this->preview): ?>
                    <img src="<?= esc_url($this->value) ?>" <?= $class ?> <?= $style ?>/>
                <?php endif; ?>
                <div class="file-field input-field">
                    <div class="btn">
                        <span>Image</span>
                        <input type="file" id="<?= esc_html($this->order) ?>" <?= $name ?> <?= $class ?> <?= $style ?> <?= $required ?>>
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text" title="<?= esc_html($this->title) ?>">
                    </div>
                </div>
            </div>
            <?php
        } else {
            ?>
            <label for="<?= esc_html($this->order) ?>"><?= esc_html($this->title) ?><?= $this->required ? '*' : '' ?></label><br/>
            <?php if ($this->preview): ?>
                <img src="<?= esc_url($this->value) ?>" <?= $class ?> <?= $style ?>/>
            <?php endif; ?>
            <input type="file" id="<?= esc_html($this->order) ?>" <?= $name ?> <?= $class ?> <?= $style ?> <?= $required ?>><br/>
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
        ?>
        <select id="<?= esc_html($this->order) ?>" name="<?= esc_html($this->name) ?>" title="<?= esc_html($this->title) ?>">
            <option value="0">No Image</option>
            <option value="1">Has Image</option>
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
        if ($this->required && empty($this->value)) {
            $errors[] = new Message($this->title . ' is required but not set.', current_user_can($this->overrideRights) ? Message::SOFT_ERROR_MESSAGE : Message::ERROR_MESSAGE);
        } elseif (!empty($this->value) && !mp_ssv_starts_with($this->value, BaseFunctions::BASE_URL)) {
            $errors[] = new Message($this->title . ' has an incorrect url.', current_user_can($this->overrideRights) ? Message::SOFT_ERROR_MESSAGE : Message::ERROR_MESSAGE);
        }
        return empty($errors) ? true : $errors;
    }
}