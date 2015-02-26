<?php
require "src/RsPassword/RsPassword.class.php";

$algorithm = "sha512";
$cost = 10;

printf("Usage: %s ['algorithm'] [cost]\n", $argv[0]);

if (isset($argv[1])) {
  $algorithm = $argv[1];
}

if (isset($argv[2])) {
  $cost = (int)$argv[2]; // do not cast to int here, RsPassword would not process it correctly (don't know why though)
}

// user needs to enter a password
print("Enter a password (no input will be visible): ");
$input = fopen("php://stdin", "r");
$password = trim(fgets($input));

$rsp = new RsPassword($algorithm);

$hash = $rsp->hashPassword($password, $cost);

printf("\nHashed: %s,%d,%s\n", $rsp->getAlgorithm(), $cost, $hash);


$rsp = new RsPassword($algorithm);

$result = $rsp->validatePassword($password, $hash, $cost);

if ($result) {
  printf("Verified\n");
  exit(0);
} else {
  printf("Could not verify!\n");
  exit(1);
}