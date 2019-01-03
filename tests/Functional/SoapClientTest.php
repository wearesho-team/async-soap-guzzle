<?php

namespace Wearesho\AsyncSoap\Guzzle\Tests\Functional;

use GuzzleHttp\Client;
use Wearesho\AsyncSoap\Guzzle\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class SoapClientTest
 * @package Wearesho\AsycSoap\Guzzle\Tests\Functional
 */
class SoapClientTest extends TestCase
{
    /** @var Factory */
    private $factory;

    protected function setUp(): void
    {
        $this->factory = new Factory();
    }

    public function testCall(): void
    {
        $client = $this->factory->create(
            new Client(),
            'http://www.webservicex.net/Statistics.asmx?WSDL'
        );
        $response = $client->call('GetStatistics', [['X' => [1,2,3]]]);
        $this->assertNotEmpty($response);
    }

    /**
     * @dataProvider webServicesProvider
     */
    public function testCallAsync($wsdl, $options, $function, $args, $contains): void
    {
        $client = $this->factory->create(
            new Client(),
            $wsdl,
            $options
        );
        $response = $client->callAsync($function, $args)->wait();
        $this->assertNotEmpty($response);
        foreach ($contains as $contain) {
            $this->assertArrayHasKey($contain, (array)$response);
        }
    }

    public function webServicesProvider(): array
    {
        return [
            [
                'wsdl' => 'http://www.webservicex.net/Statistics.asmx?WSDL',
                'options' => [],
                'function' => 'GetStatistics',
                'args' => [['X' => [1,2,3]]],
                'contains' => [
                    'Sums', 'Average', 'StandardDeviation', 'skewness', 'Kurtosis'
                ]
            ],
            [
                'wsdl' => 'http://www.webservicex.net/Statistics.asmx?WSDL',
                'options' => ['soap_version' => SOAP_1_2],
                'function' => 'GetStatistics',
                'args' => [['X' => [1,2,3]]],
                'contains' => [
                    'Sums', 'Average', 'StandardDeviation', 'skewness', 'Kurtosis'
                ]
            ],
            [
                'wsdl' => 'http://www.webservicex.net/CurrencyConvertor.asmx?WSDL',
                'options' => [],
                'function' => 'ConversionRate',
                'args' => [['FromCurrency' => 'GBP', 'ToCurrency' => 'USD']],
                'contains' => [
                    'ConversionRateResult'
                ]
            ],
            [
                'wsdl' => 'http://www.webservicex.net/CurrencyConvertor.asmx?WSDL',
                'options' => ['soap_version' => SOAP_1_2],
                'function' => 'ConversionRate',
                'args' => [['FromCurrency' => 'GBP', 'ToCurrency' => 'USD']],
                'contains' => [
                    'ConversionRateResult'
                ]
            ],
            [
                'wsdl' => 'http://www.webservicex.net/bep.asmx?WSDL',
                'options' => ['soap_version' => SOAP_1_1],
                'function' => 'BreakEvenPoint',
                'args' => [['FixedCost' => 1.1, 'VariableCost' => 1.2, 'ReturnsPerUnit' => 1.3]],
                'contains' => [
                    'BreakEvenPointResult'
                ]
            ],
            [
                'wsdl' => 'http://www.webservicex.net/bep.asmx?WSDL',
                'options' => ['soap_version' => SOAP_1_2],
                'function' => 'BreakEvenPoint',
                'args' => [['FixedCost' => 1.1, 'VariableCost' => 1.2, 'ReturnsPerUnit' => 1.3]],
                'contains' => [
                    'BreakEvenPointResult'
                ]
            ],
        ];
    }
}
