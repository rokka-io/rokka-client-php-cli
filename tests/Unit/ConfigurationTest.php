<?php

namespace RokkaCli\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RokkaCli\Configuration;

class ConfigurationTest extends TestCase
{
    public function testConfiguration()
    {
        $configuration = new Configuration('uri', 'key', 'organization');

        $this->assertEquals('uri', $configuration->getApiUri());
        $this->assertEquals('key', $configuration->getApiKey());
        $this->assertEquals('organization', $configuration->getOrganizationName());
    }

    /**
     * @group legacy
     * @expectedDeprecation The $apiSecret argument to the configuration has been removed in version 1.5, adjust how you instantiate the configuration.
     */
    public function testConfigurationBC()
    {
        $configuration = new Configuration('uri', 'key', 'secret', 'organization');

        $this->assertEquals('uri', $configuration->getApiUri());
        $this->assertEquals('key', $configuration->getApiKey());
        $this->assertEquals('organization', $configuration->getOrganizationName());
    }
}
