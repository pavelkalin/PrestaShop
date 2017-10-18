<?php
require_once 'AdminGetresponseController.php';

/**
 * Class AdminGetresponseWebTrackingController
 *
 * @author Getresponse <grintegrations@getresponse.com>
 * @copyright GetResponse
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AdminGetresponseWebTrackingController extends AdminGetresponseController
{
    public function initContent()
    {
        $this->display = 'edit';
        parent::initContent();
    }

    /**
     * Toolbar title
     */
    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->l('Administration');
        $this->toolbar_title[] = $this->l('Web Event Tracking');
    }

    public function renderForm()
    {
        if (Tools::isSubmit('submitTracking')) {
            $this->updateTracking();
        }

        $settings = $this->db->getSettings();
        $this->show_form_cancel_button = false;

        if ($settings['active_tracking'] != 'disabled') {
            $this->fields_form = array(
                'legend' => array(
                    'title' => $this->l('Web Event Tracking'),
                ),
                'description' => $this->l('
                    Enable event tracking in GetResponse to uncover who is visiting your stores, 
                    how often, and why. Analyze and react to customer buying habits.
                '),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Send web event data to GetResponse'),
                        'name' => 'tracking',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Yes')),
                            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('No'))
                        ),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitTracking',
                    'icon' => 'process-icon-save'
                )
            );
        } else {
            $this->fields_form = array(
                'legend' => array(
                    'title' => $this->l('Web Event Tracking'),
                ),
                'description' =>
                    $this->l('
                        We can’t start sending data from PrestaShop to GetResponse yet. 
                        Make sure you have a Max or Pro account.
                    ') . '<br>' .
                    $this->l('
                        If you have a Max or Pro account, try disconnecting and reconnecting 
                        the GetResponse account within the GetResponse module. This should correct the issue.
                    ')
            );
        }

        return parent::renderForm();
    }

    /**
     * Tracking on/off switch
     */
    public function updateTracking()
    {
        $tracking = Tools::getValue('tracking');
        $settings = $this->db->getSettings();
        $api = new GrApi($settings['api_key'], $settings['account_type'], $settings['crypto']);
        $snippet = '';

        if ($tracking == 1) {
            $code = (array)$api->getTrackingCode();
            if (!empty($code) && is_object($code[0]) && 0 < strlen($code[0]->snippet)) {
                $snippet = $code[0]->snippet;
            }
            $this->confirmations[] = $this->l('Web event traffic tracking enabled');
        } elseif ($tracking == 0) {
            $this->confirmations[] = $this->l('Web event traffic tracking disabled');
        }

        $this->db->updateTracking($tracking == 1 ? 'yes' : 'no', $snippet);
    }

    /**
     * Get Admin Token
     * @return string
     */
    public function getToken()
    {
        return Tools::getAdminTokenLite('AdminGetresponseWebTracking');
    }
}
