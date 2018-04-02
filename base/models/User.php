<?php

namespace mp_ssv_general\base;

if (!defined('ABSPATH')) {
    exit;
}

class User extends \WP_User
{
    function __construct(\WP_User $user)
    {
        parent::__construct($user);
    }

    public static function getByID(int $id): ?User
    {
        $user = new User(get_user_by('id', $id));
        if ($user->ID != $id) {
            return null;
        }
        return $user;
    }

    public static function getCurrent(): ?User
    {
        if (!is_user_logged_in()) {
            return null;
        }
        return new User(wp_get_current_user());
    }

    public static function register(string $username, string $password, string $email): ?User
    {
        if (empty($username) || empty($password) || empty($email)) {
            $_SESSION['SSV']['errors'][] = 'You cannot register without Username, Password and Email.';
            return null;
        }
        if (username_exists($username)) {
            $_SESSION['SSV']['errors'][] = 'This username already exists.';
            return null;
        }
        if (email_exists($email)) {
            $_SESSION['SSV']['errors'][] = 'This email address already exists. Try resetting your password.';
            return null;
        }

        $id = wp_create_user(
            BaseFunctions::sanitize($username, 'text'),
            BaseFunctions::sanitize($password, 'text'),
            BaseFunctions::sanitize($email, 'email')
        );
        if ($id instanceof \WP_Error) {
            $_SESSION['SSV']['errors'][] = $id->get_error_message();
            return null;
        }
        return self::getByID($id);
    }

    public function isCurrentUser(): bool
    {
        return $this->ID == wp_get_current_user()->ID;
    }

    public function checkPassword(string $password): bool
    {
        return wp_check_password($password, $this->data->user_pass, $this->ID);
    }

    public function updateMeta(string $metaKey, $value, $sanitize = true): bool
    {
        if (strpos($metaKey, 'password') !== false || strpos($metaKey, 'pwd') !== false) {
            return true;
        }
        if ($sanitize) {
            $value = BaseFunctions::sanitize($value, $metaKey);
        }
        if ($this->getMeta($metaKey) == $value) {
            return true;
        }

        switch ($metaKey) {
            case 'email':
            case 'email_address':
            case 'user_email':
            case 'member_email':
                $this->user_email = $value;
                $response         = wp_update_user($this);
                if ($response instanceof \WP_Error) {
                    $_SESSION['SSV']['errors'][] = $response->get_error_message();
                    return false;
                } else {
                    return true;
                }
            case 'name':
            case 'display_name':
                $this->display_name = $value;
                $response           = wp_update_user($this);
                if ($response instanceof \WP_Error) {
                    $_SESSION['SSV']['errors'][] = $response->get_error_message();
                    return false;
                } else {
                    return true;
                }
            case 'first_name':
            case 'firstname':
            case 'fname':
                $this->first_name   = $value;
                $this->display_name = $this->first_name . ' ' . $this->last_name;
                $response           = wp_update_user($this);
                if ($response instanceof \WP_Error) {
                    $_SESSION['SSV']['errors'][] = $response->get_error_message();
                    return false;
                } else {
                    return true;
                }
            case 'last_name':
            case 'lastname':
            case 'lname':
                $this->last_name    = $value;
                $this->display_name = $this->first_name . ' ' . $this->last_name;
                $response           = wp_update_user($this);
                if ($response instanceof \WP_Error) {
                    $_SESSION['SSV']['errors'][] = $response->get_error_message();
                    return false;
                } else {
                    return true;
                }
            case 'login':
            case 'username':
            case 'user_name':
            case 'user_login':
                return false;
            default:
                $response = update_user_meta($this->ID, $metaKey, $value);
                if ($response === false) {
                    $_SESSION['SSV']['errors'][] = 'Something went wrong while trying to update '.$metaKey.' in the user\'s meta data.';
                    return false;
                } else {
                    return true;
                }
        }
    }

    public function getMeta(string $metaKey, $default = ''): string
    {
        switch ($metaKey) {
            case 'email':
            case 'email_address':
            case 'user_email':
            case 'member_email':
                return $this->user_email;
            case 'name':
            case 'display_name':
                return $this->display_name;
            case 'login':
            case 'username':
            case 'user_name':
            case 'user_login':
                return $this->user_login;
            default:
                $value = get_user_meta($this->ID, $metaKey, true);
                return $value ?: $default;
        }
    }

    public function removeMeta(string $metaKey): bool
    {
        $userProfileFields = [
            'email',
            'email_address',
            'user_email',
            'member_email',
            'name',
            'display_name',
            'login',
            'username',
            'user_name',
            'user_login',
        ];

        if (!in_array($metaKey, $userProfileFields)) {
            $success = delete_user_meta($this->ID, $metaKey);
            if (!$success) {
                $_SESSION['SSV']['errors'][] = 'Something went wrong while trying to remove '.$metaKey.' from the user\'s meta data.';
            }
            return $success;
        } else {
            $_SESSION['SSV']['errors'][] = 'You cannot remove a profile field.';
            return false;
        }
    }
}