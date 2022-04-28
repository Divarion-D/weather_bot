<?
// Подключаем библиотеку
include("jpgraph/jpgraph.php");
include("jpgraph/jpgraph_line.php");

//************************************************************
//seting openweathermap
//api key
$w_key = 'c54a5ff290d0bf2f6c23ee0a7456a86a';
//Кординаты города
$lon = '32.7645';
$lat = '48.1104';
//Название города
$street_name = 'dolinska';
//************************************************************
//telegram bot seting
//bot api token
$apiToken = "1915782155:AAEOEAgi0YjoakTaYZ9G9iv_PsuvdxnoU3o";
//id канала
$gropid = '-1001755925600';
//************************************************************

$filename_data = $street_name.'_weather_data.txt';
$filename_graph_out = $street_name."_".date("Ymd")."_out.jpg";
$filename_graph_temp = "temp/".$street_name."_temp.jpg";
$filename_graph_humid = "temp/".$street_name."_humid.jpg";

$photo_data = 'http://'.$_SERVER['HTTP_HOST']."/test/weather/".$filename_graph_out;
$ydatat = array();
$ydatah = array();
$xdata = array();
$fi = 0;

$json = url_get_contents('https://api.openweathermap.org/data/2.5/onecall?lat='.$lat.'&lon='.$lon.'&exclude=minutely,hourly,alerts&units=metric&lang=ru&appid='.$w_key);
$json = json_decode($json, TRUE);
$pop = $json['daily'][0]['pop'] *100;



//записываем статистику
if(date("d") == 1 || !file_exists($filename_data)) file_put_contents($filename_data, '');

write(['date'=>date("d"), 'temperature'=>round($json['daily'][0]['temp']['day'],2), 'humidity'=> $json['daily'][0]['humidity']], $filename_data);

foreach (read($filename_data) as $data) {
    $xdata[$fi] = $data['date'];
    $ydatat[$fi] = $data['temperature'];
    $ydatah[$fi] = $data['humidity'];
    $fi++;
}

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


if ($fi == 1){
    $data = ['chat_id' => $gropid, 'text' => $text];
    $response = url_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?" . http_build_query($data));
}else{
    buildGraph($xdata, $ydatat, $ydatah);
    $data = ['chat_id' => $gropid, 'photo' => $photo_data, 'caption' => $text];
    $response = url_get_contents("https://api.telegram.org/bot$apiToken/sendPhoto?" . http_build_query($data));
    unlink($filename_graph_out);
}

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

//запись данных 
function write($data, $filename) {
    
    $file_data = read($filename);
    if($file_data == ""){
        $data_out[0] = $data;
    }else{
        $data_out = $file_data;
        $data_out[count($file_data)] = $data;
    }
    $data_out = serialize($data_out);
    file_put_contents($filename, $data_out);
    
}

//чтение данных
function read($filename){
    $data = file_get_contents($filename);
    return unserialize($data);
}

function buildGraph($xdata, $ydatat, $ydatah) {
    
    global $filename_graph_humid, $filename_graph_temp, $filename_graph_out;
    
    //Строим график для температуры
    $graph = new Graph(600, 200, "auto");
    $graph->SetScale("textlin");
    $graph->SetMarginColor('white');
    $graph->SetFrame(true, '#B3BCCB', 1);
    $graph->SetTickDensity(TICKD_DENSE);
    $graph->img->SetMargin(50, 20, 20, 30);
    $graph->title->SetMargin(10);
    $graph->xaxis->SetTickLabels($xdata);
    $graph->xaxis->SetLabelAngle(90);
    $graph->xaxis->SetPos('min');

    $lineplot = new LinePlot($ydatat);
    $graph->Add($lineplot);
    //добавляем точку пересечения осей координат
    $lineplot->mark->SetType(MARK_DIAMOND);
    $lineplot->SetColor('red');
    //Вывод графика
    $graph->Stroke($filename_graph_temp);

    $graph = new Graph(600, 200, "auto");
    $graph->SetScale("textlin");
    $graph->SetMarginColor('white');
    $graph->SetFrame(true, '#B3BCCB', 1);
    $graph->SetTickDensity(TICKD_DENSE);
    $graph->img->SetMargin(50, 20, 20, 30);
    $graph->title->SetMargin(10);
    $graph->xaxis->SetTickLabels($xdata);
    $graph->xaxis->SetLabelAngle(90);
    $graph->xaxis->SetPos('min');

    $lineplot = new LinePlot($ydatah);
    $graph->Add($lineplot);
    //добавляем точку пересечения осей координат
    $lineplot->mark->SetType(MARK_DIAMOND);
    $lineplot->SetColor('blue');
    //Вывод графика
    $graph->Stroke($filename_graph_humid);

    drawImage($filename_graph_temp, $filename_graph_humid, $filename_graph_out);
}

function drawImage($img1, $img2, $file = false) {
    if (!preg_match('~\.(jpe?g|png|gif)$~i', $img1) || !preg_match('~\.(jpe?g|png|gif)$~i', $img2)) return false;
    if (!($info[] = getimagesize($img1)) || !($info[] = getimagesize($img2))) return false;

    $info[0]['type'] = substr($info[0]['mime'], 6);
    $info[1]['type'] = substr($info[1]['mime'], 6);
    $info[0]['width'] = (int) $info[0][0];
    $info[1]['width'] = (int) $info[1][0];
    $info[0]['height'] = (int) $info[0][1];
    $info[1]['height'] = (int) $info[1][1];

    $create1 = 'imagecreatefrom' . $info[0]['type'];
    $create2 = 'imagecreatefrom' . $info[1]['type'];

    if (!function_exists($create1) || !function_exists($create1)) return false;

    $width = ($info[0]['width'] > $info[1]['width']) ? $info[0]['width'] : $info[1]['width'];
    $height = $info[0]['height'] + 1 + $info[1]['height']; //зазор в 1 пиксель между картинками (можно убрать, конечно)

    if (empty($width) || empty($height)) return false;

    $image1 = $create1($img1); //create images
    $image2 = $create2($img2); //create images

    $dst = imagecreatetruecolor($width, $height);

    if (in_array($info[0]['type'], array('gif', 'png')) || in_array($info[1]['type'], array('gif', 'png'))) {
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);

        $type = 'png';
    } else {
        $type = 'jpeg';
    }
    imagecopyresampled($dst, $image1, 0, 0, 0, 0, $width, $info[0]['height'], $info[0]['width'], $info[0]['height']);
    imagecopyresampled($dst, $image2, 0, $info[0]['height'] + 1, 0, 0, $width, $info[1]['height'], $info[1]['width'], $info[1]['height']);

    $save = 'image' . $type;

    //header('Content-type: image/' . $type);

    return false !== $file ? $save($dst, $file) : $save($dst);
}
