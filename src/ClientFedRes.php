<?php

namespace FedResSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use FedResSdk\Authorization\Authorization;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;

abstract class ClientFedRes
{
    protected $type;
    protected $headers;
    protected $body;
    protected $mainUrl;
    protected $route;
    protected $credentials;
    protected $client;
    protected $auth;

    protected $sort = 'DATE:asc';
    protected $limit = 50;
    protected $offset = 0;
    protected $dateBegin;
    protected $dateEnd;
    protected $datePublishBegin;
    protected $datePublishEnd;
    protected $messagesType;

    protected const DEFAULT_DAYS_INTERVAL = 1;
    protected const DEFAULT_CONCURRENCY = 5;

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
    abstract public function setAuthHeaders($token);
    abstract public function getMessages();
    abstract public function getAllMessages();

    protected function apiRequest($method, $url)
    {
        try {
            $request = new Request($method, $this->mainUrl . $url, $this->headers, $this->body);
            $response = $this->client->sendAsync($request)->wait();
        } catch (\InvalidArgumentException $e) {
            echo "bad query";
            var_dump($e->getMessage());
        } catch (\Exception $e) {
            echo "Exception";
            var_dump($e);
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
                $this->auth();
            }
            die();
        }
        return $response->getBody();
    }

    protected function poolRequest($requests)
    {
        $client = $this->client;
        $data = [];
        $pool = new Pool($client, $requests, [
            'concurrency' => self::DEFAULT_CONCURRENCY,
            'fulfilled' => function (Response $response, $index) use(&$data) {
                $data[] = json_decode($response->getBody()->getContents(), true);
            },
            'rejected' => function (RequestException $reason, $index) {},
        ]);

        $promise = $pool->promise();
        $promise->wait();

        return $data;
    }

    public function setMainUrl($mainUrl)
    {
        $this->mainUrl = $mainUrl;
    }

    public function setRoute($route)
    {
        $this->route = $route;
    }

    protected function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    protected function setBody($body)
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

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    public function setDates($dateBegin, $dateEnd)
    {
        $this->dateBegin = $dateBegin;
        $this->dateEnd = $dateEnd;
        $this->datePublishBegin = $dateBegin;
        $this->datePublishEnd = $dateEnd;
    }

    public function setMessagesType($messagesType)
    {
        $this->messagesType = $messagesType;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function setParams($params = [])
    {
        $classParams = get_class_vars(static::class);
        foreach ($params as $key => $value) {
            if (array_key_exists($key,$classParams)) {
                $this->$key = $value;
            }
        }
    }
    public function setParamsFromJson($json)
    {
        $params = json_decode($json, true);
        $this->setParams($params);
    }

    public function getParamsToJson()
    {
        return json_encode(get_object_vars($this));
    }

    public function initDates($daysInterval = self::DEFAULT_DAYS_INTERVAL )
    {
        $hours24 = 60 * 60 * 24;
        $this->dateBegin = date('Y-m-d', time() - $daysInterval * $hours24);
        $this->dateEnd = date('Y-m-d', time());
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
