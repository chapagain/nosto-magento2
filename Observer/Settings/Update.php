<?php
/**
 * Copyright (c) 2017, Nosto Solutions Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2017 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */

namespace Nosto\Tagging\Observer\Settings;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\StoreManagerInterface;
use Nosto\Tagging\Helper\Data as NostoHelperData;
use Nosto\Tagging\Model\Account\Settings\Service as NostoSettingsService;
use Psr\Log\LoggerInterface;

/**
 * Observer to update the account settings for each of the store views if the module is enabled and
 * an account exists for the store view.
 *
 * @package Nosto\Tagging\Observer\Settings
 */
class Update implements ObserverInterface
{
    private $storeManager;
    private $logger;
    private $moduleManager;
    private $nostoSettingsService;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param ModuleManager $moduleManager
     * @param StoreManagerInterface $storeManager
     * @param NostoSettingsService $nostoSettingsService
     */
    public function __construct(
        LoggerInterface $logger,
        ModuleManager $moduleManager,
        StoreManagerInterface $storeManager,
        NostoSettingsService $nostoSettingsService
    ) {
        $this->logger = $logger;
        $this->moduleManager = $moduleManager;
        $this->nostoSettingsService = $nostoSettingsService;
        $this->storeManager = $storeManager;
    }

    /**
     * Observer method to update the account settings for each for the store views by invoking the
     * settings management service
     *
     * @param Observer $observer the dispatched event
     */
    public function execute(Observer $observer)
    {
        if (!$this->moduleManager->isEnabled(NostoHelperData::MODULE_NAME)) {
            return;
        }

        $this->logger->info('Updating settings to Nosto for all store views');
        foreach ($this->storeManager->getStores(false) as $store) {
            $this->logger->info('Updating settings for ' . $store->getName());
            if ($this->nostoSettingsService->update($store)) {
                $this->logger->info('Successfully updated the settings for the store view');
            } else {
                $this->logger->warning('Unable to update the settings for the store view');
            }
        }
    }
}
