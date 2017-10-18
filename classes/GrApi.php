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
     * @param string $apiKey
     * @param string $accountType
     * @param string $domain
     */
    public function __construct($apiKey, $accountType, $domain)
    {
        $this->api = new GetResponseAPI3(
            $apiKey,
            $this->getApiUrl($accountType),
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
                $campaigns[$info->name] = array('id'   => $info->campaignId, 'name' => $info->name);
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
                    'id' => $body->subscriptionConfirmationBodyId,
                    'name' => $body->name,
                    'contentPlain' => $body->contentPlain
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

    public function getAccounts()
    {
        return $this->api->accounts();
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
     * @param int $newCampaignId
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param array $customs
     * @param int $cycleDay
     * @return bool
     */
    public function moveContactToGr($newCampaignId, $firstName, $lastName, $email, $customs, $cycleDay = 0)
    {
        $contactsId = (array) $this->api->getContacts(array(
            'query' => array('email' => $email)
        ));

        if (empty($contactsId) || false === isset($contactsId[0]->contactId)) {
            return false;
        }

        foreach ($contactsId as $contact) {
            try {
                $this->api->deleteContact($contact->contactId);
            } catch (Exception $e) {
                return true;
            }
        }

        $this->addContact($newCampaignId, $firstName, $lastName, $email, $cycleDay, $customs);
        return true;
    }

    /**
     * Add (or update) contact to gr campaign
     *
     * @param string $campaign
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param int $cycleDay
     * @param array $userCustoms
     *
     * @return mixed
     */
    public function addContact($campaign, $firstName, $lastName, $email, $cycleDay, $userCustoms = array())
    {
        $name = trim($firstName) . ' ' . trim($lastName);

        $params = array(
            'email' => $email,
            'campaign' => array('campaignId' => $campaign),
            'ipAddress' => $_SERVER['REMOTE_ADDR'],
        );

        $trimmedName = trim($name);

        if (!empty($trimmedName)) {
            $params['name'] = $name;
        }

        if (is_numeric($cycleDay)) {
            $params['dayOfCycle'] = $cycleDay;
        }

        $userCustoms['origin'] = self::ORIGIN_NAME;

        $results = (array) $this->api->getContacts(array(
            'query' => array('email' => $email, 'campaignId' => $campaign),
            'additionalFlags' => 'exactMatch'
        ));

        $contact = array_pop($results);

        // if contact already exists in gr account
        if (!empty($contact) && isset($contact->contactId)) {
            $results = $this->api->getContact($contact->contactId);
            if (!empty($results->customFieldValues)) {
                $params['customFieldValues'] = $this->mergeUserCustoms($results->customFieldValues, $userCustoms);
            }
            return $this->api->updateContact($contact->contactId, $params);
        } else {
            // @TODO - method setCustoms shouldn't return any values
            $params['customFieldValues'] = $this->transformCustomToGetResponseFormat($userCustoms);
            return $this->api->addContact($params);
        }
    }

    /**
     * Merge user custom fields selected on WP admin site with those from gr account
     * @param $results
     * @param $userCustoms
     *
     * @return array
     */
    public function mergeUserCustoms($results, $userCustoms)
    {
        $customFields = array();

        if (is_array($results)) {
            foreach ($results as $customs) {
                $value = $customs->value;
                if (in_array($customs->name, array_keys($userCustoms))) {
                    $value = array($userCustoms[$customs->name]);
                    unset($userCustoms[$customs->name]);
                }

                $customFields[] = array(
                    'customFieldId' => $customs->customFieldId,
                    'value' => $value
                );
            }
        }

        return array_merge($customFields, $this->transformCustomToGetResponseFormat($userCustoms));
    }

    /**
     * Set user custom fields
     * @param $userCustoms
     *
     * @TODO - this method shouldn't return any values.
     *
     * @return array
     */
    public function transformCustomToGetResponseFormat($userCustoms)
    {
        $customFields = array();

        if (empty($userCustoms)) {
            return $customFields;
        }

        foreach ($userCustoms as $name => $value) {
            if (in_array($name, array('firstname', 'lastname', 'email'))) {
                continue;
            }

            $grCustom = $this->api->searchCustomFieldByName($name);

            if (!empty($grCustom)) {
                $customFields[] = array(
                    'customFieldId' => $grCustom->customFieldId,
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
                    $customFields[] = array(
                        'customFieldId' => $custom->customFieldId,
                        'value'         => array($value)
                    );
                }
            }
        }

        return $customFields;
    }

    /**
     * Get all user custom fields from gr account
     * @return array
     */
    public function getCustomFields()
    {
        $allCustoms = array();
        $results = $this->api->getCustomFields();
        if (!empty($results)) {
            foreach ($results as $ac) {
                if (isset($ac->name) && isset($ac->customFieldId)) {
                    $allCustoms[$ac->name] = $ac->customFieldId;
                }
            }
        }
        return $allCustoms;
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
     * @param string $shopName
     * @param string $locale
     * @param string $currency
     *
     * @return mixed
     */
    public function createShop($shopName, $locale, $currency)
    {
        return $this->api->createShop($shopName, $locale, $currency);
    }

    /**
     * @param string $shopId
     * @return mixed
     */
    public function deleteShop($shopId)
    {
        return $this->api->deleteShop($shopId);
    }

    /**
     * @return array
     */
    public function getShops()
    {
        $shops = $this->api->getShops();

        return empty((array)$shops) ? array() : $shops;
    }

    /**
     * @param string $shopId
     * @param string $cartId
     * @param array $params
     * @return mixed
     */
    public function updateCart($shopId, $cartId, $params)
    {
        return $this->api->updateCart($shopId, $cartId, $params);
    }

    /**
     * @param string $shopId
     * @param string $cartId
     * @return mixed
     */
    public function deleteCart($shopId, $cartId)
    {
        return $this->api->deleteCart($shopId, $cartId);
    }

    /**
     * @param string $shopId
     * @param array $params
     * @return mixed
     */
    public function addProduct($shopId, $params)
    {
        return $this->api->addProduct($shopId, $params);
    }

    /**
     * @param string $shopId
     * @param array $params
     * @return array
     */
    public function addCart($shopId, $params)
    {
        return $this->api->addCart($shopId, $params);
    }

    /**
     * @param string $email
     * @param string $campaignId
     * @return array
     */
    public function getContactByEmail($email, $campaignId)
    {
        $params = array('query' =>
            array('email' => $email, 'campaignId' => $campaignId)
        );

        $response = (array) $this->api->getContacts($params);

        return array_pop($response);
    }

    /**
     * @param string $shopId
     * @param array $params
     * @return array
     */
    public function createOrder($shopId, $params)
    {
        return $this->api->createOrder($shopId, $params);
    }

    /**
     * @param string $shopId
     * @param string $orderId
     * @param array $params
     * @return array
     */
    public function updateOrder($shopId, $orderId, $params)
    {
        return $this->api->updateOrder($shopId, $orderId, $params);
    }

    /**
     * Return features list
     * @return mixed
     */
    public function getFeatures()
    {
        return $this->api->getFeatures();
    }

    /**
     * Return features list
     * @return mixed
     */
    public function getTrackingCode()
    {
        return $this->api->getTrackingCode();
    }

    /**
     * Map custom fields from DB and $_POST
     *
     * @param array $customer
     * @param array $customerPost
     * @param array $customFields
     * @param string $type
     * @return mixed
     */
    public function mapCustoms($customer, $customerPost, $customFields, $type)
    {
        $fields  = array();
        $customs = array();
        $addressName = '';

        // make fields array
        if (!empty($customFields)) {
            foreach ($customFields as $cf) {
                if ($type == 'export') {
                    if (!empty($customerPost['custom_field']) &&
                        in_array($cf['custom_value'], array_keys($customerPost['custom_field']))
                    ) {
                        $fields[$cf['custom_value']] = $customerPost['custom_field'][$cf['custom_value']];
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
            foreach ($fields as $fieldKey => $fieldValue) {
                $fv = $fieldValue;
                //compose address custom field
                if ($fieldKey == 'address1') {
                    $addressName = $fieldValue;
                }

                // for POST actions (export or update (order))
                if (!empty($customerPost)) {
                    if ($type != 'order' &&!empty($customerPost[$fieldKey])) {
                        $fv = $customerPost[$fieldKey];
                        //update address custom field
                        $addressName = !empty($customerPost['address1']) ? $customerPost['address1'] : null;
                    }
                }

                // allowed custom and non empty
                if (in_array($fieldKey, array_keys($customer)) == true
                    && (!empty($fv) && !empty($customer[$fieldKey]))) {
                    // validation for custom field name
                    if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', Tools::stripslashes(($fv)))) {
                        return array('custom_error' => 'true', 'custom_message' => $fv);
                    }

                    if ($fieldKey == 'birthday' && $customer['birthday'] == '0000-00-00') {
                        continue;
                    }

                    // compose address value address+address2
                    if ($fv == $addressName) {
                        $address2 = !empty($customer['address2']) ? ' ' . $customer['address2'] : '';

                        $customs[$addressName] = $customer['address1'] . $address2;
                    } else {
                        $customs[$fieldValue] = $customer[$fieldKey];
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
