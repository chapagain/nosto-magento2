<?php
/**
 * Copyright (c) 2020, Nosto Solutions Ltd
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
 * @copyright 2020 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */

namespace Nosto\Tagging\Model\Product\Sku;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection
    as ConfigurableAttributeCollection;
use Magento\Store\Model\Store;
use Nosto\Model\Product\SkuCollection;
use Nosto\Tagging\Logger\Logger as NostoLogger;
use Nosto\Tagging\Model\Product\Repository as NostoProductRepository;
use Nosto\Tagging\Model\Product\Sku\Builder as NostoSkuBuilder;
use Nosto\Types\Product\SkuInterface;

class Collection
{
    private $configurableType;
    private $logger;
    private $nostoSkuBuilder;
    private $nostoProductRepository;

    /**
     * Builder constructor.
     * @param NostoLogger $logger
     * @param ConfigurableType $configurableType
     * @param Builder $nostoSkuBuilder
     * @param NostoProductRepository $nostoProductRepository
     */
    public function __construct(
        NostoLogger $logger,
        ConfigurableType $configurableType,
        NostoSkuBuilder $nostoSkuBuilder,
        NostoProductRepository $nostoProductRepository
    ) {
        $this->configurableType = $configurableType;
        $this->logger = $logger;
        $this->nostoSkuBuilder = $nostoSkuBuilder;
        $this->nostoProductRepository = $nostoProductRepository;
    }

    /**
     * @param Product $product
     * @param Store $store
     * @return SkuCollection
     * @throws Exception
     * @suppress PhanUndeclaredMethod
     */
    public function build(Product $product, Store $store)
    {
        $skuCollection = new SkuCollection();
        if ($product->getTypeId() === ConfigurableType::TYPE_CODE) {
            $configurableAttributes = $this->getConfigurableAttributes($product);
            /** @var ConfigurableType $productTypeInstance */
            $productTypeInstance = $product->getTypeInstance();
            $usedProducts = $productTypeInstance->getUsedProducts($product);
            /** @var Product $product */
            foreach ($usedProducts as $usedProduct) {
                /** @var Product $usedProduct */
                if (!$usedProduct->isDisabled()) {
                    $sku = $this->nostoSkuBuilder->build($usedProduct, $store, $configurableAttributes);
                    if ($sku instanceof SkuInterface) {
                        $skuCollection->append($sku);
                    }
                }
            }
        }
        return $skuCollection;
    }

    /**
     * @param Product $product
     * @return ConfigurableAttributeCollection
     */
    public function getConfigurableAttributes(Product $product)
    {
        /* @var ConfigurableAttributeCollection $attributes */
        $attributes = $this->configurableType->getConfigurableAttributes($product);
        /**
         * Returning \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute[]
         * but declared to return ConfigurableAttributeCollection
         */
        /** @phan-suppress-next-line PhanTypeMismatchReturn */
        return $attributes;
    }
}
