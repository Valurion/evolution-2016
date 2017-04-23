<?php

require_once 'Framework/Modele.php';
require_once __DIR__ .'/MongoUtils.php';

/**
 * Logging manager
 *
 * Ported from ManagerCom.php to use mongo methods.
 * Dependencies:
 * - MongoDB server 2.4+ ($setOnInsert)
 * - php: pecl mongo (not mongodb !)
 *
 * @todo boolean return values
 * @todo the FIXME's
 *
 *
 * @version 1.0
 * @author ©Pythagoria - www.pythagoria.com
 * @author Philippe Huysmans <philippe.huysmans@pythagoria.com>
 * @author Benjamin Hennon
 */
class ManagerComMongo extends Modele
{

    /**
     * The database name
     */
    const DATABASE = 'cairn';

    /**
     * The logs mongo collection name
     */
    const COLLECTION = 'logs';

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
            $this->connection = new MongoDB($this->client, self::DATABASE);
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

                // set some indexes
                // ID_USER unique
                $collection->createIndex(array(
                    'ID_LOG' => 1,
                ), array(
                    'unique' => true,
                ));

                // TOUCH_DATE descending
                // FIXME date type
                $collection->createIndex(array(
                    'TOUCH_DATE' => -1
                ));

                $this->collection = $collection;

            } catch (Exception $e) {
                // hmmm ...
                // $collection = $this->getConnection()->createCollection($name);
            }
        }

        return $this->collection;
    }

    /**
     * Returns a pristine/clean slate record to use in upsert method
     *
     * @param string $id
     * @return array
     */
    protected function getPristineRecord($id)
    {
        $date = MongoUtils::getMongoDate();

        return array(

            'ID_LOG'                    => $id,
            // common fields
            'CDATE'                     => $date,
            'TOUCH_DATE'                => null,
            'ALERT_EJECT'               => 0,
            'HISTO_JSON'                => [],
            'HISTO_JSON_INT'            => [],
            // user fields
            'ID_USER'                   => null,
            'IP'                        => null,
            // multiple ips ... ??
            // session date fields
            'SESSION_GUEST_FIRST'       => null,
            'SESSION_USER_FIRST'        => null,
            'SESSION_USER_LAST'         => null,
            'SESSION_USER_END'         => null,
            'SESSION_INST_FIRST'        => null,
            'SESSION_INST_LAST'         => null,
            'SESSION_INST_END'         => null,
            'SESSION_IP_FIRST'          => null,
            'SESSION_IP_LAST'           => null,
            // session counters
            'SESSION_COUNTER'           => 0,
            'SESSION_DAY_COUNTER'       => 0,
            // subcollections
            'INSTITUTIONS'              => new stdClass(), //[],
            'DISCIPLINES'               => new stdClass(), //[],
            // consultation counters
            'CONSULTATION_COUNTER'      => 0,
            'CONSULTATION_PDF_COUNTER'  => 0,
            // kassa kassa
            'ACHATS_PPV_COUNTER'        => 0,
            'ACHATS_PPV_FIRST'          => null,   // date
            'ACHATS_PPV_LAST'           => null,   // date
            // device stuff
            'DEVICE_TYPE'               => null,
        );
    }

    /**
     * Performs an $addToSet operation
     *
     * @param string $id
     * @param array|mixed $value
     * @return boolean
     */
    protected function addToSet($id, $set, $value)
    {
        $collection = $this->getCollectiion(self::COLLECTION);
        $new = $this->getPristineRecord($id);

        $result = $collection->update(array(
            'ID_LOG'        => $id
        ), array(
            '$setOnInsert'  => $new,
            '$addToSet'     => array("${set}" => $value)
        ), array(
            '$upSert'       => true,
            'w'             => true,
            'multiple'      => false
        ));
    }

    /**
     * Performs an $push operation
     *
     * @param string $id
     * @param string $key
     * @param array|mixed $value
     * @return boolean
     */
    protected function pushToList($id, $list, $value)
    {
        $collection = $this->getCollectiion(self::COLLECTION);
        $new = $this->getPristineRecord($id);

        $result = $collection->update(array(
            'ID_LOG'        => $id
        ), array(
            '$setOnInsert'  => $new,
            '$push'         => array("${list}" => $value)
        ), array(
            '$upSert'       => true,
            'w'             => true,
            'multiple'      => false
        ));
    }

    /**
     * Increment method
     *
     * Increments a specified field with $with
     *
     * @param string $id
     * @param string $name The property to increment
     * @param int $with (optional) The increment
     * @return boolean
     */
    protected function incrementLogRecord($id, $name, $with = 1)
    {
        $collection = $this->getCollection(self::COLLECTION);

        $new = $this->getPristineRecord($id);

        // remove from setOnInsert
        unset($new[$name]);

        $result = $collection->update(array(
                'ID_LOG'        => $id
            ), array(
                '$setOnInsert'  => $new,
                '$inc'          => array("${name}" => (int) $with)
            ), array(
                'upsert'        => true,
                'w'             => true,
                'multiple'      => false,
            ));

        return $result;
    }

     /**
     * Increment list method
     *
     * Increments a specified field with $with.
     * Note: since there seems to be a bug doing an increment on a list.field item
     * when the field is numeric (as in id: 55), it will use it as the index 55
     * of the array, and thus inserts 54 null values into the list if the index
     * does not exist.... Patched via a "i" prefix
     * FIXME - when retrieving the count list, remove the "i"'s ...
     *
     * ... fixed by forcing an associative array
     *
     * @param string $id
     * @param string $name The property to increment
     * @param int $with (optional) The increment
     * @return boolean
     */
    protected function incrementListCounter($id, $list, $name, $with = 1)
    {
        $collection = $this->getCollection(self::COLLECTION);
        $new = $this->getPristineRecord($id);
        $result = $collection->update(array(
                'ID_LOG'        => $id
            ), array(
                '$setOnInsert'  => $new,
                '$inc'          => array("${list}.${name}" => (int) $with)
            ), array(
                'upsert'        => true,
                'w'             => true,
                'multiple'      => false,
            ));

        return $result;
    }

    /**
     * UpSert method
     *
     * Updates or insert a log record
     *
     * @param string $id
     * @param array $data
     * @param array $criteria (optional) The criteria to query on
     * @return boolean
     */
    protected function setLogRecord($id, array $data, $criteria = null)
    {
        $collection = $this->getCollection(self::COLLECTION);

        $new = $this->getPristineRecord($id);

        // if no criteria given, the ID_LOG functions as selector
        if (null === $criteria) {
            $criteria = array(
                'ID_LOG' => $id
            );
        }

        // in order to prevent MongoWriteConcernExceptions ...
        // remove the keys from new if they are set in data !
        $diff = array_intersect_key($new, $data);
        if (count($diff) > 0) {
            $new = array_diff_key($new, $data);
        }
        $result = $collection->update($criteria
            , array(
                '$setOnInsert'  => $new,
                '$set'          => $data,
            ), array(
                'upsert'        => true,
                'w'             => true,
                'multiple'      => false // @FIXME update emails !!!!
            ));

        return $result;
    }

    /**
     * Insert a log entry
     *
     * @param string $id
     * @param string $userId
     * @param int $interval (optional)
     * @param string $unit (optional)
     * @return boolean
     */
    public function insertUserLog($id, $userId, $interval = null, $unit = null, $first = 0, $touchInterval)
    {
        $startDate = MongoUtils::getMongoDate();
        $endDate = null;
        $touchDate = MongoUtils::getMongoDate();
        if (null !== $interval
            && null !== $unit
        ) {
            $endDate = MongoUtils::getDate(sprintf('+%s %s', $interval, $unit));
            $endDate = MongoUtils::getMongoDate($endDate);
        }
        $params = array(
            'ID_USER'            => $userId,
            'SESSION_USER_LAST' => $startDate,
            'SESSION_USER_END'  => $endDate,
            'TOUCH_DATE'         => $touchDate,
            'TOUCH_INTERVAL'    => $touchInterval
        );
        if($first == 1){
            $params["SESSION_USER_FIRST"] = $startDate;
        }
        
        $this->incrementLogRecord($id, 'SESSION_COUNTER', 1);
        $this->incrementDayCounter($id);
        
        $return = $this->setLogRecord($id, $params);
        
        return $return;
    }
    public function insertInstLog($id, $interval = null, $unit = null,$first = 0)
    {
        $startDate = MongoUtils::getMongoDate();
        $endDate = null;
        $touchDate = MongoUtils::getMongoDate();

        if (null !== $interval
            && null !== $unit
        ) {
            $endDate = MongoUtils::getDate(sprintf('+%s %s', $interval, $unit));
            $endDate = MongoUtils::getMongoDate($endDate);
        }
        
        $params = array(
            'SESSION_INST_LAST' => $startDate,
            'SESSION_INST_END'  => $endDate,
            'TOUCH_DATE'         => $touchDate,
        );
        if($first == 1){
            $params["SESSION_INST_FIRST"] = $startDate;
        }
        
        $this->incrementLogRecord($id, 'SESSION_COUNTER', 1);
        $this->incrementDayCounter($id);

        $return = $this->setLogRecord($id, $params);
                
        return $return;
    }

    /**
     * Closes the user log
     *
     * @param string $id
     * @return boolean
     */
    public function closeUserLog($id)
    {
        return $this->closeLog($id, 'SESSION_USER_END');
    }
    public function closeInstLog($id)
    {
        return $this->closeLog($id, 'SESSION_INST_END');
    }

    /**
     * Closes a log
     *
     * @param string $id
     * @param string $field The session property to "touch"
     * @return boolean
     */
    protected function closeLog($id, $field)
    {
        $endDate = MongoUtils::getMongoDate();

        return $this->setLogRecord($id, array(
           "${field}" => $endDate
        ), array(
            '$and' => array(
                array('ID_LOG'  => $id),
                /*array('$or'     => array(
                    array("${field}" => array('$gt' => $endDate)),
                    array("${field}" => array('$eq' => null)),
                )),*/
            ),
        ));
        // $sql = "UPDATE USER_LOG SET DATE_FIN = NOW() WHERE ID_USER_LOG = ? AND (DATE_FIN IS NULL OR DATE_FIN > NOW())";
    }


    /**
     *
     *
     *
     * @FIXME
     */
    public function closeOtherUserLog($userId,$id)
    {

        $collection = $this->getCollection(self::COLLECTION);

        $endDate = MongoUtils::getMongoDate();

        $result = $collection->update(array(
            '$and' => array(
                array('ID_USER' => $userId),
                array('ID_LOG' => array('$ne' => $id)),
                array('$or'     => array(
                    array('SESSION_USER_END' => array('$gt' => $endDate)),
                    array('SESSION_USER_END' => array('$eq' => null)),
            ))),
        ), array('$set' => array(
            'TOUCH_DATE'        => $endDate,
            'SESSION_USER_END' => $endDate,
            'ALERT_EJECT'       => 1
        )));

        // $sql = "UPDATE USER_LOG SET TOUCH_DATE = NOW(), DATE_FIN = NOW(), ALERT_EJECT = 1 WHERE ID_USER = ? AND (DATE_FIN IS NULL OR DATE_FIN > NOW())";
        return $result;
    }
    
    public function removeEject($id){
        $collection = $this->getCollection(self::COLLECTION);
        
        $result = $collection->update(array(
            '$and' => array(
                array('ID_LOG' => $id),
            ),
        ), array('$set' => array(
            'ALERT_EJECT'       => 0
        )));
    }


    /**
     * Touches the user log
     *
     * @param string $id
     * @return boolean
     * @alias ManagerComMongo::touchLog
     */
    public function touchUserLog($id)
    {
        return $this->touchLog($id);
    }

    /**
     * Touches the user log
     *
     * @param string $id
     * @return boolean
     */
    public function touchLog($id)
    {
        $endDate = MongoUtils::getMongoDate();

        return $this->setLogRecord($id,
            array('TOUCH_DATE' => $endDate),
            array('$and'       => array(
                array('ID_LOG' => $id),
                    // BUUGGGG SESSION /USER /GUEST IP .??????
            /*    array('$or'    =>  array(
                    array('SESSION_USER_LAST' => array('$gt' => $endDate)),
                    array('SESSION_USER_LAST' => array('$eq' => null)),
                ))*/)
            ));
        // $sql = "UPDATE USER_LOG SET TOUCH_DATE = NOW() WHERE ID_USER_LOG = ? AND (DATE_FIN IS NULL OR DATE_FIN > NOW())";
    }

    /**
     * Creates a new guest log
     *
     * @param string $id
     * @return boolean
     */
    public function insertGuestLog($id)
    {
        $date = MongoUtils::getMongoDate();
        return $this->setLogRecord($id, array(
           'SESSION_GUEST_FIRST' => $date
        ));
        // $sql = "INSERT INTO `USER_GUEST` (ID_USER, CDATE) VALUES (?, NOW())";
    }

    /**
     * Creates a new guest log
     *
     * @param string $id
     * @return boolean
     * @alias ManagerComMongo::insertGuestLog
     */
    public function insertUserGuest($id)
    {
        return $this->insertGuestLog($id);
    }

    /**
     * Creates a new ip log
     *
     * @param string $id
     * @param string $ip
     * @param string $userId
     * @param string $interval (optional)
     * @param int $unit (optional)
     * @return boolean
     * @alias ManagerComMongo::insertUserIp
     */
    public function insertIpLog($id, $ip, $userId, $interval = null, $unit = null)
    {
        return $this->insertUserIp($id, $ip, $userId, $interval, $unit);
    }

    /**
     * Creates a new ip log
     *
     * @param string $id
     * @param string $ip
     * @param string $userId
     * @param string $interval (optional)
     * @param int $unit (optional)
     * @return boolean
     */
    public function insertUserIP($id, $ip, $userId, $interval = null, $unit = null,$first=0)
    {
        $startDate = MongoUtils::getMongoDate();
        $endDate = null;
        $touchDate = MongoUtils::getMongoDate();

        if (null !== $interval
            && null !== $unit
        ) {
            $endDate = MongoUtils::getDate(sprintf('+%s %s', $interval, $unit));
            $endDate = MongoUtils::getMongoDate($endDate);
        }
        $params = array(
            'TOUCH_DATE'       => $touchDate,
            'SESSION_IP_LAST'  => $startDate,
            'IP' => $ip
        );
        if($first==1){
            $params['SESSION_IP_FIRST'] = $startDate;
        }
        
        $this->incrementLogRecord($id, 'SESSION_COUNTER', 1);
        $this->incrementDayCounter($id);
        
        $return = $this->setLogRecord($id, $params);
                
        return $return;

/*        if ($interval != null && $unit != null) {
            $sql = "INSERT INTO `USER_LOG_IP` (ID_USER_IP, ID_USER, IP_USER, CDATE, DATE_FIN, TOUCH_DATE)
                    VALUES (?, ?, ?, NOW(),NOW() + INTERVAL " . $interval . " " . $unit . ", NOW())";
        } else {
            $sql = "INSERT INTO `USER_LOG_IP` (ID_USER_IP, ID_USER, IP_USER, CDATE, TOUCH_DATE) VALUES (?, ?, ?, NOW(),NOW())";
        } */
    }

    /**
     * Touches the ip log
     *
     * @param string $id
     * @return boolean
     */
    public function touchLogIp($id)
    {
        return $this->touchLog($id);
        //$sql = "UPDATE USER_LOG_IP SET TOUCH_DATE = NOW() WHERE ID_USER_IP = ? AND DATE_FIN > NOW()";
    }

    /**
     * Closes the ip log
     *
     * @param string $id
     * @return boolean
     * @alias ManagerComMongo::closeUserLogIp
     */
    public function closeIpLog($id)
    {
        return $this->closeUserLogIp($id);
    }

    /**
     * Closes the ip log
     *
     * @param string $id
     * @return boolean
     */
    public function closeUserLogIp($id)
    {
        return $this->closeLog($id, 'SESSION_IP_END');
        // $sql = "UPDATE USER_LOG_IP SET DATE_FIN = NOW() WHERE ID_USER_IP = ? AND DATE_FIN > NOW()";
    }

     
    

    /**
     * Increment the DISCIPLINE.$disciplineId counter
     *
     * @param string $id The session id
     * @param string $disciplineId
     * @param int $with (optional) The increment
     * @return boolean
     */
    public function incrementDisciplineCounter($id, $disciplineId, $with = 1)
    {
        return $this->incrementListCounter($id, 'DISCIPLINES', $disciplineId, $with);
    }

    /**
     * Increment the INSTITUTIONS.$institutionId counter
     *
     * @param string $id The session id
     * @param string $institutionId
     * @param int $with (optional) The increment
     * @return boolean
     */
    public function incrementInstitutionCounter($id, $institutionId, $with = 1)
    {
        return $this->incrementListCounter($id, 'INSTITUTIONS', $institutionId, $with);
    }

    /**
     * Increments the CONSULTATION_COUNTER
     *
     * @param string $id
     * @param int $with The increment
     * @return boolean
     */
    public function incrementConsultationCounter($id, $with = 1)
    {
        return $this->incrementLogRecord($id, 'CONSULTATION_COUNTER', $with);
    }

    /**
     * Increments the CONSULTATION_PDF_COUNTER
     *
     * @param string $id
     * @param int $with The increment
     * @return boolean
     */
    public function incrementConsultationPdfCounter($id, $with = 1)
    {
        return $this->incrementLogRecord($id, 'CONSULTATION_PDF_COUNTER', $with);
    }

    /**
     * Increment the ACHATS_PPV_COUNTER
     *
     * @param string $id The session id
     * @param int $with (optional) The increment
     * @param boolean $touch (optional) Whether to touch the dates aswell
     * @return boolean
     */
    public function incrementAchatsCounter($id, $with = 1, $touch = false)
    {
        if ($touch) {
            $this->touchAchats($id);
        }
        return $this->incrementLogRecord($id, 'ACHATS_PPV_COUNTER', $with);
    }
    
    public function incrementDayCounter($id){
        $collection = $this->getCollection(self::COLLECTION);

        $todayDate = MongoUtils::getMongoDate(date("Y-m-d")." 00:00:00");

        $result = $collection->update(array(
            '$and' => array(
                array('ID_LOG' => $id),
                array('$or' => array(
                    array('SESSION_DAY_COUNTER' => (int) 0),
                    array('$and' => array(
                        array('$or' => array(
                            array('SESSION_USER_LAST' => null),
                            array('SESSION_USER_LAST' => array('$lt' => $todayDate)),
                        )),
                        array('$or' => array(
                            array('SESSION_INST_LAST' => null),
                            array('SESSION_INST_LAST' => array('$lt' => $todayDate)),
                        )),
                        array('$or' => array(
                            array('SESSION_IP_LAST' => null),
                            array('SESSION_IP_LAST' => array('$lt' => $todayDate)),                
                        ))
                    ))
                ))
            ),                
        ), array('$inc' => array("SESSION_DAY_COUNTER" => (int) 1)));  
        return $result;
    }

    /**
     * Touches the ACHATS_PPV_LAST date
     *
     * Sets the ACHATS_PPV_LAST to now.
     *
     * @param string $id The session id
     * @return boolean
     */
    public function touchAchats($id)
    {
        $endDate = MongoUtils::getMongoDate();

        $result = $this->setLogRecord($id,
            array('ACHATS_PPV_LAST' => $endDate)
        );

        // set the first date if its not there yet
        $collection = $this->getCollection(self::COLLECTION);
        $collection->update(array(
            '$and' => array(
                array('ID_LOG'              => $id),
                array('ACHATS_PPV_FIRST'    => null)
                )),
                array(
                    '$set' => array('ACHATS_PPV_FIRST'          => $endDate)
                ));

        return $result;
    }

    /**
     * Updates the histo json for a guest
     *
     * @param string $id
     * @param stdClass $histo The histoJson object
     * @return boolean
     */
    public function updateGuestHistoJson($id, $histo)
    {
        return $this->setLogRecord($id, array(
            'HISTO_JSON' => $histo,
        ));
        // $sql = "UPDATE `USER_GUEST` SET HISTO_JSON = ? WHERE ID_USER = ?";
    }
    public function updateGuestHistoJsonInt($id, $histo)
    {
        return $this->setLogRecord($id, array(
            'HISTO_JSON_INT' => $histo,
        ));
        // $sql = "UPDATE `USER_GUEST` SET HISTO_JSON = ? WHERE ID_USER = ?";
    }

    /**
     * Updates the user agent for a guest
     * From this information, it exists libraries which extract the device
     * It's better to run it async.
     * See for example: https://github.com/piwik/device-detector
     *
     * @param string $id
     * @param string $userAgent
     * @return boolean
     */
    public function saveUserAgent($id,$userAgent){
        return $this->setLogRecord($id, array(
            'DEVICE_TYPE' => $userAgent,
        ));
    }

}
