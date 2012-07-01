<?php

namespace Maerlyn\Tibia;

use Guzzle\Http\Client;

/**
 * Parser functions to Tibia's official site, tibia.com
 *
 * @author Gábor Fási <m@maerlyn.eu>
 */
class TibiaDotCom
{
    /**
     * Gets information about the given character
     *
     * @param string $name
     */
    public function characterInfo($name)
    {
        $html = $this->postUrl("http://www.tibia.com/community/?subtopic=characters", array("name" => $name));

        if (false !== stripos($html, "<b>Could not find character</b>")) {
            return false;
        }

        // this will be used later while we go through all the rows in the charinfo table
        $map = array(
            "Name:" => "name",
            "Sex:" => "sex",
            "Vocation:" => "vocation",
            "Level:" => "level",
            "World:" => "world",
            "Residence:" => "residence",
            "Achievement Points:" => "achievement_points",
            "Last login:" => "last_login",
            "Comment:" => "comment",
            "Account Status:" => "account_status",
            "Married to:" => "married_to",
            "House:" => "house",
            "Guild membership:" => "guild",
        );

        $domd = $this->getDOMDocument($html);
        $domx = new \DOMXPath($domd);
        $character = array();

        $rows = $domx->query("//div[@class='BoxContent']/table[1]/tr[position() > 1]");
        foreach ($rows as $row) {
            $name  = trim($row->firstChild->nodeValue);
            $value = trim($row->lastChild->nodeValue);

            if (isset($map[$name])) {
                $character[$map[$name]] = $value;
            } else {
                $character[$name] = $value;
            }
        }

        // value cleanup

        $character["last_login"] = \DateTime::createFromFormat("M d Y, H:i:s T", $character["last_login"]);

        if (isset($character["guild"])) {
            $values = explode(" of the ", $character["guild"]);
            $character["guild"] = array(
                "name"  =>  $values[1],
                "rank"  =>  $values[0],
            );
        }

        if (isset($character["house"])) {
            $values = explode(" is paid until ", $character["house"]);
            $character["house"] = $values[0];
        }

        return $character;
    }

    /**
     * Return the list of characters online at the given world
     *
     * @param type $world
     * @return array characters with name, level and vocation
     */
    public function whoIsOnline($world)
    {
        $html = $this->getUrl("http://www.tibia.com/community/?subtopic=worlds&world=" . $world);
        $domd = $this->getDOMDocument($html);

        $domx = new \DOMXPath($domd);
        $characters = $domx->query("//table[@class='Table2']//tr[position() > 1]");
        $ret = array();

        foreach ($characters as $character) {
            $name     = $domx->query("td[1]/a[@href]", $character)->item(0)->nodeValue;
            $level    = $domx->query("td[2]", $character)->item(0)->nodeValue;
            $vocation = $domx->query("td[3]", $character)->item(0)->nodeValue;

            $ret[] = array(
                "name"      =>  $name,
                "level"     =>  $level,
                "vocation"  =>  $vocation,
            );
        }

        return $ret;
    }

    /**
     * Creates a DOMDocument object from a given html string
     *
     * @param string $html
     * @return \DOMDocument
     */
    private function getDOMDocument($html)
    {
        $domd = new \DOMDocument("1.0", "utf-8");

        $replace = array(
            "&#160;"    =>  " ", // non-breaking space in the page's code
            chr(160)    =>  " ", // non-breaking space in character comments
        );
        $html = str_replace(array_keys($replace), array_values($replace), $html);

        $html = mb_convert_encoding($html, "utf-8", "iso-8859-1");

        libxml_use_internal_errors(true);
        $domd->loadHTML($html);
        libxml_use_internal_errors(false);

        return $domd;
    }

    /**
     * Fetches a page from tibia.com and returns its body
     *
     * @param string $url
     * @return string
     * @throws \RuntimeException if a http error occurs
     */
    private function getUrl($url)
    {
        $client = new Client();
        $request = $client->get($url);
        $response = $request->send();

        if ($response->isError()) {
            throw new \RuntimeException("Error fetching page from tibia.com");
        }

        return $response->getBody(/* $asString = */ true);
    }

    private function postUrl($url, array $parameters)
    {
        $client = new Client();
        $request = $client->post($url);
        $request->setBody($parameters);
        $response = $request->send();

        return $response->getBody();
    }
}
