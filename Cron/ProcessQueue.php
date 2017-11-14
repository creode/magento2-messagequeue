<?php


namespace Creode\MessageQueue\Cron;

use Creode\MessageQueue\Model\Messagequeue as Message;

abstract class ProcessQueue
{
    const MESSAGE_GROUP = 'default';

    /**
     * @var int
     */
    protected $maxFailures = 5;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Creode\MessageQueue\Model\MessagequeueFactory
     */
    protected $messageQueueFactory;

    /**
     * @var array
     */
    protected $messages = [];

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
        $this->pullMessages();

        $this->processMessages();
    }


    private function pullMessages()
    {
        $this->logger->debug('Pulling messages of type ' . static::MESSAGE_GROUP);

        $messageQueue = $this->messageQueueFactory->create();

        $this->messages = $messageQueue->getCollection()
            ->addFieldToFilter(
                'status',
                ['eq' => Message::STATUS_WAITING]
            )
            ->addFieldToFilter(
                'group',
                ['eq' => static::MESSAGE_GROUP]
            );
    }

    private function processMessages()
    {
        if (count($this->messages) == 0) {
            $this->logger->debug('No messages to process');
            return;
        }

        foreach ($this->messages as $message) {
            try {
                $this->markMessageProcessing($message);

                $this->logger->debug('Processing message ' . $message->getId());
                $this->processMessage($message);

                $this->logger->debug('Completed processing message ' . $message->getId() . ' - SUCCESS');
                $this->markMessageProcessed($message);
            } catch (\Exception $e) {
                $message->setFailures(
                    $message->getFailures() + 1
                );

                $this->logger->debug('WARNING: Failure #' . $message->getFailures() . ' processing message ' . $message->getId() . ' - MESSAGE: ' . $e->getMessage());

                if ($message->getFailures() >= $this->maxFailures) {
                    $this->markMessageFailed($message);
                } else {
                    $this->putMessageBackInQueue($message);
                }
            }
        }
    }

    /**
     * Marks a message as processing so that no other job picks it up
     * @param Message $message 
     * @return void
     */
    private function markMessageProcessing(Message $message) 
    {
        $this->logger->debug('Marking message ' . $message->getId() . ' as processing');

        $this->updateMessageStatus($message, Message::STATUS_PROCESSING);
    }

    /**
     * Marks a message as processed to indicate that it is complete
     * @param Message $message 
     * @return void
     */
    private function markMessageProcessed(Message $message)
    {
        $this->logger->debug('Marking message ' . $message->getId() . ' as processed');

        $this->updateMessageStatus($message, Message::STATUS_COMPLETED);
    }

    /**
     * Marks a message as failed after it hits the failure limit
     * @param Message $message 
     * @return void
     */
    private function markMessageFailed(Message $message)
    {
        $this->logger->debug('Marking message ' . $message->getId() . ' as failed. It will not be processed');

        $this->updateMessageStatus($message, Message::STATUS_FAILED);
    }

    /**
     * Puts a message back in the queue after failure but before it hits the cancel threshold
     * @param Message $message 
     * @return void
     */
    private function putMessageBackInQueue(Message $message)
    {
        $this->logger->debug('Putting message ' . $message->getId() . ' back into the queue after ' . $message->getFailures() . ' failures');

        $this->updateMessageStatus($message, Message::STATUS_WAITING);
    }

    /**
     * Updates the message status
     * @param Message $message 
     * @param string $status 
     * @return Message
     */
    protected function updateMessageStatus(Message $message, $status) 
    {
        $message
            ->setStatus($status)
            ->setUpdateTime(time())
            ->save();

        return $message;
    }

    /**
     * Processes the message.
     * @param Message $message 
     * @throws Exception when processing fails
     * @return mixed
     */
    abstract protected function processMessage(Message $message);
}
