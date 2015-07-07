<?php

namespace Arrilot\Widgets;

use Arrilot\Widgets\Contracts\ApplicationWrapperContract;
use Arrilot\Widgets\Misc\ViewExpressionTrait;

class WidgetGroup
{
    use ViewExpressionTrait;

    /**
     * The widget group name.
     *
     * @var string
     */
    protected $name;

    /**
     * The application wrapper.
     *
     * @var ApplicationWrapperContract
     */
    protected $app;

    /**
     * The array of widgets to display in this group.
     *
     * @var array
     */
    protected $widgets = [];

    /**
     * The position of a widget in this group.
     *
     * @var int
     */
    protected $position = 100;

    /**
     * @param $name
     * @param ApplicationWrapperContract $app
     */
    public function __construct($name, ApplicationWrapperContract $app)
    {
        $this->name = $name;

        $this->app = $app;
    }

    /**
     * Display all widgets from this group in correct order.
     *
     * @return string
     */
    public function display()
    {
        ksort($this->widgets);

        $output = '';
        foreach ($this->widgets as $position => $widgets) {
            foreach ($widgets as $widget) {
                $output .=  $this->displayWidget($widget);
            }
        }

        return $this->convertToViewExpression($output);
    }

    /**
     * Set widget position.
     *
     * @param int $position
     *
     * @return $this
     */
    public function position($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Add a widget to the group.
     */
    public function addWidget()
    {
        $this->addWidgetWithType('sync', func_get_args());
    }

    /**
     * Add an async widget to the group.
     */
    public function addAsyncWidget()
    {
        $this->addWidgetWithType('async', func_get_args());
    }

    /**
     * Getter for position.
     *
     * @return array
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Display a widget according to its type.
     *
     * @param $widget
     *
     * @return mixed
     */
    protected function displayWidget($widget)
    {
        $factory = $this->app->make($widget['type'] === 'sync' ? 'arrilot.widget' : 'arrilot.async-widget');

        return call_user_func_array([$factory, 'run'], $widget['arguments']);
    }

    /**
     * Reset the position property back to the default.
     * So it does not affect the next widget.
     */
    protected function resetPosition()
    {
        $this->position = 100;
    }

    /**
     * Add a widget with a given type to the array.
     *
     * @param string $type
     * @param array  $arguments
     */
    protected function addWidgetWithType($type, array $arguments = [])
    {
        if (!isset($this->widgets[$this->position])) {
            $this->widgets[$this->position] = [];
        }

        $this->widgets[$this->position][] = [
            'arguments' => $arguments,
            'type'      => $type,
        ];

        $this->resetPosition();
    }
}
