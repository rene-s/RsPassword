#!/bin/sh

exists () {
  type "$1" >/dev/null 2>/dev/null
}

exists phpunit

if [ $? -eq 0 ]; then
    phpunit test
else
    php /usr/bin/phpunit.phar test
fi