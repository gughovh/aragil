<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.12.2017
 * Time: 15:38
 */

namespace Aragil\Model;


abstract class Model
{
    /**
     * @var null|\PDO
     */
    private $connection = null;

    /**
     * @var null|array
     */
    private $connectionParams = null;

    /**
     * Model constructor.
     * @param array $settings
     */
    public function __construct($settings)
    {
        if(isset($settings['pdo']) && $settings['pds'] instanceof \PDO) {
            $this->setConnection($settings['pdo']);
        }

        if(isset($settings['connectionParams']) && is_array($settings['connectionParams'])) {
            $this->setConnectionParams($settings['connectionParams']);
        }
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        if(is_null($this->connection)) {
            $this->setConnection(getPdo($this->connectionParams));
        }

        return $this->connection;
    }

    /**
     * @param \PDO $pdo
     */
    public function setConnection(\PDO $pdo)
    {
        $this->connection = $pdo;
    }

    /**
     * @param array $params
     */
    public function setConnectionParams(array $params)
    {
        $this->connectionParams = $params;
    }

}