<?php
/**
 * Class Gr_Shop
 *
 *  @author Getresponse <grintegrations@getresponse.com>
 *  @copyright GetResponse
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class GrShop
{
    /**
     * @return int
     */
    public static function getUserShopId()
    {
        $context = Context::getContext();

        if (method_exists($context->cookie, 'getAll')) {
            $cookie = $context->cookie->getAll();

            if (isset($cookie['shopContext'])) {
                return (int)Tools::substr($cookie['shopContext'], 2, count($cookie['shopContext']));
            }
        }

        return $context->shop->id;
    }
}
