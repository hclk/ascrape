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

	$urls = ('urls.csv');

	$urls = file($urls);

	$inlinks = FALSE;

	printf("\rFound %s URLs\r\nWorking...\r\n", count($urls));	

    $kws  = ('keywords.csv');

    $kws  = file($kws);

    $nkws = $nurls = $nqp = $nnqp = 0;

    $matched = "";

    $headings = "\"URL\",\"Keyword\",\"Area Match\"" . PHP_EOL;

    $handle = fopen("urls.csv","r");
    if($handle){
    	while (($url = fgets($handle)) !== false){

    		$url = trim($url);

			$nurls ++;

			$data = get_htmlDOM($url);
		    //$paragraphs = $data->getElementsByTagName('p');
		    //print($data);

		    preg_match_all("#<p>(.*?)<\/p>#",$data,$paragraphs);

		    //print_r($paragraphs);

		    if(isset($paragraphs)){
		    	foreach($paragraphs as $p){
		    		if(is_array($p)){
		    			$p = implode($p);
		    		}
		    		$p = trim($p);
		    		if(strlen($p)>30){

			    		$nqp ++; // count of qualified paragraphs

			    		preg_match_all("#<a.*?>(.*?)<\/a>#", $p, $anchors); 	//make array of existing link anchors

				    	//$p = preg_replace("#<a.*?/a>#", "", $p); 						//removes all links from paragraph

				    	$p = strip_tags($p);											//removes other tags
				        
				        foreach($kws as $kw){

				        	$kw = trim($kw);

				        	if(strlen($kw)>3){

					        	$inlinks = FALSE;

					        	$kwpos = stripos($p, trim($kw));

					        	if($kwpos !== FALSE){

					        		foreach($anchors[1] as $anchor){ //checks if a link with that kw in exists already
					        			if((stripos($anchor, trim($kw)) !== FALSE) && ($inlinks === FALSE)){
					        				$inlinks = TRUE;
					        			}
					        		}

					        		if($inlinks === FALSE){

					        			$nkws ++;

						        		if($nkws===1){
						  					file_put_contents('output'.$time.'-ascrape.csv', $headings, FILE_APPEND);
						  				}

						        		$areamatch = ".." . substr($p, $kwpos - 20, 20 + strlen($kw) + 20) . "..";

						        		$output = "\"" . $url . "\",\"" . $kw . "\",\"" . $areamatch . "\"" . PHP_EOL;

						        		if(strlen($areamatch)>20){

						        			file_put_contents('output'.$time.'-ascrape.csv', $output, FILE_APPEND);

						        		}			    						

						        	}

					        	}

					        }

				        }
				    }

			    }
			
		    }
		printf("\rFound %s keyword matches in %s qualified paragraphs in %s URLs", $nkws, $nqp, $nurls);

    	} fclose($handle);
    } else {
    	print("Error opening file");
    }
        

}	catch(Exception $e){ //error reporting
            print($e->getMessage());
            die($e->getMessage());
        }

?>
