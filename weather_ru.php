<?
//************************************************************
//seting openweathermap
//api key
$w_key = 'er8g4e6sg4s684s68g4fs86dgfs68';
//Кординаты города
$lat = '150.9849';
$lon = '87.4421';
//************************************************************
//telegram bot seting
//bot api token
$apiToken = "6186464683:alaskjdkjBUBLuBLBjlhBjhbgcjfCa";
//id канала
$gropid = '-9781989849494';
//************************************************************

$json = url_get_contents('https://api.openweathermap.org/data/2.5/onecall?lat='.$lat.'&lon='.$lon.'&exclude=minutely,hourly,alerts&units=metric&lang=ru&appid='.$w_key);
$json = json_decode($json, TRUE);
$pop = $json['daily'][0]['pop'] *100;

$text  = 'Погода сейчас'.PHP_EOL;
$text .= 'Температура: '.$json['current']['temp'].'°С'.PHP_EOL;
$text .= 'Влажность: '.$json['current']['humidity'].'%'.PHP_EOL;
$text .= 'Погода: '.translete($json['current']['weather'][0]['main']).PHP_EOL;
$text .= 'Описание: '.mb_ucfirst($json['current']['weather'][0]['description']).PHP_EOL;

$text .= PHP_EOL;

$text .= 'Погода днем'.PHP_EOL;
$text .= 'Температура: '.$json['daily'][0]['temp']['day'].'°С'.PHP_EOL;
$text .= 'Влажность: '.$json['daily'][0]['humidity'].'%'.PHP_EOL;
$text .= 'Погода: '.translete($json['daily'][0]['weather'][0]['main']).PHP_EOL;
$text .= 'Описание: '.mb_ucfirst($json['daily'][0]['weather'][0]['description']).PHP_EOL;
$text .= 'Вероятность осадков: '.$pop.'%'.PHP_EOL;

$data = ['chat_id' => $gropid, 'text' => $text];
$response = url_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?" . http_build_query($data));

function url_get_contents($url, $useragent='cURL', $debug=false) {
    // initialise the CURL library
    $ch = curl_init();
    // specify the URL to be retrieved
    curl_setopt($ch, CURLOPT_URL,$url);
    // we want to get the contents of the URL and store it in a variable
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    // specify the useragent: this is a required courtesy to site owners
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    // ignore SSL errors
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // follow redirects - note this is disabled by default in most PHP installs from 4.4.4 up
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
    // if debugging, return an array with CURL's debug info and the URL contents
    if ($debug==true) {
        $result['contents']=curl_exec($ch);
        $result['info']=curl_getinfo($ch);
    }
    // otherwise just return the contents as a variable
    else $result=curl_exec($ch);
    // free resources
    curl_close($ch);

    // send back the data
    return $result;
}
function translete($string){
    $main_clauds = ['Thunderstorm' => 'Гроза', 'Drizzle' => 'Морось', 'Rain' => 'Дождь', 'Snow' => 'Снег', 'Mist' => 'Туман', 'Smoke' => 'Дымно', 'Haze' => 'Мгла', 'Dust' => 'Пыльно', 'Clear' => 'Ясно', 'Clouds' => 'Облачно'];
    $clouds = str_replace(array_keys($main_clauds), $main_clauds, $string);
    return $clouds;
}
function mb_ucfirst($str, $encoding='UTF-8'){
    $str = mb_ereg_replace('^[\ ]+', '', $str);
    $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
           mb_substr($str, 1, mb_strlen($str), $encoding);
    return $str;
}
