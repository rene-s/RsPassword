# RsPassword

RsPassword is a small PHP class for

1. generating salted password hashes with rounds and for
1. verifying salted password hashes with rounds.
1. It is supposed to adhere to the Public-Key Cryptography Standards (PKCS).

## German Web Application Developer Available for Hire!

No marketing skills whatsoever, but low rates, nearly 20 years of experience, and german work attitude.

Get in touch now: https://sdo.sh/DevOps/#contact

[![Build Status](https://travis-ci.org/rene-s/RsPassword.svg)](https://travis-ci.org/rene-s/RsPassword)
[![License](https://img.shields.io/badge/License-LGPL-blue.svg)](https://opensource.org/licenses/LGPL-3.0)

## Supported hashing algorithms

1. bcrypt (with rounds)
1. SHA256 (with rounds)
1. SHA512 (with rounds)
1. RIPEMD160 (with rounds)

# Author

Me:

1. [https://sdo.sh/](https://sdo.sh/)
1. [I am available for hire](mailto:rene+_gth@sdo.sh)

# Licence

LGPL v3 or commercial licence :) from rene+_gth@sdo.sh.

# Source/Download

[Source can be found at GitHub](https://github.com/rene-s/RsPassword)

# Requirements

1. PHP 5.3 ... PHP 7.1 (maybe also newer versions)
1. php5-mcrypt for PHP 5.x (make sure it's installed AND enabled)

Please note that PHP 5.5+ provides password_hash() which basically does what RsPassword does.
So you do not actually *need* RsPassword for secure passwords when using PHP 5.5+.
I consider RsPassword easier do use though.

# How to use

## Example script

Run the example script and follow instructions:

```
php ./hash.php [algorithm] [cost]
```

## Create password hash

```
    // Create salted SHA256 hash of $password with 10250 rounds
    $rsPassword = new RsPassword("sha256");

    $saltedHash = $rsPassword->hashPassword("password", 10250); // choose 4-15 rounds when hashing using bcrypt

    // To do for you: save salted hash, number of rounds, and used hash algorithm ($saltedHash, 10250, "sha256")
    // to database for later verification, for example when the user logs in.
```

## Verify password

```
    // To do for you: User logs in. Get the password from the form and get salted hash, number of rounds and hashing
    // algorithm from database ($passwordFromLoginForm, $saltedHashFromDb, $roundsFromDb, $hashingAlgoFromDb).

    // Verify password against saved salted hash
    $rsPassword = new RsPassword($hashingAlgoFromDb);

    $passwordMatches = $rsPassword->validatePassword($passwordFromLoginForm, $saltedHashFromDb, $roundsFromDb));

    if($passwordMatches) {
        // log in user now, grant access.
    } else {
        // passwords do not match, deny access.
    }
```

That's it.
