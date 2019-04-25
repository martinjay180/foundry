<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHLAK\Config\Config;
use Foundry\Settings;

final class SettingsTest extends TestCase
{
    // Tests getting a value that is in the yaml file
    public function testGetValue()
    {
        $val = Settings::Get("test");
        $this->assertEquals("asdf", $val);
    }

    // Tests that the default value is returned in the key isn't found
    public function testDefaultValue()
    {
        $val = Settings::Get("nokey","defaultValue");
        $this->assertEquals("defaultValue", $val);
    }
}
