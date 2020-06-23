<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class WorkerEventSubscriber
    implements EventSubscriberInterface, ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageReceivedEvent::class => 'onMessageReceived',
        ];
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event)
    {
        $this->logger()->debug('resetting logger');
        $this->logger()->reset();
        $this->logger()->debug('post reset');
    }

    protected function logger(): LoggerInterface
    {
        return $this->container->get(__METHOD__);
    }
}
