<?php

use Goutte\Client;

class Letra extends Service
{
	static $results = array();

	/**
	 * Function executed when the service is called
	 *
	 * @author salvipascual
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request)
	{
		// do not allow blank searches
		
		
			$response = new Response();
			$response->setCache();
			$response->setResponseSubject("Que letra desea buscar?");
			$response->createFromTemplate("home.tpl", array());
			$response->setCache();
			return $response;
		

		
	}

	/**
	 * Search lyric on the web
	 *
	 * @author salvipascual
	 */
	private function getLyric($search)
	{
		
		$client = new Client();
		$lyricsPage = $client->request('GET', "http://www.lyricsfreak.com".$search);
		// get the info from the songs page
		$titleANDauthor = $lyricsPage->filter('.lyric-song-head');
		$title = trim(explode("–", $titleANDauthor->html())[1], "Lyrics ");
		$author = $titleANDauthor->filter('a')->html();
		$body = $lyricsPage->filter('#content_h')->html();

		$responseContent = array(
			"title" => $title,
			"autor" =>$author,
			"letra"=>$body
		);
		$response = new Response();
		$response->setCache();
		$response->setResponseSubject("letra:".$title);
		$response->createFromTemplate("basic.tpl", $responseContent);
		$response->setCache();
		return $response;
			

		
	}
	private function getAutor($search){
		try
		{

			// access the list of possible songs
			$client = new Client();
			$crawler = $client->request('GET', 'https://www.lyricsfreak.com/search.php?a=search&type=band&q='.$search);
			// acccess the first page on the list of songs
			$link = $crawler->filter('a.song')->first()->attr('href');
			$lyricsPage = $client->request('GET', "http://www.lyricsfreak.com".$link);

			// get the info from the songs page
			$titleANDauthor = $lyricsPage->filter('.lyric-song-head');
			$title = trim(explode("–", $titleANDauthor->html())[1], "Lyrics ");
			$author = $titleANDauthor->filter('a')->html();
			$body = $lyricsPage->filter('#content_h')->html();
		}
		catch(Exception $e)
		{
			return false;
		}

		return array(
			'title' => $title,
			'body' => $body,
			'author' => $author
		);
	}
	public function _autor(Request $request){

		$client = new Client();
		$crawler = $client->request('GET','https://www.lyricsfreak.com/search.php?a=search&type=band&q='.$request->query);
			//get all links

		
		//get all author
		$autores_nombre=$crawler->filter('.colfirst a')->each(function($nodo){
		return $nodo->text();
		});
		$autores_link=$crawler->filter('.colfirst a')->each(function($nodo){
		return $nodo->attr('href');
		});
		if (!count($autores_nombre))
		{
			$response = new Response();
			$response->setResponseSubject("Autor no encontrado");
			$response->createFromText("Autor no encontrado para <b>{$request->query}</b>. Verfica que escribiste bien el nombre del autor;n. Si el problema persiste contacta con el soporte t&eacute;cnico.<br/>", array());
			$response->setCache();
			return $response;
		}
		/*if(count($autores)==1){
			return $this->getSongs($autores[0]);
		}*/
		//array of data
		$datos=array_combine($autores_nombre,$autores_link);	
		// create response
		$responseContent = array(
			"nombre" => $request->query,
			"datos" =>$datos
			
		);

		$response = new Response();
		$response->setCache();
		$response->setResponseSubject("opciones");
		$response->createFromTemplate("results.tpl", $responseContent);
		return $response;
	}

	public function _buscar_canciones(Request $request){
		$client = new Client();
		$crawler = $client->request('GET','http://www.lyricsfreak.com/'.$request->query);
		$canciones=$crawler->filter('.colfirst a')->each(function($nodo){
		return $nodo->text();
		});
		$enlaces=$crawler->filter('.colfirst a')->each(function($nodo){
		return $nodo->attr('href');
		});

		$datos=array_combine($canciones,$enlaces);
		$autor=explode("/",trim($request->query,"/"));
		$responseContent = array(
			"author" => $autor[1],
			"datos" =>$datos
			
		);

		$response = new Response();
		$response->setCache();
		$response->setResponseSubject("opciones");
		$response->createFromTemplate("results.tpl", $responseContent);
		$response->setCache();
		return $response;
	}
	
	public function _cancion(Request $request){
		
		$client = new Client();
			$crawler = $client->request('GET', 'http://www.lyricsfreak.com/search.php?a=search&type=song&q='.$request->query);
			//get all links
		$links=$crawler->filter('a.song')->each(function($nodo){
		return $nodo->attr('href');
		});
		//get all author
		$autores=$crawler->filter('.colfirst a')->each(function($nodo){
		return $nodo->text();
		});
		
		if (!count($links))
		{
			$response = new Response();
			$response->setResponseSubject("Letra de cancion no encontrada");
			$response->createFromText("Letra de canci&oacute;n no encontrada para <b>{$request->query}</b>. Verfica que escribiste bien el nombre de la canci&oacute;n. Si el problema persiste contacta con el soporte t&eacute;cnico.<br/>", array());
			$response->setCache();
			return $response;
		}
		if(count($links)==1){
			return $this->getLyric($links[0]);
		}
		//array of data
		$datos=array_combine($autores,$links);	

		// create response
		$responseContent = array(
			"title" => $request->query,
			"datos" =>$datos,
			"back"=>$request->subject
			
		);

		$response = new Response();
		$response->setCache();
		$response->setResponseSubject("opciones");
		$response->createFromTemplate("results.tpl", $responseContent);
		$response->setCache();
		return $response;
	}

	public function _buscar(Request $request){
		return $this->getLyric($request->query);
		
	}


}
