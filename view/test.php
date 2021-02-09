<?
// ЧПУ ---
	if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/d-url-rewriter.php')) {
		include_once($_SERVER['DOCUMENT_ROOT'] . '/d-url-rewriter.php');
		durRun ();
	}
// --- ЧПУ
	/*
	  TAG.Domino - система создания и управления WEB-сайтами
	  (C) 2006 TAG.Technology (anisimov@tagtech.ru)

	  index.php - файл начальной инициализации системы и загрузчик FRONT-END парсера
			  Работа системы ВСЕГДА начинается со включения этого файла
			  Используйте конструкцию require $_SERVER['DOCUMENT_ROOT'].'/index.php';
	*/

# Для оптимизации используйте функцию timer()
	timer(TRUE); // Инициализация таймера

// Установки по умолчанию.
// Переопределяются в work/ACCOUNT_START.php, work/PRIVATE/VAR/SETTINGS.var.php
	$SETTINGS=array(
		'sessionName'		=>	'TAG_DOMINO_SESSION',
		'mimeCharset'		=>	'windows-1251',
		'defaultWorkDir'	=>	'work',
		'tagPregTemplate'	=>	'/{& ([#a-zA-Z0-9\._]+) &}/',
		'dbName'		=>	'myBase',
		'dbHost'		=>	'localhost',
		'dbUser'		=>	'root',
		'dbPassword'		=>	'',
		'debugMode'		=>	1,
		'rootAdmin'		=>	1,
		'siteName'		=>	'Новый сайт',
		'siteManual'		=>	'',
		'userInfoType'		=>	'login',
		'dateFormat'		=>	'd.m.y',
		'timeFormat'		=>	'H:i',
		'useDescription'	=>	1,
		'textareaRows'		=>	20,
		'textareaCols'		=>	60,
		'textSize'		=>	60,
		'filePerms'		=>	'666',
		'dirPerms'		=>	'777',
		'hideFO'		=>	0,
		'hideFOmessage'		=>	'Сайт находится на стадии разработки и наполнения.
	 				<br />Посетите эту страницу позже.',
		'loginForBegin'		=>	'admin',
		'passwordForBegin'	=>	'system',
		'systemName'		=>	'TAG.Domino',
		'systemSupportEmail'	=>	'web@tagtech.ru',
		'siteAdminEmail'	=>	'',
		'modPagesNoShowLevel'	=>	3,
		'backEndSkin'		=>	'',
		'advancedAccess'	=>	0
	);

# Cистемные пути
	setPath('system', $_SERVER['DOCUMENT_ROOT'].'/system/');
	// Аварийные утилиты для работы с базой данных
	setPath('sysSqlDumpsFile', path('system').'utils/sqlDumps.php');
	setPath('sysSqlCreateDbFile', path('system').'utils/sqlCreateDb.php');
	setPath('sysSqlDbSettingsFile', path('system').'utils/sqlDbSettings.php');

	setPath('uriParserFile', path('system').'uriParser.php');
	setPath('sysLib', path('system').'LIB/');
	setPath('sysTags', path('system').'TAGS/');
	setPath('sysAdmin', path('system').'BE_LIB/');
	setPath('sysHttp', path('system').'HTTP/');
	setPath('sysInstall', path('system').'INSTALL/');
# Данные, относящиеся к конкретному сайту хранятся в рабочем каталоге.
# Выбор рабочего каталога
	$singleSiteDir=$_SERVER['DOCUMENT_ROOT']."/".$SETTINGS['defaultWorkDir']."/";
	$multiSiteDir=$_SERVER['DOCUMENT_ROOT']."/".$_SERVER['SERVER_NAME']."/";
	if(is_dir($singleSiteDir)) setPath('work', $singleSiteDir);
	elseif(is_dir($multiSiteDir)) setPath('work', $multiSiteDir);
	else{
		$DH=NEW DEFAULT_HTML();
		$DH->fatalError('noWorkDir', "Фатальная ошибка: невозможно определить рабочий каталог. Каталоги $singleSiteDir и $multiSiteDir не найдены.");
	}
// Пути аккаунта
	setPath('modules', path('work').'MOD/');
	setPath('cache', path('work').'CACHE/');
	setPath('files', path('work').'FILES/');
	setPath('http', path('work').'HTTP/');
	setPath('private', path('work').'PRIVATE/');
	setPath('sqlDumps', path('private').'SQL_DUMPS/');
	setPath('accountFile', path('private').'ACCOUNT_START.php');
	setPath('modFile', path('private').'MODULES_INFO.php');
	setPath('tmp', path('private').'TMP/');
	setPath('typesFile', path('private').'PAGES_TYPES.var.php');
	setPath('settingsFile', path('private').'SETTINGS.var.php');
	setPath('upload', path('private').'UPLOAD/');
	setPath('templates1', path('private').'TEMPLATES_1/');
	setPath('templates2', path('private').'TEMPLATES_2/');
	setPath('templates3', path('private').'TEMPLATES_3/');
	setPath('formats', path('private').'FORMATS/');
	setPath('includes', path('private').'INCLUDES/');
	setPath('tags', path('private').'TAGS/');
	setPath('lib', path('private').'LIB/');

# Сессия стартует только в случае получения cookie
# Директива session.auto_start работает не всегда корректно
	ini_set('url_rewriter.tags', '');
	if(!empty($_COOKIE[$SETTINGS['sessionName']])){
		session_name($SETTINGS['sessionName']);
		session_id($_COOKIE[$SETTINGS['sessionName']]);
		session_start();
	}else{
		header(sprintf('Set-Cookie: %s=%s; path=/', $SETTINGS['sessionName'], md5(microtime().getmypid()) ));
		$_SESSION=array();
	}

	if(!isset($_SESSION['foPanel'])) $_SESSION['foPanel']=0;

	define('FRONT_END', 'FE');
	define('BACK_END', 'BE');

# Система работает только в двух режимах: FRONT-END (FE) И BACK-END (BE).
# в режиме BACK-END этот файл подключается с помощью include/require из другого php-файла,
# в режиме FRONT-END этот файл вызывается непосредственно WEB-сервером

	if(
		$_SERVER['SCRIPT_FILENAME']===__FILE__ or
		// Осторожно с платформой Microsoft.
		// Там используются "неправильные" слэши и буквы дисков
		(substr(__FILE__, 0, 1)==='\\' and $_SERVER['SCRIPT_FILENAME']===str_replace('\\', '/', __FILE__)) or
		$_SERVER['SCRIPT_FILENAME']===strstr(str_replace('\\', '/', __FILE__),$_SERVER['SCRIPT_FILENAME'])
	) define('SYSTEM_MODE', FRONT_END);
	else define('SYSTEM_MODE', BACK_END);

# Запуск и поддержка процедуры инсталляции аккаунта
	if(!is_file(path('accountFile'))){
		if(!strstr($_SERVER['REQUEST_URI'], uri('sysInstall'))) redirect(uri('sysInstall'));
		else return;
	}

	require path('accountFile');

# на многих хостингах работать не будет.
# полезно на локальной машине при разработке сайта
	setting('debugMode') and error_reporting(E_ALL);
# Пользовательские типы страниц подгружаются из файла с сериализованным массивом


	$PTYPES=arrayRestore(path('typesFile'));

// Конвертация старых файлов.
// $PTYPES=arrayRestoreOld(path('private').'VAR/PAGES_TYPES');
// arraySave($PTYPES, path('typesFile'));

# Подключение БД аккаунта. Без подключения БД возможна работа только в режиме инсталляции.
	define('CONN', @mysql_pconnect(setting('dbHost'), setting('dbUser'), setting('dbPassword')));
	if(!CONN){
		$DH=NEW DEFAULT_HTML();
		$DH->fatalError('dbNoConnect');
	}
	if(!mysql_select_db(setting('dbName'), CONN)){
		$DH=NEW DEFAULT_HTML();
		$DH->fatalError('dbNoSelect');
	}

# Подключаем библиотеки страниц и типов
	require libPatch('libPages.php');
	require libPatch('libTypes.php');

# Если работа в режиме FRONT_END, загружаем uri parser
	if(SYSTEM_MODE==FRONT_END) include path('uriParserFile');

############################################################## FUNCTIONS
############################ ФУНКЦИИ, ИСПОЛЬЗУЕМЫЕ В ЛЮБОМ МЕСТЕ СИСТЕМЫ

	function parser($template, $array)
	{
		foreach($array as $key => $value) $template=str_replace("{ $key }", $value, $template);
		return $template;
	}


	function systemVersion($currentRed=FALSE)
	{
		$workFile=path('sysLib').'changeLog';
		if(!is_file($workFile) or !is_readable($workFile) or ($fileSize=filesize($workFile))==0) return FALSE;
		$file=file($workFile);
		$lastString=$file[count($file) - 1];
		$content=strtok($lastString, ' ');
		preg_match('/^[0-9]+.([0-9]+)./', $content, $matches);
		$minor=$matches[1];
		$content.=$minor % 2 ? ' Current' : ' Stable';
		if($currentRed) $content=str_replace('Current', '<span style="color: red">Current</span>', $content);
		return $content;
	}

	function timer($reset=FALSE)
// используется для профилирования быстродействия
// выводит в браузер время, прошедшее с момента запуска "загрузчика"
// если передан не пустой параметр, сбрасывает таймер, устанавливая новую "контрольную точку"
	{
		if($reset) $GLOBALS['microtimeCheckPoint']=microtime();
		else{
			$currentMicrotime=microtime();
			list($float, $int)=explode(' ', $GLOBALS['microtimeCheckPoint']);
			$start=$int+$float;
			list($float, $int)=explode(' ', $currentMicrotime);
			$current=$int+$float;
			$vector=$current-$start;
			return substr((string) $vector, 0, -11).' sec';
		}
	}

/////////// Функции для работы с установками
// Общие
	function setSetting($key, $value){
		global $SETTINGS;
		$SETTINGS[$key]=$value;
		return TRUE;
	}
	function setting($key){
		global $SETTINGS;
		return $SETTINGS[$key];
	}
// Установки путей и uri, всего лишь частный случай, но так удобнее
	function setPath($key, $value){
		global $SETTINGS;
		$SETTINGS['directories'][$key]=$value;
		return TRUE;
	}

	function path($key)
// Возвращает системный путь с именем $key
	{ return $GLOBALS['SETTINGS']['directories'][$key]; }

	function libPatch($baseName) {
		// Используется локальная копия библиотеки
		if(isset($GLOBALS['SETTINGS']['localLibs'][$baseName])) return $GLOBALS['SETTINGS']['directories']['lib'].$baseName;
		// Используется системная библиотека
		else return $GLOBALS['SETTINGS']['directories']['sysLib'].$baseName;
	}
	function localLib(){
		$funcArgs=func_get_args();
		foreach($funcArgs as $baseName) $GLOBALS['SETTINGS']['localLibs'][$baseName]=TRUE;
		return TRUE;
	}

	function uri($key, $fullUrl=NULL)
// Возвращает URI для системного пути $key
// Если второй параметр задан, возвращает полный URL, включая имя протокола и сервера
	{
		$patch=$GLOBALS['SETTINGS']['directories'][$key];
		$uri=str_replace($_SERVER['DOCUMENT_ROOT'], '', $patch);
		if($fullUrl==NULL) return $uri;
		else return sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $uri);
	}

	function sqlSet($sqlArray)
// преобразование хэш-массива в SQL-строку.
	{
		$sql="\n";
		foreach($sqlArray as $key=>$value){
			if(is_numeric($value)){
				$quote="%s";
			}elseif($value===NULL or $value=='NULL'){
				$value='NULL';
				$quote='%s';
			}else{
				$value=addslashes($value);
				$quote="'%s'";
			}
			$sql.="\t$key=".sprintf($quote, $value).", \n";
		}
		$sql=substr($sql, 0, -3)."\n";
		return $sql;
	}

	function sqlQuote($string){
		if($string===NULL) return 'NULL';
		return sprintf(($string=='NULL' ? '%s' : "'%s'"), $string);
	}

	function checkAccess($resource, $user_id='this')
// if access denied, return false, else return true
# проверка ведется для текущего пользователя, если второй аргумент не
# задан, или для пользователя $user_id
# с одним аргументом работает намного быстрее
# Для проверки доступа используйте только эту функцию, т.к. в будующем
# работа с доступом может меняться, но совместимость с этой функцией
# сохранится
	{
		if($user_id=='this'){
			// администратор системы
			if($resource=='ALL') return $_SESSION['user']['admin'];
			// администратор имеет доступ ко всем остальным ресурсам
			elseif($_SESSION['user']['admin']) return TRUE;

			global $user_access, $user;
			$user_id=$_SESSION['user']['id'];
		}

		# массив прав сохраняется как глобальный для повышения скорости
		# (только если функция вызывается с одним аргументом)
		if(!isset($user_access)){
			$user_access=array();
			$sql="SELECT resource FROM access WHERE uid=$user_id";
			$result=mysql_query($sql, CONN);
			for($i=0; $rs=@mysql_result($result, $i, 0); $i++) $user_access[$i]=$rs;
			mysql_free_result($result);
		}

		return(in_array($resource, $user_access));
	}

	function debug($variable, $die=FALSE)
// красиво печатает в html массивы и объекты. Используется для отладки
	{
		echo '<pre>';
		print_r($variable);
		echo '</pre>';
		if($die) exit;
	}

	function redirect($uri=NULL)
// перенаправление на адрес $uri и завершение сценария
	{
		if($uri=='self') $uri=$_SERVER['PHP_SELF'];
		if($uri===NULL or $uri=='request') $uri=$_SERVER['REQUEST_URI'];
		if($uri=='static') $uri=URI;
		header('Location: '.$uri,true,301);
		exit();
	}

	function sendMessage($to, $subj, $content, $from=NULL)
// отправка простого письма
	{
		if($from===NULL) $from=sprintf('mailrobot@%s', $_SERVER['HTTP_HOST']);

		$headers=
			"MIME-Version: 1.0\r\n".
			"Content-Type: text/plain; charset=".setting('mimeCharset').";\r\n".
			"Content-Transfer-Encoding: 8bit\r\n".
			"X-mailer: TAG.Domino version ".systemVersion()." on $_SERVER[HTTP_HOST]\r\n".
			"From: $from\r\n";

		return mail($to, $subj, $content, $headers);
	}

	function checkEmailSintax($email)
	{
		if(!$email) return TRUE;
		return ereg("^([-a-zA-Z0-9._]+@[-a-zA-Z0-9.]+(\.[-a-zA-Z0-9]+)+)*$", $email);
	}

	function fileChmod($target){
		$code=sprintf("chmod('%s', 0%s);", $target, setting('filePerms'));
		return eval($code);
	}

	function dirChmod($target){
		$code=sprintf("chmod('%s', 0%s);", $target, setting('dirPerms'));
		return eval($code);
	}

	function getPreview($fullName, $width, $height)
// Возвращает массив с информацией об иконке предпросмотра, если нужно, перед этим создает эту иконку
// fullName - полное имя исходного (большого изображения)
// width и height - максимальные ширина и высота
	{
		if(!is_file($fullName) or !is_readable($fullName)) return FALSE;
		$hashString=sprintf('%s.%sx%s', str_replace(path('work'), '', $fullName), $width, $height);
		// получить расширение
		preg_match('/\.([a-zA-Z0-9]+)$/', $fullName, $matches);
		$ext=$matches[1];
		// имя кэшированного файла
		$cacheName=path('cache').md5($hashString).'.'.$ext;
		// нужно создать превьюшку в каталоге кэша
		// или исходный файл изменен позже кэшированного
		if(!is_file($cacheName) or filemtime($fullName) > filemtime($cacheName)){
			createSmall($fullName, $cacheName, $width, $height);
		}

		if(!$imgInfo=getImageSize($cacheName)) return FALSE;

		$targetInfo=array(
			'uri'=>str_replace($_SERVER['DOCUMENT_ROOT'], '', $cacheName),
			'width'=>$imgInfo[0],
			'height'=>$imgInfo[1]
		);
		return $targetInfo;
	}

	function createSmall($sourceName, $targetName, $MaxX=50,$MaxY=30)
// Создает уменьшенную копию изображения
// В основном нужна для функции getPreview()
	{
		$imgInfo=GetImageSize($sourceName);

		switch ($imgInfo[2]){
			case 1: $ImgExt = "gif"; break;
			case 2: $ImgExt = "jpeg"; break;
			case 3: $ImgExt = "png"; break;
			default : return FALSE;
		}

		if(($imgInfo[0] > $MaxX) || ($imgInfo[1] > $MaxY)){

			if(!is_writable($d=dirname($targetName))){
				if(SYSTEM_MODE==BACK_END) message('dirNotWritable', $d);
				return false;
			}

			$Yt = $imgInfo[1] / $MaxY;
			$XSize = ceil($imgInfo[0] / $Yt);
			$YSize = $MaxY;
			if($XSize > $MaxX){
				$XSize=$MaxX;
				$Xt=$imgInfo[0] / $MaxX;
				$YSize=ceil($imgInfo[1] / $Xt);
			}
			$funcName='ImageCreateFrom'.$ImgExt;
			$TempImg=$funcName($sourceName);
			$SmallImg = ImageCreateTrueColor($XSize,$YSize);
			ImageCopyResized(
				$SmallImg,
				$TempImg,
				0,
				0,
				0,
				0,
				$XSize,
				$YSize,
				$imgInfo[0],
				$imgInfo[1]
			);
			$funcName="image".$ImgExt;
			$funcName($SmallImg, $targetName);
			filePerms($targetName);
		}else{
			$result=copy($sourceName, $targetName);
			filePerms($targetName);
			return $result;
		}

		return TRUE;
	}


	function arraySave($array, $fileName)
// $array - произвольный массив
// $fileName - файл для записи массива
// пишет сериализованный массив в определенный файл, возвращает
// TRUE в случае удачной записи или FALSE в случае ошибки
	{
		$phpTpl="<?\n
// Этот файл перезаписывается системой.
// Вы можете вносить изменения, но учтите, что при перезаписи
// будут сохранены только значения массива \$array.
// Это касается всех файлов с суффиксом \".var.php\"
// Динамические настройки лучше хранить в статичных PHP-файлах (c cуффиксом \".php\")\n
\$array = %s;
?>";
		$str=sprintf($phpTpl, array2string($array));
		if($fp=fopen($fileName,'w')){;
			$affect=fwrite($fp,$str);
			fclose($fp);
			return $affect;
		}else return FALSE;
	}

	function arrayRestore($fileName)
// $fileName - файл с cериализованным массивом
// возвращает массив или FALSE в случае ошибки
	{
		if(
			is_file($fileName) and
			is_readable($fileName) and
			filesize($fileName) > 0
		){
			if(!$fp=fopen($fileName, 'r')) return FALSE;
			$str=fread($fp,filesize($fileName));
			fclose($fp);
			$array=@unserialize($str);
			if(is_array($array)) return $array;
			else{
				include $fileName;
				return $array;
			}
		}else return FALSE;
	}


	function array2string($array, $level=0)
// Преобразует массив в строку (в виде PHP-кода)
// В основном служебная для arraySave(). Рекурсивная.
	{
		if(empty($array)) return 'array()';

		$tab0=str_repeat("\t", $level);
		$level++;
		$tab=str_repeat("\t", $level);

		$mainTpl="array(\n%s$tab0)";
		$elementTpl="$tab'%s' => '%s',\n";
		$arrayTpl="$tab'%s' => %s,\n";

		$string='';
		foreach($array as $key => $value){
			if (is_array($value)) $string.=sprintf($arrayTpl, $key, array2string($value, $level));
			else  $string.=sprintf($elementTpl, $key, str_replace("'", "\'", $value));
		}
		return sprintf($mainTpl, $string);
	}


	class DEFAULT_HTML
	{
		var $title;
		var $h1; // По умолчанию не задается - используется $title
		var $strongTxt;
		var $normalTxt;
		var $linksArray;

		var $sysName='TAG.Domino';
		var $mainTpl=
			// 1) Заголовок (title, он же h1)
			// 2) uri('sysHttp')
			// 3) strong text
			// 4) нормальный текст (основной)
			// 5) элементы списка ссылок
			'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>

  <head>
    <title>%1$s</title>
    <link rel="stylesheet" type="text/css" href="%2$sstyle.css" />
    <link rel="shortcut icon" href="%2$sx.gif" type="image/icon" />
    <script src="%2$sfunctions.js"></script>
  </head>

<body id="frontEndBody">
<h1>%1$s</h1>
<strong>%3$s</strong>
%4$s
<hr />
</body>
</html>
';
//<h2>Ссылки, которые могут быть полезны</h2>
//<ul>
//%5$s
//</ul>


		var $errorInfo='
<p>
  Большинство фатальных сбоев системы связаны с ошибками базы данных. Они могут появляться
  в начале инсталляции системы, ошибках в адмнинистрировании или в результате сбоев на
  сервере. Резервное копирование информации многократно снижает риск потери важных  данных.
</p>
<p>
  Также необходимо следить за тем, чтобы права доступа к файлам и каталогам файловой системы
  были правильными. Это особенно актуально на UNIX-подоных операционных системах.
</p>
';

		// 1) - email разработчиков
		var $supportMessageTpl='
<p>
  Система %1$s активно разрабатывается. Если у Вас возникли
  проблемы с ее использованием, а также если Вы хотите задать вопрос или
  поделиться новыми идеями, <a href="mailto:%2$s">обращайтесь к разработчикам</a> системы.
</p>
';

		var $liTpl="  <li>%s</li>\n";
		var $aTpl=': <a href="%1$s">%1$s</a>';
		var $adminEmailTpl='Email администрации сайта: <a href="mailto:%1$s">%1$s</a>';
		var $sysEmailTpl='Разработка и поддержка системы %1$s: <a href="mailto:%2$s">%2$s</a>';

		var $liLogin='Вход в систему управления сайтом';
		var $liDumps='Работа с дампами базы данных';
		var $liDbSettings='Настройка подключения к базе данных';
		var $liCreateDb='Создание базы данных:';

		var $message404='<!--notfound-->
<p>
  Вы можете <a href="/">перейти на главную страницу сайта</a>
</p>
';


		function DEFAULT_HTML(){
			$this->linksArray=array();
		}

		function addLinks(){
			$links=func_get_args();
			if(in_array('login', $links)) $this->linksArray[]=sprintf($this->liLogin.$this->aTpl, uri('system', 1));
			if(in_array('dumps', $links)) $this->linksArray[]=sprintf($this->liDumps.$this->aTpl, uri('sysSqlDumpsFile', 1));
			if(in_array('dbSettings', $links)) $this->linksArray[]=sprintf($this->liDbSettings.$this->aTpl, uri('sysSqlDbSettingsFile', 1));
			if(in_array('createDb', $links)) $this->linksArray[]=sprintf($this->liCreateDb.$this->aTpl, uri('sysSqlCreateDbFile', 1));
		}

		function fatalError($errorCode, $additionInfo=NULL){
			switch($errorCode){
				case 'dbNoConnect':
					$message="Ошибка базы данных: не удается подключиться к серверу баз данных<br />".mysql_error();
					unset($_SESSION['user']);
					$this->addLinks('dbSettings');
					break;
				case 'dbNoSelect':
					$message="Ошибка базы данных: не удается выбрать рабочую базу данных<br />".mysql_error(CONN);
					unset($_SESSION['user']);
					$this->addLinks('dbSettings');
					// Может просто нет такой базы данных?
					if(mysql_errno(CONN)==1049){ // Предлагаем создание
						// Проверка наличия прав создания базы данных
						// Упрощенный вариант - работает только для пользователя типа root
						// Нужно уточнить
						$sql="SHOW GRANTS";
						$result=mysql_query($sql, CONN);
						$grants=mysql_result($result, 0, 0);
						if(preg_match('/ALL PRIVILEGES ON *.*/i', $grants)) $this->addLinks('createDb');
					}
					break;
				case 'dbNoTables':
					$message="В базе данных отсутствуют таблицы. Нужно произвести импорт дампа базы данных, либо восстановить ее.";
					unset($_SESSION['user']);
					$this->addLinks('dumps');
					break;
				case 'dbNoRootPage':
					$message='Корневая страница не существует. Ее необходимо создать через интерфейс администратора.';
					$this->addLinks('login');
					break;
				case 'noWorkDir':
					$message=$additionInfo;
					break;
			}
			$this->title='Фатальная ошибка';
			$this->strongTxt=$message;
			$this->normalTxt=
				$this->errorInfo.
				sprintf($this->supportMessageTpl, $this->sysName, setting('systemSupportEmail'));
			$this->stop();
		}

		function error404(){
			$this->title='Данная страница не существует!';
			$this->strongTxt=sprintf('Запрошенная страница http://%s не найдена',
				$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']
			);
			$this->normalTxt=$this->message404;
			$this->linksArray[]=sprintf('Главная страница сайта'.$this->aTpl,
				sprintf('http://%s/', $_SERVER['HTTP_HOST'])
			);
			$this->stop();
		}

		function hideFO(){
			$this->title='Сайт заблокирован';
			$this->strongTxt=setting('hideFOmessage');
			$this->stop();
		}

		function stop(){
			if(setting('siteAdminEmail')!='') array_unshift($this->linksArray, sprintf($this->adminEmailTpl, setting('siteAdminEmail')));
			$this->linksArray[]=sprintf($this->sysEmailTpl, $this->sysName, setting('systemSupportEmail'));
			$links='';
			foreach($this->linksArray as $link) $links.=sprintf($this->liTpl, $link);
			printf($this->mainTpl,
				$this->sysName.': '.$this->title,
				uri('sysHttp'),
				$this->strongTxt,
				$this->normalTxt,
				$links
			);
			exit();
		}
	}

	function arrayRestoreOld($fileName)
	{
		if(
			is_file($fileName) and
			is_readable($fileName) and
			filesize($fileName) > 0
		){
			if(!$fp=fopen($fileName, 'r')) return FALSE;
			$str=fread($fp,filesize($fileName));
			fclose($fp);
			return unserialize($str);
		}else return FALSE;
	}

	function cp1251toUpper($string)
	{

		$alpha=array(
			'а' => 'А',
			'б' => 'Б',
			'в' => 'В',
			'г' => 'Г',
			'д' => 'Д',
			'е' => 'Е',
			'ё' => 'Ё',
			'ж' => 'Ж',
			'з' => 'З',
			'и' => 'И',
			'й' => 'Й',
			'к' => 'К',
			'л' => 'Л',
			'м' => 'М',
			'н' => 'Н',
			'о' => 'О',
			'п' => 'П',
			'р' => 'Р',
			'с' => 'С',
			'т' => 'Т',
			'у' => 'У',
			'ф' => 'Ф',
			'х' => 'Х',
			'ц' => 'Ц',
			'ч' => 'Ч',
			'ш' => 'Ш',
			'щ' => 'Щ',
			'ы' => 'Ы',
			'ь' => 'Ь',
			'ъ' => 'Ъ',
			'э' => 'Э',
			'ю' => 'Ю',
			'я' => 'Я'
		);

		foreach($alpha as $lower => $upper) $string=str_replace($lower, $upper, $string);

		return strtoupper($string);

	}

	function getVarAdd($varsArray, $uri=NULL)
	{
		if($uri===NULL) $uri=$_SERVER['REQUEST_URI'];
		$uriString=strtok($uri, '?');
		$queryString=strtok('');
		if($queryString){
			$query=explode('&', $queryString);
			$oldVars=array();
			foreach($query as $param){
				if(strstr($param, '=')){
					list($key, $value)=split('=', $param);
					$key=urldecode($key);
					$value=urldecode($value);
					$oldVars[$key]=$value;
				}else{
					$key=urldecode($param);
					$oldVars[$key]=TRUE;
				}
			}
			$varsArray=array_merge($oldVars, $varsArray);
			$firstSpliter='?';
		}else $firstSpliter='&amp;';
		$pairs=array();
		foreach($varsArray as $key => $value) $pairs[]=$key.'='.$value;
		return $uriString.$firstSpliter.implode($pairs, '&amp;');
	}

?>