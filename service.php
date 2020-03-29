<?php

use Framework\Crawler;
use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;

class Service
{

	/**
	 * Home page to search for artirst or lyrics
	 *
	 * @param Request $request
	 * @param Response $response
	 *
	 * @throws \Framework\Alert
	 * @author salvipascual
	 */
	public function _main(Request $request, Response &$response)
	{
		$response->setCache('year');
		$response->setTemplate('home.ejs', []);
	}

	/**
	 * Display a list of artist and lyrics
	 *
	 * @param Request $request
	 * @param Response $response
	 *
	 * @return \Apretaste\Response
	 * @throws \Framework\Alert
	 * @author salvipascual
	 */
	public function _search(Request $request, Response &$response)
	{
		// get the song or artist name encoded
		$query = urlencode($response->input->data->query);

		// load from cache if exists
		$cache = TEMP_PATH . 'cache/' . date('Ym')  . '_letras_'. md5($query) . '.tmp';
		if (file_exists($cache)) {
			$content = unserialize(file_get_contents($cache));
		}

		// get data from the internet
		else {
			try {
				// get the list of songs
				Crawler::start("https://www.lyricsfreak.com/search.php?q=$query");

				// get the list of authors and songs
				$list = Crawler::filter('.js-sort-table-content-item')->each(function ($node) {
					/** @var \Symfony\Component\DomCrawler\Crawler $node */
					// get author, song and link
					$author = $node->filter('.lf-list__title--secondary')->text();
					$song = trim($node->filter('.lf-list__meta')->text());
					$link = trim($node->filter('.lf-list__meta > a')->attr('href'));

					// clean the author
					$author = htmlentities($author, null, 'utf-8');
					$author = trim(str_replace(['&nbsp;', '&middot;'], '', $author));
					$author = html_entity_decode($author, ENT_QUOTES | ENT_HTML5, 'UTF-8');

					return ['author' => $author, 'song' => $song, 'link' => $link];
				});
			} catch (Exception $e) {
				return $response->setTemplate('message.ejs', []);
			}

			$content = [
				'title' => $response->input->data->query,
				'list' => $list
			];

			// save cache file
			file_put_contents($cache, serialize($content));
		}

		// message if there are not rsponses
		if (empty($content['list'])) {
			return $response->setTemplate('message.ejs', []);
		}

		// send data to the view
		$response->setCache('year');
		$response->setTemplate('search.ejs', $content);
	}

	/**
	 * Display the lyrics for a song
	 *
	 * @param Request $request
	 * @param Response $response
	 *
	 * @return \Apretaste\Response
	 * @throws \Framework\Alert
	 * @author salvipascual
	 */
	public function _lyric(Request $request, Response &$response)
	{
		// get the song or artist name encoded
		$link = $response->input->data->link;

		// load from cache if exists
		$cacheName = md5($link);
		$content = self::loadCache($cacheName);
		if ($content === null) {
			try {
				// get the song
				Crawler::start("https://www.lyricsfreak.com$link");

				// get the lytric
				$lyric = trim(Crawler::filter('#content')->html());
				$lyric = strip_tags($lyric, 'br');
				$lyric = nl2br($lyric);

				// get author
				$authorTitle = Crawler::filter('.lyric-song-head')->text();
				$authorTitleArr = explode('–', $authorTitle);
				$author = trim($authorTitleArr[0]);

				// get the song title
				$song = trim($authorTitleArr[1]);
				$song = trim(str_replace('Lyrics', '', $song));

				// create the content array
				$content = [
				  'author' => $author,
				  'song' => $song,
				  'lyric' => $lyric
				];

				// save cache file
				self::saveCache($content, $cacheName);
			} catch (Exception $e) {
				return $response->setTemplate('message.ejs', []);
			}
		}

		// complete the challenge
		Challenges::complete('view-letra', $request->person->id);

		// send information to the view
		$response->setCache('year');
		$response->setTemplate('lyric.ejs', $content);
	}


	/**
	 * Get cache file name
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public static function getCacheFileName($name): string
	{
		return TEMP_PATH.'cache/letras_'.$name.'_'.date('Ymd').'.tmp';
	}

	/**
	 * Load cache
	 *
	 * @param $name
	 * @param null $cacheFile
	 *
	 * @return bool|mixed
	 */
	public static function loadCache($name, &$cacheFile = null)
	{
		$data = null;
		$cacheFile = self::getCacheFileName($name);
		if (file_exists($cacheFile)) {
			$data = unserialize(file_get_contents($cacheFile));
		}
		return $data;
	}

	/**
	 * Save cache
	 *
	 * @param $name
	 * @param $data
	 * @param null $cacheFile
	 */
	public static function saveCache($name, $data, &$cacheFile = null)
	{
		$cacheFile = self::getCacheFileName($name);
		file_put_contents($cacheFile, serialize($data));
	}
}
