<?php

declare(strict_types=1);

namespace App\MessageHandler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;
use TexWrapper\Wrapper;
use Vrok\PdfService\Contracts\GeneratePdfMessage;
use Vrok\PdfService\Contracts\PdfResultMessage;

class GeneratePdfMessageHandler implements MessageHandlerInterface, ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    private const TEX_COMMAND = 'lualatex --file-line-error --interaction=nonstopmode --output-directory=%dir% %file% 2>&1';

    /**
     * Generate PDF file from received latex.
     */
    public function __invoke(GeneratePdfMessage $message)
    {
	$this->logger()->debug('recieving mess');

        $tex = $message->getLatex();

        $wrapper = new Wrapper();
        $wrapper->setCommand(self::TEX_COMMAND);

        $saved = $wrapper->saveTex($tex);
        if (!$saved) {
            // unrecoverable because probably within the next seconds - minutes
            // the probable filesystem problem will not be resolved
            throw new UnrecoverableMessageHandlingException('LaTeX file could not be created!');
        }

        $result = $wrapper->buildPdf();
        if (!$result) {
            foreach ($wrapper->getErrors() as $error) {
                $this->logger()->debug($error);
            }

            // unrecoverable because the probably invalid LaTex or the LuaTex
            // error will not resolve itself
            throw new UnrecoverableMessageHandlingException('PDF file wasn\'t created');
        }

        $pdf = file_get_contents($wrapper->getPdfFile());
        $wrapper->deleteTex();
        unlink($wrapper->getPdfFile());

        // base64 for safe serialization
        return new PdfResultMessage(base64_encode($pdf));
    }

    protected function logger(): LoggerInterface
    {
        return $this->container->get(__METHOD__);
    }
}
