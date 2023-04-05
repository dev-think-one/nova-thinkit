<?php

namespace NovaThinKit\Nova\ResourceTemplates;

use Laravel\Nova\Resource;

class TemplateFinder
{
    public static array $templatesMap = [];

    public static function templatesMap(string $modelClass, array $map = null, $merge = true)
    {
        if (is_array($map)) {
            collect($map)->each(function ($templateClass, $templateName) {
                if (!is_string($templateName)) {
                    throw new \Exception('Template name should be string');
                }
                if (!is_a($templateClass, ResourceTemplate::class, true)) {
                    throw new \Exception('Template class should extends ResourceTemplate');
                }
            });
            static::$templatesMap[$modelClass] = $merge && (static::$templatesMap[$modelClass] ?? false)
                ? $map + static::$templatesMap[$modelClass] : $map;
        }

        return static::$templatesMap[$modelClass] ?? [];
    }

    public static function templatesNames(string $modelClass): array
    {
        return collect(static::templatesMap($modelClass))
            ->mapWithKeys(function ($templateClass, $templateName) {
                return [$templateName => $templateClass::name()];
            })->all();
    }


    public static function find(string $template, Resource $resource): ?ResourceTemplate
    {
        $class = static::templatesMap($resource::$model)[$template] ?? null;
        if ($class
            && is_string($class)
            && is_a($class, ResourceTemplate::class, true)) {
            return new $class($resource);
        }

        return null;
    }
}
