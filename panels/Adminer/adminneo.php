<?php
/**
 * AdminNeo - Powerful database manager in a single PHP file
 * v5.8.0
 *
 * Compiled with
 * drivers:   mysql, pgsql, sqlite
 * languages: all
 * themes:    default-blue, default-green, default-orange, default-purple, default-red
 * config:    no
 *
 * @link https://www.adminneo.org/
 *
 * @author Peter Knut
 * @author Jakub Vrana (https://www.vrana.cz/)
 *
 * @copyright 2007-2025 Jakub Vrána
 * @copyright 2024-2025 Peter Knut
 *
 * @license Apache License, Version 2.0 (https://www.apache.org/licenses/LICENSE-2.0)
 * @license GNU General Public License, version 2 (https://www.gnu.org/licenses/gpl-2.0.html)
 */namespace
AdminNeo;use
Exception;use
stdClass;use
PDO;use
PDOStatement;use
mysqli;use
mysqli_result;use
SQLite3;use
SQLite3Result;abstract
class
Plugin{protected$admin;protected$config;protected$settings;protected$locale;function
inject($Ba,Config$cc,Settings$M,Locale$ah){$this->admin=$Ba;$this->config=$cc;$this->settings=$M;$this->locale=$ah;}}abstract
class
Origin
extends
Plugin{private$errors=[];private
static$instance=null;static
function
create(array$cc=[],array$Cj=[]){if(self::$instance)die("Admin instance already exists.\n");$Ba=new
static();if(!$cc&&file_exists("adminneo-config.php")){$cc=include_once("adminneo-config.php");if(!is_array($cc)){$cc=[];$Sg="href=https://github.com/adminneo-org/adminneo#configuration ".target_blank();$Ba->addError(lang(0,"<b>adminneo-config.php</b>")." <a $Sg>".lang(1)."</a>");}}$cc=new
Config($cc);$M=new
Settings($cc);if(!$Cj&&file_exists("adminneo-plugins.php")){$Cj=include_once("adminneo-plugins.php");if(!is_array($Cj)){$Cj=[];$Sg="href=https://github.com/adminneo-org/adminneo#plugins ".target_blank();$Ba->addError(lang(0,"<b>adminneo-plugins.php</b>")." <a $Sg>".lang(1)."</a>");}}self::$instance=$Cj?new
Pluginer($Ba,$Cj):$Ba;$Ba->inject(self::$instance,$cc,$M,Locale::get());foreach($Cj
as$Bj)$Bj->inject(self::$instance,$cc,$M,Locale::get());return
self::$instance;}static
function
get(){if(!self::$instance)die("Admin instance not found. Create instance by Admin::create() method at first.\n");return
self::$instance;}protected
function
__construct(){}function
getConfig(){return$this->config;}function
getSettings(){return$this->settings;}abstract
function
getOperators();function
getLikeOperator(){return
Driver::get()->getLikeOperator();}function
getRegexpOperator(){return
null;}function
init(){}function
addError($j){$this->errors[]=$j;}function
getErrors(){return$this->errors;}abstract
function
getServiceTitle();function
getCredentials(){$L=$this->config->getServer(SERVER);return[$L?$L->getServer():SERVER,$_GET["username"],get_password()];}function
verifyDefaultPassword($D){$gf=$this->config->getDefaultPasswordHash();if($gf===null||$gf==="")return
lang(2);elseif(!password_verify($D,$gf))return
lang(3);return
true;}function
authenticate($U,$D){if($D==""){$gf=$this->config->getDefaultPasswordHash();if($gf===null)return
lang(4,target_blank());else
return$gf==="";}return
true;}function
getPrivateKey($qc=false){return
get_private_key($qc);}function
getBruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
getServerName($L,$wk=true,$ae=null){if($L==""){if(!$wk)return"";$L=Connection::exists()?Connection::get()->getDefaultServerName():"";if($L=="")return$ae!==null?$ae:lang(5);$ll=null;}else$ll=$this->config->getServer($L);return$ll?$ll->getName():preg_replace('~^https?://~',"",$L);}abstract
function
getDatabase();function
getDatabases($xe=true){$g=$this->filterListWithWildcards(get_databases($xe),$this->config->getHiddenDatabases(),false,Driver::get()->getSystemDatabases());if(DB!=""&&!in_array(DB,$g))array_unshift($g,DB);return$g;}function
getSchemas($di=false){$lf=$this->config->getHiddenSchemas();if($di&&!in_array("__system",$lf))$lf[]="__system";$Tk=$this->filterListWithWildcards(schemas(),$lf,false,Driver::get()->getSystemSchemas());if(isset($_GET["ns"])&&$_GET["ns"]!=""&&!in_array($_GET["ns"],$Tk))array_unshift($Tk,$_GET["ns"]);return$Tk;}function
getCollations(array$og=[]){$Rn=$this->config->getVisibleCollations();$oe=$Rn?array_merge($Rn,$og):[];return$this->filterListWithWildcards(collations(),$oe,true);}private
function
filterListWithWildcards(array$Y,array$oe,$qg,array$hm=[]){if(!$Y||!$oe)return$Y;$t=array_search("__system",$oe);if($t!==false){unset($oe[$t]);$oe=array_merge($oe,$hm);}array_walk($oe,function(&$X){$X=str_replace('\\*',".*",preg_quote($X,"~"));});$wj='~^('.implode("|",$oe).')$~';return$this->filterListWithPattern($Y,$wj,$qg);}private
function
filterListWithPattern(array$Y,$wj,$qg){$G=[];foreach($Y
as$v=>$X){if(is_array($X)){if($Xl=$this->filterListWithPattern($X,$wj,$qg))$G[$v]=$Xl;}elseif(($qg&&preg_match($wj,$X))||(!$qg&&!preg_match($wj,$X)))$G[$v]=$X;}return$G;}abstract
function
getQueryTimeout();function
sendHeaders(){}function
updateCspHeader(array&$uc){}function
printFavicons(){$Nb=validate_color_variant($this->config->getColorVariant());echo"<link rel='icon' type='image/x-icon' href='",link_files("favicon-$Nb.ico",[]),"' sizes='32x32'>\n","<link rel='icon' type='image/svg+xml' href='",link_files("favicon-$Nb.svg",[]),"'>\n","<link rel='apple-touch-icon' href='",link_files("apple-touch-icon-$Nb.png",[]),"'>\n";}abstract
function
printToHead();function
getCssUrls(){$xn=$this->config->getCssUrls();foreach(["adminneo.css","adminneo-light.css","adminneo-dark.css"]as$n){if(file_exists($n))$xn[]="$n?v=".filemtime($n);}return$xn;}function
isLightModeForced(){return$this->isColorSchemeForced(false);}function
isDarkModeForced(){return$this->isColorSchemeForced(true);}private
function
isColorSchemeForced($zc){$Ih=$zc?Settings::$ColorSchemeDark:Settings::$ColorSchemeLight;$Jh=$zc?Settings::$ColorSchemeLight:Settings::$ColorSchemeDark;$ke=file_exists("adminneo-$Ih.css");$le=file_exists("adminneo-$Jh.css");if($ke&&!$le)return
true;return$this->settings->getColorScheme()==$Ih&&!($ke
xor$le);}function
getJsUrls(){$xn=$this->config->getJsUrls();$n="adminneo.js";if(file_exists($n))$xn[]="$n?v=".filemtime($n);return$xn;}abstract
function
printLoginForm();function
getLoginFormRow($ee,$zg,$k){if($zg)return"<tr><th>$zg</th><td>$k</td></tr>\n";else
return"$k\n";}function
printLogout(){echo"<div class='logout'>","<form action='' method='post'>\n","<span title='",lang(6),"'>",h($_GET["username"]),"</span>","<input type='submit' class='button' name='logout' value='",lang(7),"' id='logout'>",input_token(),"</form>","</div>\n";}function
getTableName(array$lm){return
h($lm["Name"]);}abstract
function
getFieldName(array$k,$Ii=0);function
formatComment($Vb){return
h($Vb);}abstract
function
printTableMenu(array$lm,$Qf);function
getForeignKeys($P){return
foreign_keys($P);}function
getBackwardKeys($P,$jm){if(!$this->settings->isRelationLinks())return[];$J=backward_keys($P);$tg=[];foreach($J
as$I){$r=$I["table_schema"].".".$I["table_name"];$tg[$r]["schema"]=$I["table_schema"];$tg[$r]["table"]=$I["table_name"];$tg[$r]["constraints"][$I["constraint_name"]][$I["column_name"]]=$I["referenced_column_name"];}foreach($tg
as$r=>$v){$A=$this->admin->getTableName(table_status1($v["table"],true));if($A!=""){$Vk=preg_quote($jm);$fl="(:|\\s*-)?\\s+";$tg[$r]["name"]=(preg_match("(^$Vk$fl(.+)|^(.+?)$fl$Vk\$)iu",$A,$z)?$z[2].$z[3]:$A);}else
unset($tg[$r]);}return$tg;}function
printBackwardKeys(array$bb,array$I){foreach($bb
as$v){foreach($v["constraints"]as$ic){$th=preg_replace('~&ns=[^&]+&~',"&ns=".urldecode($v["schema"])."&",ME);$y=$th.'select='.urlencode($v["table"]);$q=0;foreach($ic
as$b=>$W){if(!isset($I[$W]))continue
2;$y
.=where_link($q++,$b,$I[$W]);}$A=preg_replace('(^'.preg_quote($_GET["select"]).(substr($_GET["select"],-1)=="s"?"?":"").'_)',"_",$v["name"]);$S=implode(", ",array_keys($ic));echo"<a href='".h($y)."' title='".h($S)."'>".h($A)."</a>";$y=$th.'edit='.urlencode($v["table"]);foreach($ic
as$b=>$W)$y
.="&preset".urlencode("[".bracket_escape($b)."]")."=".urlencode($I[$W]);echo"<a href='".h($y)."' title='".lang(8)."'>",icon_solo("add"),"</a> ";}}}abstract
function
formatSelectQuery($F,$Ql,$Zd=false);abstract
function
formatMessageQuery($F,$Lm,$Zd=false);abstract
function
formatSqlCommandQuery($F);function
printAfterSqlCommand(){}abstract
function
getTableDescriptionFieldName($P);abstract
function
fillForeignDescriptions(array$J,array$_e);function
getFieldValueLink($W,$k){if(is_mail($W))return"mailto:$W";if(is_web_url($W))return$W;return
null;}abstract
function
formatSelectionValue($W,$y,$k,$Vi);abstract
function
formatFieldValue($X,array$k);abstract
function
printTableStructure(array$l);abstract
function
printTablePartitions(array$mj);abstract
function
printRelatedTables(array$R);abstract
function
printTableIndexes(array$u,array$lm);abstract
function
printSelectionColumns(array$K,array$c);abstract
function
printSelectionSearch(array$Z,array$c,array$u);abstract
function
printSelectionOrder(array$Ii,array$c,array$u);abstract
function
printSelectionLimit($x);abstract
function
printSelectionLength($Fm);abstract
function
printSelectionAction(array$u);function
isDataEditAllowed(){return!information_schema(DB);}abstract
function
processSelectionColumns(array$c,array$u);abstract
function
processSelectionSearch(array$l,array$u);abstract
function
processSelectionOrder(array$l,array$u);function
processSelectionLimit(){if(!isset($_GET["limit"]))return$this->settings->getRecordsPerPage();return$_GET["limit"]!=""?(int)$_GET["limit"]:0;}abstract
function
processSelectionLength();abstract
function
getFieldFunctions(array$k);abstract
function
getFieldInput($P,array$k,$Sa,$X,$p);function
getFieldInputHint($P,array$k,$X){return
support("comment")?$this->admin->formatComment($k["comment"]):"";}abstract
function
processFieldInput(array$k,$X,$p="");function
detectJson($fe,&$X,$Nj=null){if(is_array($X)){$ve=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($this->config->isJsonValuesAutoFormat()?JSON_PRETTY_PRINT:0);$X=json_encode($X,$ve);return
true;}$ve=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($Nj?JSON_PRETTY_PRINT:0);if(preg_match('~^jsonb?$~',$fe)){if($X!=null&&$Nj!==null&&$this->config->isJsonValuesAutoFormat())$X=json_encode(json_decode($X),$ve);return
true;}if(!$this->config->isJsonValuesDetection())return
false;if(is_string($X)&&$X!=""&&preg_match('~varchar|text|character varying|String|keyword~',$fe)&&($X[0]=="{"||$X[0]=="[")&&($mg=json_decode($X))){if($Nj!==null&&$this->config->isJsonValuesAutoFormat())$X=json_encode($mg,$ve);return
true;}return
false;}function
getServerVariables(){return
show_variables();}function
getStatusVariables(){return
show_status();}abstract
function
getDumpOutputs();abstract
function
getDumpFormats();abstract
function
sendDumpHeaders($wf,$Mh=false);function
dumpDatabase($Bc){}abstract
function
dumpTable($P,$Wl,$On=0);abstract
function
dumpData($P,$Wl,$F);abstract
function
getImportFilePath();abstract
function
printDatabaseMenu();abstract
function
printNavigation($Gh);abstract
function
printDatabaseSwitcher($Gh);function
printTablesFilter(){echo"<div class='tables-filter jsonly'>"."<input id='tables-filter' type='search' class='input' autocomplete='off' placeholder='".lang(9)."'>".script("initTablesFilter(".json_encode($this->admin->getDatabase(),JSON_HEX_TAG).");")."</div>\n";}abstract
function
printTableList(array$R);function
getSettingsRows($We){$M=[];if($We==1){$B=get_language_options();if($B)$M["lang"]="<tr><th id='label-language'>".lang(10)."</th>"."<td>".html_select("lang",get_language_options(),Locale::get()->getLanguage(),"","label-language")."</td></tr>\n";$Qm=get_theme_titles($this->config->getColorVariant());if(count($Qm)>1){list($Im)=validate_theme($this->config->getTheme(),$this->config->getColorVariant());$B=[""=>lang(11)." ($Qm[$Im])"]+$Qm;$M["theme"]="<tr><th id='label-theme'>".lang(12)."</th>"."<td>".html_select("theme",$B,($ta=$this->settings->getParameter("theme"))!==null?$ta:"","","label-theme")."</td></tr>\n";}$B=[""=>lang(13),Settings::$ColorSchemeLight=>lang(14),Settings::$ColorSchemeDark=>lang(15)];$M["colorScheme"]="<tr><th>".lang(16)."</th>"."<td>".html_radios("colorScheme",$B,($ta=$this->settings->getParameter("colorScheme"))!==null?$ta:"")."</td></tr>\n";}elseif($We==2){$B=[""=>lang(11),true=>lang(17),false=>lang(18),];$i=$B[$this->config->isRelationLinks()];$B[""].=" ($i)";$M["relationLinks"]="<tr><th>".lang(19)."</th>"."<td>".html_radios("relationLinks",$B,($ta=$this->settings->getParameter("relationLinks"))!==null?$ta:"")."<span class='input-hint'>".lang(20)."</span>"."</td></tr>\n";$i=$this->config->getRecordsPerPage();$B=[""=>lang(11)." ($i)","20","30","50","70","100",];$M["recordsPerPage"]="<tr><th id='label-records'>".lang(21)."</th>"."<td>".html_select("recordsPerPage",$B,($ta=$this->settings->getParameter("recordsPerPage"))!==null?$ta:"","","label-records")."<span class='input-hint'>".lang(22)."</span>"."</td></tr>\n";$i=($ta=$this->config->getEnumAsSelectThreshold())!==null?$ta:lang(23);$B=[""=>lang(11)." ($i)",-1=>lang(23),0=>lang(24),3=>lang(25,3),5=>lang(25,5),10=>lang(25,10),20=>lang(25,20),];$M["enumAsSelectThreshold"]="<tr><th id='label-enum'>".lang(26)."</th>"."<td>".html_select("enumAsSelectThreshold",$B,($ta=$this->settings->getParameter("enumAsSelectThreshold"))!==null?$ta:"","","label-enum",true)."<span class='input-hint'>".lang(27)."</span>"."</td></tr>\n";}return$M;}abstract
function
getForeignColumnInfo(array$_e,$b);}class
Pluginer{private
static$InternalMethods=["inject"=>true,"getConfig"=>true,];private
static$AppendMethods=["getErrors"=>true,"getFieldFunctions"=>true,"getDumpOutputs"=>true,"getDumpFormats"=>true,"getSettingsRows"=>true,];private$plugins;private$hooks=[];function
__construct(Origin$Ba,array$Cj){$this->plugins=$Cj;foreach(get_class_methods('\AdminNeo\Origin')as$Dh){$this->hooks[$Dh]=[];if(!(isset(self::$InternalMethods[$Dh])?self::$InternalMethods[$Dh]:false)){foreach($Cj
as$Bj){if(method_exists($Bj,$Dh))$this->hooks[$Dh][]=$Bj;}}if(isset(self::$AppendMethods[$Dh])?self::$AppendMethods[$Dh]:false)array_unshift($this->hooks[$Dh],$Ba);else$this->hooks[$Dh][]=$Ba;}}function
getPlugins(){return$this->plugins;}function
__call($A,array$fj){$Na=isset(self::$AppendMethods[$A])?self::$AppendMethods[$A]:false;$G=$Na?[]:null;assert(isset($this->hooks[$A]),"Calling unknown plugin method: $A");foreach($this->hooks[$A]as$Bj){$X=call_user_func_array([$Bj,$A],$fj);if($X!==null){if($Na)$G+=$X;else
return$X;}}return$G;}function
updateCspHeader(array&$uc){$this->__call(__FUNCTION__,[&$uc]);}function
detectJson($fe,&$X,$Nj=null){return$this->__call(__FUNCTION__,[$fe,&$X,$Nj]);}}class
Admin
extends
Origin{function
getOperators(){return
Driver::get()->getOperators();}function
getServiceTitle(){return"<a href='".h(HOME_URL)."'><svg role='img' class='logo' width='133' height='28'><desc>AdminNeo</desc>"."<use href='".link_files("logo.svg",[])."#logo'/></svg></a>";}function
getDatabase(){return
DB;}function
getQueryTimeout(){return
2;}function
printToHead(){echo"<link rel='stylesheet' href='",link_files("jush.css",[]),"'>";if(!$this->admin->isLightModeForced())echo"<link rel='stylesheet' ".(!$this->admin->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("jush-dark.css",[]),"'>\n";echo
script_src(link_files("jush.js",[]),true);}function
printLoginForm(){$hd=Drivers::getList();$ml=$this->config->getServerPairs($hd);$L=SERVER?:$this->config->getDefaultServer();echo"<table class='box box-light'>\n";if($ml)echo$this->admin->getLoginFormRow('server',lang(5),"<select name='auth[server]'>".optionlist($ml,$L,true)."</select>");else{$fd=DRIVER?:$this->config->getDefaultDriver($hd);if(count($hd)>1)echo$this->admin->getLoginFormRow('driver',lang(28),html_select("auth[driver]",$hd,$fd).script("initLoginDriver(qsl('select'));",""));else
echo$this->admin->getLoginFormRow('driver','',input_hidden("auth[driver]",$fd));echo$this->admin->getLoginFormRow('server',lang(5),"<input class='input' name='auth[server]' value='".h($L)."' title='".lang(29)."' placeholder='localhost' autocapitalize='off'>");}echo$this->admin->getLoginFormRow('username',lang(6),'<input class="input" name="auth[username]" id="username" value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'),$this->admin->getLoginFormRow('password',lang(30),'<input type="password" class="input" name="auth[password]" autocomplete="current-password">');if(!$ml){$Bc=isset($_GET["db"])?$_GET["db"]:$this->config->getDefaultDatabase();echo$this->admin->getLoginFormRow('db',lang(31),'<input class="input" name="auth[db]" value="'.h($Bc).'" autocapitalize="off">');}echo"</table>\n","<p>","<input type='submit' class='button default' value='".lang(32)."'>",checkbox("auth[permanent]",1,$_COOKIE["neo_permanent"],lang(33)),"</p>\n";}function
getFieldName(array$k,$Ii=0){$T=$k["full_type"].($k["null"]?" NULL":"");$Vb=$k["comment"];$fl=$T&&$Vb!=""?": ":"";return'<span title="'.h($T.$fl.$Vb).'">'.h($k["field"]).'</span>';}function
printTableMenu(array$lm,$Qf){echo'<p class="links top-tabs">';$Tg=[];$bl=($this->settings->isSelectionPreferred()&&!$this->settings->isNavigationReversed())||(!$this->settings->isSelectionPreferred()&&$this->settings->isNavigationReversed());if($bl)$Tg["select"]=[lang(34),"data"];if(support("table")||support("indexes"))$Tg["table"]=[lang(35),"structure"];if(!$bl)$Tg["select"]=[lang(34),"data"];$P=$lm["Name"];$hg=false;if(support("table")){$hg=is_view($lm);if(!$hg){if($P!="")$Tg["create"]=[lang(36),"edit"];}elseif(support("view"))$Tg["view"]=[lang(37),"edit"];}if($Qf!==null)$Tg["edit"]=[lang(8),"item-add"];$fj=$Qf?"&".http_build_query($Qf):"";foreach($Tg
as$v=>$W)echo" <a href='",h(ME),"$v=",urlencode($P),($v=="edit"?$fj:""),"'",bold(isset($_GET[$v])),">",icon($W[1]),"$W[0]</a>";echo
doc_link([DIALECT=>Driver::get()->tableHelp($P,$hg)],icon("help").lang(38)),"\n";}function
formatSelectQuery($F,$Ql,$Zd=false){$cm=support("sql");$Vn=!$Zd?Driver::get()->warnings():null;if($cm)$F
.=";";$fm=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$H="<pre><code class='jush-$fm'>".h(str_replace("\n"," ",$F))."</code></pre>\n";$H
.="<p class='links'>";if($cm)$H
.="<a href='".h(ME)."sql=".urlencode($F)."'>".icon("edit").lang(39)."</a>";if($Vn)$H
.="<a href='#warnings' class='toggle'>".lang(40).icon_chevron_down()."</a>";$H
.=" <span class='time'>(".format_time($Ql).")</span>";$H
.="</p>\n";if($Vn){$H
.=script("initToggles(qsl('p'));");$H
.="<div id='warnings' class='warnings hidden'>\n$Vn\n</div>\n";}return$H;}function
formatMessageQuery($F,$Lm,$Zd=false){restart_session();$nf=&get_session("queries");if(!isset($nf[$_GET["db"]]))$nf[$_GET["db"]]=[];if(strlen($F)>1e6)$F=preg_replace('~[\x80-\xFF]+$~','',substr($F,0,1e6))."\n…";$nf[$_GET["db"]][]=[$F,time(),$Lm];$cm=support("sql");$Vn=!$Zd?Driver::get()->warnings():null;$Jl="sql-".count($nf[$_GET["db"]]);$Wn="warnings-".count($nf[$_GET["db"]]);$H=" ";if($Vn)$H
.="<a href='#$Wn' class='toggle'>".lang(40).icon_chevron_down()."</a>, ";$bk=support("sql")?lang(41):lang(42);$H
.="<a href='#$Jl' class='toggle'>$bk".icon_chevron_down()."</a>";$H
.=" <span class='time'>".@date("H:i:s")."</span>\n";if($Vn)$H
.="<div id='$Wn' class='warnings hidden'>\n$Vn</div>\n";$H
.="<div id='$Jl' class='hidden'>\n";$fm=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$H
.="<pre><code class='jush-$fm'>".truncate_utf8($F,1000)."</code></pre>\n";$H
.="<p class='links'>";if($cm)$H
.="<a href='".h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($nf[$_GET["db"]])-1))."'>".icon("edit").lang(39)."</a>";if($Lm)$H
.=" <span class='time'>($Lm)</span>";$H
.="</p>\n";$H
.="</div>\n";return$H;}function
formatSqlCommandQuery($F){if(preg_match('~^DELIMITER\s~i',$F))return"";return
truncate_utf8($F,1000);}function
getTableDescriptionFieldName($P){return"";}function
fillForeignDescriptions(array$J,array$_e){return$J;}function
formatSelectionValue($W,$y,$k,$Vi){if($W===null)$Em="<i>NULL</i>";elseif(!$k)$Em=$W;elseif(preg_match("~char|binary|boolean~",$k["type"])&&!preg_match("~var~",$k["type"]))$Em="<code>$W</code>";elseif(is_blob($k)&&!is_utf8($W))$Em="<i>".lang(43,strlen($Vi))."</i>";elseif($this->admin->detectJson($k["full_type"],$Vi))$Em="<code class='jush-json'>$W</code>";else$Em=$W;if($y)$Em="<a href='".h($y)."'".(is_web_url($y)?target_blank():"").">$Em</a>";return$Em;}function
formatFieldValue($X,array$k){return$X;}function
printTableStructure(array$l){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th>",lang(44),"</th>","<td>",lang(45),"</td>","<td>",lang(46),"</td>";if(support("comment"))echo"<td>",lang(47),"</td>";echo"</tr></thead>\n";$Cn=Driver::get()->getUserTypes();foreach($l
as$k){echo"<tr>","<th>",h($k["field"]),"</th>","<td>";$T=h($k["full_type"]);if(in_array($T,$Cn))echo"<a href='".h(ME.'type='.urlencode($T))."'>$T</a>";else
echo$T;if($k["null"])echo" <i>NULL</i>";if($k["auto_increment"])echo" <i>".lang(48)."</i>";$i=h($k["default"]);if(isset($k["default"]))echo" <span title='".lang(49)."'>[<b>",$k["generated"]?"<code class='jush-".DIALECT."'>$i</code>":$i,"</b>]</span>";echo"</td>","<td>",h($k["collation"]),"</td>";if(support("comment"))echo"<td>",$this->admin->formatComment($k["comment"]),"</td>";echo"\n";}echo"</table>\n","</div>\n";}function
printTablePartitions(array$mj){$wl=isset($mj["partition_names"]);echo"<p>","<code class='jush-".DIALECT."'>BY {$mj["partition_by"]} ({$mj["partition"]})</code>";if(!$wl&&isset($mj["partitions"]))echo" ".lang(50).": ".h($mj["partitions"]);echo"</p>";if($wl){echo"<table>\n","<thead><tr><th>".lang(51)."</th><td>".lang(52)."</td></tr></thead>\n";foreach($mj["partition_names"]as$v=>$A){echo"<tr><th>";if(DIALECT=="pgsql")echo"<a href='",h(ME."table=".urlencode($A)),"'>";echo
h($A);if(DIALECT=="pgsql")echo"</a>";echo"</th><td>".h($mj["partition_values"][$v])."\n";}echo"</table>\n";}}function
printRelatedTables(array$R){echo"<ul class='links'>\n";foreach($R
as$I){$y=preg_replace('~ns=[^&]*~',"ns=".urlencode($I["ns"]),ME);echo"<li><a href='",h($y."table=".urlencode($I["table"])),"'>",icon("structure");if($I["ns"]!=$_GET["ns"])echo"<b>".h($I["ns"])."</b>.";echo
h($I["table"]),"</a>";}echo"</ul>\n";}function
printTableIndexes(array$u,array$lm){$Gc=first(Driver::get()->getIndexAlgorithms($lm));$jj=false;foreach($u
as$t){if(isset($t["partial"])?$t["partial"]:false){$jj=true;break;}}echo"<table>\n","<thead><tr>","<th>",lang(45),"</th>","<td>",lang(53)," (",lang(54),")</td>";if($jj)echo"<td>",lang(55),"</td>";echo"</tr></thead>\n";foreach($u
as$A=>$t){ksort($t["columns"]);$Rj=[];foreach($t["columns"]as$v=>$W)$Rj[]="<i>".h($W)."</i>".($t["lengths"][$v]?"(".h($t["lengths"][$v]).")":"").($t["descs"][$v]?" DESC":"");echo"<tr title='",h($A),"'>","<th>",h($t["type"]);if(isset($t['algorithm'])&&$t['algorithm']!=$Gc)echo" (",h($t['algorithm']),")";echo"</th>","<td>",implode(", ",$Rj),"</td>";if($jj){echo"<td>";if($t['partial'])echo"<code class='jush-",DIALECT,"'>WHERE ",h($t['partial']),"</code>";echo"</td>";}echo"</tr>\n";}echo"</table>\n";}function
printSelectionColumns(array$K,array$c){print_fieldset_start("select",lang(56),"columns",(bool)$K,true);$K[""]=[];$q=0;foreach($K
as$v=>$W){$W=isset($_GET["columns"][$v])?$_GET["columns"][$v]:[];$b=select_input("name='columns[$q][col]'",$c,isset($W["col"])?$W["col"]:null,$v!==""?"selectFieldChange":"selectAddRow");echo"<div ",($v!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly");if(Driver::get()->getFunctions()||Driver::get()->getGrouping())echo
html_select("columns[$q][fun]",[-1=>""]+array_filter([lang(57)=>Driver::get()->getFunctions(),lang(58)=>Driver::get()->getGrouping()]),isset($W["fun"])?$W["fun"]:null),help_script_command("value && value.replace(/ |\$/, '(') + ')'",true),script("qsl('select').onchange = (event) => { ".($v!==""?"":" qsl('select, input:not(.remove)', event.target.parentNode).onchange();")." };",""),"($b)";else
echo$b;echo" <button class='button light remove jsonly' title='",lang(59),"'>",icon_solo("remove"),"</button>",script("qsl('#fieldset-select .remove').onclick = selectRemoveRow;",""),"</div>\n";$q++;}print_fieldset_end("select",true);}function
printSelectionSearch(array$Z,array$c,array$u){print_fieldset_start("search",lang(60),"search",(bool)$Z);foreach($u
as$q=>$t){if($t["type"]=="FULLTEXT"){echo"<div>(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$t["columns"]))."</i>) AGAINST","<input type='text' class='input' name='fulltext[$q]' value='".h(isset($_GET["fulltext"][$q])?$_GET["fulltext"][$q]:null)."'>",script("qsl('input').oninput = selectFieldChange;","");if(DIALECT=='sql')echo
checkbox("boolean[$q]",1,isset($_GET["boolean"][$q]),"BOOL");echo"</div>\n";}}$ub="this.parentNode.firstChild.onchange();";foreach(array_merge((array)$_GET["where"],[[]])as$q=>$W){if(!$W||("$W[col]$W[val]"!=""&&in_array($W["op"],$this->getOperators())))echo"<div>",select_input(" name='where[$q][col]'",$c,$W["col"],($W?"selectFieldChange":"selectAddRow"),"(".lang(61).")"),html_select("where[$q][op]",$this->getOperators(),$W["op"],$ub),"<input type='text' class='input' name='where[$q][val]' value='".h($W["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $ub }, onkeydown: selectSearchKeydown});","")," <button class='button light remove jsonly' title='".lang(59)."'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-search .remove").onclick = selectRemoveRow;',""),"</div>\n";}print_fieldset_end("search");}function
printSelectionOrder(array$Ii,array$c,array$u){print_fieldset_start("sort",lang(62),"sort",(bool)$Ii,true);$_GET["order"][""]="";$q=0;foreach((array)$_GET["order"]as$v=>$W){if($v!=""&&$W=="")continue;echo"<div ",($v!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly"),select_input("name='order[$q]'",$c,$W,$v!==""?"selectFieldChange":"selectAddRow")," ",checkbox("desc[$q]",1,isset($_GET["desc"][$v]),lang(63))," <button class='button light remove jsonly' title='",lang(59),"'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-sort .remove").onclick = selectRemoveRow;',""),"</div>\n";$q++;}print_fieldset_end("sort",true);}function
printSelectionLimit($x){echo"<fieldset><legend>".lang(64)."</legend><div class='fieldset-content'>","<input type='number' name='limit' class='input size' value='$x'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
printSelectionLength($Fm){if($Fm!==null)echo"<fieldset><legend>".lang(65)."</legend><div class='fieldset-content'>","<input type='number' name='text_length' class='input size' value='".h($Fm)."'>","</div></fieldset>\n";}function
printSelectionAction(array$u){echo"<fieldset><legend>".lang(66)."</legend><div class='fieldset-content'>","<input type='submit' class='button' value='".lang(56)."'>"," <span id='noindex' title='".lang(67)."'></span>","<script".nonce().">\n";$c=new
stdClass();foreach($u
as$t){$wc=reset($t["columns"]);if($t["type"]!="FULLTEXT"&&$wc)$c->$wc=null;}echo"const indexColumns = ".json_encode($c,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG).";\n","selectFieldChange.call(gid('form')['select']);\n","</script>\n","</div></fieldset>\n";}function
processSelectionColumns(array$c,array$u){$K=[];$Ue=[];foreach((array)$_GET["columns"]as$v=>$W){if($W["fun"]=="count"||($W["col"]!=""&&(!$W["fun"]||in_array($W["fun"],Driver::get()->getFunctions())||in_array($W["fun"],Driver::get()->getGrouping())))){$K[$v]=apply_sql_function($W["fun"],($W["col"]!=""?idf_escape($W["col"]):"*"));if(!in_array($W["fun"],Driver::get()->getGrouping()))$Ue[]=$K[$v];}}return[$K,$Ue];}function
processSelectionSearch(array$l,array$u){$H=[];foreach($u
as$q=>$t){if($t["type"]=="FULLTEXT"&&isset($_GET["fulltext"])&&$_GET["fulltext"][$q]!="")$H[]="MATCH (".implode(", ",array_map('AdminNeo\idf_escape',$t["columns"])).") AGAINST (".q($_GET["fulltext"][$q]).(isset($_GET["boolean"][$q])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$Z){$Jb=$Z["col"];$zi=$Z["op"];$W=$Z["val"];if("$Jb$W"!=""&&in_array($zi,$this->getOperators())){$bc=[];foreach(($Jb!=""?[$Jb=>$l[$Jb]]:$l)as$A=>$k){$Lj="";$ac=" $zi";$oi=DIALECT=="pgsql"&&$zi=="="&&$k["type"]=="oid";if($oi)$ac
.=" ".$this->admin->processFieldInput($k,$W)."::regproc";elseif(preg_match('~IN$~',$zi)){$_f=process_length($W);$ac
.=" ".($_f!=""?$_f:"(NULL)");}elseif($zi=="SQL")$ac=" $W";elseif(preg_match('~^(I?LIKE) %%$~',$zi,$z))$ac=" $z[1] ".$this->admin->processFieldInput($k,"%$W%");elseif($zi=="FIND_IN_SET"){$Lj="$zi(".q($W).", ";$ac=")";}elseif(!preg_match('~NULL$~',$zi))$ac
.=" ".$this->admin->processFieldInput($k,$W);if($Jb!=""||(isset($k["privileges"]["where"])&&(preg_match('~^[-\d.'.(preg_match('~IN$~',$zi)?',':'').']+$~',$W)||!preg_match('~'.number_type().'|bit~',$k["type"]))&&(!preg_match("~[\x80-\xFF]~",$W)||preg_match('~char|text|enum|set~',$k["type"]))&&(!preg_match('~date|timestamp~',$k["type"])||preg_match('~^\d+-\d+-\d+~',$W))&&(!preg_match('~^elastic~',DRIVER)||$k["type"]!="boolean"||preg_match('~true|false~',$W))&&(!preg_match('~^elastic~',DRIVER)||strpos($zi,"regexp")===false||preg_match('~text|keyword~',$k["type"])))){if($oi)$bc[]=$Lj.idf_escape($A).$ac;else$bc[]=$Lj.Driver::get()->convertSearch(idf_escape($A),$Z,$k).$ac;}}if(count($bc)==1)$H[]=$bc[0];elseif($bc)$H[]="(".implode(" OR ",$bc).")";else$H[]="1 = 0";}}return$H;}function
processSelectionOrder(array$l,array$u){$H=[];foreach((array)$_GET["order"]as$v=>$W){if($W!="")$H[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$W)?$W:idf_escape($W)).(isset($_GET["desc"][$v])?" DESC".(DIALECT=="pgsql"&&(isset($l[$W]["null"])?$l[$W]["null"]:null)?" NULLS LAST":""):"");}return$H;}function
processSelectionLength(){return
isset($_GET["text_length"])?$_GET["text_length"]:"100";}function
getFieldFunctions(array$k){$H=($k["null"]?"NULL/":"");$un=isset($_GET["select"])||where($_GET);foreach([Driver::get()->getInsertFunctions(),Driver::get()->getEditFunctions()]as$v=>$Me){if(!$v||(!isset($_GET["call"])&&$un)){foreach($Me
as$wj=>$W){if(!$wj||preg_match("~$wj~",$k["type"]))$H
.="/$W";}}if($v&&$Me&&!preg_match('~enum|set|bool~',$k["type"])&&!is_blob($k))$H
.="/SQL";}if($k["auto_increment"]&&!$un)$H=lang(48);return
explode("/",$H);}function
getFieldInput($P,array$k,$Sa,$X,$p){return"";}function
processFieldInput(array$k,$X,$p=""){if($p=="SQL")return$X;if(isset($k["full_type"]))$this->admin->detectJson($k["full_type"],$X,false);$A=$k["field"];$H=q($X);if(preg_match('~^(now|getdate|uuid)$~',$p))$H="$p()";elseif(preg_match('~^current_(date|timestamp)$~',$p))$H=$p;elseif(preg_match('~^([+-]|\|\|)$~',$p))$H=idf_escape($A)." $p $H";elseif(preg_match('~^[+-] interval$~',$p))$H=idf_escape($A)." $p ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$X)&&DIALECT!="pgsql"?$X:$H);elseif(preg_match('~^(addtime|subtime|concat)$~',$p))$H="$p(".idf_escape($A).", $H)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$p))$H="$p($H)";elseif($k["type"]=="boolean"&&DIALECT=="elastic")$H=$H=="0"?"false":"true";return
unconvert_field($k,$H);}function
getDumpOutputs(){$aj=['file'=>lang(68),'text'=>lang(69),];if(function_exists('gzencode'))$aj['gz']='gzip';return$aj;}function
getDumpFormats(){return(support("dump")?['sql'=>'SQL']:[])+['csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV'];}function
sendDumpHeaders($wf,$Mh=false){$Zi=$_POST["output"];$Vd=(str_contains($_POST["format"],"sql")?"sql":($Mh?"tar":"csv"));if($Zi=="gz"){header("Content-Type: application/x-gzip");ob_start(function($O){return
gzencode($O);},1e6);}elseif($Vd=="tar")header("Content-Type: application/x-tar");elseif($Vd=="sql"||$Zi=="text")header("Content-Type: text/plain; charset=utf-8");else
header("Content-Type: text/csv; charset=utf-8");return$Vd;}function
dumpTable($P,$Wl,$On=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($Wl)dump_csv(array_keys(fields($P)));}else{if($On==2){$l=[];foreach(fields($P)as$A=>$k)$l[]=idf_escape($A)." $k[full_type]";$qc="CREATE TABLE ".table($P)." (".implode(", ",$l).")";}else$qc=create_sql($P,$_POST["auto_increment"],$Wl);set_utf8mb4($qc);if($Wl&&$qc){if($Wl=="DROP+CREATE"||$On==1)echo"DROP ".($On==2?"VIEW":"TABLE")." IF EXISTS ".table($P).";\n";if($On==1)$qc=remove_definer($qc);echo"$qc;\n\n";}}}function
dumpData($P,$Wl,$F){if($Wl){$mh=(DIALECT=="sqlite"?0:1048576);$l=[];$xf=false;if($_POST["format"]=="sql"){if($Wl=="TRUNCATE+INSERT")echo
truncate_sql($P).";\n";$l=fields($P);if(DIALECT=="mssql"){foreach($l
as$k){if($k["auto_increment"]){echo"SET IDENTITY_INSERT ".table($P)." ON;\n";$xf=true;break;}}}}$G=Connection::get()->query($F,1);if($G){$Of="";$lb="";$tg=[];$Oe=[];$Zl="";$pc=0;while($I=($P!=''?$G->fetchAssoc():$G->fetchRow())){if(!$tg){$Y=[];foreach($I
as$W){$k=$G->fetchField();if(!empty($l[$k->name]['generated'])){$Oe[$k->name]=true;continue;}$tg[]=$k->name;$v=idf_escape($k->name);$Y[]="$v = VALUES($v)";}$Zl=($Wl=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Y):"").";\n";}if($_POST["format"]!="sql"){if($Wl=="table"){dump_csv($tg);$Wl="INSERT";}dump_csv($I);}else{if(!$Of)$Of="INSERT INTO ".table($P)." (".implode(", ",array_map('AdminNeo\idf_escape',$tg)).") VALUES";foreach($I
as$v=>$W){if(isset($Oe[$v])){unset($I[$v]);continue;}$k=$l[$v];$I[$v]=($W===null?"NULL":($W===false?0:unconvert_field($k,preg_match(number_type(),$k["type"])&&!preg_match('~\[~',$k["full_type"])&&is_numeric($W)?$W:(!is_blob($k)||is_utf8($W)?q($W):Driver::get()->quoteBinary($W)))));}$Kk=($mh?"\n":" ")."(".implode(",\t",$I).")";if(!$lb)$lb=$Of.$Kk;elseif(DIALECT=="mssql"?$pc%1000!=0:strlen($lb)+4+strlen($Kk)+strlen($Zl)<$mh)$lb
.=",$Kk";else{echo$lb.$Zl;$lb=$Of.$Kk;}}$pc++;}if($lb)echo$lb.$Zl;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",Connection::get()->getError())."\n";if($xf)echo"SET IDENTITY_INSERT ".table($P)." OFF;\n";}}function
getImportFilePath(){return"adminneo.sql";}function
printDatabaseMenu(){echo"<p class='links top-links'>\n";$fi=isset($_GET["ns"])?$_GET["ns"]:null;if($fi==""&&support("database"))echo'<a href="',h(ME),'database=">',icon("edit"),lang(70),"</a>\n";if($fi!=""&&support("scheme"))echo"<a href='",h(ME),"scheme='>",icon("edit"),lang(71),"</a>\n";if($fi!=="")echo'<a href="',h(ME),'schema=">',icon("schema"),lang(72),"</a>\n";if(support("privileges"))echo"<a href='",h(ME),"privileges='>",icon("users"),lang(73),"</a>\n";echo"</p>\n";}function
printNavigation($Gh){if($Gh=="auth"){$Zi="";foreach((array)$_SESSION["pwds"]as$Kn=>$ql){foreach($ql
as$L=>$Dn){foreach($Dn
as$U=>$D){if($D!==null){$Ec=$_SESSION["db"][$Kn][$L][$U];foreach(($Ec?array_keys($Ec):[""])as$h){$nl=$this->admin->getServerName($L,false);$S=h(get_driver_name($Kn,$L)).($U!=""||$nl!=""?" - ":"").h($U).($U!=""&&$nl!=""?"@":"").h($nl).($h!=""?h(" - $h"):"");$Zi
.="<li><a href='".h(auth_url($Kn,$L,$U,$h))."' class='primary' title='$S'>$S</a></li>\n";}}}}}if($Zi)echo"<nav id='logins'><menu>\n$Zi</menu></nav>\n";}else{$this->admin->printDatabaseSwitcher($Gh);$xa=[];if(DB==""||!$Gh){if(support("sql")){$xa[]="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".icon("command").lang(41)."</a>";$xa[]="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".icon("import").lang(74)."</a>";}$xa[]="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".icon("export").lang(75)."</a>";}if(DB=="")$xa[]='<a href="'.h(ME).'database="'.bold($_GET["database"]==="").">".icon("database-add").lang(76)."</a>\n";if(DB!=""&&$_GET["ns"]===""&&!$Gh)$xa[]='<a href="'.h(ME).'scheme="'.bold($_GET["scheme"]==="").">".icon("database-add").lang(77)."</a>\n";if(DB!=""&&$_GET["ns"]!==""&&!$Gh)$xa[]='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".icon("table-add").lang(78)."</a>\n";if($xa)echo"<p class='links'>".implode("\n",$xa)."</p>";$R=[];if($_GET["ns"]!==""&&!$Gh&&DB!=""){Connection::get()->selectDatabase(DB);$R=table_status('',true);}if($_GET["ns"]!==""&&!$Gh&&DB!=""){if($R){$this->admin->printTablesFilter();$this->admin->printTableList($R);}else
echo"<div id='tables'><p>".lang(79)."</p></div>\n";}elseif($_GET["ns"]!==""&&DB!="")echo"<div id='tables'></div>\n";if(support("sql")||DIALECT=="elastic"||DIALECT=="mongo"){echo"<script".nonce().">\n";if(support("sql")&&$R){$Tg=[];foreach($R
as$P=>$T)$Tg[]=js_escape_re($P);$km=support("table")&&!$this->config->isSelectionPreferred()?"table":"select";echo"window.jushLinks = { ".DIALECT.": {\n",js_escape_key(ME.$km.'=$&'),': /\b(?<!\$)('.implode('|',$Tg).')(?!\$)\b/g';$Kl=["sql","check","event","procedure","trigger","view","type","table","processlist"];if(support('routine')&&array_intersect_key($_GET,array_flip($Kl))){foreach(routines()as$I)echo",\n",js_escape_key(ME.'function='.urlencode($I["SPECIFIC_NAME"]).'&name=$&'),': /\b'.js_escape_re($I["ROUTINE_NAME"]).'(?=["`\]]?\()/g';}echo"\n}};\n";foreach(["bac","bra","sqlite_quo","mssql_bra"]as$W)echo"jushLinks.$W = jushLinks.".DIALECT.";\n";}if(DIALECT!="elastic"&&DIALECT!="mongo"&&$this->getConfig()->isSqlAutocompletionEnabled()&&(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"]))){$vm=array_fill_keys(array_keys($R),[]);foreach(Driver::get()->getAllFields()as$P=>$l){foreach($l
as$k)$vm[$P][]=$k["field"];}echo"window.addEventListener('DOMContentLoaded', () => { autocompletion = jush.autocompleteSql('".idf_escape("")."', ".json_encode($vm,JSON_HEX_TAG)."); });\n";}echo"</script>\n";}echo
script("let autocompletion;\nwindow.addEventListener('DOMContentLoaded', () => { initSyntaxHighlighting('".js_escape(doc_version())."', '".js_escape(Connection::get()->getFlavor())."', autocompletion); });");}}function
printDatabaseSwitcher($Gh){$g=$this->admin->getDatabases();if(!$g&&DIALECT!="sqlite")return;echo"<div class='db-selector'><form action=''>";hidden_fields_get();echo"<div>";if($g)echo"<select id='database-select' name='db' title='",lang(31),"'>".optionlist([""=>"(".lang(80).")"]+$g,DB)."</select>".script("mixin(gid('database-select'), {onmousedown: dbMouseDown, onchange: dbChange});");else
echo"<input id='database-select' class='input' name='db' value='".h(DB)."' title='",lang(31),"' autocapitalize='off'>\n";echo"<input type='submit' value='".lang(81)."' class='button ".($g?"hidden":"")."'>\n","</div>";if(support("scheme")&&$Gh!="db"&&DB!=""&&Connection::get()->selectDatabase(DB)){echo"<div>","<select id='scheme-select' name='ns' title='",lang(82),"'>".optionlist([""=>"(".lang(83).")"]+$this->admin->getSchemas(),$_GET["ns"])."</select>".script("mixin(gid('scheme-select'), {onmousedown: dbMouseDown, onchange: dbChange});"),"</div>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}foreach(["import","sql","schema","dump","privileges"]as$W){if(isset($_GET[$W])){echo
input_hidden($W);break;}}echo"</form></div>\n";}function
printTableList(array$R){$od=$this->settings->isNavigationDual()||$this->settings->isNavigationHover();$vh=($od?"class='dual".($this->settings->isNavigationHover()?" hover":"")."'":($this->settings->isNavigationReversed()?"class='reversed'":""));echo"<nav id='tables'><div class='scroll-marker'></div><menu $vh>";foreach($R
as$P=>$N){$P="$P";$A=$this->admin->getTableName($N);if($A==""||(isset($N["Partition"])?$N["Partition"]:false))continue;echo"<li>";$ya=in_array($P,[$_GET["table"],$_GET["select"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"]]);$Gb="primary".(is_view($N)?" view":"");$dm=support("table")||support("indexes");$Yk=h(ME)."select=".urlencode($P);$mm=h(ME)."table=".urlencode($P);if($this->settings->isSelectionPreferred()){if($this->settings->isNavigationReversed()&&$dm)echo" <a href='$mm' title='",lang(35),"' class='secondary'>",icon("structure"),"</a>";echo"<a href='$Yk'",bold($ya,$Gb)," data-primary='true' title='$A'>$A</a>";if($od&&$dm)echo" <a href='$mm' title='",lang(35),"' class='secondary'>",icon_solo("structure"),"</a>";}else{if($this->settings->isNavigationReversed())echo" <a href='$Yk' title='",lang(34),"' class='secondary'>",icon("data"),"</a>";if($dm)echo"<a href='$mm'",bold($ya,$Gb)," data-primary='true' title='$A'>$A</a>";else
echo"<span data-primary='true'",bold($ya,$Gb),">$A</span>";if($od)echo" <a href='$Yk' title='",lang(34),"' class='secondary'>",icon_solo("data"),"</a>";}echo"</li>\n";}echo"</menu></nav>\n",script("initTablesList(".json_encode($this->admin->getDatabase(),JSON_HEX_TAG).");");}function
getSettingsRows($We){$M=parent::getSettingsRows($We);if($We==1){$B=[""=>lang(11),Config::$NavigationSimple=>lang(84),Config::$NavigationDual=>lang(85),Config::$NavigationHover=>lang(86),Config::$NavigationReversed=>lang(87)];$i=$B[$this->config->getNavigationMode()];$B[""].=" ($i)";$M["navigationMode"]="<tr><th>".lang(88)."</th>"."<td>".html_radios("navigationMode",$B,($ta=$this->settings->getParameter("navigationMode"))!==null?$ta:"")."<span class='input-hint'>".lang(89)."</span>"."</td></tr>\n";$B=[""=>lang(11),0=>lang(35),1=>lang(34),];$i=$B[$this->config->isSelectionPreferred()?1:0];$B[""].=" ($i)";$M["preferSelection"]="<tr><th id='label-links'>".lang(90)."</th>"."<td>".html_select("preferSelection",$B,($ta=$this->settings->getParameter("preferSelection"))!==null?$ta:"","","label-links",true)."<span class='input-hint'>".lang(91)."</span>"."</td></tr>\n";}return$M;}function
getForeignColumnInfo(array$_e,$b){return
null;}}class
TmpFile{private$handler;private$size;function
__construct(){$this->handler=tmpfile();}function
getSize(){return$this->size;}function
write($kc){if(!$this->handler)return;$this->size+=strlen($kc);fwrite($this->handler,$kc);}function
send(){if(!$this->handler)return;fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}function
print_select_result(Result$G,$e=null,array$Pi=[],$x=0){$Tg=[];$u=[];$c=[];$hb=[];$kn=[];$H=[];for($q=0;(!$x||$q<$x)&&($I=$G->fetchRow());$q++){if(!$q){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>";for($kg=0;$kg<count($I);$kg++){$k=$G->fetchField();if(!$k){echo"<th></th>";continue;}$A=$k->name;$Oi=isset($k->orgtable)?$k->orgtable:"";$Ni=isset($k->orgname)?$k->orgname:$A;if(isset($k->table))$H[$k->table]=$Oi;if($Pi&&DIALECT=="sql")$Tg[$kg]=($A=="table"?"table=":($A=="possible_keys"?"indexes=":null));elseif($Oi!=""){if(!isset($u[$Oi])){$u[$Oi]=[];foreach(indexes($Oi,$e)as$t){if($t["type"]=="PRIMARY"){$u[$Oi]=array_flip($t["columns"]);break;}}$c[$Oi]=$u[$Oi];}if(isset($c[$Oi][$Ni])){unset($c[$Oi][$Ni]);$u[$Oi][$Ni]=$kg;$Tg[$kg]=$Oi;}}if($k->charsetnr==63)$hb[$kg]=true;$kn[$kg]=$k->type;$S=trim(($Oi!=""?"$Oi.$Ni":($k->name!=$Ni?$Ni:""))." ".Driver::get()->getTypeName($k));echo"<th".($S!=""?" title='".h($S)."'":"").">".h($A).($Pi?doc_link(['sql'=>"explain-output.html#explain_".strtolower($A),'mariadb'=>"reference/sql-statements/administrative-sql-statements/analyze-and-explain-statements/explain#columns-in-explain-...-select",]):"");}echo"</thead>\n";}echo"<tr>";foreach($I
as$v=>$W){$y="";if(isset($Tg[$v])&&!$c[$Tg[$v]]){if($Pi&&DIALECT=="sql"){$P=$I[array_search("table=",$Tg)];$y=ME.$Tg[$v].urlencode($Pi[$P]!=""?$Pi[$P]:$P);}else{$y=ME."edit=".urlencode($Tg[$v]);foreach($u[$Tg[$v]]as$Jb=>$kg)$y
.="&where".urlencode("[".bracket_escape($Jb)."]")."=".urlencode($I[$kg]);}}$T=($hb[$v]?'blob':($kn[$v]==254?'char':''));$k=['full_type'=>$T,'type'=>$T,];$W=select_value($W,$y,$k,null);$Gb=$kn[$v]<=9||$kn[$v]==246?"class='number'":"";echo"<td $Gb>$W</td>";}}if($q)echo"</table>\n</div>";else
echo"<p class='message'>".lang(92);echo"\n";return$H;}function
referencable_primary($dl){$H=[];foreach(table_status('',true)as$om=>$P){if($om!=$dl&&fk_support($P)){foreach(fields($om)as$k){if($k["primary"]){if($H[$om]){unset($H[$om]);break;}$H[$om]=$k;}}}}return$H;}function
textarea($A,$X,$J=10,$Qb=80){echo"<textarea name='".h($A)."' rows='$J' cols='$Qb' class='sqlarea jush-".DIALECT."' spellcheck='false' wrap='off'>";if(is_array($X)){foreach($X
as$W)echo
h($W[0])."\n\n\n";}else
echo
h($X);echo"</textarea>";}function
select_input($Sa,$B,$X="",$xi="",$zj=""){if($B&&$X!=""&&!isset($B[$X]))$B=[$X=>$X]+$B;$zm=($B?"select":"input");return"<$zm $Sa".($B?"><option value=''>$zj".optionlist($B,$X,true)."</select>":" size='10' value='".h($X)."' placeholder='$zj'>").($xi?script("qsl('$zm').onchange = $xi;",""):"");}function
json_row($v,$W=null){static$qe=true;if($qe)echo"{";if($v!=""){echo($qe?"":",")."\n\t\"".addcslashes($v,"\r\n\t\"\\/").'": '.($W!==null?'"'.addcslashes($W,"\r\n\t\"\\/").'"':'null');$qe=false;}else{echo"\n}\n";$qe=true;}}function
edit_type($v,$k,$Mb,$Ae=[],$Yd=[]){$T=isset($k["type"])?$k["type"]:null;echo'<td><select name="',h($v),'[type]" class="type" aria-labelledby="label-type">';$gd=Driver::get()->getTypes();if($T&&!isset($gd[$T])&&!isset($Ae[$T])&&!in_array($T,$Yd))$Yd[]=$T;$Vl=Driver::get()->getStructuredTypes();if($Ae)$Vl[lang(93)]=$Ae;echo
optionlist(array_merge($Yd,$Vl),$T),'</select><td><input name="',h($v),'[length]" value="',h(isset($k["length"])?$k["length"]:null),'" size="3"',(!(isset($k["length"])?$k["length"]:null)&&preg_match('~var(char|binary)$~',$T)?" class='input required'":" class='input'"),' aria-labelledby="label-length"><td class="options">',($Mb?"<select name='".h($v)."[collation]'".option_types($T,'(char|text|enum|set)$').'><option value="">('.lang(94).')'.optionlist($Mb,isset($k["collation"])?$k["collation"]:null).'</select>':''),(Driver::get()->getUnsigned()?"<select name='".h($v)."[unsigned]'".option_types($T,'^$|'.number_type()).'><option>'.optionlist(Driver::get()->getUnsigned(),isset($k["unsigned"])?$k["unsigned"]:null).'</select>':''),(isset($k['on_update'])?"<select name='".h($v)."[on_update]'".option_types($T,'timestamp|datetime').'>'.optionlist([""=>"(".lang(95).")","CURRENT_TIMESTAMP"],(preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?"CURRENT_TIMESTAMP":$k["on_update"])).'</select>':''),($Ae?"<select name='".h($v)."[on_delete]'".option_types($T,'`')."><option value=''>(".lang(96).")".optionlist(Driver::get()->getOnActions(),isset($k["on_delete"])?$k["on_delete"]:null)."</select> ":" ");}function
option_types($T,$kn){return" data-types='".h($kn)."'".(preg_match("~$kn~",$T)?"":" class='hidden'");}function
process_length($w){$Ed=Driver::$EnumLengthPattern;return(preg_match("~^\\s*\\(?\\s*$Ed(?:\\s*,\\s*$Ed)*+\\s*\\)?\\s*\$~",$w)&&preg_match_all("~$Ed~",$w,$_)?"(".implode(",",$_[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$w)));}function
process_type($k,$Kb="COLLATE"){return" $k[type]".process_length($k["length"]).(preg_match(number_type(),$k["type"])&&in_array($k["unsigned"],Driver::get()->getUnsigned())?" $k[unsigned]":"").(preg_match('~char|text|enum|set~',$k["type"])&&$k["collation"]?" $Kb ".(DIALECT=="mssql"?$k["collation"]:q($k["collation"])):"");}function
process_field($k,$in){if($k["on_update"])$k["on_update"]=preg_replace('~current_timestamp(\(\))?~i',"CURRENT_TIMESTAMP",$k["on_update"]);return[idf_escape(trim($k["field"])),process_type($in),($k["null"]?" NULL":" NOT NULL"),default_value($k),(preg_match('~timestamp|datetime~',$k["type"])&&$k["on_update"]?" ON UPDATE ".$k["on_update"]:""),(support("comment")&&$k["comment"]!=""?" COMMENT ".q(normalize_newlines($k["comment"])):""),($k["auto_increment"]?auto_increment():null),];}function
normalize_newlines($X){return
str_replace("\r","",(string)$X);}function
default_value($k){if($k["default"]===null)return"";$i=normalize_newlines($k["default"]);$Ne=$k["generated"];if(in_array($Ne,Driver::get()->getGenerated())){if(DIALECT=="mssql")return" AS ($i)".($Ne=="VIRTUAL"?"":" $Ne");else
return" GENERATED ALWAYS AS ($i) $Ne";}if(stripos($i,"GENERATED ")===0)return" $i";if(preg_match('~char|binary|text|json|enum|set~',$k["type"])||preg_match('~^(?![a-z])~i',$i)){if(DIALECT=="sql"&&preg_match('~text|json~',$k["type"]))return" DEFAULT (".q($i).")";else
return" DEFAULT ".q($i);}else{$i=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$i);return" DEFAULT ".(DIALECT=="sqlite"?"($i)":$i);}}function
type_class($T){foreach(['char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',]as$Gb=>$wj){if(preg_match("~$Gb|$wj~",$T))return"class='$Gb'";}return"";}function
edit_fields(array$l,array$Mb,$T="TABLE",$Ae=[]){$l=array_values($l);$Yb=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$Wb=$Yb?"":"class='hidden'";echo"<thead><tr>\n";if(support("move_col"))echo"<th class='jsonly'></th>";if($T=="PROCEDURE")echo"<td></td>";echo"<th id='label-name'>",($T=="TABLE"?lang(97):lang(98)),"</th>\n","<td id='label-type'>",lang(45),"<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>",script("gid('enum-edit').onblur = onFieldLengthBlur;"),"</td>\n","<td id='label-length'>",lang(99),"</td>\n","<td>",lang(100),"</td>\n";if($T=="TABLE")echo"<td id='label-null'>NULL</td>\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='",lang(48),"'>AI</abbr>",doc_link(['sql'=>"example-auto-increment.html",'mariadb'=>"reference/data-types/auto_increment",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",]),"</td>\n","<td id='label-default'>",lang(49),"</td>\n",support("comment")?"<td id='label-comment' $Wb>".lang(47)."</td>\n":"";echo"<td>","<button name='add[",(support("move_col")?0:count($l)),"]' value='1' title='",lang(101),"' class='button light'>",icon_solo("add"),"</button>",(support("move_col")?"":script("qsl('button').onclick = onAddLastFieldRowClick;")),script("row_count = ".count($l).";"),"</td>\n","</tr></thead>\n";$Gb=support("move_col")?"class='sortable'":"";echo"<tbody $Gb>\n";foreach($l
as$q=>$k){$q++;$Qi=$k[($_POST?"orig":"field")];$Wc=(isset($_POST["add"][$q-1])||(isset($k["field"])&&!(isset($_POST["drop_col"][$q])?$_POST["drop_col"][$q]:null)))&&(support("drop_col")||$Qi=="");echo"<tr",($Wc?"":" hidden"),">\n";if(support("move_col"))echo"<th class='handle jsonly'>",icon_solo("handle"),"</td>";if($T=="PROCEDURE")echo"<td>",html_select("fields[$q][inout]",Driver::get()->getInOut(),$k["inout"]),"</td>\n";echo"<th>";if($Wc)echo"<input class='input' name='fields[$q][field]' value='",h($k["field"]),"' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name' ".(isset($_POST["add"][$q-1])?"autofocus":"").">";echo
input_hidden("fields[$q][orig]",$Qi);edit_type("fields[$q]",$k,$Mb,$Ae);echo"</th>\n";if($T=="TABLE"){echo"<td>",checkbox("fields[$q][null]",1,$k["null"],"","","block","label-null"),"</td>\n";$Bb=$k["auto_increment"]?"checked":"";echo"<td><label class='block'><input type='radio' name='auto_increment_col' value='$q' $Bb aria-labelledby='label-ai'></label></td>\n","<td class='default-value'>";if(Driver::get()->getGenerated())echo
html_select("fields[$q][generated]",array_merge(["","DEFAULT"],Driver::get()->getGenerated()),$k["generated"]);else
echo
checkbox("fields[$q][generated]",1,$k["generated"],"","","","label-default");$Sa="name='fields[$q][default]' aria-labelledby='label-default'";$X=h($k["default"]);if(str_contains($X,"\n")){if($X[0]=="\n")$X="\n$X";echo"<textarea $Sa rows='3' cols='30' style='vertical-align: bottom;'>$X</textarea>";}else
echo"<input class='input' $Sa value='$X'>";echo"</td>\n";if(support("comment")){$lh=Connection::get()->isMinVersion("5.5")?1024:255;$Sa="name='fields[$q][comment]' data-maxlength='$lh' aria-labelledby='label-comment'";$X=h($k["comment"]);echo"<td $Wb>";if(str_contains($X,"\n")){if($X[0]=="\n")$X="\n$X";echo"<textarea $Sa rows='3' cols='30' style='vertical-align: bottom;'>$X</textarea>";}else
echo"<input class='input' $Sa value='$X'>";echo"</td>\n";}}echo"<td>";if(support("move_col"))echo"<button name='add[$q]' value='1' title='".lang(101)."' class='button light'>",icon_solo("add"),"</button>","<button name='up[$q]' value='1' title='".lang(102)."' class='button light hidden'>",icon_solo("arrow-up"),"</button>","<button name='down[$q]' value='1' title='".lang(103)."' class='button light hidden'>",icon_solo("arrow-down"),"</button>";if($Qi==""||support("drop_col"))echo"<button name='drop_col[$q]' value='1' title='".lang(59)."' class='button light'>",icon_solo("remove"),"</button>";echo"</td>\n</tr>\n";}echo"</tbody>";}function
process_fields(&$l){$mi=0;if($_POST["up"]){$Dg=0;foreach($l
as$v=>$k){if(key($_POST["up"])==$v){unset($l[$v]);array_splice($l,$Dg,0,[$k]);break;}if(isset($k["field"]))$Dg=$mi;$mi++;}}elseif($_POST["down"]){$Fe=false;foreach($l
as$v=>$k){if(isset($k["field"])&&$Fe){unset($l[key($_POST["down"])]);array_splice($l,$mi,0,[$Fe]);break;}if(key($_POST["down"])==$v)$Fe=$k;$mi++;}}elseif($_POST["add"]){$l=array_values($l);array_splice($l,key($_POST["add"]),0,[[]]);}elseif(!$_POST["drop_col"])return
false;return
true;}function
normalize_enum($z){$W=$z[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($W[0].$W[0],$W[0],substr($W,1,-1))),'\\'))."'";}function
grant($Re,array$Uj,$c,$vi,$Bn){if(!$Uj)return
true;if($Uj==["ALL PRIVILEGES","GRANT OPTION"]){if($Re)return(bool)queries("GRANT ALL PRIVILEGES ON $vi TO $Bn WITH GRANT OPTION");else
return
queries("REVOKE ALL PRIVILEGES ON $vi FROM $Bn")&&queries("REVOKE GRANT OPTION ON $vi FROM $Bn");}if($Uj==["GRANT OPTION","PROXY"]){if($Re)return(bool)queries("GRANT PROXY ON $vi TO $Bn WITH GRANT OPTION");else
return(bool)queries("REVOKE PROXY ON $vi FROM $Bn");}return(bool)queries(($Re?"GRANT ":"REVOKE ").preg_replace('~(GRANT OPTION)\([^)]*\)~','$1',implode("$c, ",$Uj).$c)." ON $vi ".($Re?"TO ":"FROM ").$Bn);}function
drop_create($id,$qc,$kd,$Dm,$md,$ch,$zh,$xh,$yh,$ti,$ai){if($_POST["drop"])query_redirect($id,$ch,$zh);elseif($ti=="")query_redirect($qc,$ch,$yh);elseif($ti!=$ai){$tc=queries($qc);queries_redirect($ch,$xh,$tc&&queries($id));if($tc)queries($kd);}else
queries_redirect($ch,$xh,queries($Dm)&&queries($md)&&queries($id)&&queries($qc));}function
create_trigger($vi,array$cn){$Nm=" $cn[Timing] $cn[Event]".(preg_match('~ OF~',$cn["Event"])?" $cn[Of]":"");return"CREATE TRIGGER ".idf_escape($cn["Trigger"]).(DIALECT=="mssql"?$vi.$Nm:$Nm.$vi).rtrim(" $cn[Type]\n$cn[Statement]",";").";";}function
create_routine($Ek,$I){$tl=[];$l=(array)$I["fields"];ksort($l);$Af=implode("|",Driver::get()->getInOut());foreach($l
as$k){if($k["field"]!="")$tl[]=(preg_match("~^($Af)\$~",$k["inout"])?"$k[inout] ":"").idf_escape($k["field"]).process_type($k,"CHARACTER SET");}$Kc=rtrim($I["definition"],";");return"CREATE $Ek ".idf_escape(trim($I["name"]))." (".implode(", ",$tl).")".($Ek=="FUNCTION"?" RETURNS".process_type($I["returns"],"CHARACTER SET"):"").($I["language"]?" LANGUAGE $I[language]":"").(DIALECT=="pgsql"?" AS ".q($Kc):"\n$Kc;");}function
remove_definer($F){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$F);}function
format_foreign_key($o){$wi=implode("|",Driver::get()->getOnActions());$h=$o["db"];$fi=$o["ns"];return" FOREIGN KEY (".implode(", ",array_map('AdminNeo\idf_escape',$o["source"])).") REFERENCES ".($h!=""&&$h!=$_GET["db"]?idf_escape($h).".":"").($fi!=""&&$fi!=$_GET["ns"]?idf_escape($fi).".":"").idf_escape($o["table"])." (".implode(", ",array_map('AdminNeo\idf_escape',$o["target"])).")".(preg_match("~^($wi)\$~",$o["on_delete"])?" ON DELETE $o[on_delete]":"").(preg_match("~^($wi)\$~",$o["on_update"])?" ON UPDATE $o[on_update]":"").(isset($o["deferrable"])?" $o[deferrable]":"");}function
tar_file($n,TmpFile$Rm){$if=pack("a100a8a8a8a12a12",$n,644,0,0,decoct($Rm->getSize()),decoct(time()));$Db=8*32;for($q=0;$q<strlen($if);$q++)$Db+=ord($if[$q]);$if
.=sprintf("%06o",$Db)."\0 ";echo$if,str_repeat("\0",512-strlen($if));$Rm->send();echo
str_repeat("\0",511-($Rm->getSize()+511)%512);}function
doc_link(array$vj,$Em="<sup>?</sup>"){if(!(isset($vj[DIALECT])?$vj[DIALECT]:null))return"";$Ln=doc_version();$xn=['sql'=>"https://dev.mysql.com/doc/refman/$Ln/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(Connection::get()->isCockroachDB()?"current":$Ln)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".str_replace(".","",$Ln)."&id=",'elastic'=>"https://www.elastic.co/guide/en/elasticsearch/reference/$Ln/",];if(Connection::get()->isMariaDB()){$xn['sql']="https://mariadb.com/docs/server/";$vj['sql']=isset($vj['mariadb'])?$vj['mariadb']:str_replace(".html","",$vj['sql']);}return"<a href='".h($xn[DIALECT].$vj[DIALECT].(DIALECT=='mssql'?"?view=sql-server-ver$Ln":""))."'".target_blank().">$Em</a>";}function
doc_version(){return
preg_replace('~^(\d\.?\d).*~s','\1',Connection::get()->getVersion());}function
db_size($h){if(!Connection::get()->selectDatabase($h))return"?";$H=0;foreach(table_status()as$Q)$H+=$Q["Data_length"]+$Q["Index_length"];return
format_number($H);}function
set_utf8mb4($qc){static$tl=false;if(!$tl&&preg_match('~\butf8mb4~i',$qc)){$tl=true;echo"SET NAMES ".charset(Connection::get()).";\n\n";}}error_reporting(E_ALL&~E_DEPRECATED);set_error_handler(function($Gd,$j){return(bool)preg_match('~^Undefined (array key|offset|index)~',$j);},E_WARNING|E_NOTICE);;$ne=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($ne||ini_get("filter.default_flags")){foreach(['_GET','_POST','_COOKIE','_SERVER']as$W){$rn=filter_input_array(constant("INPUT$W"),FILTER_UNSAFE_RAW);if($rn)$$W=$rn;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");class
Server{private$params;private$key;function
__construct(array$fj,$v=null){$this->params=$fj;$this->key=$v;}function
getKey(){return
isset($this->key)?$this->key:substr(md5($this->getDriver().$this->getServer()),0,8);}function
getDriver(){return$this->params["driver"];}function
getServer(){return
isset($this->params["server"])?$this->params["server"]:"";}function
getDatabase(){return
isset($this->params["database"])?$this->params["database"]:"";}function
getName(){return
isset($this->params["name"])?$this->params["name"]:(isset($this->params["server"])?$this->params["server"]:"");}function
getUsername(){return
isset($this->params["username"])?$this->params["username"]:"";}function
getPassword(){return
isset($this->params["password"])?$this->params["password"]:"";}function
hasCredentials(){return$this->getUsername()!=""||$this->getPassword()!="";}function
getConfigParams(){$fj=isset($this->params["config"])?$this->params["config"]:[];$Qe=["servers"];foreach($Qe
as$ej){if(isset($fj[$ej]))unset($fj[$ej]);}return$fj;}}class
Config{static$NavigationSimple="simple";static$NavigationDual="dual";static$NavigationHover="hover";static$NavigationReversed="reversed";private$params;private$servers=[];function
__construct(array$fj){$this->params=$fj;if(isset($this->params["servers"])){foreach($this->params["servers"]as$v=>$L){$ll=new
Server($L,is_string($v)?$v:null);$this->params["servers"][$v]=$ll;$this->servers[$ll->getKey()]=$ll;}}}function
getTheme(){return
isset($this->params["theme"])?$this->params["theme"]:"default";}function
getColorVariant(){return
isset($this->params["colorVariant"])?$this->params["colorVariant"]:"blue";}function
getCssUrls(){return$this->parseList(isset($this->params["cssUrls"])?$this->params["cssUrls"]:[]);}function
getJsUrls(){return$this->parseList(isset($this->params["jsUrls"])?$this->params["jsUrls"]:[]);}function
getNavigationMode(){return
isset($this->params["navigationMode"])?$this->params["navigationMode"]:self::$NavigationSimple;}function
isNavigationSimple(){return$this->getNavigationMode()==self::$NavigationSimple;}function
isNavigationDual(){return$this->getNavigationMode()==self::$NavigationDual;}function
isNavigationReversed(){return$this->getNavigationMode()==self::$NavigationReversed;}function
isSelectionPreferred(){return
isset($this->params["preferSelection"])?$this->params["preferSelection"]:false;}function
isJsonValuesDetection(){return
isset($this->params["jsonValuesDetection"])?$this->params["jsonValuesDetection"]:false;}function
isJsonValuesAutoFormat(){return
isset($this->params["jsonValuesAutoFormat"])?$this->params["jsonValuesAutoFormat"]:false;}function
isRelationLinks(){return
isset($this->params["relationLinks"])?$this->params["relationLinks"]:false;}function
getRecordsPerPage(){return(int)(isset($this->params["recordsPerPage"])?$this->params["recordsPerPage"]:50);}function
getEnumAsSelectThreshold(){if(array_key_exists("enumAsSelectThreshold",$this->params))return$this->params["enumAsSelectThreshold"]!==null?(int)$this->params["enumAsSelectThreshold"]:null;else
return
5;}function
isVersionVerificationEnabled(){return
isset($this->params["versionVerification"])?$this->params["versionVerification"]:true;}function
isSqlAutocompletionEnabled(){return
isset($this->params["sqlAutocompletion"])?$this->params["sqlAutocompletion"]:true;}function
getHiddenDatabases(){return$this->parseList(isset($this->params["hiddenDatabases"])?$this->params["hiddenDatabases"]:[]);}function
getHiddenSchemas(){return$this->parseList(isset($this->params["hiddenSchemas"])?$this->params["hiddenSchemas"]:[]);}function
getVisibleCollations(){return$this->parseList(isset($this->params["visibleCollations"])?$this->params["visibleCollations"]:[]);}function
getDefaultDriver(array$hd){$fd=isset($this->params["defaultDriver"])?$this->params["defaultDriver"]:null;return$fd&&isset($hd[$fd])?$fd:key($hd);}function
getDefaultServer(){$L=isset($this->params["defaultServer"])?$this->params["defaultServer"]:null;if($L===null)return
null;$ll=isset($this->params["servers"][$L])?$this->params["servers"][$L]:null;if($ll)return$ll->getKey();return$L;}function
getDefaultDatabase(){return
isset($this->params["defaultDatabase"])?$this->params["defaultDatabase"]:null;}function
getDefaultPasswordHash(){return
isset($this->params["defaultPasswordHash"])?$this->params["defaultPasswordHash"]:null;}function
getSslKey(){return
isset($this->params["sslKey"])?$this->params["sslKey"]:null;}function
getSslCertificate(){return
isset($this->params["sslCertificate"])?$this->params["sslCertificate"]:null;}function
getSslCaCertificate(){return
isset($this->params["sslCaCertificate"])?$this->params["sslCaCertificate"]:null;}function
getSslTrustServerCertificate(){return
isset($this->params["sslTrustServerCertificate"])?$this->params["sslTrustServerCertificate"]:null;}function
getSslEncrypt(){return
isset($this->params["sslEncrypt"])?$this->params["sslEncrypt"]:null;}function
getSslMode(){return
isset($this->params["sslMode"])?$this->params["sslMode"]:null;}function
hasServers(){return
isset($this->params["servers"]);}function
getServerPairs(array$hd){$zl=null;foreach($this->servers
as$L){if(!isset($hd[$L->getDriver()]))continue;if(!$zl)$zl=$L->getDriver();elseif($L->getDriver()!=$zl){$zl=null;break;}}$ml=[];foreach($this->servers
as$v=>$L){if(!isset($hd[$L->getDriver()]))continue;$kl=$L->getName();if($zl&&$kl)$ml[$v]=$kl;else$ml[$v]=$hd[$L->getDriver()].($kl!=""?" - $kl":"");}return$ml;}function
getServer($jl){return
isset($this->servers[$jl])?$this->servers[$jl]:null;}function
applyServer($L){$L=$this->getServer($L);if(!$L)return;$this->params=array_merge($this->params,$L->getConfigParams());}private
function
parseList($Vg){if(is_array($Vg))return$Vg;return
preg_split('~\s*,\s*~',(string)$Vg);}}class
Settings{private
static$CookieName="neo_settings";static$ColorSchemeLight="light";static$ColorSchemeDark="dark";static$NavigationWidthMin=10;static$NavigationWidthMax=30;private$config;private$params=[];function
__construct(Config$cc){$this->config=$cc;if(isset($_COOKIE[self::$CookieName])){parse_str($_COOKIE[self::$CookieName],$this->params);$this->save();}if(isset($_COOKIE["neo_lang"])){$this->updateParameter("lang",$_COOKIE["neo_lang"]);unset($_COOKIE["neo_lang"]);cookie("neo_lang","",-3600);}}static
function
readParameter($v){parse_str(isset($_COOKIE[self::$CookieName])?$_COOKIE[self::$CookieName]:"",$fj);return
isset($fj[$v])?$fj[$v]:null;}function
getParameter($v,$i=null){return
isset($this->params[$v])?$this->params[$v]:$i;}function
updateParameter($v,$X){$this->updateParameters([$v=>$X]);}function
updateParameters(array$fj){$this->params=array_filter(array_merge($this->params,$fj),function($X){return$X!==null;});$this->save();}private
function
save(){cookie(self::$CookieName,http_build_query($this->params),7776000);}function
getTheme(){return($ta=$this->getParameter("theme"))!==null?$ta:$this->config->getTheme();}function
getColorScheme(){return$this->getParameter("colorScheme");}function
getNavigationMode(){return($ta=$this->getParameter("navigationMode"))!==null?$ta:$this->config->getNavigationMode();}function
isNavigationSimple(){return$this->getNavigationMode()==Config::$NavigationSimple;}function
isNavigationDual(){return$this->getNavigationMode()==Config::$NavigationDual;}function
isNavigationHover(){return$this->getNavigationMode()==Config::$NavigationHover;}function
isNavigationReversed(){return$this->getNavigationMode()==Config::$NavigationReversed;}function
getNavigationWidth(){$co=$this->getParameter("navigationWidth");if($co===null)return
null;return
min(max((float)$co,self::$NavigationWidthMin),self::$NavigationWidthMax);}function
isSelectionPreferred(){return($ta=$this->getParameter("preferSelection"))!==null?$ta:$this->config->isSelectionPreferred();}function
isRelationLinks(){return
isset($this->params["relationLinks"])?$this->params["relationLinks"]:$this->config->isRelationLinks();}function
getRecordsPerPage(){return($ta=$this->getParameter("recordsPerPage"))!==null?$ta:$this->config->getRecordsPerPage();}function
getEnumAsSelectThreshold(){$X=$this->getParameter("enumAsSelectThreshold");if($X<0)return
null;return$X!==null?(int)$X:$this->config->getEnumAsSelectThreshold();}}class
Hash{static
function
hkdf($w,$v,$Jf="",$Lk=""){if(extension_loaded("hash")&&PHP_VERSION_ID>=70120)return
hash_hkdf("sha1",$v,$w,$Jf,$Lk);if($Lk=="")$Lk=str_repeat("\0",20);$Vj=self::hmacSha1($v,$Lk);$qi="";for($rg="",$ib=1;!isset($qi[$w-1]);$ib++){$rg=self::hmacSha1($rg.$Jf.chr($ib),$Vj);$qi
.=$rg;}return
substr($qi,0,$w);}static
function
hmacSha1($f,$v){if(!extension_loaded("hash"))return
hash_hmac("sha1",$f,$v,true);if(strlen($v)>64)$v=sha1($v,true);$v=str_pad($v,64,"\0");$Yf=($v^str_repeat("\x36",64));$_i=($v^str_repeat("\x5C",64));return
sha1($_i.sha1($Yf.$f,true),true);}}class
Random{static
function
strongKey(){return
strtr(rtrim(base64_encode(Random::bytes(32)),"="),"+/","-_");}static
function
bytes($w){if(PHP_VERSION_ID>=70000)return
random_bytes($w);$G=self::tryAlternatives($w);if($G!==false)return$G;$G=self::lastResortRandom($w);if($G!==false)return$G;throw
new
Exception("Error generating random bytes");}private
static
function
tryAlternatives($w){if(extension_loaded("libsodium"))return
\Sodium\randombytes_buf($w);$qn=DIRECTORY_SEPARATOR==="/";if($qn){$G=self::readDevUrandom($w);if($G!==false)return$G;}$mb=$qn&&PHP_VERSION_ID>50609&&PHP_VERSION_ID<50613;if(extension_loaded("mcrypt")&&!$mb){$G=mcrypt_create_iv($w,MCRYPT_DEV_URANDOM);if($G!==false)return$G;}$nb=PHP_VERSION_ID<50444||(PHP_VERSION_ID>50500&&PHP_VERSION_ID<50528)||(PHP_VERSION_ID>50600&&PHP_VERSION_ID<50612);if(extension_loaded("openssl")&&!$nb){$G=openssl_random_pseudo_bytes($w,$Ul);if($Ul)return$G;}return
false;}private
static
function
readDevUrandom($w){static$m=null;if($m===null)$m=@fopen("/dev/urandom","rb");if(!$m)return
false;$tk=$w;$G="";do{$f=fread($m,$tk);if($f===false)return
false;$tk-=strlen($f);$G
.=$f;}while($tk>0);return$G;}private
static
function
readCapicom($w){$Sb=new
\COM("CAPICOM.Utilities.1");$tk=$w;$G="";do{$f=base64_decode((string)$Sb->GetRandom($w,0));$tk-=strlen($f);$G
.=$f;}while($tk>0);return$G;}private
static
function
lastResortRandom($w){static$v=null;static$Lk=null;if($v===null){$f=$_SERVER;$f[]=uniqid("",true);shuffle($f);$v=sha1(serialize($f),true);if(extension_loaded("openssl"))$Lk=openssl_random_pseudo_bytes(20);else{$Lk="";for($q=0;$q<20;$q++)$Lk
.=chr((mt_rand()^mt_rand())%256);}}else{if((ord($v)%2===0)===(ord($Lk)%2===0))$v=Hash::hmacSha1($v,$Lk);else$Lk=Hash::hmacSha1($Lk,$v);}return
Hash::hkdf($w,$v,"$w",$Lk);}}if(!function_exists("str_starts_with")){function
str_starts_with($hf,$Wh){return
strpos($hf,$Wh)===0;}}if(!function_exists("str_contains")){function
str_contains($hf,$Wh){return
strpos($hf,$Wh)!==false;}}if(!function_exists("password_verify")){function
password_verify($D,$gf){return
false;}}if(!function_exists("ini_set")){function
ini_set($Fi,$X){return
false;}}function
version(){return
VERSION;}function
idf_unescape($s){if(!preg_match('~^[`\'"[]~',$s))return$s;$Dg=substr($s,-1);return
str_replace($Dg.$Dg,$Dg,substr($s,1,-1));}function
q($O){return
Connection::get()->quote($O);}function
number($W){return
preg_replace('~[^0-9]+~','',$W);}function
number_type(){return'((^|[^o])int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes(array$Y,$ne=false){$H=[];foreach($Y
as$v=>$W)$H[stripslashes($v)]=(is_array($W)?remove_slashes($W,$ne):($ne?$W:stripslashes($W)));return$H;}function
bracket_escape($s,$ab=false){static$Xm=[':'=>':1',']'=>':2','['=>':3','"'=>':4'];return
strtr($s,($ab?array_flip($Xm):$Xm));}function
min_version($Ln,$fh=null,$e=null){if(!$e)$e=Connection::get();if($fh&&$e->isMariaDB())$Ln=$fh;return$Ln&&$e->isMinVersion($Ln);}function
charset(Connection$e){return($e->isMinVersion("5.5.3")?"utf8mb4":"utf8");}function
link_files($A,array$me){switch($A){case'favicon-blue.ico':$n='favicon-blue-efc74ecf884c713762eec20c9dc4d927__d1b8a450.ico';break;case'favicon-green.ico':$n='favicon-green-61f55ec5946384e95631a83959d41c21__d1b8a450.ico';break;case'favicon-orange.ico':$n='favicon-orange-3d846dbc5bc583d83829238b99df432b__d1b8a450.ico';break;case'favicon-purple.ico':$n='favicon-purple-ce6def2be0e04759b864e14b856b67aa__d1b8a450.ico';break;case'favicon-red.ico':$n='favicon-red-7f57ccae9ff7d09ea2434ac566dba317__d1b8a450.ico';break;case'favicon-blue.svg':$n='favicon-blue-c8f77d42857a74e198fe1be664c756a3__d1b8a450.svg';break;case'favicon-green.svg':$n='favicon-green-df5b9797f12d8deb6981ce8a21c0f1dc__d1b8a450.svg';break;case'favicon-orange.svg':$n='favicon-orange-64d58befffc63258fbac3323863a6071__d1b8a450.svg';break;case'favicon-purple.svg':$n='favicon-purple-bdad3857d855d0af50fd6aadec68512f__d1b8a450.svg';break;case'favicon-red.svg':$n='favicon-red-fbad82e3d5b8188f0174c9ddef67372e__d1b8a450.svg';break;case'apple-touch-icon-blue.png':$n='apple-touch-icon-blue-edb1e64fb72e25db89ad6df0b7aca237__d1b8a450.png';break;case'apple-touch-icon-green.png':$n='apple-touch-icon-green-3f24708e0caa38fd51e15489d00b98e9__d1b8a450.png';break;case'apple-touch-icon-orange.png':$n='apple-touch-icon-orange-d2ed80dd1d30e6acacf2771c0d9e9383__d1b8a450.png';break;case'apple-touch-icon-purple.png':$n='apple-touch-icon-purple-af0533cec3f6731aac6c69900fd26577__d1b8a450.png';break;case'apple-touch-icon-red.png':$n='apple-touch-icon-red-3191c4fdfaefb1884b0b7072ca253167__d1b8a450.png';break;case'logo.svg':$n='logo-72a93d75457d8f61e73114f5d5de2770__d1b8a450.svg';break;case'jush.css':$n='jush-fa8e95cf26ca8182bb368928ccfaca91__f0c41c24.css';break;case'jush-dark.css':$n='jush-dark-048f39c1bde3ca2b414902695e6944d5__d1b8a450.css';break;case'jush.js':$n='jush-a308a4ec6c6eb975a3a83d10bf26368b__b9bb9ebb.js';break;case'icons.svg':$n='icons-09002f758b06357f2d8f6171c006be60__d1b8a450.svg';break;case'default-blue.css':$n='default-blue-8b1757b086ef0b91ef798cd88b40603e__9d9e818b.css';break;case'default-green.css':$n='default-green-e89f8340ea161fe36fa11f0ff02b73c8__9d9e818b.css';break;case'default-orange.css':$n='default-orange-45573000062e3876738bef36b1734b15__9d9e818b.css';break;case'default-purple.css':$n='default-purple-2da67c4916e2605882f3e958df7acb95__9d9e818b.css';break;case'default-red.css':$n='default-red-c3dbdc15d6b5575f3763e04ab1ff18cc__9d9e818b.css';break;case'default-blue-dark.css':$n='default-blue-dark-de509ae00b0bef2b023b37e0634d0540__f0c41c24.css';break;case'default-green-dark.css':$n='default-green-dark-f91943748c8dca8c7b2f21114d441a8d__f0c41c24.css';break;case'default-orange-dark.css':$n='default-orange-dark-8bf7b6a39384f83efa6f0507fc2247c3__f0c41c24.css';break;case'default-purple-dark.css':$n='default-purple-dark-56c784b451f884d91fcf13c3ed0e0d3b__f0c41c24.css';break;case'default-red-dark.css':$n='default-red-dark-0839a253345c35672e0742f38a5ff66c__f0c41c24.css';break;case'main.js':$n='main-f0cc23f345c90ab3d091882cc5d072c6__2e3e92b4.js';break;default:$n=null;break;}if(!$n)return
null;return
BASE_URL."?file=".urldecode($n);}function
ini_bool($Fi){$W=ini_get($Fi);return
preg_match('~^(on|true|yes)$~i',$W)||(int)$W;}function
ini_bytes($Lf){$W=ini_get($Lf);switch(strtolower(substr($W,-1))){case'g':$W=(int)$W*1024;case'm':$W=(int)$W*1024;case'k':$W=(int)$W*1024;}return$W;}function
max_input_vars($I,$Xi){$ih=(int)ini_get("max_input_vars");return($ih?(int)floor(($ih-$Xi)/$I):0);}function
max_input_vars_error(){$Lf="max_input_vars";return
lang(104,"$Lf = ".(int)ini_get($Lf));}function
sid(){static$H;if($H===null)$H=(session_id()&&!($_COOKIE&&ini_bool("session.use_cookies")));return$H;}function
save_driver_name($fd,$L,$A){restart_session();$_SESSION["drivers"][$fd][$L]=$A;stop_session();}function
get_driver_name($fd,$L=null){return
isset($_SESSION["drivers"][$fd][$L])?$_SESSION["drivers"][$fd][$L]:Drivers::get($fd);}function
save_login($fd,$L,$U,$D,$h=""){$v=isset($_COOKIE["neo_key"])?$_COOKIE["neo_key"]:null;$_SESSION["pwds"][$fd][$L][$U]=$v?[encrypt_string($D,$v)]:$D;$_SESSION["db"][$fd][$L][$U][$h]=true;}function
delete_login($fd,$L,$U){unset($_SESSION["pwds"][$fd][$L][$U]);unset($_SESSION["db"][$fd][$L][$U]);}function
get_password(){$D=get_session("pwds");if(is_array($D))return$_COOKIE["neo_key"]?decrypt_string($D[0],$_COOKIE["neo_key"]):false;return$D;}function
get_vals($F,$b=0){$H=[];$G=Connection::get()->query($F);if(is_object($G)){while($I=$G->fetchRow())$H[]=$I[$b];}return$H;}function
get_key_vals($F,$e=null,$ul=true){if(!$e)$e=Connection::get();$H=[];$G=$e->query($F);if(is_object($G)){while($I=$G->fetchRow()){if($ul)$H[$I[0]]=$I[1];else$H[]=$I[0];}}return$H;}function
get_rows($F,$e=null,$j="<p class='error'>"){if(!$e)$e=Connection::get();$H=[];$G=$e->query($F);if(is_object($G)){while($I=$G->fetchAssoc())$H[]=$I;}elseif(!$G&&!is_object($e)&&$j&&(defined("AdminNeo\PAGE_HEADER")||$j=="-- "))echo$j.error()."\n";return$H;}function
unique_array(array$I,array$u){foreach($u
as$t){if(!preg_match("~PRIMARY|UNIQUE~",$t["type"])&&!$t["partial"])continue;$nn=[];foreach($t["columns"]as$v){if(!isset($I[$v]))continue
2;$nn[$v]=$I[$v];}return$nn;}return
null;}function
escape_key($v){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$v,$z))return$z[1].idf_escape(idf_unescape($z[2])).$z[3];return
idf_escape($v);}function
where($Z,$l=[]){$bc=[];foreach((array)$Z["where"]as$v=>$W){$v=bracket_escape($v,true);$b=escape_key($v);$k=isset($l[$v])?$l[$v]:null;$ie=isset($k["type"])?$k["type"]:null;$Ke=isset($k["full_type"])?$k["full_type"]:null;$cg=$k&&(is_blob($k)||preg_match('~binary~',$ie));if($cg&&!is_utf8($W))$bc[]="$b = ".Driver::get()->quoteBinary($W);elseif(DIALECT=="sql"&&$ie=="json")$bc[]="$b = CAST(".q($W)." AS JSON)";elseif(DIALECT=="pgsql"&&preg_match('~^jsonb?$~',$Ke))$bc[]="$b::jsonb = ".q($W)."::jsonb";elseif(DIALECT=="sql"&&is_numeric($W)&&strpos($W,".")!==false)$bc[]="$b LIKE ".q($W);elseif(DIALECT=="mssql"&&strpos($ie,"datetime")===false)$bc[]="$b LIKE ".q(preg_replace('~[_%[]~','[\0]',$W));else$bc[]="$b = ".(isset($l[$v])?unconvert_field($l[$v],q($W)):q($W));if(DIALECT=="sql"&&preg_match('~char|text~',$ie)&&preg_match("~[^ -@]~",$W))$bc[]="$b = ".q($W)." COLLATE ".charset(Connection::get())."_bin";}foreach((array)$Z["null"]as$v)$bc[]=escape_key($v)." IS NULL";return
implode(" AND ",$bc);}function
where_columns($Z,$l=[]){$c=[];foreach((array)$Z["null"]as$v)$c[$v]=true;foreach((array)$Z["where"]as$v=>$W){$v=bracket_escape($v,true);foreach($l
as$A=>$k){if($v==$A||strpos($v,idf_escape($A))!==false)$c[$A]=true;}}return$c;}function
where_check($W,$l=[]){parse_str($W,$yb);remove_slashes([&$yb]);return
where($yb,$l);}function
where_link($q,$b,$X,$Ci="="){return"&where%5B$q%5D%5Bcol%5D=".urlencode($b)."&where%5B$q%5D%5Bop%5D=".urlencode(($X!==null?$Ci:"IS NULL"))."&where%5B$q%5D%5Bval%5D=".urlencode($X);}function
convert_fields(array$c,array$l,array$K=[]){$G="";foreach($c
as$v=>$W){if($K&&!in_array(idf_escape($v),$K))continue;$Ra=convert_field($l[$v]);if($Ra)$G
.=", $Ra AS ".idf_escape($v);}return$G;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),[";"=>"%3B",","=>"%2C"]);}function
cookie($A,$X,$Ng=2592000){header("Set-Cookie: $A=".rawurlencode($X).($Ng?"; expires=".gmdate("D, d M Y H:i:s",time()+$Ng)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_url($wn,$lc){$H=@file_get_contents($wn,false,$lc);if(function_exists('http_get_last_response_headers'))$http_response_header=($ta=http_get_last_response_headers())!==null?$ta:[];return[$H,isset($http_response_header)?$http_response_header:[]];}function
get_settings($oc="neo_settings"){parse_str(isset($_COOKIE[$oc])?$_COOKIE[$oc]:"",$M);return$M;}function
get_setting($v,$oc="neo_settings"){$M=get_settings($oc);return
isset($M[$v])?$M[$v]:null;}function
save_settings(array$M,$oc="neo_settings"){cookie($oc,http_build_query($M+get_settings($oc)));}function
restart_session(){if(!ini_bool("session.use_cookies")&&session_status()==PHP_SESSION_NONE)session_start();}function
stop_session($ye=false){$_n=ini_bool("session.use_cookies");if(!$_n||$ye){session_write_close();if($_n&&ini_set("session.use_cookies","0")===false)session_start();}}function&get_session($v){return$_SESSION[$v][DRIVER][SERVER][$_GET["username"]];}function
set_session($v,$W){$_SESSION[$v][DRIVER][SERVER][$_GET["username"]]=$W;}function
auth_url($Kn,$L,$U,$h=null){$vn=remove_from_uri(implode("|",array_keys(Drivers::getList()))."|username|ext|".($h!==null?"db|":"").($Kn=='mssql'||$Kn=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$vn,$z);return"$z[1]?".(sid()?session_name()."=".urlencode(session_id())."&":"").urlencode($Kn)."=".urlencode($L)."&".($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"")."username=".urlencode($U).($h!=""?"&db=".urlencode($h):"").($z[2]?"&$z[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($ch,$wh=null){if($wh!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($ch!==null?$ch:$_SERVER["REQUEST_URI"]))][]=$wh;}if($ch!==null){if($ch=="")$ch=".";header("Location: $ch");exit;}}function
query_redirect($F,$ch,$wh,$jk=true,$Od=true,$Zd=false,$Lm=""){if($Od){$Ql=microtime(true);$Zd=!Connection::get()->query($F);$Lm=format_time($Ql);}$Il=$F?Admin::get()->formatMessageQuery($F,$Lm,$Zd):"";if($Zd){Admin::get()->addError(error().$Il.script("initToggles();"));return
false;}if($jk)redirect($ch,$wh.$Il);return
true;}function
queries_redirect($ch,$wh,$jk){$ak=implode("\n",Queries::$queries);$Lm=format_time(Queries::$start);return
query_redirect($ak,$ch,$wh,$jk,false,!$jk,$Lm);}class
Queries{static$queries=[];static$start=0.0;}function
queries($F){if(!Queries::$start)Queries::$start=microtime(true);if(support("sql")){Queries::$queries[]=(preg_match('~;$~',$F)?"DELIMITER ;;\n$F;\nDELIMITER ":$F).";";return
Connection::get()->query($F);}else{Queries::$queries[]=$F;return[];}}function
apply_queries($F,array$R,$Id='AdminNeo\table'){foreach($R
as$P){if(!queries("$F ".$Id($P)))return
false;}return
true;}function
format_time($Ql){return
lang(105,max(0,microtime(true)-$Ql));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($ej=""){return
substr(preg_replace("~(?<=[?&])($ej".(sid()?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($v,$Fc=false,$Mc=""){$m=$_FILES[$v];if(!$m)return
null;foreach($m
as$v=>$W)$m[$v]=(array)$W;$H='';foreach($m["error"]as$v=>$j){if($j)return$j;$A=$m["name"][$v];$Sm=$m["tmp_name"][$v];$jc=file_get_contents($Fc&&preg_match('~\.gz$~',$A)?"compress.zlib://$Sm":$Sm);if($Fc){$Ql=substr($jc,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Ql))$jc=iconv("utf-16","utf-8",$jc);elseif($Ql=="\xEF\xBB\xBF")$jc=substr($jc,3);}if($Mc){if(!preg_match("~$Mc\\s*\$~",$jc))$jc
.=";";$jc
.="\n\n";}$H
.=$jc;}return$H;}function
upload_error($j){$qh=($j==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($j?lang(106).($qh?" ".lang(107,$qh):""):lang(108));}function
repeat_pattern($wj,$w){return
str_repeat("$wj{0,65535}",$w/65535)."$wj{0,".($w%65535)."}";}function
is_utf8($W){return(preg_match('~~u',$W)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$W));}function
format_number($W){return
strtr(number_format($W,0,".",lang(109)),preg_split('~~u',lang(110),-1,PREG_SPLIT_NO_EMPTY));}function
format_rows(array$Q){$J=$Q["Rows"];$Oa=($J&&(DIALECT=="sqlite"||(isset($Q["Engine"])?$Q["Engine"]:"")==(DIALECT=="pgsql"?"table":"InnoDB")));return($Oa?"~ ":"").format_number($J);}function
friendly_url($W){return
preg_replace('~\W~i','-',$W);}function
table_status1($P,$be=false){$H=table_status($P,$be);return($H?reset($H):["Name"=>$P]);}function
column_foreign_keys($P){$H=[];foreach(Admin::get()->getForeignKeys($P)as$o){foreach($o["source"]as$W)$H[$W][]=$o;}return$H;}function
fields_from_edit(){$H=[];foreach((array)$_POST["field_keys"]as$v=>$W){if($W!=""){$W=bracket_escape($W);$_POST["function"][$W]=$_POST["field_funs"][$v];$_POST["fields"][$W]=$_POST["field_vals"][$v];}}foreach((array)$_POST["fields"]as$v=>$W){$A=bracket_escape($v,true);$H[$A]=["field"=>$A,"full_type"=>"varchar","type"=>"varchar","privileges"=>["insert"=>1,"update"=>1,"where"=>1,"order"=>1],"null"=>true,"auto_increment"=>($v==Driver::get()->primary),];}return$H;}function
dump_headers($wf,$Nh=false){$wf=friendly_url($wf).date("-Ymd-His");$Vd=Admin::get()->sendDumpHeaders($wf,$Nh);$Zi=$_POST["output"];if($Zi!="text")header("Content-Disposition: attachment; filename=$wf.$Vd".($Zi!="file"&&preg_match('~^[0-9a-z]+$~',$Zi)?".$Zi":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$Vd;}function
dump_table_order(array$Uh,array$pk){$xg=array_flip($Uh);$Li=[];$Tn=[];$yc=false;$Sn=function($A)use(&$Sn,&$Li,&$Tn,&$yc,$xg,$pk){if(isset($Li[$A]))return;if(isset($Tn[$A])){$yc=true;return;}$Tn[$A]=true;foreach(isset($pk[$A])?$pk[$A]:[]as$nk){if(isset($xg[$nk]))$Sn($nk);}unset($Tn[$A]);$Li[$A]=true;};foreach($Uh
as$A)$Sn($A);return($yc?null:array_keys($Li));}function
dump_csv($I){$hn=$_POST["format"]=="tsv";foreach($I
as$v=>$W){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($hn?'\t':'[,;]|^$').'~',$W))$I[$v]='"'.str_replace('"','""',$W).'"';}echo
implode(($_POST["format"]=="csv"?",":($hn?"\t":";")),$I)."\r\n";}function
apply_sql_function($p,$b){return($p?($p=="unixepoch"?"DATETIME($b, '$p')":($p=="count distinct"?"COUNT(DISTINCT ":strtoupper("$p("))."$b)"):$b);}function
get_temp_dir(){$uj=ini_get("upload_tmp_dir");if(!$uj)$uj=sys_get_temp_dir();return$uj;}function
open_file_with_lock($n){if(is_link($n))return
null;$m=@fopen($n,"c+");if(!$m)return
null;@chmod($n,0660);if(!flock($m,LOCK_EX)){fclose($m);return
null;}return$m;}function
write_and_unlock_file($m,$f){rewind($m);fwrite($m,$f);ftruncate($m,strlen($f));unlock_file($m);}function
unlock_file($m){flock($m,LOCK_UN);fclose($m);}function
first(array$Qa){return
reset($Qa);}function
get_private_key($qc){$n=get_temp_dir()."/adminneo.key";if(!$qc&&!file_exists($n))return
false;$m=open_file_with_lock($n);if(!$m)return
false;$v=stream_get_contents($m);if(!$v){$v=Random::strongKey();write_and_unlock_file($m,$v);}else
unlock_file($m);return$v;}function
get_random_string(){return
Random::strongKey();}function
select_value($W,$y,$k,$Hm){if(is_array($W)){$H="";if(array_filter($W,'is_array')==array_values($W)){$tg=[];foreach($W
as$V)$tg+=array_fill_keys(array_keys($V),null);foreach(array_keys($tg)as$ng)$H
.="<th>".h($ng);foreach($W
as$V){$H
.="<tr>";foreach(array_merge($tg,$V)as$En)$H
.="<td>".select_value($En,$y,$k,$Hm);}}else{foreach($W
as$ng=>$V)$H
.="<tr>".($W!=array_values($W)?"<th>".h($ng):"")."<td>".select_value($V,$y,$k,$Hm);}return"<table>$H</table>";}$Qk="";if($k&&$W!==null&&($Hm===null||strlen($W)<=$Hm)&&($Y=Driver::get()->explodeArrayValue($W,$k["full_type"],$Qk))){$Pk=$k;$Pk["type"]=$Pk["full_type"]=$Qk;$H=select_array_value($Y,$W,$y,$Pk,$Hm);return
Driver::get()->implodeArrayValues($H,$k["full_type"]);}if(!$y)$y=Admin::get()->getFieldValueLink($W,$k);if($k)$W=Connection::get()->formatValue($W,$k);$H=$k?Admin::get()->formatFieldValue($W,$k):$W;if($H!==null){if(!is_utf8($H))$H="\0";elseif($Hm!=""&&is_shortable($k))$H=truncate_utf8($H,max(0,+$Hm));else$H=h($H);}return
Admin::get()->formatSelectionValue($H,$y,$k,$W);}function
select_array_value(array$Y,$W,$y,array$k,$Hm){$G=[];foreach($Y
as$X){if(is_array($X))$G[]=select_array_value($X,$W,$y,$k,$Hm);else{$yg=preg_replace('~(where%5B\d+%5D%5Bval%5D=)'.preg_quote(urlencode($W),"~")."~",'${1}'.urlencode($X),$y);$G[]=select_value($X,$yg,$k,$Hm);}}return$G;}function
is_blob(array$k){$kn=Driver::get()->getStructuredTypes();$T=lang(111);return
preg_match('~blob|bytea|raw|file'.(DIALECT=="mssql"?'|binary|image':'').'~',$k["type"])&&!in_array($k["type"],isset($kn[$T])?$kn[$T]:[]);}function
is_generated_always(array$k){return(isset($k["generated"])?$k["generated"]:"")!=""||stripos((string)(isset($k["default"])?$k["default"]:""),"GENERATED ALWAYS AS ")===0;}function
is_mail($X){return
is_string($X)&&filter_var($X,FILTER_VALIDATE_EMAIL);}function
is_web_url($X){if(!is_string($X)||!preg_match('~^(https?:)?//~i',$X))return
false;$Zb=parse_url($X);if(!$Zb)return
false;$wn=$X;if(isset($Zb['path'])){$Ad=array_map('urlencode',explode('/',$Zb['path']));$wn=str_replace($Zb['path'],implode('/',$Ad),$wn);}if(isset($Zb['query'])){parse_str($Zb['query'],$fj);$wn=str_replace($Zb['query'],http_build_query($fj),$wn);}if(!isset($Zb['scheme']))$wn="https:$wn";return(bool)filter_var($wn,FILTER_VALIDATE_URL);}function
is_shortable($k){return$k&&!preg_match('~'.number_type().'|date|time|year~',$k["type"]);}function
host_port($L){return(preg_match('~^(:([^:].*)|(\[(.+)]|(([^:]+://)?[^:]+))(:(\d+))?)$~',$L,$z)?[(isset($z[4])?$z[4]:"").(isset($z[5])?$z[5]:""),$z[2].(isset($z[8])?$z[8]:"")]:[$L,'']);}function
count_rows($P,$Z,$dg,$Ue){$F=" FROM ".table($P).($Z?" WHERE ".implode(" AND ",$Z):"");return($dg&&(DIALECT=="sql"||count($Ue)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$Ue).")$F":"SELECT COUNT(*)".($dg?" FROM (SELECT 1$F GROUP BY ".implode(", ",$Ue).") x":$F));}function
slow_query($F){$h=Admin::get()->getDatabase();$Mm=Admin::get()->getQueryTimeout();$Al=Driver::get()->slowQuery($F,$Mm);$e=null;if(!$Al&&support("kill")){$e=connect();if($e&&($h==""||$e->selectDatabase($h))){$vg=number($e->getValue(connection_id()));echo'<script',nonce(),'>
	const timeout = setTimeout(() => {
		ajax(\'',js_escape(ME),'script=kill\', function() {
		}, \'kill=',$vg,'&token=',get_token(),'\');
	}, ',1000*$Mm,');
</script>
';}}ob_flush();flush();$H=@get_key_vals(($Al?:$F),$e,false);if($e){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$H;}function
get_token(){$fk=rand(1,1e6);return($fk^$_SESSION["token"]).":$fk";}function
verify_token(){list($Tm,$fk)=explode(":",$_POST["token"]);return($fk^$_SESSION["token"])==$Tm&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],["","same-origin"]);}function
script($El,$Wm="\n"){return"<script".nonce().">$El</script>$Wm";}function
script_src($wn,$Jc=false){return"<script src='".h($wn)."'".nonce().($Jc?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
input_hidden($A,$X=""){return"<input type='hidden' name='".h($A)."' value='".h($X)."'>";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($O){if($O===null||$O==="")return"";return
str_replace(["&","<","\"","'","\0"],["&amp;","&lt;","&quot;","&#039;","&#0;"],$O);}function
truncate_utf8($O,$w=80){if($O=="")return"";if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$w).")($)?)u",$O,$z))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$w).")($)?)",$O,$z);return
h($z[1]).(isset($z[2])?"":"<i>…</i>");}function
icon_solo($r){return
icon($r,"solo");}function
icon_chevron_down(){return
icon("chevron-down","chevron");}function
icon_chevron_right(){return
icon("chevron-down","chevron-right");}function
icon($r,$Gb=null){$r=h($r);return"<svg class='icon ic-$r $Gb'><use href='".link_files("icons.svg",[])."#$r'/></svg>";}function
checkbox($A,$X,$Bb,$zg="",$yi="",$Gb="",$Ag=""){$H="<input type='checkbox' name='$A' value='".h($X)."'".($Bb?" checked":"").($Ag?" aria-labelledby='$Ag'":"").">".($yi?script("qsl('input').onclick = function () { $yi };",""):"");return($zg!=""||$Gb?"<label".($Gb?" class='$Gb'":"").">$H".h($zg)."</label>":$H);}function
optionlist($B,$al=null,$An=false){$H="";foreach($B
as$ng=>$V){$Hi=[$ng=>$V];if(is_array($V)){$H
.='<optgroup label="'.h($ng).'">';$Hi=$V;}foreach($Hi
as$v=>$W)$H
.='<option'.($An||is_string($v)?' value="'.h($v).'"':'').($al!==null&&($An||is_string($v)?(string)$v:$W)===$al?' selected':'').'>'.h($W);if(is_array($V))$H
.='</optgroup>';}return$H;}function
html_select($A,$B,$X="",$xi="",$Ag="",$An=false){static$zg=0;$_g="";if(!$Ag&&substr(isset($B[""])?$B[""]:"",0,1)=="("){$zg++;$Ag="label-$zg";$_g="<option value='' id='$Ag'>".h($B[""]);unset($B[""]);}return"<select name='".h($A)."'".($Ag?" aria-labelledby='$Ag'":"").">".$_g.optionlist($B,$X,$An)."</select>".($xi?script("qsl('select').onchange = function () { $xi };",""):"");}function
html_radios($A,$B,$X=""){$G="<span class='labels'>";foreach($B
as$v=>$W)$G
.="<label><input type='radio' name='".h($A)."' value='".h($v)."'".($v==$X?" checked":"").">".h($W)."</label>";$G
.="</span>";return$G;}function
confirm($wh="",$cl="qsl('input')"){return
script("$cl.onclick = () => confirm('".js_escape($wh?:lang(112))."');","");}function
print_fieldset_start($r,$Jg,$vf,$Qn=false,$Cl=false){echo"<fieldset id='fieldset-$r' class='closable ".(!$Qn?" closed":"")."'>","<legend><a href='#'>$Jg</a></legend>",icon($vf,"fieldset-icon jsonly"),"<div class='fieldset-content".($Cl?" sortable":"")."'>";}function
print_fieldset_end($r,$Cl=false){echo"</div>",script("initFieldset('$r');","");if($Cl)echo
script("initSortable('#fieldset-$r .fieldset-content');","");echo"</fieldset>\n";}function
bold($jb,$Gb=""){return($jb?" class='$Gb active'":($Gb?" class='$Gb'":""));}function
js_escape($O){return
str_replace("<","\\x3C",addcslashes($O,"\r\n'\\"));}function
js_escape_key($O){return'"'.str_replace("<","\\x3C",addcslashes($O,"\r\n\t\"\\")).'"';}function
js_escape_re($O){return
addcslashes(preg_quote($O,"/"),"\r\n");}function
pagination($C,$vc){return"<li>".($C==$vc?"<strong>".($C+1)."</strong>":'<a href="'.h(remove_from_uri("page").($C?"&page=$C".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($C+1)."</a>")."</li>";}function
print_hidden_fields(array$Wj,array$yf=[],$Lj=""){$G=false;foreach($Wj
as$v=>$W){if(!in_array($v,$yf)){if(is_array($W))print_hidden_fields($W,[],$v);else{$G=true;echo
input_hidden($Lj?$Lj."[$v]":$v,$W);}}}return$G;}function
hidden_fields_get(){if(sid())echo
input_hidden(session_name(),session_id());if(SERVER!==null)echo
input_hidden(DRIVER,SERVER);echo
input_hidden("username",$_GET["username"]);}function
enum_input($Sa,array$k,$X,$yd=null,$Ab=false){preg_match_all("~'((?:[^']|'')*)'~",$k["length"],$_);$Y=$_[1];$Km=Admin::get()->getSettings()->getEnumAsSelectThreshold();$K=!$Ab&&$Km!==null&&count($Y)>$Km;$T=$Ab?"checkbox":"radio";$za=$K?"selected":"checked";$G=$K?"<select $Sa>":"<span class='labels'>";if($K&&$k["null"]&&$yd!==""){$Bb=$X===null?$za:"";$G
.="<option value='__adminneo_empty__' disabled $Bb></option>";}if($yd!==null){$Bb=(is_array($X)?in_array($yd,$X):$X===$yd)?$za:"";if($K)$G
.="<option value='$yd' $Bb>".lang(113)."</option>";else$G
.="<label><input type='$T' $Sa value='$yd' $Bb><i>".lang(113)."</i></label>";}foreach($Y
as$W){if($yd===""&&$W==="")continue;$W=stripcslashes(str_replace("''","'",$W));$Bb=is_array($X)?in_array($W,$X):$X===$W;$Bb=$Bb?$za:"";$Ee=$W===""?("<i>".lang(113)."</i>"):h(Admin::get()->formatFieldValue($W,$k));if($K)$G
.="<option value='".h($W)."' $Bb>$Ee</option>";else$G
.=" <label><input type='$T' $Sa value='".h($W)."' $Bb>$Ee</label>";}$G
.=$K?"</select>":"</span>";return$G;}function
input($k,$X,$p,$Xa=false){$A=h(bracket_escape($k["field"]));$kn=Driver::get()->getTypes();$eg=isset($k["full_type"])&&Admin::get()->detectJson($k["full_type"],$X,true);$vk=(DIALECT=="mssql"&&$k["auto_increment"]&&!$_POST["clone"]);if($vk&&!$_POST["save"])$p=null;if(in_array($k["type"],Driver::get()->getUserTypes())){$Fd=type_values($kn[$k["type"]]);if($Fd){$k["type"]="enum";$k["length"]=$Fd;}}$Sa=" name='fields[$A]' ".($Xa?" autofocus":"");$Me=(isset($_GET["select"])||$vk?["orig"=>lang(114)]:[])+Admin::get()->getFieldFunctions($k);$ef=(in_array($p,$Me)||isset($Me[$p]));echo"<td class='function'>",Driver::get()->getUnconvertFunction($k)." ";if(count($Me)>1){$al=$p===null||$ef?$p:"";echo"<select name='function[$A]'>".optionlist($Me,$al)."</select>",help_script_command("value.replace(/^SQL\$/, '')",true),script("qsl('select').onchange = functionChange;","");}else
echo
h(reset($Me));echo"</td><td>";$Mf=Admin::get()->getFieldInput(isset($_GET["edit"])?$_GET["edit"]:null,$k,$Sa,$X,$p);if($Mf!="")echo$Mf;elseif(preg_match('~bool~',$k["type"]))echo"<input type='hidden'$Sa value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?" checked":"")."$Sa value='1'>";elseif($k["type"]=="enum")echo
enum_input($Sa,$k,$X);elseif($k["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$k["length"],$_);echo"<span class='labels'>";foreach($_[1]as$W){$W=stripcslashes(str_replace("''","'",$W));$Bb=$X!==null&&in_array($W,explode(",",$X),true);$Bb=$Bb?"checked":"";$Ee=$W===""?("<i>".lang(113)."</i>"):h(Admin::get()->formatFieldValue($W,$k));echo" <label><input type='checkbox' name='fields[$A][]' value='".h($W)."' $Bb>$Ee</label>";}echo"</span>";}elseif(is_blob($k)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$A'>";elseif($eg)echo"<textarea $Sa cols='50' rows='12' class='jush-json'>".h($X).'</textarea>';elseif(($Em=preg_match('~text|lob|memo|json~i',$k["type"]))||preg_match("~\n~",$X)){if($Em&&DIALECT!="sqlite")$Sa
.=" cols='50' rows='12'";else{$J=min(12,substr_count($X,"\n")+1);$Sa
.=" cols='30' rows='$J'";}echo"<textarea $Sa>".h($X).'</textarea>';}else{$sh=!preg_match('~int~',$k["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$k["length"],$z)?((preg_match("~binary~",$k["type"])?2:1)*$z[1]+($z[3]?1:0)+($z[2]&&!$k["unsigned"]?1:0)):($kn&&$kn[$k["type"]]?$kn[$k["type"]]+($k["unsigned"]?0:1):0);if(DIALECT=='sql'&&Connection::get()->isMinVersion("5.6")&&preg_match('~time~',$k["type"]))$sh+=7;echo"<input class='input'".((!$ef||$p==="")&&preg_match('~(?<!o)int(?!er)~',$k["type"])&&!preg_match('~\[]~',$k["full_type"])?" type='number'":"").($p!="now"?" value='".h($X)."'":" data-last-value='".h($X)."'").($sh?" data-maxlength='$sh'":"").(preg_match('~char|binary~',$k["type"])&&$sh>20?" size='44'":"")."$Sa>";}$mf=Admin::get()->getFieldInputHint($_GET["edit"],$k,$X);if($mf!="")echo" <span class='input-hint'>$mf</span>";if(count($Me)>1)echo
script("qs('select', qsl('td').previousSibling).onchange(null, true);","");$re=0;foreach($Me
as$v=>$W){if($v===""||!$W)break;$re++;}if(count($Me)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $re);");}function
process_input($k){if(is_generated_always($k))return
null;$s=bracket_escape($k["field"]);$p=isset($_POST["function"][$s])?$_POST["function"][$s]:"";if($p=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?idf_escape($k["field"]):false);if($p=="NULL")return
Driver::get()->getNull();if(is_blob($k)&&ini_bool("file_uploads")){$m=get_file("fields-$s");if(!is_string($m))return
false;return
Driver::get()->quoteBinary($m);}$X=isset($_POST["fields"][$s])?$_POST["fields"][$s]:(isset($_FILES["fields"]["name"][$s])?$_FILES["fields"]["name"][$s]:null);if($X===null)return
false;if($k["auto_increment"]&&$X=="")return
null;if($k["type"]=="set")$X=implode(",",(array)$X);if($p=="json"){$X=json_decode($X,true);if(!is_array($X))return
false;return$X;}return
Admin::get()->processFieldInput($k,$X,$p);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$_k=$Hd=[];foreach(table_status("",true)as$P=>$Q){$om=Admin::get()->getTableName($Q);if(!isset($Q["Engine"])||$om==""||($_POST["tables"]&&!in_array($P,$_POST["tables"])))continue;$G=Connection::get()->query("SELECT".limit("1 FROM ".table($P)," WHERE ".implode(" AND ",Admin::get()->processSelectionSearch(fields($P),[])),1));if($G&&!$G->fetchRow())continue;$y=h(ME."select=".urlencode($P)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]));if($G)$_k[]="<li><a href='$y'>".icon("search")."$om</a></li>";else$Hd[]="<div class='error'><a href='$y'>$om</a>: ".error()."</div>";}if($_k)echo"<ul class='links'>\n",implode("\n",$_k),"</ul>\n";if($Hd)echo
implode("\n",$Hd),"\n";if(!$_k&&!$Hd)echo"<p class='message'>".lang(79)."</p>\n";}function
help_script($Em,$yl=false){return
script("initHelpFor(qsl('select, input'), '".h($Em)."', $yl);","");}function
help_script_command($Tb,$yl=false){return
script("initHelpFor(qsl('select, input'), (value) => { return $Tb; }, $yl);","");}function
edit_form($P,$l,$I,$un){$om=Admin::get()->getTableName(table_status1($P,true));$S=$un?lang(39):lang(115);page_header("$S: $om",["select"=>[$P,$om],$S]);if($I===false){echo"<p class='error'>".lang(92)."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$ud=false;$bo=($un&&!isset($_GET["select"])?where_columns($_GET,$l):[]);$mc=(count($bo)!=count($l));if(!$mc)$bo=[];if(!$l)echo"<p class='error'>".lang(116)."\n";else{echo"<table class='box'>".script("qsl('table').onkeydown = onEditingKeydown;");$Xa=!$_POST;foreach($l
as$A=>$k){echo"<tr".(isset($bo[$A])?" class='where-column'":"")."><th>".Admin::get()->getFieldName($k);$v=bracket_escape($A);$i=isset($_GET["preset"][$v])?$_GET["preset"][$v]:null;if($i===null){$i=$k["default"];if($k["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$i,$rk))$i=$rk[1];if(DIALECT=="sql"&&preg_match('~binary~',$k["type"]))$i=bin2hex($i);}$X=($I!==null?($I[$A]!=""&&DIALECT=="sql"&&preg_match("~enum|set~",$k["type"])&&is_array($I[$A])?implode(",",$I[$A]):(is_bool($I[$A])?+$I[$A]:$I[$A])):(!$un&&$k["auto_increment"]?"":(isset($_GET["select"])?false:$i)));if(!$_POST["save"]&&is_string($X))$X=Admin::get()->formatFieldValue($X,$k);if(($un&&!isset($k["privileges"]["update"]))||is_generated_always($k)){echo"<td class='function'></td><td>";if($un||!$k["generated"])echo
select_value($X,'',$k,null);else
echo"<code class='jush-".DIALECT."'>",h($X),"</code>";echo"</td>";}else{$ud=true;$p=($_POST["save"]?isset($_POST["function"][$v])?$_POST["function"][$v]:"":($un&&preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?"now":($X===false?null:($X!==null?'':'NULL'))));if(!$_POST&&!$un&&$X==$k["default"]&&preg_match('~^[\w.]+\(~',$X))$p="SQL";if(preg_match("~time~",$k["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$X)){$X="";$p="now";}if($k["type"]=="uuid"&&$X=="uuid()"){$X="";$p="uuid";}if($Xa!==false)$Xa=($k["auto_increment"]||$p=="now"||$p=="uuid"?null:true);input($k,$X,$p,(bool)$Xa);if($Xa)$Xa=false;}echo"\n";}if(!support("table")&&!fields($P))echo"<tr>"."<th><input class='input' name='field_keys[]'>".script("qsl('input').oninput = fieldChange;","")."<td class='function'>".html_select("field_funs[]",Admin::get()->getFieldFunctions(["null"=>isset($_GET["select"])]))."<td><input class='input' name='field_vals[]'>"."\n";echo"</table>\n",script("initToggles(gid('form'));");if($bo)echo
script("initWhereChange();");}echo"<p>";if($ud){echo"<input type='submit' class='button default' value='".lang(117)."'>\n";if(!isset($_GET["select"])&&$mc){$Vc=($bo&&Admin::get()->getErrors()?" disabled":"");echo"<input type='submit' class='button' name='insert' value='".($un?lang(118):lang(119))."' title='Ctrl+Shift+Enter'$Vc>\n",($un?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".js_escape(lang(120))."', this); };"):"");}}echo($un?"<input type='submit' class='button' name='delete' value='".lang(121)."'>".confirm()."\n":"");if(isset($_GET["select"]))print_hidden_fields(["check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]]);echo
input_hidden("referer",isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),input_hidden("save","1"),input_token(),"</form>\n";}function
file_upload_form_script($Be,$Nf){$kh=ini_get("max_file_uploads");$qh=ini_get("upload_max_filesize");$rh=ini_bytes("upload_max_filesize");return
script("initFilesUploadForm('".js_escape($Be)."', '".js_escape($Nf)."', "."$kh, '".js_escape(lang(122,$kh,"'max_file_uploads'"))."', "."$rh, '".js_escape(lang(123,$qh,"'upload_max_filesize'"))."')");}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($O){$Ka=array_flip(str_split(compress_alphabet()));$w=strlen($O);$Hn=($w?13*($w-1)/2-$Ka[$O[0]]:0);$fb="";$yk=0;$zk=0;for($q=1;$q<$w;$q+=2){$yk=($yk<<13)+$Ka[$O[$q]]*93+$Ka[$O[$q+1]];$zk+=13;while($zk>=8&&$Hn>=8){$zk-=8;$Hn-=8;$fb
.=chr($yk>>$zk);$yk&=(1<<$zk)-1;}}if($fb=="")return"";return
function_exists('gzinflate')?gzinflate($fb):inflate($fb);}function
inflate($fb){$Kg=[3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258];$Lg=[0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0];$Yc=[1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577];$ad=[0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13];$H="";$E=0;do{$pe=inflate_bits($fb,$E,1);$T=inflate_bits($fb,$E,2);if(!$T){$E=($E+7)&~7;$w=inflate_bits($fb,$E,16);$E+=16;$H
.=substr($fb,$E>>3,$w);$E+=$w<<3;}else{if($T==1){$Xg=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$bd=array_fill(0,30,5);}else{$Wg=inflate_bits($fb,$E,5)+257;$Zc=inflate_bits($fb,$E,5)+1;$Ii=[16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15];$Bh=array_fill(0,19,0);$Ah=inflate_bits($fb,$E,4)+4;for($q=0;$q<$Ah;$q++)$Bh[$Ii[$q]]=inflate_bits($fb,$E,3);$Ch=inflate_table($Bh);$Mg=[];while(count($Mg)<$Wg+$Zc){$em=inflate_symbol($fb,$E,$Ch);if($em==16)$Mg=array_merge($Mg,array_fill(0,inflate_bits($fb,$E,2)+3,end($Mg)));elseif($em==17)$Mg=array_merge($Mg,array_fill(0,inflate_bits($fb,$E,3)+3,0));elseif($em==18)$Mg=array_merge($Mg,array_fill(0,inflate_bits($fb,$E,7)+11,0));else$Mg[]=$em;}$Xg=array_slice($Mg,0,$Wg);$bd=array_slice($Mg,$Wg);}$Yg=inflate_table($Xg);$dd=inflate_table($bd);while(($em=inflate_symbol($fb,$E,$Yg))!=256){if($em<256)$H
.=chr($em);else{$w=$Kg[$em-257]+inflate_bits($fb,$E,$Lg[$em-257]);$cd=inflate_symbol($fb,$E,$dd);$mi=strlen($H)-$Yc[$cd]-inflate_bits($fb,$E,$ad[$cd]);for($q=0;$q<$w;$q++)$H
.=$H[$mi+$q];}}}}while(!$pe);return$H;}function
inflate_bits($fb,&$E,$pc){$H=0;for($q=0;$q<$pc;$q++){$H+=((ord($fb[$E>>3])>>($E&7))&1)<<$q;$E++;}return$H;}function
inflate_table(array$Mg){$P=[];$Ib=0;for($gb=1;$gb<=max($Mg);$gb++){foreach($Mg
as$em=>$w){if($w==$gb){$P[$gb][$Ib]=$em;$Ib++;}}$Ib<<=1;}return$P;}function
inflate_symbol($fb,&$E,array$P){$Ib=0;$gb=0;do{$Ib=($Ib<<1)+inflate_bits($fb,$E,1);$gb++;}while(!isset($P[$gb][$Ib]));return$P[$gb][$Ib];}if(isset($_GET["file"]))load_compiled_file($_GET["file"]);function
load_compiled_file($n){if($n==""){http_response_code(404);exit;}if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){http_response_code(304);exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");ini_set("zlib.output_compression","1");$Vd=pathinfo($n,PATHINFO_EXTENSION);switch($Vd){case"css":header("Content-Type: text/css; charset=utf-8");break;case"js":header("Content-Type: text/javascript; charset=utf-8");break;case"ico":header("Content-Type: image/x-icon");break;case"png":header("Content-Type: image/png");break;case"svg":header("Content-Type: image/svg+xml");break;}switch($n){case'favicon-blue-efc74ecf884c713762eec20c9dc4d927__d1b8a450.ico':$f='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGZowEUFhM2G6A4QAJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+cUn3+6qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9jompoYUe3hg9tFCL+dNX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryEWPJCE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gDMBOIUjRp9w0AAAAASUVORK5CYII=';break;case'favicon-green-61f55ec5946384e95631a83959d41c21__d1b8a450.ico':$f='AAABAAEAICAAAAEAIAC+AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYVJREFUeNpiYOhiAFBfBxBohGEcxs/RmgQQEQDbQAACBAFCmBDgogLCZkN0BwiATBZQZNsAgmwAgyMAuRZCAtvkGLTy7sEHcFbvfWT4AbjH8f75Huq/CXBRgY8lQuzx29gjxBI+KnBtBDxFgJ+QO/1AgKw24AW+Q1La4rkm4DPEkk+agCvEkqsmQKxSBGwhlkSagA7Eko72DNs4QZRO8NIOUQk+1pAbreGjlGaI3ibENNDFEO+MIbpoJHz0jfYKRngCRymLEUQXABzQRxHOjYro4wBJGwAAEaYYoIeXRg8DTBHZ2oHo0TvwGmLJK01ADnNISnPkFAEA2jhC7nSEl2YHmnAMF1VMsEEMAQDE2GCCKlw4RlMT8Ad1OAnyeGbk4SSo46I9wzPGKMC5UwFjnLVneIEYMWZo/SOmgBZmiCGA7g98hSSIscM3Y4cYkuCLJsCDWOJpAjL4CEnpAzLaHXAxhSi9h2vjZVTDCnKjFWqw/jYsI8ACIX4ZIRYIUMbfEW3maO8YAIxSqCXQN5/tAAAAAElFTkSuQmCC';break;case'favicon-orange-3d846dbc5bc583d83829238b99df432b__d1b8a450.ico':$f='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRATANhCAAEGAEGbIwEUFhM2G6A4QAJksoMi2AQTZAAZHAEsthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9fO1dVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwAv8gqS0xXNNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwMddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyBV7BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4FsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8CO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09ALYCTFLvUfsXAAAAAElFTkSuQmCC';break;case'favicon-purple-ce6def2be0e04759b864e14b856b67aa__d1b8a450.ico':$f='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGYIcFEBYbMhugM0AJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+e8Ln66qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9iompoYUe3hg9tFCL+dOX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryAWPJcE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gD0xZAYUYkFLAAAAAASUVORK5CYII=';break;case'favicon-red-7f57ccae9ff7d09ea2434ac566dba317__d1b8a450.ico':$f='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRAQY20AAAgQBQpghwEUFhM2G6A4QAJksoMi2AQTZBhgcAUgthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9eOZdVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwEv8gqS0xQtNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwKddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyB17BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4HsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8DO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09AE8YAEN5XivhAAAAAElFTkSuQmCC';break;case'favicon-blue-c8f77d42857a74e198fe1be664c756a3__d1b8a450.svg':$f='+<bATb3V?$so%el,wEIwK&mlYjGZ$a8-HGs8y$j)-UBmQx`Cf?>]C6?xmhS1<w
ZNSJl63"aZ]nB<rgk|tG)vC,pHYx;Wb2NVXBMxd!lQ=g0"!mM[]c*SW?7
E^]B7t[fHolNczfsymCW
CM~8Ult[(brlOx`71nDc
H;N~ybgNd*l6LMbRk;>%06rQeBnDc14r_7Z0b9uqO=xV5=22d05hr#V]F`V>ZD,#JA9[$XEV-*TBfz7Z%
rEJw!G!cT+[7>-u:iVU)N$iv%ySN.`._&}sV@FUIuH)%cY-0#OXWPT8t./3Oid8~R]3?9n;/Qs
Y2!K`1Q9d
tys6C=xmJfXFCT%0xl`H&%njK7`N[.q6jET6kV
VqmiJoIrZ#rsP~q0vDN<FH1w9l4R-?A#H:#onn0@0]3dNk3,E{<7r;2q
u)F!d(nhskXC~JT4N!~!g52r#`3hF[%j
oPE~ZV(W_~g#t?WXQCxEe7)ZQKGxei4.gu_R>pAL]HDZf!uSL)$_)^vZ:Xk![_HKh=C|S~PjqHcgjUutq#-~?PmA#<MYyg2R';break;case'favicon-green-df5b9797f12d8deb6981ce8a21c0f1dc__d1b8a450.svg':$f='&<bATb3V?$so%eo_t_hLePj9EjS(Ic^3}i[Os!U@i%X`9w9h7
[@
I:KPV|A4v9o?tG^4G8Kl/Za|j!Q;%pO#+U]tOQ@f!m@5m(iU+Xd
:mOOX[.7/V%^=Dj0`KWYnDy%^,cfeZsrK*5P&I[0
$m~<+JorKkYLM7yy$Aw$[w/57.-wX^0JIMH;`
EW/c}a9c

eAhG[68u92FB)
ToD/k0uYFG=PN>>d,^BJ&icL,/i,2N?:udHuPgxk>l07BF^82,>cspK:`^P,q[%?5=4<`h.?.kJ"X_a_mu|Z>-SL,/k)gDk#g):;if"T:5u9
?>JfFYF#KP)D%8xDljc2l^mJ<^%4+Iu=v?FB.Y^67X>9:@KMfCyqQL.-Kmn#DsAler%=^*ps8vI)CEG%8
bknN$znn
D-t3DO&3,C5<7r@2q],)F!d(nhsf)H-JU7?!^"9+Xtpm.q8>NCVHgU$kcVodiJa6[qLov!)IR#}x3*ku*P%3jGe?w?dILBVvhJ~,bFMpBcD%CK/#{0d-|Mu[Pn{fe.3aFbOf6.%,.opHOIuO9';break;case'favicon-orange-64d58befffc63258fbac3323863a6071__d1b8a450.svg':$f='&<bATb3V?$so%el,wEIwL!M$a_R(Ic^2cYP0
-+_=VtkTnq5G
]9Lh@D6K6GH(sVGneM;s+raV;M^D!*+KY*IOa=3Z`PmB;9_2Obq7yBg2Z%>bw,v+Y%P7|fhq*F156s0beWd,6`ay=p@_gN3Z=jciDmX_i72]oyww{Z~&Ut4M]*axgl)b!a1ju/uH<*9Xo
=
Ul9*$m_7{*MB>d@6aDhpC0Rt_1D-,/u//bLNxN!Dc[m?bh@-`9rmyGW2b(F%J[Qafv1UZr>a>F6JA8g[!0b@[fJtmTv4e%TYc-0#j"5iK2z=wl
*E@MT<:>g<H|W|2(rwB
g5j1,D7hGqj!`uuvXM9_hJa7+NN~VgB]NK[T3a!z#1A}OTmcvh["nTvNeTw$$;eT6q?q1?CDM!=<1SnN]QTt9lj&eNk(4c>}6hisx;^`kO(cefsRGicdC|"j9npLdO:cM^+&QT[{IL-Wu]3@7yXoO46wh/&9Sd+Vo~(7pzhcG()N3^n#?6MAX=Pd
]uX2I0XtRaah;-u5eKzydbLJw$UJhJ]
fe:Fe6mt>cu';break;case'favicon-purple-bdad3857d855d0af50fd6aadec68512f__d1b8a450.svg':$f='+<bAU6+V?$so%eoa6[DcHOAP_Cx(^^F;2nJOs!U09RM?Tw1h7?fDMh@D6K6GH(sW:c#bW`9DTcKpCf,1a1E%kD+5q+F]4B,8UB|ML]o,4O$F:-Sutmd.1l:6ly,?7MjE>rM=td#VYm/o}bPN3Z=AH2GFCrK4TIrcvu65o!8H3d"9V^}B7s3mnUM&Wx87ya:7X
mAh1Y7Wu72FB8FRoD/k0uYFG=PV;h%?KNUP%#GQ^
2w"
SkNgvI0*kTGqx))i0|m-597vz$R_y_,e<pwj=Y`"7[1E)$2(fp:Ex.$(:Op<o1CeTD_yBV
#FT:r
y-y?.@i`K.+Yg?j_Hk`_>f$l~LS;5RSh?>{b"
&+evz;:0eol+,pv/]hQo|q*A;p{F69^H{
F#X:3c%4:=Hdpu{k&lMXV@E/#GL#?#)e-WHQiGP@.%eYsSRXBg!n9tO*z&WP*$Up~J1]E18NCC{!iER:u`UBQ5(*#viS-0S(llO)OsT0bALBjGk4Bt}3Hwkvp!81Fs-^5[}2H/0[(gXX++<t3ZZ577
3fS]`cB;^5uWM3tX';break;case'favicon-red-fbad82e3d5b8188f0174c9ddef67372e__d1b8a450.svg':$f='+<bAU6+V?$so%eoa6[DcEe<SKeo.[BnWu^_0
-+j@96@+X_5GA4^m3%R;yn_USCF5vXi6B6jvyvvy?qZYfND@5~KR9wPw1q,+w{:cwGa2aY)<GPqWy/nLYzy>c3Au_/MA7,dc
}`rf-`x<FH$&bI]FGspJPra.)yE]$w~aKaM]on_y4%i2=`y?0`vYw6}rUy&B3JD1F
/B
o8ro30<1Tp4r,.qWnFpndQQq#ek`C+.f19/9#q+uNg4bc6H.9(02NJtu){yYINu`Uzs:%?o1GH"Lgtxvhu>9uq8)/:th8&TH,OT*]5<Ydlap!w8k>zUUDvh@h+)F+>T8=R`(;8*p2Il^<$eSAzo6lU8L_P_Yh-i#lD(4lV7"52"_UdLUTuCV+Yf/[^)f(~i
.,!Hkau}dsr
i84
>s)_:!#hJQ9:ex&|].#nB#/g7`Ds1xz$U+"f!p-.*YXigq8vUBF!9lVoojveL
+8ox,X;hOaXS*cTW+ODnn.]r,!3BBNvdJ~,brQumcAQJa9E)x*rt!$x~u(xCb
69OF`xI}1->oD?M:yg2R';break;case'apple-touch-icon-blue-edb1e64fb72e25db89ad6df0b7aca237__d1b8a450.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAK7UlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUa502RxeG0bX17btm3b/tu2bdu2PbZt21bY+6xqPduTSSddSd1zPyTdVZk+p5+prr51f7d83ixVOXXH55Yte6No2r25Qy/O/Pg7OB/4ykFO0cC/4FBma6qq2Tsmb+SVGe9/7f86zWhMFx+HQ5mjs7XmwITMT77LXe+R04WOdFdw+Ka1xOzL7vML7rTLTnd+RMHhW+Z01Owfz911izOE8IMKDl8wh6W9dPHLbsFCOD/Izyo4pB8z3EyG4GPJK5rTqeCQ2HgEGECGeL5MVHBIPAM1CAvh/AkFh5Rvrf/13STr0x/UHpmO4yXzn+7+3pev+VA0puN/fX/hDyk4fOSBkjPg96KN09KRN/KqbuBoSzsnGtNRz8NFwSFBDJRAeDdwCOsqS8/86Nu9gYP4mK25WsGBaXVNlpS8plORFeuP5k/ZkPbVnLj3J0e9Nib8uWEhj/QPvPvz8zd/eAa/54vzfOUgp2hAs6kb0uhCR7rXN1s0I42AN7dNDxxYY/CG3sCB1+wb66dwNLVaQ5Jqlu/L/XxWLPf+hvdOu8X5qS9mxS7fnxuaVNvcZtXcaiyI6IeDN9KyFW/3Bg6eTf4FR1p+0/ydWS+ODL3xfe6lsc6feGlk6IKdWWkFzW5Za+WG6YNDzF5bWIx1GQ7cUpXr+3DklLQs3pP9zNBg7plX/NmhIUv25OSWuv4KwFK7TjhsjZXic2dRQsYH3/h3OFLP6oSDP+rLcDAV6DM3jttjEu83P961gYRUDJ1wVGz8wtZYIb7Wn136b41bk0/phKNs+Zu+CUd6QbPAwmzeb158ZlHPECFVRy8cGz6nsdNuE5OP0kUv/gscCUd0wlE07T5fg4PFgQ3HCni5MCEWwrm8zScKNd3G7EEnHDX7xnGkes9occTe3pgz8I//HgDVAUfu0Et8Cg5eQxi6xT0wuQ9amNDSbtN0GBEOnXDUn1nytxEi6aQ42JEbIRo3hW/XCQfRDt+Bw2JzvDk+QgoshL89MdJqc7gRjqawrRzBs774ibWuWByvOzabg3hj4Fp/hGPaxnSJsBA+a2uGGx8rLXGHxPHCSXc47Vax0F8853EO1p2c73ePFeJO0mEhPDKtzl0T0raUM+I4XrVjiDhlb67J7vdrQp9+NyGdKuGwIXzWlgx3vcoS4/q3s4wl4mx7RkDlxi/97lWWuKe8cLw6JtzFIJgOOLI++6G1pkA06CyI9bsgmKRYiDdbF8PnOuDAC8bf7LRZRBuZwucKjps+OKNn4c1lOPDKLf1EG19beFNwsGTfGzjw5ug9oplPLdkrOEj2IfDQGzgyP/2+EMr6UrKPgkOkCboOB54/5jqntVP+NEEFh0sJxuQPc6QbL5n/jN8lGN/+8Vl54bjrs3NKmmCgfTg1Wl44Pp0Zo0RNBtri3dnywkE2q5JDGmikz4gcDrn81o/OksjodSE1ZPiykHrt4XwZ4dh4vMA1OTXPF3c+TcSY4ZNwOJzOT6bHyEUG2ginUxVv8Yi1d9oHL06UhYzhS5M6uuzuKPs00dWyTxP9q+wT/4hrDuUhGzF1YOP9M0jl3CuWJODdvUpWOM1oTAzUTwvGRafXvzHOpCmDb02IiM2oN6zUZC5L7aRikKpDKhfDA84HvnKQUzTQVKlJhpDTkZXPDQ8xDxYvjAg9G13ldKoiteYwu915LKyceR8juRcfImhoj4dXOBym5EKVmqxu6Nx0vACBvCexeH1c+JYThTUNXZoyKVT25bUdjCWUWkDobIh+elQYpRmOh5VX1HZoyswAxwdTo4orexzga2yxBCXU8M89fVP6l3Niqb1xS0/CrDSmC4U66M6PBCfUNLZatR5aQXnbR9OiFRzGLtkz9+x9VQymBRV1ncSzk3MbUQwExFefCK/YF1C6P6CUD3yNSqvjFA1o1vs5BHQ+PSRYLNkbZQoO/LOZMUJAZn5Dovfx9GiRz2GsKTjwdydFVtV3aqY3Bp63J0RwwQoOj2aCPdQ3ICK1TjOxhSXXPtAngEtVcHgnTZBFuIiUOhNiwfRTXKSCwxPWTaz6XEyVw9tRSS7gTFSliOgrOEyUYIxY8nBImc3uBUT4oweDSp8fLmIqCg5TZp8/3C9gxPKk3eeKqd1m6FDCj/Ouu+tc8fBlSUyAxAUoOOSQJjzw1QUK62w9WUgFN8IVblnESc1ropjTwIUJ9391wQBpgjdM6Vbu/uw8AZIxK5PnbMskF4ShheVcgl1ZRS2VdZ2dlr/l4/CBrySrEhyjAc1oPHtrJh3pjrzAAN2K6U2Jmm7/5JyhuhgFh7HG/ZNXmsDQpeAw0N6bHCUvHGrhzVibuTlDXjjmbs9UcBho7GshLxyE6RQcxoYgqekpIxls3UJcRMFhuCKSab901cCEFlLBYawt25cjFxwrD+SqNEHPGYp1WchYdTBP5ZB62rafLjK/4o0Aq+YVU9nnF+KqHxsQZE4yHh8YRFazhik4vCiqZkc3UxXtIFt90a5sIZ5WcHjZ8spa2bLJ608ZLqDvvLj8slbNbKZETUha5m3PvO/LC57HguV7BrDiqnZNmbfg0JNR3GWxkw+Gbva2jwwvPcgq7pezY4+ElHdZHfovXsFhiDH/Z2s3nY2hhNxj/qHdK53l2fH62PCFu7K52TChv1oVsCo4DLS/ZkUkZjdqPbSGFktsZv2+CyWsfjEtQH+mU49PM/aqpQsPLCRx/AjyNa2HRi8uG9eMNpXs88SgoNqmrt6nBCN5RW9NYDspt5ExhoUxnA985SCnaND7dGXU948OCPRcso/KBHtqcHBhRZtmessvb3tyUJDKBPOE/dsLgni+mNPisxrEq5OCw3D7z9VOahoz8dRMZmQpM2Pl8rycQ6oSjJkwMlEwlRaSp55KMPa0dZ+hyVqG0+nN0nUU9vhgSpTKPjepNIEiT7xwincZz1htY9feCyUUEVTSBDl0K+zTSfhLxKncbkx0wlNq5+3IemV0mCl0KwoOBGcuSJWoA0YtL7ck6mWXtKCFpKSkC/In3lw005rSrTzYJ+DN8RGUfhu7KoVxhZ0MWIUJSapJy2+iKCBjDOMBH/gaklhzKLiMBjSjMV0orEB38+pWFByUjpRXmjBjc4aCw0Cj3oG8cDBTVnAYaCx6oTiVkYx7vjjf1GpVcBhrpPnLCAdL9irZx3Br67SJEn2yOMWGPJRSqtIEqb4lFxykiqk0Qc8Zy1qykLF0b44mv0m2jdeoFcnmJ2Pc6hTN86ayzyl/TnKvmclALUEimYLDaxs0kdppTjJ48Nm9TobSrZD4effn500V0kCnqQlTcHhdzmSSbSLZI6Gk2jTSJgWHKPpzNLT8SZGC5XFH7sDufyLbSMFhxp1vtp4qonCxJ7EgKEc9CDn2B1IbALa220jgQJdmNBY8y2CRoK0miyk4hFEbn2oIbq/hgapq8Z7sPKnV9AoOMR1BkMjSF9XsH+kX6BoQj/QPpMb+uiP5qFF0TCwUHHIade/ZTnzJnpyJ61IHL0pgdz7yQFEskvlHTiEf+MpBTtGA4DevylLsJ/endulABgAAAGCQv/U9vmJIDuRADuRADpADOZADOZADOZADOZDjBTmQAzmQAzmQAzmQAzmQA+RADuRADuRADuRADuRADpADOZADOZADOZADOZADOSAe34f5izVe/wAAAABJRU5ErkJggg==';break;case'apple-touch-icon-green-3f24708e0caa38fd51e15489d00b98e9__d1b8a450.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALGklEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUc7syxR/Nq2bdu2bdu27fvZtm3b9nds2z5J+v2e+vGeOyeZSbqT2qv+GHRPZq3ZaVTXro56NBYklUz5LafzQxnfX5783rHxz+6EccApF7lFgdgih6C5oqBo1KcpH50c9+Q2f2oUozBVopwcAn99ddHYL+Of25mv3iqjChWpLuSITlStGp346n586aCN6jxEyBFdCPiLxnzB13XFaEJ4oJAjGuBvrM3ucC8f1UXjgTzWenJIm+EyMzQ/Ot6nAgGLySGgC/CAGbp/+cpicsgIlE/oqfETVpJDZq3/d26S8PxuxRN/+Ltltbm15W+f2/NpXZiK/3f+wg8JOaKkQ0l681BdJtBYl/LRKS2Qo2bLbF2Yik46FwvIIT5QHOF/Sg7QkLM1/pkdQyEH/rHmykIhB1AFNQUr8lYMjx/+0/KfXpzx4s2jb750yKVn9T/rhN4nHNr10L067LVDmx2wvTvuzSkXuUUBir008yWqUJHqhbWFykvg8OazOSEHKF/YNxRyYEWjP4tRcpTWl05NnfrF4i+uH3k9336b37ZxxXjUDSNv+HLxl9PSppXVlylXwYKIc3IwI83p+mgo5KBvii1yrMpf9f7890/pc8q2v23Lt/TU+IlT+576wfwPVuevdmWtlQ/mjBx69FrFYmzQ5MAaC5KjnxybijZ9svCT43odxzeLiB3f6/hPF326uXizChYstTskR3N5vj6uz1gX99R2/02OzbMckoMfjWZyMBS4ZfQtfB5D7PYxtwfXkBCK4ZAcef1eai7P06elszr9V+HqjdMdkiOny8PRSY41BWs0LUyz28bctq5wnWoNCNVxSo6+L1I44GvWg4/s9nf/BznWTXRIjozvr4g2cgRU4JcVvzC54DMYa7ze76t+V47B6MEhOYpGf86VwpGf6Cu+2vKktw7/LweoE3Ikv3dcVJGDaQhNt/4Ghtvd4+4ubyhXDoCHwyE5Smd2/EcLsWGavliXvEwXrlg6xCE58HZEDzkafA3nDTzPClpou3DQhY2+RhfJUbFk0D986i/t1VSSqa+XTP7l79fL5/eKRXK8MusVi2ih7c05b7rYrVStGa+vp399UcDXpBf6M3+9kYsl09rEXLeC38k6WmibnTHbrQFpzaaZXNFWMPRdfctXWZT4+oG4PmNuQPryzJftJccbc95wayqLj+u/7tKW6Lu1cfPy+70cc1NZ/J72kuPM/mc6cIIFSY6EF3ZvKkrTBerTVsecE8xSWuiZrQP3eZDkwNK+ODfQ3KjL2OQ+F3Js//v2ThbegiYHlj/wdV0m2hbehBws2YdCDqxy5UhdLKqW7IUcBPvgeAiFHPHP76qFstEU7CPk0GGCwZMDS/30jEBTvdVhgkKO4AOMiR9uOcA4q81tMRdgvEu7Xewlxx4d9hBpgoe4YtgV9pLj2hHXiqjJQ3y88GN7yUE0q8ghPQThMzqGwy7bqe1OBDJGXEgNM6JZSP3D8h9sJMevK38NTk5N/+Jmb6LbjKgkhz/gv3r41XYxA20EoWuSvCUcqG6svnf8vbYw48GJD9Y01biR9umrYNM+fRVbaZ/4I3637DutTDHTtvt9O6Ry7oolcXi3rJLVRjEK4wON0YRxczPnnjvgXDOZcf7A8+dnzfcs1WQyS+2EYhCqQygXzQPGAadc5BYFlKSapAkZET/ixN4nmkOLk/ucPDphNC8mSWqNQLO/edCWQYz7aMkj2ImgoR2ydYgv4FMGQlJN5lTn/LbyNwTy4aTFOQPOabOqTW51rhJYobJPr0inLSHVAkJnL/TTp/U9jdQMg7cOzqjMUCZAyHH50MuTylqd1bu4rnhSyiT+3K/OevWmUTeRe2PHNjs6pwKFqUKiDqrzkMkpk0vqSlQrEV8af+WwK4Uc3i7ZM/YMPSsGw4LMykz82ctzl6MYmJA8YWjc0J4bevba2IsDTudkzOEWBSgW+hgCdh7b81i9ZO8VhBzYdSOu0wIy84FE76phV/HaQo4wBftcPPji7KpsZTxoeC4YdAEvLOQIayTYQV0OmpU+SxmM6WnTD+h8AK8q5IhMmCCLcDPTZxpIC4af+iWFHOFAC77qMYljWLBVEQUvMDJhpPboCzkMCjBGLNl/c/8mf5MKO/jRPpv6nNT7JP0yQg4To88P7nrww5Me7rquK7nbPF3g4OHMdbus6/LQxIcYAOkXEHLYIU3Yv/P+JNZpu7otGdxwV7iyiLMybyXJnO4ad9d+nfbzQpoQAYhuZc8Oe+IgeXzK42/PfZtYEJoWlnNxdq0vXJ9VlVXb9I/ISg44JVgV5xgFKEbht+a8RUWqIy/wQLdiPETUtGu7XT3TxQg5vAffz15pAk2XkMNDXDLkEnvJIQtv3uL12a/bS4535r4j5PAQ7GthLzlw0wk5vHVBktPTRmawdQt+ESGH54pIhv3WZQPTWkghh7f4fNHndpHj6yVfS5hg+IBi3RZmfLP0G4khDTc6rOlgvuINB6uKCCT6fHzS+MO7HW4mM47ofgRRzQoIOSIoqmZHN6OSdhCt/tGCj7R4WsgRYWwp3sKWTRHvZXiBW0ffurVkqzINImpC0vLuvHf37bRv+GnB8j0NWHJZ5OTLQg4nEcV1zXXEg6Gb3bntzl5zglXcG0fdOGDzgPrmekcv7x2EHIz/2drNYWFYQuwxf2iks+72HWcPOPvDBR/yseGE82xVkFXI4SH+HhWxJGeJaiWKaosWZC3osaEHq18MC9CfOdTjU4y9aqlCh4UkjocgX1OtBLV4bUx5DQn2ObL7kfk1+aGHBCN5RW+NY3tZ7jLaGBbGMA445SK3KBB6uDLq+8O6HRa+YB+JBDum5zEJpQnKeMSVxB3V4yiJBAsH/muCoPsXM7Eoe5GeOgk5PMf/rnaS05iBpzIMRCkzYuX1JIY0wgHGDBgZKBilhaTXkwDjcKPlCE3WMiIYUMNPk9jjsqGXGRp9LtIEkjwx4dRzmfAgryav+/ruJBEUaYIduhX26cT9pf1UroOBzoy0Ge/Ne++MfmeIbsUIciA4C0KqRB4wcnm5Eqi3sWgjWkhSSgYhf2LmooyF6FYO7HLgeQPPI/Xbk1OfpF1hJwNWYaamTl2Vv4qkgLQxtAcccDoldUq/Tf0oQDEKU4XEClQX3Yq55CB1pL3ShNdmvybk8BDkO7CXHIyUhRwegkUvFKc2MmPvjnuX1pcKObwFYf42koMlewn28RxVjVU6RZ8tRrKhMIWUSpgg2bfsIgehYhImGD6wrGULMz5b9JmyDPZv4/Xo5EfNZ8ZTU59S4YdEn5P+nOBek5mBWoJAMiFHxDZoIrTTTGbQ8fF6KrIQ3QqBn3t12MsolwY6TaUh5Ii4nMmQbSLZIyGlPEUZAiGHTvozcMvAo3scHSlaIHdg9z8dbSTkMHHnm3ar25G4OJy0wClHPgg79geSDQArGioI4ECX5jUt6MvgIk5bJbCFHBrkxicbgus5PFBVfbLwE6T9SmAvOfRwBEEiS19ksz+k6yHBEeLQroeSY//H5T+iRnE+sBByWAby3rOd+KeLPn1u+nP3jL+H3fmIA0WxSOQfMYUccMpFblEA5zdTZSv2k/tLu3QgAwAAADDI3/oeXzEkB3IgB3IgB8iBHMiBHMiBHMiBHMjxghzIgRzIgRzIgRzIgRzIAXIgB3IgB3IgB3IgB3IgB8iBHMiBHMiBHMiBHMiBHBDxnn9eIYwbtwAAAABJRU5ErkJggg==';break;case'apple-touch-icon-orange-d2ed80dd1d30e6acacf2771c0d9e9383__d1b8a450.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAMAAAAKE/YAAAAC1lBMVEX//////v7yyqv//v3qrX399/PZaRL44tLjkFH9+fX007rpp3XWXgLXYQbbciH34M7jkVLggzvkk1byyqz007nYZg7+/Pr228b++vfqrHzhiEP33svii0j67OH67eL++fb//fzwxaTopXH+/PvstInwwp/++/njj0/XYAXabhv559rZaBHhiUXhiETbcyLii0npqXjZahXghT/YZQz228f66+D23crXYQfijEr45NX45NTz0LX89e/ffzXZaBLstYvhh0Lww6DpqHb9+PTabBfefTPjj07opHD12MLxyqv22sTbbx378OfbcSDcdSX89O7aaxb67ePz0bbnnmfpqnnjkFDmm2Lll1vbbxzklFbffzb11r/abBjkllnghkHkk1X23cntt43338ztupL44tHijEnqrH3oom355tjlmV7z0LbopG/rsYXvwZ3fgDfccyPfgjvuvpjoo2700rjopXLzz7TvwZ777uTstYr45dbbcB788er11r7ZaxX66t7YZAvww6HbcR/tuZH00rnrroDcdSbnomz01b3eezDwxaPstoz34c/zz7PdeCr55tfxyKn56dzzzrHnn2jfgjrnoGrpp3Txyan77+fklVn99fD88uruvZfmnmb77+bqq3vefjT++/jZaRPtt47vwJzefTLdei301Lzvv5rxxqXyzbDnoWvfgTjnoGnhiUb77uXttozeey/qrn/99/LZahTtuJDuu5TpqXflmV/ijUvlmF389O3mnWXvvpnddynwxKLssobxx6f88+z669/YZw/23MjhikfghD7deSzabhrfgTnyzK/YZQ388uv118DzzrLxyKjrsILuvJX449Pdei7cdCTghD3z0bfbciLggzzklFfss4jWXgHXYwnXYgj78enefDH56NrstIr56Nvqqnncdif78OjYYwropnPqq3rXYgfcdijqrX7XYAT01LvrsYTWXQDabRnWXwPcHI28AAAFZElEQVR42uzBgQAAAACAoP2pF6kCAAAAAACYHXuAjqRLwzj+1MS27Yw939i2Z23btm2PbduKbdvdwZMce42u2uqkC3NuLX5Hcf6Nqnve9z/e/3V0hoYEBgQEhoR2duA/Qm93Dx30dPfC6jzcbVSwuXvA0iZ5UoXnJFiXWwydcHeDRfl40ylvH1iSmzfHESzBitw5rkFLXoOcgAWvRg9P/lXfyMjIu/lPd//8aR//ytPDsm+OUQBJdv7DMIBRq75BegMcorEoWy3a1vbK/nvOs6cPcwura0YjhoZejtZUF+YeevosZ6L/103HaHxYLZoDMN+DJTGxEXQiYvp7du+AUz3yaOl3atF2mGxBv72ZE2h+/Os4qOqgPBo3AlWi2QUTec2aQRd9dG8V/l2nLPq3AIL8+Re+suhOmCYnnprci4NSqCz60GEA7+Rf1MmiG2GSd8RTs4VBkAuRRT8MSQOkLJL8uSy6AaaQmoaow9AKyATKoo9wFoClk0lOkkXXwwwP7lGnrKVwECCL/gb5AQBRJFNk0TaY4Ngp6rYs02n0EzIiAUATedb86F/QgPc6fXsUkXxDJOA2jT8w/e2xm4YMO7sQv0mSqQC++OUB0y/EPTTkO85uecP8iyIAX9pj+i3PTkNOKw4XRfRXvwIgzvTDhcYMKY5xRTTbMwGYfozTmDH8S49KNC8oou2wVnS3WjR/I48esFh0r00tuqUDzocA8dFwV4tmfrT6uCU0Wn2wvct/Wmj6YNtKQ+YKWSE00JC3CFnW3KEhMULWYkFDNCDAS8MCMtgHZhmhAelQcnOnE4MSTOM2h7rFSqKW6h7e1Omta6DGY9BGBdugB8wlVTRTB/+ncKZ3wE4H9oE2mO+77dRs3Rsxnq7OxoZ6m62+obGzC6+GVL6dmvRskiBe2pNYf7rIf3pKMizis5+rpgu+d/UKLOXFk4eP6Vzzjw99fz7E+9gUKJSUXb10uSabMtk1uZeu/ug5FH5SCxG4fQfUJCd4zRs+f+7R2XPnz8zzSkiGmpLAMYhArsyETsfyKCqaH9kFXRKOUlw0d/pCh7qbFBlNzgnXnFxLCo4m14W5wWVuP20nLRBN2jMi4ZLIk98mxUY7WNtYWSVhXJLX7dfvJEVHKxzPentcMlSlvW3F+uV0JDRaoXjlgQ9WVJaf+WGiD+CTGDRcXlnxiQMr51JJfLSqllaqExrdQkOKIcJFGlILES7QkA0Q4RkNCYMIbstoQKEEIYLGqNuQFwQ5Qt2uQ5gY6vQtCHRL34apEkIVTaZmryuDYB79Q9Qke/UaiLc4vpkua565CNYwJXUqXbK8vwtiOc60SRmxr3ECrdNWRct/XQD/JjhKCu+vpjPN1/x8o+Fo5DWIwLHNUPhD6cc3zAz0pwP/GTNTH5WWQKF0TNQQsGUr1EQ+f+EVFR4WFh7l9eJ5JNRcKRA3uWx7H3T5/BdEjlvLN0OHjVPFzohDd5KgkY/fkPDBdkY4NKnbZolpvLZMgouk87+0zArh8aOtcMHhgz3W2nuc7lecHgpJn/xUPknR0XOp0HL5qhdUvWvF9FYqTLXO3mP2qdz7/ekZSxbMj06av+DTn0nvv5/bPts6e4+HNORXEOE2DXkEEUqKacDLBxDiOg0YgRg3blK3tWsgyEnqtgrC+FGnAYgjvZm6vB8iZU6jDvGRECotlZr5pUG0sAhq8rIIFjClnRoc/Rkswe1rX6eLAlMkWMWxE8fpgpu3MmElv796jRNoP3EDllO1ejKd2jJrMazJrXRk/ZuoxNGsD22UYGmJm/bu35eXX9DaUpCft2//QNgu/Kk9OBYAAAAAGORvPYi9FRsAAAAAAAAABIY9HLkA16UTAAAAAElFTkSuQmCC';break;case'apple-touch-icon-purple-af0533cec3f6731aac6c69900fd26577__d1b8a450.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAKzUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOQBJz2xh+No2Cte2bdu2bdv4bNu2sbZtewfr3XFyn6v+/X/ZmckknTmnuqpmMt3ZVOXZxunznva8RcY6AueWDK39WN+fX935oye2fvFeFD7wlYv8RIXsgkMsNjXmO/LLrp89veWzd7lhoRqVaeJxOMQSoVnf8d+2funevPVFFZrQkOYChzdtpuJo+zcfxptOutCcmwgc3jIj4Tv2G95uWgpdCDcUOLxgicj84KoPpgULVbght9UeDukzFBlp5mP1h0zD0BgOMYYAG8hQ48vvNIZDZqA2YaEKf0JLOGTVertrk7Yv389/+i//LQPL3nnn73548+dVZRre7vqFP+QROGRA6fjuY1UdI7LQ9bNn3Akcc01XVWUaWhhcdIBDfKA4wm8IBxYeam79wj1TgQP/WGx63BQ4sLlAZKRhqvXiSOm27kt/bDr6jap9ny3b+ZHire8pWP+m3FWvvLbshZcpq191ja9c5CcqUO3yn5poQkOazwcjpp2Gw5vXZgUObDJ/eypwUHxHf5WlcISmot0FvsJ1nYe/Wsm7X/K8S2kp3Orw1yq5bU+hPzQdNdNqbIhYh4MV6dD6T6YCB2NTdsEx2jSVu6xt2/sLlzyfd2lv4U9s/0Bh7vK20abptOy18sKswaFmrzNsxiYNByUy1ul9OHwdM/mr2re8K5935kjZ8u6CgtUd/s7klwBstVuEIzY5qj6H+mpaPne3W8PReMUiHPxRL8PBVODoN6t4PS4px75dnVxHQiiGRThGdnwtNjmivgavrLlV5dn6ixbhGFr3cW/CMdY87RgWFhAZb10cIoTqWIVj+1epbMRjavIxuPL9t4Cj5rRFOPr+/BrPwWGYZdt7WFy4EAtVeLyKXb2mZWP2YBEO39Ffc2X88C/Ulfj8ZMf3Hn9rB6gFODp/9CRPwcEyhP9L9Q5cXk58vyY8EzMtGB4Oi3AEL6/+Xw9Rd0FdXOgsUZWnivdZhANvh3fgiEcSuz9eogUWquz5ZGk8mkgjHFNFe/57se1rD4oG+tX1wNl//Pf6ZO6WbITjyp+bNcJClWt/b0njsDJTdVJd7/39y4x4VG309//zrVwMXFiWdcMKfiftsFClrzSQrgnpXMNldZ0ytv+H6qf4tK/924/E9Zl1E9LLf1Ldhgc7D+tLWXxct/qVvkT9Ot+SM7rj61m3lMXvqS8cOz9cnKQTzAIcbV+5f9TXoyqEeiqzzgmmKRZqZZuk+9wCHJSe37zQiEVUHZ3c5wLH0hdcTn7jzQIclNHd31Z1vLbxJnCwZZ8KHJTp8sOqmqe27AUOgn1wPKQCR+uX76uEst4J9hE4VJhgKnBQun/5HCMa8lSYoMBhPcCY+GGu3EkZWPaurAswXvHSK/rCseoVV0WaYKMd+Hy5vnAc+nKFiJpsNGK99IWDsFORQ9pohM+oGA69yvIXXyGQ0XEhNWR4WUhduqVbRzjKd/QkJ6dO4/jCaKL6DG/CYSSMg1+q0IsMtBGmIclbMmKR+fjJH9TqQsbpn9RFF+LpSPv0u2TTPv0uy9I+GWbJ5q4lz3e7YwOpXHrFkji8lUr2zgvVqIwPNEsTxvWXB3d9zKUhg7s/UTJQGbQt1WQnW+2EYhCqQygX3QOFD3zlIj9RwZRUk3QhrZdGkba6B4tt7ytsuzJmGpKk1h2WiBtNZ4eZ99GTOziIoKFtPjdiJFzJhaSanB0Ple/sQSCfSSx2fbS4YnfvrC9simmhsp8aXqAvIdUCQmd79NNFpGZoPjc8PbJgirkBjv2fK5voX7SDb2Ey0pXn45/7yl+aj3y9kgnKshctYvShMk1I1EFzbtKV71uYjJqLtGDP3IEvlAsc9m7Z855Sz4rBtGB6JIQ/e7h+EsVAZ854y/mRuqOD9ccG+cDXvrIAP1GBaqnPIaBz8zvz1Za9XSZwUA59pUIJyNxvSPQOfLFcxXPYawIHZe+nS2fGQqbrjY5nzydLeGCBI6ORYOvekNNbEjBdbD1F/rWvy+FRBQ5nwgTZhHMhImDB9NNCmKCdJnAoX3X71THHvU88QNvlUeXRFzhcFGCMWLLx1FAi5gAi/NGGE4Nb36t8KgKHK6PP178x58xP62oO9ZO7zTTs3dZhrVtzsJ9NeSZA6gEEDj2kCWtfd53EOpV7esngRp+flk2ckcYpkjmd+F7Nmtdet/wkAof9lmJyWRwk535Zf/2frcSC0LWwnYuza7xtZmY0FA39Lx6HD3wlWBXnGBWoRuVr/2ilIc2RF9itW3GjiahpxUuv2qaLETjsN96fvtIEui6Bw0bb95kyfeGQjTd77erfWvSF4/qSVoHDRuNcC33hwE0ncNjrgiSnp45kcHSLaUiwj/2KSKb92mUDU1pIgcNeK1jboRccRRs6JUwwc4ZiXRcyijd2SQxppq1qX5/7FW84WE1HTKLPO66Pb3hLnjvJ2PjWPKKaTUzgcFBUzYlurkraQbR63sp2JZ4WOBw2f9csRzY5PsrwAEe/VRXonjXdZiJqQtKSs7R1zWuuZx4Ltu/pwHgAU8wpOKyEi8bCceLB0M0uf4ntqQfZxUUl1Xh6OBZOWHp4+0zgYP7P0W4WK0MJ74N/aKSz6R07EMfmrWjn5jBhPVsVsJq2msRzwMdQ7eRiG85PRMiWUXd0gN0vpgX/1Z9ZXI5yVi1NGLCQxHET5GvmIo1W3Idi2m0S7LPxbXlz/nDqIcFIXtFb+xBF1k3SDbAxRuEDX7nIT1RIPVwZ9f2GN+dmLthHIsE2vyM/2Dtnut4C3XOb3p4nkWCZsFstENT44k4brJ5QSyeBw3a77W4nOY2ZeJouM6KUmbHyeA7HkEqAMRNGJgqu0kIy6kmAsSvgUBGa7GU4GVBjmCT2IL2MRJ+7VJpAkicWnGotkxnjz9UeGSCJoB7SBNGtcE4n7i/lp0q7MdHpKfbnLG3b8aEi0a24Ao4kBGdIXfBwk8srLYF6vvYZtJCklExC/sTKxXStiW5l3etzdn+8hNRv53/VQL/CSQbswnQX+EabpkgKSB9Df8AHvnKx4eQQFahGZZqQWIHmoltxLxykjtRXmnD1ry0Ch41GvgN94WCmLHDYaGx6oTjVkYzVr7oWmooKHPYaYf46wsGWvQT72G6RuZhK0adLIdlQhkJKJUyQ7Ft6wUGomIQJZs7Y1tKFjII1Hab+ptkxXmd/Xu9+Ms7/usHMvEn0OenPcX26mQzUEgSSCRyOHdBEaKc7yWDg4/FMZ010KwR+usr5gUsDnabpuAkcSs7kkmMiOSNhcsA10iaBQyX9aTozvOnt+U5hgdyB0/9UtJHA4caTbyr39JG4OJNY4JQjH4Qe5wPJAYDh2RgBHOjS7MaCsQwWcdqaYrrAoYzc+GRDSHsOD1RVRL0j7TfF9IVDTUcQJLL1RTb79W/KTQ4IGpJjv3RrN2oUmVhoBod1I+89x4kXrO64+LvGkz+o4XQ+4kBRLBL5R0whH/jKRX6iAs5vlspanCf3r3bpQAYAAABgkL/1Pb5iSA7kQA7kQA6QAzmQAzmQAzmQAzmQ4wU5kAM5kAM5kAM5kAM5kAPkQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzkgDe9KFu6DmR8AAAAASUVORK5CYII=';break;case'apple-touch-icon-red-3191c4fdfaefb1884b0b7072ca253167__d1b8a450.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALAUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUe58yxR/Gfbtm3btm3btm3btrW2jWRtZG3MvM9TP/72O5vMTLqTuqf+GHRn55y+26iuWx3zGG+v7v7ukeZnT6i/d9fANWtXnL0AxgW3POQVBeKLHILJvvbOT24O3rBh+elzzdEoRmGqCDliHNOjg52f315xzoK0+qyMKlSkemySQzCQ/WnVxcvQ0mEb1fmR2CKHwJru/Ow2WtcVowvhB2OBHILp8eGmp46mUV00fpCfFXIY32coZrjMj6ePsS1LyGEwGAI8YIYaX+4Qchg8A6UJPTX+hJHkkFXr/12bVJ67SNfX9/3dGh87eOa2b3n5TFWYiv93/cIfEnLEyIBSffnKqow1PhK8YaMZyDFU+qsqTEUHg4sJ5BAfKI7wOZIDjDWXVZw1fyTkwD822d8h5AD2eFd7f2Fm+7cf1r/4QMUt5xecdWDucTtnHbpF+j7rpey8ctKWS/yxwXxY0lZLcstDXlGAYhW3XkAVKlJ9vLvD9hI4vGk2J+QAvUmvR0IOrPPTW+KUHBO9oe6E72ueuC3/9H1p+9/XncsV46cKztiv5snbuxN/mOzrsV0FGyLOycGKtPn5kyMhB2NTfJGjvyg78MC1GQds9Pt6c9OWnhp/IvOAjQMPXjdQnOPKXisN5owcavY6wGZs2OTAxtsDsU+Owcri4KM3pe+1Dm0WFUvfe93gozcPVZXY4YKtdofkmOxtU9ej9fnlZ8zz3+Qo+cUhOfijsUwOpgKFZx9E82hihecdGl5HQiiGQ3K0vnHBZG+rug398sx/FR4s+tEhOZqfOzE2yTFQkqtooZsVnnvIQFm+PRsQquOUHK+fT2FralJNPpqePPI/yJH/tUNy1N+7W8yRw7LqX3qIxQXNoK3xeQ2vPmo7BrMHh+To/PRWnnR8fJN6MjXcW33Fqv/lAHVCjsA168QUOViG0HWrNtDcii48crK/13YAPBwOyRH6+el/9BCFP6iHI4F0Vbgv7T2H5MDbETvkmB4fyz5iGyNooSzn6O2nJ8ZdJEdf6jv/8KlfsMREd4N63v3tQ39/3pvwSjySo/L2iwyihbKquy93cVgZyP1SPa+7cwdrakJt9Dc8vD8Pu394LO6GFfxOxtFCWSj1V7cmpEPFP6vnWPv7V6tXU/2dVZcuj+sz7iaklbddaC45qu66zK2lLD6u/3pLX6LeDpf/0fbGhXG3lMXvaS45sg7Z3IETLExyVJ636ERnrSowWpsTd04wQ2mhVrYO3OdhkgOrvW1ra3JclTHLfS7kmNfJxlvY5MDa3r5UlYm1jTchB1v2kZAD68/6WBWLqS17IQfBPjgeIiFHxbkLK6FsLAX7CDlUmGD45MBqbt7Mmhg1P0xQyBFWgDHxwzMHGDc+dkjcBRgnbLKQueRI3HwxkSZ4iLwTdzOXHPmn7i2iJg8RfORGc8lBNKvIIT0E4TMqhsMsS9hoAQIZoy6khhmxLKSue/4+E8lR//LD4cmpGV/cHE0sK5ZTMFjT03kn72kWM9BG0CqSvMUPTA0PFl98tCnMKLns+KmRITfSPt0RbtqnO+Is7ZNl1T17D7IRrR0b68+DVM5dsSQO75lVssooRmF8oHGaMK4n/ffsw7fWkxnZR27bk5ngWarJAFvthGIQqkMoF90DxgW3POQVBWxJNUkX0v7dRxn7rq8PLTL237Djx0/5MElSqwVQcLR9+Q7zPnryKA4iaGjbvnrPmpqyNYSkmhxrb2545REE8r6GeB22VcNrj411tNgCI1T2o0119CWkWkDo7Il++sBNSM3Q9tW7o831tkAHcuSesOtwXfWsRVA9XV2/f8M/d+UdFxeceQC5N/7YcH7nVKAwVUjUQXV+pOv3byd6u+1ZYjhYkXfS7kIOb7fsmXtGnhWDacFoSwP+7P6CDBQDXb9+1f71+y0fvtzy0StccNuT9huvKECxyOcQsDN9z7XVlr1XEHJg+aftowRk+gOJXt5Je/DZQg6fgn1yjtlxrK3J1h50PNlHbccHCzl8jQRL2WGFUMovtsboTvoxebvl+FQhR3TCBNmEC6X8rCEtmH6qjxRy+IEZfNWdP37Ghq0dVfABHd9/rDz6Qg6NAowRS7Z+9qY1OWH7Dv5oyyevZey3gfoYIYeO0ecpO65YesWJze89T+42bzc4LIu1btO7z5VcfgITIPUBQg4zpAnJ2y5LYp2G1x8ngxvuClc2cfoLs0jmVHTBEcnbLuOBNCEaEN1K4haL4yApu+bU6nuvJBaEroXtXJxdg+UFo62NUyP/iKzkgluCVXGOUYBiFK665woqUh15gde6FR0hoqaETRf2VBcj5PAWtJ+50gS6LiGHh8g5didzySEbb96i6s5LzSVH9X1XCTk8BOdamEsO3HRCDm9dkOT0NJEZHN2CX0TI4YMicl7jsoEpLaSQw1vUPH6rWeSoffpOCRP0DyjWzWHGXRJD6jca33xKf8UbDlY7KpDo885fvkzdZVU9mZG662pENdtAyBFFUTUnummVtINo9eDDNyjxtJAjyhiqLuXIpqiPMnxA4TkHDwXKbN0goiYkLdX3XZ20zdL+04LtezqwkfroyZeFHE4iiqdHR4gHQzebsPGCXnOCXdyCM/dv/fyt6bFR5x8v5PAEzP852s1hYVhC7DH/0O5KZxk7sg7bMvDQ9TQ2nHCerQqyCjk8xN+jIvpyU2etOQt19mYlNn/wErtfTAvQnznU41OMs2qpwoCFJI4fQb5mzxLU4rMx22tIsE/abquPd7ZFHhKM5BW9NY7tvvx0+hg2xjAuuOUhrygQebgy6vvUnVeRYB//IsHS9lxruKbS1h5DwfK03deQSDA/8F8LBDW+6Ine7GS1dBJyeI7/3e0kpzETT1szEKXMjJXPi3IMqQQYM2FkoqCVFpJRTwKM/cbMEZrsZUQzoMaySOyRe/wuEn2uqTSBJE8sONVaxh+Md7Q2v/8iSQRFmmCGboVzOnF/KT+Vm1AetuSfAvdfk3nwZqJb0YIcYQjOkLqQB4xcXq4E6g1WFKGFJKVkGPInVi62thDdSvL2y2cfsQ2p38quPZ1+hZMM2IXpTvi+vyibpID0MfQHXHDb/cd3rZ++QQGKUZgqJFaguuhW9CUHqSPNlSZU3nGJkMNDkO/AXHIwUxZyeAg2vVCcmsiMpK2WnOgNCTm8BWH+JpKDLXsJ9vEcU0MDKkWfKUayIZ9CSiVMkOxbZpGDUDEJE/QPbGsZc2LoY7fYhsH8Y7xKrzpZf2aUXXeG7T8k+pz05wT36swM1BIEkgk5onZAE6GdejKDgY/Ps6ML0a0Q+Jm05RJauTTQadoKQo6oy5k0OSaSMxJGGoK2JhByqKQ/bV+8nbbHmtGiBXIHTv9T0UZCDh1Pvml84wkSF/tJC5xy5IMw43wgOQBwcqCPAA50aV7TgrEMLuK0tQWmkEOB3PhkQ3A9hweqquCjNyHttwXmkkNNRxAksvVFNvuUnVYKc39k55XJsV/3wv2oURxMLIQcZoK89xwnHnz05vKbzim+6ChO5yMOFMUikX/EFHLBLQ95RQGc3yyVjThP7i/t0oEMAAAAwCB/63t8xZAcyIEcyIEcIAdyIAdyIAdyIAdyIMcLciAHciAHciAHciAHciAHyIEcyIEcyIEcyIEcyIEcIAdyIAdyIAdyIAdyIAdyQHtHp5xFOjNVAAAAAElFTkSuQmCC';break;case'logo-72a93d75457d8f61e73114f5d5de2770__d1b8a450.svg':$f='(]^+JbP.FqjXYdorFxH%oTmn1#,Na[(-^<}T{`+Ahl-RItQoM;{4bK}l["$V3F6U&V6Ey@S8#w=t>3kaN[hLow+fWEUH+K<LoXqyEy6JupFy-JyK4S8q(7tl96;KLl/F|,Cz)p?p(B)[axu/4u77-)nvU
R?vPex0x2ynqlE!VMsqy.7^Mtiv[hKzB^oh,VovqjM1XCS0v]mXW-smT}3TK7IVEL2YtHsc^Dne,}uyaN:]l/HJnieEbYSTw;KD$c_8p_B2y&,]pd?W+OvtUWi,FjFuW3Gsr=[=,k5ZhU;]w50sP*<)SM
tcO5=+WoZrY8Iq)IW=_gPo=RG*5hngIJV?j"daOWXS`x~L$e])]A/t{9it,:r%.89Z!;1rZhBw]6K6fQlvHN$Hw,QuiFcFpKmc{y#sO=!8QV,<+O&P/25]6vLiFL^ILo%v=7LZHx2=IpuT_qcxR7puVAY]-[aZk-!Hsk3@pU2?.=/khk7TY+8^U^mMe^&3|d[5+h9;Y
kr~/LPx3%=u>(#a3Hf@EX)<u
hpxoYBBVp`W(PvmMW
B#sK.gGL@Vd{:",35}yAFD8*Arm#eht>.nM#/VX$c0nfYn>@aFR7y~^p#M;>Hr]/"5-YOhURoN?g"zr)rf03v&=U+I-CNf2fyI`@2rCNwy$T>{3b.C"<mw^pUpNV.:1gW1HboUDhY6rSWb#t&3^ZZCWe([&88L?Tb:rJC{:,[0cUZh4Z?E>_4(eVbK+W4cj3K
6JZ,1OCPNi-r:-0+h9c@$6(OPFO,>/K_<D>?aD4|c[qNng
#]abQba^dg.vgT
jO4.nVHH3Y??RBOkYeEql7Z%i$fv:!`8=ol
<6HDyKdV^.GOQE<w848Z0)$;-[WOZ($QN.)/E#@[UhS3g@bs8$w@iRav#q,^!">riV0ad4mzAx-tm;I$7+G<hFV$knOjWB`9D:,!6.B`@~D~lLM@<M0y2w8SF<2z*Q?8suZ!O(%O"i>PX9(r?[=%/{TBK"Y5o,?wUbppvc%SDB9:2sH.!E?uV/?
m,@iTyWH"kU~.Qf,)]TyKwNyoX6LeQ(^HfM@6j
4o+qU-cQZ:uU]TVg=la`BE{x<YgRQys@]DNHkxs-[I/xZDH(tx~I,OKPNZ/@fA]-^.jOn630BkZbx.P^-,m);cooD1IAp.,``B4+,etGxX"U8fa;-m84^sKe*v>@/HAeYMWEKTQ)eqhf~:)bj!p<2bBA{<+-LC46:QPR:9CjzQATX#[YXUysw]
N.c{F{GlQ+bj=,TT-!C{[nb4XXv@IXBg4/"YW.M7"&I]1:iT"%EKDl:j![3j6cJm@H6qxXW2/Z3Cbs2d^_Mps>DM!ccnZ<i*Bk_oLtHcB*IHFOrym<(YWVBvJs)l@)0Z
=r:E0<}*va7n8dz1"9z&IAIi9Vql_/_GmWkv_:7+J@p:0<f]@QLtEi=rp`*wKM:5vfI1|nK.ne&[~?Dw9$GKV(o;/%`Hmip$>""Ue?0@$iQ%0E@-8u^"L:b>FLzv@>2F,<8Oa+M=?1oWnKWe[PvjmLPP1h}>?=m6-g]sv1UozX%`5v(*-1kTxb9=scVhWiuXQq$+!BPCVI)xDF&Cnc4ACZZ;UYX0(]s_GY!vk8WEz/4F"DLf=_6%>e[r;9[xM
*??SKd):Aiccqb{<(e68*v9Xya1
}IiKS_We9OJP11tEgIuGCfq=227bEC06#b8:]191/`0PF4dN3NCRTej;PMj)t1HQ
Jk-U9uH!E]5fjhHQ[+SE@:i^g{tA^Al~K<U3Js9&fM#B=^50#vEFbxFZ5L?Y3#pI^GKK[GYdMVSZS-kM<^><@^4f#(*V&b>jq3*^KjD3*Rj:sZUT"F5[bbKNE?X;A{TeBBBDDh+O^.lXKwEfA")l6+[^TWA?4gsuw|<
F:E?URQb2aF,p$7S90=|txQTehv2K|GQ]/#8t!]{/N<29Gp"TPCb9HnMc}q@$*7z?v`WcA(@>t%Q%t2zFCg.^la~3eLCq_$QqZ>erybXCLsr`Q)1Xvng-<,eXT8Gismd[Kh5k)PClZRUu<<uVag@F,=B#6wrEv$LS(Zs^CXo2:d/o%A%n/ZC1%4vJi9[DJ7|ViE>Q+(A:M5wRMExVg">y+d/OirXu6Z@>[`*:xk1k:,a64QavY*$xgkb=eYrj?%BUFsiBT>VTy`OsXZ8T]!(4(9)TVb`f![p</Q,?n.6x;Vcy,ezD|@X0Xca@ad["tI%:wj?P}^e*sm]oP?U`&OhkEg+TWAAc5FL5H.DImYqS
4fIvQ%7XhX2^!kthV*<ddA1ed`@9m6@mZ7)ocp_a%uQl@q0U??@Nf?_0.+DqepA/LGctQ1X(#=m3EmLVkn?I+7r~foFQN=BUF~8$nF"4
{9LE>C+$c%w8vSvNB?}93S#K4kkm/+t;`RE%e
;(Yq`=YE$3,5|@/mXG|%z7YbGWWmFH+IC;f:U$J6vpyr)hV7nU62e3PYwL~yj.31;lgq)EK$.>bGaY5`n6mlwn3@/u`vQ:9lc4J6.&&ga%^j!0joCA$LU&#g7trww.(CN=l2:G1CfPS:KH=d>Hfpi7[$X`,7wJZoJvRF?YKmSVzNW0`Q0FdOqAIS
n9lrNOU01c?:p/5y16+Zkgo}`M)D6xm>7RiQ_b%p%ocllip!0).myrT"w[53iGRZBt8z<.d6p9("_WYy1v;v.xBx%3c3&hfawJgMtuxeflyK1.-:wQo"f_z&)W';break;case'jush-fa8e95cf26ca8182bb368928ccfaca91__f0c41c24.css':$f='%X.mPb3V?!K0u25Dm[994[Zg@N#Q)YOC=2R_hE~4)=>cbdia55M!*B>tthOR~qe;I4(JE7nkwCcvDp&`dDJK
f-B>jO*,sE/El7[<CeY-WtHaF_.kmd7o@TK)f,bmf|yor7&?P^<qeg8="Q1IULQ7R}m#!4#2";]EMU!F1OpTIT56:qJms/Dv-ge06DN|n`FW&*;(q:eY(cbjkU?x]gUap[%36X.0y?`E_>T!nUPeLxWQNBQNxgW
`cW13j?"h~G+;>ckmN2+
!n%Sl8/pL[B8
M3BQ=4I8t<Wims^cdx*+69AM:3(4#D7NrL5JU;F-nR)NxQWs7{b*3#%wvQ^i5"h{.StgdiF@(/BD"LK.,r2{$e&b4#$3x
`jO"8tQQ1CKB,TYk$:TIGnJ]L
WcYUpj,c5;pfH7%lebNsHuwv44..jP:
oZb0YJ@}qOxs&)5?s0oVA*
swu
jafJPvJQCQ5?:;JN^klF$8EV7&
J?y"=oJ8]]rh5ZK31++0s/h[@)W/]t4{Do0LZsyY]Ui=F:=Lj0OiN0Q{mj^W2%j7&7^of",u?pagL&"iw=-;EKZzP?gdKNd/SR.wcU#s5@q~Y}(R:VF$Q8dZ,L)ey.Hq2O=)g/K}w)n3hY`Mh>::q<VI?VE1nU^-^D/qa9JwIPcYCppQ/X@!6ZZjyFhzsSz&E^ke1)2F[wC7uibhCB4fEU_/mdWy=C$?"]4Jab*)fpfaP{Xx=d3$rPj<p5Yo$$mqn$a[gdSRbtw^Cd:8IL9Ep@:NwGhGJP+=>bUa%5!)#P![lKsPx,lj]_TA`s)}ui[?@K5<"=yhHX9ac1.(pCt<=mY#,*@Ho8y`eb"03`0_6Wx1`zEp[UTJnLo&k`Z*%U;u_AP@.ah.ussF)SKcuGHm@)HJg~D|K@x[(QcKT7b133MD8{9lxGHdF&gk".u/dfBj;$`frs[]W0RtuZ<]q+TW7f)<cBALJpw,4G-PO&to>Pl>;:<vNY$aUe;BxF$7V3N~j=h.gDd*D(06YWAm/nioW)g>F(ql[`!L/h`[Z(V`2J';break;case'jush-dark-048f39c1bde3ca2b414902695e6944d5__d1b8a450.css':$f=',Gjwm6?!R"-YJmoGR`r@~cEv;#i.*-_KUyr[0$CF,>/n=#+liP*01.(73:+G.C]Ek+^-h&|hnGDq1:ccpxU98SxFh5MU%c+]DCcezAcUOWmDiL$
)yZA,ICx<`.i#E%U;lo*kf6u&LQx+!%1t]iP#G9;zGT,4U2"ha>hB#am`y1YU6$z!l#C%';break;case'jush-a308a4ec6c6eb975a3a83d10bf26368b__b9bb9ebb.js':$f='%hk^;xqDE.!v]yOYu=(i
Y)vs/y6g*Flg$jPml~(:A{"*hUQ?DHkp(ew8qFkv9z/bpcXBv}iImX?wn[G_
ekWbA6Z5:wmClJDXdv%Xk,VM[XSj&7tHp4O6
e
IPhUwoG~hnaCQ
#64+yF*h]Px#$6t%S+tPr1?zqmt&/1aIf^tZZXZ=xn$</gG`Z<`bt&UpdKt?BlePMu+lt<5jn>"1oUfa#3RP5:b5YX5vSXT^pFb}FNnEHJE?Yx!OB*yj4}L?
s%jO*MZVn;dqZUQ_gRv^N3Wbk6ZGx,V,CRmu*a}K,dKR0&J:bw.fhMePp,p7eLxPfit/meE*BE}5[B3iGXsk)yjv?I[qcT8%2Sv8:OfR96BHB"|Y8yaU7NP[2xUy57!,FFk(*u^]J;~2U^@mbrS
nRTcuEFrBQWG"4!p5sxm[cWdcNdXfB?+SafBL,_1>OE(]47oF5^6/N>13m
jYqP4J6|f/=j2h%Kt9iFB$4mNxZ!JVt;i8g(q$00gUsw?X=Bj<!fUM0KfU=E:Eh(0S/Rqdnp5]JWD$5xhw9$1ZhgH-m~KwLg[;,au(CHJ8RIkD=PireJw([0CMM.IthuL]X_yn:gG9nSX;$R[xOmC1D(HDOL8ql</<dqx+3Cnl2#t:1Uz)Aj({_V&PKrP2!w07?RCZ>wNY"YJZs2e<,+gO6yZ4aJkwEx[g,<*]JX%/7+D0+VpS)v`^eR(PfXJlvchIdqp9DEq_AU[90HWR=,ok_l(tSP-rn463#X=.ue$`bBI"X{z#qGx/fURd5z4u&Ua%z$"wp-UJGXrEvD%{0&HX/auoy$Y7tyG[x9UEYp=N,!%_
[[1ss?6yC8[Xoq4reQ%9a
D0tpLLOH8<h6&`rWl^+tPvz]VW$;_i|EiB-=b$,EnkZg>]2SK[jKsIGu$?p/aW+^/[?pSV]U|qH1sfkqPL]E-mZ#Pm`>F/-)*?+MhSXu9``mqq=r/M(.Cfbw60qPAv3L]N%Nr2l+Px^2N902&(LZ1[tbP#3!sS&h0UU;7^Q7i?lxQ&i=DGKE@t:nX[j$xGP^>ju=qsO]"sYUc_%8ow*F+$oZyB{.8+lcj%
_v@l5H**`KE|S(3j6n.p#69437DVc@m1O@ulMmmTA;X4]-k97z[`OD=NN[-|YJw5k$$aF}^Mu7-S,rUhYfd~!RaiaJ^7:Mtx<(]!J+Wix*S3M!Vi9zRV*[*bq=
|v`AJjv({D%6~#)#mn.X8={fxkZ2vq~t-96*)u9^g<uP&4DUd7Gp)6KjU0nSO#/bll3b9;QY^l7
)ag8n0kxJX1!xl@VG&C[J4F=|(RR]]fG9=o)c,$?`>6PjJWHgcBugfD7$3I$>%~9iaI;%I@^$>~Z}j4=$D"4su|[7"5VKd@KHieQzTD]TmBg;1*$b
5RQc
]6cxY?76VH2G),&N*xfDFQr>yqH=IsjhQFLrZZ)@F!w1YPtZu*q7Z"9Ri[tXhAw*e.[(SX,Ne`U@LaYs1s396|4V%+m+)FHg1zf%HR#3(bym__D%*A].4.5g7#gg)?v#=49p"<^VZh
/Q$
*^6D$4(x;MI;:]5UA)r:yBvG0PLb@()y{L("L/i-%?>iJfDM?1rY2UJrDTXbF]]Fod=hK:pD7dFB[8*l@)@)U3&P-=Y*Bj2e_AZpca3($VG/[XaeEevv+wih6u.=6w(v&Q4
#))X-9u0L09oMZZyLC;yX-g2J9i4.NER*)~ngc}aC]nW:"_9$bh"(2cyN!-IR/jPxs99ID:/NvKDug3RHu$`!^gS8-COR.cVg.z
:@qy<j=Z+jCN
6pKqBCS7IxX(0]?BU[A}`HkKHf
1A;_D(TGx4&?#OW)H_zTj`rR[ZgLpSQ.eC=J!5fMX^I%|"H+6y)F"g<v/esU*yf)~ux+;HL$DUQFEMo+$gY
oogH7,dp>v&byN9*u(N7WS"#:F[Q9M[
bZw%JrlNCj(%=Y2Fn[z*Zyk$PAua+*DE+iet(()+rs2j6y=EvIlnYqJl1(dIKdCdjV*ppI=jB?o7$i<"b(u1r5^gZ/;GH)$>RfV"inW^k:KQ/?=azOP"+C`@^6%jLk]CVLocuIpSQ7yRV&*t7%57A"wC*_w-=Q<-l9>1^CKIMP5TZ8D<E>`0uHqqk%HUc;d
#qyIOxl#IWw2qo{]4ECHQA6,D)6%3c7!T<>s@Ca=$XDwxO)O}yBS_p~>-aaG]9nTfQ"xiFYtx`WT3blywFj6QH8!@Rrt5aK!iBI@L*&h8yqv6?;,TlglSGytv;"SkPim1p[r@i:AeHp]C%q$wY.P$J,Dxf@v5OT33V,Ycjc^JS,eL!%tyx,!ePwKeP"`9+8k,09+m!x44)jHqg1hCsDp
GF=9_n
<8Mn`XKWS&LW6ag61510ssa3c3=U&DC4-[$>UHoD_C<]mu:L7c]7`XV46+~?4gcQ-g1M0"GRNSy1]rp.iusB"$^7Z-Wc"f;/+DJ
A<*o)(y_E;R$Yd<D,1#SY-h*rdnn}2IbvTWN(!~>V3%[cuMO,+#7L2f&ux}(ps3qynCJRu<+O8_wG5|*wUCg`HIv(HPB/R*xoc[fe52&tsQx#(pC[
{Sn:RbPC5]-:bD@@;SDg"(4h|?`eHA3"D,h($Ord$Rm$|%j]Yd&5X&qQRm/?+:y6?@`"p`qUWnLWVrl_~e<Sl;
Oc,aD{#Ohn>^ArikR0+D?,t[9@&8u"AnC!3U_nVwRH:$WF-DkUPHpm]^fS^U^,%(l38525k)Fo$kcz<DcSa2q$PwAO(sV~j~,J<cD|Kn;>-veb
%P7N0EoYiwL@,Nzc"x,82`c
z$z@3.l>gCN^KFryB;2xI0W
*6M(T$2[zO%dkP#:69RN+mQ0NiW/<k%_krlSDoLp=c/7Zj#:
SM(p@AaY+E`"*F<2J?NRW9PQHF?%t.
xI(Eh9,^(9{HXUx:lYvTc_"qGQU&4LiXH:i7s]7.UW>oTu-)XdTVrtw<8te,ZRgO=%6sfpi,S1gXvH.SU6c`NY0L9&p;86`Nn<s3Xl-lSaS.PT^Q<[3fz:?ek"tdzsD>;#3wZZG=&k|Z]h``
(I^m`:`1qy@]N7a$C5@C/S/:8*KS7EyF>G((jL02k6!HQ-No)XgvO:25&E[:2$EWs,H,M}
"S%RCJB$NR`7tpqZ```3(G0T14EaDB^yH`&c}"~0]R9$j<o.q<sSlkj&@E$btZrjHo%+W?$"z^[Qy:P%p"PASo^
a[Q^NSfPn1mdc9]1-_SRhc4x#
n<AZb6^JC+-nq&2P"GLtMAqIT,W]ytP)E2P:k-IsdM*WpWHf;bM(Bik-PX=6iS8I5&DkSt|5cBT)&&sZC),^f9G6su&>_izKeGP9Uhzcw1!ckY,_j`6@G0kcW;|UiwH;_hYA_k,cJ(h85%a=z*DLe$&V~nSB]Y*@I&#Fjl[Ce@.Q>C#Gk5BP"g4.tA@Q>D%AB,&)KlW5$I:?-TyBKYVy@+5
6=#wrMwZl2L4}i:Kq]uHa+R;g.b]:4mft!aMXkJ%0_{K6nU13TP!CTxY/A-YBT_xsMAW[QG$KU~r}Z(>[2x*%r*w@I4h%,ZYO?Y.>r7o,l"m.[To$>u&J.H@Gf.P`?fd|&pD.d3>|,>S#-0)dW0=t?GFMN
&[LJdz$R_=.pa(Z`1PAbi|.9XM%/e?Vw-KQlU]uC6)j`#Wt]Sh/],re%-JO)a"@<J4ntME@Or%#}S):h#@(l(E*X2P;AdmQT
maoC^0]:TF;vYx_a$C]ZbBplLeQQOq=_`HD"m"DQ|^zOG2aQ7lE"g.
#oM1jd@/V~jZA7+F"_`
$l^j!ajTxsD3%bU;FiR/4G
2mXc<xfM
*KVFV@x[ShPque_ME7t&.jpPuH.(3@:E`o#r
0nJUZefA+CxLF<s0DKc0t/E;Pv]z(j^iN(s[64[G0a(`Y&eCs2{M1d.-`H{4K5iMf2=/G4-^].
^7?cBN>:P1VMgUZ!U+nAJob,nl7Ok*y@j=Ln@CD#&q(TI}AoN*]=xeDMl~]gl-v}:^I6!*A%q5?m;#O8k"<Zn=J=
bBmtbQXE(OmWvw9%cMo/rX;YZ,Qr{99t0N"5Uc=F@EfU>yE1c@^(dNpIbs$nyGHxNbmt!UgX.a)MNX89Xp-EM^7@nUbLxM!,(?70{dAN9eXBoM/Q|U"a+@+S6j{]a@;XnO2?Ck7;xFlGkH|n{&D8acQ,6_i9)AKBX
!YL9Z:$@v>3ZxTJ?c;UQDx/Gzj5l!;Yq<)47ns%xx"5S>KL/8hwW7I8jc#]jRn%*^kpSL<<pW-1,b9ZBU=>5N(iX:A!3(D5BT%0:E=t]6am,(y~]=f0F0,zW&c{)F1-PCu)dDR_c`_Y^ka1]l39PCWqVF+hD2Xw>>mTL6`p+DftM`T&[/?4#a5ST_JvQHm<;3SsW#*PJAs
]Zg=hTG:M9Q;Z^6X8_Lm9dE.0SR0bR>Ef:[Y9xq3
P(*TK!./(g9myKvc^)Cy+RLBhThg0;DGMPX/_Q<.sp=<#.;s+RF1MK[m[q?fpB&sd"1p@#?A9qW%dq)9sM7TC/bveum,~<
9mI_jfoL*0@NALgwBzIJW961e=?GALSRNbNV5%jXB!pE*Ltf3]_yg((_#%FLZ9Ak6=N%f|VWmwEiR:dH>7)MR*_`p`v]J
x+Jfj9)m8yMio`8nuf=+
jVI9lM*Z~?<"rf`aI[)Gtjn&8&O5f3+S^I,F#U4
Zl<cC8#hg>=5m[c.fDxvy]N?P@Is<;CBmz!8<1{Kqfd!L*TR?Bu&wI``Wp&]d*3eXTJ]@s4nKp$Fvn%`V3hql#@nRZq+kWGQ3`S-Iq?W=rc<O
j@4T"7OqJ(ea+`|ps"LwV=SUSAfP
TJYq^&.5P4<O@nGJIDE+J9Ff1$[|e0UXt6+W%m$.g$v"`ga|"qkn*%MCe0F%5)y4iNRG>Ae.r!?y8FhfQ:^:C:Z?Dr3^=[Ed&(x<34T+ix:,po2;`zM.k[Fa.i+1yq`;Me^_/rsBDiBqe&)4S>9&.IrAR!6-2gEgNp?ZjWJXVK_*wjY2^WkNO>*@3`pM/(J~vhRsdzuw-@=5K=gBMfj~d;O2[.m=haakm_]|k;$_>v"tm9$Ulcy:RrA6fUku7Lb`74>^`SU-]%AMnQE,)3&3y3Vz,!"r/gWa+,Y7xsY/uqZ=2VcPhebMWR4iyX0!Gjem$dBh$1,%-2UGxy?ei)D^)el&)r^i#8%O<v0Yr!SEtmSM-;0*D1=?,EJIUIj;[YbD+&YS3yTQbRjN6`OP13O=%DwzFk;Wc
l0_fTzX
LrI8Bfu!g+hyv?@$Z|[%8YCJ%RuL.-+s7Fc1M[e&vA4Sqw-suq+y%u`9u?4HH2%::C1_NXjr<Z;qe=yCMW*MX)4Y49qj
xp/8ln|/8S7i9PkwM>+S[R!PlW-6oiXd)iwOJo}eM($PKTdFBsp=dlJ[<_Y!N<TS8xarFWypGj1k;]&urklsxC$?Co#*.?V>7eox:=|N@Dj6XF%n4v:b^C)^.smlj+!#+R>w]>&rOv!@d
+-DW*#X]
"y"Qq/-VeQU|l)yN?JZU8Ym%QMvrA,O!<4g|YOBHObK:E&+VvLLengHq@~a:kOM~#oaIR
k|MwBH
zv9Cgp+W{^?i[hx*/BAx>GWM$kbLSlg>L!xM3/fkLl)Z5R
mhW;wLaGc[Kf(L;UhWRhkZm6a30jxS?nkZ1y70h1]b0oc&k+n5@(uOAF#"HCkBye3KiZY%<q@6x@5P
E3S#Ff2^"eZt=A*Zthq2R
s2;(D2@jC([LF^%^Q9>G$_3aIuRgXyWKnE*j=07,Ne&^Ii,Mhb)a9lVINz%HlfNs]]=^C<#-O,Oro_Tu!`y:g<`4`
r?2$i7n`yeG*RTvLobYXLjE8bpC/wooR"x>Sv3lx]v=M/30<FIL-fU>+t?aX-_wmP1,L>,/T)MmtF6$`#GPk$D1aZq@OAj>O1wOT;l])kNmbBD|Yr2Q<Y^i6ZItKg^fa:w3#YpvyU^cAQ<9np8wBa>@"Pwlx6ksU7w;V/5RO92:C6@+wimfX7xBo{wq[*m#vaH5l-0>[sC{L_y7%;-E@zPk/`@7bPo49o0X(Q&R6J_3*uC-Awl:L,Oz%?BY(x]215d6!_&ewZq~5n@wF{Fly6[cwBY,l6gs8`7,mdJ]=V5_#|P_.A;@T&4kQT1Dx1L)7Y<nKaeJn<jce;YG]y^EkDG{WOkVy/ebFCV_AE%<O0mEE6)[<2R2pu$vHZLeko_#BJy3yxA:/z?2Url!diItT&0vNaUS,)q](24LVD5,2:_%R#(c/x1lP-oU(90w1IDEQ(H1/2T/M
J^L6+)2vamCY-0z#$CY1h)Bu6OB,CQk8jVr&_$ye=l3;Je00L2Xf*D%D4._+6QF1n|$iC6u?
8f$]0wlm:ab_KUD;yK"8?c2.aR$k*Ki6~4Xoe*^AW
dDY]+aGOz(9chnHQKPaq|4%?H(8-_:S<(
oF9>0(1c[Bl]-<wXEt9>|8Z<(".0!nv.!]B,UtLZ5r2h_f"?#>00V,*SSLEI}0N/R?;/7T}/l:"=mr}[sk#Y@wjD"rblWp>Saz#8fe<)S?"2X9P0.r
[5@+0VA/:K8VX`WUkA$L&_mA^V1~XenlpxyW$:40RAaiOBQ9x(,I2/8iC-?O9usb6Ct}U@x32
a}yN:&>ey&Bmq,yo>{UJz$-w(B!lXv=LWg=(8*5-5[&HmNNhAD1Nyw]W[~u*^Jv9_j+jRy,{]K?FF]"Nhg1V`Sy@6au7B":?:|k4T$;oMWwIN*(I"gd6u28R&/1_uK!Bp`mxPGFE5z-_`w-/A8-!"h<zUxlYZO?_nd&4K=0ey*nHJyhO*qU%a&]T3*a58aB>>)SV[kh;V-pFS(v^BG<?:LWJCOU?.+=M^6?~"rY(N(?V=Wx#kirdgFv3RFQM23ishPVx:w+K0JA;.i
T*XHJeuU@-oS`u_Q$0T;Hp:b-c!!PiJ61+FTU,jW0H~
aB-kZiRtwf
k;]hAyG4]b0HVr(4/a4o0MKT0KswN^9QiP<Vx$2pG&$V>Mt4,"17YgQ>)TxXf
!H23f0_e.Kow"Too>dM"W^HpMZrjr}-h9>A(Y$9Ul@3sk
K*W^MS$_&$(gBG,E6J+1&o]}PCuz?kuQUsZc4lb[tOP+KR7khJ+A"P-Z*Fa7GRiZxIq.vEIA0u>qD3R8,6hbY)Oq<_tyUc;C/=c5RS(^)VtGvPbox/eo:sdcY)WQP<#NlfYZEjA|O-aGXv6W)S9;Z`]e!_A6%oG.;yqw`ovOpe.`mY<BYsDt2%Z8:9b*D<0A4-WGQFz(tW+[p,D8SiX?)fHWBOZm2=nCw7vB63)lVgz"pSi{.o(>Q@<fwY/O3;lz,!qpET4>K&;WajT,aj%#Sz,/`dV4LF,NWt/Ob`xmRdgnMq^`VG`E?Lt{Zki];A90slA9O6n:=KkMb=nfQSErT|VNKiG
-poa-rcY!]N-QwO?6><5`4rqL<(H^w[fZ:8ji-
Th8)?B&xnN+FjZiQI.m8>G*w68gAFXy.UJ~V#KIFd$gEYp1>;dL$p20+|dqbba*WRfKCg-14|LwCTE%]U&(-"ZITc?m_;YIci+f%WBEoD&EbZ;EG_al"w
okHjfkB^xBG;]H<$vBS)jaeTh&|4[dkB40)kK%en8>1sqL
XxXg;:l>k:JYAM%.FHmnwUMWT`Yn=}$v^IwZ!P^?gKUcnRl<g?XYUO:!VnHdX%x"kDovf|95/xuFJ4kgGt1+HcX^<#>?*Tolbf0rjb<566PtS5+
o#<Bq{QH,K%y#RXK7{p`V;>(iNyY*iU3Wj:BcR50DE#rrZYp=;17_<e634<A]K%:kh)"P*2da:?hL>64$]xcs_vG0yA$K;T$K#:n(?E7ikokIp&WdqqmX8KVKsdpP|NhamYz<GvwLiOspvMQ?AII4>Wt(5_{Ne;Y+A/DtZC"e9k]5Y(|!3m.cV27r[Hqx;*kU>YK=e)1o>?CPU1)lPIgPw:#Kt9)i68XZ5>Vy`7{PB74(s0{LHc>AD4^+gI-)a6BXZ)FG[ex?$@{J`:o$T>*_&aRd~UWz$-ac*PQbG.;2L-VborKx$I|LPd:C<r9f)e8sc!;l:a"a_$XD+tfrYH+,uQ!O+[4OO#?;Z4E+e;i]H4["@:5ZR8LAuc(Ahj3hX71QOr0p$-7;TpgaY5/;CYlx.&JdHmHf^go,<
UAT84O/PD-/e7cAw"u+=7/f"266fl:)H}Ry/Te>w#vb=OfR,
u:Z(7cwOu^tR`SJ:7ay+!f9L2Nt`^pN{5vE&>W*ATi#ZOuMx83
L9|u@:ppP5/P;eUwoTY`r*hD]DojRn?Xi`2ydN1eDXzi
$g2Oph3Lx
<%v
U_KHYsr.EK9wThIuf|
sW.R=>!MhhBWe"SPkS^A?JQGt7hQ"]"i=EE3S5Dxd_pJ^
cDwq0gho#8z2kacu&7o-=MT&0h)b4ZhY/Jn=GwnFkZ<G}$l^E.Y`i34^
gnOr*d4z"{p4!3-O4(v-lN6wAv6U8;1Zbw.6-$)|8^<%=hdvc,#YCl*~-p&Z2)^@H"NjDr$=%6C#9+uYjR9$v;n"Il!]=jcfDaKk#D
:6ZU,t}>JJ)9g)lx.*(/IHWF}blq+m:
q>EHn0)26wPx$t,yqZ7[I:DAgp=h
cKc;%%B6RHCIj{F#uTt}8-$>dA@2RZG><4R,UTtAgcR3g^AWP%0FHu#+%k9fAMMXhqWuO1,Ki<I6T]tYQoZSoaMAB4veR1;2LxHBY8UMbMU@aqvZQ@N3ZiYV6h=:t2$Z0[LJvISGw+>gCI4g:<Om
uLXhV*hvs"-96E/>h(/f-)?[a<(?IAM7qHEe`xfx2k6C]2$dc#>vOn|p<0nUdH|*|LTk$vW6RcT.#,M#F:Dm0NP%kE<?s%
jARzY81biwp6HR24tHLEjY0`@Eo*h!/e^?DFi5?igZ4A.1-v^a&xc=2Ynib?oIA3GQt.>(Q"LRPhK;nuW&S:0_5UA*e9?RYJXxd@eF>`_et,IXeAk7FJauVFS:VrVlj%,TQI,nd>"lrvp~1B<YET4e^=to*n=Ep5VPq!8Ke1R{#!*1J{%TK&9o`CJGPm(![cKGAsv0b/`1W-w5
b^~Ng?<v)6HLul,$lcPR~fS]?ZB`qI@;y`&F|?S/]uejD0IP7AX[uwB#4v&G_L@opyymaNi7&G-abpJM&2fQi]y=~$G=E5%f3GIt!>"<(VkZ%Fo2(rNo]QRbM;"i<IQ8R.bK5(3`_wfAarddu*EH!$mV`:6d+2l),7}VnR7P^aJ^nY>="O4)lYYvNACF%>lNF&fhB-y]KS1Kb!$wn]}g:J"jI(Q/6q-ehP?,`I,x7P:=+$w6>iYU5UK
,Np66&vy+^n%%Y=0v#Z#"_8+,$MBn^6m8MOZ:P"]~@#e]Sqj)0!1lSW*R:zG`<kKNl{Vr!-f<koUF#Y!lv/P,EeH1L/mz5|aViY$<:@NuSt6d^ec@LafOW][e0>OaY`Zp)8A4]"4UMt
5AKOB5H(>yHo@ypu;>}T-+~?ZOqB(d4Wm&<)^#<]Ikf.WCWONvhwj;4/j-r
bxIF#DYh#f;/r(ICHWmIE`o?.]]XrnNv
GPxG8I6Zw)Q`s`$K54`u>-NJK&
K>@`nRFV8"@3yXMK!
re,Oql}]!Fw
tq^3Z+_ZU7t[*-^jE9OsT+~N%s}/;!x;Hlkf,q`KC^lOTfRx,_YSL8bf#+PWSlsQPxV0[In
-w,7XkDshoM_2x#0+Ur2"y]L/WDF
&aQR8gMM
jZuV{3/D
>5OPy+.:_lgV1q!EGx>e9[gkk.P+`iA8_opaFAk^_f<[-eUF,-JW=-2TBp7kGKMCW(,|cxB%WP"w
RTgI*yTIQ)J>9oxJZpI[fQ1y<eQWk8ri;#8!3Kmp716U-vH@aLX^(qttDA,+,gO<c@!Teb19rc:`$M#73By[3;tlL&T,.69/DESM#n5!zJM0de+0rB.,w+`fTi2
YmRNwV|:?YmpV@Xb^;q?)D:1nOy@t/6$4#*ef0OPn2M(9N]M{_?Z]$5^okgs/B`J9m]*I&N">CICi_l!eMApi>y!kfS(D`r%Ht6u)IU;iJv6x@#QB+Ncwh+jjv^*g/kiX8d&3Z{W%gEH.;[
RJ^Hw*Vb9s{>k17>ST/
6*N!+kxjJfN?*F3%K"G*qMu-=1#>>?==D.gpis7Y)O_N.cTN75ZvR5z+O1Rj]vy_omo]uX$Wemu!}e5eJlD2!O(2+p1E}uwkTtt&.i/7<Lts>uOJJ4$_mRMiE:k$:Dt(xi&4HF5%Pz";e:q;3/YJ]Pa=X+bPZ*-m(^&!Mi0j]N)9@%FxImM""Sa%4T~XS6-c:kFBWAk$>2]H[*RY8FO*|E?#[Bg4a8fXU/n#oA%)Bu@FNPt0kY}-+N(^P0n+6=;2J1_X[F32gAGlHs//?i^.Y*+ieQ~oZ2Ad`0bVEF=wG^#6vr?r1&|<RU.4*uR!T56Ot@|aZu_208tiyn3+{I2=]?>FkJxBI(}GGtZXXME4oY(Cfe(OS@50-%yD]sf`KJkV0"*NDwd`eL>X"pI&Dv|lk0ys?yj4Va!P<r{5d?r&">g0>,_
FxedC9LjH-mr$2Sjf5VehM&,)Prm*p0#.8(9I`sWW^UMJ[J]]K*`Fn?#A>3H,ya,6Ht@CPiuh`es^59=Xccs;xL^Z<(w664rj=qC
ZIynrpz(q3]r=r^Pmpm
Jq]+%wYrSri-gD`C$sXz_p=)dPGn#1Wnt>y*%np7ui]rn|Mt:C@Wl$y}hU+"m3$lq{le:0.0XckHGww-kyGKssQ&MW2O?mLN]1fMu-;DSv7ge@AAg"3nI~+WY][#9E:{SVSRWgJ^_z@8)6%u(;
GB.jV_djg__tQ-|9a[%l.
FP%^JFK.bO1YDG~NVv[x@VeNB?5H,M&CvGi>_cuF[VDr[gBRV#`XV:I9|CzU[CS+jvPT/?;!&^r2T:&N+IJvn&hMK80kM
lZ259t[3SFAJ%QrCel:tN/$r$xcq!JKROY0J}6OS%B;#LvK&(N=az`J!W:nOskW3But:!^rumo~_blpW!)xXic8(j=En_QItOn5xx-92)70
ly^N>FSdgb7EA]&b4s/YMWs1!lorT!Np:RSyOVy+1CtoXZU>#S"a.%ppX4s!"b0e_)O@"956WLW%C,ofNU,f!8C)l4s.Pcy26ZeO>872,S15hj<CC]JZ8`F8O13Lr,{#Nv5Ae5
$]U
HhJ6i>)Zn@07Ei[G2]:oa>v`mC+k"MHQM:L*(-t~oMsr^Q?IXBRx7TVDO))%>V<,(E@hRgcjB1L[!@/|?fa<
JSPw.eCPI`k)mX<c$l!Gh
)2>GrY|<Yn2q?y9bG(OctY9gpSuW,pDKaIR]YvUMx2/0v,+Y2"EEHl#[dw2j%]nBOS4vKi#?tMg"trkkZ`jA,ZdskxGe~M><
V[Zq1;k0,L
MF(&Et*X;v$-~h&m!$]
[VnVE"/*.7]4z]z<e=PGOAmn)K&TG]oDl;;+>]1c9SenQ[FS(ccP3IUk,>+Etdfy^=BB67+GI%~y:0eKu!?i.A)B[j}7"cx=8Odx!Dwji,lAGC>;/NZ=uMdmEbe3[s_XagPu6dc&>Fs
GJ(GvDnG=6xcNYajvbNTYtGDQxA9EyJ
Yc3^n/=18l13ZJ;w)`Ue:L~8l%$Jy[@P,g+ueJQk]T*v-7"5LW
VnNcacTP[qVoTH
;&2!v<3/FJpDcO
?f3B0k#~px
MD*r>H
f&B:S5](m0h.L;n5YMd_y}[8xXNT^&xME:>mXT@OEBg<5Scq71O5f<XcL&z$V=@Qc*cy4lM%32WGM8SWntn3vm:`v2wt;FBnYfAz]1`W4)]ItMuy
YuG:hoLZ5XM4W:Hvu6?R"+zB<(Rbz@}DBWZx[*@)gJ5k/Q3wY_*pypnA.hkQzI2[}upV1%9tQtlmqbbrvfx[}13Qt<aLK@Lc_X#4%<oHJP*sYlEn+^
t$*a8%rZg3`OwiGw-K!@^ZwxFKQy4@%*a<]nc2e6-4Rn47SS]9cC=U5(@IE&pJ5|g-hu&OF}5p^3evjpGBvR?j+4AL&H,_uYF,aBFRd+N>QoQ}>D#?-<Fzv^hubxTexuL:MZ2SmUP.oSy4CEVrnP.AU2M2&lb9"v+*uw.{[R`wn1"r8xnh_8GkfWkR1mb;#?v23RJq:+);D^ew1Vl(!cgG
NTeq<]yHQK4<kwB_>m?h
I]Vsh,#9uA1HDk*#?n_j^}2$0GyYl=ovIdHX40Hr39;eNNKyL-tSIUv9:4J(*o>J;XuGElH"CLc}"p#=KQSU<6nT:cd?!VZDB(mpR+L*.lIh2]q~^bY8ItWG9<?{Yan*#oJwA7I?f%PD0Mp"dB%d-"#/A$9C1lF9#8S]bp!t]}`H97+IwoNry9C6Fo9,-Z>@*HM0jqBs;JcUBO*8"#Mi$)Y2w^r9v4[@U{?.&#[KkzaV8YNa$ev@h:^N`72k44@sj/,tl;@0et7p_*O/o*J%8T]T^,Dad_"`V9Kg+;YsUnSg
d)xApw;;7^*5Bv
LzON*q2IB:"s(]r?+~hP9+jR
s9>L>6Jlz1=3Qov,iwVe#$k2s8*h.`@g5T.stI_d
-2"K1go]L0V("|,O2{SS6[yDpI?jw}GGi]#URnJy_>c![9]^Yarb9zjE3BWl_rM{=x"5(1T;N.c1M{D*DoHy;:AgJl40kb68hVqfn|PVFpNU=VdcalWGdzwtr[I7kXWvhq$[,_Dd0Xmva*CZ=LkMr9`6<@
MGSg)hQdtI8AYu=4C[:xe<,A)_<Nm?BVzea+z+TIgqx^aZH*2]njr?*uavz>)W`r+S]ZA*0qNL,$X#(&NHPC,w##IZn8By1-@09k_I=dSfWkG^"L@RVUD@U(b^F=P(T
h9j:L5`Q+udyEw$n2:
5>tn%aDh/b#Obg&F7&Kk7:N{ta9;/I!t9$
?O24lf.dfP[_97i*,[u9L%:3&7wCw]A!RmTf8$[hue^Hq7i!4V~N/j5f6^zNK,!>L;rj}+p2Zp(2EB:ux">+pa{$s8_$e,(36=J.>uzJQkRtf
@6XrX/>,xKbb#K:iY8d#.TZWj-@b3M*Y@&BR}>(3y,a,8GM>?fU5li92fyC/Yo|<>&~J:/*rL%CI/v^V.3y!=cx
|LJ"AOz0lIg(c(TB#gByMV04{Tzae>
:_^z$hso%.N8,Gg1xBMf;4v.+@gO)3VqlrMf%:k92:V][j&JmQTl>x@Y7<GVOh^o&:w<5`aq$]vhW5.N%iX|F:ON?&9HW/8[oXJrQ?<r6ORBJ`^zAOZk<[>(+{3,I$QURIW#AsP.U")xjp,6!E9p);,|`}1fMDR,;B.0P[Y|32S"mVg_/:m/+ASX5ai*s11g^gCgwT6tg+0h]I@bOkDz%8:vYZX!.guU2&f>##!]8MS};WRR:p<1:z?+Z2&`4+)~Q"75"!8GYm>rHD*!M$q{1_"C+UXIec!MH=cE>)w/p6R28FEvdwVTO?/hiW5,+5@-u<plo9Eiz#jy(z%CZl+;,DK{H=!|$kj2o%v"eJhU#:onWz8DDoSmOf$,>`Q+Br!GSru]xI#57p.3Pe_K*cxx1S0E.yCj%+,J^]xZ:$iZ3u`/E|qYvA4{#SY[qv%/,wDdkocwY96J_YPp@r&XrYW4J=.iRLsgk3`Nm_IE?vb&"*/@qBFEP~WN9(
i0g?2]~ETZ)UW
>rR^vI{Htq"S^2VM^D"[bNTA"+bcW[Z(Mb2a7/5k1hS0~v/.2D^H3?LIVeLb5jt;YkpStG?UVNT1ZkSfpvMVSA}m,Z4ZR$BGHy>;d"}6[m0Ilj4i?a6<fb)_*SDC791Vciy(%<)NHki]OZPEKgB.phwvE8<(^SejEa|O&:HL17el
Lx=;F6FRgdG]dFM
G{_0?e0C+EJff>v3(~_>"_Q^D_!c_Q`!p]mxt6J*+pH?1PQKZ,n?G}&V-
B0xptM=I+0RnmlX5vs@_UxH)2_?RoZ-PvL8Ait#r=YkYE4-i[O1#=p>L;b=h
B^ecm,"Ul5l
}YR&+2CBM&J[08fy=x3r4[cSqtuexwfsE]*D5QsRS+QmZq%>cMZ3X#p["vt!KX2[nQyeq:Vomd8Eqv~V&g^cY7d`{R7q!sKv;
$T{a6^Rr7/:@fFEb:(CWyqm4skzN1rlSCAms|b$6tMQ?{2Kn/c:<,l|4&]`,be9vs@beu>1W7s?qzfoIWWIUa$cOlv<VqxZ._]_Vn_m,OJ-g)=M;O40<@lxb~x,YEoJQb86$BHfSUdsR>RxmZlw5
b2fUD=
#<Qja[pPI4JK~qP./OZfX*wa!o">mEwIm+:s9[Km60
8vUF?
>YKv6Krwp_
8]PNNu=*C^z;K>IW9N|>IYcDE[{idaGrwtEXz$[&_*v>O?>km<RCOFXHAE6"&.D@w2s0/oc"F/^aiS22f8Mt#_&F=s~k^T{-p,vFHF
*~.TU:&jG//O)Vhs3xf1/--WnY2[$!ddfRX[];f!GOO3B)<j<Kl`/@U*/Uf`Uq,SJW8Zvs`j&:ZA`N/]
{3B2_.-9{Te@^;:x2)^2=:mZoME:,cCr4rm;
d}&yY3.{eav.v-7E
N>j&xno@NQI3apI:VFCc,3`^nu`r^VousJ*S.*[DM05!ZYEFNl0B%GiC+`1kjL9h(*ItJsAfS(fc}st?e9]r7lA%V<
[GR>)L"X$TUpW%*CL9%sKaR#pvS?Ckb
RwmHw*W<ugwO8tCPT?p9(T]Xl7`v=!C<Tl]tj]f?STZ+]*Z%"A$vu&o]7sW&X!wl>n@%s[1Va+[O$1=3>5A@fXv)Kk$_/gb
,=BSAN`%&H?EYG"XV2-}n,8@*GTYymZS?AW!M]!LRyqw^BKZR9WDMU6@i2rleIDGC4?}@RLzOTo4EX?O7-2MK7c2tQ/DOW79,n/&EZ@jJ9!+4+)T.`(gw7.k#9;h8c(nl/M]LJTD7$L]28R.So<JduS@FOw=iJo}8q1eJU.`,};hBm6-f,"AQIF"ey%9tFG~i:$W$ucy"B9FUjlE"3.P]{ou-60@yH?2#WpyXFo&Ucl*0d<2u>%u`[j?ClO5F.;|L=e^Mq!(yV#qu.kE"LM!Zf1Jx*YC
54#%y=JS(BVU^x^l`02a2fqE%QmZ9WE#^,/pn[/[+UOfxJRsH]{#Q=Sd}N`Fs/.ECcG2(^~kmHqwtyLO1
1/a.zS~Nur6sZd-Zu]&&nFW<05B;:#XnFyV8r/Go;o|"07P+UWh,P+0)wKq1W5cQ%(ut`OP*$M}:?S,V$]&V#PtD2;BPhQNKcRM2j%oX#)=x^igpR(GO}gWvX<)
v+$T{rw$`GY)Y?-o(Av#JrA9KOle_-mSm11859}qsyrUqE
(~>VY%Z50-R/#QQQD]C%$KdZ3Y1_+dKGC&!1SQ2XeQ-[,1nhD+P{suOA0|DoyW?]kcc&Y#p7aKRJ&t$H8LwG
VkFXRqN8^)(l{Q|GnrG?JW8q@`
BYb6yIxk,JM1t{od96trXRTfc6K{wb(_52`*giBhf),3g|@rDjLg]NGts>h[eq>S+RQ44Onb*k2)O}jonaOs]y%0u6i48%v8dEpqBGDJn!!Fj)+7xZ/w+X"yvSQ"Q%rY&nY7BMlS0wbFEe)MqpFJe*FTCFvnJ9-VIJ-Gm
<jiW6"7!=Q_ZZC
*rFF=3XB/x)[]1?fw[xxs`R$Uo{N@gdsr

L4m*[h<y-Ce(kbK2`{6|LgRVyYI%D?Tbh7d:k/g^s&KhGo$<G::(KZ-aGXa?v0y{!Z"&b[<!O&pI[Y,r6q9`v$5TdMf7""i{%LF~DQ:rRP7#EDG![*s_?("%-Mp?oTxPoLy>tqqmr14s8^P5oeYf!]a18@`Id`28SXVaF`a$E(ZxF"DXYhc]mgp4=C!?ixtjUAiu4$`El4jn_-i%XP-bVt7t"
MNlNSSw~[Ka{XS,l]>0)P5y@oY/)FXfi]Q40$Jf%NSi=$E]-tLVbY_o^wrIe7i585@?%Wou~Q]rF-VQXH4H
iveqJ(5T7GK^+<fI
&){!*<IF)1_Tiw[N:>bR(`YfZ6N9L5;&x.S35?]grN0JH"8&^6S!_lA.x/4o};Yy=fniY*sh|I2r6bkM+^u^"fA$f1CUTPgdOh3t<N69IanCvQh%<,,f>^@</$Ol9C@];^jw0_}e31}5HXKLPl.")yBwQy.JI2L%H`yZQU^uRQGD94|j^GU>pg|8]Zn%~"f^?ogo3rMO6E_t-rGAVfvGr^?bC-T%%Mv@|e-,3N016-vqPahI~A/P/b>q1+z
5D55P"0[v0^OzBR($q`u@#.HB?I1{mK+.&@,++2iX"+CIwu^wU0f.BS
qwvUdlVOB@h@LyM=jHBLg0q*#v)E!v-`QSI*N9C.R)&<QvWHr^WH,d%"3D=*|IuYndIWyJ.wlYYon78EA&|@&DHc&m>VeVruT?w0)$zN@`~>[D,Ee7gKtY0!:yt#]>Yw2+a>`$2:/$19d6vXNMe.)!-w)p,)/Pb@^d(H-rRi"6~4V,i(>Y*7]%<,],h=R:D>)p7N%81_ZP7=I3<0ap"S+TjN27P(<Uq(j[Y^XW&j@5{w8#*n_fs.D-C0P.8[l+kOp"EX-dnP[f?5EIi%Ixe`4Rs2Z"b7D".yN=n<KcsO>QE4yVr$CV|TLsO@i

NEM>2n]hA+xY7T/d=y<tXQ5YVYBp-F)Wfe)GpsFU*^@Z6z+UVR8tfN]sn>Pt_T(hnfTi5}tKjXsegu>|o8p*oiNTdSw[e?XFM(,O!IDGH^v>[pR@a_VDMi#5%ae@L"5]ipYsHU;B;YEa%*uWn~a1@yuG!qM{,j$Ejrd+VBci&@*vw2OVxf&.yqRQo^NDY4k3.jq&7~/r>_yBF9JdGA;_5$C`jb6U(Zyw>;qO7vdI8AwP74EiPBorQ$+@;c-d#6
&-*5Lhd[qG&m`59AhGg+I"_9}$aCAd+Qxk/>q4,%:u&K_EMV(i4p]P0,sN,rIee-#Wdfq&3BI,"R2.$bTGi<?p]3o8-"w@^*"RY[35V]L&~H"=/BPWd:vcvK
a52ua|nrCiRrW,WWb+Mr=XwwAJ59[;@DeY3m-EV$TlKyq&!Cu(DxFAm/UF1zy`3E@jYcV[XdjW[4wjCQIbpo<=CsMU/AI]c[C[+f!NxWY^LuLqr.q@aDp*_Mbkac6*(#0X[l01Clq$llia8tq>B3c0i*XW%MqF&53_)sG*tM7.iF[mQy59>r3]b6F:hnh79yG?]x&K&ZuySjsDsUj)[Yf_,-p[oY
1&fD2^v/Q@+:^"nO!`k*}VEH;>h/[qzji&YaMuSmF"dPN;fTX#YN,m-g+%]TcQSV7:f`t^bju(2u_P-EjO~_"_n"MeRU|80I))Y"!OW4amMZ
/.8tDk9b_*`g0<>sAl^clxDSeWY%8?@3%56qaU@nRUTJ?Mgy8lWmqT=bn$
9JJqH0oTjFN<rXUD$-aJWuaI(M:l4T]iCWH1a2qq@Ht?ic;NO%u#aNOp7Dse61(Q%>ol7Z<$yVEK_a7+MXWh@=a(0Zfv0c!Sw,@^;]Si,C8GbN`HwrW+^oa2xLg,WcwpES_DM.(OKQW0($@95,pU+<OkkJZr}gR51*q>eF[K0`K/OKnWK>HU@$n6]i%GAuHh&m%kS#Z("4XSO-6
98WMWmcV2P=l#7~/g_Gv$elh7QICDC=f-;M-{3=9
q<2U%&-qy9=,/bVJIc")Uu8I;k`[c[oqqy`;M^y~#Hq>0[iG=!vD9Vkh&/G"pd`XGD@`i}O
KEq1H}bxc)pa*AK;nh=|sUPfgq2S>2t51C42_cNGZ%2sTt.7fo@s[;jMSd#c+%MqD6<{F)xFbCR_SFWm.VqIRN2G+J@|3?c!A,?3Hj7G_$Uoe_3P)#@HfW7!5iQTm)&y]miJNOU-
g)f_+l~?;KtvkT?Gmb-KR[3"ZV;xz.W[P:D&8p^gDup&_`0pOSEn3RI<uFD)/D4%pa&SLF_i)n/KaOfD8,*xr]Ih&?c";9b=X>eW
<$Bil{)`(dZ_>gT*fHpvJYcCJu@2GS;yjO$<UHGP1jG~lth4V3i1:tv7rhu6OFyA7Z)g>zAw58757y;hP@H|T$R`,5r(CM).5I$l*GHZ?.!>mPPqj)GQ1RFnZ7"?^X^rvD=#
+&r
3Wl7(N#^vu,E~wGcdex(v!:,C_t=omg)~r;83J%T^N(e;LR7:c^xzXY&5el23/o<V-^0/VYL+dR=j$=a9!MV&k+GYvj:t.-$5OM2xFR
VwUeXg6qsndg5V-Ns%E,?O[NBkbY{,k6u#Jam5q]y"w/#S1HU&6$<[2.9ZiLe*~.(CJp2QKwX7UTCf?"R^1rx8()xWhiQ!mj2DkCV.4-WWu8x%|VXmSqGGPsyQ{^Tq[VF3Ju*dbb0C79oYI%14T
1DQHfaXa:#4+7XZ5kK,m2g|q:u!F)?,Z(KjRrf*6IG!lPFagh$.]52jYa="eS2-!&CS9Z$prVpod4@&jqV4m:>k1ShV8%m}!.s5G1B2e*LS
|CSUfPT]/leCh&;K:;E81N0O_OCX8M=P0@J"
`=f%f{Z,`
<+KR7EuT;r2vf%Zoq!%.5.[lv`NgQ)2-f~P4$8um?~)<uid_o-y4FYl=*i0z.xl^M3k=Gc+I?j%0l
hHjc?,"n&`x%dx6$9IJqO).dm0yL0!s16UcO=csD0?,RLR"I^dnU,15Yraa:LU=n`q:pyB3cX?qd&u3&&]<^/<mA1Gg%59aY.=lt2;fXrS*)?4`"!G(}
FY_59r.^ZCDT.!$kU;f-/rCLrM;j>Gl@A5>t9ZilBt.IrBNx|7B,BH:m<?-xJ-!wefOyGq@v"#=K=[qHR]x(R6U1j#<;Q>TbBVsWq6aTJ5*6?110#+oput2c[6BjOr--"T*Xq7pdS@1eC7.M:AYp@OB1w924R%{Dn353.ne>)fSkTr{i^i]I(Je+}S}:Gxq<*x;MB0p</jVA&Rx^U9c?22KMf%G&j;?amPES_.<o(q
Tqe4=HF9@_
%x)`EEYaj7/dz*G.
W@=8eC
5g5aI.13kBG0tS&L*>
O0DSuE+AU+C#c8A45JEJVgk7TD<`79N4ZUw7O+qb2"M_,;>8#/xUDYnPNS`X&]r"c/S_^Q@[7%wX
FAArvuk`Gh^&)B*m|.c_b6
2d39.8X,4?"fc%-KF
TD^q*$y|YA`.h
4LhD1-RE>=gTvvF~K}]q5i&;W:uo]m.!#8A/@Qy+Q=Oenbl"M}o7EZ9NA8?-dp(*y7ywYj^]".7-K
Cp:j)oB00#NuPSn=WGeeGsn4R1__!%UJL9>UrAt:PJD]
%n}E:_.R0=mH;TRJEye&V?=89tjNMis5tJ<CW&!L_G+aYj)-$P/=p-
yrvT%i0zNzIua<G1qZOxHSB#SXgwr7$R3!,}V
`Y%[`1Mpx<P1]P>8Z)wuFZ.0N;pVI-csJVP!#}*CXN2O-:,LF*,no2ij4ZYjvfj7&FF)LyN)oxh;wz&87<$~$J9}o{YbmrAZp83&7-N)D)${L?<6"!;Fnau.ccC~]>l|8.Zrx2%?^aif#,s;"(Pl1DLd+4@8IWs{DE+;(k$M&@/-+9)X+|tFs/uJePKr32.DQI/UyW3FhX/HPx^j>?-iFFY,cVe;/-2O@SsNU<?[(}`{b*#Bc9)ocXG:bl*H*aoiX_.=xX<nJc+0K?:AeMz!Q{fX:~U1e,CNgU>U=W_|qm3qeGu7
T&!H84K>miLkJExc2"erkl1g}BKab/mnC[nmqHagP_&Z-cCp
g"p^Hi=O/8-jxpd"YR]1?jy
vO]8g/e4dx
oE(>~L3V1TH1X:&b+m>=3e)Yc!?)-H3(t<sotXr=691[%tQoHd]ZFt96?76<o)b38VHg,yy.1P]wXJp<+(OiC)R<^`t^oyh_gLbM9=+a&<S2"knJ2p*TA#&&h6=oRDfNEU20%,NXF=g%l%fc!P62`FL.LbSHPvoAqDfP~sXp&O.?5exolW1`.FNx<w;-PE3j|?1n0(53]`D=wZ>qO[u47A|k0gx
-b1WtRU:J#>V>l*88@("V2tZm(fCr(,F/&;&y;c4VtJ
2S`HfIFskuCmtkfHo1{(62I"4-2<*?CP@%
:PM8!Jg=QAu7t2"
9:hISy&B.:MR-{+qVw2D/iiZaKAP4k$$dGcj;c2{Ibm^
=>~"_e-o)Lp*at[&cT|(bEk-~3.05Q-E,+C/{:J"rr#jqaT81%<Yq@LtHNG+}X"At4Yv:9#*+ue2g%v,
TH618n95D
77xR[5_7;N>j!?Nz8fP-L9L&3:YRPlZRJ}89HtMj=D.>3Voye?])G0EWs<"_$dD^A5g$
D0@J2T>GI3)rVqcVGN!m?<Qn&NogQ1|%cutM{:J.(!ElGYOi"fPe1m7>gH19JphSO5a
Z;%o<AU33t,O/*ov@,GZuNJ.6<t40OkA0k4#EQXitR|P6z)<IYU$&+)#JsZpg3BlFT{.U/698j$EnVTGU9{e7#dZPBU(#^(C{%tv3.0sT$tm($D6.yBq7XA)XQ)nTquF>)xf{BAKzqhP)%C%H&Bai?oaB9BVF&Ri?Sq#k3XZ&mF?-Gvv6Ue3Y&+G2qjv8,OOTKjOV+bv&@s%U8,%!-U,qm.3J(Vd|g>yrg:-w:bFBPtEYAWrhd-33cwSXJJk,*v^z$Sg|WsoL#9OH[DX@7-aJ4T_Bb2Q.KMw-W4<$YsBur(?sx+/Cu;"^7oeed3-<Ra&@
Jb
O5,X(JRyvBdNL_[,pw#C?nIl6Oog0r4<tx4v8iRz<CCY9YN[!XGovd`_aFXlg"v,:w"Nx-NYD;Sblj;kGV^;uZ&!BO`sm3qaq3U}HZU^o1!Mg|X0sx]:R#/lE3vzFK@?M]l.,N/Mbb-fCPNN^81QUT4tS8vn6eWShDAE%iEAA(TZf
b.CGkiuiF#mlGlj@GxH4=:.F4TehZ#cCft3v3Mjf)SpZ`(!`&yhl.MY"(a6Hlr#UV{E]g3!tJU*rdEGGo8fHF7xY
9G>;][S&tdzd}D?v"<2&!$WIoTz&<##Ct3i2dD[%^g/ax_A&i+?B="Fi"njVVaC"2VU"]&57o)?k7o}5qXGV![wnEr
E+D6`:;M5d/dWj^66+WoI.J2mrBm&h#./D/WFoDi6@>$R<"g_aWt>QvHO9x"&B*/50L6A)W?4?.j_In.6`$%W03HpR!c]I/O`|Q#Q;L}0=f&LY6_0R.T/7vh"Xu8^p:=v"8iv<:!vP@(;[dcIi1I>eG-jD)7i[2+"*q#jE"/it(`VF#+?K&
N~`hu<;M%L.r(oAcx6rV"cVNkaD0!TcmQC2i`|[z`O9u<h#nN<!A/Fn66"Q|X]3`DV>{[rNo[x(9qyq3
t
T+wuaP{#-P@.ZnH@u&-YK62e!nvvDxdIOZtSf1(:{5{-jeMpW!RIZpka")OP-%OHJQqkllSuDI$,Q[l6,yWQ`Zp#YP;&>pJq<!`+u]lUk+>vw=v4C+gUW`X1IP/]{xl=*Y(vhheXT@|.ws%d;nE>2+XG*5ABd-h<S"|M;Mv3POJJ?@,U%j6j~.RaYR]#Su0/58M=[995(dq;eK4,L!8]~;UZr2EC5D{k_NkAr3*wGN9>k;UVVgrP-J{ah
hZRvBHfT.%Z>OU=8Da;*z4QFGYYvvk
*&5FNM!>$,,+l>W|9.`j([+f*>m?*M@Hq6lcU["y08Q~*O8@fNX#5"v/X=tzsfZsfU[ANJ5,;P1F0o1N^1`6(7UZLPs]sBQ3hBXEPlix.%NO/u>Tssk%$y<^M6XDWsYpOqacf%jc8,lxikr.e2U^@6k]7-:f8ATu3-yWRBq8"*i5UD>toBM4@[jZW~*6dAg
vO^<(TPS>L:@gn-MSwu>(NI([fXKr+5-ac(LX5l5U5_R0:q7NjLNN{*1DRJuq{XM(.Yy3HQL-l"nyyT)_G#;NGZZr-r0X|A%?I7Kb)G=g"BERF"5$Z+$.44~d`OGSa,RGi9{GWPpNZGx8!_eB2U|*dHoX(P;,N8R.2T5-TYi4[VWpD#daz=UPV8s[n]YYM;qkuCgIos-+VM-%]sjNN!m`H028bH4bG2?i[O1^Zoh[,>IjLK"!.b{WNmy`V5e
v%3Sme7>8XPM)l6:[,&d~7>6L"9c?n&Jh(K@Ov@i&57-Q&GnB6h6^CUQ17b%+V5S`?+<bRvxo0ID1lH%7g-"$+>y]qs+#9,aM+l_k%ZU9>6@YVy6dW-KxZgT
T<93.oTk)mj`#jTM1&Qf!puI"^3`ciF7F:,?3tTh"~N+FE)3dS&[r
nJK0!2xt>[R>n0p~2_s!l7V"Qv89NZ]iR9q(^}/[
,pTug:.OHD,*h(
ZnI5wgDv&:nFe#b7QPme9bdDu6DbVodJC98_^^8]?Br_vOejIai@RCeH)bYj5#-g@s:q!t5_%T"Gl8d{F
"zZkJF-W#yQl(uAx?)@DpG6#k(QoByS#9ALw1iL;[*!V;18.vMJ|lOGEIY?cPvBLmtUq9:.[:x.Zw}-blUInO:q%$6LD,#)A3YOLK!Y3Pykj`=EZGmRdNQ"4%LG(C+%;irg7"pZ!,3-TUU(Q5wFgpaQcH<.`Y`:]rd6D#25t1ui2<#$kd{JIlWmSJbu@h;xDa2C8*aY.!Lxq0lE%$Wg5pJvaSmaZr6Bh6vOC/],SC`r"XK1mrIejBF<C(6D
x-I?xk$xZos:id(SL-j;3}D[IU_D%W^1SoZVifKh3V2-9`k[eMo>39x@VbQ+NlIlU9ZsR:)8KMN6`4uXc6$7*x%J/=wyo?`+IS`)bT&9Hx-2!f.E5-.Bec5U&*,>ll.n,>D8]h>*pzUWNU%4(~Oe5?RGhIZV4{RXg}om;4[N(
7=7d(L2:.K33TI4(IY$std-2Q3I]m%.AAg=HBqOB8d6?--/u5]-
RtY
P}/m!PgQw4j&`D6v%BEdE$$k4sGM(;"/0h8p3y((#+wS143
,CWx2;6%AGH}
b0@E<B_FSA4/mCa8v<^B%P4oZJ[g.09]e]!aqdy(LQ@>lQ#mM7WYr)
0TI=3(-!q":LGnCq0X.w4!4oT04+%gs<H5V+A+,#-ZTKsXBFy0g3%&^/p4(pX$6|[sI>)"*XF>VH!IkgD7Yr7d$"#[iss58M,@?`"Wf(e<g/8K$K(gi*P8rSRcYn.>k@VD0k@O0bT~oT=UQwAr;?N2ZZ$h=FJ?1pD^/{j0"+kTN9%d,f*K0>-v`Ta_5@KEQ5s`Gxx@l!#1G7j+T/YXZTe~7[I]P+*s4Ee#f"C~Z:Jxhh)S8<%YHVq05C+Z.oUomjf`^Ky9.EkTry#[3elyAY6/PbEzU73i7i8C$xJ:x%s*$ihzW9oI%8-|&QUN[Ow#qgQL3(`!116DMITR`d%-`{D97D$NL~:nOXQ4):4P%+e
Z;spHf8BkAmS;BDp`51kH@D"_]^V=2,v%6woHv&C%9NA8D/dFK_XW(;sDIsKXTKRfj%{@L_|uD1J":`6N$O%?]Qr2s$Pber/1QSaNre2@(1/_J.BadXmUTWIdeFel.mH.~-q.G@U*nq6;IpYBHHJ8p:]#hL`w`5T)p<`rTYmkm3Z*I7,44hLYu6vNFSNVx>xcy+y@f:DWM&IR]OOXFpMh{HHOaScW|b9Sc<z=oykQN3m)Y;_uhp@7-WW^{R|_A(D.|n,wCjrf#(s-6J9E"r?Y|<:Y0R%WqH
x0O38xCy
z
ux.T|m}8lqx6*!T$"D`sYSAk:AuAJ@DEiW|3,<n4&oH/sso.u48jJ,zLO)Fcxhg4]FqHph6vbrVi6&4;Ze1JJtJ<Sr!BxTBC?i!AU4A_5h=`C0[!-oCa<(YTrrP>vo9#agKw{?DF$l*!UaaY5#
K.v&r+o=vPM%4borZb5i$m!IuorD%DMM6Fg<(WS9%Xn1p1v$vlAiD>:#)":*UUaA9
Cr0$WQ[/KJNr
y+*V/RO0";su9>$]5e}.H>ylF_CRcY3&)lP?82~IYVc5Lf{OYc#pO%0ua6@1CgY$pBpH8jMXX
)Aih"TfO27
4l"GTV(Q!"O0KSjNO%txG^$>"UwhKSIrLDa$_+h$Vtgwv="G!ooHp_Cq^pTF&Vi"8M:5T@mIJ@iF0[XrJ+7~r>MvI0xTXW]V)
"uG1cTNo_kGd5Z._1V&h`5l4
O"e8`:mVHS4JyT@j-0*CsTw)U5]PYf`<IqOmjDYjUQBG}m20O;d+:*,j@%Dad/Zj(86i|"HnQBgq`iaV^rkfFJLr"8u&D%;m|g]%%911j_QrcBumMPK"*T@DEK5U+q<e5v-[7p}3zvoaCX;hVqKO~rUVP[=&bTu?;la0MV^i~o$ksCG;,4B:6^?)[V@2.Ffe|;8Oovtp*ZGI?U=g5qziHkteISa/aAC,o6z)S71Qw?[%4.F)4rPD6#P1fuG3S8ML+)UhjA)vmEZ*^*|#wI|nT25<fRv+Cr
kw0eD:(AUt:&BBh3Yj6`#Q(?XNeY1G"s%plY(/"lTXfGf@=eD]di)#)je$<HRSa5A}F649ETu&2!X}F6KZ<7d2@0e_9(Rct%M,0(<x@7?fc
<j8@-)BXraO%E}8;F`A.]4f4`6bxSUEc]5+.RJTkZ|ve[EEW
+gB&&J0X#f:aqNkw-DL*`?LGaNn/9
X;zkiZn
GN?.-(!_D`$1$xeE|;[f_-68=9!c5!KBa^Nfx2<OOqv@HWM55?[){3][I5wN$[O8#IF"zNg*8LEQ^#ygKl,o?&DTA%dV9X#C53B=oZZCcI
op<R%>.Nv<HE(&k-8z6Dpj3i<sF(N<?^^l]vy(OuJ]m.U$3Z$@nxI;i:`MG:V^j@8i6m-GyML)Tkw>PP,V>DcP_OAvoScMC:)?*U_A:M5c9Vw9f(v}Bd8S+P"{SV3D["B}2_R_snXf-}b-.(N+WF9Xwbl_hkS+XSG#PPSO5FES6|OEc*gk&JlR5/b*Hr*N6)ytXsV2mpI}x<;L?gcC2kiYx:kJg0M_Z6TcHID_pnlli(EVcX_f-RBy^B/PT4R,m;
ZNcCp3S6}TtJF9A$s`5sTqd;A<5"{X{gC[U]lBFdG@xEX/"=`7|>GUX4D`oKmq^q,D1"@70U.B*Rvw:;:%Y%HsOr<<^^HybXP&qqm%Toi#A`EiM(ou#f7dU%km."-wEgxL-@8`=f$MdW#9UKto(.gr]$$QR<w0MmK"@7egTVOysP9!`ya[;e18XW8tW>kOmWjw%FTFy,D<L8!2%1e8VbUedP6rOK<8!/%GQ%>p-j@.EA{!N_t8y@KNfywNgfN*jwOd_eQ,Tv2
h7$7m73UW>[1E&O9_E*/{G2AM)91Fc9Fsj9(}p)W(T,oL
2;!j!A!S5.,_;S13K-N+o9{A*=qm`gNY*1q-cgvjIo3N<s2Ncu9^SqUFP.JdY>"dp@vmLlRqrwz?bGmmjv``|h}fu,W<`Tb^^-~/;^ry1n|;1U%U3/ir5pC0`7@E4U|W2V($,HXw~_S^lF9TB#QJ,E@LgYNhd)<=V(vETj9qC%*COrEk?sE=owDCY<UZ!j0mrT75,@oE~vKCx4iSGpk$)1[K3kPGFPHc->
uW?_);ys91?Y"1l(9B.95NOZA$dAXT/BnMbV<5h
TGUr<fZ2Jq71_CA9$12t<c?MvNj.[mgRcYfy,H1;XJ(+4B`LZejf:"C*!;MWw)ei+AG3bCfxR@dM!Q+o^xL74MQY-$Uz"?)EUs7>/y`@f/-_bLj"YyOj?q_G_U,J_P6l)I>Q/e-g=<vH6+:oXf&_"
8Ji-W@(NWN_NbnSGX1DgKYstb.JJim9N8`UfjOc@u=<AlzemB90ffBrx]:Cy!kKg21YS$HgJM_K?sUsz9Zil4?;E)0OE@M8<uOy$-3Nzbd!1SI.DEX=&`G5V9"8FSIFY/g-uhDEn@~m]`1E=G}963r-vX`kuq?@[bmVo
(<:RIR$m[-c${5~,A,Dsv)|(8!vFrjd-B/l-7Z5+II;n~h?y^p1O&2J
<awbeeWN_+LMMyQejc[m
2XC;]?
@XhWURseXFQ4XNs*|E5;(%#T;IQ!q<f(:.V&O8HpW86>P1N@h[S-49AVD<TO_4*;tHeRCX-VD(hTp!](p3YNIlkHi;60&`2CQSg")jyp5"%3ppK9:eFsXKdT7C@h>GtZrJKO{`^BIO3)6e
c[.q6f<vAyg|asX{I[Cl7fg]GZ)g0We=)@$kX[g%U3@<ja(Ff"u`-6P
.+q64<l$0GP5"~VQw.0Z7h<#P9YPRV,L[AV:uo+*0^sTQJw,qBDgYEYqwk)^VG)!<qsj[^(AFGf!:nDj"-;S41Is(AhE
pt}cTsz$wmsmKN,Ui&"hj-Q^Sf:^QXkNKRmZu$9Lx<ywY$U-O-FU=DU6|=/mlFJeaYqk}b>K3Z"X$lq_8S"3wS]dUOpL5"XL6a*nW;-&#?l1bBID8uvl")
D+Ovk/Z%ue!y[(9.Q|BA2%OU6V:pj;"xAG:%?O=Sbn]OL.Q<[moPD-1nf3AKJgf`2BKSu|0J"lxx(*bR(E*`KGC~6Bv23:O)_gjMw}o-N]
IZ^X=g%5ji#/tLi"@W^@>firBT%Vd11SM6d4{qai~^yJuta%fCbS^Li<hp0#4uM:/g}o;jwEUBx:KIB(sieQlVAg*u1_bU?8*GsTET$wGFkK";gr7l|G8rKr$y?UP;Qhf3JlhV#m.8@cWgIUKizfA!*TeEN"_VTH8SP&m;a>(O#:H6bIAvjHRpkw7rU>8V2@Vr)_*`Wed$ubAY^P!M2gbZH;6r$8KE.7Nd_ZQLcjt$FgHS3*T@B<8c,#HKT*{Y>*H8tX6X19Rn`vVc
<d>ATZHbpf&#nA;Sj.=0VjjUW4:};60^
7m:PdBj)I?ir]fH?)uS^T=v[(y!r=Z6$p!E$
>J:R2xCMT2&$Hx.sw|8/;CqPo:E<lPOef/hm6NM>"bG$?pXn=zARHZp4TC7NI#t6b7v2Bh9MB-L-UZL/^EPrt#oO,638h}Dl?)QgH#dZ`w
Sc)@V(sg;V$7jQ5Vc%nYg;_m`1P!Xae_[7y>GK5A@;9gl!6iToQTj*3)x/wHMol$e!_TvF=nnDTX@L-&2>+MT>q5;Elo2
aLs_}X4#V?HWB&t&d]a=MM^WEu[<g2;Sy(HMs$+_pRg_aN,X^osy4)<HzPZKUq?/.1Js@7iW*5fvK(gBzj*I1JtZ!Kv=#Izs}WZS)YzM6?}O,/
6/pFM[!3LMrQ3e.dXq2&%KJ0FS$$=Uf%R&#dmnAE<:ETGd4|BC4a2maS-Y;x?CWGh/p|E5#<0LB{[?"[mk*HxFeH0t*FPjDLQfFxIAwPfm[TwK@1!@I5VOGe8-N3,;`[gq6)C>?katNB/;D#R"37-cB/#`FDu{;X+
4a7;Ug+zPAyJ8_:MFAJY7y[YUgGpD9PRkAvl#XSiZof;-(/r=$<Qg3jVdfJVu@_op*eOd!Aa0C_#XL;#6]BOE*DOJ,u(j<Z0Qu>"E9O>5)n*T^T4syrJsq-TtZg+`Gt)gwny<6]SU}2O-7O|gk:z3~h(^!CY?aVBQLmHD5Rc!KXUO#<A-n[Atc4Q%qL7>l/OO8NsrZrxUV)AU@^eDNq[)0JkjM8sj~xrC~<)?RZv#Rv5i<YtePnf:~Ob
#0B:67^PauIDhL{j
06CpThf+klOo@#m2.lu>*%k,B0@dhuB?0>;[ilbhc]tPp}.WmWAcBzx
H<<znx-=$&Ry6Nh/c&&h$G_8^D"sP5TN34:D=AcE]jp0*F5hTO+iT8DIe[!E,*boDYtBC3eiIM8kjg?W";I_N<n<O9?%^)[sp;*P`ZpM*Cp^Cs$qFz4.DFk/((]
:N?QdGBeb?AoyENbb};pgMs[F0T"J!.kb9sJm|u>$)*wp4d^#4@PVGqL5s(@$.Fa:T8G&P01"_4%<?3pcx&8lKbMZ3Z7>:+1#t52<a5L0hfHZ(:"L20M`SQ_jh]l]5ZwWlOU98+RtQ
otewfv_/USg`:c?,n2`CqL>5,7dM#/?Uw(#PH%<HA&H92e![=4CN+q[<^Gdaw7@8ad_Qoj5VH,rc)-iA"Dw`BA}1H^(!V/*Bhn6&[Ou8<[RaDU+4cXv"Hc?P3nK2^v}id*cl/9LZvtVPzpxHtJNFCp`mkA7%wW.i*Wp[Tt$DylLFcD#2<Nyb~$nfE++1Z<xsPxTE;q(E6QiAigq;o
x%Ghx$`qQ5c:_.ml^!wlLMc%Td7:hY"U)em9J/k9dEva@V^+aJy!Z0+Q}tY3ttVQY4-`+Yx]"aq=t56RIh.VL:za}%0HEph
aQ_OmYJKE[Djrp<aL9ZM4rcj{Q^`S"h2%_sQg%[lO@W*RmD,`xQ0@oky&d)JA
$ex8N)x#F)bi*,Z,1,Hmfi$pa[j(|_Vw<+njDL0K_o1[iq2d7@!PX.xVZ%d7]AZTFJ2Q18o;{BwOE]a+N4<jOI?m>
XAJha?IySqUiTTcFD&@Q}Z6NRm=smh(2avp*1:E(JBtK#?v
U83t:`mE5ar?NHG9]j9362
TIdNp$vd431nU;0eOayi!d+_L*Xf=b";W-!a&4-"Y?*#pr:kl7@L&x^(9)^#GCmOv#DM6)vatvlDs@0wYg8[]c@!k0X_4hX_v9pl/7=-0z"ZF@XELB.,P|-]T~^c=a^p0RHK;Z_4UWPyR&O<<DOUtG[XL)?ZSm*rOwwypBdRV|1BG2)2GjSZ=r2++Ca!>{du/WDc3qcY$J1|M7EKLQ9HKOLKUpkboMTQUFo@3H
!0v3wU@T%q=CLS#ijoJ3F.(De@GrDX{ewaQEU8VV)[5,
!Ind;4]zDh,I<;+T2UYK
ii$+}2l$~@ETJ/^erp~DLNax3FFI&V@s65SA_7->C.rXtLIG6NO=}bgc0+lD5ajpHQ{gkleP1({oUSa#;MuLJt=90?(6P7cwB:p%=Qh,<s]LmH/QD[A@g%u*=[WO*>Y&[b8O:Ow>]*6l,RJ93/MmB#vJF5c=eC?3z4:w<mk@[/~S&Fx92i?[v;J$[CgAN[)$o>)Wbqd:AB]+
r>mlqR]
CxWmx`k-e">eL[!kL:g0Jr
(r^dtD(2|`:Z"LW,cs1_]?baX_nUYiN$?uEhFT#g&9crFu)@zCWwno:s0>*JT]V.NPGq&Q(KmhAMh^HC#(*](T.Zex?Bef
ezF4Hg`yU(,@9eW}OCSBR?"k0h.l)!WK"zP?n(<3jIpUWLQTLTO"*aS:lch#+l(:+RZ<xm[Ai^s%f75TTP^)%a(<@{Eg8:
{5PVt"fscI~8tU)sNRDK%YbiIBwL{vwnfa(P%F)]At_BxJ0E-gK<SaQP.1XD`sJk#FsE|R}%W(qK,e3q-8e?=dJA=Grv9"CZsx7(WQ%+jdxlk!if28P1e+jt>rj[0u+BiPOL8ww?i/}I.?ckx@<aO#4Rt*+flj6ZXyOeH$`<:vet/MU,]]QrA(i#Kcs^?p*;.k>^ZbF1%(/
CS/eK-xxZjY55oeyR:T)ky|;UwcM<XjahM
f{?>cgayEQuiFFQ^wamdvM0x,Mn(=uWisZASMj$IoU.:AF&uln"vdn!b3E]T!+(`dQH[*KAxGu3REhtuod?rv|_P?1YkNy7F"}9Y1W>{UP:gRj7Rp4pIn.?{@R4havxE>e)Fu4YnZ4b2g<?Nfg37iPHh&oG_)@),3qG2D$]uJH-[>82F$S2P4i"yV6p~ZV9|+Yio3VWQx$[N^mvsKA3lF,Ralz_cv[#D]`M-,;?V4XgOH.a(Kj1-L&`9w]J9Xc7&3knS!J33WIsg]{x~ezyr8]ydL5^%qv4Nh#bo5-u=>!^.]]NqtK"!b9!$@-EP^$2r+GX2P7q!w{cAc:=)s0/gO}Kfit7^Baw.A}Bh]]uXM<q8cBBFuqs!6|DH],rZS$LUl%T6HrD`rss1jn?B%CY~koO?lJZp[%^KZNq!4Fb{T#ypv[J!)u_WaEKwsfGO++XxSov[[L.Vp4u_$3fcp5aV!WIW27.VtB1s/>8Injr.EJf6i;/$pf,lAtJJ4YyYXRbM5-ZXyo1ba=6mG.y+AVZD_C6hCA;_mJq``MNwFFJg
cwVrK4RL/rmP$)~vYl/llU[kQ#`Y":+fX8MAM?4ASFT
8drsCe8c2X)b1bc(zs#A,PAFijO_xL[&Qiv"
kOK!<lQYu=^0NdLzY{-a1xJZ2(Nb
r;rv$$6p7vo<9JEIMa)y5T(4=;g.0[ZYbc0"hHAGlKxv4f/"/5k^/h:O6/]d#n)H$@q
lsuS6_zx7LMUd#@t;M7F{y4Jc]>q}X>L!s2c-c<_W7^`z33+Zs/
z0@HO>js|*_HfLgi4]>&+Cs:jsb3`W?AugK<nbg`oc
W#enJTiT4{R]B/a1vtn@P)$ZUd6m.Es)sO_`2kO>+3]RRax!/|L{v?yKF>v2#PJpGxrzYjc`[k`g1v?!<upM+v-iWUmT).c-ln0;][X?[Jy6W{QIiUvtASrmr0a|rG=##/vAA
IgFrv3rNrEi/Eve[ESM`VH!;Ey;M[XB
O|
y0IPcu39XKx3RO%Ae
}aH^gnFO$4Dl_yq_27va/#_X^_
5Hn-My!
rHWE*
,6kYO}i(Q=t_:s[=oexnS/otAKkq+*Fi_u>^pbfrH1+1m*GQ&6@&K`Hm36.<-;`~u$d`KuQA!2_)f.R!T~6C+wUA`,6rVudf"S+EFjD5aEDL4P*CI`]d;T9ao^I!`L@~@IAg+KP@eX^FhCRnHpf
[Y);[>ku(5(U?"y>8Y(TT&r}"LDQa@6%dwFf*@a(om5"v@":rN1ih{F"HAG&N.vpl;
)Qe"2UW
f@Q=YF9Nx.)
a:8Kb)_m-)B+gY!;s/;=:3/bb)%g?JR0.5T39`!g9t_UP3y-eZ(2|*Rl^)/*G+]$jt.Cik%[*3~![@Q>_)>Rt;/[9-7S`D<?/>*v:%xYz!bS%FV<1DNI90JP8N{E14DnM>%^5d=8&!f7yEo0?,lo#tFWXtc??`!+Yp)`e#ad@UHfGF6
rqMOtx9Giluun(pPs$bbcw9c$a:x?R]n3K0qC37"Bq&X+?vK&GI7F>fVL@Y9Jw+tSK<>PP-8[D2"{Bc`$yj9|Zzf93[1O`Y!%"tb%E$Fwl|aOENtEJ8BMuz&UKh,?g~J)3"vDne*z;T*b(5BXFd(>1FEt].B&-N/^-nQQ&XlwR@G(>u`<2`0oeJ8
%uK7Go-NIG6zd-LH>V%@HpQQiLxafMm49`r
eiQ@y}-*&)dXso7&_)c4(|QJo;..D#L9k^[g?:bl.r1:?W.eGE6a3lxnj-ba;$GeofY4B?m!9,aA+0^E(4=rInqa7O%=
)R"53b-q2;&d)GSJ"Na-Mgox!T^9y53GAlOj}T^.(]NWc*Kh:BEdp>^N?ucSd<b0Cth72Z,-#yd:peU0.h]r+k8V$:[[Vy/$F`o`%(B=Y
Y%PRdyk1}b%r&=+W/YzT0Su`EVT*h$Kg[0#I;Q]fHBC:$3m%dCXjX$Q
jb+-&pSU{Cgv~DHLZtrS
rtNgyw(q>QMh3^u`Ufi|"099Ze>Y27=daKI;Gd!KM9RlCCy+26Rs^jT9N)$Tf(yZ+HyGLNr)Ti08B$_[7eU~RcI]yy4&9jaru23BX76F[T[{aB53UzF>pL0q(DE0`fu?F*32&As00Vs
Y_dq*.j1XtJtO-9]e<M?bT%OTHI~
&?~>P8Prs`Z6du|kX2V32b"Jce/dW"l#Jf%$p+~ap*S=zk<5oPn81#&dL5AoL8X=9h^(SV.J^/n,TPak2W0jC,k+]/m#xese"8QDI$#KSS~gRn(EAKaob#{kGS^RD^rm
"[t|S#M1:%e7B3[A&~1P-VS7<O&Npc#P)J2%/s,tVecpIY3o]
"C?%g&Oaxk:q$]s=5&mKQQ`f^.r
@>4.Y%(`C(+UeXO^GVO!hE->K=j(I+<88o7:)51HQ1$`?A<Zp*JnS>/c4H8;N2gOVVR(Zgpd+$`.GbQ1Fo-T!OYY[2#V(mQUXEbs?Bn#j0HsyRT:*|$mlo7Tf}]jhxHY/.<pr~n}6,Zej`pe75aiEn_!aW.x
D@9I8`:P)G%:39Yj_C9cfH[J>d=Q"35M"$CAfP4*R0jAu*SX"hT%-/bW2/Z#,[p8jw]&2^7$P*oT]l1LnRQ.bH9hS#d$y^
wo,i<vQpvZ_?)^d3yhW,de"g*JIe`w0//IP}gNVskyQ"t35[ILDl&Y]Wa()#RKDp--`%bZ=TVsR*(^*3ouyi!T;#U+I6`h_YtrciJAD@f_y*:C&?%/J!-A#~1tw:)^_=5?fx0wp]e$">Bc/w5Wtl9MTHZGDS:oca#R?!PmRx83e(#G9*6fq!_pK:Sx%JjC..CYRR%J
9#MNS35xnPlu&&*wr8QRw,n;_dJnC%R[1XCCf[FM[.>]j67!FZW"P9+)@i6A(cCj~7^&G)NY`cFZcYiQc=|Nz6tkDEr3Z@NV(`O"IJGbSnS+6.8
|GVgb`^fmpa`Y0+`r==eP.n6}BJ6Ks1(qt1["awN^2p=nY=$(L-tk?)+g(PG>vKuXA/=MM*h)o%(qAer1kTvD7;mJ
#y8Q2nF92hev?+T6$YffDP+v;e*u:Xwb?N^P8/xT6FJ"G)UF<s0)mG-68d{+1Trs:2/Ftq.7yja@3_3JDkZqGmG[YY|i]M>&-H;@jI-w{`,=.db`(>)nCkzN_]rt1Fmv*?wk+&SOXR~[)J{bMSQBB,.l!u.KjVW_hmrK3Xbp~5((lFdZ*w.U/,},UO"[Z>%w8:MXVyewGR;Uy*)yiy`+uxF4?knTgl-&GGT@ktaFH0S;{l/(FC(O]BykpMaC?nmEy5!*$W$cSY8BZV3.
!Xc_JKJTu#`im(3Jl(4Qhwits9s]ro$p9+a%4[<lM=r_--hs)f*{
iTbqQp-bEWiZewCX;B:>noo,_n|0U*67>
^#,<pF/Rfsj*Qf]fIE~aCL_Ya7oB"wTaYCS&]BI^Zu*oPf}srR-YGJ"o2u>x%HZpijMoTxrr2z)LBdcsqyC,eSR8^.:1A5eR#O)$%!72ov:7?Q,oW!`Lda>D8nz@uQGdhaP<o+]q{*
*+!1teiZ=C;TWt
M?NdFAzJ$/E$m"5`|8C&Gy%o
2P3
U6cV9i+P,K!K"%CXHx`d)q&Ixi*"BE#y>>TZsZnXA|Rqd[fVksU3bK$_(7co9<]=SDhwwtb+MSOhI[?6$^kr#ufVG-7~nuF#B*v)S/%Nv)w4<S<AZhHA(uYHQn.x#DX$`v+ol_wGIwJ5OTvnMI:r7$lyH`.0[9EbChdTr%:8?osm,470(&4uW)Q>FaKMsXlpDi/}+-h|xkflQ*Ou2vR+fv?N)-ovtlRfA0Wm16?D%-d9-Fq?j$Oyc?!.kP2xB@"@hzK}q]mx#YA{5liCwRCT%&"ew*avok+qj@,DO[0v-U,+Gx*X<jD
/Nl{29-Jmk]}wQvY#&#@:Du#kZ7r"!q:&5q/!!wU#6!u>YW,hiN,$J)l*OCl4.g:2CGnw6<~92X81"`KMvWIe3-1OqL"8^ilyxL:?q
c,@"ynT&E@XyzswFqi`d5E*/KUcD@ut,Meo`E>T(Oe=]eo?fAvSvcJs8zI_p!LP25Z!KKw~;^=^<Rv8[?-`e.quK0lAA0bW2vvdgQb~K7v:
xw}fM#CMmp%`bxr2TA?cLJ#nu!e#::h.6x~v2KAoy>sYnF)4L+&swZSbl/fUuNy$$D/U"-}?<*Ao^9/(dwcu&Ix&E9J*D`SYT;D=I=Wwg.sdFe2iA$IO/qpj&eZs57:ehAcq}/,9N=f+Tg81o,
JWmr5n$bH{Ms4rJ*S|e#sphZF8RQN*9i7IE|Ux`oI&;bvD,b)Y=R9rT7pQ_u1LoUA6*_ELul)7Qq4|CG:ufRjt(m!Zp4:j<Z@.PQ$z;y4>@+j1
t>ztvKg_)R6Ju%]ZN
BI"ep;#I0i
8$]lAZW[Ew6n0P8
14C2RtM!"-%;sW,F*1dJJrg"^nYz6^i(WA-OLLO7Br>zm"_5rxY!VjB_wc*zE]h[fT?ji;M~<"a7:084%;`~,dQ=&.JmY)yPCGsA$IH>v1COaO[E(3Ye[uoQr+JFA
-pf#C}$$NJ39_4a(?-Z-/LQ@p|d5x6t[i600@*6O37JsiMpXyY=2jE+7</KFGzcGhW&-^FODf_[v!nMjO&+S7r=!w+bc[B-QG-L(E@<58.nTxf]^:-S@QWuyT8xqbe5N&3Mv0^ec5X+gfzx*=L/w*[iZc}Oc:l&q"~)ZC.x(%]CvKU;ey[9faw*bJ3X8.MPCfYy7=du5K}VE4`ALrEmCV!XP!
,C6d+t[SS2w3*|L@"ui>PC,4Q[p)HM%oeU;=J5JFFdoaT8pC>MPb*QW<g>#Tw#VAOnZZXteq_G=sL"9k,N0<F`Px_$9ov?.qpWVsEy<(N5;k$/&;RlQ[#J<1hP?!n%c~Qv7idm/I(dz#/?ir]dN0Pid~[+rX;gGWt3+B&*9pr1Wa?++p^{L/12*uUP!C-a1/9wKbf=3,==1zIm&
;@P*(LBL8Ut#o0]|hv!hPpJ%nRKkW5dw=iw7g&"_4&h!9</7Zzj5Jcq5O#(*BJJgkVL#YkUGW8jf1Uq5p_bypsl.>c>.@oU|<kT>[gywUJP]ZWkx:"c>)t=&qc5!9*[l0v,=9;ijY4r=nOn%fFtg:t+&[MbZ8yK`x+Lx9]IKa]7B.8
AK}y+wT-w[4)84bBrAL5<f7!"LQ;v5X.OR|<f?L_|*"+Yp{KfREZ9Z,]hb`RW?)>b8n:rP,omnB_zCUo]8GPj_a-B>Y8dowT<RJ%|.)<~,!RAm{Zi"Ro74l8B&Jtx^nYKD3xh-w,b!&lcVO.fB<8PNX8FwY_IYZxWGSW4U)A<uH.(:IB)/m
4rgal3WMG4O#UZz&WF59nZLxv]4N)u?HP2BUHP(`b2P/_Mvn$[66)<,.{v-.Z8C(dFA>P#9L1;8.~S<VSP{K6!mh"`Y.&dtWzw^D=N-O]sym/vN+vy)I(<n*k!IZ}Xnn3BN@M
,WV3IErTkh>f.*@wp5i3p!rBr*l.L&H6mL$A/B9XW]<tm&nMVOD]/:E4>oH#uu#Y+;n_uKgvlWj6Qrcuz;Nq4rd$|!)GUYv0iP<0M?.llH4/HJ@4

s+_#E(`fF1?OW!tUzuK=tcM9?,Y&3JBY8cs;IK%p1H(oLc>3k4sfuC-IJSW:r:}LW-V<=N)+JE-Xc;?IvQAPvlCrwnFj-*KArB:0~u}/Y*2_==lOx
/V>B`KLk2
,SXfZ7;:?]OV:8$m2aeaX&3.fd&pv`iu-nvc~2WNnxfr[4IF~m*D@_
Ehoga
k]4GXdvn/W<[i!<wkaZm,xp}3KLr#G1/UE(#5:47cA
g%F(^B]("$DN6/U<xIN(GAr/KZf7=[$-7
_/TN&J~c[jH>Se&D6.apgQ#Pe7S#]m45ZAA60o$;-%l/+X8u9D+Se=#-PSuJ>TjAy$(E4DPS/:f>0yOZnDoC5#BQ`"BFNRBF/3z"H-~5r+<(;WcBlo:OJs*Q-:sv8xicP[ldgsEFCt2g6vUwRv$fs,-&,>yi~GBm$)^fso>>#@x$9!%.fXKJzi!UN;?(6<I3HqUCf]Dn<<Ri;!]SVC3qkDAWOK=y0As$0Q`%C(7NUQwf/Z_2},$OaHvOm=bMI
Vi|s*)RB($BOa`GU&7efU:ijN7>WDn?oZl_XA[gEb7oZBw7HcI&Vj[hguua^q.x
1rzM;qz4.CfY&gqnN.lR[&Bg*_)2v61dCXNS[e17h7]&/pp%S*O+AbTj<PKjDe!Ht$9ZM$T7V;q0qy)TckvboDw!rNW3_QCtSt=ZZ?+,M]"NIJ2nlS|M_N)0[f]::GZWzgg4n/37kxx07_L>YxM6P^?*;85mm<F?AI]Uwu)BKBbU7Jt-l)Rt]5kMfZ](&
H+mx|-jePqR6f3kE4_k3a`v-+u;6Nh%Qp_p^]gp`q3#5F(.J41)F
t:Qu=[l[P.l<(}of.-ZR^40]GBn9(Xum3Wgv&m0pgLDyJc=h4Y4g6fQxg*VQH}"tmKsSgq>a=Gmc#.2,0W3D$Fv0rLEr3fV}2KE*3VoXSba;f|9>YFO}flB:F=j0MA-~h.JpMh6wJnuw/BJf8wXYX+d/aYD$@zeZ;
O!quYKl~@#3FR9y4]$FUe-R;WheuB~8khYTnepq":NE$jFb0aW"Pwz<wvz;zI59)GV^u(X,pKg-hSwvKJ-#;)J=p[vg5opBa@=s]]7U2]Fv}-IW1s.FIilw`Yn=ioRNO39q%<r^EJl=7l3SuI8HLd)`e9B+)!"I#HPsqJTx3
]Lh%z[_]7%n1fJ`cz^7ZCeAfjhAk
n{>Swm75PU.FF"EW1pc6sqP2OAtC<R&F%?VUM$E#8.85%.-.TM0%dN?xWBo87h"@@9)FnC1m8hvaFz#wXskDv%f2`rNVV7vk75ktJ*]K?:FTQW#_!B<BnS*-::#*?fK}^!k?XV>hwK+=B``M1GTiq6q;;8sp#zCAf:FU"Tf0Ux-ME&/F*[-b1eQjtEO|7w5iAx0z0}>7-o#pn<*4iCNMPOiI%P=E9gviP$?4E)azFFp"=c0z>>cVG<U&#D>o.CgIZtP,1|;{f]MROP>4p.p{r/IpGYwh>-c9,i,x.O>UkktxU3=BMC(|h0mp"5j{DT%(boK@T%SS)5g8!#9N]wc,:uT[#.#CsZ>D`pgU+P1gU~X<CxW&^1`
+Kc0%v#($84L*`x$-
q[euYc<BZB5/TVG"q=.?MRe`V,qFQ:t,^9MmVh^3J+]^^of@uln9K_#9IBOmAte9oG)0no))"oC=hXh4B.eE"SlD]6Ta+.5eB[dytKf&nSOD3Xr1U1NZ+<m
7JTophieE,V#?[)R^@:^?~.F;eP0dj+M
[ePLQJ@ctl>CsPlqAQ*hylea#$-@VQ(8jAf:v6#LLU_a<
(K>17$lpq,?v`.B31U/
G2$WqN];,Ud+P3r"Z?t?Gig`D^!x@CtnWN(kR1:2ri?$bD~.9=O(u>4H*3=ux3PhpDt12YFElj+xXRTCIabycQ9mDE~&d;Dn)01>I@%b~rml#v*(OoHjLA)oavL(H"T>/[,^dHcY[>=9XwRebY(Iz@zcyq[^X&.G>)6osyWLFMjQr$QCWQKM`nj^i"8c-c!3Ld^XOE>yGevh=_L_#LatgvpB-J+-{D27I[!!~oxt"N4U{/gOU:5f7$W#K-r_]$F9#2tY,!fMI&=j5_5W%g/Sk&rK6i6#<uUQ~df<aO0VV+H.eo2$N&9^In13U2u5f]pDqw"M&9_nJIHU9c+2^xPB
%1a7W3[>Yax8ZlyDC_-[F,H$
r%-Q20%ae^1*Kj2QEy.illKl57D0iB/CCf)cO%Egoj<%7UJKn7SKR7"UBl!e.1&PQN~^tAst2;4(%q!7QN]/bZt;:YLO`r*@n`:H[BLl$%",wW,sf(6Z$x-splH#0$U,M:H$bGmNkS&iM&{00Sq#]<3RveW[L&B4x6D7W%:7>gIRN7s/k4]v7L7FPWDk*&YyH$9w$qU5|V4_uDL,!:1>-Gp0^)G`JprTlS|UA<pS!#!7WOsbq$$40.Hflv`p^OaemYtTeYq].i&]EpZZ"s.;=!Ly4^8n5=4YY*_XarM*l1xg25]5Um2W>,"XSpU`sv!LvD?EvSusm;5i%vPT7C^JAW{R7=^udV9^W5F5l4evwYv=MbWSw-=<ptUq|3AJ8H1qtbUM|ey>%1yB=<i?M`2b^8u9|L37mD8d6fq,~qr_W]kSLr$5(lA1G,loZ1s!.`.8]#|vI7#hkab.OMo9mj8bA0DNzOH*b?I&M&76UExU6q~2tXGiQ;Q-tm^fA23OcRNJ<ScTr`i&BPlYP<Sg2h}ko=um}c,=2UuOQI*3#>|
ID95+:nPak<7LF.,LT(sVLeC4i}Y7mCE]DP>7H<DJMv=cK7%&pEcJqe2/tMlKK8Ub10rXqQ>":/
H!SMdOW7}OY>z28=P7sG`IcBE0U.Mu<MOW7Ix;!M*xxjqI2o0=5dXZ!+tJ|b?5UHXT>76+edKn<LB2ciWgB8pdCIc[t%AS?0bb(aEx<hz8J0YeacW@JPLs:Y=>+PS9SnC>{5wK?6Bg*,^^RD_EAo9=ocACceT;tfqK7,=#3(JJV>qlPsA5Gna*:Uvjp>);#
2q,+L=C,zoi!@NA%^H3p]#.P[",(Hb6.LTSJJtOIBEI9G6b6`BS#0B#L9Qy!J2HU+B+,]`sOIXaQKjF6BP@EI4c$_p]-!r:)XET9ZfuSb^.&Bmx9s?hk.;tpVor$7/""/#YFUar@4`^6$A~/5M.DuT8#1Te2hI$&31h&Z#>9)5!Kf3nu_<]s>9
Q:b:FO79;7)bdeyQ;1So4,`B;b*bFOEs2e^^Q2Xxb2iQF.b6B4iCBeJAr#_3J];[M5R>!_0Cm7Fpvae3-Jd%Oy#zQGOE`5EwbK[~nQj(n|VS;a$,k3/;H])i0KRJ*wu!y@e^9;>[djy2,pv)p<`&%=V#Dq[#))%3k|r~=6NtjaogML!MOPsY[YRA.q7UL)OT.7aQ$#+B*S$BH.(lSK:`T43!FU>`GdRtplsMrFeOp#-]2ln{05Kqu_K;.;`-4q1b0BC
3EKYy<W(N:nlA5pB.|AmP,)*/wU<%/iu_/(^OUm^h$:8,J
@F2Ka;KUy$IOsUT8>8x)O8e);Un;nvXWiTdw[s65/egTRMR>I`f;cyOxK:C@-NKb$3Fm0t}wCJ>?gfVp2F=w]S`t
Rq=:e|R/c2GG4=N1he#fK}`>Z8)d9Be>,4TiT,;`"2m;r2X*hyJ?GwdoLHR3ZFB10=&ZAnm.3;8KjJ;`=v,!G
;>B6]FkjOR%!(dyHgp$nA+n
DH+e;2`6B@KkYI+&Hn=?_#rNYON^a0UQg~cl(fU/jB006v"3"dEv#c#W)+6X*JULApU)]{/aLS?AM[
)u2QTu2r3VQ"Xo3PAb~wT#Y`uYm,??!Y&nQoj?G0(Iq>(kM(Aq~7~f#x+NuEPqvl/"zU{&TllyUX/6R2^=ZT[f;%oQ6^ADjlVP+]e*_Jv^ABssLyYZ3g0gpApKH4v
|ytNH9lG@Az7maDnBc#R|$5,}I6/TeWpNk0Xo<>?"e/4&!9pQJxeu+q*1NM6EM/o3_`(:^s;FqB%A
I,>e+Ab+z1:QQ`h6rSXB);ldR1ui{
w"D=hj+yPZhg76]j+y`_$j&lD@X>gPWKF0pafAcB"/*qT4ujn=Nj5W-j<od_?9#;a6:8:]e+;
_4:%EDD)B<1:kr[fFc1PleAoanhLEuI$sS20m;8%E"|nu/kh(/+IpvI#>1taF;
9bid"c_~N-09<+B61}_pm?IjnxOEnoE
xF<%v_g%G{@m7C=M8Gy%p]"l$~wTB[J.:Mb[RKJGa-<@Pu2FUo>Ss?thXiV>)ue]MTh8=!I9S6x<uH!`vC8[
Q"qX$$)"P)mf`<@Vy(km0f!lt)sI.%9ueo-s*Z+eoO26IY=d9$21st+p3`f<$(L/SH:S=_O%}J<u*tEc>gI52vlvpfqnaxFYKKs0[CoHbtUn)uxn=2J77a=,@oem>mJ:J`lMBc{51rgd]a3vMD3uQrQ[pU;2Ugi]PC/tesrn/b8gJ3OvvE&5}w)oDm=atGOt%LjX?xSAG2.m>bFd_sJ#m69TD2OtMiah)IMvha

ST[F)/GK,Cjyvw/)YQ~9eU|u[r-m_AIZ+N|]Oy:^5c2RZIaI}lnsJK9+Z
ga-8!n:7x0S!?]Hc>]*K9UnAAvlelryW(FgPAPB9@
RK+Z7=VaLZ7
4%=$7;oly_)EpUZagxq"BXc3ewwt/!~jpy|v<t)
WsRUC]CoQ"_Mu^[4Z7~!lc8]OsjBHTDfx6h$*[Jhm^~E%W9B#8o?8JHFjOa3R_<Rcc}<gD
5)pxsB4>ZAYOtKn6ZnsbGxw>s8O.^jr/c>)qP6c-T05u-N,#c-7Bv<X!bW-dvw=VhR4qE(7-B_4"J{n^3r[dD3rM/4C8fwdt
}8_Tbat*<-pSw%nXqnqxcLJE>PO_iBU
9A?b/jTK1"|)8R%]P-^i}tuW~#/P-k
a41MYXM.E%Cg2G1Fy}h[TO43YDmiZ.)LFHlVl`]Xnc+c=Ce67<ct@Q,C#[n
eu`z?YN8[`mLXvW[bp!!Je2.-!:cr>6-:)O"9xY_86,]jcq^399mgo#ju;KZI_kNsX&)ETM"?+#OZ+f>3O<zn@70%S9rl:L0n_mH9F#yQj4rPgQz/tFQl.o`FCV).ye"SXB812j}f)bXvUBdjelE?!SB[J8t9yws,e2y&+)9VK,=c;&ceID4,M%<@%P<Qz?}L;#,@%xjm3m,^d4%GcF#DmJ{`g+!7|OD]euu=p`4DV[K?ZboRZb3?9!;A:b0Mq+j+ZRHXQ80Nfu&(TM|AKWXi<WY`}x8h&k5ZY@egg
|bWg.esK=#z/OfeHn@
;}uc_o<c.+w9!q,C,6mv-C7&O0uXjgk{?uK,nv4=`?*2Ef4%O$A("#(pX|#)^1#!&Prw?~fe/FkPPsnB4Mb<mi!FtT!Gd}*je3[Bv]])<P;G,{$;2e7LD^AX4PD6bZ_8gO*{Da=s;JWS]*=D`nk]lzJGt#0|(z@
6$%|yjR%!0<L5AaQiUXQlzoq0ba+pn`/%`_%,t4X5?me9O4msrkAkw1JDJA):Jdf!5DW)^Jr]@XKK)rFAbv7KvO,lvt%R[>a*ZhTq+vkJ8t7c:J@fA8JM/0?21g*@(@%qEmIG
InKV#`qH>2^M5tLG>_rLWJVPO1?i3PjT^^O#[.#k00hW!,m9wg
D>;x2v4X^QEgd>6&h[)vSR&M.pO`N
Dg^3bui*H`]W3e~eQUxM-M=#RDd
|e(M[_cE|h.as8OLG<*K_!Jd.U9_uwY+_Ec?bO/mVJ5,-#2uj*TX?WHjp,D!*OiwY5T;Pf3YBV9EggSjG9%t=0El~i0V!r
CpD:[c!;X)ZX+T<YTfe|rF^uvj^!Ngj6^`D!u3CnlmWJlFgv<H*tJm^e4+n;c|&[rm#O1Yg~2XK:RV`w]wA1ksRVCsH"]i5F/BI&q#FD49HhPTf$)-){=W<}t[/{ilHAs]w-[VGt#Cd][VRg1_)lZ,Zv6Ws?Z2=)ju
&]c7}n,UlcjpX[:XrtCGg#TB)`fUzZjDC4KD44aYK,3/iuS9#_8E``u^/XiOWSd<3=.2vg}d<CS*+cZ>`J)_Of-@m8e7Uc2-
GVsXfN3bb:Co1ovVLDZ/GQ:)C<<lqEIyMaof.(GT=o(tb-%P?:`.D37=s/3V#X-);NH"8<]Ln^4PSH#5@:,3`q#$w@0r@*S!nKmH9@vvj_OC<Zq$,60.4g;1Ld&W+mkXCaE+at4}-Uq$Z:K&NH,phG%biv#~"#4dfUIN0[F`aAD|CIED!|L6"95lW3`f;yW-w.n,&eHlBy&[
~l=`.l"o$WSlbA7YJ-`D^vq:uL[A
9FkXER7"H]FG53EU!%4)W<6RnBW%@FWGeUf;caTy`.KuZ}K[T2.v5W_5P8,-H!mItDT9u0
@RyhqhcLx4`n08//D1Y2t
SES*v5-,u!V%-oa)Y5YlyB8GgQ1BpmMhb=MP^2+m]Kh/CFtP~tUy?CvlFO>g|;h$0/sUxF8sx?IlQ#z+{$K98miP)ARqB/{d(M]/U,5#Kr&AKm5+;g}OZb4u-g"`6%8wFji]?xz]D6l=Bvxl{6#D@MlVrg>i`1rbohRfIMLvKf^cVHB>!L3v<v-tzmbA;B_"#wnK<8Xo%!^/#&UkZl<na?Xk$mLf,IQPNa2uG%Z_.1*h-QjOx23WbjQuf)"B8njd+JI>m)|_af*)7A]f|)#)|Vq1xZhWM$p2ksPU
nm;T")[j5gn)>+o!!/h0f)jK${qA%!M.;Z7xb+nqF|UY.vM^8
Rn+fiLWDY?)Z!|9mg$SvMYv*&&%xC|F$M]N}%.JA?[uPfuqQ>]sXk*kaYB9q03pm_TdQA5VWwe#u=4mi
+wF+XC
-x+Ax%;?(lFpuqb?Ya$`7T-7-;^e[6e8BK*Do6#/I6]XS~O[BBnHTxP5iMJqFCN`SB@6
IrL%,$[X!GN$HHUL)oTwuZ7oav9P0%_sSP`4wT*u+%fbIJ#eGSo>w58?`4EaoU*t1Rc<?iFk^a5B$n,k[-,!15&dkoBnc%)@Scj&r+;"*vA:a3]bj59`Y(-OX+tI*QC8Gt1hsc*<$s>y%>7`B[Uu
Goj7A:c()$o4udqrd+yD]Aqhg$2>]kq-u$RE*epg!<[,xRx$S
Y`&uyY-.WYKqZX_-me5?nhdSbKF[g/B5k,n:PB&$NY&$Oz=Lv|:j%Nsv%m=Og&p&"uF~8nSf<S?}[XY
h1l`Kp,`7=0W)ZR0Csc@G]=0.hP#60?[?_lXEO*)Kmi:Ut$-NDUQZC2xT?f>t<2F/*O897N=Hy4e3PyGrE%e%v#0s3,_K/w3iR#1j{[E0o.=Orsa4_DEP<:rZ!/_m44*w(.Ebw,hr
4|"*
OBrgtfL4~W#GT$B=!OAbkf}eU^>hU)6V<+^yq&]j60e+KssWKO.pb(iyWA,y$5&JkhsGAo>xX+$:]MsC&e#,*4c3JhtXEQ`J9"X7
3R.<bPcS=FDB;e$=t+F2O;F9c[ALYbbiVr1~Obq?N%"nW)_eh6J7?wvRE-Mnf?FnIMoe.??WrHb^$vJ7P_[}p/dYLWB
5/ctFRyqf%Z]c#Z#?}S~2!>%)R#H">E?n+Xhvs.j2=ZBAz%J%V$F;-hr(jeEBgK4m>T8dWh5!P#[<]jjXqC/@
bePoAx/k.oaQR[JgWgeBs^c/UTnsU,D13T:0A+%uB%iq344mrIF.DLq:eg"py>WV2;=d7Q*&eDA6a.bFqa](Gg(5(Q*ycP>Z("(W7a:MNn:!@1/*`Xps3e>)>r#1m/3x1Z_(Ja@3MVe9O+W&0+m<Y(;vO3yA`8Mf+/!%Lu*]kOisB6o/*#-U;l>+`IpsXo`q"^NjW+3Q
1&G-U(Du$>?qMW%%"X*
t:v0=%=!k`nY9T!dTeR7!7TE9,<?%gEr<5z-0CN5/nST#_K
@n)Yb_@4(WnTdR9GB;"ah/f3)T2W;0fv.Jj/J=}$;DpP`t%/N:P[:>@<oBO:l0Zj|Jq6fQHjHW3
u6Msp)-4LYK8IX{ktQC"[2fchSi3m/y6HoVS^
D>,GE.+0=,rS,Q
9pQmiM``OYFtbT<USrr6Do6FP[?euF0:U#OSO2=&9Z]Iwx_U!6!s>HA2!r`n/,"4rZ4bo{-Yjn&IX"T^^sd|1pFQYO#&F^0C5KN9
*nU5D-{pdk!i?FwNxQ$8
Q[U;dU3_N/;n$sKV(
]!YoZ"=yE{=}ylR6dtW!@/)%
jc@TKexk>>h;o^j9?[Ces8/;wT+yG3j&5
3#P$gl%r{(8*Hs09IO*$M9oiWC|4(OK[?IH;OtOpIHNf)F6B;=.F1;U+8?7y#IumJI|LN
)*_q!Ie,6+=w7W!NUp?QO4x8T`Mc:&]8QJ4<VICeTn?@0Cbr(0
G
c)xxH3:8F5$RBUl=Q9]5yTb1Nr1$L7-P&]+hwat%%9>KX;k,`,MiP^aA]W!_:t))`q+;`V15JIAJ?]C,VXJTmRF-t|:^X_sR1b"f&]eBL(iBdS:]6"`fkZASL2j;1ZPtI"C"4iL:AyZ?axbFq-GN1.WsCD1"Y2@wGnE#(/x*L,Xhi~)5&eoPK3DSN8/>/cS>wqoB;uUURXiWBd=o=HJ$#jf[+gQzIA-Lb
U[kaYhL,w[7DT[FZcJpJl/,I*{*dCUQUii"LuC]_s]CLq[pj_Xj9ssKb+=U<[XU;rQ)?DY-.1dI^jL>pLQK>_>x]iyV8fu
#>F+=>|#uf^Bk+DoL==8jA7
CQ).Vc2o>+r)FLTo6:jDNooPZ,-s)KLlOr79tZ53=)y
+338v"v+UV3dc.JwT310BR^ui%9-6:v.+t^%Aj
_G/;JQ2bAdlZ1MFxiEn$uf*wmf&p!y,#Ns;qY~(~h
Ar)+/SA$1n]QO?cbsM(/^D@O:#,+<F;R0Hd[R"OG_9%{91O58mcV8,B@gJ>x7=
8SVBLqMkT@wDlv}8Lv!k(:AKwqVqC.&[.nYSL^^T3kt@9>?&v8-Fw,+>ns94S6(9}<|Sn-1gFW6yH>T7"r[QZN!RRRfO7,oUY88QV<"F9`;Gmfih]U$!@3l,D]s5w8xcg2MuFh/;Ja[N)vI
_u:Q.r2
<r6.E;pMvH,Df3[xDL7AaFvI`lo-S9?=.N=w^+e,^e=GZy!o{ryegq1F31T`#p"y!Jn"aB6SIS|*
9V?I6>)@786l6T#cS`V3WRU!7S$+j[t|eT/90dBK"!ZK=gI*]yCvC&-BV$,P,zknPn]FA215R3
QFobe<@TV2S8_%i+Uobn?GH`1e*#%8rI:ZV@m+wB&)Ux,!ls%K=L/SrY,NRt-<pvlt{2m5g!S5#(<^Hy>4+,;>]RU>ME?W<ZR]=(W>Y-T)Z_`3>V)kM1Ey:*}O$MPp9`2Z,a
n$0`PD!#IIH8RunZT*xV;:a~VM8@9TNeC-gj,S9E(NL4v`^uA7:MxUf#w%;)^=(y(}&5_M+2N}z#ZG5`;#7*^PP5OarX5KXM8/4khN!+EJSKl4q}b!Ye2-PKQ57Ry%_F+,PP>._W5)x=x{couKN9A%sG%s]A?%"$.P@Tn+Q0_JNw=dY0(Q1#4E;,+$
_o!&X`(MWQ%7J-F/6gc-nWq;>)&qD1"vMf`HftgK,enP*k+7is7M%nuO(=aPglefem6./!b6CZM]YY(4P>d-2&,>?$R&^kS@kAPQYB5kv5C)qZk[<&`f_GI/,X7n5J!uws/K!8ac.3x$(pHVoyyZi?!MVp7"1vR%SU,Kj6SDDX0sX7FMUkrq6Yl[$fZp:LgN#6!R~FGmz$SuJSKqW3t#`iJ9.kKy{F[s:&0)lW3mA(QI5Zqy}c_Emr!n:PV0Ks9R`T]KH]^WeRzke.p?/^|Kn57mi>WuiHuA7#df2F%.D]G?Lk
Z1x{Ec6+J82E
32=+EB=d`_0C<$A+2d|-vvk?NewN9WTI<nK26w~mv)aIdw*+yt?RAn#5boy4atSHQqHP)C$P+pNaMcm691@I5f=TD5Z"}5&,l0r6hTA?LDq
<8dx1@G@OuP$3bQHD*Enw/Rmy.<PkX%DAVjglE;]p^)R+MRvGKl=XbF$JM`yf5(HHG(d")}`7LD?Ox<V9JPXj8<Aklt#JB5jDP#TC(IAz.zqf.MIw[Ln!S]CTDG1`m##Z3&T1jRg:[h$$YIm3A09CtD3c/yP<1iknobl,vx=O5IIZ<?sw7pi4^Y!p$)x</xWv,YUbQQuL#UHDR,^
:!bBMn<mO(@6p7MX!H_
6-:d1au^R{r1%|MuG*x(4"1d
_/Lx9=A!_BqI.nNz)ukq8p_LkyYLgH?hn^ljwWP;{o+`?wB(5)YuwjgTs"+o3B"RUkHMKX:]5mG#]0DqFnb$wZ$_
6*g{qkbiGV<h)7.,u}p)BhI4P2/O)yGVX>KGR,MKqfe3cBP+?0DYWF@DozV6NQ7@iO=Lh<87W0SDx:/B8gAgh<%
@C
(6}qKbIJXSD*z){VUoOkz)@,u<ikzBaNA@P2WBXm]bql$]1cWn[SOw?/9gApdfM26J,FopHgtt|8"8tbH,vn@QNSsKAuY`du)Z]JD0d=NIvRgL[R9t]po7`a7ejagAcuyruY$mTxUX{X"X{MAv_OwB,w$KtfZ7@OMaEXv#P:ROk)$Ib)Wf$Sk01N^A^@ixy`+lC#@#k2-=sbdh[;FChjII!+0S!DQj/N61A+nbsd)ygM._Wl963J{RiSz/_9L[/]ybbO%6n>(a>A;kyKl@@pg[;C{B8-o/5IX:>"
<$V<-Vd+lllQ%"W-(xyqy;U@qhF-S%*2w
ST!a4SY8nz.$Yu46CNjzy&=1?3eiAW,}53obm_%N7~oZeX2IV=2S&l_
WnK9SY;k@]W149PLVOb&GivcrpPjg)&N9&Z;C*cyhC1Wk<.$stOXWR:qq}S/Q(u/fr>`BFd#T`iR-"1p!iHv<)XaY[AUvs0nXtO%BFr(F^7M<W`hj~yUtW0SpDcoA]x+U6g<,3rkAVN~674idh"HR&!eb"d/Fqz&7a;>7a.Avh%Z>K3!5Np.Z_uIkho_i>m}0rKJ4.
2Kv1;fPC>8Sb_HkHH.7weB+R4flm#[M
q4bF`Cx&0H&BNt|!rR05tIm.>NYo*oDTo;
ezju)xq`y-7~O#QT40b%H<X>.NZ@xr##cn-rV]*8u?cwcuy#!8Xg@z!TyWe*QWHrSC7[
&B#7;A?_#Z]E87<?VL!tmH+[ak4Je1tMhVVxA2>If"Rku*#8"SEHq=rE8OA&#L
K#Vf3
MWRo0%X@9IvNSEL[8K&OK
ym]ZT"JV7jnPM8#`sY[Z:1XQ(=x6.eWoj`^y<,W~?8XZL_;~#TJ8?@d7_<goh(l~(YyeD/saS9.Z*-)n2gqgpW.]k;%6o*<wjPUj.[2ojpG)t|BJ;7t|_<qUh1Fzw*B)xRs(S8Ks_leegnqsnsRQu36VSh
gX[CbW.e*b-?sMOe)q@eMO_PJ[E#HD.(ta(Je5-F;1XdIA0,E2rmOWr`W#zC{"evP`qG{GhKc1,vE.X
^9VUQpc1Wllw^+$IMHhi{=N")Sc2N9eREIhRaFV.-r.2?"iO+3D2~lXv~*:HEkct#adnm-HKZo*M3O,nJDa,WIQd`<GZWLO<_;qc`Wr;pbiYt9/a.n!bH!|@2l-:@F}@P&Mfubb[cKfI3Z2q(;5ra0B5`D%buAD)ZmE8y)$(0J)$x7L*@j>2"6uo]xJqAw,S<
c/wn)OHqf/8U%qf-znL5xa!yAyhdrZ`4W6W1[R5>z8@LbXDK:"5_^d;wD8;q{TrJ,?)`:1dpACs&Abz/p6K"~8@`-/{$?6w)cZTLGR@xrG<6qQDK0C(-WB3<A<y)$1bVYkm6sF],XnJJE^;Bj3Sl5+$:)h!oE!^8:p~#B+%yWL{LH5
x97iNorV!2vh:A^%9OT_UD,u5O7FL~-%vOphViZQ=0Nrh?t(*Au$7hDiw$SCxP>Qt#]E$YOmoepMk_d[e"llO[S/%VAA479tdjU"L"6[/~@*Ct6s&ILgy8s1t8F(A":H2+p3Y:+O1TNm5|=;`}6Dhb=QUpOkNkAfR<4*r!`eZ00?L{9qBGv0
X:#`&9y#*D*X/eH1tPMc}N*<%HwDh>j2PjV6U%qp{,zG2,|M,j=;l+H
zpy]ND9.CLNPRw]H3?pQ5G76nA:t}5eb+-Rr1tpF]&t;L10]ls2TPnr&-BwuGhJRatBqGn(PAB"73g.5wax7nYmL#_!I{p*r_P)mz&)ay!`uO+t4.%RZmX%?LU<AV5~`JI{p(q|f+k4&)a})&I;,gvs_pFi5qstUiBhnQ,3URK%GMy"JI;tIM2@[UB_gAc@V_LW0Ow/I{*vF)g4bTLFjIH/i4h1m
78GX5(w-q-siX_a2bTxZuJEEi)K;?RM<KK;{s|,1W(Cct4I2*nJ]t5fQ_,:V7^mq6A4&eHn>pB3]s<nARyCSV^t#anZuY"?rn;GMjpw8Iot4azcTK$S2h!b_w/KlK$UrItHKMn/Mt4[zn?in]&Yf*)8d,fRmM~a}JtL}a>bUq
[fmRx[7Om2X:tYW
?rhkHPbTwkan`ITs?r1fHFbTwkan`ITs?r1fHFbTwkan[Z-Elx7:KKj<1bODV.sY-~5Ban!+8i_iLRtuZNAF$_23m+9}HcIU,4OTEKw&od:r`j!?BE`,Q{oGq+6G$~hut#eASfGMNI`no+^R^ehVnhADcIwLJ#vRpi?hR;Ou_a0mFw5|LK-dbM0M6$y<X$Ng
5Ry"t`jN
JVc8]XsRF=EVG069BkfHx2Jyjl`z_N_nUXX0^B
}LDAJwgmB;3l:<&yY?OvP]f]Hra[lvW8yk$/nr9GHw:-)9ksi^Coskw1
sGqKh"sy^Coskw1
sGqKh"sy^Cosi1%f@vnEX1XunaxA]f^1lznEX1XunaxA]f^1lznEX1Xu7
x7]d<kldnATEX-7
k0]d<kldnATEX-7
k8]d<klp6Y4sMnJPvULGAV_^ba4sMnJPvULGAV_^ba2-MmvTvMLGAV_Nba2-MmvTvMLGAV_Nba2-MmvTv]LGAV_nba7
MnvTv]LGAV_nba7
MnvTv]LGAV_na>->Mm&dv>xK@K_>a>->Mm&dv>xK@K_>a>->Mm&TvV&`i!5
DqTX]hU;sZRRWfsalEsa[tr>0!Mk_tUqLRnTIRt#@yh#5tHK09w&]Qq&W}a:U1LRklIRi%A
g|7:H;5hw&1mq$X`a2WwxVUjIQiEAXi"M<IL_nlxg|5TFu08D-]OD}W}`MUqI)kkIQ1yA$g|4QF55h)|11q$X`_|WwR$kfIQi%A$g
:#Ftal/P1QpqO%`OmyP^=IyXD+1[p1]Oq$RH_jUqI)kc:9@{Z4Q0MnG3cOq$-B7Sg:I_?f=+8}yRVyh!ahDG2>ylhPq(xx_8V$Muq=ISyR@{h!6WIn08MhsTq(W}a~U17sq>uWi%A~g|7zI^KkK"1>q,Y#arWx7sUiIQi%AXi"KvFu5hqV1kpqb~`O6t_lUouMeiA8nQ>/g{KjE:1[t=
,Z~&D_ZUm;?i`>OH?*_gv/"D|]_a$Wsp~jN_T?oAQgZIPF8@iru/"Dlak`1]Iq"TT_S?/AIkhIP;;@i0W1lD,akZa1EUPUw_kAuA)UfFhh"@e1z1T;rc1D_1CV#UmbfACw8fR-,mVfB[,<JV-.5qkUZ5TqMgl]Cy<b)v0cNXv%@u|9eb-N$yaxOAhW>*ShMb{Ii=8m`
hL>H),V<+Y
anLN;|;{12z%%Vf/ur<S]4boDdcLhYcP;HHuEhi<]zc:kRa;vuAX3$T4"Tl*Nu6GDHHM/ZmkS#7Vsi6tng
N1(g=i#o$uiX;n:T4DM+ztN)V+7%5xSv@>n"|
BU(@)(-?Wvp^!;wa+Y8Mx4pYspQt|SZXVk^=2Nwe[v#sIPbakv8uj[@E!kE$mIhqyU}OJbz<G="5^u)@hE86P=f5>jy&aq,0,QI&V?Dqj1I4R2L<>Mx#Q`_M~K_?vicmXe}MVh|Bb@`rOr8R7Q
8omy%o^GGnfT8s*sY}aTMLwS]djEU|]~e]&&:53W:(,KmfNK5Pdp`bG#*?miVkSQZ{i``:%t)XI3Q|.7A~9&0mw_:|5*g=AcmdJ!exB#er3$K#Lq8Nbk)+8~O^L"5d#mK<J":c>KqcmTB^g7XHakunt<EIX
.:mWDFFB<!f%7EAZG7&XM<8F[huCVzJEB10__SQh(sI0376=GjcqA)LQ)ltc<(QqVxX<AY:]B"R9
.;4(]>4T6gf4sSS,<Nv=Q+~3647g.:!,ggqi*u8py>GaYwN,Z`]L.b(Ums[O_,nOdQ~:g&8B8_{HMBPW_L}Km%jl,6Xs,QdgQ,7+l;
n<%CK_KEC&y%!C`"fmnP"Xx&VKG)buxM
(pN.,C_!0t7);;Nlt?Kr(YC#(a/T{;?%OHrOaV~M`uSb#$q9F&rPvLt8%,QXCZ|VLM@s@?r[9JaGqU&
5;E?|.=^#,^KW
`y::-!Hq}4v7QBz0_SPX@+~%`,ZgyS9wNIIT$S.xuiPPefItn?>
yvMx4D4j]e>P!Xj+s!/.2]7sgQI.<^P%qbUq
#f-Tko.??#
EXf?>n[^&3}l_N$+4qk2PUL2-tzN}4gk;g%eQs:4`%IrRQW(mx$!TqkIS*j
S.3-OY3N4+Z&3
hCF,8[I0xJ_Jy(eI$NaY~m3,]#h5$6s>w
-^IyK74S|[Y+0L&K2,uD<+^6Tk:d6/v*+MC,{6:$3k/#4ej^h7x2G22.EebGoP9!F36!pk(1(uB1IIzsdh&
DEi>swE:al%3%4rM^YCYe1q-ZS%6Cd^pRVRVNm/+A?=;-3KZ/=I9."KYGS^=/rv!+x<j9%8B1(w%=!Aj;r4]w%G<=aaw@-o^YocwX$@(!V&m7xOuu/>vNZZ!hV@#M4*HSY/1+GER)L)wy;,E70<r(S:1QIwB6;&6$HE5v#UdL_sd1>E7"(
/=!o.jKJoG1E/,Z[SW?>P`EBQ!#7Ku&NbC;EUA#E59rZa-0~)!;:=j"3N!d"aJfVY(L}f.AZEKWbSV=78m0n/)[pyL7jhwjvRFDgu9UH*itY7GPO>DXioGehj-4e=|-=]`R&]N0a)S!9Fr+E81V?KXQ"w;kA=CuJbcr&z#&%)"AppZAB+$!LPy<4_ZpCG:,DO}7dijdwY[oOjawU/%CqW&Q6U+<@_Uo0w:1uX$IJ@$X*:M<f&a?}wQ.G
$:8Usp7t`CwFC9+7_bcoN6&9Z!{LKeXP=r3+[>D[e+193_Lii0+AQAKlKtkx(9G.S%~S1B{8()#(|OIhygVfV&Prcg)W2Dy#a&6B[3N$@#2>g8}YKoXVLih({,1xSRBF$Ld9W*
o&NV:TC6%q@~(@F&_N-Z(.Qt^FC)%b,TT~E?)Km/?e#/tg7^vBu"Ki0"E)"49H`VmJjT*J`V4WOetJ$nctqVFkXdu_cl2p".x
P]3]].:[1vpK9OCn0!0d35f[uN9&V
t:a?c
BUb1MI,e!{v>PuNfG%Cm2b0k7]%<;H88.:>~;-R48?/)3Z]E2m]384EfyprR8C&>:qg3!)+N&sY>]@aS-oXhc%RvTplt>FjZZCHzLoVwtDM}cQ)4nUQ4l.ie?Ibhu{a=B1^WyPh(NC_Jv[:B/"X"Wn_*.qGDWf27v,B^A2/o6m$bHJSc3exbA_^:/9@l*}E=vOK-w$R-)[J)o3cpi)2=%f*sNG/+u)>>Gq@y-5F8@Y-<yeM4$snL8f1[Za+<Dv3Sa<Mso*dl/RV/+e3,qd"V)GNgX,x[=%,dpH_f3#oBbrnJKfU@Cr_CX~[^og-*pCQyt)PwK#6
fPdy$[W8ehL#o{Ur]fb8^4oay^Ua]*:%.5.s8oZeCr^9rV*7hlBRxc;%^-vNa-<}40cceNUL9
.3ddwO2scf^JK;xh>K#CC64/4bA[$DM(X/)~eoM.@,s2aX&H,1y^a
<Vk@,+r[?)N;wLkXZ]chQan6+w;/2n$)5Sg#p+M`6dj1iL[?IHIqIIV>rZ2j--]DJDV?t/>43Y!BZIe#';break;case'icons-09002f758b06357f2d8f6171c006be60__d1b8a450.svg':$f='!n1FChAWz1*tCrXP%
[XdY!A5,o%0f&vFT
H7Yte1D60
jJIHYvMv^Qn_I8Q|^>XG)=s>S8j,.B.h=t)(Bj*9ytiR`vqE!PHC,cqjIS7lP?]6rp7Pw"tUuW6uY$L*hoz%vPyft9SEj:7~PgI-iPs4xUt3b@cty9x!z),S+zXth:Jj5"qi;}N$w@nUqinW?Hd!n%czf[s|z&oUkvyCmiSttRs4w:w}tvH$&?_8pK[L7xxAcd%qv
BTj96gpFmjqjIU=t*pB_uoi]5hqyG$tJhHL+#VBP^rd2^=@Fv[S0[(yBKKr1.cT6="F9GmM~vQHyh]<_^&1zy>)lS6L}F)=^U[@6lWFuA<:]
QA5ug!`^7+=g{Po@#VE@X)Lshi4c41Qr|myNL+t4u-fCq+JnZezn7;Nw<JUhzo(9cnhko=f!*hrr88=jy;(q*CjDEncn>L|lwe,s8N?Ei7%W=iTND7`A7&:c&^5``=B5h9DLTuJAP&4I3mR:5k<!J
1QL_ylC]G`3H!V,gK6|s:mX.>2-a",lfIrMi?pD?}y8EZ_
ObaKc{ExGYqi!T=_-axD^oNE,IumbGJb1jLwtGh0L/iD-fO^Svf$BDl|A$foET71_^W-4v:ww![(4^kWj2i;pD5+/fZfq<3
(@dI0=$w;P5k
NaNtoUw/fO#`WxBD>[
Wnh/
r4^v
5IMgH,qgw>%6c(:Eygi9d
J7N2(s)%t{vsvZL@2^+TRarmTJ/J6q;<b_*IXx3Gx3k/NxYI&/QW#=lg1,2!iW(bdB%]+=EkOyl<5g-hm=lw<3TV^Mo$JubWv]M@WfB0ol*zj8wE6JF5`wuH,=+k#^BHABuQg_s!_}F1=_d`PsQmSJOdK~7P#A;8S8,e,uiJ`zg#2ch2VW/3A2h(NaCALN0Fy&mjTk6kC%DXT=6*8WU#?Pim
h_MX,vZ1|4r&GRtldRnbcgmveFqRIwQNYWI_$A<9=qd8//Y`?$M=To_3wcqVo-FTbNJ`G+A$oE`NB@JgUoicW6b
h;HuWk.%/+PC`4
CCr(IbS&7c&)C;Lmn17"x>%aN38j!kG2igr(Y{xFZ8#WR[Ihl65v-0-H9583-,T$J@52
{+?@dvkYXqt0fE6gg)D8*3^ls(I
nxY0hY]l2*=mL"
DU8qtBLuw1kPRpLR#9_1%/H==zp3?>i;uRfayoXaREiuN5m4$47.Se.ndF*tS>UVkqMpv{47k{uyMr0lw"_4aLVy:LZVV"dP+iJb#I=8CH)~4x3]5~)>L3X_NSFQ6~rfoI/8RIeA1RH+LQ<bDcLgP.O_p2EsiY`pFK,PH
SfdwNB"kL"/@$
9[ld8uIYdx3_jl?q5:#4et-$>Q*I[m&7u3^v[Ta7l2(d6X+!;[/89KZXEH?y3/4c,RQ6?V"(Tg2
,GFQ;V<8h`j:I7R:YTjD=uA(0-%<@IKNjv<hf_Zzk2gQ*/ohCrJPNA`4Rx.i>{p
9A0:0LLiq`-O$0o)M[$[taP.A$DoM[0YmJDAV0I}Y8K9fnL*VdxA5SWI)cxO&pRl#f2src^gsc0fl&1p@Sm@_S#$Cs.u4uL4yvJ{&"G<wc
S6G$|^f09;iY0LB9WT8YcYe6Q[/5W"ni.liZ.xP
ZLphY.qTCp0u&=L!}McjiB[qkcv]g88`iJH-&BI(|*^r(7(6:@e:KE!b&TKMZOMp{XkfcobcXUT.!D<
=U*uN*^y<dbd[
4f*<t(s"5l/XWeEyB*/yCAg![u8CsHm#(wtAppOUm$T8I*tA{c+d~S%#)4%+bk8sJ1vC5g.1qU@Eo$}+0o`J]AtYr
MFQFL"*H/<)Q!?|yEMrM$%r`43FNIv{="KzX6]~M(?0:eh=v-^pF{e96W-o`1`bu}#>!QRn6koA[9$:4&EB31<qQ:[D$o7@s=cQ.W;(DA:a+mNr:K01(D%82bizhzGfd8C8#6#so3,.2>"ejvO!.>">)?P0K&f?55Mh!33<!y[=/("s,=_,u2AY4pIg+nT(Q!z&Uy3..ge|Z>ifOkst,umOe2@+a:9p_&GO:p.NR%IS7/O/wl.Dk)s:R
HW&Skz]tFk&lOSQ)Dv,_[0(}0|jj3BT
/Vy=p?uxnANJsRMZJQl#k|ALFxLWG)7w?oQmF-M:B7i"`9r/=#w55m]|@-MX
Ow
U[`kw:%-c`G-WsLH3:=mE:&"d
5k<ascS!P$Ly;gALNgl31E<h$2ivlgw"D7ZV4J+q["EL
[(L-qGB>)OM+/PJQ>>ZVq%LHQ.e(uJg8@(`G=AW-|8qN!]$%N4Wm#V#bxDkYY!q2f$$Gq4<YJA3)2DP;?
;NxMN`4H6/M),<#Z~
Z*1P7:tta&@mGlcO.joQ[#+Ap>|&d:oWa>7[tpKg`U^lr;,!}[.FNS6#<jDZUGjiMQ3P7=bSWH:Y_#SQDJ8G!pcXkvD#eSHx,Y,)on2^v/At+]WrOP5;ZSeq9hQ"Mg^QrdS$t[(8b*9a*[lY{2hdIO$^5Hy%kv9.!b{
K*JdN;;Nm,+%g=;OWB),kjhK:%*!|pW!u*G6A=lx}pCf{>va63/YWg8[zpkFr2Q
cR<>LFW*VPurC-+7:&>h2w3Sw39a<.)BLIoYOT.)%XxB#3{#o7A90<PCD:O*++,n1/N5n*qVxA#m=>`#xMeJ::BpT
.QD"b`5lbi=orGz,#T@h-ijD/qT8q6?a=X`_UPFVGF:hUT"uiKM,ako>DeQpJ:swRX#?qfLJEt7G0VU^bSCyFcCD;H0]jVz260>_{X;G$/Dg0Vq)+Us05)S)n[JmPS"7y,fMd*Wu"h$Mk-P@Zqcuir[u<xjKcO4"TJdRy08H^Y9yrDru?H_[`
Oi_DTDOw83g^37|q/)VO?&<S]hHN}(Y1FWOC-c8"
i1p]H$v,-c`j]2ZHYz.p-,QO>Zbz#8dz
^5mib9#1i2I8]83*F8Q!%U{@KDe1{G<;MBT>[`p%<(eP5r#O9;qF(g@I*E+6
!aZEbAZm0!#F7Aj#X|.cg1UA=IRQ+HF=c;45"SH+EB
fCFPHthhL!j$e(#34CH.)>kS/)bN.
t"Z@c=B;w%%KQ)K)eY9qZ$qR2<y=5%/OMDLQ]M#Di=)G!e?yELWi<gkdErlZa^vQIYl7g(L?n#O6:1q+@K9r,R6lB^j87*vUS$eK0)2n9u
Y.1<WT"a_!KKmQr.@:YbA"?xK5DU5I#SZ;9%LM[G+lP30k^E?K.*2
@Om,
Vtak>DD5,7R&Er`_<ifX"!Nondv@2%T-eBrfU<XYU!wOgBlw}c,a4D!.2<wG/_5`.FXBI8JIeS$)7FKKm8JAnd-`Z+!JK>Bl7@D*X2;bWED*em
Ylsu6.wN]!J,JzURO"ELY"?ivWiFN5De*X-nq!(fXrSKB>7o?tkIWoL)]u1mOPc-tXSK&)gM.@ZTlq;}b(_4P53ef=puvO!jbFlz!*<Y$"Kd:[s-FgmwJ0G6en0oWq3G[RRz5$x/9U?<_DS/q"+N?*2}>_jpM3ON;X1J#wi!v!d~SmV`BHr%2|Ppq;-]uQ5Zx{vSI`1u%oDgSf1MZ(kFyS4z;]TS#sI@AJ33T<0C]V4-D~#%p?$Kw_6c>093,(moRc9+
kUbYK[/2
]X4/4z_m7[[&=A@^h,r(c[>v,5J(],<$.TbWYM?3OUlXkWsFP~*Lp~2
:a&bqMOgJ^@-adFksIlt1
m|^fTbPP$IIGQ%+G-
0R"Eli0&KpClKg==P,pN^RuE@?mHf#"a+u.dO;5AqX*[X74[dE"y&:$:D/_JU)E9h`X1ERLeAG)EpU<r.i93[N
>=r5!biEWi
%:>p3rI@/$hUF`

Gjs~U?YROwBW&W]Z>)<OG=kJJVM>$&mX^3b}Xy.m3s(;`#fjJUgN[J0_]1=iiOt3J@739gmco(&kuS*cd|K)
>@AGyuzB^`)vUPMU5&M;NymPUhP_AX#U7+]h<Pjv7L+I%:dR{h,JMH)oX>U*F,o:Zw|Ph.*<MsK8=&l#D.j2{rGTENnGtf#&v1F=D2kTVv}Wu03;TJKk79?2W-fhW(mmG.y8s
E/r@n<SRY=Gofsgo)WwYG]0Kmy[?6ALh1mj`{=nD@lxi2C,)lwArgSu:#;r&%AGa[JF66
PS0EXg_1D,Q[TLv-A@~*A$:a=W!Z#@lGzi,ph+=TMY77C
T?TAwWOJz?1Wu6n6|*m]aq>Wk_zw[`18X<mq)F#YlXX:-;xE|[1]BDY.n9yUQg,>1O0?Q<4Kg@oapO?k6JvR
<(=^l.rH^=srd@Xa!kwHLY:Zrx/%!n5(Ywu_vgVe`Y-4d
<*79!3.:v|0=c&Rkq5]|tS
@CVjY[Ot:V})f`;F/JS:>_],XH&KXm.!._d4W5~KgAyFHOK*yv)Bfc-hvvKd`mR.9Lbjc6%*8@ViQS-6D<Ncw$Skp/&atl4Po$.L!&FMmmS[E.BisizM1h!=fw8NKiS2~a5FsbfyHstD`?)Wh-=u#5Cp,drEWG+H-N55)A*#^T`RBe7])/uXeB_)O[U(g
)sF_%u=9zE]+aq6!BQrJ[B#U@iw:AKQ5]B(,M*75$&S"_0+/Uiy`TN#BI;tsZ/
i?QLRzql>yI$Y8N?<D,tq.HP4Brhg@ef!<B@BJ`(@@Dj@F4Jt)/@5b6
yfXkl!4B@uR_x%p+Y[M%@)R@&LE48h6V-$1G^^vk1n!4k6k9XT[Yw|7Wg1jtYe$.)fjrxWWNDp1@p762K]tS`oHH
y$8io6.
3A9>5%(-sB:J$31%T^H?du?TxG^t27AvoZYfw^DYpu[rq7}uzB!z)fX_qJz"ZV6?(7)@13;G@g?=-qZYTy,I5YS3^1:XkY<%]*e&?P7l?7Qeh@^>}3EB?h=0:v2<CN@&jF<v`*]T<mFXR_D#rK0vWfE[Zc.bq+9%p
ojvy~JSZg/"I]<}nD,YIaC!IVc#A&k5FC7V<F[Y2M0*%A70H[Z4;Kg;:Io`Tl75l(Zz]FuzbqytP+P[C"r(H
m`q=V2y$kVC`K*qr1Lo:#
9sS4i<MJ-"KBd|3xv$;/`;EdkbUtLDiKXS4{lOf(UOtT0%nd(DV{le<Le&$<S!3NqZeUp>:jja!~r=,4(Cd}2u.sLael4aCa0bHd[kY+3"wdq:0$Z)[@T-1E>V+V:Q
zLx9NhVfydtN/4^Ls?}[$(oZd<~GQlfkiU$+VO]=!X5c&WNYOaA<7@vix^Te9al+Rsy%Bl)J^,!mkkdL;Y_;Ps}F^g;.j.0W>u^!*
-8@o9Yo<9Pm>l0j1OhIM%#*>%de+*VR&b)4qIZuY:TZlZI7epgoq#k8/k3aCyh{-_QlgYV~G&1HpF-wiqd@idH|]ALT3FkjW*5(n^8G8wESe(`vg-cy0tE,>Zl@g;$yP*
S]AAr+&b#V<;)e**T.t0eq#p`XJBDh"yN99X+rlg(V:$o]5h"fAHF=XLQ"e!(DH`z[=RVf!?L7yU](-)Fj;,6atN)wG`DRf%f[?<L0/=#(HM=Orr1DJHyL!ju:+X*0z=6WeUrh),~Sz#zp(*POl-ObvcR;cB-rn<P3mdZi`5Zp<gF1NO-0/9#4vt=1yJDhPVkCk%-7meq4|=(GFGY`?"%A2^rsaVY3=f/;>PZ>[[y9):<)yZxSJWx1Epsax*4z()Uc4sTyus
d%=.2Qw3Bb';break;case'default-blue-8b1757b086ef0b91ef798cd88b40603e__9d9e818b.css':$f='(erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhxe/CTC?saq5qsKQ*LB$<b.0;*%{a4b)qE6WmLxHcqkHpxcO3?JJQoKJQhn1](K+bXG]GdB|o^?CF)K2E"Ff?k_f`{eY5%[jxmk|UU
_lAk[p~cpbyQFGd0kRI@7bRfIG~^Wp7

nro$!N`iR1@jK~I6S0kgrGp)jhrG?Tj1MM/&G}xZJA()Jw7
q4),:%u_@hVWM%S5W|qtF8FpsmDQh8dM&$]:lP7s
&wC9`H/8~8LoBvYc"!yr{a25$MY5|x;]dL{on1(b;Iuco`+M:DyiJmbIe>~CIv7uvLOHH3za|(V&cW]S}k~niv!EhCuO[lMZ9Df[l"?a<nLF82jYRy-
=-R@oE`VRy{250E41,y[:K`V,xS+LHUH/Fbcz>
Dkv/<T1.2W#C<g^>o/YI)RaGHzv:Nd"3`MoUY%?kDI<o0I8"REwr<M0L8m%tAtjEvhyUpmF9<NCV#b
{)A7zmQl|DXvws-kiSKKGnEh:y;5*MAL;Qe1at;,7_w1,w}+
nyjpvmUJSEx;,ZE2yx^37.kVMqF]H^/h5rH!y&n[H"d!maWQv}May4S-]l9Dz&6}ymyL54F}4mtQuzqMH.tOi<t;yvny
!X{h*A^]YiSxk9|z!4/@-tVcdJio"Zcz)Mq,bm%N"uaSRx.,cbTAHxZtwE8xSuzw;M2c850w:yGypyJz$>sN%_hMr=.a<utWU]cXk^R_7GLSKK)m~Y5xs_@qn&(1ptOMm;Sk%P
JQhU>SEJoN5%N/j6ln/q20.wq/W^yL7aB(gW40IaG
[,hzaVh:0%jg.1J*_th|KE@0:xkzDmye_.if`aRjx8yh-%tz8/ScdT$hOeP),:T}ovJ#"u)-&r)!d/H.4C#.JIL|4*!]f@"o"W85,GThIm.6#@glD66<cXm"*7AvSrw5Y*oDM,rK%yuo:[jfP=I@l$UE1vdmuZ4IiflhooIX0!@`+O.sR9-oh_l8T1Y/+o_[C7^`V2_ep?5a3Z0.!Ua$"Plx^fR2Ekc3R8Ig$2J);Po)&vL<m7$@Agn#V>PHpk$+0tR+M%!mrU_C$`V?b%m_UwsU&5tkl8I*sN0g4}^bL
n<M].,"
YH^cZfsV#x#d=@cji$?
qL_2k%^0M^V4g-"M4ejS,7BdxZ.o"MSN_~*hg%nu+a0S4JQ7[(3*r(B7npr(R|
g
ztXZuu&R21v"9/GlF@KZlR[I$uwBxPal}9L)k*"xkBB]"eo
qAoD1rxn;<j["5O7iurKtDo]I2)RY0BXa[MJ[Rf>OY6Y2jrZvvmfwRDFU(#.s2^r*1d4vX.Yr;!"zj`J^fQrl!*"":k,fGZ`[2Rf@AO5S[!u~8ZRabbEtSN,/UF=3*R7KQ)27$ZQy<S73If]c0g4PA1;>uA!~!wy/j~?_d-e}$"O23]oo>ulvhgvSp;V)@cYK-[$--e^
UT8se{-ww#8Re=N>N;sfOMUVnW;ngT@iVAPcdZCs6oa/OVX5C[,Kuf`FGlh)HkTG)4[d0$*MG>45GHH-(yO#1C<mUE0js,DHO&4^dJ+{5?P~8wHT"s*^-vMIV,)CLlRqlSQ:K09Q3:O@H}hLDV`t&s)AEY&qS^<Yf>wlcZh>7;o1@/^%U=CSGGC4:uh-1KUs9hE
]+S|8?nF$&@fm"[M@nWSyl=62P%vBBERB7rHy8HL.JZFT
^Se%fO2%1{<3
Pp4:!ntgQQ:6Gn.+,t?J4b1A.ZLi"o<3L
w7iX}AUy;M.eX?Sw~&{w9tu^Z3hPX7M`$/W7G4wW4m.7@myU{+aQ.KL[QmQr=A"]SRWN4`@7^jEf]j}?^`}+:QvZxM~?gRl/GC=sBSdv.1Tt<!JAr]vt^qFIBvb%qI.^SS
]8F$+>N(yTL!!
DGn,i@ad+^jk3+$R:cBI:+em%::D:~F-*Wm<$a=Nn"f/ml5a0TS$[&
!36N6mG%B&OQ1"k9dE0SO%@alt
.&:Z>PN~S^?Oo3sT`cSW,]5ht`
~dAn1PC^B9&Zj[zvjL,ki[Y><Dgtn,;Q!@D#d4!FhgRbM6~X?WHo@c+C[azOxl~Uhc2e:.n.x
IQhQ$Z72v5U1~))(tHDT9**%oJXT9Kvhu.@G@E+<K1F=]"jH:w{ti
,I,g^+pCDNski+x$pOmlB=lfg
@H{O.v7+x_1RF;f;9QB$+Y-($A.iV7H<%i)wC<s^Bc8(Lf@RIM?U</iQ&"<Ra_"(13<`A_P/StqHr0<9VN7WQ=JG42V={"z&
#^-HQGgPHcC8CJ:g%!O$^nhpoX=2w6RTh8FnZ-J<Qn*42{Iv?!3Ji
InV&F5(=d4VkCix+o<k4
|
o%lv<KL74)/oeYf)2n8/S%A]=mk!IHz,jsLSJQp>,:D64>_@4O6
=(YO!Ca-G
y=[-Vr).hV>6v$kS]+odV)Sw"@e>e/T:+
Ah^HzSa/fcO6"S{cD.?7:v<xE$]`Ys^$JVOg:p*6>R6y)amCx
<p].F8zY{Mv7U$(jUXktcSVJsm<4O9:hC0S]kawh>x+L1]`I7`Z8K,PI^JU&f;B.ANSs=iX*`p&lx"_cGwW7u*$x5x##&*-qoqlAEc6s]f}IUM8BTk
J5Szk-ean)7`i^_8G:Ha1|+^CEGl@0i^3c,*v{%dlc95HE3lY900s9_yCbz)V;X`_|
pGU1
fB9WmaB,lqMq".Q^Z[ab-k4lM/nOI-;?q$uPj@w.Nb]H&}L&v.lq!iUyrC=iL$RLccA#rJ
39}#^
;*/[Hu8dNKvG|=M#
Qs*X,v1e<Z]TMGqTLTG?a@m&O*A]37yAU*^9Q=9eqyf,Sh^$O.xiQ<9ii-h90PTfSU05)<:*pE-faMVJIs%a@Dh[*4c:V%^Hhp8;s{KkD5L>yFwQc"o^v,Q`Uj!iX?mclF)
v!OXQ&(0P|X<;N,zN
d.0)-GA;j%R88EyX=]i_>yl1=tr)0=kd0W?f2V]WQ}olD"E|Yi7y(ZuIoTVqMh5{T8P^h=eeGx
w":eGsg&oZ!ZZZ~9*J[m8-P.0"S.pM}><YHBVVM>KJ"PL,7H0Q=C8R&%1,EOtpFOmN-WxBt<*%{;nxhb_MB*B/6xtxP.E3ulU*IoGH&NfK<FY0],ls9[dC_YT@N-gc<gdS43_=IoXxRPkBuJiO7yymH%kNzjK2v4)Ma)o321=YQqYO6d:Uv(-w]XsLB[X>2Ojmw:@M
_xymcdZFu(M7;NGv>Z>H6/jz?&=!E]sp=t0&X0a[[m)<A:-WEr)hYJ^v;XfN/_K-Mh3adXT78th(TAkID]Tk"Yr%.:OACI6+`t+)i.?^Gg8|[3b0?I?H5LI^V8nL;&115C*302f2.|1A0dDvNGm?
I;_NB.15+Fzu7oJb~kegd%CT*VJ+`6/nQ,iu-)m3LIoe%Z7)T
*-7nwb8+@>v2:o}=yEoO#i&hT:>NO7$Rf@K%0kw9:=8N@3yPsG~9/S=p75Io6$!RlW|"NbO?1c@bxIQ=UalT14H1M4E4mhb4JLu#d(PXm.7oRY"?HA3fg@svL/gVnfga~t[9Rs$^Epih7uoWbg;4<3B-{d0UV9?ne+mH
q~w`s5;Nt{hT&R>g_2Cdv!a@$Stj&j3P&Cu]``3v@l=|3gAvR}X2khutH:Ey/

PQ)$sW3RsQ,`GWn//jr(~?+B2PQS{NeM/R@L$f(5}8q++P.p;kEgs8E1GJh"Z+es):r/cN}E.WGe_Ej5b(eDz7`Hw_1UJj.i]bzxVuf>:3
kHp6-$R?6Y&ejljcVCjB)(J<jmfX.Ycc-e0f><DUHe!@kK)bQxqhf*wh/Cia57v%IB.dk!u3;<I1->?K)BTYJNrs#>8Aw:nw8_cV6<s39.J,kk,`V=,n"Bh}Wx1.@aTx#%)e*iXh.3wU-cpxRg#D-2c
7&RV_
j^TOR+[X)yMVjfKG.~?Z+SdHH_FceuO&nQFs:%HBH
v=_pv78)bVQh%PI6]8I^!q]lusJ.:">}4h&,s%3}hJOxIy0@PqVG)~ra<]U[/qf#PP#E5B>v(T/[U1W
Vt48XRBbF6*(H`w%#~#evpt0U
[ReEb$4b"]9vf$!GiXk9]-Ms`%m"iRxv3APl.=HJy)E5i&J1S5FtFl8CQ;bc#uaAHIX/m8N>e&2JG$uuxTD;vYJ|Cmdq!f:j[81q0ryjMT(JF6DjBFvh[
0p;O:UgUs"FJ/y
,nBc,A{*WOeKTuC+;nHe|)&({I]i"rvDmKA33&-fs3W*zij=SPT.G:Q=w0(>cd/N25Ig-)ZhWV@;d?SHY^:$69(Jz2Q!st~CE"n4.+S
uIMdG0NG_W8L$7ZSSgBY(O_5a)rN<n?H4f.EkvIO?w10t#,gL-|!8g$8;EFY~S2>^GtoI1I)N0:GsE:?C9S"#R?>#!rtdA5@yNY*kb$a?YPR8DhqO%
SLOz8MF$7<N6)]EPXQV0Aht/
tVddsOVC~#9d>C_[?!7tE22B5F>f.tV3l`tO3<oCxln_<fw93:u^9:-3
!suVS)J$NS4/x)Q{,)KOY3?P"eD_dyL4i?G"r;[zA{,fRwZ.MgSo7/!JVb0n^k-qdtBj;5Q]5:k^<A)V%R
2ccc+$mC^PpJGe<%cJW74do]E60y/`D6UP@Z:"8L`grXV.YdQw*6r@SywuN)E!--pOOP^fd>KP`tnb]=ap/&q13;6yIS>?P0^]LO{;p5=e?_#+XO|(%!triuw?s4}a>tlO;UYY/MZipQl?=prYl%,=(^%aB5dw1PXk9/t.1.csqw%t(_.Ha
Dn#?@er7hFo7Al)(ABk]Qt$&de{5}Y1Aa*=hnS6GTV@RH=hSmY;iRk?+XjQ92xbKIx_N:S(y*EmdT5o3FED6
w`.H8HNXDvIr"K76$H#lTIf-P~Ak01.vony:qA_B@Pmc%Fbj224dilaV".l|yG0>-].J?AEGgbR2.*9)DcAfYyW^4@]GSJ0%>BH(O?m(axW9>#[:KkRW:>Yd!R"_Y(I&TDj6rcrMCNOy&sd2wYt7qi^ZEdUfP/Ga*gg/R#5Eqn8@a2M,vd%r+{Ds`LN7BZ/;jZP/(6&2.0;2usv(^/<,M9*aWBV8%d1I0p3M2Y=7H"pL@vt}%~d6Y|b(:4mE+Gc3fs/<_?%7X=^(OCH3BClZ$r:@
:(1P7DhlfSax;&3Y?Te44A{.oedpw;pcE.44=Z-kA;)Q[bM8@B;n|Oe5E"X18?mQR*sT~V%@+m+x-&m(,+vD9-7sDC9W$Jk-GD$WmDap<p/i{V>6/)(*xTSG!#"Fp%5-LYYm&(*6&&IOPWvH$>qc$e-4M=.8L(0&-nnb?lI(9RHUy@]jm"`:/w6M.F`HQ3%
&Vq"#;Q1+fDK3RU`z#O:2i<-Un
CKA4n]"j9"IV$uHbp9
-+D_2EvU#D8bFgl4,lNHByl6"Ty*2LljcB*fHs3<er^SN
Zrr;Cq@ovs-r_PmDO,}Iu`Tvl/n8_kVm2cD[
4Am7U<u*P>?XMcZs0qLFK4jHJ?6k:J^AcoRm@*?{d?Q23eS:#lsYf6w"Q&9i2Y<"8AP`roc-!n6x>])S/<mz_74A*2UHXLH:Oj:GbO7DU{IIRBgWpvZk#gIw=_e"uBA/(I-x.IMwfw#l$u0-:$P8-=0-)0`~hjxPtlkFcW]z+X]p&jb4I/;:YOUUk+2_(W/QNftR(zuHs[-S[eosL,6gnc%A,
w"41x/t[u$?Vs7mZfb,hH2C-`7?
X{h.m;D(J/]liH6IsECa$PdFk5881%P{^d8^pcC,fMyv&G+=G[YGeS[0.E6N<N#rr}*L!p`1F0JHM6o-h,SYV&]1/o]APM*6XYEz8@I@gso;>9NFUk=K$m2JT$Ti?lT8tD6E$Ct=$cIuEZF%C[7ZWiLpJ,?8xLs&A;1QgKV6gH18JBNoOdy-h0Sv)HpwT4KP"d;d/Uv,+-u(3!n&BYt),$WyBZ,/[jNrev9
-:Vj/ncCo+u_Hw]SBMkm,v7BpV*dnECBJ,!4nbhO3R1=;G-oN^]K.38CfZ3$rue%R<P_7[^ZwY6m`}[UQ:]{X2a^9_4>.S,U/5X-b)O&8Wnby5@Gyl8TAv>o#j:j_o:s*Tsud#4%r`KWZDgi"jG),uP=n!w72ZD{)rb;fN*aI_c6Sg#(iWgPD
foxX(]*AfJOL)wY>F<>#e>Z2Q=ugG>B"wB]xG&ZvL"c}19.L`BhEOq#}$cD}4F:1^VOI8,n|[]c1PE6pJrRD[Wqg$J@&-}2/u][@.0?&>CB[vkd|DYXl
wO%;/wMYPe6/.Rvq#!T))MNXhMPT$4b2KMb$ErSq4UuSe2{_cq+[?a+?foG&;N@Xs$bwQa.MV-,r{+[t:@*]@Aee3:FdZPI:WQ6ZH+R,2k6c+M+p|8YjMT(oQUqgGdHv]z#*PR;dO8DaTd[YlB3w&>K@^M%,uGK$LUb!2X@MPYwK=PR$8dBrE7P=iSV?G^[(q7*)|O`&@*"[0nPX42Hv,Z#7L+&2{vZjV!W]w1g!u"G1WDN.`U1[x$)]D:Hocpjd_3mtGU28G/gy`N>3,`.*}ZQ^v_):1&_fR?M+Ap?"(Q-r{.xB%gI`l"D>0C%&As
-[lKw7#xHK:?tq&y[m_PiGtTVj,n_d=`$[W5XSGXKOc_r5@!NTc3.J*2w_N5
JRtklD.<X=@d72<SW
_E@JUp5d.I#_&qTF=]^*Yy$`)R,"h=A^;3_5$HG)t.)z"7!z)h?T`I7m|BT/1^N!2)J":7/p8h/m^D#tTbyb/RPLnjX6L?wH"`Y#VD9@<A94}P1VUd$3|ro%ytN.<!_q_u$f3CTZ0/t5?&xT[;]JGBt0W38F8C;?]tqDD&xCbr!lzB1$go^#D,ha^6e
x.~19m4bX[=u~&,Z*<doUM:wwZEY)@2@w:$UmM{>A$FpkHjcu.d7LGQpHuR,u:h1lb3+f$i*YdJRim$$]>4f~d(`Cj|=%rG$K`/$@Qs&jBv)?3*>8EfD/pgES=D!a?[V<i/rOW_3)O&8bHTor`35;R))JF,0drNtG72$^;8S#P_O#kWy2WtXiF`p9guFNu62WjRW]IIlBj78{j"IZ3EN_8X>ZOb#aondG08vTd$1FDe=`gw.l05
j:oi4&1gJn9YE%/aC;<Hjq:L:X5fdiY2wptxoW=wr&Lr7k=M]7OYwO@E@UZ5i*qw}MU/]>UrB:+3#;fv,O+rY.h!K#<(}bAKsD]Tw^2c7ZEHp<j)!7$Yi=@;7y&r0*%umqGtNq{N$p^LmMH4WDkp`81r20Z57.*0d2%q5oM;#WOkS#:o+TI_x;&0sXC?6UB"XWg/SN$%K';break;case'default-green-e89f8340ea161fe36fa11f0ff02b73c8__9d9e818b.css':$f='$erWO6KZQG0Ow&8$3.4?>H1[C!`3;9E^iaV_((TT[7a$1Dy/C),.
[L<=$>x~$galXv:)/=yASk>c@xOiCP#wKZ68^+
>:&X>
k<iJ$
ajgkdJfX(g!w.lLe4b6lpjgklR&xt`0w%VuBvxsbWXFmoVTho"ds$R:&qM-
k/Hf}EyJMIP4POAyKJ.f$^NfC<b]D4tH3q4FTVt]|B|Gl4(t)4*?;HROm_AohINTDEE%bU.OPEu-ITi$s`@brdgpw5#%O<nm%j=:J=G75QuBMGh%:Qpj4M}c21,@(07Rh=(qECk7WxzdN@2LE2mID#<Ie^#h5PB<X/,k(L7kk/lxc%!nJmtZp0wA07:ZpM}!*-H
?"e]p(mb7JBn1T=Ct8C45g5/oi~u4RMIqC?tM=A$UW=@&^{%7<n$|yG6a_?6EW3b"";pJZ|)s1u"CH4#fD@nmp]eed{K-tf1b:iy*%SN6O#n
Fw2K6
6ZqB/#KffrGZ:q>-<M@%r6fe?zx,jPlugOn(C#irRpyEsvLv])ZNo"wFstM*c|Xxe:c
BeI6Za6Gmbu0BykDctEF.wsbE9rN1lxCtRydm2mjbUxay+bPc~iDtTqmK^y:,jvma4iJw<MrMbSP[Ky>MjHSyRm&^5F]m`[&bSyc5Js1Mok<WHo%oxyfW0!B^&tW7:ZIMfPdycqIM`biwxFIy&Mr,O=QH-a}797z7yN%Af4WWHI5y5,_wuSRo<3$wzxSnu_
Mbhd+Uc~Y"rSl.=(_f?E?tOOROW*S%]%yv1%3y%[/>H^O77FXd$"iH!PW+<JG`$6_UcpA#yjL1Va5GEY4Y1k_*BH(sN}c5BCrw^Tv/ZzGm2!Ef!I&.?{R5OI3wdP(!ShPggQh=]D-5D*i/O++]G^:OC3G~e?P.8er,8)Y*0OZ:jH&Tmp#L_~n-evl:K$y&^dyJcKOKM@HR7!-XW-^uWMVV(XTZJ%]78"A4v[&Rxv[-D/TqV!$&Q,9{:l#kG5eI4&@u>Y8&h6Q_;4>F"Q!u#}!@Pr"w%ilH578@>z%[XddMekR`<:"n/A-oigBF3*$e;}ZDMfj&uCLG
CHa49VnXW`@/Uf-Xlps%,B4
=8,yp/=cZ+yO`8ioE/|j=%y%SQ+=Xb,l3v{kWUf7IM.G+H:"7VdVk!JceL{msNBB0]eZ{.PEzRr7$Zh#|TUQ,v)/@t)FH^J6#b=B"J>`8@X2jG}t(4HxWYS.)
w@pxr<cFP?s"eo&<)pyxy^/40"rxM90l.Qc?~QLAhb1s"bZVv
9W.Y`;*1bF
#QLbjKwhX;r]h@v6U4fBC+ummOkp(G9<Yb_EaSYl4T.5=)fe=Y$h$1%v.3<f5k+uX_dTn&j2
_^*7ED6P[o=
$0s"+EXPP[qSzQ@[SS#_/av.#H{F<2Zy4V~@I[EO)g1Wq<Dks2#O"?Hje^*jf7Eu0O,wyQ$*-jR!7G
(tl1DAHCfqr-*IR4,C$o;xO:o@VR$%n67TDI/Mob,o!YrKq2Ca
wbLglk<)pEow*`Cf1#IL-^(11y:gg>(?WQ$>:=uckS9&8/,`:-Sj2.N(FQ?"c8-0/VT)+vBD2F-lqLt[
a"_*0bw3._NtR.V{=]Nheha?Oe

=C_gB{.9f7.e6*x.YifFEl^.d>(m_$SGUz"w<TtcM[#,Qq?Cc45ih!Ey9f(Rd:`Pe^E&?t2WJGd_K^E>9nq2kd!sVdY3!-$e8qrP4@5LB=&w=}xb`>Z/u/W"hQ8">V7dKt#duZ`"
}m-9p1$=or0b2s)5V+|3=.}NMK{(b<2X^*WogkKNFYMEqJFXqnk6FI
V"OCkq/
ajuh]EH,62)6_}w:n|rJ*#O
kePDlq,J,46z>*=zuSW/MkL-vE!1;$EfIQnFb6n)Y?C-`n?jaEl_`v^]DRQoJ6<9Na)U@7dXI_eVZ=#O52RHs=ZFPivdDFs}Ws(>SD*s1B35N6ld%J#rtz.S8AFgSS$wwnt^-jP`TR""Tk@"Y)GLkb^Yro*wo1
~Nc,+P#a!5jp=W$v*+2Bo``"(M"t~,+QgV>Hs?9r
:p6Q6~X_Tgo/bhY]X?P;m@%>vnr<8N%A0V9sg[n|Xf,5;tTA%K4h;;R*%#7`%Kc"p.(1kc
pEXTG^[,D5.+jaOk+5UDnS#Y4n0+C!8OMLrEl08p(k55NNXLFqRqyZC(KTC-kNVr%^n4?0zny=p>hcFTPrq/UeQ>,eJXo.n;nxkd1.@s=:a<IZsDl0Fb&BeQ](6Y[H@(axDf|pf2f"mdf8-D6(blclDh!>5"9.-pr(TcPjayD/4>f(L/q@hO_@j*/6m_R"m71XLupW!Ck,(u`^KXlnDLBa|R{o57XSFuJ0A+X)6x6Im#Z<Vu~Mt"]&}<,o%T4Vj&$I}S:U7<n9";X9_j62p"4).0$!j`%TIR20X6_i[&7C8%
K&A@(W2=P-
AvHC[">/FcO3{TzXB/bX0g`B2c?mAqCOz<xg2uYA?h@o{IsZU@TfrKuN@QykgyoSkPGL=T>S``"vob=B>[d^pAnVG8[W>H^)7lBk"D5dl4?q-;:,2/RB
:HOcNG/5A:*B749")^vhkKbaL`-ls3RKgg1vu8ed,
n1):k7^t]&Z|SoUtO*o#PhB_)3@GN+79H?LEF%nTlx?V]0__:I@WpFyo.R%>I&"Z5ny,P
Kb#oNAAj_<dVE7KFbZ8_aRj:XMD6;VXqU3bQpTdO;AV,A*TOA},,0"p?tTO5i`qZ%ch}"?*W4gwWPFdREPEm3g8eg^a+H=W5Ur/#
C7a;/AbpfsJ?$<O%4p+od?<ZAhuZsrEE0n%F
WvCfJZUx[}S+!V2NGn:+*Z:D4A[09[+Q!Y=XQfOAjj[U+CltpL]9I6;fJ)L_iJf+Lt2OsKEDh2]qcDj=2JI1g|0Ti5+oB9YUxh$vIe,Ct,HW/p*8PqDHse"lAOP~`0s{*
PG#Hgy8>1
d|S5b+RHF/>I0<#n.9w#+AiwRw#DNL^:i&Vc]+/#%%%R1G`O;{X)biCFjt;P&Ps9N+Yy+M&HoRGs>4Q4@7j<?:IUe[L"
z:x"
4vg#E,@S86CaAixj?-^Vu-F|?0Y>"8,x.qG0lX8JHAPGNIy;#MaYL@_cWd"yxcSwujEW:OcNm3kF#MY"wM>5q8;Kh9ytSts4HMi`o!m_-K!]W%/.$:tSO`LWZ{pQ&C@IaAIRCk7fOEtGk:FT((6O5GEgVUz(G.wH2DkX9[)c?@Q4D=1W5ckJ:82-4:)(
jh&;^oQ?Nj=A/Mp*|3W5O]A7P/WSSOV`+C2l4IX.YaZweCyNCx)h)$z(~SBCw$9uye}A,dF71^9Zt"mS4Wyqka4)]%KU_,~<AlquH_]Xar=^b?0%i<EZ%%)dfG,6hcMt>1_-wmm1<[CXDS}tI^JwlQ~"C6FH{<gR8kP(F^BhW!BFUq#2l"G4:D2ib;LJnQ:6`Z~T&tyD>d{!@tpaCd(Cqp$
-C0*FqdejY^T+8TZ7*"]f4oCZ-(BX0-TSq^%dX/#0R_Vc7Pg!qYg/5E
vi"knI1?gyRaxYG@[2h"q!qx?r}whJjw[L=MmUD+p>2Y)6LF"K,h81<"CMvy7%s7Bk9",9n+@T;mP0}iacf-=e0^oAE0X8l)R#gT?/BKUayG]cH;z3du.W+#Mjr@q0(*JmOaojHDYN=TLTu#F
"a@s$E33O148?j<0&v$)zLt%>*zI#y=HU8^=(9WNufsIC7WEt:YvPH{DS3r&n6IQ;xg<PFgSK28kDO+Lj`vdhF&j1^]M*15WIb3%i4|A5F*:=w2h=9RR2g22"3"wfO"Ki6|5NWEpOA"$E`[^:_Bs}^9Q)a-Yh`G-MaE)x?o!XOJmRX#)Tb_(et+t~I`T.Mr$OwXHs$><H_#xAP8"hd+!]`+^wa+=q(j^Z>wv@7~6tq5-lX/Sf,
q@2=
OK&2n2br}Qb`i#<mO]H?Ei3@G]8mPI.fhy9c
G[1&Q$tl:Mb:AEMe%fV%fV9HOqM0$Ot/#5S=Euw4jM:}T(XrH(v8nR#n*@2^!c5[
X&H$tOn[GqKUC/
**(?L[m)ESPANVo92*t#dl.$W(3Y-@d?ZU,{/iFQ?WX`7!J1F:yY:]8d#a@_xLXi/7

fe`[9u6}r"BBe#>y3o/Baidd!r]paXdgLX_*qJ6?3+E4C25=_#)q/9C|hL,L?)W3a_Rc7ClqeeYwT1qP9"ID(H676D^&mXO^H[Td6wAKk
3C)nIXh|h.MLK_jH&mZ$va16"taCwA0Ft`C]oR0%"}Hete"^3p,?thP9&#=YmlGINc
/X|0qd0XMt`!cvKUu`U&Lj<Dp*qm-1gR4_8^v5=4:Tce5irHS1{[m4[X9HsA]#j!DLw$O0K5MC0sPpjjR09h4G7"5-y]z]??3/n!2W#o</
:Zc06=pl-R?fG@<sfxD(.V%gN<5p_!Eq1PP?(z#-X0`vHZG*?M;]CZgU^bdnH_2_D+&A6=^D-|)BbE`+*yn38A%ikFtSB$q7_/.Jk~c+X71(Hz:O43!>I~YsS?]h:O/]?X%MpcrTKG7X3_m52
gYBoLyOFL},1J&!>"h?fu3<=O]_L@,#OGLX>I!3a)F8eL$
FkdCK[tI#Nw6<3b&$ms0q,T7+scL=CZ4.%V7ADxhFP!x.E}KgS"7GyF6}9?#sSz]:$ZfeV3qcv4].-1p/$+*zU.z#+/
~DM@ow%Ua_fPZCy56%x/-9TQfyxAGsKmbhF+g?CdWI%YJ)U
ZgV9Q(8X/GOH[_Lw"!2
J=g:@;GWZs>nDD2o;>_w{YvuIMQJZLhsZR!M&bLaRkzE2r$T"H]o02=+bV0Dz3~8:8PhO5)9oEV=0R9q_trw8"KJSqB`ZC7*EVf.0S4X+oJ./80Tc$sA|.,!7(Mu7%!xfgrZq-ixt[acOBGh7s-+}J{.r(]VRPLGA71_USJQ0>|(!5uj*L,^ZArF>wz-Zp19y(ren0T`V[z`mybGX%Pv7e1j.
.ZQ6n2
EkmfF$?9vFsB4,`.%,-1AgK,(p@_j&mRiz4`4+CTe|*0EjBp
C^/tQ&xjh8N`3^m=o$bxN%M-~eK8^wnrM4dXU:&7/&TmQPZ(GG].)4Q!Z:JrH"b1{6_>T_<P}Ke.;Gj&B,wE}UD>%*J<Ret<-)^bGsoQ/8u0&oNOTZZr:WUm)+iihE1F)J:!XT*
6wIuKt@S|%ij+0^/wKcJU;TV,53aFvlU()[Z89g%!mh<cGXppMgfQSBR~X^S_[D^_<qTXSw7o:CO<h6!][qmD"-7?(WDB&m;U1^YIYrfrb6eX%a#,Cck~Bl[pgZp}+![9Z>/w&jt,G]qw%@by)5*cn<"
:+wvG
_NGoI/YT3o"&U*@:R^iD+(GmtlREXP9-]XY@Fe;kIX%Pf+qMoH[#.?kdD*ij0+f
Jc)MF7Sql-yQJ$/liGL,>W)?fhs3:?FZXKr,kmX1hWevjkk((2f|7{l<G"hP=]c<]f`{5J=1;``EgTYvIp/xi9/UgRH1W~J%Vg$I
+2N@6?JG}gW;L3)9INutzD$;af`#")S/RH^f)`+$RKd:LVVez7Ut}]bX58b9nF`TQNTE"1tH!?RxxZ*_<8xBRdqoki=1rsV1wHxSx>7V6ERNwS[U+d{;4Sb#fQ[+OLJv5D^np^(`f$"sbk
cWVhX""KG#[!YIva9WP!CU&+bcsDD1Is>*5MBU7cIn.$SI7@y-H_xtsjJ)xNb1l.s+"<RK)jt]eebN+@HYQ@kP5fZ_G]$,dF=[Sg0BS$iX:EONAaeZz%,EnV-}A[[{2T;KPJRn#(P/68do#xt*
4HEt+6:/G9y/F%ybE[%9l2I`#=cFYf05/AaAguO8|%@"zUW(9>X(Lw17(P;p/#p]1G`r1CWL1X8pNEO!vk4kg7-?`(U3cUY5oge9r-WCSw*jI9ILM@vv-s9N2Upg,49N~M0Lo4Al>xY:C4x,7:1nWCxXNnM;"kp%jtSY667A`Mn?E`X&gi.^{O3V9fuXH_qKs3shH<S>pNH-bg{!>Stp+!DTLaT9)u0d[sg7D&q<g_4-5b^-m^IlVLd.[[%Zsc<2[>(WaMX27xZb|&9QgO
[]=kSW.Pao^TerK`BL^u_0N8(J$U8VJJ7>Ro*fearXu/&L_"iSW&"T/pb~^pJ*w~I+WgCSLmR"?dP}:0dyas8{%5i-]CAin(5UjQ6_n~I>Sh0%F^8~,5"=#a]]fKiI;M-3:B?QG8/@=!Rk-lTTG/+.Qt#kpZa{Bj!^-iRXhxw&ok><fd)Ti9!TcnSZ5hES_1XjNfC!=MIvS>_DP=$!=Nj1L6M3ps.Lv(]F0;.:sl(!J9?Yd4h{
~KMba
:Tp`RSOa02?0Yy:FvN2u5VZ9d^$Yer_77js,24l3mtss=:qaVgT#bGAL!Pi:Ne5`$=[BCDT[mBpMT)-A!X
*6@bh}4UdCf],a`.,~!iN9qrB(:ghv>v+2U>#
==:#NJ22OTrHvC!JiR6%F7)b,9CFqm(H*G-gsm"I+bOq/3Q2;ZQZR8cEq7a._68nkT_m,_0BNlSRn9,PD7CI0RV,f*kd*F4_D`)n]F<HXX9>@Es9oH<UU#*
!4!VYF0=$/nen^/SM=.sP4IT=Z)56eMgh3ATX`O4k:l%&(C`9l`o=-b!pCrD-R4^fd78=!cs]-%]FhMi#&;d$=T[4C21L*"SQPbO5eguFif+PY^3V~3!L5KfQPc0`3BzhCsWXHt,yFi^-4wVrD(xwBI(;?u^*,A,cbslflyzm
Yf^QmS)YmY_l_va)#x+{de?ZVp4dOnylZA4|ARM%1f#<xoNr2}R((%e3oL^jlL>y
Ry$B6-9pv"Fp:1@r9TW!G:(t<rFMa%"6O)3^UL=L|SuYR.UtRWdicBW#KkkWPe;U}jhKdKB]k>f
Gs9.v])=HRGxj/Z-~KDJErnL5no$^omI#frZ)pZ3LF&!&:]-qN*
.-9J,<X0([EUo@
EMWm]s#o^o6YKt:E5=LpRo2g,rJ`iSkPS96D#B"Y3FZ#NEX<2Mqa;SktF{s}73O!_-y#EB^-rlpXbg
ZP#.^G2:Vj/;/H-gcCHE]%*g/hC:lLM%[=53Y!reTNd>O
}K5*hj4b}Ug;zU8?MS^,;wFTkbE;%%/l@8SMyw2I"c8[ZcI8ngbwK%gmT-*Iyruyr+C(3Vx_armZaRmb}noTr-Pk5M+Slec9lecA$RZ7o^9eMq^f[e=Z!nA*0J"G4%1c,v&Q!#=kOc(RSR+l{K)`yLd,/ljXFB{?E5[W2!QKUFgPf;>*}&BVg_j(E[JV+d-C(Z>REkd>o
{mI05y{[.rPV:';break;case'default-orange-45573000062e3876738bef36b1734b15__9d9e818b.css':$f='+erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhxe/CTC?saq5qsKQ*LB$<b.0;*%{a4b)qE6WmLxHcqkHpxcO3?JJQoKJQhn1](K+bXG]GdB|o^?CF)K2E"Ff?k_f`{eY5%[jxmk|UU
_lAk[p~cpbyQFGd0kRI@7bRfIG~^Wp7

nro$!N`iR1@jK~I6S0kgrGp)jhrG?Tj1MM/&G}xZJA()Jw7
q4),:%u_@hVWM%S5W|qtF8FpsmDQh8dM&$]:lP7s
&wC9`H/8~8LoBvYc"!yr{a25$MY5|x;]dL{on1(b;Iuco`+M:DyiJmbIe>~CIv7uvLOHH3za|(V&cW]S}k~niv!EhCuO[lMZ9Df[l"?a<nLF82jYRy-
=-R@oE`VRy{250E41,y[:K`V,xS+LHUH/Fbcz>
Dkv/<T1.2W#C<g^>o/YI)RaGHzv:Nd"3`MoUY%?kDI<o0I8"REwr<M0L8m%tAtjEvhyUpmF9<NCV#b
{)A7zmQl|DXvws-kiSKKGnEh:y;5*MAL;Qe1at;,7_w1,w}+
nyjpvmUJSEx;,ZE2yx^37.kVMqF]H^/h5rH!y&n[H"d!maWQv}May4S-]l9Dz&6}ymyL54F}4mtQuzqMH.tOi<t;yvny
!X{h*A^]YiSxk9|z!4/@-tVcdJio"Zcz)Mq,bm%N"uaSRx.,cbTAHxZtwE8xSuzw;M2c850w:yGypyJz$>sN%_hMr=.a<utWU]cXk^R_7GLSKK)m~Y5xs_@qn&(1ptOMm;Sk%P
JQhU>SEJoN5%N/j6ln/q20.wq/W^yL7aB(gW40IaG
[,hzaVh:0%jg.1J*_th|KE@0:xkzDmye_.if`aRjx8yh-%tz8/ScdT$hOeP),:T}ovJ#"u)-&r)!d/H.4C#.JIL|4*!]f@"o"W85,GThIm.6#@glD66<cXm"*7AvSrw5Y*oDM,rK%yuo:[jfP=I@l$UE1vdmuZ4IiflhooIX0!@p,&`(5]EWOxg4T8N_%q/*]#
=;Hw{ow<2OS86W}&hEHfN#?rJ86*Cfo0FLa&_"0w~A9$x//DgV~NhF}=+2jA#BMr"P3=n6{G:NAYd7JMOiWeB%HM*5ug#]fIR$/yTjasW26+w!h$B<>ioBr=!i6vzE0I5GLA|9Rs<hqMo$()HEHGJjnu*bH?))?sP8ukINsw2onZ=@dOQBuA-O.m`vHRZpvbZ`KC),{Zn"|D8%mHCNKPf=|jq)b6#x
L=XX"kFNIfp=o3a+>@_Nft46_Fm7ljm6QRsDLWTn>pgws->XVit22`F&m(&H$b$%19?hEgRKftO>*,?t$4R4m6ESkG.zRR]P.+CqI5]Tse""N7uak6Bu8/CJab]=@k8X%ul;N4RJxul9>*HeWbptsWrbJbU?kPlg53m%sh>zZna^.!U
#4kjI<D4"|@^@c`/.T(YN#Tem"A=3nvSOC(4AgCw-.#2@P/e:l0[I#,l5C&:%i@D>AB?
$a[Y<UxN]xP%1/Lg,KF:clR+>p{4S@"n:b9&!/^<JLRR5QE$;=)h*hI:B1QgUm|)A^DbX800_DD"Ci:NnNK0C"&/z(65poRK&BHf6z#QGUBKs:M-z_o)Ss77lsEoz??4xrJ#Yv6EzUFxBenX*%pKWql??)}gu#wf*`/i@<0?/wK]>(h&ko*BGUHW[tmQ8bQ_MwsyGUPpRrwgoANl^xf7k,I4bMO7/Hf
BsydB82(xncq]YoUN`8j{[gw8h_[b[!"Nst${&,a2J#xybQW0nB:?B-W1qNLo!,8n_1w$[89IK0p-GjrOZ}ncs"HU8lXyP&IDctU0Ndk%>>*%=Vt51lJw8XD#tYX^_3*)y;ESy-EQ&LfG$!$rmAu;mthnoX(bFEE*Cf_@+[x=#?d~Q0tp"QqFZ;#{7~e9vyTAdD5N6>HxLutNB^!qWUG5QUR#S2btLtc:i$Cbe^TZ.Nw9Ayh!-;$uT8Xt=KSw/=!!FfxdV}TW"t-!L%1O.O#zLJ"PiRIr"C^)Xb#Oa!&GkULZv"HN=^PK:-[:qIE0.,!P%ln>]8N&iJ:`]_o<`Oi`y@_w%ve1+Hn>@9XME*ky5-9IAImsZcR,)j)Z&PK.;."(wI-TK=]zBk-8X@o37!fd8(hfh))9.L_
X3%E]v+9[.lv&fE@E,gaZV>C"G+yLzVE(l3T<AgL+ugU]GU[T)C_#)*?/Uyzp@x@oXNJogvi@m6lF6PcnN>(Y/R"&Ffg)h=&.@>Q0~JI&DyiVB:Z";acxVe~Nk#E1oU{<B(lWiWXPb%A!eM[Rz1j&:m1V2tWKUj&gX^`(!?A^7M&)nMx:,0i-v6Wv$O:/E$)Ur-rOC&kg{[7^Ftw:QP_wv:x,Z!B=PV`M#W+ezd(Lw(Uw/R"x=_.+=F1](4-MK4jUl3M1q,BTRZ_$**pO<=|N-fn"[(wg=)x?gI,`-3gvRC?VZkD+E$*M^po"{(]nxL{k;=B^jM^BPe]I`Q#U:1rSaf==~V"1)kc878H%u1vK`x&C21`uz#^-:F+]FAU6ZP^]-mnWidWZAaYk4C0?~(efu52n&gWW379)veX"3Tf1
R2,[YVQrxEJE]RXd2eL^N,Jo_dw_icPc7hlUr?By<i3R:vg~dYtU9B*1f%$:oz8:!Wn{L%%~4YvMF^]PT.4~aZ6X%-TFdsd.!.z#M
t#8^^
k_kiKC:wcjkIVUv~$0/c@WT*DKCkl-p_,dVlA<0iD7Js,JgapC.E@IVO&/EZVs%NYoh_ylY5VHT-@|<NT)L:Gp+y>eU1q#n*=K36XewcWMn0indrJqb(l)KZoY2gcH-Rn/fhocV"!?R_BK%Ip43Te%U/"Ns"e=No4q"4OZ>J-16q-^PYNm7Q
uOUTiM-m
Iqy3BF+YoPl.4n^%z)Pwi%+<:Rr+E
&7y@d5O*IT9_:ZR",h[vlD-4y1+I#/P>(YR{)Oe?!nrg9l#J8AKR)5QqV]D:[*E="luC_g-Y1UQ)CHu{3d/W&gVuuNYpC%I$f*<RpF_Y+k6lVfr,2,:tA#2yCD
")q48*L?<wfZD"7r[Pi-y9oHUn|GyV"&&x$TXp$JT5cjR#>gFakc%acSpf9cLnyP./@f}`~7t9T%LV<&Tr:/pMY[#]DJhjBKrn0)lPn-ro%04yz4<xJ-"`dMxy>qW/XxQuK^^0Y${)Z:qt;G4,RfX)%?E4t%DGb+|Rsw8^`JO,HitgWF5]N8#uftV7P)ngWY}Y|6"/l
-9Lf2rXKkn;*LR;klQY"3>K^H+#O9F(30!U.rHVLGKLx64d54+Q0(
Y/F)];e5k6
QAb)s9(7qJDI[Ys&EAmG6fCPq
=W@/
!3ny4p0RDNIR2Lr_aCgmEg1]9?y*W]}br
8Rt.wL&ve)f(/h}C1
@Wl+FP-dZ
-p}v=*kFo0CLl.q5,B
Q1%hr+
qu88&v,%L)}Nn7-q+SaFV)Jj2m($HW@GU5ouS&18pO?E/4"vl4KPZ$8Bzli<`)b`q<;nAgx1T#pUd+p@~i:BTFjw$@c;MBu1h`.5Y)Pn7@1
lLuW`*$MkVjLzY&"V:(`Vw?=0dg+*eg):>7/;Fz#NAs6ktIg()#P+iUd-YF(,iWj?5T,VBy9bN6JU&NnI71g02}Ch89a@!dath,q(V#Gp6tu]PNwyY1RROzYPoaS0>/:<If6_#j;1lhFg*",gf8fUYs?<YK/>[UCc/f?e2v!ch;:J+^9eb&P.6~1&C^Td<vyjO<64*]tN(/6k>s+:#.hCwh4M-f3n?W`N":e^c=jv5h45PI.S:}>#KqJ=;(i=.h1T.*9^;%w=?ZE!`:L|="TjG^&QMRD;-UiT9t+al7&P&c@a>/2i;[_ILt$FM@wQ,;r%csc(Iv8PELteMtK-!]qHR$cyTqC%2SELPeu=4wNi-Q?am37c$crEjRm.2o3B2J6hJMI^
E7HOKLeC6bN!V$^re?B2/e{`pn,qV$7=F6]<cZ%b*1/_P,:e*6N![y"5Bge<59:/qcsab9Ogyl!8FVFe$OZIU[akvo;OU@T3=:lN{C*2TKf;vc>WD=[q4t^Tyw8$5HTAw@JEysBDbIC1ZZ-Dh,4@8ACxQ"/<Zc,wD<MX`yFhW[5N&5qxSlgDTfe=Kt^_]^:=KUfd8KHNLL)k

h&:.yy-Vy70aU5xByHz1;.W%[N0pnoK_(_8r"0r`"=xoxJBJLuGYKJ6Nr`rXVVFR?nQmXhu^.;T&-3x]`YR@M:[D?3apz_?>Mds)wd$N}2s-0*o"*3w:~IL(:QT4t#?#HPXP-Cau8NJ0qB)"|uCH82i=Uyx(3#)(4E.9!bL_P2>S7YMkFs4YisMyoW{N.;pSvGi%xn<t`DNN5?Q6M5Ebb3QX:/yIsVt%A1i1(tM(vq-SWhU@nV,oOE@?=8dN>fE)(!p$-
Uca*k*E_$N0?)e2=a=S]KLH@R)mQrkU%,9)Jsq8KdfL]%b+TD/%:@1?0Z%q,*CFuiksv#@bVLCsch6XGY48n|
MPW.Be_4
Ocj.D{_Z"Q2JrY<X)j<+Z"`]k2Qy:/>f-Y<,0#

v"b@k/O^huucqI2yor!4U{x|TZ_$!#/U/.v"Tx[PM`C9hEnfZDT#t;mDN|+uNm?e/{]z,Sl|.@g8
??$>wbZFX5<%rd4^or4@x)64+gUR1wP1``|su.^9/KjLE%gMR%v]h$V3-o[dlU6oQv#AQ
zhD@a_,Xp4cU<]kAP*K(2
o,EGTuVHS%SKf@!#lh-&F^+>D>`C}SirlfiO|)&KGJg&xRd4*=1m2uYqx*Q:DTEiLIo>iua^iovJ+2&v8tBoim_@=Z49ldHNBy3tn=|T"g6%d#P%wn??:c26T4WclP:bz0XrQ=yT&(wW,-9GdXoSGd
"j+C)~71)Jm3HT>_0*B:Q<f0TJ@F.%lnKh.[gVd)W|eQt[E8%dQ?:7UjoKaa*
8y9KG}r*d+39!s<eeE5fbu(~gZxVRC-irj6KZ%WzZO*4E>V&jNL*+n8<)q"Y*3-p,^
,X:?)=TpH#pPztVM."hLyGjC;lETT!IaPPZMw&xKtv=D(_Znl?T@<$}sWF73}oJ-Ed*4DTV6mDrr&7XmDTq^oJ;]ehV_<12#9t^[[:oWp3Ia{$R0eYKDua``!w-Nxr=.(U$W{r;7!iLpiRRU?F8UD)oD~=`TaP;]#dB&74k<usw=o<N9}_tY)7c>P3.?]Rz`e`P<uk9xLGxNF+Oe`EsY`U390IlK:Zi`,n7+fea5m%UeAQhY
E}></1fTGK5I_&"mNi[~84Xm1ece]yYIU}))!~YX6EY!<h>qg![z7;iS2yBVGU(w,@d%v)^3Z[-{;vMhSr5k){DGA2kS
FyX+!grT)"VNhcQG(Fvju22<TDTjL,-Ft(Y
m]+131F!EPC$f/AS&uv).P%9M5F_Ec6:wf
w/uNYp9XLgf;4-T|G`cimAYox~`J^ZmICD/gWk[EN<>ix{7l?(EMY{*wn#[hn@K8A{
YHo(lrG=HuE?+b8_@L]/PjMfDH^`Oy0mx?~JFQwNY6&uKA]jF9RJ2Qs=v#9wm$@LNS0j{!$h[3&,JFJh/7h=dLy:Cq5_Y@QH/Unq(H[04fzZ0?D=c=v8)P81,-Xqx-u0~8Wx0OI>hP>Sq?AEM&PSf<;CSmR`k"o?Tr<q`7+nXnQ
?(xV8(}@i:]$93!3u/xxe96.$jl*BMQDo[/eht(XeH#He=2q&"a),M]dUbNL#vd^<&8<EM@x`7.bf2Y;_n#wtUUh:XDDT!>&:%MbJPk#),><$"^HSyxhESWkGSq9OCWMv^Zi7?w_*Q@,y;#j)@"l^-oaHO"DPcge5fWHeBKt.$L,G.bt~"G-[2_EoL`THxm?Z2gFG-1va_iMAthMB7gK3R(+@s>k$fu;2=Q`JaP]uj6X-MOWSea>~.^<o>rcN(E*PB;,G
@-G0nJi:Kq~)j*Gt]s3qpjEhy]4o#M[$0?Mgc&)TzO$l/"Q(;)Qi
v[ElbEr09HUHmYS,;/u8]whg2DqOV`[E&6hvJn$dJ}*4_5][esKp]V%ZR5qjHnJ"P$q8l#SJ<S>X8uF`Jek8Z:/>*wi&l%P+uQ+"R!6n>jN16B#FX2o4y:=1[4R`5g1?3ybcx4F+j9LC#K@mGl]OHUWW3xwZ%+3""0X:%WS4a`4)PhGy8UH^&$V")s5u3}*94
fCifQ*p"W;?`-VxdfP9*ALg)>v@<M9;`?}DC$q8(#NxNwLf6H!Q6G9g;?GL`u0Dh24t/5fE})<:?[BsTDdF
8<vH_q1s(3Uc)3JLC}ow@}"S;4q(t_[F,CDJx{tHt6YEC>GZ$k)R4BBLrME0EI!9fF&7l4M2%&K2s!9]`-cSt}K!fCjh3y1s*cHH?qV87b
amP&AmJk+K=+:
9$v)`FrWN(#B}wRR*k9((!
O&*t"wn%u@0cS|Svx:hT>CE[H]nMqIEzN.I7:v&
Vqw8%4"?@&"s7Tk.IO<"g23wFKpUwGxS:T1@p}p_)5BO1p$RCgmd(4(Nk#8f<];w#eHng%H:,/=?+T5IK6AU(&7hsN&Q+X;.-M>q!
@@D8l
>7BC_G5k#-SC`/@Aj6VIr""G,f""dfjl+jNexT;AxXFm&?q)N51+KKyPU
a*76$8Kv]|EQjqQzs[VsJ25*j
8uG>S=:dUDoV:`4|j~vz$Z`E%6[C;dB@`8#,)"?wINUukRR1=.?sc%.*w+uN):J0KnMU34xd+Jycq,2Y-@hIr4.Hxeu,@l$k@:4dd%c;/!yrh/Z"=Jl-1Ra,EQqZBV;#5@N-]V3EFgQjyN:h1oa-xFLI$VKL)/C{V(.(P_dhpt2f[t?nws`M-YkC#)D71A5{q%,
Qln3l0Ma+16OJ5Xuc?v(>xexQ.coEz8Tc/2^jH)~m^WKc/"IKbX@?AF5xi.vtV=H%6wK=5q%tf6-]$riMeB][~e{Y8R.(,h1Yw63,lQ;"->s8Hr>VNI2;>GYT8nKL1>w+2CSJP3s<fHXwR*ACN7esDX}Xg,H1s!8"pD[9:NNbSB8!3bnWow<lPLDUWp4Ku1^B1ULgJuq
:4t_O^c,38y.Ocl05OvXH.E({3(-+,4*{3=h4<~-?=MUiA*yMiM<u$F^x(^W{]7O%rbddX#m]!1TBNja2%yps]^l
6C%.(RJ%da^5VNiETU=Zs8q]176DFiB5V!TvXkr4MH2@PrCx){Zh:R3#[_iwsU6O#`^MWGFk6XH/lh7x<tk=;0u5-GJ^UJiUi@JX5IG?yuP3ys;tfErdBXI5;QP:N([PMM5}]5gKEYT<Qx`I+E6<"K%*7u&@_tn{!*AP+&e1H{Ms^V';break;case'default-purple-2da67c4916e2605882f3e958df7acb95__9d9e818b.css':$f='*erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhxe/CTC?saq5qsKQ*LB$<b.0;*%{a4b)qE6WmLxHcqkHpxcO3?JJQoKJQhn1](K+bXG]GdB|o^?CF)K2E"Ff?k_f`{eY5%[jxmk|UU
_lAk[p~cpbyQFGd0kRI@7bRfIG~^Wp7

nro$!N`iR1@jK~I6S0kgrGp)jhrG?Tj1MM/&G}xZJA()Jw7
q4),:%u_@hVWM%S5W|qtF8FpsmDQh8dM&$]:lP7s
&wC9`H/8~8LoBvYc"!yr{a25$MY5|x;]dL{on1(b;Iuco`+M:DyiJmbIe>~CIv7uvLOHH3za|(V&cW]S}k~niv!EhCuO[lMZ9Df[l"?a<nLF82jYRy-
=-R@oE`VRy{250E41,y[:K`V,xS+LHUH/Fbcz>
Dkv/<T1.2W#C<g^>o/YI)RaGHzv:Nd"3`MoUY%?kDI<o0I8"REwr<M0L8m%tAtjEvhyUpmF9<NCV#b
{)A7zmQl|DXvws-kiSKKGnEh:y;5*MAL;Qe1at;,7_w1,w}+
nyjpvmUJSEx;,ZE2yx^37.kVMqF]H^/h5rH!y&n[H"d!maWQv}May4S-]l9Dz&6}ymyL54F}4mtQuzqMH.tOi<t;yvny
!X{h*A^]YiSxk9|z!4/@-tVcdJio"Zcz)Mq,bm%N"uaSRx.,cbTAHxZtwE8xSuzw;M2c850w:yGypyJz$>sN%_hMr=.a<utWU]cXk^R_7GLSKK)m~Y5xs_@qn&(1ptOMm;Sk%P
JQhU>SEJoN5%N/j6ln/q20.wq/W^yL7aB(gW40IaG
[,hzaVh:0%jg.1J*_th|KE@0:xkzDmye_.if`aRjx8yh-%tz8/ScdT$hOeP),:T}ovJ#"u)-&r)!d/H.4C#.JIL|4*!]f@"o"W85,GThIm.6#@glD66<cXm"*7AvSrw5Y*oDM,rK%yuo:[jfP=I@l$UE1vdmuZ4IiflhooIX0!@p,$K##}FG(jL<b&&s"bpaa^#ZO-b9sRY*?,"T)8*<@n83J8@d$(5;BY:-5s9,-g[Nt
&r30s*F^"fc^ImC!+PTt*F]U27v&9:[{g9,bC+q~HFAR_y2qdwBcf=_]
|mydYtKJsxLRJ$ROOd^Tk_})c(q77x{5p@KWWfU=ubKxRB].6#qlw;qJwLkt0U?#s7gikE&-vM?Hd
,kg.hV*fEZ#JqM.Z$5e
n]^lC/g
1d7dHUwfJ#0HLYy[{75J%w1yA7ID~iWFml#d=Gq]JDS^ZI4pwZu`q4G(xrpw$2?[BUjjj[J4lnZC;n7`&5k&"R,8;^TSF*m!~5#00Go)3*_`>km]!LK*;*d<{ece<BEmC2W"E/.apc+dDdsI>E{_WO1)l^--@(%wdcyZrCP5;h%x-oIti+(]Z3CK.`AbD[ujcC]:-sO$D]MpNjS#]Nl`l0:;)9qY"2-3S`ZE^].,u.JVNeo8:$B^~=3Ps?wNv7ZI!%%(p2b`2aZ>#Kv8/1l#uJb+,<wT2tjRuS{4[QmLW]`mLJI)i=AVrK2*>(aG_T$V;akR"A4R:b40a?uw=YC=y$X*}WhDa"l>D".=u.JD0dzt2bn&_yz*+F
p9Rx#mF|0eftN}Vwn&[y<ynv$qgEk<0Sq-QV6&)"t}o![yG~P5%TR/CL,baA
}2W@R/QA^d+6i+64MC`+.JyDqylLa6GhAE.XA4w_2v#PP+oH(LpL?p1(YlHN[2pEtc(k|9x0oE#
3MtrAW@@&;AD%jy!w$eFt
+wkK<J5a8R
W64yhjw`,veaD8s|WqPpt?nFlSjy;t7,v|ok9H5%&"pkMb0G.C[}ZJ4nYNn8AZG[Tfd_C8B6D<21w*i8m-kiV{O|%I=p_5p]a`Xy8r7`ieR0fqpj/bw3PaOw!TY_!/<?J~%7MnS4sL.mP$HZ:.q75fnsd@"g5#@u(}(WX?CLxM+DW|9BVe-!g*tJatV$8TS`#H6~-<3;<8,$j+wR4Q/gf"8,v(*z:|%ufB$EXrw7"%B-5wQD=%,;0lw7rZcu
&R@]:;(hhmm9sB|(7b3KL"cBhV*@uYRFtYJxWEg?xP:4/WY[g73sp
HtLPp`QVg9y){1U15m8qOSw8-tidjd-H"7S=}5ToML,SA^cVc*$3:;:$+7`T]<63nhG
|+-Rpfq*kKYYA"t-
xY!W/ZpjQ6TnKfOSA(14/K8h)e2
<qt?fWv[i6"kpEr(_kHpjk*%^gZ?=h!q+6P^1Qn,:^Z`@"rpVl$-3]^6(&I@qPS<OS"#B#&nU?/J%"5*!M#2-kX9+mAU%*_V2Cfkuld}U*C9.(]DnPrV2@nlRw?S9mI*r/,a<H<7-Q:)$Z*uUh?/B#YeS$SLp8UwM!$JY">Bv^`A
kN!Kc7BsRK&u-D"/-kd*$F7y7<Q1Qp|<v6#["8N&R>W$OYz!
SUe--)T0G{ZfDC@`DmG)lu2k
V4iRFhehzeu2$bfx#]oXs=}y=6sS|q"!z-bB)";RP-z4b?NR>OpNn)gA-_Pxhe%+@s3$z8Rj5lf^BJs!>>jaV5NPK$WFC
^Y=
Z[`N2F}x=Sa4|6R1m%E2u-?A:$k7x"y+$vpx5BAyGCIv;80oTqfod[FS;E8_duQbQ+O?WU4?
T[m}qm.4R07&dJNZ2_bl`*,gGDgik>ls0{G>(.MwTM#^O`N:)PyyyFmy#:=b]8]Cv-TS,C^/3$sy&>!E
F-gq}fz^1wc5DuW[(?sfDrEc:Ole9$f
+37KCe)3o/.8um:vh9DKN/S_yVw.(`Hma6VfE.sgzb.Xt.a1ou`Js`q-R`0rA`1^!"]p0F8LfC~aRiDjp177Z(Oc!(odx[,Ri/q-xkx$=4,E}8a"BZr88Kde2!0"0M4<x%dEMvr4zw8x|bj54dw^.DunEtS)nmg4WS&j4gs*mh!PG:,q!P|i<(V7R87_A8Grf53$A"F[h&>0}:R3.k!rX"-"Lj(2O?^-_f3<.g6#ze^Dd#A;Z(hYnqoq]=-0?_hpq9Yo)rl%Sb)e@[E0(L](EkQAcN<_AY}gN=WGeE+^zQTv)fZ%5k0HF8Q%[iZc1Ax7K)HU(1o9WxZJ+Zr*,S`ITMDI`-TRIx{^;!5GTV]syH4S/j}/e*gTP@JLmAL@^]8butdW=.pT,DaawjSt>Fwke8#setCyv<xB`vYpuGA?#"D0RinkbmIB#P@
LQiGi(fe-6]Afp:CYr|6oCXU*jI*tL^qfz$L>]jOV:7.lKF=j8
QZ<1k*uXWK4=*=]N)+dZZrAcJ!$Iik>l.NgQqvv,Spy,sM865#>/<C=-<4ULuK:g)|`$:t/PRdfpi@qQg~JjHeeBS/[v1v>"D;x?nQ*?-j!Qwf?meP4mT8A+]s4RAdF1?j+i9!vKh61NZARHe$>W/}5.<;O-=^ryrPIYf.>Dl
=*H6Rg)[UFd[?
pFR7Ew3w0Y9_D"h--?k.0cO>`&!"/%mk>_sf*POb"N&FKRsq0o!-RbSJ`n+E4,G[wX_oV,L(%bG;-Ia$nH^zkVnL`*k%a#AR;=M?
SQu^A?Qr=59H0vn_qrE8"eCOFFC^RX?OG47SMFTZJ<4lT${5c@Yni>42k%Jd%N1d6.=ZK.HI(7MnlP#NFs)+^xrIWTv-yh:NA=W/PIZSFgd1}l?Kjf3&3_v@R*;0w8v8e77Z<h`vzK?;OTA35vP1?MbO^Ra.^
W9/GZ:z9CB}]l8d1T*HcE5=%0J%&r5{Bp8w)uWn7X,dJ&>=l,Z@;+[gJT$;V]u=H?eq:Z]pFz%;Ps6S^NIQ;H&h:|Q7Z$jbs|!xXR;QW+GzPzT-pD1am
FJ5[X#/MxB+$M1l"8InpTS5di9/.WKNr[_CSROE+ac&kL[i}7x(#M_LH[gS.hwioy)HL5ShG4}I_[_o/C!&q,pppGd&5e!RE`<7U!Ei~q,`2C?9eB2KYrxp{>`KL%?qwfQJZ/t&;kI
cB-QvF7b7kj&lnhIuW9?NHdlQG]5Pf,IXY<gCJik=S^PR=pL<Im[~Sf20T=2cZ}"DDuBq
C"W*R_)#YWe#Oo5C)I9XVL3JhZZh?ta12^<+voIlp`8=`lZh(DHA6:PrZ6F2:Ujvx"<O`LO^`X>c.s4XKh=$jHaUzcTfwXnVooFMLAgc~-9"Rtm$4v)b`?IKQ9.LM3eMbu!G*cSdtAxg3#j"w%Pm!CDZEn!kj:rZUOWx7r7Of<yEt)6F;c:-3+8bua)WtM3TF*0C,@wC}ab&pl,C>gnLoZYO_-cz#.tC!d20w"r:jX,pV)$(~]c!H$o!""%9@jy#M?c
]$]e_nNCcN+y)ZH$3.FR4QOvsDrAxnc8p
#a::olQy^7v""P2-ex
+76;tlhA"(Z=JX!jL`pu%y?y[N6M(@+YAQn`5@hqY4Nf_^=0f;h>W-OJ"SOw0.8Z#PkXGi4z2aC{"6r3P:-?SXB24Xam1|)^Z:TZP,rERDx2S2K"KOZbG+Q;*j<L)c+5f0q<c0s?_>2ofK,BMwm3A"c2(o!(:#g=C.%@Z8h[/3%gApk+W2]YPa98sH
8)Ks?Z+9#Ph>e(uqzJW
5#oX7vohh.]hE+d<yvN/NCu,$i!<;r;El:0M=j)Va7B@0-:cPb$Oe0A$61?=zBP7$a7f3Nu>Tq}^XJJv:K?UjN?CX(8bZ0j%2WiUzoGBes~jz;}P@uUJd)OxB)n91!M9@i;#N5le
qycj?nag_CCl."Hk0_97`~("/e?!1=nMDvo!**jL^,&-P
+NB$]PZ^P%04>{c|%1q}o@t2+n(Xr6Ro_X["lS3Chi-=X^w5Zq/FKbeer4B*FCy^dia/^Y:>r]Ng"+rqq[-f3KS_4R"8*#R$]vL*HCFm,O&KwqDgjx-p3R/p))9sm*,U,eeC)(4$&xJ{
wOmq//+>4b3(SN+.zY;:i_MuR;Xk$N)5B[|o/R4,3(4sT-B9<I74
OsS^n?T)PrDP"bX/PXIML&F#U-vs5_8q?IM]:AKt9Q26h[/zq!v35}NZ-W#C!E9aM88_6*
<ZL:265!nnjx;$Tlrnx8QSb/aBsCG!-yo+rIvl|eLqJ^"])Sa)AA#uKF_8fA#N*0]1lK[gdj"c,]t/<CcswAD
UCkVT!9o62"VIaMDsJR&z9y8lrmL#Ert3#j(JBH/|5lkox1S>h/@}-kj/%e.v<,Sr.Y<O:MNz5QCK,;mcZBVbTgE"-/L$Z~>l]<ApDZF6xsY`Jzxo"bw.NNj+9:0<$Jl*sp;LF1aa5ZOuI[#ZO6UV91i[eO9XSQwqITYi&E#LHvQ/7CFyK~W~;R1304/CdjE8:*mCa5SbHjLU,hIEc-m35@7"BusLB-F48T?kvh.@I[1w:hUB^CjgsV50k`.("k#JKUV4nW[C7MW);6U=6xUg7J?4K1@D@j!>%y!E9z,#[c1]Qq[{HkZKFqTsi(p)q&J.Q1KDWzF9:nk|Ml[&:;atL>CN`
f-i$%"=DNZ[RwOMY]Qi<?A305f@3astVc=k?o[.wjtXmqcr3JFpjqg="E*UGo^%nyYaicQsN?b#+J+ekd$.^aQrCk]Yl#NjV&_K:,3
Rn-NrD*A{l8UaMQ[OwcX6haE+c/Af,!hfo7A0TQ/)
gYGT<OTh4:e8oiu;1?mO.tqf~[Q&3-W[>=7*{.(VDjX`;1I&I
gjQgsb?]Yc|3G/r^:0"`6=A)7CI:vA}K?`t8aqc2bycQR:oQJy.5}+{wdX#gw&-[whiPG`ds<t%MC*OVhjwwyL;EvD6U<_3ub6SWnbbd@,:*R!VJk2L"*x`PW"zY&xGVq2W]HYON9dLXsG9XDc>DkTb/YTed~[:_5J9Hg#gd:M([FNxo[Wvnr=#.S<)dl&ydh8Bk%K>6~wS]5Dr>8=rqv[]s1p,bXMPtU*&3<VYaMT&u=TuG.CHBGD93Hz#?zOu
0@nV|/RJ-/K=wa2c49!8l)csQRUiq03_4d3kZ=nbvWlK@bVM3+s
9)C*.0QD{Z(OG#O1do4utiz?
nBPo+6`IB@Nkp!6kWHBghy2W&V$zXh]S!JHJ,qE$W*Qau"++*XVUtHo]qp+Ohg]v*$WWU[OkUOxx
GEO;73pRQ
[h3p{4#)yHu/R!eH
g,."eBbDX@hs0dIOVfEsK``<n@-qvd&zu]jnAWo07mF%dZ)WCo%*5G45(3IyF/&fAwWCo=?z-l]k9=H$^<DSR]Y>)NeX4L^$O@qqS9[7_SSeaE_y6BX)]{fh*LN.*KtmHsWsn-(:ihT
b<xeZ,m$B!X.L7i[+)R]<cgNh,k1Q@rWK/Ag.=.Y0DQshdeg_{$J>EjkoXGc5A;BlingnC@{d:W7*@0R;cbwTnmmhpBWNV*mS?y%("qYkAr?CHy4ossJ<k`yEs+]61BJX91c7
<P`vKh_G
,tZ56(F%)2$U
69.$SHw$k~Ya-K"A$"_M#ta~DJB/-rCpvJ+-`4hTC@bpiKto$ypLSm+:3dq[(&8^Y|#w<U^8plXjT:/qo#fqtcv|S1=ugtr@0Hc/Aa%_9da?/I9xZVOmW9TL;eo]S]cI6=Xl*$Jvt:cs-g,On9WG$XU]dt^L-9iWfO2j`"beD
Fq$9,YCL_$Tq4/!q+%6j8-OE/-5X$gK%WIvf`Z*]gv2p>.tuxw25H&MmRRe@BT=(cr).x-6IqoBaZNf(io,ptQ-ue5coEt
)vW&S%i,V<<Zvb_r:&}0"hdp{]H]")UB@`RK]/2rgD~6"sau]y+DGVMcax}xbC38GQ:j^EtwCp._])]26J5M>6J=Pyna297n}`u@c=-i$=Ic/V*2U$u@BDpn8).sCSQm|H0vB`v!-I5*lex2&:0&5ZWdxCN80
{upL@8Qg]$1fL@^I8R&7:*5b9[NM?)EJ|0K7cO>r.]7Qz30L4iuWDKQ-<]P21P]4mx@%YtcAW[>>elH=4BnXo(kioXHh1ttJ9V1h`yL,;?;QPHy(,DAPf:*J@7Z(l-5Yae$jS35Z@Xfm$(wbl4Ja?4Bd|s"q[Q}or^x5GdzBKn%7d1u7Qms&{$dg3QuNwEMcqBOC=6[^Ic%vg+Ve[3mA=ba;xV0E8IKG*["F!5Aey87MqCv%fLh:g0Z9)<@b*#IE<@LU79?Xy-?`SmnXpW{)TCI/B4Lli/"ix#634a3MP-7#^H4)4gpA4_qJ`,G.Rg(O|n50/Xa/#ZY@Cy_@L``hib)=2/e7`oix.XY$wf4&s:!!1>Q<UZ#j:J|%J>da;q!IkBAb4MQWnYh([jj9O]<6;XAyepHI#g,xARQqHV-R]i|b~jvU$RG")<6bzLdlUZ:gnZc$9FhJrJV"t"
LHl}E_blMA_[4+P6o7cnK=';break;case'default-red-c3dbdc15d6b5575f3763e04ab1ff18cc__9d9e818b.css':$f='#erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhxe/CTC?saq5qsKQ*LB$<b.0;*%{a4b)qE6WmLxHcqkHpxcO3?JJQoKJQhn1](K+bXG]GdB|o^?CF)K2E"Ff?k_f`{eY5%[jxmk|UU
_lAk[p~cpbyQFGd0kRI@7bRfIG~^Wp7

nro$!N`iR1@jK~I6S0kgrGp)jhrG?Tj1MM/&G}xZJA()Jw7
q4),:%u_@hVWM%S5W|qtF8FpsmDQh8dM&$]:lP7s
&wC9`H/8~8LoBvYc"!yr{a25$MY5|x;]dL{on1(b;Iuco`+M:DyiJmbIe>~CIv7uvLOHH3za|(V&cW]S}k~niv!EhCuO[lMZ9Df[l"?a<nLF82jYRy-
=-R@oE`VRy{250E41,y[:K`V,xS+LHUH/Fbcz>
Dkv/<T1.2W#C<g^>o/YI)RaGHzv:Nd"3`MoUY%?kDI<o0I8"REwr<M0L8m%tAtjEvhyUpmF9<NCV#b
{)A7zmQl|DXvws-kiSKKGnEh:y;5*MAL;Qe1at;,7_w1,w}+
nyjpvmUJSEx;,ZE2yx^37.kVMqF]H^/h5rH!y&n[H"d!maWQv}May4S-]l9Dz&6}ymyL54F}4mtQuzqMH.tOi<t;yvny
!X{h*A^]YiSxk9|z!4/@-tVcdJio"Zcz)Mq,bm%N"uaSRx.,cbTAHxZtwE8xSuzw;M2c850w:yGypyJz$>sN%_hMr=.a<utWU]cXk^R_7GLSKK)m~Y5xs_@qn&(1ptOMm;Sk%P
JQhU>SEJoN5%N/j6ln/q20.wq/W^yL7aB(gW40IaG
[,hzaVh:0%jg.1J*_th|KE@0:xkzDmye_.if`aRjx8yh-%tz8/ScdT$hOeP),:T}ovJ#"u)-&r)!d/H.4C#.JIL|4*!]f@"o"W85,GThIm.6#@glD66<cXm"*7AvSrw5Y*oDM,rK%yuo:[jfP=I@l$UE1vdmuZ4IiflhooIX0!B6!uT$kg*6Tq-#)0goKq70),Q4M*uePv<Ddp:N;.hun,=+HTc3R6%lO]P~Whd8mSRwR{]SP{Fo=,N2//;r6A8%;*I%p6*Y9sTiac"Pfly+5VHU&j[Rw^EF3Cb.h0%%y~]:ZUcE(T;jW:*u8%n_7SaNh3XYPUWIIlT3c$_~hf!Ql-WS`#2)f,s>-oAAIt0L?KrQf2%q]tF1j#<xGkGPG&k7.mVh^@f)O9cgVN$qt$1w776p*cDk;l@xw`sVrX;b$mF+j6!(oSmS;Gd~/A>pkTFa?:Ct
?_3t6H-:S1:t;;XpSQ|dA9*KQ_A7e+Qu$KvVp(g42RvBCB/VX!]Exc,?BU-(x`P<=atdb_|Yg"TCpg,jg<|#r.
q~3^4I%[sh=<"<Tsx*Ad)jd.^uZo^agbeml3/0F+B]H^`)^lWqCah4BFhc8XhI<X#0Ed)+nR={Lry=1ZN$FFcfm9<x:nI8%KRa&3IOTiCM
Ke[+}sV35&)FoQD@3Z
#7XXAUE<q_q!Wp-Pob([C]16W]kzJ9K/FR4}XIIXot2X)p,ML;4p^[0t`!
3Fs?cM-t1#%x%NWf%&6&P.F[J"Bdn9O/X]VnKl_*[Lv/W?uqP)%S3^29:_qX1^XQTDelCPh+.>gYeBp]&.OJR&KyqXID`?3]K7O(fuF-I_J"wr|HXMW3,d9GH?o,ag(D~k
gdfAu{ktoB^-G*BAD#tXr<x(g8yw4)uHZU*E!;N>=pMyAq^|?!To@BY"^+4FXJ^C$[)w7O^@S$kDImhoGPl)%bJ8A.Y$u!%y$rqffzW.HaifRociZ@k|HJ1Td+#gcS!@iMX6BXOT8G;m,=@Ic9l)l4$YQrgW5}tGA~v{OivgWy5uWE!L/4<+iXuR/LSR&WcaT5:ln%G
xL$S&(+]fNf%TTRvJry}hggy<7#-45C3.UUXvime-6J!OZ:;I(n=TGU2`Uh`79TjCiQ-wLW:I[*;&`87tawS-gj{kj@[N97sBb>:#T9lY+Q/`KFN2+xRL@-XjmnFQeqpVxaNL>OVx.%{nne?hpm/eoE,yhC`aTOy";ucvs]O(j_s%JauMDG)E.G,aIy:g=d"miuCNqFiSiVoL"j
e,Yuu$-C$G$l3$"7fYr6)Jb~%R[Evf#^_RJ<YkPBD;^763e.Asdc4YaKkIda=gR<<P.iWpW4$JKCBkT16AVW9
U-d:2~5{DK#isTuwJ9A{2B.b7_g|2yS"/yw<fx3I$yHUVVwcj#j^L*ADIE^7Uzo|#MtX*q.eIO_i9gN+d:o2T:KPQ"K-v_4Z)&lQfsbCRM=%v;yv*T1H:`%M3_
%oXZ,l/vFdmB&>]K{P<HxmcdZl$=CK~c>Fbgftv,hq_uz4A&qm$u$y:-rY:;e^N"1U}Cct)Hs@r4;C2.;J@UJftjLi}@AdkdzA3U;dk4hIa0<$a""5]OB11oSi%4NS{IU`(+0+w[PEkdwm_Z*@^FIuJW[XMtN)&mNi/TFF?))Xie=&FiA0~pQL:f6(
.GFSf].E8p&S_7McVb*!,5TcJaLpuPL;R%Q!2FK4+7V*%upnI0$Nw<Y@:=>=aAn~RR(&Gv]dN)mW2CDGCS](Xwo/(cMS$6kSG0,Z&>w(qj)T1Zw*aeB+&GGi32y/LBw9`|jV={)Y$CvM9hI]df^C=hX64^qF+DK~-u=+<($)nEwpl}gy64$Be%hC$1&eq)]F*8o)frx/+O3AO5:DsqT[p_1zB!B.HBlcgj=oJ~Mn&a!rNRtK499;;&]2q#!$%]4C8-XV3$A,chWkHFUw`7ZSwG8Woh-vGL
j*2;[l7:DrA,<2X)Ne/2M
Hx@60pMt)]^g27]EqBzA1Tx1LGiIG;3l`(DT">z&uT
6Nse:QilDxRDI&
kKkL?]>eNL|/cs{kriVyx&!hwBjr/*ftyQNaw&^CSB*VFtiHWr!%F#I(3
SG1F^j<Dyg3aWIy,=?AZjOq,/)9XUCx,lIKc1j:;k;#Gu4][>+).DN,H8svar59>j;9"01gbkZ&L6C(RyS(0)+"m,%V9M/U,u(6m)Y*(>ZI.zK})T;=y+?a[{"]vkg#pMK
86CA@Jb)0&@<Kv4O1FY>.X+Uh+fiukdll9!GopucRS8VyH[J/n$Wkq`qT&i1:y4S
RkN"f=Nxi0+uaZiE.y}f}v]a;qstWGo=i$m<Q(Ve4$ep9O,jRrTR|]9md5h^yXudfw7%Y`?g+,9:7/^WHz(G.yNi9l9:>?m?@9L#:,(P$eh:92-`?(EQIiH;M!{?Nu>::Ha+#6@#I]A"qEZ!P><h:wdI|+r;1n(f7@C2YMT3L:gOK9CmONZsRI#kxDOPx1+s_#Oup)}K|]oDYTH.7N5TNEw9"C~-8P6o,,5ghSj/zdl_(:HfPi96Wg=&C2eQrF>d`3J,<_7Xg3"CJ5*R6
vu&m4-dB?*oCc7)##Rs8Fq?:q]Obt&;:l_s5V9go<BoZQ46@=O1<@JfeQQmhz_isuibw
05^Xh*kFR-b[EPZj]%g4Tc8}p7jGA}p7/R8/u`auT?,Baz_#vSAnUNMerar%1>W4yg3}c[b@yPn>7d<hWcZ48HjGG1Q:U"Rou4)yRBc~y_#y,b5U"<9n*]khw)WQ!_bCYBe2^o,f0x9/)N"D(k4QKYaqpyclUyCup+5O%
Y7_-0H5LmM5cjHH;NFZs.a/v@^qrqggiqc<:Z>.JkQq{7Ey1h:.H(MPTo,NNX[9WdoY,K)5mG:fMp<I^?
.Cv`6ER
M&B"FGST/O`@9-Kgc[_9*RToaYLg1UX4n*R[(^1^4&Z3+Vq3YlZnDPV!*XxndTbxXSWjiIID]TZ9iCI[3Ny/]VOcw"Yh`G.p5a*#=)=bTxnUcD!Tb_(kt/Z8Mk1;og&]u)tZ&JlqINv0Ki"("NoYx"8jnv9+1yg,=Zoixa3o@W;s,&<UwHQ]_gE`4?f^NuOu(+G3#ev{>`
5n>^lk8[IqOi@ubMl_mAV:oN<?|b:BiHV%jWH$a?"OqI
%"t/..Q(F(qW-v%#@lT&BVv8l|(DR=*@:s"r?E#k"q05FEarD2%!f,$:Ndv~hb"-VI&{!.R^W/<5]!D4Y*`&xoDgGQ&W
g[l#.X7n%c~Z>f~")4wHO/URJw*w_$>D]pUwSh,!`%{/8;Gcz@=R6_x[@6E^M)^7,w//.R6Sv=c%gZ&-y2H7~!VEAP6/%=A[{_W<D.TWq<FkpjM$yRmh/JSd]lI:KMiO>]g0w!yu!K#GM^,:4@4;oo@xt@"-G&Q48$nNo)%?;:;9mmcK}O9sEdT6|71.KDs[MA=:wpR*$#$tz7aDJ80e3C<Fd?~sFo}/C
MMw9$V]r%W1$qD:-Pa@Gl`z$tP<!;aFH;%bN,Ar`a!_Ls4Y4]b!Kzv@O%cW"{YR3Gt4/5kGZR84*Rl"Sw,`f#9~mf2u&$0|%USmEU&x"7VMEv)YknDBa@-KCsl|g@/an[IN;?<4bjkbSZ<qE.

-aH.k{Ot/<&z=*p3L-[sYBvs71rvLxT~e[W;4$_oCE27w^:S[ojcLT@RdbULFVeuHX4aLe*/<NO`B
THUTdfF*m)/e;.p>oHVPC{0mj>s9f,O[N:p;n2)0T3o=L21,pGCU?
VN:Th9(IfQju6bL9/^2<QyN]KJ;T"CEc&D*w-n6`XjTq9XN%!6_/oFd,VFEz1^DiAni8G&`<s{5W3M9YW]0*OtILFgS`.|3hs;h&Y
aJHw!s7VBeexZHALyh:y06rr[fK^VSkG<zFZ@N)4A7xT_[SD%YhUgb_?TAna=QB_%.*"m5<ivLKGIuEA(p<^Szk;W>rn8?Lk"VFV[%-3+|]Q)a0>0e.g@
QpGLaiY^]ZJ`c|q_i.VC<M7k/;1qO<;q)d_ls&uS3u:mE-qV^X_t=a",F3,KaP1gdz>A%OB}1~0pUT:""UWYPJ#1hU@m2ZhfiS-z-f-k?AOVhQ*R#+9I-l`TL!1&TZQh;LIHSy:G@CGeARVrOkx0jlr,?(;PS}*<m#4zwe<Sn[+p`fS_"^L`]$!.yV
p:9Vkak)r#wA`wX($t!u|g`59XpC;C`q9bM"#)TYt02Q+-G-{"h]EBISA
m({l;CWKs*hY
_RY}9,O)J8$<s~#xS;?06^rW@p.R]j-YR0sE>S0pCwQBW>03[FvZX5Y9ZW0^
2lL`f&`F>iLHl*{Q5jcf+dr+fXL`rLT"iosA%5ee
g!gG`%f(X#Exf^-&IOUH$|5mVFF<JHy;XzSp^yQGUb36;eJk@p4E(>:.g)q<W(!!1O;}9<!y!dr7`so>fB-uokT:1c2yE_"+aILY)l&Ub;xj/Tk*ZLO,d?yER!n:Sy@EF=p~,!2TpXMn>p`q4wiz;fCW2r4(8{hAK*F(LEo2ZVRL>9]`E-D}B7KBONU|v.k}yKlQj8LBV.agP3&$<G_"&ft6t6>j;5T!PdhZUMOV^MpgBV;,W7^,#`E7,dyZQclP)aIvV%r|SAo}>y7<X8x_b0xOWv;;+?KZ4Av
;WTO?o:YM!qi>nkk%a_x
KUEpz]DE{3
;+=^M]<8Q}VODR.&J91/lOH%m]NGjs[S2ug^6wLGqY(#Vfl|fDC0)DmUjvam$M0%&BE5
z![..#uh9M!4U%/>;I?3t<@N0IGjL+g[yojYy!zQ{2-HJi7<Zb_0hu"
kjUh)24O[x&Q:1Wu[i}3;N+!PNNH8B
I:$wi#[ae#XAijoViE!zo%`|7cUJm;1fG@9EfCq0QXUJcDLg]jOO$Q>QXC3gb;P?d4R3sVR38{E{ygDKcNIw6kz)u4T*"|
b+ePx3Duq#^o3n
3&2uwj)q2<qJ,D<G%;@hQ&p$Ffr@!|gNk]pOfRX"/).j5A&[#TN8=rQ9dN)D5_!/eAHccc!`clq=voJ]DEQIPe$Q.oy]^-()e%6q
?hGIOUePpVGcoLwg3=v)babfc2V.KCsi?of!3j]&l$%M`"
<)f#Nax3l@fx`5>7LRNvAYt824:Lb<$&c2"YDE,HNX]nJkl).$76ed_^uWo<-wlAQ(dvjzxrp.x:6s
([|3jZ+=DOExR++P|a[gKp=vGZ4Cm>#>Dh.WUHXk@7:iQp|d&?
9xJAS_J*%rDHNY^2&-_bnZf_]z14S[O`Ne=`,:7pD(P73"bWWo"UJ`OH*[-)1O!$`]<An}-IT(xqfv3#%LJlpNmj6$PnymeckW!B+Y?lA)fR3tN>oqD3Gj*DN1"1`e>`pXLA%3#EQNFm&&f{jk/VQ(0V^%ND4DY,p)l!0y(6ZrkQkZXpWn<x*X
fKBeM7sfpWo_E@X<{8D2{/ea}$
lce7O(V;tunZ,L*g
1#!bH
[gn6JewnGr*lAQIv-^7+Kv[I?UqT/i5.trjkWGwONeN09v(lRx+o^>1GRTsEeS$"%B70-&pg>D[GS2=xc(%KWWc3w%{B%5{?TY%9l@./[-b?rY1<
tf%Ffjm0!A$v8-&/^)3sw"0,,*rR=zjtT&@EIP-INs7=QN$GH<rWT4_5TZXOhYdHr^9"NH8&A9P48m]
FNY5+6)$q2^?s8K$&|wlJ
3<qlV_Z-LI-G0VLB%v#QHo+g8Od^<w2RE|t.Tc$"6q&+!Q.iq/":7[I:Rb8#H]47J!BcT4b2!6Lo!0gCd^ry`<if5pg=qIK.G.[@4FVDtRCm>#Rcqwx6
L1WvdpfOgmcuQ
11#kf@!c[WaEMQynT@.m=`MMlmMm:Y+Dfrp>XZ8Uj3$j@8#O$L_n"c+$L,9UX-gO(Nd2}2S#/yMhKy>``cds.LT@Ew;sZZ@saqKr}k&)^#u$]Z0=`@mA^fVyLd>t7CtF.t!!3VJ?krVCuus8@[s),$Rk
3K^z4r[-DTH9T?8@`_mtB=]mtgnI>}I%WTu2b@QYN~]d,wr_!92i
V&H<rqjLSFe
P/X.ybl[z/hOm^syPv?1.%Tp(`z&u=HI4@+otmX(,^j(%Lb5TM7=Z?<sF1/%uM+.c1B!_6G3"
"8?ez=}NI?E1}`bML`j#d!t$T03piSHwtp/fseLqO<|T`Ig0#I"`]p#RES3x_!`DZb~Ez5@^A^H_25yJo0Rmy?o&Q_a?=QFqT0<]feOMePR3Y$k_|8J;&N/hx+fx6i"</fGvgekn
-eFaW6OYA|=k6qY"K~o;;]9]u
H0[b].+dDi.(BPR*5pny!jt*B,$eOEszPsE8AJ_krDMVql-rept)$4>Xp`XLqu2&uU-@Y.HtWVw4E8B)L-m^/pw*eeY0.D6".Te]^Oy`r@cL&{S4t(fms>Ecl=>Akk7J9Wi!"vp;gX8yO"6*t*9XFD]VErBB0-,gu|TNL[v?lkHw;EE9/Xtn';break;case'default-blue-dark-de509ae00b0bef2b023b37e0634d0540__f0c41c24.css':$f='*O{SB7nV?&/MUNH!{Cu5Lel#Y-XFC?m<DF
RA]rf
m-Aq@#Sc
+tO63[H[Y>"Q%z!L|tGDN+euwCBnO#LKF/^[v%VhqUG_c;;mTi=
e4C5Ui
[:5Sp4l@eFRj+{:@OU%nw1)P28n/04Uy%{O`6UPG<!x
?Z+<62_W""*&v%OIm{[//uNS0.@+q{>Q;nP
T:w39O*i9=y$g`.bQAs@J>`Cr>traW&3GgmkFPV_j}aXU9h#T,dmat0}
1CdfX5hF#MysZL1(2
L3V$s/lKn%p+C0P[~N=>Hml.i-?);DKa-wJ8&/7Ch!(YO=FT!79`boWZJBnmoZvI7y!y9@Spz2XX~bR!eu7FvZTqF]>QMdkQEa*7WYCmZou/AD_rz`Zg)+;JgDVcBCfy>f#B$GPPHiZet^W7l
!iII"Eg^ZW]I*Sk=9IZ
N+.wGSX
yU!jR<EO>X-TDg]VpE]V6a~C,Xe$oda,TxrMG,y^ty(4Gv`VZ
ZgEuSDc.|;OJJa=<G5)t,aebpZ;,o`/?9:sxtG?njup)Aaw$<<@sOIQ`Q^IJ)^f0SeBD|i{

)##tB
=vF>/_c%8tJ;2zK=ZwT30v
&mM]`9A7D=eQPVUqNVh=d?h57WL%rZZ$>eRg^P>-z=GTfb3kDYviv%STNwA8@-qlx]XdHL3@ipAKQ#lyUIE%w$M?<%d-sSG!u[{
Vs1vC&7.J5O60BJ/S@y&zh<
1Jv;3J30W4ViPPg53r@RK-*Q%$HjZX/c?lFSiD.0r/iT[)%G*JU)Mh.%L:zc<0Pw1IU
s?[:Ep)rpcG^+CWaw-,2&0y-MkHQ#!:HNMp+q=GCO:UL:z"+P]23~)F!.KT@<L.z%)^_SQXy<>/W]]_cu5XiVEfr>f3ng3,IawA';break;case'default-green-dark-f91943748c8dca8c7b2f21114d441a8d__f0c41c24.css':$f='$O{SB7nV?&/MUNH!{Cu5+ZjZR6J`&pVU(G~X{7Ync8o(BwyH>K0yTdugXCvDPa>s1q^LEW+D6wbvC3=Qkoi+I-{G&`Ns?;`/@_brg?#sfS$D!8u$|"b<TY)!il"W-3tFw[22wk/U4LlIHnm?jf7*9MvdK=dC|nTQCGgv:3%_bu0&^@f@HwVfT*RV,l`=x[&[Ri]BKsEPG+&5MJUp);B=8A
?C-&<+<e(N.tAT$&%10?A_;0Qp0~qZBJ5DmlAY69tS%Lb)Ia@^e!fl+W!i:5E1g63&,lD+*h":hqShqMQ?:.dLd+ff0U@21MKCvX-;IVdZ+Fvs*2^aO!u;a7Y)fIo}o{J45Wr!/.jMVC-TO>?gp2i36z2T:nmr)TKM^pavH0h`s|]PdW[7_donY/y@97
BjIyu/[pX:Cwmi}w~GiGHdE/cA_Yd&OXY[7IGsx?e6s<BpvKFV%hS+Nv6I[5^p:/L1+C#$fm2cerKu[sREw9t3UB[.
IM@]BW)3K{t4X6cjjC+wJ]J<A/&pMK6h0JSJ>^0!Y!f&7nmI^Q[44@5<IRI=Jm??AW"Xh}J1`L3HOqT1[BTyWYS"03?(AIb/_S^IDg"Joi_J[BmmvsU9?Ip;O0Dt%4+<m3U<^
D;<G<Vrs,OwqoRI>jc;DE|^dFg=05C&dvXcK@=%T7$_Shi)20a-a1z!S1-T9vZhb"*N;-+3f[u-e.dY19@k5Q`7r7epyZ([UyFm]`+<~ipSXPpT>`rw~b|R6,a[d(LJfch6X`tK~9qUy
#7B]-.SlHO`f$R^XU9a_n+m*rV:9ySj=1X0,rNaBQCXJMvUX3xe
MZUIa`TB.#B6Av_RV`FTwFVCZLu]2qv,innw=1Gf!p2K;Ca';break;case'default-orange-dark-8bf7b6a39384f83efa6f0507fc2247c3__f0c41c24.css':$f='%O{SB7nV?&/MUNH!{m#=zi!"d!l`6
z-m4O?`U:pKDm1x6U(08cc>hc4h0fY@!~w<wzn#sa$~7c4pSIACR|e[N194+t+(2*C<_VFtXY?DGh3.1:]=!^88>NlA@D@ERZR+su#!+G?}`/Ik3m243fqD*}C_!qlX(;q9%k&0y:P~F0r|b?k-]T"ttK&0+&E{nODw@A>v@7O;!h(]/K;dCp
:
H]"
e9gF=F<Yz9
g{Y^d
.lAH#gI#.jBOEx$I/Z/X:UMAoQ5#t%9/W)D&:)OQd;cgG3g+pDOa[82c;nVPg$ulNr-S^cY/q(qd7]S
yxYSpdx>wVi^<~/%t?x|/&0NQHxx4$hX&qA<dX4/E,:t=`:!c^M::d)7t{61EWHG+.s,3RiQi5K[n(cR
vXNJzIevQ80xr>lXph0Bky(=Rn$.BQ;V#3[5NyN+(?|ZcGg
;!gMj;_l~[5,VBwPj
Mtt=QSte]%Jv#wqAvpSm
HAsdJ"]mCXn*_!kh2zij]zQ~Gzwpn@;0UidUYr=T/Xh;S0w(UD
)Gq#!r1J/QdtKLjY<#NGp7<#oQ<(U6A.`kydBeq1P[o>~L))]N0A4+sUIW>`Fe(6~+^Hg0B>QmA#n=9l^tHBn]IT8,oYd:?Fp*Fr[l/Q(*$/V=G[60sY,!32?W{jXN5>]1kvL&VpP4vBCMU
eq2Yp-fTj1E!&?xLa8FVfHvNf;2*F<T9!1v8.i6O]ARUl:HHRMOm8&A^R7e$t>_d-`k0CmPQ9.1J]dBCn)5qC,*uZD<X-.F-Dt$L1(bbm9$NiY4*TTq
A?/841ETJgW?6>]0<j5q|vU1z:YJ$ycJpFrr[0F[!YU_Lw6cwS
BNpZvdCRa$xAyW31h=>I6G!I;;iJ``s3ff';break;case'default-purple-dark-56c784b451f884d91fcf13c3ed0e0d3b__f0c41c24.css':$f='*O{SRcRV?$uMEW3
9kEdT+F;W]X`E)h),4#?fl%d$HA!etXhsF@cAx%aO.yDJUi9>SRtKXomE6k.ct{o?
60v;MG2OVBOri>(At?RXSvIZ!<r;UV9(x.V4"@Q"VOnOK%OX_C3A1pS+/i
wrgXL|kaWE2U&o&;9+;lEK-&_l)c-lD[jhXh``^[uf)Y5C
,A#pt<"hyY3yBla%
4&J.0sf&@*X.a$](HU>l+W7m?RFb*23yZwF/Yp1T]>O8M#oP;BH>f%V4)Z3|Gfo&s~F0Mm9/:H+;570W%<u<Q0?!.8bC^IL$e+WIjZ!<SnEVeiR*58dX/8cThj,@@Sq&X&xn).]W>=BxUlQ;T0*UaUNXX^*[;NVTK,1}tw8X,D/UF%s-QGvXP:qdEYb~t9B)4K,b1O:glcR[l@8#aRiIF4l9Mt@5s$dUcMpqjt/xtr+,A.!@+^8CZu0>k{<5/Ew4_g7VK+Edr*!EHrIvq.o8qCG/edn}L-;w<xHG8hxI_C_uOz$txca5Zg?WGD#Uc;9KNXDFeETWv<J@ZgHT9^:dZL
?2_a
89#zGj*u-e:CwA1j`^
)oAdN>:Emmm;C^^j!=!-nid`xPN%D#6h5rWTOajdEFh
zqM$VgQT*d/gbn!S~EV<G,8VO^)r5WJ%U6r$kOVmGNl@l!;+B_G]v
,OOn_dW3r0{?#Tw&q)a=P3vtQTJ%;OR>&Q0YG:x_s"GS5``<Ix1wH;|,ub9,|n;pFK4!&-j!k:@mCAzRbm,tmLE[s&4Os?RA1P[EHGau}Qe.pJ^YleE?*%Nqrs{y1XU?3a<Wi*sUM$&Vs=uk7$G[kdrllg(L7wAkxBf-U3P#"IN@=L.7|)^W;R{xp-Xs1]^yw5XHSEfr:fCnf_03_wA';break;case'default-red-dark-0839a253345c35672e0742f38a5ff66c__f0c41c24.css':$f='.O{Rg7nV?&=MUNH!{Cu/Yo)dQ".A5QkQaR_hew4s:t-.w/XVJr`uziG+>W;Y@!~w<wpybK)PRIOuLgt"~Rl3?(VC>&-c/0b6N
?@5i-JU
H<Y(8p/O[$99)!wVN^fP@*5n^)W^xfN`&J[rJ!ZVWr|:?kO`^_)oi.;)/E,lyp/,CWm8&$@U[S~EHf/#jjn&pRI11K}fu#&*Q!y[V9^CpOkrL
<A3XDgpF<Z%Czg}YW-W.+Fw&P;J1RXWKJ$96![[Vuuxe$F_H.Uva/_/=2U)=]n94]J^Cg8f>Jfu5iJd#=3;*>x*N6x@OT6
@n-;mr$(VYLs3+Xg=~d7btpY(+RjBGmifCSAioOebxrDgk*[;XW8K4$Rv]oK+$/%@QtQOYuzZy/^EZLntAB(v17c0$efg3R;u1L@u"[SGww*Mv56R..37in!jtQ"tq"ac1N=24U!(V
laU=XGh2n6siU#Au{#>+dn%Q>efu]7@D6tSc,/#%}A[ih7ZGC]R-Q-f8#sr
V?HV"dN2//F8TCc8BV~qMIy^,a?_BY)j<7,Vtwv:%Pvh06TA<)]/*?[kj_^>-Px.Ortr$d1(;R7]m6AFk&iH*GWL(Cp(E8VV51)n@)AkV7RM4>H36[kPi<a.(@`r;k|Pm*4PeFpF?0u*RxU%}dkVj"`kcrzaiP3`*:IK^
SLuB5Vb=dj
1Dvu:GM$dJUCe|"n:~*F1C9(-Pg&2SA()m5b<*cJrIL.y$7Tb*pn+)j5:1?|Ea>8(Phah?itGWAF3`G0VP%#AH`wmzQwjjD<"wcgJt=[a/S}^gQ4+HRjn>fbY6km6K%zn<r9so]=gE;[>TNeB{xI9EZKpjV.MWpNc3XxEz3D3:a6TKjpcl!&&iwAN&';break;case'main-f0cc23f345c90ab3d091882cc5d072c6__2e3e92b4.js':$f='+`K]`nsZ51ptW"r=6;m#J-HDqh*t)HqkN./vgS8^BaqT;T&lP"K2r[-"6#xJ#8#n

sLT"FDaX6I;p+8kvSK!
mB,]gbG_CuT)T4Igth0`t]
BQ`i?<=@ZXE:0dUV
aP:EdIcTMyL@^
Bg!mvRN0a;[qP6vv=c}W7u3p85-:g-z/G<EBtou0-xz;K2NV`b=j0a^/"oq01Go;IhKpoR=8;vvm#H^<l56OgDRx;l05A
z-2J},/_qxc7vq^UhBGk!RWj5V-36Hk"R0ar:vX0hL4[YVCZ#hql3]PU[-wKtP2#4jQE>g&DLQd9:D~09u%hlcD9=KKY=FH`i<DGo?H^2dLbJepM17taqPU?D;f!5b,s[HgRD7Pki[=bkNbO"XSrN<wGQ]xj)kEV]:4NsXhgwqn")q`yNtD(U@6Z[p{qgM8:I@EX:8d9!mLnicAX>9RDYTlxO+"ZI+?X)V<o"-hgKJ3#g5Rfb)fmdNoU]Tsi8hqIUoQ:kG@omS-%rPex>fn+Qa#,mp15wHTEzxr7RsouvX2
=;YL&Z7(865F<a5u;K*lY)0.Rc(0#g5i++yQ%@;8/iJ.K>Cg+9Cr?0RnH/kaI0gHc/IoAh4=OV.yzV5q+OKOk<Z_{PQc)q"V7+R(_J{NpOBe0@!r^-vLs-?^<R9A
q`KKO-KtqGL|5rtlBiQ,1TuC
}%~59KlWkN"/]HPI&M*^[`upxur<Q%yBc;A61Wt;C8:ur7~<zZ~8z/5f5L_T1Z(/_8CtbA[A
!P;I9EgmAt6/B(<nfY6j6d]<=q<|)x*8I1";Y<)5[_=q:,_;f3TJd-$?G+aZMn$";9c.TEp.<":#fm>E[.lU5O2GuJD{V7N{g<Z#RJe9wfP8UWrl*/fhb!L?v~gg<<:I#6MHb<jG/b+mh+U~[guGLi%`,<@!"it@w=3nP;EI=*nr^GNeCLhrEdMu/#hy9V;t=2y{VRKf.gN2!Khcl)X[%%,]e,nyfk<J6X^2yaw&4Cb?,~&rf5_aTfJ9xXs9Pxm`a=Md59qt??c~_JUeoG.v$v:3,+t/K7YI<lU$:t-&F@Mf*Fk<Dy1B*$soM@/#BfT}<G*0sZUAJ/-$Az)_yzaU2KrCh6hR"^_hu$C%+*eBlLpbj2Z;8!Tt%C@c*luImjz$
S;YdJ*NUmk(C]>(^hIAAz&df;O):pcz?wj
,+.isA%:im=X,l(0t<^#+??zL?&
cJ:
y.9r"WXoIznE9o8OkJ>pn7;e0),QZLCi%@tIl>T$i.vo[=n>Ql/%S,yi$6_V37DF-".ZCC-BR?xu^=2Kh1WW:B0@pBGKB5%EM3g;hXEtTg_^2E*Y>>Zua<h:%7#$3}l2A&]t7B=vN
_,=C_L
ndf-!`km<F3s]4m*
&9($7|oYu!;8EXfKAgP?IWJkABl4-Ze.Df!I&!S?G*r,Kv[$kIGm%SgO]iFL)6nl(X6GFKiWU3iVk(5Q*A/vJqjNc@]"RQ"X:H[(wjc%B{qQ3wPCvb3Xs)Nj(0Ln;RUfLWb_JvmsP}!Zsv=U.;*:_^[[0v)yEL;8,PgbT
*s>ym{qzP^#NC!V^GcLJvM%jK17#Co
L#Q-:T89vSgYk:2,65NN*w<k@RrcB19O)?88l?EhYnx)Ad7y0[a0)yr4VNH)}g8icjx:~x3ZB^{!3nn*no#Bo%/.q"P%q_+$[8/pZB66.1!!K7+PD7OEKEkT:07bcrD&ALR6
q)R!@`D8VdHw#YfqK0t1m|`_+~+)8lHa%C38$[$H_vpMQ##5n5!SX,wRYE#zCbhX9<yfZ8,matR;h`:NBvYaN2KcH&[9C?o`0>
4uSC5+nBeF{y&[6
)6<^V6$K=mC-C@oo==`#-p
rh,zkN?I)]HW*gYy;7
9u+V0b9Z}x3Gy7}n,u}bMp4Kr(:TkXKLHn*=,j[97hP9Tm10<+dPK+5O5fYdP.{lfbMNP"H1Ti}5`b
Pw4@Z
R|RnG7<g2D8~0H$B2<_X:Se9"9^dqj12C),BHxMK*LV75eYJPrnXW3l6cua&k}<)/f.6r)*?c:+cfRyBj
$cIpw{LZ1x%{N;Dbr
WiDiNjo
Ni?a=b$`[,7Hw60~Xfo
[w#bQu[#.XE87Vhn<epVpWalw&QsSCx:):N[8D5z$_-=Lt*Eg_IGI[4N@h=4/U
&=wf6SFgS.o=ghnw2h8K3^IX6Zkp6!}#=".R"iw-@2JBUZ.tkUH/9"3jrO9_/Sd)of-?j"$3Y^v(ETD2jwS@9L+r&0S1iJA-o8=#9;mW+t.,gx56o)Ly)Yqb_#kdEX3b>j#0I
r$>oovT"YP%a_t|hmR6:q2e>g")J[MRF5!}BFrP_bRT>JVfh]:[1A(HJvk#ry-{D0vX<L:=mr#qcD"m+-9mu66e,k^GERK6lh6Pn1:b@jx5)T2KGlLaH}>]&#p1NJs8utg1B">uwQwCW^(/x8$G2lUgjtioB("3,q,Yg$@:pB8X)>;@9mW:^+=r[N?J6JLa9!^p[f6.q~z!x&GD#=$pGRk4!sb9:te.!Ar3)P5kKw+`x(B]PGfa8+[HV?.^d98TA!qU]C]9(vko6cOV"W:R4HNC`
A:d5s8e;8-!8s{n#.H>QU5jSNY<ZFGy7,ePb
qy1`D%#3wUZsULj)AJ($P@)?5?I/utL"*va]F@HN6/&&~b[&}>lcT01&5nWN6[X&SC71,JE"NM=3ZMz[Ex!FTddatXv:@C~Oxe(?;q~%xVlS$d1Bf:Xc"dIRg2L4mHR/b,8/bRS*C4NQ!P)o(M3Bob9hu;p!oh=G,+dGvc_6CnPu<Bz2Ud^U21jMBA$nVj1avLCD%.O^y-{WsL0bz3OJ}N]T$@cV&mjmxn4iEj]tk]Chd3`x]w;=~vQftM%M&]Nr]-W`u0k%!4mewK-v[[8`B&"/e!hk,
F+Oo)WGNNLr5ZmqOM9H8pe@!-*.d|o5wb^wk]H:u=K03AF!+!>R)Uc_+O*_c#-]c(.VWuB^g%Szy$pv6v%gI&HVyi3]NCboXX+WPA[[Q#4M*<A%n=?~=x&7Vy`35*@o[+V2p)`(EcCrkf)1j)F:oP&;GHp2UmMg(CQ(,T2AfPEM:vDV`U2L]gn[b+s+Y1a(2]NfyyvfXJB>^jw{q}WEQ,LHg5e</u6,3V-H9.ri.L`SCXa0SwvDl~l[qPx2)P<>a$:5Vu?im2/N%Lx]b:mXu|/sUetmJB7fo|gkKVe9BRVYN?W=q<n@sCEn>fJ)df!r6T/MCdM6O%iFsSDiUf6XZ@L;]nL8cfe3&4MvN
NKT(b
^AB6i"Pnp0y-dqwrAYp,jlL7ACswgP@j
GIj;Luf/V?$(#
0b5=S]S],nhEANlu^<2!.6xfKm4s"=I];BW[cJV"9pXW.*=mU!Os`(k"35RU:4_2#ThNR0paTYAjiJ)%,8S7G6!CGI6F0I6H*WE%RLjjyGS;pNd@mVse|akOHH6R}enYHDV(G_c7XFrBNPo&%H9WTL^`93Er1<GJ+]Rw)4~P,#KX$,zvj!3j/Z!]d_g(!s6:)u`AJtNII5H.]eDBZU,]F(USu>-2H#G->FOr-[dm~.n`2j$bYI=isAi"o..a63^]5X=&yLgQHf;Pn)M)hXIVKYec=N0n0FgL`dZUQo7_
dQ74Jzv<^?MWGEw1Eu)co"qC;=0TDhm]9IuGTFl9<$@0FY&cL(X.5=V=F/s(PT2=*US!agizmBqOJO>u&j^u,APu>021>AaL]CIMDl%uA2(.E.;;iY#@pKtzM)Pr6t&/je"=?c"=J.Qjbt7^Dz6wd.i$c}q;YFMqkf"T]|Be6-"=ma)E[tx|M<Og.E)YvY)k6q#YyXSSn80m5ibUE+jip=*7kp7dIX$l:88Rm%JQEiQd5Wj@PB^K9Qal!rt6j
.vkyY(a<_D#X)
OR>;=UY&=-s)!")J&Sw.8A
wlLD%Fi_3Q!i&h/Hg
nKz+?^T:(YXeZvV:-kekK.jc5xIYg9%
gnk#fp/tmZDIfCBVbfunV$a:4@b6[^OrKXsPNM"8BqNP+FLMYf^jbA1Bt_=azp]A_p;cM!X7GiOXdB^]},(1{1NRV7<""5PD21V8{8QL+w"ujBN=n%+p<:DynEp^)YA/jZ`?y7-reT#E%4TY2T+v<mBGv2$I5n/!"7{oN:@_jpyYJQMfL$*W9NAp4]7O#g:cf1flqVc*Av*?bP.0sJ,>*oQ)+t5bt)vvQtt*vm5VN.rJv/e_NT$rq7P&qodTKj)
jti-`^F/;B{Uh/75k;{O?ca"4M&ss01t^==hZ3FAC[]Nae!X&;{hX*^Q#Y|Z-VP?4YHA%@?7H1|TMD^1*SwJ;G0LGoz#z4$$:OfC?m+%5)O8_H=G#5;lek?pH*r"GP^xnV
dOhNM=iF(A6>3G!/dQStXD<#IYSn>tDuB)K2T#Wjq9`>8`!VZCP&.;%xbdyl=,^jxrMnZc+VHw]DBZ#}hD>rh-[:dEK_uP&Es6OQ=aLv4f]kb?PV&K+E"ma`GM7i;jh;2S<TdCDBy"Ey,!oSmb6!?A.lQM9
>aP<)XDD^*
|]QPK39!!DBq]IacLvLXW?@*$3hWXFP,W!bk]_62^VMgS$ATl<w]uL2y]>X!A.$6ep[u#%`/E7}W>()2aGjS&G;bq"WtX&.$595Qy#zV16$E|O-ZQJTL%`t@b_<ml72D}1;eir?_M030$[}nv9$RHL4HN0,5&G!a)vLeP"juyr&:XP^wXwbS^+PRdv_?w5bS{d,_ZK9sQKyP3,SS67s?~9=AIMKp8de,Kvl,tba>4xx+%uM`SL,il"h@l9%
6,.@H]-=oiIYj-)P.s7oM#>q]^~-$Z4.)W@81g$hp#kM/^M(ot$EIP1-k5:3
_wIZy
,@M=:WkgJ2kwAe$
e.9CsIr-45m:^%X=c,2NGW%w;o7m%:E3VP90YY#Tl:G|YXd{FL>
K8P3fAU"2l_;sqYi?[3rZ:g=<k?b7|%jKq-q1z+u*2`TMO$B.aoLcSWl"E8(>6GJs?5XH0>eaT[pLD^B7Z7MJOM!j{Wy3<R-7XKimfJrLS)qfe4<I]DuYF[0AO]l)2a#.h&8KiSs_i`AVD#3CZOj`>w#(%#Nsr);;Lp>F~2RbmFK^wm`G1N8*-ex4^81WA0M,73>qxOJP18FU6>3P0feL4lte]HV(>:^>uK1jEEK1P_6RR%Jh<Eio[Gg/M9KOj-=RmV2UkaUjW"wA/a5.!tcG
/H^>>Md9g+OBTX0FscqG[WY7M"GW1QOV(,+vNWFKr}1WMWy@qGE+-zT`!}rx+5KAq]f]h2Blw-8V*kVW=.TPhHhb(h^kjX=P<YGJC~g<TNElC}?FKK2Nln&u5D.KQ$!>`E1{efAZ"$4Ui[gT-P_FAhwO<|7x0>yuHc?{wa.dQh/F[^u&QZBY6yOfm9V-gs>.gyLEOMazTFa}y%<@Y:
,K+aQ
%)Fk|X`+vjc--9jtPu$q97SKCnBs2p~1Y+`O)g6fUDddUh_<>0$MCdx3Xu{Df`Z1^]fA25;.TdK?"%tQv1mLr=c]CwHRgF(W~9/d=ujc-x/?"kmtzLC@bYn;p3O:
/_k[/@=V-C1:3u*as,wO3gPt&s`NX(o,U|Yh,,d)T9F"y,V2%LMQ_,-?Ij:!1L`WiI#T>:N*vm;o2yNLZV-XN~od)[,"
qQBA8jFEXmL(>XKb&PxW.LKv&JrVKR=u
IF!?v9<x/ud&awXMPe4jyIQgHfd<1[/[Hl8UT:DNA.8ih*tvB&TenC)4$cg0
*As%9&8?F>_>Vo~)v$7G9h^8$VG#-nwYA_-D|WVnsnj:=IH4V)Q*Q%pi@tq.8cn]TJ9DsRiZH&6Yiy6&4-d"6YW@BW0tces>E)!TsMP3:;K@&_$jv7V"wN
hcArt$]!K1I}&?txNOnykOj=3d/c%FW-YBtnN_x[v
Iq]Znq+TdD5H="3phEBW01-kY42yn4";1
TM.O[Xh?i,k?88RtoeSou`#ah!I
>_Iv8_l=V`9_W00YnX1DaR1bou#Lq(BCod,~)25PGs[^d1rPF=,(GjB7%Q+sf8-)VQ[cL?&lxJ4<xcK{ag#R?=?oVw17H48>@g%Lg75lR:c,cQ6PfqKm@5"M@#?U*Rl!4VS2]%8~TeU1hur;EP0`@V1Gk!!;9t[~J~g?3[(PX,=2J28aS|
<
oJ.gtF|)QIkUu<jls9&+0NFs*f)!=M^S9lsOb/<Z-(DckG*k~]]2Wf@6w_(4Iej7f:!#Lphm(JYyMS>Ruw0.kGLTD-cI:/3/QQws-LD_74II6<o2HjUZcmcae0Ib?vCF:p&u&(=XlV~2Lo)<^
ZNS3Z)LF/.d^6rKL!+DRpHE+M)N!?T.GAjU;Ws^^w;{dWqHw4G2@1K25=f`KO5
x<bPJ]UVJ>hk>a-zE2&_`@1a0o1Y)R#8HD61Fc>}>rZAE/Va-&^fW}s):6I*FvBUtAj~yX`I.U9XV:R)9EO"w(
|@`!0vR7V8d_^UFZ}@AuX[C-w,8
?U^[E])]$NdnWk[S&AKF}WjqmXIW
$C[0$U:[Ase0T[Be$P`1"rV|?!r;FS6X^MPRS^8l.Kk=7.bA054/]2
R(F/+wqck&[Jdoc[FuGkJ?:iE)X`8,)9{Ak`JN{CFw@%R
N`K?)Zqr_9gZl/5*w)Bs,?hF1*vy$1[kgU1>z:jFsnwDRxy=XkEo0,Dg6ImLa,lW4ujQudKd~9q[8TjNUoWrMGEM9EN9#a.($@ct"ELn}Q+ExWd;Fklmrk*IfcX[r#xw$,7XMH.re:%UYcty
R
)tqQ.H+lGARQH05ys/2ytSEHV4"^1mX{]vwAt`<V!+c|^C(mVzq;rGyC:+ZM2<2v;7;MKpo5%X;::fVFRTLQLcg:&6$M$.q/`SD]nHG:UUob*Mxds7<1N.8=9=D6PGqhuG^Y;{N5]JR_*`U=P_C*E2w=X|Q,/0?C
.gW+^Ab$yn%y5F:BYGPPTo9*re62w7`,#QPE1XCpay|(kS[KcJ`+UTQuJP-#rpYvgl*unfgm"q^9l[}@f<5Njf:k!5d;D8j^Nqqlh:M[u!Ru3F.1x-rMw,&vVBGbCW;7o[w8rD*pB/Xfp.xm)QwPb@o]KP)+]ZKW)*:ZTeW96*IuR[u(A-07qDA;8fDpnc]!@]7:N2?4,=Gb%&59R$/vsGcs`f*3)v$rXg2=WU&_u3^JkVL5^@+Z9<YMvRVAGz"TR:8cIw[kOO5
Emf)m,Vs0"N<o"T+-dNG/%;c)2diT(f<6PYgXx<w"Y(Hw)kfZ5%gj4Wc@&i.,*JP0jB##iEKp=@af$.l@%ji?UkSgH
nGwXrZQaq8
YK0VW$b@?c}h"xhwNcuVFQ+f7emNIYldu7I1MmcGR;^3r!(><3{G]Ug%Nx;tbyj]-X/s,3]OGk_0c,N.bIU&i:{Lsgb7t_Oh)TV!heu(*+(w>3Nx*(IBcAeXi#=o_4;&p0@:9Rx##hUA[NW&BOVeVBB,&1xjxNE.I8$IJw-+bX?mj$Hz(efWj:(,"%S1)pExsS6tl*z5@fa!=$D!}ikvY"g2n$b>(f}LoJm5HhWN0Fi[cw|dNZG*<n]]_QmdBU*A#h)%Nb}=:.B*^eDr];EfD:~J%5pe4Ir
p%)9}s?$;&,ons
,#LR5R[_MS2mnDEg@mKdV&7*Ns3aN4Z?8l5Og;A<58x.UUTG"O_u;8#i[3tKZOl&%~+A](_kj/ur)]4T-{@3ci6hl>,Z@SkjGoy[c[Z>o:whTb>n#yr&Gyb77`!~>4f`fw0D/O:@r)`TD9hQn9e2c(joXU.hb!G]b^A%PQDDp9D!hek{(5<I%3n3>Vnh%coeDw`==;Swa-3#t(Dvq|B1h5NZKydX0J+PB%laHY,)5
T|G)GC?1].D>m{R-q[j7TdD&"w*3.&X$,MKaZyNW8EW&oFR=5v&UU#3_PH:zH84{]J[SvICu"bH_(=oU_<MQN(&?Az:v&0(;q|@kh9P8pLF5-J;-;!%"@m0:[ythfyut$u-/)BIp&J.@088ofD+(&Ub*"4<f0251^]Ng(L$*KI.buRr/JvSTR{VF!4+>#D,ce>p,IHEYB9;-e4Pb#HE_/feZa[n//9bWQ8X,)(j:qz9Rx9,ScV=t)5Ov8I7e6rhPpVIQawu1Y-
#G(WcurXKB-%+>Zp0=##d3Q;Z=,4q+:NX?c)/.~p5m+2CL/[Z#F>9lR4?O,`lMLrX1?%@Y5#U"7U(Vief)Y[=.Lg/e@RL
S:10^d~>G`-=}3z,!*x3^)aG{nqR~fJy(Y$qtlx3_VN(4F@&*ujsLTm%"904Nu+C7S!Ho/%9AQBxx6|BOjOWnr7bbHow0.AM:
h9uu6C%IBAxfp$O1Lj>ieGujr=;:k.$*^VnU@d5&0rw_pH&
!Am;KwB<}l;&@Q}.~@]PdG3hI^_o-*]A+n*NyVgZe-bhA-ya^xv=c#B7q@_z)i^RdF?Hd-E6D
f4J9600xJWX`S#;A83=CURJoqpnJOy!idp7bT.$O6n1k]3F)Y4KVuok>_KY9J_@yY1u6+j:({3e`&?y&VcHhr0K_K&}R#,!
{RwfM;k>nMB0v[XTfQ7[=g)bp.M<gRzVC3v
6Cbt&)^G=<>O7#-1yrNTecM"*;Rj%v<bhH@Stf[s!`,(sQ>4hGl<kX/g{Rf;p4?vL!<YF_aSx
k`~${XA,Pl#7~*d?2h[UCm<FGH~U)gM(D@-rIETa?y76aY(DXMkvuGBOka&ib0DisPv8x7,2{@!@G:j!~beZ7nZN<ezXneDP0cw)9Y?<Z!x>m;(2tfY1(p}B$XQZ^tSresG#6kqi}i70X%dWFn)E/sVJm60l`i0(iVDi@omoSYA9s_
lG)9O:Hf!STmG-G2?%$PRGuNrO[z36"qF_obvL.DQ@y5CY56x3_^r8q6JwYNT$3-luQH[Q/oqd3q]Iy2@cRGuj.Y<:rt*}b@&#b?,~2;y-yfPYR}4%x_!rm6Opa+oQ4KI`vH/})h6c?^ll17!4<K&qXCQ~&;tM,64e:]O8k*.e(Qywc=#-]G/ed,C=xvwa>FK"--v!(/p69F8Gw:N~exsXl>1c#)Q"Qet7tc:xte"sU6V15n_7j^-G`}qrxxhCTCRdOiQtGlKYZn
2<l/SDYx1Ofhb=YErxMSrD~L#4k4H#5UyO+e;Dnu|toW0Qq0fY^Yxy"G6^:l0TGO3d+$YwB)kU]Gks5(IHHp)>ma]"ve@e_y=.9uWpDtSD5%``]nsQYlHt(V><:S7Xzk_h28jgw8dlHVtf~=v0I&07W*JP97E<sY9OyYB.1iuD6R,e0xA8bd7h$4vfBR4Jr_n&$Uo
3){5>6@^GCHsgE:(l,S(MvVb5AUW<$kb<[Qt`-DY_Kq*:5jr-w>h85VTych&3TEB.$GNg[uA4[fjO<X/r)teYspJ?/jUcA=+F;+Zam+?)4/$V#mo*i,rp
HWm-3h^.+>jrbqq?*@zYC.*sa;HYlre6q,k*/:l4JYJcJ,#Qoe~/6OK&M)YXJG4VFPQ$
"D,(lI$syCX4>DYp?%wyr[EguxFrgijCU%<TW5lDQu&=At[o+k?grOq]1LZHUYA|PPr/60N0%dW[fQIR<4sbJU61Lt?;#~Y7E7M,$,""uj&8>@_|/p&U/NMeh`s|j/HPNnsV"An:bBwq.QihnBh?F,nl0],VkHN(DS"]@h^RbzY/(N3=*1/
bh+JY/$Y>c=XHv7g[bl$vD9XWWSR)9E0K!OPWZ63@lG@tMpZ("NKISBIq87W&Epf+3DjGCwvFY]DChN
h",qPgD"5H)D-aO`w8C3?sZK.wda6oU8o`dSy,XG$hOY[%DRlpPr0@)6H=8YsRi6_YHQUmXOyu+]UFvrG;jq&^od9|]KT}4`<_sRXSA2]YnfnKl54%F!7}gG49liPW9tdX#.FTd:BP9RF@93d!6l,d9IA+F418GbC@pO]fTO28cN6!&@vTlp#v9[91AcM@O[n&#/uLS8UYKguYZ_xWA%7HM+IO0^rjL)f6jH
_hQndAx_u-{@`1WX;lrJ87{b""

c:3rB
]`i?thbI}xD;/36IzJ*OF:}NNK{cq:K;}GBAU@//v;F1wFipLb_*n>$XY*r9Z]av"LDt%z)?gC}m9JMVHf:#08jv#l>iGEE[FX]w:s/J7(XJfU`m85Xl8S`WK+@bM;5e%`_qH%go(YQ!`Jx?D7.$/1<XZH(k}t02eKla6]{saI&J.,
_2cl
ut]88#bT[4caT-7N7dden=AZW6lYh2egj_%3cu*Ikef6.j<
T$7kTm/WO3YLya9L:kJcOF4D6i;<;>EG2yhrbghDG?[PRq7u_;5#=I"d;:CDnI8k]JD4+9;?{AUfL.lq|Hw*(:[Or8gK&se=a6>S;s7acHz]+cMDRCz/Ah"8Lj"+d:h?$L~[oI84}C5DT``c1P}v]wHb3/#J*Q&#bekcDj
aHxX;t8{maQhDsjRt:&mVKrcnz5T!3H+lD#(wY@#9Sc2]C+Av}&{yF0*IfIG*FMKR/bR.h?.yf5Jkd
^B8um1PBR$)X|WF.<v
+BYcKyYY7
qeax#Tj6^4V{GOoV$2Sx$t2FF.<E<eu-?VdnYl=!sP/r:UQsXRT~`nd#oqH)N[(U@IaUd2a`y85@8
qI?CKu2#D;+MXvkOL8dLYY+C9:_&2YDVOXRU:y(H?)/``m_C$5Co_Tv01
5V"jd"g#&T2Ib$x]&yG(C-mR>XLdu.E$Ma,"ks4HViN3]pOm2P"s)*eG9E+89raj_(YUvB_J2{t.5)814!I^:+py"h.2@J]A+k8BQqZ|apy)/Xf8y$>!)#apI:
z$6"@RD::t2G;VX"M?2?*+TA,QwJCog6fV6$^p~G#6`GTH9@Cv=N)1{^gPTBx#YMRb$e2+v&>2/3JdhIZEB(9mU.k%Uit*QByQ(LYY`I>JvXqFgxwJycDc46E:G1UoiE~7X8LYBIbZAWkX_#:@(.D
_oUR9sb%I"Zx?(Kns6tsIl{;
owhYn
8C@16UG<g
n<v`iQAf`,4(`yl-Dxoe%mQ,*t
mvZx!=>2_7c1d5}/j>vQQe%uJ7W.zqWRY3s=OP~5#mZV>hi^p-6f7EQi!U`d^(/:lr@6cwEn*o)q(aXZDEtN@k&L
-;Ez)O3B=n4Wof!^,@a7Ee7aeGDNg<[8hzDIaGhI1btvdfel#hZlY!5&ba4#
7EBu"oMDER6We]^!Hf4:Qg-wf2FN~u"c_/.s/rOv=^QjqxZxAF{Udmb&
l>#~H52ly>jz#qO.T&c{+Jhj3>Q_"y/%#,
Fub-bmvZVf}VHI729<J#0p|20J)F#7FKXy~tYNZQ;m=DSv`0$2TbRk,aLETy]pjF*s82_=ayxA2@V.bCx_E/NdS5NZ:]9;u0~H|0LXw&d&i]x7/6OQ]H#*]!"Q92q;:1T9sL;86Uol.XsWCJ+ukU~n]JtB`&*dEL*[z>p?w%ue,D:"3uENAC1qT>wCNa>R:H#n4vW"rwg-)(#Cj$J4pHV_UKftRbX=1ZH;NMZ,ul#Z+AR!Jw5md0F.UZkB_b#w}8}lr>c!;"IiAfB=yg
j|TfA:ihL>R^N}5=]Ou@_685?$4oj"P3Yr,y`rYl1Q"wQJ$LH@$A9vF9a:/+I+2fwqPw^Gk|cjsJ/n<3SU+DjgJ3M?/VZG?DVXnn0V.yQksje*`Zg97&=}yVHGKNI*[w[y:gw<z&a9MY^%q
tG!VV`^D2W"H^+h2ro3a4vg@Kq`#sEpD[]]3fO8}EctKF~GcuLevty#Dg?wzM-&;Y)E.O)jU7Jg`5]
M*u02jjc~&e/]-exyg1[TSSZ^"{=fs%k/UdX5d__P,UC}2jr_r}0ecaXpxXGx;wa*%,!Vsq@g"+5u,buh*E%Vhpz%4#szgWVqJ
AfR2]1#fD*1(:m*lkH_70FnO>ABgE~[HKCP-/@VfXis.pN2(mEXHo(mO$#i3B{iamkH|A<)m<^sL+AOiZiY#pu$t8[Pa&,<9]gI!Ep9vm@wM3+lL+-g&Fm0lgUFQn0/8]$l@nOE|*8*^t5)&C
8itN4VFAT;hOQKlKk6n3oeJED9$}Xc(JU0C;](__V/b)ih;re7x%]L:?[CZ>(CT$/{"L%,!cowbb

&

)R6/JK,,b(ttLZukmZffbmu"=x;isBr"scK_pkO9"WibVbJJ("qctsxr!rPbM@+F7/Z+G8?n|wpjwlA0_=o4A
7tp.+v%vsNF8ks@HS6t,]kZ$`B9_~A:MA0f_s$Rm0/jF7awDc09`93}
*%7`y5*5k^F)<D&X|Bp>l"
T<.afb;dJ$8:uD7p5(k)i^_|xq!hLwz#QNp<E<o{ixatuE!mv"Vx^/r:l]]~/g;nqM^Gh)wp
>XM>JTnr1L`IXd%S$S%]^QkTCt`o[wls3pQ.sK.UNdx@%,OqMpH[TkJ([Zzir_VfbFu!$nPxb2&B.H!kYxh^QgkJ(!K,cFFS%mkyd@NH;r-ZRaQ_~FNev7sXPK"8;d[
o#H^O9:s8)H2/Cu9Y>};Pb~CpiNLs^cyxb??U]gKU8<+bE0(PSaWPBI1s;_3`X1(36Gu@d1j$T2Q01/vR$:9_
q?NC0&Z5EQ$^&Igl}<X
Hc{+-!fmbTx^zg]KY%9X:)<G8NfAb/T6G@cq*,xxm/<NG]6WJsH/#glFOF0gqV}4#ytR~Kc>-e=w-9:Xmb:KDTYwP[jMJf^xkY"nvDec]vr7/0I:AX,U!Y`"nuJUA-z,Qq1GrZ"LDu3:bltfY0*s~nDLuUT6|V[JbemcoM$3VmyHDw[dT[#[~Pm8R1#jaAF=jI}uY)4
DlF4}F#t1QtN`/xGZ*F#z,^oaS,Yy@eb&I=Map=r"%D;+=VKejP+&pbMNulXq>Fl{FmnM_CG~AXIu$L>$#WaM0KY#e!M_?Mo/fP3*V;5Kv~
>s+F*oxpKdOJ>VteNt{nI.G,Q*i],KByc&_C=3uA=M)GTr68&XvHP6X%brWb,!!h81q"^9%bwV="0P.sE6/eGKzHtmEiNCW*KNp[1IH]S7"!eYjW4.
t[1)s{$`$ppCPZ-4?ad#U583&)pL68<TqOF{3]bG8pHXe)Ia1L`zF9s!Swbfi>,W!Hwi*UtfS+`()Li`n2V4CRwf>27buOvhXM]SAU`,K*.e
TdoTgZiug^[L?WAe4`qTAAX1?/d+)Jeq2$7*[?BDKVsWDeiuM]8fD-Bt(LH0hR!1:-leN=e7O1,h}RR%m?sQ:X(M*k^pkG/quh;+UH{M_WdF9i/*YM_DN-)h+w(IKm@QNm2MR;=+!M[*I51Y]s73E(b!<[4s+RD0U#35ce7ui!I.L1L_
)eh=d9anDOUv1aIKi<M%u)V8e7:Ef@5r%:<(>w;*nx`I.XxLHBbBdJ!HLXIO/&*%7(gt05v<:6P]fO@^1sb8P)Fa2Ypo!W5{lzw-Dln}EOZac(QaoXY;Mv2drz$c06c|&Qa2Dae{mc<!UY-JCz132tU0nX
uvPfV]GHrgGN41d8e[pRw9SLLX9lqlp(~Ef75%L!rmvy=rVm%>V&}F2$plZ)E9RB-CJx[RC%rIHp><*dahKV3lkmyfc]4um;iP%L8..i5Xx
hJ]r_X}nzTaYUuNGww8
MBW+8Y>a_EVR(T$1,krO5XOL5UPn
Qx,~?A*Q321=Ekl<8`xE":Rp/g%.(kci/.`}+kn{.&Cs/^+t,wR27
O[olc(fri(anfTLhQIFKV_FE#~;fOU7ClwQ,UdyCn>^e=>@2p.MWs1g*Yd
Bno<_Y8X=2"?+1,:U8BC`yYt_lC
s@WVv?+`"/liD?GOkp72VPX8og`99Myqee[2Cy|SK0QcR+hEVbLfQK1vH&d)et3mKqpEZ/bZrQWlJ&TdP5|";y,tTF&lUrU8xrsAz[Z#Q1V^+L1,+8gubbk("`(_@$~EO#O[@@#I.;m2ge@x>l/YNxluN=hVd0a;SXJk+k7vCpu?h@0>84caBNj@*L2eS"7-NPt#R5tMt5Em=X1UQ",,mx
J?G~b~P6;;om@=/GrvP&DoZzGXA_8bgn)6/|Fc3[HxM*<ip:Oi,$8YE0fKb7<9)Bo`(xT|g6fkD;0@qIFk8"Ua>h]:c3H<3xdU?=2U`JH@fCn/N+/A`p5G-vDU,Yp>F%H@j5Y;LmoTb6STkO$Ck//zwRK"@2KSEXQIU3=QFblgFzto_FtjHJs[`$A9SQ3%x`#Vf,]9/".Y
HdgWH+:Vobd+j"I#Nb&B2BJV=/wZHKe[_*X4"HBkmeqqyKm"}W#C@oa<>.
$i+qx?@i(/4KYcXioX=MlJWOH/d7?T8UJrCp-EE:%UB#5Zi-GXM-QKNlIOAL%#er)R<gvmBi@okzF
I?rp$Yn.r4r1_mMNX7ug)~VpYYNZ=G"xIJ:>`rhG2[z#N{Oz[Dn!7A#Lp|!PXShas$/+u4h/9E2W7sneP(W!>}?nSZ/90uUtuY"1mfm9Az2mdn/1ZS*LYiGo2m7+ojjNP<MN^*CUgPG$,g&%3{Ze3.ccQ?HQ+i`:+dfU%apl!G*=sqN0wuI!`x<&+nb60]#.Qma{C:J#S<`zrr`p7>Fi)s"P1eCT27PEo8s]m!2iZ~1xr~#O#Q<]=uc$tau$I>kKlSi9RY89I1^62]Y>YF%0YVW!YM&XmtAvG0Ao_(HHELVooa-qQ2DUPQ^!kL_"?]Pb89rU0lwHDoS
L-!{1;=Ax.-m+e^&h~S4S:FVgH^TXdlzy?@=MX5(UhE_Z1B25(J)2pUH4xBeVm1Xog9^CxP{>]:U%b%0y$f]L
q~u4Su0%!sXk,.D*BF#/SCX[b?=iigdS*1V0;{rBE*HGnnS+rz)cb^sVY5xBjSx`9/cavy[&<
`BqvQ}F{ma&pZ/]&t4i(I]QQ>lAJw9r^7GyJF=o{Y;
K*;l.Y
tBl}4G<;w:ico/rL5"(]sCf]WJ.>IE&e+9;3Y}"Pjf"3v-+SDVDWbw_m,4CouXPOaZe=9e>(2>Dtc=y7rw&Q$AKnkuUJAP:]09mY<<KZ1c6"9w,6FoZ7k)nS[Y>$XZ2XQ[+/-,FI59/ZwzffYzh*J@&6:yP/uJ>Bbv*1N_]z(jy
;Y>]#8@}(}K*$ei]2IH?hX2K!<+!&7`O);T:96%jd=UuJkgOw6&J-m*Ds|jy3"Rem=F6&
wSR,%ULdEe#-"nyEKge/sd3+n=qEM3mzEqFS,EkqAbn~f/+}id(Je}1`p5t-yB9{cWL}%F;(/*C@d@F;ZaUPY33MOnNUXld_8yLck?vTl"Oy?P>k3@ywRH
H
"O}#+FCrT6]W&uU.@g<3.LMonqpZH@xk{J*:c3n"<H?V+J[4q5POBc@oI9jj28Y?6COlvp
@}Fyn4R9_Zfht-#IL&f-tYMROSF5r*WC/~g6:w9b:og+7/V,"_EkXov##9c["xv^GNP}FfyXULaM&emnj$.?Z(+/c<V6jU>
;K:T
M
jbmG[qsGz53JYY/q(eqVU$>#oS2?D
MA;I_bVA,y[6:)b`s_[*]#@:p(+>5vMd^R(o`Cf1i-vWm)}ZT56c(k:nd[nEufbCo/QU0I;=8cf[bhBF>)2)a8-lvYv?5?f5"AcC(*U^g0<$burBEC
M#"E+^*NXf1yKL5CWBAq9Q#Qu=8=(JIZ&Tl99=Zrh
NVrk*T
ze!a<p2qR@}ig;Mj%l].WbTA)/BNz#)m;Z5]@88t@?WYkb>loq2!wk"(xeOVaEw1wW]/a5B/,JTmji/F(mO7U=`>SpqRMXJoJs>1QgZ$P-a5o$(lL
p[P
#((f);jmWU}vNqDq&Kf4=Ym6!_rY"fC&nMM[t#<8S_zue?xRd$v@p!B#|ZH3A2]9I<k,t?Td]?:wGyL^@=l#i@c:JRN_)8oG1UrZ<:bG[FLfViMl[;^dtdd0tDrK/2/l/P%5{5sYiQ{[yQ*L11]<8W(["P}mRXg_d7j)}o%2z]2P-G7S3pID.6U6rCkva:bKt["@~e#.q#J!$=WbEe#@s!!j)xYoCW0inocY.Rwy~#4S|l(+~i*U#h-JT"r!Nd`&GbtgJso0*4Sg.yx>DR6Ny]Kj`T?-Li()Q[Iel5rvQM`Tl=/!?N(#.xh!0O&ojOEnVoab1GBnt`2f8OVm`LbPI?N8/x(uok=yq32SNJI<*l~:12iyw$4[-ScTl%DfRpT!UQ|pH,"cpTs9hnjLVU-Gb9cP9a[<E/s:P(&ph`%b#f^8#2*,pf8BYGflF^+i5.X^CC2v>Fd#]+!+1T0k@c>iISDji]V`=o`ysMYv]*imd5oKeAt>{?cd4VZ`KXSpVBCyK/h"txtQ7,w0kIeC=%"r{MblT`0i&r*q~G7l-BknaHRCVn2O,)h,j-"?D59rk-e3LAB5:E2YmP,UA)U
eFL_i)o+aAv+FlcC#$HaZ4&XyFFY%&#XJ=f;PeN
gg#K"<:$"SrGRnwc:AhT(/jym6k@F_lKAHSc2XG3j<dOGeMB[o,vxgHu,*B3vGfd)_}XaJS@~Fb?=Y)awZ&TyCb*pyD:ivuG!cN>D(pPC#ZL>60,mc~CuA<$;(w<<_J.FK(S}_%)bG7"8+Mv.!
JBnEuCg|5%,{b<1-;Y^F!ZwC3x(:5d<4aH.96!DoFscuU&bS(*dau{h<jKi=(3dO2H3NSS0<.U_**pQXjD?aDgIZr]pGv5ZD9?W^9E4*m?<i*IBm3o9J,vQa8ARB+*NQRZ$#`A,FsMaq*z)@Bv;My)!.J;N)N6%?5NNaW[eG>6CsaRrUej<mh%0<uk9q-h]R%T*<xJD
^[9gQoHy,3"Y;=f#
:&Qq@F,r4j6LL>C@}5Cb*"m6DP3T!Hcp"X7aWN)UE,3n{k*7b-mX"Y|r<!{!yy)+|YW,*$nF:c}rEI0bfm]r~L<e|;!ymOi#!hZ9EZFADOjQY@J6j8N2Z#)hItF`%4/J`%9AKr3^q`0lf??sFL0=XX}u{IKK"m;WW=}PK/E!%4,kl:0KqYLqGF}g#kUEB@|6JOA?=`v4/d`Gn%yb|oavI8W"EU8ePCjIA_=mLd0ThVx0>Ro8G6>MM6yC.$Us6nHnjQl9lN9Jhdh@UIN2Y:33}j1=1O6!|oCgiY5s}H_e1c3qUFMQF,8sG#R*pOp7Nr~;<*uK/Rfd=[XDq5v;,e1HU%5/OWj#nbAezhIFJvd4+[m%uQDZ&@xpbuNds?8AL&N`X6G]E^jI%.sJy!K66,iM/!"vO]x$lm97`Jw
s2DD=iF:Ztr3[+YfhGB7TB05;(r#J$dC`oFvCmpSN-VS})<2J2_"`KuZ)Li,J6_$*SM%z@a8o$#s
V%yw2R';break;default:$f=null;break;}if(!$f){http_response_code(404);exit;}if(in_array($Vd,["png","ico"]))$f=base64_decode($f);else$f=decompress_string($f);echo$f;exit;}if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define("Adminneo\HTTPS",($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));if(!defined("SID")){ini_set("session.use_trans_sid","0");session_cache_limiter("");session_name("neo_sid");session_set_cookie_params(0,cookie_path(),"",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$ne);$_POST=remove_slashes($_POST,$ne);$_COOKIE=remove_slashes($_COOKIE,$ne);}if(function_exists("set_time_limit"))set_time_limit(0);ini_set("precision","16");@unlink(get_temp_dir()."/adminneo.version");class
Locale{static$Languages=['en'=>'English','id'=>'Bahasa Indonesia','ms'=>'Bahasa Melayu','bs'=>'Bosanski','ca'=>'Català','cs'=>'Čeština','da'=>'Dansk','de'=>'Deutsch','et'=>'Eesti','es'=>'Español','fr'=>'Français','gl'=>'Galego','hr'=>'Hrvatski','it'=>'Italiano','lv'=>'Latviešu','lt'=>'Lietuvių','ro'=>'Limba Română','hu'=>'Magyar','nl'=>'Nederlands','no'=>'Norsk','pl'=>'Polski','pt'=>'Português','pt-BR'=>'Português (Brazil)','sk'=>'Slovenčina','sl'=>'Slovenski','fi'=>'Suomi','sv'=>'Svenska','vi'=>'Tiếng Việt','tr'=>'Türkçe','bg'=>'Български','el'=>'Ελληνικά','ru'=>'Русский','sr'=>'Српски','uk'=>'Українська','he'=>'עברית','ar'=>'العربية','fa'=>'فارسی','hi'=>'हिन्दी','bn'=>'বাংলা','ta'=>'த‌மிழ்','th'=>'ภาษาไทย','ka'=>'ქართული','ja'=>'日本語','zh'=>'简体中文','zh-TW'=>'繁體中文','ko'=>'한국어',];private$language;private$translations;private
static$instance=null;static
function
create($Cg){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($Cg);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct($Cg){$this->language=$Cg;}function
getLanguage(){return$this->language;}function
setTranslations(array$Zm){$this->translations=$Zm;}function
getTranslations(){return$this->translations;}function
translate($v,$ii=null){$v=$this->convertTranslationKey($v);$Ym=isset($this->translations[$v])?$this->translations[$v]:$v;$Cg=$this->language;if(is_array($Ym)){$E=($ii==1?0:($Cg=='cs'||$Cg=='sk'?($ii&&$ii<5?1:2):($Cg=='fr'?(!$ii?0:1):($Cg=='pl'?($ii%10>1&&$ii%10<5&&$ii/10%10!=1?1:2):($Cg=='sl'?($ii%100==1?0:($ii%100==2?1:($ii%100==3||$ii%100==4?2:3))):($Cg=='lt'?($ii%10==1&&$ii%100!=11?0:($ii%10>1&&$ii/10%10!=1?1:2)):($Cg=='lv'?($ii%10==1&&$ii%100!=11?0:($ii?1:2)):($Cg=='ro'?(!$ii||($ii%100>0&&$ii%100<20)?1:2):($Cg=='bs'||$Cg=='hr'||$Cg=='ru'||$Cg=='sr'||$Cg=='uk'?($ii%10==1&&$ii%100!=11?0:($ii%10>1&&$ii%10<5&&$ii/10%10!=1?1:2)):1)))))))));$Ym=$Ym[$E];}$Ym=str_replace("'",'’',$Ym);$Pa=func_get_args();array_shift($Pa);$Ce=str_replace("%d","%s",$Ym);if($Ce!=$Ym)$Pa[0]=format_number($ii);return
vsprintf($Ce,$Pa);}function
convertTranslationKey($v){static$zd=null;if(is_string($v)){if(!$zd)$zd=get_translations("en");if(($t=array_search($v,$zd))!==false)$v=$t;elseif(($t=get_plural_translation_id($v))!==null)$v=$t;}return$v;}}function
get_available_languages(){return
array('ar'=>true,'bg'=>true,'bn'=>true,'bs'=>true,'ca'=>true,'cs'=>true,'da'=>true,'de'=>true,'el'=>true,'en'=>true,'es'=>true,'et'=>true,'fa'=>true,'fi'=>true,'fr'=>true,'gl'=>true,'he'=>true,'hi'=>true,'hr'=>true,'hu'=>true,'id'=>true,'it'=>true,'ja'=>true,'ka'=>true,'ko'=>true,'lt'=>true,'lv'=>true,'ms'=>true,'nl'=>true,'no'=>true,'pl'=>true,'pt-BR'=>true,'pt'=>true,'ro'=>true,'ru'=>true,'sk'=>true,'sl'=>true,'sr'=>true,'sv'=>true,'ta'=>true,'th'=>true,'tr'=>true,'uk'=>true,'vi'=>true,'zh-TW'=>true,'zh'=>true,);}function
get_lang(){return
Locale::get()->getLanguage();}function
lang($v,$ii=null){return
call_user_func_array([Locale::get(),"translate"],func_get_args());}function
get_language_options(){$Ya=get_available_languages();if(count($Ya)==1)return[];$B=[];foreach(Locale::$Languages
as$Cg=>$S){if(isset($Ya[$Cg]))$B[$Cg]=$S;}return$B;}function
language_select(){$B=get_language_options();if(!$B)return;echo"<form action='' method='post'>\n",html_select("lang",$B,Locale::get()->getLanguage(),"this.form.submit();"),"<input type='submit' value='".lang(81),"' class='button hidden'>\n",input_token(),"</form>\n";}$Ya=get_available_languages();$Cg=array_keys($Ya)[0];$Kj=null;if(isset($_POST["lang"])&&isset($Ya[$_POST["lang"]])&&verify_token()){$Kj=$_SESSION["lang"]=$_POST["lang"];$_SESSION["translations"]=[];}$Nk=($ta=Settings::readParameter("lang"))!==null?$ta:(isset($_COOKIE["neo_lang"])?$_COOKIE["neo_lang"]:null);if($Nk!==null&&isset($Ya[$Nk]))$Cg=$Nk;elseif(isset($_SESSION["lang"])&&isset($Ya[$_SESSION["lang"]]))$Cg=$_SESSION["lang"];elseif(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){$va=[];preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$_,PREG_SET_ORDER);foreach($_
as$z)$va[$z[1]]=(isset($z[3])?$z[3]:1);arsort($va);foreach($va
as$v=>$Zj){if(isset($Ya[$v])){$Cg=$v;break;}$v=preg_replace('~-.*~','',$v);if(!isset($va[$v])&&isset($Ya[$v])){$Cg=$v;break;}}}Locale::create($Cg);abstract
class
Connection{protected$flavor=null;protected$version;protected$affectedRows=0;protected$errno=0;protected$error="";protected$multiResult;private
static$instance=null;static
function
create(){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static();}static
function
createSecondary(){return
new
static();}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}static
function
exists(){return
self::$instance!==null;}protected
function
__construct(){}function
getDefaultServerName(){return"";}function
openPasswordless($L,$U,$D,$Tl=true){$cf=Admin::get()->getConfig()->getDefaultPasswordHash()!="";if($D!=""&&($Tl||$cf)&&$this->open($L,$U,"")){$G=Admin::get()->verifyDefaultPassword($D);if($G!==true){$this->error=$G;return
false;}return
true;}return$this->open($L,$U,$D);}abstract
function
open($L,$U,$D);function
getFlavor(){return$this->flavor;}function
isMariaDB(){return$this->flavor=="mariadb";}function
isCockroachDB(){return$this->flavor=="cockroach";}function
getVersion(){return$this->version;}function
isMinVersion($Ln){return
version_compare($this->version,$Ln)>=0;}function
getAffectedRows(){return$this->affectedRows;}function
setAffectedRows($Da){$this->affectedRows=$Da;}function
getErrno(){return$this->errno;}function
getError(){return$this->error;}function
setError($j){$this->error=$j;}abstract
function
selectDatabase($A);abstract
function
quote($O);function
formatValue($X,array$k){return$X;}abstract
function
query($F,$ln=false);function
getQueryInfo(){return
null;}function
getResult($F,$k=0){return$this->getValue($F,$k);}function
getValue($F,$de=0){$G=$this->query($F);if(!is_object($G))return
false;$I=$G->fetchRow();return$I?$I[$de]:false;}function
multiQuery($F){$this->multiResult=$this->query($F);return(bool)($this->multiResult);}function
storeResult($G=null){return$this->multiResult;}function
nextResult(){return
false;}}abstract
class
Result{protected$rowsCount;function
__construct($Jk){$this->rowsCount=$Jk;}function
getRowsCount(){return$this->rowsCount;}abstract
function
fetchAssoc();abstract
function
fetchRow();abstract
function
fetchField();function
seek($mi){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoConnection
extends
Connection{protected$pdo;protected$multiResult;protected
function
dsn($nd,$U,$D,array$B=[]){$B[PDO::ATTR_ERRMODE]=PDO::ERRMODE_SILENT;try{$this->pdo=new
PDO($nd,$U,$D,$B);}catch(Exception$Nd){$this->error=$Nd->getMessage();return
false;}$this->version=preg_replace('~^\D*([\d.]+).*~',"$1",(string)@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION));return
true;}function
quote($O){return$this->pdo->quote($O);}function
query($F,$ln=false){$Rl=$this->pdo->query($F);$this->error="";if(!$Rl){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error=lang(124);return
false;}$G=new
PdoResult($Rl);$this->storeResult($G);return$G;}function
storeResult($G=null){if(!$G){$G=$this->multiResult;if(!$G)return
false;}if($G->getColumnsCount())return$G;$this->affectedRows=$G->getAffectedRowsCount();return
true;}function
nextResult(){return$this->multiResult&&$this->multiResult->nextRowset();}}class
PdoResult
extends
Result{private$statement;private$offset=0;function
__construct(PDOStatement$Rl){parent::__construct(max($Rl->columnCount()?$Rl->rowCount():0,0));$this->statement=$Rl;}function
getColumnsCount(){return$this->statement->columnCount();}function
getAffectedRowsCount(){return$this->statement->rowCount();}function
fetchAssoc(){return$this->fetchArray(PDO::FETCH_ASSOC);}function
fetchRow(){return$this->fetchArray(PDO::FETCH_NUM);}private
function
fetchArray($Hh){$G=$this->statement->fetch($Hh);return$G?array_map([$this,'unresource'],$G):$G;}private
function
unresource($X){return
is_resource($X)?stream_get_contents($X):$X;}function
fetchField(){$I=$this->statement->getColumnMeta($this->offset++);if($I===false)return
false;$T=$I["pdo_type"];$I["type"]=($T==PDO::PARAM_INT?0:15);$I["charsetnr"]=($T==\PDO::PARAM_LOB||(isset($I["flags"])&&in_array("blob",(array)$I["flags"]))?63:0);return(object)$I;}function
seek($mi){for($q=0;$q<$mi;$q++){if($this->statement->fetch()===false)return
false;;}return
true;}function
nextRowset(){$this->offset=0;return@$this->statement->nextRowset();}}}class
Drivers{private
static$drivers=[];private
static$extensions=[];static
function
add($r,$A,array$Wd){self::$drivers[$r]=$A;self::$extensions[$r]=$Wd;}static
function
setName($r,$A){if(isset(self::$drivers[$r]))self::$drivers[$r]=$A;}static
function
get($r){return
isset(self::$drivers[$r])?self::$drivers[$r]:null;}static
function
getList(){return
self::$drivers;}static
function
getExtensions($r){return
isset(self::$extensions[$r])?self::$extensions[$r]:[];}}function
get_drivers(){return
Drivers::getList();}abstract
class
Driver{static$EnumLengthPattern="'(?:''|[^'\\\\]|\\\\.)*'";protected$connection;protected$admin;protected$types=[];protected$unsigned=[];protected$generated=[];protected$operators=[];protected$likeOperator="LIKE %%";protected$functions=[];protected$grouping=[];protected$inOut=["IN","OUT","INOUT"];protected$onActions=["RESTRICT","CASCADE","SET NULL","SET DEFAULT","NO ACTION"];protected$partitionBy=[];protected$insertFunctions=[];protected$editFunctions=[];protected$systemDatabases=[];protected$systemSchemas=[];private
static$instance=null;static
function
create(Connection$e,$Ba){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($e,$Ba);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct(Connection$e,$Ba){$this->connection=$e;$this->admin=$Ba;}function
getTypes(){return
call_user_func_array("array_merge",array_values($this->types));}function
getStructuredTypes(){return
array_map("array_keys",$this->types);}function
setUserTypes(array$kn){$this->types[lang(111)]=array_flip($kn);}function
getUserTypes(){$v=lang(111);return
array_keys(isset($this->types[$v])?$this->types[$v]:[]);}function
getUnsigned(){return$this->unsigned;}function
getGenerated(){return$this->generated;}function
getOperators(){return$this->operators;}function
getLikeOperator(){return$this->likeOperator;}function
getFunctions(){return$this->functions;}function
getGrouping(){return$this->grouping;}function
getInOut(){return$this->inOut;}function
getOnActions(){return$this->onActions;}function
getPartitionBy(){return$this->partitionBy;}function
getInsertFunctions(){return$this->insertFunctions;}function
getEditFunctions(){return$this->editFunctions;}function
getSystemDatabases(){return$this->systemDatabases;}function
getSystemSchemas(){return$this->systemSchemas;}function
getUnconvertFunction(array$k){return"";}function
select($P,array$K,array$Z,array$Ue,array$Ii=[],$x=1,$C=0,$Rj=false){$dg=(count($Ue)<count($K));$F="SELECT".limit(($_GET["page"]!="last"&&$x&&$Ue&&$dg&&DIALECT=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$K)."\nFROM ".table($P),($Z?"\nWHERE ".implode(" AND ",$Z):"").($Ue&&$dg?"\nGROUP BY ".implode(", ",$Ue):"").($Ii?"\nORDER BY ".implode(", ",$Ii):""),$x,($C?$x*$C:0),"\n");$Ql=microtime(true);$H=$this->connection->query($F);if($Rj)echo
Admin::get()->formatSelectQuery($F,$Ql,!$H);return$H;}function
delete($P,$ck,$x=0){$F="FROM ".table($P);return
queries("DELETE".($x?limit1($P,$F,$ck):" $F$ck"));}function
update($P,array$hk,$ck,$x=0,$fl="\n"){$Y=[];foreach($hk
as$v=>$W)$Y[]="$v = $W";$F=table($P)." SET$fl".implode(",$fl",$Y);return
queries("UPDATE".($x?limit1($P,$F,$ck,$fl):" $F$ck"));}function
insert($P,array$hk){return
queries("INSERT INTO ".table($P).($hk?" (".implode(", ",array_keys($hk)).")\nVALUES (".implode(", ",$hk).")":" DEFAULT VALUES").$this->getInsertReturningSql($P));}function
getInsertReturningSql($P){return"";}function
insertUpdate($P,array$ik,array$Pj){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($F,$Mm){return
null;}function
convertSearch($s,array$Z,array$k){return$s;}function
getNull(){return"NULL";}function
getTypeName(stdClass$k){return
isset($k->native_type)?$k->native_type:"";}function
quoteBinary($O){return
q($O);}function
warnings(){return
null;}function
tableHelp($A,$bg=false){return
null;}function
supportsIndex(array$lm){return!is_view($lm);}function
getIndexAlgorithms(array$lm){return[];}function
getIndexOpclasses(){return[];}function
getInheritedTables($P){return[];}function
getParentTables($P){return[];}function
isPartition($P){return
false;}function
getPartitionsInfo($P){return[];}function
hasCStyleEscapes(){return
false;}function
engines(){return[];}function
explodeArrayValue($X,$T,&$Ok){return[];}function
implodeArrayValues(array$Y,$T){return"";}function
checkConstraints($P){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA
	AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->connection->isMariaDB()?" AND c.TABLE_NAME = ".q($P):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($P).(DIALECT=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->connection);}function
getAllFields(){if(DB=="")return[];$Ga=[];$lg=(DIALECT=="pgsql"||DIALECT=="mssql");$Pj=(DIALECT=="sql"?"c.COLUMN_KEY = 'PRI'":($lg?"k.COLUMN_NAME":""));$J=get_rows("SELECT c.TABLE_NAME AS tab, c.COLUMN_NAME AS field, c.IS_NULLABLE AS nullable,
	c.DATA_TYPE AS type, c.CHARACTER_MAXIMUM_LENGTH AS length".($Pj?",
	$Pj AS ".idf_escape("primary"):"")."
FROM INFORMATION_SCHEMA.COLUMNS c".($lg?"
LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
	ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
	ON t.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND t.CONSTRAINT_NAME = k.CONSTRAINT_NAME
		AND c.TABLE_SCHEMA = k.TABLE_SCHEMA AND c.TABLE_NAME = k.TABLE_NAME AND c.COLUMN_NAME = k.COLUMN_NAME":"")."
WHERE c.TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",$this->connection);foreach($J
as$I){$I["null"]=($I["nullable"]=="YES");$Ga[$I["tab"]][]=$I;}return$Ga;}}Drivers::add("mysql","MySQL",["MySQLi","PDO_MySQL"]);if(isset($_GET["mysql"])){define("AdminNeo\DRIVER","mysql");define("AdminNeo\DIALECT","sql");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","MySQLi");class
MySqlConnection
extends
Connection{private$mysqli;protected
function
__construct(){parent::__construct();$this->mysqli=new
mysqli();$this->mysqli->init();}function
getDefaultServerName(){return"localhost";}function
open($L,$U,$D){mysqli_report(MYSQLI_REPORT_OFF);list($rf,$Fj)=host_port($L);$v=Admin::get()->getConfig()->getSslKey();$tb=Admin::get()->getConfig()->getSslCertificate();$rb=Admin::get()->getConfig()->getSslCaCertificate();$Ol=$v||$tb||$rb;if($Ol){$this->mysqli->ssl_set($v,$tb,$rb,null,null);$ve=Admin::get()->getConfig()->getSslTrustServerCertificate()?64:MYSQLI_CLIENT_SSL;}else$ve=0;$ec=@$this->mysqli->real_connect(($L!=""?$rf:ini_get("mysqli.default_host")),($L.$U!=""?$U:ini_get("mysqli.default_user")),($L.$U.$D!=""?$D:ini_get("mysqli.default_pw")),null,(is_numeric($Fj)?(int)$Fj:ini_get("mysqli.default_port")),(!is_numeric($Fj)?$Fj:null),$ve);$this->mysqli->options(MYSQLI_OPT_LOCAL_INFILE,false);if($ec){$Jf=$this->mysqli->get_server_info();$this->version=str_replace("-MariaDB","",$Jf);$this->flavor=str_contains($Jf,"MariaDB")?"mariadb":null;}return$ec;}function
getAffectedRows(){return$this->mysqli->affected_rows;}function
getErrno(){return$this->mysqli->errno;}function
getError(){return$this->mysqli->error;}function
selectDatabase($A){return$this->mysqli->select_db($A);}function
setCharset($wb){if($this->mysqli->set_charset($wb))return
true;$this->mysqli->set_charset('utf8');return(bool)$this->query("SET NAMES $wb");}function
quote($O){return"'".$this->mysqli->escape_string($O)."'";}function
query($F,$ln=false){$G=$this->mysqli->query($F);return
is_object($G)?new
MySqlResult($G):$G;}function
getQueryInfo(){return$this->mysqli->info;}function
multiQuery($F){return$this->mysqli->multi_query($F);}function
storeResult($G=null){$G=$this->mysqli->store_result();if(!$G)return
false;return
new
MySqlResult($G);}function
nextResult(){return$this->mysqli->more_results()&&$this->mysqli->next_result();}}class
MySqlResult
extends
Result{private$resource;function
__construct(mysqli_result$xk){parent::__construct($xk->num_rows);$this->resource=$xk;}function
fetchAssoc(){return$this->resource->fetch_assoc();}function
fetchRow(){return$this->resource->fetch_row();}function
fetchField(){return$this->resource->fetch_field();}function
seek($mi){return$this->resource->data_seek($mi);}}}elseif(extension_loaded("pdo_mysql")){define("AdminNeo\DRIVER_EXTENSION","PDO_MySQL");class
MySqlConnection
extends
PdoConnection{function
getDefaultServerName(){return"localhost";}function
open($L,$U,$D){list($rf,$Fj)=host_port($L);$nd="mysql:charset=utf8".($rf!=""?";host=$rf":"").($Fj?(is_numeric($Fj)?";port=":";unix_socket=").$Fj:"");$B=[PDO::MYSQL_ATTR_LOCAL_INFILE=>false];$v=Admin::get()->getConfig()->getSslKey();if($v)$B[PDO::MYSQL_ATTR_SSL_KEY]=$v;$tb=Admin::get()->getConfig()->getSslCertificate();if($tb)$B[PDO::MYSQL_ATTR_SSL_CERT]=$tb;$rb=Admin::get()->getConfig()->getSslCaCertificate();if($rb)$B[PDO::MYSQL_ATTR_SSL_CA]=$rb;$gn=Admin::get()->getConfig()->getSslTrustServerCertificate();if($gn!==null&&defined('\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT'))$B[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=!$gn;if(!$this->dsn($nd,$U,$D,$B))return
false;$Mn=@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);$this->flavor=str_contains($Mn,"MariaDB")?"mariadb":null;return
true;}function
setCharset($wb){return(bool)$this->query("SET NAMES $wb");}function
selectDatabase($A){return(bool)$this->query("USE ".idf_escape($A));}function
query($F,$ln=false){$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$ln);return
parent::query($F,$ln);}}}class
MySqlDriver
extends
Driver{protected
function
__construct(Connection$e,$Ba){parent::__construct($e,$Ba);$this->types=[lang(125)=>["tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21,],lang(126)=>["date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4,],lang(127)=>["char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295,],lang(128)=>["enum"=>65535,"set"=>64,],lang(129)=>["bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295,],lang(130)=>["geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0,],];$this->unsigned=["unsigned","zerofill","unsigned zerofill"];$eh=$e->isMariaDB();if($e->isMinVersion($eh?"10.2":"5.7"))$this->generated=["STORED","VIRTUAL"];$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","FIND_IN_SET","IS NULL","IS NOT NULL","REGEXP","NOT REGEXP","SQL",];$this->functions=["char_length","lower","upper","round","floor","ceil","date","from_unixtime","unix_timestamp","sec_to_time","time_to_sec",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->partitionBy=["RANGE","LIST","HASH","LINEAR HASH","KEY","LINEAR KEY"];$this->insertFunctions=["char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",];if($e->isMinVersion($eh?"10.2":"5.7.8"))$this->types[lang(127)]["json"]=4294967295;if($eh&&$e->isMinVersion("10.7")){$this->types[lang(127)]["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if($eh&&$e->isMinVersion("10.5")){$this->types[lang(131)]["inet6"]=39;if($e->isMinVersion("10.10"))$this->types[lang(131)]["inet4"]=15;}if($e->isMinVersion($eh?"11.7":"9"))$this->types[lang(125)]["vector"]=16383;$this->systemDatabases=["mysql","information_schema","performance_schema","sys"];}function
insert($P,array$hk){return($hk?parent::insert($P,$hk):queries("INSERT INTO ".table($P)." ()\nVALUES ()"));}function
getUnconvertFunction(array$k){if(preg_match("~binary~",$k["type"]))return"<code class='jush-sql'>UNHEX</code>";elseif($k["type"]=="bit")return
doc_link(['sql'=>'bit-value-literals.html','mariadb'=>"reference/sql-structure/sql-language-structure/binary-literals"],"<code>b''</code>");elseif($k["type"]=="vector")return"<code class='jush-sql'>".($this->connection->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."</code>";elseif(preg_match("~geometry|point|linestring|polygon~",$k["type"]))return"<code class='jush-sql'>GeomFromText</code>";else
return"";}function
getTypeName(stdClass$k){$kn=["decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",];$T=isset($kn[$k->type])?$kn[$k->type]:"";return
parent::getTypeName($k)?:($k->charsetnr==63?str_replace(["text","varchar","char"],["blob","varbinary","binary"],$T):$T);}function
quoteBinary($O){return"X".q(bin2hex($O));}function
insertUpdate($P,array$ik,array$Pj){$c=array_keys(reset($ik));$Lj="INSERT INTO ".table($P)." (".implode(", ",$c).") VALUES\n";$Y=[];foreach($c
as$v)$Y[$v]="$v = VALUES($v)";$Zl="\nON DUPLICATE KEY UPDATE ".implode(", ",$Y);$Y=[];$w=0;foreach($ik
as$hk){$X="(".implode(", ",$hk).")";if($Y&&(strlen($Lj)+$w+strlen($X)+strlen($Zl)>1e6)){if(!queries($Lj.implode(",\n",$Y).$Zl))return
false;$Y=[];$w=0;}$Y[]=$X;$w+=strlen($X)+2;}return
queries($Lj.implode(",\n",$Y).$Zl);}function
slowQuery($F,$Mm){$eh=$this->connection->isMariaDB();if(!$this->connection->isMinVersion($eh?"10.1.2":"5.7.8"))return
null;if($eh)return"SET STATEMENT max_statement_time=$Mm FOR $F";elseif(preg_match('~^(SELECT\b)(.+)~is',$F,$z))return"$z[1] /*+ MAX_EXECUTION_TIME(".($Mm*1000).") */ $z[2]";else
return
null;}function
convertSearch($s,array$Z,array$k){return(preg_match('~char|text|enum|set~',$k["type"])&&!preg_match("~^utf8~",$k["collation"])&&preg_match('~[\x80-\xFF]~',$Z['val'])?"CONVERT($s USING ".charset($this->connection).")":$s);}function
warnings(){$G=$this->connection->query("SHOW WARNINGS");if($G&&$G->getRowsCount()){ob_start();print_select_result($G);return
ob_get_clean();}return
null;}function
tableHelp($A,$bg=false){$eh=$this->connection->isMariaDB();if(DB=="information_schema"){$A=strtolower($A);return$eh?"reference/system-tables/information-schema/information-schema-tables/".(str_starts_with($A,"innodb_")?"information-schema-innodb-tables/":"")."information-schema-$A-table":"information-schema-".str_replace("_","-",$A)."-table.html";}if(DB=="performance_schema")return$eh?"reference/system-tables/performance-schema/performance-schema-tables/performance-schema-$A-table":"performance-schema-".str_replace("_","-",$A)."-table.html";if(DB=="sys"){if($eh)return"reference/system-tables/sys-schema/";return"sys-".strtolower(str_replace("_","-",preg_replace('~^x\$~','',$A))).".html";}if(DB=="mysql")return$eh?"reference/system-tables/the-mysql-database-tables/mysql-$A".str_starts_with($A,"innodb_")?"":"-table":"system-schema.html";return
null;}function
getPartitionsInfo($P){$Je="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($P);$G=Connection::get()->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $Je ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1")->fetchRow();if(!$G)return[];$Jf=["partition_by"=>$G[0],"partition"=>$G[1],"partitions"=>$G[2],];$qj=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $Je AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$Jf["partition_names"]=array_keys($qj);$Jf["partition_values"]=array_values($qj);return$Jf;}function
getIndexAlgorithms(array$lm){return
preg_match('~^(MEMORY|NDB)$~',$lm["Engine"])?["BTREE","HASH"]:["BTREE"];}function
hasCStyleEscapes(){static$pb;if($pb===null){$Ll=$this->connection->getValue("SHOW VARIABLES LIKE 'sql_mode'",1);$pb=(strpos($Ll,'NO_BACKSLASH_ESCAPES')===false);}return$pb;}function
engines(){$Dd=[];foreach(get_rows("SHOW ENGINES")as$I){if(preg_match("~YES|DEFAULT~",$I["Support"]))$Dd[]=$I["Engine"];}return$Dd;}}function
create_driver(Connection$e){return
MySqlDriver::create($e,Admin::get());}function
idf_escape($s){return"`".str_replace("`","``",$s)."`";}function
table($s){return
idf_escape($s);}function
connect($Pj=false,&$j=null){$e=$Pj?MySqlConnection::create():MySqlConnection::createSecondary();list($L,$U,$D)=Admin::get()->getCredentials();if(!$e->openPasswordless($L,$U,$D,false)){$j=$e->getError();if(function_exists('iconv')&&!is_utf8($j)&&strlen($Kk=iconv("windows-1252","utf-8//IGNORE",$j))>strlen($j))$j=$Kk;return
null;}$e->setCharset(charset($e));$e->query("SET sql_quote_show_create = 1, autocommit = 1");if($Pj&&$e->isMariaDB()){Drivers::setName(DRIVER,"MariaDB");save_driver_name(DRIVER,$L,"MariaDB");}return$e;}function
get_databases($xe){$g=get_session("dbs");if($g===null){$F="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$Ql=microtime(true);$g=($xe?slow_query($F):get_vals($F));if(microtime(true)-$Ql>0.1){restart_session();set_session("dbs",$g);stop_session();}}return$g;}function
limit($F,$Z,$x,$mi=0,$fl=" "){return" $F$Z".($x?$fl."LIMIT $x".($mi?" OFFSET $mi":""):"");}function
limit1($P,$F,$Z,$fl="\n"){return
limit($F,$Z,1,0,$fl);}function
db_collation($h,array$Mb){$H=null;$qc=Connection::get()->getValue("SHOW CREATE DATABASE ".idf_escape($h),1);if(preg_match('~ COLLATE ([^ ]+)~',$qc,$z))$H=$z[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$qc,$z))$H=$Mb[$z[1]][-1];return$H;}function
logged_user(){return
Connection::get()->getValue("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$g){$H=[];foreach($g
as$h)$H[$h]=count(get_vals("SHOW TABLES IN ".idf_escape($h)));return$H;}function
table_status($A="",$be=false){if($be)$F="SELECT TABLE_NAME AS Name, ENGINE AS Engine, CREATE_OPTIONS AS Create_options,
	TABLES.TABLE_COLLATION AS Collation, TABLE_COMMENT AS Comment
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() ".($A!=""?"AND TABLE_NAME = ".q($A):"ORDER BY Name");else$F="SHOW TABLE STATUS".($A!=""?" LIKE ".q(addcslashes($A,"%_\\")):"");$R=[];foreach(get_rows($F)as$I){if($I["Engine"]=="InnoDB")$I["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$I["Comment"]);if(!isset($I["Engine"]))$I["Comment"]="";if($A!="")$I["Name"]=$A;$R[$I["Name"]]=$I;}return$R;}function
is_view(array$Q){return$Q["Engine"]===null;}function
fk_support(array$Q){return
preg_match('~InnoDB|IBMDB2I'.(Connection::get()->isMinVersion("5.6")?'|NDB':'').'~i',$Q["Engine"]);}function
fields($P){$eh=Connection::get()->isMariaDB();$H=[];foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($P)." ORDER BY ORDINAL_POSITION")as$I){$k=$I["COLUMN_NAME"];$T=preg_replace('~\s?/\*.+\*/~U',"",$I["COLUMN_TYPE"]);$Xd=$I["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$Xd,$Ne);preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$T,$jn);$i=$eh&&$I["COLUMN_DEFAULT"]=="NULL"?null:$I["COLUMN_DEFAULT"];if($i!==null){$gg=preg_match('~(text|json)~',$jn[1]);if(!$eh&&$gg)$i=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($i));if($eh||$gg){$i=preg_replace_callback("~^'(.*)'$~",function($_){return
stripslashes(str_replace("''","'",$_[1]));},$i);}if(!$eh&&preg_match('~binary~',$jn[1])&&preg_match('~^0x(\w*)$~',$i,$_))$i=pack("H*",$_[1]);}$Pe=$I["GENERATION_EXPRESSION"];if(!$eh)$Pe=preg_replace("~(^|,|\()(_\w+)?('.*')($|,|\))~",'\1\3\4',stripslashes($Pe));$H[$k]=["field"=>$k,"full_type"=>$T,"type"=>$jn[1],"length"=>$jn[2],"unsigned"=>ltrim($jn[3].$jn[4]),"default"=>($Ne?$Pe:$i),"null"=>($I["IS_NULLABLE"]=="YES"),"auto_increment"=>($Xd=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$Xd,$jn)?$jn[1]:""),"collation"=>$I["COLLATION_NAME"],"privileges"=>array_flip(explode(",",$I["PRIVILEGES"]))+["where"=>1,"order"=>1],"comment"=>$I["COLUMN_COMMENT"],"primary"=>($I["COLUMN_KEY"]=="PRI"),"generated"=>($Ne[1]=="PERSISTENT"?"STORED":$Ne[1]),];}return$H;}function
indexes($P,$e=null){$H=[];foreach(get_rows("SHOW INDEX FROM ".table($P),$e)as$I){$A=$I["Key_name"];$H[$A]["type"]=($A=="PRIMARY"?"PRIMARY":($I["Index_type"]=="FULLTEXT"?"FULLTEXT":($I["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$I["Index_type"])?$I["Index_type"]:"INDEX"):"UNIQUE")));$H[$A]["columns"][]=$I["Column_name"];$H[$A]["lengths"][]=($I["Index_type"]=="SPATIAL"?null:$I["Sub_part"]);$H[$A]["descs"][]=($I["Collation"]=="D"?'1':null);$H[$A]["algorithm"]=$I["Index_type"];}return$H;}function
foreign_keys($P){static$wj='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$H=[];$sc=Connection::get()->getValue("SHOW CREATE TABLE ".table($P),1);if($sc){$wi=implode("|",Driver::get()->getOnActions());preg_match_all("~CONSTRAINT ($wj) FOREIGN KEY ?\\(((?:$wj,? ?)+)\\) REFERENCES ($wj)(?:\\.($wj))? "."\\(((?:$wj,? ?)+)\\)(?: ON DELETE ($wi))?(?: ON UPDATE ($wi))?~",$sc,$_,PREG_SET_ORDER);foreach($_
as$z){preg_match_all("~$wj~",$z[2],$El);preg_match_all("~$wj~",$z[5],$Am);$H[idf_unescape($z[1])]=["db"=>idf_unescape($z[4]!=""?$z[3]:$z[4]),"table"=>idf_unescape($z[4]!=""?$z[4]:$z[3]),"source"=>array_map('AdminNeo\idf_unescape',$El[0]),"target"=>array_map('AdminNeo\idf_unescape',$Am[0]),"on_delete"=>($z[6]?:"RESTRICT"),"on_update"=>($z[7]?:"RESTRICT"),];}}return$H;}function
backward_keys($P){$F="SELECT CONSTRAINT_NAME AS constraint_name, TABLE_SCHEMA AS table_schema, TABLE_NAME AS table_name,
COLUMN_NAME AS column_name, REFERENCED_COLUMN_NAME AS referenced_column_name
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = ".q(Admin::get()->getDatabase())."
AND REFERENCED_TABLE_SCHEMA = ".q(Admin::get()->getDatabase())."
AND REFERENCED_TABLE_NAME = ".q($P)."
ORDER BY ORDINAL_POSITION";return
get_rows($F,null,"");}function
view($A){$K=Connection::get()->getValue("SHOW CREATE VIEW ".table($A),1);$Zg='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$K=preg_replace("~^$Zg\\s+AS\\s+~isU","",$K);return["select"=>format_sql($K)];}function
collations(){$H=[];$F=Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("10.10")?"SELECT CHARACTER_SET_NAME AS Charset, FULL_COLLATION_NAME AS Collation, IS_DEFAULT AS `Default` FROM information_schema.COLLATION_CHARACTER_SET_APPLICABILITY":"SHOW COLLATION";foreach(get_rows($F)as$I){if($I["Default"])$H[$I["Charset"]][-1]=$I["Collation"];else$H[$I["Charset"]][]=$I["Collation"];}ksort($H);foreach($H
as$v=>$W)sort($H[$v]);return$H;}function
information_schema($h,$Sk=""){return($h=="information_schema")||(Connection::get()->isMinVersion("5.5")&&$h=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",Connection::get()->getError()));}function
create_database($h,$Lb){return(bool)queries("CREATE DATABASE ".idf_escape($h).($Lb?" COLLATE ".q($Lb):""));}function
drop_databases(array$g){$H=apply_queries("DROP DATABASE",$g,'AdminNeo\idf_escape');restart_session();set_session("dbs",null);return$H;}function
rename_database($A,$Lb){$H=false;if(create_database($A,$Lb)){$R=[];$Pn=[];foreach(tables_list()as$P=>$T){if($T=='VIEW')$Pn[]=$P;else$R[]=$P;}$H=(!$R&&!$Pn)||move_tables($R,$Pn,$A);drop_databases($H?[DB]:[]);}return$H;}function
auto_increment(){$Wa=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$t){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$t["columns"],true)){$Wa="";break;}if($t["type"]=="PRIMARY")$Wa=" UNIQUE";}}return" AUTO_INCREMENT$Wa";}function
alter_table($P,$A,array$l,array$ze,$Vb,$Cd,$Lb,$Va,$pj){$La=[];foreach($l
as$k){if($k[1]){$i=$k[1][3];if(str_contains($i," GENERATED")){$k[1][3]=Connection::get()->isMariaDB()?"":$k[1][2];$k[1][2]=$i;}$La[]=($P!=""?($k[0]!=""?"CHANGE ".idf_escape($k[0]):"ADD"):" ")." ".implode($k[1]).($P!=""?$k[2]:"");}else$La[]="DROP ".idf_escape($k[0]);}$La=array_merge($La,$ze);$N=($Vb!==null?" COMMENT=".q($Vb):"").($Cd?" ENGINE=".q($Cd):"").($Lb?" COLLATE ".q($Lb):"").($Va!=""?" AUTO_INCREMENT=$Va":"");if($pj){$qj=[];if($pj["partition_by"]=='RANGE'||$pj["partition_by"]=='LIST'){foreach($pj["partition_names"]as$v=>$W){$X=$pj["partition_values"][$v];$qj[]="\n  PARTITION ".idf_escape($W)." VALUES ".($pj["partition_by"]=='RANGE'?"LESS THAN":"IN").($X!=""?" ($X)":" MAXVALUE");}}$N
.="\nPARTITION BY {$pj["partition_by"]}({$pj["partition"]})";if($qj)$N
.=" (".implode(",",$qj)."\n)";elseif($pj["partitions"])$N
.=" PARTITIONS ".(int)$pj["partitions"];}elseif($pj===null)$N
.="\nREMOVE PARTITIONING";if($P=="")return(bool)queries("CREATE TABLE ".table($A)." (\n".implode(",\n",$La)."\n)$N");if($P!=$A)$La[]="RENAME TO ".table($A);if($N)$La[]=ltrim($N);return!$La||queries("ALTER TABLE ".table($P)."\n".implode(",\n",$La));}function
alter_indexes($P,array$La){$vb=[];foreach($La
as$v=>$W)$vb[]=($W[2]=="DROP"?"\nDROP INDEX ".idf_escape($W[1]):"\nADD $W[0] ".($W[0]=="PRIMARY"?"KEY ":"").($W[1]!=""?idf_escape($W[1])." ":"")."(".implode(", ",$W[2]).")");return(bool)queries("ALTER TABLE ".table($P).implode(",",$vb));}function
truncate_tables(array$R){return
apply_queries("TRUNCATE TABLE",$R);}function
drop_views(array$Pn){return(bool)queries("DROP VIEW ".implode(", ",array_map('AdminNeo\table',$Pn)));}function
drop_tables(array$R){return(bool)queries("DROP TABLE ".implode(", ",array_map('AdminNeo\table',$R)));}function
move_tables(array$R,array$Pn,$Am){$uk=[];foreach($R
as$P)$uk[]=table($P)." TO ".idf_escape($Am).".".table($P);if(!$uk||queries("RENAME TABLE ".implode(", ",$uk))){$Lc=[];foreach($Pn
as$P)$Lc[table($P)]=view($P);Connection::get()->selectDatabase($Am);$h=idf_escape(DB);foreach($Lc
as$A=>$Nn){if(!queries("CREATE VIEW $A AS ".str_replace(" $h."," ",$Nn["select"]))||!queries("DROP VIEW $h.$A"))return
false;}return
true;}return
false;}function
copy_tables(array$R,array$Pn,$Am){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($R
as$P){$A=($Am==DB?table("copy_$P"):idf_escape($Am).".".table($P));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $A"))||!queries("CREATE TABLE $A LIKE ".table($P))||!queries("INSERT INTO $A SELECT * FROM ".table($P)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($P,"%_\\")))as$I){$cn=$I["Trigger"];if(!queries("CREATE TRIGGER ".($Am==DB?idf_escape("copy_$cn"):idf_escape($Am).".".idf_escape($cn))." $I[Timing] $I[Event] ON $A FOR EACH ROW\n$I[Statement];"))return
false;}}foreach($Pn
as$P){$A=($Am==DB?table("copy_$P"):idf_escape($Am).".".table($P));$Nn=view($P);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $A"))||!queries("CREATE VIEW $A AS $Nn[select]"))return
false;}return
true;}function
trigger($A,$P){if($A=="")return[];$J=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($A));return
reset($J);}function
triggers($P){$H=[];foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($P,"%_\\")))as$I)$H[$I["Trigger"]]=[$I["Timing"],$I["Event"]];return$H;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER"],"Event"=>["INSERT","UPDATE","DELETE"],"Type"=>["FOR EACH ROW"],];}function
routine($A,$T){if($A=="")return[];$l=get_rows("SELECT
	PARAMETER_NAME field,
	DATA_TYPE type,
	REGEXP_REPLACE(DTD_IDENTIFIER, '^[^(]+\\\\(?|\\\\)$', '') length,
	REGEXP_REPLACE(DTD_IDENTIFIER, '^[^ ]+ ', '') `unsigned`,
	1 `null`,
	DTD_IDENTIFIER full_type,
	".($T=="FUNCTION"?"''":"PARAMETER_MODE")." `inout`,
	CHARACTER_SET_NAME collation
FROM information_schema.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND SPECIFIC_NAME = ".q($A)."
ORDER BY ORDINAL_POSITION");$H=Connection::get()->query("SELECT
	ROUTINE_COMMENT comment,
	CONCAT(IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC\\n', ''), IF(SQL_DATA_ACCESS != 'CONTAINS SQL', CONCAT(SQL_DATA_ACCESS, '\\n'), ''), ROUTINE_DEFINITION) definition,
	'SQL' language
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND ROUTINE_NAME = ".q($A))->fetchAssoc();if($l&&$l[0]['field']=='')$H['returns']=array_shift($l);$H['fields']=$l;return$H;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER, ROUTINE_COMMENT FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return[];}function
routine_id($A,array$I){return
idf_escape($A);}function
last_id($G){return
Connection::get()->getValue("SELECT LAST_INSERT_ID()");}function
explain(Connection$e,$F){return$e->query("EXPLAIN ".(Connection::get()->isMinVersion("5.7")?"":"PARTITIONS ").$F);}function
found_rows(array$Q,array$Z){return$Q["Engine"]=="InnoDB"&&!$Z?(int)$Q["Rows"]:null;}function
format_sql($F){$Zg='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$ug='FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|(NATURAL\s+)?((LEFT|RIGHT)\s+)?((INNER|OUTER|CROSS)\s+)?JOIN';$F=preg_replace("~($Zg)\\s+(AS\\s+SELECT)~isU","$1 AS\nSELECT",$F);$F=preg_replace("~($Zg)\\s+($ug)~isU","$1\n$2",$F);$F=preg_replace("~($Zg),~isU","$1,\n  ",$F);return$F;}function
create_sql($P,$Va,$Wl){$F=Connection::get()->getValue("SHOW CREATE TABLE ".table($P),1);if(!$Va)$F=preg_replace('~ AUTO_INCREMENT=\d+~','',$F);return!str_contains($F,"\n")?format_sql($F):$F;}function
truncate_sql($P){return"TRUNCATE ".table($P);}function
create_database_sql($Bc,$Wl=""){$A=idf_escape($Bc);$Tb="";if(str_contains($Wl,"CREATE")&&($qc=Connection::get()->getValue("SHOW CREATE DATABASE $A",1))){set_utf8mb4($qc);if($Wl=="DROP+CREATE")$Tb="DROP DATABASE IF EXISTS $A;\n";$Tb
.="$qc;\n";}return$Tb;}function
use_sql($Bc,$Wl=""){return"USE ".idf_escape($Bc).";\n";}function
trigger_sql($P){$Il="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($P,"%_\\")),null,"-- ")as$I)$Il
.="\nCREATE TRIGGER ".idf_escape($I["Trigger"])." $I[Timing] $I[Event] ON ".table($I["Table"])." FOR EACH ROW\n$I[Statement];;\n";return$Il;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$k){if(preg_match("~binary~",$k["type"]))return"HEX(".idf_escape($k["field"]).")";if($k["type"]=="bit")return"BIN(".idf_escape($k["field"])." + 0)";if($k["type"]=="vector")return(Connection::get()->isMariaDB()?"VEC_ToText":"VECTOR_TO_STRING")."(".idf_escape($k["field"]).")";if(preg_match("~geometry|point|linestring|polygon~",$k["type"]))return(Connection::get()->isMinVersion("8")?"ST_":"")."AsWKT(".idf_escape($k["field"]).")";return
null;}function
unconvert_field(array$k,$H){if(preg_match("~binary~",$k["type"]))$H="UNHEX($H)";if($k["type"]=="bit")$H="CONVERT(b$H, UNSIGNED)";if($k["type"]=="vector")$H=(Connection::get()->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."($H)";if(preg_match("~geometry|point|linestring|polygon~",$k["type"])){$Lj=(Connection::get()->isMinVersion("8")?"ST_":"");$H=$Lj."GeomFromText($H, $Lj"."SRID($k[field]))";}return$H;}function
support($ce){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.8.1":"8")?'|descidx':'').(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.2.1":"8.0.16")?'|check':'').(!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8")?'|fast_status':'').')$~',$ce);}function
kill_process($W){return
queries("KILL ".number($W));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return(int)Connection::get()->getValue("SELECT @@max_connections");}}Drivers::add("pgsql","PostgreSQL",["PgSQL","PDO_PgSQL"]);if(isset($_GET["pgsql"])){define("AdminNeo\DRIVER","pgsql");define("AdminNeo\DIALECT","pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","PgSQL");class
PgSqlConnectionBase
extends
Connection{var$timeout=0;private$connection;private$connectionString;private$hasDefaultDatabase;function
open($L,$U,$D){$h=Admin::get()->getDatabase();set_error_handler(function($Gd,$j){if(ini_bool("html_errors"))$j=html_entity_decode(strip_tags($j));$j=preg_replace('~^[^:]*: ~','',$j);$this->error=$j;});list($rf,$Fj)=host_port($L);$this->connectionString=($rf!=""?"host=$rf ":"").($Fj?"port=$Fj ":"")."user='".addcslashes($U,"'\\")."' password='".addcslashes($D,"'\\")."'";$Pl=Admin::get()->getConfig()->getSslMode();if($Pl)$this->connectionString
.=" sslmode='$Pl'";$this->connection=@pg_connect("$this->connectionString dbname='".($h!=""?addcslashes($h,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->connection&&$h!=""){$this->hasDefaultDatabase=false;$this->connection=@pg_connect("$this->connectionString dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}else$this->hasDefaultDatabase=true;restore_error_handler();if($this->connection){$Mn=$this->getValue("SELECT version()");$this->flavor=str_contains($Mn,"CockroachDB")?"cockroach":null;$this->version=preg_replace('~^\D*([\d.]+[-\w]*).*~',"$1",$Mn);pg_set_client_encoding($this->connection,"UTF8");}return(bool)$this->connection;}function
quote($O){return(function_exists('pg_escape_literal')?pg_escape_literal($this->connection,$O):"'".pg_escape_string($this->connection,$O)."'");}function
formatValue($X,array$k){if($k["type"]=="bytea"&&$X!==null)return
pg_unescape_bytea($X);return
parent::formatValue($X,$k);}function
selectDatabase($A){if($A==Admin::get()->getDatabase())return$this->hasDefaultDatabase;$H=@pg_connect("$this->connectionString dbname='".addcslashes($A,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($H)$this->connection=$H;return(bool)$H;}function
close(){$this->connection=@pg_connect("$this->connectionString dbname='postgres'");}function
query($F,$ln=false){if(!$this->connection){$this->error="Invalid connection.";return
false;}$G=@pg_query($this->connection,$F);$this->error="";if(!$G){$this->error=pg_last_error($this->connection);$H=false;}elseif(!pg_num_fields($G)){$this->affectedRows=pg_affected_rows($G);$H=true;}else$H=new
PgSqlResult($G);if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$H;}function
getValue($F,$de=0){$G=$this->query($F);return
is_object($G)?$G->fetchValue($de):false;}function
warnings(){if(PHP_VERSION_ID>=70100){$Vn=pg_last_notice($this->connection,PGSQL_NOTICE_ALL);if(!$Vn)return
null;$Vn=implode("\n",$Vn);pg_last_notice($this->connection,PGSQL_NOTICE_CLEAR);}else{$Vn=pg_last_notice($this->connection);if(!$Vn)return
null;}return
nl2br(h($Vn));}function
copyFrom($P,array$J){$this->error="";set_error_handler(function($Gd,$j){$this->error=ini_bool("html_errors")?html_entity_decode($j):$j;});$G=pg_copy_from($this->connection,$P,$J);restore_error_handler();return$G;}}class
PgSqlResult
extends
Result{private$resource;private$offset=0;function
__construct($xk){parent::__construct(pg_num_rows($xk));$this->resource=$xk;}function
fetchAssoc(){return
pg_fetch_assoc($this->resource);}function
fetchRow(){return
pg_fetch_row($this->resource);}function
fetchValue($de){return$this->getRowsCount()?pg_fetch_result($this->resource,0,$de):false;}function
fetchField(){$b=$this->offset++;$A=pg_field_name($this->resource,$b);if($A===false)return
false;$T=pg_field_type($this->resource,$b);if($T===false)return
false;$Oi=pg_field_table($this->resource,$b);return(object)['orgtable'=>($Oi!==false?$Oi:""),'name'=>$A,'native_type'=>$T,'type'=>(preg_match(number_type(),$T)?0:15),'charsetnr'=>($T=="bytea"?63:0),];}}}elseif(extension_loaded("pdo_pgsql")){define("AdminNeo\DRIVER_EXTENSION","PDO_PgSQL");class
PgSqlConnectionBase
extends
PdoConnection{var$timeout=0;function
open($L,$U,$D){$h=Admin::get()->getDatabase();list($rf,$Fj)=host_port($L);$nd="pgsql:".($rf!=""?"host=$rf ":"").($Fj?"port=$Fj ":"")."client_encoding=utf8 dbname='".($h!=""?addcslashes($h,"'\\"):"postgres")."'";$Pl=Admin::get()->getConfig()->getSslMode();if($Pl)$nd
.=" sslmode='$Pl'";if(!$this->dsn($nd,$U,$D))return
false;$Mn=$this->getValue("SELECT version()");$this->flavor=str_contains($Mn,"CockroachDB")?"cockroach":null;return
true;}function
selectDatabase($A){return
Admin::get()->getDatabase()==$A;}function
query($F,$ln=false){$H=parent::query($F,$ln);if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$H;}function
warnings(){return
null;}function
copyFrom($P,array$J){$G=$this->pdo->pgsqlCopyFromArray($P,$J);$Hd=$this->pdo->errorInfo();$this->error=isset($Hd[2])?$Hd[2]:"";return$G;}function
close(){}}}if(class_exists('AdminNeo\PgSqlConnectionBase')){class
PgSqlConnection
extends
PgSqlConnectionBase{private$localhostServer=false;function
open($L,$U,$D){if($L=="localhost")$this->localhostServer=true;return
parent::open($L,$U,$D);}function
getDefaultServerName(){return$this->localhostServer?"localhost":"";}function
multiQuery($F){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$F),$_)){$J=explode("\n",$_[2]);$this->affectedRows=count($J);return$this->copyFrom($_[1],$J);}return
parent::multiQuery($F);}}}class
PgSqlDriver
extends
Driver{protected
function
__construct(Connection$e,$Ba){parent::__construct($e,$Ba);$this->types=[lang(125)=>["smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20,],lang(126)=>["date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0,],lang(127)=>["character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0,],lang(129)=>["bit"=>0,"bit varying"=>0,"bytea"=>0,],lang(131)=>["cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0,],lang(130)=>["box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0,],];if($e->isMinVersion("9.2")){$this->types[lang(127)]["json"]=4294967295;if($e->isMinVersion("9.4"))$this->types[lang(127)]["jsonb"]=4294967295;}if($e->isMinVersion("12")){$this->generated[]="STORED";if($e->isMinVersion("18"))$this->generated[]="VIRTUAL";}$this->operators=["=","<",">","<=",">=","!=","~","~*","!~","!~*","LIKE","LIKE %%","NOT LIKE","ILIKE","ILIKE %%","NOT ILIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->functions=["char_length","lower","upper","round","to_hex","to_timestamp",];$this->grouping=["sum","min","max","avg","count","count distinct",];$this->partitionBy=["RANGE","LIST"];if(!$e->isCockroachDB())$this->partitionBy[]="HASH";$this->insertFunctions=["char"=>"md5","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",];$this->systemDatabases=["template1"];$this->systemSchemas=["information_schema","pg_catalog","pg_toast","pg_temp_*","pg_toast_temp_*"];}function
getNsOidSql(){return"(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";}function
getInsertReturningSql($P){$Ua=array_filter(fields($P),function($k){return$k['auto_increment'];});return
count($Ua)==1?" RETURNING ".idf_escape(key($Ua)):"";}function
insertUpdate($P,array$ik,array$Pj){foreach($ik
as$hk){$un=[];$Z=[];foreach($hk
as$v=>$W){$un[]="$v = $W";if(isset($Pj[idf_unescape($v)]))$Z[]="$v = $W";}if(!(($Z&&queries("UPDATE ".table($P)." SET ".implode(", ",$un)." WHERE ".implode(" AND ",$Z))&&Connection::get()->getAffectedRows())||queries("INSERT INTO ".table($P)." (".implode(", ",array_keys($hk)).") VALUES (".implode(", ",$hk).")")))return
false;}return
true;}function
slowQuery($F,$Mm){$this->connection->query("SET statement_timeout = ".(1000*$Mm));$this->connection->timeout=1000*$Mm;return$F;}function
convertSearch($s,array$Z,array$k){$Gm="char|text";if(strpos($Z["op"],"LIKE")===false)$Gm
.="|date|time(stamp)?|boolean|uuid|inet|cidr|macaddr|".number_type();return(preg_match("~$Gm~",$k["type"])?$s:"CAST($s AS text)");}function
quoteBinary($O){return"'\\x".bin2hex($O)."'";}function
warnings(){return$this->connection->warnings();}function
tableHelp($A,$bg=false){$Tg=["information_schema"=>"infoschema","pg_catalog"=>($bg?"view":"catalog"),];$y=$Tg[$_GET["ns"]];if($y)return"$y-".str_replace("_","-",$A).".html";return
null;}function
getInheritedTables($P){return
get_rows("SELECT relname AS \"table\", nspname AS ns
FROM pg_inherits
JOIN pg_class ON inhrelid = oid
JOIN pg_namespace ON relnamespace = pg_namespace.oid
WHERE inhparent = ".$this->tableOid($P).(Connection::get()->isMinVersion("10")?" AND relispartition::int = 0":"")." ORDER BY 2, 1");}function
getParentTables($P){return
get_rows("SELECT relname AS \"table\", nspname AS ns
FROM pg_class
JOIN pg_inherits ON inhparent = oid
JOIN pg_namespace ON relnamespace = pg_namespace.oid
WHERE inhrelid = ".$this->tableOid($P)." ORDER BY 2, 1");}function
isPartition($P){return
Connection::get()->isMinVersion("10")&&$this->connection->getValue("SELECT relispartition::int FROM pg_class WHERE oid = ".$this->tableOid($P));}function
getPartitionsInfo($P){if(!$this->connection->isMinVersion("10"))return[];$I=$this->connection->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($P))->fetchAssoc();if(!$I)return[];$Sa=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = {$I["partrelid"]} AND attnum IN (".str_replace(" ",", ",$I["partattrs"]).")");$ob=['h'=>'HASH','l'=>'LIST','r'=>'RANGE'];$Jf=["partition_by"=>$ob[$I["partstrat"]],"partition"=>implode(", ",array_map('AdminNeo\idf_escape',$Sa)),];$qj=get_key_vals("SELECT relname, pg_get_expr(c.relpartbound, c.oid) AS bound
FROM pg_inherits
JOIN pg_class c ON inhrelid = oid
WHERE inhparent = ".$this->tableOid($P)." ORDER BY 1");$Jf["partition_names"]=array_keys($qj);$Jf["partition_values"]=array_map(function($X){return
str_replace("FOR VALUES ","",$X);},array_values($qj));return$Jf;}function
tableOid($P){return"(SELECT oid FROM pg_class WHERE relnamespace = ".$this->getNsOidSql()." AND relname = ".q($P)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
getIndexAlgorithms(array$lm){static$Eh=[];if(!$Eh)$Eh=get_vals("SELECT amname FROM pg_am".($this->connection->isMinVersion("9.6")?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->connection->isCockroachDB()?"prefix":"btree")."' DESC, amname");return$Eh;}function
getIndexOpclasses(){static$Bi=[];if(!$Bi&&!$this->connection->isCockroachDB())$Bi=get_vals("SELECT DISTINCT opcname FROM pg_catalog.pg_opclass WHERE NOT opcdefault ORDER BY opcname");return$Bi;}function
supportsIndex(array$lm){return$lm["Engine"]!="view";}function
hasCStyleEscapes(){static$pb;if($pb===null)$pb=($this->connection->getValue("SHOW standard_conforming_strings")=="off");return$pb;}function
explodeArrayValue($X,$T,&$Ok){$Jn=["oidvector"=>"oid","int2vector"=>"smallint",];$Ok=isset($Jn[$T])?$Jn[$T]:null;if($Ok)return
explode(" ",$X);if(preg_match('~^(.+)\[]$~',$T,$_)){$Ok=$_[1];$X=preg_replace('~^\{(.*)}$~',"$1",$X);$Zf=str_contains($Ok,"json");if($Zf){$Y=explode('","',trim($X,'"'));array_walk($Y,function(&$V){$V=str_replace('\"','"',$V);});}elseif(preg_match('~^(\{+)(.*)(}+)$~',$X,$_)){$Y=explode("$_[3],$_[1]",$_[2]);array_walk($Y,function(&$V)use($_){$V=$_[1].$V.$_[3];});}else$Y=explode(",",$X);if(!$Zf&&$Y[0][0]=="{"){$T=$Ok;array_walk($Y,function(&$V)use($T,&$Ok){$V=$this->explodeArrayValue($V,$T,$Ok);});}elseif(!$Zf&&isset($this->types[lang(127)][$Ok])){array_walk($Y,function(&$V){$V=preg_replace('~^\'(.*)\'$~',"$1",$V);});}return$Y;}return[];}function
implodeArrayValues(array$Y,$T){if($T=="oidvector"||$T=="int2vector")return
implode(" ",$Y);if(preg_match('~^([^[]+)(\[])+$~',$T,$_)){$ag=isset($this->types[lang(127)][$_[1]]);$Zf=str_contains($T,"json");array_walk($Y,function(&$V)use($T,$ag,$Zf){if(is_array($V))$V=$this->implodeArrayValues($V,$T);elseif($Zf)$V="\"$V\"";elseif($ag)$V="'$V'";});return"{".implode(",",$Y)."}";}return"";}}function
create_driver(Connection$e){return
PgSqlDriver::create($e,Admin::get());}function
idf_escape($s){return'"'.str_replace('"','""',$s).'"';}function
table($s){return
idf_escape($s);}function
connect($Pj=false,&$j=null){$e=$Pj?PgSqlConnection::create():PgSqlConnection::createSecondary();list($L,$U,$D)=Admin::get()->getCredentials();$G=$e->openPasswordless($L,$U,$D,false);$Hd=[];if(!$G&&$L==""&&preg_match('~connection to server on socket .+ failed: No such file or directory~U',$e->getError())){$Hd[]=$e->getError();$L="localhost";$G=$e->openPasswordless($L,$U,$D,false);}if(!$G){$Hd[]=$e->getError();$j=implode("\n",$Hd);return
null;}if($e->isMinVersion("9"))$e->query("SET application_name = 'AdminNeo'");if($Pj&&$e->isCockroachDB()){Drivers::setName(DRIVER,"CockroachDB");save_driver_name(DRIVER,$L,"CockroachDB");}return$e;}function
get_databases($xe){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($F,$Z,$x,$mi=0,$fl=" "){return" $F$Z".($x?$fl."LIMIT $x".($mi?" OFFSET $mi":""):"");}function
limit1($P,$F,$Z,$fl="\n"){return(preg_match('~^INTO~',$F)?limit($F,$Z,1,0,$fl):" $F".(is_view(table_status1($P))?$Z:$fl."WHERE ctid = (SELECT ctid FROM ".table($P).$Z.$fl."LIMIT 1)"));}function
db_collation($h,array$Mb){return
Connection::get()->getValue("SELECT datcollate FROM pg_database WHERE datname = ".q($h));}function
logged_user(){return
Connection::get()->getValue("SELECT user");}function
tables_list(){$F="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$F
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$F
.="
ORDER BY 1";return
get_key_vals($F);}function
count_tables(array$g){$H=[];foreach($g
as$h){if(Connection::get()->selectDatabase($h))$H[$h]=count(tables_list());}return$H;}function
table_status($A="",$be=false){static$ff;if($ff===null)$ff=Connection::get()->getValue("SELECT 'pg_table_size'::regproc");$H=[];foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\",
	CASE relkind WHEN 'p' THEN 'partitioned' ELSE '' END AS \"Create_options\"".($ff?",
	pg_table_size(c.oid) AS \"Data_length\",
	pg_indexes_size(c.oid) AS \"Index_length\"":"").",
	obj_description(c.oid, 'pg_class') AS \"Comment\",
	".(Connection::get()->isMinVersion("12")?"''":"CASE WHEN relhasoids THEN 'oid' ELSE '' END")." AS \"Oid\",
	reltuples AS \"Rows\",
	".(Connection::get()->isMinVersion("10")?"relispartition::int AS \"Partition\",":"")."
	current_schema() AS nspname
FROM pg_class c
WHERE relkind IN ('r', 'm', 'v', 'f', 'p')
AND relnamespace = ".Driver::get()->getNsOidSql()."
".($A!=""?"AND relname = ".q($A):"ORDER BY relname"))as$I)$H[$I["Name"]]=$I;return$H;}function
is_view(array$Q){return
in_array($Q["Engine"],["view","materialized view"]);}function
fk_support(array$Q){return
true;}function
fields($P){$H=[];$Fa=['timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',];foreach(get_rows("SELECT
	a.attname AS field,
	format_type(a.atttypid, a.atttypmod) AS full_type,
	a.attndims, pg_get_expr(d.adbin, d.adrelid) AS default,
	a.attnotnull::int,
	i.indrelid AS primary,
	col_description(a.attrelid, a.attnum) AS comment".(Connection::get()->isMinVersion("10")?",
	a.attidentity".(Connection::get()->isMinVersion("12")?",
	a.attgenerated":""):"")."
FROM pg_attribute a
LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
LEFT JOIN pg_index i ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey) AND i.indisprimary
WHERE a.attrelid = ".Driver::get()->tableOid($P)."
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$I){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$I["full_type"],$z);list(,$T,$w,$I["length"],$Aa,$Qa)=$z;$_b=$T.$Aa;if(isset($Fa[$_b])){$I["type"]=$Fa[$_b];$I["full_type"]=$I["type"].$w;}else{$I["type"]=$T;$I["full_type"]=$I["type"].$w.$Aa;}for($q=0;$q<$I["attndims"];$q++){$I["length"].=$Qa;$I["full_type"].=$Qa;}if(in_array($I['attidentity'],['a','d']))$I['default']='GENERATED '.($I['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$B=["s"=>"STORED","v"=>"VIRTUAL"];$I["generated"]=(isset($B[$I["attgenerated"]])?$B[$I["attgenerated"]]:"");$I["null"]=!$I["attnotnull"];$I["auto_increment"]=$I['attidentity']||preg_match('~^nextval\(~i',$I["default"])||preg_match('~^unique_rowid\(~',$I["default"]);$I["privileges"]=["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1];if(!$I['generated']&&preg_match('~(.+)::[^,)]+(.*)~s',$I["default"],$z))$I["default"]=($z[1]=="NULL"?null:idf_unescape($z[1]).$z[2]);$H[$I["field"]]=$I;}return$H;}function
indexes($P,$e=null){if(!is_object($e))$e=Connection::get();$H=[];$qm=Driver::get()->tableOid($P);$c=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $qm AND attnum > 0",$e);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname,
	pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr".($e->isCockroachDB()?"":",
	(SELECT string_agg(CASE WHEN opcdefault THEN '' ELSE opcname END, ' ' ORDER BY s)
		FROM generate_subscripts(indclass, 1) AS s
		JOIN pg_catalog.pg_opclass ON pg_opclass.oid = indclass[s]) AS opclasses")."
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $qm
ORDER BY indisprimary DESC, indisunique DESC",$e)as$I){$sk=$I["relname"];$H[$sk]["type"]=($I["indisprimary"]?"PRIMARY":($I["indisunique"]?"UNIQUE":"INDEX"));$H[$sk]["columns"]=[];$H[$sk]["descs"]=[];$H[$sk]["algorithm"]=$I["amname"];$H[$sk]["partial"]=$I["partial"];$Gf=preg_split('~(?<=\)), (?=\()~',$I["indexpr"]);foreach(explode(" ",$I["indkey"])as$Hf)$H[$sk]["columns"][]=($Hf?$c[$Hf]:array_shift($Gf));foreach(explode(" ",$I["indoption"])as$If)$H[$sk]["descs"][]=(intval($If)&1?'1':null);$H[$sk]["opclasses"]=(isset($I["opclasses"])?explode(" ",$I["opclasses"]):[]);$H[$sk]["lengths"]=[];}return$H;}function
foreign_keys($P){$wi=implode("|",Driver::get()->getOnActions());$H=[];foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, condeferred::int AS deferred, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".Driver::get()->tableOid($P)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$I){$I['deferrable']=($I['deferrable']?'':'NOT ').'DEFERRABLE'.($I['deferred']?' INITIALLY DEFERRED':'');if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$I['definition'],$z)){$I['source']=array_map('AdminNeo\idf_unescape',array_map('trim',explode(',',$z[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$z[2],$gh)){$I['ns']=idf_unescape($gh[2]);$I['table']=idf_unescape($gh[4]);}$I['target']=array_map('AdminNeo\idf_unescape',array_map('trim',explode(',',$z[3])));$I['on_delete']=(preg_match("~ON DELETE ($wi)~",$z[4],$gh)?$gh[1]:'NO ACTION');$I['on_update']=(preg_match("~ON UPDATE ($wi)~",$z[4],$gh)?$gh[1]:'NO ACTION');$H[$I['conname']]=$I;}}return$H;}function
backward_keys($P){$F="SELECT s.constraint_name, s.table_schema, s.table_name, s.column_name, t.column_name AS referenced_column_name
FROM information_schema.key_column_usage s
JOIN information_schema.referential_constraints r USING (constraint_catalog, constraint_schema, constraint_name)
JOIN information_schema.key_column_usage t ON r.unique_constraint_catalog = t.constraint_catalog
	AND r.unique_constraint_schema = t.constraint_schema
	AND r.unique_constraint_name = t.constraint_name
	AND r.constraint_catalog = t.constraint_catalog
	AND r.constraint_schema = t.constraint_schema
	AND r.unique_constraint_name = t.constraint_name
	AND s.position_in_unique_constraint = t.ordinal_position
WHERE t.table_catalog = ".q(DB)."
AND t.table_schema = ".q($_GET["ns"])."
AND t.table_name = ".q($P)."
ORDER BY s.ordinal_position";return
get_rows($F,null,"");}function
view($A){return["select"=>trim(Connection::get()->getValue("SELECT pg_get_viewdef(".Driver::get()->tableOid($A).")"))];}function
collations(){return[];}function
information_schema($h,$Sk=""){return
in_array($Sk!=""?$Sk:get_schema(),["information_schema","pg_catalog","pg_toast"]);}function
error(){$H=h(Connection::get()->getError());if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$H,$z))$H=$z[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($z[3]).'})(.*)~','\1<b>\2</b>',$z[2]).$z[4];return
nl2br($H);}function
create_database($h,$Lb){return(bool)queries("CREATE DATABASE ".idf_escape($h).($Lb?" ENCODING ".idf_escape($Lb):""));}function
drop_databases(array$g){Connection::get()->close();return
apply_queries("DROP DATABASE",$g,'AdminNeo\idf_escape');}function
rename_database($A,$Lb){Connection::get()->close();return(bool)queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($A));}function
auto_increment(){return"";}function
alter_table($P,$A,array$l,array$ze,$Vb,$Cd,$Lb,$Va,$pj){$La=[];$ak=[];if($P!=""&&$P!=$A)$ak[]="ALTER TABLE ".table($P)." RENAME TO ".table($A);$gl="";foreach($l
as$k){$b=idf_escape($k[0]);$W=$k[1];if(!$W)$La[]="DROP $b";else{$Gn=$W[5];unset($W[5]);if($k[0]==""){if(isset($W[6]))$W[1]=($W[1]==" bigint"?" big":($W[1]==" smallint"?" small":" "))."serial";$La[]=($P!=""?"ADD ":"  ").implode($W);if(isset($W[6]))$La[]=($P!=""?"ADD":" ")." PRIMARY KEY ($W[0])";}else{if($b!=$W[0])$ak[]="ALTER TABLE ".table($A)." RENAME $b TO $W[0]";$La[]="ALTER $b TYPE$W[1]";$hl=$P."_".idf_unescape($W[0])."_seq";$La[]="ALTER $b ".($W[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) (STORED|VIRTUAL)~','EXPRESSION\1',$W[3]):(isset($W[6])?"SET DEFAULT nextval(".q($hl).")":"DROP DEFAULT"));if(isset($W[6]))$gl="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($hl)." OWNED BY ".idf_escape($P).".$W[0]";$La[]="ALTER $b ".($W[2]==" NULL"?"DROP NOT":"SET").$W[2];}if($k[0]!=""||$Gn!="")$ak[]="COMMENT ON COLUMN ".table($A).".$W[0] IS ".($Gn!=""?substr($Gn,9):"''");}}$La=array_merge($La,$ze);if($P==""){$N="";if($pj){$N=" PARTITION BY {$pj["partition_by"]}({$pj["partition"]})";if($pj["partition_by"]=='HASH'){$qj=(int)$pj["partitions"];for($q=0;$q<$qj;$q++)$ak[]="CREATE TABLE ".idf_escape($A."_$q")." PARTITION OF ".idf_escape($A)." FOR VALUES WITH (MODULUS $qj, REMAINDER $q)";}else{$Hb=Connection::get()->isCockroachDB();$Oj="MINVALUE";foreach($pj["partition_names"]as$q=>$W){$X=$pj["partition_values"][$q];$kj=" VALUES ".($pj["partition_by"]=='LIST'?"IN ($X)":"FROM ($Oj) TO ($X)");if($Hb)$N
.=($q?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$W)?$W:idf_escape($W)).$kj;else$ak[]="CREATE TABLE ".idf_escape($A."_$W")." PARTITION OF ".idf_escape($A)." FOR$kj";$Oj=$X;}$N
.=$Hb?"\n)":"";}}array_unshift($ak,"CREATE TABLE ".table($A)." (\n".implode(",\n",$La)."\n)$N");}elseif($La)array_unshift($ak,"ALTER TABLE ".table($P)."\n".implode(",\n",$La));if($gl)array_unshift($ak,$gl);if($Vb!==null)$ak[]="COMMENT ON TABLE ".table($A)." IS ".q($Vb);if($Va!="");foreach($ak
as$F){if(!queries($F))return
false;}return
true;}function
alter_indexes($P,array$La){$qc=[];$id=[];$ak=[];foreach($La
as$W){if($W[0]!="INDEX")$qc[]=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");elseif($W[2]=="DROP")$id[]=idf_escape($W[1]);else$ak[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($P."_"))." ON ".table($P).($W[3]?" USING $W[3]":"")." (".implode(", ",$W[2]).")".($W[4]?" WHERE $W[4]":"");}if($qc)array_unshift($ak,"ALTER TABLE ".table($P).implode(",",$qc));if($id)array_unshift($ak,"DROP INDEX ".implode(", ",$id));foreach($ak
as$F){if(!queries($F))return
false;}return
true;}function
truncate_tables(array$R){return(bool)queries("TRUNCATE ".implode(", ",array_map('AdminNeo\table',$R)));}function
drop_views(array$Pn){return
drop_tables($Pn);}function
drop_tables(array$R){foreach($R
as$P){$N=table_status1($P);if(!queries("DROP ".strtoupper($N["Engine"])." ".table($P)))return
false;}return
true;}function
move_tables(array$R,array$Pn,$Am){foreach(array_merge($R,$Pn)as$P){$N=table_status1($P);if(!queries("ALTER ".strtoupper($N["Engine"])." ".table($P)." SET SCHEMA ".idf_escape($Am)))return
false;}return
true;}function
trigger($A,$P){if($A=="")return["Statement"=>"EXECUTE PROCEDURE ()"];$c=[];$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($P)." AND trigger_name = ".q($A);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$I)$c[]=$I["event_object_column"];$cn=[];foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event",
	\'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers '."$Z ORDER BY event_manipulation DESC")as$I){if($c&&$I["Event"]=="UPDATE")$I["Event"].=" OF";$I["Of"]=implode(", ",$c);if($cn)$I["Event"].=" OR $cn[Event]";$cn=$I;}return$cn;}function
triggers($P){$H=[];foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($P))as$I){$cn=trigger($I["trigger_name"],$P);$H[$cn["Trigger"]]=[$cn["Timing"],$cn["Event"]];}return$H;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER"],"Event"=>["INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",],"Type"=>["FOR EACH ROW","FOR EACH STATEMENT"],];}function
routine($A,$T){$Jf=get_rows('SELECT routine_definition, LOWER(external_language) AS language, type_udt_name
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($A));$Jf=isset($Jf[0])?$Jf[0]:[];$l=get_rows("SELECT COALESCE(parameter_name, ordinal_position::text) AS field,
	CASE data_type WHEN 'USER-DEFINED' THEN udt_name WHEN 'ARRAY' THEN substr(udt_name, 2) || '[]' ELSE data_type END AS type,
	character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = ".q($A)."
ORDER BY ordinal_position");return["fields"=>$l,"returns"=>["type"=>preg_replace('~^_(.*)~','\1[]',isset($Jf["type_udt_name"])?$Jf["type_udt_name"]:"")],"definition"=>isset($Jf["routine_definition"])?$Jf["routine_definition"]:null,"language"=>isset($Jf["language"])?$Jf["language"]:null,"comment"=>null,];}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_name AS "ROUTINE_NAME", routine_type AS "ROUTINE_TYPE", type_udt_name AS "DTD_IDENTIFIER", null AS ROUTINE_COMMENT
FROM information_schema.routines
WHERE routine_schema = current_schema()'.(Connection::get()->isCockroachDB()?'':"
AND substring(specific_name, '[0-9]+\$')::oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_proc'::regclass AND deptype = 'e')").'
ORDER BY SPECIFIC_NAME');}function
routine_languages(){return
get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language");}function
routine_id($A,array$I){$H=[];foreach($I["fields"]as$k){$w=$k["length"];$H[]=$k["type"].($w?"($w)":"");}return
idf_escape($A)."(".implode(", ",$H).")";}function
last_id($G){$I=$G
instanceof
Result?$G->fetchRow():[];return$I?$I[0]:0;}function
explain(Connection$e,$F){return$e->query("EXPLAIN $F");}function
found_rows(array$Q,array$Z){if(preg_match("~ rows=([0-9]+)~",Connection::get()->getValue("EXPLAIN SELECT * FROM ".idf_escape($Q["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$rk))return(int)$rk[1];return
null;}function
types($Wd=false){return
get_key_vals("SELECT oid, typname
FROM pg_type
WHERE typnamespace = ".Driver::get()->getNsOidSql()."
AND typtype IN ('b','d','e')
AND typelem = 0".($Wd||Connection::get()->isCockroachDB()?'':"
AND oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_type'::regclass AND deptype = 'e')"));}function
type_values($r){$Fd=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $r ORDER BY enumsortorder");if(!$Fd)return"";return"'".implode("', '",array_map('addslashes',$Fd))."'";}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return
Connection::get()->getValue("SELECT current_schema()");}function
set_schema($Sk,$e=null){if(!$e)$e=Connection::get();$G=(bool)$e->query("SET search_path TO ".idf_escape($Sk));Driver::get()->setUserTypes(types(true));return$G;}function
foreign_keys_sql($P){$H="";$N=table_status1($P);$fi=idf_escape($N['nspname']);$ue=foreign_keys($P);ksort($ue);foreach($ue
as$te=>$se)$H
.="ALTER TABLE ONLY $fi.".idf_escape($N['Name'])." ADD CONSTRAINT ".idf_escape($te)." ".preg_replace('~( REFERENCES )([^(.]+\()~',"\\1$fi.\\2",$se["definition"]).";\n";return($H?"$H\n":$H);}function
foreign_key_checks_sql($_d){return"SET session_replication_role = ".($_d?"DEFAULT":"replica").";\n";}function
restart_sequences_sql($P){$H="";foreach(fields($P)as$k){if(!$k["auto_increment"]||preg_match('~^unique_rowid\(~',$k["default"]))continue;$gl="pg_get_serial_sequence(".q(table($P)).", ".q($k["field"]).")";$b=idf_escape($k["field"]);$H
.="SELECT setval($gl, MAX($b)) FROM ".table($P)." HAVING MAX($b) IS NOT NULL;\n";}return$H;}function
create_sql($P,$Va,$Wl){$Ak=[];$il=[];$N=table_status1($P);$fi=idf_escape($N['nspname']);if(is_view($N)){$Nn=view($P);return
rtrim("CREATE VIEW $fi.".idf_escape($P)." AS $Nn[select]",";");}$l=fields($P);if(count($N)<2||empty($l))return"";$H="CREATE TABLE $fi.".idf_escape($N['Name'])." (\n    ";foreach($l
as$k){if($k['default']=="nextval('$N[Name]_$k[field]_seq')"){$k['default']=null;$k['full_type']=preg_replace('~int(eger)?~','serial',$k['full_type']);}$ij=idf_escape($k['field']).' '.$k['full_type'].preg_replace('~(nextval\(\')([^.\']+\')~','\1'.str_replace("'","''",$N['nspname']).'.\2',default_value($k)).($k['null']?"":" NOT NULL");$Ak[]=$ij;if(preg_match('~nextval\(\'([^\']+)\'\)~',$k['default'],$_)){$hl=$_[1];$J=get_rows((Connection::get()->isMinVersion("10")?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($hl)):"SELECT * FROM $hl"),null,"-- ");if($J){$Hl=first($J);$il[]=($Wl=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $fi.$hl;\n":"")."CREATE SEQUENCE $fi.$hl INCREMENT $Hl[increment_by] MINVALUE $Hl[min_value] MAXVALUE $Hl[max_value]".($Va&&$Hl['last_value']?" START ".($Hl["last_value"]+1):"")." CACHE $Hl[cache_value];";}}}if(!empty($il))$H=implode("\n\n",$il)."\n\n$H";$Pj="";foreach(indexes($P)as$Ef=>$t){if($t['type']=='PRIMARY'){$Pj=$Ef;$Ak[]="CONSTRAINT ".idf_escape($Ef)." PRIMARY KEY (".implode(', ',array_map('AdminNeo\idf_escape',$t['columns'])).")";}}foreach(Driver::get()->checkConstraints($P)as$dc=>$hc)$Ak[]="CONSTRAINT ".idf_escape($dc)." CHECK ($hc)";$H
.=implode(",\n    ",$Ak)."\n)";$kj=Driver::get()->getPartitionsInfo($N['Name']);if($kj)$H
.="\nPARTITION BY {$kj["partition_by"]}({$kj["partition"]})";$H
.="\nWITH (oids = ".($N['Oid']?'true':'false').");";if($N['Comment'])$H
.="\n\nCOMMENT ON TABLE $fi.".idf_escape($N['Name'])." IS ".q($N['Comment']).";";foreach($l
as$ge=>$k){if($k['comment'])$H
.="\n\nCOMMENT ON COLUMN $fi.".idf_escape($N['Name']).".".idf_escape($ge)." IS ".q($k['comment']).";";}foreach(get_rows("SELECT indexdef FROM pg_catalog.pg_indexes WHERE schemaname = current_schema() AND tablename = ".q($P).($Pj?" AND indexname != ".q($Pj):""),null,"-- ")as$I)$H
.="\n\n$I[indexdef];";return
rtrim($H,';');}function
truncate_sql($P){return"TRUNCATE ".table($P);}function
trigger_sql($P){$N=table_status1($P);$Il="";foreach(triggers($P)as$bn=>$an){$cn=trigger($bn,$N['Name']);$Il
.="\nCREATE TRIGGER ".idf_escape($cn['Trigger'])." $cn[Timing] $cn[Event] ON ".idf_escape($N["nspname"]).".".idf_escape($N['Name'])." $cn[Type] $cn[Statement];;\n";}return$Il;}function
create_database_sql($Bc,$Wl=""){$A=idf_escape($Bc);$Tb="";if(str_contains($Wl,"CREATE")){if($Wl=="DROP+CREATE")$Tb="DROP DATABASE IF EXISTS $A;\n";$Tb
.="CREATE DATABASE $A;\n";}return$Tb;}function
use_sql($Bc,$Wl=""){return'\connect '.idf_escape($Bc).";\n";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(Connection::get()->isMinVersion("9.2")?"pid":"procpid"));}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$H){return$H;}function
support($ce){if($ce=="processlist")return!Connection::get()->isCockroachDB();elseif($ce=="materializedview")return
Connection::get()->isMinVersion("9.3");elseif($ce=="procedure")return
Connection::get()->isMinVersion("11");return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|fast_status|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table|trigger|type|variables|view)$~',$ce);}function
kill_process($W){return
queries("SELECT pg_terminate_backend(".number($W).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return(int)Connection::get()->getValue("SHOW max_connections");}}Drivers::add("sqlite","SQLite",["SQLite3","PDO_SQLite"]);if(isset($_GET["sqlite"])){define("AdminNeo\DRIVER","sqlite");define("AdminNeo\DIALECT","sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","SQLite3");abstract
class
SqLiteConnectionBase
extends
Connection{private$sqlite;function
open($L,$U,$D){$this->sqlite=new
SQLite3($L);$this->version=$this->sqlite->version()["versionString"];return
true;}function
query($F,$ln=false){$G=@$this->sqlite->query($F);$this->error="";if(!$G){$this->errno=$this->sqlite->lastErrorCode();$this->error=$this->sqlite->lastErrorMsg();return
false;}elseif($G->numColumns())return
new
SqLiteResult($G);$this->affectedRows=$this->sqlite->changes();return
true;}function
quote($O){if(is_utf8($O))return"'".$this->sqlite->escapeString($O)."'";else
return"x'".bin2hex($O)."'";}}class
SqLiteResult
extends
Result{private$resource;private$offset=0;function
__construct(SQLite3Result$xk){parent::__construct(0);$this->resource=$xk;}function
fetchAssoc(){return$this->resource->fetchArray(SQLITE3_ASSOC);}function
fetchRow(){return$this->resource->fetchArray(SQLITE3_NUM);}function
fetchField(){$kn=[1=>"integer","real","text","blob","null"];$b=$this->offset++;$T=$this->resource->columnType($b);if($T===false)return
false;return(object)["name"=>$this->resource->columnName($b),"native_type"=>$kn[$T],"type"=>($T==SQLITE3_TEXT?15:0),"charsetnr"=>($T==SQLITE3_BLOB?63:0),];}}}elseif(extension_loaded("pdo_sqlite")){define("AdminNeo\DRIVER_EXTENSION","PDO_SQLite");abstract
class
SqLiteConnectionBase
extends
PdoConnection{function
open($L,$U,$D){return$this->dsn(DRIVER.":$L","","");}function
quote($O){if(is_utf8($O))return
parent::quote($O);else
return"x'".bin2hex($O)."'";}}}if(class_exists('AdminNeo\SqLiteConnectionBase')){class
SqLiteConnection
extends
SqLiteConnectionBase{protected
function
__construct(){parent::__construct();$this->open(":memory:","","");}function
open($L,$U,$D){if(!parent::open($L,$U,$D))return
false;$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return
true;}function
close(){$this->open(":memory:","","");}function
selectDatabase($A){if(is_readable($A)&&$this->query("ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$A)?$A:dirname($_SERVER["SCRIPT_FILENAME"])."/$A")." AS a"))return
self::open($A,"","");return
false;}}}class
SqliteDriver
extends
Driver{protected
function
__construct(Connection$e,$Ba){parent::__construct($e,$Ba);$this->types=[["integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0,]];if($e->isMinVersion("3.31"))$this->generated=["STORED","VIRTUAL"];if($e->isMinVersion("3.37"))$this->types[0]["any"]=0;$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->functions=["length","lower","upper","round","hex","unixepoch",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->insertFunctions=[];$this->editFunctions=["integer|real|numeric"=>"+/-","text"=>"||",];}function
getStructuredTypes(){return
parent::getStructuredTypes()[0];}function
quoteBinary($O){return"x".q(bin2hex($O));}function
engines(){$H=["table"];if(Connection::get()->isMinVersion("3.8.2")){if(Connection::get()->isMinVersion("3.37")){$H[]="STRICT";$H[]="STRICT, WITHOUT ROWID";}$H[]="WITHOUT ROWID";}return$H;}function
insertUpdate($P,array$ik,array$Pj){$Y=[];foreach($ik
as$hk)$Y[]="(".implode(", ",$hk).")";return
queries("REPLACE INTO ".table($P)." (".implode(", ",array_keys(reset($ik))).") VALUES\n".implode(",\n",$Y));}function
tableHelp($A,$bg=false){if(preg_match('~^sqlite_(seq|stat.)~',$A,$z))return"fileformat2.html#$z[1]tab";if(preg_match('~^sqlite(_temp)?_(master|schema)$~',$A))return"schematab.html";return
null;}function
checkConstraints($P){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',$this->connection->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($P)),$_);return
array_combine($_[2],$_[2]);}function
getAllFields(){$Ga=[];foreach(tables_list()as$P=>$T){foreach(fields($P)as$k)$Ga[$P][]=$k;}return$Ga;}}function
create_driver(Connection$e){return
SqliteDriver::create($e,Admin::get());}function
idf_escape($s){return'"'.str_replace('"','""',$s).'"';}function
table($s){return
idf_escape($s);}function
connect($Pj=false,&$j=null){$e=$Pj?SqLiteConnection::create():SqLiteConnection::createSecondary();$D=Admin::get()->getCredentials()[2];if($D!=""){$G=Admin::get()->verifyDefaultPassword($D);if($G!==true){$j=$G;return
null;}}return$e;}function
get_databases($xe){return[];}function
limit($F,$Z,$x,$mi=0,$fl=" "){return" $F$Z".($x?$fl."LIMIT $x".($mi?" OFFSET $mi":""):"");}function
limit1($P,$F,$Z,$fl="\n"){return(preg_match('~^INTO~',$F)||Connection::get()->getValue("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($F,$Z,1,0,$fl):" $F WHERE rowid = (SELECT rowid FROM ".table($P).$Z.$fl."LIMIT 1)");}function
db_collation($h,array$Mb){return
Connection::get()->getValue("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name LIKE 'sqlite_%'), name");}function
count_tables(array$g){return[];}function
db_status(){$e=Connection::get();$cj=$e->getValue("PRAGMA page_size");$Ie=$e->getValue("PRAGMA freelist_count")*$cj;return["Data_length"=>$e->getValue("PRAGMA page_count")*$cj-$Ie,"Index_length"=>0,"Data_free"=>$Ie,];}function
table_status($A="",$be=false){$H=[];$J=[];if(!$be){if($A=="")Connection::get()->query("PRAGMA optimize = 0x10002");$J=get_key_vals("SELECT tbl, MAX(CAST(stat AS integer)) FROM sqlite_stat1".($A!=""?" WHERE tbl = ".q($A):"")." GROUP BY tbl");}foreach(get_rows("SELECT name AS Name, type AS Engine, sql, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($A!=""?"AND name = ".q($A):"ORDER BY (name LIKE 'sqlite_%'), name"))as$I){if($I["Engine"]=="table"){$Zl=preg_replace('~.*\)~s','',$I["sql"]);$I["Engine"]=implode(", ",array_filter([(preg_match('~\bSTRICT\b~i',$Zl)?"STRICT":""),(preg_match('~\bWITHOUT\s+ROWID\b~i',$Zl)?"WITHOUT ROWID":""),]))?:"table";}unset($I["sql"]);$I["Rows"]=isset($J[$I["Name"]])?$J[$I["Name"]]:0;$H[$I["Name"]]=$I;}if(!$be){foreach(get_rows("SELECT * FROM sqlite_sequence".($A!=""?" WHERE name = ".q($A):""),null,"")as$I)$H[$I["name"]]["Auto_increment"]=$I["seq"];}return$H;}function
is_view(array$Q){return$Q["Engine"]=="view";}function
fk_support(array$Q){return!Connection::get()->getValue("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($P){$H=[];$Il=Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($P));$Uj=["select"=>1,"where"=>1,"order"=>1];if(!preg_match('~^sqlite(_temp)?_(master|schema)$~',$P))$Uj+=["insert"=>1,"update"=>1];foreach(get_rows("PRAGMA table_".(Connection::get()->isMinVersion("3.31")?"x":"")."info(".table($P).")")as$I){$A=$I["name"];$T=strtolower($I["type"]);$i=$I["dflt_value"];$H[$A]=["field"=>$A,"type"=>(preg_match('~int~i',$T)?"integer":(preg_match('~char|clob|text~i',$T)?"text":(preg_match('~blob~i',$T)?"blob":(preg_match('~real|floa|doub~i',$T)?"real":(preg_match('~any~i',$T)?"any":"numeric"))))),"full_type"=>$T,"default"=>(preg_match("~^'(.*)'$~s",$i,$z)?str_replace("''","'",$z[1]):($i=="NULL"?null:$i)),"null"=>!$I["notnull"],"privileges"=>$Uj,"primary"=>$I["pk"],];if($I["pk"]&&preg_match('~\bAUTOINCREMENT\b~i',$Il))$H[$A]["auto_increment"]=true;}$s='(("[^"]*+")+|[a-z0-9_]+)';preg_match_all('~'.$s.'\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$Il,$_,PREG_SET_ORDER);foreach($_
as$z){$A=str_replace('""','"',preg_replace('~^"|"$~','',$z[1]));if($H[$A])$H[$A]["collation"]=trim($z[3],"'");}preg_match_all('~'.$s.'\s.*GENERATED ALWAYS AS \((.+)\) (STORED|VIRTUAL)~i',$Il,$_,PREG_SET_ORDER);foreach($_
as$z){$A=str_replace('""','"',preg_replace('~^"|"$~','',$z[1]));$H[$A]["default"]=$z[3];$H[$A]["generated"]=strtoupper($z[4]);}return$H;}function
indexes($P,$e=null){if(!is_object($e))$e=Connection::get();$H=[];$Il=$e->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($P));if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$Il,$z)){$H[""]=["type"=>"PRIMARY","columns"=>[],"lengths"=>[],"descs"=>[]];preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$z[1],$_,PREG_SET_ORDER);foreach($_
as$z){$H[""]["columns"][]=idf_unescape($z[2]).$z[4];$H[""]["descs"][]=(preg_match('~DESC~i',$z[5])?'1':null);}}if(!$H){foreach(fields($P)as$A=>$k){if($k["primary"])$H[""]=["type"=>"PRIMARY","columns"=>[$A],"lengths"=>[],"descs"=>[null]];}}$Nl=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($P),$e);foreach(get_rows("PRAGMA index_list(".table($P).")",$e)as$I){$A=$I["name"];$t=["type"=>($I["unique"]?"UNIQUE":"INDEX")];$t["lengths"]=[];$t["descs"]=[];foreach(get_rows("PRAGMA index_info(".idf_escape($A).")",$e)as$Hk){$t["columns"][]=$Hk["name"];$t["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($A).' ON '.idf_escape($P),'~').' \((.*)\)$~i',$Nl[$A],$rk)){preg_match_all('/("[^"]*+")+( DESC)?/',$rk[2],$_);foreach($_[2]as$v=>$W){if($W)$t["descs"][$v]='1';}}if(!$H[""]||$t["type"]!="UNIQUE"||$t["columns"]!=$H[""]["columns"]||$t["descs"]!=$H[""]["descs"]||!preg_match("~^sqlite_~",$A))$H[$A]=$t;}return$H;}function
foreign_keys($P){$H=[];foreach(get_rows("PRAGMA foreign_key_list(".table($P).")")as$I){$o=&$H[$I["id"]];if(!$o)$o=$I;$o["source"][]=$I["from"];$o["target"][]=$I["to"];}return$H;}function
backward_keys($P){return[];}function
view($A){return["select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($A)))];}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):[]);}function
information_schema($h,$Sk=""){return
false;}function
error(){return
h(Connection::get()->getError());}function
check_sqlite_name($A){$Wd="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($Wd)\$~",$A)){Connection::get()->setError(lang(132,str_replace("|",", ",$Wd)));return
false;}return
true;}function
create_database($h,$Lb){if(file_exists($h)){Connection::get()->setError(lang(133));return
false;}if(!check_sqlite_name($h))return
false;try{$y=SqLiteConnection::createSecondary();$y->open($h,"","");}catch(Exception$Ld){Connection::get()->setError($Ld->getMessage());return
false;}$y->query('PRAGMA encoding = "UTF-8"');$y->query('CREATE TABLE adminneo (i)');$y->query('DROP TABLE adminneo');return
true;}function
drop_databases(array$g){Connection::get()->close();foreach($g
as$h){if(!check_sqlite_name($h))return
false;if(!@unlink($h)){Connection::get()->setError(lang(133));return
false;}}return
true;}function
rename_database($A,$Lb){if(!check_sqlite_name($A))return
false;Connection::get()->close();Connection::get()->setError(lang(133));return@rename(DB,$A);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($P,$A,array$l,array$ze,$Vb,$Cd,$Lb,$Va,$pj){$zn=($P==""||$ze||$Cd);foreach($l
as$k){if($k[0]!=""||!$k[1]||$k[2]){$zn=true;break;}}$Ma=[];$Wi=[];foreach($l
as$k){if(!$k[1])continue;if($k[0]!="")$Wi[$k[0]]=$k[1][0];$Ma[]=($zn?$k[1]:"ADD ".implode($k[1]));}if(!$zn){foreach($Ma
as$W){if(!queries("ALTER TABLE ".table($P)." $W"))return
false;}if($P!=$A&&!queries("ALTER TABLE ".table($P)." RENAME TO ".table($A)))return
false;}elseif(!recreate_table($P,$A,$Ma,$Wi,$ze,$Va,[],"","",$Cd))return
false;if($Va){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $Va WHERE name = ".q($A));if(!Connection::get()->getAffectedRows())queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($A).", $Va)");queries("COMMIT");}return
true;}function
recreate_table($P,$A,array$l,array$Wi,array$ze,$Va="",array$u=[],$jd="",$_a="",$Cd=""){if($P!=""){if(!$l){foreach(fields($P)as$v=>$k){if($u)$k["auto_increment"]=0;$l[]=process_field($k,$k);$Wi[$v]=idf_escape($v);}}$Qj=false;foreach($l
as$k){if($k[6])$Qj=true;}$ld=[];foreach($u
as$v=>$W){if($W[2]=="DROP"){$ld[$W[1]]=true;unset($u[$v]);}}foreach(indexes($P)as$sg=>$t){$c=[];foreach($t["columns"]as$v=>$b){if(!isset($Wi[$b]))continue
2;$c[]=$Wi[$b].($t["descs"][$v]?" DESC":"");}if(!$ld[$sg]){if($t["type"]!="PRIMARY"||!$Qj){$sg=preg_replace('~^sqlite_~',"",$sg);$u[]=[$t["type"],$sg,$c];}}}foreach($u
as$v=>$W){if($W[0]=="PRIMARY"){unset($u[$v]);$ze[]="  PRIMARY KEY (".implode(", ",$W[2]).")";}}foreach(foreign_keys($P)as$sg=>$o){foreach($o["source"]as$v=>$b){if(!$Wi[$b])continue
2;$o["source"][$v]=idf_unescape($Wi[$b]);}if(!isset($ze[" $sg"]))$ze[]=" ".format_foreign_key($o);}queries("BEGIN");}$vb=array();foreach($l
as$k){if(preg_match('~GENERATED~',$k[3]))unset($Wi[array_search($k[0],$Wi)]);$vb[]="  ".implode($k);}$vb=array_merge($vb,array_filter($ze));foreach(Driver::get()->checkConstraints($P)as$yb){if($yb!=$jd)$vb[]="  CHECK ($yb)";}if($_a)$vb[]="  CHECK ($_a)";$Cm=($P!=""&&$P==$A?"adminneo_$A":$A);if(!$Cd&&$P!="")$Cd=table_status1($P)["Engine"]??null;if(!queries("CREATE TABLE ".table($Cm)." (\n".implode(",\n",$vb)."\n)".($Cd!="table"&&in_array($Cd,Driver::get()->engines())?" $Cd":"")))return
false;if($P!=""){if($Wi&&!queries("INSERT INTO ".table($Cm)." (".implode(", ",$Wi).") SELECT ".implode(", ",array_map('AdminNeo\idf_escape',array_keys($Wi)))." FROM ".table($P)))return
false;$fn=[];foreach(triggers($P)as$dn=>$Nm){$cn=trigger($dn,$P);$fn[]="CREATE TRIGGER ".idf_escape($dn)." ".implode(" ",$Nm)." ON ".table($A)."\n$cn[Statement]";}$Va=$Va?"":Connection::get()->getValue("SELECT seq FROM sqlite_sequence WHERE name = ".q($P));if(!queries("DROP TABLE ".table($P))||($P==$A&&!queries("ALTER TABLE ".table($Cm)." RENAME TO ".table($A)))||!alter_indexes($A,$u))return
false;if($Va)queries("UPDATE sqlite_sequence SET seq = $Va WHERE name = ".q($A));foreach($fn
as$cn){if(!queries($cn))return
false;}queries("COMMIT");}return
true;}function
index_sql($P,$T,$A,$c){return"CREATE $T ".($T!="INDEX"?"INDEX ":"").idf_escape($A!=""?$A:uniqid($P."_"))." ON ".table($P)." $c";}function
alter_indexes($P,array$La){foreach($La
as$t){if($t[0]=="PRIMARY"||(preg_match('~^sqlite_~',$t[1])))return
recreate_table($P,$P,[],[],[],"",$La);}foreach(array_reverse($La)as$W){if(!queries($W[2]=="DROP"?"DROP INDEX ".idf_escape($W[1]):index_sql($P,$W[0],$W[1],"(".implode(", ",$W[2]).")")))return
false;}return
true;}function
truncate_tables(array$R){return
apply_queries("DELETE FROM",$R);}function
drop_views(array$Pn){return
apply_queries("DROP VIEW",$Pn);}function
drop_tables(array$R){return
apply_queries("DROP TABLE",$R);}function
move_tables(array$R,array$Pn,$Am){return
false;}function
trigger($A,$P){if($A=="")return["Statement"=>"BEGIN\n\t;\nEND"];$s='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$en=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$s\\s*(".implode("|",$en["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($s))?\\s+ON\\s*$s\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($A)),$z);$li=$z[3];return["Trigger"=>$A,"Timing"=>strtoupper($z[1]),"Event"=>strtoupper($z[2]).($li?" OF":""),"Of"=>idf_unescape($li),"Statement"=>$z[4],];}function
triggers($P){$H=[];$en=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($P))as$I){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$en["Timing"]).')\s*(.*?)\s+ON\b~i',$I["sql"],$z);$H[$I["name"]]=[$z[1],$z[2]];}return$H;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER","INSTEAD OF"],"Event"=>["INSERT","UPDATE","UPDATE OF","DELETE"],"Type"=>["FOR EACH ROW"],];}function
begin(){return
queries("BEGIN");}function
last_id($G){return
Connection::get()->getValue("SELECT LAST_INSERT_ROWID()");}function
explain(Connection$e,$F){return$e->query("EXPLAIN QUERY PLAN $F");}function
found_rows(array$Q,array$Z){return
null;}function
types(){return[];}function
create_sql($P,$Va,$Wl){$H=Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($P));foreach(indexes($P)as$A=>$t){if($A==''||strpos($A,"sqlite_")===0)continue;$H
.=";\n\n".index_sql($P,$t['type'],$A,"(".implode(", ",array_map('AdminNeo\idf_escape',$t['columns'])).")");}return$H;}function
truncate_sql($P){return"DELETE FROM ".table($P);}function
trigger_sql($P){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($P)));}function
show_variables(){$H=[];foreach(get_rows("PRAGMA pragma_list")as$I){$A=$I["name"];if($A!="pragma_list"&&$A!="compile_options"){$H[$A]=[$A,''];foreach(get_rows("PRAGMA $A")as$Ik)$H[$A][1].=implode(", ",$Ik)."\n";}}return$H;}function
show_status(){$H=[];foreach(get_vals("PRAGMA compile_options")as$Fi)$H[]=explode("=",$Fi,2)+["",""];return$H;}function
convert_field(array$k){return
null;}function
unconvert_field(array$k,$H){return$H;}function
support($ce){return
preg_match('~^(check|columns|database|drop_col|dump|indexes|descidx|move_col|sql|status|table|trigger|variables|view|view_trigger)$~',$ce);}}$Dj="adminneo-plugins";if(is_dir($Dj)){foreach(glob("$Dj/*.php")as$n)include_once$n;}function
get_translations($Bg){switch($Bg){case'_template':$d='.JLyD
~.!<:c^?bO}C3n%KZj3T[u;nr>v4{oZ6+7gN_K=<,-eXxdg@Ov4Yp6:d;
N5EvhV-t%,Q<|iV_bMyw=qfV!Lgy$W_be!EGz>6UCy[srwt!Hq.6-X_jylon}tTtG=+x3f`uYBcog5sxCcti?r0,_w=J9X]ejBujrM*s2baJq5/pZ!Nx~t"5TJpD#>$s?`^^J]I=f.o+gT{0`aVo.N6&/"/&7n6G9IW97=VozLun?WjtXfsJV%o_v/4A_a!-cNSd2HT(q#_"&b#4X$vTG$L2X9(pqAlyFKxT/eaMzx]6_R2+u"[N!V>*r"-N&V>&^Y<3?n:T~"JC!P,1;"zd;*lGfjFCN^WiK3#3[#]d;(^a_C_XE
-)%%3r&H_:JU(hJd],"Rm?a
<GH"?-$jT%+!#"?-#jY*R#.OWS(<,YNITizV/k;;-v6!:dFV6EdJI/$V}&vW@=+W?iIbAmU,S;1iMu
%J2/A"<ro.W8`Ccza
5:+NpyCUGEQjd&2z!=;d*7[s4=W-TK?[#Jk98Nqp;zV6w>z)*BpXTg:1,3D<JBtJYZlpU8#90+2nPT]<5sqq?%2(7CMV+{PH4kP9)vGXuZh6ap.*.+.,QA:CZ*vOQWn(F3T&Jlt=T[soj$sCl<Anv+o7ILEv-1CzH8
1`El)7dV1MQa$#MgZU-?AF:w?W()"o:Lb2BJ,;CHT1[VK1`V$xEgiylT~6[b$wm1o$P0_ENxmutxYDR_gW9:6Z0JLgZDsuK#W6M@``T9japX.gt<V?dEpv,x:DgKnE|P<FtX:QGEu_bmK8pCUhNhDljb1nC4zGt`{xb$5`wHRpORikL5!`C/Z-IiNs#^u`_JOxVf/Ay+?H]$}a`^b!!+EoY7EWy?
T2=&5WISeQmy=gF/@7.y^D/85QFx1}M`:xSJtfn9b`7"g3>?IW%4-tGveq.^p`6*J#.b3{_K%uEq^n]T5Kb}9v7}_<67P.6tCQO*+(5GWn1tM&#Zsh:Q243wv<phE<W0K?19:MYmGO
ID*[<A{NWAF_G<[2ev5!veT!r2~[d(gQH9s_KB)r85e72@&Il5e]J>A]2T`
wQz0qkYghPwW_);c|Q5B(n,e0BW/+WB=#^:=&>9!0Or2~o?Ib@mZbw?>aIK+"taG`15rH+LY}^7diDZ;J`YL@S%So1QBZUdGc4x`Hx?@E;52`!aMsC=1xqIEv
&L1)L=V6?$j%ofFO(oWB0R-E%r!v(q/AarcB*^**$%gUa;#=1i=t<SymOuN@E%&7<6~f0tkch]RN&';break;case'ar':$d='!c0;:bpD9,|?Yd8.2882+s$8J6_C~MF$@I;W~R/!U:N0<1?-tP-ft#=/;wY338P&_[;l!G#`GW&>+<,hmqAv1
ew*rObh<D8%-"(W](K+[a^3M~G^a-hBa4<)4|01-B=s
/A47si"rAOGUMPYV:x9lz
Vln<vmAc[8|m[cl`(F&&0HLp"L@Qv<!T7Z@@mp15SHDw;AQk`d*ZXE}<srb%O&+&nG|2S[+]1=p]=3~l}R*J$a|ou9`G4aSqHYI<TBBY[?qBD^t>Snu$$Q{G}OG9:8xqpe1AN$`Wny.l#n~@9w;stpUc~VDo%P$D^qGEsNzJ297gz&bxz7bc<KC&Ax_yxwzvz,!=6spIcB]w4xZGL5VK,43c|,m[^s.<,n],{mVFKL^WtLSG`]`vssDRohqH3M6y(5Y(5(D%5*3NlEG!WFKkQD*7H#X>6eFNIlnT_Iz:8Z3EV=oauZNqQ"f!h&Axf32UiV{c*QDwn=6"j0><mFK[h]0@z99ZOce[3!b:w
?rY9"[tq2Mf/38}1=Gx1#yy4qqEPUH"aBK{VV.%$kq0!}#Hw
/0q"M-8o<33=2D%Sa<Vb2hI>Nfp_+s:EQ%EC(6T4%)#e/d,[-;4-np_A@0:lPqK_fS#mE}Ph+djJHz2]>(3b"qL)b9av2*P0f|0p4%GH+r3*@A0#>KR:=V_dkckM]5m)1Q8&D(y7-ulOwHA>J0iB4,C&7ax9b^NRqxQetR6x=B9Og:k]"Yd!M{O~x|rOTUT@>*qJa@kghQ41W7@,@7@B@L_$@1V(2o%q!77m%9(>t2M]NEWRZx?
%a_-,/+2`[b63q?9wT&y^N`ZCQ$W)WN}YDu(hjyJO=+wPz3d0nk);,CMKD,N,VQ<!)h[)YZ.BYs19RPNGoCO8<S//e;q;5U>_R_I9/&bKeu[nL8kT:1tbEd:PNdYjue~fpVT,"Nif_Ez3,p4;$nGsjw:U#lF8),dMfT_=(^neTZQthesFv!JuWdq$phx
H^|9^-l!p[%q92n8Eq4&_H>PP4}#,*Dt*^
-Y.[@XaQ*&rT`x)eC-6gu74i"iJ/&lpttaha(x4@fBC[fVD6_OPDd=q?Yp4J&mZ%[)CvM^4nEMG]g*xg1J
I[UP*x@%D;QK~J}+gL(v.R=yG@V]&
>MYbWMzQd7Jov9nv#_,q4KJR*[!g?F2A!jQ8p2U$<iv-TrM<"tB+(3}&$+yxJ*]vHE3HvOo.v:Iu%edu)Bic)kXL)&9$TH^cJ[f
G16VtjZ;>wXh
Z_o?5o93RsWD_3^(a>q8IP.9IoQ%@ie0C^//b^ehNLr@.?E
38v)e>fz%|A+L(=cIoY,$v<MZ%iF>>uOmy6Cvt]N]`g7-tp?6VbThhQTPrxm=0j|Om%h-/QMn2n}*T7t%PEiQAoxH-[9,G!mhQ!^Pwp(Ci;IZQYp:Ky:KJgiur4fk;2m229dxp2GTY?U%&wRNh5?AErqSaoDgViF*B@9O`A1.,dZEIrc5{!Yg=K^vS,s&;,$&R:Qk-e*ChB5$&N%i>dJv|xa2^sP&7"9e3qCv|(|+738fh-~pq=TeaGd-?2A_q9?C`)Ep_?m`
@aWP8YM@2x9,[pQ6CN(0u5fmN?iC,F-}`b)sULSnZf)*(jE#XKP~v/=p6uI
9GEwp?!*dU3)UqE]"`qSeGG:k-`Vw,/t4A31D#nw*2/DKFU/Thh+-HG&3kev`w3Q+6?J5)H`:P+UTYlO/~
sN#8HmFtz3)
r"f>Xd4N~a1x!6!UNaAkEYNF6n16./rPUPz]gkUPU)0[VXa:/5MsEdaBuINxg-I8IiuMK_ya@g
X!/R.r`ydBNP-g!]/q8c]]n6&F9(%P[n:]+kT~f%]7R(o8TB&;Z;wu$o-!#}#}EQn2m<4{%/[?KI[JCav@gI:e#
)&TDXbTuy%D>!"Rd10]hJ

7!cTp9@naL#C9
5;LQPPP:Uku__I9I@4!t&J3fp_t-fHJdV[8%d3)p|:X7/;:2[
%k~4g;!6BMFA21zQ:jE-Z:pGSUFM|-:ut-w%/KKO4JtTx?4H
P^N]NsouU>=@g^%aFHOvSr.%bV9&T!<4#w]kO%8Z@t,7r@9iqn#y;ziMU74*e.t#Hdd8^fc?Oti*B#]u$vX4]pc;.nZ
faX
C(Vf9jdi*>*0-m6.j1qir5W8v/8]e(s,8c!
)ZMXbly/*>=]6~G9?|A+yhvO(z!?L~MTHS=!
]Q"[}[1ADR`Us/0.K<I(]bYg[T
jlVTL[<20XmxJs`iidF6NSF_m25?m)4tq324AG#{.c"&U*IAlil@4ChOi}YuQ@JXwMT,o]^uGH2sGHvz!tW4fUu(-=Oo2C$oh{vW%eXK(Zr/</I*&r**!Qpx[([@>1Lh]=_8P094k.TUq=@l+zG%O)`U.4Bp43e.fZGf^M4+;p]K:v6HHyrJ;7kHEIKB>ZAB!33[tmJNf2RU"Sv8sF=Z"o6#9B.iF;
Bg9$f7f;Y^]7OCpF^gAd;A?F,G[JPnig%gPMr@@czQlj%L-(2c[T}xsy_<5;KZ~6[T&Y(Q"&cj5TCSILDjJQY:F4HQBD7jrVm!8]g@kl{LxV<jtj%`f>|p$<*Ma7G!4o<c[a:vQ(SY{Gsg^LvO!fxVsnT0Pm$Q3r|WA!`^=s|SD^obd/?K9V}gw/;f,)|WNZ;%1<;._ZJ,_ZKi{Me6SH]gbclg+K^
b;},#`=R3Qzd;k@EkUB`w=7;IB)0m_TO4IrMa^`ls>VfQECL!W|K~DDqsR"4rnijzRtidRkWR?DB&MIw?p&KZ:@J+5.oEEYmd9rSKO5eR4fX=2_3`]JGBB|6;auoAJ,e&Eim0m3"]w$_XQ(W-+K5Iht%;`@5}60)^"t`/lD?q&6rjmm%r"Wsrd?l&N,m~sKAmA`E+IIm%dKt&<.l,v=.
XV(Wxq6?/%"G66b
,%++_A&.5d58cMQsC8-|kZVl@-"5nHJKQ"Bf3c@4UwX4?$]mT(cq=;@FY%_x>OKTlJ!D+Th#4l2"K}Ig.z3mB*%!$@)MAL:@[q=o`qR&<EN(;{A96EgEJ]OWLI+*N%F+eo0@!bKz$Drs^%V83R0@4%b%Sw:
>=l,
9QF%5(}2xi|syF/mv=$9}(c";Uv1CcaFrIXf4d:l%ZeranLC2dQ7zX{l&(X`nn%@EDe]PY=,#?stY8:ODCzOv.)N~s,X0B>/I0q!=%hH}o{jWA/^8le0i]=GYXxlg!Wv@0<2dx(5!Y;W_Y~/ZUq$7Zc5,pjo/L<w4NChL"WS-c6ebIChAUPmi41#y;[n7kEJ&y7f&QW!@(ipln!HKI6[rA8:Ru`di$zg!wDImjt/&mli1cS!cqNXBq6_Nb[Dlg1.4@16W=$X38Qp<>w//b8cC?jUWo@DmTB#cN.!tj:/{u[vq,G_!kz:s[(4-ro+2S$l%[?(>,y9{pv-&AGGtmdO?7U:LomJM>R%~DSg-0cFCf`"JRfG+&>
0q%dq=_A2NjCf9u3S&HCYp:>2
*d`YK:0`EY51{m^r]Uky:.2M(^7EXciowRDL=0ycegj`*2=xoQsY[BU@qO8=x8~bdN0@k*I;0@p[?b/w!S:Wo>SRJm[AVJr`@bU&J%KfwQnrEjiW:k/uEWM6bpjt/f"f9nKYO#Eu~aVi4DmWvbC]|]AL%GV1bu]7pC#UN<O&^r?B&tkP3YP;REU_;
b2,@)e7kUK)]FmAWFS]8M*"ijObBo)jSc@&d>-0Rq#(Ovdy`bN2tbtR,[[Km]@VB,t7@Y1LQpXfgSP[
B/&i+LPr(`b<O<$psJ<FKMAmwVXmJfzo3ntYh`/+4JU[cA=
{N@j^e6B*rsQ"s^V4w;^:IR_h%y,8L,c
@)P1lEwMT~1M+Z
:oSH0fQ7N>:TY>]=gtJ1XiYUZ;oMHVlt6PcZ99^!dV:<L@5&i<>3hi)rp?)5bu}:`<Y?M;xARW/ibVa2!:^H`:$/w&r;$e1!a9fOck+FIyN$pCr<9r)eUTX
~E9`]=Xk7V1l2tcr_FiZ5v1P?^s;@?tYN0a_Qqk/zJMxc&s1LedW<&C:;ZVbXxZ]-c#<MspcJr+wwE
e|4U93A5Tmf>d$:KD[O8Xt6=7,O]w;fZ2zpWbxqD(sr_O:d|V4P|6dE%vq1FX!Roq4@{]/`Fx!nk*OW%izBYGyKrqEaz!YN"v_HRTKKrKX
e/JH!FGdZ`C9mv_uIky`+0rx:mAa4+Nvm(EFKiRJ(uQIm;L9Uq(aoQzi0cu2h`U0>">U?uQ]K043L^jO
v^$:&!5w7[cV@MsoG!N9IryK-St.MPs!:nfbYa$$2]0,%<kQO~%OyGS*#QP*idr"d(
/&7@_RpW3(W1RBF<WuxA8ki:(+d<CtVZX+5OznxF#l9xLXWyu&S>o%vO~EH9d+4#"=$nHMI@xrye=i/w@2Dl&

aHVZ7sTU-xE=K_sWb[yfuV`#1.M]eqoMEy>De8.2@eQ2wCQ>GhRDf>YNJ&G8M)GfkVG[vbtqlxOwf<o-kTB4v{x5yTTIHOY"G3u;HH.HZsi$/wWZ5mZBe+5hivLs?]qiu-OC`5NPZjWS*{o^Q7B/*4FFZ75i3(&kH,
4rg.WLoWZ0E:7&yBFh+`u,B4ZU2LARBDR<uue`+i&?..@]$
"<{JAL@N(tEbMG?9&gT"@4lh+#i<A
Ut?9w=,^:iqVc44$J`5`%.]@Q!/(iMMsqQ!
*V<sro)vM#b7|c|iRtKw92*j*G6
0+r;K[
,
AsYQS)7-C_:a4VO73u6!c0*8%D:
,LLSkYH)tvd^XnU",f7/:;ZXXV+F7)&f1RynB-xq*b_he7U|$4&fKR.jd<5L]aiS8*P)c[cdmb"B';break;case'bg':$d='#evF|7/Cv,|?jp}C7D~6|HmaqoE"k>~N5={NygCKs&8<A"*m
,>-d)d!._*qS(
C-9_%{%}61h+f=EasNwbE8xtnx:$cZM20hE+jJY.tam_
%kW^-q,Edy&G$a.jq7Prz7,8cBEkYL7TRPQG?rAuJkZv
)lM)T-B5kL!Gd}0u2iiT_o0rfKBQ?z$1m@nZnC]M^SvV@o*zl8i,FvLIC-83yf&Xv:VxjA8>O!Fb?1t2@~p{!!37=@*=;S&w]@[bE,9k9c4g?cAb/D0R=5t%ZHY,V~-8^Q/k,UYvV]^C(Bp[8?3
l3ny!7tQl>_fLuS6c}*]P*rk2P9Oe$RE3B,|lUSQ;eApIqt%BYt.vXtQh&t&bPnus`ypgiZvBqctc_)lm10!l-s/oev]JHB1X/]2z!_gn]2%,$xk_TKOntFjan7Zp|7e"GuomI
FEO=H:7^tAE4!hKv<VSjtr0]|t;^)=i"%4I$xj]ynDP/@.@Fk;|])EJ,RC(4u2w3T_)x>,2u(EAQ~kSiz2h.[0NQpeQAs$,
B0`8TS>s>I_#PfP0p7Eh,K]D<10tg..3&-nFVC?y`J=O/Q#C<tV
tD"dP-YYda*?Q*VN
u"S*t`BN&0uRMJ?pvfxwojH!K5mLM-_DCFOBwDorbHX8h]Mkh%)9,I@nP{j8m>SZm?7B5X4~i>L2l_:/NFHY]@JXpfacTPjs+m
5DWN/N{/_)CvW9A]TtW$r<c][fqS{;H,y7W#xHB#bXk?&txJ2SJ#mPXU<WU$|`oYC"@2tZHL>d0_&N?sV(Fl<e|[kv*Q,3^"C-Yr*F@4,UwexjR8}szve
A@g4z$J^.rbK1"VfB9HhgmW5T.0Z3%i4}R4Gb&WpvZTf<5&59stM_M1H?V#w6jss3kL
@*(nEwx:gMb/>wQ+N7A%vQv]RW%;Xb%Q0c7GX
JB|ERj7,w8}qZ<nkPt?]C_H"Ah"bZgl+@1WGJX-Skpx=$XY2T`(Fx$OltsX-MOXo/KBjZo#<q@&9F7i%n<VZ1ZX@xtRBa5#l~)~)F/fVl)8=O=B4oz#TzkP]:aWxrafr)X[1DV*-k91:-aW2]qhXz$0y{=jEJ`gRa+-36Unqp3zaQSP"xRQG
Y$2sRjBY"9gGQ(v1(}n<9)HLeI6jG/f]M)Ba-Y5+.TU^@VM,y1;`Ujf:jI1^,|KB;ta
3@]a4L@);6HHKkO3/i4i%"(S
GEt,mG[)GMiU8]}HvNW!<3RsCY+$!4gIsH&RX/,`=-DC8.{0zoyC"0>#G6Zx+1lH{VYD/iX[=%b_DG}h=Y5oTI5q2bVh@^bsvEP5mYB&(in+a*4b+m&P(J$m#O3wsoO@J5ZVPk;8u.h03::U/n9#8Hzxs#3ree$W*U_Cp[Ok=&:_&OB^CI*wx*q$Z5XJ]H,?-f6et?)k/1Sc!iu60qaOJ7TDMh{)p!?6n&U&4J{V+h^T+5[*q:/mAu@cQ%3=LKs&SV,ow8Gr+T1WGv+iitYI-vg>X;2)yu#DzOMk%;U0x)I%USQ+}@E2`GYM[2flY9p%3A9y%#Yq1lxYkiAZC0;,hKv)gT
ifuF1Ek$lci<L=)&>67mh13"h#%t`c(!)EPF.H#iB<m/Q|1vJnQGQ|xO7I^"ODZ;p{)Y2HI2S{l|yGAOArh/
l#z.
(&P%P:Oc`bFFu#KWdpj_8@oKnA.S/7hQD[b|?;A+g|_3@KgjSia[o_?;7qvJ9m(Qr604-Pndq6Vekqh,w7Kj>q^/HYsMg?HU9`O5ILZZ#!3T[p&7iGX#%cAO$B%k3^7yk6H7;8Oh^3h4_;y)O-(guA3e`(S@,tT8ND/W
S?5uz2-ZL1wSm%SXQd?$d9k3oMFo~_@m*=OP*UpZYXw60D[7s-aG>GT#65L
1D?s,hLbTRD`C<5]pXB%")7<s[6l}AGv#@]<Q,qwGDd4Jd$dbSB%$L>=s$e8`_2q56qjXx&mkh#cg.cYeh"&ukr)%gc5?_J@u-ugcWmU|&WtXQ3][2w9$W,L@&8P
>L>Rq@4dfyb"^gmu
l-sJc7*k@hoP!."pe/nTCli;n:cqRC8i/#
1]tiFSdTbc*ioAh$7n>^Q{Du)dB|gL(J,;&8wYZwf(_he$@:,oi|MIR*m)6:hJ_anZ
`kVA?4@"}aKj,F/qp;YEoN-+6YM.S2![K5p<-8F.XO;Gr7-h@F:l~1("4.d68_U(zi;B&p/>~djl.@e5M<!`St-BlMz`JxE.wB&BQTo$c;&n738oP!O%n8~OZ;*(LT~1nHf;]kZI,v//V4E5O]}UTY^4jDh%|)g<Fb{sxAWBsrzE@6u+0wk?;bj`M1]2CCq!#A.0_kjjm;a#l=C;2R+[[f~oZ9}D/f71yQ$q?FR
Zd"MyN`+8Z+[#.!<>gMO!#.f2tDN:Aph5w^ieXMeRK[T2f/+Jbp7mA^EV;B4zV;)dn*^pZRI&+JRYHj
aBN[;&>LUSeDX?E3WO>#WNG@$+Tis.ue"Yw*AU^C8qtNp(Xek"%c3et-4=mD.vFf#pq/!!N<?dJ9QngJ4>Yamd10d>]jN`
F)`*YDm:76VStBWep[GCAm"50Ql$@unk->j!d/Zsn]kP/I$1ejIF$^!?:hRPuFME_],IC7Y4=^cSvI*BWq3KJ>0L[{fr*L8l8#d$
F/aU?T;[Z+KDPt9!I7a+Io3O67A5<g:C}%c<LuZl:_`qhOcfmly!p-f0_Vug<0MN9w#G,GS_]6mOAH~Q[Tc9:GWOyNvg+="ls2+n?U
f7n]dnIJSfIcNu(7xhff%^X-SEW
bGq8YSs]Fc5#j+6`3p;WC`dc;[e0`sC<D~oJH$"1CH,B#C6jfT%*)Wr?5*hH3n=V1e:(vF%}]}
0(:4$sj)!]Z,?qu#BjfW@yT1FAFSDk^x#ZIFgpoVqe`^8D"0|h
Td#5tL6;hE[_^-=zj{lm9nf1L
lp=B;q0B!y+Z.N%6/~a$?jY89t(fs"A"k]r-F@64bAZFIi9a3=;bC%qY9u(=ZPq|rZiXt2Md9srmPh_[.l:c<h"ICWRfB65f?5GuMN7JJrOyH]!$:0!+few
wzu~AuHlW>yr,gBW.7DEecm%!TN
Ut;e
/ZUObgmhCG[d`Rc7Mdeaw7SIP0G>g!f[y<vU
sscT&Aah!BkgjGT(JzWxg+D-^T@cF%NB3^nr/o*j,k=_Zq,"b$E_FOnbJn<N@T"B0-ZR(HeQt@[!8jk{xP5OQOS[xETFy!_ORfB8;t=+N~/tjnObkRykDCnR5q!SbJy5Ith"R]RYOa,a6NyAn2,:Hwky^AHj?9S|RYI;*;oUbr^zCmMnRa`wt&<;*Lf~-6$t?FO4OhkO`{`HWipnEnWmVs29Swxa?+x>+,@~.V<~vM3Si9`lLo,"%G`$q[NpG]NqqcT2;S$u5:R;hlQAje"lI;an@:0O6v9}oo&hoL)Hn`H:,3NTqouui7S)?4=OCX;wg414!yh>)>e<9
;N+U7hf/Xev^Yo/nVP0UXZw;&4.zGsgf]=yKd9d9sh(HrV).F(XOse,
-cdFLkp"LZIm`+B1<l6*Eq;?]~T/e$fwZ,&:cEx1N!Z
ME"gyt3;yahQQh`re)DTr.%jHWt%!`;QV!tA;-/kPTuf#iPm5G;zBj:X"XY;byr%)34
7c;dNN3vcv`<1h.tN2kR+UN<`~9&]tg
#&AXP
<!)GGyNl)^$UP"K|4ro1YWI1P:@gK_0Q9^kAl/<E2h,fY25x*a]zZ}N8[].<O[v/>0vywNjn(.]-+A_QC4<h7K,/y6ji=sLwpu!)7#SuCE,LH0?$SVf?myk05</sA`ha*lslT~AQTt+}Wg2it(MiKHY+vN2qLYe_RwOBbj`Y#N((DtasDRn6VoKPdQ522d^k=:@-S-JQjT8S(#&GO*u)AL2x6<k]T0my?U6LpWfzU*B^><c@S]iqMDY/h]0)%kb.t7Mc)"=@)rV,k=7|xay;2,7h-SF!Ij]v.V8CDUN8(EEOJuNmdu6:q:T"a#-yL+=-+sbZ5>7JK&47S`&CgAU))8YNCKU^]Gt-G[!4k:_F^NO4cIFV?"DJoqIJC)&tX~hsF=sUe)a
]^_x+7EY[1EasgS`K47PA2mrbFLij$d^madI>n1Cuc/@JOU4X4BAmumg^wsE(MwjmM7$w0@!5r$#3.nRs_#6S%H53$)Y7H;C2a8o4LOy<8ZJG2V#(4
SYIH<naFv>*[ruyX4mz4ja.2!=^.
Th$z.C
o>DuzGVBREtIu<oC#+?4c*@pLSQYFoqm8>2(TofFEeJmWj=%vB6ldvQgpq%e1$2^rrSH,>lrPKEl0[2P
bzg54:=
hkECCDupY@7
EJ;(c^X1cQs($]3Bx!w6pF5F8ZKy5pvZ25i&S[$sh8dVOoF3)hQ
4E5drQ%
vfs8-L(A?L+.29XN&8t/S.hB1J2!b/q+]W67XAeh]Nv4$Ew%j2=<vi$;%,jU+U::[CZ}sqa3RT_$la+iU/a)IVgs8&y]`;IT`?HrLYnYrT!%K|X~rGhXQKsOlIO?=<`t&.G1PDR7QFQ44QLT92aCU1,:<)UjAGkyp-ne6U@R2E@_:AD:TVt>v|Yrj5EUi8WI[^fd8z#qO[/9MiGRqS2d`uZq,[#&SJhA+22:`8mj:u@Fe8YWALZ/&g!YW2JUX#9DbPazS*cCvT?x!70m(Ta|e73I)bgL0GM{rn
!dv=fZj)L5GBiaG*#0X"kmi0Nu_8cd^Dn9T[
DR936$e:Hmbc3b6)Kh`WE[Eq3yN5#@gQY,bVR3iuPK>K3i,/KdWZGF93H5"_y9=s#hXciDm`tIZHN4ei5gqD[+I+(]s}X^VQT.=J^.*,prx~b"v3`7wYK_BynQQhp):cf@=wSUtyXDDuA85q1polkxH71_mrxOiil^010yhrwidweE[{;s/GiMj[!Y&I?di1Iuu94CXpy07n!>*YjpS8.67j)"@/wxC|+3YjXcLPTtR,9tHGwL]&F#vwILdiP4E_.*qm2yIR(8o:i[XG:?jE
K_t@kL@CWL@BFvGG~;crIFQ50A-n?X~m/VuT0E(4Pnrb=fPuN/74q`o2$a^34T*H<2_n?&zqp//"?Y}yvCAXuE*]Drw&3s8"QH*cxQvhz09*+<dm&C9Xsd6ZN^|
?e
-5v"-b,F&I2qOO.|M=jFW"ox>BsVf4s{5]y<B#5raMBG[*w@<BG!M+W"Ld#{6_9;ULdg-,:9R/^NaZn%20FQp(guy8tq;sw8/8-jNLqPt]<>=8x.jwijG&m@kC@V_YHKy$x?pkr+lqmGt0:ybQ?Yks7Pr9qesy3S8S)ki:[f=e_kx,!~aXMr-a';break;case'bn':$d='"hWVkaMDG)E&/"HeS,^LKDToD*
2^BcN85e;GVENH<>"}3MN;qq"2NZZU8d=aNiBH)3/=Rk6R6:]4#=Y0#`R/E].y/$BaT^Mos9isHj5zZlb9@&
m^Ql>_;)TJHB-8QXEZE1/tei#q|X]Y@c8dRmJj8+KRL&mJe",yAR~eXR~wN?XrYpkx!,2And_.wu@y;oTC1c$$gDv@g=j*K_q&2G-()E9$i"2BzEhL@){8#oVh6_GM3P*iP=yeFBySGq,V."?(sfhC+Z*)WVx.4Z*@@2V?I9C7_i7f|Hh;"yLbtCE+~e3cfed&dg>$x10MVEl7N@wC$y-HCtCjh7tq.P)PO[tJM/:2AdchS<Kb
Kl$%m[3Tn}yv).
uG-w}KKn;s*A<BY,GiHpFmG295)yDJxbaw`?NtUq+`znMN$X>cPD7xUxzw+vYJYSPM3#7XP8yvjb#BsLF96^[2D"e#BYA;F
QJ(8o:`)pM^3b&##FUi$URB2YLG96nvw$4JW>E)![*udCdDK19!]u3a=#maG,?$VQJ40,q!=}3"@`.BJZ$
0eU)+wW?<zp-NyP,%uK@RrV_x?>L%aKwPhNjo,]D>IFQubwl:?$:UD[*j3$tClq86[/1k%>y@Bd7i^QZ44Q#u>Z~uc0/7A/K,>+@jDW3.gRisULmHd:IT80OSD&K0=wXkDhOK=
[F%X&WR8&uMQMRh
4j~w2$id/Sm^Y",-iBr7hAUn~^x$APBv&R*>z
rDr-#J{G+hdrJu[Nzak9k:>jdbUN<QV`y"!Ng,$/1
kuP[4#}j}pV#A:~$3a)&kQ7o`>XH(yPr*^~]rw}l}L}ojxk!X?"aV<K.Abai&Q7rsN;2*q4ib
pwA_i77bwI!m3;;q)-z%V!|424(=b3luOES17QBKoIM%~s),;6j^Gq~sp^EJJ+E&^.m;4-1(H[Y^cs^C?TB]%QlUF6J/}.zu>8n@zFHYS(-A`xUP(H9Z<GU9]iE
C9b]#3sfRQ]uH=}1S%P@KjifCY-hYi"Qdp?qAM^<i_[m8BlT9G4ZRaf>*q.tK8=pO64[17p7!XoS
+awq_1T"
_yx]^:G3/71h@MXK,-#Lpo3%-jF/U`oDmXwKd.{TpX]#;<AA7Ix%`rc!$OCS=v)-w@H06<k%gw10b-24.rsQu&-W.cW(;0^AkFOYh_{R`7+b.2Ix:3H"E_GGx12;
$?QKnY,FB{#|;@vfhlpZ7n=243q>dGq)HKSVk<%m?;nL@2^uX.Xy28(*RbR{d3[@B(.)Iw7{JC;u2C:GL-3c@OC@C
[g"Kh|&@X(3*LP:3t@dt
&Iav0Yr3fCUGd0s;Dk5?wP4=;1.O8b5$5AZklZra0eMskjv(!vHE>/`3_E/9q_vKgLq&V/Y0a#s-<QC)oMC-g/*2e7lL1*P@FO8O[sk+WvA0wGk>*2vv8)B&#u7qhQmWWoYcVL(4?v_`aU1b^/P-F`N+04a?65Iu.
r;D`x5Qlj:@NA$bMzc->!b0VQfT""1yd!50QQd3P~_p[^g2U|H-f|+RlHv/Q8%;j-5pD{rpLFs>r[1Xs;>&O"q^2ll&*aG!"L_ZN1BU?VT*_RFi_CJwa*k5JT!>$710H.,>O{%b5$x]oM`aPC)?hR$Hr~3W0KLdnRwUSE9b1L8zRR
<:>Jd$J<;xV!(Z5NHMo2cc_#s!3Vb]L6$-6Ak^(>jS%0jJ5)tA@SQ4[mEKG_]EHpDs=
N^1J7kK]01=:ydDy+"Qa$)enmJ|g(&PR*A"N}iDvE!DaM.gAw
.A:8[(}mAd]8_G+36:.+,xh5j)Dg1`44j7j<(=fEHt-l"mLae)<@GQ#"C@_"dZs
59KIuhRa<nRPUCS3$9FGgL>.1_R@>"FBJ;~XfDCHGK[Mpb_`L@}C.7XQe@]-+j;$a@&uCcF+,RL+ClX?~#p-b4vX`aF0z]W^<2YFVtPM`5U/:@Achmn3FZ[@PG|*InD=oyzEnVEEan05QNdF1+|(uq
*f>,rUY]
Sf)_QP["+eEAY&jq|gy,~I}.y
<H)>Dc
_e!qY?7VA6pZ1BNRlm$PrciWSys~FGVR23"0*W9ed6Cn:uWcml<U51yB^c&qkq8_PI`*SYB%6]B/SAkm><nu!onpx&z":s^u0Del`=Z7joUEQ.IGnS9g1_m{hM9:bR>vZFIHSMZS(t`{.FK@5pJ:(#Nz_N@JEX^`RzsUPiLOY(=$y#EcQ7:T*H@Jv}B%h2rS1iEskOOVu4i6!oL}NgTz(1VM${B`nk45_?
P/2#RZ?J!hi)TAN5N/J,4H?[Jn&:Yx8S,yf4}ne;dGviZkj!q;a-4[Hk],fi(U)+^&(rbvFC]eMr&W9t;L8:=L"G>o0<`*(N{^`SjOVDPCQ>F3coZ;?-G
D&?CX=.vzN/r*qB<B;@msPEhu&
v&=#Gn1ZdG_VVG0Zv.fb;4TK91(G=3/-w?Lb)iQSbJ#tlp5@
sm^C*50I?y0SlHo=A2D$r%GGVHsn6w;iL=":}9R+pPmp2&c?bd|.CU_<qO[&|QPBq-8#&FYYMNM#[RO9ou8]e6!Wj.)0|WIj83|8+j{
Wa/ge%LIADDh*uve,Az7#d%`a`tpfU*r9;)vaKxHMg^Tc](_FbV8>Ta19[!HzSco`G<T7<bC`dXCzx7r[Kt1spobBbA#]BMNVNnX6(|+D-I#|fQoTZLs"Tu3G;<()O7PD)H59^r
<5b+id/TWE$>92y3s2(>-h.Os@Au6>wAJ8mp|?c8qMHrSjqteo*XraPEu)6^Lj%`0hUY1wDH6.[]4R!n:`UkJ0voRKv
SOD2016"NbJObl[uwr]7_-8y"Z-mGH,DE(46s$3Hlc
0y__F<5Gj=/2[?L;K6YDX{$WL;IuG~L-xG9)2}VQ)QUnT}YDxR@%fffH
rSy+<M-K2f"vskaZ
4MX"*xW_oITk4mig73#YlAlv)lk9_u5rN%omqtQ>I69>B$ZoRj
".=EdC7P0AnB0KmL|@HJ9t_M8Z/+M>Yi[CEGWjwd?2OI)*EEm
4rEt.Qzqb753Vnkb2LSOVq:1vRmWWy:qpS]ljif<OSrH9-#,;iwq0xwe1;3o(pqcWJW2P:
%DrI<j#K2DCo4vuthmz!.S"vgeJGL5X}QWs&@l(OLy"Qczpg){E"XJG`nL#"yGVYe9;;i<X.%j_`e">2J<dQwNVR_SJj:%7yRO5d;CKJk&6Dg,pXZBDss%#aVa2nIYk]Vf%j2Qw/^b$I?@@~RFsIY_N+#3oU=b?{5JB(kec$bBmcr@oy(fKhw+M&k/5]`R0WbT<L75>g5"[yvE>Yor90p:c/VH+{VJ+
c7VV(.])a
7ev-a"95B5iCl%hgg#aTe[b-6vWt"GZ~:}hLXp5,JtC_+4PDJ%qwCxN$aHr9j^5:p1>Z_cq:?$WQjxp~syAX663iS>ObedChvW=Uuko/_B`-+%ru(VV(qWUHNj]aE7ImdPC(CtoOx|@m9ifGoYD99kE?G_G-o+8j>{Oha%`+X0^$obF<mW15G2QXEm95DVI&U5rVQ3#PRZZcZY0yY3q}wll{K2NIAyA+Y12ZT_iX,y$B#YkP34Ob`m*FnO"e.$rKFg>:v
`aPc0dje`VRIT*"A%A_}Dafu#%Q,5WE~6I)0jw?m"A5uXHH`r+0Z<,(Mem8}+{5xbQ)ivI?h@51kR=E@ZWAh1
#_#(u3WhCPcK0A_}shx<9e
~iB4[R^_AqoGG;!W+s`KHK_P2F)x2q4xkYzd&]-XyK4*3ZJIeY.%=^[0CK@]<,77sK&WC`YZjics.Vr"hAi,i`{^|I[QdkTWL@K$,=mrRYtYU+YmdqcPBE7]2,<D#v^_)
$02U)U{e;w]2dJ_H]GfLn.~
`KzOj5J5tGqOIcO)G(#o5EZ*/VT=uG_2=
>;Sda3N<b5q(q%6H,lt/Dy7wu;54VV,X&6fp&a$Bd>0483E0;b%LN<-*Gkk,Y%ohf0=#H*/lk/S@9!<&sOz-ccmczNDA94bF`Qs2+>
xg/^p3+&#aGkr*93dI/FD5/g>0r"((x$">Wg"iI=OVMt-$:m8)p$K0>-)J*TNt`UY,rt/6sD0Mh@tmYRH]*X2`;W2iv"ZcW~([mu6maX[r(7vq?"?uCPs*H54mJ=HZ:]^mYK9";?e]2<R+?`TmTly2Pc3O@$ECYviAI1utA%iatHNu?yv:>Yy-!(>}cSDsf=.I?A+.>=UnT1f6an*X?nbSIa&xA^*,Xfs>QtI&SWAQ$<Y*FjxK?::w_w(EEUhU1E?FxQ)an&%ao)fXJ]hZ9_5aOSGX,.w&4iLB5aXDNOZgP#A(V"9q9jg+&&NXT"1b7U1[cf.j23$F,n8c=ED_5f$)ZaIu>=&lIhDi/DD@%XZ0<X2TpF[#c:0+txZNraE=NuFf[{WyZ
Y0X13$dDTN8Xhfc:;jC
Bx2Chq<3_uW8KavN"~OkIjTPYab]/V>*#,J;_4R>l}L@]2B{$0#a<>1YRjs=Y
t^G3l*Ixl]?J>Kg#_Yn[)?bFX+o?SDbxDfoRV[[vGw9IX]m#Ub&n_sXpE<>;0RO6Z]LqZ9$xAQ!x_VUJ8eYoE%sg7#%4Rj(j[@>mDcQ_e%lX/+^864,!)wbuucQz1+rOK-+HK=JNGH$9G<KcQ|YkW3_bb<hdxC`83&Iv%v*o]IU8qZ<]lc_C%h3{qRQ5f#GMFC`n:5gW=u4(cAH!Z:`^9H/i.J7{l>C@>@fv7v.Y>0=5r;*$CcTQ+CgC
v9Q,o90L5s&YLGkCA18+kP~s7A:2ZU|k|=tq>Q2Yh1YH}FQPMao_}vVSIP}x8F1w<97647RHx-_tUqXo,[%#sKPuu0}=n7vvjE[9*):IA`z.p!4d^wJnu#]FRqvIIr+(ze
K#?l!_Nky#in:~g|L-)x5o8Qwr#=ycz!9$*~9c
Sb`Z{.Rbspr%*t!^&U>WX&6__^DHhrX?FiVQeVBT;9{R7i8KJqRRr:}HG)iq(<e*4g71GWaUn?US*;a/1(Ww/Z&8}3CA&p07W.!^7im5y")fmNj+7.vDgtiZ3UQ1};H!(wmZ-a1O2<&7p:iga&KB^u-.Z4xU&0**H&^Yxo{2vXU+Cw8W+UY8gtS.W@0u;nwQu94b4qMQnV1@66Hfx9+!v<_bzajPf@CFX0y(gX5hH9$C.3InVp&MH$yYzy!+g`m!>M}n4=UT=)UuT?]9`AE_3.dtBVW?X))2>MMJG%:,=4Zf3?V,ifdUZ>?+`Lw4Uui5!b=kVat
Og4+?5;k^SS[!Xh($tebEhd-75k)][%"I-)@pZr<)GPiZ<sPmiZ+!8lP0^{fY"b]y`)0u9S-.Tmuu,dodCL?_Yc-mTk61s.[.@*Xj9)c2Y@MV,4;<oCTJXD`O?HmqTZS4V9*J^!KeV
/qBtExyz]I.[H",P]2Pw$VOAIX(SR$mCO6/%h#AE!-WUS7bF=eA+z&3CIF]6%Fw}@;wD';break;case'bs':$d='-Zu@qaMD9*70MN.%A-mEWiTUNO5:~GAYr-gI?r>L**i`s7/IF!MhP)=$j#3c}Sstxb>P1i`2D9f.p].l>:o:,N!N=DvW>kH](kGx;t3ddWdPLnc0VB664xL;gm<U6+xe;yk#H;:cDn(=yl-g3kOt2XPPm1rD#]Y?v:4qZ@Q*yflpK>EM8;XR@i33ar"HkfHnl#g/2?f2xtlgUp&Kl]o:~096>ys>b=QkA4gGhI3/?-x?gUVB_lOmQkQyMt%MAV3ByP#7li9oHb7UMoaaN1riJpDHi!8#kAk7/lKfa-]]JqPR+Y<[kCTr`tOV/t(7W.VgZI8<UZb>r+|+f3<ERnrJ7_.3|DKE
fFTUjf*`0)%6;fq~asg&hgX*kAbc%l3tlPiruk"WsV?GW-X?IkO.+fvkER[-[iGh(>$k?KAgW7b{1Iq]
IKg@jSma($(3"q-a>q4m@[/i7&QG^^=guL9f8D>=/b}:ot~;ig!_HRo4IQTWL#z1QPXJbm8qmy"K1^@p-9LgN9qi,*;_i#_xxBPGj54#"%~]9q!;uG^@~sn,sUwe#]CsTL51nMYXI9%(eQ;lJFP?xTh?LwyAFWlw+4umU+I;OW;WAGwwT;c[aNP/KGo(KD)a2k4ZG[qT6`w4enonR,OAi?7E+R5"T+}(/Dzw7QEVgx"q2n(b4c}uSL)J}>
/gi%tL-j_U%PeDji$0>/wRr9Coa{0jq,
g"8?PgV99equ-_(G#idRtX:J}
mk}qP+@`?s&y
O}`s(G<5wZhPsoG}PHrxk(i5JaonM:-&e00EE7w~e6,#PUI,Dw
Kz!;3g:j}*sWFGdjxJhf}nB^349op0c[[No]Q_3a0N#7|N#40Iu.ngj<&$CBCYTazy`,*H|Fz[V2ANUW-*12h
tUWsap~%
y20Jh/4}]zgoGO7,bWg0,_"EM:#d!)VnW?Iyt1%V2~>)L!7G_!dAyO/w@y
E2uNA$4A
nJdakl[LaFfVIRpxi3B?R>n~=>6y28kP)ew45I$+rPT+FKB6q!c#_g1YGMm0l@x/n;@mY&o>jmUtbwj#bI9jO9KTsy@:-k^.6bI<AITZqQ-?nFAj#w3"q<0?Vd99;nu[6t-x)GLa-Oh(>R=rSgC<mM^B+sR+5^.Z.SI~,WdI*ln1VPhVs=IPbH3}c1k/1^lG1auC"P-Zy&d`!L"O,B34L~w?1?m0_&C{WsB5y.Qqelf`QKTuK[k
D]b:n=J}4k.F=>,XrJ",BOmO$)){97w$q!g]q3nb6YgM,aS<gziUkXR?ILgOI1%qQ5UcaSK,.XgPytki0/yB`wnaZ`2QZ3H}0?(!E~b)&I"rU)H0WQidt/HNY>vsE@3L]n]yEGXSQ&&KWcr>w1qkngfb4tBxZEI)[X7r<"N78sgtHPl|mP/?v
gvEF*2`S@8q,6%Dqx4jK6t+vl%Epk.u@Wnu`eb:.J8cTPUR`r3$RV@q<1_x*tCh&lib|rb:nlepI0awOg*Rd*+t%_
2S6_q)<e;=o,<<xb9%jq/GH|T~&?KshG[u!raG+K6GN?4q3f67FE88>9_UmMZc^pHP+]JQtQ5WY8&xZxB.F[@3_~aT/:w*@<eOskcBeH%5?%!tduH~ack+]yZ>mrb!nR<LLtFfDxvurps62wUf
7mRK4W.SQf<l,:k.EE5gn`L93c4HVf$+QV^RFklq0Gyx3#I>{)rDvCAAc"(r.!Wmy^xK3WOrvJbu<DGK~_/Nv=TgE:<mv2*"9&CRwuI@/C.;`<II7iM4^0yA-Gv=I-SkPSP4fPqb"=h6RI4:
QBte%+VH=D`Z[Wm3JF:=P<"F&Q"e7M7x;i#2Pm7Py{U4jYz)Q.h4NG<">3I58q9KQNR0nGG:l~ji!CQKG;n2RtbOCcA{:hU=4fXO``V2fQ_n=v;wD$lob~j{>+YF=]L>pAx(:qpnOehbSa2}:{Q(#g%`4n+9ME^vA_p/jFU3<8c+.E]HTv0`h(",DDRh#A4ZBHD+#JTAS}@,,04RMIu$_Wib"*&(?!/|$0[Q$D_D$mrukxrSYH/n/$"
dyI4YR2*<
yd#}"s
.kf0Wqo8Ha#_JG:]@?b7q]5*dTQ1cY
A1bImu?H#D?x[$i/<xH3!M"e()E(X&o15NQh/em-Yl^YG4I;)(Bb1/3aF6%=rSvt$`!/c#M8<0-4AE?{-s&$cb/H(w
@V"4&i[:8VF^zQv3wAE?Y1:Vg:wddM%p#HwO3Cw"4YJ7?vOR<=pU^8QKZ]DhtYOLsp&-Xs^2LA|ppdDntS_9qF[=~?J]-htL}Y2T]o$-wwc5x?g<96I`n)BmLANgQ>e)6X@<4?2f"`x[*H:M|%b
c5F,0f.:nhg"5:
;hR.-GAoSgN/B0;UVowd@L/NOE)I0~vT(mFdZjxLnVlV*ez"6VLaBtE?:J;W^hAt!iyp+=Tpr:N]#t=MRMu<*OesL#.aM;027z9ACF8z]Kk
09afgzX]9CN0XT(nc{mY55L3x#RdLxWS,kydy@l?g~r
+MBj<7j6tkGP:ZAE(</C>d:m_7an^"3lBr?Q/Y_Eo`M<7KvH.|0r";(,gUtCAi+8?m)P<>VG
%HNoiq<Wsc]F-LKBc<!rmIn!yJWTzgO5bqdZcT>:f`zS_[T_>*D$bR)("tIUqvJ?$`_r:/U,#.M?Y[f?v"
V2sRbvt>#[qLil.f%Qr,ym;9fef@%moT*^`Iu:R
]J4p*a%@u}p(o}U7+u1%F71PKMK~:lZ`!Z$yoLin4LYzN--dX,HZ@^4DPv6z`"Gz7(z!AZq
,LDg&5H>GJD@Krx_"l#$87;/`p.&rGP%k^bhOD:|3j(NGr=g"o.#tOT}B^6Jmw8LD:7ZDFY^trb;oA"z
N^36_D(ChCTD6;kLbOyWJb~%ajS_=R`Qz&khFk~C57hq+7f&(%U-Uu_+V?-Q}ad;5_M-FXi0qU+X.-7vw#hE(+!&tLfNs)?MC7V6),97-1=_?kGP7XeC+tnFbLoC
AjR5Q91RW#"N6"n,AiG4asRMBb#Z2m
u=t"2Ya$JRvp~9-2)cQU:]tiq`~#{tddI!#45(*ah]4@<-UDbKB&)-bvKhh<)K%7+WfkEA.dvdy4W(tT}%_Ya1reo3@nk#je0KeydyCl,Tx<"i3,sm+NK%&eUD%*&nB^Rd4T2!jSdv^52)2J=y!w>QzIn%q&{/*!ioz)G4)6%MM[e"U;7I(-9`Ql>w(=dV>H
K=oULX]~m|?nQOuX:s_,9`!/vP__:B$vL_E8kZ)JQ4ui+$2v.p<:-lu6pn-8?eb"2xZ-%/a`j"g+pX@
+uICVCs/%c"S63;]5{A?dBC>a+ZWYSlZrljnrZgbo$I`<"5l[E>HGc&==ALTk@$UFowpw!.jf1y4Wa4!pp?!
)=l)Un93Murpf8~4b`Zs^On*XSrcqM(Uatxm.^>mv:G?%;Vhog#xlCzXVedP83aVcqVyuUBs@A0]pY9I<n%:+[6&Psp?nT6QXHQ]u@^1/aU[.v<p80O%).qC=+j:@<XCpk/hEkA.7+dHk/M%YQ:pQFZny0ED}0{w3u_W4h
0b#2bXhvb=%)+~k41=n^v3,bSf^/G|G:#1c$!Hw-fBJ;hbS~frC5Te-Sx:wRKuS2E)+9*10($NytyCBc&LYwZ+m>9>ZJu@j}Z|`cCF<iR++F-?CXG}Z~]j?,uHRMFA
u[%)DFAnBpeoU-*W_heEzKU9YKb"?iY+1t/H?=N!C9sb4to*SVhlQ/l:n&{hOmkgnu"_hgj8fJX(0Mt25w,`KQC.nHHUcWb(/U616Itf9I,96?YuRj)Ml94DPWU[yC;9WZjc2&u$_LZ#x
4%l=wP*m14pg<Z55>F5diV`168oH#(|%oyrS5
N55vdup/:")tk4]+!1(,@?K9G$8d/H3KU!_T:uY+Y&YL
B%Pm-RJNbT=/3a5ee,Yf((vAe*"HrXBs[Xh%fj8GHtD]%~Pu3Ge=<CHr)o4pX2DO
L-f2JdD-G2*ax_Z]MS5w
:PHnW|0?fL?Md-yzfX`b4MaQe%#iAPm^K*3NDD=B5LwX[x6ugFV0932^dlqd&F/7C,-*89DCSd
<1_L1)yf}:qa@Kr+~h*$+kM,`KSxZy>RtpX1q.^@zqbR.ASm7a4.HNgv|!Qx.#w,~I!:y>@`J6/K?PKZv<G&J!MpC
=)lh4FdZtd;)]nDXK/R;x+`=?+U4%%FNBx3=Ue>?Uu%b0-`8+kORx8iWM!?Jp@5.nc%sE+5CymU9.U8@n9yJ~Us-?MWUE;,oy;Y
j-1Zz6GO{y7fxK_1]n<<V?usT6}Gp.,%^B<KZvd_-2r0kC3imt?ERvJ4[CqlCMhT{u<e(kkCB(ECckAOGy=/h
h;f"s
NnABAsC((!swM>@CH7ogHST%|Ot-_k@-_,A23:zW{IWO%qNhv
KK<]zjK$fRoZC90#_J6$mwFC!GOxUjHkD(&,d=DZYFZWkVEW)MCo4i
8pUV$bjyE]5UoJD&jk75V,L7&8eQF?+c;SWTiKe^7dkMy}D
<8wQB-mF=n$:>px
-#q[${az!`=FlJd5MfrTx@D0$3y><e%nE{%@H/v;,_:zN3%RjL7BrFt}F+,9yg2=M!kTP}KJDC:
uKZZ>tqlb"
Gh
&?=V>=Z(a4!7=`N<)qnG:]1bGr(5-d#HC(Q/nFiR:2:!#A&"v"[(61?%W#nuUU6/4$@L>EBi*BDoaL^"pcll=S]~xPLyT"$;s]E+WQ<n<taSO=uQKAyRAmm_=Pxsd(';break;case'ca':$d=',]^ATbPD)@80
Y,"8UWZ`pk]u428:k-oi`f]`R<@C3W9XrmRjp
fk]OZ"+YN]tbb/L,-t-:#f<#kLTDy+q^RNp>5cS]>TQ:S#Vq/6XkHKJgyd&/jrJ(%lrMppbJ^P%
Ko?H!(W5i|!/XGZ:L
ox)rv[v7X
G<kN>,mFt<uKKQcPbL).X`9Ka*jSGv6pU0l4p3?A4+_W_!F/2}n3Q@;8i9pWR%]U_T)=?dH*eH;Vg]e9<_`7.+_UqZXfr}]t
e$OBF)*eV/f1l-`rHJiM[xLS:nHnuo$4xt5>PV/x
u9K|&iJwgM>Mi%SO:uq"Hs^@Quyl:(=k>nJ4[
O
w2o}Xz:|<wIF9:<#x#h~;~n0g<8~*|38EZ<B._!"HhRZWa
.i-
;X.[=gdg)A3[VcZE^24>Dc}D!].ZK:Y9][:DAQyV);-QkuToTF54Tj,_IW:?"r])8LbnNQ&_8rP;
&+y0Q@_M>i`1r:VV&zyl.{2xy3D55e`TYE1*
:ESqeo`Lv9nVy<Z`wxxUoXV?#]|!PsVUT0Mayo:L0EP
fNqTMK~i~
jkA^wpE0qb~SDje?5[^Gy#Et6)">(ao]W+MUI0ujrHD`R?vaihRv<F@fmdK#nFdO~E!C?LD^.!3GoSKLp3E<ZkZ.X)<]"<&oph^VVw+pr/2K`oebS!
+m&kfk)|x|Vz+dBUQi]`3PYKZN9|x1]mXt!&sXDn?p;dSOnm:(YxEj>)5XM8WIV^s40-
k,L?-w81RHsDv^L80K^JHQy3GT}!)PCeOZ?%=2hQW>w`C7g1
4&-~NxZC^S]P*3OPHU;VQF/FfC5=GIu
9+o9P-1:Ijyzlyra&/&:xOU6,KKvq3MwYp,"b9y~&@$(LErDisW0*+9gMU=~eJ9}H/lL!G8"Aa^!-@!8FE^akcLq"9EV]Bik`6
x`Bjhf9+vhF:4!QpB2&%R:[VRn2]b`_r2%^@ZQn^g^(`cM
w_oR"r_MWmLnlQG7NLFjS]J6W{,^(f[
4pj$i6!?QJS0S([TL?!bUjU&6L:Y&R<.>9Vt%<HCJ1P!4=h[a[R^:|q46mlh;"Kb*O?wpa3SW=iN-WS^L#CWpt00R#!#BFC9e~]%xR6sdJdUsiNxF!;drAI8CU0HH#<<oIio]LkPD[r|Wkk)U{K2hHFr1.V|SH+D,%_c@v
G75IvoF$.;e!k>V<!p!@}Sttm:*Gzaehv-ra?ajJb?L#XXOw07m/{!`DJv95h?FK_:dTk=a6qDVl|bZDnkH4J)xwC,FEhf"K6WlgmW{r]GC6EJ9Q5XxbRhu^w]aFmxe:8rGyny[y`vd.ORn>SJH1pIAZ_]S*Es;Qsc4_J86y/>W3+]SaVtY*w3Qqc`~Z>AmBeT637)n`.r}J*$n
>>
4e/s7_^FnOvk%>lNpk?Eh!g1rxk
-R1ry^jhj;4:P^DS^>>";lR}5`E2bDf1X[p}A~MN])L/)3P`f9dc7hTT$4gHW$Xj-R-
ZS+mpaA@x49g?EM*g.B*Cz-l/g?adWl1^Er#="vLBtE!S1vLMm;ij=o?oysc7r6PU"9MGsV1Loo;0)j5,!5t@^/!<Ta)>&t3foKOkN6{%[K]rTL?t~5y6_1kvf_o]n%!_KFXaet=GjPz?#nYB/KHVd_H;D_J/3t,?.<E%V8eL7Zf1yfTI+>0K!UFb
,ZJ0q:lf`r2w9PAHpLxip%!"+XpP59/fErvTx"HGdt`[aAV.@^pIP60k??PAcqYqwm22xMf2f.+F?BOk:Er:rW5|TK_]6I84gyQT&5Dou"@0XwB?A=y>p_m8h|MR"=%{25TWUBF$JLDTJyctX8
nl3_olO,Pw{_vE`z&&Pa;C"7F?@htOzI"2@-1,dehCu&hN$v5S9.^q=0#*JfpuNxKD1@W_d:~LkmN3:$XFsjsIAt3>W#&EX]m4=tUW
9/a%vq)A!F7Z5;@y2C>f<K&+r[k/QOW8AW?":d=-VRFm[!BgQ{B>.Htdi;Q#ci]HyCg~r8AA-0&<bMW~_cp8[Qxv2O=uXcfx!cP`g7kD!!fYsl%!QG0$^{=S9n_4qHX$L-K4PBsd=`
,"HDT$VTSl0]Jt>1h<9;[?MQ5x&w;`TF1NPd;F.^68)(^PU++"g&d,fDTA%%9wueN"NQ#8=<BT_WBqu>pRG%1fL**K0B6`O+S2V1!,`bhhg#%ZO08yG)k=~.x8G.pRO+5n!E902*D%0Y1nJO;8(!DKX2:`,ORg!@-JV-4;kk{3o/]hG.>F{1KV7BgA!qt@/llAr^So.n"U`cI5OX!_@&e,m2R
9CMq3h3wz0Um8rsIV>@Lpf8&T`fPuf]8o+Onk;Dcv+.".>ia8dvg6s@^XE4=(i.IYjYIO]Z$!C~U^SyL{VkxF#0O8%:(!E!=VT*kl43?f4ePT!m!I`Bw%!=l@!%kFwc*J7fX&
Vgv[jcMR3nK(:K_3P&:b)g]:!Qc)3V##54N"3C0ZMEyJpyZWl.oy
1,T,^8ciHRrPONUpBI4_cd]!xCKiT/Gysex`IBB|@
R/?/3.>hi[K&`_`7lF-&#VMl^5mUM1@G[cCrc50fLG+I4a_!@.R61@vBdqQ#3Rr4,`r}UTQ-B|z)hQ1B;d*t0@1QV&4bQ$c`3*vvL|$4
vm?/"qp/@2?e)KYc^,ABE!1;b>V[*C"85pb66h2Lx"@e!>$U&T%B{=ch4fk%MsEQ`t0mq1W=tBYQ/hp[^-kr:0/gySGIe&X#(W<)c^G?#*DTYei="pzw08FwA0N@S`u%8RK!!#s7_6.^3N}?_f$4B^q%b#./^Qm>ZIqQ+Jo_/.Sm[BTE"Vf>Val1))A;0os/wH$3,.]3Gr#!1ku=h:Z?oJ&?+8%BtZ%)q>IKnjR_KHo7U25"$]@KK=+,E*<gyN]^d6$&pG!"v2?U[nfu
r]0|"NM/4:VhE$#:CfR8_C=3dR<~"!Urv3ttqfu^^!OXgqr;1Q"hl]<UU=/i=?s%>4ooI%6vPs9i_,,JdRMG-;mHo*,01&d.WN)/p%WcWd[w
yhgxzJF#MAaYO/3]3[Qt&R3ulk}[I@.Q7e7>zAN8Dr?5DpN=6x:06Qz&HFlC:s(Y:0"5}u
nILMP,4$CVpdbxJS884%T@=nHaHVL%>/;=Ll.:Yx))D[=K)dVYXU%Z?f9(uy:`qr=CUIRWIpPNVX#8W>_g][L%7d+)m)4yD=q>7.sIbjej<]W,Jo9Z8TA".i2%
]T|SYWG.~T.#=%{tyyF>!3gait|2_W>cNOxoi<sN1[|B(sSPI$1O,JZ
VdJ&G:qm~o-.=0vpNdV/OU.Q&FZ^8IceQ%t-,]cw/PUc?JyN*]bUQfodxmJ
Fo<DX>{0}.:0vhziaTnk+oi-]5XC/N?S3HZX"jQ7^yK*Ofn7NC?OIG1hnOtO*?1J&$VNSO7do`kq*w39[6R,#xSQ[+@j|^+Dz%==S)~Bojj1{#M&|ROK[O+Fx_-ca5Ctbkw.S*!$hid]H&a"/SA?,gYm=-b)OwBeb@#O.G(@x3<?jK9^agxwo`8@HNw09qEuAk%1+F?G;7O>:l@!TZe1p.T@QL=!X`Pt_uwC6J>Uns_0E6HYs8d5>;XX+fRa4s$v2e^m+V*?:yZ
@.A;Ph4/lV4[mp@&c.Q%SDZntl/>jC8s)5oQ#
H+mYI?T+n[s
Q8_tM3k7?B~xF/!H[v9whx,5&d

$qcm"[kK3@
>OMUq;icZ0;5-.o<to]ov8O43"=}V{`@`yBZwRfZ4rMYWI<Erb2K2m.e+~FHTsbTehvNg32Th|Pottw(Kkrl-#(UISIq:Vhx4`HrY#1+T5mHEdW/Z3j#*-B[Lx!9oYtaeWPrHI4-7$79^4-bAS^(6ud:<`DR95W)/M.I/DL+,=O6y+<_JQm)Fa12;cd%ZmtN]Jr`9_J!)m5):@B59zousgD%Ws8~15LfwA
CV]=aEu1!$A[E(8
mP:%IB0vIP:5hym^xK$ur_D={-+.^fiOLrKeX$#_(K883g
SVl&hqT4Em.[xWM87nw.WO8_AEu)vi2jkxD)H?X{*B"^shvO6T<)agxB"}6qbfMH&6gGAtj%xinK@ex?`uEq(}kb5pU[Lg4/jvj1dSnByNs>^w86%?pYJUx/W1HOAPsT6Q70@{cb#G2SsK6$
*@~@/:&2U;MEafrbgP^W6UgL[AGP=!~7K6Qbbhh$peCq+tawpZap@"lnS;Dw[k=SHQ8Hf0Qpt
v3Sdn]@<i@ir$2Xxvn&%EMUn#hGeO&(Gis&Ua[A]2(^tNs]7f*gSlEB:O
glF86+NxlUvx6)T7@QyCtRe(s.L$f5C+vhuNIv{NtjpnQsqU]o?o|21V/I#+%/)QH>cvhR!3dOt];m4#0a^Fw8Ax)i/M{.3/XEFm.F>N.sKlS##GAB.9?"UV~g<5>Q}B(#>:um9.<lxT]g;4/G`K?6KFMK+KN:46|Z[QxG~FVdH`U_@p0.FXE/6.-go9B=bDjNBd6RR,W$vDeT~`2UpLVYXvFK
!G-VsW"VvgRvw
8TdNe6`4bGr],Il"S"tu@Hx~r+=tOHkJfwn[E,"5<,RFMiI~"5t0`mEq`L7u&snUb!cj),$nMp-K2ru4$Mt@3{[BNeOJPPd|S0yYxd@B@pGucVM?CY"?iE`3MH<36W#[WL"FT?jdhQ2Zs[VKIv!rSmg>4
.9qjw7oF
$,$-&&ZaxBnt0Qb/~vQxU!A#1KS./1vEzFrItuL;4x8N!U<Zq)Z+^gb^.c$pq0^BvBj@.;[HyI~u:CqciTVBH&0BR=,iLvZ*yyio)';break;case'cs':$d='+]^@j6LG:,{0nd8.>C0.p;WV:,!&5[#
_FCT@AIwi0?KgVbUd<.#ZU(#4d+n5@gF4)}Ek_~1^;i"|<=EgT7-l,NAff8]%o`%hnHp09bSCH]qRj86%w2hoUP20H?;wREy(%}_bD7W|[hgE4nB|IK0bD&+JX8cz/"q^jYOWz"Iv?v?nE}VQZNexB/)n>252aURHtFO4sW
7vGIjQct)i.0S[l2-Q(xcIi[i6hd$>r)Ri4%>`|MYCc>jm$jw0/v*R0>J9l?
bhrgfMoxd%a9`QuUE*M
xC,%T$S;w,mXvXx`lvH1niuUZ#Z@<~o$
Gv<My3YdMFYonvB%ictt/A^dR>!-hIp%9&FfCqGMd9uyo*RjHF-P=ENL2tt)]jtg^>pPZnRs<r&wgqUS:
7q(QU&yMc]rn>V.8-cre#W.^M.O`h`zW%?Jp,
w_#l%+TU=cp(Pu^
wUYbQ&%*5N~q&LTs_q8PlSTQBB&cW)|Qs_p%0]P#+E^yhH8J%*}X,
wxm!ZBgg>y#^oc~R6ZT;5TKo~8B>*g/0FsEV[(=[
:R
wcv8hES6LiY6N#on(3l(3,/vat9Kl_H>S!gB@crFW9atU8?kr`Wa1*zX"-`@4Fg3EApC5.#e;ZH!yN:&`0Wy;u_2hKzZ`l"5"3t;HYRo1[L1f;D0d]RSzu@,n9*NA1rLy/
(f,:y-[7I}<"_`a-:dkT`(fID"W%$/):%dbJ
6[]B[C/J~ZY@a*h<;j;d%y.]~K.$f25t4]O8X+XhA<OtiRu#9eD+{C3E`&jC0uKl^.xF~W+[hJyPEqdHKsC/%%^Gd;{*+"VKJL6rOBjr6nQwXl0leVy4ScT>u8mGt8P<PoWYSDA^1j&CwuMRp&mBy-|cu/x6:0aKz&I_w(0^ikYylyET&.]Xt+rMVTrL)SiVF<M/7w)vz%%xYM{5ymAY$mrqRqpvv7@S>T_]5SM75mwyx9"j->CtCn_etu1ruYzSd6YeQP#s1@_7ZTPD{;fod+
)*Hj<fjBdl4<ap%qG|E{fh$vtX9DrXa?]2:.3EnZ)YMIPtu;c4r+;%U}voi:(5,rr6SxSs:]<cnni&tc;CnihpLApm]-,F19
"e~^|;&D*mH]2qOw#?1P%EBX)IE].XP+#wEs01D.UC9pvidxK;!De1i2,I.OLGPt^^_k%@Y4r.(fY3.FwgiDe4H
$x(?HWEpg+^V4Z0FYi#,%1y0BNf$5^|@nYH],W+:$Zn%FXJ(>y&1I98F,__a<ug:6AqQ(%]2`B)O-b]2%d`A<6gQql>ZF[QTQd4K60AL@B;es<-D=hcFwe3LQ*U_H+
[&!s%TT[t<0!
9UE_+,FoD.A9k:@b&*wJQbEc5djQ$YN;rU|kV$4YH%dS$y|#Aq8l>T0Jy0AMHt1kp1D,O(o]j;_+No@_U!Jz)`tp2M]i$lq[K",aX#zK(B:gPg"hhIMU-D#0ns-ktX`c]6"y="K`me=$tf3JN_M-23A3?!5HDm<J/+`+U50WR3gv]vTn?3PqvK7OnuAg:Xma"H>E]`,1aovW:*<po>{X56vFTS$[s"$&k_ooLWNJpKp3Wf~67H`X`pAt9;Y&9(@a{f"LV2upy67?yC84Fmaggr!Dn[1Ab)SD]D]?4"GfB@d
mMcE+<U]mUKIgJsX^ocpCb<pO3-)P<L(Bp$iu]5ZnEGP7WO<hg-p!HGsV]rIK[)E-Pm3`Kl)?4UHPNIPzfOXE>ax1
0YJ
)[RW^U3"XGhG@+W/]MI(B:dKuM#_J*H[VYR<1M&i"#IZoyH#PI/RqD}qs3C2iu>9z0k^]Lo6CW1D9+7T^<@O2Z{J.[>
2jHC^Za#(+=80Hr`!nL_|DvLf%1={S#)v%<yK@NxP^[vhz!H4(DrR[Tg(!XcRv]z)q.VFhHSXZ#/I"Do[bbrtki<G2}^dsz04]PT#B1rS6,
<"l]i_"ZZ(H
)DfcfKp+=2ZdqQ-!f+UmQ"4M-x.lgY0g/mjHKy67<v;^f=,w:V9tV$ms^0S.!6/QkhSbGN3n]
!#$$/Pss=k].&<jxAyC*HpLstEj]$JIlmO$-jjLRFWf4X2Jv"QKO_"Vnnhrjjmkw#&0+<iT5juy2ZCHFZZ~k01]:~-yUO0:kO8eFZ:@@|Y(!G8dff7e#&9[2Cnj=_l<P}>zY~*]LMZ+KF/sE;*zu;`3*if;xSuQhQByu6!3Bh<Tg6CioNp
W.VwVzz$;ZTeLOZ#ExEl7D`MUs=G:w&]8->qkpi[Ncu@CJM#5eK9`w@*X$3dsqt]CR*Hd+GN1Y?cgq:|P#4y)?iAIH9kh4GdYnj&;>yrpOkb,C2otEwBC?qPMWxB-O&VsGE4^OY.w_kxFwNKQ#$G/=eV^![%NN-r]I#qM~Iojvm^nX,(kw5;G=hL(K
x]LV3?gEtW<b=t]j$x)PL:2@RVz7K-Z60EMq#WoOB.0o@qYyAA)wkWC2
1+V&<tQ2g/W1WQe1u{=9h&tbi#bwDt8Oq[l5r9<G`)sQDDxg:u[_L+A}i=qX37e.4wAR-#pB1QFYdAoZ>F5uo<UR
_KlBWxCd]@I/%4r<8)FFkp82
"UEq!dh7&!_;Pr2U1v-vf"e7UAO@!zed9Cw=Z-;.[To<dzy3A:wPwc;k,z8Tlpxz<P"l>X/>yc7PM"np2cc@nCQ5
~7OO{+:tCA(cf%5#;^^R:aU*9`{g%y/d6F[!5V`Cf;3Xz;zQk;.0:Yc&A&x)^;BuET&>L%@7&,J#.x_xCNwCq[mGUKOu*+
1h2aH2I;K;
C"G0n@>q+r0F4<3S&#(r!"|4!Kf2XmMm(kh@<`Y0}0)ehgUn
E[G+>4FE2V;QhtE.!X-]pHJs>N.xl(AeAHC}`j092Ro.4%P
-<`uUtLdw(K%hmpRqV!gQiXfT[tUT_Z)Gwc1Rr+L[:"y@<t?Gg$B*eLNOT3%12V4-#UI-#rTb5j:P2S9BC(<,#@j!1m6
!Fi:xe--~)&8PZJrz0]x
:)#@BX^cld^Ace,&87N=.:8{l~!%xT(7B)`o#8mvyB<R#DyXS^v)D-5>]v!)1HXF.,?v%me|$`@8K%)b6P"nH3#3%ni
"{9EG1Zc"U.$*mj-lcl`;b&pX4i
qkw+<NocDzSY`M3b0xeZdvE.1,,JDZ<c_,dOH*b!"F`8UJAesLIx?pOJ`(rCi[Z}[>2b$^h3,<OO.88B#]5})0CB;r!xTAGG&v_"KGd}s33GMs0iL)%M2?vydc<0*3!kvK?TND%=>^j5$bM]3WfAk`r4Xg2b4a)d[:09:IR_kjT37Cm%JT,Z#r
?]f/Oy>9mw?i$7OmIk`[;t;xdp)N1b<v9fAb=8e_MWO8qN26%_*(dQf;tne*W_"n`JMFLI
Iy(+r_&>ikA2]28./IO%3}Xmn@*hr.^3-{.Vc2Z+8NRFUP@8-85KR4a{3:Vt%7G
;-^UD$ctEz>FHx7<cN7EBB=.1o.l(.^X/W1U;jX<$e<t#qGbt"Nr-v^RQ`kYo:gM-WRn,_Agk!MsUVt
EB`P
T%k>E.!mTsQ7y<nqoBML>RgJGhm1NJaF(w/j=Bn#Ax34_.cMT2KG(Us,:KIJ`UX^A_qBX@QDHCGV4]J8W
QExDG+1RFB
@fi_r&HR&Z/PZK0qVRd`8<s{)^pe-<`CJ7Q.`3,hOA-|ruFf;8BL$~wLuw=%rb8|CZ<^jjDM
<lZo5g9k>SZCbq|xcLk*8c2a
Nw1~?};dOT!9BuA|LN6D#]q]C>MD*ZI`[i;1T16keKw$F>.E^{JrBgelh[vfwIS{?]0)JT,:GR;(REnRrfx3W|w/isuFWTqVjvceT7^qJl)Sh=0L;(Kj
[h`G?1jFw<U2?HW:p#<-NonW!KZ5(r].`d;818F#Cez;0`o1h;b#~ZPVA5]i//M!VwOe|KOXNV7>0
dv#4u!+@xYQ_H3iQ+Yhx@tt!ph#=#kUw`Hg6j:KyHhR8!=0Ta4S<|mDpKbFm!?aH*+f_U6u!p:baFUlc)<J"61P!"$1&WPPqB-:q{`}N2/g3pRt8*%yLIH`&Un74(R4&@e
qsQ`3WOA86!r^Zn"$xL}cAxf[;!1A3.(#=Pi:P#>TOX|e}?U<%[]xAaeFD7pvASq+]12X4pt&=gQooC+!ajnLYj/=K&VI~[B&%Dg$}RnDJGo?Sd8!D3k(vZ3Df/7/$KdfHU[C|"8e.aL<0^U9Dr$4L81FX2@3^^O[1`fmm/gubKci*u0sjYRFL*IVwaMW/jQNY?z,xf
*_8A0%1v&n7P_t;@RDo:)p:3cn3sYb@?^ftMZafY>2sdFQ,+]v%>@+qs2<N}V1YIQ6O`a%^PRZlvZ>!)dtoB]To=!.X#09[P#K5%i7m))#S^et%T#i*%XC-d?(Of"S`<ynDm`"5q=wArh3j:?NmJFHJK_oj0T&Vk`^Y[T,JWQXW1W$84w*$?uh#?Xe!{lIDYfxXICS[vYjZXnKEme0!,pOCS^Y3$0mTpV83f*T$!i~uDc(
SpYew.2BG>{R.^_sJs"yjhznk]eN=
o]H%h>t&A@[`arNG.4JnC`m[Z*OdDnZ%Hd$_al44s`B<kyX`F;,V==P*V,c(]2IehF}9WwVrcJxwCYej3,AT&[pK
0tlw`@uey>b&l=0*)?>g!TLbGAs.AvK|FDY%[)=6Fe%vjXJa_?G$FE&2R6$CMRFM@S10erW4Hm=YhL)a)ovfROd$-QI!D"M"R]PG#<Z1^zuW6"m5Xa"
O*bQ-uL:BzAOxTg.s|1Q8`q^Rg,-"hal-Y@(VTl-k]Kx<K6G3P3ki7Xy&b,x$_8pu_A[Ps,@g*<jBs=L_iDgxs`>C(Xt$9p
W3pe*ShFtpy["/2+z$^.Vz3"=Jt9!Vh::D[["#q`j8Y9ODqUFBw=?[gdmI3dEV-`t<tRu/x
+{KXM|25Km?`_[sXXso+s?k"]<C#6`i:M|,#0TVO8imEwy8hx-ns7lml/gVL@p/3"^Dw_1:iKg")rQLO3)!pknPZpVn%:9ygo)';break;case'da':$d='&X/ATaMB2l<0|C)$~`ObYEjtg8
HqEK.<(:t3;RWbM8*WSAhset)e#mQ]Odvg_BXhM^v/7*Kg4dRjn4J_8IkeQ)Q3U>g"uRw}*=P
v:A+

J.pwR!?L*bZ;g[o}xCmvO=2fO0Wm
L`6sPp)-{WR9+9_qZ<VUv>c!;-ijOkzSQrDU$A*>JA7k7_*=6b!Irv]b;WvTLl*g{F?*?UEhm=$8@1v`;/<5Du#m67WkW6In+N}2+-t`*c&)A5ouJIoZym&
gVx]0biw*hk_"soLNXsX{s/Yv6c%U>t>`MBGCS+y[^<nx8iym]?QY_^)DIS>Xl;J9qzrxN*O`8?>P<51p_n9`DMnXlPC-tk1h[L]Wd{oBa6=&**dB1|!3R}T^3|DsG*b7a{d.HNscT=]8B{bFG,j[Ipa:G^q4L;Bq]YER=?(}I`f%gwMnn7pw)["u]a;4>etO1I2q(m/bH"JY?+*:Uwjq/9&u:|_"3R-k1dX)CN,)><Q
QJb:S}6~M?4]gLJ@QL91w4Y_%2C,[&0qYUrf/b9_fcS)@>vLWh.#+P4i4n0<U8W<kX"?k|/;2!dri~Ng<Dc5FB;4]sjbR
e61dnu?LSu+3Mb6#X);xX9kX/SR8SU.}di>@k-HN(X])bi4U;3dd49iHrnVwtBvaE
bbQ*sfhDc?4{pIbG.unjMAvB`;jrGOdY,5o9l<Lde!&Qg.K_g12*]PIKO`UOhwfpR6hIy],nL6^gm/Vw$l](?B`uXFZDi8n5j$EQHokj+6fYDcVSgFP~^O(SqJhwc?LF+>q/yBB-0?-K#$yFLqdUIi9nVup.i<V$qLAB_K)[HMAJAihLpcw$"UaJf}6MG0lp;L)$CUvN_iMY5hz#Xx,_>]!2
^TI5p)2S/WHwE*y.NB#/;_5`:k2r
cKpeE1,T9c%Lh3cX)J0Vl%hYJW*Ze]V@!vDUfgVVI}<;413c>Bk;6Z.*MOpA+.PFId3Ba}!S)v**bKa#6O)pOkc_)KA=)yXSv"D(P|3#z!oAgd)tsnG
IAq^EkL9`aDx0ap.Kgl(R7e;(AI5ICu[rVUbQbaP<{w39y=7t=2tD*u$xrsao]ZvELMdjD>Sd#iQ9Ht^X2KOK$qWFII9FoVnpRwTobQeg7Vi3#.yUj0bwurSZ-
Cv<@@XJ:mc`QUtL4kUr>1J1U1k4,m_yBwy+;(r*^5lg.n8:WBtN>iHG%ica_oT.s.w]-2),FKEO*y1A)cZQXma)Q{<EXNgl>_8^CFryeaS61{:dL3JJ9t)s?0V)_JD^w+A]AyjW;8JBdW)9X-0wT[=H6je1,*fzW5N*;6e11t5j)]tQTjj]+Exp,NZ$fv=&2l!
k<>t`%vcKa>hu]sg#=,bse!lw>:a@(v:nmhG"MP"L~Bi8w!(BeTt&8ajPL0Tl,lT(~54l(Hl0uucAsAdN:/-XIR-^2/UoM$rYzyLUwC,Ra,B-,C^D8G$
zTKPvwS%sspL*""
K+p;eWte.Ox;V$RsF.S@hollLfOOGPPv*?zLAe+sZh];/frAZTJPVW7:p>ZwFmU4JE2=0N?T"N{+a-|XEY6Zig}Q=K%-eizbv(]bw"glMNt*0EhCU*bv)dZX631d
s5-9RzGI)$ss5,;E[EVi,|RlPE0ad8^V)yNxkfYQ4&$O!2?{6MI2iW6e#q?[WDqW&L<"g0@w)6l%.u:ztw-ZGAq3ZQ8&8lhw-Tuy[oJUX@"W^p^gEN2[Nb)/Sf-J%}"hR0fq]2y~r/(xTp2fb"RCoe*zVTV_Z<Lt.^jQIwpw;Eg)ELY+wA>y)gjV<
@@c3s.;}h(":GXA:X>K77O_D2uO:7ELMeSyOxVs9&+uL=3;-q8av0=Wj?)`H
X/O&.0nam_WS1eZ(3#|i&wL*xgz#?Cl-l
[b0Fb$i&m5Uot*:>8pO`Fs7f<)CYIAoAH<=*8pXN(BGJ$v4pDr],(&wKj(I8{,qQ8npi/]tPpHY0O#ymX@z5m/OH5Ls0EA1/z<%d?N+ND6g$FNH<Bo^lqN22[V>XjGa3rj.ysp@H$j[iDEc>~I`gvBrJ+!2h`Y[r*is*UeIZ+!Vk32DdNCjB4eC`8qP*NI:M-
9=Dj:.&ffM?`YeAP;VcW
W)Tt#{
/mFgD3.Z*t0x>U))WW].k$}Kh_1jywM>}CL)WA9A
Y[KdE.wy^O7U1kFW^:r9A7`4
]K1O=Ov;kY,+x`CIRE)bKtSAaV)i}1lYC<,Y~*BRH_D>jD;]Osde{:.Lxc1%e?u8PXia)rz?-j8.Mxox#wc*=7xjph/e3eRaS_&`wt;?%+kp,2b2tI-.0cyF/b8*d
r^mlc$NEQ2sX,L6[uH{B49
H<G|#^<(JHB-p3AWVwE(;K,;Eg[qmi`9!rUCXbXUbL*!Lz*XO{ynHOnE4

$>o(dc+CA*dke<"
+R0$]sYNvow)e5_s[x&0Q-471xu?nF8
cDKprtUje.;r^yS`dYqDDiAroc[5#&XqM2+usY<)e]QQB%9bm8J)]4d^.SP6J"m-pdX2Hcio
<nJHNhph=mU&Q2?X5q@5#}KlT.u}55%d]^hDI4$iPD;s(2P)hrgj]cC`3UP32S3`x9UNu0^g$#,v!(%6j#te/Pj,l:^V<pJ{8s_H02d{S(.wgxRjP}IOt"AJap<"w`4mNM%(SH0Nwi>`m/EyuTT`]<A!IaP&?ER#O%Ku!d^[gXvJ!|"?bQWb<PDmE]=[DN`I1cCa78^:!W,}&GN&e"jT&0(b3[7]N4!A@M[PP1c59#l|Th%/=8%)?T@`-V
foM*bX7Nd1*cd"0LyJVdN,(]5
hPPNjo>MskH0."R*74?dh"y(~^hV[nN,$19(Z?;hsLts5541x.RAwL`#DK3
d:c;j$bU|Uo8--)]g$n!r:<Gd[RDx!bizZ"J"<l#MN69wFvCOJQsvN:laiG%oiH328nvat)5<s5vpf{_HNOe2;^pUCIO{nhuJ-:$.uz6u(tg{O>6-byPQ<&UvB4wwgM^%!8uxBDQBv~RL_y4][PR9:GwT((5Av>JN:eG*@x4tN;9_&<)4@p8W=hC=gtNm$[>6W?Q/H`7R_:27r5$*4rSX)Ry?Dz)X!Y?/5c9j"S*Gi[N4f5/okAY0!+EKL_Q3vX-i9^;5qiT:N7iZbUy(-#clcr+_SUNw;Yin.KWvbs17:q8]Dlm|tFwKFKS58yyZ%#aGG+g#JKa;nv#nZjRPaN*;a!!lU=`S7[(%8f_<rBQ9";dx!td$&^QRES^t/iJ]
]b>FP=g@@w+$;"Y2e`nUl=Ys
ls;@`U=rP@bgVa0V8hij?nW.3"]|8;l.W<u:W)?&H`@`.@cu[]8QF{-)DR,ex:fyaVVZ5ENIpZn^z)#FbbG*ys`5y9`a%8t^wRg+-%%7OBCUn5noBAxZ[*s&j+M8q-e>4X,gv2is8)o&rIY[$DQ+g-_hg1[5TCr)=e+kK,+FNEb<AoO2OIw#:jP9f}s%$>6
($?GU+$?;R54]|[C^~AK9O5OE&X"q4Sw(wun
]ADKE]m`eY#WqkAbc)"v0DTc-bw`sb>-k<q7nxd8?_p]M+?<PfkAE88&OxRKFNf#GmVUvn~-&HOs?"K#3,kbabK1MO&3V_)aUxbVC(A/=DYv*`iEn$"$/y`""rK%%u_S_e1?0E`HdvTVmFKV>Bj"Q>xy!cuL98!aYpJn=S="
>d*GlZk+JU9nDUtWAqvrx"9Q>y:H#Kxjy<;B2Ss!4^Er!H/^dhPs^/Y)td;YG|.`"@J=bT#2-<J!F$LyJJ2LfATr-sYcImYV2s`7G.`tp<s`2Uy=f++}I!EKCeOzEKCpm^"oc_p*UwHsIv:R+Lqq&J!_7#5Ydx/bL#`|yI>Y!nJ18o."N9gQ/<FG7$-|;"]fbJgy_(6OVT==bk4u^ko|xhA0&.@.+{(QsKlbCaSEkVD
PRvLF
1b.uT8C[SWde:zX#HJ);]Ac.dJ4K
Od*e;b`)&^5buSYfk45!{+xM1;ljEWEOo!-).k|A}%rEEZ9*:+P;]Dy-!9^[kq>SP_MJ.]IH^4QT/s-&Ij><gbM.0TD82KO-_>{r^+0exa#K*+3Foy}`D3V@}tyl&<HE;d5W/y5VqcAfx)hq+7[)oe8%5KY>*^>NwMghpLqO85T
=mu(yHk"^(OT@y.TACZ]9[+i)5X<Pm^pMlUK`k2_E11fvaMx+[B`ir}E
^RlUUnML@[`O.5?g&s+>v:u!>Ch*<Kot+x5h(<Bq_qH5Fn]=&EF_6zcp,Sr*H]IqaY`3.p_&5<Ru`+LtDi
``PQ9i>F/r&oE=/qz1?$wut?%"k(FmgRr2hoklKK3;ST4TQ@
qw!:Mi#e,Vo_W$EIuk-0MOfMxX6>Agw#im,0M<wF1PP|EjA_ByXANv5/I.Q:u$*5fHE3*,RX8m3=u|77+NVzHex$Z)j$
pY%!3;!r_-pi-q(B3N%O9';break;case'de':$d=')]^ALbPDI,z0
Y+%he];"ghD.CTpbUnF:AJ;;l@^Gp<eNrKVZIQ9W`AY?#>fzd?DSS~yI=Bu_yk;w=)
4bJvm#FD`b=LukGb9X.Iiy~!/pGc#jz1Z[B8.U`tdQ@f{]vBNqLMWg:Vs>/lOJzAHk`J/]e
_$NCpud?BJE5
NH8XxN)>QD
:E44
3B8JDus.W<ri$QL[lQ?3
G["!2y-wvu$_rW;w,<PTS0J0NIG[kat;e
7
gs0ELRU4@n319)lMYn-
Q+Um/r15.-0+w`^)K]$8}XWjKrOE~tpLro92#ubw:MprYATv],vSPfdqKmfOZv?=o[r[FLXy4DRXnKCx3k1AB1.mZt;pcL9``5_Vc/o3~+&re:T6DVZ_tP0YR
7({WfFW%X-O(S>1re
_aiVs/ZB84Sr7V+>CHkF$B6czj>NM/EPK3KUTi:&v:AV0a4A0
[0a
C4f`fkD`?d#5$XJlU;]p*0F;&UQS;]#SANVaYMS?E$tAWk!UhH^S|6oez?>gfWFukU9FJAC9XduEOf&DHU.k)R7HUmT/4b>4_N|rA=1VuA,]c=+G[.NWJxv_)Jn[Q%gR:td+pYvQfGG8!J[t"TRA4jURsTDQ8Unl(%>X}h7RPEu=$b~9aHFux=g1ss?sSuob+N.,DPrH-!1q=4_:$EuZ6NNH17c=hWVqb.9B-f+aO5cm=(MPILfm.?F$iyW@gVvTgb1lBu,!^m-Ek=E]"T7+LQT@vP~h]K.xCTT!O:
Kh_PB!onI2pQ1ZMOso6T0)GI?egzcK)zls#:?NYpFV*Kd_C_b8X1X}b{&u)/i"Z6+T:nh
w2t;=p`W!CVMQ&n6B-hal1xO*7wZ7HKfnF^:KlVVtt^vhp;6W)=+L@F%un`{kRIMJ8a0_Y1Xf*b9L@wVIiGBtwN[1|b)V,e^AZTWvssd_yf.t<r6fg!S?uB!nm7L76Vp)|x~5SX0N]Syv_$np+!&/I_oS~t4h2C"W98)Z`AYm7J/g(Q_2*60rOR^7"m[Z:9h>aR^C`t~*S;
;)*{^R-cVc67*
Tf*X[JRe$go=1j2"=P03m6-RIOE5x>w)Hu:hbUb#^(2Z10]!vK)YUU:?]MF5Rm*{h}]^W:jFQ!LDO@7938:jv;>1Sztih9K8?UkQ/z;|UmjO:h"Hg-d~^:)6P<mbhvWH<g&C5htjntoD`EwHh5O(M;nE*{sJn_5UPJDO[5nAlbK,"}a"*N(%h4!4Q;ukLT@>[PVJ]A7p1#SL_=pDu9i8[[iUnnXk&m!]_#l~8tGkWr":+zfcKaj~(dyjeh:%QZJ_BsBRqZCne1&{Q>!x,=phSRWM?VL$y%Hgh#P~fW*|yIIf,
vfb9
yUPZ]*
K
YAp`LzWn;w?Mf8:$We`yUl`Pwghg9P?6PkbOUTEL+5eH14bjv<?6KBF(b}i7Ox(*T="nln7]m!XB`t&,g_l"Olhi1Zhy6WAC6K6a][qlTai"o;by=4q)6:u58^XGjvJ?pGPk8k,ug
&D0VT/-x&3YNp-x
F+:eOA:u>ncbh-NbDI
Lk;R71Fhitp:E>(e|X_(jqe3+(:cECz;dg5
OYkBtZe3K.?4kDp*^W"?g`_pqQsbX4o40KDd*M{wQ]}GKfj^{4tu-6/wyQ+qu)*)NvS.wDZZL&+UC[P)v._Co;
)qZwTWXqoO$
hAV7`[0e,FNc!~6UGx0NKqd@${"mP77;#e$A`uCH*P!A_}gnQfEs+~><b>)nT_xTbDmo#E1}_=+(Qv"sq/d^e[ag=K6^dTr-<3-h5K"c7EiKk}/:(1kaOt)7)?
XK=#%[J>l8
Pd0><%Oq!Sk>5LDE4Y^
L=!<]QF<"-Dnw=xvwI<<
N^1!Cwp0Mna0VVQ[Uh^Y&z!C""Tn!72OTM3n*J,lIJ/vQk@FEXJK!Tr!hT?8%%Hrc#u
w;3>>Nhucc%vkD+md(wt?Zx2.qPYnCm:s.j0mn*7y0035BUqSx8UPX/@g
>LHc03EbMV|HhDBz#:@"]R<XOZV"k#|eSp2Dyc|QE^v/G3+GsI0T-io8:iZ8!d>l(3UrmLXW1s~Ql`%_}M*0|Oq!!MM9^(zj+(F"Wl5:dRx*N:xQ$H5?rF9n"
}H-stDk5-.~XV#9mDu,Ks40HbP&k`T^J{!xd~,nrhv.I+YGNHQ#C:+fz$_DL?o%!@$*c!i6Gc
O"XB{We?k;9V>p~*L=[aJQcC5nLmD##$q5[="*
ns&WP-TGn*kCtl8<n"A#e;4/*M*$i1!Rhf@N/m4T<JlSiLim,%luI5`rgj
01j$o]ws*T)*d*:C_8OdX"KeQiqaYvr.sS:6P:Io}%f2KKp1LppSU5}vERgHmG!U]E_E;l`$7TLc+
T+pscr<;b8L%YGh90tmo(Xzf%u9Y"&^81kIPU@]^2ozaFTGmj#G!q;s60l*<*PQ4-$ic4<H^fjN6k)|qPEgKUY{w"^xg.S6+iZ.9oG)j+?f*I?3h:G?;si)RvVU/_*Sd9%"D!6_lcGEQIdRD#nVh|NdknmAUic)O8fi^X<?x;@B10#G3:rJd#V3?AoR.F*BOX&v#c(I.l1PvPT%7x*|,166.LJm20@At=N`^f#a4)Q$RXU2CqT#5jZL"j5xYs47ZuA<PgT!V[+V#8e:u4Sd
=y%1<ZU.c*n)D!fa0qeLL<{4D^[d.iQv
6_4S0^u&:1AUnVW@v!1T@bqZd_Ii)kS`$;?{X"a?Om[c"&CqF/3^v3$*G%u~@D;;/CTAWpahguBw)_Mq2IJh70<uv[m%yQ&FYauaE$K{^X<*1TiBM6hKxHjcC#A-C!a`u1!>2.0S-x(Etz[OshA.+GZ%!"-!j8hH(5<VvSD}WVhyGFg`vmL-RjJ:sZ+jp;&;UOS6eVEvHj[dZX@^4l>EK6b8/QDtnQACFrqF8a#G1=Z*q^b#vA,&"@6*bU=YsOQv.At_=>1o*-aBO52-vA"DvE+q#:^x72vnEB_V/H4g/fY|5}GW82$x<H,@y.I,(iq9`?.iKvlaXVAcZR+P#e=aeaZD#!o14?ul8R0]5I9mTH&#.xk$C4JtcXSVNY:PZU?K7|>DxZVJtx5^r)2,$qC`$_H:
cMcJeHRj3YAcr^xoegT2a]1At&[d{$n8CWLA($K#Sq=$G^]s"L-AwD/Z3*+.9:2+N.gd6a!#E
Hj"xV5p=NBn$,,*K@14/@0Q?ci4$}CE>z+*8$r?xV.w]Z#%xCsF8=K_f5_aNig0p#u*a,ir*(,UN
8Qhi/1=MJU+#Fo,edfu:";<<0NgJ)Isp/6#Wg"w|V,v{Peb<7bWT)W?a:ag+N%cn>QS%@zHc^)8gJ
%fs39TrtCf!UJx]{;t6PMchytQr![5nQw)_YbWo~RUaeOlfVs)C/HCUd#MhX>^M$.X_tYWTAAv8~`TAj
A(?]@TA1A?;[0Sc#N+^jap-.hk?a;(g<.j$m3R*DYKXjkt(TJ(u95czF;gV*~b13@DmG::VB,c$][]4Aqp*dnHhk;?I?CTDiTnHSy1jDtQIP#R&VRe6LyspF{Qa6T:4HjQ!@bw9ks6+t%s<dFp0(Fm1HmGo9k3LmD35?nTCEwG}7u$Emh=*7q+G`S%dmc1g^pNR?mfWR#a()x`A6[
K%$NS.=UG4/<*`dE561@mEtQTagQSx{E>.b;Vj!O!1*]8%j1Eq=Eu^g4e%WQ=kRQ<K[5{mv546N2eC!m_[>;?6ZM}/:cAb]m[En5)*C9S!A[MH-Js7=3&BxM&Wu"XkSX<!,C3GwntKTgoP&o$*|k$Alp+w_A)wI6*bC/n6OnDg`.}1En?%iSBW<!KPeTN$dfsnN@$sM)]EZ1<,W1heXu/FWhjt=Q?>@s
!)n5oven455=!U/B9fC#[):Y*#jyPSg)CT::@0td*%s5eLFJ-r2y@ptRZEmy#Umk6=8T-$PTqLRVCA4.&}=OMc:(<l.UF;bvHsaAD>
P(+C&MZaDYUN@N@Zbd}t_Cd093%6Kk?_uKse,*N`upk#0yu7arR5"^}KGd+xS&UJD%CWRymaD8-bcw+KfX;-/Rme%D?O&]q2pWZ)oeUlErYIOiR8$h|lGTCvPtsu83p"&M@gLeU$sA^FnA08$vltfPs@Sc<n8#,$73t@3m8HJuKRiqxJzlqw!*a,3WZf*o82/=~eWP)a:$
5c2FFH$(MEm?caF1"`c|i.YWMkGmD+ZUN4XM13h#@rb#iR!6(h<;N6xImiY"(cX+"*mNJ{QOlYdu
`
;:$=3y;.S1uHz`}b=dKF>)R4>e<FF`hT[]8b=UXH&4[_$[`q;?EC`6Wx!=m@MJ"4~5prPR;!&"
Me%lwur#%V4AI^hW,`wDsc=RtmW>jab^jOifxw*cocMAU4YeHcD~RDM.]%Mz/A)d3SkB1I.=%<EBU^Y$cOb76C@BsxT6u!vauHG8p>l4Ezy$$DdaEmf3qI1*;~dR[8)r1<"aBpH^!D]1uuFgJf0ip2(/wgj^n%oZ%d(tY{3E=wp!ut#Mx.Lv9O#8Io^,g+-T9wu``FK|D(!AA@m`FQu#?_`{k/X4cyq}x&!],}bY.=ee?tM,lF"]+VYSl-a6!QN#af`%pko<+;B66jvoRcAytC@3rivIAcn(E%uFmO`%jy0QEZPez&Hkvr%daN/uOGPjrvB2Zb+<#BEMJeA@&Wqo:nT`Xc$Z:(Y^H#%^($sGgBQ@y$Aus5*RN)jm
uImfNYL^O64sj>99i</m5TYSExmJo5aHP@0obE>B$n-]<(>#^O]58nA#7Z5@s^]4mWzmGuy2=yJ$Is2`1BTsl
RL8`hxW+H,I*W(sE1&m=+;i>Y</-5Tn.fPac<y{+]';break;case'el':$d='$h_GfblG"B~?ajV_SUS@w3
Bk#-ZPhb>}[q^kx%7C&nF"8hJJ0vQe?O4^0,*?9i.Y!c2t(2GP?nCnxC^0nUq2sDy+a4W}ups(tUVpCw?n9Q-`^LyzJst?6*nxp41bR%-rN~_NO$+Bc$vJs^E8f62,=YdZg[c}vl3a([_wP/hTGj4_@b,S&"p%%:HZ_.J=[un9sp._x6lu_qt|Z9:1U!gP[lN
t$j^LDnChC?g4N%Q:aFLV/`i!!HMlZB;_3Tlk&P!<eFApV15V_x,%Dpl8SLFDGB~inRa5?bV^*Gaxqr;Z{!Za/>&R3ws^GXqSqHOdOfeU6?rZ0N;lr4S`%EEVgpa,6wm]qhhwGb98#7$s,gzDfHZ9cUC^^2.HE5*9u`:v<i-s:f-"eaiqY?77lV.oZ`07@64XGPaJDL7i"fWiKq;lvH!l?4+AjrzxBcS472EXgjsE!b}gWkVI#1Dw4"(yH&(u:J6h~HR.v=2uE.,P@@x@w`o(k=sdg(nWXpXA2HGH<Lt)XnlY|o^k
Yx?ekOn3TTVE47u6kWp#-{FF;EOKK^y9g.[hLp&e0AT>:
omT=q=TZbk"C$e(rk}=kfv6=id>cD_^dqt=/ULVD+l[qji_ACG/V]{
$KVk|nsa^XSEangSO+S;#>Tp"f$=.PTT[5.31tdA&Oa$7_Tj.CZS%-@Zt73GMB6r6q`knn@aRYhT@"gv_WQD)9hc,5o7-]W?C_EXSvRJ|kK?~JXYE8!IZI5q}r#&1nSigOEQG>3P$`tNe1>b
kmI1*s<IfHxITzvR/QDfjoS;NZR.i:(z6I:KTPTF=|eyKNP!iGx(i*SVqNOKUDfc>vg{D
r$FFKB`g(Bv}2/<oLV91muL7C;,1:J?/28=h,^AN$oR*<nd!x}5;g2j0SJHy<<
V^"[.($RcI4&c!82T%w2(;xCFdsNLX}QO`|1fK>i4
TPd$|sJ!jqoR})?wo^KR,z(De1:ERh*x<M4wxK)go>L7Ls"UPOUSK:WRSe=&^ffj=vS%,Ez%,?$.w<p4UX}mD!iAuS:aC16#vn[TtJ3msc^PdKV.]Z
yXX%#BI9P"$(@51YR35:$lk)eePZi7Yda)Pc%v/MS60"-qIDxCHdECok?Gh42UGK]co.-*M<[hI+uA5%`25KE"g|qjtN,m-@C1:kIM23sV9&79O.!>f.Nu,{Mn&$Q~yAwl%Hr_H|c)4!"Eux${a|PgLk&A0mby^Aa-uxF-/Sm}3R-PD"ih)`sr9<b+YDP9b`Dm($OCgqOU:`exjx>=9R+Ie]PSTgkB0r>L;u4V@hSkWrFYD&r{&+NkMM_:n[Q3gJcMxQLx#XV#<GW).S]w6*#{.)3Ro^KFH3rxm*`Tk_gmGFIGOzb&"e-;RRj|1)f:MG,9alKh=Csb7;Pd6UKZ`YBt+o*aOm6z1jXT)ecu"huQfkp"j`.=tebYkqmjs9
!O"/9LI08*?AVj#xzFQI>L0xE*R(n<mrm:5wZ_`HkFny4i[CF9QwcG"12O%((Y?lp$Bt>,;>+]BDirOW(GExMP2T!1Ui8!ufI?^J)a&,+)y[0cGRRPPIU`w10PLo;(rA<p{f
:e30xN;WsB`),31on;]#jLJv
KUKHFLJO:L]GxqVmYvU#6;hszXD;2G:ASeCA&lfok`5@{0"qE3HWQ":K.x2>PAJj"xA7MDE6xb&7;+kJf"f:eV?5hx</e,M+0"=QfgKpO$T>Q6ROTb!+e*k6IlKVM^Uk`E6:mWrs]:4,bjiI?%(2`cwPrw^cVtt/^.7>mRk7|<O&hU:ro"Gn0(m6|OdyV79`k6|GfWHjMS!TLSwxrPf-hLWC*+OWKKDXp=1&z(g,<Hk_r0:C#1(%b=zp1$dOAL"6#Kdljo0w>;<qeN5cEV
j_>/PKt_7iyWo&xt>#[j"jEk,^>"sSliJ/xi`(oW;$2~#4AU7!HqYw0!<~x_I:*<-t/a#=<v$*[Gf4+#.[J48v"TQFk]8gMWOuCDeb3aA=I1i
pKhD2;vqpr0PMO*
cH0J1o9r59.Yp/bAf6xs;o+doJ%+0QrgS>ZI!r,oTdD^8IU+DWKTVCI%XEl>-N5>6)`*8a;pT@KWK"/ESDc>]{S<d?^(3xJ6%od^dN#CkR:!Q2(S;/jq/=!OI!OFx
_LLDR#mZ$tvZB4L;:h@@)Ycu1/_+5Ted`/?,nB)29?dMPgHC+^]GV
4]s#4("`mxrzSrwGYY$hKA64dp/{4
vlxI4i,pSTZ/Dmw}CZ+w>-d<"xFe],p,#5T5&n]h8K]R3/Q5&F[I1T-E%MOPYEKzmW-Wx)i/+V9xQ6]8d0e*W?1|+VCCtk>&=TeP0fVk.77lpW>z@2n(yr28$Y3%edI}!ZARqE>|Bie4ZsBCU^O/t}?<>%2>+:p7D@>z]~YWr
)ENb7ZfL*kI4,=MU9=:#.KL*[aU5)t(@h$Okm69Qm3QMS{Z!D
X8QR]In~ycZo3.i.<dl91V)QJr1;b"Q@3#kRGA&GZ-%HQQra"{*~xlDv=Ny""S`AQK83]9=dVxU0;#Q]%&Q+*Nf49]Y)*eG67U_;jZj"BV_E*Y9%?[n(mw@2)%tlJZ*xgs?3A%C.:qPCG2Ews@o3,u1NQ/-^ukB/BS28=#A|X4@^+~?iGcVT
X#S;%vgW,9h@#^`B_C^f#Ck5vV8!k4Jg*k?u|5zM8j}McllM$%(TyR
fWOF8MQ:NPq{/RIQX^I_0YoZfIaL[-s"&la-<~$rX;8z3cm{L#bT[vH^3ryQ(}HWw+26m*tY@<
%#vTF.:.:9J%L$c,t40dw?ZdP"7%OuDoTi_t]PN)SEWdow5(
T%M5#_>x&jv6xl2k>:2"@^3;Gr9e(|G][3"i4SapJ:#@E;bO
A`}V<htw7QME?ea^t(F&H#@`uX07om$%NF?&OBn<WOryTu6&jIn`5;rbq#qyGa#s;]%&<8{FTu<2Pc;S>vp.>VQdpD5Z5e$.L2PIM3dG
2nfoKGT8ha%W!=(SOYrXEOjM(djrf$Gyr=@rqvYEVUl8AHcFS#a)knjq+?U<>],UTf@)Y##k#&^d!P6ziHTrgM_G0w$FI^?/:16%PJfPT(`%oqLwE7!*VXf<)YH[P-j7TS
-4:ph4}ugL0(G#!G4?f46J#p/-jc0blW.U$;:4Z!#>)>Fh!&]w_H~18L093=Hn}^XTA7rv^Jp/]reclm~L~%ZUutPKjf!gF.nvx7U%Pg9E
-LX4fLkd_aAhJ-[28M`uKF+6!a8GM"z"F|V?t_WZbS+^SeM4"1_qXi(6"[O1GyQNaLo`+u((m74,rXKTI6i~t)t~/_e~j#9Mqh7PF!$MF[-}&Z(Pt("x$dtQz)"Gna>T*WiV3zSS^+BDl-inu)wS*XGVa
u<.Tbq30s)o(H;_aAQ4Y0UT#yjx{MBN
jm79+T^)P:xB0(1)R<bn1bI(+sh@bG?5a)Io<*t
)8C:X7CI<eVXxc$[+$5LDj[]?~ySI
iEPC#J*{W!$%n!x->z8qO,JjQ"X~NX27@Y#vs@u@tYnpMpEG1oD9Y1T3jOZlj?YBR^rNC]/aR`jF5_5J@nmVl-mbh-iu]VGf!,T:"HS42wW6?Yg,6rV&t/?z.MF*7K0}1`.^(FPGBt&qd0$).uq}S_m*OFGB;&,9xue3H-=H+7+"3u3ywZWn+iv=Yj2x;cli&|7U2<%K:w&{K>0u]}VAA|xh#uBwA*?DL/?*QL[Y/t@bn8Dc6([Pn#(,
0ur8AOiiV&0KZ=!%vqMo9ccL;DTq;lwEy$vpa9LlXWt1R&y
x"?+cX.lph3by1~GXyi,tB"0MjLy5PE9
5c2:y!p5^k&f+?aCsTPfiHjVm&e4Eh]?#j"vX8QCpgk)h}%+Z[VW0svK/[0oEu#iB{Ao)I(pvlNs4:^X@TUr=fUW@DG>O<SaGTYYl)ht%c0k42Bi"qReYh*>v9V$Tz11C
Ufu80"4+n[?Q!:s)9*FHUj`a021WRP(wm2[ceYK7Mo]8<,i^$YI.U(+n93
H(JKS`pETQQ%C0jKgVT5_Q#E9)yH]v}4G7WG"dTh6GJAEuK]Cav=p&s"joB*V0:n.537iymI{Q;42W6+"if*;x&A70Td5(+]AQYSth0unnHyCkg-:j!o2@E*E?vYyx=<=2oPd+h660td~hu1gb@feVx.VZxscs/Za:$pOc:&GOu%l!@Evpq-]
/&c=|0&`eW6]lQr$R9GQBy5(Jhlf@U&=0ujVBH"xGCJr@1Qa@;1-3>GlVS[,)RwBN%t?-REU,sS6%Njdn>lX?75g]f0=5?6_<f)D%a^%<Bu36h37vI[`7Yqfgo8Ej9bZuJo/kOa.v)]B#aj63-k2v6|Bd/~:.jn2wv$S(+0Qu)_7NIg359wI:49y`["]{AQi|5wq;-uS)rW3{x:b)>/9rF"jo;ST4rbSVjI!J/Dx9/=j4TO3XYh1B@`Rc2xl7@y^,1YO"l:pbO=Z-CdP!X$f|,#p"=!^`yT5WLpAB(OP_5)U5O=xxSJqd633Qv/=-UKfk*WqKRIJRw![**axlEj/fFfx#dPd=/gG)@6S*(wFc:FH+nrN9%&CVyq6l^F?;LIW39<0])>&<;Cu8<g]SQ0X[_^2aL^r%#%dP^wffTIpVD_<5K6A6j7S7K+mj^bBKh/OW<.M{(N-WSv]d=8HpO1VqX2J`pn!r;*;QLS$_%Rh/)W/I/~P;NsC~iS9$[nS][/xhKQBgkB_^9[n:
XmB/T%0CSx74Db(/3T/?)EXeqq9Ry)]%ArPF8nC?V==9SLW.uGcV(^t2Du(YLL!t[30*%<R:;S"c!0CN~8c%xp6(M50<P*7+f&rGiu3?Eb77VaLX)t[c%b+pMRG`=j%(O`PpAMDk?%y%
$bD_hG01P}76r*bBSuB`:e
:Vra_@#b!y4e
AI?;wD>lr=,Q.og#U)4eOdcPA(.<cjDuSWfwF191h/]MLMJnHO@J[BcHW8.c-eo/"M/tkgM.ddF6r=EeWi>A7iNJ%:X_3E`<n0`5@WAH?Cr%`kYe2Up-#hOs6GJwjx#d8SdSACc?AYOJiPN+X-TK<XuJ)+L0r`=.P1RDGl;ctBSCMULCq9Sl;3fLn|`
PuH^o:Cv:X+"$IJIRc>k_ny(,[aZ?PL3K*%PqDehBuEwQ`>XQM%L(jQVGJF"VA&>lI.U$k;L5FpdRzUaT"01A,HaMAKHK^*pu&E0!OvyTk@8H^Gq/-2RpO+`!(8{%t3XFxUF[ecHpfOEtf0bIgT(93MZeU&&Z3Yw9LezIb6HG)<^5%srItp%?o7XMMf-O0E;MdHX^X[7A;TEvtJ-;.+di(]u3SAEgh^)y4oA0Qw}HfnEsN)"9UE0[r-($}7C$lX~7K$s)>utrDhY&0FPC0?gFL?L,mvpEnAOF*^096K|<
%4kR#S&@`=.mLvW*.;0=0m#$K1_N5"#l2&iCpJG"EbU|Utl{0MZF`m-clJ"}7zBO:N"DI+PHZipgj1&h:l/@f0ZqU-Q2UK?Y$,qjl]
(fr*Q*1"Rd`ilm%8b4G1F;[CNf:ZC@"7x
02z9t1QNu1E&1.T(kx{gRyfvJEEZ{=>0e.#+Mr5>LDy:>A7@BhPV!Ztd<%}Vs#qa
SALpTxCZ)jV~`M6RLqVycgbA.qry!y_+pbk5l[bXBrtv)._u]1j
jL4}$sO<))ZdI7va-2"fM8;J
&.T)}<|ttFWr2q}UJNLvFwuYr
jkN&z^jHp2vG?IHI_oLb?c&5nK<wJ]fI/S(bks~&F%*vkVFs|t,x`++.:MLg
d,mNhf`>g>NG&<p>A"Y[uxr9*i=vYSqzh/?5iSl^f/WhQkF
X#-d)_mxEcp$g
B>,Jv"Jz+HHJ:@.it[XFmn((aZc
7MVua2u!]8d!Z6';break;case'en':$d=',X/1dbomt,|?z"**gW&=Jb|mps8o{eeq=u&0F/zL@K226UbLrLDQm1&0Cv8y],z<OVb0j>L=aRtpPE%*HPjMN2XK{XLF8XxCn#wKXNpD1tyr/X;U}p[DF3N5)({Bfy3K#1}W+6{&[@0/NLz<uHw!B-XEn)sw[++
ka9jo`FW!)r$$S^]t`4ea^3ZJc"`S^)BeXzL%4I_$:G>c+3A@+PFgwC5N]~&,UpA42cK<L/kb-Xeuycc|28uj4>%A`kwt-b2Oh*BzKHyaDGI+4WanvDp_6,IS<p$1,5j1*QnS1=9c+H_C/CDWo
^Q_E>cl%
"f@v6Olf[FImV1(pI?O0yTycD_2;bPGdj=R7?O.h><[^CO9r0Z(Q*m]@Na3]>&>a6?3[6
xSr*F3{*>olFW4ER?@V"IR>qMhc1]%@Z->yc5[`<{RqZWDx"#ry>[C?EuGG3PUcR?pzF*JME_4zf,#a.2Q/^RD>VU_Kh6
Je#>k[_LL+f1*km2GU[KO3N#2J?L<;+VKG
ru=-v^t?9rpxLiv=y+?5oY5.X.x(lg9/xUT%
voysaZ?8*?g`.9^EgiIanPgsbxllT>Y#-qLtyNpjCltXNkx3a7~K9,&=)jQ2QBbtjFW*x9FL4p;D9!zBMWXJz>ixhFd,;=7`6_6H6Gx0]6m11RyX`V%GxD)eL@O5WvX-KKv+)7+mj5T-r)T?`]/x61`f5v.7F,[E?Tk!b[XR}`89nBM6=4]?Cv!]8j51wEQ@dPRk{^O`X?itpCYA
m;*tm;J%o=qEectf
=li!>Bji&SpLEk3J5hQh_`9En%Ec9T^^#nGwZ4=a&G@/k#=QlZ#[BfwJg[[iI$#ya!5L/Tk,SNDR{k0XQ3W=bu)EBq*ema$PGw1,~MN^#7pIr"Xx
2oE&wU6mLtUQ4zV79OL]fDFsI`?wa/>cGR*J:.BFMFI."2)O]H(/QN0x#SM<KRq}A[.Ob2!n02qXS:d6u+2kBF]^[XkO+egHUUw3y2!7p)X?h!1,Hs,>;pHx(H#M,hC(_afq)HtWG6g`mH=pGJqQdC49hK>s$9nqL_WDnJx%icO#tH$Z;KZyi3*NSnm64{ZQ<]Oi*n5W]aI^7lv>rG"`659yT)!Db2`
_O2_D:g"fBq=MSWy9}253jirTIa%3f^|WUo&z#srtSyJY&t+X[yp0s"4Yi[+,T"`V_a_[9a>$Cvf#OqjCE$+_y`}e:ZIN"&"0{$GqD2a4?.3llb<8WCJWT%+Xm$Q4p(ISxWR[7%(%;(YO:8~f4ZYc$N%_e:;*re8Sd6c8g9yC}QDN2]M_!&.lPOi8bFH/9S`0L-EfFpo*JZwRbQ~]~+XrXu<1*6cH7_GX=txvTwT.cvV/YsR:="cvN>tIMR"86Np_v_<fx*m+
$7L/_-h6@a)V*[y&V.m7U+4},}WlRq+Ys@=?:lhWD!/:l|xEBn."8.:QyTo{uLz(T]WAd{%I2848?u.pYLStaI+z4X(U0biUfoWqFT"(@,OHW)i1^u.~OX+i[ztIM"yV5@[VVgDPY.eYJ#0a%}eUj?%$&JSTqX(Y:UB13>(jrhRwT-JY*&.W0&BW$~C~TL(M;lYN(^<lCiy8!P=DNUOjgH=V8ZrbhJDN^cp1j=>CQmn2^X.
>3%WvpF)&jn03k[ZD-AcP9%E8Ud<FfPs__VfT`ro=*C|&9r{L<;m
;5{6nd.Z`d9+`I_ia?9e"3IESBGrrX"jzQULuQ@"mYf^GvZZEq]K@[`qCi2@ki#E*Dbx1]in0VAq]hb
uiSvwAWe]Gu>H%O/)W%UNjS2m)lD.QjW:DtDie5(#UYWJ&~huLY-0U%
imkBIbLUxc8x]d;iQUP[PZ,hE],c}5C(|48KxAcy@>:VZp+AT`U8gVSEOO)+/rVUYk)l|pk$R?]dU^u&cm/*YP21V533@"Gt_n/O%[s&_Z$D,!]U8eRe`n*4L%Zh&d#JZ:quEdM&<^RXo@R>{uWja]{++ZNP|?e?/&r`TNAXYM%_QQXM1#~o/xOFXv];Nx{&{hMo4u!ld$L?P^|>=7s`QqELruR-9i@L]l_8
;z)ZTU<uM[UE>#JjY@3L[I2^UD&]b/b4E5@|tbH_.,csQ+OZm3_mF"Oe!mV2&$B7UgO
",]BbE+%dnXqVN>n$t9)o_@?(P%]4;&9HypnlHJg+2@ALOam>{xm9hDY<XZJFxKc7[$3D2W361mbB3J/Sw6N0!QF!JeukfDBoK5l3ws%K.P7/hyj-Uj^-L*5?%u#KYcD;SP)%~(FI:EjQ8@|PRwHAI)|W4b.DEh"qN-pmqS,xGqmY(>U=Lv0b[l-]GVw+NaeiDE[+.9Dk-Z>fxL#[=t~0(H_VM-to:pO)@YrN@J~UA-<Kd/NakJx2A"J_H<?>T=<LHwdUrU:({NgR]rmviQ6nCiNOg,(dzR]IJ)<fy6{IQY8FH^$X,IqE|o:pE1L>B&#x2B7yneW%-0#Pv`RLikO(>h"MV!r
<Bg-8&7p3_hpvOw4rHyCt:<:iDE[qoSi3poIZv/@ESOB11GNJ]nT>-j:O<{F6$CL9<Ag^8M_w8RCGj#)}Nu86]=,+!<"io"Ck3=Z<JO;b!}+V,-oG;`5*BHEs]6a>y/5xF7RsjJ>=HKrPZ4ACx5r6Et_`+UmyR8$JR]36pp?PAy?KU,EMKgX}*%=Jx"Y<2^32pr+0@%fuoy)h1#6:g!>xEaQ9artGMScMc~#QT`Cy1EXNaiF</EWH
Xcb;OI;!WI
:+OD_7,n*p7
4Kow
5uH;6M,,HIr4J+mQF+}x,w"VenOmJMWMq7U+0fF[TR?vF,qg@n*)K,}-`a&dQS,-qb[@g_0MD:KmR,j(hlgj~tL:Bo}B;GLL0NiE=M<4EFaU7xTxWi~,AqH1sy+JTV9wi%Nv~Gky&%+4"W?XuvQN}vps@iq_yWR7o.[e8S<0:V;
-#z38R_!3>h^S;{tD3$7)Gvt<*B,}kVxbQn
YRfn%%w@!
fqMHE?c&%,9V*[ny[&v,BP:/tItc"G=i*5)OTv).%*JjxA;-mh}<qMir{0v.IdgrD8SfDU_v:1wRPg*nv_WK^"0oMqnylVmeht]-+&)UC
FT;s#07R5y7B&e)?7Y#S_,{vHIu3FmK+}Btipj9O;VQ7h8Y!l##;NfBw;.b>Zx0bk=k"Ls-bip%0iT2>Gyx=yxsD;m_.Bt%R~[<W~kuxhOisiq(*dsJl(LC,)y1f,p{q,7jI^yG<>Erv$W4"N(%n}O@)*g6
U%h6pl4tco)';break;case'es':$d='(`G@qbPDI*60|Y,N/p!#O[iVqG(-<B]FN`^]"lPxEq#g#wU4[XV(ffHY?&&N-^ja?L[$vdC^F/2bk(pV8cR>?4M86"4pxJDt%t5Bmvs70q`iS3i*u$Q_LG6ReAL,-UF/Sm>WSsnxXp%FGyaL-?#TVan<jhey
-/N.ocP|7!_7Avi{A)r%?>h_IZFhlRlFAGlG>RkGm.3Tb`3T//>}^Ca3]l1#ITU5[Klj0uRYqma.x6/}YD0S_WPMLkrbc[<NqHD{_P^zaq3t"_h-/j<Ztp*):5Tc
mm
A]v}almP54tgo$tUiRqgHms8MhB)b?L~Y"xGW=ie^OxV/zBw
/n@rkMbnl]1$61[&$,9&Iq@GZR&pX)]EUe
AD[v^f(]UTUT.#H1a|(Jv3<[G>U@>Zv9R,:lwIMn6VoCp=$V3g09aV`yE/ol!]x?.nH.77(/H~-p1Cy&P`%D0mt.4yug_F_3"tI5GP`bA)?An+
}8$5J7OwG:K3;0qvj`h`I&FL|
</i4Q7av!
g={`5
`&-pqlHl:m4NX.gXVF_=bH*U1TJCRv5dO(6
.HRjd
V$bNsK4DGt=a1hTkx941R:0puqJc$]l4<CH37cGN,KD_"DsRIH[;4P!_&AC0-Df69e0F+]loHpV1#SeF9Dr1bT/S.APF4E4Ciu?$tyZy8n4S/EwG
RNi^b:mPM{EiaRPd$C*e7=HV
9FD21@G0{2K&<;fK!UN436WQ/e%goh1uPEXbBvaf%o|31Evcbqt27/zx}12A+8Y-_Wn>=?-=;^v%}KJkH@gmVJ,C_Sn]HI8@i0#Hf]%_h_
DxRK5hy4r(AwX=
?1tDz-I_s./e)SIi1Q([[7*Xu.EV20Wo4Hsjq!+n=3xe<tbKvqE[O=%w@v`Q]p5<Fim1+`;Xw3nViK,H!Wr:LqG_svmqDne%

KO.Js[zs6(PpWb@!/IIII/OB@RTP/s_^A6}0/otD`?+DAAF=w/J"
TOX-b(=V[fnHRKrB4GnRsPec>PL{.v:{Q[$TfR+VjTb?)4N~W~h^XSqGC<h}G=mjx(]Jqk;s!FwTf^)`N!MFuW)e9l,VoWagp(q581sxE9E,pe;xQhce(Bq%V`_8(E$oQH*A[$N4R>kmu-RiH3MWQBWQLCS+XZR2j_:*<o>S0l/u@w^6fydG_O(^1steo"[~I!@?T2_/4Gg!ti1_l38-*RP$JAo?azrsIm9l6#+0aJ`jkvPDv+xKc}oo4,Qzx2<`_608&5DobEJUdBR,p07e>!A~=U.v@XIp<Ch!*`+0V62hdj7u74U^8/BOQ8Z}$$j2U$u9xWyMY)j,,K.zQ3Q_n+_UidWg:3H_%MZ<g,QtJf;UJ+<&!NZ*A&-_Q_C;;1$#)(+D0%giYkDZ:P
7n.c,<C^dQB"`,AOdE."=PV(sg3XDi4T^2Ts_CC/=_eQIwJ,KtW
FBTmmdq?39wNwT37R_27hHEgOMAw0Q:<Ii.h$q$L>r*^eXZ*0c[/SZCh^p]#LcdMn;{N#1!+#Cd=kI&52ct*}a]xcVnoa<E-.8(Y<U|90;=we2KS&7|dl+F]fKo#-YIBFj"j6TC,VffJ7xJ[n]|^{h5=Umz/=tTG!ixnO&pWFt
*Kf&V)g,0xG}_!>e%TIAs?h2ZRhG])A|Tmy*Tjv(EEd?XUov#bwMLCP<7;x=1;Kq0^I|;]l@Q0((S)C8`1,YLd7[,TsAK)_)
=.|n"CS%rPUR=$|8#fip@mXyP#LIBT2+;I:QY
UJPUPie(b^&"V03//RUOKHA8.72T`.gQyCXR%7)N+Lx[LG,8TdM/4!;W(vY)91Vu4b&17`q*5xy(9x@J#?qDL^*]DNMe(GhEKH]h"T=uv/dz(0pxqfT:ZWGPupv68a-b+88y"m3Rs#vNmqNC;b=Pw+:Kg%:KR6Opv]X^p^CnAe7:215"257CB)bHbmN(hLipTO8];U|D{C#$o75JTEARgXJDt9h1|$G%/]FT_f3EEg,?RJbP`fFt`TzM<+c=//LA87Q>^K^F4PJS/55lGY"Xv[I]u(XKd;*Inb9&8YQSo<T?#*Ag<d]&.pAy)K_!hae3{17D<X~s9yP)ETGt6bP4*XRB1]LH?1?+Ok)&1"daX2AwB-Y[i+6(yc9V(]]FyIv9U2^lKa`COA8&?k}lU%]/~s%1W98&I]%k*?;EDf,&*j}!ZCcm)H}(Gqhc$=^/a&I(HQ")D:/)gdo:bbOb/[s(D;6e`4XT1;-f99z)RpR@2vc!)_zNKJ4FX^XCTZc>aGd=^k@8?cha-O~B4^-70WmHFaks}PT&Oovt`?qqNylLJ5Ulp"e[]k8[w0m+/__a}8T3#JO<&xo9pJ[F+
sK"_P>Ih[NSbkc(jIONdlSWL"?Rn<SSP]tmf}%Jq
UgQ*l[sLc,hvLa"FZ?+B*F0Ba=)VBg1qsz8a-NK>v|U}7Y>u`0FjV<Tr2L(^fdR_CUi,j0*"Zo9`&$wDSM%heV^!n[jkSi:p4,V[T`+!BP,jmWrX5g5Hjb9V4!5}J~lIO(6kXrL>nX_.
(%ycL&Sy{)ie*4{0hQBJ.Fa=7A1X0n.I8!xynt6yRDqs/yD!|iKy92A:WkodsV.9JbC,x0UsDAnO04]4pSv:ss7
a8@?;W
,xVCe#Z3Bgu<A*9jJ%_PCY"vNP1!QA`yBDy-rckd,Yh.y#o3G0Mo65JETxc!r7p9cnxFIkdWqn4")eD0k!3Bl@CVeK?)M}SedkjlMSktYWc3X}5o:INs+X^*pbgUA:3_^bMAm
?Q.S.[LDh_[.rxk+bP)k[;hTx|s@$
v(p`cU[)1Mb^1yeKJf3|_TAEz$[tPvHnl
;G@-q~/)&_.1+%$_L:8ox9c:Eu$[Y;Z$ZCd`4UqX2/2~(6s%LlT}&y,_]^j*qpDzhEBF&sUMs24+#V.
3_T"G+Fx:*e,<UZm_/Q`k}6b.$,{BFdJ!KYB[Mz$Rg[j?E0g]JwLFXmasb:R].xGd3<up"`~h.?#@I,Qbb]ba0Og4
<>@d1HfDJT"r/y@CG-"_?4Z|b``Ei$m6L1$(d_R`e#HC.fLfYp-Oxb9_-k>D)cX8,V^M,e9{]IQx>W.2==5gsef4b
D_;SPpmH#pCcR}c/o#]u$8bsW?wiqy$k^p7|?9sB!+5[B.OK057(l]hjiz/uz#<Go7>A5*HFJzS$Hw3B77Pn&6hgD*)?n?qrn=l`;mr>QP^.`Cflho?2MYJ-:-;nL%tWR4kam/OVbXV*)37{R-PG8(Gj[}L_:tQ&5^+@bV^5
{W?+fW><Xe+L^<Ce0mAQX)_gy@0n"P3^v6[%&%A!g:#R,14VvT<Ve;@ZUh[L;fGb$w(G3I2vp$2NHSSfyRX!uB5=+w]#Lklx?s:`ki+BYtmPDIn_*qs%:B*"4UV`N%qv<Qde>&13f5dOrk"e|7PIh;#k|jjt]B.V90)`o9eP"Upo":MNE!Rjdb0JTQ#B?93Qn%$>u8[v0V87Xt][;_
e9]2irF7CoktioVBV:^P:oCpyTFDX4Q
+>TjyT)jbu4ZYs@Tt/d`U/7yDZ^k*@I}@;jRg@0u"^TI[>^K+6V21v*J?+T~^2qui1.}OUawp"fP@+Jk;{IPCbtQ)P4L>6T]S@,j9GJKuXE.93%PRJr[Fquo7pYYCROqaqYs?R[*K<Bv2wl(.`S#Vw(5@$3A>~(*RRwp%?S7S
b6n58,aBNrS?:1W.&5n9!sN1"4"=$,,nFcIy]YdT&F4{o,*g!h%kpBAZd+*TdU[)(0]mtiouC<f-dEvG,DZX_x?J_G@W)>2DcYZi_1Y&eSM|ejDf^s,&<(Eu<G^oBI8VT%#oXJ;i+rPn8,$Au($a`+Bc.r(zPY7w;:&l[6aq7mtA2Y)~3W5~iWO]2xI}){-(uS<aswK9*LZ%Ns)JOh,XCoX=ZT!}6=x8eX:Z)%
FC7p=go2iX(v|@~%|
O+(c-:qR5r?WRyEs9AQ$aI4x1X$tGyu;?P{q[xRtXuN=h*"t_EVKtn7kn2p,q!XVBf.GFQ29}:S,iUu68m)LO6wj|o5o^b6m8_V#?d;"Gt@NuDOAAxfA>c]Rw!)r8kG$2o:Z@){AyHlhx!]xTDy!U=DIw,LX7PxN4[oH@Qe$TRtuSoAd)r>U@;e4Sm4f(a/lH[4aiK6@lZZ/BNkI2A7"G$c0I4PqO6;vx`{e@G`R7#LiQSnAnn~Nok;Ky&R25eWvX0wr65$[@yf/*"1b+rHO21L5CjH:g)0hk+w#(+rc~;5stNoXL>N7qihjJ<B=.@+u%0}F@"/t`DPpi:<1z#QO_n+$cB%a
gF
ARudyguwL&s?S3dhGBgy]Rd0-$PFm3sxt3$F9,07/_$9IE`N)aQZC
!jP^|-y#b*@]T`VGWacD]Ne0<[yXB7
s#Ckz%@&Cz*%1D6ZZ6.fxN_l3%0!l?`hrzW#V9="Q;Nd+P#JbQ7ssl#e%oGfI`QqOON.:@;jE9tW)sLI-&.6w-iU2wMoej=?GIwVk_l7wIiu+WMdY{Ar6L(C[MG5HzDZ4YX1kx$}9~sS`_""@s[=!LJD2)6.crPCrXYFK.39SLn3H:U"
!Rp1A*6xhmjDP9+"Y?;tyL}km`&FhDX^fb"u@!lDc]rNw,hB.g?Jsp&(fG:W,yp^?^>QCXU&[]nt^J}KurmJfZraiaUE?LoC
v6Lb?pwc-Ka*kD=-8#7GStwnPUc]o@Y}s^e%r/P]2zn.bh8zx&I8lt!NK5+9$9=]:stE#k2aH)`v;!w+8ky{1o';break;case'et':$d='"R]ALbP.!2M0mN&$r?o;"gh.)En/[1>+xko@079S<]8sH.%.68jl]C;%xQN^eeR^expn91_7w<.g;jm]F729W+u2]+R_d0aJe5vcTnGrJ4&!2s#ioc/%Cc?vU;W
yJx`v]b]tdf:w%#HQF_O^_VCckN]G
uSOt9S`]^GG_2A6)Km@)5adn_<S*
na%5s
A1Gd4IbMa^1LB7G>@isiUH[AcMx#8FBh)Ra<gkVKr/7M0`Ovf[;sASsfEeiN2F^uF|]]2uz%uz2`B;<OM*w"xTpHyF7K$[LMFVy[hrBy&4hS;onBc@/W5$]F7lw2y[n
-
R]&7c*Oj$dyjW+tA3R;3O`9L
9i0H_],YpE0OMh^2k_OPLRz;;vV>I^qbPl&:IsMXcF9J]?w0x-`KJ0C(Y(;&]nbb4;nk7XBr>3&52g{;XfSP"V)^kySmzLHkgkw)uYQ1[mxLTk9dqvOD$l[Vzh@0kJg<wM_/w]/au[lZc4nj*u3@0TSF;c<m9q
+*Z"K3QD*TZ3H``OQHry6[*m;c^[M7&NrxZuZ%<YMeR9!hxSO4ain3U_@)M0.=7urs
}<R-%T36oSsC:X.^rqlRziCb<nJoFt^hn
},3<;^+Vrrm`WD5QZbOJQJgoZrZPVFNoR:$M9f4VjaZTI;BR.ADB<J`YD$M?~c0A5xB@8xq%Wn}&&XHS0MnA@@Nu+JW:PR{<y*.:oF?#-!p/&SJ7^`T[L)*w!
$-j-<*>%,i[cac|0oM_Fc]@@+&h^".CV48]JU2,tTKtP@y~dSJWe;s26Ti0ByxlPGLAVHMBi?MV5<6D>Im=@e0d$5QD*)t=f!/Tra2j9Unyv<BMu-*vRC9neF9Z)r?b2P2QGq1uYASAGDiwO87^qjcA40-rl%CIL+@I1Nl*REB)4UwA-@rMWU9vj|"
kO=XVtmFe<t.@kl(o/X
HE?_`vbMwO3f_>sF9
1A9&P#Wp4-ss*L
G`a+nD|/QX+TDrb-h)FrIrq`1G<wRFVIM>*nSb)GNT_U9#Ztv^hk[J_-;;8a9br,Zk6,[/.5hX%Nv+SIO2z?UjFV"cqp-.sn=4p6i8*u(`O
Zt8&)h^B5UCyJ`uOw#jnqO}bD8{[JP&]Xk{*W83
Pm-MLZ+`VoryLZ36$4Jy$7(M2N[?fIKTWcgu:;kh(&1k_`X
=mbVjb`&"hgB[ep-~S/mIgjj8l|=%0FmXAf=IC(>]x%yQe&4hs71B;lZa3>:AivvY&yRU,#os]P/XO8N9J^
yxGq+Cd5^G@;8PQ_w3mLH`rB!;%Nsvf]`!~3~JO]2.,t9YqHijon>m~w[WO;8c;Q75?kS5VuvY$Fr$An$ZD(zvt61&<
3g&P&ER,%]d5J_.7P=C8}D!-oW~.r9h:P.TFc#7fcrVcq1cG&OP&%&x?k+Y3OPY[aTz]vWoJ5=JFL`FP<J4c4@$9BZ,sc?U2K+7v*oFqK9/d[jul=<![dyww_>&(}Q.fOsmTX/<SiahnyLtgv5%]VR?sm]t4|9E=;0+al4(p3-J`)S2MQjV[??{TUJmFLU{ib7Z*eU59oK%_sVJ/VKr"K[|]
G{M38qfOXtgcIjSj1|JU4Mxlp{<W?I#~$QUtP]vf7f6$><7}.v=]Zt]u1mth[o76&72DH2VY,Hnug,`+tw`WVdX1Bp$!XM0;M!C58ul)4A4c8`Vh<"e_9^%,f`^@#m.F$(jJ.Olx`+AJw:1Of9_mIh=s*B(Zt0.pYs`@3wdKPI8ZLLr:
ng<4YZ|R_C%>z0KI8<Zs(XWbv$3:Qdjpx-V:~$"dyl-&qLAi&VYud</`zY,*jOyd!tKnx*H1q,Z8A:j!@NKsc"vp[+X`Y/@Nnm0*VNKZ6$yE.xP:Z3<(($B]Ypj_iWQiK"FAYk[YfM:nPXq2Qxp]n<XfJNr!MSDR^($.Z@[-qZrKjD$(.al*{e_>)>?>#^JKvc`Jf*?sG#_[1o?XA#^GI7Ho[52xrZ>")QA[lvnk/?Y&oQ9T&Vf.<cK!{f(x%XH@#Dn!a*b-mrL>cg5mK=@?R#M_u/Fl*)b>:gl-s`WT4(:82F3jL,Xw%$]kNDq<L)<QBaUvQ^5"Ul$sHC$?Wd]ZlK)8B]^-$5nE+UoT4Xbi*iuF^W
`8?ytcCls#NZdWg=nzdxS9rrtt5+KC]Hf`KKTX+HLE,?Px$q8iOt9N_9GC]81{$Bxga"mC%H6+i~
ZOAfs?9VZpW:B(qWt>%!Bx/IDU;J:5udgq;Lu?lk#ycLdKz(@@vEsfYT3vz!Z#^K,66Em)?!{n<C~/qpp73#YX%bz#"P(s6&V0
b~[wG1`A>++dA4l"oG#)6[N)ux/=Ai0(]QNS<1E,+"lu_<x!ki?!a>g6E]L8;0pc^;3*+Q<alM]fQ|c[N0d
xg0W=6ifT#evy8E[0
yD2wk%F>O$![nPDc_~Ji:hVQ#k:FJB*gy[/1*%&dpS/98K!l7iKcyrqmv-PjUb$UbAg,D<e~i]=l.R&j>3uzaUGDo9-Ye^CMf!0F
GU9Ss^X0PFaWi
"dhZ{Cs?MuNv!d=&nFwlf(]77@Mh=EB+5MT=$"d!LxaI&17`o4DTb#)q.qU@tA[SqOU_+r162Lt:b!=1~!jP}8o1EJ%r;H~4$m2w%uNVf<`SOp)GUOoV<pdMnN)(rPBm&hW0xcEu<hO"G`4]84+j<Hd$VHoW2(9(}4kJ
6NmCl]K)8*o^eZpL8.&Z#B#`N.3GXU$t,r65,_A!DY>$7%
LNlr*$Z!l$:ZYtko"`hMBP$r:4-uX+aw@3N%}iQ^-9,t63U;+#9^xr(?%:K&_-E!3*eWkUVI<RMWSE$HDApRP#iI3T^)IR?4-7@rA"8o/iImBo-&^$AJze
);pO6:xUCGV
^)+~4cdaJp9Y!TS>*:^m9HNOFc5eg#(1sG@_-iB8:CWB8lfLH?`)?@FsJN#&yDt`D+d;Jto|`^5p=vrglnUOqsUiD&Jn-|ro,K4qSLusvfn#3MNO96Rp.F"Z>XID&&Xbx*Axm{4%8(f+nU).rETbF+WXE@q!b:IRjx/MFQ<Xi)NF9!-=CUuqsSF0<q_0vFjTtxu5>;&4-)y"w,Et?F2w
kS`@l;Om*Rny|&ppETpGiF1CcbiXT3*+h`:^=$V83W<PaQS9D3^Pw&{SeqECf[^$
<)Aof~V~m^<+GaZ8Jw7}e(LvgWpO*31M!MY(
&Q,L`2aR6/VrR/6?2Qb$9
X8kqq`}]4X4%[)H>]8#%T8h=T^v<Y/]N_X+Z6_5CC_6NBQq;$T1oBdvI6Y`Np8JpE=Dw*D()b5Xp!FF7e[Q8~;Do=Nh5~nE!Gn-gK)$b/S%<wW1sjI0<Ad17a+R#qn1_J<X$rV8[#M<>.!s7)YWvMg~rS+P(660,O"T
G"BxVf71wPruwmV1kQs4ql@OWGr!ol#Y
V%h9c.n>2O"^LP>h_(,<k.OIfj_DYv?K&/>GMC2mB/rC&`dnCN;hOS.&"zG(%yPYpJ`?;LI5?zL.N+Z77(K&T4HZCV%IV;(&`{pW9O*Pj-o4JmwPQl(`8XX?/F^j2~-hd/N,oZ>BmQ%BDn^c6
Le0xle&"R:osl_JJ"<OQygLe4a,$yK/0nR$<[Q#}/.X+&0xO[s1heA&_!3N]%fP`b.=n+Qs+.|Qv!@L?eV*7JK#N
lp8e*X)3?=H86(fg)N)[#tK7+&pZd[pv*Nm.1rA7b&K@P9d,Eo%5l.1sR&NFu<;HP+,8Km[fY5OMgtVm~uJ<CLLk<,VM_g/ZJ@Zr:R~f:.3l8M&wpQFN"@08o5G_^D?aZ!-w`s:ZPI(R(%1E^2ZH.BvrAU9:[xam2+Y3tG{ES-#[elqv"cumiSEC,xS%/4X_[66ETMG7W!/?cbMYG>5M?hD?i52),!IMW%.#,vx&m<0TsF]"O!:R2!>BZ><inXt_;QM[=J!yYJ4cs4Xa"w4<|N7df/H+XGsEk72eV<@Feaq^C#e."6C/AMZ;KvBnb6#Fge!)V5%&"Ug/$tCHPLQwsv(!s2XlH`wv%=3$g
CY)T]%Rw_v>*!*?$jBKQXf3mJ]<dk_^,Ib8*,Sj44E05=
M${-aOODj#Act>EpK6[d=13M^)h
TI+DM7%sv?Lcy?/pz=$bNiF[2P~$nIKoZ5TG~m-VOByHmK
Pq
#r90*+yfRx
M`Jh5/X,EGvou|t`hRL)Xz1ut3(tcmmuVnxBPpw!wOWxu;1,F,jaI3x-KvYwH+k{4T_u0=?(rkLE$$TxvrG,s0KiWt*rR95AgHQ9W4CZ@w5th@LrYTN(df+?if($wJ#3fF0s#ByM(syq[n#CIG_uK-LOa!5XJ9D<IKqFwuG5`|R{E@$L30
WwdSR,:Iy3YBBP(U[xec|XF
fKOJ9X-*<PUHY3!_+Q7%9O@D[U8Kf1.Mv!Shv^5J(&jA^Svq9TMoUgNiUP6M=Ehv8&q,YgtwL@u$j1VRos2k<H!7sTjK+XoR6H(JqX|k~@*)2IWO"c<Ly3K(mGpp]&Q6E2ZuZ;^E:<?Kn3PjUsfW1XL$hQgbi.IF<67>OW8cZ*^5[FCPIxYdoQLkR$"*w"VXQNnYdyfn=G|xoi`(!L?"#;)AWN/+xs[;ZeQpPEg3QOv/ULDVn_,K|.d0`RjtX$[ObBV_>*+i8+BDLs|3@KQo8K/Yh5yB|?>[Sa<:4yt;uC&%o,iJN)qg_Mao1ogD#uy=S*vRHDHOG+hB8`1^vo(oH';break;case'fa':$d='.c0@iaMD9B}0mN&$qTA+,K6u|C]$;Qu*EdZ1<pV_[,0pg4
^b>&-|#E0>$7I/!I/:8jDB<>EC3)[/np5JEgq..duxrgA~Z*!T8FiHjdvx^M?Whz^UaYl%mXsdB}=y#l,m;C.xWNgGPbq,=mYc2Ne.Gw*=W`4JKZC=HDlW_
A<v[*3H?(P]9twh1
7r^ORQlDRF4-@#V"k_g_18yML0j?7D8iN+@!|XzdDH%nGj&[0G@Q^#
!f`
pzH$lK(&n)acL0m6erln%r5_*2<u>gN!7gEOkX1<.)(j%FfvP4_agcB?w@4uK[MaWc9`!we&O%sA[*O[
Vj;vVX|InO,wrqg
7jaMaRIRutOfkn
[+L~LvtW]j_fsP=FqivVSNeCw}6$4;f]xRtTt7o$xMpDuzcIoBq*jpst_gnUxAQ)8RigfenW4p_Vw3hwTAc,_?C&Oee_vhD8l_X]Gv9X*.lQ5:i#P_,|)/>o%+KEhy8paHhZ8
0&isftBAd|%tDs%lkyR8/uRnjVZh%>+twIuQ-(pxJ%2z$ZqN@7SiFq2O_b!k*e^Xj6Y5189cJ*_>2mS;pYd+g%AZrc8u(21*OzPS!a&-OPTe&fu}+U4yX:TzCtJ"*GsR8HK]*JCR:4OiKT(-YSB:J{U|@o*i25^V."E
s,V8Ov,*3.(DXN0(v3&PQSk,gGuM>K%N=6OnMolhCQx_4`%9_74zbCE
o%P4DSa8-9k;r)BExYiO_r4qk}0jU#,vmryvkS[]dUhFTf`<m)&.+*e^
"vnjq3ph8:@*U-_vRaW<U#Rw213jK,Clbb,8Do^v1D
cD<<5o]2agb>=RMm]vd=]AoEQtF>9]2`xp_j=xoT3A3=5q^f<Njv0p-RfxF6y^DIA8ZtyJBFO7"Z?-O<ghSQ,J6.X[H%8sk`Pi4%85L&uv;9-]S4@I0R`XI8V7fW=Ae}56q+:VCK6%:)5mg^(YvZ50spVN8?!wU_4YH/UU*I]csQ=NmnR:[DiBq|7|E^&2=#=JLDOwI!Yq=vmzhtFoo|%#aG/{,_3I!8P`5~<O5]nFNB:.ek2>eZoF7E]YGKO*s`jbd_tB1@kiicL!p`^B`Tr
wt4Pl1-!*k&%2<J_7I6M&FCB)LqK+a/RlZB4v.p/.TdEk`diC==~mj7r/M(}l)x;wL^nu>LWUIT+a`Y=%9y:e(%r9^&=2DMp2:"6rYJlXy"p`$SnQlC,BU%*>MUN5yt^q>?Noqj{FnFI&[xUe$O@+djd
C)([x,sEb>K.1T<6t2j7iU_,.,Zf5&fDNA~g:8PQ)SV"?
;]Q1WP(ZAEq?P:UM/`w]
eS3Z<8rjq1xO(g4b/-</B>.j<Ffa^1hs,uZv1``(`O^9J]kqP*5E38,=L?*KI:DD5C>SCYLp&xN]6ZUtG"(93S7ShtjlU+ka
QboLf[rq/
g%l/*j<rd1Z]u1#MlB4fI(d;UOsgiDyL-pC:"qlWaW,6Y,"y=2~[!EhLYO#XO=MP(AKR%]5=8+Va"dEhHMwu9Qvw4tWc4JvfVG),nn}t%EnO/A+(tWMwE%I-VnVxQ6ft_Y
M<jd2NqJo
+LP0,;*QIJ*m!^9Ld87h(WsE8z$xjF`9=h[PT<UQTm+j%W(Bb6Ok0C-#yX0TSi6~qxll%nU)KIO$]e%@DvTMRh-[-u23rTFt<NOXu[Z|"8VvyYNP?XM!BzSBum4~>fSL_m3K9
q#D)pz%SOU<%sDAPS=Bhyx&XQU$CGHS_x_A*u|Tvc3go.cVC+e9M`Rhrm|k{2Hajc9b}*5H+mVvxBP*z^p3eh(*tXZIOLKKl2Vi1K_dWy_cj>kyR,K!g(OHGMdnWIB,~>=Y&8{5l%NZd
340O5*<Tlf1gMp#+bB[.%ey/;(XkN%cE`w[_%@(?PFcF
;xet%z)l_Ey1$gfGxbxt-tg^b83TJ7t%!*&jj,kaI@T4lgRJ^S9:!g>zl
3:(![w@.nm?_:,,,c
-P7ZVHw&IHSi(q[At}Wd<i9xrfoOf*`DPAtWu"[DM0c93zPO*87IpzY9;tJ5do1F>FX@ullo3|Grr"/FaTkk)LH:;y$R/HR7c9Olo9Na=H^?ZYq-`,f9@m3}JMWb$a$xEOk=hr#mAC<A
R5d+,
8q&)
R/<{rq8y;:Ngo.+|#N;]P_+O$|1i?s&DURY#sz)GS+s>bGH+]Ow$XJPC,q*Xe=7PcQ8#PPyrp?N!*s=)BTcJe=`vQ*RxrTqqb/;7@W?BK{MWQu&7@TZpuZL0#z]aiS@Y7psr]-1>^|yT]h0s7U=wklhI&gr4y8&&/xgY0m5pZxP#&SeSTZ:D)6D}!?EvuXdVH^HYF-G9vmw<u,=6T$Ft=n/,g[=6$-W._mS-@qkbg2aU#y]pWKvimES[Wc,w3@?_EN^4#Ft6CM2%pm*:hIR(a*gFKZjYaRF/Vy&dtp,+njpT4wNKQmp~pfCS4"
}<IXtp/tg
J>BhQK|9`Ez9,.!I-o|gl#@Yb9EoS@2]@UXQF_nt}8@`G22<!HxJ)1QF}v%ECA):RY0H;I/b*UkEt*eFkG1Hup6T$pGFH.0FyWW:b.67S0jh.xJ1tWF
=RkNucD6iHfCK
/qYxCk
&r30QV`Sj251bN]U^Ji897RQyn/8,hQKK{ye&p,>1R=f*q:<3Nm+R>vm8y4i0a&.Os_9k?_*-lS@3"LI*G#l,%aMN[2+%MIUO7ERm~86/:&~G6K,rQaR>M;w?&VK,@
wa~hFdRBv-hC[Cu!&EW_Qv<M2Srthp,<%Z]S{tGO8H/0EbP+t_Q
,l"H
ZFPg>pCF+;`^IgcRH>V%JGA>a{-~7=Zun(vut8PQGqhrLA;2N2>t-(l?AX/H3zc}1icu1PcI6
17YV+E7{ltO%lU1r77^RUIjVi;AZ
;CURi%^&+@of9(2[_TXT+y)F7ok<0&
GtN4f6ws$@5=E~O>nR*MAj1+JcTzUqZrPz
@.;7cP}/PQC3&s)U9.Ga`I!Ph8*>fETR5cQg!7It@M#
~)?b4YbU2U?cQ$wT0X&@c)S9C`RYOk2NU/w!6I#gRT^;^+hW^&D^Z.;SvIL^4j^fa5(cm&_h#<(P^%Dpq/K]~PU"GD:^PY*OR8O5B<rrar8B*8+fNPvimT
"RiEH38<V)g$oJEeTnKJVKOue81:)!JgDUIjU7,sRf;zB*G3i{ke([W#O9QwC?v&sk?R)Jx^Q8RSeZm}]>1gc!"co`_h_.9[x$:rVt)(by:^NAfDM
nUAN-EN>2(tXI~8x2o8jw9JHkpEo[}p~y@w2-uo#<460$;h^321i6;)|LJ8&0;<d=f!#x^U$[t;5bV7IB-xlT:!W%W"7/3YO_i,ls>mq&TZoP^1Jc*O|8pBLyNi{Qg6J9u0-#FTbvb.5jokoe2^}IQ@/>N"R/G0u"bi16+)!yGnM3OsVV*l31%8k3<3AAtmF:S,mn2vxP9Md)C(5!kIcsgHje5k;/>q"9=ZFQQt:-YQ$g-I@(nv!a`wA!IJ+wClDF`DYa-2AUZ,|a*%"K0(9Dg]{mEVPMi.+jxp(g[CAjl+6qM,*VAq<mO;v?[:1bK-Br][%1aP$O
a5Bp;k>Mmar
$~Lv4c.O&+m-o>3bjHS2K&`Jrz`otpGoLA_#m)y?l)c4Ph]1"WeU[
vm!"6E
8>#USm;oEMsu@<MGK.Ox{=7"E#6WE8w]pWd^i]/mV?wbN>&@%)1dyyJ(vH)2iiYtAl1a,m:KCIk%"5_H!q?dLWV*t42td.w8Co:J|
*Q%cs"R5H%R>+0xETj5tfuE6/V3**Q=a^V<bMc"q%ajMP#`?q+VUu^>LJH0ia&%^f4J13>_t*66[Zc#l*4bs`S!Fx^="7>POwY$BIjGo_
fKaqw"Ur7m/j+_RdiC&[-&2@!Wc3FppseTY0P^+HpY1m(sYpQ4F^eg{D~
(/S0DJ,W`wtMUGF1^"%uN18/cAvrwQZ4U=13kk[9so*a>-$A-M`cImM*h-.gF0BYJRX9oM4.^Gku}lQgkSkL7X5%f2/5Us;WTCa!@:kXYatRH_|=Ik[T&,-W+6X%{t/qZJ145A4B6Hf/@c^oZopT
TLMTo1O7B;7aIFbXQbGMv):
hj1p/CkoZKt)=!i0(`o29AQ3w>ocBca^iye=uCudsg^al;BG33CkKojApU1h7vQ]sa/81Xb+?Yi"yOwk;xg7&2Zo/QMAFo9G,~R~/}e~iexF<N4&2Qvd^nAu#_/J&_UN+Iq>Mcrb%&&qO-X9
ygt;Gsd*6F)cGuN[mtG<c/Zw@qU_fw<s+JJ]X1[_h%`t*2k(jQag
X,TwL]toJL+W>lfe#UQ#.cU?&cL/0z";7WCAA2c`,V`PqrJt55$S<+hafj`YStwScAmD]Hq<fDuZ?:v3
-&^8?`$cjUJy?KiwJFlwxkhm@H7m13zAck2y%LqdBLPSbA}=NJ2+EU#t`hXPDO,wjMj4gMBkEnd%g]GmB5e*{yq?xx-)%@W$Ei9]b_!Nu<Uu2r8
p/O.q]%SbF.`ckh?,?ZOzxEI#jaHSo[bV
]WdA%K@1e/di}bwtLciK?>!Boj/8[Ek(C`sp9@zTmnw,ca<%ax
?9+(du[J2?Uj[@!~GL2W)9jw:KB(`ueosli:JjSe$;`H9AHMZnXZ>Y$PgDDR8y(YW7iVnf]oT3+Ru`6t4-^R;nC?qLt9`T,A?]bT0N::QSZd7?/+e5U@vg>Hhb8@f~@b!]E!`;mr&0)kV0-Q*mY"p[N&';break;case'fi':$d='*Zu<^5IAP+YU3d0$qYy`6X?^>//PO
>9]1<`l]3e5>4Q4`/V8d`/:s{-4"c!!K]:$dCe[X(cZwdb1<OU:#G!bPDZKF%s30_l?HzfSsnLJ$cZc5Jui,~ns_*UH5Ed;#wC|JFc)MQXWhK&)DsBqtH?h$?PBOp8{@Vee/<*lr(5~EAgpA7j=Uc;pL=vxmM],:@mCw4_OM
+zQF<:kunFL$4I78`ILYG@bO]lc|K:^Qx[JMj-h>H:
[B}bic.Q9<]*/`xUqjRJs5/f[K*Ejw=ofPF!wyE<7f1pGz%skr.+ZW^DGq%usw_H=WPxWARI5PlK52n?R8#MA6dh1%[&FS=3F9v!-P1v"SE]Pv]FyX:(Q"/?t^?^5<b^4?P8o-Jc4FqShb8/nGQ0y8fCsaG^1!Qc<kC!WP#4m
F`o""nE^!yb^3_f_#Z{,3b!Lwx08oGm4lkjT:k:JU@vP{<+@Xf}Eo-Nq~jwX4dkcnA{[|Wq]tBpkt"0KwYHM`T}k=$D1lIpVsSIYMmJ-B2><I#0vzb
j%=J=(%B3v$[F03fj+Q)n<rgCFUAaz"ZVJK*Q}<JPxI|1}gLn.[HT/uuFRF)iM51xUAL+gEPZwxqkpm,Dvodl#uTZ+oe4"S0iU$NubEb,FMR5vL3D/pO^eufO32i9,]n3b!%rbi_kfH*
uIdDt7Fsi<^GeEn1`jr*
v-,/H<<=^Sv75Xcf.;cv/^y|pqGrB%lUhVmru[8X%>,;"m+9y7`@rZ`W6,=8/I^-(/^$/6-Q>zl,fK,[hDe*7
..:HdINR[^IqtoZ7_>nnF<BGh[oq1-
F+Vr7Bt5eAX"A,*(!a*inr)pOO`]*w5r)cWmXpT5DT!^+q.6yy>i~n5o*2ixB
@KQ<1jV2qq<Wp3Cqvk=29&S>3yeEqS#:qo"^zouHoTnMv49i#=S"Eyb#poR0<lJNGCbboG<?VhNd.beFkp5X)qN1`d3u>rfwL(8S}UD?7u
$K:?gG,Nr1EGuqvipD$IOPM|l"T~K]aRtVbx_vpNr4&Y;nvg&n<O+6CC9:g[p%L*DmTj,*?Ef-y:)".HEgc{/X<3>|Ph.A:7y06iKg2^/J/W+aG.r%X1aRB"/{llbe=eZ@Mys{UO?v(n94BF4493Id&_KTz"bu"U@VYmDcgP=v:}uG)cN43BnM+!mNb$v#f*D,BKs43A,fd`OGUWr1X$n.Q8M5lo(?8Zq4;eCD,MAxCFx</D1MdoJOy+X|eAAatJ2n8<</07!{<k)5p(s66"K;8bsr7MSB3Zcg2TBT3?PlQ,%RQc%6lVNQQnWDct_Gkb_
"-b+;q&HI+uQ%(H{qI*P^>tybD3s:ybt3c+<?+(MxlNCkl/uRG]bJG>F``2nQ0[V;]wbn^?i@N4kUGwSw>
x!B.u6WbDW,7#/_/+K=^/>==*=]F}BNyCa~bi^P*[1wTvK[n0DQ%m5J/?0nh*e}O^>bb`^bDuD]C`Y#)07#TjTEgufc_A+jRhnLfgOgoJgp77TD?bvXy{>d=dU?./(B2xB38T*.1#$ptv67-K5GpK@nUHaN,n,l[*4-U8/9.xPaY+=ULSv~j8.m<fumW]GaDep96uJaDzPp2yWkWOZD7E19qvG!wL(0Bj:yJ
BeG(gpXvKKIum#pmoMuK)sW]A*VqVClRwbpTp0VG->W"I02s3}Y5A*77`[3.>*)efM^8Qo8o<:^.GFRZT%6"qe-zo8%Ck8N&J=Gd%#2VAjW<`J!Rd.<A8ZWE:)2wUr9?%3/
;/)Mf5pN?F5p>0fHqdLnlU?VD*b%.&_A3dz&s0v1XuWjk<<xxR(1<}Y,c=wsO_jXB=x[+=05rkA1(d=vj
0L=1Kbw8AL,w0ZH!TF.LPfpvdeN<v:xO"!eD.2ex1ewyP&8#+A&gNpX9%I;%6.2@HdF|UrJ3>t>~adnH26BW%v%OvFk>PGQ9)DM%J~kJ*rpV).b5n]=eae(8="qCL_@:Q@nbP`m
Rvq~t[G4=S#@H".d=uCM3S!7>QR@YHkay$>CbFm!@1iZ;8q$208{i7Ck1{DW63qgYh+vcS$+Z>K%?Y
FLw_5x-y#x"YHfW,,K5vW;71KKB!:Q
0TV=JYrN_U$i=]`W_3^y?2Y8rg8lX5em3zWgGA1q5[:!IS?g^-1Ta
)Ox&;IvoIZ@k;43A/c
3SYR"8T8Ov^pJA6S3O
>d+&3B^PS;JZG6
wVc!0jCBg[pCb7C_6_"5<@z6@xoV-OwFYFJu@q%FnT|imMk;qU"O`e,"@p{DYp,GuPvo1l"Nh;=hF!5gqq4pWf}G<jX3IC19s6>K{_!(Gf3q]Npw$X&R|&J/6jNpE2^,|PmC5(Uu`Fr$3b{OKIiS]QfCf,7iw`F;*1;!In!Kph~tMehE1#A^LID?i$7:/U5]MPZC0E`g]RiX,d}/?A&0^QI9"8yi4#4cUAC3Lj;:wX-72U{Jc-%n2Tsfh*IOlXw^blac.mKgMf;1zh(KY2qKK!L:
XM2:e
N{?x.y@y4-ipIX5#5Ex}L7HGus&+#G9IQ-D}ojThyp@d"K==^wsS#aeOV
@Q5k`sC(xoC.h?/"8}FV$
@2eQL(j8.5KptU_`#`B9l&r1.Bt4V3bBT9a/.,&-Bz9}]6x
09/0SWiIFpQo.apestcTN0YJWU.wnu$h:&J+EUOUioNWwZ_UX"<]4F4
lzc!lHk#J]gMbiOBI=%%pzM0F.@Fv4!m/a"`c8u|S,)"7&jZh=^1lU]:N9A~m#jPD,njc.(zi.ajZ,H$`+?,f#Ls"}b(i/m=C)(<[w?,]v"HI,Zv^$C}g7%/]Hf!T;Q;oJ!O7kCh^"i;gr.gafu$N*&2^$DM4ko8#PRYX{Y`$V:j;z2+8~#O+T6,eDW<*Yb!OqK4ww4z8a[Y($kd0qQ+ftd0e6,!",^3h{A{V%j-rofH/<@`XB!>1[/T<3q(ksOvM8h5DYrk?6o*DyRjHENx:d_=h~_~A(kF^$(X&=FxT@knH5AG+waxGu`^sXw2Ur25Gs.&>@DEF2&{&Hfdg|2fumFbhw.d@]r6(e$GFbJ#No#TD7ludv6X2Zo$:q3L8[1990xu##2z;i;oA),cRJLw_0=UL,bEMcUy!D*Ql/Kdj8Xh*AK,KCI$IOh0H)&EG,_Lkaj9WeM;nsd<@?P,uY+J,}+b/{S9no,VF:;,bX"e,]uukMv{6?"e%WWLDy"yB<RY8vq+vr/uO4JjQF#xvE,/2A"c?OkF=z^I,Y"YCk95.<LQg<A[Sk6-oZsaBe`E_d@H*P>@07fA*sg6E@,<Dms8@M<LW.hM49pO>nS<lc]E.@-ZBT0-jw
w0NYg&MU!B$?KlXU"v/K5tEZ7A[owfLFv#K_wK8@m3k_!./5Q0Hjp*/3wg/Yv*?V-b<+N
n:LQ>rlK.Mks2Zj0
7)U[Zoi[_sUs!Q<%M{t|QJF/e1b<d"u?r_*gDWrC&&X5*RhECpn.%21C.?A.aXt;7^TVS{aQL^dod=(JL``v87eCXbQ$>3t0<8^l?("JUmCt@Dp`V/>%=YX?T%k_jcE24b*mr&`iQ6i9AETNy)XQG<sYt7H[2g5[6b5i[LSK$|
j`r/IMx8v`,DH2"#nWdi~k_a[G7X**m8rL(oQ$&A@@iXRiH!$ZjnWb70A^ivH4n(@)mN`K3$si&oeC-`f3XIPY.=DaW"IDn%Lnz][r,M"bV5dh0Sk<M[}[h>!xHiV4:1VGCqW6GU@n$_QBcJUrNy1G7_ayqy=fREzA#VF,k1c!mj^<pxKck>tez,{iYOctw]Ve?pY7D?)J6F-J`/O?@
`"&C>N7EPMH/CH5!_@1p^IU;4OxmPJ^Wy.(`U>6<|ievmZvf~3`7p//e^U*E[nr8TvEjwQL^<>)n482$%"YA=E%F*Og`6S-M?.SJ&;[NR*"xZ$~kY_Uawm$<dx.<.S9dGpPYza2nl.
));Xdc^d42-DLG9kvh4VB9*QksI9(J1SHap>AjoaFSg;g:I>rH,w1Y]17rBSD_/NKmEo@=dIfO>%[j)oOp0J5Mk;nSL}xR/Z$
G=+F?1D"ICwu6&`XmtSK-m>P+crc7K/%D8eOpq*R<1:eI(MCHcN1jumKG8.8s7D@fRir(!8d;~sl2}SAdhwx_-TGF:XG>Hg^*D3(%O=y.uj!L)dp!`&6K[P[`qLWt"_jI=l!.EcF2HD#u)8^coBZ?hs`3H+2k[CugyUi#j`m5Wwq4@Izbxhos1y&2K]_]u
$:($$7!Q-Ui;ZP3r2MCSQek(jisSH[Zd%"?A])S%aq=7Q;]s24Em{h<kCQ[E/IRUQ/bvu>;$at52P[dqhQ`sx&aCK[%qEhBIBA@hB,=G;Q]YFHF(tUq<7JY@U>*>/x#x<blYOR{ZGux%m8
8n2$G9c>!@?q-`%O"p>Xtt<1:+n.=/sPv*jnW5T1ofA
/@!<CB.PN67ioxCv+^.[w[pm(SR`!@r@0|fNG1%(.rg<`6[8&uj@@~+F,p6r,LG|beMd(cR-!>ADcEdvJ*M<M=oFy%F*H1w?ui,iBFN})o5*:x5^
%3!oOFJGB>@EO0"uVJpxA-PB%_MDk+,m+1^Mv:1y`&A0%M_+bF_$5wQN]8@KTSLXI%{jWpWw}8(+EWr)@""0D<(&S.%bH"n"a[+j9Lymp?piEjdKQIciVyhK=';break;case'fr':$d='&ZuATbPD)@80
Y,"&?UO_qNZ3ZK1h&r/|]%mlLLBcPRSCp1g57%W=y)dEg}XxFl`f:!tFvUr4MUInq8&5
{[Z""D}RO5a<{Xjxs]RW8OA5Bv_oc
@2L:Re()x
6kahWga
zu`AEph,bNx&Rb;91Q,[,0EQ:?Mg&M;1#?T+r6h>J6Q6(xuqbF{Iymsrg<;"|XoY1Qe8Lso
`?b
!L*uw(T*i`77.*|Cv3=qV?X<}
^?"2KW=
a%ue/;}v5g<hCx;*(k%pu.a!K_yG,tZ:?p@QWq:4}Is,/UQ!#r%Eiw]1<EU_pB}SDf%`ycti9P(9|u%;n)
y?ffsq1Gci,eu$tEY&]>xa96Vu0RlEM4c,^)X0n*UPJ+U<UyKr`=$6V,F2/>4lv*L&_3QufKE^LL>,=,[:
o3`+DV?pOE)F,"y/{+W[0Om2}v[mrR$P5uRQa*<<}:H@2]`W5i->JgZShO"]>e0ioh)Ls.[ULD_ZW$1QjHvq<tU5l3BU^82EVSJ=><T>u5,
If~&Z">$7?+I:ALp`(xr<^^RqWLJ)i=ntU,$;8Ff)g!3_N)>z+g4Fu?D!ZtRfi
>0/-Eh1b%YYh(}S4.Ul5)kC3MO]T_[0Jd[QMI""Sb^Aw!Y`rX~Q9V.=9@$b/!Ic7xQ+/
m$LN(_C"-FK5NS$bnE.C|`LW60u6H_-),,$@w
,]yPp%e4)DpF9;9TD;`)Rr3oiEv1*.o@(Nz9)#mgxdSt/)#@z@fS*ajB)Y"]Rv]D}K"Ljpb]M``LrPio6/Wm;cFvx_BsjW>=&H<Z]<"RFx+6Y83;V
>M,%5C]v$NDN#Kel55Er((^T/=ZH62U3h;orGUQgtQJx*8k?c,mD:lGn85zfehhG3Mcn9jE6HU1j%vN0u!Eud`kq9f
H)Xgs~HFmFh)M2aa4srPHc%G/{92;FX~aF2G##li3KguWT2!maB~BZDfF[_B3aglV1#I;lt|TXJ/dwR5)xp_n4&lp-c]mUMVfu)UJp@JDl,YIJq[OV9>thF_6A,ufQV)rDxbQ&5<)}O3OhiRMJdh)h1{-]-hcu^o(CmO^:!$$&58eH*P0hB]WSZL-_C*(YeAo$Qu)eG@jL[PYXKrv{%:hGThD4jSm0h<.bWqBY*[b{#o)Z:CX?S:cp$F#[^NWoq>F`EX&6CR=z7-lbV#]P[W<9Xc5
UN3[;J_coKDf&D4ol)]F;H#SPMQiJ-bV@47oVn*3E=JU<fwx(4WPJYI;7}2_nCKWtOhGE_S&]8,6FHu$2DW4M^)32vf<C_FILC&T1?#;h[o
Ew#-F[cXySURkB[TTn^3*,fV;PK#bo1FD<mrjAlis8H*:"y7;9O)KXW,B|I;hP.[ATm@AM=_Xxx;Dr/nm9j=
E8q*~y+[)9ZA|S_LyC?K4:mH"b?)kbvpbi/F`Pj<>*dS~Q;w/XJ<tO<Y<>``G+:`_)]8a1|GZSZc]LfT;]y(MB?op&F`uT[tfLwE_xBBp*CC2qQu7cW2d.IYSZOZHF4i+I}y8%v`;0s&(E1K{%S/D"#7&3jU^"`nzhAPK<$ElI#wu:Up_.i<K*eK)$YD5s{2snA;?0N27T]5c3$J1L2kMFGK;)M^Bry$i`S/a/75E&Ms6v*KA%[RBt{>&d6Bgt
V6.xI"(_5%THS,X5o0xr5GOQh,!uTJuSE=i/=
"4
3_nT9I.^>NWjf0ym4hlsJ-Pxh5Y&y#f,dD5QC$T-s7$u+W3=`oR_=>~6+2QYI:A4wvCl:FgZ3)8(0t<C"/!&eq{t>aSrn=b*%h;;/h^^p*B[7BFP~
C*s6zH{g"
?NDmAS>6]gj-q^M.sL(($KqwBEQknT&c=Cc9yH3:cf0!uL:i!<$<5F-Oh>o;)s##-ixT_g+;:=:b7#`G5yVyDuXyW(rd1,DCkpbXY3!7S6R@KpptdL2,e3J6@R7Z>U;[TTuCD><t~[hDb!+>/a!O+FMV`V/q!ONT@;HGf!~FL^<WN3ok%"LU2L{=O=.gqGH:8(7g7m(FT&p!7
bCx">g:a|.8Lf;9a3
xObr-0Ua>g6Z0jH@qHGgMnh#_Uv<V8DcpLRA4bGjL"DwcZ.=$(%Pyk-LG`MY[<rCHQ|C,&pa&)>D1XhZL[hfL_2Cml*,@X
UmPUWG?)Lh)ltQW!T:LWIHF@-5DGjT)%"|E-uT&WYRGr^ii5U@NSlh$+fv0,"Zf%Wc;p9cjpw;MU%<v!EXX&J4k{<^N2e^vYd(Km)iCMrCU7(<itD#e[Psk.I$//)ip2n,j,MH+HE<-m#|t4erXLFFX8R$Hm32C2sTO<BO%UhWGC(C@/gWj%;]!*lQP`GBDb&|;+T
gp]Qi(t2;&Pv@6OOgY@b&IVa[kH7XTlWRD,pmn7Awi7Js)?K8nfSl!]T6N>pEQ<a.t)&*lb=>Y#;TAr<FCUVmsm7I9RjYBG7x(;EpOn|ae+oIl<H*i
&XO)koLq
=/OR[0r,$~Y=1%ZbicU
%4-=-VMAPzAwg|%^DuNM+.Nyxj7fZ
J*UP17JU<9<9wy(H+2i$=vT(h*)i(JSs2(&cy56e+5sR1A=(Zz%uVcV:Ke[yM??
r{JGQH[Xt?
Q#A&;%Qx.6!u|?"wgjqEXs2*C@b2L95Jqf-Icmhlv]#Rj@v41-u
5rJPF4h?]_9E.uYG%ltQbOnYcElUQJ]JreRgrQ&(@Rk.i?oe(7X*"5p1.!Zg+?|2]gJ5lZg5H936QBefm7MdepNBg;t7e6WD{9@aV-9[:#Q.!
sR!!Fq.C=;G%B0|>ba0lqP8Nnpu0fD?"1c?(T"F;.`Wf$>>Tv;&0u3XjZjLZxE-I/!g4|"Z6Z>QY?5@bpwsDbL8!RvsekG|5|)xnLH~07&pA4w@54ld&}A0[}%Z#e<67;DV*T<"5MLKS{qfk"Ot7+PF,aRjN@6c7HRlou^_5Z.Md.5e;p=4RB-=tpo$SN:(e[YWo&jM-CXG:-8Tn_pE"b[f]O2d+n-(&?e.J2b0*)YYRK^{#QSD(.@JhFx-[XW"jXaf.m2W1hMSv[ZYgP-6JeWE;)E[9+$`EP/@3is7x$%;7-uS<5$Aou9"9GE=fWpR[M!=JR/4N-m+x<GdX(wnB!"ifU8$jA6|NU9e@FbAoSW5v?&f=$@z=aJ+.oh`8!VY7vv|M<WOr9#W$Wc%4XPdsFKn(]&:+Bk-X!XZn^gl$o-1MWQ62w$LhplBci5d1O#-<
n)5>mcH^y>Q30PV<o4u"4mij37?=tHTvZf8dst6;_2=2y}IQ^c<+a]#RZ($N,FY(l?w/0
lh=z8-%e@T+t56#fg9m8.XF0f/beMAIYS|_?b00dj4bN!}BV.fCwwJ0D]d.*1P<*-$Yw1,$XNa;)/HFNrk=N7jN5r{!}]52l_a(&+tqJ.:=q?1X5EfwYnZ*yr,u{DEMR`,/p>;&:CU.!lq#[1F$k@f%|AX;Mp}V?Z=T`3sexSq/U"m:fSPZoQL
SQ"$3!:
&O2a&
kGv3D0]L8[tp7h,08/(A!Ly>X(xHO>zN_+p[z&QG+$B
/<$>[AraC&HJc!;G?N,cD8(#f"6d[G"?{0S8j9:DwX8!a1N^<J+A]Q)xFb7*G%R#gDC(bl:9qpnF*Xye0?vdx#$RAh{))/LQ#%E%n8xrf_Ldysgu~n}PpZLF(QS:lHd,ag-KCKzm6_G>"N(:e({bwI)F@l3quAqyUI6O0R1CAAK";%g.AIxiX_|gce#;Ew*-`]UP=BM+~5f,x@0;h.&4%=3F}n^qV^QS%B;p1G[t0rQK"B{ff&<]1IXwD7Jxx90+xnA-kORBB^H"+pa9v?oP{]>UxYSKMHDHL-X%17Hd[;3Jr87H?-s_W)J5l)FB,t92N@jbXs>vDo+/Y;0_J
00-8$P{C|owhHPtfq,$:<fQG@)3&=Xd+dw`w;(T.KllOHH)<dI~k=JPFmaXn?<*qV#0HfwMv""9E%s~R
d8.)5&9ad%U[9!kjlJK?f6EbjI*R)GuHmC%RSDm]U|i4r(GFuM9|2b;]o|?cm}mSja>C4`"&f|%&$1oc;PQ6J*;[0e8Z_9Ck^EHHM1IX+zU6FGd65-H:,y,bo;ufK)B$qX,-i/fR2V-z`)a$hIr3Twm`ACJ3_/EvLA%drdy+frUgw-nA1Adoq|y=;D(2
!/g!{ymbB$]fmTok:y;V$ATquP0L.C$L1q(22,Djsg8TmQyl{a4ag&avga8GSrE5]d02/-A#/Gh^5#;dl>aJ5m3p}pY<l;G:exePi$
l{?tF$d)=Ud?A9]<.V7B0-p*N,%.i;-VsnbtUGtC-`i3ZC`/i3IfC[%)0}+PrV,pKIlEvQ_?F}#JX]>Mf+M!IcN+,-3KOuYYF9.S,h0foa(7I]$Nq[S,)zmuNr4BAx,mr^c(Ys1GDD5~y_/!$Sh=o2=Q<0,uQFm&TF7Jx$wR7cN]cp,yPiBOmA$$2-awz$J;,uF4(y#(*R>uS(w9?SVW)6+#gvqTI$B)cP=&D?#VeipA_embnZ]0rnVY#$i]*M][&R
.ma92ZmMR(,,nZ|W><JK"S[:Sda,{!}`oMv%=)*nA@h4qtf<jM9Ljy)"58I$#P]=Z/@?n3ud$mNTLqB1tY^B9"`V;L&AMqy?FsQ?27?m%XjH7F^7EC
T1eb6XI0gm:]-vUH%#ns.4Mi-uRox.w2<@^8Y|NK2}S4W*?s=]u;Dv>{`Ea)*qryybez&Z:jdUlj1(,TPOgl)5m
c;BS%05*s)cpW#f=)W3FJ~O9)O8Mg^7#9RT#C--4r0va(bujNTp(8$h!wcu0$+!=P}:j2
1Mu.
F[_/jTKn"]o``[rOpLjsj^WV%U~c2VL635^Jw
|/0LYAA5Yw.5fAq`D`7SUE#`UR|I#6]
4(=DZtepD>Y,c@Rp$G`fr2+NPp";HfQ#NW^93YZH
s|CScGo(NV';break;case'gl':$d='+Zu@r6LD)*60dN.%AY|SpTv5;<?RLX?/rSkO6Z
&7*3i_B]aZqE#dtdf?=
SWwb!G(<">I}HK;GHFDe/S
tmII#sIPQ.+V-reB9s2@#tPpXRupKIL8XjXG0GNPe:
qp:l?Kn1Xuj9`*8wBGvY4B]5/~]mptk:^N_EQ@&v;#0h>M$;4E^`%TF<h~k4@]`DR$
u%]cT_E3`y,h%Vc^bG3J,V%6!()%r?3my62Z?pA<3L,WCP*j
fd[!f@Zo4L[nYxf*>%F+B_KIx
7`0()2>Ay~[[r&kXBlohK[bqMFxby=7NBL1Pskq-a+,5
eB`mIH=*kLwEUp@+[u8=nq<<9fUtM6RwxB->*Ubk7j4v35r
TUs
?.62e+,R6m~EvUXjbas<#+(M4b-
{)g_w3oD9nB!}UYpa0_uiYU>n8]%TLT%aE{*eqH)JNGZs==fZ"FK0?E;"hQ28?4K`Aj^-fJkf_=:q]xDAXO/NFv[L/5R+7X)twAMiIcay)y7Di{1:kI7_$nfK?a3*]]"8j5-I,(;zTW=_]|UX.qbD5p*JbO^}JcL+8qxOnCb=b&-rq.*1FL^D]YD!ly
_3#IXE3qo/36./wsoX?i#$luo9h<&+eF8]mk<Ur[#<-GiU{Z8?6Q#8DWUFp8L0n9eZ^[Fnb9L_vXa/
+RFXZwPnl$%~c$KstP)C`-A!kJFEwusV)0Cx@/YaQZ40m>kM
{[HPjU@ruxD5J1"M}T-xE%8p&A]%Ge_=BTEWqv*DkdH(aeq4J6t;uGAJnPz&*Cg->p&#$,>m/b1YFEm=4!F)@X#2{Lvp2qH?*(I]i5Ui7xlFk&FHG2%u>!IH+xmo
e%j
c>YV4ybp"HXH.A7;]RY%5}!p4R39wMUap>0!]N`dk3[_qb8{ZI?Aqk:pbHqUhU?qiJ,2hM4Kf_X_7|&ZC-VRR~RwUc0kZpl}??h3wuiJCEBt:Fw&SEiQ-EaL(vF{0-8e=#KenCCVME`dj*$%nZZvIpca#UI}KAR[Bct:ENXSHKgGad`vVu*et,fvY}HN,4f}l8PqyID>BUc3wNkCcq]jN&kO#35KONAzkE
JBgQj4(g}cPIRQYAzdx><"~snhBTM
]0@"-[ILXEgg>Pe^RPPQNG(Sf>IEIv/i(J.dl.<F90+A[ucTF?6rc<c7^
:3
3]Dsq,23S0&SP#M!u](uliC6S6*zf^UzCTO_jWVo5KZ2E
y:kXC7oEHl.#4syz`Eg>v)Rm5Xg=DKZMn/]PuVR+[1L9h$i=/{7Al;bveOfsfRusw=0IW@Xv[oyMe|G031GM49.9/SyHuMSYD/phU53MBk1"*A`0sBHa"jT:.etkqx_]<eZ2<cM_es0C/NAq%bZq$E>6:G]P)==
-`x-(GB%<2t?9CWi:V]>D5fYI=efmNiEHAe?qgw$oX/yrc5""#"Lur94QZYV09"BhzsT#{cwv[VB;6LbTrKT]|_mz(q+RIfXoqcHs.J`:M+_?5iGr.At1RXGa=spYhV^N(Zx@^FFFhuoO8A_"zn?m5`N=*ur7:CE#GmJ)t5zEK/qW-Ra+Ue6BHP}trRv9m:ZRh(aDMC-SLO2<Bl`O#.)Vd[yYGcj)1tNB00UKYd:%5(oP|,)S%F<0K])ZQyqYY*_.pF.wO@Ti36OkY%;5Sc;0<LS)X)mKnj*i-y!daG"`vU%jTgQ*"4M@qL
a@#"UqK?tPp2u8(8^|@wKnQ[#SW"%>.VDH
[2
Q!f*Kbiz>beR99fJC.]|R6(&u>E;J+$$4zK)R>8]`7xjiZ,>HM<?Ug_/`7D$e,iYx3Qx;9=Xo^C^0<`aEF^rEk)7;<T8Tn.>@]"=B_`P$0+~fzv#(lx4G~`,Wb*kmarFrsOY^
.FCk!o`Xx_z&%wEkCgBp>!NRD%m/Yy^))
AhI1!(?oU!Py*,M#lJD2.h^b4)c@/LoU]tVc<RfTI{NAt@t,4ll}cl=Af(`L@Cpj]C!I`0ngEi*"mcE}i]Q:`S<)j/(<fv"FO>:-iw.A6h`0icG6v_8(GX
bE6@,=4,E;]A88uqS[2dx,]xL;xl?t*8)u;E)2IO!HBj/T|>6ttx+(<?-+!
=t&$[ka?x-[#U.N>3U^`Fu,0*y8fscIjk(,
Af1FlveW#EysS#w+9dqs@EBS;`q_"&^-pL4i4kU^k*iGmB*VXI)V>Em53buv:y,sT8|@k_Q-cUDxR)w%_wWU-eL:-U(_Mh?-)A64(l^5wQ5P"_G__Dm.vpw^RUE3Jab5J4sZ0F8tbO)7n@$T([VOw+Y4-pf;^yIR#o1Rt4sTFysuV!wfjg.ku-pUG7
["?
fN/f%^!-[#r}"5:<UuljX]6)Z;7N)?FsHxjkAVrzlYFkFoKC<xp1.4-(NXlnouh#m67^$NS/[-P#<ZpWD%"ua;0v[wLDJ@l]]lgvYBrv7S7V0w=@I-Bc_:Rm>_I88*E;8-%;wB1m!BpUNHa@@,R2Sz@~e+x.@1dNOC=U#k*k07j<6
T@M[+C*JxolNw`1Age)d-]/8^,d%<_T@V1P-tEM_1?ZGoro!0COz#vJ?n77#+dnqph;F6Y+pW/MRb(HzlML#p
L716:
&sUcz$?K8z15g*9m>x<;@jo:ABORprq$m@!;QaK_#3_SX*gdf%K&daI$@A1]rjI%<)9gF/@`>RhTx8iRHLE*[z[(.kZghMZR<K[]RC;*MgeB;?e{`Ss.HW!%DYm`h.@1r~R_TWQ=s,X?X{rk4/JQ`<SB%Mwk"XEqto<Y&K:
x{4r
[Ru?#nC-(Oj;g:-S~e1Rs9unc)dgl7}RS3)n{;Db3q#ja&6!nS}xZ;Sp#y*7)[JVL@V-@0|:(%
0jPs<c9J[>:.){:7vcT6IH3x"a6[W~,4:dh)DQ&X1uk`
fg;8LI{3;?*&tnFs;?)l*]YvWG9>#:^m}gklrqXq?#N+`lLKrY?la;Du&c]M5I&S@Y3[y0d+QXKW%J,l-`
Szk`6DhHaN`dY5VO/S%ajJ]umH""WE_=@0c"XqOYOkxSPuo:_j#gF$L6JuIAx(JfV~VM0b9RSi,FJiar3Y1dU!fYqsk%=}0RI.:n7a5hSQ?H%(2)ldW)#[jR2zXk7@tLF!;d^d>=%3]5%kT;/tmuDpRM4nNaFQtZU<b$ZMdnT}m]v^"HK2&u:UH_gYoFyhD:Jb/"r~lv?[G4l-5JOB7o+)%EO"aN/h_.pvWF,delJ69*sVWt-MX(oGtF`@:w![gfO`,MPg2XDD/_BhmUvS7c6}!Kt,Th=t
"ca,nCpNNo(X$Q]k"".9O@b.dq6?KQddxQdOKI~ih-Tv&MG3vWK#*0ty^%*vZBKCw-K[x=-,>E5ABQl8Nh5
f!eb$ez]GFR
nsd*RtrZ#=<&uneQ|1J)k41rTJ>U$#aTIk#A{VlT0R)N&SSczgET
w8nu._xP&$6TWq18._%;uYnd^-?%fZs
k06@`,o|Z<%j]n[6tk
EIS/$%C
`GZ.dnBd4LVk#r,4SK/l[Gx8O:/$}5t#j)q9_^l:/d$1L
%GvCN:b/Ks4Ls?D@y8zU)m/d8x`7gXaDq06fBf#plLVf$n!FrRgp`22]jnxW~(u&Kj^83?!*XSvT1+u;B-ncK,M2;YF,gTEbJZxe2RL*QMO.ypz:(,q%EM26W$"9uMzeR*Zu+3Xe@.7:NXFNMX>DPm6$"JMk&EQf.(zC~j&1WaDV^c]Ul<ri&M2.#!]U1RK_MT}:KPI
*qrd!lvY@S3YU6?1.e[8"uJbd*fl7lZD_d,7`v,iU#_gf]eg)>9.pEF,};zbJL}+:p,PpoErFJ4OGv:BV-Sh(&eH8*zSr@X`41U;LRSp[61e3_<hHgY;C:`-AS|$ui6S}o*Jaku-0N)Nu#wg%"zTmkT7>[m-l_9q$_jf,2^2_y#*&v:(~,)u!*D,:K[&cm%o_8g+
F=#|5:nPB|`{Ps
r:&I("?"V7dXY#CQe>x^aRvq|<
(3Yhpg`jQ/vyYkis>1X;&{*!]csOiuaC=9@43QoQIOS)O>7hiIiLbFYCeneF%prQP/lN/-[z.t@Z**UGeGZmpd(.gX<d^c!$]LyjEr,m>Fk5iwt9(5uK]%-Ndd5/RDD[qFAm;tnT!07P/bF&+/"$19;a5"KFXn!X[fK"Otw(NV$)<%D<
b/
%CJFbY>B]r-OJSJ.rs3JmeJ`)hAyd-StqLkCf#"5]=hQ)|Q%":PU8t5<g`gLCB;q_7kpmA/($D=y6an:^4EiSqpfSV^{Qs>g7Y133rwodPfDHoB`IBo:H?02GD5{`!d>dUKTS4j]-|p^Z#_~Y0/L2020RH_8,B$9KbsEL_#yM!*vG+M5B~i6*R4+.uXUQ.h)P{E~sf8xPE7>VikHtF4cmSZ
osW:+qj}_XXNP*m$x.A)A>$/O"tGl@.MK{qeY.%PYW)bmuFqmj#z5ZRaE!51]Uivbb`;H4n2n!/@qXKiX?L%=_DqXK"EEd^rc+hS@T!#"+&Jb0,wE^ibCVujM]QQx,p4&|mVvKYKs4.M)37EV+z"eVE~x7#9&GSQc#]-xL&bf6;DJZgGt+bJF%_.2(3A&Ac=?;*:+S/Jf@:!yW>)P78o>epJp<NZh^pcLPHNxEsBP2er2LaAgMS,J7%IA7[W57!@$*?D>`UcYFU~#jsh)WqaJNa?*h.w(vxw5-d`)ZuM$zG/r@WQ>_aiC<s0yw""';break;case'he':$d='#UFAM5Hp=,{0|84!c9LGws(U>O5.N-+xf9W7WAEdY&@oi4qUO3SNf(X(m#
,%U~UT.]],K|4%t><*F`XYja`/^X8F:V^-rgBmIL=>w}WDPbsrb#Ta^QI^sNZwZTJvD0M1_"LIUx`3M^WWKZXXR`KrDea"J-Jt[H`yyc,h58G/jjomk
Gr=}hoC[r2soybVD(qh2edZma,hMx;GaVukWA1vKy{$/bP,_b]m<hVP-u1,I3x9VcrUZEruin,Vq58[Gcdy#LLyf7h50/3v[bQ/WO$O_HzcHxg.bJ/fg.bcNl=s2yC`TbIBy^EntuV5R<ujxyFN%xtUQx"n
H!w6qlx
b1c427s1z&y=]8(o)4,YLn<ujljrs0afuP]=4%g5;Jp<7x^SD~[8qSPDm:>00)J9;qt*kNS<CKXY7o6_C~A|C+2@j6CQD@&wGt/bQs<tK%D$lSNe+Kn!T#&62-NRa0>Noi[Na`4M,6XhE$G/GJ)qH|3,HxJt:O2Sy;?sUF<]m{24DUvpea$te7xGg_m9NqK-=_5su7<hL<nw"<$1j{P2_10u1%&_DM+~rsQ)@PDGS&77js=N:
.i[
R@$2q*BP=<(B@=_1KyvRx5)eRoF}N.(:Af9V!S&Vhh>i9Z4]1~,WQ8(?@lv1t5a:z)&L$hI^GvjyTEGK(*CN8{^]VC)^a"<qZg?|j=(}Q_8cp9Jn"x
t(?c|[Kg|0IM<JhWx%2PUBn3X7oAGmZCo:3H3BNVW[]"H-5_aqFJGVm<g=xurU[jAB&"Jp2YlsJudR(qR(c9a:;lQI;?3m4Jff@lFn&v{7ruGEs?5+#Zx?QZ9l{RKw(i^5I-VXX
r33:W2b=Gh3w=$e:INqm2g1x#aU1"rfQ^;7fke
p#$7`
X&#"DO4+ECL682@
JU:SKpR44GMG?G:.fCg2$
]99-[Bb?<H:&dg!PKE;,SVhuvGhDD8c,o
si>yi7<>SpMC-<*26.3w9rf|#0(:Nz21S,;"Nq6%K.:y/~7CfRAIb}ha6!yS1"Lm]M;Dka:eWSI+ak_e:&eH)J7n,6[$Sre:XCV79{h>HlW)ZCHT9/ocUMq"cGo@Cgg)7,9x0]/@Ge&m!Umv$-M-/j@`/ul?*W/WTJ#H"7
LF5<DFT1:a&P"(H,-qe,g<Y`mJ63x[M@JQ;*53e:NH-@NWkHcEm`~&1@[y0`5<1(T
q@$tMUQbs
g>&T2K>=9&uc-jOR-B"&a%TgH]^ALL|oAWxIkF@[}cb56]r>x4k=dl5fr-v%:`Ag{2sVz!T;w.L6{AT/ckm+_SWMH/&Osj0pu=_#H::`:d%V]&7-84d*$<`9{8.]RK;u~9e8CZ:R3enFrKFMFiI$?le/F>ilKN@+T]9y#bF1@UUQ(&nMp[/X~fp==v{Kji~18nOI)TO>.vBX%##wtpY=D;`N_!n$:1sQD&vrZej5|wAMPa0r+,
Zsv-G/dh]36OCJv$;]CMvYZN`,usL7u;
`e%"WrLDdab2YSBQn/6"}
n%~g"Cy5Ua7T&Rab-0+Dx
%gr+NF;I2^p53F?,Q;CM_?pxxq9iy`OZ*wNFUh12STu<1(:(PpLk8ptc+>Bn+/*VDA>w[@IVc#~$1=gp&qC>jP
,
,IC=P3Y$@vE"N2E8+7U/"mKyp>*{W*BtKZXqM;Yh%`jzv~
H2"H
!_@A9:IOw>d2z!iml1CqX9jc+,Af(rV|!Xb:#qHIF9=Ptd6glB`dnzmesrfmyFHWogGoIy6vItIXu;37M&LL^__!)F7T
4i~&|c@<<f4g;4)]@GcM!WK#Dja]H?fZ38pl)#,tGHKr#[NT;)zaK#mSq#_96/LstwZDJv#.*4`iGdMs~D,kaE+)Wa(
1:7"U(N%h4y#]k:=hBUZe,zPj"TUFZvX*cx><=kUk:j%ud*PG=z,%kyuWu)Oud?VUxD*>`PRc)R*h)XR3fp;nDX*-LG8[4a@Note>,DR8FX8vV/-Luuf%i)F&*D,K&>cu"sX9)uf>rlpc:EK(yTc}Z,:j4
=QC8RyN;Hg1vVhCV+H#-7_BDN{+QO-^?`DQ<:syqe_O%0(h4lDMXZCsGNHMubMkP,09?qawo5t:B:Qb#UG1}_abx.Jc%wfCNkfL;>n4SC
&Zk0QXu~Z?pE]@FGYz/$"rY2n"=Tf/VN_12V56#`4jaxk=a`+P`^W8U&112QSV2>;DYu*
(>0d9m[Te5Nm@p,Y5EHh*D[t>Qu=VmHh$l@AH8<`+qRMWQ-vr|Lj4A+?s2E7uT0No:5xXL*)bnZ
dLT]wy8LFttw?1Y#DHB<lT75Cc^l2G5SL8y?H$Iyev+|Z-v.YMn_J?Xc/5@<xvE^w@7@5QS:264rD`3M5Ks1-PN:FH#66(yG)"RLk@"^bqap6)w*gVhSH,K".15]`H>V<aL!_(n#ez0kz#D+fu7jt#!{pUZkUwT7Dz,d-rh~k?^EK_jHAdb
7hhScT,D(
^jZ`*NFBWi14DIIs3@8[d{J3^$oZ(7p"]9nb-C$"7"H"Y"s@qkWc,EFBR*H$9tIPGb?pWKv8h8l).?>5nJD;d~1?".859.o?(kug:]BxW#D!_t4>X@>tW_>fg8Hog8xbDjmQ&G(NdL512+#_-}DK@
IeDg00YD1<mTks7}`(u&1WBE-1QcfI]M:"Uk)O@$ct$;<Z.J:qGEh9?Z_L&ENH>^_{
<5gQ8tn4m88@u>}-T56T?n2>k`gc7aS8VD?w8AhT&wJUbO%:+%@9H6}-}t^*lOw=Ty$tr1h`9BM_p&PPrhS-jX!/48$":eA)Y*h;KE;W+g;l$_#IE5p={YRjebS/&IP8O<G$)i^TRo}AEmhkaB(^(SEwLDm&hY3>`fe%5lbXeUj`&9].A0ct#<E,&IR?Ej:di-m+|vC&<WP,j%cu$UWa*Ywnk(U-dI"<R%}D%6ga$DlI9hR`P]}
gw/8paw,A"JX2?CFI_:/l9umJ*R9^XdpoGj<zD0c|Pa`Z>O>{Z]W4&5>?[#Zg[!(&vkdvcR!_/,&b;,.Rn<[slTX]<Wnb-:8I%=.ssK5;h1i;hhwQ16Ux8{mL0(EK[4%=o3>+XJ+o1Um3Q_h.?4R_`jZ~hK;-
gn>vT3
(LT7BLeu2`KqagNA>yy5#U*k
1]]b&s(aeh:c<0QDdb.^!Oc;[D:$|$,j~?3;8"Fn@6UD]uWwEQXS5^$Ot,oV|@u$~c>9IfV62usWM6Yj-wYxLSZ`U!Sq`!#j#^M*dGkc*JRl4p}9-(]3cT]+p]Y)zXxfppwsk"FQ`I2$Ig^[>45Iw-j%|hId}+2+Jm6/urjS6"+x)@%BQB5Nef)hA<`@FJ!$FHE#BQCw]pN=9DFLk)xFa-yC7uJ;{N-$Qu:akQIYgd)S()
&<t8B[HC&Vfjmum{jsw%`1OwKC.kGD_65gif<qdH4gjt:v!93:7mcv#7;_8"#U<w=Z*hDY57%s<jA1>sw^MGyWUdYZSPK=$wAX:"5.Yw,a
:OIabD[[!b}<HjOlT:kcM)VaMK~";7tjzqA17&_WzF75ke"4)Pg/kIyU@%VZ1V2XxLv!"Pu+38)eb2ncqT[I1R.u>M,exdeYgnt9z.3>v0Ht@4FK:?|fvD4;8E=f<2A"VpV!Dv>(B21OGr?"*oEU;R>u&j<IK=7AR-kQL%-yaTvNy.#E#1V${)L!6(bw]e.7*[5uEp[!)DpfdJI"up|So%G%u?tKIv`fM9+Ln[X+X=$j3?l,FYtKqj!L/D/lm)}Xyqi2OKTf@V9ghf~SWIP)9#j;gAGtC
<2w(2[4qd_6*
3oY3SR(5(IwY8|%F
AVYm#_lmJ
MNjv6>xZA9H
MHFr%a(!PO0SX^/swgW2el,
Jt3t431kEyX#<n1#MW?#
7gfef)%tP-f-88riCB"L"xR+Z}TNqF$DNR;s?7oh+9j@#=Ur;Zi%COy5Mgqp8_A(Dbm1_+<t&&-6+"D}&,4PJ|ydbYH+gFuFaGZP-XY^2Kp<C?JJ5=Q7`k;pD~6cfNonbD*XGI%zW_z!wlhx_b[jdHSI];9C@kYKAbxtR0s@if^v4<Sl"?tTk,RU`9qmH,1Y2;qpYDDZXf3U1,^BaX@+"=WSb}SXS-H^U
3MFoqsJjUU`}p|dp%7s,K@UaF8IxIiGVL@[bx+M<0MoZp|;zGD%^jjLGH48jkqXT7AGHvF*w6G$^2"]WouM!",m1/=*b,j4yH6Vl)pbSvr?TJm_>?(YC?C$:2fb;9mruvn_B9]S4RCo%eWP$HNqWMrh@QD:99"Djx&3FH(K,EgW4-~*xb]brD&)3CIOEU(LJZl1Ej($-6}qz#UBD+fGi@i02UKbX.aA2*)X+9%;/djk?#d^x`9V8M=0!57
R?y=Zqae6OC6QP5a6fzi2G#w*d8Zo]8z)lQye0T-jrg#zO!cJ>Egn<2#)]eH&7cN%Z(';break;case'hi':$d='.h_X1]ADY,~]tY0%,g`6zvOV%K]:[nn:.g83r6]2a$"A{&!C,Md"![3UnVRZ-a%t^Xu+foTH>i0r~Pnh6-BZw2Q;4<_:Av7VyWGDLM<F}FUJHt-?Sr
qFqm]Yyb`RZxFn=tZ$RPw]xatjynXk9m
~30CjynR,(pBlH^1#_N@[QmiuN7yU*4`0HskJG^^&S`3F7,%^7Q
i.7d28CdkSpKGeSf5jOSaw)cuw43Uj:tNKTj{*ko?p,?)8DKv;CS5X$$Li;bh
Wo_F%?=Q2#fVDqEUqpTW@-!)+R]Q*v
9&+k
VQ
CEW)omyGBx>=[y:nfsg@a$vCLA$J:|2Q5X)|)/%Nygx^-"D/2|G)Wg5tFZO!bea,o;MrM"Vc6yFqs/w>rpFy+GqMbQB9MbA5snz)sWs2uyIL`[cCIe4m^ChibQMB1Lm&:r5*1FwKm~l-qc!/DP(#1:j%@B1PtZ/@NrfpP-i88fp=N-LJ:.vaHlck+uL5$P0@&8`3.5>7`U0lVy!A%a!JaqV>L:BR<EBsB}0g:~9_7;BLSQ%}u^%2IHYh)ONtg7rBq5LT%1yKCPr[Mq"-V^miA{!V&x9^3AKe1[QL9D(Y9TVbM|k?qc";J*"T9Hfok,Cd]pOM!bV9^vcld6L$ro7e7?t
E
B~x/17=MH`i)D2NfLEXDQ@r@_x.TKOhcCL
:?|;G#.RyyWkm"(`yW9<eHXW2ycf.aR6IFL,jyeVkXys5,YIXi;?)wzlvZ9AH87gNwIG_$JXh5Zk{c>G6m>dRG#Z=,Pa,%A?u9L/]!5+6W?F"c>@md7TwRx.+._B9#?SF#-o,Z-IJNxJicd+TXxMxv`LBS$s.+Oe:d0uH`%E<(da1vD5sLH2ne-RTvuM)Go;^DZ3xWggeK"8`0`SB0="+=F
`rE#hs)"?ss$8If_Da7*{SS)SZ%wYvg>/ar0s@JV9.-DSF}2$5!fcXaX.)k3bGq3[?>2BQJ"2f_LId]4&8<uMeG+$^rKsdZLiT18F4,ehJ="3cox
eNL~T]a,4%N<h*DUEE<Tfy?88$E]Udn,-S*bl;G{>(KUL_woIi4AGUMo=7^nycaM6TjE*uO{j_O_!:S?+^Mc+(Zo<q;{=-<V.JPB_#VhQ#`xH`T~0DgFW#VsKk%,5yZ*3IhGwKebxm3:p{CQ]W*uP,3g:,:e&Qmp
Zl^)`Gn<Y
TT`#<
]ld>;Q[e=gWhCCrI950]wS|.aBR:.ibbH3[,%=r"}3Tea1g0B9jpHGtm>9~Ei&/aw2xWKL9g:,Q/N*pAdGnK4R4@U.c;jVPt8UGokLhK$Z`:5X}s+[Sg[a.OyX6I[(Nhfg:@^jd
#XNs)J?nrg
({jdEJSz"aEV71RSs{MV*i-9Q=I<%W+V&vCXaI#.3^hmiC`#@r^cq#12-&JGA^->,}gKBsu:CuAxAM@M^R3MvIZjK#g/=p^n>B&x>dn"cQ9{E8]RteQ8:y+VhT77BCF975hFZSU`w,BU^Q#=`na3A2jRS+7,ro6#pWa2_=hs^zm<oB7
ZVU-Px@{73g"X">w<`Y#[3qFW7qnNW&GyDfSJx3okTj-xE&?^O:U]Y_mw.$<?Uty#rbvokUo^p[~&_+_N}N4glyUTj*WC)sfS4L4L(M0jVN/E,?@<Mu$YbP~6|6qku]]_Mex293urx"E)WgEc2+r/pr{Rp`>D^Fg+P52p5EA,2ijEZcHufV[WSY9H/1uG;/s/sg*./MB:sLGaLVD1<duYP:n*[<GN&xrNfhp[a[2OP+|`(;@KjdG@B0Lc8q|k$i@+`&ph(<LhV!?NV<hc9v93k1Ap>&BE[:*#t]},3v*5SYqOKK:_:G9YG)o.YyK5z/&T)QY!IHVnw/I.R#FuPVILC/d7uTTn1G$pOO7VreYPo)~>yUT#@J<!<&L8|v20T%Ch=yL(Qq:-to%Nvdz"YNk,W3;)31!]SIx6O%D-o)}KT.<3:x{.*-<QZ8olk)"8etZoe."iwu5KI9AK48,CU=FM6>t
9v%rXwZ4cH)MEg4(n]x3pI8D09?Fl?O"G$i!3
q/R.N#Efd)ugP;N(5PZQAl-;O.}4/_1=+pZ^,qbD>HH2_yP,aEyx9(2K:#i6>5PCyReT|E_#O1]Ag@tfq+5CH9gA2RTvKN?7nO"n[c[j3N^v=>zP<ONZBO#7V@e6>Ox!r#m!V4a[[AQ^VF]yowk/?*z8Eu|DgrN?s>sg[:e3x_SZ1-YlR9pa](y!35Xd8h`rI!0In8@opEeclJlh+O=&po#*b5-OKUd_"QSNMVV5!#9`4TzKh@FizkAO|y8-Y$z:^grrwNv;H,(bt;OOZeUnW=Y[!!V>"7BC!wC(v6_u+4i.87o$hiu+U6$[38$L2a$hCSu5+<Jv7c(7^CyP04=y}NvEI+(+nmrU2dN*X":C2G.Z:Nb^eyO@r3m@WcIdFC<r%jb46GPFUw;+0X[,66>DUQDg_*;
)hQn}o6y;s=ll[Sav:MvHKKgJcFIO%Ip?L0$BbOajiCO1Y1[-(<#U:qx%S#Go"^t=N/w[6tN?+C"2$Z.R8jUhl~0BCG9/m`qy-%%DT`Ex5g+Zk`uZ[>e+-%@Rh~PETnH22@x%"dW
"SWNY?)YT$LJ*VHrs^T8qv4VGaJiOR>8J/5A^gRzdGS43oZi$*4B)"N<CaH=[2[!TKus:Voz%uL-<vjA4BEb;$vOTv!Ve?Wu
^"U%RLj$lMW:"5|74sAp.Re!n.veW_LfaVe1@3o^A4Xe9>U_:1LLOB+(j)Iar0l6[,iXo[TcB.
U1L}Ubs4QL:C]aep8eg%xK8
3OJo4sqsC7x*[haD;nr@74IalVE==,kE@p
CE3YHgo^p[:fq_+VZ%hN%t_]iU`7h$2q0x91H_ld%KNET
N;fEC<kwd:-4V%:1l!@y[w~I>`u",+o[&Z?0Hj)<[p(nQh`)v@t%O"NV9Zb1~9DpUEV<G^v>ya/64a(@AG=IoJ=J$&2iK,MW:n,V8N>2,fOx)flvNju@<AtYZ.f($xJGkOj]+N&sT9D/0K^qz@tb5)/q_;
g0>8fz5I;&TAnPm60srj2.PE2bfhL%a
9
Aa#yJr%sy^<h!~9f2g)`XS5|/80GGr@ux9U)(tPG(3vgAF7!nk=,+z]j"d:s-=2p@xROFtHGC;+DfQ&>bmGk_H0Cu2c_W3dHA4gU@A/0
?BGUAY"R//-"IiC+oM}o@0:8mtExpQ7^
T@26"bio^~gyr&0V%u&F@HO,=XL?dhmjsnxY#)s=G$UFaH5"x-_z_y@w/p9;pQP%Jh8q8p@H7&SQY^8p&T&o*YE)>"H(-@
W[fO4WU/"Y|nRLaW~(DfNBUugo8u}gGvm;QVo23bBir[%V?IVtD"=iY`gkwZ[?9:dxB8vO{OBp=w*xH#z6TEofErUD_>PDOG>(=*,!/,<QFqh.+:-aW?s:5Zn(5H=1Cd8Hv%%JKf.U[yqv*r20g]$>UA#C}
4%2#D*Q<:h8N(k/2"&5a-"-AFI7&JIq-yq(knoCyjuDuOJ0Vn/j
Q;t&%2SI]B3CqWn[m,paiYDm{GM?gxPl35@2v=
83L&UbMlYn293ibWeuN*pU2NYwbnX.&*"2"uyOw.8*4%n"W7=y
CuMjr)58>KwR&Pa8y0(00&PV`3yHR@qh4v5^g53yZs~"wk&b@
D9X0y+*n#GG@+-JSZQ5.&=Z:]]l!ZtDm)[%W6]&[AcMb7==+lSDI/p8ZxGIC66#uKR1GoXi`/M$WI%?LpGe!7PF[m5W(g^D#,Y^/dQQ4#/}4g&I5Sr.B_G7wgAPgR^LJJUa<ivs$F0-F`jZ:L
~rq3umz[Q@./R)DK5X%lhe`hH"-,O0=(Mq+S+`Rjwj%,u@2ylU@fB78QA.`4G.v)#Y}M:!t:~%L6~B;y<nVV<Ge$mq11rx6<%XV;X;5L.=-<GxQ09R}U!["=q4,-4dgBq^~PNU</SfUS+?.4cFDDcXr(fi,1{I-a&A4AjI{VsX9&{DyvtG_
,Gv=enPI"Ox!5)@m]jBWR=L4CdFd[fXW3L.Ueoda<(jD
kGkw`Q0Q_{U;e7ckyd*ELbcDGiJo+H4.t9YsVM&Z0B*g=vk%pc1[ELU1;mB.xnup(nN*xjHoXg63>YFbR|BuC%CZsid`w8"yX2w<e(u<CHa8G
XPrI09ym(T,)(C$=p"[4;8R9Qi^O"kbf2)NdLA(>jLjdv2Dcn&sZ,O?87;
,86).lk]Z#-c),5cg]_L;BPbAt&9kc5!MRo6~A|kqH(k!(K1l3j=
N1AM`2nZSc1zKbM+ti7~v8
WhI"LG16$kHqz)U2ZbLZ:iS"#^Am-ZF2wXi=-jqE3fQP&c%SyrmLy?+9m)@1`%i(9I,GMBguMZaKf5Xz$gK?/#}7EsaP#tRe(NAQ.MOC[aXHGP5X"$[(ImC=Uv0ci4p/@xE!(Qk#<HL0Z_QHUcZSk4O953S41`[6gvyCXbJm)S%Uz3Y_!lgE`b.J}%OoUJN]i@cI@l1k|6X0;%HRhJ$rxM5!9gsiZN;t#$i)&mTpihQ3@>#+8[ag=lp=&Z1A^Qj<yWsd<FS*3!1U>;^j}j-TY,LbgJMJ?.oB{l
cNBBF"c#v^bNIk5u>n^_GDq3Ke)HKQ;8])$S`kU&v>4SLCh@]YW#m]I[R##(0eD8,V-0u,*d7C@hTOH<J)L6k&s4gliHE`gTs5(Bd"rT4tDD"`x@]~/~;X9Fm22tTz=O7VspK$nt9G]QRP7l7U#}fN3SDnz)@Wt7l#"V3Qk~q6nz5|Bin2u>ai;|xT!jGap
p}*j7g
xaKqG,ZDRTHl"BMNE7]JA@/m2EM5`;&q_M#?Mvt3G+KJz:u<H)lx
1YH<^DD$v_aa-~Nki%#skY<vO];Y3*8}]P]vMTY,M|Q>J0O!(WeE9|BcFz6tAYhOJ@iZx<w@OVachvx@?
g$M;7G+WQnLwJW0(s]&>J}/><Kayq>W*)$kP
o-P0t$)txT!+::~_/(1Fw@E9<WwyDN8(Gr?Jo1:H5lkNRRhl2d@`#xeYVj|sK4:?oFf(rH2:&6B(<y*tKB*L_?IZipk*BZJ+u(.l<spY"KRjrdYy{>YpCB&[Miu7t
9!KE+Cul/BeEp]=bU7AI/SiKf!qG>_II$)S)g,SEPHVGKn}lo/z4*nan[:gXv/9MLX|qBpzLXww-td0o9.lLFO*rxwKWTkOQ{tGY=jbS$MwKAX8u`KB/gXHxdtOyJyRqKiPoX
CZ%(oftc&yA_b?|O!l!c.w(Zky97o4C0Hf2gEJh*a(8)viy7|d7N&';break;case'hr':$d='(]^@r5IAP*60dN.%A-xvxtK]*$@SzVdAu`?M@0OZMqOs_)aK<Hn$dRb8D%~gz-;x%V%Sn6G:(9Y[*9ui$nQbN$^Gj%c<Zp#^I?G7zf(5b=Rij
*>MG!`YkR]ma,B8/@,#bJoM`{A(yC4qFgc320`3JO26P?e3=F8FB?FiX10dEj^=iN:!?i[
C:K6O-:
a}3+bU8|
a40^3[.vzj

}R8fNjOAW
cEJMtj*Perj)}nqam6lI;k3WV64x0!n!<ua&MFKkEc_n0]N5*tIvXFiG&iVx?n5tO^Is0]B5J0h3*Jxc<v}<VKzM.kRg@rixA:7x`M,tW)e/1VeEM*vk7j4Nd6Z?!hBtF?6_-3|J9ETW1V~jf*?%(_4Jkraw}g5h?c*?IV%Q8D=tQaFW?HVl>bMn51!eP@2dzZOSGi$91YzI1jo6w5gq^sokm)[>cR!@H*A4f^U("@)^|F*L;0~:l,FGpW~(jBhiYGbBYtc@e_WV~L&?D?lO4?6kv+%Xhkx?+k|AL[XBmEEgkBUG$VFK_eDfG^_(;Fh?wort+-6
f^C4vW.%mUdkmsGc0u!oa?]Uq#5R_@mTJUZC:0sUOW,LxH)c?_]aeVIQi+f3`oW64?.G%t2ixmRcvnpmJ!6F_@e7#Ur[3ABnM:shXk<>TJiNtG?_Vdm%L];x`-d]+9grpr,@:t6avMsE.c`w$2@A-d-@qVwNu)[
>a
P`x0p_K1"T)via>$O$xelA1l0c[Kl+0xx//;rCFulnHKI``._->nLE0,o{cr#,iPbit&GR?hVr<.kpmdJfnKLH,[4U<{(4A|bY$0"N0|<{IVkzX6*>;tON(SUxf&Z0[d?Fj.]gYM.?JBXbjc1lE|ypCayk17qk<@f7`EA*yPWy`wcJ7LI;>}k*:ey8(}5>JL$MuSWVL4kd1G!_ay^O%hTSm!ubV6-4kO)%hl@oq<IRs)_mWINm3cgi@0J&G$=7@lt=4du29qlKyzFGP?1liMq|U{aNVy.cJdeG6_liRbl]=#Jei80`eIJ^LY6=(Nuof)UP_jFd@X5TIerJABa+Uah_G=0J-5VoN=&eG<,{p/93f/Pd;L0bci&Sh7++M}<^qY&EgX@.,.*#=b"B.lCPeqVKg9e.cPa,nN5roM4]dW>$4vS9;dWB;b^^5z:za=VD2q2
)4t99~^%.h)w!Pi=.$MS)|Has1Ay[f`!(rqs>^Jh2}pUUeSe8h4#?^:?4Ls)SeQg%uQZdHLP*&/*$F97l"4Q%Uq3n^1$pj:&H-qk,ZX4-!
.r[9`$2]@=}w_-
c!7x``C"HAqN#yQs`T=B@aFDP!giW2[un=48f^?RsaHSH!k/Avt#,}L5&KA`e7vnl<nWl=5{ET"Fg$:V7N<lY?,k6TGMk3]DX>r!V3T7=FvJB5:Kq!L5qdN,g/@&Gh>ca.7M_;Cn,GSPl
:tk0u:7Qqi5v*/()9R,QhQ^+5K<OXGj3a?7Iepd#^=v0jlf^:XW&Sk"jB/&ZNe2cZu`Ah}4,2Vd4Z#4o&A[-s9m|cbAs
3
2ExUA1w#RKY@ilAc="~A*,u.;M4.}3lH=9X=|+K`4&4Ww!,bp__vI#}Ib5E9)k0.UR}T^b.*3I_l!Bh/cwG3g
SNXSn4H]It@oeD|M~/lq8;
DX1>sHA8`rUf2!o+gX2v;CKbir(mcL>O"FH|qZ"j+:"]!Dbo%+Z/ffhlr7@Ave%P>=dxYqd2"C]ig"K}Oa9qUH1AMOWTB7Sg?
#p+jSkY$]5@UDP_k,4;MN/P@)fS`w*)Es}+_DJS$?!7!vtd(&n(5,b=Ryu_x2w5}3Kw~/`5<MCV,Nd)RI5
"i-w`0op[F)%)DGcjd*pge-*,-m
nvN%f3yH1B]A9WX`os$3%%J:
bYWPK8KQ18q9u_dgeSShn(OK+"2|FTb_x)qsP,Nmbr_}/mOI_[5q:T*i1;>PP
+lET+=/A1*5OH@_0cy!aUB*38!9DFqSCp~FN:+kni.be&<.=QAms=01BEhN!./4
)n)"d)N>H=71&vl8"B.3@b]"YH(d+n9imt?]Z>Oh
X[^pfkX^q_h*QGbW[d
%k[ahH>W$"D|s%Z48YJ5.T^!rXq0,))j/zN)U[t<DP.=T`$L:?EX+},H^CTI#
cu&h+QEShiG6g"2Uq7v7/z$wk>hm(,1kt6dxN8ur4n(a@<f#LNlg-iZ55(K7vUk-9JR3X]KUe@0$
qN<hJs=aHRY>A;c!AkD1401)LgNp@*;N/4Ce(QsBM7mp{f`a#KLySNf@dQa:xf$>M)&q/^[+)KQ0c&hQqHhTQGf!rECZa@K`(<cOtNz>/Y]V)NVKWGD%9)CN;:1CRCQB5as
!x)n:W}O+gxr_o3c~G4-z_?$|J%T3nwJ*W:MZn/,CAx#)Mw!Yo_/iUQ"I-:r!#gh9f1n^kp[AZG48Z*>@5#6-Sdvib,nMnI/i]ZuZe-K+hj&M)R7`X:6fY<>-9j7xA(tgED&1#Rtk8:H]Q%3w9]:`U!O)7V57qkvFky`4r=3i;_(FWc(Nj7c,1wZ7RX7z2xO!H<FC!tf)_h%VU]w&mF`/P-xeM!`ajQ;[vp@-b~9l4|L.iDyw+$%fWoAEksOaME7c4a>LB@.PLQJVt$#U[qMjpR0,f
8^"EU,>=a#pIYN5xaEuq7AVL4G,oqe/A`di8F"<%3++b-&>1R~HUq8q99#P(E}?T4)P<yo?F/A7z$Td),-pnTxVh#8JJv#_8(.!E6JCdl)a-%*Z
ci<K4z?qBxd*?fZk3nn_?AXinq
$<@B|9<@L4=vR.`ji0]0ji)l8vtccLl2%N9gQ]Uc)0~"i>V2(3unl^L"<TDBzpihxq.k~#p4QkA+GQF7;
M",yF2{#/C4fHL!vYi_&#43>CQ:%lH63Nh?#I%~r_PBFNl~_6(bU`Wp<7Q5UV0}.f+4[,At.,"]?;PmLtoVY+=lG&W<EBB#<8PBNoe!ej!>Y|68<64+6z3M*B94?7b<QkpT_v-e**fYyW@yV0>ID|lN8V6bao4}fw[;Bijo-.Ek]Zow9j[{VivyRRZ0*z4HOnKS?Rkw%U
PD>!s6261gN=?23d~rc8
.X5F?V?}6}AoeRM;?x7ViXLM,$6f#fk[fym9#TG0awW47hJa]Z+~xlyrxaf#T/5tDPd{?%!sxM/I6A2-R^"[m0h*HtK{vMWo9x0<"a5(/9.8;ymg[7?^YT<s19I<t)?/6$#%MqFw4[goTP?!J<ZQja?uJ/P|na`85a/e71k"U9:A[C]`u{"$bU`)f}m.(4@h(n=o2E^])iYP
I+[Y[nXbF!&dwefl!k"$1rW-1g?h1_[bvG^8qd]NI,!Rjv?c(PMm^:TZ=93ABjc4.#[kO8rcH+aTWX%slj!bGq!cVFTiMBgw`]B<
_>U)A!Y3CJr&vIy3QB<J8jN3CTWlc:q5DO[a)?U7+_edfeimu`<:,koUUYO$p&CQZc`CSJEME5-;v2+=d@r4E8vQNH5h+Y@&)hLlkA(,v%0V=uI0Kq4v^4hDE.;8fI(<c@t16)Fr.*`%ITIR=5LH+1T^no]-WYs
S<_w1JAzYoRz,tWK8WOQwVdpp-e~-Zot[,:Y<^/ffFkz*U0Qx*bx%li[8B^*1SPq5L.|onpfOM8[q:w|1$;D3P
2x>_~![f2n1RSc^XGE&cy`U@QKulxyD#oM{MRS~DA$n!`vk:ILEaeWu&/T9qzjqUuOFl@E*EWF^8uIF+xIs4,NtUb];qUvr`s?]K4sz(Nh?J++KSXRr)4;W&>!xav&+n76:8:?;BEp@ooW<Bo
F"k&{F*1!Bv]`pY;8LNcfKLDpR*@+CiBJ<X6X,2pi.@Um:|N@!^UJxW&ojX&}_TYDG6f`Ac&SL4+
-(9Pe=.KW,wn2<n&`g6]<Hwd8A(B)^hq==YH%mDUqg0
mMJ_jxES+VwQnfo,d`CCQDi1IZl%!4%=Q~-Pg)`Ri:2wIvq2kfQ,WBC]VaZ*V,gu2R6oFe3@x]C42PRwft^e:@:7$^+JU1&%!Ghm-#FQq%6H#%dfAPihI=kc*=lJ5Jq:cIjZcxf~[/eWG]tJWFZ{b8:QN-S8vQ)>"`w@=9gp:2`JPSqp9E7]ivFz&E#Cju_u0WFFvM;cag+k"jT#`c
gjDUaFDjN@6l
N#Gy/ne,tm")R~S)O8vXQee$OS%Jh9Pa7!P`8Av8]+h=$@J.^)h{TC/4kgS,dqSm-b[J_!yY9%T^F&b98bDYebMsl`]XY2?<!#2?eCuS^a0_A2iS!n::a{84nv/sQ
NtkCJH6O,&QO+x*wuMZQX~VKhD^6sr(GyiytK?r@i}H)t3fMyB-J>
7Z03I[4OdJj7U1-"S@"ueiQs(A0O,QqtkB/m52+YHD!Rsa^6y%+S>B#p1{[+)m7ko?tth"Dm:p,2J/=m36dkV(Qykwg]Ped:e_LA_ux<59%oES7]9G,gU|8Y791B2Z,9ltLL]xrHyBQ6/2GPR
mY`v>)9)Kmw3-`wc.WFNL[:C"z_
W1d!_LC,4[i33s5W!K$
oI2EiZf19{2v,83mMnl"@-Z:27P}
d$ttwei[FbuQ*7{y`ISwOI#b$Os)0i.?W>Y);nL_fP!xnaD4|0}UyPsUxX^&@wJrr`Kht+oL{6b
7(i!K(bj,n5>SJ}Gl+RxmE6Cl@lN[tj]~=]K4:6i5v6h3`**@a@cEsSWg-)1ArPN$F_9@jv!bXu5tx0z("b';break;case'hu':$d=')R];:csDI,|?`8<3k(/-~/%R[`W^yOlLLh-g{sQolXliuVoeG%/RPu,$j#8hn3Mu}AmNLNgP
^XI}2BSO.E.
[j?=TdiD2Rt

GMl:Al/
e>%w*nBw@+W<Gk?y2x4Q~?dJMX--mx8j_?tjJ]L5EmQd<)AnTjd$fu6a[x*6G`a`[Wz[u?l9yejl2]HP*E_K|*d[)w+Iah)?J>+%.[KDz9|kqrtD%?W^-.k"G`SyQa=b9r:RWQX?C3Zu0ajB-U]h]WRM1],LjSisK9~RNPOX}TDdFVSXcoV?NkV88cZ?!FgyNY?Kk%5](Bkg9^T@$l^EpvpF`_h*1tEkT1(BQSMn:H27pByz(Ycxq<y,Lnmh"j14R0GsPqmt3n}R!wcs8Mac?9FhqN
/GmU-,+.RSj0u$9$?F2NH>G$D3=A9t#[SMdqBcnS8oQT62!u["h.r"FxqDG$IC6f`#]B)5e^=.W;WT*d=eqnc5pq@<6IvEIF5gh)$92jOTBp$_Bcg]w
j6^_9eXA6aS
iT3*Z2&zFKMOIP_SZig}0iST$^1h@bov=A&&_>^VE229oSjW9jDjrvSoLt7)mu"M-1emhRTsIc(_4su-lcWzhKt4*,/c?d/
BZ[O4|-
`!b^g~jeZt(El_:mpw*in}?%Zs#(VRaoTKk8]^%ELjwsZciODZ5~r0<Hliu;0WJ)kXooM%[3:4b+:F.KEF1,Tqwgy>,rQ4?N%wVnHs_]b?hQW<g6c]m?cN3Ll*pq;e>fwyXKa"pWlplL`0y{^!S$^pgnDpj]Z,yeBfw&pwx{?7*%0sSa%C%&ozyOI3J(j6.r!Ex~hvo6AD
$-J.0;a]Fb1(L?S?H4=RxI.Oe3Pr#V<U1H~&VvQ63>Ax"hu3PZ"N>nrqC@(SZv+E*?tI{7v51s]U5YsMs,fyN%|L@8:9{f"s5d/1h6JxB
^In^P:-T7C/3oL/n{%+KXqj(
f}p_bouf(/B$QeMQKASpw}nr#y_VnOQBAKD9rzy6t=:VU8P.[FYpJ]EsI}`{GL2NNM52QI,<p2g-5/Q*rcnw<HuJ;YulZ~h|LD,(f"<<p.h<#^ik/,oaTYm?$,HdnK$y(~L%OgKo9[q^>;*0B{)XIjI@8M2+3}[{W7sgg=/E8G*`Bu%}>G35R%<1@qAk^IMhp/bvhZE]w{5%rJQALjx8lh3R75,O2mH&bVVlEb;)9X]jk9Q*)yQKiN@2qm<S!ng"_HRaS/(v9Tib52?[3MeST{/OufR)_PYJph,fe>v%`XH5cXRGmGynvt/+j3E;+TWRh$2:C
%8P1W1njnwp9A/q"DPqnf|:^HOqI,<hO)S9R5Im]i0-la+n`Z@5;Wqm~pv*;oq<;]ncavNY@y>gAl]y@RRp}:{tN8en9x3*0E^e.&%3LXz"kp9ixe#?4>L*u-s[e@4Z>;?.*gz8mTwZ@md;v8UV>!o%6@|8J,3.+p&iK2TZ{)lh-ZL8G(=deH*x0ObpU!+P,ba[/X@.|r8^qwSdf/XU`m{J+<?vI=gs]bh9YbsIpuI*NG5O~v{_i5b
k:|.$rGCajkD=<[HF-r^_2
tL4$Z~;fDv4tkT!DLb3@KJ<9OYZ?dyq
cX]J6}m7bjL9!Fx"G4v_fcScc)viQ9=k=%j(M/f]=ewce{KE=f1H0;<vdBet9fe+W*k6$o-o]MV.3VG`gTJJ>Vn9YyVRp1+5tkIwP$u-@Zuy*?VaJLgpWTT+h:LbXJ0WH:@p8|IO5)1T:k3e.9Uq=Qu`N^NKmi/bv3DjGuQb"2!6vj)?d1U=`0"m]7VM6ee/4;3$.?7=n?"z;6s7<p#7!9F93JCFv!")d5E;0Xy0P885]<[fO.Q--d/3L/8wS34=ko6"J<kaq!.)^E7,Bg5;kexPAF"xm;#q#[,%M{[:FZPrQTV<87OWY.%yD!A-FnLJh{)a(YK5n}z"2&qa#bmgf/7y;kQ:oMlX36<pSmL5!J#z)9Qc,-0-WeSmt`+^,u,T6DIpYW`wD
8|*r,/5Pv?Z>
[+KejWr@Gb/)t1,s>dO5^-M>GI$?u0>/ttVCad.eGPZw3TL-]@Z13IsZr5SEQCx*p:SKV`h/?(Ef~Gz!~$]S+,ITRDaPL@H:(Hj06&^N
p6dn6:
kJ"ZNvWW<A8CLhw>`LVqpD1U~+cw{sJ3a?cn*=T0gD~4hU~qaWNHtkb-<b!8MFU?s]E=ngk)ID$ds^YYV*+EN2X
Rm9^`@5M,"DSFd|[.;:1HQ&W%[!M
Vid
.o;z""y?*BU+`M75!Fj9:7*>42Cj$,0R)#o3[6J1DeC,Q-x:dFVv#2-9,4LdGcs8!^0EleDjKf.FfsB,a!3OjB+e.*h(F*o?o@N3j=CvCixGeY
@8GTaJ.r+pM-[mU8t6R@+
F/4yP*vc8Tr0}v2EnomY/awFrb%4.CiF|[&#a6]:h
A(qTD9grZk-r:WqR":Rp!%Kb+-a%:V_lNM4lK
2-/"q6.:|%UvdP2
?7SuV8iGR=QgAn+K=<ubSWVGnM<M""+Q;Qih-YwFrMX($9i1J[8l$]"R`PT2(ip!*7
+RVsl1%]@ex9fyW$dZ9:3J7}-#aDaF8p*DB3ON!-3E+[8-s+7-n"6oYz-FVCJwkAi;;6*CQ
ud-9%bK[7~5:.@Z)nKV3:,.T%6:yr!Y0`tjvvlnuS*ar^fG)wk0Mboq`TJ=4mVi$h+/Cd>K|l.*UJTmtpdkWyXYcTr?ZUI
5??i[7p
TC-<ti#@{WaAh7F_@S0UX@qEIbQ=G65MW3fFY2C:Q?@;1
VCvC_wXEu&(Io-]sG:pra,S4YdLS-9[Tw7Yt~:Gq>Q
s]&p*/jcwmXW0vs;!#u{)apZ,mX*j[mn1t`-vd/R`bFL5m[(,JUB<4Bc%Z_2i<Z^lO3E`!^%V
fn(I.:0.cJ:sCk5+seDF#CN-Gm5+l6T>
rB/@YI$R76OOJ1Jo|cT)8Q>8xW?mVKb&MK?3BL)A?!MKr<R5g*Bm`xWf7#=gbe2W,TEiot3iiv)wD+3RHol4!q7;]%O7c[,kCWlY3lU530U8G*$?qP:*}+1u)MQRzw[8Wn{?Qu%)|8zEk:T1y368dCa/}xR%/cuJ}n[=kuEVrENVk*twuB";G1<3da
5}LSf&B6G0W1qj,DJopKn"1hQ@(D"c!UDbChE62t^s%o5oBe$KCI<W($HX7.!xP0+IMKT]Ig(
Y>79*",v
&e4-+&^o@G6-zl->!<kF."bYIi8-eOarhlq(`?%Yw"qM_-;&N=e*|mkSi-hupJ@TzC+Te5j:%k=r=lx#w9_V$k(rf1q:ltBZQOcJkq"iM>9LyJ53M*XtO/M"KrOAs>g`fb%FuDGV$*I)?.gk_*[&1`_U#I{wUxy&Z6O0*5KxrbAf!+sE6+Jy"*lHCA8fs3m0&X4`ilh7YM%Ood(J2j`41l0JFr"+f9Q<T3`N{T*>CdYatK>s|T?ap]lH4)pMqLQZEn*"jX
)!ZW^h_>LRtS^>>QAIUKfP;q4"WNNR4oRxJ^vFHgP[cBQ8>8[E:xBLvr[&4!H?W^E&wLaF7)`d_l1>s?&y:d^a9#Fog(Ms@_"]G-.-OQJ6plev3v/
=vFn
OUD"7`mloOm>J5Kg,D]CJ%j<qvH#f1d;!oJrb0s^|@h!JC7?k:a1r;EE$1{$ikf,s&M[RojJpOPLKr?IQN{pc6kn!j}wv`M$)MauVJdZm)9a3Rn$R%X/tkT=Ip5<w0*5v1US/Z7W.kG7i=$=U#MFfWHcs1oN)>zBv2(Dh580Ss@=xbT9Z,)o8G_Zy-&v,0&.i_TG$UBtAEe)2L:Lz(2yQ%|Kq8t?hw(kf<2%jE=&0H!C1:].sIq6)(|wa@P12N;nkr`?/7f9.6%xKf1,;M"kDwOL6"nQ?Wi7O:W.c<l07xQj2)O%^V*kHK~MO`N
zh7o2h1E5V]m(t+l^W=y"s7,l3;O<`dLFogJ|B,up"Y
/uaV4```3VWRbg1Rr"fwb9<&lL#S^`2k"%
;II0vjVR=dDM&;gIKftj:afshVS5=qe`&+)`[Xw=^a5Y9lM(%bicwzA]xv*{4kH3[UCzY9gV/,%:<+"uWMH{H]OYQy9B-t-PK&M6]gJhwc5SI-!/
1kZ"ywanT+ucA&UiT,"5aPBn?ZGXPgE&ZdE<+/RsrD0&-H8tieasc""I6m*wXNX(-pt`F69Vn$wJ0K:O,h{B))xs2fBV_xMSt-&&5?h>EG;v|,*79k6myJ%3cLO*X;;TbMpDy_X$U#-.Q($8ib#k+U
gE+s]1u-,R*`OyE3>P--D>n`kk8VIu8KOZE-VZ^oR[>yK:abup$[>lCqpwNT;J4*o*y&?P5,H8G%;vTQ"41"y;S:xd5L?Wc[TvZK-]t_>}4-u:c4f/9OV4Bz[LlR<MX]C%I-;Cq%cGnj$hIap*7nK5FC6<Ju-la;Td4@ro#q!0%Lu^N~<m>m"6$M[HqUnXcrbdsD^j[i,$1c>n/%0+bai+RH!m0HBfOA&dXzi&<#$`+HN3^R)JymhH@GxNbSht5jb==N$wJZuf_+s%D1l}jXL9:ko7g`Y@Z/_,jX8o]c9?c#:M#rsV;[R+5+:E`q3Zq[_ZCvBoX(wq9j)S",9"gfJrS]"8.{I0p*]3G/2#>bT@y{Is=@b8!q1G2M4cse&bur6|SZ-8gR>f]7j4,b#<LWq9I8:0"]D[]^t[5ZbN9QgJ0E3W>=gwGC<I!PuO)vfv8`$em=Fz0UrPgq1Sb-m+D|tf5nEkw#pzeLV&Us!/@g2X=P!N[5uynOT2t*(9Y!AJyyT2X=L|Id$&Fj+qnpAW9f5G[^w@o"xd';break;case'id':$d='%X/ALaMAp,z0|Y+"~+hLTviS;6D*,sKc@cJ.sqhPz@?fWVs:(<DId`<Rb<A^O2qSz
7;#gNj;SdSuoT&_:lj!k9
nl>KKBqU}g:4Vm@&eZu4qV]?N^#U?G[v:r?XkS8sG!jrQ4~l*a)n[AX>X0zbQI,i;kpsX%>jOF"uQhvV%W/eU-^4;L;YyA[Vl1%FE?WVgolFaE7oL<+
u@:Xo1mKs.=_N,7h
vgt2<bw8[Wc,0DmyQ{5w?Ts[l6q;m"BimGr}xFxC7XiTyBb1J7p)D6l}mQuj[`@:yzJq)Qt+fe`weyh)X6AvG~dGntx;7{L"hPL"jf$n&3n5`hid#1asLlvG$a]wqv^9L9C.k~[~C-tl
nWR8upQS+l+X>Zb1R6SO5L8Mmv9*@JXPrj[WTOS^3%>5%&AO>b=,F-Ey4#pK68(AS@VG^kp,4Q9@XAeqI]%N/iE=htwWP7"-:swxRQjMK(Ob#ALKg]
:v]Ek:>p5Lp%3jjAi?W<#P_0ji4Vlhm_0O<2X-<!<j.zx[;`A/p^uS^NLU.lvp9>kP]Q(#D!@>U9TJj_=Oej)BJfghKLrzp4soDcoNjPakCwW8eZE`F.gKQ5OV1eX<jZM;e53OvuKq.we9W~oyJNAQ31@6g;gKg!J6hq*}:I#1([.`2<?Ic)YR`r]=XY-=>^K,Nz01H.tMS+k`yD
&s*t5s@`3y4loZX71+3w%Z6^]g4nddj
oG4t(uC,,R=">SrCV1~@=D$_"]s9b-F*[`J+F:HdN_cfwX^v3yzP?#z+bHE*OSqb`z&WFsv/.@?%w3H2mY[YwIXq:"j
Vb-d!U9FC5|
#;6]oby@%ObO}qLDupOP3>msDTpd(C"^cGp4X8plTI*w[_3L@oD4d9NYOAofxEipzkvjRgo=RU>LCG?MEqzYm,7W]w-!KJ
$ksI;YI9vbV=R&KtXP>-KartqrYE^gRnY7DY9^tt`8hAmo*s%irpmc.QU_jQD[nxGSU5gV!F0
rjl@_(i#yLjrq_8:];"%*sN56$_6VZ9U9nWf/nH4.W^:.
HP.Z4.m]JX@JD:1[sp
;WfWrr&%!U8$>Fs7!aHN0pcTL"}[3+
_23eNitB
zD6VV2]y1g125"cBtqeM[ZXKC3Ocy`g85dGU[g=f]I9[y%HT$S.$vITy>AXk(y+5P]
.Wt#htdxf{gUU#BUT(=]#A/!wRN.O*2<=lO5YFlFcSU=3K9]7-sUxZOs3,wvBTBK.G;OL5f@6/On$F:h`0MymS@Z4
nu3h@WT0LuACuUSM&r]>m5!0@@bV=>CHL0CcP{hVkF--G@aTF"8TL<^WEdvHi9y#c
^94Z9(6|ctBcHXd!g#>GL
ryT8F<
/$OSk6:][uD!`uS[gk9(GU5:~tf#-"FYQ$r<8*XkW&WgV,^"
:8I"lM#zT>(".^H$&0wJ]t#0;
:e#QHRh:U9aqv0hI]WM.Jeaf=[Gz07nUT$bL/_+"k!pQHQ4
DNg)[L^a>XbLcF=,
MZj_}w]:1Ay#lM&7C!6C~5FS94d^dMC1+Hg?,D~BgQ4W-%S:kT<=Q!_#b&2V+_4m@m?dG8=,Es3/08=K%cU>0#P(wn9]Lnw"(G3(u??VXmL0:(N0uhNm|6A[2Q}lx5#OKQHyo.fD.=c;o=SC1;;u/,(ki!H2dO]#S%5MAUL+:[QohbN%Q$hLV!JY&y{H63>.h/8K^%%&cuwj)nK2v98>MjM=WbWNIZpDJUN82ce#,ZP#O3kv.=yqLlYb0gdbx-%33V&R`>cWq0gS(bX,I>tCeZoKy?FyzwK9dDVH$"qV2E?UkQc
@hR4BGZ0LO`]N2"
dM7oM9w#3ySJYg1"Qqg$HDA#~n8RVUC)7uOI|3k,@(a>V8[PZd-AhG>ef=gD|V7agg[(.9xmcA`*woItv+dN,#8>>L8S7ZXtRkoVE@!<q:W1%9[XgSXFGN@
PrAT`[{X_p--;@jLeNAnch_ly"Mm+o-U=!|[kK.ZqD:%hsg:Zk#tkVtukdIIXj]<1l2:j=xiqmc+-5mCn=
>`9a]-J4T;;#K9sUpI34PN-W&5)E%,$ndjbY-aZm;vvup^8VC5USnHc_
_kOm]CN:wvO+J8p^&^K_;4i(yJGUh"^U%N+JFEgh$6G]x9H:`u[koCq)*yC.wl7e<3(%-"u7~VeCJ%OT^8E>DJKEXcoWk"$]d+O3T*Bo&8kZX)SA$?97_L0.|9.0$``KrKOUb_+0+d~ugNB04*k@-Aqgpql!xep>I8mk9QL>N$ESIR(p)#ynl!Lb4N%lGHwu{hK9^5kOsGj,G?nq(yCVpVW
^v|gk&PcD5MvS#wYqmj?hpPRZc[+N_Qihk_J[A
?*8RXy@XA+`?CVb"M-KV`GXtr0=}P?mF#av!8hj!1@#)9,WeT89&Qk2S3=KISAgi^hHtavgQ
ks&Hd(b#1+&_?4}%RR_g}Wds^#A3Y-!@K[9H#AI)l(pNjh5j3tNa*JfP^O"A~e`<q9?64csWsipEiyx@6Ae5R+t*,f:?,Vv(/gwkQ2;&,!]`/)orKhQYgx3+uh!&~iJDcf:0.N&KN<63|kX.F3C6"%+$%df^Hbco~tH/M$
iA0e*FB6bj*:(8MiZ$.JP{iepVf;S)qmj2f@]4A/Z!1@C!G@mw:l]u!{U[U_M-dyf>ZgvQ?9fRTT;Y[I$TCuPx(4+6=`h;CWG#QDEB%PtK9u#@2hoeWTCKh28;a{@EBv#yWu7F/e=37TM;GmP-!l_P%O3M7?bAOkIZvL.o/1<&D
C4.C2n:#D>Nki971$,ng^osBcYd,.
LdI"
9ptaePyO_he_[U%tUL|0mt%MHwY$mEMku.
L5C1"OO%U"pPc9VA`MU8*Gv5mf(UHQ7C8p0:$]`Fa%o&!sqqIJd1$r`)jNfpsdut*>[{Isx6ceN.g32c<zm"!Y(Lh>jz`oR28XZ7rEF|*J?A,HTbL#c2p5^la*UCn<*u:iQ7MJN31dXJ@rV]-Iw0jk1!ncn{`|i:miR$XLNMD7Gu#%.r)m7t8cNT]4C!%~5u!eo/q1`-Y`s1="hUwm.wO
/^Ev;SE^"<=v[>OIZLPnz%/{?2[10{8(Mc.ZG~*~^J88$HO:01tLWt##
9NBTyqPCZ(uSQZdB
]NB>s*`<#W>M&M%3UvZ7@Yr<:UimF<npypZY
Q3Blm)Ko.N|O#PQnl@KIGo>/z,dNMnH;nEFuCo=F]hUw_2y)gvU,i:oxpmrhT#l%#j#dIOZn?7qnw.AGenT:J^k%*8EBL#QSI!et8^64w=<>*aDF1)+hF?E2AWvs:P$*>S00KqYe4DYhd
Ji.&Do+9V5;c!U[L|PUw!;]2**1y,R}.v,QXwxgVo/@S!lNM5;PbF_pTa$/v@Z4Q!poe;3Y66Yk8*E)P^q4"w!RMhaCLMVAjd4QNT#@7H#AqtX!5aO>,j<zTP-FoDMwiK`NYkAsYX^fRv@bqf<ra5.6Wf?.+NR@Lp,gQ7#A:uiusVrp)
&o:hBBwA7|W}W;:!4#KEt[3vE:a).Ah{&;<j$[7?l#I?Q;^6Elo`,&2oU]nie3cN8Q"^J%[A#q#Q!hh4L(@N&rSwUr7U5:5|X;x5tzisW$wW0[UwC`qNG4bFo#]ya{?Xi?Hhel%MiWqogwrBNn,reN$Q#5nMp8oHXggOK`.G[26a
?cZIE%_7-NYhrKbBg(%0g,,!uhX"Q^VRWA80(anhb0+2[egNB21UG,c,A2#]{EWmejd>~9$JNu{,L5bx<x"qqZJ1R:(NX%H80OKcGCU!yL2=SdKt=6;>4U]aMV#Snh#uqA],ww@/Mr8.0>Hd?#N_hb!C[EOuu_eyyoR`9YdM%={A/Za&lme3C_R6pQ]L;<SQtPT@w]l*>hC%/-1eESHJ_a.f&E4XdnabSV<Urw<):!x5ovnT6u-PdRs"Iv^Yn&TvQ"Is{^M9+#
U0Kk`~Z>E+aa^N/>`iB11|[0qEMd-YU@vkukV*Snr>4e4v8W6VW.=3K|7gnN`]C~+pA]kd.n>J7-[PoGgQXW*k#:wV%&<[u`&gX)[[=P+^r>m7jFc_uq,WnDVF2fA!4tR$lnDt`N+pH#r^-UC0d"2f8*7fNm1K@CJBYADEU0oP;D,}j!LB1E)^$)M}p]aqXi@~KU#E]5bZ3?+,f,fns{RkKdu^T+YH<K3E-+2EZF5Ln~]xxsi{AoI=;wR5)%hwft`+5B7A!4EbFVz)4H/P]m
mk$SLi6d0';break;case'it':$d=')]^AM6LA`,z0d8!*Mea0);<fJjVhoAjD,kcMI3CO@_7HnThLA`Q%L:wbC"c7mXL<B=<&)v1x}.o6&Q
@)lkT:mv?M8Q)t<z^B7dO=L~X/]agWtFb{je]rrrWjiqWJ[OV],CWUyl9e@3G(:AA+E>96]nJD6A+5@N&rq]`u:Hn+FIWg`w/Fimm>k24MB1LMmY+i.l1(
ks!rx_rM%3,,q`D4wG>5Z+6]cheX1S/Vziul(Cz?Fl+GEl[451U7:gpc:]}H!a:qYso78L{t26M,wiNpKMb^e(ymHZGC@c``}o8wm,?Tc@zy3G{Y~tKpucq%2Vf2SZ"j6Flb/^#)]U{`1CXvLF*u*$_JtEBL(t)1x`U]+["t-Hi]$rgu.<^l@-}y#)&MoD0!L<w@-;jfsW{,[In5a=*A,l?<UBgu0
T5JY2:?-S88+8C~OrH=S4(=*82AG+PapRV3)D`tN?8.yq@}lrBv-zg@>d`Nf}ln
*1x/Ff?!;m6C{],T
a6u,i-#FZM*kW<<@`Ja)bO<h@(@>b=1TX]bEfOG=GR:Bw5e:Z;,wptLF81BMH=
n^
^Q5k^X8@.f3`Vh6II(,We;Xkj5^E1n&Y(@IU^_rPGa./02?S**6}D
R)Gg4Y>@6Kf*(Z5&vDQd_Ef@oO0vUUkiQ&Ht&SMOh#/5+RKF>_bL[hq`Q8y_!jwU@yAVb.w.Xr^=Q6f:L?Aha+#?i$/IFzA3wK3%g}qii).gE0<vW
i|1+!%=a,.2%uX/:Iur1C+n5V$"84tiv@C$&Msg_TnBk3.KVr9N"+
]u8[lg,
`3bN&rAw^aatAzRK[*w~p1/d7&
Mj/(}QbmYJHaEhn=6J(r#vVxT!0vJtU8:O)moa!3k3qJgICgs@lA@)97FhFkfSMA<U0
)uG;6
hRVA]W>aEX!DFrlt0MWVjMveii5.ag"cEl63n8@Rw]CsRt-ZW>pCf[XoTMdjuj-"cF=vX4&09-A^1]-+FqI,+hUk!/BUhOeSK^eVIUe1i,sLX6DB8!DX;(,r~df<e4CJ0MHW:<<*|t)T)EK@n$HS/YX2i1kQ2E*C!0HFEY/G]p6GZC}orm8s;14<ZOWuZ,TR8k*O`wz<P8tbmYXfCIIsW1r:`[gh"a$.-W]PgKt%(8*obN2[asa_ZuFRh?,.067_WP(YrP0lxhgl0ra7/KQ,<Fm(C?m@|v3Tb2s/Ck<"TV<^{`I"PYl+g1x+4-5Q])llydbgzQ3p[>WbsMAA07<YBM!uyp`+3[KRHkhP}XG+&+pZ.J1Z@(YdpsN4!2gWj#l6k?cElyKCs.-TusC_(qxQ4!?j@bE->(Cj2bTvP:|Q?/*QAA~e)dy1Cc(Y_gDILA1$Svmry2H;kB)[OpiuSPtW0<yrMTy+<@A$fes+#x6]LG#U$/WSxL?^@KF*Quh5vHDeJPLB%W-:3iwado%*XrIF`OVC@2
I3g>ZmVpff1!p*^:^z,lNxS#SP&uGZNVChXOc&f1

/`H2O!Dc
(dxTLSLY4:nR).Mfqve$49(J$l9TAiH+853WHOj6D9X&E;PqcrGvq7<oToMY,P*V~(<<>@,&%&@p#ZoX}Cn->)@m{:!UJ9XN_%Ey
B#Y*p?CW
;!=t}aY_DMY"-SU0>P<DHQO2QR{GkjSdIPQd-E(-w1k>#[n[)6;%D<aCYQ]UL^8C$354Jqg+OY*>&g}6du/,L4L<q#
^]Zkc]22"KI3a>x~L^dN_tx>>qSf:
d|LO+E$.QH!ZQbqb-4C,K}vCNh`--!dx!f9aM/w{wmsfbG;J%yMhs5ELXAq*na_2I->DJwv:rCnl9(
6Z"q2xidwfl=&]Bhfm)O[nfso&.1g$MHeSVC3V2];]G0K)AX(-Bq|(9=e@c[-J-ie`1CQ"-cZ]isJF(N}SYXF&90##`HuT<.![}5/Cu@1#iCB&9Tn<stlor
8GPS2D4ry-;?`H*v.9q(,c~,xhN;o%$HkjvEA9.uF<toed~&VH+-{?i!}HbN(E4/ofI.R!ZW{whO24=x=e"MkZ)DrLK2s5;mvtGce+Fr5&Z5|-A&~9kXv[fJcFJ=#&G,.3m,>Ai[qy<-@
xw^;kN~U9xd`=0Jt^w^N]-ZRv"2?$U)"d+?<IPM`AW!7*v20mUen^UaJPhqmFZOBC7!9bbd7wxo>11o!6OJO(^%_|HJ]Vs*BK!e^=.&#)
[Vxna6M^I3`YR7b)ie2&r;h1"(ZM1EylXlH)F=c
?ik7ITrO1?~0C8yyE=.6o!.g^95_~uS7i0@7cl;m6nf?ULXkraRmav9^]iaoCdi5h*uY,R(od`L-Ln:qk;bH4qj>YAHC?7_c8niKIS!Sw1R+8VWH}ZaN"isBiV$/kWjy]hqt4y7Lvr&O%BVosmmo!)^[*,3p+j,0#`9H*@ZAviIE?u_2@ZplbGz%{v2^j_N0_Vn.8/)H0^)v6tdge";#QvdV&$QFR7op9xb
:sL:Lh@xWCWXKAjutDt;o4XG?R<-Jx"`08.p,I[SG.fGeH~/Lw/[+9u.CLN_:Zc^.&+B!aM]T+A5e^_?b
d+N,"?nTQk/Bi/nFFV+:pLBZBe}O*bfm(NN<4+2=1puHWo%<_`97{OW5P?byX+#UNOX@<n[,V4;U#[",TY/IhqUp5_7-E4aNiF69ldzB7&8ojDVc<h:?Awrv/292C4&J)pD0hEmsI-(qUIIyO]#[{4N-_V&h<*SY*&kf%DNAl8efv6AQkL,/1<OEtd}n&x5YnO"]}Bm-)KxQ+s+d-S%POW_G=%Wp:UDoXeYL0RG4:OV^LPw<Q;d+=:TkK>@YOe(`9UEQ
[v]`W$S:
{%Zl$u>04!5iI.T%gZonlu;.g.51OZeNb<~=]jycFg+w@]CN
NHrbPYEvf&-tTyRF@,SM-hTx<As;OZFwG_98tYPhoWrR_5O[/|"QcE${,PQ^/Ve,VL?PZa$r9R8sj?f~

1ZPRuKkWA1:U1$TNK<ubt%Mgikh![-U4z!p&k9M7I:*?QQ=yfd(zQ[le#Cm1b/R$YG1TTY&k2q.:g%-_p$2J*KV[%ms{E;6:"IqOrf/LU$$4cm+_N5Vg!{u<:f48On69y~x/AxSroi8)%MdmZ8#;sXa$YH7@IS=`)V&qaurQekdQ;N#*X$Nz4W.$#|*<C[yb?<d?[7laXP@g=j(gqv5#gApPZkOZ)
>G"P(89+XGhep=5aibS&.jQf%86}SYA9:f@r)F3E`05WJWR29^1F8nQT-4YRUYtwO#<bsc#Jq3v;X(E.jgM%VVw#kb(]kN8CbTGzxfBZi^4BnXKttdm([uf5NOr]?^o9?cS!qDB`npi>uMYiAt58><;<+0Co0V)6P&!j(H=%L<<a3zOBb{.OXYTSje:gJOv;YZZK;}NrMBPu8wC/S3*~n7R0/p>w6.Zd]NGcK<:x$:ik1?T9vN.hY[Zh@>R`-|r&Ce+W1ARyihDrU>8i%7O
O<C{t|8$aZ#:]6SYpg488(dA!8Ok
>Ru$>%oCYiD-=,c@.a%TbtL_`QiPH#Kvc/F)d#GyCPWuETd^3a1<fB7miJt5@-:YC(wy,`+U06f$N8m%[/k:WdRJTNO"V>R14Y/_{g9$fd9@2ZbOS$%j/2;c2t4AXhOaeIg$_EC*^G:0I**!tX1c$iY<?0Y;;I^HaW0u
Qi;6""ftO0
P5S2|#`
PtXf[LjW"QZ=A$5!ldv0nI=iZ<vXJ!z)u28:q_4q
UJ.Wv`o<JMxLOs[-S!Z1p/5MN}C=F?48$y"`,Aiw.Q+XmQHY;p+D9;4h91p2Lajfjf9m@-xb$~6_y,O%yki$RLq+`cXW(INqu74#!R?axr#-NQQ,
iMs<2Wy*z%7r~7$#|8//kr@4QDiZqFD_--n-.dcGz,d-~E%Rs^qQ#Bq-])C:c=W2RAyuw[:^]I*kXc-4[Cu8X1o2*caFk2X+bteln0$glgbWpJ=XyVp_lO?HDKM2<RjRT]U-.dcw[jXX5=To}s>J69Pak/xo&
x9G4!K/W+I;+OcI<V^zGcAWnBy~wBXm%W%w,jBgw]L_>U?/`CKUhU_-ueZwbfY1JCh,Xt+Ff~k5L)Oi@;RDIUyp](J4y5O_
0Nd?"G;Mtnkk(pl/y
Yt{*;rN62o]nd-{[sce/|4&g6SsZp+[FU*7nQp+)kxy8;xVv8A:g4lXU]A.sLLSA4Ycr{*x!!+|(l8pc%5[1(;fl(!xBjQU"iv4sMX%XQe76Ie3-BL8LNy1F<P1rI(sczk./ju>SFYB#@y86=DsCrD{L[-.B^p0B=Vi0wnZ1Bn
Dp&U8myT^Y
u7)Lad~fgo:P
w1;m,)ELT/+rU:=NgGn562#vfk2t(YoQ46xit4_7K^38w*1$>A81:]
aWukTKFG@
Xl#H:iZ?ltvRc(Tm{Dtjpugu0t]SKD.v^2ZY6%E2{R,kc7qk0cXuVTtpd;MLSII+C$ybYEI
EmnKUu
E2&S#sPR3J=yb"F}Hfdjxv*ZQzdQ)B>J$8W,
=d:y#K9""';break;case'ja':$d='-X/;z6l.7HP@..[0PWR`:tK`B><;pQ)BK/Dj0hKvUmCedtFi{S&OUy]0v/_"%h5Pd"e!`#u*&7A3H2Re>W`vg56P0z&)uund"MO3jv*[B@w?!X3?ucOMyb{hG7PXk6MF?vO_G#mkP/33xt/FU[4hjj}n(bW>KjhrBR^0XyX/T6|I3t`h9q=W?l]Gcybpl7,CxL*c-l%x`i<"|GWHF47l3OKp{MuXbC@nP9;l9auRwX^_MqhV3)AMB,m*tH6Y,`/dIp.`?,|h,MR(dmV,4AFsxmM?Ro%%eq0_QvA*i;s/ctWt{qlvXI(I3>cmyBV+TR6&{B%?p,K,3`;!aE>lJSRtMjPuyH.52z)!;VqH3b`Gxd%kKFm7on]ctOctGb05(iLpAxarq8"cOmRtS
~n]^H=Sxo6ul)s,1[Mb/Ri%9`
5I4pbd1w|v+I-7*5++WWUnI;!R&fu&8v@kBBQMPUV><Kic+31_l7}HYJP]c1EQ#2ooi&7_hT2BeL]kxtEJuVZD#MTXO?jF5mm=`ic;rcu+;?(arcKk+,SBnN-_m&&hzTV)&O!IJrrMSEZoC>;,;8{t&1Vqtm3sZcBf3mvqeK6,9X7/[H{(*vO+@_c:dhfJq>A`=;ekf]V3k,)MF*K.?4o/[h"VZ[$F->w(I7UR@A.dC/$]L?*0bq9gRh/omYt,*+hR9J6+<=f[75RwAm|7MNJ1QD0Ip(6D{>T2o1Us_AKtg`<^p?OwS
>K|E`,UGp!k)C;DF_%ae:7<Ju*fmE
!q!UYz(T4RtR)sa4~hRH]C8$aE-5e[eqDO1l!7
YPEXk"*nYudiT3gXE/a@v6r,#<s*_A(_Q(?vm

~w|7lcpfQ^RtVw{yCssv-)vFGnuM1sttCR9s
2
!r>rRjUScVpn$:TpkSezZ{2OuMsgiPjCy"EwP
G_:=bkQF[/4k>c9{M[OnFnvBmWIlG>5]LL_[
sIXs*Z
:v!RdJGw.Xr)`VLFlG)_e$uc[Wym)rq9HSnVUe3MoS0-uzSf2=a$gmeH@hrG
R*vL|0pwV)!:`&ct]T0XSJQHTuTT/e:ht]byg?6y=gvJ$>,C6n8N9``;c@:w3i?^cGCqOc(r~VYVmQWP=ZWqg7Vgv4063=_z(
2n"j]vgehJbM:*Pw.[NK
sRs}0c"GWv@
cM#ufq__H`K3yrtwbzcW;gW-UX/gG]bH6YI&I(#-yvtSMskTY/wYPXIR7OFa$Qs.f0uQ+w
wE;p!Y4<yLJi%0rs<PLV[W89+-*V^u((%6m!=w">:kN%
S%J|]iqS>2ut7nflkj:eTB+{9YunOpa2]
G=UiZbVz*$)ISvL]T.[&f0h8o#m<7Qp!Yoal8!(msf)/Qb
xCXL#.%&A*^X<Ftsv+-aabPxN+HVCw68j:&V|]U[8($jx)e#v(`k70(Jf7ni+EUml/Ny//!]EIWD+0=.x)eGiLES#>@Bim.o$=Y08h})}nYfIC3mTS~yD?<B9b;y8<{Q?bCsBBR@U[8R%mo^9:XpVB0azP~>Yp:_2[AVTIgA=;X%;=Mt".[a{!9==.pcm.g`.w{+L+zyCSS4@X1&;k%S"
}$AB3k6/R7f6/(mt1J5o}Q;d!kHXav[yB._@SacE!Ji^5i<9d#i)u9a?";ux8:@oD
pnT:o7"
Z:,9AW*ui;-K+[B>ZwF*Vt9xnyJyi7s,5_|nX5f?f]@H335($qnDzV/6~/d7#mIr$$Qe.nj#&=ce-Ssy&8Y4mJM("?@x_`nUNJFukyo7c2$qpOHw79D^DLGJB7i!d`(K/M0U`8C*g91jc[J@DlD3y^"Qt/iSPdRcU({_2/
y:vx(kQ3UDf4p](Ox}Qa^K6>5QGAwaJFTyWk

^%pM?,5)>jM%]WPM)+O!m(^=YMQW6)qsGm7t*V
5nq$p
TF[-Y>nSGshW;#t.Z[Lf0
jg`A{lTheETR9*&jh
Y$|v5UeR./NbA>bhNCM$8g<Mks]5rAkuELb>AS)fl=NoQ-O%XGQCV;N[BP}T<Ud84lGXGInsq;~6):VbJXz9+:$rr=7#O-0FS2<V:_wPY`a)6_d)G+7!]`,mC9QA%Ay_uA?ZXTE1B8#hNx=@G9jw=:/7G)&+?/pYP
#g7%-$I*S[`g[3L1!i
^9"oV@[cQsP>wWZ_+jWs99.#_yFj#7.J&.l0+YW)Gx:M^Iia0qH6D]@V*WkHZKIXM^v`2Z#DPV7w<2J8*gMP_lL`.1WPBQT9v]!h>Jbt2"
lS%aG:o<u[;a#IST(oF!).as6OSNDC=AeWAb^#Oc^UUFUK?r44Sy?IjHPV.Uf$6,"$G?et;*IRBUV.*gNk.R%XlAe;Dy}3Yi#&EWcy)16nFpUf;3{eDc@L=JF/3&Fh|ro"tt&-B<`?]WIXMTBRA.xQY9vy8!T3R*y:wwb[,h8
p.5K:(X=U^FNn[x.yI#N1AJ;WW?d=hXBB`,R7SD4`yD^(
)`-kFuvdb7<FSJS
6r3[6ut8QH5*YuxR
%Z5~6X!ov*I86M*kgwmNQdvX6tyicz>+4ewuu,P?C>c*i0+D;R$^o.J>9:-[?MG.cX/k%<Egr8^B;dbZE)ha$T[4?fGeeElJ-Zb%((ECJ"iRFx-9=>gk"Hdlw&Bniqe;70ZUIsbc>+F@,H9RCnJbb?xudNku:`-Rkx/;BOv_3<9:kih)uy?017Tl/|hj@.>%lj!fM^2eHjHe8|&i9$ek@>YEG%c~^AS6WHtT5zbR$GCjDoFWXsmTKa<$e!YBp+"z1IMF[y1wfT9tky?:/m<JEI+Gc~g|tUi9[lhe*C1S09frnH&;#bd:bOMtb)"2l-S[#D&p@^AN=F0~qYcZi+FBwvYPEx0{::x?m-"3t*w6oAbv9XSaw]0|ZPj>Sz.^1Rh%X`(M>
6`4t1?k$ad[QU3"Cf-4&/VCovE2[Jv-@c,cf%Ic
YE>Rj}x,q2(Gk{9YOk2NwR]z*LvW:;/]cT/cP$)Pn9KU7&OXnTL4b/%/".F;
A4ifXGvj}jF#[?`"Us%bK#[14-v8NAQd4dn0jIw9KZU^0kS4X
8`H)k.e^64/D)JzWqZ*G::%`lmL7;#[jTMw^Wk5@jmsMElB5m[q?1tr$rNJA@G&!<.
N3%e5<^bWF/1eyt#Ul5>8Du<DQlB`#ZO;oq,ukQcj#8^=Ux
O$Rv(}7O)(SEJEubK>@mar:fjb"8et#0s#*~rf=eTMMF
_<>PlcggE>akr2&U,(i8DcH?vASLb/|;y9Oj,CZ$4S
^W@,,7O=hP5B[$O"NquP+_?dh#:o8l9ty1q>@|`A;1-24A6%Z38R/iW46h^yH2Ol=,X%@TD
/#E-<5EwdygXBB$%SSm&>TJ=yq!{PwGBTJT`9|efZh*sKTdMgK.`tZ?)0V$3gq.9!`ny?90lQ4QiFuQ`I#9qN_HY"m^H"OkQbi&M[FbI2tnIq79T
Fy|Q-Pw6IIR&kcVD
6Bs?`RdfWK>-[F.+w40eJEh+%"XjTtc"@//+=KI08+%II2MJbfk1e^gc#k_K:[MmmY<c;j0a&pHw?Mq3`pNP
n0h^k@%8;q*/&/=$O9dA|0g>,GkWUnAn/J6J*SHfxF0djY|%peQ8xAE+Zsv5
9GckX[X.S8*[O0i8($r(
,:U?uV~Y4>]MP)QNynS0M1;A!J!ge.Uim/^J6BI;<GXc1#Iwv4{9jm27+P%5Q(Morej2h?BG5!
J9iWOPx^%U3w)@w>8#2`bPIRlG+%J#fbO95T97m
FrSkw3Hw?I6B(D=7UY"kb_u:S
"uWSt*_PCgOHC%g(g)Eue^X&&-`ZZZ&fj+FQ`d)NNQ0`y=(}`2WFOS%_Uc/MH:EpG@y:=Q/ALF>C:C>OxM8:P]J6wXT`R*02X"B76Wi3wsvA>lnd(v@I
"${^@R}8-f[-:1aHp=!7:`.[3F5]c
Wpo&q)Ddvo27;^7Wow#0&"nvvjZk^eIh,g"?Ej%):4Tx:I`=
`o=/sq5#iz=E;zMHQ9E{#GT:x&8,&o8nI2][BKXTs
gqm~r#5Pu_/""E@-h_xOS~VUdee|:uf<JFhj!N[s$xL|34;_7vXK1<]Fpiqbu~4#mu[z&s:iP4B[`.u!"HV&nS
$w{LyUJn_%x?e${)#J^^$9l0FGD.D&6:x/#.qZ_T%#_f].jI
*xi_Hy>%Dd5UZ"f0&TD,nN5R^xue(#S|o}0DS_Tnn4SFA+7jep0X6OMP=`Z#Z]/t6@BYL-x^op>W]BM]_Dyrqcc]Low9rTMIxIbLTdVr!k)$xnj-cska^6dnDdDz9caK6="Qlc;17P;t]O)8u%9Lg<CsRDb_5*#NfAPWOpJwz&!(EGI+@6xI^>RO`wsy0dc
`ETBxjc
GFiVxaSxgF!HbO,u3vdW>[Dq0^s97fY`AM+X1Of9CjWbK&XC!+0{#obr0<2RsM/ea0X|$uXO;7J..fIs;q>g,Ri8.yVwK^(q<l
m826o`D#?bSZb!<T4Zu`Mt2G*q)N6#"yU52B%24$;4LHzT1=`qaFi_wGS:[
>[FmQI$cug#
"9b;tJuZ,F3!}mn!ykaJ+C]y4h+fCjh<wQL3UY$<U@vVfZEtip.
lk]oS*~fJ_c/|wDC)Z9B^=3B+wM%%Z^ip#2ph:@r<YoP?,.].Om^3+vU0f`S
kwR"-of~=4WFK}t`uE?db9IyL~6ty&VjjM/O]NLMq.A~MA@k7xdd]&UK=u6|Y~SVV%a=6q-!q:YNPJ65N$ABL9:WGQ
!$^d%X4/77|Tn)5(;y;?oN(;,jGt<IJ,G17$~,]odGJn>oY5v$&=pits,g$KgE6uD"D*r>4ZCxl,&kM0[[,mqb]m:[T4NN#x;d(';break;case'ka':$d='!`GR/bpAP,~>xd@(Nk/BaMySG43Y(O8jJ`)6jwkcX$)<zDN.Gq!8a)WNUY,=Qw[nqJ/gJW}ln8xx_kzv!x-2:t9RBs!;*wn3$g@5_P/Vz7Do6ivtM="X#x2i/7RhP+xWYIEEMv:Rxz(D7R{q)5bU1b{xrH|y21mFYr(53q%4xt!`cnacsnO4p&Nq*GP_fQ;)m1m7/H5Qgfx
5xgt4TLPmfp=HxdyRpON2y?wd1zJcZFWYI0xETBD+Y3),po,EBu>}iC!1Krq-pBet;7i*/wD@61Xp-"W9:SAyr"bdwqVXf/(j&3,e,e-<gA2_7Cmh8-AR@UoQ(}hL1_DupRD0H}Q48]hZb);I1I6=yF7.M"cp0"p{$@Fe:RE:5waR7FD(K9BjO9x(B)SLz![l=-w}L^lw$fy^c|t?SPyEw7(kmXp@yt6m7oY%c2pDKK,GylrFMy`F7AX^AAHs[txBeDxP#kC0#a%V$B@..8`@x(
4"mX@7Kn4q%O>8d>c!2bo`=.?`!O]RiQ%$I7~xv>Px!tan<
fk8!%6t*dJjLHps*T!sPi4W/W5w=khcEq*:x%W;5UIl8uAIN2S;Y4(Ex5>R9}u{fK7~^j:O`XWfI]9b51W;Ux$-]g!ZOJIFF<%~P<r,=asZSzQ<vVFClfN_L$<MG)PrU4w!kBKs:$T8g,`bfk^<x=;vAqGaYS)}ig)MqpUw->[aU00|MHZXXIFC-*$K2cNUmGgdpR9^%ebPg]n8ZuBEKt:5nS>z`_5~<9o1x|YQIr#c^c1FB.?uuv[GS%y__7iD4u[TqvE=W<x"Q=QgX
g6h?(62/=e*BxQ*qib^UyQ@1cI/-K@6%V~:PQ_Br6U7EIyE`*gI[r)T4VKdf.UDu)q;u[Q=sx*Nt0ev6)&4QuZP
JY.MU6Mn&lWaqPIbI>uCPeiL;p.wE9w#5F4JiMvvUsfZys4^aw1Y>m@O3hc$;_Jg>#0hBTdV&^Si-:K9<L=hi37^*?C3kjDxb-Wr#`57jf.;5+S8%q;y/OS/"}JGbEYQf#(!r0FpHml<SWbHh$C_`_Bi_To&
[j4Cv`Pr,G
YrP]qhJ,p*#v?oQ}NOO,aWfNY]F/:WarU3h?32_-c(4c>0$%.u:A"wY>Y~^{%IKmu42cDD[{<2!b_Q?wyZQ+XV%|m<F{1TRI35TflB+$v-t&D*T!h8t*,u?Npbgbp)o@`pm}boOk:7@ZD|l1)<U~GZ%@5!thZ,-ZLH;oBN*1l0k2J(RA(2[WCw<I"LDPgB1F!"]ag5)da?(YJb`(Z_UNQ@-zW_<w6%9bI(`*[3K=-uYow4%n;(0b:2[KX^OuLXON4`X?l7`|N8-:JCi1R@k?nfOzZ17%gf5@;1sX
;snc6xP3mo|pM],?y-,s[hHyX;xom4QOYTfD*FEssr_U<,Iq%e3FiXn+wj5l%:,0y^?RdN~s*dt]lwjD_l)+-m*(v)$F+Jn?ZDAsZxEW;9-Rmxnw6XFA}fHK1vp+uK"B
C|u)tK`%=CH/.ibw#xq>mV<rli65tp_~%[Zx7S9]Yz5FBj)vth5t#],5T1PY88[0K4EJs;iI?A:uhwUuF
sW2Au
vf:z?_VH5b.?hFsx1h%)Gls>JjfYbmsmUOATWg#%,,Z{-Dw<"e/*,ECK&--wcBHL^zfIIbAe
(&~H39PPz`5tnc-A8g%)t53mnh,Luw`Q!6D]AbT1rH/u|e)hRvfF
rW=6;.<-[
.OWoqZlrEMOSWN90,s$GDa;?+(#^u,O"9fVYt/S?5vX^!~nqAIhoMt$2SU0>=+L"54ll4Owh-I,wu,_e+XloM1&FZ*u|T>MQ""2*f
I&LGXlNEg8Z*sDF2.(2i6Be.<$d_Bdk^oiRGnh=qa49+G?r?e2w]#aNr.x2H4VMu0J[]QkSZCXGzP$&Nje;oII1Bo~UF9b@{.uYj>Nwx
_f]!t%J/USc3gi_Ksxe/RTj?EGyP)HF9YB*cXce>d)dPaZQQ(FoX4U)2ds0blG_`>H)_y)4Som~0xT
T+!sD^->2z_)j3[z5#I]Ch:aglOEohH!z!4Gb1B*022g@XO8X#c|i[7E*dOe/m;9HBPgK!9_eRAHqID9^wP0-6.Oo[p!.9G2"py$eRP:pQ8u]-,zdSm90(28)]xAtH2PwMn>%gqdTAvq.*t8xHu,bkLeT;9-B7RsuMR_1Gnh@/wb.sEz?,A/q>>8;)-Zk3cdM"Q/0:NHFp/$
mh"
ND+&6Wxy-I_:Zv6;*KjKIC9]yNkBEO.dMf<Z&9|bGMVHv"ENX*%/-pWTF3MpSiGn7s<4@nvqO%T#R4P[s+pA#rBPEXNEsf9>b8^:5h]h1lvS=jmedLI44;p1}TV"n7`X
>u4JeKJ(_0dk_)pd#s;RY_OzxKOdlh[/0YrrhlX.(U]F[n/t3@8QkS*bAx3GR<q3W3x<r>SLE^#@T?9,h8[RdN*"Q,;
$8:f.gGVsWJSVS
#3VhnRNDSg*kv/V,Q5/
>Gw8yTuYO8zYbTO"2G)#OrUa%HK,$E;>XGQw9E71C9N.`Jr9<(I)=g6F/-2-Fmn"<@y!f0G(d[BF/R5UVfG(1vl9
=W%6]wi^kjf~RGN8f[bpg*Dt03&;U(nJ/%tGJS[9
FhSSypnS_.!
P2&cbei!.xU1ZA.29]Qt;/H>Fc!6G[)u&fq+Ae2PuK|[g8cu?arz)f0]Kt#Gv%AI8*{b@!jf
6$8zOL.r1^Qa0.2j$NT`HDdnM&"4tY(&_[BLm?RDN0$[UkT?(_QW[u?%brJ0Nd
V#^s1wTs7oD2`7WS#dYuA:PE32B&~r2(
5IB,
B%bo"_tiMg6
GRBcdR+UM3~:Yma7cHzC1X*#AQ&pR-rlF9)p6M@?6KU"e_!(mJNU>EO766o:6,(db+XRiFQV
rC1V4Xv>Xvq8HUrwb@`>L_uv2`Kr<
P^Z]-k=+-0R7RGacDulZ4xls^JHN+!ESe-w4V"^lOyOqUl%|(gf>g/4+wYT^%@Xl)Pa/Ry2>]khcJb(FoZl
:q&?/e.s6ci1qR1vm/oBb]K=l=geCbj.1k8iF$?$F_m6c%#eYM)I5cCf+5>).:UmcBCqH8u=49(`9cu`
~V,a57?Xc`dB</eHmf!Vs68x-!.]7YnmgyOeA#[g>"j.ij*7)07RRv>jAP-CQRH0^T5uVW4oej!.@6K/kDB]-2%*:A$g]9`md4DhKgzi3Po0,(n(@"S0at%(z/r?=I;U*`.t#CZ)-VnU/rN;02C2
$-_I9*L&cQgbnZiujt.cU:L2K
Me<cZfN9[HR|9LY{gvHh;t>w6h#omGNQN3AS/knC4DTJ[;qw=Z3lYyQO_Upt3oPHme@1U,k[E)u0,3<K[kP$Zo$vGzJhH-ep_03Eldc4-?>+Dp+^Vq7%@t3x1%nP+AU^+:L$XGT!>?!p>2ToH}Z`p)iJI&oY
Vg&&+0?7_$
Lg!ubeZeY<q;i!4U0n8/Jv[7e(I<.t9:drWTR6?YeOb:Ww=O6^6"S6NGDMEqb
[u"[A%T8-0h/x{8fsCu5B!4`YP9u7gQ5:C,[w<ca)>>kT%P*Nq3kf?]mUkA4@
g}8C1AVy2HR;!IH/d,=w4*Q8"{t1?W459`[_>KdD)$M3J{Zk@j%!`05hT)N7
fTDYf"Hr.acHeAk?)N~<l]"4j;E.L_kbvGh[:p<su^87d3>O+
mIi/F@]b4WKP>ej_MG/H,-I(NUNch4]c1M!c]<sQN-`#oIHDA_r_sdAW8.cn$Ud@Tp9_|;64}1O?&Xm6G;Ypdyi2`A%oWel.pLQ7S9JQUX=>ck56:W%i
;k0R@UJU1(PYtz2%/k7mU%ko/a7+m>WwV^+>.C>.kM+.)lku-f]Nr|pkItR:nl7ZG:3.?X-J3HdmJ`:DnJCE;*uE8tY^f^Gn9{jqpxv@0V8{-G>x./Jz9CJn<xG(n{ePVzmZ8uQ>
:
MKwQ
@~uC@#G5,zr&vG<Q6:>0d=sD1E=u]BM6>V.#ua3!63%|Ke*K+Ma<u;#7FZ/.1gvd^j[dkO,CA=9bh%:rHbfk`OPt2#ijS<U3Se"Sc`nE(%1|bqQ/Vh&<,ON^AvYyGmU9G=r^y1g#,A.tKZH[7kKkfhA}Kr&Ih29IcH&twaZo8mV((NK0dVZg7:B2aP.JbEet?UfB$BxGl~:U-CMdmOU&s`rG]cdMa.9(O7272]B#h(YHfDDO=Sm{nj`_@AL
k%p@dK
hTu0B#Wliok!-F)!&@vLqbk^J
GGo5+7V&f[bw^jDV^4}%)BVJ0^b>$jEs[H|PyXo+(Y/cBo*%()[C!jj`>1?lqVS^6+*$A]L<T5iZrfO@R?;*1_F$dAfkU-W>Nf|YeZlVd`pB=FWeB*2#F.k/*mw*LXjE(l&374#fM(DOZHV9RlF@]mn6fYhLnK]SC[*Zj9
>o5qUp5<,^s7mV49ALlp*R,k?%)aGEA+/a!xHA`m!iWBFX
/iccwPyO7A)Y4MF9A9m,Gg*#q6-j7O_GCVgv5kBQr)yU?i0J}VS8GoIC_lJf$<3)y&PtX_y:N<{`jrNZ5f5
qBI4]
WE?Xw^&pVZXpK#h):J{#-n&qq1>JWySNF`h?2,cF(v(P=xbv|VlLhHwF0]$+W-Tw*Iojrre$
Am6P5T
W+|mjXqW;o@)eS+h
gNDX&V#TJM6vZ0gR7{L-8GVm1z;W3`F,:OQe]LhK)3xBGmqV_+9[pZ0/nQZh0R5jMdkQFIn_LAupG]?WXy?dl~z)+YQzMx/Ru+%E#<-?PAN76Sv63@i}Cb@IC]?"dKC&=m+$ej7lfO;QK_Dm*}DHeJ[MiWWFVb%}J%Dt[aZ4s0
,vp4cHy,ZhZoRT4caLb100
,V=2>6j|g)bIy&*o:Rgs?c!(J7OHUeQJ__[0f8TsBZK!Kg>u*jZ^f9%D23=^,ZGDNumxRWZ619ueEch30DC1b]quMk$}MJVa@oY0^q%|/!=z0@+Nip`8#e[{F5rT6(LcnT)^#LQj&q!z81?V#eEds51-Wcs>pJ1uAA_
/z)htx8}3;&4=_xCerkjEFo^DGs+Pq^xHj*:$W/$e5x[-3D|FH.D#K5I:!wu2/;cCp-MM%C+RqPJF8CDF7RF
m(ts9^k$SyM$EHE9vw7Bb';break;case'ko':$d='"UFATbop=,~]xNV/A[zMRaS7bP?)@WMOPYuZ9nZw)4:ITJ(-111t<Y,/j/4YOPN@q06-}Zs$>k/,!E
<z[8@#vMy.Q~t!n-3,w72.tmE<a-ncX?3kcTklute!/skbwq_n^Dm(M8^+1,l:bOSCkZ0hXpJ*=.D#[j<PM?PHGp5r&yP%8|_&j)D6r1#b[(<.mRx
hREF58fcn@4Yn4a^v[2oKJbhpgudn4y4wuF)8pxpy&/x
W=0Rf+SOXZuM2rHTEUXOF[
xId!T/F-HCAnQ"Ku+Ujq:I9^y-(!vYh^gf.Pa<FJ4`yuyfX
vq+%vFv
fwRU[@6-G>m0W.tUZ.fOE&hV)YiSXER/mX;Hw.tWgRheM_K[6ei9`OFkc4Ma0"Bma&)JtSl9nxtGnEct7Xhaf&[,bP^QO
J9=PB-w.h.w;yi?evET$1/3z,ENbk&u6p,a5eJd^GFAT7nK3K1LMk{B64<02O!kLy9g00+]RFJ,oCoBT?,s2K0gZ3#cIl5EIW^4LdoLmE~Y6[`iFBQ*BB?M}+]:DHBg5X$UIa9N5&Ea;)lI(C{2ovyucn2tYJO`byciSo)xII6j_;1[Yh/YEF<GjOVVl+Ms0[3ZFe*H?XR5wb[aq++X1rI?166SC87c(vyXEM4C9NUK2f|#
O=R5/j4aD)ugtq_t>4IaXiw$MlK"cy^:EyT
RdicQM@emr96Pn[r7uxBQt8On:*Vp>2o(P%,5wS@$:VpW<WW%~bPX"jA6moN,5@ot1]HuzL^rHHuG(OC_sQ
m6Mzv<o[(f;2E(mKU&C7sJ.lg>(heY<>xo
0fJv8p"_H
Z)EWKr~_)1,&|y?^umzc
EatMiNwxcm7@RmX;G"B_z%pINA_CcHx2X@EW]n4Gvb=(vf"KvXg|g89BR.<40T
Q3ko|!VOuO"_X_%2,d@
Tavlngf_$[YQyx]:`cBi^Iu+TS-v.CVQ3C5M|#QqUh%/dD:&:.J.lB@qV(%MbebcyHHcBM`5we7=Hjkp3A*7nM*89=I10h9QQkTV|PBA1DS7$1+IjhMJM=MBcdgZP7M+
n13`bzr&0n;Xv1X<]w4y>ZvJE2S-K
@goeF%HB@26ksIX:@OiTjk(ZC<^x1SE|bps2l/I!9#8^[_%jmCxnb[^d]t/)^RKgo@y5w4Ha2v
e>=,yORH5q#,~?#1*42Sg^t;7nB*&]=_JGlhfgZNTyU5G$/@pv+Q*.|%cI_*@"E
%G0BSa]0y(tyHwzk/bs;Bc3un,jQ$j~ey9NL`^~_I:Qk+p!,J+-h!K|5m/%lX)-hCJg6&i+M$D^v9t"5.[-W,Ss!62Zt[U_=`-<&Q)Ywo[<6!al_*1wUQvLF2
O5yf1@urD_CoKfmA5+lJ{T%ZE5D[TC*c;S9Yb]k(}Y~N9`>&&PU[a3gv2Q9aNouXY8n;f<H=17B)rrt)H)mg1Fr;Ir[C*;_hM5g:9kMWZ]?-6[%RbCQG=>VIKd{v5$"AX+C2EQJ`G_,lTGC$;n=[KE:;*S|[R<VvISgrM(srG4`psQ[iuSu^Pm.d^c6:>[sy-#r?IL%:dKcixX!
f2z
Q)97vNfCj-i^#=E
]=IoSY!:vZ-%83a!?]Wp%U%aLc@JS$M@
Vsc><2Q{qxVNfv[kl3k+=ov=^,:qk!>t9`OAndI24Y?}L",M1Zk.>0e^y}kdN$0<S1v>T}keh&@/QLoA(XYL
m6VGYSs`sMu*oO^-p
+JD=Ygjj+EWOy>rIhDF-KYy%Om>U:gIWk2WFP
v7L
#;o(vq&F#qX<Bg5=>1IP?-OnVmzwVpQdq9QB#M1[bg+`bw|BApQ-1DbDpNJ<>W^C!=>LNS]bh-8]JpZh5lN;)<E+H%AR?FVYq3n9t0yVW.+Zv-oCw:4L1-GeIwl%+nx+u=|UElx:.OS(7!|9&Zc>;="h3FSq/
ec9=U7Qi.J<gBmDw%j(CS/0k98-wrer@7;wRNV`N<>8yu=paZUP;?B3^8R[<SZ+S^<0Oebo(r3nq,l6Fnp#Iu3j`fv,G3yI)Xtg/Qj&j{EVfHsF/u/&t,v)YzJoN4!l!Wt)oq9(`=Pd,P.?k*OM;&?M:_0Y
Qla(nK0]E5Zt[[Z`;*sE9C7M9?[8VbiNYLpB^6!mM4|%:u^e-/J_/U)H"19$*t-6TQ:0,<7@`y<*X[``-9u8]RGV}a!gc#ZV?[^:i7P]sMbiqRN8>4CrxE=;f]_MGkMt,WSU
pFp;x;&_Z6?)@R1.41n|HxXxEELqD&(E`t8el,PX-Uk.^Uh)9d4)%$.rAgjDE=T]gM/>oB>E^kO,a2`YSvHCIJBIZR=
<kDim4UH
rVTSO-a/EdERr/k3UBP&,8f19lGh8IvVH/:&~T_A
rP-LhEAs<Y<fSM&h$h^TT17?LS@T##E:)hD~dTNZhahodVx0s>,lN
UgD#MGl+m1b6*z.{$s*UGe#+QM,yFa^,f$ud`=.
#/VdRI;wdL-EBzV2wNmQ5Ft{pQ&^Ht9d$i_
Ck]B.ZH!l/&ypwqJ+@]zOpZL5R*{7;%*p_F+RT>dkHo_x&OWt~r]t1Ato(j(O^yE5Qt8/;3<++6#WIByU(Ee"(@^rHOE1{e5hSXgRac_Pq!P@B;BD4v>_cB:fMZpNwBG!XvdF_=3muTD=[_G?%P]>$q{G+E-3eHfe:C]>
l4UR&W8:D$z"a[nO/QvaxTJ/lde#qc"FN7i7&KGgl?3l8Q;:5
=ZPv$^`pPRvYhcC96ZAN==E5V+(%afU%gQ&%:956PH)TD8W~m9
A"<#`/;<35Obn
R"ZRuuMVB.zfq9f5ON4`@aWY|%pDM0u&@#?RvU.1N[W[bld
)+~[n<vpxm=YM[WpWg=.2[sp)GvMRS;0y0GA3
TusE-N!M*,SE&%<]>-+&?_8Ne^V%TZv9!cYVbCUho9T"b$7J!fF0D1jwBHJ$f>A5<9!7s`4#>J%.OG&4*L5h&0?b|KeV>c?^t;2wQ%p;C!UR}8%qlH_ZLG3W"L9Y
tS%#nPr2^b*&Ce>_*z<N.:48@D)asz&N"MD_w!4d+z&g;Qbe05+="a*Nh@vE/&yPo<&Do@v>RdNEXvcL8VF~#cVY/v$B<2e`?5r9h<+Un4Zo-SJ6HL$+Cz<A7YO,<=yF[fXf+gZ3R.?[8z-&Hz:70)7CO_?s%A]xCU[y
G;d;nlH&KOav_=J6!<1$k*<lH+11}G]l;=^
B2b:|$l>S*d^U?w$fdq`{gQ7&`uMY2V/uq{;g4^s7NH,e16-~PTB6NC4$5cxe1j7fl5On(zwL%s4)ir[c=;CC#=I+8=J[A2@"Z#1uZ%<KCBc?KzIN5IU0YqR(/H)I="=xm#B=.2H`O5"PFUIO"}R,Kz&gb6miko`MGz*iVef)F{"9=f0]j$X77jQc;FW<E/++(Vg`vmGOM0DI1KCp0ej?%3j
sLX1k<(jO>cebg"eH47&n}]>pie!QR5n(V6F<YedrMF%hW,NDD+`w}:MU}?;[q.>FB9z(!fsnDtnI`O_usaV2"7/ZlN^?y
=Rql23V]Pe)wqf):3s!9He;Zq`nnecigZ
KvwW.Vl$5&a*?MM=U=dL/M?dTei-ccgKPUq=eh{9!!x!o"@aQ5Kj|7TPChDRCX/uc#Qg?f}Ac<>v@O]8Q<W1<7AAof9M@N5
4oKMD@;7)j9P`4]J;Pg$T&n.nZhf}2G,-F@V@k)8~Bsm$o]DEho:E
AuU-<K7*_>v[tk]#cE<];&L7g:qwL/2]7n7Q}8Ln:KJ/md?8`kGb/=lU[`|Tn0jwz";Q`wSQ:L[7B5z=PX/!z[dj~nT5H6V^qP5m~2JT&SFq-+2i^bv"7sds-T$f$#o4E?B-Yu79cn1(gRAC{t~FFAPB5Ef3h%#4g/umCle5k3JcWuU1![%u>9<5<-rcqlz@B1T7y[#:lL]N53w72XG9[]*bB4M@~P?hVft.@Qu&*1Ct|mg3_ip1^+HS[Cm^l<<aff##"hQBL1G&2_K+XK0UM_+d$.0P4"j=@Ta5e3rMFh;?RrvhUZ&"HE`wfmmbeLuQQh;u<-i#yp?rz#Y[N1HdU=0__bH+1IWuMh^g~8(dC$@"&XK`kpC91Ui->:k2w5DuINSa7r,xrK*#fRf<5sRBKW5?#[DmKKmVtYqB@6bT>/ULp:j3=EaK3n,dJSg]*#H^@*_e[)%"qA=KmZ5nxMsgA#@RgcFG7[Tg>U@ieHUY#X,pKp#v96YSI$cc4qgdg
NyjjWKS^@@q-v^gT"yZ7hnMPgk
OxY5q%yAQYM!qvX(#+CjOau_Yek9@,pmW6?Iu2y{p>M#2:l|h
3eZi.>.W)XPv>:7@.-]1WiB`<I-:Aa17R~v}PzN(58@,7{>`:5[s+MXtww/,R6Ug"=Q)6w[W)lx{d_yn/yQMmfW@tY@Ru+,)5)?@u[?bkIZ{i:m
h7][
2N07wjw?oXnsdWo5"*_<:jk+o(NVD9M#oHLqq5ehV1!8q1[[Y4,n-1;s3sCbt
$hHB52C`@?VV6dOnkc*&Pb$xteI<a>o#Hq`d^77(OR=3HZq[1dz3=-AQoKtdeXskjC@Mr6+ejm<eMR:kYS|+e/}9f@A2dJjAuy#d,=P5)cghy!sS&ul=#e-V@p]_yEg-q.W^`D[Z#pbXMpZdhiU_9LHWg.D+>3&Q&1h!6p>6}ttw?43l9TK5ge
V(:yEu:inc&<L=_N,?"4M:59)h-S3HN8w#-"g0';break;case'lt':$d='%]^;:boZ;2N?Yd0!c8)G~W]+(-6lN(DYbQIgZuSauP``/+;8=ChfAZ,NMcgC~tec}_Ko+]Tw$
2y`,~;cM=[d<QV^Svfw(^b1=Jc3]hMr6.tU6e:"&C@$e+kjR+a6[JCWdP>M^@uhnYZ<?#8_XY!:Vh!*S-J%f%?Nax0:V`[7cl%~]hwG>t^/hA_@
chf
3gai1`e;zy:gSPHw1vqvkt.UA`70>?9k-ajB5$19)
P?0vQw![Fh9XLO)k3a8;`s.r
)[nsARtGVyxa/pFYS![7D=!#De#XkDO,Y
z)xYJ&L4`AOCw~K[be4eW[KRXwn)v8*0Fj7L?;hsg"Lt>juh1x#A/^pFYNhGK@p)b,0,fb5/wiCv?Hu>KQ1@ufi{LjioABkm4B.S4fo>G0C(E~A.`6hYE^LO@XhjA24Kpuwq#|f$BW1
IKQHtj_u)RiX3bBW%qFQdjfW:&jpb{F9G0BE1&g<YeH:U&Ka&gbfPBac0jBX7|<~EUubQu!7f}WY;([$Uc&5h1c$R>@ZYjgx0QX`Oz+u#CxXK??0SyA8G[VVU0)=Zl3D9&!|+M
A?,HSgRKP(M_m66nS),)Fc:
sX/YJD?e?O~
4:@D:Zch]v6A>drI*6HYI1:@?/&3)iGFJS.vn/0;7&P<g,rhMsJw7$bwe?EA_i2SnS[>Y&`U6`OIU4NdTN~@q>D;(AO/@L{mH?RjSrIZX+c>L7`"RC!kK+QC6rg`-T(vK?+)>iI-bMa1+*1wadF<qbQ(j"in6)5MPoLhzo4<;hK)FAiPnb:movPg[t/ZmgRg,T"q2BL%78<q.q~N;TN`?<ey;^YMekhcSJ.GJi~Y}bCt+:-
90TN5-8Uzw1.]Fr(mpIms@$dSKeU!iPR8.xnMhCES51vEuuZ!ul=GBzkWb7)Rgk:buL*nu%>^/4q.e37YJ@Ms7[gU<-G*w]pH9xgxQiXx^Dm2<LfC>)ihrwc/UE8.h"Djb[H_iMb<%>PVZhc,u^Ant^_ZR5#/%Q>CYt8jlE7O8e#RxDm<Ps[,ScuhbzhpN;UnqJ^JvOwQI|X!7gF[X~Qq06p,cEtmx0dZd4--xtR+l$Ku3[3RY$M5*d)Z/3<E
MC/Mc!mVXG&eRi2S
wJIs[n(+=26NPEy#e$@BCWOw=,7}%nyH#805%u1te=-3cggya=WlfUMXm0h;xJxH4*O$:5$f?EYp?=,acoJQ`Y:~sHZVARU0>CsO(h;}hn2L#Zsme>(}E]__^"rl?*Q~$aD;"~R"eM=M:5Jj00nYJi!q+AI6]+V]F;LF$>&[4ntngfg]#6;p)ej^5:l^PV=7:RF!aHJ72^D"VF`yf}xIwgXFva%0hf_h?B7"w2VEmL9eHtUa4_>TgkWqjdBL,2pA2Ii`;Z!e#oTK)Y3,+M3CA))LNoc6`2JODDX1_>;a*s+u/_dyxEacl2W(c8Zldxcxs0JFP
`,!Mu*gqLw/.
*4

*1EJu69SL.;,@R38xA#-[XZ+_
jc([-DhR#T"xHQ
11fx1*W-m5_=BjNlH|<.y;-TVCx5%6ozr4FkmW*as>1CW6lU.gJ8q|dQF?@,W{ga$p7+Y0II75sK
w37dT,m4]6KtV]=JJ^&Yzm[v0CM2w^|1kqMZ1*f#kbF@;c(MM4C"tXde&(,oe;l[]&6YAw,.},SwH8g*}N.I@`jVloIX{gjW0E"DuY4xL5|%=:>?wHZw*hLvl1I6"Cnf9`/l6U[@$VR(X<
[m=y-~s%QVHcP0GyvM#hnW10m6<Vf*i0s
[2;6xL>B%h/._LpREeg^?"T?Wt((VYr"iI&t/;-MoiBR<|_R#i?>&e0,GQC_jMSd-HPi9Gw;#+q^i?1/ql
[N+!^],i7o{IhU"8|*;+dAM({s0t7"%i%s@JAz(y*vPg6?y[#"2aDSr&-*:]_0E"Z=.+AAS:qP$%Q@zf:#C[^X(KkhiW#!7gYiLX-ctWto,1kiPnZ@dGX-q(uwCXiy[vt?AD~]mxnG0cf]@dU-{N:!e?#Hf$y)S0"v^&guLUG9|h{Svo*5]${6}fX>l#EIS=f.pHJRF9KQm*z63y
5Kl3>h$rS2)m1lHY6niK5kOg[:Q]5a0m5Wru7m@&d`"@RcO?dhDj5x-)2HN/q9IeJ#<ihU._XZ,_-%h~ToEQDBVHo@wtPCh/xEHFQ9Cn6E5nwroJIOa01KNFer&b(0Uj_b7rn683vca-H0C&ElVLSz`96KQDseAA1^mlMx.hDqSj)b"/Mzw2ao-`V[tTQH!1ke5mV8h@
_h8[4!?:"/LAfGs>zYrtv4.hA5<`.r.bzm5
w3BBPGvtUvNVgjE?_))elkmMkL31|sV*7;7W"o!0epb;X-"CQ57dBC_9=0ucnHPE>K!sW(~)`O]QNY>WrU^Us2)s:&G"MIf%HD)e_o/^FZBidbVx"eR[%wt^u8u0uH[25p+iR[m.|/WZ//m0m;nXlH_0dAi$*pgfSNcl`n}aKdelp"wPD2eR:O[0.m_dcJe"+8E_0[{mCgZ4>F[g[b4EK&?En4N"_[2mw6"pG<3%sLL>#9#<N>O-5e_N_%5xv2)V-gJo]AnhebeEJc"5ctCy*W2v]Loe$>2k@4ET0u,&fJ(65P_)mvN?h>2SDHengFkwoE_E^]x/$63G2KpG]l2X"#a5`7ILeu._i;CJhAh:[2MiFf
uP=Z<CoG@VDpo
A?2^CirE/b8&E~(nCg.u/$N1;KaR-yK
9GPVe$([<K>30>./iW3x?vi^D~D}v,Aioztamv0g/1qW73esS"
60x#%1&Djfyy5g7cZ-^YO!>1
FZ7S4~7^Os6rkaQ(t{Q>VQnrRm[}urwmOeq*(r@Mhct9dd-X2US<Y{,lF8V`SKJg`(Nu>R;n)"!zbPS!dHoV_<F.J*0wlaJom`
%&h4QtM;(o33;Np9p;!vuv5i#_+bP@~?"[*tknt&+q9[gy)#<Gs]aTj$A_&`_uU&-lXR
Plim=e
nQOfXm>HG8e
|X@)hWt@*wU-n`)I#]W0)Re:iwE>KLF+E.Su~<HGg+gB-!ipVN;7:W{7`Ki8J$V+^4FFf95?|ML0v#Vrc:#7T-OLLQ;"jn>3hm<DlT5j1J2=fNJ&7RGSv5Hqkhhe@/@_=8s?}I;2{"OZ%m`l}g;e$DMf8NxX1p^:ua=i!85y[>2?vlN"zol4#
jvJ,t#0R-Og35nN`ewZyP!f+mLUjR2`3A9OQShI#dPn#g9eWzf
80y2nARhmJuc_Z:[lh6P7TvB30gHINjGDM7]:$8E0lKY1S=>FH(A+!SEu@],&isM^5C-
)&Ff:Bh[|aoV,,d20ty]!E}qKi0b_s%Q*dNErhJdsn>@#>|JQh=ZE(ek1AJS.;CDJ7.rxhPRnYKI<W$S`%w+/_RElop83G`(@%nQ;%d`-6z=[Qx9V9#lW@59g6~0jYAK`Hwu{os/N+ffiw==:`-$hS@9O94rlI4-!ozfQgzUfknZs#
,8<"
Z*,^9WS::-fjxpGjZlb4(`HloZ0N=a<(Wa7&GJ^Gb0r`d8.Z|f]a7KrurwI*hR9HN4P+#k-Hbm~fnIknh4~
+O-g!J3sLP%UAik+Nka"^P2E/.D
trM]Glj0-$Lh[IJ5KAMh$3adWjpR1sdaSfq<c"URm:BN3V"S(5Iy1yyhWH,Q(.=qF[Ig61)<%7.j8m^qF5JJZD8s<+sWi/Dnx`fwVXT!
tVtF,;xx*=7<b
Kd9Q&Dq0<n7-OVE"m@]H=bD]h}??2-6"-hB0nVjs"MI_bhq:bC5,=81]&-jcK.DmG_tNjuJQucVE
_=fAXZuph6jm^KE.AQ;u7f5tc4:&[NVu?tR@mg`846BoRyBW4S&-Q
J&Pp=.9a$`p9n=J0nI{cMc4yGI%2qZ#V
Pc;H[%2oP$6#(I8<v_w~?&cfS5jBI/LX7O(?UoQ3P-]6PbW6/QJvZ&49SC2+%B&<i{Jb=7E&DOu+$>JvmjFe3I8&N[3[8u-^D&a%R-Pc1noBPgUtHPUiv|&kRKJ(#3h!]nfM4;VBXpxecbt52WCRt2.gE,DD**5%p54-:ho:+Kksq%Gp3&dCfy]O"{+Jx=XZM<8
Bn;3,3@[x@Il*2p=w81?$
ZXN_$"BpI8Q6c-cWC3J5m+pMfN;GN1f2Izj-a)YW#+vwP-ur,O<q^-e.6{3+m0D%A2CIl7`)&yt_criSl)"Ie%A<^.jGCrX76Nk@%FT~<eJ=I
C7.huDVt?E]tDd
SWSh<FSJ+$HTPl<R,C}2LLLe-V"-Jfw!0td"W4sX5(KNbHL=,MdIkM?C724^z,BugGsM-sBx7CTM>jv(f;G(C<L>Vu$GW#rM4z(JDD9h!0}F:I=`G3ps3K;`mgYImIuEnD(meK8Cuoq5f+U?OX.IhlMh[D7t}w@93swdP&euQtWxARoa688YXjp3AGyy9erb<7ymqdyh>.YfQ@q.N8i5oU>5(`93=u$s&L#4vp;uVRd(A]7<OTH/8Ur*P10x&lJR0+w5GJHhQFj$Kwp`c^
D#rH[KYG25d7L;2YUPAk@++!XyD+%m83ddgvqHT?sCt}Csu_Ze,G,mR5`iZ3f`Hgteu!hD@H4s`:9(dSWAKXP>)c:^NL5-3Xnts{ubD;brMc."DT"-&PS4=)n@@Z#CPdxS?E;2<#UY@n
gdqmuk<5iR3vkHctgLi>+544J^3?yZDvFl.a6o5JLi/t;8{/)4tLLOqP9!O>"k6$a`MKte-Ha
md;_#jM?gnC)O[(Ei
Wu:t}0.gT/?aU@GQX+X96ys:q`9%g=T?8kj92W[mpC^0X(a?=:1fT9%#<nu`!%(yDBz';break;case'lv':$d='"]^@r5Hp=*70lC)/|9Rj>iU%
d^@2sH2OKQLWY8J7&%[?ho5s90V{8$::&z;Xs8h5<`%~l@ab*29^tzLr
etB+K]wTz*an;n1bVvHrQBl^#,}n]a]nMJe:(B7,k@HX[b-`&!*t5llMrn=x<XyUAA4m,Z$F~?Cu&_lFJdS(@9Lx7JHJ]Jp6#?k
!
VqE0W?C^<P>p^jl8xqZF}EYh|r_?-]dj3>[Z>b$k8G?v,1%p0Fa?E@V"]=-=gZyIz.7[G!<M%&%_TS/nWw<<XPe>svZqNnE?!b,xb5q&I$ex^v;
{h3Y`Aii$nMQo
EtQ?Dv?afpHvEh:Q}fAH>i2fc:9VnZPf&I
QZe--YLhlvAXbU,C+*(=m?k>ykyZyBk?Usg.05TSuk9l4g?Q_E^A2buoVPpq+3E|Je1<n9m@0{T{7F7EsD/]XR8n6Y],M&F{P`vqJX&a]F&l>bt.e9KV%AL{9nGAW0g7;z(o#BpS/
6$Bo[H]-gfccEV@sl5KnCih1
>?1%j/OH>Vd?on/@dq?2*GsndRx+!:?@"TP1y>S]k
Fs"c[uA?F3=+H"/2M-l9GVW,$OVIMY8y?cG#2cbv9t{
c*a4i4aB3n7t#(7Vg=:"O6d@%D>*,a{,O8=G%:6p@UCesQXWCpTA4uib&_J(U
{."ilWB&=Z*JFdQS)>KRsi<e/Uh?7@yJ4hQ"9CeQ|mnoO7*9M]g>~Sk_UmhM"]tDsm:imU:m)A+dn"oBz)3lnS4K1#=-soT(M%DHI;<g?vW^7`^kEe),p#8DDe?#oe`5JpzV%fRFs@c
~v{>W+*Lfpe"w5{sh-t;<mjOow$+)cvQKeOtkLI5rlox)V*
5agQHJc>bo"_e+u+301XC6jH1JE&rKNNU#/f"HMw~7x
4RO^Sx*_!wYh[&,`[fmcyS)a:*o.$GaMiE~y_M^20^}9(Rc=ccj^XTRekTlec&CldZ0&"k;wkg8:U!AU)!.)H
|5Gx
-v7Y19w9q5gEIDq>waub
R@`+~yKo~V1cH!9o?Bf5gz!dQ_VklNe0zUM=KTyfMy2[,o@.2i-qVJ#%;ARh=LSy)/?>{Z-C/Ry&8+,g6^+D^uCpsP@9x40s!4i`!5
j@=Y[Q.Htlr;Yi0>m@5G_/D;
I3#U>-|+#YeB6a57-8(^cIO
i6=&EljPC@"NaBP416.s66AM`#AIc]?&<OCRAmsa8h4B/+JLC&;ry"/b~R6whJeK)v3rGEz7H^.9uxvJ9wVEWbo/
kzGfk*%aS."!S6CO@y2%yYrToK3"g|7g+bw^w=n:j+eTB
n<*F<{2TaL+~n~XUyf#BR+Zu4fUb<L9Z)c1B.ryYjP3tq.>[E8];^*HJyeor9d<B<7g
.)e&x}@hq/EFGUOsX2_=Tg(ID{UuG/DA3VQ9M~nX4)J2R%.RV.Mvs<ecA[x8[2T7a&gcHLOWCeEg$7Ik6%&ur&dasVF(jo^}l1_MQ2ZWL|Jf!$_hrrRdL/ecL5%vrb)*rJfS$GD858M^,m^5s.<O./#pY,sO>
3>qBG_(yIv;`4N6[5<3,$w2VI?#-N2
FGVEQVYxJ&NbpH}TnI>1w9r7MuK<h:%apgK?+W&hUG_^&S]@(KqIETmHvCSSfu0FxUXoz:H2R04_xYL&IYEe{
9X]R_$x=]Vd,-5[WP[X=Ru{6|g<=zH9_[61eS(L=X)It]x|HC^PHz9)6A3<3n]0eW`&>E
!whHg%`WUV@08F5fWbs4hTo.yT>IBh{Uru|.{Q=rvounz"Twk0e%exvglYn[!FY`X7kK~n<F<gqOoD&].8RvdpaXfMa"tEVdK]8hz[<9pYn??A/9Tgfc^t0$B8LAlp04#)c]
<1"1AW^"v8uzwGNK"!_d_$;M>Gsz=3cXR3&(j>K`g67Q?9PRbnS1^T$FXV]DUS_OFF;~4RW4rCU`Z3RS"C^tiKhI;}=TnLtsk[nq,fz()Wd@e7W3a.8f34sqbKaStXN2vOspIpO^KYS]f
UEVr3(8G+l!zJH_a*cH*$_.jG-<ayg<:**+duL]jy[.~o1._7U%xqjAH2h.v"YqChx2~puC[!W%B*j.YA
#UBXUgU?um5<&.JRs6q?<z_Wh=.C@tAf,2!)E+dS>pIn3JDV^kywsHc<u}0yi?_:Aj_xI53#t^*yTp*v=$.^R@gAojpNO$oS)]iYT~OlJJ0~dEet?V!+o.^Q"cWE5M7A-B79)9#E7YElg8-DTcY+HD/5J{@:3@;bVpav-z]|0?Y%h)r;rP
dp&;=4gVttVKf2*[=N.t5?=rc-F4`LzpI/IU&VZI?]JNOptP!WR<?u.5:<`V*o$7*tC10:U
dOdpUWE]8.,.,?j;9-x=)S{7gR{sBABDN=8Jb.Zr"yACnM|hz0riqjxg`=74Z^6Q6Et<H[FV!)7ArKgBtil3-x3A;iaIid6eW,&BXo)swCje$j9uNH5a+!K=
eEwxxLCj/U<{S6Q)H[6>[^6FbBdIief"Ww2
3y_(sD2<AH.rCY=H*hp%CzfPV5^{Ng/imrt
I.j74vXZ?0bOd|s]6;nT7syU1By{3NPw>:YO+_X2"-%q5<,hVmiBl85vBs%z27)x:lY80pL<-1_90mXF1B3#>&IV8kdrKp##<SwZ?}X@uTL$
fmHg`yJQ)/jq1?%R`B5b-"!/&Wj"o(N5zd#_!!yxPdt:!=8bmN8l}
;LXT}T}T>,6*@@8^g[MYjJHNQ%P`r,*5B];s.Pw36,DfR2b,zx>Ui@tSmBbwc)3$FaQ0FJPgNQdL.
nFYC;pn*C9xZWT]/N-*@&]Oi@I"wRh3[27YC-vP/usIe=dwKZ>i[ENTE}=6Wjp|^I2f_.9Nd+OTP}./3b/41/1+qS;>Uh@Q0E6%b.e:CnIH22#!Z&xL
4eL-u$=vIW6Sdg
qAP/J[&x8@^-A%O,Q5eB)x3{lKCh
@];m5Ytu!uQt<[ta9j>O;N~S5<z8$F6RW]M%1
ZEe1
:(4cG0.9[D/Q-)-osOsvTiv$1"ZJ]~BTZ(8S?k"CCq$Gv<Uld+COh-pxe[?_"Z
usW36i4h>L(V>NSN?hVej+7A:PTt.g]#G1$pS8GmBA$:x5kV#k3_BSe!q1H<k:KE<Psk;#a6vbZ8sL58GxY7?O,(QTeY9Cg%lvHU
D8Eh0.Q0>$==hTLax;?f&{eATj;|$gxOw@PI-U2(_?xBDV5AEt8ZTW[IQBDc]xkRx"9F*!3,sL*g5$2>au<oB!gH1
/Y9i4O`]5
igIgv$ep@R!:"=22HQ7Z&/%B&.h7@TZvVm@Lt*MZap0Q(C=`f&UW!wwT#q/J`8wOJRl.`QuOQ;Z7@TW?r3l$M-("EmIsP0puPzvi`k92Q;q/B!eN7<`nZ/)gJO:Ns^e(%g%9<*rH"to}Tl.yaqllAvU>y$LJ=A2xP5ku57
~pC;0Y|-7J|bBp}`vj[NM1E63@s-lKVK7`y/tVQy>UiQr]6wVqy*+98H7r2&yC]f#BkJ@V,RL2W0*(
cv#ydiu%pmji#!E+s9,9Schwy9&dpV
%&2(EAHxnqI:bR?B1EyVU!,W`.GFpaca6?K-ypNaw^0QXs@j(O/>5;u1
ts/nP7$fu)QLq!/ceX2&d9A_1W_-W=AM&@
^G8
``WJk/&*TuZEVD8DQ/RNou+:]LK*q]Ue+xqAbs*w
`kjyUi?~#%6[U#)$-.l,iHC2Dpodkl.%Nc:UXx/p)}RmeXg4?!<rF+EfPVb;1^6<MJqz&3ct&C<cLX$PsXI%>9S-$XXYO?R
;Z<os]&:G/r6Mv]Qd>#=+kX;9qCTm+Pxw=[v+7>5Q2Q9ORb#UWwLYdy"Efb$8cRpxGp<>r;U6=V2<16dBe8</~36@5@|%+%p+gRDf>5]6r#)c-$r,u>`3uR<NV(^-xq5lAO*I#uJ4hF^!uvw52G2SE?Oxz"2mPWaeBwJ2:>0eAmK-MS[WIi]*?xZ!
;r_pg(I7(M7{AD1:;pcEw8Ly;-l8*vKS/uD:+dR]1H"G"H4z>V?z+K8BJ}v_#wd88*]L&/RJIUx[:rV]s%f75<r:(4N
SK
g8U*"!/&n59K,$MScm~
T6$!ARj:h]EFPpGsDaK2agCk]I)$H]SG6Pu,Ni}HBVY2lT9mC#E$dOEP<5V6Hi+.*MP#(ojXubwpWcGaW&beK*Bdx`nd6U);O,f]_T(DcM9XjOCIg#G?WJd8sL+dR=GQ=uF_-00IdVVC5ycSa[bp
LG[QeD7##a([apl<r^vzmL.M=z.!vJ]*Xl=2_,)_VLEI*?,/gM%>gteYlLuUyV-"qWOQ!61Si
U.R{VB:gm:c|dO$R0ycED>sisqy8*>tkn9f;s1h7`z(lw+1>g;gop*u8tuF{F}TSnHN-/@
WN@!*F~a8fsOF9=`$/HJ/HO9SO61zo0>}Y!61t
--%|O!XF(<N^Is8Iy$!TcE!S/?*4!)R*GfRii0;W!cYDZ#vkm71d9qF2utewXb+G
@C(;~>P%Q+,EAxM;L<)+nUDIa0XW<MbaGG8%Z7=l)Xt]F2lyGU$v6/I0$WZ<Q6Axw9Pb*jx`XdsbR#5jqhiNE5Nw3_!;H
8(z,mUVrn&YpQV;a~HV5C-qU
K5.F65Kx-I6s,1_kLa[voTB:o7pSg:8jIL*CJqJ%YKV%tL6dU+P+nom,e#r/3MT?.t2UVG<K%PmUK[wn-Y$h8KvD>@orW*Yk-6ceFZ!?<0f|<3b5D&m}MrGp';break;case'ms':$d='%R]ALf|+MtR0q
b["ATK`=Aa}U}gup]G|Q
[S/{umAs$9w-k>x*n+1YG[m>`GvfM^2=6`"T6ZwZ.eDT5[V~Pl8@"1n%:
J)B%p4d}[VRa[#r@<&b{2$`U^TcL[UpQ#K6si5?T8zj!WpAxAq:NG/"k4*n3A3FpIj9XuR5e
I?l-phBb][6BjXu)dJPJWLUEY>&oBbu1R54PJU:*t;ngjs[kxufnBk-l;.om@n8bGknt4uc^L
z>jofyfXA
ymPXOtOrom:=<rLXQ]%sVkJrpbX^,a(
_?,bX84nh]@4wn]b-M[qz4Ut^Tc=G@nr{`EajkzUN<P2#7[]`@QFI@^eSXfOoP!B&rcwZ1<7~)*<L]l=nyXW
[0nfhgS|jBcG#+y8_&2j0&t)/=haFxR:G^fZUv:Wm0C[7-+DL)`aYdD"<hh0]Ft,3UdH;+l*b2Gs$-bC3"?dcj^l9]AVlb1E3-W}(BaIZ}v"k.H)Ml0)B<fS]5X+=tRe>*xt0cor3Z>{QDb!H.S
ik_
R]j]lV]V$6L591_%*])M?`ET.CpJmpS7r8fv`j2c^{X/,%d-(H]g(9O/fvX~>MES3h>Q2l,,ptjK%@&IE&bF?k1s
njygFG&<_sp4O+?-m`vFte|;(
umY6F=zlAf
[LWC
gSjk??d8mWD?!
,Nsb7(^np.%bykx?F`[R-@5cZM5kvtisAdfw$ljUKK2u[F;Ii/?s{9d!rN_em&YY}g;kb`!wz+@V7
}B.c:,/<K)"Lkc+4rWJ3"+~6vOBoAbw!3o"K*6ix,//_DQ]A
:7g4sc7P:}>X#D;Z)|
g(RXOm^h`TCt#T+
A
lqMUsyT>/x6WrHP:PplgjsvsZZW!<%cnpi=c2
F+Y%a%LE
N.Xy/
CQrkX2q*.b_n_tqiovtJ)yk;c2^k@r%9-QOHe[&VO/fdGEYii+Jsn8D)1kKadWdPEYGNp[@CNTr8:;C9e-XWEH/VK%@z/@1*93w<VvCN1);zwowm>~?^Cpy(d%P9.}B(Fu@LL+9z=1k#GD.]6:3qh@CEb5`)*YXQ@rki3VE0WI7s3=m}[j+15"7}qsGZ`^?%D)0iJ]G4H&L8u4
!NYGYgU]Kc&!9,kHl4L
=QTL$Ub:[9h*+&j-g^NV$c=oL_{Q-A,DLVtM<kdSP,*j&!xEKHX:@vsVxbGF%_]nBUgD=o77-3xJ)VRczR.1jEF*r0j@@IeEIdh
+?SOt+}NS#s,@bjH"ajWd@;.^9~k&-OJGx~`Tp3)<6KdPCSvxH}QghG?+ow)s;
7;lm$P6M
o$B(q(eDz!gSq%#;HM/(K81P.(0F5)icc+E]>"Th*f{w1Drp~-Rnx_X2;x#bQV
(P%!yCxBQK13h!I]vHE&X#9x0jBU<Tx)TTRyZ./"t~rB6.hNserv3*94=ph]0?j=k0!
4_@J!$XdGY(&,j;Tr6++[5Hg?+#_pjWkf);[;E
Rly]Q]=IW3
h^H1"p$|JZgk`yq62
[:NSS>v*+o<k-lBx>wgX
0hihVC),X5N4aC[mbPtVB8A"K;:GNU3jmZ2n`oz^I+8B@##$pS?Qe<|So4SNK<2<VdK%?X=Z)T"[-[%aF@Pa?VZI!Jeqt$?Lap7GF?B^j&5d+V!/)r"#8Am7;eK"MN,)e2t_e%6rUp2O4M>iLAuHK.,J/fRi>pPvF.nlWOI8(]?):r<R>mX$l1`dp"~P:C;@0p=a?iY(-qrInz)x+eL2P/L>7[s08E:e&/eoSgFj]*>o<knHE*_4-#Lsu.#Lg]iJ[ZPDO^2*PG/T7J)wuRdh.=CPkG3%#ZvkX?eO1ea7((T-!
N@*WZ]9IbU*F@&dIxCk3j&[q&8@%B"b:-Kj/q_B0l(=q>
4EG@|A_NGkv%/%ltd0m[QMV(x`jGPFCN-8+d#^>kmP1>J(*#5
<kZE.59kk"mNf-C=Vb[Z4x}UTs"Y66wJuu+]-^Ktj(`fxZ6$Z@oCM>#>ZeSRc(yRvCgw|/9V*K!&u,bC3[gp7")JyB*@>a*^1_|VAtbpQK[2J
Z&7EXf4b#T}/~c@r1h!vp-OLGTG9(]
J$4#"Kxc;72wB_N/.x(q622|!8enSgPpK=ZBS.Q.-ilYkoh.1xDdT,)M[yG#P|6[pf"zq@wdK!Q4;->{"~
0BV3ze2j&WFeqjOE>xN5!oa.R
$/S6yM]`&mdtXf.]PG-_$V]3suRVM7a^(txN~]SOwpc1wRC6#D}S>g#i?mG@{%@_a:VF/04p^W5FaD^(a507UTP>YH6!gfJcKXC7XTR*Yi"K;;XE<:xK3Eo_r+zhsD<)cHB
uUP?Fhnwo$D`ySoDs"}5)rc1^TL-SHt0l>^=?0{YWnXsdtDmGM}5rs}o|cXiu9txJvL8"_9KEM<j3h2u7jT6Hy5Z~j8`Crv=ew(R1[i-c"0%|`l#t0lZF
8T=hDvrqRrFDTbY*+,<*F%P7p.sTV*+h1
MGODP"zHR@=&wasRTn>nj0,M{jP@6o~o3DLU/eY#o8$j4A~!2Iv[Sa#PDwx]wO;2(EGOT,a,QnyL+C!yB@nP1>Ar^s{O0DDilIgrVdHBX?nPvk=<$uqvyi[[o[ed9<8>r1U$C<A6%>HX/h%[j3+SAR>+a%U"#Z/4@nio`tcW=0~uSXg>rc6u`Kt29?d;CNxNQxn#^:r"tNNYT/-t~
!IysyTXV[&vV)GL/11.t[5YN;+8-G>y@O;}*.YPM?xrM,1{i!P>?~M@;I@6N`Y[mG2&f$D,QQ7i+ReslMe-h,2MZwlHpt#2:)6^/$-4U{A|>kN2GY2|Y
&b%-F/=*%#Q*Sb!qMLxYF-VrbU5ch<6e%d$!-$f?P+2wpeC!pmP[BG-XPx>"!SeE)Rc%Hw/B7K33I{x7:[]aKN(=$Y<YMP$`IV#{dk*`,_B]PwD03V()RVJ@_WiY!h$tDp3n`myK=4"&uS.YQ<`p7T=UoaVJHzI#j|;PNC%pnKtdgyUtfo9oNQs[ukw*E`f~@)XPj*Td5(XW[THvVgrz+-lGu2+e]CDDYTs)Nh39KS[>G2uQ8w3$)74#[S=jar7wXKaVgm)awY)oLNq~T436b{wEdPTqMz.Pi!(@#C(~jtlBY2cXWIPuNdfmD6Df,=$.9)j<f{LUp.Fk>%?9p~N/5`_>T!-{)l>#XsPuZcuab7G
=UwY6%>V&@sYjlNg-yUDH$0[!EKdyR1+2V6/3o<G95EePDcRBbgfmY8MLS5[[r64s|x4/t6YuZYFi5AvyB%BBcGo!JT,oDaCRKMH$|&u$__$!JN?"_f*ZZ&T,
BCqon@/*=Yr:O#^z=dD&sNLi,<"|i*9>YqFU]5`iX8Ko;yeC[!?u2}#U`"bry~OsHR=6s7U$uS+WH7>A=v%{yGfj$ZGE_Y`Y%s`s_Z47e_5ywujX@?_]rm6n"eWS-DFIM@g/I~"P,|vf?d:Ji9vNl~WbuZw]h"HUorW}v]a[:&d@%JUc((jR;>8$##C62b6eQC:f
#CHv9UtM8Zhnu4I1`q~O(@RK4_9N$3h.A^Z,cwvp+83x+^6+7Aeufgh)f#Fo4PZ?<]sb@M!6{^ws^Ld>C;Q"F@FaA[0Yw;uaXLd(v
]gLr[N(v"g&`^".+xe3:}P[S&7Dth2QhC$lddtGgo1Npo;$R*u~bdp%IVoG:U)`R%SNh;X+I7fT"Z,hK
!M1QbWP`WJof56X4xA$Ir#$hrd^KeT]v]+0xBa;MPhv_>[YYf*?;l#c+c/SWf9n3W)(UaG$]P<)Y(!tcPpd2>7(a"90Ux,*fb@)X5d-&K
_Z1Z6V%uTsgY85sIljJHE.oQ#tl7Ub*?^mB|.LQ@"j[s[$Gdd!`;+
KoQ,!3EaiHWQfvIc$*P&Aa=NOS/KyRg9w:LOMD2hkk!SQm[R1!)7g{5!Tl]ii
5&I!2m)/7~s_=wEVI]#H;eac
3"u+s+fLW]4S"vT2<E]1/jBZ*oljq!GR|p0N&bKU/))[:N;kZv!w]9O?up8&Qq%#u,zGf+d9^/9tcU#PZ.>8/-ulZI/#>LUO5@x>-XPK$3]
64ni0,hL8YsK04Aa$cbn$Dn23I<8+)r/JW/)I+dbNs$5H.%6^$LZSLD!v^sRfuHkT!9pS#9@ZN.aAqcA$n2j|-er-
^#OQ;cN"M80Y#[44=Mmi5Jj-39OUzuY%5QZ<`RSrrQPUmit
%kL/44j7qbfgM&C"y1)TrCy!h6U"?7py{Y^]y3u:jU?%Xd[PWcL-TCvWK690n>|>|urmxO!&=DB9Q1bLcjH4+2@TBag$(!}.)5TnsC$d0';break;case'nl':$d='&ZuAM6LD),{0}N:!d9R8[/EOqr-0asE/4jE@*;MZQ]s*XhyrU_=w42v&^<A^O%3M|%*r8wS0fLTuQi/8684)rc/ro?Dhy5tLGTnDeWhUD?Ob,rz@QV*cv)an1Db_s)!clELw}%XL*7.;Y_!`2KhO85)Sl?XROH8I"]`T?Z#?@?@
*T4n#p%`Twm]eFbCSavg"hcr`Sr.zge
yIT*h,e5(?Q^$<ha()D>IM=Q$l!$"Fwd0_CHEA:KA#Yf#[$6OT"@;l%<uQ9J:t>vOiQZ)f[;l])SHw~m]cWNcx#,cSFrPV&7=vR1eqegAy&,l9qrzcMyP,[xu&do(tHI6YX1IS}W=8r&U4lwlvY0ovbQs:MWx>R_2Q|B%%x:$[iscS":}0wh09j[Lex3khb(:RAua.1xAf6op*lo*qNbH[kIWGgB@_X;<_kg`<<,Ntq>8Ie_%TRnr-N5=R0f|VJB.4ode)i%hcoZf_Q1jT)BTE@ak>o#!hra&G?^ko!-5+gM7
3#nF`iKRGTtC;,IX:JtVGE<Fhp-I.4.sw924:0hk+2j^+6#m5$NM3Op[9({%c@/Ea#xh#]9H(AgkW$#gtswqTM{XQ?<Pi5LRij!G]5((,=1g~G|0_OoMB"F2,Sc(b8XqvOQEJ>Oj6Ot5PA<L{6QX2SE%&M1g{Lt)eD_f
<QebGuiekerz9%l]m5/G3/7HK=1@Gl2>
Zt*5*EX,5c/Qv66L/!!kMz"d_On_l@1V
(/N%Xo<KTGirI&F^GLV!IY6EQD3Ki<Cyu[!.79y].Rv=E9vrx|!7.,au4s91^)C[JTJ[sW5yx9K@iOLyh@i<d"45RH)FWAwMw7&g7{ZcK3y9
"6:T?ECXAJ/O4&"z)YqlhFY+ND]7.iHIeF#Vsw3hA?MW+,Yn)Mn28cDa>?EpIYm6=]6hYO6i
/Doau/Zq2Qi#j^<dB&#<=%rd:zt$3ycRGS](A6/"-9w6nnaSW]v=7NGu?dm:)3ePc&n>,!CxSb_r*h?>hrA(26ik*S&->pb<Gbv}]NId(2HE>*xi*7B![Q0vf0w0!C/FoLU2s~sI`P@BQ>la`%XK+AA^hm!};^VEn,&M2emmVEat/N3ILUA9`6KvEFO{sMbK3FU*$K%"g7fDe}Ss
UTQW&E@k]VoF2y3x!kk"ii[Nx+f_+p/2{$#`$1c84X}%`S`:)p0M`3FFfxpuzsSw~,"uF)Ij&b4LNwstVX;c*CKJ<RayyYrkb7&LQ*hNK#Rira:Gw11`iDyEb3`Z;`=wZ.<-RqL
4PHa0`pm>UeYp3|Sm</Zc7uk@=XOMR{33W3O=vyZf5jF%5E%16"JW+!GVWWW#SOMh)/j}5*!1rB;c
y4v"yY.b%n?ID,S>A3YE]%KBKX[qj=G)sH&NcG9]QAY>b>Wu
KirC<ff6bX[O.|wWFyv[n1h%IXV-VgK
Ut
|x.ff57`gbW?/tzOs5$mRp^E
gp<te{Crod6"d
V>28.SN:la0<HX[aNC5
&/Q(17Yv"4Tswm<byb,|l|Thii2fW)^;()^/l<u#KPl5CC?Q0D;xv:$[F#+n^J0:;6D,H2vp_j5E4mAESp3XV$S`/:6O156Q_Rd.upJljU>ZA,9gqe?+/$kh_
o1_^i;:vS0S~^`yG,RDJ2e@{VC5
H0(uY07UQ*je&,wqy"I"
E:uqo%<xOm::nb@((uTMt>:-3[Y.a"+]dZ_r(_{HtFBhQZ:;TL(,X=$6Ji$5=T<lcXOx2w{g}EQj7!W[}!08!<h&~R?Du6D)^?+vzd[Ub][g^x^xP4*HNl;z%6U=
s5WQ$LuK<G6Z8FT^v5mpb%xb@0TkJ?a%@~!ldzE):6[XSNgf-i:<mO<K>Iv=&|/I+^AJX-b8<
Tyu>lCcBbhqZAm/69D[.Y6qnuX"ua
3_<5L6vx[?cuDG>tKI@eAoO]cwPnVn?Y,r!s8QCINb]p?u+U".%KRveFIwv2rsK&E%oK^6fnuRb@m#Pp-OsT
P*$m~k0y.xi;
vbFr1r.$OJPnN?FY^8P@wK"F5GKDEk!&nDZuM0<@o.!n4ZF%7OdrRc`WGhO(]3E1>`"?U>XsbMb-YQ*bJ:KBU[E#sF9=Ty:,n|f1Nv3u>]g`XT:4lS9S"+o>
JKYSz,(B4#1)B^,*VtI*cgRi!B.Rgt_N0<ATmwf/QU5FWv2EH)_J!:>!1C,4w%pwGCH3+0sT&*j=INcY$0c!Df5kp>mSb4px85%>1E?xh7#Ty,5j`10hq(4l1`;t&<}(0>D11:/x8IUtJqYN9V_I(DM@zZay-5aZ#X8ic#D?WZCD]k?X
$th`v&
IX5?u%c3Mgpn=Cs#:?<2cUX3m#xiQX
@1]CD!>xm.g[!QAb7HS630U3`17W6mnz8))&!@Ga*W!#G$WNrJkPU17?4V]PamYt"1+h#[*)
,8iM{%q!DVI6eVnh>]Z[1e2a?t&Us)-vY/s:eRw/6xojh]>]0n=g*jA`y^Q58x_if]-@^cf(3(+b0KgrYM1nmpj,mltB@pZ-(8/.Rbm:2d}[,-n*u&/(w.yYQe8I3Vi4nk`=HqRlHtjF
V5>a#Jbf*{+:9K
M.A3_LTv:nuWi-G&+mYt{(|Pc"K1ett>jlDWp$f6=S])u
GZXD41P)IvW5G)^%e+tqUQkIXGRA.Q?:,<-]{^/HZB~xf]y?h_T]!,R>Op!T|DsjL;+jBO=<vx;$8I@(<m}6Fs-%M#73%+I:0yo!qgw=,v^y2!7?*)Gtl>d47ws(U!d2,jq;V0CuSOs
2,4#f(J5CN}5>oNIu17`H#[S3$KsYJA$ka!!9nNR2VK.2EHi(!<rTF&Q2eh!REvf5`<%2gZaAv/ib?d5pr`n&i!2hRxtWlv_VI4o2Y,.*rC0,I7$%hh"xTRbXF:ia2LKLI5a`/j4wGoW{76NWb#I^"QYmwFe_H6D;i.3(Vb
>6<*PZM6o`$g_JR9)5<pl/w)=U/Nk[qx3/zgd<*A7rz^WnC3[d8[HY=1Cm/XBlG]S!@"5pziPbVJVW=xG5LS&?;aV.f+6psP$5)",cy3MjK?x7bCumz<z(,R
U(":/oaorA,ClIl6h?
quP3>9ghn*6`wMPTKM^hS5L?vecr/Ltijvp8UPNw+
YapY7&@Ba%Fb^G#!t"p]"hL!uvS[!!iFH%x-~Z5+_reX[h~P0NY4u"+M1R<Y{Y_REp!I?>BoJ1)=7/$g{rk!)iPt@`:UoV28=I@vmF.h=1+e5Uk#A_`N!`E,aA7bZN*0e%UI{S4.I[6eKDV(!hCH]Oq7.a}[N*?hc0o
N%]pSC,#%EY6~b}NeKaTx<IPgJcYlPj43&g8ow/
|5=1Po
&yp/Z>6>_$;+VF8fC@x})RMT+[
d`1VCKzC1+E]nk4GP38js,Ad)1ANq7&d8Oe.S0P%hG"rd6,+IpM;!>{Uu9f+t0S=b0tpVqueu%4ws^mB_&^U)h[H7T:%{^X_k2r;eAlSHH)!BS3M=YQ
<PQ)
aWbvYVf>:as3/rQd2M^UV~tiV1K_aJX^(3hEO%"JRPEp5uaWK4Op.e"2WoD@e6.Xg=wjHc;=/J;?rMK>*-!<PkiIY38S_XW"SF&2&Mao
NM?_8/_V-g0m[H<QM8<o-+Rq^@D[W7x.sx-KA`X72D%V0u=)HE%MtqZWY$+U2Iz[Z^GYO&^N)p+LMmRa1cAMo&Y+Ua3)WT)mdnk2
NYI>xN9d,pu(m$dd(t;.pjGTa"]6ODC(+xM
nW)Zg+Q)x0!{+"#z
k%nrRVg3ux{8gOkH>FoYz<Qy$#dpTpT&CbJ3;rR:T6)6MY!B0fEga.>5N#"HAPeIav>c}Hj48F;>$JTH-3{3(da1
3)A,X</H<?82S1TUt3!2fz)!3Xc2?zYc#]s^#qFn([yfitMMK]w5TcsulT8:MO&qS.e#,E+2Xh!)b+iZ96Y.C/-}!/W=$V4;,Hi}2TU9cRTviePc=p4;I2k$8T*S:qY)[%7,@h$%&rbF40uWu!],7x#l<Y>7)n@#HDi}d:0Jp}=nThSZQFwO+l"&:xhK9Pb9)EI}^$eB"1FYI.*JM8O7A(DXQjPhWb;lh:Hm*k*YLXCbQuN=0OLcCNBgPNrUL<Vzh/io^+@Ma@Y*5ZUWc^,~jaE$U)Mtn8+?nLkB]f%BN;$eiS
^6S%);=5*t+fmF73:y8%2ldP!qyrZb!
KutlH1o/$k<t#7QXemw0o3?,0i3!YS
O1Bs>CGKQ.xge@3Cb)5q9]<Z10h7(*D3W!V9J:)3lS-AY}D8?VFcWs7,bVebF%n$hbaUQhS
[qAnI|.q!fLt[qK@L5
r$ibE-e^[5~N=atZDFA
T`w?KA,%kStW0&mrhl[Q,,nj,"`D97R(;*]x}3gY0l=y7#}4Qy#,q)y@`,@M4cpbxOpZSc@tnIDE(r_kJb7v{M-_~SIRG1yv7K2@3JT2Pj]]24+yN.h@0%2HhKfbOuy:B*d&ac.ddQyc7*>-o&?86A5M]GG7mB#y`R-Z1j`PREpxZWu?{>f.&;}5<W@:%AQ>.bFA~#SXt$r+A3lN3s3[j(L$muWU5XR)J$28!43QH&ucJ;
Jvjr$0V5pn<jZ_UiPlH=I"g9=Oc)gMXApb>)(zxpVGv.HW2eJCWd1uN>X7xa5+';break;case'no':$d='%ZuALbPDI,z0
Y+"&T5OMpw_42huFJbA>?o1BXdwa6IlF?(.VO-hP2bOR&MSXnKyHtW>Ct|vGfC.yDToesFE@K+vhrgB4r]@;p8!$eAH_"?]pl8+HW6[#42d>VCQEafJcdb/mY,t9Y]gJGFV)ZakR0T(&+[S{a`kF<S6159m]G4?(&l%1
MKgVj%wbF,jt1)d?a1f^};vQT-BhsbAQ|?p0q0gmRqE!BgZl+7h5e72_g_e>L=hyGh}](EKxQ+@4};TdIr7dYqN0j]thnGd
Cs)v|c4]~V#gI;C1Xc[TQ?Sb0_8T&_XQN$fb%NUqPr_7IRn<@TT2+)o60*a`wF|GD72/yq7=3RS.MWF4*x*t5o+F`*?RGdt]{
jY/.qsf_7s.gWit$~<z4.0nF/%
MP661nZ?gQ1]t5@Z^
6NrU.SRP3c^tXcyTq2S[X]ospef;N(K
_
:i$P-zs+;OlG:!01#?U_o68Z[ORxZ194hye%Qt4I({?ZbwQ]gonY:ux7#%(&EkN}TV:X(_sKZJm:.O){RH<:"}y*6;khH>-?LVp]b54(xx
1`_N,gQ=yO)?#-aFs#&Ug]uC(5
vq<DSU&Upzlw4=gDG^g/@!.h6gPxJ1koWkT1,0,1I="%R>l.A8.%D<c+!o_EU0:wK=-p,BXY<c4K4k_rbvGXqXFM](L]h#0Lg
7+fy!iL+i`fX1WdX9xOPkyQ)_OVt"I*K*S-jyX(yGY/+9drgHtvbx
09#npa1UUOPNlxs"V1/!NF9,xmE!?tFNAq4@Gxv$R)QDYOT`VMtEi%1%cG>!<k+My$BMh210:3k@:,uhHnQpDGb$$Vp+>0GlC#aDXPNexC@$O
MF>D!NqJtvuN+,G)m4T5%rtgrSTv_$j"Ah?ElCp0mMWEDudXV^1vWOPto-JEiD_BkxXTPd/+9H[HA8O`aKI8Sp(|1*<p(r,kHnobYyuVpi:$!pYEc8Gd7_Xl[N/JMV/;T=W1+3hae
@L5>")cdoHg`dc@vb:30.5C4asnVD]"d4x=;;a7<CLU3SBYiNVWwOt^r=K6Z][[!*HXiI4*/rnMmnH7I&T9]?dt
!.w>a{.A4:i%A*M2_o)&A[kqF?3<yRlj1j1It.*<`GpO+Dy9n
R=4xLVUX$H$sflJPI=_bP8rJux,wy@:gc6vy=ZA>Q6JyN+fa[yC
q&XT9Tnx5U)EbNq[JzrE,JAQHWXtLTBlx?@Y<g>;(C+2UH`RUGimqK^<vgJ@-_d0TC`uQg)tM.J+H4t[lqu/:pG~0,l`[Kg1]ZC{Qq]^EH:B]%)Cf9ZrOOT=%5LOd&t6aZL|"+[{_5wHn/j
(xd]P{Ofbtud:/2LywAhtOAo$}y&:%oaMq1xpQ-H:|BUV6pvoSf
;2ekbUvE^|;3h&T&)j<c?GoJ$1vDI{yI>/4z?lL-/:gTj2OlyZbWn`G:!usz"hvCNbJs.u_o]C"m`M&.p!-*B+.u)!%>f
B/U#Uy
]-A8>+wisig#LSI<G"DLx.7B_r&@>Rk-~;Yj=<q_NH$&zz$$Ia(#-T?k]#fCjbk)!R@%kyrx9&;3s!qh<e/A$Ch_P8Xj|Ce<PT".)*+pc8q1/U1fORZiaWsP@Z=F^H(]!1&LBM!8z0@kPK7NoDnKaSYQFx42cZo3./MRKp$P><9Bf2A"tl};&adu?CR0y%P9shfr,&o89HM$o9Z?UI/.VTeHVQ_iz(`FA%0I
f(Fj/!
^s,jE@&!ZqliNz$WcZdSv0s2zs3TlB)b@"ShTv|Q6##Ap<OC6QKISUsl4]k)%*2uP<%XYkr>DW-^^(A`~FWfI-!&T6cJ@8~KL,6SC9e7(jAi?Qfvf2
9gdB:L1@xkWO0_r:$xtA,R`l,x?!8tIg0{hYbkK%GTPuZql}2:OR9`-_`.[OYO
|NVq"`=Cq(H0&FeD~IZ^.YG&xNE.X]7b00(+xr"f#I;^VlQfYAD-RPe@k
]LN9V[MBxP
<<Xll:V>G!no&(j$!lC1ksPVj^t=k3m]$|9F%bdQZ*eU]+aT-M
SKGKt0M,j7kd|vG9do*NJ0{B*AhQ(e
?.m#lS&eWDVh%,%+%@]E2jg<LT<-2(C-_,?~<cI&%OI#em-0_i;`:z9,"L$Q(mk*bo@VWVHxj")Q9{kvH]HN]>DkVdo$HKDH<b*%)s+4%m4PkTd#d;.G;LNjetMz#8e89w
%:@?cdV)zkv;c*>J"Z)[4"M1TCIQN0<#>ae8<67.=CUFqc&u,NIyQ39vq"?n5xS^wRi<^7~_jNaLEP
fq5PS=25B7`z&Y3[Ppdgx[s1;+I3?]]M`6]HGz.AGKC|[%46gSBW`)TP:o>OZ*@r=%;J7!Iz@|eJrYdk;@pW)
u=0E?uW@qDx"4sE]kqd#jf7xX$D{/"35);<]b+6Oa(7U!F^juyKTtQu5G66O)P!Yk5xnM{$FmMEI$]Bk)9)=YZb;"=`mt8k]BGr_N|h<xi#t!pGtX{*pWv9e.zK]xE__W#$4[tc~.VA7V~?8t/YjF1t?k$$fcLng?:s([3-1+I>m/U?z"ZYa(M`-8I8O6tq`0ZDI?J;3[G3R+qCbhZhv<#
wp:Pm79:wOPaFN+Z-Jv9lA`Ib8E_p#r@]h$3+0sg!U%d`J:^!!;&9-X-%"sNI8kfziYq_6t-+`XITM80$->cRDedyV<@9QPjRm8RN.vH"+B;tFs<WkCF7h_-0;j!X?Z^WbHD:d^bx"`N~1}WUKl)G@zjoN[IC9U"H&dbs
tnD4XH
qT?}JaC4*LQoZo@
E(9hZMY~<{*c[e?]IGS:B]_<o
Kluk=`qDNqL%1&1O
^PN0j`R72fG0lGsaqu8UL?RC.1rxLc=W=C;]Mo?-{%qyb
_YiJ-7DBR+"
!_<U~&L!]GdQ^*8@-0OvduW%]S#/(F%GjHV$-Qfm_A%cphVBTtds>v^]gfMn@o<(nVWn<DrG_&Vt$.u1o*n(EezB]G2SW^[T@kWLiFx"XdNimj]x,@u#yY,,ajr&NU9PF#
qix)5{jrwFkiaWcKK`s9Ju1Nj4O2kElNka3%U[c1lzs<)!XB<6b?98>qM(DZ5l&]tl6!3.h
D([^R-9I`P@M;n2Xk%s~u]&
66)b;/%pMixA0<[w1MR]$^8/HVpsEk-4eQ0NZp8jm|,V,W%A&O1aXh
1m/]e*njy,}a-]0@_k^kU[OM,+fDBxH]{4CdZ1[/@9.OZd}bXIaIm<08~t|&+i(^IQj"?t?LRDzpz1}20A_p,pZx@AB?f%#^bFpr"Pw$(el9{qi9PPiZRYpL+)6BpxM3rG65oKxYu3j_S,y@^*ApqA0KsFh0AeIPY-,p`YIS`hOQbP/-F9%@6K<4!r"&e=usic$u$

(O*s8Vo^HM$O9THcq6^{R*s&sWVKwD"T52/HfhK2[,25C9.w^w9-=m#F*=htpKo!w;w8j9S|_`O9,w96#T(2a86kBJyy/>&v]D#&:1"<"*I|N8<d2K(upUWpOXy4kc9XoWCaf`]!k?$u7q3wM8:~Ea^kq
gkr}4h+cww)ITfpqc%r6]d2j/R^3Ip(*E6qbXkk6N$9_c*HyTfurI;Lai*vN8LWfw!K7wTj6,(YGdN
X8jR:+bSw/.W;OFJ)8>uji#!^H?<mZ>jnrX/V`0.73s58kA0MQIYnt5p!GS.(j2c?%H/aPu53aC(5VEEEs7>j6;_Dn$?-E
cj;v*]XjPH@m,>kx_fE}=BZeC.S(6UamZaHr!XM7!(p0i*ZM?VR_:{v)TeyI<)81x(NVLDPOoq@BuNeS.M^.vO1S:IjWC6yBQeh/`MvN6j&_
6ve,)MhB(m/yB!.9,-{7TXG$z=4sS*<LPc,Qp9
+DQpO?Hxj=Vur5fmIDsHxEGTfa-jfa>9x>-?^QVJ+_L}K$5WRA:SXTo9LgBMfd8qLwrUEa1R9tn6HukvJK]yAjLY%MVc.DU2&|K/B)Rv8](&@c
B1[oziD<W"yyWgu#AA2BDh[^lVdrd:tDy;J3r#aKS7+e=XTK7;B3OLw4_*)we0,71hvpPxSder%yOowr!51g0#;<3!CsTh%.*
iL2NGfXjs17*brGA+OloYT]R,Sx>TC~uoZu7_5hw/_3^)h0TW]a*i+ANu+-yEmMn>f3gP0n,,A:c/"=9IbJW<1i[fW(JBGbEH@d?KmP2f1b`qI,Ni@|1$Lkicrk(M/[R/bun7h)8GVQ)rfXf%f2Emo]y`k!uh3Iy=C}ATudr5Rvq]iT8_?l-B5k0wh(qd$y1mp<nH`JaEu"?[:)LcG2L=PsJV1_]&E$_]@GbPwCon,.$<.C?F74B-aO`C(o2ZJX07/%lT
oRUNq^!*u+4G;ObZFIy>A,&bh]e9*CpYugea$gzZ%XaWRi>P2#{OgtpPk_%71.6-<g@e13fv~*L:I*`u`GwGtb17
3/>Jh+ETW4*Twn)J@6z#lT[E_!q5jJq^x=#-TMB2N%e#';break;case'pl':$d='+]^AM6LD),z0
^V!ZY|S0."OqoCV*Q%ykR`d<$1MP#,lgKhOA"GpO-3-.0-dCeRZh,~%WIUc#J)pUFWkrhmGQ*}=&hOCSv`rgB8ra4wt;WWqnum=A9SZBoOgpo"f8]zsv0FRzH*5(w|c3B]6"(ytcGlUVl#$&%a;/`W84ulr>d/
Z3/bSc<=.Q/,<T]c|DoAQZ=]`)VQ`5/QM&TO2[B@?MqT=B]f|
k[SGJD^y]i-mGWkOq6~,bMzXC%W9er~^S?8pdc;t2>9y#ZWG_s1
3JiR`ul7KaXy^>E^Ss%B$haw?x_y4s_
Yd~bnv
90Tm]2M(0evJTq^O2VM.=GV.Tryf>Iq;lII%8)nz2f^uXaPc=u<E:N-W3AMz>X7|U}pbYS4~14r@E_fqFO-dD2pv[(ev@P)Vm:`yjhHCS@*@7J?&s0cv[=66^Qpe)EwQ7Xn!M5h0/^yT%.THxK)FU.gmu[H#oAEt(lW
L:J2G3ET;c(*i4bwo2JP6v%;wHlw]9,s54XfH*]75i3d#;MVX;L)f>%`J2Kqsy]uAlUTM:G;JsmfPzNaiMOaBFoc`kuKl]Yn@F
f8aaR0pe}g$8l4WGz]6UekP?1g~!hrOU!gl0fgO^bo]l"jgG.)r1D`z0QB?"`jHLB0~Y{T[)9#<`;AaK52mU?H`vx]fJ!Q`/!s1a{z##`b;>Rk3n@i?j|HxD:@b;S:8x:5x5wy>J7TPP]mLe.erg@t{4n%KKn63r8e<
fT!t,wh@z$]3t7jrlO`G5?JgdvatkMF8YJ_e@hDI#&LMF@Q9Pr)4#vWhzq:&;Wev"Q[sPw1!nU~.+@NT
f{IN,-RdomK-RSVf%da";W/<B,8:.pD{NqrvxDF@yC0k){Z9<<Fr5JbF)CO/l
VZSEm%oQl?5LMbt
("jwxbkS7ajsI&iF3G(16<Lh=LAId[q:x[vPf-rN!gI,LsGT5RCfIRb:[lS12FGP<uJJwSDu9%2eeQl=$81yW4h5pk**_BZ[=>?{A84dWHJ%SF.WM
%Q9G0RnaE"*<h#0E=-`"(YkDRALV^Kgt<9l6@m;s<7xTGFVrR;b|5SF_oY<;T48gq3x{q(RZJbFj^<K1m&VD)47zwpHaPQ6y`m)+6h<|VlJpMEI9&dpf$[osAzb#Ul$6GJ4Q,4k:WN`
02M`3}"|fv>ry4:Ok"2$C0RzF[VvHq4&D-KF>g/3<]fv,SaRI`OJw6,E;oQM3lZp17J):zIn5lDH(>RC+[An_O@p!5:MRX^mE?Gi
?^,_mH[YQ/[lIo]u]t{nUwQsk"NU&YGBWh?+UcV
Z]{R:c}QuB2(|y?LQ+[EBa#K^w~qPi-RNa28XfvE
h7)e_^dCy&.Cx<>#=kl2XR,s.Gb5b{dcWFTe@z8K5*jre8D57NYeo=TM>`Z4xAid7I^<3eSBTw-hq2XO]NjCOSV>Bep?@K%,.q+ZEba,O@>&Rf[;96/pT>9kS/]K-+b)36;I3wAzhU0R8eXnhU6"@=B0>pCM&TNTi,6fdEJU?M<T.(+<`paTXY!^EC;/hBp6HpQp"`tgm6p|v5<R8<.B1ctDgC2>Kud,4xc2cYvFP_CbLV1kX=Qd*rSG0j,KdYHXrb$Q/KP(MQZMXD?NQy@XgN<Hb)<Af~Kd&E)1]4Awk:KuD,;LbZ5ND.8iWEkARyQ1)ChFHAxsFi;D<C)eD,rM2)"&1(p?WqZGEPNT=<w5]Fp.-LCXqtR&+/"PAyI9
mX,lobB+!Q~M42)ktGE)wLM9.0Ap".
VR&~_J@w[7+2pLjFIwn9&CF5$bYl]&4f@3e},"2ig9DR/cb?wYY}/lZlKuW2,U?LSG*b
u#y[(CF7W%Uu!oWf2gjUW<U]y?,=T#r(Jc+y:>(v.g:C%!"k!`X9h,/3!8r1D*;Vf]$>.pcT.ko%X!5rk2B7Hhx>&!b({xWw@xmoS$!3</}S@
+;^;d4?CI^CXk9a2n$zbS6W"7.wfWl3^"EC0=L"`NYH]P8Xxqc9&)
kSy
}e%<K5`Jpt("B[}ugW>sj0VRPEqcsL:e[#D.i`xt%k>A!#`p0=LF/XR@V?nhPnc--:!;}k"3]`Qn*]M
b%E.7Zpu)84s+C>"xVcSYR(E^[/i8@xL0PwY8F,Fe433.L&M9:Tl79f5`h:>~<=iAKkL
0L#ejGLhMX&;Z2eZ^`Wa1/4<[F#]A$0tlPuS@F[2YIP4JD3C9-Q5KOXjv}0e&iDS&mh#uQW6^Inu.PrqGA+LbzVD4_f)Y"(>tD2(<e[vQ)Z?M(:#m{l]QF_%c[l07)@#n
SiFHtHB/vPmK(1S)^@3U,8*]*^DInl$<PpIHi4d%$b9<<{FRgCvT
BC2aI[tAc0M%!*GgJ0S%2<XZM,$DjBB@2_0QXlq%AU;e=U&wZ8]V-6%0!-5
H
&1+Y((SHu#G@~N3z(xKMZy:h90jt)l^;MknM06T0cbh<ArS)y&dyKDUN^yQ>?*Lj-/#
VK$r!OnC^N(D"pi^Ar=m0>:]loF
=`)R8?5#Cx|G6I&B68^rB5{A*iluSPk.13Jj)&O#CyobZSbqUwIDn
b8V8kP<$GezN3d51gu_y$%(R_;KWqesNx?#
](dxa44B7ZoUGU5"<I3-stcdS-zf5W{3uRJhTOuZC6@+4EVjm<"C)#wi~%;pxR7/v-wR_y<gpnBV=,O-_;Z@$Fpf."O;ts=lY.YI/vT>G9^?qKl,G_qk44[mM0cht:nYZ=8)i*tL+>nNSftkw#VRX4iXuj)&1T[W"r8`i`8*aiGpd.5Nq*~U^;x
QtBFdk"X3$tBF,WWspd*"+b7fDZ3P=Kl4N/$8%o4sq1=]<`I&@6R38mH"]SsY`n9_M"S:5[?:6o"dS:QVQ)h*1x<E,mJ<GqUEC]xhE/2tuqW],(sBs^Ep&(.t"-TiR=<_`1gzcnO~T<TU=dwKU=Q1=|`34al[Kxg8!Bd(SqZb]w$a
sm#$h?;G_6]5a[rx*b"GRd*3}Kj/d<j(K#..16twhM>g+bk:?$m2V0.nF,y[rP@%hx+[d"1focHkf=6:5W|IxOT
jqU#?NNFIniG;Y>"$]{VJ-g?%t}<1@TIO+s_Bpk*1EQ/F%6oSJc,7#DO-2^K}GdY>HR:5xq/=35B[sIAenD"4SBj7"$f88TS-$Qi7wd(g(>$(OuZ|YM[g".JW
1"G`@B&7;!5ppq1&39&(Zn&.A3M-+xWjNaAS!3H3VM6+.=u^1A:#ySW,*]J5;$2LDTnN.KZ3ZbG4j*P"lH<Om#yY!WeOvW@D(D[CPPab9/MYuXE?(08(CVa"=.@"#>,&)3+Ol:j&(R$LOYc?8(&>^wWWX<+7~l>C@EcQR[ww6W*9fAT2B6P={NJv}c*Vmr|n{HSFC8>,>q9c%G?_U=>+;UmWigz75Ap^AV[72QYZ,>rO|=!2,B5KI3b%0P)"SDAa8L/<Hffytd/j+3!F~4fa::v%_a;qX:.A_5U@OPN
)W<%MF4jJsXZ6NFTuXvvA
R;DJtRt>>4<r#0Ffb!x2`"],jSW-vGFX,$uud-}^6!~6r!SJ?Zxg/Pm)?8>#"o1M(L{4pFn%u%OFz+Js$e:ZzK4pXH}*|Y}Ub8,EH_]+x-WP-Xh_YkhdoD|Fdq:55Lp!uk{CX.7EmY3P."lOn_}WInT#ylr+us5AH;K(shKIT;4wMO4m@S;Hut:I.xS$Alo%S*,V,4L<AJ."}Q(Z8YmQG?uv}2,JL6/)Z8_FE#NpmUw
;[Q-uR7M7`_#uB1M!S*(@oPLeo%5P7@^@awx@J.&$PJ8[@3l#BVt"e;p_K:7R0"&O*]jzeXhE.yi,yI2z4S4%cW-G3.6a:Wyh`[?wwlp8tXXov|EfeV-#NijzGF"OS>Y}d=G{ZzOy9
8rGKUvZgc?[2
p+1)8X|.bwEvA<y=_pp%b*o_a,m*Jgmu|W,>%.{^Nt.FZ4FlWlFs}P9vpVol&+(0|)kG>>_#atx.>5.stD*Wzc(#VyPnrkcf-8s3LU[Pwas$cL$bNd1#BeASbX-[&.M0,n>!#(f``LBrJam>CSJRHh`TYsb^`lNSv^b5S%K,+Vod[DZFMDr[%lrde&bdkTA;fki"|p|bB"s@Q"yaJQp3[#YHBA*RXE+iQ),#<6DqutaB~Z]>^8pC*G)DBZux*<},(L3&gsT[{ih
I+m#CR7k01we4i?pqEM/8=>F,%6y^3Cu60G6""3bg1q@/NOUTj&$z7Y+_Y4O;Py>v*Z/)+19?Z.Y8mWaK^
.i@`LYq=Il</wNrxG}QK(J]N=Qv2(54UAR$UO!C^ie1+BF8X>x3uXDDt5%Fb3{$95YJw7U:NQ~Gt`,F13DXuo[YAFZ$[gs$:xVpz,z7.l3lx)MfH!Hp&p/)51@_=4U#/O=SJri!hI0^Pc~4W9qoU$9iy:HvRQ(+(J}K,,=Vo(=6B2]EUL1Ac*#4Qt9^m$YHgFV#+S>A2C%"3F*
cV04:aO]g*m
k"DKQGn]QdSr.#0*J:TKoB^"j!BGdsxfwRv&BliM_t?<YDeRf
+4RW{V!sVyiN*AU:(=<U|
g</!q.jmw:93sd
Z=XoqjxU^#qS!#1Ic6K&ZOe8w^rh`:i~jn!D,Yg7cwky6n=jVCU%MkN?2.uF`1Gca+M59Np1YH,fW$CH;HI^"Qc;F2y
c+Mlp5jqH|k8]-.ZD"G#mkO3lvPzWb&p:)Z`.D!K^Kxe:2H!y&Dh6f/,^SuWCKLsJB!a4gc2M3$DKe&4VJrL]%e*anJ]WFV5N?c_ZsaN+V:0Pa&xe/N]FwKm&YsO8T134k9CNZ;8BDUft[T~I1jQKW#3LNee[@)}&)80SnGLAVB?d.)d=;)VL@341j0:@K>r">Xb+/>7B-f.Dmoqa+cA8_]j,+lnLx:1tr9p4<-Ubqh<>s2LJw`a"T+Fnd8}#:iD6S8b({$U!,#Uq,z%$h9~iJaSMC(-V^T4S+2rq#=n*g`{4on%A#cwIf_wp",sbyj_*l&h#.C3h?.wIP_WU*--8J={Dm"D/^8PYq)j5edk!;PX(p1Y8g1VG#Gh*Yrmyw-#';break;case'pt-BR':$d=',]^@r5IAP*60dN.$q-x`6tK]*$A@e)@-cjOkq]bT9xUbI4ea:EeX4LDOi1w&*OK>v#K>x<xf(ym<NDZZG9ui$Vi6>h|9R?g
t?O0okGa^qgEy^)bVh)@m_?4<f$H9AQY~0g[@rUu577Rv<9qBai^<ks<e/HytMpc6<A2wgb@!92r@IJY}AI]jy/UXb=l)`J?3r]:7X%<qL-_r)es]b,GVRK0FAQ@jviu#P|:A,b6xDeDT]/v9L.qUJ!4J*uc;vJjL4oJPxLJEZ-UjtIvXH/Jpw<y5t/Juw>u8ktRCV3I2Z}LO^4`rcZ_h^pv=q9uc5#c4tU:!E9.X#5+FhWp5b_^rW8`/P3?L[6UB7txBG^BU%balO5VU4&vDc+=pVVlz%EgoU~)
@#b?F.xs*
W?*~4^eZFO&<>*[a_l(zlz&O34*JQ]s`;4Z/0%c"Hz;t;*@UE:_j*w[v.Q5c.(nR_o9lEyAW"~TF`XpYU/=QM,t-r}Yj/?vH!T
}&cgY>LU{o2>D_1?K]ksco+39@F45Y+XBiBsegfIOy:(Ev}yRO4rD)r
w@OIqx&=j(9m`xJKVh*<VM>0d]|S#/DUw*]gGuHrBF|EXJ&0>8EW*>hrHP!/7>Y%SB5$^M#Gd4u+G%[,AypFWjX]/rn?}go[QW`eXG581V|2Q8*0yi$FxS>jQV),/M5mZvm?v7`WSf-$l-L6-pl0w
OJ&Tr2bl#bC$uBtk0$"bL8a
zE?_p=wYV<CA<u+yAuJPJ;g-"JGb?N=mO.YZ0ND!}-0Rz]~.[Qq=RDtN_kzfA24b^FgT:Bn#BfP3g@>E1[XpI?5v(oJGos#I]10S:q.6*-6]+K%RGo#"9J|y}qzJ%$JvX/pS8)cnQLya-7TYUWk3hlbB4Q&u8Rzh^-:Xyb7.1s#BpPR
E#xy"v_M?w?-7MsSxEFc<o3:~<10[_XQWFV1sSq`UkQXpki(k/hEcM"<~Cc-V2Q?#aKL6LtVj!Y?RVja/d,,q+@?St#&10%Bl)MSBsGQTXA=
X0qZ5GOsh9RNVb1~D2L|l)a]i;L@Bt3fvv+;vR1M"u7rorsT9&=lOdK9g{inPD<wWXU*6?wVP$n_VNb?AeT&4p/w4]vUiK9=n(MQ2$teqOs1Us#8p_*5Cc^DI
Qy=<?)1%9w!Cmi2n&HxhepV
U4$+buy:?g)X&5T]u
kzLCp<o5qDX!@+F=2b>6eaC(]b*[/oM?l#;iheI5Df.CmXdPv^M@8A0EJ^8[A$BN(8Bu@wOEuM_Qfx[idJ&zPOB!5!m:MPX"ceCReq7]c9Sa2~FY]E>f].]t:_ZmEupLM6-%kcnKSZ-+/rj!qb#n9IvyW-MpX/f=KX`ud
O:s*g68iN0Fe0~:HhF<bx=)g]n[/KmI(m^]~jry%Ari"(^>WoeM29o<ITFg5Xfc(Y(EQ7>XJix%lxBm-oWBcQk*&i$>ta<y@
*9u=`J:25M:7xal2Bf5"Rq}dK%~;IU$UgB?7sJ2@]q-tF
"XX.F5Oal-T1#:qTh(-Xj6ENT`?1/<M>g%x
6yYc|+l.h&O3B!bm_67fG0N1v
&nMmtTNJqGXRg48GgwhNMn0)jLJKkTQ!T:z9@;fq<.!4UmHtQ;)-TfX-X+qYjFP$&*I+jYn#*7T*y.U7#V#:Q#
lp^*AbGzo`C*aao=Rq
JM=4]yY3CyTL_Y|-YEsl=nL4dTmT^MSY?*V!lCvY9SYd,Ov#l28oc$m"]wYl
X@UMy."0gY^au)o1Y(@U0Th1iX0hK<q=QtU,F@1xTI9{!Xou"hN1pX=R7pywWTGC^t+>UvC($vQ+:o9ea
R0@QQGRO
sq)2sRCZ!>g3.o|SiJ/(pVzmj*_GGwR9QvL^4DI"y*xB&52KA_!uw"[l{_*`yaSk])a8[t=]RCb6*eJ;rQkjq>Zo<qxqv/%+A$$P_@w-wJK]3<0^+mK0,UQ(!fVOS&x=lv,xS62s@lMV|Vq?Rto.KhtlYR=!,!7vPRNqW=e&:>JkC_
NHuqo/iS:IGho:nFG1mxAnQ<:%gdaI^5M+sB[uVf[/pM>GGxi"[cR(M
(yfbN2MLP)1<rn_CQdsmTRo`Oqp[lS]`o|GPrJ%s:6E^M+iqyW)^Cx40RlwF:+HK-jN&FMIJo)1N?4FL[Y%_!2-uG/16fel;e^v~-t8;fjqiqw8nG{Gzw-R<]rJ4>C@~DZ:RPOeur-6;i#PV07@rL"/s-!rc,nh$xOA[(tTdK0:[9{8h(FvG&N9RyFx<PhXn@>u85-Vgin6HsH?k`xm!+/U(lB0!>5Af0A2,7-d5?7rk%1<<._GexlrKGf4(tI"Bp^9o<bG*HhEUntcCO[5aM`^f!xh"H|F2euHr!eU_U
n[J|o._X-(n#2HGAfZyT#Rd~4e;xM5O#_I]xQRkaadKuEtFgY-j,`o=Hvru#8[*//T,4sa3}=vVO^zcu@v$^9x"#sT5HJx7xfJw"j]gn]rHCn*V^DG0XPiDZw
q^+KuM@DTI!TaVv
v9;TV#pD3x,)Csy2hFgeUT#UsPF2Eo4;4`4K]cY0T"(^dFCJBq6Avy>?5#hg["_~V`b
clEMTNVRv0?K#j@>#P28@F5.]"KnBnDFhp3Rj~dq:Z;Mod8-;4R>rYJvG;f3ZEJ)5vo/pcczd{N*grAy:|BPw"LWL&/SS`5Sq*0eqkO&rdS25$gIhsX0
3+fsyG9)|!cZmqjCQG{DQ@Ho&&vEjN][C4#ZhfYltN_*qmtN[!-GB@iM<.OI*TK`+&3w(qn5~6|*~#^FIy/:1GCq#Q-M%"90,r[2hCN.fU<>7=xk`$ZYmcF(]H[><"c0&gg[Ac1]mQORA<9*Ki[jg]TE6_@qH4{ACt}`!#OxA%T6sN<mZrVUhn,GUKg4M`;"z8.)Jni-nJ-%)"8;q"BQu8VYFf
&+F`$FOOK@t)kJM641"eY/>A6y"W+EQ=B$:|]P($ect4,G+^3KCz2)D~>z+:3<n$1TSy;k(^=[5u&rInAb5|f.&Mx
/tk.@mZL]ksMXh@}t
,nC;]uIxp{e<&1s3oAvESK+~MC-}
l1x)5N>*c:WbydUT^Nvfb@/I{2^:G)
("E&i+Gz`UbKl$=7(J.hw[,w^T(^76sGv*A8+eg@Rq8-"IU~6fQU.#2Z0^b)lfwWd)mV"t[pG:[M`3JKm*R%1pvLytO&3SXo%^(*P/l0-
f~/)KD?~"<os_3W}o?UW&|_.[)OF!qPrHV:m*}>#aq@>;^cT.K?]t
HY`0U~ga&d
&Ny"=Wnj3i;W0kvltjA+r8PXVE6H#hbS]Qy0&9]rx-X/|`2d{]j&^HxT5tFpPD)VR."8leP?<iccPXPa(!*D|/]
?P
15!{=G"]-tiz;hIHkGPYj@*X6q)6&I#x![0OpUn|svQy0V@S&z;^?"amNGM3UJ,zMZuTn
I-+V5zVBDBp]R^N1UlNGjn_PAyl5%MqQuV5"Ykc#WbB,)zFY@ukyOa2SkD)JDXnq4Ji)vTrTh3.T5T/c&8Ey)C`3
}tdmG%X["l%[LwFNJop[jlFKyW#=7N;W56F>fK|Jhd{r8M!%UHoo=8KCd=O-Uo%R"7s#XQ>4+
2NE.7kM^|aKxG&7[;5z873UF0
@XoA)Di$D=!(E/lDFEUFVq!Fg&]Q?C,t&bOPd@#Qu"?gJE/Dk@WZHVx
O*K3riZ6Wp%c;Ib-n"e&o<1$]+sbEOBde(u%R;lz#!OWj>2;QRxKOv7uAh1ADeD1;5CS$YQ-{+OH[(C9f+8#,T|Y`;IDY9f;@;"fmdWZi.F+<A|TV/YrJvjNq])<4"UK1^]0x[?I5$9SS8mx{N(V@GIS
+pb?wviVr)$l.4jp2;W~pJC
RSwa$djfW^FZ$a"TK-Z`KW-c:o.-C/de;vJWB|ErE95#D7xT9~SuK9Gb6!W|Dc$x)~#G&#M56TmcE_("w82[JL)u/*(H4.#"n[Fzhu%Z<5njCXN}gkL8taN.PfsDL=_V.vawhySK^KG*65C%oqLTBQ_1Z44{tN*OVC-4gTj}ddV/Lo#@8:t?g5$Jv;x#!QL4,pvXkM6wRBy[Zq)&-2Lf0L
QyrCcv#-hRpQQVN7llLNP#e$w4OaYO@)OC=b+e(qH.Q[MHxi9nL(Tt]v6f+?Pn%?DIzNC%P)>bWSjF~CC(11gl8Qc1zI#qKpDwrA6&ciN7%7G8tIJ[B9:$(O
-2CD`EcG+7pgUuh.
l5mffqa
)pRI9u$^a!ZBJVyt?-0"`U2#J/m8l@.u9b(IScvBRZVNNlGO!eO1=1{,]in4O7GTYPNt&a<=ICm:dLkNKYe/JkcsvQwc5&
i"?z7DxIgu[nt3"Evs(RZ9y@NGyWjE:!b&;5PUwj3Rg$cly(M{ZI/l]bNG0^"#VJ$aa&p*`z*L@_F8U#u`M!8f;NQ:s=t/`CP[/tSKE;"eRr>PrdNbfY$KEyfY:!iGg%W|)t-)vOharz,7E3Sb&Fq?G^!D"7k=/Vb#w/,+o4X7w8o<rb;W"!u3#G:opQ#OPofh,OiO:aQ/FO7)&/e@GlU:qB;*U~t~f,C%p:PGk<q4%$Foc7OK;a)Uxm%K$6eKE7d/y(u`#E@6R!E&X=%.sLA:w|T%"0h~a^&{=zBjK)ui&pJlMV6&RNW=B<>9/1L<2mH.*SpXy:K>U7]C@M(d%QT@TA;<Na&;c$&8;JVLVjb;>_r<L7t#Mc-#';break;case'pt':$d='*`GATbPp-*60
Y+$nJFT?>rV{-,DQ3-3a;~6uIcAtQ1@#>ee>h8_5?$O(-!x&1i"-$f%b8qs~p;j^n-43.tr6p`]^96G_&Kl{6EEjMi=6yc6Y5)yBD<G"b|y|3#a6=ur+
DgUy}K:nA)yKJ;?4^A4@*gW^EtO@7@b.NPN0#;k@NZ5bxj@)Q.QGgvHDs.;JDj+ET>i7;ls@/b3,(bGRki-?%]JAUvseQ0>qS1uK_ZAUn?2t(u*[S>a?;9q9n/DFwI
a6L%Wv1RMxj}s4`rGhJan5t/a*nyEDd#QivOjX^IM2B}WM["HQ9`y)_xx
5bxTyaMan0L]<~@n%o`;6z7Tx8UVK"Zq3"jJ8/-nX&+8/ik/eV8nJ]<.&m^J4OkwlripBh?+w*8z1lHQfe491m@Vc#_FUP7$+l.i#&-YD276pVT?ZLs$8L]Imp%%h@.+7w
tX35rh-nhD]/7rzg}M6eTsdGL9u@Z_m]PCPA>1?[LTYU%6=4aET(bM8+.:@^&RM4?!y4l8XQ8B8qPBKCscMSyL5m88/mk^[0)Y!Vv<Kn}<S<<Eza|Xa2F]n
DI,A_9TysY9p$@n>cbe"$lp"uks?<0du]FgAG2qufj%[.21gE?I
_!R"w?ap"K%TJvB16OoGW#^<*GYqFOI!?=pJIQi#4rd9u<BZ9qvv(=[`,TAGN)B(p!dD}<
pR["E?
;x]XUcvG,f]
}XsvWHOh4*s$#q0rmM=:=:mrMO{W7d2G(@BRDEb)RKy>(%^__F$b+OFqDOL=M^CK)*Cu]&whfV`XVCmWL;{^_RQv,"bQK`];tEIxx#SElH,yM2o%T?SF*xbvNSppjIjcT?8n1O:h|=)[878?h1>]^pat=w0myEq,rs)2V:xwgO&Xe]Ty$$t_<UTSn^)>mt;=_OQhN-F,u`&S,kyc>JOVc#xtsv`I9OET["eWfJ4k^b^f3dJ)qGCy]f0SVifR{IMu$6=#440KKyEXkZ+Ll:5My)FuJ?hk@6`$rIR?[VBqZ-7du<`-#281KG`g3wf>C?3T

L;[>nkg$H@Oh!1
;QqCNu@wyV!1$!N:J_u<)QZtm[PBq4XE9^ynfq[:p4S"TEGe02d&<+f:Oaygb,FBalm"S*DTCj5B.*IeG/S~rB=KPDWsI.*&k5d}1H]821c
:Bnh"iZ|8Wlc8!l#cq0657h5g("g3:NB$8vjaj9Mu#<m#3(j,g$M2hL{%:/RE&q3)ydp_:
O(B7qb_bfIZL8d;@kNubKSU)1w$uAQo.kV];5cd8>R)UpIW"~P0`(LU131@5-@)JHbe$MXh2eD-5E/7wgic@u%?3wyod}ujhud=7LmwwlwZ)D.c#aOb*{lX=i9h&g]fMamN/SG(lL"g:[eq^{aGFc@!BUg!kP-y)w]^.{xl#puXQvjkMQ=$ep"a3!HbBmT"#&C+9[,#=jPvll"5[rRcxMJ;H&u(AvIzx}yqoVP7oa7R*P]aidp5K$m]CNbc4R+URG;/3GMs7jK
QYDUhP&}ueU3^3]W;1Z%F#Y+$1)"F&_W*p<G4{/qbPfjreg(FWHQ1&B[Mh&;&hwQ,Ue~Y"lTQDXQMkn5KhWc7$qGrbJeLKC1btI,r8jTq4if"2;/$ir%2@5Wx,27E*PS`#g<U0<
di28/C6O(Cj2+BwauwU"D:".t@/UcIgvyK@0AZC-
4g"u8Jy
Uf13RH.]"T;R=Q$.PCAY)#+J|2<Q9BFu/"&n_/:#q5K*YDJAhU:A",ad/uuuL!IeK&5a
2{U(2!X&:MmWigD(qJ3!-D>z+u?ZTYmmAiX{XKd$36l91PNf[P$k87oy]o43SmM:CS.=q.F
I8WC-_32#)3LK[DR9`:@sP[}_p:5BL6::m6Y8(pi9ln,9Vma<UbebE.1+Jn;/)J;`792yXL$oFE
`),jTC1<;yxoJ:9Cs3#b^b@K)"Z36)A;"A*Y&nD&D
S$.r9(JpE9%auS0
uXeS&kMx<Wk[h]-7#8RSqqJzreY<y^4[NVZ=UC]xsCDP!=PEqQb]a.84d6rc&FiQ0-@-euqlK``9L&8n!O$3pmn}]kt;x^Xkk?Ov`-;%$lQ$kGrp(HGrqjsnp+@?2oO1n[bMyONI8[5.){taT@
q019S3qK}@8v!wG;
X/V>(7rns<hJQ9!m:xJpp%8V6Y[[$;M%"6^d"G34fR&Del5QJh,a2$TcrO=qC%u?`Q@Q.~5s]%1_YB)T8<R[#SeZ(En@c1V|E$C9_6D~;NTDb":|]%5[(Fz!MZpw/gUkaY)wRaQ|G(DN^-Ac8+w*y(8[q*Q~/i?&3aF+lR.+
f8n5{fgx[m9QZ1?il)RaXRcIc":xZiKhDorX+W0E8C=OfT9l$I:Jd0_%Us"(9I~bam`2UG4rw9Mg4n_5+g]qo;OPgT^pOTp6AxIcV%"koEXJat$.mv3?HiE5&JAWUC%?9o{TSb6`
xte{4;-0ST)7q`w|s<"w57tIn|)ij8fS!#pq#Z/zJ0u#l.&,^f/znQN7MpscZ&L}M"x!lcx^,"9m?$!&>g<m
G$S%dqyV"i}Jk=yE2^h$52*kH;5<*6[vOI=l,PsFG06aD<,B33(_uuZi]6dB3A&@$W1%Yd#!y`[aUy>8cT7tpeW3Qjk5#C59,_986N3qui`46BES#5<leuvw./-x)b8j,_VR{G?c5-J!6*ORQ8m;<4fOedEv<3nC?
cWHaG/~(jYv*Ua-`w@^8T5va)j&xkG0Z-DuXbqY)G-Qa&Ma3]7XmidcT<U"&~RNe+70s3WnyYYjPMQA,W;,%cR>.W0`_.?alH:,ePvRF^=kem[+8U
U*4C~6JHx(yx!)kU$oYi)El1qQ{Pz.HZ2Is_8T4igfwev(
K=$9A(#M="_HJh?8<NbyA0g;tFeC%sOtA4IlQAm7#fBt@|$q;t+&a#0!J,;bR?!`dOplbKKI3DXi#fTe0u6h]!@&r"&Ht+Ak<0wBJbo2V.`Me!fx0a?c)Q[4
f9De#jSK)]^kv1T9`_0*bTak|w^#7a"sW=r4Qm2_>sU_P7$!`1.oAff."k#"g@"XvbCv$*olxGK]j
NO9"8;b[y"J-a+q`%B}d4<#e[De2k+0c}!!h]O`7fw(1o+9Nfs*]]8CXJFWBO,X^Ey">tkO;OgB9k.NUgcUjJ:{e$Q)CIAj[t3t(}*4b`#2dc;M5k,kd55W_tCEaq-!U~vv20Y^!a;%X(^s/*!q/oFKe}bBN!(bQ&FWmlFN&<xrP|D9Ce^Y=f/^:5g?2v0"2;ENyAx-2C8L5$>A-EN3isC3O:v$^dUAS_Et
Uw`i{2.w
rF9[Z$.FI-Yi8yE>n$)Qa+4@.+UhCY6~18oYZ&7&2$worPFKsfi-kE/^^op=YbhU16]HD0;$fLc91?Dk[J<_uKj35|?#V,Cp=]@#CJ)Fo-fHK5^B]"5XsA)ZS)3~
R,zHW#6DEGcO^I%5cmTSw6kuh>40AC:?w>0M#o1MA@$dmBk31iy!yo6[,j.YYiJ]A(bw4ae89]Z[-SpR:/C9C[7VeFVt=;`iv,k=?o..K&/SscR;@3v;i^74|Q+Zg2QuxOLQki9F;TYZRL<qJ<R$E2mMt#j:}ZKqwm`DcO_gAouZT6{6bfOM.d75D0.5BIAeFoQ*{[oAxSELS
j4Cu,,!Wpp/i2ddA(
%1R]R`a_FiY]*o
hf,f=-)xy?8GyV/2/^"Vty2+Z#(FOU5L[:994B*RnF`@-4"yU$n>L}s-h=-*sHKAqG.TK/S|ZJp?`@dSGOgY5s8&&%U[KD)kH`nRZM0gR)gh8d8LW@O4S2h$2X`sGT`k/Z#O
FPnd9e:(wiQDhv:F=fNr[XHe[*`pHM>%5d+DWy)
Iggx1bH1?J
,KX=J44Yb)"2a^K(HRojD=ItI%&F+(P`N^-,YXZRP4sz!"OKW"orqp3#bi#pUWv?T`nzk80;$*(nYBVUom`qN,BQVXCoJN.K?AH7+XVhl?&HWn^=d=cM*/RV7Q^p<p)WK/=$dA_!QIF:!1*(Wcupr.APl_:n0+EuBS#G$?nRHZT!$:PX?K+7DdfnHtJbNM$kGxgVnL$*HGJwL^agw$I*Hf2n-!H`@3hP5pLHBKY<
Xsg+b;>79]CA0w/+^L_;>vr^AWMB3DYm2e%axYZnVd^dtPa;0GV8=KZ`EyJp"*GoVa91k-XG`WBi>R#dIu.?-L(GPu7&uQ5G/ad&b"x<6]YB+u3aV&U<#3?2WN[L|w=C[-]!`9.=7vN/W[)52I)NW=nM+j:M!NlK14B4>;t96"/Js.])-#,an[eoOyNp--RT2i!vG,v83.FKn"1n>UlR5ju:qol__S:K0ljF)>"#CBQb,j3?"6kZnYZ-ky?J?-G[p3c+
h(&K/8/8?ho38$jzNOru?TZlgZ6l",fb1qihfXhp2_G7Ccf4B<7SC2bQ3!%%?M$+W0$yVxN0tvy!l!BGeBy<K}aHxw<H2:t1v.U2p|u8f*"+l<.nP4Vz7Jd-!xt.5#(jGfSo(w`e%QBuoi8g+Ly+!w.Wf|h-;-wK*tSuH:L$#K?Y3"#h/muR0R+6w%CZZ+4mC$9J/Q9^1*uDl_7~k>GXqeTlK{wKA]%32.
_cF;~E^:@tKh<wf5f"=](]8<219qNc>+{6{$DP5q2::<^LU@0BI-Vudn~$h';break;case'ro':$d='.]^AM6LD),z0dN.$v-lF;gh-I.LbtG9&wnS+:x<yT&Ej@G_CFfeUK>xN62*w(WPdI8%WqU;7m$Gs;_|ci;wA|=nV96Dom"F4xDvvdt-B-^+5sclS=w^*A:8X*Y{w5=#n[l*rWUhmE^.4^s_0p
iBuP[F+m)^W/ag(GlK/f6UXE6F[A
xW!#Hy342_rBipF*]Dwu>+3@GFjAl;>X^<l*u<1Qw-ex2?Yz?l
e*+Q|D)F(a-u]Z"6CDvGKQuv7g4CbB/0skxUO0kenH2Dln.R7lpG=OrbABmrev}b
1l3o@g2)ctRukv4/Mn$-y9ycz)M1c|&Hf#_5l:e7B}b
D%Mn
j]>u#yvGRgxt%R1Lxv2+;m|-Dq8pHmphY.3mph.CxCq<i<?REMBJLY}XFnUl)2:_n@#9p)"_PV<=C>Fm"4ZqM)[5lU8cB2a!T&MscqT2@8ZMsu26UggR:qh
K^|?skNbWPr@#]h))F;;bFc=?lv^i7(i]49t#B]9{6s`[6BpR-`^e
t"7`+rwUR@d,XCRY"IhL@5Lym[wUy@Fl*oqu#NWcNMe(1d^VnW(F92u`9j#n~4[R_Qv.v`Odqp@%dJt@c4n4-j~;2=lwj[RWt^2)qK&EQ.msFwOB:ga/1x<+]6V0XMheyAt`ATep"g}sJ3"oFRBrV5&fjhy@$l/V~P%scA$bDAQ_!r,Fd
rGuYe%Kg<QN5U.,dy4*dC[Ynuh]N~c"yiKKnVG/g*@0iYH*Jbx^e)M"Oo;@Phh*<h0em<uz^{?HVCHMierxBm:{)
*uR<=cpQ[K6O3W&=nw-mj9Nv5w]YqOVLgmQlt^P,hd]ALPvL(leeJd-j?J0^FA#;tY1~gS`V^|6yvdeZ[B:V^(:{;!5;;Y2!09m4*J=y7,l-a&P$.[n[aN9ucf8dWyJXyuXl]-gjyg"(e@.$#V@z6eqn)_&=HG-j)x=Me1M|7mZ;xl:bMK@MPbZijY`|`CX{9:yUIx9q@~tw@wVLGf09^.YH^rk;:nH7+7;oq)l_C!.-jF#6,g2uV$L-RO&?+}1C+=if$>+)jbN_=y25lY$U44[]>]sk1vGn6)[7r>Attp/OIfW}8{GP`$b|J9IVnB
-
._FYdxQcg:jj6i+6u/c
]^!qr!/>[tu&2+cEM>#wLM-m<aQ7J78T11klDWQ-A[VQA
IFkX2WG]qO,$RODnaEYGIWbjvcO-Tkl55!]7i;W@0l<3`/BGn"gpCkjSMwFoAXdt>u()pVUClRVi(XN2@sQ#;Ca1ZU22He4rJXq!9@S[uIiV]7aklKP<U_Q)endMJu_]mcjkaCK+(ovVcqDFT]!"e@rr1Qh*)1;$5LGD}O=Yd*j,oBMeM8Wc)2A7M8wef<;0}ay!nE!-.r6VeoR.$V0[k8&UPj<^CRgOG_#"_]F*&g
qGGce56#1!ecacFsJ~tzx-kKq]&/!|Ve;VL]>@@t5LUJb:Do2t8_%ox<Y!%%&aIBa
_VjTEeU(vRti;lbtoq18@gE0+(aDD5SXbi#W!~k!a";f]*5u:kXHfK9(uuOVXAZ-n[H5f*aG&rRvB"cdn5O;l{pKQQI6+H(Wx0#ZFF_md8rD[i*lqa;*g_Qp3K+vt5+Lgd]/3JU|HMs[nuUVIM]n_G#"fuu@Fk;YT%3f9v[*rE2;=}wEa,<9*+xj;*xa/T;!gkJ$h:mMkCslO3@&F[9[_P<bpmcU;jZOE0]m;GYEN|f[r<b1U$&T3M2[4D#@LgLy[W^_KZq!OSyNC]=M@]k8;s&6Z5A,xhI%n:0wGu@"D8JNhx$]=l$?[Bb
qNM0By5[hMad"EP2C7.n(TR3rGoC`Q[wgVI5$xUf]jB4;]p<(aWKmfsqP$D+aY9"%e@p,])YJGihQ5Zg#W^aYGwG?f<[7I<>%g@N#fAeg]bJKn@!jw@/z)6Vxq8deWGd@^ent["niXmZ?wpVVMs=W[s`Me[yeK&Dd+.gHV2Ntn=Ty^F;4x_%j.+njIOj9q,
3llYr:+Hi,
p`=,/11x]][$;H*qxuO[8yn`J%s7VgxTz^qJ">X&x0)-7SC-jq96*CBBk)OV-#eVwcdaX?&8,&Lq-[>AAN~44"ccJ[B=dP$TUAqWDc@mG;adbDD=RXUI
s(8kpj;>v?Ytd=gr?;4s?$6#^B%G.|C{l5w#iYejxCov(O]+E9iIP7$oN";FU]rMH_O[Z*+1mt.0%LBAm#dg.R+C96L7ajn":})A=UBuPvoW!Y(<x],lUAk)DHU!::"|Xio;N&Hvr13s(8mm!a,](8.t#]RX)2d(]$9`1]&JHg5@ScBDu,G17+7zvdr{BwD.&.GktEYgGXhm-$=875_]wDD7YIWFA]1pSLVc)LT
mcF
=H)#_L&"hx^M-,2QBoLGyp_!2%C
P%Oa._@Z;/.AmikhFfLib40?=}"C[AYD]E8wYQQrI*RHRpj9#Q;Le^dcTU"8%[`y"=ZDdSH_sGm=81T}Z{9(bpKFSL.+AQe6*0?X<9ssO(V)(lSyH*2V)/X0?~JyF<]oomx~"18WbG]bp*D(qD,"D+S"/Nd|FT5w#9V/SL9[WZIg!
Y.ZXd^aR8AsJn.bpJi%W%k:?@"u)SV5g8kCi!~d{7owzjyr=M)O;teTKWbU77@pUE!E(^Vqa3eR7tPa{:8AIQe;LH$J;/)7r#,O;ooYJ8<D+.PGy,!O0r1RY+N,^EoXy+}<"D*F[MP0SN%;4EGo(aWiE$ijQDvg?;;W;Y.K~W<XC*<e9@?!EbsBu`vrH$^dqWagA&!n,>x8(iPgODgvT.%G>lCAe!,aN#w4Atp4O%{OkpvlEW3eu"T!3<8.&RL)fAXiHn|k=NHqU8,8ObF5.r8K_>=#6
XH#uR>Z*1VsU(P(jqqpQ6s^$j<>XoP@P1*![$>ki%TW/8hD//Tnk!qMaQfQ[S4|NRx[E~#
9r6Qwg1U=>@/yy]J,8g_]=;l"6-fW/k8T-aV7Q(HtG3wnVZ<3SJ2R$wBZ6Z90DKB.1(LRCubZqR#YTOjlpu2Ux+Q8-y4H9;JH@s/neH_(&^+&BhVN~Uxw]f2_;@*c($)Cl(H_11%ooP:L8xsY%#MD3Xp3DRO;`XUmx"}9wnhskts-cYc75?^n7WBPROa#W*l4^4EOt))8Xw"qeH}-(6fcg`D).!d]bR,&~r;uHInw[AOB/-&))bjs9x
Pk&%l&
qPumR.,Sh"Ymr!p
^"~AiTB%~-*>1BwR!WP-[mtfMYaLn.`/|3z:4Q@${xBs@xF.>BaP);{LM7_Z

Ep4icYOS`UYct:`@G-
6V_A5/F$rq)-7sM}=+vVBZAKVYI.)6o25*u+#"X87
k_u}TUO&%Gg6t7RTl>XgKd$#5X$)POuP
;tY<9?,!Xrr%A5$fz&RgG5%[do}^4XPWCo%>Ge21EDC3.ENLjF_?X^VAM"D1JN@_Cg<ZB3^VnG>aT@O5wbe48FIH
8qB,K(:P(*1v;OQtcLS4Ir
554rMW;[8o>[q_%L
FpL*;hB-w8w(B{oFw+:l5WvAkA(>Fnk0*t;i.oN{bh=3[@Sh04X&t7MYf+Xf"wr;0@X)*-SVwV7418ESv:A|k(9Y
sL*![cE@BA=x4*`dP=,FvH&J"@[^K8[Cxc>Wp?$TM3Ufw;8a<KK]^ATdE.L!Dc^T1c2-N/x<@bUW[igt<h#pZTbl{gI>L+SmK:s,LWTF_X)h7*9[I-LB_(CGQw|c(yjwQ
mi{nH
Da__:L&dJUvOWm.8u!IrztVU
LIRb0S+6Ro3dG96]`R97*_06M8&8PdSY%}?Qc7XkYad&^8swmqLRuVCbtg?Y+&&nTM_|@qA{Pg^{@yG8$&iP,$6cPWjY7ui)MT(B>FT@=4^DNqs$Ph=RwV]TL

A-@j5o/71AL:1cU1_w`2AM:%.RKk})7hrLs2/z)H;@z$ZG<m#U,
bXd(B8)I]Nz5MMZ7hN.z#i19/i}hVtIRMw4i?9?p.6YJ!b{7ImGw]*[i^DhlzWDP)No4@e)/=]KK3+vTVT(#9<<I[nmDHTz6)8[8Q$Qg,:H-
4{J#&MjM4~cU0xO<!!,fh3J0u%tb(0_%S/lO/+E54vL*Z41^`=d3eK%hqc-<%4c)f>q016p
dG"%09c_Z3rm=?s7,:G*`|Pgx6renvx]i8)H`_E<k=1e53_)JidP:iwbkE[`tS,?7G,aud&Y5`$Dj>^$K~
?O!<,K~Mpn]@3tQ"bb0#_#?Ex!gk/"FVn+ls/;WGp+SI~5lM8L6/aIm/(?k-|w,%D.zVd9.9IkTVhX*#3Es,GR?:l*vL&9vi)X<Jt:Y=(&^7C4+H/_@Y_E13/vm2$@S3pm*Qp7XWAI7mVt3T+@D1Fa8(%k/JMYp]]jBa5Y
1.EuSMJHZjq_[Y)0
H)A(:(2>_3hZ.6#+M4*$%5{ZdUw()`?a~FMG^pA,uT@Ng;1]ot<q)/o*LqlnzWQ5.&Nt=.#g:Dx,}$D/;*CYZBveZT(E"h%Pl$R[IWOX*QfxYUrM41YD[/b+W2JGFn.Bs#Oo(iAKV2Om{Or`)7hiRh+Y<e]M
0f1/?V/a+Aom#nv*mVk9Jg>4H!c2tBj53XdNozM<gL.lZW#<R;bO@mJqp[,|`9s?XV>0*)
~h%[#Z6H]M=%i_|KEB{MC7u_]`]anuw8W[1M+Yvs$c]8aXhI#q_ZT
*yJ>H?z<D?4xWO9ICAM]$yyT$"I6]-wJ98SDFNCIg_n-%&G_OsqWKJdmpYTbOj;%;650:vX<|!:-SEk`7eA:~AQ.FmrP(i^Pmgn!voD3uMj&/)b8awQ%bpi-GpsdF*l1-*scd3G;Yi0QSp8+Dp7[)VV={3!H*oO[h!Ml0LQJ2.@GsS,Bn@a.pq[e]Hal_PI-ag/F7g82q/yK&Mb:{gL0J0`R)O?Uz"uVGbxL<&M%"kk2Ms!Geup
c*-:Ye*9Ab4
:&8=R=H.M+Cd/$bX6aXA83yDMz#W@ZoS~i|?K?+uOq8GP){70Z}Q.d174MCyI;;8C,_
Az%j1N&';break;case'ru':$d='+h_LV5I.7@91$TL_9]Pc,b^m7-g.h+q&n!_GHXeCEpN;*a`^H#m>aJtlsH+_/"BRKoAS-;O$n/UsNsnrMj_Lt/F2D)6OWK6gfB=]!v;PQ.G1XEX>I?MrLK,l:[lHHUlhgIC9~.A@_9>V{]M;C!G:1v:DTHFC+wlcmyoiOnj3d<>vM[^2+w[Vi
5.F6DXfAcMKjGOgH=s5XrQF6w"=iyOWKy-hnlwOtP
@<b#<e[M&L##Ci7rc<N8[aK2ls<wL_qXw6mv+ENS_>IE$d1Rr7{"u97CWd;q"BcA^SbtYTi)YX}F(E[x@L=xABA5jXXot.Fe3T[MhVy%Q<W"O_!m{?/kb&T!,SNbye5vX*QS$a8GnxcCa_p^AtMuqq!M/2$4emWq)fsG/q-L=<Zl/t+Pfy8G^]Nl%s+r2CLu-hdKtW<bz@DhcjH@K-`-bYbwn/t[2l?3"J%"s7F=(4?28iMI]wpbF6DY`g^KBD^.JVb?AKfXZ<BZ"igG.()A"9o,"3*750CL2tH!?M,F>o@1U$/QX_jhGyn$NQC>uhf6-Njs.qcUoNuTsmnacTaS5N$&lMt![(8u^7I&!_zyX7]nxwte.+SX4-i9DS:[l",S"<SM^uM
9Z%Y@1k?^L(f$XB5g=5_6/(o#_S$8Ml*PY]st/,]:9Vuj?hsC6h:%7Q<}cN
A4<qHQ.",0w[_s_],J..5TsDmCGe&Eb.q`^0yb{nz7|le@tFFO,o"OV9fG(+5#5pw:qS_8t.NF@eD&#GcdvX/7e%5d3G4D~H`/d1Iv0S=1^*Lua`0x]!3NV
bj
80FDlo!-A1mXR,vs`*lOk$@OU~*HwaUt)ss<6-eoyuRFn}e&Nt>pdSDiOj@SVJHiuq]&L)<E.*n9[P6&pB2RRmXl1OCnV<_q^qAshJe`k)4&;>-H4lJE.=L<n-HOULM<J98"M~?Ns2u_c>__krC$m~gKbFe&e>XUAM%~K-Kxu*8`.r<>*r)n]",Hk%r!hR(U.4+CHl!{6
44TmvHLkuU$m6JfM)UVha6(t/YXE#:J=1?v-]JDP)j_;L{q0t,TX]`vSB`k<@z2sR^i1$QQ>k$fAaCviJ%)%cB4F_DueV$
7nli&iH;ghzLs^DEK/dfe1|X9!#B1pV
s^CaH+H>iMK03K,ov&B`i#%(JE!1V(qlLH5Bm`dBIQZ-#k;g5Rt)()^Hm<#^z>Be:NMQjiqpK.QbV#!OQ/kLPsv2ts]PybruzDI5
A9<C/=Lv..Q8p~DEOIJPae`Nj31d:;qQ)c59N-:B/|C?8*>qG?($,{N.pjT6CCp9^iB)r|L<5=KpU}Jcnp#Ap6e"-:/PvL9nrv2o7qM{I<)>1_kcE^$_ms:`3v_(-_:$Qu"PC-+MMGe4/0oC*Cc[Tdq6acBM#JlX^qyl93oCo{T,pRQg)~q)
Zky^DuB&VO+i|j7l|]&lQDX3La&,j!v[s$]1qrdh2gQo8S`
F$Uj[]=yQFe*[M|EEZAk>@/M#HVTvN9YZ5

cG[pf
z"i_;QX&VY*s;`s(tUsgSMJabc4U^0{Fxj2.{+b]MwlqU#Ze:v[L/?7GyvbKC)=pYj35#S1<S:-doM|y6:iikcPuSPGolOvH=AKplKOCvL1HG*}(SZ&IgG!x>]""HVEk}Q=]6ZMe9KO8BH@y=EZ=3eV(p2}Mvle$HXsKTMOuWhC&X:#
)Y+KiTLDlu@WY?^dk[#<eVCm5Ykb8@&5RuIugt[v+D$0g%-pN`r"dZdY@A_jS89LL8GPBRmF&*cxGQhm62Y7)"_WA8<v!OP=6>M<D6*-zhnOz1H-i!J$EFNUM50@@e}:UXZO8-(!6qCHZ#epf>=e&MIp&laJ.P.(Lw
5aU7/b#$p5-5Q!H9NxRf^0$Jdrl08mL{l@b8kUZHj@)~U5Rcf_X(4]ChbSrUxxBkes:C1S`SS>?-L)dT_eu%Pn$+vnW!=_v}"vJ[^+cJ[kxm>t6I"/7xJBo7-%9tp;,:1:Jl2zJ&&%af[PbHOmwi[qWzu09F>QjS?x-FWk8Nfz
Tue:2Sqm#]Vm:e<t{S@uQ.C>pr6%Zn.;^!sKWH"5#P_!jJ2Hlh/8fPa_#?l$|5"LTL<OGo=)X/sl=BJaX&_<,gH&5(GQW,B?/l+0FKXK]_m;Re
6NOV5Jd;wJP!E1P
b,M"+$G|ecrT0n3e)G_FxTpJIOQs%0t>5$QHYf(s7:eQ>OKeTF7"rG?EIw]fH8.xmL9UR9[ReQl|2$T!A}G|W_ap6s
jJBn0mB!(r#wU!HDLQ*q3`OuMDa%X7?`7?!HyahlDUcAA,|gJN;hcZ^+3$;(%WT=H?wgKhS8&/2sL38;~;x7YTnw%5=.*g1_yet),T.W;:SiJ&KD|g"nBIbXKO1
w9owbvc)nv5Ju$D8PNYuX<}$#15o:SeCxs7]<D.HE,,,1Iq6x>,*p5eCc%!%fZS@8%/88BNOZZucXKFG>O-:?P&
)UwiW9q?^xhucrllTJ^dr-RWt8hv3DGG?8_lV(8E:9v,@$^MFVM&_;=qOPd#^cZh|#)2X>C2Oaf<*FBc~+SPUIbe3(K):YuEhQ&)#QVPs5nFd/f#5tUto(HPyv>:919=OAV^AwLL31T/Aq:C)#;/][]lhNIo+Dn.{Suh/GDj{FGk-E$x3l!(%N;Z]L7
):z)bfof4"0q$ed,0SiG/VnW<
UNC
^Y(J,v(m>CYT?Q=i$;cMU!1LTltrs)1G+j(jl$mA&knklJMd2O&_-)<OE"c$%.i]@y6q-pURIx`rv/%`WE,--;$5V11$|:XZ%DaA{I#k-]AJJ"W-kyK[J8,[$WNFH0}MlJ/tX6$d5g.peVOEGiV6@xSt^2;f2[v#t!8-xfXAX.7(/e}t_DDaeGI#o>:7W+2Ro-(_)^G/k62=W,3G57.4*q?y>L(SA!;<!>u/t>r0FtuNc+pO*C<GmT$rvZo8fdaixCug!P6GkVSdFE$SAZ=.j<BK)I4R#aoi.w3;`2NHS2ZQ5HC*#g67u&hyAj@FIFE?!>KAC32K5FtL4.drV=TxZDYT[.`xhR;UBxoA15^0Q/>a^@8jSqqOsH{/!<f-7+b[O+Wa0[wy>(6aHj_O&!NR#f|<UG+2q!(NmUpsHD&6
qGbOn8?dTJL?.FFCsAYft2WHtWvp11!o<lw]lM<_[Dx|6Rg*@D,n[A:U$n6V3iN[_i,3QpEHfS(r^kQ{]8cg(*GfmLA1a1
LbWcyN+@s6Zd=>GOjTs9gbF:zQAdvN|K<vW^"Vl%OJT*>$f_+io_8Tm>6c@pw%6@Yr
oRAq;_
<h/>7H<pYua9>=yE4.By2WHT,e@[;V~^yizXk9-N@(W$h=(4?Zz>kxVc*FtR9HU7x"Q5@:+NNbcFzb}G{i9FZ=,0Gq^"U*I&Lrj"B5l)mn(l$`j&/E}b3@
dw*BquK)B63H`g<cEFqPtA_-CL;$P2sl[3$Dt)R!$6+/>3QBv/)vH]e*Jj%18t22xy`<O1sREU"DG"j"J_$.aaPIkU)|k{1l9!1bp*FgvPp`FjYw>!54WVX&iI/Ii/_R>d+@:$m$-zI%ZW0x1xG`Yw^qhSN.?z_[OnPk%U!;WVVSA{xCO)*DMr
LnH*@Bva&cgo:5pb20dD0?;n^&3vv*,MH/a>|Xn8
6B8aQ-6]+^hyPd1gU!UFXW9]xcmI5y<TT9a=cpt
.HdVu~ns[(yWO8U|gdl_FbS`*rU_8:U*ZpBh,0;F.&2
x<$v9bbx?]Bix+<N.t0Crxq_OYHa4|1U"6MCFB&SDku
t|-BbvBpSU
k1+yxj?@S+xPn,c]wm@1j8jx1=tWJkcY1NoV8aD4Tp>aEDm6t5O[?Bi"Uf4KBX<UQkp3_t<>hA1GY-~2RJqV8=k"9G2oHZ[j*8>^gLJ
0X
V)V0An&yhT/p28oR.L2:ebF:YONZw@)N)<YD`kppjJa)8BsL3m$zSdVN"}tFr?po8old9nv8A=vp%%xzK|P,NRD8xl7$;_m=p3ZR$Gvym!w94~lvq-?dY?Z1A4:@&lBRAM]%fIe`p9,Q3L(H0Y-0rO*#]7-U@#>{%.4u1uZ<aCJ|uNC_"9xh0&"aIB$lmRH1?Y1@Or6vt@
CB`=|Ke3;"36-mYO/Q%&L,iE+mA=U4UtmS`PC$
&`BQJi#r5r(10#Jo:zk{1?WW0
[$6lDc/$CRR_64LT;c53V[/!N}X$j,C{1S)"HU2I>o2%B-#bQ/O=7+b%L4;|@
vMC:JRuH*A[pv~Nk$HUKd@5{Iys68-Fs&T_uV[J;FIK3-=M&K<!kJH)8TrPMb`KI+o,$>=D%M5ahL`]&0q;GIZ6tGYk|hyfOQB>rL|T_m+=pr/n:Q"0@9]@S2Mj;?jryD[u:-d>t5."}kQo|=uqkh/^5^lr?b|yGbWHH31H_iX!i^}7n8025G,NzFxA(fCFeD%Lk4Wu3X7sgI"8P_8t4=DjP!6
W2:-1N#*oYakpe6,?#JN!e<CA?~Q[6b;BW~Yv$0azQa#tg;j-Bt_y+X]_QuR;[s#Jpe1<A_S%!eg3#B"b8f3Hc">fYy)m)^j&GW/$,)k^GV]9kJepx9*miSZ4-4LlUYfIlFW9Gy^dK|G,"U_,j91CHD[Ja^cV:htW@^Hxwn,7xC6qT$!S4rdZ2,n!.^q1?&16-_EW`Np?
-Vod
H9@FG5HNSy]Uq6,;Pnx)p~rHZ$a/sI6kyquOo)J1u%er3:rEd}KB"gA7((K=Kf
SSxNF_tOuw"sbi;Bo-JSjbPYC$gMY^PDT)Y?:t12p$z:;Np3m#.fFu1[0nLsk@4gL/.f)%
`vBd:U)pTfOCi+K8RIOcNvWVsqKJX=Kh#S^1c7!u)9d!1@g7"yNxO"Ydw.Lny!<`khD.NZvX/2=8^$*PC]vF&obw:3MN$$dI^/Me)zm^mA+rK2sDTWfY:tuv^~_#g=rD(KG0%,a*4)Lch_hicKyG:!O&`zu4r|mj)|cT#q;))c;[FgOq!nat2GpN8.ZF_sr^-BHR7-YsrWM:v&b0-wHNbUi$,8cA$uAXP
9
*vLC`n@hkx[DO@HF$NoDji!2Pkg-0#M8=B"D>F%XfNDR_D1+A{]|j?IbN(6!;vBZ%2^?c2OZkmuLh*(LI@wbED5t"~a5%)*
uv*}d]s,%}pD/?Q|9(kb/AsI0}1EdDSioUPcfcYK&/[NGG+b^`$0Y.qIv[M@x;c5LHgch!7y>T:DT}6
;R>u-hUZ5.ApHQ$Dp9Im6odph9e618r"2sFW(K,AM1kP3Hov;lWpMxT!ucNgS8nob=:<$"8)GD9j<uL7-h[w_*fWRfu~3Bo2;nE_`lMJg[ZjSL%
v:0j4/ow7YDyyIQrIlxu29Ip8"%RXKm3LBpn;Vb`qM6HF=oH+TGqNn5zge5h")8|N~$M7V6:dUR@%Ob!#XuAf2@>KAUnGynhUpjGq<_u=1Mrh.r#NZw0y6Vd_N,ST#=~Cc9UVOcN,:$|wZX<?q/Ke-dlysP_?0$>]aSO>3q61{j_g88PBV2<Ame@iuIEFTo9u>tn
%qv&P.Z2O[W5AQbYJ`lHfbGecpMe5Kp%+F#T;T=OGfan5n@.C@92,9a-O<t?fl,ZpbEL
h/;k<4u}
[^@XH>Zt*ESJp6m.B[qLl:I%>h=WfoajWpftq+=CT
TQzAj##qSN#rVHkZkhpK#3Ot2)V;P
,2A
xM{6KWL$?7G]hoe>Vo`7xF+nsQVOW7*S0)%x0*g+*dR&$fr[f0TVZJ}XC3=1"(we*2#oqK))3bqK@?(R@Ot*jD.d&MftX';break;case'sk':$d='*]^@j5HpeB}0
"*(0*K
aS`2J1>&5`Q:GGz79
D
"jY#x(uQ:T[/{wSF1&n,l5=wq*!_yA9vCT"YTE,EiQ&wTnU&mT=_p8B*ufMKW58WoIDye,#qcpi$n[x6s[*?a`)V~HKro1<hHJ(,cnW[g7[$4?{!GmG=`sR$$q?/a4vEtV{1Us_`M@g=P[6yT0]II;+A26qBMF$(-mng|>HF`VFX+-I?nYtNu4-;:qv)&/v^KyLHXktUoBbXI]dX=_V/eS+HFZk>Mc,+3[B]A(]x8d"R
L,:iz%e%/
1zv72q+lS<y|rLDW4eCqDGE]I#7H;``6Ksu"x&4W
nf+l%ffw,Bx-s&wHu<tZbj>Gh4sx^@!e!sNx+M/GYtI[k7f/:[FMlp=Qnk?my*D_tK,90_i<%Xa1TwXq@qFvKn[qyaw2{vkR=u;R#wi]*_"@:eWi33O,yy^bYA3`Hill1D:tDo[H_Tr6EI)0(u(J>#{B|>:sMFWTFUsX&mdUfLMk;.u:%OD1lhVC{jF:qMZm`y^u]V=^n`}F3:t`UAq+2_LTOMN6Y2XL^_ZV0jbZy>x<SMF*:]0
^H>G]*0mdD|gz;CHm4!o&i)]p.pxYv7<ec4o^9cUbDv%gZ(H:;{D-.f9yS
kMZN7i]D`zHQv.fB4]B>80$ti!vgfeddgXBi4f*6PLDWqJ6VU"kJWKi}7B<BI=Du#(1FQ:
Ol>S&_K(T;.BOVi:hmO[#A3g<e:Esd
0Yo=5&4=i$_`92qf9`U~s6w|oxjACR4;!=-xy8Bx)$naD^p-gRDps>U2YYO"ds%n_JBs.)<Eq+P;ZzAQ?WeT@l0lgEB}y;n2Q/M6u?mhX|]6,N*0Y344[G/f]-V<lx*^M2:[4OpVcutOhBtJiwbD?]tJK8X&*UQO:eq1@#Byw8tcq7LW2~b
JHn`AZ>9M(LkbbNMJ/cC5JLf
dBX<_Jq
o1U2#Y$#=Hd,3y:3{G)*=]5ny=1%_r`kvosdK/XNRc[t*3KH/(i7-MmK/hi?XQ;2yV@vgfTkzaCsP"%Htqkd0O|7R
cAV%ACfmVilHjEdtx*i^EH?P
C+Co>%#!F9,XMHn?NZ^TEMHtxd,HL23U16/nO,OCMgZ"2bVWB>&{LgpmHD=@y2]7p6$7a0x/"nCTt>RBfYh"4kVwe-i%nf_!"LVT8vcg3v>)Xo7@>[izoCQqaI9{4<D~[9e
YUgZ9)iA+`emX
q2C{lHbPR#h*)@O0%nI{Vk+6actyC2u<
@UjrTSH?]*)/x+7U1-i<=wVm_`H!PR=2PD77kq&:EQI:_tB&D
JL[O.YCD`XcF};-Wp<_SO6`2}x%b~wbO{r!=AkUJ=]ng#hZ#@h6ym`,;L/Nl/5
Kz;9/#%1sa4G40d27&;`o
n3)lM:+66d3>>@L)4{Q(:a-LIAdWv]N~7a:)-1tR<2ylA6L|V_tb1WV|^;2K<^ABj@[,ycnhMwB#WcBhnOoWVBR{I!.[BD9W3N!M<Nf
h>hH=}t@HO1f]|awt2g^iEHM3^e*K,gT;-K,EYZ{mA7d5^.j0/Y<]R-o")P9h6w)4Ln{eZUQZ5L%@e"6cFLrw87"Il8l/Z2:,vX<$::RuChzh*P`w-hq>1d%?<sUivjA21D_q]27&_ahYZrpa*v|:c7L,>l&D6fOCM9-;dT[xJJHA8l,auUUJCDj<)SM(Xr)pqaTol/:DIb2^@8%DimI]:S<T6
RQFAA`3T_FP@d_:AF8HPm29]P<:Z$dcnpV]]0BJjN!SoR_^HL.`p]="Cex-_W4eqE//LaOlC*p*_6Cf#fVPr/w0AZ/YAa-yfy`NyBPIO]P^WuZ1YROKTcs_tCg|v)4u_7v[w#@MsU-qL20L"jn&#Y3}.h,F6+
i^aDiGtfO]W
EZ
XpZ=W/fT!9&WK:WX+3PXB8!UWrIAN@Oy.25b6!ly[6qW<uvLM9X$!Gv#T6ja%?z)fao#-PO+WCcS#WnsWH&L8eTPyH2N:;<>5_J#u%Z<EZA0NJ,+02TUGy@)Lf4y.BdSf(,r+qeX2!=_%F4ESZ%jZO#<l"Th(hZ:/D&foo3Lyn=uX@,cAYB{8:iS`F
[7xyVu*U$.To;teo?v7"UqYyXHo.Gvuks(.G%RRL@&eXOKOIO*d6Fgl=78|)YLCxqE)g.y{"<FF&?Pj(U4V)f%9LI6"s``WsPVCZ,+zv6&i5PX!o,d9QqDsM(<JXOj:<e5WpL,N%rJfp`W;2bc?dYNUgSBt*slxV=wG"OQJf*c;>VC6IcK[Ss.@m=FG<6UKA`a%=xLgYCQFmFs""_wJ"r"ZL8!XM_+9$hBT*ON@d?t%J,;].e"[n2l)dtcEKM!=B#;|CH6dW}={N?%{e$V4oX(pGsu#,LRm
0r3>OSwxujTBm
+"M[N*94
4jc9O<9?]8Un>Hiy:Uw_jWSwpboMhw+VTta2Mb_37,M]r,6jN5C!s,`8&
D"C-gAv4$+xQ(8:wP_))pov=l>>^Fe<:"ITm5bQ|[&0A*OS&2~__[90Je)_!/T[I:"]u=&d@hF%p_Dc(Vkgn%TjDW?.I%7bVTZyiVB7y%hx#%?!0kY%9"
H<o"#e-Ms_uYVAWa?4kZCmz"*
[4(hbHbN1ck&:rx/)3C=Jc[==,4YCeQW?E8g.-DKf2n5d+2d$!Sf#*]gQOP_.^Vo2Zu:VBPkWj$e!L/|]tRi
$-5m^Nx0>mulZ*Q^Rv9IU6hHsn`<2T:A;],%2rd?k:,0d=UY|dV=v;y9,kOhdD|.T0"_?YQc#>839(,Z(8XhI^+*%^jYA[6CLp"sr6Edm=xU?.HJej8/&,:5DtFI/)HQfTzWg^qVo>+>^w3BdR}8:#A;3S#r=X,02[OTxnOZqqMSufJsiV
g:4.U7HHq.vrdh]WPBd
qSd|-~5|>-CvX$$7>f$u&Ox=/TN2Mg<wIj6D]Nl=tE6e7VSP.^cI:Wb@Nd
0dE#=L--?mC-GrG"9EI_3pHT&L
)S2Upzb}"##RN]Iy!R-g,A1QAtnCj}MMO&E6:d*O.6.,CuDL!AT3H6Y<r#k2D3mGqCa6d@5tw2NYA<YtADkKm5T|+lLEB[5.v.q&aK@}i~lCru1y*qQtXDr
G&fEb|.s7Yqq4|C>#lJ+N.<N%KRv@h@Cdeu46{)+-WP8d@Vv-S2;ORhQQZ029*$ng+p`02WQFH&%2jUxrVn,5HGZ)DT)c|T=v~kJtJJBV>X$ZFo)4A(7RWw=Z{*Km9$S899~HB)In=rd4X)-icwsu`Y}/kgLS&.$":,VKnQP<|v-Ekax((v:4^"@;?@dnO#j8Y+A1i`Kr>$;D(e_/6F
!u,|EL5,6jT5>$#-#TYCUT)CjW(2^_ZAH+Aub<a>SE`tx,H{LG[r_>h1inOrm@`=F>@(O/W/jz([x"OF^#v/Bs9U/PR>/>iqa3j3I*D{<DV?Y=d]O-Xd+,cF,*4vZTq:ms:q&U*THhM{1.D85W=`lGr"Sr=_MR1&)As;&]e`][kJ6hK}`1?eQ]"F%3/ME"/9RR,idVT{<R8QY%4}#3M>U<9J
<!?>0Y7UY-y%~wQ%&nD.W#QyG*W,MF`ZOE,C+!]1$9V!JYfVp,~4b?YK3/3P
8cHjHn`Hb)B4S_QE]G>+LXbk]WWlW!L^G"EGwYXAu.B_1uKA7/2,-MI5VJ=7&S&B]^O-Uu%),]W@ZEjGs|5--3,/dL/(SzLCF_o{V)vn:VUU1so52drV[`10Fz<Wiw`}@_^fkWW/8FGo/.>@UmjH(:/oQ.B}fa[*1rL=W<0>g1&7]YnUtU;4LH!asnX(u?#JX.v=#Sft6HM[6ja#t(7-9Y>:#1;:LL8F9^!F:@CEf%ouK|`A?8Q`fll73m-ij2LzIYOzO~@*u%Yh&/gC`Wh(W
OmP<*fjy8t,XO&fz$)P|$dU`Ipem^b!rD8cIEoFGq"A)!62UqiVhC&BA(1xhO9G@p?3&#<SUfHb<>M4).Id&>O-AY)U,j!KCA}uH!`Z)LVJy?6dYBd6Vj)Y#+{d)47_F:?orR^cUcZ#/&~D?9<3;K&nbHY`v3cdxxXJp$|)RM1&<#ZW$"RV.,sjmb-d)wJt5u^fB$Qj~/fn/*W2
e2:mNU;OX[aO7-HYsu_ykVdBtEtKlw,FOqL$%~1{Qx$[4O5H_@3q@Dbhs%s!r>8#"h,Y=lK}sP_!&^;NES.=dZOk"Kc-c~gft</nI.m4K)KY6WG#!h;9DOY6d0:y%mE9ej7JB%pK
4sPN1n73iTxjC:gMQ6+Ow+]BXqb5vCp`O
rSPSt$ImFo^GBXCCA)+T>SB58gq$sSEqCdtRo6Qe-"wW_"+1dg:IdpFC;-
ow,e1`a%J)STHW=.Z1>RM^vxU]CDLlo#cY!HG4jQR_qZbmiNi9$dQ|IE&!,aDg+_IEmw!WXAK$Kt(CgVEemZ868m;42t[@%OqAN.m:9DrN*D
|uQS&hd]eBvMXs-?:UtoG]MYYD2Zp9)#TH<rk5Tjp?1NLUTx}tXLh@@m-k.5l[lD0"JlblmYQAal[y<Y+
Li)M!xE)!Rx"S9^d3JYe]jQ%"OQYEu//~EE)ejeSdOxAiU=fmoQJeBwt5WvyM_PG/Z_@!`uPOm$@qcEvdN>LHEyNG-aT[-A$aiGkCSO?veAMm<wMNS^SF24_jZwWQ[^+l$;N=]A[#e_0`nu4Az$8&
v<T@R^DIH`>$WOp?C`x@7G3>eW(R.Gr]ViuYCH[uIJC%;TYOgZsZYS:s*SsKvt$;U^xSNpL#a^]a)F_u}p#"b]bnWd=t6a5vB.mevVjW?4`nGbj/;E
M<tQ7{1L;DHhfE+Zl}D{C?/5VY`q8jc*Jm+sO+mn;r>Zp@aJyV[[NcL2XxOx/)Jv$Ul4O{wBn%
Mnl.Ig17@jn=]IC"HXTh_Bhm80SS97ic
wU!,L53ZcaXkvNl(*$NdK.[ao@AuMn;1T/c4WwX
fSGKVz/ZcRXq/Z7y;tcX?EtWn?V[lal*r}u?KhyaF!B&r2w8AtnD"IjtG|[]UD=wRUmt&2:2"|wGo:q:jc$u"
=HFOv|8}b*amBiijz"-#';break;case'sl':$d='#Zu<&6LA`+Y)#HT"+ol_7Tw+0G(o<qR]ovgS0*y@GivlD=AGGA,Q0[y[n"b!G2<,f.~xDXkY~00
/=};ai;,-4X8{Lq9y0_)HrqK`xQr0X!:Rw[>cB70a<;AJE@iSZ3gJL."t:`?xX}EW_#65H2_PJR6RhnXa0f
qExV(iQJ5A1q"Hh.J^?]BgWa#gT?GRWcD(zg]rehHc@^`u
sVI(x2`2^7_!X)2!>l6]l=G%r|OsjP[<s6x/?FkQXst;P`H)InkQCa6}XSi@jnox3n%gFGI3ye,BWvvG65[0wcavh_,tcdnK:)oe`:jx-}I.FgvAvQ.*vN0Ic>3gg~o^l!k,_S:~
uB:c1,-Hu90V&k7x6gdi+]-Dvll744wh5DnRm2IXV4m`@JusKwohVo8DnIkpuH>_dcS6{_;0oFa1O
AO|:6oG4`Z$?FEj#s(`ypm.q%n2lh
kEqWVQa7S%8USg
#7M4B^FwEup#4A@hcR"T`eQdE"*1FEEGDH]YlZtnSQ>|^Lg]5zE76U[;@([d][;x]txr&8Uwn``:fJ
:g)wQ.?*OA*bDj/_>*WBWJVe62Wxo(!V&Kq.W)yn6d$GQBAhNc-o;b7OM?EDKtJ[Y@:*"AA`3CwrGZOn
>YK6pre3jrX3OLhJc`&>-.%1pe6fa9d9xD?P@N70Et#.ct,j2E=rS~"`].Qe,~/fWEy0cP1CTq.>5:s
[9!xoTE(1MU6#.(@#8HR=xlsE!e13C_%6?LJ^?Ikb:8-a:Nz<m^:1~NMb^s8nm^$IDg,Rq5@#lz%3HnX"AE(QDHHlykob<efHd?Xo^s*u5yEpZ_4]bF5?ts,KL5tuMz)Yn`}Zpy7q.q!w?`4Iua[y0(pNz4QJyVcIo#m`sM{<vxxI-9Ip:l{oJG)r;,}FYG}DGje>LDO.4+xp
;0/fr.YpncfNZG]wKr6L=d?oq`o6$?]LPKLBx?Y:ef/<`5@p@z61V07Rh7>Q32^9p2_pk:p.n3x"HJ:R^b/PA]2!^3jPtVd:@
Q-+<h?*^B]WZw@R4kKB8[;X#NI)Dn&:nL_d]*wED=!tCS9"_h7JyAT^m^IkbWHBLFdH*;3sboo:R#6lIW+**o.#s/v&[0hg1.wUekTA-7+jXg)kv
O!5={hMCr.D(B<y7#*+<ywbIe8^g_I>fKEcWYksDD9iK)A8*7@*v%.vgXc,]>1?@txcIk`d.@K@,<C;
mfv&#*y`zFX1^//orD?&_7I0.Nlr2#*beH74r92ksuPGi#ZR(r=kz
Tf*"fZU?B/cAv[op5m?qxT"8(cZc]%,0tmu;Vk-(xc-reW4Tf+UkA1_[^UQnF`8`>?*hsc7T$riOm5tAtL|xW@fjs]=%!_(*h;+7""Ys[rkV:_[wdIP,}[0*e`dsG3jF7=hS"ME`IERhu0bW8B%nuQq<&Y=,E?np(wvjnbv=P/Je>VfP+/}1fLuZBB4745|[/!x8)>2)?[/[&Uq3@SIc8/}Q)vW(!Q6GU8uaw-~_5/:%ZJ*`Gk#T)m?Xd!=>tZ=`R-o/jrN>2v4^LFqnf3W&Bm4fPaB9[nD7q;KFO>*ES$b3GE4CNW(;E"t8>u560N0]Yuc"YEy.zT+3k#D,eSJ9_i"t!9_%;)rL%T/*zRdmEQO+ZvQ;"/[B#`CdD?RYm3WPalC]#-)at&2RhR3UD^qfx#[YJS|"hd,F^Hk454XMc#Vd$_EOACZWR]YoiI0$uoi?#a$lM"R";F$B3T$(Ji[S6+TZ}Aha`jpUYUX+NQ{5^A8""pc.F
9qZdK`X6]z&&-YnTl%5hCpl%ZbOcl(Saf[^8mrfXf@OJR>wB2y9VJ_;iH
t_Ztq#]y[G^_X`Vp)o$1)!Kw!v6Cdw>Y=ol//2h
&NaX-0<":H3oP<hBB"F=sNk/zIt*b!f*?,W`^a
N5d0L)W$X~)RB[:cq+I6R"sM&>)cKabbD~WyEcZQ
UrvA)AWNvm|8A352HHT"6/cgrl@CU9USl>p>@B`j
?lCr?s8/.E64Ihqsb3.mj3Vf7e4N=SNxsV5KA`IHO(&CI!
e3H;$lzhvhb]6N!"ur#Qkrv%kKb"fyt=a"7m^p|gMUW`qfs1g;j<
i_S^+8&CNn#]Sp_o%8#ZCm6K?QpTlsM3=E+AUUN?p[*eK2TVCL^mDsX;50#t"_<x*KU%iHfH#IX0.<QSgg(bp5BnIBz&`[a=x?MxM2=6&O+=;Cu&]!-y(b&&l#cN+v=l/-JXQ4w=S>59@"U224Kxk/[_]0Gr/|"s/+/]6aU@&gC-jG[R%PE,Lv9O)3IzO2tl9Fq-EKCRVg,DPcq9GBQVia/U09-m<~!;%;7+dw]d*$lFRBh]aE@alEr+Nhp
wINlOC2ON}J8RFKO.aL%/:Ca^_5k5~kU7%Vaqjuq^ES*L]iU5Meo"4cE%J7}4!@BZHvfwGk"moyV^AfOa~/nK,:y>;WZApuJrPEK<=!{2S1}MLfmMD3IT:h&)^N[.*,"ETX)Gca8!9$aPR9pecHXkOfOVw]e=xtM#Zb~AjTvYb;E5]l~]QPzx6=#2SKBMRsSK&OVuK6:N&8j0[6uqjx)!yNH6BLYyIAqFe#dexn!Pz-SkeQ;#q6&nl*cadp.NJFv#iXOF32+U0GI9x#:uJ"/wMO]jKLM###wnI?cUo=g32p*Vmr/B{xYa>@ZY7HV#KnT8MPtM}SwV^j2nh13VG+t+wSzXEAI@I#d2=8oOB,h&t(d2WeGr{0$Nva3Rc>LEkxeIg&-P-El#t$zTJ@w@!A_+,:VI{S6Vt.Yb2?MP(?-WSqKa)*)1LN
oRNm[Q9o3[U$?jCg>bOO:*ZvqYYL^XDfm&Sma`S};)I`&j.M#vPv?S:onlTIo16@rBnCq{h$@/95&ufWUDApL"XNY
%oIc,k$qfe9.e>nR)$L@c>gX1jYY/J*^hm33]jOvi"?=,8HLQ4ez15M.h
e5#^BG@z@|dC_Wwekf2m."TRQk3DWaIl5bQ>XF7lN*0`9)D,]xle#m?(S5-{E,H
s;2UtUmsP=_eVt!U0mZ(@5BD5n0:%rl;t*puKf0g;Aqy1E4eG60jp8x7PF.JMm7;nE:[9!3rEGb@SO
.E1-/r"IrRI*":zE5PZ${4:vq`{;4T~[6@dL
k_TeRaP02giox#igd7`X=Sbo3Q/IhC%mt3Oe53>~fvq*%<%!LG@r4c!DC~p.:HM.itN;"Il[#KiZm*g)FkWXPlsMGC6U9cSD#<EJD|m~8M:gbyAb=_%jFGhz,])]>ooZB;@]"H#[qu`|m9L=E{+"Y!cDYuB.F~sX!$dv5Uh2
Q95-P-SG8i{>bK!HJm%lawo9ktfO}!RbDTT)_E[Ig5R::$n546}w7A{54&#<AGseT#+J/P~pYfUOm88hf,8G.dyfr0GXqmqdI.?=-F&.98m%nKVizrmKn-$4T4|W(9dJ$p3Q,D)X?UpND/QHer(L,]ziLP,LT+e7:vZIX&#jgS1VD[H$-Uu:5StuNG=dL@X*["gY3H:!0IbHH*1r,jBFb4*5U$bC+K}gxLFPIC1h~spb^Um=aqR
&%hrBt#bnm>aMH|;?v7Ig;@C6x<lsVH2!P07ry-l+z!Z);
^-c|9e:)C:2A;`mi7LcZSsL"+Y"[SQVH2k(/Z1:g]cG96TZKAr0|91d5h3#I9Q%/8itk7?9~87b&/F;@.b.L8o:O.Z^"_wx=qiOEi1S5dnJ1qcDl4jH.5o_aPf.<KSoNdN$DGF#/BV]ulx0[$A(sPPGrj;PtW
NCC#GU-<G:aFK|cjQ.<xs(hikv@Ou~A}Ym
qfQ%{/9.;2Pu+V_CaiW`LY/!Ce;+<Dv>&$pB2AZE`(=>0`Au^N
uL&,"NGQ
?N
E/?s;jm#.nn
B;KOi+9>H@?hoyMh.;GP-c64dV>Fmab(Kc$m<~[IL<^"N?1PgE;LXU]sR._q@F6Qp^j_*Ql1#]R97(?8y+T0HeWPFXD&6N=8P7%=K
mt0)-)8mq/5JK-HY-_,UFuMb716Qun6aCBW4qcHnwwi#@L=yWjsx/!6uLhdLp,%
5^+)c|9?hq#6cf+;eWM{$((<v_6[Z)/WSeI8GWJzo
Jr&:?73)u"$#XswJ*-nGQ.hwbL2B<_f+xIY_*H_yMWDNR@bO/~y%DmkkLEo5i9*"T$W(o45N*{EXA%C{mZ6;qsU`^G.NbJuo;LH)AJ-}2Y.CHv=w4j`Q,iwH_r]=DU<GaDMzrC$8a*.A,<]"S!,rA~CD%c(*[w5Rboyto)MZWr)?AV`F9<@[RR2&X]#"!5yareSWA}kic}+^%,Ux<o4qIJU8g%#d[f+l:mXlLWF_cOt2GK_h2EtRkeqr(&VvZKEY9(q-pS1&X$LXErl(,PT:J-tZ^}#`<-I4PuV?0PW8MjdePG4;x
mvT2,2wpT2Lfinfe3enI_]&*eD6ccm,MyqKjeLMQ"!HmEWH+6o1VR$"a.K[-/mq!B{.X9U=Y=8A3=W:&Zly(06l(s5OW2OAa$ik@!hV5:#C>@=5>4,rNu(
@XS:6;@feef]er;$Br./#1aT2,3ohm#eJ6|dT=p"G^:^mF0/&!6ITx6/-#Fwj8H[q6J:]N:n6((t
/f.Z&WZMdWp1p_Z(0;Zj-+$O.YO<A.cP"|My@~*2![]^<;(*E:NK.*1Eyk&M=k&?3"WhS62LU]S]Rr(cMp%*h1T6g@*@2@FD0QcdBY7>`;/%.N^s(pTIJyd+e85"C#(?OQygQU$N$hZVXp!X+H:~h|ueISy(*q6nQdNniLC$tf';break;case'sr':$d=')c0F|6gZ;HP@!DTN81Qt6n|mH(`YrSxh<(Bf|K}#@;.W;Oe8<?`P|]KVnT_,a$k1/OTO(`mp?6k1`mZxivixEM
69t6
-?Dc`S
XD8=2)BV
EFYY$
oWv*ou9hA(gFRd=x";Ef29i7sh~,yM:Qx0_3:Z](qL<Ap*pU3czQhGs`)-}ELMBd{(ZPJ4Q^E)wDI0Nvg[1Z-CKOlgA`)N@(~GWD-^a;B9:jaA/A0rI&Z6_GXgtWc<5grwX.>H&YTDNPuEQT7.8ERive"?g.w^0@ylh,sW49x.|8tf$&fEIcDMky]7X2/Qy):Uml.2@oR*$&t"H/*_eoA#v^e?dT)7$l5g#MrBQGD7LmNpFs/e)GdV7mUrKc<H/[bwcjel=jjx
wwwpXC6i1"Iej0H?l1TZM~aqs
==vXJ6wl
iM+wTf!mz7(
5F@5S-I:5,ZGtBYSvJGC=*k;=Yv<2nbqOrG;T_5mEc{%NkDrOF=KTj^*&sX0>hhZE3@Hal54i"!cj<B3s:,RKlo$I).%KXw*S%*U(ZIW&8uYwoIDm_x1=!gV"q[s@FIHlf!VqGK2]w@1(VnG*]DH=fsY%.|Cni#Q%X_.0!"T`N
cV@M@Q.w!+m7;h16E=WrjJQP=V<vlUTIZW:/IcniEimNyA-.8;YoUIniCY#xE]$rY+ss38N~o8<P.P,nI`eE_Bjg+V$
mJ2XjYD9IjD:<hy"?L4GbgkpCo1X?!71Bb;1MxK_LcRy:nE8i8!dl--f*e5a-T$}.9/Ev]y{5W^uK)+65eK#=VS>mGTQMLCOKX2B0Z*C@wk^vBPpx}R:2%4~don[dvSIQC6YrCavH?V=iSm;`iMqAfX[E(9B5rHSX:Mna!URO%=Ra+?eT;+RcNCE:}_2p/;Q5tMs)FL"*i`u$qZaI4hQ83Sa<|oUs[X78>^!&5@m)$paxh!WnH]BD$9=oFVy%B/0::;%=P1jfDb5t^R/+K?^"G.A`gL62TuM:0SGT6ggA#cd8StPEE(=RF*4k?rXvYHp/Y5sxw=oia$R3O)}r$Af-hsKmWwtrs^uo]M]a
SmljrN,?ZpsW]rVEYici3SLl(@ik<<`Kp+vxVy_-vbYv$&mE*Tvo]f4B>e<qgZ73"Dy{LUM6h|5oZ,/dM;-Y/vDh(dskh3$I=!y!k(^(Hjxd"&R{XW7QA=&OeCLX%YOTRYAYE@Z,y~uaeT1kcln$#7Uwm+Dfer=MIt
U8hbPgx1tkM9<OS20i3#bVw&zYTgWH+^"5@vh_jJQULDu!OxB(bO,ODr}75;mXQ_1YBUvkx1i[J6),FvyV9:5aTpqSrs)55QHh&?ISaE/2d#$#=<UPuSfw7bRkfPZmG9*w!N4*gZ`jU)AEWNeA"Ss@gUbF,R1
u:n`<2^9(
@O&O<&69,":S35hx0bQS/^[A`;$jG&alKCTo&gij[2M8q-cf&cZb|0dXegchF,ilUqj]X;@1"]caFVobK)~R+@5)g1p>ta,`E;z;KF!Z
kK!Z67r<xRp-qZh3$f_!H}9Wlq_}<QjTCv0mK~gm%V0p"n8pnZ@x8XOYmT]I"@P*Nro@3wYt_ilH>.>P^t9O5s"bZOQH;b(2iaU~w*9f(qNhUb+{9,b#F6CWx773N<2_R8$|Q=wL>,+ci@;j3.Jm3)kzu/Cp2ih~;$?P@+M5x2FfI5PsqjynhUL~Q:W!IgRO&N^r-R3C)Ktzp/jO:8fSd6w{v]l!XE"k_X1?hQ?dSVYf[~[)M6mxt([}7%21m#-`;hH^Q=I%FfwApDn7JN9VEuQ{3
94Uah_;yaOi$Uu#>W{:g"++F3.IBePb<da8[k7Q`6/,XX.Z
C$S,HxVnuT$TLk#i*nav!8D<@+3%p0FnY#
t3`(2R;<jSu`8nG6-[@r$G-3;@IxT8ZXXI><y
l`K8AVN#nF5/8:@X1>pb@XG5Chxi/NNQ]"`egmRT%I4,p%JR
>M"W/d(n;::9CrT?i-CyX8nC6y*s>HX`Ao0A_Z<ZbGR}-kvNic]l+zWID#T_9*qrOP%yr|lZm1^]lH`?$c&?f4$RC%[~O7YXs)OHjlAZ(R*AHdYmdx>^d{Hgxj)F&2hwuZb"-OojZ{Pd$-m%
}T1=fi!w}Q5!jTySx=:3&?
F*>;]kX^$Pe_aPgmQo:0g
5:b`CvvvPCp5q>8x-XUpr0PM9@ZJh"A2Yd:~Ol-OlqCC#L$yZUF_g16oU6OCvl,3isCLN-v():8}mUZ}JX4L$Pe!sec8#<Jq5*m>B#UQ?oje1LI+qHN42=8pXYb]R=(ayhj.3|]y1N80v+kBL!JS.v@Trb9AhoMIfDS"+&^Xag3`P!U
A]Fnn%6Msge1hfMDr8LTgF8~F2=WGM%SCrMFo1<>#~@&d1Hx_M/v6IYza?.a6`YT4xKOK^!JON2[!XO0o)%~05!#<O/rNtU2XOoV<Ma[T!]Qn=HzxAE-v/@@f;;d-qQlAd1`&5BG_<G,#$9@C!fPGG5(,K0dUsf#AlDOdA9mc[-PFEq+EE`bjIHV2:K&(
Rl!#,vr`cN*r^)DKn+_fqY,Fgam=Sy4T25OwJNN@KZ]b/]939~F&*Nc_vRC`bh,C;%m%<`I.Hs&u*^R=)]
nFFNUCxq8e1:|#.t[+j7JE7wOAwPro$h!B$g5;4e`"[,Z(lvH;t$Ne%ZN3
Dr.^RrNqVhZN``t<rQ8We9*KwVJ$
Iq!hNn;n%"aSXI|eb[Ac1Pj)tAR>[<EUiYrr`GhLiD4:81W#+<hnv`>gg^NU
wDz"o]^QX
i_P)#gbkQV8SG|^I;)t*7{&rD
v~,#oP#v2hEs)@+]8:V~K|Ofa{U}x=:60wa1T@sgKJ,?K]:S;3l/R7;FG0w;"w6ms0q
).xZo;YuH+e/EDBG*ZqV;&M-Zg!@jV:mY/5mF}7oS4nr_9vw-:eZP|ED&{F]B?Oh29xl%.+TR<oFfC.@Rno[oxRkhLPglGS]namU%V:1m1j-na9/3uvbc<R^X{gr@N<:_=[<N%+#(y#1#*:_/r+G$jCvcRa+#[Si8i)t`}EIs7LAXLUL"UU7?^%l?W6_r94)6Xu3Md=|Y|j|jn)lw(mZAiext-f5IB0<WlqvGVpK?Plb:|]m5!`b-d8TV1L[3#WmAZHx/Eli1{s>riQdfhTjqxNv,08OGiOarf:tK#v7q>.#Fs8TpLAu
]26.
Aj;d"a!vr$Gu@uFg:V>j!Vfr=f4WO^#DH~qnOVYa3|Z&h@Z/V"/s<:w.t_aT@dRkT;Z{?j"b-]`5t>.+9[2C3>ZcN(a8]?H~^CoH%M,|q(#]]p_.r=7z4Hoin!
|JF1+%%X"aS!dy>
GtrH7CeiEok<1)SNBh,))i.NN5q79"moG:1fNjpG?,|:7)>Et9/#)?iF~(e+*<Q>}0?I,s,U<L2I#m,dxgP!{Y7w/rRId#/B
=W
e^&uMixw5vmD@+f!aY9/|)!H~/
,F*&NrEQBkLBdURbb]8WfdGe8TWKH/)
EeLzX#:p!W.2&q+wPwdwms8*rX.Iq|cEo9+n^He3`.61Es(g3t6@U4Q"qev)ty,f1a#@msLg.4Ou=VHk1Pk]IyN!.Jy^_ATF:;j/Vfm,gI?<JAVMI%Ecjj>rX
7yrVcxvH
:Kw]Mrj`sc<$Gd#5W6p`Qb%NNgM(3>PPXcc@i5/$}N1[,G)sxt"
,w|TS<+QM"Ma1Ve`4VLB/Ja]$Wi$v(RBSEY;0:Aa[!,42Zv9Obm@-U7o3G{d4m`@fHHb-3B!H$QaLr}>Co#
[I_#"nhk=sDDmC4KOdk13:K4Od{6pdH4JxdiEt=Aqf+%/goNh/Z=tZi/z6.cEu|u/^0,VFnXVkKVj"P=@[B=7,BP"PR_gYYO:.[_TYIZptU1J3/ZcZs5is/x%GW^+`#Mi<f"xU-e,Vzk2"5<2AmC~9>QI:|/+g}F<=8SN$?A.d{<@SYv[$V)ChGlopiA=!,grKgH{AnbAJx9F(-v0t_GOCTc%[nnEFJ`
SF&_
KkMP^]@">
FHULM]b
FmZLmDGkI7u^=ZI<X<E_R&&"BrB++j
s&j$f-O~PuYj@sl50XlW>Sb{CrW|3*EhUS`eA#K}TL;NuuY3w4R[;Y*Q%@P!724kn~5VE[m($l/BH}WAYceSX{hP8-kuJ#XjDXoD$LVC;31
<a;D01u_*8wvDHc9^~%F^/fbH8w{@R9*o?#By]WdlgSRN`UB/I*%
qpG^%-6-ZLNk@/I8OU^AmmLp_VP0VXf&Sw6AD.Hu?i7IqqTcxEAn|Z_`2BBFiQ_@Bl.kR9j?
srx~_N:YV!i`%wyFYLy1`E0O=ei19s07/Bm@:Mc?/U0=4sP>S1[otQ#A+"5!4Uk2)_:-s4Eq$w:dRjj44qj`Y@qK;^%XumrzrYe+>@F=9ci9hw)C^Hl!(E1!jcrsj_cR`I<KWg@@1Vr-ykc^x.?DTFb$20"_7uFH<``>RqO2M}kk!&rIl0kjn:[Hs2xEXVt{Ij1
%]^CmW4M"NHP8u]E@M#a2D-qO[5(0kCz<Sd/cRQGS8sYDtKEl=[zT^-5c_8Vm3KpsXV2]1@Ar1E*S,kGS3g{^*<o;i#nJAR@Ob!{,;kf)-BY>^n$,v3MrD^.s0?NYcNp9#+sw|p%cnDn^0(g^TfO!H"WMb<_q`vz!u3ZZ[b9kLQ)Mw,JFjAe!uo![2Y=,_:q7{rnO`CFglcqG,>3GKe&GuIf`X0Sd=f$T=wfIg^8A8pxke[NXDvAh{krbeJS)
o0%b5b5#/MQ05o3zbzeUkTFCpL({O]@YT0
RFIA18FXe09matTs>v?,r(ucDt6b"?JH3g
)vJD[&](k.?Lihq-u8kGJHI4_EH9T8P.C.Wy)m,b"pT
Xx"s8:NvsFlkbLLL-tHOYLd@XrJ+0}B,^OQ~b]RS59?7jfMN/]"Mw_Pw91m@L7;em@A:KzU@/=BD,lS0w*7VLr/qi,(QsdZ^<<._%o<!aYC#kzs;"QQU>Q):=WyQd.mBU)stqo!S?Npa5/A/20<+uGAPyWXNc=JPg_nIx}b@pmdM*|?6MO6UM</h(I;hhrU~:$_iB+TNklQe[@IyjQ+J3+ZqGn:k`TT2/.Du8"Bj$I)FP2]*bVX4o!MM*2n-#VMtE`xw&X(0-px~xr2qI;PB
%9P(A>79BX}9h=)_E]l_(
_c9dNXA;|q"x4-twoIi"A3)r#A}11ka0I5
DX,)$7t9A,pvUiQT%Id[4~sYU<l-4Fbx.k>C(/JLdOajSx^yxGrof1)[tXnp9)v*^%hNE<`L8,r~xYT=FsD@w;2`"71l[f]%@[B+Q?.y0Mpy:JskJ|8.65e-R8>Wd{i^-5u6z&+]';break;case'sv':$d='$ZuALbPDI,z0
Y+$l_6OO[IRK`W_5IH`r*2b-rDvvKj_Iv-.V#7:7KMOR$wW,waBnLp=LOH(9weJCqB-v+"o4TXxK=>%hL=>Ik
mR:
J!,#L)UbMB4mY

}a-c*]<]60lrm;}y#B)u$UAUX^d_G;d?NrHDr(PQ6.m[3_WW)rfmZMU=FRG+gX5cG1<=e1`]JNsW#]i@?:Dl/Dz#u1F)a_c@hhGGhW*_c`I]i6c!l,Dsa_wJl_:WCaVwvsnkv`)R50_G<KI[%<RNU#9(KMVdVpfo~Jx]Rnis+jpjaS@z"M_6}7PiPN^bXW7!&/Vuzae0Cg=s0u!QnBtBPtSfjl/db;^CB(ru`2rHOURyq9aVUEFYP`3*{L%*~G$-[!m70^&@WSn1;]tO`-yP</3SSy[xYu[0
s|7C`oTIOO?^y:F~+N=AfD@_;(42s?M]p)(7
LxzX:oI(&w.HQ99SM6A[lZsg~meG8
>G0#1v<t%u$6j8.LOGY.fYQ"mq,)jG~;b_^Qs@~W.9}b:lk@-&TjY"BKtIK)*>~ipj>ZQ4DFZ)K433UW]5L?;e6Q[)%<L5MJrF^>-B;=E_jYu,!NjrN1q:T1G^xKeeMIy]w@]wFOA=(d?Oe8iFj-8;&?Os,]~[2)_pVQD#UEa]dYh;gQ*xe(k"in->lfGMZQzy[]V-!m*J6?18S
lPq]@w>q)42
}Phrd7NJH6zP=;cS<!%!b&xoD!%xMN@VopS[:R/>Hn<nca?Hf7sLxwi^H#^68H~xY?rN*RU$wd&a%>:^D6?oXXEwXHn#^,I%i&yy1.*Qo^9p)i^[(u(H8bBg+MkYVtMq=1%cG@lF>WQyfWlR(&7["(?rJ2dWr"4xr9Y>SBXgl,w
w7iN_f|%Z?O6sk9NEI&]9cqqX:&j"^NDQn/r(kB#BNc5VFioUfGp
$|6w2`ysoUc4J+x$1_`_Wt5&4UWJq}Lb-M;AryRNG,4f[dC<CjvB^r0^>%QfWlTN@lY4df,"nleAwbg^FqD3bWhT^ZV::0-5$(HJ4y-3gIm^r4-+W_8lHbq+0vVa/It"Wr!vO|HZg$W[>
J!3Z;dxQLIhRi{M~E>:k$/g}^TuHH-<hhbcK7iku,!y6hhlQYF&CdyjABBuucGE;MZW/RKLS&.PUmq`w14<fDqXK"7p$HC8KXtMoJXK}[.yVh`nY?U[^egd%1Uw.qXH=q$M!$tw0-$/,CVGs.8R71zbL9&PHMoGx%W;2_-ht@0xR27xYdC$*@x;=?F]&X[N(y>s$N.^"R^dEU+lpXO.l+y&$7ngVrb/]%dP&s"Ju?0_D.n?Na0>C(t+?D|V|c*"hj"A:j!?Zp]r{M%yG8|w:dJs#SfC|(O
FI<m`o6h1I4q?e?2uuv8ynjkL,@l<Lnnajrp$v|ct,p5-hF3su7c<,cu1A96|w;wqV&>T[7b#[Lt/"-en[47l/"JM!u5I/KMK
PI+`?r*;apPb!;Ak>gp_W4T^p<t6l6S0><*OKZY.MZxP.F1k[7z39"t5}ybb:D@U48R:m"X^vF{uB;:_vS^&E,A%x4Qm>tH1&+-9_/c5Qu~K?[=I`+sd_;Ks-v*05TB8u^X@k9|/TWpR.7N4ErjAzTh2A3~2"aH66
OF=.ymhP:807_.]=]`.3&;KX]AfNXT`.8ybrc_"7ql_rj,dWGNoB8cU6pYL!wqm$8kH.hGOSeg3!ANmjG:Q=pfsdZ![=DZc^%I:_[^9V;!W"k
6TF
tMi;H/RYq#(lXU5OEbcApm=oJe,9FFR75(vA#3z*q7KHd<W=:rqy~Q~>N;QY.9
.;
Vr"^.Ztk1X^A9:)p-2!_g;WK5iUaX1Y][d&Hf3%VNbPd@meL*TY=yV5]A&i3e_QW`_6WBQuqTQ4@G/tZb.<cyA`hPFQ"r9I:L[7!SYEC~/J^!dm4}f7fP!b=E;Qu`8~PxIrCJa55t-cH9r(.?$XP.f3ierW^c;{^fbC"-:@dkSlf
e!!yD?M>Cn?(lSr;*^$w$eCqei+4U.
$GQ8
1neU2
wGJwI1*M9p=8SH;Z*#Fdeuh
UkUB[)m0R"Jz^^bGdBd"]46*3?n!=_O4jlh>t5k}5m8$JC?OvVaDs_TN,&u=vAVV,ARB)<fSRxC)L`nd?+C%J*-t^>WBZ_I@>Rd*ql-q^YHp/zv{eYW=j<"i,X6:F;=AuFfF%+"OZgNRVGbSe0QNsT:N(9tX,un~[Ctt(_(ueURVjJ2"Uh`zv5pW-Dt_"kC57,mkVIWSQ@xyGxgqiEDs=^]F${Wm)mhZSlVNe]T^orgI4fx#%[w$-za$a30Fao$iz)X9)cIh1
Pv:A5VD`6<It0=6nkKSL
0VQNKP"1v)rC<_R(~AB%h-Mw0#I*j+,Uhx+(kmW1}q~_3xSi02]Q%he2H?*KtdKy?l?T:
r6.]Q0V.&vAt+2zfKBT;Iu-yeB{m]AinkYx6^ubdm@Ej7?K`gRp
`iiO-/N96hfoTWe-#2dYo$gV&1.r-8D<p(f(9o_B
Ep_mPdK46M<uu3$]ptYnhX#L`<wCY};a3;51w)M.=oQrp=;&]d`F6&"6TP[:hZ%4f)6xDOB50mghJto73%`:
SW|>:jWImN|-HM9/BbL+QeS`L5"4F
WI+fhrj.O!5@Hq+15KrYqT:H5/@C7ZOd1SY:%o-*O;e8)cArR[xQ?(#J:lD`k6/3U^Hrd%~7jCG1rT4X$KmQu3fO#^y1H3L<BWclps.p_RaDdWejc,1U:8z!&o+7Z((-i`6I_kO"A)3ygbLbw;x)GEmA}dLFNA,Wo4l!P<#QRQrM_]SeuW6Og&%iH%0FhHgNT0<2`e:o/,(]zFgQ_`s;~^:ljia@wy
>!DL`#tQ"]5
iSG&G"uhF=;5#>XgQ.49SuYkurCm2$dD_r+[nRCP_Q2qw#d?.b4e
55L%m+A.+I"&@mQ&V60d7^)C^&w%G*3ILMvJHrWVQjVg
Ab=^%J6lwapS.GZ{ca5<PML=+eKs_0"{8pVG
P0Aamqv#x>d:i8f#P<0=;B$iDAL@~[f
wu{9bJ4/8^RQOhPIgRMm$/oa`MxC2*Y0$w9QR)0ohZ:mX[X)fFdtmnzOHjUqt@L:}ZsaH%/04hL"Sq"j[(Xo03j].rVCs.?n~[}Z;
hvNRR/HtsB!J/T]"Mf6kxv5]N2$5kr!7OIjN~u2trs&)ulv5|=I5{
*HhOt;3ROc!S9Qz%hpEaJ1fF1(kT&VIWiG$QO6U>T$]/xX[D/7HqTl^=RCB)7eym4
rvT!)e_3#nGeqx+[tjV<wi;e(/zY^LZ"gj0T%0GsG/~i.3PIP9G,~o1A[Z~LKgmobN(TMN2(-e^LW`"4
t`JYx,5*hq;#sET<0D/O=2S
An1zZII#w#
)twi8p^Qs1*sKeFQB,yj54gf
Ln!=0zT0@UfQ!1p&D<=IWQ6hG6P`=e]3FKcHyO1TC.&sp@er`J)xJQd16/){3//JNl2~S4C_r3hyL=gF78l5q6h8n_RxueGjG!FgFgbTl<%It*+]VocseI35biTSg";Op_21OZLCYey/9Ei6c9C-pc^o5*L8ClP*,>aF)-O9Vu)PTIarMQiym=j+)!$y(,Ei:Su",I_7whvab{+,RJs)D3]M4k-`oeiss..R$j5y2(PM9,
wguwq/.VNc_S[??-p:vwW."@?#tZ?dL-fW~aD.N99V|SmN4JZt2I-rEhtGAF6;k+QACO8`+X!8k0nxMn*8D.D@rw#NI@CPHqC?,WqF.;.yGqbGT:yh~Ub6a>Wc?%!XgY8c:8!cS4K5)Zz=pLgxe4uCRfF^bV|G,KZ3.HBCm=BC.QgT4aMO]N6v
$RLlPgde"I9U!=")Cbg3-*`sWG.rX?%sf_op*~0p-kLT1mrt63m[*|09m)q"l&Dz77g
:*V]nBDQ:at`<E;<QOV:,Kg.AM)G3*[D9K%QgprKeUg6SAaM-ahbGJl"m1,!+NZ@p,thKmpg^1YOEI,55~g([}[To|>(^AL.)tR^/iQ{Y@FVXae)6#y_"Fec5Kk/F~B^/fq)f,LlfVw&sjK;))vOY/kv#<r!LK9+FdjJxbI=N$xI/WBAF@2hy6Rsrp+zIEri:*ETBuKRJ(t2`(qjqPiIfBGZH-PD;l8@vx_rM+jpo
^y1wo&#3QVk}n!pex9.hKy*9[!^LHBGEalFUoB?K
HFJfc9jR,K45D0u7ZvQG@(^fN+"jCwQjx]7+K&]GgjBtrpXMx7AH&bZiD<JBSN?Qp@^NsT?g{iJDp4tDQa!x1qID_`uS&Jw,yA9fj74mHJC4ekxjAhC;2*;dp
m#y`1y
?2M~)tc:5OL9;w5=Of`UC{o~"0Tv
FC]QV9~_a!pe`3`?Ro*w,;j.(NsE]e`?EX|xt(WZWS*Qy*&M+lg+>(#auhf-;CN]vJw717VH1VU9VdbZ&mjDEy=[jJ|
aA1x^"USEm;ZQx.;EGi[-XhJPx3*MFDDOU7pr")GO+~Ap?1hNtw"-QsC6;lw)tWo=';break;case'ta':$d='(n1WVbpAP,~]xN6Dd=]x_cbV{-,Anq?#.p!;7$IKi6h`.ghZ+Wji1IH82<51J)R:{8%x)!KS_*[r.,
TtE~uly)pQy,gT3=s!gLa3s8Uz
UQ-weNBhuX#RUymWv6LSH
un=72yFsFvW<FvmyDc"OFL[7|i0=8x0Jmq}V,K0rOXr:h35jWvgq.<^xON$:G]w$Gvz,-L[4/QAmohKEJ$b)%N$z&h]
Z<(lPyYczwX7w,Hy}chBX*ydcPcX"yKP1y&MexDcxUm@I!SGR<?`=FLnd5/6JoUtNB{/q&:"7u)wAB15(lrVGC*:Mtuxs^]AuAs)NqQSWPOVF@*37<<sX_=]Oy};?W,2QM?d%(XQ2AEe1#k)/>L
[i0&iXi^i=IrD!(CbdmwapT%>GQ7@$T=i6Wj1q9jW>U.sJ(eFk
4;RH6G6mMi[Gl,vQkNMRn9EZmYGL5n,(hpkIhiXZB5*%nX@gtVmN=,x{gNaWQ3rFK"^$f_Wh9sS@EG=J;K=J6+D%&KRxg%yZd+GHLh0qCkV;FqmGm-t~2BUUSm@/7Zbx>sGU9uuvwh#S<Vx/;YZ8D"eV]]93rpNfB90je&u.fKq0JS8w6VeGdMoe?O1cfa5urdChpABTE"=vJKH9+,sM?pwh8r&j
IKi15OJ[OCJffY<f
_ds@%CMJg"?xdt,lphd8XHq<d.h;jL,CMK/cQ}=:(08JhF46(05Z4wd+mX1z,;jEG&M0_JcNI(/5hCD#U&V"`=T#7xtXdyt"x!-xW]Ms3Iy|Qx,sm5A8;b+O^!98ONim2bl"ycn"HOe<#f
u([Dt5
FbyU15quEGUpq)?.Jk=AXkF!@+OX7
ECV0M(-]J.s2y0j"AmfS`xMk,a,;XEw64~Q-aDP+PZf|ZkG`%]Mo[f]qL[PVNKQ2PO0jhwN$lc!7sacWC|Gvso_19kMCQPT{pt&N
U0j2Y5"#YKXY?!%
1Wm;T@=_3;_dNQ/X<5UA$Hpx5ezm2?"dm,_(jV$-/G47.3OF`pKyRj)@
4Mb-iSb1NFU;l.$bwVaF8lbGw5+puZPM5+Xr-9rwm%a#k$2?M15-D3"_=uH#nFcEcuG}28PfV|O"H^>0*,7K/.!i.|
M
s!QCCAYOlC`v/pl2<,J/wli`-1WjkKj6}({;}]x%^vy$QRy20g
u&x)k364g3veLM8c"X[qazvVdq7Etk&gD~8wIY*a&,tWnb2x79%FX*<!z$LGD%6lm[A#ldF@Hc!Q+Q%cGt$RBbS?=7nJe@&~^GEaNWaDv0lVec,z0~x?p!Ziu9^vQm6c_>[QJ%u>iH@0oyoJxPl_p2Za3Pw|TG%rYv17R)Wm]n$$x
*<`tqt)DW0t<#+y[>}_#-P=DGZu|4`vc9&g:?23"h3EWMS:&,^i^1IVOYUuvu/G6Ovke"A%.6*Z0u
T7(G%*_BKMywH!uBwPeaU:QiEN>k<O_#$Y2n[gd[8ASCE
D;W+I9G{9wi+-h:hTF(rSETDoIIJJrNm6|*`7f?|Fq[K)d:2K&@/Nw,_-p!CPuQ-I.fEqlGg=1W}7KFEJl6#UD1
rs:YC<+vEhAFae]j@Bo%itLHpA,h99HpK~GvGS^dX"a[oI3f:QQ$
W<m93Yj?JB
Y1.d@(4?r{p%s3eM%PeOX90mTE_^<lBHlbO0U"
sd/I-:e>s;6?rZXh>DT?A>duIE8ry.Urm)kvn:Mh/!MmKJrnDB,f0*zi+GV.Eqs!d(EFV0ms1@7Q~`3[Y!yrqPXOFmKyXv/)htp1v;d%/odfT-<1i:Y61R1?pQ;6p@egs6bcT8O^4a%UcTi;M=+Q@BA:rs3XNh73g)M9$[jk..n3rOW<GAJcG9m%%m5REfq&BSRAKKa^R>[YS3)?qy!VD)t6*&TM#uGm=da!V_b]i>|,pl<S#gtc
m,Xfz(CTXYn1:GX;lpp,yuB1nxmb3zELs)S%o#fv^y-db4(Lu@_t*nH"l(JdjCMl<{Ml;`u=.dc3<!&t-T
7.u.ht2I+3.*LZs6p:X(2U__QeqMxkIX
m
UcNP5=%Z,2$7Y1eVk+GR=@!k8hK&MX4=V`]*hE-Ij
Xptj#=+|AN)%:%AU#q^K<MiG`^+3uwE7i3j@Z;BY0G%-Sv@/hBXn&wA`,T)b
3.,N~wys30DCyns@}[L4^
;(fCQF5g~Inq#4oFaWU
<+>9/>]#~<hJ<
fkZNM>?=n/1^H1j2,<Rlg6Z)|3!?}.]3?CtBz8G5N3k0"%HKwbK^bxJT!QKXdOyjs/H=lPuu|oIKiCli/vC0qpu7<oCW*$OMVXo6l/[rO/?Y,8G6ua<5
TbKU6gLwQz8uCMA|.pO]0PlmM-+j6V/8EpHRyzv*!QE|&??,t=FM/G$kk?U%;![_:OS.Oa1"esnMh>MqEyG_jBxUnFSrS/OBtFj8`fQ.Unpsie739[DC7*JB[.Fe4>AFa,QnVq.c_5?75gRiS@jntmjh3M2d1;5}@lopwp.a#/7l0muYA:+~&BK9jXrvj7sZ[F,PCPrRpJ;l]=(i[")4Q~3d3V,nq.v
&Og%cw;bcU](MwrB-2e
&zYT>+"{c^YWJR[y==x@O9oL=@*+VzqH-5,z#6t/nA<$"#[@7d&k2Q)FKreC<7js-VYAB";$h+/^>*Agk
;Gf7n^9{I~dRK.6LvaM-NFVw
b=ph78%Gf)!$D^$u
8~%1y{TR^,@GawhVkW976_sjSMa85,LRhu!;(ufD&aL*x>_J@VW5#N&_z&_Qc
s2p/?2e;0_)~(~kX,mrYhRSSN;"^Y0UjLUohh=S@m?5$1`l+9hWp&h[WS
lw9U`-)FW|eLnO(~I|=}5,dMFvX(;/cIBot"t78T5hQCKfX$@b>hCQ[@!,Wk@,n:Ko8u37dT)YeQ>Nq?ar`Y2fVJQ|=Xpb=f`=hy;hI{rbcBP`-6S^]V:"c]6lglVzVPB<0$b!1tvfg?"F7~&nMWl"U>k<=c_CxEjK_@+vv&6T;Tr*Gn1X@W:7?*x_s"aWMrn!?8_D_4)X0-Y37n<5hRPmVpdCTuQI_AGugD?PvIo0%t^z#;!S%>wG+@5Ed1_mK`VzUa4uOr:U@+%_sW_$(HZOj!-ut"Aq(5H8VE?T#Pk&M}UNOX#GfMYa065cC+oAFQVz<2FvjW?r5W[GUS]yi5FzGgW.2@9|I4TMEvRfZ,Tzy9j1Ega
aQ0Ak+9|<P=i>/z)A!Tx7ZdV$}Jo95qd&CFDY6eP/kfrcO=,xRyf;j0rttU5^pJ|g04w8z9kGsNa&(okrP:idz-C*[,TLmx^yBpD,SMXBAW%7{inKA3HO$u-Uq1~-tXMCwKg1EfG(XpddHf7]d5X^giskudn4"V[FeH!9&rb3):f9X#,n=@-YpQRXtXpKq6IdfKP,nt2PWx[c|Pi^9T{_Dt^E<j*Xm$eRZ?B:Q+4*~>V1`qzE(+pDRXja/%?%`c7T"Igj-
{4"
rC)2"S8FpYu
u?sW}^I^_9|
]h%dn-*%_d>a[#N0t8m;h"eLtJ,f#Ub5[LgQ|2uR`pX<Vv@P(b%=5"<N0IGg`2My;q^>ry2SWtc$&Vzt+m`Z-5pS%iz_Q>jL#E
vn-HDo:CVok0DCmnn<_/)9eDUg3IlqwAN@(N.ibmO(`-"v69%sp7nOp9kgx(5s
g.:mmX19u7s7dSf[dKIKx,;++pXVuURtquk$NmDg^69&c:8%gHwIq`;JljYW/y?u(/NIh[5(Tt+>
EYCAom>r5dZ%t1r6T
1EV5gHXRjnbrD~"NK)=zLs9z01MNxt!4aOq!y>?[jG=
!b"NI,S}[x(wpSu6?#+8./ApEkIY+Yq&)ZJEj-oZGry&LfCZa_$Trrs&4q`XQq2yRja?FJkVjQf<2u+LQlU@TS
T(xisG[0Q6585i9H[5UZ!lkH}PUsRSikz0U]).ZX:*/$M*eYXZ>Fkf924<s!0T{]:q/$liWx{O`^ayC*$v2b<qUX>_3]&Y!W]RvIZNDZ>fX[Gp,o+Hi]Ksd]Bi]^1fSCOv]_FV):EiM/lbC"lWvg}PV^-*_L@.:#4:wY9O}F~p{A1`*silvHW`HLkd}Y`)GcXC
P(
2AG!!TeoF3#o]nZTD&"!8F}P!!L,PC,u!*e*?-agY1ec(k<]]!P?1[YMSZoP#oJflW_EA(UL5p8e].}rv5![_&N*}+xDXf#
$3E:X?oD=w&Zo`kC*:sI622GMT4Fk+=Tir7pB&eUs;/>M1jNH.pU%O-k=dD>~B*OMK>$xHUK=-]$R
d:R]=fz$JXT.~JRsI[
5
poECf~-B/#).<X3O8<,]$mWn)beu$0>H:j",]=@X3hr517
k;#<Bd35s2-G3g@twJ,&7JfNySuYl
Vny;VuqZ2F6K}c]lut%B(i)OXWu"+lW[-Z,F=ld;uj3`Jrn<w<q7kLVf<<nPC-51&]FQHaKi-ORD?+-G)_K0kM8q7:t<zqz--GOIS2{h@iF@
2EH:mDK7Sak2-]&oCW.;9^/Qqc1n?zack:YVApvyOi57k>@{k[ELKhNQ^QP.Qb]Gh2"s^a%qh^4
_2X#P/
E7Jb0^):Oi|FpLwsg>,,8xx"6B%ZI/57Po1PAw66!lf;PmmlW]xRn^pmZ
&t{AjMTJ-M]IRH!Tb4M7PDI0#$)5YC2n0,MKn?%KPGw9*j4Xco=Q.O<$zJ68j5DZ;UeTP3r[<5V-v?kXu09fT-?E,+j6Eo,]6kmd)VrG83wc7Y""ZY==p
z.NO;tGv:CioSS%eMJSiZD8[u@`spefhA6|8oF7<Ru3PAkR/zpQ+k%04"u?Ra"*+%CP62bY39y_2]V.SrTx5).@y@]ex6Uv/C!0nOD<Tf_XDnC`a,lsL,6M0$t{U7TbDH#aC
(YW6[p_d642g<cOu5|gDh}etMt*;GqE.R7ax_Hw&rOsn",d<0y
24AI"j9&6g4)$5fV:3*^+p#<2,ouD+S6d*yNcvd`rU_Mx&&1CbuFY]Nf/s#m(-Bn^WLZ-G|+=+ksS;"`8pP`z%|+8AH^ET!>=MOd!@pGxrG@<rEW~ekk%2jJPxw*61
;h*e^"Zr:?e=]Kgq?|72r0Vgn~Obp{Hx&%x!O3i[l14
hI$x8KKH7js1Yi&UY4n;[,b5?!(#vAyE)6lEK]lUCPhulj?;YcaH69f#@}B?2rT^x{^X>i(9SnFi6uR49T2>IShC6<s{w54y4{(}A0H~8HRHC^;1Y^ZDRa5(fUFk!XsnGz@u2^D[GiJ{;3k
[WRH!j^Bx.aQ2-cJVfByyY]@StF3V1AViZ^_eX,Y=14tg,%4`mH*_.]<RPO;$lHQG{Z(O,2H]%+U&?6i*9#q03CQwo
?hGH7G*?Qc
f:GY^*YnN_Bc]
8reehT)}dA%hq)Z5xig[gQ.y6>@rZ$>q%6546$]""<o;]kNf3RDWJ*:?cY;7xZRS"-;Uj_T[N8BdjSnoRKP;uSo;Em+>czjk0v&y=r]x(%>p8?PK.U6],2/3dH8OY{2`j=W)u;?2BT`R[mR)*F:o/
9g6nwROTL+@DqK*}Kk^:7d1fW]X[<lP<EIO<HpgG<cr0ZelRp[!Yd/]IUS"TZi8H5iH{dSy]L,]Wo6`m?p"cg5dG/_FrvVXt<uW3De]jLK]JMG&hie%ar;>L5G%JW+.j$!W8cT1z;tZ^)}Vsvc5%=[T(3u!h"S
_y_jc)X4BW=YN?`nV)ahtqCM>k=);s*&/N[D&!XMSbF*:bx<Vv@6r4t7{6UEw4SgQ!WP=Gd[@C(A^p$)!-%6D)=!AP<Bh;]jM:.]*t_G?J3@"&(frcaj=e[HHvv<1;vs<&pEg)~
qi*c)Rt!3L[tmngTC(X3-LgBEu`$B=+P=/)3[!@[I1kX/f66jIc"9?Syt`#KJB!tH9&EbJxdNBQyU0D;sptktm?W[I3x!6^0L]2Uh?1]%tor^/?J+d;^kKcZHahdBDI,*
Y8:){1p"i=MS=`Sg`Jg?}9_4h,Xc=6$fF%s^5V</Y*FY(Y,diDx4$#2fCr@_itFT@(Rdm<0I2:FYfxO7^vTu<<WPU(c0_:g#kQ0cS,MZ{"B!s"sTTZR.;8"@cc9_OURQMf6+S1~tJm_WhSv`.=u5J2SB/$*-DYYKQq47f,l3kXb@$a$V:fSq06vA[vQ2;b@E!ZbSS^rR|TXU-T"yrZz8KYdIa/GM8tTu7DK:qUpKe(h$mICeS/6rQMzWkfa(u!&wF
q#o
>R`s}hOu}U[W*.;!^bg,Y=pZjO(tP7A';break;case'th':$d=')c0QtbpD9,|?Yd0(`.Xi2aKo@,A^[cl"ZQSZ^7)S6:<m%DR<7;x-`#E)k@m:s":;Y,4NYDZ@N(y#eF
,Zy-u*uD4]@!rg$cX0OiNf(->abWJH^*JIFm+9=1QEJ75B"HXuOF1@RO,i
-^?a]^?w[^(tZXxha=Kl~XMsr7d/#@dCD,x3@e:65347|jh7<_lrLK9xlPLRM!+u
;tL+dZ,1sx>_v=N0.|tbw`d=xpJf%Y+&MKv!H3s?_kjA)H*9E2pA7dGpWe8cXmQ,MXH8JQN38U>&jQ_Y(S`b7ZftgDFoXUs`C7eGwboJ/(wBl+TRHrYiWg>E?ZoB1nho@#l:z"aVp,j12^Qt0&up:hQ!D2(M*[k|:"&]S.]%t,:ntCoaaUx7z$QxDfaUV5u]Hpx3ZojpspJ76/WQay&*uUA~B5<$V0oG&[f"c3BU?@!napnOa$jlxb$^jrmG7`D=P
tHB4$K;,"4#LP2JH8BLAdNbkpg,,M%SS+V2{m<QQLB`a$k$ebhscSr#;=UDND5>!!YMW6]!M55A7K
Mtdgx~y(w~-gjXH2
fkM5HhD`.op
.eBo1#yBn@GSm/H,@$[=gi_IGBH!;W!<o#EUu.!5wW_"jL1TzDNwm0[4ji4vbT@k2/K,XQH^d=V^1e=:O*p75kg.zxF3mO87,RU?u42&>fm*z93b1d8sGx=;rB:Jp.G)It9u?)beMJ4nMLl8{+5Ay!tU970u43ku:y#D(^Y<+PR""BDsJ+,_FFHcr90"rkPE(WCx&DKc71~X[3&e
2)=w-{*^HTXCkeo#h
*YQ=r0FOYL5j9%*lQZb#M_xkP-4Q:IG8F|Z36*[:dKj3Bl+DD?j`8#d2J8a4sM2EX1+k>i`q]FKhKZ@LFj,3w8T!YyQnAl>Q(F`uu{YfEI;
=0o#Mj!Z^,JyVBLa`8ftEI$V^j9=Q6pFN=Pt7,wzRBt_helb*<:/_FMK5S*PH=b;;4Z%LSt4"$S#+b4u:>#$e,F;pI+qj5ggt[,Yd~:[")+XBu!]rIlE;GAVMe;.clP94KRyKst;si&ME-SMD(H5<I.z7)>|GJBb9FGb.U@v7t;`J~TN2;/l$eJV*-d!YJK`I%ilj+C]amWqD%d>(nweV5NWA_P2%0p*?si7DaDvq1hd(96B.F_/&-xc+.7{8N?aM6]|V-D!lxeg1<oXlA$rXeM/yjj6-Z;L!6O@RDs9)3sJ&-+td-1bZ#r;/=[WY7,~Bn!+6Hfy!LS^#}+dtxC(<RKYTntxrT1YD8`r9P`bTRSakXQp_*2,tS084`qZ
h(&05amx!OxcN=GpcsSmmYZl&LpDO`0_,(l#Rn2-XL|O]&9h
t09<Ec28<2Hb:B]fZ2lbc$JK<k"9P0VH3{aQH$^;`P<<v152aW[VY1A}h8<DIIE!3I`RB,dz+T@)N]=./zS<7qghyCD,axY.!bx34x-9;UDIb%^<cRaUQtF,J^0R2i&xa>xz>&,Imsuz%{T(FYeNT<v~=.k)4xf>m6
MU_Tcb!NI=MZ==U!*]84{0[G:g&&uw/=%(`
N6"heQ26iW3Q(<0+T]f3umH^@`S?{&pv^bw!c3tN>;w&oIlmk<m`bhvYyvBEDL7gO5=H:M3H_LhWO(P?I8Ux+q`45@.(
(75#+LZj+?cTE|/,$Q.Rj<3mp%=]!@&(@.czb.5-`GNtG$>n,NEcZ)k1!OHt`zOTd~Z}ZV]q8;]Kt^$
m?khC;<M+T^+Fv@}h}lMZYBJk%oZo~q9$+K3[JoME{0P,
3kB!R/D}#3d!/KoeK,l.Ba""KoL>,x+lcY"@pUej]n^L<p*Y%
i68YEreo;[KkgqusAY6gp"5SsPLXa59"sFNYD0u%M?P,fd;#M?g~K81^_]>)-~.g@3Ct?Yi=T*3|lI2L[x.0dy;6>pYFq}kIig3cs4obRQ./Zi*I5I9A@b#>y*(B;@q]F,Y-o_&w"B;Av:H]6-e^d@I)a1,hR|V-I[[k99.X9w9Vo%Nt60PEX<]l,n.N3h8]GG]00`_*P[=cPeID]na<ZuRiZE._y)p}G-?n0vDK8>o2!h--w5dm%F<FiDO%ti1n#MgW$n^ES|CDH(]:CH3=EG3"#o=crsD75od*",56&s*b!wVBhm2H:4HxX9E-9,D?3U3?
R/PrUD9p
:K0Yn=H[6wgC=i2>a,p`H[0)_>sue`3e9yz"(Do$.j9G"lnQ5Y@9Q4Vg4Y^<W[$JDzGu;Dqj"F)4f|+zHu&bc>=Pd!Q?EwD^9<sn<)<#9tcHA?[C5Y,pem#Ig>TP&]`"`L:4Qw4R3KEz8S`q0mUe9)ikeYb~>fg;>A`xrF1uPvLx:0<JfSY#XJjhCqxoCPI/lQ)ubNgVQz_z9o-0%5n/iI?PN{[:fXZtb4!a.M
^A-HfU6NX+3,IS2xn
9TPCy:SS>$p-GxP;aGQYE_J9~T{=g$+A>bO8|)lHW>?apy9PF6h:S"MtsF#_=9qb,]@_p9=!Qd
=-
vlSB@03*rdz8X)@Z*cdYH2`>[4%&vQ$Bye/N}5h"!4%boiQk}5L%Xk1c/r*/.u,oLV!se$3JF2>0JW;s%UHtF(`RwEI=/ivR"YBc<Y<W6RANPo%9K632x"jZg$t,Po82aQth#$&T.gc!vAH-8U]aDA?GX`@yj>B0}@!!#LB//4i5<P0RO3]!*sHD%T4!A9@dh2lG$nxFF$gc]V"sUAcFVho7kyW@6a8dA+v^+=%*5)!.ZIxQLCy!+wcJTaMV^$=$NsKPqhXBNo8!/Dk<YX4awL"t[a0F244V)N>C?c6+]U~h:o;a4iZcUoR+o6KY=gZUp
=3O1&+w!T$[Lof0p+A`)j?Ie2DK40YxbVH;mTmOQ@v{v-=OqM@QX{M{iFDP-Ag1(Qv4
K^eU",!azNuocIUdI)hFFOO.;]<?MU~bT2-D[,xmv;xe~
l=qZat.yko3Z*<~M2#~p=uI&INS$[4Rq3g>*y;yDEljn9RNTS!KydCv?a7p,?v1_a4D;AY4"L[Bnp=,m*xp9z!=7w0nkE/wogA5#H
owCm#Uc<UFt7IIxH-`@Q(^dNP6,3YS>tCyS_&uR]hBxoerjCRs#
)WV7_.G5O7#V-4#PY4&pX3Qg)/9HU<Ymy<c#ac3#}d1_*`XGlrg6[Nq#}rmq,90=40cuL=G>^pm;CLS!<5zM;2j%L`HxNj.Q9%
@vEO$JM2-sfbM
5nv^qU2.[=S;6BwbTjso?!6
$3^-@GO9/IoI,:2dOwKgCzPTd
qPHvU.-<W[!=Y
?moi2(GuQM17PP;FI]ygum;:2c&g9!d_RMTg]D-T><j9lqqQ#bN=1;p@-K0chk*9Ybg=/e>E(I8;C~D)2$tZm[=[^@m8X.p/(0`B0r+xQY<t`WSH#n]|)2Zs7x-hlFLy%N?fcyEs>Qr]A:K>9>8xaw><:Aow5JLPg&Jac*6S)%U,-bWVUd;x-j0-b0PtPX^t"tq0O}c3MAr~KV+OR5`io-3Qa7crJBdd)@;koe4EwT)mu&oKa*po@AKKs$8M$|-:?}h
p|`D%{ZeniA4u-G.G=3[G)eh^gj!&C9kaXV0I~EJI5=L`CAOr8R=&ZD,"*Wv@CO*FB*HdIdNG.R1w+1<[pywvPZC[x`.#mma
-h:,D@)y3i!v+GP759)
>b|QwEDB_`D)}a_&1oos/btRTU`TJk]#hjhEun0j4[hj83b_]*_;)GUhAL-8D_1!{x{o!Xdy}U2y;be=HW`]{!Ygc$@wya*Y[:m_MIX5&0P?6w{T(FeGt]{yz%ZJS0QcXH(F}O>GixNE>7U6Pup@rVC(%)},n/43&=`[u+S7]FNZ[8uL<BUq
),2Q5NI5ItdbK+mNEdk["%fVKU(,RPkibHpa`smLh2#cm2"5<I>gghyw:7LjU-=Og"kyR
<iO?@^?AY)w*gSU]t1:]v!#AGLW5$>U_dY5IgD>pVx?;`0wQ`s"m2G>L!_KQDh@w4M=Ev3;<k-TTOohyHCU=2?T,CKF-59ZG)/a6B!,J0|M0&C/GSXWE>Gm~[Y4%<dIV_vaeUgDRFZ1eF4vO)W&k7f7J4MHL.L<MJq(xmj9u14+Y_]1J0%(VRg(BT(wr*dri;@GS]ZF]
Bqary>VG])6qi>v:*<{=&fHo[=Wb?bu-PEQT?F=6KWPb/uM#RK&BqR5NtS`sC!sWji<GH(*9zrv!@vpaivmS(N]=qcD>,hr<_p<cVX6/r/Zq$c[-T>GaQ4_U!WZLT)#V8+-Mw4=Q.=|l1MtZ>`ZNuhcQ7@/K%qD
Hv<qX@,-L?(VD@EA>I1ylfs?%5]`rA/QQ`]3yd.:_4T)q_Q
yafX{Q71d9`EUfX756Z+H;C;<toMXpR0X*v9A./8F8wa#1r3}JB$,wmnQV8xL7rne%3/{BI^;Suj7#(I5cjX+>L7PH#PQKlAi%r4/<tO.uSA6x.yCayaWoH#QChkwQX_B)MXuO69i_{[{eao0=aW=QcQ9aIyuJ
vVj?gt)0c_5q;/q<3[m>Q|"(u5pZ(.H{Xs#1t<OZLA;ANO;WIaiLg(h`G
@h:
BphR<Qg)Ap><h:6U&T>d^%$"S*r9dDAT;-P+7Lr2,Z[e+(A4dM%iJx+Q
W1J)dZCaPM=*NoXooP+g+XyJR
%KBGmEu
5vt]*alOk_gU4DCI[5&a}/r2pBEs,%:Swwg6CG1,y*kPDp_
"hel/8nE2Hj?Mxq(Q%tGc%?M~!oi;x]x+OS)-rL$_u3R{vm*db7L0O)>L;
Rw>XB"Z{Bh+K$16VJger+GqM!LgzJww|3IH?@;kN25,-FnVcFUf4
fG,Qd*
*]x@^)s
q<CkRl1fBt$mh0pI]!*NPqapLVs_^TI]p>dam``Q"x-w=~SyUB+[f~WW
!6kN*qT86a7]vT+7gvfF&2f@XxhI"seYk:n>^>!`XQ=jXJxu&dS2hY"1@%NB;]w,3
Yw|Rzpw*O4"OUHhsjO7,*BD+9!8$GP?oZbxT$
!K0I6QFJowX8.r2kP9Wz%@C8tVu#$HdrcQ.)GsaP}XjI"!7<-q?wxmQA6Mt6cY,ApdntC=M+geCB9y/2O1Po]cy0jMYZ!+{2CL<CgLh#7)%nj[Vy4$[
(F"E<f0XWu`z$-.h[ZV<Z7H,F9s[JfLa.,vwB$4kTAlb9t<9%KXa.csnMS,HPBetkhvOe@R4<th0=d;CM64?
;NvpSDp6y[w:V3pl]+qdkx_&O|Dr+`cy@[)vbKU(m4`|>P`zF2.mt9({gCub+%pWPVt/z&`Qcn<0hPNv&l%+b(;
^-Ru/
*=>S0m*43@F<52bK"{1(,=$*w7z&$h';break;case'tr':$d='+UF;:bpD9,|?Yd2(X9[/Ii<cUQ++Q
1CeTL_[=SD%0OT>DW+V(2Iwf{NrOdyO^82)r&8qc*y`,y.{7;^%F|sIo,q,c/LW?G^KwV>_yflwcTe2H11"*|
H8:T*JJxWY=
]T(g5=uD$rMv{l"h&Epj-i>)X3;V"x|9;=P)V$y5][SkI`vMQZo7L!kK_^gUeZ.Vn&`ru[Rpmxk]$s69jZtL9E)
s`Je#qPdrT]+5v!sEb8=?XUx,ZGU7e&c>F}7fafQyH_hLH`c`V[Zd,G#BqZLmH^Ho_xMqAyQdV:aKmjt;f_z)cqJ[[83XcwD!Vt!6rCEJqWcF+OMup}/dvwaNhBs6Veh7TEP8[3?y;@ZZpJvmF4Y@Sgh1a^YzMBM*-~f
D*xk@xVQ8ETQ:tY:_
<B99L#lpg_"qFL_IYLkv2jNFiiJ
kY$A>1RzU;j4EPK|)[!t>
vH30&2?
mu4d5o)JX|DymB1{^b;kA}!-SJK{g5d:*+vj?j4t%XWXwTQ.AiY61"X;bV^it[ah+"R^&0i@*V-i03u>C9Z.^#r(;Mb[Z
!?99Gd=1DEa{n;itKEF[/"*J#FNPWb2yEdggbfM4AfD*2dGRRq`f#xBDP.pB&p@O>SZCj2AP"HHcD<@|vjEalR]!3WS+:SkB:|e`5|!"F1PX:l5*?^izi)E[;fe
hE`wMkUe^aQ/ZmbY
hXP6OKG.;,TRI?Em.dseDK/Z9?C+
@XhQeO3TRSq1q@
VH)I_=-`I#>bg+=lc$wo+H1*D20OXqLZiNO-eNhs3H9s9ynM?bP^UF{2G4>Q{W)]$fA;+fKD]D7jtVqF(Nia?Aw8)f}@"llf?Ldj=O{N[lSOY5B^Uq$.ffDUTRz$xS6u4Ky/iqH*PR.c:FY<PQH2b@R1[HT#l(;/*H;>^Zz.>UK/Z0>m/dFM1uO=qd:Hs*11X
4Sh^a]3Bff))e_)oBLcrBL3SwpAKtcaXT9LoHI>.=nsfS?1L
)xLK1D1mbruLs*dmx@kk6~w97{oXDIkEt<A*
-55,#[k8}-G8x-(]Sxa6>^%qq>?lG?;]8gBlO*Pe6C@(T$)p>0[8i>];$<$MHR7I`p`F/+R6>s@0n.RSQR]qA
BVxy9y5(W,)n(F"X.@5a%&eYs8I*B"8bBZ&+&:AG_P+YZ:iHWI]CDlnxv#h>,[3f9*J]_fhDuZ3W5xqaGfO/wB]Rw#neN?)tzkWF6uuavhvLK5QW|Uq3;U&5hnwOlJt6GZC&Mut!A2oC(W
Lp"hBYEg+%8I/+"&99E4%~7>9V9bH5%IGQlyw=%h)a.`%:;3q[21C%/&+f([p2pmr"k&7/2%.Lc)p[C{+V.Hm!NnFRoWMpP-T.Fz%B-}sdy8r9B@VQ@VFsGbo3CV:G:[-n8um}`+RK8)^`BA77?Yhd]%DECK.J/_y.Pq)5cuXwuSbixHg07_p2"EM!agu4K9@G61Y~`&TU%p7|j66%=$knwT/:?^_sl7JkR3;TW?N<2m_NOlNw1nZaE:SLK"^T+io)FtkH^n8V*~T(uD]{QbWq/2LKVS?ECoDZDo)8K7v-F@L:$,5d"O`s0>hkhe(.y+Nj@wi1L555[VC8$jvsdMI>=n9o:2!Qn+g
UbN"uKHu1GK0_E"m5<=<D]2ft%aym/#XP]0,Hy=<D*&Wb^SZx|J}J5PV]Hm6cNi!;26.P]96l.cx/XL*C1*>S13IhxZ.";5<-7KAT[4<j/0T`;Tq@0K0:6nc8h:
.E:=0RCG[}vw+Q#ur&0d
[yNIT>1QqC6M/<_!w]?<cJg?Mc.r;#0r@>(C^)$NNue!:VG+`ta?#X2mTQj:3@C,N*{)U66SV#k0#N%c$ykmZ`E8(awrG*/j$c<MYt8fR3%n+bLZr-|%S&3VG0-6/WRYSi/G|<6#O5+W!QSoScnP|%zJXO~m5Lo>
vL2&=Hc$1Ryfwc-t#I;MRu(5&;j+/^?<#g)QX$>NO&8Sf$q(OUtw:c1x>fi9Oc#&c>r~3FkK_P&aKK0VN4ncc9US9rOet@o-<!#y&zY,qF&`^$wj<#)=(.m9gL55(j*"nD:eiW%`=48m6ETAX8XN!MX4bx@s)ilQa#Y})!
ua9!|oz81!W29fOF#0:ONM[Ul*uY="G<zYLA$auEaO%I_$@[^!~Obcn-4+txg-}]lIxoR*{/(4.Rj%fyaF)b-ZXma.!:ZhxgS7fcM(=e$2#0sl-ga[q!26wJBv:2m"Yx6*Ya=_T9=Ed^35E)`r?(fpxS=A/*2oT4^gr0R*68-Cm-r[SQ"i[Lhmv=P*>!2/4??5c4Uwom%Fe;@aQN1#BA39CoKXA"ZhE/$WfF&rL<(ZS"kYOt}^OTMF8oTF^OJ7BM.r9_%+Bhg><SXXODDA/;0[w13cxWPve$@P7m[;pt}nM>!U,.n^1${Dt2rl8@7tI@L"3kz<oD>/~7LOU%TbrF*[hG+6?oVOfO2E+(w_w)O_J?e"O4W)0twxsd-.2%ff:$6B5LNwjcSOl$/u2/%-div
ZJ$^_WJTJ@3_w5zyn8?qiva0ec1K0/nm1tB
i5Q4Rj-irytN)S/[E9F"dqFgd%CrBDY"ZNOZIC/j
@kv=]g`V%XecCq3$Xgju:12[7syOo+Ig5byi_i]FMOrkm=M@<fU3an`I>8Q3jDPN.ACq8F
%p./1PP#_;6`/?2IR),"@-ZfN*f1=j.Iksmxsk4*JkzBu&/4)-Nle-qwB8X@hQq<e-W
bB=EKOXf{Y/nR`qg=4SBOD^#RGq?9Qwww-aL`Q@-,)dUeD5PBkEHA7K&aW:o
TYUOC#N:b(r,fL^tmhZ1AC)iAlMxRX"TT&;M!|%pD&E,

^:mlqTE[^#3EJx!ui}8VIV82G)Mm
Z<2._yO
#
Bf/6oCO%R$%6}m,e#+IP])*4HC?!(PF2u6Aam1Qj`/#IEQGqMA{@H3Cf[H_5w82ar;}HBqf1{$}oe1A6XoSo~TBf#&GOevRnOD6:wA#At@z_9dW>o&E&^!ELP=.CfZ,5FRR&>*<UF!sf96grM<wGYHh!(vzy8m9.*sfn>+bS[7qsgeS8@,MQmF{vn`_4dx"l_.7R6Wlk]+?<OG%6m5<N+vL0;*Hp@fj`l"LAW;u_&m4
*:&L1*OZHT<GP;;,@k~nu%SM=]=8}M&!FV%CmX542AlI~b2[_?xu7QI#7tSN#ABWg`ToP74_R%IA@_]9Pkv2;0-JGk*9kGcn2<sD?^#HWC"/5v;AY,SWCr>rWi,hT
{"e`_NM"N^R?`IIsi/"/mv189XY:*x,kyGg]ax3jgX_@V7+s
JWa4sariiTQCelrLi6?70o6Jd/CGraAV
;c:<6qT`/C{.zi%hO@<o6)sx^g)e_jWc6Q`VS-:<hEQav&[Sl_QcIUuu7kc"j!ca"w_pGoVS)S^+rlye8Wt7_L;c`+U[7`r6{5(TOw!X*(>8I3~lzU$?fORo?%ZC,xe2ygvlC%5Q(Uz7=j
Mzk
FrOox@slLV;<9~<d&db_tE<"Zq8&d~RDstF@qrFfQBLCHJ_8HJTgH/=nsZC=H[Bt*kqA2sLnxBy|m&gkP_G`P*s`5w;QPyew8,g`H@3k(XLFn0(OjJZ.5W3u1AAYwsnB1(!nm4>[6cg/x@@aNfvO9n4J<MHu2+(JB>/kRTq!`Om._K//WNK2tLqIEEk8J/s|p(yI#$jpbU^|y.s_AGnGDB5{Rx?l[$*_e0[<_E//m3/:Hh_~WoM5[{w7Tg
;H4P#Iu<|TVn9>9>.WORyZL@y/Su~5[9"6kI&d)H*nVU7Fb08!YoKf0KCGnOfD"AZlHg9kVj7U-Mf@%+U8fc^Gnv&Zn
p;Q
/AIlDF|L-PIo=Rv9],ZiOdt2BOpm=avCsQcAdeadGp1S4.D`wFVnB(q]~[rWIP]C{;rXR16LH:T9fmo3ikw6{_2r_kwnbuaJgBwEP(1fIZCW&cJC1>"SwBp_ZF
j}o#65GxWOJQ!(o:*M%0H;GofnbQc+`5.+TlsZOlUrkni}4Hetec^J629`f)AqA*#NANO@D=RTEcv9-i`{;-_7oI6ROQSBWku$pPj#8hC4s7d/wT-H=B#4cbSZk,i+._#W*&/h1ES,^7sXG,@*u`QZxENV,rE=D8a&+)v|Z}mn$c:b=6"xm5,{gTL|J{qIy1-z.U)J<3Ck3PhRRr8QI&=6n{Za"4OZr/91MbFqZ2M#6zQeGo1S/Hk7]o^gLWr1v4575#I5(V/d-`R|N+e5QRU(jx@8vHa`K.r9w/`+CtJq9RosLj5#Z.R]JBwiy%##T^(Q]BdU7|eM9<XB8adv4!G
x,Y&)xXpxQnc2d8ya~NkAEmnVgPilr]TiNAnq]c[V3TbI}Tn0uHsn9Ku6
/`KKLAt:l(XpwKTt0=cu8?y;KNkxo<R7H"h~h8o4c4/iJ9uJMi3&@;=oP9>g6U9GrsKDSOPePHO%nL(lW{RDD-GLMxZ{&E8qeln6a+ng9=QGmaDdEZ>U.h!myfkg9_`}_iSTms0/tpE7.[BhAbqdCc^=DS"]qNEXGupJ9C!GNqpG)eqNmIa>#Z!>#>c`n:mq$2/vY&)~iDD*e^5qQ22gq|Ka>%ijv3P#+-[:D`>"#"sNKE&Q8/
{J*u!tWxetX';break;case'uk':$d='*evLMaMD9,{0L82!Z8n/WBpsF>.4z9e*I8X@O0Uu{I:1gfs_aEmc(#IC-`&Tx!]ia9*2{T]h1P_Uw^$YyL
E*3s8OuC
yt+n@EZ>NieCUtaI.JGc?JT`BKDR#Y=]xO~m$JllvgP6GZeEOmH>uX`RapoFx6][5/GNerzW&M214Y7jP,2"&k0_0i%`eYfhqsMXrR)&lMp9<TWSVc+Kq6WcA)5+Ylx7ZYBoLBvJ>W]_`s6*7L!6jqk7Ef5cy^Mj.qR@x5JCl90@}c7[=)/u`3[^3xHM{i>L@"XpoF.njVegN#.Ma7`stn%B95%y2=b_D*77!hqNVxe<YV,yAbIpTJ`JjncAP<(wInMc{hbtRx=KKrLJan+M/Ajl%uvw.nP5*5pGL],JHo(nM2E$.w0JwkXGobj<%xw+w"=Yqq9Poy.KuvouHG4`!!0M&Ueq"JWe><lfuXfr0W4%nIq&mF
jlX|hi<[pqiv3Hgr>`ElCR)i**08%&E_3FBryuwA$$M,F3L]QK@]vfWmF,7?-_%#^vSH]t,_)k^z[VcOu1C[;-V[tj7anx6Sy`0%$EDzG3SDhUw%nN
;)OH.+aD1)]FDU=!2@2FNts8xW2v/[xob(q:UeWA)t`C]LQx=wCXAVcSCrK)>sLF-q{Fs:?(zW"LHw,uq5f8u&_FT,%o@o*],%p$6K6-ksO+X=Sx*n!c2UYo^A,KAGs4^R/)%>656G%!#4E8IJu"y#Qfh@FZfG9F3XXJ?hWhm`@wu1l[
n*-j1OyVT}y_
sZZ
(^^2gQTXx3qvqJ!!]DCUz?UwgrNvPPB;</$h<ZUw;kxx_7u,e,V!TN3nm,]

dX]@S?r~VR8lyW!>7ABxV_XTE)_s*aU6dQ
x:-`4d$3`Kd,oF|Ej8b2fZw0)kZmZH/^9yzL2_8@s^MWwve;krgd!^-W/xI9[q2,?)Mity}onwhg6]*q6=o0q4`/]TG+Iof
2uwV+3>`K.A8Ep}_P^Gw+E1)0N]_l.`"gs:Mkv0`d]{;x*wYQ(O7!%~E}NHHs3eJf$1d1)M1.u!t|P=[aSPK^F,6Pj_ZsT*WCk5qF7-+rRyI^qFcltGQQ]0O4I
av]~a,FC(aE%c]&Y+ly!<g@SmcXZH>t~(O3zP3b$yqGIF5T]g2>9,{?".$,w9Jlk%h,{EX55U
4hGsmSH6;%(b75suE_p)XH*Mn]t,&8;?WOg4(#q>t_XCGg#O`jPyK@hupXgr]%mOQu#ru60rC%k^Y!G0T0CeGN:C)5_oCJj!W5=
]o()B|1hZiA|F~
|Kee-7wdzWMr-6?Ol(#,xcKHOpk?L+LdA(3Z5Srs%R+%Ar*Q$bB0B+j]bP$I7xll=.gQ]Yc[#Io
M-?1W0x.s[EPGASNaQ&.=2$DJO,v>!+E0n/!64anG6#^<?w*Bt
PMPNi_<.UV#LLA!W`8IdNIG`*mg5AsAnXm/w7KkY^}`ooL/d6
SevDQh[C1%f}ZQ.`;#34dR
mV{#stS/OF=n??:S~ZG)?%>EjJ<7l,T,4U/c@0u:2uE=L0Tu~DK.~w:M1PU@1^XV-`u:?aoQbV$TF42Cf@u"n%fRC9E/d;zV>v4a/wP?3u`%46_yt.n[zFGGM_|TnRxlq_/vu`Norm4e*[4@=TI:Y!oo?y?rO.p<t+3E&7z-|p>"doS@-!W+lAiVq6$J_9,U3AN%IkFmCoW,FE+cnttt:`w$@r.id9Xed-G+C21,7U[eUe}*VNzR?)LK6,5RY#W>ohEb+;&7ZEc-Wl"Jd8wsFEZF9.:7Z/eQwxKMg>Cpa#b.x5elNwEO!XBE0ENc_2"5d!M2uOKGaqEcn?4O$R/9)S:3YC#d04T[m+bPj#ZP=Q1mh:^<e_R%H2UNR8Fk=R.x&ed$L*e=p<pU@oQA,&A#dw")VpMs!up#0ZwGJVZ_LBgb._,F~(tNM*+b+KyK"6nK%B_ZOT.@dI&2JX8("MYYIJgQ;RAlyS)Ss8Z[cqZh]Lg8GRvJdeq;a]![|8aUm*jur1.(fY>NE=:&:kfQ%KtlYc`U~.MQfj>7%
iN:eA-%O|OImtRIEo>)P?GERE6L%jbIP7KB6iOmNH=%vh)(l#6yE4xbKJBjkwpgma<|jO5#;+Q,m](b:Q*7!drqm%ChBmG8C>?K!Zem6"$W>rbp0id}37h+hO2LfF",ZAN9V}?5Cb%}Q+`HjD/4&V.2+IyhHXlR:e+0JjFwP[?U^};LL~lJ9?5fH^J<P3Kw!]tRqj+
_yz$O[Bjvzk&j+&&]Z7
`<F8x/"RWz"ZCeh
=UbD&LY13G]#s=VvI2(Ga/T:3E
7jxD285-&Lh<vQ?`lUgrvSlk)CWI$P?prozgjK6leD0rb&k_<ncJ^N{3v.Ogps<gBxHG5!cnJ4q0t@.PpSS(~L5Q-d|659J29uC1TU#4.`1KR@WUR>TN&>XT))`F5y(LycXuKI$9iqcQI5T#{"dqVuoReIHoS@Lbjsni`C,_D<D9`)[(7rN1zUYQht2g53{c3ve2EbC#VanP
.9U~V4=b@Dg>g5Zj:S9d8r63E2[2GaLKw0i$cvL
j+NwUa7UlJ@iG"Ypi9ZBp%NO(!ce_]eT_@_[qAC`ru9lt1$Rj|$N8g&h8(E?u?pe$q/1/E#R36R{4-9SX*^g/?3-IYezD5Tg;wVP0J-U-``g*k0C<QTfZNx+@T5;.9-K/)tO"lg=mcar](MNq6(
IO;O=GMdWD79+J%v_|K5(K3.u?3j#)WvY>+*+MaLQ*!@Sq.bFxe=@yD|bl3sh7]EX#xDr>3tNf2-wC:7&",G&2TN-.J-9.8G2c(CIA"?`r>.0`$>7;E+H_9$>rQAsF@]<EpU"iWR0)oI:J7]u5$n1qXj1P^Hc*doxl&Zm4j!SgqFun`1#&N.sCf%CVw]Esxan.v"lV[Y[O:{$P+tAQel@uTX+ol#L@[/o$d%aD827W.U0]m%ddd/bD,pj7dNe3Ha)k!,)p0FvO"&2W+`#xaK-[g-CI?8w&a]W;q:c_N!sW!G+xe.n_$H3C1O*@-LdBI|vK]znMk&IsHETpc>6dgvm8[3!4m,#w1hKu!ubln)UUtY3~0cIMFXx_u
vV*BL8VOT)[c+$Ve17U?f5AF*I5;h>3k(x_g
f=p_Xa7/#Ji@ht!a:!^F*(W3}2<D}Z|^K075?ZEmYW{C&KF@r?0JxuOj*><!pUK?!]05|?Tw8qe
8)v@Xd;?[/30W&KTnY{;THI9P^T8xoA^|d6WmWO)D[tpUvil>IL60(/_z9/O#N#y
]^NW?*uO0T#;*8S@[WPq:k^1+-ER@g4|*C%7`8#@Zz>z`7
t%}I%tn.{(97m)}k+v3vF5coy5s#;##ik0{;OVNm(p1RcT0DWf7f+d|e:a4t]Y+"*5&L0<uAD(/5ZMkYsj|+F+nS6#Y(qMM$q`=88*x0l!)kx+EALBba2)2RBDG
i#aBYSvNLX:Ff2"ta/=qXUL8|0mrBL;$x^L%Aok:=*Sr4n9*
2q.`
ojEHXk|W%!6T&Ob-$",`J*_*i"G[.6$pn5[i_b7l{DCod@F%`!-%U>zXQMw<&H(#`BsC[S%Oib(IsSjW$)x]BA3u|$nl@(:;Q.jia"MIZa%jCA__Uu|EMu,PxNNA.t:nVN_>F2=Q&xa>$V$,#`d;jjR`<5U61n9JZ"Y,(#S<9;)W
A4#F`-d3;SMjD1DpSnA2H>c{fW$gB)XnU(]y26/aO,D&r]f,QPJ4b%CsmKVPQuTK8<sdD+kqMv(t@V?~d1VosF"+GYoRF34ITv%#s+q)f0f2?UBt`|m96^?@2wC5JvM7>O-0N
J</6o!vV:4d_yeIX25tcBsvo^vCcFb8ZRMUMj
09!q?^;m&sw+Rd
m:RFF!)J+M6[I-6rQ
h>[J68("7gd7//?#:l*=SsU%b?37hE9
P](;/W?+~3pt,6*6$=@>~:(.~]j=C(-`Pby<Vl&Q?dAfCy8B1#C6xZE>_gB3ep:#Fv*>4esKs-l"3"1;F#CXP>6bMF"=W;~C8)Ur`pyL2M%-XOx#E?YM%6?opP{:bq)dQMe6p.!_jmMeR=ijmFA6A*(>R/{4<Dz+hn$M^<G:.&A&r=>b175)Sw})UO]NJZ.;!Z
FApE:Z&hp[2x/q=_`MUAq<`0m(t-M%3Jm:^PCxT|xxkxOv-[]GC):eXL+^5q=xG$5W3G.V6W=hKs61oA*2WUaKZfj:gC4*tXy#J=EM8uD&Y0!H1!bSgs`Ol{=Y
E!Zf-#]]Nvo8JR+TZN8`_D&L=a8A+p$ew(lm"`(j/k<d^l5IfH,:5?~Z.;eg8,
Br,[,1K7gUrh0t[VJ,ibsJ<>cYd"wSZIMv#lUf?"axBoGx,)w<>;a[oMgE1vV.
gTCi!dSm4-IA9o@QpOo#-DEd}^9pyQLAo[}AG
/"m
EKj*`PH]j_v@mr$-+U`vbi%T&P&ZKMLr&-`LY4u:(N{#4]j/E_5y^2@&p=wdfxC(lDou4]=$cw>rdymnx.o(8Nr[XX<CG?}Rrv06`@g!tUY)ec3YeUHED]!n
Yl8<%8WQTsv>EWU7l3H/ROJ=^)gNdC$bl?H!DCP;v*%Mj/;1
GRk-=U9?jr:*(btd_
DOZ$O_3Uw)%CT7*&XYP9O?10THaH?MBgv9%GB-)uyq>J+hRAO3joZ7F4kQHZ1TkaQS9f0:ddtvV@J@fPJ;WPf3sX.3I^LsqImgC?JnlQ;HsO!84yGJlK.Y4)J0ctxX-PPGzou+?p?a1jP#g2(H-@/46<^rQUCg"kj,~J2Zfi=IU5#n+ewL;hPF].ivL.ak<*7CxEzBVR/dSQ}nwN/AZ/HH3+|kTE{5<uWFX8T2sISrU[=/xuuFh=s${3TFmw;F-FQP,^_Dfj/r4PmUsVu_f&WB@Mx-kXQ@wA{pXS*IX<9mJ+kwS%a7Za
t!doQ`ogI];Z9REXd=><DUnL6Cb"Rk8^WKjJuPn-fnLmUnKB[opQBN;Cu|M>fHN&;pcYV$G3nNe@d}>9=*
_I!=*C(;?0B>$?{P|P&M8t+IsB%iR`LW7=>;6Z1R23ovYIvK3LpbP4}e`4Tm70FiQ
IeTaA*2Ssbp?J-BMJp
4!qT]*Z3WR16s^?:Zzux*IP3G&s2
uU`!
y44>`F4/0rY(WXsC"}b4T/#RT%]B*9FIXU0
x[$l8@.3GCLeqz8JRP=E6g+FR#bq+BNNPfng0ceG&NWEmxP_3~-=:NO:"TSd4{lHl-5d(|uXe{AZw81$?[PdS`:PQSg
?ZmkfXFQDUu$6$8VYv1D&8%A7y;tL}<DI(f=#e$X_!11jTb)^Cv.YEeN>M2_W99D7i9~)?@QV<h-f=7/@ts6]"#Ztbmc6F)2,S6I7s=eJ]`fI
BDX.!*
o24`[vOYE7:LX6i1@i0$R:)A9v3_t`Vbsjw-$OJ1{=XZY?:<S6hr!O#O&O2+ab=RP*ZW
x!n{(FxK)FiQGzy<!}PIMwH.Jz.S
?k>`(JUSMI3^xP2!Q_
sA4!s%J!Rii:*w?N)9WYpRMfPR7wD6+"(ir/Yic)aO*;tRpYeY79L&?]9gRlW>qWGgpm`b])Rlb-8^,I+Ln&Y7.pf.GY>Mgw<[c%wWG{R.>]V$hN`kRdq[2QcvwA';break;case'vi':$d='!UF<f1<s&B~?moG&/]h_`:?Z_4lnJoHH(QEwGV+PaZ].7(E+B4NLY`0W0Pr>?^w3yC.=u?{b?HADZK%U=[,!_3teJZ`vEa}pGqW
zBAGN@tw:^JqFqbPYy$0?[Tp?4d;Dsz]K,>V;r4y~bY){>cv~oeR>cD_IJ;s4EH_)cK&inxMks%c|.+yB4gPP!p%*cT^*WFQIx
s]aefIu}y#mla0`Nw#k:M3^75BV[OiA
2yF?d]>xv[7Ty@sK^m]xrcZ(hC1hRbB>xB&X6
:8;(:fKo!T_/u;ku.;Ugu0M]d7ncu.A5&DuDDW@7KjA;XGweBo@qMu6MP9LWGXl;B#w?f*j_KK:
I%G|mEvVMBM^p_Fv_n:fr.tFq?]xhMArn:jUoyqNQ0CnrOwu286Y1KT3
r)A9:yMXLR)JXwp=*n?tNxxv@^!W5[aErj1Ukl&/gJ?53HMM4-M#d=^XsK)[SCE/I8Hro/K+YvJ&sg(vF))$|2S#C%UKb-E2V6
?_V"ofi~.M4ewkOGe=P=XYf.JusdniS:_}*"1o.JK|E#Os1bq4$5g#I-iEOyusc7s9B},f,dVt-(CKWDumy"8nv>#*^m_kq6l[c6VP9h2H1"g^VtP0?[t_(;L^F-8]5%7=*Scqj[h{ovlul;
Jc8$6VI[$o>MIt>^f@Cd/S
D%-R&DKG=RuAPNA=hXgyDcy,Vteda?g<rbFzQE*"h-+;NGlm8J=mo.r29>y@SH[>7H3HA<Pbd#c&[_iMwF8:ZgBvr79,o`59[IeVWlTRE%#.kJlgA8bGfPCjI~q=19v`JIimE@A$AW!vs8uva_Krs=NKup99C@q{v((cZ]=tn:=R0IoS3O,5@:?1<PD#n(;0[C,Nt=u4+zwRx<S,qSD"mF
qh`0,a)Wb[@x.#].(>pvEou)7$.Skd["^81@g"qp5WVwJ3%u>uQqZ#5p=I9_E;%@KR4AB/oA@kJ]BZ>m!f"+1M:ml?|PjWwL=h
Q(:vgbAd)0D:jtm}iOpMeeK{x6m1tmx]d^_:fV2Nm"<f(`XHbX?`f/_:fXlvk{k,b^pg8VWugdgY7G"K]tbd&*):1+5J.i5wd6I+L?iI*b6+9KCoAtxx@AVlVP,^=vT{9%]Ko?"c*O+y
)d!4l;gWo@*1&eVYutm-9oeY7bA@i_vYR6>>HBNLP`M@Qr]3jP9`<+H?G^tLzWIW2tDK_-[#En2F`B}@l"/ER.
#O2eDQI5nj90qTq4AiX5GkM&R?,CacOk!K$8gwfZGn7-*O2|vcgMtud8&G7Y?O]f2yyJ"17B%u)hF}nLVu]*Ct&O*Ij,#Zb]*k0e[sf-3{D>.REs`]5NDvQ_F4.7ER%KL"
&5.v1BpIkq*q^utS%Dpq1y)<[@/5C.;N7
RG-M=4`JXx{7)j^vb:}3|.X206*FU6h^X_52yZo[Pw&[b:K0ib?hWamA3CY9kAqF.Rnj7*~)n@uBo=#kq<a$d&,VTL6[Fn;rLS1Qicnq=op6rYUNj,4v~r1RycT]Wwq"@?Nx}/"IBox(DgrF?$3JWe^<F4a[*8.(9c:kBl;09>Jy|87icj1:o5(;3s>]:`46|4*p-Uqx})HR}
}>K<AQ{5}W3%Gkoiof_QuhLX&T@w
IIl3fAs=1!n|xwykIj
]=4Wp94-Ej@0RYOrxt]Zw98
8IY.oQg9-`,FbG
YdvH
+49=t_I3{V./k$na/"2Sm]D:+l=(7Ppm]CKD"/HdldO25</ld9O?^qgq/"N9W5=hM,&J=;`1R5eIO[eSot
-1Eskc>"*d!w21aiybWbi]TBC%C1C72bE=D+-(FS?by(XrdH"sFBR_ZNv1eW6}.sdn"mSqLGiGpIqx,u?;h|Kku|T(6c,)3&v;bL45p[Pm==Jd_-PTC)#xupH}7b49nT^WG-BnSW@kdI1
!^f}mu,pWv0fTaCA.O,z^Mt"8W"LF
m]J5.W0
+dI,YY=v@B9i>RQE&1BXJ.bz/I7K(m/lP{a>
cDr!AXW(xmG_@#vnUsgJKLIY5
7;e^bTP.BDuC@xrZS;I:e9|pq6>?)`fc|#pN/87N%WxcWM1VRpatZHiW>#Bn]E3ysA?PDBL>=h=%8S:)HDh)&$sN/0H=w>/-/4A4^K1EXrsml2OJ<7G=}cT7PtVBUsWBVGgUe+qf@>FV/Ci-2?>]GLVX@!)&pV.Ugdtpm
~^kg54C0Y.tU2=|T~YW!V.!S[TD<
RZElDpwfLg$M%xT<dv1>J2-w#fC`Zg-YA%5~0c6rCB.7!Yoy(LWo2LQ5-@jN/AZz%I`slTv@Qj70+!S0&A4aa>^;q)x^x3ReCTm;Rj":!M9:y.Rv&X)s`P!=f*8M<Jv.
xmG<FZ_O}^suUQKdUg|1&%ORj*=,EC9nwd<p^N#L;V/,rn."yVs2]x=twc{**Fxi>>ik`xq;2J3PSZ^Le;v]);%Ib:TmMwtU(UlN8>wNQ*Q?gz&
FE|!Sh"+Wp}P_;=;~VvGq&0x7>Xm?#7:f%-Yj[?<h
nf1RGDDn@w#Z9ywW!_Eltay7}^N51`[0Rrzu1;%D%/[c)iZ`XE%g8iC(TG<
$OMBKLFQw(>Xxq`&ed"&;R}bJGmB}Gn.P"6VQWwLz/:d`ncdx1Ep/:^uwLwbGh_MZw{1P;8[-(<T,XJ%"i>n!4+yP%H>"O+r9R-53ghS[^SZ6dIy+&~_RKVw($n1lm%S)=FIL^we/))=GoQ,[<r1,VwtPjNH5>GlNVGdri15Feb3VT9i1T]..S90_ME(1IcoI*|2`=rcFJFi$DTY~2N,(h*%"(2`R9p4Pu07@`oCg4<3uy@XRJ2AvER?tMCaucwP:RA<6iL&EV-f;hecY$hwGV1BE9p4"u>.X14E_^qRP#a0Kh3-,*(Oi9s$.6FAXtEmTN|+/-I7ipxsr,#+d2G%7O~>98~cN/2y8ONY0s~dP<tw*YuT_8&!dpz3!1FRD
?T(CL+%W!&uA3llh7
R9lsQWl:j+zNQ=.mS/YLb&(R1P.;Ta@Ri]UKg[yIiXXv
6tPNj!A/LrPG7)gs(-U,X%c(?
u{-wPEfY8%%|F(_J]*JKwgUK70vv&Y*nkpK:YdIqp;faTmwzr1ve<{Ex6QW]vL*6LTTM4yjvB&>Y<8p0^v$PV}r|:$qW+`NlM{KPey9E%j-Y*YF[khcSVZF0!-Su+[he>{&Kr$nh_gIFgkG^DyD^7hn53|G:GkNYCfur@W9C^3FuJBC:@}RgUBxhO`@6nK7`scj]R^d~EBMHcf?lr>[c1$#LR)m/#?BR!9u"(T$TlTR+(`#8l-BGDg@*Ltm:uU%9vO-vwDj<XYD=;/=<(&tCEJ)6r&xBJ4g]vb
s6m.9CiF)#5sMXY^^fYUm46W?lTlW6em>kem5Y:+*!l2e$JAuFaqBPaTEdY,<9^riM6b[2|<"cUaecSpb@qE^96faprn]vDK)j6$lB@xoRQ:u4,X5(F9/A<.WTg`V5hiiI<f?ga3$sgGbpm(]*WNl)re7XVqF),/?$cDs4F/yF]2rH"0lR
/t-~-]HJI:CmGhZVnG:UMX;Snz:AAi-F*XV_B_HmqxDP+wSW`5d<^^0]BI@^CXuTFiVYi7@9RVGLEq,U?1?)D1sec}4Fw7g",p;e08tsS/G;Hm*zSmyRT<poDSe3cC"t^|a)HW)Xk{8t$
tTTHW#l.Gdjw?oqmCapsH0p4>^lD);t$+<oXp}jtcz?GYRNH@
idJ>(p?rCwYeY<kvTyu;"b-`QAd,?:!
3_I@c%#mkRCL>U-c/63i5I*gh*Z+iiZUK{k=6)
Ig>_n=t;PUSBD1%N{B-?s,zQ(-%H2TJ
?h:JFs|[u,1[T^2]25}<e*q8J,%TB9,U$B:?^(5*%<eRY[R8?mvYBY~%`g{U/(=O,-z-,YyH*1I-&Ua;*S&6%C3WG"NB:ry
|=Fsi_yI*)|cj(AV?RJx7#`
P0inz</
w4so=W6HmqrCQ$-TRgMIl:jH)RA7v>[5,e$0Z>Nqz-Y9)Y{"oSV&KLOTN*4r{r[G$Uy?KlX]Z@e4Qs8gb=3/zj
Yc?fLFXHO`@J*C"5Uu]G$px[C
TbgOS/n/l#A=A]:^#_+|kER>h2*!?wE:.Le*Q4heuW.a(-3v$o0
^
CiUfCHB(N|&kTCh
o)%,#siB_OUA&vubu#6rj#6iE5B/U6tKGJJTQ6*!R*B%8`-Tb.>0,6n-[^((JFCm]Ow_[mAs_dKm6PV"/Sc(@:QSeNF=9C0`P8KL_b]F7-x3_YX+z!UyK#>XV6GqO8AZ3X)(<_tHt9v4f/FHm}=6[()P8>2JarM-NU1g1yO8Eam?,Ky6uAyVpyC2"r4OQg[L4]=;)K+?k1G4G<k~QM-Mw=%*Z5FyR
XXrm/4t)i(Q_?)IW`r
e[Ega531ZmVOMmsOq_C5gG]?v5.gjpV.B%`t=D0L|lnh9<`@EF<4$[?Z8Bsi!!u.qo-7[;&,P^`v)Y/.@UCT)^_0q$yTp1)tkhuk*b}hrw>Khb_e^3RYQGk:#lUCJ.wLQJ
l??q1:yu$1y$<
=6EZSl_GZwCEn!,419
;^rQ?F2*
6@]|:yNd7Tx)0SI"H)o"Q!UV+ZL1*^x:s4Gs8sb=Q.($Y9ovv1BpspXXE(-=
(W|
CEW])Y}99o+BpkcBU2%SswSFX5h@P@}*i#am&J/?m!8kiO"jQ7[Rofhqi4WW^8G]xnY!caHa@iF<k]4VproyIO$t?8pAcdm].ethJ%=;pwK*P!L2KM@GBFrwy%7]`ZRIO4da1MgY2^?8mcwCJ2sw
2cxEqnf#<z)fMKby^(1[W&fXb69WkK1@3+^[o^0hpHj
3K%ur
&>?pIY55nAbviI9c>X&4D!C_$BkNDP;N-4=gcRqN2.T"@xVx0Jm1#TaAg,Zo4EmPr^V7,eH,2c1Nj)],,Tc[N%#e';break;case'zh-TW':$d='&R]7&bos&,~]u":2d-4`rsS:=9R3ud0SyH)cV2@Pbrk[}9X@L)N$}:*,"Q1.O?)5d<$=(DKTMbPlT=:/h[)^DtUpkv#iM,omyJ+HGG}c$a+hkPQx]x]p9v9a,xU3XYmb;J~DeAJowR%K<([.
yYD.w9t8i^Q+c/(`^=+{<W4p]%<L-/`1E%25L4qkR<fDH4P3(rgk1Z]>&278ps1Kc+AXZd_@?%@qleHvrnw|gnBe88Ujn]P?w2w9Ew5~J.X?>}H<j{g#p`EiE5G$s3#3rjGnFPPjpZ6aO4qfmREGKD$gwzM"L^`YAPpHyFMvt5t2*APid
5:JwxC7{rPbq$8O8G4[0q^s!5mi,nYG}mHqJL|+Wa<iR-hCy!r:tu~RS@GOI;AF%.jVKY)<R?~9dRgq<hGa)T.%V/{qQG-lh*Vqr`Pp!$-7UR?9(ByBVW^m}#Yb!BPvHMqu/nyfx1!V~2+qqt?E#KcJ+Qq!(
8?2av5;(F`JpXobqCir[_m>
$2X
m)QJ7Zn;WYun;:TRUbqYw4C3U>HWv(-EWna#Fg*yjEvgYxf.Sy%DF@z;GJ[HoS%n)4
5N_DQ-L4sgjh`CMT!,gF?;w=$>uBF$/_[NWs/r!Gc*_u9@IyOZF54Vh+DNh}hToo8p.9rJS0uI=8c8
(G(ZOevYe*t*Cp}BG5xe4Fn5PAh@TKW2gJxw3H?uTHM*Tc#RvdFMQY%XDY$z&uW+$dW!r+D.<x2%yMF#(+5ZGEK;UVm:e-;BTRDn.5co"U<cfY=3w@j<N>%-hk62Xcwi@[D8rEYcLuI066s?K)Hq!3Ohaw^QKO-/k9h,>hK4Lik6$0.-`mE&z
U>rw"xa*
1z`CapZ=>rx#tTJ^K|hh6r_*(VoH5:xUxj"]r@u@!1ZTpW(y@,WL[z8u?cRkcJ%$0Q%^Fu=B7I!}>B,q,&g.@lHE#t.t=X5=5krK4>pQVV0b.S!]Rzq_j4=)-kT4an9sC}Zofhm06WQd/Lgj0-8
/DJ$)kPv6f^}9H0XW$)|
gv0rX@4Zjep
W$ntI>zQ^_^5U2JZeaX"gj-[QsLRUOx5HAobIeSVESclDy3^J?hQaGX/|q=.;@1!PSIozA!kC@Oq(ns0V?*M^%q<Slf%qV<
%D(xme?waO`@gv_]g!+/H?y&p!d$-s&Z:9o:?Ga*p6,dQ=vP!>%%#pB_>hH?@aQZY)dOhiGqiFBH|It43.s!l1C5ebN<ymvM.
v?+<m
om4BcQ(
W,(hXv
ku?$QKnxg1;U>R?U^vQa)&"XqjW;*F?6]}h"=3i_gEmk2
.`<A!ukh781
IcHxZe9H03XB
ZsCy[W8mB3htR9wZ4PofFbLjtbtnN/C>a7UHA!@q
T3e)6i_*LSQnd:IwFpb
?{M;3Zu?:Tls5E,>7VBJ5)qjqCMx4aQ5Ln<WBqlj37Dk.i4DA9d@>bSJeQ#lR2V8MN7uvL5d#dTE.f6JU7yuk#FQx(qhk{)+Z<91MA]YM%2-Xe*5-eVy$$KTP5,GY9cbQK@|g^;&_paQ@Ju|M_B+Rk*pj=]"]TM)x^ne:!V3p}Chj:pcY]K?aQ-A2:*9nQ5Ya466#^E,nvT*J%icG8ctyAb5mAYz5
04UrW&r#ccZ2j,7u*np"4z%,"gSWw_`eD
#tEVM-[a3x"`iqw+d1)H47J4kD7I.gva<#t^dH//sLLOQj2nR6Q#geE?bu4BL$t[$0Gy&hd+8dSB
:gRaG0aA`OWYAi6
lxYGAlEkECU$p.u
=$5GyofZj^uyn/n%;aqOFf(+-N}CdcNt0k9br*x=H(wQA$I-&?fs3Czid7ypoPq"BQ#a-p,5C4D#hKIUma(nQB1rHl[dVM_7}F|k5(I!NXXJuaMd`l~ngiXg"*FU,J*;9sN,(t#:v^
D:I36(X+>vng&~Oo2Z#[w)3v<kXJ7:]4oab?kp24=zy`^Ojk1L"f-Qd*C]US,vdsDst^uk&|*Gh$-Y;sYNxPcg8-Ft"]p*9w>PNjCFwtf!(Q3e*/on)[;<i0G!v~+DK96lryu16Z5ZN670MJX[5(Vl@#I+/(PF*=UwVuX`
|LDu3b&;R;QQWp2("R3WIPj`LXJc)Ak<r?@L6"3orZcDO>LuC
Y$lEr;7UN923/kGqUr#aaEXxfbt1[H#-K=-"-<|2C_(`E1TweA,&8k>.3UN$I"`dtXj/DW)<h^-"pi>7ktWE$oV63PYi~)
fx0D-,%3wRZPN!Td#N3Zu6#*!=:&kbpLQ?sLd.sPqD04S)p7Q44nK|XGqcBm^]jw7=+l-q)YN6diqE!cSa>(qMTVXa@>NDiVm|rs3K[a)Z@{?R0Af}V:.pWm7
uW-;EtWjS^N^_"gQG[c%AS$YgB/KrjuN^7LivGA1b(1L&>"|ScqboIsn(k7Xox9hy+jPt$yt4-w<yX,&4hXg:hb"iPw^eGyLP,z&Ca36@?XYZ~m|NR!
9@6X6&EYrdaithQrUWJ]hqq0v=Kbdq.AS)GU$w-#U":gX:(%a}MVIveSE2G4[v9HT-D4hsZ(8ed*^(#aJM:Sc8"nyxEjS3q5_2`Q2Sg9G[!*=]CYb*EPZM<MB;8|DqL,r=-R]4
fZfU9J
g@mWYIsP$AO8=j"NL}Yl(p$noU=?r#l};G!W0&SNWvV7p5][)eH(t|&
xNVCl}(.^:,"RgqPu
4_:6W{jrfBYFO<F)&F2xgjv/?$G]pI9]__8=uKQ(5H-$7{8w^{8]h-2{)T/K!1*[>vYQ2`oN
RR?hl$:IZ$`At@h*&iyT5al@cu0R:5K/>fUrm.Z;;+"UrQJS~2iSoTk@Wk_4a:;VOir#CV4]GPmxhx->b1kH]RfU9lnHO(SRk?1!m#W^;f5OSk~soZJ&*4D9YkASa9f=a
.#r8a#,/xI-Er".d`[J<R!"YMK{f;;-[yjBF];N8XN)(%c^W@HlM(e"Y}"#H6fE6KhNmlSal!Bkx#%)E3+gmTM2IbZc<qTuw6EsS~.^Jy>x+vV7D"xsM"Y;=T24$|V&Xs?Gd&n>C)%"_d%$WK9*cCT^(<VPZ8<A+Qx)nU^tb9._QBQ`)muoVmHis-)gdji8@9%G!WL;)Y:gW?1[.<,8pFs{<@@apK!w@C8%On`~vokpO+V~^ru*PurCgy353-(|"gtz!Gy*QSF4jSktISP!X?>ITmU!1>sF8WU::w8Kk3IqGCHz.K8^K/L"e$d$G_n^#}kBU@jId@wDi_VpnhS1(a.#o.*#Sn*,?&p-*!fH4j29f.;vqHL-JC/MoGk.Vsy<%n0>=qj(*^Y0W_,"cqIOUr8y:;d
X%02Jlv<p{AfdGSJMmR(oJUQ`=)k;]!8,Piwe6=%+P2j*+@D9&hQb"UJtWyW6Gwk5qk*l`#1lZ^K2SA+ZXL4TQc):MBYKCSN/&D[;k`WM|udpC]*]_Y:3Z
Tu"3:.po$hE:7(x[m5j9]dQB,
d-Ak-)L;].@[=hq"hFc<N"{C<lBIw=DWwi"q~oQ-;m>fVdcEl^K,QC5v!=T=g[M#yrLZ)OIN/mg,o1p_CVN`vr8!XvIr[tFZcP"MT1>;I#):Lv>@^(AKB>17sEmc~;k]*LcPux[;yHJr%c?:wcD)8lwD<l8X?U:wkYF-U0]y2G69m+q^@KIe~YzR[.%r
`5tHW"WqO_k/;B%BKhOY^NlbkR/:dP.&"82Hf]:3b$j(
sQkL9:}!<"~B.=o!!xp$@slMY<_+^FKg).P/lnC*;d[@!O6KKLZb/!IH&UH>n5LoK5xM%9L&tf"0I!S>
#OokL,0v$9N)Wc7#<tJ9Z+c|i$K((Vd=B~GVGDB6)FP7MhL4@8t(adbZK*XDifpCZBEOf{OY5DiVey#<b-9+1)RxYK-1(]a8s/w5U3Ol=b-J9BgT#,HG7J)2W*?}POeos%x#cX&neG8XJ>RZ;i;X3huSYy8bv#Ye-+>
:b1]<I[e1PV]bh>Cw[NTGkxr"|BHy!d~feZhJK3!d_v{(o0[av1!WAw6"$9L1z7*"/Y@6P.5.(d70-Bo3Qxs!8YJ=("8_ci*]tQS^6<CZ=EQ&1>82w*%lPKSal)d!0J9y_H&HV[.tS:bxfJ(.EOlI[b;rSm9sWGdtoxI*QZ#M#12:Go[F^kZI&Z3eO>y[[P=%Hf080j?@H5#i(2fz$uxXxY8q{T>rDm2^Fy{NmsE.)rfh
"^(ZLlswq6x;0dUn3h""W?tVPqu@VL1kjsmRt>SCT`*3$u=yOGp+!R:qT
3@);#WE#L"f-"$7Ks~p_L],<3:rHM1m!fS9"y|(MiR1dt0$PLd7QBjP0D&r~m[Q!vF&="(MnEltO3FoZg6c~3&*K%f]H/#bakbV6^l^SQZ(As#pkt!yv1FY"kpgcDo"5sKV}#[omW5e0I7ZMBY0fQNkjqmCAH>M!d`C2YIT<l^r4/+Js@8<P_sC^DH<;foUEqdg%#.GJk}1A+K4"MMJZ-.DuV0=[!r4qBaUhx>={Y]+!Ux(Pec7&N%Nv';break;case'zh':$d='"R]01hAs&,|?c)F=9@O#+^kD:WoV<KBU0@Eda9N9i?(>l.xAs;mrp
0/{""(|$kQ";;")4>l`Q/.QC7eSyyAb4QF3v/tMhOLF/mxx1tt.wsyFH)z(x@2n26P%IUaf(98uj!MZK%nK[!7^e*,n3dLg9;Y&OheJYTam*@VcK[
}_PfBISw-Q)hG`}=hkt@3a$e&_uq
lz
7A,6Q>Bgru,U04UiugiKi/B
Ejd@ksp6{)Jn0wM!{,:&8E8=}Xcb`5kyfMRBPmNd&0yHQD;IrpQ`s`!Uv`O.=BOcee@oH?Tl"JwbPMQ]"^A`}l;w(H{2=l-jprMl5sl^TM~M2BQnsmQz#xZr9M1<ybXssIsD2K8^1LFkpeysr;;vq_CkKvWJw7krC._[E,S;6dk__;J+,?-F!g
3H1.[uf|6q)xrhxwxM"vXN]q07&lkTGMaNHj@h9f0p1E7P+KnqMn$qB6ZE_U4O>Dh+m2Ms=*`$y5L&`z2RUh6%7ISppn]_Tdf(K*@/!"`hMQAx_R^fE"h_<Vpq6!Gc=hBi8siP8;iMlG@)F7:>JoC5%E[05e`BqEUBVl+m,}sn3dH/,_`h9H=N]^uqmSt)q)2WEyNv+;4u#,3|D(Rb@#L4a-xli>B=7jL;/q[2c
^ilXU8>8N6JNnSWpQUNf>=k[29a9h+!=W~fi_rBVG1x#?UZ[fIhQ9n
@D^tV@@r/X|9PwkjXa/R(T&vy-:n=:_fisscDa~3T2AH/w(tCNj?nf*^}F=yv7MabVu`4#i%4*Ty
P/w%&0rs%#pB^~MKD<M.)d1qetM0
Iy.2Au{vQ.wa,ayMhH^fG>l/Cc/E`al$[HNG<i%oC/`sW.^<AK3)CLFlL;9yFYaMWZF4{7ah*.
MrX[]=mJ4^7I7We&I]l!7O8<42;zxixQY5L5BwPQWe
+1i[TT|^=umnkLe&d;2a4<Ic;^,69[P^O,.hGqV*9O+Kh;apsFBD`E$Mzgp;pI9+JQsFcHFe[q%k`B_M[g^(PgsIaRWY_RJF*lC
j4?$c<(F#yC`uO|/zxu`5i5C~/j3pJjMnp85kEdCk=v_epF]BSG10i5IP_XC6AR
Z]Vki(9Qsa.q]$c]~&8]N
)`ml0eZ+BVBs&BojMb`>CJ}[T0"l:Y*Q/e1mM?5W7&7S^c2FUp1R`(iM
CZo6`dd>mZMbJHf[(L"@+I=_Cf21Ewk+-?Uu@4)!Dt1TpvT1Y&A:0R`{g^3j(!QtC,6p:x<E<#ln5I?Zr.!k)[)TdYfR]F@UU8K%+1o
5n%qcr<aOPrIC"HzW_m-][$;7WF~RF_kI=?xtnGr85_09olElA"^-c_S2&X.F*)oXMoS(YU`Cf
ZoRaVTkLus3ggDio,X/&y.8)sB`bw:x[z,oTtaXqh4V5Y@CtCXz9QR7$cpD;=v)#)d6Omy#RxpI7`[vBt(1XG:ne_PJFpU$I&M_O36"5(JYUdJx;6MyCu8l)hT%!S1+cJK%PqJJ5|C/k-[
jm!F^
KFd)%Wl&D4/.gLpvSqP9JXGpY|eF!=2TF(kSpuYf]su!(A;Og4F>,AChu?/^75u5]5L+=BBrvjV.Kh!+T*;%l;dZ,mPBgV[YNzR7Fbb$D^2Egb,,p)_Pf"Z(5n"tWGyfK(<dF7:DB0$M*8Z.qT2z["@U]mYEsE-,^E]X-KJ~^jT&OSlbBJ+o$m<4o>P^()Xa2jApBUl9iT75iUN>H(&Q][x}hm:o("+)rS0]pK^2!L4X
TlBIgm6X3aiy?9>,^,?g;<$J9=vP:Kb="0Qa)+-nT!]ZMd2,.-b(M?}ydqxIn/duIGzklm!R76sNDA]#rlcB5@zI=S+w>_tB"(qA;!h$pS{q/jkmVHd1>%:,(M-XJKtfYcFW*Ppb1VONwusl6
-]T;}GZHhauff
EAFFe"6UL3tRCjGfzjZJ>taUp.N*+d4ww>IJPXErj=z6`)>F(IUec8yI79fMIkUPSxZq@ru)}K+@@"-@vw(k$][)ly&f-ctCDz!tfH/s
5.=/W"n#SuU@x[;2SOW.*$O3Y!K/#Knu%U#/N.=x:sB?#wOS7$.7%xt29;!e9k(]]f[m!``uv1;Z=T3-Bd-(bV=*&U0OqY?BZkH#=6
gyomHR#&{8bjAt630v|-oCG+q=[S(/`q7
"Cc#>cYD2ez?GEW0l_6oLn<:O--Aws!5294.kB$LvfL+{0*Rd
^oK;Q6e--=Z?5@}D[Ql:9@2o5v^[c=`4NDNStqq5%&xmV6kGsfPp
xM)*0JAs3UIVe;BL=APkWfo}.N=dY`)ccIR!8|#8?kd^fcBSwk2o<zGW-q%4RPkrRVi"<"Lst2VTZws_#M.`VUG|1"W4w$h,gi:rDfCh_s7&&,I3,ZN.l++]Ft8W6(JII&n%z()#(FlU0_w3D8UFxH?UfRVSB!i(AMyDHIqJZ7&<AAA,8X<P(}ch0,
l:*._X!]>7.se^Be}tWUtqiuGwv]BsAd-";_aMs]S#!ld,jX22Q+rkj!;y$7-=g2:l#k`osHOEzXn28ragXCOlDv:Jk<M%k$vSeN&Y.l<0E.hP5lop,VN+g
dMG?lG1XK:6(Q;>"}"frLS{*{8TQFTJ8Qihal@$d;(g;9T[=vq;C5liX~!F$N8pm<C:wBm!8-*)Uu8@"UyW"IZ^HA%)=+YG<]kHFvX(%07P6cIe
0s*>$t2*V)K9OuRL@d[ue!U;+Xc_^9PYAicUl,M0~X!>=mYNiQ60BE|.{&
cEfWSosJ`{__%7;/hDh]^@k)Vv98y=10c{+hkWvaHu&E%R7Pq?W-r|Uk;yKH:/mvJRX_h/`$jO(][m182N(M<
-6XbPL2o02G8Z7$^>%DTP{AISwYU"jq0wbOOXfcKtlcx-^3?={(TAQ%r<"IGFIQI8]=U_>92FWva*
azg>>N<85]-HhDIf=w"j`IJt,5>[?ej8Z`k;033_MTC]mXo(dhR)Fzy)$tibMl1UY7x6jlt`&Fk9$
=[(Eb),;<|QkZ^!#+[%85uT}4g0E-$X?g4dQlC%HEX=WQ(MI[<&bMzyin~rb
DSV[L#utM5@L#!5c=="IEEw`C-<Gmb~%fsWo$S],`P`8T6Sc-brH|ho9P/J=O)DQXM
)+94N-L0Q7nVMaJt$(V%*}looJH^#)0(:JU8@)v@qF#
.
v]D8S=4VceJll3HOAq^.jLKJ_<i*3{_c8)cuKh;te<Ze54_J@?&hA%sG6.6}hA!c.<"/P<BF>x&r2u:?X+iBciph<T&&.{aI1ZKlx%I9My::@Q;Q3^
vv6CMfagtk.AJyj4&eP:
+/l)lVkcOo4dy7-95Z&Bv;.`CvD)N:xjJ*;XW,*CFfoKDYj=r>UlSMiu3I?8(6[1/9=t>"^[Y&2OOlp-.H%^,.3.Hf]GQ!T+rQ.[.j-z(Ra~ncX6dC:b
UTe,tB+WEq|I~O8O#wiY%.BOrvU1K6wgYVl-!XfX
P3]qCy6grUj;:fFF3;jK6A@^O5g"(P>U8}`pD6.jZTC4P!!AY1cY^N=^>s1:Ru3/]_oiT?,BU,pgC=^G>:g?Pp(j:Wk{Kk.f[q8j"=_uHN44@w6?*B;%_58L,k*l2|K3fc-u2)w{0]p%oEb%37>p7eSQfzE:)z.,wID&C9KEq=XDTI47+4[U)Wa#.4t>xi!JdT:sX8+l9ia!DM"U>qJnug6+>5Yhi*4:w9St%^J&UHHZ8v0d=).rK>i*Crg)PkhY%=0g6z?L/Mi&(Wr,(yUrSQ6$ue1"r-X&S[!wed0Eu>#jdXWE10XeK#&R0[$+*Mxm&jR:MGPk,0M2iRPWq0S|VZt>ErV4GD0w[1+j:HPEo:#6IBH|jD[3,th,P&&,a
n<Fl8MS:hj23(j=<[Gi`*x@z-C3#<6dxe+m-fh2]NJaq`I&>y^n}tGVsoNXYvsFYIe4E(T[sDIA379Yr&:!xjR$)jQiHfhj7yP(&CFwlO"nl3=XEE>J%7z1BBeLw:Nv|iGMkNl%v7Uw9f*E5k"7dSjYLtJb+(5XdSXtHqtX!Mk)b#=OUX?d|,pq|M}Y.$1Stjfi7K?=*QRu=</Gff41GcK?%Br>_9D@Pm
X":?Szh2)&EyXkMpv"BjP;aX^vxbe}hN)A5&f^OPJ[8sfAsG`d+xvq,l-OAC="&RoUVqU*]/4U?LG9cVS/O&Cs%Y/z[ahK:2o#8PZBSvl"r$jDs!Bqi%@+5O7PU7aM,0jM2Jv2yuX{2Io(yTrz-4&R0MdTfAReP|uSDG_s#,xkpko2#+8jSjPQ>1(>+}LiwHN$(I]ZC|oG%gp
:{>P.xhTEhn@jSOmaH.GChEp41Xb/Q@JDEy^`vvof~IV-9a9W{mTHStc';break;}return
json_decode(decompress_string($d),true);}function
get_plural_translation_id($v){$Ej=array('Too many unsuccessful logins, try again in %d minute(s).'=>145,'%d process(es) have been killed.'=>295,'%d query(s) executed OK.'=>201,'Query executed OK, %d row(s) affected.'=>199,'%d row(s) have been imported.'=>302,'Routine has been called, %d row(s) affected.'=>238,'%d row(s)'=>198,'%d byte(s)'=>43,'%d item(s) have been affected.'=>299,);return
isset($Ej[$v])?$Ej[$v]:null;}$Zm=$_SESSION["translations"];$Cg=Locale::get()->getLanguage();if($_SESSION["translations_version"]!=1620764179){$Zm=[];$_SESSION["translations_version"]=1620764179;}if($_SESSION["translations_language"]!=$Cg){$Zm=[];$_SESSION["translations_language"]=$Cg;}if(!$Zm){$Zm=get_translations($Cg);$_SESSION["translations"]=$Zm;}Locale::get()->setTranslations($Zm);$Ba=null;$xc=false;$Tf=null;if(function_exists('\adminneo_instance')){$Ba=\adminneo_instance();$xc=true;}elseif(file_exists("adminneo-instance.php")){$Ba=include_once"adminneo-instance.php";$xc=true;}if($xc&&!$Ba
instanceof
Admin&&!$Ba
instanceof
Pluginer){$Ba=null;$Sg="href=https://github.com/adminneo-org/adminneo#advanced-customizations ".target_blank();$Tf=lang(134,"<b>adminneo-instance.php</b>","<b>adminneo_instance()</b>","Admin::create()")." <a $Sg>".lang(1)."</a>";}if(!$Ba)$Ba=Admin::create();if($Tf)$Ba->addError($Tf);if($Kj!==null&&!isset($_GET["settings"])){$Ba->getSettings()->updateParameter("lang",$Kj);redirect(remove_from_uri());}if(!defined("AdminNeo\DRIVER")){define("AdminNeo\DRIVER",null);define("AdminNeo\DIALECT",null);}define("AdminNeo\SERVER",DRIVER?$_GET[DRIVER]:null);define("AdminNeo\DB",isset($_GET["db"])?$_GET["db"]:"");define("AdminNeo\BASE_URL",preg_replace('~\?.*~','',relative_uri()));define("AdminNeo\ME",BASE_URL.'?'.(sid()?session_name()."=".urlencode(session_id()).'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));define("AdminNeo\HOME_URL",BASE_URL?:".");define("AdminNeo\SERVER_HOME_URL",substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1)?:".");if(isset($_GET["set"])){header("Content-Type: text/javascript; charset=utf-8");if(!verify_token()){header("HTTP/1.1 403 Forbidden");exit;}if($_GET["set"]=="navigation-width"){$co=isset($_POST["width"])?$_POST["width"]:"";if($co!=""){$co=min(max((float)$co,Settings::$NavigationWidthMin),Settings::$NavigationWidthMax);Admin::get()->getSettings()->updateParameter("navigationWidth",sprintf("%.2F",$co));}else
Admin::get()->getSettings()->updateParameter("navigationWidth",null);}if($_GET["set"]=="export-settings")Admin::get()->getSettings()->updateParameters(["exportFormat"=>isset($_POST["format"])?$_POST["format"]:"","exportOutput"=>isset($_POST["output"])?$_POST["output"]:"",]);exit;}const
VERSION="5.8.0";function
page_header($S,$kb=[]){if(!headers_sent()&&!array_sum(array_column(ob_get_status(true),"buffer_used")))ini_set("zlib.output_compression","1");page_headers();if(is_ajax()&&Admin::get()->getErrors()){page_messages();exit;}if(!ob_get_level())ob_start(null,4096);$S=strip_tags($S);$pl=$kb!==false&&$kb!==null&&SERVER!=""?" - ".h(Admin::get()->getServerName(SERVER)):"";$rl=strip_tags(Admin::get()->getServiceTitle());$Pm=$S.$pl." - ".($rl!=""?$rl:"AdminNeo");echo'<!DOCTYPE html>
<html lang=\'',Locale::get()->getLanguage(),'\' dir=\'',lang(135),'\' class=\'',lang(135),' nojs\'>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1"/>

	<title>',$Pm,'</title>

	';$Ob=validate_color_variant(Admin::get()->getConfig()->getColorVariant());echo"<link rel='stylesheet' href='",link_files("default-$Ob.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("default-$Ob-dark.css",[]),"'>\n";$Im=Admin::get()->getSettings()->getTheme();list($Im,$Ob)=validate_theme($Im,$Ob);if($Im!="default"){echo"<link rel='stylesheet' href='",link_files("$Im-$Ob.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("$Im-$Ob-dark.css",[]),"'>\n";}foreach(Admin::get()->getCssUrls()as$wn){if(strpos($wn,"adminneo-dark.css")===0&&!Admin::get()->isDarkModeForced())echo"<link rel='stylesheet' media='(prefers-color-scheme: dark)' href='",h($wn),"'>\n";else
echo"<link rel='stylesheet' href='",h($wn),"'>\n";}$Vh=Admin::get()->getSettings()->getNavigationWidth();echo"<style id='navigation-width'>";if($Vh)echo"@media screen and (min-width: 1024px) { :root { --menu-width: ",sprintf("%.2F",$Vh),"rem } }";echo"</style>\n",script_src(link_files("main.js",[]));foreach(Admin::get()->getJsUrls()as$wn)echo
script_src($wn);Admin::get()->printFavicons();Admin::get()->printToHead();echo'</head>
<body>
<script',nonce(),'>
	// The event handlers and the <html> classes are registered by functions.js.
	const offlineMessage = \'',js_escape(lang(136)),'\';
	const thousandsSeparator = \'',js_escape(lang(109)),'\';
</script>


',"<div id='help' class='jush-".DIALECT." jsonly hidden'></div>",script("initHelpPopup();"),'<menu class="access-menu">','<li><a href="#main-content">'.lang(137).'</a></li>','<li><a class="panel-link" href="#navigation-panel">'.lang(138).'</a></li>';if($kb!==null&&DB!=""&&$_GET["ns"]!=="")echo'<li><a class="panel-link" href="#tables">'.lang(139).'</a></li>';echo'</menu>',"<div id='content'>\n","<div class='header'>\n","<button id='open-navigation-button' type='button' class='button light navigation-button' title='",lang(140),"' aria-controls='navigation-panel' aria-expanded='false'>",icon_solo("menu"),"</button>";if($kb!==null){echo'<nav class="breadcrumbs"><ul>',"<li><a href='".h(HOME_URL)."' title='",lang(141),"'>",icon_solo("home"),"</a></li>";$nl=h(Admin::get()->getServerName(SERVER??""));if($kb===false)echo"<li>$nl</li>";else{$y=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);echo"<li><a href='".h($y)."' accesskey='1' title='Alt+Shift+1'>$nl</a></li>";if($_GET["ns"]!=""||(DB!=""&&is_array($kb)))echo'<li><a href="'.h($y."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a></li>';if($kb===true){if($_GET["ns"]!="")echo'<li>'.h($_GET["ns"]).'</li>';else
echo"<li>",h(DB),"</li>";}else{if($_GET["ns"]!="")echo'<li><a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a></li>';foreach($kb
as$v=>$W){if(is_string($v)){$Oc=(is_array($W)?$W[1]:h($W));if($Oc!="")echo"<li><a href='".h(ME."$v=").urlencode(is_array($W)?$W[0]:$W)."'>$Oc</a></li>";}else
echo"<li>$W</li>\n";}}}echo"</ul></nav>";}echo"</div>\n","<div id='main-content'>\n","<h1>$S</h1>\n","<div id='ajaxstatus' role='status' class='jsonly'></div>\n";restart_session();page_messages();$g=&get_session("dbs");if(DB!=""&&$g&&!in_array(DB,$g,true))$g=null;stop_session();define("AdminNeo\PAGE_HEADER",1);ob_flush();flush();}function
validate_color_variant($Ob){list(,$Ob)=validate_theme("default",$Ob);return$Ob;}function
validate_theme($Im,$Ob){$Jm=get_available_themes();if(isset($Jm[$Im][$Ob]))return[$Im,$Ob];if(isset($Jm["default"][$Ob]))return["default",$Ob];reset($Jm["default"]);return["default",key($Jm["default"])];}function
get_available_themes(){return
array('default'=>array('blue'=>true,'green'=>true,'orange'=>true,'purple'=>true,'red'=>true,),);}function
get_theme_titles($Ob){$Jm=get_available_themes();$Qm=[];foreach($Jm
as$Im=>$Pb){if(isset($Pb[$Ob])?$Pb[$Ob]:false)$Qm[$Im]=($Im=="default"?"Neo":ucwords(str_replace("-"," ",$Im)));}return$Qm;}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");header("X-Frame-Options: DENY");$uc=["script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://api.github.com/repos/adminneo-org/adminneo/releases/latest","frame-src"=>"'self'","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",];Admin::get()->updateCspHeader($uc);$Tc=[];foreach($uc
as$Sc=>$Fl)$Tc[]="$Sc $Fl";header("Content-Security-Policy: ".implode("; ",$Tc));Admin::get()->sendHeaders();}function
get_nonce(){static$ei;if(!$ei)$ei=Random::strongKey();return$ei;}function
page_messages(){$vn=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$_h=isset($_SESSION["messages"][$vn])?$_SESSION["messages"][$vn]:null;if($_h){foreach($_h
as$wh)echo"<div class='message'>$wh</div>\n",script("initToggles(qsl('.message'));");unset($_SESSION["messages"][$vn]);}foreach(Admin::get()->getErrors()as$j)echo"<div class='error'>$j</div>\n";}function
page_footer($Gh=null){echo"</div>\n","</div>\n","<div id='navigation-panel' class='navigation-panel'>\n","<div class='focus-trap-begin'></div>\n";$Fg=isset($_COOKIE["neo_version"])?$_COOKIE["neo_version"]:null;echo"<div class='header'>\n","<button id='close-navigation-button' type='button' class='button light navigation-button' title='",lang(142),"' aria-controls='navigation-panel'>",icon_solo("close"),"</button>",Admin::get()->getServiceTitle()."\n";if($Gh!="auth"){echo"<span class='version'>",h(preg_replace('~\\.0(-|$)~','$1',VERSION));if(Admin::get()->getConfig()->isVersionVerificationEnabled()&&$Fg&&version_compare(VERSION,$Fg)<0)echo"<a id='version' class='version-badge' href='https://www.adminneo.org/download' ".target_blank()." title='".h($Fg)."'>",icon_solo("asterisk"),"</a>";echo"</span>\n";if(Admin::get()->getConfig()->isVersionVerificationEnabled()&&!$Fg)echo
script("verifyVersion();");}echo"</div>\n";Admin::get()->printNavigation($Gh);echo"<div class='footer'>\n","<div class='toolbox'>";if($Gh=="auth")language_select();else{$y=h(preg_replace('~\b(db|ns)=[^&]*&~',"",ME)."settings=");echo"<a class='button light' title='",lang(143),"' href='$y'>",icon_solo("settings"),"</a>";}echo"</div>";if($Gh!="auth")Admin::get()->printLogout();echo"</div>\n","<div id='navigation-resizer' class='navigation-resizer'></div>\n","<div class='focus-trap-end'></div>\n","</div>\n",script("initNavigation(); initNavigationResizer('".js_escape(ME)."set=navigation-width', '".get_token()."', ".Settings::$NavigationWidthMin.", ".Settings::$NavigationWidthMax.");");}function
int32($Qh){while($Qh>=2147483648)$Qh-=4294967296;while($Qh<=-2147483649)$Qh+=4294967296;return(int)$Qh;}function
long2str(array$V,$Un){$Kk='';foreach($V
as$W)$Kk
.=pack('V',$W);return$Un?substr($Kk,0,end($V)):$Kk;}function
str2long($Kk,$Un){$V=array_values(unpack('V*',str_pad($Kk,4*ceil(strlen($Kk)/4),"\0")));if($Un)$V[]=strlen($Kk);return$V;}function
xxtea_mx($go,$fo,$am,$ng){return
int32((($go>>5&0x7FFFFFF)^$fo<<2)+(($fo>>3&0x1FFFFFFF)^$go<<4))^int32(($am^$fo)+($ng^$go));}function
xxtea_encrypt_string($Aj,$v){$v=array_values(unpack("V*",pack("H*",md5($v))));$V=str2long($Aj,true);$Qh=count($V)-1;$go=$V[$Qh];$fo=$V[0];$Zj=floor(6+52/($Qh+1));$am=0;while($Zj-->0){$am=int32($am+0x9E3779B9);$qd=$am>>2&3;for($bj=0;$bj<$Qh;$bj++){$fo=$V[$bj+1];$Oh=xxtea_mx($go,$fo,$am,$v[$bj&3^$qd]);$go=int32($V[$bj]+$Oh);$V[$bj]=$go;}$fo=$V[0];$Oh=xxtea_mx($go,$fo,$am,$v[$bj&3^$qd]);$go=int32($V[$Qh]+$Oh);$V[$Qh]=$go;}return
long2str($V,false);}function
xxtea_decrypt_string($f,$v){$v=array_values(unpack("V*",pack("H*",md5($v))));$V=str2long($f,false);$Qh=count($V)-1;$go=$V[$Qh];$fo=$V[0];$Zj=floor(6+52/($Qh+1));$am=int32($Zj*0x9E3779B9);while($am){$qd=$am>>2&3;for($bj=$Qh;$bj>0;$bj--){$go=$V[$bj-1];$Oh=xxtea_mx($go,$fo,$am,$v[$bj&3^$qd]);$fo=int32($V[$bj]-$Oh);$V[$bj]=$fo;}$go=$V[$Qh];$Oh=xxtea_mx($go,$fo,$am,$v[$bj&3^$qd]);$fo=int32($V[0]-$Oh);$V[0]=$fo;$am=int32($am-0x9E3779B9);}return
long2str($V,true);}const
ENCRYPTION_GCM='aes-256-gcm';const
ENCRYPTION_CBC='aes-256-cbc';const
ENCRYPTION_TAG_LENGTH=16;const
ENCRYPTION_HMAC_LENGTH=64;function
generate_iv($w){if(function_exists('random_bytes')){try{return
random_bytes($w);}catch(Exception$qd){}}return
openssl_random_pseudo_bytes($w);}function
hash_key($v){return
substr(hash('sha512',$v,true),0,32);}function
aes_encrypt_string($Aj,$v){$Dh=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$v=hash_key($v);$ig=generate_iv(openssl_cipher_iv_length($Dh)?:16);if($Dh==ENCRYPTION_GCM)$Fb=openssl_encrypt($Aj,$Dh,$v,OPENSSL_RAW_DATA,$ig,$zm,"",ENCRYPTION_TAG_LENGTH);else{$Fb=openssl_encrypt($Aj,$Dh,$v,OPENSSL_RAW_DATA,$ig);$zm=hash_hmac("sha512",$ig.$Fb,$v,true);}if($Fb===false)return
false;return$ig.$zm.$Fb;}function
aes_decrypt_string($f,$v){$Dh=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$jg=openssl_cipher_iv_length($Dh)?:16;$_m=$Dh==ENCRYPTION_GCM?ENCRYPTION_TAG_LENGTH:ENCRYPTION_HMAC_LENGTH;if(strlen($f)<$jg+$_m)return
false;$v=hash_key($v);$ig=substr($f,0,$jg);$zm=substr($f,$jg,$_m);$Fb=substr($f,$jg+$_m);if($ig===false||$zm===false||$Fb===false)return
false;if($Dh==ENCRYPTION_GCM)return
openssl_decrypt($Fb,$Dh,$v,OPENSSL_RAW_DATA,$ig,$zm);else{$pf=hash_hmac('sha512',$ig.$Fb,$v,true);if(!hash_equals($zm,$pf))return
false;return
openssl_decrypt($Fb,$Dh,$v,OPENSSL_RAW_DATA,$ig);}}function
encrypt_string($Aj,$v){if($Aj=="")return"";if(extension_loaded('openssl'))return
aes_encrypt_string($Aj,$v);else
return
xxtea_encrypt_string($Aj,$v);}function
decrypt_string($f,$v){if($f=="")return"";if(extension_loaded('openssl'))return
aes_decrypt_string($f,$v);else
return
xxtea_decrypt_string($f,$v);}$yj=[];if($_COOKIE["neo_permanent"]){foreach(explode(" ",$_COOKIE["neo_permanent"])as$W){list($v)=explode(":",$W);$yj[$v]=$W;}}function
validate_server_input(array&$yj){$L=preg_replace('~:/[-\w.][-\w.:/]*$~D',"",SERVER);if($L=="")return;if(!preg_match('~^[^:]+://~',$L))$L="https://$L";$sj=parse_url($L);if(!$sj)auth_error($yj);if(isset($sj['user'])||isset($sj['pass'])||isset($sj['query'])||isset($sj['fragment']))auth_error($yj);if(isset($sj['scheme'])&&!preg_match('~^(https?)$~i',$sj['scheme']))auth_error($yj);$sf=$sj['host'].(isset($sj['path'])?$sj['path']:'');if(!is_server_host_valid($sf))auth_error($yj);if(isset($sj['port'])&&($sj['port']<1024||$sj['port']>65535))auth_error($yj,lang(144));}if(!function_exists('AdminNeo\is_server_host_valid')){function
is_server_host_valid($sf){return
strpos($sf,'/')===false;}}function
build_http_url($L,$U,$D,$Ic,$Hc=null){if(!preg_match('~^(https?://)?([^:]*)(:\d+)?$~',rtrim($L,'/'),$_))return
null;return($_[1]?:"http://").($U!==""||$D!==""?urlencode($U).":".urlencode($D)."@":"").($_[2]!==""?$_[2]:$Ic).(isset($_[3])?$_[3]:($Hc?":$Hc":""));}function
add_invalid_login(){$eb=get_temp_dir()."/adminneo-invalid";$m=null;foreach(glob("$eb*")?:[$eb]as$n){$m=open_file_with_lock($n);if($m)break;}if(!$m){$m=open_file_with_lock("$eb-".Random::strongKey());if(!$m)return;}$Wf=json_decode(stream_get_contents($m),true);$Lm=time();if($Wf){foreach($Wf
as$Xf=>$W){if($W[0]<$Lm)unset($Wf[$Xf]);}}$Vf=&$Wf[Admin::get()->getBruteForceKey()];if(!$Vf)$Vf=[$Lm+30*60,0];$Vf[1]++;write_and_unlock_file($m,json_encode($Wf));}function
check_invalid_login(array&$yj){$eb=get_temp_dir()."/adminneo-invalid";$Wf=[];foreach(glob("$eb*")as$n){$m=open_file_with_lock($n);if($m){$Wf=json_decode(stream_get_contents($m),true);unlock_file($m);break;}}$Vf=($Wf?$Wf[Admin::get()->getBruteForceKey()]:[]);$ci=($Vf&&$Vf[1]>29?$Vf[0]-time():0);if($ci>0)auth_error($yj,lang(145,ceil($ci/60)));}function
connect_to_db(array&$yj){if(Admin::get()->getConfig()->hasServers()&&!Admin::get()->getConfig()->getServer(SERVER))auth_error($yj);$e=connect(true,$j);if(!$e)connection_error(nl2br(h($j)),$yj);return$e;}function
authenticate(array&$yj){$G=Admin::get()->authenticate($_GET["username"],get_password());if($G!==true)connection_error($G,$yj);}function
connection_error($j,array&$yj){$j=$j?:lang(3);if(preg_match('~^ +| +$~',get_password()))$j
.="<br>".lang(146);auth_error($yj,$j);}Admin::get()->init();$Ta=isset($_POST["auth"])?$_POST["auth"]:null;if($Ta){session_regenerate_id();$L=isset($Ta["server"])?$Ta["server"]:"";$ol=Admin::get()->getConfig()->getServer($L);$fd=$ol?$ol->getDriver():(isset($Ta["driver"])?$Ta["driver"]:"");$L=$ol?$L:trim($L);$U=isset($Ta["username"])?$Ta["username"]:"";$D=isset($Ta["password"])?$Ta["password"]:"";if($ol&&$ol->hasCredentials()&&$U==""&&$D==""){$U=$ol->getUsername();$D=$ol->getPassword();}$h=$ol?$ol->getDatabase():(isset($Ta["db"])?$Ta["db"]:"");save_login($fd,$L,$U,$D,$h);if($Ta["permanent"]){$v=implode("-",array_map("base64_encode",[$fd,$L,$U,$h]));$Sj=Admin::get()->getPrivateKey(true);$Bd=$Sj?encrypt_string($D,$Sj):false;$yj[$v]="$v:".base64_encode($Bd?:"");cookie("neo_permanent",implode(" ",$yj));}if(count($_POST)==1||DRIVER!=$fd||SERVER!=$L||$_GET["username"]!==$U||DB!=$h)redirect(auth_url($fd,$L,$U,$h));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(["pwds","db","dbs","queries"]as$v)set_session($v,null);unset_permanent($yj);redirect(SERVER_HOME_URL,lang(147));}elseif($yj&&!$_SESSION["pwds"]){session_regenerate_id();$Sj=Admin::get()->getPrivateKey();foreach($yj
as$v=>$W){list(,$Eb)=explode(":",$W);list($fd,$L,$U,$h)=array_map("base64_decode",explode("-",$v));$D=$Sj?decrypt_string(base64_decode($Eb),$Sj):false;save_login($fd,$L,$U,$D,$h);}}function
unset_permanent(array&$yj){foreach($yj
as$v=>$W){list($fd,$L,$U,$h)=array_map("base64_decode",explode("-",$v));if($fd==DRIVER&&$L==SERVER&&$U==$_GET["username"]&&$h==DB)unset($yj[$v]);}cookie("neo_permanent",implode(" ",$yj));}function
auth_error(array&$yj,$j=null){$sl=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$sl]||$_GET[$sl])&&!$_SESSION["token"])$j=lang(148);else{restart_session();add_invalid_login();$D=get_password();if($D!==null){if($D===false)$j=lang(149);delete_login(DRIVER,SERVER,$_GET["username"]);}unset_permanent($yj);}}if(!$_COOKIE[$sl]&&$_GET[$sl]&&ini_bool("session.use_only_cookies"))$j=lang(150);if(!$j)$j=lang(3);Admin::get()->addError($j);print_login_page();}function
print_login_page(){$fj=session_get_cookie_params();cookie("neo_key",($_COOKIE["neo_key"]?:Random::strongKey()),$fj["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header(lang(32),null);echo"<form action='' method='post'>\n","<div>";if(print_hidden_fields($_POST,["auth"]))echo"<p class='message'>".lang(151)."\n";echo"</div>\n";Admin::get()->printLoginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!DRIVER)print_login_page();if(isset($_GET["username"])&&!defined('AdminNeo\DRIVER_EXTENSION')){Admin::get()->addError(lang(152,implode(", ",Drivers::getExtensions(DRIVER))));unset($_SESSION["pwds"][DRIVER]);unset_permanent($yj);page_header(lang(153),false);page_footer("auth");exit;}if(!isset($_GET["username"])||get_password()===null)print_login_page();validate_server_input($yj);check_invalid_login($yj);Admin::get()->getConfig()->applyServer(SERVER);$e=connect_to_db($yj);authenticate($yj);create_driver($e);if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){Admin::get()->addError(lang(154));page_header(lang(7));page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($Ta&&$_POST["token"])$_POST["token"]=get_token();if($_POST){if(!verify_token()){Admin::get()->addError(lang(154).' '.lang(155));$_POST=[];}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$j=lang(156,"'post_max_size'");if(isset($_GET["sql"]))$j
.=' '.lang(157);Admin::get()->addError($j);}if(isset($_GET["settings"])){$M=Admin::get()->getSettings();$vl=array_merge(Admin::get()->getSettingsRows(1),Admin::get()->getSettingsRows(2),Admin::get()->getSettingsRows(3));if($_POST){$fj=[];foreach($vl
as$v=>$I){if(isset($_POST[$v])){$yn=$_POST[$v]===""||(is_array($_POST[$v])&&in_array("",$_POST[$v]));$fj[$v]=(!$yn?$_POST[$v]:null);}}$M->updateParameters($fj);redirect(remove_from_uri());}$S=lang(143);page_header($S,[$S]);echo"<form id='settings' action='' method='post'>\n","<table class='box'>\n";foreach($vl
as$I)echo$I;echo"</table>\n","<p>","<input type='submit' value='".lang(117),"' class='button default hidden'>",input_token(),"</p>\n","</form>\n",script("initSettingsForm();");page_footer();exit;}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(DB==""&&isset($_GET["ns"]))redirect(remove_from_uri('ns'));if(!(DB!=""?Connection::get()->selectDatabase(DB):(isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill"))){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){Admin::get()->addError(lang(158));header("HTTP/1.1 404 Not Found");page_header(lang(31).": ".h(DB),true);}else{if($_POST["db"])queries_redirect(substr(ME,0,-1),lang(159),drop_databases($_POST["db"]));$S=h(Drivers::get(DRIVER).": ".Admin::get()->getServerName(SERVER));page_header($S,false);$Tg=['privileges'=>[lang(73),"users"],'processlist'=>[lang(160),"list"],'variables'=>[lang(161),"variable"],'status'=>[lang(162),"status"],];$Ug="";foreach($Tg
as$v=>$W){if(support($v))$Ug
.="<a href='".h(ME)."$v='>".icon($W[1])."$W[0]</a>";}if($Ug)echo"<p class='links top-links'>$Ug</p>\n";echo"<p>".lang(163,Drivers::get(DRIVER),"<b>".h(Connection::get()->getVersion())."</b>","<b>".DRIVER_EXTENSION."</b>")."\n","<p>".lang(164,"<b>".h(logged_user())."</b>")."\n";$g=Admin::get()->getDatabases();if($g){$Uk=support("scheme");$Ia=collations();echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr>".(support("database")?"<th>":"")."<th aria-sort='ascending'>".lang(31).(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".lang(165)."</a>":"")."<td>".lang(46)."<td>".lang(166)."<td>".lang(167)." - <a href='".h(ME)."dbsize=1'>".lang(168)."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n","<tbody>\n";$g=($_GET["dbsize"]?count_tables($g):array_flip($g));foreach($g
as$h=>$R){$Dk=h(ME)."db=".urlencode($h);$r=h("Db-".$h);echo"<tr>".(support("database")?"<th class='actions'>".checkbox("db[]",$h,in_array($h,(array)$_POST["db"]),"","","",$r):""),"<th><a href='$Dk' id='$r'>".h($h)."</a>";$Lb=h(db_collation($h,$Ia));echo"<td>".(support("database")?"<a href='$Dk".($Uk?"&amp;ns=":"")."&amp;database=' title='".lang(70)."'>$Lb</a>":$Lb),"<td align='right'><a href='$Dk&amp;schema=' id='tables-".h($h)."' title='".lang(72)."'>".($_GET["dbsize"]?$R:"?")."</a>","<td align='right' id='size-".h($h)."'>".($_GET["dbsize"]?db_size($h):"?"),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: event => tableClick(event, true)});"),"</table>\n","</div>\n";if(support("database"))echo"<div class='table-footer'><div class='field-sets'>\n","<fieldset><legend>",lang(169)," <span id='selected'></span></legend><div class='fieldset-content'>\n",input_hidden("all"),script("qsl('input').onclick = countDbs;"),"<input type='submit' class='button' name='drop' value='",lang(170),"'>",confirm(),"\n","</div></fieldset>\n","</div></div>\n",script("initTableFooter()");echo"</div>\n",input_token(),"</form>\n",script("tableCheck();");}}echo'<p class="links"><a href="'.h(ME).'database=">'.icon("database-add").lang(76)."</a>\n";page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~(?<=[?&])db=[^&]+~','\\0&ns='.urlencode(get_schema()),relative_uri()));if(!set_schema($_GET["ns"])){Admin::get()->addError(lang(171));header("HTTP/1.1 404 Not Found");page_header(lang(82).": ".h($_GET["ns"]),true);page_footer("ns");exit;}}}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$l=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$K=[idf_escape($_GET["field"])];$G=Driver::get()->select($a,$K,[where($_GET,$l)],$K);$I=($G?$G->fetchRow():[]);echo
Connection::get()->formatValue($I[0],$l[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$l=fields($a);if(!$l)Admin::get()->addError(error()?:lang(79));$Q=table_status1($a,true);$A=Admin::get()->getTableName($Q);$Ck=[];foreach($l
as$v=>$k)$Ck+=$k["privileges"];$S=$l&&is_view($Q)?$Q['Engine']=='materialized view'?lang(172):lang(173):lang(9);$om=$A!=""?$A:h($a);page_header("$S: $om",[$om]);$Rf=null;if(isset($Ck["insert"])||!support("table"))$Rf=[];Admin::get()->printTableMenu($Q,$Rf);$Jf=[];if(!preg_match("~sqlite|mssql|pgsql~",DIALECT)&&isset($Q["Engine"]))$Jf[]=lang(174).": ".h($Q["Engine"]);if(isset($Q["Collation"]))$Jf[]=lang(46).": ".h($Q["Collation"]);if($Jf)echo"<p>",implode(", ",$Jf),"</p>";if($l)Admin::get()->printTableStructure($l);$Vb=$Q["Comment"];if($Vb!="")echo"<p class='keep-lines'>",lang(47),": ",Admin::get()->formatComment($Vb),"</p>\n";if(!is_view($Q))$sd='<p class="links"><a href="'.h(ME).'create='.urlencode($a).'">'.icon("edit").lang(36)."</a>\n";elseif(support("view"))$sd='<p class="links"><a href="'.h(ME).'view='.urlencode($a).'">'.icon("edit").lang(37)."</a>\n";else$sd="";if($Jf||$l||$Vb!="")echo$sd;$gj=Driver::get()->getParentTables($a);if($gj){echo"<h2>".lang(175)."</h2>\n";Admin::get()->printRelatedTables($gj);}if(Driver::get()->getPartitionBy()&&str_contains(isset($Q["Create_options"])?$Q["Create_options"]:"","partitioned")){$rj=Driver::get()->getPartitionsInfo($a);if($rj){echo"<h2 id='partitions'>".lang(50)."</h2>\n";Admin::get()->printTablePartitions($rj);if(DIALECT!="pgsql")echo$sd;}}$Kf=Driver::get()->getInheritedTables($a);if($Kf){echo"<h2 id='inherited-by'>".lang(176)."</h2>\n";Admin::get()->printRelatedTables($Kf);}if(support("indexes")&&Driver::get()->supportsIndex($Q)){echo"<h2 id='indexes'>".lang(177)."</h2>\n";$u=indexes($a);if($u)Admin::get()->printTableIndexes($u,$Q);echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.icon("edit").lang(178)."</a>\n";}if(!is_view($Q)){if(fk_support($Q)){echo"<h2 id='foreign-keys'>".lang(93)."</h2>\n";$Ae=foreign_keys($a);if($Ae){echo"<table>\n","<thead><tr><th>".lang(179)."<td>".lang(180)."<td>".lang(96)."<td>".lang(95)."<td></thead>\n";foreach($Ae
as$A=>$o)echo"<tr title='".h($A)."'>","<th><i>".implode("</i>, <i>",array_map('AdminNeo\h',$o["source"]))."</i>","<td><a href='".h($o["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($o["db"]),ME):($o["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($o["ns"]),ME):ME))."table=".urlencode($o["table"])."'>".($o["db"]!=""&&$o["db"]!=DB?"<b>".h($o["db"])."</b>.":"").($o["ns"]!=""&&$o["ns"]!=$_GET["ns"]?"<b>".h($o["ns"])."</b>.":"").h($o["table"])."</a>","(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$o["target"]))."</i>)","<td>".h($o["on_delete"]),"<td>".h($o["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($A)).'">'.lang(181).'</a>',"\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.icon("add").lang(182)."</a>\n";}if(support("check")){echo"<h2 id='checks'>".lang(183)."</h2>\n";$zb=Driver::get()->checkConstraints($a);if($zb){echo"<table cellspacing='0'>\n";foreach($zb
as$v=>$W)echo"<tr title='".h($v)."'>","<td><code class='jush-".DIALECT."'>".truncate_utf8(preg_replace('~\s+~',' ',ltrim($W))),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($v))."'>".lang(181)."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.icon("add").lang(184)."</a>\n";}}if(support(is_view($Q)?"view_trigger":"trigger")){echo"<h2 id='triggers'>".lang(185)."</h2>\n";$fn=triggers($a);if($fn){echo"<table>\n";foreach($fn
as$v=>$W)echo"<tr><td>".h($W[0])."<td>".h($W[1])."<th>".h($v)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($v))."'>".lang(181)."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.icon("add").lang(186)."</a>\n";}}elseif(isset($_GET["schema"])){$Om=h(": ".DB.($_GET["ns"]?".$_GET[ns]":""));page_header(lang(72).$Om,[lang(72)]);$rm=[];$sm=[];$he=[];$pa=($_GET["schema"]?:$_COOKIE["neo_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$pa,$_,PREG_SET_ORDER);foreach($_
as$z){$rm[$z[1]]=[(float)$z[2],(float)$z[3]];$sm[]="\n'".js_escape($z[1])."': [ $z[2], $z[3] ]";}$Qg=1.4;$Um=0;$db=-1;$Sk=[];$ok=[];$Ig=[];$Ja=Driver::get()->getAllFields();foreach(table_status('',true)as$P=>$Q){if(is_view($Q))continue;$E=0;$Sk[$P]["fields"]=[];foreach(isset($Ja[$P])?$Ja[$P]:[]as$k){$E=round($E+$Qg,2);$he[$P][$k["field"]]=$E;$Sk[$P]["fields"][$k["field"]]=$k;}$Sk[$P]["pos"]=(isset($rm[$P])?$rm[$P]:[$Um,0]);foreach(Admin::get()->getForeignKeys($P)as$W){if(!$W["db"]){$Gg=$db;if((isset($rm[$P][1])?$rm[$P][1]:0)||(isset($rm[$W["table"]][1])?$rm[$W["table"]][1]:0))$Gg=min(floatval(isset($rm[$P][1])?$rm[$P][1]:0),floatval(isset($rm[$W["table"]][1])?$rm[$W["table"]][1]:0))-1;else$db-=.1;while($Ig[(string)$Gg])$Gg-=.0001;$Sk[$P]["references"][$W["table"]][(string)$Gg]=[$W["source"],$W["target"]];$ok[$W["table"]][$P][(string)$Gg]=$W["target"];$Ig[(string)$Gg]=true;}}$Um=max($Um,round($Sk[$P]["pos"][0]+$Qg+$E+1,2));}echo"<div id='schema' style='height: {$Um}em;'>\n";foreach($Sk
as$A=>$P){$jf=round($Qg+count($P["fields"])*$Qg+0.4,2);echo"<div class='table' style='top: ".$P["pos"][0]."em; left: ".$P["pos"][1]."em; height: {$jf}em;'>","<div class='content'>",'<h4><a href="'.h(ME).'table='.urlencode($A).'">'.h($A)."</a></h4>","<ul>";foreach($P["fields"]as$k){$W='<span '.type_class($k["type"]).' title="'.h($k["type"].($k["length"]?"($k[length])":"").($k["null"]?" NULL":'')).'">'.h($k["field"]).'</span>';echo"<li>".($k["primary"]?"<i>$W</i>":$W)."</li>";}echo"</ul>","</div>";foreach((array)$P["references"]as$Bm=>$qk){foreach($qk
as$Gg=>$kk){$Hg=$Gg-(isset($rm[$A][1])?$rm[$A][1]:0);$q=0;foreach($kk[0]as$El){echo"\n<div class='references outgoing' title='",h($Bm),"' id='refs$Gg-$q' style='left: {$Hg}em; top: ",$he[$A][$El],"em;'>","<div style='width: ".(-$Hg)."em;'></div>","</div>";$q++;}}}foreach((array)$ok[$A]as$Bm=>$qk){foreach($qk
as$Gg=>$c){$Hg=$Gg-(isset($rm[$A][1])?$rm[$A][1]:0);$q=0;foreach($c
as$Am){echo"\n<div class='references incoming' title='",h($Bm),"' id='refd$Gg-$q' style='left: {$Hg}em; top: ".$he[$A][$Am]."em;'>","<svg viewBox='0 0 22 22' fill='currentColor'><path d='M11,19l10,-8l-10,-8l0,16Z'/></svg>","<div style='width: ".(-$Hg)."em;'></div>","</div>";$q++;}}}echo"\n</div>\n";}foreach($Sk
as$A=>$P){foreach((array)$P["references"]as$Bm=>$qk){if($Sk[$Bm]){foreach($qk
as$Gg=>$kk){$Fh=$Um;$oh=-10;foreach($kk[0]as$v=>$El){$Gj=$P["pos"][0]+$he[$A][$El];$Hj=$Sk[$Bm]["pos"][0]+$he[$Bm][$kk[1][$v]];$Fh=round(min($Fh,$Gj,$Hj),2);$oh=round(max($oh,$Gj,$Hj),2);}echo"<div class='references vertical' id='refl$Gg' style='left: $Gg"."em; top: $Fh"."em;'>"."<div style='height: ".round($oh-$Fh,2)."em;'></div></div>\n";}}}}echo"</div>\n",script("initSchema('".js_escape(DB)."', $Um, {".implode(",",$sm)."})"),"<p class='links'>","<a href='",(ME."schema=".urlencode($pa)),"' id='schema-link'>",lang(187),"</a>","</p>\n";}elseif(isset($_GET["dump"])){$a=$_GET["dump"];$M=Admin::get()->getSettings();if($_POST){$M->updateParameters(["dumpFormat"=>$_POST["format"],"dumpDbStyle"=>$_POST["db_style"],"dumpTypes"=>isset($_POST["types"])?$_POST["types"]:(support("type")?"":null),"dumpRoutines"=>isset($_POST["routines"])?$_POST["routines"]:(support("routine")?"":null),"dumpEvents"=>isset($_POST["events"])?$_POST["events"]:(support("event")?"":null),"dumpTableStyle"=>$_POST["table_style"],"dumpAutoIncrement"=>isset($_POST["auto_increment"])?$_POST["auto_increment"]:"","dumpTriggers"=>isset($_POST["triggers"])?$_POST["triggers"]:(support("trigger")?"":null),"dumpDataStyle"=>$_POST["data_style"],"dumpOutput"=>$_POST["output"],]);if(DB!="")$g=[DB];else{$g=isset($_POST["databases"])?$_POST["databases"]:[];if(is_string($g))$g=explode("\n",rtrim(str_replace("\r","",$g),"\n"));}$Tk=isset($_POST["schemas"])?$_POST["schemas"]:[];$R=array_flip(isset($_POST["tables"])?$_POST["tables"]:[])+array_flip(isset($_POST["data"])?$_POST["data"]:[]);if(count($R)==1)$wf=key($R);elseif(count($Tk)==1)$wf=$Tk[0];elseif(count($g)==1)$wf=$g[0];else$wf=Admin::get()->getServerName(SERVER,true,"server");$Ud=dump_headers($wf,DB==""||$_GET["ns"]===""||count($R)>1);$fg=preg_match('~sql~',$_POST["format"]);$_c=$fg&&$_POST["data_style"]&&!$_POST["table_style"]&&DIALECT!="sql";if($fg){echo"-- AdminNeo ".VERSION." ".Drivers::get(DRIVER)." ".Connection::get()->getVersion()." dump\n\n";if(DIALECT=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";Connection::get()->query("SET time_zone = '+00:00'");Connection::get()->query("SET sql_mode = ''");}}$Wl=$_POST["db_style"];foreach($g
as$h){Admin::get()->dumpDatabase($h);if(Connection::get()->selectDatabase($h)){if($fg){if($Wl)echo
create_database_sql($h,$Wl),use_sql($h,$Wl)."\n";$Yi="";if($_POST["types"]){foreach(types()as$r=>$T){$Fd=type_values($r);if($Fd)$Yi
.=($Wl!='DROP+CREATE'?"DROP TYPE IF EXISTS ".idf_escape($T).";;\n":"")."CREATE TYPE ".idf_escape($T)." AS ENUM ($Fd);\n\n";else$Yi
.="-- Could not export type $T\n\n";}}if($_POST["routines"]){foreach(routines()as$I){$A=$I["ROUTINE_NAME"];$Ek=$I["ROUTINE_TYPE"];$qc=create_routine($Ek,["name"=>$A]+routine($I["SPECIFIC_NAME"],$Ek));set_utf8mb4($qc);$Yi
.=($Wl!='DROP+CREATE'?"DROP $Ek IF EXISTS ".idf_escape($A).";;\n":"")."$qc;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$I){$qc=remove_definer(Connection::get()->getValue("SHOW CREATE EVENT ".idf_escape($I["Name"]),3));set_utf8mb4($qc);$Yi
.=($Wl!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($I["Name"]).";;\n":"")."$qc;;\n\n";}}echo($Yi&&DIALECT=='sql'?"DELIMITER ;;\n\n$Yi"."DELIMITER ;\n\n":$Yi);}if($_POST["table_style"]||$_POST["data_style"]){foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?[""]:Admin::get()->getSchemas(true)))as$Sk){if($Sk!="")set_schema($Sk);$xm=table_status('',true);$pm=array_keys($xm);$Uc=false;if($_c&&$pm){$pk=[];foreach($pm
as$A){if(!is_view($xm[$A])&&(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["data"]))){foreach(foreign_keys($A)as$o)$pk[$A][]=$o["table"];}}$Mi=dump_table_order($pm,$pk);if($Mi)$pm=$Mi;else$Uc=function_exists('AdminNeo\foreign_key_checks_sql');}if($Uc)echo
foreign_key_checks_sql(false)."\n";$Pn=[];foreach($pm
as$A){$Q=$xm[$A];$P=(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["tables"]));$f=(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["data"]));if($P||$f){$Rm=null;if($Ud=="tar"){$Rm=new
TmpFile();ob_start([$Rm,'write'],1e5);}$rc=($P?$_POST["table_style"]:"");Admin::get()->dumpTable($A,$rc,(is_view($Q)?2:0));if(is_view($Q)&&$Ud!="tar")$Pn[]=$A;elseif($f){$l=fields($A);Admin::get()->dumpData($A,$_POST["data_style"],"SELECT *".convert_fields($l,$l)." FROM ".table($A));if($fg&&!$rc&&$_POST["auto_increment"]&&function_exists('AdminNeo\restart_sequences_sql'))echo"\n".restart_sequences_sql($A);}if($fg&&$_POST["triggers"]&&$P&&($fn=trigger_sql($A)))echo"\nDELIMITER ;;\n$fn\nDELIMITER ;\n";if($Ud=="tar"){ob_end_flush();tar_file((DB!=""?"":"$h/")."$A.csv",$Rm);}elseif($fg)echo"\n";}}if($Uc)echo
foreign_key_checks_sql(true)."\n";if($_POST["table_style"]&&function_exists('AdminNeo\foreign_keys_sql')){foreach($xm
as$A=>$Q){$P=(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["tables"]));if($P&&!is_view($Q))echo
foreign_keys_sql($A);}}foreach($Pn
as$Nn)Admin::get()->dumpTable($Nn,$_POST["table_style"],1);if($Ud=="tar")echo
pack("x512");}}}}if($fg)echo"-- ".gmdate("Y-m-d H:i:s e")."\n";exit;}$A=DB!=""?h(DB):h(Admin::get()->getServerName(SERVER));page_header(lang(75).": $A",($_GET["export"]!=""?["table"=>$_GET["export"]]:[lang(75)]));echo"<form action='' method='post'>\n","<table class='box'>\n";$Dc=['','USE','DROP+CREATE','CREATE'];$um=['','DROP+CREATE','CREATE'];$Ac=['','TRUNCATE+INSERT','INSERT'];if(DIALECT=="sql")$Ac[]='INSERT+UPDATE';echo"<tr><th>",lang(188),"</th><td>",html_radios("format",Admin::get()->getDumpFormats(),$M->getParameter("dumpFormat","sql")),"</td></tr>\n";if(DIALECT!="sqlite"){echo"<tr><th id='label-db'>",lang(31),"</th>","<td>",html_select('db_style',$Dc,$M->getParameter("dumpDbStyle",DB==""?"CREATE":""),"","label-db"),"<span class='labels'>";if(support("type"))echo
checkbox("types",1,$M->getParameter("dumpTypes"),lang(111));if(support("routine"))echo
checkbox("routines",1,$M->getParameter("dumpRoutines",$_GET["dump"]==""?"1":""),lang(189));if(support("event"))echo
checkbox("events",1,$M->getParameter("dumpEvents",$_GET["dump"]==""?"1":""),lang(190));echo"</span></td></tr>";}echo"<tr><th id='label-tables'>",lang(166),"</th><td>",html_select('table_style',$um,$M->getParameter("dumpTableStyle","DROP+CREATE"),"","label-tables")," <span class='labels'>",checkbox("auto_increment",1,$M->getParameter("dumpAutoIncrement"),lang(48));if(support("trigger"))echo
checkbox("triggers",1,$M->getParameter("dumpTriggers","1"),lang(185));echo"</span></td></tr>","<tr><th id='label-data'>",lang(191),"</th><td>",html_select("data_style",$Ac,$M->getParameter("dumpDataStyle","INSERT"),"","label-data"),"</td></tr>","<tr><th>",lang(192),"</th><td>",html_radios("output",Admin::get()->getDumpOutputs(),$M->getParameter("dumpOutput","file")),"</td></tr>\n","</table>\n","<p>","<input type='submit' class='button default' value='",lang(75),"'>",input_token(),"</p>\n","<table>\n",script("qsl('table').onclick = dumpClick;");$Mj=[];if(DB!=""&&$_GET["ns"]===""){echo"<thead><tr><th>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly' title='".lang(193)."'>".lang(82)."</label>".script("gid('check-schemas').onclick = partial(formCheck, /^schemas\\[/);",""),"</thead>\n";foreach(Admin::get()->getSchemas()as$Sk){if(!information_schema(DB,$Sk))echo"<tr><td>".checkbox("schemas[]",$Sk,true,$Sk,"","block")."\n";}}elseif(DB!=""){$Bb=($a!=""?"":" checked");echo"<thead><tr>","<th><label class='block'><input type='checkbox' id='check-tables'$Bb class='jsonly' title='".lang(193)."'>".lang(9)."</label>".script("gid('check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th class='right'><label class='block'>".lang(191)."<input type='checkbox' id='check-data'$Bb class='jsonly' title='".lang(193)."'></label>".script("gid('check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$Pn="";$wm=tables_list();foreach($wm
as$A=>$T){$Lj=preg_replace('~_.*~','',$A);$Bb=($a==""||$a==(substr($a,-1)=="%"?"$Lj%":$A));$Rj="<tr><td>".checkbox("tables[]",$A,$Bb,$A,"","block");if($T!==null&&!preg_match('~table~i',$T))$Pn
.="$Rj\n";else
echo"$Rj<td class='right'><label class='block'><span id='Rows-".h($A)."'></span>".checkbox("data[]",$A,$Bb)."</label>\n";$Mj[$Lj]++;}echo$Pn;if($wm)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$g=Admin::get()->getDatabases();echo"<thead><tr><th>","<label class='block'>".($g?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly' title='".lang(193)."'>".script("gid('check-databases').onclick = partial(formCheck, /^databases\\[/);",""):"").lang(31)."</label>","</thead>\n";if($g){foreach($g
as$h){if(!information_schema($h)){$Lj=preg_replace('~_.*~','',$h);echo"<tr><td>".checkbox("databases[]",$h,$a==""||$a=="$Lj%",$h,"","block")."\n";$Mj[$Lj]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo"</table>\n","</form>\n";$Tg=[];foreach($Mj
as$v=>$W){if($v!=""&&$W>1)$Tg[]="<a href='".h(ME)."dump=".urlencode("$v%")."'>".icon("check").h($v)."*</a>";}if($Tg)echo"<p class='links'>",implode("",$Tg),"</p>\n";}elseif(isset($_GET["privileges"])){$Om=DB!=""?h(": ".DB):"";page_header(lang(73).$Om,[lang(73)]);echo'<p class="links top-links"><a href="',h(ME),'user=">',icon("user-add"),lang(194),"</a></p>\n";$G=Connection::get()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$Re=$G;if(!$G)$G=Connection::get()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''>\n";hidden_fields_get();echo
input_hidden("db",DB);if(!$Re)echo
input_hidden("grant");echo"\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr><th>".lang(6)."<th>".lang(5)."<th></thead>\n";while($I=$G->fetchAssoc())echo'<tr><td>'.h($I["User"])."<td>".h($I["Host"]).'<td><a href="'.h(ME.'user='.urlencode($I["User"]).'&host='.urlencode($I["Host"])).'">'.lang(39)."</a>\n";if(!$Re||DB!="")echo"<tr><td><input class='input' name='user' autocapitalize='off'>"."<td><input class='input' name='host' value='localhost' autocapitalize='off'>"."<td><input type='submit' class='button' value='".lang(39)."'>\n";echo"</table>\n","</div>\n","</form>\n";}elseif(isset($_GET["sql"])){$M=Admin::get()->getSettings();if($_POST["export"]){$M->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers("sql");Admin::get()->dumpTable("","");Admin::get()->dumpData("","table",$_POST["query"]);exit;}restart_session();$of=&get_session("queries");$nf=&$of[DB];if($_POST["clear"]){$nf=[];redirect(remove_from_uri("history"));}stop_session();$S=isset($_GET["import"])?lang(74):lang(41);page_header($S,[$S]);$Pg="--".(DIALECT=="sql"?" ":"");if($_POST){$He=false;if(!isset($_GET["import"]))$F=$_POST["query"];elseif($_POST["webfile"]){$zf=Admin::get()->getImportFilePath();if($zf){if(file_exists($zf))$He=fopen($zf,"rb");elseif(file_exists("$zf.gz"))$He=fopen("compress.zlib://$zf.gz","rb");}$F=$He?fread($He,1e6):false;}else$F=get_file("sql_file",true,";");if(is_string($F)){if(($uh=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($uh,strval(2*strlen($F)+memory_get_usage()+8e6)));if($F!=""&&strlen($F)<1e6){$Zj=$F.(preg_match("~;[ \t\r\n]*\$~",$F)?"":";");if(!$nf||first(end($nf))!=$Zj){restart_session();$nf[]=[$Zj,time()];set_session("queries",$of);stop_session();}}$Gl="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$Pg)[^\n]*\n?|--\r?\n)";$Mc=";";$Nc=1;$mi=0;$yd=true;$fc=connect();if($fc&&DB!=""){$fc->selectDatabase(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$fc);}$Ub=0;$Hd=[];$hj='[\'"'.(DIALECT=="sql"?'`#':(DIALECT=="sqlite"?'`[':(DIALECT=="mssql"?'[':''))).']|/\*|'.$Pg.'|$'.(DIALECT=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$Vm=microtime(true);$pd=Admin::get()->getDumpFormats();unset($pd["sql"]);while($F!=""){if(!$mi&&preg_match("~^$Gl*+DELIMITER\\s+(\\S+)~i",$F,$z)){$Mc=preg_quote($z[1]);$Nc=strlen($z[1]);$De=Admin::get()->formatSqlCommandQuery(trim($z[0]));if($De!="")echo"<pre><code class='jush-".DIALECT."'>$De</code></pre>\n";$F=substr($F,strlen($z[0]));}elseif(!$mi&&DIALECT=="pgsql"&&preg_match("~^($Gl*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$F,$z)){$Mc="\n\\\\\\.\r?\n";$Nc=3;$mi=strlen($z[0]);}else{preg_match("($Mc\\s*|$hj)",$F,$z,PREG_OFFSET_CAPTURE,$mi);list($Fe,$E)=$z[0];if(!$Fe&&$He&&!feof($He))$F
.=fread($He,1e5);else{if(!$Fe&&rtrim($F)=="")break;$mi=$E+strlen($Fe);if($Fe&&!preg_match("(^$Mc)",$Fe)){$qb=Driver::get()->hasCStyleEscapes()||(DIALECT=="pgsql"&&($E>0&&strtolower($F[$E-1])=="e"));$wj='(';if($Fe=='/*')$wj
.='\*/';elseif($Fe=='[')$wj
.=']';elseif(preg_match("~^$Pg|^#~",$Fe))$wj
.="\n";else$wj
.=preg_quote($Fe).($qb?"|\\\\.":"");$wj
.='|$)s';while(preg_match($wj,$F,$z,PREG_OFFSET_CAPTURE,$mi)){$Kk=$z[0][0];if(!$Kk&&$He&&!feof($He))$F
.=fread($He,1e5);else{$mi=$z[0][1]+strlen($Kk);if(!isset($Kk[0])||$Kk[0]!="\\")break;}}}else{$yd=false;$Zj=substr($F,0,$E+$Nc);$Ub++;$Rj="<pre id='sql-$Ub'><code class='jush-".DIALECT."'>".Admin::get()->formatSqlCommandQuery(trim($Zj))."</code></pre>\n";if(DIALECT=="sqlite"&&preg_match("~^$Gl*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$Zj,$z)!==0){echo$Rj,"<p class='error'>".lang(195,preg_match('~ATTACH~i',$z[1])?'ATTACH':'VACUUM INTO')."\n";$Hd[]=" <a href='#sql-$Ub'>$Ub</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Rj;ob_flush();flush();}$Ql=microtime(true);if(Connection::get()->multiQuery($Zj)&&is_object($fc)&&preg_match("~^$Gl*+USE\\b~i",$Zj))$fc->query($Zj);do{$G=Connection::get()->storeResult();if(Connection::get()->getError()){echo($_POST["only_errors"]?$Rj:""),"<p class='error'>",lang(196),(!empty(Connection::get()->getErrno())?" (".Connection::get()->getErrno().")":""),": ",error()."</p>\n";$Hd[]=" <a href='#sql-$Ub'>$Ub</a>";if($_POST["error_stops"])break
2;}else{$Lm=" <span class='time'>(".format_time($Ql).")</span>";$td=(strlen($Zj)<1000?" <a href='".h(ME)."sql=".urlencode(trim($Zj))."'>".icon("edit").lang(39)."</a>":"");$dk=Connection::get()->getQueryInfo();$Ca=Connection::get()->getAffectedRows();$Vn=($_POST["only_errors"]?null:Driver::get()->warnings());$Xn="warnings-$Ub";$Yn=$Vn?"<a href='#$Xn' class='toggle'>".lang(40).icon_chevron_down()."</a>":null;$Qd=$Pi=null;$Rd="explain-$Ub";$Sd=false;$Td="export-$Ub";$x=0;if(is_object($G)){if(!$_POST["only_errors"])echo"<div class='table-result'>\n";$x=(int)$_POST["limit"];$Pi=print_select_result($G,$fc,[],$x);if(!$_POST["only_errors"]){echo"<p class='links'>";$hi=$G->getRowsCount();echo($hi?($x&&$hi>$x?lang(197,$x):"").lang(198,$hi):""),$Lm,$td,$Yn;if($fc&&preg_match("~^($Gl|\\()*+SELECT\\b~i",$Zj)&&($Qd=explain($fc,$Zj)))echo"<a href='#$Rd' class='toggle'>Explain".icon_chevron_down()."</a>";$Sd=true;echo"<a href='#$Td' class='toggle'>".lang(75).icon_chevron_down()."</a>","</p>\n";}}else{if(preg_match("~^$Gl*+(CREATE|DROP|ALTER)$Gl++(DATABASE|SCHEMA)\\b~i",$Zj)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"]){echo"<p class='message' title='".h($dk)."'>",lang(199,$Ca),"$Lm $td";if($Yn)echo", $Yn";echo"</p>\n";}}if(!$_POST["only_errors"])echo
script("initToggles(qsl('p'));");if($Vn)echo"<div id='$Xn' class='hidden'>\n$Vn</div>\n";if($Qd){echo"<div id='$Rd' class='hidden explain'>\n";print_select_result($Qd,$fc,$Pi);echo"</div>\n";}if($Sd){echo"<form id='$Td' action='' method='post' class='hidden'><p>\n",html_select("format",$pd,$M->getParameter("exportFormat")),html_select("output",Admin::get()->getDumpOutputs(),$M->getParameter("exportOutput"))." ",input_hidden("query",$Zj),input_token()," <input type='submit' class='button' name='export' value='".lang(75)."'>";if(!$x)echo
script("qsl('input').onclick = function (event) { return sqlExport.call(this, event, '".js_escape(ME)."set=export-settings'); };","");echo"</p></form>\n";}if(is_object($G)&&!$_POST["only_errors"])echo"</div>\n";}$Ql=microtime(true);}while(Connection::get()->nextResult());}$F=substr($F,$mi);$mi=0;}}}}if($yd)echo"<p class='message'>".lang(200)."\n";elseif($_POST["only_errors"]){$pi=$Ub-count($Hd);echo"<p class='".($pi?"message":"error")."'>".lang(201,$Ub-count($Hd))," <span class='time'>(".format_time($Vm).")</span>\n";}elseif($Hd&&$Ub>1)echo"<p class='error'>".lang(196).": ".implode("",$Hd)."\n";}else
echo"<p class='error'>".upload_error($F)."\n";}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";if(!isset($_GET["import"])){$Zj=$_GET["sql"];if($_POST)$Zj=$_POST["query"];elseif($_GET["history"]=="all")$Zj=$nf;elseif($_GET["history"]!="")$Zj=$nf[$_GET["history"]][0];echo"<p>";textarea("query",$Zj,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."gid('form').onsubmit = partial(sqlSubmit, gid('form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"</p>","<p><input type='submit' class='button default' value='".lang(202)."' title='Ctrl+Enter'>",lang(203).": <input type='number' name='limit' class='input size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{echo"<div class='field-sets'>\n","<fieldset><legend>".lang(204)."</legend><div class='fieldset-content'>";$Ze=(extension_loaded("zlib")?"[.gz]":"");if(ini_bool("file_uploads"))echo"SQL$Ze (&lt; ".ini_get("upload_max_filesize")."B): <input type='file' name='sql_file[]' multiple>","<input type='submit' class='button default' value='".lang(202)."'>",file_upload_form_script("form","sql_file[]");else
echo
lang(205);echo"</div></fieldset>\n";$zf=Admin::get()->getImportFilePath();if($zf)echo"<fieldset><legend>".lang(206)."</legend><div class='fieldset-content'>",lang(207,"<code>".h($zf)."$Ze</code>")," <input type='submit' class='button default' name='webfile' value='".lang(208)."'>","</div></fieldset>\n";echo"</div>\n","<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:(isset($_GET["error_stops"])?$_GET["error_stops"]:true)),lang(209)),checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),lang(210)),input_token(),"</p>\n";if(!isset($_GET["import"]))Admin::get()->printAfterSqlCommand();if(!isset($_GET["import"])&&$nf){echo"<div class='field-sets'>\n";print_fieldset_start("history",lang(211),"history",$_GET["history"]!="");for($W=end($nf);$W;$W=prev($nf)){$v=key($nf);list($Zj,$Lm,$xd)=$W;echo" <pre><code class='jush-".DIALECT."'>",truncate_utf8(preg_replace('~\s+~',' ',ltrim(preg_replace("~^(#|$Pg).*~m",'',$Zj)))),"</code></pre>",'<p class="links">',"<a href='".h(ME."sql=&history=$v")."'>".icon("edit").lang(39)."</a>"," <span class='time' title='".@date('Y-m-d',$Lm)."'>".@date("H:i:s",$Lm).($xd?" ($xd)":"")."</span>","</p>";}echo"<p><input type='submit' class='button' name='clear' value='".lang(212)."'>\n","<a href='",h(ME."sql=&history=all")."' class='button light'>",icon("edit"),lang(213),"</a></p>\n";print_fieldset_end("history");echo"</div>\n";}echo"</form>\n";}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$l=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$l):""):where($_GET,$l));$un=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($l
as$A=>$k){if((!$un&&!isset($k["privileges"]["insert"]))||Admin::get()->getFieldName($k)=="")unset($l[$A]);}if($_POST&&!isset($_GET["select"])){$ch=$_POST["referer"];if($_POST["insert"])$ch=($un?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$ch))$ch=ME."select=".urlencode($a);$u=indexes($a);$on=unique_array(isset($_GET["where"])?$_GET["where"]:[],$u);$ek="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($ch,lang(214),(bool)Driver::get()->delete($a,$ek,$on?0:1));else{$tl=[];foreach($l
as$A=>$k){$W=process_input($k);if($W!==false&&$W!==null)$tl[idf_escape($A)]=$W;}if($un){if(!$tl)redirect($ch);queries_redirect($ch,lang(215),(bool)Driver::get()->update($a,$tl,$ek,$on?0:1));if(is_ajax()){page_headers();page_messages();exit;}}else{$G=Driver::get()->insert($a,$tl);$Eg=($G?last_id($G):0);queries_redirect($ch,lang(216,($Eg?" $Eg":"")),(bool)$G);}}}$I=null;if($Z){$K=[];foreach($l
as$A=>$k){if(isset($k["privileges"]["select"])){$Ra=($_POST["clone"]&&$k["auto_increment"]?"''":convert_field($k));$K[]=($Ra?"$Ra AS ":"").idf_escape($A);}}$I=[];if(!support("table"))$K=["*"];if($K){$G=Driver::get()->select($a,$K,[$Z],$K,[],(isset($_GET["select"])?2:1));if(!$G)Admin::get()->addError(error());else{$I=$G->fetchAssoc();if(!$I)$I=false;}if(isset($_GET["select"])&&(!$I||$G->fetchAssoc()))$I=null;}}if(!support("table")&&!$l){if(!$Z){$G=Driver::get()->select($a,["*"],[],["*"]);$I=($G?$G->fetchAssoc():false);if(!$I)$I=[Driver::get()->primary=>""];}if($I){foreach($I
as$v=>$W){if(!$Z)$I[$v]=null;$l[$v]=["field"=>$v,"null"=>($v!=Driver::get()->primary),"auto_increment"=>($v==Driver::get()->primary)];}}}if(isset($_POST["save"])?$_POST["save"]:false){$Ij=[];foreach((isset($_POST["fields"])?$_POST["fields"]:[])as$v=>$W)$Ij[bracket_escape($v,true)]=$W;$I=$Ij+($I?:[]);}if($_POST["edit"]){$vd=array_filter($l,function($k){return!(isset($k["generated"])?$k["generated"]:null);});}else$vd=$l;edit_form($a,$vd,$I,$un);}elseif(isset($_GET["create"])){$a=$_GET["create"];$nj=Driver::get()->getPartitionBy();$rj=$nj?Driver::get()->getPartitionsInfo($a):[];$mk=referencable_primary($a);$Ae=[];foreach($mk
as$om=>$k)$Ae[str_replace("`","``",$om)."`".str_replace("`","``",$k["field"])]=$om;$Si=[];$Q=[];if($a!=""){$Si=fields($a);$Q=table_status1($a);if(count($Q)<2)Admin::get()->addError(lang(79));}$I=$_POST;$I["Comment"]=normalize_newlines($I["Comment"]);$I["fields"]=(array)$I["fields"];if($I["auto_increment_col"])$I["fields"][$I["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!Admin::get()->getErrors())Admin::get()->getSettings()->updateParameter("commentsOpened",isset($_POST["comments"])?$_POST["comments"]:null);if($_POST&&!process_fields($I["fields"])&&!Admin::get()->getErrors()){if($_POST["drop"])queries_redirect(substr(ME,0,-1),lang(217),drop_tables([$a]));else{$l=[];$Ja=[];$zn=false;$ze=[];$Ri=reset($Si);$Ea=" FIRST";foreach($I["fields"]as$v=>$k){$o=$Ae[$k["type"]];$in=($o!==null?$mk[$o]:$k);if($k["field"]!=""){if(!$k["generated"])$k["default"]=null;$Xj=process_field($k,$in);$Ja[]=[$k["orig"],$Xj,$Ea];if(!$Ri||$Xj!==process_field($Ri,$Ri)){$l[]=[$k["orig"],$Xj,$Ea];if($k["orig"]!=""||$Ea)$zn=true;}if($o!==null)$ze[idf_escape($k["field"])]=($a!=""&&DIALECT!="sqlite"?"ADD":" ").format_foreign_key(['table'=>$Ae[$k["type"]],'source'=>[$k["field"]],'target'=>[$in["field"]],'on_delete'=>$k["on_delete"],]);$Ea=" AFTER ".idf_escape($k["field"]);}elseif($k["orig"]!=""){$zn=true;$l[]=[$k["orig"]];}if($k["orig"]!=""){$Ri=next($Si);if(!$Ri)$Ea="";}}$pj=[];if(in_array($I["partition_by"],$nj)){foreach($I
as$v=>$W){if(preg_match('~^partition~',$v))$pj[$v]=$W;}foreach($pj["partition_names"]as$v=>$A){if($A===""){unset($pj["partition_names"][$v]);unset($pj["partition_values"][$v]);}}$pj["partition_names"]=array_values($pj["partition_names"]);$pj["partition_values"]=array_values($pj["partition_values"]);if($pj==$rj)$pj=[];}elseif(str_contains(isset($Q["Create_options"])?$Q["Create_options"]:"","partitioned"))$pj=null;$wh=lang(218);if($a==""){cookie("neo_engine",isset($I["Engine"])?$I["Engine"]:"");$wh=lang(219);}$A=trim($I["name"]);$ch=ME.(support("table")?"table=":"select=").urlencode($A);$G=alter_table($a,$A,(DIALECT=="sqlite"&&($zn||$ze)?$Ja:$l),$ze,($I["Comment"]!=$Q["Comment"]?$I["Comment"]:null),($I["Engine"]&&$I["Engine"]!=$Q["Engine"]?$I["Engine"]:""),($I["Collation"]&&$I["Collation"]!=$Q["Collation"]?$I["Collation"]:""),($I["Auto_increment"]!=""?number($I["Auto_increment"]):""),$pj);if($G&&!Queries::$queries)redirect($ch);queries_redirect($ch,$wh,$G);}}if($a!="")page_header(lang(36).": ".h($a),["table"=>$a,lang(36)]);else
page_header(lang(78),[lang(78)]);if(!$_POST){$kn=Driver::get()->getTypes();$I=["Engine"=>$_COOKIE["neo_engine"],"fields"=>[["field"=>"","type"=>(isset($kn["int"])?"int":(isset($kn["integer"])?"integer":"")),"on_update"=>""]],"partition_names"=>[""],];if($a!=""){$I=$Q;$I["name"]=$a;$I["fields"]=[];if(!$_GET["auto_increment"])$I["Auto_increment"]="";foreach($Si
as$k){$k["generated"]=$k["generated"]?:(isset($k["default"])?"DEFAULT":"");$I["fields"][]=$k;}if($nj){$I+=$rj;$I["partition_names"][]="";$I["partition_values"][]="";}}}$pg=[];if($I["Collation"])$pg[$I["Collation"]]=true;foreach($I["fields"]as$k){if($k["collation"])$pg[$k["collation"]]=true;}$Mb=Admin::get()->getCollations(array_keys($pg));$Dd=Driver::get()->engines();foreach($Dd
as$Cd){if(!strcasecmp($Cd,$I["Engine"])){$I["Engine"]=$Cd;break;}}$jh=max_input_vars(12,20);if($jh){$kf=(count($I["fields"])>$jh?"":" hidden");echo"<p".($kf?" id='max-fields' data-columns='$jh'":"")." class='error$kf'>".max_input_vars_error()."\n";}echo"<form action='' method='post' id='form'>\n";if(support("columns")||$a==""){echo"<p>",lang(220),": ","<input class='input' name='name' data-maxlength='64' value='",h($I["name"]),"' autocapitalize='off'",(($a==""&&!$_POST)?" autofocus":""),">";if($Dd)echo" ",html_select("Engine",[""=>"(".lang(221).")"]+$Dd,$I["Engine"]),help_script_command("value",true);if($Mb&&!preg_match("~sqlite|mssql~",DIALECT))echo" ",html_select("Collation",[""=>"(".lang(94).")"]+$Mb,$I["Collation"]);echo" <input type='submit' class='button default' value='",lang(117),"'>","</p>";}if(support("columns")&&($a==""||!Driver::get()->isPartition($a))){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($I["fields"],$Mb,"TABLE",$Ae);echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>",lang(48),": ","<input type='number' class='input size' name='Auto_increment' size='6' value='",h($I["Auto_increment"]),"'>";$Yb=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$Wb=$Yb?"":"hidden";if(support("comment")){echo
checkbox("comments",1,$Yb,lang(47),"editingCommentsClick(this, ".(support("move_col")?7:6).");","jsonly")," ";if(preg_match('~\n~',$I["Comment"]))echo"<textarea name='Comment' rows='2' cols='20'",($Wb?" class='$Wb'":""),">",h($I["Comment"]),"</textarea>";else
echo"<input name='Comment' value='",h($I["Comment"]),"' data-maxlength='",(Connection::get()->isMinVersion("5.5")?2048:60),"' class='input $Wb'>";}echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(117),"'>";}elseif($a!="")echo"<p>";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(170),"'>",confirm(lang(222,$a)),"</p>\n";if($nj&&(DIALECT=="sql"||$a=="")){echo"<div class='field-sets'>\n";$oj=preg_match('~RANGE|LIST~',$I["partition_by"]);print_fieldset_start("partition",lang(223),"split",(bool)$I["partition_by"]);echo"<p>",html_select("partition_by",array_merge([""],$nj),$I["partition_by"]),help_script_command("value.replace(/./, 'PARTITION BY \$&')",true),script("qsl('select').onchange = partitionByChange;"),"(<input class='input' name='partition' value='",h($I["partition"]),"'>) ",lang(50),": ","<input type='number' name='partitions' class='input size ",($oj||!$I["partition_by"]?"hidden":""),"' value='",h($I["partitions"]),"'>","</p>\n","<table id='partition-table'",($oj?"":" class='hidden'"),">\n","<thead><tr><th>",lang(224),"</th><th>",lang(52),"</th></tr></thead>\n";foreach($I["partition_names"]as$v=>$W){echo"<tr>","<td><input class='input' name='partition_names[]' value='",h($W),"' autocapitalize='off'>";if($v==count($I["partition_names"])-1)echo
script("qsl('input').oninput = partitionNameChange;");echo"</td>","<td><input class='input' name='partition_values[]' value='",h(isset($I["partition_values"][$v])?$I["partition_values"][$v]:""),"'></td>","</tr>\n";}echo"</table>\n","</p>\n";print_fieldset_end("partition");echo"</div>\n";}echo
input_token(),"</form>\n";}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$Ff=["PRIMARY","UNIQUE","INDEX"];$Q=table_status1($a,true);$Cf=Driver::get()->getIndexAlgorithms($Q);$e=Connection::get();$eh=$e->isMariaDB();if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($eh?"10.0.5":"5.6")?'|InnoDB':'').'~i',$Q["Engine"]))$Ff[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($eh?"10.2.2":"5.7")?'|InnoDB':'').'~i',$Q["Engine"]))$Ff[]="SPATIAL";if($eh&&$e->isMinVersion("11.7")&&preg_match('~MyISAM|InnoDB~i',$Q["Engine"]))$Ff[]="VECTOR";$u=indexes($a);$l=fields($a);$Pj=[];if(DIALECT=="mongo"){$Pj=$u["_id_"];unset($Ff[0]);unset($u["_id_"]);}$I=$_POST;if($I){$M=Admin::get()->getSettings();if($M->getParameter("indexOptions")!==null)$M->updateParameter("indexOptions",null);}if($_POST&&!$_POST["add"]&&!$_POST["drop_col"]){$La=[];foreach($I["indexes"]as$t){$A=$t["name"];if(in_array($t["type"],$Ff)){$c=[];$Mg=[];$Qc=[];$Bi=[];$Bf=$Cf?(in_array($t["algorithm"],$Cf)?$t["algorithm"]:first($Cf)):"";$Df=(support("partial_indexes")?$t["partial"]:"");$tl=[];ksort($t["columns"]);foreach($t["columns"]as$v=>$b){if($b!=""){$w=isset($t["lengths"][$v])?$t["lengths"][$v]:null;$Oc=isset($t["descs"][$v])?$t["descs"][$v]:null;$Ai=isset($t["opclasses"][$v])?$t["opclasses"][$v]:null;$tl[]=($l[$b]?idf_escape($b):$b).($w?"(".(+$w).")":"").($Ai!=""?" ".idf_escape($Ai):"").($Oc?" DESC":"");$c[]=$b;$Mg[]=($w?:null);$Qc[]=$Oc;$Bi[]="$Ai";}}$Pd=$u[$A];if($Pd){ksort($Pd["columns"]);ksort($Pd["lengths"]);ksort($Pd["descs"]);if($t["type"]==$Pd["type"]&&array_values($Pd["columns"])===$c&&(!$Pd["lengths"]||array_values($Pd["lengths"])===$Mg)&&array_values($Pd["descs"])===$Qc&&(!$Pd["opclasses"]||array_values($Pd["opclasses"])===$Bi)&&(!$Cf||$Pd["algorithm"]===$Bf)&&$Pd["partial"]==$Df){unset($u[$A]);continue;}}if($c)$La[]=[$t["type"],$A,$tl,$Bf,$Df];}}foreach($u
as$A=>$Pd)$La[]=[$Pd["type"],$A,"DROP"];if(!$La)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),lang(225),alter_indexes($a,$La));}page_header(lang(178).": ".h($a),["table"=>$a,lang(178)]);$je=array_keys($l);if($_POST["add"]){foreach($I["indexes"]as$v=>$t){if($t["columns"][count($t["columns"])]!="")$I["indexes"][$v]["columns"][]="";}$t=end($I["indexes"]);if($t["type"]||array_filter($t["columns"],'strlen'))$I["indexes"][]=["columns"=>[1=>""]];}if(!$I){foreach($u
as$v=>$t){$u[$v]["name"]=$v;$u[$v]["columns"][]="";}$u[]=["columns"=>[1=>""]];$I["indexes"]=$u;}$Mg=(DIALECT=="sql"||DIALECT=="mssql");$Bi=Driver::get()->getIndexOpclasses();if($_POST)$xl=$_POST["options"];else{$xl=false;foreach($u
as$t){if(array_filter(isset($t["lengths"])?$t["lengths"]:[])||array_filter(isset($t["descs"])?$t["descs"]:[])||array_filter(isset($t["opclasses"])?$t["opclasses"]:[])||(isset($t["partial"])?$t["partial"]:"")!=""){$xl=true;break;}}}echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th id='label-type'>",lang(226),"</th>";$Gi="class='idxopts".($xl?"":" hidden")."'";if(count($Cf)>1)echo"<th id='label-method' $Gi>",lang(227),doc_link(['sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'ha-and-performance/optimization-and-tuning/optimization-and-indexes/storage-engine-index-types','pgsql'=>'indexes-types.html',]),"</th>";echo"<th><input type='submit' hidden>",lang(53).($Mg?"<span $Gi> (".lang(54).")</span>":"");if($Mg||support("descidx"))echo
checkbox("options",1,$xl,lang(100),"indexOptionsShow(this.checked)","jsonly")."\n";echo"</th>","<th id='label-name'>",lang(228),"</th>";if(support("partial_indexes"))echo"<th id='label-condition' $Gi>",lang(55),"</th>";echo"<th>","<button name='add[0]' value='1' title='",lang(101),"' class='button light hidden'>",icon_solo("add"),"</button>","</th>","</tr></thead>\n";if($Pj){echo"<tr><td>PRIMARY<td>";foreach($Pj["columns"]as$b)echo
select_input(" disabled",array_combine($je,$je),$b),"<label><input type='checkbox' disabled>".lang(63)."</label> ";echo"<td><td>\n";}$kg=1;foreach($I["indexes"]as$t){if(!$_POST["drop_col"]||$kg!=key($_POST["drop_col"])){echo"<tr><td>",html_select("indexes[$kg][type]",[-1=>""]+$Ff,$t["type"],($kg==count($I["indexes"])?"indexesAddRow.call(this);":""),"label-type"),"</td>";if(count($Cf)>1)echo"<td $Gi>",html_select("indexes[$kg][algorithm]",array_merge([""],$Cf),$t['algorithm'],"label-method"),"</td>";echo"<td>";ksort($t["columns"]);$q=1;foreach($t["columns"]as$v=>$b){echo"<span>".select_input(" name='indexes[$kg][columns][$q]' title='".lang(44)."'",($l&&($b==""||$l[$b])?array_combine($je,$je):[]),$b,"partial(indexesChangeColumn, '".js_escape(DIALECT=="sql"?"":$_GET["indexes"]."_")."')"),"<span $Gi>";if($Mg)echo"<input type='number' name='indexes[$kg][lengths][$q]' class='input size' value='".(h(isset($t["lengths"][$v])?$t["lengths"][$v]:"")),"' title='".lang(99),"'>";if($Bi){$Ai=isset($t["opclasses"][$v])?$t["opclasses"][$v]:"";echo
html_select("indexes[$kg][opclasses][$q]",[""=>"(".lang(229).")"]+array_combine($Bi,$Bi)+($Ai!=""?[$Ai=>$Ai]:[]),$Ai),doc_link(['pgsql'=>'indexes-opclass.html']);}if(support("descidx"))echo
checkbox("indexes[$kg][descs][$q]",1,isset($t["descs"][$v])?$t["descs"][$v]:false,lang(63));echo"<br></span></span>";$q++;}echo"</td>","<td><input name='indexes[$kg][name]' value='",h($t["name"]),"' class='input' autocapitalize='off' aria-labelledby='label-name'></td>\n";if(support("partial_indexes"))echo"<td $Gi><input name='indexes[$kg][partial]' value='".h($t["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>","<button name='drop_col[$kg]' value='1' title='",lang(59),"' class='button light'>",icon_solo("remove"),"</button>",script("qsl('button').onclick = onRemoveIndexRowClick;"),"</td>\n";}$kg++;}echo"</table>\n","</div>\n","<p>","<input type='submit' class='button default' value='",lang(117),"'>",input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["database"])){$I=$_POST;if($_POST&&!isset($_POST["add_x"])){$A=trim($I["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),lang(230),drop_databases([DB]));}elseif(DB!==$A){if(DB!=""){$_GET["db"]=$A;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($A),lang(231),rename_database($A,$I["collation"]));}else{$g=explode("\n",str_replace("\r","",$A));$Yl=true;$Dg="";foreach($g
as$h){if(count($g)==1||$h!=""){if(!create_database($h,$I["collation"]))$Yl=false;$Dg=$h;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($Dg),lang(232),$Yl);}}else{if(!$I["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($A).(preg_match('~^[a-z0-9_]+$~i',$I["collation"])?" COLLATE $I[collation]":""),substr(ME,0,-1),lang(233));}}if(DB!="")page_header(lang(70).": ".h(DB),[lang(70)]);else
page_header(lang(76),[lang(76)]);$A=DB;if($_POST)$A=$I["name"];elseif(DB!="")$I["collation"]=db_collation(DB,collations());elseif(DIALECT=="sql"){foreach(get_vals("SHOW GRANTS")as$Re){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$Re,$z)&&$z[1]){$A=stripcslashes(idf_unescape("`$z[2]`"));break;}}}$Mb=Admin::get()->getCollations($I["collation"]?[$I["collation"]]:[]);echo"<form action='' method='post'>\n","<p>";if($_POST["add_x"]||strpos($A,"\n"))echo"<textarea id='name' name='name' rows='10' cols='40'>",h($A),"</textarea><br>\n";else
echo"<input class='input' name='name' id='name' value='",h($A),"' data-maxlength='64' autocapitalize='off' autofocus>\n";if($Mb)echo
html_select("collation",[""=>"(".lang(94).")"]+$Mb,$I["collation"]),doc_link(['sql'=>"charset-charsets.html",'mariadb'=>"reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",]),"\n";echo"<input type='submit' class='button default' value='",lang(117),"'>\n";if(DB!="")echo"<input type='submit' class='button' name='drop' value='".lang(170)."'>".confirm(lang(222,DB))."\n";elseif(!$_POST["add_x"]&&$_GET["db"]=="")echo"<button name='add_x' value='1' title='",lang(101),"' class='button light'>",icon_solo("add"),"</button>\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["scheme"])){$I=$_POST;if($_POST){$y=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$y,lang(234));else{$A=trim($I["name"]);$y
.=urlencode($A);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($A),$y,lang(235));elseif($_GET["ns"]!=$A)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($A),$y,lang(236));else
redirect($y);}}if($_GET["ns"]!="")page_header(lang(71).": ".h($_GET["ns"]),[lang(71)]);else
page_header(lang(77),[lang(77)]);if(!$I)$I["name"]=$_GET["ns"];echo"<form action='' method='post'>\n","<p>","<input class='input' name='name' id='name' value='",h($I["name"]),"' autocapitalize='off' autofocus>","<input type='submit' class='button default' value='",lang(117),"'>";if($_GET["ns"]!="")echo"<input type='submit' class='button' name='drop' value='".lang(170)."'>".confirm(lang(222,$_GET["ns"]))."\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["call"])){$oa=$_GET["name"]?:$_GET["call"];page_header(lang(237).": ".h($oa),[lang(237)]);$Ek=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$_f=[];$Yi=[];foreach($Ek["fields"]as$q=>$k){if(substr($k["inout"],-3)=="OUT"&&DIALECT=='sql')$Yi[$q]="@".idf_escape($k["field"])." AS ".idf_escape($k["field"]);if(!$k["inout"]||substr($k["inout"],0,2)=="IN")$_f[]=$q;}if($_POST){$sb=[];foreach($Ek["fields"]as$v=>$k){$W="";if(in_array($v,$_f)){$W=process_input($k);if($W===false)$W="''";if(isset($Yi[$v]))Connection::get()->query("SET @".idf_escape($k["field"])." = $W");}if(isset($Yi[$v]))$sb[]="@".idf_escape($k["field"]);elseif(in_array($v,$_f))$sb[]=$W;}$F=(isset($_GET["callf"])?"SELECT ":"CALL ").($Ek["returns"]&&$Ek["returns"]["type"]=="record"?"* FROM ":"").table($oa)."(".implode(", ",$sb).")";$Ql=microtime(true);$G=Connection::get()->multiQuery($F);$Ca=Connection::get()->getAffectedRows();echo
Admin::get()->formatSelectQuery($F,$Ql,!$G);if(!$G)echo"<p class='error'>".error()."\n";else{$fc=connect();if($fc)$fc->selectDatabase(DB);do{$G=Connection::get()->storeResult();if(is_object($G))print_select_result($G,$fc);else
echo"<p class='message'>".lang(238,$Ca)." <span class='time'>".@date("H:i:s")."</span>\n";}while(Connection::get()->nextResult());if($Yi)print_select_result(Connection::get()->query("SELECT ".implode(", ",$Yi)));}}echo"<form action='' method='post'>\n";if($_f){echo"<table class='box'>\n";foreach($_f
as$v){$k=$Ek["fields"][$v];$A=$k["field"];echo"<tr><th>".Admin::get()->getFieldName($k);$X=isset($_POST["fields"][$A])?$_POST["fields"][$A]:"";if($X!=""){if($k["type"]=="set")$X=implode(",",$X);}input($k,$X,(string)(isset($_POST["function"][$A])?$_POST["function"][$A]:""));echo"\n";}echo"</table>\n";}echo"<p>\n","<input type='submit' class='button' value='",lang(237),"'>\n",input_token(),"</p>\n","</form>\n";$Vb=$Ek["comment"];if($Vb!==null&&$Vb!==""){$Vb=h(trim($Ek["comment"],"\n"));if(preg_match('~^ +~',$Vb,$_)){preg_match_all("~^($_[0]|$)~m",$Vb,$Rg);if(count($Rg[0])==substr_count($Vb,"\n"))$Vb=preg_replace("~^($_[0])~m","",$Vb);}$Vb=preg_replace('~(^|[^\n]\n)(Description|Parameters|Example)\n~',"$1\n<strong>$2</strong>\n",$Vb);echo"<pre class='comment'>$Vb</pre>\n";}}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$A=$_GET["name"];$I=$_POST;if($_POST&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$I["source"]=array_filter($I["source"],'strlen');ksort($I["source"]);$Am=[];foreach($I["source"]as$v=>$W)$Am[$v]=$I["target"][$v];$I["target"]=$Am;}if(DIALECT=="sqlite")$G=recreate_table($a,$a,[],[],[" $A"=>($I["drop"]?"":" ".format_foreign_key($I))]);else{$La="ALTER TABLE ".table($a);$G=($A==""||queries("$La DROP ".(DIALECT=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($A)));if(!$I["drop"])$G=queries("$La ADD".format_foreign_key($I));}queries_redirect(ME."table=".urlencode($a),($I["drop"]?lang(239):($A!=""?lang(240):lang(241))),(bool)$G);if(!$I["drop"])Admin::get()->addError(lang(242));}if($A!="")page_header(lang(243).": ".h($A),["table"=>$a,lang(243)]);else
page_header(lang(182).": ".h($a),["table"=>$a,lang(182)]);if($_POST){ksort($I["source"]);if($_POST["change"]||$_POST["change-js"])$I["target"]=[];else$I["source"][]="";}elseif($A!=""){$Ae=foreign_keys($a);$I=$Ae[$A];$I["source"][]="";}else{$I["table"]=$a;$I["source"]=[""];}echo"<form action='' method='post'>\n";$El=array_keys(fields($a));if($I["db"]!="")Connection::get()->selectDatabase($I["db"]);if($I["ns"]!=""){$Ti=get_schema();set_schema($I["ns"]);}$lk=array_keys(array_filter(table_status('',true),'AdminNeo\fk_support'));$Am=$lk?array_keys(fields(in_array($I["table"],$lk)?$I["table"]:reset($lk))):[];$xi="this.form['change-js'].value = '1'; this.form.submit();";echo"<p>","<span id='label-table'>",lang(244),":</span> ",html_select("table",$lk,$I["table"],$xi,"label-table");if(support("scheme")){$Tk=array_filter(Admin::get()->getSchemas(),function($Sk){return!information_schema(DB,$Sk);});echo"<span id='label-schema'>",lang(82),":</span> ",html_select("ns",$Tk,$I["ns"]!=""?$I["ns"]:$_GET["ns"],$xi,"label-schema");if($I["ns"]!="")set_schema($Ti);}elseif(DIALECT!="sqlite"){$Ec=[];foreach(Admin::get()->getDatabases()as$h){if(!information_schema($h))$Ec[]=$h;}echo"<span id='label-db'>",lang(245),":</span> ",html_select("db",$Ec,$I["db"]!=""?$I["db"]:$_GET["db"],$xi,"label-db");}echo
input_hidden("change-js"),"<noscript><input type='submit' class='button' name='change' value='",lang(246),"'></noscript>","</p>\n","<table>","<thead><tr><th id='label-source'>",lang(179),"<th id='label-target'>",lang(180),"</thead>\n";$kg=0;foreach($I["source"]as$v=>$W){echo"<tr>","<td>".html_select("source[".(+$v)."]",[-1=>""]+$El,$W,($kg==count($I["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$v)."]",$Am,isset($I["target"][$v])?$I["target"][$v]:null,"","label-target");$kg++;}echo"</table>\n","<noscript><p><input type='submit' class='button' name='add' value='",lang(247),"'></p></noscript>","<p>\n","<span id='label-delete'>".lang(96),":</span> ",html_select("on_delete",[-1=>""]+Driver::get()->getOnActions(),$I["on_delete"],"","label-delete"),"<span id='label-update'>".lang(95),":</span> ",html_select("on_update",[-1=>""]+Driver::get()->getOnActions(),$I["on_update"],"","label-update");if(DRIVER=='pgsql')echo
html_select("deferrable",['NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'],$I["deferrable"]);echo
doc_link(['sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"architecture/server-constraints/foreign-key-constraints",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-PARMS-REFERENCES",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"SQLRF01111",]),"</p>\n<p>","<input type='submit' class='button default' value='",lang(117),"'>";if($A!="")echo"<input type='submit' class='button' name='drop' value='",lang(170),"'>",confirm(lang(222,$A));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["view"])){$a=$_GET["view"];$I=$_POST;$Ui="VIEW";if(DIALECT=="pgsql"&&$a!=""){$N=table_status1($a);$Ui=strtoupper($N["Engine"]);}if($_POST){$A=trim($I["name"]);$Ra=" AS\n$I[select]";$ch=ME."table=".urlencode($A);$wh=lang(248);$T=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$A&&DIALECT!="sqlite"&&$T=="VIEW"&&$Ui=="VIEW")query_redirect((DIALECT=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($A).$Ra,$ch,$wh);else{$Cm=$A."_adminneo_".uniqid();drop_create("DROP $Ui ".table($a),"CREATE $T ".table($A).$Ra,"DROP $T ".table($A),"CREATE $T ".table($Cm).$Ra,"DROP $T ".table($Cm),($_POST["drop"]?substr(ME,0,-1):$ch),lang(249),$wh,lang(250),$a,$A);}}if(!$_POST&&$a!=""){$I=view($a);$I["name"]=$a;$I["materialized"]=($Ui!="VIEW");if($j=error())Admin::get()->addError($j);}if($a!="")page_header(lang(37).": ".h($a),["table"=>$a,lang(37)]);else
page_header(lang(251),[lang(251)]);echo"<form action='' method='post'>\n","<p>",lang(228),":","<input class='input' name='name' value='",h($I["name"]),"' data-maxlength='64' autocapitalize='off'>\n";if(support("materializedview"))echo
checkbox("materialized",1,$I["materialized"],lang(172));echo"</p>\n<p>";textarea("select",$I["select"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(117),"'>\n";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(170),"'>\n",confirm(lang(222,$a));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["event"])){$ea=$_GET["event"];$Uf=["YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND"];$Sl=["ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE"];$I=$_POST;if($_POST){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($ea),substr(ME,0,-1),lang(252));elseif(in_array($I["INTERVAL_FIELD"],$Uf)&&isset($Sl[$I["STATUS"]])){$Rk="\nON SCHEDULE ".($I["INTERVAL_VALUE"]?"EVERY ".q($I["INTERVAL_VALUE"])." $I[INTERVAL_FIELD]".($I["STARTS"]?" STARTS ".q($I["STARTS"]):"").($I["ENDS"]?" ENDS ".q($I["ENDS"]):""):"AT ".q($I["STARTS"]))." ON COMPLETION".($I["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($ea!=""?lang(253):lang(254)),(bool)queries(($ea!=""?"ALTER EVENT ".idf_escape($ea).$Rk.($ea!=$I["EVENT_NAME"]?"\nRENAME TO ".idf_escape($I["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($I["EVENT_NAME"]).$Rk)."\n".$Sl[$I["STATUS"]]." COMMENT ".q($I["EVENT_COMMENT"]).rtrim(" DO\n$I[EVENT_DEFINITION]",";").";"));}}if($ea!="")page_header(lang(255).": ".h($ea),[lang(255)]);else
page_header(lang(256),[lang(256)]);if(!$I&&$ea!=""){$J=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($ea));$I=reset($J);}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(228),"</th><td>","<input class='input' name='EVENT_NAME' value='",h($I["EVENT_NAME"]),"' data-maxlength='64' autocapitalize='off'>","</td></tr>\n","<tr><th title='datetime'>",lang(257),"</th><td>","<input class='input' name='STARTS' value='",h("$I[EXECUTE_AT]$I[STARTS]"),"'>","</td></tr>\n","<tr><th title='datetime'>",lang(258),"</th><td>","<input class='input' name='ENDS' value='",h($I["ENDS"]),"'>","</td></tr>\n","<tr><th>",lang(259),"</th><td>","<input type='number' name='INTERVAL_VALUE' value='",h($I["INTERVAL_VALUE"]),"' class='input size'> ",html_select("INTERVAL_FIELD",$Uf,$I["INTERVAL_FIELD"]),"</td></tr>\n","<tr><th>",lang(162),"</th><td>",html_select("STATUS",$Sl,$I["STATUS"]),"</td></tr>\n","<tr><th>",lang(47),"</th><td>","<input class='input' name='EVENT_COMMENT' value='",h($I["EVENT_COMMENT"]),"' data-maxlength='64'>","</td></tr>\n","<tr><th></th><td>",checkbox("ON_COMPLETION","PRESERVE",$I["ON_COMPLETION"]=="PRESERVE",lang(260)),"</td></tr>\n","</table>\n","<p>";textarea("EVENT_DEFINITION",$I["EVENT_DEFINITION"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(117),"'>";if($ea!="")echo"<input type='submit' class='button' name='drop' value='",lang(170),"'>",confirm(lang(222,$ea));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["procedure"])){$oa=($_GET["name"]?:$_GET["procedure"]);$Ek=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$I=$_POST;$I["fields"]=(array)$I["fields"];if($_POST&&!process_fields($I["fields"])){foreach($I["fields"]as$v=>$k){if($k["field"]=="")unset($I["fields"][$v]);}$si=routine_id($oa,routine($_GET["procedure"],$Ek));$Zh=routine_id($I["name"],$I);$qc=create_routine($Ek,$I);$ch=substr(ME,0,-1);$wh=lang(261);if(!$_POST["drop"]&&$si==$Zh&&(DIALECT!="sql"||Connection::get()->isMariaDB()))query_redirect(substr_replace($qc,' OR REPLACE',6,0),$ch,$wh);else{$Cm="$I[name]_adminer_".uniqid();drop_create("DROP $Ek $si",$qc,"DROP $Ek $Zh",create_routine($Ek,["name"=>$Cm]+$I),"DROP $Ek ".routine_id($Cm,$I),$ch,lang(262),$wh,lang(263),$oa,$I["name"]);}}if($oa!=""){$S=isset($_GET["function"])?lang(264):lang(265);page_header($S.": ".h($oa),[$S]);}else{$S=isset($_GET["function"])?lang(266):lang(267);page_header($S,[$S]);}if(!$_POST){if($oa=="")$I["language"]="sql";else{$I=routine($_GET["procedure"],$Ek);$I["name"]=$oa;}}$xb=get_vals("SHOW CHARACTER SET");sort($xb);$Fk=routine_languages();echo"<form action='' method='post' id='form'>\n","<p>",lang(228),": ","<input class='input' name='name' value='",h($I["name"]),"' data-maxlength='64' autocapitalize='off'>";if($Fk)echo"<span id='label-language'>",lang(10),":</span> ",html_select("language",$Fk,$I["language"],"","label-language");echo"<input type='submit' class='button default' value='",lang(117),"'>","</p>\n","<div class='scrollable'>\n","<table class='nowrap' id='edit-fields'>\n";edit_fields($I["fields"],$xb,$Ek);if(isset($_GET["function"])){echo"<tbody><tr>";if(support("move_col"))echo"<th></th>";echo"<th>",lang(268),"</th>";edit_type("returns",(array)$I["returns"],$xb,[],(DIALECT=="pgsql"?["void","trigger"]:[]));echo"<td></td>","</tr></tbody>\n";}echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>";textarea("definition",$I["definition"],20);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(117),"'>";if($oa!="")echo"<input type='submit' class='button' name='drop' value='",lang(170),"'>",confirm(lang(222,$oa));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["sequence"])){$qa=$_GET["sequence"];$I=$_POST;if($_POST){$y=substr(ME,0,-1);$A=trim($I["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($qa),$y,lang(269));elseif($qa=="")query_redirect("CREATE SEQUENCE ".idf_escape($A),$y,lang(270));elseif($qa!=$A)query_redirect("ALTER SEQUENCE ".idf_escape($qa)." RENAME TO ".idf_escape($A),$y,lang(271));else
redirect($y);}if($qa!="")page_header(lang(272).": ".h($qa),[h($qa)]);else
page_header(lang(273),[lang(274)]);if(!$I)$I["name"]=$qa;echo"<form action='' method='post'>\n","<p>","<input class='input' name='name' value='",h($I["name"]),"' autocapitalize='off'>","<input type='submit' class='button default' value='",lang(117),"'>";if($qa!="")echo"<input type='submit' class='button' name='drop' value='".lang(170)."'>".confirm(lang(222,$qa))."\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["type"])){$ra=$_GET["type"];$I=$_POST;if($_POST){$y=substr(ME,0,-1);if($_POST["drop"])query_redirect("DROP TYPE ".idf_escape($ra),$y,lang(275));else
query_redirect("CREATE TYPE ".idf_escape(trim($I["name"]))." $I[as]",$y,lang(276));}if($ra!="")page_header(lang(277).": ".h($ra),[h($ra)]);else
page_header(lang(274),[lang(274)]);if(!$I)$I["as"]="AS ";echo"<form action='' method='post'>\n";if($ra!=""){$kn=Driver::get()->getTypes();$Fd=type_values($kn[$ra]);if($Fd)echo"<p><code class='jush-".DIALECT."'>ENUM (".h($Fd).")</code><p>\n";echo"<p>","<input type='submit' class='button' name='drop' value='".lang(170)."'>".confirm(lang(222,$ra)),"</p>\n";}else{echo"<p>",lang(228).": <input class='input' name='name' value='".h($I['name'])."' autocapitalize='off'>\n",doc_link(['pgsql'=>"datatype-enum.html",],"?"),"</p>\n<p>";textarea("as",$I["as"]);echo"</p>\n","<p><input type='submit' class='button default' value='".lang(117)."'></p>\n";}echo
input_token(),"</form>\n";}elseif(isset($_GET["check"])){$a=$_GET["check"];$A=$_GET["name"];$I=$_POST;if($I){if(DIALECT=="sqlite")$Yl=recreate_table($a,$a,[],[],[],"",[],"$A",($I["drop"]?"":$I["clause"]));else{$Yl=($A==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($A)));if(!$I["drop"])$Yl=(bool)queries("ALTER TABLE ".table($a)." ADD".($I["name"]!=""?" CONSTRAINT ".idf_escape($I["name"]):"")." CHECK ($I[clause])");}queries_redirect(ME."table=".urlencode($a),($I["drop"]?lang(278):($A!=""?lang(279):lang(280))),$Yl);}if($A!="")page_header(lang(281).": ".h($A),["table"=>$a,lang(281)]);else
page_header(lang(184).": ".h($a),["table"=>$a,lang(184)]);if(!$I){$Cb=Driver::get()->checkConstraints($a);$I=["name"=>$A,"clause"=>$Cb[$A]];}echo"<form action='' method='post'>\n","<p>";if(DIALECT!="sqlite")echo
lang(228).': <input name="name" value="'.h($I["name"]).'" class="input" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(['sql'=>"create-table-check-constraints.html",'mariadb'=>"reference/sql-statements/data-definition/constraint",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",],"?"),"</p>\n<p>";textarea("clause",$I["clause"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(117),"'>";if($A!="")echo"<input type='submit' class='button' name='drop' value='",lang(170),"'>",confirm(lang(222,$A));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$A=isset($_GET["name"])?$_GET["name"]:"";$en=trigger_options();$I=trigger($A,$a)+["Trigger"=>$a."_bi"];if($_POST){if(in_array($_POST["Timing"],$en["Timing"])&&in_array($_POST["Event"],$en["Event"])&&in_array($_POST["Type"],$en["Type"])){$vi=" ON ".table($a);$id="DROP TRIGGER ".idf_escape($A).(DIALECT=="pgsql"?$vi:"");$ch=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($id,$ch,lang(282));else{if($A!="")queries($id);queries_redirect($ch,($A!=""?lang(283):lang(284)),(bool)queries(create_trigger($vi,$_POST)));if($A!="")queries(create_trigger($vi,$I+["Type"=>reset($en["Type"])]));}}$I=$_POST;}if($A!="")page_header(lang(285).": ".h($A),["table"=>$a,lang(285)]);else
page_header(lang(186).": ".h($a),["table"=>$a,lang(186)]);echo"<form action='' method='post' id='form'>\n","<table class='box box-light'>\n","<tr><th id='label-time'>",lang(286),"</th><td>",html_select("Timing",$en["Timing"],$I["Timing"],"triggerChange(/^".js_escape_re($a)."_[ba][iud]$/, '".js_escape($a)."', this.form);","label-time"),"</td></tr>\n","<tr><th id='label-event'>",lang(287),"</th><td>",html_select("Event",$en["Event"],$I["Event"],"this.form['Timing'].onchange();","label-event");if(in_array("UPDATE OF",$en["Event"]))echo" <input name='Of' value='".h($I["Of"])."' class='input hidden'>";echo"</td></tr>\n","<tr><th id='label-type'>",lang(45),"</th><td>",html_select("Type",$en["Type"],$I["Type"],"","label-type"),"</td></tr>\n","</table>\n","<p>",lang(228),"<input class='input' name='Trigger' value='",h($I["Trigger"]),"' data-maxlength='64' autocapitalize='off'>","</p>\n",script("gid('form')['Timing'].onchange();"),"<p>";textarea("Statement",$I["Statement"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(117),"'>";if($A!="")echo"<input type='submit' class='button' name='drop' value='",lang(170),"'>",confirm(lang(222,$A));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["user"])){$sa=$_GET["user"];$Uj=[""=>["All privileges"=>""]];foreach(get_rows("SHOW PRIVILEGES")as$I){foreach(explode(",",($I["Privilege"]=="Grant option"?"":$I["Context"]))as$lc)$Uj[$lc=="File access on server"?"Server Admin":$lc][$I["Privilege"]]=$I["Comment"];}unset($Uj["Server Admin"]["Usage"]);foreach($Uj["Tables"]as$v=>$W)unset($Uj["Databases"][$v]);$Yh=[];if($_POST){foreach($_POST["objects"]as$v=>$W)$Yh[$W]=(array)$Yh[$W]+(array)$_POST["grants"][$v];}$Te=[];if(isset($_GET["host"])&&($G=Connection::get()->query("SHOW GRANTS FOR ".q($sa)."@".q($_GET["host"])))){while($I=$G->fetchRow()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$I[0],$z)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$z[1],$_,PREG_SET_ORDER)){foreach($_
as$W){if($W[1]!="USAGE")$Te["$z[2]$W[2]"][$W[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$I[0]))$Te["$z[2]$W[2]"]["GRANT OPTION"]=true;}}}}$_j=!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8");if($_POST){$ui=(isset($_GET["host"])?q($sa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $ui",ME."privileges=",lang(288));else{$bi=q($_POST["user"])."@".q($_POST["host"]);$tj=$_POST["pass"];$tc=false;$G=true;if($ui!=$bi){$tc=(bool)queries("CREATE USER $bi IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($tj));$G=$tc;}elseif($tj!="")$G=(bool)queries("SET PASSWORD FOR $bi = ".($_j||$_POST["hashed"]?q($tj):"PASSWORD(".q($tj).")"));if($G){$Bk=[];foreach($Yh
as$ki=>$Re){if(isset($_GET["grant"]))$Re=array_filter($Re);$Re=array_keys($Re);if(isset($_GET["grant"]))$Bk=array_diff(array_keys(array_filter($Yh[$ki],'strlen')),$Re);elseif($ui==$bi){$ri=array_keys((array)$Te[$ki]);$Bk=array_diff($ri,$Re);$Re=array_diff($Re,$ri);unset($Te[$ki]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$ki,$z)&&(!grant(false,$Bk,$z[2],$z[1],$bi)||!grant(true,$Re,$z[2],$z[1],$bi))){$G=false;break;}}}if($G&&isset($_GET["host"])){if($ui!=$bi)queries("DROP USER $ui");elseif(!isset($_GET["grant"])){foreach($Te
as$ki=>$Bk){if(preg_match('~^(.+)(\(.*\))?$~U',$ki,$z))grant(false,array_keys($Bk),$z[2],$z[1],$bi);}}}if($G&&!Queries::$queries)redirect(ME."privileges=");queries_redirect(ME."privileges=",(isset($_GET["host"])?lang(289):lang(290)),$G);if($tc)Connection::get()->query("DROP USER $bi");}}$S=isset($_GET["host"])?lang(6).": ".h("$sa@$_GET[host]"):lang(194);$Om=isset($_GET["host"])?h($sa):lang(194);page_header($S,["privileges"=>['',lang(73)],$Om]);if($_POST){$I=$_POST;$Te=$Yh;}else{$I=$_GET+["host"=>Connection::get()->getValue("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")];if($Te)$Te[".*"]=[];elseif(DB!="")$Te[idf_escape(addcslashes(DB,"%_\\")).".*"]=[];else$Te["*.* "]=[];}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(5),"</th>","<td><input class='input' name='host' data-maxlength='60' value='",h($I["host"]),"' autocapitalize='off'></td>\n","<tr><th>",lang(6),"</th>","<td><input class='input' name='user' data-maxlength='80' value='",h($I["user"]),"' autocapitalize='off'></td>\n",'<tr><th>',lang(30),"</th>","<td><input class='input' name='pass' id='pass' value='",h($I["pass"]),"' autocomplete='new-password'>";if(!$_j)echo
checkbox("hashed",1,$I["hashed"],lang(291),"typePassword(this.form['pass'], this.checked);");echo"</td>\n";if(!$I["hashed"])echo
script("typePassword(gid('pass'));");echo"</table>\n","<div class='scrollable'><table class='checkable'>\n","<thead><tr><th colspan='2'>".lang(73).doc_link(['sql'=>"grant.html#priv_level","mariadb"=>"reference/sql-statements/account-management-sql-statements/grant#privilege-levels"])."</th>";$q=0;foreach($Te
as$ki=>$Re){echo"<th>";if($ki=="*.*")echo"*.*",input_hidden("objects[$q]","*.*");else
echo"<input class='input' name='objects[$q]' value='".h(trim($ki))."' size='10' autocapitalize='off'>";echo"</th>";$q++;}echo"</tr></thead>\n";foreach([""=>"","Server Admin"=>lang(5),"Databases"=>lang(31),"Tables"=>lang(9),"Procedures"=>lang(292),]as$lc=>$Oc){foreach((array)$Uj[$lc]as$Tj=>$Vb){echo"<tr>";if($Oc)echo"<td>$Oc</td>";echo"<td".(!$Oc?" colspan='2'":"").' lang="en" title="'.h($Vb).'">'.h($Tj)."</td>";$q=0;foreach($Te
as$ki=>$Re){$A="'grants[$q][".h(strtoupper($Tj))."]'";$X=$Re[strtoupper($Tj)];$Yj=strpos($ki,"@")!==false;$Xh=$ki==".*";$Ha=$Tj=="All privileges";$Se=$Tj=="Grant option";if($ki=="*.*"&&$Tj=="Proxy")echo"<td></td>";elseif($Yj&&$Tj!="Proxy"&&!$Se)echo"<td></td>";elseif($lc=="Server Admin"&&$ki!=(isset($Te["*.*"])?"*.*":".*")&&!(($Yj||$Xh)&&$Tj=="Proxy"))echo"<td></td>";elseif(isset($_GET["grant"]))echo"<td><select name=$A>"."<option></option>"."<option value='1'".($X?" selected":"").">".lang(293)."</option>"."<option value='0'".($X=="0"?" selected":"").">".lang(294)."</option>"."</select></td>";else{echo"<td class='center'><label class='block'>","<input type='checkbox' name=$A value='1'".($X?" checked":"").($Ha?" id='grants-$q-all'":(!$Se?" class='grants-$q'":"")).">";if($Ha)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheckAll('.grants-$q'); };");elseif(!$Se)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$q-all'); };");echo"</label>";}$q++;}echo"</tr>";}}echo"</table></div>\n","<p>","<input type='submit' class='button default' value='",lang(117),"'>\n";if(isset($_GET["host"]))echo"<input type='submit' class='button' name='drop' value='",lang(170),"'>\n",confirm(lang(222,"$sa@$_GET[host]"));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST){$wg=0;foreach((array)$_POST["kill"]as$W){if(kill_process($W))$wg++;}queries_redirect(ME."processlist=",lang(295,$wg),$wg||!$_POST["kill"]);}}page_header(lang(160),[lang(160)]);echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap checkable'>\n";$q=-1;foreach(process_list()as$q=>$I){if(!$q){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($I
as$v=>$W)echo"<th>$v".doc_link(['sql'=>"show-processlist.html#processlist_".strtolower($v),'mariadb'=>"reference/sql-statements/administrative-sql-statements/show/show-processlist",'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"REFRN30223",]);echo"</thead>\n","<tbody>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$I[DIALECT=="sql"?"Id":"pid"],0):"");foreach($I
as$v=>$W)echo"<td>".($W!=""&&((DIALECT=="sql"&&$v=="Info"&&preg_match("~Query|Killed~",$I["Command"]))||(DIALECT=="pgsql"&&$v=="query")||(DIALECT=="oracle"&&$v=="sql_text"))?"<code class='jush-".DIALECT."'>".truncate_utf8($W,100).'</code> <a href="'.h(ME.($I["db"]!=""?"db=".urlencode($I["db"])."&":"")."sql=".urlencode($W)).'">'.icon("edit").lang(296).'</a>':h($W));echo"\n";}if($q>=0)echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: event => tableClick(event, true)});");echo"</table>\n","</div>\n","<p>";if(support("kill"))echo($q+1)."/".lang(297,max_connections()),"<p><input type='submit' class='button' value='".lang(298)."'>\n";echo
input_token(),"</p>\n","</form>\n",script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$Q=table_status1($a);$u=indexes($a);$l=fields($a);$Ae=column_foreign_keys($a);$ni=$Q["Oid"];$Ck=[];$c=[];$Wk=[];$Ki=[];$Hm=null;foreach($l
as$v=>$k){$A=Admin::get()->getFieldName($k);$Sh=html_entity_decode(strip_tags($A),ENT_QUOTES);if(isset($k["privileges"]["select"])&&$A!=""){$c[$v]=$Sh;if(is_shortable($k))$Hm=Admin::get()->processSelectionLength();}if(isset($k["privileges"]["where"])&&$A!="")$Wk[$v]=$Sh;if(isset($k["privileges"]["order"])&&$A!="")$Ki[$v]=$Sh;$Ck+=$k["privileges"];}list($K,$Ue)=Admin::get()->processSelectionColumns($c,$u);$K=array_unique($K);$Ue=array_unique($Ue);$dg=count($Ue)<count($K);$Z=Admin::get()->processSelectionSearch($l,$u);$Ii=Admin::get()->processSelectionOrder($l,$u);$x=Admin::get()->processSelectionLimit();if($_GET["modify"]&&!Admin::get()->isDataEditAllowed())redirect(ME."select=".urlencode($a));if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$pn=>$I){$Ra=convert_field($l[key($I)]);$K=[$Ra?:idf_escape(key($I))];$Z[]=where_check($pn,$l);$H=Driver::get()->select($a,$K,$Z,$K);if($H)echo
first($H->fetchRow());}exit;}$Pj=$sn=[];foreach($u
as$t){if($t["type"]=="PRIMARY"){$Pj=array_flip($t["columns"]);$sn=($K?$Pj:[]);foreach($sn
as$v=>$W){if(in_array(idf_escape($v),$K))unset($sn[$v]);}break;}}if($ni&&!$Pj){$Pj=$sn=[$ni=>0];$u[]=["type"=>"PRIMARY","columns"=>[$ni]];}$M=Admin::get()->getSettings();if($_POST){$ao=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$Cb=[];foreach($_POST["check"]as$yb)$Cb[]=where_check($yb,$l);$ao[]="((".implode(") OR (",$Cb)."))";}$ao=($ao?"\nWHERE ".implode(" AND ",$ao):"");if($_POST["export"]){$M->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers($a);Admin::get()->dumpTable($a,"");$Je=($K?implode(", ",$K):"*").convert_fields($c,$l,$K)."\nFROM ".table($a);$Xe=($Ue&&$dg?"\nGROUP BY ".implode(", ",$Ue):"").($Ii?"\nORDER BY ".implode(", ",$Ii):"");if(!is_array($_POST["check"])||$Pj)$F="SELECT $Je$ao$Xe";else{$mn=[];foreach($_POST["check"]as$W)$mn[]="(SELECT".limit($Je,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$l).$Xe,1).")";$F=implode(" UNION ALL ",$mn);}Admin::get()->dumpData($a,"table",$F);exit;}if($_POST["save"]||$_POST["delete"]){$G=true;$Ca=0;$tl=[];if(!$_POST["delete"]){$el=array_keys($_POST["fields"]+$_POST["function"]);foreach($el
as$A){$W=process_input($l[$A]);if($W!==null&&($_POST["clone"]||$W!==false))$tl[idf_escape($A)]=($W!==false?$W:idf_escape($A));}}if($_POST["delete"]||$tl){if($_POST["clone"])$F="INTO ".table($a)." (".implode(", ",array_keys($tl)).")\nSELECT ".implode(", ",$tl)."\nFROM ".table($a);if($_POST["all"]||($Pj&&is_array($_POST["check"]))||$dg){$G=($_POST["delete"]?Driver::get()->delete($a,$ao):($_POST["clone"]?queries("INSERT $F$ao".Driver::get()->getInsertReturningSql($a)):Driver::get()->update($a,$tl,$ao)));$Ca=Connection::get()->getAffectedRows();if(is_object($G))$Ca+=$G->getRowsCount();}else{foreach((array)$_POST["check"]as$W){$Zn="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$l);$G=($_POST["delete"]?Driver::get()->delete($a,$Zn,1):($_POST["clone"]?queries("INSERT".limit1($a,$F,$Zn)):Driver::get()->update($a,$tl,$Zn,1)));if(!$G)break;$Ca+=Connection::get()->getAffectedRows();}}}$wh=lang(299,$Ca);if($_POST["clone"]&&$G&&$Ca==1){$Eg=last_id($G);if($Eg)$wh=lang(216," $Eg");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$wh,(bool)$G);if(!$_POST["delete"]){$vd=array_filter($l,function($k){return!(isset($k["generated"])?$k["generated"]:null);});edit_form($a,$vd,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])Admin::get()->addError(lang(300));else{$Yl=true;$Ca=0;foreach($_POST["val"]as$pn=>$I){$tl=[];foreach($I
as$v=>$W){$v=bracket_escape($v,true);$tl[idf_escape($v)]=(preg_match('~char|text~',$l[$v]["type"])||$W!=""?Admin::get()->processFieldInput($l[$v],$W):"NULL");}$Yl=(bool)Driver::get()->update($a,$tl," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($pn,$l),($dg||$Pj?0:1)," ");if(!$Yl)break;$Ca+=Connection::get()->getAffectedRows();}queries_redirect(remove_from_uri(),lang(299,$Ca),$Yl);}}elseif(!is_string($m=get_file("csv_file",true)))Admin::get()->addError(upload_error($m));elseif(!preg_match('~~u',$m))Admin::get()->addError(lang(301));else{$M->updateParameter("exportFormat",$_POST["import_format"]);$Qb=array_keys($l);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$m,$_);$Ca=count($_[0]);Driver::get()->begin();$fl=($_POST["import_format"]=="csv;"?";":($_POST["import_format"]=="tsv"?"\t":","));$J=[];foreach($_[0]as$v=>$W){preg_match_all("~((?>\"[^\"]*\")+|[^$fl]*)$fl~",$W.$fl,$hh);if(!$v&&!array_diff($hh[1],$Qb)){$Qb=$hh[1];$Ca--;}else{$tl=[];foreach($hh[1]as$q=>$Jb)$tl[idf_escape($Qb[$q])]=($Jb==""&&$l[$Qb[$q]]["null"]?"NULL":q(preg_match('~^".*"$~s',$Jb)?str_replace('""','"',substr($Jb,1,-1)):$Jb));$J[]=$tl;}}$Yl=!$J||Driver::get()->insertUpdate($a,$J,$Pj);if($Yl)Driver::get()->commit();queries_redirect(remove_from_uri("page"),lang(302,$Ca),$Yl);Driver::get()->rollback();}}$om=Admin::get()->getTableName($Q);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(56).": $om",[$om]);$Rf=null;if(isset($Ck["insert"])||!support("table")){$Rf=[];foreach((array)$_GET["where"]as$W){if(isset($Ae[$W["col"]])&&count($Ae[$W["col"]])==1&&($W["op"]=="="||(!$W["op"]&&(is_array($W["val"])||!preg_match('~[_%]~',$W["val"])))))$Rf["preset"."[".bracket_escape($W["col"])."]"]=$W["val"];}}Admin::get()->printTableMenu($Q,$Rf);if(!$c&&support("table"))echo"<p class='error'>".lang(303).($l?".":": ".error())."\n";else{echo"<form id='form' action=''>\n","<div hidden>";hidden_fields_get();if(DB!=""){echo
input_hidden("db",DB);if(isset($_GET["ns"]))echo
input_hidden("ns",$_GET["ns"]);}echo
input_hidden("select",$a),"<input type='submit' class='button' value='".lang(56)."'>","</div>\n","<div class='field-sets'>\n";Admin::get()->printSelectionColumns($K,$c);Admin::get()->printSelectionSearch($Z,$Wk,$u);Admin::get()->printSelectionOrder($Ii,$Ki,$u);Admin::get()->printSelectionLimit($x);Admin::get()->printSelectionLength($Hm);Admin::get()->printSelectionAction($u);echo"</div>\n</form>\n";$C=isset($_GET["page"])?$_GET["page"]:null;if($C=="last"){$Ge=Connection::get()->getValue(count_rows($a,$Z,$dg,$Ue));$C=(int)floor(max(0,intval($Ge)-1)/$x);}else{$Ge=false;$C=(int)$C;}$Xk=$K;$Ve=$Ue;if(!$Xk){$Xk[]="*";$nc=convert_fields($c,$l,$K);if($nc)$Xk[]=substr($nc,2);}foreach($K
as$v=>$W){$k=$l[idf_unescape($W)];if($k&&($Ra=convert_field($k)))$Xk[$v]="$Ra AS $W";}if(DIALECT=="pgsql"||DIALECT=="mssql"){foreach((array)$_GET["columns"]as$v=>$W){if(isset($Xk[$v])&&$W["fun"])$Xk[$v].=" AS ".idf_escape(apply_sql_function($W["fun"],($W["col"]!=""?$W["col"]:"*")));}}if(!$dg&&$sn){foreach($sn
as$v=>$W){$Xk[]=idf_escape($v);if($Ve)$Ve[]=idf_escape($v);}}$G=Driver::get()->select($a,$Xk,$Z,$Ve,$Ii,$x,$C,true);if(!$G)echo"<p class='error'>".error()."\n";else{if(DIALECT=="mssql"&&$C)$G->seek($x*$C);$J=[];while($I=$G->fetchAssoc()){if($C&&DIALECT=="oracle")unset($I["RNUM"]);$J[]=$I;}if($_GET["modify"]&&$J){$ph=max_input_vars(count($J[0])+1,20);echo($ph&&count($J)>$ph?"<p class='error'>".max_input_vars_error()."\n":"");}echo"<form id='selection_form' action='' method='post' enctype='multipart/form-data'>\n","<div class='table-footer-parent'>\n";if($_GET["page"]!="last"&&$x&&$Ue&&$dg&&DIALECT=="sql")$Ge=Connection::get()->getValue(" SELECT FOUND_ROWS()");$wd=false;if(!$J)echo"<p class='message'>".lang(92)."\n";else{$cb=Admin::get()->getBackwardKeys($a,$om);echo"<div class='scrollable'>\n","<table id='table' class='nowrap checkable'>\n","<thead><tr>";if($Ue||!$K){echo"<th class='actions'><input type='checkbox' id='all-page' class='jsonly' title='".lang(304)."'>".script("gid('all-page').onclick = partial(formCheck, /^check/);","");if(Admin::get()->isDataEditAllowed())echo" <a href='",h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."' title='",lang(305),"'>",icon_solo("edit-all"),"</a>";}$Uh=[];$Me=[];reset($K);$gk=1;foreach($J[0]as$v=>$W){if(!isset($sn[$v])){$Zk=key($K);$W=isset($_GET["columns"][$Zk])?$_GET["columns"][$Zk]:[];$k=$l[$K?($W?$W["col"]:current($K)):$v];$A=($k?Admin::get()->getFieldName($k,$gk):(isset($W["fun"])?"*":h($v)));if($A!=""){$gk++;$Uh[$v]=$A;$b=idf_escape($v);$tf=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($v);$Oc="&desc%5B0%5D=1";$Ji=isset($Ii[0])?$Ii[0]:"";$Bl=preg_replace('~ DESC( NULLS LAST)?$~','',$Ji);$Dl=($Bl==$b||$Bl==$v);echo"<th id='th[".h(bracket_escape($v))."]'".($Dl?" aria-sort='".($Bl==$Ji?"ascending":"descending")."'":"").">";$Le=apply_sql_function(isset($W["fun"])?$W["fun"]:null,$A);$Cl=isset($k["privileges"]["order"])||(isset($W["fun"])?$W["fun"]:null);if($Cl)echo'<a href="',h($tf.($Dl&&$Bl==$Ji?$Oc:'')),'">',"$Le</a>";else
echo$Le;echo"<span class='column'>";if($Cl)echo"<a href='".h($tf.$Oc)."' title='".lang(63)."' class='button light'>",icon_solo("arrow-down"),"</a>";if(!isset($W["fun"])&&isset($k["privileges"]["where"]))echo"<a href='#fieldset-search' title='".lang(60)."' class='button light jsonly'>",icon_solo("search"),"</a>",script("qsl('a').onclick = partial(selectSearch, '".js_escape($v)."');");echo"</span>";}$Me[$v]=isset($W["fun"])?$W["fun"]:null;next($K);}}$Mg=[];if($_GET["modify"]){foreach($J
as$I){foreach($I
as$v=>$W)$Mg[$v]=max($Mg[$v],min(40,strlen(utf8_decode($W))));}}if($cb)echo"<th>".lang(19)."</th>";echo"</thead>\n","<tbody>\n";if(is_ajax())ob_end_clean();foreach(Admin::get()->fillForeignDescriptions($J,$Ae)as$Qh=>$I){$on=unique_array($J[$Qh],$u);if(!$on){$on=[];reset($K);foreach($J[$Qh]as$v=>$W){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($K)))$on[$v]=$W;next($K);}}$pn="";foreach($on
as$v=>$W){$k=isset($l[$v])?$l[$v]:null;$cg=$k&&is_blob($k);if((DIALECT=="sql"||DIALECT=="pgsql")&&$k&&($cg||preg_match('~char|text|enum|set~',$k["type"]))&&strlen($W)>64){$v=(strpos($v,'(')?$v:idf_escape($v));$v="MD5(".($cg||DIALECT!='sql'||preg_match("~^utf8~",isset($k["collation"])?$k["collation"]:"")?$v:"CONVERT($v USING ".charset(Connection::get()).")").")";$W=md5($cg?(string)Connection::get()->formatValue($W,$k):$W);}$pn
.="&".($W!==null?urlencode("where[".bracket_escape($v)."]")."=".urlencode($W===false?"f":$W):"null%5B%5D=".urlencode($v));}echo"<tr>";if($Ue||!$K){echo"<td class='actions'>",checkbox("check[]",substr($pn,1),in_array(substr($pn,1),(array)$_POST["check"]));if(!$dg&&Admin::get()->isDataEditAllowed())echo" <a href='",h(ME."edit=".urlencode($a).$pn),"' class='edit' title='",lang(39),"'>",icon_solo("edit"),"</a>";}reset($K);foreach($I
as$v=>$W){if(isset($Uh[$v])){$b=current($K);$k=isset($l[$v])?$l[$v]:null;$y="";if($k&&is_blob($k)&&$W!="")$y=ME.'download='.urlencode($a).'&field='.urlencode($v).$pn;if(!$y&&$W!==null){foreach((array)$Ae[$v]as$o){if(count($Ae[$v])==1||end($o["source"])==$v){$y="";foreach($o["source"]as$q=>$El)$y
.=where_link($q,$o["target"][$q],$J[$Qh][$El]);$y=($o["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($o["db"]),ME):ME).'select='.urlencode($o["table"]).$y;if($o["ns"])$y=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($o["ns"]),$y);if(count($o["source"])==1)break;}}}if($b=="COUNT(*)"){$y=ME."select=".urlencode($a);$q=0;foreach((array)$_GET["where"]as$V){if(!array_key_exists($V["col"],$on))$y
.=where_link($q++,$V["col"],$V["val"],$V["op"]);}foreach($on
as$ng=>$V)$y
.=where_link($q++,$ng,$V);}$gi=$W===null;$uf=select_value($W,$y,$k,$Hm);$Jd=bracket_escape($v);$r=h("val[$pn][$Jd]");$Jj=isset($_POST["val"][$pn][$Jd])?$_POST["val"][$pn][$Jd]:null;$un=isset($k["privileges"]["update"])?$k["privileges"]["update"]:false;$ud=!is_array($W)&&!($k&&is_blob($k))&&is_utf8((string)$W)&&$J[$Qh][$v]==$W&&!$Me[$v]&&!(isset($k["generated"])?$k["generated"]:false);$T=($b&&preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$b,$_)?$l[idf_unescape($_[2])]["type"]:(isset($k["type"])?$k["type"]:null));$Kh=$T=="money"||($b&&preg_match('~^SUM\((.+)\)~',$b,$_)&&$l[idf_unescape($_[1])]["type"])=="money";$Em=$T&&preg_match('~text|json|lob~',$T);$ji=($T&&preg_match(number_type(),$T))||($b&&preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|UNIX_TIMESTAMP|TIME_TO_SEC|COUNT|SUM)\(~',$b));$Gb=$ji&&($gi||is_numeric(strip_tags($uf))||$Kh)?"class='number'":"";echo"<td id='$r' $Gb";if(($_GET["modify"]&&$ud&&!$gi)||$Jj!==null){$wd=true;$af=h($Jj!==null?$Jj:$W);echo" data-editing='true'>".($Em?"<textarea name='$r' cols='30' rows='".(substr_count($W,"\n")+1)."'>$af</textarea>":"<input class='input' name='$r' value='$af' size='$Mg[$v]'>");}else{$dh=strpos($uf,"<i>…</i>");if($un)echo" data-text='".($dh?2:($Em?1:0))."'".($ud?"":" data-warning='".lang(306)."'");echo">$uf";}}next($K);}if($cb){echo"<td>";Admin::get()->printBackwardKeys($cb,$J[$Qh]);echo"</td>";}echo"</tr>\n";}if(is_ajax())exit;echo"</tbody>\n",script("mixin(qs('#table tbody'), {onclick: event => tableClick(event, false, ".(Admin::get()->isDataEditAllowed()?"true":"false")."), ondblclick: event => tableClick(event, true), onkeydown: onEditingKeydown});"),"</table>\n",script("initToggles(gid('table'));"),"</div>\n";}if(!is_ajax()){if($J||$C){$Md=true;if($_GET["page"]!="last"){if(!$x||(count($J)<$x&&($J||!$C)))$Ge=($C?$C*$x:0)+count($J);elseif(DIALECT!="sql"||!$dg){$Ge=($dg?false:found_rows($Q,$Z));if($Ge<max(1e4,2*($C+1)*$x))$Ge=first(slow_query(count_rows($a,$Z,$dg,$Ue)));elseif(DIALECT=='sql'||DIALECT=='pgsql')$Md=false;}}$dj=($x!==null&&($Ge===false||$Ge>$x||$C));if($dj){if(($Ge===false?count($J)+1:$Ge-$C*$x)>$x)echo'<p class="links">','<a href="',h(remove_from_uri("page")."&page=".($C+1)),'" class="loadmore">',icon("expand"),lang(307),'</a>',script("qsl('a').onclick = partial(loadNextPage, $x, '".js_escape(lang(308))."');","");echo"\n";}echo"<div class='table-footer'><div class='field-sets'>\n";if($dj){$nh=($Ge===false?$C+(count($J)>=$x?2:1):(int)floor(($Ge-1)/$x));$ed="<li>…</li>";echo"<fieldset><legend>".lang(309)."</legend>";if(DIALECT!="simpledb"){echo"<div id='fieldset-pagination' class='fieldset-content'><ul class='pagination'>",pagination(0,$C);if($C>5)echo$ed;for($q=max(1,$C-4);$q<min($nh,$C+5);$q++)echo
pagination($q,$C);if($nh>0){if($C+5<$nh)echo$ed;echo($Md&&$Ge!==false?pagination($nh,$C):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$nh'>".lang(310)."</a>");}echo"</ul></div>";}else{echo"<div id='fieldset-pagination'><ul class='pagination'>",pagination(0,$C);if($C>1)echo$ed;if($C)echo
pagination($C,$C);if($nh>$C){echo
pagination($C+1,$C);if($nh>$C+1)echo$ed;}echo"</ul></div>";}echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(311)."</legend><div class='fieldset-content'>";$Xc=($Md?"":"~ ").$Ge;echo
checkbox("all",1,0,($Ge!==false?($Md?"":"~ ").lang(198,$Ge):""),"countRows.call(this, '$Xc');")."\n","</div></fieldset>\n";if(Admin::get()->isDataEditAllowed()){echo"<fieldset",($_GET["modify"]?'':' class="jsonly"'),">","<legend>",lang(305),"</legend>";$Mk=($_GET["modify"]?"":" data-inline-edit='1'".($wd?"":" disabled"));echo"<div class='fieldset-content'",($_GET["modify"]?"":" title='".lang(300)."'"),">","<input type='submit' class='button' id='modify-save' value='",lang(117),"'",$Mk,">","</div>","</fieldset>\n","<fieldset>","<legend>",lang(169)," <span id='selected'></span></legend>","<div class='fieldset-content'>","<input type='submit' class='button' name='edit' value='",lang(39),"'> ","<input type='submit' class='button' name='clone' value='",lang(296),"'> ","<input type='submit' class='button' name='delete' value='",lang(121),"'>",confirm(),"</div>","</fieldset>\n";}$Ce=Admin::get()->getDumpFormats();foreach((array)$_GET["columns"]as$b){if($b["fun"]){unset($Ce['sql']);break;}}if($Ce){print_fieldset_start("export",lang(75)." <span id='selected2'></span>","export");echo
html_select("format",$Ce,$M->getParameter("exportFormat"));$Zi=Admin::get()->getDumpOutputs();echo($Zi?" ".html_select("output",$Zi,$M->getParameter("exportOutput")):"")," <input type='submit' class='button' name='export' value='".lang(75)."'>\n";print_fieldset_end("export");}echo"</div></div>\n",script("initTableFooter()");}echo"</div>\n";if(Admin::get()->isDataEditAllowed()){echo"<p>","<a href='#import'>",icon("import"),lang(74),"</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"</p>","<p id='import'",($_POST["import"]?"":" class='hidden'"),">";if(ini_bool("file_uploads"))echo"<input type='file' name='csv_file'> ",html_select("import_format",["csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"],$M->getParameter("exportFormat"))," <input type='submit' class='button default' name='import' value='".lang(74)."'>",file_upload_form_script("selection_form","csv_file");else
echo
lang(205);echo"</p>";}echo
input_token(),"</form>\n",(!$Ue&&$K?"":script("tableCheck();"));}else
echo"</div>\n";}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$N=isset($_GET["status"]);$S=$N?lang(162):lang(161);page_header($S,[$S]);$In=($N?Admin::get()->getStatusVariables():Admin::get()->getServerVariables());if(!$In)echo"<p class='message'>",lang(92),"</p>\n";else{echo"<div class='scrollable'><table>\n";foreach($In
as$I){echo"<tr>";$v=array_shift($I);echo"<th><code class='jush-".DIALECT.($N?"status":"set")."'>".h($v)."</code></th>";foreach($I
as$W)echo"<td>",nl2br(h($W)),"</td>";echo"</tr>\n";}echo"</table></div>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$bm=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$f=[];$Cc=null;foreach(table_status()as$A=>$Q){$f["Comment-$A"]=h($Q["Comment"]);if(!is_view($Q)||preg_match('~materialized~i',$Q["Engine"])){$f["Engine-$A"]=h($Q["Engine"]);$Lb=isset($Q["Collation"])?$Q["Collation"]:"";if($Lb==""){if($Cc===null)$Cc=db_collation(DB,collations())??"";$Lb=$Cc;}$f["Collation-$A"]=h($Lb);foreach($bm+["Auto_increment"=>0,"Rows"=>0]as$v=>$W){if($Q[$v]!=""){$W=format_number($Q[$v]);if($W>=0)$f["$v-$A"]=($v=="Rows"?format_rows($Q):$W);if(isset($bm[$v]))$bm[$v]+=($Q["Engine"]!="InnoDB"||$v!="Data_free"?$Q[$v]:0);}elseif(array_key_exists($v,$Q))$f["$v-$A"]="?";}}}if(function_exists('AdminNeo\db_status'))$bm=db_status();foreach($bm
as$v=>$W)$f["sum-$v"]=format_number($W);echo
json_encode($f,JSON_UNESCAPED_UNICODE);}elseif($_GET["script"]=="kill")Connection::get()->query("KILL ".number($_POST["kill"]));else{$f=[];foreach(count_tables(Admin::get()->getDatabases(false))as$h=>$W){$f["tables-$h"]=$W;$f["size-$h"]=db_size($h);}echo
json_encode($f,JSON_UNESCAPED_UNICODE);}exit;}else{$ym=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($ym&&!$_POST["search"]){$G=true;$wh="";if(DIALECT=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$G=truncate_tables($_POST["tables"]);$wh=lang(312);}elseif($_POST["move"]){$G=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$wh=lang(313);}elseif($_POST["copy"]){$G=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$wh=lang(314);}elseif($_POST["drop"]){if($_POST["views"])$G=drop_views($_POST["views"]);if($G&&$_POST["tables"])$G=drop_tables($_POST["tables"]);$wh=lang(315);}elseif(DIALECT=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$P){foreach(get_rows("PRAGMA integrity_check(".q($P).")")as$I)$wh
.="<b>".h($P)."</b>: ".h($I["integrity_check"])."<br>";}}elseif(DIALECT!="sql"){$G=(DIALECT=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),(array)$_POST["tables"]));$wh=lang(316);}elseif(!$_POST["tables"])$wh=lang(79);elseif($G=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('AdminNeo\idf_escape',$_POST["tables"])))){while($I=$G->fetchAssoc())$wh
.="<b>".h($I["Table"])."</b>: ".h($I["Msg_text"])."<br>";}queries_redirect($_SERVER["REQUEST_URI"],$wh,(bool)$G);}if($_GET["ns"]=="")page_header(lang(31).": ".h(DB),true);else
page_header(lang(82).": ".h($_GET["ns"]),true);Admin::get()->printDatabaseMenu();if($_GET["ns"]===""){echo"<h2 id='schemas'>".lang(317)."</h2>\n";$Tk=Admin::get()->getSchemas();if(!$Tk)echo"<p class='message'>".lang(318)."\n";else{echo"<div class='scrollable'>\n","<table class='nowrap'>\n",'<thead><tr class="wrap"><th>',lang(82),"</th></tr></thead>";foreach($Tk
as$A)echo"<tr><th><a href='",h(ME),"ns=".urlencode($A),"' title='",lang(319),"'>".h($A)."</a></th></tr>";echo'</table></div>';}echo'<p class="links"><a href="'.h(ME).'scheme=">'.icon("database-add").lang(77)."</a>\n";}else{echo"<h2 id='tables-views'>".lang(320)."</h2>\n";$tm=['sql'=>'show-table-status.html','mariadb'=>'reference/sql-statements/administrative-sql-statements/show/show-table-status'];$Cc=db_collation(DB,collations());$c=["Engine"=>["label"=>lang(174),"doc"=>doc_link(['sql'=>'storage-engines.html','mariadb'=>'server-usage/storage-engines']),],];if($Cc!="")$c["Collation"]=["label"=>lang(46),"doc"=>doc_link(['sql'=>'charset-charsets.html','mariadb'=>'reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations']),];$c+=["Data_length"=>["label"=>lang(321),"doc"=>doc_link($tm+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286']),"link"=>"create","title"=>lang(36),],"Index_length"=>["label"=>lang(322),"doc"=>doc_link($tm+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT']),"link"=>"indexes","title"=>lang(178),],"Data_free"=>["label"=>lang(323),"doc"=>doc_link($tm),"link"=>"edit","title"=>lang(8),],"Auto_increment"=>["label"=>lang(48),"doc"=>doc_link(['sql'=>'example-auto-increment.html','mariadb'=>'reference/data-types/auto_increment']),"link"=>"auto_increment=1&create","title"=>lang(36),],"Rows"=>["label"=>lang(324),"doc"=>doc_link($tm+['pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286']),"link"=>"select","title"=>lang(34),],];if(support("comment"))$c["Comment"]=["label"=>lang(47),"doc"=>doc_link($tm+['pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE']),];$Ii=(is_string($_GET["order"])?$_GET["order"]:"");$Pc=null;if(preg_match('~^(.+)-(asc|desc)$~',$Ii,$z)){$Ii=$z[1];$Pc=($z[2]=="desc");}if($Ii!="__table"&&!isset($c[$Ii]))$Ii="";if($Pc===null)$Pc=isset($c[$Ii]["link"]);$do=($Ii!=""&&$Ii!="__table")||support("fast_status");$wm=($do?table_status():tables_list());if(!$wm)echo"<p class='message'>".lang(79)."\n";else{echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n";if(support("table")){echo"<div class='field-sets'>\n","<fieldset><legend>".lang(325)." <span id='selected2'></span></legend><div class='fieldset-content'>",html_select("op",Admin::get()->getOperators(),isset($_POST["op"])?$_POST["op"]:Driver::get()->getLikeOperator()),"<input type='search' class='input' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = event => bodyKeydown(event, 'search');","")," <input type='submit' class='button' name='search' value='".lang(60)."'>\n","</div></fieldset>\n","</div>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable'>\n",'<thead><tr class="wrap">','<th class="actions"><input id="check-all" type="checkbox" class="input jsonly" title="'.lang(193).'">'.script("gid('check-all').onclick = partial(formCheck, /^(tables|views)\[/);","");$Rh=($Ii==""||$Ii=="__table");$nm=($Rh&&!$Pc?ME."order=__table-desc":substr(ME,0,-1));$Th=($Rh&&DIALECT!="sqlite");echo'<th'.($Th?" aria-sort='".($Pc?"descending":"ascending")."'":'').'><a href="'.h($nm).'">'.lang(9).'</a>';foreach($c
as$v=>$b){$Rc=($v===$Ii?!$Pc:isset($b["link"]));echo'<th'.($v===$Ii?" aria-sort='".($Pc?"descending":"ascending")."'":'').'><a href="'.h(ME)."order=$v-".($Rc?"desc":"asc").'">'.$b["label"].'</a>'.$b["doc"];}echo"</thead>\n","<tbody>\n";if($Ii=="__table"){if($Pc)$wm=array_reverse($wm,true);}elseif($Ii){uasort($wm,function($ua,$Za)use($Ii,$Pc){$eo=isset($ua[$Ii])?$ua[$Ii]:null;$fo=isset($Za[$Ii])?$Za[$Ii]:null;$G=($eo<$fo?-1:($eo>$fo?1:0));return($Pc?-$G:$G);});}$bm=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$R=0;foreach($wm
as$A=>$N){$Nn=($do?is_view($N):$N!==null&&!preg_match('~table|sequence~i',$N));$Cd=($do?(isset($N["Engine"])?$N["Engine"]:""):$N);$r=h("Table-".$A);echo'<tr><th class="actions">'.checkbox(($Nn?"views[]":"tables[]"),$A,in_array("$A",$ym,true),"","","",$r);if(!Admin::get()->getSettings()->isSelectionPreferred()&&(support("table")||support("indexes")))$wa="table";else$wa="select";echo"<th><a href='",h(ME),"$wa=",urlencode($A),"' id='$r'>",h($A),"</a></th>";if($Nn&&!preg_match('~materialized~i',$Cd)){$S=lang(173);$Rb=count($c)-(support("comment")?2:1);echo'<td colspan="'.$Rb.'">'.(support("view")?"<a href='".h(ME)."view=".urlencode($A)."' title='".lang(37)."'>$S</a>":$S),"<td align='right'><a href='".h(ME)."select=".urlencode($A)."' title='".lang(34)."'>?</a>";}else{foreach($c
as$v=>$b){if($v=="Comment")continue;$r=" id='$v-".h($A)."'";$y=isset($b["link"])?$b["link"]:"";if(!$y){$W="";if($do){$W=isset($N[$v])?$N[$v]:"";if($v=="Collation"&&$W=="")$W=$Cc;}echo"<td$r>".h($W);continue;}$W="?";if($do){$ii=isset($N[$v])?$N[$v]:"";if(is_numeric($ii)&&$ii>=0){$W=($v=="Rows"?format_rows($N):format_number($ii));if(isset($bm[$v])&&($Cd!="InnoDB"||$v!="Data_free"))$bm[$v]+=$ii;}}echo"<td align='right'>".(support("table")||$v=="Rows"||(support("indexes")&&$v!="Data_length")?"<a href='".h(ME."$y=").urlencode($A)."'$r title='".$b["title"]."'>".h($W)."</a>":"<span$r>".h($W)."</span>");}$R++;}echo(support("comment")?"<td id='Comment-".h($A)."'>".($do?h(isset($N["Comment"])?$N["Comment"]:""):""):""),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: event => tableClick(event, true)});"),"<tfoot><tr>","<td><th>".lang(297,count($wm)),"<td>".h(DIALECT=="sql"?Connection::get()->getValue("SELECT @@default_storage_engine"):""),($Cc!=""?"<td>".h($Cc):"");if($do&&function_exists('AdminNeo\db_status'))$bm=db_status();foreach($bm
as$v=>$am)echo"<td align='right' id='sum-$v'>".($do?format_number($am):"");echo"<td></td><td></td>";if(support("comment"))echo"<td></td>";echo"</tr></tfoot>\n","</table>\n","</div>\n",($do?"":script("ajaxSetHtml('".js_escape(ME)."script=db');"));if(Admin::get()->isDataEditAllowed()){echo"<div class='table-footer'><div class='field-sets'>\n";$Fn="<input type='submit' class='button' value='".lang(326)."'> ".help_script("VACUUM");$Ei="<input type='submit' class='button' name='optimize' value='".lang(327)."'> ".help_script(DIALECT=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE");echo"<fieldset><legend>".lang(169)." <span id='selected'></span></legend><div class='fieldset-content'>".(DIALECT=="sqlite"?$Fn."<input type='submit' class='button' name='check' value='".lang(328)."'> ".help_script("PRAGMA integrity_check"):(DIALECT=="pgsql"?$Fn.$Ei:(DIALECT=="sql"?"<input type='submit' class='button' value='".lang(329)."'> ".help_script("ANALYZE TABLE").$Ei."<input type='submit' class='button' name='check' value='".lang(328)."'> ".help_script("CHECK TABLE")."<input type='submit' class='button' name='repair' value='".lang(330)."'> ".help_script("REPAIR TABLE"):"")))."<input type='submit' class='button' name='truncate' value='".lang(331)."'> ".help_script(DIALECT=="sqlite"?"DELETE":("TRUNCATE".(DIALECT=="pgsql"?"":" TABLE"))).confirm()."<input type='submit' class='button' name='drop' value='".lang(170)."'>".help_script("DROP TABLE").confirm()."\n";$g=(support("scheme")?Admin::get()->getSchemas():Admin::get()->getDatabases());echo"</div></fieldset>\n";if(count($g)!=1&&DIALECT!="sqlite"){echo"<fieldset><legend>".lang(332)." <span id='selected3'></span></legend><div>";$h=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($g?html_select("target",$g,$h,"","label-move"):'<input class="input" name="target" value="'.h($h).'" autocapitalize="off">')," <input type='submit' class='button' name='move' value='".lang(333)."'>",(support("copy")?" <input type='submit' class='button' name='copy' value='".lang(334)."'> ".checkbox("overwrite",1,$_POST["overwrite"],lang(335)):""),"</div></fieldset>\n";}echo
input_hidden("all"),script("qsl('input').onclick = partial(countTables, $R);"),input_token(),"</div></div>\n",script("initTableFooter()");}echo"</div>\n","</form>\n",script("tableCheck();");}echo'<p class="links"><a href="',h(ME),'create=">',icon("table-add"),lang(78),"</a>\n";if(support("view"))echo'<a href="',h(ME),'view=">',icon("view-add"),lang(251),"</a>\n";if(support("routine")){echo"<h2 id='routines'>".lang(189)."</h2>\n";$Gk=routines();if($Gk){$Xb=$Gk[0]["ROUTINE_COMMENT"]!==null;echo"<table>\n",'<thead><tr>','<th>',lang(228),'</th><td>',lang(45),'</td><td>',lang(268),"</td>";if($Xb)echo"<td>",lang(47),"</td>";echo"<td></td>","</tr></thead>\n";foreach($Gk
as$I){$A=($I["SPECIFIC_NAME"]==$I["ROUTINE_NAME"]?"":"&name=".urlencode($I["ROUTINE_NAME"]));echo'<tr>','<th><a href="',h(ME.($I["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($I["SPECIFIC_NAME"]).$A),'">',h($I["ROUTINE_NAME"]),'</a></th>','<td>',h($I["ROUTINE_TYPE"]),'</td>','<td>',h($I["DTD_IDENTIFIER"]),'</td>';if($Xb)echo'<td>',truncate_utf8(preg_replace('~\s{2,}~'," ",trim($I["ROUTINE_COMMENT"])),50),'</td>';echo'<td><a href="'.h(ME.($I["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($I["SPECIFIC_NAME"]).$A).'">'.lang(181)."</a></td>";}echo"</table>\n";}echo'<p class="links">';if(support("procedure"))echo'<a href="',h(ME),'procedure=">',icon("function-add"),lang(267),"</a>";echo'<a href="',h(ME),'function=">',icon("function-add"),lang(266),"</a>\n","</p>\n";}if(support("sequence")){echo"<h2 id='sequences'>".lang(336)."</h2>\n";$il=get_vals("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = current_schema() ORDER BY sequence_name");if($il){echo"<table>\n","<thead><tr><th>",lang(228),"</th><td></td></tr></thead>\n";foreach($il
as$W)echo"<tr>","<th>",h($W),"</th>","<td><a href='",h(ME),"sequence=",urlencode($W),"'>",lang(181),"</a></td>\n";echo"</table>\n";}echo"<p class='links'><a href='",h(ME),"sequence='>",icon("add"),lang(273),"</a></p>\n";}if(support("type")){echo"<h2 id='user-types'>".lang(111)."</h2>\n";$Cn=types();if($Cn){echo"<table>\n","<thead><tr><th>",lang(228),"</th><td></td></tr></thead>\n";foreach($Cn
as$W)echo"<tr>","<th>",h($W),"</th>","<td><a href='",h(ME),"type=",urlencode($W),"'>",lang(181),"</a></td>\n";echo"</table>\n";}echo"<p class='links'><a href='",h(ME),"type='>",icon("add"),lang(274),"</a></p>\n";}if(support("event")){echo"<h2 id='events'>".lang(190)."</h2>\n";$J=get_rows("SHOW EVENTS");if($J){echo"<table>\n","<thead><tr><th>".lang(228)."<td>".lang(337)."<td>".lang(257)."<td>".lang(258)."<td></thead>\n";foreach($J
as$I)echo"<tr>","<th>".h($I["Name"]),"<td>".($I["Execute at"]?lang(338)."<td>".h($I["Execute at"]):lang(259)." ".h($I["Interval value"])." ".h($I["Interval field"])."<td>".h($I["Starts"])),"<td>".h($I["Ends"]),'<td><a href="'.h(ME).'event='.urlencode($I["Name"]).'">'.lang(181).'</a>';echo"</table>\n";$Kd=Connection::get()->getValue("SELECT @@event_scheduler");if($Kd&&$Kd!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($Kd)."\n";}echo'<p class="links"><a href="',h(ME),'event=">',icon("event-add"),lang(256),"</a></p>\n";}}}page_footer();