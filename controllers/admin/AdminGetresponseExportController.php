<?php
require_once 'AdminGetresponseController.php';

/**
 * Class AdminGetresponseExportController
 *
 * @author Getresponse <grintegrations@getresponse.com>
 * @copyright GetResponse
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AdminGetresponseExportController extends AdminGetresponseController
{
    public $name = 'AdminGetresponseExport';

    public function __construct()
    {
        parent::__construct();
        $this->addJquery();
        $this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/gr-export.js');

        if (Tools::isSubmit($this->name)) {
            $this->performExport();
        }
    }

    /**
     * Renders form for mapping edition
     * @return mixed
     */
    public function renderForm()
    {
        $fieldsForm = array(
            'legend' => array(
                'title' => $this->l('Update Mapping'),
            ),
            'input' => array(
                'id' => array(
                    'type' => 'hidden',
                    'name' => 'id'
                ),
                'customer_detail' => array(
                    'label' => $this->l('Customer detail'),
                    'name' => 'customer_detail',
                    'type' => 'text',
                    'disabled' => true
                ),
                'gr_custom' => array(
                    'label' => $this->l('Getresponse custom field name'),
                    'required'  => true,
                    'desc' => $this->l('
                        You can use lowercase English alphabet characters, numbers,
                        and underscore ("_"). Maximum 32 characters.
                    '),
                    'type' => 'text',
                    'name' => 'gr_custom'
                ),
                'default' => array(
                    'required'  => true,
                    'type' => 'hidden',
                    'name' => 'default'
                ),
                'mapping_on' => array(
                    'type'      => 'switch',
                    'label'     => $this->l('Turn on this mapping'),
                    'name'      => 'mapping_on',
                    'required'  => true,
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
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'saveMappingForm',
                'icon' => 'process-icon-save'
            )
        );

        /** @var HelperFormCore $helper */
        $helper = new HelperForm();
        $helper->currentIndex = AdminController::$currentIndex . '&mapping=1&token=' . $this->getToken();
        $helper->fields_value = array('mapping_on' => false, 'gr_custom' => false, 'customer_detail' => false);

        $customs = $this->db->getCustoms();
        foreach ($customs as $custom) {
            if (Tools::getValue('id') == $custom['id_custom']) {
                $helper->fields_value = array(
                    'id' => $custom['id_custom'],
                    'customer_detail' => $custom['custom_field'],
                    'gr_custom' => $custom['custom_name'],
                    'default' => $custom['default'] == 'yes' ? 1 : 0,
                    'mapping_on' => $custom['active_custom'] == 'yes' ? 1 : 0
                );
            }
        }

        return $helper->generateForm(array(array('form' => $fieldsForm)));
    }

    public function initContent()
    {
        $this->display = 'view';

        if (Tools::isSubmit('update' . $this->name)) {
            $this->display = 'edit';
        }

        if (Tools::isSubmit('addCampaignForm')) {
            $this->saveCampaign();
        }

        if (Tools::isSubmit('saveMappingForm')) {
            $this->saveCustom();
        }

        parent::initContent();
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['add_campaign'] = array(
            'href' => self::$currentIndex . '&action=addCampaign&token=' . $this->getToken(),
            'desc' => $this->l('Add new contact list'),
            'icon' => 'process-icon-new'
        );

        parent::initPageHeaderToolbar();
    }

    /**
     * render main view
     * @return string
     */
    public function renderView()
    {
        $settings = $this->db->getSettings();
        $isConnected = !empty($settings['api_key']) ? true : false;

        $this->context->smarty->assign(array(
            'is_connected' => $isConnected,
            'active_tracking' => $settings['active_tracking']
        ));

        if (false === $isConnected) {
            $this->apiView();
            return parent::renderView();
        }

        if (Tools::getValue('action', null) == 'addCampaign') {
            $api = $this->getGrAPI();
            $fromFields = $this->normalizeFormFields($api->getFromFields());
            $confirmSubject = $this->normalizeComplexApiData(
                $api->getSubscriptionConfirmationsSubject(),
                'id',
                'name'
            );
            $confirmBody = $this->normalizeComplexApiData(
                $api->getSubscriptionConfirmationsBody(),
                'id',
                'name',
                'contentPlain'
            );
            $this->context->smarty->assign(array(
                'selected_tab' => 'export_customers',
                'export_customers_form' => $this->renderAddCampaignForm(
                    $this->prependOptionList('Select from field', $fromFields),
                    $this->prependOptionList('Select reply-to address', $fromFields),
                    $this->prependOptionList('Select confirmation message subject', $confirmSubject),
                    $this->prependOptionList('Select confirmation message body template', $confirmBody)
                ),
                'token' => $this->getToken(),
            ));
        } else {
            $this->exportCustomersView();
        }

        return parent::renderView();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function normalizeFormFields($data)
    {
        $options = array();

        foreach ($data as $row) {
            $options[] = array(
                'id_option' => $row['id'],
                'name' => $row['name'] . '(' . $row['email'] . ')'
            );
        }

        return $options;
    }

    /**
     * @param array $data
     * @param string $id
     * @param string $name
     * @param null|string $complex
     * @param array $options
     * @return array
     */
    private function normalizeComplexApiData($data, $id, $name, $complex = null, $options = array())
    {
        foreach ($data as $row) {
            $options[] = array(
                'id_option' => $row[$id],
                'name' => $row[$name] . ' ' . ($complex != null ? $row[$complex] : '')
            );
        }

        return $options;
    }

    /**
     * Subscription via registration page
     */
    public function exportCustomersView()
    {
        $this->redirectIfNotAuthorized();

        $settings = $this->db->getSettings();
        $api = $this->getGrAPI();

        $this->context->smarty->assign(array(
            'selected_tab' => 'export_customers',
            'export_customers_form' => $this->renderExportForm(),
            'export_customers_list' => $this->renderCustomList(),
            'campaign_days' => json_encode($this->getCampaignDays($api->getAutoResponders())),
            'cycle_day' => $settings['cycle_day'],
            'token' => $this->getToken(),
        ));
    }


    /**
     * Get Admin Token
     * @return string
     */
    public function getToken()
    {
        return Tools::getAdminTokenLite('AdminGetresponseExport');
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->l('GetResponse');
        $this->toolbar_title[] = $this->l('Export Customer Data on Demand');
    }

    public function renderExportForm()
    {
        /** @var HelperFormCore $helper */
        $helper = new HelperFormCore();
        $api = $this->getGrAPI();

        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Export Your Customer Information From PrestaShop to your GetResponse Account')
                ),
                'description' => $this->l('Use this option for one time export of your existing customers.'),
                'input' => array(
                    array('type' => 'hidden', 'name' => 'autoresponders'),
                    array('type' => 'hidden', 'name' => 'cycle_day_selected'),
                    array(
                        'type' => 'select',
                        'name' => 'campaign',
                        'required' => true,
                        'label' => $this->l('Contact list'),
                        'options' => array(
                            'query' => array(
                                array('id' => '', 'name' => $this->l('Select a list'))
                                ) + $api->getCampaigns(),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'label' => $this->l('Include newsletter subscribers'),
                        'name' => 'newsletter',
                        'type' => 'switch',
                        'is_bool' => true,
                        'values' => array(
                            array('id' => 'newsletter_on', 'value' => 1, 'label' => $this->l('Yes')),
                            array('id' => 'newsletter_off', 'value' => 0, 'label' => $this->l('No'))
                        )
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => '',
                        'name' => 'addToCycle',
                        'values' => array(
                            'query' => array(
                                array('id' => 1, 'val' =>1, 'name' => $this->l(' Add to autoresponder cycle'))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Autoresponder day'),
                        'class'    => 'gr-select',
                        'name' => 'autoresponder_day',
                        'data-default' => $this->l('no autoresponders'),
                        'options' => array(
                            'query' => array(array('id' => '', 'name' => $this->l('no autoresponders'))),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'label' => $this->l('Update contacts info'),
                        'name' => 'contactInfo',
                        'type' => 'switch',
                        'is_bool' => true,
                        'values' => array(
                            array('id' => 'update_on', 'value' => 1, 'label' => $this->l('Yes')),
                            array('id' => 'update_off', 'value' => 0, 'label' => $this->l('No'))
                        ),
                        'desc' =>
                            $this->l('
                                Select this option if you want to overwrite contact details that 
                                already exist in your GetResponse database.
                            ') .
                            '<br>' .
                            $this->l('Clear this option to keep existing data intact.')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Export'),
                    'icon' => 'process-icon-download',
                    'name' => $this->name
                )
            )
        );

        $helper->fields_value = array(
            'campaign' => false,
            'autoresponder_day' => false,
            'contactInfo' => Tools::getValue('mapping', 0),
            'newsletter' => 0,
            'autoresponders' => json_encode($api->getAutoResponders()),
            'cycle_day_selected' => 0
        );

        return $helper->generateForm(array($fieldsForm)) . $this->renderList();
    }

    /**
     * Assigns values to forms
     * @param $obj
     * @return array
     */
    public function getFieldsValue($obj)
    {
        if (Tools::getValue('action', null) == 'addCampaign') {
            return array(
                'campaign_name' => null,
            );
        }

        if ($this->display == 'view') {

            return array(
                'campaign' => Tools::getValue('campaign', null),
                'autoresponder_day' => Tools::getValue('autoresponder_day', null),
                'contactInfo' => Tools::getValue('contactInfo', null),
                'newsletter' => Tools::getValue('newsletter', null)
            );
        } else {
            $customs = $this->db->getCustoms();

            foreach ($customs as $custom) {
                if (Tools::getValue('id') == $custom['id_custom']) {
                    return array(
                        'id' => $custom['id_custom'],
                        'customer_detail' => $custom['custom_field'],
                        'gr_custom' => $custom['custom_name'],
                        'default' => $custom['default'] == 'yes' ? 1 : 0,
                        'mapping_on' => $custom['active_custom'] == 'yes' ? 1 : 0
                    );
                }
            }

            return array(
                'id' => 1,
                'customer_detail' => '',
                'gr_custom' => '',
                'default' => 0,
                'on' => 0
            );
        }
    }


    public function performExport()
    {
        $campaign       = Tools::getValue('campaign');
        $addToCycle   = Tools::getValue('addToCycle_1', 0);
        $cycleDay      = Tools::getValue('autoresponder_day', null);
        $updateAddress = Tools::getValue('contactInfo', 0);
        $newsletter = Tools::getValue('newsletter', 0) ==  1 ? true : false;

        if (empty($campaign)) {
            $this->errors[] = $this->l('You need to select list');
            $this->exportCustomersView();
            return;
        }

        if (!empty($errors)) {
            $this->addErrorMessage(implode(',', $errors));
            $this->exportCustomersView();
            return;
        }

        $errorMessages = array();
        $api = $this->getGrAPI();

        // get contacts
        $contacts = $this->db->getContacts(!empty($newsletter) ? true : false);

        if (empty($contacts)) {
            $this->errors[] = $this->l('No contacts to export');
            $this->exportCustomersView();
            return;
        }

        foreach ($contacts as $contact) {
            if ($updateAddress == 1) {
                $customs = $api->mapCustoms($contact, $_POST, $this->db->getCustoms(), 'export');
            } else {
                $customs = array();
            }

            if (!empty($customs['custom_error']) && $customs['custom_error'] == true) {
                $this->errors[] = $this->l('Incorrect field name: ') . $customs['custom_message'];
                return;
            }

            $r = $api->addContact(
                $campaign,
                $contact['firstname'],
                $contact['lastname'],
                $contact['email'],
                $addToCycle == 1 ? $cycleDay : null,
                $customs
            );

            if (isset($r->httpStatus) && $r->httpStatus >= 400) {
                $errorMessages[] = '[' . $r->code . '] ' . $r->message;
            }
        }

        $this->confirmations[] = $this->l('Customer data exported');
    }
}
