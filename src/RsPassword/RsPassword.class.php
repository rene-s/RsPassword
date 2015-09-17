<?php
namespace RsPassword;

/**
 * Easy secure password generation
 *
 * 1. Hashes password with salt and rounds, validates passwords.
 * 2. Concats salt & hash for easy storage & handling. (store salt-hash in CHAR(128))
 * 3. That's it.
 *
 * @category Password
 * @package  RsPassword
 * @author   Rene Schmidt <rene@reneschmidt.de>
 * @license  https://www.gnu.org/licenses/lgpl.html LGPLv3
 * @link     https://reneschmidt.de/
 */
class RsPassword
{
    /**
     * Either sha256|sha512|ripemd160|bcrypt
     * @var string
     */
    protected $algorithm = "sha256";

    /**
     * Support for bcrypt or not
     * @var bool
     */
    protected $supportsBcrypt = true;

    /**
     * Constructor
     *
     * @param string|null $algorithm      Algorithm to use for hashing
     * @param bool|null   $supportsBcrypt Supports bcrypt()
     *
     * @throws \Exception
     */
    public function __construct($algorithm = null, $supportsBcrypt = null)
    {
        $this->supportsBcrypt = is_bool($supportsBcrypt)
            ? (bool)$supportsBcrypt
            : function_exists("password_hash");

        if (!is_callable('mcrypt_create_iv')) {
            throw new \Exception('mcrypt extension is not installed or not enabled.');
        }

        switch ($algorithm) {
            case "sha256":
            case "sha512":
            case "ripemd160":
            case "bcrypt":
                $this->algorithm = $algorithm;
                break;
            default:
                break;
        }
    }

    /**
     * Return current algorithm
     *
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * Check if we are supposed to use bcrypt
     *
     * @return bool
     */
    public function usesBcrypt()
    {
        return $this->getAlgorithm() === "bcrypt";
    }

    /**
     * Takes a password and returns the salted hash
     *
     * @param string $password Password string
     * @param int    $rounds   Amount of hashing rounds
     *
     * @return string salt-hash of the password
     * @throws \Exception
     */
    public function hashPassword($password, $rounds = null)
    {
        if ($this->usesBcrypt()) {
            if (is_null($rounds)) {
                $rounds = 10;
            } elseif ($rounds < 4 || $rounds > 15) {
                throw new \Exception(
                    "RsPassword supports bcrypt rounds only "
                    . "'4 <= \$rounds <= 15'. "
                    . "Please choose at least 4 or 15 at max."
                );
            }

            // do not create custom salt as the PHP documentation
            // recommends against it because password_hash()
            // already takes care for that.
            return password_hash(
                $password,
                PASSWORD_BCRYPT,
                array('cost' => $rounds /*, 'salt' => $this->createSalt(22)*/)
            );
        }

        $salt = $this->createSalt();
        $hash = $this->hashWithRounds(
            $password,
            $salt,
            is_null($rounds) ? 10250 : $rounds
        );

        return $salt . $hash;
    }

    /**
     * Do actual hashing
     *
     * @param string $password Password string
     * @param string $salt     Salt string
     * @param int    $rounds   Amount of hashing rounds
     *
     * @return string salt-hash of the password
     */
    public function hashWithRounds($password, $salt, $rounds)
    {
        $hash = "0";

        for ($i = 0; $i < $rounds; $i++) {
            $hash = hash($this->algorithm, $hash . $salt . $password);
        }

        return $hash;
    }

    /**
     * Get 256 random bits in hex
     *
     * @param int $length Desired salt length
     *
     * @return string Salt
     */
    public function createSalt($length = 32)
    {
        // if you get a "missing function mcrypt_create_iv()" or something similar,
        // make sure to a) have php5-mcrypt installed and b) *enabled*
        return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
    }

    /**
     * Creates a random password
     *
     * @param int $length Desired password length
     *
     * @return string Salt
     */
    public function createRandomPassword($length = 8)
    {
        return $this->createSalt($length);
    }

    /**
     * Validates a password
     *
     * @param string   $passwordToValidate Password string to be validated
     * @param string   $storedSaltHash     Stored hash string
     * @param int|null $rounds             Amount of rounds
     *
     * @return bool true if the password is valid, false otherwise.
     * @throws \Exception
     */
    public function validatePassword(
        $passwordToValidate,
        $storedSaltHash,
        $rounds = null
    ) {
        if ($this->usesBcrypt()) {
            return password_verify($passwordToValidate, $storedSaltHash);
        }

        //get the salt from the front of the "salt-hash"
        $storedSalt = substr($storedSaltHash, 0, 64);

        //the actual hash
        $storedHash = substr($storedSaltHash, 64);

        //hash the password being tested
        $calculatedHash = $this->hashWithRounds(
            $passwordToValidate,
            $storedSalt,
            is_null($rounds) ? 10250 : $rounds
        );

        //if the hashes are exactly the same, the password is valid
        return $calculatedHash === $storedHash;
    }
}
