<?php
declare(strict_types=1);

namespace Cobweb\FluidPatternEngine\Traits;

use Cobweb\FluidPatternEngine\Emulation\EmulatingTemplateParser;
use Cobweb\FluidPatternEngine\Emulation\PatternLabViewHelperInvoker;
use Cobweb\FluidPatternEngine\Hooks\HookManager;
use Cobweb\FluidPatternEngine\PatternEngineRule;
use Cobweb\FluidPatternEngine\Resolving\PatternLabTemplatePaths;
use Cobweb\FluidPatternEngine\Resolving\PatternLabViewHelperResolver;
use PatternLab\Config;
use TYPO3Fluid\Fluid\View\TemplateView;

trait FluidLoader
{
    /**
     * @var string
     */
    protected $BOOTSTRAP_PACKAGE_SYMLINK_FOLDER = 'typo3conf/ext/site_package/Resources/Private';

    /**
     * @var TemplateView
     */
    protected $view;

    /**
     * @var PatternLabTemplatePaths
     */
    protected $templatePaths;

    /**
     * @var array
     */
    protected $options = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->getTemplatePaths();
        $this->view = new TemplateView();
        $this->view->getRenderingContext()->setTemplatePaths($this->templatePaths);
        $this->view->getRenderingContext()->setTemplateParser(new EmulatingTemplateParser());
        $this->view->getRenderingContext()->setViewHelperInvoker(new PatternLabViewHelperInvoker());
        $this->view->getRenderingContext()->setViewHelperResolver(new PatternLabViewHelperResolver());

        // Resetting f namespaces to point on patterlab ViewHelpers
        $this->view->getRenderingContext()->getViewHelperResolver()->setNamespaces(array('f'=>['TYPO3\CMS\Fluid\ViewHelpers']));

        $this->view->getRenderingContext()->getViewHelperResolver()->addNamespace('plio', 'PatternLab\\ViewHelpers');
        foreach (Config::getOption(PatternEngineRule::OPTION_NAMESPACES) ?? [] as $namespaceName => $namespaces) {
            $this->view->getRenderingContext()->getViewHelperResolver()->addNamespace($namespaceName,
                (array)$namespaces);
        }

        foreach (HookManager::getHookSubscriberInstances() as $hookSubscriberInstance) {
            $this->view = $hookSubscriberInstance->viewCreated($this->view);
        }
    }

    /**
     * Sets the paths for the current template
     */
    public function getTemplatePaths(){
        $this->templatePaths = new PatternLabTemplatePaths();
        $this->templatePaths->setFormat(Config::getOption("patternExtension"));
        $this->templatePaths->setLayoutRootPaths([
            Config::getOption("styleguideKitPath") . DIRECTORY_SEPARATOR . 'Resources/Private/Layouts/',
            Config::getOption("sourceDir") . DIRECTORY_SEPARATOR . $this->getBootstrapPackageSymlinkFolder() .'/Layouts/',
        ]);
        $this->templatePaths->setPartialRootPaths([
            Config::getOption("styleguideKitPath") . DIRECTORY_SEPARATOR . 'Resources/Private/Partials/',
            Config::getOption("sourceDir") . DIRECTORY_SEPARATOR . $this->getBootstrapPackageSymlinkFolder() .'/Partials/ContentElements/',
            Config::getOption("sourceDir") . DIRECTORY_SEPARATOR . $this->getBootstrapPackageSymlinkFolder() .'/Partials/Page/',
            Config::getOption("sourceDir") . DIRECTORY_SEPARATOR . '_patterns/'
        ]);
        $this->templatePaths->setTemplateRootPaths([
            Config::getOption("styleguideKitPath") . DIRECTORY_SEPARATOR . 'Resources/Private/Templates/',
            Config::getOption("sourceDir") . DIRECTORY_SEPARATOR . $this->getBootstrapPackageSymlinkFolder() .'/Templates/ContentElements/',
            Config::getOption("sourceDir") . DIRECTORY_SEPARATOR . $this->getBootstrapPackageSymlinkFolder() .'/Templates/Page/',
        ]);
    }


    /**
     *
     * @return string
     */
    public function getBootstrapPackageSymlinkFolder(){
        return $this->BOOTSTRAP_PACKAGE_SYMLINK_FOLDER;
    }
}
