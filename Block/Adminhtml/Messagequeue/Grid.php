<?php
namespace Creode\MessageQueue\Block\Adminhtml\Messagequeue;

use Creode\MessageQueue\Model\Messagequeue as Message;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Creode\MessageQueue\Model\messagequeueFactory
     */
    protected $_messagequeueFactory;

    /**
     * @var \Creode\MessageQueue\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Creode\MessageQueue\Model\messagequeueFactory $messagequeueFactory
     * @param \Creode\MessageQueue\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Creode\MessageQueue\Model\MessagequeueFactory $MessagequeueFactory,
        \Creode\MessageQueue\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_messagequeueFactory = $MessagequeueFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_messagequeueFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		
				$this->addColumn(
					'message',
					[
						'header' => __('Message'),
						'index' => 'message',
					]
				);
				
				$this->addColumn(
					'group',
					[
						'header' => __('Group'),
						'index' => 'group',
					]
				);
				
						
						$this->addColumn(
							'status',
							[
								'header' => __('Status'),
								'index' => 'status',
								'type' => 'options',
								'options' => \Creode\MessageQueue\Block\Adminhtml\Messagequeue\Grid::getOptionArray2()
							]
						);
					
                $this->addColumn(
                    'failures',
                    [
                        'header' => __('Failures'),
                        'index' => 'failures',
                        'type' => 'number'
                    ]
                );

						
				$this->addColumn(
					'creation_time',
					[
						'header' => __('Created'),
						'index' => 'creation_time',
						'type'      => 'datetime',
					]
				);
					
					
				$this->addColumn(
					'update_time',
					[
						'header' => __('Updated'),
						'index' => 'update_time',
						'type'      => 'datetime',
					]
				);
					
					


		
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		
		   $this->addExportType($this->getUrl('messagequeue/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('messagequeue/*/exportExcel', ['_current' => true]),__('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('id');
        //$this->getMassactionBlock()->setTemplate('Creode_MessageQueue::messagequeue/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('messagequeue');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('messagequeue/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('messagequeue/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('messagequeue/*/index', ['_current' => true]);
    }

    /**
     * @param \Creode\MessageQueue\Model\messagequeue|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'messagequeue/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray2()
		{
            $data_array=array(); 
			$data_array[Message::STATUS_WAITING]='waiting';
			$data_array[Message::STATUS_PROCESSING]='processing';
			$data_array[Message::STATUS_FAILED]='failed';
            $data_array[Message::STATUS_COMPLETED]='complete';
            return($data_array);
		}
		static public function getValueArray2()
		{
            $data_array=array();
			foreach(\Creode\MessageQueue\Block\Adminhtml\Messagequeue\Grid::getOptionArray2() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		

}
