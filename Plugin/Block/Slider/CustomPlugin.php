<?php

namespace GhoSter\AmastyImageOptimizerForWeltPixel\Plugin\Block\Slider;

use Amasty\ImageOptimizer\Model\OptionSource\ReplaceStrategies;
use Amasty\ImageOptimizer\Model\Output\ReplaceConfig\ReplaceConfig;
use Amasty\ImageOptimizer\Model\Output\ReplaceConfig\ReplaceConfigFactory;
use Amasty\ImageOptimizer\Plugin\Catalog\Block\Product\View\Gallery\ProductGalleryReplace;
use Amasty\PageSpeedTools\Model\Image\ReplaceByPatternApplier;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class CustomPlugin
{
    /**
     * @var ReplaceConfig
     */
    private $replaceConfig;

    /**
     * @var ReplaceByPatternApplier
     */
    private $replaceByPatternApplier;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var string|null
     */
    protected ?string $mediaUrl = null;

    public function __construct(
        ReplaceByPatternApplier $replaceByPatternApplier,
        ReplaceConfigFactory $replaceConfigFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->replaceConfig = $replaceConfigFactory->create();
        $this->replaceByPatternApplier = $replaceByPatternApplier;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetSliderConfiguration($subject, $result)
    {
        if (!isset($result['banner_config'])) {
            return $result;
        }
        $banners = $result['banner_config'];

        $replaceStrategy = $this->replaceConfig->getData(ReplaceConfig::REPLACE_STRATEGY);
        if (!empty($result)
            && $replaceStrategy !== ReplaceStrategies::NONE
        ) {
            foreach ($banners as $key => $banner) {
                foreach (['image', 'mobile_image', 'thumb_image'] as $imageType) {
                    if (empty($banner[$imageType])) {
                        continue;
                    }
                    try {
                        $imageUrl = $this->getMediaUrl() . $banner[$imageType];
                        $image = [$imageUrl];
                        $this->replaceByPatternApplier->execute(ProductGalleryReplace::REPLACE_PATTERN_GROUP, $image);
                        $banner[$imageType] = str_replace(
                            $this->getMediaUrl(),
                            '',
                            $image[array_key_first($image)]
                        );
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                $banners[$key] = $banner;
            }
        }

        $result['banner_config'] = $banners;

        return $result;
    }

    /**
     * Get Media Url
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl(): ?string
    {
        if (!$this->mediaUrl) {
            $this->mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        }

        return $this->mediaUrl;
    }
}
