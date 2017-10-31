# Magento 2 Message Queue

A simple system for passing and processing messages in Magento 2.

Comes with classes to queue messages during normal site usage, and to process queues in cron jobs

There is only one queue, but messages can be grouped using the `setGroup()` method. 

## Usage

### Adding a message to the queue
```php
# inject a queue factory into your constructor
public function __construct(
    \Creode\MessageQueue\Model\MessagequeueFactory $messageQueueFactory
) {
    $this->messageQueueFactory = $messageQueueFactory;
}

function createAMessage() {
    $message = $this->messageQueueFactory->create();

    # create messages with waiting status so they'll be picked up when processing the queue
    $message->setStatus(\Creode\MessageQueue\Model\Messagequeue::STATUS_WAITING)
        ->setMessage('a message for Creodes group')
        ->setGroup(\Your\Module\Cron\ProcessCreodesGroup::MESSAGE_GROUP) # it's recommended to use the class constant for message group
        ->setCreationTime(time())
        ->save();
}
```

### Processing messages in a cron job
#### Your/Module/etc/crontab.xml
```xml
<?xml version="1.0" ?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Cron:etc/crontab.xsd">
    <group id="message-queue-processing">
        <job instance="Your\Module\Cron\ProcessCreodesGroup" method="execute" name="creode_processqueue_creodesgroup">
            <schedule>*/5 * * * *</schedule>
        </job>
    </group>
</config>
```

#### Your/Module/Cron/ProcessCreodesGroup.php

When extending `Creode\MessageQueue\Cron\ProcessQueue` your cron job class only needs a single method - `processMessage`

Messages that are picked up have the status `waiting`

Messages are automatically marked as `processing` when the job picks them up. Further runs will not pick 
up messages that are already being processed.

The message will be marked `completed` if the `processMessage` method completes without error.

If an exception is thrown in `processMessage`, the message processing will be aborted and the message
added back into the queue by setting to the `waiting` status.

Messages can fail up to 5 times, at which
point they are marked `failed` and will not be processed in future runs.


```php
<?php

namespace Your\Module\Cron;

use Creode\MessageQueue\Cron\ProcessQueue;
use Creode\MessageQueue\Model\Messagequeue as Message;

class ProcessCreodesGroup extends ProcessQueue
{
    const MESSAGE_GROUP = 'creodes_group';

    /**
     * @inheritdoc
     */
    protected function processMessage(Message $message)
    {
        # write your code here to do _the thing_ you need the queue for

        # log the message
        $this->logger->debug('Processing Creodes Group message with contents ' . $message->getMessage());

        # your message could be an order id, customer id etc. and you can load the object
        # by injecting a repository into the constructor (but don't forget to call parent::__construct()!)
        # or it could be a json encoded object... anything you like
    }
}

```

## Housekeeping

The module comes with a cron job that cleans the queue of any messages that
were not created or updated in the last 2 months.

If you want to make changes to the clearing criteria, override the `Creode\MessageQueue\Cron\CleanQueue`
class and adjust the `cleanupMessages` method. If you just want to override the threshold then you
can leave the `cleanupMessages` method alone and simply override
 the `DATE_THRESHOLD` class constant.


