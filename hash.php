<?php
require "lib/RsPassword.class.php";

$algorithm = "sha512";
$cost = 10;

if (!isset($argv[1])) {
  printf("Usage: %s 'string_to_be_hashed' ['algorithm'] [cost]\n", $argv[0]);
  exit(1);
}

if (isset($argv[2])) {
  $algorithm = $argv[2];
}

if (isset($argv[3])) {
  $cost = $argv[3];
}

$password = $argv[1];

$rsp = new RsPassword($algorithm);

$hash = $rsp->hashPassword($password, $cost);

printf("Hashed: %s,%d,%s\n", $algorithm, $cost, $hash);


$rsp = new RsPassword($algorithm);

$result = $rsp->validatePassword($password, $hash, $cost);

if($result) {
  printf("Verified\n");
} else {
  printf("Could not verify!\n");
}