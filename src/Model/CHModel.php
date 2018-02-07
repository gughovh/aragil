<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-18
 * Time: 3:20 PM
 */

namespace Aragil\Model;

use ClickHouseDB\Client;
use ClickHouseDB\StrictQuoteLine;

abstract class CHModel extends Client implements ModelInterface
{
    private static $instance = null;

    public function __construct(array $options = [])
    {
        parent::__construct(self::getConnectionParams($options));
        $this->database($options['database'] ?? ini('clickhouse.database'));
        $this->setTimeout(0);
    }

    /**
     * @param array $options
     * @return Client
     */
    public static function getClickHouseConnection(array $options = [])
    {
        if(is_null(self::$instance)) {
            self::$instance = new parent(self::getConnectionParams($options));
            self::$instance->setTimeout(0);
            self::$instance->database($options['database'] ?? ini('clickhouse.database'));
        }
        return self::$instance;
    }

    private static function getConnectionParams(array $options = [])
    {
        return array_merge([
            'host' => ini('clickhouse.host'),
            'port' => ini('clickhouse.port'),
            'username' => ini('clickhouse.username'),
            'password' => ini('clickhouse.password'),
        ], $options);
    }

    protected function customInsert(array $data, \Closure $closure)
    {
        if(!$data) {
            return false;
        }

        $sql = 'INSERT INTO ' . static::getTable();
        $sql .= ' (' . implode(',', array_keys($data[0])) . ') ';
        $sql .= ' VALUES ';

        $strictQuote = new StrictQuoteLine('Insert');
        foreach ($data as $row) {
            $sql .= ' (' . join(',', $closure($strictQuote->quoteValue($row))) . '), ';
        }
        $sql = trim($sql, ', ');

        return self::getClickHouseConnection()->write($sql);
    }

}