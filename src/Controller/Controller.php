<?php

namespace silverorange\DevTest\Controller;

use silverorange\DevTest\Context;
use silverorange\DevTest\Template;

abstract class Controller
{
    protected \PDO $db;

    /**
     * @var array<string>
     */
    protected array $params = [];

    /**
     * @param \PDO  $db
     * @param array<string> $params
     */
    public function __construct(\PDO $db, array $params)
    {
        $this->setDatabase($db)->setParams($params);
        $this->loadData();
    }

    public function setDatabase(\PDO $db): self
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @param array<string> $params
     *
     * @return $this
     */
    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->getProtocol() . ' 200 OK';
    }

    public function getContentType(): string
    {
        return 'text/html';
    }

    abstract public function getContext(): Context;
    abstract public function getTemplate(): Template\Template;

    public function sendHeaders(): void
    {
        header($this->getStatus());
        header('Content-Type: ' . $this->getContentType());
    }

    protected function getProtocol(): string
    {
        return is_string($_SERVER['SERVER_PROTOCOL'])
            ? $_SERVER['SERVER_PROTOCOL']
            : 'HTTP/1.0';
    }

    protected function loadData(): void
    {
    }
}
