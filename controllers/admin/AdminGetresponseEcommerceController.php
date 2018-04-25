<?php
require_once 'AdminGetresponseController.php';

/**
 * Class AdminGetresponseEcommerceController
 *
 * @author Getresponse <grintegrations@getresponse.com>
 * @copyright GetResponse
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AdminGetresponseEcommerceController extends AdminGetresponseController
{
    private $name = 'GREcommerce';

    private $api;

    public function __construct()
    {
        parent::__construct();

        $this->addJquery();
        $this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/gr-ecommerce.js');

        $settings = $this->db->getSettings();
        $this->api = new GrApi($settings['api_key'], $settings['account_type'], $settings['crypto']);

        if (Tools::isSubmit('delete' . $this->name)) {
            $this->api->deleteShop(Tools::getValue('shopId'));
            $this->confirmations[] = $this->l('Ecommerce settings saved');
        }

        if (Tools::isSubmit('submit' . $this->name) && Tools::getValue('ecommerce') !== false) {
            $grIdShop = Tools::getValue('shop');
            $isActive = Tools::getValue('ecommerce') == 1 ? 'yes' : 'no';

            if ($isActive == 'yes' && empty($grIdShop)) {
                $this->errors[] = $this->l('You need to select shop');
                return;
            }

            if ($settings['active_subscription'] != 'yes') {
                $this->errors[] = $this->l(
                    'You need to enable adding contacts during registrations to enable ecommerce'
                );
                return;
            }

            $this->db->updateEcommerceSubscription($isActive);

            if ($isActive == 'yes') {
                $this->db->updateEcommerceShopId($grIdShop);
            }

            $this->confirmations[] = $this->l('Ecommerce settings saved');
        }
    }

    public function initPageHeaderToolbar()
    {
        if (!in_array($this->display, array('edit', 'add'))) {
            $this->page_header_toolbar_btn['new_shop'] = array(
                'href' => self::$currentIndex.'&action=add&token='.$this->getToken(),
                'desc' => $this->l('Add new shop', null, null, false),
                'icon' => 'process-icon-new'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function initProcess()
    {
        if (Tools::getValue('action') == 'add') {
            $this->display = 'add';

            if (Tools::isSubmit('submit'.$this->name) && Tools::getValue('form_name') == 'add_store') {
                $shopName = Tools::getValue('shop_name');

                if (empty($shopName)) {
                    $this->errors[] = $this->l('Store name can not be empty');
                } else {
                    $this->confirmations[] = $this->l('Store added');
                    $this->api->createShop(
                        $shopName,
                        $this->context->language->iso_code,
                        $this->context->currency->iso_code
                    );

                    $this->display = 'list';
                }
            }
        }
    }

    /**
     * Get Admin Token
     * @return string
     */
    public function getToken()
    {
        return Tools::getAdminTokenLite('AdminGetresponseEcommerce');
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->l('GetResponse');
        $this->toolbar_title[] = $this->l('GetResponse Ecommerce');
    }

    /**
     * @return mixed
     */
    public function renderList()
    {
        return $this->generateForm() . $this->generateList();
    }

    /**
     * @return string
     */
    private function generateList()
    {
        /** @var HelperListCore $helper */
        $helper = new HelperList();
        $helper->no_link = true;
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'shopId';
        $helper->actions = array('delete');
        $helper->title = $this->l('Stores');
        $helper->table = $this->name;
        $helper->token = $this->getToken();
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $fieldsList = array(
            'shopId' => array('title' => $this->l('ID'), 'type' => 'text'),
            'name' => array('title' => $this->l('Shop name'), 'type' => 'text'),
        );

        return $helper->generateList(
            json_decode(json_encode($this->api->getShops()), true),
            $fieldsList
        );
    }

    /**
     * @return string
     */
    private function generateForm()
    {
        $settings = $this->db->getEcommerceSettings();
        $shops = json_decode(json_encode($this->api->getShops()), true);

        if (!is_array($shops)) {
            $shops = array();
        }
        array_unshift($shops, array('shopId' => '', 'name' => $this->l('Select a shop')));

        /** @var HelperFormCore $helper */
        $helper = new HelperForm();
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l((empty($settings) ? 'Enable ' : '') . 'GetResponse Ecommerce')
                ),
                'description' =>
                    $this->l(
                        'GetResponse helps you track and collect ecommerce data. 
                        You can stay informed about customers’ behaviour and spending habits.'
                    ) . '<br>' .
                    $this->l(
                        'Use this data to create marketing automation workflows that react to 
                        purchases, abandoned carts, or the amounts of money spent.'
                    ) . '<br>' .
                    $this->l(
                        'Make sure to <u>enable adding contacts during registration</u> to 
                        start sending ecommerce data to GetResponse.',
                        false,
                        false,
                        false
                    ),
                'input' => array(
                    array(
                        'label' => $this->l('Send ecommerce data to GetResponse'),
                        'name' => 'ecommerce',
                        'type' => 'switch',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'ecommerce_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'ecommerce_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Shop'),
                        'class' => 'gr-select',
                        'name'    => 'shop',
                        'required' => true,
                        'options' => array(
                            'query' => $shops,
                            'id' => 'shopId',
                            'name' => 'name'
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'EcommerceConfiguration'
                )
            )
        );

        $helper->submit_action = 'submit' . $this->name;
        $helper->token = $this->getToken();
        $helper->title = $this->l('Enable GetResponse Ecommerce');
        $helper->fields_value = array('ecommerce' => 0, 'shop' => '');

        if (!empty($settings)) {
            $helper->fields_value['ecommerce'] = 1;
            $helper->fields_value['shop'] = $settings['gr_id_shop'];
        }

        return $helper->generateForm(array($fieldsForm));
    }

    /**
     * @return mixed
     */
    public function renderForm()
    {
        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Add new store'),
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Store name'),
                        'required' => true,
                        'name' => 'shop_name',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'form_name'
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'back_url'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'NewAutomationConfiguration'
                ),
                'reset' => array(
                    'title' => $this->l('Cancel'),
                    'icon' => 'process-icon-cancel'
                ),
                'show_cancel_button' => true
            )
        );

        /** @var HelperFormCore $helper */
        $helper = new HelperForm();

        $helper->fields_value = array(
            'shop_name' => '',
            'form_name' => 'add_store',
            'back_url' => self::$currentIndex . '&token=' . $this->getToken(),
        );
        $helper->currentIndex = AdminController::$currentIndex . '&action=add';
        $helper->submit_action = 'submit' . $this->name;
        $helper->token = $this->getToken();

        return $helper->generateForm(array($fieldsForm));
    }
}
