<?php

declare(strict_types=1);

namespace Onliner\CommandBusBundle\Command;

use Onliner\CommandBus\Dispatcher;
use Onliner\CommandBus\Remote\AMQP\Queue;
use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Transport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCommand extends Command
{
    private ?Consumer $consumer = null;

    public function __construct(
        private Transport $transport,
        private Dispatcher $dispatcher,
        private array $config = []
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('commands:process')
            ->addArgument('pattern', InputArgument::REQUIRED, 'Routing pattern to subscribe')
            ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User for which run workers')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setupUser($input);
        $this->subscribeSignals();

        $pattern = $input->getArgument('pattern');
        $options = $this->config['queues'][$pattern] ?? [
            'durable' => true,
        ];

        $options['pattern'] = $pattern;

        $this->consumer = $this->transport->consume();
        $this->consumer->consume(Queue::create($options));
        $this->consumer->run($this->dispatcher, $this->config['options'] ?? []);

        return self::SUCCESS;
    }

    private function setupUser(InputInterface $input): void
    {
        $user = $input->getOption('user');

        if (empty($user)) {
            return;
        }

        $data = ctype_digit($user) ? posix_getpwuid((int) $user) : posix_getpwnam($user);

        posix_setgid($data['gid']);
        posix_setuid($data['uid']);
    }

    private function subscribeSignals(): void
    {
        pcntl_async_signals(true);

        foreach ([SIGINT, SIGTERM] as $signal) {
            pcntl_signal($signal, function () {
                if (!$this->consumer) {
                    return;
                }

                $this->consumer->stop();
            });
        }
    }
}
