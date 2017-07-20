<?php

/**
 * Class AdminGetresponseController
 *
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

    public function renderApiForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Connect your site and GetResponse'),
                'icon' => 'icon-gears'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('API key'),
                    'name' => 'api_key',
                    'desc' => $this->l('Your API key is part of the settings of your GetResponse account. Log in to GetResponse and go to <strong>My profile > Integration & API > API</strong> to find the key', false, false, false),
                    'empty_message' => $this->l('You need to enter API key. This field can\'t be empty.'),
                    'required' => true
                ),
                array(
                    'type'      => 'switch',
                    'label'     => $this->l('Enterprise package'),
                    'name'      => 'is_enterprise',
                    'required'  => false,
                    'class'     => 't',
                    'is_bool'   => true,
                    'values'    => array(
                      array(
                          'id'    => 'active_on',
                          'value' => 1,
                          'label' => $this->l('Enabled')
                      ),
                      array(
                          'id'    => 'active_off',
                          'value' => 0,
                          'label' => $this->l('Disabled')
                      )
                    ),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Account type'),
                    'name' => 'account_type',
                    'required' => false,
                    'values' =>  array(
                        array(
                            'id' => 'account_pl',
                            'value' => '360pl',
                            'label' => $this->l('GetResponse 360 PL')
                        ),
                        array(
                            'id' => 'account_en',
                            'value' => '360en',
                            'label' => $this->l('GetResponse 360 COM')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Your domain'),
                    'name' => 'domain',
                    'required' => false,
                    'id' => 'domain',
                    'desc' => $this->l('Enter your domain without protocol (https://) eg: "example.com"'),
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'action',
                    'values' => 'api',
                    'default' => 'api'
                )
            ),
            'submit' => array(
                'title' => $this->l('Connect'),
                'name' => 'connectToGetResponse',
                'icon' => 'icon-getresponse icon-link'
            )
        );

        //hack for setting default value of form input
        if (empty($_POST['action'])) {
            $_POST['action'] = 'api';
        }

        return parent::renderForm();
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

    public function validateCustom($custom)
    {
        if (!empty($custom) && preg_match('/^[\w\-]+$/', $custom) == false) {
            return $this->l('Custom field contains invalid characters!');
        }
    }

    /**
     * Add camapaign to GetResponse via API
     *
     * @param $campaign_name
     * @param $from_field
     * @param $reply_to_field
     * @param $confirmation_subject
     * @param $confirmation_body
     * @throws GrApiException
     */
    public function addCampaignToGR(
        $campaign_name,
        $from_field,
        $reply_to_field,
        $confirmation_subject,
        $confirmation_body
    ) {
        $settings = $this->db->getSettings();
        // required params
        if (empty($settings['api_key'])) {
            return;
        }

        $api = $this->getGrAPI();

        try {
            $params = array(
                'name'                 => $campaign_name,
                'confirmation'         => array(
                    'fromField' => array('fromFieldId'  => $from_field),
                    'replyTo'   => array('fromFieldId'  => $reply_to_field),
                    'subscriptionConfirmationBodyId'    => $confirmation_body,
                    'subscriptionConfirmationSubjectId' => $confirmation_subject
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
     * @param string $message
     */
    public function addSuccessMessage($message)
    {
        $this->context->smarty->assign(array('flash_message' => array(
            'message' => $this->l($message),
            'status' => 'success'
        )));
    }

    /**
     * @param string $message
     */
    public function addErrorMessage($message)
    {
        $this->context->smarty->assign(array('flash_message' => array(
            'message' => $this->l($message),
            'status' => 'danger'
        )));
    }

    /**
     * @param string $message
     */
    public function addWarningMessage($message)
    {
        $this->context->smarty->assign(array('flash_message' => array(
            'message' => $this->l($message),
            'status' => 'warning'
        )));
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

    public function getCampaignDays($autoresponders)
    {
        $campaignDays = array();
        if ( !empty($autoresponders) && is_object($autoresponders)) {
            foreach ($autoresponders as $autoresponder) {
                if ($autoresponder->triggerSettings->dayOfCycle == null) {
                    continue;
                }
                $campaignDays[$autoresponder->triggerSettings->subscribedCampaign->campaignId][$autoresponder->triggerSettings->dayOfCycle] =
                    array('day' => $autoresponder->triggerSettings->dayOfCycle,
                          'name' => $autoresponder->subject,
                          'status' => $autoresponder->status,
                          'full_name' => '(' . $this->l('Day') . ': ' . $autoresponder->triggerSettings->dayOfCycle . ') ' . $autoresponder->name . ' (' . $this->l('Subject') . ': ' . $autoresponder->subject . ')'
                    );
            }
        }
        return $campaignDays;
    }

    public function renderAddCampaignForm($fromFields, $replyTo, $confirmSub, $confirmBody)
    {
        $fields_form = array(
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
                    'desc' => $this->l('The confirmation message body and subject depend on System >> Configuration >> General >> Locale Options.') . '<br>' . $this->l('By default all lists you create in Prestashop have double opt-in enabled. You can change this later in your list settings.'),
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

        return $helper->generateForm(array(array('form' => $fields_form)));
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
        foreach ($campaigns as $campaign)
        {
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
            return;
        }

        try {
            $this->addCampaignToGR($name, $from, $to, $confirmSubject, $confirmBody);
            $this->confirmations[] = $this->l('List created');
        } catch (GrApiException $e) {
            $this->errors[] = $this->l('Contact list could not be added! (' . $e->getMessage() . ')');
        }
    }
}
