<?php

namespace API\Tests;

use com_gripp_API;
use PHPUnit\Framework\TestCase;

final class API extends TestCase
{
    /**
     * @var com_gripp_API
     */
    private $API;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $apikey = '[hier API-sleutel invullen]';
        $url = 'https://domeinnaam.gripp.com/public/api2.php';

        $this->API = new com_gripp_API($apikey, $url);
    }

    /**
     * Test adding a failure.
     */
    public function testBatchmode(): void
    {
        $this->API->setBatchmode(true);
        $batchmode = $this->API->getBatchmode();

        $this->assertTrue($batchmode);
    }
}