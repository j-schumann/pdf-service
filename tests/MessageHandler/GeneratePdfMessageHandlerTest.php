<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\MessageHandler\GeneratePdfMessageHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Vrok\PdfService\Contracts\GeneratePdfMessage;
use Vrok\PdfService\Contracts\PdfResultMessage;

class GeneratePdfMessageHandlerTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testErrorsOnInvalidTex()
    {
        $msg = new GeneratePdfMessage('invalid LaTeX');
        /** @var GeneratePdfMessageHandler $handler */
        $handler = self::$container->get(GeneratePdfMessageHandler::class);

        $this->expectException(UnrecoverableMessageHandlingException::class);
        $this->expectExceptionMessage('PDF file wasn\'t created');

        $handler($msg);
    }

    public function testReturnsPdfResultMessage()
    {
        $msg = new GeneratePdfMessage(
            '\documentclass{article}
            \begin{document}
            \title{Introduction to \LaTeX{}}
            \author{Author Name}
            \maketitle
            \section{Introduction}
            Here is the text of your introduction.
            \end{document}'
        );

        /** @var GeneratePdfMessageHandler $handler */
        $handler = self::$container->get(GeneratePdfMessageHandler::class);

        $result = $handler($msg);
        $this->assertInstanceOf(PdfResultMessage::class, $result);

        $pdfString = base64_decode($result->getPdfContent());
        $this->assertSame(0, strpos($pdfString, '%PDF-1.5'));
    }
}
