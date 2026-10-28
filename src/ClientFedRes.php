<?php

namespace FedResSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use FedResSdk\Authorization\Authorization;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;

abstract class ClientFedRes
{

    protected array $headers;
    protected mixed $body;
    protected mixed $mainUrl;
    protected mixed $route;
    protected mixed $credentials;
    protected Client $client;
    protected Authorization $auth;
    protected mixed $dictionary = null;
    protected string $sort = 'DATE:asc';
    protected int $limit = 50;
    protected int $offset = 0;
    protected mixed $dateBegin;
    protected mixed $dateEnd;
    protected mixed $datePublishBegin;
    protected mixed $datePublishEnd;
    protected mixed $messagesType;
    protected ?bool $includeContent = null;

    protected const DEFAULT_DAYS_INTERVAL = 1;
    protected const DEFAULT_CONCURRENCY = 5;

    protected bool $enableAsync = false;

    public function __construct(Authorization $auth)
    {
        $this->auth = $auth;
        $this->client = new Client();
        $this->initDates();
        $this->headers = [
            'Content-Type' => 'application/json'
        ];
    }

    abstract public function auth();
    abstract public function checkToken();
    abstract public function setAuthHeaders($token);
    abstract public function getMessages();



    /**
     * Sending a request to the API
     * @param mixed $method
     * @param mixed $url
     */
    protected function apiRequest($method, $url): mixed
    {
        try {
            $request = new Request($method, $this->mainUrl . $url, $this->headers, $this->body);

            if ($this->enableAsync) {
                return $this->client->sendAsync($request);
            }

            $response = $this->client->sendAsync($request)->wait();
        } catch (\InvalidArgumentException $e) {
            echo "bad query";
            var_dump($e->getMessage());
        } catch (\Exception $e) {
            $response = $e->getResponse();
            $status = $response->getStatusCode();

            if ($status == 400) {
                echo "bad request";
                var_dump($e->getMessage());
                var_dump($response);
                die();
            }
            if ($status == 401) {
                $errorToken = $response->getHeaderLine('WWW-Authenticate');
                if (str_contains($errorToken, "invalid_token")) {
                    $this->auth->deleteToken();
                }
                if ($this->auth->getAttemps() > 2) {
                    echo "auth error";
                    die();
                }
                return $this->auth();
            }
            die();
        }
        return $response->getBody();
    }

    /**
     * Sending a pool of requests to the API
     * @param mixed $requests
     * @return array
     */
    protected function poolRequest($requests)
    {
        $client = $this->client;
        $data = [];
        $pool = new Pool($client, $requests, [
            'concurrency' => self::DEFAULT_CONCURRENCY,
            'fulfilled' => function (Response $response, $index) use (&$data) {
                $data[] = json_decode($response->getBody()->getContents(), true);
            },
            'rejected' => function (RequestException $reason, $index) {},
        ]);

        $promise = $pool->promise();
        $promise->wait();

        return $data;
    }

    protected function parseResponse(mixed $response)
    {
        if ($response instanceof Stream) {
            $response = (string) $response;
        } elseif (!is_string($response)) {
            return $response;
        }

        return json_decode($response, true);
    }

    /**
     * Saving attached zip file
     * @param mixed $url
     * @param mixed $saveTo
     * @param string $method
     */  public function getZip(mixed $url, mixed $saveTo, string  $method = "GET")
    {
        $headers = $this->headers;
        $headers['Content-Type'] = "application/zip";
        $headers['sink'] = $saveTo;
        $this->client->request($method, $this->mainUrl . $url, ['headers' => $headers, 'sink' => $saveTo]);
    }

    public function setMainUrl(mixed $mainUrl)
    {
        $this->mainUrl = $mainUrl;
    }

    public function setRoute(mixed $route)
    {
        $this->route = $route;
    }

    protected function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    protected function setBody(mixed $body)
    {
        $this->body = $body;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    public function setOffset(int $offset)
    {
        $this->offset = $offset;
    }

    public function setDates(mixed $dateBegin, mixed $dateEnd)
    {
        $this->dateBegin = $dateBegin;
        $this->dateEnd = $dateEnd;
        $this->datePublishBegin = $dateBegin;
        $this->datePublishEnd = $dateEnd;
    }

    public function setMessagesType(mixed $messagesType)
    {
        $this->messagesType = $messagesType;
    }

    public function setSort(string $sort)
    {
        $this->sort = $sort;
    }

    /**
     * Setting any params from array,
     * like $this->setParams(['limit' => 100, 'offset' => 0]);
     * @param mixed $params
     * @return void
     */
    public function setParams(array $params = [])
    {
        $classParams = get_class_vars(static::class);
        foreach ($params as $key => $value) {
            if (array_key_exists($key, $classParams)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Setting any params from json,
     * like $this->setParamsFromJson('{"limit": 100, "offset": 0}');
     * @param mixed $json
     * @return void
     */
    public function setParamsFromJson(mixed $json)
    {
        $params = json_decode($json, true);
        $this->setParams($params);
    }

    /**
     * getting all params to JSON
     * like $this->limit, $this->offset and etc.
     * @return bool|string
     */
    public function getParamsToJson()
    {
        return json_encode(get_object_vars($this));
    }

    public function initDates(int $daysInterval = self::DEFAULT_DAYS_INTERVAL)
    {
        $hours24 = 60 * 60 * 24;
        $this->dateBegin = date('Y-m-d', time() - $daysInterval * $hours24);
        $this->dateEnd = date('Y-m-d', time());
    }

    public function enableAsync()
    {
        $this->enableAsync = true;
    }

    public function disableAsync()
    {
        $this->enableAsync = false;
    }

    public function enableIncludeContentOn()
    {
        $this->includeContent = true;
    }

    public function disableIncludeContent()
    {
        $this->includeContent = false;
    }

    public function isAuthorized()
    {
        if ($this->auth->issetToken()) {
            return true;
        }

        if ($this->auth->loadToken()) {
            $token = $this->auth->getToken();
            $this->setAuthHeaders($token);
            return true;
        }

        return false;
    }
}
