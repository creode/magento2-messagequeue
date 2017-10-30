<?php

namespace Creode\MessageQueue\Model\ResourceModel\Messagequeue;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Creode\MessageQueue\Model\Messagequeue', 'Creode\MessageQueue\Model\ResourceModel\Messagequeue');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>