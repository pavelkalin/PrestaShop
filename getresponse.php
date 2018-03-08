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
include_once(_PS_MODULE_DIR_ . '/getresponse/classes/GrEcommerce.php');
include_once(_PS_MODULE_DIR_ . '/getresponse/classes/exceptions/GrGeneralException.php');
include_once(_PS_MODULE_DIR_ . '/getresponse/classes/exceptions/GrConfigurationNotFoundException.php');

class Getresponse extends Module
{
    /** @var DbConnection */
    private $db;

    /** @var GrApi */
    private $api;

    /** @var array */
    private $settings;

    private $usedHooks = array(
        'newOrder',
        'createAccount',
        'leftColumn',
        'rightColumn',
        'header',
        'footer',
        'top',
        'home',
        'cart',
        'postUpdateOrderStatus',
        'hookOrderConfirmation',
        'displayBackOfficeHeader'
    );

    public function __construct()
    {
        $this->name                   = 'getresponse';
        $this->tab                    = 'emailing';
        $this->version                = '16.2.7';
        $this->author                 = 'GetResponse';
        $this->need_instance          = 0;
        $this->module_key             = '7e6dc54b34af57062a5e822bd9b8d5ba';
        $this->ps_versions_compliancy = array('min' => '1.5.6.2', 'max' => _PS_VERSION_);
        $this->displayName            = $this->l('GetResponse');

        $this->description            = $this->l('
            Add your Prestashop contacts to GetResponse or manage them via automation rules.
            Automatically follow-up new subscriptions with engaging email marketing campaigns
            ');
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

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCss($this->_path . 'views/css/tab.css');
    }

    /******************************************************************/
    /** Install Methods ***********************************************/
    /******************************************************************/

    public function installTab()
    {
        new TabCore();
        $tab             = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'Getresponse';
        $tab->name       = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'GetResponse';
        }

        if (substr(_PS_VERSION_, 0, 3) === '1.6') {
            $tab->id_parent = 0;
        } else {
            $tab->id_parent = (int) Tab::getIdFromClassName('AdminAdmin');
        }
        $tab->module = $this->name;

        $tab->add();

        $this->createSubTabs($tab->id);

        return true;
    }

    /**
     * @param int $tabId
     * @return bool
     */
    public function createSubTabs($tabId)
    {
        $langs = Language::getLanguages();
        $tabvalue = array(
            array(
                'class_name' => 'AdminGetresponseAccount',
                'name' => 'GetResponse Account',
            ),
            array(
                'class_name' => 'AdminGetresponseExport',
                'name' => 'Export Customer Data',
            ),
            array(
                'class_name' => 'AdminGetresponseSubscribeRegistration',
                'name' => 'Subscribe via Registration',
            ),
            array(
                'class_name' => 'AdminGetresponseSubscribeForm',
                'name' => 'Subscribe via Forms',
            ),
            array(
                'class_name' => 'AdminGetresponseContactList',
                'name' => 'Contact List Rules',
            ),
            array(
                'class_name' => 'AdminGetresponseWebTracking',
                'name' => 'Web Event Tracking',
            ),
            array(
                'class_name' => 'AdminGetresponseEcommerce',
                'name' => 'GetResponse Ecommerce',
            ),
        );
        foreach ($tabvalue as $tab) {
            $newtab = new Tab();
            $newtab->class_name = $tab['class_name'];
            $newtab->id_parent = $tabId;
            $newtab->module = $this->name;
            $newtab->position = 0;
            foreach ($langs as $l) {
                $newtab->name[$l['id_lang']] = $this->l($tab['name']);
            }
            $newtab->add();
        }
        return true;
    }

    /**
     * @return bool
     */
    public function install()
    {
        if (!parent::install() ||!$this->installTab()) {
            return false;
        }

        foreach ($this->usedHooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
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
        $classes = array(
            'AdminGetresponseExport',
            'AdminGetresponseSubscribeRegistration',
            'AdminGetresponseSubscribeForm',
            'AdminGetresponseContactList',
            'AdminGetresponseWebTracking',
            'AdminGetresponseEcommerce',
            'AdminGetresponseAccount',
            'AdminGetresponse',
            'Getresponse'
        );

        $result = true;
        foreach ($classes as $class) {
            $idTab = (int) Tab::getIdFromClassName($class);
            if (false === $idTab) {
                return false;
            }
            $tab = new Tab($idTab);
            $result = $tab->delete() && $result;
        }

        return $result;
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminGetresponseAccount'));
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        if (!parent::uninstall() ||!$this->uninstallTab()) {
            return false;
        }

        foreach ($this->usedHooks as $hook) {
            if (!$this->unregisterHook($hook)) {
                return false;
            }
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
     * @throws GrConfigurationNotFoundException
     */
    private function getApi()
    {
        if (empty($this->api)) {
            $settings = $this->getSettings();
            $this->api = new GrApi($settings['api_key'], $settings['account_type'], $settings['crypto']);
        }

        return $this->api;
    }

    /**
     * @return array
     * @throws GrConfigurationNotFoundException
     */
    private function getSettings()
    {
        if (empty($this->settings)) {
            $this->settings = $this->db->getSettings();
        }

        if (empty($this->settings['api_key'])) {
            throw new GrConfigurationNotFoundException();
        }

        return $this->settings;
    }

    /**
     * @return bool
     */
    public function isPluginEnabled()
    {
        try {
            $this->getSettings();
        } catch (GrConfigurationNotFoundException $e) {
            return false;
        }
        return true;
    }

    /******************************************************************/
    /** Hook Methods **************************************************/
    /******************************************************************/

    /**
     * @param array $params
     */
    public function hookCart($params)
    {
        $grIdShop = $this->db->getGetResponseShopId();
        if (empty($grIdShop)) {
            return; // E-commerce is disabled
        }

        /** @var CartCore $cart */
        $cart = $params['cart'];
        if (empty($cart) || 0 === (int)$cart->id_customer) {
            return;
        }

        $customer = new Customer($cart->id_customer);
        $settings = $this->db->getSettings();
        $ecommerce = new GrEcommerce($this->db);
        $idSubscriber = $ecommerce->getSubscriberId($customer->email, $settings['campaign_id']);

        if (empty($idSubscriber)) {
            return;
        }

        $products = $cart->getProducts(true);
        $md5 = md5(json_encode($products));

        // Cart didn't change
        if ($this->db->getGetResponseCartMD5($cart->id) === $md5) {
            return;
        }

        $grIdCart = $this->db->getGetResponseCartId($cart->id);

        if (count($products) === 0) {
            $ecommerce->removeCart($cart->id, $grIdCart, $grIdShop);
        } else {
            $ecommerce->sendCartDataToGR($cart, $grIdShop, $grIdCart, $idSubscriber);
        }

        $this->db->updateGetResponseCartMD5($cart->id, $md5);
    }

    /**
     * @param array $params
     */
    public function hookNewOrder($params)
    {
        if ($this->isPluginEnabled()) {
            $this->addSubscriberForOrder($params);
            $this->convertCartToOrder($params);
        }
    }

    /**
     * @param array $params
     */
    public function hookHookOrderConfirmation($params)
    {
        $this->convertCartToOrder($params);
    }

    /**
     * @param array $params
     */
    public function hookPostUpdateOrderStatus($params)
    {
        $grIdShop = $this->db->getGetResponseShopId();
        if (empty($grIdShop)) {
            return; // E-commerce is disabled
        }

        if (isset($params['id_order']) && !empty($params['id_order'])) {
            $params['order'] = new Order($params['id_order']);
            $this->convertCartToOrder($params);
        }
    }

    /**
     * @param array $params
     */
    private function convertCartToOrder($params)
    {
        /** @var OrderCore $order */
        $order = $params['order'];
        $grIdShop = $this->db->getGetResponseShopId();

        if (empty($grIdShop) || empty($order) || 0 === (int)$order->id_customer) {
            return;
        }

        /** @var CustomerCore $customer */
        $customer = new Customer($order->id_customer);
        $settings = $this->db->getSettings();
        $ecommerce = new GrEcommerce($this->db);
        $grIdContact = $ecommerce->getSubscriberId($customer->email, $settings['campaign_id'], true);

        if (empty($grIdContact)) {
            return;
        }

        $idOrder = (isset($order->id_order) && !empty($order->id_order)) ? $order->id_order : $order->id;
        $grOrder = $ecommerce->createOrderObject($params, $grIdContact, $grIdShop);
        $ecommerce->sendOrderDataToGR($grIdShop, $grOrder, $idOrder);
    }

    /**
     * @param array $params
     */
    public function hookCreateAccount($params)
    {
        if ($this->isPluginEnabled()) {
            $this->createSubscriber($params);
        }
    }

    /**
     * @param array $params
     */
    public function createSubscriber($params)
    {
        $settings = $this->getSettings();
        $api = new GrApi($settings['api_key'], $settings['account_type'], $settings['crypto']);

        if (isset($settings['active_subscription'])
            && $settings['active_subscription'] == 'yes'
            && !empty($settings['campaign_id'])
        ) {
            if (isset($params['newNewsletterContact'])) {
                $prefix = 'newNewsletterContact';
            } else {
                $prefix  = 'newCustomer';
            }

            $customs = $api->mapCustoms((array)$params[$prefix], null, $this->db->getCustoms(), 'create');

            if (isset($params[$prefix]->newsletter) && $params[$prefix]->newsletter == 1) {
                $api->addContact(
                    $settings['campaign_id'],
                    $params[$prefix]->firstname,
                    $params[$prefix]->lastname,
                    $params[$prefix]->email,
                    $settings['cycle_day'],
                    $customs
                );

                $ecommerce = new GrEcommerce($this->db);
                $ecommerce->getSubscriberId($params[$prefix]->email, $settings['campaign_id'], true);
            }
        }
    }

    /**
     * @param array $params
     *
     * @throws Exception
     */
    public function addSubscriberForOrder($params)
    {
        $customerPostData = $params['customer'];

        //update_contact
        $contact = $this->db->getContactByEmail($customerPostData->email);
        $customs = $this->getApi()->mapCustoms((array) $contact, $_POST, $this->db->getCustoms(), 'order');

        // automation
        if (!empty($params['order']->product_list)) {
            $categories = array();
            foreach ($params['order']->product_list as $products) {
                $tempCategories = Product::getProductCategories($products['id_product']);
                foreach ($tempCategories as $tmp) {
                    $categories[$tmp] = $tmp;
                }
            }

            $automations = $this->db->getAutomationSettings(true);

            if (!empty($automations)) {

                $automationRulesApplied = false;

                foreach ($automations as $automation) {

                    if (in_array($automation['category_id'], $categories)) {
                        // do automation
                        if ($automation['action'] == 'move') {

                            $this->getApi()->moveContactToGr(
                                $automation['campaign_id'],
                                $customerPostData->firstname,
                                $customerPostData->lastname,
                                $customerPostData->email,
                                $customs,
                                $automation['cycle_day']
                            );

                        } elseif ($automation['action'] == 'copy') {

                            $this->getApi()->addContact(
                                $automation['campaign_id'],
                                $customerPostData->firstname,
                                $customerPostData->lastname,
                                $customerPostData->email,
                                $automation['cycle_day'],
                                $customs
                            );
                            
                        }
                        $automationRulesApplied = true;
                    }
                }

                if (!$automationRulesApplied) {
                    $this->addContact($customerPostData, $customs);
                }
                return; //return so we do not hit standard case
            }
        }

        // standard case
        $this->addContact($customerPostData, $customs);
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
        $settings = $this->db->getSettings();

        if (isset($settings['active_tracking']) && $settings['active_tracking'] == 'yes') {
            $this->smarty->assign(array('gr_tracking_snippet' => $settings['tracking_snippet']));
            return $this->display(__FILE__, 'views/templates/admin/common/tracking_snippet.tpl');
        }

        return '';
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
        $email = false;
        $settings = $this->db->getSettings();

        if (Tools::isSubmit('submitNewsletter')
            && '0' == Tools::getValue('action')
            && Validate::isEmail(Tools::getValue('email'))
            && isset($settings['active_newsletter_subscription'])
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

        if (isset($this->context->customer) && !empty($this->context->customer->email) &&
            isset($settings['active_tracking']) && $settings['active_tracking'] == 'yes'
        ) {
            $email = $this->context->customer->email;
        }

        return $this->displayWebForm('footer') . $this->displayMailTracker($email);
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
     * @return mixed
     */
    private function displayWebForm($position)
    {
        if (!empty($position)) {
            $webformSettings = $this->db->getWebformSettings();

            if (!empty($webformSettings) && $webformSettings['active_subscription'] == 'yes'
                && $webformSettings['sidebar'] == $position
            ) {
                $setStyle = null;
                if (!empty($webformSettings['style']) && $webformSettings['style'] == 'prestashop') {
                    $setStyle = '&css=1';
                }
                $this->smarty->assign(array(
                    'webform_url' => $webformSettings['url'],
                    'style' => $setStyle,
                    'position' => $position
                ));
                return $this->display(__FILE__, 'views/templates/admin/common/webform.tpl');
            }
        }

        return '';
    }

    /**
     * @param string $email
     * @return mixed
     */
    private function displayMailTracker($email)
    {
        if (!empty($email)) {
            $this->smarty->assign(array('tracking_email' => $email));
            return $this->display(__FILE__, 'views/templates/admin/common/tracking_mail.tpl');
        }

        return '';
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
