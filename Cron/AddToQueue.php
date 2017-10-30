<?php


namespace Creode\MessageQueue\Cron;

use Creode\MessageQueue\Model\Messagequeue as Message;
use Creode\MessageQueue\Cron\ProcessQueue;

abstract class AddToQueue
{
    const MESSAGE_GROUP = 'default';

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
        $message = $this->createMessage();

        $this->populateMessage($message);

        $this->insertMessage($message);
    }

    /**
     * Creates a message object
     * @return Message
     */
    private function createMessage()
    {
        $message = $this->messageQueueFactory->create();
        
        $message->setStatus(Message::STATUS_WAITING);

        return $message;
    }

    /**
     * Saves the message
     * @param Message $message 
     * @return void
     */
    private function insertMessage($message)
    {
        $message
            ->setCreationTime(time())
            ->save();
    }

    /**
     * Populates the message contents.
     * @param Message $message 
     * @return void
     */
    abstract protected function populateMessage(Message $message);
}
