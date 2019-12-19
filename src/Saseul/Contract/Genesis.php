<?php

namespace Saseul\Contract;

use Saseul\Common\Contract;
use Saseul\Constant\Title;
use Saseul\Core\Env;
use Saseul\Constant\Decision;
use Saseul\Core\Key;
use Saseul\Status\Authority;
use Saseul\Status\Block;
use Saseul\Status\Tracker;
use Saseul\Util\TypeChecker;
use Saseul\Version;

class Genesis extends Contract
{
    public const TYPE = 'Genesis';
    public const T_STRUCTURE = [
        'type' => '',
        'version' => '',
        'from' => '',
        'key' => null,
        'timestamp' => 0
    ];

    protected $transaction;
    protected $thash;
    protected $public_key;
    protected $signature;

    private $type;
    private $version;
    private $from;
    private $key;
    private $timestamp;

    private $block_count;
    private $genesis_address;

    public function _init(array $transaction, string $thash, string $public_key, string $signature): void
    {
        $this->transaction = $transaction;
        $this->thash = $thash;
        $this->public_key = $public_key;
        $this->signature = $signature;

        $this->type = $this->transaction['type'] ?? '';
        $this->version = $this->transaction['version'] ?? '';
        $this->from = $this->transaction['from'] ?? '';
        $this->key = $this->transaction['key'] ?? null;
        $this->timestamp = $this->transaction['timestamp'] ?? 0;

        $this->timestamp = (int) $this->timestamp;
    }

    public function _getValidity(): bool
    {
        if (!TypeChecker::structureCheck(self::T_STRUCTURE, $this->transaction)) {
            return false;
        }

        return Version::isValid($this->version)
            && $this->type === self::TYPE
            && Key::isValidAddress($this->from, $this->public_key)
            && Key::isValidSignature($this->thash, $this->public_key, $this->signature);
    }

    public function _loadStatus(): void
    {
        Authority::GetInstance()->loadAuthority($this->from, Title::NETWORK_MANAGER);
    }

    public function _getStatus(): void
    {
        $this->block_count = Block::GetInstance()->getCount();
        $this->genesis_address = Env::getGenesisAddress();
    }

    public function _makeDecision(): string
    {
        if ($this->from === $this->genesis_address && (int) $this->block_count === 0)
        {
            return Decision::ACCEPT;
        }

        return Decision::REJECT;
    }

    public function _setStatus(): void
    {
        Tracker::GetInstance()->resetRequest();
        Tracker::GetInstance()->setValidator($this->from);
        Authority::GetInstance()->resetAuthority(Title::NETWORK_MANAGER);
        Authority::GetInstance()->setAuthority($this->from, Title::NETWORK_MANAGER);
    }
}