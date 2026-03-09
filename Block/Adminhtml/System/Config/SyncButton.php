<?php

declare(strict_types=1);

namespace DainoKit\Storage\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SyncButton extends Field
{
    protected $_template = 'DainoKit_Storage::system/config/sync_button.phtml';

    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    public function getSyncUrl(): string
    {
        return $this->getUrl('dainokit_storage/connection/sync');
    }

    public function getButtonHtml(): string
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData([
            'id' => 'dainokit_sync_button',
            'label' => __('Sync Media Files'),
        ]);
        return $button->toHtml();
    }
}
