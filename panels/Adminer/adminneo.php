<?php
/**
 * AdminNeo - Powerful database manager in a single PHP file
 * v5.9.1
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
inject($Ca,Config$kc,Settings$M,Locale$Fh){$this->admin=$Ca;$this->config=$kc;$this->settings=$M;$this->locale=$Fh;}}abstract
class
Origin
extends
Plugin{private$errors=[];private
static$instance=null;static
function
create(array$kc=[],array$sk=[]){if(self::$instance)die("Admin instance already exists.\n");$Ca=new
static();if(!$kc&&file_exists("adminneo-config.php")){$kc=include_once("adminneo-config.php");if(!is_array($kc)){$kc=[];$xh="href=https://github.com/adminneo-org/adminneo#configuration ".target_blank();$Ca->addError(lang(0,"<b>adminneo-config.php</b>")." <a $xh>".lang(1)."</a>");}}$kc=new
Config($kc);$M=new
Settings($kc);if(!$sk&&file_exists("adminneo-plugins.php")){$sk=include_once("adminneo-plugins.php");if(!is_array($sk)){$sk=[];$xh="href=https://github.com/adminneo-org/adminneo#plugins ".target_blank();$Ca->addError(lang(0,"<b>adminneo-plugins.php</b>")." <a $xh>".lang(1)."</a>");}}self::$instance=$sk?new
Pluginer($Ca,$sk):$Ca;$Ca->inject(self::$instance,$kc,$M,Locale::get());foreach($sk
as$rk)$rk->inject(self::$instance,$kc,$M,Locale::get());return
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
addError($k){$this->errors[]=$k;}function
getErrors(){return$this->errors;}abstract
function
getServiceTitle();function
getCredentials(){$L=$this->config->getServer(SERVER);return[$L?$L->getServer():SERVER,$_GET["username"],get_password()];}function
verifyDefaultPassword($E){$Bf=$this->config->getDefaultPasswordHash();if($Bf===null||$Bf==="")return
lang(2);elseif(!password_verify($E,$Bf))return
lang(3);return
true;}function
authenticate($U,$E){if($E==""){$Bf=$this->config->getDefaultPasswordHash();if($Bf===null)return
lang(4,target_blank());else
return$Bf==="";}return
true;}function
getPrivateKey($f=false){return
get_private_key($f);}function
getBruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
getServerName($L,$pl=true,$ue=null){if($L==""){if(!$pl)return"";$L=Connection::exists()?Connection::get()->getDefaultServerName():"";if($L=="")return$ue!==null?$ue:lang(5);$gm=null;}else$gm=$this->config->getServer($L);return$gm?$gm->getName():preg_replace('~^https?://~',"",$L);}abstract
function
getDatabase();function
getDatabases($Qe=true){$h=$this->filterListWithWildcards(get_databases($Qe),$this->config->getHiddenDatabases(),false,Driver::get()->getSystemDatabases());if(DB!=""&&!in_array(DB,$h))array_unshift($h,DB);return$h;}function
getSchemas($Oi=false){$Hf=$this->config->getHiddenSchemas();if($Oi&&!in_array("__system",$Hf))$Hf[]="__system";$Ml=$this->filterListWithWildcards(schemas(),$Hf,false,Driver::get()->getSystemSchemas());if(isset($_GET["ns"])&&$_GET["ns"]!=""&&!in_array($_GET["ns"],$Ml))array_unshift($Ml,$_GET["ns"]);return$Ml;}function
getCollations(array$Rg=[]){$To=$this->config->getVisibleCollations();$He=$To?array_merge($To,$Rg):[];return$this->filterListWithWildcards(collations(),$He,true);}private
function
filterListWithWildcards(array$Y,array$He,$Tg,array$fn=[]){if(!$Y||!$He)return$Y;$u=array_search("__system",$He);if($u!==false){unset($He[$u]);$He=array_merge($He,$fn);}array_walk($He,function(&$X){$X=str_replace('\\*',".*",preg_quote($X,"~"));});$kk='~^('.implode("|",$He).')$~';return$this->filterListWithPattern($Y,$kk,$Tg);}private
function
filterListWithPattern(array$Y,$kk,$Tg){$G=[];foreach($Y
as$w=>$X){if(is_array($X)){if($Vm=$this->filterListWithPattern($X,$kk,$Tg))$G[$w]=$Vm;}elseif(($Tg&&preg_match($kk,$X))||(!$Tg&&!preg_match($kk,$X)))$G[$w]=$X;}return$G;}abstract
function
getQueryTimeout();function
sendHeaders(){}function
updateCspHeader(array&$Dc){}function
printFavicons(){$Ub=validate_color_variant($this->config->getColorVariant());echo"<link rel='icon' type='image/x-icon' href='",h(link_files("favicon-$Ub.ico",[])),"' sizes='32x32'>\n","<link rel='icon' type='image/svg+xml' href='",h(link_files("favicon-$Ub.svg",[])),"'>\n","<link rel='apple-touch-icon' href='",h(link_files("apple-touch-icon-$Ub.png",[])),"'>\n";}abstract
function
printToHead();function
getCssUrls(){$yo=$this->config->getCssUrls();foreach(["adminneo.css","adminneo-light.css","adminneo-dark.css"]as$o){if(file_exists($o))$yo[]="$o?v=".filemtime($o);}return$yo;}function
isLightModeForced(){return$this->isColorSchemeForced(false);}function
isDarkModeForced(){return$this->isColorSchemeForced(true);}private
function
isColorSchemeForced($Ic){$pi=$Ic?Settings::$ColorSchemeDark:Settings::$ColorSchemeLight;$qi=$Ic?Settings::$ColorSchemeLight:Settings::$ColorSchemeDark;$De=file_exists("adminneo-$pi.css");$Ee=file_exists("adminneo-$qi.css");if($De&&!$Ee)return
true;return$this->settings->getColorScheme()==$pi&&!($De
xor$Ee);}function
getJsUrls(){$yo=$this->config->getJsUrls();$o="adminneo.js";if(file_exists($o))$yo[]="$o?v=".filemtime($o);return$yo;}abstract
function
printLoginForm();function
getLoginFormRow($ye,$dh,$l){if($dh)return"<tr><th>$dh</th><td>$l</td></tr>\n";else
return"$l\n";}function
printLogout(){echo"<div class='logout'>","<form action='' method='post'>\n","<span title='",lang(6),"'>",h($_GET["username"]),"</span>","<input type='submit' class='button' name='logout' value='",lang(7),"' id='logout'>",input_token(),"</form>","</div>\n";}function
getTableName(array$jn){return
h($jn["Name"]);}abstract
function
getFieldName(array$l,$xj=0);function
formatComment($cc){return
h($cc);}abstract
function
printTableMenu(array$jn,$og);function
getForeignKeys($P){return
foreign_keys($P);}function
getBackwardKeys($P,$hn){if(!$this->settings->isRelationLinks())return[];$J=backward_keys($P);$Wg=[];foreach($J
as$I){$s=$I["table_schema"].".".$I["table_name"];$Wg[$s]["schema"]=$I["table_schema"];$Wg[$s]["table"]=$I["table_name"];$Wg[$s]["constraints"][$I["constraint_name"]][$I["column_name"]]=$I["referenced_column_name"];}foreach($Wg
as$s=>$w){$B=$this->admin->getTableName(table_status1($w["table"],true));if($B!=""){$Ol=preg_quote($hn);$Zl="(:|\\s*-)?\\s+";$Wg[$s]["name"]=(preg_match("(^$Ol$Zl(.+)|^(.+?)$Zl$Ol\$)iu",$B,$_)?$_[2].$_[3]:$B);}else
unset($Wg[$s]);}return$Wg;}function
printBackwardKeys(array$gb,array$I){foreach($gb
as$w){foreach($w["constraints"]as$qc){$Zh=preg_replace('~&ns=[^&]+&~',"&ns=".urldecode($w["schema"])."&",ME);$z=$Zh.'select='.urlencode($w["table"]);$r=0;foreach($qc
as$c=>$W){if(!isset($I[$W]))continue
2;$z
.=where_link($r++,$c,$I[$W]);}$B=preg_replace('(^'.preg_quote($_GET["select"]).(substr($_GET["select"],-1)=="s"?"?":"").'_)',"_",$w["name"]);$S=implode(", ",array_keys($qc));echo"<a href='".h($z)."' title='".h($S)."'>".h($B)."</a>";$z=$Zh.'edit='.urlencode($w["table"]);foreach($qc
as$c=>$W)$z
.="&preset".urlencode("[".bracket_escape($c)."]")."=".urlencode($I[$W]);echo"<a href='".h($z)."' title='".lang(8)."'>",icon_solo("add"),"</a> ";}}}abstract
function
formatSelectQuery($F,$Mm,$te=false);abstract
function
formatMessageQuery($F,$Jn,$te=false);abstract
function
formatSqlCommandQuery($F);function
printAfterSqlCommand(){}abstract
function
getTableDescriptionFieldName($P);abstract
function
fillForeignDescriptions(array$J,array$Te);function
getFieldValueLink($W,$l){if(is_mail($W))return"mailto:$W";if(is_web_url($W))return$W;return
null;}abstract
function
formatSelectionValue($W,$z,$l,$Jj);abstract
function
formatFieldValue($X,array$l);abstract
function
printTableStructure(array$m);abstract
function
printTablePartitions(array$ak);abstract
function
printRelatedTables(array$R);abstract
function
printTableIndexes(array$v,array$jn);abstract
function
printSelectionColumns(array$K,array$d);abstract
function
printSelectionSearch(array$Z,array$d,array$v);abstract
function
printSelectionOrder(array$xj,array$d,array$v);abstract
function
printSelectionLimit($y);abstract
function
printSelectionLength($Dn);abstract
function
printSelectionAction(array$v);function
isDataEditAllowed(){return!information_schema(DB);}abstract
function
processSelectionColumns(array$d,array$v);abstract
function
processSelectionSearch(array$m,array$v);abstract
function
processSelectionOrder(array$m,array$v);function
processSelectionLimit(){if(!isset($_GET["limit"]))return$this->settings->getRecordsPerPage();return$_GET["limit"]!=""?(int)$_GET["limit"]:0;}abstract
function
processSelectionLength();abstract
function
getFieldFunctions(array$l);abstract
function
getFieldInput($P,array$l,$Wa,$X,$q);function
getFieldInputHint($P,array$l,$X){return
support("comment")?$this->admin->formatComment($l["comment"]):"";}abstract
function
processFieldInput(array$l,$X,$q="");function
detectJson($ze,&$X,$Dk=null){if(is_array($X)){$Oe=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($this->config->isJsonValuesAutoFormat()?JSON_PRETTY_PRINT:0);$X=json_encode($X,$Oe);return
true;}$Oe=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($Dk?JSON_PRETTY_PRINT:0);if(preg_match('~^jsonb?$~',$ze)){if($X!=null&&$Dk!==null&&$this->config->isJsonValuesAutoFormat()&&($Pg=json_decode($X))!==null)$X=json_encode($Pg,$Oe);return
true;}if(!$this->config->isJsonValuesDetection())return
false;if(is_string($X)&&$X!=""&&preg_match('~varchar|text|character varying|String|keyword~',$ze)&&($X[0]=="{"||$X[0]=="[")&&($Pg=json_decode($X))){if($Dk!==null&&$this->config->isJsonValuesAutoFormat())$X=json_encode($Pg,$Oe);return
true;}return
false;}function
getServerVariables(){return
show_variables();}function
getStatusVariables(){return
show_status();}function
getDumpFormats(){return['csv'=>['name'=>'CSV,','data'=>true],'csv;'=>['name'=>'CSV;','data'=>true],'tsv'=>['name'=>'TSV','data'=>true],];}function
getStructureDumpFormats(){return$this->filterDumpFormats("structure");}function
getDataDumpFormats(){return$this->filterDumpFormats("data");}private
function
filterDumpFormats($T){$Xe=[];foreach(self::get()->getDumpFormats()as$We=>$Wc){if(is_string($Wc))$Xe[$We]=$Wc;elseif(isset($Wc[$T])?$Wc[$T]:false)$Xe[$We]=isset($Wc["name"])?$Wc["name"]:$We;}return$Xe;}abstract
function
sendDumpFormatHeaders($Sf,$ui=false);abstract
function
getDumpOutputs();abstract
function
sendDumpOutputHeaders($Sf,$pe);function
sendDumpHeaders($Sf,$ui=false){return
null;}function
dumpDatabase($Mc){}abstract
function
dumpTable($P,$Um,$Qo=0);abstract
function
dumpData($P,$Um,$F);abstract
function
getImportFilePath();abstract
function
printDatabaseMenu();abstract
function
printNavigation($ni);abstract
function
printDatabaseSwitcher($ni);function
printTablesFilter(){echo"<div class='tables-filter jsonly'>"."<input id='tables-filter' type='search' class='input' autocomplete='off' placeholder='".lang(9)."'>".script("initTablesFilter(".json_encode($this->admin->getDatabase(),JSON_HEX_TAG).");")."</div>\n";}abstract
function
printTableList(array$R);function
getSettingsRows($sf){$M=[];if($sf==1){$C=get_language_options();if($C)$M["lang"]="<tr><th id='label-language'>".lang(10)."</th>"."<td>".html_select("lang",get_language_options(),Locale::get()->getLanguage(),"","label-language")."</td></tr>\n";$On=get_theme_titles($this->config->getColorVariant());if(count($On)>1){list($Gn)=validate_theme($this->config->getTheme(),$this->config->getColorVariant());$C=[""=>lang(11)." ($On[$Gn])"]+$On;$M["theme"]="<tr><th id='label-theme'>".lang(12)."</th>"."<td>".html_select("theme",$C,($ta=$this->settings->getParameter("theme"))!==null?$ta:"","","label-theme")."</td></tr>\n";}$C=[""=>lang(13),Settings::$ColorSchemeLight=>lang(14),Settings::$ColorSchemeDark=>lang(15)];$M["colorScheme"]="<tr><th>".lang(16)."</th>"."<td>".html_radios("colorScheme",$C,($ta=$this->settings->getParameter("colorScheme"))!==null?$ta:"")."</td></tr>\n";}elseif($sf==2){$C=[""=>lang(11),true=>lang(17),false=>lang(18),];$j=$C[$this->config->isRelationLinks()];$C[""].=" ($j)";$M["relationLinks"]="<tr><th>".lang(19)."</th>"."<td>".html_radios("relationLinks",$C,($ta=$this->settings->getParameter("relationLinks"))!==null?$ta:"")."<span class='input-hint'>".lang(20)."</span>"."</td></tr>\n";$j=$this->config->getRecordsPerPage();$C=[""=>lang(11)." ($j)","20","30","50","70","100",];$M["recordsPerPage"]="<tr><th id='label-records'>".lang(21)."</th>"."<td>".html_select("recordsPerPage",$C,($ta=$this->settings->getParameter("recordsPerPage"))!==null?$ta:"","","label-records")."<span class='input-hint'>".lang(22)."</span>"."</td></tr>\n";$j=($ta=$this->config->getEnumAsSelectThreshold())!==null?$ta:lang(23);$C=[""=>lang(11)." ($j)",-1=>lang(23),0=>lang(24),3=>lang(25,3),5=>lang(25,5),10=>lang(25,10),20=>lang(25,20),];$M["enumAsSelectThreshold"]="<tr><th id='label-enum'>".lang(26)."</th>"."<td>".html_select("enumAsSelectThreshold",$C,($ta=$this->settings->getParameter("enumAsSelectThreshold"))!==null?$ta:"","","label-enum",true)."<span class='input-hint'>".lang(27)."</span>"."</td></tr>\n";}return$M;}abstract
function
getForeignColumnInfo(array$Te,$c);}class
Pluginer{private
static$InternalMethods=["inject"=>true,"getConfig"=>true,];private
static$AppendMethods=["getErrors"=>true,"getFieldFunctions"=>true,"getDumpOutputs"=>true,"getDumpFormats"=>true,"getSettingsRows"=>true,];private$plugins;private$hooks=[];function
__construct(Origin$Ca,array$sk){$this->plugins=$sk;foreach(get_class_methods('\AdminNeo\Origin')as$ki){$this->hooks[$ki]=[];if(!(isset(self::$InternalMethods[$ki])?self::$InternalMethods[$ki]:false)){foreach($sk
as$rk){if(method_exists($rk,$ki))$this->hooks[$ki][]=$rk;}}if(isset(self::$AppendMethods[$ki])?self::$AppendMethods[$ki]:false)array_unshift($this->hooks[$ki],$Ca);else$this->hooks[$ki][]=$Ca;}}function
getPlugins(){return$this->plugins;}function
__call($B,array$Tj){$Oa=isset(self::$AppendMethods[$B])?self::$AppendMethods[$B]:false;$G=$Oa?[]:null;assert(isset($this->hooks[$B]),"Calling unknown plugin method: $B");foreach($this->hooks[$B]as$rk){$X=call_user_func_array([$rk,$B],$Tj);if($X!==null){if($Oa)$G+=$X;else
return$X;}}return$G;}function
updateCspHeader(array&$Dc){$this->__call(__FUNCTION__,[&$Dc]);}function
detectJson($ze,&$X,$Dk=null){return$this->__call(__FUNCTION__,[$ze,&$X,$Dk]);}}class
Admin
extends
Origin{function
getOperators(){return
Driver::get()->getOperators();}function
getServiceTitle(){return"<a href='".h(HOME_URL)."'><svg role='img' class='logo' width='133' height='28'><desc>AdminNeo</desc>"."<use href='".h(link_files("logo.svg",[]))."#logo'/></svg></a>";}function
getDatabase(){return
DB;}function
getQueryTimeout(){return
2;}function
printToHead(){echo"<link rel='stylesheet' href='",h(link_files("jush.css",[])),"'>";if(!$this->admin->isLightModeForced())echo"<link rel='stylesheet' ".(!$this->admin->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",h(link_files("jush-dark.css",[])),"'>\n";echo
script_src(link_files("jush.js",[]),true);}function
printLoginForm(){$wd=Drivers::getList();$hm=$this->config->getServerPairs($wd);$L=SERVER?:$this->config->getDefaultServer();echo"<table class='box box-light'>\n";if($hm)echo$this->admin->getLoginFormRow('server',lang(5),"<select name='auth[server]'>".optionlist($hm,$L,true)."</select>");else{$ud=DRIVER?:$this->config->getDefaultDriver($wd);if(count($wd)>1)echo$this->admin->getLoginFormRow('driver',lang(28),html_select("auth[driver]",$wd,$ud).script("initLoginDriver(qsl('select'));",""));else
echo$this->admin->getLoginFormRow('driver','',input_hidden("auth[driver]",$ud));echo$this->admin->getLoginFormRow('server',lang(5),"<input class='input' name='auth[server]' value='".h($L)."' title='".lang(29)."' placeholder='localhost' autocapitalize='off'>");}echo$this->admin->getLoginFormRow('username',lang(6),'<input class="input" name="auth[username]" id="username" value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'),$this->admin->getLoginFormRow('password',lang(30),'<input type="password" class="input" name="auth[password]" autocomplete="current-password">');if(!$hm){$Mc=isset($_GET["db"])?$_GET["db"]:$this->config->getDefaultDatabase();echo$this->admin->getLoginFormRow('db',lang(31),'<input class="input" name="auth[db]" value="'.h($Mc).'" autocapitalize="off">');}echo"</table>\n","<p>","<input type='submit' class='button default' value='".lang(32)."'>",checkbox("auth[permanent]",1,$_COOKIE["neo_permanent"],lang(33)),"</p>\n";}function
getFieldName(array$l,$xj=0){$T=$l["full_type"].($l["null"]?" NULL":"");$cc=$l["comment"];$Zl=$T&&$cc!=""?": ":"";return'<span title="'.h($T.$Zl.$cc).'">'.h($l["field"]).'</span>';}function
printTableMenu(array$jn,$og){echo'<p class="links top-tabs">';$yh=[];$Vl=($this->settings->isSelectionPreferred()&&!$this->settings->isNavigationReversed())||(!$this->settings->isSelectionPreferred()&&$this->settings->isNavigationReversed());if($Vl)$yh["select"]=[lang(34),"data"];if(support("table")||support("indexes"))$yh["table"]=[lang(35),"structure"];if(!$Vl)$yh["select"]=[lang(34),"data"];$P=$jn["Name"];$Kg=false;if(support("table")){$Kg=is_view($jn);if(!$Kg){if($P!="")$yh["create"]=[lang(36),"edit"];}elseif(support("view"))$yh["view"]=[lang(37),"edit"];}if($og!==null)$yh["edit"]=[lang(8),"item-add"];$Tj=$og?"&".http_build_query($og):"";foreach($yh
as$w=>$W)echo" <a href='",h(ME),"$w=",urlencode($P),($w=="edit"?$Tj:""),"'",bold(isset($_GET[$w])),">",icon($W[1]),"$W[0]</a>";echo
doc_link([DIALECT=>Driver::get()->tableHelp($P,$Kg)],icon("help").lang(38)),"\n";}function
formatSelectQuery($F,$Mm,$te=false){$an=support("sql");$Xo=!$te?Driver::get()->warnings():null;if($an)$F
.=";";$dn=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$H="<pre><code class='jush-$dn'>".h(str_replace("\n"," ",$F))."</code></pre>\n";$H
.="<p class='links'>";if($an)$H
.="<a href='".h(ME)."sql=".urlencode($F)."'>".icon("edit").lang(39)."</a>";if($Xo)$H
.="<a href='#warnings' class='toggle'>".lang(40).icon_chevron_down()."</a>";$H
.=" <span class='time'>(".format_time($Mm).")</span>";$H
.="</p>\n";if($Xo){$H
.=script("initToggles(qsl('p'));");$H
.="<div id='warnings' class='warnings hidden'>\n$Xo\n</div>\n";}return$H;}function
formatMessageQuery($F,$Jn,$te=false){restart_session();$Jf=&get_session("queries");if(!isset($Jf[$_GET["db"]]))$Jf[$_GET["db"]]=[];if(strlen($F)>1e6)$F=preg_replace('~[\x80-\xFF]+$~','',substr($F,0,1e6))."\n…";$Jf[$_GET["db"]][]=[$F,time(),$Jn];$an=support("sql");$Xo=!$te?Driver::get()->warnings():null;$Em="sql-".count($Jf[$_GET["db"]]);$Yo="warnings-".count($Jf[$_GET["db"]]);$H=" ";if($Xo)$H
.="<a href='#$Yo' class='toggle'>".lang(40).icon_chevron_down()."</a>, ";$Rk=support("sql")?lang(41):lang(42);$H
.="<a href='#$Em' class='toggle'>$Rk".icon_chevron_down()."</a>";$H
.=" <span class='time'>".@date("H:i:s")."</span>\n";if($Xo)$H
.="<div id='$Yo' class='warnings hidden'>\n$Xo</div>\n";$H
.="<div id='$Em' class='hidden'>\n";$dn=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$H
.="<pre><code class='jush-$dn'>".truncate_utf8($F,1000)."</code></pre>\n";$H
.="<p class='links'>";if($an)$H
.="<a href='".h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($Jf[$_GET["db"]])-1))."'>".icon("edit").lang(39)."</a>";if($Jn)$H
.=" <span class='time'>($Jn)</span>";$H
.="</p>\n";$H
.="</div>\n";return$H;}function
formatSqlCommandQuery($F){if(preg_match('~^DELIMITER\s~i',$F))return"";return
truncate_utf8($F,1000);}function
getTableDescriptionFieldName($P){return"";}function
fillForeignDescriptions(array$J,array$Te){return$J;}function
formatSelectionValue($W,$z,$l,$Jj){if($W===null)$Cn="<i>NULL</i>";elseif(!$l)$Cn=$W;elseif(preg_match("~char|binary|boolean~",$l["type"])&&!preg_match("~var~",$l["type"]))$Cn="<code>$W</code>";elseif(is_blob($l)&&!is_utf8($W))$Cn="<i>".lang(43,strlen($Jj))."</i>";elseif($this->admin->detectJson($l["full_type"],$Jj))$Cn="<code class='jush-json'>$W</code>";else$Cn=$W;if($z)$Cn="<a href='".h($z)."'".(is_web_url($z)?target_blank():"").">$Cn</a>";return$Cn;}function
formatFieldValue($X,array$l){return$X;}function
printTableStructure(array$m){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th>",lang(44),"</th>","<td>",lang(45)," <span class='note'>[".lang(46)."]</span></td>","<td>",lang(47),"</td>";if(support("comment"))echo"<td>",lang(48),"</td>";echo"</tr></thead>\n";$Eo=Driver::get()->getUserTypes();foreach($m
as$l){echo"<tr>","<th>",h($l["field"]),"</th>","<td>";$T=h($l["full_type"]);if(in_array($T,$Eo))echo"<a href='".h(ME.'type='.urlencode($T))."'>$T</a>";else
echo$T;if($l["null"])echo" <i>NULL</i>";if($l["auto_increment"])echo" <i>".lang(49)."</i>";if(isset($l["default"])){if($l["generated"])echo" <i>AS</i> <code class='jush-".DIALECT."'>".h(parenthesize_expression($l["default"]))."</code>"," <i>".h($l["generated"])."</i>";else
echo" [<strong>".h($l["default"])."</strong>]";}echo"</td>","<td>",h($l["collation"]),"</td>";if(support("comment"))echo"<td>",$this->admin->formatComment($l["comment"]),"</td>";echo"\n";}echo"</table>\n","</div>\n";}function
printTablePartitions(array$ak){$rm=isset($ak["partition_names"]);echo"<p>","<code class='jush-".DIALECT."'>BY ".h($ak["partition_by"])." (".h($ak["partition"]).")</code>";if(!$rm&&isset($ak["partitions"]))echo" ".lang(50).": ".h($ak["partitions"]);echo"</p>";if($rm){echo"<table>\n","<thead><tr><th>".lang(51)."</th><td>".lang(52)."</td></tr></thead>\n";foreach($ak["partition_names"]as$w=>$B){echo"<tr><th>";if(DIALECT=="pgsql")echo"<a href='",h(ME."table=".urlencode($B)),"'>";echo
h($B);if(DIALECT=="pgsql")echo"</a>";echo"</th><td>".h($ak["partition_values"][$w])."\n";}echo"</table>\n";}}function
printRelatedTables(array$R){echo"<ul class='links'>\n";foreach($R
as$I){$z=preg_replace('~ns=[^&]*~',"ns=".urlencode($I["ns"]),ME);echo"<li><a href='",h($z."table=".urlencode($I["table"])),"'>",icon("structure");if($I["ns"]!=$_GET["ns"])echo"<b>".h($I["ns"])."</b>.";echo
h($I["table"]),"</a>";}echo"</ul>\n";}function
printTableIndexes(array$v,array$jn){$Sc=first(Driver::get()->getIndexAlgorithms($jn));$Xj=false;foreach($v
as$u){if(isset($u["partial"])?$u["partial"]:false){$Xj=true;break;}}echo"<table>\n","<thead><tr>","<th>",lang(45),"</th>","<td>",lang(53)," <span class='note'>(",lang(54),")</span></td>";if($Xj)echo"<td>",lang(55),"</td>";echo"</tr></thead>\n";foreach($v
as$B=>$u){ksort($u["columns"]);$Hk=[];foreach($u["columns"]as$w=>$W)$Hk[]="<i>".h($W)."</i>".($u["lengths"][$w]?"(".h($u["lengths"][$w]).")":"").($u["descs"][$w]?" DESC":"");echo"<tr title='",h($B),"'>","<th>",h($u["type"]);if(isset($u['algorithm'])&&$u['algorithm']!=$Sc)echo" (",h($u['algorithm']),")";echo"</th>","<td>",implode(", ",$Hk),"</td>";if($Xj){echo"<td>";if($u['partial'])echo"<code class='jush-",DIALECT,"'>WHERE ",h($u['partial']),"</code>";echo"</td>";}echo"</tr>\n";}echo"</table>\n";}function
printSelectionColumns(array$K,array$d){print_fieldset_start("select",lang(56),"columns",(bool)$K,true);$K[""]=[];$r=0;foreach($K
as$w=>$W){$W=isset($_GET["columns"][$w])?$_GET["columns"][$w]:[];$c=select_input("name='columns[$r][col]'",$d,isset($W["col"])?$W["col"]:null,$w!==""?"selectFieldChange":"selectAddRow");echo"<div ",($w!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly");if(Driver::get()->getFunctions()||Driver::get()->getGrouping())echo
html_select("columns[$r][fun]",[-1=>""]+array_filter([lang(57)=>Driver::get()->getFunctions(),lang(58)=>Driver::get()->getGrouping()]),isset($W["fun"])?$W["fun"]:null),help_script_command("value && value.replace(/ |\$/, '(') + ')'",true),script("qsl('select').onchange = (event) => { ".($w!==""?"":" qsl('select, input:not(.remove)', event.target.parentNode).onchange();")." };",""),"($c)";else
echo$c;echo" <button class='button light remove jsonly' title='",lang(59),"'>",icon_solo("remove"),"</button>",script("qsl('#fieldset-select .remove').onclick = selectRemoveRow;",""),"</div>\n";$r++;}print_fieldset_end("select",true);}function
printSelectionSearch(array$Z,array$d,array$v){print_fieldset_start("search",lang(60),"search",(bool)$Z);foreach($v
as$r=>$u){if($u["type"]=="FULLTEXT"){echo"<div>(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$u["columns"]))."</i>) AGAINST","<input type='text' class='input' name='fulltext[".h($r)."]' value='".h(isset($_GET["fulltext"][$r])?$_GET["fulltext"][$r]:null)."'>",script("qsl('input').oninput = selectFieldChange;","");if(DIALECT=='sql')echo
checkbox("boolean[$r]",1,isset($_GET["boolean"][$r]),"BOOL");echo"</div>\n";}}$_b="this.parentNode.firstChild.onchange();";foreach(array_merge(array_values((array)$_GET["where"]),[[]])as$r=>$W){if(!$W||("$W[col]$W[val]"!=""&&in_array($W["op"],$this->getOperators())))echo"<div>",select_input(" name='where[$r][col]'",$d,$W["col"],($W?"selectFieldChange":"selectAddRow"),"(".lang(61).")"),html_select("where[$r][op]",$this->getOperators(),$W["op"],$_b),"<input type='text' class='input' name='where[$r][val]' value='".h($W["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $_b }, onkeydown: selectSearchKeydown});","")," <button class='button light remove jsonly' title='".lang(59)."'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-search .remove").onclick = selectRemoveRow;',""),"</div>\n";}print_fieldset_end("search");}function
printSelectionOrder(array$xj,array$d,array$v){print_fieldset_start("sort",lang(62),"sort",(bool)$xj,true);$_GET["order"][""]="";$r=0;foreach((array)$_GET["order"]as$w=>$W){if($w!=""&&$W=="")continue;echo"<div ",($w!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly"),select_input("name='order[$r]'",$d,$W,$w!==""?"selectFieldChange":"selectAddRow")," ",checkbox("desc[$r]",1,isset($_GET["desc"][$w]),lang(63))," <button class='button light remove jsonly' title='",lang(59),"'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-sort .remove").onclick = selectRemoveRow;',""),"</div>\n";$r++;}print_fieldset_end("sort",true);}function
printSelectionLimit($y){echo"<fieldset><legend>".lang(64)."</legend><div class='fieldset-content'>","<input type='number' name='limit' class='input size' value='".h($y?:"")."'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
printSelectionLength($Dn){if($Dn!==null)echo"<fieldset><legend>".lang(65)."</legend><div class='fieldset-content'>","<input type='number' name='text_length' class='input size' value='".h($Dn)."'>","</div></fieldset>\n";}function
printSelectionAction(array$v){echo"<fieldset><legend>".lang(66)."</legend><div class='fieldset-content'>","<input type='submit' class='button' value='".lang(56)."'>"," <span id='noindex' title='".lang(67)."'></span>","<script".nonce().">\n";$d=new
stdClass();foreach($v
as$u){$Fc=reset($u["columns"]);if($u["type"]!="FULLTEXT"&&$Fc)$d->$Fc=null;}echo"const indexColumns = ".json_encode($d,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG).";\n","selectFieldChange.call(gid('form')['select']);\n","</script>\n","</div></fieldset>\n";}function
processSelectionColumns(array$d,array$v){$K=[];$qf=[];foreach((array)$_GET["columns"]as$w=>$W){if($W["fun"]=="count"||($W["col"]!=""&&(!$W["fun"]||in_array($W["fun"],Driver::get()->getFunctions())||in_array($W["fun"],Driver::get()->getGrouping())))){$K[$w]=apply_sql_function($W["fun"],($W["col"]!=""?idf_escape($W["col"]):"*"));if(!in_array($W["fun"],Driver::get()->getGrouping()))$qf[]=$K[$w];}}return[$K,$qf];}function
processSelectionSearch(array$m,array$v){if(isset($_GET["where"]))$_GET["where"]=validate_search_conditions($_GET["where"]);$H=[];foreach($v
as$r=>$u){if($u["type"]=="FULLTEXT"&&isset($_GET["fulltext"])&&$_GET["fulltext"][$r]!="")$H[]="MATCH (".implode(", ",array_map('AdminNeo\idf_escape',$u["columns"])).") AGAINST (".q($_GET["fulltext"][$r]).(isset($_GET["boolean"][$r])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$Z){$Qb=$Z["col"];$nj=$Z["op"];$W=$Z["val"];if("$Qb$W"!=""&&in_array($nj,$this->getOperators())){if($nj=="SQL"&&(!$_POST||!verify_token()))Connection::get()->setUntrusted(true);$jc=[];$sg='-?\d+';$Ti='-?(\d+(\.\d*)?|\.\d+)';$_g=preg_match('~IN$~',$nj);$tg="~^$sg".($_g?"(,$sg)*":"")."$~";$Ui="~^$Ti".($_g?"(,$Ti)*":"")."$~";$Nc='~^(\d+)-(\d+)-(\d+)([ T]([01]?\d|2[0-3]):[0-5]\d(:[0-5]\d(\.\d+)?)?(\s*(z|utc|[+-](0?\d|1[0-5])(:?[0-5]\d)?))?)?$~i';foreach(($Qb!=""?[$Qb=>isset($m[$Qb])?$m[$Qb]:[]]:$m)as$B=>$l){$Bk="";$hc=" $nj";$Ag=preg_match('~'.number_type().(DIALECT=="pgsql"?"":'|bit').'~',$l["type"])&&!preg_match('~\[~',$l["full_type"]);$aj=DIALECT=="pgsql"&&$nj=="="&&$l["type"]=="oid";if($aj)$hc
.=" ".$this->admin->processFieldInput($l,$W)."::regproc";elseif($_g){$Bg=$Ag&&!(DIALECT=="pgsql"&&$l["type"]=="money");$Wf=[];foreach(parse_search_list($W)as$X)$Wf[]=$Bg&&preg_match("~^$Ti$~",$X)?$X:q($X);$hc
.=" (".($Wf?implode(", ",$Wf):"NULL").")";}elseif($nj=="SQL")$hc=" $W";elseif(preg_match('~^(I?LIKE) %%$~',$nj,$_))$hc=" $_[1] ".$this->admin->processFieldInput($l,"%$W%");elseif($nj=="FIND_IN_SET"){$Bk="$nj(".q($W).", ";$hc=")";}elseif(!preg_match('~NULL$~',$nj))$hc
.=" ".$this->admin->processFieldInput($l,$W);if($Qb!=""||(isset($l["privileges"]["where"])&&(preg_match(preg_match('~^'.int_type().'$~',$l["type"])?$tg:$Ui,$W)||!$Ag)&&(!preg_match("~[\x80-\xFF]~",$W)||preg_match('~char|text|enum|set|keyword~',$l["type"]))&&(!preg_match('~date|timestamp~',$l["type"])||(preg_match($Nc,$W,$_)&&checkdate((int)$_[2],(int)$_[3],(int)$_[1])))&&Driver::get()->isSearchable($Z,$l)&&(!preg_match('~^elastic~',DRIVER)||$l["type"]!="boolean"||preg_match('~^(true|false)$~i',$W))&&(!preg_match('~^elastic~',DRIVER)||strpos($nj,"regexp")===false||preg_match('~text|keyword~',$l["type"]))&&(!preg_match('~^elastic~',DRIVER)||!preg_match('~^((unsigned_)?long|short|byte)$~',$l["type"])||preg_match('~^-?\d+$~',$W)))){if($aj)$jc[]=$Bk.idf_escape($B).$hc;else$jc[]=$Bk.Driver::get()->convertSearch(idf_escape($B),$Z,$l).$hc;}}if(count($jc)==1)$H[]=$jc[0];elseif($jc)$H[]="(".implode(" OR ",$jc).")";else$H[]="1 = 0";}}return$H;}function
processSelectionOrder(array$m,array$v){$H=[];$be=(DIALECT=="clickhouse"?'\\\\':'');$q='~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`'.$be.']|``)+`|"(?:[^"'.$be.']|"")+")\)|COUNT\(\*\))$~';foreach((array)$_GET["order"]as$w=>$W){if($W!="")$H[]=(preg_match($q,$W)?$W:idf_escape($W)).(isset($_GET["desc"][$w])?" DESC".(DIALECT=="pgsql"&&(isset($m[$W]["null"])?$m[$W]["null"]:null)?" NULLS LAST":""):"");}return$H;}function
processSelectionLength(){return
isset($_GET["text_length"])?$_GET["text_length"]:"100";}function
getFieldFunctions(array$l){$H=($l["null"]?"NULL/":"");$vo=isset($_GET["select"])||where($_GET);foreach([Driver::get()->getInsertFunctions(),Driver::get()->getEditFunctions()]as$w=>$if){if(!$w||(!isset($_GET["call"])&&$vo)){foreach($if
as$kk=>$W){if(!$kk||preg_match("~$kk~",$l["type"]))$H
.="/$W";}}if($w&&$if&&!preg_match('~enum|set|bool~',$l["type"])&&!is_blob($l))$H
.="/SQL";}if($l["auto_increment"]&&!$vo)$H=lang(49);return
explode("/",$H);}function
getFieldInput($P,array$l,$Wa,$X,$q){return"";}function
processFieldInput(array$l,$X,$q=""){if($q=="SQL")return$X;$B=$l["field"];$H=q($X);if(preg_match('~^(now|getdate|uuid)$~',$q))$H="$q()";elseif(preg_match('~^current_(date|timestamp)$~',$q))$H=$q;elseif(preg_match('~^([+-]|\|\|)$~',$q))$H=idf_escape($B)." $q $H";elseif(preg_match('~^[+-] interval$~',$q))$H=idf_escape($B)." $q ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$X)&&DIALECT!="pgsql"?$X:$H);elseif(preg_match('~^(addtime|subtime|concat)$~',$q))$H="$q(".idf_escape($B).", $H)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$q))$H="$q($H)";elseif($l["type"]=="boolean"&&DIALECT=="elastic")$H=preg_match('~^(0|false)$~i',$X)?"false":"true";return
unconvert_field($l,$H);}function
getDumpFormats(){return(support("dump")?['sql'=>'SQL']:[])+parent::getDumpFormats();}function
sendDumpFormatHeaders($Sf,$ui=false){$pe=(str_contains($_POST["format"],"sql")?"sql":($ui?"tar":"csv"));if($pe=="tar")header("Content-Type: application/x-tar");elseif($pe=="sql"||$_POST["output"]=="text")header("Content-Type: text/plain; charset=utf-8");else
header("Content-Type: text/csv; charset=utf-8");return$pe;}function
getDumpOutputs(){$Oj=['file'=>lang(68),'text'=>lang(69),];if(function_exists('gzencode'))$Oj['gz']='gzip';return$Oj;}function
sendDumpOutputHeaders($Sf,$pe){if($_POST["output"]=="gz"){header("Content-Type: application/x-gzip");if(function_exists('deflate_init')){$tc=deflate_init(ZLIB_ENCODING_GZIP);ob_start(function($O,$nk)use($tc){return
deflate_add($tc,$O,($nk&PHP_OUTPUT_HANDLER_END?ZLIB_FINISH:ZLIB_NO_FLUSH));},1e6);}else{ob_start(function($O){return
gzencode($O);},1e6);}}}function
dumpTable($P,$Um,$Qo=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($Um)dump_csv(array_keys(fields($P)));}else{if($Qo==2){$m=[];foreach(fields($P)as$B=>$l)$m[]=idf_escape($B)." $l[full_type]";$f="CREATE TABLE ".table($P)." (".implode(", ",$m).")";}else$f=create_sql($P,$_POST["auto_increment"],$Um);set_utf8mb4($f);if($Um&&$f){if($Um=="DROP+CREATE"||$Qo==1)echo"DROP ".($Qo==2?"VIEW":"TABLE")." IF EXISTS ".table($P).";\n";if($Qo==1)$f=remove_definer($f);echo"$f;\n\n";}}}function
dumpData($P,$Um,$F){if($Um){$Sh=(DIALECT=="sqlite"?0:1048576);$m=[];$Tf=false;if($_POST["format"]=="sql"){if($Um=="TRUNCATE+INSERT")echo
truncate_sql($P).";\n";$m=fields($P);if(DIALECT=="mssql"){foreach($m
as$l){if($l["auto_increment"]){echo"SET IDENTITY_INSERT ".table($P)." ON;\n";$Tf=true;break;}}}}$G=Connection::get()->query($F,1);if($G){$mg="";$qb="";$Wg=[];$kf=[];$Xm="";$_c=0;while($I=($P!=''?$G->fetchAssoc():$G->fetchRow())){if(!$Wg){$Y=[];foreach($I
as$W){$l=$G->fetchField();if(!empty($m[$l->name]['generated'])){$kf[$l->name]=true;continue;}$Wg[]=$l->name;$w=idf_escape($l->name);$Y[]="$w = VALUES($w)";}$Xm=($Um=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Y):"").";\n";}if($_POST["format"]!="sql"){if($Um=="table"){dump_csv($Wg);$Um="INSERT";}dump_csv($I);}else{if(!$mg)$mg="INSERT INTO ".table($P)." (".implode(", ",array_map('AdminNeo\idf_escape',$Wg)).") VALUES";foreach($I
as$w=>$W){if(isset($kf[$w])){unset($I[$w]);continue;}$l=isset($m[$w])?$m[$w]:null;$I[$w]=($W===null?"NULL":($W===false?0:(!$l?(is_utf8($W)?q($W):Driver::get()->quoteBinary($W)):unconvert_field($l,preg_match(number_type(),$l["type"])&&!preg_match('~\[~',$l["full_type"])&&is_numeric($W)?$W:(!is_blob($l)||is_utf8($W)?q($W):Driver::get()->quoteBinary($W))))));}$Dl=($Sh?"\n":" ")."(".implode(",\t",$I).")";if(!$qb)$qb=$mg.$Dl;elseif(DIALECT=="mssql"?$_c%1000!=0:strlen($qb)+4+strlen($Dl)+strlen($Xm)<$Sh)$qb
.=",$Dl";else{echo$qb.$Xm;$qb=$mg.$Dl;}}$_c++;}if($qb)echo$qb.$Xm;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",Connection::get()->getError())."\n";if($Tf)echo"SET IDENTITY_INSERT ".table($P)." OFF;\n";}}function
getImportFilePath(){return"adminneo.sql";}function
printDatabaseMenu(){echo"<p class='links top-links'>\n";$Qi=isset($_GET["ns"])?$_GET["ns"]:null;if($Qi==""&&support("database"))echo'<a href="',h(ME),'database=">',icon("edit"),lang(70),"</a>\n";if($Qi!=""&&support("scheme"))echo"<a href='",h(ME),"scheme='>",icon("edit"),lang(71),"</a>\n";if($Qi!=="")echo'<a href="',h(ME),'schema=">',icon("schema"),lang(72),"</a>\n";if(support("privileges"))echo"<a href='",h(ME),"privileges='>",icon("users"),lang(73),"</a>\n";echo"</p>\n";}function
printNavigation($ni){if($ni=="auth"){$Nj="";foreach((array)$_SESSION["pwds"]as$Mo=>$lm){foreach($lm
as$L=>$Fo){foreach($Fo
as$U=>$E){if($E!==null){$Qc=$_SESSION["db"][$Mo][$L][$U];foreach(($Qc?array_keys($Qc):[""])as$i){$im=$this->admin->getServerName($L,false);$S=h(get_driver_name($Mo,$L)).($U!=""||$im!=""?" - ":"").h($U).($U!=""&&$im!=""?"@":"").h($im).($i!=""?h(" - $i"):"");$Nj
.="<li><a href='".h(auth_url($Mo,$L,$U,$i))."' class='primary' title='$S'>$S</a></li>\n";}}}}}if($Nj)echo"<nav id='logins'><menu>\n$Nj</menu></nav>\n";}else{$this->admin->printDatabaseSwitcher($ni);$xa=[];if(DB==""||!$ni){if(support("sql")){$xa[]="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".icon("command").lang(41)."</a>";$xa[]="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".icon("import").lang(74)."</a>";}$xa[]="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".icon("export").lang(75)."</a>";}if(DB=="")$xa[]='<a href="'.h(ME).'database="'.bold($_GET["database"]==="").">".icon("database-add").lang(76)."</a>\n";if(DB!=""&&$_GET["ns"]===""&&!$ni)$xa[]='<a href="'.h(ME).'scheme="'.bold($_GET["scheme"]==="").">".icon("database-add").lang(77)."</a>\n";if(DB!=""&&$_GET["ns"]!==""&&!$ni)$xa[]='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".icon("table-add").lang(78)."</a>\n";if($xa)echo"<p class='links'>".implode("\n",$xa)."</p>";$R=[];if($_GET["ns"]!==""&&!$ni&&DB!=""){Connection::get()->selectDatabase(DB);$R=table_status('',true);}if($_GET["ns"]!==""&&!$ni&&DB!=""){if($R){$this->admin->printTablesFilter();$this->admin->printTableList($R);}else
echo"<div id='tables'><p>".lang(79)."</p></div>\n";}elseif($_GET["ns"]!==""&&DB!="")echo"<div id='tables'></div>\n";if(support("sql")||DIALECT=="elastic"||DIALECT=="mongo"){echo"<script".nonce().">\n";if(support("sql")&&$R){$yh=[];foreach($R
as$P=>$T)$yh[]=js_escape_re($P);$in=support("table")&&!$this->config->isSelectionPreferred()?"table":"select";echo"window.jushLinks = { ".DIALECT.": {\n",js_escape_key(ME.$in.'=$&'),': /\b(?<!\$)('.implode('|',$yh).')(?!\$)\b/g';$Fm=["sql","check","event","procedure","trigger","view","type","table","processlist"];if(support('routine')&&array_intersect_key($_GET,array_flip($Fm))){foreach(routines()as$I)echo",\n",js_escape_key(ME.'function='.urlencode($I["SPECIFIC_NAME"]).'&name=$&'),': /\b'.js_escape_re($I["ROUTINE_NAME"]).'(?=["`\]]?\()/g';}echo"\n}};\n";foreach(["bac","bra","sqlite_quo","mssql_bra"]as$W)echo"jushLinks.$W = jushLinks.".DIALECT.";\n";}if(DIALECT!="elastic"&&DIALECT!="mongo"&&$this->getConfig()->isSqlAutocompletionEnabled()&&(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"]))){$tn=array_fill_keys(array_keys($R),[]);foreach(Driver::get()->getAllFields()as$P=>$m){foreach($m
as$l)$tn[$P][]=$l["field"];}echo"window.addEventListener('DOMContentLoaded', () => { autocompletion = jush.autocompleteSql('".idf_escape("")."', ".json_encode($tn,JSON_HEX_TAG)."); });\n";}echo"</script>\n";}echo
script("let autocompletion;\nwindow.addEventListener('DOMContentLoaded', () => { initSyntaxHighlighting('".js_escape(doc_version())."', '".js_escape(Connection::get()->getFlavor())."', autocompletion); });");}}function
printDatabaseSwitcher($ni){$h=$this->admin->getDatabases();if(!$h&&DIALECT!="sqlite")return;echo"<div class='db-selector'><form action=''>";hidden_fields_get();echo"<div>";if($h)echo"<select id='database-select' name='db' title='",lang(31),"'>".optionlist([""=>"(".lang(80).")"]+$h,DB)."</select>".script("mixin(gid('database-select'), {onmousedown: dbMouseDown, onchange: dbChange});");else
echo"<input id='database-select' class='input' name='db' value='".h(DB)."' title='",lang(31),"' autocapitalize='off'>\n";echo"<input type='submit' value='".lang(81)."' class='button ".($h?"hidden":"")."'>\n","</div>";if(support("scheme")&&$ni!="db"&&DB!=""&&Connection::get()->selectDatabase(DB)){echo"<div>","<select id='scheme-select' name='ns' title='",lang(82),"'>".optionlist([""=>"(".lang(83).")"]+$this->admin->getSchemas(),$_GET["ns"])."</select>".script("mixin(gid('scheme-select'), {onmousedown: dbMouseDown, onchange: dbChange});"),"</div>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}foreach(["import","sql","schema","dump","privileges"]as$W){if(isset($_GET[$W])){echo
input_hidden($W);break;}}echo"</form></div>\n";}function
printTableList(array$R){$Cd=$this->settings->isNavigationDual()||$this->settings->isNavigationHover();$bi=($Cd?"class='dual".($this->settings->isNavigationHover()?" hover":"")."'":($this->settings->isNavigationReversed()?"class='reversed'":""));echo"<nav id='tables'><div class='scroll-marker'></div><menu $bi>";foreach($R
as$P=>$N){$P="$P";$B=$this->admin->getTableName($N);if($B==""||(isset($N["Partition"])?$N["Partition"]:false))continue;echo"<li>";$ya=in_array($P,[$_GET["table"],$_GET["select"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"]]);$Nb="primary".(is_view($N)?" view":"");$bn=support("table")||support("indexes");$Rl=h(ME)."select=".urlencode($P);$kn=h(ME)."table=".urlencode($P);if($this->settings->isSelectionPreferred()){if($this->settings->isNavigationReversed()&&$bn)echo" <a href='$kn' title='",lang(35),"' class='secondary'>",icon("structure"),"</a>";echo"<a href='$Rl'",bold($ya,$Nb)," data-primary='true' title='$B'>$B</a>";if($Cd&&$bn)echo" <a href='$kn' title='",lang(35),"' class='secondary'>",icon_solo("structure"),"</a>";}else{if($this->settings->isNavigationReversed())echo" <a href='$Rl' title='",lang(34),"' class='secondary'>",icon("data"),"</a>";if($bn)echo"<a href='$kn'",bold($ya,$Nb)," data-primary='true' title='$B'>$B</a>";else
echo"<span data-primary='true'",bold($ya,$Nb),">$B</span>";if($Cd)echo" <a href='$Rl' title='",lang(34),"' class='secondary'>",icon_solo("data"),"</a>";}echo"</li>\n";}echo"</menu></nav>\n",script("initTablesList(".json_encode($this->admin->getDatabase(),JSON_HEX_TAG).");");}function
getSettingsRows($sf){$M=parent::getSettingsRows($sf);if($sf==1){$C=[""=>lang(11),Config::$NavigationSimple=>lang(84),Config::$NavigationDual=>lang(85),Config::$NavigationHover=>lang(86),Config::$NavigationReversed=>lang(87)];$j=$C[$this->config->getNavigationMode()];$C[""].=" ($j)";$M["navigationMode"]="<tr><th>".lang(88)."</th>"."<td>".html_radios("navigationMode",$C,($ta=$this->settings->getParameter("navigationMode"))!==null?$ta:"")."<span class='input-hint'>".lang(89)."</span>"."</td></tr>\n";$C=[""=>lang(11),0=>lang(35),1=>lang(34),];$j=$C[$this->config->isSelectionPreferred()?1:0];$C[""].=" ($j)";$M["preferSelection"]="<tr><th id='label-links'>".lang(90)."</th>"."<td>".html_select("preferSelection",$C,($ta=$this->settings->getParameter("preferSelection"))!==null?$ta:"","","label-links",true)."<span class='input-hint'>".lang(91)."</span>"."</td></tr>\n";}return$M;}function
getForeignColumnInfo(array$Te,$c){return
null;}}class
TmpFile{private$handler;private$size;function
__construct(){$this->handler=tmpfile();}function
getSize(){return$this->size;}function
write($sc){if(!$this->handler)return;$this->size+=strlen($sc);fwrite($this->handler,$sc);}function
send(){if(!$this->handler)return;fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}function
print_select_result(Result$G,$e=null,array$Dj=[],$y=0){$yh=[];$v=[];$d=[];$mb=[];$jo=[];$H=[];for($r=0;(!$y||$r<$y)&&($I=$G->fetchRow());$r++){if(!$r){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>";for($Ng=0;$Ng<count($I);$Ng++){$l=$G->fetchField();if(!$l){echo"<th></th>";continue;}$B=$l->name;$Cj=isset($l->orgtable)?$l->orgtable:"";$Bj=isset($l->orgname)?$l->orgname:$B;if(isset($l->table))$H[$l->table]=$Cj;if($Dj&&DIALECT=="sql")$yh[$Ng]=($B=="table"?"table=":($B=="possible_keys"?"indexes=":null));elseif($Cj!=""){if(!isset($v[$Cj])){$v[$Cj]=[];foreach(indexes($Cj,$e)as$u){if($u["type"]=="PRIMARY"){$v[$Cj]=array_flip($u["columns"]);break;}}$d[$Cj]=$v[$Cj];}if(isset($d[$Cj][$Bj])){unset($d[$Cj][$Bj]);$v[$Cj][$Bj]=$Ng;$yh[$Ng]=$Cj;}}if($l->charsetnr==63)$mb[$Ng]=true;$jo[$Ng]=$l->type;$S=trim(($Cj!=""?"$Cj.$Bj":($l->name!=$Bj?$Bj:""))." ".Driver::get()->getTypeName($l));echo"<th".($S!=""?" title='".h($S)."'":"").">".h($B).($Dj?doc_link(['sql'=>"explain-output.html#explain_".strtolower($B),'mariadb'=>"reference/sql-statements/administrative-sql-statements/analyze-and-explain-statements/explain#columns-in-explain-...-select",]):"");}echo"</thead>\n";}echo"<tr>";foreach($I
as$w=>$W){$z="";if(isset($yh[$w])&&!$d[$yh[$w]]){if($Dj&&DIALECT=="sql"){$P=$I[array_search("table=",$yh)];$z=ME.$yh[$w].urlencode($Dj[$P]!=""?$Dj[$P]:$P);}else{$z=ME."edit=".urlencode($yh[$w]);foreach($v[$yh[$w]]as$Qb=>$Ng)$z
.="&where".urlencode("[".bracket_escape($Qb)."]")."=".urlencode($I[$Ng]);}}$T=($mb[$w]?'blob':($jo[$w]==254?'char':''));$l=['full_type'=>$T,'type'=>$T,];$W=select_value($W,$z,$l,null);$Nb=$jo[$w]<=9||$jo[$w]==246?"class='number'":"";echo"<td $Nb>$W</td>";}}if($r)echo"</table>\n</div>";else
echo"<p class='message'>".lang(92);echo"\n";return$H;}function
referencable_primary($Xl){$H=[];foreach(table_status('',true)as$mn=>$P){if($mn!=$Xl&&fk_support($P)){foreach(fields($mn)as$l){if($l["primary"]){if($H[$mn]){unset($H[$mn]);break;}$H[$mn]=$l;}}}}return$H;}function
textarea($B,$X,$J=10,$Xb=80){echo"<textarea name='".h($B)."' rows='$J' cols='$Xb' class='sqlarea jush-".DIALECT."' spellcheck='false' wrap='off'>";if(is_array($X)){foreach($X
as$W)echo
h($W[0])."\n\n\n";}else
echo
h($X);echo"</textarea>";}function
select_input($Wa,$C,$X="",$lj="",$ok=""){if($C&&$X!=""&&!isset($C[$X]))$C=[$X=>$X]+$C;$xn=($C?"select":"input");return"<$xn $Wa".($C?"><option value=''>$ok".optionlist($C,$X,true)."</select>":" size='10' value='".h($X)."' placeholder='$ok'>").($lj?script("qsl('$xn').onchange = $lj;",""):"");}function
json_row($w,$W=null){static$Je=true;if($Je)echo"{";if($w!=""){echo($Je?"":",")."\n\t\"".addcslashes($w,"\r\n\t\"\\/").'": '.($W!==null?'"'.addcslashes($W,"\r\n\t\"\\/").'"':'null');$Je=false;}else{echo"\n}\n";$Je=true;}}function
edit_type($w,$l,$Tb,$Ue=[],$se=[]){$T=isset($l["type"])?$l["type"]:null;echo'<td><select name="',h($w),'[type]" class="type" aria-labelledby="label-type">';$vd=Driver::get()->getTypes();if($T&&!isset($vd[$T])&&!isset($Ue[$T])&&!in_array($T,$se))$se[]=$T;$Tm=Driver::get()->getStructuredTypes();if($Ue)$Tm[lang(93)]=$Ue;echo
optionlist(array_merge($se,$Tm),$T),'</select><td><input name="',h($w),'[length]" value="',h(isset($l["length"])?$l["length"]:null),'" size="3"',(!(isset($l["length"])?$l["length"]:null)&&preg_match('~var(char|binary)$~',$T)?" class='input required'":" class='input'"),' aria-labelledby="label-length"><td class="options">',($Tb?"<select name='".h($w)."[collation]'".option_types($T,'(char|text|enum|set)$').'><option value="">('.lang(94).')'.optionlist($Tb,isset($l["collation"])?$l["collation"]:null).'</select>':''),(Driver::get()->getUnsigned()?"<select name='".h($w)."[unsigned]'".option_types($T,'^$|'.number_type()).'><option>'.optionlist(Driver::get()->getUnsigned(),isset($l["unsigned"])?$l["unsigned"]:null).'</select>':''),(isset($l['on_update'])?"<select name='".h($w)."[on_update]'".option_types($T,'timestamp|datetime').'>'.optionlist([""=>"(".lang(95).")","CURRENT_TIMESTAMP"],(preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?"CURRENT_TIMESTAMP":$l["on_update"])).'</select>':''),($Ue?"<select name='".h($w)."[on_delete]'".option_types($T,'`')."><option value=''>(".lang(96).")".optionlist(Driver::get()->getOnActions(),isset($l["on_delete"])?$l["on_delete"]:null)."</select> ":" ");}function
option_types($T,$jo){return" data-types='".h($jo)."'".(preg_match("~$jo~",$T)?"":" class='hidden'");}function
process_length($x){if(preg_match('~^\s*\(?\s*max\s*\)?\s*$~i',$x))return"(max)";$Wd=Driver::$EnumLengthPattern;return(preg_match("~^\\s*\\(?\\s*$Wd(?:\\s*,\\s*$Wd)*+\\s*\\)?\\s*\$~",$x)&&preg_match_all("~$Wd~",$x,$A)?"(".implode(",",$A[0]).")":preg_replace('~^[-+.0-9].*~','(\0)',preg_replace('~[^-0-9,.+()[\]]~','',$x)));}function
process_type($l,$Rb="COLLATE"){return" $l[type]".process_length($l["length"]).(preg_match(number_type(),$l["type"])&&in_array($l["unsigned"],Driver::get()->getUnsigned())?" $l[unsigned]":"").(preg_match('~char|text|enum|set~',$l["type"])&&$l["collation"]?" $Rb ".(DIALECT=="mssql"?$l["collation"]:q($l["collation"])):"");}function
process_field($l,$go){if($l["on_update"])$l["on_update"]=preg_replace('~current_timestamp(\(\))?~i',"CURRENT_TIMESTAMP",$l["on_update"]);return[idf_escape(trim($l["field"])),process_type($go),($l["null"]?" NULL":" NOT NULL"),default_value($l),(preg_match('~timestamp|datetime~',$l["type"])&&$l["on_update"]?" ON UPDATE ".$l["on_update"]:""),(support("comment")&&$l["comment"]!=""?" COMMENT ".q(normalize_newlines($l["comment"])):""),($l["auto_increment"]?auto_increment():null),];}function
normalize_newlines($X){return
str_replace("\r","",(string)$X);}function
default_value($l){if($l["default"]===null)return"";$j=normalize_newlines($l["default"]);$jf=$l["generated"];if(in_array($jf,Driver::get()->getGenerated())){if(DIALECT=="mssql")return" AS ($j)".($jf=="VIRTUAL"?"":" $jf");else
return" GENERATED ALWAYS AS ($j) $jf";}if(stripos($j,"GENERATED ")===0)return" $j";if(preg_match('~char|binary|text|json|enum|set~',$l["type"])||preg_match('~^(?![a-z])~i',$j)){if(DIALECT=="sql"&&preg_match('~text|json~',$l["type"]))return" DEFAULT (".q($j).")";else
return" DEFAULT ".q($j);}else{$j=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$j);return" DEFAULT ".(DIALECT=="sqlite"?"($j)":$j);}}function
type_class($T){foreach(['char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',]as$Nb=>$kk){if(preg_match("~$Nb|$kk~",$T))return"class='$Nb'";}return"";}function
edit_fields(array$m,array$Tb,$T="TABLE",$Ue=[]){$m=array_values($m);$fc=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$dc=$fc?"":"class='hidden'";echo"<thead><tr>\n";if(support("move_col"))echo"<th class='jsonly'></th>";if($T=="PROCEDURE")echo"<td></td>";echo"<th id='label-name'>",($T=="TABLE"?lang(97):lang(98)),"</th>\n","<td id='label-type'>",lang(45),"<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>",script("gid('enum-edit').onblur = onFieldLengthBlur;"),"</td>\n","<td id='label-length'>",lang(99),"</td>\n","<td>",lang(100),"</td>\n";if($T=="TABLE")echo"<td id='label-null'>NULL</td>\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='",lang(49),"'>AI</abbr>",doc_link(['sql'=>"example-auto-increment.html",'mariadb'=>"reference/data-types/auto_increment",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",]),"</td>\n","<td id='label-default'>",lang(101),"</td>\n",support("comment")?"<td id='label-comment' $dc>".lang(48)."</td>\n":"";echo"<td>","<button name='add[",(support("move_col")?0:count($m)),"]' value='1' title='",lang(102),"' class='button light'>",icon_solo("add"),"</button>",(support("move_col")?"":script("qsl('button').onclick = onAddLastFieldRowClick;")),script("row_count = ".count($m).";"),"</td>\n","</tr></thead>\n";$Nb=support("move_col")?"class='sortable'":"";echo"<tbody $Nb>\n";foreach($m
as$r=>$l){$r++;$Ej=$l[($_POST?"orig":"field")];$kd=(isset($_POST["add"][$r-1])||(isset($l["field"])&&!(isset($_POST["drop_col"][$r])?$_POST["drop_col"][$r]:null)))&&(support("drop_col")||$Ej=="");echo"<tr",($kd?"":" hidden"),">\n";if(support("move_col"))echo"<th class='handle jsonly'>",icon_solo("handle"),"</th>";if($T=="PROCEDURE")echo"<td>",html_select("fields[$r][inout]",Driver::get()->getInOut(),$l["inout"]),"</td>\n";echo"<th>";if($kd)echo"<input class='input' name='fields[$r][field]' value='",h($l["field"]),"' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name' ".(isset($_POST["add"][$r-1])?"autofocus":"").">";echo
input_hidden("fields[$r][orig]",$Ej);edit_type("fields[$r]",$l,$Tb,$Ue);echo"</th>\n";if($T=="TABLE"){echo"<td>",checkbox("fields[$r][null]",1,$l["null"],"","","block","label-null"),"</td>\n";$Ib=$l["auto_increment"]?"checked":"";echo"<td><label class='block'><input type='radio' name='auto_increment_col' value='$r' $Ib aria-labelledby='label-ai'></label></td>\n","<td class='default-value'>";if(Driver::get()->getGenerated())echo
html_select("fields[$r][generated]",array_merge(["","DEFAULT"],Driver::get()->getGenerated()),$l["generated"]);else
echo
checkbox("fields[$r][generated]",1,$l["generated"],"","","","label-default");$Wa="name='fields[$r][default]' aria-labelledby='label-default'";$X=h($l["default"]);if(str_contains($X,"\n")){if($X[0]=="\n")$X="\n$X";echo"<textarea $Wa rows='3' cols='30' style='vertical-align: bottom;'>$X</textarea>";}else
echo"<input class='input' $Wa value='$X'>";echo"</td>\n";if(support("comment")){$Rh=Connection::get()->isMinVersion("5.5")?1024:255;$Wa="name='fields[$r][comment]' data-maxlength='$Rh' aria-labelledby='label-comment'";$X=h($l["comment"]);echo"<td $dc>";if(str_contains($X,"\n")){if($X[0]=="\n")$X="\n$X";echo"<textarea $Wa rows='3' cols='30' style='vertical-align: bottom;'>$X</textarea>";}else
echo"<input class='input' $Wa value='$X'>";echo"</td>\n";}}echo"<td>";if(support("move_col"))echo"<button name='add[$r]' value='1' title='".lang(102)."' class='button light'>",icon_solo("add"),"</button>","<button name='up[$r]' value='1' title='".lang(103)."' class='button light hidden'>",icon_solo("arrow-up"),"</button>","<button name='down[$r]' value='1' title='".lang(104)."' class='button light hidden'>",icon_solo("arrow-down"),"</button>";if($Ej==""||support("drop_col"))echo"<button name='drop_col[$r]' value='1' title='".lang(59)."' class='button light'>",icon_solo("remove"),"</button>";echo"</td>\n</tr>\n";}echo"</tbody>";}function
process_fields(&$m){$Yi=0;if($_POST["up"]){$ih=0;foreach($m
as$w=>$l){if(key($_POST["up"])==$w){unset($m[$w]);array_splice($m,$ih,0,[$l]);break;}if(isset($l["field"]))$ih=$Yi;$Yi++;}}elseif($_POST["down"]){$af=false;foreach($m
as$w=>$l){if(isset($l["field"])&&$af){unset($m[key($_POST["down"])]);array_splice($m,$Yi,0,[$af]);break;}if(key($_POST["down"])==$w)$af=$l;$Yi++;}}elseif($_POST["add"]){$m=array_values($m);array_splice($m,key($_POST["add"]),0,[[]]);}elseif(!$_POST["drop_col"])return
false;return
true;}function
normalize_enum($_){$W=$_[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($W[0].$W[0],$W[0],substr($W,1,-1))),'\\'))."'";}function
grant($nf,array$Kk,$d,$jj,$Co){if(!$Kk)return
true;if($Kk==["ALL PRIVILEGES","GRANT OPTION"]){if($nf)return(bool)queries("GRANT ALL PRIVILEGES ON $jj TO $Co WITH GRANT OPTION");else
return
queries("REVOKE ALL PRIVILEGES ON $jj FROM $Co")&&queries("REVOKE GRANT OPTION ON $jj FROM $Co");}if($Kk==["GRANT OPTION","PROXY"]){if($nf)return(bool)queries("GRANT PROXY ON $jj TO $Co WITH GRANT OPTION");else
return(bool)queries("REVOKE PROXY ON $jj FROM $Co");}return(bool)queries(($nf?"GRANT ":"REVOKE ").preg_replace('~(GRANT OPTION)\([^)]*\)~','$1',implode("$d, ",$Kk).$d)." ON $jj ".($nf?"TO ":"FROM ").$Co);}function
drop_create($xd,$f,$zd,$Bn,$Ad,$Hh,$fi,$di,$ei,$gj,$Ii){if($_POST["drop"])query_redirect($xd,$Hh,$fi);elseif($gj=="")query_redirect($f,$Hh,$ei);elseif(support("transaction_ddl")){Driver::get()->begin();queries_redirect($Hh,$di,queries($xd)&&queries($f)&&Driver::get()->commit());Driver::get()->rollback();}elseif($gj!=$Ii){$Cc=queries($f);queries_redirect($Hh,$di,$Cc&&queries($xd));if($Cc)queries($zd);}else
queries_redirect($Hh,$di,queries($Bn)&&queries($Ad)&&queries($xd)&&queries($f));}function
create_trigger($jj,array$ao){$Ln=" $ao[Timing] $ao[Event]".(preg_match('~ OF~',$ao["Event"])?" $ao[Of]":"");return"CREATE TRIGGER ".idf_escape($ao["Trigger"]).(DIALECT=="mssql"?$jj.$Ln:$Ln.$jj).rtrim(" $ao[Type]\n$ao[Statement]",";").";";}function
create_routine($yl,$I){$om=[];$m=(array)$I["fields"];ksort($m);$Xf=implode("|",Driver::get()->getInOut());foreach($m
as$l){if($l["field"]!="")$om[]=(preg_match("~^($Xf)\$~",$l["inout"])?"$l[inout] ":"").idf_escape($l["field"]).process_type($l,"CHARACTER SET");}$Wc=rtrim($I["definition"],";");return"CREATE $yl ".idf_escape(trim($I["name"]))." (".implode(", ",$om).")".($yl=="FUNCTION"?" RETURNS".process_type($I["returns"],"CHARACTER SET"):"").($I["language"]?" LANGUAGE $I[language]":"").(DIALECT=="pgsql"?" AS ".q($Wc):"\n$Wc;");}function
remove_definer($F){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$F);}function
format_foreign_key($p){$kj=implode("|",Driver::get()->getOnActions());$i=$p["db"];$Qi=$p["ns"];return" FOREIGN KEY (".implode(", ",array_map('AdminNeo\idf_escape',$p["source"])).") REFERENCES ".($i!=""&&$i!=$_GET["db"]?idf_escape($i).".":"").($Qi!=""&&$Qi!=$_GET["ns"]?idf_escape($Qi).".":"").idf_escape($p["table"])." (".implode(", ",array_map('AdminNeo\idf_escape',$p["target"])).")".(preg_match("~^($kj)\$~",$p["on_delete"])?" ON DELETE $p[on_delete]":"").(preg_match("~^($kj)\$~",$p["on_update"])?" ON UPDATE $p[on_update]":"").(isset($p["deferrable"])?" $p[deferrable]":"");}function
tar_file($o,TmpFile$Pn){$Df=pack("a100a8a8a8a12a12",$o,644,0,0,decoct($Pn->getSize()),decoct(time()));$Kb=8*32;for($r=0;$r<strlen($Df);$r++)$Kb+=ord($Df[$r]);$Df
.=sprintf("%06o",$Kb)."\0 ";echo$Df,str_repeat("\0",512-strlen($Df));$Pn->send();echo
str_repeat("\0",511-($Pn->getSize()+511)%512);}function
doc_link(array$jk,$Cn="<sup>?</sup>"){if(!(isset($jk[DIALECT])?$jk[DIALECT]:null))return"";$No=doc_version();$yo=['sql'=>"https://dev.mysql.com/doc/refman/$No/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(Connection::get()->isCockroachDB()?"current":$No)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".str_replace(".","",$No)."&id=",'elastic'=>"https://www.elastic.co/guide/en/elasticsearch/reference/$No/",];if(Connection::get()->isMariaDB()){$yo['sql']="https://mariadb.com/docs/server/";$jk['sql']=isset($jk['mariadb'])?$jk['mariadb']:str_replace(".html","",$jk['sql']);}return"<a href='".h($yo[DIALECT].$jk[DIALECT].(DIALECT=='mssql'?"?view=sql-server-ver$No":""))."'".target_blank().">$Cn</a>";}function
doc_version(){return
preg_replace('~^(\d\.?\d).*~s','\1',Connection::get()->getVersion());}function
db_size($i){if(!Connection::get()->selectDatabase($i))return"?";$H=0;foreach(table_status()as$Q)$H+=$Q["Data_length"]+$Q["Index_length"];return
format_number($H);}function
set_utf8mb4($f){static$om=false;if(!$om&&preg_match('~\butf8mb4~i',$f)){$om=true;echo"SET NAMES ".charset(Connection::get()).";\n\n";}}error_reporting(E_ALL&~E_DEPRECATED);set_error_handler(function($Yd,$k){return(bool)preg_match('~^Undefined (array key|offset|index)~',$k);},E_WARNING|E_NOTICE);;$Ge=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($Ge||ini_get("filter.default_flags")){foreach(['_GET','_POST','_COOKIE','_SERVER']as$W){$qo=filter_input_array(constant("INPUT$W"),FILTER_UNSAFE_RAW);if($qo)$$W=$qo;}}foreach(["file","ext","username","db","ns"]as$w){if(isset($_GET[$w])&&!is_string($_GET[$w])){http_response_code(400);exit;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");class
Server{private$params;private$key;function
__construct(array$Tj,$w=null){$this->params=$Tj;$this->key=$w;}function
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
getConfigParams(){$Tj=isset($this->params["config"])?$this->params["config"]:[];$mf=["servers"];foreach($mf
as$Sj){if(isset($Tj[$Sj]))unset($Tj[$Sj]);}return$Tj;}}class
Config{static$NavigationSimple="simple";static$NavigationDual="dual";static$NavigationHover="hover";static$NavigationReversed="reversed";private$params;private$servers=[];function
__construct(array$Tj){$this->params=$Tj;if(isset($this->params["servers"])){foreach($this->params["servers"]as$w=>$L){$gm=new
Server($L,is_string($w)?$w:null);$this->params["servers"][$w]=$gm;$this->servers[$gm->getKey()]=$gm;}}}function
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
getDefaultDriver(array$wd){$ud=isset($this->params["defaultDriver"])?$this->params["defaultDriver"]:null;return$ud&&isset($wd[$ud])?$ud:key($wd);}function
getDefaultServer(){$L=isset($this->params["defaultServer"])?$this->params["defaultServer"]:null;if($L===null)return
null;$gm=isset($this->params["servers"][$L])?$this->params["servers"][$L]:null;if($gm)return$gm->getKey();return$L;}function
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
getServerPairs(array$wd){$um=null;foreach($this->servers
as$L){if(!isset($wd[$L->getDriver()]))continue;if(!$um)$um=$L->getDriver();elseif($L->getDriver()!=$um){$um=null;break;}}$hm=[];foreach($this->servers
as$w=>$L){if(!isset($wd[$L->getDriver()]))continue;$fm=$L->getName();if($um&&$fm)$hm[$w]=$fm;else$hm[$w]=$wd[$L->getDriver()].($fm!=""?" - $fm":"");}return$hm;}function
getServer($em){return
isset($this->servers[$em])?$this->servers[$em]:null;}function
applyServer($L){$L=$this->getServer($L);if(!$L)return;$this->params=array_merge($this->params,$L->getConfigParams());}private
function
parseList($_h){if(is_array($_h))return$_h;return
preg_split('~\s*,\s*~',(string)$_h);}}class
Settings{private
static$CookieName="neo_settings";static$ColorSchemeLight="light";static$ColorSchemeDark="dark";static$NavigationWidthMin=10;static$NavigationWidthMax=30;private$config;private$params=[];function
__construct(Config$kc){$this->config=$kc;if(isset($_COOKIE[self::$CookieName])){parse_str($_COOKIE[self::$CookieName],$this->params);$this->save();}if(isset($_COOKIE["neo_lang"])){$this->updateParameter("lang",$_COOKIE["neo_lang"]);unset($_COOKIE["neo_lang"]);cookie("neo_lang","",-3600);}}static
function
readParameter($w){parse_str(isset($_COOKIE[self::$CookieName])?$_COOKIE[self::$CookieName]:"",$Tj);return
isset($Tj[$w])?$Tj[$w]:null;}function
getParameter($w,$j=null){return
isset($this->params[$w])?$this->params[$w]:$j;}function
updateParameter($w,$X){$this->updateParameters([$w=>$X]);}function
updateParameters(array$Tj){$this->params=array_filter(array_merge($this->params,$Tj),function($X){return$X!==null;});$this->save();}private
function
save(){cookie(self::$CookieName,http_build_query($this->params),7776000);}function
getTheme(){return($ta=$this->getParameter("theme"))!==null?$ta:$this->config->getTheme();}function
getColorScheme(){return$this->getParameter("colorScheme");}function
getNavigationMode(){return($ta=$this->getParameter("navigationMode"))!==null?$ta:$this->config->getNavigationMode();}function
isNavigationSimple(){return$this->getNavigationMode()==Config::$NavigationSimple;}function
isNavigationDual(){return$this->getNavigationMode()==Config::$NavigationDual;}function
isNavigationHover(){return$this->getNavigationMode()==Config::$NavigationHover;}function
isNavigationReversed(){return$this->getNavigationMode()==Config::$NavigationReversed;}function
getNavigationWidth(){$fp=$this->getParameter("navigationWidth");if($fp===null)return
null;return
min(max((float)$fp,self::$NavigationWidthMin),self::$NavigationWidthMax);}function
isSelectionPreferred(){return($ta=$this->getParameter("preferSelection"))!==null?$ta:$this->config->isSelectionPreferred();}function
isRelationLinks(){return
isset($this->params["relationLinks"])?$this->params["relationLinks"]:$this->config->isRelationLinks();}function
getRecordsPerPage(){return($ta=$this->getParameter("recordsPerPage"))!==null?$ta:$this->config->getRecordsPerPage();}function
getEnumAsSelectThreshold(){$X=$this->getParameter("enumAsSelectThreshold");if($X<0)return
null;return$X!==null?(int)$X:$this->config->getEnumAsSelectThreshold();}}class
Hash{static
function
hkdf($x,$w,$hg="",$El=""){if(extension_loaded("hash")&&PHP_VERSION_ID>=70120)return
hash_hkdf("sha1",$w,$x,$hg,$El);if($El=="")$El=str_repeat("\0",20);$Lk=self::hmacSha1($w,$El);$cj="";for($Ug="",$nb=1;!isset($cj[$x-1]);$nb++){$Ug=self::hmacSha1($Ug.$hg.chr($nb),$Lk);$cj
.=$Ug;}return
substr($cj,0,$x);}static
function
hmacSha1($g,$w){if(!extension_loaded("hash"))return
hash_hmac("sha1",$g,$w,true);if(strlen($w)>64)$w=sha1($w,true);$w=str_pad($w,64,"\0");$yg=($w^str_repeat("\x36",64));$oj=($w^str_repeat("\x5C",64));return
sha1($oj.sha1($yg.$g,true),true);}}class
Random{static
function
strongKey(){return
strtr(rtrim(base64_encode(Random::bytes(32)),"="),"+/","-_");}static
function
bytes($x){if(PHP_VERSION_ID>=70000)return
random_bytes($x);$G=self::tryAlternatives($x);if($G!==false)return$G;$G=self::lastResortRandom($x);if($G!==false)return$G;throw
new
Exception("Error generating random bytes");}private
static
function
tryAlternatives($x){if(extension_loaded("libsodium"))return
\Sodium\randombytes_buf($x);$po=DIRECTORY_SEPARATOR==="/";if($po){$G=self::readDevUrandom($x);if($G!==false)return$G;}$rb=$po&&PHP_VERSION_ID>50609&&PHP_VERSION_ID<50613;if(extension_loaded("mcrypt")&&!$rb){$G=mcrypt_create_iv($x,MCRYPT_DEV_URANDOM);if($G!==false)return$G;}$sb=PHP_VERSION_ID<50444||(PHP_VERSION_ID>50500&&PHP_VERSION_ID<50528)||(PHP_VERSION_ID>50600&&PHP_VERSION_ID<50612);if(extension_loaded("openssl")&&!$sb){$G=openssl_random_pseudo_bytes($x,$Rm);if($Rm)return$G;}return
false;}private
static
function
readDevUrandom($x){static$n=null;if($n===null)$n=@fopen("/dev/urandom","rb");if(!$n)return
false;$ml=$x;$G="";do{$g=fread($n,$ml);if($g===false)return
false;$ml-=strlen($g);$G
.=$g;}while($ml>0);return$G;}private
static
function
readCapicom($x){$Zb=new
\COM("CAPICOM.Utilities.1");$ml=$x;$G="";do{$g=base64_decode((string)$Zb->GetRandom($x,0));$ml-=strlen($g);$G
.=$g;}while($ml>0);return$G;}private
static
function
lastResortRandom($x){static$w=null;static$El=null;if($w===null){$g=$_SERVER;$g[]=uniqid("",true);shuffle($g);$w=sha1(serialize($g),true);if(extension_loaded("openssl"))$El=openssl_random_pseudo_bytes(20);else{$El="";for($r=0;$r<20;$r++)$El
.=chr((mt_rand()^mt_rand())%256);}}else{if((ord($w)%2===0)===(ord($El)%2===0))$w=Hash::hmacSha1($w,$El);else$El=Hash::hmacSha1($El,$w);}return
Hash::hkdf($x,$w,"$x",$El);}}if(!function_exists("str_starts_with")){function
str_starts_with($Cf,$Di){return
strpos($Cf,$Di)===0;}}if(!function_exists("str_contains")){function
str_contains($Cf,$Di){return
strpos($Cf,$Di)!==false;}}if(!function_exists("password_verify")){function
password_verify($E,$Bf){return
false;}}if(!function_exists("ini_set")){function
ini_set($uj,$X){return
false;}}function
version(){return
VERSION;}function
idf_unescape($t){if(!preg_match('~^[`\'"[]~',$t))return$t;$ih=substr($t,-1);$H=str_replace($ih.$ih,$ih,substr($t,1,-1));return(DIALECT=="clickhouse"?str_replace("\\\\","\\",$H):$H);}function
q($O){return
Connection::get()->quote($O);}function
number($W){return
preg_replace('~[^0-9]+~','',$W);}function
int_type(){return'(tiny|small|medium|big)?int(eger|\d)?';}function
number_type(){return'(^('.int_type().'|decimal|numeric|real|(binary_|half_|scaled_)?float\d?|(binary_)?double( precision)?|(small)?money)$)';}function
remove_slashes(array$Y,$Ge=false){$H=[];foreach($Y
as$w=>$W)$H[stripslashes($w)]=(is_array($W)?remove_slashes($W,$Ge):($Ge?$W:stripslashes($W)));return$H;}function
bracket_escape($t,$fb=false){static$Vn=[':'=>':1',']'=>':2','['=>':3','"'=>':4'];return
strtr($t,($fb?array_flip($Vn):$Vn));}function
min_version($No,$Kh=null,$e=null){if(!$e)$e=Connection::get();if($Kh&&$e->isMariaDB())$No=$Kh;return$No&&$e->isMinVersion($No);}function
charset(Connection$e){return($e->isMinVersion("5.5.3")?"utf8mb4":"utf8");}function
link_files($B,array$Fe){switch($B){case'favicon-blue.ico':$o='favicon-blue-0f5ce53a66b1e25395d0048da369f19e__f1f297b4.ico';break;case'favicon-green.ico':$o='favicon-green-def78cfa7c465c8b0e9966e3eb87407d__f1f297b4.ico';break;case'favicon-orange.ico':$o='favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__f1f297b4.ico';break;case'favicon-purple.ico':$o='favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__f1f297b4.ico';break;case'favicon-red.ico':$o='favicon-red-c2ebb34a8df5aba28e15d87728a151df__f1f297b4.ico';break;case'favicon-blue.svg':$o='favicon-blue-17e440832c1eac07527560a0d6f0d2ee__f1f297b4.svg';break;case'favicon-green.svg':$o='favicon-green-bb254c95a033f67e3d433a3df63e160d__f1f297b4.svg';break;case'favicon-orange.svg':$o='favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__f1f297b4.svg';break;case'favicon-purple.svg':$o='favicon-purple-4cfd57d31ab991e8071fe34060cd3123__f1f297b4.svg';break;case'favicon-red.svg':$o='favicon-red-a006e401273230fd6be80568c8361b57__f1f297b4.svg';break;case'apple-touch-icon-blue.png':$o='apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__f1f297b4.png';break;case'apple-touch-icon-green.png':$o='apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__f1f297b4.png';break;case'apple-touch-icon-orange.png':$o='apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__f1f297b4.png';break;case'apple-touch-icon-purple.png':$o='apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__f1f297b4.png';break;case'apple-touch-icon-red.png':$o='apple-touch-icon-red-507228751d2170d047e72142d2c02390__f1f297b4.png';break;case'logo.svg':$o='logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg';break;case'jush.css':$o='jush-b3a93b18444da26820ff61746521dede__6a1faebc.css';break;case'jush-dark.css':$o='jush-dark-f8dac59c6ad1018686e52a0e0357e421__2ec7793c.css';break;case'jush.js':$o='jush-31ccd1ce96536a294822e952872683d1__adc1a188.js';break;case'icons.svg':$o='icons-70163a2695280bf75edba563e7b5471b__f1f297b4.svg';break;case'default-blue.css':$o='default-blue-e7acfdb81453b86f081569afa115859e__e7027cc3.css';break;case'default-green.css':$o='default-green-268f042072e76ab07aa2fd0350e0aa0c__e7027cc3.css';break;case'default-orange.css':$o='default-orange-e4e5ea626cdcbe83e07c7933dc04f916__e7027cc3.css';break;case'default-purple.css':$o='default-purple-6b1de1f635d52b55797976fef486a515__e7027cc3.css';break;case'default-red.css':$o='default-red-0f424ea89c2a43c6eb0ec8f0e22362a5__e7027cc3.css';break;case'default-blue-dark.css':$o='default-blue-dark-1061ad7d216f143e3626560b92b66061__6a1faebc.css';break;case'default-green-dark.css':$o='default-green-dark-6176b1c7b42ee9f244b3a9c3d8065728__6a1faebc.css';break;case'default-orange-dark.css':$o='default-orange-dark-ef164a8d58dc89b2719f881377f73c89__6a1faebc.css';break;case'default-purple-dark.css':$o='default-purple-dark-0f4fa03fa2d9287ef390780e90d9b06d__6a1faebc.css';break;case'default-red-dark.css':$o='default-red-dark-3c1e28afe2cc92815bc7347df0d5776f__6a1faebc.css';break;case'main.js':$o='main-c1727fb392fab162b6611efe18642a5a__bd555266.js';break;default:$o=null;break;}if(!$o)return
null;return
BASE_URL."?file=".urldecode($o);}function
ini_bool($uj){$W=ini_get($uj);return
preg_match('~^(on|true|yes)$~i',$W)||(int)$W;}function
ini_bytes($jg){$W=ini_get($jg);switch(strtolower(substr($W,-1))){case'g':$W=(int)$W*1024;case'm':$W=(int)$W*1024;case'k':$W=(int)$W*1024;}return$W;}function
max_input_vars($I,$Lj){$Oh=(int)ini_get("max_input_vars");return($Oh?(int)floor(($Oh-$Lj)/$I):0);}function
max_input_vars_error(){$jg="max_input_vars";return
lang(105,"$jg = ".(int)ini_get($jg));}function
sid(){static$H;if($H===null)$H=(session_id()&&!($_COOKIE&&ini_bool("session.use_cookies")));return$H;}function
save_driver_name($ud,$L,$B){restart_session();$_SESSION["drivers"][$ud][$L]=$B;stop_session();}function
get_driver_name($ud,$L=null){return
isset($_SESSION["drivers"][$ud][$L])?$_SESSION["drivers"][$ud][$L]:Drivers::get($ud);}function
save_login($ud,$L,$U,$E,$i=""){$w=isset($_COOKIE["neo_key"])?$_COOKIE["neo_key"]:null;$_SESSION["pwds"][$ud][$L][$U]=$w?[encrypt_string($E,$w)]:$E;$_SESSION["db"][$ud][$L][$U][$i]=true;}function
delete_login($ud,$L,$U){unset($_SESSION["pwds"][$ud][$L][$U]);unset($_SESSION["db"][$ud][$L][$U]);unset($_SESSION["permanent"][$ud][$L][$U]);}function
get_password(){$E=get_session("pwds");if(is_array($E))return$_COOKIE["neo_key"]?decrypt_string($E[0],$_COOKIE["neo_key"]):false;return$E;}function
get_vals($F,$c=0){$H=[];$G=Connection::get()->query($F);if(is_object($G)){while($I=$G->fetchRow())$H[]=$I[$c];}return$H;}function
get_key_vals($F,$e=null,$pm=true){if(!$e)$e=Connection::get();$H=[];$G=$e->query($F);if(is_object($G)){while($I=$G->fetchRow()){if($pm)$H[$I[0]]=$I[1];else$H[]=$I[0];}}return$H;}function
get_rows($F,$e=null,$k="<p class='error'>"){if(!$e)$e=Connection::get();$H=[];$G=$e->query($F);if(is_object($G)){while($I=$G->fetchAssoc())$H[]=$I;}elseif(!$G&&!is_object($e)&&$k&&(defined("AdminNeo\PAGE_HEADER")||$k=="-- "))echo$k.error()."\n";return$H;}function
unique_array(array$I,array$v){foreach($v
as$u){if(!preg_match("~^(PRIMARY|UNIQUE)$~",$u["type"])||(isset($u["partial"])?$u["partial"]:"")!="")continue;$mo=[];foreach($u["columns"]as$w){if(!isset($I[$w]))continue
2;$mo[$w]=$I[$w];}return$mo;}return
null;}function
escape_key($w){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$w,$_))return$_[1].idf_escape(idf_unescape($_[2])).$_[3];return
idf_escape($w);}function
where($Z,$m=[]){$jc=[];foreach((array)$Z["where"]as$w=>$W){$w=bracket_escape($w,true);$c=escape_key($w);$l=isset($m[$w])?$m[$w]:null;$Be=isset($l["type"])?$l["type"]:null;$ff=isset($l["full_type"])?$l["full_type"]:null;$Eg=$l&&(is_blob($l)||preg_match('~binary~',$Be));if($Eg&&!is_utf8($W))$jc[]="$c = ".Driver::get()->quoteBinary($W);elseif(DIALECT=="sql"&&$Be=="json")$jc[]="$c = CAST(".q($W)." AS JSON)";elseif(DIALECT=="pgsql"&&preg_match('~^jsonb?$~',$ff))$jc[]="$c::jsonb = ".q($W)."::jsonb";elseif(DIALECT=="sql"&&is_numeric($W)&&strpos($W,".")!==false)$jc[]="$c LIKE ".q($W);elseif(DIALECT=="mssql"&&strpos($Be,"datetime")===false)$jc[]="$c LIKE ".q(preg_replace('~[_%[]~','[\0]',$W));else$jc[]="$c = ".(isset($m[$w])?unconvert_field($m[$w],q($W)):q($W));if(DIALECT=="sql"&&preg_match('~char|text~',$Be)&&preg_match("~[^ -@]~",$W))$jc[]="$c = ".q($W)." COLLATE ".charset(Connection::get())."_bin";}foreach((array)$Z["null"]as$w)$jc[]=escape_key($w)." IS NULL";return
implode(" AND ",$jc);}function
where_columns($Z,$m=[]){$d=[];foreach((array)$Z["null"]as$w)$d[$w]=true;foreach((array)$Z["where"]as$w=>$W){$w=bracket_escape($w,true);foreach($m
as$B=>$l){if($w==$B||strpos($w,idf_escape($B))!==false)$d[$B]=true;}}return$d;}function
where_check($W,$m=[]){parse_str($W,$Eb);remove_slashes([&$Eb]);return
where($Eb,$m);}function
validate_search_conditions($jc,array$Ah=[]){$H=[];foreach((is_array($jc)?$jc:[])as$w=>$ic){if(!is_array($ic))continue;$Qb=isset($ic["col"])?$ic["col"]:"";$nj=isset($ic["op"])?$ic["op"]:"";$W=isset($ic["val"])?$ic["val"]:"";if(!is_string($Qb)||!is_string($nj))continue;$Hg=is_array($W)&&in_array($Qb,array_map('strval',$Ah),true)&&array_filter($W,'is_string')===$W;if(is_string($W)||$Hg)$H[$w]=$ic;}return$H;}function
parse_search_list($_h){if(preg_match('~^\s*\((.*)\)\s*$~s',$_h,$_))$_h=$_[1];preg_match_all("~\\G\\s*+(?:'((?:''|[^'])*+)'\\s*+|([^,]*+))(?:,|\$)~s",$_h,$A,PREG_SET_ORDER);$H=[];foreach($A
as$_){if(!isset($_[2]))$H[]=str_replace("''","'",$_[1]);elseif(trim($_[2])!="")$H[]=trim($_[2]);}return$H;}function
where_link($r,$c,$X,$rj="="){return"&where%5B$r%5D%5Bcol%5D=".urlencode($c)."&where%5B$r%5D%5Bop%5D=".urlencode(($X!==null?$rj:"IS NULL"))."&where%5B$r%5D%5Bval%5D=".urlencode($X);}function
convert_fields(array$d,array$m,array$K=[]){$G="";foreach($d
as$w=>$W){if($K&&!in_array(idf_escape($w),$K))continue;$Ta=convert_field($m[$w]);if($Ta)$G
.=", $Ta AS ".idf_escape($w);}return$G;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),[";"=>"%3B",","=>"%2C"]);}function
cookie($B,$X,$sh=2592000){header("Set-Cookie: $B=".rawurlencode($X).($sh?"; expires=".gmdate("D, d M Y H:i:s",time()+$sh)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_url($xo,$tc){$http_response_header=null;$ae=[];set_error_handler(function($Yd,$k)use(&$ae){$ae[]=preg_replace('~^file_get_contents\([^)]*\):\s*~','',$k);return
true;});$H=file_get_contents($xo,false,$tc);restore_error_handler();$Ef=function_exists('http_get_last_response_headers')?http_get_last_response_headers():$http_response_header;return[$H,(array)$Ef,$H===false?implode("\n",$ae):"",];}function
get_settings($wc="neo_settings"){parse_str(isset($_COOKIE[$wc])?$_COOKIE[$wc]:"",$M);return$M;}function
get_setting($w,$wc="neo_settings"){$M=get_settings($wc);return
isset($M[$w])?$M[$w]:null;}function
save_settings(array$M,$wc="neo_settings"){cookie($wc,http_build_query($M+get_settings($wc)));}function
restart_session(){if(!ini_bool("session.use_cookies")&&session_status()==PHP_SESSION_NONE)session_start();}function
stop_session($Re=false){$Ao=ini_bool("session.use_cookies");if(!$Ao||$Re){session_write_close();if($Ao&&ini_set("session.use_cookies","0")===false)session_start();}}function&get_session($w){return$_SESSION[$w][DRIVER][SERVER][$_GET["username"]];}function
set_session($w,$W){$_SESSION[$w][DRIVER][SERVER][$_GET["username"]]=$W;}function
auth_url($Mo,$L,$U,$i=null){$wo=remove_from_uri(implode("|",array_keys(Drivers::getList()))."|username|ext|".($i!==null?"db|":"").($Mo=='mssql'||$Mo=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$wo,$_);return"$_[1]?".(sid()?session_name()."=".urlencode(session_id())."&":"").($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"").urlencode($Mo)."=".urlencode($L)."&"."username=".urlencode($U).($i!=""?"&db=".urlencode($i):"").($_[2]?"&$_[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($Hh,$ci=null){if($ci!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($Hh!==null?$Hh:$_SERVER["REQUEST_URI"]))][]=$ci;}if($Hh!==null){if($Hh=="")$Hh=".";header("Location: $Hh");exit;}}function
query_redirect($F,$Hh,$ci,$cl=true,$he=true,$te=false,$Jn=""){if($he){$Mm=microtime(true);$te=!Connection::get()->query($F);$Jn=format_time($Mm);}$Dm=$F?Admin::get()->formatMessageQuery($F,$Jn,$te):"";if($te){Admin::get()->addError(error().$Dm.script("initToggles();"));return
false;}if($cl)redirect($Hh,$ci.$Dm);return
true;}function
queries_redirect($Hh,$ci,$cl){$Qk=implode("\n",Queries::$queries);$Jn=format_time(Queries::$start);return
query_redirect($Qk,$Hh,$ci,$cl,false,!$cl,$Jn);}class
Queries{static$queries=[];static$start=0.0;}function
remember_query($F){if(!Queries::$start)Queries::$start=microtime(true);if(support("sql"))Queries::$queries[]=(preg_match('~;$~',$F)?"DELIMITER ;;\n$F;\nDELIMITER ":$F).";";else
Queries::$queries[]=$F;}function
queries($F){remember_query($F);return
support("sql")?Connection::get()->query($F):[];}function
apply_queries($F,array$R,$be='AdminNeo\table'){foreach($R
as$P){if(!queries("$F ".$be($P)))return
false;}return
true;}function
format_time($Mm){return
lang(106,max(0,microtime(true)-$Mm));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($Sj=""){return
substr(preg_replace("~(?<=[?&])($Sj".(sid()?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($w,$Rc=false,$Yc=""){$n=$_FILES[$w];if(!$n)return
null;foreach($n
as$w=>$W)$n[$w]=(array)$W;$H='';foreach($n["error"]as$w=>$k){if($k)return$k;$B=$n["name"][$w];$Qn=$n["tmp_name"][$w];$rc=file_get_contents($Rc&&preg_match('~\.gz$~',$B)?"compress.zlib://$Qn":$Qn);if($Rc){$Mm=substr($rc,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Mm))$rc=iconv("utf-16","utf-8",$rc);elseif($Mm=="\xEF\xBB\xBF")$rc=substr($rc,3);}if($Yc){if(!preg_match("~$Yc\\s*\$~",$rc))$rc
.=";";$rc
.="\n\n";}$H
.=$rc;}return$H;}function
upload_error($k){$Wh=($k==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($k?lang(107).($Wh?" ".lang(108,$Wh):""):lang(109));}function
repeat_pattern($kk,$x){return
str_repeat("$kk{0,65535}",$x/65535)."$kk{0,".($x%65535)."}";}function
is_utf8($W){return(preg_match('~~u',$W)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$W));}function
format_number($W){return
strtr(number_format($W,0,".",lang(110)),preg_split('~~u',lang(111),-1,PREG_SPLIT_NO_EMPTY));}function
format_rows(array$Q){$J=$Q["Rows"];$Pa=($J&&(DIALECT=="sqlite"||(isset($Q["Engine"])?$Q["Engine"]:"")==(DIALECT=="pgsql"?"table":"InnoDB")));return($Pa?"~ ":"").format_number($J);}function
friendly_url($W){return
preg_replace('~\W~i','-',$W);}function
table_status1($P,$ve=false){$H=table_status($P,$ve);return($H?reset($H):["Name"=>$P]);}function
column_foreign_keys($P){$H=[];foreach(Admin::get()->getForeignKeys($P)as$p){foreach($p["source"]as$W)$H[$W][]=$p;}return$H;}function
fields_from_edit(){$H=[];foreach((array)$_POST["field_keys"]as$w=>$W){if($W!=""){$W=bracket_escape($W);$_POST["function"][$W]=$_POST["field_funs"][$w];$_POST["fields"][$W]=$_POST["field_vals"][$w];}}foreach((array)$_POST["fields"]as$w=>$W){$B=bracket_escape($w,true);$H[$B]=["field"=>$B,"full_type"=>"varchar","type"=>"varchar","privileges"=>["insert"=>1,"update"=>1,"where"=>1,"order"=>1],"null"=>true,"auto_increment"=>($w==Driver::get()->primary),];}return$H;}function
dump_headers($Sf,$vi=false){$Sf=friendly_url($Sf).date("-Ymd-His");$pe=Admin::get()->sendDumpHeaders($Sf,$vi);if($pe===null)$pe=Admin::get()->sendDumpFormatHeaders($Sf,$vi);Admin::get()->sendDumpOutputHeaders($Sf,$pe);$Nj=$_POST["output"];if($Nj!="text")header("Content-Disposition: attachment; filename=$Sf.$pe".($Nj!="file"&&preg_match('~^[0-9a-z]+$~',$Nj)?".$Nj":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$pe;}function
dump_table_order(array$Bi,array$il){$bh=array_flip($Bi);$_j=[];$Vo=[];$Hc=false;$Uo=function($B)use(&$Uo,&$_j,&$Vo,&$Hc,$bh,$il){if(isset($_j[$B]))return;if(isset($Vo[$B])){$Hc=true;return;}$Vo[$B]=true;foreach(isset($il[$B])?$il[$B]:[]as$gl){if(isset($bh[$gl]))$Uo($gl);}unset($Vo[$B]);$_j[$B]=true;};foreach($Bi
as$B)$Uo($B);return($Hc?null:array_keys($_j));}function
dump_csv($I){$fo=$_POST["format"]=="tsv";foreach($I
as$w=>$W){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($fo?'\t':'[,;]|^$').'~',$W))$I[$w]='"'.str_replace('"','""',$W).'"';}echo
implode(($_POST["format"]=="csv"?",":($fo?"\t":";")),$I)."\r\n";}function
apply_sql_function($q,$c){return($q?($q=="unixepoch"?"DATETIME($c, '$q')":($q=="count distinct"?"COUNT(DISTINCT ":strtoupper("$q("))."$c)"):$c);}function
get_temp_dir(){$ik=ini_get("upload_tmp_dir");if(!$ik)$ik=sys_get_temp_dir();return$ik;}function
open_file_with_lock($o){if(is_link($o))return
null;$n=@fopen($o,"c+");if(!$n)return
null;@chmod($o,0660);if(!flock($n,LOCK_EX)){fclose($n);return
null;}return$n;}function
write_and_unlock_file($n,$g){rewind($n);fwrite($n,$g);ftruncate($n,strlen($g));unlock_file($n);}function
unlock_file($n){flock($n,LOCK_UN);fclose($n);}function
first(array$Sa){return
reset($Sa);}function
get_private_key($f){$o=get_temp_dir()."/adminneo.key";if(!$f&&!file_exists($o))return
false;$n=open_file_with_lock($o);if(!$n)return
false;$w=stream_get_contents($n);if(!$w){$w=Random::strongKey();write_and_unlock_file($n,$w);}else
unlock_file($n);return$w;}function
get_random_string(){return
Random::strongKey();}function
select_value($W,$z,$l,$Fn){if(is_array($W)){$H="";if(array_filter($W,'is_array')==array_values($W)){$Wg=[];foreach($W
as$V)$Wg+=array_fill_keys(array_keys($V),null);foreach(array_keys($Wg)as$Qg)$H
.="<th>".h($Qg);foreach($W
as$V){$H
.="<tr>";foreach(array_merge($Wg,$V)as$Go)$H
.="<td>".select_value($Go,$z,$l,$Fn);}}else{foreach($W
as$Qg=>$V)$H
.="<tr>".($W!=array_values($W)?"<th>".h($Qg):"")."<td>".select_value($V,$z,$l,$Fn);}return"<table>$H</table>";}$Jl="";if($l&&$W!==null&&($Fn===null||strlen($W)<=$Fn)&&($Y=Driver::get()->explodeArrayValue($W,$l["full_type"],$Jl))){$Il=$l;$Il["type"]=$Il["full_type"]=$Jl;$H=select_array_value($Y,$W,$z,$Il,$Fn);return
Driver::get()->implodeArrayValues($H,$l["full_type"]);}if(!$z)$z=Admin::get()->getFieldValueLink($W,$l);if($l)$W=Connection::get()->formatValue($W,$l);$H=$l?Admin::get()->formatFieldValue($W,$l):$W;if($H!==null){if(!is_utf8($H))$H="\0";elseif($Fn!=""&&is_shortable($l))$H=truncate_utf8($H,max(0,+$Fn));else$H=h($H);}return
Admin::get()->formatSelectionValue($H,$z,$l,$W);}function
select_array_value(array$Y,$W,$z,array$l,$Fn){$G=[];foreach($Y
as$X){if(is_array($X))$G[]=select_array_value($X,$W,$z,$l,$Fn);else{$ch=preg_replace('~(where%5B\d+%5D%5Bval%5D=)'.preg_quote(urlencode($W),"~")."~",'${1}'.urlencode($X),$z);$G[]=select_value($X,$ch,$l,$Fn);}}return$G;}function
is_blob(array$l){$jo=Driver::get()->getStructuredTypes();$T=lang(112);return
preg_match('~blob|bytea|raw|file'.(DIALECT=="mssql"?'|binary|image':'').'~',$l["type"])&&!in_array($l["type"],isset($jo[$T])?$jo[$T]:[]);}function
is_generated_always(array$l){return(isset($l["generated"])?$l["generated"]:"")!=""||stripos((string)(isset($l["default"])?$l["default"]:""),"GENERATED ALWAYS AS ")===0;}function
parenthesize_expression($ne){$Qm=preg_replace('~\'(?:[^\']|\'\')*\'|"(?:[^"]|"")*"|`(?:[^`]|``)*`|\[[^]]*]~',"",$ne);if(!preg_match('~^\(.*\)$~s',$Qm))return"($ne)";$ad=0;$ih=strlen($Qm)-1;for($r=0;$r<$ih;$r++){if($Qm[$r]=="(")$ad++;elseif($Qm[$r]==")"&&--$ad==0)return"($ne)";}return$ne;}function
is_mail($X){return
is_string($X)&&filter_var($X,FILTER_VALIDATE_EMAIL);}function
is_web_url($X){if(!is_string($X)||!preg_match('~^(https?:)?//~i',$X))return
false;$gc=parse_url($X);if(!$gc)return
false;$xo=$X;if(isset($gc['path'])){$Rd=array_map('urlencode',explode('/',$gc['path']));$xo=str_replace($gc['path'],implode('/',$Rd),$xo);}if(isset($gc['query'])){parse_str($gc['query'],$Tj);$xo=str_replace($gc['query'],http_build_query($Tj),$xo);}if(!isset($gc['scheme']))$xo="https:$xo";return(bool)filter_var($xo,FILTER_VALIDATE_URL);}function
is_shortable($l){return$l&&!preg_match('~'.number_type().'|date|time|year~',$l["type"]);}function
host_port($L){return(preg_match('~^(:([^:].*)|(\[(.+)]|(([^:]+://)?[^:]+))(:(\d+))?)$~',$L,$_)?[(isset($_[4])?$_[4]:"").(isset($_[5])?$_[5]:""),$_[2].(isset($_[8])?$_[8]:"")]:[$L,'']);}function
is_host_valid($Nf){if(preg_match('~^\[(.+)]$~D',$Nf,$_))return(bool)filter_var($_[1],FILTER_VALIDATE_IP,FILTER_FLAG_IPV6);return(bool)preg_match('~^[-\w.]+$~Du',$Nf);}function
count_rows($P,$Z,$Fg,$qf){$F=" FROM ".table($P).($Z?" WHERE ".implode(" AND ",$Z):"");return($Fg&&(DIALECT=="sql"||count($qf)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$qf).")$F":"SELECT COUNT(*)".($Fg?" FROM (SELECT 1$F GROUP BY ".implode(", ",$qf).") x":$F));}function
slow_query($F){$i=Admin::get()->getDatabase();$Kn=Admin::get()->getQueryTimeout();$wm=Driver::get()->slowQuery($F,$Kn);$e=null;if(!$wm&&support("kill")){$e=connect();if($e&&($i==""||$e->selectDatabase($i))){$Yg=number($e->getValue(connection_id()));echo'<script',nonce(),'>
	const timeout = setTimeout(() => {
		ajax(\'',js_escape(ME),'script=kill\', function() {
		}, \'kill=',$Yg,'&token=',get_token(),'\');
	}, ',1000*$Kn,');
</script>
';}}ob_flush();flush();$H=@get_key_vals(($wm?:$F),$e,false);if($e){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$H;}function
get_token(){$Wk=rand(1,1e6);return($Wk^$_SESSION["token"]).":$Wk";}function
verify_token(){list($Rn,$Wk)=explode(":",$_POST["token"]);return($Wk^$_SESSION["token"])==$Rn&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],["","same-origin"]);}function
is_same_origin(){if(isset($_SERVER["HTTP_SEC_FETCH_SITE"]))return$_SERVER["HTTP_SEC_FETCH_SITE"]=="same-origin";return
isset($_SERVER["HTTP_REFERER"])&&is_same_host($_SERVER["HTTP_REFERER"]);}function
is_same_host($xo){return
isset($_SERVER["HTTP_HOST"])&&preg_match('~^https?://([^/?#]*)~i',$xo,$_)&&!strcasecmp($_[1],$_SERVER["HTTP_HOST"]);}function
is_authenticated($Ya=false){static$G=false;if($Ya)$G=true;return$G;}function
script($_m,$Un="\n"){return"<script".nonce().">$_m</script>$Un";}function
script_src($xo,$Vc=false){return"<script src='".h($xo)."'".nonce().($Vc?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
input_hidden($B,$X=""){return"<input type='hidden' name='".h($B)."' value='".h($X)."'>";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($O){if($O===null||$O==="")return"";return
str_replace(["&","<","\"","'","\0"],["&amp;","&lt;","&quot;","&#039;","&#0;"],$O);}function
truncate_utf8($O,$x=80){if($O=="")return"";if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$x).")($)?)u",$O,$_))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$x).")($)?)",$O,$_);return
h($_[1]).(isset($_[2])?"":"<i>…</i>");}function
icon_solo($s){return
icon($s,"solo");}function
icon_chevron_down(){return
icon("chevron-down","chevron");}function
icon_chevron_right(){return
icon("chevron-down","chevron-right");}function
icon($s,$Nb=null){$s=h($s);return"<svg class='icon ic-$s $Nb'><use href='".h(link_files("icons.svg",[]))."#$s'/></svg>";}function
checkbox($B,$X,$Ib,$dh="",$mj="",$Nb="",$fh=""){$H="<input type='checkbox' name='".h($B)."' value='".h($X)."'".($Ib?" checked":"").($fh?" aria-labelledby='$fh'":"").">".($mj?script("qsl('input').onclick = function () { $mj };",""):"");return($dh!=""||$Nb?"<label".($Nb?" class='$Nb'":"").">$H".h($dh)."</label>":$H);}function
optionlist($C,$Ul=null,$Bo=false){$H="";foreach($C
as$Qg=>$V){$wj=[$Qg=>$V];if(is_array($V)){$H
.='<optgroup label="'.h($Qg).'">';$wj=$V;}foreach($wj
as$w=>$W)$H
.='<option'.($Bo||is_string($w)?' value="'.h($w).'"':'').($Ul!==null&&($Bo||is_string($w)?(string)$w:$W)===$Ul?' selected':'').'>'.h($W);if(is_array($V))$H
.='</optgroup>';}return$H;}function
html_select($B,$C,$X="",$lj="",$fh="",$Bo=false){static$dh=0;$eh="";if(!$fh&&substr(isset($C[""])?$C[""]:"",0,1)=="("){$dh++;$fh="label-$dh";$eh="<option value='' id='$fh'>".h($C[""]);unset($C[""]);}return"<select name='".h($B)."'".($fh?" aria-labelledby='$fh'":"").">".$eh.optionlist($C,$X,$Bo)."</select>".($lj?script("qsl('select').onchange = function () { $lj };",""):"");}function
html_radios($B,$C,$X=""){$G="<span class='labels'>";foreach($C
as$w=>$W)$G
.="<label><input type='radio' name='".h($B)."' value='".h($w)."'".($w==$X?" checked":"").">".h($W)."</label>";$G
.="</span>";return$G;}function
confirm($ci="",$Wl="qsl('input')"){return
script("$Wl.onclick = () => confirm('".js_escape($ci?:lang(113))."');","");}function
print_fieldset_start($s,$oh,$Rf,$So=false,$ym=false){echo"<fieldset id='fieldset-$s' class='closable ".(!$So?" closed":"")."'>","<legend><a href='#'>$oh</a></legend>",icon($Rf,"fieldset-icon jsonly"),"<div class='fieldset-content".($ym?" sortable":"")."'>";}function
print_fieldset_end($s,$ym=false){echo"</div>",script("initFieldset('$s');","");if($ym)echo
script("initSortable('#fieldset-$s .fieldset-content');","");echo"</fieldset>\n";}function
bold($ob,$Nb=""){return($ob?" class='$Nb active'":($Nb?" class='$Nb'":""));}function
js_escape($O){return
str_replace("<","\\x3C",addcslashes($O,"\r\n'\\"));}function
js_escape_key($O){return'"'.str_replace("<","\\x3C",addcslashes($O,"\r\n\t\"\\")).'"';}function
js_escape_re($O){return
addcslashes(preg_quote($O,"/"),"\r\n");}function
pagination($D,$Ec){return"<li>".($D==$Ec?"<strong>".($D+1)."</strong>":'<a href="'.h(remove_from_uri("page").($D?"&page=$D".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($D+1)."</a>")."</li>";}function
print_hidden_fields(array$Mk,array$Uf=[],$Bk=""){$G=false;foreach($Mk
as$w=>$W){if(!in_array($w,$Uf)){if(is_array($W))print_hidden_fields($W,[],$w);else{$G=true;echo
input_hidden($Bk?$Bk."[$w]":$w,$W);}}}return$G;}function
hidden_fields_get(){if(sid())echo
input_hidden(session_name(),session_id());if($_GET["ext"])echo
input_hidden("ext",$_GET["ext"]);if(SERVER!==null)echo
input_hidden(DRIVER,SERVER);echo
input_hidden("username",$_GET["username"]);}function
enum_input($Wa,array$l,$X,$Od=null,$Hb=false){preg_match_all("~'((?:[^']|'')*)'~",$l["length"],$A);$Y=$A[1];$In=Admin::get()->getSettings()->getEnumAsSelectThreshold();$K=!$Hb&&$In!==null&&count($Y)>$In;$T=$Hb?"checkbox":"radio";$za=$K?"selected":"checked";$G=$K?"<select $Wa>":"<span class='labels'>";if($K&&$l["null"]&&$Od!==""){$Ib=$X===null?$za:"";$G
.="<option value='__adminneo_empty__' disabled $Ib></option>";}if($Od!==null){$Ib=(is_array($X)?in_array($Od,$X):$X===$Od)?$za:"";if($K)$G
.="<option value='$Od' $Ib>".lang(114)."</option>";else$G
.="<label><input type='$T' $Wa value='$Od' $Ib><i>".lang(114)."</i></label>";}foreach($Y
as$W){if($Od===""&&$W==="")continue;$W=stripcslashes(str_replace("''","'",$W));$Ib=is_array($X)?in_array($W,$X):$X===$W;$Ib=$Ib?$za:"";$Ze=$W===""?("<i>".lang(114)."</i>"):h(Admin::get()->formatFieldValue($W,$l));if($K)$G
.="<option value='".h($W)."' $Ib>$Ze</option>";else$G
.=" <label><input type='$T' $Wa value='".h($W)."' $Ib>$Ze</label>";}$G
.=$K?"</select>":"</span>";return$G;}function
input($l,$X,$q,$cb=false){$B=h(bracket_escape($l["field"]));$jo=Driver::get()->getTypes();$Gg=isset($l["full_type"])&&Admin::get()->detectJson($l["full_type"],$X,true);$ol=(DIALECT=="mssql"&&$l["auto_increment"]&&!$_POST["clone"]);if($ol&&!$_POST["save"])$q=null;$ho=Driver::get()->getUserTypeId(isset($l["type"])?$l["type"]:"");if($ho){$Xd=type_values($ho);if($Xd){$l["type"]="enum";$l["length"]=$Xd;}}$Wa=" name='fields[$B]' ".($cb?" autofocus":"");$if=(isset($_GET["select"])||$ol?["orig"=>lang(115)]:[])+Admin::get()->getFieldFunctions($l);$_f=(in_array($q,$if)||isset($if[$q]));echo"<td class='function'>",Driver::get()->getUnconvertFunction($l)." ";if(count($if)>1){$Ul=$q===null||$_f?$q:"";echo"<select name='function[$B]'>".optionlist($if,$Ul)."</select>",help_script_command("value.replace(/^SQL\$/, '')",true),script("qsl('select').onchange = functionChange;","");}else
echo
h(reset($if));echo"</td><td>";$kg=Admin::get()->getFieldInput(isset($_GET["edit"])?$_GET["edit"]:null,$l,$Wa,$X,$q);if($kg!="")echo$kg;elseif(preg_match('~bool~',$l["type"]))echo"<input type='hidden'$Wa value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?" checked":"")."$Wa value='1'>";elseif($l["type"]=="enum")echo
enum_input($Wa,$l,$X);elseif($l["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$l["length"],$A);echo"<span class='labels'>";foreach($A[1]as$W){$W=stripcslashes(str_replace("''","'",$W));$Ib=$X!==null&&in_array($W,explode(",",$X),true);$Ib=$Ib?"checked":"";$Ze=$W===""?("<i>".lang(114)."</i>"):h(Admin::get()->formatFieldValue($W,$l));echo" <label><input type='checkbox' name='fields[$B][]' value='".h($W)."' $Ib>$Ze</label>";}echo"</span>";}elseif(is_blob($l)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$B'>";elseif($Gg)echo"<textarea $Wa cols='50' rows='12' class='jush-json'>".h($X).'</textarea>';elseif(($Cn=preg_match('~text|lob|memo|json~i',$l["type"])||(preg_match('~char|binary~',$l["type"])&&$l["length"]=="max"))||preg_match("~\n~",$X)){if($Cn&&DIALECT!="sqlite")$Wa
.=" cols='50' rows='12'";else{$J=min(12,substr_count($X,"\n")+1);$Wa
.=" cols='30' rows='$J'";}echo"<textarea $Wa>".h($X).'</textarea>';}else{$Yh=!preg_match('~int~',$l["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$l["length"],$_)?((preg_match("~binary~",$l["type"])?2:1)*$_[1]+($_[3]?1:0)+($_[2]&&!$l["unsigned"]?1:0)):($jo&&$jo[$l["type"]]?$jo[$l["type"]]+($l["unsigned"]?0:1):0);if(DIALECT=='sql'&&Connection::get()->isMinVersion("5.6")&&preg_match('~time~',$l["type"]))$Yh+=7;echo"<input class='input'".((!$_f||$q==="")&&preg_match('~^'.int_type().'$~',$l["type"])&&!preg_match('~\[]~',$l["full_type"])?" type='number'":"").($q!="now"?" value='".h($X)."'":" data-last-value='".h($X)."'").($Yh?" data-maxlength='$Yh'":"").(preg_match('~char|binary~',$l["type"])&&$Yh>20?" size='44'":"")."$Wa>";}$If=Admin::get()->getFieldInputHint($_GET["edit"],$l,$X);if($If!="")echo" <span class='input-hint'>$If</span>";if(count($if)>1)echo
script("qs('select', qsl('td').previousSibling).onchange(null, true);","");$Ke=0;foreach($if
as$w=>$W){if($w===""||!$W)break;$Ke++;}if(count($if)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $Ke);");}function
process_input($l){if(is_generated_always($l))return
null;$t=bracket_escape($l["field"]);$q=isset($_POST["function"][$t])?$_POST["function"][$t]:"";if($q=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?idf_escape($l["field"]):false);if($q=="NULL")return
Driver::get()->getNull();if(is_blob($l)&&ini_bool("file_uploads")){$n=get_file("fields-$t");if(!is_string($n))return
false;return
Driver::get()->quoteBinary($n);}$X=isset($_POST["fields"][$t])?$_POST["fields"][$t]:(isset($_FILES["fields"]["name"][$t])?$_FILES["fields"]["name"][$t]:null);if($X===null)return
false;if($l["auto_increment"]&&$X=="")return
null;if($l["type"]=="set")$X=implode(",",(array)$X);if($q=="json"){$X=json_decode($X,true);if(!is_array($X))return
false;return$X;}if($q!="SQL"&&isset($l["full_type"]))Admin::get()->detectJson($l["full_type"],$X,false);return
Admin::get()->processFieldInput($l,$X,$q);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$tl=$ae=[];foreach(table_status("",true)as$P=>$Q){$mn=Admin::get()->getTableName($Q);if(!isset($Q["Engine"])||$mn==""||($_POST["tables"]&&!in_array($P,$_POST["tables"])))continue;$G=Connection::get()->query("SELECT".limit("1 FROM ".table($P)," WHERE ".implode(" AND ",Admin::get()->processSelectionSearch(fields($P),[])),1));if($G&&!$G->fetchRow())continue;$z=h(ME."select=".urlencode($P)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]));if($G)$tl[]="<li><a href='$z'>".icon("search")."$mn</a></li>";else$ae[]="<div class='error'><a href='$z'>$mn</a>: ".error()."</div>";}if($tl)echo"<ul class='links'>\n",implode("\n",$tl),"</ul>\n";if($ae)echo
implode("\n",$ae),"\n";if(!$tl&&!$ae)echo"<p class='message'>".lang(79)."</p>\n";}function
help_script($Cn,$tm=false){return
script("initHelpFor(qsl('select, input'), '".js_escape($Cn)."', $tm);","");}function
help_script_command($ac,$tm=false){return
script("initHelpFor(qsl('select, input'), (value) => { return $ac; }, $tm);","");}function
edit_form($P,$m,$I,$vo){$mn=Admin::get()->getTableName(table_status1($P,true));$S=$vo?lang(39):lang(116);page_header("$S: $mn",["select"=>[$P,$mn],$S]);if($I===false){echo"<p class='error'>".lang(92)."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$Jd=false;$ep=($vo&&!isset($_GET["select"])?where_columns($_GET,$m):[]);$uc=(count($ep)!=count($m));if(!$uc)$ep=[];if(!$m)echo"<p class='error'>".lang(117)."\n";else{echo"<table class='box'>".script("qsl('table').onkeydown = onEditingKeydown;");$cb=!$_POST;foreach($m
as$B=>$l){echo"<tr".(isset($ep[$B])?" class='where-column'":"")."><th>".Admin::get()->getFieldName($l);$w=bracket_escape($B);$j=isset($_GET["preset"][$w])?$_GET["preset"][$w]:null;if($j===null){$j=$l["default"];if($l["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$j,$kl))$j=$kl[1];if(DIALECT=="sql"&&preg_match('~binary~',$l["type"]))$j=bin2hex($j);}$X=($I!==null?($I[$B]!=""&&DIALECT=="sql"&&preg_match("~enum|set~",$l["type"])&&is_array($I[$B])?implode(",",$I[$B]):(is_bool($I[$B])?+$I[$B]:$I[$B])):(!$vo&&$l["auto_increment"]?"":(isset($_GET["select"])?false:$j)));if(!$_POST["save"]&&is_string($X))$X=Admin::get()->formatFieldValue($X,$l);if(($vo&&!isset($l["privileges"]["update"]))||is_generated_always($l)){echo"<td class='function'></td><td>";if($vo||!$l["generated"])echo
select_value($X,'',$l,null);else
echo"<code class='jush-".DIALECT."'>",h($X),"</code>";echo"</td>";}else{$Jd=true;$q=($_POST["save"]?isset($_POST["function"][$w])?$_POST["function"][$w]:"":($vo&&preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?"now":($X===false?null:($X!==null?'':'NULL'))));if(!$_POST&&!$vo&&$X==$l["default"]&&preg_match('~^[\w.]+\(~',$X))$q="SQL";if(preg_match("~time~",$l["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$X)){$X="";$q="now";}if($l["type"]=="uuid"&&$X=="uuid()"){$X="";$q="uuid";}if($cb!==false)$cb=($l["auto_increment"]||$q=="now"||$q=="uuid"?null:true);input($l,$X,$q,(bool)$cb);if($cb)$cb=false;}echo"\n";}if(!support("table")&&!fields($P))echo"<tr>"."<th><input class='input' name='field_keys[]'>".script("qsl('input').oninput = fieldChange;","")."<td class='function'>".html_select("field_funs[]",Admin::get()->getFieldFunctions(["null"=>isset($_GET["select"])]))."<td><input class='input' name='field_vals[]'>"."\n";echo"</table>\n",script("initToggles(gid('form'));");if($ep)echo
script("initWhereChange();");}echo"<p>";if($Jd){echo"<input type='submit' class='button default' value='".lang(118)."'>\n";if(!isset($_GET["select"])&&$uc){$jd=($ep&&Admin::get()->getErrors()?" disabled":"");echo"<input type='submit' class='button' name='insert' value='".($vo?lang(119):lang(120))."' title='Ctrl+Shift+Enter'$jd>\n",($vo?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".js_escape(lang(121))."', this); };"):"");}}echo($vo?"<input type='submit' class='button' name='delete' value='".lang(122)."'>".confirm()."\n":"");if(isset($_GET["select"]))print_hidden_fields(["check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]]);echo
input_hidden("referer",isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),input_hidden("save","1"),input_token(),"</form>\n";}function
file_upload_form_script($Ve,$lg){$Qh=ini_get("max_file_uploads");$Wh=ini_get("upload_max_filesize");$Xh=ini_bytes("upload_max_filesize");return
script("initFilesUploadForm('".js_escape($Ve)."', '".js_escape($lg)."', "."$Qh, '".js_escape(lang(123,$Qh,"'max_file_uploads'"))."', "."$Xh, '".js_escape(lang(124,$Wh,"'upload_max_filesize'"))."')");}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($O,$ed=""){$Ma=array_flip(str_split(compress_alphabet()));$x=strlen($O);$Jo=($x?13*($x-1)/2-$Ma[$O[0]]:0);$kb="";$rl=0;$sl=0;for($r=1;$r<$x;$r+=2){$rl=($rl<<13)+$Ma[$O[$r]]*93+$Ma[$O[$r+1]];$sl+=13;while($sl>=8&&$Jo>=8){$sl-=8;$Jo-=8;$kb
.=chr($rl>>$sl);$rl&=(1<<$sl)-1;}}if($kb=="")return"";if($ed!=""&&function_exists('inflate_init'))return
inflate_add(inflate_init(ZLIB_ENCODING_RAW,['dictionary'=>$ed]),$kb,ZLIB_FINISH);return$ed==""&&function_exists('gzinflate')?gzinflate($kb):inflate($kb,$ed);}function
inflate($kb,$ed=""){$ph=[3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258];$qh=[0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0];$md=[1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577];$od=[0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13];$H=$ed;$wk=0;do{$Ie=inflate_bits($kb,$wk,1);$T=inflate_bits($kb,$wk,2);if(!$T){$wk=($wk+7)&~7;$x=inflate_bits($kb,$wk,16);$wk+=16;$H
.=substr($kb,$wk>>3,$x);$wk+=$x<<3;}else{if($T==1){$Ch=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$pd=array_fill(0,30,5);}else{$Bh=inflate_bits($kb,$wk,5)+257;$nd=inflate_bits($kb,$wk,5)+1;$xj=[16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15];$ii=array_fill(0,19,0);$hi=inflate_bits($kb,$wk,4)+4;for($r=0;$r<$hi;$r++)$ii[$xj[$r]]=inflate_bits($kb,$wk,3);$ji=inflate_table($ii);$rh=[];while(count($rh)<$Bh+$nd){$cn=inflate_symbol($kb,$wk,$ji);if($cn==16)$rh=array_merge($rh,array_fill(0,inflate_bits($kb,$wk,2)+3,end($rh)));elseif($cn==17)$rh=array_merge($rh,array_fill(0,inflate_bits($kb,$wk,3)+3,0));elseif($cn==18)$rh=array_merge($rh,array_fill(0,inflate_bits($kb,$wk,7)+11,0));else$rh[]=$cn;}$Ch=array_slice($rh,0,$Bh);$pd=array_slice($rh,$Bh);}$Dh=inflate_table($Ch);$rd=inflate_table($pd);while(($cn=inflate_symbol($kb,$wk,$Dh))!=256){if($cn<256)$H
.=chr($cn);else{$x=$ph[$cn-257]+inflate_bits($kb,$wk,$qh[$cn-257]);$qd=inflate_symbol($kb,$wk,$rd);$Yi=strlen($H)-$md[$qd]-inflate_bits($kb,$wk,$od[$qd]);for($r=0;$r<$x;$r++)$H
.=$H[$Yi+$r];}}}}while(!$Ie);return$ed==""?$H:substr($H,strlen($ed));}function
inflate_bits($kb,&$wk,$_c){$H=0;for($r=0;$r<$_c;$r++){$H+=((ord($kb[$wk>>3])>>($wk&7))&1)<<$r;$wk++;}return$H;}function
inflate_table(array$rh){$P=[];$Pb=0;for($lb=1;$lb<=max($rh);$lb++){foreach($rh
as$cn=>$x){if($x==$lb){$P[$lb][$Pb]=$cn;$Pb++;}}$Pb<<=1;}return$P;}function
inflate_symbol($kb,&$wk,array$P){$Pb=0;$lb=0;do{$Pb=($Pb<<1)+inflate_bits($kb,$wk,1);$lb++;}while(!isset($P[$lb][$Pb]));return$P[$lb][$Pb];}if(isset($_GET["file"]))load_compiled_file($_GET["file"]);function
load_compiled_file($o){if($o==""){http_response_code(404);exit;}if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){http_response_code(304);exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");ini_set("zlib.output_compression","1");$pe=pathinfo($o,PATHINFO_EXTENSION);switch($pe){case"css":header("Content-Type: text/css; charset=utf-8");break;case"js":header("Content-Type: text/javascript; charset=utf-8");break;case"ico":header("Content-Type: image/x-icon");break;case"png":header("Content-Type: image/png");break;case"svg":header("Content-Type: image/svg+xml");break;}switch($o){case'favicon-blue-0f5ce53a66b1e25395d0048da369f19e__f1f297b4.ico':$g='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGZowEUFhM2G6A4QAJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+cUn3+6qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9jompoYUe3hg9tFCL+dNX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryEWPJCE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gDMBOIUjRp9w0AAAAASUVORK5CYII=';break;case'favicon-green-def78cfa7c465c8b0e9966e3eb87407d__f1f297b4.ico':$g='AAABAAEAICAAAAEAIAC+AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYVJREFUeNpiYOhiAFBfBxBohGEcxs/RmgQQEQDbQAACBAFCmBDgogLCZkN0BwiATBZQZNsAgmwAgyMAuRZCAtvkGLTy7sEHcFbvfWT4AbjH8f75Huq/CXBRgY8lQuzx29gjxBI+KnBtBDxFgJ+QO/1AgKw24AW+Q1La4rkm4DPEkk+agCvEkqsmQKxSBGwhlkSagA7Eko72DNs4QZRO8NIOUQk+1pAbreGjlGaI3ibENNDFEO+MIbpoJHz0jfYKRngCRymLEUQXABzQRxHOjYro4wBJGwAAEaYYoIeXRg8DTBHZ2oHo0TvwGmLJK01ADnNISnPkFAEA2jhC7nSEl2YHmnAMF1VMsEEMAQDE2GCCKlw4RlMT8Ad1OAnyeGbk4SSo46I9wzPGKMC5UwFjnLVneIEYMWZo/SOmgBZmiCGA7g98hSSIscM3Y4cYkuCLJsCDWOJpAjL4CEnpAzLaHXAxhSi9h2vjZVTDCnKjFWqw/jYsI8ACIX4ZIRYIUMbfEW3maO8YAIxSqCXQN5/tAAAAAElFTkSuQmCC';break;case'favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__f1f297b4.ico':$g='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRATANhCAAEGAEGbIwEUFhM2G6A4QAJksoMi2AQTZAAZHAEsthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9fO1dVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwAv8gqS0xXNNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwMddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyBV7BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4FsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8CO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09ALYCTFLvUfsXAAAAAElFTkSuQmCC';break;case'favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__f1f297b4.ico':$g='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGYIcFEBYbMhugM0AJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+e8Ln66qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9iompoYUe3hg9tFCL+dOX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryAWPJcE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gD0xZAYUYkFLAAAAAASUVORK5CYII=';break;case'favicon-red-c2ebb34a8df5aba28e15d87728a151df__f1f297b4.ico':$g='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRAQY20AAAgQBQpghwEUFhM2G6A4QAJksoMi2AQTZBhgcAUgthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9eOZdVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwEv8gqS0xQtNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwKddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyB17BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4HsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8DO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09AE8YAEN5XivhAAAAAElFTkSuQmCC';break;case'favicon-blue-17e440832c1eac07527560a0d6f0d2ee__f1f297b4.svg':$g='+<bATb3V?$so%el,wEIwK&mlYjGZ$a8-HGs8y$j)-UBmQx`Cf?>]C6?xmhS1<w
ZNSJl63"aZ]nB<rgk|tG)vC,pHYx;Wb2NVXBMxd!lQ=g0"!mM[]c*SW?7
E^]B7t[fHolNczfsymCW
CM~8Ult[(brlOx`71nDc
H;N~ybgNd*l6LMbRk;>%06rQeBnDc14r_7Z0b9uqO=xV5=22d05hr#V]F`V>ZD,#JA9[$XEV-*TBfz7Z%
rEJw!G!cT+[7>-u:iVU)N$iv%ySN.`._&}sV@FUIuH)%cY-0#OXWPT8t./3Oid8~R]3?9n;/Qs
Y2!K`1Q9d
tys6C=xmJfXFCT%0xl`H&%njK7`N[.q6jET6kV
VqmiJoIrZ#rsP~q0vDN<FH1w9l4R-?A#H:#onn0@0]3dNk3,E{<7r;2q
u)F!d(nhskXC~JT4N!~!g52r#`3hF[%j
oPE~ZV(W_~g#t?WXQCxEe7)ZQKGxei4.gu_R>pAL]HDZf!uSL)$_)^vZ:Xk![_HKh=C|S~PjqHcgjUutq#-~?PmA#<MYyg2R';break;case'favicon-green-bb254c95a033f67e3d433a3df63e160d__f1f297b4.svg':$g='&<bATb3V?$so%eo_t_hLePj9EjS(Ic^3}i[Os!U@i%X`9w9h7
[@
I:KPV|A4v9o?tG^4G8Kl/Za|j!Q;%pO#+U]tOQ@f!m@5m(iU+Xd
:mOOX[.7/V%^=Dj0`KWYnDy%^,cfeZsrK*5P&I[0
$m~<+JorKkYLM7yy$Aw$[w/57.-wX^0JIMH;`
EW/c}a9c

eAhG[68u92FB)
ToD/k0uYFG=PN>>d,^BJ&icL,/i,2N?:udHuPgxk>l07BF^82,>cspK:`^P,q[%?5=4<`h.?.kJ"X_a_mu|Z>-SL,/k)gDk#g):;if"T:5u9
?>JfFYF#KP)D%8xDljc2l^mJ<^%4+Iu=v?FB.Y^67X>9:@KMfCyqQL.-Kmn#DsAler%=^*ps8vI)CEG%8
bknN$znn
D-t3DO&3,C5<7r@2q],)F!d(nhsf)H-JU7?!^"9+Xtpm.q8>NCVHgU$kcVodiJa6[qLov!)IR#}x3*ku*P%3jGe?w?dILBVvhJ~,bFMpBcD%CK/#{0d-|Mu[Pn{fe.3aFbOf6.%,.opHOIuO9';break;case'favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__f1f297b4.svg':$g='&<bATb3V?$so%el,wEIwL!M$a_R(Ic^2cYP0
-+_=VtkTnq5G
]9Lh@D6K6GH(sVGneM;s+raV;M^D!*+KY*IOa=3Z`PmB;9_2Obq7yBg2Z%>bw,v+Y%P7|fhq*F156s0beWd,6`ay=p@_gN3Z=jciDmX_i72]oyww{Z~&Ut4M]*axgl)b!a1ju/uH<*9Xo
=
Ul9*$m_7{*MB>d@6aDhpC0Rt_1D-,/u//bLNxN!Dc[m?bh@-`9rmyGW2b(F%J[Qafv1UZr>a>F6JA8g[!0b@[fJtmTv4e%TYc-0#j"5iK2z=wl
*E@MT<:>g<H|W|2(rwB
g5j1,D7hGqj!`uuvXM9_hJa7+NN~VgB]NK[T3a!z#1A}OTmcvh["nTvNeTw$$;eT6q?q1?CDM!=<1SnN]QTt9lj&eNk(4c>}6hisx;^`kO(cefsRGicdC|"j9npLdO:cM^+&QT[{IL-Wu]3@7yXoO46wh/&9Sd+Vo~(7pzhcG()N3^n#?6MAX=Pd
]uX2I0XtRaah;-u5eKzydbLJw$UJhJ]
fe:Fe6mt>cu';break;case'favicon-purple-4cfd57d31ab991e8071fe34060cd3123__f1f297b4.svg':$g='+<bAU6+V?$so%eoa6[DcHOAP_Cx(^^F;2nJOs!U09RM?Tw1h7?fDMh@D6K6GH(sW:c#bW`9DTcKpCf,1a1E%kD+5q+F]4B,8UB|ML]o,4O$F:-Sutmd.1l:6ly,?7MjE>rM=td#VYm/o}bPN3Z=AH2GFCrK4TIrcvu65o!8H3d"9V^}B7s3mnUM&Wx87ya:7X
mAh1Y7Wu72FB8FRoD/k0uYFG=PV;h%?KNUP%#GQ^
2w"
SkNgvI0*kTGqx))i0|m-597vz$R_y_,e<pwj=Y`"7[1E)$2(fp:Ex.$(:Op<o1CeTD_yBV
#FT:r
y-y?.@i`K.+Yg?j_Hk`_>f$l~LS;5RSh?>{b"
&+evz;:0eol+,pv/]hQo|q*A;p{F69^H{
F#X:3c%4:=Hdpu{k&lMXV@E/#GL#?#)e-WHQiGP@.%eYsSRXBg!n9tO*z&WP*$Up~J1]E18NCC{!iER:u`UBQ5(*#viS-0S(llO)OsT0bALBjGk4Bt}3Hwkvp!81Fs-^5[}2H/0[(gXX++<t3ZZ577
3fS]`cB;^5uWM3tX';break;case'favicon-red-a006e401273230fd6be80568c8361b57__f1f297b4.svg':$g='+<bAU6+V?$so%eoa6[DcEe<SKeo.[BnWu^_0
-+j@96@+X_5GA4^m3%R;yn_USCF5vXi6B6jvyvvy?qZYfND@5~KR9wPw1q,+w{:cwGa2aY)<GPqWy/nLYzy>c3Au_/MA7,dc
}`rf-`x<FH$&bI]FGspJPra.)yE]$w~aKaM]on_y4%i2=`y?0`vYw6}rUy&B3JD1F
/B
o8ro30<1Tp4r,.qWnFpndQQq#ek`C+.f19/9#q+uNg4bc6H.9(02NJtu){yYINu`Uzs:%?o1GH"Lgtxvhu>9uq8)/:th8&TH,OT*]5<Ydlap!w8k>zUUDvh@h+)F+>T8=R`(;8*p2Il^<$eSAzo6lU8L_P_Yh-i#lD(4lV7"52"_UdLUTuCV+Yf/[^)f(~i
.,!Hkau}dsr
i84
>s)_:!#hJQ9:ex&|].#nB#/g7`Ds1xz$U+"f!p-.*YXigq8vUBF!9lVoojveL
+8ox,X;hOaXS*cTW+ODnn.]r,!3BBNvdJ~,brQumcAQJa9E)x*rt!$x~u(xCb
69OF`xI}1->oD?M:yg2R';break;case'apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__f1f297b4.png':$g='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAK7UlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUa502RxeG0bX17btm3b/tu2bdu2PbZt21bY+6xqPduTSSddSd1zPyTdVZk+p5+prr51f7d83ixVOXXH55Yte6No2r25Qy/O/Pg7OB/4ykFO0cC/4FBma6qq2Tsmb+SVGe9/7f86zWhMFx+HQ5mjs7XmwITMT77LXe+R04WOdFdw+Ka1xOzL7vML7rTLTnd+RMHhW+Z01Owfz911izOE8IMKDl8wh6W9dPHLbsFCOD/Izyo4pB8z3EyG4GPJK5rTqeCQ2HgEGECGeL5MVHBIPAM1CAvh/AkFh5Rvrf/13STr0x/UHpmO4yXzn+7+3pev+VA0puN/fX/hDyk4fOSBkjPg96KN09KRN/KqbuBoSzsnGtNRz8NFwSFBDJRAeDdwCOsqS8/86Nu9gYP4mK25WsGBaXVNlpS8plORFeuP5k/ZkPbVnLj3J0e9Nib8uWEhj/QPvPvz8zd/eAa/54vzfOUgp2hAs6kb0uhCR7rXN1s0I42AN7dNDxxYY/CG3sCB1+wb66dwNLVaQ5Jqlu/L/XxWLPf+hvdOu8X5qS9mxS7fnxuaVNvcZtXcaiyI6IeDN9KyFW/3Bg6eTf4FR1p+0/ydWS+ODL3xfe6lsc6feGlk6IKdWWkFzW5Za+WG6YNDzF5bWIx1GQ7cUpXr+3DklLQs3pP9zNBg7plX/NmhIUv25OSWuv4KwFK7TjhsjZXic2dRQsYH3/h3OFLP6oSDP+rLcDAV6DM3jttjEu83P961gYRUDJ1wVGz8wtZYIb7Wn136b41bk0/phKNs+Zu+CUd6QbPAwmzeb158ZlHPECFVRy8cGz6nsdNuE5OP0kUv/gscCUd0wlE07T5fg4PFgQ3HCni5MCEWwrm8zScKNd3G7EEnHDX7xnGkes9occTe3pgz8I//HgDVAUfu0Et8Cg5eQxi6xT0wuQ9amNDSbtN0GBEOnXDUn1nytxEi6aQ42JEbIRo3hW/XCQfRDt+Bw2JzvDk+QgoshL89MdJqc7gRjqawrRzBs774ibWuWByvOzabg3hj4Fp/hGPaxnSJsBA+a2uGGx8rLXGHxPHCSXc47Vax0F8853EO1p2c73ePFeJO0mEhPDKtzl0T0raUM+I4XrVjiDhlb67J7vdrQp9+NyGdKuGwIXzWlgx3vcoS4/q3s4wl4mx7RkDlxi/97lWWuKe8cLw6JtzFIJgOOLI++6G1pkA06CyI9bsgmKRYiDdbF8PnOuDAC8bf7LRZRBuZwucKjps+OKNn4c1lOPDKLf1EG19beFNwsGTfGzjw5ug9oplPLdkrOEj2IfDQGzgyP/2+EMr6UrKPgkOkCboOB54/5jqntVP+NEEFh0sJxuQPc6QbL5n/jN8lGN/+8Vl54bjrs3NKmmCgfTg1Wl44Pp0Zo0RNBtri3dnywkE2q5JDGmikz4gcDrn81o/OksjodSE1ZPiykHrt4XwZ4dh4vMA1OTXPF3c+TcSY4ZNwOJzOT6bHyEUG2ginUxVv8Yi1d9oHL06UhYzhS5M6uuzuKPs00dWyTxP9q+wT/4hrDuUhGzF1YOP9M0jl3CuWJODdvUpWOM1oTAzUTwvGRafXvzHOpCmDb02IiM2oN6zUZC5L7aRikKpDKhfDA84HvnKQUzTQVKlJhpDTkZXPDQ8xDxYvjAg9G13ldKoiteYwu915LKyceR8juRcfImhoj4dXOBym5EKVmqxu6Nx0vACBvCexeH1c+JYThTUNXZoyKVT25bUdjCWUWkDobIh+elQYpRmOh5VX1HZoyswAxwdTo4orexzga2yxBCXU8M89fVP6l3Niqb1xS0/CrDSmC4U66M6PBCfUNLZatR5aQXnbR9OiFRzGLtkz9+x9VQymBRV1ncSzk3MbUQwExFefCK/YF1C6P6CUD3yNSqvjFA1o1vs5BHQ+PSRYLNkbZQoO/LOZMUJAZn5Dovfx9GiRz2GsKTjwdydFVtV3aqY3Bp63J0RwwQoOj2aCPdQ3ICK1TjOxhSXXPtAngEtVcHgnTZBFuIiUOhNiwfRTXKSCwxPWTaz6XEyVw9tRSS7gTFSliOgrOEyUYIxY8nBImc3uBUT4oweDSp8fLmIqCg5TZp8/3C9gxPKk3eeKqd1m6FDCj/Ouu+tc8fBlSUyAxAUoOOSQJjzw1QUK62w9WUgFN8IVblnESc1ropjTwIUJ9391wQBpgjdM6Vbu/uw8AZIxK5PnbMskF4ShheVcgl1ZRS2VdZ2dlr/l4/CBrySrEhyjAc1oPHtrJh3pjrzAAN2K6U2Jmm7/5JyhuhgFh7HG/ZNXmsDQpeAw0N6bHCUvHGrhzVibuTlDXjjmbs9UcBho7GshLxyE6RQcxoYgqekpIxls3UJcRMFhuCKSab901cCEFlLBYawt25cjFxwrD+SqNEHPGYp1WchYdTBP5ZB62rafLjK/4o0Aq+YVU9nnF+KqHxsQZE4yHh8YRFazhik4vCiqZkc3UxXtIFt90a5sIZ5WcHjZ8spa2bLJ608ZLqDvvLj8slbNbKZETUha5m3PvO/LC57HguV7BrDiqnZNmbfg0JNR3GWxkw+Gbva2jwwvPcgq7pezY4+ElHdZHfovXsFhiDH/Z2s3nY2hhNxj/qHdK53l2fH62PCFu7K52TChv1oVsCo4DLS/ZkUkZjdqPbSGFktsZv2+CyWsfjEtQH+mU49PM/aqpQsPLCRx/AjyNa2HRi8uG9eMNpXs88SgoNqmrt6nBCN5RW9NYDspt5ExhoUxnA985SCnaND7dGXU948OCPRcso/KBHtqcHBhRZtmessvb3tyUJDKBPOE/dsLgni+mNPisxrEq5OCw3D7z9VOahoz8dRMZmQpM2Pl8rycQ6oSjJkwMlEwlRaSp55KMPa0dZ+hyVqG0+nN0nUU9vhgSpTKPjepNIEiT7xwincZz1htY9feCyUUEVTSBDl0K+zTSfhLxKncbkx0wlNq5+3IemV0mCl0KwoOBGcuSJWoA0YtL7ck6mWXtKCFpKSkC/In3lw005rSrTzYJ+DN8RGUfhu7KoVxhZ0MWIUJSapJy2+iKCBjDOMBH/gaklhzKLiMBjSjMV0orEB38+pWFByUjpRXmjBjc4aCw0Cj3oG8cDBTVnAYaCx6oTiVkYx7vjjf1GpVcBhrpPnLCAdL9irZx3Br67SJEn2yOMWGPJRSqtIEqb4lFxykiqk0Qc8Zy1qykLF0b44mv0m2jdeoFcnmJ2Pc6hTN86ayzyl/TnKvmclALUEimYLDaxs0kdppTjJ48Nm9TobSrZD4effn500V0kCnqQlTcHhdzmSSbSLZI6Gk2jTSJgWHKPpzNLT8SZGC5XFH7sDufyLbSMFhxp1vtp4qonCxJ7EgKEc9CDn2B1IbALa220jgQJdmNBY8y2CRoK0miyk4hFEbn2oIbq/hgapq8Z7sPKnV9AoOMR1BkMjSF9XsH+kX6BoQj/QPpMb+uiP5qFF0TCwUHHIade/ZTnzJnpyJ61IHL0pgdz7yQFEskvlHTiEf+MpBTtGA4DevylLsJ/endulABgAAAGCQv/U9vmJIDuRADuRADpADOZADOZADOZADOZDjBTmQAzmQAzmQAzmQAzmQA+RADuRADuRADuRADuRADpADOZADOZADOZADOZADOSAe34f5izVe/wAAAABJRU5ErkJggg==';break;case'apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__f1f297b4.png':$g='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALGklEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUc7syxR/Nq2bdu2bdu27fvZtm3b9nds2z5J+v2e+vGeOyeZSbqT2qv+GHRPZq3ZaVTXro56NBYklUz5LafzQxnfX5783rHxz+6EccApF7lFgdgih6C5oqBo1KcpH50c9+Q2f2oUozBVopwcAn99ddHYL+Of25mv3iqjChWpLuSITlStGp346n586aCN6jxEyBFdCPiLxnzB13XFaEJ4oJAjGuBvrM3ucC8f1UXjgTzWenJIm+EyMzQ/Ot6nAgGLySGgC/CAGbp/+cpicsgIlE/oqfETVpJDZq3/d26S8PxuxRN/+Ltltbm15W+f2/NpXZiK/3f+wg8JOaKkQ0l681BdJtBYl/LRKS2Qo2bLbF2Yik46FwvIIT5QHOF/Sg7QkLM1/pkdQyEH/rHmykIhB1AFNQUr8lYMjx/+0/KfXpzx4s2jb750yKVn9T/rhN4nHNr10L067LVDmx2wvTvuzSkXuUUBir008yWqUJHqhbWFykvg8OazOSEHKF/YNxRyYEWjP4tRcpTWl05NnfrF4i+uH3k9336b37ZxxXjUDSNv+HLxl9PSppXVlylXwYKIc3IwI83p+mgo5KBvii1yrMpf9f7890/pc8q2v23Lt/TU+IlT+576wfwPVuevdmWtlQ/mjBx69FrFYmzQ5MAaC5KjnxybijZ9svCT43odxzeLiB3f6/hPF326uXizChYstTskR3N5vj6uz1gX99R2/02OzbMckoMfjWZyMBS4ZfQtfB5D7PYxtwfXkBCK4ZAcef1eai7P06elszr9V+HqjdMdkiOny8PRSY41BWs0LUyz28bctq5wnWoNCNVxSo6+L1I44GvWg4/s9nf/BznWTXRIjozvr4g2cgRU4JcVvzC54DMYa7ze76t+V47B6MEhOYpGf86VwpGf6Cu+2vKktw7/LweoE3Ikv3dcVJGDaQhNt/4Ghtvd4+4ubyhXDoCHwyE5Smd2/EcLsWGavliXvEwXrlg6xCE58HZEDzkafA3nDTzPClpou3DQhY2+RhfJUbFk0D986i/t1VSSqa+XTP7l79fL5/eKRXK8MusVi2ih7c05b7rYrVStGa+vp399UcDXpBf6M3+9kYsl09rEXLeC38k6WmibnTHbrQFpzaaZXNFWMPRdfctXWZT4+oG4PmNuQPryzJftJccbc95wayqLj+u/7tKW6Lu1cfPy+70cc1NZ/J72kuPM/mc6cIIFSY6EF3ZvKkrTBerTVsecE8xSWuiZrQP3eZDkwNK+ODfQ3KjL2OQ+F3Js//v2ThbegiYHlj/wdV0m2hbehBws2YdCDqxy5UhdLKqW7IUcBPvgeAiFHPHP76qFstEU7CPk0GGCwZMDS/30jEBTvdVhgkKO4AOMiR9uOcA4q81tMRdgvEu7Xewlxx4d9hBpgoe4YtgV9pLj2hHXiqjJQ3y88GN7yUE0q8ghPQThMzqGwy7bqe1OBDJGXEgNM6JZSP3D8h9sJMevK38NTk5N/+Jmb6LbjKgkhz/gv3r41XYxA20EoWuSvCUcqG6svnf8vbYw48GJD9Y01biR9umrYNM+fRVbaZ/4I3637DutTDHTtvt9O6Ry7oolcXi3rJLVRjEK4wON0YRxczPnnjvgXDOZcf7A8+dnzfcs1WQyS+2EYhCqQygXzQPGAadc5BYFlKSapAkZET/ixN4nmkOLk/ucPDphNC8mSWqNQLO/edCWQYz7aMkj2ImgoR2ydYgv4FMGQlJN5lTn/LbyNwTy4aTFOQPOabOqTW51rhJYobJPr0inLSHVAkJnL/TTp/U9jdQMg7cOzqjMUCZAyHH50MuTylqd1bu4rnhSyiT+3K/OevWmUTeRe2PHNjs6pwKFqUKiDqrzkMkpk0vqSlQrEV8af+WwK4Uc3i7ZM/YMPSsGw4LMykz82ctzl6MYmJA8YWjc0J4bevba2IsDTudkzOEWBSgW+hgCdh7b81i9ZO8VhBzYdSOu0wIy84FE76phV/HaQo4wBftcPPji7KpsZTxoeC4YdAEvLOQIayTYQV0OmpU+SxmM6WnTD+h8AK8q5IhMmCCLcDPTZxpIC4af+iWFHOFAC77qMYljWLBVEQUvMDJhpPboCzkMCjBGLNl/c/8mf5MKO/jRPpv6nNT7JP0yQg4To88P7nrww5Me7rquK7nbPF3g4OHMdbus6/LQxIcYAOkXEHLYIU3Yv/P+JNZpu7otGdxwV7iyiLMybyXJnO4ad9d+nfbzQpoQAYhuZc8Oe+IgeXzK42/PfZtYEJoWlnNxdq0vXJ9VlVXb9I/ISg44JVgV5xgFKEbht+a8RUWqIy/wQLdiPETUtGu7XT3TxQg5vAffz15pAk2XkMNDXDLkEnvJIQtv3uL12a/bS4535r4j5PAQ7GthLzlw0wk5vHVBktPTRmawdQt+ESGH54pIhv3WZQPTWkghh7f4fNHndpHj6yVfS5hg+IBi3RZmfLP0G4khDTc6rOlgvuINB6uKCCT6fHzS+MO7HW4mM47ofgRRzQoIOSIoqmZHN6OSdhCt/tGCj7R4WsgRYWwp3sKWTRHvZXiBW0ffurVkqzINImpC0vLuvHf37bRv+GnB8j0NWHJZ5OTLQg4nEcV1zXXEg6Gb3bntzl5zglXcG0fdOGDzgPrmekcv7x2EHIz/2drNYWFYQuwxf2iks+72HWcPOPvDBR/yseGE82xVkFXI4SH+HhWxJGeJaiWKaosWZC3osaEHq18MC9CfOdTjU4y9aqlCh4UkjocgX1OtBLV4bUx5DQn2ObL7kfk1+aGHBCN5RW+NY3tZ7jLaGBbGMA445SK3KBB6uDLq+8O6HRa+YB+JBDum5zEJpQnKeMSVxB3V4yiJBAsH/muCoPsXM7Eoe5GeOgk5PMf/rnaS05iBpzIMRCkzYuX1JIY0wgHGDBgZKBilhaTXkwDjcKPlCE3WMiIYUMNPk9jjsqGXGRp9LtIEkjwx4dRzmfAgryav+/ruJBEUaYIduhX26cT9pf1UroOBzoy0Ge/Ne++MfmeIbsUIciA4C0KqRB4wcnm5Eqi3sWgjWkhSSgYhf2LmooyF6FYO7HLgeQPPI/Xbk1OfpF1hJwNWYaamTl2Vv4qkgLQxtAcccDoldUq/Tf0oQDEKU4XEClQX3Yq55CB1pL3ShNdmvybk8BDkO7CXHIyUhRwegkUvFKc2MmPvjnuX1pcKObwFYf42koMlewn28RxVjVU6RZ8tRrKhMIWUSpgg2bfsIgehYhImGD6wrGULMz5b9JmyDPZv4/Xo5EfNZ8ZTU59S4YdEn5P+nOBek5mBWoJAMiFHxDZoIrTTTGbQ8fF6KrIQ3QqBn3t12MsolwY6TaUh5Ii4nMmQbSLZIyGlPEUZAiGHTvozcMvAo3scHSlaIHdg9z8dbSTkMHHnm3ar25G4OJy0wClHPgg79geSDQArGioI4ECX5jUt6MvgIk5bJbCFHBrkxicbgus5PFBVfbLwE6T9SmAvOfRwBEEiS19ksz+k6yHBEeLQroeSY//H5T+iRnE+sBByWAby3rOd+KeLPn1u+nP3jL+H3fmIA0WxSOQfMYUccMpFblEA5zdTZSv2k/tLu3QgAwAAADDI3/oeXzEkB3IgB3IgB8iBHMiBHMiBHMiBHMjxghzIgRzIgRzIgRzIgRzIAXIgB3IgB3IgB3IgB3IgB8iBHMiBHMiBHMiBHMiBHBDxnn9eIYwbtwAAAABJRU5ErkJggg==';break;case'apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__f1f297b4.png':$g='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAMAAAAKE/YAAAAC1lBMVEX//////v7yyqv//v3qrX399/PZaRL44tLjkFH9+fX007rpp3XWXgLXYQbbciH34M7jkVLggzvkk1byyqz007nYZg7+/Pr228b++vfqrHzhiEP33svii0j67OH67eL++fb//fzwxaTopXH+/PvstInwwp/++/njj0/XYAXabhv559rZaBHhiUXhiETbcyLii0npqXjZahXghT/YZQz228f66+D23crXYQfijEr45NX45NTz0LX89e/ffzXZaBLstYvhh0Lww6DpqHb9+PTabBfefTPjj07opHD12MLxyqv22sTbbx378OfbcSDcdSX89O7aaxb67ePz0bbnnmfpqnnjkFDmm2Lll1vbbxzklFbffzb11r/abBjkllnghkHkk1X23cntt43338ztupL44tHijEnqrH3oom355tjlmV7z0LbopG/rsYXvwZ3fgDfccyPfgjvuvpjoo2700rjopXLzz7TvwZ777uTstYr45dbbcB788er11r7ZaxX66t7YZAvww6HbcR/tuZH00rnrroDcdSbnomz01b3eezDwxaPstoz34c/zz7PdeCr55tfxyKn56dzzzrHnn2jfgjrnoGrpp3Txyan77+fklVn99fD88uruvZfmnmb77+bqq3vefjT++/jZaRPtt47vwJzefTLdei301Lzvv5rxxqXyzbDnoWvfgTjnoGnhiUb77uXttozeey/qrn/99/LZahTtuJDuu5TpqXflmV/ijUvlmF389O3mnWXvvpnddynwxKLssobxx6f88+z669/YZw/23MjhikfghD7deSzabhrfgTnyzK/YZQ388uv118DzzrLxyKjrsILuvJX449Pdei7cdCTghD3z0bfbciLggzzklFfss4jWXgHXYwnXYgj78enefDH56NrstIr56Nvqqnncdif78OjYYwropnPqq3rXYgfcdijqrX7XYAT01LvrsYTWXQDabRnWXwPcHI28AAAFZElEQVR42uzBgQAAAACAoP2pF6kCAAAAAACYHXuAjqRLwzj+1MS27Yw939i2Z23btm2PbduKbdvdwZMce42u2uqkC3NuLX5Hcf6Nqnve9z/e/3V0hoYEBgQEhoR2duA/Qm93Dx30dPfC6jzcbVSwuXvA0iZ5UoXnJFiXWwydcHeDRfl40ylvH1iSmzfHESzBitw5rkFLXoOcgAWvRg9P/lXfyMjIu/lPd//8aR//ytPDsm+OUQBJdv7DMIBRq75BegMcorEoWy3a1vbK/nvOs6cPcwura0YjhoZejtZUF+YeevosZ6L/103HaHxYLZoDMN+DJTGxEXQiYvp7du+AUz3yaOl3atF2mGxBv72ZE2h+/Os4qOqgPBo3AlWi2QUTec2aQRd9dG8V/l2nLPq3AIL8+Re+suhOmCYnnprci4NSqCz60GEA7+Rf1MmiG2GSd8RTs4VBkAuRRT8MSQOkLJL8uSy6AaaQmoaow9AKyATKoo9wFoClk0lOkkXXwwwP7lGnrKVwECCL/gb5AQBRJFNk0TaY4Ngp6rYs02n0EzIiAUATedb86F/QgPc6fXsUkXxDJOA2jT8w/e2xm4YMO7sQv0mSqQC++OUB0y/EPTTkO85uecP8iyIAX9pj+i3PTkNOKw4XRfRXvwIgzvTDhcYMKY5xRTTbMwGYfozTmDH8S49KNC8oou2wVnS3WjR/I48esFh0r00tuqUDzocA8dFwV4tmfrT6uCU0Wn2wvct/Wmj6YNtKQ+YKWSE00JC3CFnW3KEhMULWYkFDNCDAS8MCMtgHZhmhAelQcnOnE4MSTOM2h7rFSqKW6h7e1Omta6DGY9BGBdugB8wlVTRTB/+ncKZ3wE4H9oE2mO+77dRs3Rsxnq7OxoZ6m62+obGzC6+GVL6dmvRskiBe2pNYf7rIf3pKMizis5+rpgu+d/UKLOXFk4eP6Vzzjw99fz7E+9gUKJSUXb10uSabMtk1uZeu/ug5FH5SCxG4fQfUJCd4zRs+f+7R2XPnz8zzSkiGmpLAMYhArsyETsfyKCqaH9kFXRKOUlw0d/pCh7qbFBlNzgnXnFxLCo4m14W5wWVuP20nLRBN2jMi4ZLIk98mxUY7WNtYWSVhXJLX7dfvJEVHKxzPentcMlSlvW3F+uV0JDRaoXjlgQ9WVJaf+WGiD+CTGDRcXlnxiQMr51JJfLSqllaqExrdQkOKIcJFGlILES7QkA0Q4RkNCYMIbstoQKEEIYLGqNuQFwQ5Qt2uQ5gY6vQtCHRL34apEkIVTaZmryuDYB79Q9Qke/UaiLc4vpkua565CNYwJXUqXbK8vwtiOc60SRmxr3ECrdNWRct/XQD/JjhKCu+vpjPN1/x8o+Fo5DWIwLHNUPhD6cc3zAz0pwP/GTNTH5WWQKF0TNQQsGUr1EQ+f+EVFR4WFh7l9eJ5JNRcKRA3uWx7H3T5/BdEjlvLN0OHjVPFzohDd5KgkY/fkPDBdkY4NKnbZolpvLZMgouk87+0zArh8aOtcMHhgz3W2nuc7lecHgpJn/xUPknR0XOp0HL5qhdUvWvF9FYqTLXO3mP2qdz7/ekZSxbMj06av+DTn0nvv5/bPts6e4+HNORXEOE2DXkEEUqKacDLBxDiOg0YgRg3blK3tWsgyEnqtgrC+FGnAYgjvZm6vB8iZU6jDvGRECotlZr5pUG0sAhq8rIIFjClnRoc/Rkswe1rX6eLAlMkWMWxE8fpgpu3MmElv796jRNoP3EDllO1ejKd2jJrMazJrXRk/ZuoxNGsD22UYGmJm/bu35eXX9DaUpCft2//QNgu/Kk9OBYAAAAAGORvPYi9FRsAAAAAAAAABIY9HLkA16UTAAAAAElFTkSuQmCC';break;case'apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__f1f297b4.png':$g='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAKzUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOQBJz2xh+No2Cte2bdu2bdv4bNu2sbZtewfr3XFyn6v+/X/ZmckknTmnuqpmMt3ZVOXZxunznva8RcY6AueWDK39WN+fX935oye2fvFeFD7wlYv8RIXsgkMsNjXmO/LLrp89veWzd7lhoRqVaeJxOMQSoVnf8d+2funevPVFFZrQkOYChzdtpuJo+zcfxptOutCcmwgc3jIj4Tv2G95uWgpdCDcUOLxgicj84KoPpgULVbght9UeDukzFBlp5mP1h0zD0BgOMYYAG8hQ48vvNIZDZqA2YaEKf0JLOGTVertrk7Yv389/+i//LQPL3nnn73548+dVZRre7vqFP+QROGRA6fjuY1UdI7LQ9bNn3Akcc01XVWUaWhhcdIBDfKA4wm8IBxYeam79wj1TgQP/WGx63BQ4sLlAZKRhqvXiSOm27kt/bDr6jap9ny3b+ZHire8pWP+m3FWvvLbshZcpq191ja9c5CcqUO3yn5poQkOazwcjpp2Gw5vXZgUObDJ/eypwUHxHf5WlcISmot0FvsJ1nYe/Wsm7X/K8S2kp3Orw1yq5bU+hPzQdNdNqbIhYh4MV6dD6T6YCB2NTdsEx2jSVu6xt2/sLlzyfd2lv4U9s/0Bh7vK20abptOy18sKswaFmrzNsxiYNByUy1ul9OHwdM/mr2re8K5935kjZ8u6CgtUd/s7klwBstVuEIzY5qj6H+mpaPne3W8PReMUiHPxRL8PBVODoN6t4PS4px75dnVxHQiiGRThGdnwtNjmivgavrLlV5dn6ixbhGFr3cW/CMdY87RgWFhAZb10cIoTqWIVj+1epbMRjavIxuPL9t4Cj5rRFOPr+/BrPwWGYZdt7WFy4EAtVeLyKXb2mZWP2YBEO39Ffc2X88C/Ulfj8ZMf3Hn9rB6gFODp/9CRPwcEyhP9L9Q5cXk58vyY8EzMtGB4Oi3AEL6/+Xw9Rd0FdXOgsUZWnivdZhANvh3fgiEcSuz9eogUWquz5ZGk8mkgjHFNFe/57se1rD4oG+tX1wNl//Pf6ZO6WbITjyp+bNcJClWt/b0njsDJTdVJd7/39y4x4VG309//zrVwMXFiWdcMKfiftsFClrzSQrgnpXMNldZ0ytv+H6qf4tK/924/E9Zl1E9LLf1Ldhgc7D+tLWXxct/qVvkT9Ot+SM7rj61m3lMXvqS8cOz9cnKQTzAIcbV+5f9TXoyqEeiqzzgmmKRZqZZuk+9wCHJSe37zQiEVUHZ3c5wLH0hdcTn7jzQIclNHd31Z1vLbxJnCwZZ8KHJTp8sOqmqe27AUOgn1wPKQCR+uX76uEst4J9hE4VJhgKnBQun/5HCMa8lSYoMBhPcCY+GGu3EkZWPaurAswXvHSK/rCseoVV0WaYKMd+Hy5vnAc+nKFiJpsNGK99IWDsFORQ9pohM+oGA69yvIXXyGQ0XEhNWR4WUhduqVbRzjKd/QkJ6dO4/jCaKL6DG/CYSSMg1+q0IsMtBGmIclbMmKR+fjJH9TqQsbpn9RFF+LpSPv0u2TTPv0uy9I+GWbJ5q4lz3e7YwOpXHrFkji8lUr2zgvVqIwPNEsTxvWXB3d9zKUhg7s/UTJQGbQt1WQnW+2EYhCqQygX3QOFD3zlIj9RwZRUk3QhrZdGkba6B4tt7ytsuzJmGpKk1h2WiBtNZ4eZ99GTOziIoKFtPjdiJFzJhaSanB0Ple/sQSCfSSx2fbS4YnfvrC9simmhsp8aXqAvIdUCQmd79NNFpGZoPjc8PbJgirkBjv2fK5voX7SDb2Ey0pXn45/7yl+aj3y9kgnKshctYvShMk1I1EFzbtKV71uYjJqLtGDP3IEvlAsc9m7Z855Sz4rBtGB6JIQ/e7h+EsVAZ854y/mRuqOD9ccG+cDXvrIAP1GBaqnPIaBz8zvz1Za9XSZwUA59pUIJyNxvSPQOfLFcxXPYawIHZe+nS2fGQqbrjY5nzydLeGCBI6ORYOvekNNbEjBdbD1F/rWvy+FRBQ5nwgTZhHMhImDB9NNCmKCdJnAoX3X71THHvU88QNvlUeXRFzhcFGCMWLLx1FAi5gAi/NGGE4Nb36t8KgKHK6PP178x58xP62oO9ZO7zTTs3dZhrVtzsJ9NeSZA6gEEDj2kCWtfd53EOpV7esngRp+flk2ckcYpkjmd+F7Nmtdet/wkAof9lmJyWRwk535Zf/2frcSC0LWwnYuza7xtZmY0FA39Lx6HD3wlWBXnGBWoRuVr/2ilIc2RF9itW3GjiahpxUuv2qaLETjsN96fvtIEui6Bw0bb95kyfeGQjTd77erfWvSF4/qSVoHDRuNcC33hwE0ncNjrgiSnp45kcHSLaUiwj/2KSKb92mUDU1pIgcNeK1jboRccRRs6JUwwc4ZiXRcyijd2SQxppq1qX5/7FW84WE1HTKLPO66Pb3hLnjvJ2PjWPKKaTUzgcFBUzYlurkraQbR63sp2JZ4WOBw2f9csRzY5PsrwAEe/VRXonjXdZiJqQtKSs7R1zWuuZx4Ltu/pwHgAU8wpOKyEi8bCceLB0M0uf4ntqQfZxUUl1Xh6OBZOWHp4+0zgYP7P0W4WK0MJ74N/aKSz6R07EMfmrWjn5jBhPVsVsJq2msRzwMdQ7eRiG85PRMiWUXd0gN0vpgX/1Z9ZXI5yVi1NGLCQxHET5GvmIo1W3Idi2m0S7LPxbXlz/nDqIcFIXtFb+xBF1k3SDbAxRuEDX7nIT1RIPVwZ9f2GN+dmLthHIsE2vyM/2Dtnut4C3XOb3p4nkWCZsFstENT44k4brJ5QSyeBw3a77W4nOY2ZeJouM6KUmbHyeA7HkEqAMRNGJgqu0kIy6kmAsSvgUBGa7GU4GVBjmCT2IL2MRJ+7VJpAkicWnGotkxnjz9UeGSCJoB7SBNGtcE4n7i/lp0q7MdHpKfbnLG3b8aEi0a24Ao4kBGdIXfBwk8srLYF6vvYZtJCklExC/sTKxXStiW5l3etzdn+8hNRv53/VQL/CSQbswnQX+EabpkgKSB9Df8AHvnKx4eQQFahGZZqQWIHmoltxLxykjtRXmnD1ry0Ch41GvgN94WCmLHDYaGx6oTjVkYzVr7oWmooKHPYaYf46wsGWvQT72G6RuZhK0adLIdlQhkJKJUyQ7Ft6wUGomIQJZs7Y1tKFjII1Hab+ptkxXmd/Xu9+Ms7/usHMvEn0OenPcX26mQzUEgSSCRyOHdBEaKc7yWDg4/FMZ010KwR+usr5gUsDnabpuAkcSs7kkmMiOSNhcsA10iaBQyX9aTozvOnt+U5hgdyB0/9UtJHA4caTbyr39JG4OJNY4JQjH4Qe5wPJAYDh2RgBHOjS7MaCsQwWcdqaYrrAoYzc+GRDSHsOD1RVRL0j7TfF9IVDTUcQJLL1RTb79W/KTQ4IGpJjv3RrN2oUmVhoBod1I+89x4kXrO64+LvGkz+o4XQ+4kBRLBL5R0whH/jKRX6iAs5vlspanCf3r3bpQAYAAABgkL/1Pb5iSA7kQA7kQA6QAzmQAzmQAzmQAzmQ4wU5kAM5kAM5kAM5kAM5kAPkQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzkgDe9KFu6DmR8AAAAASUVORK5CYII=';break;case'apple-touch-icon-red-507228751d2170d047e72142d2c02390__f1f297b4.png':$g='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALAUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUe58yxR/Gfbtm3btm3btm3btrW2jWRtZG3MvM9TP/72O5vMTLqTuqf+GHRn55y+26iuWx3zGG+v7v7ukeZnT6i/d9fANWtXnL0AxgW3POQVBeKLHILJvvbOT24O3rBh+elzzdEoRmGqCDliHNOjg52f315xzoK0+qyMKlSkemySQzCQ/WnVxcvQ0mEb1fmR2CKHwJru/Ow2WtcVowvhB2OBHILp8eGmp46mUV00fpCfFXIY32coZrjMj6ePsS1LyGEwGAI8YIYaX+4Qchg8A6UJPTX+hJHkkFXr/12bVJ67SNfX9/3dGh87eOa2b3n5TFWYiv93/cIfEnLEyIBSffnKqow1PhK8YaMZyDFU+qsqTEUHg4sJ5BAfKI7wOZIDjDWXVZw1fyTkwD822d8h5AD2eFd7f2Fm+7cf1r/4QMUt5xecdWDucTtnHbpF+j7rpey8ctKWS/yxwXxY0lZLcstDXlGAYhW3XkAVKlJ9vLvD9hI4vGk2J+QAvUmvR0IOrPPTW+KUHBO9oe6E72ueuC3/9H1p+9/XncsV46cKztiv5snbuxN/mOzrsV0FGyLOycGKtPn5kyMhB2NTfJGjvyg78MC1GQds9Pt6c9OWnhp/IvOAjQMPXjdQnOPKXisN5owcavY6wGZs2OTAxtsDsU+Owcri4KM3pe+1Dm0WFUvfe93gozcPVZXY4YKtdofkmOxtU9ej9fnlZ8zz3+Qo+cUhOfijsUwOpgKFZx9E82hihecdGl5HQiiGQ3K0vnHBZG+rug398sx/FR4s+tEhOZqfOzE2yTFQkqtooZsVnnvIQFm+PRsQquOUHK+fT2FralJNPpqePPI/yJH/tUNy1N+7W8yRw7LqX3qIxQXNoK3xeQ2vPmo7BrMHh+To/PRWnnR8fJN6MjXcW33Fqv/lAHVCjsA168QUOViG0HWrNtDcii48crK/13YAPBwOyRH6+el/9BCFP6iHI4F0Vbgv7T2H5MDbETvkmB4fyz5iGyNooSzn6O2nJ8ZdJEdf6jv/8KlfsMREd4N63v3tQ39/3pvwSjySo/L2iwyihbKquy93cVgZyP1SPa+7cwdrakJt9Dc8vD8Pu394LO6GFfxOxtFCWSj1V7cmpEPFP6vnWPv7V6tXU/2dVZcuj+sz7iaklbddaC45qu66zK2lLD6u/3pLX6LeDpf/0fbGhXG3lMXvaS45sg7Z3IETLExyVJ636ERnrSowWpsTd04wQ2mhVrYO3OdhkgOrvW1ra3JclTHLfS7kmNfJxlvY5MDa3r5UlYm1jTchB1v2kZAD68/6WBWLqS17IQfBPjgeIiFHxbkLK6FsLAX7CDlUmGD45MBqbt7Mmhg1P0xQyBFWgDHxwzMHGDc+dkjcBRgnbLKQueRI3HwxkSZ4iLwTdzOXHPmn7i2iJg8RfORGc8lBNKvIIT0E4TMqhsMsS9hoAQIZoy6khhmxLKSue/4+E8lR//LD4cmpGV/cHE0sK5ZTMFjT03kn72kWM9BG0CqSvMUPTA0PFl98tCnMKLns+KmRITfSPt0RbtqnO+Is7ZNl1T17D7IRrR0b68+DVM5dsSQO75lVssooRmF8oHGaMK4n/ffsw7fWkxnZR27bk5ngWarJAFvthGIQqkMoF90DxgW3POQVBWxJNUkX0v7dRxn7rq8PLTL237Djx0/5MElSqwVQcLR9+Q7zPnryKA4iaGjbvnrPmpqyNYSkmhxrb2545REE8r6GeB22VcNrj411tNgCI1T2o0119CWkWkDo7Il++sBNSM3Q9tW7o831tkAHcuSesOtwXfWsRVA9XV2/f8M/d+UdFxeceQC5N/7YcH7nVKAwVUjUQXV+pOv3byd6u+1ZYjhYkXfS7kIOb7fsmXtGnhWDacFoSwP+7P6CDBQDXb9+1f71+y0fvtzy0StccNuT9huvKECxyOcQsDN9z7XVlr1XEHJg+aftowRk+gOJXt5Je/DZQg6fgn1yjtlxrK3J1h50PNlHbccHCzl8jQRL2WGFUMovtsboTvoxebvl+FQhR3TCBNmEC6X8rCEtmH6qjxRy+IEZfNWdP37Ghq0dVfABHd9/rDz6Qg6NAowRS7Z+9qY1OWH7Dv5oyyevZey3gfoYIYeO0ecpO65YesWJze89T+42bzc4LIu1btO7z5VcfgITIPUBQg4zpAnJ2y5LYp2G1x8ngxvuClc2cfoLs0jmVHTBEcnbLuOBNCEaEN1K4haL4yApu+bU6nuvJBaEroXtXJxdg+UFo62NUyP/iKzkgluCVXGOUYBiFK665woqUh15gde6FR0hoqaETRf2VBcj5PAWtJ+50gS6LiGHh8g5didzySEbb96i6s5LzSVH9X1XCTk8BOdamEsO3HRCDm9dkOT0NJEZHN2CX0TI4YMicl7jsoEpLaSQw1vUPH6rWeSoffpOCRP0DyjWzWHGXRJD6jca33xKf8UbDlY7KpDo885fvkzdZVU9mZG662pENdtAyBFFUTUnummVtINo9eDDNyjxtJAjyhiqLuXIpqiPMnxA4TkHDwXKbN0goiYkLdX3XZ20zdL+04LtezqwkfroyZeFHE4iiqdHR4gHQzebsPGCXnOCXdyCM/dv/fyt6bFR5x8v5PAEzP852s1hYVhC7DH/0O5KZxk7sg7bMvDQ9TQ2nHCerQqyCjk8xN+jIvpyU2etOQt19mYlNn/wErtfTAvQnznU41OMs2qpwoCFJI4fQb5mzxLU4rMx22tIsE/abquPd7ZFHhKM5BW9NY7tvvx0+hg2xjAuuOUhrygQebgy6vvUnVeRYB//IsHS9lxruKbS1h5DwfK03deQSDA/8F8LBDW+6Ine7GS1dBJyeI7/3e0kpzETT1szEKXMjJXPi3IMqQQYM2FkoqCVFpJRTwKM/cbMEZrsZUQzoMaySOyRe/wuEn2uqTSBJE8sONVaxh+Md7Q2v/8iSQRFmmCGboVzOnF/KT+Vm1AetuSfAvdfk3nwZqJb0YIcYQjOkLqQB4xcXq4E6g1WFKGFJKVkGPInVi62thDdSvL2y2cfsQ2p38quPZ1+hZMM2IXpTvi+vyibpID0MfQHXHDb/cd3rZ++QQGKUZgqJFaguuhW9CUHqSPNlSZU3nGJkMNDkO/AXHIwUxZyeAg2vVCcmsiMpK2WnOgNCTm8BWH+JpKDLXsJ9vEcU0MDKkWfKUayIZ9CSiVMkOxbZpGDUDEJE/QPbGsZc2LoY7fYhsH8Y7xKrzpZf2aUXXeG7T8k+pz05wT36swM1BIEkgk5onZAE6GdejKDgY/Ps6ML0a0Q+Jm05RJauTTQadoKQo6oy5k0OSaSMxJGGoK2JhByqKQ/bV+8nbbHmtGiBXIHTv9T0UZCDh1Pvml84wkSF/tJC5xy5IMw43wgOQBwcqCPAA50aV7TgrEMLuK0tQWmkEOB3PhkQ3A9hweqquCjNyHttwXmkkNNRxAksvVFNvuUnVYKc39k55XJsV/3wv2oURxMLIQcZoK89xwnHnz05vKbzim+6ChO5yMOFMUikX/EFHLBLQ95RQGc3yyVjThP7i/t0oEMAAAAwCB/63t8xZAcyIEcyIEcIAdyIAdyIAdyIAdyIMcLciAHciAHciAHciAHciAHyIEcyIEcyIEcyIEcyIEcIAdyIAdyIAdyIAdyIAdyQHtHp5xFOjNVAAAAAElFTkSuQmCC';break;case'logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg':$g='(]^+JbP.FqjXYdorFxH%oTmn1#,Na[(-^<}T{`+Ahl-RItQoM;{4bK}l["$V3F6U&V6Ey@S8#w=t>3kaN[hLow+fWEUH+K<LoXqyEy6JupFy-JyK4S8q(7tl96;KLl/F|,Cz)p?p(B)[axu/4u77-)nvU
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
n9lrNOU01c?:p/5y16+Zkgo}`M)D6xm>7RiQ_b%p%ocllip!0).myrT"w[53iGRZBt8z<.d6p9("_WYy1v;v.xBx%3c3&hfawJgMtuxeflyK1.-:wQo"f_z&)W';break;case'jush-b3a93b18444da26820ff61746521dede__6a1faebc.css':$g='%X.mPb3V?!K0u25Dm[994[Zg@N#Q)YOC=2R_hE~4)=>cbdia55M!*B>tthOR~qe;I4(JE7nkwCcvDp&`dDJK
f-B>jO*,sE/El7[<CeY-WtHaF_.kmd7o@TK)f,bmf|yor7&?P^<qeg8="Q1IULQ7R}m#!4#2";]EMU!F1OpTIT56:qJms/Dv-ge06DN|n`FW&*;(q:eY(cbjkU?x]gUap[%36X.0y?`E_>T!nUPeLxWQNBQNxgW
`cW13j?"h~G+;>ckmN2+
!n%Sl8/pL[B8
M3BQ=4I8t<Wims^cdx*+69AM:3(4#D7NrL5JU;F-nR)NxQWs7{b*3#%wvQ^i5"h{.StgdiF@(/BD"LK.,r2{$e&b4#$3x
`jO"8tQQ1CKB,TYk$:TIGnJ]L
WcYUpj,c5;pfH7%lebNsHuwv44..jP:
oZb0YJ@}qOxs&)5?s0oVA*
swu
jafJPvJQCQ5?:;JN^klF$8EV7&
J?y"=oJ8]]rh5ZK31++0s/h[@)W/]t4{Do0LZsyY]Ui=F:=Lj0OiN0Q{mj^W2%j7&7^of",u?pagL&"iw=-;EKZzP?gdKNd/SR.wcU#s5@q~Y}(R:VF$Q8dZ,L)ey.Hq2O=)g/K}w)n3hY`Mh>::q<VI?VE1nU^-^D/qa9JwIPcYCppQ/X@!6ZZjyFhzsSz&E^ke1)2F[wC7uibhCB4fEU_/mdWy=C$?"]4Jab*)fpfaP{Xx=d3$rPj<p5Yo$$mqn$a[gdSRbtw^Cd:8IL9Ep@:NwGhGJP+=>bUa%5!)#P![lKsPx,lj]_TA`s)}ui[?@K5<"=yhHX9ac1.(pCt<=mY#,*@Ho8y`eb"03`0_6Wx1`zEp[UTJnLo&k`Z*%U;u_AP@.ah.ussF)SKcuGHm@)HJg~D|K@x[(QcKT7b133MD8{9lxGHdF&gk".u/dfBj;$`frs[]W0RtuZ<]q+TW7f)<cBALJpw,4G-PO&to>Pl>;:<vNY$aUe;BxF$7V3N~j=h.gDd*D(06YWAm/nioW)g>F(ql[`!L/h`[Z(V`2J';break;case'jush-dark-f8dac59c6ad1018686e52a0e0357e421__2ec7793c.css':$g=',Gjwm6?!R"-YJmoGR`r@~cEv;#i.*-_KUyr[0$CF,>/n=#+liP*01.(73:+G.C]Ek+^-h&|hnGDq1:ccpxU98SxFh5MU%c+]DCcezAcUOWmDiL$
)yZA,ICx<`.i#E%U;lo*kf6u&LQx+!%1t]iP#G9;zGT,4U2"ha>hB#am`y1YU6$z!l#C%';break;case'jush-31ccd1ce96536a294822e952872683d1__adc1a188.js':$g='%hk^;xqDE.!v]yOYu=(i
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
<Vk@,+r[?)N;wLkXZ]chQan6+w;/2n$)5Sg#p+M`6dj1iL[?IHIqIIV>rZ2j--]DJDV?t/>43Y!BZIe#';break;case'icons-70163a2695280bf75edba563e7b5471b__f1f297b4.svg':$g='.n1F;boZc.Bo(o})e?Uah,r_6,]#*f&vFTX.Are>cU5dz3=O.b=K:xCw9_>Q|]_*[eKZ"sSUICkDTRCT*BU7|p[d&s"W9Mp[,MrS6yxy5+)_Hrru:n-yzfmIccln]H#h{29y}twyT!RpdN%uauzXwpktSn}v[A>wvv/rOyeKy3VK=t~F=7gchMjiTG0nqFU@:^Q]q7nujs
w!w0k~gz^$oXd!mBS$w?wMhn,b.EtK</c`stLO?duuLK$dxKSPK[XQ%l>}q-L[fA"ZhiZ3t3`g3VVh,D5Uh)$R+zM,l66LX34ul|[)3_<*X:m:n?Byj^pwGn?&H0GyxN6#upbP825@AIDBVtmNL}8/jQ<$?W6b+^1=Y49dD<fRakGoENtQ
?F`pI9_ITA#VMP|a*`ER1bDT{HJx[Wny}N_cj]SZpX!R1jFNa[kSlh*d2fic@bRrKbQP/wD"TiCL+?uiTND7`+5&:c&^5``=B5h9DLTuJAP&4I3mR8oG`R1H<ChCF4xry1T+YvV_!iB`|a=s@$c)cEjW<EO_72Ec-h/kmp0wBm^ejFtIL$wYM2u8Wo%"#Vcwz,7LH=.#
HRuwMRSBxAO]He_-@NgIA&Qxce1@3jUg_hQ*g2
mr}DDF6HGv:;s#D(B@v=LD[5E;Y"klP<|+&]Vx}y<Q.C;]ay~VX].]9(m^4hx9"2%l0WP8Ft?%
7L]!1wu6>{6LK,iW>kDaTn^JJ;"WU:/u#;whFLC`?S**Ex-:H`T?-Eh)(elz]6Y&qt[iBic`JubWv[MBWbX28fRXw`<2,2J5mPARa(9x:16W<"EbD^<qK%PO3CCfH(*WI&cs4:b1-Co8K)(h^7a]=+wCf05d>*?,9nIt*EE?R(M^w1q+ch@<@`#Y<G2c.72_v"c[]`$R6m<!8Z"`>bbj(s/NJQ,)eiD-XP1uQ+5Q
~Y
@7[2>V>k"SBI!~vAeC[)Esr--4U3QQF}#5RNaAgRr:Rz4~;})RKjJ%+?*]NT16D"REy1i$2t!L83G?ny5`K76IdA"@[uCh>&aQ1Sm-Xp9Qe}I(4f5y[ZC&DtN5foOV4^!EXnxPD4:L6tD:#M^ow3FX^V5XZ6h=A6I4U/Q_c0q{%x.VrB49K4
N=JPAWnuvSXr"Y,*peSMxVhV%TsBeST
g6A/Y6~,nYh
!XXvbr*)^6og;Ea]2s.cRcj]qyF6sTsP&^*7Ms@YBJD+x&EYsKG00dQyp4X5SLCSlL=U>xR#<yNrB_
dsod)ja*c=[F><yhoYYLaXW#3u]yiWS=5?:roMdg8>@e".pM;>9yQ8!SCa/OTNoawBMhF.$z*BMN"N!BgA#
1df]o`.:Y;pPvlv&*zq|&
6CY3I$4+vId,Ft3UVzJ.U3OcS/X&%/Q!Nild9}4L!c!l(d?j,&e6qFQ3P`cO#
H5u[G&k)kQ/g,)+q^BNfi`Hz!N?
e]^Y.6"`C(tP-f%H#vY$"LM
5^sTW7_yR!o1h.43?mrNhO8>E^Am>{d-%
28NW!N>#=AZ>&aL35&J#EtJ+w[]Mw>ux
JJOwu0HD5-=[1"f;$_~0h0($r?hOIe*v6S7N?K]ubK)[NTa^,03.+TKG~,u(LU}qTn-v[nMeJmNo+HCw/x%4$%ARi)O[n68Y{BI%m/%.IAU`#/N+z7H^GyCVSgD+=[&KMxI3g2I@?Gd3E=>9W@ctYM#w>y.[e`[MNqeNG1x3=tK2jI^R0&DK7"-PsYN(pVrK[ly"n:+_+5F)Q^N<F,0-kIMW&#2/1r"NEbmua
:I">1H7%-(~G|<#Q(kpyBKL7~$M`*,K:La=qU7OcR>
wv479BRh#P_s%|ns6G-o^k`rmc"_G>yJe)2k.84)Z=4QSCaO7oW50)v}S{wq(93bS:,TL"%<6^Z2.*K}0AeNRQCVCM"eJ:S)$+^;xd+pLXWmv~*<9Y?"kC+BMswK,TJ(-4<P</O[+
D/Z0^MGMmcgt=JeRg8cq_J=DC-
L+}P!GxtNwmm9B?`yBn+(&A!:<jp(5Z+}h2*1iLHYw9AV!.OFQtVf>$d%c(B)hibE:gC#jTM~Qxc2hnFh+
kZ_OT~ypxQuOW=x-q?jw!L@-/bFd1*`3ngJd)fd#]t)vl^^E*yjXlE=p`TRpK*^`UQLV^,3l!AYf6GXuyGybRJg^LPA.ml<C=I=WK4nR9;V%r@&|!kKtcOD#OJ!8h(w0cdF|Mv`n"1QSB.graW<W6(w
l(rJmli/sZ`t-A@vi;G1wn1>Y)oy]K:1K_>@W5Ib.}DJ5&9SR^YGk1y{<j
FN6Y=yYl8[|_c)wiJ#*Wc1S0d"=aWo^B~YRIbi=!u(Dqy>U2t3yNM?m)::6<B)Dg9:T7)3fUT&SCAF91Q%fp1Y,wUP5Gk2#gCr,eq/Is6fp1%g&R2>WVQqmhC`]mU:q%>[N%SS5#<ri=X`xqj;#pM2pY+sv;~Py94[E^%`wL/em*},gg:RVBI;A::AP_MAXs.%aJM^}<5aqO$?$`c"}!ZeF"P7oh#n|jYdc/iNyuVYEY*.,"=p9]L/u
#e1<.
P,kFR91P,MB+(Tf:b%j2mqwT
@G4{i`n$pC[qlCGO$AOMR/96/d@]N$HD%U`g<#md"`3:/x^vS~M]vpow9[xh9.i&Ka$z3w=.v*Ymq3do8LKfZX,pelu~HJW+go4`GWv
QA_-k.L<9dHfc==u1T^V8M3+fAhjVyTGJzU-_]7*Vy5:3Q$;_DX~uFjB.jO?Ucg+V}:[P?I$8FF{p2/m!0LkeBGm29BAt&X|%V`zyun9qv65"*uzVPe%Qz"Uh)=EG=T20MFRqt
"bhn{AjGhfu5ZptI0^;)TlJm)&htT7!
odWO4S}JR)A3MT1;pN{#
L-UJg"8S^L
;SV5a(sn{a5t53nqg)HO?RHo7hxjKg9yotZ!Me]`.Lif6A&)7mL:7jYF:g;P~Yw.___-`KGa(j`A0EvPc:(p7/ke8R7YQTT%=1J]lRc=s*.U4Gf"v:FCXL,CA(M+#_Dm|@l"AU_0*rajk`%NfXdKQhp3Sp];`$c:9&c`3naA-N?`gTgkH=de9KEEo*GOGQ&V~2ST+_sfS%pdLJLSu@E:g
U:!4#&(%n=
u`OOeOPu=<%oeON+QK
Rhz,Ll-Nob^yvX[xW#zEuuXdkf?OrI@BuEw/2$RL05~WD$
oChHd*`0eR1k*"@5K/k8e="BC46l*{t%<r5:4GdW
S]j4m>,3=n[*-o~RNbd`c#"qB=rcYDmI}%V#j8dUBEkNLVvY6,!)/.(E{60RE[:%wg"+r[Jhd=dvXB%il/oCO3If`3{e?p|d`&fE{i{yA9|Kqs;J>v1MuBU3Eql1@,k
$1q/9,WkOL?nQkXL03(4Lx
_Co@?A)n;I,3m)9A0>n9lF)|*4*S,9et>sgFD|#"BpE:Ji1LUxXz#"D;5PSoK|"LMLbT#
vG<kE:cJy{%7n9BCg/V
vrt2E._at*pH]&?p`FokXr:$.M*Gyg,1
R7k9lTM*U^A,(fq7O[k;%?jRa[;36@a^w6-$Rw@wz#62/t#(f9qPS/u._gxUhTfP>,u2>ni?S?Yo~YkBYjnRMDhQa`g3v.q
U)GRR7!O`X?/u?C"]WIJK]J7p-"Fj>IOJgJ,h0WbnP&[l2]Ve8-G;ax.SRJyPSii-RL?Sl@Nws8SQwO#mrs^
Z83;elTEmy7".l($?F0@av)JYeh[Q-Dz6uRCG.GJ"8S4=/>%;HYCFG]TYjkc.aP$Zc*C3&/t.@[;Uk&Mko=C_V39slao*`kV_+a)"n@8C50V(VZ3;T.nlAKB[&j-`01)j8480s).){JsM28s[M%5imBcn##%51q7:m[7CFVX<<k_7R<9wtTm]?w4
=(R=c
HD^ExT|5&eN[FXusRW#Z/0MB`]DEFK2]]94(bLJ<N@P8g5ah.]hJNgXGDhaUo1f^eFeW?c@a2>iKH_$lkIw6}q]c(Y0k45JOr3%yar+v41U8&z"tOGbJR??&?7+[&pEwlhpb
vNu9WffDDB=Vu06L@#U_
r98urI%*u>cr:e$o#5aGt1JM1g]V6?zx
3zhPBi@_EJJL#Mt@&()Gc(sK:x"Banka49_&,Bi=h!m(K-k*=qkdI^#e8`Y12~H;Hd*75o
%-0E45)v;7RPCP/P.3GR2&4tzp`$C^MCCo&e8S6DkLMOfm*hG]I9P!52&2#Dkp=kSrt/s3aX{0@`qL&RKuSO2H_Ca*|juFHQrC&a~3IThG?=)#%lH^!?~FoH-mhAAI7djw9/3G$4TovbpePv8$nXr+hR,e4f1DlOoUuoKiP:|k=;5mO1
(._n#SgwU$_5WILBfXWBm5JC0ne*76[HcDY
)i&
.=^lz(O*kaVT9{B@P.hg
6GORrZT&PBL$(Kn(_IghPaGG@_6IEbp`gRy$HQ&MWS=b^rlqr
[>A3:quF5R.
pB8D/ckq[5-4B.:kaHew6AN4Q9U@)J]ef@_V:)j;K6-Dy]Pr1OFo`iY8iL{D_a%b@SuC=i?o
!NAM)2#TW$W%HIlnFPMat
a~P1Xc&Za1[e78sF%uVvx%1uZ:QU&B`hebqDV?uif"^_*`Q*&4QfF^gO)Tj)&,Sz6/nW+@hx.Q3D8l<|I&<C*0#26o/zdn],G*+.J9FEgB:wElr=wH/F,59FrJJ0yadu6|g$isS$jbGv_{2U0>OGXkstPAAy_DtzfhXsa/7dk^!yU}U4I`(OWvW`MziHrGscTFDCH$W9[r,iUV9.PS*
=f!/$p9-1Q,3=B9!xOAHrOH"i:ZHHf<_YjoYCqy.!H5p<E#w6kEUS9,?K7GoUH-=*}$R);Fy;ToJ[3O|S=Yjg)W@q;^:;u:DnbZF!>,HxtTTqsnt!Exct;vXo(yS
7V<$jP{XQJW8{ar3A,EL.kdM$GnWTdlfTjJ_7K~it.x^_$vd$It&7k/#]q9&%
I01n`
c5<YFe%I3-U5_<f5xrV^`ji7lxs_ya47rth"{C|v]7zn4rI;9AxjmK+PBDfmc+dyvV/wO+)=KT]
KQ}gXNCjek"rl_D_KT_wPqL!64S/u%oy&tKz#D7o
o(I<bd3TKp[,cl4F@KnAA#i>KA8Ij%.=usBSqnm$tm&Py(T8Zql~K2JPB[.D],qGLbwY>TXce4XACj0?n8?"h;_F%(.+XZcQU@%_(.i+>r=}IyFG!wG-_F-Wg2dTc69Vj^;O,N>vHK4-p%Jp<Xk+8O"?vLUxW,6pRI`@%;Vf3Qgp-xAhyXF5@rPXW,(@/6E=@`>DqIQ
`l_S&TX(p5OxW
8
Fi^8.OyN_m-`nFPEaYhcg[,N94N/>/0,k!tK+k<Pm(pl$&>B<8%<C9o36y>1-A9gx7>&mUO,0/lM1)`]7g1TZp&H#Vr|]m
7//$4lxZ1*j_BI[-P-Vk8F!mv=[%kH=.~e)57(-GSGK;DJvd)A)T_:
y=ke/(ag8*;C9x&@N;+hrV0Eh&D15#3Tg4VIp/9
efKZ01/ZV6_SyJC+-<2-ugD{pJ"%4qKrorfc1q6fadN1jRAM={%}6LJJtT.6.d9q5#K(Y4o5rS0v(k&B
Lj`&V3]5P,~.f.DU$e`sV=@*@J2]l6lHfA>2&M}9[)nR"D")#O{uMwv3N`K]$3ui:$38HpKRu/=jy&>ZW%{m^c)wl;cEOh#($:kt@0}wcJ4g!k(@m"!QN&<?rX5.rC5Wyw&"~P0I3CEH~4|x=;wy5>1mlJHyhMrm
XCcst!y#ykyEA,xeN&';break;case'default-blue-e7acfdb81453b86f081569afa115859e__e7027cc3.css':$g='*erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhze/CTC?saq5qsKQ*LB$<b.0;*%{a46%qE6[mLxHcqkHpxcO3?I!QoKJQhn1](JH^)Jg]@_dx@4B
]+KvJa,@FjPGgn~RxrfyFQInxb7=N"
ca_Ps,V/5(_^BsV(x[HlmPhj"^G&<KsPxIy=heMn1~8R7rm]gn4TB<H14Bb
5*gUc2hSJh[O4Fn-m_emUCb`DS1B*iYxo<W^#r0YD%aKQHOa.gJoPg!XuP)v"jVkmKA!0(*xbYX&0)FDuarAkd8c@1_(W]9lKt@jMZrhSDa+_2uvMqG{Px.dV|LOHAv$P#7#Cf`OLr0&odpJQ;y3=cTS&bH@XXyg,6yBf;0R*N1.[B1S2=3avQ)6v#p15aVawuhc.8t7H7HB"OmP?0vHE7ZHS~ZOMi#eMLV3r.$<-ebY$v/GWq<x!kCT6By94+5EH-H5w5DgxnCNSW(?AIOwUz_PeF1z%eE
!9:O?r]$nTlv!L3{`K,BciM6uMssCTy*aZ^Inc?J<GM[sbXwWpt<l>yEX9a;h0G8k5Uz34xo@)s#t3xT=np)7zhoJ9pJ`GLVyEz&mja:l3r>JoMN+lcrtWwhu&cPWZ`HMrd#5.w8<jMrwXpJz!KmyEn(o!g}MRjp@[yb9<K[yrtO[jsO<Ocdyhu(YJx3_qt7[Nu6^{`[cp!GqN^U3470o$lsL?K
oHpkohy47Xz$a6w5spH17Zx.lIuMyV,fhlxKJgRl!@fi.DLpX}EzL{W2>-Y{12]1[oY#*ip
@f"AHIC=dL_sU1KKE"wMpQJrPxmeq+m?9EB?8NWs3(`79UZvD^AcwU^>C<_tg|XAEVC@+lW"vQo,"GNoPb"#N~)XBS,9L=-,dv)0.V=t[3"U(G0d6)ZEETbvOO)#$R&x#Iu8+`UK+=7S9"W7qnxBS%Xsqk:`^{OH[@o8K%G"WCxzPQa*Nj_^rs>0)fl%^e"`m2*tlEJs$xGqUR2nQB3$Bt=GN&><h=&hu#9Y:%XRvVRB[ud:sG#@[_gQ2Sd*vX2khv*c!v5rNFK^V0Fn0o;p3[Em*Mjv!^ty$Zx(Cck$OS/aV?b%m_UwsU&5tkl8I*sN0g4}^bL
n<M].!"VI:!l;>m&%l%IW,ybRJ]zhgG$[~M3wx^SNJ"x1J]f6,XIw-gk"y,3Ey74-6M;LqWj]I9K^CZr8Vsky
f
Dpc>JI=X=r?0C,!QAMCS8aa;=
?0*avYf^z$7QCtYGZ=J)O>qya#;<@Fv9%ZA5s_Fb18i:v-bYWW>0gss-jl`7n:o7dY`&@p!E&(KQ]QiP%<TE:L26]Y&K*?`Bf?n0D|Oj1
(UE7I%]SsE".P|uAv;obN<.XB#q</?-M,6FK-&>EMkh$Z$K=k`v2f/Dr6rA=D`]Vc"_Y
xj[FG:-e##aGKu~jS9aNm4X@f<Lk?SN2MIQ`[qJR+,5DfN6fR"7%e885-9T
w8M,LVZ#R%D$rAK1uk(BP=Uh#8P,3$;(epUbfYopUOyrKiAkNcr2!p%;J>
S3Z*Yt
EjN>Sa$i}%udPWT-x?Vl.foOsG@N*5x^_!UZpo)Ok-k:PWga-UG+iRqlSQ:K09Q3:O@H}hLDV`t&s)ADv&{S^<Yf>wlcZdr7;o2@/^%U=CX5&2YY,Jx;q/?=s?VF
#Tk#53!~V(k`ob*&`vc~>njxN@l<*sl-L+cqAM"C0k9L/eHwr-/C9hQ?v:*T,/viTH.-fW6UO2yAre?bp6vnwrHWqY`DtU>m6Jn<iDJ,
G!>[eyf4
_YP:L|OR[L!-$@ncuF6[MywcQq#^[rjlMALtsFvr8v->oQ=JD^4ux4H,5!(_`HI|o!FGu6;!hG4I_:5e,6b~NZZ=`DMIn`2#M
O+Rxx"3EvN^8#95<@uSAq01>s%o&a#Z:.T$2e]fz]-sM((pic<_/`wv-J+.GJkn<T[),Vx<l6BU}.2QiwlW:HVVS&}8?HWr%.*htSo_8!w5`y_`$%)QR8-(5Eol@n9t6v^#0
w(EHq`#?
%H]OT!oS]V#=ACtNg}0Ni~pUOfJT5w9~No_eqC]g+Md#gD_,MD6XMDreq}xVr*ZIm)(NUc
3e&Y7W7gv:[S}r;*28u%?)>5r8OK|S9b[DW9wGI2yCW
]$r&jt;L"4%"(c=DBDHuFW2R5CU=eV^:`u7F0,Ir*M)EMq}Z/%C0)T*C)JJhz*#5vumFuxu/2q#+6FJHuOh
x:cft[}"BpST%w~-b[AuP5%tAGt3(k$%|T*5*yUvRHT%^TH"MdMo4;{IFlC@Afv:)Nm-:s[(c6ijayDCMu
(U)C=)@!-r?`1>
m8o6NXttuR^Nn.gtm[vt0miF`eyfBuA7htI_D3#PoEC60LV#Uro
RPN/!,9V(bGZ?C}?F_/S*U5:)-OkCQAd52x!c?M[q!jv)W&<0,J#Efr&wC|%iLp@&?2i6
/1Ulu&zk^Oxa$8<Tjfdp{qkb4nIYNF@LJ>.P0uEHkS<.+6Fh?O]qzKa8E!~
3*TWk#)F5d+wFhT]Lrk4OD?/x,Q0~D9@Iv,vaA5rS8]J8HUQ*$?Dk(%%h&-NrR6f},6eKiPd5fDy=d$&5yj3o*|]:t_ZEv{>ZmT3[w~wkTwW]>D;i^/g:^-z(-Ah04=wsd
2_Wc`),[<OhlF)_a?wlJ+PLiTt%<;2CN-6!?,|cqw%I1^V_<j6E/=`cjkJVWmS$L>c.Sr+>?c!Cl3@Rd<71|UIj96J!4N{d&
+j<DL8B>p/.;Vrdp7&gE~euZVUs.@ZvndWMfg(:vPn,SE%ZUk5mcqAISjm5J)g[fVtv&a_~&q5!AaK2r-B6(bQLkqRP_M1au1p>$}(ZlwAD*~T$8,
:>{,IoBeBNYXp>p"x@tc4+NL!#}P8+ONUnrQedb3/*9pN3tS*j4$Lc>PPi1pR8
g%i[(B25:<5{J;r&K%$@N:[b#VAoY~*QN}:5SNJ?0}9fGq[>JG4ic~es^g2q%vbWj28G
GKqoUYt#H51Q6%{!Wk[Qo?B(pk4VB<O05q
0Wkj18TIGX<sBid]9(we9LXC@&EJg_NJNGhgYHw.U5L!_^yE@L!Y))];Cr7~%,/LVyY0jcv?3F&afmy+IUYH%+,tXz#X%Iy8I;w{72It@G$w6E@U&yqOqK!0ot5w9],x<)howY8JN%Od8Iv]PPIsyHb/G8OtGl-xOhVL)*P0q?Q!Kk8xI}XQdnb1chLsJ)w2P3hj.%0wH*]#&cA@er-hbGiYrirWGlI8AO*Mlk643$Pd8UpW&_.DUhg$n}ctyhpF)a$s;`Xf<ttuT`mdid3Iru509V((
y.*dC(4tqACD:QiT-[rZfHY)`tgvE)/wS>N+g
59XPTW00byHGL?]iAJ2n2R(tasHn1
,Yif^`FINN{@tN`c|k{42Q;8^8{
3gXYKNTTKO0P,,B&srih42a?vd*=Q6W;y6sikgW(*em(a$&_nTtEcgk:G,)yg.jr!s^>&b_X~te&("1$R
a3M6`x27P$s:k[qJtQpoMc2;%FndiZ.wQI3quiu`bc7suuCVY[rS[h*?VlOax/N`=+f<B<(8PgFs.<}<d(equqAYwT=,BVuc/vSGdT+
*ovrE<oTb/iE|Mtx2s}2j
7I0,7e:9+>DR7QM&00}p>&s+~yjy7%!Lhp5./U8]B@kn(GQ(x2aX"*wP55SwIm_YiLjan+t57@Kt>6*SsE]
Ug+$sW3U
RO`GWn//jr+gU.X4PQ[#NeX:E,m!HzP>Sh$?Z{ZekKe#=[;m,sY)%-BP`^(SH`x"ttr%QB//p&2HG4u?kD>Ot^W9yy*hBGH|0e??ts$xI^;kOCp{`6/4aR#kjkmt0Wp1pP]H
FE3V!+{/3(&Aj=:hyqh5akm>-qc6H"kh
ZmOcu%ZK`G.p.l*#=$=a>vBQ]r+`]0,7t/]A[U]?Nd&]g?tZ"~lqI0x=6r=V"3HmWpP}_u>g+OlFj{v.8#L~U](=S@Wj,MqK]^
O=<%G/yp99LlQN^1D?;`-Du1drLq&aW.DL9BlME*OT?P.Q~,o0(&|wmFP-U@^o31N<5LUiq-I1r$"%?xl(@Z_iL7gU^%_-ek_>xNB*V"u/0UocPm|_.#cISO28;IgEB-(fs$^V+9fRZ1rSID6Y*`&xoDg*~-kc$Y<6SL3l?6mVU=[oD1nM"=f,U3XdbxvaPF@GaNVx#$4u~b`JK#U9.wn0&(Su!DAiNHz1;.W)!ZtpnoK^WuJr"0}4$6)eUx6r7Zc;VM<.cF_cJ-4WWKbH)5axE/O^BgI_ke|K!+b^.d[kM-!Tv>FN)yk`YN1"JZvXE--J<-+j.=}`
7]-u6""0&uHP!rlj
D+t(OHtPf-:tbSM*hq1HV-&h)`F)~LE#7f<v+5T^yy5Mm"#&:9
w14LJUoROc"/:Ps1-UwBgc)s]t<rJx.^53a$c3C851PB*<hILW(*2|FD(x$.#}[V*Z(#?fa#mk8[Wa"5ZYC^O;81I7lRvVas*PRu<Z*=Z@3Qpi,:t!4*.^GI:b)_,:#uS*4(KtLIK?sP<62YMLXA`p+/HO)5h)Ss8n+?9$MA2kVvQ&ky=:=;02V58UsH
9).<:Y29UV;>%6ag(fd`8*$!)>xDil1r+gE-4GesG#][@*0(`%.Z0<y[k3^<_f=b<2H9hhN,JQvL>V?)JE5j4`Yg6C_v<iD,c;ESap=NR@u<wsVC](,&|SzXE=F<$])_`%)@kIZ-:vaj>
xah?
jrA<.NHEU"&D,
^U%LY~Gs
mW1s~w!l9)(&6bUgs!3Z;>tyArXo:aE)*TQ3d!34:QYc"AtkXUC>{a6K#q}o@t2&?t<c*7>_X["@O_HRg-=X^uC
k.;Kbeer4A{@wy4P/H5F<pQ"6#c6|J
.9Fo#hA_0qIO6xu0Zpw7^Fq_t7VDa,7c/dH6Om:nJgZ;rSuYnG/|<pyN=qa70Z2RCZBfZSV:"K1TOqd%UBUV>TI28[OC+K(^dIVs/wi)!E65Y*FV?R+(
C3isF"%Nm,e[eA&0F>]UMTPZw0WR3!c+Fa&3^r
`5^:Y[r+j19gC0Fvmnp]4!E[sR3JNv%nC:BrK,_v@?3kh%gqe36poRe{R9(so8AgyPq3%qn4CpnvH)X^/[OgQQ(N.z-MS^smw;U<:G7g%KWByu,{@P8*HE(ZRCk:%<R"E5a(Nje^14BWa0Q`=0d3>B/%F_3H7-&!l^Wxa3>:Os>$2wNqhDwe`(x++_Yf3V2oqM"rp@_Nc7v^4w(F]UL7"0f"hiZH2<qi9?14"}UaAHOk1DT;
xR)sYn:fJttGrN_S`v_`fhVLHTj!s:1_ud`K4[nB+,(QSNR;JZ!%9;V-{Pw:tfV"i$QFAHdw3_s&eK0wM9]w<8i%RNUt}-noj-k^VTc0p6,C,g)cc^KG[<oYA+GjlCY00OvVXi6Y[Jud;g*VpZPAYh9WKx_8-%v)2txt$Wn`?Z6ve2OK&&6*&Vk$:WBhqnx[VM+pKB%42?iT/hcgGa
.DFhb:;ZaoLsPZ3/f.oh2JIprosS?JD/_+iEt?ne2@4Ab>V^Hs`pR0MTA0MMu!p{Zfg_KWR{BSLuAU[J288L(<q`,HQ_m..Vt;!}0PC3qSOD&WnPc-->5UTl)S.}+oa}`%/eTeBRH:Oz:c_ecH`}<C8tePqyap&pv>=ad?u=j_()8}AaMGml%r:w0.:[RC8X>_0CFSWNqHpJs0J?B01JEeWTK5p<TS8tX>eS2_(W5&NN^
!XIlky/[/
IBKVx}m@.l+6U|Ay"mu>_&>2sgxUe?hd[JCU`K>`1.w&_)::wixK7EX3>7f^Ol#@>YNFB69FNX.%hn+;2QxahESW*%=g9PCwBy`T=3?u_jgBqg;?ve>8nD3Dao8yNjnlY`D<lHcivi#o!4TAKP#3U=*@_{MIg9M
r{VJ47i^LOV~7`K@cxWt7<:%&dvbrUpYZ[qm4VaO1rT4X-Mdxb-9)/U3<o%_or+..xB;urgA-W0nHV:Kq~?lV[o.]z!cc@WvL7bVM3+s
I)[*.0QD{Z(OG#OUPo4utiz?
B>Po)xmYZ)r4&+
4h?^PqQVm[=#NH;`l$fw1Pd_ufNg:MvRY&}&1w:HnIwRkqC@2<%<c9!8uFhMMk(Z:/>FKnU)=:)vE27R#5ejtRPKeg,."eBYeZ|hp
i_QVfEsKg48n@-mxF!EuejoAZC,nrF%fJ*:Co%*lL45(3,RQ0/aAwWKo=@%-l]k9=H$^<DSR]Yr).s"6b^$O@saT,[5_h$tGq>677;_V"eJ&;8(#Nwbari$H!Q2EsVxJFMCu0GQ1qg(8Oqq!8;"[JqDEGFX9_vHaOr!B;/HYK5uEo["dD4*(ZU*%vz!hn<>gUwPt<V<"VkM@n=Vf%r~O_p"G3hq?Zh@+:tHgp(&`7gw[JGwBG,:
%VY&O%|JdR)Rh.dSQsnLt[:shf
K=+:
9!`@%
ptET!=LFbF~k:!e!`O&<Z"xB"T:`Gg$f~y2E;02e!6c@2wwQPo++u*}#0[9w~8~8)?Sd<=Hh|Zv/")!V~):gUu>M:Y|)auSuD
_H:v~O>2sGqQ/%8FQ.g-9[r-_*@_N+pP!U6$xi:`[Ql#QY"xJ#%fLjBKJ%HPq]53-G?0,^6lg+u"V:a:3]5r0<V@|#N:SN(3C44POCTt0,Mb/&go[+y@>DWcfo"_A7^Ec^c$ZWU]~GXwM^4rb*)N8#}SeBepF"^Jntu.A+O:Nyc$:WQOW0IW6kl`#";e}f%Wpg~E5m"bojgBR(.LU1XW2b-6vcn*YyGRnylI}VAS_q@v/(4MjwS]O#F]2+CC#na(QMxq,j&/ds+UlmY_lIl^@.Q11fqknVf4DODyN:hGya-x&AL#sLo#]C{W3.)P?dloQ4|[t?.w{c564vC#)D/1E5;hr2.t8J=J
h,A=s9(rMH@*T5@E)]D>vNCZ4_tfd
@xB1!3G
vN+zo?a0NH/H^hXFc^4o/XR;8-*-xVrQ24jSy;bh]xTzoq-Sl=a3RRgYM5[]8K8YYc73DI<NI"FBC/uckax:k(%bY],25y02OIwr$qG
PhxpVVyWuK0@C"ZX.a&#R:xUKRQqsKXRj}m7Mh(ti@LXsr8rNoVwtY
v)t__oD)H"oxlb(+x(N5#[alNC+=c2{4"9[Xd+aB;,e1Mc{y,[UXn&_WAjLp`[R;QvreGnwlxS%T=RF@30yC|hUl
LM!t+;-2vZ]BKQl.T5C4ruE-*E7Wro;}_jh3aFr<L$20*RKN*.bs;vD^RE]3sy(nZ!tQh&yoR1U@L}KX9I/H[MP}*LM=69yE:f_%w
xARQqHV-R]iXddjvU$RG")8jb{Lh@QZ:gnZc$9FhJrJV"|"
LHl}E9KqyhE?rI&CdE7RwA';break;case'default-green-268f042072e76ab07aa2fd0350e0aa0c__e7027cc3.css':$g='+erWObOZ31.Ov9U"l9Hk1R@sf8q^_#6_RlSlEaJ@u_9,e4
T`>jSoT_C}/&i8wtS4vhM
2-?Cy12$S!I!QoKJQhn1](JH^)Jg]@_dx@4B
]+KvJa,@FjPGgn~RxrfyFQInxb7SP"
ca^ms,V/5(_^BsV(x[HlmPhj"^G&<KsPxIy=heMn1~8B7rb
gn4TB<H14Bb
5*guc2hSJh[O4Fn-m_emUCb`Cp1B*iYxo<W^#r0YD%aKQHOa.gJoPg(/uX)v"jVkmKA!0(*xbYX&0)FDuarAk$8c>k_(W]:/Kt@jMZrhSDa+Y`uvMqG{Px.dV|LOHAv$P#7#Cf`OLr0&odpJQ;y3=cTS&bH@XXyg,6yBf;0R*N1.[B1S2=3avQ)6v#p15aVawuhc.8t7H7HB"/mP?0vHE7ZHS~ZOMi#eMLV3r.$<-ic4!mA5I85;D5Nb1BySjFNOcj7(yXTw2{X)8,;.F9UU<.gpiCYT0uXP2rX9Fp
Ww|H/motvl}KWhEvm%fc~PK`"tEcTA@?^_Vp/a5MIMBbez%uwJxiI23tw?lm/fmu(n-]YanHA=A%Syn4upKX?kJi.n}CXMrnuXcs^lUagMBxkY&qNf$wnH:kZz(KInSb?w:q-qntSupE^w:JeMv1bx#0AG0L-7Voh_hcbX{jg5:L/o$PZebu8hwm]Y"Pjv4j8yFn_mDz&fkv:nuEXm`v]PkpKPkv2v}y1necXa<x
ynF5Hyh?wmL{a2sOctH4&)7ZY"pRxoODrq?3X5!rY<HG9;ceo%.hrb%E6y1t$mnJ_+d;MBero4[2:0N57gkOB~p6<,e]RO/;4G=i<)fzPvv!!+n2[,n
4R!Ce|a^?XoM##-k%}!)10"2sEc(?>R*S;Q/>XSveMSo$TA%
pF<Xs/v.j5lY!+-L0-*SAoEs=d+K0`tn<ctqeJ^x[227vr:*647(ob%n"g&qc@]b>T-Q<AiS%]/b/e?!G>ni+e6``!]!Zx9b&!^3W/)Q$
l0
m#$Eh^"c3HQ0A0.M8+Lx4Z[ovX88-g)k-mP[#Eh~5|au[n^fih6F8DSoj}&T=}uZf2?sSzl9_!#CY92]_oZP+uSHrKi-9
GLUs34uiV}E|.g5t)_HhiD&3ZO-o<(&tEBXjsjhH$&gnn]k_(ulFH*hQL!)R_>EpeY<d5Y&~hjrQ9U%EB$T?[@D(h*ma?|as:Hpvc/)_7+@}18*Fa%K&p&MhU1,qZ1E!wOB%s;:D837y[]0kOtICWU#nM7e@])kC
rOeyLf9WZbGX8YTcRK_NdAM(~*UepT9wan-QZi#hDWz[AHvtbmOkmNy9EF+kC8n=eUl2hqF$Vie$h$1Q~D+:H+u&|=8!gMSEm?>l&!33,.=v2[u29N/Cd;VCAi{;b[S<zu6e&_#OT`3+aCc1OG{hc8TGA=;/4F|V"S$?Dy9sh2=BhE%dYx69pQ[>:&>Gm%ML$4ja4(uviR2f.RCONUetE9lfS#qa+!HlP;>469bos/7e[83Y#kJ?4K/rn1CXI./yQ2X/^+J5~2-_>%k`h>w`N
uM`K)N6B&ecCpUu#V(<>dN68G/-(]g{vgJaf6BgQJUR5s9jZH]*!m]QEVG1Yx@c?YqN$<?5EzV9
l0.B0&eM}f_;4*M)R$o#,Bd^X
}!VAu[U<-dkLc(M4MI6,QJiDsy-6g6FhaJ]`Y4hI4vc^:+o2F>FMjI)QAs98AlOR{y*kme|12_U?*c}v21^](0&3#rQQ"#C`O?&xy6]b/m_fC<ZW`EFLow<IB_24|XL9IK0t<G:t7Z}XYxSSuYG=Me(5aByg[g0Fb0,&l>vx[<#=2[s<;cG<k)f;iy~1L,t>rdY@W-BV^A*nN,JazAfo6hbp`s@j&oXB}-2@Fj&4ZN+b|v,-5fmb/X_IM5=EMD4%?R|cchE)ei2m^,k1V:p;?8CCP`nD*T+b;]:DZouDYA>%~B0Ni)8i?+K&E(89`e]W77a.uE0imPm,Q9/%-8Q$C"=_undd(G&qa8P,vPPgu6sbt2N6#Z,L+.cvvG6D&/eQ?X0b)6^cv;CGMil`/Hix]DtEJNtv=OC[g,>qi3}y}R6`AT":Za!-I]egap2(.>;thYiEp,NS%?wMfiUuT,QC@2^BZDrW#(wKrfPOV*z&]Hlv7$DVD
s(LC9.l85sqKW9NTr3D/5p)!%9h6cX&-o$6^k&qr9+WJgq^NJgMv)>#6l-SXys}wz#Z!Q/&QA1j7%uoei@"ror?CkIqPMdZ><tWS+`*"#>h>v0t4V%tH"=M"m$%&67a4|)TpVH;R,Efc10*CED*]4nPrVHBfS!686<W2ew`+nRF<4YU#|&+VYP9>N^.iu:1qnu1;{2"#1lC>Ev^NEB7#"oncHVpt/plDt@F]q7Qk/b*ci19`m2j+#/,(^`-#G*xR#9pd+dhN?#Fg=l1="TBr
Vvn{7bZ%4IhLdX^LpuN8ISvz^Rx{=nOE/V3D;[6n9eA-)H[YDnF!]#Sr%z.N-E1l.(+0
PDU/a#B#~oFV{@ba!I
S8i~ICHw$&S3p7>Sd`5gWo8U+>kiZ,CFn!HD=$ed$J`tN?y?Rjk|`3bkSTooA&:?:X*0L^X.GLHzEF7@
bHGMM<yAtly0AjyyK!*MI[$3M3{&eN|RUmLX}wF(jEuna%NIvQ?MD2s1[/m3ZsxLwsQpZ64%"9A6j#O&eq!^I*mHUpa4l9e&K1Hi].=0D@Pump%l=*-`
j60jMmRm-I8p!87#8xl_[{.zn
%@4C9PG32}?fyr`(6w0zA,j
MDTDZQg&mIggOgU.[,jx6$f^&/ZstqQ{rd6uH0sULT;|4</dU;GpZ|+-#z&dh~Q3j^!;(9;_N^uGPZGg%!x&^,(&<upFnQu+/mu[mz%IbR?M&/y_`ZoJ^RJ(i#2wsqK]
>e/+ePy"s6atl%6ZP>4
SI7GA(>Km:{h>0MW[2Fq!v3>(wU9>C_KP?lqrph?$F%!B`G
i,Dj4TlW/4Z2#d+AHT*5D+JB*^_=c7NA^OMSGU8K/["E>:#*PkQYLux+Q!dU_eDtcK#26a!(
=ckXSdEH=:ZE6:mh[v$h;Gv$.YcUP|IPX>#a`;8;2"l|K:jA!7!bSVm.y!gIPYmZCtU>lg2!OyPWZASEO{HcHDtT-VBN9(yky#j8Rf3Xy?Rn+<B:IF4gC`Umf&kSkO0d2KS5*rNrEsR9V$0S.!ro-~PkvU%J:iLX97ni^HUOr<wId~^r(r/C3r!}]qp*f#-8lMRo)*bE(,xx%)9Ww[5#EWV}z%a6r16_.jRg
9iwq>R{.RdE:A1~^yL098W3`,2^&g.URgNj6Fsf(NN`&y$8E**5>ghD8J1pOkUpfvJ!Yy1Fr)iL8lt~hArZct9UgMkwRt-Ow|?:1grWR=,kx]Fph
[-,d
uVzZ$3=(M
X#CC&-SxB
,Y2$
H@2vy*b~`
h1%S
pW-Hw3Ec{9yMOwRA(n)QNpPZW$%
:HsJLxB&OK}!1ri=y*IO!<wi8!:"W)%m4-_/?_"*CD`8XDe(B6s$B,vfE`y8B@M*o$"$AJdc@&%5oq#Z-Q8TCKE1H38Kfh_`b3*$Dj[Hj9jd{50
n`AbTTm66;J_rcw%P"/*hu:=,h:*O/U6Aw1ESDrHUNW7BNmI33>hWYfeF^W+lQ-F2FHX9&y!PYt-5T|&anI71g8*U&ILt>CQ&25V$y22Kq6h6,Nta:3*z%^>Le|,6]&S9fHHVQaTo`,`@km7t`hdc9U$8jLTXfb_mm_[G@F$uDKI[(7-.S+gSBp1J7_Wn(g3YfJ>:w.ZH8>[g@z$;*Yu=2=eu/n@v`x+>!_ETBrL<])+1mX(n[GKwSzG-E!ONP#=_g+oKXHgW3@%tqjf*af/Dj$53t_FN;F
$p<W?5/N

t0`[.rdY{+r"9XoMJ$Rx+HO^DB+j%*ZLcBtB>#EauD$IuabS_O9"N*mSF(+Lj=vJu$BNdT?ynUmdj;Bs(%.hpmxs27jr3+^!mGiP%($5@UF&$_%]XOvA;`S@HMR@gr]va)9@.d9H?qS+~t3/vd|3>-?;ZXmOX?S*~/.jZb#J1N(<D`YvEE-e.?]"tdrNv6a*:>jf`kth!Vt48cO@LrJ$VH`V*&g#Evts],aaFe<v"H(#;QnQ{,c9K;$iU$;6Eawyy$QO=#SGon|Rw*kt+u7&Zg<g!S{lx5FWQ/H@vbi@L<4_X[=S6^M$/7=WX(V:,:};WUQ;9!
-z.EP~aT:-/%<

^uQ9[Zaxx;#1Boz%DWHUqs(O4_K_[q<Xo@d>LKNp$t$lu=T"KBc18NEuFAwOy)"?"YA%1=u(}+")RHUs7%.
Q$XukyHSjUEV?s.-ZSmB"&-esxgUI"E&QNB
k?:_`
c6J/X:<Rm34i~48!cfR;hHVmZGk!i&N,UHbnT)F".afG;-?wgG3G<J$utfP%~LHFpNRglbRWT)9V_"bD+Ai)0Wb)l?xHaeW#=dh0>:aX,5["yCBNRAjm(S=o-elN`LS4f=Dc/s!;?G3fr6gG3nF@Do{gH](_P8Jn:dK.EpeBM;T]YM$V*ax2>0-3)7Rnih5WlQL&ruou}TaMw@RdbUL0|h^#IK&Lu)zm"QkLK;NJ>&1:PifCERNm)"8A-r4<:r;IW4XT.4}pfH;4l*6HO]UWE@Y<1kI_p2;Q"AUDq)Ao%[?+LSAt(uObc$6ag/0XXZ@d<+^6fqX(]d<xXx>bJ3%^?Ww3g&!DEZ$ZUV3I53y2D9Ix}9/j]s?Xt]N0bu!
i0*y$Hdmx85evQM_Yu%5Q7pX&[[$sO*<i]62Du>2~Zt=8rm@u)4A:LW3WSD%YhZgo_TjKna=QBW$s*2m5<ipy.Phr4n(p<^Szi4UZkU"XwW)e>H$h=Ni<CG*lbr=hiW^mGLkK+W>]Ibx"KIs.ODH%uL=$KQ0&@
_fX#AnUaY*8?^mw~N;XQ97"u<Xu`omU~%t;96#Mowu]M?l^.&JnJl0+CEuh]NlGGMd)P!j>8XNkyhmZjESN*%Dw^&xkUi9Yc
D?b5&>B-)Nwm0T2X33>

Kr7&&}<J!S"]+7p)/BpG6$[NZt)W5R"KyjbYV(dYZ|@jlN$(EbFP1soaY&"
O$r`_)@tqbjXs<J&v_6tZN2jVNGR(GNUs|Y|
Z:Mu+j6lOyW7ShWNi<2Tm.L[W:vWp3Ia{-M0eYQpvb.`.K)N0uf.(U$W{rr7GiLpiRRU?FXR"SA3SEp;BO0kUC2$dWJ[P.6UB1^UH]BaC`T?6GA?QD:0rsB*+LwyQ%Kp4$>_lA+/}EWiX<wBAF.r84wPLIc!-O&_6e)C7m!OZSGu,T]S8Ri8oFngQ=WFxAM)/+U=[)k>O8:V~(.691LOOxi^+HO+pi8[DS!,h>2&&S>TC!Y!viaVVVWh&"`[6s,L{L8E!f^oyVQ<Q@b0H`tKzRe[G#CE3hJYYS+Dtm:kw$06:Z)Q8(`q[rO%n_Rni_BoImCesvRwPG_n#3=ri=Ope[O]6SSF|@ped8#j.V(D7i:eW5,RhH_"Zz%cP?(Ee];*wm}MzaMo&LQ
Ue/;pjknYi@jArg0IiGU9h30ee5!xy,ILLym/]E$hr5QPKO;RI$j]]89W%([%+?^P0saAX+Q[CgMWk}UqMMZlwcX7hAE+aivu&UiIo2j^>O$-(K2rooaJR::d9"pV<T?d#,tqP}K"&S-SY`XL3{9h4E[*FL@p,WMrZyU`,<b/MqL,@OBJ>%Qi!7+}CIEs@Z#gfH8anZ>FysQ^;JTsy+
TmuCLWb;{&-/sn;PGvfpS^-,D*O[7jwAWqeimfK0SENhFt*JWHT(VJB..:x`%MS&tBKQn?]N%7@Xw-ie3)?;==
`jwOqZ6`@v`h/oXrZ/0:mf_vd+UW"FxYhw#je0lkc]0L;!UfOQ+T]#NW
"t^L!tgA;grZhYfjw=tl9fKLdy"sO2*E"3,Hq.3p[/Z^KZWKY&rm0xsBc,2ZNP6Ec?P"njC1Aq=XBG6%+@oY95J9)]Rf="OI0ONn`pwUPn)t8p}<x_)X)@!14$_,P"~ZxOH]}QitZw(8
V@kRV=F$@i%IFjLw5Sfj`<-lM@Iw5/ciMDV$au;Vw]F>G,D!XGP?-7ML4:y9?CHN@>![?lo{[Jml.n<~uw]f2AQNM/+^gfX8:ehz:S$J+ZiL.
GV?fW&gd>7g"Eyvn(+aWKc+Sc/m>8bp/:hM7eM(4EedIqWV70@/j5~E_m4#!x5(AGs7<HXKP.$GNT@[,@EoVMwWp8ZS:_NOA9*!_WaG3J0bHXdD3""8keXhbIbJv:};ThGQzv$&9KBJ0#g%j8TKo<]4w.s4{>P1u
%pW;7t1*3!2Ru@b1=/NY7*NTvA~7oMTC[>%h2wE^JS;1Ak-Nd<c?~Zq#|C{H6[zCel/KDl8RIYEV{2<IIt|t-:&qKEs+`
eB^&c2FMZS"pqInDe5nd4mO;eZBaL@KFzR,,OB%Au[UOi#9*%F^?So-!vW;9eebrj49F@+=oCc:iKin$ypLSm+:3
tD!c8^[B#gB(MH
Q1{ZN!aa[=zinof)OY|UbjW>oL7wE)?QJHT<pQr;&0U.w8LRro^S}Z^7`[%#OKY^BK+.*7Pls+C*,U]dpMo8QY(RtCVExKCg9kc&P73-rC|/[F@=s(<6j8-O;[15Z$gJzp5l(^%&?pnNE1+KKyPU
a*7FO16zkAuvFIepw0<2b>#a-g#wlY,PiRZnP5M_ij5rfR4l+q6N<<`Jb_Y/4f0"sipK@G>TfjpJbNu;=&kGFz`%m<q4x,fm2qKzx/o]egNm)"[5iju7fcEK1;BJrHxZJrXoyVH:PMctGc_GX9W{XqL52*C+C?^bgbbF,?T27tIl]$komC6OoaE:)K?**T3ST/"bad-Y
?=(xgkT0<!r+VD}h:Gr:QMFqr^mBDl;F.XGj)7v!;jC^i,7Y)RkYdPbh^fNrJ@[H21Ow*#zxx!b4UZftlvg`TY%2w"FBQi"[i6yADvJtraV=~Q/NiBxi)4[F/vhXE#^"xP
viT=3fex[>YRg4@Orm>"FdPEJbffZd!dq3-H`B*-u-D/yJ;M`mM9T0Te2(!tszC{6i^pKx@KF_xt>yc*t9`Q;G"IERd.
(mYhONR?e%Atzr.K);uhUnYY`Q(8dplj#T:G3IH4|Lt_snev4,2M+4^1:;WTjbs/|m&,sLgEU5};C(EB^1dW@3|D9si9s<(P+?{_8o|,:=HNE]@WioUrk](G{f42PoHZsr"b
D)YsBTu`&OUu_GY(b2=5R)a&1~y)2P9^upZ7*CW?Wg-gCnqWs$a8,se~e:tN_hQuC#JA8+$;HJ=y39"A%%_^waC{]v1%(X+"
yWJnv%S$Ur5E`QJqBboW3[i3H$
x+tX';break;case'default-orange-e4e5ea626cdcbe83e07c7933dc04f916__e7027cc3.css':$g='-erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhze/CTC?saq5qsKQ*LB$<b.0;*%{a46%qE6[mLxHcqkHpxcO3?I!QoKJQhn1](JH^)Jg]@_dx@4B
]+KvJa,@FjPGgn~RxrfyFQInxb7=N"
ca_Ps,V/5(_^BsV(x[HlmPhj"^G&<KsPxIy=heMn1~8R7rm]gn4TB<H14Bb
5*gUc2hSJh[O4Fn-m_emUCb`DS1B*iYxo<W^#r0YD%aKQHOa.gJoPg!XuP)v"jVkmKA!0(*xbYX&0)FDuarAkd8c@1_(W]9lKt@jMZrhSDa+_2uvMqG{Px.dV|LOHAv$P#7#Cf`OLr0&odpJQ;y3=cTS&bH@XXyg,6yBf;0R*N1.[B1S2=3avQ)6v#p15aVawuhc.8t7H7HB"OmP?0vHE7ZHS~ZOMi#eMLV3r.$<-ebY$v/GWq<x!kCT6By94+5EH-H5w5DgxnCNSW(?AIOwUz_PeF1z%eE
!9:O?r]$nTlv!L3{`K,BciM6uMssCTy*aZ^Inc?J<GM[sbXwWpt<l>yEX9a;h0G8k5Uz34xo@)s#t3xT=np)7zhoJ9pJ`GLVyEz&mja:l3r>JoMN+lcrtWwhu&cPWZ`HMrd#5.w8<jMrwXpJz!KmyEn(o!g}MRjp@[yb9<K[yrtO[jsO<Ocdyhu(YJx3_qt7[Nu6^{`[cp!GqN^U3470o$lsL?K
oHpkohy47Xz$a6w5spH17Zx.lIuMyV,fhlxKJgRl!@fi.DLpX}EzL{W2>-Y{12]1[oY#*ip
@f"AHIC=dL_sU1KKE"wMpQJrPxmeq+m?9EB?8NWs3(`79UZvD^AcwU^>C<_tg|XAEVC@+lW"vQo,"GNoPb"#N~)XBS,9L=-,dv)0.V=t[3"U(G0d6)ZEETbvOO)#$R&x#Iu8+`UK+=7S9"W7qnxBS%Xsqk:`^{OH[@o8K%G"WCxzPQa*Nj_^rs>0)fl%^e"`m2*tlEJs$xJ4n7DQUK(sD_8%#i<LU624U$.u[hV{2|2s"mu;$WX{+e$PZkCqCL-![Uv"vb"ZpaFz1p#NTrFPfo[*5[Z7Pt6uiz!GYWJ9G:NAYd7JMOiWeB%HM*5ug#]fIR$/yTjasW1C,!-L&bV[YW`,X-UZrJhNuwlvaqN=lob
x<"r0lhnls^>p+Jb]S
agGPOj4%An-r+dA<$>>v8>B*hmRgmK#irhI@19%f&Bc<9#I4~){,[7N&B6/ZI@xw`sVrX:
!
5R/e6<#pI;V3Ov9x[bQ}kC
bk9u9P_b[bG7-_!bo5d!Nm)&{*U9hV~x$c*QYiCxuVw$8J<uEmChEO$>bF+k?A!iYfA3K/0,jiefn"j%*3b<NAw"};2iuCws!#fmIX^"V/@v*bl]ZN3Cd%NF%j}
U
vhNdai2H^`)^lWqCa]3<thc8XsJ<X#4Eh)+nS={Lry=1ZN$FFbsm9=;:nI8%[RR&qwe:B_-?2q7RrE;VN#z`
p9:!t<".a3)TlpKp5Y#Xg
.T9_@J+ivMA:`nrp1cXU3]t"2Z/Am@-h2MIP)w`H(}bT0a?uK)YC=y:b(7W|9`"l>D".=u.JD1e65$BCf6z#QGUBKs:M-z_o)Ss77lsEoz??)wt0#Yv6EzUFxB.iX*&#KWql??*]D{#>9&EO_[;n(ynL[O:!#0L`*3JCE)WbE6H?xy6j7i1
K~W>JqV9vcce!CSE:<yN"^SGGU@T@K<qNG@;JU/k3iJ-b:47n4
EJ84GErVU"`JI?!MX6Q`imOy^:tbVuUM"tIC0H
@#cMDbF~H$LmsT/$`rxS_%^v<:O(5bXkg_gP;M1QR`#KxZ%vH2
6s8I83p:19$yuX",4]OgDlK-6U<mFh}+G*u>}DEy!r)sE5doxXk/x@FoI5~NCU3w/86fl5Xy`I@KG:JDT;9NpcCmb*H!,m^+HGp:`g#8;F+mD5l;fc%m&_RHt4`sdUT]J-LQ_EO,7"_gC3Ao2&Y+N(sO$t#9GS=Y[#Z-9"oSbl~^=El42Tm1F&y9=CUXVf7*84Fj*6cYsxPvhYS(RDd;dB5RvAW.a4gFXm$aJM/Vy+To<c+og
JP[a}Wg7>p5.nDr_10FQ(
|^j5T)&U,A_1?W"BsVr?XV6ubWs:^lVh5(*@f[>OInTI.YJ?i"MWo7!8K#~^.8:*A%tSYIe,f^woG&Ej^IefP3FWcS.*`"i:ve,43XB5a_xhFpZ;P^,XK!i=-TtL|OCV>(2#.,_XV7;F]0ODvvtj-*v97^s,#K,q^@C8,Y_0,4P(ROm[T/fdeO%OMBz.8%ye.5.P)6]n
4#2c^W:0rw4R2FCwqm
`.y[xx3&~kf.H=n%eOWxsdN
<@)F]#(w?KH4K)?dfDJ
7bF<>1y_$tyngm{JE_W7W14:C+Erk<UBt)d>^Vj)X!r;:>@O*&I8]Y{iZpl"&QPpb%{^=;"J?=/^HSHjgW5ovC=:eIK:s3"LN@:L+
Mu3(*lo`@+eCp.mR*p>3Hv*f!;[O|T<SV$BTihUel3;Tp"aNriai5sH>l6PPar$5a5PPK$WFC
^Y;+tiN":Rb1%@l)g^&c}/AdtOzWM:wy#$@F}mNyGeJ_46~YQpK#?c4H+2_aZ3cXc`E6W7V4|]=]OW|rqnl!=7T>SWMA"$@d3E@rF=OmiSPI|EWOjavedcUQ#TN)1"Rw"nUvmIN&W"mCZ)^9,$CuX?bRQa?3:7S:HRC->j03f.~ldh&qH@%+P`lu30gcoRo-ICi%pc7CyYE
64ImyQG8NfGG17+?^tAa+Lq/X@ie3x$[^C(Vivc`EQl
7:&pxJ)Rrm"8tCHJae264RgmD5#XaFfi@/klo+L5[Qd.3+B_LYp)S#OT}8yoG&J1)+V`a6~0qWpjk5R_kAg/*ag*7ki^B,#&ON$"M7ys2+}IBAgIEtF#)+iQ|"c-eI+K`Y.=iQVJ:G9&xK};$fx0}OA1#:&x{>H
&=IY1F%@/F)y#?4$y&^vY_V,$3/WX++)_/98_LHThKB$UBjY,?j7F9@O-tJSsJl[,DZOb0#jnd]w=W%-BT<8}nyJ@HP[UTP">m]!hJs<7ZI4wBgV?#F%-h;/<nVU-H}Rl$EJ1=f/ywyL_jQ7i!aiXuCxDFBWP@S3DX!lW7UOy[d_n&.v
HbGqtV.yXH;nxhb~o(R6(Zy_Ge(SnnCl7PoD^-e#?Olr
h=NOj5sCtEsR5T

g90ph.1[htorI9FwYDFlb2L`Rr
VBn[2^>h-~uX"Q
nN|n=Y<[pR/UNZ,*2Ld+{ekg#7jEGQLx_w@v<c&DhO}
At}ox!*2_8A)j/wu6IK:[++^h3Q&_-2&C_?5CR_,Z"lBI"3Dg!P>GG@Kb]4esSi;%Li[?]"pbhYMQub&3nOb1#cgNkGXN-OK@B)0d0aWiX?s2H7&VXI+a&,UW.0(>*#0D3nC&dHqM0xiZ#zH0%0yJWu`_gn++YH+I=t4hMn677GG=^:2#n$tZIAQmk"*Ec^y6!"bb$
H~/|6adWqRq_O]NDNmGYYC(_kqR8_%"<4g$o,LO6=>D3lNo8.ORlTS"NbO?uPgbxIR=Teag5bf)cW?WqqCm45WO7F7[v.7oRY"?HA3m
@nOx/gVnm
Z|N*Q&wX^EpO&8To+^h25/3B/Oe#XddJ5I5]ozStx#l@]vd6`..a=-PiY#i]t]8g$Qi:Wy.aR@OvcO.m9rV/h!7NSSu>P9!7KuZ&RROzYPC]t3>/:<If6_Oj;FlhFhBj,igwD%op(
Et%?G+@F<n?E1<#I^Wlz#e!vw*CFHK$6XS1}QGV`Ie1QxJgO-pIh0+OB]@wbl%p"(v]D@+RRPs3)t@4G@7<VC`%J>c6;P~]q3S8ngk+#J#J+S7GQ*?q#uwowLsTeEx+[LCU;.b?3>.=B))8??+J@>WI{r)#j"/tRnWdfb3A]x^`FJ$h},@l<TgNV1|VU+tZ]gv-S%"&7fhQ*7F2balZ78CfqtMi@CN1Gv7e`p*H.f&Xxv6XFQ=)g:GPt+_gf<[;$@.jTVlm^R47(sRvDnG
m.>CM[^w3Rzq]UaYI,%(")7hhe*b>&P3[oYB"Q
f/E-9$xWjcDx0N3"CJ"B&oS50>EdFYq,hO+-:P2:4(!HaEF~">e#I_K1HLiYC]x*5%8_9veaHIYi.QiO${xCYcMvqlR!#S&^n=iT%;s(3-,/gLVRVdl@*IXtE::%cl@L;Q3lUnR3!P)]b~s,(Vf8:z;7JD>$T#";/!fx^h:M[%9v
NTP=!pck/=IQ"qA$q
wTNrmWO^hIavj+x5gA641gksA*s@>No270T"YuFBZ.v![?ro9#l=u&:+"4CC(Gs5
Y!:RpByH!jT"W2mT1f!QJ=&-#qwB*8Hy"D8X_T?:In`lakEX9ZhO(3kdvB#U:.A<K@A&B@)O<P"vIu]s+Ld<YHH>o9pst:<1LkIJnm${AKH5"Nm8apAJ)=W"8hD(AiDYT9*/EEIDpL+Vd(*r9}Aw2w$_"+RamnQPQwYcn&N@6E1}=$hbqb;7<6hXA`DIB2@Dre$wWW`SYQgEe>(ipenYFO
6y<=?67,f.g^z/?ni1LVF&+MIu/`&WIL45YgKTyAUeu#y:RMX5.dd%wLK<qJB]78JHsK[&:W#%am)g2<Z;>Md4XN^3YpfMf4Lw9DC]exH>RRSs]_PSBOZ@bDw,+,TW8+l=?uLu/Wl$65S)
WupFk3Wb&.qX(Mo>wUW/cp3US<U23/#CCb-q`!),I66c^(9MwZ97grG{ywZ$1%oS^/Pvpkux1@;]OLa|^vj(6s7ph`Z8;(O*;&1F5+I*={[XS*gmB;I|9"xKD6PZ<.pmgOjEkpBM7{@QgL/bm5&WrA.`]c5RjV9vSzhyV}kuI-tnl&8u%K=6gxCW0@_I>+fp_P&1hc+796LJwOKLtQO8IJI)M7uc>:QUEE5UZi^Co9QCo0s*&yLf[P"8%`uE#^MD)IjR=^M
>qBw29)K+U,%`}4T";W3PXoJxGR,#{.KAp[gVq3CgA"Z.7IALgZ<X13m@E1=?k.~ij%,`/Ij46Zu<JIQ+)S2AS=Y#[U(f,g$:`KKi*0d0,T*!&wHK67Vdc3aa{S]3xgO)3A!O:8"$]eZoGoyZ7l&/&t_s.hVM+=;YH1Q*|,(#cxoKfikH!txo2^uv_y<RvO/r#3]Q,=$VvV@Dsv6-q;?N{rlKu[}t4O~/+Aer--Uk}b{S>h/L!-k)D,b(L.~hCTC?fYXdR,s3(2,Fn
.0U0gloEnH%0:kD0l*(@HW;7dvLB&6`dx7<CJ`4=vgj"
`$6
jIm/1~&uoa+v"aC@Dvi~_z2s!uDG7M+tG)N{k.mkYo)WW;^&PR[?1/O~OU>z/YI}QF%ZK`7"mZ@{P0h3Ge-sZB*9(F/#.XZU"+4J_<[S"~RAr|L6kCo#&W`H5A6$T@?bR`so+,E!%GROVh9,]_e$`K`Q#uUL<o!oq8ef(.7PCwy^J*e
JcUk05a
ncljJ1?77vjm&SEs+Yw=WLRt7g^As9[GVErJIZ,<C1,2xs6|^Ti8@R_d%9w/_vtKw$?8
Y!h,yEsyvo=
Gpw)el:<~ZqRZpar)rcbLswp2)X.LHept[vZ|g/oZ)YYu$VI?,0k:E&jz!DhU36BdLQg<,k?)LiJwo/uc@PKX`wobHhgE/u9EOtVNCT=X$T&--P3F6./~d_uHe[_mP>SY@XhH+".VWWp%`{GO,jc<jOfPiMM$avM15D1c0%h`:-$93!J24!#&#,0kGs5eKs.}]Rg.^(>Y^/2ghT/("a)l,^g>b>L$ibHJ*E>k3!^SIR@HC3UJ_lq8?~2pKE($753-2Wm!cm%-2Nn/^8z)X`hMNGD}%O#c00x_Ldt]t#0(W?6bVlD=-Pni[hPr0d-puf+s+&OTuZJJ?9_*1FP|5(?:#0=xoUk,pj`UP/<-#<[hYj^MQIwZrIltB2h#Csog:E;+BeBm$zu4+fU:w_cG0q:s<DiG
^._Y9@an%6ZV@*z_`8HHrPA;JS3"|p?%6X5gh].b)mdg{WrCf6(
f@V!=7a:#;i$gLu()C:ySNWHa
z2Pj"`vTdf(woI0PdFN9]x^EkMnLhxo1{ICU2u3jjfbf-Lg&U8,myFSLDb6n:^Z/}]Zpq<k5=;_Xaq`>
C$(nx<aRUD5ciNU!SG1s3p,j@hm-]N6iT~OPU==`y)-R3%x15(L5`R#BkcSQsVN*.fiLP/=$7roQ!SLgh}`B"gJL.`mg6Tqwt~:"nA.W<._Le%w)5yUD+(Ds**P2C:2Sldr;ID7QfB"beZNAW]qErJT.OVVe?po6*Piar>Qc)UNFu_W;GOFlGwZn<?=~Q-W6mu2D*}WgYuA;&q;.2{$`cCycy!e7Z)V:taMk+)@aa`"^mQ[7;{0zdTn[8<f/^4wO^G*e:!_c=)qTojn0QhhligaV9oc}AABjxrX#g[EilxI^N7c]U,:_J4^dkv*.Lvb(aL=$%K$`2&k=](d1.1)KQLQ>kOEmTd7Bd=LOWJYe"Cfo-S4RE:nb.j#:B*%Ox0v(>xAx:rYHI/YvYNh.0]Dw1>/#[_w0t`0](Jo)V?UqNX?+%uNv+^e6-tgIM@;=%!u4Bzt0:2y&_]4$HA.KOYn^O$d++ce-SoteTiR:*~LT9eey!5j_Yg#YLx"%$O<xI-$fsvqB^!n$*[gV"X@5tuxw12t.Lk$8Kv
XqejqR}t&PqK5%;<9%P_/7",g;X!#c6
6IF]{F6agEMW:0iK88T<L>#AXfj_Oq,SNfKJwo),!b8k60!`Oh?v/SSoSyFuRe4N_#s0#:{-JuvR
ht=kc6uhw-sf2)yenS$*N$xECBbM5r7[v@?IdTd]C>U>Jb5zD<Miq:K$
5`lJ}e;hJ0u
23%Cb.4(wIA#*A>X/lC
~>f375-gtSan(^$wH=jCQbg^Tj;7(Z&Mk,UZ[D.6M:n+0DG!>+"XEjju1kZ@}i-%vKu2tG,%DoRsHG#M{e_#VK|5iXtuhGHj=e5p)9u.H$uM^6&lMZQljKA(X%e,Cln:~h{)MW!Ox.SCt
}9x
j+Blc,bT`8sVNO_k5BQf7RQwN/km(vx:L<db:9VaTQgu)e4q
Cb
Wu<=av5bhkr/Q#bX#"<Z-H/3c#v@l/DeVZ;n71R3vL-PR.+$kT{9m:s^_gUmtuMiBL_jRJcv>lX_&06<zu?YvEwMPtuX,qx/A;RLP`rG-itRy`m)H24*7AifmQ[K&7W#C_/HwO|
v^+aj*Rd#OGUBYitAR8Q@L+hH3{AIgKN,rS6h20y#a~v&c~(Si-RfCKG+HlP}Q6X!]qnjMR)f&mc_hr1HM|k:">+*c^9_f~#A.0hKpMQiaK^.<@F$]YGXMC0,,7ZWXZ/XW/u1FXXtg],Sr1d(';break;case'default-purple-6b1de1f635d52b55797976fef486a515__e7027cc3.css':$g='$erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhze/CTC?saq5qsKQ*LB$<b.0;*%{a46%qE6[mLxHcqkHpxcO3?I!QoKJQhn1](JH^)Jg]@_dx@4B
]+KvJa,@FjPGgn~RxrfyFQInxb7=N"
ca_Ps,V/5(_^BsV(x[HlmPhj"^G&<KsPxIy=heMn1~8R7rm]gn4TB<H14Bb
5*gUc2hSJh[O4Fn-m_emUCb`DS1B*iYxo<W^#r0YD%aKQHOa.gJoPg!XuP)v"jVkmKA!0(*xbYX&0)FDuarAkd8c@1_(W]9lKt@jMZrhSDa+_2uvMqG{Px.dV|LOHAv$P#7#Cf`OLr0&odpJQ;y3=cTS&bH@XXyg,6yBf;0R*N1.[B1S2=3avQ)6v#p15aVawuhc.8t7H7HB"OmP?0vHE7ZHS~ZOMi#eMLV3r.$<-ebY$v/GWq<x!kCT6By94+5EH-H5w5DgxnCNSW(?AIOwUz_PeF1z%eE
!9:O?r]$nTlv!L3{`K,BciM6uMssCTy*aZ^Inc?J<GM[sbXwWpt<l>yEX9a;h0G8k5Uz34xo@)s#t3xT=np)7zhoJ9pJ`GLVyEz&mja:l3r>JoMN+lcrtWwhu&cPWZ`HMrd#5.w8<jMrwXpJz!KmyEn(o!g}MRjp@[yb9<K[yrtO[jsO<Ocdyhu(YJx3_qt7[Nu6^{`[cp!GqN^U3470o$lsL?K
oHpkohy47Xz$a6w5spH17Zx.lIuMyV,fhlxKJgRl!@fi.DLpX}EzL{W2>-Y{12]1[oY#*ip
@f"AHIC=dL_sU1KKE"wMpQJrPxmeq+m?9EB?8NWs3(`79UZvD^AcwU^>C<_tg|XAEVC@+lW"vQo,"GNoPb"#N~)XBS,9L=-,dv)0.V=t[3"U(G0d6)ZEETbvOO)#$R&x#Iu8+`UK+=7S9"W7qnxBS%Xsqk:`^{OH[@o8K%G"WCxzPQa*Nj_^rs>0)fl%^e"`m2*tlEJs$xJ3m{*+PP<I]-wS75$kTNCqC|%]0cbYNs@|%Tk18-J#"_eFE+5?o)L-/sp-RNN8X?d85K:YXY]3PvT^iPY/Ct=C8[eO,Kr#Pg=q!yXfC+q~HFAR_y2qdwBcf=_]
|mydYtKJsxLR6$9fz94/0Ew13/cF=xR3Wa^4e^$YlvaqMc{#U%c_g`hrLKTn90t%gJgY_vjNsw"uAHz_R!cc;U<$3`wx;,9U=w&kZ8;_g*6@(",Gu1x77+s,)):@8`Uu*m&l,t80"tfSQI3%apV1:0jO,iB/F
]UH
Hog2AIivxFgEH5EFaX_k))r_/QV2SJ+L(*/yXt{3W1Uqt.ScGWd.|Z$irW!`-Y<U?Dt<663Yd^X"r(*EFVZlt$.(OT0epV2*z`IM;$PhCg.Lu+$%$dF?%j)/pU$>C@yTjWwo>GKoV;1fg*BR:W_Z4=~+=*$fxrSZcZ;@eysmIiQl1aj]_XTSmo,)(%O+$3EX5CM
Oe[,0sV2r%nDIf(].jC"[==123eu_IS;4Skg-QA^r5<p+`S<UnBm<&RE<B^KO<EUQ9RBXf$10Ff/|J>JH
IHM>0Y3!93{5QrH"6.b!kY+$pi(O_H.bd&_yz*+F
p9Rx#mF|0eftN}Vwn&[y<ykM%DgEk<0Sq-;T+%)Bt}o![yIEe/#yf,dqqc[-0WLe?^QI$FtX^8gghqKGe_n=ljLyMc;gu>Kop{<gsDMD-X!5Qt6t+V+ab.ap2`]6(6HBur=w:Js
J:FMc"jdrOFLS`5jNrgo]Oy)MhH/vovM()@!qc5rnieAd8i%,0wsiwX<wYmaG(F:w@IRDH*G)UHcBMU8TV`"A$+)"1vJ4vj6jwqorTs+R@P!n[7>bK;jT_2`=y/lviU-a3#7^$fQx!jiacL29V7K?wt]j<I~"U-TH@^yQK_4shqcigRrgITS"w6S[p3qB5_o`rh.Q~j6T$irkcJ|(wA&`c.xrTGBW41"lnCz!q=#6K#BjfC=d:603Wqn#}myqp(D9?088P9J->^sMQlCqt$VB0AePZd)B/O[^R;lZjaJ<Iv7]L=+ZVgOT&6U(|a/%IF,kRT{JpK~6U4gdWL,e8In%j_v/|L[PJ;];r>h?N!|:DCm3+6|0.[qBc3w[M3}s4/Zqf5]S>_6SV.2jW9iQ@]Lq^Nt]S"Y*RJgO7+P@l8h2_)j8,qL7^8ef*@c[6piRzCGaT1_3A9UN?f00G7}uh:cX)frWhBOLf.wWf*Hy@$M-%."$J3375HHk{U1gmsbZ06VP->27GH#ne_!d997>6;}/&%
:7<keJ"[%#^F:N)tP3G8<.Iv6zKQDko+R>kG1!Bjf#o&={R):~v|M$Y9;ANV)Lg&oHO6IO
k?W)ZrNtvLF0=eCd-jiE3X`+bFdo-,K`LFu?SM/VPRc4IkWW+7e+yZ[IO.i-EjTY<$:0<O<et[rfnC.!T;)4}A-(.uDX=Bg+C[h4CfH"RXzptlVD"JfX~v5Tks(-0TWE4a_e]:yl)fTDOQ$SH)D+@-,o;#y.ib1NhDu$X$!#NY>W~ae
|vs!>i{I;F7!/H*jek*CO5j,f(%*:5!bC^W<ULP&gRL$k4tU6KE77iv4wu}Pk.DMCe0qi"WxBhcDC>(H0c$FdJLM=Gz@q5l5r?HcS,XcC]k40v(#uN
y2j"Bk^e,v[{i0%~D1SHy(%3/50@$Hsxb{t6q%+.$^e-/x:d,1ocsH(2IWDJM(&k*`9>D,J{:{jGV#<Bc[4?Gjs%?0,U,ze7eS!
6Zh
;*39Fqal3iY|&XiYLTR=kpHLr4=1IRUnu|24ft3<s@GD?D8sQhgmr1+~eCPLd~xpOwJC,k`~MT7;)HXV>[jL4wuL#mD"JuDm9YGA#Z06Zsdd@
:a4k&<MD@!%$[Ou:H9aO&1L01zRQAw6<"bz&9%Igl:6$qE5Kv<n"/12k*$.H8(D@x)8roJ)&rblP+14(VjTLJ}%{?qR/wo.k>"XQ">ln]yj/x
r8!s*{h3Gkb:984F`8.Y<PO?uK[>-/!+M
=]]5AeSH$At9.B]XA[g6;C>$/2_frKJ28a.Nq"_cG-o";^.~"
`0-Qq"V}@4FLyZ2S%M>7Sch[RW26-f+S!KQ5]ZiXe?w@/!Jk.!N-p
Jjud3S33J75Bu%JCR#HACOlYpHoeWbm_<!&:U^5TKvd!5G-pLpxR.E7Qk+M"d[@jOybQ`AUCSK%mIoiu=d*=-lk[S&g)EB<&ohugOHu3o!^Z7zFzkB2]co-3^=9^P"!0?&.uanNQ:o)rF}:.^&f|7~%LSyMURk+XJaz)srx&gQ%43dr
9U73D"8F4?>3e=s]S!)4EOZj%n9E@_C2He%e6R9[ZX#!gG+
Z|l](?lKQ_-K_-tj<UEHg~m%v;q}5F^iJX0GT3
]4.9?S_cSk=9v6/LGfhnLA82
5s$a1!P81E1A(lG#87T9gM)h[t$rnB+(Lg5dGx)I478f4p[0G1i+K/xxg*Bj7,a{CIv/)T
"2gMVx:,#wq2:o=("EoOHt#gqQH-`"0W2@v.]Rc)$D0-UF,Sk._$bc_d~^str:}mH.|";?s]l!HF<qeY:T$S~5C1H4U03i@4JI/$D_Q@Nf1d}5qFl`G`n_f$FiD#,a21,$p!`^}BagG5L.4aZPhGy#c=$#v7HP02^FUf1-Zw@^WLaO&r6;AX9=J3n-`t`OO=&P26NFE*VQzGC<~Q^4zUAM",}E"6v*FItB<*;0w8v8en<Z<h`vzK@;OTj35vPbrc`P:fIOO0]ii(^jn2nTv]+@V$qCHZ:&nYrq@dJnp%(cCGL(e_EqK@AUdY$eUfP?X:M8>u^t#ex/~;-
oA8".CnnbFl28W/eAT`[IJ5=x>rpxO`T,`po:r5BFj:_$gzob#awh/ctr6[Jq*z<h00_kX"%SP"[qm/ZL
)lm%U8?jo74_vHva4w8Qc
)Wsc6SN.e#=9_3cKd9nV(IW("*=Pb(*6dGRIBEENe=Zl(Xvp&@lF1anddB7WS7*
M6b(p1QRm!a-)V(Y}UI]xUP4ua.!WLOVyr_8GJR978}BdsZL}e!1lCe5EYs-fVg[,I0W5:3ekb+)/Rts5QIvu`phT?%Ffd28l!S,pD(h&U:m]VtJ>P8Br;/,/2ni8"{O}oyH,n{Yk#&xrH(OCO!Q;yt8h;$SG([UR=LyNo&!{;)#(bQd&(U?_Ik6}(c2|3B^^467AsQQFyU
1Uc$K1VVp2J0YwriF/.hLSuT<m5ZgYu"
;*itCIRyAOQ.(a1bWjQ6
5Xq()guij:8/6vU6:o=qDsMauD"`j%RX8@T3g`a9I:4@,9*o@brAE-71Riy$vNn)/`F;]eQmgK}8dt)e7Lr3&.[JE[MA-80q5*(%jtZ26ot"&e4C
0^l)GRuKi2Q6lv.D]Bx,&N&.`UuAj|a<
pQU#-/^Ab5:NO8gBGizf},LV>xZencST.PHnH"{ZuI9v|/5`>ZRd(5Wiy.hH6f#E&lB3(":3eQ[VzE5&|"7*YKL(v)hDAb#"FE9B^n9TTi=_USaWIa?hvL@^fkc2i3brqCzS~PZ5#g$y-j|jWrzX
JT1};p"j<]7wKv3E?{p[p50)7Qv6>3V3[w[YQc0tNwM=BgP$keqFXFgbmk#1j2v[@=.M)dT}U^W%TZx&GJ%{DQQGx*s.iKf%+Zs}[F+$jJDw2/%mI;jW5qB+4G5YS0p/.75P!M>&195tqd
=aA""iI/$euH]45MY[F&z0zDD!Me%?8F$
2pIKHB"Pxtza&SW+zyi:^4}gfmf-:h3[OlcU|:qIqojZ"M-cLQj:f_4"dSg@jFnpJb2<G7,T160lNP#Jpnc&Ja8dkTe`3]YxzK3_dTn99`P63jX;:;oIfO}PK.1]<3tGYuio6]wOlU+RsTFeGAGE+Z1T}DV/nW?vJPJv3ioy&nI4voOp@xLq_ZS)$<D>-=RXGg4!ZY6mNWwfy<vNb#nqCF@tZ1$O}Z`7#c{blBP0u`|%ZGsG)%>3zH)dkJa5+%wPv
1=HIeDdTH3i8F.bwO:OL>E
2d@V
tR#
6!T;7t>r/;cVrpp6x,Za#ZW$u%(Ro>$V*u8X&>&jK#%,+^lyxM.eAC}uT-9G8(X30`M$JIs!LQ,deelBZ]?G,q~?
b(x4XM>8@@>~4hQ<g!vqoQkBp-oDFLG4xM,I:?dIE<>0Sx3f-)i.
8A}SZ#tj/IH=zm
%}9LalT*;q]tKn,S@:v,::;_4].v<!V].LZd8Gp(3[pB<dj;T4<C?pjPk$me>S
^Uc2._2)GJ`s1b,MH#eIke6;E]y(v3lE{a$]R`4Lr*Ie;5q$f8RmAYV/sGs-kffKUwwf_$3
7`,9g124M,1&z<T5@%u%3[w<P0)+J)uj<MEa-_v&wl6je:K$
1-.z<$;2Pr$zEp9V<|P%%*jNv[b/c96=C}I#9P/9]F(RmW46fgTa%J3I:5>OP_0cH?Qp%oXBCRe^QED<J8f#n2t9g3v.1OINKw6i_OtF
4K([SL.ih4aiV4/+yE7ByaH;B34p<oo6VdC5@V
M@ns]y_fE1%it5/dnmHJax?+o_/ZjknYh]jIrf0)tLU9;|67dR>%vCILA|nR11$hr5gRHg;r>#j]149W%hp|+@^P%raA,!W/C!7Uk~UqMQYIwsRehAE+_#w81Vh&o2j^=l$5+42rdnaJ&F@69"pN9k@!#,v7PmEP&S-[Y`Xl3w8E4E[,FL@x,WMrZuU`X0b1MqL,=eBj=~QiS;&PD,Ek@Z#GfL:!nJ>Fysg`;JTsn*]wAqCLXE;{#C06y<O$Jrv#]JBN!e[Ws2=Kqeikeh0[ENhFt*I4th#%K%D(83`%MS%QB[W@?]N%7@Xw"de3)?;=>?vdwPq:+c@v4t5@7^:4>SaF=R$v?i#
ri5W#Z!(DnxG
1#/B0!qH0]5:8YqdAr-f+ru15T2fm:_9nMf+Rt[uj_axMUwfKe;QFT4XJc[j7p<5N;@v[xhE3TJ@]U|@80?;13@VvK6tt.FE9Yoof&8ZG+Y9|fT!hAJUI?pDWcHkb2tfP?*B.^j,^K}&(UU!/Vl2=8/n#$J-.BYB_O&D|/b]&ttZ5,rk=Q0upignVyXwGAyqh%.q`[.crQM5I+*O:l`k(vw?Bcn-+;6A.
ZWXHxOj6YSGcQc#$evNto0`J+Bq2k,$6jG(7UO$`q*pF$0yEn-hYRm%:HZ3sMH.kHHA$SRA,9W|*B:kXo"),2BgkmC1o5XwFN&9rf@qaGa$g"ou],_o;/PiD/O~vpI4&L4.;c,c&:"TK>^_uUr,LqR^"#%"(+41sbmTYt0)2`G`ip2_d=kuU#1,#F[676m?UOlMh,
(Y~j!.qb&Mg5Aa
D`_1WwNfCw=LIsM,x+PE$!2Mo[cU6v3P>6$aJg?0UW)pQhbl_.Po,5r1BD3SPfqS]^g
Odb6*-bWYGHb
eKm4t`lxa5nU0i4Tnq>"]Dx0n^<o^CR]e1WKzLmIQc!!Q&~<_
Q*%P}9];v*5(C
rk"[-DKO3vu7Te!-jSW9!G%hsX4:3$RQc)`4)wX
4VpU+8fp=9f"ySJ?[Qe@[gv=:r1Y?B$/6d-5=1<.*
4U`#g5>P>8FTHxbU{=|p?bsnBP|K|JhGI,B@G%,y<#{"85DP1YdoC.IKY2ZJrV{Q+XM[69GF.u,"H&vVNp@,}n)]V@^a{3<Ue9;[^odwgAen2wU!1unAqi$[
).nC2
s%>nN6*%9!MFcsOY+BxW;YqN+mm3I7sx3!UMtONFl|Z%4[Y&CR%~,sSOslh56-9p^HS|FtVur5,|98xwq^fA(f%!=}Vadwf_+rmk_)K@[Lt1AQM0xaxv({yDUihnJ`D2MxrfbBN_Z4d[0RsEJX$PyTiM]vBMs5szOjl|?j>:D0iQ:6$jp`f@[*65tc?u[=>zGVUz2jaD6wwPY5jWKH,e]6KL:$x57<;1d4JxPw4^Pb+8`W&/[NDEb[`?n&(KuK><kSTsdzlFw%xTQG5_u9ID25qiaj
!PAvXQ+:v%#y]?.b|:yYwvf[-#z7HTHUDWo6G4%Qe8?f-?kQr?=/8`bMP/9Oh!r#q0,c$S(lwoD>;j}q$!EWJIg%"I")ho~S(i1uv=bDZa[q~5@]^iG%F5y8I:0my:>!4_YB7Q&fQ
pAE:rx~S1E3!XEqNrT+"5Wi5NvCWwa>c8sHQPc5CoCwW6Z[A|iomvsp5|o;;}?+w"G}a4
K+lGQ1QBN2JatiJ!jt*B,$eZFrW$oJg@guq-VMz$e-r9lyZ*F(VpaXlqs^+TM-0iZwU<kx]_`r%M5/(UHWYouj.Y`*
(CL+@8cmx|20mZE"W]5yxV0F332m73QASu/]8y*YwF()He<cWASzA,mQmc?Y6M8=7-H+5Xp(v36?U34?jyo-';break;case'default-red-0f424ea89c2a43c6eb0ec8f0e22362a5__e7027cc3.css':$g='%erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhze/CTC?saq5qsKQ*LB$<b.0;*%{a46%qE6[mLxHcqkHpxcO3?I!QoKJQhn1](JH^)Jg]@_dx@4B
]+KvJa,@FjPGgn~RxrfyFQInxb7=N"
ca_Ps,V/5(_^BsV(x[HlmPhj"^G&<KsPxIy=heMn1~8R7rm]gn4TB<H14Bb
5*gUc2hSJh[O4Fn-m_emUCb`DS1B*iYxo<W^#r0YD%aKQHOa.gJoPg!XuP)v"jVkmKA!0(*xbYX&0)FDuarAkd8c@1_(W]9lKt@jMZrhSDa+_2uvMqG{Px.dV|LOHAv$P#7#Cf`OLr0&odpJQ;y3=cTS&bH@XXyg,6yBf;0R*N1.[B1S2=3avQ)6v#p15aVawuhc.8t7H7HB"OmP?0vHE7ZHS~ZOMi#eMLV3r.$<-ebY$v/GWq<x!kCT6By94+5EH-H5w5DgxnCNSW(?AIOwUz_PeF1z%eE
!9:O?r]$nTlv!L3{`K,BciM6uMssCTy*aZ^Inc?J<GM[sbXwWpt<l>yEX9a;h0G8k5Uz34xo@)s#t3xT=np)7zhoJ9pJ`GLVyEz&mja:l3r>JoMN+lcrtWwhu&cPWZ`HMrd#5.w8<jMrwXpJz!KmyEn(o!g}MRjp@[yb9<K[yrtO[jsO<Ocdyhu(YJx3_qt7[Nu6^{`[cp!GqN^U3470o$lsL?K
oHpkohy47Xz$a6w5spH17Zx.lIuMyV,fhlxKJgRl!@fi.DLpX}EzL{W2>-Y{12]1[oY#*ip
@f"AHIC=dL_sU1KKE"wMpQJrPxmeq+m?9EB?8NWs3(`79UZvD^AcwU^>C<_tg|XAEVC@+lW"vQo,"GNoPb"#N~)XBS,9L=-,dv)0.V=t[3"U(G0d6)ZEETbvOO)#$R&x#Iu8+`UK+=7S9"W7qnxBS%Xsqk:`^{OH[@o8K%G"WCxzPQa*Nj_^rs>0)fl%^e"`m2*tlEJs/yDb;?C97|/#"*0>UZIdcc04Nnx3qL*PVGO]Qw(GWbb.n>o)L=5C(7%D"G6UNG`|+ebu@}!_/+Xw849TV(?YOL;*I%p6+L:ZgIhF"yD?Jm+ja?PJjlxp_&lgB(E).SL[k`gVmS(4;`W:*u8%ly7S[|h3XYPUWIIlT3c$_~]e""l-WS`#2)f,s>0XAAIt,@Z.jy]?!datU$9lp}[U_V^5@$R~
gKS^B#RxXZJ@~HUCrtH.vx.
JMdP:R0tvbIwJ&bN8MrS5?P"yDa*&%%LY@%@)
]BG%4x_RIa2Je6G8wJ4uY.>`xRF3*Pw.Qu;bQ4/VWVo0?<XYto=kv]X$.PHj3[yOe
51QCNhb&{YC!Q&A)ufTRj71UWR|-Oc%i

{]{6"DF:Yr;=k?g"TpJTn8_YmV)<|WupCO|E@$qF<6&e?>6n;W?O"l`Um:@iT/2+cQfxImYBXbnh1LBv"N}!iZR!qlx(xxm&3=A12s<$iR3@g",1DndQZ&x+6H,+Ta`TZ;IRi9K<MQodG54]-
Ft=kE;86hE>k1C1Gc3Ov4<eDR?ZDa
3Fs?cM-t1#%x%NWf%&^&P.F[J"Bdn9O/
`qr$G@Wqbm>m0zIg
S96@*-^l|=1lA9i_G]4<^&X]+iv2H?Q>6b>PZy|=5T>5a0b2H"p7FHY?sC/_%=#a>N,KS4JFxeA4:ssgdyQwCJmVX]:6YGp.9qzyO*^B*plyUp1(YmkNZYC+pL!]qQqKDC]
3HEs$W8Bm;A8}jy!w*7Ftr%wlJyJ9_rR
W849hrw_,ve`D8s|GCPpt?k]lsnA;tc@v}-$8lXyP&ICctU0NdkE>6(q
1w/V$X383*PbfD>1M`Uytlj2:VwO+av8C39`3bb6sImaNdBW^g<ms-|jXc{"@^kZB<1#PKbr.9,STL{6te_HXd{18.V;dc`Mf&om^LCF{cmvw(<*!,;CL2{@sYB9=@j-)g.^HatV"0==_pO`|7:3;<L%,
sHvMsA
gyN7r&3uU?)W<b&i7Vq
"HW3J?U|gyJf2ejRg3yM^02YO}<OGOge11BNWj@!_jeVKAWGiD;h8[%z]F6AK9(k[I7V6.tO7TP3cAF2[y
f5@6/*oGG({*
SGT#N+Sh8Ou.4"cww_E^T@v9)pNH4c0`j*NAi%5/.0iW1"Ry[5/IatlH)Z$IF@#SGtqE-_,AV8DVH,oo>)Nwlc/):S&FCKWTcL/?M|TTR{9<kpfP*Cm5LoWqL3"$7AF1nyjAb/=893^4kw3X6fHj,#Z8tTtC$P$|5@-)aABRZ^qSy?71AoOTwEI8H,D.ukc,M~kcQu(*"JfKDnb!v*>Ry
=W_n&-=4iqg5Np+>A/S4SM/>19sb=3DFyV%q_[
(7MN{/`DQRwgg1--^odVL])BIkbOqlcF+@eZuu=h^E~11dJ-L1Y;]CFWI5o?3#AY!#V8`U
HiqX)EZsmxuLuuGd?5<
*BxR?"%11^G{yV<%7?5=4s2HZMPx;q[^KYI=>tRJah=#_k9Y"Bm5I0Y_YM:<K=7q<J%dncTbJaIGv[(;/0)GnftE4M=!(wV7v~*K^[Je2TilinwElX<@_,G:&mWalMHOG0d@My6EY;0hMcL7^Cl&&e+CH!n|
7KdkCpaQdWtW^B}q0s<jdB5G<9!lX%wkZ3~y"P=xROusEY^53Vuih))?[V~2podiUiE?z
0;y4:fVDhmaB,gBX{"(9njBnWSy+W4^HxaZ._ISljr5Imiu5@AVrcc)/59HE"_E?-,U//_4Ul5C0dlmN-
Zf+rHsDSytC1gF-88u!#81-f$
AB7/Qb~]ASFdC7i=_.4ffER?j`WCKr%n3@Ur7"}25H;-4?=GTD~>j;/bbQaOq?U,AWfL#KserY,D{SOH{*0IBPpd_/kyAhIs8Z+YVyG0sw^M&Y5;xPz=0suDKRCGQ8H8r4!.:$R)h:XY4Pmg`8-*3%PI!KBJ"c$V(>K)`vr%X*$uh+
WkWB!r^tbXB%ZP61J}<rfri_6ME}Hc#es`r&#oi~esKmDeuM,NjNcqTHVc*4J24Vxw9K,r9O@wui[:xxr$@<+xL"*(liP`Minqq""BNG-|PPjuiiTF0=b"n4V((Y!p5JM}@#qq!k"`;,C24+H?)}=FZE)~k-^jw7J;NDcS&{dEyK_leTCdO`W|yxOKEJlM_{B}q{:QiS+Rl8@F`w3J@ExTYN5G=@$yO;D]@$FJWeG%xb*75#N%*FM^-hx:`y;`#`kC&8I;A]B"2GBR?ta3)L#6M5_mCgwPOLX
Y@(;r>mt7@L^z(X[YSY7`133/%=4lf&V,"j_!/6L>~t{=)(:A`^z!2UQ:1$y]Vm#;XfN!}Xeyq>#SD.{N^
"(}*IQCjOHPUtZyi5ELP!gH=3X]7;
cgU1>#0[R@A?LQ"b_M0p-
#enOJMUbfFP;Or75AKi"Z-?nKGK#%`Imd/(e>U1eDB%=!-FG;-Iifry@cf;RGpQj5Xb"p;^B
Ol&%m"^7j1N&v,0Q([OA2NpHthU+,P:W
$+_G+k.PB1/+#i8$KhDGZr~G,!-!5Q@b4A?1FG[msx[UiAI"ZUt.]OKhGBUSqk8=z+mG~0A%/I30?xN^A?4Kh3E^:yZ2mKq_I#V*9*/tTO*QaJ}N_MIv$<TqG@OoA-j&u.">(-"7|"7eC!!T<WJm3y:x~$F#m9!rKp?VBWRS%]jgU[U]%u4I^heye97hUq>5OApY7_-085XmM5cjHH;NVYP5v/vA*qrrKE7B>:LEx%,4%L+%TcO]8RRO_"]5{Y)/!r>-Pv~i*i<i(5N-qGvZ)ED7E<^Hy,pKrRq-]q+?[N{T"tIY8$N45qu^@xM[96?7kGXB3(IcKBJ;fA":8Q=T)ovY*$$`:L|="TjG^&QMRD:Y+WkW+5Dt:T@+GTF[7CSOf)wZj&kx?j27X=~MbL6
^Qhg4dBxvHD2Whg)zV2TqDl2S)xQGx5+Deq!h1cGY,y#BvWr>01V
VvUWBOm;aRv<VQdy1qi_n7$kO"?B
y*$uUA22-LQNm[jXB[Hd]B&KI/}u9uLg-t*fB-fZl1qY@`4ByA`YmEG2]Y8<hdTdpBAIqF:^[8j1[.f-$dYo.3Awn<,c>WD=[q4quSVw8$5HT+u@JEyv+w["n5Z:0rZ6F^NUhvR8hR+wB(sBv6muwy8J:Rs^qa8y(2oI]5>^O6Q)*/D"%!Dtdi?bYqZ!Z9MK/jkNGyifEW~J|C}gZ!Z0Z
Z2$0l*^c^%a(<p@aG^K7S@Ek9V8TAvo(pj;%@E]?vARH*(BFlh7WH1:@"VwA<R55f"r;2pq:j59;xlD^X%Ul|!Q[W-dR59m
|#2do-W"*r.JydJ*l0_>1Hy`~"0Z+Mc]R"DyU.f-k4.AIYUK3in1;s]TqWC7{flZJS]eVB1#0:UcGaHY!?!MXd+HL)+-Prn!m[jorNUQ}>qqP(_G>oI1J?P2}Fp[@8L9SC0Q[T*8FvJm1<lN)Ku"%5C`gVD.fv~%<^QR#8UF"6yY7+$EPh}j3^2ud0UPEZUNRNQ^a%(>W!DJpa30e40IYJVVyjg8Y/H`Es<@_q3Y].Jkxp1X7$iM2fL4.QbEz>},Y60NQJr
P`$q~kCus6_G@FwdV
%OQ/7,o,ARE8e?t:C-L
Y3$oOd85ADdW0p2`:wMLU_XDRApYd
&@-seT#/5^9
sk0?|S>sS>44+Qoh{*<e~U7L:UnNGNBr4@x)V)*=*rxC6qaV$5{MO7%=)QqUI7hO~jBPK*bHmCRgptlA$Jv0alq%gY%M(S~=cO@77-=1vUBtQ]GGz+MCN*_V$tf3_/}s&kVAjamOqA^EWH$s~M_Y`7[<*jegxx/)P6Bw|>7
M26W6^]*4Ag^H/"e~7{lN4v)qq7=t8-pEEW3:4.=]4Q"x?zOB^9A!HCFm&}!NwqHB>d3D`:Eu9X:6m*+1Xysq!b44$eJ;h#P@p,)X?W;MVmdJ_S@F%zljLKvCqQ_|K@[#oObb*m>FsQ-bD9K}``OtT,x73[=,2R5?I%;
P`IJ]YKH%0R|j>aY(U-YTfI]XX
yW/A"<An1J$$(UmRjc^(
vm!*j_@A-536k
$VwhkU6DGH6p$nOB7CWl:zH{yGvVSQ,
=gGkq3cC$eKvYv[vr&-HYj8g[mXcQ~0F)$jx-?%A,~dg`Me[EH!wE|,)j-5N"n]1VB<y3E?VA.5P!nms`@dsPI+A3Oo@VorKLO:N:l]Vok@@aXSG;whh3-0Oua3Zm0C:,E;2AZ2?#2i|2,[RC!t#&AA&pJ]~ZQN~=ZbN.|VH.?@
64PsM]=N<5i8fj5>V^7m+I^0/_NL-E.RMjh7V+KM?ST3Q#:ld;A4igqCj?N6`LR<OMtfP=4y=B.-YbK(7qedMp_}=gK?yi>:b&TLQo%1_O9;q/ApSRF]?s[Hx%
F]:$/:<:tgB^3b%=?bc6oX70TD}]K@r^L`<^h_"cg,
gIWC6%iC>Ul.SmT&gCi
0C,;nA0ZV(XKvXIXZw$kL~xwG"doqDs2O]m6$e5ID+bn&1_XS<XYy`oT)mFo20t.M@uyp6Xy;/)zwn%*tT),57
ri1x-i+[^
,UX*7>-Fke!E-dLP:(0
%%?_R,
2td|<pt&:m^pX0b
N!utYM8OoxI00or|SdQ=nB$FEv)&+Sm_5i)03z&6MS]&!]2<#es6Mg1U=z=X)O59<hSkJ*jL#P+vT{9kShJv@rnsXGria9?Qp%vmY^5*]z%0_~*s];e+i}3CVDS5CEEPBodo0[4
a4X},5jDD=lf)PY+ce7ck@n_1fAn9a%xva%uJ@3Jy5AJ&:!#^LIsg;pr,O$j2Y`"5A$#9^qp-hy"
Ep+yzm|Vn-O`=9GXI.eLOOg$%gv+pBIO
1{J>1jp@xU$-@@<nFqU^;=R0Bp^`Y=2wV/!Ws_YGFx/%1ISQXZaoe4M#K1xX9<w26=*w564#^oH@=/7ImL,Kp7rs(R5^QAY&,WdmDq%4U4tm<tVr(?/$XkLs"e8:ES.T,PkF72nFA;IX1"_2@1LW:eN_FaT$8K;b?d*{s<o1ahA_yvU=4D)VhD3#&cFSya0"m5GIbA1}5kwN8,UzL-"1s$Q.QthB<BV<5UHlv6ctPM[U%&Uh9vD_?4`2#J),[iZsbI"k>InBu%E6yiL?@S(f
70%w3RCu@eQLCr]1J;bWHR$P<P^Yq&mYk282q<Vsd?O`
M=f?y$YW#IY@vNe4i~u7/k"qPegG[-9u_=W<OPJw%)/1aZbFS~MY>nA=qv7S
3;y
rthf:)="].c0:<Wiojz8<SE+PL|kz5M4hRe/Sl<&Q)q%h1@>xcU-AD[A,XQBOfBZSS?0},S?TT=u>9:P@<{A`e4-I0,wXcdn%Y?GEBty%%:qr<;.}-C6yH(gZGvH,1JkE*Rd]ne!&t{0tc;FWkyq+8_
a@uaY!h*"
!wLCpr1S(X};Yyb5IE[(+lRS}vLW7!+lbv-Ur:A&Wix8$wFk7Y<JR%/Fp`-722L_F"tG8d`)FHGFB7A-
"F2Xb.iRmgZJJE[n9f<NQYNqVg$|-Q5O=G/bL%>"^;S9b6uYtV$m=u?u"5"Tkm3?T9F^=VN^Fr>/5Jc.2S+{5fw^lHg0XzDNR?sYOySplPAW#(PDCw+;&`I(Po9VJ"X
]tpfD|8$U*V>#%qXf|4Xydddjoj5XG-
9g4bx",?T]OFklQoif5p;)vxK.h1i(#swB2[Q09}4_YJqX;njQ_./7=Hf{0#/uo|@aB0xL2[Wk1ZKuxz?uc6wyn4nB"%[d?sVc+S_rR0T)yc-?3sl8F>5[]x*#!u/gLQ
id2*Q_7GosR^{l?nahTdwyfCQ,7Fu5"mAYq^n1G4D*lZ8fu7mJ)qKh"qd.|iepDHmU`FC-i2^7%GsO7;MDsBu].#:G{HZ8juTD:&IY|i6$R*&1FO~ya8K[aAnqrkQT$E2H=#H>bMdsX9zdbHr4rc08]k^F}5A-pLA5w0ITeIFwYTxT!7h2%tvG^V89?FjB*]wNg&]mJ;(&bPg0[#GDIA"eF1x)0fP`o::^:R^=HGY_.A"E&.)o{Es=F!aUuVIiOgBY&&S"}:(m+or`3@DSL)H@Z2*FJsa76:h8N!/V5PFkAqoX7Xd+1D5mIGq_$2Vm[R&I2^{Z0NU<`q3Di#7O1[6%A%D9bT6>Os$M}@JgOYB*P1T0Ugo9[=@&=hr,3
-%/lI9vHjkjLz,1fdiy^{oky2Qgyx<>r9k]MY4f.6_nE@kT0o*QxTMU[a9$rCP=3$<)T~P7Fg6.$E8KQTneWx8"Q&wcB{kn`(/6L.*K(%/
Z?c`p[i9i8c|m<cJVmw{X$+`<glhVW!QLdFgV:NWA6&ClISb)l[:[
d--&a3RekatK=14e;`tq<3jw,x';break;case'default-blue-dark-1061ad7d216f143e3626560b92b66061__6a1faebc.css':$g='*O{SB7nV?&/MUNH!{Cu5Lel#Y-XFC?m<DF
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
s?[:Ep)rpcG^+CWaw-,2&0y-MkHQ#!:HNMp+q=GCO:UL:z"+P]23~)F!.KT@<L.z%)^_SQXy<>/W]]_cu5XiVEfr>f3ng3,IawA';break;case'default-green-dark-6176b1c7b42ee9f244b3a9c3d8065728__6a1faebc.css':$g='$O{SB7nV?&/MUNH!{Cu5+ZjZR6J`&pVU(G~X{7Ync8o(BwyH>K0yTdugXCvDPa>s1q^LEW+D6wbvC3=Qkoi+I-{G&`Ns?;`/@_brg?#sfS$D!8u$|"b<TY)!il"W-3tFw[22wk/U4LlIHnm?jf7*9MvdK=dC|nTQCGgv:3%_bu0&^@f@HwVfT*RV,l`=x[&[Ri]BKsEPG+&5MJUp);B=8A
?C-&<+<e(N.tAT$&%10?A_;0Qp0~qZBJ5DmlAY69tS%Lb)Ia@^e!fl+W!i:5E1g63&,lD+*h":hqShqMQ?:.dLd+ff0U@21MKCvX-;IVdZ+Fvs*2^aO!u;a7Y)fIo}o{J45Wr!/.jMVC-TO>?gp2i36z2T:nmr)TKM^pavH0h`s|]PdW[7_donY/y@97
BjIyu/[pX:Cwmi}w~GiGHdE/cA_Yd&OXY[7IGsx?e6s<BpvKFV%hS+Nv6I[5^p:/L1+C#$fm2cerKu[sREw9t3UB[.
IM@]BW)3K{t4X6cjjC+wJ]J<A/&pMK6h0JSJ>^0!Y!f&7nmI^Q[44@5<IRI=Jm??AW"Xh}J1`L3HOqT1[BTyWYS"03?(AIb/_S^IDg"Joi_J[BmmvsU9?Ip;O0Dt%4+<m3U<^
D;<G<Vrs,OwqoRI>jc;DE|^dFg=05C&dvXcK@=%T7$_Shi)20a-a1z!S1-T9vZhb"*N;-+3f[u-e.dY19@k5Q`7r7epyZ([UyFm]`+<~ipSXPpT>`rw~b|R6,a[d(LJfch6X`tK~9qUy
#7B]-.SlHO`f$R^XU9a_n+m*rV:9ySj=1X0,rNaBQCXJMvUX3xe
MZUIa`TB.#B6Av_RV`FTwFVCZLu]2qv,innw=1Gf!p2K;Ca';break;case'default-orange-dark-ef164a8d58dc89b2719f881377f73c89__6a1faebc.css':$g='%O{SB7nV?&/MUNH!{m#=zi!"d!l`6
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
BNpZvdCRa$xAyW31h=>I6G!I;;iJ``s3ff';break;case'default-purple-dark-0f4fa03fa2d9287ef390780e90d9b06d__6a1faebc.css':$g='*O{SRcRV?$uMEW3
9kEdT+F;W]X`E)h),4#?fl%d$HA!etXhsF@cAx%aO.yDJUi9>SRtKXomE6k.ct{o?
60v;MG2OVBOri>(At?RXSvIZ!<r;UV9(x.V4"@Q"VOnOK%OX_C3A1pS+/i
wrgXL|kaWE2U&o&;9+;lEK-&_l)c-lD[jhXh``^[uf)Y5C
,A#pt<"hyY3yBla%
4&J.0sf&@*X.a$](HU>l+W7m?RFb*23yZwF/Yp1T]>O8M#oP;BH>f%V4)Z3|Gfo&s~F0Mm9/:H+;570W%<u<Q0?!.8bC^IL$e+WIjZ!<SnEVeiR*58dX/8cThj,@@Sq&X&xn).]W>=BxUlQ;T0*UaUNXX^*[;NVTK,1}tw8X,D/UF%s-QGvXP:qdEYb~t9B)4K,b1O:glcR[l@8#aRiIF4l9Mt@5s$dUcMpqjt/xtr+,A.!@+^8CZu0>k{<5/Ew4_g7VK+Edr*!EHrIvq.o8qCG/edn}L-;w<xHG8hxI_C_uOz$txca5Zg?WGD#Uc;9KNXDFeETWv<J@ZgHT9^:dZL
?2_a
89#zGj*u-e:CwA1j`^
)oAdN>:Emmm;C^^j!=!-nid`xPN%D#6h5rWTOajdEFh
zqM$VgQT*d/gbn!S~EV<G,8VO^)r5WJ%U6r$kOVmGNl@l!;+B_G]v
,OOn_dW3r0{?#Tw&q)a=P3vtQTJ%;OR>&Q0YG:x_s"GS5``<Ix1wH;|,ub9,|n;pFK4!&-j!k:@mCAzRbm,tmLE[s&4Os?RA1P[EHGau}Qe.pJ^YleE?*%Nqrs{y1XU?3a<Wi*sUM$&Vs=uk7$G[kdrllg(L7wAkxBf-U3P#"IN@=L.7|)^W;R{xp-Xs1]^yw5XHSEfr:fCnf_03_wA';break;case'default-red-dark-3c1e28afe2cc92815bc7347df0d5776f__6a1faebc.css':$g='.O{Rg7nV?&=MUNH!{Cu/Yo)dQ".A5QkQaR_hew4s:t-.w/XVJr`uziG+>W;Y@!~w<wpybK)PRIOuLgt"~Rl3?(VC>&-c/0b6N
?@5i-JU
H<Y(8p/O[$99)!wVN^fP@*5n^)W^xfN`&J[rJ!ZVWr|:?kO`^_)oi.;)/E,lyp/,CWm8&$@U[S~EHf/#jjn&pRI11K}fu#&*Q!y[V9^CpOkrL
<A3XDgpF<Z%Czg}YW-W.+Fw&P;J1RXWKJ$96![[Vuuxe$F_H.Uva/_/=2U)=]n94]J^Cg8f>Jfu5iJd#=3;*>x*N6x@OT6
@n-;mr$(VYLs3+Xg=~d7btpY(+RjBGmifCSAioOebxrDgk*[;XW8K4$Rv]oK+$/%@QtQOYuzZy/^EZLntAB(v17c0$efg3R;u1L@u"[SGww*Mv56R..37in!jtQ"tq"ac1N=24U!(V
laU=XGh2n6siU#Au{#>+dn%Q>efu]7@D6tSc,/#%}A[ih7ZGC]R-Q-f8#sr
V?HV"dN2//F8TCc8BV~qMIy^,a?_BY)j<7,Vtwv:%Pvh06TA<)]/*?[kj_^>-Px.Ortr$d1(;R7]m6AFk&iH*GWL(Cp(E8VV51)n@)AkV7RM4>H36[kPi<a.(@`r;k|Pm*4PeFpF?0u*RxU%}dkVj"`kcrzaiP3`*:IK^
SLuB5Vb=dj
1Dvu:GM$dJUCe|"n:~*F1C9(-Pg&2SA()m5b<*cJrIL.y$7Tb*pn+)j5:1?|Ea>8(Phah?itGWAF3`G0VP%#AH`wmzQwjjD<"wcgJt=[a/S}^gQ4+HRjn>fbY6km6K%zn<r9so]=gE;[>TNeB{xI9EZKpjV.MWpNc3XxEz3D3:a6TKjpcl!&&iwAN&';break;case'main-c1727fb392fab162b6611efe18642a5a__bd555266.js':$g='!`K]`nsZ32#tW"r=6.v"dSd*pMac9DI_osFcDC}l>Aw<Ar%FVN*VN>VN0"{AQ7|w,kBq_YCVqMRPtcWS2B-bW?G^+B
BlmHB,h3V1lSR.cwub:)Xs20E>./v)gB^LAGs
f-7Ir_s3*53jIRUY.i6m2(7d+sC_vlr=]5;H@IDIvHY@GPE!2+=oPjl
hIJrg^K(a{&HHD-7Ztyluh@4S:<T>|(q-_/w-2yN_fSNDwfMFZWcuI*w&u=Bu
q69$F,tB:46!sP$JXE
/S!BLi@Y
E5#ihLwhQgX4cY@+-x:ZpiY+s
K<5.Y7j,_@??.y6wwPt3_tv%E#
5Btg]v;>Q*,uYh:>tQKi$fRyVYAB0uPDA2$b[A+kj72`R1a0r>#-_r)e]EK4t?kpam--JgD
&iMJYN$OLwEoIa>*d]03AwXII3-ZyCo*&VN5S3/I{xQD~j$ua?VNdf?Fmee]lP!d!g*=~:=-5:=vVNYv~C;=8@?H<v}cT31<2_};3iA:".&M2.~!IupXej[aM/#MyO"7JtQ1fNUrqA#JP4b;#x#$2c
gwM$LZqXTS)1
J;<In6.IA_KC""Q4d_Mlf[;&UFy>5%d8UYxkHTrPoO,2S7wBexrC$l2*C)"1Yib+rv.)EBg^(Bmt]$r!8ROA~e3Xo]LS:#LDEcFFM:fBxV:xrWHta:2E,DwTrQtAr?H.@L3.D):tq%GqS*24s")6vCsb$6j60q{Rp0`1CMjgl3,y[pn@c0_)aHJyP1J02Fr*-,KLoKny`6+AXW<]
;Xq0b:FdL}N{g<$211oSv;Iw(`0PO1#[7JR]FqE4>V(wB7c@Brmh[6iU>"vC:a9JU*I{yC7l.pT6c~@vxfGEdJt/I-fzUdC,<^#<q*vVF/Jus-qn,E1MZX^},}@u<
f:(-0%<_$HMdf_-]_xQmC-xf"aP4cxuf2MwSnQIsd7Fzn~MNe~W{a<.]EjyqL6)]4/(,nQ`Q>]y|IfMB"oCqY$B0EV6V8"2f)IPce?%*)mAst]6Rx"%
R>X]B|mjdLmz3(
e^{TCq
!!IwP.LS
S7oQe-|]MeA)[:;"qj]+vX=p!1|bXFLy.vI`&1MvMV*63w5ZZPJ&3/(G{8Wr~qO#w"sF7nhN~$oU+iV,4^8?gE3$R.FRWP|h[TrrQo`S[>
$hi4F[9~+j*e%")<e{llE?2Ve6PJhQ5lkO>4.hV;%oQ`v_,N%hs
CoCTBc^+[UuWpX
f7h@;.yJ+=o<fA3`WF`^`0M[8ead4Zi1rg+iQLk^SV58STQJLr!Bq
3j^TgN^xoy"=cKMMY59EBaVHQ&ylc">nv5v#V^geqg}U53`i1)b[;5S.P<kCAe<U;2JCl6pgNC8o>%C([+Qxn0J
|7b"m9EnY:wFR`D:jC_RsxC&t_:<K5j5&siFM3<KR_6Im7uGIxku}I76#K?NmW{A
?_nFZVX.WHXqGsj
LBl}c/#43(clt&1u<r(Q1SVmJDz(]5ni+$tI4I@eo
g+38@{[{e6r#*>K[+_O?UU]]t5af2uZS[D&XPy$v5";LVTF9l[([F`rORG*`CXUQb
X
p8e1=S/Q*150$XEUI}"C-I?f3W![fn7Uh=sUBJVT%"fJvdLc6/`|B#U
9z0{QF0HXotr0a8Cre=/=Vx:1,"f1|TG
)qiXMRM.wCO,EcU3Zo#Bk;h;cTM(1:b,Le*jeqI
vL`vv2JwLm/of*K8(B=;zo,85IPu(F}b7(>^^_
318p"Q>4^Shg@o=Y`^.LP<ZL.6KqG;7OC@EmIJS85himFu_z#5HG!>G~L;MvWARSrIIB.yWRMt+3"Ij]oTEa,+,g_GrN^nCSkJRgl6Jm/PFDxU",#C:E"["ZWaZfDJ:OZw2`Fohgd2Vv$t)h@3@%!BRG?#yl*8xbdEitvz)QA*.B/rO-qVQPkT;0bVdxoY5L+SWk=Z!B`bHtlqWQ/m`W/<C5UC(TitHC.|LQ%q]G(5gbcRP5a#ucb!5;A4b4BlrBGqEA!+xH"^(udvDWfq!{xJvH:L&%Zp["p{o^fPpUwJyOju7G%yR5S.gNvReLw=jtJ8&=T:oI0e4BUB!j97RjEK:?TL,Au$Mr<sW;psf&EU*<T)/=J_TqnUs(IIG-Vw*(A[0nmJug@vsuiw">xknz?}aZX.3oXv-,Z)Ji%6Dp^_+uQ,c*CgU<<E50R~DB`JW~`?%nSnEmd(DK=$"rr_eLn12ZbvVp<8Ruj}NWJ*tnfSje:J([&*%/@@UhSw</Qze>.x%@&Bs?10VQ=gQ#ef_U6pNeF9Mi+$03SsFOB4wxp=;5*a(*[Ml5Hm_}2M.;-o5sxRtX0)aRf~7XQmk)r8R+O14e3)A!T
ut>2;hQ9_hhWkPUG2vEJDU(l(#N<M^Y
i?-tKjGno(a7]jYis9,;H)ZF.]M+FlTtntBbM-mte(#^Cn6>p%Jr6nFMtsxFR&X_x(3P2
q3jSkqB$:zXM2/gd)}xY8(;`Y@3Hn,kG]Bl18u6BIxe;YdnxUS0(x+xwB.N7_6mkDlk3gMZ_atq=(U>i1#M$uy/"vFyF%A!GS^=*H^@lN(Z6^_i$%,VJDMZn$3$~%XP,#z8AqK8?-@sbc?<(a%qU18IwjUU7lxPRW2kMx
7K&;4_Rqjz#%k!WHdivR;]rVPPEU>2^BJ2mkC>qrlfN9$Hg{+}w3"|[Yp(80+l#8!qS+*|t?V(n
.ZLImxy%aJpX
+.xt,oZls)Y3s)1Zap6AK1#(q"lwc6P+K$rGZYK65bjiJcfXe(8Dl?s3X*97vs|JPrhIBVVO~#*
|Hiama8j"R&=myXQ,&b[&M],z33T~R_vXmN,VeeVq<9gD[xo*Una[&ZQqgMx+oJpmtL$>V]Ofhmt^Eiwps`qgXVR~YVo.U]fR.ZBglQ%&^?KJrPwJf[-f
ClQ=@XfC*E#>$e+kT&a-zc1N6"B
Kp.5`w3BR5]96Y,NEYDItviW{TBO]b7,%cLsQAC5cJE=7!TU&A8?+/!Wc
4IwAq/^#4r86&&v6[x%7)K/x7%N@("iYUmnWv<EO|"UHB[L7%[#T^M*7%EaFz`q%&>%%4p;9ELupRv`I[(CwR)l-JcoD*/{J)EDHRW
b}x[
jNPh`dV:uyRlBKZJ%>IqZYa1I+Rs;.]&r-CrJs9W[)Ero_gjWPJhY9q+xHN>ELz*rl5-[n[*hBo]d`4:tU6tMH%nipO=x%bvDrnGYfSvZ#|Ns&RPGY,TvqLtJo8J`PPrQd0j~smlcd"y"lWPK@hdg2L+O[DbO0g6j!1u0ev$dee:nsOhaK/`:DW]FJ-,QIHcyf?qgycw5BFv[KbtcI4K|PGS[b=Q&U+@djz0p0j!GO89(9x[-:>i=_p?]L;>9[O)}46hkNhbiUr:*vzeJxMehd,<vq
hejnDd-.%f27+cNitAe/149,IlTz/Lu`j;p5_Td:56k@iSN+i;u)-A4|l_uT]/h64[@$dI6tZ7Jpkigw#[4_]TX+qAJs9;H%2/(_7DlM]TSVRY=Zv=XAq.F6mN>bmgEf+nM|n5^Ej]X<EwRVi(E7UIVq&?(D5JSY)wWCBkn-XXkBeo
f^3c=WJI%Q(5,:QZqg;?.eZ@xAlerGY$y^lR35&7Qm=q/K*>`h28M9[U[5z,D;J
g#9kZALly].PTi]/DhjsN=~8I?xj?)Y9]J1;hb7Y1KLw4&A(/(S6O,iX+EE^wn4WYS7M9$L#:rqv*E#wA&vgbg?x6B:F;#7To/4T:Q"jj1SbJPmtIy?Ox<R1>12(hk]4mTx7kU,n}Nmn2uBT0$ahky#hH,osKQ7620CUv:JW
McH)/hPzaoYKMedtkOEngM:9rzp6rc@ZjrsRpN;ErF>mmOG2pSFY
O&{c!0"5*?3Txh3eF-|MBYi0b](J+r;jAOWcDSBlS#{;s3<S9i^
c2wW]y[_"P1Af%7[,W;D-4`-:SV*%g_Bf3)C[
lr$iF5{2#:g8b.2jM0zt&CM%u`k#;^16AoP#E^}S@Uc`>>u48xj&b7qiKQ+e;/EIuq>2Dx-%RW23Zf,N!vhOYG}KLu&kv1=D(Q7CuhXw0A)dJC=fDm^6o>p6Y$
E]XS[7P:^/%B%]Ae[qopj25$qQ7Ndqh)L}k9%FdF!DA7Nb7]60wWR!SGL09h#Tw5bZv3X<gny[)Ut+H0opDaCBHnGh.%s:0:&
@Kq7Z@3a]ucnPfMDjS#MNH,d:S2yKRfOFXo9bwK#B0gc7uFsIM>~V7%E[cr#O7L3aU`QOLL#B9NKnz-(Ureutlgc#/oLR}C[e!DnkZTiSXtPc$
M$Hvz[YvZaZaO$60*N-WhIis~w`!H:^phM*j^x!vXnUnnoF@>s
d-77:l:9M5;}"kt_"bqjAOuwR1Vw<%MLFQwv/g9G"f]pAGaTims*Vm;+,C"&pa?WtK0$[|&NS
3:0*_U8yH!(S))=APuPuSL8`D3EodjbL#68[/CW+5w<h!0.-[6wGLJekk*l@i58IX*75#|jDi7D?ytW-E|HWClyaJBb!y%BE/XFGnx>_.ba;(6vud[ITU2Kcg=#j_<FU:wTm)|u&2H4gH"II7#?G!Tw!HT>$ldaEXzh^roIyQ
F1
hO55#%FLvB.nSilwS.p@76Z7[0SD18?
{[p6q>m_M5Ma3]xZ$0h0Zen8(^WBegzAA6cx61!WJlQv.IpesKK8C&?/oQ-?#prw{xyl@c/>F.!L`.>#S4He1!@bYMd93a
Mr^jI!L}ACMD4G"C#(#xEDquht?d_3y82eS&X)`Y.%1&IH*)+q!xkM^B2=X~"8qrW*@fiwf&jE68g{l=6Gh^Fu76Av=^[9(6C~JC0e6<@eh1V{S((xCulxVLCo[:iAE=6|)5LLVQj~$$"/!,)c<adT#dRrM9B,BdP49S3|q"xy:4T
6Rs1TS3>1=^;%m!@0!o)K="z#z?^.0QNG=Ule-Y-`/
Wa7ZDg1nGM4Ea1:Naj5ZSH/kf]TA_=M.Z6(V3)H
9Rq8YL+w29TasO5TOMJ*&]v4hfyW-U1;g#ybsq//AO|q~ttp7)&K5Ez"d`WEv/Zj<-p1vv5YdQ)<;B34s/A`D?S2^b
/Z!2OFpAQSSkrmtqj)V]-dE/fA$Aez`$d-[U!tgn8ifqz$!wO=
%YBDWD!Ux(<3T>u[apbTmaaMd6a7,uap(bNniAj;@YAey!_A]f-WTCsAHYj&!UrY`$>Ahlgf1(b,7W!2t$JEdp=2"ym56U_.yU&3JO:B[=Tjo&lwy`J0(uW^SP;W{*^)liy:gm!=F#4TEv0B)&SN=C&"Ijs+!!oHN>e^kJ`vf]ly)L{G&T$(u,8G4pJvLqhll+BW~>pI#p0E8d?a=@Lssc0`t4w&8@nCB_9k
UaO/9~OJ`QU})(:y+
((Dyo[sq!R+=&>n^S8
pYL.:C-]*Z0#^^t_ClS.df)NFUA/|"o!+_eqGD4Bb*["SrKh=MQB`LOdwM32_X(*9@MfUe/WHvglIg39|+D2=/J38]n#O`=Fz:=x-s(E>Sq?0fkh6PMT[0vZlebDVY5KMGUeoP8tq,##zBOY~XEuhN{/aF_M8/wZ4"[w%5uZ(YtHguR$+"w8@I%N7tvSoxkn8bA9Hm3T~Op_
S6`4ry:^so,QOYb=?F?qck:d4b)r`pmB&`myLl(D!jB@;OW>;VnW@=i~p#/#"kE[0@<o$7*URaXk4HMqSOn$o`3#^`G#*vwT+F*_r,P!rZV/F;5uAmCVkR4T=RU~0CE2W3.pM.MnM&5<5uEo++[lI}g@Vmp[AfG_b+C0xSUn]W.6?KK]lfccj]gjlFO5o90pGgeJ7shGMg_d6O9?x{=~Y<(D`1mN*Bp5wZ<"2ERWMd_$FX;+?O$?>yqS.NJKpmF*3OMU"zc6KKR:]sq7+t%$fm.*H`U{u[3fqOcPgj_c_XYGu`aX"Z*f/<9;DcoP
,f_d8.#JY$x4Mc)"pvnY8?wRS(:K*JKZD#P*^WI-PNwy^
xqfYe7`$
phWp/#+e>(&t@ZLW9fnh!}F_)pS|lj,vt{Bfr?yD6}.AQ.az(6_UY"J8m=#I(1sB`tS@>69O9R90ddkOZ4[LJ[B`4_9lXf(q&0)-QTiGs|j5M55&ug5bbuu7M[-]_4W,lQQQ*jtla%tb#{m/)#Y;,gkfZmshlsu9
Rh5b.Au`FCXZA_t_Bdm"",/Bi/Gq
QMVmecX^`x*~gd>fsADEyjyfLEC|r_GTv-6tg*Xn&$@9>g&;
;m0kxRkhBjo/1XVAzSnJN9xh8pDW|Nl&Jg-8.XU,_DG^_I}4=lOr_fC^Kg$/[I;LaoBx80Vst8w(%Qm5{6_#{`W4RS/UV=cC^G}]T!XO8S3G9706=BYy`NSrq.><l`^3e%X<GP`q:7Zh8MS6D/RLCe{Ly${K#lz"n<IjiTs+5Ul.qM!.6pVG*>+?[Nva%P"T]r^@43@m|
.>3%Cr*e^k?LJj_IKl8Oo`9.1`#3Bj8qAqwb78OXH+Ko6#[@Q.HL(^hOlxQoTfD
%*%L~XH7JH:M")(MGXzM0)UxbVc]z2OwQgGak)E]
k6im>S=k!Bcj;jLr8b,541!v%ymgV1(He^a;e1L2wDV+XDS7va3x+{f4sI@WcKg>BEjl`4wQrnyg_YqWXh-)6xjE;Wh_49Pk8{xZ"UA?tC=6U{3!Be@`y>WE`mx=$D
%_&_L$,m5)pB#`rA=N_[q,9Zr]0rH>kb4GA!Qs9/P6{(!+im$KWIQA4
PXIC|J$uw9vre=mN#k<)`)ZyCSH=c4]EUp{?ZGHKr?.&
0V*jw/GN
lC4a3IfTB?|
|9=0kuS
}D#LqgW@4*DP_h;/X0tQ$?kjx%1wjLe2,
V#uftYK(8(D,ul;VRW<kRj
F0Gsx$nv#0B4bp
FAj
Ex9*=g05:OL0b`?V3BPZ/6=-:Ud/wX/;XeAi0(.%GgDT#_$Y}+K#$iHuZV0]H?:)Y73iSa$I"/qrFM^S79y@z5{SQn]_`;lF.dDEU[8Qe_boWr%M"#+RtVxoZ3]`*6ryICTu!YBLJp?Yuy+6kI?N%ZQ%Ni?,_-=Amfv)u$GnmX~[D<.un(M2"IN";Bz"c]U9Uxrss6}N.!OvZH
K`d(I;/TMqB|l0_VC][iH1-)(]x*V$;/:tg[8TD4;X=?[R[t1Az!F.;f6&8I$/yRJQpX8lt1Oap|Ol.Hqq&?P.;FToEGS,tStuY_$F&-@f>q_gx3&|CPFMci+Aj#=MXz=W^f"*oAZXqm*5;qvYe:!NmnfQ?6f"tWK+>A0@tHnerd:D)]m~<V
@cgU
`k=hATZAj|
{S5M>X5U/<9S$F_PThg]f.dj=^,=>3!#;;^PN+`Nh8E`3]<1lj=Wd*`&(p33D,#9YX
pts?j>-qEc<U!Yv8*OQ4&~p0d5"ckWdLcQMl:i#V&?)u"IuG@`G-S-o`t(a#T>^pkuI;FVX69K)>M$%(t@/ZPXPIUUNhB6%p?}w-q*<d@XRqcbc;#u2(09e6or0Brq(p0ve1_mCO/tlM<EI9KPHuDNY]F("}F#Ew.}P4E.7!:v!?Vzd-/U1s
1f*Bt"%nqJI)wft!rOK<pMHyPq_$f8|!&2Ub$%4YK/%,8b!?N8e=F63JX/sAFjR,?kvQMS-mb!MPwk2,J4jI9B&jrNRJt#1QYc<ttJ<D5qLf}j+78Hoo%K,TMM2EH#BrfqYo/e^]vdgF[^>P`SkgHehZT<Jr,_>N9a;D_9w.^bU)7pJYvbhf,C]OoQ1H|tg*OEYdN-ulU^ts].<f!b6/zq?Ek&Kz"+LK+5)kxnE`za!iFi;N9%TQcfSPcC2w5GF@69S`L"9lri<1:U^O`;s;-tA[HH8acI>Blc$VmK(g$?|bx?N4otnU;k3IR)rR9(sgp$I-|OsuE5tT:S)CNwn5(KiCLdDAp,nY6Aj7G].*7*@yDihL<cHU&3Uo7q/
0M)wdc^O32N35Q;vFqf4s^i@EfFA7lLvQWLFa?XCB1"d8)P/v(*VG"qAM_Ay5tFq~["Nn0,B-?O+R=w+/n~/%YbbYd+kq>M.#?8M(kIwT:Rp`ijlc?&Ge#(Rt^{meMm`JK%B*^7@&rqt@it1>Wr^R;EJNP<vNc=rHQIJL)#C:7U6&aI5v,S)unjR/vfc`k3fHB+E6kl#xYZRk6`!5?sF?+3-6LMJE<hF8E#rM<ODabid.>Rnb-c:!Vj`0%Q2:@2wX.&hB9@n+]O#x)]W&#}q11dbQnZ-#"/S9^ZBtwXio4hBhT}TG-Ddvf.5;2kF>5{A&f~jr1t&9]KO]?<+a4:M19@=~qAg}P].S9,g)V{hdY~Dr"T,pnjYn?aJ$"e*T=DF}3)66<o/(/XuA-wPH<O$gK%Oan"A9XiTPbzCA#!lZQ
u`yL7gD}2/H
COPPJ*5E%XrI:NtJs=P+Q9>?HM2O_`
5+3s}.TSzVaxb?+_i@cB,Q/s~.~X$&,.vW|Ws<$UVO=Si)/.uEVc5Metw=-#p[q2vCstCGQxvk9@`([">%0"I%*4o%,6u-C:oVOPwat@B]Q?~V2.vCVTn;&nd_eF$uM_Gs?KSX[pXy6!mg2lD_|SX&2;}<o_W=!lz,Os(1;p`v6O1T)SRZeoJu0M:;x%$jEKEr(t.DRw6
h<^t
o7v*AxeU#l?hC5`#_j3SX&"b;dv`k6;Ww`sY-zVP
8b@jCl8%Z%vCkFUb4T$S7u20r><yg9!CMiNVI5mNjlqySF4a3wMU8%FZIS6!po
mp6GukNw(/`F4M_<Kf/d37dCwcB/Da4JC;y81[T2l:PTa4^l0%LA`J`!-<8W&8nRnG;r
lu}2++
nm@ZK/$|5nO}>M#R:("O%8y9ScLkQ,7,8r1ux8IJeHwxFl_Hu@4/!|)xpbr%4Ct/Unccd/o}/wqWwZ#af:Au:UIjNv8L;Nl3&`[I2n`;n3
RWu(s
S3![]AN4nU`6HT>FRw#,w-Z
9=rb:_{vu5@"CswEf3IVoWp3GiNrk-IXRPp65?J1^FyKFM^(yT37Lb6bI`&e$C>oW
0r0Ztp&51Cf!!QV!c"}KDk{][(YW<&fr4-"&TYsB5*i=xF,^yZJbflQH~56+#f,6|g36;[o$Ytmq;Zc3rj,#d@CF^]tuCp,M0VG##Y}&X&I1MG,:0NzJh3G"rvOlZd=f.?#iSrHUsk1-7-i_76wj[qka[k#MAxc<fE(."f
h(!jJfUO93Cr,XI@L}S/
MvV:IfG43C<@TmP*r7X*qxnM%vIx^+|&y6jx<YU(HOiEHg=4SAHvH!e3F2YJ_y3B:"D/4a%hS#|N^!+h{,b.?j/rX?pf,x],P&bo##ACuuEEDH$;15.XYQUQ,uueJ:Ma0REhAhY^T1S9-P|;kqMu&:sJi8uUsq#5nT7k{08I,rUrfi&j=O{OY
qFqLl_s
r&`/SD|L)N~X&pZj&vy.tggvE]mFn!qU9O+f_nS[mog47!u<sez9jwwlXXQHE/@!*N,!4tZ1X1mp@k=
JlSh39eZB%nP7Q4xR$Pkzioi2JGQRmEH:q0DLK7*!=;:m2Fso-9E"+rGCGIH|E|Of:;Bk-ke;SGT(R>X0ZPfrqv@[_I
)=.VM^iu4o(_.(VW/&C:EZO6,f!BRfc]oVJx1+Pmz"V]Q#r2Pl5
foynVX=xf"*F<,A"cS0gRz&5,,Bpl:vtV/rxYNA:j.g%U`;7K01,Z8[M6_euD9LI))~PH=ZF;vv);<Ud;B!Ws6XO-;N.i:x3_Pu@o62b*Om?w%UOafZ#Fk@RwO6W61-^Y@U-~m51tXsd:$MZKQ./;O-EkiW$11g>c^^NN4j:,q2<X.X
"J;5J$n_0nemT1J7S2#/WWWoYQ?:A`dk><{-Fx4m^S@0KanE.>14!!%PoO?!!f(myjg4O2Q9$Dj%c9+JdLyP@"b^ZX9?D:s.FRIqkpOo!`!m
uH@[:|2{NUw*0$2<!bw{<#U2X3l"S}aJd>I-%1e@?|7r0L2h#Y24Ks.s0)PHG~9?Yx]Uxe2K2xcnR>sM&(Qbt}l8P1@MJy?icdWDh1HWPwY!A6@-y7<Ue8vCf=/5xK_ck4@^eR#2S:ch!Gf"Hn0f94$ku&t1>aT$[0$To}@9,:ghtgI6HTJ#B(=.2e:/.NUg;JmfN_My6YY^b0ggK*NA
>@+4KFaVv1B%f1
$1
gm<]xQBPaaw3bS^tE6wbPNpMq8V0zm-3UG7(v,+3#ApIe9yU4fIwP.gGiNk<=E8Q9_%+YPa_[@#pca-TZn@mOfqeM8Ip{!ok_;zd5ZK:Q]}eg+#?2gy@sYbx
EaA$>S@J;%rl=(iN6Mby3au2D4SkZhpzgOK
F[!sr!&Y2Gd}A8XcK0<MGp"49SO&$E-C_Z2C5}M2lQdzoD-ym}qG+/IFQcK3e(ts>l`:^/dkm>t<:h;knEUIszROK+6oG5k"_}Bb<^X;QtkShJf}iBl=do?eg>@qTmgA9kJ5[zS+[@={`Ifg_;aVyo<u/Fbw?h@}R}+D]L
Edar_/GBww>d5]i*9;Nt3+vun%,Q:#U$W74D{;a%sFK(CjNw/8w$b/z%G^A*|a=S`6*G<[z2
YoHQWG+CngL/t&c8i5ZfIR@o*QVt,[(.nAwH6Zc66SYWCRE:4PY!bxZlRd]DH"9A^cJ!/9vqQ,mFKSXx4@R=aRfPyOnh93)^i3<)A+[J!:Fa1$T*hM?PqO[}mw,[_v0qcP@<aOR"^^uQ>g?o9Iy#y*n.TU66?,N4p)Xbr3G1tS)R
;tHX]Dsj>t>!0X9W6mqK>$JFel_[`v7PY;Yy,]CXpqNSJB@a]3DJZ-0MSl;w%;q
;y@I_Hc?Ebm7sX%BR$)nzt$K<TxGHP]fdPgVF^<B[DU#hslc>[J]#Qd_}@W8%B=/lEeGZYy)VQ,3GqhCO6;*^jn[f_~t6(WJCJGVxjcd^N|f*xL-5%j5=]FoIb/dR]yG|ML>m4(OQGN&nfbg.T2T&.U3q;n@|NTmLg5Vi
oStZcIaf*uCIUDxgDM@!bnnI`Y&:pocaVp3<34cvFqp7sX"uY#2H@*[M3`(Z).x2FjT0iq**4K9oq0OD[olyk2&8;Ithq4hkqF>SzoWN.M|D;3|ViZQs=XCa6p5&Vp9evlE+2ui8DdOF1]qg9sU%rVxTkEVHEEqT4cN;+xdvj_FI>Yt
|pta9l1.;hK*9/ltY<!fa1
(Torb#[NUiir
,3mvgg#]/HA2#;fm_PCy"+qvunL!l)vyfJ]])qlsZ.B^qQ2UcKZ)EQ{Xq2ht,v.,(c&X}A^&#>)GY=K4
j2uiqZL7/BEWAz[,^}+8&193S.LO3$_%sZ/d[|:rG^EEBPnfNNKN7;_VBAoM!CP>q6/kIAQc)r0/ofKb%~gB7{/BRKT6Cy:IVLh((*4+9PK5dxkZT0*MK^N:,Rs=H&8]66B[uaHc9tj}L#0y8*,-p1;&_HC
ne#&:Di&#&Qq8]l"s[qW64a@pThI4Uphox>LP#T9@>$uy3?^k0h#xwaQ31#K7OBW$+u{YZpbLrV8<|KO,~6CpJv
p*^QV=t/Qzx`YtnJ<"a/2M:sPC,yN&/m*fO>y*L_%cg?0L&*V.%)/<nBQN%YN>!pN.5{5&[M-EVh&>mnXNGryUu(7)=
.q@"RXM3FY&1B:0QmeuwvGF=4VI7*?9Uw4m~;!/-=snWP~=^p>KC>f&#J:VI9g,|e891ywSDXx&wRPi8oS_7I=>AEMribq=f<Z&-`"4,Jj5)-w56H/&+dUA*^7Vo.}O#^q@~N&HfY]l@L+rRTdbzZ&I@C#jpBv5H@a))Cz22^n8>^2L1SJO@=AQrsXh}!P>sww
i$
uvt@q$)^TC7k=uxzQ>n4*zf$g`qSgH^
g`=z3e>OmLMUN3eB!f^9kbYcihG365i_b!->ypZ1Z!j."l.kWT`"95Zm`19L/n04"*Pa<oQ#ssGlImjl"bNsS&_tZ@q~`m/DBh6uY=T=E]VvF~M0JMj9<dCdXW`slX!%6D^ai>y(qnyrf+BX^GP]j~%YE5nRrJW@?.cv3BaTULA]O"SI$3!{d["*g@bNd=oWm]nI2NP$cR7}qMO_4jQ*")Xl/x25b~Q_"1t,WDcd*,p_e<Fp7hP9x!r(2KS|$!c,(E3c5~P)!#BP_T0/[~M+#u%{D]d1ZHntjp7k6p[bLQY90"Mtg(%&Y#5bNP]iD_X~AasHZe!Eq]:IZC(mw1mB
_D4BWXnz&r3$!4herMvQP3w3B9gYZ@;1543hR"9)d4|Y?eLCaV{3cAm!W>A9OUKg8JbYR&h);y8HLE-amuBq_5juVLY8[b4[PPc7:gnoRu)R93aVJ`ke1ADp4K?Q9<a^8$7YV]>mwY7/27X;-?)#0
V>W(~2/Z@c8${h@Vt.yvJ[Ykqo$-ZLr;lkL#oHNpXsB*qW4ebydA{M2yUYA&iA1Kf;DRhjXc0c=o+%g&8A#q?GuPhGWvx3]?xM;Ufw,ouB)m29Gji>a*-`s@CNRYmkxmL<l-UH(1tv-.A/mb<p)G}j%o.:#bF"90`s<?;Pol1v0^32X+uirN^cXSe4atH>tJ-)qZHheXlwe:3nYM
!DYzh@;EHP"$X7lj3PMlR~ghMXEoO:1D&{Otl$!N#4"`AUe
SPmE[~6{/fOY*n],Ii^iI7gbBa3{_$pBw27KHa"T_tbzpGKyi}R$f1y.ab
%onHu!<t=v<H1v)7ZIUws)mwok^q0LO*"ZqN}T)=rr7W~a01`[g<}qVuwz$C9wJQhD6T}&@[h_JStWY]@gpeJ_N?EG:`$*;O#9IAos!fQ$M-<(4FqoC`0FmHH7
KOK@+P_oHws
=DwBg{3/YWFj
z-*lq_{SOpK*b5?6;RtC}h&r%OK`gkK)WNT"7kkjP=L4_qR,$>V0CODKt5@B<QC_OT$aIXw.}uS)h
@
zmjyL6QrOAnbFpK,irpS&u8<Ws4vw5cK@[|AqMT,/-l4(.O6V]=Ie`:DN.4%oe=7[fuv_
7@DtEP)TtaVlx^jy{fPE$?fku$W@Ke,Gq^Gkc6@RTLalAlulK4JIJ)=gA@B)|F#67T]QIBBoQRX8kUSN"S@o%i4RRMf`+D@v[/!K![ubrvDiw!7Xvv3fdesDGVmtCwm)zsp7?e/u&d4Q.&LiH)Y#$X^=tE
88eHf2?U/7G-#<*-N5qxB{+|E,DIBgT<$_hIv6y!w@WIS]+]Hw,)iI.XOYIeG[#$oMudH%5/^/PeRAU"6s[j$vf;4&6OpD"?lXmEhk7:^_O3W%9P5b$GR0SksJlAT>i7$@CPClVdd@R|69U6-o0O=Xc$<as<F+&[LYn(3uji9L.T
G(Atv
uAB9?+
k`yQ,EqRn;^TNsDMqX[*N0M|I?fr9<#7YYA[$D]w&e7!rzN.Ksx-wVgs>kUI&=V6_Rs$/L&M>_QhMUoRoM3RD
.+%z`OBL9xUu+LvB:7y5f,.gM~0
&0n~QNt^H<&_%r9
S!7>,`>BLcJmC&fL5D^c=Ak:!?RQN64{hiE$y"I%3xyul{ALh-FTuk=HY:`R7v6.(A$U^}rVcQ0C#!-LgsAF!%P(O
:IW@C)p$mtS8O1jS
)w5)!KxG+`S#,!:,+Z]"^^&ZRs5bAF#lR;Cmqf[a+a#pCbcmmBR]V#A`@gM@);,IZRTkgIC/>?Bo2.*M{qrDjn!XncC-X;tImUH:P[eMv*N%fq5^6;Yo#$&Ey<]PH`Lla.B-ue6@d;Wr6btm?DLh{=?[+"S%)!^8.CP4{*$c+JAq/@;B[RX@D$jv;sS=sVJd(1;jC!b_-0gI&Ir#Lye2VGNdoS>2=#d1M8i[DwnSHE|jj<(9iL8-vt6WC
HDBqV[=n@V:e^Mz`wI0YhoEoDloI7ghEj/Y[dm)&`Kjrcm-3b4j6ZNv-a>p?2t_BS/`qq#70%xcj@*)M`j~71t#E]-[!x(WXc$u]s-VO
okc,h8i(anfTM+R,Kz@]K88ekLY)^tt]-xM5KlK+2Cjidf
r`Ps-,ZT4,3mfRq[~Wz2$&?WLYs>eI0udK`h9mLFWv:0c5zG1[jA29Pv>V?CK)*sVY@,kIo+r?v75]EgErO
xr}Ap=$ke:Dh/+<GXJ.bXx}sJTBx/Zb/t8[+<`Zd_Vu$!*b)4F=W5sYRn:DW8[~jm"^6GOU)i/7E>+iZR]>jD[Z54F$P1wZ6-u+*;wP7}:_da?9-XhFFjV#.#-GAz4rVgP48]!)G=Jw68<1JlWaIwY,YIU
ql<F(0,AW~YB>SHMczR@TOK-lVFv!mAo/~>Lun,Mty3TNwf4rfv.Qx]F3^3r5X2:/."/*{
6H.o9=)LuCpW3+)U02QgqVWV,nnFI%Y,9x8&LS@c,Oe;~MM.#F#mYPw>QPc#-8qA!H7S0WI6$4kBb.:J}4QpGvsN[E;:~:^<kU(va)#y_S}6Rn|mpTv
]#<-y
6x%p7"asepyl_#&T0cZP2J=T]4274EWGe)Rp]fl(,?2NHaHs=MUa+6Ej|u<,F
X+?,BFx8E@D1gZ@.{@@:,;sNwQf-K;Ut,+lEaGFYdvq=<tUQ{#~?gB^ADC1G~@
=jylp)!AtR%[/[:{rsF"AAKxnIf9:hI~b|Vp;,wzV0L{vl;4/hT;I+k:nfrEQ8*b5{QP0s5p#jOp0;d_qO&dI1cdPx4p33]G,Jm`t8d,3lrdM[,akTu:vB25X-.0;h"4/s`4<<*_Sst;=a=WtI$xm4[9;:$>w]20/q)h=V-`c
dW9+wvhQVRejm4%7t`M$C=$I)-Y$[dEN>B^W&q9;`,R$U$^`m|ENVu(y@
F?MV6j^dK@qeW7C.s%1y]d^&,`lF*,vVGo6ZidCsTgAAZ
^r9!K}FT/vuO#$QTXB26p7#l>r%b+g^Y&h%U6>Ocq,Ukmf>m-z2,o`.)G^eWoSHx2W=4)sQwn}dc,u
@OSt@/":OC/U{[_K+QS??V>mB;a;PQdjSe+tJW2Ax`Qt(D5y1j}"9x/Js?mx.2iaNco.+Xy[v5QRyZf.NPd>CQ|[Hao1U@45dYDPd,$DGRzSU9og+maqy$`v>A#Eyv{:NN
9L0hb~m}9<8W+l=BwSG*R?M?oY4vb
rMFPrq^@m7lmU0/&l;C31n15X8.O=QQX_3r#N@1x^5uQ
~r1u7Xei3QQV_>{^K1UFKR}l5x
y_1["]`2^%qx3wYC`iYU7<$fi7Y;Tf09)yQqf7(dn^T-Rm(7nl.q/Bu6Rh2wS8acKP/Jq5slKeFf4[eX`@<*k&&fr<@ne<p@`ljh/iGi<6Ri>)MYX5:!*fCi(t3xc6G$336=w:fex7jP:Hxa(;p=msv7/M76v,$YpUa5j=43v.WKn&fibIi@/bZSuA+]Uv(`E@3j!b2g6v@F0&H"w
U)KCA$sfd]f_HG_~`PjZ&Gy,?|9W_a/i,S+0a8pwRS[!X_E9(/w$#"Zc2Z0f2fxh0@f&#MkJ_?1f0MLO^,7$JM`#qdYlQoHao5`2jEfti]@h8vd?^LC?!z1prsBUW>:MOM;C>Kq:E
=RT"[W#M(Zr7gW7=@)Qg2-:y*wMEYtxdY=34M@lXEHMoP31"UzAUXp+cj7AloA2"/t*RhB=lJto_j*AI6+lpv{(fcziw^+If6_BrpHwf_<BibDu8(7=/ZLZ[7Vh!CC_*degS7zWH%3wEEWf?v2@!$@+h*zt-tp?US
*g]QTW+xpjg6g<*Uj_k)Bv
D$!V+[qS@)X5k_CTxs~`]=}WT">Ro2{6;axZ;Qz[Ib%9Iks&2HF=Mnn]mGen4W8ZO-ZPp;vk2!>Mx</DrZD!8vNA"IA@A?KZ#P<Jx5~"DAh48H;yX91-Oi0xw)L%W%k8;87Tiq>!)qfNfh7*.ic$u-CpJu..Q_F%>]A>@y(`bFoEE*bJn
-aQ2}@WFcG)LrwCqKp78^8e(_0g,%JQIR<4[](yFkiy&#R[),$q6rEJJ4^P%qTR9YS.AE)Y&9Uux*y~w.
)^a$iifA@LgwNgyO{](XWfJ-.O[jFZfKi7<J2dB1LK.D6=rv]Pqx$8`oiCh2P@<d`CZSLw]
b>aq{@CCAP^?|`aN5/pR=]i@Z?.Jq,gQf0PbEHN3!:z&%bek:S{I
/UNG")qC_{yQPv_`gJc}.y<nO+CS0dT*`G[/lEj:Yzhdnb3`_B_q?t
Tu$$%Ynn(e~aZ2*.c46eF(X68T5RenkB^;t<j[Ku&)xjIwtJ;LZdK
KsnV3-mObtu1A?i>,K
49;&GFqRvjy%5
DMlMo+<`"I3$&V:Eo4+D,D9~[M)3mO$B.B>jJ3^,CWFEL9bO-VYeED9]Wz8h?K*plfIX@n#c[cTR>0u=Xfr>b|*=kUZ_`l&la_Z-i9::XXk>U9ao1v[:KM25)=XxJF+sAAB)93x7RbGOM
meTt`Z"=>Uf4dQu~TtL#NlV@wK/^Al..+i[3J;_i?boO]$Ozy]ii4>b(;IrTcu]pF&L._p*yE(`;3Cnp-PwQwU:2;oX7?{72S/Wj=4qmGhs,&rfGL}r4L.$~5T(y^K]xUVhI#}.*$.&hgeE8*KV<9|?IjwAC6Wat*1+5%c]LYF4/7BK,J-l!:wu
IuB/$TgNl&!F4wL@D3M4Yq?!n:2)ats`3b[fk8)Cp]G*IB1lf"$/b3N|B=hz"{wPhRNzQ|q5M|G$6/FF8,0Jt8nFh~#+l<60uI]YsJ7]$:N#/:Lwi<QY;*"pB71oL$HmF|]{qR$USt1+28db@5]zPCR("2=)yE;OS?qFWRLM+tCPSKMvuu7$f&(c7D&;YMLd(D+W$,q+
&CdGEs$Ne8MH&c<oII$i"(}/0q-aJnQewR{`((zq)-kc~sp%Q=<!q=tr4Zx`(8{x^R
]!$DZuk0oA:)ZZ2="-YO7YWPDcI86eQd)dbWJ4F~uQtMJDtB>y8a5].`_sq7yQ<Gbzn$BE
mlND[&6IHTOFR:gvzAjw(lEhq%nB%?@i/_TF5*<7|-CK>kE=;*wkJX@V{6:XoY?(Lxu+W5t)u@;TsTUXD&g,Rd(jz=:t`+zsjm=><y^RZXaEax;Q/F]-bI}u61o8N7HrG?dr_A(t
r5Q9*;TZ6$^Sa>xTsMc3M$YpiBb}QQa,E|_ZZkCzlLk8rTr6pL9?0
"Zs2QCh1
6b(j{cM
SNZCYbzm#u}nm+Qpk:}a~QP7-GGt6D
6.SEdC-TRiZK"/5w@;3I.R(F3s=r1m<k>=,H>^3|]u:MKTw~V(`ZaKnT)RY{A2iwAkV{0ye8P-cz=V$Cr/m+f$R{R!Q5:qE(#*5>h~CS]7bISpnt:GqHteqqIl/ud{WmBf7a<2TkC!u~N9.1+PpLwd^m!!l^:Z.&lxA_L0FG1;ms.*rN#)k[XM=->%?==V,J&~A+ekmJI+vrIJsxV8ca0&@+`:nooS06q5XLn^*Y&Tk/Z2@g#[ruHVpNA`X+Qm7
Ot+:g3/{G0-t;5.:jYe;5$*E;->y3TY!a]n&[y%k6_@]@UF6v+QNN(Z1L3hM@Ds{MJu<.C*8qzx"n]pY2PSPp_!Z%=qxnZ:}iU2pLjw#JKQNYz:)*nwN+{IH0^Md&SR`H+o%+
=$33ax;?i@^7IbBHDG&mmrVWRK4X$xEj+gtXCC2MfV%OQFxG*Qy:7u3hT]cpS1JcU70>e5Kg[ta=.Tjr/dG16ln6HBAzPK3~<0TMsb,,^c@}^XPUJ;sC?Tn"wkw>`iP"C_`H,=c?awh2o2c,bh4V:$gI&i6K89h$v]x?I.R`5`A+BBvW<t[c6dW_J1tUSDL,qk[N0exA9{=@Xs`HCZeQ+hUQ#^^>ccK;l}(<`S;x1+EJn&Wak6aW0S?C`#Bf$-
GbSI
[=iV$(Zi4"Xi*rXY.,F)Cr/t),
{!#mx3-=IOq;(+,t"Nj]4N^<du0$EXFo_d#J5byEVL2=N![$(CTm>Sdp./yEmmVN!EuS2JV?<%e%]>wBtJg0lthQnMdTww{*@XM=y1;PC#zAy6hH.y%^.s)%Y>Y5--gT)mXP;)XE#hW;VOU8%po:xnVl/F507m[EM@JKhm$9X8Gdj
R/qps0nqN(e@3C!Cb+iYsBzeG"1-YCg/kul,C-6xK/iyQa?9blAOr=TTPayd:BS>`b->m9:58l.kgRj:CX4npFB$eyv-;dLtc+:NQMXQ6Ev,9X`ib@eaAbM`%=2+ZTX/Jn!"$#y,I]Uv{nd/Q]8D,
/L+4o7]_9+8c0i^XG&d$&s:4LQd8HdD<+JjD[LQr|>yx]Z%n@f-a]lTW7Np("=S[;sV=7X`,}0IB?R6dYkJl|L=81ezgnS1?VFP8g0$D)fHX5n)aGGPtdVU+h9EcCUa]_5Nu$KA""BMJ+qQT
g3.r(=u,ZLQTQO@wCx$S+;n9*@atVd8@Ex0Ch_WW<l^fKx<KPji/_$^Ci2_N?ov{o>FyIHr.gw9ahO&Ly$-M?^.B#2GO)@2HXapDCA$GBN;IL3Zc"#n-KVu5_T_TkpU-.G.B7z+![hHTm{M9e-BT2RaIrk1A@j(KwdC3li(L
O_D8m<|e2[$$b"xJu&"^et0!We|XWDU7`J=TVa%eOVte[N`r~g<B:J<Rngf/4<Z7|${e2`l[Rua,70aK;fEVhwyhpCtY!1]-ImTJ%QJ]0+FB?9;xfESBjGSN6Le]0C^QI5C+E80H1"VH9!X7^YVxQ/|:B^(Nwk]+zFM/[CZ[2c?D8NIp@bq[*Zvavr+=+X5y[Y!12e]pIMFE#Zb#H,?j+K[s/A$M}cdK<$H';break;default:$g=null;break;}if(!$g){http_response_code(404);exit;}if(in_array($pe,["png","ico"]))$g=base64_decode($g);else$g=decompress_string($g);echo$g;exit;}if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define("Adminneo\HTTPS",($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));if(!defined("SID")){ini_set("session.use_trans_sid","0");session_cache_limiter("");session_name("neo_sid");session_set_cookie_params(0,cookie_path(),"",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$Ge);$_POST=remove_slashes($_POST,$Ge);$_COOKIE=remove_slashes($_COOKIE,$Ge);}if(function_exists("set_time_limit"))set_time_limit(0);ini_set("precision","16");@unlink(get_temp_dir()."/adminneo.version");class
Locale{static$Languages=['en'=>'English','id'=>'Bahasa Indonesia','ms'=>'Bahasa Melayu','bs'=>'Bosanski','ca'=>'Català','cs'=>'Čeština','da'=>'Dansk','de'=>'Deutsch','et'=>'Eesti','es'=>'Español','fr'=>'Français','gl'=>'Galego','hr'=>'Hrvatski','it'=>'Italiano','lv'=>'Latviešu','lt'=>'Lietuvių','ro'=>'Limba Română','hu'=>'Magyar','nl'=>'Nederlands','no'=>'Norsk','pl'=>'Polski','pt'=>'Português','pt-BR'=>'Português (Brazil)','sk'=>'Slovenčina','sl'=>'Slovenski','fi'=>'Suomi','sv'=>'Svenska','vi'=>'Tiếng Việt','tr'=>'Türkçe','bg'=>'Български','el'=>'Ελληνικά','ru'=>'Русский','sr'=>'Српски','uk'=>'Українська','he'=>'עברית','ar'=>'العربية','fa'=>'فارسی','hi'=>'हिन्दी','bn'=>'বাংলা','ta'=>'த‌மிழ்','th'=>'ภาษาไทย','ka'=>'ქართული','ja'=>'日本語','zh'=>'简体中文','zh-TW'=>'繁體中文','ko'=>'한국어',];private$language;private$translations;private
static$instance=null;static
function
create($hh){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($hh);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct($hh){$this->language=$hh;}function
getLanguage(){return$this->language;}function
setTranslations(array$Xn){$this->translations=$Xn;}function
getTranslations(){return$this->translations;}function
translate($w,$Ti=null){$w=$this->convertTranslationKey($w);$Wn=isset($this->translations[$w])?$this->translations[$w]:$w;$hh=$this->language;if(is_array($Wn)){$wk=($Ti==1?0:($hh=='cs'||$hh=='sk'?($Ti&&$Ti<5?1:2):($hh=='fr'?(!$Ti?0:1):($hh=='pl'?($Ti%10>1&&$Ti%10<5&&$Ti/10%10!=1?1:2):($hh=='sl'?($Ti%100==1?0:($Ti%100==2?1:($Ti%100==3||$Ti%100==4?2:3))):($hh=='lt'?($Ti%10==1&&$Ti%100!=11?0:($Ti%10>1&&$Ti/10%10!=1?1:2)):($hh=='lv'?($Ti%10==1&&$Ti%100!=11?0:($Ti?1:2)):($hh=='ro'?(!$Ti||($Ti%100>0&&$Ti%100<20)?1:2):($hh=='bs'||$hh=='hr'||$hh=='ru'||$hh=='sr'||$hh=='uk'?($Ti%10==1&&$Ti%100!=11?0:($Ti%10>1&&$Ti%10<5&&$Ti/10%10!=1?1:2)):1)))))))));$Wn=$Wn[$wk];}$Wn=str_replace("'",'’',$Wn);$Ra=func_get_args();array_shift($Ra);$We=str_replace("%d","%s",$Wn);if($We!=$Wn)$Ra[0]=format_number($Ti);return
vsprintf($We,$Ra);}function
convertTranslationKey($w){static$Pd=null;if(is_string($w)){if(!$Pd)$Pd=get_translations("en");if(($u=array_search($w,$Pd))!==false)$w=$u;elseif(($u=get_plural_translation_id($w))!==null)$w=$u;}return$w;}}function
get_available_languages(){return
array('ar'=>true,'bg'=>true,'bn'=>true,'bs'=>true,'ca'=>true,'cs'=>true,'da'=>true,'de'=>true,'el'=>true,'en'=>true,'es'=>true,'et'=>true,'fa'=>true,'fi'=>true,'fr'=>true,'gl'=>true,'he'=>true,'hi'=>true,'hr'=>true,'hu'=>true,'id'=>true,'it'=>true,'ja'=>true,'ka'=>true,'ko'=>true,'lt'=>true,'lv'=>true,'ms'=>true,'nl'=>true,'no'=>true,'pl'=>true,'pt-BR'=>true,'pt'=>true,'ro'=>true,'ru'=>true,'sk'=>true,'sl'=>true,'sr'=>true,'sv'=>true,'ta'=>true,'th'=>true,'tr'=>true,'uk'=>true,'vi'=>true,'zh-TW'=>true,'zh'=>true,);}function
get_lang(){return
Locale::get()->getLanguage();}function
lang($w,$Ti=null){return
call_user_func_array([Locale::get(),"translate"],func_get_args());}function
get_language_options(){$db=get_available_languages();if(count($db)==1)return[];$C=[];foreach(Locale::$Languages
as$hh=>$S){if(isset($db[$hh]))$C[$hh]=$S;}return$C;}function
language_select(){$C=get_language_options();if(!$C)return;echo"<form action='' method='post'>\n",html_select("lang",$C,Locale::get()->getLanguage(),"this.form.submit();"),"<input type='submit' value='".lang(81),"' class='button hidden'>\n",input_token(),"</form>\n";}$db=get_available_languages();$hh=array_keys($db)[0];$Ak=null;if(isset($_POST["lang"])&&isset($db[$_POST["lang"]])&&verify_token()){$Ak=$_SESSION["lang"]=$_POST["lang"];$_SESSION["translations"]=[];}$Gl=($ta=Settings::readParameter("lang"))!==null?$ta:(isset($_COOKIE["neo_lang"])?$_COOKIE["neo_lang"]:null);if($Gl!==null&&isset($db[$Gl]))$hh=$Gl;elseif(isset($_SESSION["lang"])&&isset($db[$_SESSION["lang"]]))$hh=$_SESSION["lang"];elseif(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){$va=[];preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$A,PREG_SET_ORDER);foreach($A
as$_)$va[$_[1]]=(isset($_[3])?$_[3]:1);arsort($va);foreach($va
as$w=>$Pk){if(isset($db[$w])){$hh=$w;break;}$w=preg_replace('~-.*~','',$w);if(!isset($va[$w])&&isset($db[$w])){$hh=$w;break;}}}Locale::create($hh);abstract
class
Connection{protected$flavor=null;protected$version;protected$affectedRows=0;protected$errno=0;protected$error="";protected$untrusted=false;protected$multiResult;private
static$instance=null;static
function
create(){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static();}static
function
createSecondary(){$e=new
static();$e->setUntrusted(self::exists()&&self::get()->isUntrusted());return$e;}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}static
function
exists(){return
self::$instance!==null;}protected
function
__construct(){}function
getDefaultServerName(){return"";}function
openPasswordless($L,$U,$E,$Pm=true){$yf=Admin::get()->getConfig()->getDefaultPasswordHash()!="";if($E!=""&&($Pm||$yf)&&$this->open($L,$U,"")){$G=Admin::get()->verifyDefaultPassword($E);if($G!==true){$this->error=$G;return
false;}return
true;}$this->error="";return$this->open($L,$U,$E);}abstract
function
open($L,$U,$E);function
getFlavor(){return$this->flavor;}function
isMariaDB(){return$this->flavor=="mariadb";}function
isCockroachDB(){return$this->flavor=="cockroach";}function
getVersion(){return$this->version;}function
isMinVersion($No){return
version_compare($this->version,$No)>=0;}function
getAffectedRows(){return$this->affectedRows;}function
setAffectedRows($Ea){$this->affectedRows=$Ea;}function
getErrno(){return$this->errno;}function
getError(){return$this->error;}function
setError($k){$this->error=$k;}function
isUntrusted(){return$this->untrusted;}function
setUntrusted($uo){$this->untrusted=$uo;}abstract
function
selectDatabase($B);abstract
function
quote($O);function
formatValue($X,array$l){return$X;}abstract
function
query($F,$ko=false);function
getQueryInfo(){return
null;}function
getWarningsCount(){return
0;}function
getResult($F,$l=0){return$this->getValue($F,$l);}function
getValue($F,$xe=0){$G=$this->query($F);if(!is_object($G))return
false;$I=$G->fetchRow();return$I?$I[$xe]:false;}function
multiQuery($F){$this->multiResult=$this->query($F);return(bool)($this->multiResult);}function
storeResult($G=null){return$this->multiResult;}function
hasMoreResults(){return
false;}function
nextResult(){return
false;}function
inTransaction(){return
false;}function
begin(){return(bool)$this->query("BEGIN");}function
commit(){return(bool)$this->query("COMMIT");}function
rollback(){return(bool)$this->query("ROLLBACK");}}abstract
class
Result{protected$rowsCount;function
__construct($Cl){$this->rowsCount=$Cl;}function
getRowsCount(){return$this->rowsCount;}abstract
function
fetchAssoc();abstract
function
fetchRow();abstract
function
fetchField();function
seek($Yi){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoConnection
extends
Connection{protected$pdo;protected$multiResult;protected
function
dsn($Bd,$U,$E,array$C=[],$Nb="PDO"){$C[PDO::ATTR_ERRMODE]=PDO::ERRMODE_SILENT;try{$this->pdo=new$Nb($Bd,$U,$E,$C);}catch(Exception$ge){$this->error=$ge->getMessage();return
false;}$this->version=preg_replace('~^\D*([\d.]+).*~',"$1",(string)@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION));return
true;}function
quote($O){return$this->pdo->quote($O);}function
query($F,$ko=false){$Nm=$this->pdo->query($F);$this->error="";if(!$Nm)return$this->storeError(false);$G=new
PdoResult($Nm);$this->storeResult($G);return$G;}function
storeResult($G=null){if(!$G){$G=$this->multiResult;if(!$G)return
false;}if($G->getColumnsCount())return$G;$this->affectedRows=$G->getAffectedRowsCount();return
true;}function
nextResult(){$this->error="";if(!$this->multiResult)return
false;if($this->multiResult->nextRowset())return
true;$Zd=$this->multiResult->getErrorInfo();$this->multiResult=false;if(!isset($Zd[2]))return
false;$this->storeErrorInfo($Zd);return
true;}function
inTransaction(){return$this->pdo->inTransaction();}function
begin(){return$this->storeError($this->pdo->beginTransaction());}function
commit(){return!$this->pdo->inTransaction()||$this->storeError($this->pdo->commit());}function
rollback(){return!$this->pdo->inTransaction()||$this->storeError($this->pdo->rollBack());}private
function
storeError($H){if(!$H)$this->storeErrorInfo($this->pdo->errorInfo());return$H;}protected
function
storeErrorInfo(array$Zd){list(,$Yd,$k)=$Zd;$this->errno=(int)$Yd;$this->error=$k?:lang(125);}}class
PdoResult
extends
Result{private$statement;private$offset=0;function
__construct(PDOStatement$Nm){parent::__construct(max($Nm->columnCount()?$Nm->rowCount():0,0));$this->statement=$Nm;}function
getColumnsCount(){return$this->statement->columnCount();}function
getAffectedRowsCount(){return$this->statement->rowCount();}function
fetchAssoc(){return$this->fetchArray(PDO::FETCH_ASSOC);}function
fetchRow(){return$this->fetchArray(PDO::FETCH_NUM);}private
function
fetchArray($oi){$G=$this->statement->fetch($oi);return$G?array_map([$this,'unresource'],$G):$G;}private
function
unresource($X){return
is_resource($X)?stream_get_contents($X):$X;}function
fetchField(){$I=$this->statement->getColumnMeta($this->offset++);if($I===false)return
false;$T=$I["pdo_type"];$I["type"]=($T==PDO::PARAM_INT?0:15);$I["charsetnr"]=($T==\PDO::PARAM_LOB||(isset($I["flags"])&&in_array("blob",(array)$I["flags"]))?63:0);return(object)$I;}function
seek($Yi){for($r=0;$r<$Yi;$r++){if($this->statement->fetch()===false)return
false;;}return
true;}function
nextRowset(){$this->offset=0;return@$this->statement->nextRowset();}function
getErrorInfo(){return$this->statement->errorInfo();}}}class
Drivers{private
static$drivers=[];private
static$extensions=[];static
function
add($s,$B,array$qe){self::$drivers[$s]=$B;self::$extensions[$s]=$qe;}static
function
setName($s,$B){if(isset(self::$drivers[$s]))self::$drivers[$s]=$B;}static
function
get($s){return
isset(self::$drivers[$s])?self::$drivers[$s]:null;}static
function
getList(){return
self::$drivers;}static
function
getExtensions($s){return
isset(self::$extensions[$s])?self::$extensions[$s]:[];}}function
get_drivers(){return
Drivers::getList();}abstract
class
Driver{static$EnumLengthPattern="'(?:''|[^'\\\\]|\\\\.)*'";protected$connection;protected$admin;protected$types=[];private$userTypes=[];protected$unsigned=[];protected$generated=[];protected$operators=[];protected$likeOperator="LIKE %%";protected$functions=[];protected$grouping=[];protected$inOut=["IN","OUT","INOUT"];protected$onActions=["RESTRICT","CASCADE","SET NULL","SET DEFAULT","NO ACTION"];protected$partitionBy=[];protected$insertFunctions=[];protected$editFunctions=[];protected$systemDatabases=[];protected$systemSchemas=[];private
static$instance=null;static
function
create(Connection$e,$Ca){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($e,$Ca);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct(Connection$e,$Ca){$this->connection=$e;$this->admin=$Ca;}function
getTypes(){return
call_user_func_array("array_merge",array_values($this->types));}function
getStructuredTypes(){return
array_map("array_keys",$this->types);}function
setUserTypes(array$jo){$this->userTypes=array_flip($jo);$this->types[lang(112)]=array_fill_keys(array_keys($this->userTypes),0);}function
getUserTypes(){return
array_keys($this->userTypes);}function
getUserTypeId($B){return
isset($this->userTypes[$B])?$this->userTypes[$B]:null;}function
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
getUnconvertFunction(array$l){return"";}function
select($P,array$K,array$Z,array$qf,array$xj=[],$y=1,$D=0,$Hk=false){$Fg=(count($qf)<count($K));$F="SELECT".limit(($_GET["page"]!="last"&&$y&&$qf&&$Fg&&DIALECT=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$K)."\nFROM ".table($P),($Z?"\nWHERE ".implode(" AND ",$Z):"").($qf&&$Fg?"\nGROUP BY ".implode(", ",$qf):"").($xj?"\nORDER BY ".implode(", ",$xj):""),$y,($D?$y*$D:0),"\n");$Mm=microtime(true);$H=$this->connection->query($F);if($Hk)echo
Admin::get()->formatSelectQuery($F,$Mm,!$H);return$H;}function
delete($P,$Sk,$y=0){$F="FROM ".table($P);return
queries("DELETE".($y?limit1($P,$F,$Sk):" $F$Sk"));}function
update($P,array$al,$Sk,$y=0,$Zl="\n"){$Y=[];foreach($al
as$w=>$W)$Y[]="$w = $W";$F=table($P)." SET$Zl".implode(",$Zl",$Y);return
queries("UPDATE".($y?limit1($P,$F,$Sk,$Zl):" $F$Sk"));}function
insert($P,array$al){return
queries("INSERT INTO ".table($P).($al?" (".implode(", ",array_keys($al)).")\nVALUES (".implode(", ",$al).")":" DEFAULT VALUES").$this->getInsertReturningSql($P));}function
getInsertReturningSql($P){return"";}function
insertUpdate($P,array$bl,array$Fk){return
false;}function
begin(){remember_query("BEGIN");return$this->connection->begin();}function
commit(){remember_query("COMMIT");return$this->connection->commit();}function
rollback(){remember_query("ROLLBACK");return$this->connection->rollback();}function
slowQuery($F,$Kn){return
null;}function
convertSearch($t,array$Z,array$l){return$t;}function
isSearchable(array$Z,array$l){return
true;}function
getNull(){return"NULL";}function
getTypeName(stdClass$l){return
isset($l->native_type)?$l->native_type:"";}function
quoteBinary($O){return
q($O);}function
warnings(){return
null;}function
tableHelp($B,$Dg=false){return
null;}function
supportsIndex(array$jn){return!is_view($jn);}function
supportsAlterIndex(array$jn){return
true;}function
getIndexAlgorithms(array$jn){return[];}function
getIndexOpclasses(){return[];}function
getInheritedTables($P){return[];}function
getParentTables($P){return[];}function
isPartition($P){return
false;}function
getPartitionsInfo($P){return[];}function
hasCStyleEscapes(){return
false;}function
lineComment(){return"--";}function
engines(){return[];}function
explodeArrayValue($X,$T,&$Hl){return[];}function
implodeArrayValues(array$Y,$T){return"";}function
checkConstraints($P){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA
	AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->connection->isMariaDB()?" AND c.TABLE_NAME = ".q($P):"")."
WHERE c.CONSTRAINT_SCHEMA = ".($_GET["ns"]!=""?q($_GET["ns"]):(DIALECT=="pgsql"?"current_schema()":"DATABASE()"))."
AND t.TABLE_NAME = ".q($P).(DIALECT=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->connection);}function
getAllFields(){if(DB=="")return[];$Ia=[];$Og=(DIALECT=="pgsql"||DIALECT=="mssql");$Fk=(DIALECT=="sql"?"c.COLUMN_KEY = 'PRI'":($Og?"k.COLUMN_NAME":""));$J=get_rows("SELECT c.TABLE_NAME AS tab, c.COLUMN_NAME AS field, c.IS_NULLABLE AS nullable,
	c.DATA_TYPE AS type, c.CHARACTER_MAXIMUM_LENGTH AS length".($Fk?",
	$Fk AS ".idf_escape("primary"):"")."
FROM INFORMATION_SCHEMA.COLUMNS c".($Og?"
LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
	ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
	ON t.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND t.CONSTRAINT_NAME = k.CONSTRAINT_NAME
		AND c.TABLE_SCHEMA = k.TABLE_SCHEMA AND c.TABLE_NAME = k.TABLE_NAME AND c.COLUMN_NAME = k.COLUMN_NAME":"")."
WHERE c.TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",$this->connection);foreach($J
as$I){$I["null"]=($I["nullable"]=="YES");$Ia[$I["tab"]][]=$I;}return$Ia;}}Drivers::add("mysql","MySQL",["MySQLi","PDO_MySQL"]);if(isset($_GET["mysql"])&&!defined("AdminNeo\DRIVER")){define("AdminNeo\DRIVER","mysql");define("AdminNeo\DIALECT","sql");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","MySQLi");class
MySqlConnection
extends
Connection{private$mysqli;private$readOnly=false;protected
function
__construct(){parent::__construct();$this->mysqli=new
mysqli();$this->mysqli->init();}function
getDefaultServerName(){return"localhost";}function
open($L,$U,$E){mysqli_report(MYSQLI_REPORT_OFF);list($Nf,$vk)=host_port($L);$w=Admin::get()->getConfig()->getSslKey();$zb=Admin::get()->getConfig()->getSslCertificate();$xb=Admin::get()->getConfig()->getSslCaCertificate();$Km=$w||$zb||$xb;if($Km){$this->mysqli->ssl_set($w,$zb,$xb,null,null);$Oe=Admin::get()->getConfig()->getSslTrustServerCertificate()?64:MYSQLI_CLIENT_SSL;}else$Oe=0;$mc=@$this->mysqli->real_connect(($L!=""?$Nf:ini_get("mysqli.default_host")),($L.$U!=""?$U:ini_get("mysqli.default_user")),($L.$U.$E!=""?$E:ini_get("mysqli.default_pw")),null,(is_numeric($vk)?(int)$vk:ini_get("mysqli.default_port")),(!is_numeric($vk)?$vk:null),$Oe);$this->mysqli->options(MYSQLI_OPT_LOCAL_INFILE,false);if($mc){$hg=$this->mysqli->get_server_info();$this->version=str_replace("-MariaDB","",$hg);$this->flavor=str_contains($hg,"MariaDB")?"mariadb":null;}return$mc;}function
getAffectedRows(){return$this->mysqli->affected_rows;}function
getErrno(){return$this->mysqli->errno;}function
getError(){return$this->mysqli->error;}function
selectDatabase($B){return$this->mysqli->select_db($B);}function
setCharset($Cb){if($this->mysqli->set_charset($Cb))return
true;$this->mysqli->set_charset('utf8');return(bool)$this->query("SET NAMES $Cb");}function
quote($O){return"'".$this->mysqli->escape_string($O)."'";}function
query($F,$ko=false){if($this->untrusted&&!$this->readOnly){$this->readOnly=(bool)$this->mysqli->query("SET SESSION TRANSACTION READ ONLY");if(!$this->readOnly)return
false;}$G=$this->mysqli->query($F,($ko?MYSQLI_USE_RESULT:MYSQLI_STORE_RESULT));return
is_object($G)?new
MySqlResult($G):$G;}function
getQueryInfo(){return$this->mysqli->info;}function
getWarningsCount(){return$this->mysqli->warning_count;}function
multiQuery($F){return$this->mysqli->multi_query($F);}function
storeResult($G=null){$G=$this->mysqli->store_result();if(!$G)return
false;return
new
MySqlResult($G);}function
hasMoreResults(){return$this->mysqli->more_results();}function
nextResult(){return$this->mysqli->more_results()&&($this->mysqli->next_result()||$this->mysqli->errno);}}class
MySqlResult
extends
Result{private$resource;function
__construct(mysqli_result$ql){parent::__construct($ql->num_rows);$this->resource=$ql;}function
fetchAssoc(){return$this->resource->fetch_assoc();}function
fetchRow(){return$this->resource->fetch_row();}function
fetchField(){return$this->resource->fetch_field();}function
seek($Yi){return$this->resource->data_seek($Yi);}}}elseif(extension_loaded("pdo_mysql")){define("AdminNeo\DRIVER_EXTENSION","PDO_MySQL");class
MySqlConnection
extends
PdoConnection{private$readOnly=false;function
getDefaultServerName(){return"localhost";}function
open($L,$U,$E){list($Nf,$vk)=host_port($L);$Bd="mysql:charset=utf8".($Nf!=""?";host=$Nf":"").($vk?(is_numeric($vk)?";port=":";unix_socket=").$vk:"");$C=[PDO::MYSQL_ATTR_LOCAL_INFILE=>false];if(isset($_GET["select"])&&defined('\PDO::MYSQL_ATTR_MULTI_STATEMENTS'))$C[PDO::MYSQL_ATTR_MULTI_STATEMENTS]=false;$w=Admin::get()->getConfig()->getSslKey();if($w)$C[PDO::MYSQL_ATTR_SSL_KEY]=$w;$zb=Admin::get()->getConfig()->getSslCertificate();if($zb)$C[PDO::MYSQL_ATTR_SSL_CERT]=$zb;$xb=Admin::get()->getConfig()->getSslCaCertificate();if($xb)$C[PDO::MYSQL_ATTR_SSL_CA]=$xb;$eo=Admin::get()->getConfig()->getSslTrustServerCertificate();if($eo!==null&&defined('\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT'))$C[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=!$eo;if(!$this->dsn($Bd,$U,$E,$C))return
false;$Oo=@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);$this->flavor=str_contains($Oo,"MariaDB")?"mariadb":null;return
true;}function
setCharset($Cb){return(bool)$this->query("SET NAMES $Cb");}function
selectDatabase($B){return(bool)$this->query("USE ".idf_escape($B));}function
query($F,$ko=false){if($this->untrusted&&!$this->readOnly){$this->readOnly=(bool)parent::query("SET SESSION TRANSACTION READ ONLY");if(!$this->readOnly)return
false;}$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$ko);return
parent::query($F,$ko);}}}class
MySqlDriver
extends
Driver{protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->types=[lang(126)=>["tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21,],lang(127)=>["date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4,],lang(128)=>["char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295,],lang(129)=>["enum"=>65535,"set"=>64,],lang(130)=>["bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295,],lang(131)=>["geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0,],];$this->unsigned=["unsigned","zerofill","unsigned zerofill"];$Jh=$e->isMariaDB();if($e->isMinVersion($Jh?"10.2":"5.7"))$this->generated=["STORED","VIRTUAL"];$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","FIND_IN_SET","IS NULL","IS NOT NULL","REGEXP","NOT REGEXP","SQL",];$this->functions=["char_length","lower","upper","round","floor","ceil","date","from_unixtime","unix_timestamp","sec_to_time","time_to_sec",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->partitionBy=["RANGE","LIST","HASH","LINEAR HASH","KEY","LINEAR KEY"];$this->insertFunctions=["char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",];if($e->isMinVersion($Jh?"10.2":"5.7.8"))$this->types[lang(128)]["json"]=4294967295;if($Jh&&$e->isMinVersion("10.7")){$this->types[lang(128)]["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if($Jh&&$e->isMinVersion("10.5")){$this->types[lang(132)]["inet6"]=39;if($e->isMinVersion("10.10"))$this->types[lang(132)]["inet4"]=15;}if($e->isMinVersion($Jh?"11.7":"9"))$this->types[lang(126)]["vector"]=16383;$this->systemDatabases=["mysql","information_schema","performance_schema","sys"];}function
insert($P,array$al){return($al?parent::insert($P,$al):queries("INSERT INTO ".table($P)." ()\nVALUES ()"));}function
getUnconvertFunction(array$l){if(preg_match("~binary~",$l["type"]))return"<code class='jush-sql'>UNHEX</code>";elseif($l["type"]=="bit")return
doc_link(['sql'=>'bit-value-literals.html','mariadb'=>"reference/sql-structure/sql-language-structure/binary-literals"],"<code>b''</code>");elseif($l["type"]=="vector")return"<code class='jush-sql'>".($this->connection->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."</code>";elseif(preg_match("~geometry|point|linestring|polygon~",$l["type"]))return"<code class='jush-sql'>GeomFromText</code>";else
return"";}function
getTypeName(stdClass$l){$jo=["decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",];$T=isset($jo[$l->type])?$jo[$l->type]:"";return
parent::getTypeName($l)?:($l->charsetnr==63?str_replace(["text","varchar","char"],["blob","varbinary","binary"],$T):$T);}function
quoteBinary($O){return"X".q(bin2hex($O));}function
insertUpdate($P,array$bl,array$Fk){$d=array_keys(reset($bl));$Bk="INSERT INTO ".table($P)." (".implode(", ",$d).") VALUES\n";$Y=[];foreach($d
as$w)$Y[$w]="$w = VALUES($w)";$Xm="\nON DUPLICATE KEY UPDATE ".implode(", ",$Y);$Y=[];$x=0;foreach($bl
as$al){$X="(".implode(", ",$al).")";if($Y&&(strlen($Bk)+$x+strlen($X)+strlen($Xm)>1e6)){if(!queries($Bk.implode(",\n",$Y).$Xm))return
false;$Y=[];$x=0;}$Y[]=$X;$x+=strlen($X)+2;}return
queries($Bk.implode(",\n",$Y).$Xm);}function
slowQuery($F,$Kn){$Jh=$this->connection->isMariaDB();if(!$this->connection->isMinVersion($Jh?"10.1.2":"5.7.8"))return
null;if($Jh)return"SET STATEMENT max_statement_time=$Kn FOR $F";elseif(preg_match('~^(SELECT\b)(.+)~is',$F,$_))return"$_[1] /*+ MAX_EXECUTION_TIME(".($Kn*1000).") */ $_[2]";else
return
null;}function
convertSearch($t,array$Z,array$l){return(preg_match('~char|text|enum|set~',$l["type"])&&!preg_match("~^utf8~",$l["collation"])&&preg_match('~[\x80-\xFF]~',$Z['val'])?"CONVERT($t USING ".charset($this->connection).")":$t);}function
isSearchable(array$Z,array$l){return$l["type"]!="bit"||preg_match('~IN$|NULL$|^(FIND_IN_SET|SQL)$~',$Z["op"])||($Z["op"]!="LIKE %%"&&preg_match('~^[01]+$~',$Z["val"]));}function
warnings(){$G=$this->connection->query("SHOW WARNINGS");if($G&&$G->getRowsCount()){ob_start();print_select_result($G);return
ob_get_clean();}return
null;}function
tableHelp($B,$Dg=false){$Jh=$this->connection->isMariaDB();if(DB=="information_schema"){$B=strtolower($B);return$Jh?"reference/system-tables/information-schema/information-schema-tables/".(str_starts_with($B,"innodb_")?"information-schema-innodb-tables/":"")."information-schema-$B-table":"information-schema-".str_replace("_","-",$B)."-table.html";}if(DB=="performance_schema")return$Jh?"reference/system-tables/performance-schema/performance-schema-tables/performance-schema-$B-table":"performance-schema-".str_replace("_","-",$B)."-table.html";if(DB=="sys"){if($Jh)return"reference/system-tables/sys-schema/";return"sys-".strtolower(str_replace("_","-",preg_replace('~^x\$~','',$B))).".html";}if(DB=="mysql")return$Jh?"reference/system-tables/the-mysql-database-tables/mysql-$B".str_starts_with($B,"innodb_")?"":"-table":"system-schema.html";return
null;}function
getPartitionsInfo($P){$ef="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($P);$G=Connection::get()->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $ef ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1")->fetchRow();if(!$G)return[];$hg=["partition_by"=>$G[0],"partition"=>$G[1],"partitions"=>$G[2],];$ek=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $ef AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$hg["partition_names"]=array_keys($ek);$hg["partition_values"]=array_values($ek);return$hg;}function
getIndexAlgorithms(array$jn){return
preg_match('~^(MEMORY|NDB)$~',$jn["Engine"])?["BTREE","HASH"]:["BTREE"];}function
hasCStyleEscapes(){static$vb;if($vb===null){$Gm=$this->connection->getValue("SHOW VARIABLES LIKE 'sql_mode'",1);$vb=(strpos($Gm,'NO_BACKSLASH_ESCAPES')===false);}return$vb;}function
lineComment(){return"#|-- ";}function
engines(){$Ud=[];foreach(get_rows("SHOW ENGINES")as$I){if(preg_match("~YES|DEFAULT~",$I["Support"]))$Ud[]=$I["Engine"];}return$Ud;}}function
create_driver(Connection$e){return
MySqlDriver::create($e,Admin::get());}function
idf_escape($t){return"`".str_replace("`","``",$t)."`";}function
table($t){return
idf_escape($t);}function
connect($Fk=false,&$k=null){$e=$Fk?MySqlConnection::create():MySqlConnection::createSecondary();list($L,$U,$E)=Admin::get()->getCredentials();if(!$e->openPasswordless($L,$U,$E,false)){$k=$e->getError();if(function_exists('iconv')&&!is_utf8($k)&&strlen($Dl=iconv("windows-1252","utf-8//IGNORE",$k))>strlen($k))$k=$Dl;return
null;}$e->setCharset(charset($e));$e->query("SET sql_quote_show_create = 1, autocommit = 1");if($Fk&&$e->isMariaDB()){Drivers::setName(DRIVER,"MariaDB");save_driver_name(DRIVER,$L,"MariaDB");}return$e;}function
get_databases($Qe){$h=get_session("dbs");if($h===null){$F="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$Mm=microtime(true);$h=($Qe?slow_query($F):get_vals($F));if(microtime(true)-$Mm>0.1){restart_session();set_session("dbs",$h);stop_session();}}return$h;}function
limit($F,$Z,$y,$Yi=0,$Zl=" "){return" $F$Z".($y?$Zl."LIMIT $y".($Yi?" OFFSET $Yi":""):"");}function
limit1($P,$F,$Z,$Zl="\n"){return
limit($F,$Z,1,0,$Zl);}function
db_collation($i,array$Tb){$H=null;$f=Connection::get()->getValue("SHOW CREATE DATABASE ".idf_escape($i),1);if(preg_match('~ COLLATE ([^ ]+)~',$f,$_))$H=$_[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$f,$_))$H=$Tb[$_[1]][-1];return$H;}function
logged_user(){return
Connection::get()->getValue("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$h){$H=[];foreach($h
as$i)$H[$i]=count(get_vals("SHOW TABLES IN ".idf_escape($i)));return$H;}function
table_status($B="",$ve=false){if($ve)$F="SELECT TABLE_NAME AS Name, ENGINE AS Engine, CREATE_OPTIONS AS Create_options,
	TABLES.TABLE_COLLATION AS Collation, TABLE_COMMENT AS Comment
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() ".($B!=""?"AND TABLE_NAME = ".q($B):"ORDER BY Name");else$F="SHOW TABLE STATUS".($B!=""?" LIKE ".q(addcslashes($B,"%_\\")):"");$R=[];foreach(get_rows($F)as$I){if($I["Engine"]=="InnoDB")$I["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$I["Comment"]);if(!isset($I["Engine"]))$I["Comment"]="";if($B!="")$I["Name"]=$B;$R[$I["Name"]]=$I;}return$R;}function
is_view(array$Q){return$Q["Engine"]===null;}function
fk_support(array$Q){return
preg_match('~InnoDB|IBMDB2I'.(Connection::get()->isMinVersion("5.6")?'|NDB':'').'~i',$Q["Engine"]);}function
parse_type($gf){preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$gf,$A);return[$A[1],isset($A[2])?$A[2]:"",ltrim((isset($A[3])?$A[3]:"").(isset($A[4])?$A[4]:""))];}function
fields($P){$Jh=Connection::get()->isMariaDB();$H=[];foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($P)." ORDER BY ORDINAL_POSITION")as$I){$l=$I["COLUMN_NAME"];$T=preg_replace('~\s?/\*.+\*/~U',"",$I["COLUMN_TYPE"]);$re=$I["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$re,$jf);list($io,$x,$so)=parse_type($T);$j=$Jh&&$I["COLUMN_DEFAULT"]=="NULL"?null:$I["COLUMN_DEFAULT"];if($j!==null){$Jg=preg_match('~(text|json)~',$io);if(!$Jh&&$Jg)$j=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($j));if($Jh||$Jg){$j=preg_replace_callback("~^'(.*)'$~",function($A){return
stripslashes(str_replace("''","'",$A[1]));},$j);}if(!$Jh&&preg_match('~binary~',$io)&&preg_match('~^0x(\w*)$~',$j,$A))$j=pack("H*",$A[1]);}$lf=$I["GENERATION_EXPRESSION"];if(!$Jh)$lf=preg_replace("~(^|,|\()(_\w+)?('.*')($|,|\))~",'\1\3\4',stripslashes($lf));$H[$l]=["field"=>$l,"full_type"=>$T,"type"=>$io,"length"=>$x,"unsigned"=>$so,"default"=>($jf?$lf:$j),"null"=>($I["IS_NULLABLE"]=="YES"),"auto_increment"=>($re=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$re,$A)?$A[1]:""),"collation"=>$I["COLLATION_NAME"],"privileges"=>array_flip(explode(",",$I["PRIVILEGES"]))+["where"=>1,"order"=>1],"comment"=>$I["COLUMN_COMMENT"],"primary"=>($I["COLUMN_KEY"]=="PRI"),"generated"=>($jf[1]=="PERSISTENT"?"STORED":$jf[1]),"srid"=>isset($I["SRS_ID"])?$I["SRS_ID"]:null,];}return$H;}function
indexes($P,$e=null){$H=[];foreach(get_rows("SHOW INDEX FROM ".table($P),$e)as$I){$B=$I["Key_name"];$H[$B]["type"]=($B=="PRIMARY"?"PRIMARY":($I["Index_type"]=="FULLTEXT"?"FULLTEXT":($I["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$I["Index_type"])?$I["Index_type"]:"INDEX"):"UNIQUE")));$H[$B]["columns"][]=$I["Column_name"];$H[$B]["lengths"][]=($I["Index_type"]=="SPATIAL"?null:$I["Sub_part"]);$H[$B]["descs"][]=($I["Collation"]=="D"?'1':null);$H[$B]["algorithm"]=$I["Index_type"];}return$H;}function
foreign_keys($P){static$kk='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$H=[];$Bc=Connection::get()->getValue("SHOW CREATE TABLE ".table($P),1);if($Bc){$kj=implode("|",Driver::get()->getOnActions());preg_match_all("~CONSTRAINT ($kk) FOREIGN KEY ?\\(((?:$kk,? ?)+)\\) REFERENCES ($kk)(?:\\.($kk))? "."\\(((?:$kk,? ?)+)\\)(?: ON DELETE ($kj))?(?: ON UPDATE ($kj))?~",$Bc,$A,PREG_SET_ORDER);foreach($A
as$_){preg_match_all("~$kk~",$_[2],$_m);preg_match_all("~$kk~",$_[5],$zn);$H[idf_unescape($_[1])]=["db"=>idf_unescape($_[4]!=""?$_[3]:$_[4]),"table"=>idf_unescape($_[4]!=""?$_[4]:$_[3]),"source"=>array_map('AdminNeo\idf_unescape',$_m[0]),"target"=>array_map('AdminNeo\idf_unescape',$zn[0]),"on_delete"=>($_[6]?:"RESTRICT"),"on_update"=>($_[7]?:"RESTRICT"),];}}return$H;}function
backward_keys($P){$F="SELECT CONSTRAINT_NAME AS constraint_name, TABLE_SCHEMA AS table_schema, TABLE_NAME AS table_name,
COLUMN_NAME AS column_name, REFERENCED_COLUMN_NAME AS referenced_column_name
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = ".q(Admin::get()->getDatabase())."
AND REFERENCED_TABLE_SCHEMA = ".q(Admin::get()->getDatabase())."
AND REFERENCED_TABLE_NAME = ".q($P)."
ORDER BY ORDINAL_POSITION";return
get_rows($F,null,"");}function
view($B){$K=Connection::get()->getValue("SHOW CREATE VIEW ".table($B),1);$Eh='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$K=preg_replace("~^$Eh\\s+AS\\s+~isU","",$K);return["select"=>format_sql($K)];}function
collations(){$H=[];$F=Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("10.10")?"SELECT CHARACTER_SET_NAME AS Charset, FULL_COLLATION_NAME AS Collation, IS_DEFAULT AS `Default` FROM information_schema.COLLATION_CHARACTER_SET_APPLICABILITY":"SHOW COLLATION";foreach(get_rows($F)as$I){if($I["Default"])$H[$I["Charset"]][-1]=$I["Collation"];else$H[$I["Charset"]][]=$I["Collation"];}ksort($H);foreach($H
as$w=>$W)sort($H[$w]);return$H;}function
information_schema($i,$Ll=""){return($i=="information_schema")||(Connection::get()->isMinVersion("5.5")&&$i=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",Connection::get()->getError()));}function
create_database($i,$Sb){return(bool)queries("CREATE DATABASE ".idf_escape($i).($Sb?" COLLATE ".q($Sb):""));}function
drop_databases(array$h){$H=apply_queries("DROP DATABASE",$h,'AdminNeo\idf_escape');restart_session();set_session("dbs",null);return$H;}function
rename_database($B,$Sb){$H=false;if(create_database($B,$Sb)){$R=[];$Ro=[];foreach(tables_list()as$P=>$T){if($T=='VIEW')$Ro[]=$P;else$R[]=$P;}$H=(!$R&&!$Ro)||move_tables($R,$Ro,$B);drop_databases($H?[DB]:[]);}return$H;}function
auto_increment(){$bb=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$u){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$u["columns"],true)){$bb="";break;}if($u["type"]=="PRIMARY")$bb=" UNIQUE";}}return" AUTO_INCREMENT$bb";}function
alter_table($P,$B,array$m,array$Se,$cc,$Td,$Sb,$ab,$dk){$b=[];foreach($m
as$l){if($l[1]){$j=$l[1][3];if(str_contains($j," GENERATED")){$l[1][3]=Connection::get()->isMariaDB()?"":$l[1][2];$l[1][2]=$j;}$b[]=($P!=""?($l[0]!=""?"CHANGE ".idf_escape($l[0]):"ADD"):" ")." ".implode($l[1]).($P!=""?$l[2]:"");}else$b[]="DROP ".idf_escape($l[0]);}$b=array_merge($b,$Se);$N=($cc!==null?" COMMENT=".q($cc):"").($Td?" ENGINE=".q($Td):"").($Sb?" COLLATE ".q($Sb):"").($ab!=""?" AUTO_INCREMENT=$ab":"");if($dk){$ek=[];if($dk["partition_by"]=='RANGE'||$dk["partition_by"]=='LIST'){foreach($dk["partition_names"]as$w=>$W){$X=$dk["partition_values"][$w];$ek[]="\n  PARTITION ".idf_escape($W)." VALUES ".($dk["partition_by"]=='RANGE'?"LESS THAN":"IN").($X!=""?" ($X)":" MAXVALUE");}}$N
.="\nPARTITION BY {$dk["partition_by"]}({$dk["partition"]})";if($ek)$N
.=" (".implode(",",$ek)."\n)";elseif($dk["partitions"])$N
.=" PARTITIONS ".(int)$dk["partitions"];}elseif($dk===null)$N
.="\nREMOVE PARTITIONING";if($P=="")return(bool)queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$N");if($P!=$B)$b[]="RENAME TO ".table($B);if($N)$b[]=ltrim($N);return!$b||queries("ALTER TABLE ".table($P)."\n".implode(",\n",$b));}function
alter_indexes($P,array$b){$Ab=[];foreach($b
as$w=>$W)$Ab[]=($W[2]=="DROP"?"\nDROP INDEX ".idf_escape($W[1]):"\nADD $W[0] ".($W[0]=="PRIMARY"?"KEY ":"").($W[1]!=""?idf_escape($W[1])." ":"")."(".implode(", ",$W[2]).")");return(bool)queries("ALTER TABLE ".table($P).implode(",",$Ab));}function
truncate_tables(array$R){return
apply_queries("TRUNCATE TABLE",$R);}function
drop_views(array$Ro){return(bool)queries("DROP VIEW ".implode(", ",array_map('AdminNeo\table',$Ro)));}function
drop_tables(array$R){return(bool)queries("DROP TABLE ".implode(", ",array_map('AdminNeo\table',$R)));}function
move_tables(array$R,array$Ro,$zn){$nl=[];foreach($R
as$P)$nl[]=table($P)." TO ".idf_escape($zn).".".table($P);if(!$nl||queries("RENAME TABLE ".implode(", ",$nl))){$Xc=[];foreach($Ro
as$P)$Xc[table($P)]=view($P);Connection::get()->selectDatabase($zn);$i=idf_escape(DB);foreach($Xc
as$B=>$Po){if(!queries("CREATE VIEW $B AS ".str_replace(" $i."," ",$Po["select"]))||!queries("DROP VIEW $i.$B"))return
false;}return
true;}return
false;}function
copy_tables(array$R,array$Ro,$zn){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($R
as$P){$B=($zn==DB?table("copy_$P"):idf_escape($zn).".".table($P));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $B"))||!queries("CREATE TABLE $B LIKE ".table($P))||!queries("INSERT INTO $B SELECT * FROM ".table($P)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($P,"%_\\")))as$I){$ao=$I["Trigger"];if(!queries("CREATE TRIGGER ".($zn==DB?idf_escape("copy_$ao"):idf_escape($zn).".".idf_escape($ao))." $I[Timing] $I[Event] ON $B FOR EACH ROW\n$I[Statement];"))return
false;}}foreach($Ro
as$P){$B=($zn==DB?table("copy_$P"):idf_escape($zn).".".table($P));$Po=view($P);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $B"))||!queries("CREATE VIEW $B AS $Po[select]"))return
false;}return
true;}function
trigger($B,$P){if($B=="")return[];$J=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($B));return
reset($J);}function
triggers($P){$H=[];foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($P,"%_\\")))as$I)$H[$I["Trigger"]]=[$I["Timing"],$I["Event"]];return$H;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER"],"Event"=>["INSERT","UPDATE","DELETE"],"Type"=>["FOR EACH ROW"],];}function
routine($B,$T){if($B=="")return[];$J=get_rows("SELECT PARAMETER_NAME, DTD_IDENTIFIER, PARAMETER_MODE, CHARACTER_SET_NAME
FROM information_schema.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND SPECIFIC_NAME = ".q($B)."
ORDER BY ORDINAL_POSITION");$m=[];foreach($J
as$I){$gf=$I["DTD_IDENTIFIER"];list($io,$x,$so)=parse_type($gf);$m[]=["field"=>$I["PARAMETER_NAME"],"type"=>$io,"length"=>$x,"unsigned"=>$so,"null"=>true,"full_type"=>$gf,"inout"=>($T=="FUNCTION"?"":$I["PARAMETER_MODE"]),"collation"=>$I["CHARACTER_SET_NAME"],];}$H=Connection::get()->query("SELECT
	ROUTINE_COMMENT comment,
	CONCAT(IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC\\n', ''), IF(SQL_DATA_ACCESS != 'CONTAINS SQL', CONCAT(SQL_DATA_ACCESS, '\\n'), '')) characteristics,
	ROUTINE_DEFINITION definition,
	'SQL' language
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND ROUTINE_NAME = ".q($B))->fetchAssoc();$f=Connection::get()->getValue("SHOW CREATE $T ".idf_escape($B),2);$O="'(?:[^'\\\\]|\\\\.)*'";$Rn='`[^`]*`|"(?:[^"\\\]|\\\.)*"|$string|/\*.*?\*/|/';$Bb="    (?:[A-Z ]+|COMMENT (?:$O)+)\\n";if($f&&preg_match("~^(?:[^(`\"'/]++|$Rn)*?(\\((?:[^()`\"'/]++|$Rn|(?1))*+\\))(?: RETURNS [^\\n]*)?\\n(?:$Bb)*~s",$f,$_))$H['definition']=substr($f,strlen($_[0]));$H['definition']=$H['characteristics'].$H['definition'];unset($H['characteristics']);if($m&&$m[0]['field']=='')$H['returns']=array_shift($m);$H['fields']=$m;return$H;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER, ROUTINE_COMMENT FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return[];}function
routine_id($B,array$I){return
idf_escape($B);}function
last_id($G){return
Connection::get()->getValue("SELECT LAST_INSERT_ID()");}function
explain(Connection$e,$F){return$e->query("EXPLAIN ".(Connection::get()->isMinVersion("5.7")?"":"PARTITIONS ").$F);}function
found_rows(array$Q,array$Z){return$Q["Engine"]=="InnoDB"&&!$Z?(int)$Q["Rows"]:null;}function
format_sql($F){$Eh='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$Xg='FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|(NATURAL\s+)?((LEFT|RIGHT)\s+)?((INNER|OUTER|CROSS)\s+)?JOIN';$F=preg_replace("~($Eh)\\s+(AS\\s+SELECT)~isU","$1 AS\nSELECT",$F);$F=preg_replace("~($Eh)\\s+($Xg)~isU","$1\n$2",$F);$F=preg_replace("~($Eh),~isU","$1,\n  ",$F);return$F;}function
create_sql($P,$ab,$Um){$F=Connection::get()->getValue("SHOW CREATE TABLE ".table($P),1);if(!$ab)$F=preg_replace('~ AUTO_INCREMENT=\d+~','',$F);return!str_contains($F,"\n")?format_sql($F):$F;}function
truncate_sql($P){return"TRUNCATE ".table($P);}function
create_database_sql($Mc,$Um=""){$B=idf_escape($Mc);$ac="";if(str_contains($Um,"CREATE")&&($f=Connection::get()->getValue("SHOW CREATE DATABASE $B",1))){set_utf8mb4($f);if($Um=="DROP+CREATE")$ac="DROP DATABASE IF EXISTS $B;\n";$ac
.="$f;\n";}return$ac;}function
use_sql($Mc,$Um=""){return"USE ".idf_escape($Mc).";\n";}function
trigger_sql($P){$Dm="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($P,"%_\\")),null,"-- ")as$I)$Dm
.="\nCREATE TRIGGER ".idf_escape($I["Trigger"])." $I[Timing] $I[Event] ON ".table($I["Table"])." FOR EACH ROW\n$I[Statement];;\n";return$Dm;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$l){if(preg_match("~binary~",$l["type"]))return"HEX(".idf_escape($l["field"]).")";if($l["type"]=="bit")return"BIN(".idf_escape($l["field"])." + 0)";if($l["type"]=="vector")return(Connection::get()->isMariaDB()?"VEC_ToText":"VECTOR_TO_STRING")."(".idf_escape($l["field"]).")";if(preg_match("~geometry|point|linestring|polygon~",$l["type"])){$Bk=(Connection::get()->isMinVersion("8")?"ST_":"");$c=idf_escape($l["field"]);return"CONCAT(IF($Bk"."SRID($c), CONCAT('SRID=', $Bk"."SRID($c), ';'), ''), $Bk"."AsWKT($c))";}return
null;}function
unconvert_field(array$l,$H){if(preg_match("~binary~",$l["type"]))$H="UNHEX($H)";if($l["type"]=="bit")$H="CONVERT(b$H, UNSIGNED)";if($l["type"]=="vector")$H=(Connection::get()->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."($H)";if(preg_match("~geometry|point|linestring|polygon~",$l["type"])){$Jm=isset($l["srid"])?$l["srid"]:0;if(preg_match("~^'SRID=(\\d+);~i",$H,$_)){$Jm=$_[1];$H="'".substr($H,strlen($_[0]));}$H=(Connection::get()->isMinVersion("8")?"ST_":"")."GeomFromText($H, $Jm)";}return$H;}function
support($we){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.8.1":"8")?'|descidx':'').(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.2.1":"8.0.16")?'|check':'').(!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8")?'|fast_status':'').')$~',$we);}function
kill_process($W){return
queries("KILL ".number($W));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return(int)Connection::get()->getValue("SELECT @@max_connections");}}Drivers::add("pgsql","PostgreSQL",["PgSQL","PDO_PgSQL"]);if(isset($_GET["pgsql"])&&!defined("AdminNeo\DRIVER")){define("AdminNeo\DRIVER","pgsql");define("AdminNeo\DIALECT","pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","PgSQL");class
PgSqlConnectionBase
extends
Connection{var$timeout=0;private$connection;private$connectionString;private$hasDefaultDatabase;function
open($L,$U,$E){$i=Admin::get()->getDatabase();set_error_handler(function($Yd,$k){if(ini_bool("html_errors"))$k=html_entity_decode(strip_tags($k));$k=preg_replace('~^[^:]*: ~','',$k);$this->error=$k;});list($Nf,$vk)=host_port($L);$this->connectionString=($Nf!=""?"host='".addcslashes($Nf,"'\\")."' ":"").($vk?"port=$vk ":"")."user='".addcslashes($U,"'\\")."' password='".addcslashes($E,"'\\")."'";$Lm=Admin::get()->getConfig()->getSslMode();if($Lm)$this->connectionString
.=" sslmode='$Lm'";$this->connection=@pg_connect("$this->connectionString dbname='".($i!=""?addcslashes($i,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->connection&&$i!=""){$this->hasDefaultDatabase=false;$this->connection=@pg_connect("$this->connectionString dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}else$this->hasDefaultDatabase=true;restore_error_handler();if($this->connection){$Oo=$this->getValue("SELECT version()");$this->flavor=str_contains($Oo,"CockroachDB")?"cockroach":null;$this->version=preg_replace('~^\D*([\d.]+[-\w]*).*~',"$1",$Oo);pg_set_client_encoding($this->connection,"UTF8");}return(bool)$this->connection;}function
quote($O){return(function_exists('pg_escape_literal')?pg_escape_literal($this->connection,$O):"'".pg_escape_string($this->connection,$O)."'");}function
formatValue($X,array$l){if($l["type"]=="bytea"&&$X!==null)return
pg_unescape_bytea($X);return
parent::formatValue($X,$l);}function
selectDatabase($B){if($B==Admin::get()->getDatabase())return$this->hasDefaultDatabase;$H=@pg_connect("$this->connectionString dbname='".addcslashes($B,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($H)$this->connection=$H;return(bool)$H;}function
close(){$this->connection=@pg_connect("$this->connectionString dbname='postgres'");}function
query($F,$ko=false){if(!$this->connection){$this->error="Invalid connection.";return
false;}if($this->untrusted)$G=(@pg_query($this->connection,"BEGIN READ ONLY")?@pg_query_params($this->connection,$F,[]):false);else$G=@pg_query($this->connection,$F);$this->error="";if(!$G){$this->error=pg_last_error($this->connection);$H=false;}elseif(!pg_num_fields($G)){$this->affectedRows=pg_affected_rows($G);$H=true;}else$H=new
PgSqlResult($G);if($this->untrusted)@pg_query($this->connection,"COMMIT");if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$H;}function
getValue($F,$xe=0){$G=$this->query($F);return
is_object($G)?$G->fetchValue($xe):false;}function
warnings(){if(PHP_VERSION_ID>=70100){$Xo=pg_last_notice($this->connection,PGSQL_NOTICE_ALL);if(!$Xo)return
null;$Xo=implode("\n",$Xo);pg_last_notice($this->connection,PGSQL_NOTICE_CLEAR);}else{$Xo=pg_last_notice($this->connection);if(!$Xo)return
null;}return
nl2br(h($Xo));}function
copyFrom($P,array$J){$this->error="";set_error_handler(function($Yd,$k){$this->error=ini_bool("html_errors")?html_entity_decode($k):$k;});$G=pg_copy_from($this->connection,$P,$J);restore_error_handler();return$G;}function
inTransaction(){$N=pg_transaction_status($this->connection);return$N==PGSQL_TRANSACTION_INTRANS||$N==PGSQL_TRANSACTION_INERROR;}}class
PgSqlResult
extends
Result{private$resource;private$offset=0;function
__construct($ql){parent::__construct(pg_num_rows($ql));$this->resource=$ql;}function
fetchAssoc(){return
pg_fetch_assoc($this->resource);}function
fetchRow(){return
pg_fetch_row($this->resource);}function
fetchValue($xe){return$this->getRowsCount()?pg_fetch_result($this->resource,0,$xe):false;}function
fetchField(){$c=$this->offset++;$B=pg_field_name($this->resource,$c);if($B===false)return
false;$T=pg_field_type($this->resource,$c);if($T===false)return
false;$Cj=pg_field_table($this->resource,$c);return(object)['orgtable'=>($Cj!==false?$Cj:""),'name'=>$B,'native_type'=>$T,'type'=>(preg_match(number_type(),$T)?0:15),'charsetnr'=>($T=="bytea"?63:0),];}}}elseif(extension_loaded("pdo_pgsql")){define("AdminNeo\DRIVER_EXTENSION","PDO_PgSQL");class
PgSqlConnectionBase
extends
PdoConnection{var$timeout=0;function
open($L,$U,$E){$i=Admin::get()->getDatabase();list($Nf,$vk)=host_port($L);$Bd="pgsql:".($Nf!=""?"host='".addcslashes($Nf,"'\\")."' ":"").($vk?"port=$vk ":"")."client_encoding=utf8 dbname='".($i!=""?addcslashes($i,"'\\"):"postgres")."'";$Lm=Admin::get()->getConfig()->getSslMode();if($Lm)$Bd
.=" sslmode='$Lm'";if(!$this->dsn($Bd,$U,$E))return
false;$Oo=$this->getValue("SELECT version()");$this->flavor=str_contains($Oo,"CockroachDB")?"cockroach":null;return
true;}function
selectDatabase($B){return
Admin::get()->getDatabase()==$B;}function
query($F,$ko=false){$H=($this->untrusted?$this->readOnlyQuery($F):parent::query($F,$ko));if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$H;}private
function
readOnlyQuery($F){$this->error="";if(!$this->pdo->query("BEGIN READ ONLY")){$this->storeErrorInfo($this->pdo->errorInfo());return
false;}$Nm=$this->pdo->prepare($F);$H=false;if($Nm&&$Nm->execute()){$H=new
PdoResult($Nm);$this->storeResult($H);}else$this->storeErrorInfo($Nm?$Nm->errorInfo():$this->pdo->errorInfo());$this->pdo->query("COMMIT");return$H;}function
warnings(){return
null;}function
copyFrom($P,array$J){$G=$this->pdo->pgsqlCopyFromArray($P,$J);$ae=$this->pdo->errorInfo();$this->error=isset($ae[2])?$ae[2]:"";return$G;}function
close(){}}}if(class_exists('AdminNeo\PgSqlConnectionBase')){class
PgSqlConnection
extends
PgSqlConnectionBase{private$localhostServer=false;function
open($L,$U,$E){if($L=="localhost")$this->localhostServer=true;return
parent::open($L,$U,$E);}function
getDefaultServerName(){return$this->localhostServer?"localhost":"";}function
multiQuery($F){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$F),$A)){$J=explode("\n",$A[2]);$this->affectedRows=count($J);return$this->copyFrom($A[1],$J);}return
parent::multiQuery($F);}}}class
PgSqlDriver
extends
Driver{protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->types=[lang(126)=>["smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20,],lang(127)=>["date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0,],lang(128)=>["character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0,],lang(130)=>["bit"=>0,"bit varying"=>0,"bytea"=>0,],lang(132)=>["cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0,],lang(131)=>["box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0,],];if($e->isMinVersion("9.2")){$this->types[lang(128)]["json"]=4294967295;$this->types[lang(133)]=["int4range"=>0,"int8range"=>0,"numrange"=>0,"daterange"=>0,"tsrange"=>0,"tstzrange"=>0,];if($e->isMinVersion("9.4"))$this->types[lang(128)]["jsonb"]=4294967295;}if($e->isMinVersion("12")){$this->generated[]="STORED";if($e->isMinVersion("18"))$this->generated[]="VIRTUAL";}$this->operators=["=","<",">","<=",">=","!=","~","~*","!~","!~*","LIKE","LIKE %%","NOT LIKE","ILIKE","ILIKE %%","NOT ILIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->functions=["char_length","lower","upper","round","to_hex","to_timestamp",];$this->grouping=["sum","min","max","avg","count","count distinct",];$this->partitionBy=["RANGE","LIST"];if(!$e->isCockroachDB())$this->partitionBy[]="HASH";$this->insertFunctions=["char"=>"md5","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",];$this->systemDatabases=["template1"];$this->systemSchemas=["information_schema","pg_catalog","pg_toast","pg_temp_*","pg_toast_temp_*"];}function
getNsOidSql(){return"(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";}function
getInsertReturningSql($P){$Za=array_filter(fields($P),function($l){return$l['auto_increment'];});return
count($Za)==1?" RETURNING ".idf_escape(key($Za)):"";}function
insertUpdate($P,array$bl,array$Fk){foreach($bl
as$al){$vo=[];$Z=[];foreach($al
as$w=>$W){$vo[]="$w = $W";if(isset($Fk[idf_unescape($w)]))$Z[]="$w = $W";}if(!(($Z&&queries("UPDATE ".table($P)." SET ".implode(", ",$vo)." WHERE ".implode(" AND ",$Z))&&Connection::get()->getAffectedRows())||queries("INSERT INTO ".table($P)." (".implode(", ",array_keys($al)).") VALUES (".implode(", ",$al).")")))return
false;}return
true;}function
slowQuery($F,$Kn){$this->connection->query("SET statement_timeout = ".(1000*$Kn));$this->connection->timeout=1000*$Kn;return$F;}function
convertSearch($t,array$Z,array$l){if(preg_match('~\[~',$l["full_type"]))return($this->isArrayComparison($Z)?$t:"CAST($t AS text)");if(preg_match('~range$~',$l["type"]))return(preg_match('~^(list(<>) =?|!?=)$~',$Z["op"])&&preg_match('~^\s*(empty|[[(].*,.*[])])\s*$~i',$Z["val"])?$t:"CAST($t AS text)");$En="char|text";if(strpos($Z["op"],"LIKE")===false&&strpos($Z["op"],"~")===false)$En
.="|date|time(stamp)?|boolean|uuid|inet|cidr|macaddr|".number_type();return(preg_match("~$En~",$l["type"])?$t:"CAST($t AS text)");}function
isSearchable(array$Z,array$l){if(strpos($Z["op"],"LIKE")!==false||strpos($Z["op"],"~")!==false)return
true;if(preg_match('~\[~',$l["full_type"])){if(!$this->isArrayComparison($Z))return
true;if(!preg_match('~^\s*\{([^{}"]*)\}\s*$~',$Z["val"],$_))return
false;foreach(($_[1]!=""?explode(",",$_[1]):[])as$Nd){if(!preg_match('~^\s*null\s*$~i',$Nd)&&!$this->isValidValue($l["type"],trim($Nd)))return
false;}return
true;}$Y=[$Z["val"]];if(preg_match('~IN$~',$Z["op"]))$Y=parse_search_list($Z["val"]);foreach($Y
as$X){if(!$this->isValidValue($l["type"],$X))return
false;}return
true;}private
function
isArrayComparison(array$Z){return
preg_match('~^(list(<>) =?|!?=)$~',$Z["op"])&&preg_match('~^\s*\{.*\}\s*$~s',$Z["val"]);}private
function
isValidValue($T,$X){if(preg_match('~^'.int_type().'$~',$T))return(bool)preg_match('~^\s*[-+]?\d+\s*$~',$X);if(preg_match(number_type(),$T))return
is_numeric(trim($X));switch($T){case"boolean":return(bool)preg_match('~^\s*(t(r(ue?)?)?|f(a(l(se?)?)?)?|y(es?)?|no?|on|off?|1|0)\s*$~i',$X);case"uuid":return(bool)preg_match('~^(\{[0-9a-f]{4}(-?[0-9a-f]{4}){7}\}|[0-9a-f]{4}(-?[0-9a-f]{4}){7})$~i',$X);case"inet":case"cidr":if(!preg_match('~^([^/]+)(/(\d+))?$~',$X,$_)||!filter_var($_[1],FILTER_VALIDATE_IP))return
false;$Ba=inet_pton($_[1]);$x=strlen($Ba)*8;$Lh=isset($_[3])?(int)$_[3]:$x;if($Lh>$x)return
false;$lb=implode("",array_map(function($ub){return
str_pad(decbin(ord($ub)),8,"0",STR_PAD_LEFT);},str_split($Ba)));return$T!="cidr"||strpos(substr($lb,$Lh),"1")===false;case"macaddr":return(bool)preg_match('~^\s*([0-9a-f]{1,2}(:[0-9a-f]{1,2}){5}|[0-9a-f]{1,2}(-[0-9a-f]{1,2}){5}|[0-9a-f]{6}[:-][0-9a-f]{6}|'.'[0-9a-f]{4}([.-])[0-9a-f]{4}\4[0-9a-f]{4}|[0-9a-f]{12})\s*$~i',$X);case"macaddr8":$am=preg_replace('~[\s0-9a-f]~i','',$X);return
preg_match('~^\s*[0-9a-f]{2}([:.-]?[0-9a-f]{2})+\s*$~i',$X)&&strlen(count_chars($am,3))<=1&&in_array(strlen(preg_replace('~[^0-9a-f]~i','',$X)),[12,16]);case"time":case"time without time zone":case"time with time zone":return(bool)preg_match('~^\s*(([01]?\d|2[0-3]):[0-5]\d(:[0-5]\d(\.\d+)?)?|([01]\d|2[0-3])[0-5]\d([0-5]\d(\.\d+)?)?|24:00(:00)?|'.'(0?[1-9]|1[0-2]):[0-5]\d(:[0-5]\d(\.\d+)?)?\s*[ap]m)(\s*(z|utc|[+-](0?\d|1[0-5])(:?[0-5]\d)?))?\s*$~i',$X);}return
true;}function
quoteBinary($O){return"'\\x".bin2hex($O)."'";}function
warnings(){return$this->connection->warnings();}function
tableHelp($B,$Dg=false){$yh=["information_schema"=>"infoschema","pg_catalog"=>($Dg?"view":"catalog"),];$z=$yh[$_GET["ns"]];if($z)return"$z-".str_replace("_","-",$B).".html";return
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
getPartitionsInfo($P){if(!$this->connection->isMinVersion("10"))return[];$I=$this->connection->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($P))->fetchAssoc();if(!$I)return[];$Wa=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = {$I["partrelid"]} AND attnum IN (".str_replace(" ",", ",$I["partattrs"]).")");$tb=['h'=>'HASH','l'=>'LIST','r'=>'RANGE'];$hg=["partition_by"=>$tb[$I["partstrat"]],"partition"=>implode(", ",array_map('AdminNeo\idf_escape',$Wa)),];$ek=get_key_vals("SELECT relname, pg_get_expr(c.relpartbound, c.oid) AS bound
FROM pg_inherits
JOIN pg_class c ON inhrelid = oid
WHERE inhparent = ".$this->tableOid($P)." ORDER BY 1");$hg["partition_names"]=array_keys($ek);$hg["partition_values"]=array_map(function($X){return
str_replace("FOR VALUES ","",$X);},array_values($ek));return$hg;}function
tableOid($P){return"(SELECT oid FROM pg_class WHERE relnamespace = ".$this->getNsOidSql()." AND relname = ".q($P)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
getIndexAlgorithms(array$jn){static$li=[];if(!$li)$li=get_vals("SELECT amname FROM pg_am".($this->connection->isMinVersion("9.6")?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->connection->isCockroachDB()?"prefix":"btree")."' DESC, amname");return$li;}function
getIndexOpclasses(){static$qj=[];if(!$qj&&!$this->connection->isCockroachDB())$qj=get_vals("SELECT DISTINCT opcname FROM pg_catalog.pg_opclass WHERE NOT opcdefault ORDER BY opcname");return$qj;}function
supportsIndex(array$jn){return$jn["Engine"]!="view";}function
hasCStyleEscapes(){static$vb;if($vb===null)$vb=($this->connection->getValue("SHOW standard_conforming_strings")=="off");return$vb;}function
explodeArrayValue($X,$T,&$Hl){$Lo=["oidvector"=>"oid","int2vector"=>"smallint",];$Hl=isset($Lo[$T])?$Lo[$T]:null;if($Hl)return
explode(" ",$X);if(preg_match('~^(.+)\[]$~',$T,$A)){$Hl=$A[1];$X=preg_replace('~^\{(.*)}$~',"$1",$X);$zg=str_contains($Hl,"json");if($zg){$Y=explode('","',trim($X,'"'));array_walk($Y,function(&$V){$V=str_replace('\"','"',$V);});}elseif(preg_match('~^(\{+)(.*)(}+)$~',$X,$A)){$Y=explode("$A[3],$A[1]",$A[2]);array_walk($Y,function(&$V)use($A){$V=$A[1].$V.$A[3];});}else$Y=explode(",",$X);if(!$zg&&$Y[0][0]=="{"){$T=$Hl;array_walk($Y,function(&$V)use($T,&$Hl){$V=$this->explodeArrayValue($V,$T,$Hl);});}elseif(!$zg&&isset($this->types[lang(128)][$Hl])){array_walk($Y,function(&$V){$V=preg_replace('~^\'(.*)\'$~',"$1",$V);});}return$Y;}return[];}function
implodeArrayValues(array$Y,$T){if($T=="oidvector"||$T=="int2vector")return
implode(" ",$Y);if(preg_match('~^([^[]+)(\[])+$~',$T,$A)){$Cg=isset($this->types[lang(128)][$A[1]]);$zg=str_contains($T,"json");array_walk($Y,function(&$V)use($T,$Cg,$zg){if(is_array($V))$V=$this->implodeArrayValues($V,$T);elseif($zg)$V="\"$V\"";elseif($Cg)$V="'$V'";});return"{".implode(",",$Y)."}";}return"";}}function
create_driver(Connection$e){return
PgSqlDriver::create($e,Admin::get());}function
idf_escape($t){return'"'.str_replace('"','""',$t).'"';}function
table($t){return
idf_escape($t);}function
connect($Fk=false,&$k=null){$e=$Fk?PgSqlConnection::create():PgSqlConnection::createSecondary();list($L,$U,$E)=Admin::get()->getCredentials();$G=$e->openPasswordless($L,$U,$E,false);$ae=[];if(!$G&&$L==""&&preg_match('~connection to server on socket .+ failed: No such file or directory~U',$e->getError())){$ae[]=$e->getError();$L="localhost";$G=$e->openPasswordless($L,$U,$E,false);}if(!$G){$ae[]=$e->getError();$k=implode("\n",$ae);return
null;}if($e->isMinVersion("9"))$e->query("SET application_name = 'AdminNeo'");if($Fk&&$e->isCockroachDB()){Drivers::setName(DRIVER,"CockroachDB");save_driver_name(DRIVER,$L,"CockroachDB");}return$e;}function
get_databases($Qe){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($F,$Z,$y,$Yi=0,$Zl=" "){return" $F$Z".($y?$Zl."LIMIT $y".($Yi?" OFFSET $Yi":""):"");}function
limit1($P,$F,$Z,$Zl="\n"){return(preg_match('~^INTO~',$F)?limit($F,$Z,1,0,$Zl):" $F".(is_view(table_status1($P))?$Z:$Zl."WHERE ctid = (SELECT ctid FROM ".table($P).$Z.$Zl."LIMIT 1)"));}function
db_collation($i,array$Tb){return
Connection::get()->getValue("SELECT datcollate FROM pg_database WHERE datname = ".q($i));}function
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
count_tables(array$h){$H=[];foreach($h
as$i){if(Connection::get()->selectDatabase($i))$H[$i]=count(tables_list());}return$H;}function
table_status($B="",$ve=false){static$Af;if($Af===null)$Af=Connection::get()->getValue("SELECT 'pg_table_size'::regproc");$H=[];foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\",
	CASE relkind WHEN 'p' THEN 'partitioned' ELSE '' END AS \"Create_options\"".($Af?",
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
".($B!=""?"AND relname = ".q($B):"ORDER BY relname"))as$I)$H[$I["Name"]]=$I;return$H;}function
is_view(array$Q){return
in_array($Q["Engine"],["view","materialized view"]);}function
fk_support(array$Q){return
true;}function
fields($P){$H=[];$Ha=['timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',];foreach(get_rows("SELECT
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
ORDER BY a.attnum")as$I){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$I["full_type"],$_);list(,$T,$x,$I["length"],$Aa,$Sa)=$_;$Gb=$T.$Aa;if(isset($Ha[$Gb])){$I["type"]=$Ha[$Gb];$I["full_type"]=$I["type"].$x;}else{$I["type"]=$T;$I["full_type"]=$I["type"].$x.$Aa;}for($r=0;$r<$I["attndims"];$r++){$I["length"].=$Sa;$I["full_type"].=$Sa;}if(in_array($I['attidentity'],['a','d']))$I['default']='GENERATED '.($I['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$C=["s"=>"STORED","v"=>"VIRTUAL"];$I["generated"]=(isset($C[$I["attgenerated"]])?$C[$I["attgenerated"]]:"");$I["null"]=!$I["attnotnull"];$I["auto_increment"]=preg_match('~^nextval\(~i',$I["default"])||preg_match('~^unique_rowid\(~',$I["default"]);$I["privileges"]=["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1];if(!$I['generated']&&preg_match('~(.+)::[^,)]+(.*)~s',$I["default"],$_))$I["default"]=($_[1]=="NULL"?null:idf_unescape($_[1]).$_[2]);$H[$I["field"]]=$I;}return$H;}function
indexes($P,$e=null){if(!is_object($e))$e=Connection::get();$H=[];$on=Driver::get()->tableOid($P);$d=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $on AND attnum > 0",$e);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname,
	pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr".($e->isCockroachDB()?"":",
	(SELECT string_agg(CASE WHEN opcdefault THEN '' ELSE opcname END, ' ' ORDER BY s)
		FROM generate_subscripts(indclass, 1) AS s
		JOIN pg_catalog.pg_opclass ON pg_opclass.oid = indclass[s]) AS opclasses")."
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $on
ORDER BY indisprimary DESC, indisunique DESC",$e)as$I){$ll=$I["relname"];$H[$ll]["type"]=($I["indisprimary"]?"PRIMARY":($I["indisunique"]?"UNIQUE":"INDEX"));$H[$ll]["columns"]=[];$H[$ll]["descs"]=[];$H[$ll]["algorithm"]=$I["amname"];$H[$ll]["partial"]=$I["partial"];$eg=preg_split('~(?<=\)), (?=\()~',$I["indexpr"]);foreach(explode(" ",$I["indkey"])as$fg)$H[$ll]["columns"][]=($fg?$d[$fg]:array_shift($eg));foreach(explode(" ",$I["indoption"])as$gg)$H[$ll]["descs"][]=(intval($gg)&1?'1':null);$H[$ll]["opclasses"]=(isset($I["opclasses"])?explode(" ",$I["opclasses"]):[]);$H[$ll]["lengths"]=[];}return$H;}function
foreign_keys($P){$kj=implode("|",Driver::get()->getOnActions());$H=[];foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, condeferred::int AS deferred, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".Driver::get()->tableOid($P)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$I){$I['deferrable']=($I['deferrable']?'':'NOT ').'DEFERRABLE'.($I['deferred']?' INITIALLY DEFERRED':'');if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$I['definition'],$_)){$I['source']=array_map('AdminNeo\idf_unescape',array_map('trim',explode(',',$_[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$_[2],$Mh)){$I['ns']=idf_unescape($Mh[2]);$I['table']=idf_unescape($Mh[4]);}$I['target']=array_map('AdminNeo\idf_unescape',array_map('trim',explode(',',$_[3])));$I['on_delete']=(preg_match("~ON DELETE ($kj)~",$_[4],$Mh)?$Mh[1]:'NO ACTION');$I['on_update']=(preg_match("~ON UPDATE ($kj)~",$_[4],$Mh)?$Mh[1]:'NO ACTION');$H[$I['conname']]=$I;}}return$H;}function
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
view($B){return["select"=>trim(Connection::get()->getValue("SELECT pg_get_viewdef(".Driver::get()->tableOid($B).")"))];}function
collations(){return[];}function
information_schema($i,$Ll=""){return
in_array($Ll!=""?$Ll:get_schema(),["information_schema","pg_catalog","pg_toast"]);}function
error(){$H=h(Connection::get()->getError());if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$H,$_))$H=$_[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($_[3]).'})(.*)~','\1<b>\2</b>',$_[2]).$_[4];return
nl2br($H);}function
create_database($i,$Sb){return(bool)queries("CREATE DATABASE ".idf_escape($i).($Sb?" ENCODING ".idf_escape($Sb):""));}function
drop_databases(array$h){Connection::get()->close();return
apply_queries("DROP DATABASE",$h,'AdminNeo\idf_escape');}function
rename_database($B,$Sb){Connection::get()->close();return(bool)queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($B));}function
auto_increment(){return"";}function
alter_table($P,$B,array$m,array$Se,$cc,$Td,$Sb,$ab,$dk){$b=[];$Qk=[];if($P!=""&&$P!=$B)$Qk[]="ALTER TABLE ".table($P)." RENAME TO ".table($B);$bm="";foreach($m
as$l){$c=idf_escape($l[0]);$W=$l[1];if(!$W)$b[]="DROP $c";else{$Io=$W[5];unset($W[5]);if($l[0]==""){if(isset($W[6]))$W[1]=($W[1]==" bigint"?" big":($W[1]==" smallint"?" small":" "))."serial";$b[]=($P!=""?"ADD ":"  ").implode($W);if(isset($W[6]))$b[]=($P!=""?"ADD":" ")." PRIMARY KEY ($W[0])";}else{if($c!=$W[0])$Qk[]="ALTER TABLE ".table($B)." RENAME $c TO $W[0]";$b[]="ALTER $c TYPE$W[1]";$cm=$P."_".idf_unescape($W[0])."_seq";$b[]="ALTER $c ".($W[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) (STORED|VIRTUAL)~','EXPRESSION\1',$W[3]):(isset($W[6])?"SET DEFAULT nextval(".q($cm).")":"DROP DEFAULT"));if(isset($W[6]))$bm="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($cm)." OWNED BY ".idf_escape($P).".$W[0]";$b[]="ALTER $c ".($W[2]==" NULL"?"DROP NOT":"SET").$W[2];}if($l[0]!=""||$Io!="")$Qk[]="COMMENT ON COLUMN ".table($B).".$W[0] IS ".($Io!=""?substr($Io,9):"''");}}$b=array_merge($b,$Se);if($P==""){$N="";if($dk){$N=" PARTITION BY {$dk["partition_by"]}({$dk["partition"]})";if($dk["partition_by"]=='HASH'){$ek=(int)$dk["partitions"];for($r=0;$r<$ek;$r++)$Qk[]="CREATE TABLE ".idf_escape($B."_$r")." PARTITION OF ".idf_escape($B)." FOR VALUES WITH (MODULUS $ek, REMAINDER $r)";}else{$Ob=Connection::get()->isCockroachDB();$Ek="MINVALUE";foreach($dk["partition_names"]as$r=>$W){$X=$dk["partition_values"][$r];$Yj=" VALUES ".($dk["partition_by"]=='LIST'?"IN ($X)":"FROM ($Ek) TO ($X)");if($Ob)$N
.=($r?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$W)?$W:idf_escape($W)).$Yj;else$Qk[]="CREATE TABLE ".idf_escape($B."_$W")." PARTITION OF ".idf_escape($B)." FOR$Yj";$Ek=$X;}$N
.=$Ob?"\n)":"";}}array_unshift($Qk,"CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$N");}elseif($b)array_unshift($Qk,"ALTER TABLE ".table($P)."\n".implode(",\n",$b));if($bm)array_unshift($Qk,$bm);if($cc!==null)$Qk[]="COMMENT ON TABLE ".table($B)." IS ".q($cc);if($ab!="");foreach($Qk
as$F){if(!queries($F))return
false;}return
true;}function
alter_indexes($P,array$b){$f=[];$xd=[];$Qk=[];foreach($b
as$W){if($W[0]!="INDEX")$f[]=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");elseif($W[2]=="DROP")$xd[]=idf_escape($W[1]);else$Qk[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($P."_"))." ON ".table($P).($W[3]?" USING $W[3]":"")." (".implode(", ",$W[2]).")".($W[4]?" WHERE $W[4]":"");}if($f)array_unshift($Qk,"ALTER TABLE ".table($P).implode(",",$f));if($xd)array_unshift($Qk,"DROP INDEX ".implode(", ",$xd));foreach($Qk
as$F){if(!queries($F))return
false;}return
true;}function
truncate_tables(array$R){return(bool)queries("TRUNCATE ".implode(", ",array_map('AdminNeo\table',$R)));}function
drop_views(array$Ro){return
drop_tables($Ro);}function
drop_tables(array$R){foreach($R
as$P){$N=table_status1($P);if(!queries("DROP ".strtoupper($N["Engine"])." ".table($P)))return
false;}return
true;}function
move_tables(array$R,array$Ro,$zn){foreach(array_merge($R,$Ro)as$P){$N=table_status1($P);if(!queries("ALTER ".strtoupper($N["Engine"])." ".table($P)." SET SCHEMA ".idf_escape($zn)))return
false;}return
true;}function
trigger($B,$P){if($B=="")return["Statement"=>"EXECUTE PROCEDURE ()"];$d=[];$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($P)." AND trigger_name = ".q($B);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$I)$d[]=$I["event_object_column"];$ao=[];foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event",
	\'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers '."$Z ORDER BY event_manipulation DESC")as$I){if($d&&$I["Event"]=="UPDATE")$I["Event"].=" OF";$I["Of"]=implode(", ",$d);if($ao)$I["Event"].=" OR $ao[Event]";$ao=$I;}return$ao;}function
triggers($P){$H=[];foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($P))as$I){$ao=trigger($I["trigger_name"],$P);$H[$ao["Trigger"]]=[$ao["Timing"],$ao["Event"]];}return$H;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER"],"Event"=>["INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",],"Type"=>["FOR EACH ROW","FOR EACH STATEMENT"],];}function
routine($B,$T){$hg=get_rows('SELECT routine_definition, LOWER(external_language) AS language, type_udt_name
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($B));$hg=isset($hg[0])?$hg[0]:[];$m=get_rows("SELECT COALESCE(parameter_name, ordinal_position::text) AS field,
	CASE data_type WHEN 'USER-DEFINED' THEN udt_name WHEN 'ARRAY' THEN substr(udt_name, 2) || '[]' ELSE data_type END AS type,
	character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = ".q($B)."
ORDER BY ordinal_position");return["fields"=>$m,"returns"=>["type"=>preg_replace('~^_(.*)~','\1[]',isset($hg["type_udt_name"])?$hg["type_udt_name"]:"")],"definition"=>isset($hg["routine_definition"])?$hg["routine_definition"]:null,"language"=>isset($hg["language"])?$hg["language"]:null,"comment"=>null,];}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_name AS "ROUTINE_NAME", routine_type AS "ROUTINE_TYPE", type_udt_name AS "DTD_IDENTIFIER", null AS ROUTINE_COMMENT
FROM information_schema.routines
WHERE routine_schema = current_schema()'.(Connection::get()->isCockroachDB()?'':"
AND substring(specific_name, '[0-9]+\$')::oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_proc'::regclass AND deptype IN ('e', 'i'))").'
ORDER BY SPECIFIC_NAME');}function
routine_languages(){return
get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language");}function
routine_id($B,array$I){$H=[];foreach($I["fields"]as$l){$x=$l["length"];$H[]=$l["type"].($x?"($x)":"");}return
idf_escape($B)."(".implode(", ",$H).")";}function
last_id($G){$I=$G
instanceof
Result?$G->fetchRow():[];return$I?$I[0]:0;}function
explain(Connection$e,$F){return$e->query("EXPLAIN $F");}function
found_rows(array$Q,array$Z){if(preg_match("~ rows=([0-9]+)~",Connection::get()->getValue("EXPLAIN SELECT * FROM ".idf_escape($Q["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$kl))return(int)$kl[1];return
null;}function
types($qe=false){$Ob=Connection::get()->isCockroachDB();$ah=($Ob?"'e'":"'b','c','d','e'".(Connection::get()->isMinVersion("9.2")?",'r'":""));return
get_key_vals("SELECT t.oid, t.typname
FROM pg_type t
WHERE t.typnamespace = ".Driver::get()->getNsOidSql()."
AND t.typtype IN ($ah)".($Ob?"
AND t.typelem = 0":"
AND (t.typrelid = 0 OR (SELECT c.relkind FROM pg_class c WHERE c.oid = t.typrelid) = 'c')"."
AND NOT EXISTS (SELECT 1 FROM pg_type e WHERE e.typarray = t.oid)".($qe?'':"
AND t.oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_type'::regclass AND deptype = 'e')"))."
ORDER BY t.typname");}function
type_values($s){$Xd=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $s ORDER BY enumsortorder");if(!$Xd)return"";$Y=[];foreach($Xd
as$Vd)$Y[]="'".str_replace("'","''",addcslashes($Vd,"\\"))."'";return
implode(", ",$Y);}function
collation_name($Zi){return(Connection::get()->isMinVersion("9.1")?"(SELECT collname FROM pg_collation WHERE oid = $Zi AND collname != 'default')":"NULL");}function
type_definition($s){$T=first(get_rows("SELECT typtype, typisdefined::int AS defined, typrelid FROM pg_type WHERE oid = $s"));$H=["kind"=>($T?$T["typtype"]:""),"definition"=>""];if(!$T||!$T["defined"])return$H;switch($H["kind"]){case'e':$Y=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $s ORDER BY enumsortorder");$H["definition"]="AS ENUM (".implode(", ",array_map('AdminNeo\q',$Y)).")";break;case'c':$d=[];$J=get_rows("SELECT attname, format_type(atttypid, atttypmod) AS full_type, ".collation_name("attcollation")." AS collation
FROM pg_attribute
WHERE attrelid = $T[typrelid] AND attnum > 0 AND NOT attisdropped
ORDER BY attnum");foreach($J
as$I)$d[]=idf_escape($I["attname"])." $I[full_type]".($I["collation"]?" COLLATE ".idf_escape($I["collation"]):"");$H["definition"]="AS (\n\t".implode(",\n\t",$d)."\n)";break;case'd':$sd=first(get_rows("SELECT format_type(typbasetype, typtypmod) AS base, typnotnull::int AS notnull, typdefault, ".collation_name("typcollation")." AS collation
FROM pg_type
WHERE oid = $s"));$H["definition"]="AS $sd[base]".($sd["collation"]?" COLLATE ".idf_escape($sd["collation"]):"").($sd["typdefault"]!=""?" DEFAULT $sd[typdefault]":"").($sd["notnull"]?" NOT NULL":"");$J=get_rows("SELECT conname, pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE contypid = $s AND contype != 'n' ORDER BY conname");foreach($J
as$I)$H["definition"].=" CONSTRAINT ".idf_escape($I["conname"])." $I[definition]";break;case'r':$Xk=first(get_rows("SELECT format_type(rngsubtype, NULL) AS subtype,
	(SELECT opcname FROM pg_opclass WHERE oid = rngsubopc) AS subtype_opclass,
	".collation_name("rngcollation")." AS collation,
	NULLIF(rngcanonical, 0)::regproc::text AS canonical,
	NULLIF(rngsubdiff, 0)::regproc::text AS subtype_diff".(Connection::get()->isMinVersion("14")?",
	(SELECT typname FROM pg_type WHERE oid = rngmultitypid) AS multirange_type_name":"")."
FROM pg_range
WHERE rngtypid = $s"));$C=[];foreach(["subtype"=>0,"subtype_opclass"=>1,"collation"=>1,"canonical"=>0,"subtype_diff"=>0,"multirange_type_name"=>1]as$w=>$be){if((isset($Xk[$w])?$Xk[$w]:"")!="")$C[]=strtoupper($w)." = ".($be?idf_escape($Xk[$w]):$Xk[$w]);}$H["definition"]="AS RANGE (".implode(", ",$C).")";break;}return$H;}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return(string)Connection::get()->getValue("SELECT current_schema()");}function
set_schema($Ll,$e=null){if(!$e)$e=Connection::get();$G=(bool)$e->query("SET search_path TO ".idf_escape($Ll));Driver::get()->setUserTypes(types(true));return$G;}function
foreign_keys_sql($P){$H="";$N=table_status1($P);$Qi=idf_escape($N['nspname']);$Ne=foreign_keys($P);ksort($Ne);foreach($Ne
as$Me=>$Le)$H
.="ALTER TABLE ONLY $Qi.".idf_escape($N['Name'])." ADD CONSTRAINT ".idf_escape($Me)." ".preg_replace('~( REFERENCES )([^(.]+\()~',"\\1$Qi.\\2",$Le["definition"]).";\n";return($H?"$H\n":$H);}function
foreign_key_checks_sql($Qd){return"SET session_replication_role = ".($Qd?"DEFAULT":"replica").";\n";}function
restart_sequences_sql($P){$H="";foreach(fields($P)as$l){if(!$l["auto_increment"]||preg_match('~^unique_rowid\(~',$l["default"]))continue;$bm="pg_get_serial_sequence(".q(table($P)).", ".q($l["field"]).")";$c=idf_escape($l["field"]);$H
.="SELECT setval($bm, MAX($c)) FROM ".table($P)." HAVING MAX($c) IS NOT NULL;\n";}return$H;}function
create_sql($P,$ab,$Um){$ul=[];$dm=[];$N=table_status1($P);$Qi=idf_escape($N['nspname']);if(is_view($N)){$Po=view($P);return
rtrim("CREATE VIEW $Qi.".idf_escape($P)." AS $Po[select]",";");}$m=fields($P);if(count($N)<2||empty($m))return"";$H="CREATE TABLE $Qi.".idf_escape($N['Name'])." (\n    ";foreach($m
as$l){if($l['default']=="nextval('$N[Name]_$l[field]_seq')"){$l['default']=null;$l['full_type']=preg_replace('~int(eger)?~','serial',$l['full_type']);}$Wj=idf_escape($l['field']).' '.$l['full_type'].preg_replace('~(nextval\(\')([^.\']+\')~','\1'.str_replace("'","''",$N['nspname']).'.\2',default_value($l)).($l['null']?"":" NOT NULL");$ul[]=$Wj;if(preg_match('~nextval\(\'([^\']+)\'\)~',$l['default'],$A)){$cm=$A[1];$J=get_rows((Connection::get()->isMinVersion("10")?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($cm)):"SELECT * FROM $cm"),null,"-- ");if($J){$Cm=first($J);$dm[]=($Um=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $Qi.$cm;\n":"")."CREATE SEQUENCE $Qi.$cm INCREMENT $Cm[increment_by] MINVALUE $Cm[min_value] MAXVALUE $Cm[max_value]".($ab&&$Cm['last_value']?" START ".($Cm["last_value"]+1):"")." CACHE $Cm[cache_value];";}}}if(!empty($dm))$H=implode("\n\n",$dm)."\n\n$H";$Fk="";foreach(indexes($P)as$cg=>$u){if($u['type']=='PRIMARY'){$Fk=$cg;$ul[]="CONSTRAINT ".idf_escape($cg)." PRIMARY KEY (".implode(', ',array_map('AdminNeo\idf_escape',$u['columns'])).")";}}foreach(Driver::get()->checkConstraints($P)as$lc=>$pc)$ul[]="CONSTRAINT ".idf_escape($lc)." CHECK ($pc)";$H
.=implode(",\n    ",$ul)."\n)";$Yj=Driver::get()->getPartitionsInfo($N['Name']);if($Yj)$H
.="\nPARTITION BY {$Yj["partition_by"]}({$Yj["partition"]})";$H
.="\nWITH (oids = ".($N['Oid']?'true':'false').");";if($N['Comment'])$H
.="\n\nCOMMENT ON TABLE $Qi.".idf_escape($N['Name'])." IS ".q($N['Comment']).";";foreach($m
as$_e=>$l){if($l['comment'])$H
.="\n\nCOMMENT ON COLUMN $Qi.".idf_escape($N['Name']).".".idf_escape($_e)." IS ".q($l['comment']).";";}foreach(get_rows("SELECT indexdef FROM pg_catalog.pg_indexes WHERE schemaname = current_schema() AND tablename = ".q($P).($Fk?" AND indexname != ".q($Fk):""),null,"-- ")as$I)$H
.="\n\n$I[indexdef];";return
rtrim($H,';');}function
truncate_sql($P){return"TRUNCATE ".table($P);}function
trigger_sql($P){$N=table_status1($P);$Dm="";foreach(triggers($P)as$Zn=>$Yn){$ao=trigger($Zn,$N['Name']);$Dm
.="\nCREATE TRIGGER ".idf_escape($ao['Trigger'])." $ao[Timing] $ao[Event] ON ".idf_escape($N["nspname"]).".".idf_escape($N['Name'])." $ao[Type] $ao[Statement];;\n";}return$Dm;}function
create_database_sql($Mc,$Um=""){$B=idf_escape($Mc);$ac="";if(str_contains($Um,"CREATE")){if($Um=="DROP+CREATE")$ac="DROP DATABASE IF EXISTS $B;\n";$ac
.="CREATE DATABASE $B;\n";}return$ac;}function
use_sql($Mc,$Um=""){return'\connect '.idf_escape($Mc).";\n";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(Connection::get()->isMinVersion("9.2")?"pid":"procpid"));}function
convert_field(array$l){return
null;}function
unconvert_field(array$l,$H){return$H;}function
support($we){if($we=="processlist")return!Connection::get()->isCockroachDB();elseif($we=="materializedview")return
Connection::get()->isMinVersion("9.3");elseif($we=="procedure")return
Connection::get()->isMinVersion("11");return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|fast_status|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table|transaction_ddl|trigger|type|variables|view)$~',$we);}function
kill_process($W){return
queries("SELECT pg_terminate_backend(".number($W).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return(int)Connection::get()->getValue("SHOW max_connections");}}Drivers::add("sqlite","SQLite",["SQLite3","PDO_SQLite"]);if(isset($_GET["sqlite"])&&!defined("AdminNeo\DRIVER")){define("AdminNeo\DRIVER","sqlite");define("AdminNeo\DIALECT","sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","SQLite3");abstract
class
SqLiteConnectionBase
extends
Connection{private$sqlite;function
open($L,$U,$E){$this->sqlite=new
SQLite3($L);if(method_exists($this->sqlite,"setAuthorizer"))$this->sqlite->setAuthorizer([$this,"authorize"]);$this->version=$this->sqlite->version()["versionString"];return
true;}function
query($F,$ko=false){$G=@$this->sqlite->query($F);$this->error="";if(!$G){$this->errno=$this->sqlite->lastErrorCode();$this->error=$this->sqlite->lastErrorMsg();return
false;}elseif($G->numColumns())return
new
SqLiteResult($G);$this->affectedRows=$this->sqlite->changes();return
true;}function
quote($O){if(is_utf8($O))return"'".$this->sqlite->escapeString($O)."'";else
return"x'".bin2hex($O)."'";}}class
SqLiteResult
extends
Result{private$resource;private$offset=0;function
__construct(SQLite3Result$ql){parent::__construct(0);$this->resource=$ql;}function
fetchAssoc(){return$this->resource->fetchArray(SQLITE3_ASSOC);}function
fetchRow(){return$this->resource->fetchArray(SQLITE3_NUM);}function
fetchField(){$jo=[1=>"integer","real","text","blob","null"];$c=$this->offset++;$T=$this->resource->columnType($c);if($T===false)return
false;return(object)["name"=>$this->resource->columnName($c),"native_type"=>$jo[$T],"type"=>($T==SQLITE3_TEXT?15:0),"charsetnr"=>($T==SQLITE3_BLOB?63:0),];}}}elseif(extension_loaded("pdo_sqlite")){define("AdminNeo\DRIVER_EXTENSION","PDO_SQLite");abstract
class
SqLiteConnectionBase
extends
PdoConnection{function
open($L,$U,$E){if(!$this->dsn(DRIVER.":$L","","",[],class_exists('Pdo\Sqlite')?'Pdo\Sqlite':'PDO'))return
false;if(method_exists($this->pdo,"setAuthorizer"))$this->pdo->setAuthorizer([$this,"authorize"]);return
true;}function
quote($O){if(is_utf8($O))return
parent::quote($O);else
return"x'".bin2hex($O)."'";}}}if(class_exists('AdminNeo\SqLiteConnectionBase')){class
SqLiteConnection
extends
SqLiteConnectionBase{private$attaching=false;protected
function
__construct(){parent::__construct();$this->open(":memory:","","");}function
open($L,$U,$E){if(stripos($L,"file:")===0)$L="./$L";if(!parent::open($L,$U,$E))return
false;$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return
true;}function
close(){$this->open(":memory:","","");}function
selectDatabase($B){if(stripos($B,"file:")===0)$B="./$B";$this->attaching=true;$Ua=is_readable($B)&&$this->query("ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$B)?$B:dirname($_SERVER["SCRIPT_FILENAME"])."/$B")." AS a");$this->attaching=false;if($Ua)return
self::open($B,"","");return
false;}function
authorize($wa,$Qa){return($wa!=24||$Qa===""||$this->attaching?0:1);}}}class
SqliteDriver
extends
Driver{protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->types=[["integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0,]];if($e->isMinVersion("3.31"))$this->generated=["STORED","VIRTUAL"];if($e->isMinVersion("3.37"))$this->types[0]["any"]=0;$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->functions=["length","lower","upper","round","hex","unixepoch",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->insertFunctions=[];$this->editFunctions=["integer|real|numeric"=>"+/-","text"=>"||",];}function
getStructuredTypes(){return
parent::getStructuredTypes()[0];}function
quoteBinary($O){return"x".q(bin2hex($O));}function
engines(){$H=["table"];if(Connection::get()->isMinVersion("3.8.2")){if(Connection::get()->isMinVersion("3.37")){$H[]="STRICT";$H[]="STRICT, WITHOUT ROWID";}$H[]="WITHOUT ROWID";}return$H;}function
insertUpdate($P,array$bl,array$Fk){$Y=[];foreach($bl
as$al)$Y[]="(".implode(", ",$al).")";return
queries("REPLACE INTO ".table($P)." (".implode(", ",array_keys(reset($bl))).") VALUES\n".implode(",\n",$Y));}function
tableHelp($B,$Dg=false){if(preg_match('~^sqlite_(seq|stat.)~',$B,$_))return"fileformat2.html#$_[1]tab";if(preg_match('~^sqlite(_temp)?_(master|schema)$~',$B))return"schematab.html";return
null;}function
checkConstraints($P){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',$this->connection->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($P)),$A);return
array_combine($A[2],$A[2]);}function
getAllFields(){$Ia=[];foreach(tables_list()as$P=>$T){foreach(fields($P)as$l)$Ia[$P][]=$l;}return$Ia;}}function
create_driver(Connection$e){return
SqliteDriver::create($e,Admin::get());}function
idf_escape($t){return'"'.str_replace('"','""',$t).'"';}function
table($t){return
idf_escape($t);}function
connect($Fk=false,&$k=null){$e=$Fk?SqLiteConnection::create():SqLiteConnection::createSecondary();$E=Admin::get()->getCredentials()[2];if($E!=""){$G=Admin::get()->verifyDefaultPassword($E);if($G!==true){$k=$G;return
null;}}return$e;}function
get_databases($Qe){return[];}function
limit($F,$Z,$y,$Yi=0,$Zl=" "){return" $F$Z".($y?$Zl."LIMIT $y".($Yi?" OFFSET $Yi":""):"");}function
limit1($P,$F,$Z,$Zl="\n"){return(preg_match('~^INTO~',$F)||Connection::get()->getValue("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($F,$Z,1,0,$Zl):" $F WHERE rowid = (SELECT rowid FROM ".table($P).$Z.$Zl."LIMIT 1)");}function
db_collation($i,array$Tb){return
Connection::get()->getValue("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name LIKE 'sqlite_%'), name");}function
count_tables(array$h){return[];}function
db_status(){$e=Connection::get();$Qj=$e->getValue("PRAGMA page_size");$df=$e->getValue("PRAGMA freelist_count")*$Qj;return["Data_length"=>$e->getValue("PRAGMA page_count")*$Qj-$df,"Index_length"=>0,"Data_free"=>$df,];}function
table_status($B="",$ve=false){$H=[];$J=[];if(!$ve){if($B=="")Connection::get()->query("PRAGMA optimize = 0x10002");$J=get_key_vals("SELECT tbl, MAX(CAST(stat AS integer)) FROM sqlite_stat1".($B!=""?" WHERE tbl = ".q($B):"")." GROUP BY tbl");}foreach(get_rows("SELECT name AS Name, type AS Engine, sql, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($B!=""?"AND name = ".q($B):"ORDER BY (name LIKE 'sqlite_%'), name"))as$I){if($I["Engine"]=="table"){$Xm=preg_replace('~.*\)~s','',$I["sql"]);$I["Engine"]=implode(", ",array_filter([(preg_match('~\bSTRICT\b~i',$Xm)?"STRICT":""),(preg_match('~\bWITHOUT\s+ROWID\b~i',$Xm)?"WITHOUT ROWID":""),]))?:"table";}unset($I["sql"]);$I["Rows"]=isset($J[$I["Name"]])?$J[$I["Name"]]:0;$H[$I["Name"]]=$I;}if(!$ve){foreach(get_rows("SELECT * FROM sqlite_sequence".($B!=""?" WHERE name = ".q($B):""),null,"")as$I)$H[$I["name"]]["Auto_increment"]=$I["seq"];}return$H;}function
is_view(array$Q){return$Q["Engine"]=="view";}function
fk_support(array$Q){return!Connection::get()->getValue("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($P){$H=[];$Dm=Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($P));$Kk=["select"=>1,"where"=>1,"order"=>1];if(!preg_match('~^sqlite(_temp)?_(master|schema)$~',$P))$Kk+=["insert"=>1,"update"=>1];foreach(get_rows("PRAGMA table_".(Connection::get()->isMinVersion("3.31")?"x":"")."info(".table($P).")")as$I){$B=$I["name"];$T=strtolower($I["type"]);$j=$I["dflt_value"];$H[$B]=["field"=>$B,"type"=>(preg_match('~int~i',$T)?"integer":(preg_match('~char|clob|text~i',$T)?"text":(preg_match('~blob~i',$T)?"blob":(preg_match('~real|floa|doub~i',$T)?"real":(preg_match('~any~i',$T)?"any":"numeric"))))),"full_type"=>$T,"default"=>(preg_match("~^'(.*)'$~s",$j,$_)?str_replace("''","'",$_[1]):($j=="NULL"?null:$j)),"null"=>!$I["notnull"],"privileges"=>$Kk,"primary"=>$I["pk"],];if($I["pk"]&&preg_match('~\bAUTOINCREMENT\b~i',$Dm))$H[$B]["auto_increment"]=true;$Gf=isset($I["hidden"])?$I["hidden"]:0;$jf=($Gf==2?"VIRTUAL":($Gf==3?"STORED":""));$H[$B]["generated"]=$jf;if($jf){$Vk=implode("|",[preg_quote($B,'~'),'"'.preg_quote(str_replace('"','""',$B),'~').'"','`'.preg_quote(str_replace('`','``',$B),'~').'`','\['.preg_quote($B,'~').']',]);if(preg_match('~[(,]\s*(?:'.$Vk.')\s(?:[^,()]|\([^()]*\))*?\bAS\s*(\((?:[^()\'"]++|\'(?:[^\']|\'\')*\'|"(?:[^"]|"")*"|(?1))*\))~i',$Dm,$_))$H[$B]["default"]=substr($_[1],1,-1);}}$t='(("[^"]*+")+|[a-z0-9_]+)';preg_match_all('~'.$t.'\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$Dm,$A,PREG_SET_ORDER);foreach($A
as$_){$B=str_replace('""','"',preg_replace('~^"|"$~','',$_[1]));if($H[$B])$H[$B]["collation"]=trim($_[3],"'");}return$H;}function
indexes($P,$e=null){if(!is_object($e))$e=Connection::get();$H=[];$Dm=$e->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($P));if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$Dm,$_)){$H[""]=["type"=>"PRIMARY","columns"=>[],"lengths"=>[],"descs"=>[]];preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$_[1],$A,PREG_SET_ORDER);foreach($A
as$_){$H[""]["columns"][]=idf_unescape($_[2]).$_[4];$H[""]["descs"][]=(preg_match('~DESC~i',$_[5])?'1':null);}}if(!$H){foreach(fields($P)as$B=>$l){if($l["primary"])$H[""]=["type"=>"PRIMARY","columns"=>[$B],"lengths"=>[],"descs"=>[null]];}}$Im=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($P),$e);foreach(get_rows("PRAGMA index_list(".table($P).")",$e)as$I){$B=$I["name"];$u=["type"=>($I["unique"]?"UNIQUE":"INDEX")];$u["lengths"]=[];$u["descs"]=[];foreach(get_rows("PRAGMA index_info(".idf_escape($B).")",$e)as$Al){$u["columns"][]=$Al["name"];$u["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($B).' ON '.idf_escape($P),'~').' \((.*)\)$~i',$Im[$B],$kl)){preg_match_all('/("[^"]*+")+( DESC)?/',$kl[2],$A);foreach($A[2]as$w=>$W){if($W)$u["descs"][$w]='1';}}if(!$H[""]||$u["type"]!="UNIQUE"||$u["columns"]!=$H[""]["columns"]||$u["descs"]!=$H[""]["descs"]||!preg_match("~^sqlite_~",$B))$H[$B]=$u;}return$H;}function
foreign_keys($P){$H=[];foreach(get_rows("PRAGMA foreign_key_list(".table($P).")")as$I){$p=&$H[$I["id"]];if(!$p)$p=$I;$p["source"][]=$I["from"];$p["target"][]=$I["to"];}return$H;}function
backward_keys($P){return[];}function
view($B){return["select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($B)))];}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):[]);}function
information_schema($i,$Ll=""){return
false;}function
error(){return
h(Connection::get()->getError());}function
check_sqlite_name($B){if(stripos($B,"file:")===0){Connection::get()->setError(lang(134));return
false;}$qe="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($qe)\$~",$B)){Connection::get()->setError(lang(135,str_replace("|",", ",$qe)));return
false;}return
true;}function
create_database($i,$Sb){if(file_exists($i)){Connection::get()->setError(lang(136));return
false;}if(!check_sqlite_name($i))return
false;try{$z=SqLiteConnection::createSecondary();$z->open($i,"","");}catch(Exception$ee){Connection::get()->setError($ee->getMessage());return
false;}$z->query('PRAGMA encoding = "UTF-8"');$z->query('CREATE TABLE adminneo (i)');$z->query('DROP TABLE adminneo');return
true;}function
drop_databases(array$h){Connection::get()->close();foreach($h
as$i){if(!check_sqlite_name($i))return
false;if(!@unlink($i)){Connection::get()->setError(lang(136));return
false;}}return
true;}function
rename_database($B,$Sb){if(!check_sqlite_name($B))return
false;Connection::get()->close();Connection::get()->setError(lang(136));return@rename(DB,$B);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($P,$B,array$m,array$Se,$cc,$Td,$Sb,$ab,$dk){$_o=($P==""||$Se||$Td);foreach($m
as$l){if($l[0]!=""||!$l[1]||$l[2]){$_o=true;break;}}$Na=[];$Kj=[];foreach($m
as$l){if(!$l[1])continue;if($l[0]!="")$Kj[$l[0]]=$l[1][0];$Na[]=($_o?$l[1]:"ADD ".implode($l[1]));}if(!$_o){foreach($Na
as$W){if(!queries("ALTER TABLE ".table($P)." $W"))return
false;}if($P!=$B&&!queries("ALTER TABLE ".table($P)." RENAME TO ".table($B)))return
false;}elseif(!recreate_table($P,$B,$Na,$Kj,$Se,$ab,[],"","",$Td))return
false;if($ab){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $ab WHERE name = ".q($B));if(!Connection::get()->getAffectedRows())queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($B).", $ab)");queries("COMMIT");}return
true;}function
recreate_table($P,$B,array$m,array$Kj,array$Se,$ab="",array$v=[],$yd="",$_a="",$Td=""){if($P!=""){if(!$m){foreach(fields($P)as$w=>$l){if($v)$l["auto_increment"]=0;$m[]=process_field($l,$l);$Kj[$w]=idf_escape($w);}}$Gk=false;foreach($m
as$l){if($l[6])$Gk=true;}$_d=[];foreach($v
as$w=>$W){if($W[2]=="DROP"){$_d[$W[1]]=true;unset($v[$w]);}}foreach(indexes($P)as$Vg=>$u){$d=[];foreach($u["columns"]as$w=>$c){if(!isset($Kj[$c]))continue
2;$d[]=$Kj[$c].($u["descs"][$w]?" DESC":"");}if(!$_d[$Vg]){if($u["type"]!="PRIMARY"||!$Gk){$Vg=preg_replace('~^sqlite_~',"",$Vg);$v[]=[$u["type"],$Vg,$d];}}}foreach($v
as$w=>$W){if($W[0]=="PRIMARY"){unset($v[$w]);$Se[]="  PRIMARY KEY (".implode(", ",$W[2]).")";}}foreach(foreign_keys($P)as$Vg=>$p){foreach($p["source"]as$w=>$c){if(!$Kj[$c])continue
2;$p["source"][$w]=idf_unescape($Kj[$c]);}if(!isset($Se[" $Vg"]))$Se[]=" ".format_foreign_key($p);}queries("BEGIN");}$Ab=array();foreach($m
as$l){if(preg_match('~GENERATED~',$l[3]))unset($Kj[array_search($l[0],$Kj)]);$Ab[]="  ".implode($l);}$Ab=array_merge($Ab,array_filter($Se));foreach(Driver::get()->checkConstraints($P)as$Eb){if($Eb!=$yd)$Ab[]="  CHECK ($Eb)";}if($_a)$Ab[]="  CHECK ($_a)";$An=($P!=""&&$P==$B?"adminneo_$B":$B);if(!$Td&&$P!=""){$Q=table_status1($P);$Td=isset($Q["Engine"])?$Q["Engine"]:null;}if(!queries("CREATE TABLE ".table($An)." (\n".implode(",\n",$Ab)."\n)".($Td!="table"&&in_array($Td,Driver::get()->engines())?" $Td":"")))return
false;if($P!=""){if($Kj&&!queries("INSERT INTO ".table($An)." (".implode(", ",$Kj).") SELECT ".implode(", ",array_map('AdminNeo\idf_escape',array_keys($Kj)))." FROM ".table($P)))return
false;$do=[];foreach(triggers($P)as$bo=>$Ln){$ao=trigger($bo,$P);$do[]="CREATE TRIGGER ".idf_escape($bo)." ".implode(" ",$Ln)." ON ".table($B)."\n$ao[Statement]";}$ab=$ab?"":Connection::get()->getValue("SELECT seq FROM sqlite_sequence WHERE name = ".q($P));if(!queries("DROP TABLE ".table($P))||($P==$B&&!queries("ALTER TABLE ".table($An)." RENAME TO ".table($B)))||!alter_indexes($B,$v))return
false;if($ab)queries("UPDATE sqlite_sequence SET seq = $ab WHERE name = ".q($B));foreach($do
as$ao){if(!queries($ao))return
false;}queries("COMMIT");}return
true;}function
index_sql($P,$T,$B,$d){return"CREATE $T ".($T!="INDEX"?"INDEX ":"").idf_escape($B!=""?$B:uniqid($P."_"))." ON ".table($P)." $d";}function
alter_indexes($P,array$b){foreach($b
as$u){if($u[0]=="PRIMARY"||(preg_match('~^sqlite_~',$u[1])))return
recreate_table($P,$P,[],[],[],"",$b);}foreach(array_reverse($b)as$W){if(!queries($W[2]=="DROP"?"DROP INDEX ".idf_escape($W[1]):index_sql($P,$W[0],$W[1],"(".implode(", ",$W[2]).")")))return
false;}return
true;}function
truncate_tables(array$R){return
apply_queries("DELETE FROM",$R);}function
drop_views(array$Ro){return
apply_queries("DROP VIEW",$Ro);}function
drop_tables(array$R){return
apply_queries("DROP TABLE",$R);}function
move_tables(array$R,array$Ro,$zn){return
false;}function
trigger($B,$P){if($B=="")return["Statement"=>"BEGIN\n\t;\nEND"];$t='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$co=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$t\\s*(".implode("|",$co["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($t))?\\s+ON\\s*$t\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($B)),$_);$Xi=$_[3];return["Trigger"=>$B,"Timing"=>strtoupper($_[1]),"Event"=>strtoupper($_[2]).($Xi?" OF":""),"Of"=>idf_unescape($Xi),"Statement"=>$_[4],];}function
triggers($P){$H=[];$co=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($P))as$I){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$co["Timing"]).')\s*(.*?)\s+ON\b~i',$I["sql"],$_);$H[$I["name"]]=[$_[1],$_[2]];}return$H;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER","INSTEAD OF"],"Event"=>["INSERT","UPDATE","UPDATE OF","DELETE"],"Type"=>["FOR EACH ROW"],];}function
last_id($G){return
Connection::get()->getValue("SELECT LAST_INSERT_ROWID()");}function
explain(Connection$e,$F){return$e->query("EXPLAIN QUERY PLAN $F");}function
found_rows(array$Q,array$Z){return
null;}function
types(){return[];}function
create_sql($P,$ab,$Um){$H=Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($P));foreach(indexes($P)as$B=>$u){if($B==''||strpos($B,"sqlite_")===0)continue;$H
.=";\n\n".index_sql($P,$u['type'],$B,"(".implode(", ",array_map('AdminNeo\idf_escape',$u['columns'])).")");}return$H;}function
truncate_sql($P){return"DELETE FROM ".table($P);}function
trigger_sql($P){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($P)));}function
show_variables(){$H=[];foreach(get_rows("PRAGMA pragma_list")as$I){$B=$I["name"];if($B!="pragma_list"&&$B!="compile_options"){$H[$B]=[$B,''];foreach(get_rows("PRAGMA $B")as$Bl)$H[$B][1].=implode(", ",$Bl)."\n";}}return$H;}function
show_status(){$H=[];foreach(get_vals("PRAGMA compile_options")as$uj)$H[]=explode("=",$uj,2)+["",""];return$H;}function
convert_field(array$l){return
null;}function
unconvert_field(array$l,$H){return$H;}function
support($we){return
preg_match('~^(check|columns|database|drop_col|dump|indexes|descidx|move_col|sql|status|table|transaction_ddl|trigger|variables|view|view_trigger)$~',$we);}}$tk="adminneo-plugins";if(is_dir($tk)){foreach(glob("$tk/*.php")as$o)include_once$o;}function
get_compressed_translations($gh){switch($gh){case'_template':return'+X70:6L+^#NBNbej*=[nQG>NYqy"zxVRrd6t6.P(0/ivjtG8r
9H=34=<0WyvU}x"szaN]mbVAW,o]+9STB0s:ic+BlvZ;7
mK,-B_{MS<v%GdKQ@%Sh6><#g7=ME"bTzPp"0B[(T=!"F"4UK0KD
:n-PJD;E;BtF/|0@;WE-GCTTZ`g5-
1a;S38Ev*JT)&Qbe08o~Xccc`doW"dC247E-o-/)93#Wd1]3QgVhq*UY;uhI;W:.`*;upRPOn:_<:3IpU([:;*+miwkXO95~3jq)*]^=ip+~UUq59-JVx0UMD3Y?mg&68<<<dheO&6=lAeSY5
Sh0<a3k-Tw3!5_jHCIq1:6k6JpUom:Uko@kypcV:v5kYi1;tgWvZFiGyG0s$1c4{+Yv)K8@C*VgrQ{0J/bltrd.)g.vLV[5V;o%GYMe!)30h!{+!6tLF3U8]%!E`Nn=wx]19u"l[#oQ-%D%k;1Y5l`U+R&;A4MBNMQU_HQcCpF])m|v-N}wxa~i/YtY(Sb$rVBk/F#_OHmMD9Q56)4MlGJ,iz(ve=Q,?v-ofLwew$(`Cf(dN7AFwLsw")iUH.wu$l^8"bRhr*me$0?BLBM^~Uns0dem
X?rIoE9@Y!W%qr:l:tpQMEdiB=.ksO)iQ
dg-iyD4G=[DR0#Mt$&?[]tDOjllq&KOxO$&=Eu->Z31Sem<j6pjF>"Uo92CRo?;zOryZ%[A5CC^Fjw,xeGMQAfBpfC=~77yV`4]J::$D"q`[xWH2!C
U?-1tj|YerX&]Q9RP^P!5!uPfYG8FYp=d-AqvI:lN8kMRL[&sTMR%j7DX

Y55A;Ac^^f@"Q0nFd>9<SrpSDyGghnMnFhD>g>Gsg}gEq03-:x#hfjTyLwKS&J>+Z5$5Ssk(&3B-2GL0KrJt%!PZD93-k"FL4D?p!wa4%U%p?kOvO}l:AJS_x&_
_e:7Pr9,SnDN;.T@.c%fS)f7ixprBR]Se)e8F#YR!)1Afddn+ue>*DC0!))_JIxuZx*ZPT-]!;_RR?5lZp2rRZ.1!+EGHLPy0Wj!@3^/TC)DXiU.s/_tuP8~n-l=#Bh(T*/oh.#gTv;exbsLDrTP
_Q;Z`>3UVJKHo)$M6@@;h$C30BC.|l2.~TJu$KaZtk!B?0yx1W@XqXq)pfGi0ybx?6Y6y7{T3<Y.s?c>@bIxawrHT';case'ar':return')c0LMaMD9,{0lC%"+-$$ThQW`wVmPi>-D!9jlms-*O|(}Z*fg"zFq<U
;`TA4ep^PTK58f<
GnD3i]7%GQ+Q`B=]0L[?/z%j/L^aU^FkByuH*HLoW_U1ZoytTXr"gK:PT,puJP%+kUzO3fTeVyO1H_+[|!H
l&"OLx{TKMXygq_&*(k2=idytGBL3I[jG?y"GTqriANpj$*^o2ji.aU_(`_E"q}gcUg3htDb;@T9#<F@RYu,[yRi/yny3u`DzyY5}5$BNIQ2UtyW`?zg0=JjU4wq#0q:MAS.H;{*[;BU9mbyVGldW_36U
:OZ2Z[qvEMD(!`|B5H=c!&Vn>(NQu*vhMR,;ig~3$8e!/YH64_QoI9C#<N[iF"[/Pq:D-uWn~A!^("oE(Tsa=5fN!F:KlE+(|.ljAw8gT
!y+yk4}5g]w%<x=c}qJ4pgN+Bht4YsTdrwC99#M*Fg7b_qk4O_QN`8O!pJSqxiXj7Xe(f;k8WH8@T2T?=wSXI8Yw(`n3R&hoj-Jv>%_UIQrv!iYZkHgOu
,<@(__O]T^LOVjwN:o5:S8E!Tb>%_g#_#>%d|];>=_@EnISj{,+-tZ[3&M$
/YRM<ofo%2<#f$Lw/:M^EpeW1t
VB3HiyILIwNE#i[v+p>
dmg!$_@=bTbo72YBLRne0+.JF>&+JjIf_j.[OH.{NkA"/&8Hc{Z5WkjhQ+F%BC]OM"2gwGR.16t/rly7^`Lg"1H<6ZmV1j"R=>Qb"H$)I=RK!115g}WApTo_svVS=~a<fRGvPH.nDR0m4Y@],R9h^Dth")FO,GVjWMTX1_i
>$6<#W$#$k],fA:jk%R{8Ju75:N>aib*+9:;_=NM+aKK)/Y-$mizk"p^4?&-6t-NQW[S?SJ@"i-$;:eX+Q-yfn,fq?PY
vP>%eI8.)N^#}O<]sNnN{bz@&=ScZ4`wgH^L$PAp<wViT3w",$}lAI15VY<in2fwM
%<LO(eN)6"(7yqI<L]fVccyCXhd69%bUR0[k2,Sn@faBUMG:7sx)]W=q:)LXc3qD#ZZ_NFiuMlcl@bBIe
>9,t*TaA)tad(@=@YI,&[/jV):H62:1_Ug6c2&4%1_7fvU2s9^?nxv[LfI$l:hP8%_7G@)bqMxn+=soDhf,7oTJ=#A;g2_X)XSNTnWnyWE(4jhgu_@LWQFgI<]`wA,;/<2F<P;s82O56`f4_n5"Ng&E*S!BYh1ef=XR
a`NlPb(_d433D9R8BJ{LUmKU&=`wkTs
U%W)T8ER0B.a6@V,t4z#<`bbU[5Gt"xi,*ENM
tMfh<Nhf
SeHJSEn[R5UKEV_5.F2Cmb:u7S*"XdCfOPSxhZnl)O5%AV5Qirp-^R;.Qp;pa06=T`.+`8fTg`<vIIUrj}V.-VcQP<qFGqz(0A&Y)S#=lz/:dB$9v{
WNd;GhdP3FaXvVy/Ac<9eRWLriV+LR~wVW/tOTh?rB?UI/<p"
l%I`o]@/?yxsDi(lKmUFW-tlH,ahLcc%er)vD<QyOWsibgU`md<m<%s;4Z.`U_lmD1S,IuV&SUtwF9BL25wF$cs$AbrqmsAuw-4Fir7XYa!$T4O+ny,o>OaP_/MIpjaM4?>T|e&@INH2zEf4Wo4MTulZ=[GO5j]0;lxT%WDkl
q
pV@:Vk|`[T@.]Fkyr5=0OwyL6i&1`J^J?8,eiP5+2k[(MP/!_dxBm*2*1MMjZnK;69AcJ#XTE<5uQ8(mYRv,Kc}K/(_TElZp$h.h|W%i^^I65
3#iX84J.#)#/H6"5MHf+">D7%.Zqjx`D&;iW;<H:pBWB-qGq[q34bqo:Q9HhD@kmV"j
E#4&/oW"WL4g!RAc.`V[Ko?L>g~CC.}96#99VE}?@moOHRvQc.~T>V)`nkH&2u(9|({/)Mr%8:[<yrp?ELY=.)9L59B-XN)ljm^n&$iu=WKQSx@pUMBu3ff&,acF6B~@D.f;i9%rRMfJsd]#3;.,_)GqRiRIP_$8{-HpxM4Hk:J0&r,k[BFLFgx3y=Y;Zsz:[:vZ@szCiKfW]xPg
yNOULw*|-~KkMdCJVQWs;O,]W!maL~_J"4o(S$d&MB6SY_d&j~Te@y3,.~ppk8ExdK#=AEVdVEdb!6Pyq"TM7gal>;Z,OVZrY0Puub7#9zQ5Da,A&8X.474R5<8,7Js-;;5^[d9sXGUW8>u{33*.sQ5sRi"An^>k#/5,?x#abaFE8C!7Ro9#7Rcm=lgA>*jV0xL,LpD7tNVyIXW?qS0XSc%h
=]RFaV97nrCq=@al{4x_f3BHm_b#-I/hakZN@[U`jO&G}CNTef{"(SPVilq4WY[qE%]0$e:Pt.fBH!Baz-Yo,dOgt8>_UTcID,SQ/QI:TP?Wpa07QvQy!RN5c<.7_E9y:@I2;LmlnwbVX-gIQF]D;;!pwA"%#;Qkn>h34llhfhrLMY%"iyl0{cXR]ATCKVM*Qd!6Fm`phaPwq!O7mHvhFvf?4HNJK[7.0`$T9hW!ICo+<pb/7xoC$E@jTcF9[U|NGG.9#U1k1MA=ZFFb*B)
n@)!i`$2vX6NOA9ioB5Uy${&B
.+H$e[vJx,6^-jRhO?]Y7i2w$D!D?fx&Eb{:#&Xl#S9o7l/!["
8|SOAqErQvh{IY%Bl~+oGSw%>%1fx$`vWzB`so9Dj*D6kF6vfnvUc)M=**b3-,$9DeL5]
PU_pTX>Zmg86C,LA%B7`/sBjI}LG/rq{ny=i])19:I!mWNDTwe1kyZ7$2x4Nqa*]ol[sa3q~aO^3k*`d)v5lmR]-d}q8c@h~$9jy"C@T;DwD)o+$P43!NrU02FOC4XN%,?hRAXnZfk]kD}ngRJ%$i.Xc1,Ec=N,Ov
@Wm#.%W@loCB_AC>RuEbj|B[xRy9vzh2v>rqP?R2c&9r
kRHjcQ]F2w>0!M)`][/]=al_O)N;TJOovAGoUAH?SNhNW(&X`1.#nHE=]P6c9]f:qgoPT&8>fHrEkq,e3BfOkMi+iLq1VLKX|f<b
q
Op#g*o,,nx1|!za[9B]F,1U)axtATQl!`yC-In<rVlJRski+F5@v+Tp]^_J,&8p`-9?dMcr@",&w-O?XUl69R,q{gG_X-T;,5x7/c?agLAOos74O+A.S@8E5_MBBnc,V;(EK@+6TND;HXiH-+Xht0NJx^|*WHdqXp7(T6;xJhG!KIaBuT|cKuRl^MM4*=!WEj~7/03pk*`iv9h"VJT)j].2yd)=}*w
F,`o8K[/H:rYhGoYgu9q~Ft`@3Od_j#KEN}a$;A!c5m;ENt
S5ZT*,ASR+g9EP6rNA#LlHiM5.HhVClXi&c7A.[-MG9-fw(Awj_!"x=OP8pTit6kZJWN2or2"k!^n**yE`TZLH_?_wrBH.5GHv,!yQ^]]xhp}ZY[W-hP7ZyM%HDd~[c%O0"QENpDfY#:})qT4aZ5L)-pA2_jX!kWu>_P:xSZ`g.JUhf!.T}mlIpu,nw[NF[:u3Sr)Q>LV8*O^+Ycd>YXUxFH@gG*+14aXsMRRK4^"fne33{[qRo-7[K4-?7AS.0:U:!c1:Y,[S!VEmOixV[Y:WCG>jcdW.y`?>{M:.R=KSA?@#02C"1MwHB+FY+asW.n?UQFG)yxYj~QM$RUi,L0s[fZipEJe<GFEucZEVUl&Wn
9C}m._;69=r8S7/G|sR.SpyGm<bUlVohzWHHJ61X(a[j"bL?N)VD{%
041NH}p=Z4K"@,]fMF[/>_TTvhnG
|kP3wS*wu1%(d;y7:y}1k]F7S=yf2V*Fwh{&!5w
0&|6w7&_h2!<[_3T>XbrKS?0m",Kp9O2U<C*ReQ5u#[kz:w^<,c)/m6FI1POojdUpb{C:%jCb1e"~74?FK3EHTkqY<IfA/b^&k(J;0DvXxD;k3SEHR!)CFTmtu9*Tv2FIo3E1s
5JEAi;Hc$wRg0gCvRes~a=k+3b.L.u(At~wK]|@W[2+&%=T*W5n}jBZW.c&H0#<|mn21f^K#xxqp=1AB"KqpG?WBro-]j^aj:N+e(l7dH<XL?X<hQ%yO
|7%sv=EJ,1H0S%(Dp5^]@j>ktjb^sLUsUc>Opvln52XaKE[P%R#nsl)C8186Ap=<W_(j*wfauj6*nGQ92GfG3(F=E#kz"P91`8[
H7j8~Q{0{QA[0j)*Ss&A#ikYSaeZ9%2rI.:9|@~xw&~HqkzH.s=Y->}Oi1Bdd<{M4?,_rUIBIahQ]*EvdH7%Q_&<nnTIUg$#~P(X|ZojPvMPIUD#C!&w"b`k-Vun!HOi,bpT^AK&`#a]
UvZ
voPeoL<KaYe--rwR-qt:j2H)h{-Lh&nf`Y[{6e!k.qo>YVnY]/)DfHfLI)V#fGXEciJA29yTXe&V9TGwmY&+,um7_OU|476Slsw<8xW=02AV:W+Sr51uM,T8UHEx1)3?Bk0vMPdYP6MmI26rH5IyCzXD0D:XG7q"KTZ9!8gtD[n@5YEkX$r.P5x"RMg!?X:Wq^1*A2?wD:?Nv?O:52ZOp
oX!In<OuX(cfK}YBq[TDbsVmCUFh1#[g,rdd%L7FGe/4ICh3x"oM74lsy
wOy_:0]vDm:nV_H+!Ld"@5^DwAp^2G,&XB"&4;;CLtW[[CM[5x29`$mnTahaHnMfCHS@!,KB5.9Beqvrh3H1eG/3KziImHOSlB&-G2;>
1ETNe>NO^qftT(t';case'bg':return'"evF<:wp=+W)!PGGw&|PKqpc#!acuk{yG;}pYV^0-;xo[1OZ,fpBXe]5.*j#.dpDH0rRmT]L[1,b%=S>1bt$JYN
uDthIatBnctP/w1((T+,"w`^G`sM0AMal.AT7kS6-SOxZal[h
^n$H;=-GucP0r:8skm~o^J)2Fa.`WDFM>c(.z%*K:cJ@?IZ23n}GDlt]TJ/]7K([YOwN`,eYVK>&%_E&1*i&bH/Gv-s0
wEmQ1ZTZmN$=w0K/6f3!d<)MwKdX,XSAcw,)%~3YO:0KVa/pe:u1:K>slCMLT(_D>79hLlx=x*GPkEA})^v2w|TEKQ%:XBn;hV
;MJ/4!8i(R0Cdm]Fe1ZRpr5a,e3EpEme8-kKrwhT4&sPiId2A$^JvqwilV,>WQfnWLl$CM>RqUuXZSx4/),*`QzPx[#&g;.2&8DX#mAE;*P,D`$wR2"iwgulQVK!Y#~+(F&Hmx|D7$3(
dWo&@dy9Nv9D=hMYmC]%"}goL7e+[G+"WGhC]bhYx;dzn+tIJ1L;=^=zNS5Bwaaey;CAyOl"G5Kaq9.E.qF}2>i<v(fCauMjjHl0K%:m"8Vic|#/YyAB=4IMYE:3dV%`BZJO`f?RpZHlD~F1:fT(QB!#HFK?M#nb0E_`S0dnsH!4W2][
S7j6rQbmQd5)(SiPv:-^Y#9-M[^+:@l]SE|YS$A*gj0V0lq+Qb2+Fn`p!>53
CEf)ncQUu?;^)+B;itoRN_Fb=vheeqgej{?r9sZ-7(Qe,1ST"8j|&j!#)o_1bJ)*;O
Gi1llQVqh2JZE4ttHGe_l"Y]LAPpr]nPyz%Vx4n(
mIbH[C*IM>/QD=:zn*:1oz!2Sh9>^q6^s%l:V1kG8Csr/UCYk#t#T8b[N1sr*Addo>t7jlZp(AeOnFW;w1V"7q5-GVP.haHe(.SmJ=:2QJdR59,%#|wqC.SQj;V)meDCJ?)f;B@zfD<TTJ^L_~5a_fF5j{w8]Q^.,^`nQ"F.o[x=^=eM6NTILE9h3</nK"Vr1/FbYzQYe,oY#SU5.{b,A4UJ8}BM*JRR&Z:0nU`3`+EPUK:PBzl*mO/m_6?%M=j<$op7azuh%%Dl,5+4Ul$z6Kaa55qZdy(v)w4m
e=@uLq2?9,-lUJ|Jdin!hd&^Vw/@NP1:F?G%D]JYM69p<Xc;&wyY<R{AL1lbhliU]S,vz`SSq>L(F_c004lHcGEB}r/asuuG{p]$~qgJ*(U+]d9GXpBi;n82`[FVT5(]
<JOsV+&W42OV$2us)~U%#QnZ%2ncYhB0Vd<aN1H2`f
h.SZ-28f^7~Cn=$UFqMX!>Xro&V2KRDKf@)f`W2!?86jg2c)Lp)[1YyO#%Z><N(0C^lXqvNAF/fZ^?N35v2`FrS/%cKU6exEx;Ww*mjd:IDp6
y31U9.cc_S/x
08n?4TNErCGL0)2GB3ORX.^fKAt#:xEv.
G>PTeVL
V6m1fqafu1o6:h$?O{hYR0<oOd-24ytblIDW44LHqrL1Er+/O9LX3Q8#XVh~1iHxZpH~r4X>i{Nc5-mV3EkhfYh)^7KoI0Od2v5MXQ8HfQa^wEp0L%TW7TFH_4]lKwWMo7l#s9_^3.`JohR^^ouwP|wb#`6UlKN7L/ksL1m`b(j,#zO?Jfe{I`LOQ(f6AhLqwG"sMd,x`)4Nk!f!QOxAAGT?fmQ2+Vbue30-KlCxMf4VTdU$GF<MXYLS/<A9FZ<k9e`R4nTzY?31AQBF`SZ!NxGj1dE>,Gv`F/CBT_<^=NwG&<EtwPSd.w`kyO!%Ci1bB^QEc[BM)6;#E8T6]2O_EsF^q9VYY[TFAnvRD3JK4L*OxJM;S-OX.,T8-p=R!?3UN7vGA<]wo
q>iC.=^?5UA-Qa3a=5BD,<Rw3tFen[(wnPm;vb5!/xt^,_D^[2(
YDYd3mkC_;`JB8&]JOp7e-4IZxRK.Uag0$m+g$c+d4Lp2up9>-WvFed=O$d4ig[iE=ugmN&8Qw[dwRb-Ci>XB)3/O+k]9OH0:a;y./JS/}2h-k74co9nUb7^qZ.T:/(g9|qB"QJDiSyd?XK<Kg`1+/1C:HEtL{vM4OJ_"sn5mr(~H:*qw/)},0v(K=kW+0H<yD4L.Z)""J<_[pCgA3P]gAR^&{,SBhN$NC/|ls&M
~;$CX=
!~E?>4bBZKPuRf4v:N$;_T[orjMH,]&zEWBpIzm,-Gb:PU4|ro/^<M?NCZj4Askd
saLTO]?64<@i*<<k^,XkqB3BY^Vv7XIK}i{F4)#^F-rS7CM;Hn
J3,"]^Hm_tx@-Gv,/1qboT&g<6/d-ctk>ADds?:~M@Y{GyXPL%/[*g"/_vNvJM<wt~(z/MwY+i9j(4ty"4%@7="L+!OwAC0GLTVhF~GH43PJF#X=G21@#uns
Rch&120Er6Tt@qC(*nF3pV*i;Y,xuvSt[a4!Hu(ervA=S0&=@VAq<+
=zg0R-yuTGK-@6Wo%gUc5dBap.Q~3(u5-`#5L&Ew;D
wx#q*K-?KYi$$CaA;%X$iHiw"V&8lRAswmeaSH
tLnygg=Ol}n_Mg2r]U=.@)Da(pdk6ljT5Kmil&hHAa/ZRJpPmN>3"AMgUC
^MKsL[dFsP]:WHD1ITj@j,~GFqt>|]ulsl`
CKkXhRtDroqaLVYps;Ag@,
!5?3KjR_$#1&A?#QY8t8UYGr!r98oS%dk[y19*>s!~jLm`CpkMP!mZ3FVCP&^<Vu).&lD^IO({7(I#n
?
6WDN:6`DTFZ~Oi!6X29rSfXwamYv*ej4URf1*.mk8]muPL:6Hm@SU/n_^p^`G$Z=$6BY8=4u(-+KZ6X>Cd.zpW+]mY>,ep<WeDJFv;?"ont*LEf!gELcDT<i4=4p#aP31Qnf<:cgi%eZy3NXH~)BiwKLaW208#E0%"3l`SYJ=1+e)<&)x03[rd#&Cwn
oV1k`_qu+k(`a"k.Q|JJe7v.1TC)"1s]&__DLmuI_E?
KQ$"5F_&&Ato-3&~f4SI2g"LSsSAwjDOiJ!H^WRzfX`9$a#jOONr#sr4gW<%b(X5is&/"rKoXREWD2*yXc@b68Y,0BI_GK<O&g,hD5
%cD,(t=RMC^+-M3>.x*Cloe9yb%(12pN^HX8xyJ2=ocGK3;v<;D;9$Cisg~=rq~-_h`M]#3
.E3ZsyA/[C9G$U~9l0%+H%Cu}I83rsm!bWYZcmolk$=^OKQKwr,5ETG#V$WtP.w!7i6@:9a_Aot4+^soR,8ps$-%H<c!2>YU*Unfo0JCJG3e&:|HWS)B(d7fZ,o"0R?jknbh.dYF}5xgfQvDr4=0>s(c<qeW/$z7y,,CKo
&#D!!T^M_y/LM-<Y&Hd-T1f$2#<$kmy4*4fn/K5K+SZ@3v73uI#6KlOm7N"&ANGiwt7&hB=d(qRHE-/KiBW$%*e/e&P
3gyV_?3GsjJr#IIU#jkDjb[`vCO_W>N,hE&V3#;G90.(&PfW.4)j.q=^)s^_Ed,c6Uix[CU[]?3wyc6z@iBva.`lfy94HL/fl*B*2Jke?BqiL}o<Y;VVm4NWV(H@JilJ?bcs_nulO;Go<dQWHuZsG}.7X{bOeWu`Z9E(iEZ#IXENc
)iN,FnEq7t-:D:"eyP"M[YgyQJZC(kQV6Fc$bLsd0e+8i/L;Y}D1v1s#?H.@5w0Bcf,)lDItu[SW:ZQx([6%1X*[dd*0:=Xkc,0|
goY>p?*@0+^/-xSpm@&+fA|"qK,-&Kq-eiP`of6VwOBXwu]@]+dJW-2/0YF$vC+?Q:+5rU?&r.;wLQ%M.pS/bo,Y>y|={rSa^v%4+Zxu+eN:X&FZS,P=aKL#3q^-,U"ed@m7=]I`IgNYL<~5+3qv-@g1q3X&[K0tnk62vClFm;GL>
$=j<}rfH#k:;7Q-8p:{?=(mp4y.o#@6b)2I8q[+Etl|Gb5bkz="X,p-GA:iM@ny+^]NGQo:G-?pIdo8%+S"9iyIbtbnU%5S/$$tJn5|XfjU2`$vH/$Vg)-g/o`Bj2C)(*b"y/[@G1T@Om#4]F3[#-S!Tz`aI~d<L9NcG0K4Bsr}w^FR/8*^-e5Hy=]T_i4bgimoUiLjuCpJru">95HFIvTX6pRYTj6=A7Hsg7/:1XB088VqPUlkuyG}Q5u5hyg<)9@tEYeuL!l}6V@`r8(:C)rD"J](UUDTk@y$@/ubB+c^n5*?fv1c2`SOry(eUWW_pDI0o
?VdrnjI}n"rO[zXy?<8=9v*K)_VC]KF0)8?riii5E(k)]Jhw]|H~b>,X.?k9FcI:jys>,XF`
luOJ=Zp7tT8x>Ra[YnQq)8wY]Xf6|lT,K#g(h*-4R`FAt%{s1D#<=Q&>qv;@+^*Zor+6OW(9`Bui_)}${VN@g1a5o.w!mh-NKJQW4ZIFH>*G-!AOFvjC:6M=,!KE)(~2LT:W=4ntV;svlhL-~A5?,k`&3KYNAHUq]T^50wl7%pU:KayEJ!(XUb@CFmHeh+XGK!%kOn_^eWIwk77)dSFKkXwYPNEB8S28,-jSROM4M!,K|WAyXkXFY,V]s[nDo39v9xl`Q?c!yJSe~^aBE=@g}pVaF1ARBot+Dhx!Z+/D$^dMOZU0;O^Nf"V-fj~D)t2Nl(p?ZJ{g^BbSLlt0H!EO>v.g/t__j2]j|Lmt>tzbEKy1*MWxP$yZy/>liU,8|;Oy56tR4aRUX6jcrR-Z-iNlEZ.QX){40=/:uk_Zx"-"Xl_*GPFyt(wS9xW>z&)8MP-"5_8<LXk7RY+NN5]xHH#>vM[5},4itlOsgqo%z*a
e%e<JmWqVqP-:Zit8[j@$DHeffcBw%P]v_0[w_.$Fq)^49qnnbeN-)Ts}[V)BK~sa.I(:W*>+r+rmVh_wGkeU[>^5VqWOq3iBZ31N4/^,u,M!9jq03(pm7|#Q&^u
e9cQ_;-rO{
g25GPyuSG:5J+L8RU$+N{"5<x[]am"|qA3.P$*}um%CY}o@7e3,Id,1;RSR!YfAvUrR[fc#sz3Z/wt^MtlQRg
(QGwVEnM*Stec>r6M""Hl%P5~:22G:?+t^=apWF_-dai$.b0ObR2QI2@?kXvF/N4Qo<N3Lxr[o`s
[uY>D-p/."M;]<G-+l1gE*i*&*=Ng1Dsj&MzFHxV(|PIL9N6j^jH=RrV)#!}*qHSt_';case'bn':return'&hWVkbPDW)F!R85.F*6&Zp>2AF:[8SaJ%mu:)-;OK$:e%e9,qd[%`@G*d`L9`rAQgi_3O"BKock>NJWB-WJ]{p0SfJQ[Px;K!L*qFz!0kh^HEVlfC2@y
r=Y%gFHBy^X27N_hLVvJA;c@b3HH>oyUbAGdA,ytcsN"
%xLM=X>iQ)uq_Dx`zMm
>VtVm_.G,$Lx](&[DOZS>=G,x<@V<$!3KSRbDHP7^RO$uN$G|a5JXRf1>!0l7H<?yfTV4?ef0`dC%[NPeocqKoRS
j~MgCoa?Phyb,~l+t.-c@Cr_qTa_.KP2t?$g!G.T
-v`FpP+PcZHT.LZ=X:/tnPq&6faN-V?DN^L)Q+<c|L*s1-QSW-&tUF>rGPV2u1ZVEGdGV_~iYNm5VS+8<&+lfQ>ql$}I^az6^Af1o$^7^+U=}OUba;XxYsV0X<[EyO?oIF&_ES^kXP/kkhFI34ruI?|&1U1![a/b>%EGBlr^d_lj}p[4x,,Qb/S!Dd#A%VDV[<us~i_EYMW]nrzf28}

6@6FBU6x;U3hFsCX9rL
#3&io`%sFD--A:JU"z=8*HKq@pL+-F`R6`m@y7GQ.jrv3iA}*:XF:a=qxlVNXz2}a?36n^8VmCT4iEp;mb?eN@_#Z|(#f.P*<M&^(oK+$?^E"j#XBA8v$T+`,!g
Whm+)XC&2z1*O|[ShSIhNL
0wbN@W$"7V:,
e`C>_jJ-J~w+Es>>6uK^MdeR2tHGp2fsXcu"Yq3MC!h&5qKEFN5IMsc3RB2"V~WxuY2}NS%nW&$00">4yM3i?qedW|6)#zw<$A+a52_xU)^SXt+-)w*ld_%l4#(X+I_Sd{9L/0QzTY6H9Y)>K>Z/hSZg:8$Ti#@8CT+Z@X$R!vTsZ6g7p(&M_r1B_PJ"Qx(J4_qBy4EwI_unY<KZwpC"[?bSGX]X;;K}j?J-
.Ifp#-oT9X*j^,t7`z!n5YU*Ek>1(-ZA/mg${[;s|`bJ0p+d6V^dm-n`wBS-{u]u7q]f`BPQA6J5+!g7T=cP@#&eJX=#qX^j*6F?5QDC[LS$$P|p1UO>jkrcF7-`:._."?m,U]!4!IfF|UEWT,6F)AJ!;A%x.LXH$1m6-mV!8[ey.1]EzRAOHg)&L/LO+(9Z_]8=aj1c&*THH75!lRQVU1T_;8!10."?gp)&:!,?OtclbiybqtqF+..e)/:E^Ua"91tGb_p/IRD9|<bC*g!W.!->#c;"Q2gyP^xp}akx%WN3ma(_LN=*EC{OpI)AE-h5.t&Iv$5"IQ@rS=t,1-9_w"DNL%;V}@8N?QXAdcT;~mrx
WxLI3>oyAu/k]8r^GCwayZdb_0dc<vE`gGe}v5K
E_(e^YImg9nU:M*tttxb["JJdx&;nD2HLZPo,^P]V>Ht-$VgyGgkYs9oX/xN%cU4YZ@epl!*He2t!{.15!u0>%jv%02ys"b)*B]9E6*cI]n|6*+9)09jqA^"RQTF?vO$a,j8[0^&EO5$C.U+mg6^4Pi~sI?;@O.uWD[f(9?j
s0<xh4dKE^J@3@vT#+"AoOQsB&rVU!>PHNT3xx6S"`L2BFBliCn>!)1oLJ4XiM#@.Ffa5lMMc_Vike@ZsjqTK^xa9[
n@
mEdA1^}g-M!*PlrF>yz63&S$PT?%jjT^f1rR`[N-rfKSuUB]HyCI?O<g):(<M$g-g$~<<@[rGTw1T,E]=^,)-<
9:q_.TRux]``Aae}5yY
"WYUQ9CNEI3/7?ZHO2!}o0T~:%p&osjO%EDIL~N*&OsWOBfj>sHVArJ.NCIP`>+cQ]C9+I>E%h(AExTB<@XV/d=ABI<b^kHmML]-A[yJsvSIe`ewaW(.VA/sf
pn8#H`1Zo5b@B]3HHMkdh#1V$y%b@3)Ej2&sHZtz6<d3qr,&f)vk^C->vw4=_rGRkYBY=*J5HhaO:rSUp(&q&|vjFC`^COSe5`J~Jpmo
@
_(.HRQyGyg5s<KFRXFm.]te24=t<CNR5#6<IAy-TxwOO_=1KU$nAUXtM$W&uQ$t!K52VieHkxlYWb8VC4I)=!E^VT"JwVj]Ob>y"o8xT-tC*j_q,cZ|jc3CdP5g]5DLwq9X4{UzorW@!D"#c|qbr|iL&B@P#&i#57@@Mhn]3}#f3u!wnX;BN0q_5q*a>:jfmSmf@Y%Qfx2CDu44*Ran;XTOS),tcTxbHn08BSB408jh%3OJ4#@h$%sR#0iQ-Y9p,wGd(,nJ;Q]x1gV1r2S=]5q;G=8>Qt2UYuZ^1P*11<%wUT^zZW/k])G!>Fe7-&%$e|lvdgYV^aFGp1I|W?9+bz<5WQ8Y(~:4QU
7c#.@pk$tX&2Ih3A<AcSf?1YrCd/W9Mi~b%in6zKy932%=m!{AOW3^0`0L
tCDX1bmL0rGz&*`HaR3?oifOhbEs9`D<_t;7k#8%C,OS&sGSN6&Oalt"vX%atLPYSj.~yU!Qi"."(w(Gm?;_tpU5>PJf6}+#D%JIT.$r;^#O"&d}sb@nECJHg$6t.vF!aB@]oT$bcD?zchZ(J`]}
QXKDvTcLj-IKt>;<
C.O/IXm*R,a!X*<iw^/tEc,4u$1)P49~VzQWhJT.y<LdJ.M>^^/^+E-qLp2Wor*R9ranRs..jVYvv5/1;"ypnfGm`V7e%_?uGFyn*0E7<<N97R7#/G@}xdj_^G#].w:bHgRnU=+rj7C}ca(NHUnU$Kl7)O7,(|XYgrFvA$%2dC7k(G20d`&]IY2_se[jgc^v1r=@`L]RF`!QEA
<U{
d8$.d%"R^er!"ql6%fJV1L[e868F&9uD>S?m3g<9.^$:2@/!!8F:gm<>n+u"O;T9U?REh2js~]4#l!Q35ShO$/*l7
0x
Fn*~TC@p#Znsp$eOQ}K)w]Yx"-W(l/VWsz
]AaFxh_
%MWe_;Vpo]x^-*M#P+Wdb8[@#GCpX;T6WmE<Twd7x;!&5tLAOR4Em4atOkE>[&&[d#8]VS(Mt7!qy/@OO&v9k,Bz!v"4RqX_c+Ile48HC1BWGp9s7!
clG+NF2|?hNtP^[R>sNpuy$,w~5-.p!"g%v-&y5(T0QiTOm^LjA!<Dmv3Qh)Th#3F#aK[yUvi}
]?)6ZhQS8h_IIBE4e#=r4Z]FJ`.9C"2OgY>]=?
qdIu?#pc^kdal0$h(
eyN5iZ@
%E:La,hDYF[vpYe$0pa)<"<X4:H%l[;Z(&,~@KMc9QeiR(a@xA1[]!lrI0)zm=:]TIT5#jmigGIl4[;wTR9ZdeF7quBcjiy!=2dhA&;%ZLXoZ_osWR6|g<Qbsd,l#x].cRns.aUID.CY5bQO7x?..y*eW%-J<&(U6>9Ji++/"R7mX@;6/p[jZOhK>L#WA+M.UAp($aY3/q8J*ZnSLd>>6{_Lu.D]b
,
"@";-1/1b8D;TAWHqMoV
f.uY%1d*_1S&O0KMKI]J$=-/;^H$6QBU]nEL83(1Mv.g+EPONK^T+ShY0LlT-xwX@=C!$V/T
OBF5(2^51!JOPGB@j(;&_#ZE)m:veFSW6d9s:Kbw`4gvWd5(M65]?=T4qwI2>~xj>>kHeV)dj?]kw6m8u7%,B{rw)q&gc**Z-*Arf91.f4a6)v^YVPluGL$IP`r
:|"/kb)jR/]+6~5+f=@lQGgpe=Ejyqc,$cE7J>$z<,KGZz3JywT(Wrux[aH8R>0Tox2!V$PfJuVM<R0+B-aMaotlA3B3ryON^DW5yc-jRW3L.cM6vM)US=Co0-,**k(,opakhrRNQQ1a&S)1@F6v*P*CU!o-CjKaYO&Q,mxL1x`.qpVsv=9tv8.]0gw*($@pJpngK3ZT;oHxabOR8u,f2lt&=L;S"K2jfki=Hv#y!4^WHZYdF&!e^7M
jqEsF%5TPx"TPnG
N#SsAV1Cn,,H8~fPn;=[j``o<LFa?2C%yHMel"JmLDB@T}ANCQ$-97(gh]fH"6$a"2+_bG^m4y2UMWQeKi*9@xtq9+<.Uc+W:9*NkSU|4w%xqI;}="J;26q*q9CfEMM,TF*a@V?Ei;c>kX!&cP0mf/3g*}ZbD*P~EFyQgP+iAbrpypODG,)1s24-F+0|kfm`!Zm7,3V6YBO|2}]qP_r9A{:d8Y?Zl[/blj#aDSo&Edrxra2r.t$sxLW$1LEBSU7S:=gNe9y1+>,-R?;T/ak3^SEb<K&99KAShOH@NGD4g+)3BUhq*9D{Q.5[UcZ
4igl:aWQqwW1Z&b3U/%-TW[B?fU-tkO;u2mFyt1!I3Bx1OY`Q{C/yz
e0xld^cK^I*?qn/sSevtCJ(gc>LhTM6qJjC&
@*@c+%omabEXu|;b4dhN0}tF1L,Y7pa<!`i!4BHmRr*:[9(ty=0bbOWKylbb2WGdw"y?f.ij?PMk_%O#/-MM,j1k$5e#3Y8Q4;:x1yaakr)VLEBYVRdfn]5{IKu}mj`mYI0]9M9K%pwK6~xE9[/lM;>QpY:d:lWuA{0aAa*=+j.U^GheN+/S1twW_^F+=3q|8?&<JGWt<Up:k$Y0`hxWLx%[POG4iCo?%.WZu(-FjNV#wRWH/
;Zn7M1&1vyg1/DplU%n!cCnj
Z
@HO2b+g->"Es545ltGMgGxr"s=82TX0MQ,ibJ/Ko,!q2x%AdsKm)7!#cjU7U1y),)S0e9N{9tA?s7;w;]K%FjrEfT-W+gC.U)Jb!]-|@,I@XBVLIoc7N9-2(x#~%h&7YvyCulQ4+ivh/sKW"|w<`AT!A&cZ=L!/I#ET:O9=`ahf5Ef*KUSUt7,xr9WSNw>Wo#xaFY&VFZX(vPwZ,k:my)RxCz6,I/`iCP]%lz=R5TC01stHKa-}b2j0s1h}/{6bz&iWtcNkUOqga{UmQ%t/Y;^AVud@EBpM**@Z=C1"oAS,oh<{LU.|>(nF)l=2HbUI7e>{tN_hQoum8kMBLt^J1FSUm:&1!c,CAi7)=dcB(le{m:Gqr7yOyP^t!_Zs)MA~PcRY$WdX>"`dje-A[UFmlp%P?[*)GP&Tg;,mvc2Z(R)v40(3Ln:!TnU1jFf$P7l#`LOO9ZC[3!:qKaZX!9?G,%6H8uC0Zta1]%<"h?1
N+7!YDy)+FwH`wBrPnB=!9f]#pjrp0V*tI#2qcD&=qwWy3RfAEP0mM[Ja~G0cELre%
BM86][ZR]s2>rx[a}4q@k"+h)pqIcw*?1<a:]"N({Snj;Kt<<7nApt]vCN04(*LUm#ODXw]o)!;?NK`m!o)A|XP
:tw@ejy1J"M40^3IC>Q5bWqdfAGIVmIJ#&pgC[dp8i}B@>cf:wcu+O6:ugUw3nxd?94y;(Tw8aqm%]TZaNd5>>-f!*#`FU&E2V=%%=4L*[r+~d!N6';case'bs':return'.ZuKs;zp-*60q(f<E]W8!xa;5
jptRx`F.[YblDn><^$L$(_9th%^6=-kLXOF$yl-cOyrsr#.Nh>?lnxlrJ3wt+jyc
n>=OvkXSaVW-`x@A.(:
H&V>2{/+x1pDx+/CHK[9;W@@tv``=wY?0;JL0/-kHRn,=%IP2),xTqrxE1BO@0X(64$y]qTDi5+4s~^_)D1>LkjV[.w(m=(`i+,?Exg<P2HIlW<}ezURvD<k,)Jvli;y.Y_,`v>9w1;qsXBQoymKR)Q4.,xQOW_
2_8Y[0Eyf#5V.)D,8UUBE]S}KzxyN~1)Vfvt^w@BM`pu;g"UGK)$=IX/<4/LBXd.BPY<2b%7#tAWLR:D6;J20K^cXl&A=RwEFIAM1B)1[2jrx0XD4!HEFy><jjn8na"
i]ybjW&zrXgU(isGO60xQJw2V5n0*-[,+84w``f2x"j,;KEtdp8h_g%f0Y)xIr!c)*my`v<.r~U[l$4$xwBYqN`1&(GtS|%t/2-B#y(7y219i|n6A7Lb=x005i;|8f78^@8o*EY#;s
Vtt%<6}/S6nk~S/f-RhItAh+Z7igfE6QkbcZvslvj*g<gV6I4yZZ"/LKl=L;%<>W]I)uD!;bXk=FMQRN;-ze@)0kj->ff1Jo:sZ+K"|d*T(Rus<w>vS1i4n>I]|tbf?y`POS^biEpr12Etya;//u-sQ9:+K&<oxIeF8="-d)L?r.6Wq:=^f:N?8SKA3C.g,;!^k(2b(U&wXj#[M]8]z),?Waad_F^%.:xHuu?(~C9E/&oBSA^Z_()9[C<m|.hkV+Ps|#Z$mw}Y.Q.cXdUCdH~E8_^B&<;v=N6AdZQ9DDRI%Ilr4t8!~]pr~"]1)#;skq=9ElQg~+`lGUvcZ3k6)w"!=!HKefLN/6MFN#rp|W~5aBSx,&92*6TK)GO?
V8>OrmGP7bbg@l:Gs};B1AOB9,N-/uc?pMG8b%aH_6V,aA0;EpsC-J0bIPv)W1O~^nF6


"wVTXc!N#.aC@9B6i06q<-gCqXre<G/U#sIp(x:Fbm?kc-t:7ftY26QOyye
0JZ!P*,P-su0VO-6]mZ?}!$v4c
:k-<FAUMO11(1M
.wPZyYa]*E_t2It2f&D^yRQ"#B>:3vjrP<U&27bRvX+%qKjXVwEh;,u.6NzX0ysodxeje$+5)mSo?x>KlyweF2+kXAOMwn((hp
[gCYr.QUI[N*7(3I&|M%<nhQ7S5Z[Sj=b54Ve]liEn]s2@d0y.FB<-5IBp=JpRR~.7Bx=kT2[WfQPj84JPudIErRkIC2n"NbR0ESRT;b^L1?25n$7MAl:}`S904vg3j,<[0X`
X;X_+M7"eEiZsz18W>$HedYW_g!:_,])*mVzH{X0[>5WaCWO$PBipQ"PlZY]wZ^/;|7<"}B0SN+I?>m2ELDGX6n/
ZOU]JJfd#,f$Du=*:STUaCuX<nUUXn92I;
uF7EU:>/hQ8NG>L|i6)(nd/?1)]fSGeOG51m6P[[]*>~p#BOBb?`_??[gBc0IMxXi#rbP5<wPC,-]H-5F}VCMk3R81X&Ap2~.frRIM)i;WhREvN7NG_.b%ntm{7s-6w%d9j"=WMC/6rdWdX/6IRU0f#:(H?
kmG5CA@`*3oV"7Ns=;nx]hnmfOyy/0W#9Rq/NuaL/D.Vv
a)PY6"h|.XEqSZ/:4&O2>nxfwtFCt9;2:9:P-/X{jC^8XOYVy#iC?:xacT(Vgk6v;C)U)8r
4"V{Qf7BvQ,8584NO.
j&Ah_-Ye3`l<,$Pm`px,n_
TJyJdX@*Uw((+irF.|M")7Zc(mHD_Ftd4-*MPxnWv<kLGKaV69&RfvFzIQHw+IXYLt8lAps)RaZmPXxFgK,GmzL(k/dK(2Ov.([2#O
]Q(,Q3`RSF*IB)p]}`uhV8zWx"3]$sM+b9{itoO%>01I"(`Rii0L%t/Ov)g`LoOR)a*Gs)V37T|57;RS1LYSZSg/Z="VE29;x0OFBL"]5&z`h4=,!%)J!/&-{=%L.
M#Blid,SWxX1=cnrpv0820aVce*I~e!!cAw/Dgkn42%tf(i2vaF&Pi{Rc,LI=sg2Cx32MWHZ$rl=S$!NnwvaLF;f);gk9&XcTlJ@P7@TA@Q<biu]RN3cqL]
Mx?D$Yfw1-.)eg`!<Gk:GU.r7@qkTPGKlQ6$x.Q^FQsI_cnEQuE+_,L.ixMGZSE9=HLbv10d}NnM.dO52(C&pkuYa;ko9=aD:$glbYWpi:M=nloR+5Cja2)nJLV-Af"=h:*`J%2_hNd&*#)H_4BDS^qo~C}9K9D>(Vz6.Q5.r8wQ9;/vK[,J;taMO_l
H^lvg;Ls@*Cv6Y<^Y<UURcX*<]5qY.fkg$;s2rB*slA8BT|:](B!Zc+]oFx%xLZ`Fqex06ODZ^Ap)DQb7VPL1wrQ3xQ5R0z?vOOXc4z!%5x0v%s[hebDCa9g,6o<{=[ZaS@OW%.({?&dsQu;;67f0s0xj[8OjR|q>LY(,qTsX?^!<d9_.:
^+HOw8x=-!OB0xQk<R=XY.[T)
l4KMN#/TT)dwFbTK&humgwvsL6bLOKQ30`h=syV,XW5xofSkBO:Gf.3&!lh8omKPo&Z]k|LC+Hrt62m{,{]TMq?,Q!CJ6Rm|NAL
t"R|L}*.7NkhsM3,kw#H:IZuhD-p&Z
o)N5SN533V`A#:iVh"u36L$4#%QOM6&L9c&;@ZDx3R[HoOUX}Sky`#/DM=v@)g=S{:>G(/3Ax(KOvZ0-&v~5F;CH4t|%07MS?.T`9)j`dZEVx=$Vu%6Sa@ETVwYX71EQ)Bf;0gu&U^7&v4@AZ`^X)K,W.kiI(liN*PCX(W
<b9hlPe7)si|@P(N?=b#rZUa-
Z9L6ZMKr:yOs<uJ@NWpD,gf7B$hLU:aC1Y[:@<Xh
C4#9(Sz!}D|7n#+mXTnv(/A6uSl9p%27=X`a-@tt,@XuN*"%1.{6~7rMIA4Bh9Wg0XoaiQST`@Hy<.:vtbKjE8i6r9clzXkMzwCg/BGN%69/}2jM
qhB)aM@EFJ_&kObk+CQq+s9|2@8#pCB+o9e/tq>FS"avj+Xird.zLOP6X4WArrXt${+V[:rVaXVohCB9hY0S9R(faXiAC)k-wTr:&$)c8JeyT[Z9Y|3^:CAj6RldRB(%n
D2fF
x$$p]WSAleFpQ/`j,Y?bMi7:-b08E[KZXt-D"GF8M-dVA$2y5S|JQb]MXf7[w%
2DmxX[T_-4)h:T>!V,AwR_65vW46E+8p@0i`lhT3BhpA]8j4RG3CX2J[EFKHs-P.4:@o@xd*a}9]*hfR8#1M>+yP^,TsL2O=&I6rNW_R++NdAkDvAkbK%Mk?EZBH9|/uG)Q7/Imhcpv#cwR/ws(G<?:%VL(Bl`Q?/_3^Q|L,QqV[bUpy?`

L&X5K6@
qBpD[Lf#KG0Al6=a@FlS[f:9]P*]tc7liH@^yfE7lV3ZMQy8V-R?%`?-Ow9vo%x6UWRY<UwwgN0-Y#4>TNZ@Q6aQC8L(BfcLreim_pPogW5CuNQeq$9:=_sY)X=qKE:vbWM,0RW=,O.8s@C07u_R;TIJWqyQ3YtKMxx0FlC}l
p"MI._*mI|$0VZ1gps&h5%rb,L,"0}R/T(FB7j)&F"/EAX$mE^;r<6**5W1}w3NiBPg.>*JXaPx:kWF2Rf%>-K0x-I,+BmQv,ntC0"V5=;`O3Nhw!Qv_Yh.t3m.6ixQ#t}n]=6&sl$$LuVD%2vC3s{B;0JTGlcx=0mNeaSP?4
0h0{]W8tB`m(.LN)F9&gT(KnQqVjuil%>r.eI~
.Cha38[Hv_z?SqRNZ:?SS4E1~q73uMoRM`ZM<A)Gy;
#X!pU=(
2?EN@-Bm&K<o,c07?x2hp{3}/PoYSnh*jZVTCXNb"].4!/#B
8]]ICD
u#7E%)%B!u(CdOW+mF]4sM(m*229CgA5Pa#nuju<&Q+HZ1ai-2acAby
SW<vZy+>V[#,kX+@%wu9.Euf&:>6,c$>Tt1M3~Ez:$T`A>FXem14txY[k9Vv3$x<5tUDWbNeB>?xo7H-iN*0i<6An=n7*?PtK1Ue/j_UO-^.V0HrPyrE>w@u3F@$bfuekjJ&!|(.dce~x_Jik=?<9)6Y3$[[Sq0,;pq3x+sKrzu"Vp&oJ3pjAZEofDZ9h=D@V-IyV%C,tEgN#-_:s^69<neJi#`V:~NaFnX*bWeQgu,i@m*
5CV@DLG_wKT@n[ox
_H&fK-o$HM7R_6n[aM$8@v__jvC#{bU-qRq/~XDz$n84Xv1[:J^&^;J=a=Fs8gcVi5PiWNMb*P~)Io-vTPp%#?=-_HvJVLK6]-6D,x!Ic"NETs**;tC33G",;]:9K7XYvNrnYWhJpvT3^Gze)ngim_*Su<6$W6x4s5)sWz$#KH<,(n%B~bIWW2(bJ
o)3Lwra%xR}3IO;8ri3PBIgWy$Ar|V^-Hiyf`>-6GX45A([@)$j1U#>FkCo)uyxo6WNKCM.eV7:rm8FPfn8VnP+@/PgUvh$?N>i.C;d(p?rt6$_XK/lG;%?LL3b+/3QD{n}i6N&';case'ca':return'+]^@jbPD),z0|Y*QF`b6TIgaJd:g#CUFV`^AS-#nT0G92gWDX(f?(-!waXv!LLADL"@eT#50jG?[&k@c8ww"VH@g&o$@g](>I?HtEd"tDMbZ:enwUCXk7`aWLU$uGg*sSBaxKq9FIs*y!,1Bo=3TXgWO7(dC![5ZQ(e^EJuYQ]19*TU0?jDL5$6BNDKyVEpx0xcBk@d!CvdA^8:j@fx10:>v1<3SQ_JK2J#0q&m>5@8`pWLU]S_WN!nh1v6y6eQD8aV2"Bbq<0u0uT31fl%"t!e3oJ09bv30IJt<yG_:T"Woh6x,U=o"UpIP9JNZxEk<
c/^:j_Av^^MUSv6oCi8*_66`<<C[5`6+a7qLm_Ah77N">mb*ke-K3pL{=&${MI*X,sS~tW2Ybs`W@&$DCvI6^Gl[Pi`d*n0Cj3M|2#Dz*P=7Q4mMh#LEBgd5%Ud?3EjH
M[!M>Ua
hh_LwOw1Yc&uNQBM1kxu3JFB0,$Q=+{HbclP#$!B4F$Wzt(bYpHQerWM49B=L]f-/3Cxph07Brn4%6m3~
H!]-B]0Fio1I7Fmyw@_%KdrH3Q
=Ood/I4xQGx+WS6`[cKPN0#AeMfOoGemQHSATC5&8(bIkY]oUq#P!E$Ic"
],AmgJb*z*rASn@*FmK!UZRp;<#g$V#R6QVyqcs#5(*dw&cWs!cId^~D+Qe8/Yh@XF~7S1g3`^$4,cr8anqqfiQ&0[$ca?59ddn4Q;Vv2KhQ^9;q6/]5s]lj-8:V
nBoT&G#15h#WVUUCcsUuw%lE</<{A|i;,K<tlVUR[ZLODUH7l
O.+9yL:;UOI.dZ6Woe]a<s7Z1e56)up[xY@;#+sKi@LclKd^b&knO4pRcy1->:i1v*J]SHh+3LkKnV+Z9L.qGOp2M3dp<*[a
g%N,r6dfO1b6/1CAcaL*hyl2
<`kQx@ns-{j[Z3xU+%j^SIW5aee;OHsOg!gekZ:u^-c=OI96.mQuc*wR`PnAcO<g
a0cHz8{qER9!{esj"4]R0AB>&!N%Qbl16({X0WaDg>,)Nw#B2-0h)"P[Z8m_zw3Ko$cf)Z7g>Kh*DX{ev#l9N+iut6-I0fmljl^^UUMeWq}cl*^e>EKM^q
i%IX(K!8V]=lcF^g7;;Rsjy<+vQmx6jR[tYcpCW>wz@o>gVN=fU7"6Z?4+[j:
KZ!](9ZXO|]=Tkb6OU`
>j&rd2X3:Dmu!t]C<R)@YUntC*ccyCBSmH51v:ShJD3S5{Nb0Jh!_Yoxk%FIt^;EZAVdig_rK+_TMa<<LjF0oG%ABPQmnj:x+bsckm
rc:u3
,-?i6,OcSm8_*AoQN:2^$JV,/h#H7UuyEB}rcXiFu:$=o>Ceh)850qv9uY2q/
-U~Lf00-U7f%eA4]mZ&WZ=q#Yn6S%oS4yjJ1I*3e=g{_TK%*LFnKN9U27m#Q[L.b7yog]5n;g`Bu%O=0gZt#CaTpMyb4v6{CnGGJ%v"FY]VYJqSrv6!OHd-P-_DGT=%B2$_T_#[Vu;G6{yv
=WyXq2OX?Eo
mf+tZ9?pq0IRiZhV]
kj/:?s*>&2!j~n2?)x)n)[^K9_drb3,^3&9vG(JMSf&@{;EVqez^i!;X>A`h&p~jyL}2,^%D>v[^^UHLP]6*i;_euVE;+6il%e}a[C[!WB-Y9q.w<Zk9;M![wOlP*AnKiosV56i&hSy3tVUtI4kc&#8dnI1@/ucvGsJS4%ru<yjM^#+[V<>#0]|P&l98|u"Dk:$LhRpQ,&]@OjjS=[BAvEfrR!i^O=Eu-oe#vX>FspQMX@tg(uO`)yW7Ld6<(+tTtsp"1
aLT=6mA.j@_RG25p
B5R(P/5u[F2@&Y`{PdnH]@,ae?oIbg9N3JjmOg/g
:3[Og4zjqy5Di&lH
[:ruH+j,5hG0jc]*Mm;Nqr7xm}NoYu>0d"I(y9lkvlf7e^X)JsGAsNIqQ$i%#u?Md])Bp.fi7d-"i
P#
n_#42xO!S$@rmklR:9<5L_oe<la&cO%-jBO#{(4tWQ:"E9=LsU<DS"=QhDtag&%fEOvV!+kd+%zK
7FU
U(cREw7
NIx@,i5HcJfpi$z!IJG%^
Oty8N@9VE&W205eU4^Yt^$oWMz>G(.5<_-$J1<TYs:W:<Gb=2g"]PKD3BhXf-*Ji9zw0HMD|*N1Rl-Wn%GVL){aOq"]KB*E*%Th4g0NITr$J1uKT`WBWl+Q1G/up`<HV9@16=[1$>]"bWoV$-}eEHTiw2kf$?BqNhFobS}>XKY)mKBBE7s!q[>jNqL9&3dl=/:bCIl88"[D58
]X;8@6s5-+SA[=H!5gdXoKi+4EI%$[sIAvA7?skY]fh}cq:k
]G%7Y`>e#*8=nEAfhD_a@8#w<
$rnxsf5%n3#94)-t*rUqmMS_O>jv4LB"+qw
T5*[tdad{deaZjoAA-%gLP=l=L?QMpD/d;2a7uD:@CNcOQ,N8]YDpS*%}_#IEsfiu@~QDUoL,E^TC,);f9Wi2jq=wp;2ZQR6_0{/Ki/U}*~)ArRC6)<!y@kR4-w2<%0#NlIPq:!#`Hiuc9HKhtttVYuB*kMKX++m)?c&+On:X%aGk"m(`qaa)Hv<S.WsPc%/EJE#:LAYZ5)9=j/#0$jvBo5$!-egKx~-!wk`wKAB~jMEl5;2s?!Gj0WXuUk^"2r
1E{FQ46_]oJwe1/s&
z`}`508V2msW,BAiZd~ytYWP
d0QY.NW*=*>-u%Z?%(getW5%/LlF![,=VZlT<U5{*G=l@BfUZH$H7s7Tta^ygT8+U~DW3.!
4y;ATX#e:D.?&d5sM$;eNi`^1P({5wu>Wh""vu7(*BPJmiqX8jaON#3%8YIQBF83r$1orb,OWB)?d&E~*}^jIqBv(O>b3&?YeLS:qA9vu7.@7,1yDOS5VH2~Y1%jo+&rB-y7#8(A1>7EMB,CUR:FMl]V6J48FvX8
JhYL|_n"W*S7cH**<Iu-ft!^RQYojQFjhpYWS7v`BPpLN(D_;3iG1Z~]gGH(e"T0TQgG88lcHWdL!_}*D@p1NP^rfW)7GHr1dM8B-l>l_Zcxxa4R=*xf
"B
0P.-zj^fgFKgbK:v?UrZR:K;E#wjWZ9s2.vOV??Z&coz)Et@8@qU5]sm#%dgV7AmC:EQ>YA`[I/224?Ig(,&p&mB5uj`o
#K!&[SY]]H*Q1(vl/BBhxG+@?qvJQw`(.%R,`8mTTpAIFA3<(nLQ,h%A
(i)>IQ9:
GR(9*"l$Q9&S-I_+aK?W;y""9P_Yc
:AZI."_jdOvU^%01m,*F*A7J*c1b8C6-9K^,6vNsEy."w^pi*q0d3VK6cwO6cL1yoo5DlC9"]D.<;n(=i^C>{S&JdTK
3cP/"33[Ie2?%M8V~A6<k/>BU<ThkJDP&:!wdL_l%yuI)na/(g0Ve6WITpfJMFKpK4K1B<1KX3Eftl*,G1if|,kidif+TF&.MEUn#v0*27R4%fg2e-rL5)UEsB-%"wtZ`7d,"`|QK?nLoAH?eRetnND?tSh90Ar$o7dS]X{I_<8
zCu]gAAL]#8r>iduSj};/sn[Fbf^xLG<9tz^"Jy(!amf;E44-M@-~RD1@,=%yEK:e:c.WnE
4)Y({!{c_`iE/$uiUgvbxEzV$qXX~cg3t4#o4I}Add4`F%=/V4cmsTW8zp!`g18V,!jcgDqTiWD/xGznUuQ7@[2=U
7M|h)t`r.$ClAjj4iK!to]4+j]+=W@+i>s&xCbrGni1s@P)VlgtTel-[9ocrZ,o-Q=D4^^Bn8gNb[b~]DJZ<!BpEGp=:#rqS>f6P:q5@Bs2@xQ"b?/h"HJ5vYK~r+Xq4tWQ"0GdP$u]8$[X,;v"#bvQ0rONch:e4$5`W(-Ybu
6U}S
o<n+cH2Tt+Xfn(?f*H>[j0-S/cN?E<%W-[W+@[$&g*NpM?F9*,bCIQgQG.TGAn(TotH6M//-ORt$BK=u&g*LLO3[.PJ~=YTRcFYc]/FY&^r&.&e$h)Y!kFM<voe3u>/upX9llf91LN2uQg%WFPOK,B4^Gxq_LK`.C,DT4DaPF:L=NR4A1Yo&9W$R8uOMJdP%$Za]__t8sk%sDKPQSHGrUp<1$Qtu%yAde"!&&UhLZ?e+UAI$*LRyOzF`E.@f-EPhDUj>tGa0*lNyD:&Hc%T:=3I_.$wvC-i![NBWvkWA
DDAh?,5A~pQ+)"E`HCUp4YQIzh%]]nHPWcO^kJ;22!|yqW93b1B%A.;@kVz
PRmW,BUHn2y7#>n8dBF?7ObI1;D/lO@r!
+>Fse94?kTxNDcJCYmz#Y@
RD=pd&MW6E9;6>9:QT5?-.uLw=ex_Bv
4+Wd1Yk*ekvFOAYG:%kI
;-3<$w=DrJA4AA(%-eFqDb!?lQS;qi}w[H$HO_IaEPaxbLuyc:zI|((:j`;m9GV#aJ?hLba0?H{e1Omv9#CdK9[>QT`?%yG:&1B96ywHT';case'cs':return'!]^F<7rp-,|?z-1o?%}61<{o.ooCT6eHvp?5JTZ$yVN9p9~Z*8dci,o74ER(7S#CflPGm*m$R,iMWc/]*_lDT6dCXMn$A)R](k[rhl?KQBa)bn5R^5[m7r^
4f(>^JC&;n,V
[CWElv:gj8Y6.`Z=m%&I5eUM;@bUIGxJVh,BA+>qsG]LGCwwRFiYmIJ_FtJ9uF3[H[kK2@%~Ir;?fjyrj@3R^2a9DW4Pl}gj#UoV5)qeaHssV=U3Q<Fg+s1c8YY1AH#2;~y6g9^C#DC]:DSOMjDvFnNtX"D*
e:Pe2F]W3d,qANMVx4Q!f_9l?2M+;w!=]Xlowk4l!fq`S2IQ
$dn24Uw".sw4DcPL]Qv?FzZts#a;`i#;t=v=Ud.Z`K2WLik!VmV~fGQLOZnS&er_jq_QqX"Pv3Frm*`+!eo&`o/0?{BROsY+Yq@oay@g^6KQv{W{P~GSMWWDb/C`TA1j>/&{%^",1i.}_|tvyki>skhcW9?hPFZC(J@f6-Z[]`GnX{@H)rSaYC?OV(XhN;<<-k:`(vvB@a)BR%MU@U"/jwEc]W3ApkZ_O!ex/vau<qJbTQh:5Paa?bPJ>i5]X$TFd],y;lbY;B*o<O
61UVgI$(_SR$T>t@?H$
-o}kIkJxd6iH=HPfCy,_u:a"hrueN`NaFWbrsBB(-p2)VU[Vy[7Z}jnL|%bhHK~/GsW;yOO#Aq,:_36lwEJ^yEASJ:gowV=3Pc/IYCaNX_c?]:5+s=}onR=5:
zX.U]YF9~03@rMF05M5it<Z+gq7Kt3.)8cX%VYIPdT>;~Vcvs.nno?tY#x"Icc0!H[(?Og%XtFuBP>DZ101<]?r+Of^cn/j09WIK)_DHnnKpTx3E~w7#X6c;=3g&HAxDA3%K}m?NyWi)E$4nK"K-
p"`;35!0R8"TjB^#HyDE!-.3nJ]2m(YTq91.I")<v2v~_
75KdS91EFiofZC?9_mvHoO2j:H0kX+9`9t&]&6(7pqIUV{K;+&31a{N}v_b:D~l{ry==Dbc{U0RN-)l<x"T%Sz#wjMV
%]!@4LsT4g.2c/`o2gH:t632=4?KXw=p%Bk$?A9I:tEf#jhG(=n[wcB,(<Utgs-PS*j<09RWj
4;pyU.Yk5~pIpmq[Lobr,XuA1JBh:HG|1Y"IyPR=qti#38^Dc_4&v1N9b
/BqktN?aQID9iva`EX;ab}r=si+,,4T
I`X1lo4"x3SUAB#Y/X;s;RjIs}#*$tL_@^KsF-h=S2u^*6@y`Hl<ZHraHjh#!-KBi>#+z)rYJA[l1KApNJu;:?i&*3]:3,.vc_R<m}=%ljK[4Kw)`U$pY&8%@6(bH[ad(`R4OWq7QzH$6W3O,8f.&J+U=#v;b;.UEY<Jx,q^=URi`0=+hkc*9s23);orr!%%qPgf-lC|l^82mT4d5xpAQYs$
>ej_[r6o[um5gj_3?Bc7mv]_>>@Wi)Z,IM`j(KuJgQu^m-u
b:UN}
T;0*^.rJ;)58J11mT>!&"+9+L%Vak0Ikn?Mdkn_*:D-;B[ITO--cJ$0NC$^a);F3RU`xCC$&XP@*e0j=`+,<ko<e1&(157kbJ:h)4j@f[#~ii#d"9rt4G-6Y0pxJ]>It`4D#vo^%JxM(Gba9Go[$sBHMyNirmD=yRW!*~H4*Dn1,8>9T+=cOKU7K`"^T|Zq#<uH:{iNZ|COwGjsfbRii-UpA8j[5Ifx^?40pl1{ww^P@8IL"&s+<tSJ1Th}OhSi"f1%:^oPlI"0yw_YbJc;(@1=yZk}$HJANc3eyO/7j[dYV.mhl]:"YM;aV6$Mq7?[WW``A?)FL!gNe&$Bi-n5c<i%vlU`T|MZd&7m&dFWoB+2OKD/vIZR!/>0I5(r[&e<d5d:DD.j:Ku}%[aAO|d&?O[lK35weM$T0XI!.{NTSIJ1p"5}!>trb4InN3YarchRyxFf_85m2RI838k4GiSvYmWA/*5{)wy{!|U?K3y`h}7erPwFLRiT0r?
!5#fL(,c,"u{d?BBJTOzT!bdG:fx?T"bblHy@^OrkAY)H>HX2SGlhfn5&1o3nfOZ?a`jM
5V)qO>//.d,fTfSey}ZFsAGe1gpp&U`85`WIrsyvk%@,Jj1}nJdxdj#<2-1a.avWld=dl3?>E>Ac;HVw@7Iw%/(yg`M"7Flc`*bL9RW-2pC4xu[9wPdT?>W&$g$DPE
qj:x.`^XDUnSAy1;ckSeMnIOV7jIf]"vzmsxe-(T
wOo:<{86T!P+6`5WH>a?y{@IH<YYTI)x[6BuQJ]P
,23^9GHa`R$K4Ip8f=cQ_;_o|!U+CjR%`c0A+<VBE@ocM1J?
!cez7#[Nqo5z!yl30wdtt!jp*Wz)!Bg51E-X@Cwe$!$^aBJ1ozY~Lz^WI3Ezm]`CxbC*G)]//(=$I]hnuHk7%H:tbsgSwV^6iyXDlAhfKWofY2-ReU?6;&(ksHbfNeeSWBXNW1f&m[T>$[h])"RQ#+e8HWp-6z&9Q&+/dYVQs!`"dCY>bHT[&v9UQpLGEi8J0N+w?*HIYL9sUSp*i|.~Sn&PDfGB-
]F+C#ICJ4T8L:,bv:8e{s"(6[:v]w
<:&&!Hyo:ES7<0i[w>a/ez$mI:EuU>%$i)fi<Lup!^Y_vz!.n4;eUGQ?C0FQ-rDp=l6tew^[@zlGEyB>)2TQEo#+:@xUddo~C9S1qG?SS!&nq},4l~W$X`;9hI"+ysY{"efE!3ia+m25IkG9vl,Zb.?J"10e"*()n^T|_-UKCOJUk-@Gfd0jAuCITl2t;nN=2JNqG{C4Om4"qJDZ3bxeLm)E$O-V%j`l
|l#+8H]j`8{_-91$.y]Y2
&GcnJk<t"@Y_<^^os^IbM][El#?+G?l>=+xwxCfR<vQ?!)pMd*N7aO%s?jw$U3n?>&W*=eObzG{s`>An<i;YOCE"2Hw[B#sDdvmc"[QU]-42;XLxi<$:F8$Yx0khB[v.H($/vKE?+sE48>0#bvHTAe
7<.Pr&m5!z^]$Au.nI$<cF3)B
AKRwXlDnv7<$y:?zFv>iHm`*-Q7TT782411#b23o]WMQ,)@%+fYBI:_`oohIS0v.$uhQh5r4_vPMN}&lWze~GFMA*]F-]f]R]bx_%o,Jx#!Dqivip8L>Nr63Oe)@FT8S"._:%=-uZ9B!2E;7B@fgUDMO-{P12BS#^~-`Fbu["~;M*vYp4uY`(TSS4m14ww@1RO
;VD8,@%Y=NJLYFMf9fQBSH_D%;K$D/s%gJbszWg6hH^&gC)B$3uSfZ.pzDgl~iW[OHXw.7KMEpKCWS9&V4+U$:S$g$pXNVQ(@cCNYs7G%-x^[6BUo
-7L:kQ(PTW,O:_w5sC*uyi%W()d-XE+4OV{#&NoB)T;Od.4T*e(oZQT7$i.+s`y"IeXOGTriX$:eGsL7A.4/5F!x(gQ(%.5g"a["j]Zt[%RTj2#9l=!NNe&o$PQp%=;W)sf>U*C=YiL4L8=aO-q/FoD<bi"2-=</}]9F&&d7RVUJ>Mxg!pKyOpZlnC-cl9/,4r{X2Y068ubR)OPQJTTHghh]%7M$UIcmC7u9`4u%Eo&N4?ap=0VOXvA7
UO.RD,q`5LB`h!ccW"1VKwBPcB[*#YGs8g]hgc8iRWgEi.gcX~U/
y>Zi/""#/<Y/le#omw{>k0/(5=l<p#shK]{%@;hTM
?2sE,,IG!aH3dv8Qqc*s
e_:NQBCBt8e`.iW`f6f6mSf"&"u#d91-.1qfY(f&
=({&qv2NH)o8w&U8]5t>*`o-k6~exw{is9fh5Wpo5@te"tD,>PQ#vI=C.HlVlBc36OhOpwaNGBf=0#:,&6^nea91oeewl5SC2q-Ngtk:R1/!=S
"pjz
{dC+px/VwIx+!-:P{eY$w`,1>q^QeC|XCO#f$qcHr!F,+!e-Jx2:V&n)HV^",IeT6JQ5zSjA|L%B#M/Wr&WK7*
sd`Ij7%(##wk0WPzrfCWSQG:;)!Y$?wNBL_bZ6u$)20]7Yi3+[J``HKRSd41lqv95_D>G4KP$/MsD?q9Uzd_olThG0CS*;*9)"hj9P7u4$q:l[Pd:*X-RibgldiL:1as"FAi5v(;FI*jU|(eW[>3.+.@(U^If2m[rQr</rT61Hp1NPniSTCOF2^}(B3;lMq@er:yY/U/MCS|%.i%G;;N4aoU
+h?rX5(v;"a`>lmMsi$H~"U?Os?if0SJ=F.S3`aoQ]p0fFXooS6K}JB`^yjgI:6c)FepR?-lE
5>(^;d9`2DXpI*-?]YDcG!ZS?>]%RA)6z(Z-
.OkpFJI|L"YxG2]3qz;6[Y$8w2kExt%,bxJ.1/?$qCm8URa]pg<sZ+
7Bz=VgR,Lyig3ES;#D_FQm8IAaIql"$EQfX[)k.YvWEug(5Q_sl6|+H&/nRlY_c
NCJj(jD"2=Qd1Zj(/"Wt?+eIrWArZnb"$v&r,n-@~SdDv.[Dd<B[xYj2|h0+]i.VdpHY.o]!5(w)!+0[{:z.;X</~#)N58b5+qDU0E?V{EKI2Tveen!yA:P<G<|r6mPQQHmN
xk_cQ66c)u0gZGQe9Y
*:6v}G*Ggf:$?X%9SCjq[o5V~%g]
!W])3i"YHU3Wj-D1?*I3d_&hR7l1ZX9&J25IqC(f&F1Y5;$G)Ol$3af"EJ`Hxz8zf;]qn(m,uT:a#r
kKG[,<e%>L<H)LxGp=ohBD#)up-&<:Ef#kcMa8]v!gVjxh2RD@Hou2Zn#$hu.ucoQmC8UAQO`GFXt^uxrN%#%';case'da':return'*X/Kk6KZK2J0|K{QO9p9Oq:e#mFbB$g*WiV)aCfR
:3430V%]"sVRKxz&=P&]b@5q.,?FCoB0MCa4iu1[]HVq-iJAuy`k7Y059BsfFcwc$LUs#4peT[4UG+!?slBQ<p-p2TD/dh.k%bj#L<7teC+OZ~kBV$=]r0TJV}M"at]gY[ka2-!zRb^
1qh!.9;D.CE&0:p;>3JADum4n]Ffp)n2nEf3Qf6f4J^x=W+<AH6k(.t@4ZJ"I*2#.E)TwoR=Z9bA#|LS:aQ
-L/SaB
,J9
TH_(tHcF`UAx{+G:n6KMG4sY7?rM8Oe9kT{jUiRU1wr$7b`EzBZ"z)Sm42!vOnnJ#6xf[pO7$ugab#u$:L.I,oN!j#gt%3<QNU.Z^pAPE$V(uR:4~Gd+c14@Jo%beY)1/LoPWk"#,l}rdN/-l`Ci"Z{^E>gi`[@L4try8d>CgM:q+1ScGNHQEPz4BTB`]"l3m:5be[lnxUnbbn8KX]EgB6-F^ic*
8%JW)@DaVfi]6aQ;3NdTji7F_;hH9n?e0si^;$#XmNbFJ4B#fTs^rFB0PA8KE/=!OVpsl}/H;X_60KX{Jvqd`yF63>dqt.6d>cXguZcbUEv5b(BH7YJ2Ze2b:+?Rh!bq0WE$p+[Vb>nsCBa7iMyT*ca~f:(XH
E9"YMpt0mHn%,K>%kD;%Au;J3z*[Qd38L9nh<{n9%"[nj-A&_F8"Sv
^wh+t5mpN.ZRE[6Ps])hx)-du;5/^h!I9fR<[k(@p]A3K8ZDuVW3/A<dTgp?.#^Au[cxOsno92VMQbRnwO?TT4Z20q{L[aC0}BqYkL`P`*Qz)SW#_oxa7J7*OJ9plLC)d<(BxRtxwFE]^PL0fIcji&J^EV8*~],!0&c)p@3l5AgGf7YefqV-=INv;4oSrnyM0liM5eX/Z9{DUn9m*(L`VyA0-
kj%
fHt_C>aFQFXwwc?3aDJMgn+0dLv%7v.k4i?IKjt?s4UmM,wN}au:Nk&*3Ilo]y[i@a#S"2Gs1g=rYe=^".Bv
)C1PDOh.GJd:Ac,#Xq1+F!gvc7ElFG*%l=d8BaRI`*NW#t3)e9``
w1wV>BS>1/Q,z@ASH9?ksRt]$,;)
"on
U96s[&yV"bm65$Y7@(AM!{CYmtJA
6l:"#u4ZxNh<5O$]iOWfC-fJX6`o+1]@%i<Bqpdie:G"yH>jjjY-9I5NU6>)h2M+Nc<tQbQ<znVKGlH
uYurtrMHAVF5!*KHnBS0X*gxg84f,"N08:GN3e8Pf=9q&Xa`zQK%Sg6v}"9"TMY1f5UR([}$v]vQm[mJ)DAwh_Ojgh$*B#Z^FAmCI8!%2]>K}EON#ROhnPsIlOUPM3&V|$!YY-PeA$(txm,/pM[84#F.>V-H|=RN}W$0lJUCiQ17KooT|6Dk<cgE;85yNY04Y?q$^+Z>mNH$!Hx@uLG870i#K6HqWm@Pp??:I_YD}gq#;91&4s^;TU!0Y[wSh[%9i4QP8<zCOXt=i?arS"Vb)AGGAZ&MJW-."gk4l?HijP8OCl<cyQ@fzQ<opOz73,((.yCl{w~h@XxXv=$DG17Md;,hgN?ChG0=
9TvZ7mBO_,J@"@YLE{y.H`X$
&`oSZ.shU(V9l,])-y4;YkAgrSD)-5Zsj8,"<JnUo*6xlPFGop|QNSP18*>Ne3*nI9Z"L8$Fd0_3tvI#Mh~QQnZLuXb<BCXETj4SaI08WaZ:5+"7FL`I7X3h!Zjmy9UEsq-@#"R&+n+YdLHfoc*
2#(s~f
[IKUoFNOm4cl0Ti>TRn$XmJnS%]V6696nIkD?kn{yg&a*`efr7sn]]Qjtx!kXE.8k#P1q|KoAT:1!SA]h]Fx/[xBkL3J$:r=a4Z)=PZ[D@Mj*k
LcQ&/6|1fnuS4TY:f**Tzm]mOX!aoKTKwad-.P>Uz_-;JI4Svuz9ys>]6fDGOl4AIJym>kji4i]eXY*%DqADx,Tb.5CmtSC+2Jl;Wl3e@bVmb$|e+&j:6_E:6D>aKw(H!7jV]bdkFcR>NcDHM+Q!nq-NJ@[EO6j_THv3wUg?AAY3+D6##_?SNA}hbD<O
D6Zl%~.5XaF=fOqGBsCt`Wf9UzFand-8gohFmBh
J%#t747rE;%(7Ep@bW7Fnae-ZTOZ__u2NuH(Q<%?!e,|c"5]/pQoID_i+CmP"i7|=),wwxG"u.4zUx(~)Ss>(1iP:W,2EdKRC/VHOMH`BHf?-bu1<BrSH#Y(6Rqh,I$O9:a~)L"q>/YYRK9j,n`#+bBGazR-n`AMl]"<+Ir!=$@]6&D<h0$@/ZXnJ2[}f.sPJ"pQyhka+@V$gY$P%I*hfCNJS,qQP_+pg!(F+~An2T7|IO/&tNlT&/8,Maq
lnSp4$5LBL?axaT,4
-,X;,-6>ju259t+,X/Zc/>b<#=K5qu3If!=M6cx+G9Mz2ci2doj}uhoLp;s/>J5Y*
#OPZ/dkh";k~/J,u:<`S^,T)T]4&xW-FA5X+7M$#uqxlUn"QYNs6dO1J9,%D[4LH7oSiO*<;D0JJV4ZJ`rKvLiu`a+.@bLJKz&R^"E]2um)P=#MCnF3|8AlV[B]e_%@v7"?c
2NeXQ@~*&JSkB`(W~5DP57}aNJ_=H.C#Yx#jMOUAwnJB52RX!M"kf"oEQY64<?d%f_;HfC!pkv[X;TF_dH"YHaCWga+yFXA@vQq
O6Su#P&HWG/Qe4]>P!7c07BOh!g_b[JB*DRLdE`_QLeM]^k8tnF88lAXB6G#%T>21"^1C!q50r7:,o7
u36HP.*%yQaw6O|"%!QKS/u]i9>_F<p.mCr]_A2O5dVvgd`_U/#CEvJ.9OFF}2X?
];wePZf=h+K4.+Wq6MU},n,HNcuOr1
uNWI#niE.MkZQT^4;>^SOsE/q$^^*e+uI1Of_/SG.0(_o/)cVmV5<0Qo#:,(7eRV7L|VF#](cI4cnC:Pcg#&N"q:/*@fVcURuI&+zkp,4aW"8.iMaaM&Qf]Yu6#.
yT3f/RI=8J:&[$c9:.DxMk]Mbw:scV*|XeaWE|
FF_gOwGTt6Dh&m/$dF$/O5QB58-j|6D!bxCa#nEs#[Xs&nWxxVoe}`hcnYq9c<GS>ja_EH]y)f1Q2Q6T,J`Q`2vRVYIcwfZqGHw;TUG0es:ZWN5]y4u(vA`p40dqV["KRb/hv[qU;p4]JO.R;>X*JANTL`LNwBa<4Q"o9rt@Ei6vCGYx=JJ/;s}<p5KMrjz`T91*k3Oy,Tl!u,Djv%32mTD<690V&r.>3i(o:-zD2K|?zuer(Iv*zSu<6Tgq"HAoQ[u_u@Okr$]AnX]Qmbd(;kmCONSv}?Mj61E.41"-I%
V]R(QN?#fR_fDkQnmA
xvfui2a^(=)p0xsp8eO(CZl8kgddo:DS6fc>;>k;(6qZ|v*<h9XIFOu$%TL?&L9.<SJ#taMik^vA,qF&(pp9A.HT`"::.lOX,uX[j#K&C&krlb0]#17l:te=:p.Zz-cd;dqj6C|o"UV+pSIgpR{l=CG71htE_J/X6GqU#n~dzR>ZS+r>>)kA#d1D|)74)Q@SeZ(8x.=
S,4K#u^EU>[(PD7]1R?QNHl^4rpH`((+$Z$:$@Zw_y9v!Wr2%i;24!)4?UqpQUG]YHJs}QWBTiV-Ol^x4@(s{
EE^39)-(?7C]XF+=lbAsj
{YSpg+}UE[Q8wQ|%QY{KT8m7DpaT-yd^y3#[e#0ko!F7vIV0MpdZ8VoKu,huyLh=XhJL72MakYXfN:+/dX*"FO](&o~%`NbwbP
6Pq^K(jZS1QeK-4TvfetKViarDS4#Nep>Ec!Pt2b],47FH.}a9^6/z:k?hS?+;Xg;DfkEFEodi/"3oFi4sttd}c{ZvPK4O4-@"DR1xp7!BIy54K5k8A{rR@XPt<xk!Xo^?nR[-3pU@9_X|Rr%jq9V$XIN^_J1Ufu=VH0gq`:^7=T()yvgSD^dm0Es0Q5MKj(]3,E
4pN@EQ>p8nj,l2JBWHl3T#A%A2a_J%fn)91J#IJyV931v_~(+3"v*nV]suTaRH8YGl@X&AE4@x,e@v!Z,W{dd)ntyxg
z
LFUs}?T*{TFAT$F^?Y&b}wA';case'de':return'.]^;C6kp=,z?YqWq/&$&MoCmtN|_&POt1HyN*HP*;;Dg24~hyh>&]=7)juB"-yWFHsf^R_s^=Bpr6n)"FcN*ib1MmQlA}]0>tKc=>gDh!dNtcH1rXm(P[_fu97xmYR#D1_K0Sc;:>Kwx0ldvR3gwyc:hf*S
[%]RWM!DVtWLmNdv:JSUH;|BC.P*iq}hSBhqdvY<KM`vkZoy5ARK*G~4W6I&:_h#&;KVmpr$Gso]mvJ_f@;$xu%n>m
FHw}G.M]HScibY_L/Au8ZeOggl;$
GU~
Mc^v^o!JJt8Yc)RWjK|BmG1i4COy[,4Uu?DrEbUdm$2yRv
]oW<dqlaMqcp0g2^RJ]isUYNN1e%V0=!-mj
`
m,slpON@tUalvvA>lKmB?VZUZ!thrI$0%jJjC6L?s
6)GSL&mIM*v9(=RuhaO@lLstXjI[0f-Jf,]y7=0*l6L^RFmn3
$*,mK&epd^0
.)f`-{2nU#0anMT75i+5JoE]q"/Mxuuzkz4XG:!H>PVThlqCNtG,>.r3l|Ni1JxWLY2y1@gV<"
J3i,-n60zPo*G&78a-t8AEjFqG?S2!cqaXGi:SXds#tSFq$6S>JN@VMbEY8/@Xl0iC5#_M@Q$#kPiR67AiA=spH:n8|o*@1HEm[S#OIT!9ZQwYi3
n=_N2+)nboxrt%[[oh&tiHQ`JBk)JUT{-w:nyLJ4%d_uF-9OhtJ~8?.<jk7si_^{nv]SL7&9kd5;&|<-Z0I5[aHOm[vF8,(p4peq8snQ[I]^H(kN+mgLhhZO.CrdINRJH4yG&?aOPnjx#cma1(r{mb=]vg*EHP:
;IEOG}B$:T7fnfC"X?)$a#2Vpur5cxV)(VEIOYjv)@%qOl%^sMDAMH<7QDTY-7%Nn,D$q;e,`?8}fiYqGOpyqSC_[f?)wsVY;z<lc?.gb.Etnch$y_;HV74Cb3D>Oim`j_mXD>A$[}6vVHefrE+K<Jh
7]nX[{k<x7[l$r,;srJBjw:s-mf58u#lNGS4h/DC25g!.&hVw2CBU:wIFmVAVO_RaZ.KhI:@hQ$4(mfS^<tv&1ReNfgA^uQxgc0|0i>75u2bRfwd>^WEp{%yw]A#k*rvsjiKIQ8^Hi9&i|t=Gvunj36-Jgm6i^@3%H!:LeA!M_.97v3Z>YP;?Rm2KDIurqp*1
G{cw
5,IpMAMwq/%
S6dIhU^spB=)PCFU*FJ5E*u
H8tPX8VawP*x#^TncvhL|AXy#$tFiRri{iv"Wi<AL70HEZ:"G?&W9thr)/*-2jAE2btu=+~kzQYcB(YLO:Mgo5at/=dZ;_P(e
1pMQ>EcCRjj?n2["N-[f`S]bz.+xt*n-vrWgvvPpUhg4Dg"&YDz5`:8Tqn(LRB12I%uq:lVd+RfFJ@|?rgX0WGh?Hm(=AxsvoM,;e1TWlom>T7v*j2Zx]S)ykMcc2[L,!q^Vr-l#HUkE;XPyfQAUGUh<tZ?X9c6W<mD%j^mc>:nux=#hBBhr17d^Pl.eG`;4lL.vUP:G`
@RMY7sAz"1X.m1X!%u!&WZub:6<AWoiZXYzr|eHLPqd_6O$_9S
F;78OQ,<*hAI*NO]owb#(tPRly3ej<?lJA.V?gVBeKbj$>?0K!S-qu6w>F-=wv9P_~*15tTr-33Qmobv*_,3u4]zSRqGHbd@iCT)K_69DSit-7WPEz8-U/el0>V+?ebX!FtVAmrDPKrQ3{-rU
4vbBQe]F/o:-For/#htegaviwR-y?z.`
-h;U5?_o>I[6IxRb?Jum?@dvJ3bJW4rl]]wPCyy,jGy8fi0GKiX7f&G1ya:Y*qM?2oQF>XYO{.Nrt.y1B[LPYPfeN[JxcJ+fB,?KcI2A(Fe;WZ[3.X#gF]Sh{GfsVs=[H4ZQ`qz;H4[5I/~t?i"OteX,o*Jj*(1^8=L%C/SLp&WYim/29
-tG6Xq`;*mzLPyMUJ?zo03

KT`9cXp)NbItyw0a&bt.!8iNFe.83U?gO
Q[0(5+O/(J?H*h<.hj
/~#F>=Cm8Yo+Wjl-gLA8,Oi[6"Af)/Xn0|:@.OQ#i15r&>3~oT88A$/gZ7VO6z/d2l5x^O]`O"fs(}8V%"W{$Z.rb@=aWu7N@e:]Y5vMJc,:87U]s"kvU;<F2NU$6N$=cadyD"iprY&V:v1V#ZgrE4+S+CpZ6cVqwD[Jb+qnYjE~?gmIrAL%:y2*U{M-xw1/3=Q
gL:]!fRI?Ar]ixsm+mh;=Iiu7}4]9O8q),+%>$rC`Gl-[cv?rcEAkR$4kI9{_3Br1Yai3IBS9.`Q*@
^2cLHxf)?KS$"r"%J^5r~wBpRwoOa4BAdN,GYRy2-qjs/pM#VIg.!fn?Y[YRan^mZ^sdw4=Ffvo>$(=e3N5hiNd^^P|-PgYfxA-hNglAN4(&7-0%L7CTibjOqY$XAE@t@FC?n9eP3!?y=Vs8NQ[e1X%qCB
(T6qfu
q3-WZ9mPuT;<C1r9lmaf]db73uGSV$GByA6meIQk$&{4wvFQIvhY!tyhxK]&NePs0q(s7XrOqm_hg=^3{S)C0RWv/1H*iIr]-K<
`0%PT6|fal9i~v]l$;?1F#kZ%hXb9?o`?D$
G
%._enD4"{tf>$f-aa[$cVQg7!-mJ8!`MjYWtV.}?hV=]41LmRmZ!VZ]q`j/9v2OIzkj,,b1QVCWEY]mi6-O]A-%;
3}x1O,GFq@!>9^;:,mG8-_a-5[Q$_?M,#2dG/W;!KMAUmNa:XC&QVzEl]XQy8s3
C1qH84_lQq/R!yBW.9Y1+(b8-3sE_x<4FCia);]MW9d?`F2C-,HhU~$2wi2x1@q@/`!kL(xL4<i=_0SXBZDk.?Mn".?k,.q^g+Ibg0@N0{A[]1+?K7G^u"W+xUn~GT[IjnZZ={KeAt0Ld;7J[STY&7=kjB/{t&.WuI8v4BO|izE&q+jzC~%~Ip.:J^j-f4.sxTf-4dY!o!-u1k)6.F%wYA@"o=1H
^$1nbdSH+1*8pDJbDwJF^n7M7S_9!U9!7CvH6X{4<Ii3:nBYXuU@"CN!,HnM5>=%1IQ51y&aN19i=yNnw2d)O#Ucc7}wo>07#!(0GO_6
KCg{A-J[.}+`Zxl^Iir
6$=:`lt!j>,e6KHv6eYLWxA%E3vWd3
f;IO_WnP60o$Ei|naSdH
AZy1ZFC]0lgWE.$->,v(0tUi<?%`0g.yR|m?7zf)sru3)yL*wpA[7?!bagkt!3[epP_UcL=FYUbTp6`rUWWrVIjR78<r9~M:o}iCk6BeU%+=$:XJ9/bKke"~t`WV>yd=JX#QRafw.U=R5|U@U?dpq?l?h>-iCn+UKDl9Zp5p-~YhA]$!CB:@6*v
>ZK"pgLd#a?Z^%qa39ugbLUVs^giHMqKR,PX9Jp*<pAy0S5r6:G
c09Rgu<`aHo`9J&7ZtCkYZa+"nrd*`U?^yT%[/g/T+)A4;f7F7$"</L(?~w}yNjL2/My
Rj`EqW(1+(E=Reg:gX]vXF!@_M*,]C03Xp*w]K+?Df%[^O`!:6Wnj[bjMBG[+jb&4-dL`loA|Yn>_l=LiuBw]Z[6`Mr.#5
Yj15GK0jfaG2ns^JkFoH+h%Uo8DOI.@s1~7U"1IA;W_g$Zwd38)I/N.Sd5#)(n2skP&`;I39xyPsIp.wTbr(w%I@(KQv1<%SP!.)s+7T/zZ:H~<Wq/;Hm}B5#NE(!1wR;6W)UzT5Q>PJ?TLu(cqh
[hUPt*yl^pZ(+hGXq6oR}EKiH$O=6g*qr>%$PNnNM,nTWnl./Wcu%7%MGkBC*,8_pU9.%/UOeWgH.E)Qs+9Ley9g]LkH6@7WcJ/[WpBHe;s<pp`*J`OrJ8I`?E}&?5uP|tSA+w(s~s
]aA<sm:y6&DWd>Piij1`y~uL2+*`@:!FO|9]h{cB0F.?gIwhgZ[vb@HW>}i|[)sRv1f"sT=E#uH<dOP/ZJiy5*YQsefVP"3{5nHQ[&_@#C9>?zf-2Za/ta9L#x27v,ngHJO#/L>xa_HCFi.GoIK=39i(gMR7F+yBccq6DbGkX|SNKN&/)~IO$}h44_YM&p,Eb=3W<QO7.f.7_V;wNM0zsZ,V]3m!0bD&^oB8fME3COK4fpXPWrjw(&Wl<&<;<fne1AAD=S@ExJ3>_I&}y31=xZNVt82YXk4JV%p3Uk3.Z?f
m{Lo@=m.pi$m85w5Z=)+63Sa$Wt!Sx4X3.o;$tg:Sv#1(:,]17E&_)L0:cZw3F$qZao?A}oL8>Mle-j]Ux[p4]QD>z$t.,eVQz2OVSt/r[4~>%,?2ZH1X5I7@ock+#A?OClnE$CM!Or>TvOJ/RKPD!f38iHckVu_<{5lBt-bpvHNi])vJ)B#X=Wno-";
}Ar1-8!pwSqOp-<Oe8i&dKQjS)Sg
JP!7L).QF9M"",Cb%_"t(oiqFj8PY&,NrZhUD>LkPQMEtMxNw/EI3Ed#HB
^hXS+@`?tOXGK5IO&EqL&0;+9H*vz1ICApY]kGTim>b-!BqjPUxmT#aayH0Q}4sl+`,47uC,4<iUk%J4=WsOtL^@{xd""';case'el':return'&h_Ks6LD)*60|Vx%#T@C]EJdJu~+m<"!C#L6n8&G>,
vKHV>wDI2{`(Rf6JwgJb&c<)wt65IWjkBg0aM-W
]E<EX4Su2vRarqkGb7?HQnyt_r1Mx`ktWhZA5B;=HFtrQb56J<q&kGQbjoy:OE5C5yBv=mCPt%]mGvsFH:$!r/#8&5PeHb/h%_MRKK$#ETPgm}c9z(ca<JIBDcy$%d+njgl*G~!%HGHIg|Utc|PJAxL-w;+_?DsT-tCYrqe#=B06%Bhfo"ygP&G*dUn+K8EEF`ij%u/{XLO0*=ux>xX`u)Fd(3w3X_*oa#QX?7(!WI[
geIQOod&-^4ve+6jVo.7-R,"1_B7j%nZ[?eF6dH_t
3ggPFeCEB~$&o.hghM3!UT%Q>"w{J%q3t,8TMG<VBc6e=pu8oXTzodt,?G+:n((x`Bqf@"XHQml>Qn0.<>T|n:=tS`qEt9ayFO>d(_.RS:eT/+J`TZbk-T$eTfl"=+fv4vj!Dc2cu8bk6PkcC=nC!@SQONu+bRdMbe#"j_t7!-A[JA<C!E5!T$pIe%qIXqn05QLXBa/*oA]_G)3P5{(Jwk+9/zoaB<@s5`5dv0w+owPn;-VMgAGrhPU$v4f`vO`-?Ch&@sjbswFj^Pl*Ts#RR7mzoD[a4obmZ+$a(d[h&?D9!O@[LX_I(/*[[D`tU@TzxY-K=1l5$/QE</!0*DJpU`QgTF8Ohb5NOBiGx(g|2cqLP.vGfbBBs#C)_z.c7#mRNs2Q,Y?X*091n83,F$+n<n0=)}]lq%msu!&!.(<&EmT>7*RG4kg%NX*N1w,Px5IYj`^
1)-}%93M%@yS>;b#1fftJk:96nA$"5oj!4&WrVk$/WA3XT00c/FyF|+m=^yt-T?.l.a1SiOw;#D*Xuxa^W3wlh_FsVa?A>0xVmWrl{a#wO,lkQTvSXxZJ(#9QU/C*ZMS"NJ~!O1?`oFw!k6lPDnpGu.rAp%K(kTgF;&NGxfw>M2bex^pS(Q#N3*-:`mLpXXYF=HugzWLcDM3AN$S<*8,u}eZ6C5kJ|s?%4y$g
_<:Z(37QVx,h3_^z/61Sv|cP`N&jnX02Kj5,[bAPX>*-akf)Qi/yX6B8p<8c/lQ|kZD#e&X}6q5ahX`JZ+hHf)kImuF32!_>S,.OH:I80ew*>W@vjeT~s`)nmu[8g5_L/I,>#{.)uv_^rx-AKjV?T^k_gm<A1DRl-M*ZU!_Gpagt34jw_rCrKnRJhloTsSI*L)BJq=#Wp*.j,Rw/X|?_IATtu9J;Q,xgJlw[rVJsgr@EU(PhOrC}L=MTS)RK,R@6V8*RStm/tJx*Auh[6N6IK.hM=[2()m+5wJfNfnB;"-@`9>VG^oks6}WQulB5=iS:#MyEZ.Rzpem61cn1.bkTDjjx)T+bZ|4:CS&;kkP>IlvzxIjYm(ck_KB/G
;%XrEAI{P<T)KD$GEDY**)vfL/y~=:La<ql,2P8e.3<KP/eeMLEN)sgfs^5$T,X_8
tlfK1
Gj@0>4b1aWTG4FP{I~+zVRP}w!#![<:py2RUA?+.@4S+d1AR;+S4)LPlIXCoIpYUuz!M?>AP=^t^f#(%7v[Et)"OKUa
R-r-,Cws;!=KX/n8iHP(,~kur?08=7x^(Cl|w#l|uu4k]R<_6HA)9RI1(NQd_OY14}4ltg.~X@m{L5bZoKqa>-tJm#>Bxp
oSd2::R^LwvnNc

=yEtaus+PduniVKygG/SR7@o"J<E:i[P<"_pmM6]J<%,|*<YWwe9`_Zpy.,qQ"S3RLWm55wE8ypc2BHV8[EPev4HZAa4MvR:0(r1>Nj@p0peya3)sZA=B^|=97.qsl~r
ZZ$OOGfhZz<D^}Ra4VNobn!pN96exaeY7z[+4RPN(d-yBi%;WJ2|UWL"Sh(5+J
&M;Ntwo]k^~XY->K]YVPbdkbV-Vuu:#D+uIU<,Wg$3/SZQqDxsGE4-g&CHz9Ew5ihyU-by6UTsyTRGlP2L&7f%xm
Pm-7c{0NpF
!(},z]BQ9X{3z`j4Krl
<G4cskMA%$ge=&>"1dxl2-ed:5S$OsYx$J:#3byNQ)OyrF&%[RFkUf|ABa!?:DLW+4Lqh!-gCYIA{^[8hbg%{_0B?/Pj51oqSP(aWtL`^C9CQve]>9o#{.T
ph7Q!(N
>1PBR()H%C2%P#W3%U%%}WhR8@P@G2);|>|[7kY(75aBbX-?noBP[bo]@,eFjaTPW_nIxpJhzxSV%jaEYX!_;60)T
cleiw<o8Isk/>)N]un+hM-OP*Oj4{&cx#tj=y<~fDGWQw
,#Brt_+[osyf)?nW#3[Wm51ElejS[b$#*E):NLk-Sa2B@2S>9EplWvF)hGt/o7A
S6"dQ+6Zo^pxNqz;8e}gch$Kz#>@GG@LKwA;MP0b1`=;EeA5UpZj*j@$cu3`b,.SAm>9BH{_U"4hb"*AI5G-O<d&XEB;D&Z[n+B2Yg#TD4ys)h;%H;y?)PSQsGRE"VOj1,6yi/u@zFp+iMZk."8Jw]WtXaj?IU[**V<!c6.xPYV-.pNo)cvZ^jEx[XG8PbsN?Me?5)%-jx#F[NgBFD-D)SOP05bO1<&E>w&6l[%Xmh?
(KzAaJn0SJu=+Px!Si*PIo#HjeD?Zhd9.Yw>kwEK_jK"`0Eop=?*xTe&e"o1Q$QKOxM@J
2&:^|)xQ`"5@J&]CP7d@^xjaE5aOsJo2Y[26G*Oge+6`P&YLdOl8:v#lq@;x*#VRLo-eg2xXD[lK5pS"
[idsZx7m_xD"jy,?fz1iB-SU&u`D6q<~u2p%cg98GtFhg53$VvSanaAK7_#4)GUVAT_j(#NK6S7V_OfLW3:MU^_hGUX,><nw*/TQ<>33p;
-J?ImvB$%m@g7rVnOO7CKA?oEJ$/g3dmZt,#EZAtCKgVJwt5JJ#1Zk{V|JtEjAw&_vhg<:BdG5MxZW.K;[x(zviZ`a_pBfbyMkas`e+=Ga^x9@.;DDmeujEcnA(IZ^2.
Cnv[A18P.Cw7^d`iF{uqJp/cqzbi0t;SRgvrAuVPHgC5vzD#T~bpC@AK(MFi*MghOf!q[HE!.NpT885rlx"n*]HJR~t`.(nLH2n(u$>f<y)YIcHS?TiY2Cbcq*D@kYo6DGooI]O6nsQ9C
<r>cgYVxO]VMGwsT40rF+{aP(a1f,B`Erj?X?AE#F1N?yL:lGHhj`?[x(RR":N7uM`o65Mi/(JF8t.nb5$T:yCKIqd;unlyrkaULF[W&^^32T0URuNQrpBSvRdZ51VM[5;=NPl8L3r`ubCI7LZE/"kaSo0NEN&(,t|"0O"GZg9rE>K:J+#h+-~-:lgvpQDGR!l%!g;i,$0`5h*TiZAFS]|2q4EJ#X_T&K*w1Tm7,R%9qO:CeD
Y:u
B6?CsN
X<^VVbVF8-zp,@v,PUC4b_DA[NjAoJ(g0NVt.4l=K]4ivOa<|ay)eXVh41Tj2D>l6<h?v"J6`k@lVNsT1Lm!G^0I!l)aT%6VeY9!oB`^H*{]31Wc%c9)-W}R6+ynB!^Qm]%;nZqaeiX#ur5w)n_wt?$G?%@i^j7qY,PoNyhYx,*>{Ez`Dg1spJPEpg,AR)&4LD{:,m}J"aUpqOE@Z%l$Rf4,uq?1<GgCc.:X>O%+~3.yN
du#Xh.8ZM7Pn9DMrPFaIE!K5T%yOqLIO
gL`UZ9(Fgu^1rKVFn!>^okA$i9&F#b`E4gC.C@
74aY`J/m=]3Rgk)BDhm1wt$^v%dh8d/ipG(?__{:t;T)esN6y2g,is;V)kGb=lu;vFC*{V7qA6A#_t9H_[gLL:9M:vhe7OSS<tix{j.P!D;e7"WX9hmv)O9Xr;Rm9HHOKEj$G2VPL$uXo*
`9@jAC&GhF%=)aRWWVs.+]F1punZ&=5qn<FV__P!(E+~t38k,mY=.&3}c[ZO1qTk$G<kiw2/S5%)K/.kOz_}[8#XSE>I/6P
`{N|`$F
%JnqXct}oad="#kUt}>x-qM!D$8:K(8}9<q1mm]]JoHU3$*R78BE5<>X]=-<1-]uE=jO0}4Jcd$sB&ug._dF]<Pl1T$pCp77%Y04elK"3Orau&ddBq+HNB;/0!K%e1Fnh&8abg<;K`^%pWOA>FJ;HIBYt+$iWQHCeG1)/+::qIDh(>;9`.veh-WeW%rt0}+o.a/ikn#
j36,F]qGAIwptP%$q0fwf94-j#$v@@ERW3on@Z`(I_D=Ok=X&d9[Tg!w
:kz^VJ-I*<K%edPc89/;qcce4b=:@;pCeORv7^xF%]tG|s(@MjKRbhvi%O-5IiN@b4F?10LtUNA&QUcIsKY5j"fK[7*p2DAo3S^T;$/oRnS^oP_:Q3
r+7u([wHs"ffM
jF1U9>eh^0w%G$B"%X7n_;+iXCk(hzH.bcEPrE(EG$(fQtT/,Bm_SgV_rjiseFjQ?3*FQOd77eU&-jNS3jnkFm:`6gk8K[dYK}!H0&#z8Dw)8O:/=!xB^:Q[Eb
;P@%JI?Zty<TqOlv4e{X6%VCX`e-w$")<=w)`i*%t#A;
U[RN4=5$CB+$piD46)R=>K_Md1b`ib>EX;&IT"L|%$!;/aHC3~;Ie
!?paskIU?Z=X:KK=?sHd54p2M%2tenZ/+?<t`
LR5~a0#5kzdQB:U%EZQr#qEb:_B(5vB
+[6R7Y*Rt]qP],An$`j9fLSGSg0q<NBv))2hZ4*QA1/tnnykOCx{8/YV:)]rKhV_YFyHB%U|1(g4?!4eQ~cIA(.;cd,ZU[8YUp(iq<m2Y")Xl~G"c#h(Ded5;%q#D87W(o;)f=2mWg<#_.xl>6V6sOAf
QW*<*3UngHzY]1>Hjp2ZaLDf&ibQ,v/p6G6X)df"_89Adkp1C%hXTa9KX6!j-<XuVU#"TrfD&htRBv>[06O
[<w055?pPY2w?Y__l.RO=%};:1j9gqkEHCx-B*YY>Ig6TVbFy1D/>p9MpYZ0iU%;-Jvm5!_EP&VfN4m0A/n?QE+B(.<Ul/d?bJ-6iH?j(rZJgA`lq8kX_L.]fiCr*xgKC)&C&gVB&h%x?YGV@m1U(7@n@r&fW.
Y}!qZV<?0G,SwaE9*Tc;`;Af8/CZLUDWVV^E`9ls,}>wxN0[0@adMnCO@KSq6fTmWRk&bHd#nl=+=U^Fby9e9Cd5C*>kCzVn/S$euK9~m=*q;2J,L`E6D[FM[*!FKVU+hY+B&3StnejeyQ#rQyNmh
7+l+j3U104,xxfOk3.*sl6LgBu2]=2<vE*i/.O0IrD-.HTN.S4xK+u`5fB+Ub`uf@+WF2V@+hpgd
8j?
r0b+atO_4YOCM!{TF"EvXilVphs58+~N1Sv9hp8Z~Qz>Ny~EVfn^u;I+@&<Twmz]ufArf3[kFgYaHg-lJ>y(
kiCyj$DK3
htbd>#Q_m4tI0NYNf<wp"0^{7%Bome/5iMcn8QtZg:Z(+LHo;Asnh/]+K&Ire&x[U[[okj1x]E#_0YG*$xW}v]kA[L@2Noq-+jE)!_WQ<,]A$OqV!tdA/*++hZtow6wb:%N70IJyOekpw?6IDuIT=L;1*Z6Npdn%(]raml[]7H<xc6N@$#UhSy
mjS[?Iyp.3<(-PZR7Rxks:(dP,iJ4<8>E*rpiiQo
C">:BCPq9vFkAWH,2AGhg09
cOK694D*rCE.7dt7N6';case'en':return'&X/1dbomt,|?z"**gW&=Jb|mps8o{eeq=u&0F/zL@K226UbLrLDQm1&0Cv8y],z<OVb0j>L=aNi1sq)*HPjMk3nv<90?8Z,:oTpme>Y7s,Qm^5a1xw(e,V,oQJV=Mf
Fkr&OFk7?H>B?Rph?|2q(7>F32iHY[
ebP]ZLN
DCRY0gqm7(]R[FgnNQxg|L=JX34E_If+?4%kAjoD~*p4mcu.76*Y>s"?NA?)fw5y3jM<8^Sh3L?GwjhD[FztI38uZmzE*[ab_/@w"92x^yqH=(dqTeg!_T3,#KmL|uWo28ZbHYt[5va6]bBeqnDA01|23N17/kpav3LRyl+C{lh+XmJU0@Y]7/H_lqvB:Occc@C,Qqt
)c;Z{0{=+^_+LJCrVTOQ0j.e3CE<v4,TbipkNN,Lx<}x|troDA%4@+
&}4$
+x,kh$i,V?;?Z[=`wCJakN?npf?fY4w931:o*gXg#%fdorCFu2*0_ZNOpL/.<d`TOcr8{>#$Y#*T~l*@1f?
@n`c[)0^BS3DRcOffXmz$DugT#D0D^J<jLfS,*`m9Gv!)fu>~G]r[#RJi<O6PwPyYl9Rc9m2XS"nf*~sYUM;I]`a%e:l/eED]GVIvvX!2[ANYq6w6<vw(!sT45rK+$OMs:?C@`:=<
sky/EIT+UuS%#M@g?JvJ^0zFtCX_"<tFeX<P=n-:C%%K:ka<cfQ::sK0E[.u+y`4lsINs62D#[/
NqJ?b_![DD5H}RKjU1_;{Bra3yd^7`C:=!qB@kigg*5W9Oa_73u].Jb=N?c1&q5MTKd,V_^MmsDA#e9!:41
#59MI&g<mn)0XVZ-*;GmL[9mCl6f;21KJa;-I;~yndW/)r^hZ5x3;5bLBv{CnMu94AK-"7|i04E_xGpH
j):ebvLbE`w3(ZUs?7FG$Vb
GY`j].kw;!A8<8.(^823b=d).~]H(/QN1(0$xVu&isa8(2u;8XOhUB78"]fnf`tyH1b,Z]o_,8
!c<uUM:){p)n?=6R+H.@ZQoJ@HTHj3&-2>H2~vm[i>qmvS6g}sg[{c]!6RhrUQ7ZDho[aw@?h`$yBax$~yGF@T$N3U:+MFY9nFZnTSr.}(t$<tJ`EsSwp(wc|$`rc(?fb,Nr4i|UNTP5gQ+F
Erqxh8AAu.L=&f
/IZ5V(]b0MryVEZN%tpz)v:v]x-)O.[!h.6`@8w"e),<6yimaPvSOvv.QUs-^fY^f7@wHkrbea/@4GCknK1_VHT]YW?,9La8+er$~@
m:;SZ+U-OMtgSV:#IX*LMqJYy`VYZP_Zpt9eJ_&Q;.I7jzIEGjIC$@ON"sjgkC"bGA"2QvD:&JJ0^
gmG_A:Xf7t$r*:2AX&J7he$]c~VbiLdO$drVV@!NREuMU*T{/m
wmK6nZ<Z9oBtAw!ao;q-jua74__+HTb%%Y6HN0(Ud_E#jDH$PW^tPR1cAIa8z
+`}.==Dld6@Mm7}3k:m71ghz"=-F%!M9C^]Ak"#aKVU8zgzJ.*FA#4/*R<~pRbRgU)U8]*>m*/~[f0+9HftPJw[gJqbTRT+08[#Og+b..O+_KA::!3F`@C>+cihQ>JhT
Rl]V&"KIk?sAWk+Hv?!U`@VB`LNn>>G:Wia^_*ypuk.6_1HT.V+K[q/)PB&jC`k2/A^Ckh"DtjiC>Q4G8u14;R6U$)Lz0>./PK#2N(N5CruT/;k!!,ChR!vRc3#vf9<p!53(kXDPX7aOmn(ueuC.U#BGrrO(#FQ]=J#(9L:b!z],ByhPtc=9hmc9_XmkjygFk4Bo6?76q]hb
uiSv{Bz9IMI=sQ
v^W%UNjSHXhdeXHob;DtKB&c%"go<W#=5+B`:kDjPwBsQwl-?4w?EY6=<KZ5mY-c0%W@iVE,!qOifCeo8_dNf&%@kZ=}N,gKT>!A,XQdL"[X,Dg<x#+.+U*YP21.533>dMfun/N2Y>!NZ$D,SZ$hqqr=T>vR%Zh&d#4X/nuDNJsC^RXo@R#YQ>vQl$Qh#HKLQu8r8Tg(!VM=o5>jQ)$ptb,050`]m4yTh(HY6bo_#a:i+M+sy#:HPxtaL("_(B;,u!5]RW[$Sv=Vcs`Opb"{7d4?HK3t&OTTSc;mnLu,9C2~K"!eVij`+H/S92qAHNb<TJM[1kgjfmaB#W-?9<mtp%MZ*Stn%sGpbE+%j@Q|wU08!iRMe9^l$W);rk;"b-rauKvr0aG1y&IU
Qcy#"3=ce1kv[6p,m#,3+h]+Fs4t(_Cfo7M*!:UV<o>0x_5HhX#,A*><nD:rIutfzOd&xP*ek6`<?WW8B#Nkt3F3hv5[}WVJ$D"tECugyTqh;a9+ixs/:tzw_)&OK@"(?tDeBn0hLQJo,*,]mh|Tcg,Z)?(5^$Q&p3)e.T&3-trJ[!Y^iCKX<.!1Z/mh:cN,7<%Y6/|=Fk1(IMe6!=n-v8uLh;BLkH1
d6}a}une;vHljW}gy_Mt;@ro<;3?Rs,)fRlUcO!VA;]Q<D#[YM
r9$0FWu;]@njIc9YUuZ^DdL))@Ez;/aHhOy1CLUP,<+nsO$osD`<xi>0Z>MMLr],-_;bq.,D[h"u="SvLWq_8GD30?ln1u4_d-hX5{0AEoqS>g9<FLC-bU2+fo,`?/rM_?uY2L;W
SfYN+s.iH(6@t4F0=p%=jPQtU7U0WGP=p.TX1GyO:BIlkNf05l2NPBH@ah_=Tej?0gU;?ec&mlP"IXPLP;X>Zw{">=a.ksCw{.[#^sW@*qji3Z9T#xXVBdG&6?tk&0&t"muEhE}no.x9Y&JtjhexB4Yr5ktnl2~2%1.N_%9[pggI[^YuUGw>RpFO0lgaGhV_Tr*%nbSy!k,1=M|:9=V6l&$r3]jm+Pbn%"HB0Dda33TDNxuRu`{7U$U_H[viASFew)UsCb5Izq9Gl4%>ylG#M8B]<f`M8h.t=,n)*klP,n:@hcQrY#h!4s4M/.RjeJ/n]>CX52>>LqjnR>S2E@w%u9Jp&,Qw#B|krkvD&L5A*-sNFMz@#tR1-
V4km_Ga,]k?1nnz)m=M6A2->9y6?)c.m~j!HL3j_lA(bX0k_:*3eQXtbCfy5`5@y"]Ws]N5&-ud%J8>@OkJaa3mZ7ySG*pP"]g9yrH^uww99e#/@DLRk!S?k;[SX|dWiXka7!as6bmr%ZJ.(/;$[%I8B?cj(RBjq77V._BJ)
k+X.YO3
[6e`G1Tmwh[_rwS!3C@;>$7^&JIsMR76g,ge2EVxA^k0[%qpFUB
gfq,8kjs](MUiOLO6|VE9L"KUKY[k70SKVutbC;KSoYo`og;L/&mN6';case'es':return'%`GF<7npM,|?z-10jR:%@/zA8JZKzQ?BzP*6l8r+_.A<8Cm+.>SR)Shu?LyYhfj)Twc9JrCk;x;j{h|IyGC(B,.k{t-k;Xf^+ycaF_YP`NY4UPBIhXg_H6*QL
wqfKAm0s2nv^/u`d8X~xDQJExZbh(0"6q&5^(gxs]kg9V(9:_sAbQpC[;Hkt`2,
l.14RlEC*ciI|hAXVwJx.yVG$5j>>6Vy5hPr|60hRUuO|#eKD0qWG,_khO7J5`6p&.{ajVgXqtrRdEAQr0HX-;V85_YhT/,.wTp6{,8_VUEv&TC6mVc9))^Y4C`&-k;V2kDYDe*Z!L3%p^a/p8;rm(.g.hxt<4-`D>jA6(,#Sa%[In&5iBcc/RqdDTE#O-3"vBZg,2^R`&1oUV1e-S1@C]xA0QgygTA[]-#5z7av7`WGd_!R[i}WqM`bm<"x"J-)91}`MCtp}g@[i8,.~sStJ+r*bO<d$y%bObJmD7;--/vb55"[(a~mEA-9m5LeP#AY(1zgT,1(cPl;dn-EaV%.P*PHAD@q
r{yxBt!bmb5g`ht=HAOed=$emc5sXSjx-z.[Nqc2u1&{#@Gl6]o>xr/R`-((u(S(/isomJnrMaoGxvf??Yx]_t4kDN*{DqT6l5wrEme!:h3B:@*JFRKd%MPA2G&/K`UGf@[C4m)-[!TkG[o+3O=bity0u)_j(^:Dns+n7L(@Ptj/]b`85fN^+Dwg>.R<Fz!*19??d^hLl7%B.x=VRnu6XfJ,#T;Otqdz;}/E]%5}WC?2nacR;T3)$H=Oecp2)VU8K@M3MS:W9Lj3+uj)I-qJG?s1YB8K]NR+Q_vg614!S8YIsQPR8?]4(Z#]6y@s$JT=V-0].D%l)JxdnT%6a*]9-m^(fVhZs{$ZpRdWd/+KOy.}*3N;m1)qB27%W.$
J%BO/BJS#wj,48iI+A;3BoQ%czV:"knkTN&Ot|_ZU=O&wW>}RHHQHKrZdMwH_[,.ev[3s$=p%`73j^_OsN2RLSMGZPw)hpl:tD+>abGVDrLtbP9Tm:XDfvvYd]7c=oiEsAsCoWWI=%iI?5=>JirX_+*-tYPG0,.kQ`PT=N<9Y}1S2mny/w)pk$&J>Zpw,0ofgff~7I2QHir;JjW8Cyp!PRxOF=wN8Ioy5O4G2Bd@i,>EG79p=V&n7#nqPziaY^dNeteJmk3dkX8xVGr)ty+LDkEB
BX4T>3~"@ccha,o+_:-b3CW<GYg$[n?yhP{"=1+hB$+Eo)
8hLq-bTO!KxQm>;{0m1[tpyH:4sq<UkVM"X#SI[Ts*I!aIVE,WU}tA8=7wmNyDC*?wEM
E-t,fMzqz$/ing=4ZtYqUuW$FH*hgS19PSC?eu!+?7j*=P`"1Hia,@X*RwH^S8csVFcsHM1
N63QNac=TrKt4L}NVCZK1$O=a#Q#+#)BA[vyF#MJi50mW3N#SksG{jiS]s}Hq^x)f#R
Rhydcv]@*0c.TqG`lmnZE0^%B&;F{EWE9]$!#Jyh}.Y5g^F2#&a>k6F
FJ=I.HyquSmv"ZmkepM?@z!f`A`fMQLw-:PL!a[PNXE<#n#/Z-;40jqOSWMPWDo(C#eRx8[vAfY_~rW92
OFv%@8)j8qdPh,DI7h+kl7`6vq.T]N
$0h;%7VX
y-/oD6RIUeBG9b0TCvD
C9VKb={(:weS3g*$?HPWbV:@+`FFV"by)#diwY[<&wEuZOovTx6Q5d8RX.WIyOYGH
5B9gAG)mjj>/Sa8&{TDtT"0A?w)P0a.mAc!
I;v*j.hjH8C&<st2NbMKxou5WNa]I4<D
wuHL#@x&m{l:vs&b*qG,uuwUj6o<,z"v:Bj36:4
E_ME&%2mxs>K,8:>Nx[fT9z%Qf
X9DQNl?8^xygr5rv30E_>-!vBNCy3N5Z2Ry]NTdilZH!JQs"mm]0/"Gs:=@wwK.iC#3.ht8#7:Yhd(N4^&3YF%"dt7gAo:oB
a_sN$)WfFE!h[lW}
*fro,V|<hsx[b3R8dC9p_T0h28O(.CK,:(p[|.2y{u^u4!1t+*
9@1})-g"5QKjj3QE
l]gHMr{DEQ=jsm=#x,hCOe3rdV/hlM{sdf+
|LL3{53g@Lx0BnMOI0k*9uj=Dp<#ZiU_q_Y)o:TGJYQgC`c;l53q3#iRmLZ/2<{CH?I0$5,iaL&]RKfgV6]$}f$f?J1X+tW_C5oM|l)r}A8bZmMmslHRT9#Vl*KGdr^u=ytg%*IXL;<*%>Lo3O}C{k=Odu-BKyTXX+<8DG#L;HwH*Yl^BwAm44:uow,t.`"9Gv8O=p:A):74sL_Gwak$m`wZP@W)>2#`PH+2_2mq@B
r|n:"YcT0L49cB:g^,Fj7HJ2&76[y07@N69*,fHW?UM=%!95E__PM5a7yaRi$xE
#cmha)o<:/V(,Xo`tc5$&PXDVP:6=2wk)xNrHQ:;f+oxQ_U<=SCZ*$U0a.9p
|kYE*V&Qi*Mg8-JqJub5*LFO3bS3tBXK|P}p#y^__5h=*=UPE!b#="n0zU6Z+Kf+"G_A<qMvlO^>#@9x)>4r{et64O66+GTAwwC2Z
?=HEdU(;hgqp_/i.Dk@64Djo%J
!`dhp(k7Jl8hH:$QLyo3BUSr^,b5IvsD]hR5qaU->EC&$xpuK~r3UP-Kr<d@D,g@@vcsU(;Mle`98e#^<OEs^*R6Tok4>Q2mVL1WEKVia9kjdZ
9LutaCtha_{XZEbnM$IVIc)h;_Qhp,38adLnJNSIPaMS.L4BMqU?uw=OZry`_#J?7=FV.Q_dBw!tIkRhBh.12AWZr?ZF4Dirm)A[plzWpTG/Znf@6Yn
c#gayh>5gm#>G`ZLxo
0u86dLTTPyg[LdeqqNsIH1+ICK,<aiMvW9`KxOY-4w<-2tH?JoQ9f3I}jW%yZ~_EJW"$NS9Mc7rl,^m>%I.F*jQLM3,^IU)BkpU^bFKX9A[Qje7&WpP&]E2$iXgcZqoE+[B"NOTm0n3_?NHox!mw5l@@<[7s#etSxI#!ea]T7Xth]RDLJ+mS4u"3?.ej^_6yeY7Za)w1a"%GKp*rVOds%w=@l!kg<I[I_SQux{;5aMQ.%5Z
_W]cXt`?(/){f>?)KqQg]SPW-R6*fSdY?,A-e*mP=()3Kp<LkzeNe+`zw$?>TF
c`*LdIdIAp`?=#Hd~#.A2RM*M@:uo9XPQhO:z53!@dsr-Q__M!X;qUuJH6pvikg
;G~0#cI>~Bsc})0U<q!+0(l>&8N(Li
!7N.2O`lD2Q8.[_LPS>a&tA{VORDh!flh4X7Qp4m.SA7=o,bq,bKQ5A|;>cc+ub-;YVqg9794GhZyO6%PA"1xJfaBu,;Y>M_=tp:o
nyc6ncW}axqRDT[+o`_m*.TH@cKnD6<i)(.lIsdbao3~3VG&NQ3(nJUA7h@50o(e]8Kv
]9q
kxWv}^!.m99k)Daf|&)IFVO+aw`X~=u9sg>7uef:7WGlae_Z`6#"V<B5_&f(?$HXJ6`Pg&7U{sf*O33d9SB)wph0CC]%D#v-7WBf#M2*OeQG.nyEX
S6AqG[dYtl{JA/Cth8s@ziVicN"o_1m:~FH31TVi?V]e119#|orVioA<8jL8(hHP{S)a97IVCRSjIv0B%Lt%J>=wNk2)HQ|9W>DY3-wlG"^<R9<jj=RMoS1Z][Pn/!VeijXsDQ=T$_/^p=GVxXT31Z#.AnL53Y/UzR6pf*gfmOgtvKhvvIFxWE&$%,GE_ESV>rPK/H]Pcd#;D%:r360s)?RwSTZa"yF+81`-gfKM*.?874=;,"K4dcR-hPw2j[WFCqwOGlAh%3H.DI7]Je@31TNkNXE**FiRaf3QBI.5b<"VbZPaoj"IYTC_YoQi%<H1qamf6$W=Qpo,jp^VQAcW~k{gL*T.c^@NQ`G$bY!K#"lRgdcoMy)Fwp4e,QUnY(=iyn~W)YeI2OsuBu0d}bsn|s^HiY]x(:(^CU_FC1M*AM/t=CS22.7RUilOd!5]UZ/LB+%J0l8CzIWU3f;xb)aov"W9YME?_[a4ANqSxgM.W"ZoK2-iAhvF":>4cUD:2&gY9^/>)qy8(y`cB76m+LvTqVCY"dCwrTxj#X|7e`bScag:x0m(tU)la-G-ptcA1vz*+Zb)!QBcL"Dp{KlB7]+m`FF
dMH?,m4`&di2aZo)]
t({t/akv0g`im$"/%Q(e!cTBhii4<?[.{M{EDKe,xI+Z~`N1h5ZwRx0!F@vKqBDD,sGnGMAnc6Dy2Bx41D$]:,@UFyU(H.w
<YI=5=,t;%aTT9Rnuj:^QM8ufrlCP:Se0uB@~$[H==F<Uk!`63kLk?_H|(#-}p]D:jQn4NOnKXPj8qrER^!I",o+h1ESNp|I+C*-d`t)wlsi}[L:uvdyzbInqWM0}J
oUs7/l5z,{LKmC<+AwhoO4/&%gs%e*lKCdubQ#?:&,e$uk"M?AB9I~2gAWFJOpC<,y&VibjddRA%6-89k[QPL+;4g-3|S6^5d0';case'et':return'#X/Ks6KpM*60dY/0r"CpN;V.Toso)V{rM`vyND/!mPn$D<YF`7c%0J%6vh-O82ho/a`!7coLU@+E5>OWl""e|l[hiB-](K7cZuzH2=Jce:5^kKirZb4RF^XqGP>`4e;51Q;aQXzcWn|$y;Hjo-p`XnKb9?H)LkO3y
w>Hl;GgmjH(P7Rmc+EuyD1##;5BjhYAlu
0,V"zE1)"_iY:60n=Wvt*J2
5mLM%%/!r3NxMy
UZrn.|!OXKUtRF:V
(%{Kty#kHI;?f04T<uLZlfVJh%duANHFW5RC|2_pP5,-4@@VA46uOx!F)u(<QZ~fYP,a;At5z)1F}+0mL2
.qpDHO.flL]j(vf(,:i>^ceQHosJ=sm2Ai)Kp/%IWdoZn:$,(q]LS]6`xA[AJ)q9O/"a&@x.h9#Bo3Un,K+L7v85?"G
DYj>RoAl>.U[OlM#[dWF>qrd^7,hA|
amfg:4)Tj
(#r5bY>vlWb@$x_i!D1a3^+4L">PGffGde!n)LaLxW{Rjru1G:v8/>mfpx9vw_KN3`%l-25e#(!sI;yU>
[0gc[PALLcBHZ"L>9ymha`w>2).d6U:)570)8WslNOXv&fa**[3OZ5pv%S&#"^Rpk"0=g@_`S!s]HJ+<M.dT1c]2@U4Bm(Ow6@*yvC98tnBtWF#[Xw[9>qfCbTT$r@7s2Kvbayg%~92F~GXP/2c3z0yky,l!D]G0Ba.ytnD
.e);y
_Yhey,jm}#aGeG<$3.}cvVvY;ga0oD,(kpH6%ePGJdxH4+o]0IQ3Vi=oTh(0"f}J&U}g:fb)A?#jVR(aP4`ty$IIa9=f4Jb2dL;3`c@YJ1JfJQ#g"nNcnXyWuQjE:/Z*2hn[HUh/p:E7.iHNiQ9xdlsy}W`@,Qr983P
ve2$[+X/,51XXH}MpZ,3(*Rj<4)i0HKLGWfo3Kxq4kPPze(%RZq(Itv3h%)x*CCTr4qf$q[<ldFEOG|rf(2EAWogLK*3-7<Z1eRW0Yde8lcOqpLj!v3[g,tj=sFcpMqS?XULPMFEMy4Hw;A:an}S
sSRC-
h
&YO5l]yTTUlD*xR;)QCBK~__t/^%Idk:a)L^nfe(*_`_Me_.pdG
UBhThU)0WgcNyrwr<E8y&q@ZxPC&7/;c!M5kY_yS[XXWs|/"sTu8^D/G_C/(.9u5H$c*]5k3iym`D@Akp3RRGhAVE71-&??e<48m.=G
z)l
C1*<u,kcK"Y=F~oL]HD"gK[4;gk8OxNX[x%AH_Evx1T=sIds#bL9gb1K-ab|FUT.KB"vlESjZ(rbII4.B(Y>3"aR_77dp7i`W$oU#oN^OU@F*ONSnCN@5F<!@QHac
g~T/<$H@fyWwt5(HZxO`BkW-pJfzuGEup</nXo`"u@-"(=ori+LB6Mbo/>7>W9`-T16_J1E"bM6i-=J,
E<7,U&zx:ISgcUl
q?ioc13Z<)cO3:

s9BD,Fq%[+/mxQsrUKHf,2d$+<e6hi`&Nn4)w8@6qA89otTegMvl,D6v5iSH{O|s{ia_%0:^U"Kmp
i/0P1d
I1$&PUr:/;T>5_CjSe#I/d:heLh+Yzc5-Z9M?tI%);*q?aPstH*MA^lF>>/@9m-K8!Y#E
:&
myX8Og+,aAB@GCMNvhW(~v|]XLUeW8q=+fALv,Z*X4xT-0J3/5y48%+iuD$92Gj8.^:dF%lai.uv""H;|=b8+?}`gZ&K}!"t!i6JE+hm57MJ/iPFo7.ln]L!DsArEK"qz481EMoQ}0!jKC)N>o]77p/0Np{C^@mC}tiHocWwB82^Z)L]=.j-l=.Tw45+b&igxdpvnn2l&L)Xg)$+
FpfFSB5Zvl^,jbgPCClYmgNnbPyqEgpTc^P:5
4%>JDj<xy^.%Q<R]*;@D(Gs+gs0o$MF(]1?aeYsi6nyEit
LCmv(WY(T,S"q]$b8H|YYlQ[Hsi9f.;dme)V=eydm]/]y
G?@`)1@Y,<8Z>/r,!Tjs9Q=w^,PIr5SQ>BtJ{*
J7Z%8&?rSA<OOe!O%#AiO12eTu*#tkSFB{>TxuH{I),2qYExgmpQP0sHh_hY#rl-M9Jm$:iq9SjhfJn71@Jj<5
QFOFQA5M!UDl~%Y3Bvxg)T}OS,6qxj<hwMTX*a80cAcMolJA.)/w[!k<U("<RE38f&c.}P452!~dz9]JSq~ZjNkM_NU[@+MOI?~:P#edO?|3:$1#g=7M3["qukRp1EV/O[/BkKFV
%HE:+e9;npe~6!O.kkX+!B<!*9YS^{N<NF99/K4%;yIqdR
rtIJUT)[ZTqbv;0^,H4P;$7>!=
30/B8~W/;GsFVId
/#)PB]piRk#1e.(S)b85)T_i(/YRd0t`oN%u^(x!qS#M8XtU0N_Z1^U,G{fp>Dx]bAbNvg;a?q1VS7.`5;=p@3Sh.Tgp`s?E2|bj&I*/RK.hqiSf3;c&Et6FKA>fBwGw-#o(4(:+Uzr7.AeW2HOdMNNP"Cb/(|7gkxAT69!rS:xY%,PxZ!.z,Hwy#i!tydHDZX-~Dq"6COYc[_!5U+;_tWCx^(VPWklyf0"o@8)O$
!)#qGPH*
n.=P>nzSQt&+`J5e0T5lDGpv4"bM<fatC[N<
OGVzWll?2^y1@i^[H!87uqITsHRd%$[vuF3Q4T8~)z;/-Oo&4$mJlv?V+(gg`:[}e2;`^/oxx|O/Gt@OSbnLxf)Xh7:qKCEh[V(2j;UiL%#$DKK]cjg+2m(=U77NUH
JD^%%#R1sZ]`y%h`>lMl,OC6ID?U,3xP`MV%yA4a(vp=l`R=q_MmHi0CofNxH+VI{G_GD^V+v&F@2m
j,0cXky~ud[cuxj@&>E/6mKiO`#&JU",U/)Y/AVQ.fP&64H+w<qgLuF[3CyEc&37vErX+V)X"OLi:uJ02X?!mgfg`t1:QVT9<IjJoKrX+70)BFR3`$NyF7TsG&QN-Tl8l7WO%<2cvZWdh}f|=HJJP#!vT0Mu5[9>fqimeEaiSLA&?n[7kS_Z`}JRsA*8j[:%uoM|/UjiZesA^)i"Zd#&lNSzW9*jv?iY9%h6l
7Zy>Qa[qPLq8nX%_9sBl`dOYSaqR"&
]QE>?bNf6
yF~%Kcsk9lEdZhz#Ei.po+AbrINuK=22!.#END7s1
.ZX-f%tQsbolb%}w_xE4|*=@c6X%}p_v}$F3YHuGK=5V|x{-qfu/:"W+x2fq?a>..u#CriPU9pu#?<4bGf/4=(pT^4+aZvGc<cg8-x<>*G>li6jdcXl;pAmQ*uJuj7g1_bo<#g)!d3U^G?LDH;DgrcM%j#r!|O5fHnYF3-(9+]<gqgKXOO*rPu0:,6C2aS.[)2XO.&z34/|C/mwhC-V]uqA_q<LBe9(041pZ2pRJ.;=$:$h2z]<GsO&]p"pxc8fi~-`D03nBHCX+&QN<PV5WNhWa!AZI_1TgZbZ1FJTPB#nUKMF"4@$6j$IkyXfHNu|
PApGz[!u
C$0QC2Eu0AR,hUYn@!;0Wfc9N0^)ShZcXc>X2o-,i"tq!Z(mf)^AY?^fT/^PiVO0Xwg>Wj;6OB`>X]]?*j4p2Q:7<^x`CaVc;A(#h9_)7$-<,K-:SAWa`;obSNaN=RD!QQrJ>[Zkn%-e)fE]dw$0=KX_Rh)Y&GEfBvrO
5$NMatIH~xEWMwwJ"#64%bpqQ?f4yeD"BghhjRNy7!"M|7a24d=V7a`Ndym:*0jI{W[Gf%cd[lEM`-&tE%0PBxGZWN9qm4Fn-f0jBN7k5i+!L2F^IUt)7=:=+d917r;yt,3D{iH0{F~nMU|^H!2e#)xIBjlsSb)$WV`"r2JnWdR?}99aR*IAAC!1NmZu/ehP)pTph*h"TV:Ta2eD6ZsE?7*yn)]h<,WU}:csu
|JgnO8}3$+|/cB+k/L7RQ&FSa?|e+9*#txG7C0&S$KCYY!oIC+A:_h}AQ7_KcK1[KKIHD[P&gJ43i-=c6:sit
@"D7g,Swo!`h&4#,yrqC:hJgsRh5}K3M1Itu;:<>9(c+Xb6YSq;h#A}I4XH8B3>7!&Nf?9<ga"0((_DY{?Z]~i_a3h_O_WIk
iysO5CNtL:c)rgaJ+qA`KZ5~oQ&{MK7/ZubF"[;,G]lxpN;q1bm5*(ExCGoLqw^d_u:~,jWDfQ[K+oQ*WIvv`r9l!vpr,="fT/]K;7KqUjbpHI.;O[O
W/QeAq15)%"!yKSX7.+du[0vkWS,R$]f)1Fl_9sjAHixAl]Cu&oED_fi7IaY1[clOilfFvyT%*({)Pw}s$4~T
uZRyLNP*G]?|-Y
e7$%`UpaOTN<,<{jpOGaYZ$u,=fwQZs[^8Gm@B0Jm(<%!g2./?bL
-]w@
zXGA2o|-v_DF^pxt|W5;V2k=1X>-,
0)6BAy&1yL;m!PWy$9z"mKChT1X=&12#9W7W5ku4m4#>eG.%v^PmI8Y0WoB"^A;whe_M[NeQG,n<)u>.CS:AbIS@g(CDa2&aBJr`&Sp!q).[*oQS~nZEk!p2fGxW3(RL^0"AO2Qt^';case'fa':return'%c0;:h%pM+W)/O@#%)?!TY@be7D;}glM@IF+|dI!"0W&)aDN<<Vg-;ZQ
?nsM_i&8V,55B.dzY!lqhtp*r"MlVqIUvi]*o%l=u{37Ovb
ckxav1H"w9clxba|(nq}sYs#j12AMhIrE"6Ca!_KI]?VGka9JquZDlxq&@Cy6Ljg`_"Gd56=_1:XWEUJ0[a7[g]j2MflMZ/
MsJ7b@W>a$GDd(sm5d[A_:dU=9yP,#GZotsT6)^r1oFm)W"I<AXY
F=m55@$$^R4FFe->J`$E~ig-OV8%}w%H@.Zm_8/?Nx{5)`[Vt?Txcx~<!i}hzmZi;R8Lzj:nrgxkhWd[QyvZ~wH"UPSg:XE=CxHapYZ`+B<o"K"M3a,SAXLEnP8nWsgOftu!3RB>IUPY#?_c1Z/Dz6eZU)jgY=p7J;-@zmaN5Tl#y,.Quh4tffVMA6$xIxxfxG|K/+uF!(fHh;
En1P+BrPnV$Zg!vXYz-gJ$v?p-a[Y8i_yHgnQw:(PZWoR2A0[TMxd5kT]5W!&6)+Qp8tLcNRKS
R0];vh#D,u>^x#*Je%aV]eu;JSzyF/<RtI"?K%-iK_k_SQBWN+^$g`e>W.Mc_:rDIo`*5bYQlt{C72,.VA*LUX1!N(?r$A~a`?j.!yJg<

=3UE^{X2v?NOsu:DQ6(9wV;i!7_y?@T":bq3t@9?ZSx[Y/pnIU[_8D
Ek@+Xnc//J4?XyK&X/gcz/Wo1M`gn%@B3N<KBop`m8]njVx*`)2AO/HE)?M&Mg+F2vE6j]_3NyYkm!deQP~N1HI&u9C+W6CIdP_T_R}f!,FQiS)fs@"r[^rv,*n2g)E`Hg
p#ZQXsu8A6kxca_Ob+EvA`J&18$kAA!qK*>@B#rN7pp:q)tI$%h?;WYXvAFhNqKM9AK
j">o#,]$Xy%0UP1*.nsi:k2gcol
,qWV3nwLKJ9/OIQPaxK3oBCBQ:6%;E`eac67De5r5k/L3g($A^eu$Pu1lS:je9$Ua}>:w2)y&elo&Vx2@*%FiQV,wyCfkQaK4NLP1<%PF^qi.c(ErlBWynaaRWl2_4u|1X%n5Mt@L,Po^yk+/7YF9gk^*dh/Zl;kSa.(aG!/
RW<XwZY>46:a$;leX#[W{li0tak
~.TV%kw24v)1>HF>JZE-)u7C9J*]gjDR`
$nT0/Tfy%C581$xT.L5Uj)gB@Hg_F7Ng4n7Exk4Cw@U?Ux!Y`M|gKld(Wsqfkd,D}II2.dkG4YS1eIK?M0BWIwe-A]yQam`:oGqsAF,4i-o,:!BRc>lfVewB
)d9]^NO>`PJBW?`<7padu<Jl0a>F6?qyu((Ml%
dMq99PG"j8dOkFhDhxX.AI-=qt,<5,-XTL{*X+YDE*3f"`rXe&)4a)fW%m(+J4VR~`^=Nq#iqmO8s*5?37;9_4r2$8+paLJ,mqQ4qbVAlqL<n6qGx__+jhMxZ&+m|!:jY9n:Jb>+TYTrwv.BDVv&$`7)sM4@J
4iuYZ-`Y_F#p4Q1eS=P#
;C9Q^"bGklNshU<mrn.1[8t}XcgN-J,1$SQ;#gj"u!DH.61opleP,;h"pX-ofa#484ICW8gwnIgU)("=@6"r@]Zh$KQs<MO{pw!`MPUt5k3My]Vxp_,2B@$bK&y#a/tOqayVPjM2qdxro"l_@i44ii;Hb.(Cc_@q=)ylq1I*^q;*z%/h.n+^U;YjXj)bsB&<r:#Sy;bjrr^^`E9iw-G4`(_C98+m57YC+MYwWey5dL
rf`?cs[KzW/5rLcj.P?<Y59W|As0F[a3kR-Oc$xw$_E@yvJS#ho-bQS7mm;c^YhYwDX7#/)B98H)b;oIa0zn98$ep1_7c#LVw6{=r!n352u;3%uaW^W>OT[S0s~J1-"(n1~?tWU3]C=cxt^?sOMFO8`&`gKEt?h3nt>cudeimQldeC6^i
^($5=3].FA#!UDr8;:`=POdNFP~`Kl{e6$($>?1rz&ebSde<94)V1Q/Gr82%Z8Q={SPaGO(od657#z!;J&GyuoOXU5:5s#fb(:X-hM!1|fSR5+owb_V>]5
neev<bCx&w&2N"Fw.$e?>4_6P{uss0z%&-sq</KD&"OPElIGQ0N&L1+B;:gL/j5G%t1-Dh?>m!1Ya"8H7$l+vvJJYxF.j]W"gA%b];I%i)"oLkO^dt.r4r:!S_7x<bCy4&QmAv56]_Fy%Rbn^gJiR.9B"MOjO$#V8=-JiKJhwL+@T$BMFKBak:sr.Ufq`=_#,KY~9vP
l6>ctguHU0$qX:93qZpQL]&=;Q.sY;"udH9M%7
xl*[iqej;mClG8,gN$g"&[1RC!>*O9d*CsukW]>Ctl`y2g_V87ym1+Hh
;YaK!weUMdJ77lN=t/Zs60p+pBMKibVs5b%.kEVq,K?1g]NBW)2
tD%8*3IQt}-=%z1~9:>yHw(2F#%FF#
MAjYjxGs|+T8{T[6UqE>Mv)>VXu[9wsSj#fvX;T_7?4L?m!h<(cL<m+,
Iw*x_7g(0RXjEOFC+r>UCr:L>PE+.yA7ck>Fb@jf#xxFe`V|f^?dZN^lkc2|JplHswAz+B"@YMvSXqZr,LlWsrsl#/5NGLpffgcfV#FA/qx<i("ZmELQ#>oM#0]tf2Ud=3(*aNH-<1=1P@Xg7$J`(,PwH~7,*a(}s__y_i=up?edr]6"RH8LB(9?I7)f:d=^FWt,ntasi.^K`D7{bJQvB7Fc
;1DEf%+V#+Cj!izX+&Cpc@I<nvl2(J6Q;/p:3a$7fPR)9.JNNl1A?>R@b;73[4vpw%/L"ptnnfYAl98Z6Ah$(B8ciwn;[63(B7)P.fy^r+*;iOja-FT`:VV]n6SYMge
!=Ik}5xP~6|5f66Np0-,u<=6c!F*NcGt<48t.1swNOKUF^TpXR)
_c,[2#Pi`8/cj9`3{Ow)VUX+N$mu&.>h&@R84EPN??iN5)}odf#2T6U[S09PT_{oj,ZnSG_p7,ajAD52W(1PC#p1iY`51T+`6Fy(;y8PW,m8q[?C2L=8X7"+qO4qX6t8mDZ/r1aBDh@pn6i5f(YN@v!3k!`({1O)|lT9tj4krl`eP$q@mE"),h]Ra7?=EQNWs`%E8^nc$H%ljN#*OC6f$%*lJ
QBHM$)(Qs1V4L?|5=o7q"kA5dMqh
2lcqq7E_
=RZ)z.pcU_"x4Q+^i./PZD%A%&/,s4OaDuFGRK+nLG|(aHF!vj{wa8eCiYNc]=Ihq1dP,eGO*F]_<*:0~@$8+#Th*?in]J5VM;eWi+];w;*@#L!4|rW9%
rcJQhf`fbS:l;n4g5t`DYbVPXr,+8cgI5PFW9&|#k1|`n;X!e/Fn/5ShR]e6jeY/{F7NUtg:_=8yZ>EWb^>&>qqllwp5RBnW%R:w<>uAkoBwXrte(_1q3x]w-MdycyS!OF7;KssXxxNj*a-v!9%ny>kXiFeP5W"4Yy*2KKbaD#A#r*1UU=wDQiA%Dz"1=*2GJBkf|^e0}(fsR"_YvE/F&m:(j)VZilQFoFiJ{&llGpF4Dxi%T`M@?&v2N<w`:NRFw]xJir]g8"0*O5e23V/"Yb8X>(<;P9V"ywH&4?CIDYRnX*M2Ps~^Y,=%4ilUDu<2ie4q:v<!0FUOKFJ.HR*P+=tPEF67}sN2t^>x)s.b%[$P+nCYcM{FL
6F"t
tQj2"r`Q/hdjNl<3<k#.A<,T@VDZ1;w62jQ&Ex
(]Y
+3%;jI?ZXEku";]2P+{Piw-TEXh"b]C7)>%HQ`p%4d(k2IM`Ogs>2Td%R+&:Prw$.Q4
n[aHm%`UUiF#W,):do%k>/?*2L%`(twPxr{0]ql=-={p/@pe8S"gSDBy*ELV7=@OVHNbBu<BZ*h);E.XE-[-JsH_RN?ST,q?RR_p|Roat-iN^lVLlV,dUR]Y3x^2=F,kmjj_go~2m`r&)9*ves/t>yAL3[g`w1C/n@WhYY?DvNjft.^T
yAy!DN0mU`;4DLt6g8*OqmVSujn.)UdPd
h43=nYFp`ZhR6+]

d-2Q{5&<Z#y9;!!2V/=B
Ftm`)WH1Y6:w!b20dJ1%vmBpx
TzjFTON)$bg5.g^LB2`7.fZDpsEr4_C}NKYXeRb}gvV/[5roRi^7-S1AIIh-]uJiA#-(sR:A
r7;V"cR-EvZz$kV][_P(PBE1+Z3g`:,#"m>L=urK?;d_D`EfI9(@|gBI`_w;/3Sp
>P<{Q"duSo^;>eu8P~S)r>lE_{W]0S4n_RF*6VS:URxG-Uu`d=eHcmXGr4uyIh9FKr$bBWm<b>!9&wOt"^"^L(IAOsn^*;%5?,CNf`Z0]7+)y&$6v~b.fBi4x0q+APj@*%@>7.0x>}Fwr@(&iQlS%a?9]@+ySCS[>cg!MZ$sW:FJvC2QtHLGl6M)pP#25051+|g1?Pa!^_mLh40j[gikW=Jqtg>qln*
T=0;_av9DhbAjrU(ujX"
2Ggvu<c[S)GFQ<OBMD+T
g[QIs*Ck=eoH_@7
4*I;@qO._=&_20_>X3/LT3h#K=3b_*`.T"dqGGf!r#IZDsyL.2%&,HPPe9"lvs9
3X.g0t&Ol|(<6ZsO6i&~Ke[XyCAW';case'fi':return'.ZuAM6KpM,z0|HYut&@61?0o;su8F@{G:Eel@xV=k)YC0?.-CT8=sNeV{yG7u.C1}
M<I1|7^SV$HJ`tD;{]*62u"yDWXnn[7tb.165_,w^9P1Wo#nZ3Ug{-XAI-7QE,nM1tS`ZsPImpxl&k;tc!x?v1MUjx$%L$"o,@XP.P;Xa,Kljg4(.R5li]H3I3,RT,UUATJ(z:=hkhB[OyV^Q^7x2x;(/"D?i:.`6,mE]y!p_:BkLI
P3r_>klU9"y_h_F7+L?
a$I+YcMSj+T^K,hkSX>M@V]Rw&i8Kp"Wk"aUx-mPq~X`46KbGU#r/q;Fnh0rekLsr<t{MUsFYs`FPo1BnMx"`~^3XniFDq7ER|_8j=$U2Clg+]FMD=FI&]tD4!MuXa!X4Z/@s8]fs0f2yJl2c2]Z5HW098Ko*~ji7=rFnPvs8+xf424S`1aV,<P8joJ94DEBrYG>`2EOg
gw+#3FKG>%6-92]=4&OA0ToZs0UN$cDlOn@G1=<)Ef#wU3pK@tXOqS`.KYX#?F]R,g>xm`d_&}pr:/eC;bO=m^Ior]/XR)VFL_q7^L9>5JYlQK_hH0Dzsn43AHMJ
b-y?tJ|Yp?G_jc9aVe3ixfkKEM]7K^N0p6K,)1bki14YKa|i[qbSIcv.d@zS)D|Oz@-Q,S|]09qk$nDQRl)Do+PpBJP=7+>".,ps;nSTe!<lTisc(9hqI52?xXO,=w%
&uC1o?us(iCM,tE)`[T2CY%t5?^Zky
+_fN4]f%KIoDMbSd`U!UD@c,CYF}wM_<2]9wY6voI<>k5Nd`d2"}D%?9Fl1:<QsINx/f%le+giKi`QP-[7!XI
:syd=4e&*[F?#RuqX
s"W
81y8s(sE30Qe),0r_1Tr7:4Y[E.tcC)j^0r^w1%(a6l44=r?NQbHDq9"H,o1CSk^daXkB>Lp@7
CUW
!ZR
K_.HBmly9^)Sjvbi0MM>nTr-WA6&(&IuNKiZZuLWw+Q?tLm;"i2e=kFBw-e]^%_-+r1SDdrDbX&Ic6.te`04b`t_4$ex;ut3aWXP4rksJ%{Cc&@he+v[T)B^OAeE
2$yXTNV)srDg#h_}nAu:`5&[Zx]Y
JpPoehwB/x5w9]E1]!!^JgxTXXa7wHA!mmTjh$C^Vn=eFkN2n_@_&oC@,/B1v;{LO)rFcB9@wHw$,r9-)_G4RlW)Ox:Br)ml~FCvX+l6aX&+*oR,-
X?zM)/F:1D0k:r+iZ$uO4DI_nk:Ph"n+E+hCW66wznEjuc[Px@^8mj
ElM*1i5K/"gkMtx@
HIh
~53u@4;3
M|mNHu
u:19]or;9mPglG9`WM7r*Wp+g41GiC~A8$_rfhI-6`CIbS!XlZe#X_X3HKc`Z:PKe$3PyB9.7`K@oW{b@r0.c@w(qy{&vb+0sPHGmso08L1%tdlRIeGS}6^8f7aq)E.xGfm2qB@o1Ko1r-Ha,T3=6Zq#1cFq-n{1m60[gqepxJjTD++x^B:P9a0R$!#%.J<a=pxC?m"6]j26M^:]
[z#>=>u<B$sHQU:|@DLPmA5=4=#R%Kb8A*.Txbp*laH{l1D|s7!=CrB,o3!z)=pO/o0zNOw4mRN0vNc>
zokS5Klt+^G5}#.Q$90xnBxi@/<o}Uh_G+IPStXB`e5x_ImrvxCRWG08i8w<$=T7C[C(f6#(_S.*~%E=j*(J:2/f,HmCirlMjl"VBbSDo;!vF]}Q$dogyWy9A*?XrR<#fJv
H>&&"7LJlNs1DOr8MCAbvM2y8_M;FeycBLv3~wDq
de^NEfIP)YAgv1k]1SBc%uHz)?n42,yRZ1)LK*sCO#6,*&n<!Wy~Pyen0
-SDd]vc%<KI[bVeWUdn6SuAtJrA$RmZo1,=^g[:BREwP^WJ%aft)nwAc;[8.G|;)aH886J9qtm/6/iQ^S~n:PLWXUE`ZO}8~J$G/b%0xeyWiJzn`HKi^pqk[T+Y,e,C:k:dhA@;=:0rfE{NMr(0RDWu
xx0xV~#e^O43>=7Ob$N[;H[W23W#DheWuz(?c)0{*<6IRZJ~_vAJtAHem@v,+.O(F>r|=y=f.HUR6#;K4f()L#=JdZB5_3P:M?K&"]e9IOgp&](U%>nn?6Td"h."&=8slY5M*7;mR+%^6a-4T3_aOx6nI9@)9,&|D[(9f^.pQPH{XR!h&H%gd$Q;S-7{8]dT&@buO<6:x/@Z-c5<1vURYG;
m/7(AmGRP!ToWe"AXzH68LdT6VH0#>@emE`5;(M!5:PTYGDd(:wB>uxsf2`0L^4Bm$BCn[VUA7ox%(pH%S3uGYjt,6Ob,a5ibjgw@v9?ERj$9J`~:16E_&UR3]QRg(jgSGu}1P:atCs0O4f-fw2;8WBe3wA^Unpt(U9-CQ.!9$d1H78"VK,q%KLcGq,t)`N+n?twCC,C6wLe^pRFkaD_Wj;^#pAm)+5rD)nr@SXg5Q/8OtdWj
@h.4lQVxhzy+ejW4d_Hdo-LvJ8o-3cY7s
wZnoP!fLY}ZiNfvRTKO=!geQXf,p!{(5W#&1+FXghrs5EQNIU3ufJ03OQ*$wDHr##QA!q/d7Gaq.-X$qv0]c"m"yJ+exk@W@FljGLK+`MPGrq+TW]?+tCP5S*h*|XI>e/_3&Up^-cKLS,u;B#&n>b=o0rD9_xR.=YZ.7G83_=qby!dws(zHV
5-St~HjNe4{"Bl#R=j84:#!n|tHbeD8MMgRR%I@I0:2d#k{o]EWfO!7buiY#Qt;=>iKAD^z6{Z.`ro
5
rZEl=:ZkS7T/iSm,N4+n"{%8&7*Ex?SoX6bj`;#.qY"*6<8+*Cr;jISKok%m3o/@a/C{m{T{ToeTTI3&!T(*`si@5
P{q0_Px<&>Mu<?a0b
3?>FX?aAygZ@]XOu(wo|4L#!y=?6y"&d:p9ckNy?E`?gDD5,Yls4rJOEut)Lbvae3n^a-Ov&Us%"$!WOhLWKmoKfO|9H6!
;#^$$EhV=8I1kyoe%!nG+L
auFTA*<lKF4,hMlW7(Et"3Zggq6)U]r`FxYH`.PLn`aa5O"n:=TbZpW
Jq-c2r_{@2EyVHD;"A.FV*1bCZ*p?l-~Q}Xhj:q:HJ/RUG7Gu1"z&@CNiLdenV-vrM$%xd/5n/f-MyoLIl/06D9Uroe@4tkG$@fC#GoK2;:dQsYc*S@Cow`..u]-(0A<I`Ak0Gjx`aU5jgq>12lWj4/TI~q5+zZiYPI`=An^x^J63hvBh{?13*Mjf>PnrzAxwM;8uuwdY1c_Qi.BYd/TxI7X)yKWu/1W-4?.^KcN;A>6>of=Yi/81ea#gZcVR*1"JQa(a0Hx)%Cm1,Q[?g9/nM)*(MXDh~D:n3K^M3Za9c;(=~[93c)B==.Zah0)U{DgN(i8!wkj_2)kiX2(^WT8y>%M@ST/4/E,>jS}SEL]:NXG5`2`psN-]Y@fz!R4@xZQwrK,pz=u[.!TS1>yx_/3>)]wTaV^p&#jiZC=2M03@p>N7>-;:AN=LLt=[;
Z?_j<w)byt9&?iCLiQ&K@8S.
eE>{TsO8-1D.a
<U7X==tuR,+y<C-FIY>`XkyRU6ed^JpsVs35+DZ[G.QD7eGyfo[xy]s~Lj]0g3G3M+Uo(Au;<vCx^GS40ia=v~uZ^Kx=2
F`Md$6dYRf*:Fzn+*P%KQ#,&KDTVG7E^W#IM
8Qu.E*)r|24L
S!&"P-vck6v=2-7CiK,4mvTf#]vGy$Dnm<57a(l7arX"8$Tv$B+;Ku/jc#D]2S^)@BhQFMNr2,<]j"fh@Fk;fvd/mYa7`0C#FEtjW9w1kW`-fo5g-$hA6^69*u@|:#2Mm[Ue4.5I?d!dsdXJEM3~F=vI93[::xdYW_C630AV1_0&M{n4P.,YQP.["I8K@A;k]cAK"BHIPY6[^S^Bq>=
Tdu{uH*duSV9y
Q5hr<j>CRW
iEXR`!vx-NAJHP8/KJ+nF5@fl24MA<5^a7["T9I*geS`?fYbMg|l^<3!y+hF>om(JU|T;eV36AnO/p`c%[rIv8&SZ9<NDAl0hKtJCR7aZO+_SC)q6Rm#an]pN[k2o6$g&Wg@TW=1yJ%g-aVS[_-#"*Vu6dB!$o>C;nG[}CeRr=D[`KzCJ@ppGoh3^GETe.(6a^<+A+7TU2>hZHhb"5A$/9~[+jvhEAnELKaji1zb1&=KyTYpLFu/uTv@LLvfAo1`9!Zuk-7$>Nra:=l9q.zA*f?Vh_CbJ5@?5^R:33zcEKq>"R"i9+d9cb4qKv(hE0aEW>"MPvds{^b@Dq-;BwRQdotVG!|,Y64rzBW8#M-V"0A"aU;YV4yJTmX=xs*VjR?.`jf8S&
ZD=|jl*&AS"9c0o4I.p{t9I,=fR~!-.r55[a?
TP7pk>-7Xz
#uB7EFk8$J]SFqWH$q>s&[?h1I0;50|F#2ym:ce00ynHn!9FpIkm{iS#N5{x8lWmu2Y@q&!(,?3i8h:*;(5fyv`gV>NOX<{7;)uY9@{Q2#lN$xcxdN&';case'fr':return'&ZuFD7rp-,z?yY2#1C1J;/-g8d;]qeT5S+Ni5g-uG;7H`n!-t;~83bd"9.`xuAUiH-aunqR
>vHr~uGR(RoQDS`n@b9@&x6]HyfAFjSl0"TGFI@DWw`K1_aR_a1)Y<}!JM^CP?;
WFSo-P9ikJ3;uF!;,a:t8+@.Uo[6PCE>acZ^v"h:xFXesM
_fB./YH-1b"
v5Ce.&9U=}$7GJ!Qd[wuJBa8L[a5Ax`CrF>nD1e4ubTT6sy|ufHDr1DO
kK4L"XJ=:s&lQ9oA0u4aJK{K.sa((Xo/G_&1kx9h_cJJt:a^sOC^q@u1
MPat4,sTtt.zI^@akDH"I8s_v}I4GgH`HfS>u;L_0}iQl2+Gi-UD3<2GLPFk^VIUPvlg-@Ib%iR@U[*T$VjwM2<*#&wE^Qx|4(&/s7fE55BJEV71kGo17n;
aLb]Sj)RspPQgH)~H"h!4S!7
08Lm^_.u!G5L+9Y%YOuA];5FT#|Q&p,:]IB52Y:he=rabwL3`9o+?A{!Y:Uw@.~ciXZ:R
}=M`VxPOB>Drf-3&()4Ad;5hOG?VXi#=A=T,!@[(4X0[o,4pZi%9{fBR*
;$HI5=VHy]qhbtv*TYz-8O8x8KpNbOE?$GkQp3-QTv+3xcAO8l4LN/df|c,1m;sob4MvDTlejM~]746HvRV1LfLKy,ALj)@2kV`OF<e!`M,jB[`8i:Wenm7uA#oV[.pQO[I$E7c_ir]l}@tg#*nM}wwn7yRj1Xo&@t:BdJo!0Qa4aA<GuYZx9=L>n_4EbP[?$6OG:^{EyaX4l4@9|S)CRg&P|y)@GyMHjWrIN9cP5h{QaX1FenkQkf/FmU1bUgPk<LWB!IYw@"!`zPWnQbKtEl|P;E6T["`F^Lyhzx(B,HW!cRs7Eq[-"V(]0?gdg6*HDVg9tiO7g2GJQm{Xpg[19e1tF:*rqlJQY.cB|1zm0LJ78"U3:F[EU;VrJ%j4lC|E?w=]4n/G:y``.DVgWR2&e8o/HRT-LK!$#`bAQS~hEPK+>US3hD&h`F0D!;U+XqawT#xK}dh9y2e9@5}+,<H%x3:CtJW)d-~CzryhG
P^4^(^=hB=:YuPX0__fk%;9?3A@a:jZ19l7ATK=oWhv"&sNL}2^6oYS`m<N2vd#.XmD7UTjxG3nkJ?d2(BBSr:acKa`MNn/4XYeQLo84=Q"(kjt3=l@o6/3`+D[;d9oq>`C1-B%h|_1]-h>>&f)G+B:%dB`h1Y@F!5g(:eqL$YlZtTvJi@k(9%Stv"=_u&-ebK#!zJ;r/f(+Zn}44V(%]ppMqdiDV.{s?`4c&Y>
zY0W"G^Nq
|_wY7[$0Gt7_?+P+-^3-+dt8D2Q$T[2e1:2Qf.jo$(2oO
WNm@rEPH~H!&VI0G="(w$<(RhMj/Is4TU>`ufZ^X+5ysFBe!?=E%Tdi!BB}wOBJFm6#`T*4a%$>39Fh
~QX-7;j&hrj&k>^;dHEa~pOw;Z4;5QEAl@,
Sb}Z21Kd`<=[3:F1^^mM(0xmJ!nUeJM(<bgfO*o[lN=#LDnv;:7J<vEsY)`i]1z^U6`lvc|i]dlFFAFx]u3brmq9:i6F8IHR6QxBT5ct&ys+5Qsq}IF(*/d&%-RmswbF&
8R")mt9?(Z.>Ec4%;9@Ed6ffG#]t?bDC+eA&M6}MN$~=0H<l4AZ$7WiEn7*&Rt^
^DS;6NtT)fk=I0=9JM(/HC~!j,"70yOP!
>>%vAiqr<!mnxn"M-D7uiVJHp?=2}=nv&^b!0GfP!2
6sEDW3"g*H.f5W:>yO+T
)K.QB)NDm
#]z--ZWVm$PFUlL.JX=1UNohz*#yBhkC$_?:/
-#*ZFF3ieQRF($:@;`+?:m;`FGu+^UF$S$,cOFua8Rq`es=X4E+!*UfHZ6Qg|7IpLJ(FfCP7UEg(W!T1,+o9]mP%kUih
8oAxEnj{H.*4*18Bb"p4x,VUES`wCzN(D
I+3</{L>0,_43gAW4($K:o:pYdYK9Fjpiql{T::LD*M,gIfBXm@s!{*
emFDKT5SU=xao<(<xRV3b!
NEtinTK&
V.s<[ho)MVswvDwB7"3;ikYRYo;ygp#nv`x%MO#mA%r
M9_/aGT]u=*k$K9Z8JvUcZQyK1QL!K=2gP5b6Y-.>[X~W^PaokL}9"9iI:qlbuJ,D?T%`//wc**V!B#n.9Uj"}g:FmX6r/q2dbh_,~_)?`PsKXY_J4l0//n8.:>*HkeI-pgHjEAJa]86Iq[cYIJ*M&0b)<*Cu$5fxOF?y_[sz%9}dTY*4Q9B
h3,yVF)4M)8a$>S@nf$n)2Ywe6x
2GL%]^p^gF;-U)M/HMiB2X8,#jbb{vD+x
di[1/o6cqb@+R&3[B?xL.q|N_(#8IP{UF+hNlN%-wwZL!2Ucz^~&%_pkDMRj$n
aboU1{<P<"]FS]x|?q
g@;G2o]fPj,QFZE6q6oF<xJ`WD&Q.a;,QfU[Ih$hhM&U*Q)%*(6jGePRkSr+;nRN3]<ohhsNsp4U|m#iTkT"x.4r_Bbuv$:%,O@r4#jfq<{-![h*S$@7;i}TRbR3{Ks/3<YR%Py
&!mIB]lut-t,PbCU63>K{drp=,kRMQ3M,S.Oc6lVe83m0<1hdsUm_cr9bhx&$y09f-&LTYWObOg;3pYV9g;Eu-bq)krpif~W{<$KG^$Q#m
dMqG#HJMx{Gl["#U0RBY^Ep*Lx-)P}Q_*oeCSAKGPQdbeBx<a0O2(,v4lK-
4ReNxOS-Q--hL)!L<nv%EN
9e(,t&-[!5?K6b:Q-s*&R!trP<-lXY!2uraGo.nFoHHMTG8aG6Z7reZrO0iV:=QYjT-Gx@Jf"KPO-A!e0co@HmTeL^j69U%#]^W`ayf]_h~dwCa@>7J4:C,)fNQsr_uhm).c]5O:Eb1xYl7xV%8fp4dw7dc)Kj?]dp7xl>{nYcOw$>~g9u?x,h`u}9,VFir)x-{[F;j8OOVe*JV0J!+0(])H/A:j2v;q%9)CY*"_x3gjJ$PybOh"S**$/,KSDPqs;*z!gq#%V%K2Qq*[LrTJTVbl62*AHx(,FjNrlNpOl?q$bh~*2S]Cbn,DQT-<]Qq;{4,_K<FZQsBrb"kd:)l[6GEw]-pEA$t[4*T!"6>m^Cg;v%sLgfH^|6l7Yc;]lPa]VuruObf2f2NfX%c&t>HY
OI$fNJ;M3zQ?@e$i#-m!_F9&$UUz@oJ/%&J0g;Ia?/JCSucBR;D9ByB7EaUa6s)+1cNVGMNRksBueRhI=x;c8iH(J!<v4+9{g-)fq~>>%ic="Fbqf~e3IIDZVr%Kj*Pz_>[f19$pUwKrIg8b"}`z+1TDU`@w:aygjH<7wt:t?}/B6&HCC/h`>83;e.N<>CF}T=AWeJ?4.SdEP[lZ(T:34/.[M-O2(4A#.:qWs_URcm#OZ%[c(38HvPESJ~ulu>,90m!?#=aM$ylK^q_nRKj@mqid/t1(7)j^Y)_$[t9=0b?Hg,hIG~G]6"elOaXrKXh?ct<`q"TZ]z!/Jmgxr:v>,q<|m`OPPf
|t&.FUihDZ6_~vNO6;1OgsO,6QOa78Nf$di,,0aWV^$1J,6aT4.AIBm6g1_E|25AP+gO0rwI$
R(_F_<0vC:7fD`8=#.*/lAFD;_8+&Ftb^y:Pi+wjp9C*Goz8;WZVr%ymAMKLsf`VJi%3mwT9Sv"p#:dI`U@OM;NRBnIV$:D)KJM<qd)ucbHrF@hj)PVa,.hEj,*8,P@FtPC2/qE`8Et(,jP)KIDq+;3
9VEUH3J]j)r`za>C;P[^zR^U3E<q.K5&y2DjdZR&z_SGDf2_Aab4/?V!H1h3ORRtZ,@O~HGCZ6wcf<RSdn7o;d8V3&^bxo%G`9]FD?p,NsBT>KNf=LM2yF&k!I].C`5cQ&P-d$(HbO5n4DmQuGDp+)i*Q
rCv&`TuNY/:63l
l>ny
2,yM0GPmZV2)0^W)8>*I?Cex|Po,]DY27a[E<h;%!@KSVnkE*8!/5kjH*^y<BYhhbQj^]P.Q$C-5y.:UW$~*{D7KW=WB6we++%CKx<RyH3-xHitXw_6DNKLSoP*dN$,uN4pH9H`NfT*)c2hIOmMp.8^1H#c9V^30FD4G84/:4J[W9!0MBh8s{=zR-oddxp[W-!_Ce({I?!NOgZi0Bn@KL;~s9StWJnN93lq`sk,Bk8{&E[SG<:|jW@_?&yQD&+D,wX86FcvXEZ,5]!3LCZ%h$UtST=j-G80lThTR@Cvf=TlZMERdrJ&`qa<ejve=ai&P^aE(^Dsypk8%
a+U6%DJC:Iy:#R8r8"1TNJ)Ve]
z0WVbB]EuL;rzH]%:[gdRuA:FLNf:s>4JUpR/rsC"m]n$2{"xA-!~kmId
BsSZ2_FS|ARZYh<f+-uEqv3s6<B
psM<isns3<?FQrkM#A))LjnlLYzis>CbM<"T;1!l+F)=+4>B+1@+e`)-c&XZ_(Gw8dgq$9-P4p
U8BA9X$5?YH.B1bBPAG;9Qje]^.a(gGWm6AD0TQiG%R,u^J>I"`+tB;YUC6^Q-tgHwXZw8-2<-U~Isf$cy9Or0F%Hvywh:9[A[edm%<yRcH
O4t=Mi.[RCvGroG:/m
+ZIEVs")`b3$?KVVZr84+K|x_,p';case'gl':return'&ZuKs5Hp=*70lC%"0C(+Iw?&MYKo>sEV~ixBK0$;:5[5v99#7)]Lr+%:~LpPg)#5x*QB&r)h#]0kLf{gGp!PMK;
EkWb_?C^IU}p(cd.BQp?*!;bTsofpz!jAf;;=w@m*ix%hg>F}j)ReUL:]<IGR>=#-T$3?`%U=XPl-^;PLU&rE
9aI?7
9N5=x`)yyt//5h(g(jgwE9T3+-apd!|-P39kJZ_tboW,;uH,&bO>
d%D<`r;S951eIo*3u"uZ5:F3FVi|U02Hx;gh8pyZCXP<AHLjE^--*xH>$O@vTnjavVFB&V,aczgW(UR$=M1C`g&imQ=g#H07pob/%a,[y5earZ61AsW*!T(Ks+As-[M-[$nCA=(#=sd.Z_hxZa6**=hrF58<ZMBNqM56"&xD(^[_/iH.mGv4nx(7kdr=p/4k&c*O=;Z(!vjQ-)v}BKAWA?#8]fTFC|viqmKo#7&rybnWL{
ma@MPc&BCm|8YCOlKgU+F?rb~6HtYO8H_]sf?%VHBQ5n6p@@4dHq9X:G:d2::Z:OaPP1L6,Jh8kUZsHkJl;_^_IJIP?RIxnl|=v9pET#:3.Z@1p35Z/x.EOR7<9oS!Nj=%;P]UUR5iwW&Jm4@pY@;=t"^]ZU:lXd#"*KQ#[
7@(38741jEN.b<wy3uY
#dtYY[U.C?,8_^KXijbMe"Xf:n=:tRY4r-_<&g,CLH#[PL$EyVFxwJ*g9ICV-B7ZS!]Q9mH54m7NIrY1sw,!7!oHQ=A^tNS_r`q*,$inmqImMV,#~r&kJ[8cf)-U:j/FQmU<[Y}LFWl,.IR1BUZG)P^->S]ruIhS
&8RK_{/?]9$w8_wZqa=@y8+U.`,=mYJZlOJV+:eN.e!BYH7n$^Vxggszy5KR9nbzfD4UNG4"bekVtJnn)23Em4nF?-pLh1Fh3Mr>+(?|_O4Yl,NvbcXp,Ll3n?5"atC_>pNthQP5E=E@(E1!l_/r)]={k/GqxQbHay4+$$Au^+ypes+[5RoF8=Sz?jd8sO+]`c#D0xizK?O4(rk:?EK`+!%ZHwLL&*5CDc>^*cC9-I4Hj_7
lc/i3p1|^p=gl
f47CinMFe
GAx`O(`}>VyEm:I8:-adn2Ahwt_Q7v#Q47#z9oyd???6nKl]h
FS>+$YprmK0Lmuo%C*@TpZ7T5pJE+i6*(~R!+kv4A79M^WhqQ9O<K>KpYYKaY7<RR)T;1TspPK^Shi4L<gy~=*oR`|3HKZHX-@TrkRL?&>:YtgGJOlURVyViW_yVC$y;M|b_Te8_3]KOsc25w8hv4l5au$85"L9Sbpd;5q0t1u?]CcT4[aPmhO[SSy/jT#b`+B4zw
G{(!`jB,%Mn7"h
S.<nsGgkhWqu(N9_{rO=p$./of)1,i
0_eM_FAdD&)v6u5)+9GDU>Z<Qh/zkzK80{P]_8V>[,Pa`nl7*n)Y%=]nhD_&S"x=p1i[g%c_n-;n2iwvkHGT9;.S^6[VbGz!HT$WUv/jus4u
2GvhLHaM)kO6>fg41Y,LlcI5`"lH6]f@(=WQ|c:ozy]<fdeD/%#G*kn7R:Qa>-PF1B^PCAQ+S<cFKHah&9+VXr/Js_[bi5QpHiy8?/IPH0[vBt/7UCh![k,nc(
%%y[gfL>#|&KAdYkT37G7>tB.r10Qa(}f5&@2U6FGBlTmhg?98^!vE(?m+I~!v<{e7%^"GF?qd806BZo&P[%,&]v)/A??*&0at";H%PbYD<mUlQg7s8{HC*
nz9j6a?N#z+SiKcolmEqf0*Vqs.z>Dtq*<e0J{
T?)hWd}]xByQ1OA<t"*SSa7?QCHstFs/hyp3{wS9
FbO:-`?X]{u;m[[0%Q[R$vmmw#_1$
9=(~b+.@cYGCe/e!YOI^KM!u*L3KHn/IaB
"6Dt|%
+r.@ownYgHyCOFyLi8h;ZsN*1RjeN3!<d`jT*>wkK#*xTya9V*W6(gC(q>YH`hq[51wn)9gj6,3|D@f>2Bd,(t]b$HK=lZ@HrQ/J8X;sSd5:$OJD!eP[ooTyAN@N;~^VW>kM$LcMNP%Q]Oie-p!+Ptn}Ax;[Vb6qu!-)@3Mlk"bkP7,
cwfD65JXxxX0E>;>R=+JX^9Cp3e9WR/x)q
#^|+UZ6*$V~Ak9tdZ>Lx2#!=7P=An<Mo
"{^.HN/.v%a9V%wto2D!b+i-d_Q[n6bfSV)t_~y5:&czL
<wOBqYyV/ny?YPgyV2BSAd3udJIn41Pe=wgzRp_Q.QF6%p&^^{8cuqrJ_~tu%XkV.,TtPedd)T
C_ytw#OQqt;Bk+%LngT9ytdm72yvstU2SNkj9Jc3HSUX:u[y7K7)OKkZFV5_CM*
m"4iZLL2n2@9FsO[6+zXP1aT/b1`=2*Obn{;KePn3!VSu6e]Dc8LgZ
oIl+/vx2ayC1ZO`<aRBSJiG
,E&YjvosLEDeSSl+P3]p)<_LSD)}["=+3lOY8ls?e;1Px%V
?9QOgHCHs*204%<}Pqyr,[GM1MK0/ckA@m/v.;A:LY4"JNA,Y<HIgAR82d=o4`x*@OsvmDLT=B22N<jlYfu-DZD<j*>TWv6{E}:6!&O.m}t?]X/xdQI)3wfD3T55.VfoB7*LC7HxYkajfvPX2X.GkJ(T,.*Gjc[<-i#hi)2cI6=W>ee$-V;FF:FMQZj
*!+0_BR]awrcQhIskS,0s?>TM(,JkR;U"Y_yFCdVp;vjsGaM0%Iucv$]k{<7DRu!d&>>9O8-_sDnf@;w7Frw
aVE%kAC=4p>?~ZUe4%W51olT$C{lt=kdMg]QCXedNf;+shpP6l(N/.Q#WVCE1F`$}5|u#Sh#meT>3
$_3;<</lP4LsHyYo5L}e4?D]Kd.fbU<P<*tJHi!"Uoj6X:?Fj;PRHMYRfDE$~)pU!CAdn[b6RR<Lc>RM$;r,fPj3$p
9hXU75,tLWs}Q7pOs!jNM?VTPzy/l8ay
aJj3F2XH3Q";~]Bq#*"GWu1"D^:HC1xpTM"N{,5(a8{nDp]s"GSVSaY!c,]YBDcTX%uX(U=3%KQXDd
PI8|RL6Fr]k/P>
%R~Z:U`,S$hVwwS#CtnjL.X<-HR^C>xWD*eH9lCcH&7OqmJpI8t$w+qwI$u[|0=1jyHLMk>Iy/&^S*4jGdS0%Cpf9Oq;3b=59!v-2`6$EE~!Z*q*o4CaRGn;pt>N|z#YK]&vb3L;<X*T+?lVrxV&Yl)<z,HTpPq.V1?I}4qe`Ck._nDr5,9Fq!cSnXcL=q]W5eO%0ZVhO!*l/:6!zEhuMJff&[OZm^kC`3lXnFAY]T^oGF&LU1WC}uo!msu/0m`Ki.9!XC(+p#`k@x)JP3PcRw$"SV{jF&wh63h
J?;H-J?)p./,Rs?SI;&"[QeK9FGpP!zZ(xP]1q!V6ycD=L7ma/#R_UaolS9F@Tkr5Q5>s(u6wi+E(<jq_m*Qzp,:5YWbV^Y>=$=!pg<N=GX69]Y:4;7-1ES>lq%*Cy/Qf(3l
0tjzWgb{U0s<E
G>)KRLTG;55HVm>uuOd%5?LSC;`?e-&:iL0"(C!*v[l5Ss8mw]UM;:,a$FOO"J6H1$H,tw/}[|dN((XWh]J;=#pvF6es%F])ZcN0+uQ)R&$y9}:aPkez3~GvrdsAi|XwSqTVqQ-AY>_KlbAZC|+$,`9X*u5v7"[8pb#L=P*E]}l(r2G(+^k1q&7?6Bk#N0]$Q+<1bi+3<9wbr.v:yks-81E!owPQ<q!_(OgNnRC)bs:u86F^i4D5>JbGZ&E?v<5BT=kcsT$
%_A`VvPG$d_[y,KSYUZx%e,O@Pb16AbHTaP`0~5clr=c7|+%]#_Bf.v&pS*Y8khy4f$)NW9#(Gh3>CQ_
ruJ*_W
<3@jly^;Y#O^o.pf2XZJ4$rq+;@g-,lbyFh]SmuSaTG6+~aNRRkS@<,OSU%|2WEu7_J_II6"NXd+p,
&OU"igb6IeHZn]]@R_=NP7YKfC}bwW23g&)t5k]E"bSMIb^L.2GQ#:&1,Q3aJ+_c(w!CyOJgCp?YzPlrv`n/D#h.y3vUxd(DdnCJNJ5.Z-@7IIy5X+kB#k#=-Xtr$;vu<Jq14q5l?7Swkh[QQ=)DWC~>gEiXRwLBe#OJT(JGnx-gePYX]AaW!_g71^
AZr5p?NBE_Zt"i-!q
pXt:IFn5r}QFkh%D+
q`1iSF.S"ldegD.V)uH"SOw;!Mwh)|d7e9KJWT:}4n+G0=
NcTJP/.hDV8jRV2PyD`Y>G$WUwyXB+0CsPie>*eJ6[
9oE>l;W-&w3:QB
|`lVp;AgPT;,;dh^RoAB3Ms5Str[xVQ]$15*?Ww(i/JgnQX]{94oNk{G-_A3ypf.qv-SAur(M3"H,t3BLTxiR
<<.q#@x1wG_+,sEFloVmR@A;yU{@=
9KkEfiHx8@|>8HTRAqv!4I">inr(wow
?MJd!NF';case'he':return'.X/@j5Hn7,{0m]rQkQ,UwmcllovSa_B>6?gt-i0Ox-BZ]1i5UF4T@_@S8y0MGvOgV4s6)!u^cNR_B2,ewl%7KMR_bx`!j$,v%UZJi-fEH%~RGvWe2e9fN3htt?3F)[PB0P@xj8*n@(3sehrbQlwpJKV+T(baF@k6dc1lOIuukE8j0e<M
^d+jAVK4H%at)|3Sn%;iE`p-nAo"G5T}^!iu)LxH1!*]xX7qw>?1?P1O&vt!x{:d1wDes]qP1+Gl_roD@{57bRa%:l`j3/tqJvZ2?&l21B*2tgtCL-M)?7On_H^J>:7mMIZ}U/=6%d1&
>nU$D:2hK6D8:!=^VoqHeH8GAtlh}d]SePj2*F~-kLlFh"(A^r*-E]%b+uU;!mN*"l_]%8{Mqo15x&bIdo8SO<[D5B:k`3ZbJVbdk-~sQHlfcsg[8OlIjk}dcNtJI9#@>5HruYuQeoTEu0?P1PQEw0Aw`#XUli+GVg
:XnieRIN1JrYA->hDePU9~]HmmuW,hmQ8xRbPQ)H?"_}5e>4"tH=dG2(A2l",#Q"rI=f&xhlayK]fWfoANKBx5e[]in92A&`x,9Y@x
&$npb;V@qU425m>$=Y<L_J2af1q7dg(W-e*n2tAn7Zm#z5RVT2yIW)9)PvQ9A_%c46&^SbzI`BAY(?|3g)3]>&}cq*WLrC0:J9$
E,6T5+&],,n]2Kxc793-IID+&b@ezMcsvM4h+S+"P2]e3aRX`:A%ox<qnu*M%nx0WHO3gX)>lg6X"[w5%E`Bb5*#`%-
%0@LK0D9-$.t3,Z:#
|:,X?UV@j-ykz8VZa8yc>9zDdG:?>U*pp+FqRSPYmB@rpr>:eD
$3g#H(5nRHv)fs:$R
Z^.g]3XE;4CP+qkn]PX7OonV?O"Iea^<J1]mt>DsdF4fH(ni,%t%q#m@8x:sLRcWW>&[x/5OtJKry[9Daibr"z8Y
dD?-C>Scg,_<)?K<$Dd]]qDBL"UZSv![Y:t(pkG+:!y2YviJMQZ1q&2BVuPn-=f&Y*)w*1=]IXom2WX[imqo]Q*O7!S@au)qpjUdS+0
^tEQcu<iB-"c;X-YICzHzr5vX`SUP:T2h6M"GqHPgk)fL!$,@GE<!2//<qA2I18eB5qQ)R}i"KunZj%F}A8vVOU[76;
Zj7+{oT`fP:pm%bGYl!a}-($M="b++}v")Oi
5H;_uxbCY.nI&:u(
[e(v_/z+Rynv[OZxk04.bxTPfgO7YiYJ%LQhM4lVTK/5;,|qJ8X9
`VaOQNmr)36&TSD=m7nWmk(sQZCiRhGn%.4>Ba!t/SO+>dEgg+_aE.=Ds8E?p5c8&Us1+tq8%)8t;b2f/!jYLNhvP^={h{:^9mhx@sb;6?Q!-$Y"DL:/md[z#1E/?Td(C1fCnu<heOJ/2.]q&-?PJs=6lPmGKOYV53yGB~kOC0aH#cm{B~5t-C6*=tBiim(a!
7z*Qg!a#oY&2lTa]2bZ5[3HJlBvnaf4pk@jmYo#s-sC;%rwwu}Bh1<GAwDAyE%HZJ"**leWT6Yo^soFW!}o#/mh~:%6f>wmFq5KM!?wcl?S,Z"sA9%5/j*sNxXwO*iAgZh!4/TrNRIUV-y
lIRZGT
D4Lcnq2xslg61VPPXsU(TVHi`g[[ih:[n~RO+~43<PHM0F/:bp=*D
f@MpSvx9._jRuKfjXSo$#4dl#,s2DM#Or$F*67-_MuM?0;TAoCTp;Ci3&csP#KEms+>TJE33G:9HJ1]F"@7e1v;XrQi,bD)1v=8ADe<]e#f7mpDXhb@h8mkH(jQ?e=
$oGju)!V
76oZX;^s![s;0-JOu>RZ?J3XXuFz<TJ-/&G|"tO.=B^d*
dpy_]!nmr"D7T2o:iJh?fsfeaq8_#H-(#{({u&H@yJR1F@1ccdcmhskW^yYVL.$9^y#Y2{`PU-6jwoKy<6^fi]Vw.H)yidVF1GEf=33n*2X]P(sa_%4,&HNMtAy/0R7ewD_W
G8(d69>1J-Sx<U2nnlOL5
P2l.BOY<.dE1Dnd"Mn^U4suErwmG.g#V[
28zt|Ut&eeP7DEd"[HlVLQ9DR1}?aJP2TJd,G7E]:HV5J-k4paSNT8lUU_#WhK3nEN2<rZ;SivSMfC;Pe:=U
PfRh.=Oh5sdBXqAu%$Y_)ftRj$/PNfvU)$mr:tEEDyKP>}ZA_e%EUs
F%}SVkV_9L2Bi4t3-U
fLbVkENbsH,~wnB~0ZUbOcrB57&cpodv"x.|>JkE[X^*5dW%<]ETSMOB.l7WK-<>DnLPkkA~QOfZ.E2%tt0jkC:>j)MHKDc;!=Gu`(5k
3ObQd<8a1/ZijJ_5Y`%2yd0Xkaej(AtbZScq7x|l"f9(BA;"<h"Y;+*8*6l.6<Rt;NWizY_#@@.fh51o-gI^buH;"@7#NwTh=S#"Pn[rw
C,ZqGqqhDl%#wu/PQ0lx?"C,F_f/zyg@if|e(k;.wdW.Gd2R#k.sspjKhSpJy>>
WK(Xg/TF/n2cT056)$ffNZUKKWiT|Zm=qN2>p!GFXO9%sS+55>4UcMT>vS+3Y!{2
W#lRjv!v1k9jW7R^gaT4bP<3Wi#SSiV*n(&V
adoFKwgH3N*$H"OkwgMmz-{X@1P%7^lDSU}6"ZHx}7MRV",&W.$SDiu.ln)997]#wY45cd7q@:`$N80]4@BEXoJ8sFGQw4vX<?vLdJ8=aKN=_qrY`h
q&<}"jvj=e:RhEAYc=K}a-L^Wc&v7qrjs}e+_)^&+IKWR
bo!i2aZwM0TkQ(PMNb;nd1aoQqrspVk]H/F5W=9IFS$jR9;l6U439H2"g*m.*}`4WU3`r)J0:w.t!XKK28@Vq^(F%?d0FvHwCnE@$?WerT8Ffq^,>IC<p>;<sK`Owvd#&J^=*IEKQzYbm%_J>#K!]73,FAXI=^;3quMMB/cLd{KU+$A|lK3$c^TG)@^(9B.S%FO]M}AP3wnIiM?H`*RJvaFP20;v5+P3:8O@&3=J!.dxBlTf%0=0[sbMh/iY=(9>kmVH70BPd,Q;yx!tii&g/>"h
FZ[;ighc@_k;wT:Y!Yni-_C4zMk)b>)[DmD0`<i>TJ<8=;zm;A3c@:s
vn0evf5UIxZ@gFC0,l/a>Lq67c{S*k_+"KL@W!?IF,3sMKP-;:sk:wZ%{K1IMI}r2N.mz
a9FE/C*WuYv%IYc
svZ>/MNwX[r_?C
Idq?Dke:4}f_M+Oy1>BeCC1ZOHZ,1yPnbu5}=LqTaRFb9U)dHiW@7aaJhCRn
HjMSPW;ON<"-C0<uoX=)f2I$PO1cJy+3<Y+BR#&fLgfFOOO8P${=1AtiO(ZfhGFQ<3~tp"*:tDdgzSfn>/eZik1,a#j1Oc[=e%N?|39lVk?;.7r6HA3*WdgvP+Y@bqpTa`j#JQ.JG+Mg(<}]Wu+#qUD9IQ,",v/4"@8qGOR
r-yY`fU3Ya$r0-FpUjLQe59Q/MhM+CZ>F`fD:
FM`Hv%gZhOs@m+U?0w_3C]<Z9?~]3=QXM4x*H!*9R;]6;Le(MF5T%ghJ&VG)y]*sZuDeU^J]?H,F/^_vOT]7_"2Ir1a3dDBUe&kWLTg1A`=`y(NZsB/=PncG|<frqVG7<0uJ@>~Xuk)^$ZD%aJET#46einlnHAIs1#sf2ZVXXGY;4?rh6;wT6/@]^<Gm-Y5-Y559i;JwYSr`tY^v&Qh4.:=PkH!Kj,>qL++w++
9W)*Ay+jK8D`BtT(Qp$irY&NF@84M1ShPu`52db}o##SxQH|CZpAE"tyg70>YPIC+z-UbVeuKo)Z:OXd_A88<$!2&/2>E.3ef#XijX$u6!U}ctpBU_Wr6_Cxj8Xcr_MO?l&Qr#h#T<NEuJMbB0cd_lv1YKc)=H.,d!pTF30vQtU6TQVQDU1|k%D$-$:`OMLhv]z!_|"x4H[?(M
3"fcrHP?pxZ9YhBK6[ROiR_w`]Uk(WEJ8<rQ*Vt=H]_rh#W^HVFkT"N^.&Qyn^t+(y{p#3vOU,DV,xHod8Q+}JLcdn0&(w3([rs"%XqGqmS$EjDr(FlY9@5)`<V
7"If~XS<_ZhoH=r9Uvz[(x99@f`9C"#nN=q(t<uvM>r4/[t?PI3xf%+`hkP+Q,fl803))X843vIXFQ+WNw%#di0rLH{[j=E
Q<6:k4N:suOU7Wz[S_axr/;th!t#Fa#+CC{)iG|AF$-+$daw=9+e]uC22i&7W@&iAorhz$-<shcTTQypj[r0()*63[_cc*o/PM`uTX[V#i<M&j7&PQd%n["nkG&8sq!Zzu+#]x+FEbRs*i)R(M}`,Vz*<&{JGg2C$r-N&';case'hi':return'-h_Vcb0Ap*70}N+iPtuwnIj;vI(t~%I8U$l,W"m&r?z*M)s%QQ+BccarMGh*{QqBBYEZuv2!g_<*,tS93#
6>;XSzW9?.Xn]ryeR)h"xW[5Hq.$,r<Ocb<.jItOa!Jrv4/f?8JJy$I}t7L!`#H(c~??h3&ExLB`POV1`&GF*bM`_aBZ7.W9N{7-o)wVaCD>Fns8F$LTcm%Jn{0-^RMQy!xEf53_n!Ol4hb~6xU,,P7ft>WbcLC`EzB6Q:#fX*vtN|v6QL-!?7Ot
KqkP*6;>#&iN8M$P{trM3Tuf$;#829^L?>e[nJn%0uk2QC,<p-Y2ayIBm7{Uas:fY+c`?@z(a;Ru0HeTIaa/rbp!3Zw)MPf"u7F.*%N*%NFS&q5&+&a>n&fC
XSof:A]!=Y7KApn/$CJ/TRr)NC@Pq]rz$q`+^g0;rUxYAH?#VEx3sVx&_XD(/X>*=uaOX][J44q>u{$Ef5)T]Iv_P!Sa9Ei:N03S0H(?m&Fri7cV_MPEW!y-rntq3r"JE<#7#;m{7oc5OK>(qf-:tP#TA<7(psa}#QvKw/`NpLy&$$r64@-1IOoSa]!;7s@#OuSK+XX)u1K}F>w@CZY!+Jau^aD$%1OHc6PJ6JhNM2m2x=i*^5_f!7">l0fcj,HJtHU^n-SItS8QHs#dSqGx`d>m7wR^=@dZ<_>)j]h=St%dF#lEuPhO]b!SLWVTH;W.Q`29UDwgFJ#9D7>f@JZOKDxcCLc-qHX[c]Gy0+;CvR^wL6jT]Rtfd26/Yx
nk6HOFW>2CiO<fW?(pt8q?)t52v!>3Yl^35[jwHaf&;^27m[ZuYq2o+w"BlGDj>HV3jEZ=2LT@*h_^AgQt4kPn81.K~4)sS:2-uVG2*#GbimR#ekV#xm.&60*h-pg$1t~4}(|jM(Yk*"xun+@Sroo=)X>@VNSa,eeh`NOTnTK<0[W^#medL,+JrS?@/@tA#eX>=;,lKh`48ZR0s0"G{jhU<5ZZX0J#>OV^0R_#ji|n?9O0w)@kEQ:BI)_e-B,N2Bq/sQSso.XA"=;:D%^]!vY#Eqxe$-Gs0#Tm/(l-Ln(?S^n
)uR04_N!^Z7mGPT_&TD$Pn+wK1jc{DKt"Sk.aMS8x=mLT3[B-@Z"}
keaX<.|9Zw5@_azGdDFmt&}LD3VA8g:7V.+)M<6k{b]f/0X_FZxh<xuiaaWXf,-r5Trl>w:>aD-ByVnc;LD%Uhfg:B$li?2i8J,!dtO?;]n9uEDI3.@G#g!qH-jwmn80jdF-spwRB51*jdY
he/`CFH*H3Ztq[wG8.QId;p-sn{dgBsx#E;bo%zA0nz%dv)O-Jp;+A~_Q*~&Y;{n"L,:$0r4!aA>@>AYcI_!.U#AIi43Z06Dxz$l<pIdf1`]|dBL*THuybxj^j#c4L^AYLAM0XEjGQ8h=0(n7Ho;dP(>b)f+
db(^ncI-I(4NK{`wezJtrXys8V4VF>Fg1"[dAeYlw4(9.:H73xFo
@T#-I/.5;sigP+&q{JJ(jf2yB>3-aK(_YTUNW<{+M>^K0.c6f[jan?&QLA*=0"Hh4$-P#rA?4Gz=M-NMpA0)2b8uEuw!_v^_sF{tNe)AR,3`h6+mH:7Nf%P%dRdRYfc[6#NbDdD?s+46<`16!f8n|%v176u,LNmW)ai=eE!R,5)gJ58gr>P:*>Eihy.K)*a2#%F3wtR==E/RijAQHuBS32n>"!VO*y!K,>!4{"bYJZH-dJ$*;*d7ruVnpJx+A/#/H2
Nlfb*=&J^
P0b~X)9=:2=..b,z6EO=hLu`YCZ27rV>Hl2[9zp;vFTVa/e_rDftW
SXI18vvq+@xX8]Srfl#To2"OUL^Cn2?Ao"iC)7UFGz3ZW3y?]s<v.xEl/-:#UhIc-JD"2rl9M
UZF$x)u0GwRhnSMc"j)uv_:^6n@V;[X+ex".?8)TtT
:H])f"[sHY[Aa,^[pcCNZGTKUOk+&PtW>MRa)#@<m^/M,9K(hb|ymF8qzc?g*J>ItY,,,1y(#L)5"*JHYP8KE7jqGc0Eu^2(q5}#LD]O)bWX*@ytxWeEJfOU^mkDxT,J0Dm;Is~ushQ:hFBb&12Y_W?;
9laE#K_x=Y6SQ5nf/auU9#${jNY)$O[n+>i(Hpw#xdhz`g^Uw.&;Lwd41.YoEV<CjHS"3%SYjJQ4%uNE
v.fXRvd^hJS2|yt$mNk$L(xeBhz+/Ihk@_*R8YPOU&f&2[.-!h=KI]>uE";(KC5,"w5cQ$hW_n
^W6iEt^|Sg?(C:3wiLCbCfTDs99!TqiwWB4K4JooWZh}(LsIc[vQ-Wp9BYD1wnd~")"#1nc7SaE-Pd%1vm!|wyG4mrMU-U
$*YPMMR88tt_p_d-W@m.rQ_$SCNRc/uPWjsBX%n_?#B_f!*Oecf6YS-aG+UWN9!As&<Nn*Z=sCe/`!T_s#rmcCtQhaGeGG6PxC!=fmcLCm)iT3?Qx9*tMeMCPFPf!se:aAmnHaDtg"/1Be23;#tbmC$.8;u6&*n,TM(f^2I<;b~ub(Z[fRw9)w:su`tsGUr$2*)$Pv(X|02:@V^"y@~jjJhcB
5UEcWjaH_449tL.<[l*-sn1Mojiw^bFt>?9jIo0YGsf;-EGN}@"E-+oAlJGgTp!;hmiA756/@S{LR=4Q!MK77poP<o4Q:%lV|OeP/8BEHF-fgs^."atqmpmEf_M6n4E(T()]bxW/lY6E0E)8
%<l5!,dI1v_0L],=>Om
c/j{/Wf2+OCcKjYSc)+P1|8L6;)}"
tz]9X"B5D?HMbB!%bwa,PiebWZ8Io/T8>J^ZL32U9K.g/Tvt-gp@H&FJc=lQ9thLH{f}A$V;9VjS4S0>
f,MSC%v^sAYE/&}51CUT-5l#.ji`}
GPof-GY)k@sK5,x."Wv@_%
SR`{my6{p]#3L3we6W=<A7eya`t!#YxwnN(YU{!tW"?RGTDdWm-qs9gAP/l:,1x0[&!VYudSIq*m2Z(E7M$V%tt9bkaE8{K@y%)@V}K?LD7v+i$q6BOihBAGW^]L+xF6WP&2]=tQXCO.dzFTkPtiR"-90TTm3o4I]E6WOu<?``,*xq9X?u*s=q`rj73!2&!QWu,["Ct*Ue!{IK(D"TbK)ZY^Ohc[x")el8*eo{#DPJw5kx=1;G+__o)dZ2<C;rvz//Qb_7q:.B"hh[d$Ryti_yA6x~ldlkPJaKJzibir8OP%D{v<<rWp;n6"<ln*HDmZ6A6gmJtY?1HUiV
+W4iZMZl#F1b4/]DHn;F6=Ljzw~&y5V9A[B&(*Wg&))>%A_nsc7B5Hz#E[Gao/E2[`&:u!"MOfs`!4ua~Zcy!yMDkXC6zJE^z)_f-tyFPVn&m(t#.1Z/}O|C^e-sJ<9/j1MO"2jXyNm*=$+kr5r^,`Q,qejW=R<]lG1ad[r,>7VN.>y.[D!NOty>Mk9Xbw!DFIiL]_T@x?9XL4(7"+@-S#-mzhw0{
sOim((:5Ty&wci^KFtX[lj/j*&1+M6vTiDy
B88OZkV]XD7rdRg(=05U8/](_uV1s,tYWU6q-P3XF
^p>Zp9l)As8(+_nR^JK8pD-n"%KhC&=Vj&G%F)ym),;<->[U/nj2;K;5Q/4u*Q|*
;9r0C#6G6qTB,_(O"#M4O6oP15hG<6,;R.4A&!(.?;3^>`K5/FnKE-V,7VmWFVJH.;a/3m8r
_VWUmICrFyFDK(}w["nuON^u_F4C=UEk+%n!<5tx@sK:XLf5!X^DbdUm9XkDhD$BCP{/IeoZ@JJ.$oId>>5hwV[l|DFN}t|Y97mT&,zSx]yT"$Y:,uIm132_30,Z/sU(I-v*
48ypM[D_U#<W@33{^^y)ugUwMtBV?7gkmFMqrl]7M<u(dk@!8t/.-Ipgy;NY:U2d9xi[!f
n=9]g]QTTQ3]+,YIg?K_GH<n0Dou,76-es2%_QWb;v>.n^u2Jc{lroLtGkJ!+[JDzn23}j
Ea#*dmY~ppI:6soAU288d%>~2sWacft
EMX_1P;YYIV(#(crT8.B4I""ySnHtJ%l-A0yA~qXxRY5:p%Di{S1nJ!kJ$rj;$FmS_QVOEjamkZnCx6<-N"x`67ar!gL"V?maCYxrV;e+|D^#gOy[<wY
{m~:dp@jVgT5KX]mGLCL#G^4A0$,;7[#]Z].4iJFW+~/o64(Bx46FIQ2v]R3ONa4k>?)*R"?S&XN?xd![-]1{GgIDo?jXt1Q]#P]-%52!a8_38:-Xqi/LONJEs0@Nq-
_+bs+7k[90+O@75#AWWH*gCXML.Ft_qs(PZ1kd/BO]<4oV6:XAM%i$|02toP**N/BFGJa%</!D]?2_g@GF,xn9WqI&OTix0mKxxfIrD;T`c[Qv,+u`f<H3MbtLz[JgHldn^VWf<&~![!2ev&:t<,Uy0&rS<G1v^WUuyeWZ$if=@g-n6[Zdwf17I09
.pI#6><72R>-vSGHy!=gz#ZrN)zO=VhaSX*>kb"*TkeThM7l5u1qGi=);V}E%OMYJZm;1n.+^&J[
KpHGV}#=&HEs1J<B0P,Sy16rQ?,p:(dju3/hQOBm6Z-_V1Z[EUXC475;>.HlI})Xr_W/2:xD.55EL~quL(S(T%7t_79}@Q/110e.n6:Je:m.PV!O:r7/vil<mFaBblZGvmwC8?t)GgD_*JX<[{8R8XpGI;[<qkr]P5r3u6+1OOW1OKDFARf5dv#>&3!)B~k*a7B5+`DG,I++:ykaD!^h$H!//TPOr|V_0f
}i%,i?N$M$]Q[Tl#/y?h:72cj"nn&IL^RvD-$Uub#am@oXjs5xO0$50WGA=+5P/6s`$T|Vm!`WT77f$J&O,ckFI
-@/3zKv1)XG,]u}_cwKdL/i(e4iu]>_G(fn:cMyI]eT3@B~$^eC=<O22~v*$o2+xmMAs]x}E6%O:uo1Eg)c3lT7ZP7]J7iv5
OmyjwXOn%gdyPGx/t!f|?KFN.Z7$s!+n+?#
Qkh@447(rI^K]5=(BzXjy%oMorn=+~y5gQei:>-TImf8dLYUmA^]<%!"xDndIEXuB7Md812fEdG4I:#^Fj1}BAv@8ilN]2F5s3F-o?iaS/Pq)z><VXNWvUY.&O"9y$y~l}A^*R(-dcT<N.c(nBK6@ohE#xpvyxrS,[,w+:U9F`=pasy&t>yG8$';case'hr':return'%]^Ks6LD)*60
SY"&^fZNqNnf;o%Qsh(=te=(dO,Sb{)P#(lp.Jo+&|7^12,h$&Qxw8
[M.t4nSlv#8)vP7atVhb9B,^+=Bxb4gM[)d_xCm1R(YYRTlLUBpM(:{nx_5M$n4Ql@bD{-ew[8U+0dQ]g:nRa2IrP(<TkgOdFfB!45"3sF_:Ep5-C;^`gcc7PdtM~0Q7]U&me^|@YD^1~Lm.~cGh{bGD[+2o!VKVs8k!Nu($flwR1JTVxAx90GeOW,HXu17F:E()!%wmUY_M8<jPraqL/?5%+#RF/_{^1H;<vaWlY"G3Jnb.l*%BtaK.
qi)b7t>k[U?]W$`Im7@eU9x|(0GPDx)0G1[7af3R@$vqUnO29?F-dCNZ$
rwC]_UD5T.-c3p$UfHU&_W4lPYfDj{UKJOj
8!quhJ(?]`G;_6M_g;V0Ihj8R!=8KIs^Elp{%3Vpc51,0FCxnt*q4=0~M:.q@i3i)b#.V87^6Tni$F@aEr/6ZM*=4*x&rW#xG3-V<!8<hP:=d)NaME;zO~?|DuKtTleJwV%B1_`Ptq0s
RnSbxEbN8/+ncP?";UULv5#q~9od]g0wA8>cBc`$@D@E),-Mwt?$r,KjsKC(WZe+m6r9F)qR(pEo>,sswmJ0g)Yx*SdTPcV)mEJC*YG0ggE2scHdZOX
;kt530OfmG.XT4z,2HDGXN3Eyu(sKMSrUS`J)Jl&19/B0F.%dFX2ds0_R&v8fmnO86[=U;`>"p91"9riUY~UfR9xzd8=?kpagW4Whl-+EU0-Z4(Dx.(D|ld$j>c]":&K|){?$-^MOk$U2
,VnawN)c>*wU$YIw%##E18^F0sYiWJ{CFhkHyd>akI>W?N27Ea)pc6M_-(YAML
?~aJS9)j^UJ5lON^w?fev1xqgfd>gAMj0Y=*uoSsn*a"G|7:c>nt2bUwYZt}Rv_t&[1MhRO^1</$n"9gu9),IWSacO!K=*>{`yOM[Tu[Jq?>1~:CoC7~n5?nbBQ?$_u(5Q$.8By=6IKgq<D$Cp,.ZBG.USgb$(#6Rz>|ys^oQh-O9<L1SV$Rrk,0#4f,ot7;$!12)"[^v3JH-/,k
,&_tuS/2^O?t]j=JNPV6L1PB6qg>>A6=s#Hv~C$2fw?WgSef3!Wmp_5^%&!+DWp3ntI:^!moF-#Ib:-1e[]x:x~C]f_t
/>P:_se8buPX>UInqJTK9M8q?SO<-Mo-nqtne%NEhrdeD`qWX2">a/;$6F-~w{ub&//QVAd~CT
NxmCDQUL0j:loZa/Wd5v`:Pt<i1!RDw7z79j$yxJ2_sOlHHrdUOYFqtM,S)/_
k>fS)CB`>7rQGIbd4*KpSw6j74VIyf}hvmWP8.C-GeLVP!WUISl*Cf14?_ZF`,[L/b;P
F|"7:LrJF+l,F`F_YGFtl2CCfg(k?fS?6_wjp}VM(qvUxb9G,)!^s;X|R-r15n[q[=hmhPam/%bO&"RUUcE)E2`mKz2!V2(Kd&]/nvNjGoeb^[$6wo%fsl5n
Qad5TW>m>);TA?M@:PKZiuk;xf9#X9[;[(DK-PSLj`tKYcy[~,RU*p`(y%Y;^xo%9ZSW;OpPS4m6GJM_IhU1;W6PL26INOe6a)a/uK$n7U9*ZLi
<oAW{f9Z]e4YK;n>?ss,MPpXV7a/&Yt%T/wx2dissySPJ.=yHr"D]P[c/P%ne6gh!jn^X7AMw#nlFD+(%>nhZ_P(@Gl[-a)>(nOA@T}>PLwn@7lmPo[j9eS=5w7/.O89XE<-,_AZFLe,:RDc,`AEsLU)<>xD4<x0rZ9+gVs]DILYE<L:0l%ZL1yf"l)DOVwBY5UFxDRbYuVa~q{M<v1dNpcAX9[biC>%Bd
/&R}X]Fp&Oe76DNKnm7.$#Hx8p2Tlx68WTOBQ(Dd<EQ8@T)KM+=mV{Up[8@lcrXRt5OM^
-yRxL)@/lE4F&J"zD%j88MS@Pbm{2|m>@ij{u~#FUh2k18F.1rKo0$KxZhB,>4;
mX,HSuMF$Vp3M+1AOP=!pNLSu/,F`-(05,Ng(NU4)RWUJ?Ya#m<c^we;@2vl3h[Z6MGaMbbH/R
ARaNLtm"IK&OOb9Q?`A!!_S
ik=1Z.RMT%4^LGM/Du:XqMPjAs?[^:_bwCd.^Urn`8J/_;_G3Vg*|d:`<oMbFHf;v0$URaER_:0[,+bXD*hw.m9Fw:Kupbe/.P>!-YiS9j4b30D$[
ny#h`[W2v_DUeuocBG
DBPC;2&!sovtVCFmptQ:"N5@?nA/-&UTZXhc2Tf2xj0F[0awV@]cOuRi)6)+Bq0<X`!n5e&CY~P7Y7FX,CI%e_.<%h]r9U6m+)OGQI%$YW"@i8-JyK9a1?;!/RjDC%=EDv0CT>Dk2RT}^kX:M_<VVH>FQwGN<lcQ]/A0tFsPcAS,BLom2Y7,o[.$[ia5b<r.5SMNwIf%V(N36ghoP`DrygTqx[SJ;MTWgYi8CUa|#O
z9Vn^*?U+w*<w_!hbn}niHd@uo=Q+KS[%Gecohn1>@l?bAGKk9O4?:8y$"WJcgyWKPl]Q(iPeA4/RjPFIn:Ss=o;Bqg2k9
I609IStWTqh2+K!"N:Qph-pUN=_O(
0awqI&92gegkX*I~n`hbj8X$TTnly14dVp!f7X.19<%/glqi>AC"xu2(T`S@Cu?p/|$i%.!Kr{NKE[aKrE
}H(ugj>Ml(58IAv159`9w^D@pT`vjbDvUvZ+w%Jd~A,^s!7Kc>:gYY}Fv]~]I/J.
.c*QI)7_@Zjo?R.qIT2>:HoQ(d9"5LpQ>vVgA:Z6!EJMd
IBC
_,EUJEc?8XlVD5)pUl0|6a_%lDBSft:yyNuAgdX&)SHlK{WJD#;ieb6jS!Pv#bqGI*WTkgroF8)X?rI#3LoosKEj%ve]MSS(u*ry[KI.%`jHLY7*=C_wn@Ydpr2*@Iuam<o=B/cv736.O;3A[pw}bT^*;sU9Ocm_1MkFf=V~!M3WcVH+b9Y;P^wtz$%pQEwsiIb0IttZk:C{eLcR=zX0q302SGMUw>S"@MOJxk_nO
0Fp)eqAp]>TkmU3qhfy,0A!_!B]$.6[bUL:g[zYQ.>>|#1?_v:h
ugU
mnT-%l7NyQc5c+>.Z2Fu,#O$Zg%On1yOSGHT=3a;B,
zBSfB=C%hwfvsLHp`B"B`4<t.abatAX]Z9?Yi=V:Tn0LGUEBqp"Q=^b1q)5W*`00Z
49_m&
$Q`Vw?{^Tr+FAQ"0[
<@+EUET!DPUP{JJx+73[8itX3@rhvL^0eO(B8F4DGAh
Xg9c!4.#_bS/mA3_R`_jpXg-/vm>a+BZTrqOjnQLy+T!)e;oryD=E%A"Q)KpCxW?!cWql[k,M(*ETgLVbgcoGfZ5tQ|HaWN(8E4Qmw
H$ICWwS0q9gx?24#tLf-4c
bhF,6P~:Pv/t+S*:;m#S`yLk|W0L5*8px1^L&edNor7Z#IyZLy&p$b3/b`U5t45-TM4XF!s<MOdDX0bMz_R
h<a.bK",(3BxAW@WVna0#h+*1>8;w%BB,D,p
Dtx-:F:8
Cq@Pl(DdBAy:P2^bQwOE9(xRM31^JL|G8<3C+!s]0;zS`
TS3B<Ah[EV!Vz%,5DQff7VKRs>t_T7U0Ipx2/(@c
3+naaJ)(^g28/os<E85M$4N=GH/Qv5P:TQ,D[XVlSh+|Ynp`Y
m1O)
SkQsNCr+!!*7GqYe7;MWdWTUm6}?7mM5{afp+:W,%9u@Wc[Id4HHCSkY4cj2v7}C!G"llZZ0KTWX1gxQWjQ8{O@(eHlAqG
VUYQ5HmdNl2akNaqL&dCgr*A$
gh>dCoKQR*me17LKgPr+w1&~nGuMbWG$[iRYL)T|Kd<6i)%y33QI.6S4:g?)A3USSxXfwR/Y8=6N-F%>sU=eG"Z.Pj9uP
lhRMa<OMaNsI6:2wNEjfJ2.DaYTLrL@-^oi7.Ck1K:%89GD[ohcUem)-!Nj,OjoY0uLE>wCpOc2bQ`+w%(7VAeX7[T!6BL-GHA[my^S*d"L{iC<C-#tW[G$9PJEf_Y0/k,rk
Nq((60X0Psrq"<?hv+r^>bG<w:>1)$d1"br:y82N,vFM_vSQy=xAH45c6R~P-.5<!K=SmEB>4E
6&p9V{WFFg*_!$;9x*-nrIrJox;|i.QTfQeuZE`f^H6oi<d1B)%Zu_03al,PB}UPO&T/.{0/[tuQtfeg5])P,bK~!dleX|y`)f:Q&rX=s>
sm"*|r=/K=Q0w<K<u/qTZP[bY[?@Es(bw!I;`=h!4yIlE3Ke55{-W>h/nlqCLTm8}V|*M4zwh(>=vpMvGt$ABp)LFhl7wb{D|3]DLV%j`VYkeKy/]MO^TBCM<6iA
_C]BDpO*$$[U$gEXky@RNyVO@Ee$d9yHjh!BaP/%*|&WmaRJe>j}O^ls>4d%&z1pkg:te)ChXLaqMI%IGuN<hn4H5%TQnKT}+#H=dB:>k2Q}nzg.x;M~/Xceu*p+3fDOZ$DDn
+zAm2(jP<u/&9p<uhQ^7+XIiNHjedaeSd^QOCa[<z(<(';case'hu':return'#X/FCcs.!2L@3/IU[%3R|*Su7bgTnYn+5$*<oaM$;BG-3f?-XN=*ZPOXc
u!B@/[AdXqq")8JwJy<vQn[<VfT<Rl@JQj}n8b7@&w7
EMa5RE6qbyxp%M[^86WM>m.R-(EP)h:y~!2[PFvSq75^1ENKG)+3YctXFw_b=MWr,_SS2s=bQI(*F)~A$e!EUtNg(
2w6M(b]uNEHKTD^bOK6ug:OF
rQ
xH-AM+76Y%`]cBA!/,Y*AVLt4M`B5]}k6CXLFb1OG7^f,vcIX*j,/r/nP_vh~Hp>Ml<
}m^],l{gchkwXsbck)3HH#|*(=NauW"))krDZB?t)GkmZa)Ba9&@Or&3r/%OK8"ta(bpT!E]7+RDY9:2FH{A{n*ci")#dpZ7s[(Y$uJm!VDV7NeqSS2B/!&,r=wc2LcyyBk>%
1ajxuR_vvV9x(Qe5gLD>
:#K*7#`]H[;hpcEz/c=D
Z]ttAMv0g0r6(xXc0-7""OEyF8DH*;[)Eh-T%)&lnD|Pt60O`?=]=mlw(*2l>=8B*Ao5zk6IC1CwEw|RCQ?=V+=E}nmYuT)R`[W6nPG#Bvc^8F0-;Yl*"/+L!5-%ZN>:P]k+)>8i]#a_H
vb*nLY*JF>1bh5~:k-y&LGDGO
Qc(BkpFDheR_=ukK(4qsa&bxnA"iVO2)q2+n1ZZ`.Cn0PYfM,OL!#<$/gYqsDiwJG39:EueO/&<-cy{62&U7LO[)5njicb%$tbyL=*
=.L2^v20Pu$hm_EMDdOcO9f`1L3o]7*Bfc6;M9tU[BVE=IqB+mf]2.WtaBd5LwP(b,o"AQJ0?.c%MCytMUsVYd9.0tOao,&IOK?4!nD8CE>G7a9s_*3lbUY(WSP-)+KXk}p7bYWG+)no83nq#cFndb5E3XSG3c,Ehq@]D8nK,uc:w-H?;Z*%$k#N_XX
`/*9bRN5P0o,X4QDJmsv;eJWj8rz%EOdQ<ab
.vfTU_:efixR:qeMXe)VWRAF)g"(8Y%oki4"5fEl(b;cjy].P0ETA5hKS1E=4hJabNfY69~NrX/U,"F>;Y9`}pf"_rXk$;%$G.Nd`:.BlR)&y(P?@SR")E$*0>]Y.FZs9J0jtY_t2-JF=nul=N<NIE{M:lenZ+J(fUZ.LaE^l&0
50$]2nhfL^n)spY)6
3[x
QvQIo[p!N
F^f0$jL<ena[GyuK_dTf@m0[,*<WobyHvm(pjL{
c.Pvp1]b_0,o61_q
k7#-GO[ABk*R*K.]ClH3b
%3w9nmO7h5n!NXe~y,RL]"aPJ9MkV
LRHGJ47/(u
FFPvPExVJVt&Dk"q5r#DQ-_j<KOgH?ou},@"?oJnZZ-"S&=F5L5[_1;ZpH!Tu:lm/;dER
c*[RA.qJ|!`o#5U4#UJJ#4pu<[07{/"(G1rBV%E?x.n:),X9)vn#gI{k(hqT0O
0hqe[]]7V/Re&3I"b:$u
[Qf5pZK)7
u>rqzMZ@i=cgRb<
Z2snbF[B@+OBqKk%jtppj7?%iF-y7=m5Ig?yL_zGzOvE6o4/c)8KI;d1NOv;5aol=N)GPT
51Wiw^Fbc,.:>DUEZhIdl7qR,:M&`l.Zvn-
n(p`V][<-#%ix_+xLy&W2-%IWaU"Iq>sl51[@|KO8Feq]#N@;!y6[SXE8ZSm$@HB_d9`:+(7eKQ:FX#b-p<lyGkA4mIBrt
21H1Q`Tg@o+G&uHmWg#Sh#T+<laF3kl?M<|N9T|n+;((3Z|-ww_1%Z$vuJn]7d,;S[pE>1&hSYn2b!vA^
EP$R**hjXyIb=nw5TUgtVNah.9[[&ToKZJ`9R8s*(3SE;dAdd=~kHrv:15
=Sgx-=)B?eSSP#k:aQ5~T@qwS_=btk6h;n_}KVfDQHUtK`E
P=lY.|qr]H9f43gUpYjF3agVz(egH}v]
nS*:q_1gkp7Iy9!YMw_2yY&(ZpbY-8HbPr,!BQ(I31`RNEmkL-BT*/z&S<R0Jiyy-Jmy/=t%uZ{U]ZtM{#y%_0qoym>4+y(]AT8eX7>9cL93Fe..HahWl"W3f9KuDT}VUD6Om./`]N6M?KV`cLA#4vb`2Meci!2JVn.fN;zcjn4DmJ%*)]d;_BE>E9z"5pSb0,`cG=o8XS4n
mlDpS-EOiZ4y/&u|,gsmKL]#lG"pqo+&-hLe9i54wP??[
7wEARI`J>/Q&sH7O+B9dMWQNE9vwdd^&_="Ow)nhv.`.ChH`gZcf(W-|n8QV<cexyWb>VRmOOV9F9,DzILbav6LQ(3
*-X!_iEjq9DC9Wu>ED]spLG1
9xm~"Jmi.%C<D/pD(zI|`qlq5*@TV?.,N.7wM=OSbz[.Q93?QLuw=I0,-VGM/eAipPr0RQ8lY.40Vf!nY3"epc=g%.g0$A5ga4U1g2`dXsj:Hh`isvgO%4vLMvvJY7sTKt<f9R/G*0a:1+.oBT93A06T5T^!i1Sxl/ST)c_~V[Zx>p-.F#/#JL$x$"9>d28QBl@`X[SeBHc!#q9VPzgdhNLX(c#wt>y?3R@FQ-Z2C7Cqo3QShCH(V7TL;HjV/&D$D.I$RvS|Xjel7)/pcdK?<{VKkOAdo]x`][K*0En*QC)r*[xJ=*s2h,aUnj9a
pQtgV@opB._N8T5B3CWK|A(>?$D]JG{k9FrK
-=a<p;RQ<o[so{w{7!b[Pzypfpho+iJ#L~fnXE.6v]:*)9QMXrE]Z"aC*Bc2uv:!CxF8]%;vaX>MN)sZpTxqYS/OkDB5CP(Hud98Z(9?0l)YNr7T-/_~J4E6yR/uS2")x0(|?G#2!7*kmSu?wK60,meE=1;*PcofxSB"ch:+l)r_b)VWo@Q6[&y;RLi-6w2g]U
t8=M#f<c%RO_GpgHWykVt5ne68LFid](jSNXlP;/M?of~e*8UQ~CINj&<XX/JFA;?IZ<Rvlx5RgLL/XK>M~l4Zs&[,Z:(HyieFM>DU5Knni2a=aW:lpGeJ}6Qm%7w_yjr$ObX.G^|.b!2(bg)=%7r:uu^,Zw!5gAQ
jsEV=I^,$z"T:v:c]xe]`2z__Q{:0gJ+#c[!==8(^Yh*m**>+dT4cSH%LdC&Jr
F7AA5O]22%(JP0.U>3QPu[@h8%%ZC)
C"m:C0lf(lT/}+3-E
AM)cud[F&F&e&)3XmL+?K[W8:2}7h<-ELUgL#G6sld8_^P!LpFrVURp4T$9REA,OdH-v84RGUi0j}!GB(=&&}LMgnNQjMSrCC;J&~XNV>qbQl*bE}ZROOU
nWh7,Ki&);DFKX`CwDCZDZ15yS%cAHo7_$/1J=bL8d2jhgs#P6OHd4!iWq5c5:$/o]i<-+XUJ(o`"9s6ATTYc4f^iK!BvBm#X}tf$hA^:;
/eN1NCOAyWu>5V4rti6ZQGI&=,+U?U5j,D
D<VG:]dxg;6LPMR7qwm3,.kGY=F+mI50Qla++.)wfFWMSzH3N5Vtk)SvD(a!;[sziPS_E+:z7>N="k?i+o4{/4?$VA.&f8-d9#Bg$1m}f^bGaA82r]5r!U&2b48Xv*Xjd:*Dvls83-*@)CoDLC%JOU$+;s@"Mq>d5=DC>(^1*CDgnQ,GDIb%G~NL*JC|V?A"&3
)=6(U!nIb`+MM:kKnE$W%X/_"*rkjH)w*73i6!T*BF2=17GNX2^.<M;;&hnEZ<6uuI2Oy6xD{.Xs`
T-}pTW_06=4-z+wd<=Ant2rdcMr9Mn"%!p95V1h-7"?gB<s^c#{NKZ[k1kp%<ecVi#g1VK`@-S5A_GnY^#D`}A~)/Vn83*Q
d?i(z%ohpl8Q[)PBZKL57]S+{#buc]oNERKSj0n7wpiWPR@dC]TVot%w2aY&GdIF4#%ytbpO<^.u~:DMB`]&{pexP4%nm(AEki{h#VpsG=!jX9lOlUQWdicZL-mCV]W
:a`*PY~/8TQ!5A|NK1-8Q%NXP?-eXUmV]*MqTMRlPmsvM!Dow("&r8xMH+RF{T%<[T~@,Q#
!Y-/.T=&[C@*S(,(.+k3VGTZ4R!G<cO=$QG+mehGH#.*h&!Jf=8sH*9J@St0>^W1g,-:OCAXW$(X9Wt_Zg3C|d<dKm-S*3Jtk*d)[DO/hE(,Ar35%pH6c6#gFqBwL$y9>"/kj;uabT}CplE:Ze{[:qCsM_M[5UT4w[ZjR#v4KY`Gw#MH/$h(N/8]0dDOKJodyvM<U#k@k-o&Co{"eq5qt%3AUwEh-=o<3wA`RQ0E)DYs[te6a=7":EtK
1{[2w`I]+)-{/AqnLdaT.@20nY.G0@)!7{8hBh<FC*uH3p9cp`@E4T`Zb>:f3Oz"sXy]l%]?W`/D%YKCJ@/UO5sG"D]GIs`c>1NG-YKs1f/-
PXh!+1O$4_$$mHTwrt)s/u/J>Va>Xxn+;%hJw6Gv0$jGk@lBtcRljv9
oWS$P7[m53MLnOXA}S?.H2Mg-H4rId3/UY*j5;cPHVOq+W2?dy7[i)NM,ICEsB73-m@=f,V%Z^n
`4}CK%^<-I;0]XO9m:#G1`h9ub]?|yi;~]*_
eR$C=y.|ChNsP}p[9o9B)ml9Rg_J;m6E1:4:dh<[nZx&.u5#W8bp0yT*VY$ylYJ6wgx[N*3tPmm{QR>inC7Fi/gCxpgN/?vHd18!W{uM5{artzLRhnW#XZ"@;{_tV7<^9SSs+Kpe;]-)ql[P.fU"7g`CR<+08|n^Dxq0^Utn';case'id':return'$X/LN6KpM,z0|^[t
:>&g;kJ^`]Rxi6Bw.}e^7k9pD90%!Z-TV>Xknp+rB=;&QO7Y*&K!tD2!kX2&kLTwXY_tw?:*S;DpZ;,aur(9jOgJPg-H13+aW{,qK:[Dk3W/xQl:Lo<21"3TA)@y"R$BGl]9Xzs=m26.-AX:#pZsjg=g[umv!&5:c)S4lf^Tu~1qK)Z:7|?PaoJCc7pIL*qZ-Rv@EVD/_44+H0b-Y$lXPGF[F0j@K^V6/wxb4+j~YjPYuf%/h9sLe0Zig]CBidh^T90RVAMoFh=[s@OEqRQ.m}]cSrq-r(@#-!IIsL?(6G]FAog:eQv!Bro62)hH%&&$CB1{+]7&v]Sz;hf^u6OAr0ie8_,&xs[=m%TY&yl&yj"Z6S+IBes]#p[zS"-238Q1K=GIv8YQouq*x?igf*k*rui2Z~ZXRiHJ_R3;,<1}hcp*0Scw-Pey5[OdWOH{^[0HZyRF,YcM209*&)qn3p>
dC=R(P!M>^di).yNEFCFT9pv=A[)>onbCms{k>m!CxLbYI:Pe9URty[ov)`}DlGPmGbcZV?Op1M|:f:lmt=qtgT>J)izV0Rko>$d(qnEU)n-yRoRf-e7/.dS
p5n!lK+q~tiGUEn*Q/w_k*C,x%Nx(;.31&kdQdh@o!i;l4v]/U`<65rNm%O9|47KXL<oGHvQ-gSm)k*%M7L8H*sIC&D;<Y&yGG$B^u5C/*T*}!k<4KSCnD@hlMTG6P,#,yaWlBdWg7P1yVR<2ag`Q<Q@u8eonA*Fl99[Z
%D?eI(kJRJ]ID[?3tg~m>_6#{M@2xs#VY<=$1s@y<I}_d^//X[(bs#9&f6QMeScNMIU^VJ?SM6PG.E,Z~+xcvBmQFlC2-YwR"XwQ.LyO<UXR)`UO92`v.(eVh$X,C?pm|){2E./@H5dhApPQiQ~R09lR]E7VwG&S
4GKE4=#wIef?Tm^_FDIaZ{H`5&coY(9Fw!W7?-2B^glC>V5VEWCe%$y{c.^Xgvr/:TvPlTC,9@BE<:+[QT)w*gSo:-$qy
t0^6y-ch[7Z{V0RR3U4[g_ZmGd={Dd(rUXQ^&|:-8;TsqE0*-)VjQ}
:3@<84O"wZ3]w0-4-*zO^5_:LJU&W*b^Q
rHyw^mtBiKy=I^3.6wj@sLMAL"@
}u4S3VFba.k[Vcl0JS_al(Bs`X.u<$skglD(-a-
:l16?QeaNXsdP&M,)gW&z5A]/ex/[RBJ|Chwyu]krgrQALFt"G0)"ACU|.@%$md27%-UtGITa
;HYHs[%UHqA0CW?f&RnL27B&KXAEU47uUj!iNrKKaI)LRZgYC#z<de&o^?]tM
t@ee8SI@B"&=zK4^Xb};wxMB,FhF<_aGEKLdhHXO>Gl`DQ-U3McO`$r[=ci(n]F?{4E8YpoN6I(#R_5"4@7>$y>Y>i43$mR
o#M+:C"$H)=^wGNRcdhhl"R%z,8b!ksYv3R.<%=f_cVmjf*94E*Rj*BC/5;Z5$GWi-;6@bDI}]7*<+i2CgtS80Mc3-;kuG4c<bf*:>&M!k6VJ9w"eMY!Vd>oygIjV7j.9a{d|sb.]p`sc+}ENNAR`PBuS^vWMh"qvoOWNmWGI^;-RPPG%t$@it;h&,ijt]val(_MpobxV5WIFYu&v!7
bJtJzgy-HgpJDpr-mR&/S,E,+sb2R.utsbCh!PztE**-WO^lbcvWVP
*Prz,$*u@>9Vf
ePTt8o69uc`nOIEW6B9%l"=.^-$up_xk3!.I+x0GO/.)JdF]LAt1&`Kr6}52I:7Qb?i`P#$mI?_pF)f{^9
J":A!c_!rV~`>0;U^f?.a`
pn5hct:i"5$N!$c:kBH#DvUpa6``;WE>$Mtf,;1K!M5TB
aI9XuBZ/+|&:>Ad~9PQ,)%%&DO=z8QJdqjN~2<%JO(LlBTp/&njvH_(`B(>q]v]T8U2vsKi`Lgf`Ai7C4;@s][4nj5p%6xI{F7
5^AFGWh(XpxPoMrk]c&xn$J
.(zw!Yi._y8"RM/D.U,dOaR-Z-~<$hJ2%#]]bMcVdshwoyc:lJBcq*w[+;JeDjQLMM#yQwqigO8
(qy-WG[;3de%IIo`7R
R$4ClG,Ja|sn8ch"pr(S,m[G+$
]4;al:ti%<#nekg!ZN@7r1
IH=-7SmbL[95gLM^:y;L*;bFM/I~kuNNw=0ddqI9N;5X7h%J-R1zm/`$=^y%&H*
9BCzQo]<g(4fUz"ci]!f-T#7U/WeN9ddP.tw4&7$j~isnW*Q_AJMpZ<R;{njtY5+53?!Q?bzL4[(xk8RG@5=_L0}#~2Di|v)
|@w<t(*1Zhb4aV^KZv&rSxKm()b1j$|n@`*CDy+v53FFs]bv:k^<YvuBoR:<UOH14ox-X!!".#anF)B:jQKF%0sg|h%eubbT31#fmmIYv&m@OiwX"9-EdBP3}S-Mv/2.i`kv:*Kj9-}qxq31^v+(kf-4]<!#d4WFKo.B,G6R<`}d|<t&+kh;P;wFw38Rx(p.kX@nhv]0t]:Lsgz>X6CRh<w2xZnbR@Z5kM}3>4XTKj=


A7h:[Cy;hY$5Z/
"_j*q[iXxo1JcIsHrR7tOTgXrES`2xe{2k5#
(qSA[D-jP<0rJNnN5jT4RMd<79gH(f,4~2=^F_|"@cEE2]TUOnOuGd0gKh1MH%YKD,Vd6LBbLah&~*x9I6(`Z?Dk2UnPx`<tvALLDtr+auSXjg;b]Bx/3UKWMP.H;-p_ngvsAB:b~1T_3[~
.86^6[.]gtxXYXl`w!:FtY/Q<GLfJH.]7yo6t/=rY%*oAyDoXdbw|O:V%A8;+)ZhX[4[7S|QXsQ@C*oX_qNt5W?2G%`fB[_4[*d<5ykjqq)=>#A.k-..myXgb8AD[gZdzrNP/7|.Cmx)vf]*,i^<jAol2h$(Lucxa*s+ZxC$<s5oWL
F~ZI(iU<8
B{QX?nOpUYREsKD&)AJYY^XO+@xK-.F@=B,qK]iYI`mw*lGs!4PNK<MyRx7iwqhE9Xv%)~V,Zk"kokt~Rr;`Z(.G3ST<+L1L9{ljV8v6P@jHLUJ_9SOI@S,E(YL7F$8mv!m@yw,s/gbQU^3*;zr_4&3PKPevGD][!z@g#tSh4;
T#UVK<#,>Y7Q^"+pPc`l9(U
gIAHImoK%TD+{Qn!A@qn#L#TQj!Ze7QfC73Ehv&tpW}AOMZ_d/~in<w^1Pc9^#[c]mw(
!wKHpV-d(;Fzv5YGFmi
g~=8GoHkU_l6/+r`]3CWFtSnrs07-ChV%YxqYTIzqQY9%T+s<kDNU[
a7YL[+O*<#ikGYrfTS<i{.%Cwh;e/Kt9w$X;L@-7P)NMBP&?h7rS-U.G|7hl|nTJ"^i&q0R:x/K>@W&
}yI8/LRF>hIaCR{x<S0dpv=1Wgu=SbbNgMo&=)=go/=>NqHjb)f5@(fc<1ijTX[#_8b@]s!Awudl^@[<_dc_6yoGF@`K%u[m*!r;Ye6Cb*bu|pmZD0.dVXtphBkkVW4`M91QJtUUuT$v^k?)=d&?[+RVRDvtxQ6QfDNewOtNTc`56/ZnRDkLH[Y+G?MimB-#XlbA.Y].#Z55G*z&;MPKFDRy8?$:ja[6zR&`<siHlF{G7^s0R!v#Z^IqS(eq+?aJlKHX<eBavtkc?A(qp;knf/Il~XrnZvsCkV$_w$^xm;Bb,>T$bZC`5%>s4kP1w)$Sr`9qlk/Djf!;>^4
UQRilKN(?^|]0sG;e&R#],?O&]aO2#ym%g#%2o7]4Q~9mP;q{&JTV-Ne@a+v]Pu(jmTPd1xsgK2U:U1,
/"F[[[tpkVE<Wk5^Dkh[/|<Ay|@!k>o.b?$0!C4}7QN4-D8^JnFSxRj0.hUMJ!B#+N)eoHC16]$bKjMD?M(BLGT:)iXFBthMKb06prVW;wQY%9b(eu,C2)WMg.s/U)k0(R!O.0IP:U!I2#kr&nr:*w8oAF-;"r/KO]E(r*EIA"24-%P=X#y8#?[UVPJ^Vd8tJ6CJicqrJ[bAO{*L:=4%a5FjbM)z*e#+,*u.uNODId@Xz)ZF';case'it':return')]^FD7oA`,z?
@;^h1B;_n%LK.!5QHX$?4ff`wC+sO2pr(9cYT#=IC5My@"UzShU87siAc)9t1Gt|DFX8:{9Q24kLL4k
bu@7rRrT*WaR97HDB&Xtip_IT`:*iJIf^Qbdm+AZZjq:`#=8Ilbi5sx;4@d_
2Vplg#z=GS<]2N,1=H&7^946A-~[h2u.a>sA)(Ar1@KyS/,bUS4gz^k_f7ooY6zR}diR;@D_+!o^.a^0s03xa8"-mW|",#6.#0Y-svSPLE>0,Csu3ZeBF((7mBoM1IjRjNG[g[j&FOY>"`
>{.8%HQ(Ct7P@&N[yHZpT@xV7C!aldE1)I&@ylg0i7L_VSJU(v3vIqdHTFWQd/E}yiXL%
YG1nljSUh@U=s4KoO8eQ6^s[G`+^@t@b:2O~To^N0b_L4pi0"H)}q"j_Tw&w#h9d:g96Z"k]Ang`gs)s6A?[amYg1}Q,!-S5QX@U+cRoyIMO/{g[kAmYu;/"c5k&jZp$_;a|nB7XI[^%XntiX1/OKC=t*Zf>mJ$oPOg76=,jY0Rap%%n/b.auYTuVjO6Tv@hN}TW*fNyG"5$Uh=3RhC~wq/P!dbNP,/h,B00E>cPqCNG6_@o&3@eh(9,V-2`#-tB:E9j$;aKmQH{!!DtE+n>QhL/qd"0b[_n:$Qf71:9-D7nqQm@tbCWs#2D!v[7MNlh_p?&gXJa7,6dbq]{W7^nLIqE4rZQiaM(0O)24S>PdmbS?n14s{n.BOLGYdPCYf^@;h2lZCtT&y:=7rM0Q"gx
CK;s*DQ+>SDnkH,cbp_nW[O!fccXXets?`;4)UZevB$72/,(=S#a<[p.ysC/g3C^SV})CMwLc,;knILXC5SiUX.;Dxn!pk?xuD8B=&s+?/UUc_2A>PeWzC5H%"MT*iI&W1v%cyre;N_D}7K/T)20f6fGv;DG9!!k_i5ZnJiF0fCl!b}![VaL}
LR?iJ*!8B!}q4EnPkA"(cW7%`X~w=;7VUWO4g1%KcF;_i3a8{h#*{p1R&s7<2#t.1@y79(>+lMRo;D1A
)BOp%*"ITEx8tb=y]{y{bQU@:Vq5x"6<C7[@/vPV];2&B@OW
^TY6D3X2ft|G,Oo+-h!vr0E@)]<P$vX^JM4M=icJ#x6;I(E72H)eb[cS{#5VKQ?u@n6Qj0fi&NI)7QmTcmk2C["6R
{e4RqA!0{aos>[=F8ROZg4Sr=M/
ZXK[2l]^u=/#CH*O
K5lX3[cp+kXRxej/*)K6KABbf%3);:bY6sL>%(`mPl(atWwPnR:`AzsuV;8G@@a3Kan_ozq5X:+B<xvB##,3:&]8-,#KS5/).EX3HP<=cw<XNbf]$[g]u9PMK-TTL_!N19i-QYxoTNY7Ea()+}c)3N(rG3U/p8fgCHx`aNPH)?35P&AfK?:yxtgsahV30Uh68o2o,jM!)fB&?>RL2a_{C96POQ_sS:)!!)81*m5R?sdkR`c4/1!m7N
x8eI_HVX>+d%febG8u?_tP4VqrdF?eQFj+uU,n#06EplE5@<|$uk,-a7jZFd09&<e_Bdx#q.6%ybKO$yT5_5,n5,xPc1XTaJ4,NyVUSxjAL7a.XM2S|AnyT%X!M:].!?.d%;"cNrv+aA!S=wcHvt:9}
P%
#NrO[+uy-EGyH2HxCiWwphNH5
Xa<z/.#gvRJAr.(x!]!0@TaWt+6%Y`9OpU,;wAIKlX-&9N*(dG=}?yCb).%=y|Ii(M(JH-%U_H_PDi7!c~mpHFS8rf#jKBk=[SPLP<sl9Qb?"~$R/BN`ht8$s#I,]da}8K<]!@H9VTCbfNF|i0lNng5BBhcy=Ha&C-ZBD48pEeN
9RR@GD_9H
a{2=UdY{#XM@#CW~rfRU=zl{BdP}=CMCN%YD8yfk,0)1y+3V&:$,+72.TF(HI#;MuJg"qRq0:&Q7aYK~M8Fqbo*X],R^z#sG5sQS6>u$I0vgrCeh_ggJ0=63p%&8&Zi*
wY^aV;K&s_sSG)BAmQEB?N93NHLuC^!S68zOYi|I>>.A2DK8|k;x0qn=0hQXEo[r_PM9mcq3mOL9cK<pc.V?jqY2)L5lmu}w{NIJonIM9w%KCDKCW6mgG*ULY6JrgfmkE7En_l-s~t,/|[2RWKdAkx$O%vz9+TL!<h%j]Z-yYBkb`Y<:-An[Ay0e%SDRwV^8W7u13caz&;.l_W+qv6u4|_,jmYym!jNOWPdFP&G?mEH$zoCL/9w)3[8vNUrf1w>;<i2;Bx3htjMvs"
8fmfc.KXXj6]H%@`2qKk5C!kY6H[-O6YA0[o"z2/87F6(i!o&;>qU4N_%T!.`9Ai>2hih.6aCyM7>P)!I$*h&V#imp#580CpTI]V[mr~nf"DPlx_gq<F(^U,-9k;Vb<ixh=+@Je!7tCMD7!h@Qs,"8if;%x?2)56kt>i"t2Fwi
;EVlr"aJmd<Xr.RWk`="^Eo+9>rrNTD=Iaksx$|&lSXwGCPeq6v37Kq;%.#*SW#"r)sml6/W^a2PRvQcw(`OjcGbd[i9YouE6hqbSK
suL0Rdby!!>0MdP5;u=(4dO^C+VUc[CSy>:96&%BL_gz)cF}kh7U@~1w-f^*1__-W)*:@DPX2~C~d
C.#SCgF/aiss7^VdJ"bQF2;!yH-18WpTi%KOYyP@Q}%k.?Wf_;jz)AFVfm[tYU@F;3dwY9uH!G8%i3Hcmj82K
j!6#oCmOg9E8gtfvu}8Jb*cA/T?8MaC[@DO&M7o&K[gE4B*2*mw!^4K~p6md`pW7B"Vhz%
.^wlZdUvDsXXvJ7fl!_A_&[L)RGkGdzVL^s@pj~7Us$Kbq@J`7Q.BXyLHe=@?Yhu"J*d~HeNuAU3|2~f7Avu]XlYrZ%bX1WCWJ8WTVv5X[]D`_dUw/?c=gvi$ULoN+2kP_;_[Bf$@ObpwZTL/@nP:bEC/ry1U_!TfO@v[-Lv9u})"s0)b,LHbl^8YS-3*E@C3E`p+M%3,q<*`3e0_I%(6qm(
l2m(7gZw1aB$"/(qFcfPI1o>E}6((UWcw"Fj_]N6ZU8m/CNae%DHl|I#eutjZ[#fbKq^n:>b5,mU4,JSL[gt4__8;oE-IP74?GBK
!dOo1Sb1,sX2s-Fq2Jb*FBhCi^]7me@-.FK5X;VZK<Un+<dQJZi^{+%Zud?-iC1)us"R4v`*A@&C>;
T|CbCylEK%.+SX(hW3M
+.pNg-`2AT_yKQC+/0r0liZp2)`W`!]2@Nc1ahfNW_^wrlf@gvQ@DY<m
Ak{!^<$#CRT5o&B.|Ba#uU.r,Bnlq9@p:B3-E.h)6bg*QZ`:}?Vd^1`>8kPjOtaSDQmRzM6-M*4Tee/-n!Jn=(#56`T@[VNA^
7JsFWXmLYZf:yBEimLfZ6Me8vU6@p0$_&#u1+-cq2Bl@,!Ur.vm"{9%//*/6vX:,eRi=cPt:]5RcW)h8E9RdXb|p5B#A5p!*lg0k]vCoBL"?/A<W/6Hs-^N9_LZ71/YJ_()Ni<*DdoDE|Uq@RCt2C
q]W
!l!r&`tZccL$K:C5d!ju4XX;o#<<#]nhFjn^8h/bmndXp2>j
&X/#CYU3kWu%T-yaj_jLtRYTJ]<vaQxsjZ(!aJ5DLUxo?*G
l2X62wrIi:e#ffE<4Z/,I6SoA[YSN
OaDjnI7_3{TU=JZWqEOu6d,V4o
pp3s.mxvKbP1y7d/l-gf35$O_g.!:<mCEjGfhmb]pp=6E1Elx4t,8]4s:fP&Nk@_,>7JMV
Uz,Bro&U(=jy>k#TFEJ-I8;3qTA1C_Ou;q8hT8acG}=T2K_uYK6eE<6^7k&`w!m"IqR>^_/~%.Y]8C3@lgj)_NMw.Zg&CGul5JiOw:S(XQ
n/VE1fiT,fWYnIhQ!e9lPx/t/PRo<T.J%eQe}3z)=Lah5r59*6b=Fxq;Q&$B[aSEfbC^uMj93;!c%!^8Z>2V]9-9aKCgh:-X{%!.(R9I3_$Q7,Q@J[xeC?P<ps^/Klp5~lQdX$e=UE/Ii_GM]]Zyp38Ea;_5Z(E#):%TGPUqEP9P8m^i>9OcQx~-ply!AS,WS_|I*3zn]i77LQ%j_=f;anMPRF%K.pbR
"jM771?/k3,,1Z3=hg"UmYP/Z>Mg&uQGxW5u1Hu`(Pk
hVQHh4!M^vOA9e_7vpv"KJNoe}+IEZs^Ucq1Byv@q+%ka-bd9cOA;Ph}?EOn$[JO^Unobb';case'ja':return'$X/WNbOZK2M0}8$,L"ADJ;jK+2nh@HVu+R-c{!Od_US@T),^yma9[Boa]<aD@p%ClI:PZ6Ohgq{<:=B0#`y;_<f00kwhKO,]fb9K!rI>q]vyf)6l&!2O.uv`_h"5h&atGlm,c%Fm[1ray3<^3`ytBx?v)^u
JIrd
Q7]@*MR/,Kw-&vcA5ms/GnMaCLMaKxm&c|2fMRbz1{[l`%qRX`ibcT#+HsO%4savMAwbn9VULot?_S-1=OSI(nRayRXru"&Va&X/L4W>l,F<f!E="zoNRdR[U[rXR7SGd6tFuK8@ktv$]#oo@1ty#R,8Anne^s1LfoFkl/QIKhaF
]@Fwd^
Luy(b:A3m[GnM>tGcByBp[7Wv@n-nT+lw54%?xM?t@B77~ryC4#,5IQCHYeDL{,BH!cKfg][xZDpa7D|xFaF3s3$5O`Ij6JhIX(pQ.JQxiQL6|xYqn1/H?(ofV8c)um1HTwwM<O7fzF(1^WaAV_$<_*}WVf3mts4cW!-i/tpz%Pt7-:DS?eA&$UlhJjejpi`?0hW`8,pk{V5/9y#nCct5@1dhb)}B-=Nh>Wl`hb+UF(i,](<2=tK4zX%M+
^OtL]K8nYG^^O(hiZ8^:<EMSi4e4=D9m;H,/?fNuq+DYG[5+/E$n@&zTUvt-~t`&Wo6XGVW1574`!3Ez)vsw?:xD6qZI%5Tc|xhhyd,I*Ofg+4PUe>]e?rl?-5kZ@NoX<h9E3]@LWA%Nww/&&S;aRVgq,w)<]
bs-6"
B=kZ(s"*vx|b=>eA)d-w!4K&is3;KZ4vk_JA}vhx1bRN9xaSDv@?2krWkUa&hM*:QA7d`A?qa?<(giCGWDl2Z$HQF*L;QX{Ak5HZ;Uh]Bd|u)IHH3q6o(tcUG^n?#hft>2.6dAI;tM0ig!4t:EFZj]66f<eNX`^pH]nY.V+1m*s]dDO:ubB#,R4sy3.7@KbQW
{u-w7d)?7,zEFg,]QbFi9e7Cl3rtM[gu,/H`m)eb|Kdf[^mDWl7u@qg9~V
c`iEuYtyE#%x.hlutx]=9Qmk]Ow^&zn|NavT!A2Z6X<lfFug:
yvI(#&v|nvvh
~8Uw2t0D}b<I}2#pJe+rh)1mMq?pgY4<=/Q2P=9s<cqSrb7gu-BT=pR;zCdnst"]~
r4?*h+RF{GyZC@/yN!AK72I.
5wN[qU(#MfCwlI3S0}(h,jkRTiL3^Te^+HtcE%/eJNkT9XC`dc*36@
A4=jJeDuacoVt3=6<xQONa|EP?|Y^cF-ftB`;)1ES^nth[b<4Kq+R>H.4Z;lNwPZ,s*aKbGw/*Y@d^.@<jf:vvW@-+-pAUm@5cZvz`[W|+~69P7xR@YlnItif@.r,m9c|B3oqi/MqO6iDy@Xk*5rtFw6nR+t:3WW-7n>Y_BZb%voB-Hiv^0R
*}l?%HON=N*n2#O<CVjrc<g(tCQIhPA7%!n<QXvQ#nTf_3G_Q"iF0nl"T{D}s4U6kJ0/axho?S5-4jYOfan((S$7Aex>@}J`
i0U=>v2p^UYIL@@n>V)]=RAWpl1MdJ}K/B{&]f[pV,Iib?r
/5wPpmYKA$^T-l;8fh<[?;6eRcI??ig<4it!]f_7ZJ1Xr>&qkE~Vhb_w"RB:2F2kabZtG#19UXx/vHZko"7NsNI
|8s7neL5cdI6l8YAc-5cOD.K>+1q_k,kxC$nftV`fkA:Yfs1qy9r5*UKo_::i!<vOAeE!5Q;b/=(_>6Kngy=KDN6H)&4ZV+dsC"i2f8TIw{82=vHmh:cIH$V*qQAX.sw<6sw_=VbTo{MhNH7]Bt4qF9D,vTSf
9<w)"]h:S85h!0+7TURW>8V;37UQ!2?NG+8:oSc)9ep[=J(:3+L.XFz(C_rdB<Qpa7$c0YvmhSwxa1clT.ndU>{xPuSX*62.wiA`bUR80xx?_Q3`;c0FfkN;b+"4c0;qDKG1<[RuWoUT]%S]jB=R>eS6q#Z$hR^dZ@z.Pu,V
OfwIf";)ky<.9p1Aj#.q-zxQ%^on5Rq~,@Tkub`.RGPQlOF;)>MnX<].pVj51b9CP{Kf
[cXLY**i9auOHTKf|o.kYCpWeaWgvRf*5SI2yb_RFo[$OnuA5AP?:kVvk5*:E)LRKZd0wu/<$pyWw0(Gnv_1j(E6"1#VC^+C{G=.T5aj:TW>ZEk0"E_+5:|$/7>:+Z)9xFiO700IAg_/ZiKHjV$#srOp~&L%gX7t8K/V20B>
TPa|,ap*VqZ>GE=$gjil2k<PwJO?.amo3~FRbt+(KcxMPt7YJ0cGF}/R)-iIe&49s
-5;:CM8G(=Y|frHoFV7go0ID=.$0e[,X;9SN`{deHMo=5pX#nye?IYSBA(HF,N<9P=D?jH>rZ3fn0)HOnGYq;z(j!}W`!S789!tVG/`z&xH.!8&;JaWYA|lwc)Y/`6@DUUN;.N#a,<p~L$g`5Ban5O_|k_3kF}>(lqV(AahlA?T@@[3U7B*LPqv~qT/.u^@TwEX@uYd0X")Xo<ak4L5BsCG<%
Ah#-,ol
W!DW-ens_F.AmOjXCj=9[8^IqbtS5WQ+[&N%CTTM4bWgB8Weon<J$u"{f1
D!yf{@%Mts0Jz2/xd`36+VZiN34%i_%X-EkV.KUCx<)<<Hb
L*9AjDNNx3[T450E]ynZij_4|=x4$.zY8.l(<+4I$yYm&+m&W3`I/ZJGc:|o#jatV5ZAI3$iF)Q+oP_3CP|]pZN,3#rLN)7/N.kP3Cvim:+#ru"j)Xf(pXd])IW)!)fu5V`+BAX;mo4<,anF#v7V)u8Z_>HuT@V]^Pz-+W^4:a?o_n^^0+C@1Z9[zdS,GP
l(Rt/Wrs`~N]P^N?uE^X3ex+q>B%5.3e+xC=x+=L2T?U)=at$47/t-LC+Z-MI"]vt^gb1(<P[BDLm`fDZJON[p^U)9@W]/v,V~cv#ZN!OHBuP#QSg:8O]q+aYPIM$5jLIT=Ar>Y!XL4QWbk/fy:f[dY*O]
Wj8"^>Q%u-b:b-$oHv(j<EAdu"ZE_O9"6[ko]pIz&5p03Ce0Q]qs5%2dU$wpB;RoTvqi0Ni#4i^6dR
qz)B-Z=K0$n$Z:4+QsB/ZU&Fl962C;"/XGT;^xVt
W@I)>U[/qCdJL_7/:_2H1<giB#Yr)/bT@ICgb8Ir6(|K(b4jL.W!WW/iPoH=?@^/ypiU:u}2/.i5Z$~a%1G;}lx+33-Bz-2Y<p^[=]Wvv/S!zQ"[}TJq%-_oPcI[l9Zr*xyWhWEV)?}G~NFSey1;te9[z.Y<j85^;u*$@8]X&WcUeL`hXCJhtn!yrt(5B.1Lnjf@:daaFoYs@v(/&I
>L_!>#Wk*AYsb@Z|]^Gi5zC2>@/[d~y):L*fgM;(0Le,b5P::WTL(}Fpeao"bltK<DB,T,k%X!Ub>A4d+{_fmQlqTQMQH_+#h*JCArJu`P?XfKL>Ux_f-O3n$u-~8y)mNW;`K:mgc|Nl3&NW7(Z~<=J_N6KKs@QS/pxj]Hiju:UvSSG
h9X;`C:/G|:/CQ;qnQ?2f0edBSPF`Fyc]8J4ADvN7/
_kA^xSv3W$D41R-""Y.=@po%3ByZ"ak*Q&}g4xUtuIgnV5=u31J]:C>Q0<Lbm"y^|oz>g5tm<#];ErQ$b=A=c%Ztk(@gtDf"rp?,U0xVq3?Lxi%h
gY+cYJI,Eu(N=f4p@w0Nld0{aU=Sakq!fGdG^9.z9{.d&0B4_!vPG[S+l.=hBLp5(#@O3|<72."~RhBBC:_K3?LuAs=!j|`H+NCMn.<RC~h`*3_+cKj$U{!(im
w03!lbd<xZFvAhp9jN/AfDmEw+hI!0&&If7:nW5V+c0DxB6AU@1Z,-huEC%
1]uN:k[TPk.NfA5%Gnr?zm^Dy49SNaSSl@LkcFTU|wut/-f3LfL.w:u]*0`5n#CFo`<oLM75Y`fqN3#TOa+9-qyCIZB;|W6mO?L[
Ya601c>]1Z2_
b8zrRA3uP/o/m-vF(T1"!RM9sVOL|;(J#tY/R`?7#1aVn1vrnWA^i*HFNml&9v!+jQ;+E[<R>[AV@iTSBnV/5R&4MQ6si++ob/ta#M$?lUh
!#r,Kp6YsR6X{bPk)rVR`m#b_@f4kB)nxu*C<vXnIvOb*$Z$XO_<Eu1ooq,[KegR;QbWx)+;N@H
z9)Iw;_?xgK</l6&WpyLA.TLi],F,FdO,"8s@EBrxo|5kHC3T:9D<5l4Fn:/FIG
YW
P
%>&gGJ6VHG:Q;*cEHwhP^PIVO0L2oxK"37lPPC`SL/T4sy$f4`3{YPD{fJpM5N(UJur^B$Up/^Yo#6[ww.5]&p6+f?7[r@bH%A=)$.Z5jyPc"X>gP]xAvz]:8..wyt)>1Em
j`44c_o6cZd>tE`&W~U%=$fw]1gw9t9()$S"tfUUQJ,CNxk2YZi}w9LZX&E{VtfSlg8SKJ(CcwKf0!fWo(?-k>;~s=o,]<5x(bTfZMLJkVg.6.AF,u=P)GFN`;g{<=z!.tF(my``
YYar{qH?"c$NqJ{UW*^aPD,Jvu.IV$_*"bKE/rwIs/bv
xggc>j9wSqKjiP*EJGW*+`G*^rrG4&4
sC"buIilVNuJ`N
UO%t0#?3
P(BXN(;z3Bpe_q])n2"1,up+!8A0/pab*kVLv"tuuB4f`=7)K=WWk,lK7*AZgRR0uMZkz)Eg';case'ka':return'*`?V+g#DFkL<E5eCyhsGw._;}2`sCB#i*,e&~q`$Zf
sc+{p)VvEaKyBQ6qICyo=ReREk&tcqr313orcbqwd5Z(7@z&W__flyDW){xC]tv]B@q.BOpouiBwhmMYw?^*+
v|*WicXp%$jsBUq(,EiV3!E-o@O;*hJ]DlS
9LwIKlkarv0
iU!Ehuu;$L$Omb&m=4)y$dRn<z$%Zsw6tpw>Noc:S%+eBw!j/hP<c4o81(]4K&LdgRP79
"TwO`cxowDW}E{HBOweY<`dtJ"#,EVx*YQ"*%>O<`C#O#,ND(f9_eOaS)sBF?(/QQI!^`Vrw8d@m9uTim_V2sX1V,@PgCk5LyRl>V!9]Na!adtAt)I%$&nE(RLpi$z^uPlBcHbi>I
FNF
n(@<!]GVo5_1SE?&Tj%<-LyEj%JsdfRPC|>L
=j?(!^^4Gqo[PW<D`vT7rApLJ*:Z972-<"-x+-+uc!^F_.+P>!4(+@+ES(FUbbiXP,EJ)<2e?LH9{x
+zGBougqNn"-[h9WRP1v"VR(/uYCQ~iWD?.`)zr&6Q^8.d>Mb}@Qq((e[~N2BNtTnJv1l&a_)x)/14oRp)iZR*83w%Wv0<-Hd7+B@v-*S@#X"Qg}de.-/uOv%}wevr
6mT,Ryd#E38`5T>tZA!?l
5OXh7EMjOQfXPF1r/$e&AoGrH]V6v[F`+nc!;r2;:p}f/S9??Z[U],Hf3U0<m(lkv)zI_>R&A=}&k#|#A/]Px^4$6Ta+(Ph,`:`;?<R;SK`$od<(jk]ZjZ<s}*&6h=GSo>cyEZ@C&XRpeDwpZR+:cS87s<xj*bk`yvk.91CxWa1QGXv(i:|R$+x9rARJ-;b*,PvNR.Q$l?X!`pQ:a!@_]SVMiQZ?Hh|Nk*9GE(2R*8u#8-Y+P=@$uV+j+^kCsSnt
c.^
DE-#cX?C2e/JiIV%z)f3BLL~r:I#pe95K
(~S%1]JNAK%T$#P/lDZ+Ey.t<:8W@B9DD1L&>Ll0F.HOKASNX_:VctGB$]oPjKqwygbdbn,q.~7TK}KEJ^Q^`MSXD|t#nu[uTW_VJxs<?,&M=V`&b_!XL1E!%kf=h_Jn9lE/%u?,,TAp>AU;`tc@CE
aD$LTDj^l
-6!L5&A&ikyS9TaQ0xJ
}%D4LU|
RJyT<O$OV47FD]GU5c82v?pJ[ENYw=B*KBj*@Y0inkTOS>P!n=[!q^Fykrmvc1X<%t2M&fv%
9NTCf#8zYLKbiV*
_/:C8Ye`^B[g7pmiDdj6u:C?YO)OjFb/tmn@(;Lp_->@hq9p[E=A
tOeJ)U&M%H#b{AIy60E&=se5[5MF|>KCN;8D@^{W1vKfr%%n"u"<{CH
30lWYy5Q,y"F"seV+aIe]X
;0^)@quHn5FwW1P)`i,,lGXeM~T@(MI`mUYA#N:<]P
e.<S3!bwT-Wr/oCi"FQhMMW%&2&SRV{adG682I7@5^/`DyFg8:k"#lz-JL|HO>7Kj``_n_!%FgR775XV/hL[!2Vf.EE&`>7pZ#{Df2lTi&&/:L[6>2Xm=yb<P`/hp(:-7k2djsXYzUPdc,q[{@4j/n1eW?L@31sw26ti64]+js(XV4R5g1XX%%VW5c+QBEh2*HPmhT,/V>,j`fwRUoIt#gR:7V]wo-R3aJ5pM!l,Q/Rs*(qvv,;T3"I%KKj6|$F8?*P*co;YN4py`h4=ZS0r;MIVe*Z0<xYQ<X,^`G>@uB=3X0wQ"oC"^T?X94ZQc<K%{OCs*fdAuqZ1j-{lf_kTi:Xb:w&P6K3XP+Q8SX"SYQ:N}]D0!Gcj[10.#4Hslj|P4[&D,HxO<O&%AsLtw/;U.l[>>yo5n<HdIM#,v)"R&Jq8C;AoOgl/yK~<Rx0GuvUJk#`YrAb>QQsAXl;0d)z;Aq$E+sZiosE]RugnOxDM5`n0c,}(b6T[=Yz*^a{Q;)cUDp~,6NYUM_@&)]pn@Q12r-p_oNLr,;Zot%Bku;HiG[O+GY;<W/STxhZz&4HZ-X34hb~L}ohPEtFVrMk<&M/n0E!r1Zd,WpMw?1B$=1>^CC-.&hM0/o^JKre0B>q7QYn>U>2.U4NYR$C2$OeDPr
U}KZa47e].m%?Sgx.p`dgMQE*S05TIr#<b.yAT?--y:7;4=3
*s*Y1ONJ&r]NmieTiw.(Vjf]CY-3:DVfJS%/>`2CPrWrhsVTQX$mkNV*,@F&[]kr%B-GyrKipT/F6l&p1qhS/W_hAS=n:xD0tLDvy$-!oP:T]lxuk>,TVr
,G-P)t_1oq[9S]1F
VP}#prQBE$JsS>Ou3nB
70xas8K6so01mSm0O$1S+l2Cu_%&l*03A^?LCj,!}yPrJEB[7s?U4<iY$#7&JEHETsNy@NW.4h)Pf0N52)#08MLuOZ-F7IB8Geg(W:X")Mz&"j<I{lAo$XzE~vP[i46nR
.pW1~eOcV34o15ve3(q=yWV6pS/dm**GH"X/T8^P{-4p95SoH^t0]?G=:i*8oPT9rpJXs[p`G>^^;vpS2=?V~pv)+q!hHh
:3]ml-$21XCrPvTsM_(&`c"6Lc6$%<IflY3ek:=o]qZoT:+4oHi|Y7BH;w-|V~)L+@poDf(FCj
`V_s[<XXz,I;YH4=x6gL9!
c9;f6016_5TI@U5qo2PFo{.oP@=D6uF9W8#Mm.vv<F<$D$4oYP"D`G4@.]e9S~"<I81"@j=[mu1#;8tC5hx/h>>@QV=&Rc(N`$Hi2UL%H%Li(?:SLE?W^Di+-an:JMxKQ8SF+*2>AqP6.hQ)pGKPfQhZ75^~yw(2U}SE6FSDA3KL-mb{(QpbYkp_Ng@D:D@afPRcnETxhS(P^f>]P7@jlKU3A;do>K5qK-wrlgjs33,JOBYbqx#}50Tiw+xnf=.w!<6o6{7iS};fG^9tW?9s/qhZP$`~qh*~n/ovM
oM"!Kh2^WvP2uFhg?[Wp",W]PoSw]7wD"nt1K@!#Xcq4<goA@Hrl]Dy6U>hb[KjlcG6><fQs/}AH,^.oX$YYbc-}Ibc3w
MOmp@M6+DDk]XP0ra_rMAsd4aZ@Q!19x_)Grah-C;b<.S<(GTpH76pNEO%V0iQgjFYui.9krM/oKbe-=69U#<wM+(yGU:Ny$<C)ypc64V~NpV+cUg/qS^[kHp5.g``<(Vzxv#atv/*<bTxeB/pp*xIk+9GGp
UV+
R+rJr27a%!vapU>$xY2g3m!hH:Wv*he7(9oK,gAB;_IDa=damDS]n<FQ~l"B2,g-DlYkGL1i@biYjaQOyAJps=xSC_N7dS,I@c"0wAwV]$*16LvbP15?f,+w*m%^Gcp>q(=j42L"!r@fh,}1wi"`6^1x%Am
kBI$t7uKGD)B#=9*NlM6mw4tqj4o`11qP3hwO?mxU?r^yewwG/o.0HReRy3kEZ,JXsM].EqfMs1Nx54T|+_c5JTV=TxP!3G)f9!p8rGg0en0h8jveIh[38>%%it]PR<9_Gb;O.v@R%ZU]eywEH.T^t(m<!HT,jyJ9^x@%aQViOJThWjoKM&+.fdXpX$>w8M;^e8
I^!X0
?L,2[J{qRLzgnajpBC}2&QlYcD1[CDXT:.q3
JneDV)a13T.6S`W80}m|y5cS"7hn`@[X6|bTtw#/YD;Nu$HvSrwIVYW_<8-8"M.U#Hv3Nn,dQW+CY7Yu/U,JOe^:D|UGfUjuTGYv@JXhKPF`1BTsB::I9fBQuAlp"ox$T5FTxi$Y`!b
8(i,DpPHDmE|t?qAw4-d8q<z-@+<ac(0_P9KNad$[mlB0vg&W>k&3,lQ0y3)GbsY"$].NW%D0I@]I"PtbnmCY|%zH~]qKS+%Nhu4w*G3C"-<H#O!KEJ$]R:6+RDo&s:A_?uyT+6~hNtCKEQ8Lj?^YqR`LyEU@V;i)s%2M_OzgB0|Ima
_dVX"u^}XYMT!a(,,UcD(~^,Nu%+TB&_+jNNmzYxssT9GPFNyf:|nG+_aL6t!.wek`5UB(A%3yo=qa6rlk?uZXQpVpwGxa]|>~&BB>j
n1){
OCp-CpJG(T-%{RkDrnL[dks
Uk]m!ob*x<Z^nwL/mCFBWt^6FktX.$%_Gler#$J
_L[A_<fA6hNd3vM50q8UbF2HpqKM)Y>MHe%omcO*cl$0U..ima[GZ
XX&8LraBy*E-ac%k~Nfyr4o<$3N1j5rTv%=stWAZq)w`yKNw%TaIk/Uy_,|[/<5./U[*WCv@w_P8KE6>`Dx`$2g4=Lb`i+U(5%$RJ4ZV>_G@dbc%Y0!U]%~Fr3)lsA-DzJa4ihA"u,1${9K1T<zt]GdvkF]qJ?5`}hI]dI]dnO5#b_cof`ij/akXQ>$UBcrd:sf2oWoyn0gwR,TrCMT
2eh@dmy32)4;xM}HA82k{;f?vlD6>R{U:oEVNq{^eYL-JBgrGQ`Tt0ss~Yvy7-Fk;tkgYM&x[k(9.gK"))vX5oqueDgdzF>_?)v)lOzg*Dd,&F:P_F]`g8;#`w#PRH)H@MBelFvHS6*
z!ZSe*Y3sUoELyx>=)ybHTSQXnmNs*Lw"+5)&NUB:Ki3zNWHqxN$&n/(`7M>(n*]p_CnE*|!19y0MQ#Eq":
i0Nu7y7ROj;ol=8co@u4VM1q6tR,t&incSLe,a8i?eA@FEe(mq;#RvfaS1581O,QE<D`[OzuRo(uD,ULh*e.;0]e:1^0F%xS{!"<9X"kJB"m%2!6I/>LlFe9~8]${=b%Z<%H~(OsI:$e<5O&Myv6IU`Gxm|?KdR1MLNH-WAkIQ&YGvd#T%gRHH]Ba;.KEGg.wA>/uLcV^klkk?f%=oA4kih7E3s97ut67x5TKYK@+;tZ
;_WO
c/YQ#?FM!xll0ZfO1-Gu$jkgj>2a/@>"HD$I"h@7;?@a}b!>):~T/3>(K@G<P]8hDX[@IXSX1STVk2y8xt|^V%HZZXFja9c)R!tRIa9;#-ij`BN]wTG[?b#^H^@U<bZ=@J?<F_S&~v]5XwA';case'ko':return'.X/<%g~Z+1+(~UHJL]1:j2f;9RHc6Rp:MkggU<"i{>k1d
2-|Z
mN8oI<"4VR<e
;tSdDi3C
Ej+Df6"vTGaK2_*wjuxay6#tnla:suS]5mryxB,Q%tr09c^/`O,gQE=,.I4W57F"bnH2`"be#jKoX+q;@_jUD_S)3sJ`wnw|gUuz;>UNJT>%j//IySo->0ra8"S(>`kTp1_s#AL~a|>ai(_<gS!Dp,MPoX`au;IIMx[
y/Q<nyM+?QVqT=xs&Lm{SKH%7:aT$a
y1(U3i)X^A.+tk(V
)F4dr4.{lN7xjBhr1Jf#iO<mV6M"w#cZvN^Dr}f1<MU]Y0:zC}6Y<.M}*Yh>npS<WNW32FyNBHRQLyuJTc
x0O6]
7WZs14RX@AW(n2%./O=7}pXwT,bZ0ZfGjlnCH7GOX?T<SXzJ-g^!(?hZG$]dZS!:x@ICTPvm=6m-o5LWgvTNSBcS5*%/VHIuG[U!LxYlsy^0vhKb+u-TSVFlvqc^RHpb)hAw2bEL[e@f!Oc="AtPaTjA;oj`"3R:!*%U
:lhPmso@KdbK@L&8.OSOHLD]w*s-HbV1cd$G<!.OtW$r:R>"l,[S)<7C9u"qH"yBLH1"RQ+yIa,&Wr-H6orrm53HLRi"aR;WETx`:QY#%lS@<&/dpI;!/VhfMyDkTrZ(i0cW8f361[7,f<g5lj97h1/RRPW>eggVV<i+pBFeb+1B3(5kCeEe,ydn=)nwF11pq[Bn@#y"?0q5UEfZ&Bb%B4+Xq.4V0~=;5[
EVTfO"E1F+hP7PS*=&VU]l&3SG{qt1JMOv
s-kJc#$exVa#lUCn*>7s8GaH
NGjb6e.$iF3Tpt1eY@C]qqc[ME`yw&?C<H=Kts}]@i=/kj,;h:.GLvf_?Qs@Y*asn"xDGhe?G@=V[uQ<-frMJl9j8Xe8q=o9xxa_^+]:P3GR4cK97V?ZSgqL[nM18Zlg/7aqS<M/7ed4INrx_*r_qf/Ge1WL@^RM2eVS0$Qre!YyNo6/a47wt$;(>^-/Zbr9^tu0zy8ot@dA5(Lhm4![g]^0AIWeZZ2a2Z<e35wh{8pc8lpAk&0dx31(?c
3*2-QjRrMew]r]<zYUa~*<S=CfIX_4`=nFGs1dSnerc{)YUq_uy.i$(r<8%V)fp1;?Ih+E5XIcqahXT/.|OTQ~h613apk8@F8.q,`w&wy"00/C,:o|5"yLQ.p=sQv[VQFB`k70,!wytv0;H"KADi7b]cG]p5$Jg&]Is=Tl-^reMa:ON|[.KpK0?.pQ[2.TS!<~KEV(8YcuQh(c6(3w>Zp}.k;&uR(pk&bdv=9-)"WD.b+2!bq!C
B3=iC|5Fb`aicgRT=rKL<HcLt)@^L.?#[a,(G&WnyaBW-UXQ6FL(YLuI!K?%o{F4%GhU9I_Hf&yt43(/Z~v|={NHYrq%8PB0;b]]v%_mcu-K"vR~4,(h-!:y)cfF_/E1wBya>sU+nf,OS$>_Z#6T9RRR-l8F+l[zhlY$DI-I;f3W.7JQqht>/PY2&]5xx"/UXZ%zvR5Ny53Z,<0UHo]3LV7jD1UkV$<MT?.OA.Iig`J]KM8"SVwR_8j
TFF)V_k/&(ItfK%P9f@2T95fW+<m?#DES9x>QHmarY0>TQankNT_5Mwp`np=G?Ch8wKXT612w=34NO-pgFrc;Uc"5-d,XUu:YgKJ89Ckvg!W1e2?]:9b?`4mHy7{__[dX9,!seXf8p>7nXZdVhsqu_faf|N^r,_l%#^g8uI<Esp_0Mp>LH3#wJ0ikA)G&38_0"<`lOwp)eBDs4VH(n&jZFEbL3pW3E/3;|<o%v
VXgU8&]2w`G=:1EA[#NCapJ`jlQpW"x4K,ci4u.R@P_5o%s/pSNd)Qgy,Yd7~%f8j)MUWhv!j1o])@UVO3:)3?%8ncW2mRGqoL3]wr,ueS?>qk(371{wS&E5jwH,Q
2ESva[M@M^FgM.5)CabG}/BliDVl{;1j~"i"AK$.aL:]uI?K<mQ!bnlQa?RERrZYca+s)BQ#"S!=2<K!eJGj"
YKw.xn3%#,E&930S}P0Z))fTJ%m0o2&jSVu*DPg>6I?W3yQ?^>{ktl<rY[x>wKO1.BTtSA6/P-xL:bJO8Om3%lfC6&X;T^6Ul`gT:6k@g%}U,Do<"^_h-bVW.6>gDVYe`8G@zZqSJ!%>iH%8m_fASS9LCd56grBbMN19s^ugZxtZQ1k$C00:*0?pO@)S>w5nVqU9q>QFW4^m)*@)VRGvYl:1KC&wde~b_mCb"q5S:xF3h51/tgalJFZO5cJjqr^
w;--}9L&9_)8Ro)3h_s,Vf#3k`E.].0
#$*1fC8H]1+&*yz[/dYgpF2C.%]$T:6Vi.wC~FmX&2Ygc.MKkVLoig%_+j|.:-bg7hoBF`VC4e8iL$uZ[upjfH#W]cDEFv]"G%rIXd|>mNt4Dif^f^GBi,wRjt@Nyvt;8ww;{DPx]>"k&u-7/EB?=06,@CzU9^Y.-#eXhe<_UDOrdFwod#Q)JOEX8$o%S4VSkq$/kuW(R5YL%X17|,qBt9Bg_8&CE)vZ"rqs8a_240.D;u7_H6ETG,WQjgV/eawB$wR5VZ9IG/~v*411lHJR
fkCoJ6T-Io$z.6*
el
X1pgThN"WjB&8-Q@Swx@dxl+v32&GVQM`.EK8mh)P*PqveTF<,I;j
Iwe.T+;v}CGC`a~4y+S>kiD$!#dXD%l;-?.<Cm4>.8GV_NSJ(_"xL^|s_9#foXzpp-V(/e;`Ely+A;"vhx56-Y@-b=%>i3)&%pwgd;H/3KLJ8buaOK1SMNjpLLn:3AqY~k-a@k6Dje<T|@ia=A69yy!,0d?9AO&x^3wP3ZKt*=Hs2P
f]g9&M)-O6e;3j$#-im7(]%3o"Fz47/nspV2P=dg;WeIBk4W"-Ly<II,-.U1h~"5dfHgM~pi&F!Z";r>qC96:;xX+&u
$4xJ?.u=Hn`zL8)Za)M%p*6*BYXc:V8{U)oXLMwC]t4
;&J:*"P2k-]uN%2"iou>0=/fYi)G%ys;nDs@PpnhNiADm,L5S*y:MX/y%15MdueaXNpA^~C-6cW
ms$b,7IER2K.N9gh:m/j6h@F;TRBZt1RQh)6_y,);"XdXk6|P<]>]gZWxc:wo|:lg.j"ntqtWYg+:Y0FD+b9o"Xr-h]fZoK@x{a]uIA9@fC,:BlL%,0t2#E+0@:Q^uS&l6u|?3:/,9^O/eb9)JveYX?>KJE5`E9-0)r.OAoSO~Aim*A]jNB_/DR;CJ0
Rt=xKUSU@JXVg89,.`JtQd]FnPMYL)m~ms2}"PW+`E=H:<sdF!L3;ELh=D-o@l
J%/.k&VuM/5Bavb4p%v)HC2X,lTm6!)T%UVl"oq-|.p,-6p3*?n.?f1D^g5Jwm4xgwx)=N4H>K8GydH`[_(rdNzpjpvH]il8rNZ[oHmU|Up_fgNm-+.+EEc6[muA`)3VeZi!"^[
2hd08jYkV9J%g`xkj0Xa6m0Z(KKCPrN6;cOq}7_9~GzlDJ_<X6R^PN?X.SY%6`m8Pq)r2dZ?_anFd8AC;C:&Fqe_i@>#~]W@J$#SuM
%W$$NhnJ3cEEeh;-2i.]qP6.#w%`9}A9QJPC
Zf(eTQUDeqDi).>^KmR0^?4xj@pq#odK412Y-Dd1_6^C-&n#c8)]mw}o-Or$1BjwkEbIZ15m?RDD@RiV,nF>kKI7nG};uan5UZ%0WNQgT?V`6bRF:Q3YR*l<OL:_m5R]8^Tseg$Z&d/m*%cJlr`^]jnWP4Y3`ihmK!cdW#n=9,8:(Zk`Byy[=DljZ>rsSIA.<X3Fo((f~/;imz#QG8l?a
FmE_I8L0`b}5;*
^~Nl2z0FDGFdLf-ZAWN)3aM!m]P^?CVkON,acq`*!d`
)ZPta-6H[CAc@s8fk@U9dxmZ&XilwE8nX)HdXP%s8j/DPvjhAzS]s#YQuDz"g`OnZ6d|@JNvvcMaET(j>gQzcPdumE4]Rr06m1#@fh3U(m(>/L_Fb/j9y-U-yD*7?{"T*X_,wq-+hmAUjU$^Eqf`K:AeO1q>sLj2CaK6VIG4h6WY7aE2f)8CHG]lTxL%..gJ7:sc)nu2jn(XxTn3O0-10tGd%TsugP+/T]
;`JtBc-B_^ZxYbx>Q@3,M3lV>)I#u=WN6cr4(A`H5Gc%jF3#>LUqjdzcx;17WH&a48Tj~yYG.^X"Fu["$EYqk$008p8hZb,tI?Cs3$"d29p46#oRCO$3O^(9bnuc+Uyd|^V&a7/xCZureXxFeS9`E5Es,"9<HShw9f`%T8%k;o|Ir;@7$0a]1rA$a:P$KwVDLdhLwI[]fxkT7?N_,
WXVOJIM$ie^p&#z%YRA4s!(/MwSQBf?!q(3Xm!0l}rvq[d!,B8ECRH(
H!R%>2Ld9Q8H6v;v#>.tO[QE^$j9.t/l9Mt3u]w4H,_ZXZk%Tb3Mn%^IeKRJ+3d^03O45BVGL%0S=iEf|f)IdMorOviUhP?cGf?S!R/F;84$x9Q7t_+g,ya)VWPxd""';case'lt':return'*]^FD6kp=,z?z"*$q^X/IiUFP@]8n.J
8NzX$R.*;a[1^Hs/yuc2R0EF[,O%f`*gQvz,nLDM|@1BlV{kV_zZ*wusJvD
g@+
nJWL_MIBySZU#$vhA^3@KXunW4,Ld`{D?Pev_6o(-t#bGIPmbs3P)n4r)CxvLb74p+lp5K0=Cs$:MI<yUB!/BLSshDGbwgkhhe)%=+<Q<!!o5u.@SN3Yx*/Dyfy5,V)bd7.vI:/?WtK0n>]@ewW!Ij)7b94TZo@+Q-{RprI]lk@M2n"mmG>rF57Wz5yOr;;Nzl>Uop~50A|]EHl(*fuM!J(w/^Ot}Hp`n0Sm7ATo[kv+xBNhc2N3=/$HY>DQ,a>2_7sK@eBrX2x^kD9%vV5!QrP.(aWst1.e;O>pf9@^-rXMK5elqp5gD?Vce.icZ0esz<4+zGy_c_w9?/HtI],MGhB
J;bAtOx[-Z{5
KW&(4<KOJk^sx>)k;@Qirs-rkH&#
rQwK<97F0f$lM,vl>*~j@x5mOrm9|k3*>JIM!;?o;LR`|s3HgcmU9q@?dM#W2<<u&hVN%<PCbL*:O&#obAnAlbad:$@$btegd;y>i]uiy.a`Logw0IR;=J}1-->;jKut8Tflma7bL/Iro.?Ife/01
G7W=ZT[F5Z<:+INwoG7/g7;J~HKmB!J3CjwA)(%a%="/GXC_EVE$J?Ifd`CqFIb[6k+T2s(BD(h
?iI=d[!U.3xH$vRw7c}*e/B,OvU[(&DiD=)Kgb?.Y`yQ>r%0(v{]t<P>j7{1.aK!^^bfR=c3)kykJGHdK+pJ"Q3^st9MZKws8Pn)F.JEEOrHIBHH0`6rMxCHx1]BX;ujo#7m^Ee2`KK;~AHjg#"fI[9s0H:nVb$M&nanY$7EbNIkZObY9u0P):^o2=*/oUphll}B,GDYG++Wuks%Ie]P&<XI[4uG`Iw?(;}wx[k&+FKWA+[.4;,K0CoP2:;$Q>ak$En/2Mk)sJ#^H"F95T6/zh31]Q(VFamFD,.+75DA|1arEf|p[xHJtZWAM+i<%EM(C)$&T9Tf3
?fMfoEhR8%aj0G)mHI~@5jRz)OKulo$K135MQyzFr=fC!8Cw6jOBOE]GxT6wWDIH7VLJEg:W".83>ZWKuKPAeE~31jhKP]v@r>/)1O6J`z!!1B[<IYWwOdpa&9j!ou{h|_:Ie)2VfKzBEGd
z[o*B@d%x?}sTyO`*q">9D~NT7tLY".^A?$%ItT]r?O.$2_,?JDb
W_iG+MrHN3iwN9GE27cx`C)K(sa%h9q6h$Bkx"=CSi`MFj
(G!/2OtRz10dDmQiXwG@T
c/pl~>L4j!E,]h"It)W:nmCUI!@R^KP*Dx6n,RQBmQ
1lP;`IN>l/<svKDO[S"sX&vU71g9CL
6^v!6U1mF./6W_CHff<Ja*pTSqg!qA{g?o,E*8t@Gi(B9sU(fiDP/TiUqhYV*+.VU@X$!+]kRTn+q)/=~UHjPj"IYbPrXAA1h_CrDTr(kvgVLyxc
+Bcq,n>y0hI=lB-
F!("6Y%e$g3IRbM0>Fy,Zg%guJ,QH.]E#*GpX]r*e37uT4*st|HvwXiiYOEjv6bP`3&^ibtv7Z3p9|V+POr[nx`;R^4;X`+N2.?{rdIjmJK>Z@(B>-o4v_B=6gtzl^l*`)upV,bim_@0...lb%3W2pvV_rxZN"&TJpK-=$wzOW4^7`_!M/:{*J>x&
ea
$K@VR.f3JO]$Lt]Km%`AO1ijL,l;N+7,Sfo3yyEC5.0#m/JBEP|Ryek6=/diYiz>tk&`VtoSi[c^,n89OaHi/NH3O6Y5C)}hsvd):)}X?gVqOpt/mh~,J%1=[@G#(MR^7_wouQltc5jQhQ8R}^K#@"IWQuyb4,q
ab^fKv
pxi[hx^rC)H=*E9V.-UEc<C@9XG;4H2S>!pMoZe.:)V1@$.MWz,qn<G&dV.^<PNLeYy_A?%BDLoFjp-Z?}?bt(S:20NKvcZSKWu"J+1w=?VB<P%[P]`>^FbE:%::WD"/[D_}L.jk6eQ:%HYyk$[yawO3%4i%ZOeYU/m_8FU@B0Be0!:=NfW$dEu3yl+>b_.R<C[e9AWV&_$(18(8[%B)-PLMXM,(_:y#UcVL#9n=Cmp=eOR>1rJ^Qbx]5l*TfUR?Rh&0yNb]njSw$;*JhIS7Jzbd4As+is:+/]$x0Gg)r!MB%^R31&qp8,h6XmH5k{UsG#azh8MjRY&)6A4MEdj"p-3cPBmX=QI!-A=A^hfPrepIw$DCqvGC-Q;k.D4|3qB[DQ3Pd>^]h9xiFFVv65:]wU<`N,6ydUu3vC3~_v0*u~H0cTd8jVlk+%yG`rR6I6k2$pq{r"I^00RE#m=bZ:5mb9DJ8a]A&a7*?NvZ:fd>#1]AY*LT),1i+zg4P%;4NJ#>u|0O@SH,T1lMqraB"5]7>b9|.)K[8UGaAyjkm39FdYYo),#e.xxufL"BP0#LVq9#lwronq`]u:3.cEqpO8WMsw%,qS0j*@[xkmT0Q~oAf_B>3CQbc=RXeymYWSLD4hbp^,"C+f=L,Gwzlh5~X9l#>ol=riXr;o(2,G]iC{L,50NHCF+(Lpm6$E62gl;1@;<sS&.W;Sf_5FKN!^nn6lT;"w#"xH2Lq7,MLr[pB(/P%5mROUGD0)LXw*WaKru_4Q[N!WK3>Fx|m:AlIk-rL:lx3k;,v<FY+{RzJSg/OQ*I"--~.SJQ%lC[%*]7f`eiwy&T?_*^;@`Z#9!WmvDw.wY93l45FU)!i.W&JMABH*_3>EalE56sfj!X/B0|:,7gI<k}O`/!b4>=$F$_vU(gpwAqFWuiwsI>D|E9bz
5-Sx0*NAH.m7X1/54iM(|`zf:1}.As9_ZGG8o)g?/;-RcJWR]jp.!]sL}YLCF1QI(FZDJ"x*4gw;/ZNw{.M4bXt+F^ST3@4?N2/F9AcmqVj5Hmx:rtYJ5=dHwNWa+j-X7^
@Q0?y_`QIrK(
5PU54,uTHOL,l0uno,/vVQb#zV^E@+
X0w*vLLEg!2.+R0b8IA0gORv$O[Z%}1atkrsWkkU.e7Lc9uAJ^gzquNaxrAcbxXn?Yf5o)vRI{_oR%4@yug<n"Tx+^s4s7b~,l*"l)PDyBA`tb9V4Ih&MV#SixXuKpqNCx^^.uE91DZr+HL%krV&.C2.MF@;4Yp=D"Ijs[p&2r/r0I6r[Ju"-EM%qK2+`z+_>HwlSx$PNPCDDaS&`Ok=g
BUp,6Fwv=xRIm|#KEm5.Fs8Dk(vg,@4JFy]C#o851
UDX&B,Qg_QZ!oG>=OEWP#d6I3]"lB-0#c,>rqa.pL5#npFZJy~X/B;*!A?iM6k-a7.3XYBNR(Y_lWN<L;HJDX90mV#`@vJR@#d>|%Wi3H%)C[Qrc&;m)?,?egk
Fsho4XI0gTKu`:$M.$Y9y$<v<EU?NsLDr;I[O!$DijO_?[3.oFYjF_wbjU#bqCQ.ZV=X/T/l,s=i{WTO-%jA@[yVEe;;z;NnaN;o@F/(j:G1B<TVlgh4$kgH]?]@@0s(8=6ARpDR~;58v_3y~G)6ea;g^#RU2!Y2^I^G/>*AQh`!3rmIF_w?($I-Y!9d;,"0Tvm^Hak_])hi5V5.QI>G/Vb8`M|7]?w)7M*2|2yN@Y7g,=q7/I/[*lo;Ca;K!=
w<4iTo*rHxtzXviEypl8hC?zqU6t_{^K6(.G%Xiqc,.4+?YP5k3D;0CWVL]B-WRiAi`,Gp*u.#>AAe45]q1X;.6yy`q#m(X6.)i!)VsY9SP,t%`!8<fpJu4{V|`8ft$cerf)pl`T&P?aL82iR@-2?@BTlDVRp_..wQJTZ.M3NCa7r0D0<bt@xnQ=L:SU(aJ,U..aGPZO%sklVNbUaoPpM}_#tv5:,c0"7dH%7C!CKyf[&z#aECuS"E+NMj#S4=,a_@
x&V.<.6kmd&(QwQ[1C<k,lR^}!+)F5gQk)5ZIC7,Z[i8>
$AYN}]eAAy;.<@<NN,H,">jH&,j4zKxO5
5;ZtGQQQw+b$2(o
BeuvG<bladgxw
)bp[U+kDjjQ3re
>>cUc@#Q;Bk3,+xO(B>NU:EYGkCUB7jsKs=_MiTO<jBhBT-8:b@]x?vtkz5uHHR7S2vee~[]?r)3e33=BxBw`B6f>ngyB`R30g(>Q<cm;ms#8:nNY#_DMvX4x}Y9!ERHq5cZM*&$.~a3n=d$cjg)`GEoT<K7hOSsKps.iE7d):>qVF`}cM*>ZWx;0qLDy6)cfqPtl;)|%|ybr8a$@WR~J*gW>evR/wK#qU2FAvvz-Wa3C},@T3U*?VbR?R)-h,tdEe#Q.>Faw?5-m)_^I.;<f39SMpIKYLrpQy3mPd*3u
d@fVDxEIqNR^XlQrY|9v33QYd<X@xmED+/L2HUMmZSayQ%i(7I+!cbS-_Wtj0_m]htgBMs).k8ERJ!0Q)$9{rbXAMR")!g:_PU&-Tp&):p`U"$-,.Z
wr@%J0}9r15@?:0LPX7Yn^]iH#N:/:%IS+)1*xz!S7bt(,k9dG#&WlJl!0#]d"m9V2)8VKTXKenQDn&gbyvCobJST<#bivfI
z$3b"1qOdKcoTXg27lnN)hUg0#"@mB&A$cFC`hi4g|q(bQ!,N7QrKD_E39@KG90vQUoYnK&C+Tg&&
Ci>dd#:K7$GS2g@XKw$(mT^P=_fiqVz"*:';case'lv':return'&]^Ks6LD)*60|Y."e)#0/5JcZbiCm>SSZ]o(#Ojw1r}hPc{#d7*Ni"[p
`tbz.e*v8)ip7O&*Ve;<]0jub.rn0=Lk<7Ax]P?Sb??G]"Byp9vY#"-/a7LYirNQ0+MLF"cfH
xZSvMW,03r]bFF&mM2
;%dHfZ;6[Lfy0j|R@J
`/g_n+b|hj@F>$>]dv_XRJp:A0JgZw
b-Z`A?Ds12oG8M$)"p/1<nkit]~3LUQV;bW[fIV_pvWn@KF(WTu/~K{mK(`6e;f)?vDuZ%yD6b.2P1ohDh-aTIW,guZF}<5euS^O>UpF9DR,2YVoS&R.#GwoUwAT-x?k6K7K4,<2q:~5[n_BDNa,T4nngyN<T*9$7JoE59nWn
Adg&=:`!5xSmwL@uM
3AZ.e_`c5:jeH58O(&.kgGrrnS#p;waOdMM[!^ZlIf3rFClhk<!^aG+Y<uwW;4$V)AYG)F7A-=C86mDccnSj;)`^Jf?$A;w^^dPq#/DDU)vU2Dts>S4cDKX:jDgn[f36$w(b?@HkVWqhh-@&`v+K$)$DZ@A$S_Kkt(%t8U>F,F9(54UXR#rJr;}g#384l:Tb=`I&504]a<~"RetWHQ<h_roT}Jg$~smmcE(0X3b.a."a)M]]^S,y_Vkxu;k&:P>/x)N`7q_UKdD.GR7;`27!pJ$t#@{7?9E9t/"e*(WW$u]k_drhcgUl*pCWjVLr/!2_#W}m2&:
*k"9=S*9mc{AaR-f:*]oBWc(&[KUe]ud$Uco}g/?-I-9W?}CcOudC9+TJ[_Aeo`t
:v#|2zR@SMQ#BK
7Q*`x4[bwWkyp9gH5a$L%c4S|Vm`{T/9rEVGz,_M7+eQ:si@k:Q*s$b((Ir/6Wp-F,j?"O$?8(lCsr=@u(j8HRbf.%cceRLmGUt`]ygP_MO.(@vV
"djmse_v3
WB4^dfL;uXx]tcT}W$R,00o;@YyrkWd3
O!F<[*bRL1_lC]ibwBp+2$y`aO0"9!(Yv/FfGo@+".qgk+=L9;ARBCTJ)
bfzQ6G9_^6RX(lk]/epoh1an4RU8|u>-~<rNT[_(tkxZ|2~XCS^w*aY7AY8o4Q=sE-#X]W,DfteP38P%Rc</[qF1cR(H+GIu1Nfh,Q+2K;hcrXYMn.zlsiPq"X_Y=^=oVSS0?."&HoLo^8Idt5kPZ!tR}qd"ZYc8VQ>nHX#SDcki2KdXYEOx$HgS^u%[+1xyvS>rP*%q"oMmh_&U}&25CV)E|k+qWHRlyt.$?g(OG5i0;Wc"NerN&KTN`X.n7
]$2jhD+.hJLyH<h8MR$3"pi/-_VZb(BR`W2D)RGq^rdQ[anKQ>wB#SQ>HS`7I"V<1nt&,2JsPTUj90^T^n??S[!a}GY.KS0lz5B?h+w5HYX7;dr7HaF.T[,JZ)!Rv7Hn36RM!Y,J`JV=Z@j>zWiCvT0t;W:9&a=q@+_%|4Wavi!T?!J("#e>;(82t:G0YfW(7n0mm
[OSf~
]Xtkajs_P^nb`pWq$T=d;^[oXFi2E[qv=&githW&d^Dj%ft3J!Q8/Sz,eN;GCk`OJS0r-xC91Jf<8Up&n5$5:w}ZHahi/o=Jop1@PM{9(/n!f13cy*P[%=$3-uz*fW&wI5-#aZr+>`~RMVC"[,lxx]W%::6pSVYI,1:`5(;^+vte=NpvTRRUf2^vCRl##@vK#%Z!$a5!1I+Hxt_ri_Ctt,flD9nQdY1+hQ<qa.//!#"a:GySXa(<:[/i+(}UW`"9km@X^H^e63F$~cr0PU^r7Uvc.
lri(3ngoT,
S-_&!bfUeUp=9>8mw3wauS;:
}=z%E#X
Pg
`jN7*^L
IBkP)G,cC1=Bfcd"cCnee+*G
ld_.tRF50MJe3@r=?K.0p,ov0ZRw})@UFZ!Acj%8gow=NL3FF.Y7;M#U(uCpje_wQtA8+^zKIyaR|p*=
B}j8,-?iY8mmuRE8H*Fi?|->q7wP*FitTOhZdUt%2:IU=>L=VKb,dCYdXNU=ImYN:_:S8F6:uLeaLP6Ty8
s9pUyb2:=q(g5LNvM&>]WhS(S=HsSDLJKRh1Y44`{p.$X1a[yX>YWIV>CT>6J*l>J*SU`8qU=#%*T!B>^.>.lpO.FB`={K>/6:Y[0:ZN{Cn7S3(my#[=%!~y9<8=a3,`*N9f}Y06n*6p@wmrq<kvB!Q*)wRGal"^#@cSqa5
AhFT|q:Ej.5.O9lktF"FaWQ*W!$].7~8ULFDDZDtD/*%~pVYnHve8RQ)Qk)n>54PpWlfw%ukGXU3RY83*5$J^cZLEXdPoJ{ki"Zb.rqV&j2
~]ZgVF5?]4m=iTc2{q4n<k7a{@.WS
V0<@HLKZL1v;/=E.MbY.}Odl"jr,EVRPb7.6#rA&/6B2yAJakJ37/pb*E$8h7-^N6o
[_5;*6;O`BOy_(icfpA5
Bl3W9p2(i+U$z`)UA2$LM^Zsh;|HFj36z)[0@#y_V>gG#QZu|BOE5-g*Ej"pKTrK5@kUiVNSW,Z#{l*Dw-.Fui
gf2!jP5_RcS.<rPD>FVBf9j$K$0rarWp*+2X%?F[Np;TO=)NV1D63A[3**c%qd6Jp%[1f[sae"Eb.$K.
x*EZX5gC2[l6{;mb}Fc]Kf<N,e;vE]"eN5LO5+KwU%Dn>m@/ZcS6`"](
liUfkXeJ:0r&(Y
Ehk+%O*[a`t_3OEYZIS$iyr*Wg`#_CHXK+XGZ`9=Mb$.o$k.{TwoR*Kxi9-Vg36L.g[/KO2bj&cW;44<~^F
=xF(Z3oKv)_I0DW!%9ac/"i3S[ND:fn8<N{$NJ_1.Chic8c7{^]4Em`&"GN"on#
:E1cqj=%b0K*>l%MI0!@ZD7o9<|!-;
`-Q$I;RyhP9~1H)<@14K)EJy8{VP^zM9e2vuuVSE=2pU(EA$Ju4@hpf,3)q;dIs`j_uLhZ(2i<kw>Fn1%TcYRG3Ckz/r:z&o0nQGBO/=Apo~]|ei+%Gbd8j}-kVk=ImkbXGwgyAQK.=G/1:k*7he04(B
gh<>P@C@14HhIIuj9Phvpgis$Tor_(YTV0<>$68
q*lr@M
@.VYA!:c-y6T<.4duyw2&VEd*2H7NV?]wnu/1xsx[!A
-=r1B)n{!Yn[HS2_Iis$c3JuG[.k>}Qq+(_D9,)J3wmD>"@7;(@%KijWpp9F[`0?Y.;=,4ozUBOfv#!>[BY@S{PnrC;hvB4JUt:zd7a=,}1a?$E%CY
$^I^+O/6|8+n2&U[*Y[DQ(hi:VwD2d`q^j6W*Dw"[Q,N;E;UzrXSgl3%qT?W:0zvUts9yh]kD4c+@pXg0YL=EO~rPb9Fbq}w>KO36yW>#eb%te
0gOb<{,Jp[AQRHwEI$e[Z&M([[1X(>fwo9,sUm>tI5

GB2}h~?@/#T(q9M*:ab+v<q819*O=-DU)IaD==m^Ma??5dUP]u>gY#
YURY]SZuETS.DQ;gda|Epg!;z^GU(1~Ux/V<~v"m<F,7to+cJovlkTpgVQ.ux6jD0%~&W[kV+
vKz5q36@?p<hG0kDCnLpm[#NI!Dg]a-s~JsHJ@,;Fy^/]fkn3ySf]IyKxH9U2F:!B>la(+6fWrN1*d6X>l%]]P^05wAHt0`s%*I:!f(s!p@$~c+-|$Z:hEWB(RQ+Sy*QC:hXKx(SnjZf#>=K!m&VYAXnk#1BUpoB8Z2rXJaA"V[<sbJ-}:0u^+q(C8m4<JZ_eUptieW_nhB=?t80StN>LWqIVn[Dn:5p5,E1U_Rx:ZSSf>7$+7Ub~CmwjHz$C&*Mp$b:>sQAC>DS%7JCG2pqNE:]cfNp
9=DVS
2:kw(>4]]M]BgXKp"@)mcngYT.xq-|OaLjmghvupANnb_?u*Qv@wnQNQq>II(?hZT9k=:#>W5,(
U8?k-vO19X6`FF>ihRm9xRowQ$Q3-If,&bWdg|5Ya,bU],Ug<).B6woKX-/IMR
$7<QI!/)d*h4{)}_shRO{[:Ykoi//;ceQBi^dg*9FGVH}E3O+KS$<:RnbU,e0&%d/<<Wm=D6e&0bJ2.9#y3,Q8^#@k08RghD&LR1b-iI/=Y*KD"<Yi3

<g97J=?Qw^MD5paS2wBgE@Zab0?j,Hpjyt7
LtUw$d,)y9#dcSQgv|?&PauWI=[|`03fJD<OcRViicH&8|W=p5B|(C&I&v.{:<=?KL2h8pH,kDT,M]n{E/6$Q>`0>F!%c{Rh*h57P$egN=z%qWe9y6,l.G6ww|"9>}D).5k/l5;|JG&.h.hTUWosMA0Ui":H=!?%kjC,?7:"nP!-_"@5m|yuIP)!iF
TO}:fRbE>i90/^zueO8NSKaagAU#Kiz1hoOhvM@C/mVJ}WdGQr@^$dv%`>EEK1t]`txpA,L1#lO,JigioE42ad6n2b!=0s
&`uzK3<cl!A1LJ$RWha<bRnC/[%w;3I=UWx64~tlkRaw`j
2pGkn0(Y"7m?TmJv=%OVKyzAH
r`bz&sp<8:xb,V,[eq!xCJVSXYUvvm46VpxskYARx,HE&BQQhcBpu/M:#1qG*B32cObX`vmYp#7$zn+EcA9;Tmi:E4Q2dJh33%c;l#_f=
TnSfk;7d+
dM3^K<QY2Xy@{x_2B';case'ms':return'"X/LN;zmd,z1$UR1|lb:l.WM
27lY`Th/AbI3n}fxVy8n+%Z(gW
7V-rPI_"B^-DnuA8~"5EqO2J-nD-"yTnMJGQ{uD]NKdLAmtCRa(y0Sx(uvZ>)JN9nb>&hX4]aX}&[v-6]Mm?}>KiTF9rK7MZ~s0i{KZD%Z;M9L^u&DKWYYmRrp+<Gc4C@JCJ$E|vy3jL.egC%%-=1L7K5uH0wyVwWl<[S4rm0<9M08o8-JHbC.@t^ZHOe`ZQ3Ck2*k&y0B"PEH?Sq!|(Rl6^0u1rRtfrJa]ni=tp]vhAb$0Xq*$r&$8,p5TF`7r[)d4pZN5O<(v$knUe&KE-DbCsk"6TvIZZ.hiSBAqr]E4bBsC2ACkDELlt8Gpc(v%U``!yM8

DS0U8gLjySiB)(cGg);,BVyOR+R%pcvKX@6NU,#=1;JmdHvowbI9uNS@b(1vc5S$z&y2/WhS2gZKakN,b9jo7-RT|Q]/vR;<kD
m#x5"li_CQM^<`I&ACe&8(f}RkA2%a)^H?a"GByOJA*uw_m:`i_g8HqMX:h5Zh$J4@n*/*lc*!a=kIOyO
?A)`/Ee-6B-]BK&>FUuO%DeZWh;9K~Slw"@3@Qh(Pc%:I>X89@k?UFIP&BBZ&9vW;Njx)x
~"P&6.Pwxt308G_v[&9qC,u:/A(*j]RfRbW8P!7
"Avovkg+.tWxh=@Y6mLf)F~>7J$RO##y>0]Q;eJN8--9E2Hw_+@)WUWIL+2pt#"pr4PsL/@PfhDAOL(5t3R!/*K(>rr?V?<TTV|C]eIpfbz?,v`h5a)gDhluzdb_YHr65Su(-g;NrW!#>XxUfk+J$;^:vxLT"@8,C6E1~=bP#!>$R%vu~
G:pFzyXV=Y(tHveMWX8#tpUK,_z*yLyr7%fI1_<-ZGD!i^6s`
=VC@KD6ZFV44]L1afpW^a8S"J<`HpH,jTlvVTWn3O]%F&r]JoD}Rfn&;ZCRk2;qB}XQu/2(;cLu^%4>nMB]$dlE<hnIG_`M<rCP)w4{ast-*!N-5{xRFO1fI]1--qMMhALC,Z"%<`f?1gyBWbH8AI=50z2WQ14/R938@RDzOT*PAsFe41feWGstd0[("{!d/!SN:6Y{5hf2DyQwI^CVQ%].h,EaYc=JJB;^S4JX3pV8f0<j"I>R#]L!JfZ&EmZ;1KM*[K#V/W@.Bh)OT9@mUKNjAQ?M1%eb<bk$2=7lH>Q`5zL;"Cj8!>AS
E?}Xm2Txj+YmCVlFTNSV^uxd)ko4VU>uBMb*e,<.PXWv_e#<S+kMaE&@)f]r
_U]]J3jfFyk?w!7B%cAB
@P{)~bB+
-7=KxaQSxH#!]E#+A:q;13(-EfV~^ieKIO@_U:X"FL(TI|X4%r8oB@E#H
0)0rQ-1afUoNux%#G3dkGXxh/,[:xr[1cUhwR8cy)pEa]4!1Y<
Gf4!pE[!*9{-W4!s9Myt
d&OR<4-A^1raok!1ygRyU;OAHd=VPs(p(,pd+H!RY|qcxTqZ<KCg8&#C0jy0EI^i.N@2_
NPc%IO:=ky[4!hZaR5ql0<Gu1/9~LDU1*SD*bAA&*Bbr:d^]oUO&QVS(pJ>VXf6/&YkF_(Slh{rV-;O/p5>7OSEhwY&AvPg-L!LmI
m`
c^Ri[:PZ1Vk7BWqk]WlqY"331xBORn^3.4$U6Y&l
#Z"mRNlC]Mh]:*E+Pw!(PRa9J5`0g{e6PPYvN~r+3>*hE!e.C2H8^)KF*<9-aG12TA?u0BR:2nVLgFw"B5&f#EfR9=G#F~-`[zk|f9C>+]=e>w&eyn5s2f`&,6-!^@WdB~JhTj4pQ1L|W1-^3N`a@K40J,f!Q4tfGr(r</"%x*b/jRwZ/p.Yn;hu9lv*O<q%Cf(nx8,+&(]A6ni|P7"Jk?J}`UVh%IRdu7FYJ%wK
@UACIfj
{P`OFOMO;1*JbmlA;V/"2bGZKE;XgjA]e1]Qq$s6q
=<1j^,4%QW,D/wf%(aK:,hcjFJL)IjOnnO"5?i/91WsvGcJDc1NG!2#O-
h6cD3l~xmY5X`2F%b$g&[v?HB/FTio$x7Wl#SR^<R1a
Y&h7r@raNhafq(6hq)^qBgvUQ"}(hH,BER>L{;7i$#59i`G%yNDy4^?q`kG5OTx4Va*Zdo,tF5<1c=Zol]?y=Nn^-M6P]M-L(,G:koxn+>=e}%@>v(h?V5}yWKYr]wy7`5x5du;$g"F+R?[>o"1itorRT[1)HVee5Br8>6;bYnDxD+)DkFA1>h8frpcWJ!dj0(ZhW*Sszfe2R.x=kJbua9yx{!LZg2`J&=")}0+y[h=slC^E{@GwI6eD&LLi$4h9`8Td4e++?oDf.@8#;^EER1b@)U-=MWa7f2&fW`=u@MFF)B4t|V-jwE8qT1bVCY!ec=c!Bs_GXl`v)m`uS3:P*5Cw
DmI29(H1L?17A~/AYY4U5;c9<Bhc/9:Bl#2MB69|intd-bp",A#!+cwJV>dVLk,r&g4v-o"tDb2.2I%qj;[gCV&@&Z4rP,2a&>ui[41m?}BOXF
rynW]2b7VPEUa6[YZbo
<l-OB$i35yz1;K&sQ%UGpW?a*OEC2*_>i<_;GE]F/*:d(Lhg?Lv0WCCn~:B-dq,h,Q[`Z?]`bkYuE0rV6k2ClcIoYZ|/gx1-!5`n``</ZJ-2VYW2dowKR]3DK8gHkZW4k.rdie8Wh%n.17L0/@*;Vhznt`-1_B>W(44q5-uA2HF]uZOyn7N.xT+p`Jp6UY3!<!E35<1nBRCRZ+X/K?_2,VOl(j}!DG3+#6Ue#0,SfxLVm^Zc;xGUueXEf*]+LmJZT:5Jp*XJCW!C8y_wx93<FGmt+RFRHI/#<3;WG4w>3W<%#%q#-GW!)t@.7*hBto/o9%(^UPNj

@<~XDS2n$V6m4FGZZ&u>Vy:Th.2u}&jeP-jvJNMFiHAD{:dn[y"Mhs;/=j`Hz&Uu"-sRb
I/dDUZ/4|Q`Fd2{!|ABQF,us:2uv5YfIFh:E%h
c9!lgqdEy|wz-2C{8aYRT1+xres?Jh_ei|*
vz/ZD0U?LPsw?mwg/a`D2"eVu`e(x
Tu@6OdvsgU.m2=1R$q8rj^6=oVu^F^<Cp}^~opi~P87-l6x"FqQ=M3tv2{W<gU]GECSFc#7}+:N]RI+1LKVlHi:VJ[#2@s`?x*Klor_wsFDjWfc+!Hlrgp2WaW?w/[j<J9Wl028s;,rtJ]$JiOUmg|p2I;c>?V8F!b.Ww6*B"+Jp
;w-fDVU?f#`,k]/L/%=q8Cr,4m9>M7-6!4fLr"d=Yo
IYZ/lH&N0Q72VXMKgO;/*z7EEn@Ww#]gM-1e6_IPT6ta&jsCi5%+c$Rj^JA7-"#xUv`o,X0L1Z6JdJs{]<IN^yGXU#Sr
{6,*JJ`>*nA"dDw_B+.psP}Nnra:^CH@<w5SmNjX4^W-!Z@fOXS5f(&i8:fn_h%I}/BR_!_:BKiV$3)L+UIj3p#E0[,8V
tQ~,LFpA.P@[#9i4r_B&uUaxAG]Tau)AJe%Kh>P5w;V2,M1"8v`U1VY>a-_r^Nq]H0mr}5_T-GZq%Wh.P[sw>7Y
Y"deuY2`fUMea7_5j
iPp]b?;).
4.?QDf_W}w_aML/BrvnG(y{[p.i%{%Hr%v8obdMV-u16JpuAH4,U*?7IW+$VfOcs/1b@U[~@
e`HVB
O[CX17+}Z/X3v:QaC}.!fU]XuPNip1p1?%yB?BXCecphYG1m0[=y&ir9%c>S)[U.T
AY<5%VOEI^chw=gRS,3<AY^L7S?Il(3,l>wNO]Xa7RuR[{9TFyY&7QQ*no.3)!RC`6X%Lg`(]VdI]05G[:x6Ao(;=E4"uMJ#+9kW7,nv`coDo%^$AWPpOcZzuRFcl,.k003fW^,mIJ:
WY#KBCkyE^>(lav$s#T9iz67YLi@O<1"]-r"Q_<y("gdYP%tshe
c;*=wxTT[jIvuVGGL7Vj4_C,E]>?kSTP/8Dy&9bt<:0(.0!DEhKs44-P.EPiugSyXqS/qkX"0bZ7V"*m"-6A;&De%<H=bfX?v!<t]1F(5T.$P
=]X&#DmbU^2f01b`3pwB';case'nl':return'*ZuKs6KpM*60|E(t0PC?=O]^F-Ys
2{kX;GeC&2xF%ev+$J=786RJlEKa9:fvM90^npx;4+,Pqs+)-^!P?croB@x>59KciRnKpW.dm-!yrz3mS@*3AR&@e/Db#C&
Sy_nYsS6Zq9QYNLg7[VveBVcQVkVv|XgfZ!kGSMc@A&MwG3/7n4q$0LTbn,EmM!>1ema+Sk^qmZ~e|Sv5^nm+JOn3#s?oZq=;9w5_ZKZV+z%C^a=1P<uJ^YyU51aT7nvyZe9Jb;ssbHk-zYF-B*j5%*iY"[@Q^gGk-j>fnBT8qwy3jTXIB)}m
]^*,:j51MiU
l0S44-e++pSVl~I~q;k0N3URn=DWU"(;mQ,xWkldm(k!vNujCVqt"em{o5JX&~?6$?NMgHNNbQgJ4`-l5BD
9m-^-~Pp/0qLm.F$]oSPKJi7poucr7#Ut@`A7
BP1,KIZ<LIYUQmEctrTh4
[NAR_
G6lrNUa^o
lzt{+86Do37`J}Y23Dl{3sF+lL[+QQK.Pa/e9MtPo*rkZY5@toG@53!VZ1^7l;<8q]e6l_6m-m#nJH<XvpO.Q1R;_wYh@z5O%]8?B-_Ox+
.iX]pyO)rhGEL"QiV5
$1F<A@A
Kyhx;i")w8Dc6EkN`<#>YVK
Vk,Y%rRsj:XUuq%J&SWYP;"sU%0ffui?CC$~."QS]:Lfl4C?2`i5-)8`)rqv67+eZA6%Kz3gkmEzyz$B64G"Z|@/"^fw2{s<<*t8G86)Ah;=axl.mYM3i!9d:^A/!Kx)%xrS4(HO1ZKhcQG}p}B<s6FQ6D>Khx8k&X<(OkIZH9jq,KDIXgl{YrlM>~v1^&HN*]U,O17#kK422))2vR^
ReAb>-nk;M2)8t.$uy1f^[.{!?^ND{u|4+"0
[;3tq>&I!`p>%J*brRP@@H1G*b7TH]:B/U<;v0t6I]W[7X.Vbr#.NsryrvCXmHN+(76H;aU5_TK=sm51>A7x|V2VP
Q#i`fQ`Y2`hYbB"^>l*R[^jTo_7]e
E=m`ehik4L:V)>d]MEu!fw,+-Ve4QK-;$$;[icI)xFA)lYf<%Y6n1-sVK?%&Ll-9*]LcQn5VE>@Q[QVrYw"^ha+6H#H@W*c@H+]5;hs]E^T8qld:.&Hb?a)GW[:H`g,M$Yb-8$_Lr/T@tRuV^rz[dOW$URuBnnHOQR<[WyQ9|^vx<(K/*.;V:$~LL^.-QuYX>g{9@BFb7xoRKlKW<sAW>I-4HC=IHQ*Z>*;Lj2}JX3<,&Jw+{M*aqq|^VB>8V(MPS6b2Rs<TC=Ca<)G677.<IWK
"
A35o>(iodc8Vd^8`hbWIE9;`.dUlz]iPelA;FWR7T
!sOWmC89?G
sQs5O5)YpRpKM9fcqYIpZ%*o?!vUZJg)U+xdUIQ$$pyWvv3>DDtK.:Ug4eaL6GWLz">&QU4O^Z0/o7_wTY+V!r/`x+,Z^L#{=x9ia{jzdl5I?`W{*N
_I:d-sXJZ,m9=AR4u8D*A))$Od4vs+L8%FYi&=tfW&;Lys;ekx)&]&HJ1EgLfLw/V(mbBZDcfhwUq#^nhS
oL4>)_bw,,VCaB/NgNLagrgE]MCv*>#LTZY+&CW-=Y#fPm-|i;GtB`Rx).RB<pbEyFH3i#+rV[ccN&ohuT57Xq_d@o8ZhCud6J>~iQ"C/nDs>TT"BjI2Ooi-ioMI0Sm>XZ
x!"!o.}_zSfvt/wk2QM#.Hw_y6WHj]$6&PewvAL:/g-KhmS:d<5&L5ADuJw_DH[Z`!&kjXefPla1RfI@jk.u&`BBLW"CY6u1HeC
P1]X<VjI0hFNT$>:cFbkMTGF4mW9ksK6n[!Z>fXy0@@nPcB7>)&@HyRx%V~r3?VNV+S:_D/H<36^wR,Y`,idN
-db_8sn_,J%"_Fy#7@`p4.ibd>Tf%G6j$x$mO9@wem$]a6Y#e0>=FO32x%aLx[rUZQA$Z-h=[#0dp=u7r,[1@Eh+y.Dl//hJXZ|Jp*[h-20?>d!5]/^(JM#%5sxb>FAbqh1cBZnCy3!w03t1.a{J2B;tK&XEOwuA;]vTW+nhCg4uW9TkvrZh:s0HrZFL"S|M~P3;}KOoF]J_Ws=@t9+W7jiShT-+g+}PeU9"`E6y`dNP~l~BjD_>]HoZi(a@b.z_TWL_asBk#^$.bHV2&WZ"72WD4(sfaDaSRHmn(WM"]Pux!EXnG`/,o7!l"/b!zuDUj"
/4dcm[Ed8p!R6sH~9:.1)E6s=_5;j;#s23-7s~LFo73A("@vIV_;^ocHSz)j/K>fWim92lN.X&(wHA[4$wLh=!ta9D8s=-!V>F4vErOl,@??t|R[>`5uZ<W]+2a6V}s":R+,_
a%[[<Q=
BJRb:*Z$Y{![_kEmkZWG(JAK%6I;vpl`8)TY0$&~p&C74l0ZPVe/(MPVBT2]<cO=6j@a$$3M?$:8vZZv>dp_rm5Hf-QV`5vZ6yD
$eAamFKsT43:q;fE3)0R[<v8/N;

toZvhOD;r9H>37e
J89,su>[ij42]UH]};>sLmq]C33Zc/vRsdP1<]@XD?.>Obl*=o|i?*rVX9ABU_2u
HNf%X{g/5G(0Mhhj6*7`Ldo+5"Dvc(_5Z-BuqX6%S9
a!my)
EgW.)(
Gf(5e@9LQu:1
TvgYB)HU|Ud7;8Yr5?z&C!X!9FY#+)hhs+/IG@{RMC{baH:)7L[fmA;^*@Nol?!W--D<e1i"ApjpoI
4:pDbF?
g2X}mkHE(iX,7DOgDVPSCp1G#&A5(LM+E0`qf?:gA4*/EG:LalYz>(OT
Qp4==$bD}^X[_IM+#7"gAf`hAfIyucKH
o
G~Lr5"qTv/bHv&3_]yJ>?sQ2c
8,sY)z(hab5-"~^q>;70ZA^>c/TmP`u7iA6b8.T+KL=IZU^"g_e-Ru`IX*3du}p"L_EVa+iO-4!:kyhVdnxIv.l]"}PLIcFM+:T>s!Bzb1kM-oUL?7yX[Z*ua%XC_"8jQ#6`e@QXkXvX*I_]>iDMFTS9n/-kro6<e.s{`;h|ewAWb"
5,-H,Q][Q3G0t=>,3V2R~=D_D0vgXJ@O^_EX&h#kW;_dag90xHuSpYPbBk_kO93^|5Owh&O$su$*)bd
?;Od_?SWQT9h!Qm>%5]6w*E3^`z]VoCsX*,j<b<SL!LjC&+>4TtiD-J9Ww_V=GTVDA%osb-V"6=]|"DlVB?,rK(o~7<Nnb$P,ebN)_e6.T8j+Rp(B$=aB0U2lTX8iX}xdrur[B/2|+U2kjpVDa`cg0g)&qH,gDc!daC&#oG,"*3p-ii@#iQ5a"<Xi_PPx.<CP).U^d>Fg:$/%iF+1YyH
Xnf:!@*U=zWMY%(X-a<3/J-ES8b!;IYMQiI]<"ZpX]69?u(1b!X"d[2be?n`irg!IOa{F7VkgRs?66F!yPtlct`ckG3i*RSOL<kj6UH](Tu_JC[*#5r%O;<P5N/SIJq92+f2eTWoq;^M2Jkd:!e}4%.ggw1QJ%nmNkU02sSvbgBM5?t0;<fa^zMmFh:F8KAhWU&dQaAxDQ^+_F)br+m!ms;DPM-1ZL<k)]8EE-VOo7U/Qre8XAQ6kg77fXb
<f
6H4p:Ta]9Uz0!kw)T`pIrPE_L]H@Pg`Lc;:V|5d9A62]DDn2hDUUwbm298=
[w2sjOZf0@@6P+&oLs}Z
+c;_sbH{I%n8b"[h3j4^C6PIy40ULIPZjx8nIBDV"8+vX*TIHm<3kF`h6@cQ2nwTb/p2R,Nr
fe2S?8F*!1ebI`i&y9=]"1|dP(7Np&_i!%K<8s5S8>1wKD.prwt^>f:Qcpzcp=n[_o5FYa~y{o&CE7-+ZbtYjrVszs{
#4u@ohnK(e:fF<&
`YMo:+t(TU;P(f
+r*xiTO5w<+US|RX#`ugLns
QShucjcp(Jg,[2jkUbv=0r
Xvv1pshor#RC6KlETv8oah_v)8I7Fx?tlCbZ:E!htR9%+g-1P1d;T,X@2b6=2@-_|R[yMus9{WzlrJB*D)R73s=p?c$/HI~,<EGte02L5H)fP^cVc?0q[@XQ{d:>">y%nrUP4U]l92z+3b;e{+<^H%S<!r1yL

*Ucfs(hR"qXWKg*~oN_5>}DLFc
#[hc<R{argb@nN>a;*H%Jpl9]Tv2X%S9(^BJQZ80w)/2x
dx*)r?#B*Y!X?.%q"IAoZYN(Jy%);Cg7@)|;=]8W(xV[xE{2gT~P9noY&cMwA';case'no':return'&ZuLN6KpM,z0|=YvJ8-
8QkV6*UbD:!vblk7p3|RZNlqa/q2v.r"eStwa6zHG2-u!JCgenSr6d,g8i:rc_p@(bU
[C$p&bq=z)prJxzD`6^A;<
[<WjXe->Cm.lypexYkb)_b
Cy^$
4J/8?SDNlM0@?-iNDfmH_;Bc
zB(=flxxq2bhEdO0yp`XtVFw/bdN[bC`du%Vpc<D4%;plvIy~y,4E2m&P`n>Z%5`rk7H;Ey4EGZ+_ACe?O(*s0naU[eub,)0(OO
*@aI.wMOx,wA+7/A;5MU*+ZaZ)g.86W6[>s!My[0!ky96/<C^*2u$-Lh<v<7T>```+)?aPvl@3D`Qe@PL/$^3dd=SNP(S!LAh^^Ok:@bRw#
_de*ujZ9h@Xq|Gm^!nA_K8"mumHNz)O%$x-
|J~bMu(weQEehlZ!
yEonULuQtrY~cl,jVTeDm_jg@0/mJN<Z+a"kJaTM9?]eDt;Lc5]aN~o)]"%mtnmW4G]PDE-)P$1vu%d.?8+lObG8",(o[[/urIcjYir&Q/y(]p#Yt9Ki0E5qk{gYIl3j>~F%@]0t/ThA!Yr.FZBLnw<fy}]dmRI1C|L59~ue_#BU[TATko^7tIE_*bQEeNkM6+>vlT>2b>^`*uGx:e:1??E*Mv+<0tKV_C:{Y2N4v9[k.7.`<3"D7sUDHU7hMzRl:4CjJ*n}AGB-L:8aKNX!#wPc98x!@p
&rDq4?IT}`fEv^V(H@N^>F#f@3WqWFH7?BFA|%,oz^<`Iia:pih*hPe]+#t6
AC(mHonaG^^adcolmXExGx^@%ClHL7D-FXce?<B}^xcEIq"(IF2aqFT5crf]9S5:2OG=0@GatE*?rJQf?1+X;;F!2
3*WFHQF.t{BJvgogl(fhpuSFkt[Ih&h].34_LkdBT;iQu3$S1:Y.0Qp0^$S0kky0O"sT5XKr@bB?rv&J9t`E]h!H2P]okD$3/gmSI9@lh;e2*8_8^I[sy(s`/ta-gWv~aK53sOP~auKy4wgP=hJU.7QRfl9]n3rF)~!oy&w!fIG^8mK-6Vr,(Q@RE=XgL14AH7""C+2#/r)SN+De;FOh[5O-KA+`.;JMF)E+8)Zi;2ru]f9k@L;h!mb5eBl?v;yEs#n8r]1ttJMF*t-Qh;GRGQ:4t3FS1Ic}1e,kG3#VeUb;QeS/)@`U%$!*=XRzk;kp[bX4Y?G[].La70WT8sGs5X!HQ<fsB.a"<`H=MZX;-?VtSI88&+s@2lL+6*-}2ZRm]bkGI,"ZJK$5&@x?As,PmHIn@bm86F+vfs]#V")R&>
xn6=;u_PM:r"
[$dbjJMm%)Mm+n`7"V.[
P2EYDb{%>QQFdho#tS^DmX5.*SS6&EKHg1pPOBi(].Ts&(N=UN4,llHx4O)K?#G^kb1v`BEVq"TAQ4m(&^2JZ"F[?1Q:V&qhtrdl{eQO*Fhy9q]>4j-D]C*0o4;r!/#Qq?5<saCM.oY*&CGsa&
m69wpGLrG78]eV-L(r?fr
4c[W&1o|TD61So5
3PF5!MOX2nFel@u4w>ulW(jeJ
A6rj&
2@mjKG/4>[V4.i3]ad*]?-DhvVF0(sprTVYoH"B8^G9]cd`)MeFeiy2GF$Id+%xGiKSdD^%S3URQbzykZ~5c[)trhh<!8QZY;o=nx>`*9_u-_*>LB6d88{7z-e0dosk!?H]rY"-|Af_&.6#CBPi--OYk1
KX>13
aK>h*
WT
g^%-FW!3BCgj{JW@r>DDRw~eUFX/?IwW"A!gs4/YEwK[,1sAYO"%u]xTPn*m]2(T,@*P|W
$9HjcV0]8Yo+pA!~+opOxHH6B`[q&<V&V8r&x,ykWfMslB-Xv:FQepOq@:n<PfJ=t~U,rf4:)Wi4%5c96RW$^(_J.%#6I|qTdtcMwmaaN]`iZ3WIR/qv]G;r``@ZcWhI7(pATCinS<+Z/t8b@JMi5Q8Z,d+)6m){Mg,LNHGdMsv#*.-)p&bGLbcKY0Afl6H.c,>sr{E3hVZ&:Dsr
IVf:bcFOc.r
D23qp"ix2yi@LpRqMXUN]HC;mDE#-(.Fqy>>:$cj.cz3<yn^@MrQ}IyZt],DzO<lMf3MVU}6!&D_tNQRrJ.`eBm%2i~"j>pdAN#%X!%+4&LG9)P/kF%xaMQt?wM$IANILT|^0ob=fT#GU@)?vJ[.B"AbIiu(v]RfX^,I:^xRC+&B+RJnMI|Gu/&9m
Ed`wvCld.^MWK+vy%`P9DlGWC4.]=NqW5*O,3acv&8>wjy6Dfl>4[GnS6+|ktMt=2cySXq5u"IY_m"ESE3qDOfn)_Tg)d<wQbkhjHO-H2UOPibhJ3YID|K#E0g1H,DDVWg9.>L@6@$M,--!cn
Lx4D$(#kdCq#ZsRkRU"DZMrBS>X4^d
@/,-*UFKx(<uUwZ!XZm87$Y}E,il!KseA^nYDe=W9imNYn9O,H$0D9QjFv&DRH<N:ttv5.hYp&hWv]!WDS]LIC&[#C:iaAgg/":tM8#x,xE/2RRrOUt[5A=omqu%hl.2g9Bml`g$!3Z[r3r<neA!"g4CQF5g
dYrjs@->/TN4byw?ODDGyrSaY8^Bw_f
S`]eV*,>qCe,L3b.~LUgCfv3z.Yf<VfGTBg>G0)gI1l3TvQdlTq7VGn4&]HS7yG)|JxYl-&HG;!Bg%U9*[#s0Vhp_RMKUnMWP
3mgcao2>85!KRVb8l?(N5)+X(_mi[Tex4tO[zD)5VK>x~UA6NQk-0yDve
&-ne+Tdp_trl)Saz$+v*_o^w"`O9KOKg2,UkHS1*aMrZ[D}VFDynjYpR}j-Oph<G)*>]-.bYy=B3o/b9Z%$dA/ERwV.Se7;/$=,#]Y,PmPbF#8
KChuH%.}FY1JH2%sR,*a24uKu)YA5scCXWy@+!=<#q_[-fGFX76r1(5]7kok_;%$!X,Gi!lk3vR#D!+2*^N>Kb2V[jL)_;"`;bG6PPG6[r*H[Y6?o^YRflniQ@$~[OJg+eOa*+0+2d&`7rMrt01rpA/VS9c3$xFft~#R2-LQ2A_1hYhtpm6-w.9x`4<MTajEi>p5Nnb9T]j7y!!}W+gsyXFC@bkbV`4y7d>!izMZna97Jd1Tw
6iEKovPQFYdGh,MmT<_m%h@L[-GO!8Gpc"r]u?w[x16|U1wWrCIDR.R$x|3p[7ZJYcN^Y7SX-FqwUi>8;Zui&//2t?;nC(Y/`uFiQX-7m["J/nsg`%sVve,}M?$<WYWr
YZ-O(#R/{>8VPnN9{apB{<LDLNAoGu-A7OoITI7@c9d8GZUR,iJ`(#Yy~0_YX4?f}"VO+(GE;-G/DA36E*5%:4"dT9Me?s2
Qr/S$v09O=bce4vhcmM&u"jS5JHD*iyRXW=``:[q`YT.!N&G7sJf?Yca`@Ou(1#%{mmJc4<BH[HFp6pd]`2kIndqL#3,#lhw8-^nR7s$~.7wf7E"aXUPJh_)Xs+:dQ&5m(SD@@_YQ2x6gSs,(PDFg
s[wLLK]@7pGXP8;AV19uo_!YFQxRCYjj]1|fNV!MRK@,AJ|w*AkWbQ+h&E#c(E.pCmrN2:u3idF1yH@y%SMTw7hO5R*a")WA`42`VnNJzun`3ug*XD)=/OLrlMgN+#Y6:uyWR(Gc)3yh?>0ACUc>z2!Pvo].4[o^nZIJ0J?kV2o!5O#E(PT,HkFLmM;)nRVAX:c2u$<j;g]Ut3hE$3O(,RMMw/fOeRu!)F[2BT~=S;Gv9k.OJ#"b]N7hx&-UK1f8mluWGlZ*?5/yluC7&EaS>sH
-%+Hu[k68Zlr2!eQ5oE_ud
V
iCf6P/Bhf?aWw#Nos
eU.NURCwn}
L/JDmM(mp_m
Fs5jV]u6<
1C:<Q1n=H0(>HZE]
4z>&ABJg6"_Fu&eDCy$8nH
T_<f),.cL66onY7Aoc+,,B$O
uMx|?+EFF`I"/;k$A!/^IQQlN7h*4.JN959PUWAq^V]E-l[OEb8+N)otcB=3xldOta.OP
9N

>d^W^^jpSnoJ+ab+$_1Z2bb7&$d<edGen)%dOuHSC}of({&uGEMdpAhD0&TUR}q5=QCU@cgt44(L/dP2l[V[rZ^VR?^`6Oy-#ZZ<"mH;-9l@XWA@)7rk
{r%P05tO)1fohw|]O^HVwYoIbXn8oj=34`Iz)#5';case'pl':return'.]^@r6Op-*60|SXJ%e}+/byY>G`"zi6``pDY*R[[#<</r?%Nn!]T9&1BA#+@yRA^jLMq
riraJDHJJ<=ab{01QJlUUUbAl:^UpBJI6}A[mI@V6=s<K6M6e~wje%7xiXv?=nRL78h"WuryD}@H3g5ktqUn[&`mX9q<#]$$6AeI]/`P<htwQ+<8Pj+Aa-vrl~=DY
#4Tf?&W<?ZK02SY|*nlQy[S%K0,mTb>9Vi+xX@,X#s>dn>S:x/@{KV5okZ>/nrm0<?P_95hIP=$ZtvnlsJLJGaeYH5Bbk~cp88,,Z"E8^an;g`ItcX3!Yl8zb2u5!Yy<KYn<dgI}(2!~QxS"xCj$Ss=H^>WEO,O[+R`i2abN9(lRd0(#]G5hh{^f,!m";SXhnAk-.@7^P:s*+I,[x=DuCEKIddVkuj?h"#y;GckLd0"6,_/$LkoTLIOL[vE+LjZOii==u9_Cg4bQSq_WXeLCF=_>-`M?6tfQ!fH^S{N.<X+G
|0@#hh/4d=Q@roVS!(CifF&kCJQl9hOn
AjH+
/C>CkT0uqY.A)$;lZAHG)Te]ACiSFnDl5R&noT10e<dFz]~PI]Jf_&rp=#nFp6Lw(Z0Fwm,lf0jU(sk%yeBJpuh_W98AXeND?f(91?cQS2rn`J2NPC#tmR67McUBxX7QG3zYX)E31&Leq5Xls(0x.wTwX"lEO(;]D+X/8^[o^:1oy1~/Hf7>GR:D5mp+y.rK!"|HO=s1M*hh
`)i
&mg(E/h(HQM5/:Tz_JZlw:11nW%]-T>5%M
^W;:qRMct^c#{J9S,fhoM,h93;]m5[AQu?49aV<4W]0Z$"mt{ss&[W]MCBp=%$/xF;kx{)d(HsWD9J7-xGDIfM9AQJH:03ZdKA1]NKnN7`",
VTAElmp;->GgE!D>D5NtoE0&SmPk[9w[p;EH6]VG$V*Rho+M=KDbG=<F_0
]YR6vD+EWKN!5!D7kxSI6$CiT(O>ol@BMpF]$UVNyoF`6`UdY0?#1cb/5ec+7Qw16d-n:961G<H+H,V05O5F*"96:BPN0wp6!icgwP^B;Y($IujW^b;`Li~q;fi_#$:pDC}wadqCapxkEMb].d9siE=pBN
#`Dh/vE0&L/Sw5+"!-)>I-f.58U3n?dD_ItAZ-p4djvyk&we8<5F?VwLl4>sQmsMQ[NO`{Q$xX#CSH0zpKedL.DA&Ga:O8A)M:g}HB9=!8h:+<)wHBKUJ}Gw`fD4hj5B]@!O;sI_tR%3Z44lu3X}ti>^m0xd6`X$
bhY;u5gDo5NFYJsy(Mw#s,{!01?P/6.HoO%3b;maOWAXa,Ch5!B^Y
!8I7L#!P;)!at^n7g)[8NU<3Cpq2h#f3OJ7F*aS[RrVc(1i?O5C*OOCpPbL!YPY1n]h9_sr#m+Vv@;@[Ly#qGuHLljDt]sYf<>YcL.*[4F_F0&AbNIpM@FwOh5-iNLzd_b<"}lO@uy#%42uIx_44DBD]),
E~nh],[Y/)gZWn(5,~?RWmApQMamiu<(jA?:JW+>RC+xp,9b*uSvHU
UUz["uKLC`YM2UO#qW"dkm}Ac7e/pvpljR7pN4/8]nBZ#6]bvqLjn/e;mk&DA+MmEw`jo?mnnK_wvMq2rkH&Jrxy[kAaUkYJI-7scDzcfLsij4,]ae9UBNEpR]Si:[6q8)$!A<CaHRE(?tm3!<-GhenfS0N$ok,dtZ
*agCAz/K9`k;Zsm/Z>/MFa9)W!P."KE/*u4dS+X$/fkRBTfyVj6Q=v&dD~JNTK5kjtG*V6r[uY2W,PT-"l]>#vUHI;.GT=I?X:hJ9H[y%#;GK@Fz=[4Z1sm!IQ@%R=tE3P[uX<G
D~KGiAOCE*bCg8@i^qHJdBD8Ij"M+=2q74SF>O+Iq>N"rm+")C@;gwp+(wpN/W#VM.Gh;+YRE8[{&h)L<ONS#uDKi1!"1D(JYn/wL]Et"43sxYi[>
ALx7rjLySp]EZwl
h>q/2Il?;H2_y3j`ie/%Y?VwRJxA^g<d)TPpkP:-VY$r2c&d?:BQn:j`_akjDLMP#"3aNr`gGLK9?E#_>
Uc_I^xtoNV^D&?){h|Q"lob!-nqK1Pu`Y.Xz9lt-;a_jL}H3g2ubJz6Hwhd3`3u7*,XoAQX6.,M*_>d"8PiJwC+v4412H
Upl"((kQ*.@;lYgdlgL.sS<g*[_EEZ.n;.VWqX%>cA@8UjE}f+M$*BI#]O5^
6laf6:z:+@1L0ZrZ7B6&HSri)!|LDrSE-[4vJ_fGuS]_v/p
YHp!sD@l,#/,g21=A"!(7xSs+Y#Mus8Y;3Kw5@")e(>+WX(1B(VUi$K?z/)m[S/p#@U3?q3/P"(K[Q_Hr<{J,-WMbQDZbSGCI&3eL`"^/Sj/cLlEk)%7t4vZ(
j!+!#v36e9{F^2!
&V8C}2;(nY$YEo$)8aXF3(86k%x5SCiar%<L9M2UIfA-Vd]D,?:]`ih2BXao!nh"ufD0U81+;0TKbCK6DjP"i!j&S97Orye6<RU,k:TsJgmlw3aDKm:8,6E?L3d=iL+?P6kg-9-^~FzUsc)di9EDWms4:=]0Ur2fLVz6t/<oAn+:Xtd#M;-,n
joC2x>)x%FR55gsORYQSXR;LX+]T553:E^,=b=&NA6G0ge%rzs{8}pym-P&F7?DfY5kEWGL5<U-oTjfZvU*rN-3ZuZtePoDP5Nm:%cpg><AZT*%:#FXvAH6IGc}Ut"|k-?HbUZ#%vl/FXFdSFYU1URa(TY:Nj+aCRDCFV4xs6l2iQ/687`.q8%
@Qbeg)TCygOjD*n:3P,I]E&IS*"q:L@X,gK$4S]PYhDK-m,XXNB@f;>:*r;urC8LQ%iJ>(C`d1.WiHRO.Ig^iQXp>KaLs?`y[DLJ$3$=MM8=ZZ#"XeWhAzNh"vnITh;7w41QZ_WwJv#&-J)tGc"d9=##&qNw4~/),22j[;8H;Dh+36?`Yh-0ROd9U&8JGmCbY&q&@E`&TF:wX]I(Rk*mM4(,x7q/#p"6n.t4k!j/+d3SKBTO%DZ$.ka(_{42h|P~(afSQ<EJ(@:vQl<>X8&83XIUkEFrT!of])^giQSmXWLAeUZU]XY<([2DM^FY`F",V@V!:/-gQ#b5OdWZhII.#WN^7Rp1`Gc"p]0&rjSwJiVZi~,7)nmmpUFlfd,QE]+88Q;7csz&HSCBF.9wFfvZs!S,nZQ{%J>AVFc
c:m*pGns:e`6^{[R@1]]-eD3x>C."@iQ=g4mH7[D2z<|22^Pm)b|xMS*p|iQ-|f+.!Cn@WKc@WoLR;+#E2G[:FNFQ)e(F&$6q[Ci$Xh{<;!FKfAv%
@eiv]W]3Qph3`quOfxRlys?:@&022<RG1>^v7#*LGS?71+v4Rf(EsYR`pn:1]Q=!iIY-T90q!`Q":>LM0fwiAYJRR)8H-Q//Q{i&7UhU4G0xpn`HXN5Aw.oO#O)k,)ICa
3Bw&0`BCDM2w=1Dy-]a0@P8o(Bt,5R@%K_-jNVJTYs"nNvN;N*?P
d=[kjW%jfM/9nMb-5i4KGiFOh?


]!dk,~q}gia8YoUhu7;R<g+^&#s3CHt>F|E-$Eeq8O2>3hLRB$)2%)wlcuI3[k?.dEyy[nti2/6G=-h#rd[h6sQy,:R{#5H~gPNX/ffw-DvtH3(!B7_%Fm;/N@I&5|m"o_bz=BvYdwZCvXs]c))H:(=AZR2ze&@IZS?<F@Nq?[Qhq<J%]~RRI$(nY|#DWn%UM
<IAcp.XX*WA7ByOqfBGXE]otuhG%0rrMknbDv~jf_p;vA(O#
a*iK3K]yX!wMYu>a+I:RUl7O8m|x}Q0,|j[)Ic:jns!9ffY
YP]B]5#P<g-od=9q/J;+vHY6;10(a`3!AVx`M<6$w:<KkSVZA5w7!TcV";9n{d}f[
wE+MV4!j&99B!WudMxJj?9IuCD{J35Z-E^VTdlw=sW|Mcw%pTB-WZ+W)FuEonYh3-UPu+ueK;/|W2V{<6qgLqf54G#9/4iD:c[0lq(=g,bKhMnfh-2KXyIrbbrW0QSY.I)DT/*g(`[S`?*E]$+EP/Wu94_:.THm
$AmX4krkh)bY(o*61MLr6fM@m,[KlqAJi[-w|4@PGx^o=sNKfqFr03&o=OTTK@QXHe-0=+P],ZZ[=u3*>(mZdwb+x<+jpE|&6Q#@v_S;Q<iHI$]on#Oa{l?<tk$#:L}8>BCl%"vr!<#DPG+/AET*,jqV%B"7t5H6v3s?]dH:Nf|mVp>GMpYtBE"3Wp/L%Mii}y-a37dH=7DKkiRCGs~RslQH|ZFe~C_P=A2Z:[sdyJXI=bgvlkn:FEvOuDwKA6aa@MnY0C0aw"SWM@.x/O~3ENCB:1M#Qm!!fedgd;Hg!UbXs2X.bIubfh`sgsz;wI*DRUWx4:<(;2g7p]_fn=xpQcJp8kD#4X,UuS:,uqp1jk>fyoVu}w.%8rrQ`&?]d:#D/5eW)sv=w&F9,BZ5BW^4CnA)_D*MLNF*D^-W;c{e5W2
Y9U`$POZ,[;^y9~"uGri9t3y
nteP>gm@s|)y3bYJd0ukUMS
St7rnljwN=R<&G1li(%"/vutM>lSg-=,rUa)Ceuv].#+
12@gvn35NOGP8u<Mer/8U.svbZ3ZO6Phv^owqG85|k{`r1HHC5Y>w]rXv"n8Pl>?UJ#"~HOx7tWO}Htn
R<Ep9m5BE"@MK6c-ea?mKFhkl&pqC}W;Y.BBr7/sK9Xki*:^OG62/1:RX)L|xvPcYT[Kp"IFN<!=*dpls:"t1odpvdAwOCj#/CmaNgy8F80C"|pimf=zS)[iF0_NmJ-FJpfD/Go:-I7/Bf"dCHM42+tPN}JQqGGx^fMBowPIaa
D![kxL~m"xd""';case'pt-BR':return')]^FD7npM,z?z-0t1NY?)Y
*6**lHd[)&_l7VUBRzXJdp-lS~0<ifv"xu*`K~*0"I.(s4qR
>vHyYJ;fG-!ofEA_^@*s2B
^;cl_aux!::,>;e8T4GgvDqq.ZJHLlhK"w.gh8]ZCXwOyaE
g&g9`%U5+iLEmGujNq&%eOb!9sXQ+h&"Wr/rfC1obh%wgPZ$4?7"Xa&+1[rEV00j#|SIkJe3?.l+q$HFw,fmkzs"DK[+Z0j.?dD,31z%MA+Ykq)[gR
cFe0[l=`"?syII7;myLIf9Zc/@:.t%McpPD`zq5#pcZ3OK)e/IRg>!.$)CpUx>I/U=I!"%-?J5mkK1x&XSDKTxSZso;GcqE:<hHPOQb`{8

j^j-eq49@`>*H[4qx&8JG:@lJ1U]Emyn.
hvOe%8N95/$FaY<;V`WFk2u>NCLkWfe<gDOLPS(P.*RwPBdt/uMJQEr2%_wxw>%;SUZ`WM4[Ux.u^?5&5WpJiKF0V9ni@br*CW#@3^_>;d
0FNG>_WcS#qeJ2h`)3rx
l?I,{6kHO-@fO>EaAf2cpm9M8(7@uqnquZu*w:`a0y2y3Nr
&=KY4AT`:hDt~?+spCH.wVrVD=/dC
F3@fpu
?StuM*C
jFF@YS#zwFkY&%x9&2@@P`mA?}^cO_yP6~sa#8J/VZUfV^7}2/hUjjA=n*0J)AU2nKxSx,e!8^Z*R;<Qo}C^B-SUsST`Ib^I9yyVCE!0@8PQsQr@4QQ>?:rvVyZY3gWM9q98k|`UI~x_`h#?B((w/tW;MCk5UWpAQYDAry]]FJTWSf6;pzeIff0Xv}q.!8ee1NN,L7xEcf+W,oQ[U-)&qJA:R7?;U;iBnxQOflt#
X&BUHmC]83uXNuq!|&~9B2??]II/cw,.$pJ5Nl*qE*AK;^rJ$*(N~XC@N5&bCd*wz8d4r+ZZ-!]v;vM?9Xti"@"p@R#4i.9_lCr`khf%shst_:^5nj3vhaBafh;=Pp5u~rM;5U$@lDX,x:83RJc[S`O23:<YeP*vjlsU"^ar|TKam]diy*<P/j4J?23UC-Z0p):h9(02>T?W/
wwIJ=v%_{OE1*;">Q(uF(ZUnW3pn/W{5Z)BbI.mdyI@e{pbhl!Xne4!;.#0:CF)a%><f6FNxlOTuV]i+2/V`QUGRoG>^IH^E{:n:1KyiaY]b?R-,{NlJ8XIvDo4)56**(K=P0h3jw.`f(aE:],qD&BDuc<+pYKDmq)}k]>
%N&uXVd$_-eI"^>cc>L{NzKPKLuPkpxBhLV"DPo{I]T5@"lqc"^raNh_:~EFOUWmkQK}0KgIK+H)Lz"(e>/h`^R5BF[}QK6YVfJWp*:a#-G!BOFI1p)5;(>2`trbCPp[isr=5ndTBb%e<LhmIzqn/3t7Lgi!^BT2t25~0a/O7t9</e_Ieu${9EICH
W-E$F_W"+8bC:WVF*cZOg:o4ry"sj59gat2`6CL{<aZ;AXE2C(QUE|3,]gBck}R,hT96:1rpo|Uy*}y<"x3:<xGR(]D=3_O0$zi/V2eyWIZ014"-I+XrZUk=[mj=6z*DN6ns1>;*
;PhR[H&h^I:KPS"0`A_$6[Z&P-~*R^GVf9k@@bA6f%plSR4]"q%k*g~s,9)amH>1K;P`+_;Ra4Qm+eSA5a[1A)gU0,]&[$<YV=IjPHkiG$Wni!OyM+;"ca*rmc~[0LZlBwp97Pj.qk!N6"FqaG-:Z;eX?Uy*VZ<5kBnP?"WUV.!.M"_]Yh,qS;/h^xXH7I7PI`^*:?kBS7AJs9[wf71i%D;k&8O:JJ.E37&8K+/[z2z
v^6EDrNMf7<S.Yedug4H~-Sa]Ym)8k2o*XTc!vTL-W<&ANl(rI<SoBxPJ%DvMszZF/nLXH%nYMw0$P0K~
Tc|O>*Z^:5U>f4rh+eKL&TI>K,w6)OAVq
W9EdmHHhr2@P(-&tM8;K:B`(6<)*i^j/IwF3xu@(ec!)%&iE#"!cBo{9Ar$br"1y~[UkTi[C;OjAX./!bBZD[=?9/xG!`8TX9:>Mkb|]2L,STS$4irrPxB~O&T!`Qt.sB^[;1B(?BwupH:of7fH``]a$4u|el28m7Y@7ZIwHQv8BhRrV0[i4>RqUF2"
7c#OA]Hw?_Ewj0#wp6f;I&}fTLR0m;`B|frao4`teEDEfJIpdS]2C(mKWJKNv!87FmEt|2W_#QiUpRK+hJ4S0p]Y3@8Uv,k+JE!"d)R^5JqtauM$P`*4SA8i=GM9Ia8aJ"C(
9A;!#LFE6d7U@]L}rMX^b$>&#T
oZ-EoEtMW2LA}6UIQ()n|22In;G3"b9-4GumV9PX[Sgr^4!+.Ja3L,d?bj,b4_73i:A3Fakf~@j
(07dNlmFs]6oMI1dmw|u]
4sX8hYQFw(TuD1;E8&U:ALiEx`%i#7|/.Eu0{j_qWU{_RvncepIM5GCaqrhtGVdVIc!5RCRTQC&W(Sxv%C
m&nC)EI_&qsASS2hn@s/Z2l_
P.8,pgO"xqR9Q(!cOJ@0*P=@1`:iFnU"o_{&]QZrt:rulNKVhGGkJ=XPP%mCr-5QwC^j:QFAw1C;@$BU#/KnXLLX9tLu=?j5I[wwL-e#<]
kHu89}kdsn(XAd-M6!*?i/0O?TZ{eYL)I?F:f!3k1#8jU>ofqy@V
FWr-j$pog:J=[IWa%gqGJ(RMG[`CYy,B(:iSNt_^lh4r0>6wjX`oEvwDj]!CWToPt<$Et(fPwPP-70|,m9_*H9M:;I1lZ98MP$}ZdSFf{;rK!Ws*fy?,$u&::q3F@s$?QL|$AD5!AA}DDHFHF`
!wicH&
-iY9/PpPGNF7"6*"H9
_FYU<S^s(`[FREj
ldo@58pSshr,bj`&Omb7bX>5Ph51H)R+xxpe-/i%$~W-9I7
XJ$JXT/>B12vyFFeo*@f-HE;T|FTvvq~s~l]9I/+cVs3]t,!&J:^X6@nT-eC:R.*(?7eK}^b0ynr0R^}HXmQqw1@5GPEkf2b4)]R1^v/
*L*>R_K6XS`>znc"_e|[|9q%*%o
v=vvcaUUiW9p%)><$*Qi)GTZ~Qify1dj/W2hNbzsORG<y?ScE+JmP*w2le`;s[Ntvo@3MGJ,ackbUS[?3s7"p]8;pq|IN_AugmtID9UpQQhrJc!Ysy4^gE5+<)H8I,+;yNrg;,Qd5Q|X-*[6*0#;#"0RhChGMIPQ4=*7*t^b)FGkwx{%/2N*YDe,9/[qbG~P9PG!k=muf:WYPV_PY`P+kh0_gX)g*<]6B,%jGL7<hW}6dJB<[A9iREed>&O4btaiMc4%wgKS>_V*7J$E:gc0qYn[+MI7wX).bsQnO5r2`paXfFA!Kcf1BKK6_]EqLG/Oe4CAcuxFz**j6yolcYwZcrov$mP*48ig"N:;np;>?DS#T;olCDh!LRafPj4&k+A:K]i8JXqihSAjOhng-kn/tZ~GBsN=Au*18I9i01XbCfBP+x>LyuJnt34qbB]TZqJlx78v!vqV!fB3)C{6W>*n!.T4dueJ?>->BMo:0)x
bX7Q4GhX9.Dp]
,;yN$I^/U@^qBuYe16O!ZqS4GanQH--UB#_P*F(y{r$J|<bTzp]L:Y$XAJW;Gkr:=JWBpSGUUCkp9,bjYQ#b)fxaS,U1<9YrTdtej8g=yH/m%_/4W=x*qBUnx%[sa?1A*;wo4A_v>2"2XXx25S2TYV1Z.;zVz]MXH0r_!"WW*%P":>n-$y$`lr]EN8%egLAy:wr/*g/YV4(1M?Ig(EfSr0IVD/QKS[.TsEu&_r^Xw"v`L:0bq-20w
ll
PblvmDDVl4Z{,yG@f`loNeahCdt.w,L2&Vz%_qaPyYa^SfgoGc3SFtVz,atz=d(h&|/pdzKdwV&^4>gVDo)dI&HIKjnLf?obfi&q4f3s-%:U.6uR[wh-"a9rj;:>1>tV+[Y%76>bp$.
M1C>LZ]`&tpjx3AneqcJ`i@]svu&.
yrlCM}jJp-@=N&Y@Oe]Z;qjcVlcg)+9RA?-7m[uLNqQvrWH|<l={U?!??:b<qDv,?8ZFRVYe?V#Ovj`-A)%>0Nf{b^KQC73xS*r^_U=IM~[=^UR%=,a)Ot/Dv~^N2LX%][jC)e"F;d-%V4ba(,$1O2W?C~%+#4Nl=fu
s2?6?nf=SS!NXRfrPidL?#Ol79f07.*03`1wE3f&eX@rqxMq9V1g(1-/0UqKtqp&@45c,!#8?rN(YAE3,M(A*N;{Tz:QEcsj@s"mab!w$M5h<+yT1;RW/=X_:a/zd<$J_o83X_GDcZ+d=HH7,SEd$EBGl/*/dlTdRw?bEF#YX7R=Wb07Y,x,YsS{wqN3tis/xHA|B]a+g7_Soak[V%h~[)yzm<WK3yw&__oZ7S[gw#.CWa/`R+o$z(kjHq3rnqRH)Z.~!mQ9[61Cg+):XrG50]&c]87@,gL`';case'pt':return'*`GKkbOpM,z0|Y!a@Wq3`>7hXk}x?;E4^SNS7uGQan+t$ZL._TzVK$(MG#BedO}$otq@_*gylS*5uQNL}%1;.lq1*4Jb9@!^@Y^xVHBy8YiPc:og:l-dWj?jD3L7GE:Ou(D`
?lwZyN,G8if~1u?OQ/W_[JT5Sg,8-F=}%oypZLaG<JRMF[Epo-4YXtn
V-
LnO*mBzUiD-q@8ibupqBFO*1aB:
tusJg=*yb^K46Xay<9,v@`m$JBejMeHnccjUs1@04]Eke=3U#Rw/RO[G59*3PR{w]gJcg%[2V3KVY50[H/tC+jIr|mq
lu|[xfzn=s`l:-sht_O3M^=E3E]HyI$0U`-#;&#b7iAwDiH^m7&<yEe-:9T*Ek@J})@Oi5V7^HqO/Sa5J[1:WkMyH.{=3[t&qD9xM^CSxCnBj0$x])Me=$2>qkBnSH;?G60etQ`b>U/C2;FsK+UO8P5[Yuz7bV)x(Rru?C,20c;9Us_@x[j_#d)wA"Ia]U{obeZH"eNXh&"wXgRG69)mxOJfsOP>nN:W;7)oM)Mq"#Wj1ajRP!;d*G4J
La4h,`vD0D!6NT;];1.Tb*k5Wv#GPzy1)*k&0<V
31Q:xl$Y74UL:
IDrNeK8-p7eUj!R-<KlB8N&4-f$m?Hs-M%/9O>[r={ihP(^zPry!n/
2
R67LEd478j/37.+H7
G+XD09"h9Bue3
*gA"
pbP2(rBs;K:rgc9);3y
eep_^~b~=7_bRMl=Y-LoI/3mgg/S$@F3Ip/ek2f~LfL[imeHGj)b?m=]XumXb@+xC[Y,v^/,Bqq[@O&
0`)r5`^-_AbfE]T7OE]_b}A-B_*pbCvZcNN2"7v=X"miHDM=0)3)(Qn9.}O`DR6%9lgg-96UI9u@3YmPlLSo"kuZv*>/k&Ljd__GFc+qw/B;bbi9P3g`.`*t4us
A*2aR7D7l#SLM>vF"Xfp/HHvp&=+dB`C
EyZ<aByc,ZG
{3~TakX3Y/fF1qr-LwxJpR"^j%LC<S8?NG@CuFt&ASwN]ZGKUm;b_xFpH]ZOpQ_x,5:BlhcH/S(VXjk@6hw%1;ZS-rL9Yo%MG"?Uu)Jf2/aB[0;4yK&t`vav#LbeEByS_Z@]9CJ@K<Ro4vIAi6+)HfUZDU(?d>ov~?+b`-uWC!N,SZMrW0Di$8P0"34it]xKuEP_:o#PsBr.|aRJ|>;#_^?W%QiC9KZ[LnQG-
]GN$L3IlHPs*|^f^s!>v48eT&2gpS0hDsf+k=&!]b?|dm=y+j"CAQdi5Cm_Eh8}j9JY!KivwP5rPOoF`bG>_
CsrD_dXBsOtpK:x#2Hr;Q=eJn59":<0Q_`5$wTJCvuV4$.yV(APAQfg6tqH@O}(x4<1P=-PtX^;Ri~8=<,1
a9Z/*7QEs"?r[Efrp9LIW<5_IWxi(mLPr,K$E02Eh#[|t%`s.$
KLvT16#5o1Tdq@TFht>P;/`eM7
P4ZL;FV<"u(@%g0&Osf*jkC}[HT*(XRN-|"y5LvU:pd"YfD>+P60Nci_9)"x?>fM!})p
}J|Zz"6tz%oI|<x%"jc={<L>0Q@#SnuJgYkD&fU]a-4kzLBD:*JY!c+`5>%OM0_eRroDRh/])3]O?7<kx(JRtN7Ncf"=Ljg,qZP,@pzv9k*VQ<b+ad(o~x:6cFhkdvkVDXZb`X/G_ZcBja<m$AOdYoB/<eJRY#6.IxD=aJIC5.dy~l].G<B(u7kY[RC"urXgUP=nhG(me-kDInZbTT#!NLHn"]6pO2oh:spwM-DUQ>Kv
8GXa`}xEZP32k@f]"N!#/eQ^iJN8G]3=1zQL>sP~G3F(
<J0jGjYZ1HN.E+*xm0.cWHv8.Zr:uJc%}/f,ch(CpX@^ME1iOw"30X,8gL@($9w31*Z]*#$"*#<pz9=`#BRF7K@o{fPeGi=UtWN]##JA%.W?p3D5r"mh?Prf,eFqkMb`s8m!W:cZeG+^RQ2DC8ANo3mXgE8U%J")y)w#`R]Gkqz[>Pl)^tx]0`E#HHn3}7KgR):S_l!848D+$5rM3j-,n#n
Kuo
:^Y^e
#=Xd!n[`EKA9D,jD5d5/H&Na#[|"DsA.r2Pk-x"PwkdV[x+dM$h=G0a:*aYa<6x5HR/.Cn:#^$KL6S{F|+<D%Tc"VEx?JHg-rRTuV`1c$<EWrS(
%2*HQo7n-#(=m"bs]nl*@foOn,OW;;rGBSr^}x_$8mOwl!%DgI6wQ?
`%xZ4wn
wkmc
#Dbdaep5vI}1PIpr(FI]x#n]NHD*NJaE=tw3yB]s`GsQlDe3Hg^G*IN=
lE/Y%VYfEkHg3Mc^DEqrUa,6jPojo3s),*@FlW;gD_;x:lDO&O8gs+<3)&$U
ZjAVaMh$.c0.|/3wWO/BdAc$sIMmHk}:W
sM59Cd;^1+
o$<f4PiW09`($Vb<`ZQ<Y*k&7Q2L6A_mbq77mQ[+%1KkQsB7$d,8GkQ:B--TTfwyugCr9<?JWg8kKRoba$Yb4Bf;C9gW2ss#x,4u%z9s
Pj8Mc9{+>dws-HEJK`-)}V}63xZp3750O(WOnvc.
J2C.l]BMFeHX%CC1<3<QPg4-/c4)P^I90c88Q2&%^;3#JQs&#X.3WbYe1Z[w!xRZYeoE(Yvzj2&2CK`-aMpow0pm!s%X!KE0m~Oj]N@aHuu-POBv`UA=>|*z0dT/S(eO<x:L.6H0P462S=v%GPXiZ$;7e=OM_g*H3-QSr7MwSt0!d;3MNCXdFOby!{m3T)&0C0VErcT~-U8ZN.(OX`-eYGP|xSGY4x61*wd6=6Wlt/O0]q1!m"rI$/<l6355a`h=``gRS%bNDP@VxeCP#X`f+|Bp&>DFQe7_?We&ZT;umSeU7d3
eP]@!2P[T
D+qoU,6:eIin8ae$ULs"tdEf;TC:wqIe%Z>zucg`[}SnRKXh@Fd_rF]|!|_>4s;Sl3Gt$Z&X?VQmfDF*j;5IT_6q$AP{ZEy"P5Sfj4dxTbgU,a&R#jUkG:OC&aC8a(v@Q"W!?zo3f`03F:f?><V$Mph,YmpmX^INOdM6qw=plGVA+0&ZN)J3>7Dkv:K~CMr4]VXJAl#cEJbS^IUaM3:vRw8m4O0~m_vS*$F!wL.xhOsZ
nda*d:%LBpC`*`#Hc4gUeiuG:MC&S*J)
2?%0p
$zhkdpTHffv(*rCY3ekdW}2xjXmR_Eh;2j]U;M6Doa#32s<x+w2viV[}Fy_LPp0}2kg"Fo(Y)jq<!}6GO|Wh1(vqsP3_<6AfFg%%K*IT0?I@5I=}2YLhD78GE&f7ruID$lV]"tjNs=X>3"h]v=(ZA{;1FmJ)O?4?rZ/lUTGZdw%ZGlx3_aHWWZJ>b..M$!b0*{*@<aW_G;9`5],S<dUn6gH>wGocB?E%$/:(t)1G)k9S.?Z$fq)]V1*h]z.#etEUUxDPFv>w<ZbNP1U1UnsnghAw3Z^>8[-`vGiJwbpKe>A.waBVYf>)K$1;:/2g.fkf[a[a.I%?Y7gC`"!Kw2HAg"7h>o/P;1C
U,7+:M1l-kAAsKG.IW8I];:)Er%=$vuHQLTtjWykr}jJ9waYVZo59x!bub^D9U"hQ+@d`l`o-eb,5FBD!?kp3ZZpvPge/B_Ea.HuA/+se?xvfwH[/fu<^s@nKH9d;Bs56jsU31w,=O%.:Rqi
i)<:Rl>FajBT=@@dn8s.YQ0RRg9(u4^`<eZA:Kk/-m{Lp=~H>F]Z_oME
;*<EF05Sam`Qjx#ne
JQ-G&w1IHMCKAVhg<br`N"h,SpQ
`Z#vG}QEs03^_o%
(AOQ4+`PKXsdjiSB:8%As1k:Q_8gsMo%[u
gyfdcoIR4rc/XNh]6_=Rm#PhV*+8_E`m`hV^G#ph*L.fge+u.RTiv@.icrVFPM:J!2O=nNn](4x
Po&=~:WWStktT&fJw.PVnsru4;bSAVOAQI~v`HQA7MQ&#;1%lj1,`Su3cRqe6SlSK2!d6"l;f]K<At)Cl,U7g$~iO
0@tfxpyvP<4C=HG#DG*SR8J7d`&=W3:WF61)y(vk>UL;O$U2O6hQi9EJ4L3^^CR/Zu1iDvGJr44f)9_>n(~Nt,jiLwIsccI`qxDH2S.bx
_7s%+M)?Z#9,Op~7.=YY?#gP~R,T|HyF1-:7n=?Ax/cvEk*_OQYjjGTnBsTG_BV,APVS34HHIG;SP1,l2P>et$nhs4M1:%5<68*gERn;h4?r~M]wib-Xt)AH^e8vypo&yL=27Gx+
Q[Q7/IA{NNv8^q[HXsD:36PalBSa<6N5^8ZYGDx0rY3f+gI7"Aqx)gNMVfa3#SniM9]g*WV
K),;brW:Fw>Zx{F;Yi)hJ?EnK)5#v8#J=~;@`kP}A[&~L{nemixLGxszP.VuQ>^)=Ia-T~$gwGuT7s4KpPA,8Y<7LOywl@';case'ro':return'!]^QE1=.72L?|HaN1uU;t@*rplW-lH48P^A&SvJcQQb><FM9uFk:v_RvdX`Lvx)O),by_7}(
k_y<_,mtVpb7JKV:C3Cyq;QnT*-je
3$<O,ePgqyg+)v?{t
9Fc<MV"qJ>G*h}.)3zu2!%QtKxDOvXN"p>r+`3hmc;6i=j&/A7!:^ZJWhBvo`uo$^`b-X(kB(Fs:8M&D-2%+w%
;1YF{TyT|i)a1Ce:)je3ZOj_=*{s-^luC>a,s]*A@n5x?<}`UTpg$GN!53qGmbp]#o1/{9K
X>D?`CPYc]0m_r_B
l8xb1&IA8J@MU|t2jcno)b.tNq:4o/-6-Q-4">34(pM<0//Il1wkS(O]"FIs5^WwBPwu_E"wBch~T[6B
{]@M`&
9Y?i5(x7t.<$W
nwf(30J94x2|sssvAfW#2P&
T"^dB:2&cA)V#32KK"Es!%R"jbSC(Iu)sJ/#J(=.K%e:wP#[VeqT_COEgZa{8}Q7Y{WT4>u<V#]NU6IPw{h5B:n(J~>pl^U@hjpPj):mN+Hq9sEW1~
vg[uw]YczX58esRTm-~O)cB0m;NE4d>Aw01[$$z)=]
I!OJQ70jW{J8xn0GBG=Xs(w.e{5Qs|I1]FCa"Qo}:D!N%"ajui*-SSE"+%4^Xpdwf!D/:/,.nF%
fV1xiP%8!]LoZdHEwH.)gCawkt)K=ZFg>+O/>Vc91UJ,T"C_I:4Z(/CqX~!@
-+uJ0TLo{W
-#2rLQGh(?o{FF-k&wC^Y}!vHIH0Jc9*R6)HindS#J
hp5QH(BI8LtbJTHL&C@@kvZm,nz](v&EK;lC|;io_QN/(2#J(wz#I68o7U|y<T*q[U05|#2cz.vL[`HbP$;b*OrB^%.Q>tSya>20"y"Nxw<jC/1C/*G&cav:jL}FAc/&@`I/4L}fA`hLmt8t_Y*wh<*hQnu<l2gfKY"moG.-o7+D!uj[:Xi*tgtflx_1j`8e8_rDenaYAy?3pU3xg?yO,`$76M>(sg%u#x$+G!6a*2fBBfa?B/DnTQez!2mv^7GmF]$w_gH#{_E)L"]:mAbOf3x^u*ybQe(/At7d>xOy/)YI{tdBjHY*xVY1^u=4e/qLs4+3,&7-P%36iUP<OOJLU&o!]C/mO>v<R@cG#weM>ox88mWU*m~=pQbGW_Y(7lA^-c_V-<<1HiIgzR$Ed*O"cyNpH-K,EZ7ynf~v7%zmI_00N7gmg!N.i:d3Z,Fc9yO?sj4+119*gWVcOC_aQEz
qbR[J?.y-CqT5Ybut%t>HnCYW-7:WZwLM+1j*WBb<X:NRv1$z3kWbQI#zI2q2+x,zy;3nL#8~lX`;n
pL1tb-@gY.E=^z5;#<KeoSf_[J*@RljBc3#zU^n=$=>c!>+|JE2^2/k|L(sA-6w:]#H2smyd&M^iPdH-Nf6[75k`41T%H,8KI.;Wnw_Ev)Q4?nDoHO*4s)czknvl]]3AVamFPv.y;wJz5<R=])0t1DWctO[e^%t544(Q7[hV@l/=lC4qEkbC!BAn9NnSvj`dOzN%>fZ`5eg>_YUgL4B[
OHXyr)jwa
O
"/}a1<TJ,p(/+
bK%L#SRCjxeO;IIptfj_3&u1$jl`YK{J8-XM
;o#5DSKIL=!NeFc;Mb_*u+ur>{c(_khGC+nY)c_YqdVd2^8ZW~Z*=z5}
2pRZRMCGx.*UGE_6|8x-w6LALYgf(SfuWbb,rx]&ma#Y7!!(a^m0@6I!wUS&NYZEFEr>!sd3^7AlP=3u38mL]9_H]vOieuFIm9!q+(-C?O$Llsh#WRm:JWIGf)?eEK6q561#-$&X5*(OYOh0S$;,>Vc6O4G=RguMn5(iZUluB_(g/rZjmZZ6;TDpn/cY085G3tX@n4R>Fk
PjLZvF#&@uGg^#@S8=YnSVM="F?A1tgQZ*%xlmAdWkArj+58yZ6RpHYV=nd{Joo=^PRs3>g^:y7x<=yK0t]DmS92]DOkBcc5#p2:x[,a8SR[jDD8MX7hgxO#rqpP"l.=uF5QV#e#w)"66o8c)jf
a?_OXg$P>npfDR!jQ7)ExhXSJ{_]<vlqpf&X(^!vMfTlsVgt0wPSFK;a;e=L
zZNht_zQ4a1=XU{Z}>:vT,4KR_sV3&8P.DIce;iM-N3KvFLf0JHuqxpAi$f2Qr7Ss`-iWG*e{;z
X$OJE:dUQ?m0w"/GziYYMj`vB>{hUo%x]
cvEgAW.PK52$)#FR,@^,;Yn&1&Ph8xu*1jPAq"Z#E2w1fOwU=N!
%4y:fj-z&2li)$],]iv@R`!MFP
Zb[`jr1KBh?6>#GPsX9bG*B*HP]1^0<.vW<b@t"y&<G3$uH@3y6=X-[yZ8#VgDg@@";
Sx3UZ
^*m
9Ti:h9%!fo"]pkR4;JVNd&7W"&!~YrD#7:WF-i@&5X"t
1e??
Y+DvYNR?DK)JY!2^&9>.J7#]DU)$WdRv0eaWuxWbE;?]2;_iA-K:oFsv11Oqtb-k]7Ci+~h4Z[jN.Po5SyW5p*#=e[0;^G$;^=+pt5R`q.FuH%a!i;*;Qyw+DqUOEWW434:;Rnd1p2jUG:ki^kKTO2f]r3/-;c4XgTpl8,-qPv"kM,Z#JylP_*;Fl1+cl9""2#jbKdJ<sX,u?p*}&<v5k+90R-l+Ch`Pm3S"QREuviS5d
ezG+QD8y7oC/Sf"nywE"0/<EZ8)VcLi;`-G=c{jt%`8TS;=5X5&O(M9EX`
E2(.Do$f<2tJ0crg}D:*E(T)[&?05iGAPB;KRQ3Ya;[!n7uj~_9S#P@>F9^(3`0j0G}:{#YI}a[,9`IIj/4*N8d.!07PF,=q0]mTcn79KK^k!C].5KJkV.&.>!;8YYP$WeR?T`EBuBm&`Pw"k3HPx!]#8+h$^X,N+>+h~9+LYMwkmGJXqWBnlR@iBLh#XrmV-F}JxAF8b2kV#-55_U%2R,z.2NTR]9.%fR%s8j8#JIsZZK7
oac.9;9?y8N*^@"XTd&jyN@$&,6P0,9?"k"22La89mPHi#/%8%t#N;8Wl@[T~N7j9Z2;^9fd}lU8oD/9uk)
2X|w[j]![boDYxL^9AH6&lLhZ$:a%dDltVC9T@Sw,pUlQa^=Tm(#Z4|mlusO?Q1+C5%
QN+#9$jNYrf&b1uSS%PP]h]
i6/6
Oz_d:.[A</5}WS)`;TWhngPk;VM(mEh-y``xUAkdJm_rinkH[H<X*l+uWiW]4UU@;lkCEwOnpjLYDEk0H&Gam9C-v8p"+s];.1aZ
$1YApR<LU0I4Sw8WEe`*w8~u<N8=&XmUOK3yfd&KAj/8eFA)BqX#s5os&7x_-D.d^W{]cMT%?+/4]hL&i>]>"QA(F=w+%[@0]"C>s_.B=<g5Xj4;aQlTj%o-JckTZ%;FKE;TTvXc)F!i:Ce3,7MMR*gV+`@i04a[Qjjbdc,AckFsT]Y2%V0doaOQz,nUR6Z7Ai9rbe{fxbi`(nJaPuo41/{X-9zjn7bZ35*HP#Oi@+?v;hz-..M>fRD3e7$h4E{VsU7aSCki4y6sX*?Ukt-Xqi[[@8]@6%N-N
k@Y/x%KIP,-h+_MKvm"u:[k"_I2V:nf.F[Eh7QXi#T))=mK
lrxqT!C9@fdL2!#U:JIki@iNLv(>{&9FiY>i2^ehSyX^|+dlE"d6q<8H7#O^fMP0Ffmy{>+9N=^!+_*V*fmqkZ;H{*dm04BWnTl!sqoBfFCN$<HjY7q[,t1mSOo:|2q=Xa[!z.0gAJ:9A?w6-HugN*J:aEH%XWjuk;L;4=0%$o42#shx6:+_<3?Z1(f@q:Vq*c:d6eVCKmxo5pIb
O@j(v`,="bF{Zv5_F/q+xB*H1E.gF!R^Lww$f0P5*P%_YlQ%dT(Ioka8o1Nh`j
$jW"j5_F4c5*nl4v9eia3_e
1t{4eb-t1Yf;?5iY2&v;`+bQ=fq&_[6xD&u,Ww`^{lKEhbaHNY#u4!%Vr
>7#En4.s!^i(v)9mqD%stIv-k3a1|J.pFkq8LBr%&594+i0<>OT*L?)g/^/pgJT(-[hEda$et1(*|Ke"j-o_cOe_IQp
SV$=30dQ69PYo5"T]L>L|^I#OC]:)S**Sy:X9bG9gQ~E"J,7>wM6(,gQv!!nJMiD6Sc&
5nB&I|C3GDRM#QkbC=GkA}YndR%$yW$qP$+?0n4mm9@j32P`Y
sgvcuC+LI!sjaZbWCDsKk&7I4M*<P[=a9!/~;xmjR1B;*6ryh)]J$J=5]Qs:ooXXn?)sf;73Wpou3_/RtcJ1UgJKR<)D[w;>[0TboqZ}N75,[PU5"GVJQ|J<R9G8U,]eOG)617
3*]6n*{L&Wgro,NiQF!Fm`+kl8N`b#T;FoLN7ty5l^RMW@vT7jMR%owE-vl
2EBH";B-Mnor:"=r37NMfW2Jjq258iSN6f7R5UG36J.qoq1,TRxTeV#OW$&dLk|12R$=VcU=MkUjrD^x-]0UoA"oGes>)"uLB
+5yhPWM>1Y?L))WJhe&(MNR:G,HYh7g.2F6[+6dF@R6p;&,UT)ck3l?sf%oQ28Z?_+m(!,bSbWmMU"a+OYu`:lW=/p8m&qCqdNSM5U*6?4,Hw2jv{i_sNE]H
wSj>+K%E17pY0n)1Uk,"J6)xEm%d!ffc[5A|Q.+,+[GpNWGewT4,43^TWDw;c3.smR`NAt3,%%[~
/)l4Y;Sh5#3aMe%XV:?Q4X$D!L%G,:.Iclq!e^QMpNagt8a<c]V4gx.=O@Wwsz)#%';case'ru':return'*h_VkaMG",{0l83M+C<=ALO3>`&12]CDzgtt8DJ!YKi>WGD-L%XPNlNLq=[O-5GNi`xg("Qq~2iu_U?rew;6TnIgQ3f"j!WW>w+`zL3kLBxtVb_ps04H)@2kD@&b-FiG`A8(2Gf@OxAhru:To`kx.]NKewhx6?C^aDsD`!&l8=3UM,6
U`N`a1mgOaviL?~40s-S"qIt1ye7~Zo3LnC9JqHgY,.JA>wEb3d[4,.JYi~6Gu~hrU&."NpU5Z8LVC,v^Z=dYKh_Ow@J)vwyfi?)a3@I>o5Dkuz$WlP?Z#L+za}]`tC?nTdM!f9:@4QS~WX*77`FT)5<@KLb1v.
ZV}[zEj&7iSSt[*vP5%Qit_(mJ(:IM%hilgxqe}!>XVX!g%p$rfih
f0Nwiqk]7Y.(Z>Kip0Zp]&Smz8KuIkFcdguI&U14f."H?<FTu1&>=[X7&c@L,5._#h!o/tJcoW^X2IAUj-ZyM6mf&?zqW:!8Ohr,z[$eFd`7<%=A-_73(,?70vW=`bVi%Lwb(r#pR;+PDaX5Q1&[TE#dW0cnoa"8Y7shbj-mj(K0SKHr5UJrvL7;s7*B{M6X]?2@M3p
)6rXKMrhE^/j#qn?7RG>Li1QM+:
[@Y^)wmMvx=o/XT(2M`9C2HRc

.d+FEg$qahhMaWKfX03OE_4KF^VZvZO,0YO}fOA;QxSW
;b2mRQ=]jg2@Q,&QC`]Ot^4Z*x2[pETbAmI9$!M9{5a6@O8tiVpa.(}nB;/]0erUD8;7Y8
R}mi[wx{h
LD`^
"bLEmt$5F&>6CE(q?$6]2UF+.`4`@N-*2@O;`+,B,5AUIn93I-.HN+xccA<4f_^vLU2avO"&$FY"t>SQDg$TB%47"-{aAZbWg%o`_+:TfJm@DR=mx.-<ok/UN)]qWP8#B.w1?<bJ+?OviYPJehklvWQTSWP,5?VvKA.":cm
_qf9$q.JfdOG@fnbOt"F}Ux+I_zIub<z!u;4-I*s>yWqHFT`IADtjcI>|%~&$p1!-23&Uu(WK:M[m#&_Zj!1PvH3$8SHiq.(YV69jw-=(l;V!NcRpZvt|EO((rJhOu>]XT6FO9J2`3C9}hNP8dQ+}yQ5WX:>i?E6Y*xl$+gf0bS$Dnrj/Xa?et|Krh""`8/^7#VVwSY)|m6N0&-Y0w_T-_,:EbMF_g
Q,NkL
0omE-BoDyZ[/#0mnlWv=WQ4;cuH8ET
U>Qs,K4#|b:W}FH3B478xM>r."s9Wc^prOz&/.jH^bI500Qy!d&-{qPdWx%2<$FVd8GCZRrV}-o=)F&W>QGE/%1:Rh>M<pw.&uO@Bn0<=/M?k0vrc_t$cjACdC6AI&zEGmZ(e>%Z<y|Z3S-gx:w<1wK;S.a<~_p?@5h
@=vN|=sgU6-2YRrdzQ0x<xgKIyf
]DS,qn80
;t6e5"vW+u+<Iv7.%F>Oae0ooz)RRh9KZ,7*a2!T&9`;_vTLN7y0ue(p>RF}REN,=]k$,6SMgK?}V:[>c,<XvXUkM3Y]fr3oY1X3mve,#:wuX>KsKCTa,`ujvmKw0Mcztg_VFfOW&d&(j,ddijO}Nr
6*bDui)x<Q|1zhAG:Jq:Xb9j`nEtc.GCd&I6?[qsP$ubN9=HUF29"H^6L3K2uf^ILEy;dCpYEhT6T[f3q6LjV_|3CbK)NQn0&#G5t<L,<1X<1;aD,_Ds"#mXMfV-X0mooH{4JC?#$iRGw_Pim_tx<9yM|j[n.i/9-W3dt<OpCbH5MbpGLH(%of[8<UfxTS_=0foX130D_T#T#C_%;SGP>0./O6wg#/+Iy@q^Q=XmA;vbnCqiqA~vVW/ZKHu8-pxe6s62NSIvCtO%2s0LT`f_P:FJUeg?v.r?uGdgDy/H}?nnB-/SWH`mbeVvt(X]BCM32?L(Ly<]:YC,GMC3
w0p>vX^*JQbk-!M|J@9H9{sT0
$oldFz@|oN$1)fMeAd[!im>(bzNw4RL0We41=M+REzQ(_aq1a$ku<iLgKd6VgG%<mdsmOVI6O[64IfRMjSE32w<TIl#,<S5:ZzU=GPy7]AI+]=5sW;0gMuC(,6)1Dmm@^sKUjLiQkGLl"BFt6@<0j3<Pm#yoDZkAXw1l@Z4":fUiKoZ<0d2KKrqUpS0zudY9xX0F9q::7-V`x8u>u"m#<N/E[+Q$Q~TXje9.Va=<@6D;flY|(|m0M<SD4"LDXUn./w2+0;4$=,.+56?>/|VTo`@Yj>HQ*Ag7K%[sT*r4lA;*wEqZV&2hYq5{m^8n057C5d+?hQ4I4$hsd)vYY3hMG.%>umE-CVt{6v>Mtq^>kh<8PPnBc.p86"7Yf6
!p)*jghf<Xm6wL<pB?g_vE3,#&Fgu^%
Z*OG*s<@w6!r*5Sp,Q6RR]yob%=Ziar*Y`&1{QFbbu[=MT)]1Pj>NF<S%2~wZ;m7Cod*pl=]n1qE_y0&xQL
ze7lqClEAeo^{-x=Z.Ub~eX*H+N,mu
W/P8&G@Uc**2V8v$GL["^.Q#B|*Q.+-9YzIM*5"79/^(i
/L-0@C6laN,L((c#OA>2NVg#DX$Vr">:4zh?3}dlN30R9{^pDa%Bwjv%=^vjF5wT@$sZulYn7A98;]yiOa*:QF8x:>g|G38{cRiCc^
)e4SOO7%Y-%
o9esHuB+fYyeT7>A#M#mL&0ny6l*X--(vFjlN4F,0Yr_YcRf*ny)Bd.:eg?`zUkYhXgu<_;pR[{J@+7X%Y&uf_ly&v^07Fq3;@GB0aF>;*K=Bg9C"rn
&830G>vrk,K!88e*/e0<^/i"
hPo]9BfPOURcCYN59zk@,G.;k}sR9WNegB@d.<B
M;lY!q0VUew591C[(eVxr^5t^,%B!]>_X/OZAxhK15Qig5qlT51o6M`kNp4`HQwWYTY&y_)l<dRMg"](wVU]@Ap8/ZO#89s~%PT;qAeFz"88boWU0=rV`ZvLyDO/4-B-i%%$R7Oy)Ra!lN_p@IOy5$:LYfTWLg*Lk{uBy*HrCRA1p"&($AFe+-[?nxZf%qLswp@oGaM+OP;t?@?;_!Q%gX,jA0B*OR@~VT`,6mO/HwH[(0)4G#4Ztxxopj=(Q",[>Mq6LfpWV1]
x::0h
]Z>8eT>
EUOe90WwImO-m_,Z[u+)RT)RH4`jWok!Ff%@y+P3MgGj?Z)=7$>l[_m3&eKU!{rUx16Dw
<v^qau3Ha(N`L_WI;fsm:=_INK1Yj^5
@R:m>7PQes1GJZv>ZRi11[5?+1#GypW,!Y]4I5A`g_ta"{"j
Cmei_CK>*2-9*kva>yLxUuDr*YrN0O1uus:Th4E
._uQG+vhfI^Nn,eRk?I4EZ7-&Ze
~m~RmOy&]XLSvoD@&5Zp68s*p33Re^{vXFL?;I>*$PqA?yB<I,K#nHqNij|CM9u_S/AK`0er{TJ/!rlCHm(M*dBZ5$k8R-guiwYj61lvD7YEw]H`
)rAy@kF.IYO29
K$JAD:qo0ZE-Hc?tlI4L0D5TpUhfq%rs5[xuhzMG(f6D6ic9`{)X2x#NL25Z9P$~tORZ/"*RK{WP;u-A.b&~1f$(ruLP@MM$Tzt#XxNx7Z`/wLOzX)%}RRRt97.#!VRZ8LMAu>,&+#!>oCj:>|b8xZY%dP2~b1&$O4*<Ud0D^ak#SpC{J?Wb,zQx1O+<6FnE:O7zk83K9Gi.ns$>bcWXK&a;^a%l5J>#%rgvA{>/o<+=iZPIH;tK3K@JYN6cFj3;i@<J<vN}5Ep+lXqyi"LZe;=_d6GO-FxOuB!c
haly]u9%d=!#=b0dE7$`WlF`4"p:
2|.=2M(]?;[7[{r^6_.}3m]9E;4|@z+)N]#-X3Up1|3m0&w2o5QJ6R%CZ,GF7vP0=oScOja"&(v{R}V{u*+N
R<#U4<HK"4Po?i-kt]-<uf:N[oit)3oI3OSomQ{VD[",]2XTLON)ZC3/^Th)17.lR-Qm%x@%#D,Slv(Feb,pJ:k:+2s%Apml!1s[8x*!.nd&9io1G.7n"u{Ibc-H<=gHW/EgugGPw7glI[.[|V1Nz2*2aD>2bHIn
/+VVRJ-=6[#4*{T7.M?j&2b3V30wB!Y%H]u1mo1lX:fxUXaiA}S8<$<_,e$OT4la^$".8^#;%,rl%M%wnM(]aL6OY/,&-1yq.,c+X*F8RmhD/!tXAOJ*[!k&W6In^XZ~2+D>Y(K%t3[`dJYtSs,LdTaoahD%`_k5EZ_!Bq[qlMrtp^_/fW)694;`k=IzEwvx/H"W464[9d;a$-u"WcX#8cNsk[Izp?cmH"AI0Dk<`K]`PMyuQ"]0bq(Qgw7|nOT-ABw;X.u~6y7I6~USnqH=^Jxp^#eE=^$$2_t.0dT6Ru;&JBf>d|F?7m=$+/Qg:])
fJ"BayFp!1(r#L
YdGSe%Sv2RVan&8efR^JGHC"7la9VFr4B5pI.fhc|mxr>(!Yb6+v_t2Wl6tPnUb8[j-G!O0Xx8p5)aMHbbXP>Cdt<cm1PI
ec=@<6Nc1i5!k}t;s%fz+HLQn<4dPElh%*,jgcnUPD;*<iF|v&!K3zw[,+.Of1.aQk@c"=[?9UAIN6`{>B"lV6VM3UN([]7y?(Jth)tm@/WLS63>9p3?)3B=P!6qogQV?ePqbZIu386Ve_Hz4!9u3"^rn+6LR>>Are!JF(AF.STtg`87K!6If<5718`(q=tm2=(=qv4Sfa",K:tisv3|^vREEcK/Iv@Pe+o@$O`m33c>x6IlwDFDTjSZeP;xXrAk!i)VKrMJRdM=7HtA#w
V$d,ZS%*pD4w:M[Fd+QJ&]E^3GSirX`fRGj#kZX;?.Q3.,^Yj*sxIA.(:r>#rWq(]4D)CiJ!Y
=:}CEj~kw:6mtf:TaWT"VJ0=8`L3CRAT`l6APogk,x9qP4/*XZ,GKH[w,4Mq$!x&l93-fBmHT++j`ok`[HCifo;tuiGEt2uD.QzrC!GH002-P4S@H>15(]pgffdN18Qw(Sh^SPg]bhS;`a4bidu?wbmMEiU$Au2vp_%5jUnE~b*6rJBWmx,!)U2v~L~Kxoj3/C<3}we-(BIB~AJHHJc5gCJ,e]#x-+l-v)kM/vkyAJ)L_E6oKJ7u(6F*.YTT}i}-?N`Yl-:#E`Xub].-uwo_RgWoEGO[b<&4a*fo:b?k4C>l<WN<=ReZ|TziktZI/@aZw"3
A2d>8*
&ZFmE6oPL[jfMw
kitu*CiUz[t"|.QBxmy0W.-NcINHJSC7v9}i
=2_!*al3-g!Nk#FL]O+-2P:
#T*_AC8KRql6W06Vo`e6h#6ya>R]qhTW>]Rv`j!b)[;w_;4EuNu*u{,.
JL#
RNgsJu*w
JBgn+7P$h/v|5|`21Gqgv6.Q_j`i*@#j@2jhEK_T#AIY1[x>w5j:r0J=yvY)b*@J^QVI,U8x7mkCgVlS/tdl#d]/U@KtOEl/D<"Z#1f~yB^Qt0Yv^QUP>0jxxe
ke%.irO[;ViDAXkmcI}wLN.?8FO9oM|)?Tc,!HlZw8]B.Q7C#P1;>L,!?O7N;R1k=17BdI"Y
r0y[smW}(>Md6Ehl[25YI@tgA(9JD4_L]!E@kYa=-5#4w"7G]|KT;oSmZI#u@+&q:*?@t`c$)wM~"#dio(xdwA';case'sk':return'&]^FD7rp-,z?z-1o?P5,+<{o.s;CT6cELn9aNTZ$~Sn!5UoNj/3>7yw.%pPrN*BIjI@d72`-QV=<!
kJL0E.Cda%Favlzb9?Cw6;vz)jILriDXF_8$BZtE^Dg++dBDjeRjIIkX-]irgtLt;u?:NO=!zx;r@%-mF;_3.MLK[2}FJ38-n1w?OjjUhZ)n^k73>E&DyAx5QD-9+b!`>LIIPy.s..r6IxGh&czp;;Yb#YLIVECZ%b~1n/v?sJA9yW!AH"t4,`nOg7GLuf@yqf7E.R,T.T>k|[[kjrqqguyT(f8@q?PSpS;wK@>:}F0R^[tc&R6[Dr7lm`cHeQnQGvP(<[,[pW%+(ckZ8DK[`,NW$h,:5I5VJHp@="Xvslhm|+.opjxXNXl!}nw/uq]eM<tZ&Za5A6tx}m-`i<h-_SJBXANY/O4nD6}T?D):TI5Y<-x/jsa)|L@*=/G@7x1"LEsTD;
A
sK-Y!`>pFOZB)@aq5+;&4Z
.`7?P]/=[,XZ>UzO{
-7B=VGKu2H|.%ArkG-8caH,v%_G#P;2(Ipt
9::fLcL)fO-pyrUmtDXT_:cYwV6/FF$`X9DVvad4
S^
o<*VdWewF3<$N5i@(H&KI1u0T<p?opAgD
F,(`-7MSy8%F(Il[k"DUj;m,+u1jM".?Hu2hu9D#CZd(x27i0d9TIQ_[^MGb
ohqv3>LD9zt,f
pqWc9(N=g[dCz)O%<*vVlZQK]i.vh?ar5cBTDnavm+s0p4t0YmO,R>bLrC:-@8_$Z
?f
axyHgUX,09xt{r>%4.rI&lQp4d|;ndV^~r8F8GyR.@NgmMLy?giLRs0HNjDNKog!I+xe%E5E
*{lDB^=voVDcLKANa0.BLXVFP-[0aD#q7KnQ+-UpX`p@cWi&@SS#POSqmG3V$41{-vl$GK#(h@Wo"@C&DqY`7l!1d4w*Kpf$j*v$wo3}*tcj-v+($hjAda%^Gr8b311bD-Be%SR_w%qJ#$-MjO[3+(US622cw,oiIyM,i]D%d[tll]!qpzO`84>EkB$khrfMYUsW)ki8ILQ+F73m0O8^XE,9Ym(}90&#fpJ@g1_t0]&*xwi+u
akob1<g#M76ID_x)i4OKK:@vF)VK=c)u;k4M^8j`F|#K`n)AQ(Dn
E>/>e
y=0agjnavw8+,OWfo,,rq#uW?DacpOMp1P-k$K^RUr,hkFz1_ofD*4JhC_)&jg)k8;6RKhWQZ0ZItd"6|ODP-tYB_[YBT7acLc=4z&i4(b$bW_i0)+a>!@SH5.)XFuz&BK2*[BeLVlF:RwEqI1MviLF*.OOn}X3x3ynabdrf[pZ?z"ip7P%P&oy%N]Cz$hQK(5V)a4uVM:=h,9t`_7*r@T[aa<^24[<T?QO3oUottsn/jX7:n2.2tuAQ@_|a,qH.z:(iwr!I[
tN0`XbCWZS0L:T#fsJs7nI3*{WegU6k;C4[BHvYoO)5vRyjZJitxw@z5w#f2<e*A1u*r6y5oAuW`cAVmME7R5I}*,ck$4Z#]5$~q<k#/=Q<ZU
yGF(Id=*O*:`x)5^%p+r9-%wMkxotxF%<<Y[X^vTvy
=)l!X5t]gxU4N<Aq6Q)YxfS,i.<CEBiN8([OZb4Adpy0#4Kp?o-`r[xb0#*H/cXdBK($@4%|B3>N-S0T,K9IlW(4.Z*cw]9]^8Jc2IN"f=`=?`wpZ{oU-mA;"{[YYNM#JDZKfW0_Lo-!65$E;m=1J,;k3$n3><giG:0Ck}Mpbjl0DqII]Obc-[g]D^N1d=3qt)xJcp!&mT7p[MQmLe1H-(bkIHyIon>eC>q[aGrME8S=:;$?RE
4wGozu<.iHi@XvRrjO.qhQ"4sP,Zy>:_r&q>+9q"LA[qo(%=5ms^`"b8sh
06d_DW#}P]s<z%:b_ZdebM.E.ogDc`AWWMfUeDx.U.cIH]KtjM4[&.dD7S8n+?58vm#eX<EI-E6P3uWK@F.1,h!<@q/}:BGHZBfAj@/_2+t(RvFZ;lAd?U6"EUxIEqkUke?2FouF=?]H?RV5*z*WUbnZ>~)hm9Ymnr*W>fO*+NBa#ak17TM93;5LhYN8MPk/U>MhkcO1T<%G^RSi,=JmFJ^u(Q5?h8q>N6EPKfO3ftww`2<86cq?GgCDvrMQwOb>kCG#drsw7NbHu|y^jL.jffSNn*W80`hsS3?/A[/f0ndH@7k|0WIph}J4bB2TZDwzdV%>!3"I@Pi,N-n2O+8^B:(QEj0P4djuNObh[-K3ME^vhq1Uv/Zge*h.-8QpbbFRww[!s$z%Pbo!biVUS}/%@.A&W&P|2BF;<6KOnDg[Ey``e|?Y-LxoIx#}n68O.<@b=jP=CM^t59DPWO_`Hx>@QOcfmDe[BZF~
em?X2oPCBgJZ<3^3A@P@OrAuU8GR)V1%Jd$DVefLi1a7HpkSuS}_X>b73/XO`BgJuh3.Tmnl+f,iHSB[A;q&K^dN.(=%A)FLF4`T-K6Ra4QMV;B/OU`C9@
DP8-nA8>DA[|%6C0$XcbM;Q<fiYJe.s=j{OPlDOC45c&JRx
(pA,"nC
(sV+@-rA:)>LcW*Y*)-ex,Fv5s=T3ZrBiHEoEv5M$p
bBV6p=_%.o:cI`%P{s/kN5wO<l`RhvQd;o}^W-="P6Z9M7~Y%>|5S/7c*I{#!]auGqeF5ODc-16ge&gH0ghlZTL]%_|7fm_+8.//CY}ls2|T$b!wv,Uf_Z~dQww2h["t}lY_2+7sCH5]"@EUgJQ]2QF?2L#N^v88+ji.
(pqVC|_^27cmN+cBwFp0&UEE#qGK)g@3K=!NK[3w$V?[n_$p.[)*78^,2-2Kw3IvFB[qn/Qa-?&g`p(K^i%!0-[%M&fF=r(4l6fO#RLT99)Iq!?k3=A]o~<k&ap2_"swHxuX_
SJm7dFMNdv:0Vvx7,d(.@-`cM"sJa{dvAYX1M{bt(/6=oWIPDo8nLru(<
-&JF2}3ZEJBVb9Uf;#p/XjD^Er!,<%al%WG5gg0~5#p-vGqJ5C/LG2:%kFy_saF&I`3WK;=,imr4..g;i*J9YYXf3Wr(arLmi"SgiP<Z,c$5"YY5]<14p#<,V2l^)%J=Bb3aXdej32wb>PBZdm,hI_90M]67%o#@nE3zC{#r4"logGP-/n
A[oawb"1w1M,/1$W/t,:d&+y-/Dw4l1naC#6i[@0MI3yA
bLuEv,ORmCuE_V>2oH_#aB"NUpSM
vpZH0md2x$8~y^8^4nl~jQk6=+m
*;5.N/[QPuW$MV@<`*?9O(qVYo,)3jLLLMPXbZciBZFc9nYB@%Cd8=hRO_?[E}xncvyq^"btIY5~%dQk/B36Jmh*%*GS<Qv^,zij
<.3mIK6=|C:j]i_6UBW7FH.7"0T>v5.^c7"UvvsDMCUx|<JrJw
RWW+3V^E7DfZN*I7u>bG9P5$>PaNr5;%6.V59Q$o@>1N6Bf^Gb[AtESNnnG62VLhmO^qWu!z1&v@*%]`;qlci(d3db00@d=,;C>ZrU
YeQ0jL([0bw-{!4f.t67M#h<S2z6RdWSdmIGRatpi*L2Y[@LxPVE^%M>PED2E#MsF:YUpE"YoMQf;-{j5E:;g;A(C`%&glYjU-dg+<,z"%aI,5~<tuaH[I{eN#NbD8^k/ntOf.G^uaMfO%:y7iFuUmZsOK<`onEoLVb8SbR-uh1)>]_;~[)7;2OIzxVoCj(]KC=bU<d]_:-N>RQck[CCQqR"0HpDHx0BZf^R>x8Y__/^63+C3I"g6v1S!RXcqNU15m-][ecEG+Ae{=>A{Xb!rUE4WC3=kih5xsatWt9kk6o?JpD=89RhiN:e4iuH"<%>ai{*jP)GNF`WX52XQawNE&E+"o9M{-v5F#!9[),Q2,2rN-_[tJ3h9.NATx-u79c[[7GU)pup^R~^QB*PUEu*07=*fY_@)IQ.)t:<>2G-SH*k=KOLDb*;:Y#[P3,WklyYDwOkBZAZ;H`5MCJU_^!,NUb^!2OXw#~Q4jTIGU:WSn`)QaZnOJ&a[Jup9DO^(kR/YX93?3I@FMcc{TJ:H7gNogj$A5>6!:7TI`6Vp9A[MbV52H?CpZimO91AF/5/<deE_tY>XL9rK">Gp>[EO9ef,"3VRV<Pv!:S24JJd>Tmb83[k53(xOSM;m_9#B
B#U=Z+keH3x[<R/Xx*q_Vo%>X|(!6B.8I_7pmh4/f"pBB4<lI"Q,%42I6Ai#O<Yp5Us!mxG>Vox
QqT8Ew-Zr{r8)N(7S-m9SE*#=uR.qzo,Qfu[N
5rX%sjXIuH`JFg^)-&93gA&(__CRX.tyCly,63_w<rjS:&&Kg.(qIZmN"j6E>[]NFsp2JYi/&z?/nw;nKS5aDqF:,!e$*m9xt`g$eVGb)l[+6K$.xoNp
S=rjw=,:AZ0XU9;vFVYaopa=^G0wMM-&5wDV**S3&3))zuOW:?UjYVnQHvp_!UKkfguZs&Hu7994G+HAn9:DrSu^$Wsb*:~>{.IM}%t.(+.$Oah;~ru;u27@kJT!Gp4gb.oQGZQLB!V&"Z
g8B[Uh?]o<2IVxWR1:)/j25}@Eb-R;.>^5.14:+xf*htpi&Y?b5ga,g"pB8g$x4n6+u.5^H$F;f%lLg"aFkye/c7e#CA78L~kL-p=P^+N1QjCPy-6C5T
N10!pWX#,iN"/^e6GY^G5Zi]S1HP=i&#]$.EMj[I~!yuJ6XPDcy3xVrAbI2PZUn*TX"`oeH`5I1^Agl%_c1)xWKmB^?l"sRaYiP^Q]V@XXNj{UaZUqd1{5NfB*rU4effjLM(k_xS#)"04=2GM/9HXdG"uej=edeaUV!3"UqbdgnZiz&**';case'sl':return')ZuKrh&A@*60q_un3-J59x62}acpS)r_b@,t/U{E~"FSXR<1}tp%^L@7iMSQn)lrixCD0#%g#[*`ee9NFid$:s2vVHQxZKRXS@cUiJN5HJ.SzqRCxu7Q]PZgbw@sfFAm6YNIFaVev)(Q#ERELDdW/,Y5+RbpTF%f@>v_T[T^5X|Uum4Pu3CFj3h=:3Sgh-)^!9Gpx$b#
wgx0IHh*q$)fy^1
TXi&pr>L_XURX*s[`oMhp&^BA]Mt>cYsnBR`WYJL)IFQKjvjoEoVC^+^o2i@(!plGF/9[@wp:Sj_%I.U;,gW@hd<IFJ7FY-Ghs)]P>ZVSp
8P4Gk(mkn%ct3_O43#"iilec/A,kngNLkpWd[D9K(=-X%"VgS(=S4IbT.C<@Iq(%[aD_O.!c!0VCuV3*P3#kH0q:|:m_2a)nXOhhkbxE
lqtl/cKlbBpGUa8m3R(A/BrQX=*Bk7R9MK4aaJ;_U5fjW")g^P.IFuGrLU:$UuP9[+[%9(H9U0l7r|d>&:-f$Y?Y!b3A2&6@*poDj%bI/}+]$1(-v{T"OKK[&;
SoQmi"lZ@CFjNut-VT,^GuT$0r~9?>2kKG|-^55-bdV>prgdEv:Dlx{/As,JTYT)yi`,0qdw_w-b~U|lR_52uhlg0b>?
WbG@H/(i<U,rDfpga^GIKJ+jI23Q!w*WAN9w8gL)E0u4lj1oDB`lWncUjOa~xdM93XyYR@_B0LM%=N+<Q?0%kWo},p^)=9MyiXt%5_U:GgGZbzo
>+8XhR.Li$=1)31@$H0osET=AG0M1E2obx?AKF[?xK,68)4A%/oW/u)|i*kBL;d~S%4O6*p#09XBI?#Dl{R0X*?VqUvQ%CdflHv=2gB?&nu!^0s]JA$*j[wvjTnQE!GzOEC_+TT%mXQ,]T)S3T176qBiqhw?h6mE^+lg5)d+JLOm(v11k7Wd_$!9]HTIJ*$GV[o6T-5ZKfdui#bW]Nbqe_B"3H1
qC=dP~LZ./iDX%^V?/+MyDLL%g^nxWJMPr
<^o6$VeRY<2=?/%C8!)H"QR0`qKKc*IqnCmQIuKQFr)tEi?L%4m`f1,CW&e=_x;xK:/3uL=onWHMx-pm0O6l]94CG>,J7bZjBlbW+-SiwGe[ZC*Gg`=UV*1Ht(/n>.RG{T[o)R!)v;FUr2O#}0(_qU.QOB0TWHxZ5YX%UCEyl=rUkg$?Z5$mm;[sWu$J(j4i/h]qUt@eohor}P*LrO<PY_hr;Eh1;qH%
KrHaU"`y0VCw55
$kVO@VW[BK"HHi0=ev*?@lF]+2-a-3FJfmAw93NLoYzMd[%?Rn3s,B!.a33ITZat*PXHOJF5gt4l)-q?)hag#p0xo,:+Y>;w)`b,nO"?7WvO~
-"1<jv4-UtJc05"Q_re$/9Sfr)ev^%|!XO%!">8YQdwz#)>xJnlx=nYX8HGNJ<}uqbWiWbz88nt`]NLCfSI=v&^c+d,3/hm^Y<X`w$ybFUg?F^B36VIghI`_U=_g:N0_hL!E0KRLt2[5DMKG[d=5Gd
:M,:.ogbg&./#<0$ppGf/:xw^rWe@M
c[7frOu>I:.t6oBX$uil#Y~jkv[2ZRxQ"noGBf;4cvXehV6>"lIBDe?0tDLcd_b?.og3H%v4[$.>ym*uH;3?v9;>L>NexQlXATs2Fd/.[p6yL<&9BKP4VhP?^GS^x3)jx3#5|$%f=_Ev:3;42Erm]uX<-KtiAu&vQeJ1#yRyYA9RG!lld9~5]Un]5j4BOycW6oHRCr$$z7r7BSz)9[t7|u`9IoP/*;><h!nl4,RJ&JEPSFQhFVE6yhaD.6Rr)_^O?jV:G3{TG/yD3bZ.1k/Ay
lSRYp4h,
f/Z/vA<@H^4|%dyJ-C/9YG?Ce>!)"<l5y)d?YFuAs;u-k~?q90u).z$;`fPm/G$pCf`>+uyh.LZ_wUWnou6GcJar_hv29z=HV]%8;&4PY4:zE06f`F,YAOq(Z<"smHFBSbo{m6KgTMC;TDgQ=zxD
O!v+*UsR9/yPHPt1C(`qD<]YnLd^R9_mAL*Mhu]M7MD
eO>ZAP
5^f6"+&rZFDiiDUm[iX/ugz$6=VMLyQ@0qC43S-.w%B],1/IbQv6`r>PiaA#P?k6o38gVY6I;NgWX4^w(eG&E3h.BB=lNu(v^WgG6{8cP=x>N6B>T*6oc@/%O*ZFj;NRd/AcZ,.%?]Y2uYw5.iea9O([ur/}1StD$GH|&RLPm@wboku?r#KAWeX[dv$w]j."=#Kj!P^e?2HZ96CJRAjwY|*b,6CHMlKo*;?XZ+?XdBcR!=n?XS/_(_7bBfR4LodsfAuXMJ;qHp$-1J;u?GIs:Sz(9Xq=;3ZY@<HTtmYW0(nW*8u#n&?t<*TaPvi-5ItEFx^vH7UJ4~stL3=H9p[7YbaPP;_"E>=VOpubHAc1vcJ%q38>]d:F7~6hVr^[]~dm#Vl3f6`Y><^dQp8EdfIx%x=nQU+.TlM(<zW**H*4T%K?N3*#AeI]11r=0Yr-9J2cyf#u#|]z?x$wVvW+=oYl#!@?4!l&X
]Yo8[&G18fnU5k:s9,h!H&9+7b>a*`u9`,SoZjOTe>9$]l+M`Q%^*0lgXok,L>)+SiS>Ga13C-aF6Rp(3nc
!0`q_/eOQ2AI[0s-q*[r0Z%0oh:k%NSrgqI!g?&U3b*(&f(X02-OgWb<y:6)(&pR#H_YsA-|J$%Gr=;5+q9&xjX:0,rwZL>9M5:CLDX5N)R44pe5X$;CBpv;hTUg8+@8Q8N/NseU;WZE^s%%vxD$h!umGDju:aE,`NptRZUqjPmh6"];]j$9XjZdIm+-uIr@:;e!Bxru[FV8A&$^LpOXmC,%6)^6op-^$1weN0!+`NF!TVs0%840aPizRFga]V!3:-/HN
V`%97Ag.P;HV5v@Y"Zg9iBwz65[g%N>MZ"yu4g[%VO[Tven3o#I23Bc)mzMvUby28~&AM|)!Bd[U6..`u+Qxs]fS+x?3v?`1g|<rXC3}Z$EIAn6PD8jcoy&FxLZ81CuR<xwW2?bkf%%}wh911SH4t4gr@O,/k5CM$?WBor%RIbQOqZLd$MP~Op/$^XNR>0::_nq[D8o<e(lRRN!i2oeU+8Z}=a*+h75|i{.PYD]1#FLN1T
vYVG<YqZ3lb21OLnp$P]lE@0y4
_~g.1cDQdn#]y8dcOEPSlt.*j,iyXMlg(:WIw?94/W/@<%J&-@8Qix-Omm=F,u:=0~T[6"F)kApYQ!6bo>)~1]"bay.;[Yoxfo*-wRGSC;G4&I2C(N5/x3Dc/T5
.kGbjvr36Wf4@hm,x~KzXAjAgjrF;bq0.gw2VmFxS9`N0unh`$D4CUvh3Y*9+{mMU-<i^S**81Q(_G(9j0ek%uM+
a2>TPcVsXAPG)>`qP<$`xfdc2E&VM$^a0*__gR>kchF)v>e+M)i?]a3i
GumG(gyfp;B0Ma_f/gx.vZD1/^S)G-jBqh21,ECn)?n~%jE=QV1:u+.?Fn(pV4m/l%BN8Sb/U2ia04O]!yHpq~>cVhyoG_?$"P(w
5`^g./[6uAqOGVXk*oD]9.}s~b),O]frxk
WswFQ/>X5/Qp*]B^6aJJQG#"%warF+)#oBcQ-ak"<xAy2Uqh<!.|g]x6EU&}29F<=39{90br%q`gBhNI[Lw:P)ENf;g}gKwn5<bykE2l:B9zQ"w#!ruDR=_vHwE"+K
>9HlMcCC*
;D9imK}A`^,/GJeip8{FjDwl:t)EB;hOQ
.HJ`OQiw?jxCO3jc8bA%E#*1sAPqjC;_cI]wQBmN-eU$>
yvc=M[ofks3ezojisanVT!YcA=;kgse<16GI_IK7MC(wd#E7=X]JQh<V0A^_LF2i!%4#6O`K;m<gIq%e13oQR+V`8QtW8p@ecI#IOchdtB=-XLBD/@@WObU];;p/>`t*1WSq}aq@im_5WQ;)t]P:A&IRM+|S<q17N$vl:G(FPsRJIu+x%iDV>?TYk+C"~w4`6)LX;#a)`i2eMtxI,)!Dvk&Ofj3&Z=6>q:Twee&b?pYE+n(S/Bq(RXN!s-ViLpo(VgmGaqyMrwTOW=z_FD3FmZ01MLSA*gJRwy$xVIqS.k!SA"Er-DJLyUADTIp-ueHDZA8Vb=0jCY~kUnm?Y3U/AK|.^emEKKk>G8)w:#%bU$p%S<4x6H<Li62n4P2C,H=4p*_X;.+`(9ibdh|qXUR*Cz$.u%,Xfk1:-oX/vuHQHHe7>c$Qp:u.
P1=%x@KW>g:
7EXBv8>eYnpp$+M]=SoRc"(|>_+1#YX=[R.$C.jZ;^:kEYqD]qPA0c=]97iYQ(5]wcAtbA%(K%"dL<O4CtR(14,cu&3}3Q;_/F;1]v0jWt`bG|2|g+H,iD)f*fXD-M/`6+cpC/k|</kYu[
yT.
[ft:EVBtkPs!wU@%5&3=;K3N^dzGb.W0$%=NG*-G3Jb`ZIGBg-G=V[>xd_es4dr,i+rpM?pQLI<amQv.)39Z0s:1MEjXMNc#(^^/g*~8jT%O]g/g9L}KXfWPz]*=agE5!u!M{NgXQeW
-mH2I,{:YAzq(%_(-8TXc7wHT5?WlBpJBAIeq"z
#U"F
oLqU_xyw6^';case'sr':return'.c0Vt:wmT*60qTr<EW}Br7?pd6Np~fM.^;_^5BmK"*f
IDs-~yXDu+t6PU7p>KF@Hnk,#c:^V":T#BpP-T8m2Y!R_l[n1qNw8EiK{x,;S,0+
LmA@5^DCT?gpiMtfB!WB7oVK+O;NWveDIws$PJao3l:_veX7Tyu2eAwN;q5],vsK#5mDIz!<O}11lOA"6A0>:Cb2745}A&,YV?*vPicJ0h%W1z!)ANe)&.rx3}$WaquR/G6sxi4Eos+:
nKbdcvuic%0VS^[N[&9Z3y8%Tl(oX!;wNPG1u*`b:OdbHCHG4?:y3wkq"7?L)jtc]
XZ>yNX7I|FQH:/vT*$NXum8U!B-B8)q=Y[|f?GO3MGK-Umn`4tUTKb}6iJ;HCka&G,<KUgYPZ*GsOnIJ?9R:+LR_k:1Q(Bt3xm"9L-guykS-lqR0Ge4
VpL#JRnXIo;O;Ie<v2/^r^3h=k`)[LGbagiUMf9`xVURnb]-Fq5PXD~pi8
2_=a2h0N%qZ`-^:TX/DxsO>h++)?0LHM^[PfnU9L_2Beb`G0a7pw85
*v(j]bVDoCW8^d)pjToP$C4+}/q+s94iLy&XV4+=2Wd/DL#
7
Ib;Rc-`MOA>VU7K
x^{?q[S7I2BqAyq`.6_qYbcVA9E;Io++6@uHuPt
3NF
dtjLWDbg:>m3nAfM^.xqi;njbg@O3Hd;fnt2C&6b`E.(PQn`=PiMT.)I>&uJDy[72viAap;byC{DR1BV|;n&xX5i:!{-
h/O^a)"=Myfn<ULQ<~svLpIFEL/Q`q9wF_8j,6mm5-2F-359)CM%u
jlcRwD>rr-g4"@pzamkCMSdUD4%c[rZY>OiLD(,e.TL?+8sJCw.JRtWM9.3UEU>[3~%-]FEfwBNcZ
*"g{-]osu0w/@_Z-Xj+ByJ:c-T<{4FXEo#95>k!If+SYwdT0CMln/dDi<Ak6l1D.>iYiH3Yx:4kldjRzm?&s1sJ">h,,t.Mx]OcFc%!sr_4&oE2o%bDp25_Hj$0FVl)?
TAO`8-#-AhIfk#zo3,Fd?Y)HYB<YtJc/onpG}URMYu2Zy5[f#a4f[l1DAr}G##}k+M"(^dEkc0YM6^]6DEJ7_`a*GA6tt.]0_O2HIp%;Pytxhk=fNh1ER`%C8hj5RAPm]mHL4B/<aoksnLlu!+dm3p?QRH16QU=$O-~&N-C&LfFP5!dI"w^^Or+?SGj3}n`)Ds#pyIp+0b6LJUiZC.P^4aTJY/:q-k/l*!Y.xLQ[zHtWx#KK6mQ3Zau?DNI=U/(
H0XD#utM:;&cIBX-/B)Qh9YJn1L0LSzw{vBpCbFr<IqAEllPyW1al2G=7@<aL@4Az;NUu(lj-g
?A;4qvOxqZD[MXUY:(_!w3Q0_
kN*hX.fz_UjiMc)o@|.*#+J$nJ%/9]eWq_.|(.;HNdiaVoT}e=nN!e9`WHR20d-OR<&69^NH&;yqh-%i,r/2l|:X+J5v8dc4wxYq)m%7fN(k21%u4[PEKGx&m@ZVFAd$D0S#4!Ue.z/rw0MEh}BYmI7k0oP4xV6A8JHeb{*BkUh!cVBN]:394h?)Xs4##?W{0uIA6t4"6jS#x)+4Pc-MN`b26cEm4|ZLER4mRb=2O5MIi86M0ITqH5d""eb~!faJKEr5n511pC4p8&hqv?9XU(3A,s.a5+0m6(R#TeS|%{crpaO,BsrYYd2[i|IgvNk*A:,7c^^uc}P?/S&UggBi#
mB5|4jt%h:V[USL^0+^1IxqeJ9kq-~dj
A+!lrIEi51nb5jx`?dFS{!&@DHfL`bxmHT1!8#Hed1JNZT<!#NIEZ=lykaOJTHZDe+FkwmGO$]#6pDeME9U*[x/m)Vw$hYPcus95FK(QUqK*jM{;g2a^o6b9^R6.k#BAuM5f:mOsk5anMqtIZ5h1D"FGN9wvO#"sda/)vi,08s7"iHh&G3Wdf%<l)L~kz&IjAZz?qu;ewhpgW0h=0&+H^hNR^Asm?fvttJz?Qd92i/4[y4Z^w3?Z-ik/x`<07gxj0beo:H_IIm:NXd-qyhxH$&!/J6EcrHW6#13Htyo`F3xB60`t6N@S_3VpCZaE,HQ],y;`Z`;CR3H@iIiE[R6I&@~DUhs)dSiC`LYIz&PHlf+pMn[b9][2!aSc`FnI>G~)d,fWA$?W?Mu#3^^xHJ3pQE9q*bZ;!VJltS4p8DEjN@ILh%[9wD^0J&Zy*9S`ds
h@KYy,]s1:Yd*9aOcU);-YYd3<H]D]H~.`Hcfa1xsY,`?*lYP`oqWeSU>fUyrs-N9-CY?6p,a%FWEbu6EUmqWb>e1H>`Z)0b_&gg)x7,;2]%oj&Hx^kQtz_l*#-K#n,LS.s(<AUgg0ZhEq8@LD]H#vsVT*51C8,82d8{0;NwScRC4RW*D4`WtXq+HQai@=bTPC9O<_$"w,mg4wD3h[7wt2wh?}]hH9f9mPEK"+VM.5D>G2&.=Cw3R?9tO5qMNH+i,7h&iVF#"b!0#e/=3M5zhfsV(Cw&d&-5<F]Q-JG0=",EQAtuVA@ocAoo;+hd*/j+<+Zz5d-vicv)k`KAK6bNI%A6I8#,DFV<"/lW(95{bzta/Zw"F>4l%LK).f82l7f(+Ad:ga(!h]cmmEqsS|lcx~`}s$rF"s%$kkL0VG,8.>[F`},}$`."MS<}o>NtwJ3:;b$2)B&NuWeIy_;e75
u,5mYVuJC&8PJoQ@u>Eh!c`5)0exDNP
a6<11UZ6n.A.f9Um}PEWmn{*QlWiOtD@D?y#UDI=lt|4w<0RsLKUCmQJDE_nb>&_Y39;Ec1>3P"A3cq3H:D*}H4ES2p9#`X2K5qR=ROZAc9?yH]sAyF+Nx"*TD64U:qCke7i9GJ3fV)iw:@(
fiJ4HL#c0G`5M%f&ldaBAoK?.7ZBsW)X
l@Ki#+aJNS$ob)qU8S`6?=ca<-vG^Ss33AnGeZ^dll1LcG?1ZLk>D2qa/AOQrkPI]/V@w]|Ap9rWZ8%blUnNo0)cg#ept2nA{^oW$,eA7WiZMbH>(2woQUvRZD4a<<!8M6j1TrwRS%9"=J}b#ZwVlJV.<><kb+)4Rrb&qceloBR8
QQRh%`k%[PH!r3*?#H&a`1,7%g-qQM!=^/GoCj,jffA,C:.FQWLco`@IBCh3du"4OAkH+0r*8Le#(}H!T+8u*8pe"l/B#fSM"1LLWlWx(y=<h^M;VN5(FdFKtfkSA
*b-37:)rhH+h,aY;futq>.ba4gHB%m)5*?8rY;."/_$$<K>/?;ma5K4;;/.R>ZjdwkBe`p*iB0".+Y(d`xLTY@9ak2-t!31[cQ:r[9</EL-?qsL`^|anUgCu[pLuf|(C`{d?9=Zya0@PX
qioB!GyG7BVT&GJGhzOX9,YtCf
7$vs`34Et#o_]57I?<vl,GTq=`?KP?IxXp^qu=(ow=XRQL)Pph/2S%;Z8x+dH6FlJsO@WZ~,S>%-|fm)}n#fo7r]6xrZ0d=2}U9!{sLnH&1C#vk4#T.av3-"88)MWU=cTBE/Cy#N{$c7(HsGx_`nnUvX_,q#ZxDKeFLS1b3+lFq8T2j.Bc#g5Ev?hJ{cr^(0Nmecv
/-yXFEe/_4s8[TLm(jUTMO@L0j=[8bW_B2"#"H2L(]:k6^[m+e0XJ_}?Jd_NnE2sd=qrB-hx:=CKt[<e/_9;yyL)3eF!g:T
>vUSZnL[&lp>n`6esT|1Q?Q$1/GT)#8ZUP~k._BIck9"D?{V)sd5!+.%iW5u.BJuaRf"v1<Yk@Ui~__=*1qA;tG1))OpTAl0K*d#cl,2hHLNsp55i$D,fu*M:TGLL1?p5fXcDHVd!e@96oN+{lJfMbRPoB13;o;ty2w_Sj"H%f>!+Qt-zmp`!spXH:HyCOq]8=1
g+Mpr!F#Ueo!(4:L<J/Q$^d@[,I_lG[XNpV8}[4KVJ1]~s@RWSLVp-:U:R;`"!@+41wQsxV`Cjo*&yF;,CCL%1yDN+1HzOzq4>}R8;VUuKe7g9WoD[WPsu`g^<a;"&4ywg6I4*FK-Q,YB9Fc3HQLG-TJ54Hv5FFvTp+-1]
VvLUj*^jl7fro*inTtkJPpI!G"mQ0k?]u~jINd^4m-#bm~Y4oWv?pX`&GF@:J
=pes0;l6%P-ll95Ia8FG;pmx!}jpL?ggDm-[-[CPrd;_HqmRk9vDOhW+Jp-mmH(QtP>n)
.<3Q.pXTy)tS)h2(tjwo35H2IYUv:H8,@kX#><>,0Q<Q[*(g6i".CVclA{;yx=;Z6,Ve!h>s>G=*>5s6fcnxbz]yTR^w`F=QVRv*oIfbQ+Z*l4Uj=%G%H!MnDwFkoLRoiDXo]&:FucQ`5il]LaR]19@1(94"p
.(.=Q3TvtM]^f{*Gw~HwHPLV&:
Iw~g+/|swT<.R=N;7GWhXEU69:_*Kq]`Pa&F*K1Vx%-1LjS`u1#i!K<g,:X%*qkW+IJ$FB;GKIK-?*4-L&p(QL,@!ppo<J).;t&p!86P~YP6U_^*u<sX6.;E>?+LW:$+wykBeYEvQ$%QrdT"omsoJIS
2-
OW(]SmJySBfD/Ea&f%BVeHQp+J_8TO+mwTfMP?;"G92<4.2W+R7>"(B3wRYiv]^T",`gBw03&9"}V=R+1Ad[Y3K`CL>[8;(l<k3q19$O)vhX*HC}p1_Cap&^#Nym^#6w>Oe~tiJM=rf04Bi4`Qf
?=!%E3Vv_!"`^ZY]QKd-3v-o!*,6c;xjUg-gWB*"bgo{YYojngq$V<]Pyl0{#h1obu#
Q#)N)u:}-N$|o;vr#nhi`D<+Uv$Nc{j|CnQa=B`SY{Yx3i-sY$oqydDO18%HR"
i(vBC$|PK%x@eiC&3CGG/Ure"0.]cu7_MH[j50XWB5)0M9F>Rl;Vh<CC"PZtG)%CpF9J,v;+TFg_
<TArr{-wAd<=_[
9
{6o4M!$Y5Jfehp"ogq^qIZEQx%kT7hW%yON^xHQ^>q3g{U$qZE4Xw0^=(&<NwpMh7_ks(1XCr1k_.G4uw_r.#hCy_E<.K7e
y
Qb6*SQow44T#l[Q2(#B1HG>>`x1Ngg3-cDx_e?Q_:pSB9![&3mV#=16)~abB|SMcZUQwU/9C$a|(njFa7.#(<3-!|B]/^4lI%G1sMFf*t)^9n2.P?B32is:j$H3KE0yWb=oPC^|9|0}PGtWwHN&';case'sv':return',ZuF;f{p]+W)/`S!U
SA^Dd?&#eZm!w<Re
[{2%F&;Bb=$}irPx/Kte^R(B8OtXBbC~ndgP$@0yPcZ,dG?%sJUMnoJMq
qm34y?AjEj@Z^m2KeTC[0^8NFVWrv1!RS~YRn)gQknl-uzA:Rt@R
nrQ<$vDQAV}1PK(BI)J!<O(4hgee&LD?A9j0i-pC%Mi35K_qalnrQvmmwuFmhl)rWa?*C$
BHFZZ_t~x"Y$;;,=P;=~>`1S=^uh$G3`K;ilRz>cPzS$ZKP(p[;&O$<NngE.&"V(>9&CDH0$
/R==J!D)`8;-rIO%Dm$"w^S>"+"7<A2V;L[@-DKir
LwL4c+IpOD+y
4OCfIEK3!Na(n+Y1ea8s7Alrc@/h?4_}"nco5X2~pk9Ht-KbD3`plf-t-L2}8Q,{Ep"w7k^|p`EEAr/91&uZ4PQ!o9xx0;mLC9PJX1+`@J.cQIj30[i&`GOiItAa0tC;W
.f-C_&Z,g?%b)fEg&.=~=SDO3:mEg9gKZ!Jto&TD&P$1%K=guquKM=j0`>56"fU[S4[aJ:b0t"v]k+WqAL#~>r)t:V_rk8/.;Xk9FHI_Ss.qO-j0laZwtdLQ^|"^,3$u65ozN]nG]P<km%d7U/K=NYsDN#,Y`C5)w{/O7O,#Q%+;mq[.<Q]4=xnRLD/+Bo9{8fLD-9x@oeXNQ6M{iW_CQF6n42bDg~L9)j@
5j)9#dD(a0r:XL2<"Jxe3
vjvO*~tC`9s:
S9_2M@ggbZZBdY@I"w.0p@ygj!CW,G;hTUD0%iP
x)^?J^A1;uc)/b5s>B3@2_1d].pvu)MC&<<o">^8@?f`Ep"u~`4AJUEcQ!#UengVyOHNsIW$A7Z7r2~8<I93?Oxk/I
$A*kS{W1=lW)vu;p<|H,E9
*xol_.Ym!R1F(xFPY)E>fc<Dqd&:`XI4o2J36w@xU^cA-Gwig
k$(IiLA6$=r3=Dy(dkTh-_V>]Rsc&kFxWN4rHYR
-!`eV0gdZy$Pel#r#K}AF7Lm22=6e$3IhN}CI2(q6:5j.Vivs$$N_M/E1Op>vYdWa]PvwBmU~VTW];J@P>I
Fb25-M:wARNg+=YO^:ead-+YIdkn3fk&UoZH_K1D?LNb/*;=sT<q$SpJTWD:#&z0"s]_v9}HPb3+f,WXyX<H_*yAWH=Jzh*_(W4D,B9cH$?nEks[!5]
PJIqA;lSShq+LXo3HutN%e&J3s4n{[Rs/x3nyl/a{miD%"-UDM:8pXb7Rf@y=ed6!!d&i>),J0
&<PC8]bGf;.V4OCmgly%2@`
kZOEi`<3(b_0yF**ex`]]<BA894dGh
~y}Q(I?9N#|ErZ[Y/(v9@*d*
qK^CKj@$Mfi|6~GzCLw-C"jk4>f<*Q-QX3*bC>bg8:BwRs5)[fFvS{C`4w@i)(.%eD.iB,Q`",omlq_ybJ$FIQ4Z+_m{-kp>d$%b9/?]fI"u>6BY2IPt7ZwEJGg/J6Fx2L/Wq|9eVlPx-lFVoCYi2hAyL$dAq0Va.2>?]g;k-;h7D_r;t8Bw4j,k^K]^"tEWSvM/&[ij]B_.%zyg+~C6s^T<3aK[&rTR?.*Z-g25]U90!)%.mK8dbo?h%7<pb8UBmnEP
QD^iX;,:zYw2/ijk#h]=n.L8<"e*7Nq_8]]uy_#*9tp,uFOF{K;h*]wA.UFQDZ!?$@[S1IiADu`h/EC%&8hYYQTp5T1G"vo-jWbwnR;9[Kd>I4,s]P7aM=&.MJ(kU+mLP&?1k5lsMnmF]uVioPOVn,O#O0!)0H`t$ix;F"]GT?SSH?LK60.X>VqV(5n=~eR:s^.nP1gRM8G[Y;y$9&QfXsEM>lCh2Kz/w
|98#U+#-WU0`=U"<Mi`w^(h?}(LyB$c]veTld35UzM
HDn{]!$R-X8.?Fp@rzWSGf=.!$:[g^QG6;CS-q[QQ32dyKEPl13"VtXYCl=Lbk
Zs6/P
!CG<xpnc`QxH-JX
"##+K-IK|CPB^%CO/eaCspB[*x|z),z@
)&^rt}um^A@XC:c%,V@YtnRt0-M+X}23`~d(wJ2T5LM4F;G<?QC(0rP}ATqJ1e)n-,@7;%y!4Gy(_5U057>ld^@Hy;e(Y|@{_j1DcL-2SToEqVeP1%m/UV);1QvZwkHx3RBs.GJC!4J2l%!pss2>Ot"xcft_)Cvf8fPs,ySu3c%9NF.857V.xCy>eO/M=SjbW!!JBK>qaldu;oWWV{DQ
_E86UcqX`u#%scm<_jDv2.+;<kJhFe9H(3y!z%Y!tns5Itlmgj2:s*v+UVivq]3.R"N-RBU9YyG=.bjd?Q]v=dM:=I>S=5.Mjj2vh:MShvvyvM?sne3=rR<3l/1B3MNvbxo.gL9i>V&V/JI_F!v+_h6@Y/`O27
qLJ]^b=z@
=IV?"W5}4d)Fs/9r4fTe&eMK)8ettiJeUKr=1[:TE:8mrzaFZItxKnsm@4Y3S]^Gk]hUip
V`Xe[CI#h./W5:,>P3VgE7R56hF6{>D+]e98fK@x+$<U6O3gU/(i}ejt;V_GFj[h]g;msRUt8dXDJAfJZ!iSvwmk|=<2cq:l~g&
qcY(ABdK3hfS{3SNoJ{1m`(&4pS:+:@Xt#+p[m?!;24PUb5J5cGO_wF;Z8~9L0`J!T]nAqz[e!;2bd%"agAd1XL:
$"d7XCbg:yJS-vtZY0&iHYo}2vY:M@0&Gv2sXk!&sdp.YUw;%Wyei^+jqoP`pwcf1qA9H^gbR19DVNU
VDy<fxEM_[DxK?u|opv;p6dFrIY4d0kJ5`?o.oe!8!MVomLM/$;<J0fj9CqeYN=V:U[r5$
B;y#;>mM/RWAaZK=v!?7uYMvRPRu!g24xg4ctRL6*@CL"^xS(#sv18pqayF>#l)fvV7C;%F3/qK5W
+O4u{jU2SFMPK-8Y3_.&9qqTnERiDk_o7Ea(WCng
EHK]`{%Om2A5E>MKkN#xqQl0l%APw&*_I`*SO|4rt65f9[<lonI&AOY8mH-xfhsLj~.*MYJOOQ2Y%opQ3-tL`8oCl&
5QUu48byz$qCwL2@um
H5Ls@zgLHqn&.2D{vJEhlt;xB3I)abs|vzQ7
Cbq_n=/mDQ<OXV}sCrvTdx0aAt(Y6B[=Qd]V7J77P[Iu%R=!MoGG2=Uye-fQSW9]tuSLG.6GG/):i5(sU=P${ZFX/<6P+qt&(I3E/a_.-$Wl9ZAI
Sl[NikMaAivHN?60,R%?-HO/y1<xfUMVqPVS?4]jm]tiO&JF6L4Ggap^J44A`ap/o4S0YHoGxUv:pa>3)3.w[ti])*k3wlw<ku;C>*B=B#q;uePg<f#cTWnmgk8Wd&r0??-qsAZe9Rg|eOAId*DwpwS9B-3xoe7`XEl
)e1C<otePp
;?Pe>pf82nY8-)|y1`7?[104x
|Egy0@`Zsg_$w%^[
eSyRQ:Qq+dF~Nn^oegG}>Q=al%kZ5zIF57gs,[,~jW7O.-r7VG+MY4.F8Oh|CX
XQSBDTLY{X6Z2Z#=$$}WZ$0J(>|31Z0^x(BCsr#]ga;95c2-Q_6RwV02cl4G)fOlPWYx!v:Puc$
u,_YnP_"%e&,6
T,SZ48<;$TZy#4Z.Y=;5[?p)qPt(11I7tq=>(aVh8NGW%pR-=n0lnJJKFH[]ul%Y7Dq^E0bA<ljE-:RQbVPt;jtYH`J;1Nd7BZTr~q1yUbEw!/s?z4Nq1_WC1m
T<AiXUt9nv8|fp5-VYtOH[k5vl7QtM;=k|rY,Bf4#l>q.@y>pXiJEOyqH/7`U9PV<hs5>rW}rrcd/
0<cqp6CYI_
cJnKuN{(~qlh5X17a:qVo"`79DqimUp;B0E
7A6A=EDeZ_dw<4c_IS=mpX5gYz"kSh6h9^:"vG5!ObMQBrOtZi,(/^W%:4Mu._-*ZU=/1s~wGUo]sm4?0fw,^_NExiw_CoNxY^>+>`ZmW`
i84Hb0"&B+6MxjJ#[hd~:5/P@"s4f9Rfqlc+[_B2[@PA/iXQ!Es!g;>#]LVvWfd{^Sa]BEu+G_LreXb}d*qdeOEHCL@x"%F7A,&LPT]K#z&$U}6^+odDyss~1;h)lVIH3NX;3FIQ;)*|T(b6=G7x!y?ahV4A@#cx/RN[TdsS?rr!"oV}av`_&+`OD}k56unEuW8[9)Tnw:S:?UXJe)e|A6yR-SoTgB9cEcVbsyq3Ax!=F}Ri1=r}5,lx/I*[s&6}"(;$v2tv`eT]24^k${xxBAyhC%';case'ta':return'+n)V+aMDg)E&.O0C!Nl,>,ICVWMf/q!f0D|xG#WO)d/eQ;GSgKCd9_1$4eK*h#(`2;rc7.D;lj"XYn0
gw/Xc3lD[GqN&XA4?b9LZc|,sv57p?:&qCcMIe7SD
we6ffL{=Cwx<`rCy|)p".1cq-,p*I?=*IWXExx8ytuvdiO@Q~#nrq!"ya(p]KJIIawm:9uQr+io/Ys4$eS0eRosM8(o%o&rD},xKu+
_C@??G"[8ztgMjy%PIm6fuhoMdYlP":5fE_Zf#Bb1{4qcXqGxuP[Pjr.RtP
3~G}wp7c<#C$,X9[9%TKw1wxRblxCG4>[BZN;9D4E+<x^`Kga-VI2@<kjilG_:<0^2*9w@89W{3c1~:zahZcamQvq!o%%0hPP.RuPsEnSbEz"#V
/2D
Q9/uEqjAv4*pHsqfeJVM]^-W$=OClItG:/Pa984)RoOE$d&-28^J=j*+8K=D3$LAg>=E++7Qp#aY@=O::=WSy!W+P;Sp?VOWM}-SF1_Z)IIm`6^Y[z$QIV;pBm<9Ve)J=L":GaFq`rdkAmwd&em2i27)2fug*T/06
[miL.pk.e2xAs?$w05QL[b55NOSpq^QIvYd0lbV#iFIc-r>Dp]KjEm]O_7EoelifR|mI4HH?$%&Al/=+#EDq4SPZ"NnHe3=U$
F#m1gM%V$0V~17RtU{8jtkkVqF]8e{KKS$h5_Ru%7PT:YoA"3[6`r}#CNFiJ/Y1*]<W5:Mj22Ro_Mkw)]p!tqPp>T{ESP
r:0OHzA0ZRpllPqt_,Jss>1?i`1L_V0-!|,/T09}-KmAT|T9r/^X18_x6WVRbp>J5$u=)TS6M#9ZF^1TU]u{3?d?3_S3e,SBHOkfJ%-mPK7NH#X[^fDWp*CGWCN80FFB60Y=5%h^-H#8*skSEsy(p&E!c)S#k}QRRAH=SuP@I]cK(75Q*L-0MMe0JfT<`fom:$B}("
1A74N$E-$GRs_+#8PM)&ka4vmb85Sknq]%F!<UJu@
Z;5j#P8uCyqUq;U[z?JlO,e"K%[^OY1Y9xb*BFX&1M&pQ3DoH8>QBU9SVW0HuT=0;PsBz8ydc%1O5`VxorlelZihGRQI0Y3UPnur>aa97ZBRsMkEs8Xr~d.
bO?=f]nJn7+u
m(c>*]IZgg#pQ+@hD,M:j;&Z0P1pn!HaE|ZtQ@`&QXv2d<Ri"whFa%j}woOOFGg`0`JF<E+JULIU0pl?AF*X7D7"7PJclc#+_fLvm,3*[G^X%gITo*[-#KGKZW9ypzmKDtnPbv,6/}:$+R]Y`}AA1ZqE&]3!Z/EZg-#+8M^@m2Q@@
SkFUHHdtEJ1,qK4mtZW,Zc02"X.8s[RDT"sN401V527CD6dXs}C`f5Je,sBL`G-@HAbxPxJ_f>;U7S?;b&$#Dx6O@D,e9NP7YSqM-[jcr%dKQZ_/j*[]k@^iY;>}GVkn=CHZ5pw+Wo;@FFG-+?7$",(`h<)C?T5.`aYA@uO</`uc5}vQN<pVr+(TIsgO=7V|mg8-?~k->-S$Ifoyg3if$hD>xdWaG:F~l#lbn<+kjzIW=yt=OZ/teQk=uf%PnnMo/g>(Y`@5)}TU5Q/]CI30QW@!kwCg^nr},v_*R*Ok`/u8[[Qf,VtW]>?AFIgAc`B]F`Hz67D+Z
8aCU5v1xc>0PN#+0+bFzFkRSv~c]vXDayrjE.K#"="@GP`E9?7j^sG<+rpJEfjRW
K,lW~wnY=lW%,N`6Wg`2;1r%2gB$E2k6C!KL~.v_p[K#;3ORmCnn:S=7#VDprmlj,beAxlP`{a^b04Up$
e%mG1T!S7as+~6LBRH9QPZlZ$nr$qa:mr)KwY1gnyt"(Ro/d{a_$KJEKo=@C~gbnZ+pdK1_1w9;4KlR?^ibFfcn%PQ)nn_OXY3(?KIv4<M"c=r}pZ_`A^#,7m*vP_LAiS[$AL[CERKTX)/P19:?!]`egCwKh#e&kZNp/8wcMkZaUk_PHdPR9lM[B[UdG#X)F<%S8;BOnm%!"Fs~%NAIEZmz*yi3A
:]3Fno-b7H^Nwd3/sb#@g4G<mb2HEBc9B424P.C,c6_n`DVxCb3Z%>73iTq)r3P"JO;R5CZy%eijx6ll_%^:L%<30GISf9ew9W;U:U*$]6_z2tfW-,D2C],/MGg-,fg9=
2@g$c9vjh8L$p(,TaRh-<.-@qnhnTzRBi;OZQml7x;qtGS0+`QK*V`2E3N!&ML<op]iXepjA$!:IU[X>"Ol}?J)];2Zg-~i([x1M(x;G5B;I-l5]jLZrv#V$xl41g]QG@3ts1D!?LS*YU4HA4@w98c)+d>5EF3g/8JjqT4fSE$R|
`h5?"7gJfbWSL_K6Gc
rUuUHVssg]O@E-1CBPOX@{
,ZZeIj;Nb-W?icUi8@7qh7{ZkT7@/6:dQ!mXmv6]QhUF
`,UTd
CVL8rBga`k9SN;jKjT+V08I^L7,NjsFSo+pr-vLs*SQ;M1/6;w@#/MBB!,)j5y07;>yRe22wNx_
Hi_x13BsTT!6Z[u4t!e6Dg4h0HT,6"r*F:r`6!"6UteD6p=L/LTiIj`WS/.h5E^twZdXb[,+DfkJXz4KSJb#2say@PL]alNZ`VXO+w0LY8Ta"SMxH26`sftff4*N?b*A;k`Z:eGx%D@7I>Bg@EewS)BnTrQl<>V$.PR_@Ka;8zd]oSK43y:DTl9?3,K5)Jq|<oI(o.b#M(%v9qk.m/ac_vVPURTr%[aVaKC~tFNtw-OG
6>C=Q>:Uv8!k<tEbVqqC&G`/!d
("36mCYI3Q:-etC"]5kt>U^WVH1uw!4KEOn(v3(*rxrI8di:Lmk;<zaGgx"xv#q|P:Fgd](RU;T#jEG{lK,=&"by?1j]/$s]84sT1mFyGv&4b<AQSQ(B7]MT!QStidfz=!KC
/OT
hb4j2ovhC9fH|jrdNd/hdouIitgFFwOuZjVclMvAGNeDdv01W/Vjqjp_|WNT(QZYt&bD>s(U{^Iv:dpT7%2RYnr
%91eX5A<Om2T^2o#jJGF(
>JXLh]OAKt3h
5l/*;9YcK/<sy(j%m1HrvP,/Jgh[qcV*dzF+$oqrJX_?"WR>mXE8_X&&D?@_m^-`7`5sjZqSE)v7]FIKC`R~QCDyChO|AYdYtu%50uI|-V#JDknn/W@h"I/w1t)wq<jm
_,%FA]#Gg6u4,?`Ex=,$38glW(Fm.%8SCej]46dNKiRqaF!A([DHeN@_1[Gs(3^dG?RUE"I&3JcNgjCQvxU.6PQo:6qHoBOg~5^&HC3Z:>nGV<,R9ozdCi!7r%G9#*N)f2f>7):bRQe:Yq`/bO?oZ0lN&))w6v`9jT$o5H9;tNR1HlV0Dq(+O]6yMc2:wY2^q:Aiyjm"BZ~Azh")D0m<KL~yt5`lIFZc4Lq`(jf1g
1FoClV4P.?Mmd`ah0l*jR0d=`@#?8&F@*@eJ#
+L(*pna-H^!T.DcRu@=vvBbKg/5l4b]#Xvc)Eq.>kUBE,L
O%mVZUB[o&+b#Mdxx*c8=pe#+d=@SnK(0wSTsA!e)S7+igiur%=
%YD<p]&N.YKdd4VpLWpEu:m@/PQ_$~FN=^R@`g1181+>66[^J7"eiPXroWEZe.$bj7Lw)BFm7$Gp9AtLAZP6]_C0VWuJf<32uKYf-Gq0Mqf$;G;E`QMS%k^9!!1At^L(
[!,c@.POc9a87q4?Da36nBcoYbd!a!JtY5/)3H%p,3vqJS@JoS}Y{s+[OC<e=
#?o1*?!%%cRhBk.Zj9b0;_F1hS44nAC@%+26N3{8q/ShuJTWp-c/6B?)7d.)8V"/Vfu@"L8Vln_Bq)3IM%5_Uk6!(#y:|jg5T&$hqu,.oofKHpy%2UafHhOh
ds3Z_tGz!q:Y`aXGEA5Bht72m#(Ce!fy4*cP-l`(]Os/K(yuEtj{&G<>aSu?4*/:7Bl.22^D&80>j{6,#S#>]<hoyuK_o-g~,^:
7A<E`K/%-siL!HKI
+Z@X<""K0V:qs%!a4)HOl,v_LKYyna{GXvUW)_D3sn?21M2cPpF+U)g<eF@+#WQC&@fEBG^i-h[
uYw5RLdYI5b-qpTlRJUC~3N)"CCp?tDyI/{LwI#H)E!khb(T2r}r:s8B-cM6^X
b7#cOX@X:6hW,x-mmV.G7mc8H{sT#)Zv#,x1[J$+kkyJ%1s?,qc{HGVjfU92;)^m<pd(w5z!1c9RX/9X^!v1I[*ctua"wVRdV>cE-:(zHVK]@jBLW^%,VX$a_`?axOCJB)SIs.I|9@E4c"r<900kz(Ie`{LwQ@kzEv84Ch1gw*54dC
=1q72Px#St./v1&sl"I*P"RxZ]JTEo(fkHFoMBJjz]x@+>+AVKl0>bG;KhIUtb4OQX>abkum|_(P;yMs:(A28?6Mabe[e46t5Ko^kgh.BU
0]t^EB&XYk#~dH#th-4&N~]x4v6Zc$/4T8R)Z#,Vwfr-Em2cYZ"Uia,[J@thjc2X*/uBDV#G9]44uJRnk(v)Kpj3HfOT5^p=G_]z[~,Y2K!OAF&+Fy1qqo_hf`:p^3VOFW>hcB"m6GRf@(v>&6Ib6!B%dU%(8:?muh&
"3q6U42zqB_J3EqYFZ!m_QW;_YZb/*pVgXIl[:`Oe^ej6+s~L{Jn;LKG;I#a87k7I9X=ty4"O:FXfBuzoa=&
(Kr=RU5@+jBa)
g6lj1FMT;VgmWo#-VPo;(5p+]IhHZ^f&[ZB[6!,Ko9JW*u]JK/-.:Q^9oqIY1t
kWFdw2wN<H#c"Gkw@
%J#r6R9R[Nt<JW?VMD#|%^ODbb.+Q^OF..S,EnYYGSBTs{PQ^-)=?"7rH<.wX%XL"4Z&K`l^u@sS_)Y(.8^L@R"8fDWnTS?f){w,1t5^@*QUcD[qDh9uf$psQSu&v@<r`Mw]UoCNe284hG)_
93HhjgO
?KIM[-M?+EG6&gTd.CtKO*R7ZtK&]#R3j4~ii]7_)g)ijem${&SXgdlI)>6hi)#p~yuE1-_cTu30~MmWxv{t$JfG!9rMn3KttFJt=q|
7d7t1NeLerfl)1&Zghk@ERY#>D{N#@rq+h?Q*%>Kf@Q?01E2`FMGq8!Sqt2l%Q1E)5&@ay4L=:mYf*Eck=V%<lKB.UqG
.&e$l)P}c{,,NVJ%6g6;Q~KWdm4u8a63YC`SV5!#T^,,#z3os(l`Zc5K+*`B&c%P?`7`U76Ue;KiFM^+T7TNnHs!uD$ajDcZ1q1L4r4[W]=[${rW)n4})RBOZDy:
$VNtug=>"9~%E]^bnq|q=5$!,pRM?Fg!&k!6qrx$tb>"JuWJ=rM_ftK27U:9xG~=C@9qIDaqa`t@)By;R$>R(
WM0p&=7RjQ"MFy-7b/
nYv}vQTOAu^*;0)mX/EI9lH#?=?kpN&M
O$bNf9yTZ:xpcR=ioND[nV74@^>EbRkY4Nkz$1xseCBychO["NO$8Ds
EWr;<[&3ZewA*TR
Z2s/?kZfd&CDXaE)(Yt!bSI!.#z7)l3e21.?vg^aFU_4KlN#]<;NpsZYVnM%V]u=Rf|UdeWIVZ[PQiw&]R=Pgt=nv!Q';case'th':return'"c0Q<bop],|@-/
^e"I"a5s7AVB"PKi`i/"#|xtD:#E=UjX=I"c0flV6|)QT@-E8t^t<=5:&mr;CT
g?GM
RuYINFN*IkpK@&ra=D@"n}j]x
Nr/umaana26)a<_$[+n{u)n{[Jnp"A4|xSkzJxI6a<EBFuQ?dckOc`1hLPL|l,xSM]Dt1=ida*Z=]X
m^Dhb@),Um@yo&1B>7*1,B-rXWj=2r$NqmxsDj7-3hLc`^u,vy00Jn"MRJUM^n!JI[oQ:+/]Lr9=1R8A=YBEUJ*E?x^:v^uc]KAF
yILztr%Vk"23KeuHB_penRCC"&1aTQnmH.Tw9}8&U*a<:2f]$Cw8r~DA+{TDfcZ[1oqSHDMAqZ$[Bt4T[S.@-G3$!#sqppvqORJ3qZn*BX!P6Gf3Qz6@w-+6
WYjK/r-Ryd<XH+X`(6$^BZAv-r9g.D&34pD58o(v/SlCJMkk
Dc&NQf^r*2+Jl>OKgZ^"?A1<@.ticqQl""bfA9tHXJ2a+]rmD8t0Hsr80newtd*mrKDqx;=#?:62(oD.3ReO5fsPOWTlW?v)Tbl;$oI4B!!`rFTFRAaR&n_VcoI"E*5NC0R;bqY7;:jY"BUyCXNuvtC=k)I_;<WYw@P^/Foxvh4z5@sqL-HY/=bEW7shxce?*KfwQ&gBAl&.GVWXj.9$c*]GJN$rbSRs#<^/HSr*_+x=4Zwz%!-xjfZ&/L]A?G8ajcl_[|s;)EqqfKMW(kG`s#bae(m1h2qc53T4Lm2/rhn_7$bo?~>M#3yriXtpE|kUyY_JJp!EX!x%inK4tPTrK]f7j8z(Ky8yqa9_pSZl*L2$@.xv9Z,-v*NzXc)o!k-p<,WW8b=kF9*3[5>_@6A}_~#S!g/Pxg<Tu(>iEdX8Gs@@HE+JdiX_fu%TjqrfRSnt+>atW^Re
{Z+_SZ;?vV#x?E{h#
(qnY4r;U7Sl!f9=en_=6-?KK2B#w}W2I%xmL9dB7r0-pjUsj1fbO&>!--vn`yR#
65_UO"D]8JOM(UNj3>X/
#6DLUIWS/CQB#{F63tHP0XWhKc^"W`L4%vRA(1W6y,vqsxG>1q,lKzf81{5;k{!sEr-2-NwxB{#0<IXy]Qme//$MXB:.hEBy4},7iKY*<3N|/MhXF]ZLf:Wxv^ivC=@c#[D`>47-l;x40rY)JAr[#E:~s.bL
kf8JiL>[[j$1d>]Ch1hqSP?4[5tW9%|6zv"R=qWvVNHl<*BoADi?kOrrFbM+51MNCM,:wVqDIXHR14n:`>OX^X,yXFJw$H|(eGEE:<1DvBWF
<T&|yP^x=~o}w&EfaEx#.j6@+{eDm4)/hy-!pZJaFFpR4%
~2^oaJtAZCg#vP4te<ZJC*5NHV5+IT0UutmR.]E:cn;YZr2F3e")2kor?$G*S&gmaV
q71|Is=iweJ(6Jaw.tN.cVSC:Vi+tG5)<yV|p>5wvkv/[PY;=6ba!DJ
U(0m7jtcBef;P=m_l,+C,{1#8jHu#YnnJ}QJ_cnvN&`FO.s:#lXS)39b5Dku,yOT_U(zKdv|$$xz([j{sK;qGXpdlCuQLJ+X
C,jSYH7sewklVE&XXL[;
hE>AZx:K4=20mX"G!7o<H,*O/&%Ge!7W:Jo00FG/I=OKYhi*6;7)eVm54{DE@VyG&YTEFxTA/:bAn-RYp7/pd3AOMw:yInGl#8%h60&PZEPbDEFg2upF&TE$fC-tk"I<I1U#_KEi?iMK`W?5S&`~qZ1=WRCOLbaQgz]Eoz$KgoH{fdRm]rd0%eN
#"SQk9h"3qVfe31:2:W&"<dIMr(La>"Y%p.t
C+(6/syspYip/u)Nd`l4zx0qwfa&o`PHP%NJ/n"K}y0:sVg+0gLvL<BED40CY<XLJpI]d.$NcEn13eAawLyX!(0"
/H$x$s%KNT#^t<N~O`7iS).k(4W}p"oV?*%{/70!R9*y4v$A-AX~>x_I4bW(ARF*P=p}hCUj&k#3qS1O/.vJy1S5@NUOF95>i2>8T|Y?Y`#O<R=(nfO1WTaYrV[o<HUY(6op,Q3z6ZM-#Cjkt{=m2R9p^r*mynq|.P/R1(^G<vERCMV>OQ$3R]R(@dYhXZLbTu@53f^YD8oGR>F@]V@`PIKU-
]OVaPZ-2o
`a*p]B)H]=3VN>pId.*)n(ePyecEOX>Z0BWU^>o(jrA|Oo7lmYt~6C0Ww%Qw=`sY)O;nDB^:sPdoKWal.y@[-2$XKQYDK5!zj<(Soy$wC|-=g.W7)_.BMzKUeH%WC5K%Q)+uKNu+MMS6j-modftehlPLE^3nKrDWBS0=jt!rC@Oel~EQkZjy.~>,BH)ObeYeX%qGQA0p^ZKLGJyhmx!ij6;Bu4;R74D6);YxU|hImuM>p>H`;5Ne:%fl-dUi+pOk$a_z0(:ARHK$Uc9V*LqCB*$}So,<,V5o?V9>EVibq;]%Y<h@oWBa#bK.,Y[_BI-;YJ$82Ps$=ZmV=29yZY(84o1zsN)nQx8Gf5=.ZlJ3b3NomndH^q>;]S@x^"4u7^H9L,6*(y9l8$KKK,hW5"?f[?RhO;=Fmp,uj{buc!*R[euEd`X,jAUQpQevp%"1Z8m}k0oJ(R+yW&p#BjRLh8ER@TgK<9qCpT->dC<u[=i3h6$GtDo2ccdQ`jX{/Ux&"G$j6^f"0]J^<%lt_7!#Py"&4l.4`-jEi}]v<jDPH_PRt4w8<XjZ#Ee+7v$^yjx-L,_pR5I+&5&wh:-hly^M
x%!-5>DdZ>RKk<oJ<QgB}!tc2&JEx]zJc:=ykrS-N&wQ6L]]#olk#KKR"_~m!CY%t!SlQu|0PMSa:wh:<fMigyiXeLaGw71?*O-Q[m)gbKl%i0EGswJ+:]FKoT$fjVl,}]d/y)l$m]Y.w(|)h!ATq0-!VAXf;c<4MMxEej]Puf),MnVUTjquE-BPZVp/9ODlnP[6CCyD{t@YSf)suZ|+Ul-Q0&"C)N
oZd+_t??"2KdKFju=wIw^J@0bFrNg8#YB!64]sH&VF

(tdvHZt]FkSMZrXz(glzl$#I/y(rdsLy`,m}eTUhEPyY!("I8>$:yJ-anW3NLmR.=IZ:M9n<"$Zah[W]5dL-)_(FZFVFUC,ht66OTL/1UkYX&^GzA0x=5^-tgleV_@#?`WZi?0l|PpOeQ8y{AUQA?:S.V~>LY]YWsLq=/!wEK"GPZ#`+vZFdV{xhOnO0A)cV:UVrjh=@#/%jI|/:q*>~aIJ(1)6Ce%>0@/A5oTpbaohh^E_Lft,_A`$>E/jJ3HE~0MwUB{81ji&c*AgMW:6MH)
>)f$R2rqvCjp
^_(a!Ndrq!BJO1Qu2}<dPuUU>O!zll.hRkKF*+HuwhIky~?WTHJ0JamHj;%"B6>$7r4skupo$QA}0s2*eu7jOqY9%:C8*n/yH>&hTI4toi.uU8V6B/aQ*J6m9,#,AC+iw0u"uoj2L<WqqA5]:RJCrWW{?p/ee%
vt6^_IcXKZDf8
v?(^i)BKNg.&l"04%+)gQfx0hZfu=b#g`!6^QT;HLw(435%dHI>Qq62^--lY!(5(FbX>S@c+*W"el4>Sm?J.OQ~Hx1Ee(!aYy[:u9r"p8[T)Q[&s,
Y58M`B!?
X;PC)r_L(Z+|_N+=:Jk7`[vC5?;f1$TPy>rQ?~28->IuA]YLi9ni*8ncYhy<(v&Jf5*To6.WKwc.VfE
>Z@XD&y[Y.UD`/vsTE_/vxF=$g$_E9V.J5:WsDQ{3$ro6;Z6$5JZI>#aH!<FeXXX9q_Ri>wMn*+zMmpTc8?cF[BU?~&e<CI6IcR#/}T]we6lQdv3dy#}Aw0S9p(}X}h5g|0`w?9jOF"=u8,N!&8|Eu1:+(5<u}b<m8:`tdux`)=qw"<{vY:X(p<7
D
H;zTvi
-ds%3M@c,C@Cq}Biq:;IwLA0D+t@M8vT[lXtnP`+-/t;n{1Ap0p?`8^<%6N^bkgy;_Tc^+gcnDeV2RU7:Erdf^=7,7QcB&^_pk9xKUR%F_]dmzK6s(l$&$#
&l[#HPW`10hds!TBQSIF@Iy`?=aN*~fXLa2*q<ue
!pi?./}&sG4eGV`
PvjU"v6%$TnlO;{.y64q?5h;XuaI>N5M(
s6IwyODA%Tn^r.6,X&.CSEVaYC+bXW:hgZo]HNl3}=v`Cb*s5W_B24wD4uU&Bi.f%q:pN,:%
#zix&u-;5|tPBx%/"0rI,eOIV_S(KL,nF6mV"oN"v]R}EET0,{x@_FE[Ha#2!""H*B?
.tax]X8$_2j<p:BtyXQc%M!!baysC*qr7/vD1G"a3pYa@=8.+[o1.,VZb#nY0-.4TT*Z>90GtS5*74ZuJiQN**>"6X&coRuxV.DOO:FY(hN_$2v/h^I/n|ekvR-d
-Csg@[NAi82v
p[lw18`vwm22"dh}V!Xan;+2Wau.29hF:&sJn<.$.],2_WK~r_%;WV.v+c18t`./nt<|Ekw(d:pj1uh)gEO+=Zo7m
U,Q=x]ZJtfG=q5T;5Z`Bx]QiM8Og])Vl>7/nvYX$p{BNK#QvtDs~p9A5iq(.gJt/(aAgl@6o`6HY`6b@I%hxxuSwrcqVaQ
5>>p#>`y:Nn1PcJ_%S/aDk+M7V""1``9F
XU9]D0MuTv@:}5F,2xeM2UOk"4j<_,(Ks<U6h#R280M5{]xoEy@.6p;#.wO.m-1[?/dT%W?wMf74^k$;]4S1nl;cHR/%bVc*nm$aD*(<)MA!-wJA$]nb~1&]X)E[!LLla"LBkDE!="ZS.g#ytsL^E)-qWFbadLBea;]2{ofoBEqPu?c^(%MIOkM
il[#Lwqku,rTZ<onfF_>6n#qlH~h*Sx.D&4DsRd8oxC^X;]r-iM8mqxX5i=Jg7
l}Vx/0*x?M(Cd"`eB/bsSM1@Q;#yiDFcUf^BsPxwNEJvF{@AH36_@bC$yKneMbc
cdYVcIEHOir]DvaX)l)74)u3R).T
%BV*!F
)5t4/`/"W~]
Vc1{o2IXui)r[Ix~A4xUj{JY9oSakEf}P
XJv7@/`ZG{w{sd!/5I%nsRof?snIu@1Ok<`DCv_OmMbwv_%%mhCM
67hst';case'tr':return'.X/KC6KpM,z0dR0fsRB_c=g4:xf*kbj(Re45TL@"AG13iRLo{T|2|<X-?tU*-4&cQf"w&/b^PXs?GjD?KoeP=QJl{bWJHb7r<h{w?GoLQ3FPAC0?``])bK0X1_s)9f!(JS^#-E$;*r_OGn(mdQ9fJ*!-YY%
p&y`4k|CCfEx>Z^K;Dp<%i0DqMv1gg)gJ-~qf%6Rz]m><rp`tDzr<@*
t?kqTWsaE!=^!XR7/I]ym)-3pd_BW
L,;R&^=99a{7~?BDhyvhNm<SeqAC40-UkuX`F^uBYxDs[DmE)s|y/J{w~n?<|S<K,1#!cx,#<CI#ud=0R->Fm)%$l*g%`/$d_!z(vN^A[YshVPEDx2;:yH"eGEGP?>
w*3P&7YnbUk%/-%oM|bv@[t46:>
o4NUJmp:Up#01)>#]S*=_7o!Zl7S=|,xqVd{
U!MVAZ_g=#n-oCYDo3+k1)4nDV0d]Un:rB5WzJwrYo#E`7dJ<"[4j][SWfxB21xDf
zI=]+:d@41JDLT`D5X*O%lHYW_tSb`y3>[rigMf/?HIyVl35n*sv_
-&FUb0wDTwW<$fYdz#`NA=_q
g#q82T+xE&=i4nPart$ZY..)JYH>u}eNo{L0Fz7U7DHAN%:$,NM7ryO-WnA]ZtiynPT(GhR7hglJX;
M!?`s[Jks-!O7P.b`VMwAxbxq7_Ojo$r}?B-6H1&,QDS;`Wf]B`p:,QqWfFw~d3KU*2az:gb[<n>[R9a"^!?auOqbw`yQ1CaLPHe/U.G4ZRZk*MZ/b,S2.i`leY
80c9X;Z_i:ANcwb_{+ximF"7C7jW=e#DvdA)/8PiIqDb=jR0DPcvJinrN&GgkCgX<gcY$qhB[2-Bu3o?xN.Vcw#AUfgfwJM:3.4a,])l=cCfS<Uxk<nf"_r$_.yl`3>7oX.2%J%i.YcQ>D]V1=<Lvj}l0H%R76IG=aW>G44,MU9SAI}&aiwo=c5W3Qk<(2HkDrY&J0|Zce1%j$n4>&g+d_5T?6|V}mmfA@x[Z.ykF8MA*W~HtN:2""zGRpu<!4+e?pJ<]qmduWy:ivKnK!?
-DiPrBO>o9c4u(JkVv;u>,2[rEGSm1}_.CToV277?5BjkEG==hkDdbR0&_}n?L&]ng},nkI=MX05F!%8WMHeK/c*p7Dv^1&OishOg4oNgT[7YQ~R%I:(s)6Nct2O7jo..0iR-$YR3P`@_%MiJ_&
rLY<:!$_{@vuS^>PnrRG1?0cK4VlX48D^lZxog#xa,Sqz^(VvVJ1#R~>ZRh[}=[AA]G4TXRWfBB:&T03^G}[pjQ?[D1C}qLpLZ)X8nB7oLYUruZNU"iGu)rk:)TJBH(`M7UVUN/l4TC?sSPjh20_:W>31;GDzS~9ulJk>pbl3b(m|5NLWnFGad=/Qo-uVgoDLm**Vd1s@-x<+*KHJ891lFWG^grbnP8!/&=N"y>!DWXO)"B:Sd{SpVIU[3qZlB!xj7nTrn=a}:9AQ2Tbt9q:S!)QM.<(Y*sBSq?;BIM&Vt;_a&$_T%Kx0Sq.Vo2TF7A?W[:&Owm%Un%4jHjM;<MvYGwn.<
B%6hP,B|&8Z;JjN4&h%edpt`#@C)d{oWHYgqA%vd^LSBShx;0|gF&lA$YVMypa^w:y<i7AHbR*
Pq-OA.07AritaJ0l7l-u$3+`iSvNuB&uL=kDmS5/a4pHk*ltTZPxmDB]w!|MVO}#Nc;`|^`DaTN8Nr<`"He4%&~dkZ&X;9rJ)n2[/QI++a4ps=ju9O)`,;bN^r.r)fi/6u635?1]@:yS{hzP5=2#~S&m9aiJ{q^7^ZG1]/Ny~c*qP:+N4B-(<a$(be

w>flX=cFbs19"rKmotxv59ml[?U=^9nlHG,3&)ew"*z9|GPF~E<t3(nlaf@A`G5m.F$.^M0.+*:9gTxh*gxb%U|LN69`$"V4SWU_iR7quA[2$k{P9Z9^<LRO:K=m8#zRgeA9;h%kRkG=K8*rYUBh}q7j;W[mc40ssq5y4h(^oxisj))!s>]%!!blu?Z(d?pmU+1C&+zLR8TbXN>x$+c#}XZN?aeygjT
CMC$C$rCo7w"{WeK9p@1q9)!.@`oSuf%<#LkmjlOx._UHw_wR$P9+X]y[LE+h#3pABU^m0^eL&L=mCR)}IRHA%Wd|iSP&A3x@t|D=VS
(id2YC-.v0%Pb;UnSoFp_W;TPRHEI?V9AAXK"T8J/IR_*9s!dEO,1uAT-fbp:YyBL<v<]7C=zDp:[d~4)]h?Jv)0YF4a9LAI1N5_O:FA:M-.
YM6:I.<nDXJ;IF0QP$t(y@0fA+/U;vK#M4$ZViR}"hYXCDJaLR[R$r;NAa(gU91sA^UPPwY1D`=aM1uiAk=vm9KowLLuV/Rme7c.8bng)w?jO,n=Cmdk<OgibvXGX;h<nqsD!1^N-bE^L7JY<:$9aMa*]+C>U80&2wO=f=o|d+ZP*AgeS8fEpM#.PnW(x%d,[$LUc3"{r$R"e(0#e9#SCWBeGul19p,UtO3Dm`k7`2gNB(
.xuJ0Wa9C0p)|Y`$~eUt
GBawG9NplkHl?jGT)%DCd[wAu})@0u`iHiL,tO.qx5YS--,O%hYds^aSqvmGF8h|7]O$bor;KS]9EO<]LX
d84M,5"*zegN1Yu
u&_(-kI0
ydHhg409m>AvmjPKx@,HI0(FCAhG&AYP$f[,T2N)o3
lD#V?qzgO<}opvWz$9(:5f1kWhsmI-clpTnuhJoP*G:&"5Z`S&$@QfkkqQcs%K)Olq3Bmo|]=_z`!</C5.[4yQ$;&&1)(mO4oPHf"*e*I/J0)4zhANYZQY0d]>[9b9U:>$HM6MZ+SpHEx;%3;(@Z)*-MGmJ8*;l4+g=P~-hHWpe<c8<&RJ7qcXR33bx$!>h-<D%@~_;jX@)6|Kknn=qEPy2Xsf`f+[k0CZ,NQQ(g#G,)FkCkTLl;Xx=v*T!0}Q3;n-UCu<nSI%2xB8GsF+3&}_r4W>j%>HL&^Eun.ZT7_M39a>0v0?[C,24HKB~c]b%_;h+,hssTcW=4ckO**:q8w3^akw}EnT/Rnu6W$Q6&hQR7EC="w0mrt3bN[5w:K1:v}]3.*]}oc54"tX*h#uvMoczQxc&Lt#6_aG_7
Y,t$Q-0xe7-UagD:)nFpb,o|#~>Z>m$3*-8@+(]
;%(?a1:0)&X=Yd0BdK)Ay&
q@rv1uK2>5Ii++T="tKXe6Ja]i~Gb7n3UwLCI30>f3yB0^q,tQ|Fn+Z_&uJuXj=jl4-k^S^PrIMyX-m:z<p7eCn)fpjLLiMwXSOgCuFZi)Dme,~(+R9($r{Sw_(kP#/.*Syp1xu.=[(UY11A0BWKhTAj-g9Y[nqImiK5aC[guA2Yi6|HzJyS_RCm,MVuiX*d0lLU>s@$gcfLYlq-$!hHu8eI4Zo7{:F0=^Sz)/CyQU&fZMHsYo]CHf
$W=$`i4+Z3l8CdMaIQf~>6!pb*[Dg#N^a4/n4*vbIzQ`w|*?OAti
kUp@)aoP8$st6+_
B>X,.vpgeQ#ELq;0<k06d+8s1!:2]oK[1SIDJ^G!JPxl=^Y!s=Fi{I8<M0Dy)+cA3tLC_!+S{;EHx$^<mU;K$RE`I[YT^t?I0=x`yF/:Lt20:^]lRCeS=H}0Nm,C}SvCRJ=;W?KM8#NT91Wj!htwPwNB7pnDP=:scswLb@!HOj&h=M$u{
R<1fG(zM/r^)Zc]`_rjGT+?A^LJoJVp:@@|bZvcqum[AhKBrROX.jvnV3
.fwyR2)$uRVKlX?`#N(C/3(_G]q1PI$LiF5Uv)x6.o5<,:LH:#Nhs:&7V^h(vBrqd,YZ"5W/5Q5VQY=]0e6LQrp3Selm=I4jg7Wh%eNcdR^6qdSa
];V]x~3CC4Tcy7wzd`h<8^)!pMq[6b-m$aU-i/%SqFu4f*w~0;sRP*)f_kghV("euP8*Q2t]N5]L&V2U581GweX+lQh55RqRSprXR
S#YW
Ag~t&R0Eh+m#_y@U6Yi.FMFmuwONQ_3TI_/kDs%Oh=ejQgP)>:MLx
-q.>=OaT%^PR
5"/~)uQR`;ZP3~"5!ogF%p$[y3G&u-Q)@ysY.O0D:.Hg0xjJU?8vG_#iQIGeUHFUDhaby0Wi]x4%7[$PQG:V+ku03UpFaWEqbI#|%.ux]RKNsgH_TFAglWLg2V/5h_R;/w]}bS2ppNIWCWJVH$Cqy"$!?g]bKY/-99X{c6Lgc5K)AY)(eDtrqU<Pm9[yC]aO0SY,leGp8ok^kw)Y2]t)Jx!!"bWnySX3j(Q/nQ-iKLXmn.Qws<HevE!_95QP<`I~8MsDHv8
mqsR6t_?eC:4M|
=m7WZ2sa~J"
LEMuS9rK[c~:;qT<)B1bZre$t>jD1K"h/=,Vh@!M$hjd<%;7`X3/tIA4-,uSN6?]g#rsp`!a.UfRc3*dS893?X=:VUCG?&CgC<xTrD{&`(DK[s4xfN&';case'uk':return'&evKraMD9*70l82*Q0g([EYhS(?]z,nLCqr"4NJ:#-$#1Om3))04aT[qQmzgeTWF-Bn6v@5(>Xc@7[dJ3XHZA,x84%zK;]0?Sb??GFmcHHIlRoIE`Gh`KX~[Hm>B|s$s.n)PE,z[<qL.dQ.w}(E:PCpDs!EV@01mA54y4`Ut;glBvE(!@[#P+]_
2yP7
BpVz<v)v3c<}KTh^&zZs4q]xhwC{H8qPnONdfm)ugXn"fa($M06"UmmZYv%4XiE"[:hOIvUhan(71pCx1J9U3.
rVvdGcF`>n
a{TLmrr],q0>3CtP<&MH5(Eh&enhM
LW>Alp(d;[.t<nm_s^DG;!ju-SV3d|nFPap;S:QD<JBTHO+SLrq5734P%9l<"q!cP>>[YXBS<1.bR~gw7#^>xL"|b4v8:
##=cMm4{P3&eOxN*#v4XFHW%w+Sm3Rb[=`ftAS_FD8$GU6a>^@r3h:g(%RirIuNal*:pab@oOUWWP1`wCfr<w.f|%p?l%IWV%i5^cW`L?y2ix3"tY&p?pF]CGf7Sjs[k9El>>F4$eWh>m:`wXEpxQTS<Q)J)TDja>(+GB^Wr9YLEtDd)s<IEwT]*!og|"CcPbN,b]$Pmls49:]S*-9Wk(jDtX=]]hKYqV^vBx_jRRsc,grN#7:[V*>FebR]bw&IV:51S(4U!1<sx6}k@<HfL2JjDGnqK_GZ$Ep%@1(&s=u@X],V<%LKFRbIuQyE4#zM_]QgLAf]ONDl=g@<>#BE>pQisagLy[Ibi06X~I"I6TWm@=HW^"&Y}Uhol?H<c5b6$IjXY%8R]Ehj"i:u|x[>$G$3]a3:8b.F;]F^|n"Nrhfd#jLf7@h<;t@XDQn$NN)_G#
H^U{7s%D?R&dQBlbYLuuHk3~7#/NwDOF85.*pU5xxDg?<(fee&n7f#LaW(Jo=y>fGU!7.<N20Och"@=|k<Q2`,<m2{9CmgJ(Tu[EY"+xvo<O$dP5K>U(fgl3.5Qsh:gBO-<ro+ynT,l1[vghKpO%[}AeJf
LU{SJcu;Xba=mmd"Ex,3&wglw3Riyw3&HmsFSc/tf561ATNH8Y%$!@cSyskU#`EhDC3Ogw4/oEO!}@V=$yY/I!}Wj
mwYv!;CU9D<>G570%T?qi1C4o>gt5)o^=SkDr6?CvVTRZK,X:Kj7Qp2RuTCamc!K|fq-zr+NyX
8PHVGdMLdY$e:?Vq*ho{h(qbRmixix%6oOWjx*m^ZSh
ajM1Ps&oiPN,WX)pZp`H(ipiXS=%^n./A3fSwr3Qlk_EJr$x`Op@1<*R9|myU3f&*t>j_:FES9v{LtE)fT44nP#ZbNuNG9W;Cn*&@3teX{:%<5amyedMbL7l?#q_;r=&n/YdZ"e8KM$10uA59Qg`IAHK0@v+E4>OIY`sF@326v[&!,=c>N)V.YTdVfky:y"!
K(K
W
gnf$_<d$]h^PH*|@G"iJ>/nE=VY#:]Q,[HjINAZ-J(bhB$ZXoC5?:h/8BH"jb0F)Af.*$u0U}/O;GNyd$.r5<4~3e0S@:t2ek?tdWa*JZ6cO"LxpI6oj6tEd.udCE;sN*JS0fe4Jflk8{.)PxO:>L_3U@FJvdU9Ts1>GFTv7`kw9ctDQ*uQfg$Kr`a/
ogn4}W`9Dg3t=[qOVda]O6HrUxBW=qa
qNG2`dOH!4L#.Zx
_GK1nMWS~upuDw^Z|*UOXl%Z?UV9_BlDo`$4?.BKX8nDgr0eJFu6NZme6[P7?QPQak/fy&0rl*OqGdj=VIOHSDC!xVhtLBoOWbAC_fl"aR,J+adYGrOgO$kgu!eF8m-.@8dTl)>M)Y[;cTGKT^p$*#prZZ}M-eQ(&
-MdN@R`JoFQQeyJY
VywVKVd^/8R3!0-=PD<TZeiPeJ?06BAvx56DVv)$pW9-ZV1aBz,dOH=UjK]6s%uKXi[[3}kZF<S20^b:^Wb:8;57SRTrGlh#d$(2veL8_/.=6r+W_/2^`8qww%sht#T`B9+,ZLoK3lP}+9A?wfeU*ReA:6k}G}q}v=QIc.Gs
R6i*1PHqI8*7"-S%QqaI0fI=NCb,4r_ftFU9%Kt&/4BlJw2X_)Z9NK|o<$#
X3{y:ps?UJno$nHJ78h</qUu,[~@1qp,{YAVIo}Nho%#JCd9%X{X8apSyrw8tIjO*[}$x;I1@%RIp3+rM!7G6(-;)9p[2^G:YELXVKD9md6fGHCJT/,a4*`G2Rq#ZBzD0ZZa;GO*We5em-a9,cmP3];58CgtRv{KjW+=f1r!b__kE8aI^ef6_=~5oI>]"_NupvJ:25qkV7W2{Z2:`q!dIVee&Hk2%4Pm=^2t~k}B+b]2CRI5?4)IXNRf#Jk1l+nX6aH^:*U*]4Z0G?;+L@s4SPBW9u4>(hScuP8&<0Jx"g0fU^-nxQ+db"Ar4?Pyrm(9Xq"4d6jm#?/RTaEUYHtsmWNAMG*GzG=)G3t397yb2cC4R8P+y6$GJN<VTte"[6(jQt"-pSUYg0P-Q3Mp1tA/L9sA9EU-MOAl|B`?_m|.`PssK4Y>qVfs)$=QN(i*WvTyyt2v[P`eA3gD(ntQ!O#.*b]HI"_uFvGj_G2)%&{dZh%"G?R#t0GD8J!<]GhYXaw=7a}[pU]j-ws^iX?aXJDLBo+y9FOhcu
_I5Hxwl.Y^ymu6C<et?cI!L|S}@["(-O("`a
c`3aGQ&aBdxc_A?m<#/BmKW8)T&]oC}<u1x):$*L%SKQ]2K.Vd2mb&D#lUBvCQ"w1nw<CR>yT1Qu|q$=4uC1P"cC;J-F!kM^Na2!q:en"jV]$C~!SHDSpKkTP>vCJFTiRA@dJz(wWIVwQA}YeRcKu5Tl!f"x"Xep<o;Cso<DTrHdh-kUe1RG~2iP@oEnQX-qj#wAAmTn7BP0UyY+;Gwn%ICErBxWkT"X3JY*%y~[BOB("$E04L(tDSuID7g(mY31ti-Z~h:*&]{(^Q&`~O4kr%Z89Q+_3w3S%@#]DZJ_ben^B*]P;,>Zoh&H&9}qlmr@mu3PeqGW9=Tr&)8`d^/N0cb&w.1aLk<$9-Ita,&<zW0K{Ek>kOpp{0[4Umy#hm#xbooKZXxbL
DLWRo661l3/oU7]KHwC]qw9c<7.Vt!)!~K:QO]qIx]C@
hbEgrT-Xt#($<UbhGSMHtd_LK
0Xc5[qWUU$66@z0~7-c4+,Oiu_e-G5U1`q!n1wKC0
(D]/Luqi52!r_GU"1pr|M*n"kT[wn)aO"{/<0<^`J^lgCQ1MuYA3/kePx*Kv-K(S>|&/>S3"=![iudcw5RUj&9y7WIs:5~ZG
v2"[wXUgwMkoLViWA8&4D-yC-rnc2)w;A7JT.T[Coy[FaJSu9PT&]TMYeE=!PKkM+>9Az)!D$rgPSS:mxgs.-k?6O2AUy%|[/C.&~k=j
56&kYzphgtYN)v/>ud)WuJ0;?Sw%
*-A3r9l%Kpm+Z;JqMGD,WX65
e3c&hto-CLQ!5Py^
S/6DRKV9xR"
uF(`AA!K}5Vdm8mZI6o836tS/U?!.v/d%k#w_5^
aE~qvIy#Pc9#<=MaoI%`i1!k8So#nu-rnw5^ab-B|[Ue"3
U(i3S,]iNf70^`:9X{hULhbMlsv,,Hg%Y6W},
ejB8IZgFqxlK92jvRK
icKj8a{XTcJ.RK0L{qhHUA^A{mM7_AO:o^sMcNd"!=]3Cg7&Pi&d"kVkpezwVux/q4aujtW9CvFR#n<rw`a>N3=?cDk2Ow-Q8<1EBN1PY*5b/Cf6QqIQOG.fo*gmLxf(A"-b)@QP4+D:tl[Z8k],g
shY^K;!6@29YSxk,d#77b@fYS`]V%ktK~A)i~D(+`MB%f].8*na*F/eCs@Z5dVcA)>ETy_L!e:z+^NAEx&J@(XZVzPB0vGDn#c]i1<f<8s]"f,K/;-<CQNqD+]+J]^s,JTlbx9AaB^~GSb$%wv{gLlcD:Oik{qt6Us=v#FENk(P+c-Rm}0K
h^3vLTot
P8R)uCATnc((88aac">(<K_SkwBu)k:1xk-|42v_NUY0n|K#FT+sU8xn@Fi3u<VEWQR{kd-M16FJ%p:.D|7-4SE|w7]4*G`tObgJ>M)vemTC3#=/4MB>Kc>UPK-]d=b=P[2n$/=BBe<ImN/
+Mk=ZGP9_63h^Q6wYSbsfD,w,UO;P35%N]5)tTK.
6V~GIWs.AiUTd1%]QwyRKQf3/WzX8SN=+b
^U?;Jf(dm
UOcgEy=D]D`i./GH4enJ5lf-w?!su^LQj2
S8OneLK7,hC5&:Mnh]XW]K)f<Ft({Y{y7$LrhLX8P6gtfLg-H"x&a.ugT<MBRL,?Q"JW{9?_hOZ+BU
7L=>c)LmiSor)2tR[Kt=HqqUKg
(7m*c+Gk^D
<Xl7>S_20<9XL=W*m4/
n;C78[<S
^oM,tT6
t_:g+@lW"hO<OT;W81&I[6FZ>p{*N(+c`:*SD]6QA0A!Xt@j0r+
]J-@iAa6st.&Y1T5qv/s!oZ-ijhr!f!8Xqq]*%^cp?!H.EUE(mGGKD?@Id=w<:BU-O=E
sXYjTq*?/"bAL.Sd0l>wmYU?&G?!yxVNZaALVvQI^>[+_$BypbfrM7)Kl{FbjU?.g{%4Nm0;!2HFL1j5gjROw.loug>?B<Lm;ECl?,q,p-8*FlUn)2)Cc17xH*j%:LmpYC9l)&HirHNaFD?egV-!Q|bQ1b;,d{J"9g+uy%4OVPrZLu.o;Yq70an^k.Q
nrl:&a/w^IM-f9=UM(Q[@2_n1"-11yDRy"$=8nL:8=DIjtcjw$6By:+8sBIqCm]"x8eBc9&NbB:Cg2]7
1BpId;fa6jDf%4NwiLMCaHCA<xq#EtJj:3qRo!H5AVo"[
?Ce9;$2`V,Bk(;-IBNt8|I(!g7^iRr@A&@=?|YzB<F>){ZAXs[t<uE`bYLLLrJcw*-X!*+qOdtO;pYfEMl4k63hs!oGK6Nz,|wGK0+p(0@YaD56XyLSX?f<l3]`VsttB9iUR?<~3RaETGGX
#>B)gFng<qON,5SEx->=k1Buw(bA^<"Q$SYF^K3;;z$([6L<!>U#l]TuuT%:mpt[a-bC%.gWX];d-=sB^9_5TXQ,WE(no4XyL=|BwgSJwy{Sk4lQl-Z3k"{4l37Wx::I?YCppOZwF
,VqgS_ns}MdJf*rpAP=_slSU`FVn(a4x.@GCf`Gmisb-anl:^;a18py$`wW=#bsx_p]l@Z`!P3Yr/V}:AvH;?gi0Yf8&ShtqikR,tmtFi&<o4%rmQe.k`3XoVIBpSXu;C!8"%#l",kZPZU<.|k?;mse(lJCoQ$vSd.3d&*A4K[(/&=3]2YN5b4nY-WbEN4eQ@`Fmy4pj6b~83pqk&%DtI!;cVu0l/n3ja-qc72aobS9z$r|-qv+AV4EB~W=5#[Ri<K_9B64
=1;X)Drv3l&j[1/L+D;"^Q^B&lXZK20kh=gl:NY`%!(s^08X_FY5b%]v5xa2B';case'vi':return')X/;z]aG"B~@%o5!T-v^M5iEYUalb2
I_rt%~Z/_y;AO/ZY)0!s?-7aPfF>ZpeO!z-Mihe}1aQk`lT(Yf+y7}fmjHc|]b[]g6_~vwWWcdE5bQsca=ujJQnE:#:,yu){pX9NnOM>@GiG9Mh-L~;):(y[jhu.3s6+Pj*+9y_!rTxq8]ilz(s|(`aVnrmP14w~(Ah%CDhj.8Hs1tl;,]QqDyl[%J[Qow_66cM"<uv
(4qIuw=3nt1Qqk66@icNq+KhtQI[<.ImZ:a|lYt5S
kj*fF-,UyAZO(ha&q_H*+kG%yA^6yYG#e*vJm~`S&?tQS+;jP&"6y|R*0KHKytP(wMjKa)Y"(+YorQcoD;%M[#7q+X>o6VKf!j"0<-_>bO/.K_2r8LeI<th,wo1rgKNv9!3<#U7a)<NwCP%MMG"?JI,MD3O<=2]dy_(8yQih*7dV$dHNLR:a.aQB$DB=){o82c9VMM8C^}B?OJZfLw7aKQC3&wMje#J^P6#-Iv7(jr-1C%5vh,bNERAVN}R5cV5*Q9mXCQ+F/lI=fl_H-OPc,6ArNbYjmrgLIBXxjgS90GD6+$UrwXKz^Hm|bP*)VZXtdO&HL^E
S
n?G"2]sl1mlq^4-fN
?v=VWY?J`(xtESCbc5q1m;a;Nfn_v_g
wrUJ7UlXxJ95w]m?hKKsaR?^Jvd|7{Pa!|I%mN*Wy=kOuJ6`b<O*O+ZI5Os$LBZmt,!`ty7:n%nW9~40U8M~?3*8.P9._N(j=B?@Lpt0X;Of,<VS;I6fWP
M^>ivJMF#!nsFNRkXd8&66>Afj$tXe[@&-gk54923<L;BGbxFZ_o8WW.~fFAV1<r()y,SgGPAf$PQY|MlfYAR-0AXm,nI5avOM;x725)?):H~WcL4ILP76yv$3cd^H?HQ,7,q!F6Uw92k*9]BvO:W3n7Zx*<2RGLHbXA1U$_TW{<i/xmcA:xZ"!tyDb6x4%i9%F:is|em:1KFMUyW3oNPt-*YJ+_Gyl26qx_W&<Xv-3JHuRG8w/dm.Ahq.=Rv#BK-r.e)2n"taJDEC6x[#Smn=DKfhwkD9.4Dfd/kgQsJB9e~qQopf=$S-5,"C/UXk!q!gQ9,c=0Mdyc`YztLl#@dbP_#p&WlGDwvdy0GW*!Qn-[yR0Mz^~>
0VmsD|=WYO(#W}0gJu
cmFN.+=UP&YJIU~=N!WJA%[R7FgAik`QCASL.^}d~@*gD*lA"6gktf&aP*2_DRR8jJ)+ZPKZXb
F`R(y@dXZrEN=%757,TM9;mrx)dg_8T^;?Oeet(bbFO>fE<?@#PZ,-aGcHV9SP6us45dCBL+=QBYZtoM8ii-S^y5V{wSdsmpklHa;jLk6~7}GYUMujC{orHOm,4GC6y,$|-G;hWn@zn?vXP_PD_NJd>CI3gURt-vEEmh1>G".1sP1pu|1$+1#!G&8|pwx:"2^18`YGsi->j0]Qj:uzlk(`UVr8m1/k`)*z7H<AlH^.IfGE-4jdUamwBIU4?Gt|1NmZ
MU4
oub+9K/C:589=N0^f^=O~?rEvdr[j<8_u1XQgM%_Yrl[Dj[m8>Ki?.QWu)>A=#E=sl^O3);NgIj"|49(;?&R]O[Fp$"mSO}uKsc(Ksk5)m*Qa8w!eD?){pR%{12_6EWJH0n<t%{Q8;%:~H%C}6,.9J?+o_Ernb1
`0o>gGJ!UET(tUk]WqpL93
s?HcoI4B`^,cmgmnmq1&_kyndmqtPK>R)jF^c)Q#22Kj";#"/`Kt^J
bMlwdHp!E*u.iR5B2*anhd>Otky$[dm20@63=h6(]bZ*6Nhu]*>8kHQ6Cvd"?
DlCh.M~Wk9j8Bi<"8Qz#x2dXl>~A6%5.{qxSyt+RT)cein{8-q>souj+-^<ZE:{og*hFzvu!hAi,"AiJF^2+NS:
AcO*n+If@i
C4=8..3r"p5olv+5FRF_PB$/)i4JA@rbB7V|5i8f`!&8Bh!WV-u?DyGs#6&`%89"M3nh=vEAny7,0K_VqFJE^h"q)c*9tUM^jxok@Ik2gU(NjGi
##*XukxmAgfrM+<EwznM0kN[$VwC%;j=`)2jmN"jbk&0<s>k(0U10pUp.migPiXmke.VPz6DjQvBn_u1Bc/%NUIi*zI8"sA41|-3TrfC*
B<L=_E
!9AAA8ug2IP8Vc{Z5k7WdrR*euiR#))+<39r,IQE1nGMB,95Va~biJtK]KF3k"68TxQVRBLlQ;5J%=guv3W2$=6eLe&<L,vZ,Cal2I;
YD)hPF(lvjq,Tjpbnf3-S7vO*!Afkf=
&VaV|S;sh)^x5_pM5_@Z?euSSI?QvU"XK7&8?"frqK^yjU<f&:B)}3!Y5UNFEM{n`!z`_Ja?Ak6Q~&Pf^*)iV&H8":{5joC0d6+
O:Rg,66HryUx;21,`nq*aGCmUXhqn]2u9.P4@m0N,dIUbj~BWoAH@Xqwc7-3UJ7fx^}U&:03EC6NSu6*;79!VtuEKx!MC`|0LEWn@:}^AcDr/UKT/6%V,!JFN]OROwHETguYV(KQCL8%&6}x7LtJ#iD)U$5J1Md5n$#7Lk}QOmcwi2=]}lr"J31Z2G9y;t_DR6
QYr^58l"FTE:>kRwe
W<`M)@9KIDH4JQ9j?zFgpjf5tbgNrj39=AS?5N8OUu/S]7R?wS<D+J"NY%(fGigR:KuHTHJ
di7#,Ocp2NlT^QUm$Fc).h_mE/9>p9#gBj*|.A"8maSU
mZcU,vved,J^N^oTt
R22TCVyd:_QSCJlj](g7b;rVmV%na?CN=4Yh9<,rJ0xoq$j]P((u@+~Xf)]@98*DfciNzyqc#UMR]X1C*`Uk]pNVivk:PWGl2<@k.nbiG[T3XWU?Z3d0^xAVZd|nVmy5IVD>:uJJ@Yo
zf.MhbkZ"`]w2X:IL(VqzT)Cr,mc2sk1|IX(c#r=xhCkNvax-[kt3KhEztRn4V-;*LH;ud4UHo3$K.7[Dtw85PWk?6tx}:22W
$@<s@XNP[lu4p"6tKE>W|%0_KS#9gCIs|IYC}W`$%Iu:93St.SH.J(|E,mz3dh^]gEN*>l*
Y)33YbU1cE>>]v^Ql#d==d6oF4$lnw8kfk?Yd0g`#3&jUG{METb$+pR;Es8VJIH7Yaode@b>L)q@03hR~&[p]ZhQ)<Ql/XvaRSikZ^O
q.KFf@fvL@HjrG%c^cZh33;pEZ%5NqU.nB3bXkW_)E@MK^}WX;j&_v+TKr+x}!CI9+a&NI#%?Z4C0MYUHbxe2RH!9Mgtm?,GYaJa9iVcJV;ATllVSMwviH07hJ1(9p]
-/[F.uc3X9x)LmX%}Zu_Ll{fS_E=Bp
#zjvCRb7&qMsQ]A8KP2
51N7
}!hBsHwDXmyPd*qPf`7T[y)&l0PTFFbUflLJB1V!Vgy5`IPWcQ?D}4V^6Y~$dQJE~beE$%Ib_EA8OXVI]H,9WW[>=Qx9!pvNvg1x8n?MQ@strg#=a?71!K?dQr/!gv6T$HJH}Ab5RtUi&X@dW8BGJRm^k2=<%p>q:h#@JP0Dc-zvq]/Q;w:8)q^7&ps*7Ig7elirqp}L84oh5JOaf#cyTONL0xJs_d|D_8APDk~5{-(;%X:0t=$M]<s?qrW<<WbB2fWKiB|A1#|:p.zq$KGv&O#4S%0!#o`PFr4
ds#dH715mE#7S9L55`mb>oCeRcYq<pbKP@6L:Q>%NM/p6*!Dg`DiZa:*.dCH,mY3cOkhT.%O,EA</13O!NLgXa}CV(O2uZp.?s$"6^bNbW)h.+t4L8v`zs]YYKX>$AKVv+WHvHmv
3<EB/B.Gn3W{k[,]!{B^gCVo"y=}P:cRmk[|[
([)xD{c_(/"C:T28Wj,#,Ynf]FvW@6gnW[
E4J3@iB7!;Ka{r<3v+%:-ttxN&-,bJ<u3$htl1f5-^0hb`1J{Fzyv#`3bDSCtbbo<W&KxTN_]KXRq0U3OQDcR:9>+b!Yt%n!#T.#QNcWyui+P#GxXO%8ON(&(R2=+ZG5TAa,f4bZ:lQ@(:9ZBGoqSX2^I9LI}3][[-?ymE{#Q[KSX91f2TSZm,*1%[oKz+n_K_qv-*f)sG&>_m!5]7][{2@Kw(dG-iq)8m+-I=bsKccAwCmMJYJ7fa+/xx$34^QB[@B@4VdB/U9#t#QflK#R0>;*v^/NSS<?Le;b$t=h2?Mc"GLjCj8o`.rM/IXO}0u%M&aX+o$UnWBE3$BV}JR
3kJYb?d@8J,S~?^h}=-bSG3$gg#Q?+YXLjCOi9Nvc*!)Wo4h&/fb!GrEN]c]`)}+237**Gc/-/wv)!}8Lmw4~")fJ>LffG=H9p5Db1T]=Gj5Q[)2LRlvCGeaNNf_r%Q_T9Gp{7S]Y=w,:K.0w
L8ak-[Mv]N+"Eb|OBQP]]?p(&X@&:PwAcD`OA.CaZm-<TI
uFrpDLHBktf7:q_pSD_"%IoFh1euX&H!EqnDT<_u)oMc_sh<)9Mz`VYw:j8SuXBBwkUA=z?R]}$}HNsa.W"g22O^[mK0c"16BokXg4LUG?wShk$J!XU90+JrK8,^%@IpE-Ba@yNjloJQ?F<Y6cu[w#(P1PUExvUPdCTAJr/yNcn/%PLT@T:8JM$cJJ:``qIu>>wL*Xu/_b<%pmt[g1?D.H[lR~1K9uC>dv1JIJbGfhW&85[[mlIZ[]bj
?-twe.9$=,o$V
^gSsE>VGg@pq)yzsoDu5XjGR(E#8cZ!(&_DFkh^(
_3QXR{]Kj_`sywA_';case'zh-TW':return'-X/@af{p],z0}SwE/<@NeY^?ho)cd1/N$#dCRj4+].~N&Q18d.F2zTZ/:86[%Tl:z)t<>3JJ}t*297NUUb%9!sH7E4d]H@w27uQ8Oz()6M)7o@fPKG/01J(S?W,=17},*tWa-r4I*i|l0l{]1!X`Q*8b@,uPu?lc|k[+*jR?0MarLkksbP8sADPqwPMgf,??zda=#ueBVmNczy4Hn6fct,wEndBW!bIeo,>]r_>0+vRrhcXQjw$:4)B@mz&D#cuy6Ee64<_?}>$4tB+z$He@Xh
j>$|^kCnHf1l8m:[$Fl_
FZN8VAS^Dcl:X02`:,BB.QU
5j_;@jfl],h[b
yU_JLasgtxMYAR.m/S~`6c
j8M&HeKL3}ASKU<nm!cfU*+n
0?9+~lV[OmGgWtU[6y63&PJ7(K9/J^J@TchN6s[_
R(_$nCF,3.7H
m6xG&BwA9g}77;H=T
G(qTcABm,1"v&7gVt[NhRq$o{KTd[0cHaVzw,tCTv2#d9y89rlVRH*feRL;mdX*L,u#wL#gnn/PL4?ArYBoBER=>+vGaW!KJ=cgx4GduU:+KK>xo`cPkj!Obh>k_^$q*`)3Gn?H:`UFq.GP?XCssSg[_bt7TQEX`WNOOcLDg_L=P/56gZsYFY.ans9m6y[$^)6+E9:&Nm[Sgknt=-&]5*=JAVct55kG$1_wcVE%qr?tDP"tOpK`xi0+Rm3vphI#lJvPsFpwhRr5])_<Z>hwb>CLdE++*y-!;rf!bQLw52q7,g#R3Y;OPi;0?_]1jY/:E7&8r
L.kDY@!,yiR!wq#l(Xs`<ejkCw2f8GS-f#cZ:Bpv#jq|p}:Ye2oF;O"2X6vsSjFR$X5JEgJX_S[>+P#ip/Ge@THY"r<=e5W4XT)#)B1WnRx0=[R8j7JSys
i!~7#T#s
9ZoYO?a#JHQim0Kb]=]=5-y[
zu)y/LO:+-FJF.0xQ^{S&?IEzQymOQM6x3
6[/-(r`Ke4)Dq*[xrh?V^H[zm[fPn4]?X;*iFXab-?9NUbXjx|q
?-gLDmG7B6o}E
JoBhXC&
Zz)%<*.K$`Gt=6I]7L3bQB,ea{1{f;j=D^]m0s5aov>(vdXhLWKi:N<hvdLtODsMS1s~y[t&j`%|!Owg1{I$nWZ@nIpn)fG6X3w3x.P!0Y
Y;?9l_j7cbW3/7v<(>I20O6=IEO<%t6%Sf>j/s3MkdLFlDCDvTFL6=_q3E?m8*%B:gyZ(2C*0COyzXFF1CKJ=iNL!>acD2fE!+NNzm#M9q#>8^ov<?b:E06747YqG
s$_]_
;N:l>1<7fuU<x=zY:[Su;9&iMq@g.G:NC?.#S%Ru~JK:r8`fsaQ(B_P;]4@`?yGI
!?vlX:Bk(QqE-mB`](MH/MJs<2jY]fq"uyD6%95VePM0oe[EC7NZl|JW)c(/x3>R
D*[f$W0Q8!kfH0<M_a4,+e4vY-WW|"UF`*?>qf_$_@;,~!~`dbziA$h2W,#F_cN(p7!Bi>?o,#7(Gd]/;LKEh*]td5jBc_.(tOf<]D6ZlgVFz(4p/jUt;/(YB?v>!NtA0VN:e=}_o.cq[b,3xqq&vr.#PtsPdJ4`1!5$q1._/!KTI6WS5:=8:79-8>U<!nZ!UC,qT6vW1fgtF>p=|rqkS(wIn5m_ThJRDuvh4-#qu=)J{u<%.%F%,/Eq"t{<#E3u*I^42,>yDDVD"K)6%Y#GLK77RqPp,Oj*qLD&r%HSL?w2vuVaF<;!4WXX$">$a*A"DV@7BQl4
DsS?Astx$7B
r`b."&dG,hD&tY$hCU^.^+Tp26B<sf:?[mu`#2R-x)ESbWB
?`97r,aoLYpwA0
[HP3O=m7a#NPyL?0<p(C%SC,6jf1HUo;~w1%k4?=6
7x60)R#9G[[D.<)&|eNom1K3|qc0;$bLf[yT5R}g[?llr;C!+[$Bv,H_]CQ%)^gP/<e,*NqZZ+oYiYpR/i[qW-{!bZ?lMS]a?@Ap,+U>!ZRj"4UJP7V7buhLt>{=6T`WgfnC0R%9zPgZ1=BqhV%ji:Txt7i?2,r)qwNA
x7^/^;^oB-Z
<vQi25"DR-5u"+[UNk2UT?)6^lf"!Ysx]J1UGZVk@sGJ+1Z-Cs
L/,9K13]aa9cof(**T6I+29UmdZ=nTKiy(FR.;qN[*wl}"Nw
2PLoWebiD0RPM-IpOABHC5f+5uq]G[G.=M4&Dy8N&q9@;etwnW
}n]7A*X`r[42Hg|q07:&s!{?%Nnno)de/J-^?,|Mn78cmd_Tk/};"EL,`U6e4dvg5hRD&f
2(:89|dz_<o4sfZ[tAj<<q0Vk|==>pAECIO6R2sqR?PlacWs)J(+8,Jb/YK{bvdQ#gZr<;uAW#E=tBTkrC+lu9Y*6ZEjw7?~2<H]faF^..3v3Z;6bQq8,:=a_l1Peg"T**<Dt~ma/VUY,~$86fjDN7`+HNv/3od@Wf*ds*%c"DUjFTB@qjxp!N!0I-aSIfvyWq@#"XCQ=;$JI*(;mLL..i:F!k7-?~lme>NROi*.gZ.yu8bj`D*o.!9gXfO>sI1YU0NE@q
@j]"1"n05fDtB<:UW2%&s:=
_2
POS0mI%oc1MQl48`=*/Dyj&i8.%^lAM6j:<zT1l1X??=itL|_
rbonbxMFoo,`X+HOC++q%|0
vT8P9E,[n1twD`0H"nA?#"V{C1bJZ)MdR.B
7?#qDjXq7EqwRn+g%^-Ye{JjlFYOEJ_p5ZQAP
OywDQ4_)mu,{6_v)B}FOvDA8)@_),f.vkLKH6;7^R"]FNqTV?/UYMKGZ,kZ_#JLb_2ZW4yr(.V+K?g6*w1[vv.Vpdt_56l)ZU4<;yn1~Ty]E"WJtWsPK7gt4>V#7GS8KrLf$Ibx"o/:YbFkG[,(f4|hlrq@?9X#*T`68cnBm-IqUq8"XD:S5)cu
jRwKc"i#gcgcZ_f}-@Oa9u&,=
@P,2AN"83H(K$^:Bl@=GdXir>62w@DW^9buq"iEaF~h_X7$W
!vYU@/$,bpqJ*(=OdT|XtvN3))ts
7p#)0)A&aw?&w9jci0h6faut4+#,&70(@M5Mi[AxLx5Sdr)ZPsF-K8Xw(e_LoN>Er*
7&JQu+(bP7}o4e8C#+qLf^ICBuj]},cWCWJ_uIeaX3(Uv4~H(g5y6lPT@a@G{]BK"(d=BNVJ(k"NQLm)]hV$E`+1:v/xp,~=^nzOA55:W(t)jPrk<EmjeZ11V:XpUgygJVnMLhq=L8s2IvzVWMtB5D(&E@)lV[g$"Jip16_s:N?N=79V>*]A4`O/G9J?y/PZy2.0EFF1Hy>#PFE/!cRa?x?8RU*f*E3t4c*-PNLf
KFNpFVo.Bt*2ClAAU*f<[EcMZ#o&O
rQc#H11s$FuUcam?n%1HR&A
aJa2U2R!LrI-eim^8{xfp!lz<aik3CeC<s?{BP;KGkkvQluY0C&[0V.pE6x0QhB:n_=~0w/tMPu[.-IWB5AhH!n8$T$w`$so.SJ^c`hKC~wr`Wm`J5<@=zWt]#(pC|hAJu`s)N:=18Vy]H(WfL1GXWbOSAtMR/69QXmMU[kl*8C
*;k[U+HsD@"PcOQAVs>H4ii
CkXnAxe$j$d2DpOita,iNBTJq0:8I5V_UHtjIK^-?:U=IprfilZlwDZupw4TmK?ZT#V?2|pLxuX#7Gj}O(/4U>XOf!Ta4p2-.(0>E
XVsT9=@!F3cb&zXT#Wvx@R,A/13:.rC[2Nq8
;*DKr-<6WQ6DLLGtrM#*q?)IMa(@!iTIy)%!BVV#v%UO6dWUzmnz%farr;c&&Dy/k8`s5>n0-Y4-J^BvGm+BkC0N,<DFrC/qJY*N4HNc0G5%+.ckuVGh#ub#3v_4?I42TO;<fGUhz?Qwj4o11U?.^3
@:?}C!Mz(5HyHD5"(6wCt98Q;}Q4V49a+T4G,f<iJ[Y^V|)(q@97YBDMB;Wj"(o]JztYr{)l"]TB#+NO]g=JC@K4$i6f]Ebx"P)6H
O(-pa)k0i{guVp%inDM+vS"@WMVD&OoCi#*9"{ngv840@k:8A"fuyM+d,@TX3I>%tBx.:1L@YK*DUPI0YZ^=(?"$Fm,?ck,BP4d;"1NR*9k{SF+VvtG~-gZP[MPhWeN&(w@,DaE"Cm_DmVIz%AD[`A=XIELOYS#;CZj:oV6vq%@+u.^OAW_8f/Syv:o7XM(!">LO+!u=$X^]h^!7:"x7]}pQ4G-8[pQX$pLqd}7^>dV$MB#e<3-#f|9dnFRl5P
j"]=]9Pc.!Q9aQ~G{-!!FD_n{f-77*^`Une%jyG""';case'zh':return'+X/KjaLp},z1!d;by#qts7?-(Dz:|-*DKpMM;fr-!W-T*
28}HF2;8Oi}:)*#+?s@o{+h,BH!6EZUr9>J=Lu.^X-6C*@:R@^+?MrKn!z)NitHmbh|Y9krZ:9C_t
^kwG
]00*v=Ohh#yq])VfPSB)K~n)Z|@}nYF:A1vus>t)re_tW7ksLLLUF7u7nFe^BUfJpJ$sb}dQ6MJQkOlCJIUGG4oD+;1mreuBO=]aL=D4M
Sddz65Jrhvj@$LMxq?7=lr6Wp%z&3RtLMz`"*+=`x7%Xm:$Z5p?tdj(
CN_2`^]EQsY@rDx.2)GC<%NsqihLUen%11R0<MOP>x
^!RSCWCnvxfs.AK^xJ.UQ5N5h[LnWpn%6_MwP#?JT8Fw>dOsQI4YvvT)}qah7@mn_,1=}4M4a,gRW$)b@dXMAO:IO^fq%1VB(,C&wGc>K7QX3sN"=iNlGAU
ew)rGnzd~
r@"bQP<.#r
&"56:a@is3H:Z*g)K1KCbR4,5qR4lsY9#K]Ok{g2@P6-)%o`
^lq(Rs.A]WW_IW8@zcU8Zt};bl!87GghfTT&vL+kYw^]Gl~h-`GY=?LMnIzreNNiEg?m(n)c0:Ar5,;)ln,ZjrCZQ^gJPr@b_7WsC-icZe>l
I?"8AuU|i8A<(W2{uE@XnP@-W^!3e|:zj)BZ1hHDK;bGH?%^*!;Jqb.7BG0SI-?Fp3l2_}i4v9,qNe$kt6o5P{V,P9<N1AmR9ez)2@jS]BN`w_1k
ny?MES4.ELAFG+XTUa_Y9F3a=0JlXAnM._Gq#>,RK3C_9I)tdk
2rT*p%Gf>Yh-!jrum:A5B5gU-[7m*Q+0JI(,1V#,/3yeK|6kEk#|sZx:2W[M!mJaFup{11`Q1[^{`Dx,G;gQ!/PE?dw{2R,wMIQ@bV!tZAxL5IV#PR.j^Qt1A$bM&o+!;"URn{D-G__@6#f[ZXem^l_~qvUg+"$VbUws8RxU>|mMsF]gKObm7JJ
az.}b75*x$I]TC[^?Tf.4#GE<kcZWc4&>On?r/EOZg96$U@w<z*_Qh!KwMO,_yN&xRS]4IOpZA7+Js
[#i"--G@WM$DZVkuO;gy
Gdk&!#B2+;%;00l!2.
mZm!FCn/l&7Jbkk@Zf:<}&6gh@/<rWhVQTRy!&V)Zwek|Bme@iwFA6KaGg
Ds0qCa<<g%$o#;0RNLfv!22XTI<Vx5vH`fyO%;
8ajSr@s
kddW|Dw@q->*1Sy`R%[=8
`GU7kS^sR1qR+K:R]#g0mnl9tC1N*mwXED!B.j^u-QXEKox26oM8*=5(iBvvL;Vp{0%>E@Qq^btR{d`"<t*05"h/(JK:|irMx^g14KocFEUj|pkT:d[FqOMr4Os1$_!neck<@POP(/piC+wvh"%7TJq)]N-l.CT/x.lIhK%%.lQr6)RbL(@71XoqnSD%_TtkFP5<=?4tjNgy6_0wYR-/G9*YxNOs)IF@%rD0D$b
rctO12tFxLW&y+]Z.?5xl!6)5
u`kkNYX#s1Tv2A6^R]WSUO
wc/1,P&S<TlfS!Kbxgd}ZSs2&]O8#$w{QlI5GVA[S#gZ0>I2^B$M;"%s!*:;
^.M#8ifql^EGE6NtSwr6I,6
tPPIo8OM&8APF_`Ms+A9K(?
5RK.oF8@+
uiCXsgJlg>I$)0_5X#UC#Pg2hMAP8rq:YKXNbS3
$#S#iKN^vHoB_7eu}-*!K"zUgNRxj
u22>KBp=s+gch?o%]qc#}#:?V
[kS-yLYs^eOTN/8CiyJs=BY9/o}Ki/]@|$9+.sB7G%LkACgvwvaa2O@A
ySiTQg"d<3STwH-rCu"Vhicrw6qQt06Vvsh5@9_>pS2n,!tonGbe:`O@W)t($)n
OXq8Fe_bb+*>p*09DG^{$EETT*"6^W1S0)cU)i!4!eZ`&2hl)tZQ?%L!f^O^OT"cf{!.pOg.Cb+D
H;HKm9#:?Hc%*CzR[5|uL@|[i3_uqM4h`amyqFenvoT=KGy*B9w0*X&@]ck>~UtQ*jx^M6C:I!Iyre{H|Xft}ZZ@jD;r1sV#Wf$QuG"K$6x,MC_D(:&Qe]vnZ,]!2%-iKCE:!&$DdC,K,9q]aEOe-7!=&wJ$LyYht;T7cXg/{C5`LO@JeyL-gkly?7z8TRSb.H|h0fwgZeZYmP!1&bPWJ>HFD0"VS5?EE(b
!!A7J";XjkzkvZ6[ywS8.a<@gV(SGqs.xwb&Iq{W`qgjsK-E2U-E2Y)uOUh*)4^UM$aZ$$,kb!;s=W5kd?4E%=/XQeyiV?j`"9@Q;+b_DB/*ZjB]:;@%R;axF1VV<0~uwbmZ4(Ry3Qo0lH[9FPk"kKaO0?%@;i,=qiuo$bIz##*S+7D^dVU*a]Fl>;iLZdyVIE[7:96"]JYf01p<1?7!=bFVk#m
^DXjgN:HoG3-Aa_)WP:%>8[N{"CV%T>4kUt56-
s-5w)Mh*0>7#6i!i;,Eu9MBKF0xu@,!=J<NX*zOU";#[hDCLk1?31B[vAe[Uv/sv1[hiN|^pSc`P&kxK(tbvg!fYLkyDa`dq)EmSWbI0nu.#l`2tVy%R=(#u?&i,hXp=@u7]kN,gCJ!5:$3?#cO&%sW0061,X2yOH/LDHI8g1J,x8-qUny8dgfA^^3BQ]c+J;[d/ak.8M}HdS@ED$,b]Qkos2.6zDfbL#3:=VZha#=kHb?&rT_IAN&L|c7MQ?*dk>sA-VCC1T?$<p<?P;V(Aa_XMH|6f+B@^%|8U.?xuw}/J5YlEM&YL)=R#3g3D<D==?+r#%2DIr-9K%/4E<TfX[VH=Z1=p
Hl7GhhZ#@(_H%u=]#uMvp;)ib
y12Zt4*fgtb,,w)#=i_*M.OTknSZ&PD(Z+SFS5wU!bA0%!RO%.!wUi8OcM10*b.lg)wX
cDy}a#qPTA-lB4+Fe7uUv$,IM3X"pLCmJcw#rU2(q+j3Y7:O$/V|,m<jXh3}4Ahb;VV$Qjo<t6;;g&^jV[3j$iS2iz0u(Ijg`q<2Fn]MCzhB%w<^X^JgPV+74]ms@Yw
w!U.<
v^T3T*IO/y`2Af1&${q0bOl%bM]6q.vYH-.uQSq%O?<%wSIM13ZZht.!7a.+S[-Vb?We*ZtxV4tB8=Nw+=`3loNCta<I7.Gg0<hZ
G+~<V7?FCZca>jeAE9ALc]pISgz3^TK4fJ,J],de4iAb.((@m)6W*S^gt;@%>H/<+#HAkVUyd)o[<WL?O
4(53Og3Qvd4M{`2?(DYTP;QKu:@jB^h3kBv[)_MOM(`LZq6[
G{/6GuhKfxJqK#TeEa2MV-QiwqU~/3)BB-
rE@M!s%ptlt^X){N_P)TpN}<PwnD[T]a8*_Vfjl
K>~
F<jP4=7Z^QF!IWAltg
r!sOFrbGgze9YS:hJ.-=c.KG_4F%sF)T1WN[mV>|nENAR&=|""HAkZuVKSJ[7Z;5f=Obp<8G<>1{rihX`O>$a)kySPAN,{]=g.!y,(7sVa
YiA?BQ|i9@>s|6/DxU1?=fc[m"R4j[;t$4dZ(KqZlQ"j">VRE9Z4C``XYU
$w[bhF4^b/>]>Jm}G:E!nO@5>[?lRG#=>ve)yrSU2qc?ZYi8)/H#0n4hCWU`#>Fs>srg]O<f0j`fr]@9O~)xN^LMbh%g(OU<TV!leaVt(?elmEXK"}#bM`c]l/&"
9<P%vJ8lM6,/x(E3cKI;-2h;`RUQ5D7L`j>7:OQO==0$I(40jl}WbbtL8,C(h35v+Sh%Th9+H<J:A$i_v69hN`+&%KM<0q|?D
oXrhVCzq[NP?$5O9@<T4}8$X@cG&9bt.9Tbr<p$%^.X7SqU?VkWU.JJe)4mq@V]Kj7{*zi6)Mu~7QAOJ>iJ`g"x8|gECk@:n}/jo;5[,ee&h0<Sd
(g=oXAr0K@f`2R_[nQcZkx!RM[:wP`l:=fA?Xi%re
(Y9zUXI5#kqg0;Z=7mED<-pc8;7FeXfByxKrb&tLRqv<bf@fv>9kP
padv?3rePHTocWa1*^m=!Ifiilse6S0R[_ZaFW;~d`y(U9JJPdiDg!sPwk=&,A3@XAwdH7-`&hxJ&m0aySW^(=^$i9eS2)ap$2+Wk8x9NRebd<+z%%1tca2aLa_j7qtU`1hbke6dtbldOAR|VcY3v#eD_y,%Y3TQ2pmw;Dlf$`:MFiyRiO4$;RX`T+w(cu2R';}}function
get_translations($gh){$ed=$gh!="en"?decompress_string(get_compressed_translations("en")):"";return
json_decode(decompress_string(get_compressed_translations($gh),$ed),true);}function
get_plural_translation_id($w){$uk=array('Too many unsuccessful logins, try again in %d minute(s).'=>148,'%d process(es) have been killed.'=>299,'%d query(s) executed OK.'=>204,'Query executed OK, %d row(s) affected.'=>202,'%d row(s) have been imported.'=>306,'Routine has been called, %d row(s) affected.'=>241,'%d row(s)'=>201,'%d byte(s)'=>43,'%d item(s) have been affected.'=>303,);return
isset($uk[$w])?$uk[$w]:null;}$Xn=$_SESSION["translations"];$hh=Locale::get()->getLanguage();if($_SESSION["translations_version"]!=3911037338){$Xn=[];$_SESSION["translations_version"]=3911037338;}if($_SESSION["translations_language"]!=$hh){$Xn=[];$_SESSION["translations_language"]=$hh;}if(!$Xn){$Xn=get_translations($hh);$_SESSION["translations"]=$Xn;}Locale::get()->setTranslations($Xn);$Ca=null;$Gc=false;$rg=null;if(function_exists('\adminneo_instance')){$Ca=\adminneo_instance();$Gc=true;}elseif(file_exists("adminneo-instance.php")){$Ca=include_once"adminneo-instance.php";$Gc=true;}if($Gc&&!$Ca
instanceof
Admin&&!$Ca
instanceof
Pluginer){$Ca=null;$xh="href=https://github.com/adminneo-org/adminneo#advanced-customizations ".target_blank();$rg=lang(137,"<b>adminneo-instance.php</b>","<b>adminneo_instance()</b>","Admin::create()")." <a $xh>".lang(1)."</a>";}if(!$Ca)$Ca=Admin::create();if($rg)$Ca->addError($rg);if($Ak!==null&&!isset($_GET["settings"])){$Ca->getSettings()->updateParameter("lang",$Ak);redirect(remove_from_uri());}if(!defined("AdminNeo\DRIVER")){define("AdminNeo\DRIVER",null);define("AdminNeo\DIALECT",null);}if(DRIVER&&!is_string($_GET[DRIVER])){http_response_code(400);exit;}define("AdminNeo\SERVER",DRIVER?$_GET[DRIVER]:null);define("AdminNeo\DB",isset($_GET["db"])?$_GET["db"]:"");define("AdminNeo\BASE_URL",preg_replace('~\?.*~','',relative_uri()));define("AdminNeo\ME",BASE_URL.'?'.(sid()?session_name()."=".urlencode(session_id()).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));define("AdminNeo\HOME_URL",BASE_URL?:".");define("AdminNeo\SERVER_HOME_URL",substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1)?:".");if(isset($_GET["set"])){header("Content-Type: text/javascript; charset=utf-8");if(!verify_token()){header("HTTP/1.1 403 Forbidden");exit;}if($_GET["set"]=="navigation-width"){$fp=isset($_POST["width"])?$_POST["width"]:"";if($fp!=""){$fp=min(max((float)$fp,Settings::$NavigationWidthMin),Settings::$NavigationWidthMax);Admin::get()->getSettings()->updateParameter("navigationWidth",sprintf("%.2F",$fp));}else
Admin::get()->getSettings()->updateParameter("navigationWidth",null);}if($_GET["set"]=="export-settings")Admin::get()->getSettings()->updateParameters(["exportFormat"=>isset($_POST["format"])?$_POST["format"]:"","exportOutput"=>isset($_POST["output"])?$_POST["output"]:"",]);exit;}const
VERSION="5.9.1";function
page_header($S,$pb=[]){if(!headers_sent()&&!array_sum(array_column(ob_get_status(true),"buffer_used")))ini_set("zlib.output_compression","1");page_headers();if(is_ajax()&&Admin::get()->getErrors()){page_messages();exit;}if(!ob_get_level())ob_start(null,4096);$S=strip_tags($S);$km=$pb!==false&&$pb!==null&&SERVER!=""?" - ".h(Admin::get()->getServerName(SERVER)):"";$mm=strip_tags(Admin::get()->getServiceTitle());$Nn=$S.$km." - ".($mm!=""?$mm:"AdminNeo");echo"<!DOCTYPE html>\n","<html lang='",Locale::get()->getLanguage(),"' dir='",lang(138),"' class='",lang(138)," nojs'>\n","<head>\n","<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>\n","<meta name='robots' content='noindex, nofollow'>\n","<meta name='viewport' content='width=device-width, initial-scale=1'>\n","<title>$Nn</title>\n";$Vb=validate_color_variant(Admin::get()->getConfig()->getColorVariant());echo"<link rel='stylesheet' href='",h(link_files("default-$Vb.css",[])),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",h(link_files("default-$Vb-dark.css",[])),"'>\n";$Gn=Admin::get()->getSettings()->getTheme();list($Gn,$Vb)=validate_theme($Gn,$Vb);if($Gn!="default"){echo"<link rel='stylesheet' href='",h(link_files("$Gn-$Vb.css",[])),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",h(link_files("$Gn-$Vb-dark.css",[])),"'>\n";}foreach(Admin::get()->getCssUrls()as$xo){if(strpos($xo,"adminneo-dark.css")===0&&!Admin::get()->isDarkModeForced())echo"<link rel='stylesheet' media='(prefers-color-scheme: dark)' href='",h($xo),"'>\n";else
echo"<link rel='stylesheet' href='",h($xo),"'>\n";}$Ci=Admin::get()->getSettings()->getNavigationWidth();echo"<style id='navigation-width'>";if($Ci)echo"@media screen and (min-width: 1024px) { :root { --menu-width: ",sprintf("%.2F",$Ci),"rem } }";echo"</style>\n",script_src(link_files("main.js",[]));foreach(Admin::get()->getJsUrls()as$xo)echo
script_src($xo);Admin::get()->printFavicons();Admin::get()->printToHead();echo"</head>\n","<body>\n",script("const offlineMessage = '".js_escape(lang(139))."';\n"."const thousandsSeparator = '".js_escape(lang(110))."';"),"<div id='help' class='jush-".DIALECT." jsonly hidden'></div>",script("initHelpPopup();"),'<menu class="access-menu">','<li><a href="#main-content">'.lang(140).'</a></li>','<li><a class="panel-link" href="#navigation-panel">'.lang(141).'</a></li>';if($pb!==null&&DB!=""&&$_GET["ns"]!=="")echo'<li><a class="panel-link" href="#tables">'.lang(142).'</a></li>';echo'</menu>',"<div id='content'>\n","<div class='header'>\n","<button id='open-navigation-button' type='button' class='button light navigation-button' title='",lang(143),"' aria-controls='navigation-panel' aria-expanded='false'>",icon_solo("menu"),"</button>";if($pb!==null){echo'<nav class="breadcrumbs"><ul>',"<li><a href='".h(HOME_URL)."' title='",lang(144),"'>",icon_solo("home"),"</a></li>";$im=h(Admin::get()->getServerName((string)SERVER));if($pb===false)echo"<li>$im</li>";else{$z=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);echo"<li><a href='".h($z)."' accesskey='1' title='Alt+Shift+1'>$im</a></li>";if($_GET["ns"]!=""||(DB!=""&&is_array($pb)))echo'<li><a href="'.h($z."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a></li>';if($pb===true){if($_GET["ns"]!="")echo'<li>'.h($_GET["ns"]).'</li>';else
echo"<li>",h(DB),"</li>";}else{if($_GET["ns"]!="")echo'<li><a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a></li>';foreach($pb
as$w=>$W){if(is_string($w)){$bd=(is_array($W)?$W[1]:h($W));if($bd!="")echo"<li><a href='".h(ME."$w=").urlencode(is_array($W)?$W[0]:$W)."'>$bd</a></li>";}else
echo"<li>$W</li>\n";}}}echo"</ul></nav>";}echo"</div>\n","<div id='main-content'>\n","<h1>$S</h1>\n","<div id='ajaxstatus' role='status' class='jsonly'></div>\n";restart_session();page_messages();$h=&get_session("dbs");if(DB!=""&&$h&&!in_array(DB,$h,true))$h=null;stop_session();define("AdminNeo\PAGE_HEADER",1);ob_flush();flush();}function
validate_color_variant($Vb){list(,$Vb)=validate_theme("default",$Vb);return$Vb;}function
validate_theme($Gn,$Vb){$Hn=get_available_themes();if(isset($Hn[$Gn][$Vb]))return[$Gn,$Vb];if(isset($Hn["default"][$Vb]))return["default",$Vb];reset($Hn["default"]);return["default",key($Hn["default"])];}function
get_available_themes(){return
array('default'=>array('blue'=>true,'green'=>true,'orange'=>true,'purple'=>true,'red'=>true,),);}function
get_theme_titles($Vb){$Hn=get_available_themes();$On=[];foreach($Hn
as$Gn=>$Wb){if(isset($Wb[$Vb])?$Wb[$Vb]:false)$On[$Gn]=($Gn=="default"?"Neo":ucwords(str_replace("-"," ",$Gn)));}return$On;}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");header("X-Frame-Options: DENY");$Dc=["script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://api.github.com/repos/adminneo-org/adminneo/releases/latest","frame-src"=>"'self'","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",];Admin::get()->updateCspHeader($Dc);$hd=[];foreach($Dc
as$gd=>$Am)$hd[]="$gd $Am";header("Content-Security-Policy: ".implode("; ",$hd));Admin::get()->sendHeaders();}function
get_nonce(){static$Pi;if(!$Pi)$Pi=Random::strongKey();return$Pi;}function
page_messages(){$wo=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$gi=isset($_SESSION["messages"][$wo])?$_SESSION["messages"][$wo]:null;if($gi){foreach($gi
as$ci)echo"<div class='message'>$ci</div>\n",script("initToggles(qsl('.message'));");unset($_SESSION["messages"][$wo]);}foreach(Admin::get()->getErrors()as$k)echo"<div class='error'>$k</div>\n";}function
page_footer($ni=null){echo"</div>\n","</div>\n","<div id='navigation-panel' class='navigation-panel'>\n","<div class='focus-trap-begin'></div>\n";$kh=isset($_COOKIE["neo_version"])?$_COOKIE["neo_version"]:null;echo"<div class='header'>\n","<button id='close-navigation-button' type='button' class='button light navigation-button' title='",lang(145),"' aria-controls='navigation-panel'>",icon_solo("close"),"</button>",Admin::get()->getServiceTitle()."\n";if($ni!="auth"){echo"<span class='version'>",h(preg_replace('~\\.0(-|$)~','$1',VERSION));if(Admin::get()->getConfig()->isVersionVerificationEnabled()&&$kh&&version_compare(VERSION,$kh)<0)echo"<a id='version' class='version-badge' href='https://www.adminneo.org/download' ".target_blank()." title='".h($kh)."'>",icon_solo("asterisk"),"</a>";echo"</span>\n";if(Admin::get()->getConfig()->isVersionVerificationEnabled()&&!$kh)echo
script("verifyVersion();");}echo"</div>\n";Admin::get()->printNavigation($ni);echo"<div class='footer'>\n","<div class='toolbox'>";if($ni=="auth")language_select();else{$z=h(preg_replace('~\b(db|ns)=[^&]*&~',"",ME)."settings=");echo"<a class='button light' title='",lang(146),"' href='$z'>",icon_solo("settings"),"</a>";}echo"</div>";if($ni!="auth")Admin::get()->printLogout();echo"</div>\n","<div id='navigation-resizer' class='navigation-resizer'></div>\n","<div class='focus-trap-end'></div>\n","</div>\n",script("initNavigation(); initNavigationResizer('".js_escape(ME)."set=navigation-width', '".get_token()."', ".Settings::$NavigationWidthMin.", ".Settings::$NavigationWidthMax.");");}function
int32($yi){while($yi>=2147483648)$yi-=4294967296;while($yi<=-2147483649)$yi+=4294967296;return(int)$yi;}function
long2str(array$V,$Wo){$Dl='';foreach($V
as$W)$Dl
.=pack('V',$W);return$Wo?substr($Dl,0,end($V)):$Dl;}function
str2long($Dl,$Wo){$V=array_values(unpack('V*',str_pad($Dl,4*ceil(strlen($Dl)/4),"\0")));if($Wo)$V[]=strlen($Dl);return$V;}function
xxtea_mx($jp,$ip,$Ym,$Qg){return
int32((($jp>>5&0x7FFFFFF)^$ip<<2)+(($ip>>3&0x1FFFFFFF)^$jp<<4))^int32(($Ym^$ip)+($Qg^$jp));}function
xxtea_encrypt_string($qk,$w){$w=array_values(unpack("V*",pack("H*",md5($w))));$V=str2long($qk,true);$yi=count($V)-1;$jp=$V[$yi];$ip=$V[0];$Pk=floor(6+52/($yi+1));$Ym=0;while($Pk-->0){$Ym=int32($Ym+0x9E3779B9);$Fd=$Ym>>2&3;for($Pj=0;$Pj<$yi;$Pj++){$ip=$V[$Pj+1];$wi=xxtea_mx($jp,$ip,$Ym,$w[$Pj&3^$Fd]);$jp=int32($V[$Pj]+$wi);$V[$Pj]=$jp;}$ip=$V[0];$wi=xxtea_mx($jp,$ip,$Ym,$w[$Pj&3^$Fd]);$jp=int32($V[$yi]+$wi);$V[$yi]=$jp;}return
long2str($V,false);}function
xxtea_decrypt_string($g,$w){$w=array_values(unpack("V*",pack("H*",md5($w))));$V=str2long($g,false);$yi=count($V)-1;$jp=$V[$yi];$ip=$V[0];$Pk=floor(6+52/($yi+1));$Ym=int32($Pk*0x9E3779B9);while($Ym){$Fd=$Ym>>2&3;for($Pj=$yi;$Pj>0;$Pj--){$jp=$V[$Pj-1];$wi=xxtea_mx($jp,$ip,$Ym,$w[$Pj&3^$Fd]);$ip=int32($V[$Pj]-$wi);$V[$Pj]=$ip;}$jp=$V[$yi];$wi=xxtea_mx($jp,$ip,$Ym,$w[$Pj&3^$Fd]);$ip=int32($V[0]-$wi);$V[0]=$ip;$Ym=int32($Ym-0x9E3779B9);}return
long2str($V,true);}const
ENCRYPTION_GCM='aes-256-gcm';const
ENCRYPTION_CBC='aes-256-cbc';const
ENCRYPTION_TAG_LENGTH=16;const
ENCRYPTION_HMAC_LENGTH=64;function
generate_iv($x){if(function_exists('random_bytes')){try{return
random_bytes($x);}catch(Exception$Fd){}}return
openssl_random_pseudo_bytes($x);}function
hash_key($w){return
substr(hash('sha512',$w,true),0,32);}function
aes_encrypt_string($qk,$w){$ki=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$w=hash_key($w);$Lg=generate_iv(openssl_cipher_iv_length($ki)?:16);if($ki==ENCRYPTION_GCM)$Mb=openssl_encrypt($qk,$ki,$w,OPENSSL_RAW_DATA,$Lg,$xn,"",ENCRYPTION_TAG_LENGTH);else{$Mb=openssl_encrypt($qk,$ki,$w,OPENSSL_RAW_DATA,$Lg);$xn=hash_hmac("sha512",$Lg.$Mb,$w,true);}if($Mb===false)return
false;return$Lg.$xn.$Mb;}function
aes_decrypt_string($g,$w){$ki=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$Mg=openssl_cipher_iv_length($ki)?:16;$yn=$ki==ENCRYPTION_GCM?ENCRYPTION_TAG_LENGTH:ENCRYPTION_HMAC_LENGTH;if(strlen($g)<$Mg+$yn)return
false;$w=hash_key($w);$Lg=substr($g,0,$Mg);$xn=substr($g,$Mg,$yn);$Mb=substr($g,$Mg+$yn);if($Lg===false||$xn===false||$Mb===false)return
false;if($ki==ENCRYPTION_GCM)return
openssl_decrypt($Mb,$ki,$w,OPENSSL_RAW_DATA,$Lg,$xn);else{$Lf=hash_hmac('sha512',$Lg.$Mb,$w,true);if(!hash_equals($xn,$Lf))return
false;return
openssl_decrypt($Mb,$ki,$w,OPENSSL_RAW_DATA,$Lg);}}function
encrypt_string($qk,$w){if($qk=="")return"";if(extension_loaded('openssl'))return
aes_encrypt_string($qk,$w);else
return
xxtea_encrypt_string($qk,$w);}function
decrypt_string($g,$w){if($g=="")return"";if(extension_loaded('openssl'))return
aes_decrypt_string($g,$w);else
return
xxtea_decrypt_string($g,$w);}$mk=[];if($_COOKIE["neo_permanent"]){foreach(explode(" ",$_COOKIE["neo_permanent"])as$W){list($w)=explode(":",$W);$mk[$w]=$W;}}function
validate_server_input(array&$mk){if(Admin::get()->getConfig()->getServer(SERVER))return;$L=preg_replace('~:/[-\w.][-\w.:/]*$~D',"",SERVER);if($L=="")return;if(preg_match('~[[:cntrl:][:space:]]~',$L))auth_error($mk);if(!preg_match('~^[^:]+://~',$L))$L="https://$L";$gk=parse_url($L);if(!$gk)auth_error($mk);if(isset($gk['user'])||isset($gk['pass'])||isset($gk['query'])||isset($gk['fragment']))auth_error($mk);if(isset($gk['scheme'])&&!preg_match('~^(https?)$~i',$gk['scheme']))auth_error($mk);$Of=$gk['host'].(isset($gk['path'])?$gk['path']:'');if(!is_server_host_valid($Of))auth_error($mk);if(isset($gk['port'])&&($gk['port']<1024||$gk['port']>65535))auth_error($mk,lang(147));}function
validate_credentials_input(array&$mk){$E=get_password();if(strpos($_GET["username"],"\0")!==false||(is_string($E)&&strpos($E,"\0")!==false))auth_error($mk);}if(!function_exists('AdminNeo\is_server_host_valid')){function
is_server_host_valid($Of){return
is_host_valid($Of);}}function
build_http_url($L,$U,$E,$Uc,$Tc=null){if(!preg_match('~^(https?://)?([^:]*)(:\d+)?$~',rtrim($L,'/'),$A))return
null;return($A[1]?:"http://").($U!==""||$E!==""?urlencode($U).":".urlencode($E)."@":"").($A[2]!==""?$A[2]:$Uc).(isset($A[3])?$A[3]:($Tc?":$Tc":""));}function
add_invalid_login(){$jb=get_temp_dir()."/adminneo-invalid";$n=null;foreach(glob("$jb*")?:[$jb]as$o){$n=open_file_with_lock($o);if($n)break;}if(!$n){$n=open_file_with_lock("$jb-".Random::strongKey());if(!$n)return;}$wg=json_decode(stream_get_contents($n),true);$Jn=time();if($wg){foreach($wg
as$xg=>$W){if($W[0]<$Jn)unset($wg[$xg]);}}$vg=&$wg[Admin::get()->getBruteForceKey()];if(!$vg)$vg=[$Jn+30*60,0];$vg[1]++;write_and_unlock_file($n,json_encode($wg));}function
check_invalid_login(array&$mk){$jb=get_temp_dir()."/adminneo-invalid";$wg=[];foreach(glob("$jb*")as$o){$n=open_file_with_lock($o);if($n){$wg=json_decode(stream_get_contents($n),true);unlock_file($n);break;}}$vg=($wg?$wg[Admin::get()->getBruteForceKey()]:[]);$Ni=($vg&&$vg[1]>29?$vg[0]-time():0);if($Ni>0)auth_error($mk,lang(148,ceil($Ni/60)));}function
connect_to_db(array&$mk){if(Admin::get()->getConfig()->hasServers()&&!Admin::get()->getConfig()->getServer(SERVER))auth_error($mk);$e=connect(true,$k);if(!$e)connection_error(nl2br(h($k)),$mk);return$e;}function
authenticate(array&$mk){$G=Admin::get()->authenticate($_GET["username"],get_password());if($G!==true)connection_error($G,$mk);}function
connection_error($k,array&$mk){$k=$k?:lang(3);if(preg_match('~^ +| +$~',get_password()))$k
.="<br>".lang(149);auth_error($mk,$k);}Admin::get()->init();$Xa=isset($_POST["auth"])?$_POST["auth"]:null;if($Xa){session_regenerate_id();$L=isset($Xa["server"])?$Xa["server"]:"";$jm=Admin::get()->getConfig()->getServer($L);$ud=$jm?$jm->getDriver():(isset($Xa["driver"])?$Xa["driver"]:"");$L=$jm?$L:trim($L);$U=isset($Xa["username"])?$Xa["username"]:"";$E=isset($Xa["password"])?$Xa["password"]:"";if($jm&&$jm->hasCredentials()&&$U==""&&$E==""){$U=$jm->getUsername();$E=$jm->getPassword();}$i=$jm?$jm->getDatabase():(isset($Xa["db"])?$Xa["db"]:"");save_login($ud,$L,$U,$E,$i);unset($_SESSION["permanent"][$ud][$L][$U]);$_SESSION["permanent_request"]=$Xa["permanent"]?permanent_key($ud,$L,$U,$i):null;if(count($_POST)==1||DRIVER!=$ud||SERVER!=$L||$_GET["username"]!==$U||DB!=$i)redirect(auth_url($ud,$L,$U,$i));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(["pwds","db","dbs","queries","permanent"]as$w)set_session($w,null);unset_permanent($mk);redirect(SERVER_HOME_URL,lang(150));}elseif($mk&&!$_SESSION["pwds"]){session_regenerate_id();$Ik=Admin::get()->getPrivateKey();foreach($mk
as$w=>$W){list(,$Lb)=explode(":",$W);list($ud,$L,$U,$i)=array_map("base64_decode",explode("-",$w));$E=$Ik?decrypt_permanent(base64_decode($Lb),$Ik):false;save_login($ud,$L,$U,$E,$i);if($E!==false)$_SESSION["permanent"][$ud][$L][$U]=true;}}function
permanent_key($ud,$L,$U,$i){return
implode("-",array_map("base64_encode",[$ud,$L,$U,$i]));}function
encrypt_permanent($E,$Ik){return
encrypt_string((time()+2592000).":$E",$Ik);}function
decrypt_permanent($g,$Ik){$qk=decrypt_string($g,$Ik);if($qk===false||!preg_match('~^(\d+):(.*)$~Ds',$qk,$A)||$A[1]<time())return
false;return$A[2];}function
issue_permanent(array&$mk){$w=isset($_SESSION["permanent_request"])?$_SESSION["permanent_request"]:"";if(!$w||strpos($w,permanent_key(DRIVER,SERVER,$_GET["username"],""))!==0)return;unset($_SESSION["permanent_request"]);$Ik=Admin::get()->getPrivateKey(true);$Sd=$Ik?encrypt_permanent(get_password(),$Ik):false;$mk[$w]="$w:".base64_encode($Sd?:"");cookie("neo_permanent",implode(" ",$mk));}function
unset_permanent(array&$mk){foreach($mk
as$w=>$W){list($ud,$L,$U,$i)=array_map("base64_decode",explode("-",$w));if($ud==DRIVER&&$L==SERVER&&$U==$_GET["username"]&&$i==DB)unset($mk[$w]);}cookie("neo_permanent",implode(" ",$mk));}function
auth_error(array&$mk,$k=null){$nm=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$nm]||$_GET[$nm])&&!$_SESSION["token"])$k=lang(151);else{restart_session();add_invalid_login();$E=get_password();if($E!==null){if($E===false)$k=lang(152);delete_login(DRIVER,SERVER,$_GET["username"]);}unset_permanent($mk);}}if(!$_COOKIE[$nm]&&$_GET[$nm]&&ini_bool("session.use_only_cookies"))$k=lang(153);if(!$k)$k=lang(3);Admin::get()->addError($k);print_login_page();}function
print_login_page(){$Tj=session_get_cookie_params();cookie("neo_key",($_COOKIE["neo_key"]?:Random::strongKey()),$Tj["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header(lang(32),null);echo"<form action='' method='post'>\n","<div>";if(print_hidden_fields($_POST,["auth"]))echo"<p class='message'>".lang(154)."\n";echo"</div>\n";Admin::get()->printLoginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!DRIVER)print_login_page();if(isset($_GET["username"])&&!defined('AdminNeo\DRIVER_EXTENSION')){Admin::get()->addError(lang(155,implode(", ",Drivers::getExtensions(DRIVER))));unset($_SESSION["pwds"][DRIVER]);unset_permanent($mk);page_header(lang(156),false);page_footer("auth");exit;}if(!isset($_GET["username"])||get_password()===null)print_login_page();validate_server_input($mk);validate_credentials_input($mk);check_invalid_login($mk);Admin::get()->getConfig()->applyServer(SERVER);$e=connect_to_db($mk);authenticate($mk);issue_permanent($mk);create_driver($e);if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){Admin::get()->addError(lang(157));page_header(lang(7));page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($Xa&&$_POST["token"])$_POST["token"]=get_token();if($_POST){if(!verify_token()){Admin::get()->addError(lang(157).' '.lang(158));$_POST=[];}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$k=lang(159,"'post_max_size'");if(isset($_GET["sql"]))$k
.=' '.lang(160);Admin::get()->addError($k);}is_authenticated(true);if(isset($_GET["settings"])){$M=Admin::get()->getSettings();$qm=array_merge(Admin::get()->getSettingsRows(1),Admin::get()->getSettingsRows(2),Admin::get()->getSettingsRows(3));if($_POST){$Tj=[];foreach($qm
as$w=>$I){if(isset($_POST[$w])){$zo=$_POST[$w]===""||(is_array($_POST[$w])&&in_array("",$_POST[$w]));$Tj[$w]=(!$zo?$_POST[$w]:null);}}$M->updateParameters($Tj);redirect(remove_from_uri());}$S=lang(146);page_header($S,[$S]);echo"<form id='settings' action='' method='post'>\n","<table class='box'>\n";foreach($qm
as$I)echo$I;echo"</table>\n","<p>","<input type='submit' value='".lang(118),"' class='button default hidden'>",input_token(),"</p>\n","</form>\n",script("initSettingsForm();");page_footer();exit;}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(DB==""&&isset($_GET["ns"]))redirect(remove_from_uri('ns'));if(!(DB!=""?Connection::get()->selectDatabase(DB):(isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill"))){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){Admin::get()->addError(lang(134));header("HTTP/1.1 404 Not Found");page_header(lang(31).": ".h(DB),true);}else{if($_POST["db"])queries_redirect(substr(ME,0,-1),lang(161),drop_databases($_POST["db"]));$S=h(Drivers::get(DRIVER).": ".Admin::get()->getServerName(SERVER));page_header($S,false);$yh=['privileges'=>[lang(73),"users"],'processlist'=>[lang(162),"list"],'variables'=>[lang(163),"variable"],'status'=>[lang(164),"status"],];$zh="";foreach($yh
as$w=>$W){if(support($w))$zh
.="<a href='".h(ME)."$w='>".icon($W[1])."$W[0]</a>";}if($zh)echo"<p class='links top-links'>$zh</p>\n";echo"<p>".lang(165,Drivers::get(DRIVER),"<b>".h(Connection::get()->getVersion())."</b>","<b>".DRIVER_EXTENSION."</b>")."\n","<p>".lang(166,"<b>".h(logged_user())."</b>")."\n";$h=Admin::get()->getDatabases();if($h){$Nl=support("scheme");$Ka=collations();echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr>".(support("database")?"<th>":"")."<th aria-sort='ascending'>".lang(31).(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".lang(167)."</a>":"")."<td>".lang(47)."<td>".lang(168)."<td>".lang(169)." - <a href='".h(ME)."dbsize=1'>".lang(170)."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n","<tbody>\n";$h=($_GET["dbsize"]?count_tables($h):array_flip($h));foreach($h
as$i=>$R){$xl=h(ME)."db=".urlencode($i);$s=h("Db-".$i);echo"<tr>".(support("database")?"<th class='actions'>".checkbox("db[]",$i,in_array($i,(array)$_POST["db"]),"","","",$s):""),"<th><a href='$xl' id='$s'>".h($i)."</a>";$Sb=h(db_collation($i,$Ka));echo"<td>".(support("database")?"<a href='$xl".($Nl?"&amp;ns=":"")."&amp;database=' title='".lang(70)."'>$Sb</a>":$Sb),"<td align='right'><a href='$xl&amp;schema=' id='tables-".h($i)."' title='".lang(72)."'>".($_GET["dbsize"]?$R:"?")."</a>","<td align='right' id='size-".h($i)."'>".($_GET["dbsize"]?db_size($i):"?"),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: event => tableClick(event, true)});"),"</table>\n","</div>\n";if(support("database"))echo"<div class='table-footer'><div class='field-sets'>\n","<fieldset><legend>",lang(171)," <span id='selected'></span></legend><div class='fieldset-content'>\n",input_hidden("all"),script("qsl('input').onclick = countDbs;"),"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(),"\n","</div></fieldset>\n","</div></div>\n",script("initTableFooter()");echo"</div>\n",input_token(),"</form>\n",script("tableCheck();");}}echo'<p class="links"><a href="'.h(ME).'database=">'.icon("database-add").lang(76)."</a>\n";page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~(?<=[?&])db=[^&]+~','\\0&ns='.urlencode(get_schema()),relative_uri()));if(!set_schema($_GET["ns"])){Admin::get()->addError(lang(173));header("HTTP/1.1 404 Not Found");page_header(lang(82).": ".h($_GET["ns"]),true);page_footer("ns");exit;}}}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$m=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$K=[idf_escape($_GET["field"])];$G=Driver::get()->select($a,$K,[where($_GET,$m)],$K);$I=($G?$G->fetchRow():[]);echo
Connection::get()->formatValue($I[0],$m[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$m=fields($a);if(!$m)Admin::get()->addError(error()?:lang(79));$Q=table_status1($a,true);$B=Admin::get()->getTableName($Q);$wl=[];foreach($m
as$w=>$l)$wl+=$l["privileges"];$S=$m&&is_view($Q)?$Q['Engine']=='materialized view'?lang(174):lang(175):lang(9);$mn=$B!=""?$B:h($a);page_header("$S: $mn",[$mn]);$pg=null;if(isset($wl["insert"])||!support("table"))$pg=[];Admin::get()->printTableMenu($Q,$pg);$hg=[];if(!preg_match("~sqlite|mssql|pgsql~",DIALECT)&&isset($Q["Engine"]))$hg[]=lang(176).": ".h($Q["Engine"]);if(isset($Q["Collation"]))$hg[]=lang(47).": ".h($Q["Collation"]);if($hg)echo"<p>",implode(", ",$hg),"</p>";if($m)Admin::get()->printTableStructure($m);$cc=$Q["Comment"];if($cc!="")echo"<p class='keep-lines'>",lang(48),": ",Admin::get()->formatComment($cc),"</p>\n";if(!is_view($Q))$Hd='<a href="'.h(ME).'create='.urlencode($a).'">'.icon("edit").lang(36)."</a>";elseif(support("view"))$Hd='<a href="'.h(ME).'view='.urlencode($a).'">'.icon("edit").lang(37)."</a>";else$Hd="";if($Hd&&($hg||$m||$cc!="")){echo"<p id='table-links' class='links'>",$Hd;$Sm=Admin::get()->getStructureDumpFormats();if($Sm){$yc=Admin::get()->getSettings()->getParameter("copyFormat");if(!isset($Sm[$yc]))$yc=key($Sm);echo"<span>","<a id='copy-table-structure' href='".h(ME)."dump=".urlencode($a)."&format=".urlencode($yc)."' class='jsonly'>".icon("copy").lang(177,h($Sm[$yc]))."</a>";$Sl=count($Sm)>1;if($Sl)echo"<a href='#format-list' class='toggle jsonly' title='".lang(178)."'>".icon_chevron_down()."</a>";echo"</span></p>\n";if($Sl){echo"<ul id='format-list' class='select-menu hidden'>";foreach($Sm
as$We=>$B)echo"<li><a href='".h(ME)."dump=".urlencode($a)."&format=".urlencode($We)."'>".h($B)."</a></li>";echo"</ul>",script("initToggles(gid('table-links'));");}echo
script("initCopyTableStructure();");}else
echo"</p>\n";}$Uj=Driver::get()->getParentTables($a);if($Uj){echo"<h2>".lang(179)."</h2>\n";Admin::get()->printRelatedTables($Uj);}if(Driver::get()->getPartitionBy()&&str_contains(isset($Q["Create_options"])?$Q["Create_options"]:"","partitioned")){$fk=Driver::get()->getPartitionsInfo($a);if($fk){echo"<h2 id='partitions'>".lang(50)."</h2>\n";Admin::get()->printTablePartitions($fk);if(DIALECT!="pgsql"&&$Hd)echo"<p class='links'>$Hd</p>\n";}}$ig=Driver::get()->getInheritedTables($a);if($ig){echo"<h2 id='inherited-by'>".lang(180)."</h2>\n";Admin::get()->printRelatedTables($ig);}if(support("indexes")&&Driver::get()->supportsIndex($Q)){echo"<h2 id='indexes'>".lang(181)."</h2>\n";$v=indexes($a);if($v)Admin::get()->printTableIndexes($v,$Q);if(Driver::get()->supportsAlterIndex($Q))echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.icon("edit").lang(182)."</a>\n";}if(!is_view($Q)){if(fk_support($Q)){echo"<h2 id='foreign-keys'>".lang(93)."</h2>\n";$Ue=foreign_keys($a);if($Ue){echo"<table>\n","<thead><tr><th>".lang(183)."<td>".lang(184)."<td>".lang(96)."<td>".lang(95)."<td></thead>\n";foreach($Ue
as$B=>$p)echo"<tr title='".h($B)."'>","<th><i>".implode("</i>, <i>",array_map('AdminNeo\h',$p["source"]))."</i>","<td><a href='".h($p["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($p["db"]),ME):($p["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($p["ns"]),ME):ME))."table=".urlencode($p["table"])."'>".($p["db"]!=""&&$p["db"]!=DB?"<b>".h($p["db"])."</b>.":"").($p["ns"]!=""&&$p["ns"]!=$_GET["ns"]?"<b>".h($p["ns"])."</b>.":"").h($p["table"])."</a>","(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$p["target"]))."</i>)","<td>".h($p["on_delete"]),"<td>".h($p["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($B)).'">'.lang(185).'</a>',"\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.icon("add").lang(186)."</a>\n";}if(support("check")){echo"<h2 id='checks'>".lang(187)."</h2>\n";$Fb=Driver::get()->checkConstraints($a);if($Fb){echo"<table cellspacing='0'>\n";foreach($Fb
as$w=>$W)echo"<tr title='".h($w)."'>","<td><code class='jush-".DIALECT."'>".truncate_utf8(preg_replace('~\s+~',' ',ltrim($W))),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($w))."'>".lang(185)."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.icon("add").lang(188)."</a>\n";}}if(support(is_view($Q)?"view_trigger":"trigger")){echo"<h2 id='triggers'>".lang(189)."</h2>\n";$do=triggers($a);if($do){echo"<table>\n";foreach($do
as$w=>$W)echo"<tr><td>".h($W[0])."<td>".h($W[1])."<th>".h($w)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($w))."'>".lang(185)."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.icon("add").lang(190)."</a>\n";}}elseif(isset($_GET["schema"])){$Mn=h(": ".DB.($_GET["ns"]?".$_GET[ns]":""));page_header(lang(72).$Mn,[lang(72)]);$pn=[];$qn=[];$Ae=[];$pa=($_GET["schema"]?:$_COOKIE["neo_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$pa,$A,PREG_SET_ORDER);foreach($A
as$_){$pn[$_[1]]=[(float)$_[2],(float)$_[3]];$qn[]="\n'".js_escape($_[1])."': [ $_[2], $_[3] ]";}$vh=1.4;$Sn=0;$ib=-1;$Ll=[];$hl=[];$nh=[];$La=Driver::get()->getAllFields();foreach(table_status('',true)as$P=>$Q){if(is_view($Q))continue;$wk=0;$Ll[$P]["fields"]=[];foreach(isset($La[$P])?$La[$P]:[]as$l){$wk=round($wk+$vh,2);$Ae[$P][$l["field"]]=$wk;$Ll[$P]["fields"][$l["field"]]=$l;}$Ll[$P]["pos"]=(isset($pn[$P])?$pn[$P]:[$Sn,0]);foreach(Admin::get()->getForeignKeys($P)as$W){if(!$W["db"]){$lh=$ib;if((isset($pn[$P][1])?$pn[$P][1]:0)||(isset($pn[$W["table"]][1])?$pn[$W["table"]][1]:0))$lh=min(floatval(isset($pn[$P][1])?$pn[$P][1]:0),floatval(isset($pn[$W["table"]][1])?$pn[$W["table"]][1]:0))-1;else$ib-=.1;while($nh[(string)$lh])$lh-=.0001;$Ll[$P]["references"][$W["table"]][(string)$lh]=[$W["source"],$W["target"]];$hl[$W["table"]][$P][(string)$lh]=$W["target"];$nh[(string)$lh]=true;}}$Sn=max($Sn,round($Ll[$P]["pos"][0]+$vh+$wk+1,2));}echo"<div id='schema' style='height: {$Sn}em;'>\n";foreach($Ll
as$B=>$P){$Ff=round($vh+count($P["fields"])*$vh+0.4,2);echo"<div class='table' style='top: ".$P["pos"][0]."em; left: ".$P["pos"][1]."em; height: {$Ff}em;'>","<div class='content'>",'<h4><a href="'.h(ME).'table='.urlencode($B).'">'.h($B)."</a></h4>","<ul>";foreach($P["fields"]as$l){$W='<span '.type_class($l["type"]).' title="'.h($l["type"].($l["length"]?"($l[length])":"").($l["null"]?" NULL":'')).'">'.h($l["field"]).'</span>';echo"<li>".($l["primary"]?"<i>$W</i>":$W)."</li>";}echo"</ul>","</div>";foreach((array)$P["references"]as$_n=>$jl){foreach($jl
as$lh=>$dl){$mh=$lh-(isset($pn[$B][1])?$pn[$B][1]:0);$r=0;foreach($dl[0]as$_m){echo"\n<div class='references outgoing' title='",h($_n),"' id='refs$lh-$r' style='left: {$mh}em; top: ",$Ae[$B][$_m],"em;'>","<div style='width: ".(-$mh)."em;'></div>","</div>";$r++;}}}foreach((array)$hl[$B]as$_n=>$jl){foreach($jl
as$lh=>$d){$mh=$lh-(isset($pn[$B][1])?$pn[$B][1]:0);$r=0;foreach($d
as$zn){echo"\n<div class='references incoming' title='",h($_n),"' id='refd$lh-$r' style='left: {$mh}em; top: ".$Ae[$B][$zn]."em;'>","<svg viewBox='0 0 22 22' fill='currentColor'><path d='M11,19l10,-8l-10,-8l0,16Z'/></svg>","<div style='width: ".(-$mh)."em;'></div>","</div>";$r++;}}}echo"\n</div>\n";}foreach($Ll
as$B=>$P){foreach((array)$P["references"]as$_n=>$jl){if($Ll[$_n]){foreach($jl
as$lh=>$dl){$mi=$Sn;$Uh=-10;foreach($dl[0]as$w=>$_m){$xk=$P["pos"][0]+$Ae[$B][$_m];$yk=$Ll[$_n]["pos"][0]+$Ae[$_n][$dl[1][$w]];$mi=round(min($mi,$xk,$yk),2);$Uh=round(max($Uh,$xk,$yk),2);}echo"<div class='references vertical' id='refl$lh' style='left: $lh"."em; top: $mi"."em;'>"."<div style='height: ".round($Uh-$mi,2)."em;'></div></div>\n";}}}}echo"</div>\n",script("initSchema('".js_escape(DB)."', $Sn, {".implode(",",$qn)."})"),"<p class='links'>","<a href='",h(ME."schema=".urlencode($pa)),"' id='schema-link'>",lang(191),"</a>","</p>\n";}elseif(isset($_GET["dump"])){$a=$_GET["dump"];$M=Admin::get()->getSettings();$Sm=Admin::get()->getStructureDumpFormats();$Jc=Admin::get()->getDataDumpFormats();$zc=is_ajax()&&$a!="";if($zc){$yc=isset($_GET["format"])?$_GET["format"]:null;if(!isset($Sm[$yc])){reset($Sm);$yc=key($Sm);}$M->updateParameter("copyFormat",$yc);$_POST=["format"=>$yc,"output"=>"text","db_style"=>"","table_style"=>"CREATE","tables"=>[$a],"triggers"=>(support("trigger")?"1":""),"data_style"=>"",];}if($_POST){$Ed=isset($Sm[$_POST["format"]]);$Dd=isset($Jc[$_POST["format"]]);if(!$zc)$M->updateParameters(["dumpFormat"=>$_POST["format"],"dumpOutput"=>$_POST["output"],]+($Ed?["dumpDbStyle"=>$_POST["db_style"],"dumpTypes"=>isset($_POST["types"])?$_POST["types"]:(support("type")?"":null),"dumpRoutines"=>isset($_POST["routines"])?$_POST["routines"]:(support("routine")?"":null),"dumpEvents"=>isset($_POST["events"])?$_POST["events"]:(support("event")?"":null),"dumpTableStyle"=>$_POST["table_style"],"dumpAutoIncrement"=>isset($_POST["auto_increment"])?$_POST["auto_increment"]:"","dumpTriggers"=>isset($_POST["triggers"])?$_POST["triggers"]:(support("trigger")?"":null),]:[])+($Dd?["dumpDataStyle"=>$_POST["data_style"],]:[]));$sn=($Ed?$_POST["table_style"]:"CREATE");$Lc=($Dd?$_POST["data_style"]:"");if(DB!="")$h=[DB];else{$h=isset($_POST["databases"])?$_POST["databases"]:[];if(is_string($h))$h=explode("\n",rtrim(str_replace("\r","",$h),"\n"));}$Ml=isset($_POST["schemas"])?$_POST["schemas"]:[];$R=array_flip(isset($_POST["tables"])?$_POST["tables"]:[])+array_flip(isset($_POST["data"])?$_POST["data"]:[]);if(count($R)==1)$Sf=key($R);elseif(count($Ml)==1)$Sf=$Ml[0];elseif(count($h)==1)$Sf=$h[0];else$Sf=Admin::get()->getServerName(SERVER,true,"server");$oe=dump_headers($Sf,DB==""||$_GET["ns"]===""||count($R)>1);$Ig=preg_match('~sql~',$_POST["format"]);$Kc=$Ig&&$Lc&&!$sn&&DIALECT!="sql";if($Ig){if(!$zc)echo"-- AdminNeo ".VERSION." ".Drivers::get(DRIVER)." ".Connection::get()->getVersion()." dump\n\n";if(DIALECT=="sql"){if(!$zc)echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($Lc?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";Connection::get()->query("SET time_zone = '+00:00'");Connection::get()->query("SET sql_mode = ''");}}$Um=($Ed?$_POST["db_style"]:"");foreach($h
as$i){if(Connection::get()->selectDatabase($i)){Admin::get()->dumpDatabase($i);if($Ig){if($Um)echo
create_database_sql($i,$Um),use_sql($i,$Um)."\n";$Mj="";if($_POST["types"]){foreach(types()as$s=>$T){$Wc=type_definition($s);$Wi=($Wc["kind"]=='d'?"DOMAIN":"TYPE");if($Wc["definition"])$Mj
.=($Um!='DROP+CREATE'?"DROP $Wi IF EXISTS ".idf_escape($T).";;\n":"")."CREATE $Wi ".idf_escape($T)." $Wc[definition];\n\n";else$Mj
.="-- Could not export type $T\n\n";}}if($_POST["routines"]){foreach(routines()as$I){$B=$I["ROUTINE_NAME"];$yl=$I["ROUTINE_TYPE"];$f=create_routine($yl,["name"=>$B]+routine($I["SPECIFIC_NAME"],$yl));set_utf8mb4($f);$Mj
.=($Um!='DROP+CREATE'?"DROP $yl IF EXISTS ".idf_escape($B).";;\n":"")."$f;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$I){$f=remove_definer(Connection::get()->getValue("SHOW CREATE EVENT ".idf_escape($I["Name"]),3));set_utf8mb4($f);$Mj
.=($Um!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($I["Name"]).";;\n":"")."$f;;\n\n";}}echo($Mj&&DIALECT=='sql'?"DELIMITER ;;\n\n$Mj"."DELIMITER ;\n\n":$Mj);}if($sn||$Lc){foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?[""]:Admin::get()->getSchemas(true)))as$Ll){if($Ll!="")set_schema($Ll);$vn=table_status('',true);$nn=array_keys($vn);$id=false;if($Kc&&$nn){$il=[];foreach($nn
as$B){if(!is_view($vn[$B])&&(DB==""||$_GET["ns"]===""||in_array($B,(array)$_POST["data"]))){foreach(foreign_keys($B)as$p)$il[$B][]=$p["table"];}}$Aj=dump_table_order($nn,$il);if($Aj)$nn=$Aj;else$id=function_exists('AdminNeo\foreign_key_checks_sql');}if($id)echo
foreign_key_checks_sql(false)."\n";$Ro=[];foreach($nn
as$B){$Q=$vn[$B];$P=(DB==""||$_GET["ns"]===""||in_array($B,(array)$_POST["tables"]));$g=(DB==""||$_GET["ns"]===""||in_array($B,(array)$_POST["data"]));if($P||$g){$Pn=null;if($oe=="tar"){$Pn=new
TmpFile();ob_start([$Pn,'write'],1e5);}$Ac=($P||!$Ed?$sn:"");Admin::get()->dumpTable($B,$Ac,(is_view($Q)?2:0));if(is_view($Q)&&$oe!="tar")$Ro[]=$B;elseif($g){$m=fields($B);Admin::get()->dumpData($B,$Lc,"SELECT *".convert_fields($m,$m)." FROM ".table($B));if($Ig&&!$Ac&&$_POST["auto_increment"]&&function_exists('AdminNeo\restart_sequences_sql'))echo"\n".restart_sequences_sql($B);}if($Ig&&$_POST["triggers"]&&$P&&($do=trigger_sql($B)))echo"\nDELIMITER ;;\n$do\nDELIMITER ;\n";if($oe=="tar"){ob_end_flush();tar_file((DB!=""?"":"$i/")."$B.csv",$Pn);}elseif($Ig)echo"\n";}}if($id)echo
foreign_key_checks_sql(true)."\n";if($Ig&&$sn&&function_exists('AdminNeo\foreign_keys_sql')){foreach($vn
as$B=>$Q){$P=(DB==""||$_GET["ns"]===""||in_array($B,(array)$_POST["tables"]));if($P&&!is_view($Q))echo
foreign_keys_sql($B);}}foreach($Ro
as$Po)Admin::get()->dumpTable($Po,$sn,1);if($oe=="tar")echo
pack("x512");}}}}if($Ig&&!$zc)echo"-- ".gmdate("Y-m-d H:i:s e")."\n";exit;}$B=DB!=""?h(DB):h(Admin::get()->getServerName(SERVER));page_header(lang(75).": $B",($_GET["export"]!=""?["table"=>$_GET["export"]]:[lang(75)]));echo"<form id='dump-form' action='' method='post'>\n","<table class='box'>\n";$Pc=['','USE','DROP+CREATE','CREATE'];$sn=['','DROP+CREATE','CREATE'];$Lc=['','TRUNCATE+INSERT','INSERT'];if(DIALECT=="sql")$Lc[]='INSERT+UPDATE';echo"<tr><th>",lang(178),"</th><td>",html_radios("format",$Sm+$Jc,$M->getParameter("dumpFormat","sql")),"</td></tr>\n";if(DIALECT!="sqlite"){echo"<tr><th id='label-db'>",lang(31),"</th>","<td>",html_select('db_style',$Pc,$M->getParameter("dumpDbStyle",DB==""?"CREATE":""),"","label-db"),"<span class='labels'>";if(support("type"))echo
checkbox("types",1,$M->getParameter("dumpTypes"),lang(112));if(support("routine"))echo
checkbox("routines",1,$M->getParameter("dumpRoutines",$_GET["dump"]==""?"1":""),lang(192));if(support("event"))echo
checkbox("events",1,$M->getParameter("dumpEvents",$_GET["dump"]==""?"1":""),lang(193));echo"</span></td></tr>";}echo"<tr><th id='label-tables'>",lang(168),"</th><td>",html_select('table_style',$sn,$M->getParameter("dumpTableStyle","DROP+CREATE"),"","label-tables")," <span class='labels'>",checkbox("auto_increment",1,$M->getParameter("dumpAutoIncrement"),lang(49));if(support("trigger"))echo
checkbox("triggers",1,$M->getParameter("dumpTriggers","1"),lang(189));echo"</span></td></tr>","<tr><th id='label-data'>",lang(194),"</th><td>",html_select("data_style",$Lc,$M->getParameter("dumpDataStyle","INSERT"),"","label-data"),"</td></tr>","<tr><th>",lang(195),"</th><td>",html_radios("output",Admin::get()->getDumpOutputs(),$M->getParameter("dumpOutput","file")),"</td></tr>\n","</table>\n",script("initDumpFormats(".json_encode(array_keys($Sm)).", ".json_encode(array_keys($Jc)).");"),"<p>","<input type='submit' class='button default' value='",lang(75),"'>",input_token(),"</p>\n","<table>\n",script("qsl('table').onclick = dumpClick;");$Ck=[];if(DB!=""&&$_GET["ns"]===""){echo"<thead><tr><th>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly' title='".lang(196)."'>".lang(82)."</label>".script("gid('check-schemas').onclick = partial(formCheck, /^schemas\\[/);",""),"</thead>\n";foreach(Admin::get()->getSchemas()as$Ll){if(!information_schema(DB,$Ll))echo"<tr><td>".checkbox("schemas[]",$Ll,true,$Ll,"","block")."\n";}}elseif(DB!=""){$Ib=($a!=""?"":" checked");echo"<thead><tr>","<th><label class='block'><input type='checkbox' id='check-tables'$Ib class='jsonly' title='".lang(196)."'>".lang(9)."</label>".script("gid('check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th class='right'><label class='block'>".lang(194)."<input type='checkbox' id='check-data'$Ib class='jsonly' title='".lang(196)."'></label>".script("gid('check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$Ro="";$un=tables_list();foreach($un
as$B=>$T){$Bk=preg_replace('~_.*~','',$B);$Ib=($a==""||$a==(substr($a,-1)=="%"?"$Bk%":$B));$Hk="<tr><td>".checkbox("tables[]",$B,$Ib,$B,"","block");if($T!==null&&!preg_match('~table~i',$T))$Ro
.="$Hk\n";else
echo"$Hk<td class='right'><label class='block'><span id='Rows-".h($B)."'></span>".checkbox("data[]",$B,$Ib)."</label>\n";$Ck[$Bk]++;}echo$Ro;if($un)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$h=Admin::get()->getDatabases();echo"<thead><tr><th>","<label class='block'>".($h?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly' title='".lang(196)."'>".script("gid('check-databases').onclick = partial(formCheck, /^databases\\[/);",""):"").lang(31)."</label>","</thead>\n";if($h){foreach($h
as$i){if(!information_schema($i)){$Bk=preg_replace('~_.*~','',$i);echo"<tr><td>".checkbox("databases[]",$i,$a==""||$a=="$Bk%",$i,"","block")."\n";$Ck[$Bk]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo"</table>\n","</form>\n";$yh=[];foreach($Ck
as$w=>$W){if($w!=""&&$W>1)$yh[]="<a href='".h(ME)."dump=".urlencode("$w%")."'>".icon("check").h($w)."*</a>";}if($yh)echo"<p class='links'>",implode("",$yh),"</p>\n";}elseif(isset($_GET["privileges"])){$Mn=DB!=""?h(": ".DB):"";page_header(lang(73).$Mn,[lang(73)]);echo'<p class="links top-links"><a href="',h(ME),'user=">',icon("user-add"),lang(197),"</a></p>\n";$G=Connection::get()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$nf=$G;if(!$G)$G=Connection::get()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''>\n";hidden_fields_get();echo
input_hidden("db",DB);if(!$nf)echo
input_hidden("grant");echo"\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr><th>".lang(6)."<th>".lang(5)."<th></thead>\n";while($I=$G->fetchAssoc())echo'<tr><td>'.h($I["User"])."<td>".h($I["Host"]).'<td><a href="'.h(ME.'user='.urlencode($I["User"]).'&host='.urlencode($I["Host"])).'">'.lang(39)."</a>\n";if(!$nf||DB!="")echo"<tr><td><input class='input' name='user' autocapitalize='off'>"."<td><input class='input' name='host' value='localhost' autocapitalize='off'>"."<td><input type='submit' class='button' value='".lang(39)."'>\n";echo"</table>\n","</div>\n","</form>\n";}elseif(isset($_GET["sql"])){function
get_unsupported_query($F,$Bm){if(DIALECT!="sqlite"||preg_match("~^(?:$Bm|;)*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$F,$_)===0)return
null;return
preg_match('~ATTACH~i',isset($_[1])?$_[1]:"")?'ATTACH':'VACUUM INTO';}$M=Admin::get()->getSettings();$uh=Driver::get()->lineComment();$Bm="(?:\\s|\xEF\xBB\xBF|/\\*[\s\S]*?\\*/|(?:$uh)[^\n]*\n?|--\r?\n)";if($_POST["export"]&&!get_unsupported_query($_POST["query"],$Bm)){$M->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers("sql");Admin::get()->dumpTable("","");Admin::get()->dumpData("","table",$_POST["query"]);exit;}restart_session();$Kf=&get_session("queries");$Jf=&$Kf[DB];if($_POST["clear"]){$Jf=[];redirect(remove_from_uri("history"));}stop_session();$S=isset($_GET["import"])?lang(74):lang(41);page_header($S,[$S]);if($_POST){$cf=false;if(!isset($_GET["import"]))$F=$_POST["query"];elseif($_POST["webfile"]){$Vf=Admin::get()->getImportFilePath();if($Vf){if(file_exists($Vf))$cf=fopen($Vf,"rb");elseif(file_exists("$Vf.gz"))$cf=fopen("compress.zlib://$Vf.gz","rb");}$F=$cf?fread($cf,1e6):false;}else$F=get_file("sql_file",true,";");if(is_string($F)){if(($ai=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($ai,strval(2*strlen($F)+memory_get_usage()+8e6)));if($F!=""&&strlen($F)<1e6){$Pk=$F.(preg_match("~;[ \t\r\n]*\$~",$F)?"":";");if(!$Jf||first(end($Jf))!=$Pk){restart_session();$Jf[]=[$Pk,time()];set_session("queries",$Kf);stop_session();}}$Yc=";";$Zc=1;$Yi=0;$Od=true;$xc=false;$nc=connect();if($nc&&DB!=""){$nc->selectDatabase(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$nc);}$bc=0;$ae=[];$Vj='[\'"'.(DIALECT=="sql"?'`':(DIALECT=="sqlite"?'`[':(DIALECT=="mssql"?'[':''))).']|/\*|'.$uh.'|$'.(DIALECT=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$Tn=microtime(true);$Jc=Admin::get()->getDataDumpFormats();unset($Jc["sql"]);while($F!=""){if(!$Yi&&preg_match("~^$Bm*+DELIMITER\\s+(\\S+)~i",$F,$_)){$Yc=preg_quote($_[1]);$Zc=strlen($_[1]);$Ye=Admin::get()->formatSqlCommandQuery(trim($_[0]));if($Ye!="")echo"<pre><code class='jush-".DIALECT."'>$Ye</code></pre>\n";$F=substr($F,strlen($_[0]));}elseif(!$Yi&&DIALECT=="pgsql"&&preg_match("~^($Bm*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$F,$_)){$Yc="\n\\\\\\.\r?\n";$Zc=3;$xc=true;$Yi=strlen($_[0]);}else{preg_match("($Yc\\s*|$Vj)",$F,$_,PREG_OFFSET_CAPTURE,$Yi);list($af,$wk)=$_[0];if(!$af&&$cf&&!feof($cf))$F
.=fread($cf,1e5);else{if(!$af&&rtrim($F)=="")break;$Yi=$wk+strlen($af);if($af&&!preg_match("(^$Yc)",$af)){$wb=Driver::get()->hasCStyleEscapes()||(DIALECT=="pgsql"&&($wk>0&&strtolower($F[$wk-1])=="e"));$kk='(';if($af=='/*')$kk
.='\*/';elseif($af=='[')$kk
.=']';elseif(preg_match("~^(?:$uh)~",$af))$kk
.="\n";else$kk
.=preg_quote($af).($wb?"|\\\\.":"");$kk
.='|$)s';while(preg_match($kk,$F,$_,PREG_OFFSET_CAPTURE,$Yi)){$Dl=$_[0][0];if(!$Dl&&$cf&&!feof($cf))$F
.=fread($cf,1e5);else{$Yi=$_[0][1]+strlen($Dl);if(!isset($Dl[0])||$Dl[0]!="\\")break;}}}elseif(DIALECT=="sqlite"&&preg_match("~^(?:$Bm|;)*+\$~",substr($F,0,$wk+$Zc))){$F=substr($F,$Yi);$Yi=0;}else{$Od=false;$Pk=substr($F,0,$wk+$Zc);$bc++;$Hk="<pre id='sql-$bc'><code class='jush-".DIALECT."'>".Admin::get()->formatSqlCommandQuery(trim($Pk))."</code></pre>\n";if($to=get_unsupported_query($Pk,$Bm)){echo$Hk,"<p class='error'>".lang(198,$to)."\n";$ae[]=" <a href='#sql-$bc'>$bc</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Hk;ob_flush();flush();}$Mm=microtime(true);if(Connection::get()->multiQuery($Pk)&&is_object($nc)&&preg_match("~^$Bm*+USE\\b~i",$Pk))$nc->query($Pk);do{$G=Connection::get()->storeResult();if(Connection::get()->getError()){echo($_POST["only_errors"]?$Hk:""),"<p class='error'>",lang(199),(!empty(Connection::get()->getErrno())?" (".Connection::get()->getErrno().")":""),": ",error()."</p>\n";$ae[]=" <a href='#sql-$bc'>$bc</a>";if($_POST["error_stops"])break
2;}else{$Jn=" <span class='time'>(".format_time($Mm).")</span>";$Id=(strlen($Pk)<1000?" <a href='".h(ME)."sql=".urlencode(trim($Pk))."'>".icon("edit").lang(39)."</a>":"");$Tk=Connection::get()->getQueryInfo();$Da=Connection::get()->getAffectedRows();$si=Connection::get()->hasMoreResults();$Xo=($_POST["only_errors"]||$si?null:Driver::get()->warnings());$Zo=($si?Connection::get()->getWarningsCount():0);$ap="warnings-$bc";if($Xo)$bp="<a href='#$ap' class='toggle'>".lang(40).icon_chevron_down()."</a>";elseif($Zo)$bp="<span>".lang(40).": $Zo</span>";else$bp=null;$je=$Dj=null;$ke="explain-$bc";$le=false;$me="export-$bc";$y=0;if(is_object($G)){if(!$_POST["only_errors"])echo"<div class='table-result'>\n";$y=(int)$_POST["limit"];$Dj=print_select_result($G,$nc,[],$y);if(!$_POST["only_errors"]){echo"<p class='links'>";$Si=$G->getRowsCount();echo($Si?($y&&$Si>$y?lang(200,$y):"").lang(201,$Si):""),$Jn,$Id,$bp;if($nc&&preg_match("~^($Bm|\\()*+SELECT\\b~i",$Pk)&&($je=explain($nc,$Pk)))echo"<a href='#$ke' class='toggle'>Explain".icon_chevron_down()."</a>";$le=true;echo"<a href='#$me' class='toggle'>".lang(75).icon_chevron_down()."</a>","</p>\n";}}else{if(preg_match("~^$Bm*+(CREATE|DROP|ALTER)$Bm++(DATABASE|SCHEMA)\\b~i",$Pk)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"]){echo"<p class='message' title='".h($Tk)."'>",lang(202,$Da),"$Jn $Id";if($bp)echo", $bp";echo"</p>\n";}}if(!$_POST["only_errors"])echo
script("initToggles(qsl('p'));");if($Xo)echo"<div id='$ap' class='hidden'>\n$Xo</div>\n";if($je){echo"<div id='$ke' class='hidden explain'>\n";print_select_result($je,$nc,$Dj);echo"</div>\n";}if($le){echo"<form id='$me' action='' method='post' class='hidden'><p>\n",html_select("format",$Jc,$M->getParameter("exportFormat")),html_select("output",Admin::get()->getDumpOutputs(),$M->getParameter("exportOutput"))." ",input_hidden("query",$Pk),input_token()," <input type='submit' class='button' name='export' value='".lang(75)."'>";if(!$y)echo
script("qsl('input').onclick = function (event) { return sqlExport.call(this, event, '".js_escape(ME)."set=export-settings'); };","");echo"</p></form>\n";}if(is_object($G)&&!$_POST["only_errors"])echo"</div>\n";}$Mm=microtime(true);}while(Connection::get()->nextResult());}$F=substr($F,$Yi);$Yi=0;if($xc){$Yc=";";$Zc=1;$xc=false;}}}}}if($Od)echo"<p class='message'>".lang(203)."\n";else{$Yf=Connection::get()->inTransaction();Driver::get()->rollback();if($Yf)echo"<pre><code class='jush-".DIALECT."'>ROLLBACK -- AdminNeo</code></pre>\n";if($_POST["only_errors"]){$bj=$bc-count($ae);echo"<p class='".($bj?"message":"error")."'>".lang(204,$bc-count($ae))," <span class='time'>(".format_time($Tn).")</span>\n";}elseif($ae&&$bc>1)echo"<p class='error'>".lang(199).": ".implode("",$ae)."\n";}}else
echo"<p class='error'>".upload_error($F)."\n";}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";if(!isset($_GET["import"])){$Pk=$_GET["sql"];if($_POST)$Pk=$_POST["query"];elseif($_GET["history"]=="all")$Pk=$Jf;elseif($_GET["history"]!="")$Pk=$Jf[$_GET["history"]][0];echo"<p>";textarea("query",$Pk,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."gid('form').onsubmit = partial(sqlSubmit, gid('form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"</p>","<p><input type='submit' class='button default' value='".lang(205)."' title='Ctrl+Enter'>",lang(206).": <input type='number' name='limit' class='input size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{echo"<div class='field-sets'>\n","<fieldset><legend>".lang(207)."</legend><div class='fieldset-content'>";$vf=(extension_loaded("zlib")?"[.gz]":"");if(ini_bool("file_uploads"))echo"SQL$vf (&lt; ".ini_get("upload_max_filesize")."B): <input type='file' name='sql_file[]' multiple>","<input type='submit' class='button default' value='".lang(205)."'>",file_upload_form_script("form","sql_file[]");else
echo
lang(208);echo"</div></fieldset>\n";$Vf=Admin::get()->getImportFilePath();if($Vf)echo"<fieldset><legend>".lang(209)."</legend><div class='fieldset-content'>",lang(210,"<code>".h($Vf)."$vf</code>")," <input type='submit' class='button default' name='webfile' value='".lang(211)."'>","</div></fieldset>\n";echo"</div>\n","<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:(isset($_GET["error_stops"])?$_GET["error_stops"]:true)),lang(212)),checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),lang(213)),input_token(),"</p>\n";if(!isset($_GET["import"]))Admin::get()->printAfterSqlCommand();if(!isset($_GET["import"])&&$Jf){echo"<div class='field-sets'>\n";print_fieldset_start("history",lang(214),"history",$_GET["history"]!="");for($W=end($Jf);$W;$W=prev($Jf)){$w=key($Jf);list($Pk,$Jn,$Md)=$W;echo" <pre><code class='jush-".DIALECT."'>",truncate_utf8(preg_replace('~\s+~',' ',ltrim(preg_replace("~^(?:$uh).*~m",'',$Pk)))),"</code></pre>",'<p class="links">',"<a href='".h(ME."sql=&history=$w")."'>".icon("edit").lang(39)."</a>"," <span class='time' title='".@date('Y-m-d',$Jn)."'>".@date("H:i:s",$Jn).($Md?" ($Md)":"")."</span>","</p>";}echo"<p><input type='submit' class='button' name='clear' value='".lang(215)."'>\n","<a href='",h(ME."sql=&history=all")."' class='button light'>",icon("edit"),lang(216),"</a></p>\n";print_fieldset_end("history");echo"</div>\n";}echo"</form>\n";}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$m=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$m):""):where($_GET,$m));$vo=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($m
as$B=>$l){if((!$vo&&!isset($l["privileges"]["insert"]))||Admin::get()->getFieldName($l)=="")unset($m[$B]);}if($_POST&&!isset($_GET["select"])){$Hh=$_POST["referer"];if($_POST["insert"])$Hh=($vo?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$Hh)||!is_same_host($Hh))$Hh=ME."select=".urlencode($a);$v=indexes($a);$no=unique_array(isset($_GET["where"])?$_GET["where"]:[],$v);$Uk="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Hh,lang(217),(bool)Driver::get()->delete($a,$Uk,$no?0:1));else{$om=[];foreach($m
as$B=>$l){$W=process_input($l);if($W!==false&&$W!==null)$om[idf_escape($B)]=$W;}if($vo){if(!$om)redirect($Hh);queries_redirect($Hh,lang(218),(bool)Driver::get()->update($a,$om,$Uk,$no?0:1));if(is_ajax()){page_headers();page_messages();exit;}}else{$G=Driver::get()->insert($a,$om);$jh=($G?last_id($G):0);queries_redirect($Hh,lang(219,($jh?" $jh":"")),(bool)$G);}}}$I=null;if($Z){$K=[];foreach($m
as$B=>$l){if(isset($l["privileges"]["select"])){$Ta=($_POST["clone"]&&$l["auto_increment"]?"''":convert_field($l));$K[]=($Ta?"$Ta AS ":"").idf_escape($B);}}$I=[];if(!support("table"))$K=["*"];if($K){$G=Driver::get()->select($a,$K,[$Z],$K,[],(isset($_GET["select"])?2:1));if(!$G)Admin::get()->addError(error());else{$I=$G->fetchAssoc();if(!$I)$I=false;}if(isset($_GET["select"])&&(!$I||$G->fetchAssoc()))$I=null;}}if(!support("table")&&!$m){if(!$Z){$G=Driver::get()->select($a,["*"],[],["*"]);$I=($G?$G->fetchAssoc():false);if(!$I)$I=[Driver::get()->primary=>""];}if($I){foreach($I
as$w=>$W){if(!$Z)$I[$w]=null;$m[$w]=["field"=>$w,"null"=>($w!=Driver::get()->primary),"auto_increment"=>($w==Driver::get()->primary)];}}}if(isset($_POST["save"])?$_POST["save"]:false){$zk=[];foreach((isset($_POST["fields"])?$_POST["fields"]:[])as$w=>$W)$zk[bracket_escape($w,true)]=$W;$I=$zk+($I?:[]);}if($_POST["edit"]){$Kd=array_filter($m,function($l){return!(isset($l["generated"])?$l["generated"]:null);});}else$Kd=$m;edit_form($a,$Kd,$I,$vo);}elseif(isset($_GET["create"])){$a=$_GET["create"];$bk=Driver::get()->getPartitionBy();$fk=$bk?Driver::get()->getPartitionsInfo($a):[];$fl=referencable_primary($a);$Ue=[];foreach($fl
as$mn=>$l)$Ue[str_replace("`","``",$mn)."`".str_replace("`","``",$l["field"])]=$mn;$Gj=[];$Q=[];if($a!=""){$Gj=fields($a);$Q=table_status1($a);if(count($Q)<2)Admin::get()->addError(lang(79));}$I=$_POST;$I["Comment"]=normalize_newlines($I["Comment"]);$I["fields"]=(array)$I["fields"];if($I["auto_increment_col"])$I["fields"][$I["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!Admin::get()->getErrors())Admin::get()->getSettings()->updateParameter("commentsOpened",isset($_POST["comments"])?$_POST["comments"]:null);if($_POST&&!process_fields($I["fields"])&&!Admin::get()->getErrors()){if($_POST["drop"])queries_redirect(substr(ME,0,-1),lang(220),drop_tables([$a]));else{$m=[];$La=[];$_o=false;$Se=[];$Fj=reset($Gj);$Fa=" FIRST";foreach($I["fields"]as$w=>$l){$p=$Ue[$l["type"]];$go=($p!==null?$fl[$p]:$l);if($l["field"]!=""){if(!$l["generated"])$l["default"]=null;$Nk=process_field($l,$go);$La[]=[$l["orig"],$Nk,$Fa];if(!$Fj||$Nk!==process_field($Fj,$Fj)){$m[]=[$l["orig"],$Nk,$Fa];if($l["orig"]!=""||$Fa)$_o=true;}if($p!==null)$Se[idf_escape($l["field"])]=($a!=""&&DIALECT!="sqlite"?"ADD":" ").format_foreign_key(['table'=>$Ue[$l["type"]],'source'=>[$l["field"]],'target'=>[$go["field"]],'on_delete'=>$l["on_delete"],]);$Fa=" AFTER ".idf_escape($l["field"]);}elseif($l["orig"]!=""){$_o=true;$m[]=[$l["orig"]];}if($l["orig"]!=""){$Fj=next($Gj);if(!$Fj)$Fa="";}}$dk=[];if(in_array($I["partition_by"],$bk)){foreach($I
as$w=>$W){if(preg_match('~^partition~',$w))$dk[$w]=$W;}foreach($dk["partition_names"]as$w=>$B){if($B===""){unset($dk["partition_names"][$w]);unset($dk["partition_values"][$w]);}}$dk["partition_names"]=array_values($dk["partition_names"]);$dk["partition_values"]=array_values($dk["partition_values"]);if($dk==$fk)$dk=[];}elseif(str_contains(isset($Q["Create_options"])?$Q["Create_options"]:"","partitioned"))$dk=null;$ci=lang(221);if($a==""){cookie("neo_engine",isset($I["Engine"])?$I["Engine"]:"");$ci=lang(222);}$B=trim($I["name"]);$Hh=ME.(support("table")?"table=":"select=").urlencode($B);$G=alter_table($a,$B,(DIALECT=="sqlite"&&($_o||$Se)?$La:$m),$Se,($I["Comment"]!=$Q["Comment"]?$I["Comment"]:null),($I["Engine"]&&$I["Engine"]!=$Q["Engine"]?$I["Engine"]:""),($I["Collation"]&&$I["Collation"]!=$Q["Collation"]?$I["Collation"]:""),($I["Auto_increment"]!=""?number($I["Auto_increment"]):""),$dk);if($G&&!Queries::$queries)redirect($Hh);queries_redirect($Hh,$ci,$G);}}if($a!="")page_header(lang(36).": ".h($a),["table"=>$a,lang(36)]);else
page_header(lang(78),[lang(78)]);if(!$_POST){$jo=Driver::get()->getTypes();$I=["Engine"=>$_COOKIE["neo_engine"],"fields"=>[["field"=>"","type"=>(isset($jo["int"])?"int":(isset($jo["integer"])?"integer":"")),"on_update"=>""]],"partition_names"=>[""],];if($a!=""){$I=$Q;$I["name"]=$a;$I["fields"]=[];if(!$_GET["auto_increment"])$I["Auto_increment"]="";foreach($Gj
as$l){$l["generated"]=$l["generated"]?:(isset($l["default"])?"DEFAULT":"");$I["fields"][]=$l;}if($bk){$I+=$fk;$I["partition_names"][]="";$I["partition_values"][]="";}}}$Sg=[];if($I["Collation"])$Sg[$I["Collation"]]=true;foreach($I["fields"]as$l){if($l["collation"])$Sg[$l["collation"]]=true;}$Tb=Admin::get()->getCollations(array_keys($Sg));$Ud=Driver::get()->engines();foreach($Ud
as$Td){if(!strcasecmp($Td,$I["Engine"])){$I["Engine"]=$Td;break;}}$Ph=max_input_vars(12,20);if($Ph){$Gf=(count($I["fields"])>$Ph?"":" hidden");echo"<p".($Gf?" id='max-fields' data-columns='$Ph'":"")." class='error$Gf'>".max_input_vars_error()."\n";}echo"<form action='' method='post' id='form'>\n";if(support("columns")||$a==""){echo"<p>",lang(223),": ","<input class='input' name='name' data-maxlength='64' value='",h($I["name"]),"' autocapitalize='off'",(($a==""&&!$_POST)?" autofocus":""),">";if($Ud)echo" ",html_select("Engine",[""=>"(".lang(224).")"]+$Ud,$I["Engine"]),help_script_command("value",true);if($Tb&&!preg_match("~sqlite|mssql~",DIALECT))echo" ",html_select("Collation",[""=>"(".lang(94).")"]+$Tb,$I["Collation"]);echo" <input type='submit' class='button default' value='",lang(118),"'>","</p>";}if(support("columns")&&($a==""||!Driver::get()->isPartition($a))){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($I["fields"],$Tb,"TABLE",$Ue);echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>",lang(49),": ","<input type='number' class='input size' name='Auto_increment' size='6' value='",h($I["Auto_increment"]),"'>";$fc=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$dc=$fc?"":"hidden";if(support("comment")){echo
checkbox("comments",1,$fc,lang(48),"editingCommentsClick(this);","jsonly")," ";if(preg_match('~\n~',$I["Comment"]))echo"<textarea name='Comment' rows='2' cols='20'",($dc?" class='$dc'":""),">",h($I["Comment"]),"</textarea>";else
echo"<input name='Comment' value='",h($I["Comment"]),"' data-maxlength='",(Connection::get()->isMinVersion("5.5")?2048:60),"' class='input $dc'>";}echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(118),"'>";}elseif($a!="")echo"<p>";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$a)),"</p>\n";if($bk&&(DIALECT=="sql"||$a=="")){echo"<div class='field-sets'>\n";$ck=preg_match('~RANGE|LIST~',$I["partition_by"]);print_fieldset_start("partition",lang(226),"split",(bool)$I["partition_by"]);echo"<p>",html_select("partition_by",array_merge([""],$bk),$I["partition_by"]),help_script_command("value.replace(/./, 'PARTITION BY \$&')",true),script("qsl('select').onchange = partitionByChange;"),"(<input class='input' name='partition' value='",h($I["partition"]),"'>) ",lang(50),": ","<input type='number' name='partitions' class='input size ",($ck||!$I["partition_by"]?"hidden":""),"' value='",h($I["partitions"]),"'>","</p>\n","<table id='partition-table'",($ck?"":" class='hidden'"),">\n","<thead><tr><th>",lang(227),"</th><th>",lang(52),"</th></tr></thead>\n";foreach($I["partition_names"]as$w=>$W){echo"<tr>","<td><input class='input' name='partition_names[]' value='",h($W),"' autocapitalize='off'>";if($w==count($I["partition_names"])-1)echo
script("qsl('input').oninput = partitionNameChange;");echo"</td>","<td><input class='input' name='partition_values[]' value='",h(isset($I["partition_values"][$w])?$I["partition_values"][$w]:""),"'></td>","</tr>\n";}echo"</table>\n","</p>\n";print_fieldset_end("partition");echo"</div>\n";}echo
input_token(),"</form>\n";}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$dg=["PRIMARY","UNIQUE","INDEX"];$Q=table_status1($a,true);$ag=Driver::get()->getIndexAlgorithms($Q);$e=Connection::get();$Jh=$e->isMariaDB();if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($Jh?"10.0.5":"5.6")?'|InnoDB':'').'~i',$Q["Engine"]))$dg[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($Jh?"10.2.2":"5.7")?'|InnoDB':'').'~i',$Q["Engine"]))$dg[]="SPATIAL";if($Jh&&$e->isMinVersion("11.7")&&preg_match('~MyISAM|InnoDB~i',$Q["Engine"]))$dg[]="VECTOR";$v=indexes($a);$m=fields($a);$Fk=[];if(DIALECT=="mongo"){$Fk=$v["_id_"];unset($dg[0]);unset($v["_id_"]);}$I=$_POST;if($I){$M=Admin::get()->getSettings();if($M->getParameter("indexOptions")!==null)$M->updateParameter("indexOptions",null);}if($_POST&&!$_POST["add"]&&!$_POST["drop_col"]){$b=[];foreach($I["indexes"]as$u){$B=$u["name"];if(in_array($u["type"],$dg)){$d=[];$rh=[];$dd=[];$qj=[];$Zf=$ag?(in_array($u["algorithm"],$ag)?$u["algorithm"]:first($ag)):"";$bg=(support("partial_indexes")?$u["partial"]:"");$om=[];ksort($u["columns"]);foreach($u["columns"]as$w=>$c){if($c!=""){$x=isset($u["lengths"][$w])?$u["lengths"][$w]:null;$bd=isset($u["descs"][$w])?$u["descs"][$w]:null;$pj=isset($u["opclasses"][$w])?$u["opclasses"][$w]:null;$om[]=($m[$c]?idf_escape($c):$c).($x?"(".(+$x).")":"").($pj!=""?" ".idf_escape($pj):"").($bd?" DESC":"");$d[]=$c;$rh[]=($x?:null);$dd[]=$bd;$qj[]="$pj";}}$ie=$v[$B];if($ie){ksort($ie["columns"]);ksort($ie["lengths"]);ksort($ie["descs"]);if($u["type"]==$ie["type"]&&array_values($ie["columns"])===$d&&(!$ie["lengths"]||array_values($ie["lengths"])===$rh)&&array_values($ie["descs"])===$dd&&(!$ie["opclasses"]||array_values($ie["opclasses"])===$qj)&&(!$ag||$ie["algorithm"]===$Zf)&&$ie["partial"]==$bg){unset($v[$B]);continue;}}if($d)$b[]=[$u["type"],$B,$om,$Zf,$bg];}}foreach($v
as$B=>$ie)$b[]=[$ie["type"],$B,"DROP"];if(!$b)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),lang(228),alter_indexes($a,$b));}page_header(lang(182).": ".h($a),["table"=>$a,lang(182)]);$Ce=array_keys($m);if($_POST["add"]){foreach($I["indexes"]as$w=>$u){if($u["columns"][count($u["columns"])]!="")$I["indexes"][$w]["columns"][]="";}$u=end($I["indexes"]);if($u["type"]||array_filter($u["columns"],'strlen'))$I["indexes"][]=["columns"=>[1=>""]];}if(!$I){foreach($v
as$w=>$u){$v[$w]["name"]=$w;$v[$w]["columns"][]="";}$v[]=["columns"=>[1=>""]];$I["indexes"]=$v;}$rh=(DIALECT=="sql"||DIALECT=="mssql");$qj=Driver::get()->getIndexOpclasses();if($_POST)$sm=$_POST["options"];else{$sm=false;foreach($v
as$u){if(array_filter(isset($u["lengths"])?$u["lengths"]:[])||array_filter(isset($u["descs"])?$u["descs"]:[])||array_filter(isset($u["opclasses"])?$u["opclasses"]:[])||(isset($u["partial"])?$u["partial"]:"")!=""){$sm=true;break;}}}echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th id='label-type'>",lang(229),"</th>";$vj="class='idxopts".($sm?"":" hidden")."'";if(count($ag)>1)echo"<th id='label-method' $vj>",lang(230),doc_link(['sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'ha-and-performance/optimization-and-tuning/optimization-and-indexes/storage-engine-index-types','pgsql'=>'indexes-types.html',]),"</th>";echo"<th><input type='submit' hidden>",lang(53).($rh?"<span ".str_replace("class='","class='note ",$vj)."> (".lang(54).")</span>":"");if($rh||support("descidx"))echo
checkbox("options",1,$sm,lang(100),"indexOptionsShow(this.checked)","jsonly")."\n";echo"</th>","<th id='label-name'>",lang(231),"</th>";if(support("partial_indexes"))echo"<th id='label-condition' $vj>",lang(55),"</th>";echo"<th>","<button name='add[0]' value='1' title='",lang(102),"' class='button light hidden'>",icon_solo("add"),"</button>","</th>","</tr></thead>\n";if($Fk){echo"<tr><td>PRIMARY<td>";foreach($Fk["columns"]as$c)echo
select_input(" disabled",array_combine($Ce,$Ce),$c),"<label><input type='checkbox' disabled>".lang(63)."</label> ";echo"<td><td>\n";}$Ng=1;foreach($I["indexes"]as$u){if(!$_POST["drop_col"]||$Ng!=key($_POST["drop_col"])){echo"<tr><td>",html_select("indexes[$Ng][type]",[-1=>""]+$dg,$u["type"],($Ng==count($I["indexes"])?"indexesAddRow.call(this);":""),"label-type"),"</td>";if(count($ag)>1)echo"<td $vj>",html_select("indexes[$Ng][algorithm]",array_merge([""],$ag),$u['algorithm'],"label-method"),"</td>";echo"<td>";ksort($u["columns"]);$r=1;foreach($u["columns"]as$w=>$c){echo"<span>".select_input(" name='indexes[$Ng][columns][$r]' title='".lang(44)."'",($m&&($c==""||$m[$c])?array_combine($Ce,$Ce):[]),$c,"partial(indexesChangeColumn, '".js_escape(DIALECT=="sql"?"":$_GET["indexes"]."_")."')"),"<span $vj>";if($rh)echo"<input type='number' name='indexes[$Ng][lengths][$r]' class='input size' value='".(h(isset($u["lengths"][$w])?$u["lengths"][$w]:"")),"' title='".lang(99),"'>";if($qj){$pj=isset($u["opclasses"][$w])?$u["opclasses"][$w]:"";echo
html_select("indexes[$Ng][opclasses][$r]",[""=>"(".lang(232).")"]+array_combine($qj,$qj)+($pj!=""?[$pj=>$pj]:[]),$pj),doc_link(['pgsql'=>'indexes-opclass.html']);}if(support("descidx"))echo
checkbox("indexes[$Ng][descs][$r]",1,isset($u["descs"][$w])?$u["descs"][$w]:false,lang(63));echo"<br></span></span>";$r++;}echo"</td>","<td><input name='indexes[$Ng][name]' value='",h($u["name"]),"' class='input' autocapitalize='off' aria-labelledby='label-name'></td>\n";if(support("partial_indexes"))echo"<td $vj><input name='indexes[$Ng][partial]' value='".h($u["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>","<button name='drop_col[$Ng]' value='1' title='",lang(59),"' class='button light'>",icon_solo("remove"),"</button>",script("qsl('button').onclick = onRemoveIndexRowClick;"),"</td>\n";}$Ng++;}echo"</table>\n","</div>\n","<p>","<input type='submit' class='button default' value='",lang(118),"'>",input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["database"])){$I=$_POST;if($_POST&&!isset($_POST["add_x"])){$B=trim($I["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),lang(233),drop_databases([DB]));}elseif(DB!==$B){if(DB!=""){$_GET["db"]=$B;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($B),lang(234),rename_database($B,$I["collation"]));}else{$h=explode("\n",str_replace("\r","",$B));$Wm=true;$ih="";foreach($h
as$i){if(count($h)==1||$i!=""){if(!create_database($i,$I["collation"]))$Wm=false;$ih=$i;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($ih),lang(235),$Wm);}}else{if(!$I["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($B).(preg_match('~^[a-z0-9_]+$~i',$I["collation"])?" COLLATE $I[collation]":""),substr(ME,0,-1),lang(236));}}if(DB!="")page_header(lang(70).": ".h(DB),[lang(70)]);else
page_header(lang(76),[lang(76)]);$B=DB;if($_POST)$B=$I["name"];elseif(DB!="")$I["collation"]=db_collation(DB,collations());elseif(DIALECT=="sql"){foreach(get_vals("SHOW GRANTS")as$nf){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$nf,$_)&&$_[1]){$B=stripcslashes(idf_unescape("`$_[2]`"));break;}}}$Tb=Admin::get()->getCollations($I["collation"]?[$I["collation"]]:[]);echo"<form action='' method='post'>\n","<p>";if($_POST["add_x"]||strpos($B,"\n"))echo"<textarea id='name' name='name' rows='10' cols='40'>",h($B),"</textarea><br>\n";else
echo"<input class='input' name='name' id='name' value='",h($B),"' data-maxlength='64' autocapitalize='off' autofocus>\n";if($Tb)echo
html_select("collation",[""=>"(".lang(94).")"]+$Tb,$I["collation"]),doc_link(['sql'=>"charset-charsets.html",'mariadb'=>"reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",]),"\n";echo"<input type='submit' class='button default' value='",lang(118),"'>\n";if(DB!="")echo"<input type='submit' class='button' name='drop' value='".lang(172)."'>".confirm(lang(225,DB))."\n";elseif(!$_POST["add_x"]&&$_GET["db"]=="")echo"<button name='add_x' value='1' title='",lang(102),"' class='button light'>",icon_solo("add"),"</button>\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["scheme"])){$I=$_POST;if($_POST){$z=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$z,lang(237));else{$B=trim($I["name"]);$z
.=urlencode($B);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($B),$z,lang(238));elseif($_GET["ns"]!=$B)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($B),$z,lang(239));else
redirect($z);}}if($_GET["ns"]!="")page_header(lang(71).": ".h($_GET["ns"]),[lang(71)]);else
page_header(lang(77),[lang(77)]);if(!$I)$I["name"]=$_GET["ns"];echo"<form action='' method='post'>\n","<p>","<input class='input' name='name' id='name' value='",h($I["name"]),"' autocapitalize='off' autofocus>","<input type='submit' class='button default' value='",lang(118),"'>";if($_GET["ns"]!="")echo"<input type='submit' class='button' name='drop' value='".lang(172)."'>".confirm(lang(225,$_GET["ns"]))."\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["call"])){$oa=$_GET["name"]?:$_GET["call"];page_header(lang(240).": ".h($oa),[lang(240)]);$yl=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$Wf=[];$Mj=[];foreach($yl["fields"]as$r=>$l){if(substr($l["inout"],-3)=="OUT"&&DIALECT=='sql')$Mj[$r]="@".idf_escape($l["field"])." AS ".idf_escape($l["field"]);if(!$l["inout"]||substr($l["inout"],0,2)=="IN")$Wf[]=$r;}if($_POST){$yb=[];foreach($yl["fields"]as$w=>$l){$W="";if(in_array($w,$Wf)){$W=process_input($l);if($W===false)$W="''";if(isset($Mj[$w]))Connection::get()->query("SET @".idf_escape($l["field"])." = $W");}if(isset($Mj[$w]))$yb[]="@".idf_escape($l["field"]);elseif(in_array($w,$Wf))$yb[]=$W;}$F=(isset($_GET["callf"])?"SELECT ":"CALL ").($yl["returns"]&&$yl["returns"]["type"]=="record"?"* FROM ":"").table($oa)."(".implode(", ",$yb).")";$Mm=microtime(true);$G=Connection::get()->multiQuery($F);$Da=Connection::get()->getAffectedRows();echo
Admin::get()->formatSelectQuery($F,$Mm,!$G);if(!$G)echo"<p class='error'>".error()."\n";else{$nc=connect();if($nc)$nc->selectDatabase(DB);$Mi=false;do{$G=Connection::get()->storeResult();if($Mi&&Connection::get()->getError())echo"<p class='error'>".error()."\n";elseif(is_object($G))print_select_result($G,$nc);else
echo"<p class='message'>".lang(241,$Da)." <span class='time'>".@date("H:i:s")."</span>\n";}while($Mi=Connection::get()->nextResult());if($Mj)print_select_result(Connection::get()->query("SELECT ".implode(", ",$Mj)));}}echo"<form action='' method='post'>\n";if($Wf){echo"<table class='box'>\n";foreach($Wf
as$w){$l=$yl["fields"][$w];$B=$l["field"];echo"<tr><th>".Admin::get()->getFieldName($l);$X=isset($_POST["fields"][$B])?$_POST["fields"][$B]:"";if($X!=""){if($l["type"]=="set")$X=implode(",",$X);}input($l,$X,(string)(isset($_POST["function"][$B])?$_POST["function"][$B]:""));echo"\n";}echo"</table>\n";}echo"<p>\n","<input type='submit' class='button' value='",lang(240),"'>\n",input_token(),"</p>\n","</form>\n";$cc=$yl["comment"];if($cc!==null&&$cc!==""){$cc=h(trim($yl["comment"],"\n"));if(preg_match('~^ +~',$cc,$A)){preg_match_all("~^($A[0]|$)~m",$cc,$wh);if(count($wh[0])==substr_count($cc,"\n"))$cc=preg_replace("~^($A[0])~m","",$cc);}$cc=preg_replace('~(^|[^\n]\n)(Description|Parameters|Example)\n~',"$1\n<strong>$2</strong>\n",$cc);echo"<pre class='comment'>$cc</pre>\n";}}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$B=$_GET["name"];$I=$_POST;if($_POST&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$I["source"]=array_filter($I["source"],'strlen');ksort($I["source"]);$zn=[];foreach($I["source"]as$w=>$W)$zn[$w]=$I["target"][$w];$I["target"]=$zn;}if(DIALECT=="sqlite")$G=recreate_table($a,$a,[],[],[" $B"=>($I["drop"]?"":" ".format_foreign_key($I))]);else{$b="ALTER TABLE ".table($a);$G=($B==""||queries("$b DROP ".(DIALECT=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($B)));if(!$I["drop"])$G=queries("$b ADD".format_foreign_key($I));}queries_redirect(ME."table=".urlencode($a),($I["drop"]?lang(242):($B!=""?lang(243):lang(244))),(bool)$G);if(!$I["drop"])Admin::get()->addError(lang(245));}if($B!="")page_header(lang(246).": ".h($B),["table"=>$a,lang(246)]);else
page_header(lang(186).": ".h($a),["table"=>$a,lang(186)]);if($_POST){ksort($I["source"]);if($_POST["change"]||$_POST["change-js"])$I["target"]=[];else$I["source"][]="";}elseif($B!=""){$Ue=foreign_keys($a);$I=$Ue[$B];$I["source"][]="";}else{$I["table"]=$a;$I["source"]=[""];}echo"<form action='' method='post'>\n";$_m=array_keys(fields($a));if($I["db"]!="")Connection::get()->selectDatabase($I["db"]);if($I["ns"]!=""){$Hj=get_schema();set_schema($I["ns"]);}$el=array_keys(array_filter(table_status('',true),'AdminNeo\fk_support'));$zn=$el?array_keys(fields(in_array($I["table"],$el)?$I["table"]:reset($el))):[];$lj="this.form['change-js'].value = '1'; this.form.submit();";echo"<p>","<span id='label-table'>",lang(247),":</span> ",html_select("table",$el,$I["table"],$lj,"label-table");if(support("scheme")){$Ml=array_filter(Admin::get()->getSchemas(),function($Ll){return!information_schema(DB,$Ll);});echo"<span id='label-schema'>",lang(82),":</span> ",html_select("ns",$Ml,$I["ns"]!=""?$I["ns"]:$_GET["ns"],$lj,"label-schema");if($I["ns"]!="")set_schema($Hj);}elseif(DIALECT!="sqlite"){$Qc=[];foreach(Admin::get()->getDatabases()as$i){if(!information_schema($i))$Qc[]=$i;}echo"<span id='label-db'>",lang(248),":</span> ",html_select("db",$Qc,$I["db"]!=""?$I["db"]:$_GET["db"],$lj,"label-db");}echo
input_hidden("change-js"),"<noscript><input type='submit' class='button' name='change' value='",lang(249),"'></noscript>","</p>\n","<table>","<thead><tr><th id='label-source'>",lang(183),"<th id='label-target'>",lang(184),"</thead>\n";$Ng=0;foreach($I["source"]as$w=>$W){echo"<tr>","<td>".html_select("source[".(+$w)."]",[-1=>""]+$_m,$W,($Ng==count($I["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$w)."]",$zn,isset($I["target"][$w])?$I["target"][$w]:null,"","label-target");$Ng++;}echo"</table>\n","<noscript><p><input type='submit' class='button' name='add' value='",lang(250),"'></p></noscript>","<p>\n","<span id='label-delete'>".lang(96),":</span> ",html_select("on_delete",[-1=>""]+Driver::get()->getOnActions(),$I["on_delete"],"","label-delete"),"<span id='label-update'>".lang(95),":</span> ",html_select("on_update",[-1=>""]+Driver::get()->getOnActions(),$I["on_update"],"","label-update");if(DRIVER=='pgsql')echo
html_select("deferrable",['NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'],$I["deferrable"]);echo
doc_link(['sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"architecture/server-constraints/foreign-key-constraints",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-PARMS-REFERENCES",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"SQLRF01111",]),"</p>\n<p>","<input type='submit' class='button default' value='",lang(118),"'>";if($B!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$B));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["view"])){$a=$_GET["view"];$I=$_POST;$Ij="VIEW";if(DIALECT=="pgsql"&&$a!=""){$N=table_status1($a);$Ij=strtoupper($N["Engine"]);}if($_POST){$B=trim($I["name"]);$Ta=" AS\n$I[select]";$Hh=ME."table=".urlencode($B);$ci=lang(251);$T=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$B&&DIALECT!="sqlite"&&$T=="VIEW"&&$Ij=="VIEW")query_redirect((DIALECT=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($B).$Ta,$Hh,$ci);else{$An="adminneo_".uniqid();drop_create("DROP $Ij ".table($a),"CREATE $T ".table($B).$Ta,"DROP $T ".table($B),"CREATE $T ".table($An).$Ta,"DROP $T ".table($An),($_POST["drop"]?substr(ME,0,-1):$Hh),lang(252),$ci,lang(253),$a,$B);}}if(!$_POST&&$a!=""){$I=view($a);$I["name"]=$a;$I["materialized"]=($Ij!="VIEW");if($k=error())Admin::get()->addError($k);}if($a!="")page_header(lang(37).": ".h($a),["table"=>$a,lang(37)]);else
page_header(lang(254),[lang(254)]);echo"<form action='' method='post'>\n","<p>",lang(231),":","<input class='input' name='name' value='",h($I["name"]),"' data-maxlength='64' autocapitalize='off'>\n";if(support("materializedview"))echo
checkbox("materialized",1,$I["materialized"],lang(174));echo"</p>\n<p>";textarea("select",$I["select"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(118),"'>\n";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>\n",confirm(lang(225,$a));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["event"])){$ea=$_GET["event"];$ug=["YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND"];$Om=["ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE"];$I=$_POST;if($_POST){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($ea),substr(ME,0,-1),lang(255));elseif(in_array($I["INTERVAL_FIELD"],$ug)&&isset($Om[$I["STATUS"]])){$Kl="\nON SCHEDULE ".($I["INTERVAL_VALUE"]?"EVERY ".q($I["INTERVAL_VALUE"])." $I[INTERVAL_FIELD]".($I["STARTS"]?" STARTS ".q($I["STARTS"]):"").($I["ENDS"]?" ENDS ".q($I["ENDS"]):""):"AT ".q($I["STARTS"]))." ON COMPLETION".($I["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($ea!=""?lang(256):lang(257)),(bool)queries(($ea!=""?"ALTER EVENT ".idf_escape($ea).$Kl.($ea!=$I["EVENT_NAME"]?"\nRENAME TO ".idf_escape($I["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($I["EVENT_NAME"]).$Kl)."\n".$Om[$I["STATUS"]]." COMMENT ".q($I["EVENT_COMMENT"]).rtrim(" DO\n$I[EVENT_DEFINITION]",";").";"));}}if($ea!="")page_header(lang(258).": ".h($ea),[lang(258)]);else
page_header(lang(259),[lang(259)]);if(!$I&&$ea!=""){$J=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($ea));$I=reset($J);}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(231),"</th><td>","<input class='input' name='EVENT_NAME' value='",h($I["EVENT_NAME"]),"' data-maxlength='64' autocapitalize='off'>","</td></tr>\n","<tr><th title='datetime'>",lang(260),"</th><td>","<input class='input' name='STARTS' value='",h("$I[EXECUTE_AT]$I[STARTS]"),"'>","</td></tr>\n","<tr><th title='datetime'>",lang(261),"</th><td>","<input class='input' name='ENDS' value='",h($I["ENDS"]),"'>","</td></tr>\n","<tr><th>",lang(262),"</th><td>","<input type='number' name='INTERVAL_VALUE' value='",h($I["INTERVAL_VALUE"]),"' class='input size'> ",html_select("INTERVAL_FIELD",$ug,$I["INTERVAL_FIELD"]),"</td></tr>\n","<tr><th>",lang(164),"</th><td>",html_select("STATUS",$Om,$I["STATUS"]),"</td></tr>\n","<tr><th>",lang(48),"</th><td>","<input class='input' name='EVENT_COMMENT' value='",h($I["EVENT_COMMENT"]),"' data-maxlength='64'>","</td></tr>\n","<tr><th></th><td>",checkbox("ON_COMPLETION","PRESERVE",$I["ON_COMPLETION"]=="PRESERVE",lang(263)),"</td></tr>\n","</table>\n","<p>";textarea("EVENT_DEFINITION",$I["EVENT_DEFINITION"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(118),"'>";if($ea!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$ea));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["procedure"])){$oa=($_GET["name"]?:$_GET["procedure"]);$yl=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$I=$_POST;$I["fields"]=(array)$I["fields"];if($_POST&&!process_fields($I["fields"])){foreach($I["fields"]as$w=>$l){if($l["field"]=="")unset($I["fields"][$w]);}$fj=routine_id($oa,routine($_GET["procedure"],$yl));$Hi=routine_id($I["name"],$I);$f=create_routine($yl,$I);$Hh=substr(ME,0,-1);$ci=lang(264);if(!$_POST["drop"]&&$fj==$Hi&&(DIALECT!="sql"||Connection::get()->isMariaDB()))query_redirect(substr_replace($f,' OR REPLACE',6,0),$Hh,$ci);else{$An="adminneo_".uniqid();drop_create("DROP $yl $fj",$f,"DROP $yl $Hi",create_routine($yl,["name"=>$An]+$I),"DROP $yl ".routine_id($An,$I),$Hh,lang(265),$ci,lang(266),$oa,$I["name"]);}}if($oa!=""){$S=isset($_GET["function"])?lang(267):lang(268);page_header($S.": ".h($oa),[$S]);}else{$S=isset($_GET["function"])?lang(269):lang(270);page_header($S,[$S]);}if(!$_POST){if($oa=="")$I["language"]="sql";else{$I=routine($_GET["procedure"],$yl);$I["name"]=$oa;}}$Db=get_vals("SHOW CHARACTER SET");sort($Db);$zl=routine_languages();echo"<form action='' method='post' id='form'>\n","<p>",lang(231),": ","<input class='input' name='name' value='",h($I["name"]),"' data-maxlength='64' autocapitalize='off'>";if($zl)echo"<span id='label-language'>",lang(10),":</span> ",html_select("language",$zl,$I["language"],"","label-language");echo"<input type='submit' class='button default' value='",lang(118),"'>","</p>\n","<div class='scrollable'>\n","<table class='nowrap' id='edit-fields'>\n";edit_fields($I["fields"],$Db,$yl);if(isset($_GET["function"])){echo"<tbody><tr>";if(support("move_col"))echo"<th></th>";echo"<th>",lang(271),"</th>";edit_type("returns",(array)$I["returns"],$Db,[],(DIALECT=="pgsql"?["void","trigger"]:[]));echo"<td></td>","</tr></tbody>\n";}echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>";textarea("definition",$I["definition"],20);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(118),"'>";if($oa!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$oa));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["sequence"])){$qa=$_GET["sequence"];$I=$_POST;if($_POST){$z=substr(ME,0,-1);$B=trim($I["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($qa),$z,lang(272));elseif($qa=="")query_redirect("CREATE SEQUENCE ".idf_escape($B),$z,lang(273));elseif($qa!=$B)query_redirect("ALTER SEQUENCE ".idf_escape($qa)." RENAME TO ".idf_escape($B),$z,lang(274));else
redirect($z);}if($qa!="")page_header(lang(275).": ".h($qa),[h($qa)]);else
page_header(lang(276),[lang(277)]);if(!$I)$I["name"]=$qa;echo"<form action='' method='post'>\n","<p>","<input class='input' name='name' value='",h($I["name"]),"' autocapitalize='off'>","<input type='submit' class='button default' value='",lang(118),"'>";if($qa!="")echo"<input type='submit' class='button' name='drop' value='".lang(172)."'>".confirm(lang(225,$qa))."\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["type"])){function
enum_values($Wc){$X="'(?:[^']|'')*'";if(!preg_match('~^AS\s+ENUM\s*\(\s*('.$X.'(?:\s*,\s*'.$X.')*)\s*\)$~i',$Wc,$_))return
null;preg_match_all('~'.$X.'~',$_[1],$A);return$A[0];}function
add_enum_values($T,$dj,$Ei){$ij=enum_values($dj);$Li=enum_values($Ei);if($ij===null||$Li===null)return
null;$H=[];$r=0;foreach($Li
as$X){if($X===(isset($ij[$r])?$ij[$r]:null))$r++;else$H[]="ALTER TYPE ".idf_escape($T)." ADD VALUE $X".($r<count($ij)?" BEFORE ".$ij[$r]:"");}return$r==count($ij)?$H:null;}$ra=$_GET["type"];$I=$_POST;$T=($ra!=""?type_definition((int)Driver::get()->getUserTypeId($ra)):["kind"=>"","definition"=>""]);$Wi=($T["kind"]=='d'?"DOMAIN":"TYPE");if($_POST){$z=substr(ME,0,-1);$B=trim($I["name"]);$Ta=trim($I["as"]);$Ji=(preg_match('~^AS\s+(?!ENUM\b|RANGE\b|\()~i',$Ta)?"DOMAIN":"TYPE");$ci=lang(278);$b=(!$_POST["drop"]&&$ra!=""&&$Ji==$Wi?($Ta==$T["definition"]?[]:add_enum_values($ra,$T["definition"],$Ta)):null);if($b!==null){if($ra!=$B)$b[]="ALTER $Wi ".idf_escape($ra)." RENAME TO ".idf_escape($B);if(!$b)redirect($z);$te=false;foreach($b
as$F){if(!queries($F)){$te=true;break;}}queries_redirect($z,$ci,!$te);}else
drop_create("DROP $Wi ".idf_escape($ra),"CREATE $Ji ".idf_escape($B)." $Ta","","","",$z,lang(279),$ci,lang(280),$ra,$B);}if($ra!="")page_header(lang(281).": ".h($ra),[h($ra)]);else
page_header(lang(277),[lang(277)]);if(!$I){$I["name"]=$ra;$I["as"]=($ra!=""?$T["definition"]:"AS ");}echo"<form action='' method='post'>\n","<p>",lang(231).": <input class='input' name='name' value='".h($I['name'])."' autocapitalize='off'>\n",doc_link(['pgsql'=>"sql-createtype.html",],"?"),"</p>\n<p>";textarea("as",$I["as"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(118),"'>";if($ra!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$ra));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["check"])){$a=$_GET["check"];$B=$_GET["name"];$I=$_POST;if($I){if(DIALECT=="sqlite")$Wm=recreate_table($a,$a,[],[],[],"",[],"$B",($I["drop"]?"":$I["clause"]));else{$Wm=($B==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($B)));if(!$I["drop"])$Wm=(bool)queries("ALTER TABLE ".table($a)." ADD".($I["name"]!=""?" CONSTRAINT ".idf_escape($I["name"]):"")." CHECK ($I[clause])");}queries_redirect(ME."table=".urlencode($a),($I["drop"]?lang(282):($B!=""?lang(283):lang(284))),$Wm);}if($B!="")page_header(lang(285).": ".h($B),["table"=>$a,lang(285)]);else
page_header(lang(188).": ".h($a),["table"=>$a,lang(188)]);if(!$I){$Jb=Driver::get()->checkConstraints($a);$I=["name"=>$B,"clause"=>$Jb[$B]];}echo"<form action='' method='post'>\n","<p>";if(DIALECT!="sqlite")echo
lang(231).': <input name="name" value="'.h($I["name"]).'" class="input" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(['sql'=>"create-table-check-constraints.html",'mariadb'=>"reference/sql-statements/data-definition/constraint",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",],"?"),"</p>\n<p>";textarea("clause",$I["clause"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(118),"'>";if($B!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$B));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$B=isset($_GET["name"])?$_GET["name"]:"";$co=trigger_options();$I=trigger($B,$a)+["Trigger"=>$a."_bi"];if($_POST){if(in_array($_POST["Timing"],$co["Timing"])&&in_array($_POST["Event"],$co["Event"])&&in_array($_POST["Type"],$co["Type"])){$jj=" ON ".table($a);$xd="DROP TRIGGER ".idf_escape($B).(DIALECT=="pgsql"?$jj:"");$Hh=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($xd,$Hh,lang(286));else{if($B!="")queries($xd);queries_redirect($Hh,($B!=""?lang(287):lang(288)),(bool)queries(create_trigger($jj,$_POST)));if($B!="")queries(create_trigger($jj,$I+["Type"=>reset($co["Type"])]));}}$I=$_POST;}if($B!="")page_header(lang(289).": ".h($B),["table"=>$a,lang(289)]);else
page_header(lang(190).": ".h($a),["table"=>$a,lang(190)]);echo"<form action='' method='post' id='form'>\n","<table class='box box-light'>\n","<tr><th id='label-time'>",lang(290),"</th><td>",html_select("Timing",$co["Timing"],$I["Timing"],"triggerChange(/^".js_escape_re($a)."_[ba][iud]$/, '".js_escape($a)."', this.form);","label-time"),"</td></tr>\n","<tr><th id='label-event'>",lang(291),"</th><td>",html_select("Event",$co["Event"],$I["Event"],"this.form['Timing'].onchange();","label-event");if(in_array("UPDATE OF",$co["Event"]))echo" <input name='Of' value='".h($I["Of"])."' class='input hidden'>";echo"</td></tr>\n","<tr><th id='label-type'>",lang(45),"</th><td>",html_select("Type",$co["Type"],$I["Type"],"","label-type"),"</td></tr>\n","</table>\n","<p>",lang(231),"<input class='input' name='Trigger' value='",h($I["Trigger"]),"' data-maxlength='64' autocapitalize='off'>","</p>\n",script("gid('form')['Timing'].onchange();"),"<p>";textarea("Statement",$I["Statement"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(118),"'>";if($B!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$B));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["user"])){$sa=$_GET["user"];$Kk=[""=>["All privileges"=>""]];foreach(get_rows("SHOW PRIVILEGES")as$I){foreach(explode(",",($I["Privilege"]=="Grant option"?"":$I["Context"]))as$tc)$Kk[$tc=="File access on server"?"Server Admin":$tc][$I["Privilege"]]=$I["Comment"];}unset($Kk["Server Admin"]["Usage"]);foreach($Kk["Tables"]as$w=>$W)unset($Kk["Databases"][$w]);$Gi=[];if($_POST){foreach($_POST["objects"]as$w=>$W)$Gi[$W]=(array)$Gi[$W]+(array)$_POST["grants"][$w];}$pf=[];if(isset($_GET["host"])&&($G=Connection::get()->query("SHOW GRANTS FOR ".q($sa)."@".q($_GET["host"])))){while($I=$G->fetchRow()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$I[0],$_)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$_[1],$A,PREG_SET_ORDER)){foreach($A
as$W){if($W[1]!="USAGE")$pf["$_[2]$W[2]"][$W[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$I[0]))$pf["$_[2]$W[2]"]["GRANT OPTION"]=true;}}}}$pk=!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8");if($_POST){$hj=(isset($_GET["host"])?q($sa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $hj",ME."privileges=",lang(292));else{$Ki=q($_POST["user"])."@".q($_POST["host"]);$hk=$_POST["pass"];$Cc=false;$G=true;if($hj!=$Ki){$Cc=(bool)queries("CREATE USER $Ki IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($hk));$G=$Cc;}elseif($hk!="")$G=(bool)queries("SET PASSWORD FOR $Ki = ".($pk||$_POST["hashed"]?q($hk):"PASSWORD(".q($hk).")"));if($G){$vl=[];foreach($Gi
as$Wi=>$nf){if(isset($_GET["grant"]))$nf=array_filter($nf);$nf=array_keys($nf);if(isset($_GET["grant"]))$vl=array_diff(array_keys(array_filter($Gi[$Wi],'strlen')),$nf);elseif($hj==$Ki){$ej=array_keys((array)$pf[$Wi]);$vl=array_diff($ej,$nf);$nf=array_diff($nf,$ej);unset($pf[$Wi]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$Wi,$_)&&(!grant(false,$vl,$_[2],$_[1],$Ki)||!grant(true,$nf,$_[2],$_[1],$Ki))){$G=false;break;}}}if($G&&isset($_GET["host"])){if($hj!=$Ki)queries("DROP USER $hj");elseif(!isset($_GET["grant"])){foreach($pf
as$Wi=>$vl){if(preg_match('~^(.+)(\(.*\))?$~U',$Wi,$_))grant(false,array_keys($vl),$_[2],$_[1],$Ki);}}}if($G&&!Queries::$queries)redirect(ME."privileges=");queries_redirect(ME."privileges=",(isset($_GET["host"])?lang(293):lang(294)),$G);if($Cc)Connection::get()->query("DROP USER $Ki");}}$S=isset($_GET["host"])?lang(6).": ".h("$sa@$_GET[host]"):lang(197);$Mn=isset($_GET["host"])?h($sa):lang(197);page_header($S,["privileges"=>['',lang(73)],$Mn]);if($_POST){$I=$_POST;$pf=$Gi;}else{$I=$_GET+["host"=>Connection::get()->getValue("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")];if($pf)$pf[".*"]=[];elseif(DB!="")$pf[idf_escape(addcslashes(DB,"%_\\")).".*"]=[];else$pf["*.* "]=[];}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(5),"</th>","<td><input class='input' name='host' data-maxlength='60' value='",h($I["host"]),"' autocapitalize='off'></td>\n","<tr><th>",lang(6),"</th>","<td><input class='input' name='user' data-maxlength='80' value='",h($I["user"]),"' autocapitalize='off'></td>\n",'<tr><th>',lang(30),"</th>","<td><input class='input' name='pass' id='pass' value='",h($I["pass"]),"' autocomplete='new-password'>";if(!$pk)echo
checkbox("hashed",1,$I["hashed"],lang(295),"typePassword(this.form['pass'], this.checked);");echo"</td>\n";if(!$I["hashed"])echo
script("typePassword(gid('pass'));");echo"</table>\n","<div class='scrollable'><table class='checkable'>\n","<thead><tr><th colspan='2'>".lang(73).doc_link(['sql'=>"grant.html#priv_level","mariadb"=>"reference/sql-statements/account-management-sql-statements/grant#privilege-levels"])."</th>";$r=0;foreach($pf
as$Wi=>$nf){echo"<th>";if($Wi=="*.*")echo"*.*",input_hidden("objects[$r]","*.*");else
echo"<input class='input' name='objects[$r]' value='".h(trim($Wi))."' size='10' autocapitalize='off'>";echo"</th>";$r++;}echo"</tr></thead>\n";foreach([""=>"","Server Admin"=>lang(5),"Databases"=>lang(31),"Tables"=>lang(9),"Procedures"=>lang(296),]as$tc=>$bd){foreach((array)$Kk[$tc]as$Jk=>$cc){echo"<tr>";if($bd)echo"<td>$bd</td>";echo"<td".(!$bd?" colspan='2'":"").' lang="en" title="'.h($cc).'">'.h($Jk)."</td>";$r=0;foreach($pf
as$Wi=>$nf){$B="'grants[$r][".h(strtoupper($Jk))."]'";$X=$nf[strtoupper($Jk)];$Ok=strpos($Wi,"@")!==false;$Fi=$Wi==".*";$Ja=$Jk=="All privileges";$of=$Jk=="Grant option";if($Wi=="*.*"&&$Jk=="Proxy")echo"<td></td>";elseif($Ok&&$Jk!="Proxy"&&!$of)echo"<td></td>";elseif($tc=="Server Admin"&&$Wi!=(isset($pf["*.*"])?"*.*":".*")&&!(($Ok||$Fi)&&$Jk=="Proxy"))echo"<td></td>";elseif(isset($_GET["grant"]))echo"<td><select name=$B>"."<option></option>"."<option value='1'".($X?" selected":"").">".lang(297)."</option>"."<option value='0'".($X=="0"?" selected":"").">".lang(298)."</option>"."</select></td>";else{echo"<td class='center'><label class='block'>","<input type='checkbox' name=$B value='1'".($X?" checked":"").($Ja?" id='grants-$r-all'":(!$of?" class='grants-$r'":"")).">";if($Ja)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheckAll('.grants-$r'); };");elseif(!$of)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$r-all'); };");echo"</label>";}$r++;}echo"</tr>";}}echo"</table></div>\n","<p>","<input type='submit' class='button default' value='",lang(118),"'>\n";if(isset($_GET["host"]))echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>\n",confirm(lang(225,"$sa@$_GET[host]"));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST){$Zg=0;foreach((array)$_POST["kill"]as$W){if(kill_process($W))$Zg++;}queries_redirect(ME."processlist=",lang(299,$Zg),$Zg||!$_POST["kill"]);}}page_header(lang(162),[lang(162)]);echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap checkable'>\n";$r=-1;foreach(process_list()as$r=>$I){if(!$r){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($I
as$w=>$W)echo"<th>$w".doc_link(['sql'=>"show-processlist.html#processlist_".strtolower($w),'mariadb'=>"reference/sql-statements/administrative-sql-statements/show/show-processlist",'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"REFRN30223",]);echo"</thead>\n","<tbody>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$I[DIALECT=="sql"?"Id":"pid"],0):"");foreach($I
as$w=>$W)echo"<td>".($W!=""&&((DIALECT=="sql"&&$w=="Info"&&preg_match("~Query|Killed~",$I["Command"]))||(DIALECT=="pgsql"&&$w=="query")||(DIALECT=="oracle"&&$w=="sql_text"))?"<code class='jush-".DIALECT."'>".truncate_utf8($W,100).'</code> <a href="'.h(ME.($I["db"]!=""?"db=".urlencode($I["db"])."&":"")."sql=".urlencode($W)).'">'.icon("edit").lang(300).'</a>':h($W));echo"\n";}if($r>=0)echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: event => tableClick(event, true)});");echo"</table>\n","</div>\n","<p>";if(support("kill"))echo($r+1)."/".lang(301,max_connections()),"<p><input type='submit' class='button' value='".lang(302)."'>\n";echo
input_token(),"</p>\n","</form>\n",script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$Q=table_status1($a);$v=indexes($a);$m=fields($a);$Ue=column_foreign_keys($a);$Zi=$Q["Oid"];$wl=[];$d=[];$Pl=[];$zj=[];$Fn=null;foreach($m
as$w=>$l){$B=Admin::get()->getFieldName($l);$_i=html_entity_decode(strip_tags($B),ENT_QUOTES);if(isset($l["privileges"]["select"])&&$B!=""){$d[$w]=$_i;if(is_shortable($l))$Fn=Admin::get()->processSelectionLength();}if(isset($l["privileges"]["where"])&&$B!="")$Pl[$w]=$_i;if(isset($l["privileges"]["order"])&&$B!="")$zj[$w]=$_i;$wl+=$l["privileges"];}list($K,$qf)=Admin::get()->processSelectionColumns($d,$v);$K=array_unique($K);$qf=array_unique($qf);$Fg=count($qf)<count($K);$Z=Admin::get()->processSelectionSearch($m,$v);$xj=Admin::get()->processSelectionOrder($m,$v);$y=Admin::get()->processSelectionLimit();$Ga=$K;if(DIALECT=="pgsql"||DIALECT=="mssql"||DIALECT=="clickhouse"){foreach((array)$_GET["columns"]as$w=>$W){if(isset($Ga[$w])&&$W["fun"])$Ga[$w].=" AS ".idf_escape(apply_sql_function($W["fun"],($W["col"]!=""?$W["col"]:"*")));}}if($_GET["modify"]&&!Admin::get()->isDataEditAllowed())redirect(ME."select=".urlencode($a));if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$oo=>$I){$Ta=convert_field($m[key($I)]);$K=[$Ta?:idf_escape(key($I))];$Z[]=where_check($oo,$m);$H=Driver::get()->select($a,$K,$Z,$K);if(is_object($H))echo
first($H->fetchRow());}exit;}$Fk=$ro=[];foreach($v
as$u){if($u["type"]=="PRIMARY"){$Fk=array_flip($u["columns"]);$ro=($K?$Fk:[]);foreach($ro
as$w=>$W){if(in_array(idf_escape($w),$K))unset($ro[$w]);}break;}}if($Zi&&!$Fk){$Fk=$ro=[$Zi=>0];$v[]=["type"=>"PRIMARY","columns"=>[$Zi]];}$M=Admin::get()->getSettings();if($_POST){$dp=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$Jb=[];foreach($_POST["check"]as$Eb)$Jb[]=where_check($Eb,$m);$dp[]="((".implode(") OR (",$Jb)."))";}$dp=($dp?"\nWHERE ".implode(" AND ",$dp):"");if($_POST["export"]){$M->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers($a);Admin::get()->dumpTable($a,"");$ef=($K?implode(", ",$Ga):"*").convert_fields($d,$m,$K)."\nFROM ".table($a);$tf=($qf&&$Fg?"\nGROUP BY ".implode(", ",$qf):"").($xj?"\nORDER BY ".implode(", ",$xj):"");if(!is_array($_POST["check"])||$Fk)$F="SELECT $ef$dp$tf";else{$lo=[];foreach($_POST["check"]as$W)$lo[]="(SELECT".limit($ef,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$m).$tf,1).")";$F=implode(" UNION ALL ",$lo);}Admin::get()->dumpData($a,"table",$F);exit;}if($_POST["save"]||$_POST["delete"]){$G=true;$Da=0;$om=[];if(!$_POST["delete"]){$Yl=array_keys($_POST["fields"]+$_POST["function"]);foreach($Yl
as$B){$W=process_input($m[$B]);if($W!==null&&($_POST["clone"]||$W!==false))$om[idf_escape($B)]=($W!==false?$W:idf_escape($B));}}if($_POST["delete"]||$om){if($_POST["clone"])$F="INTO ".table($a)." (".implode(", ",array_keys($om)).")\nSELECT ".implode(", ",$om)."\nFROM ".table($a);if($_POST["all"]||($Fk&&is_array($_POST["check"]))||$Fg){$G=($_POST["delete"]?Driver::get()->delete($a,$dp):($_POST["clone"]?queries("INSERT $F$dp".Driver::get()->getInsertReturningSql($a)):Driver::get()->update($a,$om,$dp)));$Da=Connection::get()->getAffectedRows();if(is_object($G))$Da+=$G->getRowsCount();}else{foreach((array)$_POST["check"]as$W){$cp="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$m);$G=($_POST["delete"]?Driver::get()->delete($a,$cp,1):($_POST["clone"]?queries("INSERT".limit1($a,$F,$cp)):Driver::get()->update($a,$om,$cp,1)));if(!$G)break;$Da+=Connection::get()->getAffectedRows();}}}$ci=lang(303,$Da);if($_POST["clone"]&&$G&&$Da==1){$jh=last_id($G);if($jh)$ci=lang(219," $jh");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$ci,(bool)$G);if(!$_POST["delete"]){$Kd=array_filter($m,function($l){return!(isset($l["generated"])?$l["generated"]:null);});edit_form($a,$Kd,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])Admin::get()->addError(lang(304));else{$Wm=true;$Da=0;foreach($_POST["val"]as$oo=>$I){$om=[];foreach($I
as$w=>$W){$w=bracket_escape($w,true);if(isset($m[$w]["full_type"]))Admin::get()->detectJson($m[$w]["full_type"],$W,false);$om[idf_escape($w)]=(preg_match('~char|text~',$m[$w]["type"])||$W!=""?Admin::get()->processFieldInput($m[$w],$W):"NULL");}$Wm=(bool)Driver::get()->update($a,$om," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($oo,$m),($Fg||$Fk?0:1)," ");if(!$Wm)break;$Da+=Connection::get()->getAffectedRows();}queries_redirect(remove_from_uri(),lang(303,$Da),$Wm);}}elseif(!is_string($n=get_file("csv_file",true)))Admin::get()->addError(upload_error($n));elseif(!preg_match('~~u',$n))Admin::get()->addError(lang(305));else{$M->updateParameter("exportFormat",$_POST["import_format"]);$Xb=array_keys($m);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$n,$A);$Da=count($A[0]);Driver::get()->begin();$Zl=($_POST["import_format"]=="csv;"?";":($_POST["import_format"]=="tsv"?"\t":","));$J=[];foreach($A[0]as$w=>$W){preg_match_all("~((?>\"[^\"]*\")+|[^$Zl]*)$Zl~",$W.$Zl,$Nh);if(!$w&&!array_diff($Nh[1],$Xb)){$Xb=$Nh[1];$Da--;}else{$om=[];foreach($Nh[1]as$r=>$Qb)$om[idf_escape($Xb[$r])]=($Qb==""&&$m[$Xb[$r]]["null"]?"NULL":q(preg_match('~^".*"$~s',$Qb)?str_replace('""','"',substr($Qb,1,-1)):$Qb));$J[]=$om;}}$Wm=!$J||Driver::get()->insertUpdate($a,$J,$Fk);if($Wm)Driver::get()->commit();queries_redirect(remove_from_uri("page"),lang(306,$Da),$Wm);Driver::get()->rollback();}}$mn=Admin::get()->getTableName($Q);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(56).": $mn",[$mn]);$pg=null;if(isset($wl["insert"])||!support("table")){$pg=[];foreach((array)$_GET["where"]as$W){if(isset($Ue[$W["col"]])&&count($Ue[$W["col"]])==1&&($W["op"]=="="||(!$W["op"]&&(is_array($W["val"])||!preg_match('~[_%]~',$W["val"])))))$pg["preset"."[".bracket_escape($W["col"])."]"]=$W["val"];}}Admin::get()->printTableMenu($Q,$pg);if(!$d&&support("table"))echo"<p class='error'>".lang(307).($m||error()==""?".":": ".error())."\n";else{echo"<form id='form' action=''>\n","<div hidden>";hidden_fields_get();if(DB!=""){echo
input_hidden("db",DB);if(isset($_GET["ns"]))echo
input_hidden("ns",$_GET["ns"]);}echo
input_hidden("select",$a),"<input type='submit' class='button' value='".lang(56)."'>","</div>\n","<div class='field-sets'>\n";Admin::get()->printSelectionColumns($K,$d);Admin::get()->printSelectionSearch($Z,$Pl,$v);Admin::get()->printSelectionOrder($xj,$zj,$v);Admin::get()->printSelectionLimit($y);Admin::get()->printSelectionLength($Fn);Admin::get()->printSelectionAction($v);echo"</div>\n</form>\n";foreach((array)$_GET["where"]as$W){if($W["op"]=="SQL"&&!is_same_origin()){echo"<p class='error'>".lang(157)." ".lang(158)."\n";page_footer();exit;}}$D=isset($_GET["page"])?$_GET["page"]:null;if($D=="last"){$bf=Connection::get()->getValue(count_rows($a,$Z,$Fg,$qf));$D=$y?(int)floor(max(0,intval($bf)-1)/$y):0;}else{$bf=false;$D=(int)$D;}$Ql=$Ga;$rf=$qf;if(!$Ql){$Ql[]="*";$vc=convert_fields($d,$m,$K);if($vc)$Ql[]=substr($vc,2);}foreach($K
as$w=>$W){$l=$m[idf_unescape($W)];if($l&&($Ta=convert_field($l)))$Ql[$w]="$Ta AS $W";}if(!$Fg&&$ro){foreach($ro
as$w=>$W){$Ql[]=idf_escape($w);if($rf)$rf[]=idf_escape($w);}}$G=Driver::get()->select($a,$Ql,$Z,$rf,$xj,$y,$D,true);if(!is_object($G))echo"<p class='error'>".(error()?:lang(125))."\n";else{if(DIALECT=="mssql"&&$D)$G->seek($y*$D);$J=[];while($I=$G->fetchAssoc()){if($D&&DIALECT=="oracle")unset($I["RNUM"]);$J[]=$I;}if($_GET["modify"]&&$J){$Vh=max_input_vars(count($J[0])+1,20);echo($Vh&&count($J)>$Vh?"<p class='error'>".max_input_vars_error()."\n":"");}echo"<form id='selection_form' action='' method='post' enctype='multipart/form-data'>\n","<div class='table-footer-parent'>\n";if($_GET["page"]!="last"&&$y&&$qf&&$Fg&&DIALECT=="sql")$bf=Connection::get()->getValue(" SELECT FOUND_ROWS()");$Ld=false;if(!$J)echo"<p class='message'>".lang(92)."\n";else{$hb=Admin::get()->getBackwardKeys($a,$mn);echo"<div class='scrollable'>\n","<table id='table' class='nowrap checkable'>\n","<thead><tr>";if($qf||!$K){echo"<th class='actions'><input type='checkbox' id='all-page' class='jsonly' title='".lang(308)."'>".script("gid('all-page').onclick = partial(formCheck, /^check/);","");if(Admin::get()->isDataEditAllowed())echo" <a href='",h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."' title='",lang(309),"'>",icon_solo("edit-all"),"</a>";}$Bi=[];$Vi=[];$if=[];reset($K);$Yk=1;foreach($J[0]as$w=>$W){if(!isset($ro[$w])){$Tl=key($K);$W=isset($_GET["columns"][$Tl])?$_GET["columns"][$Tl]:[];$l=$m[$K?($W?$W["col"]:current($K)):$w];$B=($l?Admin::get()->getFieldName($l,$Yk):(isset($W["fun"])?"*":h($w)));if($B!=""){$Yk++;$Bi[$w]=$B;$Vi[$w]=($l&&preg_match(number_type(),$l["type"])&&!preg_match('~\[~',$l["full_type"]))||preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|UNIX_TIMESTAMP|TIME_TO_SEC|COUNT|SUM)\(~',(string)current($K));$c=idf_escape($w);$Pf=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($w);$bd="&desc%5B0%5D=1";$yj=isset($xj[0])?$xj[0]:"";$xm=preg_replace('~ DESC( NULLS LAST)?$~','',$yj);$zm=($xm==$c||$xm==$w);echo"<th id='th[".h(bracket_escape($w))."]'".($Vi[$w]?" class='number'":"").($zm?" aria-sort='".($xm==$yj?"ascending":"descending")."'":"").">";$hf=apply_sql_function(isset($W["fun"])?$W["fun"]:null,$B);$ym=isset($l["privileges"]["order"])||(isset($W["fun"])?$W["fun"]:null);if($ym)echo'<a href="',h($Pf.($zm&&$xm==$yj?$bd:'')),'">',"$hf</a>";else
echo$hf;echo"<span class='column'>";if($ym)echo"<a href='".h($Pf.$bd)."' title='".lang(63)."' class='button light'>",icon_solo("arrow-down"),"</a>";if(!isset($W["fun"])&&isset($l["privileges"]["where"]))echo"<a href='#fieldset-search' title='".lang(60)."' class='button light jsonly'>",icon_solo("search"),"</a>",script("qsl('a').onclick = partial(selectSearch, '".js_escape($w)."');");echo"</span>";}$if[$w]=isset($W["fun"])?$W["fun"]:null;next($K);}}$rh=[];if($_GET["modify"]){foreach($J
as$I){foreach($I
as$w=>$W)$rh[$w]=max($rh[$w],min(40,strlen(utf8_decode($W))));}}if($hb)echo"<th>".lang(19)."</th>";echo"</thead>\n","<tbody>\n";if(is_ajax())ob_end_clean();foreach(Admin::get()->fillForeignDescriptions($J,$Ue)as$yi=>$I){$no=unique_array($J[$yi],$v);if(!$no){$no=[];reset($K);foreach($J[$yi]as$w=>$W){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($K)))$no[$w]=$W;next($K);}}$oo="";foreach($no
as$w=>$W){$l=isset($m[$w])?$m[$w]:null;$Eg=$l&&is_blob($l);if((DIALECT=="sql"||DIALECT=="pgsql")&&$l&&($Eg||preg_match('~char|text|enum|set~',$l["type"]))&&strlen($W)>64){$w=(strpos($w,'(')?$w:idf_escape($w));$w="MD5(".($Eg||DIALECT!='sql'||preg_match("~^utf8~",isset($l["collation"])?$l["collation"]:"")?$w:"CONVERT($w USING ".charset(Connection::get()).")").")";$W=md5($Eg?(string)Connection::get()->formatValue($W,$l):$W);}$oo
.="&".($W!==null?urlencode("where[".bracket_escape($w)."]")."=".urlencode($W===false?"f":$W):"null%5B%5D=".urlencode($w));}echo"<tr>";if($qf||!$K){echo"<td class='actions'>",checkbox("check[]",substr($oo,1),in_array(substr($oo,1),(array)$_POST["check"]));if(!$Fg&&Admin::get()->isDataEditAllowed())echo" <a href='",h(ME."edit=".urlencode($a).$oo),"' class='edit' title='",lang(39),"'>",icon_solo("edit"),"</a>";}reset($K);foreach($I
as$w=>$W){if(isset($Bi[$w])){$c=current($K);$l=isset($m[$w])?$m[$w]:null;$z="";if($l&&is_blob($l)&&$W!="")$z=ME.'download='.urlencode($a).'&field='.urlencode($w).$oo;if(!$z&&$W!==null){foreach((array)$Ue[$w]as$p){if(count($Ue[$w])==1||end($p["source"])==$w){$z="";foreach($p["source"]as$r=>$_m)$z
.=where_link($r,$p["target"][$r],$J[$yi][$_m]);$z=($p["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($p["db"]),ME):ME).'select='.urlencode($p["table"]).$z;if($p["ns"])$z=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($p["ns"]),$z);if(count($p["source"])==1)break;}}}if($c=="COUNT(*)"){$z=ME."select=".urlencode($a);$r=0;foreach((array)$_GET["where"]as$V){if(!array_key_exists($V["col"],$no))$z
.=where_link($r++,$V["col"],$V["val"],$V["op"]);}foreach($no
as$Qg=>$V)$z
.=where_link($r++,$Qg,$V);}$Ri=$W===null;$Qf=select_value($W,$z,$l,$Fn);$ce=bracket_escape($w);$s=h("val[$oo][$ce]");$_k=isset($_POST["val"][$oo][$ce])?$_POST["val"][$oo][$ce]:null;$vo=isset($l["privileges"]["update"])?$l["privileges"]["update"]:false;$Jd=!is_array($W)&&!($l&&is_blob($l))&&is_utf8((string)$W)&&$J[$yi][$w]==$W&&!$if[$w]&&!(isset($l["generated"])?$l["generated"]:false);$T=($c&&preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$c,$A)?$m[idf_unescape($A[2])]["type"]:(isset($l["type"])?$l["type"]:null));$ri=$T=="money"||($c&&preg_match('~^SUM\((.+)\)~',$c,$A)&&$m[idf_unescape($A[1])]["type"])=="money";$Cn=$T&&preg_match('~text|json|lob~',$T);$Nb=$Vi[$w]&&($Ri||is_numeric(strip_tags($Qf))||$ri)?"class='number'":"";echo"<td id='$s' $Nb";if(($_GET["modify"]&&$Jd&&!$Ri)||$_k!==null){$Ld=true;$wf=h($_k!==null?$_k:$W);echo" data-editing='true'>".($Cn?"<textarea name='$s' cols='30' rows='".(substr_count($W,"\n")+1)."'>$wf</textarea>":"<input class='input' name='$s' value='$wf' size='$rh[$w]'>");}else{$Ih=strpos($Qf,"<i>…</i>");if($vo)echo" data-text='".($Ih?2:($Cn?1:0))."'".($Jd?"":" data-warning='".lang(310)."'");echo">$Qf";}}next($K);}if($hb){echo"<td>";Admin::get()->printBackwardKeys($hb,$J[$yi]);echo"</td>";}echo"</tr>\n";}if(is_ajax())exit;echo"</tbody>\n",script("mixin(qs('#table tbody'), {onclick: event => tableClick(event, false, ".(Admin::get()->isDataEditAllowed()?"true":"false")."), ondblclick: event => tableClick(event, true), onkeydown: onEditingKeydown});"),"</table>\n",script("initToggles(gid('table'));"),"</div>\n";}if(!is_ajax()){if($J||$D){$fe=true;if($_GET["page"]!="last"){if(!$y||(count($J)<$y&&($J||!$D)))$bf=($D?$D*$y:0)+count($J);elseif(DIALECT!="sql"||!$Fg){$bf=($Fg?false:found_rows($Q,$Z));if($bf<max(1e4,2*($D+1)*$y))$bf=first(slow_query(count_rows($a,$Z,$Fg,$qf)));elseif(DIALECT=='sql'||DIALECT=='pgsql')$fe=false;}}$Rj=($y&&($bf===false||$bf>$y||$D));if($Rj){if(($bf===false?count($J)+1:$bf-$D*$y)>$y)echo'<p class="links">','<a href="',h(remove_from_uri("page")."&page=".($D+1)),'" class="loadmore">',icon("expand"),lang(311),'</a>',script("qsl('a').onclick = partial(loadNextPage, $y, '".js_escape(lang(312))."');","");echo"\n";}echo"<div class='table-footer'><div class='field-sets'>\n";if($Rj){$Th=($bf===false?$D+(count($J)>=$y?2:1):(int)floor(($bf-1)/$y));$td="<li>…</li>";echo"<fieldset><legend>".lang(313)."</legend>";if(DIALECT!="simpledb"){echo"<div id='fieldset-pagination' class='fieldset-content'><ul class='pagination'>",pagination(0,$D);if($D>5)echo$td;for($r=max(1,$D-4);$r<min($Th,$D+5);$r++)echo
pagination($r,$D);if($Th>0){if($D+5<$Th)echo$td;echo($fe&&$bf!==false?pagination($Th,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$Th'>".lang(314)."</a>");}echo"</ul></div>";}else{echo"<div id='fieldset-pagination'><ul class='pagination'>",pagination(0,$D);if($D>1)echo$td;if($D)echo
pagination($D,$D);if($Th>$D){echo
pagination($D+1,$D);if($Th>$D+1)echo$td;}echo"</ul></div>";}echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(315)."</legend><div class='fieldset-content'>";$ld=($fe?"":"~ ").$bf;echo
checkbox("all",1,0,($bf!==false?($fe?"":"~ ").lang(201,$bf):""),"countRows.call(this, '$ld');")."\n","</div></fieldset>\n";if(Admin::get()->isDataEditAllowed()){echo"<fieldset",($_GET["modify"]?'':' class="jsonly"'),">","<legend>",lang(309),"</legend>";$Fl=($_GET["modify"]?"":" data-inline-edit='1'".($Ld?"":" disabled"));echo"<div class='fieldset-content'",($_GET["modify"]?"":" title='".lang(304)."'"),">","<input type='submit' class='button' id='modify-save' value='",lang(118),"'",$Fl,">","</div>","</fieldset>\n","<fieldset>","<legend>",lang(171)," <span id='selected'></span></legend>","<div class='fieldset-content'>","<input type='submit' class='button' name='edit' value='",lang(39),"'> ","<input type='submit' class='button' name='clone' value='",lang(300),"'> ","<input type='submit' class='button' name='delete' value='",lang(122),"'>",confirm(),"</div>","</fieldset>\n";}$Jc=Admin::get()->getDataDumpFormats();foreach((array)$_GET["columns"]as$c){if($c["fun"]){unset($Jc['sql']);break;}}if($Jc){print_fieldset_start("export",lang(75)." <span id='selected2'></span>","export");echo
html_select("format",$Jc,$M->getParameter("exportFormat"));$Nj=Admin::get()->getDumpOutputs();echo($Nj?" ".html_select("output",$Nj,$M->getParameter("exportOutput")):"")," <input type='submit' class='button' name='export' value='".lang(75)."'>\n";print_fieldset_end("export");}echo"</div></div>\n",script("initTableFooter()");}echo"</div>\n";if(Admin::get()->isDataEditAllowed()){echo"<p>","<a href='#import'>",icon("import"),lang(74),"</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"</p>","<p id='import'",($_POST["import"]?"":" class='hidden'"),">";if(ini_bool("file_uploads"))echo"<input type='file' name='csv_file'> ",html_select("import_format",["csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"],$M->getParameter("exportFormat"))," <input type='submit' class='button default' name='import' value='".lang(74)."'>",file_upload_form_script("selection_form","csv_file");else
echo
lang(208);echo"</p>";}echo
input_token(),"</form>\n",(!$qf&&$K?"":script("tableCheck();"));}else
echo"</div>\n";}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$N=isset($_GET["status"]);$S=$N?lang(164):lang(163);page_header($S,[$S]);$Ko=($N?Admin::get()->getStatusVariables():Admin::get()->getServerVariables());if(!$Ko)echo"<p class='message'>",lang(92),"</p>\n";else{echo"<div class='scrollable'><table>\n";foreach($Ko
as$I){echo"<tr>";$w=array_shift($I);echo"<th><code class='jush-".DIALECT.($N?"status":"set")."'>".h($w)."</code></th>";foreach($I
as$W)echo"<td>",nl2br(h($W)),"</td>";echo"</tr>\n";}echo"</table></div>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$Zm=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$g=[];$Oc=null;foreach(table_status()as$B=>$Q){$g["Comment-$B"]=h($Q["Comment"]);if(!is_view($Q)||preg_match('~materialized~i',$Q["Engine"])){$g["Engine-$B"]=h($Q["Engine"]);$Sb=isset($Q["Collation"])?$Q["Collation"]:"";if($Sb==""){if($Oc===null)$Oc=(string)db_collation(DB,collations());$Sb=$Oc;}$g["Collation-$B"]=h($Sb);foreach($Zm+["Auto_increment"=>0,"Rows"=>0]as$w=>$W){if($Q[$w]!=""){$W=format_number($Q[$w]);if($W>=0)$g["$w-$B"]=($w=="Rows"?format_rows($Q):$W);if(isset($Zm[$w]))$Zm[$w]+=($Q["Engine"]!="InnoDB"||$w!="Data_free"?$Q[$w]:0);}elseif(array_key_exists($w,$Q))$g["$w-$B"]="?";}}}if(function_exists('AdminNeo\db_status'))$Zm=db_status();foreach($Zm
as$w=>$W)$g["sum-$w"]=format_number($W);echo
json_encode($g,JSON_UNESCAPED_UNICODE);}elseif($_GET["script"]=="kill")Connection::get()->query("KILL ".number($_POST["kill"]));else{$g=[];foreach(count_tables(Admin::get()->getDatabases(false))as$i=>$W){$g["tables-$i"]=$W;$g["size-$i"]=db_size($i);}echo
json_encode($g,JSON_UNESCAPED_UNICODE);}exit;}else{$wn=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($wn&&!$_POST["search"]){$G=true;$ci="";if(DIALECT=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$G=truncate_tables($_POST["tables"]);$ci=lang(316);}elseif($_POST["move"]){$G=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$ci=lang(317);}elseif($_POST["copy"]){$G=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$ci=lang(318);}elseif($_POST["drop"]){if($_POST["views"])$G=drop_views($_POST["views"]);if($G&&$_POST["tables"])$G=drop_tables($_POST["tables"]);$ci=lang(319);}elseif(DIALECT=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$P){foreach(get_rows("PRAGMA integrity_check(".q($P).")")as$I)$ci
.="<b>".h($P)."</b>: ".h($I["integrity_check"])."<br>";}}elseif(DIALECT!="sql"){$G=(DIALECT=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),(array)$_POST["tables"]));$ci=lang(320);}elseif(!$_POST["tables"])$ci=lang(79);elseif($G=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('AdminNeo\idf_escape',$_POST["tables"])))){while($I=$G->fetchAssoc())$ci
.="<b>".h($I["Table"])."</b>: ".h($I["Msg_text"])."<br>";}queries_redirect($_SERVER["REQUEST_URI"],$ci,(bool)$G);}if($_GET["ns"]=="")page_header(lang(31).": ".h(DB),true);else
page_header(lang(82).": ".h($_GET["ns"]),true);Admin::get()->printDatabaseMenu();if($_GET["ns"]===""){echo"<h2 id='schemas'>".lang(321)."</h2>\n";$Ml=Admin::get()->getSchemas();if(!$Ml)echo"<p class='message'>".lang(322)."\n";else{echo"<div class='scrollable'>\n","<table class='nowrap'>\n",'<thead><tr class="wrap"><th>',lang(82),"</th></tr></thead>";foreach($Ml
as$B)echo"<tr><th><a href='",h(ME),"ns=".urlencode($B),"' title='",lang(323),"'>".h($B)."</a></th></tr>";echo'</table></div>';}echo'<p class="links"><a href="'.h(ME).'scheme=">'.icon("database-add").lang(77)."</a>\n";}else{echo"<h2 id='tables-views'>".lang(324)."</h2>\n";$rn=['sql'=>'show-table-status.html','mariadb'=>'reference/sql-statements/administrative-sql-statements/show/show-table-status'];$Oc=db_collation(DB,collations());$d=["Engine"=>["label"=>lang(176),"doc"=>doc_link(['sql'=>'storage-engines.html','mariadb'=>'server-usage/storage-engines']),],];if($Oc!="")$d["Collation"]=["label"=>lang(47),"doc"=>doc_link(['sql'=>'charset-charsets.html','mariadb'=>'reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations']),];$d+=["Data_length"=>["label"=>lang(325),"doc"=>doc_link($rn+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286']),"link"=>"create","title"=>lang(36),],"Index_length"=>["label"=>lang(326),"doc"=>doc_link($rn+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT']),"link"=>"indexes","title"=>lang(182),],"Data_free"=>["label"=>lang(327),"doc"=>doc_link($rn),"link"=>"edit","title"=>lang(8),],"Auto_increment"=>["label"=>lang(49),"doc"=>doc_link(['sql'=>'example-auto-increment.html','mariadb'=>'reference/data-types/auto_increment']),"link"=>"auto_increment=1&create","title"=>lang(36),],"Rows"=>["label"=>lang(328),"doc"=>doc_link($rn+['pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286']),"link"=>"select","title"=>lang(34),],];if(support("comment"))$d["Comment"]=["label"=>lang(48),"doc"=>doc_link($rn+['pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE']),];$xj=(is_string($_GET["order"])?$_GET["order"]:"");$cd=null;if(preg_match('~^(.+)-(asc|desc)$~',$xj,$_)){$xj=$_[1];$cd=($_[2]=="desc");}if($xj!="__table"&&!isset($d[$xj]))$xj="";if($cd===null)$cd=isset($d[$xj]["link"]);$gp=($xj!=""&&$xj!="__table")||support("fast_status");$un=($gp?table_status():tables_list());if(!$un)echo"<p class='message'>".lang(79)."\n";else{echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n";if(support("table")){echo"<div class='field-sets'>\n","<fieldset><legend>".lang(329)." <span id='selected2'></span></legend><div class='fieldset-content'>",html_select("op",Admin::get()->getOperators(),isset($_POST["op"])?$_POST["op"]:Driver::get()->getLikeOperator()),"<input type='search' class='input' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = event => bodyKeydown(event, 'search');","")," <input type='submit' class='button' name='search' value='".lang(60)."'>\n","</div></fieldset>\n","</div>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable'>\n",'<thead><tr class="wrap">','<th class="actions"><input id="check-all" type="checkbox" class="input jsonly" title="'.lang(196).'">'.script("gid('check-all').onclick = partial(formCheck, /^(tables|views)\[/);","");$zi=($xj==""||$xj=="__table");$ln=($zi&&!$cd?ME."order=__table-desc":substr(ME,0,-1));$Ai=($zi&&DIALECT!="sqlite");echo'<th'.($Ai?" aria-sort='".($cd?"descending":"ascending")."'":'').'><a href="'.h($ln).'">'.lang(9).'</a>';foreach($d
as$w=>$c){$fd=($w===$xj?!$cd:isset($c["link"]));echo'<th'.($w===$xj?" aria-sort='".($cd?"descending":"ascending")."'":'').'><a href="'.h(ME)."order=$w-".($fd?"desc":"asc").'">'.$c["label"].'</a>'.$c["doc"];}echo"</thead>\n","<tbody>\n";if($xj=="__table"){if($cd)$un=array_reverse($un,true);}elseif($xj){uasort($un,function($ua,$eb)use($xj,$cd){$hp=isset($ua[$xj])?$ua[$xj]:null;$ip=isset($eb[$xj])?$eb[$xj]:null;$G=($hp<$ip?-1:($hp>$ip?1:0));return($cd?-$G:$G);});}$Zm=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$R=0;foreach($un
as$B=>$N){$Po=($gp?is_view($N):$N!==null&&!preg_match('~table|sequence~i',$N));$Td=($gp?(isset($N["Engine"])?$N["Engine"]:""):$N);$s=h("Table-".$B);echo'<tr><th class="actions">'.checkbox(($Po?"views[]":"tables[]"),$B,in_array("$B",$wn,true),"","","",$s);if(!Admin::get()->getSettings()->isSelectionPreferred()&&(support("table")||support("indexes")))$wa="table";else$wa="select";echo"<th><a href='",h(ME),"$wa=",urlencode($B),"' id='$s'>",h($B),"</a></th>";if($Po&&!preg_match('~materialized~i',$Td)){$S=lang(175);$Yb=count($d)-(support("comment")?2:1);echo'<td colspan="'.$Yb.'">'.(support("view")?"<a href='".h(ME)."view=".urlencode($B)."' title='".lang(37)."'>$S</a>":$S),"<td align='right'><a href='".h(ME)."select=".urlencode($B)."' title='".lang(34)."'>?</a>";}else{foreach($d
as$w=>$c){if($w=="Comment")continue;$s=" id='$w-".h($B)."'";$z=isset($c["link"])?$c["link"]:"";if(!$z){$W="";if($gp){$W=isset($N[$w])?$N[$w]:"";if($w=="Collation"&&$W=="")$W=$Oc;}echo"<td$s>".h($W);continue;}$W="?";if($gp){$Ti=isset($N[$w])?$N[$w]:"";if(is_numeric($Ti)&&$Ti>=0){$W=($w=="Rows"?format_rows($N):format_number($Ti));if(isset($Zm[$w])&&($Td!="InnoDB"||$w!="Data_free"))$Zm[$w]+=$Ti;}}echo"<td align='right'>".(support("table")||$w=="Rows"||(support("indexes")&&$w!="Data_length")?"<a href='".h(ME."$z=").urlencode($B)."'$s title='".$c["title"]."'>".h($W)."</a>":"<span$s>".h($W)."</span>");}$R++;}echo(support("comment")?"<td id='Comment-".h($B)."'>".($gp?h(isset($N["Comment"])?$N["Comment"]:""):""):""),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: event => tableClick(event, true)});"),"<tfoot><tr>","<td><th>".lang(301,count($un)),"<td>".h(DIALECT=="sql"?Connection::get()->getValue("SELECT @@default_storage_engine"):""),($Oc!=""?"<td>".h($Oc):"");if($gp&&function_exists('AdminNeo\db_status'))$Zm=db_status();foreach($Zm
as$w=>$Ym)echo"<td align='right' id='sum-$w'>".($gp?format_number($Ym):"");echo"<td></td><td></td>";if(support("comment"))echo"<td></td>";echo"</tr></tfoot>\n","</table>\n","</div>\n",($gp?"":script("ajaxSetHtml('".js_escape(ME)."script=db');"));if(Admin::get()->isDataEditAllowed()){echo"<div class='table-footer'><div class='field-sets'>\n";$Ho="<input type='submit' class='button' value='".lang(330)."'> ".help_script("VACUUM");$tj="<input type='submit' class='button' name='optimize' value='".lang(331)."'> ".help_script(DIALECT=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE");echo"<fieldset><legend>".lang(171)." <span id='selected'></span></legend><div class='fieldset-content'>".(DIALECT=="sqlite"?$Ho."<input type='submit' class='button' name='check' value='".lang(332)."'> ".help_script("PRAGMA integrity_check"):(DIALECT=="pgsql"?$Ho.$tj:(DIALECT=="sql"?"<input type='submit' class='button' value='".lang(333)."'> ".help_script("ANALYZE TABLE").$tj."<input type='submit' class='button' name='check' value='".lang(332)."'> ".help_script("CHECK TABLE")."<input type='submit' class='button' name='repair' value='".lang(334)."'> ".help_script("REPAIR TABLE"):"")))."<input type='submit' class='button' name='truncate' value='".lang(335)."'> ".help_script(DIALECT=="sqlite"?"DELETE":("TRUNCATE".(DIALECT=="pgsql"?"":" TABLE"))).confirm()."<input type='submit' class='button' name='drop' value='".lang(172)."'>".help_script("DROP TABLE").confirm()."\n";$h=(support("scheme")?Admin::get()->getSchemas():Admin::get()->getDatabases());echo"</div></fieldset>\n";if(count($h)!=1&&DIALECT!="sqlite"){echo"<fieldset><legend>".lang(336)." <span id='selected3'></span></legend><div>";$i=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($h?html_select("target",$h,$i,"","label-move"):'<input class="input" name="target" value="'.h($i).'" autocapitalize="off">')," <input type='submit' class='button' name='move' value='".lang(337)."'>",(support("copy")?" <input type='submit' class='button' name='copy' value='".lang(338)."'> ".checkbox("overwrite",1,$_POST["overwrite"],lang(339)):""),"</div></fieldset>\n";}echo
input_hidden("all"),script("qsl('input').onclick = partial(countTables, $R);"),input_token(),"</div></div>\n",script("initTableFooter()");}echo"</div>\n","</form>\n",script("tableCheck();");}echo'<p class="links"><a href="',h(ME),'create=">',icon("table-add"),lang(78),"</a>\n";if(support("view"))echo'<a href="',h(ME),'view=">',icon("view-add"),lang(254),"</a>\n";if(support("routine")){echo"<h2 id='routines'>".lang(192)."</h2>\n";$_l=routines();if($_l){$ec=$_l[0]["ROUTINE_COMMENT"]!==null;echo"<table>\n",'<thead><tr>','<th>',lang(231),'</th><td>',lang(45),'</td><td>',lang(271),"</td>";if($ec)echo"<td>",lang(48),"</td>";echo"<td></td>","</tr></thead>\n";foreach($_l
as$I){$B=($I["SPECIFIC_NAME"]==$I["ROUTINE_NAME"]?"":"&name=".urlencode($I["ROUTINE_NAME"]));echo'<tr>','<th><a href="',h(ME.($I["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($I["SPECIFIC_NAME"]).$B),'">',h($I["ROUTINE_NAME"]),'</a></th>','<td>',h($I["ROUTINE_TYPE"]),'</td>','<td>',h($I["DTD_IDENTIFIER"]),'</td>';if($ec)echo'<td>',truncate_utf8(preg_replace('~\s{2,}~'," ",trim($I["ROUTINE_COMMENT"])),50),'</td>';echo'<td><a href="'.h(ME.($I["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($I["SPECIFIC_NAME"]).$B).'">'.lang(185)."</a></td>";}echo"</table>\n";}echo'<p class="links">';if(support("procedure"))echo'<a href="',h(ME),'procedure=">',icon("function-add"),lang(270),"</a>";echo'<a href="',h(ME),'function=">',icon("function-add"),lang(269),"</a>\n","</p>\n";}if(support("sequence")){echo"<h2 id='sequences'>".lang(340)."</h2>\n";$dm=get_vals("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = current_schema() ORDER BY sequence_name");if($dm){echo"<table>\n","<thead><tr><th>",lang(231),"</th><td></td></tr></thead>\n";foreach($dm
as$W)echo"<tr>","<th>",h($W),"</th>","<td><a href='",h(ME),"sequence=",urlencode($W),"'>",lang(185),"</a></td>\n";echo"</table>\n";}echo"<p class='links'><a href='",h(ME),"sequence='>",icon("add"),lang(276),"</a></p>\n";}if(support("type")){echo"<h2 id='user-types'>".lang(112)."</h2>\n";$Eo=types();if($Eo){echo"<table>\n","<thead><tr><th>",lang(231),"</th><td></td></tr></thead>\n";foreach($Eo
as$W)echo"<tr>","<th>",h($W),"</th>","<td><a href='",h(ME),"type=",urlencode($W),"'>",lang(185),"</a></td>\n";echo"</table>\n";}echo"<p class='links'><a href='",h(ME),"type='>",icon("add"),lang(277),"</a></p>\n";}if(support("event")){echo"<h2 id='events'>".lang(193)."</h2>\n";$J=get_rows("SHOW EVENTS");if($J){echo"<table>\n","<thead><tr><th>".lang(231)."<td>".lang(341)."<td>".lang(260)."<td>".lang(261)."<td></thead>\n";foreach($J
as$I)echo"<tr>","<th>".h($I["Name"]),"<td>".($I["Execute at"]?lang(342)."<td>".h($I["Execute at"]):lang(262)." ".h($I["Interval value"])." ".h($I["Interval field"])."<td>".h($I["Starts"])),"<td>".h($I["Ends"]),'<td><a href="'.h(ME).'event='.urlencode($I["Name"]).'">'.lang(185).'</a>';echo"</table>\n";$de=Connection::get()->getValue("SELECT @@event_scheduler");if($de&&$de!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($de)."\n";}echo'<p class="links"><a href="',h(ME),'event=">',icon("event-add"),lang(259),"</a></p>\n";}}}page_footer();