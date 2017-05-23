<?php

/**
 * Class GrApiException
 */
class GrApiException extends \Exception
{
    const INCORRECT_API_TYPE = 'Incorrect API type';
    const CAMPAIGN_NOT_ADDED = 'Campaign has not been added';

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
}
