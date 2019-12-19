<?php

namespace Saseul\Core;

use Saseul\Constant\Directory;
use Saseul\Constant\Structure;
use Saseul\System\Cache;
use Saseul\System\Database;
use Saseul\Util\Logger;
use Saseul\Util\TypeChecker;

class Env
{
    public static $memcached = [];
    public static $mongoDb = [];

    public static $node = [];
    public static $genesis = [];

    public static function load(): bool
    {
        if (!is_file(Directory::ENV_FILE)) {
            return false;
        }

        $env = file_get_contents(Directory::ENV_FILE);
        $env = json_decode($env, true);

        if (!TypeChecker::structureCheck(Structure::ENV, $env)) {
            return false;
        }

        self::$memcached = $env['memcached'];
        self::$mongoDb = $env['mongo_db'];

        self::$node = $env['node'];
        self::$genesis = $env['genesis'];

        return true;
    }

    public static function checkMemcached(): bool
    {
        return Cache::GetInstance()->isConnect();
    }

    public static function checkMongo(): bool
    {
        return Database::GetInstance()->IsConnect();
    }

    public static function registerErrorHandler()
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            if (!(error_reporting() & $errno)) {
                return;
            }

            switch ($errno) {
                case E_USER_ERROR:
                    $msg = PHP_EOL.'ERROR: ';
                    break;
                case E_USER_WARNING:
                    $msg = PHP_EOL.'WARNING: ';
                    break;
                case E_USER_NOTICE:
                    $msg = PHP_EOL.'NOTICE: ';
                    break;
                default:
                    $msg = PHP_EOL.'UNKNOWN: ';
                    break;
            }

            $e = new \Exception();

            $msg.= "[{$errno}] {$errstr} in {$errfile} on line {$errline} ".PHP_EOL.PHP_EOL;
            $msg.= $e->getTraceAsString().PHP_EOL;

            Logger::log($msg);
            $file = Directory::DEBUG_LOG_FILE;
            $f = fopen($file, 'a');
            fwrite($f, $msg);
            fclose($f);

            Logger::log('See '.$file);
            exit();
        });
    }

    public static function getPrivateKey(): string
    {
        return self::$node['private_key'];
    }

    public static function getPublicKey(): string
    {
        return self::$node['public_key'];
    }

    public static function getAddress(): string
    {
        return self::$node['address'];
    }

    public static function getGenesisAddress(): string
    {
        return self::$genesis['address'];
    }
}