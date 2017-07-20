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
    private $id_shop;

    /**
     * @param Db $db
     * @param int $shop_id
     */
    public function __construct($db, $shop_id)
    {
        $this->db = $db;
        $this->id_shop = $shop_id;
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
            `id_shop` = ' . (int) $this->id_shop;

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
            `id_shop` = ' . (int) $this->id_shop;

        if ($results = $this->db->ExecuteS($sql)) {
            return $results[0];
        }

        return array();
    }

    /**
     * @param string $email
     * @param string $id_campaign
     * @return bool|string
     */
    public function getGrSubscriberId($email, $id_campaign)
    {
        $sql = '
        SELECT
            `gr_id_user`
        FROM
            ' . _DB_PREFIX_ . 'getresponse_subscribers
        WHERE
            `email` = "' . pSQL($email) . '"
            AND `id_campaign` = "' . pSQL($id_campaign) . '"';


        if ($results = $this->db->ExecuteS($sql)) {
            if (isset($results[0]['gr_id_user'])) {
                return $results[0]['gr_id_user'];
            }
        }

        return false;
    }

    /**
     * @param string $email
     * @param string $id_campaign
     * @param string $gr_id_user
     */
    public function setGrSubscriberId($email, $id_campaign, $gr_id_user)
    {
        $query = '
            INSERT IGNORE INTO 
                ' . _DB_PREFIX_ . 'getresponse_subscribers
            SET
                `email` = "' . pSQL($email) . '",
                `id_campaign` = "' . pSQL($id_campaign) . '",
                `gr_id_user` = "' . pSQL($gr_id_user) . '"
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
     * @param bool $newsletter_guests
     * @return array
     */
    public function getContacts($newsletter_guests = false)
    {
        if (version_compare(_PS_VERSION_, '1.7') === -1) {
            $newsletter_table_name = _DB_PREFIX_ . 'newsletter';
            $newsletter_module = 'blocknewsletter';
        } else {
            $newsletter_table_name = _DB_PREFIX_ . 'emailsubscription';
            $newsletter_module = _DB_PREFIX_ . 'emailsubscription';
        }
        $ng_where = '';

        if ($newsletter_guests && $this->checkModuleStatus($newsletter_module)) {
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
                    "" as country
                FROM
                    ' . $newsletter_table_name . ' n
                WHERE
                    n.active = 1
                AND
                    id_shop = ' . (int) $this->id_shop . '
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
                    cu.id_shop = ' . (int) $this->id_shop . '
                    GROUP BY cu.email
                ' . $ng_where;

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
            AND cu.`id_shop` = ' . (int) $this->id_shop;

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
            id_shop = ' . (int) $this->id_shop;

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
            id_shop = ' . (int) $this->id_shop;

        if ($isActive) {
            $sql .= ' AND `active` = "yes"';
        }

        if ($results = $this->db->ExecuteS($sql)) {
            return $results;
        }

        return array();
    }

    /**
     * @param string $api_key
     * @param string $account_type
     * @param string $crypto
     */
    public function updateApiSettings($api_key, $account_type, $crypto)
    {
        $query = '
        UPDATE 
            ' .  _DB_PREFIX_ . 'getresponse_settings 
        SET
            `api_key` = "' . pSQL($api_key) . '",
            `account_type` = "' . pSQL($account_type) . '",
            `crypto` = "' . pSQL($crypto) . '"
         WHERE
            `id_shop` = ' . (int) $this->id_shop;

        $this->db->execute($query);
    }

    /**
     * @param int $webform_id
     * @param string $active_subscription
     * @param string $sidebar
     * @param string $style
     * @param string $url
     */
    public function updateWebformSettings($webform_id, $active_subscription, $sidebar, $style, $url)
    {
        $query = '
        UPDATE 
            ' . _DB_PREFIX_ . 'getresponse_webform
        SET
            `webform_id` = "' . pSQL($webform_id) . '",
            `active_subscription` = "' . pSQL($active_subscription) . '",
            `sidebar` = "' . pSQL($sidebar) . '",
            `style` = "' . pSQL($style) . '",
            `url` = "' . pSQL($url) . '"
        WHERE
            `id_shop` = ' . (int) $this->id_shop;

        $this->db->execute($query);
    }

    /**
     * @param string $active_subscription
     */
    public function updateWebformSubscription($active_subscription)
    {
        $query = '
        UPDATE 
            ' . _DB_PREFIX_ . 'getresponse_webform 
        SET
            `active_subscription` = "' . pSQL($active_subscription) . '"
        WHERE
            `id_shop` = ' . (int) $this->id_shop;

        $this->db->execute($query);
    }

    /**
     * @param $active_tracking
     * @param $snippet
     */
    public function updateTracking($active_tracking, $snippet)
    {
        $query = '
        UPDATE 
            ' . _DB_PREFIX_ . 'getresponse_settings
        SET
            `active_tracking` = "' . pSQL($active_tracking) . '",
            `tracking_snippet` = "' . pSQL($snippet, true) . '"
        WHERE
            `id_shop` = ' . (int) $this->id_shop;

        $this->db->execute($query);
    }

    /**
     * @param string $active_subscription
     * @param string $campaign_id
     * @param string $update_address
     * @param string $cycle_day
     * @param string $newsletter
     */
    public function updateSettings($active_subscription, $campaign_id, $update_address, $cycle_day, $newsletter)
    {
        $query = '
        UPDATE 
            ' .  _DB_PREFIX_ . 'getresponse_settings 
        SET
            `active_subscription` = "' . pSQL($active_subscription) . '",
            `active_newsletter_subscription` = "' . pSQL($newsletter) . '",
            `campaign_id` = "' . pSQL($campaign_id) . '",
            `update_address` = "' . pSQL($update_address) . '",
            `cycle_day` = "' . pSQL($cycle_day) . '"
        WHERE
            `id_shop` = ' . (int) $this->id_shop;

        $this->db->execute($query);
    }

    /**
     * @param string $active_subscription
     */
    public function updateSettingsSubscription($active_subscription)
    {
        $query = '
        UPDATE 
            ' . _DB_PREFIX_ . 'getresponse_settings 
        SET
            `active_subscription` = "' . pSQL($active_subscription) . '"
        WHERE
            `id_shop` = ' . (int) $this->id_shop;

        $this->db->execute($query);
    }


    /**
     * @param string $active_ecommerce
     */
    public function updateEcommerceSubscription($active_ecommerce)
    {
        if ($active_ecommerce === 'yes') {
            $query = '
                INSERT INTO 
                    ' . _DB_PREFIX_ . 'getresponse_ecommerce 
                SET
                    `id_shop` = ' . (int) $this->id_shop . '
            ';
        } else {
            $query = '
                DELETE FROM
                    ' . _DB_PREFIX_ . 'getresponse_ecommerce 
                WHERE
                    `id_shop` = ' . (int) $this->id_shop;
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
                    `id_shop` = ' . (int) $this->id_shop;

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
                `id_shop` = ' . (int) $this->id_shop;

        $this->db->execute($query);
    }

    /**
     * @param $cart_id
     * @return string
     */
    public function getGetResponseCartMD5($cart_id)
    {
        $sql = 'SELECT `cart_hash` FROM
                    ' . _DB_PREFIX_ . 'cart 
                WHERE
                    `id_cart` = ' . (int) $cart_id;

        return $this->db->getValue($sql);
    }

    /**
     * @param int $cart_id
     * @param string $hash
     * @return bool
     */
    public function updateGetResponseCartMD5($cart_id, $hash)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'cart 
                SET
                    `cart_hash` = "' . $hash . '"
                WHERE
                    `id_cart` = ' . (int) $cart_id;

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
                    `id_shop` = ' . (int) $this->id_shop;

        return $this->db->getValue($sql);
    }

    /**
     * @param $cart_id
     * @return string
     */
    public function getGetResponseCartId($cart_id)
    {
        $sql = 'SELECT `gr_id_cart` FROM
                    ' . _DB_PREFIX_ . 'cart 
                WHERE
                    `id_cart` = ' . (int) $cart_id;

        return $this->db->getValue($sql);
    }

    /**
     * @param string $cart_id
     * @param int $id
     * @return bool
     */
    public function updateGetResponseCartId($cart_id, $id)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'cart 
                SET
                    `gr_id_cart` = "' . $id . '"
                WHERE
                    `id_cart` = ' . (int) $cart_id;

        return $this->db->execute($sql);
    }

    /**
     * @param int $order_id
     * @return string
     */
    public function getGetResponseOrderId($order_id)
    {
        $sql = 'SELECT `gr_id_order` FROM
                    ' . _DB_PREFIX_ . 'orders 
                WHERE
                    `id_order` = ' . (int) $order_id;

        return $this->db->getValue($sql);
    }

    /**
     * @param int $order_id
     * @param string $id
     * @return bool
     */
    public function updateGetResponseOrderId($order_id, $id)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'orders
                SET
                    `gr_id_order` = "' . $id . '"
                WHERE
                    `id_order` = ' . (int) $order_id;

        return $this->db->execute($sql);
    }

    /**
     * @param int $id_product
     * @return string
     */
    public function getGetResponseProductId($id_product)
    {
        $sql = 'SELECT `gr_id_product` FROM
                    ' . _DB_PREFIX_ . 'getresponse_products 
                WHERE
                    `id_product` = ' . (int) $id_product;

        return $this->db->getValue($sql);
    }

    /**
     * @param int $id_product
     * @param string $gr_id_product
     * @return bool
     */
    public function updateGetResponseProductId($id_product, $gr_id_product)
    {
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'getresponse_products 
                SET
                    `gr_id_product` = "' . $gr_id_product . '",
                    `id_product` = ' . (int) $id_product;

        return $this->db->execute($sql);
    }

    /**
     * @param array $customs
     */
    public function updateCustomsWithPostedData($customs)
    {
        $settings_customs = $this->getCustoms();
        if (empty($settings_customs) || empty($customs)) {
            return;
        }

        $allowed = array();
        foreach ($settings_customs as $sc) {
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
                    `id_shop` = ' . (int) $this->id_shop . '
                    AND `custom_value` = "' . pSQL($a) . '"';

                $this->db->Execute($sql);
            } elseif ('yes' !== $allowed[$a]['default']) {
                $sql = '
                UPDATE
                    ' . _DB_PREFIX_ . 'getresponse_customs
                SET
                    `active_custom` = "no"
                WHERE
                    `id_shop` = ' . (int) $this->id_shop . '
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
                    `id_shop` = ' . (int) $this->id_shop . '
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
                `id_shop` = ' . (int) $this->id_shop . '
                AND `custom_value` = "' . pSQL($custom['custom_value']) . '"';

            $this->db->Execute($sql);
        }
    }

    /**
     * @param int $category_id
     * @param int $automation_id
     * @param int $campaign_id
     * @param string $action
     * @param int $cycle_day
     */
    public function updateAutomationSettings($category_id, $automation_id, $campaign_id, $action, $cycle_day)
    {
        $query = '
        UPDATE
            ' . _DB_PREFIX_ . 'getresponse_automation
        SET
            `category_id` = "' . pSQL($category_id) . '",
            `campaign_id` = "' . pSQL($campaign_id). '",
            `action` = "' . pSQL($action) . '",
            `cycle_day` = "' . pSQL($cycle_day) . '"
        WHERE
            `id` = ' . (int)$automation_id . '
            AND `id_shop` = ' . (int) $this->id_shop;

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
            `id_shop` = ' . (int) $this->id_shop . ' AND `id` = ' . (int) $id;

        $this->db->execute($query);
    }

    /**
     * @param int $category_id
     * @param int $campaign_id
     * @param string $action
     * @param int $cycle_day
     */
    public function insertAutomationSettings($category_id, $campaign_id, $action, $cycle_day)
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
            "' . pSQL($category_id) . '",
            "' . pSQL($campaign_id) . '",
            "' . pSQL($action) . '",
            "' . pSQL($cycle_day) . '",
            "' . (int) $this->id_shop . '",
            "yes"
       )';

        try {
            $this->db->execute($query);
        } catch (Exception $e) {
        }
    }

    /**
     * @param int $automation_id
     */
    public function deleteAutomationSettings($automation_id)
    {
        if (empty($automation_id)) {
            return;
        }

        $sql = '
        DELETE FROM 
            ' . _DB_PREFIX_ . 'getresponse_automation 
        WHERE 
            `id` = ' . (int) $automation_id;

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
            AND o.`id_shop` = ' . (int) $this->id_shop . ')
        LEFT JOIN
            ' . _DB_PREFIX_ . 'category_product cp ON (cp.`id_product` = od.`product_id` 
            AND od.`id_shop` = ' . (int) $this->id_shop . ')
        LEFT JOIN
            ' . _DB_PREFIX_ . 'category_lang cl ON (cl.`id_category` = cp.`id_category` 
            AND cl.`id_shop` = ' .
            (int) $this->id_shop . ' AND cl.`id_lang` = cu.`id_lang`)
        WHERE
            cu.`newsletter` = 1
            AND cu.`email` = "' . pSQL($email) . '"
            AND cu.`id_shop` = ' . (int) $this->id_shop;

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
     * @param int $store_id
     *
     * @return string
     */
    private function sqlMainSetting($store_id)
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
            ' . (int) $store_id . ', \'\', \'no\', \'no\', \'no\', \'\', \'no\', \'0\', \' \', \'gr\', \'\'
        )
        ON DUPLICATE KEY UPDATE `id` = `id`;';
    }

    /**
     * @param int $store_id
     * @return string
     */
    private function sqlWebformSetting($store_id)
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
            ' . (int) $store_id . ',  \'\',  \'no\',  \'left\',  \'webform\'
        )
        ON DUPLICATE KEY UPDATE `id` = `id`;';
    }

    /**
     * @param int $store_id
     *
     * @return string
     */
    private function sqlCustomsSetting($store_id)
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
            (' . (int) $store_id . ', \'firstname\', \'firstname\', \'firstname\', \'yes\', \'yes\'),
            (' . (int) $store_id . ', \'lastname\', \'lastname\', \'lastname\', \'yes\', \'yes\'),
            (' . (int) $store_id . ', \'email\', \'email\', \'email\', \'yes\', \'yes\'),
            (' . (int) $store_id . ', \'address\', \'address1\', \'address\', \'no\', \'no\'),
            (' . (int) $store_id . ', \'postal\', \'postcode\', \'postal\', \'no\', \'no\'),
            (' . (int) $store_id . ', \'city\', \'city\', \'city\', \'no\', \'no\'),
            (' . (int) $store_id . ', \'phone\', \'phone\', \'phone\', \'no\', \'no\'),
            (' . (int) $store_id . ', \'country\', \'country\', \'country\', \'no\', \'no\'),
            (' . (int) $store_id . ', \'birthday\', \'birthday\', \'birthday\', \'no\', \'no\'),
            (' . (int) $store_id . ', \'company\', \'company\', \'company\', \'no\', \'no\'),
            (' . (int) $store_id . ', \'category\', \'category\', \'category\', \'no\', \'no\');';
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
