<?php
require_once 'vendor/autoload.php'; // Include Composer's autoloader
use Goutte\Client;

class NewsScraper {

	public function __construct()
	{
		$this->client = new Client();
		$this->newsPages = [];
	}


	public function getNewsList($category){

		$url = 'https://techcrunch.com/category/' . $category ;
		error_log("url: " . $url);
		$crawler = $this->client->request('GET', $url);
		$articleNodes = $crawler->filter('div.post-block');
		$articles = [];

		$articleNodes->each(function ($node) use (&$articles) {
		    $headerNode = $node->filter('h2.post-block__title')->first();
		    $linkNode = $node->filter('a.post-block__title__link')->first();
		    $header = $headerNode->text();
		    $link = $linkNode->attr('href');
		    if ($header && $link) {
		        $this->newsPages[] = [
		            'header' => $header,
		            'link' => $link,
		        ];
		    }
		});

		return $articles;

	}

	public function getNews($page_link)
	{

		$crawler = $this->client->request('GET', $page_link);
		$articleNode = $crawler->filter('article.article-container')->first();
		$articleData = [];
		if ($articleNode->count()) {
		    $headerNode = $articleNode->filter('h1.article__title')->first();
		    $imageNode = $articleNode->filter('img.article__featured-image')->first();
		    $articleData["header"] = $headerNode->text();
		    $articleData["imageSrc"] = $imageNode->attr('src');
		    $articleData["content"] = $articleNode->filter('div.article-content')->html();
		    
		} else {
		    echo "No article found on the page.";
		}
		return $articleData;

	}
}


$req = new NewsScraper();
// $req->getNewsList('https://techcrunch.com/category/apps');
// $pages = $req->newsPages;

// foreach($pages as $page)
// {
// 	var_dump($page["header"]);
// 	// echo "Header: " . $page;
// }


var_dump($req->getNews('https://techcrunch.com/2023/08/04/spotify-introduces-new-product-to-help-software-development-teams-with-with-a-b-testing/'))




?>