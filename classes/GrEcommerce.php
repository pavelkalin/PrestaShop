<?php
/**
 * Class GrEcommerce
 *
 *  @author Getresponse <grintegrations@getresponse.com>
 *  @copyright GetResponse
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class GrEcommerce
{
    /** @var GrApi $api */
    private $api;

    /** @var DbConnection $db */
    private $db;

    /**
     * @param DbConnection $db
     */
    public function __construct(DbConnection $db)
    {
        $settings = $db->getSettings();
        $this->api = new GrApi($settings['api_key'], $settings['account_type'], $settings['crypto']);
        $this->db = $db;
    }

    /**
     * @param int $id_cart
     * @param string $gr_id_cart
     * @param string $gr_id_shop
     */
    public function removeCart($id_cart, $gr_id_cart, $gr_id_shop)
    {
        $this->api->deleteCart($gr_id_shop, $gr_id_cart);
        $this->db->updateGetResponseCartId($id_cart, '');
    }

    /**
     * @param array $product
     * @param string $gr_id_shop
     * @return string
     */
    public function createProductInGr($product, $gr_id_shop)
    {
        $ps_product = new Product($product['id_product']);
        $categories = $ps_product->getCategories();
        $product_name = strip_tags((is_array($ps_product->name) ? array_shift($ps_product->name) : $ps_product->name));
        $product_description = strip_tags((is_array($ps_product->description_short) ? array_shift($ps_product->description_short) : $ps_product->description_short));

        $gr_product = array(
            'name' => $product_name,
            'url' => Tools::getHttpHost(true) . __PS_BASE_URI__ . '?controller=product&id_product=' . $ps_product->id,
            'type' => $ps_product->getWsType(),
            'vendor' => $ps_product->getWsManufacturerName(),
            'categories' => array(),
            'variants' => array(
                array(
                    'name' => $product_name,
                    'description' => $product_description,
                    'price'=> floatval($ps_product->price),
                    'priceTax' => 0.00,
                    'sku' => $ps_product->reference
                )
            )
        );

        foreach ($categories as $id_category) {
            $category = new Category($id_category);
            $gr_product['categories'][] = array('name' => $category->getName());
        }

        $response = $this->api->addProduct($gr_id_shop, $gr_product);

        return $this->handleProductResponse($response);
    }

    /**
     * @param array $params
     * @param string $gr_id_contact
     * @param string $gr_id_shop
     * @return array
     */
    public function createOrderObject($params, $gr_id_contact, $gr_id_shop)
    {
        /** @var OrderCore $order */
        $order = $params['order'];
        /** @var CartCore $cart */
        $cart = $params['cart'];

        $order_date = DateTime::createFromFormat('Y-m-d H:i:s', $order->date_add);

        $gr_order = array(
            'contactId' => $gr_id_contact,
            'totalPrice' => $order->total_paid_tax_excl,
            'totalPriceTax' => $order->total_paid_tax_incl - $order->total_paid_tax_excl,
            'currency' => $params['currency']->iso_code,
            'orderUrl' => Tools::getHttpHost(true) . __PS_BASE_URI__ . '?controller=order-detail&id_order=' . $order->id,
            'externalId' => $order->reference,
            "billingAddress" => $this->returnAddressDetails(new Address($order->id_address_invoice)),
            "shippingAddress" => $this->returnAddressDetails(new Address($order->id_address_delivery)),
            "status" => $this->getOrderStatus($order),
            "shippingPrice" => floatval($order->total_shipping),
            "selectedVariants" => $this->returnOrderedVariantsList($order->getProducts(), $gr_id_shop, false),
            "billingStatus" => $this->getOrderStatus($order),
            "processedAt" => $order_date->format('Y-m-d\TH:i:sO')
        );

        $gr_id_cart = $this->db->getGetResponseCartId($cart->id);

        if (!empty($gr_id_cart)) {
            $gr_order['cartId'] = $gr_id_cart;
        }

        return $gr_order;
    }

    /**
     * @param AddressCore $address
     * @return array
     */
    private function returnAddressDetails($address)
    {
        return array(
            "countryCode" => $this->convertCountryCode((new Country($address->id_country))->iso_code),
            "countryName" => $address->country,
            "name" => $address->firstname . ' ' . $address->lastname,
            "firstName" => $address->firstname,
            "lastName" => $address->lastname,
            "address1" => $address->address1,
            "address2" => $address->address2,
            "city" => $address->city,
            "zip" => $address->postcode,
            "phone" => $address->phone,
            "company" => $address->company
        );
    }

    /**
     * @param CartCore $cart
     * @param string $gr_id_shop
     * @param string $gr_id_cart
     * @param string $gr_id_customer
     */
    public function sendCartDataToGR($cart, $gr_id_shop, $gr_id_cart, $gr_id_customer)
    {
        $cart_details = $cart->getSummaryDetails();
        $products = $cart->getProducts(true);
        $params = array(
            'contactId' => $gr_id_customer,
            'currency' => (new Currency($cart->id_currency))->iso_code,
            'totalPrice' => $cart_details['total_price_without_tax'],
            'selectedVariants' => $this->returnOrderedVariantsList($products, $gr_id_shop),
            'externalId' => $cart->id,
            'totalTaxPrice' => $cart_details['total_tax'],
            'cartUrl' => Tools::getHttpHost(true) . __PS_BASE_URI__ . '?controller=order'
        );

        if (empty($gr_id_cart)) {
            $response = $this->api->addCart($gr_id_shop, $params);
            $this->db->updateGetResponseCartId($cart->id, $response->cartId);
        } else {
            $this->api->updateCart($gr_id_shop, $gr_id_cart, $params);
        }
    }

    /**
     * @param string $gr_id_shop
     * @param array $gr_order
     * @param int $id_order
     */
    public function sendOrderDataToGR($gr_id_shop, $gr_order, $id_order)
    {
        $gr_order_id = $this->db->getGetResponseOrderId($id_order);

        if (empty($gr_order_id)) {
            $response = $this->api->createOrder($gr_id_shop, $gr_order);
            if (!empty($response) && !empty($response->orderId)) {
                $this->db->updateGetResponseOrderId($id_order, $response->orderId);
            }
        } else {
            $this->api->updateOrder($gr_id_shop, $gr_order_id, $gr_order);
        }
    }

    /**
     * @param array $products
     * @param string $gr_id_shop
     * @param bool $cart
     * @return array
     */
    private function returnOrderedVariantsList($products, $gr_id_shop, $cart = true)
    {
        $variants = [];

        foreach ($products as $product) {
            if ($cart) {
                $price = $product['price_with_reduction_without_tax'];
                $tax = $product['price_with_reduction'] - $product['price_with_reduction_without_tax'];
                $quantity = $product['quantity'];
            } else {
                $price = $product['unit_price_tax_excl'];
                $tax = $product['unit_price_tax_incl'] - $product['unit_price_tax_excl'];
                $quantity = $product['product_quantity'];
            }

            $variants[] = array(
                'variantId' => $this->getVariantId($product, $gr_id_shop),
                'quantity' => $quantity,
                'price' => $price,
                'priceTax' => $tax
            );
        }

        return $variants;
    }

    /**
     * @param array $product
     * @param string $gr_id_shop
     * @return string
     */
    private function getVariantId($product, $gr_id_shop)
    {
        $id_variant = $this->db->getGetResponseProductId($product['id_product']);

        if (empty($id_variant)) {
            $id_variant = $this->createProductInGr($product, $gr_id_shop);
            $this->db->updateGetResponseProductId($product['id_product'], $id_variant);
        }

        return $id_variant;
    }

    /**
     * @param string $email
     * @param string $id_campaign
     * @param bool $force
     * @return string
     */
    public function getSubscriberId($email, $id_campaign, $force = false)
    {
        $gr_id_user = $this->db->getGrSubscriberId($email, $id_campaign);

        if (empty($gr_id_user) || $force) {
            $gr_contact = $this->api->getContactByEmail($email, $id_campaign);

            if (empty($gr_contact) || !isset($gr_contact->contactId)) {
                return false;
            }

            $this->db->setGrSubscriberId($email, $id_campaign, $gr_contact->contactId);
            return $gr_contact->contactId;
        }

        return $gr_id_user;
    }

    /**
     * @param \stdClass $response
     * @return string
     */
    private function handleProductResponse($response)
    {
        return !isset($response->productId) ? '' : $response->variants[0]->variantId;
    }

    /**
     * @param string $country_code Two letters country code
     * @return string|bool Three letters country code
     */
    function convertCountryCode($country_code)
    {
        $iso_3166_1 = array(
            'AF' => 'AFG',
            'AX' => 'ALA',
            'AL' => 'ALB',
            'DZ' => 'DZA',
            'AS' => 'ASM',
            'AD' => 'AND',
            'AO' => 'AGO',
            'AI' => 'AIA',
            'AQ' => 'ATA',
            'AG' => 'ATG',
            'AR' => 'ARG',
            'AM' => 'ARM',
            'AW' => 'ABW',
            'AU' => 'AUS',
            'AT' => 'AUT',
            'AZ' => 'AZE',
            'BS' => 'BHS',
            'BH' => 'BHR',
            'BD' => 'BGD',
            'BB' => 'BRB',
            'BY' => 'BLR',
            'BE' => 'BEL',
            'BZ' => 'BLZ',
            'BJ' => 'BEN',
            'BM' => 'BMU',
            'BT' => 'BTN',
            'BO' => 'BOL',
            'BQ' => 'BES',
            'BA' => 'BIH',
            'BW' => 'BWA',
            'BV' => 'BVT',
            'BR' => 'BRA',
            'IO' => 'IOT',
            'BN' => 'BRN',
            'BG' => 'BGR',
            'BF' => 'BFA',
            'BI' => 'BDI',
            'KH' => 'KHM',
            'CM' => 'CMR',
            'CA' => 'CAN',
            'CV' => 'CPV',
            'KY' => 'CYM',
            'CF' => 'CAF',
            'TD' => 'TCD',
            'CL' => 'CHL',
            'CN' => 'CHN',
            'CX' => 'CXR',
            'CC' => 'CCK',
            'CO' => 'COL',
            'KM' => 'COM',
            'CG' => 'COG',
            'CD' => 'COD',
            'CK' => 'COK',
            'CR' => 'CRI',
            'CI' => 'CIV',
            'HR' => 'HRV',
            'CU' => 'CUB',
            'CW' => 'CUW',
            'CY' => 'CYP',
            'CZ' => 'CZE',
            'DK' => 'DNK',
            'DJ' => 'DJI',
            'DM' => 'DMA',
            'DO' => 'DOM',
            'EC' => 'ECU',
            'EG' => 'EGY',
            'SV' => 'SLV',
            'GQ' => 'GNQ',
            'ER' => 'ERI',
            'EE' => 'EST',
            'ET' => 'ETH',
            'FK' => 'FLK',
            'FO' => 'FRO',
            'FJ' => 'FIJ',
            'FI' => 'FIN',
            'FR' => 'FRA',
            'GF' => 'GUF',
            'PF' => 'PYF',
            'TF' => 'ATF',
            'GA' => 'GAB',
            'GM' => 'GMB',
            'GE' => 'GEO',
            'DE' => 'DEU',
            'GH' => 'GHA',
            'GI' => 'GIB',
            'GR' => 'GRC',
            'GL' => 'GRL',
            'GD' => 'GRD',
            'GP' => 'GLP',
            'GU' => 'GUM',
            'GT' => 'GTM',
            'GG' => 'GGY',
            'GN' => 'GIN',
            'GW' => 'GNB',
            'GY' => 'GUY',
            'HT' => 'HTI',
            'HM' => 'HMD',
            'VA' => 'VAT',
            'HN' => 'HND',
            'HK' => 'HKG',
            'HU' => 'HUN',
            'IS' => 'ISL',
            'IN' => 'IND',
            'ID' => 'IDN',
            'IR' => 'IRN',
            'IQ' => 'IRQ',
            'IE' => 'IRL',
            'IM' => 'IMN',
            'IL' => 'ISR',
            'IT' => 'ITA',
            'JM' => 'JAM',
            'JP' => 'JPN',
            'JE' => 'JEY',
            'JO' => 'JOR',
            'KZ' => 'KAZ',
            'KE' => 'KEN',
            'KI' => 'KIR',
            'KP' => 'PRK',
            'KR' => 'KOR',
            'KW' => 'KWT',
            'KG' => 'KGZ',
            'LA' => 'LAO',
            'LV' => 'LVA',
            'LB' => 'LBN',
            'LS' => 'LSO',
            'LR' => 'LBR',
            'LY' => 'LBY',
            'LI' => 'LIE',
            'LT' => 'LTU',
            'LU' => 'LUX',
            'MO' => 'MAC',
            'MK' => 'MKD',
            'MG' => 'MDG',
            'MW' => 'MWI',
            'MY' => 'MYS',
            'MV' => 'MDV',
            'ML' => 'MLI',
            'MT' => 'MLT',
            'MH' => 'MHL',
            'MQ' => 'MTQ',
            'MR' => 'MRT',
            'MU' => 'MUS',
            'YT' => 'MYT',
            'MX' => 'MEX',
            'FM' => 'FSM',
            'MD' => 'MDA',
            'MC' => 'MCO',
            'MN' => 'MNG',
            'ME' => 'MNE',
            'MS' => 'MSR',
            'MA' => 'MAR',
            'MZ' => 'MOZ',
            'MM' => 'MMR',
            'NA' => 'NAM',
            'NR' => 'NRU',
            'NP' => 'NPL',
            'NL' => 'NLD',
            'AN' => 'ANT',
            'NC' => 'NCL',
            'NZ' => 'NZL',
            'NI' => 'NIC',
            'NE' => 'NER',
            'NG' => 'NGA',
            'NU' => 'NIU',
            'NF' => 'NFK',
            'MP' => 'MNP',
            'NO' => 'NOR',
            'OM' => 'OMN',
            'PK' => 'PAK',
            'PW' => 'PLW',
            'PS' => 'PSE',
            'PA' => 'PAN',
            'PG' => 'PNG',
            'PY' => 'PRY',
            'PE' => 'PER',
            'PH' => 'PHL',
            'PN' => 'PCN',
            'PL' => 'POL',
            'PT' => 'PRT',
            'PR' => 'PRI',
            'QA' => 'QAT',
            'RE' => 'REU',
            'RO' => 'ROU',
            'RU' => 'RUS',
            'RW' => 'RWA',
            'BL' => 'BLM',
            'SH' => 'SHN',
            'KN' => 'KNA',
            'LC' => 'LCA',
            'MF' => 'MAF',
            'SX' => 'SXM',
            'PM' => 'SPM',
            'VC' => 'VCT',
            'WS' => 'WSM',
            'SM' => 'SMR',
            'ST' => 'STP',
            'SA' => 'SAU',
            'SN' => 'SEN',
            'RS' => 'SRB',
            'SC' => 'SYC',
            'SL' => 'SLE',
            'SG' => 'SGP',
            'SK' => 'SVK',
            'SI' => 'SVN',
            'SB' => 'SLB',
            'SO' => 'SOM',
            'ZA' => 'ZAF',
            'GS' => 'SGS',
            'SS' => 'SSD',
            'ES' => 'ESP',
            'LK' => 'LKA',
            'SD' => 'SDN',
            'SR' => 'SUR',
            'SJ' => 'SJM',
            'SZ' => 'SWZ',
            'SE' => 'SWE',
            'CH' => 'CHE',
            'SY' => 'SYR',
            'TW' => 'TWN',
            'TJ' => 'TJK',
            'TZ' => 'TZA',
            'TH' => 'THA',
            'TL' => 'TLS',
            'TG' => 'TGO',
            'TK' => 'TKL',
            'TO' => 'TON',
            'TT' => 'TTO',
            'TN' => 'TUN',
            'TR' => 'TUR',
            'TM' => 'TKM',
            'TC' => 'TCA',
            'TV' => 'TUV',
            'UG' => 'UGA',
            'UA' => 'UKR',
            'AE' => 'ARE',
            'GB' => 'GBR',
            'US' => 'USA',
            'UM' => 'UMI',
            'UY' => 'URY',
            'UZ' => 'UZB',
            'VU' => 'VUT',
            'VE' => 'VEN',
            'VN' => 'VNM',
            'VG' => 'VGB',
            'VI' => 'VIR',
            'WF' => 'WLF',
            'EH' => 'ESH',
            'YE' => 'YEM',
            'ZM' => 'ZMB',
            'ZW' => 'ZWE'
        );

        return isset($iso_3166_1[$country_code]) ? $iso_3166_1[$country_code] : false;
    }

    /**
     * @param OrderCore $order
     * @return string
     */
    private function getOrderStatus($order)
    {
        $status = (new OrderState((int)$order->getCurrentState(), $order->id_lang))->name;

        return empty($status) ? 'new' : $status;
    }
}
