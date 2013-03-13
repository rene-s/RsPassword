<?php
/**
 * 1. RsPassword class.
 * 2. Hashes password with salt and rounds, validates passwords.
 * 3. Concatenates salt and hash for easy storage and handling. (store hash in CHAR(128))
 * 4. That's it.
 *
 * @package RsPassword
 * @author  Rene Schmidt <rene@reneschmidt.de>
 */
class RsPassword
{
  /**
   * @var string
   */
  protected $algorithm = "sha256";

  /**
   * Constructor
   *
   * @param string|null $algorithm Algorithm to use for hashing
   *
   * @return void
   */
  public function RsPassword($algorithm = null)
  {
    switch ($algorithm) {
      case "sha256":
      case "sha512":
      case "ripemd160":
        $this->algorithm = $algorithm;
        break;
      default:
        break;
    }
  }

  /**
   * Takes a password and returns the salted hash
   *
   * @param string $password Password string
   * @param int    $rounds   Amount of hashing rounds
   *
   * @return string hash of the password
   */
  public function hashPassword($password, $rounds = 10250)
  {
    $salt = $this->createSalt();
    $hash = $this->hashWithRounds($password, $salt, $rounds);

    return $salt . $hash;
  }

  /**
   * @param string $password Password string
   * @param string $salt     Salt string
   * @param int    $rounds   Amount of hashing rounds
   *
   * @return string
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
   * @return string
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
   * @return string
   */
  public function createRandomPassword($length = 8)
  {
    return $this->createSalt($length);
  }

  /**
   * Validates a password
   *
   * @param string $passwordToValidate Password string to be validated
   * @param string $storedSaltHash     Stored hash string
   * @param int    $rounds             Amount of rounds
   *
   * @return bool true if the password is valid, false otherwise.
   */
  public function validatePassword($passwordToValidate, $storedSaltHash, $rounds = 10250)
  {
    $salt = substr($storedSaltHash, 0, 64); //get the salt from the front of the "salt-hash"
    $storedHash = substr($storedSaltHash, 64, 64); //the actual hash

    //hash the password being tested
    $calculatedHash = $this->hashWithRounds($passwordToValidate, $salt, $rounds);

    //if the hashes are exactly the same, the password is valid
    return $calculatedHash === $storedHash;
  }
}
