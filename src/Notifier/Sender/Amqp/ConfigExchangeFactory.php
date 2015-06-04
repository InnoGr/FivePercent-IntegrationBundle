<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\Notifier\Sender\Amqp;

use FivePercent\Component\Notifier\Sender\Amqp\AmqpExchangeFactoryInterface;

/**
 * Factory for create exchange via configuration
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class ConfigExchangeFactory implements AmqpExchangeFactoryInterface
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $vhost;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $exchangeName;

    /**
     * @var string
     */
    private $exchangeType;

    /**
     * Construct
     *
     * @param string $host
     * @param int    $port
     * @param string $vhost
     * @param string $login
     * @param string $password
     * @param string $exchangeName
     * @param string $exchangeType
     */
    public function __construct($host, $port, $vhost, $login, $password, $exchangeName, $exchangeType = 'direct')
    {
        $this->host = $host;
        $this->port = $port;
        $this->vhost = $vhost;
        $this->login = $login;
        $this->password = $password;
        $this->exchangeName = $exchangeName;
        $this->exchangeType = $exchangeType;
    }

    /**
     * {@inheritDoc}
     */
    public function createExchange()
    {
        // Create AMQP connection
        $amqpConnection = new \AMQPConnection([
            'host' => $this->host,
            'port' => $this->port,
            'vhost' => $this->vhost,
            'login' => $this->login,
            'password' => $this->password
        ]);

        $amqpConnection->connect();

        // Create channel
        $channel = new \AMQPChannel($amqpConnection);

        // Create exchange
        $exchange = new \AMQPExchange($channel);
        $exchange->setName($this->exchangeName);
        $exchange->setType($this->exchangeType);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();

        return $exchange;
    }
}