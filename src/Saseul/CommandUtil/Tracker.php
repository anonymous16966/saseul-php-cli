<?php

namespace Saseul\CommandUtil;

class Tracker
{
    public static $file = ROOT_DIR.DIRECTORY_SEPARATOR.'saseul-cli-tracker.json';

    public static function getPeers(): array
    {
        $json = json_encode([]);

        if (is_file(self::$file)) {
            $json = file_get_contents(self::$file);
        }

        return json_decode($json, true);
    }

    public static function setPeer(string $host): void
    {
        $peers = self::getPeers();
        $peers[] = $host;

        $peers = array_values(array_unique($peers));
        $json = json_encode($peers);

        file_put_contents(self::$file, $json);
    }

    public static function getRandomHost(): string
    {
        $peers = self::getPeers();
        $random_peer = rand(0, count($peers) - 1);

        return $peers[$random_peer];
    }
}