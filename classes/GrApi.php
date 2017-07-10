<?php
/**
 * Class Api
 *
 * @author Getresponse <grintegrations@getresponse.com>
 * @copyright GetResponse
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class GrApi
{
    const SMB_PREFIX = 'gr';
    const MX_PL_PREFIX = '360pl';
    const MX_US_PREFIX = '360en';
    const ORIGIN_NAME = 'prestashop';
    const SMB_API_URL = 'https://api.getresponse.com/v3';
    const MX_PL_API_URL = 'https://api3.getresponse360.pl/v3';
    const MX_US_API_URL = 'https://api3.getresponse360.com/v3';

    /** @var GetResponseAPI3 */
    private $api;

    /**
     * @param string $api_key
     * @param string $account_type
     * @param string $domain
     */
    public function __construct($api_key, $account_type, $domain)
    {
        $this->api = new GetResponseAPI3(
            $api_key,
            $this->getApiUrl($account_type),
            $domain
        );
    }

    /**
     * @return bool
     */
    public function checkConnection()
    {
        $result = $this->api->ping();
        return isset($result->accountId) ? true : false;
    }

    /**
     * @return array
     */
    public function getCampaigns()
    {
        $campaigns = array();

        try {
            $results = $this->api->getCampaigns();

            if (empty($results)) {
                return $campaigns;
            }

            foreach ($results as $info) {
                $campaigns[$info->name] = array(
                    'id'   => $info->campaignId,
                    'name' => $info->name
                );
            }

            ksort($campaigns);
            return $campaigns;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * @return array
     */
    public function getWebForms()
    {
        $webForms = array();

        try {
            $results = $this->api->getWebForms();

            if (empty($results)) {
                return $webForms;
            }

            foreach ($results as $id => $info) {
                $webForms[$id] = $info;
            }
            return $webForms;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * @return array
     */
    public function getForms()
    {
        $forms = array();

        try {
            $results = $this->api->getForms();

            if (empty($results)) {
                return $forms;
            }

            foreach ($results as $id => $info) {
                $forms[$id] = $info;
            }
            return $forms;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * @param string $lang
     * @return array
     */
    public function getSubscriptionConfirmationsSubject($lang = 'EN')
    {
        $subjects = array();

        try {
            $results = $this->api->getSubscriptionConfirmationsSubject($lang);

            if (empty($results)) {
                return array();
            }

            foreach ($results as $subject) {
                $subjects[] = array(
                    'id' => $subject->subscriptionConfirmationSubjectId,
                    'name' => $subject->subject
                );
            }
            return $subjects;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * @param string $lang
     * @return array
     */
    public function getSubscriptionConfirmationsBody($lang = 'EN')
    {
        $bodies = array();

        try {
            $results = $this->api->getSubscriptionConfirmationsBody($lang);

            if (empty($results)) {
                return array();
            }

            foreach ($results as $body) {
                $bodies[] = array(
                    'id'            => $body->subscriptionConfirmationBodyId,
                    'name'          => $body->name,
                    'contentPlain'  => $body->contentPlain
                );
            }
            return $bodies;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * @return array
     */
    public function getFromFields()
    {
        $fromFields = array();

        try {
            $results = $this->api->getAccountFromFields();
            if (empty($results)) {
                return array();
            }

            foreach ($results as $info) {
                $fromFields[] = array(
                    'id'    => $info->fromFieldId,
                    'name'  => $info->name,
                    'email' => $info->email,
                );
            }

            return $fromFields;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * @return array
     */
    public function getAutoResponders()
    {
        try {
            return $this->api->getAutoresponders();
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * First delete contact from all campaigns then move contact to new one
     *
     * @param int $new_campaign_id
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @param array $customs
     * @param int $cycle_day
     * @return bool
     */
    public function moveContactToGr($new_campaign_id, $first_name, $last_name, $email, $customs, $cycle_day = 0)
    {
        $contacts_id = (array) $this->api->getContacts(array(
            'query' => array('email' => $email)
        ));

        if (empty($contacts_id) || false === isset($contacts_id[0]->contactId)) {
            return false;
        }

        foreach ($contacts_id as $contact) {
            try {
                $this->api->deleteContact($contact->contactId);
            } catch (Exception $e) {
                return true;
            }
        }

        $this->addContact($new_campaign_id, $first_name, $last_name, $email, $cycle_day, $customs);
        return true;
    }

    /**
     * Add (or update) contact to gr campaign
     *
     * @param string $campaign
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @param int $cycle_day
     * @param array $user_customs
     *
     * @return mixed
     */
    public function addContact($campaign, $first_name, $last_name, $email, $cycle_day, $user_customs = array())
    {
        $name = trim($first_name) . ' ' . trim($last_name);

        $params = array(
            'email'      => $email,
            'campaign'   => array('campaignId' => $campaign),
            'ipAddress'  => $_SERVER['REMOTE_ADDR'],
        );

        $trimmedName = trim($name);

        if (!empty($trimmedName)) {
            $params['name'] = $name;
        }

        if (!empty($cycle_day)) {
            $params['dayOfCycle'] = $cycle_day;
        }

        $user_customs['origin'] = self::ORIGIN_NAME;

        $results = (array) $this->api->getContacts(array(
            'query' => array(
                'email'      => $email,
                'campaignId' => $campaign
            ),
            'additionalFlags' => 'exactMatch'
        ));

        $contact = array_pop($results);

        // if contact already exists in gr account
        if (!empty($contact) && isset($contact->contactId)) {
            $results = $this->api->getContact($contact->contactId);
            if (!empty($results->customFieldValues)) {
                $params['customFieldValues'] = $this->mergeUserCustoms($results->customFieldValues, $user_customs);
            }
            return $this->api->updateContact($contact->contactId, $params);
        } else {
            // @TODO - method setCustoms shouldn't return any values
            $params['customFieldValues'] = $this->transformCustomToGetResponseFormat($user_customs);
            return $this->api->addContact($params);
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

        return array_merge($custom_fields, $this->transformCustomToGetResponseFormat($user_customs));
    }

    /**
     * Set user custom fields
     * @param $user_customs
     *
     * @TODO - this method shouldn't return any values.
     *
     * @return array
     */
    public function transformCustomToGetResponseFormat($user_customs)
    {
        $custom_fields = array();

        if (empty($user_customs)) {
            return $custom_fields;
        }

        foreach ($user_customs as $name => $value) {
            if (in_array($name, array('firstname', 'lastname', 'email'))) {
                continue;
            }

            $gr_custom = $this->api->searchCustomFieldByName($name);

            if (!empty($gr_custom)) {
                $custom_fields[] = array(
                    'customFieldId' => $gr_custom->customFieldId,
                    'value'         => array($value)
                );
            } else {
                $custom = $this->api->addCustomField(array(
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
        $results     = $this->api->getCustomFields();
        if (!empty($results)) {
            foreach ($results as $ac) {
                if (isset($ac->name) && isset($ac->customFieldId)) {
                    $all_customs[$ac->name] = $ac->customFieldId;
                }
            }
        }
        return $all_customs;
    }

    /**
     * @param array $params
     * @return object
     */
    public function createCampaign($params)
    {
        return $this->api->createCampaign($params);
    }

    /**
     * Map custom fields from DB and $_POST
     *
     * @param array $customer
     * @param array $customer_post
     * @param array $custom_fields
     * @param string $type
     * @return mixed
     */
    public function mapCustoms($customer, $customer_post, $custom_fields, $type)
    {
        $fields  = array();
        $customs = array();
        $address_name = '';

        // make fields array
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $cf) {
                if ($type == 'export') {
                    if (!empty($customer_post['custom_field']) &&
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

        // for fields from DB
        if (!empty($fields)) {
            foreach ($fields as $field_key => $field_value) {
                $fv = $field_value;
                //compose address custom field
                if ($field_key == 'address1') {
                    $address_name = $field_value;
                }

                // for POST actions (export or update (order))
                if (!empty($customer_post)) {
                    if ($type != 'order' &&!empty($customer_post[$field_key])) {
                        $fv = $customer_post[$field_key];
                        //update address custom field
                        $address_name = !empty($customer_post['address1']) ? $customer_post['address1'] : null;
                    }
                }

                // allowed custom and non empty
                if (in_array($field_key, array_keys($customer)) == true
                    && (!empty($fv) && !empty($customer[$field_key]))) {
                    // validation for custom field name
                    if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', Tools::stripslashes(($fv)))) {
                        return array('custom_error' => 'true', 'custom_message' => $fv);
                    }

                    if ($field_key == 'birthday' && $customer['birthday'] == '0000-00-00') {
                        continue;
                    }

                    // compose address value address+address2
                    if ($fv == $address_name) {
                        $address2 = !empty($customer['address2']) ? ' ' . $customer['address2'] : '';

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
     * @param string $type
     * @return string
     * @throws GrApiException
     */
    private function getApiUrl($type)
    {
        switch ($type) {
            case (self::MX_PL_PREFIX):
                return self::MX_PL_API_URL;
            case (self::MX_US_PREFIX):
                return self::MX_US_API_URL;
            case (self::SMB_PREFIX):
                return self::SMB_API_URL;
            default:
                throw GrApiException::createForIncorrectApiTypeException();
        }
    }
}
