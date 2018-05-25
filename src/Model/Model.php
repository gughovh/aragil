<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.12.2017
 * Time: 15:38
 */

namespace Aragil\Model;


abstract class Model implements ModelInterface
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
    public function __construct($settings = null)
    {
        if(isset($settings['pdo']) && $settings['pdo'] instanceof \PDO) {
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
            $this->setConnection(
                getPdo(
                    $this->getConnectionParams()
                )
            );
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

    /**
     * @return array|null
     */
    public function getConnectionParams()
    {
        if(is_null($this->connectionParams)) {
            $this->setConnectionParams(config('mysql') ?? ini('mysql'));
        }

        return $this->connectionParams;
    }

    public function insert($data)
    {
        if(empty($data)) {
            return 0;
        }

        if (!isset($data[0])) {
            $data = [$data];
        }

        $values = join(',', array_map(function ($row) {
            $row = array_map(function($value) {
                return $this->getConnection()->quote($value);
            }, $row);
            return '(' . join(',', $row) . ')';
        }, $data));

        $fields = join(',', array_keys($data[0]));

        return $this->getConnection()->exec("
            INSERT INTO {$this->getTable()}
              ({$fields})
            VALUES
              {$values}
        ");
    }
}