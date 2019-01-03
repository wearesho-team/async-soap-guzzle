<?php

namespace Wearesho\AsyncSoap\Guzzle\Tests\Unit;

use GuzzleHttp;
use PHPUnit\Framework\TestCase;
use Wearesho\AsyncSoap\Guzzle;

/**
 * Class FactoryTest
 * @package Wearesho\AsyncSoap\Guzzle\Tests\Unit
 */
class FactoryTest extends TestCase
{
    public function testNonWsdlMode(): void
    {
        $factory = new Guzzle\Factory();
        $client = $factory->create(new GuzzleHttp\Client(), null, ['uri' => '', 'location' => '']);

        $this->assertTrue($client instanceof Guzzle\SoapClient);
    }

    public function testWsdlFromHttpUrl(): void
    {
        $handlerMock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(
                '200',
                [],
                fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'example.wsdl', 'r')
            )
        ]);
        $handler = new GuzzleHttp\HandlerStack($handlerMock);
        $clientMock = new GuzzleHttp\Client(['handler' => $handler]);

        $factory = new Guzzle\Factory();
        $client = $factory->create($clientMock, 'http://www.mysite.com/wsdl');

        $this->assertTrue($client instanceof Guzzle\SoapClient);
    }

    public function testWsdlFromLocalFile(): void
    {
        $factory = new Guzzle\Factory();
        $client = $factory->create(new GuzzleHttp\Client(), dirname(__FILE__) . DIRECTORY_SEPARATOR . 'example.wsdl');

        $this->assertTrue($client instanceof Guzzle\SoapClient);
    }

    public function testWsdlFromDataUri(): void
    {
        $wsdlString = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'example.wsdl');
        $wsdl = 'data://text/plain;base64,' . base64_encode($wsdlString);

        $factory = new Guzzle\Factory();
        $client = $factory->create(new GuzzleHttp\Client(), $wsdl);

        $this->assertTrue($client instanceof Guzzle\SoapClient);
    }
}
