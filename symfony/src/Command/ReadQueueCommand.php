<?php

namespace Chs\Messages\Command;

use Chs\Messages\Util\Logger;
use Monolog\Level;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReadQueueCommand extends Command
{

    protected $io;

    protected $logger;

    protected static $defaultName = 'queue:read-queue-service';
    protected static $defaultDescription = 'Read from a provided queue';

    protected function configure()
    {
        $this->addArgument('queue', InputArgument::REQUIRED, 'The name of the queue to read from');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->logger = Logger::getLog();

        $queue = $input->getArgument('queue');

        $this->log(Level::Info, self::$defaultName . ' started', $input->getOptions());

        /**
         * Connect to the RabbitMQ service and get a channel object from the service
         */
        $connection = new AMQPStreamConnection('rabbit', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $this->io->writeln(" [*] Waiting for logs. To exit press CTRL+C");

        /* the callback function will be called whenever the consumer is provided with a new message */
        $callback = function ($msg) {
            $this->io->writeln(' [x] ' . $msg->getRoutingKey() . ':' . $msg->body);
        };

        /* these two lines set up the consumer service and the wait loop which will run until broken or connection failure */
        $channel->basic_consume($queue, '', false, true, false, false, $callback);
        while ($channel->is_open()) {
            $channel->wait();
        }

        /**
         * Clean up the connections
         */
        $channel->close();
        $connection->close();

        $this->log(Level::Info, self::$defaultName . ' completed');
        return self::SUCCESS;
    }

    /**
     * A message or array of messages to log to the output
     *
     * @param Level $level
     * @param array|string $message
     * @param array $context
     * @return void
     */
    protected function log(Level $level, array|string $message, array $context = []): void
    {
        $messages = \is_array($message) ? array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->logger->log($level, $message, $context);
        }
    }
}
