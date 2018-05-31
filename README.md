# phpunit-tideways-listener

Test Listener for [PHPUnit](https://github.com/sebastianbergmann/phpunit/) that uses [Tideways](https://tideways.com/)' [profiler](https://github.com/tideways/php-xhprof-extension) extension for PHP 7 to dump profiling information.

## Installation

You can add this library as a local, per-project, development-time dependency to your project using [Composer](https://getcomposer.org/):

    composer require --dev phpunit/phpunit-tideways-listener

## Usage

The example below shows how you activate and configure this test listener in your PHPUnit XML configuration file:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/7.2/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         forceCoversAnnotation="true"
         beStrictAboutCoversAnnotation="true"
         beStrictAboutOutputDuringTests="true"
         beStrictAboutTodoAnnotatedTests="true"
         verbose="true">
    <testsuites>
        <testsuite name="default">
            <directory suffix="Test.php">tests</directory>
        </testsuite>
    </testsuites>

    <filter>
        <whitelist processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">src</directory>
        </whitelist>
    </filter>

    <listeners>
        <listener class="PHPUnit\Tideways\TestListener">
            <arguments>
                <string>/tmp</string>
            </arguments>
        </listener>
    </listeners>
</phpunit>
```

The following elements are relevant to this test listener and its configuration:

* `<listeners>` is the configuration section for test listeners
* `<listener>` configures (an instance of) the `PHPUnit\Tideways\TestListener` class as a test listener
* `<arguments>` is the configuration for that test listener
* The only argument is the path to the directory where the profile information for each test is to be dumped, in this example `/tmp`

The rest of the `phpunit.xml` example shown above are best practice configuration defaults that were generated using `phpunit --generate-configuration`.

For each test that was run there will be a `.json` file in the specified directory. These file contain the `json_encode()`d profiling data returned by the profiler extension.

