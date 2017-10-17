<?php
/**
 * Class GetResponseAPI3
 *
 * @author Getresponse <grintegrations@getresponse.com>
 * @copyright GetResponse
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class GetResponseAPI3
{
    const X_APP_ID = '2cd8a6dc-003f-4bc3-ba55-c2e4be6f7500';

    /** @var string */
    private $apiKey;

    /** @var string */
    private $apiUrl;

    /** @var int */
    private $timeout = 8;

    /** @var string */
    private $domain;

    /**
     * @param string $apiKey
     * @param string $apiUrl
     * @param string $domain
     */
    public function __construct($apiKey, $apiUrl, $domain)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->domain = $domain;
    }

    /**
     * get account details
     *
     * @return mixed
     */
    public function accounts()
    {
        return $this->call('accounts');
    }

    /**
     * @return mixed
     */
    public function ping()
    {
        return $this->accounts();
    }

    /**
     * Return all campaigns
     * @return mixed
     */
    public function getCampaigns()
    {
        return $this->call('campaigns');
    }

    /**
     * get single campaign
     * @param string $campaignId retrieved using API
     * @return mixed
     */
    public function getCampaign($campaignId)
    {
        return $this->call('campaigns/' . $campaignId);
    }

    /**
     * adding campaign
     * @param $params
     * @return mixed
     */
    public function createCampaign($params)
    {
        return $this->call('campaigns', 'POST', $params);
    }

    /**
     * list all RSS newsletters
     * @return mixed
     */
    public function getRSSNewsletters()
    {
        return $this->call('rss-newsletters', 'GET', null);
    }

    /**
     * @param string $lang
     * @return mixed
     */
    public function getSubscriptionConfirmationsSubject($lang = 'EN')
    {
        return $this->call('subscription-confirmations/subject/' . $lang);
    }

    /**
     * @param string $lang
     * @return mixed
     */
    public function getSubscriptionConfirmationsBody($lang = 'EN')
    {
        return $this->call('subscription-confirmations/body/' . $lang);
    }

    /**
     * send one newsletter
     *
     * @param $params
     * @return mixed
     */
    public function sendNewsletter($params)
    {
        return $this->call('newsletters', 'POST', $params);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function sendDraftNewsletter($params)
    {
        return $this->call('newsletters/send-draft', 'POST', $params);
    }

    /**
     * add single contact into your campaign
     *
     * @param $params
     * @return mixed
     */
    public function addContact($params)
    {
        return $this->call('contacts', 'POST', $params);
    }

    /**
     * retrieving contact by id
     *
     * @param string $contactId - contact id obtained by API
     * @return mixed
     */
    public function getContact($contactId)
    {
        return $this->call('contacts/' . $contactId);
    }

    /**
     * get contact activities
     * @param $contactId
     */
    public function getContactActivities($contactId)
    {
        $this->call('contacts/' . $contactId . '/activities');
    }

    /**
     * retrieving contact by params
     * @param array $params
     *
     * @return mixed
     */
    public function getContacts($params = array())
    {
        return $this->call('contacts?' . $this->setParams($params));
    }

    /**
     * updating any fields of your subscriber (without email of course)
     * @param       $contactId
     * @param array $params
     *
     * @return mixed
     */
    public function updateContact($contactId, $params = array())
    {
        return $this->call('contacts/' . $contactId, 'POST', $params);
    }

    /**
     * drop single user by ID
     *
     * @param string $contactId - obtained by API
     * @return mixed
     */
    public function deleteContact($contactId)
    {
        return $this->call('contacts/' . $contactId, 'DELETE');
    }

    /**
     * retrieve account custom fields
     * @param array $params
     *
     * @return mixed
     */
    public function getCustomFields($params = array())
    {
        return $this->call('custom-fields?' . $this->setParams($params));
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function addCustomField($params = array())
    {
        return $this->call('custom-fields', 'POST', $params);
    }

    /**
     * retrieve account from fields
     * @param array $params
     *
     * @return mixed
     */
    public function getAccountFromFields($params = array())
    {
        return $this->call('from-fields?' . $this->setParams($params));
    }

    /**
     * retrieve autoresponders
     * @param array $params
     *
     * @return mixed
     */
    public function getAutoresponders($params = array())
    {
        return $this->call('autoresponders?' . $this->setParams($params));
    }

    /**
     * add custom field
     *
     * @param $params
     * @return mixed
     */
    public function setCustomField($params)
    {
        return $this->call('custom-fields', 'POST', $params);
    }

    /**
     * @param $customId
     * @return mixed
     */
    public function getCustomField($customId)
    {
        return $this->call('custom-fields/' . $customId, 'GET');
    }

    /**
     * retrieving billing information
     *
     * @return mixed
     */
    public function getBillingInfo()
    {
        return $this->call('accounts/billing');
    }

    /**
     * get single web form
     *
     * @param int $wId
     * @return mixed
     */
    public function getWebForm($wId)
    {
        return $this->call('webforms/' . $wId);
    }

    /**
     * retrieve all webforms
     * @param array $params
     *
     * @return mixed
     */
    public function getWebForms($params = array())
    {
        return $this->call('webforms?' . $this->setParams($params));
    }

    /**
     * get single form
     *
     * @param int $formId
     * @return mixed
     */
    public function getForm($formId)
    {
        return $this->call('forms/' . $formId);
    }

    /**
     * retrieve all forms
     * @param array $params
     *
     * @return mixed
     */
    public function getForms($params = array())
    {
        return $this->call('forms?' . $this->setParams($params));
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
        $params = array(
            'name' => $shopName,
            'locale' => $locale,
            'currency' => $currency
        );
        return $this->call('shops', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @return mixed
     */
    public function deleteShop($shopId)
    {
        return $this->call('shops/' . $shopId, 'DELETE');
    }

    /**
     * @return mixed
     */
    public function getShops()
    {
        return $this->call('shops');
    }

    /**
     * @param string $shopId
     * @param string $cartId
     * @param array $params
     * @return mixed
     */
    public function updateCart($shopId, $cartId, $params)
    {
        return $this->call('shops/' . $shopId . '/carts/' . $cartId, 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param string $cartId
     * @return mixed
     */
    public function deleteCart($shopId, $cartId)
    {
        return $this->call('shops/' . $shopId . '/carts/'.$cartId, 'DELETE');
    }

    /**
     * @param string $shopId
     * @param array $params
     * @return mixed
     */
    public function addProduct($shopId, $params)
    {
        return $this->call('shops/'.$shopId.'/products', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param array $params
     * @return mixed
     */
    public function addCart($shopId, $params)
    {
        return $this->call('shops/'.$shopId.'/carts', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param array $params
     * @return mixed
     */
    public function createOrder($shopId, $params)
    {
        return $this->call('shops/'.$shopId.'/orders', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param string $orderId
     * @param array $params
     * @return mixed
     */
    public function updateOrder($shopId, $orderId, $params)
    {
        return $this->call('shops/'.$shopId.'/orders/'.$orderId, 'POST', $params);
    }

    /**
     * Return features list
     * @return mixed
     */
    public function getFeatures()
    {
        return $this->call('accounts/features');
    }

    /**
     * Return features list
     * @return mixed
     */
    public function getTrackingCode()
    {
        return $this->call('tracking');
    }

    /**
     * Curl run request
     *
     * @param null $apiMethod
     * @param string $httpMethod
     * @param array $params
     * @return mixed
     * @throws GrApiException
     */
    private function call($apiMethod, $httpMethod = 'GET', $params = array())
    {
        if (empty($apiMethod)) {
            throw GrApiException::createForEmptyApiMethod();
        }

        $params = Tools::jsonEncode($params);
        $url = $this->apiUrl  . '/' .  $apiMethod;

        $headers = array(
            'User-Agent: ' . 'PrestaShop/' . _PS_VERSION_,
            'X-APP-ID: ' . self::X_APP_ID,
            'X-Auth-Token: api-key ' . $this->apiKey,
            'Content-Type: application/json'
        );

        if (!empty($this->domain)) {
            $headers[] = 'X-Domain: ' . $this->domain;
        }

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_ENCODING => 'gzip,deflate',
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => 'User-Agent: PHP GetResponse client 0.0.2',
            CURLOPT_HTTPHEADER => $headers
        );

        if ($httpMethod == 'POST') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $params;
        } elseif ($httpMethod == 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $result = curl_exec($curl);

        if (false === $result) {
            throw GrApiException::createForInvalidCurlResponse(curl_error($curl));
        }

        $response = Tools::jsonDecode($result);

        curl_close($curl);
        return (object) $response;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function setParams($params = array())
    {
        $result = array();
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $result[$key] = $value;
            }
        }
        return http_build_query($result);
    }

    /**
     * @param string $name
     *
     * @return null|stdClass
     */
    public function searchCustomFieldByName($name)
    {
        $customs = $this->call('custom-fields?query[name]=' . $name, 'GET');

        if (empty($customs)) {
            return null;
        }

        foreach ($customs as $custom) {
            if ($custom->name === $name) {
                return $custom;
            }
        }
        return null;
    }
}
