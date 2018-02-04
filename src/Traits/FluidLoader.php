<?php
declare(strict_types=1);

namespace NamelessCoder\FluidPatternEngine\Traits;

use NamelessCoder\FluidPatternEngine\Emulation\EmulatingTemplateParser;
use NamelessCoder\FluidPatternEngine\Emulation\PatternLabViewHelperInvoker;
use NamelessCoder\FluidPatternEngine\Hooks\HookManager;
use NamelessCoder\FluidPatternEngine\Resolving\PatternLabTemplatePaths;
use NamelessCoder\FluidPatternEngine\Resolving\PatternLabViewHelperResolver;
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
        $this->view->getRenderingContext()->getViewHelperResolver()->addNamespace('plio', 'PatternLab\\ViewHelpers');
        foreach (Config::getOption('fluidNamespaces') ?? [] as $namespaceName => $namespaces) {
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
