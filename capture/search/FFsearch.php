<?php

if ($argc < 1)
    die; // only run from command line
// ----- params -----
set_time_limit(0);
error_reporting(E_ALL);
include_once "../../config.php";
include_once BASE_FILE . '/common/functions.php';
include_once BASE_FILE . '/capture/common/functions.php';

require BASE_FILE . 'capture/common/tmhOAuth/tmhOAuth.php';

// DEFINE SEARCH PARAMETERS HERE
$searchbin = getBestSearchBin();
$bin_name = $searchbin['bin_name'];       // name of the bin
$keywords = $searchbin['keywords'];       // separate keywords by 'OR', limit your search to 10 keywords and operators - https://dev.twitter.com/docs/using-search
$type = 'search';     // specify 'search' if you want this to be a standalone bin, or 'track' if you want to be able to continue tracking these keywords later on via BASE_URL/capture/index.php

if (empty($bin_name))
    die("bin_name not set\n");
if (empty($keywords))
    die("keywords not set\n");

$querybin_id = $searchbin['querybin_id'];

$current_key = $looped = $tweets_success = $tweets_failed = $tweets_processed = 0;
$all_users = $all_tweet_ids = array();

// ----- connection -----
$dbh = pdo_connect();
create_bin($bin_name, $dbh);

$ratefree = 0;

search($keywords);

queryManagerCreateBinFromExistingTables($bin_name, $querybin_id, $type, explode("OR", $keywords));

updateSearchTime($querybin_id);

function search($keywords, $max_id = null) {
    global $twitter_keys, $current_key, $ratefree, $all_users, $all_tweet_ids, $bin_name, $tweets_success, $tweets_failed, $tweets_processed, $dbh;

    $ratefree--;
    if ($ratefree < 1 || $ratefree % 10 == 0) {
	$keyinfo = getRESTKey($current_key, 'search', 'tweets');
	$current_key = $keyinfo['key'];
	$ratefree = $keyinfo['remaining'];
    }

    $tmhOAuth = new tmhOAuth(array(
                'consumer_key' => $twitter_keys[$current_key]['twitter_consumer_key'],
                'consumer_secret' => $twitter_keys[$current_key]['twitter_consumer_secret'],
                'token' => $twitter_keys[$current_key]['twitter_user_token'],
                'secret' => $twitter_keys[$current_key]['twitter_user_secret'],
            ));
    $params = array(
        'q' => $keywords,
        'count' => 100,
    );
    if (isset($max_id))
        $params['max_id'] = $max_id;

    $code = $tmhOAuth->user_request(array(
        'method' => 'GET',
        'url' => $tmhOAuth->url('1.1/search/tweets'),
        'params' => $params
            ));

    if ($tmhOAuth->response['code'] == 200) {
        $data = json_decode($tmhOAuth->response['response'], true);
        $tweets = $data['statuses'];
        $tweet_ids = array();
        foreach ($tweets as $tweet) {

            $t = Tweet::fromJSON(json_encode($tweet)); // @todo: dubbelop

            $all_users[] = $t->user->id;
            $all_tweet_ids[] = $t->id;
            $tweet_ids[] = $t->id;

            $saved = $t->save($dbh, $bin_name);

            print ".";
        }

        if (!empty($tweet_ids)) {
            print "\n";
            if (count($tweet_ids) <= 1) {
                print "no more tweets found\n\n";
                return false;
            }
            $max_id = min($tweet_ids);
            print "max id: " . $max_id . "\n";
        } else {
            print "0 tweets found\n\n";
            return false;
        }
        sleep(1);
        search($keywords, $max_id);
    } else {
        echo $tmhOAuth->response['response'] . "\n";
        if ($tmhOAuth->response['response']['errors']['code'] == 130) { // over capacity
            sleep(1);
            search($keywords, $max_id);
        }
    }
}

?>
