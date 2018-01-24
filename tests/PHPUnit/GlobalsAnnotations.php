<?php
declare(strict_types=1);

namespace Zalas\BundleTest\Tests\PHPUnit;

/**
 * Enables @env and @server annotations on test classes and methods.
 *
 * Examples (with "@" omitted so they're not parsed by PHPUnit):
 *
 * <code>
 * env APP_ENV=bar
 * server APP_DEBUG=1
 * server FOO=
 * </code>
 */
trait GlobalsAnnotations
{
    protected function checkRequirements()
    {
        parent::checkRequirements();

        $globalVars = $this->getGlobalVariableAnnotations();

        if (!empty($globalVars['env'])) {
            foreach ($globalVars['env'] as $name => $value) {
                $_ENV[$name] = $value;
            }
        }
        if (!empty($globalVars['server'])) {
            foreach ($globalVars['server'] as $name => $value) {
                $_SERVER[$name] = $value;
            }
        }
    }

    private function getGlobalVariableAnnotations(): array
    {
        $annotations = $this->getAnnotations();

        $globalVarAnnotations = array_filter(
            array_merge_recursive($annotations['class'], $annotations['method']),
            function ($annotationName) {
                return in_array($annotationName, ['env', 'server']);
            },
            ARRAY_FILTER_USE_KEY
        );

        return array_map(function ($annotations) {
            return array_reduce($annotations, function ($carry, $annotation) {
                if (!strpos($annotation, '=')) {
                    $carry[$annotation] = '';
                } else {
                    list($name, $value) = explode('=', $annotation, 2);
                    $carry[$name] = $value;
                }

                return $carry;
            }, []);
        }, $globalVarAnnotations);
    }
}
