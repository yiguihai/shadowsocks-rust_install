<?php
//https://codebeautify.org/php-beautifier
error_reporting(1); //https://www.php.net/manual/zh/errorfunc.constants.php

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
function external_ip_address($ipv)
{
    //https://stackoverflow.com/a/36604437
    if ($ipv == 4) {
        $ip = "8.8.8.8";
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }
    if ($ipv == 6) {
        $ip = "2001:4860:4860::8888";
        $sock = socket_create(AF_INET6, SOCK_DGRAM, SOL_UDP);
    }
    if (in_array($ipv, [4, 6])) {
        if (@socket_connect($sock, $ip, 53)) {
            socket_getsockname($sock, $localAddr);
            socket_shutdown($sock, 2);
            socket_close($sock);
            return $localAddr;
        }
    }
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
    if (isset($buf) && $buf != $input) {
        return $buf;
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
    return $used ?? null;
}
//https://shadowsocks.org/en/wiki/SIP008-Online-Configuration-Delivery.html
header("Content-Type: application/json; charset=utf-8");
$array = [
    "version" => (int) 1,
    "servers" => (array) [],
];
$arrContextOptions = [
    "http" => [
        "timeout" => 3,
    ],
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ],
];
$ini_array = parse_ini_file("/etc/ssmanager/conf/config.ini");
$port_list = "/etc/ssmanager/port.list";
$tls_cert = "/etc/ssmanager/ssl/server.cer";
$ipCheck = empty($_SERVER["HTTP_CDN_LOOP"]) ? false : true;
//https://www.geeksforgeeks.org/how-to-get-parameters-from-a-url-string-in-php/
@parse_str(base64_decode($_GET["en_par"]), $en_par);
$route = @$_GET["route"] ?? $en_par["route"];
$remote_dns = @$_GET["remote_dns"] ?? $en_par["remote_dns"];
$bypass_app = @$_GET["bypass_app"] ?? $en_par["bypass_app"];
if (file_exists($port_list)) {
    $my_ipv4 = external_ip_address(4);
    if (empty($my_ipv4)) {
        @exec("ip -4 -o route get to 8.8.8.8", $out4, $ret4);
        if ($ret4 == 0) {
            $my_ipv4 = explode(" ", explode("src", $out4[0])[1])[1];
        }
    }
    $my_ipv4 = filter_var(
        $my_ipv4,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
    $my_ipv6 = external_ip_address(6);
    if (empty($my_ipv6)) {
        @exec("ip -6 -o route get to 2001:4860:4860::8888", $out6, $ret6);
        if ($ret6 == 0) {
            $my_ipv6 = explode(" ", explode("src", $out6[0])[1])[1];
        }
    }
    $my_ipv6 = filter_var(
        $my_ipv6,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
    if (empty($my_ipv4)) {
        $my_ipv4 = file(
            "https://v4.ipv6-test.com/api/myip.php",
            FILE_SKIP_EMPTY_LINES,
            stream_context_create($arrContextOptions)
        )[0];
    }
    if (empty($my_ipv6)) {
        $my_ipv6 = file(
            "https://v6.ipv6-test.com/api/myip.php",
            FILE_SKIP_EMPTY_LINES,
            stream_context_create($arrContextOptions)
        )[0];
    }
    switch ($ini_array["Protocol"]) {
        case "auto":
            if (isset($my_ipv4)) {
                unset($my_ipv6);
                $server_addr = $my_ipv4;
            } else {
                $server_addr = $my_ipv6;
            }
            break;
        case "ipv4":
            unset($my_ipv6);
            $server_addr = $my_ipv4;
            break;
        case "ipv6":
            unset($my_ipv4);
            $server_addr = $my_ipv6;
            break;
    }
    if (empty($server_addr)) {
        exit();
    }
    if ($bypass_app == "true") {
        if (file_exists("/tmp/android_list")) {
            if (time() - filemtime("/tmp/android_list") > 86400) {
                unlink("/tmp/android_list");
            }
        }
        if (is_file("/tmp/android_list") == false) {
            if (
                !file_put_contents(
                    "/tmp/android_list",
                    file_get_contents(
                        "https://github.com/yiguihai/shadowsocks_install/raw/dev/conf/android_list",
                        false,
                        stream_context_create($arrContextOptions)
                    ),
                    LOCK_EX
                )
            ) {
                exit();
            }
        }
        $android_list = file("/tmp/android_list", FILE_SKIP_EMPTY_LINES);
    }
    $names = file($port_list, FILE_SKIP_EMPTY_LINES);
    $i = 0;
    foreach ($names as $name) {
        foreach (explode("|", $name) as $name) {
            $name = explode("^", $name);
            //$server = $_SERVER["SERVER_ADDR"];
            $server = $server_addr;
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
                //if ($ipCheck && !preg_match("[quic|grpc]", $plugin_opts)) {
                if ($ipCheck and !strrpos($plugin_opts, "quic")) {
                    if (
                        strrpos($plugin_opts, "grpc") and
                            strrpos($plugin_opts, "tls") or
                        !strrpos($plugin_opts, "grpc")
                    ) {
                        $server =
                            @$_GET["cloudflare_ip"] ?? $en_par["cloudflare_ip"];
                        if (empty($server)) {
                            $server = $_SERVER["SERVER_NAME"];
                        }
                        if (str_contains($plugin_opts, "tls")) {
                            $server_port = "443";
                        } else {
                            $server_port = "80";
                        }
                    }
                }
                if (
                    str_contains($plugin_opts, "grpc") &&
                    str_contains($plugin_opts, "tls")
                ):
                    $plugin_opts =
                        "tls;mode=grpc;host=" .
                        plugin_opts_val($plugin_opts, "host") .
                        ";serviceName=" .
                        plugin_opts_val($plugin_opts, "serviceName") .
                        ";certRaw=" .
                        $v2ray_certraw;
                elseif (str_contains($plugin_opts, "grpc")):
                    $plugin_opts =
                        "mode=grpc;host=" .
                        plugin_opts_val($plugin_opts, "host") .
                        ";certRaw=" .
                        $v2ray_certraw;
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
        $array["servers"][$i]["remarks"] =
            (string) "Server #" . $i + 1 . $percent;
        $array["servers"][$i]["server"] = (string) $server;
        $array["servers"][$i]["server_port"] = (int) $server_port;
        $array["servers"][$i]["password"] = (string) $password;
        $array["servers"][$i]["method"] = (string) $method;
        if ($ini_array["Protocol"] == "ipv6") {
            $array["servers"][$i]["ipv6"] = (bool) true;
        } else {
            $array["servers"][$i]["ipv6"] = (bool) false;
        }
        if (is_string($route)) {
            $array["servers"][$i]["route"] = (string) $route;
        }
        if (is_string($remote_dns)) {
            $array["servers"][$i]["remote_dns"] = (string) $remote_dns;
        }
        if ($plugin && $plugin_opts) {
            $array["servers"][$i]["plugin"] = (string) $plugin;
            $array["servers"][$i]["plugin_opts"] = (string) $plugin_opts;
        } else {
            $udp_list[] = (int) $i;
        }
        if (is_array($android_list)) {
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
