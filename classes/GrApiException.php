<?php
/**
 * Class GrApiException
 *
 * @author Getresponse <grintegrations@getresponse.com>
 * @copyright GetResponse
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class GrApiException extends \Exception
{
    const INCORRECT_API_TYPE = 'Incorrect API type';
    const CAMPAIGN_NOT_ADDED = 'Campaign has not been added';
    const INVALID_API_METHOD = 'Invalid API method';

    /**
     * @return GrApiException
     */
    public static function createForIncorrectApiTypeException()
    {
        return new self(self::INCORRECT_API_TYPE, 10001);
    }

    /**
     * @param Exception $e
     * @return GrApiException
     */
    public static function createForCampaignNotAddedException(\Exception $e)
    {
        return new self(self::CAMPAIGN_NOT_ADDED . ' - ' . $e->getMessage(), $e->getCode());
    }

    /**
     * @param string $errorMessage
     *
     * @return GrApiException
     */
    public static function createForInvalidCurlResponse($errorMessage)
    {
        return new self($errorMessage, 10002);
    }

    /**
     * @return GrApiException
     */
    public static function createForEmptyApiMethod()
    {
        return new self(self::INVALID_API_METHOD, 10003);
    }
}
