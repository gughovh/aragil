<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-02-01
 * Time: 3:02 PM
 */

namespace Aragil\Console\Commands\Database;


use Aragil\Console\Command;
use Aragil\Database\Seeder;
use Aragil\Model\CHModel;

class Seed extends Command
{
    const STATUS_OK = 'ok';

    const DBS = [
        'mysql' => [
            'handle' => 'seedMysql',
            'seedFile' => 'MysqlSeedData.json',
            'seedDir' => DATABASE_DIR . DS . 'mysql' . DS . 'seeds',
        ],
        'ch' => [
            'handle' => 'seedClickHouse',
            'seedFile' => 'ClickHouseSeedData.json',
            'seedDir' => DATABASE_DIR . DS . 'clickhouse' . DS . 'seeds',
        ]
    ];

    protected $description = 'Database seed. Available databases({db})` mysql, ch.';

    public function handle()
    {
        if(!array_key_exists($db = $this->arguments('db'), self::DBS)) {
            throw new \InvalidArgumentException();
        }

        $this->{self::DBS[$db]['handle']}(self::DBS[$db]);
    }

    private function seedMysql(array $options)
    {
        $pdo = getPdo($a = [
            'host' => $this->options('h') ?? ini('mysql.host'),
            'username' => $this->options('u') ?? ini('mysql.username'),
            'password' => $this->options('p') ?? ini('mysql.password'),
            'database' => $this->options('d') ?? ini('mysql.database'),
        ]);

        $this->run($options['seedDir'], $options['seedFile'], function ($data) use($pdo) {
            if(is_string($data)) {
                $pdo->exec($data);
            } elseif (is_array($data)) {
                throw new \Exception('Array seeds do not implemented yet');
            } else {
                throw new \LogicException('Unknown seed type');
            }

            if($pdo->errorCode() != '00000') {
                $error = $pdo->errorInfo();
                throw new \PDOException("SQLSTATE : $error[0] $error[2]");
            }
        });

    }

    private function seedClickHouse(array $options)
    {
        $this->run($options['seedDir'], $options['seedFile'], function ($data) {
            if(is_string($data)) {
                CHModel::getClickHouseConnection()->write($data);
            } elseif (is_array($data)) {
                throw new \Exception('Array seeds do not implemented yet');
            } else {
                throw new \LogicException('Unknown seed type');
            }
        });
    }

    private function run($seedDir, $seedFile, \Closure $seed)
    {
        $commandData = $this->getSeeded($seedFile);
        $seeders = $this->getSeeds($seedDir, $this->getSeedOk($commandData));

        /** @var  $seeder Seeder */
        foreach ($seeders as $filename => $seeder) {
            if(!($seeder instanceof Seeder)) {
                throw new \LogicException("{$filename} does not implement Seeder interface");
            }

            $meta = [
                'filename' => $filename,
                'status' => self::STATUS_OK,
            ];

            try {
                $this->line("Started seeder {$filename}");
                $seed($seeder->getDataOrQuery());
                $this->line("Ended seeder {$filename}");
                $failed = false;
            } catch (\Throwable $e) {
                $this->line("Failed seeder {$filename}");
                $this->line($e->getMessage());
                $meta['status'] = 'failed';
                $meta['error'] = (array)$e;
                $failed = true;
            }

            $commandData[$filename] = $meta;
            if($failed) {
                break;
            }
        }

        if(empty($seeders)) {
            $this->line('Nothing to seed.', false);
        }

        $this->updateSeedData($seedFile, $commandData);
    }

    private function getSeedOk(array $commandData)
    {
        return array_column(
            array_filter($commandData, function ($seedInfo) {
                return $seedInfo['status'] === self::STATUS_OK;
            }),
            'filename'
        );
    }

    private function getSeeds($dir, $ignore)
    {
        $migrations = [];
        foreach (glob("{$dir}/*.php") as $seeder) {
            $pathInfo = pathinfo($seeder);

            if(!in_array($pathInfo['filename'], $ignore)) {
                include $seeder;

                if(!class_exists($pathInfo['filename'])) {
                    throw new \LogicException("{$pathInfo['filename']} seeder class does not exists");
                }
                $migrations[$pathInfo['filename']] = new $pathInfo['filename'];
            }
        }

        return $migrations;
    }

    private function updateSeedData(string $file, array $seedData)
    {
        file_put_contents(MIGRATED_DIR . DS . $file, json_encode($seedData));
    }

    private function getSeeded($file)
    {
        $migrated = [];
        $file = MIGRATED_DIR . DS . $file;

        if(file_exists($file)) {
            $migrated = (array)json_decode(file_get_contents($file), true);
        }

        return $migrated;
    }
}