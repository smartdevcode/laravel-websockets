<?php
/**
 * Created by PhpStorm.
 * User: freek
 * Date: 2018-11-23
 * Time: 22:01
 */

namespace BeyondCode\LaravelWebSockets\Server;

use Exception;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;
use Ratchet\RFC6455\Messaging\Message;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

class Logger implements MessageComponentInterface
{
    /** @var \Ratchet\Http\HttpServerInterface */
    protected $app;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    protected $consoleOutput;

    /** @var bool */
    protected $enabled;

    public function __construct(MessageComponentInterface $app)
    {
        $this->app = $app;

        /*
        $this->consoleOutput = $consoleOutput;

        $this->enabled = $enabled;
        */
    }

    public function enable()
    {
        $this->enabled = true;
    }

    public function onOpen(ConnectionInterface $connection)
    {
        $request = $connection->httpRequest;

        $queryParameters = [];
        parse_str($request->getUri()->getQuery(), $queryParameters);

        $this->warn("New connection opened for app key {$queryParameters['appKey']}.");

        $this->app->onOpen($connection);
    }

    public function onMessage(ConnectionInterface $connection, MessageInterface $message)
    {
        $this->info("{$connection->appId}: connection id {$connection->socketId} received message: {$message->getPayload()}.");

        $this->app->onMessage($connection, $message);
    }

    public function onClose(ConnectionInterface $connection)
    {
        $this->warn("{$connection->appId}: connection id {$connection->socketId} closed.");

        $this->app->onClose($connection);
    }

    public function onError(ConnectionInterface $connection, Exception $exception)
    {
        $exceptionClass = get_class($exception);

        $message = "{$connection->appId}: execption `{$exceptionClass}` thrown: `{$exception->getMessage()}`";

        /*
         * TODO: add verbose option
        if ($this->isVerbose) {
            $message .= $exception->getTraceAsString();
        }
        */

        $this->error($message);

        $this->app->onError($connection, $exception);
    }

    protected function info(string $message)
    {
        $this->line($message, 'info');
    }

    protected function warn(string $message)
    {
        $this->line($message, 'warning');
    }

    protected function error(string $message)
    {
        $this->line($message, 'error');
    }

    protected function line(string $message, string $style)
    {
        echo $message;

        return;

        $styled = $style ? "<$style>$message</$style>" : $message;

        $this->consoleOutput->writeln($styled);
    }




}