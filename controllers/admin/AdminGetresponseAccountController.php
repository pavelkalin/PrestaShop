<?php

require_once 'AdminGetresponseController.php';

/**
 * Class AdminGetresponseAccountController
 *
 * @author Getresponse <grintegrations@getresponse.com>
 * @copyright GetResponse
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AdminGetresponseAccountController extends AdminGetresponseController
{
    public function __construct()
    {
        parent::__construct();

        $this->meta_title = $this->l('GetResponse Integration');
        $this->identifier  = 'AdminGetresponseAccountController';

        $this->context->smarty->assign(array(
            'gr_tpl_path' => _PS_MODULE_DIR_ . 'getresponse/views/templates/admin/',
            'action_url' => $this->context->link->getAdminLink('AdminGetresponseAccount'),
            'base_url', __PS_BASE_URI__
        ));

        $this->db = new DbConnection(Db::getInstance(), GrShop::getUserShopId());
    }

    public function initContent() {
        $this->display = 'view';

        parent::initContent();
    }

    /**
     * Toolbar title
     */
    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->l('Administration');
        $this->toolbar_title[] = $this->l('GetResponse Account');
    }

    /**
     * Page Header Toolbar
     */
    public function initPageHeaderToolbar()
    {
        if (Tools::getValue('action') != 'automation' || Tools::getValue('edit_id') != 'new') {
            parent::initPageHeaderToolbar();
        }

        unset($this->page_header_toolbar_btn['back']);
    }

    /**
     * API key settings
     */
    public function apiView()
    {
        if (Tools::isSubmit('connectToGetResponse')) {
            $this->connectToGetResponse();
        } elseif (Tools::isSubmit('disconnectFromGetResponse')) {
            $this->disconnectFromGetResponse();
        }

        $settings = $this->db->getSettings();

        if (!empty($settings['api_key'])) {
            $api = new GrApi($settings['api_key'], $settings['account_type'], $settings['crypto']);
            $data = $api->getAccounts();

            $this->context->smarty->assign(array(
                'gr_acc_name' => $data->firstName . ' ' . $data->lastName,
                'gr_acc_email' => $data->email,
                'gr_acc_company' => $data->companyName,
                'gr_acc_phone' => $data->phone,
                'gr_acc_address' => $data->city . ' ' . $data->street . ' ' . $data->zipCode,
            ));
        }

        $this->context->smarty->assign(array(
            'api_key' => $this->hideApiKey($settings['api_key']),
            'is_connected' => !empty($settings['api_key']) ? true : false,
            'form' => $this->renderApiForm()
        ));
    }

    /**
     *
     * render main view
     * @return mixed
     */
    public function renderView()
    {
        $settings = $this->db->getSettings();
        $isConnected = !empty($settings['api_key']) ? true : false;

        $this->context->smarty->assign(array(
            'selected_tab' => 'api',
            'is_connected' => $isConnected,
            'active_tracking' => $settings['active_tracking']
        ));

        $this->apiView();
        return parent::renderView();
    }

    /**
     * Process Refresh Data
     * @return mixed
     */
    public function processRefreshData()
    {
        return $this->module->refreshDatas();
    }

    private function disconnectFromGetResponse()
    {
        $this->db->updateApiSettings(null, null, null);
        $this->confirmations[] = $this->l('GetResponse account disconnected');
    }

    private function connectToGetResponse()
    {
        $api_key = Tools::getValue('api_key');
        $is_enterprise = (bool) Tools::getValue('is_enterprise');
        $account_type = Tools::getValue('account_type');
        $domain = Tools::getValue('domain');

        $account_type = $is_enterprise ? $account_type : 'gr';

        if (false === $this->validateConnectionFormParams($api_key, $is_enterprise, $account_type, $domain)) {
            return;
        }

        $api = new GrApi($api_key, $account_type, $domain);

        try {
            if (true === $api->checkConnection()) {
                $this->db->updateApiSettings($api_key, $account_type, $domain);
                $this->confirmations[] = $this->l('GetResponse account connected');

                $this->db->updateTracking(
                    (false === $api->getFeatures()->feature_tracking) ? 'disabled': 'no', ''
                );
            } else {
                $msg = $account_type !== 'gr' ? 'The API key or domain seems incorrect.' : 'The API key seems incorrect.';
                $msg .= ' Please check if you typed or pasted it correctly. If you recently generated a new key, please make sure you\'re using the right one';
                $this->errors[] = $this->l($msg);
            }
        } catch (GrApiException $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * @param string $api_key
     * @param bool $is_enterprise
     * @param string $account_type
     * @param string $domain
     *
     * @return bool
     */
    private function validateConnectionFormParams($api_key, $is_enterprise, $account_type, $domain)
    {
        if (empty($api_key)) {
            $this->errors[] = $this->l('You need to enter API key. This field can\'t be empty.');
            return false;
        }

        if (false === $is_enterprise) {
            return true;
        }

        if (empty($account_type)) {
            $this->errors[] = $this->l('Invalid account type');
            return false;
        }

        if (empty($domain)) {
            $this->errors[] = $this->l('Domain field can not be empty');
            return false;
        }

        return true;
    }

    /**
     * Get Admin Token
     * @return bool|string
     */
    public function getToken()
    {
        return Tools::getAdminTokenLite('AdminGetresponseAccount');
    }

    /**
     * @param string $api_key
     *
     * @return string
     */
    private function hideApiKey($api_key)
    {
        if (Tools::strlen($api_key) > 0) {
            return str_repeat("*", Tools::strlen($api_key) - 6) . Tools::substr($api_key, -6);
        }

        return $api_key;
    }
}
