<?php
//https://codebeautify.org/php-beautifier
error_reporting(1); //https://www.php.net/manual/zh/errorfunc.constants.php
//https://www.uuidgenerator.net/dev-corner/php
function guidv4($data = null)
{
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);
    // Set version to 0100
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    // Output the 36 character UUID.
    return vsprintf("%s%s-%s-%s-%s-%s%s%s", str_split(bin2hex($data), 4));
}
function plugin_opts_val($data, $key)
{
    foreach (explode(";", $data) as $data1) {
        if (isset(explode("=", $data1)[0]) && isset(explode("=", $data1)[1])) {
            if ($key == explode("=", $data1)[0]) {
                return explode("=", $data1)[1] ?? null;
            }
        }
    }
}
//https://stackoverflow.com/a/38298029
function ip_in_range($ip, $range)
{
    if (strpos($range, "/") == false) {
        $range .= "/32";
    }
    // $range is in IP/CIDR format eg 127.0.0.1/24
    list($range, $netmask) = explode("/", $range, 2);
    $range_decimal = ip2long($range);
    $ip_decimal = ip2long($ip);
    $wildcard_decimal = pow(2, 32 - $netmask) - 1;
    $netmask_decimal = ~$wildcard_decimal;
    return ($ip_decimal & $netmask_decimal) ==
        ($range_decimal & $netmask_decimal);
}
function subject_cn($path)
{
    $cert_content = file_get_contents($path);
    // Get a certificate resource from the PEM string.
    $cert = openssl_x509_read($cert_content);
    // Parse the resource and print out the contents.
    $cert_data = openssl_x509_parse($cert);
    return $cert_data["subject"]["CN"] ?? null;
    // Free the resource
    //openssl_x509_free( $cert );
}
function _cloudflare_CheckIP($ip, $arrContextOptions)
{
    if (file_exists("/tmp/ips4")) {
        $cf_ips = file("/tmp/ips4");
    } else {
        $cf_ips = file(
            "https://www.cloudflare.com/ips-v4",
            FILE_SKIP_EMPTY_LINES,
            stream_context_create($arrContextOptions)
        );
    }
    $is_cf_ip = false;
    foreach ($cf_ips as $cf_ip) {
        if (ip_in_range($ip, $cf_ip)) {
            $is_cf_ip = true;
            break;
        }
    }
    return $is_cf_ip;
}
function controller_ipc($input)
{
    $client_side_sock = "/tmp/ss-client2.socket";
    if (file_exists($client_side_sock)) {
        unlink($client_side_sock);
    }
    if (!($socket = socket_create(AF_UNIX, SOCK_DGRAM, 0))) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);

        die("Couldn't create socket: [$errorcode] $errormsg \n");
    }
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, [
        "sec" => 1,
        "usec" => 0,
    ]);
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, [
        "sec" => 1,
        "usec" => 0,
    ]);
    if (!socket_bind($socket, $client_side_sock)) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
        die("Could not bind socket : [$errorcode] $errormsg \n");
    }
    socket_sendto(
        $socket,
        $input,
        strlen($input),
        0,
        "/tmp/ss-manager.socket",
        0
    );
    if (!socket_recvfrom($socket, $buf, 64 * 1024, 0, $source)) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
        die("Could not receive data: [$errorcode] $errormsg \n");
    }
    // close socket and delete own .sock file
    socket_close($socket);
    unlink($client_side_sock);
    if (isset($buf)) {
        if ($buf != $input) {
            return $buf;
        }
    }
}
function used_traffic($port)
{
    $data = json_decode(
        str_replace("stat: ", "", controller_ipc("ping")),
        true
    );
    foreach ($data as $key => $value) {
        if ($key == $port) {
            $used = $value;
            break;
        }
    }
    if (isset($used)) {
        return $used;
    }
}
//https://shadowsocks.org/en/wiki/SIP008-Online-Configuration-Delivery.html
header("Content-Type: application/json; charset=utf-8");
$array = [
    "version" => (int) 1,
    "servers" => (array) [],
];
$arrContextOptions = [
    "http" => [
        "timeout" => 2,
    ],
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ],
];
$port_list = "/etc/ssmanager/port.list";
$tls_cert = "/etc/ssmanager/ssl/server.cer";
//$id = guidv4();
//$server_ip = gethostbyname(gethostname());
$server_ip = $_SERVER["SERVER_ADDR"];
/*
if (file_exists($tls_cert)) {
    $ipCheck = _cloudflare_CheckIP(
        gethostbyname(trim(subject_cn($tls_cert))),
        $arrContextOptions
    );
}
*/
$ipCheck = empty($_SERVER["HTTP_CDN_LOOP"]) ? false : true;
$connection = @fsockopen($server_ip, 53, $errno, $errstr, 1);
if (is_resource($connection)) {
    $dns = $server_ip . ":53";
    fclose($connection);
} else {
    $dns = false;
}
if (
    !in_array(
        gethostbyname("raw.githubusercontent.com"),
        ["127.0.0.1", "0.0.0.0"],
        true
    )
) {
    $url = "https://github.com/yiguihai/shadowsocks_install/raw/dev";
} else {
    $url = "https://cdn.jsdelivr.net/gh/yiguihai/shadowsocks_install@dev";
}
$android_list = file(
    $url . "/conf/android_list",
    FILE_SKIP_EMPTY_LINES,
    stream_context_create($arrContextOptions)
);
if (file_exists($port_list)) {
    $names = file($port_list);
    $i = 0;
    foreach ($names as $name) {
        foreach (explode("|", $name) as $name) {
            $name = explode("^", $name);
            $server = $server_ip;
            switch ($name[0]) {
                case "server_port":
                    $server_port = $name[1];
                    break;
                case "password":
                    $password = $name[1];
                    break;
                case "method":
                    $method = $name[1];
                    break;
                case "plugin":
                    $plugin = $name[1];
                    break;
                case "plugin_opts":
                    $plugin_opts = $name[1];
                    break;
                case "total":
                    $total = $name[1];
                    break;
            }
        }
        $used = used_traffic($server_port);
        $percent = null;
        if (empty($used)) {
            $percent = " Offline";
        }
        if (is_numeric($used) && is_numeric($total)) {
            $percent = " " . round($used / $total, 2) * 100 . "%";
        }
        switch ($plugin) {
            case "obfs-server":
                $plugin = "obfs-local";
                $plugin_opts =
                    $plugin_opts . ";obfs-host=checkappexec.microsoft.com";
                break;
            case "kcptun.sh":
                $plugin = "kcptun";
                break;
            case "v2ray-plugin":
                if (file_exists($tls_cert)) {
                    $v2ray_certraw = trim(
                        str_replace(
                            "-----END CERTIFICATE-----",
                            "",
                            str_replace(
                                "-----BEGIN CERTIFICATE-----",
                                "",
                                file_get_contents($tls_cert)
                            )
                        )
                    );
                }
                if ($ipCheck && !preg_match("[quic|grpc]", $plugin_opts)) {
                    //$server = "1.1.1.0"; //基本不用再优选IP了这个就是速度最快的
                    //$server = $_SERVER['SERVER_NAME'];
                    $server = "1.0.0.0";
                    if (str_contains($plugin_opts, "tls")) {
                        $server_port = "443";
                    } else {
                        $server_port = "80";
                    }
                }
                if (
                    str_contains($plugin_opts, "grpc") &&
                    str_contains($plugin_opts, "tls")
                ):
                    $plugin_opts =
                        "tls;mode=grpc;host=" .
                        plugin_opts_val($plugin_opts, "host") .
                        ";certRaw=" .
                        $v2ray_certraw;
                elseif (str_contains($plugin_opts, "grpc")):
                    $plugin_opts =
                        "mode=grpc;host=" .
                        plugin_opts_val($plugin_opts, "host");
                elseif (str_contains($plugin_opts, "quic")):
                    $plugin_opts =
                        "mode=quic;host=" .
                        plugin_opts_val($plugin_opts, "host") .
                        ";certRaw=" .
                        $v2ray_certraw;
                elseif (str_contains($plugin_opts, "tls")):
                    $plugin_opts =
                        "tls;host=" .
                        plugin_opts_val($plugin_opts, "host") .
                        ";path=" .
                        plugin_opts_val($plugin_opts, "path") .
                        ";certRaw=" .
                        $v2ray_certraw;
                else:
                    $plugin_opts =
                        "host=" .
                        plugin_opts_val($plugin_opts, "host") .
                        ";path=" .
                        plugin_opts_val($plugin_opts, "path");
                endif;
                break;
        }
        //$array["servers"][$i]["id"] = (string) $id;
        $array["servers"][$i]["remarks"] =
            (string) "Server #" . $i + 1 . $percent;
        $array["servers"][$i]["server"] = (string) $server;
        $array["servers"][$i]["server_port"] = (int) $server_port;
        $array["servers"][$i]["password"] = (string) $password;
        $array["servers"][$i]["method"] = (string) $method;
        $array["servers"][$i]["ipv6"] = (bool) false;
        $array["servers"][$i]["route"] = (string) "bypass-china";
        if (is_string($dns)) {
            $array["servers"][$i]["remote_dns"] = (string) $dns;
        }
        if ($plugin && $plugin_opts) {
            $array["servers"][$i]["plugin"] = (string) $plugin;
            $array["servers"][$i]["plugin_opts"] = (string) $plugin_opts;
        } else {
            $udp_list[] = (int) $i;
        }
        if (isset($android_list) && is_array($android_list)) {
            $array["servers"][$i]["proxy_apps"]["enabled"] = (bool) true;
            $array["servers"][$i]["proxy_apps"]["bypass"] = (bool) true;
            $array["servers"][$i]["proxy_apps"][
                "android_list"
            ] = (array) $android_list;
        }
        $array["servers"][$i]["bytes_used"] = (int) $used;
        $array["servers"][$i]["bytes_remaining"] = (int) $total;
        $i++;
    }
}
//https://stackoverflow.com/a/4414669
if (isset($udp_list)) {
    $i = 0;
    foreach ($array["servers"] as $item1) {
        $a = $array["servers"][$i]["server_port"];
        foreach ($udp_list as $item2) {
            $b = $array["servers"][$item2]["server_port"];
            if (is_numeric($a) && is_numeric($b) && $a != $b) {
                $array["servers"][$i]["udpdns"] = (bool) true;
                $array["servers"][$i]["udp_fallback"] = (array) [
                    "server" => (string) $array["servers"][$item2]["server"],
                    "server_port" =>
                        (int) $array["servers"][$item2]["server_port"],
                    "password" =>
                        (string) $array["servers"][$item2]["password"],
                    "method" => (string) $array["servers"][$item2]["method"],
                ];
            }
        }
        $i++;
    }
}
die(str_replace('\n', "", json_encode($array, JSON_NUMERIC_CHECK))); //需要去除证书换行\n否则出错
?>
