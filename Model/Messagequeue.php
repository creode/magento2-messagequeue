<?php
namespace Creode\MessageQueue\Model;

class Messagequeue extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_WAITING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_FAILED = 2;
    const STATUS_COMPLETED = 3;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Creode\MessageQueue\Model\ResourceModel\Messagequeue');
    }
}
?>
