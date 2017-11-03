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
     * @param int $idCart
     * @param string $grIdCart
     * @param string $grIdShop
     */
    public function removeCart($idCart, $grIdCart, $grIdShop)
    {
        $this->api->deleteCart($grIdShop, $grIdCart);
        $this->db->updateGetResponseCartId($idCart, '');
    }

    /**
     * @param array $product
     * @param string $grIdShop
     * @return string
     */
    public function createProductInGr($product, $grIdShop)
    {
        $psProduct = new Product($product['id_product']);
        $categories = $psProduct->getCategories();
        $productName = strip_tags((is_array($psProduct->name) ? array_shift($psProduct->name) : $psProduct->name));
        $productDescription = strip_tags(
            (
                is_array($psProduct->description_short)
                ? array_shift($psProduct->description_short)
                : $psProduct->description_short
            )
        );

        $grProduct = array(
            'name' => $productName,
            'url' => Tools::getHttpHost(true) . __PS_BASE_URI__ . '?controller=product&id_product=' . $psProduct->id,
            'type' => $psProduct->getWsType(),
            'vendor' => $psProduct->getWsManufacturerName(),
            'externalId' => $product['id_product'],
            'categories' => array(),
            'variants' => array(
                array(
                    'name' => $productName,
                    'description' => $productDescription,
                    'price'=> floatval($psProduct->price),
                    'priceTax' => 0.00,
                    'sku' => $psProduct->reference
                )
            )
        );

        foreach ($categories as $idCategory) {
            $category = new Category($idCategory);
            $grProduct['categories'][] = array('name' => $category->getName());
        }

        $response = $this->api->addProduct($grIdShop, $grProduct);

        return $this->handleProductResponse($response);
    }

    /**
     * @param array $params
     * @param string $grIdContact
     * @param string $grIdShop
     * @return array
     */
    public function createOrderObject($params, $grIdContact, $grIdShop)
    {
        /** @var OrderCore $order */
        $order = $params['order'];
        /** @var CartCore $cart */
        $cart = $params['cart'];

        $orderDate = DateTime::createFromFormat('Y-m-d H:i:s', $order->date_add);

        $grOrder = array(
            'contactId' => $grIdContact,
            'totalPrice' => $order->total_paid_tax_excl,
            'totalPriceTax' => $order->total_paid_tax_incl - $order->total_paid_tax_excl,
            'currency' => $params['currency']->iso_code,
            'orderUrl' => Tools::getHttpHost(true) . __PS_BASE_URI__ .
                '?controller=order-detail&id_order=' . $order->id,
            'externalId' => $order->reference,
            "billingAddress" => $this->returnAddressDetails(new Address($order->id_address_invoice)),
            "shippingAddress" => $this->returnAddressDetails(new Address($order->id_address_delivery)),
            "status" => $this->getOrderStatus($order),
            "shippingPrice" => floatval($order->total_shipping),
            "selectedVariants" => $this->returnOrderedVariantsList($order->getProducts(), $grIdShop, false),
            "billingStatus" => $this->getOrderStatus($order),
            "processedAt" => $orderDate->format('Y-m-d\TH:i:sO')
        );

        $grIdCart = $this->db->getGetResponseCartId($cart->id);

        if (!empty($grIdCart)) {
            $grOrder['cartId'] = $grIdCart;
        }

        return $grOrder;
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
     * @param string $grIdShop
     * @param string $grIdCart
     * @param string $grIdCustomer
     */
    public function sendCartDataToGR($cart, $grIdShop, $grIdCart, $grIdCustomer)
    {
        $cartDetails = $cart->getSummaryDetails();
        $products = $cart->getProducts(true);
        $params = array(
            'contactId' => $grIdCustomer,
            'currency' => (new Currency($cart->id_currency))->iso_code,
            'totalPrice' => $cartDetails['total_price_without_tax'],
            'selectedVariants' => $this->returnOrderedVariantsList($products, $grIdShop),
            'externalId' => $cart->id,
            'totalTaxPrice' => $cartDetails['total_tax'],
            'cartUrl' => Tools::getHttpHost(true) . __PS_BASE_URI__ . '?controller=order'
        );

        if (empty($grIdCart)) {
            $response = $this->api->addCart($grIdShop, $params);
            if (isset($response->cartId)) {
                $this->db->updateGetResponseCartId(
                    $cart->id,
                    $response->cartId
                );
            }
        } else {
            $this->api->updateCart($grIdShop, $grIdCart, $params);
        }
    }

    /**
     * @param string $grIdShop
     * @param array $grOrder
     * @param int $idOrder
     */
    public function sendOrderDataToGR($grIdShop, $grOrder, $idOrder)
    {
        $grOrderId = $this->db->getGetResponseOrderId($idOrder);

        if (empty($grOrderId)) {
            $response = $this->api->createOrder($grIdShop, $grOrder);
            if (!empty($response) && !empty($response->orderId)) {
                $this->db->updateGetResponseOrderId($idOrder, $response->orderId);
            }
        } else {
            $this->api->updateOrder($grIdShop, $grOrderId, $grOrder);
        }
    }

    /**
     * @param array $products
     * @param string $grIdShop
     * @param bool $cart
     * @return array
     */
    private function returnOrderedVariantsList($products, $grIdShop, $cart = true)
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
                'variantId' => $this->getVariantId($product, $grIdShop),
                'quantity' => $quantity,
                'price' => $price,
                'priceTax' => $tax
            );
        }

        return $variants;
    }

    /**
     * @param array $product
     * @param string $grIdShop
     * @return string
     */
    private function getVariantId($product, $grIdShop)
    {
        $idVariant = $this->db->getGetResponseProductId($product['id_product']);

        if (empty($idVariant)) {
            $idVariant = $this->createProductInGr($product, $grIdShop);
            $this->db->updateGetResponseProductId($product['id_product'], $idVariant);
        }

        return $idVariant;
    }

    /**
     * @param string $email
     * @param string $idCampaign
     * @param bool $force
     * @return string
     */
    public function getSubscriberId($email, $idCampaign, $force = false)
    {
        $grIdUser = $this->db->getGrSubscriberId($email, $idCampaign);

        if (empty($grIdUser) || $force) {
            $grContact = $this->api->getContactByEmail($email, $idCampaign);

            if (empty($grContact) || !isset($grContact->contactId)) {
                return false;
            }

            $this->db->setGrSubscriberId($email, $idCampaign, $grContact->contactId);
            return $grContact->contactId;
        }

        return $grIdUser;
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
     * @param string $countryCode Two letters country code
     * @return string|bool Three letters country code
     */
    private function convertCountryCode($countryCode)
    {
        $iso31661 = array(
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

        return isset($iso31661[$countryCode]) ? $iso31661[$countryCode] : false;
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
