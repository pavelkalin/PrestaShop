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

include_once(_PS_MODULE_DIR_ . '/getresponse/classes/DbConnection.php');
include_once(_PS_MODULE_DIR_ . '/getresponse/classes/GrApiException.php');
include_once(_PS_MODULE_DIR_ . '/getresponse/classes/GetResponseAPI3.php');
include_once(_PS_MODULE_DIR_ . '/getresponse/classes/GrApi.php');
include_once(_PS_MODULE_DIR_ . '/getresponse/classes/GrShop.php');

class Getresponse extends Module
{
    /** @var DbConnection */
    private $db;

    /** @var GrApi */
    private $api = null;

    /** @var array */
    private $settings = null;

    public function __construct()
    {
        $this->name                   = 'getresponse';
        $this->tab                    = 'emailing';
        $this->version                = '16.1.5';
        $this->author                 = 'GetResponse';
        $this->need_instance          = 0;
        $this->module_key             = '7e6dc54b34af57062a5e822bd9b8d5ba';
        $this->ps_versions_compliancy = array('min' => '1.5.6.2', 'max' => _PS_VERSION_);
        $this->displayName            = $this->l('GetResponse');

        $this->description            = $this->l(
            'Add your Prestashop contacts to GetResponse or manage them via automation rules. Automatically follow-up new subscriptions with engaging email marketing campaigns'
        );
        $this->confirmUninstall       = $this->l(
            'Warning: all the module data will be deleted. Are you sure you want uninstall this module?'
        );

        parent::__construct();

        $this->db = new DbConnection(Db::getInstance(), GrShop::getUserShopId());

        if (version_compare(_PS_VERSION_, '1.5') === -1) {
            $this->context->smarty->assign(array('flash_message' => array(
                'message' => $this->l('Unsupported Prestashop version'),
                'status' => 'danger'
            )));
        }

        if (!function_exists('curl_init')) {
            $this->context->smarty->assign(array('flash_message' => array(
                'message' => $this->l('Curl library not found'),
                'status' => 'danger'
            )));
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

    /**
     * @return bool
     */
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

        $this->db->prepareDatabase();
        return true;
    }

    /******************************************************************/
    /** Uninstall Methods *********************************************/
    /******************************************************************/

    public function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminGetresponse');
        if (false === $id_tab) {
            return false;
        }
        $tab = new Tab($id_tab);
        return $tab->delete();
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminGetresponse'));
    }

    /**
     * @return bool
     */
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

        $this->db->clearDatabase();
        return true;
    }

    /**
     * @return GrApi
     * @throws Exception
     */
    private function getApi()
    {
        if ($this->api === null) {
            $settings = $this->getSettings();
            $this->api = new GrApi($settings['api_key'], $settings['account_type'], $settings['crypto']);
        }

        return $this->api;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getSettings()
    {
        if ($this->settings === null) {
            $this->settings = $this->db->getSettings();
        }

        if (empty($this->settings['api_key'])) {
            throw new Exception('Not connected');
        }

        return $this->settings;
    }

    /******************************************************************/
    /** Hook Methods **************************************************/
    /******************************************************************/

    /**
     * @param array $params
     */
    public function hookNewOrder($params)
    {
        try {
            $this->addSubscriberForOrder($params);
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @param array $params
     */
    public function hookCreateAccount($params)
    {
        try {
            $this->createSubscriber($params);
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @param array $params
     */
    public function createSubscriber($params)
    {
        $settings = $this->getSettings();
        if (isset($settings['active_subscription'])
            && $settings['active_subscription'] == 'yes'
            && !empty($settings['campaign_id'])
        ) {
            if (isset($params['newNewsletterContact'])) {
                $prefix = 'newNewsletterContact';
            } else {
                $prefix  = 'newCustomer';
            }

            $customs = $this->getApi()->mapCustoms((array)$params[$prefix], null, $this->db->getCustoms(), 'create');
            $this->addContact($params[$prefix], $customs);
        }
    }

    /**
     * @param array $params
     *
     * @throws Exception
     */
    public function addSubscriberForOrder($params)
    {
        $prefix = 'customer';

        //update_contact
        $contact = $this->db->getContactByEmail($params[$prefix]->email);
        $customs = $this->getApi()->mapCustoms((array) $contact, $_POST, $this->db->getCustoms(), 'order');

        // automation
        if (!empty($params['order']->product_list)) {
            $categories = array();
            foreach ($params['order']->product_list as $products) {
                $temp_categories = Product::getProductCategories($products['id_product']);
                foreach ($temp_categories as $tmp) {
                    $categories[$tmp] = $tmp;
                }
            }

            $automations = $this->db->getAutomationSettings(true);
            if (!empty($automations)) {
                $default = true;
                foreach ($automations as $automation) {
                    if (in_array($automation['category_id'], $categories)) {
                        // do automation
                        if ($automation['action'] == 'move') {
                            $settings = $this->getSettings();
                            $this->getApi()->moveContactToGr(
                                $automation['campaign_id'],
                                $params[$prefix]->firstname,
                                $params[$prefix]->lastname,
                                $params[$prefix]->email,
                                $customs,
                                $settings['cycle_day']
                            );
                        } elseif ($automation['action'] == 'copy') {
                            $this->addContact($params[$prefix], $customs);
                        }
                        $default = false;
                    }
                }

                if ($default) {
                    $this->addContact($params[$prefix], $customs);
                }
                return; //return so we do not hit standard case
            }
        }

        // standard case
        $this->addContact($params[$prefix], $customs);
    }

    /**
     * @return string
     */
    public function hookDisplayRightColumn()
    {
        return $this->displayWebForm('right');
    }

    /**
     * @return string
     */
    public function hookDisplayLeftColumn()
    {
        return $this->displayWebForm('left');
    }

    /**
     * @return string
     */
    public function hookDisplayHeader()
    {
        return $this->displayWebForm('header');
    }

    /**
     * @return string
     */
    public function hookDisplayTop()
    {
        return $this->displayWebForm('top');
    }

    /**
     * @return string
     */
    public function hookDisplayFooter()
    {
        if (Tools::isSubmit('submitNewsletter')
            && '0' == Tools::getValue('action')
            && Validate::isEmail(Tools::getValue('email'))
        ) {
            $settings = $this->db->getSettings();

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

                $this->createSubscriber($data);
            }
        }

        return $this->displayWebForm('footer');
    }

    /**
     * @return string
     */
    public function hookDisplayHome()
    {
        return $this->displayWebForm('home');
    }

    /**
     * @param string $position
     *
     * @return string
     */
    private function displayWebForm($position)
    {
        if (empty($position)) {
            return '';
        }

        $webform_settings = $this->db->getWebformSettings();
        if (empty($webform_settings) ||
            $webform_settings['active_subscription'] != 'yes' ||
            $webform_settings['sidebar'] != $position
        ) {
            return '';
        }

        $set_style = null;
        if (!empty($webform_settings['style']) && $webform_settings['style'] == 'prestashop') {
            $set_style = '&css=1';
        }

        $this->smarty->assign(array('webform_url' => $webform_settings['url'], 'style' => $set_style));
        return $this->display(__FILE__, 'views/templates/admin/getresponse/helpers/view/webform.tpl');
    }

    /**
     * @param object $contact
     * @param array $customs
     *
     * @throws Exception
     */
    private function addContact($contact, $customs)
    {
        $settings = $this->getSettings();
        if (isset($contact->newsletter) && $contact->newsletter == 1) {
            $this->getApi()->addContact(
                $settings['campaign_id'],
                $contact->firstname,
                $contact->lastname,
                $contact->email,
                $settings['cycle_day'],
                $customs
            );
        }
    }
}
