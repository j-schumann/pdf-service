<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Vrok\MessengerReply\ReplyToStamp;
use Vrok\PdfService\Contracts\GeneratePdfMessage;

class TestPdfCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'pdf:test';

    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus, string $name = null)
    {
        $this->bus = $bus;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription('Push a test GeneratePdfMessage to the input queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $m = new GeneratePdfMessage(
        '\documentclass{article}
            \begin{document}
            \title{Introduction to \LaTeX{}}
            \author{Author Name}
            \maketitle
            \section{Introduction}
            Here is the text of your introduction.
            \end{document}'
        );
        $m->setTask('research');
        $m->setIdentifier(\uniqid('', true));

        $e = new Envelope($m);
        $this->bus->dispatch($e
            ->with(new ReplyToStamp('pdf-output'))
            ->with(new AmqpStamp('pdf-input'))
        );

        return 0;
    }
}
