<?php

namespace App\Traits;

use App\Enums\CarProviderEnum;

trait CarProviderTrait
{
    public function setProviderByClassName(): void
    {
        $classPaths = explode('\\', get_class($this));

        $className = end($classPaths);

        $classNameParts =
            array_values(
                array_filter(
                    preg_split('/(?=[A-Z])/', $className)
                )
            );

        $key = mb_strtolower($classNameParts[0]);

        if ($key == 'usados') {
            $key = 'usadosbr';
        
        } elseif ($key == 'i') {
            $key = 'icarros';
        }

        $provider = CarProviderEnum::fromValue($key);

        $this->provider = $provider;
    }
}
