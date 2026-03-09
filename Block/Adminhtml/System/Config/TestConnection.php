<?php

declare(strict_types=1);

namespace DainoKit\Storage\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestConnection extends Field
{
    protected $_template = 'DainoKit_Storage::system/config/test_connection.phtml';

    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    public function getAjaxUrl(): string
    {
        return $this->getUrl('dainokit_storage/connection/test');
    }

    public function getButtonHtml(): string
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData([
            'id' => 'dainokit_test_connection',
            'label' => __('Test Connection'),
        ]);

        return $button->toHtml();
    }
}
