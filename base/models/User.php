<?php

namespace mp_ssv_general\base;

use mp_ssv_general\custom_fields\Field;
use mp_ssv_general\custom_fields\input_fields\CustomInputField;
use mp_ssv_general\custom_fields\input_fields\HiddenInputField;
use mp_ssv_general\custom_fields\input_fields\TextInputField;
use mp_ssv_general\custom_fields\InputField;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 14:48
 */
class User extends \WP_User
{
    /**
     * User constructor.
     *
     * @param \WP_User $user the WP_User component used as base for the User
     */
    function __construct($user)
    {
        parent::__construct($user);
    }

    /**
     * This function searches for a User by its ID.
     *
     * @param int $id is the ID used to find the SSV_User
     *
     * @return User|false returns the User it found or null if it can't find one.
     */
    public static function getByID($id)
    {
        if ($id == null) {
            return false;
        }
        $user = new User(get_user_by('id', $id));
        if ($user->ID != $id) {
            return false;
        }
        return $user;
    }

    /**
     * @return bool|User
     */
    public static function getCurrent()
    {
        if (!is_user_logged_in()) {
            return false;
        }
        return new User(wp_get_current_user());
    }

    /**
     * @param $username
     * @param $password
     * @param $email
     *
     * @return Message|User
     */
    public static function register($username, $password, $email)
    {
        if (empty($username) || empty($password) || empty($email)) {
            return new Message('You cannot register without Username, Password and Email.', Message::ERROR_MESSAGE);
        }
        if (username_exists($username)) {
            return new Message('This username already exists.', Message::ERROR_MESSAGE);
        }
        if (email_exists($email)) {
            return new Message('This email address already exists. Try resetting your password.', Message::ERROR_MESSAGE);
        }
        $id = wp_create_user(
            BaseFunctions::sanitize($username, 'text'),
            BaseFunctions::sanitize($password, 'text'),
            BaseFunctions::sanitize($email, 'email')
        );

        return self::getByID($id);
    }

    public static function getDefaultFields()
    {
        /** @var HiddenInputField $registrationDateField */
        $registrationDateField = Field::fromJSON(
            json_encode(
                array(
                    'container_id'   => '',
                    'order'          => 0,
                    'override_right' => '',
                    'title'          => 'Registration Date',
                    'field_type'     => 'input',
                    'input_type'     => 'hidden',
                    'name'           => 'registration_date',
                    'default_value'  => 'NOW',
                    'class'          => '',
                    'style'          => '',
                )
            )
        );

        /** @var TextInputField $usernameField */
        $usernameField = Field::fromJSON(
            json_encode(
                array(
                    'container_id'   => '',
                    'order'          => 0,
                    'override_right' => '',
                    'title'          => 'Username',
                    'field_type'     => 'input',
                    'input_type'     => 'text',
                    'name'           => 'username',
                    'disabled'       => false,
                    'required'       => true,
                    'default_value'  => '',
                    'placeholder'    => '',
                    'class'          => '',
                    'style'          => '',
                )
            )
        );

        /** @var TextInputField $emailField */
        $emailField = Field::fromJSON(
            json_encode(
                array(
                    'container_id'   => '',
                    'order'          => 0,
                    'override_right' => '',
                    'title'          => 'Email',
                    'field_type'     => 'input',
                    'input_type'     => 'text',
                    'name'           => 'email',
                    'disabled'       => false,
                    'required'       => true,
                    'default_value'  => '',
                    'placeholder'    => '',
                    'class'          => '',
                    'style'          => '',
                )
            )
        );

        /** @var CustomInputField $passwordField */
        $passwordField = Field::fromJSON(
            json_encode(
                array(
                    'container_id'   => '',
                    'order'          => 0,
                    'override_right' => '',
                    'title'          => 'Password',
                    'field_type'     => 'input',
                    'input_type'     => 'password',
                    'name'           => 'password',
                    'disabled'       => false,
                    'required'       => true,
                    'default_value'  => '',
                    'placeholder'    => '',
                    'class'          => 'validate',
                    'style'          => '',
                )
            )
        );

        /** @var CustomInputField $confirmPasswordField */
        $confirmPasswordField = Field::fromJSON(
            json_encode(
                array(
                    'container_id'   => '',
                    'order'          => 0,
                    'override_right' => '',
                    'title'          => 'Confirm Password',
                    'field_type'     => 'input',
                    'input_type'     => 'password',
                    'name'           => 'password_confirm',
                    'disabled'       => false,
                    'required'       => true,
                    'default_value'  => '',
                    'placeholder'    => '',
                    'class'          => 'validate',
                    'style'          => '',
                )
            )
        );

        return array(
            $registrationDateField->name => $registrationDateField,
            $usernameField->name         => $usernameField,
            $emailField->name            => $emailField,
            $passwordField->name         => $passwordField,
            $confirmPasswordField->name  => $confirmPasswordField,
        );
    }

    public static function getPasswordChangeFields()
    {
        /** @var CustomInputField $currentPassword */
        $currentPassword = Field::fromJSON(
            json_encode(
                array(
                    'container_id'   => '',
                    'order'          => 0,
                    'override_right' => '',
                    'title'          => 'Current Password',
                    'field_type'     => 'input',
                    'input_type'     => 'password',
                    'name'           => 'current_password',
                    'disabled'       => false,
                    'required'       => true,
                    'default_value'  => '',
                    'placeholder'    => '',
                    'class'          => '',
                    'style'          => '',
                )
            )
        );

        /** @var CustomInputField $newPassword */
        $newPassword = Field::fromJSON(
            json_encode(
                array(
                    'container_id'   => '',
                    'order'          => 0,
                    'override_right' => '',
                    'title'          => 'New Password',
                    'field_type'     => 'input',
                    'input_type'     => 'password',
                    'name'           => 'new_password',
                    'disabled'       => false,
                    'required'       => true,
                    'default_value'  => '',
                    'placeholder'    => '',
                    'class'          => '',
                    'style'          => '',
                )
            )
        );

        /** @var CustomInputField $confirmNewPassword */
        $confirmNewPassword = Field::fromJSON(
            json_encode(
                array(
                    'container_id'   => '',
                    'order'          => 0,
                    'override_right' => '',
                    'title'          => 'Confirm New Password',
                    'field_type'     => 'input',
                    'input_type'     => 'password',
                    'name'           => 'confirm_new_password',
                    'disabled'       => false,
                    'required'       => true,
                    'default_value'  => '',
                    'placeholder'    => '',
                    'class'          => '',
                    'style'          => '',
                )
            )
        );

        return array(
            $currentPassword->name    => $currentPassword,
            $newPassword->name        => $newPassword,
            $confirmNewPassword->name => $confirmNewPassword,
        );
    }

    /**
     * @return bool returns true if this is the current user.
     */
    public function isCurrentUser()
    {
        if ($this->ID == wp_get_current_user()->ID) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $capability
     *
     * @return bool true if this user has the given capability.
     */
    public static function currentUserCan($capability)
    {
        if (!is_user_logged_in()) {
            return false;
        }
        return self::getCurrent()->has_cap($capability);
    }

    /**
     * @param string $password The plaintext new user password
     *
     * @return bool false, if the $password does not match the member's password
     */
    public function checkPassword($password)
    {
        return wp_check_password($password, $this->data->user_pass, $this->ID);
    }

    /**
     * This function sets the metadata defined by the key (or an alias of that key).
     * The aliases are:
     *  - email, email_address, member_email => user_email
     *  - name => display_name
     *  - login, username, user_name => user_login
     * The function will also update the display name after the first or last name is updated.
     *
     * @param string $meta_key the key that defines which metadata to set.
     * @param string $value    the value to set.
     * @param bool   $sanitize set false if the value is already sanitized.
     *
     * @return bool|Message true if success, else it provides an object consisting of a message and a type (notification or error).
     */
    function updateMeta($meta_key, $value, $sanitize = true)
    {
        if (strpos($meta_key, 'password') !== false || strpos($meta_key, 'pwd') !== false) {
            return true;
        }
        if ($sanitize) {
            $value = BaseFunctions::sanitize($value, $meta_key);
        }
        if ($this->getMeta($meta_key) == $value) {
            return true;
        }
        if ($meta_key == "email" || $meta_key == "email_address" || $meta_key == "user_email" || $meta_key == "member_email") {
            wp_update_user(array('ID' => $this->ID, 'user_email' => $value));
            update_user_meta($this->ID, 'user_email', $value);
            $this->user_email = $value;
            return true;
        } elseif ($meta_key == "name" || $meta_key == "display_name") {
            wp_update_user(array('ID' => $this->ID, 'display_name' => $value));
            update_user_meta($this->ID, 'display_name', $value);
            $this->display_name = $value;
            return true;
        } elseif ($meta_key == "first_name" || $meta_key == "firstname" || $meta_key == "fname" || $meta_key == "last_name" || $meta_key == "lastname" || $meta_key == "lname") {
            if ($meta_key == "first_name" || $meta_key == "firstname" || $meta_key == "fname") {
                $this->first_name = $value;
                wp_update_user(array('ID' => $this->ID, 'first_name' => $value));
            } else {
                $this->last_name = $value;
                wp_update_user(array('ID' => $this->ID, 'last_name' => $value));
            }
            update_user_meta($this->ID, $meta_key, $value);
            $display_name       = $this->getMeta('first_name') . ' ' . $this->getMeta('last_name');
            $this->display_name = $display_name;
            wp_update_user(array('ID' => $this->ID, 'display_name' => $display_name));
            update_user_meta($this->ID, 'display_name', $display_name);
            return true;
        } elseif ($meta_key == "login" || $meta_key == "username" || $meta_key == "user_name" || $meta_key == "user_login") {
            return new Message('Cannot change the user-login.', Message::ERROR_MESSAGE); //cannot change user_login
        } else {
            update_user_meta($this->ID, $meta_key, $value);
            return true;
        }
    }

    /**
     * This function returns the metadata associated with the given key (or an alias of that key).
     * The aliases are:
     *  - email, email_address, member_email => user_email
     *  - name => display_name
     *  - login, username, user_name => user_login
     *
     * @param string $meta_key defines which metadata should be returned.
     * @param mixed  $default  is the value returned if there is no value associated with the key.
     *
     * @return string the value associated with the key or the default value if there is no value associated with the key.
     */
    function getMeta($meta_key, $default = '')
    {
        if ($meta_key == "email" || $meta_key == "email_address" || $meta_key == "user_email" || $meta_key == "member_email") {
            return $this->user_email;
        } elseif ($meta_key == "name" || $meta_key == "display_name") {
            return $this->display_name;
        } elseif ($meta_key == "login" || $meta_key == "username" || $meta_key == "user_name" || $meta_key == "user_login") {
            return $this->user_login;
        } else {
            $value = get_user_meta($this->ID, $meta_key, true);
            return $value ?: $default;
        }
    }

    /**
     * @param string $target
     *
     * @return string of the full <a> tag.
     */
    public function getProfileLink($target = '')
    {
        $href   = esc_url($this->getProfileURL());
        $target = empty($target) ? '' : 'target="' . $target . '"';
        $label  = $this->display_name;
        return "<a href='$href' $target>$label</a>";
    }

    /**
     * @return string the url for the users profile
     */
    public function getProfileURL()
    {
        $url = get_edit_user_link($this->ID);
        $url = apply_filters(BaseFunctions::HOOK_USER_PROFILE_URL, $url, $this);
        return $url;
    }
}