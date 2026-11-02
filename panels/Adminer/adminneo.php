<?php
/**
 * AdminNeo - Powerful database manager in a single PHP file
 * v5.9.0
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
inject($Ca,Config$hc,Settings$M,Locale$_h){$this->admin=$Ca;$this->config=$hc;$this->settings=$M;$this->locale=$_h;}}abstract
class
Origin
extends
Plugin{private$errors=[];private
static$instance=null;static
function
create(array$hc=[],array$mk=[]){if(self::$instance)die("Admin instance already exists.\n");$Ca=new
static();if(!$hc&&file_exists("adminneo-config.php")){$hc=include_once("adminneo-config.php");if(!is_array($hc)){$hc=[];$sh="href=https://github.com/adminneo-org/adminneo#configuration ".target_blank();$Ca->addError(lang(0,"<b>adminneo-config.php</b>")." <a $sh>".lang(1)."</a>");}}$hc=new
Config($hc);$M=new
Settings($hc);if(!$mk&&file_exists("adminneo-plugins.php")){$mk=include_once("adminneo-plugins.php");if(!is_array($mk)){$mk=[];$sh="href=https://github.com/adminneo-org/adminneo#plugins ".target_blank();$Ca->addError(lang(0,"<b>adminneo-plugins.php</b>")." <a $sh>".lang(1)."</a>");}}self::$instance=$mk?new
Pluginer($Ca,$mk):$Ca;$Ca->inject(self::$instance,$hc,$M,Locale::get());foreach($mk
as$lk)$lk->inject(self::$instance,$hc,$M,Locale::get());return
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
verifyDefaultPassword($E){$zf=$this->config->getDefaultPasswordHash();if($zf===null||$zf==="")return
lang(2);elseif(!password_verify($E,$zf))return
lang(3);return
true;}function
authenticate($U,$E){if($E==""){$zf=$this->config->getDefaultPasswordHash();if($zf===null)return
lang(4,target_blank());else
return$zf==="";}return
true;}function
getPrivateKey($f=false){return
get_private_key($f);}function
getBruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
getServerName($L,$il=true,$re=null){if($L==""){if(!$il)return"";$L=Connection::exists()?Connection::get()->getDefaultServerName():"";if($L=="")return$re!==null?$re:lang(5);$Zl=null;}else$Zl=$this->config->getServer($L);return$Zl?$Zl->getName():preg_replace('~^https?://~',"",$L);}abstract
function
getDatabase();function
getDatabases($Ne=true){$h=$this->filterListWithWildcards(get_databases($Ne),$this->config->getHiddenDatabases(),false,Driver::get()->getSystemDatabases());if(DB!=""&&!in_array(DB,$h))array_unshift($h,DB);return$h;}function
getSchemas($Ii=false){$Ef=$this->config->getHiddenSchemas();if($Ii&&!in_array("__system",$Ef))$Ef[]="__system";$Fl=$this->filterListWithWildcards(schemas(),$Ef,false,Driver::get()->getSystemSchemas());if(isset($_GET["ns"])&&$_GET["ns"]!=""&&!in_array($_GET["ns"],$Fl))array_unshift($Fl,$_GET["ns"]);return$Fl;}function
getCollations(array$Mg=[]){$Lo=$this->config->getVisibleCollations();$Ee=$Lo?array_merge($Lo,$Mg):[];return$this->filterListWithWildcards(collations(),$Ee,true);}private
function
filterListWithWildcards(array$Y,array$Ee,$Og,array$Xm=[]){if(!$Y||!$Ee)return$Y;$u=array_search("__system",$Ee);if($u!==false){unset($Ee[$u]);$Ee=array_merge($Ee,$Xm);}array_walk($Ee,function(&$X){$X=str_replace('\\*',".*",preg_quote($X,"~"));});$ek='~^('.implode("|",$Ee).')$~';return$this->filterListWithPattern($Y,$ek,$Og);}private
function
filterListWithPattern(array$Y,$ek,$Og){$G=[];foreach($Y
as$w=>$X){if(is_array($X)){if($Nm=$this->filterListWithPattern($X,$ek,$Og))$G[$w]=$Nm;}elseif(($Og&&preg_match($ek,$X))||(!$Og&&!preg_match($ek,$X)))$G[$w]=$X;}return$G;}abstract
function
getQueryTimeout();function
sendHeaders(){}function
updateCspHeader(array&$Ac){}function
printFavicons(){$Sb=validate_color_variant($this->config->getColorVariant());echo"<link rel='icon' type='image/x-icon' href='",link_files("favicon-$Sb.ico",[]),"' sizes='32x32'>\n","<link rel='icon' type='image/svg+xml' href='",link_files("favicon-$Sb.svg",[]),"'>\n","<link rel='apple-touch-icon' href='",link_files("apple-touch-icon-$Sb.png",[]),"'>\n";}abstract
function
printToHead();function
getCssUrls(){$qo=$this->config->getCssUrls();foreach(["adminneo.css","adminneo-light.css","adminneo-dark.css"]as$o){if(file_exists($o))$qo[]="$o?v=".filemtime($o);}return$qo;}function
isLightModeForced(){return$this->isColorSchemeForced(false);}function
isDarkModeForced(){return$this->isColorSchemeForced(true);}private
function
isColorSchemeForced($Fc){$ji=$Fc?Settings::$ColorSchemeDark:Settings::$ColorSchemeLight;$ki=$Fc?Settings::$ColorSchemeLight:Settings::$ColorSchemeDark;$Ae=file_exists("adminneo-$ji.css");$Be=file_exists("adminneo-$ki.css");if($Ae&&!$Be)return
true;return$this->settings->getColorScheme()==$ji&&!($Ae
xor$Be);}function
getJsUrls(){$qo=$this->config->getJsUrls();$o="adminneo.js";if(file_exists($o))$qo[]="$o?v=".filemtime($o);return$qo;}abstract
function
printLoginForm();function
getLoginFormRow($ve,$Yg,$l){if($Yg)return"<tr><th>$Yg</th><td>$l</td></tr>\n";else
return"$l\n";}function
printLogout(){echo"<div class='logout'>","<form action='' method='post'>\n","<span title='",lang(6),"'>",h($_GET["username"]),"</span>","<input type='submit' class='button' name='logout' value='",lang(7),"' id='logout'>",input_token(),"</form>","</div>\n";}function
getTableName(array$bn){return
h($bn["Name"]);}abstract
function
getFieldName(array$l,$rj=0);function
formatComment($ac){return
h($ac);}abstract
function
printTableMenu(array$bn,$lg);function
getForeignKeys($P){return
foreign_keys($P);}function
getBackwardKeys($P,$Zm){if(!$this->settings->isRelationLinks())return[];$J=backward_keys($P);$Rg=[];foreach($J
as$I){$s=$I["table_schema"].".".$I["table_name"];$Rg[$s]["schema"]=$I["table_schema"];$Rg[$s]["table"]=$I["table_name"];$Rg[$s]["constraints"][$I["constraint_name"]][$I["column_name"]]=$I["referenced_column_name"];}foreach($Rg
as$s=>$w){$B=$this->admin->getTableName(table_status1($w["table"],true));if($B!=""){$Hl=preg_quote($Zm);$Sl="(:|\\s*-)?\\s+";$Rg[$s]["name"]=(preg_match("(^$Hl$Sl(.+)|^(.+?)$Sl$Hl\$)iu",$B,$_)?$_[2].$_[3]:$B);}else
unset($Rg[$s]);}return$Rg;}function
printBackwardKeys(array$eb,array$I){foreach($eb
as$w){foreach($w["constraints"]as$nc){$Th=preg_replace('~&ns=[^&]+&~',"&ns=".urldecode($w["schema"])."&",ME);$z=$Th.'select='.urlencode($w["table"]);$r=0;foreach($nc
as$c=>$W){if(!isset($I[$W]))continue
2;$z
.=where_link($r++,$c,$I[$W]);}$B=preg_replace('(^'.preg_quote($_GET["select"]).(substr($_GET["select"],-1)=="s"?"?":"").'_)',"_",$w["name"]);$S=implode(", ",array_keys($nc));echo"<a href='".h($z)."' title='".h($S)."'>".h($B)."</a>";$z=$Th.'edit='.urlencode($w["table"]);foreach($nc
as$c=>$W)$z
.="&preset".urlencode("[".bracket_escape($c)."]")."=".urlencode($I[$W]);echo"<a href='".h($z)."' title='".lang(8)."'>",icon_solo("add"),"</a> ";}}}abstract
function
formatSelectQuery($F,$Em,$qe=false);abstract
function
formatMessageQuery($F,$Bn,$qe=false);abstract
function
formatSqlCommandQuery($F);function
printAfterSqlCommand(){}abstract
function
getTableDescriptionFieldName($P);abstract
function
fillForeignDescriptions(array$J,array$Qe);function
getFieldValueLink($W,$l){if(is_mail($W))return"mailto:$W";if(is_web_url($W))return$W;return
null;}abstract
function
formatSelectionValue($W,$z,$l,$Dj);abstract
function
formatFieldValue($X,array$l);abstract
function
printTableStructure(array$m);abstract
function
printTablePartitions(array$Uj);abstract
function
printRelatedTables(array$R);abstract
function
printTableIndexes(array$v,array$bn);abstract
function
printSelectionColumns(array$K,array$d);abstract
function
printSelectionSearch(array$Z,array$d,array$v);abstract
function
printSelectionOrder(array$rj,array$d,array$v);abstract
function
printSelectionLimit($y);abstract
function
printSelectionLength($wn);abstract
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
getFieldInput($P,array$l,$Va,$X,$q);function
getFieldInputHint($P,array$l,$X){return
support("comment")?$this->admin->formatComment($l["comment"]):"";}abstract
function
processFieldInput(array$l,$X,$q="");function
detectJson($we,&$X,$yk=null){if(is_array($X)){$Le=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($this->config->isJsonValuesAutoFormat()?JSON_PRETTY_PRINT:0);$X=json_encode($X,$Le);return
true;}$Le=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($yk?JSON_PRETTY_PRINT:0);if(preg_match('~^jsonb?$~',$we)){if($X!=null&&$yk!==null&&$this->config->isJsonValuesAutoFormat()&&($Kg=json_decode($X))!==null)$X=json_encode($Kg,$Le);return
true;}if(!$this->config->isJsonValuesDetection())return
false;if(is_string($X)&&$X!=""&&preg_match('~varchar|text|character varying|String|keyword~',$we)&&($X[0]=="{"||$X[0]=="[")&&($Kg=json_decode($X))){if($yk!==null&&$this->config->isJsonValuesAutoFormat())$X=json_encode($Kg,$Le);return
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
filterDumpFormats($T){$Ue=[];foreach(self::get()->getDumpFormats()as$Te=>$Tc){if(is_string($Tc))$Ue[$Te]=$Tc;elseif(isset($Tc[$T])?$Tc[$T]:false)$Ue[$Te]=isset($Tc["name"])?$Tc["name"]:$Te;}return$Ue;}abstract
function
sendDumpFormatHeaders($Pf,$oi=false);abstract
function
getDumpOutputs();abstract
function
sendDumpOutputHeaders($Pf,$me);function
sendDumpHeaders($Pf,$oi=false){return
null;}function
dumpDatabase($Jc){}abstract
function
dumpTable($P,$Mm,$Io=0);abstract
function
dumpData($P,$Mm,$F);abstract
function
getImportFilePath();abstract
function
printDatabaseMenu();abstract
function
printNavigation($hi);abstract
function
printDatabaseSwitcher($hi);function
printTablesFilter(){echo"<div class='tables-filter jsonly'>"."<input id='tables-filter' type='search' class='input' autocomplete='off' placeholder='".lang(9)."'>".script("initTablesFilter(".json_encode($this->admin->getDatabase(),JSON_HEX_TAG).");")."</div>\n";}abstract
function
printTableList(array$R);function
getSettingsRows($pf){$M=[];if($pf==1){$C=get_language_options();if($C)$M["lang"]="<tr><th id='label-language'>".lang(10)."</th>"."<td>".html_select("lang",get_language_options(),Locale::get()->getLanguage(),"","label-language")."</td></tr>\n";$Gn=get_theme_titles($this->config->getColorVariant());if(count($Gn)>1){list($zn)=validate_theme($this->config->getTheme(),$this->config->getColorVariant());$C=[""=>lang(11)." ($Gn[$zn])"]+$Gn;$M["theme"]="<tr><th id='label-theme'>".lang(12)."</th>"."<td>".html_select("theme",$C,($ta=$this->settings->getParameter("theme"))!==null?$ta:"","","label-theme")."</td></tr>\n";}$C=[""=>lang(13),Settings::$ColorSchemeLight=>lang(14),Settings::$ColorSchemeDark=>lang(15)];$M["colorScheme"]="<tr><th>".lang(16)."</th>"."<td>".html_radios("colorScheme",$C,($ta=$this->settings->getParameter("colorScheme"))!==null?$ta:"")."</td></tr>\n";}elseif($pf==2){$C=[""=>lang(11),true=>lang(17),false=>lang(18),];$j=$C[$this->config->isRelationLinks()];$C[""].=" ($j)";$M["relationLinks"]="<tr><th>".lang(19)."</th>"."<td>".html_radios("relationLinks",$C,($ta=$this->settings->getParameter("relationLinks"))!==null?$ta:"")."<span class='input-hint'>".lang(20)."</span>"."</td></tr>\n";$j=$this->config->getRecordsPerPage();$C=[""=>lang(11)." ($j)","20","30","50","70","100",];$M["recordsPerPage"]="<tr><th id='label-records'>".lang(21)."</th>"."<td>".html_select("recordsPerPage",$C,($ta=$this->settings->getParameter("recordsPerPage"))!==null?$ta:"","","label-records")."<span class='input-hint'>".lang(22)."</span>"."</td></tr>\n";$j=($ta=$this->config->getEnumAsSelectThreshold())!==null?$ta:lang(23);$C=[""=>lang(11)." ($j)",-1=>lang(23),0=>lang(24),3=>lang(25,3),5=>lang(25,5),10=>lang(25,10),20=>lang(25,20),];$M["enumAsSelectThreshold"]="<tr><th id='label-enum'>".lang(26)."</th>"."<td>".html_select("enumAsSelectThreshold",$C,($ta=$this->settings->getParameter("enumAsSelectThreshold"))!==null?$ta:"","","label-enum",true)."<span class='input-hint'>".lang(27)."</span>"."</td></tr>\n";}return$M;}abstract
function
getForeignColumnInfo(array$Qe,$c);}class
Pluginer{private
static$InternalMethods=["inject"=>true,"getConfig"=>true,];private
static$AppendMethods=["getErrors"=>true,"getFieldFunctions"=>true,"getDumpOutputs"=>true,"getDumpFormats"=>true,"getSettingsRows"=>true,];private$plugins;private$hooks=[];function
__construct(Origin$Ca,array$mk){$this->plugins=$mk;foreach(get_class_methods('\AdminNeo\Origin')as$ei){$this->hooks[$ei]=[];if(!(isset(self::$InternalMethods[$ei])?self::$InternalMethods[$ei]:false)){foreach($mk
as$lk){if(method_exists($lk,$ei))$this->hooks[$ei][]=$lk;}}if(isset(self::$AppendMethods[$ei])?self::$AppendMethods[$ei]:false)array_unshift($this->hooks[$ei],$Ca);else$this->hooks[$ei][]=$Ca;}}function
getPlugins(){return$this->plugins;}function
__call($B,array$Nj){$Na=isset(self::$AppendMethods[$B])?self::$AppendMethods[$B]:false;$G=$Na?[]:null;assert(isset($this->hooks[$B]),"Calling unknown plugin method: $B");foreach($this->hooks[$B]as$lk){$X=call_user_func_array([$lk,$B],$Nj);if($X!==null){if($Na)$G+=$X;else
return$X;}}return$G;}function
updateCspHeader(array&$Ac){$this->__call(__FUNCTION__,[&$Ac]);}function
detectJson($we,&$X,$yk=null){return$this->__call(__FUNCTION__,[$we,&$X,$yk]);}}class
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
printLoginForm(){$td=Drivers::getList();$am=$this->config->getServerPairs($td);$L=SERVER?:$this->config->getDefaultServer();echo"<table class='box box-light'>\n";if($am)echo$this->admin->getLoginFormRow('server',lang(5),"<select name='auth[server]'>".optionlist($am,$L,true)."</select>");else{$rd=DRIVER?:$this->config->getDefaultDriver($td);if(count($td)>1)echo$this->admin->getLoginFormRow('driver',lang(28),html_select("auth[driver]",$td,$rd).script("initLoginDriver(qsl('select'));",""));else
echo$this->admin->getLoginFormRow('driver','',input_hidden("auth[driver]",$rd));echo$this->admin->getLoginFormRow('server',lang(5),"<input class='input' name='auth[server]' value='".h($L)."' title='".lang(29)."' placeholder='localhost' autocapitalize='off'>");}echo$this->admin->getLoginFormRow('username',lang(6),'<input class="input" name="auth[username]" id="username" value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'),$this->admin->getLoginFormRow('password',lang(30),'<input type="password" class="input" name="auth[password]" autocomplete="current-password">');if(!$am){$Jc=isset($_GET["db"])?$_GET["db"]:$this->config->getDefaultDatabase();echo$this->admin->getLoginFormRow('db',lang(31),'<input class="input" name="auth[db]" value="'.h($Jc).'" autocapitalize="off">');}echo"</table>\n","<p>","<input type='submit' class='button default' value='".lang(32)."'>",checkbox("auth[permanent]",1,$_COOKIE["neo_permanent"],lang(33)),"</p>\n";}function
getFieldName(array$l,$rj=0){$T=$l["full_type"].($l["null"]?" NULL":"");$ac=$l["comment"];$Sl=$T&&$ac!=""?": ":"";return'<span title="'.h($T.$Sl.$ac).'">'.h($l["field"]).'</span>';}function
printTableMenu(array$bn,$lg){echo'<p class="links top-tabs">';$th=[];$Ol=($this->settings->isSelectionPreferred()&&!$this->settings->isNavigationReversed())||(!$this->settings->isSelectionPreferred()&&$this->settings->isNavigationReversed());if($Ol)$th["select"]=[lang(34),"data"];if(support("table")||support("indexes"))$th["table"]=[lang(35),"structure"];if(!$Ol)$th["select"]=[lang(34),"data"];$P=$bn["Name"];$Fg=false;if(support("table")){$Fg=is_view($bn);if(!$Fg){if($P!="")$th["create"]=[lang(36),"edit"];}elseif(support("view"))$th["view"]=[lang(37),"edit"];}if($lg!==null)$th["edit"]=[lang(8),"item-add"];$Nj=$lg?"&".http_build_query($lg):"";foreach($th
as$w=>$W)echo" <a href='",h(ME),"$w=",urlencode($P),($w=="edit"?$Nj:""),"'",bold(isset($_GET[$w])),">",icon($W[1]),"$W[0]</a>";echo
doc_link([DIALECT=>Driver::get()->tableHelp($P,$Fg)],icon("help").lang(38)),"\n";}function
formatSelectQuery($F,$Em,$qe=false){$Sm=support("sql");$Po=!$qe?Driver::get()->warnings():null;if($Sm)$F
.=";";$Vm=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$H="<pre><code class='jush-$Vm'>".h(str_replace("\n"," ",$F))."</code></pre>\n";$H
.="<p class='links'>";if($Sm)$H
.="<a href='".h(ME)."sql=".urlencode($F)."'>".icon("edit").lang(39)."</a>";if($Po)$H
.="<a href='#warnings' class='toggle'>".lang(40).icon_chevron_down()."</a>";$H
.=" <span class='time'>(".format_time($Em).")</span>";$H
.="</p>\n";if($Po){$H
.=script("initToggles(qsl('p'));");$H
.="<div id='warnings' class='warnings hidden'>\n$Po\n</div>\n";}return$H;}function
formatMessageQuery($F,$Bn,$qe=false){restart_session();$Gf=&get_session("queries");if(!isset($Gf[$_GET["db"]]))$Gf[$_GET["db"]]=[];if(strlen($F)>1e6)$F=preg_replace('~[\x80-\xFF]+$~','',substr($F,0,1e6))."\n…";$Gf[$_GET["db"]][]=[$F,time(),$Bn];$Sm=support("sql");$Po=!$qe?Driver::get()->warnings():null;$ym="sql-".count($Gf[$_GET["db"]]);$Qo="warnings-".count($Gf[$_GET["db"]]);$H=" ";if($Po)$H
.="<a href='#$Qo' class='toggle'>".lang(40).icon_chevron_down()."</a>, ";$Lk=support("sql")?lang(41):lang(42);$H
.="<a href='#$ym' class='toggle'>$Lk".icon_chevron_down()."</a>";$H
.=" <span class='time'>".@date("H:i:s")."</span>\n";if($Po)$H
.="<div id='$Qo' class='warnings hidden'>\n$Po</div>\n";$H
.="<div id='$ym' class='hidden'>\n";$Vm=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$H
.="<pre><code class='jush-$Vm'>".truncate_utf8($F,1000)."</code></pre>\n";$H
.="<p class='links'>";if($Sm)$H
.="<a href='".h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($Gf[$_GET["db"]])-1))."'>".icon("edit").lang(39)."</a>";if($Bn)$H
.=" <span class='time'>($Bn)</span>";$H
.="</p>\n";$H
.="</div>\n";return$H;}function
formatSqlCommandQuery($F){if(preg_match('~^DELIMITER\s~i',$F))return"";return
truncate_utf8($F,1000);}function
getTableDescriptionFieldName($P){return"";}function
fillForeignDescriptions(array$J,array$Qe){return$J;}function
formatSelectionValue($W,$z,$l,$Dj){if($W===null)$vn="<i>NULL</i>";elseif(!$l)$vn=$W;elseif(preg_match("~char|binary|boolean~",$l["type"])&&!preg_match("~var~",$l["type"]))$vn="<code>$W</code>";elseif(is_blob($l)&&!is_utf8($W))$vn="<i>".lang(43,strlen($Dj))."</i>";elseif($this->admin->detectJson($l["full_type"],$Dj))$vn="<code class='jush-json'>$W</code>";else$vn=$W;if($z)$vn="<a href='".h($z)."'".(is_web_url($z)?target_blank():"").">$vn</a>";return$vn;}function
formatFieldValue($X,array$l){return$X;}function
printTableStructure(array$m){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th>",lang(44),"</th>","<td>",lang(45)," <span class='note'>[".lang(46)."]</span></td>","<td>",lang(47),"</td>";if(support("comment"))echo"<td>",lang(48),"</td>";echo"</tr></thead>\n";$xo=Driver::get()->getUserTypes();foreach($m
as$l){echo"<tr>","<th>",h($l["field"]),"</th>","<td>";$T=h($l["full_type"]);if(in_array($T,$xo))echo"<a href='".h(ME.'type='.urlencode($T))."'>$T</a>";else
echo$T;if($l["null"])echo" <i>NULL</i>";if($l["auto_increment"])echo" <i>".lang(49)."</i>";if(isset($l["default"])){if($l["generated"])echo" <i>AS</i> <code class='jush-".DIALECT."'>".h(parenthesize_expression($l["default"]))."</code>"," <i>".h($l["generated"])."</i>";else
echo" [<strong>".h($l["default"])."</strong>]";}echo"</td>","<td>",h($l["collation"]),"</td>";if(support("comment"))echo"<td>",$this->admin->formatComment($l["comment"]),"</td>";echo"\n";}echo"</table>\n","</div>\n";}function
printTablePartitions(array$Uj){$km=isset($Uj["partition_names"]);echo"<p>","<code class='jush-".DIALECT."'>BY {$Uj["partition_by"]} ({$Uj["partition"]})</code>";if(!$km&&isset($Uj["partitions"]))echo" ".lang(50).": ".h($Uj["partitions"]);echo"</p>";if($km){echo"<table>\n","<thead><tr><th>".lang(51)."</th><td>".lang(52)."</td></tr></thead>\n";foreach($Uj["partition_names"]as$w=>$B){echo"<tr><th>";if(DIALECT=="pgsql")echo"<a href='",h(ME."table=".urlencode($B)),"'>";echo
h($B);if(DIALECT=="pgsql")echo"</a>";echo"</th><td>".h($Uj["partition_values"][$w])."\n";}echo"</table>\n";}}function
printRelatedTables(array$R){echo"<ul class='links'>\n";foreach($R
as$I){$z=preg_replace('~ns=[^&]*~',"ns=".urlencode($I["ns"]),ME);echo"<li><a href='",h($z."table=".urlencode($I["table"])),"'>",icon("structure");if($I["ns"]!=$_GET["ns"])echo"<b>".h($I["ns"])."</b>.";echo
h($I["table"]),"</a>";}echo"</ul>\n";}function
printTableIndexes(array$v,array$bn){$Pc=first(Driver::get()->getIndexAlgorithms($bn));$Rj=false;foreach($v
as$u){if(isset($u["partial"])?$u["partial"]:false){$Rj=true;break;}}echo"<table>\n","<thead><tr>","<th>",lang(45),"</th>","<td>",lang(53)," <span class='note'>(",lang(54),")</span></td>";if($Rj)echo"<td>",lang(55),"</td>";echo"</tr></thead>\n";foreach($v
as$B=>$u){ksort($u["columns"]);$Bk=[];foreach($u["columns"]as$w=>$W)$Bk[]="<i>".h($W)."</i>".($u["lengths"][$w]?"(".h($u["lengths"][$w]).")":"").($u["descs"][$w]?" DESC":"");echo"<tr title='",h($B),"'>","<th>",h($u["type"]);if(isset($u['algorithm'])&&$u['algorithm']!=$Pc)echo" (",h($u['algorithm']),")";echo"</th>","<td>",implode(", ",$Bk),"</td>";if($Rj){echo"<td>";if($u['partial'])echo"<code class='jush-",DIALECT,"'>WHERE ",h($u['partial']),"</code>";echo"</td>";}echo"</tr>\n";}echo"</table>\n";}function
printSelectionColumns(array$K,array$d){print_fieldset_start("select",lang(56),"columns",(bool)$K,true);$K[""]=[];$r=0;foreach($K
as$w=>$W){$W=isset($_GET["columns"][$w])?$_GET["columns"][$w]:[];$c=select_input("name='columns[$r][col]'",$d,isset($W["col"])?$W["col"]:null,$w!==""?"selectFieldChange":"selectAddRow");echo"<div ",($w!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly");if(Driver::get()->getFunctions()||Driver::get()->getGrouping())echo
html_select("columns[$r][fun]",[-1=>""]+array_filter([lang(57)=>Driver::get()->getFunctions(),lang(58)=>Driver::get()->getGrouping()]),isset($W["fun"])?$W["fun"]:null),help_script_command("value && value.replace(/ |\$/, '(') + ')'",true),script("qsl('select').onchange = (event) => { ".($w!==""?"":" qsl('select, input:not(.remove)', event.target.parentNode).onchange();")." };",""),"($c)";else
echo$c;echo" <button class='button light remove jsonly' title='",lang(59),"'>",icon_solo("remove"),"</button>",script("qsl('#fieldset-select .remove').onclick = selectRemoveRow;",""),"</div>\n";$r++;}print_fieldset_end("select",true);}function
printSelectionSearch(array$Z,array$d,array$v){print_fieldset_start("search",lang(60),"search",(bool)$Z);foreach($v
as$r=>$u){if($u["type"]=="FULLTEXT"){echo"<div>(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$u["columns"]))."</i>) AGAINST","<input type='text' class='input' name='fulltext[$r]' value='".h(isset($_GET["fulltext"][$r])?$_GET["fulltext"][$r]:null)."'>",script("qsl('input').oninput = selectFieldChange;","");if(DIALECT=='sql')echo
checkbox("boolean[$r]",1,isset($_GET["boolean"][$r]),"BOOL");echo"</div>\n";}}$yb="this.parentNode.firstChild.onchange();";foreach(array_merge((array)$_GET["where"],[[]])as$r=>$W){if(!$W||("$W[col]$W[val]"!=""&&in_array($W["op"],$this->getOperators())))echo"<div>",select_input(" name='where[$r][col]'",$d,$W["col"],($W?"selectFieldChange":"selectAddRow"),"(".lang(61).")"),html_select("where[$r][op]",$this->getOperators(),$W["op"],$yb),"<input type='text' class='input' name='where[$r][val]' value='".h($W["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $yb }, onkeydown: selectSearchKeydown});","")," <button class='button light remove jsonly' title='".lang(59)."'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-search .remove").onclick = selectRemoveRow;',""),"</div>\n";}print_fieldset_end("search");}function
printSelectionOrder(array$rj,array$d,array$v){print_fieldset_start("sort",lang(62),"sort",(bool)$rj,true);$_GET["order"][""]="";$r=0;foreach((array)$_GET["order"]as$w=>$W){if($w!=""&&$W=="")continue;echo"<div ",($w!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly"),select_input("name='order[$r]'",$d,$W,$w!==""?"selectFieldChange":"selectAddRow")," ",checkbox("desc[$r]",1,isset($_GET["desc"][$w]),lang(63))," <button class='button light remove jsonly' title='",lang(59),"'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-sort .remove").onclick = selectRemoveRow;',""),"</div>\n";$r++;}print_fieldset_end("sort",true);}function
printSelectionLimit($y){echo"<fieldset><legend>".lang(64)."</legend><div class='fieldset-content'>","<input type='number' name='limit' class='input size' value='".h($y?:"")."'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
printSelectionLength($wn){if($wn!==null)echo"<fieldset><legend>".lang(65)."</legend><div class='fieldset-content'>","<input type='number' name='text_length' class='input size' value='".h($wn)."'>","</div></fieldset>\n";}function
printSelectionAction(array$v){echo"<fieldset><legend>".lang(66)."</legend><div class='fieldset-content'>","<input type='submit' class='button' value='".lang(56)."'>"," <span id='noindex' title='".lang(67)."'></span>","<script".nonce().">\n";$d=new
stdClass();foreach($v
as$u){$Cc=reset($u["columns"]);if($u["type"]!="FULLTEXT"&&$Cc)$d->$Cc=null;}echo"const indexColumns = ".json_encode($d,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG).";\n","selectFieldChange.call(gid('form')['select']);\n","</script>\n","</div></fieldset>\n";}function
processSelectionColumns(array$d,array$v){$K=[];$nf=[];foreach((array)$_GET["columns"]as$w=>$W){if($W["fun"]=="count"||($W["col"]!=""&&(!$W["fun"]||in_array($W["fun"],Driver::get()->getFunctions())||in_array($W["fun"],Driver::get()->getGrouping())))){$K[$w]=apply_sql_function($W["fun"],($W["col"]!=""?idf_escape($W["col"]):"*"));if(!in_array($W["fun"],Driver::get()->getGrouping()))$nf[]=$K[$w];}}return[$K,$nf];}function
processSelectionSearch(array$m,array$v){$H=[];foreach($v
as$r=>$u){if($u["type"]=="FULLTEXT"&&isset($_GET["fulltext"])&&$_GET["fulltext"][$r]!="")$H[]="MATCH (".implode(", ",array_map('AdminNeo\idf_escape',$u["columns"])).") AGAINST (".q($_GET["fulltext"][$r]).(isset($_GET["boolean"][$r])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$Z){$Ob=$Z["col"];$hj=$Z["op"];$W=$Z["val"];if("$Ob$W"!=""&&in_array($hj,$this->getOperators())){if($hj=="SQL"&&(!$_POST||!verify_token()))Connection::get()->setUntrusted(true);$gc=[];$pg='-?\d+';$Ni='-?(\d+(\.\d*)?|\.\d+)';$xg=preg_match('~IN$~',$hj);$qg="~^$pg".($xg?"(,$pg)*":"")."$~";$Oi="~^$Ni".($xg?"(,$Ni)*":"")."$~";$Kc='~^(\d+)-(\d+)-(\d+)([ T]([01]?\d|2[0-3]):[0-5]\d(:[0-5]\d(\.\d+)?)?(\s*(z|utc|[+-](0?\d|1[0-5])(:?[0-5]\d)?))?)?$~i';foreach(($Ob!=""?[$Ob=>isset($m[$Ob])?$m[$Ob]:[]]:$m)as$B=>$l){$wk="";$fc=" $hj";$yg=preg_match('~'.number_type().(DIALECT=="pgsql"?"":'|bit').'~',$l["type"])&&!preg_match('~\[~',$l["full_type"]);$Ui=DIALECT=="pgsql"&&$hj=="="&&$l["type"]=="oid";if($Ui)$fc
.=" ".$this->admin->processFieldInput($l,$W)."::regproc";elseif(preg_match('~IN$~',$hj)){$Tf=process_length($W);if((!$yg||(DIALECT=="pgsql"&&$l["type"]=="money"))&&preg_match('~^\([-+.\d,]+\)$~',$Tf))$Tf="(".implode(",",array_map('AdminNeo\q',explode(",",substr($Tf,1,-1)))).")";$fc
.=" ".($Tf!=""?$Tf:"(NULL)");}elseif($hj=="SQL")$fc=" $W";elseif(preg_match('~^(I?LIKE) %%$~',$hj,$_))$fc=" $_[1] ".$this->admin->processFieldInput($l,"%$W%");elseif($hj=="FIND_IN_SET"){$wk="$hj(".q($W).", ";$fc=")";}elseif(!preg_match('~NULL$~',$hj))$fc
.=" ".$this->admin->processFieldInput($l,$W);if($Ob!=""||(isset($l["privileges"]["where"])&&(preg_match(preg_match('~^'.int_type().'$~',$l["type"])?$qg:$Oi,$W)||!$yg)&&(!preg_match("~[\x80-\xFF]~",$W)||preg_match('~char|text|enum|set|keyword~',$l["type"]))&&(!preg_match('~date|timestamp~',$l["type"])||(preg_match($Kc,$W,$_)&&checkdate((int)$_[2],(int)$_[3],(int)$_[1])))&&Driver::get()->isSearchable($Z,$l)&&(!preg_match('~^elastic~',DRIVER)||$l["type"]!="boolean"||preg_match('~true|false~',$W))&&(!preg_match('~^elastic~',DRIVER)||strpos($hj,"regexp")===false||preg_match('~text|keyword~',$l["type"]))&&(!preg_match('~^elastic~',DRIVER)||!preg_match('~^((unsigned_)?long|short|byte)$~',$l["type"])||preg_match('~^-?\d+$~',$W)))){if($Ui)$gc[]=$wk.idf_escape($B).$fc;else$gc[]=$wk.Driver::get()->convertSearch(idf_escape($B),$Z,$l).$fc;}}if(count($gc)==1)$H[]=$gc[0];elseif($gc)$H[]="(".implode(" OR ",$gc).")";else$H[]="1 = 0";}}return$H;}function
processSelectionOrder(array$m,array$v){$H=[];foreach((array)$_GET["order"]as$w=>$W){if($W!="")$H[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$W)?$W:idf_escape($W)).(isset($_GET["desc"][$w])?" DESC".(DIALECT=="pgsql"&&(isset($m[$W]["null"])?$m[$W]["null"]:null)?" NULLS LAST":""):"");}return$H;}function
processSelectionLength(){return
isset($_GET["text_length"])?$_GET["text_length"]:"100";}function
getFieldFunctions(array$l){$H=($l["null"]?"NULL/":"");$no=isset($_GET["select"])||where($_GET);foreach([Driver::get()->getInsertFunctions(),Driver::get()->getEditFunctions()]as$w=>$ff){if(!$w||(!isset($_GET["call"])&&$no)){foreach($ff
as$ek=>$W){if(!$ek||preg_match("~$ek~",$l["type"]))$H
.="/$W";}}if($w&&$ff&&!preg_match('~enum|set|bool~',$l["type"])&&!is_blob($l))$H
.="/SQL";}if($l["auto_increment"]&&!$no)$H=lang(49);return
explode("/",$H);}function
getFieldInput($P,array$l,$Va,$X,$q){return"";}function
processFieldInput(array$l,$X,$q=""){if($q=="SQL")return$X;$B=$l["field"];$H=q($X);if(preg_match('~^(now|getdate|uuid)$~',$q))$H="$q()";elseif(preg_match('~^current_(date|timestamp)$~',$q))$H=$q;elseif(preg_match('~^([+-]|\|\|)$~',$q))$H=idf_escape($B)." $q $H";elseif(preg_match('~^[+-] interval$~',$q))$H=idf_escape($B)." $q ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$X)&&DIALECT!="pgsql"?$X:$H);elseif(preg_match('~^(addtime|subtime|concat)$~',$q))$H="$q(".idf_escape($B).", $H)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$q))$H="$q($H)";elseif($l["type"]=="boolean"&&DIALECT=="elastic")$H=$H=="0"?"false":"true";return
unconvert_field($l,$H);}function
getDumpFormats(){return(support("dump")?['sql'=>'SQL']:[])+parent::getDumpFormats();}function
sendDumpFormatHeaders($Pf,$oi=false){$me=(str_contains($_POST["format"],"sql")?"sql":($oi?"tar":"csv"));if($me=="tar")header("Content-Type: application/x-tar");elseif($me=="sql"||$_POST["output"]=="text")header("Content-Type: text/plain; charset=utf-8");else
header("Content-Type: text/csv; charset=utf-8");return$me;}function
getDumpOutputs(){$Ij=['file'=>lang(68),'text'=>lang(69),];if(function_exists('gzencode'))$Ij['gz']='gzip';return$Ij;}function
sendDumpOutputHeaders($Pf,$me){if($_POST["output"]=="gz"){header("Content-Type: application/x-gzip");if(function_exists('deflate_init')){$qc=deflate_init(ZLIB_ENCODING_GZIP);ob_start(function($O,$hk)use($qc){return
deflate_add($qc,$O,($hk&PHP_OUTPUT_HANDLER_END?ZLIB_FINISH:ZLIB_NO_FLUSH));},1e6);}else{ob_start(function($O){return
gzencode($O);},1e6);}}}function
dumpTable($P,$Mm,$Io=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($Mm)dump_csv(array_keys(fields($P)));}else{if($Io==2){$m=[];foreach(fields($P)as$B=>$l)$m[]=idf_escape($B)." $l[full_type]";$f="CREATE TABLE ".table($P)." (".implode(", ",$m).")";}else$f=create_sql($P,$_POST["auto_increment"],$Mm);set_utf8mb4($f);if($Mm&&$f){if($Mm=="DROP+CREATE"||$Io==1)echo"DROP ".($Io==2?"VIEW":"TABLE")." IF EXISTS ".table($P).";\n";if($Io==1)$f=remove_definer($f);echo"$f;\n\n";}}}function
dumpData($P,$Mm,$F){if($Mm){$Mh=(DIALECT=="sqlite"?0:1048576);$m=[];$Qf=false;if($_POST["format"]=="sql"){if($Mm=="TRUNCATE+INSERT")echo
truncate_sql($P).";\n";$m=fields($P);if(DIALECT=="mssql"){foreach($m
as$l){if($l["auto_increment"]){echo"SET IDENTITY_INSERT ".table($P)." ON;\n";$Qf=true;break;}}}}$G=Connection::get()->query($F,1);if($G){$jg="";$ob="";$Rg=[];$hf=[];$Pm="";$xc=0;while($I=($P!=''?$G->fetchAssoc():$G->fetchRow())){if(!$Rg){$Y=[];foreach($I
as$W){$l=$G->fetchField();if(!empty($m[$l->name]['generated'])){$hf[$l->name]=true;continue;}$Rg[]=$l->name;$w=idf_escape($l->name);$Y[]="$w = VALUES($w)";}$Pm=($Mm=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Y):"").";\n";}if($_POST["format"]!="sql"){if($Mm=="table"){dump_csv($Rg);$Mm="INSERT";}dump_csv($I);}else{if(!$jg)$jg="INSERT INTO ".table($P)." (".implode(", ",array_map('AdminNeo\idf_escape',$Rg)).") VALUES";foreach($I
as$w=>$W){if(isset($hf[$w])){unset($I[$w]);continue;}$l=$m[$w];$I[$w]=($W===null?"NULL":($W===false?0:unconvert_field($l,preg_match(number_type(),$l["type"])&&!preg_match('~\[~',$l["full_type"])&&is_numeric($W)?$W:(!is_blob($l)||is_utf8($W)?q($W):Driver::get()->quoteBinary($W)))));}$xl=($Mh?"\n":" ")."(".implode(",\t",$I).")";if(!$ob)$ob=$jg.$xl;elseif(DIALECT=="mssql"?$xc%1000!=0:strlen($ob)+4+strlen($xl)+strlen($Pm)<$Mh)$ob
.=",$xl";else{echo$ob.$Pm;$ob=$jg.$xl;}}$xc++;}if($ob)echo$ob.$Pm;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",Connection::get()->getError())."\n";if($Qf)echo"SET IDENTITY_INSERT ".table($P)." OFF;\n";}}function
getImportFilePath(){return"adminneo.sql";}function
printDatabaseMenu(){echo"<p class='links top-links'>\n";$Ki=isset($_GET["ns"])?$_GET["ns"]:null;if($Ki==""&&support("database"))echo'<a href="',h(ME),'database=">',icon("edit"),lang(70),"</a>\n";if($Ki!=""&&support("scheme"))echo"<a href='",h(ME),"scheme='>",icon("edit"),lang(71),"</a>\n";if($Ki!=="")echo'<a href="',h(ME),'schema=">',icon("schema"),lang(72),"</a>\n";if(support("privileges"))echo"<a href='",h(ME),"privileges='>",icon("users"),lang(73),"</a>\n";echo"</p>\n";}function
printNavigation($hi){if($hi=="auth"){$Hj="";foreach((array)$_SESSION["pwds"]as$Eo=>$em){foreach($em
as$L=>$yo){foreach($yo
as$U=>$E){if($E!==null){$Nc=$_SESSION["db"][$Eo][$L][$U];foreach(($Nc?array_keys($Nc):[""])as$i){$bm=$this->admin->getServerName($L,false);$S=h(get_driver_name($Eo,$L)).($U!=""||$bm!=""?" - ":"").h($U).($U!=""&&$bm!=""?"@":"").h($bm).($i!=""?h(" - $i"):"");$Hj
.="<li><a href='".h(auth_url($Eo,$L,$U,$i))."' class='primary' title='$S'>$S</a></li>\n";}}}}}if($Hj)echo"<nav id='logins'><menu>\n$Hj</menu></nav>\n";}else{$this->admin->printDatabaseSwitcher($hi);$xa=[];if(DB==""||!$hi){if(support("sql")){$xa[]="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".icon("command").lang(41)."</a>";$xa[]="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".icon("import").lang(74)."</a>";}$xa[]="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".icon("export").lang(75)."</a>";}if(DB=="")$xa[]='<a href="'.h(ME).'database="'.bold($_GET["database"]==="").">".icon("database-add").lang(76)."</a>\n";if(DB!=""&&$_GET["ns"]===""&&!$hi)$xa[]='<a href="'.h(ME).'scheme="'.bold($_GET["scheme"]==="").">".icon("database-add").lang(77)."</a>\n";if(DB!=""&&$_GET["ns"]!==""&&!$hi)$xa[]='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".icon("table-add").lang(78)."</a>\n";if($xa)echo"<p class='links'>".implode("\n",$xa)."</p>";$R=[];if($_GET["ns"]!==""&&!$hi&&DB!=""){Connection::get()->selectDatabase(DB);$R=table_status('',true);}if($_GET["ns"]!==""&&!$hi&&DB!=""){if($R){$this->admin->printTablesFilter();$this->admin->printTableList($R);}else
echo"<div id='tables'><p>".lang(79)."</p></div>\n";}elseif($_GET["ns"]!==""&&DB!="")echo"<div id='tables'></div>\n";if(support("sql")||DIALECT=="elastic"||DIALECT=="mongo"){echo"<script".nonce().">\n";if(support("sql")&&$R){$th=[];foreach($R
as$P=>$T)$th[]=js_escape_re($P);$an=support("table")&&!$this->config->isSelectionPreferred()?"table":"select";echo"window.jushLinks = { ".DIALECT.": {\n",js_escape_key(ME.$an.'=$&'),': /\b(?<!\$)('.implode('|',$th).')(?!\$)\b/g';$zm=["sql","check","event","procedure","trigger","view","type","table","processlist"];if(support('routine')&&array_intersect_key($_GET,array_flip($zm))){foreach(routines()as$I)echo",\n",js_escape_key(ME.'function='.urlencode($I["SPECIFIC_NAME"]).'&name=$&'),': /\b'.js_escape_re($I["ROUTINE_NAME"]).'(?=["`\]]?\()/g';}echo"\n}};\n";foreach(["bac","bra","sqlite_quo","mssql_bra"]as$W)echo"jushLinks.$W = jushLinks.".DIALECT.";\n";}if(DIALECT!="elastic"&&DIALECT!="mongo"&&$this->getConfig()->isSqlAutocompletionEnabled()&&(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"]))){$ln=array_fill_keys(array_keys($R),[]);foreach(Driver::get()->getAllFields()as$P=>$m){foreach($m
as$l)$ln[$P][]=$l["field"];}echo"window.addEventListener('DOMContentLoaded', () => { autocompletion = jush.autocompleteSql('".idf_escape("")."', ".json_encode($ln,JSON_HEX_TAG)."); });\n";}echo"</script>\n";}echo
script("let autocompletion;\nwindow.addEventListener('DOMContentLoaded', () => { initSyntaxHighlighting('".js_escape(doc_version())."', '".js_escape(Connection::get()->getFlavor())."', autocompletion); });");}}function
printDatabaseSwitcher($hi){$h=$this->admin->getDatabases();if(!$h&&DIALECT!="sqlite")return;echo"<div class='db-selector'><form action=''>";hidden_fields_get();echo"<div>";if($h)echo"<select id='database-select' name='db' title='",lang(31),"'>".optionlist([""=>"(".lang(80).")"]+$h,DB)."</select>".script("mixin(gid('database-select'), {onmousedown: dbMouseDown, onchange: dbChange});");else
echo"<input id='database-select' class='input' name='db' value='".h(DB)."' title='",lang(31),"' autocapitalize='off'>\n";echo"<input type='submit' value='".lang(81)."' class='button ".($h?"hidden":"")."'>\n","</div>";if(support("scheme")&&$hi!="db"&&DB!=""&&Connection::get()->selectDatabase(DB)){echo"<div>","<select id='scheme-select' name='ns' title='",lang(82),"'>".optionlist([""=>"(".lang(83).")"]+$this->admin->getSchemas(),$_GET["ns"])."</select>".script("mixin(gid('scheme-select'), {onmousedown: dbMouseDown, onchange: dbChange});"),"</div>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}foreach(["import","sql","schema","dump","privileges"]as$W){if(isset($_GET[$W])){echo
input_hidden($W);break;}}echo"</form></div>\n";}function
printTableList(array$R){$_d=$this->settings->isNavigationDual()||$this->settings->isNavigationHover();$Vh=($_d?"class='dual".($this->settings->isNavigationHover()?" hover":"")."'":($this->settings->isNavigationReversed()?"class='reversed'":""));echo"<nav id='tables'><div class='scroll-marker'></div><menu $Vh>";foreach($R
as$P=>$N){$P="$P";$B=$this->admin->getTableName($N);if($B==""||(isset($N["Partition"])?$N["Partition"]:false))continue;echo"<li>";$ya=in_array($P,[$_GET["table"],$_GET["select"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"]]);$Lb="primary".(is_view($N)?" view":"");$Tm=support("table")||support("indexes");$Kl=h(ME)."select=".urlencode($P);$cn=h(ME)."table=".urlencode($P);if($this->settings->isSelectionPreferred()){if($this->settings->isNavigationReversed()&&$Tm)echo" <a href='$cn' title='",lang(35),"' class='secondary'>",icon("structure"),"</a>";echo"<a href='$Kl'",bold($ya,$Lb)," data-primary='true' title='$B'>$B</a>";if($_d&&$Tm)echo" <a href='$cn' title='",lang(35),"' class='secondary'>",icon_solo("structure"),"</a>";}else{if($this->settings->isNavigationReversed())echo" <a href='$Kl' title='",lang(34),"' class='secondary'>",icon("data"),"</a>";if($Tm)echo"<a href='$cn'",bold($ya,$Lb)," data-primary='true' title='$B'>$B</a>";else
echo"<span data-primary='true'",bold($ya,$Lb),">$B</span>";if($_d)echo" <a href='$Kl' title='",lang(34),"' class='secondary'>",icon_solo("data"),"</a>";}echo"</li>\n";}echo"</menu></nav>\n",script("initTablesList(".json_encode($this->admin->getDatabase(),JSON_HEX_TAG).");");}function
getSettingsRows($pf){$M=parent::getSettingsRows($pf);if($pf==1){$C=[""=>lang(11),Config::$NavigationSimple=>lang(84),Config::$NavigationDual=>lang(85),Config::$NavigationHover=>lang(86),Config::$NavigationReversed=>lang(87)];$j=$C[$this->config->getNavigationMode()];$C[""].=" ($j)";$M["navigationMode"]="<tr><th>".lang(88)."</th>"."<td>".html_radios("navigationMode",$C,($ta=$this->settings->getParameter("navigationMode"))!==null?$ta:"")."<span class='input-hint'>".lang(89)."</span>"."</td></tr>\n";$C=[""=>lang(11),0=>lang(35),1=>lang(34),];$j=$C[$this->config->isSelectionPreferred()?1:0];$C[""].=" ($j)";$M["preferSelection"]="<tr><th id='label-links'>".lang(90)."</th>"."<td>".html_select("preferSelection",$C,($ta=$this->settings->getParameter("preferSelection"))!==null?$ta:"","","label-links",true)."<span class='input-hint'>".lang(91)."</span>"."</td></tr>\n";}return$M;}function
getForeignColumnInfo(array$Qe,$c){return
null;}}class
TmpFile{private$handler;private$size;function
__construct(){$this->handler=tmpfile();}function
getSize(){return$this->size;}function
write($pc){if(!$this->handler)return;$this->size+=strlen($pc);fwrite($this->handler,$pc);}function
send(){if(!$this->handler)return;fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}function
print_select_result(Result$G,$e=null,array$yj=[],$y=0){$th=[];$v=[];$d=[];$kb=[];$bo=[];$H=[];for($r=0;(!$y||$r<$y)&&($I=$G->fetchRow());$r++){if(!$r){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>";for($Ig=0;$Ig<count($I);$Ig++){$l=$G->fetchField();if(!$l){echo"<th></th>";continue;}$B=$l->name;$xj=isset($l->orgtable)?$l->orgtable:"";$wj=isset($l->orgname)?$l->orgname:$B;if(isset($l->table))$H[$l->table]=$xj;if($yj&&DIALECT=="sql")$th[$Ig]=($B=="table"?"table=":($B=="possible_keys"?"indexes=":null));elseif($xj!=""){if(!isset($v[$xj])){$v[$xj]=[];foreach(indexes($xj,$e)as$u){if($u["type"]=="PRIMARY"){$v[$xj]=array_flip($u["columns"]);break;}}$d[$xj]=$v[$xj];}if(isset($d[$xj][$wj])){unset($d[$xj][$wj]);$v[$xj][$wj]=$Ig;$th[$Ig]=$xj;}}if($l->charsetnr==63)$kb[$Ig]=true;$bo[$Ig]=$l->type;$S=trim(($xj!=""?"$xj.$wj":($l->name!=$wj?$wj:""))." ".Driver::get()->getTypeName($l));echo"<th".($S!=""?" title='".h($S)."'":"").">".h($B).($yj?doc_link(['sql'=>"explain-output.html#explain_".strtolower($B),'mariadb'=>"reference/sql-statements/administrative-sql-statements/analyze-and-explain-statements/explain#columns-in-explain-...-select",]):"");}echo"</thead>\n";}echo"<tr>";foreach($I
as$w=>$W){$z="";if(isset($th[$w])&&!$d[$th[$w]]){if($yj&&DIALECT=="sql"){$P=$I[array_search("table=",$th)];$z=ME.$th[$w].urlencode($yj[$P]!=""?$yj[$P]:$P);}else{$z=ME."edit=".urlencode($th[$w]);foreach($v[$th[$w]]as$Ob=>$Ig)$z
.="&where".urlencode("[".bracket_escape($Ob)."]")."=".urlencode($I[$Ig]);}}$T=($kb[$w]?'blob':($bo[$w]==254?'char':''));$l=['full_type'=>$T,'type'=>$T,];$W=select_value($W,$z,$l,null);$Lb=$bo[$w]<=9||$bo[$w]==246?"class='number'":"";echo"<td $Lb>$W</td>";}}if($r)echo"</table>\n</div>";else
echo"<p class='message'>".lang(92);echo"\n";return$H;}function
referencable_primary($Ql){$H=[];foreach(table_status('',true)as$en=>$P){if($en!=$Ql&&fk_support($P)){foreach(fields($en)as$l){if($l["primary"]){if($H[$en]){unset($H[$en]);break;}$H[$en]=$l;}}}}return$H;}function
textarea($B,$X,$J=10,$Vb=80){echo"<textarea name='".h($B)."' rows='$J' cols='$Vb' class='sqlarea jush-".DIALECT."' spellcheck='false' wrap='off'>";if(is_array($X)){foreach($X
as$W)echo
h($W[0])."\n\n\n";}else
echo
h($X);echo"</textarea>";}function
select_input($Va,$C,$X="",$fj="",$ik=""){if($C&&$X!=""&&!isset($C[$X]))$C=[$X=>$X]+$C;$pn=($C?"select":"input");return"<$pn $Va".($C?"><option value=''>$ik".optionlist($C,$X,true)."</select>":" size='10' value='".h($X)."' placeholder='$ik'>").($fj?script("qsl('$pn').onchange = $fj;",""):"");}function
json_row($w,$W=null){static$Ge=true;if($Ge)echo"{";if($w!=""){echo($Ge?"":",")."\n\t\"".addcslashes($w,"\r\n\t\"\\/").'": '.($W!==null?'"'.addcslashes($W,"\r\n\t\"\\/").'"':'null');$Ge=false;}else{echo"\n}\n";$Ge=true;}}function
edit_type($w,$l,$Rb,$Re=[],$pe=[]){$T=isset($l["type"])?$l["type"]:null;echo'<td><select name="',h($w),'[type]" class="type" aria-labelledby="label-type">';$sd=Driver::get()->getTypes();if($T&&!isset($sd[$T])&&!isset($Re[$T])&&!in_array($T,$pe))$pe[]=$T;$Lm=Driver::get()->getStructuredTypes();if($Re)$Lm[lang(93)]=$Re;echo
optionlist(array_merge($pe,$Lm),$T),'</select><td><input name="',h($w),'[length]" value="',h(isset($l["length"])?$l["length"]:null),'" size="3"',(!(isset($l["length"])?$l["length"]:null)&&preg_match('~var(char|binary)$~',$T)?" class='input required'":" class='input'"),' aria-labelledby="label-length"><td class="options">',($Rb?"<select name='".h($w)."[collation]'".option_types($T,'(char|text|enum|set)$').'><option value="">('.lang(94).')'.optionlist($Rb,isset($l["collation"])?$l["collation"]:null).'</select>':''),(Driver::get()->getUnsigned()?"<select name='".h($w)."[unsigned]'".option_types($T,'^$|'.number_type()).'><option>'.optionlist(Driver::get()->getUnsigned(),isset($l["unsigned"])?$l["unsigned"]:null).'</select>':''),(isset($l['on_update'])?"<select name='".h($w)."[on_update]'".option_types($T,'timestamp|datetime').'>'.optionlist([""=>"(".lang(95).")","CURRENT_TIMESTAMP"],(preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?"CURRENT_TIMESTAMP":$l["on_update"])).'</select>':''),($Re?"<select name='".h($w)."[on_delete]'".option_types($T,'`')."><option value=''>(".lang(96).")".optionlist(Driver::get()->getOnActions(),isset($l["on_delete"])?$l["on_delete"]:null)."</select> ":" ");}function
option_types($T,$bo){return" data-types='".h($bo)."'".(preg_match("~$bo~",$T)?"":" class='hidden'");}function
process_length($x){if(preg_match('~^\s*\(?\s*max\s*\)?\s*$~i',$x))return"(max)";$Td=Driver::$EnumLengthPattern;return(preg_match("~^\\s*\\(?\\s*$Td(?:\\s*,\\s*$Td)*+\\s*\\)?\\s*\$~",$x)&&preg_match_all("~$Td~",$x,$A)?"(".implode(",",$A[0]).")":preg_replace('~^[-+.0-9].*~','(\0)',preg_replace('~[^-0-9,.+()[\]]~','',$x)));}function
process_type($l,$Pb="COLLATE"){return" $l[type]".process_length($l["length"]).(preg_match(number_type(),$l["type"])&&in_array($l["unsigned"],Driver::get()->getUnsigned())?" $l[unsigned]":"").(preg_match('~char|text|enum|set~',$l["type"])&&$l["collation"]?" $Pb ".(DIALECT=="mssql"?$l["collation"]:q($l["collation"])):"");}function
process_field($l,$Yn){if($l["on_update"])$l["on_update"]=preg_replace('~current_timestamp(\(\))?~i',"CURRENT_TIMESTAMP",$l["on_update"]);return[idf_escape(trim($l["field"])),process_type($Yn),($l["null"]?" NULL":" NOT NULL"),default_value($l),(preg_match('~timestamp|datetime~',$l["type"])&&$l["on_update"]?" ON UPDATE ".$l["on_update"]:""),(support("comment")&&$l["comment"]!=""?" COMMENT ".q(normalize_newlines($l["comment"])):""),($l["auto_increment"]?auto_increment():null),];}function
normalize_newlines($X){return
str_replace("\r","",(string)$X);}function
default_value($l){if($l["default"]===null)return"";$j=normalize_newlines($l["default"]);$gf=$l["generated"];if(in_array($gf,Driver::get()->getGenerated())){if(DIALECT=="mssql")return" AS ($j)".($gf=="VIRTUAL"?"":" $gf");else
return" GENERATED ALWAYS AS ($j) $gf";}if(stripos($j,"GENERATED ")===0)return" $j";if(preg_match('~char|binary|text|json|enum|set~',$l["type"])||preg_match('~^(?![a-z])~i',$j)){if(DIALECT=="sql"&&preg_match('~text|json~',$l["type"]))return" DEFAULT (".q($j).")";else
return" DEFAULT ".q($j);}else{$j=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$j);return" DEFAULT ".(DIALECT=="sqlite"?"($j)":$j);}}function
type_class($T){foreach(['char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',]as$Lb=>$ek){if(preg_match("~$Lb|$ek~",$T))return"class='$Lb'";}return"";}function
edit_fields(array$m,array$Rb,$T="TABLE",$Re=[]){$m=array_values($m);$dc=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$bc=$dc?"":"class='hidden'";echo"<thead><tr>\n";if(support("move_col"))echo"<th class='jsonly'></th>";if($T=="PROCEDURE")echo"<td></td>";echo"<th id='label-name'>",($T=="TABLE"?lang(97):lang(98)),"</th>\n","<td id='label-type'>",lang(45),"<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>",script("gid('enum-edit').onblur = onFieldLengthBlur;"),"</td>\n","<td id='label-length'>",lang(99),"</td>\n","<td>",lang(100),"</td>\n";if($T=="TABLE")echo"<td id='label-null'>NULL</td>\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='",lang(49),"'>AI</abbr>",doc_link(['sql'=>"example-auto-increment.html",'mariadb'=>"reference/data-types/auto_increment",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",]),"</td>\n","<td id='label-default'>",lang(101),"</td>\n",support("comment")?"<td id='label-comment' $bc>".lang(48)."</td>\n":"";echo"<td>","<button name='add[",(support("move_col")?0:count($m)),"]' value='1' title='",lang(102),"' class='button light'>",icon_solo("add"),"</button>",(support("move_col")?"":script("qsl('button').onclick = onAddLastFieldRowClick;")),script("row_count = ".count($m).";"),"</td>\n","</tr></thead>\n";$Lb=support("move_col")?"class='sortable'":"";echo"<tbody $Lb>\n";foreach($m
as$r=>$l){$r++;$zj=$l[($_POST?"orig":"field")];$hd=(isset($_POST["add"][$r-1])||(isset($l["field"])&&!(isset($_POST["drop_col"][$r])?$_POST["drop_col"][$r]:null)))&&(support("drop_col")||$zj=="");echo"<tr",($hd?"":" hidden"),">\n";if(support("move_col"))echo"<th class='handle jsonly'>",icon_solo("handle"),"</th>";if($T=="PROCEDURE")echo"<td>",html_select("fields[$r][inout]",Driver::get()->getInOut(),$l["inout"]),"</td>\n";echo"<th>";if($hd)echo"<input class='input' name='fields[$r][field]' value='",h($l["field"]),"' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name' ".(isset($_POST["add"][$r-1])?"autofocus":"").">";echo
input_hidden("fields[$r][orig]",$zj);edit_type("fields[$r]",$l,$Rb,$Re);echo"</th>\n";if($T=="TABLE"){echo"<td>",checkbox("fields[$r][null]",1,$l["null"],"","","block","label-null"),"</td>\n";$Gb=$l["auto_increment"]?"checked":"";echo"<td><label class='block'><input type='radio' name='auto_increment_col' value='$r' $Gb aria-labelledby='label-ai'></label></td>\n","<td class='default-value'>";if(Driver::get()->getGenerated())echo
html_select("fields[$r][generated]",array_merge(["","DEFAULT"],Driver::get()->getGenerated()),$l["generated"]);else
echo
checkbox("fields[$r][generated]",1,$l["generated"],"","","","label-default");$Va="name='fields[$r][default]' aria-labelledby='label-default'";$X=h($l["default"]);if(str_contains($X,"\n")){if($X[0]=="\n")$X="\n$X";echo"<textarea $Va rows='3' cols='30' style='vertical-align: bottom;'>$X</textarea>";}else
echo"<input class='input' $Va value='$X'>";echo"</td>\n";if(support("comment")){$Lh=Connection::get()->isMinVersion("5.5")?1024:255;$Va="name='fields[$r][comment]' data-maxlength='$Lh' aria-labelledby='label-comment'";$X=h($l["comment"]);echo"<td $bc>";if(str_contains($X,"\n")){if($X[0]=="\n")$X="\n$X";echo"<textarea $Va rows='3' cols='30' style='vertical-align: bottom;'>$X</textarea>";}else
echo"<input class='input' $Va value='$X'>";echo"</td>\n";}}echo"<td>";if(support("move_col"))echo"<button name='add[$r]' value='1' title='".lang(102)."' class='button light'>",icon_solo("add"),"</button>","<button name='up[$r]' value='1' title='".lang(103)."' class='button light hidden'>",icon_solo("arrow-up"),"</button>","<button name='down[$r]' value='1' title='".lang(104)."' class='button light hidden'>",icon_solo("arrow-down"),"</button>";if($zj==""||support("drop_col"))echo"<button name='drop_col[$r]' value='1' title='".lang(59)."' class='button light'>",icon_solo("remove"),"</button>";echo"</td>\n</tr>\n";}echo"</tbody>";}function
process_fields(&$m){$Si=0;if($_POST["up"]){$dh=0;foreach($m
as$w=>$l){if(key($_POST["up"])==$w){unset($m[$w]);array_splice($m,$dh,0,[$l]);break;}if(isset($l["field"]))$dh=$Si;$Si++;}}elseif($_POST["down"]){$Xe=false;foreach($m
as$w=>$l){if(isset($l["field"])&&$Xe){unset($m[key($_POST["down"])]);array_splice($m,$Si,0,[$Xe]);break;}if(key($_POST["down"])==$w)$Xe=$l;$Si++;}}elseif($_POST["add"]){$m=array_values($m);array_splice($m,key($_POST["add"]),0,[[]]);}elseif(!$_POST["drop_col"])return
false;return
true;}function
normalize_enum($_){$W=$_[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($W[0].$W[0],$W[0],substr($W,1,-1))),'\\'))."'";}function
grant($kf,array$Ek,$d,$dj,$vo){if(!$Ek)return
true;if($Ek==["ALL PRIVILEGES","GRANT OPTION"]){if($kf)return(bool)queries("GRANT ALL PRIVILEGES ON $dj TO $vo WITH GRANT OPTION");else
return
queries("REVOKE ALL PRIVILEGES ON $dj FROM $vo")&&queries("REVOKE GRANT OPTION ON $dj FROM $vo");}if($Ek==["GRANT OPTION","PROXY"]){if($kf)return(bool)queries("GRANT PROXY ON $dj TO $vo WITH GRANT OPTION");else
return(bool)queries("REVOKE PROXY ON $dj FROM $vo");}return(bool)queries(($kf?"GRANT ":"REVOKE ").preg_replace('~(GRANT OPTION)\([^)]*\)~','$1',implode("$d, ",$Ek).$d)." ON $dj ".($kf?"TO ":"FROM ").$vo);}function
drop_create($ud,$f,$wd,$un,$yd,$Bh,$Zh,$Xh,$Yh,$aj,$Ci){if($_POST["drop"])query_redirect($ud,$Bh,$Zh);elseif($aj=="")query_redirect($f,$Bh,$Yh);elseif(support("transaction_ddl")){Driver::get()->begin();queries_redirect($Bh,$Xh,queries($ud)&&queries($f)&&Driver::get()->commit());Driver::get()->rollback();}elseif($aj!=$Ci){$_c=queries($f);queries_redirect($Bh,$Xh,$_c&&queries($ud));if($_c)queries($wd);}else
queries_redirect($Bh,$Xh,queries($un)&&queries($yd)&&queries($ud)&&queries($f));}function
create_trigger($dj,array$Sn){$Dn=" $Sn[Timing] $Sn[Event]".(preg_match('~ OF~',$Sn["Event"])?" $Sn[Of]":"");return"CREATE TRIGGER ".idf_escape($Sn["Trigger"]).(DIALECT=="mssql"?$dj.$Dn:$Dn.$dj).rtrim(" $Sn[Type]\n$Sn[Statement]",";").";";}function
create_routine($rl,$I){$hm=[];$m=(array)$I["fields"];ksort($m);$Uf=implode("|",Driver::get()->getInOut());foreach($m
as$l){if($l["field"]!="")$hm[]=(preg_match("~^($Uf)\$~",$l["inout"])?"$l[inout] ":"").idf_escape($l["field"]).process_type($l,"CHARACTER SET");}$Tc=rtrim($I["definition"],";");return"CREATE $rl ".idf_escape(trim($I["name"]))." (".implode(", ",$hm).")".($rl=="FUNCTION"?" RETURNS".process_type($I["returns"],"CHARACTER SET"):"").($I["language"]?" LANGUAGE $I[language]":"").(DIALECT=="pgsql"?" AS ".q($Tc):"\n$Tc;");}function
remove_definer($F){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$F);}function
format_foreign_key($p){$ej=implode("|",Driver::get()->getOnActions());$i=$p["db"];$Ki=$p["ns"];return" FOREIGN KEY (".implode(", ",array_map('AdminNeo\idf_escape',$p["source"])).") REFERENCES ".($i!=""&&$i!=$_GET["db"]?idf_escape($i).".":"").($Ki!=""&&$Ki!=$_GET["ns"]?idf_escape($Ki).".":"").idf_escape($p["table"])." (".implode(", ",array_map('AdminNeo\idf_escape',$p["target"])).")".(preg_match("~^($ej)\$~",$p["on_delete"])?" ON DELETE $p[on_delete]":"").(preg_match("~^($ej)\$~",$p["on_update"])?" ON UPDATE $p[on_update]":"").(isset($p["deferrable"])?" $p[deferrable]":"");}function
tar_file($o,TmpFile$Hn){$Af=pack("a100a8a8a8a12a12",$o,644,0,0,decoct($Hn->getSize()),decoct(time()));$Ib=8*32;for($r=0;$r<strlen($Af);$r++)$Ib+=ord($Af[$r]);$Af
.=sprintf("%06o",$Ib)."\0 ";echo$Af,str_repeat("\0",512-strlen($Af));$Hn->send();echo
str_repeat("\0",511-($Hn->getSize()+511)%512);}function
doc_link(array$dk,$vn="<sup>?</sup>"){if(!(isset($dk[DIALECT])?$dk[DIALECT]:null))return"";$Fo=doc_version();$qo=['sql'=>"https://dev.mysql.com/doc/refman/$Fo/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(Connection::get()->isCockroachDB()?"current":$Fo)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".str_replace(".","",$Fo)."&id=",'elastic'=>"https://www.elastic.co/guide/en/elasticsearch/reference/$Fo/",];if(Connection::get()->isMariaDB()){$qo['sql']="https://mariadb.com/docs/server/";$dk['sql']=isset($dk['mariadb'])?$dk['mariadb']:str_replace(".html","",$dk['sql']);}return"<a href='".h($qo[DIALECT].$dk[DIALECT].(DIALECT=='mssql'?"?view=sql-server-ver$Fo":""))."'".target_blank().">$vn</a>";}function
doc_version(){return
preg_replace('~^(\d\.?\d).*~s','\1',Connection::get()->getVersion());}function
db_size($i){if(!Connection::get()->selectDatabase($i))return"?";$H=0;foreach(table_status()as$Q)$H+=$Q["Data_length"]+$Q["Index_length"];return
format_number($H);}function
set_utf8mb4($f){static$hm=false;if(!$hm&&preg_match('~\butf8mb4~i',$f)){$hm=true;echo"SET NAMES ".charset(Connection::get()).";\n\n";}}error_reporting(E_ALL&~E_DEPRECATED);set_error_handler(function($Vd,$k){return(bool)preg_match('~^Undefined (array key|offset|index)~',$k);},E_WARNING|E_NOTICE);;$De=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($De||ini_get("filter.default_flags")){foreach(['_GET','_POST','_COOKIE','_SERVER']as$W){$io=filter_input_array(constant("INPUT$W"),FILTER_UNSAFE_RAW);if($io)$$W=$io;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");class
Server{private$params;private$key;function
__construct(array$Nj,$w=null){$this->params=$Nj;$this->key=$w;}function
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
getConfigParams(){$Nj=isset($this->params["config"])?$this->params["config"]:[];$jf=["servers"];foreach($jf
as$Mj){if(isset($Nj[$Mj]))unset($Nj[$Mj]);}return$Nj;}}class
Config{static$NavigationSimple="simple";static$NavigationDual="dual";static$NavigationHover="hover";static$NavigationReversed="reversed";private$params;private$servers=[];function
__construct(array$Nj){$this->params=$Nj;if(isset($this->params["servers"])){foreach($this->params["servers"]as$w=>$L){$Zl=new
Server($L,is_string($w)?$w:null);$this->params["servers"][$w]=$Zl;$this->servers[$Zl->getKey()]=$Zl;}}}function
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
getDefaultDriver(array$td){$rd=isset($this->params["defaultDriver"])?$this->params["defaultDriver"]:null;return$rd&&isset($td[$rd])?$rd:key($td);}function
getDefaultServer(){$L=isset($this->params["defaultServer"])?$this->params["defaultServer"]:null;if($L===null)return
null;$Zl=isset($this->params["servers"][$L])?$this->params["servers"][$L]:null;if($Zl)return$Zl->getKey();return$L;}function
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
getServerPairs(array$td){$nm=null;foreach($this->servers
as$L){if(!isset($td[$L->getDriver()]))continue;if(!$nm)$nm=$L->getDriver();elseif($L->getDriver()!=$nm){$nm=null;break;}}$am=[];foreach($this->servers
as$w=>$L){if(!isset($td[$L->getDriver()]))continue;$Yl=$L->getName();if($nm&&$Yl)$am[$w]=$Yl;else$am[$w]=$td[$L->getDriver()].($Yl!=""?" - $Yl":"");}return$am;}function
getServer($Xl){return
isset($this->servers[$Xl])?$this->servers[$Xl]:null;}function
applyServer($L){$L=$this->getServer($L);if(!$L)return;$this->params=array_merge($this->params,$L->getConfigParams());}private
function
parseList($vh){if(is_array($vh))return$vh;return
preg_split('~\s*,\s*~',(string)$vh);}}class
Settings{private
static$CookieName="neo_settings";static$ColorSchemeLight="light";static$ColorSchemeDark="dark";static$NavigationWidthMin=10;static$NavigationWidthMax=30;private$config;private$params=[];function
__construct(Config$hc){$this->config=$hc;if(isset($_COOKIE[self::$CookieName])){parse_str($_COOKIE[self::$CookieName],$this->params);$this->save();}if(isset($_COOKIE["neo_lang"])){$this->updateParameter("lang",$_COOKIE["neo_lang"]);unset($_COOKIE["neo_lang"]);cookie("neo_lang","",-3600);}}static
function
readParameter($w){parse_str(isset($_COOKIE[self::$CookieName])?$_COOKIE[self::$CookieName]:"",$Nj);return
isset($Nj[$w])?$Nj[$w]:null;}function
getParameter($w,$j=null){return
isset($this->params[$w])?$this->params[$w]:$j;}function
updateParameter($w,$X){$this->updateParameters([$w=>$X]);}function
updateParameters(array$Nj){$this->params=array_filter(array_merge($this->params,$Nj),function($X){return$X!==null;});$this->save();}private
function
save(){cookie(self::$CookieName,http_build_query($this->params),7776000);}function
getTheme(){return($ta=$this->getParameter("theme"))!==null?$ta:$this->config->getTheme();}function
getColorScheme(){return$this->getParameter("colorScheme");}function
getNavigationMode(){return($ta=$this->getParameter("navigationMode"))!==null?$ta:$this->config->getNavigationMode();}function
isNavigationSimple(){return$this->getNavigationMode()==Config::$NavigationSimple;}function
isNavigationDual(){return$this->getNavigationMode()==Config::$NavigationDual;}function
isNavigationHover(){return$this->getNavigationMode()==Config::$NavigationHover;}function
isNavigationReversed(){return$this->getNavigationMode()==Config::$NavigationReversed;}function
getNavigationWidth(){$Xo=$this->getParameter("navigationWidth");if($Xo===null)return
null;return
min(max((float)$Xo,self::$NavigationWidthMin),self::$NavigationWidthMax);}function
isSelectionPreferred(){return($ta=$this->getParameter("preferSelection"))!==null?$ta:$this->config->isSelectionPreferred();}function
isRelationLinks(){return
isset($this->params["relationLinks"])?$this->params["relationLinks"]:$this->config->isRelationLinks();}function
getRecordsPerPage(){return($ta=$this->getParameter("recordsPerPage"))!==null?$ta:$this->config->getRecordsPerPage();}function
getEnumAsSelectThreshold(){$X=$this->getParameter("enumAsSelectThreshold");if($X<0)return
null;return$X!==null?(int)$X:$this->config->getEnumAsSelectThreshold();}}class
Hash{static
function
hkdf($x,$w,$eg="",$yl=""){if(extension_loaded("hash")&&PHP_VERSION_ID>=70120)return
hash_hkdf("sha1",$w,$x,$eg,$yl);if($yl=="")$yl=str_repeat("\0",20);$Fk=self::hmacSha1($w,$yl);$Wi="";for($Pg="",$lb=1;!isset($Wi[$x-1]);$lb++){$Pg=self::hmacSha1($Pg.$eg.chr($lb),$Fk);$Wi
.=$Pg;}return
substr($Wi,0,$x);}static
function
hmacSha1($g,$w){if(!extension_loaded("hash"))return
hash_hmac("sha1",$g,$w,true);if(strlen($w)>64)$w=sha1($w,true);$w=str_pad($w,64,"\0");$vg=($w^str_repeat("\x36",64));$ij=($w^str_repeat("\x5C",64));return
sha1($ij.sha1($vg.$g,true),true);}}class
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
\Sodium\randombytes_buf($x);$ho=DIRECTORY_SEPARATOR==="/";if($ho){$G=self::readDevUrandom($x);if($G!==false)return$G;}$pb=$ho&&PHP_VERSION_ID>50609&&PHP_VERSION_ID<50613;if(extension_loaded("mcrypt")&&!$pb){$G=mcrypt_create_iv($x,MCRYPT_DEV_URANDOM);if($G!==false)return$G;}$qb=PHP_VERSION_ID<50444||(PHP_VERSION_ID>50500&&PHP_VERSION_ID<50528)||(PHP_VERSION_ID>50600&&PHP_VERSION_ID<50612);if(extension_loaded("openssl")&&!$qb){$G=openssl_random_pseudo_bytes($x,$Jm);if($Jm)return$G;}return
false;}private
static
function
readDevUrandom($x){static$n=null;if($n===null)$n=@fopen("/dev/urandom","rb");if(!$n)return
false;$fl=$x;$G="";do{$g=fread($n,$fl);if($g===false)return
false;$fl-=strlen($g);$G
.=$g;}while($fl>0);return$G;}private
static
function
readCapicom($x){$Xb=new
\COM("CAPICOM.Utilities.1");$fl=$x;$G="";do{$g=base64_decode((string)$Xb->GetRandom($x,0));$fl-=strlen($g);$G
.=$g;}while($fl>0);return$G;}private
static
function
lastResortRandom($x){static$w=null;static$yl=null;if($w===null){$g=$_SERVER;$g[]=uniqid("",true);shuffle($g);$w=sha1(serialize($g),true);if(extension_loaded("openssl"))$yl=openssl_random_pseudo_bytes(20);else{$yl="";for($r=0;$r<20;$r++)$yl
.=chr((mt_rand()^mt_rand())%256);}}else{if((ord($w)%2===0)===(ord($yl)%2===0))$w=Hash::hmacSha1($w,$yl);else$yl=Hash::hmacSha1($yl,$w);}return
Hash::hkdf($x,$w,"$x",$yl);}}if(!function_exists("str_starts_with")){function
str_starts_with($_f,$yi){return
strpos($_f,$yi)===0;}}if(!function_exists("str_contains")){function
str_contains($_f,$yi){return
strpos($_f,$yi)!==false;}}if(!function_exists("password_verify")){function
password_verify($E,$zf){return
false;}}if(!function_exists("ini_set")){function
ini_set($oj,$X){return
false;}}function
version(){return
VERSION;}function
idf_unescape($t){if(!preg_match('~^[`\'"[]~',$t))return$t;$dh=substr($t,-1);return
str_replace($dh.$dh,$dh,substr($t,1,-1));}function
q($O){return
Connection::get()->quote($O);}function
number($W){return
preg_replace('~[^0-9]+~','',$W);}function
int_type(){return'(tiny|small|medium|big)?int(eger|\d)?';}function
number_type(){return'(^('.int_type().'|decimal|numeric|real|(binary_|half_|scaled_)?float\d?|(binary_)?double( precision)?|(small)?money)$)';}function
remove_slashes(array$Y,$De=false){$H=[];foreach($Y
as$w=>$W)$H[stripslashes($w)]=(is_array($W)?remove_slashes($W,$De):($De?$W:stripslashes($W)));return$H;}function
bracket_escape($t,$db=false){static$Nn=[':'=>':1',']'=>':2','['=>':3','"'=>':4'];return
strtr($t,($db?array_flip($Nn):$Nn));}function
min_version($Fo,$Eh=null,$e=null){if(!$e)$e=Connection::get();if($Eh&&$e->isMariaDB())$Fo=$Eh;return$Fo&&$e->isMinVersion($Fo);}function
charset(Connection$e){return($e->isMinVersion("5.5.3")?"utf8mb4":"utf8");}function
link_files($B,array$Ce){switch($B){case'favicon-blue.ico':$o='favicon-blue-0f5ce53a66b1e25395d0048da369f19e__590bdf89.ico';break;case'favicon-green.ico':$o='favicon-green-def78cfa7c465c8b0e9966e3eb87407d__590bdf89.ico';break;case'favicon-orange.ico':$o='favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__590bdf89.ico';break;case'favicon-purple.ico':$o='favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__590bdf89.ico';break;case'favicon-red.ico':$o='favicon-red-c2ebb34a8df5aba28e15d87728a151df__590bdf89.ico';break;case'favicon-blue.svg':$o='favicon-blue-17e440832c1eac07527560a0d6f0d2ee__590bdf89.svg';break;case'favicon-green.svg':$o='favicon-green-bb254c95a033f67e3d433a3df63e160d__590bdf89.svg';break;case'favicon-orange.svg':$o='favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__590bdf89.svg';break;case'favicon-purple.svg':$o='favicon-purple-4cfd57d31ab991e8071fe34060cd3123__590bdf89.svg';break;case'favicon-red.svg':$o='favicon-red-a006e401273230fd6be80568c8361b57__590bdf89.svg';break;case'apple-touch-icon-blue.png':$o='apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__590bdf89.png';break;case'apple-touch-icon-green.png':$o='apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__590bdf89.png';break;case'apple-touch-icon-orange.png':$o='apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__590bdf89.png';break;case'apple-touch-icon-purple.png':$o='apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__590bdf89.png';break;case'apple-touch-icon-red.png':$o='apple-touch-icon-red-507228751d2170d047e72142d2c02390__590bdf89.png';break;case'logo.svg':$o='logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg';break;case'jush.css':$o='jush-b3a93b18444da26820ff61746521dede__4fa892fb.css';break;case'jush-dark.css':$o='jush-dark-f8dac59c6ad1018686e52a0e0357e421__2ec7793c.css';break;case'jush.js':$o='jush-31ccd1ce96536a294822e952872683d1__1d05977c.js';break;case'icons.svg':$o='icons-70163a2695280bf75edba563e7b5471b__929918c3.svg';break;case'default-blue.css':$o='default-blue-e7acfdb81453b86f081569afa115859e__54f93b23.css';break;case'default-green.css':$o='default-green-268f042072e76ab07aa2fd0350e0aa0c__54f93b23.css';break;case'default-orange.css':$o='default-orange-e4e5ea626cdcbe83e07c7933dc04f916__54f93b23.css';break;case'default-purple.css':$o='default-purple-6b1de1f635d52b55797976fef486a515__54f93b23.css';break;case'default-red.css':$o='default-red-0f424ea89c2a43c6eb0ec8f0e22362a5__54f93b23.css';break;case'default-blue-dark.css':$o='default-blue-dark-1061ad7d216f143e3626560b92b66061__42198a80.css';break;case'default-green-dark.css':$o='default-green-dark-6176b1c7b42ee9f244b3a9c3d8065728__42198a80.css';break;case'default-orange-dark.css':$o='default-orange-dark-ef164a8d58dc89b2719f881377f73c89__42198a80.css';break;case'default-purple-dark.css':$o='default-purple-dark-0f4fa03fa2d9287ef390780e90d9b06d__42198a80.css';break;case'default-red-dark.css':$o='default-red-dark-3c1e28afe2cc92815bc7347df0d5776f__42198a80.css';break;case'main.js':$o='main-c1727fb392fab162b6611efe18642a5a__693556d7.js';break;default:$o=null;break;}if(!$o)return
null;return
BASE_URL."?file=".urldecode($o);}function
ini_bool($oj){$W=ini_get($oj);return
preg_match('~^(on|true|yes)$~i',$W)||(int)$W;}function
ini_bytes($gg){$W=ini_get($gg);switch(strtolower(substr($W,-1))){case'g':$W=(int)$W*1024;case'm':$W=(int)$W*1024;case'k':$W=(int)$W*1024;}return$W;}function
max_input_vars($I,$Fj){$Ih=(int)ini_get("max_input_vars");return($Ih?(int)floor(($Ih-$Fj)/$I):0);}function
max_input_vars_error(){$gg="max_input_vars";return
lang(105,"$gg = ".(int)ini_get($gg));}function
sid(){static$H;if($H===null)$H=(session_id()&&!($_COOKIE&&ini_bool("session.use_cookies")));return$H;}function
save_driver_name($rd,$L,$B){restart_session();$_SESSION["drivers"][$rd][$L]=$B;stop_session();}function
get_driver_name($rd,$L=null){return
isset($_SESSION["drivers"][$rd][$L])?$_SESSION["drivers"][$rd][$L]:Drivers::get($rd);}function
save_login($rd,$L,$U,$E,$i=""){$w=isset($_COOKIE["neo_key"])?$_COOKIE["neo_key"]:null;$_SESSION["pwds"][$rd][$L][$U]=$w?[encrypt_string($E,$w)]:$E;$_SESSION["db"][$rd][$L][$U][$i]=true;}function
delete_login($rd,$L,$U){unset($_SESSION["pwds"][$rd][$L][$U]);unset($_SESSION["db"][$rd][$L][$U]);}function
get_password(){$E=get_session("pwds");if(is_array($E))return$_COOKIE["neo_key"]?decrypt_string($E[0],$_COOKIE["neo_key"]):false;return$E;}function
get_vals($F,$c=0){$H=[];$G=Connection::get()->query($F);if(is_object($G)){while($I=$G->fetchRow())$H[]=$I[$c];}return$H;}function
get_key_vals($F,$e=null,$im=true){if(!$e)$e=Connection::get();$H=[];$G=$e->query($F);if(is_object($G)){while($I=$G->fetchRow()){if($im)$H[$I[0]]=$I[1];else$H[]=$I[0];}}return$H;}function
get_rows($F,$e=null,$k="<p class='error'>"){if(!$e)$e=Connection::get();$H=[];$G=$e->query($F);if(is_object($G)){while($I=$G->fetchAssoc())$H[]=$I;}elseif(!$G&&!is_object($e)&&$k&&(defined("AdminNeo\PAGE_HEADER")||$k=="-- "))echo$k.error()."\n";return$H;}function
unique_array(array$I,array$v){foreach($v
as$u){if(!preg_match("~^(PRIMARY|UNIQUE)$~",$u["type"])||(isset($u["partial"])?$u["partial"]:"")!="")continue;$eo=[];foreach($u["columns"]as$w){if(!isset($I[$w]))continue
2;$eo[$w]=$I[$w];}return$eo;}return
null;}function
escape_key($w){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$w,$_))return$_[1].idf_escape(idf_unescape($_[2])).$_[3];return
idf_escape($w);}function
where($Z,$m=[]){$gc=[];foreach((array)$Z["where"]as$w=>$W){$w=bracket_escape($w,true);$c=escape_key($w);$l=isset($m[$w])?$m[$w]:null;$ze=isset($l["type"])?$l["type"]:null;$cf=isset($l["full_type"])?$l["full_type"]:null;$Ag=$l&&(is_blob($l)||preg_match('~binary~',$ze));if($Ag&&!is_utf8($W))$gc[]="$c = ".Driver::get()->quoteBinary($W);elseif(DIALECT=="sql"&&$ze=="json")$gc[]="$c = CAST(".q($W)." AS JSON)";elseif(DIALECT=="pgsql"&&preg_match('~^jsonb?$~',$cf))$gc[]="$c::jsonb = ".q($W)."::jsonb";elseif(DIALECT=="sql"&&is_numeric($W)&&strpos($W,".")!==false)$gc[]="$c LIKE ".q($W);elseif(DIALECT=="mssql"&&strpos($ze,"datetime")===false)$gc[]="$c LIKE ".q(preg_replace('~[_%[]~','[\0]',$W));else$gc[]="$c = ".(isset($m[$w])?unconvert_field($m[$w],q($W)):q($W));if(DIALECT=="sql"&&preg_match('~char|text~',$ze)&&preg_match("~[^ -@]~",$W))$gc[]="$c = ".q($W)." COLLATE ".charset(Connection::get())."_bin";}foreach((array)$Z["null"]as$w)$gc[]=escape_key($w)." IS NULL";return
implode(" AND ",$gc);}function
where_columns($Z,$m=[]){$d=[];foreach((array)$Z["null"]as$w)$d[$w]=true;foreach((array)$Z["where"]as$w=>$W){$w=bracket_escape($w,true);foreach($m
as$B=>$l){if($w==$B||strpos($w,idf_escape($B))!==false)$d[$B]=true;}}return$d;}function
where_check($W,$m=[]){parse_str($W,$Cb);remove_slashes([&$Cb]);return
where($Cb,$m);}function
where_link($r,$c,$X,$lj="="){return"&where%5B$r%5D%5Bcol%5D=".urlencode($c)."&where%5B$r%5D%5Bop%5D=".urlencode(($X!==null?$lj:"IS NULL"))."&where%5B$r%5D%5Bval%5D=".urlencode($X);}function
convert_fields(array$d,array$m,array$K=[]){$G="";foreach($d
as$w=>$W){if($K&&!in_array(idf_escape($w),$K))continue;$Sa=convert_field($m[$w]);if($Sa)$G
.=", $Sa AS ".idf_escape($w);}return$G;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),[";"=>"%3B",","=>"%2C"]);}function
cookie($B,$X,$nh=2592000){header("Set-Cookie: $B=".rawurlencode($X).($nh?"; expires=".gmdate("D, d M Y H:i:s",time()+$nh)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_url($po,$qc){$http_response_header=null;$Xd=[];set_error_handler(function($Vd,$k)use(&$Xd){$Xd[]=preg_replace('~^file_get_contents\([^)]*\):\s*~','',$k);return
true;});$H=file_get_contents($po,false,$qc);restore_error_handler();$Bf=function_exists('http_get_last_response_headers')?http_get_last_response_headers():$http_response_header;return[$H,(array)$Bf,$H===false?implode("\n",$Xd):"",];}function
get_settings($tc="neo_settings"){parse_str(isset($_COOKIE[$tc])?$_COOKIE[$tc]:"",$M);return$M;}function
get_setting($w,$tc="neo_settings"){$M=get_settings($tc);return
isset($M[$w])?$M[$w]:null;}function
save_settings(array$M,$tc="neo_settings"){cookie($tc,http_build_query($M+get_settings($tc)));}function
restart_session(){if(!ini_bool("session.use_cookies")&&session_status()==PHP_SESSION_NONE)session_start();}function
stop_session($Oe=false){$to=ini_bool("session.use_cookies");if(!$to||$Oe){session_write_close();if($to&&ini_set("session.use_cookies","0")===false)session_start();}}function&get_session($w){return$_SESSION[$w][DRIVER][SERVER][$_GET["username"]];}function
set_session($w,$W){$_SESSION[$w][DRIVER][SERVER][$_GET["username"]]=$W;}function
auth_url($Eo,$L,$U,$i=null){$oo=remove_from_uri(implode("|",array_keys(Drivers::getList()))."|username|ext|".($i!==null?"db|":"").($Eo=='mssql'||$Eo=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$oo,$_);return"$_[1]?".(sid()?session_name()."=".urlencode(session_id())."&":"").($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"").urlencode($Eo)."=".urlencode($L)."&"."username=".urlencode($U).($i!=""?"&db=".urlencode($i):"").($_[2]?"&$_[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($Bh,$Wh=null){if($Wh!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($Bh!==null?$Bh:$_SERVER["REQUEST_URI"]))][]=$Wh;}if($Bh!==null){if($Bh=="")$Bh=".";header("Location: $Bh");exit;}}function
query_redirect($F,$Bh,$Wh,$Vk=true,$ee=true,$qe=false,$Bn=""){if($ee){$Em=microtime(true);$qe=!Connection::get()->query($F);$Bn=format_time($Em);}$xm=$F?Admin::get()->formatMessageQuery($F,$Bn,$qe):"";if($qe){Admin::get()->addError(error().$xm.script("initToggles();"));return
false;}if($Vk)redirect($Bh,$Wh.$xm);return
true;}function
queries_redirect($Bh,$Wh,$Vk){$Kk=implode("\n",Queries::$queries);$Bn=format_time(Queries::$start);return
query_redirect($Kk,$Bh,$Wh,$Vk,false,!$Vk,$Bn);}class
Queries{static$queries=[];static$start=0.0;}function
remember_query($F){if(!Queries::$start)Queries::$start=microtime(true);if(support("sql"))Queries::$queries[]=(preg_match('~;$~',$F)?"DELIMITER ;;\n$F;\nDELIMITER ":$F).";";else
Queries::$queries[]=$F;}function
queries($F){remember_query($F);return
support("sql")?Connection::get()->query($F):[];}function
apply_queries($F,array$R,$Yd='AdminNeo\table'){foreach($R
as$P){if(!queries("$F ".$Yd($P)))return
false;}return
true;}function
format_time($Em){return
lang(106,max(0,microtime(true)-$Em));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($Mj=""){return
substr(preg_replace("~(?<=[?&])($Mj".(sid()?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($w,$Oc=false,$Vc=""){$n=$_FILES[$w];if(!$n)return
null;foreach($n
as$w=>$W)$n[$w]=(array)$W;$H='';foreach($n["error"]as$w=>$k){if($k)return$k;$B=$n["name"][$w];$In=$n["tmp_name"][$w];$oc=file_get_contents($Oc&&preg_match('~\.gz$~',$B)?"compress.zlib://$In":$In);if($Oc){$Em=substr($oc,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Em))$oc=iconv("utf-16","utf-8",$oc);elseif($Em=="\xEF\xBB\xBF")$oc=substr($oc,3);}if($Vc){if(!preg_match("~$Vc\\s*\$~",$oc))$oc
.=";";$oc
.="\n\n";}$H
.=$oc;}return$H;}function
upload_error($k){$Qh=($k==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($k?lang(107).($Qh?" ".lang(108,$Qh):""):lang(109));}function
repeat_pattern($ek,$x){return
str_repeat("$ek{0,65535}",$x/65535)."$ek{0,".($x%65535)."}";}function
is_utf8($W){return(preg_match('~~u',$W)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$W));}function
format_number($W){return
strtr(number_format($W,0,".",lang(110)),preg_split('~~u',lang(111),-1,PREG_SPLIT_NO_EMPTY));}function
format_rows(array$Q){$J=$Q["Rows"];$Oa=($J&&(DIALECT=="sqlite"||(isset($Q["Engine"])?$Q["Engine"]:"")==(DIALECT=="pgsql"?"table":"InnoDB")));return($Oa?"~ ":"").format_number($J);}function
friendly_url($W){return
preg_replace('~\W~i','-',$W);}function
table_status1($P,$se=false){$H=table_status($P,$se);return($H?reset($H):["Name"=>$P]);}function
column_foreign_keys($P){$H=[];foreach(Admin::get()->getForeignKeys($P)as$p){foreach($p["source"]as$W)$H[$W][]=$p;}return$H;}function
fields_from_edit(){$H=[];foreach((array)$_POST["field_keys"]as$w=>$W){if($W!=""){$W=bracket_escape($W);$_POST["function"][$W]=$_POST["field_funs"][$w];$_POST["fields"][$W]=$_POST["field_vals"][$w];}}foreach((array)$_POST["fields"]as$w=>$W){$B=bracket_escape($w,true);$H[$B]=["field"=>$B,"full_type"=>"varchar","type"=>"varchar","privileges"=>["insert"=>1,"update"=>1,"where"=>1,"order"=>1],"null"=>true,"auto_increment"=>($w==Driver::get()->primary),];}return$H;}function
dump_headers($Pf,$pi=false){$Pf=friendly_url($Pf).date("-Ymd-His");$me=Admin::get()->sendDumpHeaders($Pf,$pi);if($me===null)$me=Admin::get()->sendDumpFormatHeaders($Pf,$pi);Admin::get()->sendDumpOutputHeaders($Pf,$me);$Hj=$_POST["output"];if($Hj!="text")header("Content-Disposition: attachment; filename=$Pf.$me".($Hj!="file"&&preg_match('~^[0-9a-z]+$~',$Hj)?".$Hj":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$me;}function
dump_table_order(array$wi,array$bl){$Wg=array_flip($wi);$uj=[];$No=[];$Ec=false;$Mo=function($B)use(&$Mo,&$uj,&$No,&$Ec,$Wg,$bl){if(isset($uj[$B]))return;if(isset($No[$B])){$Ec=true;return;}$No[$B]=true;foreach(isset($bl[$B])?$bl[$B]:[]as$Zk){if(isset($Wg[$Zk]))$Mo($Zk);}unset($No[$B]);$uj[$B]=true;};foreach($wi
as$B)$Mo($B);return($Ec?null:array_keys($uj));}function
dump_csv($I){$Xn=$_POST["format"]=="tsv";foreach($I
as$w=>$W){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($Xn?'\t':'[,;]|^$').'~',$W))$I[$w]='"'.str_replace('"','""',$W).'"';}echo
implode(($_POST["format"]=="csv"?",":($Xn?"\t":";")),$I)."\r\n";}function
apply_sql_function($q,$c){return($q?($q=="unixepoch"?"DATETIME($c, '$q')":($q=="count distinct"?"COUNT(DISTINCT ":strtoupper("$q("))."$c)"):$c);}function
get_temp_dir(){$ck=ini_get("upload_tmp_dir");if(!$ck)$ck=sys_get_temp_dir();return$ck;}function
open_file_with_lock($o){if(is_link($o))return
null;$n=@fopen($o,"c+");if(!$n)return
null;@chmod($o,0660);if(!flock($n,LOCK_EX)){fclose($n);return
null;}return$n;}function
write_and_unlock_file($n,$g){rewind($n);fwrite($n,$g);ftruncate($n,strlen($g));unlock_file($n);}function
unlock_file($n){flock($n,LOCK_UN);fclose($n);}function
first(array$Ra){return
reset($Ra);}function
get_private_key($f){$o=get_temp_dir()."/adminneo.key";if(!$f&&!file_exists($o))return
false;$n=open_file_with_lock($o);if(!$n)return
false;$w=stream_get_contents($n);if(!$w){$w=Random::strongKey();write_and_unlock_file($n,$w);}else
unlock_file($n);return$w;}function
get_random_string(){return
Random::strongKey();}function
select_value($W,$z,$l,$yn){if(is_array($W)){$H="";if(array_filter($W,'is_array')==array_values($W)){$Rg=[];foreach($W
as$V)$Rg+=array_fill_keys(array_keys($V),null);foreach(array_keys($Rg)as$Lg)$H
.="<th>".h($Lg);foreach($W
as$V){$H
.="<tr>";foreach(array_merge($Rg,$V)as$zo)$H
.="<td>".select_value($zo,$z,$l,$yn);}}else{foreach($W
as$Lg=>$V)$H
.="<tr>".($W!=array_values($W)?"<th>".h($Lg):"")."<td>".select_value($V,$z,$l,$yn);}return"<table>$H</table>";}$Cl="";if($l&&$W!==null&&($yn===null||strlen($W)<=$yn)&&($Y=Driver::get()->explodeArrayValue($W,$l["full_type"],$Cl))){$Bl=$l;$Bl["type"]=$Bl["full_type"]=$Cl;$H=select_array_value($Y,$W,$z,$Bl,$yn);return
Driver::get()->implodeArrayValues($H,$l["full_type"]);}if(!$z)$z=Admin::get()->getFieldValueLink($W,$l);if($l)$W=Connection::get()->formatValue($W,$l);$H=$l?Admin::get()->formatFieldValue($W,$l):$W;if($H!==null){if(!is_utf8($H))$H="\0";elseif($yn!=""&&is_shortable($l))$H=truncate_utf8($H,max(0,+$yn));else$H=h($H);}return
Admin::get()->formatSelectionValue($H,$z,$l,$W);}function
select_array_value(array$Y,$W,$z,array$l,$yn){$G=[];foreach($Y
as$X){if(is_array($X))$G[]=select_array_value($X,$W,$z,$l,$yn);else{$Xg=preg_replace('~(where%5B\d+%5D%5Bval%5D=)'.preg_quote(urlencode($W),"~")."~",'${1}'.urlencode($X),$z);$G[]=select_value($X,$Xg,$l,$yn);}}return$G;}function
is_blob(array$l){$bo=Driver::get()->getStructuredTypes();$T=lang(112);return
preg_match('~blob|bytea|raw|file'.(DIALECT=="mssql"?'|binary|image':'').'~',$l["type"])&&!in_array($l["type"],isset($bo[$T])?$bo[$T]:[]);}function
is_generated_always(array$l){return(isset($l["generated"])?$l["generated"]:"")!=""||stripos((string)(isset($l["default"])?$l["default"]:""),"GENERATED ALWAYS AS ")===0;}function
parenthesize_expression($ke){$Im=preg_replace('~\'(?:[^\']|\'\')*\'|"(?:[^"]|"")*"|`(?:[^`]|``)*`|\[[^]]*]~',"",$ke);if(!preg_match('~^\(.*\)$~s',$Im))return"($ke)";$Xc=0;$dh=strlen($Im)-1;for($r=0;$r<$dh;$r++){if($Im[$r]=="(")$Xc++;elseif($Im[$r]==")"&&--$Xc==0)return"($ke)";}return$ke;}function
is_mail($X){return
is_string($X)&&filter_var($X,FILTER_VALIDATE_EMAIL);}function
is_web_url($X){if(!is_string($X)||!preg_match('~^(https?:)?//~i',$X))return
false;$ec=parse_url($X);if(!$ec)return
false;$po=$X;if(isset($ec['path'])){$Od=array_map('urlencode',explode('/',$ec['path']));$po=str_replace($ec['path'],implode('/',$Od),$po);}if(isset($ec['query'])){parse_str($ec['query'],$Nj);$po=str_replace($ec['query'],http_build_query($Nj),$po);}if(!isset($ec['scheme']))$po="https:$po";return(bool)filter_var($po,FILTER_VALIDATE_URL);}function
is_shortable($l){return$l&&!preg_match('~'.number_type().'|date|time|year~',$l["type"]);}function
host_port($L){return(preg_match('~^(:([^:].*)|(\[(.+)]|(([^:]+://)?[^:]+))(:(\d+))?)$~',$L,$_)?[(isset($_[4])?$_[4]:"").(isset($_[5])?$_[5]:""),$_[2].(isset($_[8])?$_[8]:"")]:[$L,'']);}function
count_rows($P,$Z,$Bg,$nf){$F=" FROM ".table($P).($Z?" WHERE ".implode(" AND ",$Z):"");return($Bg&&(DIALECT=="sql"||count($nf)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$nf).")$F":"SELECT COUNT(*)".($Bg?" FROM (SELECT 1$F GROUP BY ".implode(", ",$nf).") x":$F));}function
slow_query($F){$i=Admin::get()->getDatabase();$Cn=Admin::get()->getQueryTimeout();$pm=Driver::get()->slowQuery($F,$Cn);$e=null;if(!$pm&&support("kill")){$e=connect();if($e&&($i==""||$e->selectDatabase($i))){$Tg=number($e->getValue(connection_id()));echo'<script',nonce(),'>
	const timeout = setTimeout(() => {
		ajax(\'',js_escape(ME),'script=kill\', function() {
		}, \'kill=',$Tg,'&token=',get_token(),'\');
	}, ',1000*$Cn,');
</script>
';}}ob_flush();flush();$H=@get_key_vals(($pm?:$F),$e,false);if($e){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$H;}function
get_token(){$Qk=rand(1,1e6);return($Qk^$_SESSION["token"]).":$Qk";}function
verify_token(){list($Jn,$Qk)=explode(":",$_POST["token"]);return($Qk^$_SESSION["token"])==$Jn&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],["","same-origin"]);}function
script($tm,$Mn="\n"){return"<script".nonce().">$tm</script>$Mn";}function
script_src($po,$Sc=false){return"<script src='".h($po)."'".nonce().($Sc?" defer":"")."></script>\n";}function
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
icon($s,$Lb=null){$s=h($s);return"<svg class='icon ic-$s $Lb'><use href='".link_files("icons.svg",[])."#$s'/></svg>";}function
checkbox($B,$X,$Gb,$Yg="",$gj="",$Lb="",$ah=""){$H="<input type='checkbox' name='$B' value='".h($X)."'".($Gb?" checked":"").($ah?" aria-labelledby='$ah'":"").">".($gj?script("qsl('input').onclick = function () { $gj };",""):"");return($Yg!=""||$Lb?"<label".($Lb?" class='$Lb'":"").">$H".h($Yg)."</label>":$H);}function
optionlist($C,$Nl=null,$uo=false){$H="";foreach($C
as$Lg=>$V){$qj=[$Lg=>$V];if(is_array($V)){$H
.='<optgroup label="'.h($Lg).'">';$qj=$V;}foreach($qj
as$w=>$W)$H
.='<option'.($uo||is_string($w)?' value="'.h($w).'"':'').($Nl!==null&&($uo||is_string($w)?(string)$w:$W)===$Nl?' selected':'').'>'.h($W);if(is_array($V))$H
.='</optgroup>';}return$H;}function
html_select($B,$C,$X="",$fj="",$ah="",$uo=false){static$Yg=0;$Zg="";if(!$ah&&substr(isset($C[""])?$C[""]:"",0,1)=="("){$Yg++;$ah="label-$Yg";$Zg="<option value='' id='$ah'>".h($C[""]);unset($C[""]);}return"<select name='".h($B)."'".($ah?" aria-labelledby='$ah'":"").">".$Zg.optionlist($C,$X,$uo)."</select>".($fj?script("qsl('select').onchange = function () { $fj };",""):"");}function
html_radios($B,$C,$X=""){$G="<span class='labels'>";foreach($C
as$w=>$W)$G
.="<label><input type='radio' name='".h($B)."' value='".h($w)."'".($w==$X?" checked":"").">".h($W)."</label>";$G
.="</span>";return$G;}function
confirm($Wh="",$Pl="qsl('input')"){return
script("$Pl.onclick = () => confirm('".js_escape($Wh?:lang(113))."');","");}function
print_fieldset_start($s,$jh,$Of,$Ko=false,$rm=false){echo"<fieldset id='fieldset-$s' class='closable ".(!$Ko?" closed":"")."'>","<legend><a href='#'>$jh</a></legend>",icon($Of,"fieldset-icon jsonly"),"<div class='fieldset-content".($rm?" sortable":"")."'>";}function
print_fieldset_end($s,$rm=false){echo"</div>",script("initFieldset('$s');","");if($rm)echo
script("initSortable('#fieldset-$s .fieldset-content');","");echo"</fieldset>\n";}function
bold($mb,$Lb=""){return($mb?" class='$Lb active'":($Lb?" class='$Lb'":""));}function
js_escape($O){return
str_replace("<","\\x3C",addcslashes($O,"\r\n'\\"));}function
js_escape_key($O){return'"'.str_replace("<","\\x3C",addcslashes($O,"\r\n\t\"\\")).'"';}function
js_escape_re($O){return
addcslashes(preg_quote($O,"/"),"\r\n");}function
pagination($D,$Bc){return"<li>".($D==$Bc?"<strong>".($D+1)."</strong>":'<a href="'.h(remove_from_uri("page").($D?"&page=$D".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($D+1)."</a>")."</li>";}function
print_hidden_fields(array$Gk,array$Rf=[],$wk=""){$G=false;foreach($Gk
as$w=>$W){if(!in_array($w,$Rf)){if(is_array($W))print_hidden_fields($W,[],$w);else{$G=true;echo
input_hidden($wk?$wk."[$w]":$w,$W);}}}return$G;}function
hidden_fields_get(){if(sid())echo
input_hidden(session_name(),session_id());if($_GET["ext"])echo
input_hidden("ext",$_GET["ext"]);if(SERVER!==null)echo
input_hidden(DRIVER,SERVER);echo
input_hidden("username",$_GET["username"]);}function
enum_input($Va,array$l,$X,$Ld=null,$Fb=false){preg_match_all("~'((?:[^']|'')*)'~",$l["length"],$A);$Y=$A[1];$An=Admin::get()->getSettings()->getEnumAsSelectThreshold();$K=!$Fb&&$An!==null&&count($Y)>$An;$T=$Fb?"checkbox":"radio";$za=$K?"selected":"checked";$G=$K?"<select $Va>":"<span class='labels'>";if($K&&$l["null"]&&$Ld!==""){$Gb=$X===null?$za:"";$G
.="<option value='__adminneo_empty__' disabled $Gb></option>";}if($Ld!==null){$Gb=(is_array($X)?in_array($Ld,$X):$X===$Ld)?$za:"";if($K)$G
.="<option value='$Ld' $Gb>".lang(114)."</option>";else$G
.="<label><input type='$T' $Va value='$Ld' $Gb><i>".lang(114)."</i></label>";}foreach($Y
as$W){if($Ld===""&&$W==="")continue;$W=stripcslashes(str_replace("''","'",$W));$Gb=is_array($X)?in_array($W,$X):$X===$W;$Gb=$Gb?$za:"";$We=$W===""?("<i>".lang(114)."</i>"):h(Admin::get()->formatFieldValue($W,$l));if($K)$G
.="<option value='".h($W)."' $Gb>$We</option>";else$G
.=" <label><input type='$T' $Va value='".h($W)."' $Gb>$We</label>";}$G
.=$K?"</select>":"</span>";return$G;}function
input($l,$X,$q,$ab=false){$B=h(bracket_escape($l["field"]));$bo=Driver::get()->getTypes();$Cg=isset($l["full_type"])&&Admin::get()->detectJson($l["full_type"],$X,true);$hl=(DIALECT=="mssql"&&$l["auto_increment"]&&!$_POST["clone"]);if($hl&&!$_POST["save"])$q=null;$Zn=Driver::get()->getUserTypeId(isset($l["type"])?$l["type"]:"");if($Zn){$Ud=type_values($Zn);if($Ud){$l["type"]="enum";$l["length"]=$Ud;}}$Va=" name='fields[$B]' ".($ab?" autofocus":"");$ff=(isset($_GET["select"])||$hl?["orig"=>lang(115)]:[])+Admin::get()->getFieldFunctions($l);$xf=(in_array($q,$ff)||isset($ff[$q]));echo"<td class='function'>",Driver::get()->getUnconvertFunction($l)." ";if(count($ff)>1){$Nl=$q===null||$xf?$q:"";echo"<select name='function[$B]'>".optionlist($ff,$Nl)."</select>",help_script_command("value.replace(/^SQL\$/, '')",true),script("qsl('select').onchange = functionChange;","");}else
echo
h(reset($ff));echo"</td><td>";$hg=Admin::get()->getFieldInput(isset($_GET["edit"])?$_GET["edit"]:null,$l,$Va,$X,$q);if($hg!="")echo$hg;elseif(preg_match('~bool~',$l["type"]))echo"<input type='hidden'$Va value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?" checked":"")."$Va value='1'>";elseif($l["type"]=="enum")echo
enum_input($Va,$l,$X);elseif($l["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$l["length"],$A);echo"<span class='labels'>";foreach($A[1]as$W){$W=stripcslashes(str_replace("''","'",$W));$Gb=$X!==null&&in_array($W,explode(",",$X),true);$Gb=$Gb?"checked":"";$We=$W===""?("<i>".lang(114)."</i>"):h(Admin::get()->formatFieldValue($W,$l));echo" <label><input type='checkbox' name='fields[$B][]' value='".h($W)."' $Gb>$We</label>";}echo"</span>";}elseif(is_blob($l)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$B'>";elseif($Cg)echo"<textarea $Va cols='50' rows='12' class='jush-json'>".h($X).'</textarea>';elseif(($vn=preg_match('~text|lob|memo|json~i',$l["type"])||(preg_match('~char|binary~',$l["type"])&&$l["length"]=="max"))||preg_match("~\n~",$X)){if($vn&&DIALECT!="sqlite")$Va
.=" cols='50' rows='12'";else{$J=min(12,substr_count($X,"\n")+1);$Va
.=" cols='30' rows='$J'";}echo"<textarea $Va>".h($X).'</textarea>';}else{$Sh=!preg_match('~int~',$l["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$l["length"],$_)?((preg_match("~binary~",$l["type"])?2:1)*$_[1]+($_[3]?1:0)+($_[2]&&!$l["unsigned"]?1:0)):($bo&&$bo[$l["type"]]?$bo[$l["type"]]+($l["unsigned"]?0:1):0);if(DIALECT=='sql'&&Connection::get()->isMinVersion("5.6")&&preg_match('~time~',$l["type"]))$Sh+=7;echo"<input class='input'".((!$xf||$q==="")&&preg_match('~^'.int_type().'$~',$l["type"])&&!preg_match('~\[]~',$l["full_type"])?" type='number'":"").($q!="now"?" value='".h($X)."'":" data-last-value='".h($X)."'").($Sh?" data-maxlength='$Sh'":"").(preg_match('~char|binary~',$l["type"])&&$Sh>20?" size='44'":"")."$Va>";}$Ff=Admin::get()->getFieldInputHint($_GET["edit"],$l,$X);if($Ff!="")echo" <span class='input-hint'>$Ff</span>";if(count($ff)>1)echo
script("qs('select', qsl('td').previousSibling).onchange(null, true);","");$He=0;foreach($ff
as$w=>$W){if($w===""||!$W)break;$He++;}if(count($ff)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $He);");}function
process_input($l){if(is_generated_always($l))return
null;$t=bracket_escape($l["field"]);$q=isset($_POST["function"][$t])?$_POST["function"][$t]:"";if($q=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?idf_escape($l["field"]):false);if($q=="NULL")return
Driver::get()->getNull();if(is_blob($l)&&ini_bool("file_uploads")){$n=get_file("fields-$t");if(!is_string($n))return
false;return
Driver::get()->quoteBinary($n);}$X=isset($_POST["fields"][$t])?$_POST["fields"][$t]:(isset($_FILES["fields"]["name"][$t])?$_FILES["fields"]["name"][$t]:null);if($X===null)return
false;if($l["auto_increment"]&&$X=="")return
null;if($l["type"]=="set")$X=implode(",",(array)$X);if($q=="json"){$X=json_decode($X,true);if(!is_array($X))return
false;return$X;}if($q!="SQL"&&isset($l["full_type"]))Admin::get()->detectJson($l["full_type"],$X,false);return
Admin::get()->processFieldInput($l,$X,$q);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$ml=$Xd=[];foreach(table_status("",true)as$P=>$Q){$en=Admin::get()->getTableName($Q);if(!isset($Q["Engine"])||$en==""||($_POST["tables"]&&!in_array($P,$_POST["tables"])))continue;$G=Connection::get()->query("SELECT".limit("1 FROM ".table($P)," WHERE ".implode(" AND ",Admin::get()->processSelectionSearch(fields($P),[])),1));if($G&&!$G->fetchRow())continue;$z=h(ME."select=".urlencode($P)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]));if($G)$ml[]="<li><a href='$z'>".icon("search")."$en</a></li>";else$Xd[]="<div class='error'><a href='$z'>$en</a>: ".error()."</div>";}if($ml)echo"<ul class='links'>\n",implode("\n",$ml),"</ul>\n";if($Xd)echo
implode("\n",$Xd),"\n";if(!$ml&&!$Xd)echo"<p class='message'>".lang(79)."</p>\n";}function
help_script($vn,$mm=false){return
script("initHelpFor(qsl('select, input'), '".h($vn)."', $mm);","");}function
help_script_command($Yb,$mm=false){return
script("initHelpFor(qsl('select, input'), (value) => { return $Yb; }, $mm);","");}function
edit_form($P,$m,$I,$no){$en=Admin::get()->getTableName(table_status1($P,true));$S=$no?lang(39):lang(116);page_header("$S: $en",["select"=>[$P,$en],$S]);if($I===false){echo"<p class='error'>".lang(92)."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$Gd=false;$Wo=($no&&!isset($_GET["select"])?where_columns($_GET,$m):[]);$rc=(count($Wo)!=count($m));if(!$rc)$Wo=[];if(!$m)echo"<p class='error'>".lang(117)."\n";else{echo"<table class='box'>".script("qsl('table').onkeydown = onEditingKeydown;");$ab=!$_POST;foreach($m
as$B=>$l){echo"<tr".(isset($Wo[$B])?" class='where-column'":"")."><th>".Admin::get()->getFieldName($l);$w=bracket_escape($B);$j=isset($_GET["preset"][$w])?$_GET["preset"][$w]:null;if($j===null){$j=$l["default"];if($l["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$j,$dl))$j=$dl[1];if(DIALECT=="sql"&&preg_match('~binary~',$l["type"]))$j=bin2hex($j);}$X=($I!==null?($I[$B]!=""&&DIALECT=="sql"&&preg_match("~enum|set~",$l["type"])&&is_array($I[$B])?implode(",",$I[$B]):(is_bool($I[$B])?+$I[$B]:$I[$B])):(!$no&&$l["auto_increment"]?"":(isset($_GET["select"])?false:$j)));if(!$_POST["save"]&&is_string($X))$X=Admin::get()->formatFieldValue($X,$l);if(($no&&!isset($l["privileges"]["update"]))||is_generated_always($l)){echo"<td class='function'></td><td>";if($no||!$l["generated"])echo
select_value($X,'',$l,null);else
echo"<code class='jush-".DIALECT."'>",h($X),"</code>";echo"</td>";}else{$Gd=true;$q=($_POST["save"]?isset($_POST["function"][$w])?$_POST["function"][$w]:"":($no&&preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?"now":($X===false?null:($X!==null?'':'NULL'))));if(!$_POST&&!$no&&$X==$l["default"]&&preg_match('~^[\w.]+\(~',$X))$q="SQL";if(preg_match("~time~",$l["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$X)){$X="";$q="now";}if($l["type"]=="uuid"&&$X=="uuid()"){$X="";$q="uuid";}if($ab!==false)$ab=($l["auto_increment"]||$q=="now"||$q=="uuid"?null:true);input($l,$X,$q,(bool)$ab);if($ab)$ab=false;}echo"\n";}if(!support("table")&&!fields($P))echo"<tr>"."<th><input class='input' name='field_keys[]'>".script("qsl('input').oninput = fieldChange;","")."<td class='function'>".html_select("field_funs[]",Admin::get()->getFieldFunctions(["null"=>isset($_GET["select"])]))."<td><input class='input' name='field_vals[]'>"."\n";echo"</table>\n",script("initToggles(gid('form'));");if($Wo)echo
script("initWhereChange();");}echo"<p>";if($Gd){echo"<input type='submit' class='button default' value='".lang(118)."'>\n";if(!isset($_GET["select"])&&$rc){$gd=($Wo&&Admin::get()->getErrors()?" disabled":"");echo"<input type='submit' class='button' name='insert' value='".($no?lang(119):lang(120))."' title='Ctrl+Shift+Enter'$gd>\n",($no?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".js_escape(lang(121))."', this); };"):"");}}echo($no?"<input type='submit' class='button' name='delete' value='".lang(122)."'>".confirm()."\n":"");if(isset($_GET["select"]))print_hidden_fields(["check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]]);echo
input_hidden("referer",isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),input_hidden("save","1"),input_token(),"</form>\n";}function
file_upload_form_script($Se,$ig){$Kh=ini_get("max_file_uploads");$Qh=ini_get("upload_max_filesize");$Rh=ini_bytes("upload_max_filesize");return
script("initFilesUploadForm('".js_escape($Se)."', '".js_escape($ig)."', "."$Kh, '".js_escape(lang(123,$Kh,"'max_file_uploads'"))."', "."$Rh, '".js_escape(lang(124,$Qh,"'upload_max_filesize'"))."')");}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($O,$bd=""){$La=array_flip(str_split(compress_alphabet()));$x=strlen($O);$Bo=($x?13*($x-1)/2-$La[$O[0]]:0);$ib="";$kl=0;$ll=0;for($r=1;$r<$x;$r+=2){$kl=($kl<<13)+$La[$O[$r]]*93+$La[$O[$r+1]];$ll+=13;while($ll>=8&&$Bo>=8){$ll-=8;$Bo-=8;$ib
.=chr($kl>>$ll);$kl&=(1<<$ll)-1;}}if($ib=="")return"";if($bd!=""&&function_exists('inflate_init'))return
inflate_add(inflate_init(ZLIB_ENCODING_RAW,['dictionary'=>$bd]),$ib,ZLIB_FINISH);return$bd==""&&function_exists('gzinflate')?gzinflate($ib):inflate($ib,$bd);}function
inflate($ib,$bd=""){$kh=[3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258];$lh=[0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0];$jd=[1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577];$ld=[0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13];$H=$bd;$qk=0;do{$Fe=inflate_bits($ib,$qk,1);$T=inflate_bits($ib,$qk,2);if(!$T){$qk=($qk+7)&~7;$x=inflate_bits($ib,$qk,16);$qk+=16;$H
.=substr($ib,$qk>>3,$x);$qk+=$x<<3;}else{if($T==1){$xh=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$md=array_fill(0,30,5);}else{$wh=inflate_bits($ib,$qk,5)+257;$kd=inflate_bits($ib,$qk,5)+1;$rj=[16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15];$ci=array_fill(0,19,0);$bi=inflate_bits($ib,$qk,4)+4;for($r=0;$r<$bi;$r++)$ci[$rj[$r]]=inflate_bits($ib,$qk,3);$di=inflate_table($ci);$mh=[];while(count($mh)<$wh+$kd){$Um=inflate_symbol($ib,$qk,$di);if($Um==16)$mh=array_merge($mh,array_fill(0,inflate_bits($ib,$qk,2)+3,end($mh)));elseif($Um==17)$mh=array_merge($mh,array_fill(0,inflate_bits($ib,$qk,3)+3,0));elseif($Um==18)$mh=array_merge($mh,array_fill(0,inflate_bits($ib,$qk,7)+11,0));else$mh[]=$Um;}$xh=array_slice($mh,0,$wh);$md=array_slice($mh,$wh);}$yh=inflate_table($xh);$od=inflate_table($md);while(($Um=inflate_symbol($ib,$qk,$yh))!=256){if($Um<256)$H
.=chr($Um);else{$x=$kh[$Um-257]+inflate_bits($ib,$qk,$lh[$Um-257]);$nd=inflate_symbol($ib,$qk,$od);$Si=strlen($H)-$jd[$nd]-inflate_bits($ib,$qk,$ld[$nd]);for($r=0;$r<$x;$r++)$H
.=$H[$Si+$r];}}}}while(!$Fe);return$bd==""?$H:substr($H,strlen($bd));}function
inflate_bits($ib,&$qk,$xc){$H=0;for($r=0;$r<$xc;$r++){$H+=((ord($ib[$qk>>3])>>($qk&7))&1)<<$r;$qk++;}return$H;}function
inflate_table(array$mh){$P=[];$Nb=0;for($jb=1;$jb<=max($mh);$jb++){foreach($mh
as$Um=>$x){if($x==$jb){$P[$jb][$Nb]=$Um;$Nb++;}}$Nb<<=1;}return$P;}function
inflate_symbol($ib,&$qk,array$P){$Nb=0;$jb=0;do{$Nb=($Nb<<1)+inflate_bits($ib,$qk,1);$jb++;}while(!isset($P[$jb][$Nb]));return$P[$jb][$Nb];}if(isset($_GET["file"]))load_compiled_file($_GET["file"]);function
load_compiled_file($o){if($o==""){http_response_code(404);exit;}if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){http_response_code(304);exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");ini_set("zlib.output_compression","1");$me=pathinfo($o,PATHINFO_EXTENSION);switch($me){case"css":header("Content-Type: text/css; charset=utf-8");break;case"js":header("Content-Type: text/javascript; charset=utf-8");break;case"ico":header("Content-Type: image/x-icon");break;case"png":header("Content-Type: image/png");break;case"svg":header("Content-Type: image/svg+xml");break;}switch($o){case'favicon-blue-0f5ce53a66b1e25395d0048da369f19e__590bdf89.ico':$g='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGZowEUFhM2G6A4QAJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+cUn3+6qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9jompoYUe3hg9tFCL+dNX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryEWPJCE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gDMBOIUjRp9w0AAAAASUVORK5CYII=';break;case'favicon-green-def78cfa7c465c8b0e9966e3eb87407d__590bdf89.ico':$g='AAABAAEAICAAAAEAIAC+AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYVJREFUeNpiYOhiAFBfBxBohGEcxs/RmgQQEQDbQAACBAFCmBDgogLCZkN0BwiATBZQZNsAgmwAgyMAuRZCAtvkGLTy7sEHcFbvfWT4AbjH8f75Huq/CXBRgY8lQuzx29gjxBI+KnBtBDxFgJ+QO/1AgKw24AW+Q1La4rkm4DPEkk+agCvEkqsmQKxSBGwhlkSagA7Eko72DNs4QZRO8NIOUQk+1pAbreGjlGaI3ibENNDFEO+MIbpoJHz0jfYKRngCRymLEUQXABzQRxHOjYro4wBJGwAAEaYYoIeXRg8DTBHZ2oHo0TvwGmLJK01ADnNISnPkFAEA2jhC7nSEl2YHmnAMF1VMsEEMAQDE2GCCKlw4RlMT8Ad1OAnyeGbk4SSo46I9wzPGKMC5UwFjnLVneIEYMWZo/SOmgBZmiCGA7g98hSSIscM3Y4cYkuCLJsCDWOJpAjL4CEnpAzLaHXAxhSi9h2vjZVTDCnKjFWqw/jYsI8ACIX4ZIRYIUMbfEW3maO8YAIxSqCXQN5/tAAAAAElFTkSuQmCC';break;case'favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__590bdf89.ico':$g='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRATANhCAAEGAEGbIwEUFhM2G6A4QAJksoMi2AQTZAAZHAEsthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9fO1dVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwAv8gqS0xXNNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwMddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyBV7BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4FsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8CO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09ALYCTFLvUfsXAAAAAElFTkSuQmCC';break;case'favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__590bdf89.ico':$g='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGYIcFEBYbMhugM0AJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+e8Ln66qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9iompoYUe3hg9tFCL+dOX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryAWPJcE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gD0xZAYUYkFLAAAAAASUVORK5CYII=';break;case'favicon-red-c2ebb34a8df5aba28e15d87728a151df__590bdf89.ico':$g='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRAQY20AAAgQBQpghwEUFhM2G6A4QAJksoMi2AQTZBhgcAUgthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9eOZdVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwEv8gqS0xQtNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwKddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyB17BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4HsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8DO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09AE8YAEN5XivhAAAAAElFTkSuQmCC';break;case'favicon-blue-17e440832c1eac07527560a0d6f0d2ee__590bdf89.svg':$g='+<bATb3V?$so%el,wEIwK&mlYjGZ$a8-HGs8y$j)-UBmQx`Cf?>]C6?xmhS1<w
ZNSJl63"aZ]nB<rgk|tG)vC,pHYx;Wb2NVXBMxd!lQ=g0"!mM[]c*SW?7
E^]B7t[fHolNczfsymCW
CM~8Ult[(brlOx`71nDc
H;N~ybgNd*l6LMbRk;>%06rQeBnDc14r_7Z0b9uqO=xV5=22d05hr#V]F`V>ZD,#JA9[$XEV-*TBfz7Z%
rEJw!G!cT+[7>-u:iVU)N$iv%ySN.`._&}sV@FUIuH)%cY-0#OXWPT8t./3Oid8~R]3?9n;/Qs
Y2!K`1Q9d
tys6C=xmJfXFCT%0xl`H&%njK7`N[.q6jET6kV
VqmiJoIrZ#rsP~q0vDN<FH1w9l4R-?A#H:#onn0@0]3dNk3,E{<7r;2q
u)F!d(nhskXC~JT4N!~!g52r#`3hF[%j
oPE~ZV(W_~g#t?WXQCxEe7)ZQKGxei4.gu_R>pAL]HDZf!uSL)$_)^vZ:Xk![_HKh=C|S~PjqHcgjUutq#-~?PmA#<MYyg2R';break;case'favicon-green-bb254c95a033f67e3d433a3df63e160d__590bdf89.svg':$g='&<bATb3V?$so%eo_t_hLePj9EjS(Ic^3}i[Os!U@i%X`9w9h7
[@
I:KPV|A4v9o?tG^4G8Kl/Za|j!Q;%pO#+U]tOQ@f!m@5m(iU+Xd
:mOOX[.7/V%^=Dj0`KWYnDy%^,cfeZsrK*5P&I[0
$m~<+JorKkYLM7yy$Aw$[w/57.-wX^0JIMH;`
EW/c}a9c

eAhG[68u92FB)
ToD/k0uYFG=PN>>d,^BJ&icL,/i,2N?:udHuPgxk>l07BF^82,>cspK:`^P,q[%?5=4<`h.?.kJ"X_a_mu|Z>-SL,/k)gDk#g):;if"T:5u9
?>JfFYF#KP)D%8xDljc2l^mJ<^%4+Iu=v?FB.Y^67X>9:@KMfCyqQL.-Kmn#DsAler%=^*ps8vI)CEG%8
bknN$znn
D-t3DO&3,C5<7r@2q],)F!d(nhsf)H-JU7?!^"9+Xtpm.q8>NCVHgU$kcVodiJa6[qLov!)IR#}x3*ku*P%3jGe?w?dILBVvhJ~,bFMpBcD%CK/#{0d-|Mu[Pn{fe.3aFbOf6.%,.opHOIuO9';break;case'favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__590bdf89.svg':$g='&<bATb3V?$so%el,wEIwL!M$a_R(Ic^2cYP0
-+_=VtkTnq5G
]9Lh@D6K6GH(sVGneM;s+raV;M^D!*+KY*IOa=3Z`PmB;9_2Obq7yBg2Z%>bw,v+Y%P7|fhq*F156s0beWd,6`ay=p@_gN3Z=jciDmX_i72]oyww{Z~&Ut4M]*axgl)b!a1ju/uH<*9Xo
=
Ul9*$m_7{*MB>d@6aDhpC0Rt_1D-,/u//bLNxN!Dc[m?bh@-`9rmyGW2b(F%J[Qafv1UZr>a>F6JA8g[!0b@[fJtmTv4e%TYc-0#j"5iK2z=wl
*E@MT<:>g<H|W|2(rwB
g5j1,D7hGqj!`uuvXM9_hJa7+NN~VgB]NK[T3a!z#1A}OTmcvh["nTvNeTw$$;eT6q?q1?CDM!=<1SnN]QTt9lj&eNk(4c>}6hisx;^`kO(cefsRGicdC|"j9npLdO:cM^+&QT[{IL-Wu]3@7yXoO46wh/&9Sd+Vo~(7pzhcG()N3^n#?6MAX=Pd
]uX2I0XtRaah;-u5eKzydbLJw$UJhJ]
fe:Fe6mt>cu';break;case'favicon-purple-4cfd57d31ab991e8071fe34060cd3123__590bdf89.svg':$g='+<bAU6+V?$so%eoa6[DcHOAP_Cx(^^F;2nJOs!U09RM?Tw1h7?fDMh@D6K6GH(sW:c#bW`9DTcKpCf,1a1E%kD+5q+F]4B,8UB|ML]o,4O$F:-Sutmd.1l:6ly,?7MjE>rM=td#VYm/o}bPN3Z=AH2GFCrK4TIrcvu65o!8H3d"9V^}B7s3mnUM&Wx87ya:7X
mAh1Y7Wu72FB8FRoD/k0uYFG=PV;h%?KNUP%#GQ^
2w"
SkNgvI0*kTGqx))i0|m-597vz$R_y_,e<pwj=Y`"7[1E)$2(fp:Ex.$(:Op<o1CeTD_yBV
#FT:r
y-y?.@i`K.+Yg?j_Hk`_>f$l~LS;5RSh?>{b"
&+evz;:0eol+,pv/]hQo|q*A;p{F69^H{
F#X:3c%4:=Hdpu{k&lMXV@E/#GL#?#)e-WHQiGP@.%eYsSRXBg!n9tO*z&WP*$Up~J1]E18NCC{!iER:u`UBQ5(*#viS-0S(llO)OsT0bALBjGk4Bt}3Hwkvp!81Fs-^5[}2H/0[(gXX++<t3ZZ577
3fS]`cB;^5uWM3tX';break;case'favicon-red-a006e401273230fd6be80568c8361b57__590bdf89.svg':$g='+<bAU6+V?$so%eoa6[DcEe<SKeo.[BnWu^_0
-+j@96@+X_5GA4^m3%R;yn_USCF5vXi6B6jvyvvy?qZYfND@5~KR9wPw1q,+w{:cwGa2aY)<GPqWy/nLYzy>c3Au_/MA7,dc
}`rf-`x<FH$&bI]FGspJPra.)yE]$w~aKaM]on_y4%i2=`y?0`vYw6}rUy&B3JD1F
/B
o8ro30<1Tp4r,.qWnFpndQQq#ek`C+.f19/9#q+uNg4bc6H.9(02NJtu){yYINu`Uzs:%?o1GH"Lgtxvhu>9uq8)/:th8&TH,OT*]5<Ydlap!w8k>zUUDvh@h+)F+>T8=R`(;8*p2Il^<$eSAzo6lU8L_P_Yh-i#lD(4lV7"52"_UdLUTuCV+Yf/[^)f(~i
.,!Hkau}dsr
i84
>s)_:!#hJQ9:ex&|].#nB#/g7`Ds1xz$U+"f!p-.*YXigq8vUBF!9lVoojveL
+8ox,X;hOaXS*cTW+ODnn.]r,!3BBNvdJ~,brQumcAQJa9E)x*rt!$x~u(xCb
69OF`xI}1->oD?M:yg2R';break;case'apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__590bdf89.png':$g='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAK7UlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUa502RxeG0bX17btm3b/tu2bdu2PbZt21bY+6xqPduTSSddSd1zPyTdVZk+p5+prr51f7d83ixVOXXH55Yte6No2r25Qy/O/Pg7OB/4ykFO0cC/4FBma6qq2Tsmb+SVGe9/7f86zWhMFx+HQ5mjs7XmwITMT77LXe+R04WOdFdw+Ka1xOzL7vML7rTLTnd+RMHhW+Z01Owfz911izOE8IMKDl8wh6W9dPHLbsFCOD/Izyo4pB8z3EyG4GPJK5rTqeCQ2HgEGECGeL5MVHBIPAM1CAvh/AkFh5Rvrf/13STr0x/UHpmO4yXzn+7+3pev+VA0puN/fX/hDyk4fOSBkjPg96KN09KRN/KqbuBoSzsnGtNRz8NFwSFBDJRAeDdwCOsqS8/86Nu9gYP4mK25WsGBaXVNlpS8plORFeuP5k/ZkPbVnLj3J0e9Nib8uWEhj/QPvPvz8zd/eAa/54vzfOUgp2hAs6kb0uhCR7rXN1s0I42AN7dNDxxYY/CG3sCB1+wb66dwNLVaQ5Jqlu/L/XxWLPf+hvdOu8X5qS9mxS7fnxuaVNvcZtXcaiyI6IeDN9KyFW/3Bg6eTf4FR1p+0/ydWS+ODL3xfe6lsc6feGlk6IKdWWkFzW5Za+WG6YNDzF5bWIx1GQ7cUpXr+3DklLQs3pP9zNBg7plX/NmhIUv25OSWuv4KwFK7TjhsjZXic2dRQsYH3/h3OFLP6oSDP+rLcDAV6DM3jttjEu83P961gYRUDJ1wVGz8wtZYIb7Wn136b41bk0/phKNs+Zu+CUd6QbPAwmzeb158ZlHPECFVRy8cGz6nsdNuE5OP0kUv/gscCUd0wlE07T5fg4PFgQ3HCni5MCEWwrm8zScKNd3G7EEnHDX7xnGkes9occTe3pgz8I//HgDVAUfu0Et8Cg5eQxi6xT0wuQ9amNDSbtN0GBEOnXDUn1nytxEi6aQ42JEbIRo3hW/XCQfRDt+Bw2JzvDk+QgoshL89MdJqc7gRjqawrRzBs774ibWuWByvOzabg3hj4Fp/hGPaxnSJsBA+a2uGGx8rLXGHxPHCSXc47Vax0F8853EO1p2c73ePFeJO0mEhPDKtzl0T0raUM+I4XrVjiDhlb67J7vdrQp9+NyGdKuGwIXzWlgx3vcoS4/q3s4wl4mx7RkDlxi/97lWWuKe8cLw6JtzFIJgOOLI++6G1pkA06CyI9bsgmKRYiDdbF8PnOuDAC8bf7LRZRBuZwucKjps+OKNn4c1lOPDKLf1EG19beFNwsGTfGzjw5ug9oplPLdkrOEj2IfDQGzgyP/2+EMr6UrKPgkOkCboOB54/5jqntVP+NEEFh0sJxuQPc6QbL5n/jN8lGN/+8Vl54bjrs3NKmmCgfTg1Wl44Pp0Zo0RNBtri3dnywkE2q5JDGmikz4gcDrn81o/OksjodSE1ZPiykHrt4XwZ4dh4vMA1OTXPF3c+TcSY4ZNwOJzOT6bHyEUG2ginUxVv8Yi1d9oHL06UhYzhS5M6uuzuKPs00dWyTxP9q+wT/4hrDuUhGzF1YOP9M0jl3CuWJODdvUpWOM1oTAzUTwvGRafXvzHOpCmDb02IiM2oN6zUZC5L7aRikKpDKhfDA84HvnKQUzTQVKlJhpDTkZXPDQ8xDxYvjAg9G13ldKoiteYwu915LKyceR8juRcfImhoj4dXOBym5EKVmqxu6Nx0vACBvCexeH1c+JYThTUNXZoyKVT25bUdjCWUWkDobIh+elQYpRmOh5VX1HZoyswAxwdTo4orexzga2yxBCXU8M89fVP6l3Niqb1xS0/CrDSmC4U66M6PBCfUNLZatR5aQXnbR9OiFRzGLtkz9+x9VQymBRV1ncSzk3MbUQwExFefCK/YF1C6P6CUD3yNSqvjFA1o1vs5BHQ+PSRYLNkbZQoO/LOZMUJAZn5Dovfx9GiRz2GsKTjwdydFVtV3aqY3Bp63J0RwwQoOj2aCPdQ3ICK1TjOxhSXXPtAngEtVcHgnTZBFuIiUOhNiwfRTXKSCwxPWTaz6XEyVw9tRSS7gTFSliOgrOEyUYIxY8nBImc3uBUT4oweDSp8fLmIqCg5TZp8/3C9gxPKk3eeKqd1m6FDCj/Ouu+tc8fBlSUyAxAUoOOSQJjzw1QUK62w9WUgFN8IVblnESc1ropjTwIUJ9391wQBpgjdM6Vbu/uw8AZIxK5PnbMskF4ShheVcgl1ZRS2VdZ2dlr/l4/CBrySrEhyjAc1oPHtrJh3pjrzAAN2K6U2Jmm7/5JyhuhgFh7HG/ZNXmsDQpeAw0N6bHCUvHGrhzVibuTlDXjjmbs9UcBho7GshLxyE6RQcxoYgqekpIxls3UJcRMFhuCKSab901cCEFlLBYawt25cjFxwrD+SqNEHPGYp1WchYdTBP5ZB62rafLjK/4o0Aq+YVU9nnF+KqHxsQZE4yHh8YRFazhik4vCiqZkc3UxXtIFt90a5sIZ5WcHjZ8spa2bLJ608ZLqDvvLj8slbNbKZETUha5m3PvO/LC57HguV7BrDiqnZNmbfg0JNR3GWxkw+Gbva2jwwvPcgq7pezY4+ElHdZHfovXsFhiDH/Z2s3nY2hhNxj/qHdK53l2fH62PCFu7K52TChv1oVsCo4DLS/ZkUkZjdqPbSGFktsZv2+CyWsfjEtQH+mU49PM/aqpQsPLCRx/AjyNa2HRi8uG9eMNpXs88SgoNqmrt6nBCN5RW9NYDspt5ExhoUxnA985SCnaND7dGXU948OCPRcso/KBHtqcHBhRZtmessvb3tyUJDKBPOE/dsLgni+mNPisxrEq5OCw3D7z9VOahoz8dRMZmQpM2Pl8rycQ6oSjJkwMlEwlRaSp55KMPa0dZ+hyVqG0+nN0nUU9vhgSpTKPjepNIEiT7xwincZz1htY9feCyUUEVTSBDl0K+zTSfhLxKncbkx0wlNq5+3IemV0mCl0KwoOBGcuSJWoA0YtL7ck6mWXtKCFpKSkC/In3lw005rSrTzYJ+DN8RGUfhu7KoVxhZ0MWIUJSapJy2+iKCBjDOMBH/gaklhzKLiMBjSjMV0orEB38+pWFByUjpRXmjBjc4aCw0Cj3oG8cDBTVnAYaCx6oTiVkYx7vjjf1GpVcBhrpPnLCAdL9irZx3Br67SJEn2yOMWGPJRSqtIEqb4lFxykiqk0Qc8Zy1qykLF0b44mv0m2jdeoFcnmJ2Pc6hTN86ayzyl/TnKvmclALUEimYLDaxs0kdppTjJ48Nm9TobSrZD4effn500V0kCnqQlTcHhdzmSSbSLZI6Gk2jTSJgWHKPpzNLT8SZGC5XFH7sDufyLbSMFhxp1vtp4qonCxJ7EgKEc9CDn2B1IbALa220jgQJdmNBY8y2CRoK0miyk4hFEbn2oIbq/hgapq8Z7sPKnV9AoOMR1BkMjSF9XsH+kX6BoQj/QPpMb+uiP5qFF0TCwUHHIade/ZTnzJnpyJ61IHL0pgdz7yQFEskvlHTiEf+MpBTtGA4DevylLsJ/endulABgAAAGCQv/U9vmJIDuRADuRADpADOZADOZADOZADOZDjBTmQAzmQAzmQAzmQAzmQA+RADuRADuRADuRADuRADpADOZADOZADOZADOZADOSAe34f5izVe/wAAAABJRU5ErkJggg==';break;case'apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__590bdf89.png':$g='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALGklEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUc7syxR/Nq2bdu2bdu27fvZtm3b9nds2z5J+v2e+vGeOyeZSbqT2qv+GHRPZq3ZaVTXro56NBYklUz5LafzQxnfX5783rHxz+6EccApF7lFgdgih6C5oqBo1KcpH50c9+Q2f2oUozBVopwcAn99ddHYL+Of25mv3iqjChWpLuSITlStGp346n586aCN6jxEyBFdCPiLxnzB13XFaEJ4oJAjGuBvrM3ucC8f1UXjgTzWenJIm+EyMzQ/Ot6nAgGLySGgC/CAGbp/+cpicsgIlE/oqfETVpJDZq3/d26S8PxuxRN/+Ltltbm15W+f2/NpXZiK/3f+wg8JOaKkQ0l681BdJtBYl/LRKS2Qo2bLbF2Yik46FwvIIT5QHOF/Sg7QkLM1/pkdQyEH/rHmykIhB1AFNQUr8lYMjx/+0/KfXpzx4s2jb750yKVn9T/rhN4nHNr10L067LVDmx2wvTvuzSkXuUUBir008yWqUJHqhbWFykvg8OazOSEHKF/YNxRyYEWjP4tRcpTWl05NnfrF4i+uH3k9336b37ZxxXjUDSNv+HLxl9PSppXVlylXwYKIc3IwI83p+mgo5KBvii1yrMpf9f7890/pc8q2v23Lt/TU+IlT+576wfwPVuevdmWtlQ/mjBx69FrFYmzQ5MAaC5KjnxybijZ9svCT43odxzeLiB3f6/hPF326uXizChYstTskR3N5vj6uz1gX99R2/02OzbMckoMfjWZyMBS4ZfQtfB5D7PYxtwfXkBCK4ZAcef1eai7P06elszr9V+HqjdMdkiOny8PRSY41BWs0LUyz28bctq5wnWoNCNVxSo6+L1I44GvWg4/s9nf/BznWTXRIjozvr4g2cgRU4JcVvzC54DMYa7ze76t+V47B6MEhOYpGf86VwpGf6Cu+2vKktw7/LweoE3Ikv3dcVJGDaQhNt/4Ghtvd4+4ubyhXDoCHwyE5Smd2/EcLsWGavliXvEwXrlg6xCE58HZEDzkafA3nDTzPClpou3DQhY2+RhfJUbFk0D986i/t1VSSqa+XTP7l79fL5/eKRXK8MusVi2ih7c05b7rYrVStGa+vp399UcDXpBf6M3+9kYsl09rEXLeC38k6WmibnTHbrQFpzaaZXNFWMPRdfctXWZT4+oG4PmNuQPryzJftJccbc95wayqLj+u/7tKW6Lu1cfPy+70cc1NZ/J72kuPM/mc6cIIFSY6EF3ZvKkrTBerTVsecE8xSWuiZrQP3eZDkwNK+ODfQ3KjL2OQ+F3Js//v2ThbegiYHlj/wdV0m2hbehBws2YdCDqxy5UhdLKqW7IUcBPvgeAiFHPHP76qFstEU7CPk0GGCwZMDS/30jEBTvdVhgkKO4AOMiR9uOcA4q81tMRdgvEu7Xewlxx4d9hBpgoe4YtgV9pLj2hHXiqjJQ3y88GN7yUE0q8ghPQThMzqGwy7bqe1OBDJGXEgNM6JZSP3D8h9sJMevK38NTk5N/+Jmb6LbjKgkhz/gv3r41XYxA20EoWuSvCUcqG6svnf8vbYw48GJD9Y01biR9umrYNM+fRVbaZ/4I3637DutTDHTtvt9O6Ry7oolcXi3rJLVRjEK4wON0YRxczPnnjvgXDOZcf7A8+dnzfcs1WQyS+2EYhCqQygXzQPGAadc5BYFlKSapAkZET/ixN4nmkOLk/ucPDphNC8mSWqNQLO/edCWQYz7aMkj2ImgoR2ydYgv4FMGQlJN5lTn/LbyNwTy4aTFOQPOabOqTW51rhJYobJPr0inLSHVAkJnL/TTp/U9jdQMg7cOzqjMUCZAyHH50MuTylqd1bu4rnhSyiT+3K/OevWmUTeRe2PHNjs6pwKFqUKiDqrzkMkpk0vqSlQrEV8af+WwK4Uc3i7ZM/YMPSsGw4LMykz82ctzl6MYmJA8YWjc0J4bevba2IsDTudkzOEWBSgW+hgCdh7b81i9ZO8VhBzYdSOu0wIy84FE76phV/HaQo4wBftcPPji7KpsZTxoeC4YdAEvLOQIayTYQV0OmpU+SxmM6WnTD+h8AK8q5IhMmCCLcDPTZxpIC4af+iWFHOFAC77qMYljWLBVEQUvMDJhpPboCzkMCjBGLNl/c/8mf5MKO/jRPpv6nNT7JP0yQg4To88P7nrww5Me7rquK7nbPF3g4OHMdbus6/LQxIcYAOkXEHLYIU3Yv/P+JNZpu7otGdxwV7iyiLMybyXJnO4ad9d+nfbzQpoQAYhuZc8Oe+IgeXzK42/PfZtYEJoWlnNxdq0vXJ9VlVXb9I/ISg44JVgV5xgFKEbht+a8RUWqIy/wQLdiPETUtGu7XT3TxQg5vAffz15pAk2XkMNDXDLkEnvJIQtv3uL12a/bS4535r4j5PAQ7GthLzlw0wk5vHVBktPTRmawdQt+ESGH54pIhv3WZQPTWkghh7f4fNHndpHj6yVfS5hg+IBi3RZmfLP0G4khDTc6rOlgvuINB6uKCCT6fHzS+MO7HW4mM47ofgRRzQoIOSIoqmZHN6OSdhCt/tGCj7R4WsgRYWwp3sKWTRHvZXiBW0ffurVkqzINImpC0vLuvHf37bRv+GnB8j0NWHJZ5OTLQg4nEcV1zXXEg6Gb3bntzl5zglXcG0fdOGDzgPrmekcv7x2EHIz/2drNYWFYQuwxf2iks+72HWcPOPvDBR/yseGE82xVkFXI4SH+HhWxJGeJaiWKaosWZC3osaEHq18MC9CfOdTjU4y9aqlCh4UkjocgX1OtBLV4bUx5DQn2ObL7kfk1+aGHBCN5RW+NY3tZ7jLaGBbGMA445SK3KBB6uDLq+8O6HRa+YB+JBDum5zEJpQnKeMSVxB3V4yiJBAsH/muCoPsXM7Eoe5GeOgk5PMf/rnaS05iBpzIMRCkzYuX1JIY0wgHGDBgZKBilhaTXkwDjcKPlCE3WMiIYUMNPk9jjsqGXGRp9LtIEkjwx4dRzmfAgryav+/ruJBEUaYIduhX26cT9pf1UroOBzoy0Ge/Ne++MfmeIbsUIciA4C0KqRB4wcnm5Eqi3sWgjWkhSSgYhf2LmooyF6FYO7HLgeQPPI/Xbk1OfpF1hJwNWYaamTl2Vv4qkgLQxtAcccDoldUq/Tf0oQDEKU4XEClQX3Yq55CB1pL3ShNdmvybk8BDkO7CXHIyUhRwegkUvFKc2MmPvjnuX1pcKObwFYf42koMlewn28RxVjVU6RZ8tRrKhMIWUSpgg2bfsIgehYhImGD6wrGULMz5b9JmyDPZv4/Xo5EfNZ8ZTU59S4YdEn5P+nOBek5mBWoJAMiFHxDZoIrTTTGbQ8fF6KrIQ3QqBn3t12MsolwY6TaUh5Ii4nMmQbSLZIyGlPEUZAiGHTvozcMvAo3scHSlaIHdg9z8dbSTkMHHnm3ar25G4OJy0wClHPgg79geSDQArGioI4ECX5jUt6MvgIk5bJbCFHBrkxicbgus5PFBVfbLwE6T9SmAvOfRwBEEiS19ksz+k6yHBEeLQroeSY//H5T+iRnE+sBByWAby3rOd+KeLPn1u+nP3jL+H3fmIA0WxSOQfMYUccMpFblEA5zdTZSv2k/tLu3QgAwAAADDI3/oeXzEkB3IgB3IgB8iBHMiBHMiBHMiBHMjxghzIgRzIgRzIgRzIgRzIAXIgB3IgB3IgB3IgB3IgB8iBHMiBHMiBHMiBHMiBHBDxnn9eIYwbtwAAAABJRU5ErkJggg==';break;case'apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__590bdf89.png':$g='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAMAAAAKE/YAAAAC1lBMVEX//////v7yyqv//v3qrX399/PZaRL44tLjkFH9+fX007rpp3XWXgLXYQbbciH34M7jkVLggzvkk1byyqz007nYZg7+/Pr228b++vfqrHzhiEP33svii0j67OH67eL++fb//fzwxaTopXH+/PvstInwwp/++/njj0/XYAXabhv559rZaBHhiUXhiETbcyLii0npqXjZahXghT/YZQz228f66+D23crXYQfijEr45NX45NTz0LX89e/ffzXZaBLstYvhh0Lww6DpqHb9+PTabBfefTPjj07opHD12MLxyqv22sTbbx378OfbcSDcdSX89O7aaxb67ePz0bbnnmfpqnnjkFDmm2Lll1vbbxzklFbffzb11r/abBjkllnghkHkk1X23cntt43338ztupL44tHijEnqrH3oom355tjlmV7z0LbopG/rsYXvwZ3fgDfccyPfgjvuvpjoo2700rjopXLzz7TvwZ777uTstYr45dbbcB788er11r7ZaxX66t7YZAvww6HbcR/tuZH00rnrroDcdSbnomz01b3eezDwxaPstoz34c/zz7PdeCr55tfxyKn56dzzzrHnn2jfgjrnoGrpp3Txyan77+fklVn99fD88uruvZfmnmb77+bqq3vefjT++/jZaRPtt47vwJzefTLdei301Lzvv5rxxqXyzbDnoWvfgTjnoGnhiUb77uXttozeey/qrn/99/LZahTtuJDuu5TpqXflmV/ijUvlmF389O3mnWXvvpnddynwxKLssobxx6f88+z669/YZw/23MjhikfghD7deSzabhrfgTnyzK/YZQ388uv118DzzrLxyKjrsILuvJX449Pdei7cdCTghD3z0bfbciLggzzklFfss4jWXgHXYwnXYgj78enefDH56NrstIr56Nvqqnncdif78OjYYwropnPqq3rXYgfcdijqrX7XYAT01LvrsYTWXQDabRnWXwPcHI28AAAFZElEQVR42uzBgQAAAACAoP2pF6kCAAAAAACYHXuAjqRLwzj+1MS27Yw939i2Z23btm2PbduKbdvdwZMce42u2uqkC3NuLX5Hcf6Nqnve9z/e/3V0hoYEBgQEhoR2duA/Qm93Dx30dPfC6jzcbVSwuXvA0iZ5UoXnJFiXWwydcHeDRfl40ylvH1iSmzfHESzBitw5rkFLXoOcgAWvRg9P/lXfyMjIu/lPd//8aR//ytPDsm+OUQBJdv7DMIBRq75BegMcorEoWy3a1vbK/nvOs6cPcwura0YjhoZejtZUF+YeevosZ6L/103HaHxYLZoDMN+DJTGxEXQiYvp7du+AUz3yaOl3atF2mGxBv72ZE2h+/Os4qOqgPBo3AlWi2QUTec2aQRd9dG8V/l2nLPq3AIL8+Re+suhOmCYnnprci4NSqCz60GEA7+Rf1MmiG2GSd8RTs4VBkAuRRT8MSQOkLJL8uSy6AaaQmoaow9AKyATKoo9wFoClk0lOkkXXwwwP7lGnrKVwECCL/gb5AQBRJFNk0TaY4Ngp6rYs02n0EzIiAUATedb86F/QgPc6fXsUkXxDJOA2jT8w/e2xm4YMO7sQv0mSqQC++OUB0y/EPTTkO85uecP8iyIAX9pj+i3PTkNOKw4XRfRXvwIgzvTDhcYMKY5xRTTbMwGYfozTmDH8S49KNC8oou2wVnS3WjR/I48esFh0r00tuqUDzocA8dFwV4tmfrT6uCU0Wn2wvct/Wmj6YNtKQ+YKWSE00JC3CFnW3KEhMULWYkFDNCDAS8MCMtgHZhmhAelQcnOnE4MSTOM2h7rFSqKW6h7e1Omta6DGY9BGBdugB8wlVTRTB/+ncKZ3wE4H9oE2mO+77dRs3Rsxnq7OxoZ6m62+obGzC6+GVL6dmvRskiBe2pNYf7rIf3pKMizis5+rpgu+d/UKLOXFk4eP6Vzzjw99fz7E+9gUKJSUXb10uSabMtk1uZeu/ug5FH5SCxG4fQfUJCd4zRs+f+7R2XPnz8zzSkiGmpLAMYhArsyETsfyKCqaH9kFXRKOUlw0d/pCh7qbFBlNzgnXnFxLCo4m14W5wWVuP20nLRBN2jMi4ZLIk98mxUY7WNtYWSVhXJLX7dfvJEVHKxzPentcMlSlvW3F+uV0JDRaoXjlgQ9WVJaf+WGiD+CTGDRcXlnxiQMr51JJfLSqllaqExrdQkOKIcJFGlILES7QkA0Q4RkNCYMIbstoQKEEIYLGqNuQFwQ5Qt2uQ5gY6vQtCHRL34apEkIVTaZmryuDYB79Q9Qke/UaiLc4vpkua565CNYwJXUqXbK8vwtiOc60SRmxr3ECrdNWRct/XQD/JjhKCu+vpjPN1/x8o+Fo5DWIwLHNUPhD6cc3zAz0pwP/GTNTH5WWQKF0TNQQsGUr1EQ+f+EVFR4WFh7l9eJ5JNRcKRA3uWx7H3T5/BdEjlvLN0OHjVPFzohDd5KgkY/fkPDBdkY4NKnbZolpvLZMgouk87+0zArh8aOtcMHhgz3W2nuc7lecHgpJn/xUPknR0XOp0HL5qhdUvWvF9FYqTLXO3mP2qdz7/ekZSxbMj06av+DTn0nvv5/bPts6e4+HNORXEOE2DXkEEUqKacDLBxDiOg0YgRg3blK3tWsgyEnqtgrC+FGnAYgjvZm6vB8iZU6jDvGRECotlZr5pUG0sAhq8rIIFjClnRoc/Rkswe1rX6eLAlMkWMWxE8fpgpu3MmElv796jRNoP3EDllO1ejKd2jJrMazJrXRk/ZuoxNGsD22UYGmJm/bu35eXX9DaUpCft2//QNgu/Kk9OBYAAAAAGORvPYi9FRsAAAAAAAAABIY9HLkA16UTAAAAAElFTkSuQmCC';break;case'apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__590bdf89.png':$g='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAKzUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOQBJz2xh+No2Cte2bdu2bdv4bNu2sbZtewfr3XFyn6v+/X/ZmckknTmnuqpmMt3ZVOXZxunznva8RcY6AueWDK39WN+fX935oye2fvFeFD7wlYv8RIXsgkMsNjXmO/LLrp89veWzd7lhoRqVaeJxOMQSoVnf8d+2funevPVFFZrQkOYChzdtpuJo+zcfxptOutCcmwgc3jIj4Tv2G95uWgpdCDcUOLxgicj84KoPpgULVbght9UeDukzFBlp5mP1h0zD0BgOMYYAG8hQ48vvNIZDZqA2YaEKf0JLOGTVertrk7Yv389/+i//LQPL3nnn73548+dVZRre7vqFP+QROGRA6fjuY1UdI7LQ9bNn3Akcc01XVWUaWhhcdIBDfKA4wm8IBxYeam79wj1TgQP/WGx63BQ4sLlAZKRhqvXiSOm27kt/bDr6jap9ny3b+ZHire8pWP+m3FWvvLbshZcpq191ja9c5CcqUO3yn5poQkOazwcjpp2Gw5vXZgUObDJ/eypwUHxHf5WlcISmot0FvsJ1nYe/Wsm7X/K8S2kp3Orw1yq5bU+hPzQdNdNqbIhYh4MV6dD6T6YCB2NTdsEx2jSVu6xt2/sLlzyfd2lv4U9s/0Bh7vK20abptOy18sKswaFmrzNsxiYNByUy1ul9OHwdM/mr2re8K5935kjZ8u6CgtUd/s7klwBstVuEIzY5qj6H+mpaPne3W8PReMUiHPxRL8PBVODoN6t4PS4px75dnVxHQiiGRThGdnwtNjmivgavrLlV5dn6ixbhGFr3cW/CMdY87RgWFhAZb10cIoTqWIVj+1epbMRjavIxuPL9t4Cj5rRFOPr+/BrPwWGYZdt7WFy4EAtVeLyKXb2mZWP2YBEO39Ffc2X88C/Ulfj8ZMf3Hn9rB6gFODp/9CRPwcEyhP9L9Q5cXk58vyY8EzMtGB4Oi3AEL6/+Xw9Rd0FdXOgsUZWnivdZhANvh3fgiEcSuz9eogUWquz5ZGk8mkgjHFNFe/57se1rD4oG+tX1wNl//Pf6ZO6WbITjyp+bNcJClWt/b0njsDJTdVJd7/39y4x4VG309//zrVwMXFiWdcMKfiftsFClrzSQrgnpXMNldZ0ytv+H6qf4tK/924/E9Zl1E9LLf1Ldhgc7D+tLWXxct/qVvkT9Ot+SM7rj61m3lMXvqS8cOz9cnKQTzAIcbV+5f9TXoyqEeiqzzgmmKRZqZZuk+9wCHJSe37zQiEVUHZ3c5wLH0hdcTn7jzQIclNHd31Z1vLbxJnCwZZ8KHJTp8sOqmqe27AUOgn1wPKQCR+uX76uEst4J9hE4VJhgKnBQun/5HCMa8lSYoMBhPcCY+GGu3EkZWPaurAswXvHSK/rCseoVV0WaYKMd+Hy5vnAc+nKFiJpsNGK99IWDsFORQ9pohM+oGA69yvIXXyGQ0XEhNWR4WUhduqVbRzjKd/QkJ6dO4/jCaKL6DG/CYSSMg1+q0IsMtBGmIclbMmKR+fjJH9TqQsbpn9RFF+LpSPv0u2TTPv0uy9I+GWbJ5q4lz3e7YwOpXHrFkji8lUr2zgvVqIwPNEsTxvWXB3d9zKUhg7s/UTJQGbQt1WQnW+2EYhCqQygX3QOFD3zlIj9RwZRUk3QhrZdGkba6B4tt7ytsuzJmGpKk1h2WiBtNZ4eZ99GTOziIoKFtPjdiJFzJhaSanB0Ple/sQSCfSSx2fbS4YnfvrC9simmhsp8aXqAvIdUCQmd79NNFpGZoPjc8PbJgirkBjv2fK5voX7SDb2Ey0pXn45/7yl+aj3y9kgnKshctYvShMk1I1EFzbtKV71uYjJqLtGDP3IEvlAsc9m7Z855Sz4rBtGB6JIQ/e7h+EsVAZ854y/mRuqOD9ccG+cDXvrIAP1GBaqnPIaBz8zvz1Za9XSZwUA59pUIJyNxvSPQOfLFcxXPYawIHZe+nS2fGQqbrjY5nzydLeGCBI6ORYOvekNNbEjBdbD1F/rWvy+FRBQ5nwgTZhHMhImDB9NNCmKCdJnAoX3X71THHvU88QNvlUeXRFzhcFGCMWLLx1FAi5gAi/NGGE4Nb36t8KgKHK6PP178x58xP62oO9ZO7zTTs3dZhrVtzsJ9NeSZA6gEEDj2kCWtfd53EOpV7esngRp+flk2ckcYpkjmd+F7Nmtdet/wkAof9lmJyWRwk535Zf/2frcSC0LWwnYuza7xtZmY0FA39Lx6HD3wlWBXnGBWoRuVr/2ilIc2RF9itW3GjiahpxUuv2qaLETjsN96fvtIEui6Bw0bb95kyfeGQjTd77erfWvSF4/qSVoHDRuNcC33hwE0ncNjrgiSnp45kcHSLaUiwj/2KSKb92mUDU1pIgcNeK1jboRccRRs6JUwwc4ZiXRcyijd2SQxppq1qX5/7FW84WE1HTKLPO66Pb3hLnjvJ2PjWPKKaTUzgcFBUzYlurkraQbR63sp2JZ4WOBw2f9csRzY5PsrwAEe/VRXonjXdZiJqQtKSs7R1zWuuZx4Ltu/pwHgAU8wpOKyEi8bCceLB0M0uf4ntqQfZxUUl1Xh6OBZOWHp4+0zgYP7P0W4WK0MJ74N/aKSz6R07EMfmrWjn5jBhPVsVsJq2msRzwMdQ7eRiG85PRMiWUXd0gN0vpgX/1Z9ZXI5yVi1NGLCQxHET5GvmIo1W3Idi2m0S7LPxbXlz/nDqIcFIXtFb+xBF1k3SDbAxRuEDX7nIT1RIPVwZ9f2GN+dmLthHIsE2vyM/2Dtnut4C3XOb3p4nkWCZsFstENT44k4brJ5QSyeBw3a77W4nOY2ZeJouM6KUmbHyeA7HkEqAMRNGJgqu0kIy6kmAsSvgUBGa7GU4GVBjmCT2IL2MRJ+7VJpAkicWnGotkxnjz9UeGSCJoB7SBNGtcE4n7i/lp0q7MdHpKfbnLG3b8aEi0a24Ao4kBGdIXfBwk8srLYF6vvYZtJCklExC/sTKxXStiW5l3etzdn+8hNRv53/VQL/CSQbswnQX+EabpkgKSB9Df8AHvnKx4eQQFahGZZqQWIHmoltxLxykjtRXmnD1ry0Ch41GvgN94WCmLHDYaGx6oTjVkYzVr7oWmooKHPYaYf46wsGWvQT72G6RuZhK0adLIdlQhkJKJUyQ7Ft6wUGomIQJZs7Y1tKFjII1Hab+ptkxXmd/Xu9+Ms7/usHMvEn0OenPcX26mQzUEgSSCRyOHdBEaKc7yWDg4/FMZ010KwR+usr5gUsDnabpuAkcSs7kkmMiOSNhcsA10iaBQyX9aTozvOnt+U5hgdyB0/9UtJHA4caTbyr39JG4OJNY4JQjH4Qe5wPJAYDh2RgBHOjS7MaCsQwWcdqaYrrAoYzc+GRDSHsOD1RVRL0j7TfF9IVDTUcQJLL1RTb79W/KTQ4IGpJjv3RrN2oUmVhoBod1I+89x4kXrO64+LvGkz+o4XQ+4kBRLBL5R0whH/jKRX6iAs5vlspanCf3r3bpQAYAAABgkL/1Pb5iSA7kQA7kQA6QAzmQAzmQAzmQAzmQ4wU5kAM5kAM5kAM5kAM5kAPkQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzkgDe9KFu6DmR8AAAAASUVORK5CYII=';break;case'apple-touch-icon-red-507228751d2170d047e72142d2c02390__590bdf89.png':$g='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALAUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUe58yxR/Gfbtm3btm3btm3btrW2jWRtZG3MvM9TP/72O5vMTLqTuqf+GHRn55y+26iuWx3zGG+v7v7ukeZnT6i/d9fANWtXnL0AxgW3POQVBeKLHILJvvbOT24O3rBh+elzzdEoRmGqCDliHNOjg52f315xzoK0+qyMKlSkemySQzCQ/WnVxcvQ0mEb1fmR2CKHwJru/Ow2WtcVowvhB2OBHILp8eGmp46mUV00fpCfFXIY32coZrjMj6ePsS1LyGEwGAI8YIYaX+4Qchg8A6UJPTX+hJHkkFXr/12bVJ67SNfX9/3dGh87eOa2b3n5TFWYiv93/cIfEnLEyIBSffnKqow1PhK8YaMZyDFU+qsqTEUHg4sJ5BAfKI7wOZIDjDWXVZw1fyTkwD822d8h5AD2eFd7f2Fm+7cf1r/4QMUt5xecdWDucTtnHbpF+j7rpey8ctKWS/yxwXxY0lZLcstDXlGAYhW3XkAVKlJ9vLvD9hI4vGk2J+QAvUmvR0IOrPPTW+KUHBO9oe6E72ueuC3/9H1p+9/XncsV46cKztiv5snbuxN/mOzrsV0FGyLOycGKtPn5kyMhB2NTfJGjvyg78MC1GQds9Pt6c9OWnhp/IvOAjQMPXjdQnOPKXisN5owcavY6wGZs2OTAxtsDsU+Owcri4KM3pe+1Dm0WFUvfe93gozcPVZXY4YKtdofkmOxtU9ej9fnlZ8zz3+Qo+cUhOfijsUwOpgKFZx9E82hihecdGl5HQiiGQ3K0vnHBZG+rug398sx/FR4s+tEhOZqfOzE2yTFQkqtooZsVnnvIQFm+PRsQquOUHK+fT2FralJNPpqePPI/yJH/tUNy1N+7W8yRw7LqX3qIxQXNoK3xeQ2vPmo7BrMHh+To/PRWnnR8fJN6MjXcW33Fqv/lAHVCjsA168QUOViG0HWrNtDcii48crK/13YAPBwOyRH6+el/9BCFP6iHI4F0Vbgv7T2H5MDbETvkmB4fyz5iGyNooSzn6O2nJ8ZdJEdf6jv/8KlfsMREd4N63v3tQ39/3pvwSjySo/L2iwyihbKquy93cVgZyP1SPa+7cwdrakJt9Dc8vD8Pu394LO6GFfxOxtFCWSj1V7cmpEPFP6vnWPv7V6tXU/2dVZcuj+sz7iaklbddaC45qu66zK2lLD6u/3pLX6LeDpf/0fbGhXG3lMXvaS45sg7Z3IETLExyVJ636ERnrSowWpsTd04wQ2mhVrYO3OdhkgOrvW1ra3JclTHLfS7kmNfJxlvY5MDa3r5UlYm1jTchB1v2kZAD68/6WBWLqS17IQfBPjgeIiFHxbkLK6FsLAX7CDlUmGD45MBqbt7Mmhg1P0xQyBFWgDHxwzMHGDc+dkjcBRgnbLKQueRI3HwxkSZ4iLwTdzOXHPmn7i2iJg8RfORGc8lBNKvIIT0E4TMqhsMsS9hoAQIZoy6khhmxLKSue/4+E8lR//LD4cmpGV/cHE0sK5ZTMFjT03kn72kWM9BG0CqSvMUPTA0PFl98tCnMKLns+KmRITfSPt0RbtqnO+Is7ZNl1T17D7IRrR0b68+DVM5dsSQO75lVssooRmF8oHGaMK4n/ffsw7fWkxnZR27bk5ngWarJAFvthGIQqkMoF90DxgW3POQVBWxJNUkX0v7dRxn7rq8PLTL237Djx0/5MElSqwVQcLR9+Q7zPnryKA4iaGjbvnrPmpqyNYSkmhxrb2545REE8r6GeB22VcNrj411tNgCI1T2o0119CWkWkDo7Il++sBNSM3Q9tW7o831tkAHcuSesOtwXfWsRVA9XV2/f8M/d+UdFxeceQC5N/7YcH7nVKAwVUjUQXV+pOv3byd6u+1ZYjhYkXfS7kIOb7fsmXtGnhWDacFoSwP+7P6CDBQDXb9+1f71+y0fvtzy0StccNuT9huvKECxyOcQsDN9z7XVlr1XEHJg+aftowRk+gOJXt5Je/DZQg6fgn1yjtlxrK3J1h50PNlHbccHCzl8jQRL2WGFUMovtsboTvoxebvl+FQhR3TCBNmEC6X8rCEtmH6qjxRy+IEZfNWdP37Ghq0dVfABHd9/rDz6Qg6NAowRS7Z+9qY1OWH7Dv5oyyevZey3gfoYIYeO0ecpO65YesWJze89T+42bzc4LIu1btO7z5VcfgITIPUBQg4zpAnJ2y5LYp2G1x8ngxvuClc2cfoLs0jmVHTBEcnbLuOBNCEaEN1K4haL4yApu+bU6nuvJBaEroXtXJxdg+UFo62NUyP/iKzkgluCVXGOUYBiFK665woqUh15gde6FR0hoqaETRf2VBcj5PAWtJ+50gS6LiGHh8g5didzySEbb96i6s5LzSVH9X1XCTk8BOdamEsO3HRCDm9dkOT0NJEZHN2CX0TI4YMicl7jsoEpLaSQw1vUPH6rWeSoffpOCRP0DyjWzWHGXRJD6jca33xKf8UbDlY7KpDo885fvkzdZVU9mZG662pENdtAyBFFUTUnummVtINo9eDDNyjxtJAjyhiqLuXIpqiPMnxA4TkHDwXKbN0goiYkLdX3XZ20zdL+04LtezqwkfroyZeFHE4iiqdHR4gHQzebsPGCXnOCXdyCM/dv/fyt6bFR5x8v5PAEzP852s1hYVhC7DH/0O5KZxk7sg7bMvDQ9TQ2nHCerQqyCjk8xN+jIvpyU2etOQt19mYlNn/wErtfTAvQnznU41OMs2qpwoCFJI4fQb5mzxLU4rMx22tIsE/abquPd7ZFHhKM5BW9NY7tvvx0+hg2xjAuuOUhrygQebgy6vvUnVeRYB//IsHS9lxruKbS1h5DwfK03deQSDA/8F8LBDW+6Ine7GS1dBJyeI7/3e0kpzETT1szEKXMjJXPi3IMqQQYM2FkoqCVFpJRTwKM/cbMEZrsZUQzoMaySOyRe/wuEn2uqTSBJE8sONVaxh+Md7Q2v/8iSQRFmmCGboVzOnF/KT+Vm1AetuSfAvdfk3nwZqJb0YIcYQjOkLqQB4xcXq4E6g1WFKGFJKVkGPInVi62thDdSvL2y2cfsQ2p38quPZ1+hZMM2IXpTvi+vyibpID0MfQHXHDb/cd3rZ++QQGKUZgqJFaguuhW9CUHqSPNlSZU3nGJkMNDkO/AXHIwUxZyeAg2vVCcmsiMpK2WnOgNCTm8BWH+JpKDLXsJ9vEcU0MDKkWfKUayIZ9CSiVMkOxbZpGDUDEJE/QPbGsZc2LoY7fYhsH8Y7xKrzpZf2aUXXeG7T8k+pz05wT36swM1BIEkgk5onZAE6GdejKDgY/Ps6ML0a0Q+Jm05RJauTTQadoKQo6oy5k0OSaSMxJGGoK2JhByqKQ/bV+8nbbHmtGiBXIHTv9T0UZCDh1Pvml84wkSF/tJC5xy5IMw43wgOQBwcqCPAA50aV7TgrEMLuK0tQWmkEOB3PhkQ3A9hweqquCjNyHttwXmkkNNRxAksvVFNvuUnVYKc39k55XJsV/3wv2oURxMLIQcZoK89xwnHnz05vKbzim+6ChO5yMOFMUikX/EFHLBLQ95RQGc3yyVjThP7i/t0oEMAAAAwCB/63t8xZAcyIEcyIEcIAdyIAdyIAdyIAdyIMcLciAHciAHciAHciAHciAHyIEcyIEcyIEcyIEcyIEcIAdyIAdyIAdyIAdyIAdyQHtHp5xFOjNVAAAAAElFTkSuQmCC';break;case'logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg':$g='(]^+JbP.FqjXYdorFxH%oTmn1#,Na[(-^<}T{`+Ahl-RItQoM;{4bK}l["$V3F6U&V6Ey@S8#w=t>3kaN[hLow+fWEUH+K<LoXqyEy6JupFy-JyK4S8q(7tl96;KLl/F|,Cz)p?p(B)[axu/4u77-)nvU
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
n9lrNOU01c?:p/5y16+Zkgo}`M)D6xm>7RiQ_b%p%ocllip!0).myrT"w[53iGRZBt8z<.d6p9("_WYy1v;v.xBx%3c3&hfawJgMtuxeflyK1.-:wQo"f_z&)W';break;case'jush-b3a93b18444da26820ff61746521dede__4fa892fb.css':$g='%X.mPb3V?!K0u25Dm[994[Zg@N#Q)YOC=2R_hE~4)=>cbdia55M!*B>tthOR~qe;I4(JE7nkwCcvDp&`dDJK
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
)yZA,ICx<`.i#E%U;lo*kf6u&LQx+!%1t]iP#G9;zGT,4U2"ha>hB#am`y1YU6$z!l#C%';break;case'jush-31ccd1ce96536a294822e952872683d1__1d05977c.js':$g='%hk^;xqDE.!v]yOYu=(i
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
<Vk@,+r[?)N;wLkXZ]chQan6+w;/2n$)5Sg#p+M`6dj1iL[?IHIqIIV>rZ2j--]DJDV?t/>43Y!BZIe#';break;case'icons-70163a2695280bf75edba563e7b5471b__929918c3.svg':$g='.n1F;boZc.Bo(o})e?Uah,r_6,]#*f&vFTX.Are>cU5dz3=O.b=K:xCw9_>Q|]_*[eKZ"sSUICkDTRCT*BU7|p[d&s"W9Mp[,MrS6yxy5+)_Hrru:n-yzfmIccln]H#h{29y}twyT!RpdN%uauzXwpktSn}v[A>wvv/rOyeKy3VK=t~F=7gchMjiTG0nqFU@:^Q]q7nujs
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
XCcst!y#ykyEA,xeN&';break;case'default-blue-e7acfdb81453b86f081569afa115859e__54f93b23.css':$g='*erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhze/CTC?saq5qsKQ*LB$<b.0;*%{a46%qE6[mLxHcqkHpxcO3?I!QoKJQhn1](JH^)Jg]@_dx@4B
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
LHl}E9KqyhE?rI&CdE7RwA';break;case'default-green-268f042072e76ab07aa2fd0350e0aa0c__54f93b23.css':$g='+erWObOZ31.Ov9U"l9Hk1R@sf8q^_#6_RlSlEaJ@u_9,e4
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
x+tX';break;case'default-orange-e4e5ea626cdcbe83e07c7933dc04f916__54f93b23.css':$g='-erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhze/CTC?saq5qsKQ*LB$<b.0;*%{a46%qE6[mLxHcqkHpxcO3?I!QoKJQhn1](JH^)Jg]@_dx@4B
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
v^+aj*Rd#OGUBYitAR8Q@L+hH3{AIgKN,rS6h20y#a~v&c~(Si-RfCKG+HlP}Q6X!]qnjMR)f&mc_hr1HM|k:">+*c^9_f~#A.0hKpMQiaK^.<@F$]YGXMC0,,7ZWXZ/XW/u1FXXtg],Sr1d(';break;case'default-purple-6b1de1f635d52b55797976fef486a515__54f93b23.css':$g='$erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhze/CTC?saq5qsKQ*LB$<b.0;*%{a46%qE6[mLxHcqkHpxcO3?I!QoKJQhn1](JH^)Jg]@_dx@4B
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
(CL+@8cmx|20mZE"W]5yxV0F332m73QASu/]8y*YwF()He<cWASzA,mQmc?Y6M8=7-H+5Xp(v36?U34?jyo-';break;case'default-red-0f424ea89c2a43c6eb0ec8f0e22362a5__54f93b23.css':$g='%erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhze/CTC?saq5qsKQ*LB$<b.0;*%{a46%qE6[mLxHcqkHpxcO3?I!QoKJQhn1](JH^)Jg]@_dx@4B
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
d--&a3RekatK=14e;`tq<3jw,x';break;case'default-blue-dark-1061ad7d216f143e3626560b92b66061__42198a80.css':$g='*O{SB7nV?&/MUNH!{Cu5Lel#Y-XFC?m<DF
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
s?[:Ep)rpcG^+CWaw-,2&0y-MkHQ#!:HNMp+q=GCO:UL:z"+P]23~)F!.KT@<L.z%)^_SQXy<>/W]]_cu5XiVEfr>f3ng3,IawA';break;case'default-green-dark-6176b1c7b42ee9f244b3a9c3d8065728__42198a80.css':$g='$O{SB7nV?&/MUNH!{Cu5+ZjZR6J`&pVU(G~X{7Ync8o(BwyH>K0yTdugXCvDPa>s1q^LEW+D6wbvC3=Qkoi+I-{G&`Ns?;`/@_brg?#sfS$D!8u$|"b<TY)!il"W-3tFw[22wk/U4LlIHnm?jf7*9MvdK=dC|nTQCGgv:3%_bu0&^@f@HwVfT*RV,l`=x[&[Ri]BKsEPG+&5MJUp);B=8A
?C-&<+<e(N.tAT$&%10?A_;0Qp0~qZBJ5DmlAY69tS%Lb)Ia@^e!fl+W!i:5E1g63&,lD+*h":hqShqMQ?:.dLd+ff0U@21MKCvX-;IVdZ+Fvs*2^aO!u;a7Y)fIo}o{J45Wr!/.jMVC-TO>?gp2i36z2T:nmr)TKM^pavH0h`s|]PdW[7_donY/y@97
BjIyu/[pX:Cwmi}w~GiGHdE/cA_Yd&OXY[7IGsx?e6s<BpvKFV%hS+Nv6I[5^p:/L1+C#$fm2cerKu[sREw9t3UB[.
IM@]BW)3K{t4X6cjjC+wJ]J<A/&pMK6h0JSJ>^0!Y!f&7nmI^Q[44@5<IRI=Jm??AW"Xh}J1`L3HOqT1[BTyWYS"03?(AIb/_S^IDg"Joi_J[BmmvsU9?Ip;O0Dt%4+<m3U<^
D;<G<Vrs,OwqoRI>jc;DE|^dFg=05C&dvXcK@=%T7$_Shi)20a-a1z!S1-T9vZhb"*N;-+3f[u-e.dY19@k5Q`7r7epyZ([UyFm]`+<~ipSXPpT>`rw~b|R6,a[d(LJfch6X`tK~9qUy
#7B]-.SlHO`f$R^XU9a_n+m*rV:9ySj=1X0,rNaBQCXJMvUX3xe
MZUIa`TB.#B6Av_RV`FTwFVCZLu]2qv,innw=1Gf!p2K;Ca';break;case'default-orange-dark-ef164a8d58dc89b2719f881377f73c89__42198a80.css':$g='%O{SB7nV?&/MUNH!{m#=zi!"d!l`6
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
BNpZvdCRa$xAyW31h=>I6G!I;;iJ``s3ff';break;case'default-purple-dark-0f4fa03fa2d9287ef390780e90d9b06d__42198a80.css':$g='*O{SRcRV?$uMEW3
9kEdT+F;W]X`E)h),4#?fl%d$HA!etXhsF@cAx%aO.yDJUi9>SRtKXomE6k.ct{o?
60v;MG2OVBOri>(At?RXSvIZ!<r;UV9(x.V4"@Q"VOnOK%OX_C3A1pS+/i
wrgXL|kaWE2U&o&;9+;lEK-&_l)c-lD[jhXh``^[uf)Y5C
,A#pt<"hyY3yBla%
4&J.0sf&@*X.a$](HU>l+W7m?RFb*23yZwF/Yp1T]>O8M#oP;BH>f%V4)Z3|Gfo&s~F0Mm9/:H+;570W%<u<Q0?!.8bC^IL$e+WIjZ!<SnEVeiR*58dX/8cThj,@@Sq&X&xn).]W>=BxUlQ;T0*UaUNXX^*[;NVTK,1}tw8X,D/UF%s-QGvXP:qdEYb~t9B)4K,b1O:glcR[l@8#aRiIF4l9Mt@5s$dUcMpqjt/xtr+,A.!@+^8CZu0>k{<5/Ew4_g7VK+Edr*!EHrIvq.o8qCG/edn}L-;w<xHG8hxI_C_uOz$txca5Zg?WGD#Uc;9KNXDFeETWv<J@ZgHT9^:dZL
?2_a
89#zGj*u-e:CwA1j`^
)oAdN>:Emmm;C^^j!=!-nid`xPN%D#6h5rWTOajdEFh
zqM$VgQT*d/gbn!S~EV<G,8VO^)r5WJ%U6r$kOVmGNl@l!;+B_G]v
,OOn_dW3r0{?#Tw&q)a=P3vtQTJ%;OR>&Q0YG:x_s"GS5``<Ix1wH;|,ub9,|n;pFK4!&-j!k:@mCAzRbm,tmLE[s&4Os?RA1P[EHGau}Qe.pJ^YleE?*%Nqrs{y1XU?3a<Wi*sUM$&Vs=uk7$G[kdrllg(L7wAkxBf-U3P#"IN@=L.7|)^W;R{xp-Xs1]^yw5XHSEfr:fCnf_03_wA';break;case'default-red-dark-3c1e28afe2cc92815bc7347df0d5776f__42198a80.css':$g='.O{Rg7nV?&=MUNH!{Cu/Yo)dQ".A5QkQaR_hew4s:t-.w/XVJr`uziG+>W;Y@!~w<wpybK)PRIOuLgt"~Rl3?(VC>&-c/0b6N
?@5i-JU
H<Y(8p/O[$99)!wVN^fP@*5n^)W^xfN`&J[rJ!ZVWr|:?kO`^_)oi.;)/E,lyp/,CWm8&$@U[S~EHf/#jjn&pRI11K}fu#&*Q!y[V9^CpOkrL
<A3XDgpF<Z%Czg}YW-W.+Fw&P;J1RXWKJ$96![[Vuuxe$F_H.Uva/_/=2U)=]n94]J^Cg8f>Jfu5iJd#=3;*>x*N6x@OT6
@n-;mr$(VYLs3+Xg=~d7btpY(+RjBGmifCSAioOebxrDgk*[;XW8K4$Rv]oK+$/%@QtQOYuzZy/^EZLntAB(v17c0$efg3R;u1L@u"[SGww*Mv56R..37in!jtQ"tq"ac1N=24U!(V
laU=XGh2n6siU#Au{#>+dn%Q>efu]7@D6tSc,/#%}A[ih7ZGC]R-Q-f8#sr
V?HV"dN2//F8TCc8BV~qMIy^,a?_BY)j<7,Vtwv:%Pvh06TA<)]/*?[kj_^>-Px.Ortr$d1(;R7]m6AFk&iH*GWL(Cp(E8VV51)n@)AkV7RM4>H36[kPi<a.(@`r;k|Pm*4PeFpF?0u*RxU%}dkVj"`kcrzaiP3`*:IK^
SLuB5Vb=dj
1Dvu:GM$dJUCe|"n:~*F1C9(-Pg&2SA()m5b<*cJrIL.y$7Tb*pn+)j5:1?|Ea>8(Phah?itGWAF3`G0VP%#AH`wmzQwjjD<"wcgJt=[a/S}^gQ4+HRjn>fbY6km6K%zn<r9so]=gE;[>TNeB{xI9EZKpjV.MWpNc3XxEz3D3:a6TKjpcl!&&iwAN&';break;case'main-c1727fb392fab162b6611efe18642a5a__693556d7.js':$g='!`K]`nsZ32#tW"r=6.v"dSd*pMac9DI_osFcDC}l>Aw<Ar%FVN*VN>VN0"{AQ7|w,kBq_YCVqMRPtcWS2B-bW?G^+B
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
D$!V+[qS@)X5k_CTxs~`]=}WT">Ro2{6;axZ;Qz[Ib%9Iks&2HF=Mnn]mGeGabe]7YNPqgzU0q
M8A^COOC!8vN?
IA>{?KC~PKJv5}NHAh1OMjx57k-O^/xw&c%W%K8;87TinU&fqdNfh5*.ib$u-Cjxu..Q_E%>]A>@v?UaFoEE*bJn
-aQ2|l[FcG)LrwCqKp78^MD%v0g,%GhIR:n[](yFKj,%|R[)+fw6rEJ?3HN%q(N9YS.+C$*&9TRx*y~w.[F^a$aidAXLcwNQwO{[bW4fJ-.O[j&ZfKI7<J2dB&KK.D6=rJYQDwy8`p(Cb2P@<d`CZR)w]
b>Aq{@CCAP^?|4]N5/PR=sk;+?.Jq,GQf0PbEHN3!:z&%beU8S{I[[YN?"Iq;_{yQN0_`gJc}.i<nO!CS0dT*`G[/@Aj:Nyhdnb3`_B_q?s
Tu$$%Ymn(e~aZ2*.bv<OD(X68u8M6BgB^;t<j[Ku%U|jIwtJ;LZchazrKV3-mOzhPTB@}/Bs2>{(U`3Wpj#uspYWwo0Y62R#af-3v+DNRsA:})u+u3et(ACIh=Dp.b;P54uqu]oS#PEVe(N_f$x?A/DGJ<EJb(.,J>]9r[{KH.xuiE:)yQcm#@6pO<O&7,B5~<6>Pf:c#*_j@bn))H!l03JG:JW1bq}JcZBx+2_iJ@4(c;LVHerieE
q}%58/ptBGI-Rr%AWy>
yH@c#G`c)>m:<y>pl[.i0&v_a4e8q)<
@7UQTtl{M+q&okoj5^+!)l<9t>%uJ?5B^O_X1{2e+Zuv];r)3Spo!_2(bX?|63U[RF*R4{-tVr-IBzV#DK<%F&t>n:BZFo
rO%PhicwHC"_@@YN]s<gJ`X&`/@L|63<-mjR_4nVf304lXbERUEiMX[>b?lT=Z.gM`Q)gl^jA%D?t6wxK_54Rg[.|Vwyh^"s*U|#e/L_|LCK]#`BrrahlIz_Px4+*up&#jS,VrC.;#^)kWar3u^
JarU!,v#9
yc@*#@b5Y(S2J8m3OK%5I7,A5Jasi3JRacNi"i?v=,a=/VB78ORii<p2:$yV0p)Pi3Hc?$%f+Ynw@eTd^5WCIW@U}u*KLJP2x=s=rT`8^w%a+F#4-9x9HY-kOj)%j^@6y2"(fTg>MQh)5_H_$NdOmwc1
Q~gU2d2!lfi*kF]}jRc:vIah>3/Yo1R$l)Vawf7`Hw>3MQ0sI1Sa2{f0;zQ"+T+VF<cdC87u]P9P?xbDdT[)7axQ#viX?:y7E_?/HJHHE=B0McxOomGyOuBzndG_;Ct`26IB*E<06moKI.4gIa;Hb37$xAS2s3DI[iOfRQg~/z)?r1/.>$WVF5hBZX4<DN&.r+]io(rU_kv[vd%24Duh:xkRD>s5HYgh>(=bq~U."37a[m$SZ~.y=MZdr-8&xFqu)HO[_DGs=nKUH!Tu3Dpi:vvN3Hb]Uk0V7A#1q=3S!c($oZY#gdT(%Oh-9C[d4u%!F1=3^laHX1p%q%n@iPo|>*Bq%
A*:)u%HJ]J&b!Wyl@F*)Z9Hv)Q5e/?/""=UORNo75Dq%^iG?(_KvmZO/eBo.fC-V++I(xds73_1pPwie39Q;^-Plq9oy69p51uQR/`N%rsZ2`Ntx]DYO<K:dL%ACf+?*EuK%`]CU):GBi*m4x/aMnhmTZ3,>m^xkZvT#*LHwL@DD6yjT]TCQ(GZYYR^WH[J(&(y5)CD<.noj[j&#)5RE&-&qn*"G10iss-Ndp!LcYo<4v"DMD!ZmT.8"",hEox_^CJb{5B@CRd-"
;E6IVV2yi5*TdCM/3Y>L5/87ieFtcbgm*/GP{(0[HrHHMQ[
Xy+4(54x;5.HQ*~fhqD)d7!LukIKvHD3ttvD.4<ke5{W}2Ah5"jct,`lR/GsACXyTyWnP;#y=6OmH_m
Z&GpxZhBRVy<pyt^Wtc?`b[3k&+j^S[:@4<JPfcEqfv+bk"awklD=qZBxlV*3fXihJpf
qRHONMv<ijn~Uv/M4D1l+3.Iw<p9f
4RqD0=Eml%F,Us>0B}k<7o46s(c{Tt0smX)(cCE)l1fA!Ytf:6(Z7+uso$/e<Ql-,*]dW`IjI:>Xrq[z?CjWL`$~[kh}hHWT,x,BT
j%MW:LMW&WTcRfYK>J]wA&Jo:+,
)xD<@yaiF$^
$(vsfBA]C/PfypnuuHX/r[cq8M*;O|D7dWR@Y(-.Jg"GdJ50@+J&0t
Y9LM,aw[oeI6UxjSLtCC?*[<8^h+;(I_qr;b-_7ch225v<8,PVO9n=-,<lePM3BFD"R"i_z-^x!GjZ&[$J{X--TtGEp-d"qP~Y0YJU/Y2W]MJ@Y^oSJHfQDIY!A-aN?Q.^^h8KES$sA^Yy1C&+ICkJ;8*gSf[#NL-&Vr?=(%Xn}B9AIJ{#)J}B+^!,WhnPOOTdVF$#ruQZwNQJ@L[;;DNY-sd>++YHJRQR5J~C%(EKSOVByLHRU_nhFZFEwx@xVP{xOvLd>KJa!$c_E?[64#x.7.2AP_BrY]l=`s{s$IbUypKDZFX%6:#"(Vx4=6nMsMo^LJ02pP2?0Ebsj"Q$&0bL]@00%5CYHRA(
K$?#sV_Hu1CAI#!JvX@ZapD!k>o9N!CQjabl87.~Zz;t"hVF0b:kEW%f7)G,`hF!pb9`!"-!`{4Ft)2EdifW7pXO%qf
M:3ch)A:mZ9,]^R9Z542u&(`56u?`K@L!1)M_14Db<x[S<Km+JCX/Z
^XiN-9PqVPVmPg@A-A(SYXtwe0FSBe.3Vya&GFVf.-FbG_"#}=2Du_2B&<"^Rf{;13!R`[G+EQhZ92DNabCdS$*L-j&uyl.0~l[(&EF(v$SZ{.sK"mm4k%aX1`<q
.5&Hj=X+m}Hp0il:+[Dst;4i
NN&v>SpG6u%0;^*AfJOQjo3XE7"bl"sd4^Kfl5#nF&S&^6"4oaS"4s,Pkhh^"W#

&u=[G%[IcJN}*Pl+Rt#dP5u9l6T*q.T[7#`+rbO)iZ)bS"wNU}Tp!]Jy$5s.]|KRy1b_o(,`';break;default:$g=null;break;}if(!$g){http_response_code(404);exit;}if(in_array($me,["png","ico"]))$g=base64_decode($g);else$g=decompress_string($g);echo$g;exit;}if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define("Adminneo\HTTPS",($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));if(!defined("SID")){ini_set("session.use_trans_sid","0");session_cache_limiter("");session_name("neo_sid");session_set_cookie_params(0,cookie_path(),"",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$De);$_POST=remove_slashes($_POST,$De);$_COOKIE=remove_slashes($_COOKIE,$De);}if(function_exists("set_time_limit"))set_time_limit(0);ini_set("precision","16");@unlink(get_temp_dir()."/adminneo.version");class
Locale{static$Languages=['en'=>'English','id'=>'Bahasa Indonesia','ms'=>'Bahasa Melayu','bs'=>'Bosanski','ca'=>'Català','cs'=>'Čeština','da'=>'Dansk','de'=>'Deutsch','et'=>'Eesti','es'=>'Español','fr'=>'Français','gl'=>'Galego','hr'=>'Hrvatski','it'=>'Italiano','lv'=>'Latviešu','lt'=>'Lietuvių','ro'=>'Limba Română','hu'=>'Magyar','nl'=>'Nederlands','no'=>'Norsk','pl'=>'Polski','pt'=>'Português','pt-BR'=>'Português (Brazil)','sk'=>'Slovenčina','sl'=>'Slovenski','fi'=>'Suomi','sv'=>'Svenska','vi'=>'Tiếng Việt','tr'=>'Türkçe','bg'=>'Български','el'=>'Ελληνικά','ru'=>'Русский','sr'=>'Српски','uk'=>'Українська','he'=>'עברית','ar'=>'العربية','fa'=>'فارسی','hi'=>'हिन्दी','bn'=>'বাংলা','ta'=>'த‌மிழ்','th'=>'ภาษาไทย','ka'=>'ქართული','ja'=>'日本語','zh'=>'简体中文','zh-TW'=>'繁體中文','ko'=>'한국어',];private$language;private$translations;private
static$instance=null;static
function
create($ch){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($ch);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct($ch){$this->language=$ch;}function
getLanguage(){return$this->language;}function
setTranslations(array$Pn){$this->translations=$Pn;}function
getTranslations(){return$this->translations;}function
translate($w,$Ni=null){$w=$this->convertTranslationKey($w);$On=isset($this->translations[$w])?$this->translations[$w]:$w;$ch=$this->language;if(is_array($On)){$qk=($Ni==1?0:($ch=='cs'||$ch=='sk'?($Ni&&$Ni<5?1:2):($ch=='fr'?(!$Ni?0:1):($ch=='pl'?($Ni%10>1&&$Ni%10<5&&$Ni/10%10!=1?1:2):($ch=='sl'?($Ni%100==1?0:($Ni%100==2?1:($Ni%100==3||$Ni%100==4?2:3))):($ch=='lt'?($Ni%10==1&&$Ni%100!=11?0:($Ni%10>1&&$Ni/10%10!=1?1:2)):($ch=='lv'?($Ni%10==1&&$Ni%100!=11?0:($Ni?1:2)):($ch=='ro'?(!$Ni||($Ni%100>0&&$Ni%100<20)?1:2):($ch=='bs'||$ch=='hr'||$ch=='ru'||$ch=='sr'||$ch=='uk'?($Ni%10==1&&$Ni%100!=11?0:($Ni%10>1&&$Ni%10<5&&$Ni/10%10!=1?1:2)):1)))))))));$On=$On[$qk];}$On=str_replace("'",'’',$On);$Qa=func_get_args();array_shift($Qa);$Te=str_replace("%d","%s",$On);if($Te!=$On)$Qa[0]=format_number($Ni);return
vsprintf($Te,$Qa);}function
convertTranslationKey($w){static$Md=null;if(is_string($w)){if(!$Md)$Md=get_translations("en");if(($u=array_search($w,$Md))!==false)$w=$u;elseif(($u=get_plural_translation_id($w))!==null)$w=$u;}return$w;}}function
get_available_languages(){return
array('ar'=>true,'bg'=>true,'bn'=>true,'bs'=>true,'ca'=>true,'cs'=>true,'da'=>true,'de'=>true,'el'=>true,'en'=>true,'es'=>true,'et'=>true,'fa'=>true,'fi'=>true,'fr'=>true,'gl'=>true,'he'=>true,'hi'=>true,'hr'=>true,'hu'=>true,'id'=>true,'it'=>true,'ja'=>true,'ka'=>true,'ko'=>true,'lt'=>true,'lv'=>true,'ms'=>true,'nl'=>true,'no'=>true,'pl'=>true,'pt-BR'=>true,'pt'=>true,'ro'=>true,'ru'=>true,'sk'=>true,'sl'=>true,'sr'=>true,'sv'=>true,'ta'=>true,'th'=>true,'tr'=>true,'uk'=>true,'vi'=>true,'zh-TW'=>true,'zh'=>true,);}function
get_lang(){return
Locale::get()->getLanguage();}function
lang($w,$Ni=null){return
call_user_func_array([Locale::get(),"translate"],func_get_args());}function
get_language_options(){$bb=get_available_languages();if(count($bb)==1)return[];$C=[];foreach(Locale::$Languages
as$ch=>$S){if(isset($bb[$ch]))$C[$ch]=$S;}return$C;}function
language_select(){$C=get_language_options();if(!$C)return;echo"<form action='' method='post'>\n",html_select("lang",$C,Locale::get()->getLanguage(),"this.form.submit();"),"<input type='submit' value='".lang(81),"' class='button hidden'>\n",input_token(),"</form>\n";}$bb=get_available_languages();$ch=array_keys($bb)[0];$vk=null;if(isset($_POST["lang"])&&isset($bb[$_POST["lang"]])&&verify_token()){$vk=$_SESSION["lang"]=$_POST["lang"];$_SESSION["translations"]=[];}$_l=($ta=Settings::readParameter("lang"))!==null?$ta:(isset($_COOKIE["neo_lang"])?$_COOKIE["neo_lang"]:null);if($_l!==null&&isset($bb[$_l]))$ch=$_l;elseif(isset($_SESSION["lang"])&&isset($bb[$_SESSION["lang"]]))$ch=$_SESSION["lang"];elseif(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){$va=[];preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$A,PREG_SET_ORDER);foreach($A
as$_)$va[$_[1]]=(isset($_[3])?$_[3]:1);arsort($va);foreach($va
as$w=>$Jk){if(isset($bb[$w])){$ch=$w;break;}$w=preg_replace('~-.*~','',$w);if(!isset($va[$w])&&isset($bb[$w])){$ch=$w;break;}}}Locale::create($ch);abstract
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
openPasswordless($L,$U,$E,$Hm=true){$vf=Admin::get()->getConfig()->getDefaultPasswordHash()!="";if($E!=""&&($Hm||$vf)&&$this->open($L,$U,"")){$G=Admin::get()->verifyDefaultPassword($E);if($G!==true){$this->error=$G;return
false;}return
true;}return$this->open($L,$U,$E);}abstract
function
open($L,$U,$E);function
getFlavor(){return$this->flavor;}function
isMariaDB(){return$this->flavor=="mariadb";}function
isCockroachDB(){return$this->flavor=="cockroach";}function
getVersion(){return$this->version;}function
isMinVersion($Fo){return
version_compare($this->version,$Fo)>=0;}function
getAffectedRows(){return$this->affectedRows;}function
setAffectedRows($Ea){$this->affectedRows=$Ea;}function
getErrno(){return$this->errno;}function
getError(){return$this->error;}function
setError($k){$this->error=$k;}function
isUntrusted(){return$this->untrusted;}function
setUntrusted($mo){$this->untrusted=$mo;}abstract
function
selectDatabase($B);abstract
function
quote($O);function
formatValue($X,array$l){return$X;}abstract
function
query($F,$co=false);function
getQueryInfo(){return
null;}function
getWarningsCount(){return
0;}function
getResult($F,$l=0){return$this->getValue($F,$l);}function
getValue($F,$ue=0){$G=$this->query($F);if(!is_object($G))return
false;$I=$G->fetchRow();return$I?$I[$ue]:false;}function
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
__construct($wl){$this->rowsCount=$wl;}function
getRowsCount(){return$this->rowsCount;}abstract
function
fetchAssoc();abstract
function
fetchRow();abstract
function
fetchField();function
seek($Si){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoConnection
extends
Connection{protected$pdo;protected$multiResult;protected
function
dsn($zd,$U,$E,array$C=[],$Lb="PDO"){$C[PDO::ATTR_ERRMODE]=PDO::ERRMODE_SILENT;try{$this->pdo=new$Lb($zd,$U,$E,$C);}catch(Exception$de){$this->error=$de->getMessage();return
false;}$this->version=preg_replace('~^\D*([\d.]+).*~',"$1",(string)@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION));return
true;}function
quote($O){return$this->pdo->quote($O);}function
query($F,$co=false){$Fm=$this->pdo->query($F);$this->error="";if(!$Fm)return$this->storeError(false);$G=new
PdoResult($Fm);$this->storeResult($G);return$G;}function
storeResult($G=null){if(!$G){$G=$this->multiResult;if(!$G)return
false;}if($G->getColumnsCount())return$G;$this->affectedRows=$G->getAffectedRowsCount();return
true;}function
nextResult(){$this->error="";if(!$this->multiResult)return
false;if($this->multiResult->nextRowset())return
true;$Wd=$this->multiResult->getErrorInfo();$this->multiResult=false;if(!isset($Wd[2]))return
false;$this->storeErrorInfo($Wd);return
true;}function
inTransaction(){return$this->pdo->inTransaction();}function
begin(){return$this->storeError($this->pdo->beginTransaction());}function
commit(){return!$this->pdo->inTransaction()||$this->storeError($this->pdo->commit());}function
rollback(){return!$this->pdo->inTransaction()||$this->storeError($this->pdo->rollBack());}private
function
storeError($H){if(!$H)$this->storeErrorInfo($this->pdo->errorInfo());return$H;}protected
function
storeErrorInfo(array$Wd){list(,$Vd,$k)=$Wd;$this->errno=(int)$Vd;$this->error=$k?:lang(125);}}class
PdoResult
extends
Result{private$statement;private$offset=0;function
__construct(PDOStatement$Fm){parent::__construct(max($Fm->columnCount()?$Fm->rowCount():0,0));$this->statement=$Fm;}function
getColumnsCount(){return$this->statement->columnCount();}function
getAffectedRowsCount(){return$this->statement->rowCount();}function
fetchAssoc(){return$this->fetchArray(PDO::FETCH_ASSOC);}function
fetchRow(){return$this->fetchArray(PDO::FETCH_NUM);}private
function
fetchArray($ii){$G=$this->statement->fetch($ii);return$G?array_map([$this,'unresource'],$G):$G;}private
function
unresource($X){return
is_resource($X)?stream_get_contents($X):$X;}function
fetchField(){$I=$this->statement->getColumnMeta($this->offset++);if($I===false)return
false;$T=$I["pdo_type"];$I["type"]=($T==PDO::PARAM_INT?0:15);$I["charsetnr"]=($T==\PDO::PARAM_LOB||(isset($I["flags"])&&in_array("blob",(array)$I["flags"]))?63:0);return(object)$I;}function
seek($Si){for($r=0;$r<$Si;$r++){if($this->statement->fetch()===false)return
false;;}return
true;}function
nextRowset(){$this->offset=0;return@$this->statement->nextRowset();}function
getErrorInfo(){return$this->statement->errorInfo();}}}class
Drivers{private
static$drivers=[];private
static$extensions=[];static
function
add($s,$B,array$ne){self::$drivers[$s]=$B;self::$extensions[$s]=$ne;}static
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
setUserTypes(array$bo){$this->userTypes=array_flip($bo);$this->types[lang(112)]=array_fill_keys(array_keys($this->userTypes),0);}function
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
select($P,array$K,array$Z,array$nf,array$rj=[],$y=1,$D=0,$Bk=false){$Bg=(count($nf)<count($K));$F="SELECT".limit(($_GET["page"]!="last"&&$y&&$nf&&$Bg&&DIALECT=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$K)."\nFROM ".table($P),($Z?"\nWHERE ".implode(" AND ",$Z):"").($nf&&$Bg?"\nGROUP BY ".implode(", ",$nf):"").($rj?"\nORDER BY ".implode(", ",$rj):""),$y,($D?$y*$D:0),"\n");$Em=microtime(true);$H=$this->connection->query($F);if($Bk)echo
Admin::get()->formatSelectQuery($F,$Em,!$H);return$H;}function
delete($P,$Mk,$y=0){$F="FROM ".table($P);return
queries("DELETE".($y?limit1($P,$F,$Mk):" $F$Mk"));}function
update($P,array$Tk,$Mk,$y=0,$Sl="\n"){$Y=[];foreach($Tk
as$w=>$W)$Y[]="$w = $W";$F=table($P)." SET$Sl".implode(",$Sl",$Y);return
queries("UPDATE".($y?limit1($P,$F,$Mk,$Sl):" $F$Mk"));}function
insert($P,array$Tk){return
queries("INSERT INTO ".table($P).($Tk?" (".implode(", ",array_keys($Tk)).")\nVALUES (".implode(", ",$Tk).")":" DEFAULT VALUES").$this->getInsertReturningSql($P));}function
getInsertReturningSql($P){return"";}function
insertUpdate($P,array$Uk,array$_k){return
false;}function
begin(){remember_query("BEGIN");return$this->connection->begin();}function
commit(){remember_query("COMMIT");return$this->connection->commit();}function
rollback(){remember_query("ROLLBACK");return$this->connection->rollback();}function
slowQuery($F,$Cn){return
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
tableHelp($B,$_g=false){return
null;}function
supportsIndex(array$bn){return!is_view($bn);}function
supportsAlterIndex(array$bn){return
true;}function
getIndexAlgorithms(array$bn){return[];}function
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
explodeArrayValue($X,$T,&$Al){return[];}function
implodeArrayValues(array$Y,$T){return"";}function
checkConstraints($P){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA
	AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->connection->isMariaDB()?" AND c.TABLE_NAME = ".q($P):"")."
WHERE c.CONSTRAINT_SCHEMA = ".($_GET["ns"]!=""?q($_GET["ns"]):(DIALECT=="pgsql"?"current_schema()":"DATABASE()"))."
AND t.TABLE_NAME = ".q($P).(DIALECT=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->connection);}function
getAllFields(){if(DB=="")return[];$Ha=[];$Jg=(DIALECT=="pgsql"||DIALECT=="mssql");$_k=(DIALECT=="sql"?"c.COLUMN_KEY = 'PRI'":($Jg?"k.COLUMN_NAME":""));$J=get_rows("SELECT c.TABLE_NAME AS tab, c.COLUMN_NAME AS field, c.IS_NULLABLE AS nullable,
	c.DATA_TYPE AS type, c.CHARACTER_MAXIMUM_LENGTH AS length".($_k?",
	$_k AS ".idf_escape("primary"):"")."
FROM INFORMATION_SCHEMA.COLUMNS c".($Jg?"
LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
	ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
	ON t.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND t.CONSTRAINT_NAME = k.CONSTRAINT_NAME
		AND c.TABLE_SCHEMA = k.TABLE_SCHEMA AND c.TABLE_NAME = k.TABLE_NAME AND c.COLUMN_NAME = k.COLUMN_NAME":"")."
WHERE c.TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",$this->connection);foreach($J
as$I){$I["null"]=($I["nullable"]=="YES");$Ha[$I["tab"]][]=$I;}return$Ha;}}Drivers::add("mysql","MySQL",["MySQLi","PDO_MySQL"]);if(isset($_GET["mysql"])){define("AdminNeo\DRIVER","mysql");define("AdminNeo\DIALECT","sql");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","MySQLi");class
MySqlConnection
extends
Connection{private$mysqli;protected
function
__construct(){parent::__construct();$this->mysqli=new
mysqli();$this->mysqli->init();}function
getDefaultServerName(){return"localhost";}function
open($L,$U,$E){mysqli_report(MYSQLI_REPORT_OFF);list($Kf,$pk)=host_port($L);$w=Admin::get()->getConfig()->getSslKey();$xb=Admin::get()->getConfig()->getSslCertificate();$vb=Admin::get()->getConfig()->getSslCaCertificate();$Cm=$w||$xb||$vb;if($Cm){$this->mysqli->ssl_set($w,$xb,$vb,null,null);$Le=Admin::get()->getConfig()->getSslTrustServerCertificate()?64:MYSQLI_CLIENT_SSL;}else$Le=0;$jc=@$this->mysqli->real_connect(($L!=""?$Kf:ini_get("mysqli.default_host")),($L.$U!=""?$U:ini_get("mysqli.default_user")),($L.$U.$E!=""?$E:ini_get("mysqli.default_pw")),null,(is_numeric($pk)?(int)$pk:ini_get("mysqli.default_port")),(!is_numeric($pk)?$pk:null),$Le);$this->mysqli->options(MYSQLI_OPT_LOCAL_INFILE,false);if($jc){$eg=$this->mysqli->get_server_info();$this->version=str_replace("-MariaDB","",$eg);$this->flavor=str_contains($eg,"MariaDB")?"mariadb":null;}return$jc;}function
getAffectedRows(){return$this->mysqli->affected_rows;}function
getErrno(){return$this->mysqli->errno;}function
getError(){return$this->mysqli->error;}function
selectDatabase($B){return$this->mysqli->select_db($B);}function
setCharset($Ab){if($this->mysqli->set_charset($Ab))return
true;$this->mysqli->set_charset('utf8');return(bool)$this->query("SET NAMES $Ab");}function
quote($O){return"'".$this->mysqli->escape_string($O)."'";}function
query($F,$co=false){$G=$this->mysqli->query($F,($co?MYSQLI_USE_RESULT:MYSQLI_STORE_RESULT));return
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
__construct(mysqli_result$jl){parent::__construct($jl->num_rows);$this->resource=$jl;}function
fetchAssoc(){return$this->resource->fetch_assoc();}function
fetchRow(){return$this->resource->fetch_row();}function
fetchField(){return$this->resource->fetch_field();}function
seek($Si){return$this->resource->data_seek($Si);}}}elseif(extension_loaded("pdo_mysql")){define("AdminNeo\DRIVER_EXTENSION","PDO_MySQL");class
MySqlConnection
extends
PdoConnection{function
getDefaultServerName(){return"localhost";}function
open($L,$U,$E){list($Kf,$pk)=host_port($L);$zd="mysql:charset=utf8".($Kf!=""?";host=$Kf":"").($pk?(is_numeric($pk)?";port=":";unix_socket=").$pk:"");$C=[PDO::MYSQL_ATTR_LOCAL_INFILE=>false];if(isset($_GET["select"])&&defined('\PDO::MYSQL_ATTR_MULTI_STATEMENTS'))$C[PDO::MYSQL_ATTR_MULTI_STATEMENTS]=false;$w=Admin::get()->getConfig()->getSslKey();if($w)$C[PDO::MYSQL_ATTR_SSL_KEY]=$w;$xb=Admin::get()->getConfig()->getSslCertificate();if($xb)$C[PDO::MYSQL_ATTR_SSL_CERT]=$xb;$vb=Admin::get()->getConfig()->getSslCaCertificate();if($vb)$C[PDO::MYSQL_ATTR_SSL_CA]=$vb;$Wn=Admin::get()->getConfig()->getSslTrustServerCertificate();if($Wn!==null&&defined('\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT'))$C[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=!$Wn;if(!$this->dsn($zd,$U,$E,$C))return
false;$Go=@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);$this->flavor=str_contains($Go,"MariaDB")?"mariadb":null;return
true;}function
setCharset($Ab){return(bool)$this->query("SET NAMES $Ab");}function
selectDatabase($B){return(bool)$this->query("USE ".idf_escape($B));}function
query($F,$co=false){$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$co);return
parent::query($F,$co);}}}class
MySqlDriver
extends
Driver{protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->types=[lang(126)=>["tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21,],lang(127)=>["date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4,],lang(128)=>["char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295,],lang(129)=>["enum"=>65535,"set"=>64,],lang(130)=>["bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295,],lang(131)=>["geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0,],];$this->unsigned=["unsigned","zerofill","unsigned zerofill"];$Dh=$e->isMariaDB();if($e->isMinVersion($Dh?"10.2":"5.7"))$this->generated=["STORED","VIRTUAL"];$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","FIND_IN_SET","IS NULL","IS NOT NULL","REGEXP","NOT REGEXP","SQL",];$this->functions=["char_length","lower","upper","round","floor","ceil","date","from_unixtime","unix_timestamp","sec_to_time","time_to_sec",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->partitionBy=["RANGE","LIST","HASH","LINEAR HASH","KEY","LINEAR KEY"];$this->insertFunctions=["char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",];if($e->isMinVersion($Dh?"10.2":"5.7.8"))$this->types[lang(128)]["json"]=4294967295;if($Dh&&$e->isMinVersion("10.7")){$this->types[lang(128)]["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if($Dh&&$e->isMinVersion("10.5")){$this->types[lang(132)]["inet6"]=39;if($e->isMinVersion("10.10"))$this->types[lang(132)]["inet4"]=15;}if($e->isMinVersion($Dh?"11.7":"9"))$this->types[lang(126)]["vector"]=16383;$this->systemDatabases=["mysql","information_schema","performance_schema","sys"];}function
insert($P,array$Tk){return($Tk?parent::insert($P,$Tk):queries("INSERT INTO ".table($P)." ()\nVALUES ()"));}function
getUnconvertFunction(array$l){if(preg_match("~binary~",$l["type"]))return"<code class='jush-sql'>UNHEX</code>";elseif($l["type"]=="bit")return
doc_link(['sql'=>'bit-value-literals.html','mariadb'=>"reference/sql-structure/sql-language-structure/binary-literals"],"<code>b''</code>");elseif($l["type"]=="vector")return"<code class='jush-sql'>".($this->connection->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."</code>";elseif(preg_match("~geometry|point|linestring|polygon~",$l["type"]))return"<code class='jush-sql'>GeomFromText</code>";else
return"";}function
getTypeName(stdClass$l){$bo=["decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",];$T=isset($bo[$l->type])?$bo[$l->type]:"";return
parent::getTypeName($l)?:($l->charsetnr==63?str_replace(["text","varchar","char"],["blob","varbinary","binary"],$T):$T);}function
quoteBinary($O){return"X".q(bin2hex($O));}function
insertUpdate($P,array$Uk,array$_k){$d=array_keys(reset($Uk));$wk="INSERT INTO ".table($P)." (".implode(", ",$d).") VALUES\n";$Y=[];foreach($d
as$w)$Y[$w]="$w = VALUES($w)";$Pm="\nON DUPLICATE KEY UPDATE ".implode(", ",$Y);$Y=[];$x=0;foreach($Uk
as$Tk){$X="(".implode(", ",$Tk).")";if($Y&&(strlen($wk)+$x+strlen($X)+strlen($Pm)>1e6)){if(!queries($wk.implode(",\n",$Y).$Pm))return
false;$Y=[];$x=0;}$Y[]=$X;$x+=strlen($X)+2;}return
queries($wk.implode(",\n",$Y).$Pm);}function
slowQuery($F,$Cn){$Dh=$this->connection->isMariaDB();if(!$this->connection->isMinVersion($Dh?"10.1.2":"5.7.8"))return
null;if($Dh)return"SET STATEMENT max_statement_time=$Cn FOR $F";elseif(preg_match('~^(SELECT\b)(.+)~is',$F,$_))return"$_[1] /*+ MAX_EXECUTION_TIME(".($Cn*1000).") */ $_[2]";else
return
null;}function
convertSearch($t,array$Z,array$l){return(preg_match('~char|text|enum|set~',$l["type"])&&!preg_match("~^utf8~",$l["collation"])&&preg_match('~[\x80-\xFF]~',$Z['val'])?"CONVERT($t USING ".charset($this->connection).")":$t);}function
isSearchable(array$Z,array$l){return$l["type"]!="bit"||preg_match('~IN$|NULL$|^(FIND_IN_SET|SQL)$~',$Z["op"])||($Z["op"]!="LIKE %%"&&preg_match('~^[01]+$~',$Z["val"]));}function
warnings(){$G=$this->connection->query("SHOW WARNINGS");if($G&&$G->getRowsCount()){ob_start();print_select_result($G);return
ob_get_clean();}return
null;}function
tableHelp($B,$_g=false){$Dh=$this->connection->isMariaDB();if(DB=="information_schema"){$B=strtolower($B);return$Dh?"reference/system-tables/information-schema/information-schema-tables/".(str_starts_with($B,"innodb_")?"information-schema-innodb-tables/":"")."information-schema-$B-table":"information-schema-".str_replace("_","-",$B)."-table.html";}if(DB=="performance_schema")return$Dh?"reference/system-tables/performance-schema/performance-schema-tables/performance-schema-$B-table":"performance-schema-".str_replace("_","-",$B)."-table.html";if(DB=="sys"){if($Dh)return"reference/system-tables/sys-schema/";return"sys-".strtolower(str_replace("_","-",preg_replace('~^x\$~','',$B))).".html";}if(DB=="mysql")return$Dh?"reference/system-tables/the-mysql-database-tables/mysql-$B".str_starts_with($B,"innodb_")?"":"-table":"system-schema.html";return
null;}function
getPartitionsInfo($P){$bf="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($P);$G=Connection::get()->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $bf ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1")->fetchRow();if(!$G)return[];$eg=["partition_by"=>$G[0],"partition"=>$G[1],"partitions"=>$G[2],];$Yj=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $bf AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$eg["partition_names"]=array_keys($Yj);$eg["partition_values"]=array_values($Yj);return$eg;}function
getIndexAlgorithms(array$bn){return
preg_match('~^(MEMORY|NDB)$~',$bn["Engine"])?["BTREE","HASH"]:["BTREE"];}function
hasCStyleEscapes(){static$tb;if($tb===null){$_m=$this->connection->getValue("SHOW VARIABLES LIKE 'sql_mode'",1);$tb=(strpos($_m,'NO_BACKSLASH_ESCAPES')===false);}return$tb;}function
lineComment(){return"#|-- ";}function
engines(){$Rd=[];foreach(get_rows("SHOW ENGINES")as$I){if(preg_match("~YES|DEFAULT~",$I["Support"]))$Rd[]=$I["Engine"];}return$Rd;}}function
create_driver(Connection$e){return
MySqlDriver::create($e,Admin::get());}function
idf_escape($t){return"`".str_replace("`","``",$t)."`";}function
table($t){return
idf_escape($t);}function
connect($_k=false,&$k=null){$e=$_k?MySqlConnection::create():MySqlConnection::createSecondary();list($L,$U,$E)=Admin::get()->getCredentials();if(!$e->openPasswordless($L,$U,$E,false)){$k=$e->getError();if(function_exists('iconv')&&!is_utf8($k)&&strlen($xl=iconv("windows-1252","utf-8//IGNORE",$k))>strlen($k))$k=$xl;return
null;}$e->setCharset(charset($e));$e->query("SET sql_quote_show_create = 1, autocommit = 1");if($_k&&$e->isMariaDB()){Drivers::setName(DRIVER,"MariaDB");save_driver_name(DRIVER,$L,"MariaDB");}return$e;}function
get_databases($Ne){$h=get_session("dbs");if($h===null){$F="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$Em=microtime(true);$h=($Ne?slow_query($F):get_vals($F));if(microtime(true)-$Em>0.1){restart_session();set_session("dbs",$h);stop_session();}}return$h;}function
limit($F,$Z,$y,$Si=0,$Sl=" "){return" $F$Z".($y?$Sl."LIMIT $y".($Si?" OFFSET $Si":""):"");}function
limit1($P,$F,$Z,$Sl="\n"){return
limit($F,$Z,1,0,$Sl);}function
db_collation($i,array$Rb){$H=null;$f=Connection::get()->getValue("SHOW CREATE DATABASE ".idf_escape($i),1);if(preg_match('~ COLLATE ([^ ]+)~',$f,$_))$H=$_[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$f,$_))$H=$Rb[$_[1]][-1];return$H;}function
logged_user(){return
Connection::get()->getValue("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$h){$H=[];foreach($h
as$i)$H[$i]=count(get_vals("SHOW TABLES IN ".idf_escape($i)));return$H;}function
table_status($B="",$se=false){if($se)$F="SELECT TABLE_NAME AS Name, ENGINE AS Engine, CREATE_OPTIONS AS Create_options,
	TABLES.TABLE_COLLATION AS Collation, TABLE_COMMENT AS Comment
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() ".($B!=""?"AND TABLE_NAME = ".q($B):"ORDER BY Name");else$F="SHOW TABLE STATUS".($B!=""?" LIKE ".q(addcslashes($B,"%_\\")):"");$R=[];foreach(get_rows($F)as$I){if($I["Engine"]=="InnoDB")$I["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$I["Comment"]);if(!isset($I["Engine"]))$I["Comment"]="";if($B!="")$I["Name"]=$B;$R[$I["Name"]]=$I;}return$R;}function
is_view(array$Q){return$Q["Engine"]===null;}function
fk_support(array$Q){return
preg_match('~InnoDB|IBMDB2I'.(Connection::get()->isMinVersion("5.6")?'|NDB':'').'~i',$Q["Engine"]);}function
parse_type($df){preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$df,$A);return[$A[1],isset($A[2])?$A[2]:"",ltrim((isset($A[3])?$A[3]:"").(isset($A[4])?$A[4]:""))];}function
fields($P){$Dh=Connection::get()->isMariaDB();$H=[];foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($P)." ORDER BY ORDINAL_POSITION")as$I){$l=$I["COLUMN_NAME"];$T=preg_replace('~\s?/\*.+\*/~U',"",$I["COLUMN_TYPE"]);$oe=$I["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$oe,$gf);list($ao,$x,$ko)=parse_type($T);$j=$Dh&&$I["COLUMN_DEFAULT"]=="NULL"?null:$I["COLUMN_DEFAULT"];if($j!==null){$Eg=preg_match('~(text|json)~',$ao);if(!$Dh&&$Eg)$j=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($j));if($Dh||$Eg){$j=preg_replace_callback("~^'(.*)'$~",function($A){return
stripslashes(str_replace("''","'",$A[1]));},$j);}if(!$Dh&&preg_match('~binary~',$ao)&&preg_match('~^0x(\w*)$~',$j,$A))$j=pack("H*",$A[1]);}$if=$I["GENERATION_EXPRESSION"];if(!$Dh)$if=preg_replace("~(^|,|\()(_\w+)?('.*')($|,|\))~",'\1\3\4',stripslashes($if));$H[$l]=["field"=>$l,"full_type"=>$T,"type"=>$ao,"length"=>$x,"unsigned"=>$ko,"default"=>($gf?$if:$j),"null"=>($I["IS_NULLABLE"]=="YES"),"auto_increment"=>($oe=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$oe,$A)?$A[1]:""),"collation"=>$I["COLLATION_NAME"],"privileges"=>array_flip(explode(",",$I["PRIVILEGES"]))+["where"=>1,"order"=>1],"comment"=>$I["COLUMN_COMMENT"],"primary"=>($I["COLUMN_KEY"]=="PRI"),"generated"=>($gf[1]=="PERSISTENT"?"STORED":$gf[1]),];}return$H;}function
indexes($P,$e=null){$H=[];foreach(get_rows("SHOW INDEX FROM ".table($P),$e)as$I){$B=$I["Key_name"];$H[$B]["type"]=($B=="PRIMARY"?"PRIMARY":($I["Index_type"]=="FULLTEXT"?"FULLTEXT":($I["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$I["Index_type"])?$I["Index_type"]:"INDEX"):"UNIQUE")));$H[$B]["columns"][]=$I["Column_name"];$H[$B]["lengths"][]=($I["Index_type"]=="SPATIAL"?null:$I["Sub_part"]);$H[$B]["descs"][]=($I["Collation"]=="D"?'1':null);$H[$B]["algorithm"]=$I["Index_type"];}return$H;}function
foreign_keys($P){static$ek='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$H=[];$zc=Connection::get()->getValue("SHOW CREATE TABLE ".table($P),1);if($zc){$ej=implode("|",Driver::get()->getOnActions());preg_match_all("~CONSTRAINT ($ek) FOREIGN KEY ?\\(((?:$ek,? ?)+)\\) REFERENCES ($ek)(?:\\.($ek))? "."\\(((?:$ek,? ?)+)\\)(?: ON DELETE ($ej))?(?: ON UPDATE ($ej))?~",$zc,$A,PREG_SET_ORDER);foreach($A
as$_){preg_match_all("~$ek~",$_[2],$tm);preg_match_all("~$ek~",$_[5],$rn);$H[idf_unescape($_[1])]=["db"=>idf_unescape($_[4]!=""?$_[3]:$_[4]),"table"=>idf_unescape($_[4]!=""?$_[4]:$_[3]),"source"=>array_map('AdminNeo\idf_unescape',$tm[0]),"target"=>array_map('AdminNeo\idf_unescape',$rn[0]),"on_delete"=>($_[6]?:"RESTRICT"),"on_update"=>($_[7]?:"RESTRICT"),];}}return$H;}function
backward_keys($P){$F="SELECT CONSTRAINT_NAME AS constraint_name, TABLE_SCHEMA AS table_schema, TABLE_NAME AS table_name,
COLUMN_NAME AS column_name, REFERENCED_COLUMN_NAME AS referenced_column_name
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = ".q(Admin::get()->getDatabase())."
AND REFERENCED_TABLE_SCHEMA = ".q(Admin::get()->getDatabase())."
AND REFERENCED_TABLE_NAME = ".q($P)."
ORDER BY ORDINAL_POSITION";return
get_rows($F,null,"");}function
view($B){$K=Connection::get()->getValue("SHOW CREATE VIEW ".table($B),1);$zh='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$K=preg_replace("~^$zh\\s+AS\\s+~isU","",$K);return["select"=>format_sql($K)];}function
collations(){$H=[];$F=Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("10.10")?"SELECT CHARACTER_SET_NAME AS Charset, FULL_COLLATION_NAME AS Collation, IS_DEFAULT AS `Default` FROM information_schema.COLLATION_CHARACTER_SET_APPLICABILITY":"SHOW COLLATION";foreach(get_rows($F)as$I){if($I["Default"])$H[$I["Charset"]][-1]=$I["Collation"];else$H[$I["Charset"]][]=$I["Collation"];}ksort($H);foreach($H
as$w=>$W)sort($H[$w]);return$H;}function
information_schema($i,$El=""){return($i=="information_schema")||(Connection::get()->isMinVersion("5.5")&&$i=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",Connection::get()->getError()));}function
create_database($i,$Qb){return(bool)queries("CREATE DATABASE ".idf_escape($i).($Qb?" COLLATE ".q($Qb):""));}function
drop_databases(array$h){$H=apply_queries("DROP DATABASE",$h,'AdminNeo\idf_escape');restart_session();set_session("dbs",null);return$H;}function
rename_database($B,$Qb){$H=false;if(create_database($B,$Qb)){$R=[];$Jo=[];foreach(tables_list()as$P=>$T){if($T=='VIEW')$Jo[]=$P;else$R[]=$P;}$H=(!$R&&!$Jo)||move_tables($R,$Jo,$B);drop_databases($H?[DB]:[]);}return$H;}function
auto_increment(){$Za=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$u){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$u["columns"],true)){$Za="";break;}if($u["type"]=="PRIMARY")$Za=" UNIQUE";}}return" AUTO_INCREMENT$Za";}function
alter_table($P,$B,array$m,array$Pe,$ac,$Qd,$Qb,$Ya,$Xj){$b=[];foreach($m
as$l){if($l[1]){$j=$l[1][3];if(str_contains($j," GENERATED")){$l[1][3]=Connection::get()->isMariaDB()?"":$l[1][2];$l[1][2]=$j;}$b[]=($P!=""?($l[0]!=""?"CHANGE ".idf_escape($l[0]):"ADD"):" ")." ".implode($l[1]).($P!=""?$l[2]:"");}else$b[]="DROP ".idf_escape($l[0]);}$b=array_merge($b,$Pe);$N=($ac!==null?" COMMENT=".q($ac):"").($Qd?" ENGINE=".q($Qd):"").($Qb?" COLLATE ".q($Qb):"").($Ya!=""?" AUTO_INCREMENT=$Ya":"");if($Xj){$Yj=[];if($Xj["partition_by"]=='RANGE'||$Xj["partition_by"]=='LIST'){foreach($Xj["partition_names"]as$w=>$W){$X=$Xj["partition_values"][$w];$Yj[]="\n  PARTITION ".idf_escape($W)." VALUES ".($Xj["partition_by"]=='RANGE'?"LESS THAN":"IN").($X!=""?" ($X)":" MAXVALUE");}}$N
.="\nPARTITION BY {$Xj["partition_by"]}({$Xj["partition"]})";if($Yj)$N
.=" (".implode(",",$Yj)."\n)";elseif($Xj["partitions"])$N
.=" PARTITIONS ".(int)$Xj["partitions"];}elseif($Xj===null)$N
.="\nREMOVE PARTITIONING";if($P=="")return(bool)queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$N");if($P!=$B)$b[]="RENAME TO ".table($B);if($N)$b[]=ltrim($N);return!$b||queries("ALTER TABLE ".table($P)."\n".implode(",\n",$b));}function
alter_indexes($P,array$b){$zb=[];foreach($b
as$w=>$W)$zb[]=($W[2]=="DROP"?"\nDROP INDEX ".idf_escape($W[1]):"\nADD $W[0] ".($W[0]=="PRIMARY"?"KEY ":"").($W[1]!=""?idf_escape($W[1])." ":"")."(".implode(", ",$W[2]).")");return(bool)queries("ALTER TABLE ".table($P).implode(",",$zb));}function
truncate_tables(array$R){return
apply_queries("TRUNCATE TABLE",$R);}function
drop_views(array$Jo){return(bool)queries("DROP VIEW ".implode(", ",array_map('AdminNeo\table',$Jo)));}function
drop_tables(array$R){return(bool)queries("DROP TABLE ".implode(", ",array_map('AdminNeo\table',$R)));}function
move_tables(array$R,array$Jo,$rn){$gl=[];foreach($R
as$P)$gl[]=table($P)." TO ".idf_escape($rn).".".table($P);if(!$gl||queries("RENAME TABLE ".implode(", ",$gl))){$Uc=[];foreach($Jo
as$P)$Uc[table($P)]=view($P);Connection::get()->selectDatabase($rn);$i=idf_escape(DB);foreach($Uc
as$B=>$Ho){if(!queries("CREATE VIEW $B AS ".str_replace(" $i."," ",$Ho["select"]))||!queries("DROP VIEW $i.$B"))return
false;}return
true;}return
false;}function
copy_tables(array$R,array$Jo,$rn){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($R
as$P){$B=($rn==DB?table("copy_$P"):idf_escape($rn).".".table($P));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $B"))||!queries("CREATE TABLE $B LIKE ".table($P))||!queries("INSERT INTO $B SELECT * FROM ".table($P)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($P,"%_\\")))as$I){$Sn=$I["Trigger"];if(!queries("CREATE TRIGGER ".($rn==DB?idf_escape("copy_$Sn"):idf_escape($rn).".".idf_escape($Sn))." $I[Timing] $I[Event] ON $B FOR EACH ROW\n$I[Statement];"))return
false;}}foreach($Jo
as$P){$B=($rn==DB?table("copy_$P"):idf_escape($rn).".".table($P));$Ho=view($P);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $B"))||!queries("CREATE VIEW $B AS $Ho[select]"))return
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
as$I){$df=$I["DTD_IDENTIFIER"];list($ao,$x,$ko)=parse_type($df);$m[]=["field"=>$I["PARAMETER_NAME"],"type"=>$ao,"length"=>$x,"unsigned"=>$ko,"null"=>true,"full_type"=>$df,"inout"=>($T=="FUNCTION"?"":$I["PARAMETER_MODE"]),"collation"=>$I["CHARACTER_SET_NAME"],];}$H=Connection::get()->query("SELECT
	ROUTINE_COMMENT comment,
	CONCAT(IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC\\n', ''), IF(SQL_DATA_ACCESS != 'CONTAINS SQL', CONCAT(SQL_DATA_ACCESS, '\\n'), '')) characteristics,
	ROUTINE_DEFINITION definition,
	'SQL' language
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND ROUTINE_NAME = ".q($B))->fetchAssoc();$f=Connection::get()->getValue("SHOW CREATE $T ".idf_escape($B),2);$O="'(?:[^'\\\\]|\\\\.)*'";$Jn='`[^`]*`|"(?:[^"\\\]|\\\.)*"|$string|/\*.*?\*/|/';$_b="    (?:[A-Z ]+|COMMENT (?:$O)+)\\n";if($f&&preg_match("~^(?:[^(`\"'/]++|$Jn)*?(\\((?:[^()`\"'/]++|$Jn|(?1))*+\\))(?: RETURNS [^\\n]*)?\\n(?:$_b)*~s",$f,$_))$H['definition']=substr($f,strlen($_[0]));$H['definition']=$H['characteristics'].$H['definition'];unset($H['characteristics']);if($m&&$m[0]['field']=='')$H['returns']=array_shift($m);$H['fields']=$m;return$H;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER, ROUTINE_COMMENT FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return[];}function
routine_id($B,array$I){return
idf_escape($B);}function
last_id($G){return
Connection::get()->getValue("SELECT LAST_INSERT_ID()");}function
explain(Connection$e,$F){return$e->query("EXPLAIN ".(Connection::get()->isMinVersion("5.7")?"":"PARTITIONS ").$F);}function
found_rows(array$Q,array$Z){return$Q["Engine"]=="InnoDB"&&!$Z?(int)$Q["Rows"]:null;}function
format_sql($F){$zh='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$Sg='FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|(NATURAL\s+)?((LEFT|RIGHT)\s+)?((INNER|OUTER|CROSS)\s+)?JOIN';$F=preg_replace("~($zh)\\s+(AS\\s+SELECT)~isU","$1 AS\nSELECT",$F);$F=preg_replace("~($zh)\\s+($Sg)~isU","$1\n$2",$F);$F=preg_replace("~($zh),~isU","$1,\n  ",$F);return$F;}function
create_sql($P,$Ya,$Mm){$F=Connection::get()->getValue("SHOW CREATE TABLE ".table($P),1);if(!$Ya)$F=preg_replace('~ AUTO_INCREMENT=\d+~','',$F);return!str_contains($F,"\n")?format_sql($F):$F;}function
truncate_sql($P){return"TRUNCATE ".table($P);}function
create_database_sql($Jc,$Mm=""){$B=idf_escape($Jc);$Yb="";if(str_contains($Mm,"CREATE")&&($f=Connection::get()->getValue("SHOW CREATE DATABASE $B",1))){set_utf8mb4($f);if($Mm=="DROP+CREATE")$Yb="DROP DATABASE IF EXISTS $B;\n";$Yb
.="$f;\n";}return$Yb;}function
use_sql($Jc,$Mm=""){return"USE ".idf_escape($Jc).";\n";}function
trigger_sql($P){$xm="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($P,"%_\\")),null,"-- ")as$I)$xm
.="\nCREATE TRIGGER ".idf_escape($I["Trigger"])." $I[Timing] $I[Event] ON ".table($I["Table"])." FOR EACH ROW\n$I[Statement];;\n";return$xm;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$l){if(preg_match("~binary~",$l["type"]))return"HEX(".idf_escape($l["field"]).")";if($l["type"]=="bit")return"BIN(".idf_escape($l["field"])." + 0)";if($l["type"]=="vector")return(Connection::get()->isMariaDB()?"VEC_ToText":"VECTOR_TO_STRING")."(".idf_escape($l["field"]).")";if(preg_match("~geometry|point|linestring|polygon~",$l["type"]))return(Connection::get()->isMinVersion("8")?"ST_":"")."AsWKT(".idf_escape($l["field"]).")";return
null;}function
unconvert_field(array$l,$H){if(preg_match("~binary~",$l["type"]))$H="UNHEX($H)";if($l["type"]=="bit")$H="CONVERT(b$H, UNSIGNED)";if($l["type"]=="vector")$H=(Connection::get()->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."($H)";if(preg_match("~geometry|point|linestring|polygon~",$l["type"])){$wk=(Connection::get()->isMinVersion("8")?"ST_":"");$H=$wk."GeomFromText($H, $wk"."SRID($l[field]))";}return$H;}function
support($te){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.8.1":"8")?'|descidx':'').(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.2.1":"8.0.16")?'|check':'').(!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8")?'|fast_status':'').')$~',$te);}function
kill_process($W){return
queries("KILL ".number($W));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return(int)Connection::get()->getValue("SELECT @@max_connections");}}Drivers::add("pgsql","PostgreSQL",["PgSQL","PDO_PgSQL"]);if(isset($_GET["pgsql"])){define("AdminNeo\DRIVER","pgsql");define("AdminNeo\DIALECT","pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","PgSQL");class
PgSqlConnectionBase
extends
Connection{var$timeout=0;private$connection;private$connectionString;private$hasDefaultDatabase;function
open($L,$U,$E){$i=Admin::get()->getDatabase();set_error_handler(function($Vd,$k){if(ini_bool("html_errors"))$k=html_entity_decode(strip_tags($k));$k=preg_replace('~^[^:]*: ~','',$k);$this->error=$k;});list($Kf,$pk)=host_port($L);$this->connectionString=($Kf!=""?"host=$Kf ":"").($pk?"port=$pk ":"")."user='".addcslashes($U,"'\\")."' password='".addcslashes($E,"'\\")."'";$Dm=Admin::get()->getConfig()->getSslMode();if($Dm)$this->connectionString
.=" sslmode='$Dm'";$this->connection=@pg_connect("$this->connectionString dbname='".($i!=""?addcslashes($i,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->connection&&$i!=""){$this->hasDefaultDatabase=false;$this->connection=@pg_connect("$this->connectionString dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}else$this->hasDefaultDatabase=true;restore_error_handler();if($this->connection){$Go=$this->getValue("SELECT version()");$this->flavor=str_contains($Go,"CockroachDB")?"cockroach":null;$this->version=preg_replace('~^\D*([\d.]+[-\w]*).*~',"$1",$Go);pg_set_client_encoding($this->connection,"UTF8");}return(bool)$this->connection;}function
quote($O){return(function_exists('pg_escape_literal')?pg_escape_literal($this->connection,$O):"'".pg_escape_string($this->connection,$O)."'");}function
formatValue($X,array$l){if($l["type"]=="bytea"&&$X!==null)return
pg_unescape_bytea($X);return
parent::formatValue($X,$l);}function
selectDatabase($B){if($B==Admin::get()->getDatabase())return$this->hasDefaultDatabase;$H=@pg_connect("$this->connectionString dbname='".addcslashes($B,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($H)$this->connection=$H;return(bool)$H;}function
close(){$this->connection=@pg_connect("$this->connectionString dbname='postgres'");}function
query($F,$co=false){if(!$this->connection){$this->error="Invalid connection.";return
false;}if($this->untrusted)$G=(@pg_query($this->connection,"BEGIN READ ONLY")?@pg_query_params($this->connection,$F,[]):false);else$G=@pg_query($this->connection,$F);$this->error="";if(!$G){$this->error=pg_last_error($this->connection);$H=false;}elseif(!pg_num_fields($G)){$this->affectedRows=pg_affected_rows($G);$H=true;}else$H=new
PgSqlResult($G);if($this->untrusted)@pg_query($this->connection,"COMMIT");if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$H;}function
getValue($F,$ue=0){$G=$this->query($F);return
is_object($G)?$G->fetchValue($ue):false;}function
warnings(){if(PHP_VERSION_ID>=70100){$Po=pg_last_notice($this->connection,PGSQL_NOTICE_ALL);if(!$Po)return
null;$Po=implode("\n",$Po);pg_last_notice($this->connection,PGSQL_NOTICE_CLEAR);}else{$Po=pg_last_notice($this->connection);if(!$Po)return
null;}return
nl2br(h($Po));}function
copyFrom($P,array$J){$this->error="";set_error_handler(function($Vd,$k){$this->error=ini_bool("html_errors")?html_entity_decode($k):$k;});$G=pg_copy_from($this->connection,$P,$J);restore_error_handler();return$G;}function
inTransaction(){$N=pg_transaction_status($this->connection);return$N==PGSQL_TRANSACTION_INTRANS||$N==PGSQL_TRANSACTION_INERROR;}}class
PgSqlResult
extends
Result{private$resource;private$offset=0;function
__construct($jl){parent::__construct(pg_num_rows($jl));$this->resource=$jl;}function
fetchAssoc(){return
pg_fetch_assoc($this->resource);}function
fetchRow(){return
pg_fetch_row($this->resource);}function
fetchValue($ue){return$this->getRowsCount()?pg_fetch_result($this->resource,0,$ue):false;}function
fetchField(){$c=$this->offset++;$B=pg_field_name($this->resource,$c);if($B===false)return
false;$T=pg_field_type($this->resource,$c);if($T===false)return
false;$xj=pg_field_table($this->resource,$c);return(object)['orgtable'=>($xj!==false?$xj:""),'name'=>$B,'native_type'=>$T,'type'=>(preg_match(number_type(),$T)?0:15),'charsetnr'=>($T=="bytea"?63:0),];}}}elseif(extension_loaded("pdo_pgsql")){define("AdminNeo\DRIVER_EXTENSION","PDO_PgSQL");class
PgSqlConnectionBase
extends
PdoConnection{var$timeout=0;function
open($L,$U,$E){$i=Admin::get()->getDatabase();list($Kf,$pk)=host_port($L);$zd="pgsql:".($Kf!=""?"host=$Kf ":"").($pk?"port=$pk ":"")."client_encoding=utf8 dbname='".($i!=""?addcslashes($i,"'\\"):"postgres")."'";$Dm=Admin::get()->getConfig()->getSslMode();if($Dm)$zd
.=" sslmode='$Dm'";if(!$this->dsn($zd,$U,$E))return
false;$Go=$this->getValue("SELECT version()");$this->flavor=str_contains($Go,"CockroachDB")?"cockroach":null;return
true;}function
selectDatabase($B){return
Admin::get()->getDatabase()==$B;}function
query($F,$co=false){$H=($this->untrusted?$this->readOnlyQuery($F):parent::query($F,$co));if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$H;}private
function
readOnlyQuery($F){$this->error="";if(!$this->pdo->query("BEGIN READ ONLY")){$this->storeErrorInfo($this->pdo->errorInfo());return
false;}$Fm=$this->pdo->prepare($F);$H=false;if($Fm&&$Fm->execute()){$H=new
PdoResult($Fm);$this->storeResult($H);}else$this->storeErrorInfo($Fm?$Fm->errorInfo():$this->pdo->errorInfo());$this->pdo->query("COMMIT");return$H;}function
warnings(){return
null;}function
copyFrom($P,array$J){$G=$this->pdo->pgsqlCopyFromArray($P,$J);$Xd=$this->pdo->errorInfo();$this->error=isset($Xd[2])?$Xd[2]:"";return$G;}function
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
getInsertReturningSql($P){$Xa=array_filter(fields($P),function($l){return$l['auto_increment'];});return
count($Xa)==1?" RETURNING ".idf_escape(key($Xa)):"";}function
insertUpdate($P,array$Uk,array$_k){foreach($Uk
as$Tk){$no=[];$Z=[];foreach($Tk
as$w=>$W){$no[]="$w = $W";if(isset($_k[idf_unescape($w)]))$Z[]="$w = $W";}if(!(($Z&&queries("UPDATE ".table($P)." SET ".implode(", ",$no)." WHERE ".implode(" AND ",$Z))&&Connection::get()->getAffectedRows())||queries("INSERT INTO ".table($P)." (".implode(", ",array_keys($Tk)).") VALUES (".implode(", ",$Tk).")")))return
false;}return
true;}function
slowQuery($F,$Cn){$this->connection->query("SET statement_timeout = ".(1000*$Cn));$this->connection->timeout=1000*$Cn;return$F;}function
convertSearch($t,array$Z,array$l){if(preg_match('~\[~',$l["full_type"]))return($this->isArrayComparison($Z)?$t:"CAST($t AS text)");if(preg_match('~range$~',$l["type"]))return(preg_match('~^(list(<>) =?|!?=)$~',$Z["op"])&&preg_match('~^\s*(empty|[[(].*,.*[])])\s*$~i',$Z["val"])?$t:"CAST($t AS text)");$xn="char|text";if(strpos($Z["op"],"LIKE")===false&&strpos($Z["op"],"~")===false)$xn
.="|date|time(stamp)?|boolean|uuid|inet|cidr|macaddr|".number_type();return(preg_match("~$xn~",$l["type"])?$t:"CAST($t AS text)");}function
isSearchable(array$Z,array$l){if(strpos($Z["op"],"LIKE")!==false||strpos($Z["op"],"~")!==false)return
true;if(preg_match('~\[~',$l["full_type"])){if(!$this->isArrayComparison($Z))return
true;if(!preg_match('~^\s*\{([^{}"]*)\}\s*$~',$Z["val"],$_))return
false;foreach(($_[1]!=""?explode(",",$_[1]):[])as$Kd){if(!preg_match('~^\s*null\s*$~i',$Kd)&&!$this->isValidValue($l["type"],trim($Kd)))return
false;}return
true;}$Y=[$Z["val"]];if(preg_match('~IN$~',$Z["op"])){preg_match_all("~".self::$EnumLengthPattern."|[^,()]+~",process_length($Z["val"]),$A);$Y=array_map(function($X){return
str_replace("''","'",preg_replace("~^'(.*)'$~s",'\\1',$X));},$A[0]);}foreach($Y
as$X){if(!$this->isValidValue($l["type"],$X))return
false;}return
true;}private
function
isArrayComparison(array$Z){return
preg_match('~^(list(<>) =?|!?=)$~',$Z["op"])&&preg_match('~^\s*\{.*\}\s*$~s',$Z["val"]);}private
function
isValidValue($T,$X){if(preg_match('~^'.int_type().'$~',$T))return(bool)preg_match('~^\s*[-+]?\d+\s*$~',$X);if(preg_match(number_type(),$T))return
is_numeric(trim($X));switch($T){case"boolean":return(bool)preg_match('~^\s*(t(r(ue?)?)?|f(a(l(se?)?)?)?|y(es?)?|no?|on|off?|1|0)\s*$~i',$X);case"uuid":return(bool)preg_match('~^(\{[0-9a-f]{4}(-?[0-9a-f]{4}){7}\}|[0-9a-f]{4}(-?[0-9a-f]{4}){7})$~i',$X);case"inet":case"cidr":if(!preg_match('~^([^/]+)(/(\d+))?$~',$X,$_)||!filter_var($_[1],FILTER_VALIDATE_IP))return
false;$Ba=inet_pton($_[1]);$x=strlen($Ba)*8;$Fh=isset($_[3])?(int)$_[3]:$x;if($Fh>$x)return
false;$jb=implode("",array_map(function($sb){return
str_pad(decbin(ord($sb)),8,"0",STR_PAD_LEFT);},str_split($Ba)));return$T!="cidr"||strpos(substr($jb,$Fh),"1")===false;case"macaddr":return(bool)preg_match('~^\s*([0-9a-f]{1,2}(:[0-9a-f]{1,2}){5}|[0-9a-f]{1,2}(-[0-9a-f]{1,2}){5}|[0-9a-f]{6}[:-][0-9a-f]{6}|'.'[0-9a-f]{4}([.-])[0-9a-f]{4}\4[0-9a-f]{4}|[0-9a-f]{12})\s*$~i',$X);case"macaddr8":$Tl=preg_replace('~[\s0-9a-f]~i','',$X);return
preg_match('~^\s*[0-9a-f]{2}([:.-]?[0-9a-f]{2})+\s*$~i',$X)&&strlen(count_chars($Tl,3))<=1&&in_array(strlen(preg_replace('~[^0-9a-f]~i','',$X)),[12,16]);case"time":case"time without time zone":case"time with time zone":return(bool)preg_match('~^\s*(([01]?\d|2[0-3]):[0-5]\d(:[0-5]\d(\.\d+)?)?|([01]\d|2[0-3])[0-5]\d([0-5]\d(\.\d+)?)?|24:00(:00)?|'.'(0?[1-9]|1[0-2]):[0-5]\d(:[0-5]\d(\.\d+)?)?\s*[ap]m)(\s*(z|utc|[+-](0?\d|1[0-5])(:?[0-5]\d)?))?\s*$~i',$X);}return
true;}function
quoteBinary($O){return"'\\x".bin2hex($O)."'";}function
warnings(){return$this->connection->warnings();}function
tableHelp($B,$_g=false){$th=["information_schema"=>"infoschema","pg_catalog"=>($_g?"view":"catalog"),];$z=$th[$_GET["ns"]];if($z)return"$z-".str_replace("_","-",$B).".html";return
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
getPartitionsInfo($P){if(!$this->connection->isMinVersion("10"))return[];$I=$this->connection->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($P))->fetchAssoc();if(!$I)return[];$Va=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = {$I["partrelid"]} AND attnum IN (".str_replace(" ",", ",$I["partattrs"]).")");$rb=['h'=>'HASH','l'=>'LIST','r'=>'RANGE'];$eg=["partition_by"=>$rb[$I["partstrat"]],"partition"=>implode(", ",array_map('AdminNeo\idf_escape',$Va)),];$Yj=get_key_vals("SELECT relname, pg_get_expr(c.relpartbound, c.oid) AS bound
FROM pg_inherits
JOIN pg_class c ON inhrelid = oid
WHERE inhparent = ".$this->tableOid($P)." ORDER BY 1");$eg["partition_names"]=array_keys($Yj);$eg["partition_values"]=array_map(function($X){return
str_replace("FOR VALUES ","",$X);},array_values($Yj));return$eg;}function
tableOid($P){return"(SELECT oid FROM pg_class WHERE relnamespace = ".$this->getNsOidSql()." AND relname = ".q($P)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
getIndexAlgorithms(array$bn){static$fi=[];if(!$fi)$fi=get_vals("SELECT amname FROM pg_am".($this->connection->isMinVersion("9.6")?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->connection->isCockroachDB()?"prefix":"btree")."' DESC, amname");return$fi;}function
getIndexOpclasses(){static$kj=[];if(!$kj&&!$this->connection->isCockroachDB())$kj=get_vals("SELECT DISTINCT opcname FROM pg_catalog.pg_opclass WHERE NOT opcdefault ORDER BY opcname");return$kj;}function
supportsIndex(array$bn){return$bn["Engine"]!="view";}function
hasCStyleEscapes(){static$tb;if($tb===null)$tb=($this->connection->getValue("SHOW standard_conforming_strings")=="off");return$tb;}function
explodeArrayValue($X,$T,&$Al){$Do=["oidvector"=>"oid","int2vector"=>"smallint",];$Al=isset($Do[$T])?$Do[$T]:null;if($Al)return
explode(" ",$X);if(preg_match('~^(.+)\[]$~',$T,$A)){$Al=$A[1];$X=preg_replace('~^\{(.*)}$~',"$1",$X);$wg=str_contains($Al,"json");if($wg){$Y=explode('","',trim($X,'"'));array_walk($Y,function(&$V){$V=str_replace('\"','"',$V);});}elseif(preg_match('~^(\{+)(.*)(}+)$~',$X,$A)){$Y=explode("$A[3],$A[1]",$A[2]);array_walk($Y,function(&$V)use($A){$V=$A[1].$V.$A[3];});}else$Y=explode(",",$X);if(!$wg&&$Y[0][0]=="{"){$T=$Al;array_walk($Y,function(&$V)use($T,&$Al){$V=$this->explodeArrayValue($V,$T,$Al);});}elseif(!$wg&&isset($this->types[lang(128)][$Al])){array_walk($Y,function(&$V){$V=preg_replace('~^\'(.*)\'$~',"$1",$V);});}return$Y;}return[];}function
implodeArrayValues(array$Y,$T){if($T=="oidvector"||$T=="int2vector")return
implode(" ",$Y);if(preg_match('~^([^[]+)(\[])+$~',$T,$A)){$zg=isset($this->types[lang(128)][$A[1]]);$wg=str_contains($T,"json");array_walk($Y,function(&$V)use($T,$zg,$wg){if(is_array($V))$V=$this->implodeArrayValues($V,$T);elseif($wg)$V="\"$V\"";elseif($zg)$V="'$V'";});return"{".implode(",",$Y)."}";}return"";}}function
create_driver(Connection$e){return
PgSqlDriver::create($e,Admin::get());}function
idf_escape($t){return'"'.str_replace('"','""',$t).'"';}function
table($t){return
idf_escape($t);}function
connect($_k=false,&$k=null){$e=$_k?PgSqlConnection::create():PgSqlConnection::createSecondary();list($L,$U,$E)=Admin::get()->getCredentials();$G=$e->openPasswordless($L,$U,$E,false);$Xd=[];if(!$G&&$L==""&&preg_match('~connection to server on socket .+ failed: No such file or directory~U',$e->getError())){$Xd[]=$e->getError();$L="localhost";$G=$e->openPasswordless($L,$U,$E,false);}if(!$G){$Xd[]=$e->getError();$k=implode("\n",$Xd);return
null;}if($e->isMinVersion("9"))$e->query("SET application_name = 'AdminNeo'");if($_k&&$e->isCockroachDB()){Drivers::setName(DRIVER,"CockroachDB");save_driver_name(DRIVER,$L,"CockroachDB");}return$e;}function
get_databases($Ne){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($F,$Z,$y,$Si=0,$Sl=" "){return" $F$Z".($y?$Sl."LIMIT $y".($Si?" OFFSET $Si":""):"");}function
limit1($P,$F,$Z,$Sl="\n"){return(preg_match('~^INTO~',$F)?limit($F,$Z,1,0,$Sl):" $F".(is_view(table_status1($P))?$Z:$Sl."WHERE ctid = (SELECT ctid FROM ".table($P).$Z.$Sl."LIMIT 1)"));}function
db_collation($i,array$Rb){return
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
table_status($B="",$se=false){static$yf;if($yf===null)$yf=Connection::get()->getValue("SELECT 'pg_table_size'::regproc");$H=[];foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\",
	CASE relkind WHEN 'p' THEN 'partitioned' ELSE '' END AS \"Create_options\"".($yf?",
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
fields($P){$H=[];$Ga=['timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',];foreach(get_rows("SELECT
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
ORDER BY a.attnum")as$I){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$I["full_type"],$_);list(,$T,$x,$I["length"],$Aa,$Ra)=$_;$Eb=$T.$Aa;if(isset($Ga[$Eb])){$I["type"]=$Ga[$Eb];$I["full_type"]=$I["type"].$x;}else{$I["type"]=$T;$I["full_type"]=$I["type"].$x.$Aa;}for($r=0;$r<$I["attndims"];$r++){$I["length"].=$Ra;$I["full_type"].=$Ra;}if(in_array($I['attidentity'],['a','d']))$I['default']='GENERATED '.($I['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$C=["s"=>"STORED","v"=>"VIRTUAL"];$I["generated"]=(isset($C[$I["attgenerated"]])?$C[$I["attgenerated"]]:"");$I["null"]=!$I["attnotnull"];$I["auto_increment"]=preg_match('~^nextval\(~i',$I["default"])||preg_match('~^unique_rowid\(~',$I["default"]);$I["privileges"]=["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1];if(!$I['generated']&&preg_match('~(.+)::[^,)]+(.*)~s',$I["default"],$_))$I["default"]=($_[1]=="NULL"?null:idf_unescape($_[1]).$_[2]);$H[$I["field"]]=$I;}return$H;}function
indexes($P,$e=null){if(!is_object($e))$e=Connection::get();$H=[];$gn=Driver::get()->tableOid($P);$d=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $gn AND attnum > 0",$e);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname,
	pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr".($e->isCockroachDB()?"":",
	(SELECT string_agg(CASE WHEN opcdefault THEN '' ELSE opcname END, ' ' ORDER BY s)
		FROM generate_subscripts(indclass, 1) AS s
		JOIN pg_catalog.pg_opclass ON pg_opclass.oid = indclass[s]) AS opclasses")."
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $gn
ORDER BY indisprimary DESC, indisunique DESC",$e)as$I){$el=$I["relname"];$H[$el]["type"]=($I["indisprimary"]?"PRIMARY":($I["indisunique"]?"UNIQUE":"INDEX"));$H[$el]["columns"]=[];$H[$el]["descs"]=[];$H[$el]["algorithm"]=$I["amname"];$H[$el]["partial"]=$I["partial"];$bg=preg_split('~(?<=\)), (?=\()~',$I["indexpr"]);foreach(explode(" ",$I["indkey"])as$cg)$H[$el]["columns"][]=($cg?$d[$cg]:array_shift($bg));foreach(explode(" ",$I["indoption"])as$dg)$H[$el]["descs"][]=(intval($dg)&1?'1':null);$H[$el]["opclasses"]=(isset($I["opclasses"])?explode(" ",$I["opclasses"]):[]);$H[$el]["lengths"]=[];}return$H;}function
foreign_keys($P){$ej=implode("|",Driver::get()->getOnActions());$H=[];foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, condeferred::int AS deferred, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".Driver::get()->tableOid($P)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$I){$I['deferrable']=($I['deferrable']?'':'NOT ').'DEFERRABLE'.($I['deferred']?' INITIALLY DEFERRED':'');if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$I['definition'],$_)){$I['source']=array_map('AdminNeo\idf_unescape',array_map('trim',explode(',',$_[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$_[2],$Gh)){$I['ns']=idf_unescape($Gh[2]);$I['table']=idf_unescape($Gh[4]);}$I['target']=array_map('AdminNeo\idf_unescape',array_map('trim',explode(',',$_[3])));$I['on_delete']=(preg_match("~ON DELETE ($ej)~",$_[4],$Gh)?$Gh[1]:'NO ACTION');$I['on_update']=(preg_match("~ON UPDATE ($ej)~",$_[4],$Gh)?$Gh[1]:'NO ACTION');$H[$I['conname']]=$I;}}return$H;}function
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
information_schema($i,$El=""){return
in_array($El!=""?$El:get_schema(),["information_schema","pg_catalog","pg_toast"]);}function
error(){$H=h(Connection::get()->getError());if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$H,$_))$H=$_[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($_[3]).'})(.*)~','\1<b>\2</b>',$_[2]).$_[4];return
nl2br($H);}function
create_database($i,$Qb){return(bool)queries("CREATE DATABASE ".idf_escape($i).($Qb?" ENCODING ".idf_escape($Qb):""));}function
drop_databases(array$h){Connection::get()->close();return
apply_queries("DROP DATABASE",$h,'AdminNeo\idf_escape');}function
rename_database($B,$Qb){Connection::get()->close();return(bool)queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($B));}function
auto_increment(){return"";}function
alter_table($P,$B,array$m,array$Pe,$ac,$Qd,$Qb,$Ya,$Xj){$b=[];$Kk=[];if($P!=""&&$P!=$B)$Kk[]="ALTER TABLE ".table($P)." RENAME TO ".table($B);$Ul="";foreach($m
as$l){$c=idf_escape($l[0]);$W=$l[1];if(!$W)$b[]="DROP $c";else{$Ao=$W[5];unset($W[5]);if($l[0]==""){if(isset($W[6]))$W[1]=($W[1]==" bigint"?" big":($W[1]==" smallint"?" small":" "))."serial";$b[]=($P!=""?"ADD ":"  ").implode($W);if(isset($W[6]))$b[]=($P!=""?"ADD":" ")." PRIMARY KEY ($W[0])";}else{if($c!=$W[0])$Kk[]="ALTER TABLE ".table($B)." RENAME $c TO $W[0]";$b[]="ALTER $c TYPE$W[1]";$Vl=$P."_".idf_unescape($W[0])."_seq";$b[]="ALTER $c ".($W[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) (STORED|VIRTUAL)~','EXPRESSION\1',$W[3]):(isset($W[6])?"SET DEFAULT nextval(".q($Vl).")":"DROP DEFAULT"));if(isset($W[6]))$Ul="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($Vl)." OWNED BY ".idf_escape($P).".$W[0]";$b[]="ALTER $c ".($W[2]==" NULL"?"DROP NOT":"SET").$W[2];}if($l[0]!=""||$Ao!="")$Kk[]="COMMENT ON COLUMN ".table($B).".$W[0] IS ".($Ao!=""?substr($Ao,9):"''");}}$b=array_merge($b,$Pe);if($P==""){$N="";if($Xj){$N=" PARTITION BY {$Xj["partition_by"]}({$Xj["partition"]})";if($Xj["partition_by"]=='HASH'){$Yj=(int)$Xj["partitions"];for($r=0;$r<$Yj;$r++)$Kk[]="CREATE TABLE ".idf_escape($B."_$r")." PARTITION OF ".idf_escape($B)." FOR VALUES WITH (MODULUS $Yj, REMAINDER $r)";}else{$Mb=Connection::get()->isCockroachDB();$zk="MINVALUE";foreach($Xj["partition_names"]as$r=>$W){$X=$Xj["partition_values"][$r];$Sj=" VALUES ".($Xj["partition_by"]=='LIST'?"IN ($X)":"FROM ($zk) TO ($X)");if($Mb)$N
.=($r?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$W)?$W:idf_escape($W)).$Sj;else$Kk[]="CREATE TABLE ".idf_escape($B."_$W")." PARTITION OF ".idf_escape($B)." FOR$Sj";$zk=$X;}$N
.=$Mb?"\n)":"";}}array_unshift($Kk,"CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$N");}elseif($b)array_unshift($Kk,"ALTER TABLE ".table($P)."\n".implode(",\n",$b));if($Ul)array_unshift($Kk,$Ul);if($ac!==null)$Kk[]="COMMENT ON TABLE ".table($B)." IS ".q($ac);if($Ya!="");foreach($Kk
as$F){if(!queries($F))return
false;}return
true;}function
alter_indexes($P,array$b){$f=[];$ud=[];$Kk=[];foreach($b
as$W){if($W[0]!="INDEX")$f[]=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");elseif($W[2]=="DROP")$ud[]=idf_escape($W[1]);else$Kk[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($P."_"))." ON ".table($P).($W[3]?" USING $W[3]":"")." (".implode(", ",$W[2]).")".($W[4]?" WHERE $W[4]":"");}if($f)array_unshift($Kk,"ALTER TABLE ".table($P).implode(",",$f));if($ud)array_unshift($Kk,"DROP INDEX ".implode(", ",$ud));foreach($Kk
as$F){if(!queries($F))return
false;}return
true;}function
truncate_tables(array$R){return(bool)queries("TRUNCATE ".implode(", ",array_map('AdminNeo\table',$R)));}function
drop_views(array$Jo){return
drop_tables($Jo);}function
drop_tables(array$R){foreach($R
as$P){$N=table_status1($P);if(!queries("DROP ".strtoupper($N["Engine"])." ".table($P)))return
false;}return
true;}function
move_tables(array$R,array$Jo,$rn){foreach(array_merge($R,$Jo)as$P){$N=table_status1($P);if(!queries("ALTER ".strtoupper($N["Engine"])." ".table($P)." SET SCHEMA ".idf_escape($rn)))return
false;}return
true;}function
trigger($B,$P){if($B=="")return["Statement"=>"EXECUTE PROCEDURE ()"];$d=[];$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($P)." AND trigger_name = ".q($B);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$I)$d[]=$I["event_object_column"];$Sn=[];foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event",
	\'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers '."$Z ORDER BY event_manipulation DESC")as$I){if($d&&$I["Event"]=="UPDATE")$I["Event"].=" OF";$I["Of"]=implode(", ",$d);if($Sn)$I["Event"].=" OR $Sn[Event]";$Sn=$I;}return$Sn;}function
triggers($P){$H=[];foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($P))as$I){$Sn=trigger($I["trigger_name"],$P);$H[$Sn["Trigger"]]=[$Sn["Timing"],$Sn["Event"]];}return$H;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER"],"Event"=>["INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",],"Type"=>["FOR EACH ROW","FOR EACH STATEMENT"],];}function
routine($B,$T){$eg=get_rows('SELECT routine_definition, LOWER(external_language) AS language, type_udt_name
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($B));$eg=isset($eg[0])?$eg[0]:[];$m=get_rows("SELECT COALESCE(parameter_name, ordinal_position::text) AS field,
	CASE data_type WHEN 'USER-DEFINED' THEN udt_name WHEN 'ARRAY' THEN substr(udt_name, 2) || '[]' ELSE data_type END AS type,
	character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = ".q($B)."
ORDER BY ordinal_position");return["fields"=>$m,"returns"=>["type"=>preg_replace('~^_(.*)~','\1[]',isset($eg["type_udt_name"])?$eg["type_udt_name"]:"")],"definition"=>isset($eg["routine_definition"])?$eg["routine_definition"]:null,"language"=>isset($eg["language"])?$eg["language"]:null,"comment"=>null,];}function
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
found_rows(array$Q,array$Z){if(preg_match("~ rows=([0-9]+)~",Connection::get()->getValue("EXPLAIN SELECT * FROM ".idf_escape($Q["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$dl))return(int)$dl[1];return
null;}function
types($ne=false){$Mb=Connection::get()->isCockroachDB();$Vg=($Mb?"'e'":"'b','c','d','e'".(Connection::get()->isMinVersion("9.2")?",'r'":""));return
get_key_vals("SELECT t.oid, t.typname
FROM pg_type t
WHERE t.typnamespace = ".Driver::get()->getNsOidSql()."
AND t.typtype IN ($Vg)".($Mb?"
AND t.typelem = 0":"
AND (t.typrelid = 0 OR (SELECT c.relkind FROM pg_class c WHERE c.oid = t.typrelid) = 'c')"."
AND NOT EXISTS (SELECT 1 FROM pg_type e WHERE e.typarray = t.oid)".($ne?'':"
AND t.oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_type'::regclass AND deptype = 'e')"))."
ORDER BY t.typname");}function
type_values($s){$Ud=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $s ORDER BY enumsortorder");if(!$Ud)return"";$Y=[];foreach($Ud
as$Sd)$Y[]="'".str_replace("'","''",addcslashes($Sd,"\\"))."'";return
implode(", ",$Y);}function
collation_name($Ti){return(Connection::get()->isMinVersion("9.1")?"(SELECT collname FROM pg_collation WHERE oid = $Ti AND collname != 'default')":"NULL");}function
type_definition($s){$T=first(get_rows("SELECT typtype, typisdefined::int AS defined, typrelid FROM pg_type WHERE oid = $s"));$H=["kind"=>($T?$T["typtype"]:""),"definition"=>""];if(!$T||!$T["defined"])return$H;switch($H["kind"]){case'e':$Y=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $s ORDER BY enumsortorder");$H["definition"]="AS ENUM (".implode(", ",array_map('AdminNeo\q',$Y)).")";break;case'c':$d=[];$J=get_rows("SELECT attname, format_type(atttypid, atttypmod) AS full_type, ".collation_name("attcollation")." AS collation
FROM pg_attribute
WHERE attrelid = $T[typrelid] AND attnum > 0 AND NOT attisdropped
ORDER BY attnum");foreach($J
as$I)$d[]=idf_escape($I["attname"])." $I[full_type]".($I["collation"]?" COLLATE ".idf_escape($I["collation"]):"");$H["definition"]="AS (\n\t".implode(",\n\t",$d)."\n)";break;case'd':$pd=first(get_rows("SELECT format_type(typbasetype, typtypmod) AS base, typnotnull::int AS notnull, typdefault, ".collation_name("typcollation")." AS collation
FROM pg_type
WHERE oid = $s"));$H["definition"]="AS $pd[base]".($pd["collation"]?" COLLATE ".idf_escape($pd["collation"]):"").($pd["typdefault"]!=""?" DEFAULT $pd[typdefault]":"").($pd["notnull"]?" NOT NULL":"");$J=get_rows("SELECT conname, pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE contypid = $s AND contype != 'n' ORDER BY conname");foreach($J
as$I)$H["definition"].=" CONSTRAINT ".idf_escape($I["conname"])." $I[definition]";break;case'r':$Rk=first(get_rows("SELECT format_type(rngsubtype, NULL) AS subtype,
	(SELECT opcname FROM pg_opclass WHERE oid = rngsubopc) AS subtype_opclass,
	".collation_name("rngcollation")." AS collation,
	NULLIF(rngcanonical, 0)::regproc::text AS canonical,
	NULLIF(rngsubdiff, 0)::regproc::text AS subtype_diff".(Connection::get()->isMinVersion("14")?",
	(SELECT typname FROM pg_type WHERE oid = rngmultitypid) AS multirange_type_name":"")."
FROM pg_range
WHERE rngtypid = $s"));$C=[];foreach(["subtype"=>0,"subtype_opclass"=>1,"collation"=>1,"canonical"=>0,"subtype_diff"=>0,"multirange_type_name"=>1]as$w=>$Yd){if((isset($Rk[$w])?$Rk[$w]:"")!="")$C[]=strtoupper($w)." = ".($Yd?idf_escape($Rk[$w]):$Rk[$w]);}$H["definition"]="AS RANGE (".implode(", ",$C).")";break;}return$H;}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return(string)Connection::get()->getValue("SELECT current_schema()");}function
set_schema($El,$e=null){if(!$e)$e=Connection::get();$G=(bool)$e->query("SET search_path TO ".idf_escape($El));Driver::get()->setUserTypes(types(true));return$G;}function
foreign_keys_sql($P){$H="";$N=table_status1($P);$Ki=idf_escape($N['nspname']);$Ke=foreign_keys($P);ksort($Ke);foreach($Ke
as$Je=>$Ie)$H
.="ALTER TABLE ONLY $Ki.".idf_escape($N['Name'])." ADD CONSTRAINT ".idf_escape($Je)." ".preg_replace('~( REFERENCES )([^(.]+\()~',"\\1$Ki.\\2",$Ie["definition"]).";\n";return($H?"$H\n":$H);}function
foreign_key_checks_sql($Nd){return"SET session_replication_role = ".($Nd?"DEFAULT":"replica").";\n";}function
restart_sequences_sql($P){$H="";foreach(fields($P)as$l){if(!$l["auto_increment"]||preg_match('~^unique_rowid\(~',$l["default"]))continue;$Ul="pg_get_serial_sequence(".q(table($P)).", ".q($l["field"]).")";$c=idf_escape($l["field"]);$H
.="SELECT setval($Ul, MAX($c)) FROM ".table($P)." HAVING MAX($c) IS NOT NULL;\n";}return$H;}function
create_sql($P,$Ya,$Mm){$nl=[];$Wl=[];$N=table_status1($P);$Ki=idf_escape($N['nspname']);if(is_view($N)){$Ho=view($P);return
rtrim("CREATE VIEW $Ki.".idf_escape($P)." AS $Ho[select]",";");}$m=fields($P);if(count($N)<2||empty($m))return"";$H="CREATE TABLE $Ki.".idf_escape($N['Name'])." (\n    ";foreach($m
as$l){if($l['default']=="nextval('$N[Name]_$l[field]_seq')"){$l['default']=null;$l['full_type']=preg_replace('~int(eger)?~','serial',$l['full_type']);}$Qj=idf_escape($l['field']).' '.$l['full_type'].preg_replace('~(nextval\(\')([^.\']+\')~','\1'.str_replace("'","''",$N['nspname']).'.\2',default_value($l)).($l['null']?"":" NOT NULL");$nl[]=$Qj;if(preg_match('~nextval\(\'([^\']+)\'\)~',$l['default'],$A)){$Vl=$A[1];$J=get_rows((Connection::get()->isMinVersion("10")?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($Vl)):"SELECT * FROM $Vl"),null,"-- ");if($J){$wm=first($J);$Wl[]=($Mm=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $Ki.$Vl;\n":"")."CREATE SEQUENCE $Ki.$Vl INCREMENT $wm[increment_by] MINVALUE $wm[min_value] MAXVALUE $wm[max_value]".($Ya&&$wm['last_value']?" START ".($wm["last_value"]+1):"")." CACHE $wm[cache_value];";}}}if(!empty($Wl))$H=implode("\n\n",$Wl)."\n\n$H";$_k="";foreach(indexes($P)as$Zf=>$u){if($u['type']=='PRIMARY'){$_k=$Zf;$nl[]="CONSTRAINT ".idf_escape($Zf)." PRIMARY KEY (".implode(', ',array_map('AdminNeo\idf_escape',$u['columns'])).")";}}foreach(Driver::get()->checkConstraints($P)as$ic=>$mc)$nl[]="CONSTRAINT ".idf_escape($ic)." CHECK ($mc)";$H
.=implode(",\n    ",$nl)."\n)";$Sj=Driver::get()->getPartitionsInfo($N['Name']);if($Sj)$H
.="\nPARTITION BY {$Sj["partition_by"]}({$Sj["partition"]})";$H
.="\nWITH (oids = ".($N['Oid']?'true':'false').");";if($N['Comment'])$H
.="\n\nCOMMENT ON TABLE $Ki.".idf_escape($N['Name'])." IS ".q($N['Comment']).";";foreach($m
as$xe=>$l){if($l['comment'])$H
.="\n\nCOMMENT ON COLUMN $Ki.".idf_escape($N['Name']).".".idf_escape($xe)." IS ".q($l['comment']).";";}foreach(get_rows("SELECT indexdef FROM pg_catalog.pg_indexes WHERE schemaname = current_schema() AND tablename = ".q($P).($_k?" AND indexname != ".q($_k):""),null,"-- ")as$I)$H
.="\n\n$I[indexdef];";return
rtrim($H,';');}function
truncate_sql($P){return"TRUNCATE ".table($P);}function
trigger_sql($P){$N=table_status1($P);$xm="";foreach(triggers($P)as$Rn=>$Qn){$Sn=trigger($Rn,$N['Name']);$xm
.="\nCREATE TRIGGER ".idf_escape($Sn['Trigger'])." $Sn[Timing] $Sn[Event] ON ".idf_escape($N["nspname"]).".".idf_escape($N['Name'])." $Sn[Type] $Sn[Statement];;\n";}return$xm;}function
create_database_sql($Jc,$Mm=""){$B=idf_escape($Jc);$Yb="";if(str_contains($Mm,"CREATE")){if($Mm=="DROP+CREATE")$Yb="DROP DATABASE IF EXISTS $B;\n";$Yb
.="CREATE DATABASE $B;\n";}return$Yb;}function
use_sql($Jc,$Mm=""){return'\connect '.idf_escape($Jc).";\n";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(Connection::get()->isMinVersion("9.2")?"pid":"procpid"));}function
convert_field(array$l){return
null;}function
unconvert_field(array$l,$H){return$H;}function
support($te){if($te=="processlist")return!Connection::get()->isCockroachDB();elseif($te=="materializedview")return
Connection::get()->isMinVersion("9.3");elseif($te=="procedure")return
Connection::get()->isMinVersion("11");return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|fast_status|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table|transaction_ddl|trigger|type|variables|view)$~',$te);}function
kill_process($W){return
queries("SELECT pg_terminate_backend(".number($W).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return(int)Connection::get()->getValue("SHOW max_connections");}}Drivers::add("sqlite","SQLite",["SQLite3","PDO_SQLite"]);if(isset($_GET["sqlite"])){define("AdminNeo\DRIVER","sqlite");define("AdminNeo\DIALECT","sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","SQLite3");abstract
class
SqLiteConnectionBase
extends
Connection{private$sqlite;function
open($L,$U,$E){$this->sqlite=new
SQLite3($L);if(method_exists($this->sqlite,"setAuthorizer"))$this->sqlite->setAuthorizer([$this,"authorize"]);$this->version=$this->sqlite->version()["versionString"];return
true;}function
query($F,$co=false){$G=@$this->sqlite->query($F);$this->error="";if(!$G){$this->errno=$this->sqlite->lastErrorCode();$this->error=$this->sqlite->lastErrorMsg();return
false;}elseif($G->numColumns())return
new
SqLiteResult($G);$this->affectedRows=$this->sqlite->changes();return
true;}function
quote($O){if(is_utf8($O))return"'".$this->sqlite->escapeString($O)."'";else
return"x'".bin2hex($O)."'";}}class
SqLiteResult
extends
Result{private$resource;private$offset=0;function
__construct(SQLite3Result$jl){parent::__construct(0);$this->resource=$jl;}function
fetchAssoc(){return$this->resource->fetchArray(SQLITE3_ASSOC);}function
fetchRow(){return$this->resource->fetchArray(SQLITE3_NUM);}function
fetchField(){$bo=[1=>"integer","real","text","blob","null"];$c=$this->offset++;$T=$this->resource->columnType($c);if($T===false)return
false;return(object)["name"=>$this->resource->columnName($c),"native_type"=>$bo[$T],"type"=>($T==SQLITE3_TEXT?15:0),"charsetnr"=>($T==SQLITE3_BLOB?63:0),];}}}elseif(extension_loaded("pdo_sqlite")){define("AdminNeo\DRIVER_EXTENSION","PDO_SQLite");abstract
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
open($L,$U,$E){if(!parent::open($L,$U,$E))return
false;$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return
true;}function
close(){$this->open(":memory:","","");}function
selectDatabase($B){$this->attaching=true;$Ta=is_readable($B)&&$this->query("ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$B)?$B:dirname($_SERVER["SCRIPT_FILENAME"])."/$B")." AS a");$this->attaching=false;if($Ta)return
self::open($B,"","");return
false;}function
authorize($wa,$Pa){return($wa!=24||$Pa===""||$this->attaching?0:1);}}}class
SqliteDriver
extends
Driver{protected
function
__construct(Connection$e,$Ca){parent::__construct($e,$Ca);$this->types=[["integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0,]];if($e->isMinVersion("3.31"))$this->generated=["STORED","VIRTUAL"];if($e->isMinVersion("3.37"))$this->types[0]["any"]=0;$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","IS NULL","IS NOT NULL","SQL",];$this->functions=["length","lower","upper","round","hex","unixepoch",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->insertFunctions=[];$this->editFunctions=["integer|real|numeric"=>"+/-","text"=>"||",];}function
getStructuredTypes(){return
parent::getStructuredTypes()[0];}function
quoteBinary($O){return"x".q(bin2hex($O));}function
engines(){$H=["table"];if(Connection::get()->isMinVersion("3.8.2")){if(Connection::get()->isMinVersion("3.37")){$H[]="STRICT";$H[]="STRICT, WITHOUT ROWID";}$H[]="WITHOUT ROWID";}return$H;}function
insertUpdate($P,array$Uk,array$_k){$Y=[];foreach($Uk
as$Tk)$Y[]="(".implode(", ",$Tk).")";return
queries("REPLACE INTO ".table($P)." (".implode(", ",array_keys(reset($Uk))).") VALUES\n".implode(",\n",$Y));}function
tableHelp($B,$_g=false){if(preg_match('~^sqlite_(seq|stat.)~',$B,$_))return"fileformat2.html#$_[1]tab";if(preg_match('~^sqlite(_temp)?_(master|schema)$~',$B))return"schematab.html";return
null;}function
checkConstraints($P){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',$this->connection->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($P)),$A);return
array_combine($A[2],$A[2]);}function
getAllFields(){$Ha=[];foreach(tables_list()as$P=>$T){foreach(fields($P)as$l)$Ha[$P][]=$l;}return$Ha;}}function
create_driver(Connection$e){return
SqliteDriver::create($e,Admin::get());}function
idf_escape($t){return'"'.str_replace('"','""',$t).'"';}function
table($t){return
idf_escape($t);}function
connect($_k=false,&$k=null){$e=$_k?SqLiteConnection::create():SqLiteConnection::createSecondary();$E=Admin::get()->getCredentials()[2];if($E!=""){$G=Admin::get()->verifyDefaultPassword($E);if($G!==true){$k=$G;return
null;}}return$e;}function
get_databases($Ne){return[];}function
limit($F,$Z,$y,$Si=0,$Sl=" "){return" $F$Z".($y?$Sl."LIMIT $y".($Si?" OFFSET $Si":""):"");}function
limit1($P,$F,$Z,$Sl="\n"){return(preg_match('~^INTO~',$F)||Connection::get()->getValue("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($F,$Z,1,0,$Sl):" $F WHERE rowid = (SELECT rowid FROM ".table($P).$Z.$Sl."LIMIT 1)");}function
db_collation($i,array$Rb){return
Connection::get()->getValue("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name LIKE 'sqlite_%'), name");}function
count_tables(array$h){return[];}function
db_status(){$e=Connection::get();$Kj=$e->getValue("PRAGMA page_size");$af=$e->getValue("PRAGMA freelist_count")*$Kj;return["Data_length"=>$e->getValue("PRAGMA page_count")*$Kj-$af,"Index_length"=>0,"Data_free"=>$af,];}function
table_status($B="",$se=false){$H=[];$J=[];if(!$se){if($B=="")Connection::get()->query("PRAGMA optimize = 0x10002");$J=get_key_vals("SELECT tbl, MAX(CAST(stat AS integer)) FROM sqlite_stat1".($B!=""?" WHERE tbl = ".q($B):"")." GROUP BY tbl");}foreach(get_rows("SELECT name AS Name, type AS Engine, sql, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($B!=""?"AND name = ".q($B):"ORDER BY (name LIKE 'sqlite_%'), name"))as$I){if($I["Engine"]=="table"){$Pm=preg_replace('~.*\)~s','',$I["sql"]);$I["Engine"]=implode(", ",array_filter([(preg_match('~\bSTRICT\b~i',$Pm)?"STRICT":""),(preg_match('~\bWITHOUT\s+ROWID\b~i',$Pm)?"WITHOUT ROWID":""),]))?:"table";}unset($I["sql"]);$I["Rows"]=isset($J[$I["Name"]])?$J[$I["Name"]]:0;$H[$I["Name"]]=$I;}if(!$se){foreach(get_rows("SELECT * FROM sqlite_sequence".($B!=""?" WHERE name = ".q($B):""),null,"")as$I)$H[$I["name"]]["Auto_increment"]=$I["seq"];}return$H;}function
is_view(array$Q){return$Q["Engine"]=="view";}function
fk_support(array$Q){return!Connection::get()->getValue("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($P){$H=[];$xm=Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($P));$Ek=["select"=>1,"where"=>1,"order"=>1];if(!preg_match('~^sqlite(_temp)?_(master|schema)$~',$P))$Ek+=["insert"=>1,"update"=>1];foreach(get_rows("PRAGMA table_".(Connection::get()->isMinVersion("3.31")?"x":"")."info(".table($P).")")as$I){$B=$I["name"];$T=strtolower($I["type"]);$j=$I["dflt_value"];$H[$B]=["field"=>$B,"type"=>(preg_match('~int~i',$T)?"integer":(preg_match('~char|clob|text~i',$T)?"text":(preg_match('~blob~i',$T)?"blob":(preg_match('~real|floa|doub~i',$T)?"real":(preg_match('~any~i',$T)?"any":"numeric"))))),"full_type"=>$T,"default"=>(preg_match("~^'(.*)'$~s",$j,$_)?str_replace("''","'",$_[1]):($j=="NULL"?null:$j)),"null"=>!$I["notnull"],"privileges"=>$Ek,"primary"=>$I["pk"],];if($I["pk"]&&preg_match('~\bAUTOINCREMENT\b~i',$xm))$H[$B]["auto_increment"]=true;$Df=isset($I["hidden"])?$I["hidden"]:0;$gf=($Df==2?"VIRTUAL":($Df==3?"STORED":""));$H[$B]["generated"]=$gf;if($gf){$Pk=implode("|",[preg_quote($B,'~'),'"'.preg_quote(str_replace('"','""',$B),'~').'"','`'.preg_quote(str_replace('`','``',$B),'~').'`','\['.preg_quote($B,'~').']',]);if(preg_match('~[(,]\s*(?:'.$Pk.')\s(?:[^,()]|\([^()]*\))*?\bAS\s*(\((?:[^()\'"]++|\'(?:[^\']|\'\')*\'|"(?:[^"]|"")*"|(?1))*\))~i',$xm,$_))$H[$B]["default"]=substr($_[1],1,-1);}}$t='(("[^"]*+")+|[a-z0-9_]+)';preg_match_all('~'.$t.'\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$xm,$A,PREG_SET_ORDER);foreach($A
as$_){$B=str_replace('""','"',preg_replace('~^"|"$~','',$_[1]));if($H[$B])$H[$B]["collation"]=trim($_[3],"'");}return$H;}function
indexes($P,$e=null){if(!is_object($e))$e=Connection::get();$H=[];$xm=$e->getValue("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($P));if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$xm,$_)){$H[""]=["type"=>"PRIMARY","columns"=>[],"lengths"=>[],"descs"=>[]];preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$_[1],$A,PREG_SET_ORDER);foreach($A
as$_){$H[""]["columns"][]=idf_unescape($_[2]).$_[4];$H[""]["descs"][]=(preg_match('~DESC~i',$_[5])?'1':null);}}if(!$H){foreach(fields($P)as$B=>$l){if($l["primary"])$H[""]=["type"=>"PRIMARY","columns"=>[$B],"lengths"=>[],"descs"=>[null]];}}$Bm=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($P),$e);foreach(get_rows("PRAGMA index_list(".table($P).")",$e)as$I){$B=$I["name"];$u=["type"=>($I["unique"]?"UNIQUE":"INDEX")];$u["lengths"]=[];$u["descs"]=[];foreach(get_rows("PRAGMA index_info(".idf_escape($B).")",$e)as$ul){$u["columns"][]=$ul["name"];$u["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($B).' ON '.idf_escape($P),'~').' \((.*)\)$~i',$Bm[$B],$dl)){preg_match_all('/("[^"]*+")+( DESC)?/',$dl[2],$A);foreach($A[2]as$w=>$W){if($W)$u["descs"][$w]='1';}}if(!$H[""]||$u["type"]!="UNIQUE"||$u["columns"]!=$H[""]["columns"]||$u["descs"]!=$H[""]["descs"]||!preg_match("~^sqlite_~",$B))$H[$B]=$u;}return$H;}function
foreign_keys($P){$H=[];foreach(get_rows("PRAGMA foreign_key_list(".table($P).")")as$I){$p=&$H[$I["id"]];if(!$p)$p=$I;$p["source"][]=$I["from"];$p["target"][]=$I["to"];}return$H;}function
backward_keys($P){return[];}function
view($B){return["select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($B)))];}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):[]);}function
information_schema($i,$El=""){return
false;}function
error(){return
h(Connection::get()->getError());}function
check_sqlite_name($B){$ne="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($ne)\$~",$B)){Connection::get()->setError(lang(134,str_replace("|",", ",$ne)));return
false;}return
true;}function
create_database($i,$Qb){if(file_exists($i)){Connection::get()->setError(lang(135));return
false;}if(!check_sqlite_name($i))return
false;try{$z=SqLiteConnection::createSecondary();$z->open($i,"","");}catch(Exception$be){Connection::get()->setError($be->getMessage());return
false;}$z->query('PRAGMA encoding = "UTF-8"');$z->query('CREATE TABLE adminneo (i)');$z->query('DROP TABLE adminneo');return
true;}function
drop_databases(array$h){Connection::get()->close();foreach($h
as$i){if(!check_sqlite_name($i))return
false;if(!@unlink($i)){Connection::get()->setError(lang(135));return
false;}}return
true;}function
rename_database($B,$Qb){if(!check_sqlite_name($B))return
false;Connection::get()->close();Connection::get()->setError(lang(135));return@rename(DB,$B);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($P,$B,array$m,array$Pe,$ac,$Qd,$Qb,$Ya,$Xj){$so=($P==""||$Pe||$Qd);foreach($m
as$l){if($l[0]!=""||!$l[1]||$l[2]){$so=true;break;}}$Ma=[];$Ej=[];foreach($m
as$l){if(!$l[1])continue;if($l[0]!="")$Ej[$l[0]]=$l[1][0];$Ma[]=($so?$l[1]:"ADD ".implode($l[1]));}if(!$so){foreach($Ma
as$W){if(!queries("ALTER TABLE ".table($P)." $W"))return
false;}if($P!=$B&&!queries("ALTER TABLE ".table($P)." RENAME TO ".table($B)))return
false;}elseif(!recreate_table($P,$B,$Ma,$Ej,$Pe,$Ya,[],"","",$Qd))return
false;if($Ya){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $Ya WHERE name = ".q($B));if(!Connection::get()->getAffectedRows())queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($B).", $Ya)");queries("COMMIT");}return
true;}function
recreate_table($P,$B,array$m,array$Ej,array$Pe,$Ya="",array$v=[],$vd="",$_a="",$Qd=""){if($P!=""){if(!$m){foreach(fields($P)as$w=>$l){if($v)$l["auto_increment"]=0;$m[]=process_field($l,$l);$Ej[$w]=idf_escape($w);}}$Ak=false;foreach($m
as$l){if($l[6])$Ak=true;}$xd=[];foreach($v
as$w=>$W){if($W[2]=="DROP"){$xd[$W[1]]=true;unset($v[$w]);}}foreach(indexes($P)as$Qg=>$u){$d=[];foreach($u["columns"]as$w=>$c){if(!isset($Ej[$c]))continue
2;$d[]=$Ej[$c].($u["descs"][$w]?" DESC":"");}if(!$xd[$Qg]){if($u["type"]!="PRIMARY"||!$Ak){$Qg=preg_replace('~^sqlite_~',"",$Qg);$v[]=[$u["type"],$Qg,$d];}}}foreach($v
as$w=>$W){if($W[0]=="PRIMARY"){unset($v[$w]);$Pe[]="  PRIMARY KEY (".implode(", ",$W[2]).")";}}foreach(foreign_keys($P)as$Qg=>$p){foreach($p["source"]as$w=>$c){if(!$Ej[$c])continue
2;$p["source"][$w]=idf_unescape($Ej[$c]);}if(!isset($Pe[" $Qg"]))$Pe[]=" ".format_foreign_key($p);}queries("BEGIN");}$zb=array();foreach($m
as$l){if(preg_match('~GENERATED~',$l[3]))unset($Ej[array_search($l[0],$Ej)]);$zb[]="  ".implode($l);}$zb=array_merge($zb,array_filter($Pe));foreach(Driver::get()->checkConstraints($P)as$Cb){if($Cb!=$vd)$zb[]="  CHECK ($Cb)";}if($_a)$zb[]="  CHECK ($_a)";$tn=($P!=""&&$P==$B?"adminneo_$B":$B);if(!$Qd&&$P!=""){$Q=table_status1($P);$Qd=isset($Q["Engine"])?$Q["Engine"]:null;}if(!queries("CREATE TABLE ".table($tn)." (\n".implode(",\n",$zb)."\n)".($Qd!="table"&&in_array($Qd,Driver::get()->engines())?" $Qd":"")))return
false;if($P!=""){if($Ej&&!queries("INSERT INTO ".table($tn)." (".implode(", ",$Ej).") SELECT ".implode(", ",array_map('AdminNeo\idf_escape',array_keys($Ej)))." FROM ".table($P)))return
false;$Vn=[];foreach(triggers($P)as$Tn=>$Dn){$Sn=trigger($Tn,$P);$Vn[]="CREATE TRIGGER ".idf_escape($Tn)." ".implode(" ",$Dn)." ON ".table($B)."\n$Sn[Statement]";}$Ya=$Ya?"":Connection::get()->getValue("SELECT seq FROM sqlite_sequence WHERE name = ".q($P));if(!queries("DROP TABLE ".table($P))||($P==$B&&!queries("ALTER TABLE ".table($tn)." RENAME TO ".table($B)))||!alter_indexes($B,$v))return
false;if($Ya)queries("UPDATE sqlite_sequence SET seq = $Ya WHERE name = ".q($B));foreach($Vn
as$Sn){if(!queries($Sn))return
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
drop_views(array$Jo){return
apply_queries("DROP VIEW",$Jo);}function
drop_tables(array$R){return
apply_queries("DROP TABLE",$R);}function
move_tables(array$R,array$Jo,$rn){return
false;}function
trigger($B,$P){if($B=="")return["Statement"=>"BEGIN\n\t;\nEND"];$t='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$Un=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$t\\s*(".implode("|",$Un["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($t))?\\s+ON\\s*$t\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($B)),$_);$Ri=$_[3];return["Trigger"=>$B,"Timing"=>strtoupper($_[1]),"Event"=>strtoupper($_[2]).($Ri?" OF":""),"Of"=>idf_unescape($Ri),"Statement"=>$_[4],];}function
triggers($P){$H=[];$Un=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($P))as$I){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$Un["Timing"]).')\s*(.*?)\s+ON\b~i',$I["sql"],$_);$H[$I["name"]]=[$_[1],$_[2]];}return$H;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER","INSTEAD OF"],"Event"=>["INSERT","UPDATE","UPDATE OF","DELETE"],"Type"=>["FOR EACH ROW"],];}function
last_id($G){return
Connection::get()->getValue("SELECT LAST_INSERT_ROWID()");}function
explain(Connection$e,$F){return$e->query("EXPLAIN QUERY PLAN $F");}function
found_rows(array$Q,array$Z){return
null;}function
types(){return[];}function
create_sql($P,$Ya,$Mm){$H=Connection::get()->getValue("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($P));foreach(indexes($P)as$B=>$u){if($B==''||strpos($B,"sqlite_")===0)continue;$H
.=";\n\n".index_sql($P,$u['type'],$B,"(".implode(", ",array_map('AdminNeo\idf_escape',$u['columns'])).")");}return$H;}function
truncate_sql($P){return"DELETE FROM ".table($P);}function
trigger_sql($P){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($P)));}function
show_variables(){$H=[];foreach(get_rows("PRAGMA pragma_list")as$I){$B=$I["name"];if($B!="pragma_list"&&$B!="compile_options"){$H[$B]=[$B,''];foreach(get_rows("PRAGMA $B")as$vl)$H[$B][1].=implode(", ",$vl)."\n";}}return$H;}function
show_status(){$H=[];foreach(get_vals("PRAGMA compile_options")as$oj)$H[]=explode("=",$oj,2)+["",""];return$H;}function
convert_field(array$l){return
null;}function
unconvert_field(array$l,$H){return$H;}function
support($te){return
preg_match('~^(check|columns|database|drop_col|dump|indexes|descidx|move_col|sql|status|table|transaction_ddl|trigger|variables|view|view_trigger)$~',$te);}}$nk="adminneo-plugins";if(is_dir($nk)){foreach(glob("$nk/*.php")as$o)include_once$o;}function
get_compressed_translations($bh){switch($bh){case'_template':return'(X70:6L+^#NBNbej*C-nQG>NYqy"zxVRrd6t6/3!}/ivjl5d{rTeI="]/X(y^uObqs.,Ar@YX,_*5i/):DRS4jY^;W[c$(B])1,u{ygbj2D%Edu($%Sh6><)96J7B"B;N9Y!WQe#]!]"K-#YCg`2v(5*I8LTf(9Kh;YjF!cltV#.m*[_B:p>bSfhHjE:NU)<XHLe&6UEjXw]E%8N8@[L/`A2TA?.|"eC)4X95<eL6W@.:E-YJZ<6%.:3D:Z`V@c[Q7=m)l&DVP5rD;FdaO:VBt[&F5-*FNVgTG$3(#cD60a3.H^FP/-3C.l-ECh/M0TrUfpRBf7jJm^F6$#W:WsF%2c3V+DEwmNAIH6hk6/H)WILBy:6W`ujScO$#MB&]3jM8O}@oX+o"ktgW4pCl&:r6uzxFl+eh^|a
3pbDFj-ZT<*U
dNQ"kBlAAxuN>!o9",?Y1(~n$LN^_K3i{Yn"h>O!Z/lx-.j(M(ARUBt_XfENZpbloC$^Rz!hsaFPIT_4w>v6`Cn4|+")2X*Xsg6FXwOB37nf5sd^J+
^Qp.xQMNJ^p"#)BUoxCgaVgmtsr{)aLz1_/BxqmiHDnD+Pe$0C8="Q9im/LUPdqaMByZP]n#8)imN(t;2=^ktbQ4C=Ur_i>?0|iL"1Gzx`OLCnDNwKHFZ`IB0<U2hdBk1KUiDC"4n:/|>J@am%;B6#TQ
h1cUq92_1`7gD]H+=*"ydBSw-cxf/QHM_yB0c8}anql1:Yz5;U{7@Y&6_bSyqon8fZ+^i<}[}H1[!Fn`D6nLt*D.[$r%0(@PfCbfvDAh1y7$$@L8z2A@1Tz%1P%C>Jpt~dyDc-**E)q&d#BSr`y
ORfY^i,nw3
+L^zR`%,`TZQ&~p:LFaJL{?v
*j/
~j~[&%PD?4VtB!7^18GSrhB/0,P]:fDR&PFD7>*Y9>#
*fyB.D~a`^J%y6LeXg#DN*[UcDz*^P52WQ,<<e-,JDK0j$m+ckr@~^sY|1^<4FGRY3)[-^k
=[7o&,JCW*FNioGZ.@Th!+!D:*RNl^sU]fM:~jNc7l-Kz_BCz_ead6-
wrxFpyd3Cr%QXkEfz#+FPg
ylsjr_A1%TILbvd~@$kv?ZoVkxU7D^2nIWK9>I#n.1qx3YUMA=
9D{TOm75x5ycsdOW$Xwa1_mIuK;vm%9J2>HkF_>QeA-iJw?"b';case'ar':return'+c0KbaPs&B}0
N>$zd.%l?K$GoTa-gvUcdy)c10-c3@3A*Q-.&>IO[EZe(GGG_hv0
gyKw4noUF.^8>hhZaB@xXq[7WyvE8qhora>YBH)n2A]roD!v%t)tTXf1vl9w+W`kxyXXrXZQ9$Fw2t"REYW0KA]Z(%gZnwxuJL6`
]#!/anZ@scbFr[mvgFHN0`&EgmE&
x+$yZi-Bp3*roGLxaj7U`6rDo7Z#xJj9l,^Db06I&66u_w:JMsG(3b:vfX)AFX`9zC,eIEgB3DA7`gmm`V.lZ5Vn?"Cc%S
`PvRxcnejr^@3;l:_eU=$m#OYDd*1#oq2up|.,aXA`GQQuBNhLb)B+,"&Vj7i+(Y)m9ca_PoH4+Xf5;{!:Qj;iV}J8iZT}Wz6GtDy[6
"O155vN+!S8Y]pY%WtnD]0U6qPDo2;hvvU9}yCmLg)d%)NhzePV6MA#Ma?!WKsF}bSyyk3l@L(Z<8<d$$z0kaRP>ID]W(2t
#p-GBQ*_vuPK+VDIk|N2&*^6?+Q_G/
L"vC3(i)YRr:k_$0ri]xD=,7YFX"fvB"3#@
PYsQ8)=,y?NPodT=M$BaTR98hZ9)/;L#Y)q]q$
l*JqqqSU=fj)xUM=a-9_-q,)SMax>1d}N&<j14."hLW[0,?l*q>=Zma5mJFknK7h".SfG9l5l;5^;u,vBj-iYZ4B!4u^/"dU3]i;R!YQSTnx&-c/cG95x`]alC!88t-1^Y`ya_mS@@v6[}(TC<#.<@^XO-cN^
DR+KC.W6(/2*KdsMBB^bQt*[P7afPL9R7%5^U%OD"7uxTFPC-c*G+:*<;MGUH$CGZf0xdO={P$(_W$HP%T(gj~:/$i,N3*3;T(#F$|:ggB%eh7uyaz%M&_
;jm6hYG:R#m1G
3K})`2X[IH]vDpMj=*i"&c<6^VKEbfn!y[D@NeA"XhiNZ=OA{RSO}@Z3p6##F)Q){%Dt4GA@$k%>r4-_epMMhtqt[D%S#Y@VxI]K3RPI+*58Fv)>Brj].PW8B1"o&5wK~E*0"*To?irdUR=kO-m
yLr;nL@V""okBicco[8UnW#?y&n)n6g;(_9GIk2=-,eAmJRhJ:E>~=q.oHDt|K6Fha}]}l2Fo@kOIfo6Ed}ugyK&FD"?i_s*il:nPI5%dVR*+t3_.(Ak.M})-LTu_w**2)qGCm|>ac%fThlgF,,!j+t=y+^CRA9+0wb"Y*S*+]t1EdtBH
a4jlW3;_
.a3sQ#d
[0w+U01I-.vl<^0(-U?.#O-{vPt5D37Ih6!OWKF]#vVJ%fW%Dv#che`*7S/v*t_L<`MiUKM@_|)4&qDBi7cz[$iy492p7Yd)%JT%NsLdILG&V!?G-zvc;Eo<3:Whk:$A!yYMay:du>pb_wY{pO&[3i3yb=C0xThlF{Zj,^dxks4v2X4m_2I)R`4[*WQ[G;qkO)!>>p2fIP7yHJaro{F)XZ$)x]Ea?qm:TA
APjnQ2vW8x+I42yD(q4]p)cYglN.+i!?n$#QBoXdhsKN|=fYuZ|drm?%D*`^;RCmNj3lZ/yp3L?"6?S[OymTcI7Lmh;Kci$a
VzN`UTm.G4J-%sh27aIbQ7%;GwMP.;m7u]FY:oT.CH)[Dx7}G/Ncx)dni-0vIj0$VzDzE%F=VtOL]5oZ,1mQfb:K9D^Mm!dAV,hGq)IpGLNL]}"l513d4E)Ug7$p9"gRL.nf<utoo~U!#gw2G"%b8CW;#Q[u_NsN5RM#`e/@NU]?elpuN75,wAm)U>>7QX;oH9:,H79&U;:ko=.R_XL(MRi.h}WT0
Qx4{YP.#L.m>P"+-*mZVV{(?)XDo=$&Q[-Ca>=`V%UUCOsV]A*H:!5
<`x/Kf&N#G_,#Y]I$>sdpY&p
(xcg:<h*k7]1RP1W3w[Qf}H5)?w)gNBL]3yPmEY6?QAC5k")%fKD-$!/9:;^8CA{NUcc@[K>Jq.S/nxjPN=k]w0z]._08|+-_]NEnj^kpiLKoP]sVsUdZY7)b3TNZr
ERlpUUa4*UjD;QM?gHsikrwPCv[o9UZ>Pq:n?yR1s[Ip<oBrmLWLo7>bR-Dt_z)odytfA:+_X"Z`Ft.sNC7]F:>ij2oEBq<ZOV,Dc@937H;/U?bK*.1:d"-3ME~Bu
MIspNs{s*fT"/<JULudf7x!3ry17O0-l$$#CwUxh.8!]Z3dAwbI2`#D9>Y1EHO+W?Sb5t>TlF!^<)[gN/M&$887TZO3Zt@$qVMDfKns2
p-4Uh|K^*V
Jj3
M@#iMM^j]v:_vjlG_E7DGCQ::=#IxOdK8"
M#mMM<n.dx
kAI;J%$3]_Ks,[_Qg/+
0ezGa.g@vNkZgZA#5?NV*YW"=^/4M/:VvUkB:K3EhPjHIJ1SGthHqCJpj(x2Q748vU`>rl)*f=~p;?|L#
]vBm@tbeu$2Od!ryfDU6Cs0FMtZ6
0GDE*Ol#
s?Q5Ka~KxJyqpE&,{Su^}tAaKOy9-j?mX<v2w@pk_w4(ccW=[yV-{HM]rXK8J]!?(U(rM7T+2d`jL="jHiC5e3?Cy_$IFJ|Q#r_#k(J6=?a9<O]`D7epv_4/iM,Qbx=X~TQRZer-Vo&aML)[o&q2A0](w9|fQna]`7&QPAgADuDYs50+V95IH567JCqn$/_1(3v=Ol
@`?pmw(^ud%lW<c~UBLU,Z`FgwYg1]gz,&P)R1Q)PlrsDr<7hghG"uE7/!U;&-&w1boyA]:ZK6HdnNdN5
E@"Jrwtygg`
gCBmhi3p3_DpG`-])[AO92<@bZrwkEm!Q4u<4Nx;hABNs0Sqn3xd^Z*+9Z@.3ZN
Ik%>*>etR9aF5sv%y>yzxK5floUN%axWmnNsCYve9Nb?v=p1ri<-LL&9!&X4M#q%:J#1&SMlAdSKtQ=@eatR7VwJ[m#0)Im840]9
$o{STgZ`v4R&3RLvh?+J`E=%XLZTJj-S|jO?9NFCzZQa@(`r^Hf+-i%3~
z#^A;o*R$H],Ud@[
`.)*gCp/ZSDCA[yI*/nMB<)+)&[G?LMKlT9mp?!=_E?_;G[RX(Ndj,n2f|:3f>
ZZhUt`!3`
kEcTNS{svpXe=1qQn@Y<0
jdYh^SFGD1O`w9)D}TqRL:ch]](t,q0s;)B^]@Q]_>+BsVhjKK@MU7MScq/]i6TQ!;8ypFf+Pvg(Ul{BcdvVDAGIp5*+g7.MLOcL/Y_;g=:LKs</7)I=/Ua
8A+@+gO0wvwbSqwG8[@4eS&cDOgL:pYi/h2)]mKQ.NpG;!^,:eF@r
0`r%EQeiy%imx/9xm"+9C%C[yDF:3KC6RH]!K*`ZC`&l
oKLDl{W-eP6)+/A_<JOq^vP{v,_)
Z1TwZ5B;
T)l5G(+pK?7#kg!a*+MaUR/9tk8exMnLj`;;oX
,-t!pmHwLgv;":>9r&C;kx(=49m/
k*8}8UsNn"g0%e(@mpW03I;FW#g4&5/^*R.9vqofE:FWqHO!rVqva{%&tP_t5aT]
9IjgyM=.!CeQNC$Orj$7iX_Vdj1!iA@Sp,m3.lu(h$K-ZW=pR^nLr
zkFDw2UAnBWGf8+FlX*O
6yE0%#hE+p)CJmRvKbp})l
mQ5Ct#TCk6A@iR4xaPJC[%[<#;qnDVR3mwVq*1{/[u")+L?oa]Z;5M.jjlYuNM)eNwYb9J&M%;OW3X6SYmoz"ayn;X0
;mG>dxqTN<aP156+Dc?*[7bIN988Seesoh8$QG|(%<iX%C[0`i-86#!(8/.PEr!]?t9e^3w<{bh$<*)x7!K2Nsh)k?_:-W9_m5_/L[(+s40(XVVi/mZ*0AB9<OV1lkP+4G5"Kf_m$N@DXx*-TfA/Ut$R|iXj7=G0?A5/>7SZt
lhk*F`<`SJ[FEQwA60w;eayE1t4
fFA@2DMMNt]:0t+xk>wa"/WAwQt>
C,X:ZU0`

e([S7mip[[7o%;4T@(n!R!f^MeXI
8p`_YRl?p#RjYK!P33_SuD^N[vTj3(97>?C/:,;I~c6DAbu30D/N8:Zn~(ZaT[U^_)_hbixi@*BuAW"nmH
t}tLm,(Kv(Yj+cr8KT04yiD8PCXG2|jie83n4#l7O!j`=/2AbI#p<:o:5<T6t2I/t=QV79Ml[/5:k
M%9
y=Pm?,>yFY&SC8j|<O8[XL..Ia7hYGBSGV2C6r#A3w7;dvRdYOQ3>_kO,xF8;GBTDwTfKnpZ,ZH*oU9q(ZIh.Kjw5|Q|]Hg
6}BfNqb~qP#|H9IG`C+:,}nfyN(uwPwW5%)nW4Uui}La%vM>cRKJT5)%yEs0Oq6C4fV^>@!jM>;H>~7PQ)EJeq*X(seS3glR/WbE5WaV1zLb@2Y_w-8LUJN|P+L.LH0!hiRgon]|QBG627DoHCx0,A&X:wL;Gi$^a8Kr10]q<WcLBPO+_A/7?4+8V)nMbjgbfOLpev?u<NbtPdhNP_KIo/ZH*dnh%Cs~5KD8N{Yx!TjPvK$F,GXKg8WQI3iX0Mj3&Ix`*qT$]q$ey50!5~B`p.D}v?OB:a]8Oapz=KhhQ;B(F~shtatsDK3sp7lT)h<%5pOE!T60gr;D#uY"ITv,Kb3dvJz!L`bp@r0
PuI:.nAVo%^LnCD"`ot-#.^i9#N(E>9{xyb?/&GUD&c#=vK71B
nrFMIj?)-1iwKs{PcQ
smPho$f^<DjubC`oPo^
)UVHTt>8gh$f.w,KiFnv-#';case'bg':return'&evKs:wmT*60qR/ug>IO05Bi+63pR^M2_I@U0
[g<g=+s>2Q:2*uSa"bLq(mwS4("g;p.wiq|y6N&$(CnbT@g$S%.w[RZv-:hb>w}M)Y&ZROj^u(dKt,%b?
kKurOxyy
ocbhg~bP`/tM%sL7B=JO(#<IX&xYxRE;vZ0vs"a&a0c@IZ8=wL6:Kt3;"~$gvOGl$bK&9$95%wkfr7lw?6l-sKL%o6lsI9BUw
#{)8!b<-mX^j
yG*bckxi(gmC%e1@&S=@~SSY6E)!n:sVqDSQoy`KhnIyyT?%`a7u9/1p%04Ko]n58P
*oMMoE7fSF5uTrZs+^Jy^34A>u5i806TsPu
MR`mJ4b1kGL
+649YW*oQXRH/ew%Nntn/6vWv=R5nA)73^khajQS,wE&4{GguBM~A$?ahFDT>"K~4^_+[qppOP=vO7tDN?D&=KSB!o&x.Z8TltoO"m!q_KPEa;O02jX%Rtn7nyi*=Z#k`-LZ?V?E8qwDH)uu]u$.tr2=a+M.x|nkG82L,_,iJo`<^xwBjmc7h.Ee:KtPR`/A@,<<Ynu>a5uEg/BBmrAREaM!sc/3N6aAj_JXql?#;yFJ1yj}g,5HNkBx"OGcVlK@X~*gW)j7/W9H!<bt=o]Zo%*ubpA~pRiZcl"BnWDRF^x]9lIC"<2u.o8(NlY]Fn=H&x0pA?]YDX-G4u*G2xWSm4AU_Ib]P(fdY|?8QB`mD=qYd+5a2{%u=)DV2vr+`&/l0y_"
l7!U59u17Tns^OF"hpN&(AK#yD{kdhV)<jkR!xVHj9vz#2.flGinU6GD.;<b}@]prYbQ,SNX>`b+FldbDY~VUM>.nD-:|nJ)~en,c-EP[C+K>k>sh.&]!Yfnzc6P?=nam;Irx,Ocnnd)~,/w7Wx0|T^-JSYvqaCa}xgcX005G4Kdl:T9(k**v/T#m7y)(qNq0Rx7t:pB1H}PXk1@y/>Ef+64*QYc`oSg6WL;kpK_ViAhAtBF6
DSclgZO_58SaG07
<0Uj+8OVJsZCb?>#a>L*I#c.:PB1
>HkK
V4}p~$p^Vm!D#fk<k=@M8`$:-x}A]^x4r_C*oE6g=.6ZF7|]:2$PrjGnY.O;)PN`w
3Ma]#=vNhZDlu?R<n>r$"[m^Q)=E9gk4~#{n
Hps@3MdQAiRw76!M6e*FHmnoV
T$23TNm+wp&lADOla;ea!C(~?;>KJ%;:
8!,
!a73[FJ&GU4-i1HHa2VeF#a,-[Y=.hO3;!%l{GNja>I4==xm&8n4F*{UXqxXN/k3`=WXcJk#sbVVlf.1FL;0aC54i&CQtsDx=(GO-l9`b:Beeb%irp7.dD%l1pRYhnO>HJ?,buo?MOM>H@}&*?n"G51,Rss^YDmc9R<a%-9U10y7GLwx&V3@V+7XA?0tL.bWYkGj(IAKOJyFUGNE__gqMp[LIQ{VfrA
}]wUv0m!];I4/IZa($@n}&VV/iFvBxv5m,mGxkZi`Gd^2S#iNtcIyDgny?y1Xq?RKLXdxASO-An<w"oe*V{1LZEU}^*S&B1>kW-QH/7LKL7c:(`x8rAc}BJWoC+]@J^0zY.VZK(y-,LXr`fEeJA?@4S0rj_XEr`7QACjMxRqOuP`_7pZ"1}9-S*q3oaJyvSXPHQcKiy)LN
4d<aI@>4,3R2aQyGu7,.xfB[DbF{dSry8?xTGS
!IvqmBGxAVO8,gDI&dB^:6
_FZTH9xoWv5V?l>nFi[)f,`P-8)yTPFLveE85"o|S!7SFx8WcX;PH5SpWE]oR#q;Sqd9w@8>:MsDsK(
e{FNbSudDu7w%A*!&X6V(sQ3d:?:RL6TQ?-rc4UPL7FN%y3`v*rB,~/q8sf>4(V=,L<w$%
jZ$7:p9PJ!IK(<`u,(./xGj3fw`PR7Q=Pq+a!^;uQkdSvbC969DtN;gCv3}!#,S6k(_-N*#mIl3A=4P2u*T@$RB@7TzR?ZVNagm/.fq(0V(]kpw?G)C)5#3R67d?0XiA!IKA$7@eWjU!wX&F/S+;(^`0iJ
7=Rt4/Aqu>%m/lo$vB>gp>!/JL:NCv5o+9F>*1?EyUszByMzUF;^
2h,H+QNR/[;t5QRY^-"*~UVs7Cbp6sw^d[$N3B#Y]v-y,aH-H4X=UBL/EN<0At;d.!
4$fMrGojvJ==g7,PG7nfRX/r4+>"x@",(JEu4?4Z/
8grG!KO9A]01*5ti"}&hy#
+rg0rXQEkC}kBmjevJ`&VDPDI]zDHS)II^"[H^VElXKJ:2}[{UAP
+DP]2f&In@KUWrB/EDD+Z)eC_|/2E>j)&gAn/a7S!U?#/enx48<m
c];LhA$WC.3OCBWR#NgeZ.%Oi=lX!O0_*(0:TPQNV.n"vT$+_7*KOJan2ecW&:
sMD.TUlV86^8/v`n
9Seo_r6#(bPw7Fi%vI1MAIdrV4G(,y-3cKxCA7qPY0;j8k<@[GI,fF%w27Ixmo$J=K4-zl,I5lT8
Z!ve0Bh&,Fww-,][X-SyvRchxztJ2=/
EL,8.Vl2"FNk;)vfElN$dSLwpTN=hOw3sPj,MzjYl7XWJ-VPm<61yUD4?7oL$Y=.IhD8P2(le.,:QjFP1U@K9P2h=BaVVs4]<gu[2MqycNg=aKj:ClyHfhX6EA7CD!Wxg4cVTO*7+#K.AQrB<_aB!>&0@EB&c80]86plRa#jui%9%LR#CRUEn{Ez_0O.b*ANGB3J#;N)S;eZvO-8p7b~(d7gAC
>rAE&%qO}K/c?Fpm.Oa3V?QPum+"+39UcwYw
_g/JZw,*S-e8Ll$L+{g<!eEd"wfg80k81Z#jKg5mDm]10TkeWynC`un)tJFV?JdMA?*fj:dt7X6-:/9e"xGX_EA~*Z,yUlleAomci}"A`!&13`xZ>l-a
tJ+R"gNPrL;]9t;M}E?QnLPm>!et9NFjJcBo/qL36CPe&)s0qZ`cRFw`}4sDAj+&<Z&n*#GY~$llY+nO
fQK>wO3k:Wxz2jF|orQ?2<O

JBQ#kF9i_#-3h8L2gXFNCS3><bs,&Id_:htk~`7dn"lHtE~PI(=WkTCU8tL[%[s%+F`5tX}1&Eru6U:lV"{[2NHEUGM4lRoSI"U)0(O<#<6PXX.LP3&mdf=O9WHJ</dC4885>
<td^[Q*D)AC9TtT0Y?J;yiqf[>5w76ZGY9t:)hu+0wuF8C;E~PYB!0%0w&&I}x0>u]j%
m"Rz1Ghf`iW0P^ZSP^kViYC`Tc(ko9f-s$eP]X">T`7BBQ*r&xK-3>rWWV.9Lx>Lce-uD)Fz$)m2?A!_.C,Bxr"XlDa-y.*viVKB0fh#g=a,<5Bte(0m)k5ClO>oiw:)$ev%c.CS[xoyJWsU%CnpZ4;4@<_P/}fh;#
jWVdaIH"p0oO8(zhd!=Z[fdXkHhP%o1Q{sl7gCY,(sW(XWaaf(#q?A9wV5z>_6N=Ji.&b3g@1Z?88#9W#J`V-fMcwT;*E7F(;$"U:5?0Aa[:|(.n46iv&T$ht`iSt*i"<Z]+(&E55$ciXu!Zw_Wdf&x@?%vh[nNuV96OOA5<7_wrWbS;FQC*m-KcDOMwr[iVsoxUq@E:Aj0KqQS._:dP#jwk_wB]}3rWrc&l#7_.CUMhg0Of>&>p!Z
B3fs%M.Vi(_,!sK%u?rtS+j)5)vTy$C1BYDBlc3h@p<ewk`]kVoNcDJ7M]-V2=0
oMYlo<ai0;S.Rso=^
y"2UazZvV_AzjPQfS=5b[X=:&~=]<ojUiEyeu~(B0*W650SPSl/byS<(31]BWk/u/2-+IW"wP2Q8Za.5dK[eB:OBBjX)pc$P2PIa0);C(S9::pi>&%9!K<9R9hh7c*&?7]Jl#V:fd}VQvC9xbN=0*fI!y@T[>3Q
.VW9s"M"#U&vRu_,@6<S[b=6VxFy?|3ga):~E](W9IQs@uG0`#wKb;!CL4vzBhg&P0mVjoCYq$g-;:Sn@+LWE:#.4sBZ-}lUcdAWsyI4W79FOx.u*s&LD}6Q^2oRu&i*_m99Jbh&:+j6E?])]x"cK2i^rWv!oOSYF6)SE4
2*~4<
&^a`/"uFt*;spOFUbGdpA]CVjJ,N.YGB]aEhtlk0uH=1KkO:o)fK=%n+8g=o%*Vtb46<xr_4WF5ft/;0-B@N7+PfWlkuu<|&TH~hyj%?[@tE9wwLgj!1)@d[s(oC-kY##@!3ii@]*w{^=q0b2M6b@2]SlWICB,uq=/L<)4[Zb
Ho
JUdr7u#Kn^rO]!XY>y8=wP*K:ZlE
)
lvC>O0e:Y7?0ZAQqCo^B,i*$]QM?D),X4y$u?SBh_?!B>LcZP,w[-$BX3[QWdq,9:`-%|BYLh&3dy"<R[+6A4hvRkK;2wYn9rV{xr]!5&>hv"T1=6_G
9F*"t$9sA.ZUt6|j_9`q5V8yTS&8h;;k2*MqAMi]>)^XoZEu4c"%;JNez7M+psIhQWh3~4>c$93&T)QJZhlK06!aVl4]R.:/L:Fv/74Xi[[71+NgBb<p%4;
CE[uo!3jxJ(KX+Pp+WGf.IZ6Eh]YQ$tHHu,ZXV_n%[j[[rG1n0]%_d2Og(n2@2@FcZL>S_ZHBpXZ6*sGEOv9,jSYC;P?1p*#c
61z$OqIKoSIUK84.J9=Q!dc`+Gitlc2umVU8OywF@@YsFJ;N>,^(n5N$%8KfLui(?_0c*3c4W&/)K26f!.`$|13cTl#gy^w]^ET#c%bx>6|dX;Q;a"095Of0(F%V78)?xl"H_K8Z:^~SDs/89^XlT!o5~^oiUq4;1/*.ZuxS~ZA"=T{!EH_]Zn^MF3Gds-9YN9zAE]Px`OZr"M;wp$#m38H!-kbFeqo1>di]`?jLNryUI_?C$gQ$nJeNZr_TbS`!}dx#W4"L!bDh~]RA1_wqs>ln?sf6Dsu:|B)<Yx[c1_YO_e3$*KQk4ibe&%.&+JXF@,bLeX.^e+KW`b)dK2L:!Ck&1&o>wRY-vdtZXA+r5";Bsd^3VAg#ld6?HJvd+E9>,(K+)
gS{wNc/9neHtf%>3]th3rS-h9ZreUff
xhzU^ME"OuG,`"<T6)>8OQT]bj]3.iJ4KZjS/K^pvX)c3u~yCUp,yKc[ut>V>1#SD:{B|H/"2*"H6<QI1qEetqec[i)ym<isxt<ogi-*3LW*ia`9OH+]K#:.#26yDP^R61R*#z&P
';case'bn':return'"hWVcaLpk?G3udJ8$!K#2tP_Op?gUa`f61n2ZI8N/-0DyR2oH&99,DL.A,)WE@~km8XiX#C%bpaJQUMA6JPI[I0@X?MbwH?l;LZ^Uv1MqFXpRB8H=]~77Fq^5a,^54a
wB<K*J3tK@RbNoTw+KxfEnc]"B@s4rZyTJXv-[065vaXhd>_nnQS2]FH%1&h9D+oH
65o3udgoRvId/SWyWa&4k&e^mMPw+-{x_w2tsy9[r+jN#vw4QCFlfGfb7O5!a2}d)6JM>[E__&"V}oDT&"/oeK$pElfbzHe%%mXji8=9hOC!ByIt=M5-D9:?;K^tQcW5*A)&W9fqF#&$xun$;,LN{vPb#Bxw@A<I
2$$,"oe:kxU;<S!cv[O?=yb=8A8l.9RJrv#EcbmM*8,X,+
Mhmo/oZHT72i@:,Qc.+o#A>`KFW>XFi9{+T$m:/Z.Nt^2%)`RD[S6`sTjaWD4ZzO^bgk}=.DVk2O>X"YF-(a?rv?26.y$yAj/8XfXHsIENi6Qudn$Q)4:)Z>Wl@hUL5I_a`<=hRcR4%tH3-a.PIi9[EQ70yl(,a6bI2p]F_t$OWkuv)ryq8_yk2Z[i(>nY)wJ"a8_iyCgM{eyo+f}LC#P19=M#@]nC"@n#-fXq7?W/k)P5/4X=An~A5cCtx3>ITQW&`IKJ1-RHs;1@u"&k>2@?g]M3rs{",.Yv("L/}%ZBRAV(I#+nMj/m~kEYh;1uhpas8(6eT`kRm$NN%9UVfg2d=/2qHdI]:obsuvXI;_<F0>Sh,9u#;1=F"!qY|Chx8ET]eQB5mKT&)l8(-b~7u_x4$cB1o,(JZ)4GIesgQRdsM>Hd].G1Q9lrkT,P&#*K^DEei/f?_%wS9=+EZA@KYOs!~W[Z>iwqkR=[&]fY|x,R#3L>}gHp7io[?q"oF;!yv7v]~6/G`
uft>9k&?,a^I6Up-/jKX-?=z&7_z!Qp]aVIn!1hdo8tAkI&8aB1?Gt5#tT436ZT<FsKT
RU<Kf_Y"7B"]/1sgnFdlx98`A7&9!/FV&W7:_^JjQD+Hhpk}*s(-nQE{=$>MwEkSjr(XWR@LL1^!j7]m
JkQWzsh:Dm;L2j1jMnIIzH6/fKoIq+lmVkcPhh,7}R4hV3/5
-}
jd}LM&!k.hZu<mD1ggJ&(jc.*8Vd81|:T2#:Bx0_w:m-~-:fi"8S!4&240%KuD)h*p"@G6RI@;1BV^b/")j?*sA1&Jjl$)VgG0h^]+|9ZscEwki[Y4
E#y29s/!h>QD$Q1zyx<T-T0I8S-)lR%x7a"J(TVGV3Mk]KY
"YtwHo$47Eq*m-S{$*rC:?CT>YD_fwvjPVi6#Z5TX_[H)bj7o.Uk==o2glZwF-,T/Th&tI]b]9(tD6tKM6nZ9/e,&q3Uoj8`uZxd)$?1RCb)H).W%B:P0OrX*d-EI=-WD&FgE%
nZE(N_E)TP62b@xm93Gq4[ZJr?U.&)&[JI[H@VcAU;{n<:dVLb#X$h[NT(7HdtY@#<O
nk(C=1,@Y7IYDD"_+X{dkq6OQwHL]X,)h[Bh<eYf>o2R1Js]{w$8c04JsEmwGTDX:.W+nhb"w=<um*<bukbc[e6wFV,O_,-2+WY]n!|GYa-^+@+7[jQ$d&uj;Q|VlV4wt^*vnJ#.6Gk+;HZd}^"pph|hj-uEPUYc[r"LX*B]{Bb7sAn(}]>(q4JH;ERP"W7kC`eY0Isd.S!OHKt:Uoy_@7E.h5/2#-q08XJ6$cicXH6&2MvOQm[kzg{9jA+"QUl:KNXDkHgV
ysOx"l1Ob&[Xf91OSmF@d9WCW9Us`5QG8pfysWc3sKqKj"#<1Fq|bW"Yqmrrj<;iv)[q1H-EX2`RLlL+?R@ZO~_eqJw8)icS
1j3:Rv`i53v9u4t-*nN&-"E`xK<GVn<+[VlbXKZdZ`#@5w/mPk,",%]22=Te<ycg^vtdv/{!
-QpzkvQdHi:L#rn^t$;643OxQH[;G5[k,%pF2{&NF+fwb+;h
CSD)iTA,o*X$S4sy}-6JX=ThLom$a`XL=v[RMd<*`$V*>rY-?e{%ReyVZ-mYO(b);RRJF[/t<KK)b<TgL#ep~i`^iojTLmo@{g7D$7m-)xC,_c.6r3SCv&95knwo:x{KYSu(xiZOhMK/C!|XL[EE!:}GfFQHYE7>L8uLk0u[Mf3h,>JUWJBy,uznyP=<"v(sF;sWQ;Q-,[GTxMmgak[=$&`0TL]4t:KKNE}aS`~MNY)c;Y,3Xp.$<@eNC(i0-Esc>D2?
Bm(>:=N@U
Lknn<25
VfYb?qM{/_h1%GK:[N./XHLNhTZeQW`tQ~=]JmpVV.;a>gs}VH.IeE!
hWf4K;DJ?oRpbJ65G=+$PKJlPo&Kp^D1q#bog!(r;E9^C[]|%#VB`K6BWJ5R%Q&)9gW6Yo]OC6fOR2])Npm,%e3)[0(]F~9/Co"ceR4
Q=#)<yZAp4I~RNG{#7RG=VP*P<GY^@eApkdaE[=t_aTp,8%LJx)U5vUzUO@xW6]&1|0DNjdx*+J.Cv%(uRCY$RMKD@_F.=h!K".dGP--W&_82kgf_^g9nHj?[=nw1T+[]NrN9g0#T
1`:@:H=mq8oyC
,D(vcUpe;2a76Z1fTO[iNPQ*sok%jxlP^v[
N~9!u==1"US0ON8IUtYHwj=v5tAeFRFj4GacX0"91|kTtM2k>{;`Vpaz8vUuiG/.j>rUaqMIH+W&M)r,vw(B*y9`R{$=XG(.vKJfQ4fOl(D15M=>HK&9_roFXoF]hQ^?c"ExNkI^L78~KM`"LLp`X3(1DJ.T+wQh=NqOZq;n5uq*IG-w.(knfyiw;y"?WBr^0V6|!y$;.J4Y(1vHr04zolP!7lJ24Zp.kXq0LYF@FNsnC{RGMFhFg"R>`#HK&pNQZRe$83;tAap|B`ahs#b>H;d@c>8zFDxr11(~N9?E1LaX-e7KBR6X!qtUxwp.CFVV(bwe4n2QH8.-l!*~kShfG[7_uT63]yBV-5xbJqcj6lZfaCq~/7RGSVBQd`c(Yq*mX.4("BLGeFa?e2P.DmWY)W[]o4#ho_Cw0wm$*(qQ3kDlq(o=>"sM.=T*[]m8k~QEJ&]%!;@>Dh4FmwV/<38|"v=sji1SU:"0-O/prs(z^IISBNc5BHUFN2$pYin@7iJVSZr5QaYcV"!F&*hoT;7.k/p_=`@W^L4jW%ZMcT%zC-O";d,/Og^(kiat@lsG@YX,`T
)$&>SeGT^EQe,CwA!_q7n2o%Voc2Q^Fn^D;:SVt>Gqs
n1W5^tDa7
M22s5uxLWsphXWS1{E7>}/Yd7tK(w`(^C!aVSDcl{"`BPYBSVd[CBp%JB5b4L7MaqS^UyCr;d398_b#e;*DM3&:fd;r"wjZ%nv{:6fHt7iW.Gp
VJy%FWb.1<m*K/`9a]ZXm=Z^T.2x]DMRdYR-F-=q8;g838s0r|flYWmu5v9#I!U}4>TAs5^!.$_iIrU:"?wd#Zgxf>qQFSwx<HjtvQX:r;5?;h5^$91Ba_Z2#$=2Ah96R(5k0{?`>t/S;#hLNI)3UTD|RW^h.+[xbY,TY[Lu
l,^Cs_#Z%bB-VNmQH<=3g)
50kSwt#}
janjE.KZtIKV?Gw$zWe4r!0dcO,5gM!u`M$Q*z(]1berE3/N,;r_-0ada<+jBdv?Rv1T7W!2g#.6u)rW"s0GtLH(<Z@
lm3eU>#!wiLXF4h02V}C8,FU4?,`ZiTO]JIIW=|:o8AQ`[M&&o0p
vgatVL:^_I@y8lTR=!/Y1O2K9veXa@7:9u<TS?!*SEvbt<oPE@7OHz"u[bF*UK9m?fTa.,5k?6(0oji3qNU/EmTy<?j&E]@0eia7vfvD&@<8Y?[|.UH

bMt5GiY>BSXrulK:SX^[>OM<LHyn[ex./^2H6lthZ-&o@t]lbH+uPl(e@[i&np($j7}3{"gOi&B"b5-rieGmcd5dq)CJr2K^Idu5T620vt(3m9pc9ahV.@+*(?)W|K8^wKx4t_L833G]+R0hN:<
tKw7am<k*e%A-tqO`-@:-),AZ)qtnP6D9IVR*CX@TEJ@&[X/>@C>tnnNt,(:8BDX2)cf,c}"1piul,I/>@<3+=FjB%Sg(cknm
+
Lj}URyNpoF:TiX$Y!M!RYTsPIdr#W.XF(g+0S<MB|iP_a4FSVFAZ&a{$p*`Jw*rFZSfM9*rSPT)Dd+~r>siJRamJ$eu1rZ_;-YP.A1,=-hR(<9%4[gIF`6})WxF^d*S3lpuI2rtTBBM[4N<W.G8n%S)A|uRfb
5
<^!l~sV%SF`Uv:">]!rgjM*Y0?"gw8tn%]#EVvwxacmYJ5{kFl$1vY)Vn=v`t^0GSV7Mxe~N,9~ZFq(a_g,(HH3j&wb(}Q*U"k8K>WQ^bg(W.:<14UQ9|QQZ9E&q`)o2-l&8R?7PGOoj0-hCE`eOj:)ve-2CMU`9*_+*(E=LD8Mj?hVv{n&"3x::dwEir"4.3hr08jTA2db+-5~1LAF/<<(sjw)(aV|gpSV>&e8O;J`%lTqliPTQ5U?jieSf(c>D6s@1.^t^d
).#0@e*sBtFiy=hsDD##E_J$Q%BK[Y51bER<7Z<+!5
VUs8jfuHm4$}"::k
@=)
SN!kmCox8TVC~Oex@*./>JGN7R=[n2Cr_n!1*q3)0P8;7P2dKhT91y#+0/53UhsP^8!.WO"PC,KY^`nM;eQW!L5<<C>9ifkcJHkI<v9s)(rE[XOVh:#eXX9I$SFZ$5GQ!MrRNJ5ly8,aqtKO7kBO}4:?lbKLlQDpUwsR8Cz6-I/P9se^yPiK",L0qdD^m6_R?"v?OnC>b_H6ww:Y(o@#P;viHIo]X("x3BMB`3dNVhcfr29_5Xd?dt@S+oH<{Mxk3(!A_?l1mof6:`d[xl%EJ)XqSOgxbwjBj@k-$`J!W7E8"aSNn85d%(de|m:Asr7s}s~jX!_Zt3oM"O@&e+LiG!ub*"X!b>jvEsN$:hQ#;4w[>CZjhp,WE%:ui+Y5TcLD&j5;8H~L;8imVlX"m6f`w@Y.HX6I8#yL7!"XY"Na7j?FJw:U/bC$st[8MqvK(PVMxy<=_3;Y&O#&-e5?E&GZU#r2>L~Scg$w-i+=yO=lxb_K
52dHydDu&:B.^OS0roy)udG,C$LBG`#&p=ppTMtEkxn9;?".CJUsu<Km=?tI:;`Qc%S&[m&RE-e-ZS%|ht">Fd
uLXR2%~_rT;c,kgK
;Ctdg7?gQ42?mjjDC
&`4FT^Ll!.0JWWDdu/A8G/p2LqlP<g.[k=M;H=HcZ`MI,3K`Rz`,]+g[e*LaY?CQw{ikQ,
pk|DU3fWv(y$Hc|^u';case'bs':return'"ZuKs;zp-*60q(f<E]78!xa;5
hptRx`V1DYblDn><^$L$(_9th%^6=-kLXOF$yl-cOyrsr#.Nh>?lnxlrJ3wt+jyc
n>=OvkXSafW-`x@A.-:
H&V>2{/+x1pDx+/CHK[9;W@@tn>X0>H`).b+#VT
K<H!/R62V=!=;IvQIZ28W;H&*eOafM;sEZ&Xw%lDQeUb]zr@>^yytE0>EUS4ZPpaO,56s`PRqFglx5.ci+_hGeP")`lIh#0mWQ.yK.S8vFGe:#e]j-JPdo@qWgY1e.`cD"WnSfT..^gTZC;aLvyQdT4Q:~xW^w@BM`pu;g"UGK)$=IX/<4/Lcr8(`98NCJ)oQ][_w%RnJTr">uC@cE%1Y&t`mZ`x@^0Ah?[^v76^CCnhksY4[^bGc{#:Y3x{[++v?6U#/TVk!+?r(gru^U
d29&,78GP<=R;It__T4SpRBOR:B)Mk71qq%CQ-IalGeV;kv6a];0%whc4hsF8V"h;-q?s9Y8b%w-*L6@SYrbJ`LwENp?aICPJP0LGBWP"!d7yUx?&pD(VI5=G@]]v,5R1*fqiaR55BWUE<G)RL#%
mRsm3OXs22pGy.9w<vuyXvT(*G55p1Zd,TK*
Tl?({"J9vf`-V]mCXR"lwdKlJJv#zN+.!+d@[z#r<A[G]ZqAvo>&Qy8&uC@Hbj%j8AFo|M{<=Z/lxPR)s+zeYv|ikX"9Jg[Rh:K5kRWCEhtYf,l`He^!wT/D2.;J!0#u&[@hu:sBPF0ZGI
Z4iwTNNBolZR/~dPh@*<73a<;wD*NOdWVt<u0n5"nT;/!
uLC4(3y.N~dfp(hNEUb*WxF=!saJ$nSPg%e#qZjOnF-~+Xn_"x@0#2m`n"P)_$Uu5BS`2)yAEWJ1]u,X,njORo"4EIk];`e,5uICc(v.*PBrJ~t1l|R92FZ}kSmaMFKG_YRqlP(n@a$ZP7"1=lAPh6lOJ/IJD;2/HxIQiblL>D?Eq"r(490{CU>7?4>[^y1pL$z$<f8JV4KS>KhN9oed6;$Zl=0]V_hmv*vK_21K?9RMi
8{Ja%pw~>Fs5,>2?#Gn&J+"jw@[[]{B1oUM1^
8V>]6C#U&!1M
.wPZyYa]*E_t2It2f&D^yRQ"#B>:3vjrP<U&27bRvX+%qKjXVwEh;,u.6NzX0ysodxeje$+5)mSo?x>KlyweF2+kXAOMzb!/Rg6<$9@j3)c[.$qL.9o+yL4W^W[7%K{<}ZMJ@s2Q6^hSdA_C"N7x2>^V6ISy[V-g>6v9)7}_*-X!.Rx!MYKs"E-v=j{
`dCaX.C(p=$+#V),d@]BiVvLxaYS|F|fHE!T]Z*XZk&G45j7?4xL#P]Y.mm@M55<qNX9(EG,SpC:V3
3mox67GSI0H]/E&~cTd3#BT):Wt{B4UyL^&db?,r3M0g`8hvfm6CbUJ+%$lnsNM97M#~pp2S-"1Kel5lZ)-~bHMqStpbLk1-Z=T2O=lZwz,.5^cA<TBwA&7fPtlHA[K"!.Bq[~euaYcUboDTs:TZL6puw*Wzk@!#Wh#t6Y@f8H?h5tMk3R81X&Ap2~.frRIM)i;WhRF9N7NG_.b%ntm{7s-6w%d9j"=WMC/6rdWdX/6IRU0f#:(H?
kn&2CA@`*3oV"7Ns=;nx]hnmfOyy/0W#9Rq/NuaL/D.Vv
a)PY6"h|.XEqSZ/:4&O2>nxfwtFCt9;2:9:`-/X{jC^8XOYVy#iC?:xa_((Vgk6v;C)U)8r
4"V{Qf7BvQc=4U4~OB
j!4h_-Ye3`l<,$Pm`px,n_
TJyJdX@*Uw((+irF.|M")7Zc(m@0=ywW%UR;eQETxSFa_FmnX3O[.J785H5LQEiQ]{.*1ovX:AjKfdGRJ9!4sx`AFW85QLI-^Av/fZ-Pm_FwVGZN;EC&A.)_N-J(g1;_H!1A*(HxazGJIkm`Evjdgt#eFDbL^rX/p3QDq"jDtvpEi7bZS<9cRi>Db=@knnNH"I!]#wDm
Hni_7FCt8btnN_IiZc7WY:{7[x^rOB=(ZiT=~.yXrPd>x"m6bl9=n
Qjp&SPPZ;Z".G:XkEDqg
vl<vltUgFL;0Z#96fl#g[ZrBZ1?r7jjAetrQU[X-M"OLagmP;IArt!MUv9X{tN$aLRpmCB.^UGnJiL<k!vm.(o2ASKTI*(<`KBfOU_925lV.o1yi0[
SF{L4,`=vV&dP9KBGq8#L,>V4![1tS3/Z%Uds)-6
%(*@P&0(PK;eE}J^iHOxK_cdYr^zv#Cu
U$9H]7tvoo2Wdp4@qjJ_od~1SChx8VwRs[uNh@x0DRBUJkw=H=r#dt"5Ub-$Wp`Y#h%eb8n$c"XkqAJpwNXNLJ==
#iGocS$)V[YBg934Z6.D5fMF)u1d#{axW6HNJ@M7_">{oWfYUEml17byhDxzRAToG!di<?wN#/+{O|#9Tmr]Y$xYFCX
`}QSQBi0d@0LNf0ztge
Z_#op-(myJSKCbpS[lAXx
)|mCi|H=d9HYK3
#2Mw8B4tl%:)0<kT=*U=T:hoAEQHX^x.[[:I4,D-";GpXHgm84Le9b~_Rc:BW^0YGYp%`3WLQ/$[ai&KLDMV1>-I!&::xu
5L!5xcF2xqe#fHx<S*4~$*s-B}Znoo*C=06LC+ne6PZ8c+>YlLXYU~Lmo-4%61hM$L2
m:I"3jRr3Xlt.z?Ns![~Ax$%!c.K+?2k-`q=RY0z`{R#
>%}REay)vl`FVNP.0sn2$/1moJ^g
X&4xZ~L$#5q|%t)`oG.W.mhErVS+X&jlhHxG0Uj-J{.9&;0>A>Va_^Ul+(s>-|DMZ?CVft[x6
gdZXtO;GhR-
0<#]l3Z]AL`dYR"<GsW{.p(vL*Za8a@LeOKxnd]
$FF`,a?,Ql<P/dtaiHTO9z2w7o$s?BW7"Ig;
])ge/;Q@UEzm#hjrvtU*C^e262#>8="KdalmyI2
&5w[2iY<9F]83p+RH
ghE-U7hl.?;Q3im*IF~s2_!jnUkQ^/z5qJAi6tV#w6uQnyU`f/u2q_^uN=AAuTRKiATDw(?&PeR1@FYmrz"t/>n-SgM3IYJWOHHD/h.ZSF,ngUBCKdbdO"$P2udW[Q=3[D;,WHG;XS()[q=4Kk5%@-a1)@>s:VNHEB&fVnn

jK_P)erLQ2.JG(]KkN_#iXKv.,pc+Mw:%TtLDp3#l3d9PZWq90c.,;
h`70
>>O/$)UZB&=0HBv}]$Tb(vYrq&0s^:h|-hYxk1D-mz2uA-^SID6wN6VSrF(j=wJqhlr}&"J=0XX+A|O"`z*4tyf{9]EAFA9>0ws>O<&<:iL9vCB~A<Kb`Q$w<H3N@A,%>2qqqG[3smgwY70v[3v
DS3QfD%F-m!ImIcn@{F)mVY,.bv)5[r72d0g%YpBaUkQ:x0=p#7CkIL<fFG,V5UL&[BQNrxo@.[`+dGRp
hq3DU!gjj,cuZFc$JX.BJfn9EQ@8tS++VVlT?2Eyq9M2iM0W
"L{BUApko*qg|F3;PGDK~4]5/9m>t#ZMlERwa+qCUiD2hG
3|48==CS)8/YX6gh*,XXF3m+ve]sYicD0mVSvc5x%GD37}^TypPZhg.qf3/UKVS(){AkVOuVR|2e_e^K"Rf(ZA$yn)$;</S]Ux;Y<6f`_qZ
[3/a+k$MLY8M^<Dv
*7`Ags^FkJ4:D#^Se(*Sh#$^KP!SK50`+<+/MA:WM[NPlL@i{TO*f"
F^OKKPtCPaPNG#(fKjt~Sh2y?|2.)6@csFy+X5dGmm90+?)C*1ko041~<VU^8&ZaPHQ!5nE<aN)J?x/|oMs,5Cuy()Y(]$)KR/qT*[D&]BIlK_M)"^.DDtX1w?h.(V8.wEY7xy!l@hrIA:O(@hdpxClu=(wafQKrd7/ONv^1[>OQp;SUoBdOHbH_r<eh(hGU/Z8c-ki`Z]Y)Gt4rl#7/&:8CgfdG+A!lJIaB<CBl#SVYG
5ti_?Q/eMe^gT&9pJu<Ht^7Vl[+hp]u&un*N8t"aa]
#oUj0NNb?k48~bN;V!]2O#f/5hOfC,nf(=8GO$)6Sx7xDIuf;bYf7/dnsPEQg&J:`[3CK"U.y.reb_YwIs^mA&F;G
a9c[`BYFr)Y
VX@FIc|[Z:o=gI"c8UeK*$jSi_6D7eAe9`K"iX$C<A|<}O;Q~r|1/d~b{HK8M(j$fvj^&2][+hF^Jyk3qqZB"R:X2aHjeW594_Ze`VO!|R<NL-qmR#n(~ye/[^Mq{2$-ct_x2jMg+Cijn&yDbap>LB15]7jBTQlq}y$g4E:%EJpCEwIW6P`gMeG
IUKQl".(n.NQ0AMdo>^NFR5><X{2wAfvQ^G&$(?Y]a//-J86/Q+vQBpbrB,jp:-0n56/6QHh*rqFu4nDT/UuX;x!i!UuaI*<kM5K+t{wbdnpSjuY"x{D{4eksF#MiWp*!=6GbQu?)I?H1lPk{C8V~l}Cx-!$&-{uU/NFA^]&qE5hY"g[-F=B.;Wndtt1bF=$7bZQvwv>_-sgsD;&,^=!H1fU{
z[S:dR`/a]fsq!@6l=YlT(
vvEE4<E#gxctXC';case'ca':return'#]^AUbTCf*60Ndtr-8>M7E2qCmLlKN=;9SrVb[v1"p3h_4$Cb^}_eUCS7,_CBpPA4.]!q>.ghWD
MkHc@s[.G9>JdtUAJ](?H
ona6S=#yF9(g`u#8kslBL2)0)pej-j6baUjmwjol;u;LJ`vH4`LgWO7,0E-[9[h.7^Bhn-I]/9*TU0?jDMXL.BOEu$ENf7I`1q(bdL?(Y]gR+#5OLN:@fhXoPVI1.EGhFuKl2+R^5GzJE2y&74m4r5zW|gNhR$txWG~xoE-ee`vBTV36O
A$,5bD<=OQAt2Z<(NtQ7A&Y"[qnK(U[<O#4V5%e0q9#k]m*IL,Qh{_yNO!dY900ds"wD8J^*ZjevzveHERu[dbUx,x_ZQDK`/"R=Oy;c;-Dysu54]uk$cEz@sIg1s1enhisR~]JG|KOeC(dM5xuB#gwt`TEUF^%>7vXP-0FV/
eELU6B=gMeF11kRQ_MrbK=j5vX+/P$eX;E+m!_UbCgh58ob?a=QmI
uiuF)mC4cs3TUk1y!einx?`-O9cyPqmGt?i87LO9Hc>*6@G`{%NSXK.u>gA@?(Gl(BmyNS*365_JZj6xiwW6e
:!t6r"KK
S5>#W))i7rJ.FT^sL.W,F~.sqq4SS(
A8=yj^WtiT|6S_fg_414`82:J[k;n(Kh}"mkLw5F+$T/QQmg203."UqCU[T#o"9OOazkAB-?IqR;(t6sq`VN%yhC@1kSK[Q.T>3Yq>5eteg>`&U3ny,f2BA];"W:@nl&fq7!2;u5Wf*b73ia6VxkEmm9%t/L`CS6Q3HXTXYsrT-biIh&B<#xx+CEDe*$@qTO{v[4%LDUwnr+XQN,]Ac2m_t7$w8Yf!*p_@~%9T2yY^O%&0vjkwG6-iKw|>1yX^J((`?
K&7va!:H6P=]/:rJcC_+g_E
lb2I)YWGkxxu&2U)tsrb|Z{Yj:Gs*+%j^PxW5aee;ZIsOg!gek::uc|N^OI96.mQuc*wR`Ro$cO<g
c0cI^8{qE<7!{esj"4]<.AB>&!N%Qb|16({X0WaDg>,)Nw#B2-.Bm#"=-ShE~t<t|!PP"Y4g>LK*DWXpu#l9N+iut7PI0fl5uSntTUMeWq}cl*^p?EKM^q
i%IX(K!8V]=lcF^g7;;RsnmZ6wQmx6jR[qCaD?W>wz@ljkVN=fU7"6Z?`3[j:
KZScT=ZXO
]=ZIb6OS`
>j&rd2Wx>WGzOJke.W%glWH+2dnuMq2:tKmVIF;6W2,O718BUzDx._ar?;*;BZdOU,FFF.
[]iw3T[w/Nn9jdHgJlkb[O*O=b6(zOtUV[{pwoIq;w`y%Jt]dQkObx*9uIlr!M.$W+sl=rn7KRQ0w6#jJvpGur&%B^>6#vBBh#msL+Y2{-DUZ/sj@]`k?pWm?t
[JN4fO>-;_OUinhwUf?KmZ8<Tc#=QVbAXh5Qf"]2?z`PeR2g1)Mc_4jWb,
7h%ML,}Z9pEUgX
E3cq:>/Cb$7O=1<$VYmoAk+AUs4uD{"1KCJ!GdIzZEmDuSlxQ0y&az`
A6n9nf15R|IJl[L)rCwVc0?%BYJ="u,hK5fY=G?S6M*%gwRw.9L}iocpQ4:g&9O|v1WTLbu
33
K[%+t2-1Vw:m@
lvaG|WQVfk)gQ3(v3&8]cIxpFbP+`0}i:CEE%6/7Jy6x.+d?cFn[w5Fq(UMR~
>WY^u]U9.rCMb(a3p(/+iEN[,7e==n_=pu-Mc,aa<Y-F
k5pGb>VI
0U0XdJ$J%[fQ*g"kwJ;7(%igv?RI&cU#fQ^U:25G|d2*U[CBJl=?LS&Mh=?B_DDa?pcT3@nS-QP)@yju*5bE@7B^y<f
{V4PUWLr-Btx2n&p0d*af9U0@IZ40pJZz.%O^w/<!9>?T&,t`0[q2,im
2tajGk
ILNVi6xF=t/&RJBU+JI_5-!1fkxF"TMmt53i$qLQ(4g-(;i-6"+!8wFT).$]obaFn?:W4q{LR[%ytW6H;%Qp<Y)Nh_vnvK]YKY2OOh)^Rd`%fu$@P@[]dlX1@i*D52!s_oht-
3DB8a<.Dui4w>^n_VL
.Wb]Mgwlx]0MV.B#gJM~
Z1Tf1j+,z+X::mt>apy-5$@`)M`Xio(FY"hi7*0^@8MTCX4`?
*w)aPMRY
a5][t6VpB&,oO6fpggB|N4b/]#Y.T]fJ?K1?I*<,#W#Cwg[N%&B")ba.*}a:d]fYWC<&y|?7HZ:c168,;Fc:3i*C[WIYR>5^"/!Qy#6;>pHITT5m0N-exgl/#xfUI95O^P).B;mQ7vx,TVy&q{9T3U48CN0yjaFX*KPx&:JrF`6"P*pmUC)?GOENp]nM3JBi?6]hcrR~<nr?Itb7(x8CjLnL*&4xVfT!o`n~ykpiA9&6Xq8iH(<fV++d>rav^W:T`2X,EK;8,c8Scf$8V>BzP;
jpT-O?m7Ru5vGcfhVv(J|FWs3]6KmYF%O?Y:pV.Ykk"dx]l6TS=KJ/)n0o7Y<*CXGd=8AR@I@DG07*L*yL>rVWnq0!yAS,ZIE1LQ=-W8N^*$)!{j.OywY-h9%Y9Ha,2VN:3lIU|qUOaa5^7Vc:K
gY72~7_`p`-)QQmYB(@R&)qZkfY@qZBXDg|gV5}iBG~#SC]EwB)-bGK8ITzxv2
!/SVKZLmSpD`ALbp/gFGR6N&*JYm`Pw*jrh,?}+-jz]=4YW(=ftxhF/)`ThkA/b.VUgk<~<U2)o,oT7wm{dp8*9pT
9kZuQ.v3=}YuoSw8To(e]B&0!?s=s?qAOk!7Pm]6p?rQ"Rl)$S#)wRrt<+$WCsHe-23>FJQqf.$</"<>-aMd%_QR3wM,(z5OoPXKC5o}7XEoZ4.
R;CuP[rye"ytpa#SOxo;Emn~Jk3w+J<)j0#LM,q3t}/P$|D,/zw`$p=OW3snf^EAASwJ#H_G"r=G.io.X<m$wb$>?<>Un6pF6UG!V8y:38tt
Zm;<bY:AuI)lrKRouk*giC*eDR-wqbTFA_;/HReT/sm^&k8C&k[;OEwdp^h`x`lI[J_8M;;/I2w;OuJI,g,jJ-HMW_WBDE}/s*Wf&`mriyf,=A>TOremY31HM+u.FYi@tZ";@7~a6
I^IkcW/YX*?[e$8=Iy<Y[V(-W?3h2y0M0u&?I[C92aB;%2>F"EtFO+S,>[W4}e-cev1fof.5HK7).j7AMojn.aUF#`u5}--je<iJ=KVPYEZYFnl>B/K-?M4*^<cR{kZE|=4M^8rc_G%^"@X<)7Af
Hu&6%3B3+Z5<R
IHC?AGw-p#,M:,vMF".$53/>F_3;j
>lMX^ZQBs{
WF<#!!rxGA,gxmT0f&Vofq[e^N9"Ayrd>FVJ($>e#%Kn4$NQh-QJsD"`Qj(%MmM0b[ywna#Z$1d[<Z"
u0LvTs*;7mqW0,.6#,wgDb/uZtGvQe6bZY&.4J8sYQ^V)
kC#7s
uTaNU+C=l$"x$/Nb;&rs3&h!6PY*^PXp+XET8w|j(Rjr~/1Xr1DLx(K?[5IFW&=f5f+dHbWri)nupVa>r1eCXmsWT>RVfVa#h[lO|YM-hYY8Z_"QLv?yw^~a(D3&2h*o{qM-}R/7%@-(".<sGXm4^H:g=R0pnbfVkQ
VaM29^e
Utxop#:V<U[0k(^5
k-TT,*!w%l&I+7LL_
]3I*,7!wCn!XtI9]]P8%6B4vBDgikaUsgi9DsOOsfISdbiHU"_]t/llXc>pa??<y{q4xf0Q#;v^L|&bB@aDG%@-F,?"*4rkuWLwtL+JImxQ
lAm
wWM0gL{lw(|7S/fnPH`B;[tV6`gG/c+A(6.JS]>
V;LZF4?bX>*3^Hw]fE%
npan2!piv6;v);Rs`7OM>4x8@mJ%|f.OJi-1$rZ@opz]M(%M)!7KUIB4&;iJA_:0Q-Bi{@AXK3wt+U|CVG+<760OZ,X(Y@H^~Ok%(=H+hO!.rty7??"SO1_)+a06.wW?
ea7Qm;hM*KdBMuT:))T$fJqKg)r<_.)HIyZ;G0>L+Pp4$)RlScCB
bM&B1WqDf5Kgj0oTA:.UuMJVFJ0jOU3:ZpXS0RgwZ?fJ""|mX-EDOlBZ,m@Z)?P5D-*lP9$.aSV-tq]M_Rmir&:w_866@r`YuQ6!Aqy3Y1rc3:k4e[`XI9
IHCCUI+nfZSkA<>W({p,GCPgD|R+u}TZ>#uk#j9m$KS:noAa=0(7(1d
rGQrf9Q!jm)NP73-ftaI:,-!QIl(4/AQYb1mOi@mrg(""Syj;(d{AEWj^y_UM<0`5UacJA4kZ$3q1v?p]KPq7(3Q46G{$PiU2<4
odCum8]W"$78&~ry1r0""E1C65nIk$ESI~C-Ih9n#I(q?:x7AMTu6ki>$3JK+NdU8Fuc"3Mw@FJf-5F;w
?v&s;dT:9wus]1W6Z|N@d
=Q)6ZeK)_%&Oh*y&FmaU/&%OE{"?MyZd:K5T=F^wG{/E=KhT;J4
y8:;0"`@Xq@zNWKH/9C$tv';case'cs':return',]^F<7rp-,|?z-1o?%}61<{o.ooCT6eHvp?5Jp.$yVN9p9~Z*8dci,lc8ER(7S#CflPGm*m$R,iMWc/]*_lDT6dCXMn$A,+](k[rhl?MgBa)bn5R^5[m7r^
4f(>^Jk&;n,V
[CWElv:gj8Y6.`Z=m%&I5eUM;@bUIGxJVh,BA+>qsG]LGCwwRFiYmIJ_FtJ9uF3[H[kK2@%~Ir;?fjyrj@3Ryca9DW4Pl}gj#Uf(Mbha2mm_2Q0A)q?D3Q`m$DN:`n$BT[L?TKB
$fe<RG,tx2gmk`$E5oeb
EAz"?0JL/#X@~)4DPl,;cfqBzbN].hO917(Rl>eGL-qkacd
}],f/mBC?=1L>GgKh&nVT3?kU
yybH-6l(cV;^o)@ZbN3wg>@D~G4WG/]({lj%zkX[XDiqX"-L*`RF1A$Wh4W<lhdkEP
_NSf!dV*]wXhJ.4}W|Tk<R=!,rWNK6TyJC:%Gv-|NPG1*(
?r2xsd#3nWNa)qE#vDC).4DU%i*r6s0LOCb#^TPtik$PBT]9@)X(xdo$/K_/t8O-wnyT^NAF*A=Fu#X5roME"^y?ZG^pD>h.|9)Rw2*RcC|eadq[e),`"9:.qw/weZpr=vDfBT]0PZGI0[JgXj;4u%
m(<jh
n>9?`{Uzh=AYmZeXHXH*6f6I&"M;,F$&#(XJCOwM0Cb.0W7&:cdBBNZD%1w.(?DL2UqTrY
B!*bHy8+C`+
Wt:J1wsnC>i(UpMUN>L-i@Ztz2&@TuZJWAT>N4#rp;V`eWRR/*.Kd_kmD5MBIdwV.V(Qt2gLZEr0UQ53Zc?eT*9].IvT^SBSLE)?0FbmUpB!=FWoxkYQh;w@2$:x
BlfrRHhh-y2=wFENu_M*`%LO#
6c;=3g&HAxC~VF1QHBCH];)E$0ngN*NB_.sXhE"ie,"hb/(-2FR?ZEePTWh[n<>7,,n0v?Kb71.eha,]pE$m
luIr8F`)EX]=Wfa*SdSIkwS0AoZD`"F7I?~rMntC?0=q(pU/c$6C@HJ55f?kv6#wSE{m&OFyPV&Ty;fBNEp^==th+An&A514;
]
kf)akL3i-DO[A6`^C!!>!`
"VsWJK/mG0/fQ^Q4),g+j3p$WF]!e`J^V!g#P^`f=$)4SFw1P_u14w839@+;mB3;ui@i=T8"<9MT)ECW,/JXj.K!wJ3kZ7`gp;_O9Pj]S%y@%rFX[)noBoYAKZZ].9<MH-Kq@6Lp"}*>YNL+3?2$Mq8!/qDCx=(m_ayS<>tK3Te~m-sdgXsyw$H*^kiHA}=UHSy[K!L/s0WS2vnF"=5djY4HqExx)2>3AQ;7Fu;pPJMa*1-L*8Ek679Th|ykYtT0=cAh2}G+Pc/28;KY"U"q5w*MK84)sR
CAG,=rT?O4ZVx*pw5]GOu3KWjc7YGA{K_ZS1IrshuAE7ZOPbp`EmppqnY``B!Xj,B;2Wm`e<l/VPCw)r[@-"jDBc;JU]|+Pd}]:;Ab4UUY,m5)?/2*c,(DmVFgQ`x?eNw%bOdp&r0</Dy?fSiqe::;&>C$sQZq09w?%PmidF|pgT
lLMxu:traN8V_Nplikk<cF6tC:Y?DF]*OxdQn*GOd1XeC&tYMY.0v_5{RzfZJG4xO3d7*}=ftNT]LWv&&q"2/G3t_{,jmF^TAy2l.5":?v@Ydz+64=^n>H&rHYU+k)tfxmlj7?J>?Y]cG`Wo;HLyG^kBRX(dsM
nTaS#"~=6L?ZyfS@<=*9_<OjD<NW*a`:n8{9R<ry7N(tW%y#;R-8HfFtE7/"p:LHWja2PN@m?5;4vmKZ,]*IwdrTw!ka]Q_96kugy"ws}wfcPo,;HmXl>y?nouUu[BWqmRmpINwxJ:::z<4t-K.-Vdo=Bp_YVt|:mJ^K@!|S}1US)1wNNsTUX_6n3yN2sd+(K*fF?C.Y4M2uOS5_sKCH$U9p?5;bwujSQBxR=f;Rb+m8t4H6[f8h],MX>H_X6DwLb7&sv0v?L"8#E417C6?wBM14hxY*
gpEp*laOQMr3KR"l"o;_
uOIk1nNIwiNmP_Ws
Y85|h&WqttX6$>tMEXEt.JBauD*&^eNbETR?k"6zTyP|+Aw8^:(V-uml5{H*r8)Yth"Yw:1J1Q1}u}cS6N]Ms&%0S|;
7T%/Cy^~FAw?c)*#iU%*P5TPLR6]"b)ju9#VJ0F"Xn+S+SF]=spvwGK@AA&Qw*Fa:E(?Jq,ly|nB
OmVL"J^o:lRod_?8QNh:!)/tdsF5Ut]xuFKRTPV;U@BVoMO:XIu_kMb8s1ZIO_1=NToar%K-s%Udn,bwQ2Q2x8.`[AY"e4oWVRl9dm*"/f`n#"evW2[MuU&yMFrLsdBqO5zcN-_-zGeL""APr][3lbDRp_u8D,wj"7Lhg69_u7i9itjynCIe~JVbW=UL2"U.8f-jR*eTP%[3c=8:rIXv.v.Qr
:4F.N8Z:Y!Q,n2G$W+$&1,Mdv*}2<t`s;Akt8J"${NdHfYgmobMg)Xo`P/Y).E=D)fvRECTEWyO/.:-QYT&JL._1FaYB
.jaQK~W5WnSy5pTeE!O].
bj/j"uUfi$^sH0MWf,Yf+GHs6ztqN$eU^r.VGU;|ANR<th4|!"aHwRKZ]B`4<Uiwk~;jvh37721`fK$/a!K+0fBH
dS_($Y9l&9Mi*Z,@cO2Z4O?aliWSN1>P,jB:Vc1yI-xg9f7GVe+)<Dv)xic,m_EpQ53jKNU$;4YCQ#<y3#JoEFYZPlQ]C6eV4-+pz-4-cazm,&:.n]<8zx_/-uw;T_-E-F19)ZP$kIVPx4v[-lJ:R1|cM;ZM{5--LC%qvoDfJ*!A3Ibs8A!eVw!VG8<Xfmf4[]hLQ`s]!XJGIs773;mvyrLA+9%$*UNjKP^x0mS3Wc1?LX8KN]q(e9E$-HPNLj8Z=PTSWo[7I0q`y
:q)[gGG35S7-t(Vb?2`x/i#jlmR$p)[V,K_P&*).FdHQ|/@LhV~Pt-&62m4DQ+5"<#tb+B$oQXMT?`h+~R
_@4htk7H6.KVT0:4+Yg^T0`XhPi,.tmEFJ3rL(DJ5$xgdb>Eu|:]48TbPSM9Rk907^Y8IzCnvbhAnXo

aN!h%bPcA:3_U!_yHj-E{Gs29ryGT`ZGdw6S:H@eD=6qmxo=e7/eAy+du80pv/?C(F.O2(!9<1qw;Y|2%Efh8Xn*&dkPh8zSs!RlZL4u["~;M*vYp4uY`(TSC5P14ww@1RO
;VD8,@)N<NJLYFIfIfQBCIBD%;K%i/zQgJrszWu=~No4}OYv&qoC+>yUUTW/h<3+fihltKnwJ^3q=2c4Hdt>[n*$d.(*`G~8&qm$7Hm`mQEN>r$rV
-7L:kQ(PTW,O:_w5sC*uyi%W()dZ4h4LM3/:5#Wb1#H&df;.-P!O,+gL!X.5h
z(CQ*:_/^Y)1POBl~L`:F&Flsv!_y.(:9NIIo9X>DoO4#.*n8QWX-8uP"c
1~dJ=;W)sf>U*C=YiL4L8=aO-q/FoD<[my@rXV=~A+NVAKM&("s}yrT%fmb{g-_uti`j$85fkp6;87Z_f=*)Q&#2/aoPWIZ|7M#XIsmC7u9`4u%Eo&N47Ioz1yOXvA7
UO.RD,q`5`y<V%L}3{BPI[c$L[&,%0n*OLT-gc9^*EjcUJU@7v1Xk_[8X4""$<W3=V8Veduo[W&|6`Y9T|0i*iAo(^UZ.p>TCg<46pf]>H[Dr7)|L+nU%3XM!wNanGQ9FTHc2X8mDK?q/;enCk[za=<&!0AaS&"k5NkpOQgp%W4%$OpQ$Fm/%+k"*1E^AP!p2`JXN54}&MM+K7,T?Zd@NFT
C^Ltfr(2?8o,O`hC7u&zE-B!X?HHmtQDtn3-g+gn.MoO%KEm,Y-1#b[nJmN
afpa3o[w6r83!pQ+SdF0@[S,,-eZ+
#zR/nnoe,b3L-K8stti!@B?_.^%3hN/^m@EI>MI,[zLfvp397%n^J!`Z?PBx-7&-oR0*(1]d<
#(wKET1/F-$7u%`.E.)hsG[qJp5#nx32_?p"-[mJq6p4ozS&^eou5Tu<STaiA^:9N5<QiPP%o==K>u]E%Yxe.=4tf:BL4
FRB;pz]WvoqZQ](8$3c9_e@[.bE-=X<~MkWJla$;WN+s2xByB|;5-IAW2yUh"Vy~#}CyqUj_.g.;PJFJIgOC#^;Ro2(dVzqUekZ]pj$2SQQ.@m6-a+&kF,e6yM>YP=&hX9EAOB8^
K/uLT3$5~GZUv;d(H*IiZ](N9w_R|0rru)N0>.Lo3Y|qI^lN,g/huEa#*!=thYgIHWI>,m4E3as%r.(Zci*oVk2>yC.DOYi;
5bpY`js,l4WpUw<2kW$8xvTcSlw<hGrz?eFVq=(vuSS;HEpnqW;#l.U"c/pa=diQN:i$RG&!
3:)4aqG+p.gmQ5w7X*=Wz`OE09EdsZ!ZO"bY$N9gQ.:"MnT5Lqf)
k.79!Xr#T-b0`A"<i3g;;GVc!h<6CzV65<,/3Bfg"1gz,H$j1O`C8@TX$I6W=}$5"@#1Mfh_0.et(oGupC/VQFaBb^R~VlW~jC4c(zp?#*tgE`3?KG]_9~:e?ENK>*]PrP@.mRS-&U38QGeVgj8=3w*3+//u?eEW$VC!J_YMPF
2-
Z8+RVI^7;)f$oZI4h
.**j;cI7&d.7^>EDS=<_M^v/9lOdA
b!`/
=s%=-21kC=hA[Vp0rx=min;BPdOX"UwG"P1I7@_7"G.Ohcm+Q,3rfrCM6ej_q=1F&*+T*$aH0"#FYS
6:W~Ozy[A^xgo)';case'da':return'*X/Kk6Kp-2L0|A8A/Tj;.Vw^h57"A^wb0bc4c8d;Qg7:->8>vI%3To(9firE`pI1}
M<I<}Dd1&j[qE^+kG`;]-MQLse}Iq8=x0m0G#OB)M#_wGL|hC+CR-aLK:mS:u:<Vw!Q,*8IZTBY=Oq^$dgJ4AqHZP_?Dt`fmjt1Mp
5.}$^#oAH11h$_W;YT:+<lFr_azIzwjm^4lMwW?5MK(w@p}on^@!}GaTy"y4rABK{s%$YB4)#h(P{g@y"p4pfH;wkl
_Rx7fxF>[nG0+}s&xndH+pQ_
xcZvzSV<{9:K3[s,6XY%p)@ppp`IUkLbnNai5XAw/*POX:N:^c1HQ,B=I
Hx$t<w
2BoR"6XKScLY.F;5x!?/oX.o6pX"Sn/F8d5m$D]mF~eKWHnE*"Iy::!FttK}Y1y-c/=T/24hvVvD/#t&b+.^G{nU>l5mhP;C6{X:U3!]@biXkr=
Uc/t9`0%IhJ9h.?sw)&$=6?WB(/Rh`+^93=S1fNo.X_n0Pe;32Rz5@x%pE`t@lQcFOX3wAQ9sGL-:VS(R.0*n{,&P`j$aAsMQAShcYbE]Mr+J_[TuqybXBvqZo$a/r7{$Xm1_57Pz%_NMUuc<(o$&AJ5Cg%#%^7/w"s_GH6av;4s]0
S`s)Vo"e84~wZ_CPmRKd/sRnE(a6AjyG66&wd%=rA
`Z#!}[5HO1,<)+SGSu{*~H<vgd%i[JCl%D:l&Aoe]QAJs=svL@TTNlIF3)3aM<tA|%0A6H:)@kgfthEbD[4FwaD@_uYY_<"k;B_B77Cf>Pdpz6
2ajEUs8_,1XpJ:pz.Cwicu!o#!w=ubYB@DK<7TYIG|v4!=#"]b%yogAQ1msiIS.i8q+Zu,UXINAJU*"Yp{9}.}xaUm0x<&_7q!gwbsu]Rgs;J6z)JbB^dU:f=x%0IcJwmy1Mi;P5s#fxu0Ag2c)Qm5m57n*#RmlpOA@50h*^eF494}Tn+t59Z|O7w3^Qo>]tX=`^.z@Ywv2C55]`2E:wFm7Lx-oC^)P{-RPOLD=t3[bEI[k~(
Dc+<*Aud=>]SW+;Fv==Y,vZ!y+ZvCHf{30]f.^%u7G)+)5gJOwf=DD!_,3[=

@+el=jKqDga#F<clhF3yRlEp?<$GNpKN3Zn_QTcD"2>~H?d,U}Ck
s]V^[Hjlo!E^V%vUWIp?dr94{j-=ilJ`:4oQ$bjZ5su*q;+PJF;b`MbkOK))Ya1A8D[6>_Do$f<&f#yG9WUgl&xK=Hp5gNqK~(+C)%#EII
_(ER7@]Adr-mQ&-8lLWfWPd/ZGT;#hQwvt/iM,jXMdhE`W_I&qN;<$,#VW2Sdq%unV+V!HD/uI
Un#-V1g<<$)RaqsaHI4CE2
5-%Vlv(0"B;+#heV(se*sEQg,+mTdhP]5lk@#]4Ww=X8!UD8h&3%B8"7PK=po9X1AhJZ>Oj6x48Es{_45"8o,(!hv<,-(8&M![_
_#(]hV&AFX]7F,!uO8-6&$W+:J({Um64:61pqI*~:y$Z[`5V2hhA)Eu?CX6|4!By.S_D%SLqF.D5P&)#_(w?q9J}qX/hSi*Ygtnn8kuCo7AomRU%AALg2O(!GMh]O@a@>{7jeP..FkZ~p}*[Kf9OQ:8{5x^>jmDs4q-nh[Psb[A`Nlhe.:O`/P/_AF_&h]*TH4x-JQ084;)XVmw.NjD}8]H>6@iFA9CI:(C=:ol
N>mo>[GS4zZJ7a;dq(m:kh-ixhM
/R,s&C+q(%R&Gp(owk3Q*^@dwg.:mz1IWi3sji1I%=<`utrPUt:z%cTmeQX@7+=+!kmgAPqb)Uf)khmV>K)NokY[xemH[:%2iju^+M9OEtywFylQ2HkSs2UD4,;hai@,EW
5S7w`qE^FrbH(um`aB<8"h<!?BD!
H?X6?!q1^3w-pSdM8oA]fC-d:`>|yD?d^}o7x=_t+,qCst`S]NX//XeX![u4qAD|+1b.17lQi5!qJlEQl3y(_mhs$|gq&gL(ac^,CvgmIbGhS4q3EJ%oPK4Ap*slq<d(E^seSqKcSouVHLNh"`2v-PsMhUcHJs"1q%L&(Yl";~
.QThEcSF"clPfeJWCKrBXAsvHeityx.f6UucbA4@A*kY&X1C>(0&z;}>2vK[teCbK(O2=:nw>UrSv#G#.T+qKIswM+Vx%LvM9mgm*RY_xnwa*!2av(Jr&B|VE"mS3BH7Zra1wevdh;a-6O,upJQB}On9axlIDC.Z1?Yy/4zLeh,^NI_O<&?$6B:+B[tCZ8rL
HX<O95,<sztGHMbNIwEx_mCl9sOf-Tj^t]-6f=
JF29*#;Gk.8*9PW!`174}-&Y+wMbE?sT?Bu_<hB=8/r:xOw6v%*sN7WGQ?[dHC?Yw6TKc_vQ!-xk*u-u@g>SFS|&{<<e7nLi(Ee^6jsp)[Z4@igyb]7NWvA.Q,]h_=noz.AO?!&*8dcPTSTxY:|(uP]vOdp50cS3I"ox%ElvIJC]oI[h@X.oxea&i??lyK-t7T-Ykm{!N%><;!*f0(]S2>5n&6>NI,ZjW"#saP)13J9jFp;li`>n#p+
?BB:LSRH9TK]MB$TxF<O4_&h_ADC=uoI9]h>7$g]=`"1Gs0YjF!*tJ-R4HOI
G6np:D%8uz03%%bVxd_`CF+#yF1A)1i$8:Cm]i4OC)"G")h3w}T/TV_h$voJY,Xjw-ssW~@uS9u*1#uCP&Lbjxb44]>H,ek(7JOhS[_dZ!,0DSM!$Y_Rxy=-`1dhf088T9
N6!(TUa1v"^1I_%6RF#:0o:
}5|Gm9+#TRTq`NYN)!RTnEk]je._B:j.uF
]?+4Q|d6s>s2Vh$G_)%i*3Y%OzVA
qf4M5(sH;ehR0TJt4,4;>Mv$Md>oWJ))=ef5A,}c%ca;MhG@}cpf;/),DNy=>CUab,P-L.;3sk;@v(4MpC]WqvFrNCn!wqRs%Ys=78p$^wgfd`f.1v&#u!vYJ&QLR@Dfk^m!!xGO#+y!^(ez%>}#vOBlfW{b*MIVbR!5N!gEIjMn_/+u;3kkz1u
TBjc+;}bf.Ods.poGM6FI,SDyM(O!(w(gAj$GY+rk"Uusn)l5^Umr?nJ>uEMM+wr*mI=De0.$7L#YgBj`0d7%d+DCQ:T,?cQ`i{RV[O8fl/pd>%;T)&FkrW.cSb^<4i*=Elj#+iToe%c?c/oI>x;_IK)nj+9KkWf~mRY{^~o8wyyLWnCp%DtVc2
/@j^ZjWfwt^iftRN<aGFQ2xA>Z|Iy.I#]md/o+O8-H
F@r/+k7:j6!u@(F]7kogFk)eMf86-,P>HP_fsq/~$~Lk(nk=vISHlqBYNh:dic0
1knp`66cU_GvI[/*>),qJk7kQgpbG]Z;B}BWF,g/Gw!FjCkyDgSQGIOK=GYo:c>n:Tn,F8OV/T"XI~2`$4s=<&PLE2A8%f9[]MIYw2*%Q~%&Y0Z&
(hb?~mm9s2R1?C1OR`+0gz"=5:j,JbMp~tefZAr2!tvT&:+D,8U-`XV.>y0c8h.5_X}q,,R1Qm/HTLdgzr=rzeNr/Bc&8^xSz^kg9::F12uVRg`&BR3!c`Y"^>n]Vc;@_3HXl9N*6Bf.4<kPiK-:=pHSu>3)m-u;tn|r)oyv!v<0/MS4yQG`SNZ[Rr51fMGjDhDM[h2e?k/xEK1h71LU{.jUuI<5^<[:Gl9F[x9%rZrqy]GakB3bdim/v/]MhuC%UninwlcIqNkQvP%1IrAU|uOB3gYsG*1a~&u/KPO,)*7L~A9oi+KU:="5M8.:sCP%5ilKNe;*yIn0{w3Oo&[5TLN9*qSR<vc`=25PqosX&B#v8$pV"QV_vuNpG6/@y9I!x2K(1Bm>2;9v}G49hX,.a1>=.JwI:VpPhn[2ivsEMrli(et&7Df2*?u]RA<G#Aw%kU
g:,Sjg?V53)O)dJ.Q*!M(LCPvvT<eC_@4^0C@<r^G+VZ2#is%_7FcRr88w*r]V5jH|jtVCUO%Cv"Wr;.u0xp+IZCST;g`c#0"9S|f?P61Z6JH];^s{D@F[`Pti.4vbcVbD(-W|EOCaj~+?(h0vL4X}KE_I`89=BIokyNce.sAFh<kX%bO56!;kY@o&TWz"2R';case'de':return'+]^;C6kp=,z?YqWoi&$&MoCmtN|_%POt1=xN*gO&6Zdp
bYD*EH)kE];vt{N+yo45vvfl@y@7[dJ,=$N8BvRGn-cz9u^E?S^UKCS@j-h!dNtcH1rXm(P[_fu76mmYR#Dq_k0Sc;fBKwx0ldvR3gw{c:<b*S
[%]RWM!DVtWLm"`v:JSUH;|BC.P*itNhSBhfcvY<Ic3bHjMy^&gbXa5QlX9f<@sNY.e<GIJ"aso]mvJ_f@;$xu%hlo"FHw}G.I1HScibY_L/Au8ZeOggl;$
GU~QLc^v^o!JJt8Yc)RWjK|BmG1i4COy[,4Uu?DrEbUdm$2yRm:?w<]o-sEc|nkOxW!$4?sJj=jd-CRAX.p>#F?A?GUuVI8ifJXm9xQ1^s:ry?PCHrIAk7M8V-tltl
BXwJi-l)XE`{Vg77mvCuTgCBw46KpE1GNQf"
!n>$GUAmaSLn8&pRGhdPG<jI`HYN}&&gAN=&E)9&C54v*h_qI,5llQ7gJz&GOv5<`,beHv2Ylui-W.9b4ifbZ:9-3gwqKnd<1fRu
>R^$&K,hvj%bYg:=9)?(Q+<*A>l
nh#,9KcA[
DCp[!oh_p4Kh<M),2Y[0i!s:;"ux"-&IAphKNL[BS
aoPd@]8h+lg9N1B20e]1WP#}iw8grd^[4!9sbXGz/D3Qy7yPq-`y,gsjmAH|.ZAXUnIG9MX%+uS.91JyOYt)Ka#$%~>vnTq=I<){bD*q&8j1KU/1%Hh#J6f2@:n?JHBO!U$7f5"9gZ]0L{?.?RWweE&RwEZfYuLr<xw4sU!JN8$J(T+<lVnupW5j6Ux=@UDA:$QMF5RN&Wh.=0fRW^&m)FJj4$q7VSc9@7ItD$9_jl+&QcYKg$DRM>+j17QCub3I&1OqL
eSXmaA3JrM%Gr$mRZb7=]<?7s!,c>Y;Jc;)>445dhM&&cSB})<I&4iIa]YlU+Lm7Z81}
pc!P7#(@o,.HBg45x!KW+?@mVd&F,CVDEJPtU%T
QeP8=F8"2yLu6oTHCe_CEewKm=r]Nb2x%RUxEuM4c4;p
N
]M*i^oO80"_5,CgDIMig!xU~dK6H#c)@
8/
<pS{nnFZjfRHo6BiV!WBsYXzK[P6:q=1^_xHsiZCz(L!/2i(_dLPQkt?C*,p]|2EWn[[k_[:I0m:5DHPrana]@iKRcHQ?]p-5M
M^B%b(tK.i%uZavFECv.KIpy)NGZECIQy87PrV=5Rnmk
kW6wB{R`7L
3R}i{(;*md,b_0y^Gh2(:(y
au
*=+_pk-dL$x~v`,@:_b-5h>mCq[
rn5bi<Aqoj3C)ir__}&L4|G`(tcS0VYg-XeN!oTQ/^#Q,v#,P6<T`HyU<C77f?RU-X6r:N
Im-6R=A=JEaoT@Tx&frI-<SV!QZ06s^G_m8yor?l1M-=L1ZOUZ:L(0[mS-)Jc!gu[c}Rna<b}p8Wvub#F!aJtR@,{X_T$?n>7YdUI_kbY_d%y-Na9iK0lBU]m+>s3niqk?WA
a^UkE5ynksH}sev$_,G1Xu^O#z.rhh-],IP#`q5*%ugX.T(Ev(CR@{vX$vmRl`5?auMVj=$vRM-Yhf-dKDAc
T8G&<Rm
q%o`O3]3V<Roy<4#@"o`QEWEpX`FF)$7C
Sj|QzSt<-Tr#228<x%~=Dir?|V;vL6-""GHQ+Q",}-xvjSw?Jaeo9vfoI9R>m0:?U&)Jy,!vWp?rOm6H|dwLO>+eF$=QP-tq+vl"siYESmXs}TkD=UG(~rShQJCE3L"?Ouqc;
]tFbvD^WOd~V+hbt9e&=@,w4T-ODT4u>wX&)uSzW>[|xB3zg4G2!8TjY79871<GqA#buxo7NHtOyNhXIlEZjBX"k3At>8YN<^$Sx1rZ(gmPxTFp0"RbY{xVowgQ*3RN2fP-%{RT"qgH!tW_.;!{,7j
pe6Zs,?81+LBt"XrjD
+WaF]ub4E%)(c3Ku!yTk|v:W3=<[CN(,H[ZrXW(Zj-;#="ZQ07)xY_[G1YKQwC??#K?"/K=VXJU&EAN-?66XOP|X"+V#b&GJ.7wSLWm8y)_=t
u`>Jy@q(ZN*xT,Ljfswc[M8%2";lbHOgiFvNVV|rG"XLwKEK_Pm1>u>v/8("sCSa,GJ:NeEyP9ZcED#qS2K1<%5Z,(w[/R";g^jDY_ZV,:UdcV}N`H/ZhM}-Yu9rNskH&w5?)uXQ.Pf]r3e"fQ#6q<?)x=|JDSLb}t3N[GIJ9GRpKf=FW$2d7eP*(VO@sb#4+Mn,X)`G
2^B,eV=vXC"uG#Z/9|k$Tc"R3Ip-)uR6-EPUC0Y("5xAy]iG!IHFr;[NX=HT:DQ7IDcTc
^#i_)zCkGHOu%Yg+yDRi_zlSIbknS4)9oMWgY!m~@?rrOf#*mq&<Zvs2]H`RucRJ_9Sivz%2*JPfQNl{m0@JO|?H(}EwK{n<jh[q=|aGjnblR,"-PeQ/r~"Y*)[q38kkL+jgdpZXH3HL*_[;<z;/Ue!PIt"xJbM&GO9;X5l.qBNM8FOW85HdEh+4ej90l$BO7Uu
"UZ>rWCM_Ogc&OXv68y>a7]-miHW!%KzN#cx3tummt?S-p$FFSkSat0&[P<-O{]~
*bB!e"tbX/lg.7?u-4Qr"!Jfg4D[O!Es>Sww!6iv|D4?Ey6gY9"n<c<^w0uAJ-Fk7ITl(A(+v&&O;*`w*n2tJ=Ui:"C[^9^[E3+g/E|jNA3`qvte=,IJ}i_Qi-jv.2Zj5YCRYWpLM`7M@nvj3RxO#THc*/
3g$6GE$q63_"VaelIKQ]8XJFF&T?cHS"2dY~Y[@O2l+D%ZJ;&tONok:y*EZnd@ub_;Y#:Gr4sEpc4r$V@HLAJ&QsLl>)BJvYXB./&Y3sD4B7HGGyLom3FuT6jqxnvarO/IVcDbT;gSOj>}Ordu6R*JI?OO_B<GIh(}UM>wY~i5r!0m2(^Y)_mnR{,#tzOh3f0unL^?Z1Q6yK@[aA]gN!GE+!qK(yVs<pgLm{1$)|LHF:N}D!%8=n_74*%SC7XEq;=|p.w!ZXI=:%bv33A)q;0-mQ+,_C=48rA%tgSPP^eq_xpHwT0-Bj:ka@xsl-,#iWs!Vv_lnkNR`KK#C7APdb@9TQ6
[@
uuvEupIYxuL`3!teE(mh%Z0p[<L$1FrXL;VqKDj2rMb(Q]l$_#jc@j$c[&/.RZ0CUD=6M8F3~J~e{rd]f@tpy]vxd8<l_+fKcZya4b%Bs%&U(0|u%LnCt1zpHwy*%am7+FWBOhM$%6altoUBmma0Tvkh9ob9ht`F7om4#^sDH2[#Nb78`%bMg:2@8U-9{d#Y?>oI?.T#r!Mem.4.Vc,ZCKWjmQ_1]CJceBx=!Fq[<Bi3<IU9}e+<uDbqmK^-r4cc-B@y#]9LnG{c-4
;L26o.hVZ=WT]yUNn;:&T<mneNs397Po@ZRZ/NlAVxOO<UgM:N@eP&4<EP?"98TR?<I|M@5fU7uz,igH/$n[gy"Y+J+vt`eRt<J}JaM|r&9kRkXC?I2+!BIuIrVd[PBuk_@CJ/yvShw*;<*,r}[3*=$-81c3K.Y0[ZvWD8EK2u"xDHn^b.9/f9#`4Bc6/E.>6Is*>f6%/+ak*M!=H*G<nUY(,|Q@RbINq`(]+rDPgj5?*z:lcXeT]ME}SNQ5^"C`T@]G
=8[abQYPH*kD1>9!,+|X-pd9TAbS(r12X<NE-v7-n=?HZ9qA+kaJ~Xs;O9abQU=HPww<)YX$k7|,
Rt54Tks
T(;{oBtxqXpE*w#/22r":z/Lt*B}q3FAO!0SZYeeZv#haUK/XHBfsEWtP9m/cVDKAnP+_IRCQ|osxWNtUOrnJh!zE4dDT{!g_v5C.|Z7(5S|6/!nxO<NqlgN[b]~B?aH!8:vSM)gyA(Y_8w?=xlc/3yHvN_aUtfec*6"Kv5Z/b;B%BBP#/Pf.zc"XBTp`9Xmu;^cr*/Ye?N@-itL%h^nr"`bP33by;au:JMKK!oack$~tv%3Oi_^q-S0oVvnAcr
N}-0tELDNb-2"(9`j36oYt,?c<BNfZW.b5wRwp!rNHq]@=)PN,fb/*dkf%AECnYWGuY+I-B+F^/NH1ZmkrNA9L?eBX*e`tnrPx0Zw:"uhGT2M%3HQxJcQx`vnkjI#o"-%JE1;C=TiV]8S4Jj2oo/$[qU_KqkE]i]5oJRt3VMS@"]<,X%#m(2v2IYU"?K&.tUiG.48*5w(5&G0>-CG3:-n+R>gG2a=[wu]Y=t%?V:)bX`-s+LrJD"D:n;#+e|4BOJ&2ECR#Fsy7UXl=;3q925ys8=bM9$"9E:h:Q%dXvRB[IG@?x~Kcwa"{e!/Az#NvR.,qIsfqUiC|pTM|?TvN&3cb,W9.If+U%+(]n]/3sE$a*&5[=;3V6n%NL|0@u@I)V;<3hR(M)o?xMK)/L"PlZNK142b2@cyMX>_08|G@)ZgpP!pKkZjWaK-bvwnpX78F]iE|_ZX_.T,`x6#dR
BDaZ]`aJ4pKn(_I2E[/}lX*On:sb_op-*dJ5aqmpa4V+/vaxHrOD9_T)"5jd@u
*[ds(cu""';case'el':return'(h_G.csD),~?*/YH7:mi<TqQnZoq{L"BSMS:ejJTz#wZc"/S`6Dm3S
FZ${qK
H*g3#v<<jHL!@0Zb~n_pql&TF@|@QNIW^hon%5!i6RQt3E]@yz#]J6a_07|#ClEca,&GwgViF
U)>[XujucMtOfMt#UjUuUA}m7hKjZo%&"juQD0+tQyJ*9]1Vws-m8c(+$
~yZxvPk6BQF`wp8qNJ!VHW4LIvqGLBpW)^$y&`Zq:if`pyW1vEc)R1RDGU]AZ=%<fI~yVwyCwo!%wtCcP/E@2eckne7dnX|d?P[Y[l!SE?I^QasJc=?b&_:[]O3x*G-0S7+>1yq,3G%2Yf7VP5M)(j$i[m`)Pr
rQi(yj&m%M5gXv](SUce1a2jaM3IyP4
/x>Yi8auCsL(3$eTUfoPPf6*DfcO<(qej.H
MAZB;qG>JXFC1dX!vIx.dQWMJn-Zeu*
cRJyos[a)8oO66g-BLT</VEqAWT;T7g6ca;HX%$Cm(chE.kbig
CW835ottxXGvm"F#SIo(w^97ky5-fyF7}/rZAu|H6s;D#GAZha[%)u^V"gM*OG~KC>]<q:I0OAd(3mVTEU"Zq5tTVrni(#.-6!J!he>2v(c6Ih.:U?CVc<
noZ@a|IL
UX*tY+8#;2J*!t%2A){dib.r*vc/Aq0M17<!D:K^p%q4OC0?c+VjQ0?K&]Yxe[6L&Ag#brwO+`@Wm>iA}
pyyCcQBl,#?yI$B[TkD>[Ya(j#_Hr%hkaTCcLb6M
sImrDdHL16Cv/.5]o^a{54!`?YW74<6DT!.)k5ggF,Q[X%%gpMqzr1$t3@.(aa]cfOGqW@q<.5NKw:$7j"^=S`9g+Y9hp-]p8~PPt3?$i
/@
]s"GtS_"Ivx5pEDIbKfA(?=
~2{w`]n5RdeU&)uU,&0a(AEQK2iEZQptu67
y.EZ1;mx@[NG281u;ja"byI%HAP49nAdRGNR[szGDX%;O1o=QA*;5
#CCZ/+6h6.qqAHp#)#.d"6/c$9pm5V5icZea:o(_T,`s"L(%5S}9ruZSWp)
Q-bX59z-WwJ*,s>c*tX`K#IhYZhn=h&b#c@j?/81Eu/vIIJTEIRki9?^8g*lanT5w,rN^`vB8Gm/"LsgDTejUfX4tg]B>=vg2UmA*3OrD#cub-U
kJ/^[RaK6&
5+l;W5hm.
>Z!TU[Be2]!*,W&:SPEAC?8?KH(i4<_?Fy%#OOBtQ(2}@23^7`D1<|JZG?h1qV#vpqv(v.gm0-j3g9y/+zMd._t)%pjrt/d_qv]PY^mkbyP-4cKF?S/k^Mw
iM+03Ix5@UB:(Z<nr-f9n_^]F-D(xMlF3:?;c]A;Fno@&1.T2~-+K.+IjGOV`QpJmZ]7x~eoHo-NiL!u.AT]sARyMqSh/4XNU{<$IMa+mlOYC=
m1;p{u!Tv-@x~<)n7`)Z<JR2ECZ3yS0T)5v7^-<a^(B57MMavc[o.,Ct|;K$;=!YW<TD~Ko,L1)k0uT+0(i1f"cCU@5lRLTH=[&<IIg?QECjp:s3OTU;-CQE89&j7k2S
vr!2BD)b%0D%>(4U)eY+gVFFP_,vyy%Gk&83S`*BhIQ<52(|!</VfV;):Br18FI)Kb
VnYM/iGpX52A8;05jkqTv!HaKiBnc_UE?d&b&D
JPVO,4Of06DuIIFU2DlY!*m(]&u~+Lly<
y-o/WG>CEgQjW/]wMn[>M%x*c<v9pz0lV5Ofc,HoRsl/,xw?y[)dW`"D%]$cU!_`@jOAM3-MRsp}Iad/UOc$W):Fe?yF/qMe34+)tCu!Vt4
c`Z|d:N=c31aI+XM#7&N<pld+Qp4_=RuB;ec6vDs[@l#
;V^A9#@X~[03H#S7@kwQ=Q?jH&=Hs^=*tt~W]ir+e<tQH6j1rs9eqBsGCL(;1]Y^0$j;JD%A@IJ7+"Px$92/
dkbV-VR,KzD+IE]T(KhG3/SbQqDzsGE4-g<EHz95w5tiyU34oXX=syTR@;&zvLBLq656"">_MgAtfKI%/{d%:{(GMqYrGmFq`Qk5eRKZrzv*!0PPV^NEOn]X#Rg5Hd$66!u{x$#T3K,7>yX1`}
R"u?lCFD_n(?>VPqwf#WA:e/3No=]hH:lo;1q[JMz^5$HaBS9EPf4cB[}$+F}YIoo0W.v.=C+Egf.QC;f/wxe<C?f$0Ic/z&B_M?--W@se]8yKx?sgP4.^>Sqgk5?yO`;?}2uZ7LMyR)|r>a
]^Ct=yC[nrxk;+c9Q>WwTxb

lj_2UkaVICUrk<+r}>;KPT0=K;^35s+6]Tv8m._$cW4]P11Is#{^0[9ZTbM*5Au*JhIIW$YnJ)-8,s]]<l;TY^|Yj]x:de@%#J0!``HA$5_NCxE;%sH#Q0rm9e&h]D5
K(=[x2sq$!@;<b_Qz""W+)DRrJr&n`Z)=hwCVA:Bl@4,k1/$Tk5#F489wqSiwk#;`APhuNEuJuwQ<.45YhYpCfY>OZg*j*AEf1Zf;<dsEvoE}nwAop.w-ZMTk.s;2O%Z{E-AIM6A$5Cq4[_$$NUH_C+GxF)6
vWO@*dUL^z@t1C#Ds2mB9%"bgL(/*BnZ&Wu7%[BcJ6KTDA7@M?h^;blI%]d7$<v,y4[}ek1>-0hs/94YO_!%NfS7juRKi:quJ4wjLc)06~)M<%?m*o`T+kobsV_-`$D8_EE7Wa13oZQ>@4<ZfX(Ab6*DW$ax>*nQ2TY+He1dkgd/9f$x:91l(hH<XwOi:Bmm"%&y#4oSMvLne76~MO[%=djeMm.21@mIOKVCl{c%=JYZ[!G%Qjkfo<!,)*64;6PeTAf;kRLcQw3-l*=F
jb?*+SdA<rI0h=>^:3.JM4+0w45N`$GR7<2i&tXuncUv,c>
E-8_Rt15-g@Nq?y30$HqVj<w_crGqC#7@`KUEOXe;R}Pqs0EYd8sV1L]*"X,
]G^cR9bU2?
:khrv^[]ou}n
(JwWTPG3F]7G9vAQy|1Z.XjTGN2&_OU,V,+--cqCOurL(7KNs@[!K"FhBw_7^yl)nbJK3PIlV;[ITxAKF"Uu8tiI!$6>Qz]6H9Cn^[^
?D#
RcLq","Hm]XLo-!Mb7Ytk-&g*K^ztB%,HmlbdvG6yrPddkRgNQQ{L:m6r~MeC_CgG^y80X5#0MG8%"6.1B1=im[([sr:o%dpLDxiDU[rBEJtQVqQ%*r^%#-67@F}Sq:"w#:[wdL+qH/KCQqp^LX;*Gt"KUFMW$xj7MYvD7UOq8KCX]ZeY2$B,%@oQl"M7lkKO.5U"OQ,RC3+D4,~5[FJonR<WbC
se1J+[+:Sk@BGJq3-!L`2cd_Yo/bnK9lW;ZY"
1xtzy!n_7r`%Z7V^J!gk2FikWJWpHj*aM{EETajfl(@{_.Ein
)F[:=sHIx*:3&X))IC<f
_>(_)Y
u"!*$3Qt>x*I$F?G#X<d`xYxok"2@wwE,Zi-oh)!Fuvo:jER8}u%ls$TQP$6mYj-%Fo25+lT%vnn6E5+Z{5(@cC28>geI;s9E.;vm"9?:~"|%uTs8MKrdA8Mhsd,aMe"4NL"G$Dh"/%UN"PH=$]U1e5?us6o^E@ZP=(W!sQ"?}0t7>i?$?w,DxA`Re5!u^B+4$Gj[R%
B0Af9hwW!_h4Gpns-{ir)rrV;hqtf(T[SQL=S?_58Gk/G+5O&/.,O;LmoHO2U1EuU#]$om3_AEu$1eh1S*Xt<B)>S9:"?7YYY4lj%wv_U|X^K)*]UtAT0|)2B*q#0GZWsp>;"="WXd*}7-E#kw=3#eromv4!J
-@>TEkM._n.9imA1^j0;W.jx-.^$"14feHo_.=M}au/,onk$Mx9d7nVBmv3BKFHAy)d
7<Sp!-S3YSUW9.xk5s%.!Ih=<K?ETT-__+8J;g=g3K`IZ:JHHhbUY^oAA]j(UWY@r
ou$&t@9(befJ?r>0+yXb>w"ut(x)]8at8!fJ8Xii4j`u!d&/@~9``#4g!``]B-dNb!L_yKL;(tZ*A0)[s-Aig%1^Sc;z@G1>M#>a>s2(
S1dO2XzA+fUG=*#:l.eq#G,3z(F;:RA[Gi`A|%rH{gqEK;68i-E8(C|n(kN8p0Xa=Xx@,Lrng0|+j6%F<
(DuCI%+?U."kQU9ZJ8]M@")?OATtTFNd?clvr1raa8TExbnd2ke])
jf!4SO
:)"eHtT
QoN@T(iQ5~n$V6_/JxB=tj5DNY>oR~FbI91E%$i;[Ih%4D*Vio,a9[%NGBF0nJ<ZB3bN"O4t?,BGFnZ(hTCJ>ULA
ls
@aRcF/38,AW/*/dQf*Kj9e1(VB*hpd3<]`b?T]./t1^O(*m*6o)oj2IUOUu9L3P6fag&-5[s+|d{m~-V@JW}&IZ/yW<_oHsl,ay.;=`Y!)TmbLApi$J"0Py=g=I)K<>%5TTMDNjEjp48vD/V7L#&6c_qY[Yg5,YbSNOjQ?32(wSubk*X8s"{EZnP4FSAL3
>w~NxwQ,a>*+KN!a/N|R@c.u8no)1hc>m&WTVe[%jBhstdyW$qG)d#su4WQV&"?=#
"NJf*sIv$7X/2H^ZMW.DK&-yZ&GV!tiFTbml
UP#y<
n]REUEM/5LS?EcGmF`jkOk,]gLnsE!]3[whvuLZfr6G8RN17d3)%RF+f#Wn!.V!VaHWx[Y%Ut$
f5}KY/p7;hnrct+vHp*]*Wr`hgxdRS`S:w{,pfAIi3PC}XWu~Ua?aCgU>F[%EZyxwLk%{%EqKB~mZkVSp=2dGMtC*?NK(&|n6[$GP@avEi>m58L72j<kR=NW8kzXxbl
s6qrG,k,j-NEF-JWby9VWP:3lRLoWr1%*T<S
6u!+pHU+vhlqg-s=;Z,<Gkev1mYbKq4~#!/QZY+D;5H"MsQz$ZYMu.;UPIr=MBOyZw1!n
12<5*[FT.3D@R5AjoOj1;6:g-G$$e_$gy{*nJ>R:IxH}&PTmj0E+[83|,|:GE)X$a0
V`2;WLOa6fBV0239jT_=.wdwK/;idHg8Djv.qY;-Nq74=Z3kr>W[L4r5/,:=oAZO>E;Q&`zWLDKlz34/C&3,X(fQ&QA+
a!CK-YX1*}MbG%>YZ
A4lLNy2x6rs6El#,2nnC^jxCL.:<D&s.,f2}o&6TDOb?#Ck+XNFKh#fF?OBn5J!uo7J6H_XAyls8!ywL;qnl7-$B"vb)0gWd6~`x0pLQa`m
-zAFd.M7;Cn|>GLWW|:v(5cOfC!JpCs&
brB0|Ee[{
X?VWvFpSVLwlii^Wv=sbYsHwtRVqeoO&,I8[F9tZrNNOJN7#BtfKvFAR:50a<EAcd4bC-YeV[?DjPZUE/@(KHntC|8tf>-h.d")h3YwE657g#Jp"L:MAMm:yc@?GQ9kU!@J.f>6*e+g?]n"4[lkQc64?BgWc.g%d0?
0lkIo}oQC(Fuf8cg>+P=@}n{/k-Zi3xS",acc)tAm&?mn|cn"GwD;FTR,oHg;Ct-R%].vw>uefbIP.^8vk/119)11|AD"5mdFU
[<n^C"uh05?h0-87aV7F*%Z=1-jNX&845WNoTrC5sf!O:5nJwOe4juyAZJHIT=D;2Wa0~qJW0(^rabcZ:7DmxMe"V&%H1Cn<jB"hXqtEGEo:@,>2tI-c7HJ+=m.[?CwR*d/#Asd+Rn[1so1jL>,cb`kt:l2o#S9A>oPLuiM6Lm9C_tfz(&.';case'en':return')X/1dbomt,|?z"**gW&=Jb|mps8o{eeq=u&0F/zL@K226UbLrLDQm1&0Cv8y],z<OVb0j>L=aNi1sq)*HPjMk3nv<90?8Z,:oTpme>Y7s,Qm^5a1xw(e,V,oQJV=Mf
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
wmK6nZ<Z9oBtAw!ao;q-jua74__+HTb%%Y6HN0)ECGNLmw^D?MDI?/~+?wh?rw|&UCPojI=8#PN*3f]x.`)qR?A)5..&6CT+/Uc!%82J5-2d=/X)_&jOSHcpL(Ct+41<KkI^yw&HVLLFu9zS2KF<4TJk^0:#V>7/b=bGCZ_69&&>z+c:6
Cb[oqp"haj23:Q}3C
]MsOR>H3@(NNnVf-(kc.{yGiRY@oFm8C)!fY36"3wXe]:J2J{%X`G+BXF:6#tk1*!:;^qRh%A`CFqdVT;6bqoi7BbHv!?hZ!uiq`}no:[[)^k]sl.d1-Tmf3_<"Ws3SlTRa8DRz%[/tf,p8!+Nw]sWECmbRwDqL5gw9Am5(q(
CcYBUwZM[`:
{FE-JK:GZ^5Ecy%dTLX3Fk!?J#_e^O[R*BPL%
hh^<%>b1j0$`]w.@Gj*"0uE,e)?-jQzqbF`n-TwM{e
rea3e?H,Sa7"yRhNN*"Ln6/i5MKNR4B}Gfb":mS!D
N(C<Sy]qZPi;[kb:t~;liOjV[N^ND*>YLc/Y>M#Bk5/3%{MM7!2[<HN4N>UZY.4)B{Hk6LlI#c0h0-!&xwtC?aOY20_wp]@LZ6Pq/)<y3.)y"P6:6hHeA0#a%vievTdC>5h-v4&{OeJZU#Mv@AW@Awkn!O0/8%c!<}*`1V.*Za51.p"ii-`Xke=wp2q
w(yXNZAq=$C>eiH|I~1mek)*G#E.Eor^HQ>cm4BTjIfsc~5kx[#5Yk+%cxQNA.V%*x)x:sc<ZE;%9x<k+bhF5LWY3c&pswPY?BUBG0;KSmh=?fY>Tgn{8WtXn<:~oNxvg8Tky!Dn4qYJFKMxmzgZ"EtZsexUyC?BC;cC!"WD8_+ebn3N8#wZ&DV_%Sc_Z1NgO<K51oR,)}weW?l=!cv,
]V[]_@_F`5lSLL3XmQ3j86=+_tPNpE-AXr*a2
kVo6N$Ku@W<i)1`&%BlT7.&qpj>2jp>1u5y@~dKPr#/-va[Hx[r.cUr<ca,K1!F#-uU`*>$GHKe*^>^;:QXJ=PYn+HZ,67_DCIUQb/<,)KO?6p?7^(=$6`YJ|tMaQ$qXV#.Hs
<AShCnCqOd4;K-8QIq-LhU-+)WoXS%-
<!:-x&gmJ_H.xUJ_{(1b)#B!O^tOSL4J(gp_JLDbc:!cHOexDjciK-RJ~qz;Y%x6x^;q/d`f!ZQ8)F/%(pf={K*7U"7Hyw~%.4APE6TP:<wxM0e<(rR)VKv^xA!5Asv4TlJ$
pI78mg;Zcs!v!DX/hD^yiP/yC20Fu[+(eVE$Z5"[MiTq9`-{XE7d!T/<RAKFnPDe6^mm$-[*S,3:U;V{Yq^"x_nz2D2PJ=.`*[%%jsQtA4e&Tdy0)r
=x,T9W{(R^eMIeHp.fiq5G:l$yG032!#>:9EA99>CfXLxL0[X2I%$o{@8V_D9JYo+V%RaagiB#<RwHq-`[g;]vqXe/dRzWw`2BnPkT1eABIj~s+!F%ErC_1]67~XW>$e7jIae]_OEP,[y*O%CuJ`uL5sXt*_qh$8?br2)sF:sZ+jwM2]OU3F<k
je<CNMt"Uos0iAM$xNb#FZP(+(gMp+m=nA@XBO>kh(:z-rU}tUL5MTA%TsLo_
c7
U7Lv$kfL?LDlJ.E
{T/VyX>G^o.K3>]<gK#!A9)waXWtoCV!8.m/&VspV^j<Q8"GRcm0Y(nG`3;KQ<chAw}ZP5t/xp;%IAyxmXdA(&=I9QbrLc]o{wY7{$"9NcY3|p_O*=QZN6GCQY592n=pnnag)h;U?Vfgsp~Qb1#5=D|gIX3Nx4K2GuX?Lk#&xtA29n/VLMBi8_2mT9NuN6-K8(hV]$UPOgj@8c):eKR9vkvwPMhX7ESNG/{N<4rW6@(*XKtGYcG0^<&Ti6-<Qz&(t';case'es':return',`GF<7npM,|?z"-*i:>#]UCsY_Y6|sMAk,c$d6JQCL)S"%-P|.~b[)V-Js=5ZLa!)WFo/Q.h#]0?8ZQ*ZfnfT5!Fdew^Kw^B,XOpI2a7Iwj9"WX">w</0[;+w(nK4y4vw`)@zx|gMqyo>@hM
8QE59geFZa]Pv$F8G+jD4R![,uEPm27@3hZySwb,X8`H*-63NXbsmy2XF]qVF1w^SAZs<QU~q(5.GYJ#3OLNF[)(i?]N]CN$camLO(Za4s1[&"LSKlW-Lr`PF%;r0FA("g@NHc2{12%N!Bxn1(JTG^Y:/`VU3F#9&EAwOG=Q
}:cR~:e.};E4v$*D>(2]
??OFTpjXviDK0P
y4&91(S]x?L`}ucV6tM%4Pg`>P|;NpX4K)]iX"@+,b>-Iq4"
_X]KRUk%[7/{&nAbtf=.shGoT`De+HQVcUhiKPV#gGoPFvV
1A?Vl">=LRRVh"x|s_h8&7t?qdujW(
9q@#v./g*0inYf-][FG)OYj+qRtN,an[qE0=1"#/G4Ab}+k%!5gnPmTgl?,t;Bt!bmb5g`hw&A^(g"v@+C!q1*y@8f,;6#
Farw+w$XmIxSdSw]=&F0./e*,&=dhhbGnr7_o7xuek]Dw2D~D6fz4:gd.C2=u[j?e{PiE%RZ2rk%v,)
<R@!*
j=2+OrB0<Z019<0prgh8ppZ9YBFirnE-fBRgcO5^Bu(@Ptj/ev`85fN^*awg8
R=
|!*19Dnd^ecl7%B.x=VSNpAmgr>$vkBoZOnV.B;?=4%7E
-X;LzU5I_&.X}NWewh/1gt[M3MS:W9Lj3)/j)I-q:G?l)8[Nu@o(G[#sEJ@F%,G9+wz%W"Y@H6*;EI-3cGv-.i5?;:f?[.,wbWy)nt#Ey:?+~`oTmxp&!dEW~N4Jo$ITt,j%aqx
^I7v8MZ1`S~KrVcqS+hW9h57IGJhLLv.$xzK5#XcP.nGtowJ[/n:#vk[|*bm]o(U,O<^eGv0[UASp]X?O/~viApgLjbeKt%wUV)M1Bbc<ACh@;v5>1RDuxX#3?%wHdoi!#vwsbDLWe:HI$oF
L6LrQqM~>a?L`
buP9v_8!08139JMWpblcCPE#u0[R=o9wBnIPWx%#GP,U)p!dfsz(0x1i81M;bBAv@,2.y_FU1Q-__P5Zb&i%)s[AG|)!!m$2R`Z]t8
TJ=GbR2w*xMo:[Urs35/]ESpUl8hU?oggdy"=[]A8H{vUPt!41t;?ft!0%N$fl9VBLlKRtlEl#9S`&P)5pP^}^In<k#vtiievT4/%PEf(c^f*c1mn]&z#u3fJ+O+lXwR/]SD^ybn#Tf1E3d*f)wH^DGxtoCJ{cx0n/jY%9(f#jZcRmqu<RATJHFC@-V8mn-N&Lp&]`boQUm5j0Uw)R!@2;KmZ@G:.caVa$<PkMA`
j*j5!g,(7#!RTx("x8/gb|5s6yw"lIpN0uByF/[6.=,
`u%&+]`q</$HvxX)s:n(.9E>8R2#iAHY^cFy%xGWu~SJT.n
,%Vg)>RIeQhTkZYwkwRZnDTl0dG*!ciB.9#OSRu^rQt9Ldw<c0*G33I:Q^)=hB4GhDC?92me0iYh^&xi2U#<*ZW[(Aa70VQn9oM;^c[n2;fP]zo?7tOTi/IuwenmKFvfiY.,13TD
]RD2b&Ksrx%si.#x-.?<ZE@,^;a8*SS%.7349S0y-7"r!ns)1V?N=:c6R8LE{ssI+`}<mH:Zlhd-F<9Cz#f-B7Qr@nZT-ogcO"D?4pS7I_Po77n#(_<!lw*`wQ3k@ZY"4WK5[6b7gvEjESQ^G*XI7S/AiSw>4jqY(x`P#()v[
cKAGKw=yF/}@x/0J*aP_-3;aN(Go=GoCnHPp"r_nsDFvUeicYMD1E_^5e`?(|n*x1R8SBY_4

;IsjmYad!NG#(9?.E8?d,:!VvMsyC4g8&AJq*opPJv+*]#cidbeh_MMc"%
*,]{0R8X,aA;O:v;apLsdMVG]zB88
i9Tk/~mjlU(6Lbh,Z>&.&owH"WDTSSX6MJ7]*tA?:u.w8vlb/;hyn)V@>KbMosgz9(xjIH`|m&p%j@5r?4q;f"b~^Wo$c:+B<jflg)#=3Np|%EtlY[Mwc|vS=WRZM^+a@}G5GEhf?)S^p0^MAnY{(??pX>C(D^EBW_:2]AcB_+,3&J3ltg6ho^V7KwM"mO]%:b7/W7UPR!Pb^&N
e_YiA8MkQ^Wk6X+;J_HFL-ds^k*La)@=O+[Bv"@,n5V$:T,-Y7k=p;m9[P4E?_Ay5"W2I1b
)TBE+4Yp55
!1S-IctDGHAIG&y1r<+5rPp.gs-G]L9yNeqas=8b<P;&:U^
c&fGl<eH1uy"3yPFcp{7`-7>G+gC(B^JT&]77Q{^$ME*0vy4O*.C6#cd/^P]>-yWAgfRnD0Kan{"FA/aQ:%i.we$Iox[IAEkVZYL(Dk&N-X)>U0
}90Z.hsqM?yPF*?g0.;jwy8lWSKS><a&!A5I.Y&y>yZEQ4U=(?[P2wQ"Z4,1-0#:1IE4"fDa:JVtE$nYy]&aD:8R2iCI"+xOD_mHBItk0QiJ!$BF}$E3#w,gH.jc,kPq94=K"O;<D"3wkRO@nQ~I2/snk#vH~rN;16@2YJB$HjPRVPCA<JSy}7GkcK0m-;671#I9(G/%t&C
3eaG2U_<BkP]qI
Fp
86B_
0$/|/|]X(6ThTnx60_GsJ;p}%(Tm?v@UaMB;1AcR17wBiB3rq2D$tf@nb]h
D+%F7uBf"up!4e"|e
`9c+pvnmt&^NH-2uj(<F&6
Lyp0,)@_0F;HS[UVk[i80_zCyB[
)We@T
e8cj.eCUTcCwAcZ(,ao=~X(enUhY,0wBihB*l`0%7(=1wPHZud|>d=Qgki(b(+>XOUAwz]d@;42DkIl6pn1r|#VD+b%F<P#?3@bA=N!8:YjBZd>Pc`rdw
0"noxnR$#WxR[v(_UI#=NC9?Ub!HI_jo<@.eM;O5eJ8La"9f+%/Vq;b-yUHB#yS1gLs4Tm|3THb1Mc{C(1xQY+ZcF61x,fg1e5D^_"J+gXm/
t(b]7^XJ)sM*K)LGT:&:xaTkunt=/5>l@a9zMNZ^Afe[Oa`,^tGiV.`?(1(
m;?<bVRBZkxO"Q7MfSj+mv+#gpW$H)!mwt<Nk:OTh$+44m;qX.[Y,?%7nb)@/~;.;=ZKjP[P4^[3srF?Dqe>5-"/u~iLX_@F#w^h5F.djnHp_oA5R$Uuf?bN<{Oh)Zmwh9,gdBVQdxNgHfdx0+.F8d]qQq=
F.
BWx6/++IKpeR>UfW(Jv.cWl%AYK1)dxoH)
t1ZNVlJ>fMKKD%1T3Ai;YEoCHG<mSci8
P20hep#q6Mk?82zbSbRmbUYSSsMq(iM,X60Z@i!Hl<*;51Bb{3,EGQWTQ5y8A,(*~Vn`VN;VZ^8:}.I]/VoT0k_W}g:=}rhy
y>h(<9isZM+#38O$n9f.;n65[iUcSv6.?wQ$Fgu_O-5Mkwe5-9WU<d"s"nZ&_)f_:y>+4<1i3_ZQ[{?Up(]JN`1N"GC;OKV+5uWVemKUjima1@ah[EGP(Rv7
wkHgK`"(!q9FKM[>uS(-vpc$CQvi<h@YZLf"|Y1y[Y[7B#MNL/)<C[=KzAKZf@l%&XL^9-9CWT?qwsF_JZ@4[!r)*Po%$7DND/--VFH/hc{$V>?jkt,PnoxF=?`m~;#@W*G/c9dS=*XCQT5sx*r>*;|P.ID4~eJdwaN6sug7|X@am8SAPm`&O+=,7nb0ll_Ej`/HlJbb@V$/.^NRBl/$
x^@=^}/Mh12Rg:>--#@mGdCCF019*rX1A!2.IwIO=(QBVaZ?"/BQZopnE<Oe%:P3_Sy#KP3x_uGG0NA!0>&`RW00nr,.1H
C%Ckbg0$Eu)wiP&m!cJk~/LujCBPy(BV3=hW2Ro=S6Q"G:DoFtzMT4Lu/CuenH=Q3r#H0<
=bl]fCabw[dRnNdEMXaQirM%.%@.j
42Uj&1c[a1^m+M,xf?qyduR$?j>(c6&R6(Gm2|
@;YD.MB%pHzN@Yp7b0oDB+1dMg#pj(<NBHe*!qdEL4".0FsgeZ.$Di`@(
,(y-%MonT^0sZ2K
*E,?y"+x|gQF"iS,rB%=:As.M)GQO;TGB!d!wwF]
&<+Uou%S9`niN7INmjc_kY@!BP
U!BJ?JnDj^f<3s^Dirm#fxat#6b_Pt]"`Vm-)Hs2El>_yUD)7VmB`@rBL$f+f1|1kWO>}yU5$,BbW=5<P<>,:j:HCHJVqMx_4+)_!?
S5<EniQ(%CFW0TThWWbZozY>T*s.vnG!mRM=m9WkdRYBLqUkQKm]>njcJL]wRO^@lq,J#Tg!ac;+`z?~aJJkEb2ab"nas1G;z"gsgrZHKi1>$i:zGh#}4eJ#
Gdhyxc{MpbED1PZGyG7Wq.rH?D7=ALL;<Ij,w2_E}YbR`*Eb[I~xj/z;#YFwJ*p81L7;$2&rU]1h[Si1JR.-8A(_E@AAMg
r,QejB=8F2K@b2z&vIo)';case'et':return'.X/Ks6KpM*60dY/0r"CpN:s.$ot2S3hjqGFxrf<Y`"(!IW3kAMG(>r(K^P_$FCRg*a`!7coLU@+E5>OWl""e|l[hiB-](K7cZuzH2=Jce:5^kKjrZb4RF^XqGP>`4e;51Q;aR,vYYn|$y;Hjo-p[KH6n1Z)Qigj,B?<ahG.vyGsa)D-fInII|MaUU"]+pgD?&s?k-.1"N3W%Pl|=
,>M^h~w"3B?KsecV#G$zVjy;w,h3kG)r$gBeg}P..<?=3C*gc|.nmt?L<K;O@dknpB0s#qaj:z3yWp_$VTu;+UiWZL<QW,Khy(ZWwU[=m=Cze+A]
jW~PyJYQzmL2
.qpHHO.flL
G(vf(,:=:^ceQHosF=sm2Ai)Kp/%iXwo^n:$,(q
)S]6`xA[AJ)q9O/"a&@x.h9#Bo3Un,K+L7v85?"G
DYj>RoAlAWU[OtM#[dWF=Zrd^7,hA|
amfg:4)Tj
(#r5bY>vlWb@$x]i!D1a3^+4L">PGffGde!n)LaLxW{Rjru1G:v8/>mfpx9vw_KN3`%l-25p$(!sI;yU>
[0gc[PALLcBHZ"L>9ymha`w>2).d6U:)570)8WslNN5v&fa**[3OZ5pv%S&#"^Rpk"P=g@_`S!s]HJ+<M.dT1c]2@U4Bm(Ow6@*yvC98tnBs4F#[Xw[:an4eF1h!eSHl2uxK<yG@*PBk]m2$no[Es?4se4q,g@h>cH#nam`8aRoUt?48!Qk7XbV%;jclv&@;|yj.790U;?_f6$Sffv](KDW?48$qd,sQ`6:p/,Cx6T`N]UxF7)cW4=7$=1t`G%:H^)!o,28x8#2G3r%Y[`WGg+P<tBIR_%4ibv7L3nQ1]*0LpB"s^s.:@RhG<S+x=NFOx9$wbufyt2`cz*A$"4T>bE]7cj0LhF{XLKjKZNn-~:ri~+F`xsp5Dd|urh>]&/oe(%RZq(Itv2kOhG43D;B(apGIi.$o.RAS.,.Ry5PV%`jB5AnS3Q,v?EDePalWID
X$L%`n4O>nhxn[-"K4g*sq4{/cM?Md
TG!lVu:usniZ.Q%LK"<eAG?ymr<s75yR;)QCBK~_st/^%*px0mXM:HDC3at`_Me_.pdG
X+a@m--U5hLsy^IiVfPVAe_5vvo+L<)C,xIX#>x|=J6~6lh!gPpG8`<mD|G+8Wu5H$O.]5k3iym`D@Akt!*:mQcthL@4*
];VHP>$Nm:z)_5ec2Wp-]?sx;:k^do@fepTo<~UPY]&-.!=iTdo=inv11:lIZ`%EvAU=AW9<KrkL#-v(OrY0.19|n!&2F:cRdVC~H{D:MJ[dY:3|C}0c"S%#^k2]#$b]"FC;V,j!2_c
h#3cV&n_TO5`kX.o;j%9cH43fkSnpeilfN=^7HEzpV7w.Ye^c-u?JyF(==LS72En.<xi>C`vr~u/WJia[F44x(%=t1;x6(@bh=?[PT^jRz@v&X,*]<$URcbl1/
[GV2%fop{Ucow*G4
oS8hI~JRAp#5te0y&NcXItuIB3]rivMlei)&l[9r9GUYcA:)CY
Tl^!hPMqY(.7@ZjVH%#r#$~I9!a,e*3([H&PKXe&^-VJ]Tk
0#j<SYVRtE82A@W:jb7!jOZ!]Knnc**
gm0&
Z"G5t%Y
RS$k>~?Dm-`3t/T1"X5^6+uTx,9Cme$<^PeSPjmd.Y9@Q@&[[R"lby$`15esUZ!a+w1L-X%(mer<R#qw6"bRxYkJtw;Yy8(;7g^:`OGk_M,Ma2q4k2Z6v"b:Lb7?Zy%XP|"%fJuU&JaHUX:_J^PoYth"xOiX$"N&BX_Ak1NJ6LHPj.^b2@21F1j<J=GPrx6b;BH1RA)z7%eJm]x!?</&pKD/HV0.rMM4XuT]?R^vxPPs-Yv29]_B9jfZ`p?A%]j/C#cVP|udwkh/^QTZhEr&AO-s6d#T=uvZrci
N?c`Ge?78Ydt%iUzewdn0xXJ
/?8bgVIwCXH>4+^+hU{ZSJrpjP
@a?$K
TsN>=7w:Gq$sE$^
M]H[rVCRu<YB&vJyE)M3o:cf.(q&j]9-WFhYR|fVS
-A2w@zA9@dXB
yiJe[.]P^#!1%fTh=DS[-S)8+okn9rVEPNm[a1KgLmF.hSWS~JUdRC0NcrX=mBmZ!^gnA1X0((VZ2#%hI9h?&SrQ5JnSoI5cq.1e1R^].tp0]pNvX"a7)3V::b*8f;B_82(DW-3A#XNx%Q~lM0gv_p"=8I~cgStc?(`hH6/fWXWR8.e$1^Rx(+@@:*58}8nj)g1HV8x[obNq$."7zl,fDhl8|uv+em|]=%W9LIICYT$E&_I`FU-uF#,)Fr+G63La84nIWr4dl7^Uk-h+5s:vf:&(b!udj`Gf$Pe`1g3nzrLj{
nTM
8Z3Edr9G/c<n+FS:L5)+_V!,QBr?7Z$QG
^gpAPOZwf4d,N0rUc,`dllJfqZ.]<xt2S1nc]8G@3k~Um9C^:y
!by1(_woZnN*r&@buU+RH)J@?a&=cXj?]"OW/}lj(;!Z9xFx6o_jgY.-n8ng;);3g?8Ld}8}H8+&
@Ono!OxB&2w5PK0R6)/]kG)$P,0%gm=,$vN:g&[a!,lu`&ob(d[g/s9`;LK(27]ZCy{jSi>rDgIavwqEh@.l`?v6_MRi"bIDDTDC!y26p%:tnN`^naH34<K2.vc0[
xwKqDj
@Mp#rhVMt>.?h&j>.aBrcE/%IY>7XMW_0.G@^fq"S!9>
2m+2K:1S{e]Kc&ADwGXDUlQY/uS;VbOd;mM1gkgg8O;`cwz#-6-$]5RpaAMWN[%QxELrw<fB?U[[{SK@SUC:Dt15{-`Sn#tM8X!CzI5MBXjXS^=y/C~$,9Nvpf{HZ*%Y!
y"F0YP}3C&9.+L?Mr,wyp=!u[s0s/(Z2C7]FDSa2Xjr4)t,eK;fmUW`)i#wHlp&2L5}#JBjO0;00d_/3F!kDm3$1Yc,pR67,^uhle.HB]V.^_)>jx1U!gomjE$ffb$twax&fz?~I5r}r|U6BX)c7^2),9O!A+5LLniV47VK`7M3)QL~aHY5)Gr4@qNYS?&N/.]C1`eHd!KR0L82)#nC-89Rf)Q{Pu*ZhB2Z8xls9gXzh@W5U28@#Cs^hh@?1KC/&K7C5r;f$MbT6,wo
2ry*}yq#mvBHv*E<;/%HG-Z!A13Q6-;+65vibV"XzwGu..7%wjI)0w=aaJb5aPm
L!I@/dsxwC]Y`5"q9;C[nr8(QU~#_U#uDaY=/HN`xn%Q72NCMu154;IE,n%8o+9J"nxMvK|3"P(FYXLd8Vq#tQdWE"knQMA8.wFE|AbE1k{WJ0,0O.r<{$1k;Ab!<yl?%D>>Vw^W,e+Eu8>e._R7A]L<Lu>&z,O03Obf-3{(FO%+#RzE$#X$t-2p.Qg)c6%l[Sb/_vB1QJ]?_T]UWE.5oVQk7&]KchN6Lf-a%O#<@
GO$5&
]=U
;$d2W.t#>^dy4gG21]{6j"bUk%X^<?XuP?YkmYz:qb|*>JHbu5SA^:j5H5^6!V{vE-dj>VZ7rSuT[Ab_XbCQp%@5z4gxg6}XMGM;V<3hi)5h`C>N
dWx*F[K8p-pe0UY8MO1`Nc%+F,@
bqDd#yyhhCq+1Vy8BD%eHamL8{7MON,IvRWbC@af`?18OL_Ep5v[7&2tq^S>w.0kCVG0?[PlI-y>O%D:hWoA*b6wk&qLSs_2g@wh"#t3UMQEGdQsC/]eYKnYGY?_&LdSw*"ALH;94Ol].2#d7r])rP2h(8OW[3J:M
M(L|`d[oKwJ`#NCl;
xLsOGnwCa5b/p=KXwAis10to/(5iS8]$JsSy/kLmw])<395eQ@[b^|NHiTa~+):CC#9Q;Nq_*cML43"X5%?00-F]wy_5eTs-HxL~pV`cJwp`1B**i)M(k:gH]kC]E/O+a]*<*R1.lYti:>Ird]&>P_TXlb7/I?mtw10?c*5.e.Le5SG|[46$&Y@PU:&HVF!su<rQU|Ph
3:[xz^M&*h8^~mF^{Kdl/7_-GF/.w`}c@
Z?gEX`qi>+6o},a.YjZlLF"+5]3PM%T/cFSC?pi#TL,?AAJ`),u*iwLRce:J=A1vLtY$rt,[{i!WkP~O%%zGK!5#"[$qC8@_J`S(=B;"W`*NEI!kU<1A=J$&e&7WS?SIbp:F?mEgl)+#
Wy`p0"9%3V4CqA*pu5cyS!(>(RL:0e35aW>."5wSd4q%Hsh
]^@&6<0V`h>a
JPwcYGX9LI-_M4EO:jY,yu@f?Fsw}/ogDA&uc:bY_^%i>AXasVeV:I2mW2EFpaX&c?Br*?E`IeOUMBdIUX8,ufC)nR<OheP0Y6h%)l*s(`4T8:P:s+!_Dng=*d%A3trk<)@)4TS>}0sQ3k?>?P6K1t)"o:j[`K:uzMKp:F@,+up)c%8oD3}`A6J^^!#FhL3@
W=lm<m^J0:7lJdNP)*hgNnUXuJjy/+#,x}!HeWhZ)5K)
QKk<MO/_IZ7mDmM8h:oiu$STZeb;a^6-cghaM5}<i1WbADI7|k<N&';case'fa':return'(c0:_g"n7+ZUP!-#PVA5v7PCATO&yT2HqarV"I+/#-TiZR#A7IV.Weo
,bp+ZPN,q3RnE]6#-3(B<t"o7v$Ek!E5*:ifiiUG/x4(FOvb
bxPivqF<!L6uPja|DBsCp0tG3,2AMhIrE"6#w)_UIm?RGkpK69wP_GL&d4Sr!,v5C&SVKDoF6UR@Ea!xxXA^Uf^QsTcTqj7IL>[ZiHM>B&l@+k$]<L)gKZoao".D$B*jaxuqbJ8RN;D{#E%J0s0uGG6{R?iWt88g/AZ<Oz&q/M=^@QaMq,g
!`0JbRBWG0)cwXch,OcunlXjb[+D=Jh8<o.y"yr8m1)Ma,8c6o"U48&*.B(=trLglmmpN4gPJEMpmwtzxm5MKnCS*l6%b,(kp<aI4.BP4NMt@BvPsJXttgT49v02&#wZ5<ERt>(0GA)wM>1H-&gLB#qYrcvmwzTl5Ks]ar@O//YKND2lM*$-wdnh#w$|yB.!-jC/)_s!X$-$pOY%?I.@"_5T)T`<)yuo#"n=
:xd"f%fQ@..MDe0bm=u!
q(h)CIu%@
"T6EOs1Nov5YQAs.<wAsr&[zV?/?HNjkg@<8R6XRQNA!T.[011-*c=lLS178g>xe^d(aCmGgc?2FR1u@Dg.SnFQ]?YMqr(>|5!9]FHkTKnYgWC.bO`^XX<,U/s]a..fag9`lW3:}0o[wpnKq>_-7)1;vWZm<EI.^?8nJ&P[l!H/OY?M`s$.lQg8?+]xGn&
#t+<G$z#"]K(#Z)9""IooXE5^gQ@2Vgv.l1T#d)%mSb
E;8/G#CS>BlJ89HoKls.>309a1_J@Cy*{tBo|O;@Fr<t@_:x
K{xG42x)K8T"6G15Jp)/"br#3[-a%tD]46,[eAuAtN/XNjrbFSUoE=Up
q%"/Zy)#SSYLwdSY@Z/]~n)?~]$$y&e?rCDRM
C229@Fs#MH^Obh*CEs3oL_m>}SB!u1nBJTB7<O065Se#Fyi
Y?UGbm7CUh5lnJ.QLdMxgHFBEI5W~xdJMvCk[(@=w:]gp>
*<j*Y~g!Hu<bF(ZOgpeJZ2jOFW$7ShCm*wk3En((9e$vR#7x/e"(kEosA3
2@;#eqtq3J?^Z:?M`<&%<?ncN>L..?OL&jI@PhiAP/]9<`=i5gt$TxNihMgS$h@)#SjV)>h^+!jS+3eH2nJ+M&<2dl/?Oi7gZcSAOu}/()t.`E_Bws?Le?$mrg+XqN{4DBHH@$JvCeKbW$tCKhLAAec
wZ-`mjj8b6k#JS?V_DKHPKYJxCi+w&v+5]n(kb6]V&WBg0r0$Fc77;a4|7vu<M30P8~&>)NMg%smXmmMX$FLM1{3D8:#o4vxo-Aj_dom_$o3kI1!Ip.h;b-kLm~;yCdinTdTZu/=!>,ls`J9}DWBg`n>}8nHjmZB_ked>gHSUC$2jU*s2e8DBLuVWgjI%[P[V<7WX.EGcW]b]Z3GmXoNs2_l0`]+58O%YE0A7G<Iuem&x;/(E`T<d)|0R!S()u}lTg90c8je8Xd](:=bL[wk~V=O0cgLN<u-|k=1OYqi!ly"<o4>XZ0C3=4cu__9*x$In_-2_,iQwS<C19ME~g^QnRLo-XOsF&70nC]vr+:ATNM^#C
sVC^$CD/yhUpm_a
[,t=vL?{u)nkr/2)h-6,m$["Z</N*{Xz=h^I/puyN
LQtCn-qPKr!|(X<hq[hx`b0OoAtpQKFm"S94)`;]m}I))q-401f}<{h:J!+nA,"@P8W=3Plm2L5lRJIhxT;B2U
1[$tdjXZrp{YQ`Gmck%LWy7C>^GJ)Jgtyx~9EhBR/
T)c!%:J9f%a=WP=r6S$#r+OElrFO{#Ad8e2.MlWh@:zKbYG#Z]9:.U=GLC;W]Ac:iv<T+kKv,Uu/k/ey7AR9g
PBx&Ju=)ZU>e*KvtEif<vufJ(lHM"mJ*j9]H3$mV>&P#H2g!R[gEhef/*3_h&RPm<d+&pOP>6s8fv+#@7f9#,hUW=9?V5%v?O@Q19@yMIts`;g=HJ(eW%Y:<h&&eQcpWcC:pL=c2;
i[sx#gmBE!JCmn,Dptp%)@nUa%;Z:VL8#n<d&,xWJy#Mg+(=0
jTCk`S]w6F5,;]{CWu|00DQU@wGb,@m(<lF0FocJJIxD1Z-3HYlDh`kx^^jn:R^Z:W4JJY*gZJAj|ivDv+,J:hJ/8(caUbG;AW"b
,^u*l4SN$P!i>0gRfzQ6M:B+DWQ@Qu<e^MNXhEtW9OPlopSntp,<i$vw&)QhT<@=2
=:m=kB_q!A&OLYsBw!FyHT/9iKAQDc1f({w(`^^1#D^Wykh[$U0&-[bhG2/!E1OGo>n1eWw`+{+;MYODG^AtG"puN:47JxQ
E&m1?wtS
{
YYa0GbMpYP<4C25(LVU>YmEO$ojav+u(NlFmhNrP(;=J%mnPFDpKcp54$xwI#.1L7!;PW5_:33b-K&*Gyryg}E==tA|3!.pYe1P?K5Ek`>sPeEmjV+[iinHC.
z,{mWZgf
v!&A`Lp9HLQ[_#N|4iCKaH3JEl
>n^;G`(j_WOs`
@Odqmk]H"Je!@,r[hC-&aRWIGm
fgfZBxw7j~W(4gw37`sdX)(N:T("_4Yw7XLM71us1ol5O:()qA(|l
2iDL5rItVUE=Y5gS2i<-El
3D2R)4B;G`KikF<H;;=6$3V
R].
*:8.]!$3mul(W>o=^3;s)nlUL=2[rXYyLGF02c_?C>lK_-q(syS4ey#=zW0<wy6F#*9n>
S>VJm#7HoI*p$K[[nd,%5cokyX&gCQ`VnGe8<0w/8L|NA;g]-vIJn<7Qp%BJH+U`Ag*?b*X6H-;[*6lKyZYT5,j
[^+]=mM<b$vkPA
w;v%Qe${^SO:1?<_0nJS]ZN!-b9^<O"d@W&,kk!uBmcAOJvsHKY]f*1(vGRig9VTSj?E*@%UJ/gU&CGj5i@]
3gWTbSju@,ti3b:g&P?h~/Ig!O)=dX1PBntk:Mp*0U`S}8267oX4zV#R`9.D}@2(}u~hJ/COvJoAy<BO23q5t)ni++w9XDR/s/{M]eoyx<y5e(IY@vG;G-P)y7+>H&AfQZ!FprB9`!O71]N4fD|vwXb/bJ4dQWUO+7sog4;_Mx:5e8T*puY@F_:u~?oPtZh)x*DxOQ6C^gt[6w+lxXLuHC^i!&W?s+MI|--Ug`DK]eQC:Os"~%UXlAoi@p#j&rTm~6kn}$:eZ-NN7v_"kFRY.XX:]<ksr5=&*Vnk$i|q78h<*-#XGjW5t,[KWf:w{QVZaGwJUWS45(h)7qZ?M//EHrxizWWfeAq;GoHy>Nd4"A*@xtx5/3uU{!6_Z.(Jl(d_qJ!?)*^+zP73d%U/{>VB~*lkpR/cZ5Y<gPaPt)pDAD[A]7.(>(Zg04{LT73E^bu//rC.z7~[kX#:h&K[.FW`Ic$HOko
Co#A()eZI1UFL?Kkd&blp%~Gi;BnxQMEB)NUU=z?bQq%<Mk6l)Ot6SSn4Nvj6t,gs!$X`.=3e>Vrn!$Y&Ttq2L+EBHhl-7?B%]Zs}=`]=jSVzLxuT<iHICZOxcSZ=!$Wi-q@gPqtY@7UucWKn8*Bm$`,#hM8B+:fk[0Tc:X&TERYge/=A?UU(v1V"M/h*yN4rdiOxa@9rfH.v5H%Dxqu1Q#r1.xirc_AYC"a{l9:7cV/84)-Z_nK]@"9m:i#jJqZV6J&oE7`=D1(|u.620DN{K2F"pq_F/~"PA!=wlD;f5,llSP`z1J5mPrCeaPI`f%a6mS#H#H>Kgj$71C2D
TpJe`(K97Z2(>X.K^T@gg?ILD.Y0V7mMx>v,Xbe?fZ1PkO0A.)&4BJ
QU*XvA<e5r/v_/x@+aAd8?sP7[lWS[u=9ok@ttd1%^(edchl2:x5KQn0?tJ^5,L$VVLCjiJ2^)Z%ri8ty`UXo"DmZx-p5UJp:CYHx!yDJ`3gLCl2Vbah];iI0Uf[g73y4,CV
0yVBZ4)/$<<6!6B5$xwg7a~[2x{S+.ttb#93
llE[`ThbC4RXe`@!k
.3V{PB$Bj^!(d,f2:DG!l03zB=!]hONrQg$IH&k:cBa9/.ek7,<=nI"7)=,QJ]"~lMO1;{lMb)GIP1(W
<ubkeZ+[Uy`RhHE4()`!{E*l>6%]u>0vjNqUP+6%{BhZIL6N#G-w"g<q9g$D8h?rHCNi4^Mi/Kx$8UxTu[o3SeU^`.XJd.>/H+C]UqG#aUr0"@&4m8<;EuH)DhLi)wNH4@RwZ3n^/10BzD$F/iJT%=/7`xlog_"6upY1+h`l-m~OgJ9AhjzX09[Xpj}91Dus6"*vB;[?21D*?@ckdPkeAb7ZC$oBAwG0n@NQj+v2Gu/NSF+Gx4P9QMI
$B%0ILY&R;u<K1M9zfhO%yt;TSwqlxKjxn82Y=x+.y@_uDfqpZBJE0#Qp"l]Sx~?=f|G`505xV/S}-Et8of4[8yK.p654cSL9TeTnb?_~3jM%DUE[l:4yhXN8
GJnF.
%P@z"ZdHFQ>-|1B&hDc.3;q>`uY-Et&1$0rm?%w0r@V1W5V7R)}o>7}Y4&qC~(HlKd:]h?^;FTo.xM$5m0*<H*TMi,r@d^QL|L^"b';case'fi':return'+ZuALbSZ+2L0T=YvdNO,+*v]E9G1]f97G6ZCW8gc[R2am!}=g9Q8&fZC$(cVAyM60qYS+hFWsvQN22yI]BwB/g8%jy~b^^5b$*ug@!Fq"9`/3(C,FCS#gW!E@R`6?.l<`L[w?wGBc]!LN?bIdSD)J)LH.27)o8DY7B0)NRz-KEdr{w~>`SXRZfkmyYw0O+[n7szD3019+H0m?fXxCBMyr[g_L+erR%;ix!oEvbmA0a3;1Tk](SX7z04`x#j+
.D]g`IX!$C)Ou1uz""h}V]c21DWSQia,7WXbR[xqQE,(Pb=.HPGnqcj_6u!cU+/MV%SjV`Ye9<TpXC;Cf70f
4<J95V8uZ=2JBL5Ii#
)^rZtC&q4/l*Wo"Ru+kUyMO@tr4Wg`J"+x%>HF-yo$sv.DneNdrO*nEF6TBz1CG7h0g4M>]RTx2Bi@#<`yihCTi&<W,lP4[
?bw9W
L-&L&@E_dmSGYy*.yN[E3`pS#%_;V}G+a,4ZHY@m>Lk0)6ta5$<PJRS/M/2%dz:gsWt/BowH3L)rhuaNyuDtAfOf;T<hw(=0_!lOLXN-yUQ..MIgx(@SiCv{kf2-TY_pT4:;-mq,a+FrkXs#+XG&KCRd+jl0PaZF^UN|:`R!.|L9Oy_}V%b"X"I5SS-6gxj4=)SkFl2u_=vM
%?jwu(,L=]Op9MQ<vgMO5-$OG,8wF_FYbLBW$V0KQ6y=1rmjrpBBPFcBg"n)D2)6|^NNdj"XRuW]2nAb:J.7{Z;@X$@aUtIW,Afkjs[<1*X-<mr+8sx2"h6n3/ln6Apk:<Vq~2dN|lW?A+4%mjbLE-?Nx9?I/4n,K1
CU
BR26w(6$_>n!gf6A+F1L}:`7
q]d+MoL8xI&
dyOnm<]A46i=?JFD&]]"*nx[c87="iGNyDR_PgC-sNlj=jm9*@+d48HcEd*),e&lg]sds*r2/wsBAZ`~yu^JDKM43k,zn#/!Pu(AgoT^xxnH
=75:KPIRhi:Ciuyw_`F@99Wr{jO$j6%^;q|I<:?au1]$.*|ADmO*WR1xokXX:RUmJJ&UaRm2q$3C=!-jkQXw;bE(l[
K%QNASv[_dP=lbM
y&bFUpin?p@XIIoAELt1H)MA%oS@PQba2k[%2I=0ba9N4tE&O?s57mtvw)Af]LmlKSkQ#<G"jYW8:`lw@)T&bmdY+*h512X2]8ey5p?5.{_HW>L|POi?t@S(@F:UF`/sBkQN>&m=4;MYu{diC?lhGM`GSpN/O6#6U>.;SLc@y6aB/ye}.jc!/9V8FgZ;8oWu[l,u
gPW&XIN[W1dVsPgef.rQcWt1S.^jpGgp49<WWALow&62,pmF!_OT9tQ5n6(.s!+nhZQu$36/e
<qM&[Lq1mHUTZ+<G[u!XbGG4&cB6g6/DoqmTg6:.{cHI-I=lV_|ON^6!W7jP6-1!t%*#!u-&ktGrBw],jiO!_2w1!GF1},eEpEd<efJM`!sXk=*w&*+OS=mI~;^lGsL1>Sd=d(e(dK2Mdk&VyyY8`c
TfqG3up_d%#s&$?_*^000IduJ6S/Tz3y#"5d0?bU]$Mo9oSp[Iw7$n^w70
Hs=U$VP<L!ebBa8haM[_qdfkxZ:%kC]avS4md$i[-?9[K5jn-pW@2=~0dVDe~o%"9O9k[!-hnhr6qA7uKgi[?r&"MNO]Gv*Ppl+b(Pn?)1tn#=^apJ2Lk!f5ybyqgD{OnLHtI8s`0
Q7<:RfqS]6yJFti&"pwjOPu-)K4_[lp]8gB0trO-x@;SGY|kbJM]#O>Rr,gFospnwblOe*Vf:S+#2do#PixCD:F^ttUA2aP[cnCDU2/5>Z7m+ARjCa1Wv84NHL6!/oSC]Tmu]3@EYdqN4)&F3<v8`6z#z2-tWEZeqSxB"-Z%8b1u}P|1#e^C9^C*RW%S{75P=WZUB^T#9e$`%h-ch/Iers?L@l:GkgPTJW@;%iYrB[7q}sxu3p_.799;MQC?R*dnqa;Y41kd,"{S})3K)sy*`jM1%`9+If4:@eV<:`zY<W-(/6A^kXnmBx.k+iFYt3C(ke";^WSO^-0]L2~G"3mAu7[4^%bF3Vv.8K(l.SU.H:4*aCJe)d-e@"9O6VKiarCo27$7k9e:uw^CoJnC!Yf<y!&Z<64;.$lRkh>_9QN${"v>~!D]4=iT;>4[HDA"y>4S^&rBnv;R8[34~;&2r(p)rwJU>Ma&~K,akD9$#xhd`fN+XGM.Q+ieB$cfrB4B0.s?nV495VTKDu4dgG$V%tTyU&!Bwpv][&
WnFA8Y>Bg!kd!nBn:4R5Z7
@f-)K+=R+D{`_VM6`ZS0zOYLVP=VkK#lL!!MiK@5#ZDI|J8,wY=h?WuO!*,)G7*9vj&"
-T2WsgevX~9v*ruSoWpu"`%""0o40L$vq?wUv
*&h2-uTzc
-sODwR)9+2a[0,D5[M7Y`[7!(<<OPsNtZNuS:G2e3jXOb3W^2yo{oJOQx,sq8$yc8eb)wlnHxWPaeA;HOirs1V$8YTR_cHPL79#H3y29t16e+Zi^Te"o8TqWuTfz
jW8hn0qcj14^Xvh!*",e!S)!Ru5X[o~>>Xf.A-KY!kfrnfVmpBSs{^L
N`h$F+(UH+/c~4^HD)92x7"k=FS
mwPC{U?B
w3nqD>"Cc1adI*v7/570&(jSu+_<*O5Imng*v7%n5E?].L,Dhvi^qKC)+9I(L!fJ80L]j[!_GC!J@f3#<yGx/rl?m>miROVZ-_iaK}85X@EnK|LL``P__EUumiEO7*&N0Dv:31><x#WF2Se=-^-`YX.!Mn8fjq@-)jo66{8E!<fnZ+c*6/u9mk-OPK<Jv|&9.AoiF+XB"nU$RJSmR?`RI@g=DC2}UJk_E.btg3N3y
TJ(|ydi~aM&bF:
tZi(}]U*QUL>mug4q-<3h?`w$XY@uSGm(TL=;Jl,oiC=)uMpdi=nrO|j#mC-?+[Gl3WG
I(pr)d!Ur8Zv(X*+Xzo&#Ya2y*!18(iPywqGF!F;a0NJjM3dD%v=8H8zTo1/r@@GrK]]OOjD+`LJq7W2!UhLjnGEqELMn*"(-:OBvg%^QSK]-!oHqt_U4?H^jf>ft*#[;*g~f3rD1Fgn*:<0fUkIFlV[;#7@WI!@dp[RuZAVm])N5M(gbG:P`76Q]t8GG}%K9vSwsJL(uI$(!F_s06P~HLa`M
[C4-q7Va"mEDiD`r!A94mV,"4OA]E,t*:cUB4.u-w0x`^GhG^;2
t!S@aa#Gd?5|xrDBIUV5%9vpv"vg+BiP;i7Zk9`08+B+MD%ysTICIM8HTjXH/.TAA
FBVlD"tdt5=,sgu)>zOU;J<`==2"KV9oug>h8Ex/I2R]&[^Zw#3=AY-x]@dBj23#02BF(,v&:0<p@K6zGiuEn5S
RZ+9r2,*)R2^&bc&lp`A$sRN:xpF/1[;g4Knv#%T:6_vIosAuL*MK,Oo=}[>!Tg-43Tr/+I.
T_BV_"<P)d)X
=M]alDC}7D0$:?D?PXp1/Gb0=hT:tCbytE$yi#<:Q(*UP:/?ZHAc(>O;Y6p:
-hYMZfXHqW[+y&I5]HbIF!WylTFq;l9E;h>r@#83eI3eecg.,:)v)wlw/]z?7Zs0^62;s%25w!!4a57mQTvmVLQby^RwmaHM+71O0F6-d)XW,5(>{"J6*"+7~lTXe5_s6BWTD2J:)]!hH)H@x9
)
;0LJkixSw*XdEEMaG9%2(CL
X]]#tTfdBvr]x}hC8<;KN&sV2nkUki2
aKeF2E/7a]NT7pD~$pK:>pmyY1d_xX`Tv2=RKTimX
vnq,^gfo0*.!s63ub=*uBB:#7zm;`^2ha]:9(GslXIE-?&F<JEK!ov$vlsV
";7|A&,22,ywkIP.B[P,1d#(9.@Q;k^F+=&NH9a,58H9a-t!!VTev>f=,j_QV6y<F0e*E`2xe=UOa39^F$u`deW7?nk?X(J"&zMas1RGPt7[C_85*WeOab$;bEiK]:2#Q#4[d@_+%3;=r?rV*:cJeNu=f??{VqZKfpY_eZGwOu7
m3f0n#j+@i2TD(fKosD3ulIpA-y:J&beG99vV3%Pt&Wtqp@vCGN5w~d3#n^oaHGqx`5
:*$dn?6k_,^0>)x|*bJh=I=|)Yl33+"N;K[bCx5U9h,c#(Z$>EKwDPt7_icW%>Xhmz`aa]rC-e4kT~8b][1xETH`A+Qslp@G"m8H@?q{_N!k1XqWh9@^mWWsYsDf93U]nf+y2(9gHG&5b,C^uyrWE4UU_n0bBdw#yjEo0XuR1Jxlemwf<4aPQ;X?JO1yY&_Nh&.dO(;M@RW1xAEfaqkWi-:HQ[Fd-:PBit0"snQu/#"MnWwEaBuRw1l]<5o|$W%6R`
O)GRH!KvFEp.CZ^xuiH+<=S+meGM}WE0?x>G-b@AmIA%g+"<9&A^N%T7zxjO?XLRQK#3t"E=#,Q4[w5Wbk*(2<?(zT
J{$(?)HVc=b2?/-Nl28@UT[qkdordAnIK<yg""';case'fr':return')ZuKC6Op-,z0dC0#1C;FP<A(SYL@YvSR5_56@H8o;L=Q5Et+nA)98o<OR$?pQG|279DfUi:
>vHtDuGR(RQ#_gVt5B-
eM0>@Mr[Fz%&@&5<;[<g"Mnwe;[_Lcc8,;&[LGxI}qsDloQbk!kbd@G09&A@f^J:VZR,.:b<<+)w/ZP)_Q1#m@r2E3B0iLx$hq-C5muR:&[$ygT8=]Sb$q}7gS)V=2=JYfq7Wy-Q^"/al1{0qy8C$Bh`{vov}b6`hbD)5>E7l=#QT8~SFhnAXIr.EB&1~m+b~;:MA
GMpQ,vVy>x~+6+vbpkqpejp;c`e
4H%;:i"Q."]+
IqiUN
+V%RR*RO16n|oDlM
GbL
)&E.yn}`,0!HY0tO^ut&EI|bQ8,C&WBvZat5PX@!pPfJUd2%{Q"f{s$Chpb9{`@$w@~co(cyqRTbAm_q!4jq
haaR?IueSY-0c:jeQzCHjT.V>mgsnxQT>&1k:)V@+xTQY%Yu4}DFo8CQhb1oiiqONh66M|1twevPL*H0y2,/b3os_p?:#EBR[WoSQYEBD<fDIYh&N0rLf&SYr{:zn-.=a1sY_Bc/UKI>,e"W!knrctvc]a0>%S2Pxr3e9YMmQr_:CK0%:h!!O=brHDyVsys.!(*5`dI35e`J[?@XXs$aU$Wo8iY++myUW2YFh
>y45S}7Egd$[U91zHh"nJvjw]B-CFy)]@d"0GqH/oR6,pqUpX=_9^
L?4S^Ugs7ty*v]d?#rx!`>E(TIb=gyIt-6jTys`ai7n;4vp%SaFj@ZV)"5
S>}@l_=Uw<,Px>k)PwW0ElR-x26Az.jAetCl"a`kq^{B@pA3Oysue.W2yM39"ET6,uWm!O@HF*wai<<$Q
R;E7CQS<="$ta0u::$g^S9M
]835jHD
=M0/E!Dl&qJq<=M:O)
odH@9HFl]8R
<Il?^QgZL:c,"t2WFWGLm<gI&M6*(JD
w9]5n;KjxZFBl]SbaO#1LFB5/ne7i*&eR>XiNy
;$&H_#5Dn$%W<ikUTWjvufdt~
vEsP<bBCKP0?_4vVn(NEEepqvsW9zeVktVcl)GbADX{
)WPO^%f]1,)@npM.*FTd#@@_2Xf`Gypm;V@Y8dYLIBdM&#*G3X@8iG>;rf0K,[bq1E0n36)^0B~TU$O]yxGBIn+5+Y_RQu"W+etQI*w*^G1HZT_>L_bZu[=IbfMU5V-Ij1=1-_[U!3#m$V,"x_q09`?1X-VBVKGCYge;X
w[Qu-I[HjQ0oBZ1d[nN`RH}Mvd8:XxQy;)$Hm.PPjh;&28Q^B]MZ(wQ=-n0(jO{_W^2Zvn.-<G{K<^r&4[6C/H63}-!2TV15)6^3ZC}VtJ!y8,m(>OE1$V4Cx)}_1eRsKP4!8t
Wom2){twMvB$FlFb`/[T^3T#p-n)pNThNip#MrP2sx$ej&,hS*brUTU>kBVvKEwCK+D_vfkr`@h2x|,~C^Kg`ER=VSfCLj->sq_~Wi/t^J)XaJ@(/DhZGLGu6CU.E/S|%{dnG*yk1p!*B?+.W>:!iN;RT,&9XoyV#lF}tL1_Q)HniL;K,!_4RzIrns0R>!OJ;8k`0)kK
2f8B(Ni`Xesb4*)e;(*xfS^KN>^vSfP-jE:[lW%7!9YPTG1<6I2n4:Vm+fbgHD$hgJu;Rcg,SmbCh":r[F=..Zq9cRP-l*FC
wCVJ9=SwMu1`hEiy?htfB=!^*6>SvDX%`yY|XDs)K2mJR&Ls[Q[S&(UwY,ZBEpG},WOmjcT
GY$lM^(tjy(JucvY8>Ld<I2=07:F2{+3/5h,1q=aO0.*t9>)(zj6`Jt6MwviTb5c9/wz%k;;OC@w&NKdM~D?^c?.QfIXC)TQ5sT1t)mPyTEg$$MMB^@lpUo/^<R8q"
1Rsed6paON`iMf*%OU,B!&!U,6.9zZ5o,_~yb#B[WYo$)tCa/ZH#v$ts@P84/bgi{5(sJiqu|.+);J-<cpTuu:d*qK>/q_5pR-Y4["<Pi0vo[p/d<1%:je,+/^Q_W2o]1b?>3@,95R#/Tl#@Cqh]YyJrPkI^(orLmLV8Q%y(64jB:YD83131W(gE55^V)$*)ZxtaTWY]7@}6`p7L_=aoa&{:`+DuZh}i4:j@h_v>]5UxmBi!plf&6Lvc`@{LF5tkh:aOdilPnHt>B6W/I1JUAR_4G!?8^,,U$7j6#-U!GPt&4cbwR>jVX7[Z#YNPhB1_X>#&
M;:2iAX?%^V?tfVmOrRO)4Z{_l*NsH$Fb][EXx"$]]xI:pG!%Ihjc81w]lHijf^}CHi0cRu;7uV38Xs:9O`"fw$}jYw11oG_S!rv2rNV=(ey]2WQ+cn<FC"
P[mEsJi)p
Fzn*EnN@ApYLv}j(Ha<cKv2R_7wUkLc|L=KX]es!*pf!*yTT
to6nh*0FekRfB544pw=]_H1i;ylAJX
G_@LJ1lFS5D9iK_E58XDpmo?ox4(T+=z5(pS:?H?B@eOuZS+m1S]a+j=iBn6kJE@gl[Kj4)Eiz$Pi[%VN!A$ii2_"D#;p<$"F_tx];mWs[#5"fP0j
vvVg4.!g.|-oc&#nw&fSQS5}s.FzuJiM(G+ex<EG;.r^Jf/:ZF1+l;<mRTD#CIrBA@I~P.9nN]6&/SrOCF#*4j&_%run4&%`7JMAFFbL.2`1$z,7O-p5rsYB-1&J=9?Ps&m=Cy@C$=x`Dg`oRt#)ZPFS02nTJ;d:/e1,k`bn+%RyZ~e-&r[V(#h^7c-kQ$rv7n6dj7blZqQs7TiUV&E|nXgs.YY+.V:9q(1%CgY+eZ
=[f)HsB`qxISspls%q4(3l4[d;oMw!mR]0BeA1Xeo!sj@*gb4e1hA-8P+kiQj7Ga!`rwzsIY+J[kQ.B(V&qI#HWI!g@Vu-UJ5#!QjMdyvxEuR6p&.6MTx:.m/_n5p$Y=Ge$3OWR`cp^F0M!q=;M-^h@G=Qo`$?e0:^me1O0L|`*)5441%M]lwZ;r-W:<;`mAaiSg
Q_+~x0%1"d:J2P#,,W(#lL3u-Pgy)-3~7pV-m_/xl3EndeLBANiwuq0Z[
9o()Emc;-u]w6y!mr-8HOt]2Ja2JTM;
3[Z%EZk=.N6nhf`/]D6rCMj5A2bxYbdxk2k4!,-K^f4#rO`3%8unJ%xIa0$[Z8K;AvO_(xxtoi@=owJ5$(@<M+(A#Ge3[rU+-?eA^VH:<|munu3cUL+IUtFTCp4uD,8S$K"zE>t7DymYg<23gsdrZp]{+*rgl~9cQT%_m6-ELE;ae.=<`5+]h1:T8%BUaC.]S>%%Ey.#(F4I9#^x,L
yIB(v-HaMFfo:Lo5(boj<-gBir?HL!5e_Mva>DJ9To{`jtg<JkD-7&:6^ppt,x,kdPrshv)]53Zu
=?E|"uDZBu?PZ"./NU+n(!jp[X,ST{/z2Ej|Bv/7rz=&q`"1poU86f:Zw^8_IJ6=iT#8PZ#W[!^hGGVb.;
a9"tIJP]18RG
HoLCaK@4&kwh&Jv]tlK[@u^k@!k)(mC$-ri`r(EWBF(pbg8:Q@Y4a,;!)X?x@D/jVfS?g2`MIQAtwb&dR[*Gm%gfb<E-t=n5;x;t_~`Qii@R$|q$5LX-2s_P+8j+<%?r"pZt1ck{U$i[G<):X5ycaL9wOMMrwgL)hY07+MZ+,Hestzk=xajmI@u~+hi^.edY8S"{(&Y:QY
|Yhd3EESqc8`|Ni^0HjPy:|hS[.E$;fRGH.gQiU1_=[[AC7NqWi@W<VF!!_5-9_?cy`)i.X^+8x
{TMa!cbJm7d,7lmldCJ@2J4@s7Qq?prW4*:jX[>CaT&PQ:Chq^ELqL+>($oN9Y{GZJgfbe,x9#K>(4wT:#hcY4XY?BmMw;3mMl"i4]gE:($iDGHEP*UQ}>W4su~sviX">ErfV4g`h9pG`/acJZQ2H8p?J_;M42vNU?SW;naIlYzp{H/7wt=t]3a8i8xF*SeNbVF#&n/)Y/H/:!julO{y(,&E:rVIGOvsS9{XP)A/5vRmF`(
q$*%}-rKedYC%&TvokK:$!#FDo|PO^p2.#4O?E$gM6Ax0$YKNVBpOCYRwDC<HYnhL>6LxwKcM*gM/E|dJ!he]4;cL9Sseu1G:?[07"mpCy7yKuAI&WfFVngp.&o/q5|Et(-Y:veu?alYfjRK~Q|gBrFX`:hn9+*,4yfU`@WMQ[5Z
=XsqF^cn:ydDp*up)q*..>4D6>;)iQDQLL(I43v/Ck9G<a:E"Z[37u[tb+2`6bnpgkvvI*ydgVn?Hb?{dBVhM}J@d|Gl.ldvBl=Zw-nLoW*8)yH:%[ojB81:@h?qFxE<?Mu4%<Yai1b!:7mQf;sW6@A4CDd6<[a6]aD*#q^sYzOlq*!VgKfO.a1/kh`*h<Z{0_quedszRD
rsm&^,9*x<?Enm,9jmW%~[^5T4;Y_eNBUU`ei:Zc[In4IV!+pcIS_hd%NU@*$VU)Jl=k[FC3[OQes]LAv.W)$_cK+-w/9?b5a8{L:;C[3g^bI`n=d(h1#SOu^9kK(`#w*:lkE2S%Iims<xqq
R0RxDVh!cco<>qZ>ei/4u<50DED:(3C17(5"U
!_PciAh|<{A*x>4N^~
"ZH^SX3]glgFSW6ryCxOel-_py{6^';case'gl':return'(ZuKs5Hp=*70lC%"0-&,0L_$/iisJsErRk^BK0$I$5[5v99#7)]Lr*b:nLpPg)#5x*QB&r)h#]0kLfw;(I$jlba?3roB@
e@5^Bv+XsSk9w
x[^B;JgIzMA
3p3[%FMuL40>LIB6=wtp9#s>;[9[=
3df;#*_8djK=ILVkOeC8hvWv.lE
_9[d/P~ua#wt//5h(g&(bKA<=3+-b&F(!-T39kJZ_tboW,;uH,&bO>
d%Ik`r;S951e<(*3vEuZ4WF3FVi|U.2Hx9gh8pyZCXP<AHLk[`--*xH>$O@vV4javVFB&V,acz;S(UR$=M&B`g&imQ=g#H07pob/%a,[y5earZ61AsW*!T(Ks+As-[M..}nCA=>%=sd.Z_hxZa6**AgsjhNW:p6imSmO"=pN<ZXHY(6)Luk8MKPy@{poOVm_)gE@c/OA9~%[Q>m0KdH5]K&z`J;FQn@kd%oq!)-4xSLUys
Vi^y=_~KAI
EHNPoH/}GV+Ss%t#N,%GfLaD,LF;[&/d?[=#Bu.KVhJ[
7"kL&Nh(f&B_m05l{&,5.
_k$HBgggbnB++>Qv)q0DW!vX8)keqS1^[;/WrrKme2h3<Zn3l;I/)+c@6/v:,E|nRk@T-A
Oe!A`:>iFqyS!vo9(>ZiAo[qx1`yWmTAaNq"gkox#xPVX$R!(Q!>bxAyhTy?4q*mJ_0744mCR#2-.1ROR!-Bl#D4gWpWu}pNjf_utE8xPQ:pQ:bA0t3.[@dwB3vq,lo=w<,m30+xE>M#-(u:
=hYJ[6|QZj$[(sH;N2O)NYPUBl*WX[%h}ck0^rGAV6Vlp!I9}Y04pp,oW;%T*F68Lm_,~Rh;0w0VYb$!Bi+bIYT]|e
t0?LR2r0,s?YMm,l6Rv;k,x@>0NrLAn.C@9189LwxQkBM30IElv%Pr05aVRm)#)HkVJT
SCALia^edHU4qM?Xg6PX[M,"N[i,
Q}!wWzA#A&25#7[Q*S?>=|KTq&G{U{mpUl>Uav+>cXa;%7hM"90X/ldId_LQ6`$G*/O1JO4d2OFPTIua`P!pq5`^$"I!_*DX(6j%806Q]8c
NyB"qI<MCiNSZEh`&5YUAvQ6nG/d(119nSxa4.o,Qfj?`7az
sF^I9&FFlAMS&Mef;Z%X#UYW1)!ai=>_;^gkJc+66N4[`gE,!JEgS._JBdy$SLE
4_j%(A_X/3L"I_3Xk8h(9=sWV/
3;l{s/!0nYWMs-xIs#+|uNF/O_dbEC3"6U]%hh4y&}hDARA870_jJHGlQytWyjovc^4,,;6d[{l"v]M@HuFQT_)#%;4<8o:f*mV``&p0Ck7j+!L(NEiQ3V(6N?4Wcf_LI>efISRO399r07?m":0ht5K1w4;T7R"}"H
eR"ZER^AiS+$[[u?HY,D"5]sp]1K]H2vVj$02[>%@?lM6K19f,i8B>/L[%6M4bP2b<u!#G9ey5qrysL86Yir}JwGEe!pcJCaLS%YYb$n<v2y4o5*xA[N+hTmablaB5opfL-bFeLSOCW8NwZLhIQ#Ymh+K^.YP)rLK9jy3WNOCf=!al7^xM5S"Go:DnMb{&
_]a;QsjtoDXiP5(%kWsXJaKK2wiP8hdZ;NRj9frcn4M,eq"3]Rbl4j()c&X/v:%y*taJ?"-Y6fL[nzFf@?);/zR@@`C+Jjlc_"5FTTPO,+r`.}U+sC-mRGPD?G"lj=iENRJc;U*O<06*B20<`
0>*?Ia"
k?!>CcW
1R#uMgOwk|3ZXlhzKC
x%5K*V!MxIQk*&23.jB%k]Po;2WP6t+>~
qW(OsAicp)
PLR<"s,uE
]#domaj$C"xvExwfPvkJ#!9A]2AppLbQ-
C$ip@E+9WrqN<f9$(QI?-
X@,xSQ:r@r[~D$!-:uWYSV#S9Xh$w0:>o!j;uz!8fln[f.D``%T8>VK{L&P~1Nh*c>y3`(aa
tjcxG+73w7IW^`7lEm?8>[6q)qxo`:
[S#HXvQNv("fj=?Yf7oGuD5qMj[yK7
1#SbbF&Tzez/@*("QsvT;iB_kwad@*Ta2%+T-/sCL7/ai_T1O*Mor8~ysuNq|C6&dy=4If,0cJXy((^ChE$W)m8XW;BeB,i+.`a#=
Ale+UU/42+-&E/>8}F.LW2e6Ry?1fYl(Wj(iM;x[<MxWdlmpQ-r0Wi-NeQ[n6Wh_zJg>}yu#y7u:fI]7uMI[)uS=?R2AyR^f2US=j&D7a)jH[0QWN1[?
#b&PpxYM$.WH]|FR
>idO|
7:PRL<j,fP
@2aMsx]0![EvaL6ag2uo
-%Ne<0Tebm*S-eQ#T=QmGQQ9IJuxk(5,gt8pftND!h-k5]!"m@H>:Rtu_59h
DUh{v-91Z`ohk1WC)>,
qh(!)O8)es=mI=`qttS`pSB/"9axE?x}2]r5CK`~sko|y06-[0fFf6iRBw[.&|+W0TqfM]0XgSc5Ct/UOY@Af
2U`?*rY(ILTfe"qY@&I$>(Abh>;.7uyhOKjY4Ay=hVfLPQx.U&4=<4DtBQ6LTD`GB2*f`-5RP23O"!</?
*w:]KlJ4tIxtU%"|8"TtO#YhSg:;0#y*(]R;n6D#+2unZ]7u-R3[N~i],zrVZ32u^yIKCn-zab]E
XC?<1
2Hc4T!|P?<N?+$m]_SysS:^Ut#WurfqZKa04!hXF?G/Q7*fqBm?:.hc6<L3";K:2~5mSNcvC)k!+!dNW9[dW
Zp.S_u<[yHq|c#$`JRCTxHtk(F(8Of=$.|vf.{$GIAV[NVSZ[2u)*]M^OM4+Z-+G3.)}AHT<RV3QYoW"w`("?pe8,hDpJ".H;=;FTNF8F&NvZ)>w"T?EJ/(Ngt7&*.B{SWA]gBscarDE@!6nlDe_`=S%as6WV^#%>|V2RTkUUB5cwn+Uff!}Gd0$daZS;ww*$|x*p|u>V(7J!MCtl`PoM$B7XxLUrZ%ExjrDuKJV*qaeypl0_4
2)o3E3-S0RV<)]?qCJ~GWwW2t
tHC/4qW7
S)X)(c:A,.xwq|<^U/aY3I,]->LxT8FsVcTz%?Q#,@lvU8EKQ*6Xr_k/E5tmR>ZBU_,S%1X=5M,>vx_K1B<-M}g>k7Qr*e=@iY7D1>NN+Luw8XF%!eKe$q]BFG4Sym6GixJ<4Q^SqYg^ds%(Co:5Ou=9b52O(Y-&b}#b:}3~lo%@4#w
FK<#ntPB7baf
TUc64gPRTUNk`Vrx61Zjbh~,LT0$m.Y1_4&4q9|Ck/BB@r5+v%r))T1XcL=qUNzeo01ZV<;7
jiP8*eDe_OM`9_[QZm2oKu3LntGcY]O+p*s1G(1w"rw6iicS/0mHKi-V!
DJ+p.ej]b7JO3px7r9"e3pZbXGVDDo>j
Umur
1af01Tx9,`Vqe=vJtP>YlJCx$%y_?vs*2Cy8=*rOBs7WF;_8!mK@<DV]g-;?X<o2q>J-!3H+6tddA>*D4|&3y)"=^~JDPL%o&iF!t&`5*gDqT1X%=.UsCLv@0`:{D8]V=U`6ua>M^sXI_-?m^WTR;Re7;{,#gSy{o<t%O#k,&<3&7UZ";I6E52BX9@%2Tj?b.zLd+W!i$Hs]^+bE.8YyYf#]/CF]4=j}5~UDZm)7/W^0T^"ZIY.>1o-pUt,_tW)Si|aV
[_,9]IUOmV=2b$]/&Skg#na*2j*wD.ni0rkF+3R[]CWK&eH>HcLfq<howZq.~t9wgYSNk<>:L-0rKk/8phF:Q[RwhBD%u1v3i4Bt`O9X"!?xG"!pZ:D#H9MK(AIU@lM5"3
/Jj8T&_0qg6^?=C+qQ+I,rh?v3p&P&US0tKzCwrNsCsO<V,P]{poE48dysF0]ug8*=^ySqDE%*5Olx+c$-%{;
2O;C0N]>gMDfHA2_E&EQc#Mu(KN=TpdBSYj*tTG*DtNMDSw@4>9*gso~^jI}og3x?rC%K{8$2&d]YOx]lUgYr$%^"qRTZ$($%*0Psd!GU-`ED"g##mxEpqQquOFRh|:AyF[mh3EMyT5+y#BM!ySMl+"kHjKDI?t$ex$k(ifS9f,,[i9sC9pcq@3Pa?&AWX^,S(DbOL[g,P:MZ;h(6.gL>t,";:7I2,p|bzmBqiBg@p`D@whEs@0Z1Nj3ngG-C6=*8nhkcn5eSr_t0|>^c*B4c5aF@WQ>qb"NE[YQ"i5Bq<pLp.uzlms`QNh|&!AZtH19^G-p#/gNg$.V(RH2c}w;=Owg)|N-fIu:)yS{sa4n>Y>kJYkuVU37Bm;oBe-XTZO&^$H!qQK5F[QI,w&zDujlXA)8W0BpF>7%g!/7]em%EE/@/o:vK,$jcxO*J_yGp/e5YUCs]w^nC;IO=!Wh1%02a`&eO`AXY]gqijT`UJgO5ni3Frf#bEbOKg=V7oZ{2MUlE[a``TFM_:].P#GnC:1aAxC!Zoq$SXHb)9lBShN&DGdPF)Qin$u
Yf(e<+vkz(%K';case'he':return'$X/@qaPmt*70}V3W?PxY&5MY3s!$v;>fsf{0JH:U7O?Sr]lH-+(L)
FZ
y0BFvUaTllQ>5MC;$4Pq_BHjs1vJR1y$nKJep]U!=OKXy3Qm5(l_K[QF=+ceq~D57rlIf?yU4~xs&j)#GkHw7`Q&nsxJxsSEZHovl1p1q_tLa">Uy%,K3|ay7Cxs0SstR1BZxa5uVmB]]b_CdftU2Pch:,l)o80wp>(0=9w<,xA
A~k|<VeoX{Vaw8nBuv@]MM@tmBC$1@Hz@;vO2.T}]w2Es?]xBa0!7c
Y,O_5wPtMbsna.TeEtsc9j>5*MI4KVR&q6;.=ssvl)sE30"6THh&Z2Roru]nPl`o2WtNU8f,{6O
0Q0u0]+";cuf3$}Ate*Um:Hm[u(^H_`0siE$+nVj@59EfKbkS?pnSo
0F`cpL:Q@IR@!DW$#tWb/Gs!9Wa%S1,pQ)$fm5e+Vp,4?4)q:KOI4THjkiX"&v>5FJQUneqS!4yD8{2Ih&9:?aH`nBh
`qUVQ-L{uUG9),aPp
NRQE9^3;7a>Ds-XYkl>0-nbS(q*()xfglQ6-C-rM"=->&-Y8-NFMqIhuE)TI-z#O11&g]ETMFs38[d`m;y20yd2~BMOPiQ1uaod!lx3tCR:+F]28G;;i8)ePXEj^Jq1F[,]}M^eQ`AR<&Cta2xh>)Susu,nWh[U?t
1Im,l(XUaSn*x`K5hpmk&N<sTfjW>4"%x;CxrAbfM?P
jh/0dD^81)V}yS7`<!p^yXuDDOBVr4WX04l7hEri05x%_k/"/nF7htL9<QVv3(J3Ju*{n(HWtPmpl*.rt,*c=(rohJ@r1yfB+N@9Bc>wGjM-I
mQ^<GSKS
R;L$~
Rsp#xQh<Z8LF#@$N-Ci=mJYQG[!b%EOhK-:IyRc&
/&!D>xi0K/F
,wT+eqx?]22>9ScM9R&]`x0
nwho8_h2i^V}rn67U#Ek[2AIl`5mHq
oHutVatlt_?;wn4%_q?:^<o$lmn&pL{
YB$/o&-f<V5;2"gNpk{4+
*TIbIrPF;T/Un^LS<H=t)<L&/(eGs-f1n0)(-@t
xjq<M]fA}d@6Cy<JlI(.24A7^Tv[AIivNrXS
261le_s3Q)X]dxe8_FLDn@lwGC6@n"b%rx^?/%q&WNs)O>3jp_H]O660j}IMX,EZ"rn`
1U`]Npc8`#epdBw;9oO_|aY9Z"Jj71itTf1e.Pm1`:dy58K*
`#tWbrgHT_
$[NaGy+S6t>->Y~BP?bJ<O|brdHyj4#Xx;f@fecV;fNy#iolj:!B
l[aNKn<,:{v9]l/O-Nk~.*A+8ns7hMH+06gyt[.&j7riQvYc6wdf(8.>Qo)=KfG.UY%h+b,d!yhwAS#XEQ.Ay+"ReWH`i3(=.yth%d<HP]9gtYVfNtuvQw2S1p^T)EZ9f+x0C&s?sxR?BYm!Mf20PB!S2L5[ckK0-k"n@-S8p]NLY-gO=>v!@RHp&q3z1tR6F$o9hUmCB
*"ALR
,$cQ"!eU&8dTy-nV<,=+4X*4*V;=tQ%%*B8ZLWyVJv.:Kcj6DxJ4=Xft1:P3NA(08+]/;xXSRN2]*lcR
B@v%jrP6dX[5e"#p.s17RV/bF?nAmASM9-?l`O<rt/>_h$Q:fvgK&?t7h%^DCts!yu6#MS%2e<deRy,6(j*R}l,)#0;vw*Y
b;J1>5iM
qhy:4t8h597G3E8BZJf@]D29wTHG:c/G1R/s`aWb_|XdP8"P$U-9?,,aCo-.a{)P=6%M+Q[+W]&3iidCN,S/;<*(%SUc.T.~DY.I
"EX!rdz
$p:r!Nn<?"PtZWj^H$u:.PL>=TTDp:L(EJ^xfNCP#A$pUhTB?0},mdsjXj?4*)&i~.Sxr:T"s$r:yTHD})tO[C-&u=DE}yD6zM{]c+)T1?gLd4qC&Nj=3?tbgo/2U]Xr*_K<8c78WBD%>*_
5^I*Viwm
sMry]&L_Z*;/RtM),Rg^a^"tBWl704#SLv#/egX>gj.2yD)`cY4`LyBL%;6Q%W9@1F={DWBE`}x_1{Wz2#+"#$idIsLb>r9ngzxT2*)*BYkfom=Iits,>P5=#tbbP;ZH86"|>vS=GZ8iPv!xc!t$nWC/R~(If7^9yx+L`7#QSHClF3U=K7mD
g.m@NF!cYJQks=ewrDCnpJ|2sT%_z[hOs1JcxY(jPt9EVq%$-t.#BF4y"Hlh_$SbGj%P<5z1usN8xwkO@N#Fb"AwE&rXIqq_&_KZ_#[5i^f&J]5j`I~ZSHs/|0rdlBAPPuSh?ur-
tBBcYeMMan>J71q&(SVylwkV`$f%v-f9B(Yzd;;;b
(2vB*p]2x[&<a23M]nkPa11kdB,FA1gsgo>l)K^.#);6rXOSFa58hV-o95ojGle-?t]2)ur[m^.*kM+R!oEgSxM3bO?NkpsEM+Tk+mwX`{C6C;lP+7[?6pQb]o5TO~WH=`KYu
HH0[udRXC:oI!s^SwxR/afRwDJ<#]~_J4FO=Q|Y@a0t
gYC}6]5x#Bh<sSb,.`XH"!6w[6nOWL)96*JJ7cnw`@Ep=[WGpM1E&A9"(NLr4%oKS"@}_N$0rob(7GEnb&;.l_9r@fY|/GL^<:C?%LQ/a!r^jfB`o7rC&GZjFtXTI)Ckr@Tv=`Y7D`U(=lx)w"/:)Z_fo8/jIam(Kz+uqJlc4`gY&^-3S6&:$Am1SrEvUG2FS2f7dJ)b7MjV[@c_[3bFwn!S==:pM$>SO=<6b`!#XL.h?VO^#85.VBn
,
t}%`KW(0/xZg>ca2.Y-E/Z0S"HqQ"-eRT#@?b?&E.~;Qk!(9381!tTjg!UbHP2m(k:PNn&+<?TdA-/USZefcKo/05AUk3;%MA"
)Cp`D/%p+2v!}-~011eQ{TG1r*8_s4oqc=gE^3uoz
t2PQ[jn>Puf`0q"2B9G(FS/@zT
tB0Sl8@k*U88X?F<2H9WrQ>UrB4Hf6nDfue(Q&@)-K!W4cEQhD94I:mqiZLkCj@"4H3I6&tv4(&y2$5eM/dQvzA`;-y^6+1>RU)|l*E<GH*
-=2U11[U,1gf`.Ab^LvG;=I4U4!k8|u*;sf5q|g{3gj5Z/=<BW.vYy[cI`U^W
U8UHA4@[L8@AqWgvhc]_V[PKtKw?!K]`_A^,?,/Am)x{q;txc^OJE6#fbUS6/W=)UehFu_$Qi@C|4gGIVODm0!dv[y&`C<;fn{nFs`Z/_l0zQs,CH!@/f,Yid>7phdJ0^7yXQ&0X%v[[ReIemY3BJ.QahR4/B)Ez*?Rcmswv[Zw#`E%MpZ8nCYp2I
bY`<D`GFq,ZL.;<Hu=)jLE?N$J*Z^Z<WKf2RJN@&P8J?/PEr40/jDn:67jD[j<Mm@4hD:.[MkG;r&OYDF?[IUi
k.-j%E5Wg6VL#pbsIGkO5jV%K]ej^JSJkd]>pd}#o>V
:LH_uZwIQpXg;_,ooX2I".|0`j!,d!KWk
gk%Y$/l?H[~#r>lP@
K#~Q.jPMIF8uG*nnnv/6@GjKSG_DuoFoP%%tfQO9Q8Rjj1aQt5NHO]!C}QF&hcR2P//JY9kDw8m,5hi#d33HTjkj$=xI]5,I(r,n9*LXyRHoT
Z`FiWscxNAlw;w$EIRc]YW{4eeeriCwrv5u
i]mA1MrmR!~
=`&cg(U-ml.Z.UDEbK^YkwlBt
vn{0a5cR3v0ehE&+O,6my@:Q@@rUCj#MyseIHBh7jtoq@>q-s`vuLju^1Djx}8,_"N_:^Y4/3"ik~N^eK<O,+IayY(Kl+gU<O,*]+:x>&=H,O4Xi81@m%`N#V_sllu?/M%Oc*oJNKsAe1Hl`Oq)BC1V;?uRtRIXYrGXq#l,l#B-wJLe*>!ys$T8N5wnMRBQ7`QruNY+cE/KjSMdpTHfUP9qh!I(9omvgd9W+4Pt8bY1Mq7PMzG]d}iAD2W:
5>:cXGu?pxZ8UDo6Rl
jj9$y&]5k,WCJ8hw$tp+Ee4n)A.X^HWj.}:92,1Ryn^w4?o<f
/l%#7"*$xE2gZ+S%$E@+MH)TL..WuT84l9Br4t"WJDlmSk[yZl9c
<o,tiVT)f3{wHu{x(,!`By+=nbC=z)*pd+t/*j^#}yI*=eX
jKWM&OI1L4B:?,{^s0adODc69jOa(R?g(fc!w$nH"XMwxL_rcTcWa:UU%s]
vnOlsWiFDk(&:!"(UE[)kT[#24=Gmq"&63I#}CQ:MyK0l3(4fga@_6_5I;EVxM8+_J%?|rd?Q!e6-gh$Tp]W?JP;~01x`I,g6,Qi{ge`V6*(Jb6/dV#-jh6/0j*e=):`lRds+Eea(gA(7=@<Zi8C$oA';case'hi':return',h_VcaMDY,{0|N+w*=rJ%2@MD2X6]8Mtr$A"zS~>VUPoV*}N.jp?D/$.|Z43}!y(wqDyYX`*cGJS~9{HXe-u"2dVa3Y9SaQ/}z(P{u
ap>[lvyRuljY^5A]ranuH3H2g@S,>tm`o~yvmXHJtEmOcbcpb"+[Kx4r5pM]2+jCfZh0xQAZM#?K4{Sia~QQ:Go,+0*YK5S<b%xgY$w.-8xsl=c<^rE=G|Se=d4-w&qWg)qpwag!q=Ho&IM^9g8}0HFD`P)=cI18-E[B1*c>L(clM-Goc5owYX+
%2lI7m#iu_!5VY@6R/OLZj;
xmM>N0C*!2O<qvy[h`?3u"%9Iq36HM<e5Zd<9=CjoQN(p$cbrs.{cE#buo35YYK6iXF-Cf,WAMK=aKl3^R7*@!
,4&[$jlaBsOE7G%,H1[rY*7?"&277i?7f^Q@2Z^T-J>"atSG.P8)ek(@`/j8SN5S~cNwJT5d1juQTIf)RNGPR&@+`[R"n7!6bqE:Hjk!8e8
0InA
X2/U8=J+-`8$gBhaEj!#n&(%ja
07pi]MMr_256w^ZHCniJ+V"fcbbuON6^!*;G(AW47>3TO6{E9_$n32C)
&eeVaojKNGUum{RWH[WRc_f/aPR0tUyQAUD}MXH
LCh;6k?
qFEERoGV-q[dj?F>5OxtpKsq$:wP^eYnl!9m7)MoQ5qGZOE`k}+VfDFbbm;Bf=6UXI>:ZiLu:bT4HVjK`Aep5}$"+Zc=B`D.sSV}9EBNwJ4E_+&84f?GMS)fX~<2UGu/@wrF[%qD)X!@c-6ndUkE8y9eP3(uUJLY"uuu&|<q%6wP=Xm_b~eG$FoTr_Z$<>6dJFr[nuJ$r{]hs.9dhN_t_>K,8JN`QoDeg@OK];;XhQUK9Tmc%JZzOKPX]A]cCDTeCv9T0E(Q:Z%v493.,^#)
yn*R:4uIdh`f6qHL@-Xd2?Gp)dt"el;/qkAKROJ^":/3iPia{?[=]9M_k#+vM2{H;,t<u7CKmRgm4Zj[!/"aN%HFp
:[}0yfZg{D;_Q&9H90]Kib&TC90rBm$>kJ4bB"7%DVbDcLASN<0KA
KYib8*?*t?}0"?;&bGlCur(.x:jg+[,%PbIvfc"gcpPWPi_6"5Y

hKA-7aUEa11jC.H$/
o=:`?vEDy>3RX"h(gh^h>E
XgZLq&Ky-w2NraGWJmf%J.:@j=zeRG+=7?Nv|ai;z]^K6KHl`3<1[llU0?F!V?7v5;.%9x=IAnZ@2Le+_M%alQU<F.CUxld]_&p5IU6:jxDVZ%#R~38<!(ufQ4o&8S{dZ5rn[YY$qycX9wZXz/lgf`qQ,yI1>?Si^0xS:^1&2[-?9a,h{q3B$_<PgNO.{4@*A3}x=$M`Dvq^hS)^ubLy#Ku7F`qu3+*:}ntMZU5ES3,^E?dL/H#RedbJJ0EQ4+x_!Fx@,blG3i8M~m%0gn4?g2x`k64m[^Bm@:Is=(:cRl1@aIkw.P@:(u[9gJOp.NZ^ps&,3-fN}N4W0!BZdf=@=6JCpluXV!JJUHU0eFbe|^[`3L2:=d2M]U0DdS*.jyY-ON&FgTvw6<Vo=sW.}vr
!<jQz0;7R%xC07#cTx0!2c6@fJgZg_]Fg-cY{
(l`TvKO:RAz2~szGQ1"7j:}a[]`x+4NeL;nvf.?ZUVb2XMKalw=WrM0Hi0EEP$e#djaJ]NXr*UN:Xy!Q5oqm`2JW|&&t;1o)XN~dG=Z!N$zHed2(.2K2[ixN?XWu9AZ
^nIDLb:n,%[:Bu~#sX:#X@SWtZr`g:tmGWdL&E[J3@RD|+r[pu8"1qu%3b_]q
"?otpV[3NKPLR&
6&TVjgD!J"5m,8yHSQ%+1V94n_^M8[unn|QXG?r@JNw(<.:h"N4=>f!w"GhF;@o:#%kN8{eJgfbBM}2H
|4e`rDF#g/Kur%rNdN~S_J95@$lFSLUqZpu(d89LAtDU;ZF-;w)c2L`YtAWfr,JG?9as#D~>Wx)sc;Y;Q/>kVd$DZt0Ts1Ea-9.m3(,dDYIy|TNI-#(@Q@0OCnGODa/%hen>Z2
J"@J&#@NE<i9%3:7t%@`4PP70/8M_F6(pb2Eg6vAj*kZHZ(GoVF34{=!=ZFgB.HpfbO8S(Cn8FHXS68t%.i35GL!GmK8&()|%FM`u5`RyAB"tat)-4b6/}mfQWDl%?^xPny-3=&kGU0y1Y(R3q6O^ZV!#MS_ude1+*5PfX,pkb*JP2-!o?-t$y,&/"ks=4"$pV&b<~R-8|4<e$w-<(DYfbE+QX<:c5Q*`^mmwg"M_A/6DbRRv4Nm*t3DgajlVO75(GP/oPO["/"$A^Fs1A<47q(AsS"9tk=Z]Ay*I[N.!ly5Mr8S6])4b!IY$)6C:j*M$5GI<046T&vPPyy
`iLX4fHS)Yd6v%]1bfpTn=ByE5+]
<J2#6"D#itp5Nn^)Z#!L*X2;OCih<:js5W}NM7_moS!@,:-M%b"/j6cDLm>`ia|Cy2NL|^`09QmqYFg64yi4?T4)*7i6fP9PlbxQ<x=,F,Nv;.UOF=>xjHDlze9`3$@4Ig+cv_O$<SudnQQ3e4fcBFC9Sb4>uKF"~HuIEhWfN-fGYyS3dK|cgEQ*^s9SI)G.=k?ORr^@/gJoBCtld,9.NP#+Ofe/;CdIKiNVzrm!I:`lhHT]4MHCQ(CmvQ!I~dc]foB76.YV$cDLp
/FHV.$=Yk$-H?oc9gGbtB]jPodO68NeLe[%vcG
pK
[6-)X,PZWmMNtJOfP[&(f$UDP*ADs"~S[BF?MV1<&GEst*t&J%1[@2MQ6H:L"@A?7Zr!arsD.fn=zJGhZJ#$<y,#>WO]U-ng#Ha$oA$V;4#c-VE)2?d$P:`$}wOYdk[)tVrtp87F>NVrImRDcf7p"sp)K(/K>Ff##]TVkHeQhcdk!c:eQOuL+r86W!@aq%`337_;>dMcuuN,NC[U#Rd8Nm>wh[)/tTY6kY[m^hC?q-.9d"Ha:kCo1&PJ"8/SVxFfnnB[P-#[
b$B<ko+Eqn3ogJJLEILFV[o5Vp%Mh5o`$oj@Nx&=GV-lbh>T1fq}cNXdlu)<Nf&YOS_#9r
Bd]R^Na<99;My-.l=]!QypljJ`R54
F
r0H"G/pq78|YLZN=p/m[sCS36WJtIH6Y]yAaqJ`4|5<ymhqrBH4GQ,NgRip.woW<5TS=f,I!*CX1@i
)zY&I:,x$B$2_uM
/BAyUsD?V~BRlAAlgr7v3G!mK~QM-pb?e:PG*wFKkB!D$2-_$OMhyP=;<oH<!Ei3<D>(.5>*-RCP"^u}]cZXr8>TOP>7<TU&k~eFHPa,h}NFQ526*q%!sh@|d|-"@|EmrG:yrPMk7rQ;q^=*,,r
9y-QyOL0]!#"eo"<%mdm-OaFKX6!>Z;M;{
$<J:gD^f.us)A,+@!;gH>uM:uL!A;`49Wj
)j(_d|@}U%3,ilwQU>sOr0V8HPtb<.a:a?F%QW)?-e+P-_G9+lm-
OF[U;KR;M3=n5y:l!@=sv/0T7m3]N#56F#83g/y2h$xU`lgE*Qu]i>!"KUP::/WuJMM#yOAU1p+E"T8]/>^c?9
<HT(cH`*Dm
^CEG=o4&C@X<Il?dimy)yENl+Z56p/HNa9|u#r9?inEGZ)@0tsR2]KtVqTJg$k}>Vd,1eEx5$W9!jnS^wAiQ0x4+/
[G#9MyK#,kL:<^1d`JRw4
5Z|8&M+ML;CWSZSK:JA
|5GW8$-4M7xoi(@j/0}[})^Ww.Ex
F2$CyQt2J,mn4u^Nr:5N(dD$gPjcF$VsvuVJ=XU*u,^yxS#Tiat"U]Ox6$dg3YI/>K8>0dh}n/er`_8HTzKEL3%7OR<1M_y7i&0INu]a;BGBx)vw1g7vc-]0UP_SM`j/A!bEx>OOt*Ox<J8lgHxDWFRH8(S7YO2{UWBW>_A
.{)`?Y1eqR]0J6Apx<eYe=Ic:/@.,+qE4Ksu;m8^B4z#
udoc`
kX)<ngvL(Hg.j^>%VOdKeh|pZKeeIF4OrxnZdGb-FN,)%9So~mH_"<D-uq/FChj:>Lw-CFmRegnu<CEs:spSQs,2&&n$I68.GL|#HaMui@E*!Mu%i#eGL.BeU>@Vphv)G.uSt,^OXn[@L--XN;.%MJV(<4#T9+8uN]B>:*%SPIi%CYd7353u[sym5L3>.AM`PQ@;38A,>k-5}>6JG.cemE;/%L*r/D?!Js?O_1ZMc]!FC"]tX-G99@TWUyod5pakPT4/|8XjV9qG$IsQ;dsh(;4P~p1lvu(fjUG5Fl(M
=>>5+CMlP5%ynsY|6nK9k^G"kt6ZAX",`EB4GT0}iSPF$dNV8qop3{xBQQGGWvD(VPBh>K<K?~UQ%zek:x]Stq5Z[:4!g[GkXKZEDBIF:+h?CIh712g8hZG}V{T@$1hPEKqo#ett?X$6I8gpmsomuqd5C.b17m:
1
$
qKS"VHJvf*CXpMUsS;7Q5P.)lTf00Dwh(Dvj`,;kXEI/6!)BSnj`69Y(R?C.5Bm(LO*h<lu
>?Xz,(NpwK=h(9k$d8h~K8Vdw{XwbbhZOKhhg3Yhq.=vg
*;wEBn-R+iM{Y,vQcG,I@wQ)J#mV<cN++d&99(CA[5]#;}2d4KV[:iY@rB0c9{c_fg!J93V^Hs)#IxLc%Ir(&VA8]@C^)Z!Pw-!M,^=$w"2Q%+2I;ClbB882dl0.V>Ipq!TA!Mw[V/Y"dd:2
1&`Yf#}6lywR2WiteR-N>xYs.tlE2Jvt^>Lbv,Uo)]a.Wo8aP)jHNgWN|c-VqWll$FVm!r4@aG2k<CX%ZYngSX{8@^@AS+i%k4N]^@rDCkWPJwO5G1%p
KW3.T@g=8E$zH.0AyN4tZe&f3nDpx9*fImg<4>@Wrvq.9BUy%6#X$j_mT+Fy[vM"OAaruI-;1NY,njCTb*[e4J3|,G#w4Wb8W]D[k6KIi!I6J$HUE8x6*U8_vf<L&qTWTw0m;YaoZ8*8V9nx)-[)Y(.=:dqh.`eT7aa|@A2aiAHCsX9TY}H{sl5T=+O?G8eH<cO:<F.Qp8heUK%;<ecqS+oi.4I.aSn=vXivd43]>IN
&fvPDx^maaEeR#>Z3Jc/bda{cu==0s%TpVRC3B
p[Wm4,vOI>45:iFuhWqLip[0|BT-Ev%w+l%E6>sraU(qPk?y92?5&@ucN8:jOw@xoo)';case'hr':return'(]^Ks6KZ+/e0|^V)R80=uT75pT@CiQ5vv:Bmp&g4@mSfRO[lP;2aB)f5z12,x$!Qxxoh#M.t4m/RD9{nbe`hNi,B-^+@&`Qy&hGiJ$[<ClR%Si6G{pY,
?tXeBG57`87QH+9s^)3NSvLlYCR[o=fH.H$GV9v[)#V3DUoGfB!45"3sE|:Ep5-C;^`gccMb9gw8?CB;1F5@IC_3Q3A~w{;~KB6}P4gwJ=a<303_OW,{p&&kuj*9rg(hc7P.mN%g6o?{@ljRh.1o$Aa$#DLJWV!^I[v*ay!Q%%j=EpM5m1W~I(_k"dDsbzFZ0bcztp;zSI1EMi:Y=#1H3zFyvF_L0)x8/a@jmE/{VW<DIeE%^!Gh7&#Wffj8N=.2%t?sjkE#P?0y9G:
!,Rg5RE!GR!,R_[t22Fw``N-SZVk.}A9lUD2spTM2>qQZ!5)W+Hmm5j=gg+.3:LD@6>jerb~IeCp?}mQ=)_CK!1E$;/]Gk@)cKRnYqjH<C:q2x;.v#k7+AlD9-XrN7VzQ6"5(hxhkp#0^<gkuj/RPiu$>
>WFwo_?h?.]LLLi>%0<5X?&T"U1%wm2#l^Q`O4T7HTT-Kx7?&^f^h068yo7M(HLw[_td$+<fa_F6Pj4U*&fAoW6EAt`h@2G6v*-B.WM%1khpd38dB8T@ClLfO>*W=jscE
?@SOjuc5Gu5_Xlb0"
tqp!lly(kd-9oGsY*@Nvb>j<)iUGF2ktD~+m#ZaT$FJt[rT~O&fH@2QdXA9v1F*iwjOiXMc,HeJI2`^P4i1Y95F.g1$4gy_a2RZ%k{R,tv*6?$-^MOk$U2
,VnawN)c>*wU$YIw%##E18^F0sYiWJ{CFhgHyd>akI>W?N27Ea)pc6M_-(YAML
?~aJS9)jHS?4lON^v]Dcx1x*E%o2D`7tX+[8lz<?swAPa$nZnbt/*B=AipwSfP*oPCV-:98nUaTUsCC}K,%UVn:pXi58.qrTjhdj>iwu6Yb2V%Z6wN,~i.^@B"?3"]w_Wl#(-.M^X)bwI]_!2vSLI34V;YJBO)"RfS0oBx@Hf*N:.@wafoO>JBS-"Yp)I
Xa##U|QTjrx~
e!W!Fk,$`wKe8*P8_K?r3b<9<78)g]}DD0pGTY8"cxX1.VXL_<s$hD+Q2<rl^@#P*Reh{V{vvOC$vtfN&5pZ3X_>NM.MP2^DCL"Tbgx@yC[nO9=0;b]uhdQZ-"D^3dQN=HVHQwKCRC0qNow_GI+!($wA7.QVrT%sRKpP,Tm<1dW4/k,Gw_pen7*F.<Gkh(kHXwb.9Mu?f$l`sXpS3FcMtb.@YCv_sJS;g=bu}cYf83t?F0D:T2`m4*3f**ko/RJu>xVr0TW6?DKqPGk9+(2TZCeh=$k;d:uWeoJm6lp`E2?afB.eC52",Z;JV(~s+`E.nigJUDA3$9CQJ0rl7+]bv_Qh[Q@y_M235&BPzyI2NE(v-Wzj|>^/=q=B93SB8#o?jgu3T6AmKc#V(<**b8#?OHM8F)vCp@NO.MZ#rvxWzvBlR+yh`sbgc8I]-&-96jIrL.{Z7NoZ4#h&V6V>icJmG3#C?J#0ughuG$jOp.nylO`>:<m>Ge>mEUOb;wmCu)mB*dX@62|dv,AQtU16Qt0gX#U7ek/H`<}I^i`Y[lS.v
0uZ!Gjz<Y,p+;i}ZjS
M)tzv]M/$F.=yHr"D]P[c/P%ne6gR%jn^X7AMw#nlFD+(%>nhZ_d(@Gl[.@&>+BKA@T}>PLwn@7lmPo[j9es=5SK/.O89XE<-,_AZFJ26S+B6%F9j*l*1y/mkuWq)
=25P(_B$Ds>2VvR7[8;1LqQx]ulO3!y5I,kqg"HspSIvk5xWr8O6r<_k[Rm=B1^pS-YLw:i28Ah:(E#E_G$6P4pYYm00i<CoB8#Mnsi
`{D|rCT</DD{bYDi]4Rvl*i@0LL@.L[(o/J}h5dk$w6-J)60kwku<L!7st<6F@OMM1LGUm9PTFQL.a>G_Fvx/nv3gDqM.;k
u).<r|"ET7*koP;i[!;-,
C]:RH%k7Q3U7f8?`i(:Bhls;V/sbUbz$=l5iRX8/h2P,p6m=d{Q{UV(2rw]]GF>yRd@zr"v@j]=5ly$#vec_%j[;@QMng[*
i0;^s:])3Y28VSUh-o2w3$388dP0i[R|@o]"ij8~=H9@2])C4ur}Z^ij"}fPI=GT00!ee!#2e|QgutVhw5nUDO_-p~Au=6
LFcM(>#8`isZ_K:yd@moqs<sWFxdP3y?];a-f&_]?h;SX=uXnn$?zLh9)?I3A=%;$b.2)J*:VOwby$r&We1*0*7?Rdv2*!Sw"Kr^~=*P><>BZBCVB15!joUAc9nW*VF;O@t;H""uI13l6;Y.:-+#zUV[:mlfL%Sy]8~+D>u72.e?uL[bHiF/^)yQ"&oi9wNK4QMsr^,b;&4@.s81LeuY*=ED6pDlba7)U>5oa>hfMoME]^lAlNi4M-jp{xfgYL4Zmk4@s^AtMaG+YtceWv)9#)1BwPc%HX0%)L8D_9xw&%tLO$.%,$>sLFZ<qKj(aCI(o"ha
0Vpi4ch[ASU^g
VbcD:--529k>h?mR*i6-p1gL^XNN6Mbc-%vhdq%6c[,Eq}aaS>jWI5P7x5Ze7TY>B}:I[BhG-q*a?]FwJ[eP>u
exCIFZo>)nbY|&p4Y!cE4w{>Ms!KHP7
J`bx34%Ctv.-73Fh&e/As@Fa$W)4GE6`K+LnHx4>t,2jgPTKpgCS{>;)JA$ITDy5D.w/Qf)RCw3fdX/]|0gV!LB52%KWc)peZ-MY+g[kz+=M&]Q&QTS&kRM610it*&Te~+E`h`%A`oFQudK5I8V9Fv]T.?5r%7d[0x^GVQ
(2$1pPJ.8t/X4[RNn$_P@!;]^`fYR3V=(+D{7y950cv"JYRq@y5.]k?GTknOr
LeXzt-!@0=J
e:Vtkl#gs"xmsFj~.gT]8Kqmq96/6v>pARE[f)?<*xqo2Dh>x+_WrpY}+(q>t@2(/Dq:7K
Mh+j%>kQ*=au09e?PWqZT7Lwlo!3)D.!Xtvi=(@ZuR1)0I]_#;
H,ny4#`0^+8
7G
TS!XX>{,v
FOU"#?1Rc@Uj-J?q"@cH~d}]GrIwk`fx|:.]T
lK[,0T,0!M2wC,Id)bdiHK!s^I>+5"&2IpSgfsXTQL`L8k4b4DVvsH:v3$JQ8C.*eJSsS@%M:Qi.`dXbj>pKajJ
J]D(W:vdz0REPBLd#e0[["n]qZk<mX-n27`,&-`k`r.q9Vm#CJWEF5Hs-0}2Tsh;@,zov><;Rr#Zz/>ml8FkBUh^tX8wZ#,_sW9lm-z@8/wuVq7n>J`+d(CtBLp<`$i1f,?m}Z?v57nBrud&a6,S1dH6r$^6?i#AJO5mKSv3B@3fIIzT
O}Jy3[@v[6Z)M)2K_V?#DXsi9Y5rZYJ6JD4&i[PKtfa;m^kEc|+>G,j0q:uJ;:)bY<-:qn)
k&7N]|h}[V&9sAthP~EW/D.HD{yDXQC!H}03agrSTgl,w=kKvw:(B:c?SgA/<jsH,!.I0sjD4z3j<K^YN!$p&ShhIX$Sl7S<E,8pgD,zrWhzeGgG"c("b6EE>R:7J1lbX~&7J3J*N7PDs)DaOao;7l]frVX"?ct(/Ts(,mG
UN;($zu]NteAD,Q?Jb%A_sLn;eEX2!D"JSV!%Dfh.,;o+:/Q2ZZGA%hK/ZOisHDFJF4
Y0daaXF5iL=PZkYrjp/^K.jJ>[RmxK4+@Jz"%U/HuN>:Gu#+wVUf:lX}%$@h_s[:1#.>6HnUokp_Fjf+%
e9o5(%OG]@hGj0$F@Dd`7!?SWT(GeoBHo:ChmA=R:9bbI}tgU0n1A}7eFIjFYJgKC[J*APSI=$+aK77keMkA]wQ+wXeT6`$1md%#<OEmO
Al*^7sh(6dgUEHcq8IeJDik~Q;$fWD[.,ufBUB=%boP:K>,nZmcm;w-&.e!NpR/yZzMqRF/x&/A*ljY2(P/:OM>~q?:SvHo]sR5sMMuL%.LM8+vZJGyZqMLBDa")N#4h4$5.4uV?;+ZI?n=Z1wSf>W=zHC1IDFHdpyL%ljI149BR7bA|pO3HCzW2P%1&,U-}UsEeLC1K%m/;Q~uL>&ey.:kDI(:=6Y4bq(;WAmO%uG.Yh"j;v<V]n%4%QVL9ImsJH6LBXg^gaM?$p?ij8~7bMz(b$#D>:Si~8-s"o>azHx%u]yK/!r`&yPq%2P=%!ZbU
/A%IU3|?ph|R.l6VxTBj+&p&Ma:<U_,U~LJnfOoYqnMZ[qSFQ*eF>XT8,-^?[OgcX%1Zr;
mnOyM.dW-4j^x5I}Rgsd7dM](oXTS.VR>T<bFyA`qSWs6Xc{K"4xy3s{
Na|?[3y(&W}(p+TbYFh&D8mG7
W-gwHy//p.Bn~Cm):R.`O<}81jA:4<Kca,6cl*[0u$io8HwfSm|/R%))qJ"q<m-&%:~l$fq]Z#6D%>lG:EB[dF]y6Y%ZQiUFbuBT6bET)5sG,M[b)?t5_/L(Zb9.8``tIi$Na<a%0irE?-$:L_7Kfz(#E';case'hu':return'&X/Ea7o.!2L?h(-^v+FGyx2Rt;K%=GB/)kd6}_13hXL*;DYnFuI!X&3gdO9akaG2
OI[FC9(3EibQ?Wt%/5tHd?:qHK$_?G^+?NvGs4w^B4^Mo(^Js3e:(/R/m~Gg%H![nE]n@*W_/=Qpe=gna(4cKpP8T}usXoi?G{kL3?=n
GqZ20+=OebJN.RWM=WCn*EG^Jl/E5hn87GK?&cb,-r*_"n=vI!LX7ydQ-n@!.0k$bSb`WTx9uyuPIkp6:]lcH4:h+D+JIcNbr;Z$@/dt.lbsLZ(F1q*@jhjn?
%n3pg1Jy
B]A-32dQdQSswp]DObWJc."PqZvys+>dS4<K<#rrMu[:Y=(,K7mDoshDN4Z~OxG;/mCF=/y<kJ&{$idOf"<,0Jw@Cw`y]t.g9Py@[,=m9<EeVF]~Mgz&V84"+%G{xIp?FD/!-L3IR}7^*s.BbXXB`]H[;(v4F%/c=D
ZGr!;Lk
k
z60xVc--7""OE_PDY4G#h!(Dke]Psby3?9K,-:5
R?`G5bUR/0k/[b6]x+nr.wg4cCxYz.eR"=V+AF@nmYqS^S#i|,H94d^xGko)(!]=uWU!rM-AW%2eS/
:F&T*[/l;W@wkV@`u~=Tb8F+nK+nOIT#P4a*[d?TBS^Nj33E(<p
7]M50CbMu`7#UspKoc9yR)a1sY1S@F8v/xY%GQe/O&%JJG>&Ka7Vf7g<L.Ak2sml,^PRPyf?@R:]@wB/Pr@OhrTIqd_f=d4{8)N6X41buI-l<IYl&}+Zk#%OI(X+_q4Rsbh!@-JD<x3nc"]nTz:VLa^@ys2$a%S1X+vOH<XT7}AKIpY5>OQa.l5kdLoI7?eO:F5;sI=+-N1{AdqNrr$8
@f#1ow9KGvz)0[>3pijfjfUW1!]Rtv2w=#P[`M1?y@Z5,/ZH@y"0~nY#ggTdJ1Bs3.zv
r1-.oZ6>PT1|mgmS>LX4icb
-iYNCpBNs{S2(=`4@M6bu,45;g*FqaVWhUIYZBZDnN*mZMxFreq^vr[gdYOt6!.k^<iJg%#xj>^b#HrlS`UP3[-.!bEo]kLFeZL0`%8j$?
B5G2t=PM=xK
OnMSR")E$*0>]Y6?2lIr?N/:[n*>Divy&fgN<NIE{M:kQlTAL(fUSZPZP^l&0
5*tC#c`1-i8*}u1>4ZX`i
7k^gtYd,`[Ge*6>:sJuK3Tcc:ot5n"lG!rL3.aMGFI3viNcwx?@;2sYA;Jwr2o61n:Wk7#-Goh
cW3%2T8RdvxsK3"sV6l!O7hEn!NXe~xiOc]"aPK
7u<?1h54bk+4W?
FFPkOEnVZVt$~k"q5r#DQ-_j:KOgH?ou},@"?r3d8Z1$&,am.%_[_]?ZpH+ZG:}m#;$Edes&^f.?n6O*?tVAlCjh!V~WMwd>WY%>RQ8UlTm#c?PgC*)N,+u+a-KV_`FsbDv8Lgh#rVv=4ZjLNdjLbo~"uA5o~<yF/:20aBjb>nCqtgS*b`!`=;16)X31i-Z3TW68jK^PZeJ_;PXv<Nf_txiEM+O;;AqKKme#m@aFbF]NR?cGy$%oyrsrfvsc,6eA80vgN4+Z4F%TTHqMXnf]d]-FW$08AozV6n8pK":.^5fmjD3HQ-xa~GWT83Gsbg|]s$-bDt{f}Sn
,Y=W(bm_*Z;>3OI$^Vgf;FQ*L.Nag1UG9-+G;
PBv)l$2XN8P4P&`D|:I!u97JGX0GZ:{
Q$wxxbt0cm5Zq8AgRF89bRoZ,uQpjQQeib]
|@}D"<P>vII`jtxKzh8#v:Cr_y_@wQ&QAsU,}^KX1%}>Xm@po6iSagPanL@W%.GkPQ&@p2XJf4*oRL9>0otkU%cnL.hj+.%p{vE^*^&o[+7ZJ)*mc=5Tw_?6@9:At;iWna9"jWnX?U%t#Z)A(u
^llhs+psiIEiqwqMUJMg[p@LL+sI
W5-!T&0Xv!4avu&C.0
AehN&sZZVWV&fH5<ri[we$v>Yx-4?3o{7$0vw40DO{=k
{@P-O.%"-?/e)B|Dr)yHIC6:ZR?%e]}1!k7p>lyQcuvEr
Yo*W"XS"$7ndRu6@G*t2m[$lE[iEVGgdmruaTn/Fa*[ETM]Eb:=Sfff(xeYMBicN2u{i!&AyfQ*Ekv,>au]dLa@Q3i%LoQxaX>f-aN?_E-28|@^m2[`;/`wrs1#k:]%g]8YDatolgqZ4>16#dl|u=[yS{$7X-KG37jCQ$dS0:xh9CQfJEeh3}U*K~mUB:nz][!EFi;~g7A.W]v("<0JJLk*TyS*
[qz<6#8%TsGbY/fg{=}K#5Fv~sLkV@ToG#bB+D*uu>,MKeaoJus*xyca#FF8j<Be>?bSbUe!Ylg*
)8v8171g5W02VVNePM%:Ld+f9GerwXQ*.KH3-KsW/KR0c),p/vW]DR$1yN:!d%QQZOV#5$cNyTO>sbu>wYe*ZZW7Yv]@TW]&`D@vmtn,hKLF5a@Hv6&x0U44pLo)p
iGO9:}k3F5[U-SDs9H@L&>1aqn.Ft
mhE&<cV"@GFjjLCQ+%6z%LAi&;k3u*k:0HA+n*=`DV<amv/<f:YN0)+RnFNo<;`6YpPhwX-nk4L7:}n]gG[3Zcu0uXvLs~[%TRYs.vTAG{B?eZ.HdH;}1iiJ_a
9-<IjGAA>QqHKa??8otu+@Cn,BLMv:vk#T%l<5yCTp=`zD,97qW.$5rNSgXC|V7jV"H3wdZ/QJYHg@QlXW%U6^$<7rRb#n*Sq5h8w,[.r3em)dvJU[&3?u8:POp*{SXQe-QqrO>5|gY@+ZDcwa/,;2S!<O:0ed4Y3t~=tXLN7i#f)Hs>@r9_!MBcqIq&-2`XBS)mW#~U[Kpb)Xa.Hke_5i!XA)d-ggT$.Kz$4sgJ~mS-".__2Y$@}>dy%=zF,Wx9MKd-y`dP.P-R=L@:,-/J8YGQwW*WT0Q*0I}R?R-CLLX&-nCTLZU_yg7wyV&3)Gehu7A_+6>&8i2>/_vU%o?3pI;vt#k+`pl[-d^[9_|yIi2-FZYY}(7fUay10*Ps^`W!ve#Ri;Ju_TQuPCF$>`4ZCW"^y+Bv-*Y*I_`U$
e".<Cj(v~ts//eP/IG7IZ3)a,m&W`*3k[E52^SVrYtx5G;RSeYPsF"IDA-4.s@TF#"e.(fs??8#>5@Z1OQR*YMT(A=fEA?.HwfS/fpy^3IJRZJJkmwSUsbvH6,-W]ClRu((Z*k{M)O?VdR@WL(*4ElDX-Tlfao$8{DbFGf{_J.
R#/KP27=-BkfVCQ=1POIl~u[N(s-/RKQA&s>i)n|@hQoo
-u(OKPR:h&B2qGT)&45(L:XZYF3(+a/-H""%e5@H8]$j7v87VLlDxDFqC}^9rN3rD@JC4b/IHWNk,Sj.$owagbS!L-E>G+D#]n6V)vu[31Jh)=AI:3YxQa-`&z>YDqq*,6-4f?`<3Q
6I#j5`hMl)2H/n)1pRc3N-}hq#$I
Y@.(q4F^)4jGckdaP}.&a9&JR/gUI?+j-P>d-gR5Vv[r%<bnQ5IbMQo?DA(-
<E/@O3!QP!$=eM"$0CPgJ_eEKF&k$PCip=7%r42*

BLJxUhF,bZ]u3Z#7oKJ
-Ua[rat$
>=)G9"!aG"$*C>XL+PXa#0_WR<<3MLpq%PXOhZO2K+LAv/J:dPj=:lE`y{&/O#*M!!MOD[R,EbXGUu0JP=7!$2Fk&H5eqK<:(
=j9m^cAY`g^e%=,kVk4_3R=n&trz;1-fE8j;RC<
C&wbJ?UP:9S[A#h]A"`6[hf[+#1-"=W`@<yUOvs]Q2$cvR6G;8$<CC;eRYQ#np4Scqq.)K!XwtE,LZOF@o-O?abiwmK.x+>jIm[%d$5%i?t`QPuU7D:QP(rt7#jpXke<fa;3M~kXDL:=5}6&3Hq2bfTe2hjKHy3"/9v"1oEpScFye
;_SH#&4v^ouf3h4vd[4VCT%NXPkFPw7,>4H{f@z)Svc&F]t?fJCy)@PTeY;s@76xW8Ew[N"KMRC2R=3}*Tdd5m-s#95wq;f<S?y
[;B6c((d=tu3mQLB6Z)YhPXO@r7CteDF>;DJLPL:&xda7g&/.02y/0U_9uT)N-`9)ADsdL4lh4wMRph,3xp.MTffw[DQTch[wW!sSC"=]][@I=/kej^b^GQp:ui"gB.gRC#_M>`E-eefiGe~+_7Ps;"+F)7Vv^>8%FV/:bc+>xT%bs3?>g<A,qFBG:IR`o2@o<H(bz7-3-5dWFxER7=3Is*A*<Gzh&r;q0o#P?!g%3FEk~tU^nQ&l,emg@RKUftni?<$e<htcPY5r5]D]d[LW5?vZWnjH!7`7/H!/,f%dKnQT`%B2y"fC1_^,/1bpZ-7@EVkb{j0g1D$ODOu%%T}7Z@<7}$`Hr7.)`.n40xG:^3P>,iIGTfNWKTEUwST?#D&jNq#pF(/A9E8"SKXPRZDoWSU;BADmp;9pG0,Iynk
[4fJHP0Gcx0;IOwM-7>+?Bo9T3,k5h(N9bl1Ch]9VZAjEW*$#(qH($o74)@Y@jhr~HO=A`B[<L8N9Gv9mmmH]^x4(+R$kD#lO(&`ns1$UDz/=AgQD</Xl#_IfQ$A
qE@_3*_J,1V&2,>m]3-VuG!-eV+0XRv=)>D,h339k4AY.&4:,N1(8@#1qM9eO|sZOPs95Nn(l#v1mt"H7GX93G-+,?xML);DAM(=?<
D15q0162_z)%{';case'id':return')X/F|7nn!,|?iY,o=8P0VlWIq?*4%+)TEjEWJY#]>+HDwCV0m^{cpL}tKI}URZtXdbbQ<g3$PK$uT_0i&_q`kMR$~*v!F,1.B,ZjCb<Q:7p.J]gOY(g@9^5k3b2jkmMn#RsrE:H?dr7%~N(#x
;B4icsc+M0f+co]Y0Hi5X
XX$+/IST8/b3jw*
cc%A@AQE-,}
jk5L?BKI1XI@0S
x53*u&nss#`j<USKq{M0`FbsEn&>h0T/Go+n
R@ke,nWwQq1q8obQZuNVe?!!{Dp>H[0By!hU-%kqN5sx)w#
x/pwp
oT1fo#[dA`?w[rvw%SiolL&]k2`UvLKpMNa^tUO(tB
y$gCahqgHaB
`+/x"?!NAbp4G2@nOns(@,NB7;1e^TjP"7T~h.XKa`d9bb4f5H!fH{ITkK]>D&FTw2[SjD>=EF_4l|V?&L*)EgI20hs1Q*H}RZ-Qp!+fmd;
07xK#[?7$^(,i5z)OWee__V5VmL^%?CEQ`tJm)a?E{w
71^EU9/]*L6T`E"C5P6>[uN__4Ditarf]Us-@uI
J3&}aBU(aJB|S|*|4<I}p*I?kpykTR8Y7sSrRAb1m:n^ypDOav[rN?a`M."(C|J@qo`~)2P4)NVn%p2B#lwS4,Og6Dg,G1]8OT-RQrA|Qai#phCXac-=:u_Fy}2WcFZmF/sNDq+#aj8]%#Q;B57.XO`kiJ)es0N,<A(W8LChKsQQGJ+i;;7y!1C%2qMnD(N$iY=Je=[23
0V]X@$^r-Cwd]`Qu<"
}
%D?uzoFb>+Baajs%JqepqitOD%EWt?@AdZLO-vcyaW&@Ql<TojWU[N`$D7:bS;$87an@
60fJ7>I1_)jT$5n~[befs:Ta=]kW;vd/`hdaogf)A+dB*AK}"ZB,:[sx?oml){2O1^@H*bhApQ%eQ~uy9lRUE7VwG&S
4?KE4=#wM1P>T]^_Fd>p
AJf5(^@Y59Fw!TN?-2BVOo,>Wl[EWCe%$M]=+mdpvtfZ?IHE^2uY_
q/>&kK`;}#
f{.+N&yiw-?IM]nvj?>NSNf.*l*[Ox>G59Qo4f%J;ierPW.&YRgJufSt"%<F5jAt*_P0+8NPkqk??)VHR2CoWsZ+6;O]RN=P?*5QwWH,^Gcc/
XoT0Lu1J6t]iN5?S^tP9GMBASWjnnyk<8(B(
7?mh}F0OOrwDL%?xW?2r:Rdf9Andvo]/@RF9z$N)Z)MGGZ#:26O_2pjLQ%mp}ee74J{4GQV]e&&T5#QsyV0#FAK`(%Gk2a_VIkuftp7)2RYGN$F7-+=$Fnb_n+,u(F$f]y#bdF!7z(8in"|W?CRHnbDJTe~hHo=qY-&"45Nb=lIBwaTc5
e`Ye
ASJd6eo)*@:$`igW;
;Tl092OJ@$Bd*w(}s&(KYAjKeS5SNjmA8m.B
+A~o4qmQ%sRQbemPaLI<(9hbm@c#%KM3c!_"}$V2#`-r-ee;:Np`X^B=3uI=bBP9%$,*<=#0!d?fP#L&v^3*[0m,@PO&"M[CmL4CPQ%1U,oH=p,PL3}*c4VGq!]CAjoOj8%b6_W6t!KF&q>^k%z;1wn3ax,*nqvD5Y8b81G0e5(WTv)[XC>,fG*:rpE>/>iUfG-vzO{J9
`]~=>nyMwKYi6#Ur%OG$`]!
@^N_Ctb3Ln,kH%-O&&hLQBlwJ<<"MLfndHpp!]+=O$q-HK%d"EH-O)[b</N"p?iNKu63.Fl(&t0vnQjAL@J5_5zgHXv8IRh(4?P-u:12F4%+_Dj*nurpk
;*{9.>,VYp:8ErO"Z`Qxr?y#F/vr}:g$w)"^4%1*SjhAD&
Bcodd5=i?4`j64cnFPx@1:F[G9iGkD&V":Mz#N?u#`Fac:CAIR
z70snPp:{!.!W3j?s"fY+TI*zG!l-h6e2iW+`OE9#A18=WjU}i$[5Hi9C][/s#ZUuQg&&yO>E_SosUp`":T64AN
Fbo/~-WtE5^Q1CQs&#3t2oBdgPmuNAa$Fp"8hX?
0d7Rfp]]lY./n39Yfp~g%)X+[yq!(hSJT*68klj<N>%lJEVme&FvqgNG*$fl)O_P/6~i@=sZs;V5|`ku.ddic?DT)-{_!?TsbR{vgEp!cbK(nCjEJ#^
-s_8rGcA-l(cvo/Z5x-1"R;V?vSbWc1M[IuI{pg9-B4ehwn[0ck)
$otP
2:oWXy@plv::Ge~1[fx
?"PT;p4DQB@X
(Rkdrr.=N^rU!SQ)O"FP8g8?iuO)NETM6oaaEy^>Q~nhb;t]ZzCcD8wAWY,D03?2AkMA3Qa2)@<
p()b1E4_hq+F)7TWU4%)yK??6mZlQ*$?qF0to9cCgQJzc8y)Q4i4S`t1;X7+^qX)hh;FH?M(`MTR9unpG33bCD;nKNg^9<PnN?K5&X>F3B*?fiO{pdbvBBgNP&DGF8ZRo`s#L$#8qwhS0Ef?(/!MLyZ^JryOlu0/I<cX,L9#ypi^Ajw-k$H_ufqBnG?SQ#@TQ}&59jd?kJ`P4.YmT
0*D/dr>)i5!@vtQWEd6pOo9>@w#]IYFh<+"SZE!OQsT`%0X(62Qb(nJ(*#9XcDjsCt2Un)&|<0,|Ut6JB
cY9>2[bvBY6>F.l_$-/[O>U/F?1M8^yRfN$z8I8CiX+z7}`]CuPTRPWL7s^nrkd&ms3
kn9)=6wg-+GNpj^>NLL{PUDOy09{BFqW"@p8k>BA:ox.;6j|>GKt&SbTKN&,KY2ME?B_X~)
F^@qd9#|Xk$zvOvDi!Ui)9]J>])&5F84j99aL0=UO=0q^F^i
$=j58c/j!l"K-+(TvCHP[trJ;3$oS7_9#h%2Nk,$&fHAApQ0#j#J8G>Q/["xKEAQlCzI,
<RdwL(bK54Uu%qid7*e:m%1M>06/l]EKg5FX+SmEjDW
r^1w^sS@|u"<"+;@j"tXQ,oO0.5c0oCX4+biFR`)&ow_M/p(q7*0f^juRiyKoiBe0=*v$utD4MopMGW;JpJ.X*~<}+OQu<h8`Hp=2tVj2v=0a-t.?v_iLkF8d]0#>Ub"Je1l-17U_P>m
E]kBQF7TgV5QpAmN2t.,-eO}xDVYq(9u(Kb1kLxlx/MN*8D{b+UX<vRtmA/Z?rFeYOek.Jc}AwQ$=m8p9,R8x6e0&.:+([EVwjq@>.
Gq]6yYp[zVC9Bj_]?b
*M@,QMsij!PRQIt>GuE]PG-vR.a"KaYxb`.rlFx+/oa=%cZmdJ[H6&Yk!w%ai8I:$=qsAuk<UvEQKiOwM/i!uS#X62F/Xc(>_;&F.L5#M#F*JltiN[&=AeJUv`#:#dG.
Ifb%M(_6.]Fx0b3YCxDSa7L.>c*N5P4LWQKBWxWv%jp@sOWf"O1C$7RM_Hhwr*HVH"jJaTJAz;x!C,AARn/yV1_6l=MpNnIfRwj9R6^$$$J3hDUwW$0vE-ul&kP86JR
3V}alqag/aCTAx<S@Z$faRrp!wJMHn8HC>f]L.E@-%%@]"lq1;psF9s!7)ZcNE+Kl[/r1=8egWv
_@EC$sGk./W$=(i.yvq(!W{Zp@%#N7swaZUfxVDrocD-r,lNh51Q}o[Yr<aKu^_73.{xG8inL(Td^"zV2l1Uc^U)?3u+Pdag(9)%fEPPB?bbEik-M=
A^,J8O,Kh`UHEc_4R=LSMd6I
A
?JI=~":v;m0B=4U+!+Fa@o#Dtfh0U5vt(EUIyhhd"lT-MJZJ5E>;s=E;/vBeb+6,DS-Dd8m^&N=w709FPSW=
M#LK?:js;KO{v98L<[7#km<Qd&*=5_];@,
~itMe39rHpJtcTsD
rw7f16bkuEQzo%3&J9Z13|3Ne~<:5NCdo~l9G-%y5|o*q<gW
N+e8tHq0&W_ltG2<UDe6A8joubRMs2gZMs)vU$Sip*rW*Q*8q%:rxoeTLWSWmDu
T00b3)z
?+n:wuwK1os6H9PKtr/^O.i]7o`EN@C"vY5v?]hOZD)"5QQ=ufdE^Vc&CQ?FQb0MQeRr%-rS~!}u$8^HTP[jn<[$GLb/q;0h^/Dn;I<]>R^s6U)0to(HU1hc2IVXaxd';case'it':return'!]^Kk6LDI,y0mR%lq)b]Cmrc:SfpW$k$
d9kmBcO9q}3zJ|Ggg=eJ+UB|fMs>vdc~2~r%A$huTSwO+!d}<ZhIUMY};VyCW8np$R!it4`!$G$"@s&TWH-j8j$/X3avkw%xBPtB9?5(*lTqvx,Ta^>W.z%q.zA_m_p
qI8(6n6Md2jAtCF5e+89Ma/+AI8iJ>/+8#T5Q|D[tTl,FS#ksmHgUkfvM"Smtd9l6iQE`fXvcxqn=5pPgTOjek5EW+ZI0Ml
o3-;W@AH&co*+8vbuRxGKvAg!kMmM[hL;2)b!V>80N=?:53nuu[ERqo3WLE:OloI$uZQ3%]SDnwNja6od$e}H(e#fri*#F!n*+QDj#yH5K(tj>6]q#ZDD,]"Afhh+D.(d6poFFo
162"DWX.XAcL?/>,l]bD!3rj^p$$XtDX*phH7]7g1<d)i3[i_%sz`yCSUuC)qWS(MMt`Z`9!P]ybf=blJ"UW:|s3$;FQGf^j
>o
*>Zeohh4H;+$q+6E]GvZ%?-u@lg7`b%k:dLvA"sQ&_t@;HTP3N6XBfBaUs4r2Eg})P*6pr+,]?nl?Pme)tQuF^:04{bCRs1{+ki_hsGEh6CP6^@o&/Mk%mAFV<tD$8Bvn7PrO8I2U7tk%j/-<tm;gnMOEb=`793R?Q<G#/;fZ+7(s"m8f}D:FR/#Q9s$5`Dv0!Wls3nXG?s$1B6j-;t>i$c::yYKx6MQNJXq0__%yIn;YAJt&$I.tF[k(Rc3yF5("OM]D)a<o`scJ
yB2uY6Ifz(k&
J4[XcR|ngw(TG6$Wa;tx>LV*a2|piFQ!P)@`DiwVJ02*vntZV_(3-WeiR7kIs
(BryN6%]S*-bXL0i%he9=7;0*t]#ii:`OnG+D[1NQ54m2w<",5Kn)#0256*c9aA3Hub&p$Q`pp4X2>dr:abKg<k8dbNVv^8?`ELT&FuIvbD%0>-gDc5GloK1w,A2MgXruLkAsHEF:Zby6K"2E,c<vg&v}1x]I!T6gcj19Tpg[w}wt#V8l,|40yRqu.x`}%G;;6sMe?*MU5;iLR.
Lv,;b.Dq,y1k+keHGpAt{/~*i]N?E-IZ!R4EUJI(j(Z36AH.5sZ5AN1cB8yU)x@U#S?b>Pg*5ZkfNY$-Re5$Y1i*LxbDze+C0O*a%t9@%.
;kO;q7&ZEB[El&+Y
FQ{Bo$w<j4MXXMCnVj{9rntm+v)xb4R<s_vm{]"cHgDq}g+M}A;$K4[3p:?XwMDF,V)JSKyBb.}0@g>b]&vc`Q,_*Pl(btUu:bzSA5r6a28P:Ptl1dQgD9z=_WW+D<xhY"@,0f*s:/r#KQwEK.EX$c}VHcwh<Nbf]P_j`43.6baT4L_w/?`EV9tLeUiY7Ea()+~M[Dl.DG3U0CQfi-Fo"tw&ilPDQ&"au2RU:wbUhIj-=k0VVYV2oBl&T)fGU?>RL3,_{oE6QKk*y<"%S!=Ck`HnKi]SioD*-dX#L=FFI2:4b&Np3JMy"F(ssc}P4I0umF7h:F*h^NvW6LOgk_;u|&y>iNy<5i*[
-A2t!i5!,"8<$S%!(ORlC:v@=GX#7}O@&LqTuF2$h=l<v4Bnnr.bJ09}ICy<x}"^E*;{>z-Vv.LA_4,dWQQxI*CIi;:(;_)G#nYx?hlk4kv^.;WJwR.0AnSYOR0GZ%:"Y7?gRfn-fNO
.6iby7j$5,$JErPz4h?3f,#UDgb_v-InB1m..f:;11JrSj]z(7=j*uX$
AN-KDktdHd@w=Mx
u,8-4a!$,A*)C,9r&(^$+N:?,f:x:eybdB3cvR:JUW4YL`_w7:Iv{XDUK&d6:tWP%RD(n@s5Ce`N8".#~L75ceK@E`qi^a[^TqkPCH@AF!v/8(:pCty3!Rss5d4HSd0P_(w"
CPZ[:tX<[i;n6"<w+Q%IpLBZi^<(Cp+->6wu<nd%q;_S];-*l7<fiM@+<4[$LX:L6nikeYqtwhC
X
n.COL3[Uo=0j]j)27[hAfiuC^GRuEfML@nUDhBNDKUGk4AbYur7cHbY7MuS,>VKT[tq271MwVe&Icu^uHURNvrw4Zxs7W,JF-4KU(2N[]eoFv..ERctV=#t!lZR)942SS*iAyFhp_}3if1L,KTa<dsH3ejWiB-+,q[Q&@70"3Gwn]24asWc#(cL3xGacb"gTr[x+oSwivk"eARnNq#U=5{v2]Dwv-`p&2s`0WZ3#eXicfqX0"I5m$j-tj1Gp3PMxZTV5RRCOX-9Q!R6@t"+;smS4qbFW#|h}S785Y)n7wKhb+aK8"Ih*=V6)pC8j#5k:Cs^VYP_B
G+D;V$DJIKQZCWI<lvA&gJmg
ZMT`gA+1-ID0+a#8>BOXFk-#AAwa,0=1!k6MY?<Z>:6guw[5bUw1X8^cPHMPU<&+"t+8Yd-~8a3Xs"5WX#B0,Z>7d5vM<E
i@.35c{gLZN+P;_YIxi#N=]M<v1iP:;7BgB6j_K-*"q]KP;T|e2<9)
7$()H?.WU#[#p5vje7B`J/dvMSC
TNQ#xlX];Y%{l[5zT~u6fzkF;mxS1J_9Ls_}QbM-8:%vNa/W,lm7p{]5VYX{GsRWAThs;faeqp-<IX]HRVj)",gu5|Qt!{#-d:%(8fFos_X;kMqeoBF:sR/B9+w%a9)2#K()uCg#F_.^G9UQ2b9hvA]w7P;roN9_i0O^uy8Gu4-
d7%~h#"Q,z.-$1kK[bul;pjl8ALwdF8Y>CxV[zBYyk1:;|V|rIxB7s!axit~Ehe5B/.BkT#2
6]J7NP*c]66S2FNV<X,Rql8rVJ*MQ"o%}<=1$@_.6
qn:;UGo]Suob~4XPFWp^ut%)$RCVVWk,d5OP2Z==)%
c:5
l%=?ceQM.XG]mM2UAFUo&}RM;EXgRW.l@gkjKhQ!axuR1391b4>>?a>rW|Bm44JK=;r2j3e}*{(_gzYrZeYDn5SFMNK~+
JWa$XjVAaf-=fQr*gw;Am~j862Mo]=8vETU9gm/.P^WJljd<osTjG$bFN1.A43hxo]N0adIKPC.JH6alW3"TdP"gxKp9%)o6IgDfA+RdyF;,-@eR`J>olUp9F,:%8qWXWmV.pwS?
5F-6s_44mx]*$:Z:}i<,0CKBt1jC,H#ON3S+,%~ig/=O_MHPb/K!okYu?t"/CXs"t/Km?x|!dX)cS$B0;COHSOj1_U+.|8J4.";+e6WO3QV15hK^"8@nU:.W`HT$~2:l>&

+[NM0UsqD/gr}++@_uGPHS@4|&sP3.JM@udS+tlG=qvhF.w-EYn)4t[%t7z[!hg`3RvpG>8Co<Ia.2Eu~2]T]lz!
o_7.EgR&+>koS8TWZrh:v"h(d9UNu,`~g!qc5V!L+f7~,VI+/X`YqxK[,W$D1nQwVehEZo>(*D_-=2E55CT2wx`-s:QG5yQOes@_oB#<>:?^W@W&/oR6:5</rR*:*cRj?*Zx`Ip_;+WFM.&kT)9dvMVk?~2/
t_WswoNhOeniZ*E?i,(o[2v%xSwwo-{nyjcef;<1,<j5jc._@[2dY3R>{V
%Bq8C|^>ITb_?xI|efM9Qzkpt=sgGrFVe
^;t}E)?]h+]j
dUZvGBWS!N4E9%zPf7A5Z["Pc0OP"Q}qku.3c7@X7WIYZuwef*M&>nPFd)xDJJ"P67=31D0F#nEE5jO4%CA2`_*cm&T8_X}lJh)D4>2Vj5]//0C>x>[K$ZpuKdTR-vtyuq`9e^#0Fw_eKBM8c&^9ZJE8FfcM/^iMal"xq^;<4t
vrrSMaEhf;I".%Oovk5khnC7u<I@
Aor<}t=%,oc`%-)yzH;avfi,PD-NpDp;*vP+W^E.]E*m8h|Un-_v)cNP(yRMcv2uj@$<e5k:1gaLJ(!VJ8J&$epjJy#5R#aReZ(-Y1@nR9`u"2UYtRNtk6mo^852+A^Cv,~tRhwmiX
=X%Re%#1].2W[>"<5yuO<SBe*YjLSJLZHW_#+>@[bjdgvE;N1@!2lPnUhD;m$;>3[a>hgecumtx}I<W^[!2>#](b-A%r`Qp"$M^-GN3XaDn<$SO<t:BA99a2>)6bn]w-5F%IkC[vOsGD:a4#bVuF?n"&7],_
[FW"x?xT<3ad5c@#ul],y#;ow7CgUZ#[T.Ys+l!%?$f)^fzQRq2n:mchO
8[GFxPya07[XL.{L{MVO]$vh018%"6n5WUMi`>]:K?wy5ba#%';case'ja':return'&X/Q|7rZKHP@&pV42T6eQlyt4I2W;
5rz?pMo7|!Mhzk&L@"?-%,h%%$8irW*CGI_+0?3qap=RiTLz&0IqVGGtN;X3pqOU-]ZgerLK*b
];yvQZtK$XdZw~WJq%+sPIw8G7SHPIhp)wn#5bjhmQqfM0x1ifkZ*sp&e_:PR{f,S9xZ/IB`WjJW4vct5Nct6{sVo$VP7hnJ!7>um!ul=AKBn-dc5IdV6Kl[cTLrt1]:byt?_S!_2NSI(nUJyRe"KPP@ABi.co+Zl,F<f!E=$Ydw+B*}6anS*I)b#@y>eNPBf+[G1rSmB9ZR*nw"8aN$e7bX-hs(?W[fewpjr7H~pZo:x;I}xEJ-2;^JvmcCw8v}_5+JrWb0cA7lt@
,JyM?t@B77~ryC4#,5IQCHYpE&CXs4AXgDEkqB=4lm_-}M4Aj(2*QWoBXqu.xX|#d0SmpniC4iF^4K@q)#Bp;8C&udwjbhdMkeAter4RUOjxWQ=f=G&Z:94bzxb7/Jt8]T3B}iOJ;:cRM>2OTG!qx0)rHGVM$p4s|*.3,Z_u25bV=EdheMkuR;(EI[)[JMClvc1geQ9a&NKl%eAXU7O
&8")@HqK*^5G-HBF|Dg35SkQBWRfp([W3_1r>a;(_D8Kx#wj*IW&W3Ayf$N;;xO!~wE"vHZi8h@kh/Am(akL_x^s2.M34Ikav6jm^MUEKok5Ldvpy+5=80?oaK*FPWy>1CEgmq4q7]@LWA%Nww/&&S;aRVgq,w)<]
bs-6"Z|=kZ(s"*ty#9t03]ZC!x_(MPivY.ej/FrmKs|O{ch=1/-7`G)y/`FKrE-UPO7Xmj3l62T,9J3%`Rkqe4i2d*>#59b]<.h=NcI@IZ;Uh]Bd|u)IHH3q6o(tcUG^n?#hft>2.6dAI;tM0ig!4t:EFZj]66f<eNX`^pH]nY.V+1m*s]dDO:ubB#4PsnN.8L^uf)(>MD:51d)>T,zEFg*]QbFi9e7Cl3rtM[gvO/:bJQvMP6qpBlK_@s(r5uW-~f}C"qfKl5Kb>#[_NGKwA?`-(1{B.w^&zn|Naww!A2[m]<lfFug:
yvI(#&v|nvvfJQY/x
w-IQkI61*"v]oYJE$u1dLHI$t^[gTl*9#xJ]l6:xy1o[!ThDK~jUAnvyAU^HFVUCfYfGB]AQ0*?U7bh0,~h1dM!@#:LYeNfglTvl9]%wJ7#?4=J9BveNm6ovE-HKD_V%0"r,-*5zL:-O4>3Ib
KD4e;!VrDlInpBm;3Wnu]!LauSpTJY?,p%9ckp/.)5FBWF##
^8qk+6EmDv2@h`}w/a
92rr!lg}$tQ@nW/hfR0)E_?GHIMz1VqUV.X1fWM:1;t/K{qvX@b&`q^;f[KoIu*5YBIrMq/ZkYx<x6:OYuG-U3EF$c?1hEDoOCfp:sJ1xXY1UwwHY.Q"
O.8Na33Ag6mksr5w~j9@l,6-r5(Z,<ZfXD[boViNrw<gY[+Z*l}Fmq5kD8mtVD4l.<TO~
z3)w)#
u0k>:-@&^.Zs2;r!E=X6INm+
@W
+y`%@sw2L"5:2NBZ<ooVoCcVe45{pYW?v0FFM5D88?FoRYpP.?0a-6Xl0~;51pGO$hI/moTEfeFJEf4ahDr179Fu))W&-T201*fI=V[i&1bk]!$"-ClPk#5aU1pHWqD8Ru3Ms7=[>;34.K!K(hGx09z!p}w@=:rtZ5pSr4[%yi@cCHMcbr=rpFwml9T$&SjS9{OSv0,L_K&**{^4RU&>P+JlV5G!3NIA2x;.u<G4aciOS?CxW>%z4+c$qm_
h$?mbw,IH
SL@0UQA)w^3".ZA->!9qui>0!UIS;]l?I1
IeN/S>r:Zd|h9T}eQ;nCw>~A,/MhCT>3L0#ddp9U[pV,IkwmI]yxtKk/?IB>*8>IxBY1F/bi4"N_[RiZw2i6B*+Yn.m2IM+)MF8[V/2?<x~^G?I`8cHT}(p_")M1&KoW2/[C7-#()W!QvhDAe3`k/7B5OQrm&T!gFDz?].kDRy4C_J#[OX1tP(B<xvpT2
.b#R_^*t%>m)hyOZm_t12]Tsz5XOF""l0Qc2
t:KC$:Bp7YeisKAnN:+l<kH-U%:HW(3}l
UcE)3Ap211WceJa$5WW3oi1w7ds8Z]^kKI-$>!q%)27Mi47Ny8]d,q)Yux@MhF;+u`8!O+m*Q9WlKeID(,HtR~e;"92
cmt=mgiai4Vv#[.n_w%UZ[Y}2J$]LG:2w]Vc,;I+JKMX_),>j)T5BFqsv(L,v#s<>*Fr@F5FX}(@V*/tE/o6b>uMB3#V_M4MRz`_`ISA,3^o^:WNe9do`cYr.DGpwOA=KB&{qfqkuX+mYb<"LXO#Tsu+@W
?5w>%.LLm4ut?]}3r$W._Uf>}LnP9j6*7,?,5X4*ga4PrLCtVA|af[AWFQJ*??G-b=S=-a.Udr+0<oGq.ouif@D^/jw4cE/AIr(GsfnSwdaVQuiOM;;aF5c`K@FfIdLfUK3"df^`k9MQ|`=N]G$^.?#oqE-i0MtHvJ0$f3FJ5EG3qw3?zNz1%GlxbFJum=#_H9~co"M0O)Fgu+NnT$|1QX6Uxj2WQ!}
cUchCI3Mno)7>N|l!9y,9w#7bn
1B
=/;o7r~>[KfJX$m6@ajRkPH=}CwbxkIuZY[XF#+eW.a&B`0(XR45=@04Khaj+F.ij4K+mX}_h-nMr<O7ipKH<er7@?aAU,K:m_=`RSmGO)FOH.a,2-Cu07lUq0L<z8n&VezYSmu=|q>p(kaSaXf
F_Y)**6j/TzQ#_#lz:%+#B_]096fjb;%hz&TN)AkoFJ%06#B+%$f5Vl`e<AwkX.+Z>
!*u
>ybG*p,;_Tiw#I$`1H07dc*E[+PehewDElfe.-Z:wt37[@4:i.TIsdNz.jAmUlflOR8OLHYKvHO$X5N}"rUN"VcOb.+x&:v.ktNHBB,;yF,{S,8F#c[AEB$->J*q%ps.5e[#`"K==7L$j=F7(wZI:ofoDM
mouN])+eEQBdgQUr!plvFQI_kdR#}-O8A.CxaKUIu<}:2A_A<Kj6&Ha5@o`g^g9v1H-W4"1o5`imJ%%0be5R!jk%2?|
/&r_T%=0>
r4ViD"%#H/Oozp4kkY74gGYC5go%XBfhkOShnAVkz#1i}LU
@D[X!"ujC0]!4B$[Aas0#_~[`jxKAXG?bQ45zF2M3;t=}&7rn
HQq3M4FE<XP?78/r6wBIb>hWI$^%OZMm,Q(9")5HjZ,
7ixwg[:`}EDZrnA*}-$.J
~PAHo;TACS{lKZ",3d[(B4|]SpNXl3=<1bU`tnfu1=$w^k_`["]2`huwPwShTS=^A@C0"<Q6~iGr=b*Ze9"vlnJ"y(usoZh.wh7muaS19#5bNZJn+e+^+-khg^U]")2Ef/|?=QJ*^9l`xT%wh,.n<B33)p/at]TC`L4[7alb@0/I5y4=AlnVT+9eqT$Y1bO8<Di_vt:B}g4"+!kVQ/eJ(HyY?HbH?m#-)c!bs"uizX_S3M2h9B9`B:/t3<;d`?d_N1"k#QAc,)KFIL4c[?ZcRhVvxX;?<p^@=:zWReA&v9TY3<3[;ozP@S8ewBX6mZqpMf+yUK]D?Xe5Y!^U~Y4e;pz;SNAQ$Mkr2]Or~-c.bK,_r.<01#qwj0:CUS$"Q<(SGZ~;Fwab1?@/9CIR}=i(.]|)Ccnnn>0
rld0]Ab.Inl(JD4C4*+Uup|jrr~Om+,^DTG^!?kt"*%gaF0*-Te3A5il!/RTxa^CyRdTZ
";;TK7l)k8/`~94TO(W&kF*YWm(%w0GwC;6!vo<*2=K)>[>LWPpHV
P]Q]SQypX%rO3j.l%:I/#]Er?+JUxYbrj[pHf+M>6Dy"O)?[CvPE;T}Np2mv:D!B^n*FK1|<8j~`5PC..6RW[p|BKy"9I5]vLZnh[CDh]3)WyD<O|kzA:*/x,uX;%ixiKZK#2u`9}fYA%vaZ/3T9mII
sm8ORE?O~=:@LYo;S41
;Tx@EOSiH?ze^`$cq(B+!(p/DF9#"&8ej#u*RIa$D:vBw;I<9Dc9lG=EQZZ)fsDH.OopWY
]<7fYBJ[g=@s.C%aWwGQaDFLMf=uDd.cc@JnH>.S?s$#Fg>,A<MbV/K}czk{Y%Qh/,fqdj@b;%HLhmLIbs*a(V/P1t/{d~!{kMeh#WZ_^HJ,z!s7>oA9f7W$%?LM7BA/p,)vSA*%yA[;C8kicVX
q}os<mFn62
;T>k~o+q<I^3C^qs:iI+HOefa7+%!ygi5[%Xai##k2u^CH*XhKX[rwN!^,*=lEd_2#3/-fK`~QT:fFOp5SUp:(1A=9HOOWZ
^`CM5h6G4]6RZl<w"t8<DqPp:]0aj!)+eDGx%<{]Yp@i$5bc>+,]!30m`)<5`)*]&(fyahE@|j?+@m_,%4:JaPL.k6HE_&cw_v%9a.lGbmY=#BXrWXsee?ca0joxy0A;_p!3-#0TFFTnjX%!0k2>l7O-9nME~b!F)N3$]Gzh[vB^#65Uq9l@n7^rFg9Q&nz=Bo;Tx]x:RyBp2q%$W2#H)
#J$l:=Fs2wHT|PTS6Y_uTX6Hrp!4J5=l3obeuFJ#H-[$kgL8zChgOk4j6$MJ]X]T424kQ#WmLO6/6nS?Orr#Awp*2E"^(O,T%W:[P8sS$.9Ds=nh:Nj73ZHdqx.n$SaByUP/tz%cu';case'ka':return'$`GVsg#AOl=0WUZhjI=a,tU3gxa;i_:&W+gK$pRcIi":of0H#Ez:>F1krW<G%VuG,H#dvvBjZ!U%*t50D*%LLSy$h9Jd(h{z"/Hy"v(*ef!6uR%L?21IV2Lxuy^2DrncP6]@&)VxS&<tsSG#Tu7]|p(s~o(3&0RHW8]K$8"mu@C]?xr6uguI))A/h$bLD9i#7#xv]PKfbR#&mW@/N<NjNx^wML^e/S-fV$-2LP{.Ee3+4toNbkp6RcgDl9,ZT!)tf[dlWy}^Wk,4R)jNe^Y(.dJ4eRWpwhT*O2`,JoF_e&B,5W{/*D^eN6DJl>
R9!9;w:XImia#PWQN4HJAuHLqoiN6e)NegC*y~HM0
%HOG/Jg"5c=W(o!Rha[D[V;8=ON*Hk0yInAu^
b/g6NC4-Vz4.vqd},5ZXKe=r,<A{_IM>7i20R
;At%Z6T%Bj#2:P7Y^s&5riTFrL<Ac3v1Ce!F.[ZC[ck*$9]s:uLAYqMjomQ&MW.MEj?YQ+i.V/Y3r)CcjUB
Y?T/8DVf4yv1oH2S&?plr}lP5Qnof?f>F1SQ9_Ry
&:SA}L;]Tg(U@RS]QIf/Wa5R`WS:8)<O"BDXEn&@COLMr;F?l%mbCe%][(<K=`514y3Ve[5pttsGM5DY$2,`8/[xZ>&]GpR1H19V04LorC@t#L@>%m9T!I0<MUH]RoG>37N@LkD2$7.z&oI:}R_h41GI[-pn*-3SN3*P%P[a;93=D0@KI#S
%+;ODX:/^NS21$6Q()
PH,b=I@M<V?&%XNA::iB;/P.i:_*AOie739B<cvTncd6Hk3D.E/RL|w7Z*Kii4-n!2uHqM[OmxJir5DsWra)I`(MI^U3AUv{)i[Bp~(U@E%M%~MHtQ%
svnjem$@hFS,9:!qWFy`1;M-thZ*q7GWq@lA+FmZCy0hX&3r)FIe*sv0"a<ZGIIzS0Slz)UB&,>pLTY/_v[0T`
!*0NFIlZTH^J?Ae)o/L%kJ/Gyl{K>Qmt_kcS$Cz1UhY47=5UZ(ou6iJmO.Ath/yxhM3B[A9!GP+nAJrlXs~).D+oVk^b0N$ry.d<]maTeI92Oegj=`&ZIT~)HB`&)CPs_ku=tEA?}[$c-
8S
FVLLw/Pv_#+,_}S*(?A5fGm)^!k.ZImd?#FA-RECF?aM!&qYeP27SWl%)dXQ/?`NW4Z/[gPSgl:BHeWg
glX2"*":Rnh%8*BJC2{Nm#DWU^Q4.Y|in;I`0-&9i,i2848b"?WV%xh#|_Ogr]gAMr][NxLK(hUS8yg
RYBQZ$QcBMqOb`~0uU-V,IBFA,6r
2cS6u4G=J?d1!.Bos[.Wy=S3RdQhYtlRFSk{AbH~0J5O5S`|`Kc.e~m#f.ed[.,kZYyun`L89EkaD1=/-8@%b?uHyFJm?qNc_&b{^a73xk4"4JZ}ce880"PQ4NW!a9V>A&o-w<93O_C{ne/,<P:G)OMoiVO%jPeZ$Tr!)}aU]
w@.+N)%R0xW~uaiF<Ho/wWhi/#-oFR)soOXrve/rOj364*9shzN/,]TP99k]][xKoUHE`pi.JDH[Z[!)X5)MZx)mqt&JS#H(y4euc*2/]kR3_ca&eiu5Uvn]R-b]p3.cICn
b(U`G{>MX]gvnQk:y"NzBT)g+*3^M6ZF2;s.;?xFA]m{iLA"ASPO4_T9i}G-x!bi4xM3X9"T^u`-Nv2X1=<(-+1|_8^y@R&Qb_-Rf7#7NG14+*p^<]es>2c8642*5q(Wn87{E}"JXI<P;#/V7u8ol.]h9"#w
H[{=0%Crunu*fnp5w]|$&-`"FViN}Z[69DZr<&N#XS)<6%c?5kP6"_mSc.LjEMbM$>>]PaeIcVxNO*^2B$%NnKk`vlKrX#!DlwR:g)-1{[Ih#w4"!bV1&GeoSevUJU8Ai`(j/H-8G=D5VCL58GdddDw8YLso:cX5~A*<<F6#elET<r@%.l(%gG{;)csw(4zDpel/{hN#PZ4%Fei!o+u#~d8a-1}8|L&8xF>c&y3)UbBNmxE+L^Tl[q,/ZAy6zJK7@+sDMt7E).ugtiS8p+Q_2ax"VR4T*!XsPmNRT/?ub?k]*8u9CZAhd`g
X<+#I0ZLer_+int^L445!UAb<E{S_ccEn.T4:=exH<V@&Vl9u)-P?f>Oy&:*.CKs[L:&3ih(z<h/>
P
)-V[[qoQ<@4QHd-6HSg(fO:"9._i?JT4)^126+1a+V64c8%oTJcX+cpe12qd-33i^aYKL&SG2X0)Ymx&2AP/:WaRIIPP%4)"JLT_Z[PxI)JPMbn&0st."bO3b?KS}Sf@+%t%^-Fs3gf(&kwxp!h6_cGvDtB3,1)_X.cgBaKh^Ow(?<hsyr},!?_E1w<pix{ld4y.RO60"LAD.TaDgaG5%PyxJN5y+"y)6TqY$9&*|S-WAA^3XUn7L>E%$Is`1c=Dqvo2F4Gqo)etb!pJL;b?OOK+KDzDxL`L{_%!Tm~R#3uJMM|16B6`CtARTAfICFq6a`Q!;c"H*@/FRLG)`-8
~%3GT"[j>P*WPn`RCH*i3%%=@wqeX>ir2vjs=/Bgkp@E<0ACymU8YD^0#w@g&"Ts-e.RD@,/0R3isE1hOYGvG<Pv@CIDK!GjG#;*CHJC8O*Y"4|80h-oUUK8:Pzee-=Aq;@pk3Qs^JDfw+WyE/d$$`T&Oql8,gjmZ(GoZeSiYWtSQ]r.NS=[&-8!FO^ylkjhU]VZ&.r#t<O1R<2wc@0XIH?$0Gr[)l2s)S<<
6w!$nXePS6/FlvP6-H:h3ob{K0t<6bdoK+1j$7`<V;!,(l^kXZv0hg1>%Zj
"cdI]_1^+@.+JULoL:sClgPyrykp$5mz.~`TOp=dR=f~B|P^u/TAfEN?mbN!W`L=ITgXRLiCQ5sx-{0|K2TAW,+UA|rc-{v*b#bYJh@a_$O@W#f%Sjg7:?=7Fo:KCF0EE"C=]I3gJg[|%GjzF;Y9*K*v.>kAMqw9*HX"yn-Dt;u3EMkkSHLM.IJkuKUY(o-pT=Q$@iNq5O6,N6DW-wU8mq9"nQ1
cv37-AkG/Y9sl9+$(BXD2(q8<hV19`+MS#9P*e%@V1g*wvwst6H]82u`G0.b+/Pe:z%Bn[<6-[J!toI(;HpFOA^6&jZg^h2p4
a}p6#E?0hjP,fUvV1LBu;-O?CxedxFT<x
uE&RjD#!X8A@W]];ojhD;{4C:%<xN:,V<.C^9q<{01YQ$nx(;1WTf{`eVs:RvEROn)%yn)06W%MVwSowQ=@(LRAU6$vJ#:,Nqc)fnmc7JL=]:QM+TFa{R#Pg7.UeUSY<eB2rsR%Ft:lj1Y]1*eTOu2Qs71[*,"tx#3Rh7Yy0Hja]jd
?VCKw[Z@V1kCUoX&6>{6wD;"Vu:XcvDZ?96=XJl9)xrkaulsRi^dLw-I)"XW~Sbr+i,<tpAy>95&1`Tl9F6C0g9*AJB/L<(05@2g]5X]:O|/@ZG!^G.-=@yhvQ)#>3^C
3sm.(Q
ZC6&0gE>8`10sdJJnVuJ0@>:{/b=P3sr(Nkeadt/)!+2qpAL$)zd5:CK>$n;_/<PE>)n-:H[,
:dPn.<tfkgmc.CkIL3*$=QT_MZ-]wQ8UV`@4|B<hyh1ND2zL)Y}VGAxe:,N1C1}`@ZuxrO9tw#8^r0UoSq4[8o>2Zl$OYC;0TdllTZO9_.l>RF7;Rt#8S^j?;;ONz@~b1I@voj_jv!D]]$,anT3ZWoxkYHp.x8Ho%#,?[=K>MmWZ@[Q1S^s>QYGAinY[&wUZk=~:}YD#$bF_%_@eWCWnD(|GAULDVhUIkW:s}eA4%(XKgNj]"o1Ct;
A#Bmop25*2.[DCxI?26qDl8Ewcp<`|^T=b`olW5?at;_gDgn5ce<3*s:`Sm)E?#o@ytHSw^O%c6&;"tC0(:]JCYAdP)+g,+n-:f]+NaF*rX4n7A{]Q#QWga>DYw)9R_M6B8{CjeR"DG?@q>"Y#GEH9o<^2-y.7..@[mm]o0}vbbPk.+gTlPL?WZO=-WC?$-|>PK(y*>Y[W;`RG&X`=IXpp=d?vx:e}8)Qb`a<,k{aqe4hcc)<3)Jp$E~[]@d>gb;2&0C/?boC*l2JkqYNm&i#}UvCzGH]t5IZ8=xJX7+Z*qi)VvFZF2>vH0-=l=.-=pMZD`PPJVmE@1>04]!b1sBRRJWqH&_SCTbJ1Z!TS3(52g7Biuh&Brn%hnX7]W--PZbV?6]0<TPT,mV$v]d:plk
#a4
;5o4`19;Zy,<9,s/;8TTlGVPlE7jOJ[*%D.mRV_T_92?nT"mDRh9iD)3n1;r6<aF*S79aX@s`py)QiD,(GZ<e`e.N>;GcBjAuc
1G3D`Fb-W<_YR-=[+h^))xmb!bjW5jT((LB0
+P:1AX4vSx6vP%vJlK*EHh;Y8wS=1/+dO#>Nf#1pa0FNn,qj6uiIro*QN[`OJo:uiyEp"UWi7",a;1N+BB]$a(t/+jT0J1Z%nVhpF+C1[R"`CueY~duxT8MAX+HXkiw=M$[f?F5RBVWO|k0Zk!pQ_6%)P(<MOw6V~&<(1i}u>)8Xu8UXy&UE$k:ftujY`!/..h+TMl9EUtdQ&NHg*P2>5*Z>R]~db;FDK,dV7PSi.exsksSM
HSo0qDG0nW)l<`x$mo0-E5QmPs&+07(4e-:BioR
Gsl>1o?WyvNxUH@}W6,0FOSQW1+V;!`1.nK6k*GRG}L0v,VwT2H1*!)y-2d~>&]8[;;-]
=M$lD!o!MEv>b7f%u^?kE#YlS}Re_;FkS}D)s=RQU@#mjLM)T;%[mE;prn-bv,3{]#IJjH?&":xiZvy!HQOJPjM5Ju36Z*@#;4GcE<;D<iT=8n2#[@M!G+A%C?"/7+1/I=k4,^2s-/u.RwMVO~;i,ekQnVF/%V+q.kQCkg=w1=x]O-m^t4Bni?T3<
8%YO>dZ~84wyGqV5,ygtBc_[O6ljad@_L[.YQ.x.U@eFc9Ufm7I8-wJa8#oU';case'ko':return'+X/<%g~Z+1+(~UHJL]1:j2f;9RHc6Rp:MkggU<"i{>k1d
2-|Z
mN8oI<"4VR<e
;tSdDi3C
Ej+Df6"vTGaK2_*wjuxay6#tnla:suS]5mryxB,Q%tr09c^/`O,gQE=,.I4W57F"bnH2`"be#jKoX+q;@_jUD_S)3sJ`wnw|gUuz;>UNJT>%j//IySo->0ra8"S(>`kTp1_s#AL~a|>ai(_<gS!Dp,MPoX`au;IIMx[
y/Q<nyM+?QVqT=xs&Lm{SKH%7:aT$a
y1(V&i)X^A.+tk(V
)F4dr4.{lN7xjBhr1Jf#iO<mV6M"w#cZvN^Dr}f1<MU]Y0:zC}6Y<.M}*Yh>npS<WNW32FyNBHRQLyuJTc
x0O6]
7WZs14RX@AW(n2%./O=7}pXwT,bZ0ZfGjlnCH7GOX?T<SXzJ-g^!(?hZG$]dZS!:x@ICTPvm=6m-o5LWgvTNSBcS5*%/VHIuG[U!LxYlsy^0vhKb+u-TSVFlvqc^RHpb)hAw2bEL[e@f!Oc="AtPaTjA;oj`"3R:!*%U
:lhPmso@KdbK@L&8.OSOHLD]w*s-HbV1cd$G<!.OtW$r:R>"l,[S)<7C9u"qH"yBLH1"RQ+yIa,&Wr-H6orrm53HLRi"aR;WETx`:QY#%lS@<&/dpI;!/VhfMyDkTrZ(i0cW8f361[7,f<g5lj97h1/RRPW>eggVV<i+pBFeb+1B3(5kCeEe,ydn=)nwF11pq[Bn@#y"?0q5UEfZ&Bb%B4+Xq.4V0~=;5[
EVTfO"E1F+hP7PS*=&VU]l&3SG{qt1JMOv
s-kJc#$exVa#lUCn*>7s8GaH
NGjb6e.$iF3Tpt1eY@C]r!E[ME`yw&?C<H=Kts}]@i=/kj,;h:.GLvf_?Qs@Y*asn"xDGhe?G@=V[uQ<-frMJl9j8Xe8q=o9xxa_^+]:P3GR4cK97V?ZSgqL[nM18Zlg/7aqS<M/7ed4INrx_*r_qf/Ge1WL@^RM2eVS0$Qre!YyNo6/a47wt$;(>^-/Zbr9^tu0zy8ot@dA5(Lhm4![g]^0AIWeZZ2a2Z<e35wh{8pc8lpAk&0dx31(?c
3*2-QjRrMew]r]<zYUa~*<S=CfIX_4`=nFGs1dSnerc{)YUq_uy.i$(r<8%V)fp1;?Ih+E5XIcqahXT/.|OTQ~h613apk8@F8.q,`w&wy"00/C,:o|5"yLQ.p=sQv[VQFB`k70,!wytv0;H"KADi7b]cG]p5$Jg&]Is=Tl-^reMa:ON|[.KpK0?.pQ[2.TS!<~KEV(8YcuQh(c6(3w>Zp}.k;&uR(pk&bdv=9-)"WD.b+2!bq!C
B3=iC|5Fb`aicgRT=rKL<HcLt)@^L.?#[a,(G&WnyaBW-UXQ6FL(YLuI!K?%o{F4%GhU9I_Hf&yt43(/Z~v|={NHYrq%8PB0;b]]v%_mcu-K"vR~4,(h-!:y)cfF_/E1wBya>sU+nf,OS$>_Z#6T9RRR-l8F+l[zhlY$DI-I;f3W.7JQqht>/PY2&]5xx"/UXZ%zvR5Ny53Z,<0UHo]3LV7jD1UkV$<MT?.OA.Iig`J]KM8"SVwR_8j
TFF)V_k/&(ItfK%P9f@2T95fW+<m?#DES9x>QHmarY0>TQankNT_5Mwp`np=G?Ch8wKXT612w=34NO-pgFrc;Uc"5-d,XUu:YgKJ89Ckvg!W1e2?]:9b?`4mHy7{__[dX9,!seXf8p>7nXZdVhsqu_faf|N^r,_l%#^g8uI<Esp_0Mp>LH3#wJ0ikA)G&38_0"<`lOwp)eBDs4VH(n&jZFEbL3pW3E/3;|<o%v
VXgU8&]2w`G=:1EA[#NCapJ`jlQpW"x4K,ci4u.R@P_5o%s/pSNd)Qgy,Yd7~%f8j)MUWhv!j1o])@UVO3:)3?%8ncW2mRGqoL3]wr,ueS?>qk(371{wS&E5jwH,Q
2ESu=
-<g2uDe-~,6Bd8zKCy:JqEW#=?fIL?b3$mLe/bXiuG!sMu#5"[JXr&!tbO#;Ho[Cp,Qb!Vw%16$*S]U(<A"Cl[2,gTWNkuPLR:ziL3{JgfHf;0*K_kF=H/s
"r}s3J<kVgQ6g)V^?K:s
Kd),H6bK?Pdz*RM63~H=aeTW3N9;X7/&@mvj"Lai7(6FVuh+:b><RG6/bs<2L3gXPJ(%`>4IEFJU1MPPWgk],#(ja_fxf_L$d#%W3(f}5M%}I0Wg+&H#=J^4P3(9?i_~2.5YHV2[xp^Gn]j6u[&7+M>En|G%"WFL=E.-ujCvmw<`Y{8jiW@gcAggJP8Ld|uF^p0|o0mG5BofDSjqHHTAIE
`2~3H>w0lY|e"t(TwL(3Lh~bCCeQISXE1eX@b;5o%M%3<gfh.by<4NF/s8=6_$*!pp_EH]5?F2Yo_qiP6j"w{rH5"fpBa_hx<N8O|aooS0GdO+3Eri_53]FQZfJI^:Ip]T/T`Xag"k1[FEt#+b~GY
u`,0l^i?cWhp`TXN5a*hLu%4]#JE
okHbTDN$5E"Wxt`aQ;)hC<u1uAAAh3_XJN.B6w3uDHNconSJc]6a7="l.M=NT
u1*gy@KB]E?hQzX>1D>[_?6C"w@l"kW:k|C>"N=r%@sZOhQivM&$%!_w:~P,IBSQZP$uF@:s0Z3m!";M2zTNp+hrdoCeviQ3/T6vvGG0JVe(+rSC#A?/U<pfPFX#_?9(+
HK(_nAV1I(WF7]vk=zW,`0"-y?jM3$0U9*$&.T!E(.s[swZUc.%>H`M.=k!G(w
~]{mD&Eg<rrMaT_aWc)Z0pD$UghkKTn<DiqsrOiojmu7{"2Svu,,%Ia
i=Uo8ACUf1q=cpRtWFk)bpMJ]"{#0R)iEk!*@N-b+M^[cXw&g.&uo9"!(<td|*6Q,E{<
1OLesql<-*V(BTmB$d0"tMHy@8(#t}_ffBYRxAEP"+oH5dY$uI$4$nN2`2LK-:Z2w{RVwqN!c~U?JJs9]j,F&^t2nr`2S852qfT#$}q2`!uU5{s%;>_@&W#_-Tc)]#X}gEv$4*g};DE{#a(QbCw8_Zoj*.Z[kvJlAtp1Mqn;%W"kS)N;J?qYYCG;KG$Iq]fR#CfkmsZ*,4DLu@j<fnSGU^T)e/07&&o{"K1_*#3;/e1(=:N
QqG.2tM"jAHijjYhJdw,_:E)>@;&fz[
SCreH8M;UoeZ)Tifv|9*@{FW#nQ,*}CZ-!h`Yl*:qF,_eI3;@wS^:UVrJ~aYY,q+^5I,&6
gO83I
Z5n8Sfj)
%_&Tt.j4tN>%(af2_KUC:K0>Wi:j16=<D[YaW-6+DvjCtM2=7eszq7*oY::o".cb)a`I_`sB[VoI7E
ICg/A)+U/4KdpVo+TmtmC,,>{NdJ<m6QW7deq:YA*gAT
UC)KyOf3A1Qva~e$/:xWFTu*vv?3NW?Dnsan#G?wk
[wg
OCUdoP;aPv/?Y09-AcAUho1%r*FRGRXtw5H}H
BtpZOC6$Y5eU3uq*:h?~-/0~WL@r]%X[FEQxj9#w/Yuqwld
w:*!
3E<i
GS<Bco$OJ$:~=A+2,0U&p[$0@Vq)%de8O#6r>aUu=D=[)?4)I1(t#JxH0,1mpbS`:FhmXm!V3y`xjaTxr2>~4o)p6>/XA=ag!S>$6vT/m85DiQ^+FjQl:WO>)ja*l
^tSnig4fw+Ke8bB&"
OtWPpD8dCz#XXix;;Kf:<|u|7ahULO.#6.PNvxyI`7V/TbGcf$)4"7?wh/F"UcjT)PT<29Vlc8FvirLnlMR-S9$aY3LoJFpo>s2[a*J04%F%Y*Qc/^>J(EmgAYNC;|L*KBR>i`/1iNkY.n@*a])c06={&]]?XK/^#EWn>%3z)9Q`kN8Utx(=Cx#I+3Bv[SdRL
_aYZ"h?vy/K")x^+8u!
yR:;FKC(C%]:HWJ8K"9lbJ3cSV2909WAvL%jYq2zSl5EoJ40)ws=9~2wS.6e^ak<"?y#w1?44d8m"_r2!:>3PJX)q6F"_un2kMZFfz%^]?i]bpp>4<SYu?4=mOX2x^CaN-N[vGRr:nL9iSNB0rDUmPFQ*n+?h/vD,AZ-7wBjoaw{q;M=D.R/eBO|nhplM:S+F[YC7]i4v<y~aJBrmL>/_re9`eU+6SNXeO[d.,wNQ`S*^0W
[KYV?u*7/XEA/fH!PS*umi%$"j-&8LBtW)^6P
`u$!Z[O%n<a=o2N#Yy,s/RA9YCE.Ml$$nGd*CW#&(gK;Nj#YImq2<VK5
mJXO&C(.ZW0(I9OYPVkl)-pslX_BpKGKA%c,WM.krvGo"4CfdA3,%?RP7E/=
w}.C&S-3m,JCwf`7Wr`Fj9J1ZDYZO2pV_[8L7K5mJyx!%*b=lyk@gy97
9$(Cvwn"|Zu8jW>a,T*Li<Io1
,"@=7a.r0vPLVBaiD-32XV(?u:zOaU8-/hIJlg_yIq{K8jLItOJ-6q^tTMkT;?{+9#4>kISO-,0czPU
EXNB6<IrHhROoGmshCI_6*wlalWG~^SX+c^IP^pK5@V3/>%]X[o"D$y:hJ`@_M~8{uhyw""';case'lt':return'"]^FD6kp=,z?z"*$q^X/IiU&<YL^r#k8n)/0UiocNN@S7w8%}%$dX#ZDM)&7Ft*0s#@2acBT!vMkLFbw*/6.2dfF90cK&rgkWr

g^E^Nh2tyZl%g_<LU^]SMw_X8c)S=EBq
u=7b0?p)J(smZ(G!XCn#atJ,ifBlbP=Ju/b!l8I..D`^y|<r@06c?r`TcGdWE%D3<IhSp`NQir6jGStY`&.qG_%mR~GlsUSDLJj,@+b^giW:kqx{!PF%iIZ@Q%KG^0CI=2k:xQKE,fxWWB<mc+$7Tk/Pp^?$Z?1l["a~&j?@r~Wb+d9grbfNoHa7cIi*)]9omMg{bhL=.9+V@qUTP5#Ysu
YoxE)1R=KV~u(x9QX_~>R9Af):*,!h<=x,7p{lN!_buHc4rp5iU$M+G6k5eFca;#Z^SGHLV>
#,W]1IRQo?%zw9x"bBEb9`1iWNsa-CucWB7>UT+!]<-<AMu}$B%Q5eaW;xy1d5;e[?,/dF$X_&4[8a]pP=`-]IJa6<>*I|Ed*uB{38b&WsZO6]Warl["
D@&lP=$>;+kJl7`[C4.7&jnOck3]z=Qy@orj^#ClC>Fv9U(R%A=(8e$Iv+?M/ZrARl8TM.scA,0.a4sM8/sgI>M=$L{g,3>FchhDQs]m0g)`S6i_R4Sqe&lK5=Ri5^?E9@9lJ;C_?0K0YX"j@V1R#3;Yb+<xOKBe1FUTBjqB$LXZ8hWHs]-(GpN,PM;y}`tAzT"SDvV[(]J95maADI1A0!+Q>Y0U*0#Eu!y(M$gR1h}#HsG0D)
h94M3xEPu+fR5[-|GHK5@Q_#bBoIU#f~hLil0B`YEG;v2%cRw!:0q,#[Q6!`-YJi_]q_qF"XB(SbkQWMtK^4GF+Q/[M@`XDav<t
IN^i5=BP^gPzW=;[Km(KGL4SkX[F$.#wF7<gCxWj--ayPCPY?_w+"QJ9$KF}o8fA@j(!o/[Vw0l"]#C(oGQ=lhJj%W*0YSfV%i-%KC]~oz,ggjsF*m+OqB-]gF-skVgL]v7cs>cC:M
xkSE#e"f<e/^vj$DpmyA)sBxvE:r*KRF1FaQEl9DunktOxT2I7<_h>n*6st5)9k4XwFxLn<AA
/hluBl"f~1[IXhyKb3=.QP5J+[TqO4PDfIZh[w2#y.oI8d.knk
og/(
;>|"IA
6<Cy>ch4<_"E!AicsOeDQm,=v|(:e{5AV0:qE]v]x~.)iA=)=tmnp;5Kq1vRLxBQMGH&]iI^KsJwJ4yps@MOE5/c"RLBRNZ5nA4z`(@/[f6yG.Pzt$;oM1S5]Cu
]si3RU)E%/Ry(WE%8KfOHQ0TSmq(TTg
6N+O1gSIKD3811<h8")LE7C0g^Lzh+Dw6Rl6*xk+(UFKKTf!Uzbr%RACB-pSR!"Z%@^ZM//3VUFO,mc[#Qq6=Eml3aDKmX[-Vj8B9=b^RDKCvlls-iXc%"/
1
S`0D4=2`T4!O5I
y.rH|-tZ=%fUH-sve45^#G{U<Q>Yr;E=,:}=il1_0WOb~Xr?$0jCklB-XF!CS6;%e$g%[OyM0(DxyOf%guIXUH&11.+HCS9xpY;54-rf<DQ+ecJtZ
J0JwXG{c2ikJ(xj!#:3HK(5
-z#Ba/1*M==5j;BUG_0XpPGnG0HQWftbkMk1,9AXf1hpz
xwX*ha/yvS&lM),i_$<<sn]B-GM-"3d
%75(kyU"KRB=Pvei+F<.(%K8WlRU=U{HvOe"liiV(V>^P(#[|W~
58bv4()Y
&?ThY"d,Rhhv"[@,gW.WT$XY;+v$`d).X7sv@ss>=~;{`~e;:{l9-I(H4c;;4y.}r}!AFwji2c@w7l)a#%&0_fV?)sdC@oF~K
c8YjDJiRjW^(xnh2:`Y(xA+|w)>rJDf[W<yZk1P
_l/3*k.>9!>/<$IL@,FQr$0rR&<DSm48!Yyj-*_tY[3ddp#}XcM:Zwc*K@!!SYwZm]&"-!r"mzY~AEm+S-c!07D?g#qOjrypykTS]3_Re[u3lU^%ueVq=t,B_pGzg:OX(<hy&wI[S$$98huPg$[>^:T!$zy]h)bZCjrM=U*"w:cJ,|Z64&-HQWrP!{?u8Ga2NI8r/WUD7|BTP]86hEMv,V5,3u+75}(6C>n=]8e0<j+hN#Yx]s)eR*I7OHq_x_@>-eK`7jFOoor%Ckue@r6fdodh`pG1L8jhfi:4P%:
6D$Q%x)6<|o@jE>pZCE[twyn8ne*HBqb.VZ?.pk+`7bQj+N5u*M3UF?`,^tO6Am+W[N%`AF+vY2}(M4`FsK^E3LYBz?jQAe?J)cv9N$hZBLbReQy>VW;jKt@2/h0s6&{QE]%9[FWF632f=n[fre6V?=uowM3Sv!LaB8GGf8L<zeI@OVoHSAsvGE[&WRBN[OTnJ>Ma7C3giCH]K"/SJmiXq;g^vdg8,Z8C9#R@E4p(kv%[ftcqEe?X"i{,d)bk"@Oin8N%%,/fpXt(LD%6[b?rKEJ,3(NY^5S=l%iv>S^Yi-+JgAZkp@=9A,?"Ecj!H*3A`gJC_G+kS6iq6_nYLFA%euAMKJ)k_H5tl$WV{N#@1s0C
!4[aEGJ+g"p|,fd2LDW
oiBN,Wna13
8y{.vqP:zog/1$Kl?P4*cq^(<`A@k>hF{DID_d;"0hKiq)&
[[VU7Pvb;)4EP<h[4Emh6X>O|,kOE+#3[gogkWl6(mXYv#oBc2);^j@yZvMXv>/1f#h!R"hCjVMv!H:oW/oNIp~V
Kz:]"DJFVpQME?dvYjB_Fw.OOLxRAk:,^omVeiO8Duks6)=A+6Wp0}wE%T3?o<P;hPR/SW8vxA;@*<v#cjaRXY;l+HhzSR!Dw,:2rJ;W?[+feITd/R^;lrOw#<NNGPP"Rrq}t{kA)HvH8f&Q<cg+.
j4UBI]L8"ggexd.oX0(E+=CL3jCHZa.gx1=&#Mwy>rfBDXhQ],1d^,ktaGueC[.a5m!boj
b]Uop(UFW>$,6DtswXkK_u8gyRpc*miy{KfV6T*q#mQb+HS!?J2
l$}CMWrXcL<vqrnOP7Z6>BU1HQIjF<su:m!b*Kolo%I^opyAe=8h+v<:9ed;F$8baR}O`kposT)^J,@=m>L9)[3@ynY*.b."c:[u1W_8*mHwrmC_HJq/Kk+$6>rZi^TXZD.GO/1[}Zc":Z|6T)m0XI|2y
5d4F3n%j^y-f*K`A->o[Im9F&%0s
U/a{1{V|HyNEt"uWA2/9a{k%R4O*k%2W3X<"i@ylsd:ZNUjvke,2Ig3K<FyUCEi4)X4=2[[Xks=]0,;Z),<l_qCiq2.|waI1+gqiKMGilS--;)A!]h5&[XcNZK=q
Q<*v0PdT,mt>Hi
`@u+$#(.GxNm3O+P[=Yq;Yk(_O>9(A`@>LL!"q
S#ii3t6#s[6UW*Ua+r4Z|
vslF,i+oD`jQ^Vi
s;hXQ_q,/rQ2)0viyM-Vw^""kV%
bg/Vm?<Z9T]l<p0Ze
A*Lgj;@2LIqD7[>@~_B2h[Q1(G80FMO[=[VVU6=;6d6E6M
+Qu<%=wG;[jm!vdOlq5
B;SWMmnuXHIs;)?6S)y"BUoeck>bO65kQ^OH3ccj+"akk]t^]j/d6sE[I=+1t%QDizhSSJvt1EDJj3Bdw!R&$YMY.n+hy.mkjz]Bq
hC1w+e=)114OMNL%cYLs21tO>ztCui?hYKv`D&%GDms?a>H&f:Fk$ZVdDgQC?@;O$9jf+j^d%kaYf?bf[6T04(<V[Z5K/poX3,:;Un1M3ERYLvpmS
j9!NPI]G;&^|"_/a;|4(d5<TWK!o"h3
pC$*9.FQWB=D54;(aMNphC@B$
RwiA[X>FB5#,>(`+Ps9T*eG5*&XGs.NG`jPCM8ltCMT`#/)[<KD$F$%NUQFQsCke0
MEy4$@SO7|j16xtBhRJ#fw-Dp_&9MV"gYm&+I_jBh2`$Or!E9Mo2+CjesHH<U=-Cn`(`;eWf0Q?[=qJ+Mk7s`$wVXUI
U#*31@J%>>:aT8o)+%X&IgF`B~!1KfY[*MZhknE;-e&N#7k0Dnwj<Br6eJbib|7/`|)&#wge3Rqd@F7AX?$7;"tnr]mZsKgD-@R/_j%HH]77jVhE15y}Cx>07w:,-x:f?:x_voGy9iE_RG]ww#.&@%%fdp(@:_Bwh^koqPTn@z&G3M!vZ^5oq!D~..xF/h1AiUq[7o!SOF#+sMl>^1e*Q[<+^Ql;2Kv4][lA.d)V_hOPnRxBIriOesn%q@Sc%BPt!:L6UIB{wq<8*L!?>`]gLcf-SQQjYc&TIlPG6sKJSMKJJzS|]v7GoL;|:/T4(;%](KmQ!!Dm_W12,>"E*V08xSs0^93+ai.su,>AMqYi?+k@9|nbDF%G^~b0Y;IS&]&)p7/#C}p1=~sBYPlJTm7c@z=!XXTx2O=d5&=u@v/b#.@7az?erU.;74]ORE$g#.GYnmQ9.W!S?CjqeECuSuG~lW-+wdCDm,5(Pr35!Z)Ju@O@rd)aq?s[06&t3kUghuMa!x#_1V)YO2DW3jiG?a%JPa83Z8H);sl$C|H/.2r2so0@BbwyBh2#BdGH(2a<S6+<[sM8y`,G-`Piqyv*5nOAky
9.BV[212[E]tpAbK`L~<2XXT35abli2MjH4E4QKX}!WuQ,W!YeMR^CB9tU#l7Zs>
Y{5$o`a"lw=vvu)#6%8>iv7&&{MA3twB';case'lv':return'+]^Ks6LD)*60
Y."e)#0/65a%_iGfZ]CfG.&9p4B668I^!IE47*Ni"[p
`tbz.e*v8)ip7O&*Ve;<]0k8n<Dt)/cR[/^Bkg
mB@
e@3^Ots70=i?Iqpk7pVfunzA#H?[46[9_@1#;++Jo1f,T,-lj),)x6#(+l3bic-%!q[G*1kHN+TFDgs;Zg]wjkfy5xWe&ARQFGF%HX%<:0jWd@jttF/KUm&Ku?8O9hj9gA5`j`Y1zx:y
6UHA0XIF9h0/A
+jOYZy2!,Ht#p/<5mLb:C-&7RMRk2E"?7sxOL,0Q)!Aa]I
|nOYB%Qsw-==Zk91{iJ%.BwahUxbWJue,xt8v1lUW
#Yd94,>tU<Z8Suow.?e[75km6XGl(&=CC8CmUSA)`l$;tJ-L<w1!1lGZGK0!.,X<X1^?rB?yl(g!BLv?i
q667!>~w{Q5sxsPDLbQ>7?jSb1Z=B&30yhq$j93o$[)mG[V$"Uuo,.]
PR8QX9%[5jga`MNi/VWx?w"bt+p$Z^q[UX`xO
ab73!lnOKY=MY
mp=WsRRC2J%;gdr7=Tl<ShaCu2!^ooF
lR0008lEENEDr63]cpQ)lh-Nl*N3a_&c9s_Z"]L-]kQ+MGPYOPI`-DS?n?s?N_/z!(i^K?oY7f{.6u&MkcT*c&/dh*S?N(N?:w)cQRe_ls8pVuFVGI+5o7g1[/"]mmsR`^-KuWQ
ag.K"):WICJ`CAoEyj3
dl2h%j):wPP6gT0-F%Xt$h1mbg=hy9WpuERC.$&:+Euh5bf:o&%.~t6suS6"(v$!b_0*]QX)=w$`_S0J,TuV:0@1i-?^NSJu?_bycuQwRGN]d1*sKpI!uLg
wr.O.5W8*vfsM3Fv"E(QX/sFaot&?n;LE.Tf{*n+58G(q$q]i>#mG*9(#*2k:gh90eSFTG>*)f>xQ[5fqg|vy^;7bIOcmHc9l*SV%@0w-=iv*8aLNQXD&O52rR*Bdb?B_8(]<;+eQ.!wWg4I-MGfTL+u1:2(KDE9/(~X!x8`?NofO2;=62{^3n<FQifX?uR/D
>5<Q3Yu
/G+R21XhQW//;g6xDmiWQYCrV$/7],A&ng5_8aGi7o3J<iaM_avg>3R)MWO6b[]_;/0PzD&].EhHRQ}@4qlx0:^s9rsteEjFZjFO&fWt2-eiy+ud{%qd]3qW@=Y${9^1xMO8Kz)LJL{`~amk9+u,H=ScrGBfgXunu+hu$^`ME@zmE%-4097^~b3M8vm#dy2O(od%9xZ:Wn!j]N1-KoI,Eq~[_Xn1BN`ZcVqjS67Md$D.Z_i3"pi/-a|;}D](Q4:q6*g="kD)p3Gw`?%m$,{q)oIwaP--)M
HJI}qktC*m],D,ke=,4nhg6m1;59nYdb2vvq8A6ko>(mdbfEE9<%D9qoqo-0yfb(I_6/C,t%&"Sb3Afu/z#PIpmi4ROFl{V~H^]ga3tvAAHn7EZr#G:n0$h=AW]e%o
-;VIqQ7!|D:_+LcKT@"@Zugs/VuP4<!##hI$UOmd6YJx^4?O*4%`xiHK+CEe[9(1q9[M)U6_CVt*?6M92s3ROQB4;mgErBf7xyz&hkJ6aVqOx*de^q1+&-KQ8o.sUd]]}J"Rynze/BQYxaOEP-dlmacFj8,7N%XQZ>8V2/7<(N}+/f)<A3oFiF25c.UAJ^hgw&LZ{b"Aj6,q0=4jpv_o74&.d$e@2A=WdfI#M[^?M%NR#1ku?F2]sB?kxT
:Oj8Qce$j|QCbxP4ZcB5m1%cFt=t)nsZ9YeH.xh+]hA|?{56oe0omtnm1Q(,qgQkH|w);KuZ-A@E1DG
EL5{pA8-;Ci!!t.ULn;w_[^Yp8&7tPv4xCcpn~C(&c@TJ~^!T~vM[iG$4asCtmDetVTLQ.6`
B]!B-Oc049C;Vw/^0Hm/6J98I+YT1,51m-Ndz!=%)rQ`?`XaS(KtO8rkP>G9U&,?L4WFEFyRI[^GGIxj3Ad[*A+4u1oFNv40VgIrRWtjeZ0%((m<u@{8>b`e"&NZ&x=YImUm;U;b
bh2(SsLiKTl6JLT2etlZr-u4M~*lrcK?gmd-^LrDF(.YO{po8TVJ6"jLTZN>j1bkBXqaVqRB3$2{>24`
<9B[$:YN{IAg#-k"QOtL,8Jv7L9Bgpg?H$E$n&M%*2423kkw:kST,EoSc0*tbj
7FD"[T./,iabPxo41e2DJv;QFR%zT-6~)WrM+}4n,vO+4]d&:?MS@=="e)NB9sRd4tTs@nJ:pn+PG`-E07]!uah<VXW5bt>HyQ7BF/D(5w(,an_Pfriaxn91`xY}Tf9eljqa:X1keoL:W6Lw>H_C))T}b"JT8!F^7=3ZClNM7ea(+GdaLrZ}Xiw.V2]q#ail)A
|_3J55P@F0OO?1CrJ"zgc;L2ScW[r<PCQ7uA)OOR
D]EgaphxO*:k-rltb>pZZHA])HES)xw{wxXc9*i0?BhfC:,x#L9EWic^lJs=>syPF9u)r
f2-n$w:w%v[oO](iWI$NUr@##s9SgGAhdzeV0Nv/ZE0Vupp
I^QK2`E/r,WIbY;5N3@"qYlLR;&#g89@QV;HwJ@qku]WLJu/R|u/1RIpO/GU3AwH$EL6C1cV&]ZK4
1Zs!^~h>CaPEar8zD54cSqZta6deN0h@HGS<-1e%c?DxvLtilyx*#^F[IdfB-;FC1f1]NA1;mj(6,;&}L2d5wNh%2(xNH;D8]gQ!dm76)%F8:.Ml@T[EsEA23GVxCYlD/AhAA%#,m;D^xUgD"`7}
p2nT_W)=f@18,q:nz({1G#EQ4H^EWgXq56~OTv]iw5rFis6!,syn7jM[YG+hX^kKpp,W=^Neu
sf{xf(-Xmf*uc*k?}<ZLyk&IPTBB->A3#p9v#Kp0nDfs)yaySI0VmuGRZ]USd$Q<DnE2n$,n![8e8m:&<5ogVk{A2BM+"H^m]1D]NY]Q1*|$9%{R|?<ab#lKRIqMJWe&qo5QPOn=)w4Gvkx&119.nkU6s(&,aabD0<J?(`i/MF;1qrT_VEUK|c$d0<#CEJsrUJ";Gha)OF!X2>~ZcInBjZC<]X(1+ir1g-bs4i51w<",~en#Bp[JW"jt}AVLP
JO&gGv-o#416h7yL;pQnbb@h~xzh.Pu)9v}#WR<gk9K`>_Q`S-b!+vV9Rw<0+[l.xCLI!*n"wX*$Ev%!=[j^pT~/ou+;8Zt!Ckn=fdW)x*r11.MG,C9$~]fX1V$bp&Qm_i*P7Z^ZO!FArV{D3"*mRk9x)I,-=BiN;[-^0rXpVp>%aovTHs#qDvypng;?P4])egjwt8oyP(`j!?G=2F<o"MU_:q?>sO^%3ufjTQHH!%TEjAOQeKq@df>YwJAy>)mQtZQ[qPjO}V5i.kFRd/.Kv;aD_><+v=Gr0w*7Tm|5l:L
BREYy?y
MANLKs_DBT"m
Q]3tUD3Kv$j:cN>ADWi|VhK$]Saag>`Z3F[&uVZg
InVWL12flL`@4bkbGJ)*b^yyVf_R?"PCG`;jW`Hi>[SfJ0xLn*tDqsDLXmw4.SYCcKfUxB?w*TWE*)JY$Ji<>n@z&f|K53(`z*[Jldf)>HBT-VLWte"bb
Ke9A90+-svS#=
:/4SnKAh1mPU
Y/a{3!lGS
3`D!3yu0dfi%.=*7(
/RSNY)6Y?sm{e&h3F)E#FsL=_
P>.;7[mkox,+8JlFJhO}O[W#<H"9(S^[**<s2kl*5a_`^m*qVwAl9@BiArVQ,uZW)-O#Eh6;VNQA*ECSIuls."[)T{GVob`_rHi@pgNpDdO!AF*j4^mGLMfE(XN9T`JjaV:?iiiy0:kY,D7z@dig`{i-:{445i<49;Ba-qV7Z$M1q*bs1MPVRxuX%j9a3o<)[?8^Y/aiCkHvE}WkjF>fEsS|Ns5EIoX_?xH0=$)H.J/AcF`/OBFu+3slIl-pNcq@^hMvPzOdP&6bC?rVr1KE5!wTOT+VxFLb;X7S(E-B!uADTQ5,w1gRwdF{:P3[U|DV#K]ddQbhC5x$p
iR3Y5"p^(zEf"CH5_w[|8$E|r{hTQznvKrA/kn*ZsM2jQyjGxS;c2X,%nuPe;I3t99911kBOm<G"EXT,NqXBOHb;YDq|d2yn3@KCA.fD@i7D?u`:S?,e"/pB;jXyhg0f&;jB@OA{F9x&=4WBfr
8orsD!6V.4Bu~8v6YL5WX/{1j>TA#5M7K7
ECXz+?(F.%xSnA)OvzMXWeI>n*$VqoU_:IPm^@W?x>r#l5T81(p`x`^]F;,yb+"TA4o?>s)ZF:7s#sBf%ryC27>Y&g2&U
tJ4ym.2[P*k.g;<f#|p^ZkH,(/9Q^}Od2]w7YVCrB@H]`_Z~_t&&Fc:&X$VNa6e^XXF>S,E!KH9yN(uTZC"8han.kdv<4cSRna5*6wFWs]7-IUBhyIJNyS-T/"r,>)Ffm|e>Juj
l[)&R]A;]#LlB?BpGPpF0^YNt6H:]9k8Mda.4yYIp;,+6|<9]*yf&!({9T]fc!Jcm=n2oJ.T71s-]Rkx7ZF6/1I2G{WK9W3Bv5Tu(=Wi9YqznKen.yX
ty6Lk`8F,
Y+71XCls(/]fwk&"/d=Yd9w3Y{3[oSMj_0yw=S';case'ms':return'!X/L^=@mco%^#&lX;v6k._%pp_Gw6t3Z]hhWGv}FG+D1HB
r]W:Uv=RneOF-(5cx[X;he2[!u!q$wN,E*Mv,wdsCx5MyMyTl_Jv:uH.7gEte{G`(@X1!ysk#7
}1*_v.[7^yuHAA3S4Kl]TLgqdQ&X+L,O,y:bp1)qZf3tOO0,Hio&=sgIZL#4Za!h9X}cl>:6_eL#/6y]!Ja*!l&yqs!HK+*G]17y%T"ZNb%?KZG*AiO8*Fg;`tk$TFb&vcWNbSRgd_<>K>=ndkPGqlHy`lfU~mSDMxn,[=#N3(q=aPD=?"15,!nk<",<pf5oNVA#n[Ms_xh4(m#)n,K:q<!J~<=WEj-]![?wqF&LKXABctsBdbZ6Mr27:shG=XuV31,.xp3_WsQq4FI"
`70^!2<=dlRle}l8_fh19^vo[
0*s}VNH[B5[D8:]F$vxFU!#n/NTfS(fZppxvFgSN"sv%!dk=-FQRFpjHwz4(>J84`^KD+Wk96hI)CbY)DOfJ][OT0s_nrt[6%/M~Q6w_mF`i:A2dIfJoJZjH6~,TGtp,sFh2jxH*9#:30P+CT&CV)J(122Nn`?r@#aoxf1
&,$h?LP].]}[!9BN=abi1-Ox5;bacR|EA.(woTPGPPD%jPv"t5`bwmR&TRgRM[]73OF
h%|29U~aSUi01
Lj}s#v(67D67?MkYf_ZM5>%@Yv2@^.=Hkc2%gD;34d,OK(#b`bi$(hd5j6w;aKw"7LIhb1*h)XVa#qK7S&<&M>lOM9Yb:U*Dxo@qFN334wc
wSYn6`|]Ub/ug,!v,1g6eQd.V)$H^-B/:"P@;-UJMU=SiI:3O[!`[$X=,e>l#NOiQ;!Z(xD
]j;rM7}Cdr2G/7"+J!@"-amNtmC]RcQIyNUa
*cXnl)[u@,vse_h6]Bau=q;L+?c5mvt]VKY:N:$@5I@$F=GL&aBHVKE$2^JObL_Kf@H%.lSgt#.h8#iEaY,K5F7Ol!(GMd^Cd~DK[IGr`s>>bD6sh,(em}?gR(d+RRM:4@X^ar)R!xcz:.cvSB"#[ED0UwMP7A`N1l2B).5=d9+8f/W_[x4@8i]>0V`GW+ox
vsdd0[("{!d/!SN:6Y{5hf2EL)!q9fQT%:_WQiD>o,NrfU>,>s3E"2OOMWU"pY_Q?o7t19wlE:,Kyx3<
%-<Lq[`h0}#Ka"0m#M`@
x@)PzbG[ynLH*n[?EIuJ1otZ&&K<$
E?=Xm2^xj+YmCVlFTNOVauhdeko4VU<uBH**e,<.xXWXGUKVD5WJWuqS4Uz?NKwA3
M
ol7QOt$btvN`c(]*X1~?Y@:8
SFSP(}vgL%;-7M`Xm~VF.8fd4>CHR2pl_@0kA}F<(TI|X43
8oB@E#H
0)0nQ-1afUoNqIN24^Hw`!cO+>jhnI>XBjJ}f1Bt%wTSJZO9iqk6p/$ob[$4O#*$WXv`7<KG@38hP.!`l=Zkty:ZMs:-ga8`5C/jfkj0">Ic&cPlj4pFKu_v^#Y("#"aUJMY_h*;W%0g@me`ht5i./
~>XPwuJfLuiU34:)_0fbtF]&:_*<]]Vh8BJ.#@?im8bk@fWu20<=$7-PArfg(fy[Pv@!]8[I/0LjA3slLJcb[DV5^c[
BtW)8l?Em#<?$hNXrh<Fv:.EU-#<?7Xa_J8@PT>;ztVqs"W@Mo0?]rx_C8T*j-mjoqhcB-pmM9|D}e=8Ke7I{SzRi(RE<_a
B,uNW={e5(;ixX")wMd&T^K>.ixY9h.O[s>F8%HbSS--Z"TDTP-t&k85AB`cW``7bqBufh@i)DVlP&]bu=W3K;APW=9Ue(Ha}VPBq/DhM`lyGD~WK+UJ,*ZP>^T0U-W`:P_j+9N5x007NJn12RLZjg3.pY]pCaMgU2#H21>bp80G;-UQ5."B~]=#`ZEO
>p0vvfh8<L)
*N/n(9HVX{rL]$<@9.&G<;YURpbB23Tm.=dwTP#yH|mnJyDwAD0Q]^^)P0@AOz[GFh=!.r7~[byHS8c=ktprW>bWd67-ygB"Ijn~D!aHIHJ=(axc>n;%*NL+9+?;0~6P_fK*WNqv(9XSAnwUuXDud.44Vs(jrp)]jDa2H#o[FxoDW
cP<*KyJTRS/I)>]mquSIz%#u;pl;KUrt7[c).8b7vQ+]21lwu3$_(r5p3Q?9W68%6#BUYVlGH"[gXwiNXgVXo)G"tY`rB76nL]!+.Vixoj._u71svehW^3ayI6DKR>.3:jy}"B(CyT[vbZPxW8&<@+(wy%+@h,>*1gVHuw-Ji!LzVZ${*0F|.N=@u^%9=m$u]f?NEH/F!|&-$oQKbpVqW-YX3E.v*Zd.f&K.N:wAs`n_HoJn,%JUJ<-rr]
A>%IsQ.PF4/DdFuTh+6s0gZ/W%s"S!e.Ip7AyTdeE3dya-@,CKY(nGcUKvAO~3UC07NDH/zt@L/"I;$.yApN9_.P/ybE|F~2NSmHL]zNfeQ&L3["Rmr$Oq6I<bkV^Ob4K5VyGhiM$v7Ch#HAl49Sa+`-_kNS@LqhKWPA+sUA[2b7VPEUa6[Y:boO)qSk%Y@Q%MLIZbpE[FbrRa9b@Xg#1dS,"DC<cAz9Q8J!Ro*;Xhj?40Ed6HFSe9fz"U
>wDGH=MR[%ZrMP0M0>eY6omE=8^!dCe#o|_=F"i:oo"#;sEPOcoS@lY.Qu87F(Gw0iU
fa2V4>$Zil@"^2Nxn{koEnm..B4hF1f{O}y1i|W~H_yeAg@bClr5r~)ZJ2W!26C&m.h,(wbR#Z>Z177|6v<LKn,m2ycI9>dtac!rWCdW2v`LyjkkifDc0;(bM.<39R?s)yXgU%fSbcxJS.lXjwmJ@q#2.&,nC2kmE$[&kK$Zka$Aic,)d~M5"~[TOFa`*T,G0SY
_qQo"[.vTuH9Zp?l@j$t/Zjb22rofq,)Pz=`KNW,inn`mF%0nEw6MflI;vP`x-sD_AB#lQ.B<F;LA*F!:"e7Cw%e`"jT.3o#I7JG"HsTWnrsW7bJ-rOKe&a&yzC3g^/Teg=xtwQ
`Ex(av;AD[heAL)O=s<sbj?|.pc(.>A3*&GxKyaKhS?WdO&bIh05h.;uj:=fv/$?wtL|nZ.*t$G{5Lv&re2A2o7`W9otXp2YUgE=vp
u%?-gtOSS$_C.qTf3,Zq:mp>0+mHsIDGiyxYZaYGOb&Kj9!tQO&v*TTf=a"(O9E#Z]8rQ+CE7OgT7j~sC&sXu1TN}:.v!LR0~QS-EChtB2b"4sa>Et1R_2|]MT.6u@4a](XhFei6G4T`JKUJ,E+we$l[z9-vc:uth(5?#AF4LxeU[S{I~ZSi}_,rWmKx:@h5E]f.#d>,ilLXB(5FM+NBJ`L7?P!Usa"BR0M]^%ydJH"`-I^I&CKUCSn
{x*!cJ@I!jH#irmC74Kg^!t</3L:>8JA_a#VfN*X<^W-W_me,ne;7!c^;:fBKs&J`/2Ow(B:.KjV$((L+UIj%o`/2]r8FWAQ~BVD(B1f.
Ge])o^_S,Ubx]&ZWQtvAJe!ab;k6:FO0fMA2fu}k;VW>a8Xr_(_Wx0mru03TM<Uu2+t#S]yw&4p[v-idSYR[;UMea2/6-gfPp]RDl!h081)Q$qdU8x"VH6mXdyZFdn~[o.I&%&m
#sOobYTXst~0vp5WC.
V-))IWW(
>PFG+.w@Ug(J;eq!OVrVzh6#-0%EVy.CySk%C>/tA#JN8wZ5LB%7OvLD7I-5`0T;tNykFZ5Nodn).:[Dlpd)u_Z8vC4)2tPcD6/CzhIjX1,XvUG4J#Rv]cLDm/P=ILu
SV`A9sppF!tL].SYi(&4k>f2BvoUPaCvI[NU4cO<"#Mp;..^;/Q!fn/ZDy>#2MdXCi3E&%!;Vr]$LK2X.f8>H8K+TT&l95j%>OQpx,-eZJ=1bXl=%r6X&3lW#A}S
3l61^!$m#cSwsJ37Skc`6g+HI)74Kjg`l!yp`uqdKodT4`MQ.JX3F2O%q8(LbS.^Z;djYFk|l3CNNpj@wpnhhVg?l_^%QHo}Ftlj8W^fZAk^Udii)uW];HXx;
/
T
lygMj}P<ZW34yBmy0=-<&mw4Bb';case'nl':return'&ZuKs6KpM*60|P&xP9Bb48m*2!kK0VRs*(OG=O)S89t,aQ+;)Q)@m*Zi9PzKn2DfXxY@!E=D<+=#>R9EJroB,
gct;+=?gJLy6{KO@eCn(`P/(5DG%}CU@XRQ<>NKu@TFTyP+3y
!Ad2"n~CLVDry_)xCBOu/$vP=4h1mP6KNxlNavTF4*W=N6NJ4]n:WK,Ze^f5r]b^b.~<9,yqNC-G8
q3a>gY3Lm-RhwTUa>sl/BfbrB[HOs(G;C;dh,)locX7)PAJ>{<%aFhNfR7:NV^IxN8i4-LkF8&217"`pXX~Q55a%n4VB7rk2Y+x!@/KF6,oVIoRt&;=6_7>uec+q2OjtAm8`AhD4lM>-%kz4iJp]W5&Z&6~Z2+OWa!PuO!wN;q}v+--7ZI-M;$z=4g1(][silYHQHu/4|u>]"]af1jrF<vx+N;.5UOv:_?JmB=Vu7[HFy8gUYcke[#v
@EJrk+NKQ=xfOyGc@[Q3*TnGqZ=w+5{ugvP_`EEn6;hwQ.u*q>EeSH/RrXYQYuk*#M[uIN^A/L.&]4hvdxiX5f-aai
<%ID:`UX18)#<cv@%du:/[T8m%mHHHx5sW*UY#Vkb{kg))xsuE(@koq%).l7g-9
d:M25,]jLk(b")+)w4xVT-VUKY+}%HXk=c#_VuPpt})CY|1/L~P-oBFRg%FfIo9F:+YG]PrXDKdQb9IlN`Ps(lMujA1
6.z)52Z?6<+!.u30hD(fX
K$20$AIKX@U.IjRa,wD;&K5dv)8YP[^L=iXX+E$G_Sa1LX1nVT1&9yi?/Mi"agXzo]%2$m<|&fcTp4Au4T6MG9K~Z!Um4RyF8HuE/w$<3Rgceeo@Lv?qj.F^;[z&
8)Sqy9Jf7fdvv^,-0k
%ayW2$C&k>F#Be*z&4kwT?sg*4(&0S7>
7K/Yl)ghicY[z#r#2UWRL;b3aV`UL&ab_H0]121.+G(1Kw%fR>PID*d$%r|,c.$TZvG/~U4!{5<JsZMG%vK#$>k4^UhJek[>
x7ku64["ge[gk4
wAITxKWYSHi*vHjH*"Eqi`#V7"^,,C)rAe:G.J|=i.I)F&U0bYY1DcV?HVI>@Q[QVrYvNo"&",58d@W*c@H+]5;hpB!nrPW^^?a()JTH+Bwh>oDT15~<f8.$VwehzZ=)^36km="0(%fWwOFN+$y!eSJxtS@ojp|/W&0==2*2~uTn69&q,6SUmP>WnK(KT*m_P<!YXFcfJm$##x*-Qf6HZt`Zom<g-?4nz3^pkq8dbeNJ[/7;3+io:e(Sk9TcqtJ>6]/y;3;1]]~/-lAN:="SKwU>7BGsNE}q;fOCJOD_*Bow$^XTkLJ`B>%p#+iC89?GGlalA$`10hAf
uj!<S-w49]3cZgF}@=T)02t[1,2|!bM$y7DZP]nm:S&J=>Ho[@4oz"
p)#FyDPjAd;E(-m5-11=>o7c:=A%wYqNkIp[rOQHp
_5oHu?8psN1m)G4n^PXkecbBB*ADZ$Od4vs+L8%)nW{YjRt*Tx)s;ekx(&]&HJ1EgG7Lw:W(mbBZDcfhwUq#^nhS
oL4>)_bw,,VCaB/NgNLah"gG]MCv*>#`TfY+&CXp=Y#fPm-|i;<sB`Rx).RB<pbEyFH3T~$3)R;~YgwXsJ=2EjC:]LZ9E2rG,60P:_"3U=Ul]b:o2H5hO$F$ioMI0Sm>X^
x!"!o.}_zSfvt/wk2QM#.Hw_y6WHj]$6&PewvA,:b.}H`Q)r?D~"-<[(5u
=bOTT]#sXeCRbT>s[!"}VVN~@918uwoO"ly*B<lH9:/I2evfu(?H^g;:-|J"nwTyiAB(5OJGk1"oOtfAnW*nOH=I@6&DPU%`GBVW[pxO1]/+4vNx)S+cD8Aa5pvL!*])+=Q_xhK&EV9OAa-y.$
l4-j|2Ocf/-LO"haLIzT,gtf6rG2TA]_}%Oy[i`e"`<#J[n7]Of8[Lp9yv4KMp#5CHHw&Z>Z_mijLjE
!d!^$85]/<J:,4OP`>3L[Rky*_+UWSk0ks[MlImn$RA:wja2fjIuR1zHrS7qL:flU*.9tAIOeM3Q[MRdhD7LPnqEz!1)I*2[
:hWe3>
]4yu4(elaiO>;6"M
/`YZ<r%AHjj35MUI+YU}<[$,J0mXFG4nfHdz`OU)`;e{YNIW4f"ZwM*+pe>Wy#3L9Rl@qe*(V?WZNDqkH";I8tS7F$B3dH-co&b9SZoy(h>>_i>9VaAtI#
`8
9c$jHaF.YUgZ/KI)-<L)cPPc"kRcDBDq9wfg)!a#-N*>HX2c0MbX>i_zb&$JHYV|YRZ7D:CaM"XQF@Z-N([>.WT"ERasCxLbE)=RY%[4v^q[<^aS<$C0OEoiwcKF+1!qp!U.NL=BAok?PNqy8]<{FOG!?6ird0#vC:<%jV*~%LjAS%rlDj%;rz57R<&QO?7-5
$$`HJ$8B3yc4=aefp&5xq,RY?raQ2,^!0M,vk21YM/QpT+0x5SR]B74!fA5%m"Cr&
uK%dA9HbQ7KR^-$PWla[6d,EW@
ED#:Y#oe?Di7lA*O==^%l#*93JHgLKKk.D/blKz(W(W:xiXK$MK/(lveIwd$aW&RnccS>$80&XD:6x{?M4[Als#M"/Q_-UC-Fcy@b5ck%DU&TYp5IQ#`IfvvFVW`/jO3Q3N.=g7X$GM-E
Pp)&SDUeE*mp:S/&MuN1=l,G]um!@K
tm0A?]aP>z4k!Y>s;{N-wh^~qH<Xc#]|)IuC/_Me+YDaEU!AoMWEoZlRgW-BkrO_!;*k6SKq>5K.:!lg(.]~0$AlY)gaLz.0NtVkG1YcWv[>Qwwm33^yI5z$t)Wg6jmF=9SNwrJ>tIL$*ol#-U1|9xlW8lsY+/(daj5-"~YB>WM2ZA1wZXWV$Lu9iA6j:4T+@K=IZecQf|O3Rt`Ic+3$vPuOK|EXcrkm"?!:?uk@E<WB"F4o88pD>Q(CfE9
ca*82#vJ${1
?@XtFI:8yB[
]==c-hS5`9o{Dty5f9pd)3leBSZC/Q;;L?:A^nxY]Zum2{)5H,0T:UAU/<05+qQi+/iDpi#8*6]<&Zb3,)YLGD$T_TvLN|_G3?uV,(9Is?s}.va+j%.OS:,Ie3dzt"Kxt@Nqjja4j(/54*Hp_X?3i?^<E-<JM7A0teJkR+F/B/:ePir6a}
/;K:`VQYoLo</`mfq2uHyB!e=,/l$NDG|H2SN6SyR,]dLB#IWD;d2=),(;-F&fK%4#/mdY;Vk;=-LiH?z66x6>a<JRp&t45[.`d=OQee~ww:#VdeO^78~wNi(3qy8Et1"q2$~*T,E_PSIW|"7R%gzo44DZ!(REbR
=|5?=7D.$_R?Y:]i=R%<8C/*ThT@:Kn(Zh:}1
5n/">HiCX1s#%)mET6o
2bMGi1jUwW66a[9tX1;Nxl5SFwyPu-cp`#kU3i0$SIITl-+PJ#TXp0K&["#6r%O;;-5N/SHgpv2+fRpUVLEGcz1gkl<le]4%.gh*6~J%nmRgx00g!j!jhTDe>m8JbeS1V}FOKdM#Ae)dk#b#@ipn:@`M0B=5h/$`P7aF5kCI(IN?6>g[-?Q_)3]}W|D`s^cwE%_@`Uf}Jc3J$+v=H>3+QAns/,_^ADa
w"_aZuFd3%X#f@9-xr-wx3/t3N<|hA%r6=voga_cPGo<%x/Y$^/ms{(L=LQ8D_&@[XL2RO,7<_O2?er^NFFKj4=L<_H$yMIA;sIWN&l/W2?RDN!GZ#2ore5TI;=23&b?u<^{0wW!0H^b_/L1W>`WGRk17059[6&o-!9n=`%1Ym"hPttL74V`b`e"ZE39?kb1`G<cR,4L1sqjl/vLA}2_km=$iD.<(Kx@8>vZt<qyo2DXXy[~G{YGN_D/&pdMdUCVUrFlIJ7;]?wg<s&<6Z@[,[IY;HTff}vj
B_rR&r7S@3-N;Z5D_#[tW/k4(:(aRPbK0F4Y#.g?P*5Ff_5rd>P_^&th@TXHjO[i}Xc-Dn#(#FjRFsj:c_,e*b>RCW
9#^WgKTda<QKLVWU7}O+M~4
QB>mUn
l5g)Q>g3qIpceaGT%&s=H(6YbFv;F)D9a4Rh{Nxc,TC8ocmxb1YC=o(=6UhtzAyX}=^/T1`o}6C-m]_X+nC];=LnPN~U;wtoAtayl"&`9u12
>NBmrg]T2}VZgB4&Pj4.Oq[!QO%F_>yR4|]v*|HyHMoS:N7<u)?,;e@p7<k=AHNV]C3{H39fz($X';case'no':return'#ZuL%bOpm,z0T=Yy)8mF6QI^NY0tF^H9c[Gr7L%,LNFASr3<3ZJO**9u:pMu~BxbqJSjaE]
;.}8ag_vwkK<Il{IchMh[m^3dMOBQ_uCUZH(+#Wb%x]Vd$}_]4pHqMAj;;}X79q*xXyfJuwpu
Q;ep"8}GFDsv{6X.+4M$]dJAHSMpFjl:4S*5v=?`:OMSKvV2OM7FXiR0H0?Iye^+q,EN$y|[Af.=m6K-ZSgbO0,H3`{CSvx"Z.z<1%8tk/U?{47XHf$<w;]h=/kaRt_.qqmmFSHP@!4>Gp;4eEE#S0[U&ARiayF=_&dwYsL9hTigOu+$ZkW6E`x=uu)PlUM&f++ml>ts6YDB/wh%w"Cv:eOJl.R%63Qdc_U_YvZV!.J;Up7G^9VF|t9LKwogZ#%YKd)$!;B+ssLFLGp.W5M<mr
6[caOt_7U9+1V%qRre4hLscz[2[3N,rY?WOY8n/![z3U4]`s-`4l
<"$lXaNVdl^X;9qS%Y
foW4evN?C_ql-mda*J,F3`e2Gnr?S
CG;wT{cyTU&njRjs
Fv<gYZDlqYl&9^!0$/dh?!Y*RGRh{HL[OMhM-a:mqT|XiD5x.Gv1O]/j
n_ig3M)d-08LEA$aQc+O)D/Sq5G+j[n4kq<$&5z(I"<V+$HPh6CkYIX$tUgU2ySujG%HJTk"Iu,}Otj/;$<(5.UZ5r,;2o6fdhhc",^-J{&d`7IUD:t0;GS!Jh8D/Q05:Z0ZYwBWasU*$C%}ov#)(y,^FPn-"2D>"Rx3ktN(x{f+8UV$
M`tW)6`c}74)v?1FmYz`O$
Ju[L1-NPf+aas1$OY!EKxUy)xga6@JbldclgwI2nTnPb<8(ko~9pHof<LYSuZ3=a*%h"$YTWR(05JQl1[+;psbX_ALgU[Sa*1]7a:x!8utpo1AhdWf*+Sl/tcYbq>V_tyW052-4Cd|I7v|kxk#7dx6vHhC3UKzw~;gA*nWa5CGxZ!MM+dcki?oc:_bO(KL)}!UmKgLufXft@V0Rihl2-oM,jLP0O=37<7~snay@|doox^X,{^,&GZq:KuD?4P,5S%&"t)[b"0`^i3c!=l#ft*AOit1GL[BN8$q,.:d9p=hEQYQNh+&%E@:aNf
vwrQIR
HbIcNiY5/e?Pq_y)]6C4)_cXryHLo*UaO8OQ&JP
c%?+/rwe3`fO%DwB^jnvFW8+*W2IeP//ca;iLgj+^w61g*u]Geuteu$k0w-Ddl9xl(u75QA!CSp[/<c(}B,jon-+-6aA?q/:y]H@~7uTm3fUDh)_|i?NUAmab4(2X]VL!*XpVNg$|&h$?j,gaMu5SKGd&a(,$)-bc2?YOb{:}U]L6hq(cShA(3gRT2KF/Wjdtae6_M+EEV[k?#}ZO-;Gf-449$+HyPuE&`KpL?^v#S-t)91R-k_$NN|_b+m-17nrYf[=}E.9lJwn?9).4eudJBCFUj"AT)7[=;Pa[JBp@J~CHsA,"g"ejFILrG3<heU_")U)Cs&4;PV#HIl`6JA-7D"?g>F+[[+-UX3-Coyx`l|<SFCbTB]EsWP1]mrH^[P;~.kA]Xtrf;B=#[J52`d&ERQ9"09oeJ4!,)s`5bav/EMriu%-Xr@If!YLGn=V=F."2q`%JM#7a
E6FO}x~1k;p949Z;Ps2la*tYYHu?oB^d
T$Lr8x1HYqml?hhkTsODG3TLrXUs1f8k&|>{g1_q[(9ku,pDI/f/^RAG1qRIW1CHby_K2"O^t?JJ;AOa+w=jIx;lV<9vsb%*!be3f"
~s(`.L~a!.?2P1lOqv%aI!@,-)[,X`j;r!MS?Cz91r2Z~JjrK:(T!;7%g9=#M(qmmgeHTr,v9[]&R[k7"!cyQ+"TRAI8kq
*"P6tI.O=NNQTx^w[<xet6l(B4_NiM<R&;C[2y?gA[rjmNLEm_MLNO#}I&yDOWn[bt^YxP(dTKcu9_
?q/Its]^ki375@,RcS?<^lRB
6(jK7t`nsoUp>k3
5;6@VY<kJ&UTUrN33(-qk9Vzk=0SM{bE*Tx;fjw`hg3]IbeF%+hGqH4k+I3x7$EfcLz&vZ<,I$F2Zp
_Q<#1a2DyBTv69VY[L/MH75")*$@(NJj@ExxgP{UYZbj7#odSRl#S;X(8yfHKoenDtw;<WxGOFnHpPuT}`qy}QwbBTv"1<{:qc!X+u2pAr:>6<,D1LT2}w600?*$fj#xA@26da
Eka6s!+`yD`.A
^t`|na2lp;j:T,iXCUMG&:;,x]Gh!.)5H0=/s13(k#N#?3ca]RnRU:8TD9%(vO]7$
Q"Q5oSJVwF.u%?OS9o`S&.h<w8ZeS$BGbn3=9&HA9
flN]/Gx1?]N;pi"?T;0)>VA[W4eH(}NSx<sH#2FsMpp}s-,K2jlZS(2^Gl<^`f4WVc6v_rH:0SK"_^:Lw
3U[wPhw2AZl}_wV@-4.sNJYs,H$0D9W;jJW>%8+WSfp"%^hYp&i:v]!g;$@nhd1c#@@3DCa+%}VRx/%u5+e#HTAksSV%"3ja!.Qv)CX!vJU4=yd/:"5mCC>4UGr#TJ"t?1hGUe_cz%5eHxZ(E,e*tbS1KPVyN4%?sc@(..xRe$E/g:sJ<~2[U-l=J~nO8m(A$ptNWmCC24(G;ExwNAg~w+6B;0@94kjhq(@B!K"O@&
2jj2/ks]i=hZl"1EB5
]|/lqr22u
vFF~V55A)!&iXF=$CY.ONuu{NG_m=aAKb*yy]pu
1GLP+mX{AGxB-XX*DO_ObefS=}k2v7J{Wn,JA>(9-Hymib.[Pm!t`EfEB*H~xR$$T?13(MG[(,i>Y3dR0~4D!?j4ZsOp*D1.""eaCJOHG28+UK^r3/1zhb(XdwSWG9j$ATHba`[-V*U7A5c)+4TH-Mf(b~4*Zpbz>%fZtvPQ[^Ov^X8rh
Q264/aai/UqQ9_1_Z9R3PMvU3K6"rJ3T]Y`ANP;/J#=QsGfv/
.qD6K`-QN/>k6?m1A9(pMw*sW1QqH{E,pV:k5MSF%<9Wvcv3n6AfqQ;`Wo?u0Uruec((bOsyO3>g@Gmdj=J9i1S6rBUT.ip+T&fA.
Ji)rZCxt9}CyA6u~1:j0:8J"nNyS:LCkB17.$HqC*}wsKsBE
B,=
>eJ-4y1;)i!1$CqVAbRE"N%roK{c$%*s@THqmkx70]D:PK<ljFfpXdN2b
@";"gsd#[]]>?_()om!Df_ibxN0ie5QRR`/$wLh3w!Oj#*8-IvuwHKroqF]`a%!RJZbiK;`%T)u.}>!;9@;o!Kz?#KF19@GaiZk2%Nh6ojCu6CZW?+)]Pr&r_&Ta`t^!]8jGK#IP
FhPXsT"Gt)AxP|m2,/UC765SxM.^T<xb#/Zvz%8.5}#T&%cBS0+Yn1UsQ-6|55aK^V&^n(^pkDqS0nM
.tNTWR9WV(9uG=f9-_cF]U`m-2jAY1,7=B8}2>CLP{_<!734-DhDRyw6@M%ve*>vc^T^y)9174D9i{9jwz(9Dy>{S+.zVX89jfLKhd*%K*QiJOAgXZF?T,>rDAhdo3s$iF#(dVhS;":;k5RAKlX7lr8
7z.:(T8]c/$Yy:LCY]ySSMrNwmB,8<N*pU?n`qvAwHUnQW#Y2lq@"`Q6j.I/-D$;iSGU;WwRtn?M+B_j${Vd8u?o30On"d_Bsz,{`L]anI/:ue+)cil#[![,42")]v&LyXuGE-
LPA-PED<Zx~Esol%jFGw"5>P/^rW,dM&BN8E*4m1X"bb0fR/T$ty
D#2(Lf69CeGVBEmv=BF16sMrOR1zQ"a@2w`hI0+&u;[@).Rd!$,Q:Ts_"EW@A}=`$;+j?h.t3F-g]j+rLm!41EjQQ`fIK_S_o?PSIGI$:y&xElDpcQTl;w^L[_AhE2F:_bi|U29v){P|;QJ$FHwL)3](oe?SE}^]_CrmJkU9(#g<Qt?1`wJ%?MEzP[:rvLA8n6(-J01lEsms(kF#/Q8O-HU%e(rb!A$`UJ6=W]-#o%-6/<dH*QI3[M?y1
(&,"3gOW.$.iLl]LUy[t_9/2vy;G_n@FPYa3U|rF![)i*An>1:1$5?b1[lS(&@KSSOf33:^si8PzV21+!m@
D5)UpE;N0nsw>9sU/xd_Um:ON]cnDR:UPxQgxotPc}LK5nWuJr^`qqk=4zW/w=s,tX';case'pl':return'$]^AUbOp-*60TS
KIp>$[rw[B,6>j
2=/5Sd-650n/_O]OY18#M-}w58<js$`I6N{(oynZ(X1ujK6)+^peh_P(bto/5sRVsv7y?sRBQc[rHi-Ix^Q!NrmELqm99`kH8&,8x4{.UH>hYa
EU_rP?0-W0<0odAz;eTg3U7X23w
s2?uy]4{7+0|_;HJH&m~q@C^nXrH1}&aAUs$Hzll;_T/D{>Au(v%Ycy3W3St]#qCqPKm+cX9t4KkjS^UgZ<Fb~R6M.GeQ}BD&%pX.r6uflsj?cE>G8]fEox:wVyVN6?URR5wSk>sA,)4WqT..{j@Rb_;r=qeuo]^dfOyIu>CLOV,W8Os,.nx
b1=!y=/n:NU)qP/NSSe0k@dIRG@Ht5J_gU(7JbY]Sd<Gl!.l"7Z6uhjgk8DtqOB3P)vq>"#y;Gkl$o--1!@TE^;tmVdDhrSKq*lF7AcT7bmlEWi<!a`;~yDU1BT^
_Xo(:"R
5
?idME+#{UR1E%
9P74(3q>
[D6]It[E=5ct5`(Mjvkt>m0;ZmgYorS+3v6pDNz=xX
fL9a9xQ_Vy65mAanW.>Q/TrGOwmeVGncyV">Ja![XS9t^2wNhQ+J7e.qlqc[YLlOtBnQ_DQ-mP5kG;x{gMs}F"#!&k[V[sDGEl@U9CR-&mF)5RPh3{(E9j%$mnD9n=2u=5cx722
p3Y96{UKYmFFBE4$J_#!uDxpkR*RP2smn_drqYY,q7Dj"{ocrlPtc)=UMK_o=*$?TuT.Qc4In0H?SnX?!fYj3Xu5k&g~0lI)z&sjp.!uD=u9bkfP-B(HDR=n!ekZ_B
7p7Pcv_Y+crK^p&lwiS#aTFS[7s*_b@Lk3~3[Q@f)bt
|mSx__T>s#Kq>5oAG?LHJ*:#MCbhekDG[m9uDc?Jeq:CKSWJzoSkqeOe1Eck]beL}xp#LaK9:8Kp?o;FD2xK}n{4h]QCE:+jh-vwHq>vTk^Lal7vca3e?$?V.yro.4x],LZ64(T:^mf^mJ-.8dBh?8P7A^3Yj5gx0dC@Z_"Rxe|d-I
f,qq^Jw6WagfEy)BQQ>D,fkRmXYtPA7gE2n?-(/0xI^H$|)jj"h^fau+c*1BZ0)z_z_MdjN|RUPxub?yC<0(dJMfi/9wIJ+$/pUa;;T?7gR20}BV=L-f8
BwoA=*]ffsInCz35CGMm.8*:c,t#mkny4vWyx!HaS=sa*VSKhO8|xdiJ:q>hJUn$4u?dOwk5x
<6:+$=u/q]2oJ{@JCL`:$xyUF;7OZiPzdq=yS7qxA8k_2KROju:Ir[L`u^yb`E#N^n1PbNkpdi8JeoaFLYRv9WM^>VTo!TRe6f:g>~*<1tf~=y9#]l8pZ2dp^s@r.u0uh(Q>89GCMQ^h..vG,&i^ihf:uqkVBvIyijv#`Uo{q`z(%nfbK5i;[}JT<
k?7cx*;YM8)q-;64o@juk{VGucSSs@Kdw$<;K~s)bm(&6gl`!!i@
Af+%kvWf_B>3K+Com!!V^h[(Sf)SMp~5jYq`SYu2#k5?H^Nf8R~u.ou<M/iPml2Qzm@eS5v`YM2)K#qU
dkn"AcBf[t!#l~R7pN4w8]nBo{5:GZhoa)<jUh^hf`)|a$I6a*]
7QuOuLcs2rkH"~
#,jF`AzL<65iaMQDzcfLsij4.pcCd8wd7x!?mqPj^t|QUSO:=ahRE(?tm3!<#GhpofS0M$ok,g]Z
*agDW|/S?2k;Dam0Z>/]F`e-W7PL"lRrK!n8b$pOX[)v$2-QD|sB#a,Y?v`vA+EN@%^<-"
4gIp8L"fR%%_%?_9{ga^X88g;Jw4q=a
H8.!,g-@>ov^f2ar6TJKv`ywJqKdmYRAA]n9:7l!:PirYpaE"O_XqNXf]LD"xJFI5L!,fYY4qhRy@kV6h0hXw0!h0K4T@n&(Ovka4/dOVVrZ"w9:?3S/%(4St6F7E3C><Q1dYqd-W3AgL21@N<I1bt&jmq7RoP~Bw)2G#^^KGccK"p.8%AAOx7jdw+>$JSxD,;"qhmd*N+$Tqihy=KahZ%tVkb/nW#=)nhM@^gcU&6/#Kc=!5/oF29TQssKP{bV`#!!RNLy9&_M0viTw66
pPhPmk>nIvN<@ZxsUp77S63ZPhr]Cp7mTcX+ibc6s`SH//w2ZX5USgmmGl@y:AG*bznhWO5}fFc=U|-YLET
"pywBt?BZMV#pQ9(oOZ;jCZdC+l`m$&uH>pl;fJVZr$R0TT9,|[<#V.hFm?DDZq8v)"Z"
F-$(!x4"s>1"NFE>5:W8StV<cnynwmd~;P-ELDH5CF.7Z2j][UWe_A4k18t^P0H+(r9,g9;]XP"/p@1(e75gA!OZw}?vZ~Hy"{J:GR
>D];GXd)W:i)kmJI&%(Pfi@KGXOe%!Y#si_bU!y#yT5:Wu9XvBDizsk/qwS4s>F.JAD(B9Bz&^$UaZ5PCY91e1?k3bkOAMiwa)=RNJ.W<L`Oxp`.QiqhmDR7<`)9A!w?]z)4F;n,ci(nm`M^|E#VEZyOg?cX*Sh<6w|E*3A%/;^.z)9&],b$1^MHWvh*|wO%TmSP<UX1M$Mp<JtUnC=/yT8bQ9~9:Z&Z0v`sAD<)l*S8!C<KNv.Ix,3HD]hB
CCt>"NmE
};XRUc^Q&O~AI)tbv4G(#`]]TYMog(=R^<@?OBA/PNf6LU&*PBp)9%&V<p9.r3;Vf;z#j
;kJblfpyi>;#JUy
.5&TS#:1cj1^0E$%~uQ!l;9L-sd<J+37|kq_.rrTz8,P
&;`(GZE?(%m
;,9(kbE*Tz-m5)OKF=[jN`vVN&Ch1E]$;KF"kVGQ*&S|RkW[MULrfH.
-Q.)1Xy*kJ$5-hWUeKNz?yfVwuwc"6N^uLbFZBh=m:t"?N!-eV?wHS48JJF%Zv!!;m,[u-$-1)8$3~t9o:U<r]e]AF.bwquC:o+8Lc#UWd6`:Z_E<4-,0/&8E_!UW]_l-XDB.X$0i5%$#y+CnL&>z)1cpy9pEOPDsJ5Sxu*qlS,O
AC%?)%XieggY}!#ho2@OB/qW{1hggnK
5RH,$/0P7(*sm0%ih)RPHmC&CEO;BCQ)%b,[K7_xO$i.YbO-khp%}6.hJUs>~X;rLsr]}VB0X%VZl*+(4>S<rMR*(?Imf;-oGf_)qccJL9cD<J"
I6*usUHnQBhG$.[ABmr9jf{GQvJbAxcvQy7JNNHe@.@uogQin>;agy0cAH#csFfd&vUm,tF4d;zi,jPP{<a2?LR.FLGoQ>1JMS(AwZp3ZMCj/qUhZ65U^7iQ6Im:-kOT,v,34dm+OI|eYA#X=#F>+hXk1GXe%9O27^$m9*#umwg>Y3lZE@EVW,CUz2AkEU$$etpFP`r;IBc)=lO(4KAt|gW
L?"pyA7)8nOA$dnSDKyVf,uC0-n)q0((:]SybkbuQ`H^G*(O1D@9sV7b~Kf6(F`kVgZEDZ.tgrm8h$}17L:sNHnJ5x.hdZMg[CjVz;oIlFpI!ea/fX(JY^8oi83D0pA)dDEHy:sLg?FYx&14#/Eb<S$6|@cVX^mXER#s:?1knG65Xitlst+/X0&.6bqm0@J$P?uuJSkFtGr#beu;!^".;MuB,&GOXKhc~I3Pk?.iltK]$jK2>6WH0g@E5d"6krt(2!J(eT"%/@o.cf{0-I=L~0sx/>Sk;()((o7)"d&8qM8Y&&)c#e_&-msxL0qB:y%Zq;T*4e6DR^_C$I)N>kkQhZgJfaJRZMT(#%]Y0Hh%4x9bWC9,J[EG&FY8l9TA%jlKkZq/Ib$uiUXrMT#v+`g1#`qZ[:U>-7Zr1j9dnert|uwfSl]Ol/bt;nvTs2tCm(+e09nHIEuhK.7V!TY9>)dnw8(I+s
+@Z@%#;}0Fr>${GeaNObkRg_2ZLFTYLpRUaxT34+Av-lECMMH#<[5Q.4VrMTH;RnAx1HAUGe9O26P75$Q`uA22%1heJ<F?a>&o<|O!5rT..1ci]wAK(]Q<nm!,;lP!bJ6%0Hl/!C0*qD0*sNL=
!41b86+7$a$0=6ZM2-b_WH#JRn>Zs^_OaCy2xu
ZWU%j.I9,J1`$t5t:0Yq%/Igh^_UGM:@^,@RB"!1%EW_=<//x?Q-m
MiQ}Hz:clu9Mo$l$[ih-=9cmu,S5ZNAyW9sS/L)@P+7Xm0-p!4_-8K+&y{(-p_e~_aR>>~Z#kOje-o1.=SyG=[
sn1v:GQ8k[`f}:f6JN0X&FDJ+Ra.]%(.DFXu<*/ck/WMc/L]NT)6..<E1s^u64&_Gw73PVs%<c7HGE~MSm^,qa3Xe-jEj^g4|fOs`*j?7uof|Y[?iSrrsY{B1-nD+F)]MqP:GFY#AelC+mnwNIB"?<x6flbWpk{YPON.0^OBr/o*fmG(K0Y.uZ$=rM_d<WR<zv55.Jm^k2;[2WLkarY+e$tghy%URZN9NY:x8)Z=rSS)=Bl7%=&f2w(>{-rq#>F)%+uRR!"&?]fP%Ed5eW)sv=w&F9,BZ5Be)W7v{R,3&7ed:&ru`t/^3^pZ_Fh;.]u8YsP[pR@k*d,mTI/x^wNviYJ?5vmG"eg5{nw(#CFHxiS7NT,<hxD^)(w4#6U_B2<-+yfvt$
743_PXw29?)ky(b4O]bWW_`81xgS3n:%R&JJk-]nQ`[?)Tgt%CDdf",}:UIR@rD{a[Kw!!o8Pm5
_Dlju5Z[FD*w=JyIj4O4BH
D#eVZB4X2f)fHZ5lk>4/Oyn[R0neek=6<$Q0t>=pk7NW(ZM#R/c8k9d1+qmU"c&GWw#_kL`^d80R$Pb)(@Q#&D8T!CkrzYzs"x!$&f"Y<mdKn78:F=6W*aU<l1uyO:3``kan2I%_OBIqqUs(3L+^qu#^3sN^oQWt[a=hdX=-/J,W?nUyf.B';case'pt-BR':return'#]^FD7npM,z?z"0ys8-
X>Ph*#>G]8W)&_vMXUBRzXJdp-lS~0<ifv"xu*`K~*0"I.(s4qR
>vHyYJ;fG-!ofEA_^@&s2B
^Ccl_Yuw$Q:,>7e4>5GgvDqq-wM1LlhK#*.f13s
CXwOyaETg&g9`%`6"nLEmGujO4&%eOP1:VXQ1:&"WrEtfC1obh&%@=Qok:vd+M/]`OUiAgs-!X7t?T%.>mG
TMybkJ-"L_rFT6a7S0:;;;SE[`xNK5LWAo@W/o
5gi[,nlYFA]6bs5U
mmqM;D%_^8%c+$cp$@`mq;%bBlli7woK4QuLN<Rs@nq&?mQ:+[NzYgVvq]F_f!NS@{rt^?tlM5D
ANePYVEB16S[0csj!aap!WF9P)Vwu}#3R^>kJ`T`01a,a)
n#oK3SWSn++x8rVTZG[n=h>#Z+6ekd%gW;2m_-[kOOO,I@4L=k3XqW6mWG`Xfkg_JOssCy{FGc0R~0iNNum^/^?1W:~q*3!OB./;kkeacJ+Z3j{%^<N)%M*lz.,aIIlW6H~dG&L78]KMfOr,X!4TG^Emaj{!Jucl="6yJX[x_0*uZ4l+);a6v%x7r-/GH0F`l1zC+0z&~hTK#E8@.UYd=UC21i?H>W{]S:oG/0{d)EjDoNq#UpfBI_H$@&%pciek]5q<)]|C^!dyGt4tu"!^CrXrs?R;poHa>ZW5}!F*>CoHK4;&x+9N-DTW$W7wuAN[c*:^+>"Jx<~U|BZl5=qomkz/bWumE60"pa<]~Ydnc_Y,RrBZ39#kzt^[1^=,,>I,O@_Fa=7V:ORl/1%,vBC>qa>2JK{/:DD"Uj9nswInVx`>ZZZ]r4rvzCO
$[sL>o}=Y7x_VFNDl
~lECNY+uRX8QgIy,YLJ^yUL6[B>PgR*Yvx9hV2
tUCEtl129|6]3)MXHWN;p?PLA/G!R~8[l7kZ>vMFKj@!SH&rm!"PiK<&Vu2{1b*5eh,Tvm:QViY.G0wOX?:EwEj0jO/{39we7q&BE%tK<qD.BdX%:g;zx}_`%ZEB@1)9HqWD_=2w&5Z>rlBE-sO/?a0SV@/bB{.y3TUbtkr9
+G5Pd:^UEDhEqj/5RL%iE??Hlp.9ssQk<%OfuU^TQ5+=rL
Up13&E4c:GcWSk38=]I;3TUxGak&6K]`
tH?D5L{Nx9I8F2Yi=N{<el,B^M`u.[Aa~muN2B:seB;o9+0HK=GTf&%H`iS5-oGXFyp&)eYLG6t%qkM>N"4i.cZa{($iUO5p7537.NxLSVMvul=rfg*U_?$u-TbXbkCrO`yu7[wh_0(CoPX11j.LX%Fh,K([AM=!YiJqF[/Q2=w]DQk;@Xm3*wY8k97INBaKv/)?7;z?u>vt+DCZQm@rm%>OOH<(c/OLwnv:{n<wPXey
0sAc6d=}i-=4PViTF1&B2}OB;FaeE{?~_>sYhuJ
i2!d)j$sQ`8kxg$9jeB%aT!W4}y1GdYX+FH/C(rTC5^}]iB#a7T<hT8{;TrcuNU9A2q##A5aZ.`lQF_3Vs>(ORES=Mop<f>)?[N*aYHG>7rT>xFoXJ#j8LHFUbZhkReI$D7iE@5
bk:`UC1o9/jpP=!zPxl8hJ-519B0Xh.xqx:;?NuTF
sgv)N]Av58$_/I+$l`:Kx@*^p*GZAm?Z#.mMl*P"Np=h[hEv5VqS.9s&PkMf!@8Djnv[z&jcc2LcLwYW9FTM%L9ON(Cl59.6.s<}h#$vj3&HHJ#.Q*m@VnT;"0k0EgH^1?EBM=5,5k>cK?VV::BS,HJr9[o,i83a6D6a"Pg_WMl`Do&v.2G~yxUw:%H`D.mdXz!95~@_P,n@uk<HA0G,7+ak$awafmKbO(b`So-J-Xb&]BOp^A&b6U^TOkSi.=W7b`wX>!g-&JRzr:+L2m-{%,;el
Pv*SoB%3L}2LQn&ap+
E8GyVs-`R*ta!*b,@[H=F-|gmxTeWH>OfNIINp%XCw3N5*Y6j"Uf_qOq.miotQ9Y/J`8=`)0@QkOEvv7]8$jbPU@I<b@)+|"`o!Dk)wdYfcx_-xDsX1wVFyGqN_1
S~o"!lTFNV<eGz%4Q"U[[@bT<VVKWmb(jxAHo)?@gX7jKVw6CBWJ9N1
S?%Pp{Y%oo1,BA2^w"&~vmHhCNB-6!6qC=2NT8l7VMD}xg7>"_-qk}vTC/$E:"W>BfS0BGjX07Ax^f^A
mcF)P96lm_7`bkgcIBe
mK*MgXY*PO2!|UZ(C_xCjvTGg8$%HpCY:C5B"sedBQvVuAAtJ^6nt-&)&tsAg*|s9H9*8S|^#*NLm@9QF+|=yk_B-S]PMobNJfQf5J@(/R]bD@bSG.-F!vRZUGN!u6ubv9S6h@/#lKA6#]avxK>)N_"Lnx}Q80PjKV@O#d@6H:]*wf1Z5yIlM`QR.7<fSDR,o0qIg;gIgQ2vkgcS973tLQJhcdip&r:xAkztKA2R)YNV.<b?E%>:
.AS-D?PcA$A{ijVC-Fkd4OJTvLqMMH"5f}#)wAAJc7-+=r49<z,%m7+.B7n},PWN$+,bX~dz(,p,gw9`2GA3%wgSpdW).hd@IkNPEA2}/{DiJ_!ZfeZ`i~F}ux%).$,(*8?YOJt#>k>rx5H+UU_Eg<)gg".]]aNNC^ydjUC|y/Zn`B8~W|UE-F:{trxi]PD];U>1T|Hs#5.J5[<!p,]
%fL%:qM9Le,$<.i0t_`rs5v:=sg:Xa-Om~E-3i`NS
lN894TN^9qqO2f[m!@8}lV9H85j4]N9(="!&Zla2g>6?FvW3@^uuUOdT:@ESMO^eCZ9CiYgt/pW@ruO8Bp(|2l_IZ0?DGA388,ZPh5$e%4HT^Zsi_<pf+Bg7
]Q(6tGk<pIFy_M^LC:[W2=gV([[h[w`PJhn_H:
Wt._WY-EgzSeEfIkdf>p#_](q3X{s_xd0a9k`>)mRZna+~y@6A[}8|@670kU.8odQ+Tn;r><aOQ0#QNoEgS=)XOv6?ZvkasuWNn!#w/bTRb;.H!34Kq$yYkP=<;XUe:Y>7F~a.82KIQVkG"qJJ>)E|j
nqAR;9u7Qco]lof@F1.zUuQ4vw
]?mLF]Y+B[Q>X9u]{:=Jt:GwDCm.Q>jx32UQ<5!SMjl2nu~UAbBN8
QFjx!AdsG43EM7J[~mt_$7(*.?"^[`^1D92o~/mZ?F;TAVc9A5L_PqY:2<;%Xj<N)Z3AomJ+oCn>siAM$i(XRJ@^-dq&-$/@CEY;GaimVp2O6@I095k^4
iadOE0<EnV0ssA61xq9iMS(Z],Ur|.-w2X,(^k{b`l^TqP4[h>Zx_XcRdDK:
ln%yW()~Zs,1u?>M6AY"Eh;40s5-?bh6adESg_jx^P9,niS7Fr5[mBZ0<WThXS]`O?<SiVRd3;S9>Xw7`W${m%17XD]AqX1XD[J&AC;qO|YOogabQIaL5G-Vd
mccH[(Y6EIYt@4f`Qw,%bPSA
[LPL<LV"p@y=O<m-Rt(XvEWu7q"t$`Ywo&z`/ZHLHC"0:XQ#0<vD%b3YDQCKSeY(M.Rxm`9N>e!4B>XHh
oq
0+NKK$(O+UWzl{)?wEv4_2^.8&wv&Ys@ovN)qXdNYvWWd%6"X<TeZJVbBXV=K,n$ZYkRik,,ju.Dp#<8mr-asZwT;q7UB;-QVJL$B++@^X7++-_Wt4)Hu(oz1(xBTV!{V-JnfHA)k}66$t"=O)Gp-Q%(mI@c3;9]lwi008;L4)HUYB:t*@d;8D!MtNfX>
WA,@q6iDSMQU-Ky!KaT$QdfGZ1nVr8r*2YD@o|EIo_02V=51RC:^D9y((>XVw"aVKnskGwp!$%U7g=MPX
x}sm=47pMm4W=b9FROyFQ-)Stk!KTaXw_<+zqN23"t%NSWmvbo!AJ*>50w5CX(k7]@3yc,l|`;6E;>Efj|)rlAr#$kW|f!,l.-o)w6?j!7p8I~wfd{yD0_H5e/TuMdgh;gis<ytNw9xfDDA+y9,
7&F`z!LA^U
Z+iFn2R(w3XUOr>W.3biY9QRGFz$*W0,J2
m;x5Lj(c(~.o"]UCE2atB)UBF-3p/xX+8Gbu&RBTO%m4^Y211p*d]spAL#&E(1o!j/1.e!k{m8>MT%`Td$0F,=_">/)_#*m*$jU9@621VT&R8`iC4$#a!&8g:qyxJPW0ai.Ao!$cnLh6$QoM=]en7<QS:<1kh`a`Va)z()EFYLy@(0`|:F#&IF3
PK)gcFgYrJ,:XL%6&49Loz&)&:L2-{uhKZX&FPv
)d:jR$Jkuj+<%l;nr{`Ly3LBlR%Lt{6E#^]Sk[F/jpx{t{O7yG!:ybw"2"9YGBf
g=WYWq^gR-Om,z-|YQPI#1WY:?%jc4_@i,]nL-(o7%437Cw>i+fF&!tq`G:9_l#4+0rKF;u=!.hwxL]Rxc7zJos+fQy$O`^r0,@$>M3&4?:[g)h&rSDQF{Cf^UoC';case'pt':return'!`GKs6LD)*60
Y!K>)#IaI&^N1YkB8iO}Qeb=)*`HRlg]&Fp]3WEp$(BF"^f&ip"%5|5"Z/&"a9d%<+cr-+h|Gl+6c0](?Cz!h?pE4!7Y5
91ZXp`G!o0r4r?*eXg(WdyQWAA0uwMyJRwYIo1E^0g::&cB8i%qx!-!R
!#w6Z_:B&/68q4JZJtZB6d5@Bl/
Dml5nB{)aD-q;YN$1IK^697kn=0UO@{m]
{ytl:m*:YyqX^x5WI_l2C]tq.GGnxg}UcU1U$%o0}.H#8;Go]XB)ORGqTy1DpmTOA$j*e<5W^A,j{^Yr9v
FY??I8k2pRG&_kE(Q#Is.[&43onx@hpVAbF`Dl8Qb|H-tzcFucl8y{FGpk9I$q`}LEX
D
2n7f*!!4^b=T[Oy5pfy/QkYx?8Qo2j7!nlfGlb[AOu:#SPNQ&U/vY~GwK<Uq,Aeah_my+"5v9TJevvIng7ie*xBch2lzeE;I1o?`Jv0wJc1IUYlOL"bb*Ji`F~F,/0V!`k<:5yIb9wcI/:BHh5DRV~
,CK;6*nYA!+H]r]C07.x.N;oH1/0ekiV&*&BX*&G#i-DHCR08VNk6+2)IY/K`mxdtF^Z`(afuid:[$&/]EA0ZG~`B@D-#c:lM;T2i@oHt$mCXd08P]GB1=C>QSoU;[rW?2fpPtxlurx1Q8l/P.25ll<uA/IG;
L)Qp8GN;BMtr|Eu!t*t#PMESsi&1-%+gR@^:xgeiQ`.X.MG*-
8Q^_l?FXK[AmG#l+rCq-(ca>
IN;]bdHDl.bRqw[dCv>PS6p@6R5veAC@j}/D$`s"w`2t-XfY&QH;Qi4]E]Rp"owOZgXzBK3lbG?U7Zoa2c^]c8p1iG<n=H=u/GW.c;OOpjGr:Rrf5Wxkdvsy>>[Yj_i}(gHc6C/egqXDqi
=6Fi)>oiKz"AO9Z.sWmi$-fy:hM`#93u4w=dScdJKYR8!)6/vK+/--0mfVlbK/a7|pg>4y>VBQ7mB+}__Z]voaOuUWTJWlZ#eY4$)v|3|=k4M$AF&@V!WxkEXX:u0SC/0dz9OL+7~,^r>F)tS^pTYBpgT;GCiTKD>c#YpG/nxHaUu)Jf2/aB
A=Gsi*p]G8wUwFfe5)-HF{@K8n_%W&iFt.I?0@BHX"S?_#_CUfbDDIl1V:YKvn1)7
]&mkjKU-R?(0K9aZWgun&ALh_NtZWDgFI;omDrI0>H`A%Bf>D%:(cSfdB
K?0YpUN7$_4%%+Dc=:oR$K##COQU?UghI:cGV1Fj-h$Ldh2mZ$P;$lCTMc,56M=P?u,o:i-Swov}o_E05r:0g>3n7WwnQJt7&ax`n3TE,]fe
dS+RJH_.`S,q%HOy!.3sfr*-~Ro(c!dobm<;M![r*FVo^ix/&T""xc7?qb!gMZ7!H+Cb/JtCE*Ex0m,d{aDe3nIj{9:rMk@MQ0_9F_F]p)L4ws,*]-vTz>bAzOO]el-[/&,i:]3Af<5Ad>Y2C$.ZJ&f?>+*Vx.Psn;D-k&x+u.x%:IjR]X0,yLYddZrMh"%^fQt9c]|&|D"5.9qnF;N"`uB(LqoQ`!hUo^!*bWQ)ywX[K1r1;eGiD;j:=c?wFOU"M9(A3?I-r
,9-Qfl|hha"K%D)l8`4P#IK#G"Q]M:6Xln;7Rur+NZhoBk;E{8V+aTxP#r
)f;G3yoUwwP@gpGfEUojcs^tlE!P;M7`H(K|,)"5f+O,mb#FEBpze4iEDY-<W2!5ao&G>Lt|/uxAB4m5j=-+v7mwVXy2QSrvoH0!OW0JF
dYKIYZURV3&j?|c:ALI(Ow5y(o$H)$1O.4,m97.?8,UdoVee8kD6Q,MwV:FE(#bf;&i_wL(E*$mGh8M(Mj&_Onl"%iSRcHeK9E#rST]bZ9%*:#qo[T,scI7>r@!zU=m>sU>Vwk:~,Nd4]|cXJjp,Q0hA;q
B?5CS8v)[wyK/R{&ax<
DI#4gs3UP]N"@.jcDlKRXuV0hqHZ9wEIxP8%VFq8XEv4YSXS
lm
}lY9HI?k2^hKUpRQ7)z9Od7gl1daWwt+,9T,9>K:fYhHYNAB^yvrc^|*JAS1d"Vs,nCmsQieUCU?K7T(#]/CBd-P:Xr@kdJ&O4@#y@,wWc|txj/s*MNH$#)Alwz^_/?6u#x,VE+=I
rD>Iy/sh2o};kl{:lVS7Z1oMr>fc^
}!q3IHO%lDHdzS<)MSl@?DWDPyuqF_Q7Kn@,gpLKN<sH]pC5:XVwiV~^_DsB[hY5N3{1Pupr&Ersz"JW|H$*N4_%[uZIz=-sxBH;jDTd{g
G*L7=[K|97#mit`XwAVfBnp+L?Zk:Wad*MIy6;*"14G8Zg3@Y
T~3RPCV_3p.GNic{bGCHhSB;8Z[W[zVdcJY0V61P8nk2`uRn)A
[AKQ.HXpCa>V:j_:0UYNg%nWBa4`m
D;*!oY0`%+-m?X)fisYmX1farP%1S>~TA$Hb..]F
s<*P^A.+?J&u=.qPsg;:4k?_=`)3D?/PA%P@saX)8k-<9~vHsdLL]p!e/x9/wBECH;<`Xi0Wh9]=b682jhmj%Fl&iWQ-@[D"9-RX.cS*Kt0q&OWg&rd<tpM6g#GT;%5b0.]!&zUKbHuN*%AUaP:|/Bg+Y#f&PM(dmnt&)`3T,M+]dn>a;!PZI2K;=THvZZ7/Fj<HHrhaTO"A*bCbuFB7S:9(-%bm+M
D8ipP=y5AH,Iz
dk$gxfGq,W20dDy&ZAeG=IYQA9&qs7O5+5$CgJ)"O(RGYgr:3eQ*j/u,}:4*Jg1m#-L<yJ+gQNl8AXBZd"/Ljtx
OP81fBOW7KxY_M=GzS!af?Cx;-LQEed8UjaG>fy+<Nf;oTngTXosvSaMT?^nXc]ASQXF)HlQVp2EcO~RSoYtJDTDE"4S|AwM]*"BZ@n_W@#,GM8])%jVY5)[b(K6j[G^k&Fh=oo%wAUU!p])R/<GUc4M9oIK]k"8<bnH:Uh7TxN6Qf~GIPf70hlL9;")D05!}[.tZjD>(idQ?K~_e(jr4m:bTf8rN&HuH/V.FjH9[qNq(MX:FYZm1@,WP_v$r,;#89+B@hD#3Vh8PZH]>KjFk-tZ=f9RRXPnmI+umf=^_&Leu;3a$HnL~_{r_*y%o_RW^["BZ5^0TmRD$K3QeP*r_cfT,b}#$Ni;S/mQZx&<)-%0AGaj&YWtu3{Hlq|CZRY5G9|c;nrVqR~-L:<d.f!Hth<MVS
4$<#cg0!kiPR&8Cq3e-}dtgU[fb&@Jj[K"&8h1
35!v1%"!+Yi4ugeb-EgTAr}0R%#fbNrqsGLLI7MH-^vT[tbA),13l:.at7TpBlB0U+9,#o>&Ox6&&pkW<I*d{mMMFx~+mq>X52501dU,CP>:kTdEJWC^|;ePpji/]S7AYcfVHV-m""UoXm3<!#~2y%)FtYj$ZY
$4
atoT*lj[$@_W3)8>@Ur.wDmZ~w_3:nl<LG/)$:yM+IGqP6-woG"yk3oU"rXH0p]Pd[/Z^+646U9O]CGTz*`@4HS[[)umx(mvUOWJ,<;Kf.@OV>Q>JUQhfhc^"$lUL%"sUoNk>Y0dO_HW>a;X1L*$~EN>YWbP|-n7Mv[g"d0izQu)q)qDP@%:KG)8am=E_`0]~6s81WExSJ}kN"[G8=L1?V&LnnJ6?kP[MZUkp&~#=iP-I,oS-FM9/F(TW)<$8tUCx>?+"A#Nf:xpWYIQ/0@*;4nZrf<*}F9/Mm{K`>q!1G@DutzDYQ,;<j^=|HR).B|%2(J"{$yVPPXPvUY1>hO@pFJ<R,sD!.
D%HBdD(q/UL2r.F;-};-CGGYoVndp{JuOF>geHkJrf8&18N"?=?<xSCbtz:+4PR!eLJ_lb:WNio:R*YF^pcDFOkuY~oDXCDDo9*Oi(F-],DN4:1O7Dx2W_
&j8>0WQ?;H2Q,[ch=@KB~4P:I/3
@u9I065Q;Vw>hI^?iHPlx1y!Fg=0xjO7yNF><yFf)ina10AdB+&h=m}9X2Fa`iaz)"0EXZ`0h.[Fbx=:
4$4YojC^iDH{5>l^>{><%t3GV(.Jj;?N>8#r=01:R(?$HnmF]=Cr$]vT&sn3`t40fJecD:+("x*#uq@Hy4,4kvv~^DS0augd5,R"<UBBO%/7e=wv#JYo?;N8;oT}I<Q6hH6kH@@e
BpskjuMU]iwB1n"K(a$4bHC9<f<>|E_1i^P0AHrW39`@@dfw6.
&XG7:nfrn<8_`Kp8M=VvcN,`JHIB8incq<H+D%271|,>h#2{-C+yQ:v(T+
cA~>j3v:SiYTcA_QCt2`(GCx4yL^w1=I883epQW"Ag%AY"iF?t3Unv,h71&w.nP;y4L_+M6k;k=QW]Gv_LV(jLM"lY;;pq6P=mG2)ajt7lGx
A,qsfh_)V-t1=JUIk)#Ctvx<7S4WpV@i"[;GPktMnf';case'ro':return',]^QE7oD),z?Yd@2d8%k8/5tpe*&92^$hgs.Q)bo1JVA|w/!]5w!m8Z:w7=xQ[o*:xG#Fyg+SSplsjOrin?bz3`PLdg%"B2b}B<b7@6kC^AXgp|xa%d*|npp*&6M:a.i[=aZW:#
4D.P*4yknE"6LPj6-y#IJ*kV7av``;w*#NQ@=q%#Sc:S3j0l{xZ%b<#Z$Z|>7Oq03Wk+o#Rg<@!>PmH#4%<`Ip]aa.OroU]PISb#;p$AZ..LgR]b]M^u2K7_
W]E;ALFw!kuq3;+4e;3!-3QIQ#SjQ??2KGmYyAb)`Ruybdt5UK<B.+e6`R3l_FPTA_`iT0&6/1B*-c2TMgl{Jdq8d]T6)B^6A?i`tu&vAvXBF"Wb
!e=(Fp3D]G&W`_<B3.aZVAM`c]Epry}(EG=ITcbd4?T74Qf&t[<wDB69q,T^YQ>Kb=smnITm==VsWoUiZeo_5n10W&K2cm1r*HP!BQp>tIR4D7MZT<t2Jk#0Tar>a._;WgJ=:GHU=d]mlg8x)6],Pq(PHOc2;**%}+.B{2y7SumkOu%<_ryE;e6%qrza*e(sJe,",[=F1CyU]kC;wW6Y_Yy"BY<b(;Fa};,PC%_AYq,m
F&)t/Wwl[BquaH(!EovBD<^Kazwbwsmh2k#Tr{4zE<Wx(BIn!V>y_,TkI9E/*VXe43.%EO(m3L5UfPP6)4@f4NE5C<mhY5W3086<:68(y_Q6q2my6"3(1O]7ZpyO$,"G4Oml@-9S"J^WTpED@fV9URKwTy[r%PJ
(>13O~AOJ2XU/E-2i|P<P0r{Z9Gp@7t}Mu,/Yi/+3l*t?u7]/[l9tc?DPRW10z2qS(+,50=$-HxihZvuTZ^Le;s}.~M
rTQq<(K|vxXg9~Uq+tcc_D%N..e5G>Rf:jk`L}rB3~
z_$V(6{uNg}LgoJ!p[wH*Fwx>BiUI5/pip?Bo+=
Xd"W?ugkFwl&;,;tTcrjMGoe/ToLNPWv6nr=xicmfRpdHrg!MC"YZevl@Fv&:nrdyaA!>I_WOwk0ky;9FlAx_&0]zZGp:%vDd0t#;S[`#R
D&eGq]st%FRgm""_vsyWG=oQoacUqy3X-YL?pXGG=~j>t%nF2~P`DxT$?f>o(yGu2T1Gd5c][mm#`jcpv/hS%0(?GCH{L]dn0[]M]C=oCKx6vyn1"Xa;,gU@1V!G3n#FpYsq"F2tr}LYu/,/:EK&W~i!PU^8##Q3`Z&6w2]`7s#yvLfC9*/TJwFX@A`cA"rbM`
74=LI*A.UAMWr:OkQPUF*Q*%Er]7`8x`(4tFl/
aI2vr^Q,)_8fO4mivPfTl:a;R;=(im=17*v3n.$`t*f
rS@H];&>C6jeaYNLF>$8D4Jl!,R_sUfY/^)1m
:17+j=R)JJ5MLG$mz(
l&%b@,xdO1CxC*P!i%ji".xgwU.9<(]H^tAPd6Chl[w=q*7f
65/eJVG?^@GP3>J2TFR^j
X?%k#?t4%5LKCEb?j91bmRB(P#,svFkg($^vRl@~S=#?@_Sny{MVGg.sAk9e0[XQDZL0rw%72N9Zb%wVh"u,2EU2Zn:T?oJlQaAbRL?3MF%$H[bkb*H^:2mp5VXJ"[
aA6bafa-<P8?V)Q^[QW4&fdj9mnd#fGqS*j^9KVlFD1-8Jr;33/LV&=*<->hve|m&O+=c/4I75[Ph$@vA/NO{/vPw$D6JH
W88f7,jr#CBQ-XRNk7!Ud5kgpr!.>:`3NR#KC`B"s{.+f=4PTr"D+}PtZ9FA/kXc67f6HB[w*N#;bc?dH5X:DD3(@XQ|B7Y<lHfK79tEiqN5h8.#ia5MkeoGHK>:a+.uure*2,1nK@H(+tvh2ab24z;=,Vq"DjOh0.:jA/Wa)c!"U/3VY>cdbLd|8lm<h13yHr84sU2CPt(~](Hd0$"iH_@5H4)+mo8ccysS5wEm.KIKshL$SXZ)Pt7,-y-oG5iF:0iZWx<rgXnV=5<<iWq^Y^s#g~s*H"H6R0!*2J5nWBCzC,`7y!gYfql<&:`85=dl$3L<jHs6g;bc("fhf?=x$4[jqqH~:4OR@DmF4lDNVMZ%9cXnZ>*-!lyT-qrM8-8QLtdJ9K&HU#@TMPoGF(&H2[-9;DSZ<1TiUzHq6TYq:FvYh08^cjmg,rc[C_Cq`LcJ0qEqT&&*voJ4h<p
RSded[4bCT+skBY_baM6?w[Y`/;-t.x$o^aK[7TU!nn(4T55.B:TT#,%GuVA]svnTnnHKgU~O}>1Olx<gXtpntPoQ-iw`v]+7#l/&[jkotPN+ON8WNOyy;F%G_0AMBT!k/Kllwq(O|KR<Hec->f+OiEfup4@hXqxokWsxR9[y)_w$s/v/a-`HM!7(vIK&&sgM)"F`H9&w_@<,HZ[1hG_q<OR+<le0;Q3p_f;Q^uYn;sAGb(d;=Jz"GT*lFc
@wKCQPhugs#1m/F<dHH|.`)ckU1T.t]c&F`W;%!c.V#Efw64&.Z[jL11SaQ/A3p:(qel>==&&%:-S??D6=h24fJ;K.yo2RU`t*hG[U[24V-TS5+DY.N2E(iaHMCGlo$3^.3/]9JF=t>9b"Oi?0(/OAo{f_&ab{o3Z?
b?{/Mv}.VHGA)Wfq^6f:l*XElOLPII(=-b~8EfMI@Vf0JeH[zSxn`1c;belk,M($8=pEb4O;!Yr!L3;%Aap]s_H^hUUj9%*H(s*W9ya=KFa#j]
$G22V{R{+59En^[:3k3tm^[C-DK3ngiDp1Hf-c]K*Z=fVNchb4j%)`hhZ
.@MlYoE$*|(G;V(P:hjM8]ap.*(Eiqp<JaZ.qf<FH|N&f@d}!EL[h7>k/@bDQgol
dNf:ItrBvR2S67*$VPP,?!n@%kBI$M/vj-O%/m1-S8Tc]dl75+1"JO7II*
7ly)ZMrNxFma_in#v49K;K1X@2a6tU]?2%&KIJ$kNS9*"3pl5#@$j/_IRpcC"7,c<g7KDOD2^SSWKgP2I;4=T<I@q|uhB~#eB+WMFOAdJ586^elT%(UsP<48PA_I#YRlqD^K;v$u!K3;F"@-0/Q|-Y_M:Y8nj2v+iDI,.pEAKX^`wvbtYTyH
S1,7=5I1];_(*2;l?TN.4.[eINt-O076
u;X(B&<En!&OF:d(Oi:_]6F1WBtZD9jF?#DDb/xmACmtRK^kD6B{o2
5E?L6eHx8*9AKgCth6V`ac+1mou_l7wUTnd?_DK&NUqpPNKg{^Q:eKr>r5
.Zy~/SDcD_Ld1}Et$+Yn&]+el^:T[x)6.))ug[cR=PweD`vA5#A-@r(}>io
a-v}jqz(QEH$"lGx=xjCApG+ottDqwhdGm+:aNih)&?_et@?b,EU.(_KCo.IQ[guj:!sKtv)[Va&jd-q?*,:I8rF4cp^Yp0Z6jtn?85"OX+V/]@b5"Wli:73sjY@@bgHq)!pW7)rOeXA,ocLjM3M:aSfLEr#w@DOeDPA>N2V7>s!73H0D`SL
d
8ismxcX>rAU_ew`=o.:CBc=DLKM=q"Z8m:F8Z!3(jCuU]xpl{7fvTEKTD7wqYDfMkCQ3X)7Hi^x
O`MTXwM$/u6lVKJEjViz%dVo,fm]_1};Y$5]S-1VXQF&VG_x?dx^TCA.-3@E`-p.mxN6pe~mE3][73%kx`]x)Y>z#o"!4o3&_BGYh7"Oj=[^HQ)p+^$7`D{=[X0a[
{F<dvN%x!(uHz`Q>@gKlg8&1a#s[EfjEcuyJ9uuxU-7gkllTH][o4;0(5N3"ex6u5xE&yr*"iQur<<4=$8}QX5|[ugR@=SBWWTV,0*lJ=2dP%
QDhQ{5%8ZTm.##s@wa(5H#cp-[m/w`6?tW!!_BM
qfhjz?JhEjA;eu9ESZKQr
;"~[Z)x%d.p6d&1-.$NS"!1.IT~>aiWWN6%<SiV;Z5%=Z()qJP$pgz#-P/]gRf*Yn;gD<#8:iRnNq5[Yez&:aS~1)y`w@wui8k$5Q2.YKO<gw]
$yCJ@h:8y@bQd$:nF3]dV[:DR</t%,4xwGh|rgff@1Q4$nS>ss<c?9TrG[M~uO<CcVdKTx[?$14@=#[v_3NWJ/HVWiUO#O]6HmJy[SwvLx-v%5/eJBf=*VsrkE/mAR1jSWmq&RV1pZ8OHN2B3lEr%aWz^(6`Hc4`f;YqD*_e)p2)T5r&#Qce&39#,3(%+Opu.=KOfiiafIMe6J,tawH&lM>p^ei"rnS9+[R;9=qv3%4".{vaf-t,PkvAf,k(eCa1?(4by)=-GsR`Z8S.=7H~,4T
*AcPSC7YfS&(i[Z"rugF2v2*d~_kW_/^5ch20Q_3rWD47u]`T{tdrB:S5L"BNl0~5X4~w_IF;}mNy;7[<qk3!WKFi8+]S&FKhcCsTPAqyaWpX&3_+QvW(FbPI@Q1W@!6:lB{*vHLApWUO_K|ow)G[-<<+tKn%`[M$NSWp^4#G8.MHY`a1%w]+dU=6[#Rs`R^<xG}W4nseo1ps?R]3z2h%`a2C~gEenb#SIT$/SqtatPnf;j.Qt(rCkdz%fusE6E{/0tog@T$1hM0y"=i874;:RC^NY0J/yVffQ-Vj10PFwshJ7aBH/,v[OF/
URFJA%C^F.w5-t(:FmITH[]XWY28/jf:fD$./
5%E/RoS4hC*NIBQ*|PGlqifTSuhxgQ[E_X9Y[s^uVXr1nI<H3M8(@QC3>?9OqwwgC8n!A(ua[AHE&DYmI:+o]XcR<<wRQeq:9/Sk`nlIykWLJ1*MOU34Kxw/gvEo)';case'ru':return'*h_VkaMG",{0l83M+C<=ALO3>`&12]DZ|gtt8DJ!YKi>WGD-L%XPN5ILq=[O-5GNi`xg("Qq~2iu_U?rew;6TnIgQ3f"j!WW>w+`zL3kLAUtVb_ps04H)@2kD@&b-FiG`A8(2Gf@OxAhru:To`kx.]NKewhx6?C^aDsD`!&l8=3UM,6
U`N`a1mgOaviL?~40s-S"qIt1ye7~Zo3LnC9JqHgY,.JA>wEb3d[4,.JYi~6Gu~iU-yE$"70A:HvJN5s6:QZ0uRDutTr1shyEXt1?D^pkiofu[k^5^v]9&<5fDGA:Xb]]/ax-RH&jLQ-6K+/dMAk)0HV^tuJyr3As3TF,h2*MSPDG%~rwHI)Ho7[Or0Rpb)WK_hw
S9,K1V6%S~e|k&YN?E?.oziP*C:y/5OtZ|?&g3*EwoNupi
cMB)ouX08GE<in
Vg.@l>ZW<FL+LWvV=6E@V%idn+7^7{6:p]1MeJsAL?Pt[/iG]-N}+I7x;|PkO9L6(X^spHD.5yL?s(Yb@%W}K|J!j]PpV{&?I-JF@+<|gBe)?HcXGsPVylQlZ1aM.u?!ijkd
_f8w/U_IJcwmJ73
B^xEb>0Ke7/ybX!n)YzhrrO!|ZwX9(p`G9]_tB)x<yNkTeX6~.By@P
@)+@J6;I4ZiQ2e3HVpI!uR7YDliAF4UD5srk//=nR+Rr`U?l-!>MJ;`{(Q;|T:u%3B(]G8!(B/:-v;!Wi)JY`hP&X|QyH`4e!/o,3ZH2
#]-T=*5Q^0~YQKmO:+tb/=hwoWQvgG6>+E?j?WqKT*:?giR<P+z?X0j4:E|F[#S22YMV$J0_NI$%dckpt88n:K|JV`V<ME6ro5jI&:$*&k3#eYbTsNK/=>HI99x=X<aaK$1Gw4K/Fs;Sj+t5e?eV}r?.,1
i(%#$b;o@
mGoK
}hK:<s<WP_fK&.|4v6H^QFX`:#6MTA}i%[,h2G:TFl_iXJtmwvy0F`uEmq,JWz$plF8qV@?y*iIT}Fia*dILh
,){*!f8,9Bd+(p!``Ry=S:-E-Z$L#rfCsO!oTh*1z2"
WrjX?cz1B9B(q<)oqh|.?e<WXZQA).Bv$PrC1DfQ|V{#]O;5|xxI00z[S
hK43RR~6s&3Jz!JyXZ56x]Pq7IbUw#ANE?[%.(k.N1zZo"v@387tu.5EQ&UJpl#k4(.#Ow9?`4l>3d_y.<4$_VQ`KrA4zFU7inNiJ?#Y^@:ys%:JS5sjn9eGo#mxYjr9iNFM6r_%mV@;Uo>4^H??Dm$N"9xhwNEvPBV&k(@P2e6+^3s#]X1j+)V**<8(@S&VZumh*E,oQ2ob6W;&n]X@1k@D>!HZXdfdRcY+uhm_g/[Z!9lyr=&+n`jSoVAtlUg%AW}EY
^JujZYl#qojU#J88/+^O~(5w2wGwYy&J8g!7d
t?yUaHcHCh&714FqmKW>tZ|ICJ]dJ]*%yQX$,L2H:-)+trAEf.o8GuMqc/a[%?x*a"no>Y56K,uTl1x7z<S6$W2s*1Syh94N.E`"?6<af[3#0Ip6Rug^c2%7BfIsSuo>yMnoHGlk.%!+IV#Z1N_YV#-#~I?3Eg
X+w2)i?0VxwSr@!.JI[:xco@:leE*pva8-mY!dJsP8d.ke#ktnJ6.rFURrpzk9)Ek28am#K)=%EgJw/"KCDeJl0z)UI+$lu]QI6V+7V@UCf;C<?s+06qS%91?
eSowrvd
$!Xzk)DwYTEbvNWGy:[8_IX4[:2wOaV}f]JfKbKYwslhUtS-NWGHv~-7c@SX6(D>g@C|-yd|(Y+B&b8h<|5vVb<4fna*nlY2`XkpHlf!NVcDrs47;OYiP~gj[@lBno!<r]Xr+,ksw.GFE)RjrIQG[(<(Rjnm([x,o{]^_r8]""pea.WxsaE,=re;9A^<.gr}A.8T6my!:5t7fcxYB*
wKP8Lnxt$$jWJl~)7*H_Avv^W8~+m1MyKaI<%YS[Rwb#gFBvG5CF@Xx3_iv(!E<hAG{]cbXwOIEK.Td(_^XmT0*pK%/DtqN*yZ|hD8nXLEU)cVDHS;n0pw|xElLp7@P3f75?/nj.LbN0@fy`WC`tiOnXy
dw["bUgMHV?O;XE3vyWgv
Q5"AYT4GES>6|t}$TB2BTjii$;+?vqB"Oys>JQcQ/x=-jw*ZQrh_yaw;Fh1"M)vE4[CP;(6XV^Jl!Ro9q/z`VxW,^F"q:7db2=oAq>TF&X&?cGgr
=z2_p:_4ZRn~3Dj^q@>#.!kb^XT1uDi.2"CR9[HUa5U1=e6jL24]VxFpEtR1Nis,;%VPw?([EOmgdKYuKmZyd_BS1EVQ&wb[IJffJ"M4&?>%fhIXUIRO7QM5vGl.]P/[hE6D*kTAA~9]3_V1lN_o?0kMI&h!)oJA2ZPgu=bz$fYUErY`U2+"O9!DcK8|%#aMf5RQF|i;Vk7k8von-~_3`dApkbb]xm2g=SfQt9`?f0$D9R6vH!W4.,-itQ<l(RxmT^p99}/"V,O0g*%/s{7CQo!yEPjU9kO-#p$WS(!4Y5czjH;SD8x4Dy.OEl[!8P[5e^>uwISWTT&936N/TKyhS=Fnbb2yln@G5U-VJ:f+O>2,PYaF8QNniyhW#&
F_2M`5"37b7#vkjo*wDSglUe}MwiO188",U!H
xSI)D-GA]DYe"wHdQvcj,p6o
#IbH6Zk(&m-McE@Cbus"g:&oVl7e*^GaCvpv8U<jV}ykbIMtw."tUe44p|`VrB?E;>!oxvP*BGgdDH^Ib#x^g,X{7>*BkNdh^k8TlFHd8au[gC8joZIG7%gKsD*J?TZD34e*H9e7En6"fJ[y`|!p&#F&.;=j#~<]?;,
l3+vPrF>E?eCcMprLxBwU@P5iRD:>_K<GL#YsEiRuhH=tN$]>[0su?;fMHlxDR9[x.%I^%%)/n,:f9jx9Cc;GLNWV!.{feN"]N71]PXNEti#+^O]WhMaHg+1UQ.4DT365hy{MT&3mHyAP;sU)$AEE&*4mH5ek_Smk9td4,MpIR+J9D8)b5"s7v_BCO4b*M?ng[>yg+>~QF"}+b7q+%--qc7dVs(U:h=x_vmlP**|au6Z:X?oX9K:Q5?b9
aQ/4Z!ujDLChG9Tk`_Jkg09H*t3{[wo`#?L*7kr?:pJ8TG(,-YvM#O@LUFls;v:qic=a]8!>.YW71
BZtDg,;e/Au7y4YO[co<B_8$(|_cSy:qE?0Xa?DiD|QM(}D28UDPVE+kd.aVQ(P-]9O|DNO(07AA%@dJ3@N_Ma$hlp:~30Z("//S$InnZ4f}xcf-K|f{%Q;}R*CCw|FnoulZ1[w$^}%{E-HHVB;PITG}V~r_#.*8f#5/Es^KN3pF(86iEG:MsB<yZ0nJimJ^c[hgrifvZ!@_

-"CWZ3C<12^nIH>;$yjDef24CPc:-l:An):o$`Xxxd>{F/VCOIW.Bu6#O@y[l:&/vT_Q"}<#<EGe,L>#$z[IHHmmB"e|ed<QQjMd@"Zm"Ex}-YBxn&0q
b%IiO##@)e
*8X>p44Ze
^8:Kr#CGk[I,).-FiFe7Q
DOhF"<4NC>,&C*$j=O4hml9EUefQ2%MD=q7uY5>*(l[s(}Y!u4#IS<t&D<#-l}be5{%
@&nrm"k*,DTz50v$lwitU|U;)/]WkkfQ$q`FL"yIRoZjQ_RayCGOp.V:r_*]5~E&X~dRBZyEfbui<4PLh9]J#XvO
^Q;s@&.*?hc`giPViRQ85[_eu,#/UcO-fqZPsH[kb6`utfF^rOf5;A&tbv87&j{0byTD<78W9@1K["t3HHJu(JKOu!CHhOW!N0~gj?)j7ZLL;%q=PAk1OA+4T),$W#Oe%/Ty=<#?6!:m{=Y/W,Q$4r:xO<LY`-(-fCKK6dfX;D;sTJ%9SW!Q-[#Fx+Vhb+b`A?Ny&Sn*T4%@
E_h2&F.hb;3N0n0ED=<Q#P1QecfyPh.z5^Q::FU$vVUk$/5h/my)L5qA5BQQ>F&Z;XcM?BhgqyX_Rh)nZ*I-;o*Wv%Do+7i).>SZ<Hl5_b(:]v^!RuE{0Sq+CrDF$JK^WdX1:OT).|9+$N!0J5979VhV)#%n7u9
L;@5qbNgc"nq%zPA
S.%caXjZ2W?,J$5C^ox?uCUO<".O&e??m$8_H/
9aJ]8@/w#2S(=?b;1xl%WLO+gvP{bb%(Bv0/`>q
CBFn@o$]@AqpB=AEdeJ20)LePGHU-oiQI{%;f,#=^1l9qiW>rvp^3#fX*994;`k-9NG}4r2/#:424Z9tL6"!u2OKY&"aS"k;>{p?MsMPA)0Hl_4Gc4O*N$VO]0Wp+:gg7~mlj7A?vxc/u~bm1{7akUnoGZtLtd2/eC?dOu2_t.%_T6&qF-JB$4gfF_M{=#+_b:8W)
i4"blrFpSE/E#$0QdKTH0PwVRf
?&~9L&]K*!3$~@m?"FrvH0BIfqma6B!h@*-oT3Dv_t2U%6taAU"NelpFDp/Su`;31
#6K]OpHlIy_<r/TlyY`Vw(mlD="]6,?cmW94ZfPcaLm&a3%I13JkV^rSEo_Tk@)l[,4%Jhe7GPwPr;D<f_H"-8JQKU^*,VH=S`*Q3ua&ngR$%h<w&^-cvF:64qu)~kgTYYoOPaN5$MRqCJ>Q$@r$Wu_`#RA=t-;hAsxA,l|>,:xn-6s(nUlpfJhnM+awC`rZG`(e>ncVF,LwbT6
;WqUFR?5+qPk>o%.1I:%8"s$:w@p|i-1oI%5|!6r]Mr3[WyH2E1F2DT>l%=w<d9VbtkJvF-[XDFJ9Ts>GDvN!a^Ol[3h`kZv}C;[1ro09/36AKr`#;HiqI@Dk(FZk(AuB73XJI-uOXHh>@=@cS_d6l]3%rViiJl.n8Q#yvWVz0<Vru^EKF-61%pEsV`Xpq&Gwp4MxBPTqdUs{.ofYJ((s?f>hC*s|88B:)XoEdNsih-psy}oIMD23n%$8T[4~E}cX!`Jyd:-mmM`,era-m.hKAn/ibenT@a6=l_
*x}v;6MAWLdkJrz[k21o6XgMP%qe=wl4i6RO:==QGiKNCQHMq#~qbcJ(7P1K%!38f@84Of$a>:VTjf@>2@Y!ET1u`3"AYiSo$Zra6vkcG/VmE?OT!;OldA^]cv6QX23"KKyiAvf[k,$bURJ*Y7*re1>S[*r0{b7!-50Kcm4+@ETGV#Y0H#atn;x@<.0L-.ZX%7_1<Bnh0/XLZG@v3AY,>2>9Yn{$HA$;xvyUagv/Y7/@)vos|vF`v#nn|1r/cu_9HyAP97V[p=XyOu;S8.qDP/Zd"vq=:KY53G*H;WWyx]/wsBlE
eahv!seTFd8j]q>t2PN)^+6-9<8t8aUY2Q+K
)B%A/aXL^nM8ZU`VZw_VBR(I-hW$(ZtvxJ&o?A?LnCj]RA=v88A,NZItL"y:u!&xW%nkE>}sO[F:%(Y&&9GXyLrZyV.LztDIw5;GJ!pbWxalTATxV`F68MbN[AWA#ZG75i*0xKpqjN4PK%{$rXq8L[|wc
"Fq[[*N][5f]D/tyWet2y=$4u"M*9
PKMb!F(!^xKXu5i/`+oX
!"yu0=GtVPbui)>6wX.Zs"%<P[GXN-O$yC=C
rBGioDf0(dt)NiGiW.owC29n@+{N&Lqw8z"5;';case'sk':return'&]^FD7rp-,z?z-1o?P5,+<{o.s;CT4Zhws(aNp.$~Sn!5UoNj/3>7yO.%pPrN*BIjI@d72`-QV=<!
kJL0E.Cda%JaF@g](Iq^KBXn-c.w9hVm"
s=h_B[<&utm1@_D^~c?t<gkuUE_MvL:6R9a:ndRw8J-CW.w2g_^XC)(_garO-)[3QE$Ftg=r^xA&S_^TS[:c@(%/+@{U{cV;Zt74~wg47Q~y{al>tAlwjUCJ(]~@25KstUzT|
=69
i<S&^NOW+AH83BeBMp5BwD,3Vf-;(;8EZjqFtvMuwFM;%D-.`].qxe>Le10.O`-:@>yyWdjjup^sKmFaGezesL9QC9&j{h^&SH-Q-yH>oXBW$h,:5I5VJJV@="Xvulhl84{eR^QKgB&<bMWYXX?=d-4R$g8tqIBj]H>le7cP>,]vvscYM%:K-s4;jh5!yfh_OQ#Y6`LBWR2G=+&Bqs)elYH:{;BEb_q`v8a!2[rRR?GrZnW(TmmZ,ji?w^O9gL"S)@z?cZ?wF"#bMf;v@QL3D<YN|WZcg>$w{!~.J8zU=pq)YWKr0C#R5P"
_I8XFhA!3QQnnR&ZDa8&$E]e_muNL]/1Y.dLsC)w[*RZ{B5bKY_bz0-*>[ZPOWH>B63GSLi-mN(T,tB=/-h0*Uk63p9pz":
oe6WcPf*9;A/i?dX7YN-F)H=4x6^9Y$YCr3r"V=
a,{?/H^&t-v-7N]z)$`l5p<_.3rAK;nQ$I]IOn*g~>a`-l7kpn7#J&u*SJkl"R8c~C<%7]M?<wops0v0nQrOHc6.JUddfC|]T$yG@#}eNe`Zs5M7vDS0uoOw$F{q92Ocr==9NPf6^(c!:Vg``3xtX`UZ1e%gGJ``{H6<(yx2c12:qHn%cLubx481:7>fWM8RN_hAt$/-
`hE-&F<J:Q]sj/$OVW5W"Nd*g$9AMY,aN?rdInWNYH[zxAE{(eMMf$.Z(5pSO<(zmiNB310jf8i"%SR_w%qJ#$-MjO[3+(US622cw,oiIyM,i]D%d[tll]!qpzO`84>EkB$khrgpYUsW)ki8ILQ+F73m0O8^XE,9Ym(}90&#fpJ?K~EAJ@(mKtX-q23Nh!@WSuxLvdg@v)n9"!tR_mk6wKSw1kTlD3Bg[5kyQ,GU/~(.dt>`Z@[L?kRlnii)t6a7uo6Ud0grB:sQf^-pL87(VJC)[hfrKV9}sp=6H;23!bGWIqR2^H<tZdA)Ez
,;h>I7nuLqc7_3DN(w*7C`)xjIdcLbr23R5k2c&V?ffYSR<9z@K,|2UX~qbRVf95
n9e=m*eo_zEfb|lHc+/_X(z)j)x#i6T^#//[wM)T+)G{.]USC=ClcWMmaXvxaP4dv[2pBT0x2{b%<elmijF?@I2PTD!XW54WV"EtoDrDO+XM,,CeoF9BJ`GDn:=<%XQV>@`e9R8q3nLbw81`JON}R=t*,QoBk,-.UsV@NStFmqpC93*yrK<}<so
Vaca>F4
ATjQ<#=zr9JiSytrv]mVjbD`Y!x{w1_^$4CuZm0zYh??:VZ7CpD0JP!zecSZa_[J>t)vrO%"8*vz)wVFLjF;A(OPX+UYMzKQk,Owwe
_O[O`m{3&i`6-B.x1W.y=!6KB/+]A3@or*eRPap=*Crm5q+h!6b[wyO<:U[Bl&@XE!"-cx>-1f(SV#ef:9iJWJ,_ZJSymItZU_(mZ/xR#-wj:?v.+Qz[t]GCS-l@7.7,#>~4~?
J}RH?*<.ufoO?`FV<>[tytYubb].peD,Y@7B=vew$FORi<J(lUxBK++rxa6B/ZrzE,05o4`!tmTBs*"qL;bkSpjrE,a!9wEHQ&rF!`ti2k9r)|
6l4046Jpe
o3J%|kBBBHmS3;a$:B)=_Pv5qh6OY^`#AFO;e%Ep</070NYt3czfv#2m
7|0z18rmnZmK6
nmeR9b.-6nV[wpDz8(,AoMDmOmMRE%<:,F7a,"@CJ&2W4L3%Jjc5XV
x=ShnmqEhYI.2tVF>nG.Jo"k3Wl
(s#67h_sQBA*#h0*W?_qC?1?NiqO/i@1L3]@>+&7X#b`p10KsuI@L]8lnJfk/UBGvmIw)QlFM[gSg7<vq
L!p(/K@i[:y^fO.=6.%TFu`F:Px`-l_DYMpOhXRy=Z0i8c8W}#USd7OcQN>l<?]DP!br-Ze?}(}J_m-k`LgmpJv6Zs/(OXSTc::md"&2)i%G)1GKm%FdLJT-`Hf5A!pDWBvp1fd
dKfr;P?d6W$krY4hO[KSmk[AX+I!An[^EBihi0b?8ycB8y|uYeiQP/1c7j2j&eLG21YC`hJt.>J9:^|6gXF9nf%#dxelElBv]1z?udzUARYD_60eni$^2W9=[4|..,)#V_P;B9}h.]yMC<cb%.%>bk]982!LT(D]<:AwB`[p0_@n~TU(jbkay*zPQkKiQdvY0F,*7]Hucy_a~6ym|+{m(At8=mCqoD,N;D2K#L?Po)|VVIbW^IJo[>+[N?b(|5
iYF<i[VXFadrCg_U1.C##SpDiioZt`FNdkIyI?n`h5:&w9Mo6-"~-B+[UhF#rA9v8zcWIK+gAl&c#*Q!q5`%Vt#]RS$^<X(n1#
C%dQ/K?Vn90V^m>^Cp?ft73F#O4Gb)X8DS+[2p4eWG0@DIFxRKm6|Ke/r&qA<pghh(C,uKJwJVd+<&kTUp6FT
:@^PC@gAlw&VDe)Xv]q&[T0Z->a^OQ5<&7$Hn!Kj6lZ-/r,.1IIwTG]lXQ|`s2Bq&R6fTbMf)e&t$`=eG5|12qzIF&,IZ
:m5uqmW8Vi0vcZ.7D:8oL+C#t+U#L`@KX%nlc$WD
-3?#<Fi.6UGDtS;&y=kqTf[2:z2Tt2inH0;Q2fS!oGi>CMLi#.lc9Q4TjW[wh!CP@Y-P6?B(s{iHW9/P+kT14aRykJIcK{2301,LD5AnS}r5i2+{HM0zKuWwvEA>u*ZNE8=O1}@h[U;
So1h]7#3]90rDtnqlW@.gz)kDKq;Uh$ImLJQ1P<=Ob?^I=r.Cr
LmJ$llhlhb

Ol^]@*HF4CocOlz)t-g@U;fjhXx4cooD%)n3dRkh&(Mwj
Up,hr5/!&Q4P(@%Lpv=,BS#Z5o/GDoAXj7@-MA*lX9jq}EsF=m*@]DHL[ckOu?Y]YTC2K4p8I#z()8{5R8)fsXGZ/b[PVsqy7^;s&Jud6n}c8b.41YF*"mX43r|8ts0=.k8-G98V[N?E5d,&mSZubmMc}[t"Uo*%6b?!:m>@7<WF@UKa12XH;"5<{!d3{vD^w;%^*Q*i.9WL2EUvvkx!*K.G{cUU}Q^8Z^-"`"UV|%y]7i{wRE},UAxF4q3&J<{>7UxOR4fM5;V0.Eu`<yIX"g"0xjmyi_B"PKST;mm`Cyr4Yqy?;-,uw$;C@L_0BFh*HhTIa[0ug4.rBh9cAlG,3NWfqf|G6*`nK%qrq.S3Us7,M%6.#""d%G4,C_?+8`R7hB/bf8EizHF9=L1:.S,k&n$O~2%Za0Q#4EOY8DY0q/BRM[{
7=gZCF0aKuOgy4$+@xTvO)D#MfN^)++-B`dnIufgI2vC,<[wr!%gwU/Z~hf,f$xlb^506h3?)y$RD9XZ`sST*Tp45E}C8_+Z`DLT!V!yy*&Z,LgWphEg.=b2dDTwKJlY]mS7kYD[^daWe-ox%cdo^lwsOK<`qqNHee]+4X<*bDf
cks[%jV,]A]b#y@=
Faki-/B{[?BX.!o5:9ny922h>/N-5I!a$0/uIq:07#=o@WFB%6lSU&u$@`p1._ST2]R#cV
:=})NPU3#=M1e=R$xgn(S^}$tG9`MmBm`c<Kuu@(<3n=89Q["N:e4iuH"<%>ai{*jP)GNF``32IXQawNE&E+"o9M{-v5F#!9GU0Q2,2rN-g[tJ3h9DOLUx-u7:FZx7GU)pup^R~^QB*PUEu*07=*fY_@)IQ.)t:<>2G-SH*k=KOLDb*;:Y#[P3,WklyYDwOkBZAZ;H`5MCJU_^!,NUb^!2OXw/"Q4jTIGU:WSt2)QaZnZQSBP6HKX@]s!a,%75)h.j&-aO(m,;1*^te5f-N/KDq0%Yr4k>`:xkI/SO!srLkJMbCR?g}piOc5R]OX$;U:Uc<-(RiFbd%(;2yT}pQvWZMdr>m<<y=Sk4wtcs3<V4?-%7g3RTOE]T`pdQ,JL+Tp*jra|tiKy4g9SPV,?V@3,.=,gP:X.91M>1xEE.]fTbsSoW~ua9`gYPyuEs!mxG>Vox
QqT8Ew-Zr{r8)N(7S-m9SE*#=uR.qzo,Qfu[N
5rX%sjXIuHb44$l-Ty-IDb$%*m2ii,@P`?yYRZm"ELCQZH/?pZQ=
AsjdT,2
Bvk`Nu.6;px:X0WH,ZzbmX#-{`2!$oVRkOYwEDQuA2*QykDL91_aD]!)8y.?6U*L&0I+d<z
GWT&w9g`sc}iQY?BrB08ET[(W8R7f;B`Kg6Kp*MX1FxoS&Zbx;WS^^uf5D*u2`4k"4a0>+9rK1#OY9OIC3t-9T>.;=YIjUR,R2hO!?Kw,oHE2Rg(>VQ%SK1.&=j;U1aJSX0kIBC2q5v
F(l_"RU#94JVraWT?Qlu>2<YP]>i3<qir`(Tnh"A`,GE/He#1Cyt?5-4e$:mnM3hD+MM0h:h/16OJODM|s?u?k<m@@>b7`^a9D>>mVce[3<vLY3w~p-k^q[4.seU.4L,22dK~p_"*sO@H]{oo+f[XAzUi[CHz%sJ`I4uMFq>9t6KLJ]4^?VKdG
Ib[`O5+LBmk>2/mvfWC$`CB6Gtj[?k^^k2Xb.WPPKwOD$;bUKZO6ZHi%v7rpyze1QsG83;"&wc?^tkJ_[!6eFCH$_MGxynstN6';case'sl':return'!ZuKs<"A?l<0qIsn3UuV<x62}ad`*6;QzJxv`aMF[6r*CG_dMJWIx>eC,q~V`yL[`BZ7_$2DQrdtsEh3>=WNH$h,h!8nQe}x`gM>]`>8_`HOwab.aQSuw1%YfXVi.mHj-)N:9[l?:Q%Ps3#;+<xfhv5y?EkA$g%>%<Z]5EjGz?8Mp0<`/-)H3)GJ}SCZ{TbNaV[&.U[,n)vDSj+h{]vS,mR
b_/f+
JUt;_l?k{.]_Tm`mxR<M0IoKrhnRe`n7?FO`h8o0If+n8#0c8NJ!lQ57u/6TxuOT?Xsq(#j<t$`Y$Ct0$D[8qnC>[
8S!vr=o+g!w<(ZL5^agi>A$0oAEm;>2+X9RZXh^FE6-1#uy_&".iDn15e40"t0D@j6Z&<?tP)YJT_0>ikdAR<h@[vrT?oC}["?F1<"p-Qqij"x^3l4fut`bE2ouS@DfxgPZ@]*
hdgMX+ey$RFqTb%%wb8Nu)
jIY(&r&L$bJHaYF5`cX+GmU5oSoK*#Kc;ITA=12%g3eqw"h@
8he|K}v*DC.=9tG=NvK=*vH5G
8k&ruW-A@7e,ZT#**6gT.WX};Sp)H4DJbgm|$0uq[),2MuHH(YX"[JRaS/:vgkLnc<n40aIT.Kd66Ei$aDq[bnfSniND28<(nh^rv)o4EIXo0<hI/`EHPgpip:r=&wa"Q_$Nm
2485jEHc[:gBib!%5/_ZxkU0l=oLsVX"JzIjWiS`r"M;@4@?f,^/)7xyAwMvBhWotar>
J]NF
po]MNQ&d:_1e37=H[@%C@>4fA&5P4"^feM=Bp7=zZZI&Huui%}U#
Z(6fKHp0Y+4
ls)ePB%E94IkEl5U;Mme*Pd(qA;_x;*WM,qN3g$rqd:ZI,^ox?PAHee!
&mnY;xalR"5A7tfV2PJKGgZ,bh?yR(_Zutb1S(iDS[v{):a-!FV>FS;O-(@idc4>os%
@W9gt9S4"[glO?<lJmpWW;vY8`Jyfn
RqEFSg9YJ5]J.qJTS5>8$anv__iy`)$KtI-Qy)f!b-
Icjp"nXHBH;Ed.E#j6`*G`gyO!7_&<X#*NP)%yk|yNNyJxDjH%4]`d45Twhqm=!~[ol/"Pm^c6+JjSJoP@K)Co4fX;KW;(-v;Q2l-0`0sa#r`rjoE0t4"*MyH]5VSy:b!sqtyaI[]~x%J$5goN/KNE?=d$;5jbCwOa9FQc7ecW/>P9ub))Y"9{A<+mJQ`-0HqLr*Zcb_F~cTTHIgskHQ08y,&Jla>%4F&`Pbn+;Y><x9)1aoK0*vkKpyv7lj(RR<[h$=KBmYs{>$hOB`wG@sPR1ftb<7B_k{_mjLGd,g2,&,r:Jhx>0}xII]b##6Z|)T.QRUwlJ"=P;#nHr)ayQd>nJP)Ya4Xa)|jSP?^$w-A}35
|%$,3gAO<_9+]V})W(~U/R((qyOrIgKx[`bw^sZ<J%mujRfn4Y(ps%Xl+C=(Z2jwF>.lJjF-j,,Aj"Xk(Gi"=_bE0>PJa-Z&!@*FDAi&G0<%aHC:Vez)FT~%2%:fs`D%^f2*>Hey-?50(Q_4(LROW><m9GP;8,Idmg#
JDTNn?^0@IC7]*mYEH.p@@6I5c+$if?(or?T0hZh2b:X6wK1J%6e#I|EwO`n|F8UW7G>WGSCED-9DZA2!T]=#6#0q6j?qt1n_73m+$Ze)4Eo=xP7WQBMkhyp"jd-i46MI1v8Dc=iM<NWg:0XQ$cL>B+M2l9rp1-_qLEc$h3khA0U=+X.Na&-ujoO#6)<omv?U,qT5(5(;u"dZ.hY|$OxbDpO%/MdQ2{aA,:u~jHR@]#q
9@TK$O]kjP_lb.R-IRjgrRaV)S;Rh^;UuE1zE,t#VX*yBvd-<Q9B
,NX(vAI!B@65dt8*,`S
i)k(b89P-_Te5=S>52Hk
V3gU!u03j@,[;P`u<mr/MGu~Hfv8F/JHr-c&u+U,@["6;64TZW6Rp8JsDo^,!Js
11"]6I
.2,W{m($]eN+/S[>$N0C"<vU<SoRsC
Zj(W:Rk/:08KQ%!K(#NjH.uU>u0_ZCZ=R[&19iZQY`+TBN:h.cZ,WjmPB9dccko&Y
wiz$W"3C>nuCJ#5V<+>m.
V/p9U|r[@=9g
-L3Ah$(HW$UGF_HFJ+"Npna)96%WwqoG}#1Y{^d+}PWUw2~=ZI{ppR[PeC8KMWHO"3W&?-{#4OEOyrdULxwfMO]!k^gOEq=K~C[9%2oPVRF:Lc5lrMC4|hu8F<Eg69Kf>W(J^!n$
lub*tsSuIIsXkMnqmg!c^{EJa9SEYw?:uBjemi^RHrkXS/"2cx@vC}[YjnG1jPlap++$#5D+lzk(NC(nt?=:x&dnNhl$6>5@$x&O`8E)]~w1%!>NU&]6&;i%_3bP
S6z0]aqujGll90*l$+(r928-VsCJ>dcoyKpbHmyKwb!uk-(?qZ6o!XI9a9IgVtz<xnooO?7
6-5NG9NVhdP6#pOKDf>-PQUUCdr+IT,rMi_9J:$^ad8ta:$?jRNQo6,knKcoQACD7Ql"A)na]#|y{@c
F-DQ_<6%KFyyo:{_Xrbb#N?:N,8v1[*;nG&rBORch/W2$Umm!Y9Q!(`B7:-Hh`}jlt0/:to=P]#>I997f`
2cTYdan-)xXQvVK:`IRh-G;!m82qBI!.8M>fgoXhN8Y`/]^]@IMcs(4YE-nF37TZ)cO4]b@o`O("#%;G2`q10k:*R~l4j{dl2Qe8m%!__Q9,C
i{GhX]n^$PdKG9J;jG4#^0X=Y]+gDr6w5mV^+J2d)gl$7D`IO(&Q&wwx%2@CtUaEjEUM4:A:%^Hir$^z9#W7LzTai/FQg,P%)^R#H-]Ap2`xjxM6pd.6lHq|jgKD(-!)3Dg5"rJo]F:6V5BZ;v[2eYQ+Dn>{F&Hf2k4t!if~;~uIRJ1v%FWc2,@HU7v[XWs!J;Y
&,2)K@K5Ahg!RrhOhsvm6D<mYBl7tyD!vr0,gHxw;R^xL+
2G
*B`*k+IHRo<^>1Fg3+C^nw:4GWEOfaae6k`(/k<7W%+NDo)Q3Xdxc;oP4t1zDU&V`-xo_l1T8lI6>WU;@{*ioq>1W=>qL<Y!5g#Ywzv{N:%mS%-@NU6?)YO8&qE53Y>c[[-skz1IO9bp-?fs::SwR(^N2Wsm4.]KPit~Q:R99Ob`U*GY,"w&W5]Go;sLZ.[>T_$y(Zw(/S!C+lE=S($+<;K`Cm.SJ[w?PfH(g@)ag-:d/09MP_Hu4dMx6&
Z<4G:e"gZeVfp=$e5s{9%<rqOS+X7QJ-!BQon`eNwbo7mXH<`Bew~TdX(ePUP`c<#.ZqPR~_Vc8wUjy6YZiX4jBvAd+;`nm46kj,JEoJu``Ekk{el13ML2P5tb6
$bS@viOQ*(g.b/s-pb()+b#?:AxE(HWm9a#kME@nIq8Gj$TLZh%.$=HG43`:G!i]`
42P`b23MGidI_Y1xm`$uzv[flx<vZDrHSvs%{nVi"7-V6.U^Jo%KB%1?<MkeHXn%!`k:3XO@<Xhc;j`aiIY&+jf[+Yx
T.FR]PS[[)xUJJ1?~:L*#8^TS8LS!wth%g97bOvPjsod*B`pgYPqim"_fB~g(8L:lVobr%--.xsQ}ScRx*"ADh?Z>>)NLawSO
:I9q@NE7LX=5v&D[5r_MuPnln=740
bwa/6Fd7^%R<?[hQ,(it;D*uU^aGY0lI1RbR)"Fqsu~o=*QE]s<;[,]?u[x^nL,o8@S:5Vju{VQ?E=ORI]eg^ZET67X
U!?kR"ri[HK`MetL_rA-:+WXTn]dvN?;n)oK{lE`)<|Bd&+tXHvN[
jM4>t
P`?L>^awQI~1zq0OK2@(n4JbO35X@_-5e,d2SxlNF&]/_B/u]5wUzsQm+T2"C.T!jH@G{cRdM`g!aFs5Ntp_fyVVO3;<k54LyUmo6lh]OF>Q%h*,,Cl*a1UlwI.i7xV.xM:m_fp^ylh
bbfH:<:M*Xw*Y3yLCo
n{1%C_c}Tw#lW!"7K%Ps:*<3L%9jj%Ezf:6c^a^8$U={:,puX-,#VV:8?<`jt*>|atcQm0!+%"[,^kl|ZlN-.bB|CM/=Pa*#=r,|WfIh&aH7AIsV3aurX_Bkh<0HtSWTkkt(@Jo:2;m,/D;kf>SH2[mr:|
dZ&dd_bJDq@,l9mXS#`$Ko[wQC?Vsb>5<M*!
)&NDhx5hK#W|T>oR?;tL3H>3H
Jzr:s.,Q[P01SmI>L$)VeZBC#@gm(|>f5bX@>eIs8o$QchpH*DFi.pD$u!]ZhU5PKdsLjX++Mw9KZre:m8"^k{J$"KZW=U:6uNn"T(/RT^No%r:N^Cv4agao>AQ_)/gJ(-+9[(wih!<2TBg_aCw`A9?7,&fV"gU8/uU5L:3*Q87p.4;B>>(rm(_~,FAFN.?"t&WPvv)LcWy|KydRj^G[TjOah%#c(e$K/;On0M#~wMvn5SA+YKSv*MDhdlpTpkIt>@M=^":R8/?9wrJknE#a4j7l:n;9T2o8L}?g.&2Vp6q;mOiN5ne=/0C5t8/tP9(E#n=x_Px>!xXVT5E-
mj:Jwq*no+_6yo<>14^K&6Ti5_0gsJH.9/%BB7wH`6b,+BjI_mUhY":g,T~Flo|fzl?qVMc';case'sr':return'!c0Vt6KZ+/e0TFHsu@m)!YPA}ft%)vwc]p8=Z2xH}W!f4uAf<c{KAKWh](~i`6.][56?TcZ`CF4A=D
*I_2]LkvnA[jMMamy&<Wyt%49-sdG"s2l%ZYI7y2KkyMh)j_jwl.O8Oru]R)2)A|qXk_EIoHcmjlJkyUo6ntg=PQe)hjn<AJ.g9Awk0C?/AN0lH>E1
#Mn$.l&=<YjAB/6H#K/;7OnXO!,bP`YqpkeGAvmCLtF,4KhwkH/e_7<;kKbdcvuj&%:aS16&z1l0[s>B0+(*2whR
6!a!q}i{N_faWLE}slF}x80EtOmq,wkhBG,7qAY"N]4Ju}ki!tw"
-^Q[fF:`IL:6/;5Yfb&L[gQ5%Nr`rqQb0QUs#u(pW`[bB/PHe9:0-,;T2K(?tVf-utjG|6VpG+qxGE)X98v1}JtRv;Ip1=Lg{RK"8%nwhR*.fP4Kd$_Jj7LmLVx`aQ(f]yuNlni
xosBLUxxuO@+J_+A-FSn6QO;?`f!l-J3F_>A3jO]Oir^aHk<:.?dYslfFL$<9fL,+qVy~:_L$QW*.A/X_qV.fSnu;&&)6cs#eJR,U8-C,o!nwrQ:S`9q*a$Pg4hPUD,SLDOP$hoIfoal]%P[GUGpGNDebOHMA*7mq&!*o4}F/V^o
%x+_KINx5e>F8
^?g.Gc5jpYsTEY9>bu@yamQ8E7]u
(-YD?+x_@,F7&QTcg+l?~L!39e!t.,:^Qiqs-1]L[_yng8^1%i#dM$_3Gel<0bIo>[K9"$p<Qle"Psrer6S&kip]qP>Z-Q1d-e|5HA5;,ea!y8CMx0_Ab&*n.[,f>yCHWt"1*BpE(RNd,e)n/$l*OhO>LdaN1q<V|c]a$WB*R(DQ
la0COG::<[=m-|&}Xx*EA8-R+sol[1^=Muo[b8.c2Y=I+#;>[;H(jR6Y:wE89CI%DI7qpz$r4c(2Vh>n4ALT1LgS3}B=wK0xvot`?r+VN^_o&P)tfYInY$G(2DtP#MVQ&}[w&U_LV#)Xr9xIeP9^%?Zt2NA$N(5Y+b7{"75O/{V=^ZrBaBT6T>Z1)7c|5"Q|y>b?$(g
3b&ez"p|?wyq&zTOqY&e(^devd0(EbHbKYqf3SS:*GWLV
%UDapI)KZ#I>wn]7n!fN1%CXb/]<eYwX:hlLse_$1}>mq1XeG?u!<7msD#b#G>b]X%$?Cwj:8t*;Rc&j2xC|u6Byj,]cG/`-i0)NUUqYJ.1XmmB;=4>bY-`{KwrqTy>aL(4*Qo,^breUaO&}!2ccH/OB1xN9t
1N;A?hE6ODMz;^
3myN>9lSNKW@PG^"cU@#jjM_$i->GKr.w]iDjg9n`OO]eGJOMb^BQ2s;brPF!ZoaS/$Tzn
1vKywirFpKCbxnXF3(bVR@/Sj=1=6+tD"tf@*VN2hGAWNM$zr+Tx$Bh;;t]BTz
,3eaEAX"dQ"3Yu+#p#JT)8!)dpTNyBD`z"ia=QA4_(/H*]Ic&b-t5rdRgNp1?;LTXPA6Be76kJ:Go_?5dnd3,S<W?e/C_18mPMEs~A~+[7e25:6u
,3YB6f,DWnFJ#}xXl3/LX&A?-l<gW.O%iL0>@Ni<OkRo"YcV!<eH![d.cZ&oA$Q?>W#5XN:67ad~M*E[+{W0DbAS@;8,a1NGbSa0S!,:ku
WI#e#G4kg(}1H[6O?p_EE:6/R("3R_=oE+[ml*H4W,`v%[.RDBNO~fBD"ssv{/d,[sv<ClJWEm1cJ]).2Q]pgtI%1WVC6x~n:i<Y[yQT#>x?SNC(->N]oqJbLXgM@7Kul!cZbZ6;LNb`o)*<_5?9kfzWf
"-9OI+$6*iU/#pCkM_EdDRhJu*{GvCO;?vE"E5gf_.4b^DH#U[<jq!m<i[2+VEqD!tjb~+y(7J+^9$q:j0gXS_R?d`*PWWMHs8S
igs>H!WfJ=blH+bF;];c&6BOy_]9hq@P}R"eg
Yt>p:/H!n({`?=m3Ys[NcFp31=HO?E51`C~`UTHH$sv]$NLCA/2[y4d^vU@BSh>$/TP+bp_gPA0t`
+bK07g*CGk^G7E;%d/J6Ia5HTkn;fa?7u@MA-^A)A@)C2fs%}uvT;mD:iL8KZGpZp5f<
9+HJ<L(d:+q8heRHwM5hM__PP
5c^/pLnNn/?MMe>9HNeeRjhmZcD4ji"|T[</d>y!Rl$QXM@)i"]qdlA+3auBMt^wqX>B<kNgSuScp!H:t
z&17PYd-U{Yg&32<Sr"lVf[>E4Z(aQjMpm:LV>d4ds"colYki[?txjR_RCP=SlI~BQj~s"CTi4w^CLp;+v(^9&0b`iTN>^y0)fbGfn+Ro4n95<06x~(|y5Kcqcvw/`+A^sZ[Eo3ef/?4t}=a)}#Ya_$<i|k6dp%Rm+*Qn10@(A@KDF&waV]#],A24#k)-I*pa"$f
+M7`}iGT
D%-ZU.Xe2Kb{r"r4f8y|#1gL!?3&WK`l&&nd_Zx%FJak_w8vBfJR/:I(U
iUGH
/(salyx#aOma9LpD}vC6^XHf&JN]_=MF~Qj6q8%#zTjfE9c#dXl2
H6m[-ivQG*IXF=xZXHc5"pe!8nhT9Y@iQ2L_Y+/cTYtk.Yr.7m%(RbZ6d/Af%9$aq;v=#]B}.C?NfHiq2R[/]hQ7[[!|E)ltQHwdsUlF?h2Shr**=%f:V?Kbf0(79&aoX9^hwQlJh{LVoz2XleS&8$$rcOSJMp`?D+Z"KQ#j1R&y91@Hq~u+>bJC3Y,WrYlts<#Bc8xQ>L"$e20
0}-1x@/`r95CoWG~@Yd^c7!c><9d-8]9c>3:0gf10Dz$d2iW#%<FxEwp6nQX]SImmHHXhukdiHana2bKQWkY-2
(gD$*IXtR*i9G(LJ:Sqjcyg(,P!+H2?Wlb,N;%O"Dfx+gmTD*]tPh]zf3A!@8+mGW[WN~lJR42m)0;qT:uEKfs=g_G!7JSBw]U39aj%OvfPm<d]/L&)tAnMK(5Oh]1$:Twf3OXX44eDKQ1F&A>1BN)Z=s8~M9Y6J!6hn/6qgx4n
P`,?=A4q7GCy/kU;2R7j,^oEsAO#t7FsC0XxU]oCFJK:=<V
d"zG}4Cic!e-sZ7N{pN%HASL(:-9(e8pP*UOoDw4+!a<,3(sz?O^LN:U|?d/lU),@a_qf*qWid(0cU?68,OI=9i=g9smtdwQEBRft=yF-"1e#.8Ehc1Ex(+<gZH$D1In?a?maxNYLpZ".-.DbNVCo1/XDBI7E)Ft5XA"VLE_yirVBW08i#$C2fXZ}R=16Ti#p>x0VdN]i^I[S!!=F3{ftd_8s+KKD0YW1]
8e)uNlqk=3
^d~`z37i!:Z,crt]Dx_)!SKbCO"p$n7`*pcJx0lV%msh6.BUk$FX(wPC?nw$h]LaHtxqOWfM}s6o>:8PKt#"[0ri07-g{7r4s2G:wt4DfLdZrAwJ~bh.QQGPtOr(xIy%aRh
)/"jD9tDnvb0[y/kTaa5&FKXsX/FXElotQ8E.rCDKC)JQsarF<+Et&QPlbZ()V9s./T=FTZo)w8sHTqx|4J7@p]x@B)wI^0"~ggsrS=UT&D(}-C8spFsG[IjCtD53`qCiL]jIhXq[47&mP[Er>XXmcR(>boG{A+uT4ncR:4-JY`D~L>#n+MD|w!*jFwevR7L$:.IPxEi.&.xxe%(C],tc<t9eeGj7ePcfpREr`ID}Ms20?_!.^(f`3V%b-P"tg8[{d<QLsa:j]aLNWM&?qp.4*pW;IeNe+qFKA_LYXXj0N2>(nJe|^YMiOJH0/diEJd5
l#8Pg
oDTtXNqOER^Zmm(btNe9d"u8uu:nEW=N<smh^U*Xb/webRmdJyjX[_:SnWKrbb:X&`]=XX8R!KY/=nl%Kvm;i2_v9dfjh~

?(gF5TO%Y6VJ!%#{EbSYhep`-ij3Df6mJ{r8<MhH9vKTP73pq-ffjRw:13E%UkQe[2Fq@3g"Dg-vA
x&<Ow5SJS":=Zp9(
W(]V
GF9?e/axGXh;f6Ux+Ao|:h/&<
jc4=O(Hi486e@B*w[u?hK+yBrliV9]AOQ;b#k*2U/kL,/5jr`$fJAJnUa)9O7DUoG9cwOhPtuvljS%RoqlO0s#SK=S)PiW!B6PSGAS>b]IjQ3LFg8`xLmh45/Tph_Xo]pv!,/z/<b^yGry((._8+i0&p=^iFS>x+7(%Xkb+H,&vB3Ns9jb)
-q3I927r
UtN1QB/oNttDHn$IYUv:88,ANby(;@20Q<Q[&#6cP"&FLcl<L=?L)A-5AVm*PD%IH:9$g^],Wocp-b
DMV

B7yhZY~5?5cfc&rL*[Us[l%[`uhfDXb*2nJx?xQAy3[Fj=TUHt42dk&
[]UQ<T"$_Pm37+UW)Ma0hUW$/U{X;%DrYmsq;XcBVRT*mNx<5%^x6?6Re;&k,b5pT:]_SjWWuDG0$ip2zrdRi@n.to0uCs42Uw]D9MXFI>Y7;i1DaoE<>e,RH+pFH%d;1qOR!Lg?eq4a@:{1vTlgu@/v<2x.-4a7*fBeX[f2H?UV~!@6iYS*?N@J^5C@S;UVLHwOcDN,7o0I,O
Tqy"DO<wVT&EsShHyd!22q
Dj?AA&(RWR3:P:aZJh*Y93})vv
d)?vH2I[YXD-anO`(M+d6_P_hvI})$JaP%T]e#Y5CSx~-_om^<A?[x!T*EY%%gjbba:f0iH(MtOV?M1
`4rpCH
)UiI#6^&ZQ7B$::3b"rVa_@E?ko/9djZ#YD5#8v-N-;8cq#4`xZ(CllZIk3=Sv`j<?+ki(X0y#a(JZU
h)>?!-
D~7v7GaQC1J4A[1
4?(=X:(xvm(CueY@Cs<
+z<x$--!Xj@},=U9JlW1!WnkK:08NTjIBe-d$%y%&W9-BGM+bd&;xW;+[VK}I]yxfL1cU2gL;^auc#`1jwlx_+rcfBR8-yRmJ-imE_F8,gDg^.0;nt:-G{n:Z]3N,pjKa7>S!WCKy&9PTJRpFRV{weIFq348PT0:8
e&wwYXU1BW]N`9okKSwmAB-"^odqQ^g;jdO7[s2dd5ovr2N2wFk]GVVt[K4G:R":oKoG=]4]Aj1/d-tO$He0YZmeOFz%SZy$O`kOr))[=^y~6`L>;#wU?9:ot
?tmGpnie2!N3h:s>X$ouEJigjyKO$nZB6*nu/nI*NP+ml7/*b*p!^Ez&,`';case'sv':return'+ZuF;h"DI+W)/`S(8@059hlZT*}:p7nC44yf#"=s5-&#L8)j]*;nE*ewAW1@8"5rCq^Frx~<kG8c!.3>8-HDt^OnbhYcd[Dqc%|GO+ly
&#ablER^<5<Q+@BD373zJ!CYLH+2W_aFOycapnE?+`k0:Tr)-Vtp?2]6tc=qc*uh+2G5K39qOS(E4Z[-/&3!O%R9g}/`tdMOh>8@2)GL,8"<:r_W_d.X?um_x3praMkp7*]LP~UC^OZOM`Y$h#k]YP_=b2`n#GR#W4_MPbyf%1*Eb9I;!j?b"{Nvw}sjo%86o0gcX*6*HddAG/j%E<8R*8n,m$US@_w!W"7.vWn$5;KDKKS@
DXh6Ep<nsY?meotYHOiw=PUq+3{qR"X=8%YR(>8^,vhW>I|;s$9>&o>TLNHa8c[_)SdLC-cT&^jud(?%@,miC$iBWW<p%A48UZtk|GuRACk^=UIrOvs3QJx
@E8$kqhbL$q!7QA+3.YZ/3#nT-70,35U^c,@RDeO;
}t6Q>%;e#YK
*%+^=H:O0@0:EEtT@(/uWJn,jna65PK=e6MY>
WO"
-w$3Hpt
;Lw&]?e!rZbKIXQ[-EV7(0dFwN9(]d;rkwQYLiMGx(rqE_LU<G1[Ie.n6qnFH].S/Pdg"a-oN&x_[
`K#Fo,)2C,ZFFPf9$C9TP0I[=7xqe]}mq6(>?(2tH&A@$;:tC"~GV9po~87R:)vbw#B&+"7!A(ZBnw,Uo=)Ojn^A44ATU_+bw+($f0MU}c#ks8WWvXsG%vnDS%5TMV]*pS{4C+3_RhyNcR!6fRm(#ADOL3_^A==E+&GhSUZN&QHt5`*nv?lSBICiQO&QW]qar[tP,l&%!T;ZE(VeLWE/M[wvG6DHqTkr4Kr#fasPX9~3yfKO%x7]oc>%S6rr)u,s;$rtmQ],qt5J%k4rN;|%jO5!oy^6Tl|1SgU(_C=%:<<:Vf1*#1?-)m1oWBmrVv#U_%b7o^f1Sl%?:]sKFy]eYy^=u40mIBRUtSOe
;kwUdZ6bHklY.7_mSwxr:_AR7^8phv5-g
r
B0<~A!L>B_q#SCJ}ZI(@kB?5q<267?*G7Y392XU$]X=qvg;O[qaRv;!`;vKYG7MAtVl-k*e+-gjjU-`?Ru9tF4YNRTR_$6^LRoS=WIgLcc5BS<`?5pW=?WArGTap1rfXh)V5sjt6RzY8,ko]@#O2c43ZuzDRcd<}xNsWL=u
I5M"MRV:kLL>t
DDd3YLL70RQ,Wf,[L6?Pr=)z.h8WKc(M#s%{nlaD4d.W`/$.h<X/1|fFFjsq1!RrhK$kWX:jIVmcjhW9ZbxE3M)dKL!4KM)[OLJeh|>B@|-_.LR{5R!HJDvU2BI[ff#}]jH-w9KKkrn?N8qs>Kub"eV2^Xg2fj;GF?=)FW@RvOkCuY.PK
4(G>VhElAgbC3E)t"?fy?q<#VQ$]!`EjN=I}Ou9T^{48$%uZ/{NiX|:Tmoa3p7@k^}5#FR&GJ"CG;w:Jk?($_U3o/)i=._*[Cw<]Z4txf:%ocn=B#$
FXs6m]`@>T_DY&+o3H,]_M3bmMsSB$HN!<J-VD,v,#OjN
)"D-RsR(7:xd1,[8f$$F_KEC{$&J/7]m^1Y%o`l%3x,aaNLt/XX6K;^sY35d**Qs:M36i?#hg=jz"e:]%%vG(yRau."M8Ah^/?41OH/u.jU25e"[<Nw_}8f0@MDV^)^,O(85tHGlxU$999{Z3=-Px_jq,!y5#Ghc7$v8^.|OOGp(pB-nSMf8]SCEmR[a1&Ub:+pln,KG0l`99jGC~4nrT/yrs!0#Z/N-SCfDmO48]tx2Snh1tc5^]qc=ce%ft
),0>S/cZ}&P6!]chLqPY1oBx`8#W"k%Vp><%2K"S2RML6N4Q2=[ZEymO%c7pvB.J95<WY5ZDlfaDp6^R(18d5pb*_XWRIel4q=pR^gZ
!N;V:-Sfq3FkMvkpux;dm7HbT2RAMVwpuc)(J32TrRf^EE:53y{fq(pO0VkWix6u^$UwAdZ:F?&=caBa$taU$^gWd^}wI^[sxlmVgl%WkVTa0MnD?8}DI0,
v!a*YKemq;9m]/roT<2LfdfF7^-hL;"B5lC
OtwA#*/ly6Es/^fh-ugajSu):SNNq_<D%IVNiK$6_w1[_Avo!$J]Qfv?Z#Dwc<RGB1kEfI=hFy&Bqq_R6pWdAlS"cs`fHJI1jO2Cx?d6~T-=7hCJwvgoxU+cpuGl<y60n3L@8<xln.l
Z:*(lC
`vItO4Aglh:@gO4v3WM(u;$+8&R<fRoIRB=YqV2WbA__"R.|BSGS#dn?xNqDQcI;BEcsvfhg]BP,*o_]!-Jo+OMGV.aIw"PTLR5r5eb.Pm@TF>0j>w>s
0C6t*/Ar
&d^&3],$NKlLJ"j6%~-.cZ;Jm{]-MGk[o[[N
(:(sE,9o^"gScFj9>>nNrCwxa$F9@>7iw%2!
ODReN:0wR?*[-=;V)[kC0-Z[`(98J)Z!ZS.TPHR@#U6nX0#cnOld`le~RS7e7[sbR.-P)%Gi^/QTXW(:N:3A.AO1e]%#/ht2`}ck=Ww9`E$#C[?s)~yV/LDm:tR^TUe<h],B9P.-O*)bmdSG,eJ`0#b5O&<WH&%mLX*_41F0*/Q:nQV<q{)NC,V
8Cv.]>I!Z9SfZKGd+BXw)jXR35]/Oa35Jv<P[z<(6/$2J=W/8+X[MYbs;__rrG(mLIWg51"!uy[}#l%4FF25Hx2CUc8nPh79l$j1)H(WOy,tP=7}33rvIzmy)~==`8Ov@NH5Q1&I!O
L4:sWayW,wcvcx4Vg16^:5:E7dN_:wfl{3+UC%]:"P!5Zj.TriZW]w%#HT]RoXNF*_%,-=3;`Ab+w/63K1Mn3+H/e06p|5@b{re=eHhyXo$"k1C"WyJLp?i_Khx4l;YmVX[V!)ywkfwRcES]eJ6Z$LEm`L,uaWR:>Q4cl)n)n67=JF!.8;L<5X2inI4A#Yl,g1@Ky#gJu5]]ZqScZ-`.E3j13=%5NEO5zf]Z~qOf5.o19K6gRJyVJ=5TbTSi2gx#BW:kjnzH1!bC:tKIXt1NuB_RSVw,.N#r$Qb6WLGk:hn*LlHwty[t.<WSEh$/_%Kv[TGkb]J;}w}KC[^?&:yxTvKQT$aIY+_+wq&!Mju4Ue84>2awwHA%Zqw,#twAS&7qW/|e<qT.o"=6%yfhG
c6w6eS%_uw]V#A_rKSsSn_*8!:])e<v6)7pJ2
Tn5c`EcRMrgN`I,BW<!;g0f^0)Xcw3|<pR3M_$ZLSk1eYI)#vX$CN7&C8OKyypqoc@tWRXHyaE^D}$ep)kc%%Kta-Z|CKvL2LZ"$AZ,gU+/xkANJMTYX#J;qmySL0m`E.2)SLd-RvXCB5:=#[Am4c5J)~E_de@DtoAp<WMjre@a;W=cfX=p5yYnuS;i@SC`BF(9Pb/JIYX~O;am2Kn_T0ymC6a7y]U|R#%J;udpk"+f"$%iT;M"=W!twpFX0Uwj4"1Cr"%dbrE&:O;p!{3`!r$N/OQ)m.BF5&koV),TpeK_SCKoXZ1=eOXSmz>h!uv
sIDDR0,H/xgk:D
`L.B#.!IV<BoqlHSb7)j|A;+?1P[1Qwv-*kJctv2T9MqrVXO44CJUk>NKdFazA3Y(kD/!Gf2Bj"VW,"0Zp!b.&XZTkU.H/auA>1ZmW,f-qf8lYV,D[M^kV#93x}.+h]r0tux(4$NIKfRSy~9E<>xWs;x8VKHMZ{oUEK>kB4#4nE-qA~7PvA[htQ=;J>mq8)7Jg_^YtJI)+(vl4>+(4&aLtXHtQT)PbU+C@Go6iJ3M/|XF.9VdUFkQqXr=l]5Lw&M
ETwO&k*{ZIkEnm-zbTgu_7"|X4Nd)
KbMRn>^F:8ZZJb&;LetGP405+o+CE-Eh3k4L)plLfU<K>xV&GB`DBbGlP$<K]@t0.<<},wDLY+<kkdN"p4x2YLq+Z$J;t?eeb]i*n?Ffl9(radgg!3KX"w1AkIn>@_t3Bgm:iTl.7x=,q0+jlQbz-qXl4[me4%t
4JUGdAEyQjOT>-Ke/ACRAa=QS+U3mrM3CJjuVvvC/V]D8P*d!F#`>rIYplW;Z<X-bajTPnKd]w.tt9:qmfABD6_PbQ4*614&qdcXo/s#%tk|31A"TswHT6?.cwid5ejb?@5i4`6OI*1{Pj0sRi]1g|5,A74**[]$2@"HE|uEwPA#eVV7@FORyR+Lyw=S';case'ta':return'+n)V+aMDg)E&.O0C!Nl,>,ICVWMf/q!f0Dvvd&SP-N3Pxjo-GteO;e+U."(D@&BdIV)A?:fUZ_L7,b7?Gt57;EYgGmd"d6HCtJHw6Mqc|r>L~r
(z9Fx2PKX4f=fmnwfb,ay~=#SEs;Q{Y9)qL/SM3|rl#Kyef/Joo(dLyC8+I-u)P4yt!IJ~%
cjxf2QMFxit%Th8c:Hw0wB^aMWPi9m3[2l==2qSLNM+=]&42YVSVBstWLHqxf%yoBz*Ym{-0p8mtD.Xw)M&*p5Tt8"EfNMcLZ2og#hWT$6=OISjym9I&(JZNuu5YT6,?#w3]ExA!<Cf4f6%!R"a/
>rJb@pWX3yhw)*j)R"Yw/v?Kvg!Cqv1%}XMTkrHBMRq8vAzr`/}2ca/*Z`lHd*kouUd:}Z:f>FrKECUpl7PMJwyxB`_81:o^Nn
uI@K?,(]Wlfudwd/xBFm9hI&(vDe(Vw?&@k
"RqlRwG"[]*A)#5J244L/uwb`k^Wl>9mGQICY}<zg$n>j::$LggkkgTfFV9cxa9Mn(#qa9lJG",lKqwKm"HI$:]|O,OQs$k=KsY4m5/syrc>=upQ2y`:[L>338bor9BaHV7I/B(kna;@p^b&MJ;N0s+jTyu#DMqCJgUJ*tNU;2v)n="blf8QC`i^3&Hn(wO&+"Cu_IuSdZDh#!-S9s)*7E`<MprsKi,COHw%Gw&0SHE#aQ)G<IWD5uSp[vu?dg%iQyMW!iZPRswwPk,=4SYkxp&8?iv,VM!CYw3Wq$%UR|4Q$8K#!,72.x0S.94k`?-(#@xo!nfn2iDQ4,Lg&2S5G`2$Ept3Z^%ebvR`-?y}&@]U&c^nBZ%S@Nq8?4VJ5>^5632#Dyj-e}s0tNs9aro=+EvnZO%QTQ&"
)US_3lM8A$.B+XFcmD]+LBg.y)#KhIHX9"gS_R|gIN~Mdd*9T]+@x^/U#)`?$R)%~FSj~6K#[8?be1C,5:RfsV.n}`b`ww+[
)q7.dKCBu@t"a(%0*~_"]~+Z(Ng<)8
gJzp8^].pUB.I?[7dh2QXCYFBbz]}xi[pB(I:dj3^1}6&[2SytV%5[nEOi
XYBSM2At4;gCK0Gx6^TMb[vCV0KI!`j*/h&O/oTdV^vF>D;OM7H.qh>BbZPa/Mt$F7e@=vS9&ZcuwLCROsOa+Q0Lsg.[@"`]8N7SaIT.C/Vn?uA7Mijl]V5cpvcZQAZ/_N1zesW@r>u`f&Eg2L+v+EDTkjXubt<M;>]Sd{!gxTF>&N+#^z?EKd`u+8:^[Q:eZQ.#<:p#A/G`)-3P7^Smtz])E9u,-9>{,.hLsE?#2y].61VEp*KtsZC!qaVh<8dV_HAsh-1Q1xTcO#3dL]//h7!-$;bnK)_84U
ZT=k{AXKOf"20Np8bT-)Q-(mHP!Qg8C0P6dB#EUt{fAH<P-+gB3`mOpPA,0X$Y{6KH*Q^O93.^M?5I@WZe6oD(1*:=fE781CzlvB81OjS12Vecx8^!FN&a#@$g$#8q8uJ._vz-c;d2&NynMc.cv9+gq6b"Ss/&|"H4Me6i;O8lN+K"[N^WXT~9[24+!ZCEp*2Mb_LwagbpWeVXMy#:{jjiosz-.S@Gb%SD?^%Usf2ok
n=Kva
WAnAVy^1YY[t<9&G`HI;EVZDYS+<PJ:dDt//GsJ(3v!<>U@_.J`PgQnD"wwEj-<1A(uJA4D1rj-5ex]O?;BtMKx^9uATLsT`(JVvn>bAcP0r/qd1DZa<4V#eXJ6CB).rPL2e$koK>l]Gwn(2/ZY8a)/4}4ti(UW+9i/P"y?yAI,t:e{tj-{QAr.1"8^+Tpi+;@PW+0Q]qN6WOOAu*=D"}XC"~oVq/bL)w&Jsz=5<ms82{6XnDI{Dn$!$v$.IY<(mLEU!^-Q`?+B&yjTf<EAiFf>Rea>,.Mt`
s$oUw:OpScK,5zQLfGrgrIj?/bKzg33"=U]ECkcH_q2evmd.RdM+z".+Dl6tn,YXOtMO!Mep`E)nau.V-/]9N#Wo86:LRrRG51t#x}u*sceBTA26]QW$w=L3WV4kS1ePcGH3a63`BL7
)H.#/mBJ5vbPBT!x16
_-Y:f?Wy.9m4N<vagZk#`oDm5Bn@a`aV{&6
JD.t#N@Lu:t#p%x-v?J4x$)@YDQ-/=:IlrJ*]R*lE0Y0KlrK9Wz@<a+[XZ<QpL_09f1yFko?)i}$%TJ=zmByIsW
$[$vc]`Z,9F-v5t]lr5tma_@R`%CDjbvV(c@A5rW!0%Q+ZaZXIkK}fuOn(BS>ds%]Rr]H[w7,FF7s+}IJYu$&y-
}:d,|q2qU+P<X8"2]dL2gXFVG_HN1a<OR6E39DaFy)fDvBT6}DMp<YxmWl9/9Y,gJI^:.pkeajA8cFjd6(^0p,n/dkKw}=Q
8F{)-n+o<Pv!Hx-kj::Kdm2;foS2i7->NGy@cWg9;gSc[b|5dI[+cLE?jh~EH$L=/N/IcCk/ZkFPb05KUSf`<5i?m/I3dERlg#A8z*hg^dm[l_7B$:Zc;3:fUKl,,TrX(7:ky#|Fm@lYs*[GJ;w=}dHW(gGR#QBkBm1h8YC?)JB12ODa4@ivS<O^i+x]e%w`MknZ%sn10ZweMA[Y+%K%1SWV<:GpF2(XdsA-SewT(J/r}p[`xHg=ba;JX?iRZ[
o{2H;JJDpCQZ(99}hCW^-,B~MmM,hU(ZOf(Hheb`#V3k[<aFt^k=,b?LZ/E[p.Bt@2eS0g[o#+m.<7_xS`<VLVC^=hrP!O0^%XY(rouzF&m#V@AZQ8_F3xJ+IO.^RGTHYQ5z
xWJnEC(4|Als}0rh}P1oS.3ki
w=8pr#]icbtU@T*ht]kh.(UVP_YF2bC8H@0PGUI)NL"rA2)((9pK8>$-V7FLzkxQ`=r>AIHxj;
a^YK"F2&(~*M[+&XngK&_!Z~I/WT,QA;;Ao9<`!DC]VZq+*vN]K9I
l[c#nJMmynZ[FO7o+gX3TTWEWf(0TPCH,7GOb!bi^,!Ul8B?F<g74|$z^YS1)}J[pg8Zd@kIi}rqXQ,)bJ)pDUo{Nj#W
SPZZd,z+_bVpi,k"
vjm=MR;;h4q@igMS5&o{G4=+hk>j`cH"b
VX__nuek(ey4P[tHpcJv-|R)Um6PH*h=I/oO`@eg>:3X&=`}>h37UEjU%W4c+wP;o0(JKMCq*e%]&hXXw,Ub0b:UDCLi.RFckz!cvscV"H2&_/k7Fwm3,{0V&>bQqh
B+:V7&n0o_CY^_sZNuv5oQPHTi=;Wi)8.7<sdN,H$YcWQW0L-rKEqj[_DQcd5F]pY6pPHjH"aSdoSY/c
K=d~#YB%ZY^]:UYKhY$Ig{#e-3SlSS)]XE/KvU)<U]_o$HBDiW[|H/)py8q]ZW;ZN1`6fEiwj-"B[",K<e&E)?`ecdyT-WmNrZ_hy(U#l,GYabF/-kZ@PND|lAJ_mb[*@MJOLI3Jc%o<kqTM3Y7`IuI)3M+@a$Y@4xSLcBRTdveZR)[{cQ-`K#0+GZlx:G.=,9m(^BtV_;"fCOMfMV"<$@Af=@SnK(0wSTsi!e)S7+igiur%=
&|D<p]&N9Z`bH7E7Uxwz<|,.(_f&#PJB/ml!<1Wb.LRdXp>fb1NWqgiNto.;p5e@CCcp
e`KXM[LZ-",1ke0?n2WhPwfja?0u%=rSkw"7xC~/FGE?scM.z>_PW$1Iz7.GVV+n_T=>K8cSb5pB,G^7cD@KA/IPPD:L`<m)9XxK=&QL6jtj/Dr*O7^r:N+u7

kQQr)<OO^QFq``;1T_;hmv<^CtkQUt1i:><|&QSJQ1]$mrfQgYOJK->-X00O1w=2d6^"vW6:btcc1csa-xEd
B)E%sSzZ$u/$[V9Z4<$eEtoi1T?/GfXqhrfo^Q?l}w%P|-zxJgr_t+~qOBRsV%2oWC*W*=:SyW!BQJW6Ox[_}V~OUq:3a+wFA8CPgaTN}wG/}r|#^5=5Lvt.
tOa{R"%l^eKpg0"8?mk/u,C5k!n23KH%W!qo"=i"uoSiD#j|f8Ntm
JVK<X`u1X~XQ-|1hxNU2yBMBABird>Ns
ySo43O)%d>
bNH"VK8O4ZN`b^.g(-5]Mg?q>h6L@^Q|2.It]Bm*!`a)-0q&C}i.IsQ$*-)njKlE`EE[(o$SYzilrt%4aIDGOR94**:[V5t,1`Ug>{ScxlU:ZcL)iQD,+~A]2phrEPlLfyGTU]u3*Z,RTw[#aVI,/s1i7S<xS:cHqGMe_7>X(,#Soy1o=3%0QxANYkFu8B4drjX11K]!u9X+-t
O+v4K7>=_.qc|i$V9y{psL#ZmF4G1C
/F4s6avD4PhJ!~OYAivEYU7/wGo+Ny+KkOjHd!KLcDB&s!-t.n
nxjrz$Npr4|
7lb_X5)=l&$?]L=*2JKMIN$5!/{F=3Is3vZ,!ddw/s.UK]}abpb%5SE%qaL?;-(hu"!MNltIyey0Bu6q+wgAq)"c!g0a82:fV($RR@=XT=qmZ5+P?;=2i^H6BHX(8_-qaLlqCZ>AXXU!A_}@u,cQ|JH7)VzTW^qo_tofD_UH,#+y6#UH1@M)mV%1"i
_OV]8R=M!iO__mUU6`ihiXE(=7iqo*,LKfT^Q<6C#*&|[IdHgV
E
vQXNr+e]OK#A+xO5D6wp@nbV7a,9Zw1Z}8=j{7*[Q1iWc3a[t)[]B<GcHRN6/[`>UKF`Hy+rxY|Ios
JmKq4HX;<g
S5K4)a2={$O)7Fw%%m*QWSra"4"5oWw/S(@Ch:~v(Vk[m^>9qqdH?,pk2S^o+,%]>"5ial79O

,^:Un1ky2aHi5B]+"h*VwGj`
)IGb5/A1lH(C4A
=})?3t)uNq:$(1NG%Lf`c6t>Ax
{%K([%cp|C6G>FFVcc1ie>$`xj
.trfG$x5:T#Mq~t2F"=5co.c0W,ug>:,R6J[<A3"QaTY5VvArdvNfkQRZS$5fbR66`q4a8-SO&cd%%t`j_>dGZ0d<#&?Q06x-42l*p6c:M#i&Ij.h}1Mr&FQgmynPC/W]2M/cb!>qFOQ4f7}*PK8;s6>;}C$p/wp&(Y
>Xyp7,1y!3g1p%H36<M=M6l&-cEIe%0htC.O,:NY<C#I$%kz3+>I0we4`Nqil7km5~u+b@9k4++}[!g~:="I&3X.VGWPC_fq:KeDTMlVg"^:ALiZTm@|g|KlJ7
1C]d3>]^,7;
]Ti"Fg{D#=gB.ITlmfeLg$Xe^xA@_wfGmH,2FOVXhs*>S.?jcZ6afZlN77U>"IEI&_5A6c~5L;F!1nBpnd3Hg
jawkunRp;Srb*V9fESr+}],c["2iKd*Xieu7
^lo()12-KO_Kw!@O!NqIDaqa`t@)By;R$>R(
WM0p&=7RjQ"MFy-7b/
nYv}y:TOAu^*;0)mX/EI9lH#?=?kpN&M
O$bNf9yTZ:xpcR=ioND[nV74@^>EbS.YtP9z$1xseCByc<K["NO$8Ds
EWr;<[&3ZewA*TR
Z2s/?kZfe+rDXaE)(Yt!bSI!.#z7)l3e21.:Gg^aFU_4KlN#]<;NpsZYVnM%V]u=Rf|UdeWIVZ[fSiw&]R=Pgt=nv!Q';case'th':return'.c0Q<bsp=,|?z(s^wd?PO!=Z{"+S`Gjeb-9yw&=dX%W]?1WK@%@SJ-Xk_ij$l"j>L
{[,q&xi6cvH
eo#dd1q3w$h:O<7vL^+v])AFMz$2CeOCMDckNKtrv_`AM_D<eBehqA,VxZ+y3=9vMnSx?Kt
v_dmdP:n[UUm8TwSQrhEVIv>[CQa/Gf@_X119haK2D^F,^0iT80(Qof8OroB,)30ZyoT{X`^BMTr2P8f,seyv50<yvzp*KrW[j7&"JBk(@VUGi@++v*3_v>mJ>jOob]`,;~xaSE+9tT,<f^mmBV;<Ip:
H2G5t4p-HT1|U-i66Tk(x%_9)+Kt
Z:1KCn}nponT1wghC*{+:&|:U#@v|_&5:DkdwDCNl:LHVKtrjnXHg2%+va4PVneoVwiYVE8ncQ|`$DT^4,;;Ex!(dO84Ycoj7!gvoygad]sp?yNO8styp*[J`@:)Rvg_S$}I[dWY]loqw75VZOSQh4g!6!O:oiY&&$%^4%gDlvV&s[0!>fWvpD>&6w$d]7TUeo&44#O508K`bI[mk#5c]anF1<et@aS^EtxJ|k7Hxx7;Ar+Kntt#*W[ZwF$qaLeJJ4ypN%Pm*pU(?h6H]yImd*xs~"7uj7nu;onkcysO7/YnQf$k=(A<!4MB`6Lx"_Y5
HxoN0}dd#y3sm!R}w%`FQEdQ>d2Z#;vYslpo)}Mn0e,WHyOSL5?&PHJKi+;4J*MJFiM4e@X/VX2<IErHy;7Nr]xEkLB0`w!Xi@`V6s&gV8bJeyV#H[HR6~ylNw,;SR/~,mCD;!7u+~a>M`uRf`&:X!iVfYe`BO/:htH_-T#".u^O#O=p2FDJZhxzd8iyF9pg_:uKe6@Sh^8w;djVvR/mHe2w7}W./Z+0WHhQZ8(
.5Q*Gs)5nN*DXK,U=.X2o_*m@{lR6Db@,E
W,-bP,vcU&Hx5R}L0nVWLg7K^?$G;7e9(FcT?`BOEID?Q[k["EKFMo;O<L+I]n)T9JCe[&)9"`afBVZTjiWR(&Tj8)ai?pvg$h:e](=cr:mL&d*Vz
)x{ip3S5t1]C@m]Y1AkKNGK^S!Gcs+IF<!pHTw$[t2p#05;u0+m6r1+B1ZM^</0n
L.%"*=Uix#!wE*k7?GnijJ!7&qE}(bSy7S%p4fBm#!MsE/WdE~8Pw_3`tU;"W1KwXY9I#i>[xC
%2=5v7>W8cm
&etX5&DqaTCGP$}hNe>QKq#.RBi:_eM)no9:Je0%a_~=<[(@Vf:u{h1;UC~WEJ8.Qy<ibq$QL]-a4,Skk4~Q6)`;[v6)Yq,?mpf9cH@Qq/KnKMq@+WRHGIRTl-NlG6G.AAWY(+nkWCxRzD1m8e;hw$e_n_}C0!^S5k;Z4T7a?0{I-g3QL^7Bi;V4!X?L%4j$ZA`H}c27Uilu,VXWxr?t$"~l%fOHF=;owiNNpEkTu,^ZPhQ)FKtI=<Iw$SnqeHHRY+}Yv+xwPWW!eF9%J<`Fm,FG7S0vuC@2qlKYi>g$l=QPR2ebHc4$hue[rWy=_x
o|
)D)b=.A2cvaQ`h8XbhS,BuIwQc/dVRFWx,`MeiB8S`JGm>V]kI.Xy;qgpIiS4j-jdAore_cO[*)?VRe:-=a4d44O&9u=v+heGDN)dJM/Le,F6rZ+{_0p1R[723c`#vC[jdJ^}lnQ7XSXYiI=lbhozM#4S/hdXa<oF_(1@8X>-^thC]^Ft3<-;_Nhz
2S)<"0}k1E#0>wk8c-usoI<9bunZ`/6e{]q&:ADQ
$$]tfPc#;>]nMmLp2V^wsL%ly<wdhBwE(,jlV9iwR2)O>Unju)u;H0r]
:&)=9>ck0a4&b@6;mB0T~D7<7%#3fo`^c+r;yV9R?9%Fg%=1rjo3q^?dljaVJ#T^$kUudC-n)_S$3-|url_VoY)Ae5?XM*<&/C*1r*Ge_@M?hoFi[$&9YXpE@g<S{HThY6WWCb|htkBnijDPB=mm6f[D/,QSaa}P~t9`?G]Op]x:(QN?<"0.mHN4f$Z26Oj%s^~Cg)*9fZ@f^txqMr7(J%qCowI%|6i9aU8t6
QmDiPWi;)#gQc5AEJiQ(<?2jBuO2wm,Dd$}"6:rmV
ik@1Ee>U^Qby=g1ImO)J6,aWn63-f1?lolL.{+`IgszSI0}enJoS53*QOE
lDJY$*!8^0P[P.s^PA+"W?[{yTy4Iz;&_Xl`a^T]^-[4-ka#P6onltqe6HI]X@0bWtS)At7c"RKTuV"8c.%$/Z=86_9]G%$4)[j*tqtVtpq1+G=fk@`#":/Ed7RUjNK<,0y+&%I`C*x3I%+H,;,`_d`oVxucp2KQ0#R?8RTUYyN.xXTj3p6#$ZR@J5G^Rd#*-1`!9A^pj#f3(b+zA(.9[(/)sS9I>ibqq3jg*R
Av&MRx""Cd5C@.45_Q(I@]s]h:tV5)KXz*jUxbIEVanhrSc){*E%me*rZV-!zXs8dLtc($(1}py>-mU=RDxP{3H((=9,7-}8H[t4p
EJ.i?vPu}vh<raOn^$4m]*zEfGKhbwY1>q!PO"(C2K,h9_
A0]d<6oG#Kt3SaI,;8H{b.5r<a9NK.6O_X.2;<
BfO-AS-KpV_fqkI*h4}pVap2WH^V[Cg2>s|QlRQN[C_WT<5KuVbn5f5D*7@Ozsj5*8])%T?/]xbGcOu6<o-_Kt}[mDROtgdb;8eV6QF<BvZtvo$Xzu.,,Yqv-fZCDhRd$75@4kkv7t:tpo-OKDLnAUwKKz#wBD]lD<;1
j0cXj%#Cl$:_YzJWP)nX;1w;-s3>98rcMAj2I$b"2Y<h0NsW5Td
Ng=[mCSg7Qlylg:<fGiYyiSAP;l"BZ0ToZ4>GTDtb0`[^C`$O,LTbnq=Lo*LE!HK@0(<?--QOcU_"DG$0?mfxp"n%_u,mR"ns4s/JJ$X#5Jab]YKu;d&Rbc.dFETdHw7Api#:&innVgx#%rr)ow{l-c-mW#)T*@mb%ZC@"L0Aomek+]ibO;z0GE
purI2e0Qq/3CXPXxGv>I]Mq60-P6`}o1.-#D(J^.blWjLPy$;#5c3-9gY%/$Gz`HpCZ&
Z5S)(Rj$r4v`sHe%/x.U^ordLGd*af+i=#/<.jRch`3_JDY5c7JOR>wu|i:aX0NDQEu@L^]q"
/]F(/LP[l8DgmSGnpV;6qE[Y9ZH.mM
JT2{k/.,Fg5oNAPgTTX
*,pen{jxi;Sdl7]mP_p
)}<GDBLJ0e=%sV4M2:&#k1WPa.@~NKlz
Hm~%sfY?eFiP#O"p_E<ZI%e"5L`ic$R*=A+duDDSXob+V&ch`$@GPDjFssrL"]mm?]_f0CbU1SwCEB77X5fVfKO.l1!
A0^YmBe01#LDyT"@#pn67!pSd6Mvh^(=qm1e^Eu8EV-`F1v-JLYl
Z*r=XiEPlX
WrB
$(=SA(W3v82rSb4=]]y/k?rJwM(?dQ2PoP`6/3J_:f=ST(q5H-ccM=/)>0Tl]6dQv?#PSAxA6M`n
%iHgfbJrg&E5SK(V]"!vK_HF(hEmG|#M>0r7DZ*($3@{&Wn8xWe@aVrQ@|e%.T*4Dv`=qON:"4?<TOt-A)Dc&gKv2bN{V`;8WR0@4*VVRT)-@0vGfkHxaTq"iAcAucjWxZ5j/DPs^Cb!2|[QgjCZ$vn7YPc/M>U##[q4UbG0a8rc-Ru3jmtsvK?gV=5Rv[#*W36c,)R83*dj5^wuA.i)<r)s`UP1X+=A
SDjG
v;DwG=b8%JiUdE
@VzW{m"MVD!(~K|:"H.o,D|*|i~*oSragFcH^N
Y6v!
Z6XnASwvkDvZ*_S;rrSU|WOcS1tJ>)1E!.M^+B@Z^8haJ*?==[o;Ly@"KFbn#La?ywI0X><Wdga^pU6)[Ui#:&]_O
asqVjbO+=7^gdXaJomgKP)0-xKKDzMm#YYdtv@F/,Z5#B!<l8i@M"%v>b9DsD`Ea5yzHgfBS9&Ks/hZZ$DduiT~ZM<,6u$q@G?+
}f!>QXTy-SwZ
o&T8ovpOl=?#.o6"WIu**LI$(1azV,m"I1ZKMDU<n?GLwGnQ$[COp`U}g%X:3t?ETc*@Q@4w"mas1]L5(pX.O^e(NFFLBjo<r9#A_7/9H*XZdrE
`UZy5}R+;iXv!m&(tEl_isi+&bn:6gZ1V>$@La
}3mhfoNvQ@*<;0xU!(([P;:T3=ztndQ^/o%6e`%!^G<MoFV;H-(2]AM>*ChNz7`Neo{C!r%33:u%}*O3ftvksSLHhfjdSw_cr/|_6ats3ki$(tel
SWCUOu3Gp=VxJ"%m,$7FTynh%:P48Jh#M`xr^v/^i-e>N=ouv5Fn/i#,sJ#13hmK68#s1;TKbC$pUEr~fYn|wjc(m)hQv5"{"_lLH1D|F5pH5}g*-AYG
6IiRA3ris5(&n
S<@j]w~B%!pS}7tvcngX2hz$aCQO;E5:aG_fk2<6j%~l!)fWFEUR1aadR]rUMuq)+>1(,?d"FEC,0ooV<.JyZ$vLm4gCaFeHg$n.f#fh]Ot*lnwVG&nvyllQ;6PK1Xo7,Bo"I]0[I?+@}7!t@*EfeO%j&srX}Ad[!
)YL2EXaJ$6"!%&jR2tK^7%K;IXw3i<X6;K/B&
YX>pc1{VBPFWM9*1-s%PTfT_WNj`jaZu$uRdZ4p<u4m;Hi?D^7m1%I-b<Z$%*<*&4
<bTd9Z|etPl&kx!,s2`W~d(B2Z$K~r_"m`?len|h@(hH}V3#,_TLob^7XSZKg>i<d+Mj(h)*dd/Rb("Za>2G,
=)5UYKi*k*#_4YDN-(/5]C"c7v[o{n#&XV"^B@Ng;$M16be1Oix0eK88L<{+3@!0oC5ym+8#C>B;Dx`&XFV+Pc$^+kGi^DgdXhKI%:sMnv^@f,#K{fw7"3k4Ti0[hLG_p9_p3i-Now@?~1DbZ34#w3"N<V{^@
iv[X7MuHWt3R_
p]nq_0ph3z&b]tWw;SPv#w=RM^j,6[McxT%AI"k,cr*I?rbDERxluoRi0dwuDVwl^W3>S=3a%+CoVa.SH;o_+6n2)r!(5W6)8tv(LnY6<v5n_BiB7odi8<<&<>-KIB@7e>P&VxN
;w:_5n(bLNs_>^N)iQ_Cc>5xe""';case'tr':return',X/KC6KpM,z0dR0fs&6FC/sA8JZ&fxD(Re45SmS")cE*t:3H}gS*O/=!WN$R+b%Bhp&LRTtl=:a]*;.1(oeP=QJl{bWJHb7r<h{w?GoLR3FPAC0?``])bJeX1_s)Cf!(JS`O1E$;*r_O*n(mdX.fJ*!-YV<=C$Im/s4^eD3c*A*6]3I-qq[_Mc~UoDTE!
Kqf*eRz]m><rp`tDzr<@*
t?kqTWsaE!=^!XTc3I]ym)-3pd_BW
L,;R&^=99a{7~?BDhyvhNm<SeqAC40-UkuX`F^uBYxDs[DmE)s
y/J{w~n?<|S<w01#!cx,#<CI#ud=0R->Fm)%$l*g%`/$d_!z(vN^A[YshVPEDx2;:yH"h0CAP?>
w,3P&7YnbUk%/-%oM|bv@[t46:>
o4Neslf?06PB=J>l4(H`efC#WjL9?9K`X#(C
DBtDV(;)I);%<UmU7[@A0>=:hBM:H8r.!4^Mhm2a@M,nbv,l(/IjD`>=`-LJZ
4Tr]`ef23,mA?3rSf<qhjHnQb8JOK=Q@qlTg?
&dis=X]cTu9Bnq7H;lV_i4:VJ]bT+Y|2%,?0X("#28UWxClU@8L>_V+N~gl,wh<)b"^Wlj7c@i`(CrSoj1Ox:yT7&w?*"5xyZ1M6pI,H6R]:5K_<q4v2e4gocH1[AB@k|+DA=Nx&P"-tRI6s8t#u1z$U*EW^CJ>Q[b&8".
Lzf],]LWRkLWRS+@EZ*ho)7or~,Vt?4QRP0*nTm$?;gtcPpKwib3p"AV"4>sN.U}j~ICRHg37v)Em$(nDh_U!v0Ff.W_/
o/igLM8}p$v&M)AT&O?D([=T$!3?+SxO<PqY)sl3C|]$_P%xR<4{0TM:ns;qbOBNi@W_%.D_mdFi,w-Hk*VzP$n<i.@3K8+t3L^k7|)it@+9V=>vhX7#M.b/T#6=PN)VU1XQ1Tuv2]@}bX2btE_+u]:n_qKs>`&eku4_9HN8vhG{0m!7b})6
/3%S#V$&61).^Tx7hIUP^;)Ia@;JEm(E
Wukd=xO^F:JOeU+-a?%t^
),7]k,R}Pf4Oc|"YIX!NjqwU1iZ?k+-;wFH;($WMA9?@uzfzw+N7X6O1
Hfcf)N[bvl:qN&sR"7l4mV1suT`k0K"r*`K<lJT?J2@K-CY6/$UaZ+]XL?uxs*:ca):k_*3m1*<:vxV7=1
fv<-jx$_aih_:ARRR(/UXz"",Qph/r7/q)[utM2~7O=
Mlfpy5.P/j^W?w5Co$Cev+W;CpoV/TtDL.Z8x*ENC5S+72<22,Yh8MEu3el2LZHYEm*,/Ug55m^e<MlIRWQ2baSWQ]?pK-M<yn@{ft%HQ,P~Bq(`=Ik^b8kaL2>P#lnH;;@f7p:#b
lFFBfW7kU%.o+?oDDMTSnTr+Hxovt7wr[.$<B-Sef}G.U.EwD;#lHxTM1[-pc]O=[`cj`C3{u-@p3m3"iNvzMaEH&N9H!w&,D0CSl@iGUD?*xsMN/`a^39RQa#8)M!QSXV,1>j:W/3(fc(<`T`q[A0kcE[5$C[U,pe.>%,g#.bL`^S<:%NuS?6_7GUdTywVixZmpx4TPi=@M&.cy*Q:L]
%!+1)LN7o9*0d1erdjo4U[^cGNBo,[-RvL?ZTf+Y4#>Ry3g<FQSsVHK=CB*-?ZR!!B:>A`kLC7wp]b^1o|Cr<O/,#S
[qQYWdr,`HDF>Cc3Zo"$uwTfcAk-yM*+E${6CGrCYrE-O"wjQFZYIF(+~OP8)b`Qcr1x59L(h45H?go_&of:3CEUF#:j3>%Xx<KpBG1[~@ZSt-pR?S!X:#8,#`IJnsxnbM>f`A<=>nqMNhvWb">x:.VG{05RUkk[N_)YGkEku[$jmaHu>r@QT_0
hN<S$^Vl6D*GNq248Qyl~?yn!n<E`_;R6aEml3{j(;}Z]GemS1^)=jg^}"=%uVu9F),W@%gpx`tv[uSEb:w**RG8+A:+bG51FM&<[/(Wcinw{GNXpR=(*1VIe%%2,M|n=;nWY@78/-}0f+~!E3IEpNpr&^LeL
(Y}rpq?BhsUiv!VOIa"i)+T$;pVsoNYej1WR}Zms"Y@9>)f5wc)3etMYo,/?1<=-aZJXY0mAm]a/+i_K_a}q=u~hV]y`AD=GVQOa_8[eTXOq$AoAzNo7N3l1XUPyQeV%8?j-)J%9#jcC}$FIClldAxw*TjTNy/_<k8|bOFu(D2kM]^^vL!`S%*
hk/qUfjlbS;H02VHD)$a`0D0oT*B`v
iLEU;_LmP1bb_"%Vhp5T*.@#Wj~:URxrIhF!)1xkE^m+w*1.q;vOfQD5)Dj,DPf<UP/POT,VKcHX998#J4@]s:cx-)u<ge%epSUtem"!"Kt?g/|<;g|X29Ve)BzcA/1l$&yt]HY;+4U6@/w.&HRYr%tlrl|jTyfoEH
MDH31L?tqO2}%arG@J,rX%!l!vR)eZNUd*[#*?d<!L[Aq08IN)W(gVbfp{G%Xk-MyP#u>%TC"]$-2[7tch%k`sR|tW5im@`2bx
M<V]QbcD]v~D8+B)
Ne%aOKo-H%asEsN@g<J0?rDk(B#4d[wQpL(=
y[;I0M*O-[%eK0wd$JS_#4%R38}oX`#c*/OXeLLRV@U$cU&w"c,1QkMhQ-Rok_i?9SxxX1}L`QH"5;jkuW<&[0
naMWQ25gl{6ylFPkx?,H_2%^D$hG$z-
57
Oj4SXo3rbEFV?wJhr<urWv7y}=4:5q0j4<gmJ.&lxWWID?nPj]D&#5zUN$^lefilTQkumJFOxomB]d}]=J+`$A>CA1C4i
%;&R5#Um/4wPHeg*i+,EL-?4:]@QBZQ^_f#(Y<H9UE;%kxw=*,vp8KL;E37)cYv*-L$A^C);,?4ew%-(7IzZKB58
GQHpqCcSIuxj,>=eCBEH@~Ygk{@)1LLNnj@[E0haXs:dnDZh0S[ON1V[g#0o&]kcvUKIh/gjvj(33fQ30q0>Ce<jQCgPp(9*hI,V!@OB5:(h%=GiJVC/B*U!6|y?hN=mk3BDC<22Gais^1bETBfd,hnBUFm/4ckoK18*9*.1cq5bWbg,qIKZhUk.Of9pXf2~"LT(JK*pe%my.7U`e6m#TJ:QIT+QS~=&D{yDckB|iKnVcP"Zlv3I*Gd(w!9V,wC[!n@P3.%v`MB!KeO%
B1r#*WZ-V"Sua
95A9=I_k]T+Ky4>53.0[K,eI&46>t=Zs|2#iZJstUu.YVLt,:ET`Cs;Hq&ZoRs3h4d;K7e/MV1D]7My9~m"h+:q:2jq@>9m-<m<x(Q8-d5;p]S9loOBtK7Trq7L$7hbTt9~I?7q:H2nE.
v;Vl=@l`>-l(B3Td;6XB?AA8YaTr"?R.c*V`*+*JA6Lx!Tf(d_^QI.i3
(YHM9odE[$rM5$jv)$OR(2heM^inc"VP)J%B!m-9,ZIJweH&Q!yfz$G_pyE!I>p*jH//>2J{Jng0E>;G.eMIX{qU;9:<SUV;j?4V9[&se|=tY>^yYCU*i/dT,GOW?u`FbOh83U9&K-o><BWqsl`2?=9j5J3V<6ZbNVk|BbMLFM$}2v5^-8LhE79-crN>Q98R*]S_EWQQ&>dZkDEN&~JnQCSSQ/73H@[pa{De
}6BVuKxR|Q<a*:t-:2#<%Nde^!UL#QX=ijE)[Pw%v
*>H]Fs]-oS^FTQ}H[eJe5sd/|-eK|G(Hgo;b=MaQuCpq~Y,=eo74`YZs$>W2ovc^u?
F&lRo$m:$uE^1WbcJkhAk*w1Y9tcj{"A;Vy*2=T5N8y},*!lWYjU6U/p")d
.1GMm?A%pfaYg^1E1}J:d0a=QSB^*JW?s-KhCI5AbCiQ1b::I/8=Tu"E:W*-XZw$`XCbQrUOqjq9M/U>qzJYWY@_PGu&:}34asDe8c)ny(3`O9VneG-C:mi1Lh9c!A/ncA*K<OjqSN_s;ym^G)0*qd`E/}#boy8:Q<u@83]L$p^a/h][r@UAC.eR65PkS}Fdy+RAC-Vug~=%Y#pY1E!+b{U4Y)phE.o#PxS?^`Qe_/?@qaOh!on
QN7$:-Ak]Ph4r,<NN`Rl4}0aJlNi`K_|3~d;"@gf;r%qNWi.k:dZSOK^oN/25m9
Hg/{;"[6>80MZ{+(`n]h@)Y>#8Z}Q:x+!``EtxIH@?*vsQN^wp2d#pg3!+=OKX,
/dpX25W<T*.$RnEtT+)z0X+(?OuHbk#[daZO;*[eK`38y
]^>UKvN7oP!HM`RACBmz`A+Mv:)De^9}s[)0Hz$uR:<.8~y!qjO=`)`$!lO&_|^Fty(2loK1AAN]QYS3,cjpiarN%gDA:1X4RTQaC22%>1,L87to&e3DG]["u,(wu)]^D_LFCL/WR!A_)vlHQx+twyl}q8*a^ME(D+%U4,F;q(ADn90vr{[&v^(w3<cx
t&,FRSxvOwgD=qs#;a5lnnDKk4
:1)D"obdyf-<*f*eT
DU51;#+I.&^ko(x)o)';case'uk':return',evKrbP.!/e0TY+R=+^VdDXhP`Yt>,XvaffY1fxD{E,Sdrn[p^IaP5VGNa[R:pEpKK}cM@7.aH)w/[KrvIkRbmG-;%XK;]0?Sb??HQ>Xea:s^CUE`<g`KX~b=m>B|]"s.k@PE,z[<ro/GQ.w})(?}g!gg,k39(7cBH!m:HGBIU?c_@93r2F^KERg8Yjyb6=Bh>*="28EBW[iz[?+vFhD8n9n3OayBX$tuY$Mdn_:eWyue-BA.Q4P"tHi80?pta3hmWOMxXrXHvgN.q:4i+zE<R~p,Bi$Bh3>Sue(^6SRqe[g*a.h)^tCTEQr?yXqng
W8B{"=TBDrU$y><fyOuKs8``S$FxV7>bY.tSDU2;
YcB"a`oDA2.DA1ZilmB/|gb>_0%/s1)0F01-VIPPU])X@&ZAhF[/b^$>IvR!K17V}iq#4kM!;lQ^.jAm}9!h*i<Kt;_hzg&X
2O&i7ssdTTgr)Z:@[KqZ^A+{;i^>T=_5;V66IO_t=p+15A<%?cJ>G#lH:ih.r=)9s;)^SK2;ceuwKulNm5u%cT73y{YZ?|4^;Z#b]2HM?%]F9Xfc,N!#LzY|p`e`af-t1Mg,lp$bS1XBeusy4Jl(:H2/^eU/oK#CE89(f1[u]Z"(sN*IHn
yV.=&?6@xu/vE$FXiP+v<qBZV()B_d~d#/%b3)b?>#)@G(oP[7r:=YH]<uok8LXaKc/_.?"
;hZ?.;vT&INoT?;#6&il+kJ<h2g1jkXlgE{UZSXmRN-s&8;Y@M-v%1z,dFD0m;j>u<U,_pbw8IpD!U%8zUho|?F=FKd3;IjX[;h+#tSEmx{ozkXs,=q+R7)5/>A6JCb
"uak:0%m`HHONR8[GVen8Lb
l&K9d(d4S=|I~v2JCK^`FD~9s9DJB,K)&OReH
1USB!"FFKrM/#k/+ky7,0l6t:Dv/4+XkEQ8
};[FTk>geqe1-Cxq))>r[AQRk.oe7h8Jh(iS-X^6yv_<?*7?+rX>+,gBNRq]Y;AWz_]NNRQo3L~vx9G_0VvMJ#G
GMO+f?2n8JsxEF8m^NQ?5!m98R9fd9?pBN1X
2{Ikj!sLdq[IE3recUc_!EgN;4?LG|0&La#2=:Kp"hHY!V<Lr1o+Dr1B%E^1RAeWWJ<V4h=yxhau9CLzkol2KOolsGju%1UN
$?L/An-tK`k03
hmM%T^2QmR.xIv.Bo1<+,?J>f)D+I?
Q6_|?e<LbS%pTsRxt@PY=[Bc.b=P+JqB/O"~^5B&[r$Oq7+)n1E=,ZFQ]usSb8Rcdo?CT?2+QEoCB:G![~
s-h9?odQY+nF=R=-V^|:~EHIU=8SRNkUj75q=?<?P!>,XS*$,T$f5XvH263jF2J*0xylvh3GD^%)(=pn8P/ADxlj*dDYxTMe_]HJNUxJ-h|q<(9V4.2vA.1kU;X^o_B
~=ud=vC+hJ9"x
Pu}a&-evox^:E:943?!bW=j/9)Yr%`.%`>_<="^mJ@)wL2}]p
g*(hB,u2hN`Wj0XpINe=3:^?B&N4Bh+LykO:R;ioZl8S[AwtHVpb)@*2$r`<+Yb_bvr5J;qu^^Gv,S<AL96`""(_a"m[6?f?0:7gK)G*V9+(~UPS)
;!8_-P`X]DCDtXu!O`n/PLE:IUI7[3/[Xw[^R]F3QB|,n;$KidP0B[:ELm:=rl4fU6s@H$qO?R)qpA%3*27BmVCclih)V5V.eS/@_SM#:L;67B2:ovT>o%N>h7GRb.l=QSOcpER^o0I42(+nn?*D&O";zP&^
>fipdr#%@qyGLA[Sk
r)@]M/_RvWh:Bs+,+xT@!_,
nnNb_<>w->p~B!-u_{^BZ^J#?l=2$]@}U-tH.xR=*m^.>v6}F&I?cAgaV/klp>-VbXZ)4HhAS6#8h|4[CSrfCkB>vr2I.ST7;H_)h~[G"V_s#ElewBxyLj##=l8RaN0Uwf6OYj/]fK75?lb<-%JRY.7}=0DFG,QvST>s]Ay)D4:VM$4}91E<;1wLJ.yDr
jr?M*IZOm)DgCqD9GTpb3Ef4,23[aBI^#<[i=(r]Hj>>u)cA5"4j"Sqx+-Z8H[&JH,c`!9s
>d8W>0*-hlC-g<rIaKvv^p-^iV#a(b04KKxKgT/%v|vYnh(}AmV=R{rU=uP
su,kd7S*NQ=3!!$-3tI6@6hv9ZRD(vR`aR
s;-(D[e`(>1XMRiR~1J^j$U0|*`aQcJXAVDL)k70B#3]Qy~AaCdMD(Zion&%hx:FvPwI=2C!-4D-D!C*gm(3oOLMW95cLa2DnKf8paG8FE1f;Zh*go6BJtXdo^EuHuqvj/-5rDtS@A3j,(1am2Z:3v;b+25m?DQ$0T*_hM8?omM6r:A]u;}_
,4F?9clgM9`Tj+n
oGc5J+1va]/vEt=
V5Y0]*4@c$5!pvwBXoi@?D
tj{(plYcs%p8<@[jRfye4X9(7s"Ra1H.uc_31PHD9qV4tssSV>Y4tg|$iG6%}MhQblJYuSUYg.Z1=2JrsqL9N1FM`7b!4^^=>t|TIej@K]O59Kw[>s{iRq=+-R0BA2iI3!
e9hL&+
26;"E?hT.8gyJFx,3#sfiG.gJvH"~
"Z+;<-^9=Lf<=E"Sfa?Bha}(wR4N)au`/,&b:H76@r<ZN`+W=>CL4r@ZSxV$fcYMaN
;OZrP=xF:+&",7+($>."s?
wQ:E~T7"#&ObnX,e/hUM/5
-9<jk~$u5OKyciL~F6oVguf.>Nt$^Vr[XvU~/&dZU>]$KZdmN|%H41dd<5)W>$Ux$=6QC@tYom`mqd5sn#8YtYDMv%Lo_X*a&X&Lk
0l2MXcz(m+LrK:b.oMJg%rT]hb7HPP5bS0q2s>&CRXAO>c"5d3,>NH0gy,i,M{wyP5`GE%1cXoG%n[,l3(e}]cw$ek9
FGC7a~ufu5_wyd!HT[F.`C!4X?T7!5$`fl3VO+b>FP@V5)Qc#,mEky
*)L-fi|9e@YLPfX^<]DZJ`[?@M
>dUSkr1f2#Or)4iTa}H_vf
wA]IK#$A?Z#CgG-,+&Bx~GK(Jrt(p+QO0dqIPA-GnE4YxvJ-R6O>o^<pmy%^T0Xc_(wB>
M9kSCu8!;O_:0,%!H1u[78{HO?4S.]>:+mFs?f"Il&zWUwKGx[YWsmWGD)?K%;k1L!/gu/B[+WAE*P=a$a`WRq9:S.gPU.i]IqJO&qDn|QQB
i~Y%r+D3
`L,&^/$nfY!.V>fIvcyO!VxQGYJFbvo!,f}kTP{nKmc$~;Ar^11X:/1@h`{g@1Y$h_:iOw%<R-Fw)YO,&l"?7X*:e&cUTn2LlB_4(P`J!4tvR*[>H0;iTW}D(uG/u8NT}dT3k!<Eh)NYvP%(6jFsF=NR|K,WD?(Q)wCX1oljIW[J7Fr$D@P3ithv]aaVl:7-5FM6/!@UyUce,-!PTF^3-XBdwJ(KPVj:qIduP;q!},fx7J5VD;t#KorF3+e"SjmsU..V
Wz)PRu:!&</Q?YwT#&>rLX00Fr>ngVR]Zu0tF=jFT6N15.(4VnMJu!$NU
rh[.XB&6z$PGMIZ/=|(q$NV@lW^e+MyBaB)"q/3ZTq;90c@zUeyS.u
Gyq>~2D3VJyr*hVe3d;"#P_nvMr`YF9v8O
QxL3?T5-sMWL">M|VLSpNZ`Q/4iQREq
,C^<v#^v0ob}qJ7LU0^Hrxm<yy+`"CO4wVH4
$NNQt.^4#6Fyql!A.0KrIi?Y7H!qFk<QhggRcB0u_`~g3TAUh65UI6D=h[=O8hH9>4SG4HZ<M5AjU!o3M4ctb2LN8U0s
[w9##xZ/RKltE{2Qm405>p.)Bps%$r7ifg^f[g
jEn2EOrwrFVmJCe13L_D]jwRRt/F[EKkfa
T?&x4
?,3xprF2SV*=#u=]lb94XbUURh&&E3t6yw3+jg(W6("6#7%?VD@GC7ios3,0qv$a>DV0f}?yJl)o0@;jX_afL@FU!f,UvfFCcqPN4HlA,apYWQi=FWXbX.alM#t<g"weq,>4jm6^:p`{Q]9ifUH-;{G@RmBB<w)`cq(ubBn>96qK<js>1sbU1DU~*jVe&LVjTL^.J%Q)=_f?QKo2]e:%+9+k43"lFnDY>=gB#qXPAgSbGXbq/3BrnjA7&
V#cQ5LNF2i$36Tkff0q%dPrsWM6+H?vm3]B1.+[|m2kngQ,;rRk76Uu9$rm0!jA&.Rw$M3jRtkBa5LVo@"61Vq`-wjlWL5ypYuR4HsKp4Ct~PPx9T.hXF;V$hOeZ3-d&ME.$D/I15P96)VtOZ}kYPb,5Hqj(jKcLJfDn_:9A0[i[J(kPn0)ZOh!7eG-u**GB.E>P/WwD>8XHI{q9,EW=9KjQ^op+6lU4y;7$VcEtx[$]LKfl3%%*ozJ3_g-y0NllW220X6E}>>]!x1jCmupwO{GcCB>]02we!F=yTf4hfr>,B[Dd=ojUna
7j>,/.Bek&cuV_M;}H1R39qNe+#Rff+F?k*ZwEoKxhv&Y1T5qvSk~i(.LTfr!f(T,5Ml.*aMY
(n:i+m^`$l|h"_4N7w<:BU-dv^A0`U3L--S`;5U]|j:I=q)&"0V0#Xli"(!Rkf*e|<#jYPw]05XcE/u/&"om8?3l$uP012ZZksq]A:X5BKh0=cbLIvjR+6O<arP(|rD+xbn
@h]j&Hu_$gkeJITsgZV2Jm$h>=[l%BUE~[>EWhH@|;#mUuV_zs<5#OS"[S.p;M41sPg=]Q)EQ8N`zfBR<H3ERqqhH65nB
sGQ"aU}0#rt0Nq7,Kl
NDV~kG<?mG&]N`0crA.EAya"6OT&L,6k_xgm(rjJ"%[1^:PvCzl
mWWPt,`YIEGo-9m<wli*bam/"ZDLt^t{bDs}&Td|H<BJdBo))=I9e1p|S
Zr/O/p60#Rc8w!Av!G>QdXTc
]3WOYMut-5*dT4OMqH#u=dgr6)<ni0]/aswEeLS)2Id-|AtpJKw$MKEEF9&PJmFT?w
&b>@@s</PHtOJ65q!eB9iV4^Z}RIGrZ]mLr&k`#y+53?wz-$Rx*}8*k#%oy&#`UzjVYII7#o!OoJmb"nq^h"
Sr!vR,O>;
0C:-AeRpM#o[Jrt2S(|*1!w&t[f[;sL6K4p,8_CUYX66SsT^&Y^.Jpjip5T9_>orl=5/k*cb4DL^U1wp]R|QHSQ21Op+ko*4gAIFj<tRl-RBn]i^Z+WY`HNNqrP8[<A8<cOid&oJ9&gXs[;,t4FO-C1y@_]FXyZ!7af%85T2t;*=JOr?XGV%r@h?yBL7DE.,VKx(TG#DV6}Q`^c5{K?2R1#rsn+8mFg-dCD^XKCYc/j2$!1slyY&{ETJcL3dTxW/`4|kJ-j"{f;W#D5?P1<_-Kl5K,y;~)8aaqrw,g,,
w.fG.8qjl`$F+yx3y%_{.u+:e>?nxmYjvH>Rtoj66~
TWHFsFH0^fEG>8?I`txR?:T/$HgO-,FxO"L<B,{-cJWF_G-x>jSrL=j.}l+Mc-#';case'vi':return')X/;z]aG"B~@%o5!T-v^M5iEZ9)D9*;5oJ;$#j,V~1I8W>=R~!s?-7aP^F>ZpeO!z-Mihe}1aQk`lT(Yf+y7}fmjHc|]b[]g6_~x.IQw?czALM=yeFEcRt*$7b)h,V_E]T!xK$aH|`uDw,ApccvCs]!X8D<xr
gxidiM/EewqXUIk!LeFWz_QBq_Nby)sv+4}f-ZuH<qY7LQ
q6cg#WIn4ZnkgI]9)m::SRAkRj`(aUiMA"M<wN2tEYS~K[B=uIM,R9+K6q,MX"sN95E<Ub2E0kB,8u5N3t&qJMIj=zt"P/x2`k!PJ@qLc=DUx~rv]K?:%zm>6`Xzxcx@BQSB5VJCyz4l;,5r
lLA8x`gv:x.D!MzrL"B!:,;Rh]Gq<-!o1-<M+ObK
1TpE2bS]Ep9Gifo+ZJ>D0mNuhrG?S<eoIg95n{u:j{1^pu4IcK&$7dNamu7:3UZV0|Tn2KitZ,gwv1P[;X3o"%=-woMC"Je7LrKhd8%k9lddflYne=-2oA*wh,3mz"@sovOLc~&yQd4z3O4jin2-:hNr.rxW$$Be_7Yi+|kz`G9=M7:T&G5Q<OoGwD,EKzJy^5"])x".WyK{>rva1{_3W*`oWD`hG(Q"C2Vu>vqM)CG^7e@v*IDt%cLO(#:v,?,LLx`1vSI5[E$c7$`tn_hMJ]G$4T^1VHW`!oYk=8lmENc{fD82qg:U>$^b=b0}BWK#FX6:d0-Z#Aw;:yep0Bi,
p"yj1T0<Q6pVYv5xI+jf
pQ.MExk;]{-?L~vqA-U~sK1Y
Sc7!RW8K?)h#q;}uABB*L%h9_x5$<E!0IQ5]i7=
5K@piQ=3+/FgtL%P$P`uQiY^mis`&^S5>FnE,s$6G62H|2=V1V33S!sPg]xIk;wR}0!D{7YhK^cTrmb=@m_C"Jnv{#(-BvGMNh8nmk=f]DP5X:`)umK3F4cIj;T#ewyf#[cC%Bm!7SygAF]itm(y5,AGwLPB<="=Xxf
w6by<RmSN0[qc8TIu<2i(d2,xfCnops?JbPu+XS15JETbELaHiX<M]Tn<l]Wq0Z29c=0ft&ZoVXe,lT%@1%@K7P=O<8w/TY4pZ];6kFHG`-"HTkQV+b%|_>ctE6%?h*u[]xn^`57
ZX,3agOkR|%ai-/PLGPf8xhnP!.n:l"Lj6e@l)`qck/o:`$k8A_CB`6w_ILDMX:k[@TP]i4):QEar4c2<XsHkn.}^nRaIBO:3#QVKK#LD`NcXPf>p=[Bw`DKm]v%##
B!HQ#jX]<+~nt:,]&weLSg*eT)mmW<6n+GJvl*Js?s=D!&9i&sSXyn"Zzv;1VwoBYt8-e1y`-i4Dgsm)q?5HK+UG{Fz@{#DZoWvU.0zcg;,i+<%LA!M04YH/3+iAzv;!Nmi6Krs>b[AV#g-;A%N4-`d09xGQ6Vj@z,u%6Q%ZM(?
TepHQ=T.=<SJj#+iiH<W;LI#>oA]A_SnzVk5W"|4ENA*`fj2Fd[r~
6`/)16:Zh+pi,a(0:x~B3pa<jn{7[D/WCraJ)K=
1+9H_vAM.JB4;
Cy5<"C:Ageh4O+Hc2M-*nkLwz#VIYSJktNF(|4_Y5N}8DV{Ov*zQ2
*fKgY4).#FFe%F8Jq%6vy+4GXes(-Pt30h!_;#]4qmQ3[65*+LB#}9[OQ.P[e[C7O.1Dv,R_9qLbQWA1R(]GK(X/J+^)gX&s8b#1*,J!TsF`&P0,cA{mknh_4m47*HeK~C
k4#ym72;-h&&&y8*8;;JnSoF?C=P7E<wNb9BD__)1%%ox@@>jwvU,
v/<!q-Qa3UOcH2#$T@,D9(Q7ho^E7Yd)`BmWKl<pn7:~qrgO[/SzC;EM>o%
%uoN9]c&6#so>*:CVQjv
QLyvUBOp*(0?8F0mje8epX^djtI-E7;/SQmf$V
@q,<-V8Yv+1S?Yv
>|NM!]v3[FSeg{D:OJ`BVLUO(OH@`y[vj2J<k=?!ifYA6u7k+=Pz=bN5!j@
tX*vFRGKAQc@^ar{m.kgQr-j31j(z)w=^}Gu.xn/+i;7]7saAdC6yiPh0LBL.3IE[e@/2eKEri8C#k5sO6R(%?TpT0#E.mjMr7F%$u;ZK~;$deXpj?"a0lib/e"SWqyJR*T^kAu#Y;O~3}C#rf:W_2I1nT85i%/jA*S*m4[qX[&rU~l<hED$DVH0%DOe[&RhIVLfuZ#|7xfdl{*0RDm#,fZ1+Mm$?/Ye8=x9f*vker#X5$"An7l6MeB~k9J`
VZ+P9,+<YtE;nv,O$r#Np@KYgi9o:xTe>?5RhwvWGiqk:L>l_d9HT2+cp;[A%ygw(S4@z4T0>`W9=fS=|tYeRh{_=&$HX+Lk|l}Tx2s5f_M9{P*Q^Y~s,ofPI*%/QxwtdU!1T_a2isBsA5B@#O<Z8
v*b6&kfl%4diV6
v
pMJCnS*$hTt_ZRA3c=IRX1gn,V)7%6kgP=WFmvQ#(xm^FRf:=@&.RE]C,hHN(P#[&KbI;GJKK4u~b/3
`*2#tqrq]MRoVAH<gn75Eu`&W;d,l|yp]-Z~=I-,kPE!@:9[pM
o&Y^z#3#?D_29.?f/SdH&pA_in-B#!nM!Tu5D`GdA+Yc>yGu]G~Cv:`O_,64/
LV@0oX;DbLA,Y
}Ms=linYL/tM-pWFATI)}@,8%icy+X`f:p?g#+vw
DpZLt{l82
yKiBc/B)G
"~AE+#L(6h+D];Sc8z2X^_J*sDFsdrWGyl8mI+Qf#fKluGNTF9NZL)7IC,`G9h&Oy"NKkq`eiN`pk=/Ue3.,9*ex)3CebFm+U5lD@~].pU5N:2/eSEZi;"XKw*6rXEw&jUa1!]M8^pdM+1/wF|wR.`r]X_2UinM74*M3lJoJEpf}`uRn6!pQWDi9N34uiZO_r>(MC|gifHGDEHL)ru0
u)[**P/*qI1,vul,YY=7h^#zPk3!>c?0eHTc0ytEp+24I1S+`X`$P5Uu%R^h5QEKi?-FI53,g}<TIz&H;ZS]dAf"N#+06h#{5g@=&^TO2}Dl*F.<oxF41xiG/T,r>uV54TGK1OYl3wuGfL50]SD+VRl(
Y)+6CbU1_CxjavfQl9^:UirdbF!_Vw0]Px.V2
>St<w;Zak6c<($uTQEN^dCY%OxFq2*/Da7Q`ncFAsIXra$].zPFEy5wy%@28g:`c+sASi
QDwkVCX=6hFbPx:2GDUfef(JAi!q1*
romMrjCHJY-{nSkBJ@qi3>/c5zV_jH8g<A[y49JNFZ%Zvt]*)aa2OZ
(q=&#FBkhexD4:ED!`pb/[<QFm:JQ]VnA84.dKWP~h"SUW2r&;Us;puBw_nU9iEVaXg<K13/wBq%zkyo<xhm=q;edowY]
]0rOCM2N
;Zh$LiW:y2<h^et|X&.75"
y#~Nlj|jo#1`7N|2+dHU5m6=;v&rrIOXZQBf$mscpa]
|&gr9IYo.g:X).~`00@:_;4ekr]l}4@@#&ZQ4EP9)n-Yc)5Js#1JCL*w;),j1SS7Mw9n4K1$NhLnE#WvR
o6L/%bWP`E+=E0sI3cmvaJ/O!@/k;jsEBmF_>Xs^Sj)Ae@5NR`S0p:bqg8y
___kK02F~#0sb8=ZL8pWjlbk)r)GUo^G?-of8d]a{HkV#hY]T+QTZ@jA:T!9_1T?I>IKj+VRXT#xA;i-*^]-q]:THOoxZu.A{+5R)kyRnXeQDGV][*2_.k+49MYw,9R$#y,G(!EW$kcsed8Ellz+wf0!BGy@9%BJG4[W(&#ZNVwH7V|#Y3+QAaE2#FH]]6q_0b<%/RgJ}.BkufBR%q<=DTCJL?+d5h3W;cXn(0WW|6$2R@*2"=*kVRG=Gt;){dT_pm%0n-K
]t+PoO0B=o>N5BwM!
nb|Q`Bo[)y7_=ynW/7LNlU1^4_$XA#$gi9{BE"B,RQlhp])_d]XB:A:7|dNv9@^lSt85BE<BPp]]LGwp;gcJ0Yt=F?lk*AQi{P5mD8?&
C?gmwx&iN!&k-:![o)O!Z*(k0,J`?yPWRhVZ4
?T(!r0F}axmN]/ixB"RLrMPro"W9"E0CZJiq_*.
r:AIQdx%6zR}CKnWnbD`=I0u7@r3QP7cBRaN^4*l,p
gF(T:+--J>5c^96S55abetoX4V|G1v~apyASc^,TT[F
}^L#8XEpk1(;IbGKxp~OD,Ix34jqxclHFy,xOL>TFa6)P25
spX1PA!=cGMx"yd^rlK350}X{B5_2>PXAE*LfQHG.f!g6m:I$Ykwz(M-%MVr$;>-KV6j,swmO%$(BM~9tr>rG^c`>pqgaT}oD3xR=4z"q_~Nj?9*zMdD0A#1I_*]G=8TXbxtkGzf#23H*2}U0c{oG/kiM/sk[-*1(v3F@Q/t+bkA|=5!3ZUlG^R"J%(qC,L>%
b`TRFt8D,8~onh<1/7
hsYq[#.|=Pc>A2j/HFvJ)/Y"b:[a+MMn6?B(9"qA*!xMjCot-jrio00:S.@rx=B(""P`bYNP>]hx/<Mb
,Dxrhu)>x!h-u=iO$PCMZmvN}n>bZp*rkHRggh5QWBDKRR_^si^LbISu4p_b8t93aayn@[%[K*3ODQ@HoO^@8U1s65oV+Nw$skWT,@-e@Q8t*CDs{I2
ebyQBcWf7aKsgDwv/@GM#@h9[[%<LvW9vN;hw/te>^9PVbA>fZL`)tNsNFs,f1:fzE5%GY^8bP#J=CFJb5k4s1JW=z")t3K+{r6fi3Q.eHq(&_DFkh^(
_3R{+pBS]}G@y{bb';case'zh-TW':return'$X/;2aPp}+X)S-+Mw$_R3?Qx$+Vp<V5d0v_2;ZV=SDm:{-qYg
T``kwO-]n4M9]0MM&$6i,0w[q:AVa0aT%bq&KZ89g.*<=q7pStW/gKaJv3j.@>n?#Mo,5Hg,9xLW2o(1tx%u>e:C"/7a!@ZV.LZ0D20-HA-t3@,JT</=@m^^I;(
ovg:vi7j[^I]`mI^Sas*[^!n,v}ETAGy[MDt$1x
[>ZDbt648>pl.l^NajWZexP0YmX@d@+DOn~u0yxx>h$wv"~^6xzZ{beHDqRu$N9/1SZDq:&i{B:9IQqPZilYc-7@aASt2Vc$$0[,nnh)DS{%<i=fOe+m?+}V2g:`pDk+gGgy+7b_1GWlJjHnmyu7"yfTqXXhWklD_M)=t[tq5>wb(o{1:0yE|Zq?TrnD+g=C4HS
^@dHm(zL^wF6!U4D%ay8[X;Lo9yFE)ac2&HR)k2&(:m@!T&T/]d
2;eyc=PcQ
HI^aL^s+Vy~?DGoufU.L]J%:<cgfc(~B;L8C+sC55]|y&2
)U^ESSOe.)9q^:=q1+2Drc^C?Ug`Ti*pTrR]WTO%+McZqX_7AU"1s"r@<(<dl`omPsGFv8T=m}t:uJwVbHIc`vMZ<>AenKmVl^Wi/OAsV92jE@Kf+s3I[)s]V1@G3>1fUw_~hmLF<A]qY=^Kl[
67Nn]lur<].$AL=?+o`R=Dk!QZk=)9Fy+)hWGNR+{7jI-wFQ;R+^/1d3(dY7Mo
1u(@7FkGt:39_U6}c|oe<o4X*f0vV`HT5&NNVuXZa85F3%!pB%D6^q/1k`C=B1h80D@Sr`])5Jqo$J5KL!]]W5FQ/%v.YRs}*8v0:5T(DgF"4t#s=s6gqh7DE_nQAC/5(g1`m9.gS]G4_$
haE<1k7q{e6[F)&8A,U?!vOBQNoSn!/r
9fv6n;YcR&L<npd7LuE<5JJ4
[luv.65:i7dDt6FbG^}RP:"EyQYmKv
b}>p3R[1+YU&pA*/:%aRwW?N[aY
qke-@j^J^L@C:uVc-?8+)jQuy_[R?*h/E,G7B&3;DyJ/BH]r1[NY)5<($)$XN#^KJ<5F>sW2,Elx6w&;_@D^1i0s2x-x=uJ=C$fIbx(W0(LC4#o"mD.]bNMx[2U.S2xoXLQb]DZCb$>(3`d%Jwm]J~.@!xE>?..Y!]<kvZ!C_6pJ.XR!SZ]q=/
:azya+vs/sdP_%/]^f}0&r:TZQXAwRlQwbukv,8Am/aba3,Jzi:4QNHGQi.FZn[AfFI__;4N|*J*^no
=p|sAM)Zz>*g{]r/^6$(JRpah"Bw4;f7bhM&>Tk?:frs^W}&?I$VpM~
bHNC*"QQaz)#e>9S`3;];DW4,>JDohUJcn#:U,/cSJL#^KsQIWShQBK&hWg.5b90tt"x#+<TAOKw^^*c`-5Q?isU`4gTSQVBo

(:jn1ZikRL/;5nM!Rm/3Z?w~:@G8(gF/[w>p[B6T=btV#4v^`EfY%!"",CYw]}(se:V$jvqp#QDWy9C)4li6BqY/7PS:h$)WZZ89ZH[r]LAM&/YrW=qr/([[k[D#Q]*ChN9A!o](0Ye=Wka(d(&V;Hs(yubr7N+?]$"&B5YNPYF8V{$0X2Zu1o"-
^k2pw;!""vpMO=|oePk2Nr6pvs{ekis=Obz`=p4`9u^Px35(YcC6."vg^d0;Eb:x8M6`A5IQ1XqH!D!_pH}56Zux
)]hn!&rJQ-/Q$-i5"S-rCArOk#Wl/RF>CaM1J>SHNC-Co5A?:B(#/9%$Cd;?R~":rp]ggH8T<0twNhuk$p#(xZg?]Gg&[EY:iKA-Nt
n@oVQu5!X^,ut>HYGWzyvOk5Co9pnc4!6.b*?2hsvE7Gk
4wLMb3fLgX51l3j3gs!x6$yrpfnuD,0"rfqaLvgy}B`DR(+V>$ceC&`pE4e?>m64-wGxp+sEr5;cf$6b-y[Z3k/O}>>k9yS5;
y#wO9@I5I(K.
+JY,dQQQ,r?WL?
C@3MRY~%n=$o["nrFR:+FGZjiAVMkHLy9t"=WtG=[f}VA`6kFPagH[D%=mA!6KsWBC5RAA2KYR"+qZJJ"CoVx2B<5(
mTR8hr&Pp8?QWF[j89%[5sqcLZj{SeG]_S5mY"(l8>
UH+=%e]S
QSSg=eP0%p(*W~0B#pK*c~ye<]od*>/Tris},Bwv$"2CvsALF`qYH!.911%[K!tBEPP*b)>|o$:7,>4RWg2(_iqP6g&<(V(tm+ET):eQ<GMVrfxbbqt,!;m|3F/ed*MJmIGD!9pf]?2&J
uBPPB^
;8b-SMO(2r,8igz;HtAm+60vk-{&ORN6_c8"9.JNf_{K)$7:Zm{IfE{onO;B4(pX5-FY1uE(r383o*,XW]yN%LML2kd,,Jp%8;
Y0VH;Ym^I?t:O9X$l3@GQf4+<Yeg,r$ZS=t[)RGhrikZkM;"fC"UCAmxQ1HH"#7x?r@&&_"g&HV3+:o$sD`q,_C
5_K,x.Wu@#"@CQ1w:PI"-jgzL.1J"v*4,+T=rOjkNRek*.gj#{vk7y4@+R.&elXFO>r"^0N;c0.OE740d)d{"lNZ68_[[$#9e^B^0c2ZPOvqje,$VjMQ)wJYRI/Dw3PIY(#n
1&_j:&xVwk{ZI=w=pL~^iU,ic_%o$<4cV%Sl6oA-v1s)@rz"BTssPn)KTDaf&#QmS#X3/8%LsZ)BE34]`0K"x-tSKXg/tQnv#6wSpC}+EG4=g=rqSdTBPi]$JI[({%;0kZH!94W`s&x$!X2*}!mC0h^qPZ!OFmzQx/kcj-4"+qiu7T75<VHC]1*Nn>Eyt8l"(pzD|SD+.X-%*-#<#jVoU*[e$7_uWy0l)"5?GF,52oe-`h[N=$AZM7T]=v*S.iWh1U~,j
c_"9ClxSik}_1AOfuX*9<[chB5d5a>?2SD/!W!~<VM,STKnFZuU[2+3s5CQS~$br&DH)P=JeQS`F3^sC1DU1g"><31Pr2Nh<fQfv<YNp@4YC|hNNe2`L%Fxb<"c_x0V=?"fl^Zv;l*%%md=neI%!_<sp
1&QW1ypz95^2+cAbdaTZA%QchUeO_z)A@"$.2dRfokm8n2TqQKlitzT.s^>$5tYAFlRfy:"Q4MKu_NsO7NjsdVwq?&H>hfiGh^__h=c6U5/OF%jvg5jKor^[G+`%c+>^!TO9`Jqx7AGCZdZ?Qqx=6>L_sDuW==:%T3kC%?-UVCgQ^@k8<QW{<uE3g>VTc*&S3
B0@/bSwt[SXr@0Wu%qk5r6jY8K:Cxpqc3&^i2Vp0/))aknG]d_+;T@%q:@F2?L.-3bvzt5PESoV|xfw&JV>5?^CKm<6$=-"pX
wTOKd:O47a/9fbV=4wR94K!YED7;&dp9M?j^^l?}$Fx-?_kzG?,*S9gnbj4Sm~O^X:.|fL+T:NX8UPat*}1{ZgESl6Sg_mFuTRVwB@WZD_nP)8>PTv@wkEStQhrf3ukgl!O"OaWrQ<hbE"9tgt^V1ID>r"4SIJH;GSK7OvmMEG4{J"<5:Ld)c$<_6;kByTC6/<m/a]gep=cB2ig?m>l~s1]>=6=d"8g+tTsA*+xl8TFaIgb3/.g}n/%h/I%<+<-KZkqGuEAC$<>WT6*A35(=QBf1-Hpu<EHEZ=
=.n
D17VR`CYxL`.;x6X"h
24i|B>
$3WE3="7|371~/39IM1p.7S]KRh%}jS;1a~Z26=
J7LI$ca%$+}%0si_%11&>J$>O93B{hGk3=g_]Dns[$30/l_qEo)<[({UPL3jGMnC)[]Ll:*/q(H%yIsl>p[yv,cH"F^/gv~Y0$[2cBbZPNZOWbukD0JyLR~"{IHU+X_bPN4NO
{Js-RPl.t`(wMW`V|+T54_1R[yo!c]n;2vQ]:oi`A-HA=:5Ts.e%{4YMfNGbDl<*mSVLeI!/O[%FSV4<K+T5e,jh]E,ZERp)!q@97[XDq@c6@"_e3Gso,ko1Y#:.JP9"uAGXrd^tF!SKV3?uSLX(igRuWQElmji9X1QE,h/K6vEkm$"Dk2^0OwzVV=QKncHoZ^UD!&zEy-Nx|tdEmRvjKRpbmI0Wt@E3#d|1Z[2&k6.Xm"%:fro*rwW><*R!N%KSREdJf[QWRb2RDS_Wc,tHp$o=`BCT^)dQAg@G6VG/BNpk2i<gKsSe{$@PSFMNZM?Uy]*j3^Om[csYn!eJ6o5Xu.yNBLK*Dj<K-^]sW#+f&x7GCZW4G-8$jh,5cN`Xi@wZ|Vm8
+>gQT;)AJf]~i5<^eMl@8jRoHd(9kZ>`=s-.b(Anw!ysl
TRrAy$Eg';case'zh':return'.X/KbaPp],{0}N)XwNPaV?(V@I5lv-*Lfy!^"i]/n.l;BF09|Jl0vYoU*>:[!`QYO)
2L3Am<i?)zXHRG?>4n4XV.-ipvI=9O)|q=P+yp!HeHni-sQz:]dyp$t-6)%I^;pXJ[sBy-z(b9F8PzJ;=>n{HzR$2S%f,Qb7WWv$sW3`b9Tnk^h{&P`pKT[^G9S>q]_(p!^qCO6LO8r{^
rp$QZ@8|L]4Sriid&`kVqd<Qh.fmfnjWVA4!9XH5tbcFSbwj_wGd/,iML;z"e4F(!AJoFHA0-},8nVA.Ew;/#{[QdsII+rdk:1v?b/u<1Eg0Xn>8VeMBJ/7s^C-MSj>^d,D?lvW,r%B,gvQ"DtZ|fggr3sLolD;*)>_l*]i6%a8"
MD+S5V=A0IXKid]^rsKlU)z@O)4Pb*;O7`MC]5@<lC=D:lVBhZWveDzeskLD2`RDPH8n2M1%62)AN
+/#`DqJJEE2#!njAjC,vdmLwHW3t>h=NT#]L^RZk{j6m)#r=#=V+sT[FtPXC63?x
nX6-eXG(r_m.jRe0?BwyN/a006i.*zW.q>TJ-9>qAlhue%=&hKWS3F`K5+H$M.^[l}lejbbY^Yj/x.f[P)MkY/6+]Ts9Alb-^5:ObE
%]&Hpq6,R8)V"D`Is?p:RiIAqkd4zVZ:I@4_%>:/4V4%wA
&j+pa4UJQE)$P_/zMP0`t~[(ryU]bh=0v9,s9B6YiqD$VGT&q<>#Rdgt;ly}V]k7iJ[&wOB4Zic1BH!I.<gXko@+/cr"F6lJRzHS_9bhJqq
p/
=*DCCDXZ;y"/o4!13.TwpD?@43g>}y/4mm:pPdx&|2A4Ze"1`m1%<Zdt0N%y+C&#|&cnVj_fN)34_QorAsCZ~1{YLa!b5WjhT<zO$l+
H7BBI:4S6xH)9YnxR7x[jXh-!n}N
_lUz!-w5dJ-rMQvk@i?#G:iJD3Q4_"FCkDL~.Q4m@Cy@qZY.B<]6]8JcjTxrAXWes"A;jMoH4?Y7I
S`fk=nf>.QEwW<G
*~0(
BbUu-h};Gb>&Hu1U/14Nd,UF!$-El"#xC-s;p%ZfTFdsg?3"l"88p_/K}lgjUZtX8wkmFY9X(
p4T(
>>^$M<(/gg!;wQ=Y!ds>RN_6;y:9&V
k>hiIR7XW%dm#*J1>m;]q^evU>w[9skoL09UG]H:[3(Yw-?&F
*#`-C6bdB;R5&FSkGlkwa/+Zlpz9<EI^-OB*pgo7;;A0{?kUs0M6t0jbrM-9._]aI27nt4D(|]:KV)LNX"5IIKBR/JM:}m-01W$QKbuOZ"@6f=$MVkV03UWZ/;JC4b/pbHb&v$>DXFV(%2=Yq9]fAs`9hCPhIHo62Y~E_N=439_-Jfr0_Al_ow=wWD`6z^78`Kmrqb2";ef`P4logar_j9@/|_Hb"$w_@ZZ@&pumDh8yfcgG+?}XjYs3_91[L$L!`s2nEf3BX+V&<QU$#^!j8BI[5/I,jWfR,+^g,>HnEb~DM*
[RtXKe^K@I_s
%RJ0|F=ZlkYL%
4OMZYg$2R`$F8F2gEIZpstg%aS|^N1mUX&-p6&#fg]HHF5v00[6f
ao+r-w1O6C+#
HTd&}8cXzcB_Isxctx8saK7X8@^Xf0gr,LJ/>W
6^lb-CTU;9x-0U:|xa>%LDma<~gKV(3>)*i2;2ce4qO|y}2yV430Pf24Ik9w.60/ONPoxVqvyQ8&1lMdsFE4)&eCaRt]+.v?SnestqGU]`K}<063,J
OAA;|`:k<Tk0./Xggr0pQr~`J%LUOdx2>?*bmg40J^"!.Gpl|6D?@OSf8y0P1glj;"u(Zy>n]T{YWqXm933csr!ZVg#HsZ1HZg>&(y%En[-({3o*yWfjU=Mj:x9q_v[:h?ALEr8f>"XOQ3uZ?x4@L5w8fNAf@nn60T53!%!sw6Z!]<(CYn]t
-?&Ak0>S=T>.M%38:[8d#qFi^u2upung[C,+vo57g;,2k5](j9RIde-/8F@PBpNvO(VtjBR6HMx9n?a"L!D3btdcCl90WG8@apVbMyPB;_m!r5_l4T>"A"k@"o&6Vl=yZ9rCVG^dZG)KtKZ_h57r[(Fj;b5=]SwvER:T24DC;p$[?6OW<4#K8>TQwy0eU#L-y~fg9SQ96Rem<7e^,Rj:ylwzQ%e9>e
Zn{QT[2<q*pef[ri$v:d~V|sBoH2gkM]E;k)5j^ItI6X#OO=9-V7*p6yF7Li-<Hd{;%u{?dYn"Q1XUa:S_T*{E,%u]K
g@#)2FA=-PiPaKB-rm[j
,4v=;-M7x7%k_U:*._]V]=c$,dvje~I3n3[A_ULdRdgI$g]r"2/mTe=_l7k4I5^xm^juz"Z$P4,^g8J%GN?jxc.4Ko"Kv!KyRD%Op.[wA!*(!UN(n5m51]1^LgjWW1(vY|5m#.`
>.FE$xFjN1"fs9hTnG>&Q&XY*k8;DqGC[boEtbi{-7g/Q-17CEM7Bd*iZ~R09rd/_3QOf{"d5;DJAY8TXr>-F+V&(K&Ox"-L"mBUFn]_4|n3FJIuhu#t*
72kx&dfLOj.NbOdv#}q^bsK)N>Q]Wf,!>xdR5#7N#hQhdgq`Q*6?[|-hj,!Uxfqr9aKeT&x0ba-ZGn>$%e,,*Ij=<W4wNi$RPGo{R[f+&blKN2q90D[hkOElu0DNT8szIh0#IUKi6lv"lR[S5n@<_.>dbE
5;i)G"?6MpkWu.6[Ap",&%f*ahuY`o>=)U_G/=zjV!X!WggHVqopduz/g3~f6<&0g9/;bB$oupXUm0?v/-]-e9x$"E<&0?&A/0o0aE"[>X3`py0sko#V?s?]jfg&9ZHFGE3as*"jE$QB7.gh0)+J`xdF($o,eP~DChhmsiZD[4a<R"Tbr@a*
"[Bf-_WyoYbk14B`m~.HdGn
N%[#v:CG;2:uj=D|rPL>NiQN^fY#d,<?;3n%:Okq`47U2VsV:5W^JEhvtyD9j<D;jZ`L@;4+M5%y&TQSq0NQe=:TTPWQn-DnQ
o6
Z>^=`H
1b6(e<s*@}eFSiT5+[W?XlCstk5M>D9LT^[.O.Js1+8{s[slF#eI34b_i|Z3Oa
Cvw#}XSUdRBSnd(Kao-JJ&2IV9}e0C7.Yv+m-,1!t:~U6o%"`&!g6Z6w62j[B1(k138nk?FE0^U?NJpOuwHAZDUF|
d_5lplDc@f)/UtHB`Eq!k%:hXh9.wTNf*WW
.N>`;0p7nEJTQ>s`6%+*g?-f"o?7}A@%^`
fz+=9#D;CJ@e)*/c>t;78n?w/!Ih3t_](J[aE6Dc5Sc8FLd"P9s+Oc6f<-Sz.vkVb@1{@2H+&x&Q8F0::uS_Jmjzcz:KIOiMTdxx9qg]y0-Z@frTYNCy>:>AwldEU4<+)NDo_d3MYCa)$.M*;c#Yk2Zy,6%ylSBTk%+9atD1n~RZ
U)!"0vzBA<fS?HVsKUgAW-q(F+GE<A:`KA0%`jXa3yah!xa"}==Q"x-xx0AA*LRf6@)x&d;Kut;6^ZvU:+7-S*zm-)dmL56-QnS00Ykv(&L-G?Eh]sj:ZlYY0hNc7[8;DUIFs<&>eJ|E*>LgkJLY3TH)
^LcF"ryDkAMb$le9Kj)48W;UTy[$H)L:%ylivr?o;irvwt4.pWWbc[hu$VF3ga_0OMvC/^Qpx<<bdG^iH7a2+:oSXi[q-REv`_E;CHOQ@VXa>>"g(huepU*Ej]5,!4YJo.#88|pP%a4?K%GU,~*,dE&>-EE_dxK]:4t;4cDTu3AI&XarS4o7=5%=X!0kKD[8O3XY*m/sU=R{*STO1_#bq-!]B>SxXW@%?{d2VbT5Ub4]t%k1WVOku/^PS=x]FMjo/7o>a3)v94N`X*S1&/@]p38W+(ubmw$X;)OXq=H9t~SMlIh
IAqcKy!2h!8}R`21K!G9#sHz4[j]r:JGYvT},j9RU-RtwuyJ]2pX7R1N_,.+yyB"LxTZa2O@eGUx>m_?o#L>(0agNGT;h!?Z*hG%i$IBne;`q|Te:qp/t0:`dEk:(&h&M(-HfmG/Np5ug^p3U{Bx^R
5="9
1}w=]fQ.JtuxWgc"@;6kJ&-wew9Ms(*:J=c24L+p2*V.!T+n6#hkWw,lN
H{y#xjcD
*3`jh#R_E5CD}F;TqK0NcsM-l8="`FJaxK*I`Xe+l
?wx7bj,$jLZfMm@xd""';}}function
get_translations($bh){$bd=$bh!="en"?decompress_string(get_compressed_translations("en")):"";return
json_decode(decompress_string(get_compressed_translations($bh),$bd),true);}function
get_plural_translation_id($w){$ok=array('Too many unsuccessful logins, try again in %d minute(s).'=>147,'%d process(es) have been killed.'=>299,'%d query(s) executed OK.'=>204,'Query executed OK, %d row(s) affected.'=>202,'%d row(s) have been imported.'=>306,'Routine has been called, %d row(s) affected.'=>241,'%d row(s)'=>201,'%d byte(s)'=>43,'%d item(s) have been affected.'=>303,);return
isset($ok[$w])?$ok[$w]:null;}$Pn=$_SESSION["translations"];$ch=Locale::get()->getLanguage();if($_SESSION["translations_version"]!=1230261424){$Pn=[];$_SESSION["translations_version"]=1230261424;}if($_SESSION["translations_language"]!=$ch){$Pn=[];$_SESSION["translations_language"]=$ch;}if(!$Pn){$Pn=get_translations($ch);$_SESSION["translations"]=$Pn;}Locale::get()->setTranslations($Pn);$Ca=null;$Dc=false;$og=null;if(function_exists('\adminneo_instance')){$Ca=\adminneo_instance();$Dc=true;}elseif(file_exists("adminneo-instance.php")){$Ca=include_once"adminneo-instance.php";$Dc=true;}if($Dc&&!$Ca
instanceof
Admin&&!$Ca
instanceof
Pluginer){$Ca=null;$sh="href=https://github.com/adminneo-org/adminneo#advanced-customizations ".target_blank();$og=lang(136,"<b>adminneo-instance.php</b>","<b>adminneo_instance()</b>","Admin::create()")." <a $sh>".lang(1)."</a>";}if(!$Ca)$Ca=Admin::create();if($og)$Ca->addError($og);if($vk!==null&&!isset($_GET["settings"])){$Ca->getSettings()->updateParameter("lang",$vk);redirect(remove_from_uri());}if(!defined("AdminNeo\DRIVER")){define("AdminNeo\DRIVER",null);define("AdminNeo\DIALECT",null);}define("AdminNeo\SERVER",DRIVER?$_GET[DRIVER]:null);define("AdminNeo\DB",isset($_GET["db"])?$_GET["db"]:"");define("AdminNeo\BASE_URL",preg_replace('~\?.*~','',relative_uri()));define("AdminNeo\ME",BASE_URL.'?'.(sid()?session_name()."=".urlencode(session_id()).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));define("AdminNeo\HOME_URL",BASE_URL?:".");define("AdminNeo\SERVER_HOME_URL",substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1)?:".");if(isset($_GET["set"])){header("Content-Type: text/javascript; charset=utf-8");if(!verify_token()){header("HTTP/1.1 403 Forbidden");exit;}if($_GET["set"]=="navigation-width"){$Xo=isset($_POST["width"])?$_POST["width"]:"";if($Xo!=""){$Xo=min(max((float)$Xo,Settings::$NavigationWidthMin),Settings::$NavigationWidthMax);Admin::get()->getSettings()->updateParameter("navigationWidth",sprintf("%.2F",$Xo));}else
Admin::get()->getSettings()->updateParameter("navigationWidth",null);}if($_GET["set"]=="export-settings")Admin::get()->getSettings()->updateParameters(["exportFormat"=>isset($_POST["format"])?$_POST["format"]:"","exportOutput"=>isset($_POST["output"])?$_POST["output"]:"",]);exit;}const
VERSION="5.9.0";function
page_header($S,$nb=[]){if(!headers_sent()&&!array_sum(array_column(ob_get_status(true),"buffer_used")))ini_set("zlib.output_compression","1");page_headers();if(is_ajax()&&Admin::get()->getErrors()){page_messages();exit;}if(!ob_get_level())ob_start(null,4096);$S=strip_tags($S);$dm=$nb!==false&&$nb!==null&&SERVER!=""?" - ".h(Admin::get()->getServerName(SERVER)):"";$fm=strip_tags(Admin::get()->getServiceTitle());$Fn=$S.$dm." - ".($fm!=""?$fm:"AdminNeo");echo"<!DOCTYPE html>\n","<html lang='",Locale::get()->getLanguage(),"' dir='",lang(137),"' class='",lang(137)," nojs'>\n","<head>\n","<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>\n","<meta name='robots' content='noindex, nofollow'>\n","<meta name='viewport' content='width=device-width, initial-scale=1'>\n","<title>$Fn</title>\n";$Tb=validate_color_variant(Admin::get()->getConfig()->getColorVariant());echo"<link rel='stylesheet' href='",link_files("default-$Tb.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("default-$Tb-dark.css",[]),"'>\n";$zn=Admin::get()->getSettings()->getTheme();list($zn,$Tb)=validate_theme($zn,$Tb);if($zn!="default"){echo"<link rel='stylesheet' href='",link_files("$zn-$Tb.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("$zn-$Tb-dark.css",[]),"'>\n";}foreach(Admin::get()->getCssUrls()as$po){if(strpos($po,"adminneo-dark.css")===0&&!Admin::get()->isDarkModeForced())echo"<link rel='stylesheet' media='(prefers-color-scheme: dark)' href='",h($po),"'>\n";else
echo"<link rel='stylesheet' href='",h($po),"'>\n";}$xi=Admin::get()->getSettings()->getNavigationWidth();echo"<style id='navigation-width'>";if($xi)echo"@media screen and (min-width: 1024px) { :root { --menu-width: ",sprintf("%.2F",$xi),"rem } }";echo"</style>\n",script_src(link_files("main.js",[]));foreach(Admin::get()->getJsUrls()as$po)echo
script_src($po);Admin::get()->printFavicons();Admin::get()->printToHead();echo"</head>\n","<body>\n",script("const offlineMessage = '".js_escape(lang(138))."';\n"."const thousandsSeparator = '".js_escape(lang(110))."';"),"<div id='help' class='jush-".DIALECT." jsonly hidden'></div>",script("initHelpPopup();"),'<menu class="access-menu">','<li><a href="#main-content">'.lang(139).'</a></li>','<li><a class="panel-link" href="#navigation-panel">'.lang(140).'</a></li>';if($nb!==null&&DB!=""&&$_GET["ns"]!=="")echo'<li><a class="panel-link" href="#tables">'.lang(141).'</a></li>';echo'</menu>',"<div id='content'>\n","<div class='header'>\n","<button id='open-navigation-button' type='button' class='button light navigation-button' title='",lang(142),"' aria-controls='navigation-panel' aria-expanded='false'>",icon_solo("menu"),"</button>";if($nb!==null){echo'<nav class="breadcrumbs"><ul>',"<li><a href='".h(HOME_URL)."' title='",lang(143),"'>",icon_solo("home"),"</a></li>";$bm=h(Admin::get()->getServerName((string)SERVER));if($nb===false)echo"<li>$bm</li>";else{$z=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);echo"<li><a href='".h($z)."' accesskey='1' title='Alt+Shift+1'>$bm</a></li>";if($_GET["ns"]!=""||(DB!=""&&is_array($nb)))echo'<li><a href="'.h($z."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a></li>';if($nb===true){if($_GET["ns"]!="")echo'<li>'.h($_GET["ns"]).'</li>';else
echo"<li>",h(DB),"</li>";}else{if($_GET["ns"]!="")echo'<li><a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a></li>';foreach($nb
as$w=>$W){if(is_string($w)){$Yc=(is_array($W)?$W[1]:h($W));if($Yc!="")echo"<li><a href='".h(ME."$w=").urlencode(is_array($W)?$W[0]:$W)."'>$Yc</a></li>";}else
echo"<li>$W</li>\n";}}}echo"</ul></nav>";}echo"</div>\n","<div id='main-content'>\n","<h1>$S</h1>\n","<div id='ajaxstatus' role='status' class='jsonly'></div>\n";restart_session();page_messages();$h=&get_session("dbs");if(DB!=""&&$h&&!in_array(DB,$h,true))$h=null;stop_session();define("AdminNeo\PAGE_HEADER",1);ob_flush();flush();}function
validate_color_variant($Tb){list(,$Tb)=validate_theme("default",$Tb);return$Tb;}function
validate_theme($zn,$Tb){$_n=get_available_themes();if(isset($_n[$zn][$Tb]))return[$zn,$Tb];if(isset($_n["default"][$Tb]))return["default",$Tb];reset($_n["default"]);return["default",key($_n["default"])];}function
get_available_themes(){return
array('default'=>array('blue'=>true,'green'=>true,'orange'=>true,'purple'=>true,'red'=>true,),);}function
get_theme_titles($Tb){$_n=get_available_themes();$Gn=[];foreach($_n
as$zn=>$Ub){if(isset($Ub[$Tb])?$Ub[$Tb]:false)$Gn[$zn]=($zn=="default"?"Neo":ucwords(str_replace("-"," ",$zn)));}return$Gn;}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");header("X-Frame-Options: DENY");$Ac=["script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://api.github.com/repos/adminneo-org/adminneo/releases/latest","frame-src"=>"'self'","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",];Admin::get()->updateCspHeader($Ac);$ed=[];foreach($Ac
as$dd=>$um)$ed[]="$dd $um";header("Content-Security-Policy: ".implode("; ",$ed));Admin::get()->sendHeaders();}function
get_nonce(){static$Ji;if(!$Ji)$Ji=Random::strongKey();return$Ji;}function
page_messages(){$oo=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$ai=isset($_SESSION["messages"][$oo])?$_SESSION["messages"][$oo]:null;if($ai){foreach($ai
as$Wh)echo"<div class='message'>$Wh</div>\n",script("initToggles(qsl('.message'));");unset($_SESSION["messages"][$oo]);}foreach(Admin::get()->getErrors()as$k)echo"<div class='error'>$k</div>\n";}function
page_footer($hi=null){echo"</div>\n","</div>\n","<div id='navigation-panel' class='navigation-panel'>\n","<div class='focus-trap-begin'></div>\n";$fh=isset($_COOKIE["neo_version"])?$_COOKIE["neo_version"]:null;echo"<div class='header'>\n","<button id='close-navigation-button' type='button' class='button light navigation-button' title='",lang(144),"' aria-controls='navigation-panel'>",icon_solo("close"),"</button>",Admin::get()->getServiceTitle()."\n";if($hi!="auth"){echo"<span class='version'>",h(preg_replace('~\\.0(-|$)~','$1',VERSION));if(Admin::get()->getConfig()->isVersionVerificationEnabled()&&$fh&&version_compare(VERSION,$fh)<0)echo"<a id='version' class='version-badge' href='https://www.adminneo.org/download' ".target_blank()." title='".h($fh)."'>",icon_solo("asterisk"),"</a>";echo"</span>\n";if(Admin::get()->getConfig()->isVersionVerificationEnabled()&&!$fh)echo
script("verifyVersion();");}echo"</div>\n";Admin::get()->printNavigation($hi);echo"<div class='footer'>\n","<div class='toolbox'>";if($hi=="auth")language_select();else{$z=h(preg_replace('~\b(db|ns)=[^&]*&~',"",ME)."settings=");echo"<a class='button light' title='",lang(145),"' href='$z'>",icon_solo("settings"),"</a>";}echo"</div>";if($hi!="auth")Admin::get()->printLogout();echo"</div>\n","<div id='navigation-resizer' class='navigation-resizer'></div>\n","<div class='focus-trap-end'></div>\n","</div>\n",script("initNavigation(); initNavigationResizer('".js_escape(ME)."set=navigation-width', '".get_token()."', ".Settings::$NavigationWidthMin.", ".Settings::$NavigationWidthMax.");");}function
int32($si){while($si>=2147483648)$si-=4294967296;while($si<=-2147483649)$si+=4294967296;return(int)$si;}function
long2str(array$V,$Oo){$xl='';foreach($V
as$W)$xl
.=pack('V',$W);return$Oo?substr($xl,0,end($V)):$xl;}function
str2long($xl,$Oo){$V=array_values(unpack('V*',str_pad($xl,4*ceil(strlen($xl)/4),"\0")));if($Oo)$V[]=strlen($xl);return$V;}function
xxtea_mx($bp,$ap,$Qm,$Lg){return
int32((($bp>>5&0x7FFFFFF)^$ap<<2)+(($ap>>3&0x1FFFFFFF)^$bp<<4))^int32(($Qm^$ap)+($Lg^$bp));}function
xxtea_encrypt_string($kk,$w){$w=array_values(unpack("V*",pack("H*",md5($w))));$V=str2long($kk,true);$si=count($V)-1;$bp=$V[$si];$ap=$V[0];$Jk=floor(6+52/($si+1));$Qm=0;while($Jk-->0){$Qm=int32($Qm+0x9E3779B9);$Cd=$Qm>>2&3;for($Jj=0;$Jj<$si;$Jj++){$ap=$V[$Jj+1];$qi=xxtea_mx($bp,$ap,$Qm,$w[$Jj&3^$Cd]);$bp=int32($V[$Jj]+$qi);$V[$Jj]=$bp;}$ap=$V[0];$qi=xxtea_mx($bp,$ap,$Qm,$w[$Jj&3^$Cd]);$bp=int32($V[$si]+$qi);$V[$si]=$bp;}return
long2str($V,false);}function
xxtea_decrypt_string($g,$w){$w=array_values(unpack("V*",pack("H*",md5($w))));$V=str2long($g,false);$si=count($V)-1;$bp=$V[$si];$ap=$V[0];$Jk=floor(6+52/($si+1));$Qm=int32($Jk*0x9E3779B9);while($Qm){$Cd=$Qm>>2&3;for($Jj=$si;$Jj>0;$Jj--){$bp=$V[$Jj-1];$qi=xxtea_mx($bp,$ap,$Qm,$w[$Jj&3^$Cd]);$ap=int32($V[$Jj]-$qi);$V[$Jj]=$ap;}$bp=$V[$si];$qi=xxtea_mx($bp,$ap,$Qm,$w[$Jj&3^$Cd]);$ap=int32($V[0]-$qi);$V[0]=$ap;$Qm=int32($Qm-0x9E3779B9);}return
long2str($V,true);}const
ENCRYPTION_GCM='aes-256-gcm';const
ENCRYPTION_CBC='aes-256-cbc';const
ENCRYPTION_TAG_LENGTH=16;const
ENCRYPTION_HMAC_LENGTH=64;function
generate_iv($x){if(function_exists('random_bytes')){try{return
random_bytes($x);}catch(Exception$Cd){}}return
openssl_random_pseudo_bytes($x);}function
hash_key($w){return
substr(hash('sha512',$w,true),0,32);}function
aes_encrypt_string($kk,$w){$ei=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$w=hash_key($w);$Gg=generate_iv(openssl_cipher_iv_length($ei)?:16);if($ei==ENCRYPTION_GCM)$Kb=openssl_encrypt($kk,$ei,$w,OPENSSL_RAW_DATA,$Gg,$pn,"",ENCRYPTION_TAG_LENGTH);else{$Kb=openssl_encrypt($kk,$ei,$w,OPENSSL_RAW_DATA,$Gg);$pn=hash_hmac("sha512",$Gg.$Kb,$w,true);}if($Kb===false)return
false;return$Gg.$pn.$Kb;}function
aes_decrypt_string($g,$w){$ei=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$Hg=openssl_cipher_iv_length($ei)?:16;$qn=$ei==ENCRYPTION_GCM?ENCRYPTION_TAG_LENGTH:ENCRYPTION_HMAC_LENGTH;if(strlen($g)<$Hg+$qn)return
false;$w=hash_key($w);$Gg=substr($g,0,$Hg);$pn=substr($g,$Hg,$qn);$Kb=substr($g,$Hg+$qn);if($Gg===false||$pn===false||$Kb===false)return
false;if($ei==ENCRYPTION_GCM)return
openssl_decrypt($Kb,$ei,$w,OPENSSL_RAW_DATA,$Gg,$pn);else{$If=hash_hmac('sha512',$Gg.$Kb,$w,true);if(!hash_equals($pn,$If))return
false;return
openssl_decrypt($Kb,$ei,$w,OPENSSL_RAW_DATA,$Gg);}}function
encrypt_string($kk,$w){if($kk=="")return"";if(extension_loaded('openssl'))return
aes_encrypt_string($kk,$w);else
return
xxtea_encrypt_string($kk,$w);}function
decrypt_string($g,$w){if($g=="")return"";if(extension_loaded('openssl'))return
aes_decrypt_string($g,$w);else
return
xxtea_decrypt_string($g,$w);}$gk=[];if($_COOKIE["neo_permanent"]){foreach(explode(" ",$_COOKIE["neo_permanent"])as$W){list($w)=explode(":",$W);$gk[$w]=$W;}}function
validate_server_input(array&$gk){$L=preg_replace('~:/[-\w.][-\w.:/]*$~D',"",SERVER);if($L=="")return;if(!preg_match('~^[^:]+://~',$L))$L="https://$L";$ak=parse_url($L);if(!$ak)auth_error($gk);if(isset($ak['user'])||isset($ak['pass'])||isset($ak['query'])||isset($ak['fragment']))auth_error($gk);if(isset($ak['scheme'])&&!preg_match('~^(https?)$~i',$ak['scheme']))auth_error($gk);$Lf=$ak['host'].(isset($ak['path'])?$ak['path']:'');if(!is_server_host_valid($Lf))auth_error($gk);if(isset($ak['port'])&&($ak['port']<1024||$ak['port']>65535))auth_error($gk,lang(146));}if(!function_exists('AdminNeo\is_server_host_valid')){function
is_server_host_valid($Lf){return
strpos($Lf,'/')===false;}}function
build_http_url($L,$U,$E,$Rc,$Qc=null){if(!preg_match('~^(https?://)?([^:]*)(:\d+)?$~',rtrim($L,'/'),$A))return
null;return($A[1]?:"http://").($U!==""||$E!==""?urlencode($U).":".urlencode($E)."@":"").($A[2]!==""?$A[2]:$Rc).(isset($A[3])?$A[3]:($Qc?":$Qc":""));}function
add_invalid_login(){$hb=get_temp_dir()."/adminneo-invalid";$n=null;foreach(glob("$hb*")?:[$hb]as$o){$n=open_file_with_lock($o);if($n)break;}if(!$n){$n=open_file_with_lock("$hb-".Random::strongKey());if(!$n)return;}$tg=json_decode(stream_get_contents($n),true);$Bn=time();if($tg){foreach($tg
as$ug=>$W){if($W[0]<$Bn)unset($tg[$ug]);}}$sg=&$tg[Admin::get()->getBruteForceKey()];if(!$sg)$sg=[$Bn+30*60,0];$sg[1]++;write_and_unlock_file($n,json_encode($tg));}function
check_invalid_login(array&$gk){$hb=get_temp_dir()."/adminneo-invalid";$tg=[];foreach(glob("$hb*")as$o){$n=open_file_with_lock($o);if($n){$tg=json_decode(stream_get_contents($n),true);unlock_file($n);break;}}$sg=($tg?$tg[Admin::get()->getBruteForceKey()]:[]);$Hi=($sg&&$sg[1]>29?$sg[0]-time():0);if($Hi>0)auth_error($gk,lang(147,ceil($Hi/60)));}function
connect_to_db(array&$gk){if(Admin::get()->getConfig()->hasServers()&&!Admin::get()->getConfig()->getServer(SERVER))auth_error($gk);$e=connect(true,$k);if(!$e)connection_error(nl2br(h($k)),$gk);return$e;}function
authenticate(array&$gk){$G=Admin::get()->authenticate($_GET["username"],get_password());if($G!==true)connection_error($G,$gk);}function
connection_error($k,array&$gk){$k=$k?:lang(3);if(preg_match('~^ +| +$~',get_password()))$k
.="<br>".lang(148);auth_error($gk,$k);}Admin::get()->init();$Wa=isset($_POST["auth"])?$_POST["auth"]:null;if($Wa){session_regenerate_id();$L=isset($Wa["server"])?$Wa["server"]:"";$cm=Admin::get()->getConfig()->getServer($L);$rd=$cm?$cm->getDriver():(isset($Wa["driver"])?$Wa["driver"]:"");$L=$cm?$L:trim($L);$U=isset($Wa["username"])?$Wa["username"]:"";$E=isset($Wa["password"])?$Wa["password"]:"";if($cm&&$cm->hasCredentials()&&$U==""&&$E==""){$U=$cm->getUsername();$E=$cm->getPassword();}$i=$cm?$cm->getDatabase():(isset($Wa["db"])?$Wa["db"]:"");save_login($rd,$L,$U,$E,$i);if($Wa["permanent"]){$w=implode("-",array_map("base64_encode",[$rd,$L,$U,$i]));$Ck=Admin::get()->getPrivateKey(true);$Pd=$Ck?encrypt_string($E,$Ck):false;$gk[$w]="$w:".base64_encode($Pd?:"");cookie("neo_permanent",implode(" ",$gk));}if(count($_POST)==1||DRIVER!=$rd||SERVER!=$L||$_GET["username"]!==$U||DB!=$i)redirect(auth_url($rd,$L,$U,$i));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(["pwds","db","dbs","queries"]as$w)set_session($w,null);unset_permanent($gk);redirect(SERVER_HOME_URL,lang(149));}elseif($gk&&!$_SESSION["pwds"]){session_regenerate_id();$Ck=Admin::get()->getPrivateKey();foreach($gk
as$w=>$W){list(,$Jb)=explode(":",$W);list($rd,$L,$U,$i)=array_map("base64_decode",explode("-",$w));$E=$Ck?decrypt_string(base64_decode($Jb),$Ck):false;save_login($rd,$L,$U,$E,$i);}}function
unset_permanent(array&$gk){foreach($gk
as$w=>$W){list($rd,$L,$U,$i)=array_map("base64_decode",explode("-",$w));if($rd==DRIVER&&$L==SERVER&&$U==$_GET["username"]&&$i==DB)unset($gk[$w]);}cookie("neo_permanent",implode(" ",$gk));}function
auth_error(array&$gk,$k=null){$gm=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$gm]||$_GET[$gm])&&!$_SESSION["token"])$k=lang(150);else{restart_session();add_invalid_login();$E=get_password();if($E!==null){if($E===false)$k=lang(151);delete_login(DRIVER,SERVER,$_GET["username"]);}unset_permanent($gk);}}if(!$_COOKIE[$gm]&&$_GET[$gm]&&ini_bool("session.use_only_cookies"))$k=lang(152);if(!$k)$k=lang(3);Admin::get()->addError($k);print_login_page();}function
print_login_page(){$Nj=session_get_cookie_params();cookie("neo_key",($_COOKIE["neo_key"]?:Random::strongKey()),$Nj["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header(lang(32),null);echo"<form action='' method='post'>\n","<div>";if(print_hidden_fields($_POST,["auth"]))echo"<p class='message'>".lang(153)."\n";echo"</div>\n";Admin::get()->printLoginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!DRIVER)print_login_page();if(isset($_GET["username"])&&!defined('AdminNeo\DRIVER_EXTENSION')){Admin::get()->addError(lang(154,implode(", ",Drivers::getExtensions(DRIVER))));unset($_SESSION["pwds"][DRIVER]);unset_permanent($gk);page_header(lang(155),false);page_footer("auth");exit;}if(!isset($_GET["username"])||get_password()===null)print_login_page();validate_server_input($gk);check_invalid_login($gk);Admin::get()->getConfig()->applyServer(SERVER);$e=connect_to_db($gk);authenticate($gk);create_driver($e);if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){Admin::get()->addError(lang(156));page_header(lang(7));page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($Wa&&$_POST["token"])$_POST["token"]=get_token();if($_POST){if(!verify_token()){Admin::get()->addError(lang(156).' '.lang(157));$_POST=[];}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$k=lang(158,"'post_max_size'");if(isset($_GET["sql"]))$k
.=' '.lang(159);Admin::get()->addError($k);}if(isset($_GET["settings"])){$M=Admin::get()->getSettings();$jm=array_merge(Admin::get()->getSettingsRows(1),Admin::get()->getSettingsRows(2),Admin::get()->getSettingsRows(3));if($_POST){$Nj=[];foreach($jm
as$w=>$I){if(isset($_POST[$w])){$ro=$_POST[$w]===""||(is_array($_POST[$w])&&in_array("",$_POST[$w]));$Nj[$w]=(!$ro?$_POST[$w]:null);}}$M->updateParameters($Nj);redirect(remove_from_uri());}$S=lang(145);page_header($S,[$S]);echo"<form id='settings' action='' method='post'>\n","<table class='box'>\n";foreach($jm
as$I)echo$I;echo"</table>\n","<p>","<input type='submit' value='".lang(118),"' class='button default hidden'>",input_token(),"</p>\n","</form>\n",script("initSettingsForm();");page_footer();exit;}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(DB==""&&isset($_GET["ns"]))redirect(remove_from_uri('ns'));if(!(DB!=""?Connection::get()->selectDatabase(DB):(isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill"))){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){Admin::get()->addError(lang(160));header("HTTP/1.1 404 Not Found");page_header(lang(31).": ".h(DB),true);}else{if($_POST["db"])queries_redirect(substr(ME,0,-1),lang(161),drop_databases($_POST["db"]));$S=h(Drivers::get(DRIVER).": ".Admin::get()->getServerName(SERVER));page_header($S,false);$th=['privileges'=>[lang(73),"users"],'processlist'=>[lang(162),"list"],'variables'=>[lang(163),"variable"],'status'=>[lang(164),"status"],];$uh="";foreach($th
as$w=>$W){if(support($w))$uh
.="<a href='".h(ME)."$w='>".icon($W[1])."$W[0]</a>";}if($uh)echo"<p class='links top-links'>$uh</p>\n";echo"<p>".lang(165,Drivers::get(DRIVER),"<b>".h(Connection::get()->getVersion())."</b>","<b>".DRIVER_EXTENSION."</b>")."\n","<p>".lang(166,"<b>".h(logged_user())."</b>")."\n";$h=Admin::get()->getDatabases();if($h){$Gl=support("scheme");$Ja=collations();echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr>".(support("database")?"<th>":"")."<th aria-sort='ascending'>".lang(31).(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".lang(167)."</a>":"")."<td>".lang(47)."<td>".lang(168)."<td>".lang(169)." - <a href='".h(ME)."dbsize=1'>".lang(170)."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n","<tbody>\n";$h=($_GET["dbsize"]?count_tables($h):array_flip($h));foreach($h
as$i=>$R){$ql=h(ME)."db=".urlencode($i);$s=h("Db-".$i);echo"<tr>".(support("database")?"<th class='actions'>".checkbox("db[]",$i,in_array($i,(array)$_POST["db"]),"","","",$s):""),"<th><a href='$ql' id='$s'>".h($i)."</a>";$Qb=h(db_collation($i,$Ja));echo"<td>".(support("database")?"<a href='$ql".($Gl?"&amp;ns=":"")."&amp;database=' title='".lang(70)."'>$Qb</a>":$Qb),"<td align='right'><a href='$ql&amp;schema=' id='tables-".h($i)."' title='".lang(72)."'>".($_GET["dbsize"]?$R:"?")."</a>","<td align='right' id='size-".h($i)."'>".($_GET["dbsize"]?db_size($i):"?"),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: event => tableClick(event, true)});"),"</table>\n","</div>\n";if(support("database"))echo"<div class='table-footer'><div class='field-sets'>\n","<fieldset><legend>",lang(171)," <span id='selected'></span></legend><div class='fieldset-content'>\n",input_hidden("all"),script("qsl('input').onclick = countDbs;"),"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(),"\n","</div></fieldset>\n","</div></div>\n",script("initTableFooter()");echo"</div>\n",input_token(),"</form>\n",script("tableCheck();");}}echo'<p class="links"><a href="'.h(ME).'database=">'.icon("database-add").lang(76)."</a>\n";page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~(?<=[?&])db=[^&]+~','\\0&ns='.urlencode(get_schema()),relative_uri()));if(!set_schema($_GET["ns"])){Admin::get()->addError(lang(173));header("HTTP/1.1 404 Not Found");page_header(lang(82).": ".h($_GET["ns"]),true);page_footer("ns");exit;}}}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$m=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$K=[idf_escape($_GET["field"])];$G=Driver::get()->select($a,$K,[where($_GET,$m)],$K);$I=($G?$G->fetchRow():[]);echo
Connection::get()->formatValue($I[0],$m[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$m=fields($a);if(!$m)Admin::get()->addError(error()?:lang(79));$Q=table_status1($a,true);$B=Admin::get()->getTableName($Q);$pl=[];foreach($m
as$w=>$l)$pl+=$l["privileges"];$S=$m&&is_view($Q)?$Q['Engine']=='materialized view'?lang(174):lang(175):lang(9);$en=$B!=""?$B:h($a);page_header("$S: $en",[$en]);$mg=null;if(isset($pl["insert"])||!support("table"))$mg=[];Admin::get()->printTableMenu($Q,$mg);$eg=[];if(!preg_match("~sqlite|mssql|pgsql~",DIALECT)&&isset($Q["Engine"]))$eg[]=lang(176).": ".h($Q["Engine"]);if(isset($Q["Collation"]))$eg[]=lang(47).": ".h($Q["Collation"]);if($eg)echo"<p>",implode(", ",$eg),"</p>";if($m)Admin::get()->printTableStructure($m);$ac=$Q["Comment"];if($ac!="")echo"<p class='keep-lines'>",lang(48),": ",Admin::get()->formatComment($ac),"</p>\n";if(!is_view($Q))$Ed='<a href="'.h(ME).'create='.urlencode($a).'">'.icon("edit").lang(36)."</a>";elseif(support("view"))$Ed='<a href="'.h(ME).'view='.urlencode($a).'">'.icon("edit").lang(37)."</a>";else$Ed="";if($Ed&&($eg||$m||$ac!="")){echo"<p id='table-links' class='links'>",$Ed;$Km=Admin::get()->getStructureDumpFormats();if($Km){$vc=Admin::get()->getSettings()->getParameter("copyFormat");if(!isset($Km[$vc]))$vc=key($Km);echo"<span>","<a id='copy-table-structure' href='".h(ME)."dump=".urlencode($a)."&format=".urlencode($vc)."' class='jsonly'>".icon("copy").lang(177,h($Km[$vc]))."</a>";$Ll=count($Km)>1;if($Ll)echo"<a href='#format-list' class='toggle jsonly' title='".lang(178)."'>".icon_chevron_down()."</a>";echo"</span></p>\n";if($Ll){echo"<ul id='format-list' class='select-menu hidden'>";foreach($Km
as$Te=>$B)echo"<li><a href='".h(ME)."dump=".urlencode($a)."&format=".urlencode($Te)."'>".h($B)."</a></li>";echo"</ul>",script("initToggles(gid('table-links'));");}echo
script("initCopyTableStructure();");}else
echo"</p>\n";}$Oj=Driver::get()->getParentTables($a);if($Oj){echo"<h2>".lang(179)."</h2>\n";Admin::get()->printRelatedTables($Oj);}if(Driver::get()->getPartitionBy()&&str_contains(isset($Q["Create_options"])?$Q["Create_options"]:"","partitioned")){$Zj=Driver::get()->getPartitionsInfo($a);if($Zj){echo"<h2 id='partitions'>".lang(50)."</h2>\n";Admin::get()->printTablePartitions($Zj);if(DIALECT!="pgsql"&&$Ed)echo"<p class='links'>$Ed</p>\n";}}$fg=Driver::get()->getInheritedTables($a);if($fg){echo"<h2 id='inherited-by'>".lang(180)."</h2>\n";Admin::get()->printRelatedTables($fg);}if(support("indexes")&&Driver::get()->supportsIndex($Q)){echo"<h2 id='indexes'>".lang(181)."</h2>\n";$v=indexes($a);if($v)Admin::get()->printTableIndexes($v,$Q);if(Driver::get()->supportsAlterIndex($Q))echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.icon("edit").lang(182)."</a>\n";}if(!is_view($Q)){if(fk_support($Q)){echo"<h2 id='foreign-keys'>".lang(93)."</h2>\n";$Re=foreign_keys($a);if($Re){echo"<table>\n","<thead><tr><th>".lang(183)."<td>".lang(184)."<td>".lang(96)."<td>".lang(95)."<td></thead>\n";foreach($Re
as$B=>$p)echo"<tr title='".h($B)."'>","<th><i>".implode("</i>, <i>",array_map('AdminNeo\h',$p["source"]))."</i>","<td><a href='".h($p["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($p["db"]),ME):($p["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($p["ns"]),ME):ME))."table=".urlencode($p["table"])."'>".($p["db"]!=""&&$p["db"]!=DB?"<b>".h($p["db"])."</b>.":"").($p["ns"]!=""&&$p["ns"]!=$_GET["ns"]?"<b>".h($p["ns"])."</b>.":"").h($p["table"])."</a>","(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$p["target"]))."</i>)","<td>".h($p["on_delete"]),"<td>".h($p["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($B)).'">'.lang(185).'</a>',"\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.icon("add").lang(186)."</a>\n";}if(support("check")){echo"<h2 id='checks'>".lang(187)."</h2>\n";$Db=Driver::get()->checkConstraints($a);if($Db){echo"<table cellspacing='0'>\n";foreach($Db
as$w=>$W)echo"<tr title='".h($w)."'>","<td><code class='jush-".DIALECT."'>".truncate_utf8(preg_replace('~\s+~',' ',ltrim($W))),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($w))."'>".lang(185)."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.icon("add").lang(188)."</a>\n";}}if(support(is_view($Q)?"view_trigger":"trigger")){echo"<h2 id='triggers'>".lang(189)."</h2>\n";$Vn=triggers($a);if($Vn){echo"<table>\n";foreach($Vn
as$w=>$W)echo"<tr><td>".h($W[0])."<td>".h($W[1])."<th>".h($w)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($w))."'>".lang(185)."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.icon("add").lang(190)."</a>\n";}}elseif(isset($_GET["schema"])){$En=h(": ".DB.($_GET["ns"]?".$_GET[ns]":""));page_header(lang(72).$En,[lang(72)]);$hn=[];$in=[];$ye=[];$pa=($_GET["schema"]?:$_COOKIE["neo_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$pa,$A,PREG_SET_ORDER);foreach($A
as$_){$hn[$_[1]]=[(float)$_[2],(float)$_[3]];$in[]="\n'".js_escape($_[1])."': [ $_[2], $_[3] ]";}$qh=1.4;$Kn=0;$gb=-1;$El=[];$al=[];$ih=[];$Ka=Driver::get()->getAllFields();foreach(table_status('',true)as$P=>$Q){if(is_view($Q))continue;$qk=0;$El[$P]["fields"]=[];foreach(isset($Ka[$P])?$Ka[$P]:[]as$l){$qk=round($qk+$qh,2);$ye[$P][$l["field"]]=$qk;$El[$P]["fields"][$l["field"]]=$l;}$El[$P]["pos"]=(isset($hn[$P])?$hn[$P]:[$Kn,0]);foreach(Admin::get()->getForeignKeys($P)as$W){if(!$W["db"]){$gh=$gb;if((isset($hn[$P][1])?$hn[$P][1]:0)||(isset($hn[$W["table"]][1])?$hn[$W["table"]][1]:0))$gh=min(floatval(isset($hn[$P][1])?$hn[$P][1]:0),floatval(isset($hn[$W["table"]][1])?$hn[$W["table"]][1]:0))-1;else$gb-=.1;while($ih[(string)$gh])$gh-=.0001;$El[$P]["references"][$W["table"]][(string)$gh]=[$W["source"],$W["target"]];$al[$W["table"]][$P][(string)$gh]=$W["target"];$ih[(string)$gh]=true;}}$Kn=max($Kn,round($El[$P]["pos"][0]+$qh+$qk+1,2));}echo"<div id='schema' style='height: {$Kn}em;'>\n";foreach($El
as$B=>$P){$Cf=round($qh+count($P["fields"])*$qh+0.4,2);echo"<div class='table' style='top: ".$P["pos"][0]."em; left: ".$P["pos"][1]."em; height: {$Cf}em;'>","<div class='content'>",'<h4><a href="'.h(ME).'table='.urlencode($B).'">'.h($B)."</a></h4>","<ul>";foreach($P["fields"]as$l){$W='<span '.type_class($l["type"]).' title="'.h($l["type"].($l["length"]?"($l[length])":"").($l["null"]?" NULL":'')).'">'.h($l["field"]).'</span>';echo"<li>".($l["primary"]?"<i>$W</i>":$W)."</li>";}echo"</ul>","</div>";foreach((array)$P["references"]as$sn=>$cl){foreach($cl
as$gh=>$Wk){$hh=$gh-(isset($hn[$B][1])?$hn[$B][1]:0);$r=0;foreach($Wk[0]as$tm){echo"\n<div class='references outgoing' title='",h($sn),"' id='refs$gh-$r' style='left: {$hh}em; top: ",$ye[$B][$tm],"em;'>","<div style='width: ".(-$hh)."em;'></div>","</div>";$r++;}}}foreach((array)$al[$B]as$sn=>$cl){foreach($cl
as$gh=>$d){$hh=$gh-(isset($hn[$B][1])?$hn[$B][1]:0);$r=0;foreach($d
as$rn){echo"\n<div class='references incoming' title='",h($sn),"' id='refd$gh-$r' style='left: {$hh}em; top: ".$ye[$B][$rn]."em;'>","<svg viewBox='0 0 22 22' fill='currentColor'><path d='M11,19l10,-8l-10,-8l0,16Z'/></svg>","<div style='width: ".(-$hh)."em;'></div>","</div>";$r++;}}}echo"\n</div>\n";}foreach($El
as$B=>$P){foreach((array)$P["references"]as$sn=>$cl){if($El[$sn]){foreach($cl
as$gh=>$Wk){$gi=$Kn;$Oh=-10;foreach($Wk[0]as$w=>$tm){$rk=$P["pos"][0]+$ye[$B][$tm];$sk=$El[$sn]["pos"][0]+$ye[$sn][$Wk[1][$w]];$gi=round(min($gi,$rk,$sk),2);$Oh=round(max($Oh,$rk,$sk),2);}echo"<div class='references vertical' id='refl$gh' style='left: $gh"."em; top: $gi"."em;'>"."<div style='height: ".round($Oh-$gi,2)."em;'></div></div>\n";}}}}echo"</div>\n",script("initSchema('".js_escape(DB)."', $Kn, {".implode(",",$in)."})"),"<p class='links'>","<a href='",(ME."schema=".urlencode($pa)),"' id='schema-link'>",lang(191),"</a>","</p>\n";}elseif(isset($_GET["dump"])){$a=$_GET["dump"];$M=Admin::get()->getSettings();$Km=Admin::get()->getStructureDumpFormats();$Gc=Admin::get()->getDataDumpFormats();$wc=is_ajax()&&$a!="";if($wc){$vc=isset($_GET["format"])?$_GET["format"]:null;if(!isset($Km[$vc])){reset($Km);$vc=key($Km);}$M->updateParameter("copyFormat",$vc);$_POST=["format"=>$vc,"output"=>"text","db_style"=>"","table_style"=>"CREATE","tables"=>[$a],"triggers"=>(support("trigger")?"1":""),"data_style"=>"",];}if($_POST){$Bd=isset($Km[$_POST["format"]]);$Ad=isset($Gc[$_POST["format"]]);if(!$wc)$M->updateParameters(["dumpFormat"=>$_POST["format"],"dumpOutput"=>$_POST["output"],]+($Bd?["dumpDbStyle"=>$_POST["db_style"],"dumpTypes"=>isset($_POST["types"])?$_POST["types"]:(support("type")?"":null),"dumpRoutines"=>isset($_POST["routines"])?$_POST["routines"]:(support("routine")?"":null),"dumpEvents"=>isset($_POST["events"])?$_POST["events"]:(support("event")?"":null),"dumpTableStyle"=>$_POST["table_style"],"dumpAutoIncrement"=>isset($_POST["auto_increment"])?$_POST["auto_increment"]:"","dumpTriggers"=>isset($_POST["triggers"])?$_POST["triggers"]:(support("trigger")?"":null),]:[])+($Ad?["dumpDataStyle"=>$_POST["data_style"],]:[]));$kn=($Bd?$_POST["table_style"]:"CREATE");$Ic=($Ad?$_POST["data_style"]:"");if(DB!="")$h=[DB];else{$h=isset($_POST["databases"])?$_POST["databases"]:[];if(is_string($h))$h=explode("\n",rtrim(str_replace("\r","",$h),"\n"));}$Fl=isset($_POST["schemas"])?$_POST["schemas"]:[];$R=array_flip(isset($_POST["tables"])?$_POST["tables"]:[])+array_flip(isset($_POST["data"])?$_POST["data"]:[]);if(count($R)==1)$Pf=key($R);elseif(count($Fl)==1)$Pf=$Fl[0];elseif(count($h)==1)$Pf=$h[0];else$Pf=Admin::get()->getServerName(SERVER,true,"server");$le=dump_headers($Pf,DB==""||$_GET["ns"]===""||count($R)>1);$Dg=preg_match('~sql~',$_POST["format"]);$Hc=$Dg&&$Ic&&!$kn&&DIALECT!="sql";if($Dg){if(!$wc)echo"-- AdminNeo ".VERSION." ".Drivers::get(DRIVER)." ".Connection::get()->getVersion()." dump\n\n";if(DIALECT=="sql"){if(!$wc)echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($Ic?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";Connection::get()->query("SET time_zone = '+00:00'");Connection::get()->query("SET sql_mode = ''");}}$Mm=($Bd?$_POST["db_style"]:"");foreach($h
as$i){if(Connection::get()->selectDatabase($i)){Admin::get()->dumpDatabase($i);if($Dg){if($Mm)echo
create_database_sql($i,$Mm),use_sql($i,$Mm)."\n";$Gj="";if($_POST["types"]){foreach(types()as$s=>$T){$Tc=type_definition($s);$Qi=($Tc["kind"]=='d'?"DOMAIN":"TYPE");if($Tc["definition"])$Gj
.=($Mm!='DROP+CREATE'?"DROP $Qi IF EXISTS ".idf_escape($T).";;\n":"")."CREATE $Qi ".idf_escape($T)." $Tc[definition];\n\n";else$Gj
.="-- Could not export type $T\n\n";}}if($_POST["routines"]){foreach(routines()as$I){$B=$I["ROUTINE_NAME"];$rl=$I["ROUTINE_TYPE"];$f=create_routine($rl,["name"=>$B]+routine($I["SPECIFIC_NAME"],$rl));set_utf8mb4($f);$Gj
.=($Mm!='DROP+CREATE'?"DROP $rl IF EXISTS ".idf_escape($B).";;\n":"")."$f;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$I){$f=remove_definer(Connection::get()->getValue("SHOW CREATE EVENT ".idf_escape($I["Name"]),3));set_utf8mb4($f);$Gj
.=($Mm!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($I["Name"]).";;\n":"")."$f;;\n\n";}}echo($Gj&&DIALECT=='sql'?"DELIMITER ;;\n\n$Gj"."DELIMITER ;\n\n":$Gj);}if($kn||$Ic){foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?[""]:Admin::get()->getSchemas(true)))as$El){if($El!="")set_schema($El);$nn=table_status('',true);$fn=array_keys($nn);$fd=false;if($Hc&&$fn){$bl=[];foreach($fn
as$B){if(!is_view($nn[$B])&&(DB==""||$_GET["ns"]===""||in_array($B,(array)$_POST["data"]))){foreach(foreign_keys($B)as$p)$bl[$B][]=$p["table"];}}$vj=dump_table_order($fn,$bl);if($vj)$fn=$vj;else$fd=function_exists('AdminNeo\foreign_key_checks_sql');}if($fd)echo
foreign_key_checks_sql(false)."\n";$Jo=[];foreach($fn
as$B){$Q=$nn[$B];$P=(DB==""||$_GET["ns"]===""||in_array($B,(array)$_POST["tables"]));$g=(DB==""||$_GET["ns"]===""||in_array($B,(array)$_POST["data"]));if($P||$g){$Hn=null;if($le=="tar"){$Hn=new
TmpFile();ob_start([$Hn,'write'],1e5);}$yc=($P||!$Bd?$kn:"");Admin::get()->dumpTable($B,$yc,(is_view($Q)?2:0));if(is_view($Q)&&$le!="tar")$Jo[]=$B;elseif($g){$m=fields($B);Admin::get()->dumpData($B,$Ic,"SELECT *".convert_fields($m,$m)." FROM ".table($B));if($Dg&&!$yc&&$_POST["auto_increment"]&&function_exists('AdminNeo\restart_sequences_sql'))echo"\n".restart_sequences_sql($B);}if($Dg&&$_POST["triggers"]&&$P&&($Vn=trigger_sql($B)))echo"\nDELIMITER ;;\n$Vn\nDELIMITER ;\n";if($le=="tar"){ob_end_flush();tar_file((DB!=""?"":"$i/")."$B.csv",$Hn);}elseif($Dg)echo"\n";}}if($fd)echo
foreign_key_checks_sql(true)."\n";if($Dg&&$kn&&function_exists('AdminNeo\foreign_keys_sql')){foreach($nn
as$B=>$Q){$P=(DB==""||$_GET["ns"]===""||in_array($B,(array)$_POST["tables"]));if($P&&!is_view($Q))echo
foreign_keys_sql($B);}}foreach($Jo
as$Ho)Admin::get()->dumpTable($Ho,$kn,1);if($le=="tar")echo
pack("x512");}}}}if($Dg&&!$wc)echo"-- ".gmdate("Y-m-d H:i:s e")."\n";exit;}$B=DB!=""?h(DB):h(Admin::get()->getServerName(SERVER));page_header(lang(75).": $B",($_GET["export"]!=""?["table"=>$_GET["export"]]:[lang(75)]));echo"<form id='dump-form' action='' method='post'>\n","<table class='box'>\n";$Mc=['','USE','DROP+CREATE','CREATE'];$kn=['','DROP+CREATE','CREATE'];$Ic=['','TRUNCATE+INSERT','INSERT'];if(DIALECT=="sql")$Ic[]='INSERT+UPDATE';echo"<tr><th>",lang(178),"</th><td>",html_radios("format",$Km+$Gc,$M->getParameter("dumpFormat","sql")),"</td></tr>\n";if(DIALECT!="sqlite"){echo"<tr><th id='label-db'>",lang(31),"</th>","<td>",html_select('db_style',$Mc,$M->getParameter("dumpDbStyle",DB==""?"CREATE":""),"","label-db"),"<span class='labels'>";if(support("type"))echo
checkbox("types",1,$M->getParameter("dumpTypes"),lang(112));if(support("routine"))echo
checkbox("routines",1,$M->getParameter("dumpRoutines",$_GET["dump"]==""?"1":""),lang(192));if(support("event"))echo
checkbox("events",1,$M->getParameter("dumpEvents",$_GET["dump"]==""?"1":""),lang(193));echo"</span></td></tr>";}echo"<tr><th id='label-tables'>",lang(168),"</th><td>",html_select('table_style',$kn,$M->getParameter("dumpTableStyle","DROP+CREATE"),"","label-tables")," <span class='labels'>",checkbox("auto_increment",1,$M->getParameter("dumpAutoIncrement"),lang(49));if(support("trigger"))echo
checkbox("triggers",1,$M->getParameter("dumpTriggers","1"),lang(189));echo"</span></td></tr>","<tr><th id='label-data'>",lang(194),"</th><td>",html_select("data_style",$Ic,$M->getParameter("dumpDataStyle","INSERT"),"","label-data"),"</td></tr>","<tr><th>",lang(195),"</th><td>",html_radios("output",Admin::get()->getDumpOutputs(),$M->getParameter("dumpOutput","file")),"</td></tr>\n","</table>\n",script("initDumpFormats(".json_encode(array_keys($Km)).", ".json_encode(array_keys($Gc)).");"),"<p>","<input type='submit' class='button default' value='",lang(75),"'>",input_token(),"</p>\n","<table>\n",script("qsl('table').onclick = dumpClick;");$xk=[];if(DB!=""&&$_GET["ns"]===""){echo"<thead><tr><th>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly' title='".lang(196)."'>".lang(82)."</label>".script("gid('check-schemas').onclick = partial(formCheck, /^schemas\\[/);",""),"</thead>\n";foreach(Admin::get()->getSchemas()as$El){if(!information_schema(DB,$El))echo"<tr><td>".checkbox("schemas[]",$El,true,$El,"","block")."\n";}}elseif(DB!=""){$Gb=($a!=""?"":" checked");echo"<thead><tr>","<th><label class='block'><input type='checkbox' id='check-tables'$Gb class='jsonly' title='".lang(196)."'>".lang(9)."</label>".script("gid('check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th class='right'><label class='block'>".lang(194)."<input type='checkbox' id='check-data'$Gb class='jsonly' title='".lang(196)."'></label>".script("gid('check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$Jo="";$mn=tables_list();foreach($mn
as$B=>$T){$wk=preg_replace('~_.*~','',$B);$Gb=($a==""||$a==(substr($a,-1)=="%"?"$wk%":$B));$Bk="<tr><td>".checkbox("tables[]",$B,$Gb,$B,"","block");if($T!==null&&!preg_match('~table~i',$T))$Jo
.="$Bk\n";else
echo"$Bk<td class='right'><label class='block'><span id='Rows-".h($B)."'></span>".checkbox("data[]",$B,$Gb)."</label>\n";$xk[$wk]++;}echo$Jo;if($mn)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$h=Admin::get()->getDatabases();echo"<thead><tr><th>","<label class='block'>".($h?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly' title='".lang(196)."'>".script("gid('check-databases').onclick = partial(formCheck, /^databases\\[/);",""):"").lang(31)."</label>","</thead>\n";if($h){foreach($h
as$i){if(!information_schema($i)){$wk=preg_replace('~_.*~','',$i);echo"<tr><td>".checkbox("databases[]",$i,$a==""||$a=="$wk%",$i,"","block")."\n";$xk[$wk]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo"</table>\n","</form>\n";$th=[];foreach($xk
as$w=>$W){if($w!=""&&$W>1)$th[]="<a href='".h(ME)."dump=".urlencode("$w%")."'>".icon("check").h($w)."*</a>";}if($th)echo"<p class='links'>",implode("",$th),"</p>\n";}elseif(isset($_GET["privileges"])){$En=DB!=""?h(": ".DB):"";page_header(lang(73).$En,[lang(73)]);echo'<p class="links top-links"><a href="',h(ME),'user=">',icon("user-add"),lang(197),"</a></p>\n";$G=Connection::get()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$kf=$G;if(!$G)$G=Connection::get()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''>\n";hidden_fields_get();echo
input_hidden("db",DB);if(!$kf)echo
input_hidden("grant");echo"\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr><th>".lang(6)."<th>".lang(5)."<th></thead>\n";while($I=$G->fetchAssoc())echo'<tr><td>'.h($I["User"])."<td>".h($I["Host"]).'<td><a href="'.h(ME.'user='.urlencode($I["User"]).'&host='.urlencode($I["Host"])).'">'.lang(39)."</a>\n";if(!$kf||DB!="")echo"<tr><td><input class='input' name='user' autocapitalize='off'>"."<td><input class='input' name='host' value='localhost' autocapitalize='off'>"."<td><input type='submit' class='button' value='".lang(39)."'>\n";echo"</table>\n","</div>\n","</form>\n";}elseif(isset($_GET["sql"])){function
get_unsupported_query($F,$vm){if(DIALECT!="sqlite"||preg_match("~^(?:$vm|;)*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$F,$_)===0)return
null;return
preg_match('~ATTACH~i',isset($_[1])?$_[1]:"")?'ATTACH':'VACUUM INTO';}$M=Admin::get()->getSettings();$ph=Driver::get()->lineComment();$vm="(?:\\s|\xEF\xBB\xBF|/\\*[\s\S]*?\\*/|(?:$ph)[^\n]*\n?|--\r?\n)";if($_POST["export"]&&!get_unsupported_query($_POST["query"],$vm)){$M->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers("sql");Admin::get()->dumpTable("","");Admin::get()->dumpData("","table",$_POST["query"]);exit;}restart_session();$Hf=&get_session("queries");$Gf=&$Hf[DB];if($_POST["clear"]){$Gf=[];redirect(remove_from_uri("history"));}stop_session();$S=isset($_GET["import"])?lang(74):lang(41);page_header($S,[$S]);if($_POST){$Ze=false;if(!isset($_GET["import"]))$F=$_POST["query"];elseif($_POST["webfile"]){$Sf=Admin::get()->getImportFilePath();if($Sf){if(file_exists($Sf))$Ze=fopen($Sf,"rb");elseif(file_exists("$Sf.gz"))$Ze=fopen("compress.zlib://$Sf.gz","rb");}$F=$Ze?fread($Ze,1e6):false;}else$F=get_file("sql_file",true,";");if(is_string($F)){if(($Uh=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($Uh,strval(2*strlen($F)+memory_get_usage()+8e6)));if($F!=""&&strlen($F)<1e6){$Jk=$F.(preg_match("~;[ \t\r\n]*\$~",$F)?"":";");if(!$Gf||first(end($Gf))!=$Jk){restart_session();$Gf[]=[$Jk,time()];set_session("queries",$Hf);stop_session();}}$Vc=";";$Wc=1;$Si=0;$Ld=true;$uc=false;$kc=connect();if($kc&&DB!=""){$kc->selectDatabase(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$kc);}$Zb=0;$Xd=[];$Pj='[\'"'.(DIALECT=="sql"?'`':(DIALECT=="sqlite"?'`[':(DIALECT=="mssql"?'[':''))).']|/\*|'.$ph.'|$'.(DIALECT=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$Ln=microtime(true);$Gc=Admin::get()->getDataDumpFormats();unset($Gc["sql"]);while($F!=""){if(!$Si&&preg_match("~^$vm*+DELIMITER\\s+(\\S+)~i",$F,$_)){$Vc=preg_quote($_[1]);$Wc=strlen($_[1]);$Ve=Admin::get()->formatSqlCommandQuery(trim($_[0]));if($Ve!="")echo"<pre><code class='jush-".DIALECT."'>$Ve</code></pre>\n";$F=substr($F,strlen($_[0]));}elseif(!$Si&&DIALECT=="pgsql"&&preg_match("~^($vm*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$F,$_)){$Vc="\n\\\\\\.\r?\n";$Wc=3;$uc=true;$Si=strlen($_[0]);}else{preg_match("($Vc\\s*|$Pj)",$F,$_,PREG_OFFSET_CAPTURE,$Si);list($Xe,$qk)=$_[0];if(!$Xe&&$Ze&&!feof($Ze))$F
.=fread($Ze,1e5);else{if(!$Xe&&rtrim($F)=="")break;$Si=$qk+strlen($Xe);if($Xe&&!preg_match("(^$Vc)",$Xe)){$ub=Driver::get()->hasCStyleEscapes()||(DIALECT=="pgsql"&&($qk>0&&strtolower($F[$qk-1])=="e"));$ek='(';if($Xe=='/*')$ek
.='\*/';elseif($Xe=='[')$ek
.=']';elseif(preg_match("~^(?:$ph)~",$Xe))$ek
.="\n";else$ek
.=preg_quote($Xe).($ub?"|\\\\.":"");$ek
.='|$)s';while(preg_match($ek,$F,$_,PREG_OFFSET_CAPTURE,$Si)){$xl=$_[0][0];if(!$xl&&$Ze&&!feof($Ze))$F
.=fread($Ze,1e5);else{$Si=$_[0][1]+strlen($xl);if(!isset($xl[0])||$xl[0]!="\\")break;}}}elseif(DIALECT=="sqlite"&&preg_match("~^(?:$vm|;)*+\$~",substr($F,0,$qk+$Wc))){$F=substr($F,$Si);$Si=0;}else{$Ld=false;$Jk=substr($F,0,$qk+$Wc);$Zb++;$Bk="<pre id='sql-$Zb'><code class='jush-".DIALECT."'>".Admin::get()->formatSqlCommandQuery(trim($Jk))."</code></pre>\n";if($lo=get_unsupported_query($Jk,$vm)){echo$Bk,"<p class='error'>".lang(198,$lo)."\n";$Xd[]=" <a href='#sql-$Zb'>$Zb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Bk;ob_flush();flush();}$Em=microtime(true);if(Connection::get()->multiQuery($Jk)&&is_object($kc)&&preg_match("~^$vm*+USE\\b~i",$Jk))$kc->query($Jk);do{$G=Connection::get()->storeResult();if(Connection::get()->getError()){echo($_POST["only_errors"]?$Bk:""),"<p class='error'>",lang(199),(!empty(Connection::get()->getErrno())?" (".Connection::get()->getErrno().")":""),": ",error()."</p>\n";$Xd[]=" <a href='#sql-$Zb'>$Zb</a>";if($_POST["error_stops"])break
2;}else{$Bn=" <span class='time'>(".format_time($Em).")</span>";$Fd=(strlen($Jk)<1000?" <a href='".h(ME)."sql=".urlencode(trim($Jk))."'>".icon("edit").lang(39)."</a>":"");$Nk=Connection::get()->getQueryInfo();$Da=Connection::get()->getAffectedRows();$mi=Connection::get()->hasMoreResults();$Po=($_POST["only_errors"]||$mi?null:Driver::get()->warnings());$Ro=($mi?Connection::get()->getWarningsCount():0);$So="warnings-$Zb";if($Po)$To="<a href='#$So' class='toggle'>".lang(40).icon_chevron_down()."</a>";elseif($Ro)$To="<span>".lang(40).": $Ro</span>";else$To=null;$ge=$yj=null;$he="explain-$Zb";$ie=false;$je="export-$Zb";$y=0;if(is_object($G)){if(!$_POST["only_errors"])echo"<div class='table-result'>\n";$y=(int)$_POST["limit"];$yj=print_select_result($G,$kc,[],$y);if(!$_POST["only_errors"]){echo"<p class='links'>";$Mi=$G->getRowsCount();echo($Mi?($y&&$Mi>$y?lang(200,$y):"").lang(201,$Mi):""),$Bn,$Fd,$To;if($kc&&preg_match("~^($vm|\\()*+SELECT\\b~i",$Jk)&&($ge=explain($kc,$Jk)))echo"<a href='#$he' class='toggle'>Explain".icon_chevron_down()."</a>";$ie=true;echo"<a href='#$je' class='toggle'>".lang(75).icon_chevron_down()."</a>","</p>\n";}}else{if(preg_match("~^$vm*+(CREATE|DROP|ALTER)$vm++(DATABASE|SCHEMA)\\b~i",$Jk)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"]){echo"<p class='message' title='".h($Nk)."'>",lang(202,$Da),"$Bn $Fd";if($To)echo", $To";echo"</p>\n";}}if(!$_POST["only_errors"])echo
script("initToggles(qsl('p'));");if($Po)echo"<div id='$So' class='hidden'>\n$Po</div>\n";if($ge){echo"<div id='$he' class='hidden explain'>\n";print_select_result($ge,$kc,$yj);echo"</div>\n";}if($ie){echo"<form id='$je' action='' method='post' class='hidden'><p>\n",html_select("format",$Gc,$M->getParameter("exportFormat")),html_select("output",Admin::get()->getDumpOutputs(),$M->getParameter("exportOutput"))." ",input_hidden("query",$Jk),input_token()," <input type='submit' class='button' name='export' value='".lang(75)."'>";if(!$y)echo
script("qsl('input').onclick = function (event) { return sqlExport.call(this, event, '".js_escape(ME)."set=export-settings'); };","");echo"</p></form>\n";}if(is_object($G)&&!$_POST["only_errors"])echo"</div>\n";}$Em=microtime(true);}while(Connection::get()->nextResult());}$F=substr($F,$Si);$Si=0;if($uc){$Vc=";";$Wc=1;$uc=false;}}}}}if($Ld)echo"<p class='message'>".lang(203)."\n";else{$Vf=Connection::get()->inTransaction();Driver::get()->rollback();if($Vf)echo"<pre><code class='jush-".DIALECT."'>ROLLBACK -- AdminNeo</code></pre>\n";if($_POST["only_errors"]){$Vi=$Zb-count($Xd);echo"<p class='".($Vi?"message":"error")."'>".lang(204,$Zb-count($Xd))," <span class='time'>(".format_time($Ln).")</span>\n";}elseif($Xd&&$Zb>1)echo"<p class='error'>".lang(199).": ".implode("",$Xd)."\n";}}else
echo"<p class='error'>".upload_error($F)."\n";}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";if(!isset($_GET["import"])){$Jk=$_GET["sql"];if($_POST)$Jk=$_POST["query"];elseif($_GET["history"]=="all")$Jk=$Gf;elseif($_GET["history"]!="")$Jk=$Gf[$_GET["history"]][0];echo"<p>";textarea("query",$Jk,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."gid('form').onsubmit = partial(sqlSubmit, gid('form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"</p>","<p><input type='submit' class='button default' value='".lang(205)."' title='Ctrl+Enter'>",lang(206).": <input type='number' name='limit' class='input size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{echo"<div class='field-sets'>\n","<fieldset><legend>".lang(207)."</legend><div class='fieldset-content'>";$sf=(extension_loaded("zlib")?"[.gz]":"");if(ini_bool("file_uploads"))echo"SQL$sf (&lt; ".ini_get("upload_max_filesize")."B): <input type='file' name='sql_file[]' multiple>","<input type='submit' class='button default' value='".lang(205)."'>",file_upload_form_script("form","sql_file[]");else
echo
lang(208);echo"</div></fieldset>\n";$Sf=Admin::get()->getImportFilePath();if($Sf)echo"<fieldset><legend>".lang(209)."</legend><div class='fieldset-content'>",lang(210,"<code>".h($Sf)."$sf</code>")," <input type='submit' class='button default' name='webfile' value='".lang(211)."'>","</div></fieldset>\n";echo"</div>\n","<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:(isset($_GET["error_stops"])?$_GET["error_stops"]:true)),lang(212)),checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),lang(213)),input_token(),"</p>\n";if(!isset($_GET["import"]))Admin::get()->printAfterSqlCommand();if(!isset($_GET["import"])&&$Gf){echo"<div class='field-sets'>\n";print_fieldset_start("history",lang(214),"history",$_GET["history"]!="");for($W=end($Gf);$W;$W=prev($Gf)){$w=key($Gf);list($Jk,$Bn,$Jd)=$W;echo" <pre><code class='jush-".DIALECT."'>",truncate_utf8(preg_replace('~\s+~',' ',ltrim(preg_replace("~^(?:$ph).*~m",'',$Jk)))),"</code></pre>",'<p class="links">',"<a href='".h(ME."sql=&history=$w")."'>".icon("edit").lang(39)."</a>"," <span class='time' title='".@date('Y-m-d',$Bn)."'>".@date("H:i:s",$Bn).($Jd?" ($Jd)":"")."</span>","</p>";}echo"<p><input type='submit' class='button' name='clear' value='".lang(215)."'>\n","<a href='",h(ME."sql=&history=all")."' class='button light'>",icon("edit"),lang(216),"</a></p>\n";print_fieldset_end("history");echo"</div>\n";}echo"</form>\n";}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$m=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$m):""):where($_GET,$m));$no=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($m
as$B=>$l){if((!$no&&!isset($l["privileges"]["insert"]))||Admin::get()->getFieldName($l)=="")unset($m[$B]);}if($_POST&&!isset($_GET["select"])){$Bh=$_POST["referer"];if($_POST["insert"])$Bh=($no?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$Bh))$Bh=ME."select=".urlencode($a);$v=indexes($a);$fo=unique_array(isset($_GET["where"])?$_GET["where"]:[],$v);$Ok="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Bh,lang(217),(bool)Driver::get()->delete($a,$Ok,$fo?0:1));else{$hm=[];foreach($m
as$B=>$l){$W=process_input($l);if($W!==false&&$W!==null)$hm[idf_escape($B)]=$W;}if($no){if(!$hm)redirect($Bh);queries_redirect($Bh,lang(218),(bool)Driver::get()->update($a,$hm,$Ok,$fo?0:1));if(is_ajax()){page_headers();page_messages();exit;}}else{$G=Driver::get()->insert($a,$hm);$eh=($G?last_id($G):0);queries_redirect($Bh,lang(219,($eh?" $eh":"")),(bool)$G);}}}$I=null;if($Z){$K=[];foreach($m
as$B=>$l){if(isset($l["privileges"]["select"])){$Sa=($_POST["clone"]&&$l["auto_increment"]?"''":convert_field($l));$K[]=($Sa?"$Sa AS ":"").idf_escape($B);}}$I=[];if(!support("table"))$K=["*"];if($K){$G=Driver::get()->select($a,$K,[$Z],$K,[],(isset($_GET["select"])?2:1));if(!$G)Admin::get()->addError(error());else{$I=$G->fetchAssoc();if(!$I)$I=false;}if(isset($_GET["select"])&&(!$I||$G->fetchAssoc()))$I=null;}}if(!support("table")&&!$m){if(!$Z){$G=Driver::get()->select($a,["*"],[],["*"]);$I=($G?$G->fetchAssoc():false);if(!$I)$I=[Driver::get()->primary=>""];}if($I){foreach($I
as$w=>$W){if(!$Z)$I[$w]=null;$m[$w]=["field"=>$w,"null"=>($w!=Driver::get()->primary),"auto_increment"=>($w==Driver::get()->primary)];}}}if(isset($_POST["save"])?$_POST["save"]:false){$tk=[];foreach((isset($_POST["fields"])?$_POST["fields"]:[])as$w=>$W)$tk[bracket_escape($w,true)]=$W;$I=$tk+($I?:[]);}if($_POST["edit"]){$Hd=array_filter($m,function($l){return!(isset($l["generated"])?$l["generated"]:null);});}else$Hd=$m;edit_form($a,$Hd,$I,$no);}elseif(isset($_GET["create"])){$a=$_GET["create"];$Vj=Driver::get()->getPartitionBy();$Zj=$Vj?Driver::get()->getPartitionsInfo($a):[];$Yk=referencable_primary($a);$Re=[];foreach($Yk
as$en=>$l)$Re[str_replace("`","``",$en)."`".str_replace("`","``",$l["field"])]=$en;$Aj=[];$Q=[];if($a!=""){$Aj=fields($a);$Q=table_status1($a);if(count($Q)<2)Admin::get()->addError(lang(79));}$I=$_POST;$I["Comment"]=normalize_newlines($I["Comment"]);$I["fields"]=(array)$I["fields"];if($I["auto_increment_col"])$I["fields"][$I["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!Admin::get()->getErrors())Admin::get()->getSettings()->updateParameter("commentsOpened",isset($_POST["comments"])?$_POST["comments"]:null);if($_POST&&!process_fields($I["fields"])&&!Admin::get()->getErrors()){if($_POST["drop"])queries_redirect(substr(ME,0,-1),lang(220),drop_tables([$a]));else{$m=[];$Ka=[];$so=false;$Pe=[];$_j=reset($Aj);$Fa=" FIRST";foreach($I["fields"]as$w=>$l){$p=$Re[$l["type"]];$Yn=($p!==null?$Yk[$p]:$l);if($l["field"]!=""){if(!$l["generated"])$l["default"]=null;$Hk=process_field($l,$Yn);$Ka[]=[$l["orig"],$Hk,$Fa];if(!$_j||$Hk!==process_field($_j,$_j)){$m[]=[$l["orig"],$Hk,$Fa];if($l["orig"]!=""||$Fa)$so=true;}if($p!==null)$Pe[idf_escape($l["field"])]=($a!=""&&DIALECT!="sqlite"?"ADD":" ").format_foreign_key(['table'=>$Re[$l["type"]],'source'=>[$l["field"]],'target'=>[$Yn["field"]],'on_delete'=>$l["on_delete"],]);$Fa=" AFTER ".idf_escape($l["field"]);}elseif($l["orig"]!=""){$so=true;$m[]=[$l["orig"]];}if($l["orig"]!=""){$_j=next($Aj);if(!$_j)$Fa="";}}$Xj=[];if(in_array($I["partition_by"],$Vj)){foreach($I
as$w=>$W){if(preg_match('~^partition~',$w))$Xj[$w]=$W;}foreach($Xj["partition_names"]as$w=>$B){if($B===""){unset($Xj["partition_names"][$w]);unset($Xj["partition_values"][$w]);}}$Xj["partition_names"]=array_values($Xj["partition_names"]);$Xj["partition_values"]=array_values($Xj["partition_values"]);if($Xj==$Zj)$Xj=[];}elseif(str_contains(isset($Q["Create_options"])?$Q["Create_options"]:"","partitioned"))$Xj=null;$Wh=lang(221);if($a==""){cookie("neo_engine",isset($I["Engine"])?$I["Engine"]:"");$Wh=lang(222);}$B=trim($I["name"]);$Bh=ME.(support("table")?"table=":"select=").urlencode($B);$G=alter_table($a,$B,(DIALECT=="sqlite"&&($so||$Pe)?$Ka:$m),$Pe,($I["Comment"]!=$Q["Comment"]?$I["Comment"]:null),($I["Engine"]&&$I["Engine"]!=$Q["Engine"]?$I["Engine"]:""),($I["Collation"]&&$I["Collation"]!=$Q["Collation"]?$I["Collation"]:""),($I["Auto_increment"]!=""?number($I["Auto_increment"]):""),$Xj);if($G&&!Queries::$queries)redirect($Bh);queries_redirect($Bh,$Wh,$G);}}if($a!="")page_header(lang(36).": ".h($a),["table"=>$a,lang(36)]);else
page_header(lang(78),[lang(78)]);if(!$_POST){$bo=Driver::get()->getTypes();$I=["Engine"=>$_COOKIE["neo_engine"],"fields"=>[["field"=>"","type"=>(isset($bo["int"])?"int":(isset($bo["integer"])?"integer":"")),"on_update"=>""]],"partition_names"=>[""],];if($a!=""){$I=$Q;$I["name"]=$a;$I["fields"]=[];if(!$_GET["auto_increment"])$I["Auto_increment"]="";foreach($Aj
as$l){$l["generated"]=$l["generated"]?:(isset($l["default"])?"DEFAULT":"");$I["fields"][]=$l;}if($Vj){$I+=$Zj;$I["partition_names"][]="";$I["partition_values"][]="";}}}$Ng=[];if($I["Collation"])$Ng[$I["Collation"]]=true;foreach($I["fields"]as$l){if($l["collation"])$Ng[$l["collation"]]=true;}$Rb=Admin::get()->getCollations(array_keys($Ng));$Rd=Driver::get()->engines();foreach($Rd
as$Qd){if(!strcasecmp($Qd,$I["Engine"])){$I["Engine"]=$Qd;break;}}$Jh=max_input_vars(12,20);if($Jh){$Df=(count($I["fields"])>$Jh?"":" hidden");echo"<p".($Df?" id='max-fields' data-columns='$Jh'":"")." class='error$Df'>".max_input_vars_error()."\n";}echo"<form action='' method='post' id='form'>\n";if(support("columns")||$a==""){echo"<p>",lang(223),": ","<input class='input' name='name' data-maxlength='64' value='",h($I["name"]),"' autocapitalize='off'",(($a==""&&!$_POST)?" autofocus":""),">";if($Rd)echo" ",html_select("Engine",[""=>"(".lang(224).")"]+$Rd,$I["Engine"]),help_script_command("value",true);if($Rb&&!preg_match("~sqlite|mssql~",DIALECT))echo" ",html_select("Collation",[""=>"(".lang(94).")"]+$Rb,$I["Collation"]);echo" <input type='submit' class='button default' value='",lang(118),"'>","</p>";}if(support("columns")&&($a==""||!Driver::get()->isPartition($a))){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($I["fields"],$Rb,"TABLE",$Re);echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>",lang(49),": ","<input type='number' class='input size' name='Auto_increment' size='6' value='",h($I["Auto_increment"]),"'>";$dc=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$bc=$dc?"":"hidden";if(support("comment")){echo
checkbox("comments",1,$dc,lang(48),"editingCommentsClick(this);","jsonly")," ";if(preg_match('~\n~',$I["Comment"]))echo"<textarea name='Comment' rows='2' cols='20'",($bc?" class='$bc'":""),">",h($I["Comment"]),"</textarea>";else
echo"<input name='Comment' value='",h($I["Comment"]),"' data-maxlength='",(Connection::get()->isMinVersion("5.5")?2048:60),"' class='input $bc'>";}echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(118),"'>";}elseif($a!="")echo"<p>";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$a)),"</p>\n";if($Vj&&(DIALECT=="sql"||$a=="")){echo"<div class='field-sets'>\n";$Wj=preg_match('~RANGE|LIST~',$I["partition_by"]);print_fieldset_start("partition",lang(226),"split",(bool)$I["partition_by"]);echo"<p>",html_select("partition_by",array_merge([""],$Vj),$I["partition_by"]),help_script_command("value.replace(/./, 'PARTITION BY \$&')",true),script("qsl('select').onchange = partitionByChange;"),"(<input class='input' name='partition' value='",h($I["partition"]),"'>) ",lang(50),": ","<input type='number' name='partitions' class='input size ",($Wj||!$I["partition_by"]?"hidden":""),"' value='",h($I["partitions"]),"'>","</p>\n","<table id='partition-table'",($Wj?"":" class='hidden'"),">\n","<thead><tr><th>",lang(227),"</th><th>",lang(52),"</th></tr></thead>\n";foreach($I["partition_names"]as$w=>$W){echo"<tr>","<td><input class='input' name='partition_names[]' value='",h($W),"' autocapitalize='off'>";if($w==count($I["partition_names"])-1)echo
script("qsl('input').oninput = partitionNameChange;");echo"</td>","<td><input class='input' name='partition_values[]' value='",h(isset($I["partition_values"][$w])?$I["partition_values"][$w]:""),"'></td>","</tr>\n";}echo"</table>\n","</p>\n";print_fieldset_end("partition");echo"</div>\n";}echo
input_token(),"</form>\n";}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$ag=["PRIMARY","UNIQUE","INDEX"];$Q=table_status1($a,true);$Xf=Driver::get()->getIndexAlgorithms($Q);$e=Connection::get();$Dh=$e->isMariaDB();if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($Dh?"10.0.5":"5.6")?'|InnoDB':'').'~i',$Q["Engine"]))$ag[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($Dh?"10.2.2":"5.7")?'|InnoDB':'').'~i',$Q["Engine"]))$ag[]="SPATIAL";if($Dh&&$e->isMinVersion("11.7")&&preg_match('~MyISAM|InnoDB~i',$Q["Engine"]))$ag[]="VECTOR";$v=indexes($a);$m=fields($a);$_k=[];if(DIALECT=="mongo"){$_k=$v["_id_"];unset($ag[0]);unset($v["_id_"]);}$I=$_POST;if($I){$M=Admin::get()->getSettings();if($M->getParameter("indexOptions")!==null)$M->updateParameter("indexOptions",null);}if($_POST&&!$_POST["add"]&&!$_POST["drop_col"]){$b=[];foreach($I["indexes"]as$u){$B=$u["name"];if(in_array($u["type"],$ag)){$d=[];$mh=[];$ad=[];$kj=[];$Wf=$Xf?(in_array($u["algorithm"],$Xf)?$u["algorithm"]:first($Xf)):"";$Yf=(support("partial_indexes")?$u["partial"]:"");$hm=[];ksort($u["columns"]);foreach($u["columns"]as$w=>$c){if($c!=""){$x=isset($u["lengths"][$w])?$u["lengths"][$w]:null;$Yc=isset($u["descs"][$w])?$u["descs"][$w]:null;$jj=isset($u["opclasses"][$w])?$u["opclasses"][$w]:null;$hm[]=($m[$c]?idf_escape($c):$c).($x?"(".(+$x).")":"").($jj!=""?" ".idf_escape($jj):"").($Yc?" DESC":"");$d[]=$c;$mh[]=($x?:null);$ad[]=$Yc;$kj[]="$jj";}}$fe=$v[$B];if($fe){ksort($fe["columns"]);ksort($fe["lengths"]);ksort($fe["descs"]);if($u["type"]==$fe["type"]&&array_values($fe["columns"])===$d&&(!$fe["lengths"]||array_values($fe["lengths"])===$mh)&&array_values($fe["descs"])===$ad&&(!$fe["opclasses"]||array_values($fe["opclasses"])===$kj)&&(!$Xf||$fe["algorithm"]===$Wf)&&$fe["partial"]==$Yf){unset($v[$B]);continue;}}if($d)$b[]=[$u["type"],$B,$hm,$Wf,$Yf];}}foreach($v
as$B=>$fe)$b[]=[$fe["type"],$B,"DROP"];if(!$b)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),lang(228),alter_indexes($a,$b));}page_header(lang(182).": ".h($a),["table"=>$a,lang(182)]);$_e=array_keys($m);if($_POST["add"]){foreach($I["indexes"]as$w=>$u){if($u["columns"][count($u["columns"])]!="")$I["indexes"][$w]["columns"][]="";}$u=end($I["indexes"]);if($u["type"]||array_filter($u["columns"],'strlen'))$I["indexes"][]=["columns"=>[1=>""]];}if(!$I){foreach($v
as$w=>$u){$v[$w]["name"]=$w;$v[$w]["columns"][]="";}$v[]=["columns"=>[1=>""]];$I["indexes"]=$v;}$mh=(DIALECT=="sql"||DIALECT=="mssql");$kj=Driver::get()->getIndexOpclasses();if($_POST)$lm=$_POST["options"];else{$lm=false;foreach($v
as$u){if(array_filter(isset($u["lengths"])?$u["lengths"]:[])||array_filter(isset($u["descs"])?$u["descs"]:[])||array_filter(isset($u["opclasses"])?$u["opclasses"]:[])||(isset($u["partial"])?$u["partial"]:"")!=""){$lm=true;break;}}}echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th id='label-type'>",lang(229),"</th>";$pj="class='idxopts".($lm?"":" hidden")."'";if(count($Xf)>1)echo"<th id='label-method' $pj>",lang(230),doc_link(['sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'ha-and-performance/optimization-and-tuning/optimization-and-indexes/storage-engine-index-types','pgsql'=>'indexes-types.html',]),"</th>";echo"<th><input type='submit' hidden>",lang(53).($mh?"<span ".str_replace("class='","class='note ",$pj)."> (".lang(54).")</span>":"");if($mh||support("descidx"))echo
checkbox("options",1,$lm,lang(100),"indexOptionsShow(this.checked)","jsonly")."\n";echo"</th>","<th id='label-name'>",lang(231),"</th>";if(support("partial_indexes"))echo"<th id='label-condition' $pj>",lang(55),"</th>";echo"<th>","<button name='add[0]' value='1' title='",lang(102),"' class='button light hidden'>",icon_solo("add"),"</button>","</th>","</tr></thead>\n";if($_k){echo"<tr><td>PRIMARY<td>";foreach($_k["columns"]as$c)echo
select_input(" disabled",array_combine($_e,$_e),$c),"<label><input type='checkbox' disabled>".lang(63)."</label> ";echo"<td><td>\n";}$Ig=1;foreach($I["indexes"]as$u){if(!$_POST["drop_col"]||$Ig!=key($_POST["drop_col"])){echo"<tr><td>",html_select("indexes[$Ig][type]",[-1=>""]+$ag,$u["type"],($Ig==count($I["indexes"])?"indexesAddRow.call(this);":""),"label-type"),"</td>";if(count($Xf)>1)echo"<td $pj>",html_select("indexes[$Ig][algorithm]",array_merge([""],$Xf),$u['algorithm'],"label-method"),"</td>";echo"<td>";ksort($u["columns"]);$r=1;foreach($u["columns"]as$w=>$c){echo"<span>".select_input(" name='indexes[$Ig][columns][$r]' title='".lang(44)."'",($m&&($c==""||$m[$c])?array_combine($_e,$_e):[]),$c,"partial(indexesChangeColumn, '".js_escape(DIALECT=="sql"?"":$_GET["indexes"]."_")."')"),"<span $pj>";if($mh)echo"<input type='number' name='indexes[$Ig][lengths][$r]' class='input size' value='".(h(isset($u["lengths"][$w])?$u["lengths"][$w]:"")),"' title='".lang(99),"'>";if($kj){$jj=isset($u["opclasses"][$w])?$u["opclasses"][$w]:"";echo
html_select("indexes[$Ig][opclasses][$r]",[""=>"(".lang(232).")"]+array_combine($kj,$kj)+($jj!=""?[$jj=>$jj]:[]),$jj),doc_link(['pgsql'=>'indexes-opclass.html']);}if(support("descidx"))echo
checkbox("indexes[$Ig][descs][$r]",1,isset($u["descs"][$w])?$u["descs"][$w]:false,lang(63));echo"<br></span></span>";$r++;}echo"</td>","<td><input name='indexes[$Ig][name]' value='",h($u["name"]),"' class='input' autocapitalize='off' aria-labelledby='label-name'></td>\n";if(support("partial_indexes"))echo"<td $pj><input name='indexes[$Ig][partial]' value='".h($u["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>","<button name='drop_col[$Ig]' value='1' title='",lang(59),"' class='button light'>",icon_solo("remove"),"</button>",script("qsl('button').onclick = onRemoveIndexRowClick;"),"</td>\n";}$Ig++;}echo"</table>\n","</div>\n","<p>","<input type='submit' class='button default' value='",lang(118),"'>",input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["database"])){$I=$_POST;if($_POST&&!isset($_POST["add_x"])){$B=trim($I["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),lang(233),drop_databases([DB]));}elseif(DB!==$B){if(DB!=""){$_GET["db"]=$B;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($B),lang(234),rename_database($B,$I["collation"]));}else{$h=explode("\n",str_replace("\r","",$B));$Om=true;$dh="";foreach($h
as$i){if(count($h)==1||$i!=""){if(!create_database($i,$I["collation"]))$Om=false;$dh=$i;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($dh),lang(235),$Om);}}else{if(!$I["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($B).(preg_match('~^[a-z0-9_]+$~i',$I["collation"])?" COLLATE $I[collation]":""),substr(ME,0,-1),lang(236));}}if(DB!="")page_header(lang(70).": ".h(DB),[lang(70)]);else
page_header(lang(76),[lang(76)]);$B=DB;if($_POST)$B=$I["name"];elseif(DB!="")$I["collation"]=db_collation(DB,collations());elseif(DIALECT=="sql"){foreach(get_vals("SHOW GRANTS")as$kf){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$kf,$_)&&$_[1]){$B=stripcslashes(idf_unescape("`$_[2]`"));break;}}}$Rb=Admin::get()->getCollations($I["collation"]?[$I["collation"]]:[]);echo"<form action='' method='post'>\n","<p>";if($_POST["add_x"]||strpos($B,"\n"))echo"<textarea id='name' name='name' rows='10' cols='40'>",h($B),"</textarea><br>\n";else
echo"<input class='input' name='name' id='name' value='",h($B),"' data-maxlength='64' autocapitalize='off' autofocus>\n";if($Rb)echo
html_select("collation",[""=>"(".lang(94).")"]+$Rb,$I["collation"]),doc_link(['sql'=>"charset-charsets.html",'mariadb'=>"reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",]),"\n";echo"<input type='submit' class='button default' value='",lang(118),"'>\n";if(DB!="")echo"<input type='submit' class='button' name='drop' value='".lang(172)."'>".confirm(lang(225,DB))."\n";elseif(!$_POST["add_x"]&&$_GET["db"]=="")echo"<button name='add_x' value='1' title='",lang(102),"' class='button light'>",icon_solo("add"),"</button>\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["scheme"])){$I=$_POST;if($_POST){$z=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$z,lang(237));else{$B=trim($I["name"]);$z
.=urlencode($B);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($B),$z,lang(238));elseif($_GET["ns"]!=$B)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($B),$z,lang(239));else
redirect($z);}}if($_GET["ns"]!="")page_header(lang(71).": ".h($_GET["ns"]),[lang(71)]);else
page_header(lang(77),[lang(77)]);if(!$I)$I["name"]=$_GET["ns"];echo"<form action='' method='post'>\n","<p>","<input class='input' name='name' id='name' value='",h($I["name"]),"' autocapitalize='off' autofocus>","<input type='submit' class='button default' value='",lang(118),"'>";if($_GET["ns"]!="")echo"<input type='submit' class='button' name='drop' value='".lang(172)."'>".confirm(lang(225,$_GET["ns"]))."\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["call"])){$oa=$_GET["name"]?:$_GET["call"];page_header(lang(240).": ".h($oa),[lang(240)]);$rl=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$Tf=[];$Gj=[];foreach($rl["fields"]as$r=>$l){if(substr($l["inout"],-3)=="OUT"&&DIALECT=='sql')$Gj[$r]="@".idf_escape($l["field"])." AS ".idf_escape($l["field"]);if(!$l["inout"]||substr($l["inout"],0,2)=="IN")$Tf[]=$r;}if($_POST){$wb=[];foreach($rl["fields"]as$w=>$l){$W="";if(in_array($w,$Tf)){$W=process_input($l);if($W===false)$W="''";if(isset($Gj[$w]))Connection::get()->query("SET @".idf_escape($l["field"])." = $W");}if(isset($Gj[$w]))$wb[]="@".idf_escape($l["field"]);elseif(in_array($w,$Tf))$wb[]=$W;}$F=(isset($_GET["callf"])?"SELECT ":"CALL ").($rl["returns"]&&$rl["returns"]["type"]=="record"?"* FROM ":"").table($oa)."(".implode(", ",$wb).")";$Em=microtime(true);$G=Connection::get()->multiQuery($F);$Da=Connection::get()->getAffectedRows();echo
Admin::get()->formatSelectQuery($F,$Em,!$G);if(!$G)echo"<p class='error'>".error()."\n";else{$kc=connect();if($kc)$kc->selectDatabase(DB);$Gi=false;do{$G=Connection::get()->storeResult();if($Gi&&Connection::get()->getError())echo"<p class='error'>".error()."\n";elseif(is_object($G))print_select_result($G,$kc);else
echo"<p class='message'>".lang(241,$Da)." <span class='time'>".@date("H:i:s")."</span>\n";}while($Gi=Connection::get()->nextResult());if($Gj)print_select_result(Connection::get()->query("SELECT ".implode(", ",$Gj)));}}echo"<form action='' method='post'>\n";if($Tf){echo"<table class='box'>\n";foreach($Tf
as$w){$l=$rl["fields"][$w];$B=$l["field"];echo"<tr><th>".Admin::get()->getFieldName($l);$X=isset($_POST["fields"][$B])?$_POST["fields"][$B]:"";if($X!=""){if($l["type"]=="set")$X=implode(",",$X);}input($l,$X,(string)(isset($_POST["function"][$B])?$_POST["function"][$B]:""));echo"\n";}echo"</table>\n";}echo"<p>\n","<input type='submit' class='button' value='",lang(240),"'>\n",input_token(),"</p>\n","</form>\n";$ac=$rl["comment"];if($ac!==null&&$ac!==""){$ac=h(trim($rl["comment"],"\n"));if(preg_match('~^ +~',$ac,$A)){preg_match_all("~^($A[0]|$)~m",$ac,$rh);if(count($rh[0])==substr_count($ac,"\n"))$ac=preg_replace("~^($A[0])~m","",$ac);}$ac=preg_replace('~(^|[^\n]\n)(Description|Parameters|Example)\n~',"$1\n<strong>$2</strong>\n",$ac);echo"<pre class='comment'>$ac</pre>\n";}}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$B=$_GET["name"];$I=$_POST;if($_POST&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$I["source"]=array_filter($I["source"],'strlen');ksort($I["source"]);$rn=[];foreach($I["source"]as$w=>$W)$rn[$w]=$I["target"][$w];$I["target"]=$rn;}if(DIALECT=="sqlite")$G=recreate_table($a,$a,[],[],[" $B"=>($I["drop"]?"":" ".format_foreign_key($I))]);else{$b="ALTER TABLE ".table($a);$G=($B==""||queries("$b DROP ".(DIALECT=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($B)));if(!$I["drop"])$G=queries("$b ADD".format_foreign_key($I));}queries_redirect(ME."table=".urlencode($a),($I["drop"]?lang(242):($B!=""?lang(243):lang(244))),(bool)$G);if(!$I["drop"])Admin::get()->addError(lang(245));}if($B!="")page_header(lang(246).": ".h($B),["table"=>$a,lang(246)]);else
page_header(lang(186).": ".h($a),["table"=>$a,lang(186)]);if($_POST){ksort($I["source"]);if($_POST["change"]||$_POST["change-js"])$I["target"]=[];else$I["source"][]="";}elseif($B!=""){$Re=foreign_keys($a);$I=$Re[$B];$I["source"][]="";}else{$I["table"]=$a;$I["source"]=[""];}echo"<form action='' method='post'>\n";$tm=array_keys(fields($a));if($I["db"]!="")Connection::get()->selectDatabase($I["db"]);if($I["ns"]!=""){$Bj=get_schema();set_schema($I["ns"]);}$Xk=array_keys(array_filter(table_status('',true),'AdminNeo\fk_support'));$rn=$Xk?array_keys(fields(in_array($I["table"],$Xk)?$I["table"]:reset($Xk))):[];$fj="this.form['change-js'].value = '1'; this.form.submit();";echo"<p>","<span id='label-table'>",lang(247),":</span> ",html_select("table",$Xk,$I["table"],$fj,"label-table");if(support("scheme")){$Fl=array_filter(Admin::get()->getSchemas(),function($El){return!information_schema(DB,$El);});echo"<span id='label-schema'>",lang(82),":</span> ",html_select("ns",$Fl,$I["ns"]!=""?$I["ns"]:$_GET["ns"],$fj,"label-schema");if($I["ns"]!="")set_schema($Bj);}elseif(DIALECT!="sqlite"){$Nc=[];foreach(Admin::get()->getDatabases()as$i){if(!information_schema($i))$Nc[]=$i;}echo"<span id='label-db'>",lang(248),":</span> ",html_select("db",$Nc,$I["db"]!=""?$I["db"]:$_GET["db"],$fj,"label-db");}echo
input_hidden("change-js"),"<noscript><input type='submit' class='button' name='change' value='",lang(249),"'></noscript>","</p>\n","<table>","<thead><tr><th id='label-source'>",lang(183),"<th id='label-target'>",lang(184),"</thead>\n";$Ig=0;foreach($I["source"]as$w=>$W){echo"<tr>","<td>".html_select("source[".(+$w)."]",[-1=>""]+$tm,$W,($Ig==count($I["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$w)."]",$rn,isset($I["target"][$w])?$I["target"][$w]:null,"","label-target");$Ig++;}echo"</table>\n","<noscript><p><input type='submit' class='button' name='add' value='",lang(250),"'></p></noscript>","<p>\n","<span id='label-delete'>".lang(96),":</span> ",html_select("on_delete",[-1=>""]+Driver::get()->getOnActions(),$I["on_delete"],"","label-delete"),"<span id='label-update'>".lang(95),":</span> ",html_select("on_update",[-1=>""]+Driver::get()->getOnActions(),$I["on_update"],"","label-update");if(DRIVER=='pgsql')echo
html_select("deferrable",['NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'],$I["deferrable"]);echo
doc_link(['sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"architecture/server-constraints/foreign-key-constraints",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-PARMS-REFERENCES",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"SQLRF01111",]),"</p>\n<p>","<input type='submit' class='button default' value='",lang(118),"'>";if($B!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$B));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["view"])){$a=$_GET["view"];$I=$_POST;$Cj="VIEW";if(DIALECT=="pgsql"&&$a!=""){$N=table_status1($a);$Cj=strtoupper($N["Engine"]);}if($_POST){$B=trim($I["name"]);$Sa=" AS\n$I[select]";$Bh=ME."table=".urlencode($B);$Wh=lang(251);$T=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$B&&DIALECT!="sqlite"&&$T=="VIEW"&&$Cj=="VIEW")query_redirect((DIALECT=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($B).$Sa,$Bh,$Wh);else{$tn="adminneo_".uniqid();drop_create("DROP $Cj ".table($a),"CREATE $T ".table($B).$Sa,"DROP $T ".table($B),"CREATE $T ".table($tn).$Sa,"DROP $T ".table($tn),($_POST["drop"]?substr(ME,0,-1):$Bh),lang(252),$Wh,lang(253),$a,$B);}}if(!$_POST&&$a!=""){$I=view($a);$I["name"]=$a;$I["materialized"]=($Cj!="VIEW");if($k=error())Admin::get()->addError($k);}if($a!="")page_header(lang(37).": ".h($a),["table"=>$a,lang(37)]);else
page_header(lang(254),[lang(254)]);echo"<form action='' method='post'>\n","<p>",lang(231),":","<input class='input' name='name' value='",h($I["name"]),"' data-maxlength='64' autocapitalize='off'>\n";if(support("materializedview"))echo
checkbox("materialized",1,$I["materialized"],lang(174));echo"</p>\n<p>";textarea("select",$I["select"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(118),"'>\n";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>\n",confirm(lang(225,$a));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["event"])){$ea=$_GET["event"];$rg=["YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND"];$Gm=["ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE"];$I=$_POST;if($_POST){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($ea),substr(ME,0,-1),lang(255));elseif(in_array($I["INTERVAL_FIELD"],$rg)&&isset($Gm[$I["STATUS"]])){$Dl="\nON SCHEDULE ".($I["INTERVAL_VALUE"]?"EVERY ".q($I["INTERVAL_VALUE"])." $I[INTERVAL_FIELD]".($I["STARTS"]?" STARTS ".q($I["STARTS"]):"").($I["ENDS"]?" ENDS ".q($I["ENDS"]):""):"AT ".q($I["STARTS"]))." ON COMPLETION".($I["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($ea!=""?lang(256):lang(257)),(bool)queries(($ea!=""?"ALTER EVENT ".idf_escape($ea).$Dl.($ea!=$I["EVENT_NAME"]?"\nRENAME TO ".idf_escape($I["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($I["EVENT_NAME"]).$Dl)."\n".$Gm[$I["STATUS"]]." COMMENT ".q($I["EVENT_COMMENT"]).rtrim(" DO\n$I[EVENT_DEFINITION]",";").";"));}}if($ea!="")page_header(lang(258).": ".h($ea),[lang(258)]);else
page_header(lang(259),[lang(259)]);if(!$I&&$ea!=""){$J=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($ea));$I=reset($J);}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(231),"</th><td>","<input class='input' name='EVENT_NAME' value='",h($I["EVENT_NAME"]),"' data-maxlength='64' autocapitalize='off'>","</td></tr>\n","<tr><th title='datetime'>",lang(260),"</th><td>","<input class='input' name='STARTS' value='",h("$I[EXECUTE_AT]$I[STARTS]"),"'>","</td></tr>\n","<tr><th title='datetime'>",lang(261),"</th><td>","<input class='input' name='ENDS' value='",h($I["ENDS"]),"'>","</td></tr>\n","<tr><th>",lang(262),"</th><td>","<input type='number' name='INTERVAL_VALUE' value='",h($I["INTERVAL_VALUE"]),"' class='input size'> ",html_select("INTERVAL_FIELD",$rg,$I["INTERVAL_FIELD"]),"</td></tr>\n","<tr><th>",lang(164),"</th><td>",html_select("STATUS",$Gm,$I["STATUS"]),"</td></tr>\n","<tr><th>",lang(48),"</th><td>","<input class='input' name='EVENT_COMMENT' value='",h($I["EVENT_COMMENT"]),"' data-maxlength='64'>","</td></tr>\n","<tr><th></th><td>",checkbox("ON_COMPLETION","PRESERVE",$I["ON_COMPLETION"]=="PRESERVE",lang(263)),"</td></tr>\n","</table>\n","<p>";textarea("EVENT_DEFINITION",$I["EVENT_DEFINITION"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(118),"'>";if($ea!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$ea));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["procedure"])){$oa=($_GET["name"]?:$_GET["procedure"]);$rl=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$I=$_POST;$I["fields"]=(array)$I["fields"];if($_POST&&!process_fields($I["fields"])){foreach($I["fields"]as$w=>$l){if($l["field"]=="")unset($I["fields"][$w]);}$Zi=routine_id($oa,routine($_GET["procedure"],$rl));$Bi=routine_id($I["name"],$I);$f=create_routine($rl,$I);$Bh=substr(ME,0,-1);$Wh=lang(264);if(!$_POST["drop"]&&$Zi==$Bi&&(DIALECT!="sql"||Connection::get()->isMariaDB()))query_redirect(substr_replace($f,' OR REPLACE',6,0),$Bh,$Wh);else{$tn="adminneo_".uniqid();drop_create("DROP $rl $Zi",$f,"DROP $rl $Bi",create_routine($rl,["name"=>$tn]+$I),"DROP $rl ".routine_id($tn,$I),$Bh,lang(265),$Wh,lang(266),$oa,$I["name"]);}}if($oa!=""){$S=isset($_GET["function"])?lang(267):lang(268);page_header($S.": ".h($oa),[$S]);}else{$S=isset($_GET["function"])?lang(269):lang(270);page_header($S,[$S]);}if(!$_POST){if($oa=="")$I["language"]="sql";else{$I=routine($_GET["procedure"],$rl);$I["name"]=$oa;}}$Bb=get_vals("SHOW CHARACTER SET");sort($Bb);$sl=routine_languages();echo"<form action='' method='post' id='form'>\n","<p>",lang(231),": ","<input class='input' name='name' value='",h($I["name"]),"' data-maxlength='64' autocapitalize='off'>";if($sl)echo"<span id='label-language'>",lang(10),":</span> ",html_select("language",$sl,$I["language"],"","label-language");echo"<input type='submit' class='button default' value='",lang(118),"'>","</p>\n","<div class='scrollable'>\n","<table class='nowrap' id='edit-fields'>\n";edit_fields($I["fields"],$Bb,$rl);if(isset($_GET["function"])){echo"<tbody><tr>";if(support("move_col"))echo"<th></th>";echo"<th>",lang(271),"</th>";edit_type("returns",(array)$I["returns"],$Bb,[],(DIALECT=="pgsql"?["void","trigger"]:[]));echo"<td></td>","</tr></tbody>\n";}echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>";textarea("definition",$I["definition"],20);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(118),"'>";if($oa!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$oa));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["sequence"])){$qa=$_GET["sequence"];$I=$_POST;if($_POST){$z=substr(ME,0,-1);$B=trim($I["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($qa),$z,lang(272));elseif($qa=="")query_redirect("CREATE SEQUENCE ".idf_escape($B),$z,lang(273));elseif($qa!=$B)query_redirect("ALTER SEQUENCE ".idf_escape($qa)." RENAME TO ".idf_escape($B),$z,lang(274));else
redirect($z);}if($qa!="")page_header(lang(275).": ".h($qa),[h($qa)]);else
page_header(lang(276),[lang(277)]);if(!$I)$I["name"]=$qa;echo"<form action='' method='post'>\n","<p>","<input class='input' name='name' value='",h($I["name"]),"' autocapitalize='off'>","<input type='submit' class='button default' value='",lang(118),"'>";if($qa!="")echo"<input type='submit' class='button' name='drop' value='".lang(172)."'>".confirm(lang(225,$qa))."\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["type"])){function
enum_values($Tc){$X="'(?:[^']|'')*'";if(!preg_match('~^AS\s+ENUM\s*\(\s*('.$X.'(?:\s*,\s*'.$X.')*)\s*\)$~i',$Tc,$_))return
null;preg_match_all('~'.$X.'~',$_[1],$A);return$A[0];}function
add_enum_values($T,$Xi,$zi){$cj=enum_values($Xi);$Fi=enum_values($zi);if($cj===null||$Fi===null)return
null;$H=[];$r=0;foreach($Fi
as$X){if($X===(isset($cj[$r])?$cj[$r]:null))$r++;else$H[]="ALTER TYPE ".idf_escape($T)." ADD VALUE $X".($r<count($cj)?" BEFORE ".$cj[$r]:"");}return$r==count($cj)?$H:null;}$ra=$_GET["type"];$I=$_POST;$T=($ra!=""?type_definition((int)Driver::get()->getUserTypeId($ra)):["kind"=>"","definition"=>""]);$Qi=($T["kind"]=='d'?"DOMAIN":"TYPE");if($_POST){$z=substr(ME,0,-1);$B=trim($I["name"]);$Sa=trim($I["as"]);$Di=(preg_match('~^AS\s+(?!ENUM\b|RANGE\b|\()~i',$Sa)?"DOMAIN":"TYPE");$Wh=lang(278);$b=(!$_POST["drop"]&&$ra!=""&&$Di==$Qi?($Sa==$T["definition"]?[]:add_enum_values($ra,$T["definition"],$Sa)):null);if($b!==null){if($ra!=$B)$b[]="ALTER $Qi ".idf_escape($ra)." RENAME TO ".idf_escape($B);if(!$b)redirect($z);$qe=false;foreach($b
as$F){if(!queries($F)){$qe=true;break;}}queries_redirect($z,$Wh,!$qe);}else
drop_create("DROP $Qi ".idf_escape($ra),"CREATE $Di ".idf_escape($B)." $Sa","","","",$z,lang(279),$Wh,lang(280),$ra,$B);}if($ra!="")page_header(lang(281).": ".h($ra),[h($ra)]);else
page_header(lang(277),[lang(277)]);if(!$I){$I["name"]=$ra;$I["as"]=($ra!=""?$T["definition"]:"AS ");}echo"<form action='' method='post'>\n","<p>",lang(231).": <input class='input' name='name' value='".h($I['name'])."' autocapitalize='off'>\n",doc_link(['pgsql'=>"sql-createtype.html",],"?"),"</p>\n<p>";textarea("as",$I["as"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(118),"'>";if($ra!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$ra));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["check"])){$a=$_GET["check"];$B=$_GET["name"];$I=$_POST;if($I){if(DIALECT=="sqlite")$Om=recreate_table($a,$a,[],[],[],"",[],"$B",($I["drop"]?"":$I["clause"]));else{$Om=($B==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($B)));if(!$I["drop"])$Om=(bool)queries("ALTER TABLE ".table($a)." ADD".($I["name"]!=""?" CONSTRAINT ".idf_escape($I["name"]):"")." CHECK ($I[clause])");}queries_redirect(ME."table=".urlencode($a),($I["drop"]?lang(282):($B!=""?lang(283):lang(284))),$Om);}if($B!="")page_header(lang(285).": ".h($B),["table"=>$a,lang(285)]);else
page_header(lang(188).": ".h($a),["table"=>$a,lang(188)]);if(!$I){$Hb=Driver::get()->checkConstraints($a);$I=["name"=>$B,"clause"=>$Hb[$B]];}echo"<form action='' method='post'>\n","<p>";if(DIALECT!="sqlite")echo
lang(231).': <input name="name" value="'.h($I["name"]).'" class="input" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(['sql'=>"create-table-check-constraints.html",'mariadb'=>"reference/sql-statements/data-definition/constraint",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",],"?"),"</p>\n<p>";textarea("clause",$I["clause"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(118),"'>";if($B!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$B));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$B=isset($_GET["name"])?$_GET["name"]:"";$Un=trigger_options();$I=trigger($B,$a)+["Trigger"=>$a."_bi"];if($_POST){if(in_array($_POST["Timing"],$Un["Timing"])&&in_array($_POST["Event"],$Un["Event"])&&in_array($_POST["Type"],$Un["Type"])){$dj=" ON ".table($a);$ud="DROP TRIGGER ".idf_escape($B).(DIALECT=="pgsql"?$dj:"");$Bh=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($ud,$Bh,lang(286));else{if($B!="")queries($ud);queries_redirect($Bh,($B!=""?lang(287):lang(288)),(bool)queries(create_trigger($dj,$_POST)));if($B!="")queries(create_trigger($dj,$I+["Type"=>reset($Un["Type"])]));}}$I=$_POST;}if($B!="")page_header(lang(289).": ".h($B),["table"=>$a,lang(289)]);else
page_header(lang(190).": ".h($a),["table"=>$a,lang(190)]);echo"<form action='' method='post' id='form'>\n","<table class='box box-light'>\n","<tr><th id='label-time'>",lang(290),"</th><td>",html_select("Timing",$Un["Timing"],$I["Timing"],"triggerChange(/^".js_escape_re($a)."_[ba][iud]$/, '".js_escape($a)."', this.form);","label-time"),"</td></tr>\n","<tr><th id='label-event'>",lang(291),"</th><td>",html_select("Event",$Un["Event"],$I["Event"],"this.form['Timing'].onchange();","label-event");if(in_array("UPDATE OF",$Un["Event"]))echo" <input name='Of' value='".h($I["Of"])."' class='input hidden'>";echo"</td></tr>\n","<tr><th id='label-type'>",lang(45),"</th><td>",html_select("Type",$Un["Type"],$I["Type"],"","label-type"),"</td></tr>\n","</table>\n","<p>",lang(231),"<input class='input' name='Trigger' value='",h($I["Trigger"]),"' data-maxlength='64' autocapitalize='off'>","</p>\n",script("gid('form')['Timing'].onchange();"),"<p>";textarea("Statement",$I["Statement"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(118),"'>";if($B!="")echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>",confirm(lang(225,$B));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["user"])){$sa=$_GET["user"];$Ek=[""=>["All privileges"=>""]];foreach(get_rows("SHOW PRIVILEGES")as$I){foreach(explode(",",($I["Privilege"]=="Grant option"?"":$I["Context"]))as$qc)$Ek[$qc=="File access on server"?"Server Admin":$qc][$I["Privilege"]]=$I["Comment"];}unset($Ek["Server Admin"]["Usage"]);foreach($Ek["Tables"]as$w=>$W)unset($Ek["Databases"][$w]);$Ai=[];if($_POST){foreach($_POST["objects"]as$w=>$W)$Ai[$W]=(array)$Ai[$W]+(array)$_POST["grants"][$w];}$mf=[];if(isset($_GET["host"])&&($G=Connection::get()->query("SHOW GRANTS FOR ".q($sa)."@".q($_GET["host"])))){while($I=$G->fetchRow()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$I[0],$_)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$_[1],$A,PREG_SET_ORDER)){foreach($A
as$W){if($W[1]!="USAGE")$mf["$_[2]$W[2]"][$W[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$I[0]))$mf["$_[2]$W[2]"]["GRANT OPTION"]=true;}}}}$jk=!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8");if($_POST){$bj=(isset($_GET["host"])?q($sa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $bj",ME."privileges=",lang(292));else{$Ei=q($_POST["user"])."@".q($_POST["host"]);$bk=$_POST["pass"];$_c=false;$G=true;if($bj!=$Ei){$_c=(bool)queries("CREATE USER $Ei IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($bk));$G=$_c;}elseif($bk!="")$G=(bool)queries("SET PASSWORD FOR $Ei = ".($jk||$_POST["hashed"]?q($bk):"PASSWORD(".q($bk).")"));if($G){$ol=[];foreach($Ai
as$Qi=>$kf){if(isset($_GET["grant"]))$kf=array_filter($kf);$kf=array_keys($kf);if(isset($_GET["grant"]))$ol=array_diff(array_keys(array_filter($Ai[$Qi],'strlen')),$kf);elseif($bj==$Ei){$Yi=array_keys((array)$mf[$Qi]);$ol=array_diff($Yi,$kf);$kf=array_diff($kf,$Yi);unset($mf[$Qi]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$Qi,$_)&&(!grant(false,$ol,$_[2],$_[1],$Ei)||!grant(true,$kf,$_[2],$_[1],$Ei))){$G=false;break;}}}if($G&&isset($_GET["host"])){if($bj!=$Ei)queries("DROP USER $bj");elseif(!isset($_GET["grant"])){foreach($mf
as$Qi=>$ol){if(preg_match('~^(.+)(\(.*\))?$~U',$Qi,$_))grant(false,array_keys($ol),$_[2],$_[1],$Ei);}}}if($G&&!Queries::$queries)redirect(ME."privileges=");queries_redirect(ME."privileges=",(isset($_GET["host"])?lang(293):lang(294)),$G);if($_c)Connection::get()->query("DROP USER $Ei");}}$S=isset($_GET["host"])?lang(6).": ".h("$sa@$_GET[host]"):lang(197);$En=isset($_GET["host"])?h($sa):lang(197);page_header($S,["privileges"=>['',lang(73)],$En]);if($_POST){$I=$_POST;$mf=$Ai;}else{$I=$_GET+["host"=>Connection::get()->getValue("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")];if($mf)$mf[".*"]=[];elseif(DB!="")$mf[idf_escape(addcslashes(DB,"%_\\")).".*"]=[];else$mf["*.* "]=[];}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(5),"</th>","<td><input class='input' name='host' data-maxlength='60' value='",h($I["host"]),"' autocapitalize='off'></td>\n","<tr><th>",lang(6),"</th>","<td><input class='input' name='user' data-maxlength='80' value='",h($I["user"]),"' autocapitalize='off'></td>\n",'<tr><th>',lang(30),"</th>","<td><input class='input' name='pass' id='pass' value='",h($I["pass"]),"' autocomplete='new-password'>";if(!$jk)echo
checkbox("hashed",1,$I["hashed"],lang(295),"typePassword(this.form['pass'], this.checked);");echo"</td>\n";if(!$I["hashed"])echo
script("typePassword(gid('pass'));");echo"</table>\n","<div class='scrollable'><table class='checkable'>\n","<thead><tr><th colspan='2'>".lang(73).doc_link(['sql'=>"grant.html#priv_level","mariadb"=>"reference/sql-statements/account-management-sql-statements/grant#privilege-levels"])."</th>";$r=0;foreach($mf
as$Qi=>$kf){echo"<th>";if($Qi=="*.*")echo"*.*",input_hidden("objects[$r]","*.*");else
echo"<input class='input' name='objects[$r]' value='".h(trim($Qi))."' size='10' autocapitalize='off'>";echo"</th>";$r++;}echo"</tr></thead>\n";foreach([""=>"","Server Admin"=>lang(5),"Databases"=>lang(31),"Tables"=>lang(9),"Procedures"=>lang(296),]as$qc=>$Yc){foreach((array)$Ek[$qc]as$Dk=>$ac){echo"<tr>";if($Yc)echo"<td>$Yc</td>";echo"<td".(!$Yc?" colspan='2'":"").' lang="en" title="'.h($ac).'">'.h($Dk)."</td>";$r=0;foreach($mf
as$Qi=>$kf){$B="'grants[$r][".h(strtoupper($Dk))."]'";$X=$kf[strtoupper($Dk)];$Ik=strpos($Qi,"@")!==false;$_i=$Qi==".*";$Ia=$Dk=="All privileges";$lf=$Dk=="Grant option";if($Qi=="*.*"&&$Dk=="Proxy")echo"<td></td>";elseif($Ik&&$Dk!="Proxy"&&!$lf)echo"<td></td>";elseif($qc=="Server Admin"&&$Qi!=(isset($mf["*.*"])?"*.*":".*")&&!(($Ik||$_i)&&$Dk=="Proxy"))echo"<td></td>";elseif(isset($_GET["grant"]))echo"<td><select name=$B>"."<option></option>"."<option value='1'".($X?" selected":"").">".lang(297)."</option>"."<option value='0'".($X=="0"?" selected":"").">".lang(298)."</option>"."</select></td>";else{echo"<td class='center'><label class='block'>","<input type='checkbox' name=$B value='1'".($X?" checked":"").($Ia?" id='grants-$r-all'":(!$lf?" class='grants-$r'":"")).">";if($Ia)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheckAll('.grants-$r'); };");elseif(!$lf)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$r-all'); };");echo"</label>";}$r++;}echo"</tr>";}}echo"</table></div>\n","<p>","<input type='submit' class='button default' value='",lang(118),"'>\n";if(isset($_GET["host"]))echo"<input type='submit' class='button' name='drop' value='",lang(172),"'>\n",confirm(lang(225,"$sa@$_GET[host]"));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST){$Ug=0;foreach((array)$_POST["kill"]as$W){if(kill_process($W))$Ug++;}queries_redirect(ME."processlist=",lang(299,$Ug),$Ug||!$_POST["kill"]);}}page_header(lang(162),[lang(162)]);echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap checkable'>\n";$r=-1;foreach(process_list()as$r=>$I){if(!$r){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($I
as$w=>$W)echo"<th>$w".doc_link(['sql'=>"show-processlist.html#processlist_".strtolower($w),'mariadb'=>"reference/sql-statements/administrative-sql-statements/show/show-processlist",'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"REFRN30223",]);echo"</thead>\n","<tbody>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$I[DIALECT=="sql"?"Id":"pid"],0):"");foreach($I
as$w=>$W)echo"<td>".($W!=""&&((DIALECT=="sql"&&$w=="Info"&&preg_match("~Query|Killed~",$I["Command"]))||(DIALECT=="pgsql"&&$w=="query")||(DIALECT=="oracle"&&$w=="sql_text"))?"<code class='jush-".DIALECT."'>".truncate_utf8($W,100).'</code> <a href="'.h(ME.($I["db"]!=""?"db=".urlencode($I["db"])."&":"")."sql=".urlencode($W)).'">'.icon("edit").lang(300).'</a>':h($W));echo"\n";}if($r>=0)echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: event => tableClick(event, true)});");echo"</table>\n","</div>\n","<p>";if(support("kill"))echo($r+1)."/".lang(301,max_connections()),"<p><input type='submit' class='button' value='".lang(302)."'>\n";echo
input_token(),"</p>\n","</form>\n",script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$Q=table_status1($a);$v=indexes($a);$m=fields($a);$Re=column_foreign_keys($a);$Ti=$Q["Oid"];$pl=[];$d=[];$Il=[];$tj=[];$yn=null;foreach($m
as$w=>$l){$B=Admin::get()->getFieldName($l);$ui=html_entity_decode(strip_tags($B),ENT_QUOTES);if(isset($l["privileges"]["select"])&&$B!=""){$d[$w]=$ui;if(is_shortable($l))$yn=Admin::get()->processSelectionLength();}if(isset($l["privileges"]["where"])&&$B!="")$Il[$w]=$ui;if(isset($l["privileges"]["order"])&&$B!="")$tj[$w]=$ui;$pl+=$l["privileges"];}list($K,$nf)=Admin::get()->processSelectionColumns($d,$v);$K=array_unique($K);$nf=array_unique($nf);$Bg=count($nf)<count($K);$Z=Admin::get()->processSelectionSearch($m,$v);$rj=Admin::get()->processSelectionOrder($m,$v);$y=Admin::get()->processSelectionLimit();if($_GET["modify"]&&!Admin::get()->isDataEditAllowed())redirect(ME."select=".urlencode($a));if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$go=>$I){$Sa=convert_field($m[key($I)]);$K=[$Sa?:idf_escape(key($I))];$Z[]=where_check($go,$m);$H=Driver::get()->select($a,$K,$Z,$K);if(is_object($H))echo
first($H->fetchRow());}exit;}$_k=$jo=[];foreach($v
as$u){if($u["type"]=="PRIMARY"){$_k=array_flip($u["columns"]);$jo=($K?$_k:[]);foreach($jo
as$w=>$W){if(in_array(idf_escape($w),$K))unset($jo[$w]);}break;}}if($Ti&&!$_k){$_k=$jo=[$Ti=>0];$v[]=["type"=>"PRIMARY","columns"=>[$Ti]];}$M=Admin::get()->getSettings();if($_POST){$Vo=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$Hb=[];foreach($_POST["check"]as$Cb)$Hb[]=where_check($Cb,$m);$Vo[]="((".implode(") OR (",$Hb)."))";}$Vo=($Vo?"\nWHERE ".implode(" AND ",$Vo):"");if($_POST["export"]){$M->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers($a);Admin::get()->dumpTable($a,"");$bf=($K?implode(", ",$K):"*").convert_fields($d,$m,$K)."\nFROM ".table($a);$qf=($nf&&$Bg?"\nGROUP BY ".implode(", ",$nf):"").($rj?"\nORDER BY ".implode(", ",$rj):"");if(!is_array($_POST["check"])||$_k)$F="SELECT $bf$Vo$qf";else{$do=[];foreach($_POST["check"]as$W)$do[]="(SELECT".limit($bf,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$m).$qf,1).")";$F=implode(" UNION ALL ",$do);}Admin::get()->dumpData($a,"table",$F);exit;}if($_POST["save"]||$_POST["delete"]){$G=true;$Da=0;$hm=[];if(!$_POST["delete"]){$Rl=array_keys($_POST["fields"]+$_POST["function"]);foreach($Rl
as$B){$W=process_input($m[$B]);if($W!==null&&($_POST["clone"]||$W!==false))$hm[idf_escape($B)]=($W!==false?$W:idf_escape($B));}}if($_POST["delete"]||$hm){if($_POST["clone"])$F="INTO ".table($a)." (".implode(", ",array_keys($hm)).")\nSELECT ".implode(", ",$hm)."\nFROM ".table($a);if($_POST["all"]||($_k&&is_array($_POST["check"]))||$Bg){$G=($_POST["delete"]?Driver::get()->delete($a,$Vo):($_POST["clone"]?queries("INSERT $F$Vo".Driver::get()->getInsertReturningSql($a)):Driver::get()->update($a,$hm,$Vo)));$Da=Connection::get()->getAffectedRows();if(is_object($G))$Da+=$G->getRowsCount();}else{foreach((array)$_POST["check"]as$W){$Uo="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$m);$G=($_POST["delete"]?Driver::get()->delete($a,$Uo,1):($_POST["clone"]?queries("INSERT".limit1($a,$F,$Uo)):Driver::get()->update($a,$hm,$Uo,1)));if(!$G)break;$Da+=Connection::get()->getAffectedRows();}}}$Wh=lang(303,$Da);if($_POST["clone"]&&$G&&$Da==1){$eh=last_id($G);if($eh)$Wh=lang(219," $eh");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$Wh,(bool)$G);if(!$_POST["delete"]){$Hd=array_filter($m,function($l){return!(isset($l["generated"])?$l["generated"]:null);});edit_form($a,$Hd,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])Admin::get()->addError(lang(304));else{$Om=true;$Da=0;foreach($_POST["val"]as$go=>$I){$hm=[];foreach($I
as$w=>$W){$w=bracket_escape($w,true);if(isset($m[$w]["full_type"]))Admin::get()->detectJson($m[$w]["full_type"],$W,false);$hm[idf_escape($w)]=(preg_match('~char|text~',$m[$w]["type"])||$W!=""?Admin::get()->processFieldInput($m[$w],$W):"NULL");}$Om=(bool)Driver::get()->update($a,$hm," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($go,$m),($Bg||$_k?0:1)," ");if(!$Om)break;$Da+=Connection::get()->getAffectedRows();}queries_redirect(remove_from_uri(),lang(303,$Da),$Om);}}elseif(!is_string($n=get_file("csv_file",true)))Admin::get()->addError(upload_error($n));elseif(!preg_match('~~u',$n))Admin::get()->addError(lang(305));else{$M->updateParameter("exportFormat",$_POST["import_format"]);$Vb=array_keys($m);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$n,$A);$Da=count($A[0]);Driver::get()->begin();$Sl=($_POST["import_format"]=="csv;"?";":($_POST["import_format"]=="tsv"?"\t":","));$J=[];foreach($A[0]as$w=>$W){preg_match_all("~((?>\"[^\"]*\")+|[^$Sl]*)$Sl~",$W.$Sl,$Hh);if(!$w&&!array_diff($Hh[1],$Vb)){$Vb=$Hh[1];$Da--;}else{$hm=[];foreach($Hh[1]as$r=>$Ob)$hm[idf_escape($Vb[$r])]=($Ob==""&&$m[$Vb[$r]]["null"]?"NULL":q(preg_match('~^".*"$~s',$Ob)?str_replace('""','"',substr($Ob,1,-1)):$Ob));$J[]=$hm;}}$Om=!$J||Driver::get()->insertUpdate($a,$J,$_k);if($Om)Driver::get()->commit();queries_redirect(remove_from_uri("page"),lang(306,$Da),$Om);Driver::get()->rollback();}}$en=Admin::get()->getTableName($Q);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(56).": $en",[$en]);$mg=null;if(isset($pl["insert"])||!support("table")){$mg=[];foreach((array)$_GET["where"]as$W){if(isset($Re[$W["col"]])&&count($Re[$W["col"]])==1&&($W["op"]=="="||(!$W["op"]&&(is_array($W["val"])||!preg_match('~[_%]~',$W["val"])))))$mg["preset"."[".bracket_escape($W["col"])."]"]=$W["val"];}}Admin::get()->printTableMenu($Q,$mg);if(!$d&&support("table"))echo"<p class='error'>".lang(307).($m||error()==""?".":": ".error())."\n";else{echo"<form id='form' action=''>\n","<div hidden>";hidden_fields_get();if(DB!=""){echo
input_hidden("db",DB);if(isset($_GET["ns"]))echo
input_hidden("ns",$_GET["ns"]);}echo
input_hidden("select",$a),"<input type='submit' class='button' value='".lang(56)."'>","</div>\n","<div class='field-sets'>\n";Admin::get()->printSelectionColumns($K,$d);Admin::get()->printSelectionSearch($Z,$Il,$v);Admin::get()->printSelectionOrder($rj,$tj,$v);Admin::get()->printSelectionLimit($y);Admin::get()->printSelectionLength($yn);Admin::get()->printSelectionAction($v);echo"</div>\n</form>\n";foreach((array)$_GET["where"]as$W){if($W["op"]=="SQL"&&!in_array(isset($_SERVER["HTTP_SEC_FETCH_SITE"])?$_SERVER["HTTP_SEC_FETCH_SITE"]:"",["","same-origin"])){echo"<p class='error'>".lang(156)." ".lang(157)."\n";page_footer();exit;}}$D=isset($_GET["page"])?$_GET["page"]:null;if($D=="last"){$Ye=Connection::get()->getValue(count_rows($a,$Z,$Bg,$nf));$D=$y?(int)floor(max(0,intval($Ye)-1)/$y):0;}else{$Ye=false;$D=(int)$D;}$Jl=$K;$of=$nf;if(!$Jl){$Jl[]="*";$sc=convert_fields($d,$m,$K);if($sc)$Jl[]=substr($sc,2);}foreach($K
as$w=>$W){$l=$m[idf_unescape($W)];if($l&&($Sa=convert_field($l)))$Jl[$w]="$Sa AS $W";}if(DIALECT=="pgsql"||DIALECT=="mssql"){foreach((array)$_GET["columns"]as$w=>$W){if(isset($Jl[$w])&&$W["fun"])$Jl[$w].=" AS ".idf_escape(apply_sql_function($W["fun"],($W["col"]!=""?$W["col"]:"*")));}}if(!$Bg&&$jo){foreach($jo
as$w=>$W){$Jl[]=idf_escape($w);if($of)$of[]=idf_escape($w);}}$G=Driver::get()->select($a,$Jl,$Z,$of,$rj,$y,$D,true);if(!is_object($G))echo"<p class='error'>".(error()?:lang(125))."\n";else{if(DIALECT=="mssql"&&$D)$G->seek($y*$D);$J=[];while($I=$G->fetchAssoc()){if($D&&DIALECT=="oracle")unset($I["RNUM"]);$J[]=$I;}if($_GET["modify"]&&$J){$Ph=max_input_vars(count($J[0])+1,20);echo($Ph&&count($J)>$Ph?"<p class='error'>".max_input_vars_error()."\n":"");}echo"<form id='selection_form' action='' method='post' enctype='multipart/form-data'>\n","<div class='table-footer-parent'>\n";if($_GET["page"]!="last"&&$y&&$nf&&$Bg&&DIALECT=="sql")$Ye=Connection::get()->getValue(" SELECT FOUND_ROWS()");$Id=false;if(!$J)echo"<p class='message'>".lang(92)."\n";else{$fb=Admin::get()->getBackwardKeys($a,$en);echo"<div class='scrollable'>\n","<table id='table' class='nowrap checkable'>\n","<thead><tr>";if($nf||!$K){echo"<th class='actions'><input type='checkbox' id='all-page' class='jsonly' title='".lang(308)."'>".script("gid('all-page').onclick = partial(formCheck, /^check/);","");if(Admin::get()->isDataEditAllowed())echo" <a href='",h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."' title='",lang(309),"'>",icon_solo("edit-all"),"</a>";}$wi=[];$Pi=[];$ff=[];reset($K);$Sk=1;foreach($J[0]as$w=>$W){if(!isset($jo[$w])){$Ml=key($K);$W=isset($_GET["columns"][$Ml])?$_GET["columns"][$Ml]:[];$l=$m[$K?($W?$W["col"]:current($K)):$w];$B=($l?Admin::get()->getFieldName($l,$Sk):(isset($W["fun"])?"*":h($w)));if($B!=""){$Sk++;$wi[$w]=$B;$Pi[$w]=($l&&preg_match(number_type(),$l["type"])&&!preg_match('~\[~',$l["full_type"]))||preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|UNIX_TIMESTAMP|TIME_TO_SEC|COUNT|SUM)\(~',(string)current($K));$c=idf_escape($w);$Mf=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($w);$Yc="&desc%5B0%5D=1";$sj=isset($rj[0])?$rj[0]:"";$qm=preg_replace('~ DESC( NULLS LAST)?$~','',$sj);$sm=($qm==$c||$qm==$w);echo"<th id='th[".h(bracket_escape($w))."]'".($Pi[$w]?" class='number'":"").($sm?" aria-sort='".($qm==$sj?"ascending":"descending")."'":"").">";$ef=apply_sql_function(isset($W["fun"])?$W["fun"]:null,$B);$rm=isset($l["privileges"]["order"])||(isset($W["fun"])?$W["fun"]:null);if($rm)echo'<a href="',h($Mf.($sm&&$qm==$sj?$Yc:'')),'">',"$ef</a>";else
echo$ef;echo"<span class='column'>";if($rm)echo"<a href='".h($Mf.$Yc)."' title='".lang(63)."' class='button light'>",icon_solo("arrow-down"),"</a>";if(!isset($W["fun"])&&isset($l["privileges"]["where"]))echo"<a href='#fieldset-search' title='".lang(60)."' class='button light jsonly'>",icon_solo("search"),"</a>",script("qsl('a').onclick = partial(selectSearch, '".js_escape($w)."');");echo"</span>";}$ff[$w]=isset($W["fun"])?$W["fun"]:null;next($K);}}$mh=[];if($_GET["modify"]){foreach($J
as$I){foreach($I
as$w=>$W)$mh[$w]=max($mh[$w],min(40,strlen(utf8_decode($W))));}}if($fb)echo"<th>".lang(19)."</th>";echo"</thead>\n","<tbody>\n";if(is_ajax())ob_end_clean();foreach(Admin::get()->fillForeignDescriptions($J,$Re)as$si=>$I){$fo=unique_array($J[$si],$v);if(!$fo){$fo=[];reset($K);foreach($J[$si]as$w=>$W){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($K)))$fo[$w]=$W;next($K);}}$go="";foreach($fo
as$w=>$W){$l=isset($m[$w])?$m[$w]:null;$Ag=$l&&is_blob($l);if((DIALECT=="sql"||DIALECT=="pgsql")&&$l&&($Ag||preg_match('~char|text|enum|set~',$l["type"]))&&strlen($W)>64){$w=(strpos($w,'(')?$w:idf_escape($w));$w="MD5(".($Ag||DIALECT!='sql'||preg_match("~^utf8~",isset($l["collation"])?$l["collation"]:"")?$w:"CONVERT($w USING ".charset(Connection::get()).")").")";$W=md5($Ag?(string)Connection::get()->formatValue($W,$l):$W);}$go
.="&".($W!==null?urlencode("where[".bracket_escape($w)."]")."=".urlencode($W===false?"f":$W):"null%5B%5D=".urlencode($w));}echo"<tr>";if($nf||!$K){echo"<td class='actions'>",checkbox("check[]",substr($go,1),in_array(substr($go,1),(array)$_POST["check"]));if(!$Bg&&Admin::get()->isDataEditAllowed())echo" <a href='",h(ME."edit=".urlencode($a).$go),"' class='edit' title='",lang(39),"'>",icon_solo("edit"),"</a>";}reset($K);foreach($I
as$w=>$W){if(isset($wi[$w])){$c=current($K);$l=isset($m[$w])?$m[$w]:null;$z="";if($l&&is_blob($l)&&$W!="")$z=ME.'download='.urlencode($a).'&field='.urlencode($w).$go;if(!$z&&$W!==null){foreach((array)$Re[$w]as$p){if(count($Re[$w])==1||end($p["source"])==$w){$z="";foreach($p["source"]as$r=>$tm)$z
.=where_link($r,$p["target"][$r],$J[$si][$tm]);$z=($p["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($p["db"]),ME):ME).'select='.urlencode($p["table"]).$z;if($p["ns"])$z=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($p["ns"]),$z);if(count($p["source"])==1)break;}}}if($c=="COUNT(*)"){$z=ME."select=".urlencode($a);$r=0;foreach((array)$_GET["where"]as$V){if(!array_key_exists($V["col"],$fo))$z
.=where_link($r++,$V["col"],$V["val"],$V["op"]);}foreach($fo
as$Lg=>$V)$z
.=where_link($r++,$Lg,$V);}$Li=$W===null;$Nf=select_value($W,$z,$l,$yn);$Zd=bracket_escape($w);$s=h("val[$go][$Zd]");$uk=isset($_POST["val"][$go][$Zd])?$_POST["val"][$go][$Zd]:null;$no=isset($l["privileges"]["update"])?$l["privileges"]["update"]:false;$Gd=!is_array($W)&&!($l&&is_blob($l))&&is_utf8((string)$W)&&$J[$si][$w]==$W&&!$ff[$w]&&!(isset($l["generated"])?$l["generated"]:false);$T=($c&&preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$c,$A)?$m[idf_unescape($A[2])]["type"]:(isset($l["type"])?$l["type"]:null));$li=$T=="money"||($c&&preg_match('~^SUM\((.+)\)~',$c,$A)&&$m[idf_unescape($A[1])]["type"])=="money";$vn=$T&&preg_match('~text|json|lob~',$T);$Lb=$Pi[$w]&&($Li||is_numeric(strip_tags($Nf))||$li)?"class='number'":"";echo"<td id='$s' $Lb";if(($_GET["modify"]&&$Gd&&!$Li)||$uk!==null){$Id=true;$tf=h($uk!==null?$uk:$W);echo" data-editing='true'>".($vn?"<textarea name='$s' cols='30' rows='".(substr_count($W,"\n")+1)."'>$tf</textarea>":"<input class='input' name='$s' value='$tf' size='$mh[$w]'>");}else{$Ch=strpos($Nf,"<i>…</i>");if($no)echo" data-text='".($Ch?2:($vn?1:0))."'".($Gd?"":" data-warning='".lang(310)."'");echo">$Nf";}}next($K);}if($fb){echo"<td>";Admin::get()->printBackwardKeys($fb,$J[$si]);echo"</td>";}echo"</tr>\n";}if(is_ajax())exit;echo"</tbody>\n",script("mixin(qs('#table tbody'), {onclick: event => tableClick(event, false, ".(Admin::get()->isDataEditAllowed()?"true":"false")."), ondblclick: event => tableClick(event, true), onkeydown: onEditingKeydown});"),"</table>\n",script("initToggles(gid('table'));"),"</div>\n";}if(!is_ajax()){if($J||$D){$ce=true;if($_GET["page"]!="last"){if(!$y||(count($J)<$y&&($J||!$D)))$Ye=($D?$D*$y:0)+count($J);elseif(DIALECT!="sql"||!$Bg){$Ye=($Bg?false:found_rows($Q,$Z));if($Ye<max(1e4,2*($D+1)*$y))$Ye=first(slow_query(count_rows($a,$Z,$Bg,$nf)));elseif(DIALECT=='sql'||DIALECT=='pgsql')$ce=false;}}$Lj=($y&&($Ye===false||$Ye>$y||$D));if($Lj){if(($Ye===false?count($J)+1:$Ye-$D*$y)>$y)echo'<p class="links">','<a href="',h(remove_from_uri("page")."&page=".($D+1)),'" class="loadmore">',icon("expand"),lang(311),'</a>',script("qsl('a').onclick = partial(loadNextPage, $y, '".js_escape(lang(312))."');","");echo"\n";}echo"<div class='table-footer'><div class='field-sets'>\n";if($Lj){$Nh=($Ye===false?$D+(count($J)>=$y?2:1):(int)floor(($Ye-1)/$y));$qd="<li>…</li>";echo"<fieldset><legend>".lang(313)."</legend>";if(DIALECT!="simpledb"){echo"<div id='fieldset-pagination' class='fieldset-content'><ul class='pagination'>",pagination(0,$D);if($D>5)echo$qd;for($r=max(1,$D-4);$r<min($Nh,$D+5);$r++)echo
pagination($r,$D);if($Nh>0){if($D+5<$Nh)echo$qd;echo($ce&&$Ye!==false?pagination($Nh,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$Nh'>".lang(314)."</a>");}echo"</ul></div>";}else{echo"<div id='fieldset-pagination'><ul class='pagination'>",pagination(0,$D);if($D>1)echo$qd;if($D)echo
pagination($D,$D);if($Nh>$D){echo
pagination($D+1,$D);if($Nh>$D+1)echo$qd;}echo"</ul></div>";}echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(315)."</legend><div class='fieldset-content'>";$id=($ce?"":"~ ").$Ye;echo
checkbox("all",1,0,($Ye!==false?($ce?"":"~ ").lang(201,$Ye):""),"countRows.call(this, '$id');")."\n","</div></fieldset>\n";if(Admin::get()->isDataEditAllowed()){echo"<fieldset",($_GET["modify"]?'':' class="jsonly"'),">","<legend>",lang(309),"</legend>";$zl=($_GET["modify"]?"":" data-inline-edit='1'".($Id?"":" disabled"));echo"<div class='fieldset-content'",($_GET["modify"]?"":" title='".lang(304)."'"),">","<input type='submit' class='button' id='modify-save' value='",lang(118),"'",$zl,">","</div>","</fieldset>\n","<fieldset>","<legend>",lang(171)," <span id='selected'></span></legend>","<div class='fieldset-content'>","<input type='submit' class='button' name='edit' value='",lang(39),"'> ","<input type='submit' class='button' name='clone' value='",lang(300),"'> ","<input type='submit' class='button' name='delete' value='",lang(122),"'>",confirm(),"</div>","</fieldset>\n";}$Gc=Admin::get()->getDataDumpFormats();foreach((array)$_GET["columns"]as$c){if($c["fun"]){unset($Gc['sql']);break;}}if($Gc){print_fieldset_start("export",lang(75)." <span id='selected2'></span>","export");echo
html_select("format",$Gc,$M->getParameter("exportFormat"));$Hj=Admin::get()->getDumpOutputs();echo($Hj?" ".html_select("output",$Hj,$M->getParameter("exportOutput")):"")," <input type='submit' class='button' name='export' value='".lang(75)."'>\n";print_fieldset_end("export");}echo"</div></div>\n",script("initTableFooter()");}echo"</div>\n";if(Admin::get()->isDataEditAllowed()){echo"<p>","<a href='#import'>",icon("import"),lang(74),"</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"</p>","<p id='import'",($_POST["import"]?"":" class='hidden'"),">";if(ini_bool("file_uploads"))echo"<input type='file' name='csv_file'> ",html_select("import_format",["csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"],$M->getParameter("exportFormat"))," <input type='submit' class='button default' name='import' value='".lang(74)."'>",file_upload_form_script("selection_form","csv_file");else
echo
lang(208);echo"</p>";}echo
input_token(),"</form>\n",(!$nf&&$K?"":script("tableCheck();"));}else
echo"</div>\n";}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$N=isset($_GET["status"]);$S=$N?lang(164):lang(163);page_header($S,[$S]);$Co=($N?Admin::get()->getStatusVariables():Admin::get()->getServerVariables());if(!$Co)echo"<p class='message'>",lang(92),"</p>\n";else{echo"<div class='scrollable'><table>\n";foreach($Co
as$I){echo"<tr>";$w=array_shift($I);echo"<th><code class='jush-".DIALECT.($N?"status":"set")."'>".h($w)."</code></th>";foreach($I
as$W)echo"<td>",nl2br(h($W)),"</td>";echo"</tr>\n";}echo"</table></div>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$Rm=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$g=[];$Lc=null;foreach(table_status()as$B=>$Q){$g["Comment-$B"]=h($Q["Comment"]);if(!is_view($Q)||preg_match('~materialized~i',$Q["Engine"])){$g["Engine-$B"]=h($Q["Engine"]);$Qb=isset($Q["Collation"])?$Q["Collation"]:"";if($Qb==""){if($Lc===null)$Lc=(string)db_collation(DB,collations());$Qb=$Lc;}$g["Collation-$B"]=h($Qb);foreach($Rm+["Auto_increment"=>0,"Rows"=>0]as$w=>$W){if($Q[$w]!=""){$W=format_number($Q[$w]);if($W>=0)$g["$w-$B"]=($w=="Rows"?format_rows($Q):$W);if(isset($Rm[$w]))$Rm[$w]+=($Q["Engine"]!="InnoDB"||$w!="Data_free"?$Q[$w]:0);}elseif(array_key_exists($w,$Q))$g["$w-$B"]="?";}}}if(function_exists('AdminNeo\db_status'))$Rm=db_status();foreach($Rm
as$w=>$W)$g["sum-$w"]=format_number($W);echo
json_encode($g,JSON_UNESCAPED_UNICODE);}elseif($_GET["script"]=="kill")Connection::get()->query("KILL ".number($_POST["kill"]));else{$g=[];foreach(count_tables(Admin::get()->getDatabases(false))as$i=>$W){$g["tables-$i"]=$W;$g["size-$i"]=db_size($i);}echo
json_encode($g,JSON_UNESCAPED_UNICODE);}exit;}else{$on=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($on&&!$_POST["search"]){$G=true;$Wh="";if(DIALECT=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$G=truncate_tables($_POST["tables"]);$Wh=lang(316);}elseif($_POST["move"]){$G=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Wh=lang(317);}elseif($_POST["copy"]){$G=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Wh=lang(318);}elseif($_POST["drop"]){if($_POST["views"])$G=drop_views($_POST["views"]);if($G&&$_POST["tables"])$G=drop_tables($_POST["tables"]);$Wh=lang(319);}elseif(DIALECT=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$P){foreach(get_rows("PRAGMA integrity_check(".q($P).")")as$I)$Wh
.="<b>".h($P)."</b>: ".h($I["integrity_check"])."<br>";}}elseif(DIALECT!="sql"){$G=(DIALECT=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),(array)$_POST["tables"]));$Wh=lang(320);}elseif(!$_POST["tables"])$Wh=lang(79);elseif($G=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('AdminNeo\idf_escape',$_POST["tables"])))){while($I=$G->fetchAssoc())$Wh
.="<b>".h($I["Table"])."</b>: ".h($I["Msg_text"])."<br>";}queries_redirect($_SERVER["REQUEST_URI"],$Wh,(bool)$G);}if($_GET["ns"]=="")page_header(lang(31).": ".h(DB),true);else
page_header(lang(82).": ".h($_GET["ns"]),true);Admin::get()->printDatabaseMenu();if($_GET["ns"]===""){echo"<h2 id='schemas'>".lang(321)."</h2>\n";$Fl=Admin::get()->getSchemas();if(!$Fl)echo"<p class='message'>".lang(322)."\n";else{echo"<div class='scrollable'>\n","<table class='nowrap'>\n",'<thead><tr class="wrap"><th>',lang(82),"</th></tr></thead>";foreach($Fl
as$B)echo"<tr><th><a href='",h(ME),"ns=".urlencode($B),"' title='",lang(323),"'>".h($B)."</a></th></tr>";echo'</table></div>';}echo'<p class="links"><a href="'.h(ME).'scheme=">'.icon("database-add").lang(77)."</a>\n";}else{echo"<h2 id='tables-views'>".lang(324)."</h2>\n";$jn=['sql'=>'show-table-status.html','mariadb'=>'reference/sql-statements/administrative-sql-statements/show/show-table-status'];$Lc=db_collation(DB,collations());$d=["Engine"=>["label"=>lang(176),"doc"=>doc_link(['sql'=>'storage-engines.html','mariadb'=>'server-usage/storage-engines']),],];if($Lc!="")$d["Collation"]=["label"=>lang(47),"doc"=>doc_link(['sql'=>'charset-charsets.html','mariadb'=>'reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations']),];$d+=["Data_length"=>["label"=>lang(325),"doc"=>doc_link($jn+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286']),"link"=>"create","title"=>lang(36),],"Index_length"=>["label"=>lang(326),"doc"=>doc_link($jn+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT']),"link"=>"indexes","title"=>lang(182),],"Data_free"=>["label"=>lang(327),"doc"=>doc_link($jn),"link"=>"edit","title"=>lang(8),],"Auto_increment"=>["label"=>lang(49),"doc"=>doc_link(['sql'=>'example-auto-increment.html','mariadb'=>'reference/data-types/auto_increment']),"link"=>"auto_increment=1&create","title"=>lang(36),],"Rows"=>["label"=>lang(328),"doc"=>doc_link($jn+['pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286']),"link"=>"select","title"=>lang(34),],];if(support("comment"))$d["Comment"]=["label"=>lang(48),"doc"=>doc_link($jn+['pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE']),];$rj=(is_string($_GET["order"])?$_GET["order"]:"");$Zc=null;if(preg_match('~^(.+)-(asc|desc)$~',$rj,$_)){$rj=$_[1];$Zc=($_[2]=="desc");}if($rj!="__table"&&!isset($d[$rj]))$rj="";if($Zc===null)$Zc=isset($d[$rj]["link"]);$Yo=($rj!=""&&$rj!="__table")||support("fast_status");$mn=($Yo?table_status():tables_list());if(!$mn)echo"<p class='message'>".lang(79)."\n";else{echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n";if(support("table")){echo"<div class='field-sets'>\n","<fieldset><legend>".lang(329)." <span id='selected2'></span></legend><div class='fieldset-content'>",html_select("op",Admin::get()->getOperators(),isset($_POST["op"])?$_POST["op"]:Driver::get()->getLikeOperator()),"<input type='search' class='input' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = event => bodyKeydown(event, 'search');","")," <input type='submit' class='button' name='search' value='".lang(60)."'>\n","</div></fieldset>\n","</div>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable'>\n",'<thead><tr class="wrap">','<th class="actions"><input id="check-all" type="checkbox" class="input jsonly" title="'.lang(196).'">'.script("gid('check-all').onclick = partial(formCheck, /^(tables|views)\[/);","");$ti=($rj==""||$rj=="__table");$dn=($ti&&!$Zc?ME."order=__table-desc":substr(ME,0,-1));$vi=($ti&&DIALECT!="sqlite");echo'<th'.($vi?" aria-sort='".($Zc?"descending":"ascending")."'":'').'><a href="'.h($dn).'">'.lang(9).'</a>';foreach($d
as$w=>$c){$cd=($w===$rj?!$Zc:isset($c["link"]));echo'<th'.($w===$rj?" aria-sort='".($Zc?"descending":"ascending")."'":'').'><a href="'.h(ME)."order=$w-".($cd?"desc":"asc").'">'.$c["label"].'</a>'.$c["doc"];}echo"</thead>\n","<tbody>\n";if($rj=="__table"){if($Zc)$mn=array_reverse($mn,true);}elseif($rj){uasort($mn,function($ua,$cb)use($rj,$Zc){$Zo=isset($ua[$rj])?$ua[$rj]:null;$ap=isset($cb[$rj])?$cb[$rj]:null;$G=($Zo<$ap?-1:($Zo>$ap?1:0));return($Zc?-$G:$G);});}$Rm=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$R=0;foreach($mn
as$B=>$N){$Ho=($Yo?is_view($N):$N!==null&&!preg_match('~table|sequence~i',$N));$Qd=($Yo?(isset($N["Engine"])?$N["Engine"]:""):$N);$s=h("Table-".$B);echo'<tr><th class="actions">'.checkbox(($Ho?"views[]":"tables[]"),$B,in_array("$B",$on,true),"","","",$s);if(!Admin::get()->getSettings()->isSelectionPreferred()&&(support("table")||support("indexes")))$wa="table";else$wa="select";echo"<th><a href='",h(ME),"$wa=",urlencode($B),"' id='$s'>",h($B),"</a></th>";if($Ho&&!preg_match('~materialized~i',$Qd)){$S=lang(175);$Wb=count($d)-(support("comment")?2:1);echo'<td colspan="'.$Wb.'">'.(support("view")?"<a href='".h(ME)."view=".urlencode($B)."' title='".lang(37)."'>$S</a>":$S),"<td align='right'><a href='".h(ME)."select=".urlencode($B)."' title='".lang(34)."'>?</a>";}else{foreach($d
as$w=>$c){if($w=="Comment")continue;$s=" id='$w-".h($B)."'";$z=isset($c["link"])?$c["link"]:"";if(!$z){$W="";if($Yo){$W=isset($N[$w])?$N[$w]:"";if($w=="Collation"&&$W=="")$W=$Lc;}echo"<td$s>".h($W);continue;}$W="?";if($Yo){$Ni=isset($N[$w])?$N[$w]:"";if(is_numeric($Ni)&&$Ni>=0){$W=($w=="Rows"?format_rows($N):format_number($Ni));if(isset($Rm[$w])&&($Qd!="InnoDB"||$w!="Data_free"))$Rm[$w]+=$Ni;}}echo"<td align='right'>".(support("table")||$w=="Rows"||(support("indexes")&&$w!="Data_length")?"<a href='".h(ME."$z=").urlencode($B)."'$s title='".$c["title"]."'>".h($W)."</a>":"<span$s>".h($W)."</span>");}$R++;}echo(support("comment")?"<td id='Comment-".h($B)."'>".($Yo?h(isset($N["Comment"])?$N["Comment"]:""):""):""),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: event => tableClick(event, true)});"),"<tfoot><tr>","<td><th>".lang(301,count($mn)),"<td>".h(DIALECT=="sql"?Connection::get()->getValue("SELECT @@default_storage_engine"):""),($Lc!=""?"<td>".h($Lc):"");if($Yo&&function_exists('AdminNeo\db_status'))$Rm=db_status();foreach($Rm
as$w=>$Qm)echo"<td align='right' id='sum-$w'>".($Yo?format_number($Qm):"");echo"<td></td><td></td>";if(support("comment"))echo"<td></td>";echo"</tr></tfoot>\n","</table>\n","</div>\n",($Yo?"":script("ajaxSetHtml('".js_escape(ME)."script=db');"));if(Admin::get()->isDataEditAllowed()){echo"<div class='table-footer'><div class='field-sets'>\n";$_o="<input type='submit' class='button' value='".lang(330)."'> ".help_script("VACUUM");$nj="<input type='submit' class='button' name='optimize' value='".lang(331)."'> ".help_script(DIALECT=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE");echo"<fieldset><legend>".lang(171)." <span id='selected'></span></legend><div class='fieldset-content'>".(DIALECT=="sqlite"?$_o."<input type='submit' class='button' name='check' value='".lang(332)."'> ".help_script("PRAGMA integrity_check"):(DIALECT=="pgsql"?$_o.$nj:(DIALECT=="sql"?"<input type='submit' class='button' value='".lang(333)."'> ".help_script("ANALYZE TABLE").$nj."<input type='submit' class='button' name='check' value='".lang(332)."'> ".help_script("CHECK TABLE")."<input type='submit' class='button' name='repair' value='".lang(334)."'> ".help_script("REPAIR TABLE"):"")))."<input type='submit' class='button' name='truncate' value='".lang(335)."'> ".help_script(DIALECT=="sqlite"?"DELETE":("TRUNCATE".(DIALECT=="pgsql"?"":" TABLE"))).confirm()."<input type='submit' class='button' name='drop' value='".lang(172)."'>".help_script("DROP TABLE").confirm()."\n";$h=(support("scheme")?Admin::get()->getSchemas():Admin::get()->getDatabases());echo"</div></fieldset>\n";if(count($h)!=1&&DIALECT!="sqlite"){echo"<fieldset><legend>".lang(336)." <span id='selected3'></span></legend><div>";$i=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($h?html_select("target",$h,$i,"","label-move"):'<input class="input" name="target" value="'.h($i).'" autocapitalize="off">')," <input type='submit' class='button' name='move' value='".lang(337)."'>",(support("copy")?" <input type='submit' class='button' name='copy' value='".lang(338)."'> ".checkbox("overwrite",1,$_POST["overwrite"],lang(339)):""),"</div></fieldset>\n";}echo
input_hidden("all"),script("qsl('input').onclick = partial(countTables, $R);"),input_token(),"</div></div>\n",script("initTableFooter()");}echo"</div>\n","</form>\n",script("tableCheck();");}echo'<p class="links"><a href="',h(ME),'create=">',icon("table-add"),lang(78),"</a>\n";if(support("view"))echo'<a href="',h(ME),'view=">',icon("view-add"),lang(254),"</a>\n";if(support("routine")){echo"<h2 id='routines'>".lang(192)."</h2>\n";$tl=routines();if($tl){$cc=$tl[0]["ROUTINE_COMMENT"]!==null;echo"<table>\n",'<thead><tr>','<th>',lang(231),'</th><td>',lang(45),'</td><td>',lang(271),"</td>";if($cc)echo"<td>",lang(48),"</td>";echo"<td></td>","</tr></thead>\n";foreach($tl
as$I){$B=($I["SPECIFIC_NAME"]==$I["ROUTINE_NAME"]?"":"&name=".urlencode($I["ROUTINE_NAME"]));echo'<tr>','<th><a href="',h(ME.($I["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($I["SPECIFIC_NAME"]).$B),'">',h($I["ROUTINE_NAME"]),'</a></th>','<td>',h($I["ROUTINE_TYPE"]),'</td>','<td>',h($I["DTD_IDENTIFIER"]),'</td>';if($cc)echo'<td>',truncate_utf8(preg_replace('~\s{2,}~'," ",trim($I["ROUTINE_COMMENT"])),50),'</td>';echo'<td><a href="'.h(ME.($I["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($I["SPECIFIC_NAME"]).$B).'">'.lang(185)."</a></td>";}echo"</table>\n";}echo'<p class="links">';if(support("procedure"))echo'<a href="',h(ME),'procedure=">',icon("function-add"),lang(270),"</a>";echo'<a href="',h(ME),'function=">',icon("function-add"),lang(269),"</a>\n","</p>\n";}if(support("sequence")){echo"<h2 id='sequences'>".lang(340)."</h2>\n";$Wl=get_vals("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = current_schema() ORDER BY sequence_name");if($Wl){echo"<table>\n","<thead><tr><th>",lang(231),"</th><td></td></tr></thead>\n";foreach($Wl
as$W)echo"<tr>","<th>",h($W),"</th>","<td><a href='",h(ME),"sequence=",urlencode($W),"'>",lang(185),"</a></td>\n";echo"</table>\n";}echo"<p class='links'><a href='",h(ME),"sequence='>",icon("add"),lang(276),"</a></p>\n";}if(support("type")){echo"<h2 id='user-types'>".lang(112)."</h2>\n";$xo=types();if($xo){echo"<table>\n","<thead><tr><th>",lang(231),"</th><td></td></tr></thead>\n";foreach($xo
as$W)echo"<tr>","<th>",h($W),"</th>","<td><a href='",h(ME),"type=",urlencode($W),"'>",lang(185),"</a></td>\n";echo"</table>\n";}echo"<p class='links'><a href='",h(ME),"type='>",icon("add"),lang(277),"</a></p>\n";}if(support("event")){echo"<h2 id='events'>".lang(193)."</h2>\n";$J=get_rows("SHOW EVENTS");if($J){echo"<table>\n","<thead><tr><th>".lang(231)."<td>".lang(341)."<td>".lang(260)."<td>".lang(261)."<td></thead>\n";foreach($J
as$I)echo"<tr>","<th>".h($I["Name"]),"<td>".($I["Execute at"]?lang(342)."<td>".h($I["Execute at"]):lang(262)." ".h($I["Interval value"])." ".h($I["Interval field"])."<td>".h($I["Starts"])),"<td>".h($I["Ends"]),'<td><a href="'.h(ME).'event='.urlencode($I["Name"]).'">'.lang(185).'</a>';echo"</table>\n";$ae=Connection::get()->getValue("SELECT @@event_scheduler");if($ae&&$ae!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($ae)."\n";}echo'<p class="links"><a href="',h(ME),'event=">',icon("event-add"),lang(259),"</a></p>\n";}}}page_footer();