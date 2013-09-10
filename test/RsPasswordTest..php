<?php

/**
 * Test RsPassword
 *
 * @package    RsPassword
 * @subpackage TestUnit
 * @author     Rene Schmidt <rene@reneschmidt.de>
 * @license    LGPLv3
 */
class RsPasswordTest extends PHPUnit_Framework_TestCase
{

  /**
   * Test hashPassword function
   *
   * @return void
   */
  public function testHashPassword()
  {
    $rsp = new RsPassword();

    $hashRounds1 = $rsp->hashPassword("a", 1);
    $hashRounds2 = $rsp->hashPassword("a", 2);

    $this->assertNotEquals($hashRounds1, $hashRounds2);
  }

  /**
   * Test createSalt
   *
   * @return void
   */
  public function testCreateSalt()
  {
    $rsp = new RsPassword();

    $this->assertRegExp("/^[a-f0-9]{64}$/", $rsp->createSalt());
    $this->assertRegExp("/^[a-f0-9]{128}$/", $rsp->createSalt(64));
  }

  /**
   * Test createRandomPassword
   *
   * @return void
   */
  public function testCreateRandomPassword()
  {
    $rsp = new RsPassword();
    $this->assertRegExp("/^[a-f0-9]{16}$/", $rsp->createRandomPassword());
    $this->assertRegExp("/^[a-f0-9]{32}$/", $rsp->createRandomPassword(16));
  }

  /**
   * Test validatePassword
   *
   * @param String $algo Hashing algorithm
   *
   * @return void
   */
  public function testValidatePassword($algo = "sha256")
  {
    $rsp = new RsPassword($algo);

    $password = "123werQWER§%&";
    $hash = $rsp->hashPassword($password, 10250);

    $this->assertFalse($rsp->validatePassword($password, $hash, 10251)); // wrong amount of rounds
    $this->assertFalse($rsp->validatePassword($password, "wrongHash", 10251)); // wrong hash
    $this->assertFalse($rsp->validatePassword("wrongPassword", $hash, 10250)); // wrong password

    $this->assertTrue($rsp->validatePassword($password, $hash, 10250));
  }

  /**
   * Test validatePassword with SHA-512
   *
   * @return void
   */
  public function testValidatePasswordSha512()
  {
    $this->testValidatePassword("sha256");
  }

  /**
   * Test validatePassword with RIPEMD160
   *
   * @return void
   */
  public function testValidatePasswordRipeMd160()
  {
    $this->testValidatePassword("ripemd160");
  }

  /**
   * Test validatePassword with invalid algorithm ID. Must handle that gracefully.
   *
   * @return void
   */
  public function testValidatePasswordInvalid()
  {
    $this->testValidatePassword("invalid");
  }

  /**
   * Test that hashes are different alright
   *
   * @return void
   */
  public function testCompareHashes()
  {
    $password = "123werQWER§%&";

    $rsp = new RsPassword();
    $hash = $rsp->hashPassword($password);

    $rsp = new RsPassword("sha256");
    $hashSha256 = $rsp->hashPassword($password);

    $rsp = new RsPassword("sha512");
    $hashSha512 = $rsp->hashPassword($password);

    $rsp = new RsPassword("ripemd160");
    $hashRipeMd160 = $rsp->hashPassword($password);

    $this->assertNotEquals($hashSha256, $hashSha512); // different salts, different algorithm
    $this->assertNotEquals($hashSha256, $hashRipeMd160); // different salts, different algorithm
    $this->assertNotEquals($hashSha512, $hashRipeMd160); // different salts, different algorithm
    $this->assertNotEquals($hashSha256, $hash); // same algorithm but different salts
  }

  /**
   * Test bcrypt support
   *
   * @return void
   */
  public function testBcrypt()
  {
    if (version_compare(PHP_VERSION, '5.5') < 0) {
      // current PHP version is too old. only check if RsPassword throws an exception when trying to use bcrypt with PHP < 5.5
      try {
        new RsPassword("bcrypt");
        $this->fail("Exception expected. bcrypt is available only with PHP 5.5 and newer.");
      } catch (Exception $e) {
        $this->assertInstanceOf("Exception", $e);
      }
    } else {
      // current PHP version supports bcrypt.
      $password = "123werQWER§%&";

      $rsp = new RsPassword("bcrypt");
      $hashBcrypt = $rsp->hashPassword($password);

      $rsp = new RsPassword();
      $hashSha256 = $rsp->hashPassword($password);

      $rsp = new RsPassword("sha512");
      $hashSha512 = $rsp->hashPassword($password);

      $rsp = new RsPassword("ripemd160");
      $hashRipeMd160 = $rsp->hashPassword($password);

      $this->assertNotEquals($hashBcrypt, $hashSha512); // different salts, different algorithm
      $this->assertNotEquals($hashBcrypt, $hashRipeMd160); // different salts, different algorithm
      $this->assertNotEquals($hashBcrypt, $hashSha256); // same algorithm but different salts

      $rsp = new RsPassword("bcrypt");
      $hashBcrypt = $rsp->hashPassword($password);

      $verifiedHash = $rsp->validatePassword($password,10251);
      $this->assertNotEquals($hashBcrypt, $verifiedHash); // same algorithm but rounds

      $verifiedHash = $rsp->validatePassword($password,10250);
      $this->assertEquals($hashBcrypt, $verifiedHash); // same algorithm, same rounds, same salt, same hash = ok
    }
  }
}