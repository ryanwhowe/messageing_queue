<?php
namespace Chs\Messages\Command;

use Chs\Messages\Entity\Message;
use Chs\Messages\Entity\MessageBuilder;
use Chs\Messages\Util\Dot;
use Chs\Messages\Util\Logger;
use Monolog\Level;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateSystemMessagesCommand extends Command {

    protected $io;

    protected $logger;

    protected static $defaultName = 'queue:generate-system-messages';
    protected static $defaultDescription = 'Generate test messages to the transaction exchange and separate by systems';

    protected function execute(InputInterface $input, OutputInterface $output) {

        $exchange_name = 'transaction';

        $this->io = new SymfonyStyle($input, $output);
        $this->logger = Logger::getLog();

        $this->log(Level::Info, self::$defaultName . ' started', $input->getOptions());

        $this->logger->debug('Creating 1000 messages');

        /**
         * Create 1000 messages with either egov or k2 as a destination system
         */
        $messages = MessageBuilder::createMany(1000);
        $counts = [];
        /** @var Message $message */
        foreach ($messages as $message) {
            $this->logger->debug($message->jsonSerialize());
            Dot::set($counts, $message->getSystem(), Dot::get($counts,$message->getSystem(),0, '~') + 1, '~');
        }

        /**
         * Generate a report of the random message municipality breakdown
         */
        $report = [];
        foreach($counts as $system => $count) $report[] = [$system, $count];
        $this->io->table(['System', 'Count'], $report);

        /**
         * Connect to the RabbitMQ service and get a channel object from the service
         */
        $connection = new AMQPStreamConnection('rabbit', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        /**
         * We utilize a message exchange that is able to broadcast to multiple topic recipients
         */
        $channel->exchange_declare($exchange_name, 'topic', false, false, false);

        /* This is a special topic queue, this will get ALL messages from the exchange routed to it */
        $channel->queue_declare('all_transactions', false, true, false, false);
        $channel->queue_bind('all_transactions', $exchange_name, '#');

        /* declaring the queues and bindings each run ensure that they exist, if they already do nothing is done */
        foreach ($counts as $system => $count) {
            $channel->queue_declare($system . '_transaction', false, true, false, false);
            $channel->queue_bind($system . '_transaction', $exchange_name, $system);
        }

        /* loop through all the messages and send them to the single exchange to be routed the the correct queue */
        /** @var Message $message */
        foreach ($messages as $message) {
            $msg = new AMQPMessage($message->jsonSerialize());
            $channel->basic_publish($msg, $exchange_name, $message->getSystem());
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
    protected function log(Level $level, array|string $message, array $context = []) {
        $messages = \is_array($message) ? array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->logger->log($level, $message, $context);
        }
    }
}