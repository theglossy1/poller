<?php

namespace Poller\Pipelines;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use RuntimeException;

class Fetcher
{
    public function fetch()
    {
        $client = new Client();
        try {
            $response = $client->post(getenv('SONAR_INSTANCE_URL') . 'api/poller', [
                'headers' => [
                    'User-Agent' => "SonarPoller/" . getenv('SONAR_POLLER_VERSION') ?? 'Unknown',
                    'Accept' => 'application/json',
                    'Accept-Encoding' => 'gzip',
                ],
                'timeout' => 30,
                'json' => [
                    'api_key' => getenv('SONAR_POLLER_API_KEY'),
                    'version' => getenv('SONAR_POLLER_VERSION')
                ]
            ]);
            $data = json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            $response = $e->getResponse();
            try {
                $message = json_decode($response->getBody()->getContents());
                throw new RuntimeException($message->error->message);
            } catch (Exception $e) {
                throw new RuntimeException($e->getMessage());
            }
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        return $data;
    }
}
