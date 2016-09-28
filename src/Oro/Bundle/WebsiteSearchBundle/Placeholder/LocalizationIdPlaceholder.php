<?php

namespace Oro\Bundle\WebsiteSearchBundle\Placeholder;

use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManager;

class LocalizationIdPlaceholder implements WebsiteSearchPlaceholderInterface
{
    const NAME = 'LOCALIZATION_ID';

    /**
     * @var UserLocalizationManager
     */
    private $localizationManager;

    /**
     * @param UserLocalizationManager $localizationManager
     */
    public function __construct(UserLocalizationManager $localizationManager)
    {
        $this->localizationManager = $localizationManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlaceholder()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $localization = $this->localizationManager->getCurrentLocalization();

        if (!$localization) {
            throw new \RuntimeException('Can\'t get current localization');
        }

        return (string) $localization->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function replace($string, $replaceValue)
    {
        return str_replace(self::NAME, $replaceValue, $string);
    }
}
