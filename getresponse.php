<?php
/**
 * This module integrate GetResponse and PrestaShop Allows subscribe via checkout page and export your contacts.
 *
 *  @author Getresponse <grintegrations@getresponse.com>
 *  @copyright GetResponse
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

define('_PS_CLASS_DIR', _PS_MODULE_DIR_ . '/getresponse/classes');

class Getresponse extends Module
{
    public function __construct()
    {
        $this->name                   = 'getresponse';
        $this->tab                    = 'emailing';
        $this->version                = '4.0.8';
        $this->author                 = 'GetResponse';
        $this->need_instance          = 0;
        $this->module_key             = '7e6dc54b34af57062a5e822bd9b8d5ba';
        $this->ps_versions_compliancy = array('min' => '1.5.6.2', 'max' => _PS_VERSION_);
        $this->displayName            = $this->l('GetResponse');
        $this->description            = $this->l('Add your Prestashop contacts to GetResponse or manage them via '
            . 'automation rules. Automatically follow-up new subscriptions with engaging email marketing campaigns');
        $this->confirmUninstall       = $this->l(
            'Warning: all the module data will be deleted. Are you sure you want uninstall this module?'
        );

        // API urls
        $this->api_urls = array(
            'gr' => 'https://api.getresponse.com/v3'
        );

        parent::__construct();

        if (!Configuration::get('getresponse')) {
            $this->warning = $this->l('No name provided');
        }

        require_once(_PS_CLASS_DIR . '/DbConnection.php');
        $instance = Db::getInstance();
        $this->db = new DbConnection($instance);

        if (version_compare(_PS_VERSION_, '1.5') == '-1') {
            return false;
        }

        if (!function_exists('curl_init')) {
            return false;
        }
    }

    /******************************************************************/
    /** Install Methods ***********************************************/
    /******************************************************************/

    public function installTab()
    {
        $tab             = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'AdminGetresponse';
        $tab->name       = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'GetResponse';
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminAdmin');
        $tab->module    = $this->name;

        return $tab->add();
    }

    public function install()
    {
        if (!parent::install() ||!$this->installTab() ||!$this->registerHook('newOrder') ||
            !$this->registerHook('createAccount') || $this->registerHook('leftColumn') == false ||
            $this->registerHook('rightColumn') == false || $this->registerHook('header') == false ||
            $this->registerHook('footer') == false || $this->registerHook('top') == false ||
            $this->registerHook('home') == false
        ) {
            return false;
        }

        //Update Version Number
        if (!Configuration::updateValue('GR_VERSION', $this->version)) {
            return false;
        }

        $sql = array();

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'getresponse_settings` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`id_shop` char(32) NOT NULL,
			`api_key` char(32) NOT NULL,
			`active_subscription` enum(\'yes\',\'no\') NOT NULL DEFAULT \'no\',
			`active_newsletter_subscription` enum(\'yes\',\'no\') NOT NULL DEFAULT \'no\',
			`update_address` enum(\'yes\',\'no\') NOT NULL DEFAULT \'no\',
			`campaign_id` char(5) NOT NULL,
			`cycle_day` char(5) NOT NULL,
			`account_type` enum(\'gr\',\'360en\',\'360pl\') NOT NULL DEFAULT \'gr\',
			`crypto` char(32) NULL,
			PRIMARY KEY (`id`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'getresponse_customs` (
			`id_custom` int(11) NOT NULL AUTO_INCREMENT,
			`id_shop` int(6) NOT NULL,
			`custom_field` char(32) NOT NULL,
			`custom_value` char(32) NOT NULL,
			`custom_name` char(32) NOT NULL,
			`default` enum(\'yes\',\'no\') NOT NULL DEFAULT \'no\',
			`active_custom` enum(\'yes\',\'no\') NOT NULL DEFAULT \'no\',
			PRIMARY KEY (`id_custom`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'getresponse_webform` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`id_shop` int(6) NOT NULL,
			`webform_id` char(32) NOT NULL,
			`active_subscription` enum(\'yes\',\'no\') NOT NULL DEFAULT \'no\',
			`sidebar` enum(\'left\',\'right\',\'header\',\'top\',\'footer\',\'home\') NOT NULL DEFAULT \'home\',
			`style` enum(\'webform\',\'prestashop\') NOT NULL DEFAULT \'webform\',
			`url` varchar(255) DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'getresponse_automation` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`id_shop` int(6) NOT NULL,
			`category_id` char(32) NOT NULL,
			`campaign_id` char(32) NOT NULL,
			`action` char(32) NOT NULL DEFAULT \'move\',
			`cycle_day` char(5) NOT NULL,
			`active` enum(\'yes\',\'no\') NOT NULL DEFAULT \'yes\',
			PRIMARY KEY (`id`),
			UNIQUE KEY `id_shop` (`id_shop`,`category_id`,`campaign_id`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        //multistore
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
            $shops = Shop::getShops();

            if (!empty($shops) && is_array($shops)) {
                foreach ($shops as $shop) {
                    $sql[] = $this->sqlMainSetting($shop['id_shop']);
                    $sql[] = $this->sqlWebformSetting($shop['id_shop']);
                    $sql[] = $this->sqlCustomsSetting($shop['id_shop']);
                }
            }
        } else {
            $sql[] = $this->sqlMainSetting('1');
            $sql[] = $this->sqlWebformSetting('1');
            $sql[] = $this->sqlCustomsSetting('1');
        }

        //Install SQL
        foreach ($sql as $s) {
            if (!Db::getInstance()->Execute($s)) {
                return false;
            }
        }

        return true;
    }

    private function sqlMainSetting($store_id)
    {
        if (empty($store_id)) {
            return false;
        }

        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'getresponse_settings` (
				`id_shop` ,
				`api_key` ,
				`active_subscription` ,
				`active_newsletter_subscription` ,
				`update_address` ,
				`campaign_id` ,
				`cycle_day` ,
				`account_type` ,
				`crypto`
				)
				VALUES (
				' . (int) $store_id . ',  \'\',  \'no\', \'no\',  \'no\',  \'0\',  \' \',  \'gr\',  \'\'
				)
				ON DUPLICATE KEY UPDATE
				`id` = `id`;
			';

        return $sql;
    }

    private function sqlWebformSetting($store_id)
    {
        if (empty($store_id)) {
            return false;
        }

        $sql = 'INSERT INTO  `' . _DB_PREFIX_ . 'getresponse_webform` (
				`id_shop` ,
				`webform_id` ,
				`active_subscription` ,
				`sidebar`,
				`style`
				)
				VALUES (
				' . (int) $store_id . ',  \'\',  \'no\',  \'left\',  \'webform\'
				)
				ON DUPLICATE KEY UPDATE
				`id` = `id`;
			';

        return $sql;
    }

    private function sqlCustomsSetting($store_id)
    {
        if (empty($store_id)) {
            return false;
        }

        $sql = 'INSERT INTO  `' . _DB_PREFIX_ . 'getresponse_customs` (
				`id_shop` ,
				`custom_field`,
				`custom_value`,
				`custom_name`,
				`default`,
				`active_custom`
				)
				VALUES
				(' . (int) $store_id . ', \'firstname\', \'firstname\', \'firstname\', \'yes\', \'yes\'),
				(' . (int) $store_id . ', \'lastname\', \'lastname\', \'lastname\', \'yes\', \'yes\'),
				(' . (int) $store_id . ', \'email\', \'email\', \'email\', \'yes\', \'yes\'),
				(' . (int) $store_id . ', \'address\', \'address1\', \'address\', \'no\', \'no\'),
				(' . (int) $store_id . ', \'postal\', \'postcode\', \'postal\', \'no\', \'no\'),
				(' . (int) $store_id . ', \'city\', \'city\', \'city\', \'no\', \'no\'),
				(' . (int) $store_id . ', \'phone\', \'phone\', \'phone\', \'no\', \'no\'),
				(' . (int) $store_id . ', \'country\', \'country\', \'country\', \'no\', \'no\'),
				(' . (int) $store_id . ', \'birthday\', \'birthday\', \'birthday\', \'no\', \'no\'),
				(' . (int) $store_id . ', \'company\', \'company\', \'company\', \'no\', \'no\'),
				(' . (int) $store_id . ', \'category\', \'category\', \'category\', \'no\', \'no\');';

        return $sql;
    }

    /******************************************************************/
    /** Uninstall Methods *********************************************/
    /******************************************************************/

    public function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminGetresponse');
        if ($id_tab) {
            $tab = new Tab($id_tab);

            return $tab->delete();
        } else {
            return false;
        }
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminGetresponse'));
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !$this->uninstallTab() || !$this->unregisterHook('newOrder') ||
            !$this->registerHook('createAccount') || !$this->registerHook('leftColumn') ||
            !$this->registerHook('rightColumn') || !$this->registerHook('header') ||
            !$this->registerHook('footer') || !$this->registerHook('top') || !$this->registerHook('home')
        ) {
            return false;
        }

        //Delete Version Entry
        if (!Configuration::deleteByName('GR_VERSION')) {
            return false;
        }

        // Uninstall SQL
        $sql   = array();
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'getresponse_settings`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'getresponse_customs`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'getresponse_webform`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'getresponse_automation`;';

        foreach ($sql as $s) {
            if (!Db::getInstance()->Execute($s)) {
                return false;
            }
        }

        return true;
    }

    /******************************************************************/
    /** Hook Methods **************************************************/
    /******************************************************************/

    public function hookNewOrder($params)
    {
        $this->addSubscriber($params, 'order');
    }

    public function hookCreateAccount($params)
    {
        $this->addSubscriber($params, 'create');
    }

    private function addSubscriber($params, $action)
    {
        $settings = $this->db->settings;

        if (!empty($settings['api_key'])) {
            if (
                ('create' === $action
                && isset($settings['active_subscription'])
                && $settings['active_subscription'] == 'yes'
                && !empty($settings['campaign_id'])
                )
                || 'order' === $action
            ) {
                $this->db->addSubscriber(
                    $params,
                    $settings['campaign_id'],
                    $action,
                    $settings['cycle_day']
                );
            }
        }
    }

    public function hookDisplayRightColumn()
    {
        return $this->displayWebform('right');
    }

    public function hookDisplayLeftColumn()
    {
        return $this->displayWebform('left');
    }

    public function hookDisplayHeader()
    {
        return $this->displayWebform('header');
    }

    public function hookDisplayTop()
    {
        return $this->displayWebform('top');
    }

    public function hookDisplayFooter()
    {
        if (Tools::isSubmit('submitNewsletter')
            && '0' == Tools::getValue('action')
            && Validate::isEmail(Tools::getValue('email'))
        ) {
            $settings = $this->db->settings;

            if (isset($settings['active_newsletter_subscription'])
                && $settings['active_newsletter_subscription'] == 'yes'
            ) {
                $client = new stdClass();
                $client->newsletter = 1;
                $client->firstname = 'Friend';
                $client->lastname = '';
                $client->email = Tools::getValue('email');

                $data = array();
                $data['newNewsletterContact'] = $client;

                $this->addSubscriber($data, 'create');
            }
        }
        return $this->displayWebform('footer');
    }

    public function hookDisplayHome()
    {
        return $this->displayWebform('home');
    }

    private function displayWebform($position)
    {
        if (empty($position)) {
            return false;
        }

        $webform_settings = $this->db->getWebformSettings();
        if (empty($webform_settings) ||
            $webform_settings['active_subscription'] != 'yes' ||
            $webform_settings['sidebar'] != $position
        ) {
            return false;
        }

        $set_style = null;
        if (!empty($webform_settings['style']) && $webform_settings['style'] == 'prestashop') {
            $set_style = '&css=1';
        }

        $this->smarty->assign(array('webform_url' => $webform_settings['url'], 'style' => $set_style));

        return $this->display(__FILE__, 'views/templates/admin/getresponse/helpers/view/webform.tpl');
    }
}
