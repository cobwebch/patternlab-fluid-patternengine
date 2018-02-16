<?php
declare(strict_types=1);
namespace Cobweb\FluidPatternEngine\Loaders;

use Cobweb\FluidPatternEngine\Hooks\HookManager;
use Cobweb\FluidPatternEngine\Traits\FluidLoader;
use \PatternLab\PatternEngine\Loader;
use TYPO3Fluid\Fluid\Exception;

class FilesystemLoader extends Loader
{
    use FluidLoader;

    public function render(array $options = [])
    {
        $this->view->assignMultiple($options['data']);
        $this->view->getRenderingContext()->setControllerAction(ucfirst($options['template']));
        try {
            $source = $this->view->getRenderingContext()->getTemplatePaths()->getTemplateSource('Default', $options['template']);
            $content = (string) $this->view->render();
            foreach (HookManager::getHookSubscriberInstances() as $hookSubscriberInstance) {
                $content = $hookSubscriberInstance->viewRendered($this->view, $this->options, $source, $content);
            }
            return $content;
        } catch (Exception $error) {
            return $error->getMessage() . ' (' . $error->getCode() . ')';
        }
    }
}
