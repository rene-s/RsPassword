<?php

/**
 * 1. RsPassword class.
 * 2. Hashes password with salt and rounds, validates passwords.
 * 3. Concatenates salt and hash for easy storage and handling. (store salt-hash in CHAR(128))
 * 4. That's it.
 *
 *
 * @package RsPassword
 * @author  Rene Schmidt <rene@reneschmidt.de>
 * @license LGPLv3
 */
class RsPassword
{
  /**
   * @var string
   */
  protected $algorithm = "sha256";

  /**
   * @var bool
   */
  protected $supportsBcrypt = true;

  /**
   * Constructor
   *
   * @param string|null $algorithm      Algorithm to use for hashing
   * @param bool|null   $supportsBcrypt Supports bcrypt()
   *
   * @return void
   * @throws \Exception
   */
  public function RsPassword($algorithm = null, $supportsBcrypt = null)
  {
    $this->supportsBcrypt = is_bool($supportsBcrypt) ? (bool)$supportsBcrypt : function_exists("password_hash");

    switch ($algorithm) {
      case "sha256":
      case "sha512":
      case "ripemd160":
        $this->algorithm = $algorithm;
        break;
      case "bcrypt":
        $this->algorithm = $algorithm;
        break;
      default:
        break;
    }
  }

  /**
   * Check if we are supposed to use bcrypt
   *
   * @return bool
   */
  public function usesBcrypt()
  {
    return $this->algorithm === "bcrypt";
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
      } else if ($rounds < 4 || $rounds > 15) {
        throw new \Exception("RsPassword supports bcrypt rounds only '4 <= \$rounds <= 15'. Please choose at least 4 or 15 at max.");
      }

      // do not create custom salt as the PHP documentation recommends against it because password_hash() already takes care for that.
      return password_hash($password, PASSWORD_BCRYPT, array('cost' => $rounds /*, 'salt' => $this->createSalt(22)*/));
    }

    $salt = $this->createSalt();
    $hash = $this->hashWithRounds($password, $salt, is_null($rounds) ? 10250 : $rounds);

    return $salt . $hash;
  }

  /**
   * @param string $password Password string
   * @param string $salt     Salt string
   * @param int    $rounds   Amount of hashing rounds
   *
   * @return string salt-hash of the password
   */
  private function hashWithRounds($password, $salt, $rounds)
  {
    $hash = "0";

    for ($i = 0; $i < $rounds; $i++) {
      $hash = hash($this->algorithm, $hash . $salt . $password);
    }

    return $hash;
  }

  /**
   * get 256 random bits in hex
   *
   * @param int $length Desired salt length
   *
   * @return string Salt
   */
  public function createSalt($length = 32)
  {
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
  public function validatePassword($passwordToValidate, $storedSaltHash, $rounds = null)
  {
    if ($this->usesBcrypt()) {
      return password_verify($passwordToValidate, $storedSaltHash);
    }

    $salt = substr($storedSaltHash, 0, 64); //get the salt from the front of the "salt-hash"
    $storedHash = substr($storedSaltHash, 64, 64); //the actual hash

    //hash the password being tested
    $calculatedHash = $this->hashWithRounds($passwordToValidate, $salt, is_null($rounds) ? 10250 : $rounds);

    //if the hashes are exactly the same, the password is valid
    return $calculatedHash === $storedHash;
  }
}
