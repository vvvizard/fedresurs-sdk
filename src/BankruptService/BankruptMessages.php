<?php

namespace FedResSdk\BankruptService;

use FedResSdk\BankruptService\BankruptServiceClient;
use FedResSdk\Authorization\Authorization;
use GuzzleHttp\Psr7\Request;

/**
 * Client for getting BankruptService Messages
 */
class  BankruptMessages extends  BankruptServiceClient
{
    /**
     * @param \FedResSdk\Authorization\Authorization $auth
     */
    public function __construct(Authorization $auth)
    {
        parent::__construct($auth);
    }


    public function getMessagesIds()
    {
        $data = $this->getMessages();
        return array_column($data['pageData'], 'guid');
    }


    public function getMessage($id)
    {
        $url = self::ROUTE_MESSAGES . $id;
        $response = $this->apiRequest("GET", $url);
        $data = json_decode($response, true);
        return $data;
    }




    /**
     * Gets list of messages with all data from inner cards 
     * @param array $messagesIds
     * @param mixed $params
     * @return array
     */
    public function getMessagesWithCards(array $messagesIds, $params = [])
    {
        if (!empty($params)) {
            $this->setParams($params);
        }
        $requests = [];
        $responses = [];
        $countRequest = 0;
        foreach ($messagesIds as $key => $messageId) {
            $url = self::ROUTE_MESSAGES . "/" . $messageId;
            $requests[] = new Request('GET', $this->mainUrl . $url,  $this->headers, $this->body);
            $countRequest++;
            if ($countRequest == self::MAX_QUERY_LIMIT || $countRequest >= count($messagesIds)) {
                $response = $this->poolRequest($requests);
                $response = $this->createMessagesWithCards($response);
                $responses = array_merge($responses, $response);
                $countRequest = 0;
                $requests = [];
            }
        }
        return $responses;
    }

    /**
     * creates message card 
     * @param array $messages
     * @return array
     */
    protected function createMessagesWithCards(array $messages)
    {
        $dictionary = $this->getDictionary();
        $resultMessages = [];
        foreach ($messages as $message) {
            $cardData = $this->parseXml($message['content'], $message['type']);
            if (isset($cardData['files']) && $cardData['files'] === true) {
                $fileName  = $this->downloadDir . "/" . $this->messagesType . "/" . $message['guid'] . '.zip';
                $message['files'] = $this->getFiles($message['guid'], $fileName);
            }
            $message['typeText'] = $dictionary->getMessageTypeString($message['type']);
            $message['content'] = $cardData;
            $message['link'] = self::MESSAGE_LINK_TPL . $this->guidToLinkFormat($message['guid']);
            $linkedMessages = $this->getLinkedMessages($message['guid']);
            $message['linked'] = $this->formatLinkedMessages($linkedMessages, $message['guid']);

            $resultMessages[] = $message;
        }
        return $resultMessages;
    }

    /**
     * formats data about linked messages
     * @param mixed $linkedMessages
     * @return array
     */
    protected function formatLinkedMessages($linkedMessages, $messageId)
    {
        $dictionary = $this->getDictionary();
        $formatedMessages = [];
        foreach ($linkedMessages as $linkedMessage) {
            if ($linkedMessage['guid'] !== $messageId) {
                $formatedMessages[] = [
                    'guid' => $linkedMessage['guid'],
                    'typeText' => $dictionary->getMessageTypeString($linkedMessage['type']),
                    'type' => $linkedMessage['type'],
                    'link' => self::MESSAGE_LINK_TPL . $this->guidToLinkFormat($linkedMessage['guid']),
                    'datePublish' => $linkedMessage['datePublish'],
                ];
            }
        }
        return $formatedMessages;
    }
}
