<?php
namespace Creode\MessageQueue\Model\ResourceModel;

class Messagequeue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('creode_messagequeue', 'id');
    }
}
?>
