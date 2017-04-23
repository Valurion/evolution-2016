<?php

require_once 'Framework/Modele.php';
require_once __DIR__ .'/MongoUtils.php';
require_once __DIR__ .'/ManagerComMongo.php';

/**
 * Logging content
 *
 * Ported from ContentCom.php to use mongo methods.
 * Dependencies:
 * - MongoDB server 2.4+ ($setOnInsert)
 * - php: pecl mongo (not mongodb !)
 *
 * @version 1.0
 * @author ©Pythagoria - www.pythagoria.com
 * @author Philippe Huysmans <philippe.huysmans@pythagoria.com>
 * @author Benjamin Hennon
 */
class ContentComMongo extends Modele
{

    /**
     * @var MongoDB
     */
    private $connection;

    /**
     * @var MongoCollection
     */
    private $collection;

    /**
     * Constructor
     *
     * @param string $dns_name The dsn mongo server à la 'mongodb://user:pass@host:port'
     * @todo database name in dsn
     * @return ManagerComMongo
     */
    public function __construct($dsn_name)
    {
        $this->client = new MongoClient($dsn_name);
    }

    /**
     * Get the connection
     *
     * @return MongoDB
     */
    public function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = new MongoDB($this->client, ManagerComMongo::DATABASE);
        }
        return $this->connection;
    }

    /**
     * Get a collection by name
     *
     * Note, will create the collection if it doesn't exist yet.
     *
     * @param string $name
     * @return MongoCollection
     */
    public function getCollection($name)
    {
        if (null === $this->collection) {

            try {

                // hmm, this try might be useless as its only throwing exceptions
                // when the name is invalid (not non-existing...)
                $collection = $this->getConnection()->createCollection($name);
                $this->collection = $collection;

            } catch (Exception $e) {
                // hmmm ...
                // $collection = $this->getConnection()->createCollection($name);
            }
        }

        return $this->collection;
    }

    /**
     * Patch the record's date fields
     *
     * Converts MongoDate fields to regular strings
     *
     * @param array $record
     * @return array The patched record
     */
    protected function patchRecordDates(array $record)
    {
        foreach(array(
            'TOUCH_DATE',
            'SESSION_USER_LAST',
            'SESSION_USER_FIRST',
            'SESSION_USER_END',
            'SESSION_INST_LAST',
            'SESSION_INST_FIRST',
            'SESSION_INST_END',
            'SESSION_IP_FIRST',
            'SESSION_IP_LAST',
            'SESSION_GUEST_FIRST',
            'SESSION_GUEST_LAST'
        ) as $field) {
            if (array_key_exists($field, $record)
                && is_object($record[$field])
                && $record[$field] instanceof MongoDate
            ) {
                // microtime format ....
                $record[$field] = MongoUtils::fromMongoDate($record[$field]);
            }
        }

        return $record;
    }

    /**
     * Finds one record
     *
     * @param string $id
     * @return false|array
     */
    protected function findRecord($id, $criteria = null, $fields = null)
    {
        $collection = $this->getCollection(ManagerComMongo::COLLECTION);

        $result = $collection->findOne(array(
            'ID_LOG' => $id
        ));

        if (null === $result) {
            return false;
        }

        // pathc 'em
        $result = $this->patchRecordDates($result);

        return $result;
    }

    /**
     * Retrieve a list of records
     *
     * @param array $criteria
     * @return array
     */
    public function findRecords($criteria)
    {
        $collection = $this->getCollection(ManagerComMongo::COLLECTION);

        $cursor = $collection->find($criteria);

        // FIXME
        $records = iterator_to_array($cursor);
        foreach ($records as &$record) {
            $record = $this->patchRecordDates($record);
        }

        return $records;
    }

    /**
     * Get the collection count
     *
     * @param array $critiria
     * @return int
     */
    public function getCount($criteria)
    {
        $collection = $this->getCollection(ManagerComMongo::COLLECTION);

        return $collection->count($criteria);
    }

    /**
     * Retrieve the guest info
     *
     * @param string $id
     * @return false|array
     */
    public function getGuestInfos($id)
    {
        return $this->findRecord($id);
    }

    /**
     * Get the amount of connected users between dates
     *
     * @param string $startDate (optional)
     * @param string $endDate (optional)
     * @return int
     */
    public function getUsersCountSessions($startDate = null, $endDate = null)
    {
        if (null === $startDate) {
            $startDate = MongoUtils::getDate(sprintf('- %s %ss', Configuration::get('userSessionDuration'), Configuration::get('userSessionUnit')));
        }

        if (null === $endDate) {
            $endDate = MongoUtils::getDate();
        }

        return $this->getCount(array(
            '$and' => array(
                array('SESSION_USER_FIRST' => array('$gte' => MongoUtils::getMongoDate($startDate))),
                array('SESSION_USER_LAST'  => array('$lte' => MongoUtils::getMongoDate($endDate)))
        )));
    }

    /**
     * Get the amount of connected guests between dates
     *
     * @param string $startDate (optional)
     * @param string $endDate (optional)
     * @return int
     */
    public function getGuestCountSessions($startDate = null, $endDate = null)
    {
        if (null === $startDate) {
            $startDate = MongoUtils::getDate(sprintf('- %s %ss', Configuration::get('guestSessionDuration'), Configuration::get('guestSessionUnit')));
        }

        if (null === $endDate) {
            $endDate = MongoUtils::getDate();
        }

        return $this->getCount(array(
            '$and' => array(
                array('SESSION_GUEST_FIRST' => array('$gte' => MongoUtils::getMongoDate($startDate))),
                array('SESSION_GUEST_LAST'  => array('$lte' => MongoUtils::getMongoDate($endDate)))
        )));
    }

    /**
     * Get the amount of connected institutions between dates
     *
     * @param string $startDate (optional)
     * @param string $endDate (optional)
     * @return int
     */
    public function getInstitutionCountSessions($startDate = null, $endDate = null)
    {
        if (null === $startDate) {
            $startDate = MongoUtils::getDate(sprintf('- %s %ss', Configuration::get('userInstSessionDuration'), Configuration::get('userInstSessionUnit')));
        }

        if (null === $endDate) {
            $endDate = MongoUtils::getDate();
        }

        return $this->getCount(array(
            '$and' => array(
                array('SESSION_IP_FIRST' => array('$gte' => MongoUtils::getMongoDate($startDate))),
                array('SESSION_IP_LAST'  => array('$lte' => MongoUtils::getMongoDate($endDate)))
        )));
    }

    /**
     * Retrieve all guest log records between dates
     *
     * @param string $startDate (optional)
     * @param string $endDate (optional)
     * @return array
     */
    public function getDataBoardGuest($startDate = null, $endDate = null)
    {
        if (null === $startDate) {
            $startDate = MongoUtils::getDate(sprintf('- %s %ss', Configuration::get('guestSessionDuration'), Configuration::get('guestSessionUnit')));
        }

        if (null === $endDate) {
            $endDate = MongoUtils::getDate();
        }

        return $this->findRecords(array(
            '$and' => array(
                array('SESSION_GUEST_FIRST' => array('$gte' => MongoUtils::getMongoDate($startDate))),
                array('SESSION_GUEST_LAST'  => array('$lte' => MongoUtils::getMongoDate($endDate)))
        )));
    }

    /**
     * Get the amount of sessions for a user
     *
     * @param string $idUser
     * @return int
     */
    public function getCountSessionByUser($idUser)
    {
        return $this->getCount(array(
            'ID_USER' => $idUser
        ));
    }

    /**
     * Checks if the session is valid
     *
     * @param string $id
     * @param int $interval
     * @param string $unit
     * @return string|false
     */
    public function checkSession($id, $interval, $unit, $type='U')
    {
        $field = $type=="U"?"SESSION_USER_END":"SESSION_INST_END";

        $now = MongoUtils::getMongoDate();
        $touchDate = MongoUtils::getDate(sprintf('-%s %s', $interval, $unit));
        $touchDate = MongoUtils::getMongoDate($touchDate);

        $return = $this->findRecords(array(
            '$and' => array(
                array('ID_LOG' =>$id),
                array('$or' => array(
                    array($field => array('$eq' => null)),
                    array($field => array('$gt' => $now))
                )),
                array('TOUCH_DATE' => array('$gte' => $touchDate),
            ))
        ));

        return $return;
    }

    public function validateSession($idUser, $id, $interval, $unit)
    {
        $now = MongoUtils::getMongoDate();
        $touchDate = MongoUtils::getDate(sprintf('- %s %s', $interval, $unit));
        $touchDate = MongoUtils::getMongoDate($touchDate);

        return $this->findRecords(array(
            '$and' => array(
                array('ID_USER' => $idUser),
                array('ID_LOG' => array('$ne' => $id)),
                array('$or' => array(
                    array('SESSION_USER_END' => array('$eq' => null)),
                    array('SESSION_USER_END' => array('$gt' => $now))
                ),
                //array('TOUCH_DATE' => array('$gt' => $touchDate)),
            ))
        ));

    }

    /**
     * Checks whether the user has been ejected
     *
     * @param string $id
     * @return int
     */
    public function isEjectMode($id)
    {
        $record = $this->findRecord($id);

        if (false === $record) {
            return 0; // $false;
        }

        return (int) $record['ALERT_EJECT'];
    }

    /**
     * Checks if the session is valid
     *
     * @param string $id
     * @param int $interval
     * @param string $unit
     * @return string|false
     */
    public function checkSessionIP($id, $interval, $unit)
    {
        $now = MongoUtils::getMongoDate();
        $touchDate = MongoUtils::getDate(sprintf('- %s %s', $interval, $unit));
        $touchDate = MongoUtils::getMongoDate($touchDate);

        return $this->findRecords(array(
            '$and' => array(
                array('ID_LOG' =>$id),
                array('TOUCH_DATE' => array('$gt' => $touchDate)),
            )
        ));

    }

}
