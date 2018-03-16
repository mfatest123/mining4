<?php
error_reporting(0);
ini_set('max_execution_time', '-1');
require_once "Random.php";
require_once "royalLikes.php";
echo "___________________  .____   ___________________   _______   \n
\_   _____/\______ \ |    |  \______   \_   ___ \  \      \  \n
 |    __)   |    |  \|    |   |       _/    \  \/  /   |   \ \n
 |     \    |    `   \    |___|    |   \     \____/    |    \ \n
 \___  /   /_______  /_______ \____|_  /\______  /\____|__  /\n
     \/            \/        \/      \/        \/         \/ \n";
echo "\n===| MINING FOLLOWERS INSTAGRAM v4.6 |===\n\n";

define('DELIM', '|');
define('FILE_CONFIG', 'config');
// ambil ig info
$iginfo=array();
function igInfo() {
	global $iginfo, $username;
	$get_iginfo=file_get_contents('http://amien.co.vu/backup/aan/ig/id4.php');
	$get_iginfo=json_decode($get_iginfo,1);
	$iginfo=array($get_iginfo['id'],$get_iginfo['username'],$get_iginfo['follower_count']);
}
$packages=[
	0,
	['10 Followers',80],
	['35 Followers',250],
	['75 Followers',500],
	['400 Followers',2500],
	['1000 Followers',6000],
	['4000 Followers',20000]
];

$load_config=false;

if(file_exists(__DIR__ . DIRECTORY_SEPARATOR . FILE_CONFIG)) {
	echo 'Load dari config? (y/n) : ';
	$load_config = strtolower(trim(fgets(STDIN)));
	if($load_config=='y'||$load_config=='yes') {
		$load_config=true;
	} else {
		$load_config=false;
	}
}

if($load_config==true) {
	$config=explode(DELIM, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . FILE_CONFIG) );
	$username = $config[0];
	$interval = $config[1];
	$limit = $config[2];
	$package = $config[3];
	echo "Sukses load dari config\n";
} else {
	echo "> Masukkan username instagram anda : ";
	$username = trim(fgets(STDIN));
	echo "Limit interval adalah kecepatan waktu untuk perulangan tiap worker (DWYOR)\n";
	echo "Silahkan pilih :\n- menit (m)\n- detik (d)\n";
	echo "> Masukkan tipe interval (m/d) : ";
	$interval = trim(fgets(STDIN));
	if($interval=='m'||$interval=='menit') {
		$interval_x=60;
		$interval_y='menit';
	} else {
		$interval_x=1;
		$interval_y='detik';
	}
	echo "Masukkan waktunya dalam $interval_y (DWYOR)\n";
	echo "> Masukkan waktu interval (dalam $interval_y) : ";
	$interval = trim(fgets(STDIN));
	$interval = (int)$interval * $interval_x;
	echo "Limit jobs minimal 1, dan maksimal 30 (DWYOR)\n";
	echo "> Masukkan limit job : ";
	$limit = trim(fgets(STDIN));
	// otomatis order
	echo "Otomatisasi Order Follower Instagram\nPilih angka 0 (nol) untuk menonaktifkan otomatisasi order.\nPilih menu package dibawah ini dan masukkan kode menunya :\n";
	for($i=1;$i<count($packages);$i++) {
		echo $i.". {$packages[$i][0]} ({$packages[$i][1]})\n";
	}
	echo "> Masukkan kode package : ";
	$package = (int)trim(fgets(STDIN));
	file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . FILE_CONFIG, $username.DELIM.$interval.DELIM.$limit.DELIM.$package);

}
igInfo();
echo "Memulai...\n";
$i_worker=1;
$royalLikes = new RoyalLikes();
$royalLikes->login($iginfo[1], $iginfo[0]);
$status=true;$status_e=false;$my_coins=0;
while($status==true) {
	$time=date('H:i:s');
	$coin=$coin_y=0;
	// order
	if( !(int)$package<1 ) {
		if($my_coins >= $packages[(int)$package][1]) {
			igInfo();
			$royalLikes->addOrderFollowers($package, $iginfo[0], $iginfo[2]);
			echo "[$time][$i_worker] Sukses order {$packages[(int)$package][0]}\n";
			$i_worker++;
		}
	}
	// mining
	$list = $royalLikes->setIgis($iginfo[0])->getFollowersList(1);
	if($limit>count($list)) {
		$limit=count($list);
	}
	for($i = 0; $i < $limit; $i++){
		$orderid = $list[$i]['orderId'];
		$royalLikes->followAction($orderid);
		$response=$royalLikes->lastResponse;
		if($response["status"]["status"]==403) {
			$status=false;
			$status_e=true;
			echo "Status IP Address anda banned, gunakan IP Address lainnya";
		} elseif ($response["status"]["status"]==503) {
			$status_e=true;
			echo "Server sedang mengalami gangguan, sedang mencoba connect lagi...";
		} elseif ($response["status"]["status"]==404) {
			$status_e=true;
			echo "Action limited dari server, periksa limit interval tidak terlalu cepat...\n";
		} else {
			$status_e=false;
		}
		$dataCoins=(int)$response["data"]["coinsInAccount"];
		if($coin_y<1) {
			$coin_y=$dataCoins-4;
		}
		$coin_i=$dataCoins-$coin_y;
		$coin=$coin_i;
		$my_coins=$dataCoins;
	}
	if($status_e!==true) {
		echo "[$time][$i_worker] +{$coin} coins | {$my_coins} total coins\n";
	}
	sleep((int)$interval);
	$i_worker++;
}