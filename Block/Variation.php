<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Nosto
 * @package   Nosto_Tagging
 * @author    Nosto Solutions Ltd <magento@nosto.com>
 * @copyright Copyright (c) 2013-2017 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Nosto\Tagging\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\Store;
use Nosto\Tagging\Helper\Account as NostoHelperAccount;

/**
 * Page type block used for outputting the variation identifier on the different pages.
 */
class Variation extends Template
{
    private $nostoHelperAccount;

    /**
     * Constructor.
     *
     * @param Template\Context $context
     * @param NostoHelperAccount $nostoHelperAccount
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        NostoHelperAccount $nostoHelperAccount,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->nostoHelperAccount = $nostoHelperAccount;
    }

    /**
     * Return the current variation id
     *
     * @return string
     */
    public function getVariationId()
    {
        /** @var Store $store */
        $store = $this->_storeManager->getStore(true);
        return $store->getCurrentCurrencyCode();
    }

    /**
     * Checks if store uses more than one currency in order to decide whether to hide or show the
     * nosto_variation tagging.
     *
     * @return bool a boolean value indicating whether the store has more than one currency
     */
    public function hasMultipleCurrencies()
    {
        /** @var Store $store */
        $store = $this->_storeManager->getStore(true);
        return count($store->getAvailableCurrencyCodes(true)) > 1;
    }

    /**
     * Overridden method that only outputs any markup if the extension is enabled and an account
     * exists for the current store view.
     *
     * @return string the markup or an empty string (if an account doesn't exist)
     */
    protected function _toHtml()
    {
        if ($this->nostoHelperAccount->nostoInstalledAndEnabled($this->_storeManager->getStore())) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
