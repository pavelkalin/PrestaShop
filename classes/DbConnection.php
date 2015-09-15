<?php
/**
 * This module integrate GetResponse and PrestaShop Allows subscribe via checkout page and export your contacts.
 *
 *  @author    Grzegorz Struczynski <gstruczynski@getresponse.com>
 *  @copyright GetResponse
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

require_once( _PS_MODULE_DIR_ . '/getresponse/classes/jsonRPCClient.php' );

/**
 * Class is used to calls to the PrestaShop Database
 *
 * Functions Create, Update, Insert
 * @uses Database instance [ie DB::getInstance()]
 */
class DbConnection
{

    public function __construct($database)
    {
        $this->db  = $database;
        $this->obj = 1;

        $context       = Context::getContext();
        $this->id_shop = $context->shop->id;

        //db prefix
        $this->prefix_settings   = _DB_PREFIX_ . 'getresponse_settings';
        $this->prefix_webform    = _DB_PREFIX_ . 'getresponse_webform';
        $this->prefix_automation = _DB_PREFIX_ . 'getresponse_automation';
        $this->prefix_customs    = _DB_PREFIX_ . 'getresponse_customs';
    }

    /******************************************************************/
    /** Get Methods ***************************************************/
    /******************************************************************/

    public function getSettings()
    {
        $sql = 'SELECT
                    *
                FROM
                    ' . $this->prefix_settings . '
                WHERE
                    id_shop = ' . $this->id_shop . '
                ';

        if ($results = $this->db->ExecuteS($sql)) {
            return $results[0];
        }
    }

    public function getWebformSettings()
    {
        $sql = 'SELECT
                    webform_id, active_subscription, sidebar, style, url
                FROM
                    ' . $this->prefix_webform . '
                WHERE
                    id_shop = ' . $this->id_shop . '
                ';

        if ($results = $this->db->ExecuteS($sql)) {
            return $results[0];
        }
    }

    public function getCampaigns($api_key, $api_url)
    {
        if (empty( $api_key )) {
            return array();
        }

        try {
            $client  = new JsonRpcClient($api_url);
            $results = $client->get_campaigns($api_key);
            if (!empty( $results )) {
                $campaigns = array();
                foreach ($results as $id => $info) {
                    $name             = isset( $info['name'] ) ? $info['name'] : $info['description'];
                    $campaigns[$name] = array(
                        'id'   => $id,
                        'name' => $name,
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

    public function getWebforms($api_key, $api_url)
    {
        if (empty( $api_key )) {
            return array();
        }

        try {
            $client  = new JsonRpcClient($api_url);
            $results = $client->get_webforms($api_key, array('campaign' => array()));
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

    public function getFromFields($api_key, $api_url)
    {
        if (empty( $api_key )) {
            return false;
        }

        $fromfields = array();

        try {
            $client  = new JsonRpcClient($api_url);
            $results = $client->get_account_from_fields($api_key);
            if (!empty( $results )) {
                foreach ($results as $id => $info) {
                    $fromfields[] = array(
                        'id'    => $id,
                        'name'  => $info['name'],
                        'email' => $info['email'],
                    );
                }
            }

            return $fromfields;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getConfirmationSubjects($api_key, $api_url)
    {
        if (empty( $api_key )) {
            return false;
        }

        $subjects = array();

        try {
            $client  = new JsonRpcClient($api_url);
            $results = $client->get_confirmation_subjects($api_key);
            if (!empty( $results )) {
                foreach ($results as $id => $info) {
                    $subjects[] = array(
                        'id'            => $id,
                        'content'       => $info['content'],
                        'language_code' => $info['language_code'],
                    );
                }
            }

            return $subjects;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getConfirmationBodies($api_key, $api_url)
    {
        if (empty( $api_key )) {
            return false;
        }

        $bodies = array();

        try {
            $client  = new JsonRpcClient($api_url);
            $results = $client->get_confirmation_bodies($api_key);
            if (!empty( $results )) {
                foreach ($results as $id => $info) {
                    $bodies[] = array(
                        'id'            => $id,
                        'plain'         => $info['plain'],
                        'html'          => $info['html'],
                        'language_code' => $info['language_code'],
                    );
                }
            }

            return $bodies;
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
        $where = !empty( $email ) ? " AND cu.email = '" . $email . "'" : null;

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
                        id_shop = ' . $this->id_shop . '
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
                    cu.id_shop = ' . $this->id_shop . '
                AND
                    ad.active = 1
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
                ' . _DB_PREFIX_ . 'order_detail od ON (od.id_order = o.id_order AND o.id_shop = ' . $this->id_shop . ')
            LEFT JOIN
                ' . _DB_PREFIX_ . 'category_product cp ON (cp.id_product = od.product_id AND od.id_shop = ' .
               $this->id_shop . ')
            LEFT JOIN
                ' . _DB_PREFIX_ . 'category_lang cl ON (cl.id_category = cp.id_category AND cl.id_shop = ' .
               $this->id_shop . ' AND cl.id_lang = cu.id_lang)
            WHERE
                    cu.newsletter = 1' . $where . '
                AND
                    cu.id_shop = ' . $this->id_shop . '
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
        $where = !empty( $default ) ? " AND `default` = '" . $default . "'" : null;

        $sql = 'SELECT
                    *
                FROM
                    ' . $this->prefix_customs . '
                WHERE
                    id_shop = ' . $this->id_shop . $where;

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
                    ' . $this->prefix_automation . '
                WHERE
                    id_shop = ' . $this->id_shop . $where_status;

        if ($results = $this->db->ExecuteS($sql)) {
            return $results;
        }
    }

    public function getCycleDay($api_key, $api_url)
    {
        if (empty( $api_key )) {
            return array();
        }

        try {
            $client  = new JsonRpcClient($api_url);
            $results = $client->get_messages($api_key, array('type' => 'autoresponder'));

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
        $data = array('api_key' => pSQL($apikey), 'account_type' => pSQL($account_type), 'crypto' => pSQL($crypto));

        return (bool) $this->db->autoExecute($this->prefix_settings, $data, 'UPDATE', 'id_shop = ' . $this->id_shop);
    }

    public function updateWebformSettings($webform_id, $active_subscription, $sidebar, $style, $url)
    {
        $data = array(
            'webform_id'          => pSQL($webform_id),
            'active_subscription' => pSQL($active_subscription),
            'sidebar'             => pSQL($sidebar),
            'style'               => pSQL($style),
            'url'                 => pSQL($url)
        );

        return (bool) $this->db->autoExecute($this->prefix_webform, $data, 'UPDATE', 'id_shop = ' . $this->id_shop);
    }

    public function updateWebformSubscription($active_subscription)
    {
        $data = array('active_subscription' => pSQL($active_subscription));

        return (bool) $this->db->autoExecute($this->prefix_webform, $data, 'UPDATE', 'id_shop = ' . $this->id_shop);
    }

    public function updateSettings($active_subscription, $campaign_id, $update_address, $cycle_day)
    {
        $data = array(
            'active_subscription' => pSQL($active_subscription),
            'campaign_id'         => pSQL($campaign_id),
            'update_address'      => pSQL($update_address),
            'cycle_day'           => pSQL($cycle_day)
        );

        return (bool) $this->db->autoExecute($this->prefix_settings, $data, 'UPDATE', 'id_shop = ' . $this->id_shop);
    }

    public function updateSettingsSubscription($active_subscription)
    {
        $data = array('active_subscription' => pSQL($active_subscription));

        return (bool) $this->db->autoExecute($this->prefix_settings, $data, 'UPDATE', 'id_shop = ' . $this->id_shop);
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
                                    ' . $this->prefix_customs . '
                                SET
                                    custom_name = "' . pSQL($customs[$a]) . '",
                                    active_custom = "yes"
                                WHERE
                                    id_shop = "' . $this->id_shop . '"
                                AND
                                    custom_value = "' . pSQL($a) . '"';

                        $this->db->Execute($sql);
                    } elseif ($allowed[$a]['default'] != 'yes') {
                        $sql = 'UPDATE
                                    ' . $this->prefix_customs . '
                                SET
                                    active_custom = "no"
                                WHERE
                                    id_shop = "' . $this->id_shop . '"
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
                                ' . $this->prefix_customs . '
                            SET
                                active_custom = "no"
                            WHERE
                                id_shop = "' . $this->id_shop . '"
                            AND
                                custom_value = "' . pSQL($sc['custom_value']) . '"';

                    $this->db->Execute($sql);
                }
            }
        }
    }

    public function updateAutomationSettings($category_id, $automation_to_edit, $campaign_id, $action, $cycle_day)
    {
        $data = array(
            'category_id' => pSQL($category_id),
            'campaign_id' => pSQL($campaign_id),
            'action'      => pSQL($action),
            'cycle_day'   => pSQL($cycle_day)
        );

        return (bool) $this->db->autoExecute($this->prefix_automation, $data, 'UPDATE', 'id = ' . $automation_to_edit);
    }

    public function updateAutomationStatus($status, $id)
    {
        $data = array('active' => pSQL($status));

        return (bool) $this->db->autoExecute(
            $this->prefix_automation,
            $data,
            'UPDATE',
            'id_shop = ' . $this->id_shop . ' AND id = ' . $id
        );
    }

    /******************************************************************/
    /** Insert Methods ************************************************/
    /******************************************************************/

    public function insertAutomationSettings($category_id, $campaign_id, $action, $cycle_day)
    {
        $data = array(
            'category_id' => pSQL($category_id),
            'campaign_id' => pSQL($campaign_id),
            'action'      => pSQL($action),
            'cycle_day'   => pSQL($cycle_day),
            'id_shop'     => $this->id_shop,
            'active'      => 'yes'
        );

        try {
            $this->db->autoExecute($this->prefix_automation, $data, 'INSERT');
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /******************************************************************/
    /** Delete Methods ************************************************/
    /******************************************************************/

    public function deleteAutomationSettings($automation_id)
    {
        $sql = 'DELETE FROM `' . $this->prefix_automation . '` WHERE `id` = ' . (int) $automation_id;

        return (bool) $this->db->execute($sql);
    }

    /******************************************************************/
    /** API Methods *****************************&*********************/
    /******************************************************************/

    public function exportSubscriber($api_key, $api_url, $campaign_id, $customers, $cycle_day)
    {
        if (empty( $_POST )) {
            return array('status' => '0', 'message' => 'Request error');
        }

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
                $r = $this->addContactToGR(
                    $api_key,
                    $api_url,
                    $campaign_id,
                    $customer['firstname'],
                    $customer['lastname'],
                    $customer['email'],
                    $customs,
                    $cycle_day
                );

                if (!empty( $r['contact_error'] ) && $r['contact_error'] == true) {
                    if (preg_match('[Invalid email syntax]', $r['contact_message'])) {
                        return array('status' => '0', 'message' => 'Error - Invalid email syntax');
                    }
                    if (preg_match('[Missing campaign]', $r['contact_message'])) {
                        return array('status' => '0', 'message' => 'Error - Missing campaign');
                    }
                    if (preg_match('[Invalid param]', $r['contact_message'])) {
                        return array('status' => '0', 'message' => 'Error - Invalid param');
                    }
                }
            }
        }

        return array('status' => '1', 'message' => 'Export completed.');
    }

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

        // default reference custom
        $customs[] = array(
            'name'    => 'ref',
            'content' => 'prestashop'
        );

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

                    // compose address value address+address2
                    if ($fv == 'address1' && !empty($address_name)) {
                        $address2 = !empty( $customer['address2'] ) ? ' ' . $customer['address2'] : '';

                        $customs[] = array(
                            'name'    => $address_name,
                            'content' => $customer['address1'] . $address2
                        );
                    } else {
                        $customs[] = array(
                            'name'    => $field_value,
                            'content' => $customer[$field_key]
                        );
                    }
                }
            }
        }

        return $customs;
    }

    public function addSubscriber($params, $apikey, $api_url, $campaign_id, $action, $cycle_day)
    {
        $allowed = array('order', 'create');
        $prefix  = 'customer';

        //add_contact
        if (!empty( $action ) && in_array($action, $allowed) == true && $action == 'create') {
            $prefix  = 'newCustomer';
            $customs = $this->mapCustoms($params[$prefix], null, 'create');

            if (isset( $params[$prefix]->newsletter ) && $params[$prefix]->newsletter == 1) {
                $this->addContactToGR(
                    $apikey,
                    $api_url,
                    $campaign_id,
                    $params[$prefix]->firstname,
                    $params[$prefix]->lastname,
                    $params[$prefix]->email,
                    $customs,
                    $cycle_day
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
                    $categories[] = $products['id_category_default'];
                }

                $automations = $this->getAutomationSettings('active');
                if (!empty( $automations )) {
                    $default = false;
                    foreach ($automations as $autmation) {
                        if (in_array($autmation['category_id'], $categories)) {
                            // do automation
                            if ($autmation['action'] == 'move') {
                                $this->moveContactToGr(
                                    $apikey,
                                    $api_url,
                                    $campaign_id,
                                    $autmation['campaign_id'],
                                    $params[$prefix]->email
                                );
                            } elseif ($autmation['action'] == 'copy') {
                                $this->addContactToGR(
                                    $apikey,
                                    $api_url,
                                    $campaign_id,
                                    $params[$prefix]->firstname,
                                    $params[$prefix]->lastname,
                                    $params[$prefix]->email,
                                    $customs,
                                    $cycle_day
                                );
                            }
                        } else {
                            $default = true;
                        }
                    }

                    if ($default === true && isset( $params[$prefix]->newsletter ) &&
                        $params[$prefix]->newsletter == 1) {
                        $this->addContactToGR(
                            $apikey,
                            $api_url,
                            $campaign_id,
                            $params[$prefix]->firstname,
                            $params[$prefix]->lastname,
                            $params[$prefix]->email,
                            $customs,
                            $cycle_day
                        );
                    }
                } else {
                    if (isset( $params[$prefix]->newsletter ) && $params[$prefix]->newsletter == 1) {
                        $this->addContactToGR(
                            $apikey,
                            $api_url,
                            $campaign_id,
                            $params[$prefix]->firstname,
                            $params[$prefix]->lastname,
                            $params[$prefix]->email,
                            $customs,
                            $cycle_day
                        );
                    }
                }
            } else {
                if (isset( $params[$prefix]->newsletter ) && $params[$prefix]->newsletter == 1) {
                    $this->addContactToGR(
                        $apikey,
                        $api_url,
                        $campaign_id,
                        $params[$prefix]->firstname,
                        $params[$prefix]->lastname,
                        $params[$prefix]->email,
                        $customs,
                        $cycle_day
                    );
                }
            }
        }

        return true;
    }

    public function addContactToGR(
        $api_key,
        $api_url,
        $campaign_id,
        $first_name,
        $last_name,
        $email,
        $customs,
        $cycle_day
    ) {
        // required params
        if (empty( $campaign_id ) || empty( $email )) {
            return false;
        }

        try {
            $client = new JsonRpcClient($api_url);

            $name = (!empty($first_name) || !empty($last_name)) ? $first_name . ' ' . $last_name : 'Friend';

            $params = array(
                'campaign'     => $campaign_id,
                'name'         => trim($name),
                'email'        => $email,
                'consumer_key' => 'nKNAoE', // do not change
                'customs'      => $customs
            );

            if (!empty( $cycle_day )) {
                $params['cycle_day'] = $cycle_day;
            }

            $result = $client->add_contact($api_key, $params);

            return $result;
        } catch (Exception $e) {
            // if contact is already added to target campaign - update cutom fields
            if (preg_match('[Contact already added to target campaign]', $e->getMessage())) {
                $contact_id = $this->getContactFromGr($api_key, $api_url, $email, $campaign_id);
                $this->updateGrContact($api_key, $api_url, $contact_id, $customs);
            } else {
                return array('contact_error' => 'true', 'contact_message' => $e->getMessage());
            }
        }
    }

    public function moveContactToGr($api_key, $api_url, $current_campaign_id, $new_campaign_id, $email)
    {
        // required params
        if (empty( $api_key ) || empty( $api_url )) {
            return false;
        }

        $contact_id = $this->getContactFromGr($api_key, $api_url, $email, $current_campaign_id);

        if (!empty( $contact_id ) && is_array($contact_id)) {
            foreach (array_keys($contact_id) as $k) {
                try {
                    $client = new JsonRpcClient($api_url);
                    $params = array(
                        'contact'  => $k,
                        'campaign' => $new_campaign_id
                    );

                    $result = $client->move_contact($api_key, $params);

                    return $result;
                } catch (Exception $e) {
                    return true;
                }
            }
        }
    }

    public function getContactFromGr($api_key, $api_url, $contact_email, $campaign_id)
    {
        // required params
        if (empty( $api_key ) || empty( $api_url )) {
            return false;
        }

        try {
            $client = new JsonRpcClient($api_url);

            $result = $client->get_contacts(
                $api_key,
                array(
                    'email'     => array('EQUALS' => $contact_email),
                    'campaigns' => array($campaign_id),
                )
            );

            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function updateGrContact($api_key, $api_url, $contact_id, $customs)
    {
        // required params
        if (empty( $api_key ) || empty( $api_url )) {
            return false;
        }

        $contat_key = array_keys($contact_id);
        $contact_id = array_pop($contat_key);

        if (empty( $contact_id )) {
            return false;
        }

        try {
            $client = new JsonRpcClient($api_url);

            $result = $client->set_contact_customs(
                $api_key,
                array(
                    'contact' => $contact_id,
                    'customs' => $customs
                )
            );

            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
