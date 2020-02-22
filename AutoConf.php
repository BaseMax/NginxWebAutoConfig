<?php
// Max Base
// https://github.com/BaseMax/NginxWebAutoConfig
// wget https://dl.eff.org/certbot-auto
// sudo mv certbot-auto /usr/local/bin/certbot-auto
// sudo chown root /usr/local/bin/certbot-auto
// sudo chmod 0755 /usr/local/bin/certbot-auto

$domains=[];
$domains[]=[
	"name"=>"site.com",
	"www"=>true,
	"ssl"=>false,
];
// $domains[]=[
// 	"name"=>"maxbase.org",
// 	"www"=>true,
// 	"ssl"=>false,
// 	"subdomains"=>[
// 		"fa",
// 	],
// ];
$domains[]=[
	"name"=>"aterd.com",
	"www"=>true,
	"ssl"=>false,
	"subdomains"=>[
		"mail",
		"net",
		"server",
		"panel",
	],
];
$domains[]=[
	"name"=>"asrez.com",
	"www"=>true,
	"ssl"=>false,
	"subdomains"=>[
		"service",
	],
];
$domains[]=[
	"name"=>"onelang.org",
	"www"=>true,
	"ssl"=>false,
];
// aterd.com,www.aterd.com,server.aterd.com,panel.aterd.com,net.aterd.com,mail.aterd.com
foreach($domains as $domain) {
	setConfig($domain);
}
function supportPHP() {
return "	location ~ \.php\$ {
		fastcgi_pass   127.0.0.1:9000;
		fastcgi_index  index.php;
		fastcgi_param  SCRIPT_FILENAME  \$document_root\$fastcgi_script_name;
		include        fastcgi_params;
	}
";
}
function setConfigSub($domain, $subdomain) {
// $config="server {
// 	#port
// 	listen 80;
// 	#domain
// 	server_name ".$subdomain.".".$domain["name"].";
// 	root   /site/".$domain["name"]."/sub-".$subdomain.";
// ";
// $config.=supportPHP();
// $config.="}
// ";
// 	file_put_contents("/etc/nginx/conf.d/subdomains/$subdomain.".$domain["name"].".conf", $config);
}
function setConfig($domain) {
	if(isset($domain["subdomains"]) and is_array($domain["subdomains"]) and count($domain["subdomains"]) > 0) {
		foreach($domain["subdomains"] as $sub) {
			setConfigSub($domain, $sub);
		}
	}
$config="server {
	#port
	listen 80;
	#domain
	server_name www.".$domain["name"]." ".$domain["name"].";
	root   /site/".$domain["name"]."/root;
";
	if(isset($domain["ssl"]) and $domain["ssl"] == true) {
$config.="	return 301 https://".$domain["name"]."\$request_uri;
";
	}
if(isset($domain["php"]) and $domain["php"] == false) {}
else if(!isset($domain["php"])) {
$config.=supportPHP();
}
$config.="}
";
if(isset($domain["ssl"]) and $domain["ssl"] == true) {
$config.="server {
	#port
	listen 443 ssl;
	#ssl
	ssl_certificate /etc/letsencrypt/live/".$domain["name"]."/fullchain.pem;
	ssl_certificate_key /etc/letsencrypt/live/".$domain["name"]."/privkey.pem;
	include /etc/letsencrypt/options-ssl-nginx.conf;
	ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
	#domain
	server_name www.".$domain["name"].";

	return 301 \$scheme://".$domain["name"]."\$request_uri;
}
server {
	#port
	listen 443 ssl;
	#ssl
	ssl_certificate /etc/letsencrypt/live/".$domain["name"]."/fullchain.pem;
	ssl_certificate_key /etc/letsencrypt/live/".$domain["name"]."/privkey.pem;
	include /etc/letsencrypt/options-ssl-nginx.conf;
	ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
	#domain
	server_name ".$domain["name"].";
	#config
	root   /site/".$domain["name"]."/root;
	index  index.htm index.html index.php;
";
if(isset($domain["php"]) and $domain["php"] == false) {}
else if(!isset($domain["php"])) {
$config.=supportPHP();
}
if(isset($domain["nodejs"]) and $domain["nodejs"] == false) {
$config.="	location /test/ {
		proxy_set_header   X-Forwarded-For \$remote_addr;
		proxy_set_header   Host \$http_host;
		proxy_pass         http://127.0.0.1:4545;
	}
";
}
$config.="}
";
}
	file_put_contents("/etc/nginx/conf.d/".$domain["name"].".conf", $config);
}
