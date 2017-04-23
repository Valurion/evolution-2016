<?php

/**
 *
 *
 *
 */
class MongoUtils
{

    /**
     * The date format to use
     * @var string
     */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Returns the now date, or with an offset "+ 2 weeks" ...
     *
     * @param string $offset (optional)
     * @return string
     */
    public static function getDate($offset = null)
    {
        if (null === $offset) {
            $date = date(self::DATE_FORMAT);
        } else {
            $date = date(self::DATE_FORMAT, strtotime($offset));
        }
        return $date;
    }

    /**
     * Returns a mongodate instance of a date string
     *
     * @param string $date (optional) Date string Y-m-d H:i:s format
     * @return MongoDate
     */
    public static function getMongoDate($date = null)
    {
        if (null === $date) {
            return new MongoDate();
        }
        return new MongoDate(strtotime($date));
    }

    /**
     *
     * @alias MongoUtils::getMongoDate
     * @param string $date (optional) Date string Y-m-d H:i:s format
     * @return MongoDate
     */
    public static function toMongoDate($date = null)
    {
        return self::getMongoDate($date);
    }

    /**
     * Converts the MongoDate to date string in format Y-m-d H:i:s
     *
     * @param MongoDate $date
     * @return string
     */
    public static function fromMongoDate(MongoDate $date)
    {
        return $date->toDateTime()->format(self::DATE_FORMAT);
    }

}
