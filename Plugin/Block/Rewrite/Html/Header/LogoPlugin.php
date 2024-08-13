<?php

namespace GhoSter\AmastyImageOptimizerForWeltPixel\Plugin\Block\Rewrite\Html\Header;

use Amasty\ImageOptimizer\Model\OptionSource\ReplaceStrategies;
use Amasty\ImageOptimizer\Model\Output\ReplaceConfig\ReplaceConfig;
use Amasty\ImageOptimizer\Model\Output\ReplaceConfig\ReplaceConfigFactory;
use Amasty\ImageOptimizer\Plugin\Catalog\Block\Product\View\Gallery\ProductGalleryReplace;
use Amasty\PageSpeedTools\Model\Image\ReplaceByPatternApplier;

class LogoPlugin
{
    /**
     * @var ReplaceConfig
     */
    private $replaceConfig;

    /**
     * @var ReplaceByPatternApplier
     */
    private $replaceByPatternApplier;

    public function __construct(
        ReplaceByPatternApplier $replaceByPatternApplier,
        ReplaceConfigFactory $replaceConfigFactory
    ) {
        $this->replaceConfig = $replaceConfigFactory->create();
        $this->replaceByPatternApplier = $replaceByPatternApplier;
    }

    public function afterGetTypeLogoSrc($subject, $result)
    {
        return $this->replaceImage($result);
    }

    public function afterGetLogoSrc($subject, $result)
    {
        return $this->replaceImage($result);
    }

    private function replaceImage($result)
    {
        $replaceStrategy = $this->replaceConfig->getData(ReplaceConfig::REPLACE_STRATEGY);
        if (!empty($result)
            && $replaceStrategy !== ReplaceStrategies::NONE
        ) {
            $image = [$result];
            $this->replaceByPatternApplier->execute(ProductGalleryReplace::REPLACE_PATTERN_GROUP, $image);
            $result = $image[array_key_first($image)];
        }

        return $result;
    }
}
