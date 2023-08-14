<?php

require 'vendor/autoload.php';
require_once 'NewsScraper.php';

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Attributes\ParseMode;
use SergiX44\Nutgram\Telegram\Attributes\MessageTypes;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Conversations\InlineMenu;
use SergiX44\Nutgram\RunningMode\Webhook;



$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


$token = "5933453564:AAH42uCHt3yZh45y-48WctUd_dGTRVlZtn0";
$bot = new Nutgram($token);
// $bot->setRunningMode(Webhook::class);

error_log("{$_ENV['BOT_TOKEN']}");


// handles command `/start`


// class TechCrunchBot {

//     private function __construct()
//     {
//         $this->bot = new Nutgram($_ENV['BOT_TOKEN']);        
//         $this->newsList = [];
//         $this->newsData = "";
//     }


//     public function runBot(){
//         return $this->bot->run();
//     }


// }



$bot->onCommand('start', function (Nutgram $bot) {

    $bot->sendMessage("Choose Category", [
        "reply_markup" => ReplyKeyboardMarkup::make(one_time_keyboard: true)
            ->addRow(
                KeyBoardButton::make("🏢 Startups"),
            )->addRow(
                KeyBoardButton::make("🔒 Security")
            )->addRow(
                KeyBoardButton::make("📱 Apps")
            )->addRow(
                KeyBoardButton::make("₿ Cryptocurrency")
            )
        ]


    );
});


$bot->onText('{category}', function(Nutgram $bot, string $category){

    if ($category != '/start') {

        error_log("category {$category}");
        global $scraper;
        $scraper = new NewsScraper();
        $types = ["🏢 Startups" => "startups", "🔒 Security" => "security", "📱 Apps" => "apps", "₿ Cryptocurrency" => "cryptocurrency"];
        // error_log("catgor: {$types[$category]}");
        $scraper->getNewsList("{$types[$category]}");
        $pages = $scraper->newsPages;
        $index = 1;
        foreach($pages as $page)
        {
            error_log("" . $page["header"]);
            global $message;
            $message .= $index . "." . $page["header"] . "\n";
            $message = $message; 
            $index ++;
        }

        error_log("message" . $message);
        $message = "News List \n" . $message;
        $bot->sendMessage($message);

    }

});







//message gets broken up into multiple individual messages to avoid message too long error
$bot->onCommand('select {news_index}', function(Nutgram $bot, string $news_index) {
    // $scraper = new NewsScraper();
    global $scraper;
    error_log("{$news_index}");
    $article = $scraper->getNews($scraper->newsPages[$news_index - 1]["link"]);
    $pattern = '/<p[^>]*>(.*?)<\/p>/s';
    preg_match_all($pattern, $article["content"], $matches);

    if (!empty($matches[1])) {
        foreach ($matches[1] as $content) {
            $text = strip_tags($content);
            $text = trim($text);

            if (!empty($text)) {
                $bot->sendMessage($text);
                echo $text . "\n"; 
            }
        }
    } else {
        echo "No <p> tags found.\n";
    }

    // $message = "" . $article["imageSrc"] . "\n" . $article["header"]. "\n" . $article["content"]; 

    // $bot->sendMessage($article["content"] , ["parse_mode" => ParseMode::HTML]);    
});




//commands `/start :parameter`
$bot->onCommand('start {parameter}', function (Nutgram $bot, $parameter) {
    $bot->sendMessage("Hello world you said: " . $parameter);
});



$bot->onCommand('options', function(Nutgram $bot){
    $bot->sendMessage(
        text: 'Welcome!',
        
        reply_markup: InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('A', callback_data: 'type:a'), 
                InlineKeyboardButton::make('B', callback_data: 'type:b')
            )
    );
});


$bot->onCallbackQueryData('type:a', function(Nutgram $bot){
    $bot->answerCallbackQuery([
        'text' => 'You selected A'
    ]);
});

$bot->onCallbackQueryData('type:b', function(Nutgram $bot){
    $bot->answerCallbackQuery([
        'text' => 'You selected B'
    ]);
});


//for media
$bot->onMessageType(MessageTypes::PHOTO, function (Nutgram $bot) {
    $photos = $bot->message()->photo;
    $bot->sendMessage('Nice pic!');
});

//just for text
$bot->onText('My name is {name}', function (Nutgram $bot, $name) {
    $bot->sendMessage("Hi {$name}");
});












$bot->run();



?>