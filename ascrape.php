<?php

require("simple_html_dom.php");

function get_htmlDOM($href){

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_URL, $href);
    curl_setopt($curl, CURLOPT_REFERER, $href);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.125 Safari/533.4");
    $str = curl_exec($curl);
    curl_close($curl);

    // Create a DOM object
    $dom = new simple_html_dom();
    // Load HTML from a string
    $dom->load($str);

    return $dom;
}

try {

	$time = time();

	$url = "http://www.cosmos.co.uk/turkey/turquoise-coast-dalaman/sarigerme/holidays";

    $kws  = ('keywords.csv');

    $kws  = file($kws);

    $matched = "";

	$data = get_htmlDOM($url);
    $guide = $data->getElementById('guide');

    if(isset($guide)){

    	preg_match_all("#<a.*?\"http://.*?\">(.*?)</a>#", $guide, $links);

    	$guide = preg_replace("#<a.*?/a>#", "", $guide); //removes all links from guide

    	print($guide);

    	print_r($links);

        foreach($links[1] as $anchor){
            $anchor = trim($anchor);
            print($anchor . "\r\n");
            foreach($kws as $kw){
                $kw = trim($kw);
                if(strpos($anchor, $kw) !== false){
                    $matched .= $anchor . "; ";
                }
            }
        } 
    } else {
            $accomguide = $data->getElementById('PropertyList_container');

            unset($links);

            if(isset($accomguide)){ //start loop if accomodationguide found

                //$accomguidecheck = NULL;

                $titlefind = ": Holiday Accommodation";

                $tabfind = "<div id=\"tabs\"";

                $accomguide = substr($accomguide, strpos($accomguide,$titlefind) + strlen($titlefind) + 3); //strip until start of guide

                $accomguide = substr($accomguide,0,strpos($accomguide, $tabfind)); //strip after end of guide

                preg_match_all("#<a.*?\"http://.*?\">(.*?)</a>#", $accomguide, $links);

                $accomguide = preg_replace("#<a.*?/a>#", "", $accomguide); //removes all links from guide

                $accomguide = strip_tags($accomguide); //removes all tag markup

                print($accomguide);

                print_r($links);

                foreach($links[1] as $anchor){
                    $anchor = trim($anchor);
                    print($anchor . "\r\n");
                    foreach($kws as $kw){
                        $kw = trim($kw);
                        if(strpos($anchor, $kw) !== false){
                            $matched .= $anchor . "; ";
                        }
                    }
                } 

            }
    }

        if(isset($matched)){
            print("\r\nMatched keywords: " . $matched);
        } else {
            print("\r\nNo matches!");
        }

    

}	catch(Exception $e){ //error reporting
            print($e->getMessage());
            die($e->getMessage());
        }

?>