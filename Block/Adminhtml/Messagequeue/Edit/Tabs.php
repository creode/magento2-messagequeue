<?php
namespace Creode\MessageQueue\Block\Adminhtml\Messagequeue\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('messagequeue_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Message Details'));
    }
}
