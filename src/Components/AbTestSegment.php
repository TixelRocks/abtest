<?php

namespace Tixel\AbTest\Components;

use Illuminate\View\Component;

class AbTestSegment extends Component
{
    public $category;

    public $action;

    public $label;

    public function __construct($category, $action, $label)
    {
        $this->category = $category;
        $this->action = $action;
        $this->label = $label;
    }

    public function render()
    {
        return '<script type="text/javascript">
if (typeof analytics == \'object\') {
analytics.track(\''.$this->action.'\', ' . json_encode(['category' => $this->category, 'label' => $this->label, 'transport_type' => 'beacon']) . ');
}
</script>';
    }
}
