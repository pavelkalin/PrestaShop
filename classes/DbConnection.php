<?php
/**
 * Class DbConnection
 *
 * @author Getresponse <grintegrations@getresponse.com>
 * @copyright GetResponse
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class DbConnection
{
    /** @var Db */
    private $db;

    /** @var int */
    private $idShop;

    /**
     * @param Db $db
     * @param int $shopId
     */
    public function __construct($db, $shopId)
    {
        $this->db = $db;
        $this->idShop = $shopId;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $sql = '
        SELECT
            `id`,
            `id_shop`,
            `api_key`,
            `active_subscription`,
            `active_newsletter_subscription`,
            `active_tracking`,
            `tracking_snippet`,
            `update_address`,
            `campaign_id`,
            `cycle_day`,
            `account_type`,
            `crypto`
        FROM
            ' . _DB_PREFIX_ . 'getresponse_settings
        WHERE
            `id_shop` = ' . (int) $this->idShop;

        if ($results = $this->db->ExecuteS($sql)) {
            return $results[0];
        }

        return array();
    }

    /**
     * @return array
     */
    public function getWebformSettings()
    {
        $sql = '
        SELECT
            `webform_id`, 
            `active_subscription`, 
            `sidebar`, 
            `style`, 
            `url`
        FROM
            ' . _DB_PREFIX_ . 'getresponse_webform
        WHERE
            `id_shop` = ' . (int) $this->idShop;

        if ($results = $this->db->ExecuteS($sql)) {
            return $results[0];
        }

        return array();
    }

    /**
     * @param string $email
     * @param string $idCampaign
     * @return bool|string
     */
    public function getGrSubscriberId($email, $idCampaign)
    {
        $sql = '
        SELECT
            `gr_id_user`
        FROM
            ' . _DB_PREFIX_ . 'getresponse_subscribers
        WHERE
            `email` = "' . pSQL($email) . '"
            AND `id_campaign` = "' . pSQL($idCampaign) . '"';


        if ($results = $this->db->ExecuteS($sql)) {
            if (isset($results[0]['gr_id_user'])) {
                return $results[0]['gr_id_user'];
            }
        }

        return false;
    }

    /**
     * @param string $email
     * @param string $idCampaign
     * @param string $grIdUser
     */
    public function setGrSubscriberId($email, $idCampaign, $grIdUser)
    {
        $query = '
            INSERT IGNORE INTO 
                ' . _DB_PREFIX_ . 'getresponse_subscribers
            SET
                `email` = "' . pSQL($email) . '",
                `id_campaign` = "' . pSQL($idCampaign) . '",
                `gr_id_user` = "' . pSQL($grIdUser) . '"
        ';

        $this->db->execute($query);
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function checkModuleStatus($moduleName)
    {
        if (empty($moduleName)) {
            return false;
        }

        $sql = '
        SELECT
            `active`
        FROM
            ' . _DB_PREFIX_ . 'module
        WHERE
            `name` = "' . pSQL($moduleName) . '"';

        if ($results = $this->db->ExecuteS($sql)) {
            if (isset($results[0]['active']) && 1 === (int) $results[0]['active']) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param bool $newsletterGuests
     * @return array
     */
    public function getContacts($newsletterGuests = false)
    {
        if (version_compare(_PS_VERSION_, '1.7') === -1) {
            $newsletterTableName = _DB_PREFIX_ . 'newsletter';
            $newsletterModule = 'blocknewsletter';
        } else {
            $newsletterTableName = _DB_PREFIX_ . 'emailsubscription';
            $newsletterModule = _DB_PREFIX_ . 'emailsubscription';
        }
        $ngWhere = '';

        if ($newsletterGuests && $this->checkModuleStatus($newsletterModule)) {
            $ngWhere = 'UNION SELECT
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
                    "" as country
                FROM
                    ' . $newsletterTableName . ' n
                WHERE
                    n.active = 1
                AND
                    id_shop = ' . (int) $this->idShop . '
            ';
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
                    co.iso_code as country
                FROM
                    ' . _DB_PREFIX_ . 'customer as cu
                LEFT JOIN
                    ' . _DB_PREFIX_ . 'address ad ON cu.id_customer = ad.id_customer
                LEFT JOIN
                    ' . _DB_PREFIX_ . 'country co ON ad.id_country = co.id_country
                WHERE
                    cu.newsletter = 1
                AND
                    cu.id_shop = ' . (int) $this->idShop . '
                    GROUP BY cu.email
                ' . $ngWhere;

        $contacts = $this->db->ExecuteS($sql);

        if (empty($contacts)) {
            return array();
        }

        foreach ($contacts as $id => $contact) {
            $contacts[$id]['category'] = $this->getContactCategory($contact['email']);
        }
        return $contacts;
    }

    /**
     * @param string $email
     * @return array
     */
    public function getContactByEmail($email)
    {
        $sql = '
        SELECT
            cu.`firstname`,
            cu.`lastname`,
            cu.`email`,
            cu.`company`,
            cu.`birthday`,
            ad.`address1`,
            ad.`address2`,
            ad.`postcode`,
            ad.`city`,
            ad.`phone`,
            co.`iso_code` as country
        FROM
            ' . _DB_PREFIX_ . 'customer as cu
        LEFT JOIN
            ' . _DB_PREFIX_ . 'address ad ON cu.`id_customer` = ad.`id_customer`
        LEFT JOIN
            ' . _DB_PREFIX_ . 'country co ON ad.`id_country` = co.`id_country`
        WHERE
            cu.`newsletter` = 1
            AND cu.`email` = "' . pSQL($email) . '"
            AND cu.`id_shop` = ' . (int) $this->idShop;

        $contacts = $this->db->ExecuteS($sql);

        if (empty($contacts)) {
            return array();
        }

        $contact = $contacts[0];
        $contact['category'] = $this->getContactCategory($contact['email']);
        return $contact;
    }

    /**
     * @return array
     */
    public function getCustoms()
    {
        $sql = '
        SELECT
            `id_custom`,
            `id_shop`,
            `custom_field`,
            `custom_value`,
            `custom_name`,
            `default`,
            `active_custom`
        FROM
            ' . _DB_PREFIX_ . 'getresponse_customs
        WHERE
            id_shop = ' . (int) $this->idShop;

        if ($results = $this->db->ExecuteS($sql)) {
            return $results;
        }

        return array();
    }

    /**
     * @param bool $isActive
     * @return array
     */
    public function getAutomationSettings($isActive = false)
    {
        $sql = '
        SELECT
            `id`, `id_shop`, `category_id`, `campaign_id`, `action`, `cycle_day`, `active`
        FROM
            ' .  _DB_PREFIX_ . 'getresponse_automation
        WHERE
            id_shop = ' . (int) $this->idShop;

        if ($isActive) {
            $sql .= ' AND `active` = "yes"';
        }

        if ($results = $this->db->ExecuteS($sql)) {
            return $results;
        }

        return array();
    }

    /**
     * @param string $apiKey
     * @param string $accountType
     * @param string $crypto
     */
    public function updateApiSettings($apiKey, $accountType, $crypto)
    {
        $query = '
        UPDATE 
            ' .  _DB_PREFIX_ . 'getresponse_settings 
        SET
            `api_key` = "' . pSQL($apiKey) . '",
            `account_type` = "' . pSQL($accountType) . '",
            `crypto` = "' . pSQL($crypto) . '"
         WHERE
            `id_shop` = ' . (int) $this->idShop;

        $this->db->execute($query);
    }

    /**
     * @param int $webformId
     * @param string $activeSubscription
     * @param string $sidebar
     * @param string $style
     * @param string $url
     */
    public function updateWebformSettings($webformId, $activeSubscription, $sidebar, $style, $url)
    {
        $query = '
        UPDATE 
            ' . _DB_PREFIX_ . 'getresponse_webform
        SET
            `webform_id` = "' . pSQL($webformId) . '",
            `active_subscription` = "' . pSQL($activeSubscription) . '",
            `sidebar` = "' . pSQL($sidebar) . '",
            `style` = "' . pSQL($style) . '",
            `url` = "' . pSQL($url) . '"
        WHERE
            `id_shop` = ' . (int) $this->idShop;

        $this->db->execute($query);
    }

    /**
     * @param string $activeSubscription
     */
    public function updateWebformSubscription($activeSubscription)
    {
        $query = '
        UPDATE 
            ' . _DB_PREFIX_ . 'getresponse_webform 
        SET
            `active_subscription` = "' . pSQL($activeSubscription) . '"
        WHERE
            `id_shop` = ' . (int) $this->idShop;

        $this->db->execute($query);
    }

    /**
     * @param $activeTracking
     * @param $snippet
     */
    public function updateTracking($activeTracking, $snippet)
    {
        $query = '
        UPDATE 
            ' . _DB_PREFIX_ . 'getresponse_settings
        SET
            `active_tracking` = "' . pSQL($activeTracking) . '",
            `tracking_snippet` = "' . pSQL($snippet, true) . '"
        WHERE
            `id_shop` = ' . (int) $this->idShop;

        $this->db->execute($query);
    }

    /**
     * @param string $activeSubscription
     * @param string $campaignId
     * @param string $updateAddress
     * @param string $cycleDay
     * @param string $newsletter
     */
    public function updateSettings($activeSubscription, $campaignId, $updateAddress, $cycleDay, $newsletter)
    {
        $query = '
        UPDATE 
            ' .  _DB_PREFIX_ . 'getresponse_settings 
        SET
            `active_subscription` = "' . pSQL($activeSubscription) . '",
            `active_newsletter_subscription` = "' . pSQL($newsletter) . '",
            `campaign_id` = "' . pSQL($campaignId) . '",
            `update_address` = "' . pSQL($updateAddress) . '",
            `cycle_day` = "' . pSQL($cycleDay) . '"
        WHERE
            `id_shop` = ' . (int) $this->idShop;

        $this->db->execute($query);
    }

    /**
     * @param string $activeSubscription
     */
    public function updateSettingsSubscription($activeSubscription)
    {
        $query = '
        UPDATE 
            ' . _DB_PREFIX_ . 'getresponse_settings 
        SET
            `active_subscription` = "' . pSQL($activeSubscription) . '"
        WHERE
            `id_shop` = ' . (int) $this->idShop;

        $this->db->execute($query);
    }


    /**
     * @param string $activeEcommerce
     */
    public function updateEcommerceSubscription($activeEcommerce)
    {
        if ($activeEcommerce === 'yes') {
            $query = '
                INSERT INTO 
                    ' . _DB_PREFIX_ . 'getresponse_ecommerce 
                SET
                    `id_shop` = ' . (int) $this->idShop . '
            ';
        } else {
            $query = '
                DELETE FROM
                    ' . _DB_PREFIX_ . 'getresponse_ecommerce 
                WHERE
                    `id_shop` = ' . (int) $this->idShop;
        }

        $this->db->execute($query);
    }

    /**
     * @return array|bool
     */
    public function getEcommerceSettings()
    {
        $sql = 'SELECT * FROM
                    ' . _DB_PREFIX_ . 'getresponse_ecommerce 
                WHERE
                    `id_shop` = ' . (int) $this->idShop;

        return $this->db->getRow($sql);
    }

    /**
     * @param string $shopId
     */
    public function updateEcommerceShopId($shopId)
    {
        $query = '
            UPDATE
                ' . _DB_PREFIX_ . 'getresponse_ecommerce 
            SET
                `gr_id_shop` = "' . $shopId . '"
            WHERE
                `id_shop` = ' . (int) $this->idShop;

        $this->db->execute($query);
    }

    /**
     * @param $cartId
     * @return string
     */
    public function getGetResponseCartMD5($cartId)
    {
        $sql = 'SELECT `cart_hash` FROM
                    ' . _DB_PREFIX_ . 'cart 
                WHERE
                    `id_cart` = ' . (int) $cartId;

        return $this->db->getValue($sql);
    }

    /**
     * @param int $cartId
     * @param string $hash
     * @return bool
     */
    public function updateGetResponseCartMD5($cartId, $hash)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'cart 
                SET
                    `cart_hash` = "' . $hash . '"
                WHERE
                    `id_cart` = ' . (int) $cartId;

        return $this->db->execute($sql);
    }

    /**
     * @return string
     */
    public function getGetResponseShopId()
    {
        $sql = 'SELECT `gr_id_shop`
                FROM
                    ' . _DB_PREFIX_ . 'getresponse_ecommerce 
                WHERE
                    `id_shop` = ' . (int) $this->idShop;

        return $this->db->getValue($sql);
    }

    /**
     * @param $cartId
     * @return string
     */
    public function getGetResponseCartId($cartId)
    {
        $sql = 'SELECT `gr_id_cart` FROM
                    ' . _DB_PREFIX_ . 'cart 
                WHERE
                    `id_cart` = ' . (int) $cartId;

        return $this->db->getValue($sql);
    }

    /**
     * @param string $cartId
     * @param int $id
     * @return bool
     */
    public function updateGetResponseCartId($cartId, $id)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'cart 
                SET
                    `gr_id_cart` = "' . $id . '"
                WHERE
                    `id_cart` = ' . (int) $cartId;

        return $this->db->execute($sql);
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getGetResponseOrderId($orderId)
    {
        $sql = 'SELECT `gr_id_order` FROM
                    ' . _DB_PREFIX_ . 'orders 
                WHERE
                    `id_order` = ' . (int) $orderId;

        return $this->db->getValue($sql);
    }

    /**
     * @param int $orderId
     * @param string $id
     * @return bool
     */
    public function updateGetResponseOrderId($orderId, $id)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'orders
                SET
                    `gr_id_order` = "' . $id . '"
                WHERE
                    `id_order` = ' . (int) $orderId;

        return $this->db->execute($sql);
    }

    /**
     * @param int $idProduct
     * @return string
     */
    public function getGetResponseProductId($idProduct)
    {
        $sql = 'SELECT `gr_id_product` FROM
                    ' . _DB_PREFIX_ . 'getresponse_products 
                WHERE
                    `id_product` = ' . (int) $idProduct;

        return $this->db->getValue($sql);
    }

    /**
     * @param int $idProduct
     * @param string $grIdProduct
     * @return bool
     */
    public function updateGetResponseProductId($idProduct, $grIdProduct)
    {
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'getresponse_products 
                SET
                    `gr_id_product` = "' . $grIdProduct . '",
                    `id_product` = ' . (int) $idProduct;

        return $this->db->execute($sql);
    }

    /**
     * @param array $customs
     */
    public function updateCustomsWithPostedData($customs)
    {
        $settingsCustoms = $this->getCustoms();
        if (empty($settingsCustoms) || empty($customs)) {
            return;
        }

        $allowed = array();
        foreach ($settingsCustoms as $sc) {
            $allowed[$sc['custom_value']] = $sc;
        }

        if (empty($allowed)) {
            return;
        }

        foreach (array_keys($allowed) as $a) {
            if (in_array($a, array_keys($customs))) {
                $sql = '
                UPDATE
                    ' . _DB_PREFIX_ . 'getresponse_customs
                SET
                    `custom_name` = "' . pSQL($customs[$a]) . '",
                    `active_custom` = "yes"
                WHERE
                    `id_shop` = ' . (int) $this->idShop . '
                    AND `custom_value` = "' . pSQL($a) . '"';

                $this->db->Execute($sql);
            } elseif ('yes' !== $allowed[$a]['default']) {
                $sql = '
                UPDATE
                    ' . _DB_PREFIX_ . 'getresponse_customs
                SET
                    `active_custom` = "no"
                WHERE
                    `id_shop` = ' . (int) $this->idShop . '
                    AND `custom_value` = "' . pSQL($a) . '"';

                $this->db->Execute($sql);
            }
        }
    }

    public function updateCustom($custom)
    {
        $sql = '
                UPDATE
                    ' . _DB_PREFIX_ . 'getresponse_customs
                SET
                    `custom_name` = "' . pSQL($custom['name']) . '",
                    `active_custom` = "' . pSQL($custom['active']) . '"
                WHERE
                    `id_shop` = ' . (int) $this->idShop . '
                    AND `id_custom` = "' . pSQL($custom['id']) . '"';
        $this->db->Execute($sql);
    }

    public function disableCustoms()
    {
        $customs = $this->getCustoms();

        if (empty($customs)) {
            return;
        }

        foreach ($customs as $custom) {
            if ('no' !== $custom['default']) {
                continue;
            }

            $sql = '
            UPDATE
                ' . _DB_PREFIX_ . 'getresponse_customs
            SET
                `active_custom` = "no"
            WHERE
                `id_shop` = ' . (int) $this->idShop . '
                AND `custom_value` = "' . pSQL($custom['custom_value']) . '"';

            $this->db->Execute($sql);
        }
    }

    /**
     * @param int $categoryId
     * @param int $automationId
     * @param int $campaignId
     * @param string $action
     * @param int $cycleDay
     */
    public function updateAutomationSettings($categoryId, $automationId, $campaignId, $action, $cycleDay)
    {
        $query = '
        UPDATE
            ' . _DB_PREFIX_ . 'getresponse_automation
        SET
            `category_id` = "' . pSQL($categoryId) . '",
            `campaign_id` = "' . pSQL($campaignId). '",
            `action` = "' . pSQL($action) . '",
            `cycle_day` = "' . pSQL($cycleDay) . '"
        WHERE
            `id` = ' . (int)$automationId . '
            AND `id_shop` = ' . (int) $this->idShop;

        $this->db->execute($query);
    }

    /**
     * @param  string $status
     * @param int $id
     */
    public function updateAutomationStatus($status, $id)
    {
        $query = '
        UPDATE 
            ' . _DB_PREFIX_ . 'getresponse_automation
        SET
            `active` = "' . pSQL($status) . '"
        WHERE
            `id_shop` = ' . (int) $this->idShop . ' AND `id` = ' . (int) $id;

        $this->db->execute($query);
    }

    /**
     * @param int $categoryId
     * @param int $campaignId
     * @param string $action
     * @param int $cycleDay
     */
    public function insertAutomationSettings($categoryId, $campaignId, $action, $cycleDay)
    {
        $query = '
        INSERT INTO ' . _DB_PREFIX_ . 'getresponse_automation (
            `category_id`, 
            `campaign_id`, 
            `action`, 
            `cycle_day`, 
            `id_shop`, 
            `active` 
       ) VALUES (
            "' . pSQL($categoryId) . '",
            "' . pSQL($campaignId) . '",
            "' . pSQL($action) . '",
            "' . pSQL($cycleDay) . '",
            "' . (int) $this->idShop . '",
            "yes"
       )';

        try {
            $this->db->execute($query);
        } catch (Exception $e) {
        }
    }

    /**
     * @param int $automationId
     */
    public function deleteAutomationSettings($automationId)
    {
        if (empty($automationId)) {
            return;
        }

        $sql = '
        DELETE FROM 
            ' . _DB_PREFIX_ . 'getresponse_automation 
        WHERE 
            `id` = ' . (int) $automationId;

        $this->db->execute($sql);
    }

    /**
     * @param string $email
     * @return string
     */
    private function getContactCategory($email)
    {
        $sql = '
        SELECT
            group_concat(DISTINCT cp.`id_category` separator ", ") as category
        FROM
            ' . _DB_PREFIX_ . 'customer as cu
        LEFT JOIN
            ' . _DB_PREFIX_ . 'address ad ON cu.`id_customer` = ad.`id_customer`
        LEFT JOIN
            ' . _DB_PREFIX_ . 'country co ON ad.`id_country` = co.`id_country`
        LEFT JOIN
            ' . _DB_PREFIX_ . 'orders o ON o.`id_customer` = cu.`id_customer`
        LEFT JOIN
            ' . _DB_PREFIX_ . 'order_detail od ON (od.`id_order` = o.`id_order` 
            AND o.`id_shop` = ' . (int) $this->idShop . ')
        LEFT JOIN
            ' . _DB_PREFIX_ . 'category_product cp ON (cp.`id_product` = od.`product_id` 
            AND od.`id_shop` = ' . (int) $this->idShop . ')
        LEFT JOIN
            ' . _DB_PREFIX_ . 'category_lang cl ON (cl.`id_category` = cp.`id_category` 
            AND cl.`id_shop` = ' .
            (int) $this->idShop . ' AND cl.`id_lang` = cu.`id_lang`)
        WHERE
            cu.`newsletter` = 1
            AND cu.`email` = "' . pSQL($email) . '"
            AND cu.`id_shop` = ' . (int) $this->idShop;

        $categories = $this->db->ExecuteS($sql);

        if (empty($categories)) {
            return '';
        }
        return $categories[0]['category'];
    }

    public function prepareDatabase()
    {
        $sql = array();

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'getresponse_settings` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`id_shop` char(32) NOT NULL,
			`api_key` char(32) NOT NULL,
			`active_subscription` enum(\'yes\',\'no\') NOT NULL DEFAULT \'no\',
			`active_newsletter_subscription` enum(\'yes\',\'no\') NOT NULL DEFAULT \'no\',
			`active_tracking` enum(\'yes\',\'no\', \'disabled\') NOT NULL DEFAULT \'disabled\',
			`tracking_snippet` text,
			`update_address` enum(\'yes\',\'no\') NOT NULL DEFAULT \'no\',
			`campaign_id` char(5) NOT NULL,
			`cycle_day` char(5) NOT NULL,
			`account_type` enum(\'gr\',\'360en\',\'360pl\') NOT NULL DEFAULT \'gr\',
			`crypto` char(32) NULL,
			PRIMARY KEY (`id`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'getresponse_customs` (
			`id_custom` int(11) NOT NULL AUTO_INCREMENT,
			`id_shop` int(6) NOT NULL,
			`custom_field` char(32) NOT NULL,
			`custom_value` char(32) NOT NULL,
			`custom_name` char(32) NOT NULL,
			`default` enum(\'yes\',\'no\') NOT NULL DEFAULT \'no\',
			`active_custom` enum(\'yes\',\'no\') NOT NULL DEFAULT \'no\',
			PRIMARY KEY (`id_custom`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'getresponse_webform` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`id_shop` int(6) NOT NULL,
			`webform_id` char(32) NOT NULL,
			`active_subscription` enum(\'yes\',\'no\') NOT NULL DEFAULT \'no\',
			`sidebar` enum(\'left\',\'right\',\'header\',\'top\',\'footer\',\'home\') NOT NULL DEFAULT \'home\',
			`style` enum(\'webform\',\'prestashop\') NOT NULL DEFAULT \'webform\',
			`url` varchar(255) DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'getresponse_automation` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`id_shop` int(6) NOT NULL,
			`category_id` char(32) NOT NULL,
			`campaign_id` char(32) NOT NULL,
			`action` char(32) NOT NULL DEFAULT \'move\',
			`cycle_day` char(5) NOT NULL,
			`active` enum(\'yes\',\'no\') NOT NULL DEFAULT \'yes\',
			PRIMARY KEY (`id`),
			UNIQUE KEY `id_shop` (`id_shop`,`category_id`,`campaign_id`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'getresponse_ecommerce` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_shop` int(11) DEFAULT NULL,
            `gr_id_shop` varchar(16) DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'getresponse_products` (
            `id_product` int(11) unsigned NOT NULL,
            `gr_id_product` varchar(32) DEFAULT NULL,
            UNIQUE KEY (`id_product`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'getresponse_subscribers` (
            `id_user` int(11) unsigned NOT NULL,
            `id_campaign` varchar(16) DEFAULT NULL,
            `gr_id_user` varchar(16) DEFAULT NULL,           
            `email` varchar(128) DEFAULT NULL,
            UNIQUE KEY `id_user` (`id_user`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'cart` ADD `cart_hash` varchar(32);';
        $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'cart` ADD `gr_id_cart` varchar(32);';
        $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'orders` ADD `gr_id_order` varchar(32);';

        //multistore
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
            $shops = Shop::getShops();

            if (!empty($shops) && is_array($shops)) {
                foreach ($shops as $shop) {
                    if (empty($shop['id_shop'])) {
                        continue;
                    }
                    $sql[] = $this->sqlMainSetting($shop['id_shop']);
                    $sql[] = $this->sqlWebformSetting($shop['id_shop']);
                    $sql[] = $this->sqlCustomsSetting($shop['id_shop']);
                }
            }
        } else {
            $sql[] = $this->sqlMainSetting('1');
            $sql[] = $this->sqlWebformSetting('1');
            $sql[] = $this->sqlCustomsSetting('1');
        }

        //Install SQL
        foreach ($sql as $s) {
            try {
                Db::getInstance()->Execute($s);
            } catch (Exception $e) {
            }
        }
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    private function sqlMainSetting($storeId)
    {
        return '
        INSERT INTO `' . _DB_PREFIX_ . 'getresponse_settings` (
            `id_shop` ,
            `api_key` ,
            `active_subscription` ,
            `active_newsletter_subscription` ,
            `active_tracking` ,
            `tracking_snippet`,
            `update_address` ,
            `campaign_id` ,
            `cycle_day` ,
            `account_type` ,
            `crypto`
        )
        VALUES (
            ' . (int) $storeId . ', \'\', \'no\', \'no\', \'no\', \'\', \'no\', \'0\', \' \', \'gr\', \'\'
        )
        ON DUPLICATE KEY UPDATE `id` = `id`;';
    }

    /**
     * @param int $storeId
     * @return string
     */
    private function sqlWebformSetting($storeId)
    {
        return '
        INSERT INTO  `' . _DB_PREFIX_ . 'getresponse_webform` (
            `id_shop` ,
            `webform_id` ,
            `active_subscription` ,
            `sidebar`,
            `style`
        )
        VALUES (
            ' . (int) $storeId . ',  \'\',  \'no\',  \'left\',  \'webform\'
        )
        ON DUPLICATE KEY UPDATE `id` = `id`;';
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    private function sqlCustomsSetting($storeId)
    {
        return '
        INSERT INTO `' . _DB_PREFIX_ . 'getresponse_customs` (
            `id_shop` ,
            `custom_field`,
            `custom_value`,
            `custom_name`,
            `default`,
            `active_custom`
        )
        VALUES
            (' . (int) $storeId . ', \'firstname\', \'firstname\', \'firstname\', \'yes\', \'yes\'),
            (' . (int) $storeId . ', \'lastname\', \'lastname\', \'lastname\', \'yes\', \'yes\'),
            (' . (int) $storeId . ', \'email\', \'email\', \'email\', \'yes\', \'yes\'),
            (' . (int) $storeId . ', \'address\', \'address1\', \'address\', \'no\', \'no\'),
            (' . (int) $storeId . ', \'postal\', \'postcode\', \'postal\', \'no\', \'no\'),
            (' . (int) $storeId . ', \'city\', \'city\', \'city\', \'no\', \'no\'),
            (' . (int) $storeId . ', \'phone\', \'phone\', \'phone\', \'no\', \'no\'),
            (' . (int) $storeId . ', \'country\', \'country\', \'country\', \'no\', \'no\'),
            (' . (int) $storeId . ', \'birthday\', \'birthday\', \'birthday\', \'no\', \'no\'),
            (' . (int) $storeId . ', \'company\', \'company\', \'company\', \'no\', \'no\'),
            (' . (int) $storeId . ', \'category\', \'category\', \'category\', \'no\', \'no\');';
    }

    public function clearDatabase()
    {
        $this->db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'getresponse_settings`;');
        $this->db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'getresponse_customs`;');
        $this->db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'getresponse_webform`;');
        $this->db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'getresponse_automation`;');
        $this->db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'getresponse_ecommerce`;');
        $this->db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'getresponse_products`;');
        $this->db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'getresponse_subscribers`;');
    }
}
