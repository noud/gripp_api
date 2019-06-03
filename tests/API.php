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

        $this->API = new com_gripp_API($apikey);
    }

    /**
     * Test Autopaging.
     */
    public function testAutopaging(): void
    {
        $this->API->setAutopaging(true);
        $autopaging = $this->API->getAutopaging();

        $this->assertTrue($autopaging);
    }

    /**
     * Test Batchmode.
     */
    public function testBatchmode(): void
    {
        $this->API->setBatchmode(true);
        $batchmode = $this->API->getBatchmode();

        $this->assertTrue($batchmode);
    }
}