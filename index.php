<?php

if(isset($_GET["lang"])) {
    setcookie("lang", $_GET["lang"], time()+3600*24*100);
    $_COOKIE["lang"] = $_GET["lang"];
}

file_put_contents("counter.txt", @file_get_contents("counter.txt") + 1);

$default_lang = strpos(strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]), "de") !== false ? "de" : "en";
$lang = in_array(@$_COOKIE["lang"],["de","en"]) ? @$_COOKIE["lang"] : $default_lang;

include("./strings/strings-$lang.php");
$translations["LANG"] = $lang;


 if(@$_GET["page"] == "") {
    $content = file_get_contents("./templates/startpage.html");
} else if(@$_GET["page"] == "questions") {
    $content = file_get_contents("./templates/questions.html");
} else if(@$_GET["page"] == "description") {
    $content = "";
    
    $os      = @$_GET["os"];
    $client  = @$_GET["client"];
    $level   = @$_GET["level"];
    $browser = @$_GET["browser"];
    $keys    = @$_GET["keys"];

    $added_files = array();
    function add($file) {
        global $content, $lang, $added_files;
        $description = "";
        if (file_exists("./strings/descriptions-$lang/$file")) {
            $description = file_get_contents("./strings/descriptions-$lang/$file");
            $added_files[] = "./strings/descriptions-$lang/$file";
        } else {
            $description = file_get_contents("./strings/descriptions-$lang/not_translated.html");
        }
        if(isset($_GET["debugSnippets"])) {
            $content .= "[$file]";
        }
        $content .= str_replace("{CONTENT}", $description, file_get_contents("./templates/description_block.html"));
    }
    
    if(isset($_GET["debugSnippets"])) {
        $files = scandir("./strings/descriptions-$lang");
        foreach($files as $file) {
            if($file != "." && $file != "..") {
                add($file);
            }
        }
    } else {
        // Fills $content with snippets:
        include("./snippet-loader.php");
    }
    
    $most_current_date = 0;
    foreach ($added_files as $file) {
        if (filemtime($file) > $most_current_date) {
            $most_current_date = filemtime($file);
        }
    }
    
    $content = str_replace("{CONTENT}", $content, file_get_contents("./templates/description.html"));
    if ($lang == "de"){
        setlocale(LC_TIME, 'de_DE@euro', 'de_DE', 'de', 'ge');
        $content = str_replace("{LAST_MODIFIED}", strftime("%d. %B %Y",  $most_current_date), $content);
    }
    else{
    $content = str_replace("{LAST_MODIFIED}", date("F d Y", $most_current_date), $content);
    }
} else if(@$_GET["page"] == "impressum") {
    $content = file_get_contents("./templates/impressum.html");
} else if(@$_GET["page"] == "sources") {
    $content = file_get_contents("./templates/sources.html");
} else {
    $content = file_get_contents("./templates/error404.html"); 
}


$main_page = file_get_contents("./templates/main_page.html");
$main_page = str_replace('{CONTENT}', $content, $main_page);


foreach($translations as $name => $value) {
    $main_page = str_replace('$(' . $name . ')', $value, $main_page);
}

$main_page = preg_replace('/\s+/', " ", $main_page);
$main_page = str_replace("> <", "><", $main_page);

echo $main_page;

?>
