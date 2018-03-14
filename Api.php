<?php

namespace CoopTilleuls\Payum\BamboraNorthAmerica;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;

class Api
{
    const PAYMENTS_PATH = '/payments';

    protected $client;
    protected $messageFactory;
    protected $options = [];

    /**
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    public function makePayment(array $fields): array
    {
        return $this->doRequest('POST', self::PAYMENTS_PATH, $fields);
    }

    protected function doRequest(string $method, string $path, array $fields): array
    {
        $headers = [
            'Authorization' => 'Passcode '.base64_encode("{$this->options['merchant_id']}:{$this->options['api_access_passcode']}"),
            'Content-Type' => 'application/json',
        ];

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint().$path, $headers, json_encode($fields));

        $response = $this->client->send($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw HttpException::factory($request, $response);
        }

        return json_decode((string) $response->getBody(), true);
    }

    protected function getApiEndpoint(): string
    {
        return 'https://api.na.bambora.com/v1';
    }
}
