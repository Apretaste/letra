<?php

use Goutte\Client;

class Service
{
	/**
	 * Home page to search for artirst or lyrics
	 *
	 * @author salvipascual
	 * @param Request
	 * @param Response
	 */
	public function _main (Request $request, Response $response)
	{
		$response->setCache("year");
		$response->setTemplate("home.ejs", []);
	}

	/**
	 * Display a list of artist and lyrics
	 *
	 * @author salvipascual
	 * @param Request
	 * @param Response
	 */
	public function _search (Request $request, Response $response)
	{
		// get the song or artist name encoded
		$query = urlencode($response->input->data->query);

		// load from cache if exists
		$cache = Utils::getTempDir() . date("Ym") . "_letras_" . md5($query) . ".tmp";
		if(file_exists($cache)) $content = unserialize(file_get_contents($cache));

		// get data from the internet
		else {
			try {
				// get the list of songs
				$client = new Client();	
				$crawler = $client->request('GET', "https://www.lyricsfreak.com/search.php?q=$query");

				// get the list of authors and songs
				$list = $crawler->filter('.js-sort-table-content-item')->each(function($node) {
					// get author, song and link
					$author = $node->filter('.lf-list__title--secondary')->text();
					$song = trim($node->filter('.lf-list__meta')->text());
					$link = trim($node->filter('.lf-list__meta > a')->attr("href"));

					// clean the author
					$author = htmlentities($author, null, 'utf-8');
					$author = trim(str_replace(["&nbsp;", "&middot;"], "", $author));
					$author = html_entity_decode($author, ENT_QUOTES | ENT_HTML5, 'UTF-8');

					return ["author"=>$author, "song"=>$song, "link"=>$link];
				});
			} catch(Exception $e) {
				return $response->setTemplate('message.ejs', []);
			}

			$content = [
				"title" => $response->input->data->query,
				"list" => $list
			];

			// save cache file
			file_put_contents($cache, serialize($content));
		}

		// message if there are not rsponses
		if (empty($content['list'])) return $response->setTemplate('message.ejs', []);

		// send data to the view
		$response->setCache("year");
		$response->setTemplate("search.ejs", $content);
	}

	/**
	 * Display the lyrics for a song
	 *
	 * @author salvipascual
	 * @param Request
	 * @param Response
	 */
	public function _lyric (Request $request, Response $response)
	{
		// get the song or artist name encoded
		$link = $response->input->data->link;

		// load from cache if exists
		$cache = Utils::getTempDir() . date("Ym") . "_letras_" . md5($link) . ".tmp";
		if(file_exists($cache)) $content = unserialize(file_get_contents($cache));

		// get data from the internet
		else {
			try {
				// get the song
				$client = new Client();	
				$crawler = $client->request('GET', "https://www.lyricsfreak.com$link");

				// get the lytric
				$lyric = trim($crawler->filter('#content')->text());
				$lyric = nl2br($lyric);

				// get author
				$authorTitle = $crawler->filter('.lyric-song-head')->text();
				$authorTitleArr = explode('–', $authorTitle);
				$author = trim($authorTitleArr[0]);

				// get the song title
				$song = trim($authorTitleArr[1]);
				$song = trim(str_replace("Lyrics", "", $song));
			} catch(Exception $e) {
				return $response->setTemplate('message.ejs', []);
			}

			// create the content array
			$content = [
				"author" => $author,
				"song" => $song,
				"lyric" => $lyric
			];

			// save cache file
			file_put_contents($cache, serialize($content));
		}

		// send information to the view
		$response->setCache("year");
		$response->setTemplate("lyric.ejs", $content);
	}
}
