<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-04
 * Time: 6:13 PM
 */

namespace Aragil\Http;


class Response
{
    private static $sent = false;

    private $content;
    private $status;
    private $headers;

    public function __construct($content, $status = 200, $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    /**
     * @return void
     */
    public function send()
    {
        if(self::$sent) {
            return;
        }

        http_response_code($this->status);
        $this->parseContenet();
        $this->setHeaders();

        echo $this->content;
    }

    /**
     * @param $header
     * @param $value
     */
    public function addHeader($header, $value)
    {
        $this->headers[$header] = $value;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return void
     */
    private function setHeaders()
    {
        foreach ($this->headers as $header => $value) {
            header("{$header}: {$value}");
        }
    }

    /**
     * @return void
     */
    private function parseContenet()
    {
        switch (true) {
            case is_array($this->content):
                $this->content = json_encode($this->content);
                $this->addHeader('Content-Type', 'application/json');
                break;
            case ($this->content instanceof self):
                $this->headers = $this->content->getHeaders();
                $this->status = $this->content->getStatus();
                $this->content = $this->content->getContent();
                break;
        }
    }
}
