<?php
/**
 * This module integrate GetResponse and PrestaShop Allows subscribe via checkout page and export your contacts.
 *
 *  @author    Grzegorz Struczynski <gstruczynski@getresponse.com>
 *  @copyright GetResponse
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

require_once( _PS_MODULE_DIR_ . '/getresponse/classes/GetResponseAPI3.class.php' );

/**
 * Class is used to calls to the PrestaShop Database
 *
 * Functions Create, Update, Insert
 * @uses Database instance [ie DB::getInstance()]
 */
class DbConnection
{
    public $grApiInstance;
    public $all_custom_fields;

    /** @var DbPDO */
    private $db;

    public function __construct($database)
    {
        $this->db  = $database;
        $this->obj = 1;
        $this->api_key = null;
        $this->settings = null;

        $context = Context::getContext();
        $cookie = $context->cookie->getAll();

        if (isset($cookie['shopContext'])) {
            $this->id_shop = (int)Tools::substr($cookie['shopContext'], 2, count($cookie['shopContext']));
        } else {
            $this->id_shop = $context->shop->id;
        }

        //db prefix
        $this->prefix_settings   = _DB_PREFIX_ . 'getresponse_settings';
        $this->prefix_webform    = _DB_PREFIX_ . 'getresponse_webform';
        $this->prefix_automation = _DB_PREFIX_ . 'getresponse_automation';
        $this->prefix_customs    = _DB_PREFIX_ . 'getresponse_customs';

        if (Module::isInstalled('getresponse')) {
            $this->settings = $this->getSettings();
        }
        $this->grApiInstance = $this->getApiInstance();
    }

    public function getApiInstance()
    {
        if (empty($this->api_key)) {
            return array();
        }

        try {
            $apiInstance = new GetResponseAPI3($this->api_key);

            return $apiInstance;
        } catch (Exception $e) {
            return array('message' => $e->getMessage());
        }
    }

    public function ping($api_key)
    {
        if (empty($api_key)) {
            return false;
        }

        $api = new GetResponseAPI3($api_key);
        $ping = $api->ping();

        if (isset($ping->accountId)) {
            return true;
        } else {
            return false;
        }
    }

    /******************************************************************/
    /** Get Methods ***************************************************/
    /******************************************************************/

    public function getSettings()
    {
        $sql = 'SELECT
                    *
                FROM
                    ' . pSQL($this->prefix_settings) . '
                WHERE
                    id_shop = ' . (int) $this->id_shop . '
                ';

        if ($results = $this->db->ExecuteS($sql)) {
            $this->api_key = $results[0]['api_key'];
            return $results[0];
        }
    }

    public function getWebformSettings()
    {
        $sql = 'SELECT
                    webform_id, active_subscription, sidebar, style, url
                FROM
                    ' . pSQL($this->prefix_webform) . '
                WHERE
                    id_shop = ' . (int) $this->id_shop . '
                ';

        if ($results = $this->db->ExecuteS($sql)) {
            return $results[0];
        }
    }

    public function getCampaigns()
    {
        if (empty( $this->api_key )) {
            return array();
        }

        try {
            $results = $this->grApiInstance->getCampaigns();

            if (!empty( $results )) {
                $campaigns = array();
                foreach ($results as $info) {
                    $campaigns[$info->name] = array(
                        'id'   => $info->campaignId,
                        'name' => $info->name
                    );
                }
                ksort($campaigns);

                return $campaigns;
            }

            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public function getWebforms()
    {
        if (empty( $this->api_key )) {
            return array();
        }

        try {
            $results = $this->grApiInstance->getWebForms();

            if (!empty( $results )) {
                $webforms = array();
                foreach ($results as $id => $info) {
                    $webforms[$id] = $info;
                }
                return $webforms;
            }

            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public function getForms()
    {
        if (empty( $this->api_key )) {
            return array();
        }

        try {
            $results = $this->grApiInstance->getForms();

            if (!empty( $results )) {
                $forms = array();
                foreach ($results as $id => $info) {
                    $forms[$id] = $info;
                }
                return $forms;
            }

            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public function getSubscriptionConfirmationsSubject($lang = 'EN')
    {
        if (empty($this->api_key)) {
            return array();
        }

        try {
            $results = $this->grApiInstance->getSubscriptionConfirmationsSubject($lang);

            if (!empty($results)) {
                $subjects = array();
                foreach ($results as $subject) {
                    $subjects[] = array(
                        'id'            => $subject->subscriptionConfirmationSubjectId,
                        'name'          => $subject->subject
                    );
                }
                return $subjects;
            }

            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public function getSubscriptionConfirmationsBody($lang = 'EN')
    {
        if (empty($this->api_key)) {
            return array();
        }

        try {
            $results = $this->grApiInstance->getSubscriptionConfirmationsBody($lang);

            if (!empty($results)) {
                $bodies = array();
                foreach ($results as $body) {
                    $bodies[] = array(
                        'id'            => $body->subscriptionConfirmationBodyId,
                        'name'          => $body->name,
                        'contentPlain'  => $body->contentPlain
                    );
                }
                return $bodies;
            }

            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public function getFromFields()
    {
        if (empty( $this->api_key )) {
            return false;
        }

        $fromfields = array();

        try {
            $results = $this->grApiInstance->getAccountFromFields();
            if (!empty( $results )) {
                foreach ($results as $info) {
                    $fromfields[] = array(
                        'id'    => $info->fromFieldId,
                        'name'  => $info->name,
                        'email' => $info->email,
                    );
                }
            }

            return $fromfields;
        } catch (Exception $e) {
            return false;
        }
    }

    public function checkModuleStatus($module)
    {
        if (empty( $module )) {
            return false;
        }

        $sql = 'SELECT
                    active
                FROM
                    ' . _DB_PREFIX_ . 'module
                WHERE
                    name = "' . pSQL($module) . '"
                ';

        if ($results = $this->db->ExecuteS($sql)) {
            if (isset( $results[0]['active'] ) && $results[0]['active'] == 1) {
                return 'active';
            }
        }

        return false;
    }

    public function getContacts($email = null, $newsletter_guests = null)
    {
        $where = !empty( $email ) ? " AND cu.email = '" . pSQL($email) . "'" : null;

        if (!empty( $newsletter_guests )) {
            $blocknewsletter = $this->checkModuleStatus('blocknewsletter');

            if ($blocknewsletter == 'active') {
                $ng_where = 'UNION SELECT
                        "Friend" as firstname,
                        "" as lastname,
                        n.email as email,
                        "" as company,
                        "" as birthday,
                        "" as address1,
                        "" as address2,
                        "" as postcode,
                        "" as city,
                        "" as phone,
                        "" as country,
                        "" as category
                    FROM
                        ' . _DB_PREFIX_ . 'newsletter n
                    WHERE
                        n.active = 1
                    AND
                        id_shop = ' . (int) $this->id_shop . '
                ';
            }
        }

        $sql = 'SELECT
                    cu.firstname as firstname,
                    cu.lastname as lastname,
                    cu.email as email,
                    cu.company as company,
                    cu.birthday as birthday,
                    ad.address1 as address1,
                    ad.address2 as address2,
                    ad.postcode as postcode,
                    ad.city as city,
                    ad.phone as phone,
                    co.iso_code as country,
                    "" as category
                FROM
                    ' . _DB_PREFIX_ . 'customer as cu
                LEFT JOIN
                    ' . _DB_PREFIX_ . 'address ad ON cu.id_customer = ad.id_customer
                LEFT JOIN
                    ' . _DB_PREFIX_ . 'country co ON ad.id_country = co.id_country
                WHERE
                    cu.newsletter = 1' . $where . '
                AND
                    cu.id_shop = ' . (int) $this->id_shop . '
                ' . $ng_where . '
                ';

        $contacts = $this->db->ExecuteS($sql);

        $sql = 'SELECT
                cu.email as email,
                group_concat(DISTINCT cp.id_category separator ", ") as category
            FROM
                ' . _DB_PREFIX_ . 'customer as cu
            LEFT JOIN
                ' . _DB_PREFIX_ . 'address ad ON cu.id_customer = ad.id_customer
            LEFT JOIN
                ' . _DB_PREFIX_ . 'country co ON ad.id_country = co.id_country
            LEFT JOIN
                ' . _DB_PREFIX_ . 'orders o ON o.id_customer = cu.id_customer
            LEFT JOIN
                ' . _DB_PREFIX_ . 'order_detail od ON (od.id_order = o.id_order AND o.id_shop = ' . (int) $this->id_shop . ')
            LEFT JOIN
                ' . _DB_PREFIX_ . 'category_product cp ON (cp.id_product = od.product_id AND od.id_shop = ' .
            (int) $this->id_shop . ')
            LEFT JOIN
                ' . _DB_PREFIX_ . 'category_lang cl ON (cl.id_category = cp.id_category AND cl.id_shop = ' .
            (int) $this->id_shop . ' AND cl.id_lang = cu.id_lang)
            WHERE
                    cu.newsletter = 1' . $where . '
                AND
                    cu.id_shop = ' . (int) $this->id_shop . '
            ';

        $addresses = $this->db->ExecuteS($sql);

        if (!empty( $addresses )) {
            $adr = array();
            foreach ($addresses as $address) {
                $adr[$address['email']] = $address['category'];
            }

            foreach ($contacts as $id => $contact) {
                if (in_array($contact['email'], array_keys($adr))) {
                    $contacts[$id]['category'] = $adr[$contact['email']];
                }
            }
        }

        if (!empty( $where )) {
            return $contacts[0];
        } else {
            return $contacts;
        }
    }

    public function getCustoms($default = null)
    {
        $where = !empty( $default ) ? " AND `default` = '" . pSQL($default) . "'" : null;

        $sql = 'SELECT
                    *
                FROM
                    ' . $this->prefix_customs . '
                WHERE
                    id_shop = ' . (int) $this->id_shop . $where;

        if ($results = $this->db->ExecuteS($sql)) {
            return $results;
        }
    }

    public function getAutomationSettings($status = null)
    {
        $where_status = !empty( $status ) ? " AND `active` = 'yes'" : null;

        $sql = 'SELECT
                    *
                FROM
                    ' . pSQL($this->prefix_automation) . '
                WHERE
                    id_shop = ' . (int) $this->id_shop . $where_status;

        if ($results = $this->db->ExecuteS($sql)) {
            return $results;
        }
    }

    public function getCycleDay()
    {
        if (empty( $this->api_key )) {
            return array();
        }

        try {
            $results = $this->grApiInstance->getAutoresponders();

            return $results;
        } catch (Exception $e) {
            return array();
        }
    }

    /******************************************************************/
    /** Update Methods ************************************************/
    /******************************************************************/

    public function updateApiSettings($apikey, $account_type, $crypto)
    {
        $query = "
        UPDATE " . $this->prefix_settings . " SET
            `api_key` = '".pSQL($apikey)."',
            `account_type` = '".pSQL($account_type)."',
            `crypto` = '".pSQL($crypto)."'
         WHERE
            `id_shop` = ".$this->id_shop;

        return $this->db->execute($query);
    }

    public function updateWebformSettings($webform_id, $active_subscription, $sidebar, $style, $url)
    {
        $query = "
        UPDATE ".$this->prefix_webform." SET
            `webform_id` = ".pSQL($webform_id).",
            `active_subscription` = '".pSQL($active_subscription)."',
            `sidebar` = '".pSQL($sidebar)."',
            `style` = '".pSQL($style)."',
            `url` = '".pSQL($url)."'
        WHERE
            `id_shop` = ".$this->id_shop;

        return $this->db->execute($query);
    }

    public function updateWebformSubscription($active_subscription)
    {
        $query = "
        UPDATE ".$this->prefix_webform." SET
            `active_subscription` = '".pSQL($active_subscription)."'
        WHERE
            `id_shop` = ".$this->id_shop;

        return $this->db->execute($query);
    }

    public function updateSettings($active_subscription, $campaign_id, $update_address, $cycle_day, $newsletter)
    {
        $query = "
        UPDATE ".$this->prefix_settings." SET
            `active_subscription` = '".pSQL($active_subscription)."',
            `active_newsletter_subscription` = '".pSQL($newsletter)."',
            `campaign_id` = '".pSQL($campaign_id)."',
            `update_address` = '".pSQL($update_address)."',
            `cycle_day` = '".pSQL($cycle_day)."'
        WHERE
            `id_shop` = ".$this->id_shop;

        return $this->db->execute($query);
    }

    public function updateSettingsSubscription($active_subscription)
    {
        $query = "
        UPDATE ".$this->prefix_settings." SET
            `active_subscription` = '".pSQL($active_subscription)."'
        WHERE
            `id_shop` = ".$this->id_shop;
        return $this->db->execute($query);
    }

    public function updateCustoms($customs)
    {
        $settings_customs = $this->getCustoms();
        if (empty($settings_customs)) {
            return false;
        }

        if (!empty( $customs )) {
            $allowed          = array();
            foreach ($settings_customs as $sc) {
                $allowed[$sc['custom_value']] = $sc;
            }

            if (!empty( $allowed )) {
                foreach (array_keys($allowed) as $a) {
                    if (in_array($a, array_keys($customs))) {
                        $sql = 'UPDATE
                                    ' . pSQL($this->prefix_customs) . '
                                SET
                                    custom_name = "' . pSQL($customs[$a]) . '",
                                    active_custom = "yes"
                                WHERE
                                    id_shop = "' . (int) $this->id_shop . '"
                                AND
                                    custom_value = "' . pSQL($a) . '"';

                        $this->db->Execute($sql);
                    } elseif ($allowed[$a]['default'] != 'yes') {
                        $sql = 'UPDATE
                                    ' . pSQL($this->prefix_customs) . '
                                SET
                                    active_custom = "no"
                                WHERE
                                    id_shop = "' . (int) $this->id_shop . '"
                                AND
                                    custom_value = "' . pSQL($a) . '"';

                        $this->db->Execute($sql);
                    }
                }
            }
        } else {
            foreach ($settings_customs as $sc) {
                if ($sc['default'] === 'no') {
                    $sql = 'UPDATE
                                ' . pSQL($this->prefix_customs) . '
                            SET
                                active_custom = "no"
                            WHERE
                                id_shop = "' . (int) $this->id_shop . '"
                            AND
                                custom_value = "' . pSQL($sc['custom_value']) . '"';

                    $this->db->Execute($sql);
                }
            }
        }
    }

    public function updateAutomationSettings($category_id, $automation_to_edit, $campaign_id, $action, $cycle_day)
    {
        $query = "
        UPDATE ".$this->prefix_automation." SET
            `category_id` = ".pSQL($category_id).",
            `campaign_id` = '".pSQL($campaign_id)."',
            `action` = '".pSQL($action)."',
            `cycle_day` = '".pSQL($cycle_day)."'
        WHERE
            `id` = ".(int)$automation_to_edit;
        return $this->db->execute($query);
    }

    public function updateAutomationStatus($status, $id)
    {
        $query = "
        UPDATE ".$this->prefix_automation." SET
            `active` = '".pSQL($status)."'
        WHERE
            'id_shop = " . (int) $this->id_shop . " AND id = " . (int) $id;
        return $this->db->execute($query);
    }

    /******************************************************************/
    /** Insert Methods ************************************************/
    /******************************************************************/

    public function insertAutomationSettings($category_id, $campaign_id, $action, $cycle_day)
    {
        $query = "
        INSERT INTO ".$this->prefix_automation."  (
            `category_id`, 
            `campaign_id`, 
            `action`, 
            `cycle_day`, 
            `id_shop`, 
            `active` 
            
        ) VALUES (
            ".pSQL($category_id).",
            '".pSQL($campaign_id)."',
            '".pSQL($action)."',
            '".pSQL($cycle_day)."',
            ".(int) $this->id_shop.",
            'yes'
        )";

        try {
            return $this->db->execute($query);
        } catch (Exception $e) {
            return false;
        }
    }

    /******************************************************************/
    /** Delete Methods ************************************************/
    /******************************************************************/

    public function deleteAutomationSettings($automation_id)
    {
        $sql = 'DELETE FROM `' . pSQL($this->prefix_automation) . '` WHERE `id` = ' . (int) $automation_id;

        return (bool) $this->db->execute($sql);
    }

    /******************************************************************/
    /** API Methods *****************************&*********************/
    /******************************************************************/

    /**
     * Export newsletter subscribers from Prestashop to GR campaign
     *
     * @param       $campaign_id
     * @param array $customers
     * @param int   $cycle_day
     *
     * @return mixed
     */
    public function exportSubscriber($campaign_id, $customers, $cycle_day)
    {
        if (empty( $_POST )) {
            return array('status' => '0', 'message' => 'Request error');
        }

        $failed = 0;

        if (!empty( $customers )) {
            foreach ($customers as $customer) {
                $customs = $this->mapCustoms($customer, $_POST, 'export');

                if (!empty( $customs['custom_error'] ) && $customs['custom_error'] == true) {
                    return array(
                        'status'  => '0',
                        'message' => 'Incorrect field name: "' . $customs['custom_message']
                    );
                }

                // add contact to GR via API
                $r = $this->addContact(
                    $campaign_id,
                    $customer['firstname'],
                    $customer['lastname'],
                    $customer['email'],
                    $cycle_day,
                    $customs
                );

                if (!empty($r->message) && $r->message != 'Contact in queue') {
                    if (in_array($r->message, array('Cannot add contact that is blacklisted', 'Email domain not exists'))) {
                        $failed++;
                    } else {
                        return array('status' => '0', 'message' => $r->message);
                    }
                }
            }
        }

        if ($failed > 0) {
            return array('status' => '1', 'message' => 'Export completed. ' . $failed . ' addresses that you blacklisted in your GetResponse account were skipped');
        }

        return array('status' => '1', 'message' => 'Export completed.');
    }

    /**
     * Map custom fields from DB and $_POST
     *
     * @param       $customer
     * @param       $customer_post
     * @param       $type
     *
     * @return mixed
     */
    private function mapCustoms($customer, $customer_post, $type)
    {
        $fields  = array();
        $customs = array();

        //get fields form db
        $custom_fields = $this->getCustoms();

        // make fields array
        if (!empty( $custom_fields )) {
            foreach ($custom_fields as $cf) {
                if ($type == 'export') {
                    if (!empty( $customer_post['custom_field'] ) &&
                        in_array($cf['custom_value'], array_keys($customer_post['custom_field']))
                    ) {
                        $fields[$cf['custom_value']] = $customer_post['custom_field'][$cf['custom_value']];
                    }
                } else {
                    if ($cf['active_custom'] == 'yes') {
                        $fields[$cf['custom_value']] = $cf['custom_name'];
                    }
                }
            }
        }

        if (is_object($customer)) {
            $customer = get_object_vars($customer);
        }

        // default reference custom
        $customs['ref'] = 'Prestashop - ' . Configuration::get('PS_SHOP_NAME');

        // for fields from DB
        if (!empty( $fields )) {
            foreach ($fields as $field_key => $field_value) {
                $fv = $field_value;
                //compose address custom field
                if ($field_key == 'address1') {
                    $address_name = $field_value;
                }

                // for POST actions (export or update (order))
                if (!empty( $customer_post )) {
                    if ($type != 'order' &&!empty( $customer_post[$field_key] )) {
                        $fv = $customer_post[$field_key];
                        //update address custom field
                        $address_name = !empty( $customer_post['address1'] ) ? $customer_post['address1'] : null;
                    }
                }

                // allowed custom and non empty
                if (in_array($field_key, array_keys($customer)) == true &&
                    (!empty($fv) && !empty($customer[$field_key]))
                ) {
                    // validation for custom field name
                    if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', Tools::stripslashes(( $fv )))) {
                        return array('custom_error' => 'true', 'custom_message' => $fv);
                    }

                    if ($field_key == 'birthday' && $customer['birthday'] == '0000-00-00') {
                        continue;
                    }

                    // compose address value address+address2
                    if ($fv == $address_name) {
                        $address2 = !empty( $customer['address2'] ) ? ' ' . $customer['address2'] : '';

                        $customs[$address_name] = $customer['address1'] . $address2;
                    } else {
                        $customs[$field_value] = $customer[$field_key];
                    }
                }
            }
        }

        return $customs;
    }

    /**
     * Add (or update) contact to gr campaign depending on action and apply automation rules
     *
     * @param array $params
     * @param       $campaign_id
     * @param       $action
     * @param int   $cycle_day
     *
     * @return mixed
     */
    // TODO: implementacja uzytkownikow GR360 - wtedy bedzie trzeba przekazywac apikey i api_url
    public function addSubscriber($params, $campaign_id, $action, $cycle_day)
    {
        $allowed = array('order', 'create');
        $prefix  = 'customer';

        //add_contact
        if (!empty( $action ) && in_array($action, $allowed) == true && $action == 'create') {
            if (isset($params['newNewsletterContact'])) {
                $prefix = 'newNewsletterContact';
            } else {
                $prefix  = 'newCustomer';
            }
            $customs = $this->mapCustoms($params[$prefix], null, 'create');

            if (isset( $params[$prefix]->newsletter ) && $params[$prefix]->newsletter == 1) {
                $this->addContact(
                    $campaign_id,
                    $params[$prefix]->firstname,
                    $params[$prefix]->lastname,
                    $params[$prefix]->email,
                    $cycle_day,
                    $customs
                );
            }
        } else {
            //update_contact
            $contact = $this->getContacts($params[$prefix]->email, null);
            $customs = $this->mapCustoms($contact, $_POST, 'order');

            // automation
            if (!empty( $params['order']->product_list )) {
                $categories = array();
                foreach ($params['order']->product_list as $products) {
                    $temp_categories = Product::getProductCategories($products['id_product']);
                    foreach ($temp_categories as $tmp) {
                        $categories[$tmp] = $tmp;
                    }
                }

                $automations = $this->getAutomationSettings('active');
                if (!empty( $automations )) {
                    $default = false;
                    foreach ($automations as $automation) {
                        if (in_array($automation['category_id'], $categories)) {
                            // do automation
                            if ($automation['action'] == 'move') {
                                $this->moveContactToGr(
                                    $automation['campaign_id'],
                                    $params[$prefix]->firstname,
                                    $params[$prefix]->lastname,
                                    $params[$prefix]->email,
                                    $customs,
                                    $cycle_day
                                );
                            } elseif ($automation['action'] == 'copy') {
                                $this->addContact(
                                    $automation['campaign_id'],
                                    $params[$prefix]->firstname,
                                    $params[$prefix]->lastname,
                                    $params[$prefix]->email,
                                    $cycle_day,
                                    $customs
                                );
                            }
                        } else {
                            $default = true;
                        }
                    }

                    if ($default === true && isset( $params[$prefix]->newsletter ) &&
                        $params[$prefix]->newsletter == 1) {
                        $this->addContact(
                            $campaign_id,
                            $params[$prefix]->firstname,
                            $params[$prefix]->lastname,
                            $params[$prefix]->email,
                            $cycle_day,
                            $customs
                        );
                    }
                } else {
                    if (isset( $params[$prefix]->newsletter ) && $params[$prefix]->newsletter == 1) {
                        $this->addContact(
                            $campaign_id,
                            $params[$prefix]->firstname,
                            $params[$prefix]->lastname,
                            $params[$prefix]->email,
                            $cycle_day,
                            $customs
                        );
                    }
                }
            } else {
                if (isset( $params[$prefix]->newsletter ) && $params[$prefix]->newsletter == 1) {
                    $this->addContact(
                        $campaign_id,
                        $params[$prefix]->firstname,
                        $params[$prefix]->lastname,
                        $params[$prefix]->email,
                        $cycle_day,
                        $customs
                    );
                }
            }
        }

        return true;
    }

    /**
     * first delete contact from all campaigns then move contact to new one
     *
     * @param       $new_campaign
     * @param       $firstname
     * @param       $lastname
     * @param       $email
     * @param int   $cycle_day
     * @param array $user_customs
     *
     * @return mixed
     */
    public function moveContactToGr($new_campaign_id, $firstname, $lastname, $email, $customs, $cycle_day = 0)
    {
        // required params
        if (empty( $this->api_key )) {
            return false;
        }

        $contacts_id = (array) $this->grApiInstance->getContacts(array(
            'query' => array(
                'email' => $email
            )
        ));

        if (!empty($contacts_id) && isset($contacts_id[0]->contactId)) {
            foreach ($contacts_id as $contact) {
                try {
                    $this->grApiInstance->deleteContact($contact->contactId);
                } catch (Exception $e) {
                    return true;
                }
            }

            return $this->addContact($new_campaign_id, $firstname, $lastname, $email, $cycle_day, $customs);
        }
    }

    /**
     * Add (or update) contact to gr campaign
     *
     * @param       $campaign
     * @param       $firstname
     * @param       $lastname
     * @param       $email
     * @param int   $cycle_day
     * @param array $user_customs
     *
     * @return mixed
     */
    public function addContact($campaign, $firstname, $lastname, $email, $cycle_day = 0, $user_customs = array())
    {
        $name = trim($firstname) . ' ' . trim($lastname);

        $params = array(
            'name'       => $name,
            'email'      => $email,
            'dayOfCycle' => (int) $cycle_day,
            'campaign'   => array('campaignId' => $campaign),
            'ipAddress'  => $_SERVER['REMOTE_ADDR'],
            'consumer_key' => 'nKNAoE'
        );

        $this->all_custom_fields = $this->getCustomFields();

        $user_customs['origin'] = 'prestashop';

        $results = (array) $this->grApiInstance->getContacts(array(
            'query' => array(
                'email'      => $email,
                'campaignId' => $campaign
            )
        ));

        $contact = array_pop($results);

        // if contact already exists in gr account
        if (!empty($contact) && isset($contact->contactId)) {
            $results = $this->grApiInstance->getContact($contact->contactId);
            if (!empty($results->customFieldValues)) {
                $params['customFieldValues'] = $this->mergeUserCustoms($results->customFieldValues, $user_customs);
            }
            return $this->grApiInstance->updateContact($contact->contactId, $params);
        } else {
            $params['customFieldValues'] = $this->setCustoms($user_customs);
            return $this->grApiInstance->addContact($params);
        }
    }

    /**
     * Merge user custom fields selected on WP admin site with those from gr account
     * @param $results
     * @param $user_customs
     *
     * @return array
     */
    public function mergeUserCustoms($results, $user_customs)
    {
        $custom_fields = array();

        if (is_array($results)) {
            foreach ($results as $customs) {
                $value = $customs->value;
                if (in_array($customs->name, array_keys($user_customs))) {
                    $value = array($user_customs[$customs->name]);
                    unset($user_customs[$customs->name]);
                }

                $custom_fields[] = array(
                    'customFieldId' => $customs->customFieldId,
                    'value'         => $value
                );
            }
        }

        return array_merge($custom_fields, $this->setCustoms($user_customs));
    }

    /**
     * Set user custom fields
     * @param $user_customs
     *
     * @return array
     */
    public function setCustoms($user_customs)
    {
        $custom_fields = array();

        if (empty($user_customs)) {
            return $custom_fields;
        }

        foreach ($user_customs as $name => $value) {
            // if custom field is already created on gr account set new value
            if (in_array($name, array_keys($this->all_custom_fields))) {
                $custom_fields[] = array(
                    'customFieldId' => $this->all_custom_fields[$name],
                    'value'         => array($value)
                );
            } else {
                $custom = $this->grApiInstance->addCustomField(array(
                    'name'   => $name,
                    'type'   => "text",
                    'hidden' => "false",
                    'values' => array($value),
                ));

                if (!empty($custom) && !empty($custom->customFieldId)) {
                    $custom_fields[] = array(
                        'customFieldId' => $custom->customFieldId,
                        'value'         => array($value)
                    );
                }
            }
        }

        return $custom_fields;
    }

    /**
     * Get all user custom fields from gr account
     * @return array
     */
    public function getCustomFields()
    {
        $all_customs = array();
        $results     = $this->grApiInstance->getCustomFields();
        if (!empty($results)) {
            foreach ($results as $ac) {
                if (isset($ac->name) && isset($ac->customFieldId)) {
                    $all_customs[$ac->name] = $ac->customFieldId;
                }
            }
        }

        return $all_customs;
    }
}
