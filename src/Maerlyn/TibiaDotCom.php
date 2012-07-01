<?php

namespace Maerlyn;

use Guzzle\Http\Client;

/**
 * Parser functions to Tibia's official site, tibia.com
 *
 * @author Gábor Fási <m@maerlyn.eu>
 */
class TibiaDotCom
{
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
}
