<?php
require_once 'AdminGetresponseController.php';

/**
 * Class AdminGetresponseController
 * @author Getresponse <grintegrations@getresponse.com>
 * @copyright GetResponse
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AdminGetresponseContactListController extends AdminGetresponseController
{
    private $name = 'GRContactList';

    public function __construct()
    {
        parent::__construct();

        $this->addJquery();
        $this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/gr-automation.js');

        $this->context->smarty->assign(array(
            'gr_tpl_path' => _PS_MODULE_DIR_ . 'getresponse/views/templates/admin/',
            'action_url' => $this->context->link->getAdminLink('AdminGetresponseContactList'),
            'base_url', __PS_BASE_URI__
        ));
    }

    public function initProcess()
    {
        if (Tools::isSubmit('update' . $this->name)) {
            $this->display = 'edit';
        }
        if (Tools::isSubmit('create' . $this->name)) {
            $this->display = 'add';
        }
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->l('GetResponse');
        $this->toolbar_title[] = $this->l('Contact List Rules');
    }

    /**
     * @return mixed
     */
    public function renderList()
    {
        if (Tools::isSubmit('delete'.$this->name)) {
            $this->db->deleteAutomationSettings(Tools::getValue('id'));
            $this->confirmations[] = $this->l('Rule deleted');
        }

        if (Tools::isSubmit('submitBulkdelete'.$this->name)) {
            $selected = (array)Tools::getValue($this->name . 'Box');
            foreach ($selected as $toDelete) {
                $this->db->deleteAutomationSettings($toDelete);
            }
            $this->confirmations[] = $this->l('Rules deleted');
        }

        $this->page_header_toolbar_btn['new_rule'] = array(
            'href' => self::$currentIndex.'&create' . $this->name . '&token='.$this->getToken(),
            'desc' => $this->l('Add new rule', null, null, false),
            'icon' => 'process-icon-new'
        );

        $fieldsList = array(
            'category' => array('title' => $this->l('If customer buys in the category'), 'type' => 'text'),
            'action' => array('title' => $this->l('they are'), 'type' => 'text'),
            'contact_list' => array('title' => $this->l('Into the contact list'), 'type' => 'text'),
            'cycle_day' => array(
                'title' => $this->l('Add into the cycle on day'),
                'type' => 'bool',
                'icon' => array(
                    0 => 'disabled.gif',
                    1 => 'enabled.gif',
                    'default' => 'disabled.gif'
                ),
                'align' => 'center'
            ),
            'autoresponder' => array('title' => $this->l('Autoresponder'), 'type' => 'text'),
        );

        /** @var HelperListCore $helper */
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = false;

        $helper->title = $this->l('Contact List Rules');
        $helper->table = $this->name;
        $helper->token = $this->getToken();
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $helper->bulk_actions = array(
            'delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?'))
        );

        return $helper->generateList($this->getAutomationList(), $fieldsList);
    }

    /**
     * @return array
     */
    private function getAutomationList()
    {
        $api = $this->getGrAPI();
        $automations = array();
        $automationSettings = $this->db->getAutomationSettings();
        $categories = Category::getCategories(1, true, false);
        $autoresponders = $api->getAutoResponders();
        $campaigns = $api->getCampaigns();

        foreach ($automationSettings as $setting) {
            $automations[] = array(
                'id' => $setting['id'],
                'category' => $this->getCategoryName($categories, $setting['category_id']),
                'action' => $this->getActionName($setting['action']),
                'contact_list' => $this->getCampaignName($campaigns, $setting['campaign_id']),
                'cycle_day' => (is_numeric($setting['cycle_day'])),
                'autoresponder' => $this->getAutoresponderName($autoresponders, $setting['cycle_day']),
            );
        }

        return $automations;
    }

    /**
     * @param array $campaigns
     * @param string $campaignId
     * @return string
     */
    private function getCampaignName($campaigns, $campaignId)
    {
        foreach ($campaigns as $campaign) {
            if ($campaign['id'] == $campaignId) {
                return $campaign['name'];
            }
        }

        return '';
    }

    /**
     * @param array $categories
     * @param int $categoryId
     * @return string
     */
    private function getCategoryName($categories, $categoryId)
    {
        foreach ($categories as $category) {
            if ($category['id_category'] == $categoryId) {
                return $category['name'];
            }
        }

        return '';
    }

    /**
     * @param array $autoresponders
     * @param int $cycleDay
     * @return string
     */
    private function getAutoresponderName($autoresponders, $cycleDay)
    {
        foreach ($autoresponders as $autoresponder) {
            if ($autoresponder->triggerSettings->dayOfCycle == $cycleDay) {
                return '(' . $this->l('Day') . ': ' .
                    $autoresponder->triggerSettings->dayOfCycle . ') ' .
                    $autoresponder->name . ' (' . $this->l('Subject') .
                    ': ' . $autoresponder->subject . ')';
            }
        }

        return '';
    }

    /**
     * @param string $action
     * @return string
     */
    private function getActionName($action)
    {
        if ($action == 'move') {
            return $this->l('moved');
        } elseif ($action = 'copy') {
            return $this->l('copied');
        }

        return '';
    }

    /**
     * Tracking page view
     */
    public function automationView()
    {
        $settings = $this->db->getSettings();

        $this->page_header_toolbar_btn['new_rule'] = array(
                'href' => self::$currentIndex.'&action=add&token='.$this->getToken(),
                'desc' => $this->l('Add new rule', null, null, false),
                'icon' => 'process-icon-new'
            );

        $this->context->smarty->assign(array(
            'selected_tab' => 'automation',
            'tracking_status' => !empty($settings['active_tracking']) ? $settings['active_tracking'] : 'no'
        ));
    }

    /**
     * @return mixed
     */
    public function renderForm()
    {
        $id = Tools::getValue('id');

        if (Tools::isSubmit('submit'.$this->name)) {
            $category  = Tools::getValue('category');
            $campaign  = Tools::getValue('campaign');
            $action    = Tools::getValue('a_action');
            $addToCycle = Tools::getValue('options_1');
            $cycleDay = !empty($addToCycle) ? Tools::getValue('autoresponder_day') : null;

            if (empty($category)) {
                $this->errors[] = $this->l('The "if customer buys in category field" is invalid');
            }
            if (empty($action)) {
                $this->errors[] = $this->l('The "they are" field is required');
            }
            if (empty($campaign)) {
                $this->errors[] = $this->l('The "into the contact list" field is required');
            }

            if (!empty($addToCycle) && $cycleDay == '') {
                $this->errors[] = $this->l('The "autoresponder" field is required');
            }

            if (!empty($id) && empty($this->errors)) {
                try {
                    $this->db->updateAutomationSettings(
                        $category,
                        $id,
                        $campaign,
                        $action,
                        $cycleDay
                    );
                    Tools::redirectAdmin(AdminController::$currentIndex);
                } catch (Exception $e) {
                    //$this->addErrorMessage('Selected category and action are similar to the other one');
                }
            } elseif (empty($this->errors)) {
                $this->db->insertAutomationSettings(
                    $category,
                    $campaign,
                    $action,
                    $cycleDay
                );
                Tools::redirectAdmin(AdminController::$currentIndex);
            }
        }

        $api = $this->getGrAPI();
        $fieldsForm = array(
            'form' => array(
            'legend' => array(
                'title' => $this->l('Add new rule'),
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'automation_id',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'autoresponders',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'cycle_day_selected',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('If customer buys in category'),
                    'class'    => 'gr-select',
                    'name' => 'category',
                    'required' => true,
                    'options' => array(
                        'query' => Category::getCategories(1, true, false),
                        'id' => 'id_category',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('They are'),
                    'class'    => 'gr-select',
                    'name' => 'a_action',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array('id' => '', 'name' => $this->l('Select from field')),
                            array('id' => 'move', 'name' => $this->l('Moved')),
                            array('id' => 'copy', 'name' => $this->l('Copied')),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'class'    => 'gr-select',
                    'label' => $this->l('Into the contact list'),
                    'name' => 'campaign',
                    'required' => true,
                    'options' => array(
                        'query' => array_merge(
                            array(array('id' => '', 'name' => $this->l('Select a list'))),
                            $api->getCampaigns()
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type'    => 'checkbox',
                    'label'   => '',
                    'name'    => 'options',
                    'values'  => array(
                        'query' => array(array('id' => 1, 'name' => $this->l(' Add to autoresponder cycle'))),
                        'id'    => 'id',
                        'name'  => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Autoresponder day'),
                    'class'    => 'gr-select',
                    'name' => 'autoresponder_day',
                    'data-default' => $this->l('no autoresponders'),
                    'required' => true,
                    'options' => array(
                        'query' => array(array('id' => '', 'name' => $this->l('no autoresponders'))),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'NewAutomationConfiguration'
            ),
            'reset' => array(
                'title' => $this->l('Cancel'),
                'icon' => 'process-icon-cancel'
            ),
            'show_cancel_button' => true,
            )
        );

        /** @var HelperFormCore $helper */
        $helper = new HelperForm();

        $helper->fields_value = array(
            'category' => false,
            'a_action' => false,
            'campaign' => false,
            'autoresponder_day' => false,
            'cycle_day_selected' => false,
            'automation_id' => false,
            'autoresponders' => json_encode($api->getAutoResponders())
        );

        if (!empty($id)) {
            $automations = $this->db->getAutomationSettings();

            foreach ($automations as $automation) {
                if ($automation['id'] == $id) {
                    $helper->fields_value['category'] = $automation['category_id'];
                    $helper->fields_value['a_action'] = $automation['action'];
                    $helper->fields_value['campaign'] = $automation['campaign_id'];
                    $helper->fields_value['autoresponder_day'] = $automation['cycle_day'];
                    $helper->fields_value['cycle_day_selected'] = !empty($automation['cycle_day']);
                    $helper->fields_value['automation_id'] = $id;
                    break;
                }
            }
        }

        $helper->submit_action = 'submit' . $this->name;
        $helper->token = $this->getToken();

        return $helper->generateForm(array($fieldsForm));
    }

    /**
     * Get Admin Token
     * @return string
     */
    public function getToken()
    {
        return Tools::getAdminTokenLite('AdminGetresponseContactList');
    }
}
