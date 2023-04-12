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
        $this->io = new SymfonyStyle($input, $output);
        $this->logger = Logger::getLog();

        $this->log(Level::Info, self::$defaultName . ' started', $input->getOptions());

        $this->logger->debug('Creating 1000 messages');

        $messages = MessageBuilder::createMany(1000);
        $counts = [];
        /** @var Message $message */
        foreach ($messages as $message) {
            $this->logger->debug($message->jsonSerialize());
            Dot::set($counts, $message->getSystem(), Dot::get($counts,$message->getSystem(),0, '~') + 1, '~');
        }

        $report = [];
        foreach($counts as $muni => $count) $report[] = [$muni, $count];
        $this->io->table(['System', 'Count'], $report);

        $connection = new AMQPStreamConnection('rabbit', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('transactions', 'direct', false, false, false);

        foreach ($counts as $system => $count) {
            $channel->queue_declare($system . '_transaction', false, true, false, false);
            $channel->queue_bind($system . '_transaction', 'transactions', $system);
        }

        /** @var Message $message */
        foreach ($messages as $message) {
            $msg = new AMQPMessage($message->jsonSerialize());
            $channel->basic_publish($msg, 'transactions', $message->getSystem());
        }

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