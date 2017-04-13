<?php
/**
 * This module integrate GetResponse and PrestaShop Allows subscribe via checkout page and export your contacts.
 *
 *  @author Getresponse <grintegrations@getresponse.com>
 *  @copyright GetResponse
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once(_PS_MODULE_DIR_ . '/getresponse/classes/DbConnection.php');

class AdminGetresponseController extends ModuleAdminController
{
    /**
     * construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->bootstrap  = true;
        $this->display    = 'view';
        $this->meta_title = $this->l('GetResponse Integration');

        $this->apikey      = null;
        $this->crypto      = null;
        $this->identifier  = 'api_key';
        $this->api_url     = 'https://api.getresponse.com/v3';
        $this->gr_tpl_path = _PS_MODULE_DIR_ . 'getresponse/views/templates/admin/';
        $this->gr_css_path = _PS_MODULE_DIR_ . 'getresponse/views/css/';

        // API urls
        $this->api_urls = array(
            'gr' => 'https://api.getresponse.com/v3'
        );

        $instance = Db::getInstance();
        $this->db = new DbConnection($instance);

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    /**
     * Set Css & js
     * @return mixed
     */
    public function setMedia()
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

        return parent::setMedia();
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
        $action = Tools::getValue('action');

        // set main settings
        $this->setSettings();

        if (!empty($this->apikey)) {
            switch ($action) {
                case 'api':
                    $this->apiView();
                    break;
                case 'exportcustomers':
                    $this->exportView();
                    break;
                case 'viapage':
                    $this->viapageView();
                    break;
                case 'viawebform':
                    $this->viawebformView();
                    break;
                case 'automation':
                    $this->automationView();
                    break;
                default:
                    $this->apiView();
            }
        } else {
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
     * Set main settings
     */
    public function setSettings()
    {
        $this->context->smarty->assign(array('form_status' => false));
        $this->context->smarty->assign(array('status_text' => false));

        $this->action_url = $this->context->link->getAdminLink('AdminGetresponse');

        $this->context->smarty->assign(array('gr_tpl_path' => $this->gr_tpl_path));
        $this->context->smarty->assign(array('action_url' => $this->action_url));
        $this->context->smarty->assign('base_url', __PS_BASE_URI__);

        $settings = $this->db->getSettings();
        if (!empty($settings)) {
            if (!empty($settings['api_key'])) {
                $this->apikey = $settings['api_key'];
                $this->context->smarty->assign(array('api_key' => $this->apikey));
            }

            // settings
            if (!empty($settings['account_type'])) {
                $this->api_url = $this->api_urls[$settings['account_type']];

                if ($settings['account_type'] != 'gr') {
                    $this->api_url = $this->api_urls[$settings['account_type']] . '/' . $settings['crypto'];
                }

                $this->context->smarty->assign(array('api_url' => $this->api_url));
            }

            if (!empty($settings['crypto'])) {
                $this->crypto = $settings['crypto'];
            }

            if (!empty($settings['cycle_day'])) {
                $this->cycle_day = $settings['cycle_day'];
            }
        }
    }

    /**
     * API key settings
     */
    public function apiView()
    {
        $this->context->smarty->assign(array('selected_tab' => 'api'));

        $is_submit = Tools::isSubmit('ApiConfiguration');
        if ($is_submit) {
            $api_key = Tools::getValue('api_key');

            if (!empty($api_key)) {
                $account_type = Tools::getValue('account_type');
                $api_crypto   = Tools::getValue('crypto');

                if (empty($account_type[0])) {
                    $account_type[0] = 'gr';
                }

                if ($account_type[0] != 'gr' && empty($api_crypto)) {
                    $this->context->smarty->assign(array(
                        'form_status' => 'error',
                        'status_text' => $this->l('For GetResponse360 Crypto can not be empty')
                    ));
                } else {
                    if ($account_type[0] == 'gr') {
                        $api_crypto = null;
                    }

                    $this->api_url = $this->api_urls[$account_type[0]] . '/' . $api_crypto;

                    // check if api key is correct
                    $response = $this->db->ping($api_key);

                    // set api key and api url to DB if are correct
                    if ($response === true) {
                        $this->db->updateApiSettings($api_key, $account_type[0], $api_crypto);
                        $this->context->smarty->assign(array(
                            'form_status' => 'success',
                            'status_text' => $this->l('API Key updated')
                        ));
                    } else {
                        if ($account_type[0] != 'gr') {
                            $this->context->smarty->assign(array(
                                'form_status' => 'error',
                                'status_text' => $this->l('Wrong API key or Crypto')
                            ));
                        } else {
                            $this->context->smarty->assign(array(
                                'form_status' => 'error',
                                'status_text' => $this->l('Wrong API Key')
                            ));
                        }
                    }
                }
            } else {
                $this->context->smarty->assign(array(
                    'form_status' => 'error',
                    'status_text' => $this->l('Api Key field can not be empty')
                ));
            }
        }

        $settings = $this->db->getSettings();
        if (!empty($settings)) {
            $this->context->smarty->assign(array('api_key' => $settings['api_key']));
            $this->context->smarty->assign(array('account_type' => $settings['account_type']));
            $this->context->smarty->assign(array('crypto' => $settings['crypto']));
        }
    }

    /**
     * Export customers
     */
    public function exportView()
    {
        $this->context->smarty->assign(array('selected_tab' => 'exportcustomers'));

        $campaigns = $this->db->getCampaigns();
        $this->context->smarty->assign(array('campaigns' => $campaigns));

        $fromfields = $this->db->getFromFields();
        if (!empty($fromfields)) {
            $this->context->smarty->assign(array('fromfields' => $fromfields));
        }

        $subscriptionConfirmationsSubject = $this->db->getSubscriptionConfirmationsSubject();
        if (!empty($subscriptionConfirmationsSubject)) {
            $this->context->smarty->assign(
                array('subscriptionConfirmationsSubject' => $subscriptionConfirmationsSubject)
            );
        }

        $subscriptionConfirmationsBody = $this->db->getSubscriptionConfirmationsBody();
        if (!empty($subscriptionConfirmationsBody)) {
            $this->context->smarty->assign(
                array('subscriptionConfirmationsBody' => $subscriptionConfirmationsBody)
            );
        }

        $cycle_days = $this->db->getCycleDay();
        $this->context->smarty->assign(array('cycle_days' => $cycle_days));

        $custom_fields = $this->db->getCustoms();
        $this->context->smarty->assign(array('custom_fields' => $custom_fields));

        $this->context->smarty->assign(array('token' => $this->getToken()));

        $c_param = Tools::getValue('c');
        if ($c_param) {
            $this->context->smarty->assign(array('c' => $c_param));
        }

        // export customer
        $is_submit = Tools::isSubmit('ExportConfiguration');
        if ($is_submit) {
            // set api key
            if (empty($this->apikey)) {
                $this->context->smarty->assign(array(
                    'form_status' => 'error',
                    'status_text' => $this->l('Wrong API Key')
                ));
            }

            // check _POST
            $campaign = Tools::getValue('campaign');

            if (empty($campaign[0]) || $campaign[0] == '0') {
                $this->context->smarty->assign(array(
                    'form_status' => 'error',
                    'status_text' => $this->l('No campaign selected')
                ));
            } else {
                $newsletter_guests = false;

                // check _POST
                $ng        = Tools::getValue('newsletter_guests');
                $cycle_day = Tools::getValue('cycle_day');

                if (!empty($ng)) {
                    $newsletter_guests = true;
                }

                $posted_customs = Tools::getValue('custom_field');
                $validation     = $this->validateCustoms($posted_customs);
                if (is_array($validation) && !empty($validation['form_status'])) {
                    $this->context->smarty->assign($validation);
                } else {
                    // get contacts
                    $contacts = $this->db->getContacts(null, $newsletter_guests);
                    if (empty($contacts)) {
                        $this->context->smarty->assign(array(
                            'form_status' => 'error',
                            'status_text' => $this->l('No contacts to export')
                        ));
                    } else {
                        $add_to_cycle = Tools::getValue('add_to_cycle');
                        $cycle_day    = !is_null($add_to_cycle) ? $cycle_day : null;
                        // export contacts to GR campaign
                        $add = $this->db->exportSubscriber(
                            $campaign[0],
                            $contacts,
                            $cycle_day
                        );

                        // show notice
                        if (is_array($add) && isset($add['status']) && $add['status'] == 1) {
                            $this->context->smarty->assign(array(
                                'message' => $add['message'],
                                'form_status' => 'success',
                                'status_text' => $add['message']
                            ));
                        } else {
                            $this->context->smarty->assign(array(
                                'form_status' => 'error',
                                'status_text' => $add['message']
                            ));
                        }
                    }
                }
            }
        }

        $settings = $this->db->getSettings();


        if (!empty($settings)) {
            $this->context->smarty->assign(array('api_key' => $settings['api_key']));
            $this->context->smarty->assign(array('account_type' => $settings['account_type']));
            $this->context->smarty->assign(array('crypto' => $settings['crypto']));
            $this->context->smarty->assign(array('update_address' => $settings['update_address']));
        }
    }

    /**
     * Subscription via registration page
     */
    public function viapageView()
    {
        $this->context->smarty->assign(array('selected_tab' => 'viapage'));

        $fromfields = $this->db->getFromFields();
        if (!empty($fromfields)) {
            $this->context->smarty->assign(array('fromfields' => $fromfields));
        }

        // ajax - update subscription
        $subscription = Tools::getValue('subscription');
        if ($subscription) {
            $this->db->updateSettingsSubscription($subscription);
        }

        $campaigns = $this->db->getCampaigns();
        $this->context->smarty->assign(array('campaigns' => $campaigns));

        $subscriptionConfirmationsSubject = $this->db->getSubscriptionConfirmationsSubject();
        if (!empty($subscriptionConfirmationsSubject)) {
            $this->context->smarty->assign(
                array('subscriptionConfirmationsSubject' => $subscriptionConfirmationsSubject)
            );
        }

        $subscriptionConfirmationsBody = $this->db->getSubscriptionConfirmationsBody();
        if (!empty($subscriptionConfirmationsBody)) {
            $this->context->smarty->assign(array('subscriptionConfirmationsBody' => $subscriptionConfirmationsBody));
        }

        $cycle_days = $this->db->getCycleDay();
        $this->context->smarty->assign(array('cycle_days' => $cycle_days));

        $custom_fields = $this->db->getCustoms();
        $this->context->smarty->assign(array('custom_fields' => $custom_fields));

        $this->context->smarty->assign(array('token' => $this->getToken()));

        $c_param = Tools::getValue('c');
        if ($c_param) {
            $this->context->smarty->assign(array('c' => $c_param));
        }

        $is_submit = Tools::isSubmit('ViapageConfiguration');
        if ($is_submit) {
            if (empty($this->apikey)) {
                $this->context->smarty->assign(array(
                    'form_status' => 'error',
                    'status_text' => $this->l('Wrong API Key')
                ));
            }

            // check _POST
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
                $cycle_day      = !is_null($add_to_cycle) ? $cycle_day : null;

                $validation = $this->validateCustoms($posted_customs);
                if (is_array($validation) && !empty($validation['form_status'])) {
                    $this->context->smarty->assign($validation);
                } else {
                    $this->db->updateSettings($status, $campaign[0], $update_address, $cycle_day, $newsletter);
                    $this->db->updateCustoms($posted_customs);
                    $this->context->smarty->assign(array(
                        'form_status' => 'success',
                        'status_text' => $this->l('Settings update successful')
                    ));
                }
            } elseif (!empty($campaign[0]) && $campaign[0] == '0') {
                $this->context->smarty->assign(array(
                    'form_status' => 'error',
                    'status_text' => $this->l('No campaign selected')
                ));
            }
        }

        $settings = $this->db->getSettings();

        if (!empty($settings)) {
            $this->context->smarty->assign(array('status' => $settings['active_subscription']));
            $this->context->smarty->assign(array('selected_campaign' => $settings['campaign_id']));
            $this->context->smarty->assign(array('selected_cycle_day' => $settings['cycle_day']));
            $this->context->smarty->assign(array('update_address' => $settings['update_address']));
            $this->context->smarty->assign(
                array('active_newsletter_subscription' => $settings['active_newsletter_subscription'])
            );

            $custom_fields = $this->db->getCustoms();
            $this->context->smarty->assign(array('custom_fields' => $custom_fields));
        }
    }

    /**
     * Subscription via webform
     */
    public function viawebformView()
    {
        $this->context->smarty->assign(array('selected_tab' => 'viawebform'));

        $campaigns = $this->db->getCampaigns();
        if (!empty($campaigns)) {
            $campaign_id = array();
            foreach ($campaigns as $campaign) {
                $campaign_id[$campaign['id']] = $campaign['name'];
            }

            $this->context->smarty->assign(array('campaigns' => $campaign_id));
        }

        // get old webforms
        $webforms = $this->db->getWebforms();
        if (!empty($webforms)) {
            $this->context->smarty->assign(array('webforms' => $webforms));
        }

        // get new forms
        $forms = $this->db->getForms();
        if (!empty($forms)) {
            $this->context->smarty->assign(array('forms' => $forms));
        }

        // ajax - update subscription
        $subscription = Tools::getValue('subscription');
        if ($subscription) {
            $this->db->updateWebformSubscription($subscription);
        }

        $is_submit = Tools::isSubmit('ViawebformConfiguration');
        if ($is_submit) {
            // check _POST
            $webform_id      = Tools::getValue('webform_id');
            $webform_sidebar = Tools::getValue('webform_sidebar');
            $webform_style   = Tools::getValue('webform_style');
            $webform_status  = Tools::getValue('webform_status');

            if (is_array($webform_id) && empty($webform_id[0])) {
                $this->context->smarty->assign(array(
                    'form_status' => 'error',
                    'status_text' => $this->l('You have to select a webform')
                ));
            } else {
                $webforms_all = array_merge($webforms, $forms);
                $webforms_merged = array();
                foreach ($webforms_all as $form) {
                    $webforms_merged[$form->webformId] = $form->scriptUrl;
                }

                // set web form info to DB
                $this->db->updateWebformSettings(
                    $webform_id[0],
                    $webform_status,
                    $webform_sidebar[0],
                    $webform_style[0],
                    $webforms_merged[$webform_id[0]]
                );
                $this->context->smarty->assign(array(
                    'form_status' => 'success',
                    'status_text' => $this->l('Settings update successful')
                ));
            }
        }

        $settings = $this->db->getWebformSettings();
        if (!empty($settings)) {
            $this->context->smarty->assign(array('webform_id' => $settings['webform_id']));
            $this->context->smarty->assign(array('webform_sidebar' => $settings['sidebar']));
            $this->context->smarty->assign(array('webform_style' => $settings['style']));
            $this->context->smarty->assign(array('webform_status' => $settings['active_subscription']));
        }
    }

    /**
     * Automation
     */
    public function automationView()
    {
        $this->context->smarty->assign(array('selected_tab' => 'automation'));
        $this->context->smarty->assign(array('gr_css_path' => $this->gr_css_path));

        if (empty($this->apikey)) {
            $this->context->smarty->assign(array('form_status' => 'error', 'status_text' => $this->l('Wrong API Key')));
        }

        $categories = Category::getCategories(1, true, false);
        if ($categories) {
            $this->context->smarty->assign(array('categories' => $categories));
        }

        $campaigns = $this->db->getCampaigns();
        $this->context->smarty->assign(array('campaigns' => $campaigns));

        $cycle_days = $this->db->getCycleDay();
        $this->context->smarty->assign(array('cycle_days' => $cycle_days));

        // add new automation
        $is_submit = Tools::isSubmit('NewAutomationConfiguration');
        if ($is_submit) {
            // check _POST
            $category  = Tools::getValue('category');
            $campaign  = Tools::getValue('campaign');
            $action    = Tools::getValue('a_action');
            $cycle_day = Tools::getValue('cycle_day');

            if (empty($category[0])) {
                $this->context->smarty->assign(array(
                    'form_status' => 'error',
                    'status_text' => $this->l('Category can not be empty')
                ));
            } elseif (empty($campaign[0])) {
                $this->context->smarty->assign(array(
                    'form_status' => 'error',
                    'status_text' => $this->l('Campaign can not be empty')
                ));
            } elseif (empty($action[0])) {
                $this->context->smarty->assign(array(
                    'form_status' => 'error',
                    'status_text' => $this->l('Action can not be empty')
                ));
            } else {
                $add_to_cycle = Tools::getValue('add_to_cycle');
                $cycle_day    =!empty($add_to_cycle) ? $cycle_day[0] : null;
                // set automation info to DB
                $this->db->insertAutomationSettings($category[0], $campaign[0], $action[0], $cycle_day);
                $this->context->smarty->assign(array(
                    'form_status' => 'success',
                    'status_text' => $this->l('Automatic segmentation created')
                ));
            }
        }

        // edit automation
        $is_submit          = Tools::isSubmit('EditAutomationConfiguration');
        $automation_to_edit = Tools::getValue('update_id');
        if ($is_submit && $automation_to_edit) {
            $category  = Tools::getValue('category');
            $campaign  = Tools::getValue('campaign');
            $action    = Tools::getValue('a_action');
            $cycle_day = Tools::getValue('cycle_day');

            if (empty($campaign)) {
                $this->context->smarty->assign(array(
                    'form_status' => 'error',
                    'status_text' => $this->l('Campaign can not be empty')
                ));
            } elseif (empty($action)) {
                $this->context->smarty->assign(array(
                    'form_status' => 'error',
                    'status_text' => $this->l('Action can not be empty')
                ));
            } else {
                $add_to_cycle = Tools::getValue('add_to_cycle');
                $cycle_day    =!empty($add_to_cycle) ? $cycle_day : null;
                // set automation info to DB
                $this->db->updateAutomationSettings($category, $automation_to_edit, $campaign, $action, $cycle_day);
                $this->context->smarty->assign(array(
                    'form_status' => 'success',
                    'status_text' => $this->l('Automatic segmentation updated')
                ));
            }
        }

        // delete automation
        $delete_id = Tools::getValue('delete_id');
        if ($delete_id) {
            $this->db->deleteAutomationSettings($delete_id);
            $this->context->smarty->assign(array(
                'form_status' => 'success',
                'status_text' => $this->l('Automatic segmentation removed')
            ));
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
        return Tools::getAdminToken($this->context->shop->domain . $this->context->shop->id_theme);
    }

    /**
     * Validate custom fields
     *
     * @param $customs
     *
     * @return array|bool
     */
    private function validateCustoms($customs)
    {
        if (is_array($customs)) {
            foreach ($customs as $custom) {
                if (!empty($custom) && preg_match('/^[\w\-]+$/', $custom) == false) {
                    return array(
                        'form_status' => 'error',
                        'status_text' => 'Error - "' . $custom . '" ' . $this->l('contains invalid characters')
                    );
                }
            }
        }

        return true;
    }

    /**
     * Ajax for add cycle
     * &ajax&action=getmessages
     */
    public function displayAjaxGetMessages()
    {
        if (empty($this->apikey)) {
            die(Tools::jsonEncode(array('error' => 'Wrong API Key', 'table' => '')));
        }

        $campaign_id   = Tools::getValue('campaign_id');
        $campaign_name = Tools::getValue('campaign_name');

        if (empty($campaign_id)) {
            die(Tools::jsonEncode(array('error' => 'Campaign id can\'t be empty.', 'table' => '')));
        }

        // add new campaign to GR
        $messages = $this->getMessagesFromGr();

        $table   = array();
        $counter = 1;
        if (is_object($messages)) {
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
        }

        $return = array('error' => '', 'table' => $table);
        die(Tools::jsonEncode($return));
    }

    /**
     * Get Messages from GetResponse via API
     *
     * @param $campaign_id
     *
     * @return string
     */
    private function getMessagesFromGr()
    {
        // required params
        if (empty($this->apikey)) {
            return false;
        }

        try {
            $result = $this->db->grApiInstance->getAutoresponders();

            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Ajax add campaign
     * &ajax&action=addcampaign
     */
    public function displayAjaxAddCampaign()
    {
        if (empty($this->apikey)) {
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

        // add new campaign to GR
        $add = $this->addCampaignToGR(
            $campaign_name,
            $from_field,
            $reply_to_field,
            $confirmation_subject,
            $confirmation_body
        );

        // show notice
        if (is_object($add) && isset($add->campaignId)) {
            die(Tools::jsonEncode(array(
                'type' => 'success',
                'msg'  => 'Campaign "' . $campaign_name . '" sucessfully created.',
                'c'    => $campaign_name
            )));
        } else {
            die(Tools::jsonEncode(array(
                'type' => 'error',
                'msg'  => 'Campaign "' . $campaign_name . '" has not been added ' . ' - ' . $add->message
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
     *
     * @return string
     */
    private function addCampaignToGR(
        $campaign_name,
        $from_field,
        $reply_to_field,
        $confirmation_subject,
        $confirmation_body
    ) {
        // required params
        if (empty($this->apikey) || empty($this->api_url)) {
            return false;
        }

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

            $result = $this->db->grApiInstance->createCampaign($params);

            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
