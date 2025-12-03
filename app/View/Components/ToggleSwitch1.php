<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ToggleSwitch1 extends Component
{
    public $id;
    public $name;
    public $checked;

    /**
     * Create a new component instance.
     */
    public function __construct($id, $name, $checked = false)
    {
        $this->id = $id;
        $this->name = $name;
        $this->checked = $checked;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.toggle-switch-1');
    }
}
