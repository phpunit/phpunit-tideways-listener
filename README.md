# phpunit-tideways-listener

Test Listener for [PHPUnit](https://github.com/sebastianbergmann/phpunit/) that uses the [tideways_xhprof](https://github.com/tideways/php-profiler-extension) extension to dump memory profile information.

## Installation

You can add this library as a local, per-project, development-time dependency to your project using [Composer](https://getcomposer.org/):

    composer require --dev phpunit/phpunit-tideways-listener

## Usage

The example below shows how you activate and configure this test listener in your PHPUnit XML configuration file:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/6.5/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         forceCoversAnnotation="true"
         beStrictAboutCoversAnnotation="true"
         beStrictAboutOutputDuringTests="true"
         beStrictAboutTodoAnnotatedTests="true"
         verbose="true">
    <testsuite>
        <directory suffix="Test.php">tests</directory>
    </testsuite>

    <filter>
        <whitelist processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">src</directory>
        </whitelist>
    </filter>

    <listeners>
        <listener class="PHPUnit\Tideways\TestListener">
            <arguments>
                <string>/tmp</string>
                <string>callgrind</string>
            </arguments>
        </listener>
    </listeners>
</phpunit>
```

The following elements are relevant to this test listener and its configuration:

* `<listeners>` is the configuration section for test listeners
* `<listener>` configures (an instance of) the `PHPUnit\Tideways\TestListener` class as a test listener
* `<arguments>` is the configuration for that test listener
* The only argument is the path to the directory where the profile information is to be dumped, in this example `/tmp`

The rest of the `phpunit.xml` example shown above are best practice configuration defaults that were generated using `phpunit --generate-configuration`.

After the test suite has been executed, the test listener creates a file in the specified target directory that contains a `serialize()`d `ProfileCollection` object. This collection has a `Profile` object for each test that was executed. These `Profile` objects contain the raw profile information gathered by the `tideways_xhprof` extension as well as metadata on the test.
