<?php

use Goutte\Client;

class Letra extends Service {
	
	static $results = array();
	
	/**
	 * Function executed when the service is called
	 *
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request){
		
		$argument = trim($request->query);
		
		if ($argument=='') {			
			$response = new Response();
			$response->setResponseSubject("Falta el nombre de la cancion");
			$response->createFromText("No escribi&oacute; el nombre de la canci&oacute;n", array());
			return $response;
		}
		
		$lyric = $this->getLyric($argument);
		
		if ($lyric === false){
			$response = new Response();
			$response->setResponseSubject("Letra de cancion no encontrada");
			$response->createFromText("Letra de canci&oacute;n no encontrada", array());
			return $response;
		}
		
		// create response
		$responseContent = array (
				"title" => $lyric['title'],
				"author" => $lyric['author'],
				"lyric" => $lyric['body'],
				"song" => ucfirst($argument)
		);
	
		$response = new Response();
		$response->setResponseSubject("Letra: ".$lyric['title']."");
		$response->createFromTemplate("basic.tpl", $responseContent);
		
		return $response;
	}
	
	/**
	  * Search lyric on the web http://lyrics.com
	  */
	public function getLyric($search){
		
		$client = new Client();
		
		$url = 'http://www.lyrics.com/search.php?keyword='.$search.'&what=all&search_btn=Search';
		//$url = 'http://localhost/test/search.htm';
		
		$crawler = $client->request('GET', $url);
		
		// <a href="/yesterday-lyrics-the-beatles.html" class="lyrics_preview" t_id="T  4592984">Yesterday</a>
		// <div id="lyrics" class="SCREENONLY" itemprop="description">Yesterday, all my troubles seemed so far away,<br>

		self::$results = array();
		
		try {
			$crawler->filter('a.lyrics_preview')->each(function ($node, $i) {
				 self::$results[] = $node->attr('href');
			});
		} catch(Exception $e){
			return false;
		}
		
		foreach(self::$results as $result){
			
			try {
				$url = "http://".str_replace("//","/","www.lyrics.com/".$result);
				$crawler = $client->request('GET', $url);
				
				$title = $crawler->filter('#profile_name')->html();
				$title = substr($title,0,strpos($title,'<br'));
				$author = $crawler->filter('#profile_name > span > a')->text();
				$body = $crawler->filter('#lyrics')->html();
				$pos = strpos($body,"---");
				
				if ($pos !== false) 
					$body = substr($body, 0, $pos);
				
				return array(
					'title' => $title,
					'body' => $body,
					'author' => $author
				);
			} catch(Exception $e) {
				// continue
			}
			
		}
		return false;
	}
	
}