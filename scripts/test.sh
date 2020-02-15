#!/bin/sh
#echo $XDEBUG_CONFIG
export XDEBUG_CONFIG="idekey=PHPSTORM"
PHPUNIT_XML="/home/pasha/www/my/dev-modules/scripts/phpunit.xml"
#php vendor/bin/phpunit -c $PHPUNIT_XML web/core/tests/Drupal/KernelTests/Core/Entity/EntityTypedDataDefinitionTest.php
#php vendor/bin/phpunit -v --debug  -c $PHPUNIT_XML web/modules/custom/etree/etree_content_core/tests/src/Kernel/TreeEntityContentTest.php
#php vendor/bin/phpunit --testdox --group active --colors -c $PHPUNIT_XML web/modules/custom/etree/etree_content_core/tests/src/Kernel/TreeEntityContentTest.php
php vendor/bin/phpunit --testdox --group active --colors -c $PHPUNIT_XML web/modules/custom/gpb_weather/tests/src/Kernel/WeatherServiceTest.php
