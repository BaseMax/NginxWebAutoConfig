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
// asrez.com,www.asrez.com,service.asrez.com
// aterd.com,www.aterd.com,server.aterd.com,panel.aterd.com,net.aterd.com,mail.aterd.com
// onelang.org, www.onelang.org
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
	$domain["subdomains"]=[];
	$domain["domainDirectory"]=$domain["name"]. "/sub-". $subdomain;
	$domain["sslDomainDirectory"]=$domain["name"];
	$domain["name"]=$subdomain.".".$domain["name"];
	$domain["www"]=false;
	setConfig($domain);
}
function setConfig($domain) {
	if(!isset($domain["domainDirectory"])) {
		$domain["domainDirectory"]=$domain["name"]."/root";
	}
	if(!isset($domain["sslDomainDirectory"])) {
		$domain["sslDomainDirectory"]=$domain["name"];
	}
	if(isset($domain["subdomains"]) and is_array($domain["subdomains"]) and count($domain["subdomains"]) > 0) {
		foreach($domain["subdomains"] as $sub) {
			setConfigSub($domain, $sub);
		}
	}
$config="server {
	#port
	listen 80;
	#domain
";
if($domain["www"] == true) {
$config.="	server_name www.".$domain["name"]." ".$domain["name"].";
";
}
else {
$config.="	server_name ".$domain["name"].";
";
}
$config.="	root   /site/".$domain["domainDirectory"].";
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
if($domain["www"] == true) {
$config.="server {
	#port
	listen 443 ssl;
	#ssl
	ssl_certificate /etc/letsencrypt/live/".$domain["sslDomainDirectory"]."/fullchain.pem;
	ssl_certificate_key /etc/letsencrypt/live/".$domain["sslDomainDirectory"]."/privkey.pem;
	include /etc/letsencrypt/options-ssl-nginx.conf;
	ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
	#domain
	server_name www.".$domain["name"].";

	return 301 \$scheme://".$domain["name"]."\$request_uri;
}
";
}
$config.="server {
	#port
	listen 443 ssl;
	#ssl
	ssl_certificate /etc/letsencrypt/live/".$domain["sslDomainDirectory"]."/fullchain.pem;
	ssl_certificate_key /etc/letsencrypt/live/".$domain["sslDomainDirectory"]."/privkey.pem;
	include /etc/letsencrypt/options-ssl-nginx.conf;
	ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
	#domain
	server_name ".$domain["name"].";
	#config
	root   /site/".$domain["domainDirectory"].";
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
