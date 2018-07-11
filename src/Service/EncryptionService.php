<?php

namespace App\Service;

use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;

class EncryptionService
{
    const ENCRYPTED_CHECK = '<E>';

    /** @var  string */
    protected $secret;

    /**
     * @var Key
     */
    protected $key;

    /**
     * @param string $secret
     *
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function __construct(string $secret)
    {
        $this->secret = $secret;
        $this->key = Key::loadFromAsciiSafeString($this->secret);
    }

    /**
     * @param string $value
     *
     * @return string
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function encrypt(string $value)
    {
        if (empty($value) || $this->checkEncrypted($value)) {
            return $value;
        }

        return $this->addEncryptedCheck(Crypto::encrypt($value, $this->key));
    }

    /**
     * @param string $value
     *
     * @return string
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException
     */
    public function decrypt(string $value)
    {
        if (empty($value) || !$this->checkEncrypted($value)) {
            return $value;
        }

        $withoutCHeck = $this->removeEncryptedCheck($value);

        $decrypted = Crypto::decrypt($withoutCHeck, $this->key, false);

        return $decrypted;
    }

    private function checkEncrypted(string $value)
    {
        return strpos($value, self::ENCRYPTED_CHECK) !== false;
    }

    private function addEncryptedCheck(string $value)
    {
        return self::ENCRYPTED_CHECK . $value;
    }

    private function removeEncryptedCheck(string $value)
    {
        return str_replace(self::ENCRYPTED_CHECK, '', $value);
    }
}
