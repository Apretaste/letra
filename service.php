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
		$argument = trim($request->query);

		if ($argument=='')
		{
			$response = new Response();
			$response->setResponseSubject("Falta el nombre de la cancion");
			$response->createFromText("No escribi&oacute; el nombre de la canci&oacute;n", array());
			return $response;
		}

		// get the best matching lyric
		$lyric = $this->getLyric($argument);

		if ($lyric === false)
		{
			$response = new Response();
			$response->setResponseSubject("Letra de cancion no encontrada");
			$response->createFromText("Letra de canci&oacute;n no encontrada para <b>$argument</b>. Verfica que escribiste bien el nombre de la canci&oacute;n. Si el problema persiste contacta con el soporte t&eacute;cnico.", array());
			return $response;
		}

		// create response
		$responseContent = array(
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
	 * Search lyric on the web
	 *
	 * @author salvipascual
	 */
	public function getLyric($search)
	{
		try
		{
			// access the list of possible songs
			$client = new Client();
			$crawler = $client->request('GET', 'http://www.lyricsfreak.com/search.php?a=search&type=song&q='.$search);

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

}
