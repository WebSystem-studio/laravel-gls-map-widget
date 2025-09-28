<?php

namespace WebSystem\GlsMapWidget\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \WebSystem\GlsMapWidget\GlsMapWidget
 */
class GlsMapWidget extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \WebSystem\GlsMapWidget\GlsMapWidget::class;
    }
}
