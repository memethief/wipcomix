<?php

/**
 * Abstract class representing an object that may be placed in a panel. This object may have sub-components, each of
 * which is also a PanelObject. The object is moody and orientable.
 */
abstract class PanelObject implements SVGObject
{

    // An array of sub-objects, each registered as a listener for some of our methods
    protected $components = Array();
    
    // x-coordinate of this object
    protected $x;
    // y-coordinate of this object
    protected $y;

    // Mood of this object
    protected $mood;

    // Orientation of this object
    protected $orientation;

    /**
     * Given a string label and a PanelObject, add the object to our array of components (indexed by the label),
     * thereby registering it as a listener for our methods
     *
     * TODO make this a fluent interface?
     *
     * @param string $label
     * @param PanelObject $component
     */
    protected function register(string $label, PanelObject $component)
    {
        $this->components[$label] = $component;
    }
    
    /**
     * Given a Mood object, set the mood of this object and all registered sub-objects.
     *
     * @param Mood $mood
     */
    public function set_location(float $x, float $y)
    {
        $this->x = $x;
        $this->y = $y;
        foreach ($this->components as $component) {
            $component->set_location($x, $y);
        }
    }
    
    /**
     * Given a Mood object, set the mood of this object and all registered sub-objects.
     *
     * @param Mood $mood
     */
    public function set_mood(Mood $mood)
    {
        //echo "<!-- Setting mood of " . get_class($this) . " to " . $mood->name . " -->\n";
        $this->mood = $mood;
        foreach ($this->components as $component) {
            $component->set_mood($mood);
        }
    }
    
    /**
     * Given a float, set the orientation of this object and all registered sub-objects.
     *
     * @param float $orientation
     */
    public function set_orientation(float $orientation)
    {
        $this->orientation = $orientation;
        foreach ($this->components as $component) {
            $component->set_orientation($orientation);
        }
    }

    /**
     * Magic __get method: alias unknown properties to elements of our $components array.
     *
     * @param string $name
     * @return mixed|NULL
     */
    public function __get(string $name)
    {
        //echo "Dynamic get of property $name\n";
        if (array_key_exists($name, $this->components)) {
            return $this->components[$name];
        }
        
        return null;
    }

    /**
     * Magic __set method: alias unknown properties to elements of our $components array.
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        //echo "Dynamic set of property $name\n";
        if ($value instanceof PanelObject)
            $this->register($name, $value);
        elseif ($value == null)
            unset($this->components[$name]);
        else 
        {
            $trace = debug_backtrace();
            trigger_error(
                'Invalid property value via __set(): ' . $name .
                ' with type: ' . (gettype($value)) .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE);
        }
    }

    /**
     * Magic __clone method: Make sure that when this object is cloned all of its sub-objects are cloned as well.
     */
    function __clone()
    {
        foreach ($this->components as $label => $component)
            $this->$label = clone $component;
    }
}