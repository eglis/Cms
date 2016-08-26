<?php
namespace Cms\View\Helper;

use Cms\Service\BlockService;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Block extends AbstractHelper
{

    /**
     * @var LanguageService
     */
    protected $service;

    /**
     * @var MvcTranslator
     */
    protected $translator;

    /**
     * LanguageCodes constructor.
     * @param LanguageService $service
     * @param \Zend\Mvc\I18n\Translator $translator
     */
    public function __construct(BlockService $service, \Zend\Mvc\I18n\Translator $translator)
    {
        $this->service = $service;
        $this->translator = $translator;
    }

    public function __invoke($block, $showAlertbox = true)
    {
        $strBlock = null;

        if (!empty($block)) {
            $locale = $this->translator->getLocale();
            $fallback = $this->translator->getFallbackLocale();

            // check and get the locale version if it is not exists a fallback version will be print
            $theBlock = $this->service->findByPlaceholder($block, $locale);
            if (!empty($theBlock)) {
                $strBlock = $theBlock->getContent();
            } else {
                // Check if the fallback locale version is present
                $theBlock = $this->service->findByPlaceholder($block, $fallback);
                if (!empty($theBlock)) {
                    $strBlock = $theBlock->getContent();
                } else {

                    try {
                        return $this->view->$block();
                    } catch (\Exception $e) {

                    }
                    if ($showAlertbox) {
                        $strBlock = "<div class=\"alert alert-danger\">" . sprintf($this->translator->translate("Block %s%s%s doesn't found!"), "<strong>", $block, " ($locale)</strong>") . "</div>";
                    } else {
                        $strBlock = sprintf($this->translator->translate("Block %s%s%s doesn't found!"), "", $block, "");
                    }
                }
            }
        }
        return $strBlock;
    }

}