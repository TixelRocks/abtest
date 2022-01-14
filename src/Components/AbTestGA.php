<?php

namespace Tixel\AbTest\Components;

use Illuminate\View\Component;

class AbTestGA extends Component
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
if (typeof gtag == \'function\') {
gtag(\'event\', \''.$this->action.'\', ' . json_encode(['event_category' => $this->category, 'event_label' => $this->label, 'transport_type' => 'beacon']) . ');
}
</script>';
    }
}
