<?php

namespace Comfino\Widget;

enum WidgetTypeEnum: string
{
    case WIDGET_SIMPLE = 'simple';
    case WIDGET_MIXED = 'mixed';
    case WIDGET_WITH_CALCULATOR = 'with-modal';
    case WIDGET_WITH_EXTENDED_CALCULATOR = 'extended-modal';
}
