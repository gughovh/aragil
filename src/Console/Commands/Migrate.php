<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-18
 * Time: 6:50 PM
 */

namespace Aragil\Console\Commands;


use Aragil\Model\CHModel;
use Aragil\Console\Command;

class Migrate extends Command
{
    const STATUS_OK = 'ok';

    const DBS = [
        'mysql' => [
            'handle' => 'migrateMysql',
            'migratedFile' => 'MysqlMigrations.json',
            'migrationsDir' => DATABASE_DIR . DS . 'mysql' . DS . 'migrations',
        ],
        'ch' => [
            'handle' => 'migrateClickHouse',
            'migratedFile' => 'ClickHouseMigrations.json',
            'migrationsDir' => DATABASE_DIR . DS . 'clickhouse' . DS . 'migrations',
        ]
    ];

    protected $description = 'Database migration. Available databases({db})` mysql, ch.';

    public function handle()
    {
        if(!array_key_exists($db = $this->arguments('db'), self::DBS)) {
            throw new \InvalidArgumentException();
        }

        $this->{self::DBS[$db]['handle']}(self::DBS[$db]);
    }

    private function migrateMysql(array $options)
    {
        $pdo = getPdo([
            'host' => $this->options('h') ?? ini('mysql.host'),
            'username' => $this->options('u') ?? ini('mysql.username'),
            'password' => $this->options('p') ?? ini('mysql.password'),
            'dbname' => $this->options('d') ?? ini('mysql.database'),
        ]);

        $this->migrate($options['migrationsDir'], $options['migratedFile'], function ($migration) use($pdo) {
            $pdo->exec($migration);
        });

    }

    private function migrateClickHouse(array $options)
    {
//        $client = Model::getClickHouseConnection();

        $this->migrate($options['migrationsDir'], $options['migratedFile'], function ($migration) /*use($client)*/ {
            CHModel::getClickHouseConnection()->write($migration);
        });
    }

    private function migrate($migrationsDir, $migratedFile, \Closure $migrate)
    {
        $migratedData = $this->getMigrated($migratedFile);
        $migrations = $this->getMigrations($migrationsDir, $this->getMigratedOk($migratedData));

        foreach ($migrations as $filename => $migration) {
            $meta = [
                'filename' => $filename,
                'status' => self::STATUS_OK,
            ];

            try {
                $this->line("Started migration {$filename}");
                $migrate($migration);
                $this->line("Ended migration {$filename}");
            } catch (\Throwable $e) {
                $this->line("Filed migration {$filename}");
                $meta['status'] = 'failed';
                $meta['error'] = (array)$e;
            }

            $migratedData[$filename] = $meta;
        }

        if(empty($migrations)) {
            $this->line('Nothing to migrate.');
        }

        $this->updateMigrated($migratedFile, $migratedData);
    }

    private function getMigratedOk(array $migratedData)
    {
        return array_column(
            array_filter($migratedData, function ($migratedInfo) {
                return $migratedInfo['status'] === self::STATUS_OK;
            }),
            'filename'
        );
    }

    private function getMigrations($dir, $ignore)
    {
        $migrations = [];
        foreach (glob("{$dir}/*.sql") as $migration) {
            $pathInfo = pathinfo($migration);

            if(!in_array($pathInfo['filename'], $ignore)) {
                $migrations[$pathInfo['filename']] = file_get_contents($migration);
            }
        }

        return $migrations;
    }

    private function updateMigrated(string $file, array $migratedData)
    {
        file_put_contents(MIGRATED_DIR . DS . $file, json_encode($migratedData));
    }

    private function getMigrated($file)
    {
        $migrated = [];
        $file = MIGRATED_DIR . DS . $file;

        if(file_exists($file)) {
            $migrated = (array)json_decode(file_get_contents($file), true);
        }

        return $migrated;
    }
}