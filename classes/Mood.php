<?php

/**
 *
 */
class Mood
{

    const DEFAULT = "default";

    const ANGRY = "angry";

    const SLEEPY = "sleepy";

    const SURPRISED = "surprised";

    const DISTURBED = "disturbed";

    private static $mood_list;

    private $properties;

    function __construct(Array $map = Array())
    {
        $this->properties = $map;
    }

    /**
     * Magic __get method: look for properties in our $properties array
     *
     * @param
     *            $name
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }
        
        return null;
    }

    /**
     * Return a list of standard moods
     * 
     * TODO Parse this from an XML file
     */
    public static function list()
    {
        if (! Mood::$mood_list) {
            Mood::$mood_list = Array(
                Mood::DEFAULT => new Mood(Array(
                    "name" => "default",
                    "pupil_colour" => "black",
                    "eyelid_theta0" => 0.25 * M_PI,
                    "eyelid_theta1" => M_PI
                )),
                Mood::ANGRY => new Mood(Array(
                    "name" => "angry",
                    "pupil_colour" => "red",
                    "eyelid_theta0" => 0.05 * M_PI,
                    "eyelid_theta1" => 0.75 * M_PI
                )),
                Mood::SLEEPY => new Mood(Array(
                    "name" => "sleepy",
                    "pupil_colour" => "black",
                    "eyelid_theta0" => 0.25 * M_PI,
                    "eyelid_theta1" => M_PI,
                    "eyelid_theta2" => 1.1 * M_PI,
                    "eyelid_theta3" => 1.5 * M_PI
                )),
                Mood::SURPRISED => new Mood(Array(
                    "name" => "surprised",
                    "pupil_colour" => "black"
                )),
                Mood::DISTURBED => new Mood(Array(
                    "name" => "disturbed",
                    "pupil_colour" => "black",
                    "eyelid_theta0" => 0.25 * M_PI,
                    "eyelid_theta1" => 0.85 * M_PI,
                    "eyelid_theta2" => 1.05 * M_PI,
                    "eyelid_theta3" => 1.95 * M_PI
                ))
            );
        }
        
        return Mood::$mood_list;
    }

    static function get($label): Mood
    {
        return Mood::list()[Mood::standardize_label($label)];
    }

    private static function standardize_label($label)
    {
        switch (strtolower($label)) {
            case "sleepy":
            case "1":
                return Mood::SLEEPY;
            case "surprised":
            case "2":
                return Mood::SURPRISED;
            case "disturbed":
            case "3":
                return Mood::DISTURBED;
            case "angry":
            case "4":
                return Mood::ANGRY;
            case "default":
            case "0":
            default:
                return Mood::DEFAULT;
        }
    }
}