<?php

namespace mp_ssv_forms\options;

use mp_ssv_forms\models\SSV_Forms;
use mp_ssv_general\base\BaseFunctions;
use stdClass;
use wpdb;

if (!defined('ABSPATH')) {
    exit;
}

require_once 'templates/base-form-fields-table.php';
require_once 'templates/forms-table.php';
require_once 'templates/form-editor.php';

abstract class Forms
{

    public static function filterContent($content)
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $table = SSV_Forms::SITE_SPECIFIC_FORMS_TABLE;
        $forms = $wpdb->get_results("SELECT * FROM $table");
        foreach ($forms as $form) {
            if (strpos($content, $form->f_tag) !== false) {
                $content = str_replace($form->f_tag, self::getFormHTML($form), $content);
            }
        }
        return $content;
    }

    public static function setupNetworkMenu()
    {
        add_menu_page('SSV Forms', 'SSV Forms', 'edit_posts', 'ssv_forms', [self::class, 'showSharedBaseFieldsPage']);
    }

    public static function setupSiteSpecificMenu()
    {
        add_menu_page('SSV Forms', 'SSV Forms', 'ssv_not_allowed', 'ssv_forms');
        add_submenu_page('ssv_forms', 'All Forms', 'All Forms', 'edit_posts', 'ssv_forms', [self::class, 'showFormsPage']);
        add_submenu_page('ssv_forms', 'Add New', 'Add New', 'edit_posts', 'ssv_forms_add_new_form', [self::class, 'showNewFormPage']);
        add_submenu_page('ssv_forms', 'Manage Fields', 'Manage Fields', 'edit_posts', 'ssv_forms_base_fields_manager', [self::class, 'showSiteBaseFieldsPage']);
    }

    public static function showSharedBaseFieldsPage()
    {
        ?>
        <div class="wrap">
            <?php
            if (BaseFunctions::isValidPOST(SSV_Forms::ALL_FORMS_ADMIN_REFERER)) {
                if ($_POST['action'] === 'delete-selected' && !isset($_POST['_inline_edit'])) {
                    mp_ssv_general_forms_delete_shared_base_fields(false);
                } elseif ($_POST['action'] === '-1' && isset($_POST['_inline_edit'])) {
                    $_POST['values'] = [
                        'bf_id'        => $_POST['_inline_edit'],
                        'bf_name'      => $_POST['name'],
                        'bf_title'     => $_POST['title'],
                        'bf_inputType' => $_POST['inputType'],
                        'bf_value'     => isset($_POST['value']) ? $_POST['value'] : null,
                    ];
                    mp_ssv_general_forms_save_shared_base_field(false);
                } else {
                    echo '<div class="notice error">Something unexpected happened. Please try again.</div>';
                }
            }
            /** @var wpdb $wpdb */
            global $wpdb;
            $order      = isset($_GET['order']) ? BaseFunctions::sanitize($_GET['order'], 'text') : 'asc';
            $orderBy    = isset($_GET['orderby']) ? BaseFunctions::sanitize($_GET['orderby'], 'text') : 'bf_title';
            $baseTable  = SSV_Forms::SHARED_BASE_FIELDS_TABLE;
            $baseFields = $wpdb->get_results("SELECT * FROM $baseTable ORDER BY $orderBy $order");
            $addNew     = '<a href="javascript:void(0)" class="page-title-action" onclick="mp_ssv_add_new_base_input_field()">Add New</a>';
            ?>
            <h1 class="wp-heading-inline"><span>Shared Form Fields</span><?= current_user_can('manage_shared_base_fields') ? $addNew : '' ?></h1>
            <p>These fields will be available for all sites.</p>
            <?php
            self::showFieldsManager($baseFields, $order, $orderBy, current_user_can('manage_shared_base_fields'), ['Role Checkbox', 'Role Select']);
            ?>
        </div>
        <?php
    }

    public static function getWordPressBaseFields(): array
    {
        return json_decode(
            json_encode(
                [
                    [
                        'bf_id'        => null,
                        'bf_name'      => 'username',
                        'bf_title'     => 'Username',
                        'bf_inputType' => 'text',
                        'bf_value'     => null,
                    ],
                    [
                        'bf_id'        => null,
                        'bf_name'      => 'first_name',
                        'bf_title'     => 'First Name',
                        'bf_inputType' => 'text',
                        'bf_value'     => null,
                    ],
                    [
                        'bf_id'        => null,
                        'bf_name'      => 'last_name',
                        'bf_title'     => 'Last Name',
                        'bf_inputType' => 'text',
                        'bf_value'     => null,
                    ],
                    [
                        'bf_id'        => null,
                        'bf_name'      => 'email',
                        'bf_title'     => 'Email',
                        'bf_inputType' => 'email',
                        'bf_value'     => null,
                    ],
                    [
                        'bf_id'        => null,
                        'bf_name'      => 'password',
                        'bf_title'     => 'Password',
                        'bf_inputType' => 'password',
                        'bf_value'     => null,
                    ],
                    [
                        'bf_id'        => null,
                        'bf_name'      => 'password_confirm',
                        'bf_title'     => 'Confirm Password',
                        'bf_inputType' => 'password',
                        'bf_value'     => null,
                    ],
                ]
            )
        );
    }

    public static function showFormsPage()
    {
        ?>
        <div class="wrap">
            <?php
            if (BaseFunctions::isValidPOST(SSV_Forms::EDIT_FORM_ADMIN_REFERER)) {
                /** @var wpdb $wpdb */
                global $wpdb;
                $wpdb->replace(
                    SSV_Forms::SITE_SPECIFIC_FORMS_TABLE,
                    [
                        'f_id'     => $_POST['form_id'],
                        'f_tag'    => $_POST['form_tag'],
                        'f_title'  => $_POST['form_title'],
                        'f_fields' => json_encode($_POST['form_fields']),
                    ]
                );
            } elseif (BaseFunctions::isValidPOST(SSV_Forms::ALL_FORMS_ADMIN_REFERER)) {
                if ($_POST['action'] === 'delete-selected' && !isset($_POST['_inline_edit'])) {
                    mp_ssv_general_forms_delete_site_specific_base_fields(false);
                } elseif ($_POST['action'] === '-1' && isset($_POST['_inline_edit'])) {
                    $value = isset($_POST['value']) ? $_POST['value'] : null;
                    if (is_array($value)) {
                        $value = implode(';', $value);
                    }
                    $_POST['values'] = [
                        'bf_id'        => $_POST['_inline_edit'],
                        'bf_name'      => $_POST['name'],
                        'bf_title'     => $_POST['title'],
                        'bf_inputType' => $_POST['inputType'],
                        'bf_value'     => $value,
                    ];
                    mp_ssv_general_forms_save_site_specific_base_field(false);
                } else {
                    echo '<div class="notice error"><p>Something unexpected happened. Please try again.</p></div>';
                }
            }
            /** @var wpdb $wpdb */
            global $wpdb;
            $order   = BaseFunctions::sanitize(isset($_GET['order']) ? $_GET['order'] : 'asc', 'text');
            $orderBy = BaseFunctions::sanitize(isset($_GET['orderby']) ? $_GET['orderby'] : 'f_title', 'text');
            $table   = SSV_Forms::SITE_SPECIFIC_FORMS_TABLE;
            $forms   = $wpdb->get_results("SELECT * FROM $table ORDER BY $orderBy $order");
            $addNew  = '<a href="?page=ssv_forms_add_new_form" class="page-title-action">Add New</a>';
            ?>
            <h1 class="wp-heading-inline"><span>Site Specific Forms</span><?= current_user_can('manage_site_specific_forms') ? $addNew : '' ?></h1>
            <p>These forms will only be available for <?= get_bloginfo() ?>.</p>
            <form method="post" action="#">
                <?php
                show_forms_table($forms, $order, $orderBy, current_user_can('manage_site_specific_forms'));
                if (current_user_can('manage_site_specific_forms')) {
                    echo BaseFunctions::getFormSecurityFields(SSV_Forms::ALL_FORMS_ADMIN_REFERER, false, false);
                }
                ?>
            </form>
            <?php
            ?>
        </div>
        <?php
    }

    public static function showNewFormPage()
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $sharedBaseFieldsTable       = SSV_Forms::SHARED_BASE_FIELDS_TABLE;
        $siteSpecificBaseFieldsTable = SSV_Forms::SITE_SPECIFIC_BASE_FIELDS_TABLE;
        $formsTable                  = SSV_Forms::SITE_SPECIFIC_FORMS_TABLE;
        $baseFields                  = $wpdb->get_results("SELECT * FROM (SELECT * FROM $sharedBaseFieldsTable UNION SELECT * FROM $siteSpecificBaseFieldsTable) combined ORDER BY bf_title");
        $newId                       = $wpdb->get_row("SELECT MAX(f_id) AS maxId FROM $formsTable")->maxId + 1;
        show_form_editor($newId, $baseFields);
    }

    public static function showSiteBaseFieldsPage()
    {
        $activeTab = "shared";
        if (isset($_GET['tab'])) {
            $activeTab = $_GET['tab'];
        }
        $function = 'showSiteBaseFields' . ucfirst($activeTab) . 'Tab';
        ?>
        <div class="wrap">
            <h2 class="nav-tab-wrapper">
                <a href="?page=<?= esc_html($_GET['page']) ?>&tab=shared" class="nav-tab <?= $activeTab === 'shared' ? 'nav-tab-active' : '' ?>">Shared</a>
                <a href="?page=<?= esc_html($_GET['page']) ?>&tab=siteSpecific" class="nav-tab <?= $activeTab === 'siteSpecific' ? 'nav-tab-active' : '' ?>">Site Specific</a>
                <a href="http://bosso.nl/plugins/ssv-file-manager/" target="_blank" class="nav-tab">
                    Help <!--suppress HtmlUnknownTarget -->
                    <img src="<?= esc_url(BaseFunctions::URL) ?>/images/link-new-tab-small.png" width="14" style="vertical-align:middle">
                </a>
            </h2>
            <?php
            if (method_exists(Forms::class, $function)) {
                self::$function();
            } else {
                ?>
                <div class="notice error"><p>Unknown Tab</p></div><?php
            }
            ?>
        </div>
        <?php
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function showSiteBaseFieldsSharedTab()
    {
        if (BaseFunctions::isValidPOST(SSV_Forms::ALL_FORMS_ADMIN_REFERER)) {
            if ($_POST['action'] === 'delete-selected' && !isset($_POST['_inline_edit'])) {
                mp_ssv_general_forms_delete_shared_base_fields(false);
            } elseif ($_POST['action'] === '-1' && isset($_POST['_inline_edit'])) {
                $_POST['values'] = [
                    'bf_id'        => $_POST['_inline_edit'],
                    'bf_name'      => $_POST['name'],
                    'bf_title'     => $_POST['title'],
                    'bf_inputType' => $_POST['inputType'],
                    'bf_value'     => isset($_POST['value']) ? $_POST['value'] : null,
                ];
                mp_ssv_general_forms_save_shared_base_field(false);
            } else {
                echo '<div class="notice error">Something unexpected happened. Please try again.</div>';
            }
        }
        /** @var wpdb $wpdb */
        global $wpdb;
        $order      = BaseFunctions::sanitize(isset($_GET['order']) ? $_GET['order'] : 'asc', 'text');
        $orderBy    = BaseFunctions::sanitize(isset($_GET['orderby']) ? $_GET['orderby'] : 'bf_title', 'text');
        $baseTable  = SSV_Forms::SHARED_BASE_FIELDS_TABLE;
        $baseFields = $wpdb->get_results("SELECT * FROM $baseTable ORDER BY $orderBy $order");
        $addNew     = '<a href="javascript:void(0)" class="page-title-action" onclick="mp_ssv_add_new_base_input_field()">Add New</a>';
        ?>
        <h1 class="wp-heading-inline"><span>Shared Form Fields</span><?= current_user_can('manage_shared_base_fields') ? $addNew : '' ?></h1>
        <p>These fields will be available for all sites.</p>
        <?php
        self::showFieldsManager($baseFields, $order, $orderBy, current_user_can('manage_shared_base_fields'), ['Role Checkbox', 'Role Select']);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function showSiteBaseFieldsSiteSpecificTab()
    {
        if (BaseFunctions::isValidPOST(SSV_Forms::ALL_FORMS_ADMIN_REFERER)) {
            if ($_POST['action'] === 'delete-selected' && !isset($_POST['_inline_edit'])) {
                mp_ssv_general_forms_delete_site_specific_base_fields(false);
            } elseif ($_POST['action'] === '-1' && isset($_POST['_inline_edit'])) {
                $value = isset($_POST['value']) ? $_POST['value'] : null;
                if (is_array($value)) {
                    $value = implode(';', $value);
                }
                $_POST['values'] = [
                    'bf_id'        => $_POST['_inline_edit'],
                    'bf_name'      => $_POST['name'],
                    'bf_title'     => $_POST['title'],
                    'bf_inputType' => $_POST['inputType'],
                    'bf_value'     => $value,
                ];
                mp_ssv_general_forms_save_site_specific_base_field(false);
            } else {
                echo '<div class="notice error"><p>Something unexpected happened. Please try again.</p></div>';
            }
        }
        /** @var wpdb $wpdb */
        global $wpdb;
        $order      = BaseFunctions::sanitize(isset($_GET['order']) ? $_GET['order'] : 'asc', 'text');
        $orderBy    = BaseFunctions::sanitize(isset($_GET['orderby']) ? $_GET['orderby'] : 'bf_title', 'text');
        $baseTable  = SSV_Forms::SITE_SPECIFIC_BASE_FIELDS_TABLE;
        $baseFields = $wpdb->get_results("SELECT * FROM $baseTable ORDER BY $orderBy $order");
        $addNew     = '<a href="javascript:void(0)" class="page-title-action" onclick="mp_ssv_add_new_base_input_field()">Add New</a>';
        ?>
        <h1 class="wp-heading-inline"><span>Site Specific Form Fields</span><?= current_user_can('manage_site_specific_base_fields') ? $addNew : '' ?></h1>
        <p>These fields will only be available for <?= get_bloginfo() ?>.</p>
        <?php
        self::showFieldsManager($baseFields, $order, $orderBy, current_user_can('manage_site_specific_base_fields'));
    }

    private static function showFieldsManager($baseFields, $order, $orderBy, $hasManageRight, $excludedRoles = [])
    {
        ?>
        <form method="post" action="#">
            <?php
            echo BaseFunctions::getInputTypeDataList($excludedRoles);
            show_base_form_fields_table($baseFields, $order, $orderBy, $hasManageRight);
            if ($hasManageRight) {
                ?>
                <script>
                    let i = <?= count($baseFields) > 0 ? max(array_column($baseFields, 'bf_id')) + 1 : 1 ?>;

                    function mp_ssv_add_new_base_input_field() {
                        event.preventDefault();
                        mp_ssv_add_base_input_field('the-list', i, '', '', '');
                        document.getElementById(i + '_title').focus();
                        i++;
                    }
                </script>
                <?= BaseFunctions::getFormSecurityFields(SSV_Forms::ALL_FORMS_ADMIN_REFERER, false, false) ?>
                <?php
            }
            ?>
        </form>
        <?php
    }

    private static function getFormHTML(stdClass $form): string
    {
        $fields              = json_decode($form->f_fields);
        $wordPressBaseFields = self::getWordPressBaseFields();
        $formFields          = array_filter(
            $wordPressBaseFields,
            function ($field) use ($fields) {
                return in_array($field->bf_name, $fields);
            }
        );
        /** @var wpdb $wpdb */
        global $wpdb;
        $fields                      = '"' . implode('", "', $fields) . '"';
        $sharedBaseFieldsTable       = SSV_Forms::SHARED_BASE_FIELDS_TABLE;
        $siteSpecificBaseFieldsTable = SSV_Forms::SITE_SPECIFIC_BASE_FIELDS_TABLE;
        $formFields                  = array_merge($formFields, $wpdb->get_results("SELECT * FROM (SELECT * FROM $sharedBaseFieldsTable UNION SELECT * FROM $siteSpecificBaseFieldsTable) combined WHERE bf_name IN ($fields)"));
        ob_start();
        foreach ($formFields as $field) {
            $field = json_decode(json_encode($field), true);
            $newField = [];
            foreach ($field as $key => $value) {
                $newField[str_replace('bf_', '', $key)] = $value;
            }
            require_once 'templates/fields/text-input.php';
            show_text_input_field($newField);
        }
        return ob_get_clean();
    }
}

add_action('network_admin_menu', [Forms::class, 'setupNetworkMenu'], 9);
add_action('admin_menu', [Forms::class, 'setupSiteSpecificMenu'], 9);
add_filter('the_content', [Forms::class, 'filterContent'], 9);