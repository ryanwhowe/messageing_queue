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

class ReadQueueToEndCommand extends Command{

    protected $io;

    protected $logger;

    protected static $defaultName = 'queue:read-queue-toend';
    protected static $defaultDescription = 'Read from a provided queue and terminate when queue is empty';

    protected function configure() {
        $this->addArgument('queue', InputArgument::REQUIRED, 'The name of the queue to read from');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $exchange_name = 'transactions';

        $this->io = new SymfonyStyle($input, $output);
        $this->logger = Logger::getLog();

        $queue = $input->getArgument('queue');

        $this->log(Level::Info, self::$defaultName . ' started', $input->getOptions());

        /**
         * Connect to the RabbitMQ service and get a channel object from the service
         */
        $connection = new AMQPStreamConnection('rabbit', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $this->io->note(" Getting Messages from Queue");

        /* get a message from the requested queue */
        $msg = $channel->basic_get($queue, true);

        $counter = 0;
        while (null !== $msg){
            $this->io->writeln(' [x] '. $msg->getRoutingKey(). ':'. $msg->body);
            $msg = $channel->basic_get($queue, true);
            $counter++;
        }

        $this->log(Level::Info, "${counter} messages processed");

        /**
         * Clean up the connections
         */
        $channel->close();
        $connection->close();

        $this->io->note("${queue} messages processed, ${counter} messages read");
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
    protected function log(Level $level, array|string $message, array $context = []) {
        $messages = \is_array($message) ? array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->logger->log($level, $message, $context);
        }
    }
}
