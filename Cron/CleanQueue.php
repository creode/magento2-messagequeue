<?php


namespace Creode\MessageQueue\Cron;

use Creode\MessageQueue\Model\Messagequeue as Message;

class CleanQueue
{
    const DATE_THRESHOLD = '-2 month';
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Creode\MessageQueue\Model\MessagequeueFactory
     */
    protected $messageQueueFactory;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Creode\MessageQueue\Model\MessagequeueFactory $messageQueueFactory
    ) {
        $this->logger = $logger;
        $this->messageQueueFactory = $messageQueueFactory;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $this->cleanupMessages();
    }

    /**
     * Deletes messages older than the threshold
     * @return void
     */
    private function cleanupMessages()
    {
        $cutoffDate = (new \DateTime())->modify(static::DATE_THRESHOLD);
        $formattedCutoffDate = $cutoffDate->format('Y-m-d H:i:s');

        $this->logger->debug('Deleting messages older than ' . $formattedCutoffDate);

        $messageQueue = $this->messageQueueFactory->create();

        $messageCollection = $messageQueue->getCollection()
            ->addFieldToFilter(
                'status', 
                [
                    'in' =>
                    [
                        Message::STATUS_FAILED,
                        Message::STATUS_COMPLETED
                    ]
                ]
            )
            ->addFieldToFilter(
                'update_time',
                ['lteq' => $formattedCutoffDate]
            );

        $this->logger->debug('Deleting ' . count($messageCollection) . ' messages');

        $messageCollection->walk('delete');
    }
}
