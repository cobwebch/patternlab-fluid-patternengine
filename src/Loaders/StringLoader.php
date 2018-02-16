<?php
declare(strict_types=1);
namespace Cobweb\FluidPatternEngine\Loaders;

use Cobweb\FluidPatternEngine\Hooks\HookManager;
use Cobweb\FluidPatternEngine\Traits\FluidLoader;
use \PatternLab\PatternEngine\Loader;
use TYPO3Fluid\Fluid\Exception;

class StringLoader extends Loader
{
    use FluidLoader;

    public function render(array $options = [])
    {
        $this->view->assignMultiple($options['data']);
        $this->view->getTemplatePaths()->setTemplateSource($options['string']);
        try {
            $content = (string) $this->view->render();
            foreach (HookManager::getHookSubscriberInstances() as $hookSubscriberInstance) {
                $content = $hookSubscriberInstance->viewRendered($this->view, $this->options, $options['string'], $content);
            }
            return $content;
        } catch (Exception $error) {
            return $error->getMessage() . ' (' . $error->getCode() . ')';
        }
    }
}
