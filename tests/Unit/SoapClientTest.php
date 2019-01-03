<?php

namespace Wearesho\AsyncSoap\Guzzle\Tests\Unit;

use GuzzleHttp;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Wearesho\AsyncSoap\Guzzle\SoapClient;
use Meng\Soap\HttpBinding;
use PHPUnit\Framework;

/**
 * Class SoapClientTest
 * @package Wearesho\AsyncSoap\Guzzle\Tests\Unit
 */
class SoapClientTest extends Framework\TestCase
{
    /** @var GuzzleHttp\Handler\MockHandler */
    private $handlerMock;

    /** @var GuzzleHttp\ClientInterface */
    private $client;

    /** @var Framework\MockObject\MockObject */
    private $httpBindingMock;

    /** @var GuzzleHttp\Promise\PromiseInterface */
    private $httpBindingPromise;

    protected function setUp(): void
    {
        $this->handlerMock = new GuzzleHttp\Handler\MockHandler();
        $handler = new GuzzleHttp\HandlerStack($this->handlerMock);
        $this->client = new GuzzleHttp\Client(['handler' => $handler]);

        $this->httpBindingMock = $this->getMockBuilder(HttpBinding\HttpBinding::class)
            ->disableOriginalConstructor()
            ->setMethods(['request', 'response'])
            ->getMock();
    }

    public function testMagicCallDeferredHttpBindingRejected(): void
    {
        $this->expectException(\Exception::class);

        $this->httpBindingPromise = new GuzzleHttp\Promise\RejectedPromise(new \Exception());
        $this->httpBindingMock->expects($this->never())->method('request');

        $client = new SoapClient($this->client, $this->httpBindingPromise);
        $client->someSoapMethod(['some-key' => 'some-value'])->wait();
    }

    public function testMagicCallHttpBindingFailed(): void
    {
        $this->expectException(HttpBinding\RequestException::class);

        $this->httpBindingPromise = new GuzzleHttp\Promise\FulfilledPromise($this->httpBindingMock);

        $this->httpBindingMock->method('request')
            ->will(
                $this->throwException(new HttpBinding\RequestException())
            )
            ->with(
                'someSoapMethod',
                [['some-key' => 'some-value']]
            );

        $this->httpBindingMock->expects($this->never())->method('response');

        $client = new SoapClient($this->client, $this->httpBindingPromise);
        $client->someSoapMethod(['some-key' => 'some-value'])->wait();
    }

    public function testMagicCall500Response(): void
    {
        $this->httpBindingPromise = new GuzzleHttp\Promise\FulfilledPromise($this->httpBindingMock);

        $this->httpBindingMock->method('request')
            ->willReturn(
                new GuzzleHttp\Psr7\Request('POST', 'www.endpoint.com')
            )
            ->with(
                'someSoapMethod',
                [['some-key' => 'some-value']]
            );

        $response = new GuzzleHttp\Psr7\Response('500');
        $this->httpBindingMock->method('response')
            ->willReturn(
                'SoapResult'
            )
            ->with(
                $response,
                'someSoapMethod',
                null
            );

        $this->handlerMock
            ->append(GuzzleRequestException::create(
                new GuzzleHttp\Psr7\Request('POST', 'www.endpoint.com'),
                $response
            ));

        $client = new SoapClient($this->client, $this->httpBindingPromise);
        $this->assertEquals('SoapResult', $client->someSoapMethod(['some-key' => 'some-value'])->wait());
    }

    public function testMagicCallResponseNotReceived(): void
    {
        $this->expectException(GuzzleHttp\Exception\RequestException::class);

        $this->httpBindingPromise = new GuzzleHttp\Promise\FulfilledPromise($this->httpBindingMock);

        $this->httpBindingMock->method('request')
            ->willReturn(
                new GuzzleHttp\Psr7\Request('POST', 'www.endpoint.com')
            )
            ->with(
                'someSoapMethod',
                [['some-key' => 'some-value']]
            );

        $this->httpBindingMock->expects($this->never())->method('response');

        $this->handlerMock
            ->append(GuzzleRequestException::create(new GuzzleHttp\Psr7\Request('POST', 'www.endpoint.com')));

        $client = new SoapClient($this->client, $this->httpBindingPromise);
        $client->someSoapMethod(['some-key' => 'some-value'])->wait();
    }

    public function testMagicCallUndefinedResponse(): void
    {
        $this->expectException(\Exception::class);

        $this->httpBindingPromise = new GuzzleHttp\Promise\FulfilledPromise($this->httpBindingMock);

        $this->httpBindingMock->method('request')
            ->willReturn(
                new GuzzleHttp\Psr7\Request('POST', 'www.endpoint.com')
            )
            ->with(
                'someSoapMethod',
                [['some-key' => 'some-value']]
            );

        $this->httpBindingMock->expects($this->never())->method('response');

        $this->handlerMock->append(new \Exception());

        $client = new SoapClient($this->client, $this->httpBindingPromise);
        $client->someSoapMethod(['some-key' => 'some-value'])->wait();
    }

    public function testMagicCallClientReturnSoapFault(): void
    {
        $this->expectException(\SoapFault::class);

        $this->httpBindingPromise = new GuzzleHttp\Promise\FulfilledPromise($this->httpBindingMock);

        $this->httpBindingMock->method('request')
            ->willReturn(
                new GuzzleHttp\Psr7\Request('POST', 'www.endpoint.com')
            )
            ->with(
                'someSoapMethod',
                [['some-key' => 'some-value']]
            );

        $response = new GuzzleHttp\Psr7\Response('200', [], 'body');
        $this->httpBindingMock->method('response')
            ->will(
                $this->throwException(new \SoapFault('soap fault', 'soap fault'))
            )
            ->with(
                $response,
                'someSoapMethod',
                null
            );

        $this->handlerMock->append($response);

        $client = new SoapClient($this->client, $this->httpBindingPromise);
        $client->someSoapMethod(['some-key' => 'some-value'])->wait();
    }

    public function testMagicCallSuccess(): void
    {
        $this->httpBindingPromise = new GuzzleHttp\Promise\FulfilledPromise($this->httpBindingMock);

        $this->httpBindingMock->method('request')
            ->willReturn(
                new GuzzleHttp\Psr7\Request('POST', 'www.endpoint.com')
            )
            ->with(
                'someSoapMethod',
                [['some-key' => 'some-value']]
            );

        $response = new GuzzleHttp\Psr7\Response('200', [], 'body');
        $this->httpBindingMock->method('response')
            ->willReturn(
                'SoapResult'
            )
            ->with(
                $response,
                'someSoapMethod',
                null
            );

        $this->handlerMock->append($response);

        $client = new SoapClient($this->client, $this->httpBindingPromise);
        $this->assertEquals('SoapResult', $client->someSoapMethod(['some-key' => 'some-value'])->wait());
    }

    public function testResultsAreEquivalent(): void
    {
        $this->httpBindingPromise = new GuzzleHttp\Promise\FulfilledPromise($this->httpBindingMock);

        $this->httpBindingMock->method('request')
            ->willReturn(
                new GuzzleHttp\Psr7\Request('POST', 'www.endpoint.com')
            )
            ->with(
                'someSoapMethod',
                [['some-key' => 'some-value']]
            );

        $response = new GuzzleHttp\Psr7\Response('200', [], 'body');
        $this->httpBindingMock->method('response')->willReturn(
            'SoapResult'
        );

        $this->handlerMock->append($response);
        $this->handlerMock->append($response);
        $this->handlerMock->append($response);

        $client = new SoapClient($this->client, $this->httpBindingPromise);
        $magicResult = $client->someSoapMethod(['some-key' => 'some-value'])->wait();
        $syncResult = $client->call('someSoapMethod', [['some-key' => 'some-value']]);
        $asyncResult = $client->callAsync('someSoapMethod', [['some-key' => 'some-value']])->wait();
        $this->assertEquals($magicResult, $asyncResult);
        $this->assertEquals($syncResult, $asyncResult);
    }
}
