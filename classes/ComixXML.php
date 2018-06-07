<?php

/**
 * class to handle XML-run comics
 */
class ComixXML implements ComixFactory
{

    private static $basedir = ".";

    private static $stripdir = "./strips";

    private static $playerdir = "./players";

    public static $singleton = null;

    function __construct()
    {
    }

    /* factory method */
    static function get()
    {
        if (ComixXML::$singleton == null)
            ComixXML::$singleton = new ComixXML();
        return ComixXML::$singleton;
    }

    /**
     * fetch a strip and return its object.
     * If $num is passed, we look for a file that matches it, and
     * throw an exception if none is found.
     * If no $num is passed, we take the most recent strip.
     * TODO get smart about identifying unpublished strips
     * TODO make sure code injection is impossible
     */
    public function fetch_strip($num = null)
    {
        $strip_file = '';
        // If a null number was passed, we find the latest strip.
        if ($num == null) {
            $strip_file = $this->get_current_strip_filename();
        } else {
            // Sanitize input and convert the number into a 0-padded string
            $id = str_pad(filter_var($num, FILTER_VALIDATE_INT), 6, "0", STR_PAD_LEFT);
            
            $strip_file = ComixXML::$stripdir . "/strip_$id.xml";
        }
        
        // Parse the strip file
        $strip = $this->parse_strip($strip_file);
        // echo("print.<pre>".print_r($obj,true)."</pre><br />");
        return $strip;
    }

    /**
     * Get the filename of the current strip
     *
     * Currently this simply finds the last sorted filename of the form "strip_*.xml"
     *
     * TODO Make "current strip" detection smarter
     */
    function get_current_strip_filename()
    {
        $pattern = ComixXML::$stripdir . "/strip_*.xml";
        $strips = glob($pattern);
        if (count($strips) == 0)
            throw new Exception("No strip found with ID '$id'!");
        
        return end($strips);
    }

    /**
     * Given a filename, we wish to parse it into an object suitable to
     * send into a Strip object.
     */
    function parse_strip($file): Strip
    {
        if (!file_exists($file)) 
            throw new Exception("File not found: $file");
        
        $xml = new SimpleXMLElement(file_get_contents($file));
        $strip = new Strip();
        debug("\$xml<pre>" . print_r($xml, true) . "</pre><br />");
        if ($xml->striptitle) {
            $strip->title = ((string) $xml->striptitle);
        }
        if ($xml->strippubdate) {
            $strip->date = ((string) $xml->strippubdate);
        }
        foreach ($xml->playerdef as $xmlplayer) {
            $id = ((string) $xmlplayer['id']);
            $source = ((string) $xmlplayer['source']);
            $href = ((string) $xmlplayer['href']);
            $player = $this->parse_player($source, $href);
            $strip->characters[$id] = $player;
        }
        debug("Strip characters<pre>" . print_r($strip->characters, true) . "</pre><br />");
        foreach ($xml->panel as $xmlpanel) {
            $panelnumber = ((int) $xmlpanel->panel_number);
            $panel = $strip->add_panel($panelnumber);
            // $panel = new Panel();
            // $panel->number = ((int)$xmlpanel->panel_number);
            $panel->left = ((int) $xmlpanel['xoffset']);
            $panel->top = ((int) $xmlpanel['yoffset']);
            $panel->width = ((int) $xmlpanel['xsize']);
            $panel->height = ((int) $xmlpanel['ysize']);
            foreach ($xmlpanel->player as $xmlplayer) {
                $player_name = ((string) $xmlplayer['name']);
                $player = clone ($strip->characters[$player_name]);
                //echo "<!--\n" . var_export($player) . "\n-->";
                //$player = new Character();
                $player->ID = ((string) $xmlplayer['name']);
                $player->set_location((int) $xmlplayer['xoffset'], (int) $xmlplayer['yoffset']);
                //$player->left = ((int) $xmlplayer['xoffset']);
                //$player->top = ((int) $xmlplayer['yoffset']);
                if (isset($xmlplayer['mood'])) {
                    $player->set_mood(Mood::get((int) $xmlplayer['mood']));
                }
                //player->set_mood(Mood::get($xmlplayer->mood ? (string)$xmlplayer->mood : Mood::DEFAULT));
                $player->set_orientation((float) $xmlplayer['facing']);
                $panel->add_character($player);
            }
            foreach ($xmlpanel->paneltext as $xmltext) {
                $txt = new TextPanel();
                $txt->left = ((int) $xmltext['xoffset']);
                $txt->top = ((int) $xmltext['yoffset']);
                $txt->width = ((int) $xmltext['xsize']);
                $txt->height = ((int) $xmltext['ysize']);
                $txt->content = ((string) $xmltext);
                $panel->add_text($txt);
            }
        }
        foreach ($xml->paneltext as $xmltext) {
            $txt = $strip->add_text();
            $txt->left = ((int) $xmltext['xoffset']);
            $txt->top = ((int) $xmltext['yoffset']);
            $txt->width = ((int) $xmltext['xsize']);
            $txt->height = ((int) $xmltext['ysize']);
            $txt->content = ((string) $xmltext);
            // $strip->add_panel($txt);
        }
        debug("Strip<pre>" . print_r($strip, true) . "</pre><br />");
        return $strip;
    }

    /**
     * Parse character information from an XML data file
     * we take in an ID and information on where to find the
     * definition of this character.
     * $source will determine what
     * we do with $href:
     * $source action
     * local use $href as a local path
     * http use $href as a URL and fetch it from the network
     * TODO implement 'http' source, possibly others such as ftp or xml-rpc
     */
    function parse_player($source, $href)
    {
        $player = null;
        switch ($source) {
            case 'local':
                // read local file
                $file = ComixXML::$playerdir . "/$href";
                if (! file_exists($file)) {
                    throw new Exception("File not found: $file");
                }
                $xml = new SimpleXMLElement(file_get_contents($file));
                $player = new Character();
                if ($xml->name) {
                    $player->name = ((string) $xml->name);
                }
                $player->set_mood(Mood::get($xml->defaultmood ? (string)$xml->defaultmood : Mood::DEFAULT));
                if ($xml->facecolour) {
                    $player->set_skin_colour((string) $xml->facecolour);
                }
                foreach ($xml->mood as $xmlmood) {
                    // TODO create 'Mood' object, and add it to player
                }
                return $player;
                break;
            default:
                throw new Exception("Character data source '$source' not implemented");
        }
    }
}
