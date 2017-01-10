<?php

require_once 'input-fields/TextInputField.php';
require_once 'input-fields/CheckboxInputField.php';
require_once 'input-fields/SelectInputField.php';
require_once 'input-fields/ImageInputField.php';
require_once 'input-fields/CustomInputField.php';

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 6-1-17
 * Time: 6:38
 */
class InputField extends Field
{
    const FIELD_TYPE = 'input';

    /** @var string $inputType */
    public $inputType;

    /**
     * InputField constructor.
     *
     * @param int    $id
     * @param string $title
     * @param string $inputType
     */
    protected function __construct($id, $title, $inputType)
    {
        parent::__construct($id, $title, self::FIELD_TYPE);
        $this->inputType = $inputType;
    }

    /**
     * @param string $json
     *
     * @return InputField
     */
    public static function fromJSON($json)
    {
        $values = json_decode($json);
        switch ($values->input_type) {
            case TextInputField::INPUT_TYPE:
                return TextInputField::fromJSON($json);
            case SelectInputField::INPUT_TYPE:
                return SelectInputField::fromJSON($json);
            default:
                return CustomInputField::fromJSON($json);
        }
    }

    /**
     * @return string the class as JSON object.
     * @throws Exception if the method is not implemented by a sub class.
     */
    public function toJSON()
    {
        throw new Exception('This should be implemented in a sub class.');
    }
}
