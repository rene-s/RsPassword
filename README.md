# RsPassword

RsPassword is a small PHP class for

1. generating salted password hashes with rounds and for
1. verifying salted password hashes with rounds.
1. It is supposed to adhere to the Public-Key Cryptography Standards (PKCS).

## Tests

There are some basic PHPUnit tests.

![Build result](https://api.travis-ci.org/rene-s/RsPassword.png "Build result")

([Travis CI](https://travis-ci.org/rene-s/RsPassword))

## Supported hashing algorithms

1. bcrypt (with rounds)
1. SHA256 (with rounds)
1. SHA512 (with rounds)
1. RIPEMD160 (with rounds)

# Author

Me:

1. [https://reneschmidt.de/wiki/](https://reneschmidt.de/wiki/)
1. [I am available for hire](mailto:rene+_gth@reneschmidt.de)

# Licence

LGPL v3 or commercial licence :) from github@reneschmidt.de.

# Source/Download

[Source can be found at GitHub](https://github.com/rene-s/RsPassword)

# Requirements

1. PHP 5.3, PHP 5.4, PHP 5.5 (maybe also newer versions)
1. php5-mcrypt

Please note that PHP 5.5+ provides password_hash() which basically does what RsPassword does. So you do not actually *need* RsPassword for secure passwords when using PHP 5.5+. I consider RsPassword easier do use though.

# How to use

## Create password hash

```php
    // Create salted SHA256 hash of $password with 10250 rounds
    $rsPassword = new RsPassword("sha256");

    $saltedHash = $rsPassword->hashPassword("password", 10250); // choose 4-15 rounds when hashing using bcrypt

    // To do for you: save salted hash, number of rounds, and used hash algorithm ($saltedHash, 10250, "sha256")
    // to database for later verification, for example when the user logs in.
```

## Verify password

```php
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

