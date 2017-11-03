<?php
/**
 * Class AdminGetresponseController
 * @static $currentIndex
 * @property $display
 * @property $confirmations
 * @property $errors
 * @property $context
 * @property $toolbar_title
 * @property $module
 * @property $page_header_toolbar_btn
 * @property $bootstrap
 * @property $meta_title
 * @property $identifier
 * @property $show_form_cancel_button
 * @method string l() l($string, $class = null, $addslashes = false, $htmlentities = true)
 * @method void addJs() addJs($path)
 * @method void addJquery()
 * @method null initContent()
 * @author Getresponse <grintegrations@getresponse.com>
 * @copyright GetResponse
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AdminGetresponseController extends ModuleAdminController
{
    /** @var DbConnection */
    public $db;

    public function __construct()
    {
        parent::__construct();

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }

        $this->bootstrap  = true;
        $this->meta_title = $this->l('GetResponse Integration');

        $this->identifier  = 'api_key';

        $this->context->smarty->assign(array(
            'gr_tpl_path' => _PS_MODULE_DIR_ . 'getresponse/views/templates/admin/',
            'action_url' => $this->context->link->getAdminLink('AdminGetresponseAccount'),
            'base_url', __PS_BASE_URI__
        ));

        $this->db = new DbConnection(Db::getInstance(), GrShop::getUserShopId());


        $settings = $this->db->getSettings();
        $isConnected = !empty($settings['api_key']) ? true : false;

        if ('AdminGetresponseAccount' !== Tools::getValue('controller') && false === $isConnected) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminGetresponseAccount'));
        }
    }

    /**
     * Set Css & js
     * @param bool $isNewTheme
     */
    public function setMedia($isNewTheme = false)
    {
        $this->context->controller->addJquery();
        $this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/gr-account.js');

        parent::setMedia($isNewTheme);
    }

    /**
     * Toolbar title
     */
    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->l('Administration');
        $this->toolbar_title[] = $this->l('Settings');
    }

    /**
     * Page Header Toolbar
     */
    public function initPageHeaderToolbar()
    {
        if (Tools::getValue('edit_id') != 'new') {
            parent::initPageHeaderToolbar();
        }

        unset($this->page_header_toolbar_btn['back']);
    }

    /**
     * render main view
     * @return mixed
     */
    public function renderView()
    {
        $settings = $this->db->getSettings();
        $isConnected = !empty($settings['api_key']) ? true : false;

        $this->context->smarty->assign(array(
            'is_connected' => $isConnected,
            'gr_base_url' => $this->context->shop->getBaseURL(true),
            'active_tracking' => $settings['active_tracking']
        ));

        switch (Tools::getValue('action')) {
            case 'api':
                $this->apiView();
                break;
            default:
                break;
        }

        return parent::renderView();
    }

    /**
     * API key settings
     */
    public function apiView()
    {
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
     * Process Refresh Data
     * @return mixed
     */
    public function processRefreshData()
    {
        return $this->module->refreshDatas();
    }

    /**
     * Get Admin Token
     * @return bool|string
     */
    public function getToken()
    {
        return Tools::getAdminTokenLite('AdminGetresponse');
    }

    /**
     * Validate custom fields
     *
     * @param $customs
     *
     * @return array
     */
    public function validateCustoms($customs)
    {
        $errors = array();
        if (!is_array($customs)) {
            return array();
        }
        foreach ($customs as $custom) {
            if (!empty($custom) && preg_match('/^[\w\-]+$/', $custom) == false) {
                $errors[] = 'Error - "' . $custom . '" ' . $this->l('contains invalid characters');
            }
        }
        return $errors;
    }

    /**
     * @param string $custom
     * @return string
     */
    public function validateCustom($custom)
    {
        if (!empty($custom) && preg_match('/^[\w\-]+$/', $custom) == false) {
            return $this->l('Custom field contains invalid characters!');
        }
    }

    /**
     * Add camapaign to GetResponse via API
     * @param string $campaignName
     * @param string $fromField
     * @param string $replyToField
     * @param string $confirmationSubject
     * @param string $confirmationBody
     * @throws GrApiException
     */
    public function addCampaignToGR(
        $campaignName,
        $fromField,
        $replyToField,
        $confirmationSubject,
        $confirmationBody
    ) {
        $settings = $this->db->getSettings();
        // required params
        if (empty($settings['api_key'])) {
            return;
        }

        $api = $this->getGrAPI();

        try {
            $params = array(
                'name'                 => $campaignName,
                'confirmation'         => array(
                    'fromField' => array('fromFieldId'  => $fromField),
                    'replyTo'   => array('fromFieldId'  => $replyToField),
                    'subscriptionConfirmationBodyId'    => $confirmationBody,
                    'subscriptionConfirmationSubjectId' => $confirmationSubject
                    ),
                'languageCode'         => 'EN'
            );

            $campaign = $api->createCampaign($params);

            if (isset($campaign->codeDescription)) {
                throw new GrApiException($campaign->codeDescription, $campaign->code);
            }
        } catch (Exception $e) {
            throw GrApiException::createForCampaignNotAddedException($e);
        }
    }

    /**
     * @param string $apiKey
     *
     * @return string
     */
    private function hideApiKey($apiKey)
    {
        if (Tools::strlen($apiKey) > 0) {
            return str_repeat("*", Tools::strlen($apiKey) - 6) . Tools::substr($apiKey, -6);
        }

        return $apiKey;
    }

    public function redirectIfNotAuthorized()
    {
        $settings = $this->db->getSettings();

        if (empty($settings['api_key'])) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminGetresponse'));
        }
    }

    public function getGrAPI()
    {
        $settings = $this->db->getSettings();
        return new GrApi($settings['api_key'], $settings['account_type'], $settings['crypto']);
    }

    /**
     * @param array $autoresponders
     * @return array
     */
    public function getCampaignDays($autoresponders)
    {
        $campaignDays = array();
        if (!empty($autoresponders)) {
            foreach ($autoresponders as $autoresponder) {
                if ($autoresponder->triggerSettings->dayOfCycle == null) {
                    continue;
                }

                $cycleDay = $autoresponder->triggerSettings->dayOfCycle;
                $campaignId = $autoresponder->campaignId;

                $campaignDays[$campaignId][$cycleDay] =
                    array('day' => $autoresponder->triggerSettings->dayOfCycle,
                          'name' => $autoresponder->subject,
                          'campaign_id' => $autoresponder->campaignId,
                          'status' => $autoresponder->status,
                          'full_name' => '(' . $this->l('Day') . ': ' .
                              $cycleDay . ') ' . $autoresponder->name .
                              ' (' . $this->l('Subject') . ': ' . $autoresponder->subject . ')'
                    );
            }
        }
        return $campaignDays;
    }

    public function renderAddCampaignForm($fromFields, $replyTo, $confirmSub, $confirmBody)
    {
        $fieldsForm = array(
            'legend' => array(
                'title' => $this->l('Add new contact list'),
                'icon' => 'icon-gears'
            ),
            'input' => array(
                'contact_list' => array(
                    'label' => $this->l('List name'),
                    'name' => 'campaign_name',
                    'hint' => $this->l('You need to enter a name that\'s at least 3 characters long'),
                    'type' => 'text',
                    'required' => true
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('From field'),
                    'name' => 'from_field',
                    'required' => true,
                    'options' => array(
                        'query' => $fromFields,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Reply-to'),
                    'name' => 'replyto',
                    'required' => true,
                    'options' => array(
                        'query' => $replyTo,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Confirmation subject'),
                    'name' => 'subject',
                    'required' => true,
                    'options' => array(
                        'query' => $confirmSub,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Confirmation body'),
                    'name' => 'body',
                    'required' => true,
                    'desc' =>
                        $this->l(
                            'The confirmation message body and subject depend on System >> 
                            Configuration >> General >> Locale Options.'
                        ) .
                        '<br>' .
                        $this->l(
                            'By default all lists you create in Prestashop have double opt-in enabled.
                            You can change this later in your list settings.'
                        ),
                    'options' => array(
                        'query' => $confirmBody,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'addCampaignForm',
                'icon' => 'process-icon-save'
            )
        );

        /** @var HelperFormCore $helper */
        $helper = new HelperForm();
        $helper->currentIndex = AdminController::$currentIndex . '&token=' . $this->getToken();
        $helper->fields_value = array(
            'campaign_name' => false,
            'from_field' => false,
            'replyto' => false,
            'subject' => false,
            'body' => false,
        );

        return $helper->generateForm(array(array('form' => $fieldsForm)));
    }

    /**
     * Converts campaigns to display array
     *
     * @param $campaigns
     *
     * @return array
     */
    public function convertCampaignsToDisplayArray($campaigns)
    {
        $options = array(
            array(
                'id_option' => 0,
                'name' => $this->l('Select a list')
            )
        );

        foreach ($campaigns as $campaign) {
            $options[] = array(
                'id_option' => $campaign['id'],
                'name' => $campaign['name']
            );
        }

        return $options;
    }

    /**
     * Saves customs
     */
    public function saveCustom()
    {
        $custom = array(
            'id' => Tools::getValue('id'),
            'value' => Tools::getValue('customer_detail'),
            'name' => Tools::getValue('gr_custom'),
            'active' => Tools::getValue('mapping_on') == 1 ? 'yes' : 'no'
        );

        if (Tools::getValue('default') == 1) {
            $this->errors[] = $this->l('Default mappings cannot be changed!');
            return;
        }

        $error = $this->validateCustom($custom['name']);

        if (empty($error)) {
            $this->db->updateCustom($custom);
            $this->confirmations[] = $this->l('Custom sucessfuly edited');
        } else {
            $this->erors[] = $this->l($error);
        }
    }

    /**
     * @param string $name
     * @param array $list
     * @return array
     */
    public function prependOptionList($name, $list)
    {
        return array_merge(array(array('id_option' => '', 'name' => $this->l($name))), $list);
    }

    /**
     * Saves campaign
     */
    public function saveCampaign()
    {
        $name = Tools::getValue('campaign_name');
        $from = Tools::getValue('from_field');
        $to = Tools::getValue('replyto');
        $confirmSubject = Tools::getValue('subject');
        $confirmBody = Tools::getValue('body');

        if (strlen($name) < 4) {
            $this->errors[] = $this->l('The "list name" field is invalid');
        }
        if (strlen($from) < 4) {
            $this->errors[] = $this->l('The "from" field is required');
        }
        if (strlen($to) < 4) {
            $this->errors[] = $this->l('The "reply-to" field is required');
        }
        if (strlen($confirmSubject) < 4) {
            $this->errors[] = $this->l('The "confirmation subject" field is required');
        }
        if (strlen($confirmBody) < 4) {
            $this->errors[] = $this->l('The "confirmation body" field is required');
        }

        if (!empty($this->errors)) {
            $_GET['action'] = 'addCampaign';
            return;
        }

        try {
            $this->addCampaignToGR($name, $from, $to, $confirmSubject, $confirmBody);
            $this->confirmations[] = $this->l('List created');
        } catch (GrApiException $e) {
            $this->errors[] = $this->l('Contact list could not be added! (' . $e->getMessage() . ')');
        }
    }

    /**
     * Renders custom list
     * @return mixed
     */
    public function renderCustomList()
    {
        $fieldsList = array(
            'customer_detail' => array(
                'title' => $this->l('Customer detail'),
                'type' => 'text',
            ),
            'gr_custom' => array(
                'title' => $this->l('Custom fields in GetResponse'),
                'type' => 'text',
            ),
            'on' => array(
                'title' => $this->l('Active'),
                'type' => 'bool',
                'icon' => array(
                    0 => 'disabled.gif',
                    1 => 'enabled.gif',
                    'default' => 'disabled.gif'
                ),
                'align' => 'center'
            )
        );

        /** @var HelperListCore $helper */
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id';
        $helper->actions = array('edit');
        $helper->show_toolbar = true;

        $helper->title = $this->l('Contacts info');
        $helper->table = $this->name;
        $helper->token = $this->getToken();
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        return $helper->generateList($this->getCustomList(), $fieldsList);
    }

    /**
     * Returns custom list
     * @return array
     */
    public function getCustomList()
    {
        $customs = $this->db->getCustoms();
        $result = array();
        foreach ($customs as $custom) {
            $result[] = array(
                'id' => $custom['id_custom'],
                'customer_detail' => $custom['custom_field'],
                'gr_custom' => $custom['custom_name'],
                'default' => $custom['default'] == 'yes' ? 1 : 0,
                'on' => $custom['active_custom'] == 'yes' ? 1 : 0
            );
        }

        return $result;
    }
}
