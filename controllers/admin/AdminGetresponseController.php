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
    private $db;

    public function __construct()
    {
        parent::__construct();

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }

        $this->bootstrap  = true;
        $this->display    = 'view';
        $this->meta_title = $this->l('GetResponse Integration');

        $this->identifier  = 'api_key';

        $this->context->smarty->assign(array(
            'gr_tpl_path' => _PS_MODULE_DIR_ . 'getresponse/views/templates/admin/',
            'action_url' => $this->context->link->getAdminLink('AdminGetresponse'),
            'base_url', __PS_BASE_URI__
        ));

        $this->db = new DbConnection(Db::getInstance(), GrShop::getUserShopId());
    }

    /**
     * Set Css & js
     * @param bool $isNewTheme
     */
    public function setMedia($isNewTheme = false)
    {
        $this->context->controller->addJquery();

        $this->addCSS(array(_MODULE_DIR_ . $this->module->name . '/views/css/normalize.css'));
        $this->addCSS(array(_MODULE_DIR_ . $this->module->name . '/views/css/grid.css'));
        $this->addCSS(array(_MODULE_DIR_ . $this->module->name . '/views/css/form.css'));
        $this->addCSS(array(_MODULE_DIR_ . $this->module->name . '/views/css/style.css'));

        $this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/app.src-verified.js');
        $this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/fullSelect.src-verified.async.js');
        $this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/lightbox.src-verified.async.js');
        $this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/growler.src-verified.async.js');
        $this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/getresponse-custom-field.src-verified.js');
        $this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/gr_main.js');

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
        if (Tools::getValue('action') != 'automation' || Tools::getValue('edit_id') != 'new') {
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
            'gr_base_url' => Tools::getHttpHost(true)
        ));

        if (false === $isConnected) {
            $this->apiView();
            return parent::renderView();
        }

        switch (Tools::getValue('action')) {
            case 'api':
                $this->apiView();
                break;
            case 'export_customers_show':
                $this->exportView();
                break;
            case 'export_customers_save':
                $this->performExport();
                break;
            case 'subscribe_via_registration_show':
                $this->subscribeViaRegistrationView();
                break;

            case 'subscribe_via_registration_ajax':
                $this->subscribeViaRegistrationAjax();
                break;

            case 'subscribe_via_registration_send':
                $this->performSubscribeViaRegistration();
                break;
            case 'subscribe_via_form':
                $this->subscribeViaFormView();
                break;

            case 'subscribe_via_form_ajax':
                $this->subscribeViaFormAjax();
                break;
            case 'subscribe_via_form_send':
                $this->performSubscribeViaForm();
                break;
            case 'automation':
                $this->automationView();
                break;
            default:
                $this->apiView();
        }

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

        $this->context->smarty->assign(array(
            'selected_tab' => 'api',
            'api_key' => $this->hideApiKey($settings['api_key']),
            'is_connected' => !empty($settings['api_key']) ? true : false
        ));
    }

    private function disconnectFromGetResponse()
    {
        $this->db->updateApiSettings(null, null, null);
        $this->addSuccessMessage('You have been disconnected.');
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
                $this->addSuccessMessage('You have been connected.');
            } else {
                $this->addErrorMessage($account_type !== 'gr' ? 'Wrong API key or domain' : 'Wrong API Key');
            }
        } catch (GrApiException $e) {
            $this->addErrorMessage($e->getMessage());
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
            $this->addErrorMessage('Api Key field can not be empty');
            return false;
        }

        if (false === $is_enterprise) {
            return true;
        }

        if (empty($account_type)) {
            $this->addErrorMessage('Invalid account type');
            return false;
        }

        if (empty($domain)) {
            $this->addErrorMessage('Domain field can not be empty');
            return false;
        }

        return true;
    }

    /**
     * Export customers
     */
    public function exportView()
    {
        $this->redirectIfNotAuthorized();

        $api = $this->getGrAPI();

        $this->context->smarty->assign(array(
            'selected_tab' => 'export_customers',
            'campaigns' => $api->getCampaigns(),
            'fromfields' => $api->getFromFields(),
            'subscriptionConfirmationsSubject' => $api->getSubscriptionConfirmationsSubject(),
            'subscriptionConfirmationsBody' => $api->getSubscriptionConfirmationsBody(),
            'cycle_days' => $api->getAutoResponders(),
            'custom_fields' => $this->db->getCustoms(),
            'token' => $this->getToken()
        ));
    }

    public function performExport()
    {
        $this->redirectIfNotAuthorized();
        $exportSubscribers = Tools::getValue('export_subscribers');
        if (empty($exportSubscribers)) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminGetresponse'));
        }

        $campaign = Tools::getValue('campaign');
        $ng = Tools::getValue('newsletter_guests');
        $cycle_day = Tools::getValue('cycle_day');
        $posted_customs = Tools::getValue('custom_field');
        $add_to_cycle = Tools::getValue('add_to_cycle');
        $cycle_day    = 'yes' === $add_to_cycle ? $cycle_day : null;

        if (empty($campaign[0]) || $campaign[0] == '0') {
            $this->addErrorMessage('No campaign selected');
            $this->exportView();
            return;
        }

        $errors = $this->validateCustoms($posted_customs);

        if (!empty($errors)) {
            $this->addErrorMessage(implode(',', $errors));
            $this->exportView();
            return;
        }

        $errorMessages = array();
        $api = $this->getGrAPI();

        // get contacts
        $contacts = $this->db->getContacts(!empty($ng) ? true : false);

        if (empty($contacts)) {
            $this->addErrorMessage('No contacts to export');
            $this->exportView();
            return;
        }

        foreach ($contacts as $contact) {
            $customs = $api->mapCustoms($contact, $_POST, $this->db->getCustoms(), 'export');

            if (!empty($customs['custom_error']) && $customs['custom_error'] == true) {
                $this->addErrorMessage('Incorrect field name: ' . $customs['custom_message']);
                return;
            }

            $r = $api->addContact(
                $campaign[0],
                $contact['firstname'],
                $contact['lastname'],
                $contact['email'],
                $cycle_day,
                $customs
            );

            if (isset($r->httpStatus) && $r->httpStatus >= 400) {
                $errorMessages[] = '[' . $r->code . '] ' . $r->message;
            }
        }

        if (0 == count($errorMessages)) {
            $this->addSuccessMessage('Export completed');
        } elseif (1 == count($errorMessages)) {
            $message = 'Export completed. One contact hasn\'t been exported due to error :';
            $this->addSuccessMessage($message . ' ' . $errorMessages[0]);
        } else {
            $message = 'contacts haven\'t been exported due to various reasons';
            $this->addSuccessMessage('Export completed. ' . count($errorMessages) . ' ' . $message);
        }

        $this->exportView();
    }

    public function subscribeViaRegistrationAjax()
    {
        header('Content-Type: application/json');

        // ajax - update subscription
        $subscription = Tools::getValue('subscription');

        if (!in_array($subscription, array('yes', 'no'))) {
            die(Tools::jsonEncode(array('error' => 'Incorrect subscription type.')));
        }

        $this->db->updateSettingsSubscription($subscription);
        die(Tools::jsonEncode(array('success' => 'Settings updated.')));
    }

    /**
     * Subscription via registration page
     */
    public function subscribeViaRegistrationView()
    {
        $this->redirectIfNotAuthorized();

        // ajax - update subscription
        $subscription = Tools::getValue('subscription');

        if ($subscription) {
            $this->db->updateSettingsSubscription($subscription);
        }

        $settings = $this->db->getSettings();
        $api = $this->getGrAPI();

        $this->context->smarty->assign(array(
            'selected_tab' => 'subscribe_via_registration',
            'fromfields' => $api->getFromFields(),
            'campaigns' => $api->getCampaigns(),
            'subscriptionConfirmationsSubject' => $api->getSubscriptionConfirmationsSubject(),
            'subscriptionConfirmationsBody' => $api->getSubscriptionConfirmationsBody(),
            'cycle_days' => $api->getAutoResponders(),
            'custom_fields' => $this->db->getCustoms(),
            'token' => $this->getToken(),
            'status' => $settings['active_subscription'],
            'selected_campaign' => $settings['campaign_id'],
            'update_address' => $settings['update_address'],
            'active_newsletter_subscription' => $settings['active_newsletter_subscription']
        ));
    }

    public function performSubscribeViaRegistration()
    {
        $this->redirectIfNotAuthorized();
        $subscribeViaRegister = Tools::getValue('subscribe_via_registration');
        if (empty($subscribeViaRegister)) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminGetresponse'));
        }

        $status         = Tools::getValue('status');
        $campaign       = Tools::getValue('campaign');
        $add_to_cycle   = Tools::getValue('add_to_cycle');
        $cycle_day      = Tools::getValue('cycle_day');
        $update_address = Tools::getValue('update_address');
        $newsletter     = Tools::getValue('newsletter');
        $posted_customs = Tools::getValue('custom_field');

        // check subscription settings
        if (!empty($campaign[0]) && $campaign[0] != '0' && !empty($status)) {
            $update_address = empty($update_address) ? 'no' : $update_address;
            $newsletter = empty($newsletter) ? 'no' : $newsletter;
            $cycle_day = 'yes' === $add_to_cycle ? $cycle_day : null;

            $errors = $this->validateCustoms($posted_customs);
            if (!empty($errors)) {
                $this->context->smarty->assign(implode(',', $errors));
            } else {
                $this->db->updateSettings($status, $campaign[0], $update_address, $cycle_day, $newsletter);
                if (!empty($posted_customs)) {
                    $this->db->updateCustomsWithPostedData($posted_customs);
                } else {
                    $this->db->disableCustoms();
                }

                $this->addSuccessMessage('Settings update successful');
            }
        } elseif (!empty($campaign[0]) && $campaign[0] == '0') {
            $this->addErrorMessage('No campaign selected');
        }

        $this->subscribeViaRegistrationView();
    }

    public function subscribeViaFormAjax()
    {
        header('Content-Type: application/json');

        // ajax - update subscription
        $subscription = Tools::getValue('subscription');

        if (!in_array($subscription, array('yes', 'no'))) {
            die(Tools::jsonEncode(array('error' => 'Incorrect subscription type.')));
        }

        $this->db->updateWebformSubscription($subscription);
        die(Tools::jsonEncode(array('success' => 'Settings updated.')));
    }


    /**
     * Subscription via webform
     */
    public function subscribeViaFormView()
    {
        $this->redirectIfNotAuthorized();

        $api = $this->getGrAPI();
        $this->context->smarty->assign(array('selected_tab' => 'subscribe_via_form'));

        $campaigns = $api->getCampaigns();

        if (!empty($campaigns)) {
            $campaign_id = array();
            foreach ($campaigns as $campaign) {
                $campaign_id[$campaign['id']] = $campaign['name'];
            }

            $this->context->smarty->assign(array('campaigns' => $campaign_id));
        }

        // get old webforms
        $webforms = $api->getWebForms();
        if (!empty($webforms)) {
            $this->context->smarty->assign(array('webforms' => $webforms));
        }

        // get new forms
        $forms = $api->getForms();
        if (!empty($forms)) {
            $this->context->smarty->assign(array('forms' => $forms));
        }

        $webformSettings = $this->db->getWebformSettings();
        if (!empty($webformSettings)) {
            $this->context->smarty->assign(array('webform_id' => $webformSettings['webform_id']));
            $this->context->smarty->assign(array('webform_sidebar' => $webformSettings['sidebar']));
            $this->context->smarty->assign(array('webform_style' => $webformSettings['style']));
            $this->context->smarty->assign(array('webform_status' => $webformSettings['active_subscription']));
        }
    }

    public function performSubscribeViaForm()
    {
        $this->redirectIfNotAuthorized();

        $subscribeViaForm = Tools::getValue('subscribe_via_form');
        if (empty($subscribeViaForm)) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminGetresponse'));
        }

        // check _POST
        $web_form_id      = Tools::getValue('webform_id');
        $web_form_sidebar = Tools::getValue('webform_sidebar');
        $web_form_style   = Tools::getValue('webform_style');
        $web_form_status  = Tools::getValue('webform_status');

        if (is_array($web_form_id) && empty($web_form_id[0])) {
            $this->addErrorMessage('You have to select a WebForm');
            return;
        }

        $api = $this->getGrAPI();

        $web_forms = array_merge($api->getWebForms(), $api->getForms());
        $merged_web_forms = array();

        foreach ($web_forms as $form) {
            $merged_web_forms[$form->webformId] = $form->scriptUrl;
        }

        // set web form info to DB
        $this->db->updateWebformSettings(
            $web_form_id[0],
            $web_form_status,
            $web_form_sidebar[0],
            $web_form_style[0],
            $merged_web_forms[$web_form_id[0]]
        );
        $this->addSuccessMessage('Settings update successful');
        $this->subscribeViaFormView();
    }

    /**
     * Automation
     */
    public function automationView()
    {
        $this->redirectIfNotAuthorized();

        $api = $this->getGrAPI();

        $this->context->smarty->assign(array('selected_tab' => 'automation'));

        $categories = Category::getCategories(1, true, false);

        if ($categories) {
            $this->context->smarty->assign(array('categories' => $categories));
        }

        $this->context->smarty->assign(array('campaigns' => $api->getCampaigns()));
        $this->context->smarty->assign(array('cycle_days' => $api->getAutoResponders()));

        // add new automation
        $is_submit = Tools::isSubmit('NewAutomationConfiguration');

        if ($is_submit) {
            // check _POST
            $category  = Tools::getValue('category');
            $campaign  = Tools::getValue('campaign');
            $action    = Tools::getValue('a_action');
            $cycle_day = Tools::getValue('cycle_day');

            if (empty($category[0])) {
                $this->addErrorMessage('Category can not be empty');
            } elseif (empty($campaign[0])) {
                $this->addErrorMessage('Campaign can not be empty');
            } elseif (empty($action[0])) {
                $this->addErrorMessage('Action can not be empty');
            } else {
                $add_to_cycle = Tools::getValue('add_to_cycle');
                $cycle_day    = 'yes' === $add_to_cycle ? $cycle_day : null;
                // set automation info to DB
                $this->db->insertAutomationSettings($category[0], $campaign[0], $action[0], $cycle_day);
                $this->addSuccessMessage('Automatic segmentation created');
            }
        }

        // edit automation
        $is_submit          = Tools::isSubmit('EditAutomationConfiguration');
        $automation_id = Tools::getValue('update_id');
        if ($is_submit && $automation_id) {
            $category  = Tools::getValue('category');
            $campaign  = Tools::getValue('campaign');
            $action    = Tools::getValue('a_action');
            $cycle_day = Tools::getValue('cycle_day');

            if (empty($campaign)) {
                $this->addErrorMessage('Campaign can not be empty');
            } elseif (empty($action)) {
                $this->addErrorMessage('Action can not be empty');
            } else {
                $add_to_cycle = Tools::getValue('add_to_cycle');
                $cycle_day    =!empty($add_to_cycle) ? $cycle_day : null;
                // set automation info to DB
                $this->db->updateAutomationSettings($category, $automation_id, $campaign, $action, $cycle_day);
                $this->addSuccessMessage('Automatic segmentation updated');
            }
        }

        // delete automation
        $delete_id = Tools::getValue('delete_id');
        if ($delete_id) {
            $this->db->deleteAutomationSettings($delete_id);
            $this->addSuccessMessage('Automatic segmentation removed');
        }

        $update_status = Tools::getValue('update_status');
        $update_id     = Tools::getValue('update_id');

        // update automation status
        if ($update_status && $update_id) {
            $this->db->updateAutomationStatus($update_status, $update_id);
        }

        // default params
        $selected_category  = '';
        $selected_campaign  = '';
        $selected_action    = '';
        $selected_cycle_day = '';
        $show_box           = 0;

        // get automations
        $automation_settings = $this->db->getAutomationSettings();
        $edit_automation     = Tools::getValue('edit_id');
        $this->context->smarty->assign(array('edit_automation' => $edit_automation));
        if (!empty($automation_settings)) {
            $this->context->smarty->assign(array('automation_settings' => $automation_settings));

            if ($edit_automation) {
                foreach ($automation_settings as $as) {
                    if ($as['id'] == $edit_automation) {
                        $selected_category  = $as['category_id'];
                        $selected_campaign  = $as['campaign_id'];
                        $selected_action    = $as['action'];
                        $selected_cycle_day = $as['cycle_day'];
                        $show_box           = $edit_automation;

                        $this->context->smarty->assign(array('selected_id' => $as['id']));
                    }
                }
            }
        } else {
            $this->context->smarty->assign(array('no_automation' => 'yes'));
        }

        // set categroies for edit automation view
        if ($categories) {
            foreach ($categories as $id => $category) {
                if ($automation_settings) {
                    foreach ($automation_settings as $automation) {
                        // unset category if already is set automation for this option
                        if ($automation['category_id'] == $category['id_category']) {
                            unset($categories[$id]);
                        }
                    }
                }
            }

            $this->context->smarty->assign(array('d_categories' => $categories));
        }

        $this->context->smarty->assign(array('selected_category' => $selected_category));
        $this->context->smarty->assign(array('selected_campaign' => $selected_campaign));
        $this->context->smarty->assign(array('selected_action' => $selected_action));
        $this->context->smarty->assign(array('selected_cycle_day' => $selected_cycle_day));
        $this->context->smarty->assign(array('show_box' => $show_box));
    }

    /**
     * Get Admin Token
     * @return bool|string
     */
    public function getToken()
    {
        return Tools::getAdminTokenLite(Tab::getIdFromClassName('AdminGetresponse'));
    }

    /**
     * Validate custom fields
     *
     * @param $customs
     *
     * @return array
     */
    private function validateCustoms($customs)
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
     * Ajax for add cycle
     * &ajax&action=getmessages
     */
    public function displayAjaxGetMessages()
    {
        header('Content-Type: application/json');

        $settings = $this->db->getSettings();

        if (empty($settings['api_key'])) {
            die(Tools::jsonEncode(array('error' => 'Wrong API Key', 'table' => '')));
        }

        $api = $this->getGrAPI();

        $campaign_id   = Tools::getValue('campaign_id');
        $campaign_name = Tools::getValue('campaign_name');

        if (empty($campaign_id)) {
            die(Tools::jsonEncode(array('error' => 'Campaign id can\'t be empty.', 'table' => '')));
        }

        try {
            $messages = (array) $api->getAutoresponders();
        } catch (Exception $e) {
            $messages = array();
        }

        $table = array();
        $counter = 1;

        foreach ($messages as $message) {
            $message_info                          = array();
            $message_info['id']                    = $message->autoresponderId;
            $message_info['on_day']                = $message->triggerSettings->dayOfCycle;
            $message_info['triggers_name']         = $message->name;
            $message_info['messages_id']           = $message->autoresponderId;
            $message_info['messages_name']         = $message->name;
            $message_info['messages_subject']      = $message->subject;
            $message_info['messages_campaigns_id'] = $message->triggerSettings->selectedCampaigns;
            $message_info['status']                = 'active';
            $message_info['campaigns_name']        = $campaign_name;

            $table[] = $message_info;
            $counter ++;
        }

        $return = array('error' => '', 'table' => $table);
        die(Tools::jsonEncode($return));
    }

    /**
     * Ajax add campaign
     * &ajax&action=addcampaign
     */
    public function displayAjaxAddCampaign()
    {
        header('Content-Type: application/json');

        $settings = $this->db->getSettings();

        if (empty($settings['api_key'])) {
            die(Tools::jsonEncode(array('type' => 'error', 'msg' => 'Wrong API Key')));
        }

        $campaign_name        = Tools::getValue('campaign_name');
        $from_field           = Tools::getValue('from_field');
        $reply_to_field       = Tools::getValue('reply_to_field');
        $confirmation_subject = Tools::getValue('confirmation_subject');
        $confirmation_body    = Tools::getValue('confirmation_body');

        if (empty($campaign_name)) {
            die(Tools::jsonEncode(array('type' => 'error', 'msg' => 'Campaign name can\'t be empty.')));
        }

        if (empty($from_field)) {
            die(Tools::jsonEncode(array('type' => 'error', 'msg' => 'From field can\'t be empty.')));
        }

        if (empty($reply_to_field)) {
            die(Tools::jsonEncode(array('type' => 'error', 'msg' => 'Reply field can\'t be empty.')));
        }

        if (empty($confirmation_subject)) {
            die(Tools::jsonEncode(array('type' => 'error', 'msg' => 'Confirmation subject can\'t be empty.')));
        }

        if (empty($confirmation_body)) {
            die(Tools::jsonEncode(array('type' => 'error', 'msg' => 'Confirmation body can\'t be empty.')));
        }

        $campaign_name = Tools::strtolower($campaign_name);

        if (preg_match('/^[\w\-]+$/', $campaign_name) == false) {
            die(Tools::jsonEncode(array('type' => 'error', 'msg' => 'Campaign name contains invalid characters.')));
        }

        try {
            // add new campaign to GR
            $this->addCampaignToGR(
                $campaign_name,
                $from_field,
                $reply_to_field,
                $confirmation_subject,
                $confirmation_body
            );

            die(Tools::jsonEncode(array(
                'type' => 'success',
                'msg'  => 'Campaign "' . $campaign_name . '" sucessfully created.',
                'c'    => $campaign_name
            )));
        } catch (GrApiException $e) {
            die(Tools::jsonEncode(array(
                'type' => 'error',
                'msg'  => $e->getMessage()
            )));
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
    private function addCampaignToGR(
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
    private function addSuccessMessage($message)
    {
        $this->context->smarty->assign(array('flash_message' => array(
            'message' => $this->l($message),
            'status' => 'success'
        )));
    }

    /**
     * @param string $message
     */
    private function addErrorMessage($message)
    {
        $this->context->smarty->assign(array('flash_message' => array(
            'message' => $this->l($message),
            'status' => 'danger'
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

    private function redirectIfNotAuthorized()
    {
        $settings = $this->db->getSettings();

        if (empty($settings['api_key'])) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminGetresponse'));
        }
    }

    private function getGrAPI()
    {
        $settings = $this->db->getSettings();
        return new GrApi($settings['api_key'], $settings['account_type'], $settings['crypto']);
    }
}
