<?php

require_once 'Framework/Modele.php';
require_once __DIR__ .'/MongoUtils.php';

/**
 * Stats manager
 *
 * Ported from ManagerStat.php to use mongo methods.
 * Dependencies:
 * - MongoDB server 2.4+ ($setOnInsert)
 * - php: pecl mongo (not mongodb !)
 *
 * @version 1.0
 * @author ©Pythagoria - www.pythagoria.com
 * @author Philippe Huysmans <philippe.huysmans@pythagoria.com>
 * @author Benjamin Hennon
 */
class ManagerStatMongo extends Modele
{

    /**
     * The database name
     */
    const DATABASE = 'cairn';

    /**
     * The logs mongo collection name
     * Note: the "stats" collection name seems to be a reserved word
     */ 
    private static $COLLECTION_STATS;
    const COLLECTION_STATS_INFO = 'statz';
    const COLLECTION_STATS_INT = 'statz_int';

    /**
     * Robot flags ..
     */
    const COLLECTION_STATS_ROBOT = 'statz_robot';

    /**
     *
     */
    const COLLECTION_SEARCH = 'search';

    /**
     * @var MongoDB
     */
    private $connection;

    /**
     * @var array of MongoCollection
     */
    private $collections;

    /**
     * Constructor
     *
     * @param string $dns_name The dsn mongo server à la 'mongodb://user:pass@host:port'
     * @todo database name in dsn
     * @return ManagerStatMongo
     */
    public function __construct($dsn_name)
    {
        $this->client = new MongoClient($dsn_name);
        $this->collections = array();
        
        if(Configuration::get("mode") == 'cairninter'){
            self::$COLLECTION_STATS = self::COLLECTION_STATS_INT;
        }else{
            self::$COLLECTION_STATS = self::COLLECTION_STATS_INFO;
        }
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
        if (false === array_key_exists($name, $this->collections)) {

            try {

                // hmm, this try might be useless as its only throwing exceptions
                // when the name is invalid (not non-existing...)
                $collection = $this->getConnection()->createCollection($name);

                // DATE descending
                // FIXME date type
                $collection->createIndex(array(
                    'DATE' => -1
                ));

                $this->collections[$name] = $collection;

            } catch (Exception $e) {
                // hmmm ...
                // $collection = $this->getConnection()->createCollection($name);
            }
        }

        return $this->collections[$name];
    }

    /**
     * Returns a pristine/clean slate record to use in upsert method
     *
     * @param string $collection
     * @return array
     */
    protected function getPristineRecord($collection)
    {
        if (self::$COLLECTION_STATS === $collection) {
            return $this->getPristineStatsRecord();
        }
        if (self::COLLECTION_SEARCH === $collection) {
            return $this->getPristineSearchRecord();
        }
        return array();
        // throw something
    }

    /**
     * Returns a pristine/clean slate record to use in upsert method
     *
     * @param string $id
     * @return array
     */
    protected function getPristineStatsRecord()
    {
        $date = MongoUtils::getMongoDate();

        return array(

            'DATE'                      => $date,

            'IP'                        => null,
            'SESSION_ID'                => null,
            'INSTITUTION'               => null,
            'USER'                      => null,
            'GUEST'                     => null,

            'TYPE'                      => null,
            'ID_REVUE'                  => null,
            'ID_NUMPUBLIE'              => null,
            'ID_ARTICLE'                => null,
            'HTTP_USER_AGENT'           => null,

        );
    }

    /**
     *
     *
     */
    protected function getPristineSearchRecord()
    {
        $date = MongoUtils::getMongoDate();

        return array(

            'DATE'                      => $date,

            'IP'                        => null,
            'SESSION_ID'                => null,
            'INSTITUTION'               => null,
            'USER'                      => null,
            'GUEST'                     => null,

            'SEARCH_TERM'               => null,
            'SEARCHT'                   => null,

        );
    }

    /**
     * Insert method
     *
     * Inserts a stat record.
     *
     * @param string $collection
     * @param array $data The data to insert
     * @return string|boolean The MongoID or false when the insert failed
     */
    protected function insertRecord($collection, array $data)
    {
        $col = $this->getCollection($collection);
        $new = $this->getPristineRecord($collection);
        $record = $data + $new;

        $result = $col->insert((
            $record
        ), array(
            'w'     => true,
        ));

        // retrieve mongoid from the record..
        if (array_key_exists('_id', $record)) {
            return (string) $record['_id'];
        }

        return false;
    }

    /**
     * Inserts an article stat
     *
     * @param string $type
     * @param string $idArticle
     * @param string $idNumPublie
     * @param string $idRevue
     * @param array $authInfos
     * @param string $userAgent (optional)
     * @return string The mongo record _id
     */
    public function insertArticle($type, $idArticle, $idNumPublie, $idRevue, array $authInfos, $userAgent = null)
    {
        return $this->insertRecord(self::$COLLECTION_STATS, array(
            'IP'                => $authInfos['IP'],
            'SESSION_ID'        => $authInfos['G']['TOKEN'],
            'INSTITUTION'       => (array_key_exists('I', $authInfos) ? $authInfos['I']['ID_USER'] : ''),
            'USER'              => (array_key_exists('U', $authInfos) ? $authInfos['U']['ID_USER'] : ''),
            'GUEST'             => (array_key_exists('G', $authInfos) ? $authInfos['G']['TOKEN'] : ''),

            'TYPE'              => $type,
            'ID_REVUE'          => $idRevue,
            'ID_NUMPUBLIE'      => $idNumPublie,
            'ID_ARTICLE'        => $idArticle,
            'HTTP_USER_AGENT'   => $userAgent
        ));
    }

    /**
     * Insert stat for a robot
     *
     * @param string $id
     * @return string|false
     * @feedback Shouldn't this be setting a flag on the stat record ?
     */
    public function insertArticleCrossValidation($id)
    {
        return $this->insertRecord(self::COLLECTION_STATS_ROBOT, array(
            'LOG_ID'            => $id
        ));
    }

    /**
     * Insert a search stat
     *
     * @param string $searchTerm
     * @param array $authInfos
     * @param string $searchT (optional)
     * @return string|false
     */
    public function insertRecherche($searchTerm, $authInfos, $searchT = null)
    {
        return $this->insertRecord(self::COLLECTION_SEARCH, array(
            'IP'                => $authInfos['IP'],
            'SESSION_ID'        => $authInfos['G']['TOKEN'],
            'INSTITUTION'       => (array_key_exists('I', $authInfos) ? $authInfos['I']['ID_USER'] : ''),
            'USER'              => (array_key_exists('U', $authInfos) ? $authInfos['U']['ID_USER'] : ''),
            'GUEST'             => (array_key_exists('G', $authInfos) ? $authInfos['G']['TOKEN'] : ''),

            'SEARCH_TERM'       => $searchTerm,
            'SEARCHT'           => $searchT,
        ));
    }

}
