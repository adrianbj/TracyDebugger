<?php
/**
 * AdminNeo - Powerful database manager in a single PHP file
 * v5.8.0
 *
 * Compiled with
 * drivers:   mysql
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
mysqli_result;abstract
class
Plugin{protected$admin;protected$config;protected$settings;protected$locale;function
inject($ya,Config$Sb,Settings$O,Locale$xg){$this->admin=$ya;$this->config=$Sb;$this->settings=$O;$this->locale=$xg;}}abstract
class
Origin
extends
Plugin{private$errors=[];private
static$instance=null;static
function
create(array$Sb=[],array$Oi=[]){if(self::$instance)die("Admin instance already exists.\n");$ya=new
static();if(!$Sb&&file_exists("adminneo-config.php")){$Sb=include_once("adminneo-config.php");if(!is_array($Sb)){$Sb=[];$pg="href=https://github.com/adminneo-org/adminneo#configuration ".target_blank();$ya->addError(lang(0,"<b>adminneo-config.php</b>")." <a $pg>".lang(1)."</a>");}}$Sb=new
Config($Sb);$O=new
Settings($Sb);if(!$Oi&&file_exists("adminneo-plugins.php")){$Oi=include_once("adminneo-plugins.php");if(!is_array($Oi)){$Oi=[];$pg="href=https://github.com/adminneo-org/adminneo#plugins ".target_blank();$ya->addError(lang(0,"<b>adminneo-plugins.php</b>")." <a $pg>".lang(1)."</a>");}}self::$instance=$Oi?new
Pluginer($ya,$Oi):$ya;$ya->inject(self::$instance,$Sb,$O,Locale::get());foreach($Oi
as$Ni)$Ni->inject(self::$instance,$Sb,$O,Locale::get());return
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
getCredentials(){$N=$this->config->getServer(SERVER);return[$N?$N->getServer():SERVER,$_GET["username"],get_password()];}function
verifyDefaultPassword($F){$Ie=$this->config->getDefaultPasswordHash();if($Ie===null||$Ie==="")return
lang(2);elseif(!password_verify($F,$Ie))return
lang(3);return
true;}function
authenticate($V,$F){if($F==""){$Ie=$this->config->getDefaultPasswordHash();if($Ie===null)return
lang(4,target_blank());else
return$Ie==="";}return
true;}function
getPrivateKey($dc=false){return
get_private_key($dc);}function
getBruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
getServerName($N,$Ej=true,$Jd=null){if($N==""){if(!$Ej)return"";$N=Connection::exists()?Connection::get()->getDefaultServerName():"";if($N=="")return$Jd!==null?$Jd:lang(5);$ok=null;}else$ok=$this->config->getServer($N);return$ok?$ok->getName():preg_replace('~^https?://~',"",$N);}abstract
function
getDatabase();function
getDatabases($ce=true){$g=$this->filterListWithWildcards(get_databases($ce),$this->config->getHiddenDatabases(),false,Driver::get()->getSystemDatabases());if(DB!=""&&!in_array(DB,$g))array_unshift($g,DB);return$g;}function
getSchemas($wh=false){$Ne=$this->config->getHiddenSchemas();if($wh&&!in_array("__system",$Ne))$Ne[]="__system";$Zj=$this->filterListWithWildcards(schemas(),$Ne,false,Driver::get()->getSystemSchemas());if(isset($_GET["ns"])&&$_GET["ns"]!=""&&!in_array($_GET["ns"],$Zj))array_unshift($Zj,$_GET["ns"]);return$Zj;}function
getCollations(array$Lf=[]){$Lm=$this->config->getVisibleCollations();$Wd=$Lm?array_merge($Lm,$Lf):[];return$this->filterListWithWildcards(collations(),$Wd,true);}private
function
filterListWithWildcards(array$Cm,array$Wd,$Nf,array$hl=[]){if(!$Cm||!$Wd)return$Cm;$s=array_search("__system",$Wd);if($s!==false){unset($Wd[$s]);$Wd=array_merge($Wd,$hl);}array_walk($Wd,function(&$Y){$Y=str_replace('\\*',".*",preg_quote($Y,"~"));});$Hi='~^('.implode("|",$Wd).')$~';return$this->filterListWithPattern($Cm,$Hi,$Nf);}private
function
filterListWithPattern(array$Cm,$Hi,$Nf){$I=[];foreach($Cm
as$u=>$Y){if(is_array($Y)){if($Xk=$this->filterListWithPattern($Y,$Hi,$Nf))$I[$u]=$Xk;}elseif(($Nf&&preg_match($Hi,$Y))||(!$Nf&&!preg_match($Hi,$Y)))$I[$u]=$Y;}return$I;}abstract
function
getQueryTimeout();function
sendHeaders(){}function
updateCspHeader(array&$hc){}function
printFavicons(){$Db=validate_color_variant($this->config->getColorVariant());echo"<link rel='icon' type='image/x-icon' href='",link_files("favicon-$Db.ico",[]),"' sizes='32x32'>\n","<link rel='icon' type='image/svg+xml' href='",link_files("favicon-$Db.svg",[]),"'>\n","<link rel='apple-touch-icon' href='",link_files("apple-touch-icon-$Db.png",[]),"'>\n";}abstract
function
printToHead();function
getCssUrls(){$sm=$this->config->getCssUrls();foreach(["adminneo.css","adminneo-light.css","adminneo-dark.css"]as$n){if(file_exists($n))$sm[]="$n?v=".filemtime($n);}return$sm;}function
isLightModeForced(){return$this->isColorSchemeForced(false);}function
isDarkModeForced(){return$this->isColorSchemeForced(true);}private
function
isColorSchemeForced($mc){$bh=$mc?Settings::$ColorSchemeDark:Settings::$ColorSchemeLight;$ch=$mc?Settings::$ColorSchemeLight:Settings::$ColorSchemeDark;$Sd=file_exists("adminneo-$bh.css");$Td=file_exists("adminneo-$ch.css");if($Sd&&!$Td)return
true;return$this->settings->getColorScheme()==$bh&&!($Sd
xor$Td);}function
getJsUrls(){$sm=$this->config->getJsUrls();$n="adminneo.js";if(file_exists($n))$sm[]="$n?v=".filemtime($n);return$sm;}abstract
function
printLoginForm();function
getLoginFormRow($Nd,$Vf,$k){if($Vf)return"<tr><th>$Vf</th><td>$k</td></tr>\n";else
return"$k\n";}function
printLogout(){echo"<div class='logout'>","<form action='' method='post'>\n","<span title='",lang(6),"'>",h($_GET["username"]),"</span>","<input type='submit' class='button' name='logout' value='",lang(7),"' id='logout'>",input_token(),"</form>","</div>\n";}function
getTableName(array$ll){return
h($ll["Name"]);}abstract
function
getFieldName(array$k,$D=0);function
formatComment($Lb){return
h($Lb);}abstract
function
printTableMenu(array$ll,$qf);function
getForeignKeys($Q){return
foreign_keys($Q);}function
getBackwardKeys($Q,$jl){if(!$this->settings->isRelationLinks())return[];$L=backward_keys($Q);$Pf=[];foreach($L
as$K){$r=$K["table_schema"].".".$K["table_name"];$Pf[$r]["schema"]=$K["table_schema"];$Pf[$r]["table"]=$K["table_name"];$Pf[$r]["constraints"][$K["constraint_name"]][$K["column_name"]]=$K["referenced_column_name"];}foreach($Pf
as$r=>$u){$A=$this->admin->getTableName(table_status1($u["table"],true));if($A!=""){$bk=preg_quote($jl);$lk="(:|\\s*-)?\\s+";$Pf[$r]["name"]=(preg_match("(^$bk$lk(.+)|^(.+?)$lk$bk\$)iu",$A,$z)?$z[2].$z[3]:$A);}else
unset($Pf[$r]);}return$Pf;}function
printBackwardKeys(array$Ua,array$K){foreach($Ua
as$u){foreach($u["constraints"]as$Vb){$Mg=preg_replace('~&ns=[^&]+&~',"&ns=".urldecode($u["schema"])."&",ME);$x=$Mg.'select='.urlencode($u["table"]);$q=0;foreach($Vb
as$b=>$X){if(!isset($K[$X]))continue
2;$x
.=where_link($q++,$b,$K[$X]);}$A=preg_replace('(^'.preg_quote($_GET["select"]).(substr($_GET["select"],-1)=="s"?"?":"").'_)',"_",$u["name"]);$T=implode(", ",array_keys($Vb));echo"<a href='".h($x)."' title='".h($T)."'>".h($A)."</a>";$x=$Mg.'edit='.urlencode($u["table"]);foreach($Vb
as$b=>$X)$x
.="&preset".urlencode("[".bracket_escape($b)."]")."=".urlencode($K[$X]);echo"<a href='".h($x)."' title='".lang(8)."'>",icon_solo("add"),"</a> ";}}}abstract
function
formatSelectQuery($H,$Pk,$Id=false);abstract
function
formatMessageQuery($H,$Jl,$Id=false);abstract
function
formatSqlCommandQuery($H);function
printAfterSqlCommand(){}abstract
function
getTableDescriptionFieldName($Q);abstract
function
fillForeignDescriptions(array$L,array$fe);function
getFieldValueLink($X,$k){if(is_mail($X))return"mailto:$X";if(is_web_url($X))return$X;return
null;}abstract
function
formatSelectionValue($X,$x,$k,$li);abstract
function
formatFieldValue($Y,array$k);abstract
function
printTableStructure(array$l);abstract
function
printTablePartitions(array$yi);abstract
function
printRelatedTables(array$S);abstract
function
printTableIndexes(array$t,array$ll);abstract
function
printSelectionColumns(array$M,array$c);abstract
function
printSelectionSearch(array$Z,array$c,array$t);abstract
function
printSelectionOrder(array$D,array$c,array$t);abstract
function
printSelectionLimit($w);abstract
function
printSelectionLength($El);abstract
function
printSelectionAction(array$t);function
isDataEditAllowed(){return!information_schema(DB);}abstract
function
processSelectionColumns(array$c,array$t);abstract
function
processSelectionSearch(array$l,array$t);abstract
function
processSelectionOrder(array$l,array$t);function
processSelectionLimit(){if(!isset($_GET["limit"]))return$this->settings->getRecordsPerPage();return$_GET["limit"]!=""?(int)$_GET["limit"]:0;}abstract
function
processSelectionLength();abstract
function
getFieldFunctions(array$k);abstract
function
getFieldInput($Q,array$k,$Ma,$Y,$p);function
getFieldInputHint($Q,array$k,$Y){return
support("comment")?$this->admin->formatComment($k["comment"]):"";}abstract
function
processFieldInput(array$k,$Y,$p="");function
detectJson($Od,&$Y,$Zi=null){if(is_array($Y)){$ae=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($this->config->isJsonValuesAutoFormat()?JSON_PRETTY_PRINT:0);$Y=json_encode($Y,$ae);return
true;}$ae=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($Zi?JSON_PRETTY_PRINT:0);if(preg_match('~^jsonb?$~',$Od)){if($Y!=null&&$Zi!==null&&$this->config->isJsonValuesAutoFormat())$Y=json_encode(json_decode($Y),$ae);return
true;}if(!$this->config->isJsonValuesDetection())return
false;if(is_string($Y)&&$Y!=""&&preg_match('~varchar|text|character varying|String|keyword~',$Od)&&($Y[0]=="{"||$Y[0]=="[")&&($Jf=json_decode($Y))){if($Zi!==null&&$this->config->isJsonValuesAutoFormat())$Y=json_encode($Jf,$ae);return
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
sendDumpHeaders($Ye,$fh=false);function
dumpDatabase($pc){}abstract
function
dumpTable($Q,$Wk,$Im=0);abstract
function
dumpData($Q,$Wk,$H);abstract
function
getImportFilePath();abstract
function
printDatabaseMenu();abstract
function
printNavigation($Zg);abstract
function
printDatabaseSwitcher($Zg);function
printTablesFilter(){echo"<div class='tables-filter jsonly'>"."<input id='tables-filter' type='search' class='input' autocomplete='off' placeholder='".lang(9)."'>".script("initTablesFilter(".json_encode($this->admin->getDatabase(),JSON_HEX_TAG).");")."</div>\n";}abstract
function
printTableList(array$S);function
getSettingsRows($Ae){$O=[];if($Ae==1){$C=get_language_options();if($C)$O["lang"]="<tr><th id='label-language'>".lang(10)."</th>"."<td>".html_select("lang",get_language_options(),Locale::get()->getLanguage(),"","label-language")."</td></tr>\n";$Ol=get_theme_titles($this->config->getColorVariant());if(count($Ol)>1){list($Gl)=validate_theme($this->config->getTheme(),$this->config->getColorVariant());$C=[""=>lang(11)." ($Ol[$Gl])"]+$Ol;$O["theme"]="<tr><th id='label-theme'>".lang(12)."</th>"."<td>".html_select("theme",$C,($ra=$this->settings->getParameter("theme"))!==null?$ra:"","","label-theme")."</td></tr>\n";}$C=[""=>lang(13),Settings::$ColorSchemeLight=>lang(14),Settings::$ColorSchemeDark=>lang(15)];$O["colorScheme"]="<tr><th>".lang(16)."</th>"."<td>".html_radios("colorScheme",$C,($ra=$this->settings->getParameter("colorScheme"))!==null?$ra:"")."</td></tr>\n";}elseif($Ae==2){$C=[""=>lang(11),true=>lang(17),false=>lang(18),];$i=$C[$this->config->isRelationLinks()];$C[""].=" ($i)";$O["relationLinks"]="<tr><th>".lang(19)."</th>"."<td>".html_radios("relationLinks",$C,($ra=$this->settings->getParameter("relationLinks"))!==null?$ra:"")."<span class='input-hint'>".lang(20)."</span>"."</td></tr>\n";$i=$this->config->getRecordsPerPage();$C=[""=>lang(11)." ($i)","20","30","50","70","100",];$O["recordsPerPage"]="<tr><th id='label-records'>".lang(21)."</th>"."<td>".html_select("recordsPerPage",$C,($ra=$this->settings->getParameter("recordsPerPage"))!==null?$ra:"","","label-records")."<span class='input-hint'>".lang(22)."</span>"."</td></tr>\n";$i=($ra=$this->config->getEnumAsSelectThreshold())!==null?$ra:lang(23);$C=[""=>lang(11)." ($i)",-1=>lang(23),0=>lang(24),3=>lang(25,3),5=>lang(25,5),10=>lang(25,10),20=>lang(25,20),];$O["enumAsSelectThreshold"]="<tr><th id='label-enum'>".lang(26)."</th>"."<td>".html_select("enumAsSelectThreshold",$C,($ra=$this->settings->getParameter("enumAsSelectThreshold"))!==null?$ra:"","","label-enum",true)."<span class='input-hint'>".lang(27)."</span>"."</td></tr>\n";}return$O;}abstract
function
getForeignColumnInfo(array$fe,$b);}class
Pluginer{private
static$InternalMethods=["inject"=>true,"getConfig"=>true,];private
static$AppendMethods=["getErrors"=>true,"getFieldFunctions"=>true,"getDumpOutputs"=>true,"getDumpFormats"=>true,"getSettingsRows"=>true,];private$plugins;private$hooks=[];function
__construct(Origin$ya,array$Oi){$this->plugins=$Oi;foreach(get_class_methods('\AdminNeo\Origin')as$Xg){$this->hooks[$Xg]=[];if(!(isset(self::$InternalMethods[$Xg])?self::$InternalMethods[$Xg]:false)){foreach($Oi
as$Ni){if(method_exists($Ni,$Xg))$this->hooks[$Xg][]=$Ni;}}if(isset(self::$AppendMethods[$Xg])?self::$AppendMethods[$Xg]:false)array_unshift($this->hooks[$Xg],$ya);else$this->hooks[$Xg][]=$ya;}}function
getPlugins(){return$this->plugins;}function
__call($A,array$ti){$Ha=isset(self::$AppendMethods[$A])?self::$AppendMethods[$A]:false;$I=$Ha?[]:null;assert(isset($this->hooks[$A]),"Calling unknown plugin method: $A");foreach($this->hooks[$A]as$Ni){$Y=call_user_func_array([$Ni,$A],$ti);if($Y!==null){if($Ha)$I+=$Y;else
return$Y;}}return$I;}function
updateCspHeader(array&$hc){$this->__call(__FUNCTION__,[&$hc]);}function
detectJson($Od,&$Y,$Zi=null){return$this->__call(__FUNCTION__,[$Od,&$Y,$Zi]);}}class
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
printLoginForm(){$Uc=Drivers::getList();$pk=$this->config->getServerPairs($Uc);$N=SERVER?:$this->config->getDefaultServer();echo"<table class='box box-light'>\n";if($pk)echo$this->admin->getLoginFormRow('server',lang(5),"<select name='auth[server]'>".optionlist($pk,$N,true)."</select>");else{$Sc=DRIVER?:$this->config->getDefaultDriver($Uc);if(count($Uc)>1)echo$this->admin->getLoginFormRow('driver',lang(28),html_select("auth[driver]",$Uc,$Sc).script("initLoginDriver(qsl('select'));",""));else
echo$this->admin->getLoginFormRow('driver','',input_hidden("auth[driver]",$Sc));echo$this->admin->getLoginFormRow('server',lang(5),"<input class='input' name='auth[server]' value='".h($N)."' title='".lang(29)."' placeholder='localhost' autocapitalize='off'>");}echo$this->admin->getLoginFormRow('username',lang(6),'<input class="input" name="auth[username]" id="username" value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'),$this->admin->getLoginFormRow('password',lang(30),'<input type="password" class="input" name="auth[password]" autocomplete="current-password">');if(!$pk){$pc=isset($_GET["db"])?$_GET["db"]:$this->config->getDefaultDatabase();echo$this->admin->getLoginFormRow('db',lang(31),'<input class="input" name="auth[db]" value="'.h($pc).'" autocapitalize="off">');}echo"</table>\n","<p>","<input type='submit' class='button default' value='".lang(32)."'>",checkbox("auth[permanent]",1,$_COOKIE["neo_permanent"],lang(33)),"</p>\n";}function
getFieldName(array$k,$D=0){$U=$k["full_type"].($k["null"]?" NULL":"");$Lb=$k["comment"];$lk=$U&&$Lb!=""?": ":"";return'<span title="'.h($U.$lk.$Lb).'">'.h($k["field"]).'</span>';}function
printTableMenu(array$ll,$qf){echo'<p class="links top-tabs">';$qg=[];$hk=($this->settings->isSelectionPreferred()&&!$this->settings->isNavigationReversed())||(!$this->settings->isSelectionPreferred()&&$this->settings->isNavigationReversed());if($hk)$qg["select"]=[lang(34),"data"];if(support("table")||support("indexes"))$qg["table"]=[lang(35),"structure"];if(!$hk)$qg["select"]=[lang(34),"data"];$Q=$ll["Name"];$Ef=false;if(support("table")){$Ef=is_view($ll);if(!$Ef){if($Q!="")$qg["create"]=[lang(36),"edit"];}elseif(support("view"))$qg["view"]=[lang(37),"edit"];}if($qf!==null)$qg["edit"]=[lang(8),"item-add"];$ti=$qf?"&".http_build_query($qf):"";foreach($qg
as$u=>$X)echo" <a href='",h(ME),"$u=",urlencode($Q),($u=="edit"?$ti:""),"'",bold(isset($_GET[$u])),">",icon($X[1]),"$X[0]</a>";echo
doc_link([DIALECT=>Driver::get()->tableHelp($Q,$Ef)],icon("help").lang(38)),"\n";}function
formatSelectQuery($H,$Pk,$Id=false){$cl=support("sql");$Pm=!$Id?Driver::get()->warnings():null;if($cl)$H
.=";";$fl=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$J="<pre><code class='jush-$fl'>".h(str_replace("\n"," ",$H))."</code></pre>\n";$J
.="<p class='links'>";if($cl)$J
.="<a href='".h(ME)."sql=".urlencode($H)."'>".icon("edit").lang(39)."</a>";if($Pm)$J
.="<a href='#warnings' class='toggle'>".lang(40).icon_chevron_down()."</a>";$J
.=" <span class='time'>(".format_time($Pk).")</span>";$J
.="</p>\n";if($Pm){$J
.=script("initToggles(qsl('p'));");$J
.="<div id='warnings' class='warnings hidden'>\n$Pm\n</div>\n";}return$J;}function
formatMessageQuery($H,$Jl,$Id=false){restart_session();$Pe=&get_session("queries");if(!isset($Pe[$_GET["db"]]))$Pe[$_GET["db"]]=[];if(strlen($H)>1e6)$H=preg_replace('~[\x80-\xFF]+$~','',substr($H,0,1e6))."\n…";$Pe[$_GET["db"]][]=[$H,time(),$Jl];$cl=support("sql");$Pm=!$Id?Driver::get()->warnings():null;$Lk="sql-".count($Pe[$_GET["db"]]);$Qm="warnings-".count($Pe[$_GET["db"]]);$J=" ";if($Pm)$J
.="<a href='#$Qm' class='toggle'>".lang(40).icon_chevron_down()."</a>, ";$lj=support("sql")?lang(41):lang(42);$J
.="<a href='#$Lk' class='toggle'>$lj".icon_chevron_down()."</a>";$J
.=" <span class='time'>".@date("H:i:s")."</span>\n";if($Pm)$J
.="<div id='$Qm' class='warnings hidden'>\n$Pm</div>\n";$J
.="<div id='$Lk' class='hidden'>\n";$fl=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$J
.="<pre><code class='jush-$fl'>".truncate_utf8($H,1000)."</code></pre>\n";$J
.="<p class='links'>";if($cl)$J
.="<a href='".h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($Pe[$_GET["db"]])-1))."'>".icon("edit").lang(39)."</a>";if($Jl)$J
.=" <span class='time'>($Jl)</span>";$J
.="</p>\n";$J
.="</div>\n";return$J;}function
formatSqlCommandQuery($H){if(preg_match('~^DELIMITER\s~i',$H))return"";return
truncate_utf8($H,1000);}function
getTableDescriptionFieldName($Q){return"";}function
fillForeignDescriptions(array$L,array$fe){return$L;}function
formatSelectionValue($X,$x,$k,$li){if($X===null)$Dl="<i>NULL</i>";elseif(!$k)$Dl=$X;elseif(preg_match("~char|binary|boolean~",$k["type"])&&!preg_match("~var~",$k["type"]))$Dl="<code>$X</code>";elseif(is_blob($k)&&!is_utf8($X))$Dl="<i>".lang(43,strlen($li))."</i>";elseif($this->admin->detectJson($k["full_type"],$li))$Dl="<code class='jush-json'>$X</code>";else$Dl=$X;if($x)$Dl="<a href='".h($x)."'".(is_web_url($x)?target_blank():"").">$Dl</a>";return$Dl;}function
formatFieldValue($Y,array$k){return$Y;}function
printTableStructure(array$l){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th>",lang(44),"</th>","<td>",lang(45),"</td>","<td>",lang(46),"</td>";if(support("comment"))echo"<td>",lang(47),"</td>";echo"</tr></thead>\n";$ym=Driver::get()->getUserTypes();foreach($l
as$k){echo"<tr>","<th>",h($k["field"]),"</th>","<td>";$U=h($k["full_type"]);if(in_array($U,$ym))echo"<a href='".h(ME.'type='.urlencode($U))."'>$U</a>";else
echo$U;if($k["null"])echo" <i>NULL</i>";if($k["auto_increment"])echo" <i>".lang(48)."</i>";$i=h($k["default"]);if(isset($k["default"]))echo" <span title='".lang(49)."'>[<b>",$k["generated"]?"<code class='jush-".DIALECT."'>$i</code>":$i,"</b>]</span>";echo"</td>","<td>",h($k["collation"]),"</td>";if(support("comment"))echo"<td>",$this->admin->formatComment($k["comment"]),"</td>";echo"\n";}echo"</table>\n","</div>\n";}function
printTablePartitions(array$yi){$zk=isset($yi["partition_names"]);echo"<p>","<code class='jush-".DIALECT."'>BY {$yi["partition_by"]} ({$yi["partition"]})</code>";if(!$zk&&isset($yi["partitions"]))echo" ".lang(50).": ".h($yi["partitions"]);echo"</p>";if($zk){echo"<table>\n","<thead><tr><th>".lang(51)."</th><td>".lang(52)."</td></tr></thead>\n";foreach($yi["partition_names"]as$u=>$A){echo"<tr><th>";if(DIALECT=="pgsql")echo"<a href='",h(ME."table=".urlencode($A)),"'>";echo
h($A);if(DIALECT=="pgsql")echo"</a>";echo"</th><td>".h($yi["partition_values"][$u])."\n";}echo"</table>\n";}}function
printRelatedTables(array$S){echo"<ul class='links'>\n";foreach($S
as$K){$x=preg_replace('~ns=[^&]*~',"ns=".urlencode($K["ns"]),ME);echo"<li><a href='",h($x."table=".urlencode($K["table"])),"'>",icon("structure");if($K["ns"]!=$_GET["ns"])echo"<b>".h($K["ns"])."</b>.";echo
h($K["table"]),"</a>";}echo"</ul>\n";}function
printTableIndexes(array$t,array$ll){$uc=first(Driver::get()->getIndexAlgorithms($ll));$wi=false;foreach($t
as$s){if(isset($s["partial"])?$s["partial"]:false){$wi=true;break;}}echo"<table>\n","<thead><tr>","<th>",lang(45),"</th>","<td>",lang(53)," (",lang(54),")</td>";if($wi)echo"<td>",lang(55),"</td>";echo"</tr></thead>\n";foreach($t
as$A=>$s){ksort($s["columns"]);$bj=[];foreach($s["columns"]as$u=>$X)$bj[]="<i>".h($X)."</i>".($s["lengths"][$u]?"(".h($s["lengths"][$u]).")":"").($s["descs"][$u]?" DESC":"");echo"<tr title='",h($A),"'>","<th>",h($s["type"]);if(isset($s['algorithm'])&&$s['algorithm']!=$uc)echo" (",h($s['algorithm']),")";echo"</th>","<td>",implode(", ",$bj),"</td>";if($wi){echo"<td>";if($s['partial'])echo"<code class='jush-",DIALECT,"'>WHERE ",h($s['partial']),"</code>";echo"</td>";}echo"</tr>\n";}echo"</table>\n";}function
printSelectionColumns(array$M,array$c){print_fieldset_start("select",lang(56),"columns",(bool)$M,true);$M[""]=[];$q=0;foreach($M
as$u=>$X){$X=isset($_GET["columns"][$u])?$_GET["columns"][$u]:[];$b=select_input("name='columns[$q][col]'",$c,isset($X["col"])?$X["col"]:null,$u!==""?"selectFieldChange":"selectAddRow");echo"<div ",($u!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly");if(Driver::get()->getFunctions()||Driver::get()->getGrouping())echo
html_select("columns[$q][fun]",[-1=>""]+array_filter([lang(57)=>Driver::get()->getFunctions(),lang(58)=>Driver::get()->getGrouping()]),isset($X["fun"])?$X["fun"]:null),help_script_command("value && value.replace(/ |\$/, '(') + ')'",true),script("qsl('select').onchange = (event) => { ".($u!==""?"":" qsl('select, input:not(.remove)', event.target.parentNode).onchange();")." };",""),"($b)";else
echo$b;echo" <button class='button light remove jsonly' title='",lang(59),"'>",icon_solo("remove"),"</button>",script("qsl('#fieldset-select .remove').onclick = selectRemoveRow;",""),"</div>\n";$q++;}print_fieldset_end("select",true);}function
printSelectionSearch(array$Z,array$c,array$t){print_fieldset_start("search",lang(60),"search",(bool)$Z);foreach($t
as$q=>$s){if($s["type"]=="FULLTEXT"){echo"<div>(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$s["columns"]))."</i>) AGAINST","<input type='text' class='input' name='fulltext[$q]' value='".h(isset($_GET["fulltext"][$q])?$_GET["fulltext"][$q]:null)."'>",script("qsl('input').oninput = selectFieldChange;","");if(DIALECT=='sql')echo
checkbox("boolean[$q]",1,isset($_GET["boolean"][$q]),"BOOL");echo"</div>\n";}}$mb="this.parentNode.firstChild.onchange();";foreach(array_merge((array)$_GET["where"],[[]])as$q=>$X){if(!$X||("$X[col]$X[val]"!=""&&in_array($X["op"],$this->getOperators())))echo"<div>",select_input(" name='where[$q][col]'",$c,$X["col"],($X?"selectFieldChange":"selectAddRow"),"(".lang(61).")"),html_select("where[$q][op]",$this->getOperators(),$X["op"],$mb),"<input type='text' class='input' name='where[$q][val]' value='".h($X["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $mb }, onkeydown: selectSearchKeydown});","")," <button class='button light remove jsonly' title='".lang(59)."'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-search .remove").onclick = selectRemoveRow;',""),"</div>\n";}print_fieldset_end("search");}function
printSelectionOrder(array$D,array$c,array$t){print_fieldset_start("sort",lang(62),"sort",(bool)$D,true);$_GET["order"][""]="";$q=0;foreach((array)$_GET["order"]as$u=>$X){if($u!=""&&$X=="")continue;echo"<div ",($u!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly"),select_input("name='order[$q]'",$c,$X,$u!==""?"selectFieldChange":"selectAddRow")," ",checkbox("desc[$q]",1,isset($_GET["desc"][$u]),lang(63))," <button class='button light remove jsonly' title='",lang(59),"'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-sort .remove").onclick = selectRemoveRow;',""),"</div>\n";$q++;}print_fieldset_end("sort",true);}function
printSelectionLimit($w){echo"<fieldset><legend>".lang(64)."</legend><div class='fieldset-content'>","<input type='number' name='limit' class='input size' value='$w'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
printSelectionLength($El){if($El!==null)echo"<fieldset><legend>".lang(65)."</legend><div class='fieldset-content'>","<input type='number' name='text_length' class='input size' value='".h($El)."'>","</div></fieldset>\n";}function
printSelectionAction(array$t){echo"<fieldset><legend>".lang(66)."</legend><div class='fieldset-content'>","<input type='submit' class='button' value='".lang(56)."'>"," <span id='noindex' title='".lang(67)."'></span>","<script".nonce().">\n";$c=new
stdClass();foreach($t
as$s){$jc=reset($s["columns"]);if($s["type"]!="FULLTEXT"&&$jc)$c->$jc=null;}echo"const indexColumns = ".json_encode($c,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG).";\n","selectFieldChange.call(gid('form')['select']);\n","</script>\n","</div></fieldset>\n";}function
processSelectionColumns(array$c,array$t){$M=[];$ze=[];foreach((array)$_GET["columns"]as$u=>$X){if($X["fun"]=="count"||($X["col"]!=""&&(!$X["fun"]||in_array($X["fun"],Driver::get()->getFunctions())||in_array($X["fun"],Driver::get()->getGrouping())))){$M[$u]=apply_sql_function($X["fun"],($X["col"]!=""?idf_escape($X["col"]):"*"));if(!in_array($X["fun"],Driver::get()->getGrouping()))$ze[]=$M[$u];}}return[$M,$ze];}function
processSelectionSearch(array$l,array$t){$J=[];foreach($t
as$q=>$s){if($s["type"]=="FULLTEXT"&&isset($_GET["fulltext"])&&$_GET["fulltext"][$q]!="")$J[]="MATCH (".implode(", ",array_map('AdminNeo\idf_escape',$s["columns"])).") AGAINST (".q($_GET["fulltext"][$q]).(isset($_GET["boolean"][$q])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$Z){$_b=$Z["col"];$Ph=$Z["op"];$X=$Z["val"];if("$_b$X"!=""&&in_array($Ph,$this->getOperators())){$Rb=[];foreach(($_b!=""?[$_b=>$l[$_b]]:$l)as$A=>$k){$Xi="";$Qb=" $Ph";$Eh=DIALECT=="pgsql"&&$Ph=="="&&$k["type"]=="oid";if($Eh)$Qb
.=" ".$this->admin->processFieldInput($k,$X)."::regproc";elseif(preg_match('~IN$~',$Ph)){$df=process_length($X);$Qb
.=" ".($df!=""?$df:"(NULL)");}elseif($Ph=="SQL")$Qb=" $X";elseif(preg_match('~^(I?LIKE) %%$~',$Ph,$z))$Qb=" $z[1] ".$this->admin->processFieldInput($k,"%$X%");elseif($Ph=="FIND_IN_SET"){$Xi="$Ph(".q($X).", ";$Qb=")";}elseif(!preg_match('~NULL$~',$Ph))$Qb
.=" ".$this->admin->processFieldInput($k,$X);if($_b!=""||(isset($k["privileges"]["where"])&&(preg_match('~^[-\d.'.(preg_match('~IN$~',$Ph)?',':'').']+$~',$X)||!preg_match('~'.number_type().'|bit~',$k["type"]))&&(!preg_match("~[\x80-\xFF]~",$X)||preg_match('~char|text|enum|set~',$k["type"]))&&(!preg_match('~date|timestamp~',$k["type"])||preg_match('~^\d+-\d+-\d+~',$X))&&(!preg_match('~^elastic~',DRIVER)||$k["type"]!="boolean"||preg_match('~true|false~',$X))&&(!preg_match('~^elastic~',DRIVER)||strpos($Ph,"regexp")===false||preg_match('~text|keyword~',$k["type"])))){if($Eh)$Rb[]=$Xi.idf_escape($A).$Qb;else$Rb[]=$Xi.Driver::get()->convertSearch(idf_escape($A),$Z,$k).$Qb;}}if(count($Rb)==1)$J[]=$Rb[0];elseif($Rb)$J[]="(".implode(" OR ",$Rb).")";else$J[]="1 = 0";}}return$J;}function
processSelectionOrder(array$l,array$t){$J=[];foreach((array)$_GET["order"]as$u=>$X){if($X!="")$J[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$X)?$X:idf_escape($X)).(isset($_GET["desc"][$u])?" DESC".(DIALECT=="pgsql"&&(isset($l[$X]["null"])?$l[$X]["null"]:null)?" NULLS LAST":""):"");}return$J;}function
processSelectionLength(){return
isset($_GET["text_length"])?$_GET["text_length"]:"100";}function
getFieldFunctions(array$k){$J=($k["null"]?"NULL/":"");$pm=isset($_GET["select"])||where($_GET);foreach([Driver::get()->getInsertFunctions(),Driver::get()->getEditFunctions()]as$u=>$re){if(!$u||(!isset($_GET["call"])&&$pm)){foreach($re
as$Hi=>$X){if(!$Hi||preg_match("~$Hi~",$k["type"]))$J
.="/$X";}}if($u&&$re&&!preg_match('~enum|set|bool~',$k["type"])&&!is_blob($k))$J
.="/SQL";}if($k["auto_increment"]&&!$pm)$J=lang(48);return
explode("/",$J);}function
getFieldInput($Q,array$k,$Ma,$Y,$p){return"";}function
processFieldInput(array$k,$Y,$p=""){if($p=="SQL")return$Y;if(isset($k["full_type"]))$this->admin->detectJson($k["full_type"],$Y,false);$A=$k["field"];$J=q($Y);if(preg_match('~^(now|getdate|uuid)$~',$p))$J="$p()";elseif(preg_match('~^current_(date|timestamp)$~',$p))$J=$p;elseif(preg_match('~^([+-]|\|\|)$~',$p))$J=idf_escape($A)." $p $J";elseif(preg_match('~^[+-] interval$~',$p))$J=idf_escape($A)." $p ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$Y)&&DIALECT!="pgsql"?$Y:$J);elseif(preg_match('~^(addtime|subtime|concat)$~',$p))$J="$p(".idf_escape($A).", $J)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$p))$J="$p($J)";elseif($k["type"]=="boolean"&&DIALECT=="elastic")$J=$J=="0"?"false":"true";return
unconvert_field($k,$J);}function
getDumpOutputs(){$pi=['file'=>lang(68),'text'=>lang(69),];if(function_exists('gzencode'))$pi['gz']='gzip';return$pi;}function
getDumpFormats(){return(support("dump")?['sql'=>'SQL']:[])+['csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV'];}function
sendDumpHeaders($Ye,$fh=false){$oi=$_POST["output"];$Ed=(str_contains($_POST["format"],"sql")?"sql":($fh?"tar":"csv"));if($oi=="gz"){header("Content-Type: application/x-gzip");ob_start(function($Tk){return
gzencode($Tk);},1e6);}elseif($Ed=="tar")header("Content-Type: application/x-tar");elseif($Ed=="sql"||$oi=="text")header("Content-Type: text/plain; charset=utf-8");else
header("Content-Type: text/csv; charset=utf-8");return$Ed;}function
dumpTable($Q,$Wk,$Im=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($Wk)dump_csv(array_keys(fields($Q)));}else{if($Im==2){$l=[];foreach(fields($Q)as$A=>$k)$l[]=idf_escape($A)." $k[full_type]";$dc="CREATE TABLE ".table($Q)." (".implode(", ",$l).")";}else$dc=create_sql($Q,$_POST["auto_increment"],$Wk);set_utf8mb4($dc);if($Wk&&$dc){if($Wk=="DROP+CREATE"||$Im==1)echo"DROP ".($Im==2?"VIEW":"TABLE")." IF EXISTS ".table($Q).";\n";if($Im==1)$dc=remove_definer($dc);echo"$dc;\n\n";}}}function
dumpData($Q,$Wk,$H){if($Wk){$Fg=(DIALECT=="sqlite"?0:1048576);$l=[];$Ze=false;if($_POST["format"]=="sql"){if($Wk=="TRUNCATE+INSERT")echo
truncate_sql($Q).";\n";$l=fields($Q);if(DIALECT=="mssql"){foreach($l
as$k){if($k["auto_increment"]){echo"SET IDENTITY_INSERT ".table($Q)." ON;\n";$Ze=true;break;}}}}$I=Connection::get()->query($H,1);if($I){$of="";$eb="";$Pf=[];$te=[];$Zk="";$cc=0;while($K=($Q!=''?$I->fetchAssoc():$I->fetchRow())){if(!$Pf){$Cm=[];foreach($K
as$X){$k=$I->fetchField();if(!empty($l[$k->name]['generated'])){$te[$k->name]=true;continue;}$Pf[]=$k->name;$u=idf_escape($k->name);$Cm[]="$u = VALUES($u)";}$Zk=($Wk=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Cm):"").";\n";}if($_POST["format"]!="sql"){if($Wk=="table"){dump_csv($Pf);$Wk="INSERT";}dump_csv($K);}else{if(!$of)$of="INSERT INTO ".table($Q)." (".implode(", ",array_map('AdminNeo\idf_escape',$Pf)).") VALUES";foreach($K
as$u=>$X){if(isset($te[$u])){unset($K[$u]);continue;}$k=$l[$u];$K[$u]=($X===null?"NULL":($X===false?0:unconvert_field($k,preg_match(number_type(),$k["type"])&&!preg_match('~\[~',$k["full_type"])&&is_numeric($X)?$X:(!is_blob($k)||is_utf8($X)?q($X):Driver::get()->quoteBinary($X)))));}$Qj=($Fg?"\n":" ")."(".implode(",\t",$K).")";if(!$eb)$eb=$of.$Qj;elseif(DIALECT=="mssql"?$cc%1000!=0:strlen($eb)+4+strlen($Qj)+strlen($Zk)<$Fg)$eb
.=",$Qj";else{echo$eb.$Zk;$eb=$of.$Qj;}}$cc++;}if($eb)echo$eb.$Zk;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",Connection::get()->getError())."\n";if($Ze)echo"SET IDENTITY_INSERT ".table($Q)." OFF;\n";}}function
getImportFilePath(){return"adminneo.sql";}function
printDatabaseMenu(){echo"<p class='links top-links'>\n";$yh=isset($_GET["ns"])?$_GET["ns"]:null;if($yh==""&&support("database"))echo'<a href="',h(ME),'database=">',icon("edit"),lang(70),"</a>\n";if($yh!=""&&support("scheme"))echo"<a href='",h(ME),"scheme='>",icon("edit"),lang(71),"</a>\n";if($yh!=="")echo'<a href="',h(ME),'schema=">',icon("schema"),lang(72),"</a>\n";if(support("privileges"))echo"<a href='",h(ME),"privileges='>",icon("users"),lang(73),"</a>\n";echo"</p>\n";}function
printNavigation($Zg){if($Zg=="auth"){$oi="";foreach((array)$_SESSION["pwds"]as$Em=>$tk){foreach($tk
as$N=>$zm){foreach($zm
as$V=>$F){if($F!==null){$sc=$_SESSION["db"][$Em][$N][$V];foreach(($sc?array_keys($sc):[""])as$h){$qk=$this->admin->getServerName($N,false);$T=h(get_driver_name($Em,$N)).($V!=""||$qk!=""?" - ":"").h($V).($V!=""&&$qk!=""?"@":"").h($qk).($h!=""?h(" - $h"):"");$oi
.="<li><a href='".h(auth_url($Em,$N,$V,$h))."' class='primary' title='$T'>$T</a></li>\n";}}}}}if($oi)echo"<nav id='logins'><menu>\n$oi</menu></nav>\n";}else{$this->admin->printDatabaseSwitcher($Zg);$va=[];if(DB==""||!$Zg){if(support("sql")){$va[]="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".icon("command").lang(41)."</a>";$va[]="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".icon("import").lang(74)."</a>";}$va[]="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".icon("export").lang(75)."</a>";}if(DB=="")$va[]='<a href="'.h(ME).'database="'.bold($_GET["database"]==="").">".icon("database-add").lang(76)."</a>\n";if(DB!=""&&$_GET["ns"]===""&&!$Zg)$va[]='<a href="'.h(ME).'scheme="'.bold($_GET["scheme"]==="").">".icon("database-add").lang(77)."</a>\n";if(DB!=""&&$_GET["ns"]!==""&&!$Zg)$va[]='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".icon("table-add").lang(78)."</a>\n";if($va)echo"<p class='links'>".implode("\n",$va)."</p>";$S=[];if($_GET["ns"]!==""&&!$Zg&&DB!=""){Connection::get()->selectDatabase(DB);$S=table_status('',true);}if($_GET["ns"]!==""&&!$Zg&&DB!=""){if($S){$this->admin->printTablesFilter();$this->admin->printTableList($S);}else
echo"<div id='tables'><p>".lang(79)."</p></div>\n";}elseif($_GET["ns"]!==""&&DB!="")echo"<div id='tables'></div>\n";if(support("sql")||DIALECT=="elastic"||DIALECT=="mongo"){echo"<script".nonce().">\n";if(support("sql")&&$S){$qg=[];foreach($S
as$Q=>$U)$qg[]=js_escape_re($Q);$kl=support("table")&&!$this->config->isSelectionPreferred()?"table":"select";echo"window.jushLinks = { ".DIALECT.": {\n",js_escape_key(ME.$kl.'=$&'),': /\b(?<!\$)('.implode('|',$qg).')(?!\$)\b/g';$Mk=["sql","check","event","procedure","trigger","view","type","table","processlist"];if(support('routine')&&array_intersect_key($_GET,array_flip($Mk))){foreach(routines()as$K)echo",\n",js_escape_key(ME.'function='.urlencode($K["SPECIFIC_NAME"]).'&name=$&'),': /\b'.js_escape_re($K["ROUTINE_NAME"]).'(?=["`\]]?\()/g';}echo"\n}};\n";foreach(["bac","bra","sqlite_quo","mssql_bra"]as$X)echo"jushLinks.$X = jushLinks.".DIALECT.";\n";}if(DIALECT!="elastic"&&DIALECT!="mongo"&&$this->getConfig()->isSqlAutocompletionEnabled()&&(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"]))){$ul=array_fill_keys(array_keys($S),[]);foreach(Driver::get()->getAllFields()as$Q=>$l){foreach($l
as$k)$ul[$Q][]=$k["field"];}echo"window.addEventListener('DOMContentLoaded', () => { autocompletion = jush.autocompleteSql('".idf_escape("")."', ".json_encode($ul,JSON_HEX_TAG)."); });\n";}echo"</script>\n";}echo
script("let autocompletion;\nwindow.addEventListener('DOMContentLoaded', () => { initSyntaxHighlighting('".js_escape(doc_version())."', '".js_escape(Connection::get()->getFlavor())."', autocompletion); });");}}function
printDatabaseSwitcher($Zg){$g=$this->admin->getDatabases();if(!$g&&DIALECT!="sqlite")return;echo"<div class='db-selector'><form action=''>";hidden_fields_get();echo"<div>";if($g)echo"<select id='database-select' name='db' title='",lang(31),"'>".optionlist([""=>"(".lang(80).")"]+$g,DB)."</select>".script("mixin(gid('database-select'), {onmousedown: dbMouseDown, onchange: dbChange});");else
echo"<input id='database-select' class='input' name='db' value='".h(DB)."' title='",lang(31),"' autocapitalize='off'>\n";echo"<input type='submit' value='".lang(81)."' class='button ".($g?"hidden":"")."'>\n","</div>";foreach(["import","sql","schema","dump","privileges"]as$X){if(isset($_GET[$X])){echo
input_hidden($X);break;}}echo"</form></div>\n";}function
printTableList(array$S){$Zc=$this->settings->isNavigationDual()||$this->settings->isNavigationHover();$Og=($Zc?"class='dual".($this->settings->isNavigationHover()?" hover":"")."'":($this->settings->isNavigationReversed()?"class='reversed'":""));echo"<nav id='tables'><div class='scroll-marker'></div><menu $Og>";foreach($S
as$Q=>$P){$Q="$Q";$A=$this->admin->getTableName($P);if($A==""||(isset($P["Partition"])?$P["Partition"]:false))continue;echo"<li>";$wa=in_array($Q,[$_GET["table"],$_GET["select"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"]]);$yb="primary".(is_view($P)?" view":"");$dl=support("table")||support("indexes");$ek=h(ME)."select=".urlencode($Q);$ml=h(ME)."table=".urlencode($Q);if($this->settings->isSelectionPreferred()){if($this->settings->isNavigationReversed()&&$dl)echo" <a href='$ml' title='",lang(35),"' class='secondary'>",icon("structure"),"</a>";echo"<a href='$ek'",bold($wa,$yb)," data-primary='true' title='$A'>$A</a>";if($Zc&&$dl)echo" <a href='$ml' title='",lang(35),"' class='secondary'>",icon_solo("structure"),"</a>";}else{if($this->settings->isNavigationReversed())echo" <a href='$ek' title='",lang(34),"' class='secondary'>",icon("data"),"</a>";if($dl)echo"<a href='$ml'",bold($wa,$yb)," data-primary='true' title='$A'>$A</a>";else
echo"<span data-primary='true'",bold($wa,$yb),">$A</span>";if($Zc)echo" <a href='$ek' title='",lang(34),"' class='secondary'>",icon_solo("data"),"</a>";}echo"</li>\n";}echo"</menu></nav>\n",script("initTablesList(".json_encode($this->admin->getDatabase(),JSON_HEX_TAG).");");}function
getSettingsRows($Ae){$O=parent::getSettingsRows($Ae);if($Ae==1){$C=[""=>lang(11),Config::$NavigationSimple=>lang(82),Config::$NavigationDual=>lang(83),Config::$NavigationHover=>lang(84),Config::$NavigationReversed=>lang(85)];$i=$C[$this->config->getNavigationMode()];$C[""].=" ($i)";$O["navigationMode"]="<tr><th>".lang(86)."</th>"."<td>".html_radios("navigationMode",$C,($ra=$this->settings->getParameter("navigationMode"))!==null?$ra:"")."<span class='input-hint'>".lang(87)."</span>"."</td></tr>\n";$C=[""=>lang(11),0=>lang(35),1=>lang(34),];$i=$C[$this->config->isSelectionPreferred()?1:0];$C[""].=" ($i)";$O["preferSelection"]="<tr><th id='label-links'>".lang(88)."</th>"."<td>".html_select("preferSelection",$C,($ra=$this->settings->getParameter("preferSelection"))!==null?$ra:"","","label-links",true)."<span class='input-hint'>".lang(89)."</span>"."</td></tr>\n";}return$O;}function
getForeignColumnInfo(array$fe,$b){return
null;}}class
TmpFile{private$handler;private$size;function
__construct(){$this->handler=tmpfile();}function
getSize(){return$this->size;}function
write($Xb){if(!$this->handler)return;$this->size+=strlen($Xb);fwrite($this->handler,$Xb);}function
send(){if(!$this->handler)return;fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}function
print_select_result(Result$I,$e=null,array$fi=[],$w=0){$qg=[];$t=[];$c=[];$ab=[];$fm=[];$J=[];for($q=0;(!$w||$q<$w)&&($K=$I->fetchRow());$q++){if(!$q){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>";for($Hf=0;$Hf<count($K);$Hf++){$k=$I->fetchField();if(!$k){echo"<th></th>";continue;}$A=$k->name;$ei=isset($k->orgtable)?$k->orgtable:"";$di=isset($k->orgname)?$k->orgname:$A;if(isset($k->table))$J[$k->table]=$ei;if($fi&&DIALECT=="sql")$qg[$Hf]=($A=="table"?"table=":($A=="possible_keys"?"indexes=":null));elseif($ei!=""){if(!isset($t[$ei])){$t[$ei]=[];foreach(indexes($ei,$e)as$s){if($s["type"]=="PRIMARY"){$t[$ei]=array_flip($s["columns"]);break;}}$c[$ei]=$t[$ei];}if(isset($c[$ei][$di])){unset($c[$ei][$di]);$t[$ei][$di]=$Hf;$qg[$Hf]=$ei;}}if($k->charsetnr==63)$ab[$Hf]=true;$fm[$Hf]=$k->type;$T=trim(($ei!=""?"$ei.$di":($k->name!=$di?$di:""))." ".Driver::get()->getTypeName($k));echo"<th".($T!=""?" title='".h($T)."'":"").">".h($A).($fi?doc_link(['sql'=>"explain-output.html#explain_".strtolower($A),'mariadb'=>"reference/sql-statements/administrative-sql-statements/analyze-and-explain-statements/explain#columns-in-explain-...-select",]):"");}echo"</thead>\n";}echo"<tr>";foreach($K
as$u=>$X){$x="";if(isset($qg[$u])&&!$c[$qg[$u]]){if($fi&&DIALECT=="sql"){$Q=$K[array_search("table=",$qg)];$x=ME.$qg[$u].urlencode($fi[$Q]!=""?$fi[$Q]:$Q);}else{$x=ME."edit=".urlencode($qg[$u]);foreach($t[$qg[$u]]as$_b=>$Hf)$x
.="&where".urlencode("[".bracket_escape($_b)."]")."=".urlencode($K[$Hf]);}}$U=($ab[$u]?'blob':($fm[$u]==254?'char':''));$k=['full_type'=>$U,'type'=>$U,];$X=select_value($X,$x,$k,null);$yb=$fm[$u]<=9||$fm[$u]==246?"class='number'":"";echo"<td $yb>$X</td>";}}if($q)echo"</table>\n</div>";else
echo"<p class='message'>".lang(90);echo"\n";return$J;}function
referencable_primary($jk){$J=[];foreach(table_status('',true)as$ol=>$Q){if($ol!=$jk&&fk_support($Q)){foreach(fields($ol)as$k){if($k["primary"]){if($J[$ol]){unset($J[$ol]);break;}$J[$ol]=$k;}}}}return$J;}function
textarea($A,$Y,$L=10,$Gb=80){echo"<textarea name='".h($A)."' rows='$L' cols='$Gb' class='sqlarea jush-".DIALECT."' spellcheck='false' wrap='off'>";if(is_array($Y)){foreach($Y
as$X)echo
h($X[0])."\n\n\n";}else
echo
h($Y);echo"</textarea>";}function
select_input($Ma,$C,$Y="",$Nh="",$Ki=""){if($C&&$Y!=""&&!isset($C[$Y]))$C=[$Y=>$Y]+$C;$yl=($C?"select":"input");return"<$yl $Ma".($C?"><option value=''>$Ki".optionlist($C,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$Ki'>").($Nh?script("qsl('$yl').onchange = $Nh;",""):"");}function
json_row($u,$X=null){static$Yd=true;if($Yd)echo"{";if($u!=""){echo($Yd?"":",")."\n\t\"".addcslashes($u,"\r\n\t\"\\/").'": '.($X!==null?'"'.addcslashes($X,"\r\n\t\"\\/").'"':'null');$Yd=false;}else{echo"\n}\n";$Yd=true;}}function
edit_type($u,$k,$Cb,$ge=[],$Hd=[]){$U=isset($k["type"])?$k["type"]:null;echo'<td><select name="',h($u),'[type]" class="type" aria-labelledby="label-type">';$Tc=Driver::get()->getTypes();if($U&&!isset($Tc[$U])&&!isset($ge[$U])&&!in_array($U,$Hd))$Hd[]=$U;$Vk=Driver::get()->getStructuredTypes();if($ge)$Vk[lang(91)]=$ge;echo
optionlist(array_merge($Hd,$Vk),$U),'</select><td><input name="',h($u),'[length]" value="',h(isset($k["length"])?$k["length"]:null),'" size="3"',(!(isset($k["length"])?$k["length"]:null)&&preg_match('~var(char|binary)$~',$U)?" class='input required'":" class='input'"),' aria-labelledby="label-length"><td class="options">',($Cb?"<select name='".h($u)."[collation]'".option_types($U,'(char|text|enum|set)$').'><option value="">('.lang(92).')'.optionlist($Cb,isset($k["collation"])?$k["collation"]:null).'</select>':''),(Driver::get()->getUnsigned()?"<select name='".h($u)."[unsigned]'".option_types($U,'^$|'.number_type()).'><option>'.optionlist(Driver::get()->getUnsigned(),isset($k["unsigned"])?$k["unsigned"]:null).'</select>':''),(isset($k['on_update'])?"<select name='".h($u)."[on_update]'".option_types($U,'timestamp|datetime').'>'.optionlist([""=>"(".lang(93).")","CURRENT_TIMESTAMP"],(preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?"CURRENT_TIMESTAMP":$k["on_update"])).'</select>':''),($ge?"<select name='".h($u)."[on_delete]'".option_types($U,'`')."><option value=''>(".lang(94).")".optionlist(Driver::get()->getOnActions(),isset($k["on_delete"])?$k["on_delete"]:null)."</select> ":" ");}function
option_types($U,$fm){return" data-types='".h($fm)."'".(preg_match("~$fm~",$U)?"":" class='hidden'");}function
process_length($v){$pd=Driver::$EnumLengthPattern;return(preg_match("~^\\s*\\(?\\s*$pd(?:\\s*,\\s*$pd)*+\\s*\\)?\\s*\$~",$v)&&preg_match_all("~$pd~",$v,$_)?"(".implode(",",$_[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$v)));}function
process_type($k,$Ab="COLLATE"){return" $k[type]".process_length($k["length"]).(preg_match(number_type(),$k["type"])&&in_array($k["unsigned"],Driver::get()->getUnsigned())?" $k[unsigned]":"").(preg_match('~char|text|enum|set~',$k["type"])&&$k["collation"]?" $Ab ".(DIALECT=="mssql"?$k["collation"]:q($k["collation"])):"");}function
process_field($k,$dm){if($k["on_update"])$k["on_update"]=preg_replace('~current_timestamp(\(\))?~i',"CURRENT_TIMESTAMP",$k["on_update"]);return[idf_escape(trim($k["field"])),process_type($dm),($k["null"]?" NULL":" NOT NULL"),default_value($k),(preg_match('~timestamp|datetime~',$k["type"])&&$k["on_update"]?" ON UPDATE ".$k["on_update"]:""),(support("comment")&&$k["comment"]!=""?" COMMENT ".q(normalize_newlines($k["comment"])):""),($k["auto_increment"]?auto_increment():null),];}function
normalize_newlines($Y){return
str_replace("\r","",(string)$Y);}function
default_value($k){if($k["default"]===null)return"";$i=normalize_newlines($k["default"]);$se=$k["generated"];if(in_array($se,Driver::get()->getGenerated())){if(DIALECT=="mssql")return" AS ($i)".($se=="VIRTUAL"?"":" $se");else
return" GENERATED ALWAYS AS ($i) $se";}if(stripos($i,"GENERATED ")===0)return" $i";if(preg_match('~char|binary|text|json|enum|set~',$k["type"])||preg_match('~^(?![a-z])~i',$i)){if(DIALECT=="sql"&&preg_match('~text|json~',$k["type"]))return" DEFAULT (".q($i).")";else
return" DEFAULT ".q($i);}else{$i=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$i);return" DEFAULT ".(DIALECT=="sqlite"?"($i)":$i);}}function
type_class($U){foreach(['char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',]as$yb=>$Hi){if(preg_match("~$yb|$Hi~",$U))return"class='$yb'";}return"";}function
edit_fields(array$l,array$Cb,$U="TABLE",$ge=[]){$l=array_values($l);$Ob=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$Mb=$Ob?"":"class='hidden'";echo"<thead><tr>\n";if(support("move_col"))echo"<th class='jsonly'></th>";if($U=="PROCEDURE")echo"<td></td>";echo"<th id='label-name'>",($U=="TABLE"?lang(95):lang(96)),"</th>\n","<td id='label-type'>",lang(45),"<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>",script("gid('enum-edit').onblur = onFieldLengthBlur;"),"</td>\n","<td id='label-length'>",lang(97),"</td>\n","<td>",lang(98),"</td>\n";if($U=="TABLE")echo"<td id='label-null'>NULL</td>\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='",lang(48),"'>AI</abbr>",doc_link(['sql'=>"example-auto-increment.html",'mariadb'=>"reference/data-types/auto_increment",]),"</td>\n","<td id='label-default'>",lang(49),"</td>\n",support("comment")?"<td id='label-comment' $Mb>".lang(47)."</td>\n":"";echo"<td>","<button name='add[",(support("move_col")?0:count($l)),"]' value='1' title='",lang(99),"' class='button light'>",icon_solo("add"),"</button>",(support("move_col")?"":script("qsl('button').onclick = onAddLastFieldRowClick;")),script("row_count = ".count($l).";"),"</td>\n","</tr></thead>\n";$yb=support("move_col")?"class='sortable'":"";echo"<tbody $yb>\n";foreach($l
as$q=>$k){$q++;$gi=$k[($_POST?"orig":"field")];$Jc=(isset($_POST["add"][$q-1])||(isset($k["field"])&&!(isset($_POST["drop_col"][$q])?$_POST["drop_col"][$q]:null)))&&(support("drop_col")||$gi=="");echo"<tr",($Jc?"":" hidden"),">\n";if(support("move_col"))echo"<th class='handle jsonly'>",icon_solo("handle"),"</td>";if($U=="PROCEDURE")echo"<td>",html_select("fields[$q][inout]",Driver::get()->getInOut(),$k["inout"]),"</td>\n";echo"<th>";if($Jc)echo"<input class='input' name='fields[$q][field]' value='",h($k["field"]),"' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name' ".(isset($_POST["add"][$q-1])?"autofocus":"").">";echo
input_hidden("fields[$q][orig]",$gi);edit_type("fields[$q]",$k,$Cb,$ge);echo"</th>\n";if($U=="TABLE"){echo"<td>",checkbox("fields[$q][null]",1,$k["null"],"","","block","label-null"),"</td>\n";$tb=$k["auto_increment"]?"checked":"";echo"<td><label class='block'><input type='radio' name='auto_increment_col' value='$q' $tb aria-labelledby='label-ai'></label></td>\n","<td class='default-value'>";if(Driver::get()->getGenerated())echo
html_select("fields[$q][generated]",array_merge(["","DEFAULT"],Driver::get()->getGenerated()),$k["generated"]);else
echo
checkbox("fields[$q][generated]",1,$k["generated"],"","","","label-default");$Ma="name='fields[$q][default]' aria-labelledby='label-default'";$Y=h($k["default"]);if(str_contains($Y,"\n")){if($Y[0]=="\n")$Y="\n$Y";echo"<textarea $Ma rows='3' cols='30' style='vertical-align: bottom;'>$Y</textarea>";}else
echo"<input class='input' $Ma value='$Y'>";echo"</td>\n";if(support("comment")){$Eg=Connection::get()->isMinVersion("5.5")?1024:255;$Ma="name='fields[$q][comment]' data-maxlength='$Eg' aria-labelledby='label-comment'";$Y=h($k["comment"]);echo"<td $Mb>";if(str_contains($Y,"\n")){if($Y[0]=="\n")$Y="\n$Y";echo"<textarea $Ma rows='3' cols='30' style='vertical-align: bottom;'>$Y</textarea>";}else
echo"<input class='input' $Ma value='$Y'>";echo"</td>\n";}}echo"<td>";if(support("move_col"))echo"<button name='add[$q]' value='1' title='".lang(99)."' class='button light'>",icon_solo("add"),"</button>","<button name='up[$q]' value='1' title='".lang(100)."' class='button light hidden'>",icon_solo("arrow-up"),"</button>","<button name='down[$q]' value='1' title='".lang(101)."' class='button light hidden'>",icon_solo("arrow-down"),"</button>";if($gi==""||support("drop_col"))echo"<button name='drop_col[$q]' value='1' title='".lang(59)."' class='button light'>",icon_solo("remove"),"</button>";echo"</td>\n</tr>\n";}echo"</tbody>";}function
process_fields(&$l){$Ch=0;if($_POST["up"]){$ag=0;foreach($l
as$u=>$k){if(key($_POST["up"])==$u){unset($l[$u]);array_splice($l,$ag,0,[$k]);break;}if(isset($k["field"]))$ag=$Ch;$Ch++;}}elseif($_POST["down"]){$le=false;foreach($l
as$u=>$k){if(isset($k["field"])&&$le){unset($l[key($_POST["down"])]);array_splice($l,$Ch,0,[$le]);break;}if(key($_POST["down"])==$u)$le=$k;$Ch++;}}elseif($_POST["add"]){$l=array_values($l);array_splice($l,key($_POST["add"]),0,[[]]);}elseif(!$_POST["drop_col"])return
false;return
true;}function
normalize_enum($z){$X=$z[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($X[0].$X[0],$X[0],substr($X,1,-1))),'\\'))."'";}function
grant($we,array$ej,$c,$Lh,$xm){if(!$ej)return
true;if($ej==["ALL PRIVILEGES","GRANT OPTION"]){if($we)return(bool)queries("GRANT ALL PRIVILEGES ON $Lh TO $xm WITH GRANT OPTION");else
return
queries("REVOKE ALL PRIVILEGES ON $Lh FROM $xm")&&queries("REVOKE GRANT OPTION ON $Lh FROM $xm");}if($ej==["GRANT OPTION","PROXY"]){if($we)return(bool)queries("GRANT PROXY ON $Lh TO $xm WITH GRANT OPTION");else
return(bool)queries("REVOKE PROXY ON $Lh FROM $xm");}return(bool)queries(($we?"GRANT ":"REVOKE ").preg_replace('~(GRANT OPTION)\([^)]*\)~','$1',implode("$c, ",$ej).$c)." ON $Lh ".($we?"TO ":"FROM ").$xm);}function
drop_create($Vc,$dc,$Wc,$Cl,$Xc,$y,$Sg,$Qg,$Rg,$Jh,$th){if($_POST["drop"])query_redirect($Vc,$y,$Sg);elseif($Jh=="")query_redirect($dc,$y,$Rg);elseif($Jh!=$th){$gc=queries($dc);queries_redirect($y,$Qg,$gc&&queries($Vc));if($gc)queries($Wc);}else
queries_redirect($y,$Qg,queries($Cl)&&queries($Xc)&&queries($Vc)&&queries($dc));}function
create_trigger($Lh,array$Yl){$Ll=" $Yl[Timing] $Yl[Event]".(preg_match('~ OF~',$Yl["Event"])?" $Yl[Of]":"");return"CREATE TRIGGER ".idf_escape($Yl["Trigger"]).(DIALECT=="mssql"?$Lh.$Ll:$Ll.$Lh).rtrim(" $Yl[Type]\n$Yl[Statement]",";").";";}function
create_routine($Mj,$K){$wk=[];$l=(array)$K["fields"];ksort($l);$ef=implode("|",Driver::get()->getInOut());foreach($l
as$k){if($k["field"]!="")$wk[]=(preg_match("~^($ef)\$~",$k["inout"])?"$k[inout] ":"").idf_escape($k["field"]).process_type($k,"CHARACTER SET");}$yc=rtrim($K["definition"],";");return"CREATE $Mj ".idf_escape(trim($K["name"]))." (".implode(", ",$wk).")".($Mj=="FUNCTION"?" RETURNS".process_type($K["returns"],"CHARACTER SET"):"").($K["language"]?" LANGUAGE $K[language]":"").(DIALECT=="pgsql"?" AS ".q($yc):"\n$yc;");}function
remove_definer($H){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$H);}function
format_foreign_key($o){$Mh=implode("|",Driver::get()->getOnActions());$h=$o["db"];$yh=$o["ns"];return" FOREIGN KEY (".implode(", ",array_map('AdminNeo\idf_escape',$o["source"])).") REFERENCES ".($h!=""&&$h!=$_GET["db"]?idf_escape($h).".":"").($yh!=""&&$yh!=$_GET["ns"]?idf_escape($yh).".":"").idf_escape($o["table"])." (".implode(", ",array_map('AdminNeo\idf_escape',$o["target"])).")".(preg_match("~^($Mh)\$~",$o["on_delete"])?" ON DELETE $o[on_delete]":"").(preg_match("~^($Mh)\$~",$o["on_update"])?" ON UPDATE $o[on_update]":"").(isset($o["deferrable"])?" $o[deferrable]":"");}function
tar_file($n,TmpFile$Pl){$Ke=pack("a100a8a8a8a12a12",$n,644,0,0,decoct($Pl->getSize()),decoct(time()));$vb=8*32;for($q=0;$q<strlen($Ke);$q++)$vb+=ord($Ke[$q]);$Ke
.=sprintf("%06o",$vb)."\0 ";echo$Ke,str_repeat("\0",512-strlen($Ke));$Pl->send();echo
str_repeat("\0",511-($Pl->getSize()+511)%512);}function
doc_link(array$Gi,$Dl="<sup>?</sup>"){if(!(isset($Gi[DIALECT])?$Gi[DIALECT]:null))return"";$Fm=doc_version();$sm=['sql'=>"https://dev.mysql.com/doc/refman/$Fm/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(Connection::get()->isCockroachDB()?"current":$Fm)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".str_replace(".","",$Fm)."&id=",'elastic'=>"https://www.elastic.co/guide/en/elasticsearch/reference/$Fm/",];if(Connection::get()->isMariaDB()){$sm['sql']="https://mariadb.com/docs/server/";$Gi['sql']=isset($Gi['mariadb'])?$Gi['mariadb']:str_replace(".html","",$Gi['sql']);}return"<a href='".h($sm[DIALECT].$Gi[DIALECT].(DIALECT=='mssql'?"?view=sql-server-ver$Fm":""))."'".target_blank().">$Dl</a>";}function
doc_version(){return
preg_replace('~^(\d\.?\d).*~s','\1',Connection::get()->getVersion());}function
db_size($h){if(!Connection::get()->selectDatabase($h))return"?";$J=0;foreach(table_status()as$R)$J+=$R["Data_length"]+$R["Index_length"];return
format_number($J);}function
set_utf8mb4($dc){static$wk=false;if(!$wk&&preg_match('~\butf8mb4~i',$dc)){$wk=true;echo"SET NAMES ".charset(Connection::get()).";\n\n";}}error_reporting(E_ALL&~E_DEPRECATED);set_error_handler(function($rd,$j){return(bool)preg_match('~^Undefined (array key|offset|index)~',$j);},E_WARNING|E_NOTICE);;$Vd=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($Vd||ini_get("filter.default_flags")){foreach(['_GET','_POST','_COOKIE','_SERVER']as$X){$mm=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($mm)$$X=$mm;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");class
Server{private$params;private$key;function
__construct(array$ti,$u=null){$this->params=$ti;$this->key=$u;}function
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
getConfigParams(){$ti=isset($this->params["config"])?$this->params["config"]:[];$ve=["servers"];foreach($ve
as$si){if(isset($ti[$si]))unset($ti[$si]);}return$ti;}}class
Config{static$NavigationSimple="simple";static$NavigationDual="dual";static$NavigationHover="hover";static$NavigationReversed="reversed";private$params;private$servers=[];function
__construct(array$ti){$this->params=$ti;if(isset($this->params["servers"])){foreach($this->params["servers"]as$u=>$N){$ok=new
Server($N,is_string($u)?$u:null);$this->params["servers"][$u]=$ok;$this->servers[$ok->getKey()]=$ok;}}}function
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
getDefaultDriver(array$Uc){$Sc=isset($this->params["defaultDriver"])?$this->params["defaultDriver"]:null;return$Sc&&isset($Uc[$Sc])?$Sc:key($Uc);}function
getDefaultServer(){$N=isset($this->params["defaultServer"])?$this->params["defaultServer"]:null;if($N===null)return
null;$ok=isset($this->params["servers"][$N])?$this->params["servers"][$N]:null;if($ok)return$ok->getKey();return$N;}function
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
getServerPairs(array$Uc){$Bk=null;foreach($this->servers
as$N){if(!isset($Uc[$N->getDriver()]))continue;if(!$Bk)$Bk=$N->getDriver();elseif($N->getDriver()!=$Bk){$Bk=null;break;}}$pk=[];foreach($this->servers
as$u=>$N){if(!isset($Uc[$N->getDriver()]))continue;$nk=$N->getName();if($Bk&&$nk)$pk[$u]=$nk;else$pk[$u]=$Uc[$N->getDriver()].($nk!=""?" - $nk":"");}return$pk;}function
getServer($mk){return
isset($this->servers[$mk])?$this->servers[$mk]:null;}function
applyServer($N){$N=$this->getServer($N);if(!$N)return;$this->params=array_merge($this->params,$N->getConfigParams());}private
function
parseList($sg){if(is_array($sg))return$sg;return
preg_split('~\s*,\s*~',(string)$sg);}}class
Settings{private
static$CookieName="neo_settings";static$ColorSchemeLight="light";static$ColorSchemeDark="dark";static$NavigationWidthMin=10;static$NavigationWidthMax=30;private$config;private$params=[];function
__construct(Config$Sb){$this->config=$Sb;if(isset($_COOKIE[self::$CookieName])){parse_str($_COOKIE[self::$CookieName],$this->params);$this->save();}if(isset($_COOKIE["neo_lang"])){$this->updateParameter("lang",$_COOKIE["neo_lang"]);unset($_COOKIE["neo_lang"]);cookie("neo_lang","",-3600);}}static
function
readParameter($u){parse_str(isset($_COOKIE[self::$CookieName])?$_COOKIE[self::$CookieName]:"",$ti);return
isset($ti[$u])?$ti[$u]:null;}function
getParameter($u,$i=null){return
isset($this->params[$u])?$this->params[$u]:$i;}function
updateParameter($u,$Y){$this->updateParameters([$u=>$Y]);}function
updateParameters(array$ti){$this->params=array_filter(array_merge($this->params,$ti),function($Y){return$Y!==null;});$this->save();}private
function
save(){cookie(self::$CookieName,http_build_query($this->params),7776000);}function
getTheme(){return($ra=$this->getParameter("theme"))!==null?$ra:$this->config->getTheme();}function
getColorScheme(){return$this->getParameter("colorScheme");}function
getNavigationMode(){return($ra=$this->getParameter("navigationMode"))!==null?$ra:$this->config->getNavigationMode();}function
isNavigationSimple(){return$this->getNavigationMode()==Config::$NavigationSimple;}function
isNavigationDual(){return$this->getNavigationMode()==Config::$NavigationDual;}function
isNavigationHover(){return$this->getNavigationMode()==Config::$NavigationHover;}function
isNavigationReversed(){return$this->getNavigationMode()==Config::$NavigationReversed;}function
getNavigationWidth(){$Wm=$this->getParameter("navigationWidth");if($Wm===null)return
null;return
min(max((float)$Wm,self::$NavigationWidthMin),self::$NavigationWidthMax);}function
isSelectionPreferred(){return($ra=$this->getParameter("preferSelection"))!==null?$ra:$this->config->isSelectionPreferred();}function
isRelationLinks(){return
isset($this->params["relationLinks"])?$this->params["relationLinks"]:$this->config->isRelationLinks();}function
getRecordsPerPage(){return($ra=$this->getParameter("recordsPerPage"))!==null?$ra:$this->config->getRecordsPerPage();}function
getEnumAsSelectThreshold(){$Y=$this->getParameter("enumAsSelectThreshold");if($Y<0)return
null;return$Y!==null?(int)$Y:$this->config->getEnumAsSelectThreshold();}}class
Hash{static
function
hkdf($v,$u,$jf="",$Rj=""){if(extension_loaded("hash")&&PHP_VERSION_ID>=70120)return
hash_hkdf("sha1",$u,$v,$jf,$Rj);if($Rj=="")$Rj=str_repeat("\0",20);$fj=self::hmacSha1($u,$Rj);$Gh="";for($Of="",$bb=1;!isset($Gh[$v-1]);$bb++){$Of=self::hmacSha1($Of.$jf.chr($bb),$fj);$Gh
.=$Of;}return
substr($Gh,0,$v);}static
function
hmacSha1($f,$u){if(!extension_loaded("hash"))return
hash_hmac("sha1",$f,$u,true);if(strlen($u)>64)$u=sha1($u,true);$u=str_pad($u,64,"\0");$yf=($u^str_repeat("\x36",64));$Qh=($u^str_repeat("\x5C",64));return
sha1($Qh.sha1($yf.$f,true),true);}}class
Random{static
function
strongKey(){return
strtr(rtrim(base64_encode(Random::bytes(32)),"="),"+/","-_");}static
function
bytes($v){if(PHP_VERSION_ID>=70000)return
random_bytes($v);$I=self::tryAlternatives($v);if($I!==false)return$I;$I=self::lastResortRandom($v);if($I!==false)return$I;throw
new
Exception("Error generating random bytes");}private
static
function
tryAlternatives($v){if(extension_loaded("libsodium"))return
\Sodium\randombytes_buf($v);$lm=DIRECTORY_SEPARATOR==="/";if($lm){$I=self::readDevUrandom($v);if($I!==false)return$I;}$fb=$lm&&PHP_VERSION_ID>50609&&PHP_VERSION_ID<50613;if(extension_loaded("mcrypt")&&!$fb){$I=mcrypt_create_iv($v,MCRYPT_DEV_URANDOM);if($I!==false)return$I;}$gb=PHP_VERSION_ID<50444||(PHP_VERSION_ID>50500&&PHP_VERSION_ID<50528)||(PHP_VERSION_ID>50600&&PHP_VERSION_ID<50612);if(extension_loaded("openssl")&&!$gb){$I=openssl_random_pseudo_bytes($v,$Uk);if($Uk)return$I;}return
false;}private
static
function
readDevUrandom($v){static$m=null;if($m===null)$m=@fopen("/dev/urandom","rb");if(!$m)return
false;$Bj=$v;$I="";do{$f=fread($m,$Bj);if($f===false)return
false;$Bj-=strlen($f);$I
.=$f;}while($Bj>0);return$I;}private
static
function
readCapicom($v){$Ib=new
\COM("CAPICOM.Utilities.1");$Bj=$v;$I="";do{$f=base64_decode((string)$Ib->GetRandom($v,0));$Bj-=strlen($f);$I
.=$f;}while($Bj>0);return$I;}private
static
function
lastResortRandom($v){static$u=null;static$Rj=null;if($u===null){$f=$_SERVER;$f[]=uniqid("",true);shuffle($f);$u=sha1(serialize($f),true);if(extension_loaded("openssl"))$Rj=openssl_random_pseudo_bytes(20);else{$Rj="";for($q=0;$q<20;$q++)$Rj
.=chr((mt_rand()^mt_rand())%256);}}else{if((ord($u)%2===0)===(ord($Rj)%2===0))$u=Hash::hmacSha1($u,$Rj);else$Rj=Hash::hmacSha1($Rj,$u);}return
Hash::hkdf($v,$u,"$v",$Rj);}}if(!function_exists("str_starts_with")){function
str_starts_with($Je,$ph){return
strpos($Je,$ph)===0;}}if(!function_exists("str_contains")){function
str_contains($Je,$ph){return
strpos($Je,$ph)!==false;}}if(!function_exists("password_verify")){function
password_verify($F,$Ie){return
false;}}if(!function_exists("ini_set")){function
ini_set($Wh,$Y){return
false;}}function
version(){return
VERSION;}function
idf_unescape($af){if(!preg_match('~^[`\'"[]~',$af))return$af;$ag=substr($af,-1);return
str_replace($ag.$ag,$ag,substr($af,1,-1));}function
q($Tk){return
Connection::get()->quote($Tk);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
number_type(){return'((^|[^o])int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes(array$Cm,$Vd=false){$J=[];foreach($Cm
as$u=>$X)$J[stripslashes($u)]=(is_array($X)?remove_slashes($X,$Vd):($Vd?$X:stripslashes($X)));return$J;}function
bracket_escape($af,$Ta=false){static$Vl=[':'=>':1',']'=>':2','['=>':3','"'=>':4'];return
strtr($af,($Ta?array_flip($Vl):$Vl));}function
min_version($Fm,$_g=null,$e=null){if(!$e)$e=Connection::get();if($_g&&$e->isMariaDB())$Fm=$_g;return$Fm&&$e->isMinVersion($Fm);}function
charset(Connection$e){return($e->isMinVersion("5.5.3")?"utf8mb4":"utf8");}function
link_files($A,array$Ud){switch($A){case'favicon-blue.ico':$n='favicon-blue-0f5ce53a66b1e25395d0048da369f19e__6bb95962.ico';break;case'favicon-green.ico':$n='favicon-green-def78cfa7c465c8b0e9966e3eb87407d__6bb95962.ico';break;case'favicon-orange.ico':$n='favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__6bb95962.ico';break;case'favicon-purple.ico':$n='favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__6bb95962.ico';break;case'favicon-red.ico':$n='favicon-red-c2ebb34a8df5aba28e15d87728a151df__6bb95962.ico';break;case'favicon-blue.svg':$n='favicon-blue-17e440832c1eac07527560a0d6f0d2ee__6bb95962.svg';break;case'favicon-green.svg':$n='favicon-green-bb254c95a033f67e3d433a3df63e160d__6bb95962.svg';break;case'favicon-orange.svg':$n='favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__6bb95962.svg';break;case'favicon-purple.svg':$n='favicon-purple-4cfd57d31ab991e8071fe34060cd3123__6bb95962.svg';break;case'favicon-red.svg':$n='favicon-red-a006e401273230fd6be80568c8361b57__6bb95962.svg';break;case'apple-touch-icon-blue.png':$n='apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__6bb95962.png';break;case'apple-touch-icon-green.png':$n='apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__6bb95962.png';break;case'apple-touch-icon-orange.png':$n='apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__6bb95962.png';break;case'apple-touch-icon-purple.png':$n='apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__6bb95962.png';break;case'apple-touch-icon-red.png':$n='apple-touch-icon-red-507228751d2170d047e72142d2c02390__6bb95962.png';break;case'logo.svg':$n='logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg';break;case'jush.css':$n='jush-b3a93b18444da26820ff61746521dede__d6435f99.css';break;case'jush-dark.css':$n='jush-dark-f8dac59c6ad1018686e52a0e0357e421__2ec7793c.css';break;case'jush.js':$n='jush-615bc0b9720a1de8edd2c6876a3495b6__42cd49bd.js';break;case'icons.svg':$n='icons-70163a2695280bf75edba563e7b5471b__2ec7793c.svg';break;case'default-blue.css':$n='default-blue-e7acfdb81453b86f081569afa115859e__9486a148.css';break;case'default-green.css':$n='default-green-268f042072e76ab07aa2fd0350e0aa0c__9486a148.css';break;case'default-orange.css':$n='default-orange-e4e5ea626cdcbe83e07c7933dc04f916__9486a148.css';break;case'default-purple.css':$n='default-purple-6b1de1f635d52b55797976fef486a515__9486a148.css';break;case'default-red.css':$n='default-red-0f424ea89c2a43c6eb0ec8f0e22362a5__9486a148.css';break;case'default-blue-dark.css':$n='default-blue-dark-1061ad7d216f143e3626560b92b66061__747ec25d.css';break;case'default-green-dark.css':$n='default-green-dark-6176b1c7b42ee9f244b3a9c3d8065728__747ec25d.css';break;case'default-orange-dark.css':$n='default-orange-dark-ef164a8d58dc89b2719f881377f73c89__747ec25d.css';break;case'default-purple-dark.css':$n='default-purple-dark-0f4fa03fa2d9287ef390780e90d9b06d__747ec25d.css';break;case'default-red-dark.css':$n='default-red-dark-3c1e28afe2cc92815bc7347df0d5776f__747ec25d.css';break;case'main.js':$n='main-0864f21d8576870afa2ea1b4d2bb6be8__bb1d1ac2.js';break;default:$n=null;break;}if(!$n)return
null;return
BASE_URL."?file=".urldecode($n);}function
ini_bool($Wh){$X=ini_get($Wh);return
preg_match('~^(on|true|yes)$~i',$X)||(int)$X;}function
ini_bytes($lf){$X=ini_get($lf);switch(strtolower(substr($X,-1))){case'g':$X=(int)$X*1024;case'm':$X=(int)$X*1024;case'k':$X=(int)$X*1024;}return$X;}function
max_input_vars($K,$mi){$Bg=(int)ini_get("max_input_vars");return($Bg?(int)floor(($Bg-$mi)/$K):0);}function
max_input_vars_error(){$lf="max_input_vars";return
lang(102,"$lf = ".(int)ini_get($lf));}function
sid(){static$J;if($J===null)$J=(session_id()&&!($_COOKIE&&ini_bool("session.use_cookies")));return$J;}function
save_driver_name($Sc,$N,$A){restart_session();$_SESSION["drivers"][$Sc][$N]=$A;stop_session();}function
get_driver_name($Sc,$N=null){return
isset($_SESSION["drivers"][$Sc][$N])?$_SESSION["drivers"][$Sc][$N]:Drivers::get($Sc);}function
save_login($Sc,$N,$V,$F,$h=""){$u=isset($_COOKIE["neo_key"])?$_COOKIE["neo_key"]:null;$_SESSION["pwds"][$Sc][$N][$V]=$u?[encrypt_string($F,$u)]:$F;$_SESSION["db"][$Sc][$N][$V][$h]=true;}function
delete_login($Sc,$N,$V){unset($_SESSION["pwds"][$Sc][$N][$V]);unset($_SESSION["db"][$Sc][$N][$V]);}function
get_password(){$F=get_session("pwds");if(is_array($F))return$_COOKIE["neo_key"]?decrypt_string($F[0],$_COOKIE["neo_key"]):false;return$F;}function
get_vals($H,$b=0){$J=[];$I=Connection::get()->query($H);if(is_object($I)){while($K=$I->fetchRow())$J[]=$K[$b];}return$J;}function
get_key_vals($H,$e=null,$xk=true){if(!$e)$e=Connection::get();$J=[];$I=$e->query($H);if(is_object($I)){while($K=$I->fetchRow()){if($xk)$J[$K[0]]=$K[1];else$J[]=$K[0];}}return$J;}function
get_rows($H,$e=null,$j="<p class='error'>"){if(!$e)$e=Connection::get();$J=[];$I=$e->query($H);if(is_object($I)){while($K=$I->fetchAssoc())$J[]=$K;}elseif(!$I&&!is_object($e)&&$j&&(defined("AdminNeo\PAGE_HEADER")||$j=="-- "))echo$j.error()."\n";return$J;}function
unique_array(array$K,array$t){foreach($t
as$s){if(!preg_match("~PRIMARY|UNIQUE~",$s["type"])&&!$s["partial"])continue;$im=[];foreach($s["columns"]as$u){if(!isset($K[$u]))continue
2;$im[$u]=$K[$u];}return$im;}return
null;}function
escape_key($u){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$u,$z))return$z[1].idf_escape(idf_unescape($z[2])).$z[3];return
idf_escape($u);}function
where($Z,$l=[]){$Rb=[];foreach((array)$Z["where"]as$u=>$X){$u=bracket_escape($u,true);$b=escape_key($u);$k=isset($l[$u])?$l[$u]:null;$Qd=isset($k["type"])?$k["type"]:null;$pe=isset($k["full_type"])?$k["full_type"]:null;$_f=$k&&(is_blob($k)||preg_match('~binary~',$Qd));if($_f&&!is_utf8($X))$Rb[]="$b = ".Driver::get()->quoteBinary($X);elseif(DIALECT=="sql"&&$Qd=="json")$Rb[]="$b = CAST(".q($X)." AS JSON)";elseif(DIALECT=="pgsql"&&preg_match('~^jsonb?$~',$pe))$Rb[]="$b::jsonb = ".q($X)."::jsonb";elseif(DIALECT=="sql"&&is_numeric($X)&&strpos($X,".")!==false)$Rb[]="$b LIKE ".q($X);elseif(DIALECT=="mssql"&&strpos($Qd,"datetime")===false)$Rb[]="$b LIKE ".q(preg_replace('~[_%[]~','[\0]',$X));else$Rb[]="$b = ".(isset($l[$u])?unconvert_field($l[$u],q($X)):q($X));if(DIALECT=="sql"&&preg_match('~char|text~',$Qd)&&preg_match("~[^ -@]~",$X))$Rb[]="$b = ".q($X)." COLLATE ".charset(Connection::get())."_bin";}foreach((array)$Z["null"]as$u)$Rb[]=escape_key($u)." IS NULL";return
implode(" AND ",$Rb);}function
where_columns($Z,$l=[]){$c=[];foreach((array)$Z["null"]as$u)$c[$u]=true;foreach((array)$Z["where"]as$u=>$X){$u=bracket_escape($u,true);foreach($l
as$A=>$k){if($u==$A||strpos($u,idf_escape($A))!==false)$c[$A]=true;}}return$c;}function
where_check($X,$l=[]){parse_str($X,$qb);remove_slashes([&$qb]);return
where($qb,$l);}function
where_link($q,$b,$Y,$Th="="){return"&where%5B$q%5D%5Bcol%5D=".urlencode($b)."&where%5B$q%5D%5Bop%5D=".urlencode(($Y!==null?$Th:"IS NULL"))."&where%5B$q%5D%5Bval%5D=".urlencode($Y);}function
convert_fields(array$c,array$l,array$M=[]){$I="";foreach($c
as$u=>$X){if($M&&!in_array(idf_escape($u),$M))continue;$La=convert_field($l[$u]);if($La)$I
.=", $La AS ".idf_escape($u);}return$I;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),[";"=>"%3B",","=>"%2C"]);}function
cookie($A,$Y,$kg=2592000){header("Set-Cookie: $A=".rawurlencode($Y).($kg?"; expires=".gmdate("D, d M Y H:i:s",time()+$kg)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_url($rm,$Yb){$J=@file_get_contents($rm,false,$Yb);if(function_exists('http_get_last_response_headers'))$http_response_header=($ra=http_get_last_response_headers())!==null?$ra:[];return[$J,isset($http_response_header)?$http_response_header:[]];}function
get_settings($bc="neo_settings"){parse_str(isset($_COOKIE[$bc])?$_COOKIE[$bc]:"",$O);return$O;}function
get_setting($u,$bc="neo_settings"){$O=get_settings($bc);return
isset($O[$u])?$O[$u]:null;}function
save_settings(array$O,$bc="neo_settings"){cookie($bc,http_build_query($O+get_settings($bc)));}function
restart_session(){if(!ini_bool("session.use_cookies")&&session_status()==PHP_SESSION_NONE)session_start();}function
stop_session($de=false){$vm=ini_bool("session.use_cookies");if(!$vm||$de){session_write_close();if($vm&&ini_set("session.use_cookies","0")===false)session_start();}}function&get_session($u){return$_SESSION[$u][DRIVER][SERVER][$_GET["username"]];}function
set_session($u,$X){$_SESSION[$u][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($Em,$N,$V,$h=null){$qm=remove_from_uri(implode("|",array_keys(Drivers::getList()))."|username|ext|".($h!==null?"db|":"").($Em=='mssql'||$Em=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$qm,$z);return"$z[1]?".(sid()?session_name()."=".urlencode(session_id())."&":"").urlencode($Em)."=".urlencode($N)."&".($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"")."username=".urlencode($V).($h!=""?"&db=".urlencode($h):"").($z[2]?"&$z[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($y,$Pg=null){if($Pg!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($y!==null?$y:$_SERVER["REQUEST_URI"]))][]=$Pg;}if($y!==null){if($y=="")$y=".";header("Location: $y");exit;}}function
query_redirect($H,$y,$Pg,$tj=true,$yd=true,$Id=false,$Jl=""){if($yd){$Pk=microtime(true);$Id=!Connection::get()->query($H);$Jl=format_time($Pk);}$Kk=$H?Admin::get()->formatMessageQuery($H,$Jl,$Id):"";if($Id){Admin::get()->addError(error().$Kk.script("initToggles();"));return
false;}if($tj)redirect($y,$Pg.$Kk);return
true;}function
queries_redirect($y,$Pg,$tj){$kj=implode("\n",Queries::$queries);$Jl=format_time(Queries::$start);return
query_redirect($kj,$y,$Pg,$tj,false,!$tj,$Jl);}class
Queries{static$queries=[];static$start=0.0;}function
queries($H){if(!Queries::$start)Queries::$start=microtime(true);if(support("sql")){Queries::$queries[]=(preg_match('~;$~',$H)?"DELIMITER ;;\n$H;\nDELIMITER ":$H).";";return
Connection::get()->query($H);}else{Queries::$queries[]=$H;return[];}}function
apply_queries($H,array$S,$td='AdminNeo\table'){foreach($S
as$Q){if(!queries("$H ".$td($Q)))return
false;}return
true;}function
format_time($Pk){return
lang(103,max(0,microtime(true)-$Pk));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($si=""){return
substr(preg_replace("~(?<=[?&])($si".(sid()?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($u,$tc=false,$_c=""){$m=$_FILES[$u];if(!$m)return
null;foreach($m
as$u=>$X)$m[$u]=(array)$X;$J='';foreach($m["error"]as$u=>$j){if($j)return$j;$A=$m["name"][$u];$Ql=$m["tmp_name"][$u];$Wb=file_get_contents($tc&&preg_match('~\.gz$~',$A)?"compress.zlib://$Ql":$Ql);if($tc){$Pk=substr($Wb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Pk))$Wb=iconv("utf-16","utf-8",$Wb);elseif($Pk=="\xEF\xBB\xBF")$Wb=substr($Wb,3);}if($_c){if(!preg_match("~$_c\\s*\$~",$Wb))$Wb
.=";";$Wb
.="\n\n";}$J
.=$Wb;}return$J;}function
upload_error($j){$Jg=($j==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($j?lang(104).($Jg?" ".lang(105,$Jg):""):lang(106));}function
repeat_pattern($Hi,$v){return
str_repeat("$Hi{0,65535}",$v/65535)."$Hi{0,".($v%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
format_number($X){return
strtr(number_format($X,0,".",lang(107)),preg_split('~~u',lang(108),-1,PREG_SPLIT_NO_EMPTY));}function
format_rows(array$R){$L=$R["Rows"];$Ia=($L&&(DIALECT=="sqlite"||(isset($R["Engine"])?$R["Engine"]:"")==(DIALECT=="pgsql"?"table":"InnoDB")));return($Ia?"~ ":"").format_number($L);}function
friendly_url($X){return
preg_replace('~\W~i','-',$X);}function
table_status1($Q,$Kd=false){$J=table_status($Q,$Kd);return($J?reset($J):["Name"=>$Q]);}function
column_foreign_keys($Q){$J=[];foreach(Admin::get()->getForeignKeys($Q)as$o){foreach($o["source"]as$X)$J[$X][]=$o;}return$J;}function
fields_from_edit(){$J=[];foreach((array)$_POST["field_keys"]as$u=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$u];$_POST["fields"][$X]=$_POST["field_vals"][$u];}}foreach((array)$_POST["fields"]as$u=>$X){$A=bracket_escape($u,true);$J[$A]=["field"=>$A,"full_type"=>"varchar","type"=>"varchar","privileges"=>["insert"=>1,"update"=>1,"where"=>1,"order"=>1],"null"=>true,"auto_increment"=>($u==Driver::get()->primary),];}return$J;}function
dump_headers($Ye,$gh=false){$Ye=friendly_url($Ye).date("-Ymd-His");$Ed=Admin::get()->sendDumpHeaders($Ye,$gh);$oi=$_POST["output"];if($oi!="text")header("Content-Disposition: attachment; filename=$Ye.$Ed".($oi!="file"&&preg_match('~^[0-9a-z]+$~',$oi)?".$oi":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$Ed;}function
dump_table_order(array$nh,array$zj){$Tf=array_flip($nh);$bi=[];$Nm=[];$lc=false;$Mm=function($A)use(&$Mm,&$bi,&$Nm,&$lc,$Tf,$zj){if(isset($bi[$A]))return;if(isset($Nm[$A])){$lc=true;return;}$Nm[$A]=true;foreach(isset($zj[$A])?$zj[$A]:[]as$xj){if(isset($Tf[$xj]))$Mm($xj);}unset($Nm[$A]);$bi[$A]=true;};foreach($nh
as$A)$Mm($A);return($lc?null:array_keys($bi));}function
dump_csv($K){$cm=$_POST["format"]=="tsv";foreach($K
as$u=>$X){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($cm?'\t':'[,;]|^$').'~',$X))$K[$u]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($cm?"\t":";")),$K)."\r\n";}function
apply_sql_function($p,$b){return($p?($p=="unixepoch"?"DATETIME($b, '$p')":($p=="count distinct"?"COUNT(DISTINCT ":strtoupper("$p("))."$b)"):$b);}function
get_temp_dir(){$Fi=ini_get("upload_tmp_dir");if(!$Fi)$Fi=sys_get_temp_dir();return$Fi;}function
open_file_with_lock($n){if(is_link($n))return
null;$m=@fopen($n,"c+");if(!$m)return
null;@chmod($n,0660);if(!flock($m,LOCK_EX)){fclose($m);return
null;}return$m;}function
write_and_unlock_file($m,$f){rewind($m);fwrite($m,$f);ftruncate($m,strlen($f));unlock_file($m);}function
unlock_file($m){flock($m,LOCK_UN);fclose($m);}function
first(array$Ka){return
reset($Ka);}function
get_private_key($dc){$n=get_temp_dir()."/adminneo.key";if(!$dc&&!file_exists($n))return
false;$m=open_file_with_lock($n);if(!$m)return
false;$u=stream_get_contents($m);if(!$u){$u=Random::strongKey();write_and_unlock_file($m,$u);}else
unlock_file($m);return$u;}function
get_random_string(){return
Random::strongKey();}function
select_value($X,$x,$k,$Fl){if(is_array($X)){$J="";if(array_filter($X,'is_array')==array_values($X)){$Pf=[];foreach($X
as$W)$Pf+=array_fill_keys(array_keys($W),null);foreach(array_keys($Pf)as$Kf)$J
.="<th>".h($Kf);foreach($X
as$W){$J
.="<tr>";foreach(array_merge($Pf,$W)as$_m)$J
.="<td>".select_value($_m,$x,$k,$Fl);}}else{foreach($X
as$Kf=>$W)$J
.="<tr>".($X!=array_values($X)?"<th>".h($Kf):"")."<td>".select_value($W,$x,$k,$Fl);}return"<table>$J</table>";}$Wj="";if($k&&$X!==null&&($Fl===null||strlen($X)<=$Fl)&&($Cm=Driver::get()->explodeArrayValue($X,$k["full_type"],$Wj))){$Vj=$k;$Vj["type"]=$Vj["full_type"]=$Wj;$J=select_array_value($Cm,$X,$x,$Vj,$Fl);return
Driver::get()->implodeArrayValues($J,$k["full_type"]);}if(!$x)$x=Admin::get()->getFieldValueLink($X,$k);if($k)$X=Connection::get()->formatValue($X,$k);$J=$k?Admin::get()->formatFieldValue($X,$k):$X;if($J!==null){if(!is_utf8($J))$J="\0";elseif($Fl!=""&&is_shortable($k))$J=truncate_utf8($J,max(0,+$Fl));else$J=h($J);}return
Admin::get()->formatSelectionValue($J,$x,$k,$X);}function
select_array_value(array$Cm,$X,$x,array$k,$Fl){$I=[];foreach($Cm
as$Y){if(is_array($Y))$I[]=select_array_value($Y,$X,$x,$k,$Fl);else{$Uf=preg_replace('~(where%5B\d+%5D%5Bval%5D=)'.preg_quote(urlencode($X),"~")."~",'${1}'.urlencode($Y),$x);$I[]=select_value($Y,$Uf,$k,$Fl);}}return$I;}function
is_blob(array$k){$fm=Driver::get()->getStructuredTypes();$U=lang(109);return
preg_match('~blob|bytea|raw|file'.(DIALECT=="mssql"?'|binary|image':'').'~',$k["type"])&&!in_array($k["type"],isset($fm[$U])?$fm[$U]:[]);}function
is_generated_always(array$k){return(isset($k["generated"])?$k["generated"]:"")!=""||stripos((string)(isset($k["default"])?$k["default"]:""),"GENERATED ALWAYS AS ")===0;}function
is_mail($Y){return
is_string($Y)&&filter_var($Y,FILTER_VALIDATE_EMAIL);}function
is_web_url($Y){if(!is_string($Y)||!preg_match('~^(https?:)?//~i',$Y))return
false;$Pb=parse_url($Y);if(!$Pb)return
false;$rm=$Y;if(isset($Pb['path'])){$ld=array_map('urlencode',explode('/',$Pb['path']));$rm=str_replace($Pb['path'],implode('/',$ld),$rm);}if(isset($Pb['query'])){parse_str($Pb['query'],$ti);$rm=str_replace($Pb['query'],http_build_query($ti),$rm);}if(!isset($Pb['scheme']))$rm="https:$rm";return(bool)filter_var($rm,FILTER_VALIDATE_URL);}function
is_shortable($k){return$k&&!preg_match('~'.number_type().'|date|time|year~',$k["type"]);}function
host_port($N){return(preg_match('~^(:([^:].*)|(\[(.+)]|(([^:]+://)?[^:]+))(:(\d+))?)$~',$N,$z)?[(isset($z[4])?$z[4]:"").(isset($z[5])?$z[5]:""),$z[2].(isset($z[8])?$z[8]:"")]:[$N,'']);}function
count_rows($Q,$Z,$Af,$ze){$H=" FROM ".table($Q).($Z?" WHERE ".implode(" AND ",$Z):"");return($Af&&(DIALECT=="sql"||count($ze)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$ze).")$H":"SELECT COUNT(*)".($Af?" FROM (SELECT 1$H GROUP BY ".implode(", ",$ze).") x":$H));}function
slow_query($H){$h=Admin::get()->getDatabase();$Kl=Admin::get()->getQueryTimeout();$Dk=Driver::get()->slowQuery($H,$Kl);$e=null;if(!$Dk&&support("kill")){$e=connect();if($e&&($h==""||$e->selectDatabase($h))){$Rf=number($e->getValue(connection_id()));echo'<script',nonce(),'>
	const timeout = setTimeout(() => {
		ajax(\'',js_escape(ME),'script=kill\', function() {
		}, \'kill=',$Rf,'&token=',get_token(),'\');
	}, ',1000*$Kl,');
</script>
';}}ob_flush();flush();$J=@get_key_vals(($Dk?:$H),$e,false);if($e){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$J;}function
get_token(){$pj=rand(1,1e6);return($pj^$_SESSION["token"]).":$pj";}function
verify_token(){list($Rl,$pj)=explode(":",$_POST["token"]);return($pj^$_SESSION["token"])==$Rl&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],["","same-origin"]);}function
script($Hk,$Ul="\n"){return"<script".nonce().">$Hk</script>$Ul";}function
script_src($rm,$xc=false){return"<script src='".h($rm)."'".nonce().($xc?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
input_hidden($A,$Y=""){return"<input type='hidden' name='".h($A)."' value='".h($Y)."'>";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Tk){if($Tk===null||$Tk==="")return"";return
str_replace(["&","<","\"","'","\0"],["&amp;","&lt;","&quot;","&#039;","&#0;"],$Tk);}function
truncate_utf8($Tk,$v=80){if($Tk=="")return"";if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$v).")($)?)u",$Tk,$z))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$v).")($)?)",$Tk,$z);return
h($z[1]).(isset($z[2])?"":"<i>…</i>");}function
icon_solo($r){return
icon($r,"solo");}function
icon_chevron_down(){return
icon("chevron-down","chevron");}function
icon_chevron_right(){return
icon("chevron-down","chevron-right");}function
icon($r,$yb=null){$r=h($r);return"<svg class='icon ic-$r $yb'><use href='".link_files("icons.svg",[])."#$r'/></svg>";}function
checkbox($A,$Y,$tb,$Vf="",$Oh="",$yb="",$Xf=""){$J="<input type='checkbox' name='$A' value='".h($Y)."'".($tb?" checked":"").($Xf?" aria-labelledby='$Xf'":"").">".($Oh?script("qsl('input').onclick = function () { $Oh };",""):"");return($Vf!=""||$yb?"<label".($yb?" class='$yb'":"").">$J".h($Vf)."</label>":$J);}function
optionlist($C,$gk=null,$wm=false){$J="";foreach($C
as$Kf=>$W){$Yh=[$Kf=>$W];if(is_array($W)){$J
.='<optgroup label="'.h($Kf).'">';$Yh=$W;}foreach($Yh
as$u=>$X)$J
.='<option'.($wm||is_string($u)?' value="'.h($u).'"':'').($gk!==null&&($wm||is_string($u)?(string)$u:$X)===$gk?' selected':'').'>'.h($X);if(is_array($W))$J
.='</optgroup>';}return$J;}function
html_select($A,$C,$Y="",$Nh="",$Xf="",$wm=false){static$Vf=0;$Wf="";if(!$Xf&&substr(isset($C[""])?$C[""]:"",0,1)=="("){$Vf++;$Xf="label-$Vf";$Wf="<option value='' id='$Xf'>".h($C[""]);unset($C[""]);}return"<select name='".h($A)."'".($Xf?" aria-labelledby='$Xf'":"").">".$Wf.optionlist($C,$Y,$wm)."</select>".($Nh?script("qsl('select').onchange = function () { $Nh };",""):"");}function
html_radios($A,$C,$Y=""){$I="<span class='labels'>";foreach($C
as$u=>$X)$I
.="<label><input type='radio' name='".h($A)."' value='".h($u)."'".($u==$Y?" checked":"").">".h($X)."</label>";$I
.="</span>";return$I;}function
confirm($Pg="",$ik="qsl('input')"){return
script("$ik.onclick = () => confirm('".js_escape($Pg?:lang(110))."');","");}function
print_fieldset_start($r,$gg,$Xe,$Km=false,$Fk=false){echo"<fieldset id='fieldset-$r' class='closable ".(!$Km?" closed":"")."'>","<legend><a href='#'>$gg</a></legend>",icon($Xe,"fieldset-icon jsonly"),"<div class='fieldset-content".($Fk?" sortable":"")."'>";}function
print_fieldset_end($r,$Fk=false){echo"</div>",script("initFieldset('$r');","");if($Fk)echo
script("initSortable('#fieldset-$r .fieldset-content');","");echo"</fieldset>\n";}function
bold($cb,$yb=""){return($cb?" class='$yb active'":($yb?" class='$yb'":""));}function
js_escape($Tk){return
str_replace("<","\\x3C",addcslashes($Tk,"\r\n'\\"));}function
js_escape_key($Tk){return'"'.str_replace("<","\\x3C",addcslashes($Tk,"\r\n\t\"\\")).'"';}function
js_escape_re($Tk){return
addcslashes(preg_quote($Tk,"/"),"\r\n");}function
pagination($E,$ic){return"<li>".($E==$ic?"<strong>".($E+1)."</strong>":'<a href="'.h(remove_from_uri("page").($E?"&page=$E".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($E+1)."</a>")."</li>";}function
print_hidden_fields(array$gj,array$bf=[],$Xi=""){$I=false;foreach($gj
as$u=>$X){if(!in_array($u,$bf)){if(is_array($X))print_hidden_fields($X,[],$u);else{$I=true;echo
input_hidden($Xi?$Xi."[$u]":$u,$X);}}}return$I;}function
hidden_fields_get(){if(sid())echo
input_hidden(session_name(),session_id());if(SERVER!==null)echo
input_hidden(DRIVER,SERVER);echo
input_hidden("username",$_GET["username"]);}function
enum_input($Ma,array$k,$Y,$jd=null,$sb=false){preg_match_all("~'((?:[^']|'')*)'~",$k["length"],$_);$Cm=$_[1];$Il=Admin::get()->getSettings()->getEnumAsSelectThreshold();$M=!$sb&&$Il!==null&&count($Cm)>$Il;$U=$sb?"checkbox":"radio";$xa=$M?"selected":"checked";$I=$M?"<select $Ma>":"<span class='labels'>";if($M&&$k["null"]&&$jd!==""){$tb=$Y===null?$xa:"";$I
.="<option value='__adminneo_empty__' disabled $tb></option>";}if($jd!==null){$tb=(is_array($Y)?in_array($jd,$Y):$Y===$jd)?$xa:"";if($M)$I
.="<option value='$jd' $tb>".lang(111)."</option>";else$I
.="<label><input type='$U' $Ma value='$jd' $tb><i>".lang(111)."</i></label>";}foreach($Cm
as$X){if($jd===""&&$X==="")continue;$X=stripcslashes(str_replace("''","'",$X));$tb=is_array($Y)?in_array($X,$Y):$Y===$X;$tb=$tb?$xa:"";$ke=$X===""?("<i>".lang(111)."</i>"):h(Admin::get()->formatFieldValue($X,$k));if($M)$I
.="<option value='".h($X)."' $tb>$ke</option>";else$I
.=" <label><input type='$U' $Ma value='".h($X)."' $tb>$ke</label>";}$I
.=$M?"</select>":"</span>";return$I;}function
input($k,$Y,$p,$Qa=false){$A=h(bracket_escape($k["field"]));$fm=Driver::get()->getTypes();$Bf=isset($k["full_type"])&&Admin::get()->detectJson($k["full_type"],$Y,true);$Dj=(DIALECT=="mssql"&&$k["auto_increment"]&&!$_POST["clone"]);if($Dj&&!$_POST["save"])$p=null;if(in_array($k["type"],Driver::get()->getUserTypes())){$qd=type_values($fm[$k["type"]]);if($qd){$k["type"]="enum";$k["length"]=$qd;}}$Ma=" name='fields[$A]' ".($Qa?" autofocus":"");$re=(isset($_GET["select"])||$Dj?["orig"=>lang(112)]:[])+Admin::get()->getFieldFunctions($k);$He=(in_array($p,$re)||isset($re[$p]));echo"<td class='function'>",Driver::get()->getUnconvertFunction($k)." ";if(count($re)>1){$gk=$p===null||$He?$p:"";echo"<select name='function[$A]'>".optionlist($re,$gk)."</select>",help_script_command("value.replace(/^SQL\$/, '')",true),script("qsl('select').onchange = functionChange;","");}else
echo
h(reset($re));echo"</td><td>";$mf=Admin::get()->getFieldInput(isset($_GET["edit"])?$_GET["edit"]:null,$k,$Ma,$Y,$p);if($mf!="")echo$mf;elseif(preg_match('~bool~',$k["type"]))echo"<input type='hidden'$Ma value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked":"")."$Ma value='1'>";elseif($k["type"]=="enum")echo
enum_input($Ma,$k,$Y);elseif($k["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$k["length"],$_);echo"<span class='labels'>";foreach($_[1]as$X){$X=stripcslashes(str_replace("''","'",$X));$tb=$Y!==null&&in_array($X,explode(",",$Y),true);$tb=$tb?"checked":"";$ke=$X===""?("<i>".lang(111)."</i>"):h(Admin::get()->formatFieldValue($X,$k));echo" <label><input type='checkbox' name='fields[$A][]' value='".h($X)."' $tb>$ke</label>";}echo"</span>";}elseif(is_blob($k)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$A'>";elseif($Bf)echo"<textarea $Ma cols='50' rows='12' class='jush-json'>".h($Y).'</textarea>';elseif(($Dl=preg_match('~text|lob|memo|json~i',$k["type"]))||preg_match("~\n~",$Y)){if($Dl&&DIALECT!="sqlite")$Ma
.=" cols='50' rows='12'";else{$L=min(12,substr_count($Y,"\n")+1);$Ma
.=" cols='30' rows='$L'";}echo"<textarea $Ma>".h($Y).'</textarea>';}else{$Lg=!preg_match('~int~',$k["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$k["length"],$z)?((preg_match("~binary~",$k["type"])?2:1)*$z[1]+($z[3]?1:0)+($z[2]&&!$k["unsigned"]?1:0)):($fm&&$fm[$k["type"]]?$fm[$k["type"]]+($k["unsigned"]?0:1):0);if(DIALECT=='sql'&&Connection::get()->isMinVersion("5.6")&&preg_match('~time~',$k["type"]))$Lg+=7;echo"<input class='input'".((!$He||$p==="")&&preg_match('~(?<!o)int(?!er)~',$k["type"])&&!preg_match('~\[]~',$k["full_type"])?" type='number'":"").($p!="now"?" value='".h($Y)."'":" data-last-value='".h($Y)."'").($Lg?" data-maxlength='$Lg'":"").(preg_match('~char|binary~',$k["type"])&&$Lg>20?" size='44'":"")."$Ma>";}$Oe=Admin::get()->getFieldInputHint($_GET["edit"],$k,$Y);if($Oe!="")echo" <span class='input-hint'>$Oe</span>";if(count($re)>1)echo
script("qs('select', qsl('td').previousSibling).onchange(null, true);","");$Zd=0;foreach($re
as$u=>$X){if($u===""||!$X)break;$Zd++;}if(count($re)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $Zd);");}function
process_input($k){if(is_generated_always($k))return
null;$af=bracket_escape($k["field"]);$p=isset($_POST["function"][$af])?$_POST["function"][$af]:"";if($p=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?idf_escape($k["field"]):false);if($p=="NULL")return
Driver::get()->getNull();if(is_blob($k)&&ini_bool("file_uploads")){$m=get_file("fields-$af");if(!is_string($m))return
false;return
Driver::get()->quoteBinary($m);}$Y=isset($_POST["fields"][$af])?$_POST["fields"][$af]:(isset($_FILES["fields"]["name"][$af])?$_FILES["fields"]["name"][$af]:null);if($Y===null)return
false;if($k["auto_increment"]&&$Y=="")return
null;if($k["type"]=="set")$Y=implode(",",(array)$Y);if($p=="json"){$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}return
Admin::get()->processFieldInput($k,$Y,$p);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$Ij=$sd=[];foreach(table_status("",true)as$Q=>$R){$ol=Admin::get()->getTableName($R);if(!isset($R["Engine"])||$ol==""||($_POST["tables"]&&!in_array($Q,$_POST["tables"])))continue;$I=Connection::get()->query("SELECT".limit("1 FROM ".table($Q)," WHERE ".implode(" AND ",Admin::get()->processSelectionSearch(fields($Q),[])),1));if($I&&!$I->fetchRow())continue;$x=h(ME."select=".urlencode($Q)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]));if($I)$Ij[]="<li><a href='$x'>".icon("search")."$ol</a></li>";else$sd[]="<div class='error'><a href='$x'>$ol</a>: ".error()."</div>";}if($Ij)echo"<ul class='links'>\n",implode("\n",$Ij),"</ul>\n";if($sd)echo
implode("\n",$sd),"\n";if(!$Ij&&!$sd)echo"<p class='message'>".lang(79)."</p>\n";}function
help_script($Dl,$Ak=false){return
script("initHelpFor(qsl('select, input'), '".h($Dl)."', $Ak);","");}function
help_script_command($Jb,$Ak=false){return
script("initHelpFor(qsl('select, input'), (value) => { return $Jb; }, $Ak);","");}function
edit_form($Q,$l,$K,$pm){$ol=Admin::get()->getTableName(table_status1($Q,true));$T=$pm?lang(39):lang(113);page_header("$T: $ol",["select"=>[$Q,$ol],$T]);if($K===false){echo"<p class='error'>".lang(90)."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$fd=false;$Vm=($pm&&!isset($_GET["select"])?where_columns($_GET,$l):[]);$Zb=(count($Vm)!=count($l));if(!$Zb)$Vm=[];if(!$l)echo"<p class='error'>".lang(114)."\n";else{echo"<table class='box'>".script("qsl('table').onkeydown = onEditingKeydown;");$Qa=!$_POST;foreach($l
as$A=>$k){echo"<tr".(isset($Vm[$A])?" class='where-column'":"")."><th>".Admin::get()->getFieldName($k);$u=bracket_escape($A);$i=isset($_GET["preset"][$u])?$_GET["preset"][$u]:null;if($i===null){$i=$k["default"];if($k["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$i,$Aj))$i=$Aj[1];if(DIALECT=="sql"&&preg_match('~binary~',$k["type"]))$i=bin2hex($i);}$Y=($K!==null?($K[$A]!=""&&DIALECT=="sql"&&preg_match("~enum|set~",$k["type"])&&is_array($K[$A])?implode(",",$K[$A]):(is_bool($K[$A])?+$K[$A]:$K[$A])):(!$pm&&$k["auto_increment"]?"":(isset($_GET["select"])?false:$i)));if(!$_POST["save"]&&is_string($Y))$Y=Admin::get()->formatFieldValue($Y,$k);if(($pm&&!isset($k["privileges"]["update"]))||is_generated_always($k)){echo"<td class='function'></td><td>";if($pm||!$k["generated"])echo
select_value($Y,'',$k,null);else
echo"<code class='jush-".DIALECT."'>",h($Y),"</code>";echo"</td>";}else{$fd=true;$p=($_POST["save"]?isset($_POST["function"][$u])?$_POST["function"][$u]:"":($pm&&preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$pm&&$Y==$k["default"]&&preg_match('~^[\w.]+\(~',$Y))$p="SQL";if(preg_match("~time~",$k["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$p="now";}if($k["type"]=="uuid"&&$Y=="uuid()"){$Y="";$p="uuid";}if($Qa!==false)$Qa=($k["auto_increment"]||$p=="now"||$p=="uuid"?null:true);input($k,$Y,$p,(bool)$Qa);if($Qa)$Qa=false;}echo"\n";}if(!support("table")&&!fields($Q))echo"<tr>"."<th><input class='input' name='field_keys[]'>".script("qsl('input').oninput = fieldChange;","")."<td class='function'>".html_select("field_funs[]",Admin::get()->getFieldFunctions(["null"=>isset($_GET["select"])]))."<td><input class='input' name='field_vals[]'>"."\n";echo"</table>\n",script("initToggles(gid('form'));");if($Vm)echo
script("initWhereChange();");}echo"<p>";if($fd){echo"<input type='submit' class='button default' value='".lang(115)."'>\n";if(!isset($_GET["select"])&&$Zb){$Ic=($Vm&&Admin::get()->getErrors()?" disabled":"");echo"<input type='submit' class='button' name='insert' value='".($pm?lang(116):lang(117))."' title='Ctrl+Shift+Enter'$Ic>\n",($pm?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".js_escape(lang(118))."', this); };"):"");}}echo($pm?"<input type='submit' class='button' name='delete' value='".lang(119)."'>".confirm()."\n":"");if(isset($_GET["select"]))print_hidden_fields(["check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]]);echo
input_hidden("referer",isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),input_hidden("save","1"),input_token(),"</form>\n";}function
file_upload_form_script($he,$nf){$Dg=ini_get("max_file_uploads");$Jg=ini_get("upload_max_filesize");$Kg=ini_bytes("upload_max_filesize");return
script("initFilesUploadForm('".js_escape($he)."', '".js_escape($nf)."', "."$Dg, '".js_escape(lang(120,$Dg,"'max_file_uploads'"))."', "."$Kg, '".js_escape(lang(121,$Jg,"'upload_max_filesize'"))."')");}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($Tk){$Fa=array_flip(str_split(compress_alphabet()));$v=strlen($Tk);$Bm=($v?13*($v-1)/2-$Fa[$Tk[0]]:0);$Ya="";$Gj=0;$Hj=0;for($q=1;$q<$v;$q+=2){$Gj=($Gj<<13)+$Fa[$Tk[$q]]*93+$Fa[$Tk[$q+1]];$Hj+=13;while($Hj>=8&&$Bm>=8){$Hj-=8;$Bm-=8;$Ya
.=chr($Gj>>$Hj);$Gj&=(1<<$Hj)-1;}}if($Ya=="")return"";return
function_exists('gzinflate')?gzinflate($Ya):inflate($Ya);}function
inflate($Ya){$hg=[3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258];$ig=[0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0];$Lc=[1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577];$Nc=[0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13];$J="";$G=0;do{$Xd=inflate_bits($Ya,$G,1);$U=inflate_bits($Ya,$G,2);if(!$U){$G=($G+7)&~7;$v=inflate_bits($Ya,$G,16);$G+=16;$J
.=substr($Ya,$G>>3,$v);$G+=$v<<3;}else{if($U==1){$ug=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$Oc=array_fill(0,30,5);}else{$tg=inflate_bits($Ya,$G,5)+257;$Mc=inflate_bits($Ya,$G,5)+1;$D=[16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15];$Vg=array_fill(0,19,0);$Ug=inflate_bits($Ya,$G,4)+4;for($q=0;$q<$Ug;$q++)$Vg[$D[$q]]=inflate_bits($Ya,$G,3);$Wg=inflate_table($Vg);$jg=[];while(count($jg)<$tg+$Mc){$el=inflate_symbol($Ya,$G,$Wg);if($el==16)$jg=array_merge($jg,array_fill(0,inflate_bits($Ya,$G,2)+3,end($jg)));elseif($el==17)$jg=array_merge($jg,array_fill(0,inflate_bits($Ya,$G,3)+3,0));elseif($el==18)$jg=array_merge($jg,array_fill(0,inflate_bits($Ya,$G,7)+11,0));else$jg[]=$el;}$ug=array_slice($jg,0,$tg);$Oc=array_slice($jg,$tg);}$vg=inflate_table($ug);$Qc=inflate_table($Oc);while(($el=inflate_symbol($Ya,$G,$vg))!=256){if($el<256)$J
.=chr($el);else{$v=$hg[$el-257]+inflate_bits($Ya,$G,$ig[$el-257]);$Pc=inflate_symbol($Ya,$G,$Qc);$Ch=strlen($J)-$Lc[$Pc]-inflate_bits($Ya,$G,$Nc[$Pc]);for($q=0;$q<$v;$q++)$J
.=$J[$Ch+$q];}}}}while(!$Xd);return$J;}function
inflate_bits($Ya,&$G,$cc){$J=0;for($q=0;$q<$cc;$q++){$J+=((ord($Ya[$G>>3])>>($G&7))&1)<<$q;$G++;}return$J;}function
inflate_table(array$jg){$Q=[];$zb=0;for($Za=1;$Za<=max($jg);$Za++){foreach($jg
as$el=>$v){if($v==$Za){$Q[$Za][$zb]=$el;$zb++;}}$zb<<=1;}return$Q;}function
inflate_symbol($Ya,&$G,array$Q){$zb=0;$Za=0;do{$zb=($zb<<1)+inflate_bits($Ya,$G,1);$Za++;}while(!isset($Q[$Za][$zb]));return$Q[$Za][$zb];}if(isset($_GET["file"]))load_compiled_file($_GET["file"]);function
load_compiled_file($n){if($n==""){http_response_code(404);exit;}if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){http_response_code(304);exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");ini_set("zlib.output_compression","1");$Ed=pathinfo($n,PATHINFO_EXTENSION);switch($Ed){case"css":header("Content-Type: text/css; charset=utf-8");break;case"js":header("Content-Type: text/javascript; charset=utf-8");break;case"ico":header("Content-Type: image/x-icon");break;case"png":header("Content-Type: image/png");break;case"svg":header("Content-Type: image/svg+xml");break;}switch($n){case'favicon-blue-0f5ce53a66b1e25395d0048da369f19e__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGZowEUFhM2G6A4QAJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+cUn3+6qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9jompoYUe3hg9tFCL+dNX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryEWPJCE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gDMBOIUjRp9w0AAAAASUVORK5CYII=';break;case'favicon-green-def78cfa7c465c8b0e9966e3eb87407d__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC+AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYVJREFUeNpiYOhiAFBfBxBohGEcxs/RmgQQEQDbQAACBAFCmBDgogLCZkN0BwiATBZQZNsAgmwAgyMAuRZCAtvkGLTy7sEHcFbvfWT4AbjH8f75Huq/CXBRgY8lQuzx29gjxBI+KnBtBDxFgJ+QO/1AgKw24AW+Q1La4rkm4DPEkk+agCvEkqsmQKxSBGwhlkSagA7Eko72DNs4QZRO8NIOUQk+1pAbreGjlGaI3ibENNDFEO+MIbpoJHz0jfYKRngCRymLEUQXABzQRxHOjYro4wBJGwAAEaYYoIeXRg8DTBHZ2oHo0TvwGmLJK01ADnNISnPkFAEA2jhC7nSEl2YHmnAMF1VMsEEMAQDE2GCCKlw4RlMT8Ad1OAnyeGbk4SSo46I9wzPGKMC5UwFjnLVneIEYMWZo/SOmgBZmiCGA7g98hSSIscM3Y4cYkuCLJsCDWOJpAjL4CEnpAzLaHXAxhSi9h2vjZVTDCnKjFWqw/jYsI8ACIX4ZIRYIUMbfEW3maO8YAIxSqCXQN5/tAAAAAElFTkSuQmCC';break;case'favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRATANhCAAEGAEGbIwEUFhM2G6A4QAJksoMi2AQTZAAZHAEsthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9fO1dVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwAv8gqS0xXNNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwMddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyBV7BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4FsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8CO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09ALYCTFLvUfsXAAAAAElFTkSuQmCC';break;case'favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGYIcFEBYbMhugM0AJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+e8Ln66qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9iompoYUe3hg9tFCL+dOX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryAWPJcE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gD0xZAYUYkFLAAAAAASUVORK5CYII=';break;case'favicon-red-c2ebb34a8df5aba28e15d87728a151df__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRAQY20AAAgQBQpghwEUFhM2G6A4QAJksoMi2AQTZBhgcAUgthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9eOZdVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwEv8gqS0xQtNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwKddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyB17BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4HsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8DO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09AE8YAEN5XivhAAAAAElFTkSuQmCC';break;case'favicon-blue-17e440832c1eac07527560a0d6f0d2ee__6bb95962.svg':$f='+<bATb3V?$so%el,wEIwK&mlYjGZ$a8-HGs8y$j)-UBmQx`Cf?>]C6?xmhS1<w
ZNSJl63"aZ]nB<rgk|tG)vC,pHYx;Wb2NVXBMxd!lQ=g0"!mM[]c*SW?7
E^]B7t[fHolNczfsymCW
CM~8Ult[(brlOx`71nDc
H;N~ybgNd*l6LMbRk;>%06rQeBnDc14r_7Z0b9uqO=xV5=22d05hr#V]F`V>ZD,#JA9[$XEV-*TBfz7Z%
rEJw!G!cT+[7>-u:iVU)N$iv%ySN.`._&}sV@FUIuH)%cY-0#OXWPT8t./3Oid8~R]3?9n;/Qs
Y2!K`1Q9d
tys6C=xmJfXFCT%0xl`H&%njK7`N[.q6jET6kV
VqmiJoIrZ#rsP~q0vDN<FH1w9l4R-?A#H:#onn0@0]3dNk3,E{<7r;2q
u)F!d(nhskXC~JT4N!~!g52r#`3hF[%j
oPE~ZV(W_~g#t?WXQCxEe7)ZQKGxei4.gu_R>pAL]HDZf!uSL)$_)^vZ:Xk![_HKh=C|S~PjqHcgjUutq#-~?PmA#<MYyg2R';break;case'favicon-green-bb254c95a033f67e3d433a3df63e160d__6bb95962.svg':$f='&<bATb3V?$so%eo_t_hLePj9EjS(Ic^3}i[Os!U@i%X`9w9h7
[@
I:KPV|A4v9o?tG^4G8Kl/Za|j!Q;%pO#+U]tOQ@f!m@5m(iU+Xd
:mOOX[.7/V%^=Dj0`KWYnDy%^,cfeZsrK*5P&I[0
$m~<+JorKkYLM7yy$Aw$[w/57.-wX^0JIMH;`
EW/c}a9c

eAhG[68u92FB)
ToD/k0uYFG=PN>>d,^BJ&icL,/i,2N?:udHuPgxk>l07BF^82,>cspK:`^P,q[%?5=4<`h.?.kJ"X_a_mu|Z>-SL,/k)gDk#g):;if"T:5u9
?>JfFYF#KP)D%8xDljc2l^mJ<^%4+Iu=v?FB.Y^67X>9:@KMfCyqQL.-Kmn#DsAler%=^*ps8vI)CEG%8
bknN$znn
D-t3DO&3,C5<7r@2q],)F!d(nhsf)H-JU7?!^"9+Xtpm.q8>NCVHgU$kcVodiJa6[qLov!)IR#}x3*ku*P%3jGe?w?dILBVvhJ~,bFMpBcD%CK/#{0d-|Mu[Pn{fe.3aFbOf6.%,.opHOIuO9';break;case'favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__6bb95962.svg':$f='&<bATb3V?$so%el,wEIwL!M$a_R(Ic^2cYP0
-+_=VtkTnq5G
]9Lh@D6K6GH(sVGneM;s+raV;M^D!*+KY*IOa=3Z`PmB;9_2Obq7yBg2Z%>bw,v+Y%P7|fhq*F156s0beWd,6`ay=p@_gN3Z=jciDmX_i72]oyww{Z~&Ut4M]*axgl)b!a1ju/uH<*9Xo
=
Ul9*$m_7{*MB>d@6aDhpC0Rt_1D-,/u//bLNxN!Dc[m?bh@-`9rmyGW2b(F%J[Qafv1UZr>a>F6JA8g[!0b@[fJtmTv4e%TYc-0#j"5iK2z=wl
*E@MT<:>g<H|W|2(rwB
g5j1,D7hGqj!`uuvXM9_hJa7+NN~VgB]NK[T3a!z#1A}OTmcvh["nTvNeTw$$;eT6q?q1?CDM!=<1SnN]QTt9lj&eNk(4c>}6hisx;^`kO(cefsRGicdC|"j9npLdO:cM^+&QT[{IL-Wu]3@7yXoO46wh/&9Sd+Vo~(7pzhcG()N3^n#?6MAX=Pd
]uX2I0XtRaah;-u5eKzydbLJw$UJhJ]
fe:Fe6mt>cu';break;case'favicon-purple-4cfd57d31ab991e8071fe34060cd3123__6bb95962.svg':$f='+<bAU6+V?$so%eoa6[DcHOAP_Cx(^^F;2nJOs!U09RM?Tw1h7?fDMh@D6K6GH(sW:c#bW`9DTcKpCf,1a1E%kD+5q+F]4B,8UB|ML]o,4O$F:-Sutmd.1l:6ly,?7MjE>rM=td#VYm/o}bPN3Z=AH2GFCrK4TIrcvu65o!8H3d"9V^}B7s3mnUM&Wx87ya:7X
mAh1Y7Wu72FB8FRoD/k0uYFG=PV;h%?KNUP%#GQ^
2w"
SkNgvI0*kTGqx))i0|m-597vz$R_y_,e<pwj=Y`"7[1E)$2(fp:Ex.$(:Op<o1CeTD_yBV
#FT:r
y-y?.@i`K.+Yg?j_Hk`_>f$l~LS;5RSh?>{b"
&+evz;:0eol+,pv/]hQo|q*A;p{F69^H{
F#X:3c%4:=Hdpu{k&lMXV@E/#GL#?#)e-WHQiGP@.%eYsSRXBg!n9tO*z&WP*$Up~J1]E18NCC{!iER:u`UBQ5(*#viS-0S(llO)OsT0bALBjGk4Bt}3Hwkvp!81Fs-^5[}2H/0[(gXX++<t3ZZ577
3fS]`cB;^5uWM3tX';break;case'favicon-red-a006e401273230fd6be80568c8361b57__6bb95962.svg':$f='+<bAU6+V?$so%eoa6[DcEe<SKeo.[BnWu^_0
-+j@96@+X_5GA4^m3%R;yn_USCF5vXi6B6jvyvvy?qZYfND@5~KR9wPw1q,+w{:cwGa2aY)<GPqWy/nLYzy>c3Au_/MA7,dc
}`rf-`x<FH$&bI]FGspJPra.)yE]$w~aKaM]on_y4%i2=`y?0`vYw6}rUy&B3JD1F
/B
o8ro30<1Tp4r,.qWnFpndQQq#ek`C+.f19/9#q+uNg4bc6H.9(02NJtu){yYINu`Uzs:%?o1GH"Lgtxvhu>9uq8)/:th8&TH,OT*]5<Ydlap!w8k>zUUDvh@h+)F+>T8=R`(;8*p2Il^<$eSAzo6lU8L_P_Yh-i#lD(4lV7"52"_UdLUTuCV+Yf/[^)f(~i
.,!Hkau}dsr
i84
>s)_:!#hJQ9:ex&|].#nB#/g7`Ds1xz$U+"f!p-.*YXigq8vUBF!9lVoojveL
+8ox,X;hOaXS*cTW+ODnn.]r,!3BBNvdJ~,brQumcAQJa9E)x*rt!$x~u(xCb
69OF`xI}1->oD?M:yg2R';break;case'apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__6bb95962.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAK7UlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUa502RxeG0bX17btm3b/tu2bdu2PbZt21bY+6xqPduTSSddSd1zPyTdVZk+p5+prr51f7d83ixVOXXH55Yte6No2r25Qy/O/Pg7OB/4ykFO0cC/4FBma6qq2Tsmb+SVGe9/7f86zWhMFx+HQ5mjs7XmwITMT77LXe+R04WOdFdw+Ka1xOzL7vML7rTLTnd+RMHhW+Z01Owfz911izOE8IMKDl8wh6W9dPHLbsFCOD/Izyo4pB8z3EyG4GPJK5rTqeCQ2HgEGECGeL5MVHBIPAM1CAvh/AkFh5Rvrf/13STr0x/UHpmO4yXzn+7+3pev+VA0puN/fX/hDyk4fOSBkjPg96KN09KRN/KqbuBoSzsnGtNRz8NFwSFBDJRAeDdwCOsqS8/86Nu9gYP4mK25WsGBaXVNlpS8plORFeuP5k/ZkPbVnLj3J0e9Nib8uWEhj/QPvPvz8zd/eAa/54vzfOUgp2hAs6kb0uhCR7rXN1s0I42AN7dNDxxYY/CG3sCB1+wb66dwNLVaQ5Jqlu/L/XxWLPf+hvdOu8X5qS9mxS7fnxuaVNvcZtXcaiyI6IeDN9KyFW/3Bg6eTf4FR1p+0/ydWS+ODL3xfe6lsc6feGlk6IKdWWkFzW5Za+WG6YNDzF5bWIx1GQ7cUpXr+3DklLQs3pP9zNBg7plX/NmhIUv25OSWuv4KwFK7TjhsjZXic2dRQsYH3/h3OFLP6oSDP+rLcDAV6DM3jttjEu83P961gYRUDJ1wVGz8wtZYIb7Wn136b41bk0/phKNs+Zu+CUd6QbPAwmzeb158ZlHPECFVRy8cGz6nsdNuE5OP0kUv/gscCUd0wlE07T5fg4PFgQ3HCni5MCEWwrm8zScKNd3G7EEnHDX7xnGkes9occTe3pgz8I//HgDVAUfu0Et8Cg5eQxi6xT0wuQ9amNDSbtN0GBEOnXDUn1nytxEi6aQ42JEbIRo3hW/XCQfRDt+Bw2JzvDk+QgoshL89MdJqc7gRjqawrRzBs774ibWuWByvOzabg3hj4Fp/hGPaxnSJsBA+a2uGGx8rLXGHxPHCSXc47Vax0F8853EO1p2c73ePFeJO0mEhPDKtzl0T0raUM+I4XrVjiDhlb67J7vdrQp9+NyGdKuGwIXzWlgx3vcoS4/q3s4wl4mx7RkDlxi/97lWWuKe8cLw6JtzFIJgOOLI++6G1pkA06CyI9bsgmKRYiDdbF8PnOuDAC8bf7LRZRBuZwucKjps+OKNn4c1lOPDKLf1EG19beFNwsGTfGzjw5ug9oplPLdkrOEj2IfDQGzgyP/2+EMr6UrKPgkOkCboOB54/5jqntVP+NEEFh0sJxuQPc6QbL5n/jN8lGN/+8Vl54bjrs3NKmmCgfTg1Wl44Pp0Zo0RNBtri3dnywkE2q5JDGmikz4gcDrn81o/OksjodSE1ZPiykHrt4XwZ4dh4vMA1OTXPF3c+TcSY4ZNwOJzOT6bHyEUG2ginUxVv8Yi1d9oHL06UhYzhS5M6uuzuKPs00dWyTxP9q+wT/4hrDuUhGzF1YOP9M0jl3CuWJODdvUpWOM1oTAzUTwvGRafXvzHOpCmDb02IiM2oN6zUZC5L7aRikKpDKhfDA84HvnKQUzTQVKlJhpDTkZXPDQ8xDxYvjAg9G13ldKoiteYwu915LKyceR8juRcfImhoj4dXOBym5EKVmqxu6Nx0vACBvCexeH1c+JYThTUNXZoyKVT25bUdjCWUWkDobIh+elQYpRmOh5VX1HZoyswAxwdTo4orexzga2yxBCXU8M89fVP6l3Niqb1xS0/CrDSmC4U66M6PBCfUNLZatR5aQXnbR9OiFRzGLtkz9+x9VQymBRV1ncSzk3MbUQwExFefCK/YF1C6P6CUD3yNSqvjFA1o1vs5BHQ+PSRYLNkbZQoO/LOZMUJAZn5Dovfx9GiRz2GsKTjwdydFVtV3aqY3Bp63J0RwwQoOj2aCPdQ3ICK1TjOxhSXXPtAngEtVcHgnTZBFuIiUOhNiwfRTXKSCwxPWTaz6XEyVw9tRSS7gTFSliOgrOEyUYIxY8nBImc3uBUT4oweDSp8fLmIqCg5TZp8/3C9gxPKk3eeKqd1m6FDCj/Ouu+tc8fBlSUyAxAUoOOSQJjzw1QUK62w9WUgFN8IVblnESc1ropjTwIUJ9391wQBpgjdM6Vbu/uw8AZIxK5PnbMskF4ShheVcgl1ZRS2VdZ2dlr/l4/CBrySrEhyjAc1oPHtrJh3pjrzAAN2K6U2Jmm7/5JyhuhgFh7HG/ZNXmsDQpeAw0N6bHCUvHGrhzVibuTlDXjjmbs9UcBho7GshLxyE6RQcxoYgqekpIxls3UJcRMFhuCKSab901cCEFlLBYawt25cjFxwrD+SqNEHPGYp1WchYdTBP5ZB62rafLjK/4o0Aq+YVU9nnF+KqHxsQZE4yHh8YRFazhik4vCiqZkc3UxXtIFt90a5sIZ5WcHjZ8spa2bLJ608ZLqDvvLj8slbNbKZETUha5m3PvO/LC57HguV7BrDiqnZNmbfg0JNR3GWxkw+Gbva2jwwvPcgq7pezY4+ElHdZHfovXsFhiDH/Z2s3nY2hhNxj/qHdK53l2fH62PCFu7K52TChv1oVsCo4DLS/ZkUkZjdqPbSGFktsZv2+CyWsfjEtQH+mU49PM/aqpQsPLCRx/AjyNa2HRi8uG9eMNpXs88SgoNqmrt6nBCN5RW9NYDspt5ExhoUxnA985SCnaND7dGXU948OCPRcso/KBHtqcHBhRZtmessvb3tyUJDKBPOE/dsLgni+mNPisxrEq5OCw3D7z9VOahoz8dRMZmQpM2Pl8rycQ6oSjJkwMlEwlRaSp55KMPa0dZ+hyVqG0+nN0nUU9vhgSpTKPjepNIEiT7xwincZz1htY9feCyUUEVTSBDl0K+zTSfhLxKncbkx0wlNq5+3IemV0mCl0KwoOBGcuSJWoA0YtL7ck6mWXtKCFpKSkC/In3lw005rSrTzYJ+DN8RGUfhu7KoVxhZ0MWIUJSapJy2+iKCBjDOMBH/gaklhzKLiMBjSjMV0orEB38+pWFByUjpRXmjBjc4aCw0Cj3oG8cDBTVnAYaCx6oTiVkYx7vjjf1GpVcBhrpPnLCAdL9irZx3Br67SJEn2yOMWGPJRSqtIEqb4lFxykiqk0Qc8Zy1qykLF0b44mv0m2jdeoFcnmJ2Pc6hTN86ayzyl/TnKvmclALUEimYLDaxs0kdppTjJ48Nm9TobSrZD4effn500V0kCnqQlTcHhdzmSSbSLZI6Gk2jTSJgWHKPpzNLT8SZGC5XFH7sDufyLbSMFhxp1vtp4qonCxJ7EgKEc9CDn2B1IbALa220jgQJdmNBY8y2CRoK0miyk4hFEbn2oIbq/hgapq8Z7sPKnV9AoOMR1BkMjSF9XsH+kX6BoQj/QPpMb+uiP5qFF0TCwUHHIade/ZTnzJnpyJ61IHL0pgdz7yQFEskvlHTiEf+MpBTtGA4DevylLsJ/endulABgAAAGCQv/U9vmJIDuRADuRADpADOZADOZADOZADOZDjBTmQAzmQAzmQAzmQAzmQA+RADuRADuRADuRADuRADpADOZADOZADOZADOZADOSAe34f5izVe/wAAAABJRU5ErkJggg==';break;case'apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__6bb95962.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALGklEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUc7syxR/Nq2bdu2bdu27fvZtm3b9nds2z5J+v2e+vGeOyeZSbqT2qv+GHRPZq3ZaVTXro56NBYklUz5LafzQxnfX5783rHxz+6EccApF7lFgdgih6C5oqBo1KcpH50c9+Q2f2oUozBVopwcAn99ddHYL+Of25mv3iqjChWpLuSITlStGp346n586aCN6jxEyBFdCPiLxnzB13XFaEJ4oJAjGuBvrM3ucC8f1UXjgTzWenJIm+EyMzQ/Ot6nAgGLySGgC/CAGbp/+cpicsgIlE/oqfETVpJDZq3/d26S8PxuxRN/+Ltltbm15W+f2/NpXZiK/3f+wg8JOaKkQ0l681BdJtBYl/LRKS2Qo2bLbF2Yik46FwvIIT5QHOF/Sg7QkLM1/pkdQyEH/rHmykIhB1AFNQUr8lYMjx/+0/KfXpzx4s2jb750yKVn9T/rhN4nHNr10L067LVDmx2wvTvuzSkXuUUBir008yWqUJHqhbWFykvg8OazOSEHKF/YNxRyYEWjP4tRcpTWl05NnfrF4i+uH3k9336b37ZxxXjUDSNv+HLxl9PSppXVlylXwYKIc3IwI83p+mgo5KBvii1yrMpf9f7890/pc8q2v23Lt/TU+IlT+576wfwPVuevdmWtlQ/mjBx69FrFYmzQ5MAaC5KjnxybijZ9svCT43odxzeLiB3f6/hPF326uXizChYstTskR3N5vj6uz1gX99R2/02OzbMckoMfjWZyMBS4ZfQtfB5D7PYxtwfXkBCK4ZAcef1eai7P06elszr9V+HqjdMdkiOny8PRSY41BWs0LUyz28bctq5wnWoNCNVxSo6+L1I44GvWg4/s9nf/BznWTXRIjozvr4g2cgRU4JcVvzC54DMYa7ze76t+V47B6MEhOYpGf86VwpGf6Cu+2vKktw7/LweoE3Ikv3dcVJGDaQhNt/4Ghtvd4+4ubyhXDoCHwyE5Smd2/EcLsWGavliXvEwXrlg6xCE58HZEDzkafA3nDTzPClpou3DQhY2+RhfJUbFk0D986i/t1VSSqa+XTP7l79fL5/eKRXK8MusVi2ih7c05b7rYrVStGa+vp399UcDXpBf6M3+9kYsl09rEXLeC38k6WmibnTHbrQFpzaaZXNFWMPRdfctXWZT4+oG4PmNuQPryzJftJccbc95wayqLj+u/7tKW6Lu1cfPy+70cc1NZ/J72kuPM/mc6cIIFSY6EF3ZvKkrTBerTVsecE8xSWuiZrQP3eZDkwNK+ODfQ3KjL2OQ+F3Js//v2ThbegiYHlj/wdV0m2hbehBws2YdCDqxy5UhdLKqW7IUcBPvgeAiFHPHP76qFstEU7CPk0GGCwZMDS/30jEBTvdVhgkKO4AOMiR9uOcA4q81tMRdgvEu7Xewlxx4d9hBpgoe4YtgV9pLj2hHXiqjJQ3y88GN7yUE0q8ghPQThMzqGwy7bqe1OBDJGXEgNM6JZSP3D8h9sJMevK38NTk5N/+Jmb6LbjKgkhz/gv3r41XYxA20EoWuSvCUcqG6svnf8vbYw48GJD9Y01biR9umrYNM+fRVbaZ/4I3637DutTDHTtvt9O6Ry7oolcXi3rJLVRjEK4wON0YRxczPnnjvgXDOZcf7A8+dnzfcs1WQyS+2EYhCqQygXzQPGAadc5BYFlKSapAkZET/ixN4nmkOLk/ucPDphNC8mSWqNQLO/edCWQYz7aMkj2ImgoR2ydYgv4FMGQlJN5lTn/LbyNwTy4aTFOQPOabOqTW51rhJYobJPr0inLSHVAkJnL/TTp/U9jdQMg7cOzqjMUCZAyHH50MuTylqd1bu4rnhSyiT+3K/OevWmUTeRe2PHNjs6pwKFqUKiDqrzkMkpk0vqSlQrEV8af+WwK4Uc3i7ZM/YMPSsGw4LMykz82ctzl6MYmJA8YWjc0J4bevba2IsDTudkzOEWBSgW+hgCdh7b81i9ZO8VhBzYdSOu0wIy84FE76phV/HaQo4wBftcPPji7KpsZTxoeC4YdAEvLOQIayTYQV0OmpU+SxmM6WnTD+h8AK8q5IhMmCCLcDPTZxpIC4af+iWFHOFAC77qMYljWLBVEQUvMDJhpPboCzkMCjBGLNl/c/8mf5MKO/jRPpv6nNT7JP0yQg4To88P7nrww5Me7rquK7nbPF3g4OHMdbus6/LQxIcYAOkXEHLYIU3Yv/P+JNZpu7otGdxwV7iyiLMybyXJnO4ad9d+nfbzQpoQAYhuZc8Oe+IgeXzK42/PfZtYEJoWlnNxdq0vXJ9VlVXb9I/ISg44JVgV5xgFKEbht+a8RUWqIy/wQLdiPETUtGu7XT3TxQg5vAffz15pAk2XkMNDXDLkEnvJIQtv3uL12a/bS4535r4j5PAQ7GthLzlw0wk5vHVBktPTRmawdQt+ESGH54pIhv3WZQPTWkghh7f4fNHndpHj6yVfS5hg+IBi3RZmfLP0G4khDTc6rOlgvuINB6uKCCT6fHzS+MO7HW4mM47ofgRRzQoIOSIoqmZHN6OSdhCt/tGCj7R4WsgRYWwp3sKWTRHvZXiBW0ffurVkqzINImpC0vLuvHf37bRv+GnB8j0NWHJZ5OTLQg4nEcV1zXXEg6Gb3bntzl5zglXcG0fdOGDzgPrmekcv7x2EHIz/2drNYWFYQuwxf2iks+72HWcPOPvDBR/yseGE82xVkFXI4SH+HhWxJGeJaiWKaosWZC3osaEHq18MC9CfOdTjU4y9aqlCh4UkjocgX1OtBLV4bUx5DQn2ObL7kfk1+aGHBCN5RW+NY3tZ7jLaGBbGMA445SK3KBB6uDLq+8O6HRa+YB+JBDum5zEJpQnKeMSVxB3V4yiJBAsH/muCoPsXM7Eoe5GeOgk5PMf/rnaS05iBpzIMRCkzYuX1JIY0wgHGDBgZKBilhaTXkwDjcKPlCE3WMiIYUMNPk9jjsqGXGRp9LtIEkjwx4dRzmfAgryav+/ruJBEUaYIduhX26cT9pf1UroOBzoy0Ge/Ne++MfmeIbsUIciA4C0KqRB4wcnm5Eqi3sWgjWkhSSgYhf2LmooyF6FYO7HLgeQPPI/Xbk1OfpF1hJwNWYaamTl2Vv4qkgLQxtAcccDoldUq/Tf0oQDEKU4XEClQX3Yq55CB1pL3ShNdmvybk8BDkO7CXHIyUhRwegkUvFKc2MmPvjnuX1pcKObwFYf42koMlewn28RxVjVU6RZ8tRrKhMIWUSpgg2bfsIgehYhImGD6wrGULMz5b9JmyDPZv4/Xo5EfNZ8ZTU59S4YdEn5P+nOBek5mBWoJAMiFHxDZoIrTTTGbQ8fF6KrIQ3QqBn3t12MsolwY6TaUh5Ii4nMmQbSLZIyGlPEUZAiGHTvozcMvAo3scHSlaIHdg9z8dbSTkMHHnm3ar25G4OJy0wClHPgg79geSDQArGioI4ECX5jUt6MvgIk5bJbCFHBrkxicbgus5PFBVfbLwE6T9SmAvOfRwBEEiS19ksz+k6yHBEeLQroeSY//H5T+iRnE+sBByWAby3rOd+KeLPn1u+nP3jL+H3fmIA0WxSOQfMYUccMpFblEA5zdTZSv2k/tLu3QgAwAAADDI3/oeXzEkB3IgB3IgB8iBHMiBHMiBHMiBHMjxghzIgRzIgRzIgRzIgRzIAXIgB3IgB3IgB3IgB3IgB8iBHMiBHMiBHMiBHMiBHBDxnn9eIYwbtwAAAABJRU5ErkJggg==';break;case'apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__6bb95962.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAMAAAAKE/YAAAAC1lBMVEX//////v7yyqv//v3qrX399/PZaRL44tLjkFH9+fX007rpp3XWXgLXYQbbciH34M7jkVLggzvkk1byyqz007nYZg7+/Pr228b++vfqrHzhiEP33svii0j67OH67eL++fb//fzwxaTopXH+/PvstInwwp/++/njj0/XYAXabhv559rZaBHhiUXhiETbcyLii0npqXjZahXghT/YZQz228f66+D23crXYQfijEr45NX45NTz0LX89e/ffzXZaBLstYvhh0Lww6DpqHb9+PTabBfefTPjj07opHD12MLxyqv22sTbbx378OfbcSDcdSX89O7aaxb67ePz0bbnnmfpqnnjkFDmm2Lll1vbbxzklFbffzb11r/abBjkllnghkHkk1X23cntt43338ztupL44tHijEnqrH3oom355tjlmV7z0LbopG/rsYXvwZ3fgDfccyPfgjvuvpjoo2700rjopXLzz7TvwZ777uTstYr45dbbcB788er11r7ZaxX66t7YZAvww6HbcR/tuZH00rnrroDcdSbnomz01b3eezDwxaPstoz34c/zz7PdeCr55tfxyKn56dzzzrHnn2jfgjrnoGrpp3Txyan77+fklVn99fD88uruvZfmnmb77+bqq3vefjT++/jZaRPtt47vwJzefTLdei301Lzvv5rxxqXyzbDnoWvfgTjnoGnhiUb77uXttozeey/qrn/99/LZahTtuJDuu5TpqXflmV/ijUvlmF389O3mnWXvvpnddynwxKLssobxx6f88+z669/YZw/23MjhikfghD7deSzabhrfgTnyzK/YZQ388uv118DzzrLxyKjrsILuvJX449Pdei7cdCTghD3z0bfbciLggzzklFfss4jWXgHXYwnXYgj78enefDH56NrstIr56Nvqqnncdif78OjYYwropnPqq3rXYgfcdijqrX7XYAT01LvrsYTWXQDabRnWXwPcHI28AAAFZElEQVR42uzBgQAAAACAoP2pF6kCAAAAAACYHXuAjqRLwzj+1MS27Yw939i2Z23btm2PbduKbdvdwZMce42u2uqkC3NuLX5Hcf6Nqnve9z/e/3V0hoYEBgQEhoR2duA/Qm93Dx30dPfC6jzcbVSwuXvA0iZ5UoXnJFiXWwydcHeDRfl40ylvH1iSmzfHESzBitw5rkFLXoOcgAWvRg9P/lXfyMjIu/lPd//8aR//ytPDsm+OUQBJdv7DMIBRq75BegMcorEoWy3a1vbK/nvOs6cPcwura0YjhoZejtZUF+YeevosZ6L/103HaHxYLZoDMN+DJTGxEXQiYvp7du+AUz3yaOl3atF2mGxBv72ZE2h+/Os4qOqgPBo3AlWi2QUTec2aQRd9dG8V/l2nLPq3AIL8+Re+suhOmCYnnprci4NSqCz60GEA7+Rf1MmiG2GSd8RTs4VBkAuRRT8MSQOkLJL8uSy6AaaQmoaow9AKyATKoo9wFoClk0lOkkXXwwwP7lGnrKVwECCL/gb5AQBRJFNk0TaY4Ngp6rYs02n0EzIiAUATedb86F/QgPc6fXsUkXxDJOA2jT8w/e2xm4YMO7sQv0mSqQC++OUB0y/EPTTkO85uecP8iyIAX9pj+i3PTkNOKw4XRfRXvwIgzvTDhcYMKY5xRTTbMwGYfozTmDH8S49KNC8oou2wVnS3WjR/I48esFh0r00tuqUDzocA8dFwV4tmfrT6uCU0Wn2wvct/Wmj6YNtKQ+YKWSE00JC3CFnW3KEhMULWYkFDNCDAS8MCMtgHZhmhAelQcnOnE4MSTOM2h7rFSqKW6h7e1Omta6DGY9BGBdugB8wlVTRTB/+ncKZ3wE4H9oE2mO+77dRs3Rsxnq7OxoZ6m62+obGzC6+GVL6dmvRskiBe2pNYf7rIf3pKMizis5+rpgu+d/UKLOXFk4eP6Vzzjw99fz7E+9gUKJSUXb10uSabMtk1uZeu/ug5FH5SCxG4fQfUJCd4zRs+f+7R2XPnz8zzSkiGmpLAMYhArsyETsfyKCqaH9kFXRKOUlw0d/pCh7qbFBlNzgnXnFxLCo4m14W5wWVuP20nLRBN2jMi4ZLIk98mxUY7WNtYWSVhXJLX7dfvJEVHKxzPentcMlSlvW3F+uV0JDRaoXjlgQ9WVJaf+WGiD+CTGDRcXlnxiQMr51JJfLSqllaqExrdQkOKIcJFGlILES7QkA0Q4RkNCYMIbstoQKEEIYLGqNuQFwQ5Qt2uQ5gY6vQtCHRL34apEkIVTaZmryuDYB79Q9Qke/UaiLc4vpkua565CNYwJXUqXbK8vwtiOc60SRmxr3ECrdNWRct/XQD/JjhKCu+vpjPN1/x8o+Fo5DWIwLHNUPhD6cc3zAz0pwP/GTNTH5WWQKF0TNQQsGUr1EQ+f+EVFR4WFh7l9eJ5JNRcKRA3uWx7H3T5/BdEjlvLN0OHjVPFzohDd5KgkY/fkPDBdkY4NKnbZolpvLZMgouk87+0zArh8aOtcMHhgz3W2nuc7lecHgpJn/xUPknR0XOp0HL5qhdUvWvF9FYqTLXO3mP2qdz7/ekZSxbMj06av+DTn0nvv5/bPts6e4+HNORXEOE2DXkEEUqKacDLBxDiOg0YgRg3blK3tWsgyEnqtgrC+FGnAYgjvZm6vB8iZU6jDvGRECotlZr5pUG0sAhq8rIIFjClnRoc/Rkswe1rX6eLAlMkWMWxE8fpgpu3MmElv796jRNoP3EDllO1ejKd2jJrMazJrXRk/ZuoxNGsD22UYGmJm/bu35eXX9DaUpCft2//QNgu/Kk9OBYAAAAAGORvPYi9FRsAAAAAAAAABIY9HLkA16UTAAAAAElFTkSuQmCC';break;case'apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__6bb95962.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAKzUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOQBJz2xh+No2Cte2bdu2bdv4bNu2sbZtewfr3XFyn6v+/X/ZmckknTmnuqpmMt3ZVOXZxunznva8RcY6AueWDK39WN+fX935oye2fvFeFD7wlYv8RIXsgkMsNjXmO/LLrp89veWzd7lhoRqVaeJxOMQSoVnf8d+2funevPVFFZrQkOYChzdtpuJo+zcfxptOutCcmwgc3jIj4Tv2G95uWgpdCDcUOLxgicj84KoPpgULVbght9UeDukzFBlp5mP1h0zD0BgOMYYAG8hQ48vvNIZDZqA2YaEKf0JLOGTVertrk7Yv389/+i//LQPL3nnn73548+dVZRre7vqFP+QROGRA6fjuY1UdI7LQ9bNn3Akcc01XVWUaWhhcdIBDfKA4wm8IBxYeam79wj1TgQP/WGx63BQ4sLlAZKRhqvXiSOm27kt/bDr6jap9ny3b+ZHire8pWP+m3FWvvLbshZcpq191ja9c5CcqUO3yn5poQkOazwcjpp2Gw5vXZgUObDJ/eypwUHxHf5WlcISmot0FvsJ1nYe/Wsm7X/K8S2kp3Orw1yq5bU+hPzQdNdNqbIhYh4MV6dD6T6YCB2NTdsEx2jSVu6xt2/sLlzyfd2lv4U9s/0Bh7vK20abptOy18sKswaFmrzNsxiYNByUy1ul9OHwdM/mr2re8K5935kjZ8u6CgtUd/s7klwBstVuEIzY5qj6H+mpaPne3W8PReMUiHPxRL8PBVODoN6t4PS4px75dnVxHQiiGRThGdnwtNjmivgavrLlV5dn6ixbhGFr3cW/CMdY87RgWFhAZb10cIoTqWIVj+1epbMRjavIxuPL9t4Cj5rRFOPr+/BrPwWGYZdt7WFy4EAtVeLyKXb2mZWP2YBEO39Ffc2X88C/Ulfj8ZMf3Hn9rB6gFODp/9CRPwcEyhP9L9Q5cXk58vyY8EzMtGB4Oi3AEL6/+Xw9Rd0FdXOgsUZWnivdZhANvh3fgiEcSuz9eogUWquz5ZGk8mkgjHFNFe/57se1rD4oG+tX1wNl//Pf6ZO6WbITjyp+bNcJClWt/b0njsDJTdVJd7/39y4x4VG309//zrVwMXFiWdcMKfiftsFClrzSQrgnpXMNldZ0ytv+H6qf4tK/924/E9Zl1E9LLf1Ldhgc7D+tLWXxct/qVvkT9Ot+SM7rj61m3lMXvqS8cOz9cnKQTzAIcbV+5f9TXoyqEeiqzzgmmKRZqZZuk+9wCHJSe37zQiEVUHZ3c5wLH0hdcTn7jzQIclNHd31Z1vLbxJnCwZZ8KHJTp8sOqmqe27AUOgn1wPKQCR+uX76uEst4J9hE4VJhgKnBQun/5HCMa8lSYoMBhPcCY+GGu3EkZWPaurAswXvHSK/rCseoVV0WaYKMd+Hy5vnAc+nKFiJpsNGK99IWDsFORQ9pohM+oGA69yvIXXyGQ0XEhNWR4WUhduqVbRzjKd/QkJ6dO4/jCaKL6DG/CYSSMg1+q0IsMtBGmIclbMmKR+fjJH9TqQsbpn9RFF+LpSPv0u2TTPv0uy9I+GWbJ5q4lz3e7YwOpXHrFkji8lUr2zgvVqIwPNEsTxvWXB3d9zKUhg7s/UTJQGbQt1WQnW+2EYhCqQygX3QOFD3zlIj9RwZRUk3QhrZdGkba6B4tt7ytsuzJmGpKk1h2WiBtNZ4eZ99GTOziIoKFtPjdiJFzJhaSanB0Ple/sQSCfSSx2fbS4YnfvrC9simmhsp8aXqAvIdUCQmd79NNFpGZoPjc8PbJgirkBjv2fK5voX7SDb2Ey0pXn45/7yl+aj3y9kgnKshctYvShMk1I1EFzbtKV71uYjJqLtGDP3IEvlAsc9m7Z855Sz4rBtGB6JIQ/e7h+EsVAZ854y/mRuqOD9ccG+cDXvrIAP1GBaqnPIaBz8zvz1Za9XSZwUA59pUIJyNxvSPQOfLFcxXPYawIHZe+nS2fGQqbrjY5nzydLeGCBI6ORYOvekNNbEjBdbD1F/rWvy+FRBQ5nwgTZhHMhImDB9NNCmKCdJnAoX3X71THHvU88QNvlUeXRFzhcFGCMWLLx1FAi5gAi/NGGE4Nb36t8KgKHK6PP178x58xP62oO9ZO7zTTs3dZhrVtzsJ9NeSZA6gEEDj2kCWtfd53EOpV7esngRp+flk2ckcYpkjmd+F7Nmtdet/wkAof9lmJyWRwk535Zf/2frcSC0LWwnYuza7xtZmY0FA39Lx6HD3wlWBXnGBWoRuVr/2ilIc2RF9itW3GjiahpxUuv2qaLETjsN96fvtIEui6Bw0bb95kyfeGQjTd77erfWvSF4/qSVoHDRuNcC33hwE0ncNjrgiSnp45kcHSLaUiwj/2KSKb92mUDU1pIgcNeK1jboRccRRs6JUwwc4ZiXRcyijd2SQxppq1qX5/7FW84WE1HTKLPO66Pb3hLnjvJ2PjWPKKaTUzgcFBUzYlurkraQbR63sp2JZ4WOBw2f9csRzY5PsrwAEe/VRXonjXdZiJqQtKSs7R1zWuuZx4Ltu/pwHgAU8wpOKyEi8bCceLB0M0uf4ntqQfZxUUl1Xh6OBZOWHp4+0zgYP7P0W4WK0MJ74N/aKSz6R07EMfmrWjn5jBhPVsVsJq2msRzwMdQ7eRiG85PRMiWUXd0gN0vpgX/1Z9ZXI5yVi1NGLCQxHET5GvmIo1W3Idi2m0S7LPxbXlz/nDqIcFIXtFb+xBF1k3SDbAxRuEDX7nIT1RIPVwZ9f2GN+dmLthHIsE2vyM/2Dtnut4C3XOb3p4nkWCZsFstENT44k4brJ5QSyeBw3a77W4nOY2ZeJouM6KUmbHyeA7HkEqAMRNGJgqu0kIy6kmAsSvgUBGa7GU4GVBjmCT2IL2MRJ+7VJpAkicWnGotkxnjz9UeGSCJoB7SBNGtcE4n7i/lp0q7MdHpKfbnLG3b8aEi0a24Ao4kBGdIXfBwk8srLYF6vvYZtJCklExC/sTKxXStiW5l3etzdn+8hNRv53/VQL/CSQbswnQX+EabpkgKSB9Df8AHvnKx4eQQFahGZZqQWIHmoltxLxykjtRXmnD1ry0Ch41GvgN94WCmLHDYaGx6oTjVkYzVr7oWmooKHPYaYf46wsGWvQT72G6RuZhK0adLIdlQhkJKJUyQ7Ft6wUGomIQJZs7Y1tKFjII1Hab+ptkxXmd/Xu9+Ms7/usHMvEn0OenPcX26mQzUEgSSCRyOHdBEaKc7yWDg4/FMZ010KwR+usr5gUsDnabpuAkcSs7kkmMiOSNhcsA10iaBQyX9aTozvOnt+U5hgdyB0/9UtJHA4caTbyr39JG4OJNY4JQjH4Qe5wPJAYDh2RgBHOjS7MaCsQwWcdqaYrrAoYzc+GRDSHsOD1RVRL0j7TfF9IVDTUcQJLL1RTb79W/KTQ4IGpJjv3RrN2oUmVhoBod1I+89x4kXrO64+LvGkz+o4XQ+4kBRLBL5R0whH/jKRX6iAs5vlspanCf3r3bpQAYAAABgkL/1Pb5iSA7kQA7kQA6QAzmQAzmQAzmQAzmQ4wU5kAM5kAM5kAM5kAM5kAPkQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzkgDe9KFu6DmR8AAAAASUVORK5CYII=';break;case'apple-touch-icon-red-507228751d2170d047e72142d2c02390__6bb95962.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALAUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUe58yxR/Gfbtm3btm3btm3btrW2jWRtZG3MvM9TP/72O5vMTLqTuqf+GHRn55y+26iuWx3zGG+v7v7ukeZnT6i/d9fANWtXnL0AxgW3POQVBeKLHILJvvbOT24O3rBh+elzzdEoRmGqCDliHNOjg52f315xzoK0+qyMKlSkemySQzCQ/WnVxcvQ0mEb1fmR2CKHwJru/Ow2WtcVowvhB2OBHILp8eGmp46mUV00fpCfFXIY32coZrjMj6ePsS1LyGEwGAI8YIYaX+4Qchg8A6UJPTX+hJHkkFXr/12bVJ67SNfX9/3dGh87eOa2b3n5TFWYiv93/cIfEnLEyIBSffnKqow1PhK8YaMZyDFU+qsqTEUHg4sJ5BAfKI7wOZIDjDWXVZw1fyTkwD822d8h5AD2eFd7f2Fm+7cf1r/4QMUt5xecdWDucTtnHbpF+j7rpey8ctKWS/yxwXxY0lZLcstDXlGAYhW3XkAVKlJ9vLvD9hI4vGk2J+QAvUmvR0IOrPPTW+KUHBO9oe6E72ueuC3/9H1p+9/XncsV46cKztiv5snbuxN/mOzrsV0FGyLOycGKtPn5kyMhB2NTfJGjvyg78MC1GQds9Pt6c9OWnhp/IvOAjQMPXjdQnOPKXisN5owcavY6wGZs2OTAxtsDsU+Owcri4KM3pe+1Dm0WFUvfe93gozcPVZXY4YKtdofkmOxtU9ej9fnlZ8zz3+Qo+cUhOfijsUwOpgKFZx9E82hihecdGl5HQiiGQ3K0vnHBZG+rug398sx/FR4s+tEhOZqfOzE2yTFQkqtooZsVnnvIQFm+PRsQquOUHK+fT2FralJNPpqePPI/yJH/tUNy1N+7W8yRw7LqX3qIxQXNoK3xeQ2vPmo7BrMHh+To/PRWnnR8fJN6MjXcW33Fqv/lAHVCjsA168QUOViG0HWrNtDcii48crK/13YAPBwOyRH6+el/9BCFP6iHI4F0Vbgv7T2H5MDbETvkmB4fyz5iGyNooSzn6O2nJ8ZdJEdf6jv/8KlfsMREd4N63v3tQ39/3pvwSjySo/L2iwyihbKquy93cVgZyP1SPa+7cwdrakJt9Dc8vD8Pu394LO6GFfxOxtFCWSj1V7cmpEPFP6vnWPv7V6tXU/2dVZcuj+sz7iaklbddaC45qu66zK2lLD6u/3pLX6LeDpf/0fbGhXG3lMXvaS45sg7Z3IETLExyVJ636ERnrSowWpsTd04wQ2mhVrYO3OdhkgOrvW1ra3JclTHLfS7kmNfJxlvY5MDa3r5UlYm1jTchB1v2kZAD68/6WBWLqS17IQfBPjgeIiFHxbkLK6FsLAX7CDlUmGD45MBqbt7Mmhg1P0xQyBFWgDHxwzMHGDc+dkjcBRgnbLKQueRI3HwxkSZ4iLwTdzOXHPmn7i2iJg8RfORGc8lBNKvIIT0E4TMqhsMsS9hoAQIZoy6khhmxLKSue/4+E8lR//LD4cmpGV/cHE0sK5ZTMFjT03kn72kWM9BG0CqSvMUPTA0PFl98tCnMKLns+KmRITfSPt0RbtqnO+Is7ZNl1T17D7IRrR0b68+DVM5dsSQO75lVssooRmF8oHGaMK4n/ffsw7fWkxnZR27bk5ngWarJAFvthGIQqkMoF90DxgW3POQVBWxJNUkX0v7dRxn7rq8PLTL237Djx0/5MElSqwVQcLR9+Q7zPnryKA4iaGjbvnrPmpqyNYSkmhxrb2545REE8r6GeB22VcNrj411tNgCI1T2o0119CWkWkDo7Il++sBNSM3Q9tW7o831tkAHcuSesOtwXfWsRVA9XV2/f8M/d+UdFxeceQC5N/7YcH7nVKAwVUjUQXV+pOv3byd6u+1ZYjhYkXfS7kIOb7fsmXtGnhWDacFoSwP+7P6CDBQDXb9+1f71+y0fvtzy0StccNuT9huvKECxyOcQsDN9z7XVlr1XEHJg+aftowRk+gOJXt5Je/DZQg6fgn1yjtlxrK3J1h50PNlHbccHCzl8jQRL2WGFUMovtsboTvoxebvl+FQhR3TCBNmEC6X8rCEtmH6qjxRy+IEZfNWdP37Ghq0dVfABHd9/rDz6Qg6NAowRS7Z+9qY1OWH7Dv5oyyevZey3gfoYIYeO0ecpO65YesWJze89T+42bzc4LIu1btO7z5VcfgITIPUBQg4zpAnJ2y5LYp2G1x8ngxvuClc2cfoLs0jmVHTBEcnbLuOBNCEaEN1K4haL4yApu+bU6nuvJBaEroXtXJxdg+UFo62NUyP/iKzkgluCVXGOUYBiFK665woqUh15gde6FR0hoqaETRf2VBcj5PAWtJ+50gS6LiGHh8g5didzySEbb96i6s5LzSVH9X1XCTk8BOdamEsO3HRCDm9dkOT0NJEZHN2CX0TI4YMicl7jsoEpLaSQw1vUPH6rWeSoffpOCRP0DyjWzWHGXRJD6jca33xKf8UbDlY7KpDo885fvkzdZVU9mZG662pENdtAyBFFUTUnummVtINo9eDDNyjxtJAjyhiqLuXIpqiPMnxA4TkHDwXKbN0goiYkLdX3XZ20zdL+04LtezqwkfroyZeFHE4iiqdHR4gHQzebsPGCXnOCXdyCM/dv/fyt6bFR5x8v5PAEzP852s1hYVhC7DH/0O5KZxk7sg7bMvDQ9TQ2nHCerQqyCjk8xN+jIvpyU2etOQt19mYlNn/wErtfTAvQnznU41OMs2qpwoCFJI4fQb5mzxLU4rMx22tIsE/abquPd7ZFHhKM5BW9NY7tvvx0+hg2xjAuuOUhrygQebgy6vvUnVeRYB//IsHS9lxruKbS1h5DwfK03deQSDA/8F8LBDW+6Ine7GS1dBJyeI7/3e0kpzETT1szEKXMjJXPi3IMqQQYM2FkoqCVFpJRTwKM/cbMEZrsZUQzoMaySOyRe/wuEn2uqTSBJE8sONVaxh+Md7Q2v/8iSQRFmmCGboVzOnF/KT+Vm1AetuSfAvdfk3nwZqJb0YIcYQjOkLqQB4xcXq4E6g1WFKGFJKVkGPInVi62thDdSvL2y2cfsQ2p38quPZ1+hZMM2IXpTvi+vyibpID0MfQHXHDb/cd3rZ++QQGKUZgqJFaguuhW9CUHqSPNlSZU3nGJkMNDkO/AXHIwUxZyeAg2vVCcmsiMpK2WnOgNCTm8BWH+JpKDLXsJ9vEcU0MDKkWfKUayIZ9CSiVMkOxbZpGDUDEJE/QPbGsZc2LoY7fYhsH8Y7xKrzpZf2aUXXeG7T8k+pz05wT36swM1BIEkgk5onZAE6GdejKDgY/Ps6ML0a0Q+Jm05RJauTTQadoKQo6oy5k0OSaSMxJGGoK2JhByqKQ/bV+8nbbHmtGiBXIHTv9T0UZCDh1Pvml84wkSF/tJC5xy5IMw43wgOQBwcqCPAA50aV7TgrEMLuK0tQWmkEOB3PhkQ3A9hweqquCjNyHttwXmkkNNRxAksvVFNvuUnVYKc39k55XJsV/3wv2oURxMLIQcZoK89xwnHnz05vKbzim+6ChO5yMOFMUikX/EFHLBLQ95RQGc3yyVjThP7i/t0oEMAAAAwCB/63t8xZAcyIEcyIEcIAdyIAdyIAdyIAdyIMcLciAHciAHciAHciAHciAHyIEcyIEcyIEcyIEcyIEcIAdyIAdyIAdyIAdyIAdyQHtHp5xFOjNVAAAAAElFTkSuQmCC';break;case'logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg':$f='(]^+JbP.FqjXYdorFxH%oTmn1#,Na[(-^<}T{`+Ahl-RItQoM;{4bK}l["$V3F6U&V6Ey@S8#w=t>3kaN[hLow+fWEUH+K<LoXqyEy6JupFy-JyK4S8q(7tl96;KLl/F|,Cz)p?p(B)[axu/4u77-)nvU
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
n9lrNOU01c?:p/5y16+Zkgo}`M)D6xm>7RiQ_b%p%ocllip!0).myrT"w[53iGRZBt8z<.d6p9("_WYy1v;v.xBx%3c3&hfawJgMtuxeflyK1.-:wQo"f_z&)W';break;case'jush-b3a93b18444da26820ff61746521dede__d6435f99.css':$f='%X.mPb3V?!K0u25Dm[994[Zg@N#Q)YOC=2R_hE~4)=>cbdia55M!*B>tthOR~qe;I4(JE7nkwCcvDp&`dDJK
f-B>jO*,sE/El7[<CeY-WtHaF_.kmd7o@TK)f,bmf|yor7&?P^<qeg8="Q1IULQ7R}m#!4#2";]EMU!F1OpTIT56:qJms/Dv-ge06DN|n`FW&*;(q:eY(cbjkU?x]gUap[%36X.0y?`E_>T!nUPeLxWQNBQNxgW
`cW13j?"h~G+;>ckmN2+
!n%Sl8/pL[B8
M3BQ=4I8t<Wims^cdx*+69AM:3(4#D7NrL5JU;F-nR)NxQWs7{b*3#%wvQ^i5"h{.StgdiF@(/BD"LK.,r2{$e&b4#$3x
`jO"8tQQ1CKB,TYk$:TIGnJ]L
WcYUpj,c5;pfH7%lebNsHuwv44..jP:
oZb0YJ@}qOxs&)5?s0oVA*
swu
jafJPvJQCQ5?:;JN^klF$8EV7&
J?y"=oJ8]]rh5ZK31++0s/h[@)W/]t4{Do0LZsyY]Ui=F:=Lj0OiN0Q{mj^W2%j7&7^of",u?pagL&"iw=-;EKZzP?gdKNd/SR.wcU#s5@q~Y}(R:VF$Q8dZ,L)ey.Hq2O=)g/K}w)n3hY`Mh>::q<VI?VE1nU^-^D/qa9JwIPcYCppQ/X@!6ZZjyFhzsSz&E^ke1)2F[wC7uibhCB4fEU_/mdWy=C$?"]4Jab*)fpfaP{Xx=d3$rPj<p5Yo$$mqn$a[gdSRbtw^Cd:8IL9Ep@:NwGhGJP+=>bUa%5!)#P![lKsPx,lj]_TA`s)}ui[?@K5<"=yhHX9ac1.(pCt<=mY#,*@Ho8y`eb"03`0_6Wx1`zEp[UTJnLo&k`Z*%U;u_AP@.ah.ussF)SKcuGHm@)HJg~D|K@x[(QcKT7b133MD8{9lxGHdF&gk".u/dfBj;$`frs[]W0RtuZ<]q+TW7f)<cBALJpw,4G-PO&to>Pl>;:<vNY$aUe;BxF$7V3N~j=h.gDd*D(06YWAm/nioW)g>F(ql[`!L/h`[Z(V`2J';break;case'jush-dark-f8dac59c6ad1018686e52a0e0357e421__2ec7793c.css':$f=',Gjwm6?!R"-YJmoGR`r@~cEv;#i.*-_KUyr[0$CF,>/n=#+liP*01.(73:+G.C]Ek+^-h&|hnGDq1:ccpxU98SxFh5MU%c+]DCcezAcUOWmDiL$
)yZA,ICx<`.i#E%U;lo*kf6u&LQx+!%1t]iP#G9;zGT,4U2"ha>hB#am`y1YU6$z!l#C%';break;case'jush-615bc0b9720a1de8edd2c6876a3495b6__42cd49bd.js':$f=',hk]`!>p9CvwpHP(hqgS0$0>DjvvnfNtZ=+9nN
$.nl"6@fn<kV(<^:]t^9c|n2m~.TGXrpInd(UuxUWLkl
*BAM/ne)r4ogC<0Eg7_(|eIi=nSi_k{<p@N9~i>R.x"MZP=Dy74;<h~shEg7y3hwym~^?k{HAqiq>Yqi$s>S_nae`<bE.Kljt`?7de~t|wBqj]sHR98]oS9mWC,xq"pC4jtZ5NRB[[Emsy&Xo_5yDw=v}1=j$oH^H#<R,cTl6y.O&I.lx8Z)zP"m:mIB}4bNu2UC;NO*GRku0y.XaxKOxdZhs!/HGK]/%.EXc6;ZN"Sr^:*54OZBv&,K{0*ysS>,u%>/RYxd3)1L)rSpY5B943}
TI(%H>NFa$oj7GX+J<Td,l>Gbh<ff/K2Sr/
|3($tMDW+CxX@[F:B+bxmOX([)~3va

i2SByA*^g/8uHsueh=y<#36+byU&)($&l4;IZCY4v+{v$Syqu?KrH&>51fyfW`>/Rv9U"y4mS<3R@cHL+l!oNgeXKa%qgTtK9@v&_2Sd%:;6-HNfeqmDHLV[Y4i
A!#jUa,L-dA:H##HDk$e{U[xAqdZ}oDEhI=LR2PpjJ=31^W&j!K+-+9T$j1BN^"y62hO8!~58w/]=B[I}<.A@!!AyD}IO0A"dE-8"2_&hIJuz,mRK0wQ-5&<]6^.@HG@
0<@|nj=oK[t-Pz44a-KS&{ktf#"Wa>bN"Z0w6/GZ*}2]?%T>Tn)oS<8Djxbe?*5!&|I5c<nhb<y^[3.b[*`>-Br*mk:-Z:WmiMVDe?0D6i(@fMKQC):E?6
u4@T2w$YJ?NB7f-YRSDd^,=uWTQH"TmXRIF4&F5ow"}Tx?&G]w^yB:UD6T"mv
[iNJG9#
F_`]&Qnkt!C^Oy!`EKsW0TsmX@*l-IBhjBO[jaO,syHtMP+M[*LV1Zj<x%-]XyR2gof;Q8KwoX2m=p!mQiJBW`VMan-D]h:t(u:;Q?@1UYj!^&>isfU0.GMX:$wY9/AL?9=G7s;7RuiWNk[)"k_kA)TB{v&947loVGF[Upw//1``bNPODu,_FR!LIJgX^v"o]nhewizy3j1Ht
d$K$RLeU1xDM?[{IT6z,BUZ6Gid6vo"SVV=$VeV;S+5jI+XGP^KvUCE$Za$fIfMSVR[c_FoZ:x4.~kWawhx9:$O7TB?P=fA&>RhI*=.n6V8xsTMGN]!#qN7Xy%b9hi!X!nmqgGVXUxnTiX`A*O+)D>bkxXBi=yq`?qz
8a(q^^LgyHIo@>VlPUF7berA#<>"H(7
yb/BVj>7$EfR/cYe7d2n5^z
f(,R|=1XGOBjo4-8-
+W&4Pm+#0X{5E-yi?q%kNEv58#*Ty6D+@qKE],&l$Vi9=aJ?CRsue;p7ohg
v#=UFvY%QWf0O;3c.`K8a4qJlxNDk==XYt^T`GRY!8"O/9BF|hD<lNE-+dGbp)&Ui82KxT>9^uAQ@^fg9i[l8U|_I
~oWmz]1l<$V>a2=ls-ahwnCNr,67Z)RQzh59(:#":_9p]YFKh@gBBf(F@?PBQ-Z?lL5%8DO^D*Ysmj%exK5(Fd-DJ(5UH3647v|d,gh`En9Aq/@7qYYn3ZMI*pN)]H[_9)Q:!10=Da?&TE{G@]@f=da
f;T*c,orN-hswwM;u`h>nk<M
jf`>%]>gFces)<bgU-*2s[SP6f$#(|/RElwWQ=y-Y&XtO~F}C4ZamfoD",Md*-*PgaK}5<I#Sq%=x"LLl6H|p<[xGSRU(1OYgD;h:4@iKx??A9qrgGt<d.qL6i)iFG+pt,&ZD*j
]b@uC{b*pc]UD~/)yUq{sJ%!1RwA=1fvhM=*fzwD-~$/C.ROPioy@R)w1>%@Q[kHANX=%|s3x<cTXP5vLp_uKfuBFj_;?xa<LxKd+r]^IM%hE7BoQxpl+W[Iv=j|c/<m571g7N!rVX)>j!e+fuK]6"q`f^jJ2YpA5{f4FeiVL
ui=^3lnpazny(dIHWXON4rsY_?xTW,"Wo
8D*zj{bwr7TQ)d
v
F<#"ew%h>35k/2$+j8Fe=aCvga%sEbRx5Ag/g4d7?<;Og:YPAq_)+@=GJR4Sz6g0"q%rTEZC)D`C9*lF{9lF[bAT3llO_L17Q[BSMN.;E=c7cT$&(QEw.Wl!YM7fFfvhjmZ0_*wa1pp)w2;^)o@4CJT/UdsKS:nn)1-=@#:1Fxb)NQ~oDACtd9.p55yW-2fS+C.kOQ>s#@Folk7/D06q>!5RjTLz%32hQ!.qZ#s+2T9/*!E$XVLUg^^uYUkev+F5h,fQXO.bQSi!(d70<:=-(>Q!>[(&`^4k_iz-qFD28Y2dnXc1Bp^GfHATo!7+21=UM/WATuKA#mt?tha3c(SUqT|9^[d(K@IYllExRxk=?(i/#cld<F=Dr>Y
4&"Yc_)U_Dp2|qcMJjfd_8bfoQM7|b[73K_?([/%3+D;W[tRvYJ-NZ[-%%(f14wZxN"[#u{*k;`.W#&nBDhq5g`)t/*Ne%WGm8a=.*ygQ9OB,$K89Wz)3^Kp+UMo=ETb}m,x"n.nZ1:m`#x,}FBm.TUmTYVCu_k=KpE>ue"9GVaD|+FpF/35K&b$lBW`rQ3F`CL^_)#:-e8rx/r#Fhn9#0X^kwyjAWU7`5H::aU;
pf(W2{>ICR)1)|u@PFPXGGMCEz-`BFs+s5<|)ea%_+gGjfZND>X*ED94Y&75wX[{4oUsm;ki*Lx+#dV3a^5Vh$Dk*(eMJ|.G_:QTj&k3dAa02<
qPP)1%CRn)0p6]abLPt!!YQc&QdLV?ds]g}?W]HMkg~;OXo3CnFiXUE+>bRPME;Vn[M!GAmOy"+[`!}pfPNpIIZQ?r=w{GYuYp^3BfH1Q`6j_NldO>N)Eke4SM4Z-I$APXH,-<FE0%SsA
*-<?8.eA7e;kA#Q@]7JBGs=f}0Ge*p811O6lltwh<uH,VRgO;&dvvu)i8T5=L*%EkXJgj?_c1[M-xXM=w[VVqr.K_Kof^OomE;05fCO"fN8?J?mN3y.4!ZScBkf5UR&%0A"EYp4nFZ|*IJ^ZM%+ej<H/yd8d`o&h[(7GX-uB#w`#_qsVj+s@?(E^_F
jw"}(S=B82iziq%AFc8`d_)o_XG|AK9(4?vIWK>N6<pK#y?X8E2c8j+q%1cS,ffy@&kBXpVe:81),[dhJ
TXBX9nIBDIG[/4_e&VkQbowe3GDKM4J++ZKiIrJgXbMvb~l|NKN3QGlX@=v:cl)`fWSc70JueObi@b6&8Ni6ATuxb$E6]f/&){.v6oO%$#SGD~sl_]r30`.d+x:d06C%ftM.0j`eYir<D&9}oIaqrfZ/-eT,im5)tA78gXLoUDH*]uDca(?4!U8yU$f4Xe;8^x<n35kEf#?bkWaC#d"?v[O.]lu}gui)adNkn^6Tf5<;F4H;sn#pl*#`-KbcUhC9lo>8ejm9W8EtcyZI]HQlW!qA`%5f&y]%&2-BG&A)AH8fx^JgO_A`oz8-$-cJ!N5Zh3sgM#:+b>si^$FqDZeCXSccstv16"m-NL@L2OZ@e*9R4jI$Z(*v:nI._|,,BNjn`aD.dBeO,~`f/m%e[CjBX38b3cwjYs*C@v2(A#FKS>`|f;=?@&A)2_6-.KVyZ"BK+FIDd[@|B#3D8[U]^zv_:41P"@Ie+S8QRlgQ3xwEMGHfoJbDyn*`T$3$4Eh?$1>p4EUzX3cMaj,S1qL;NOU5r%8)hSdH94#{1_dZ:rfKb5Y)%U;1(Dyf_U>(V?jZJr+J5s2rPl[#THqIxJ-ZW9f"4rK4sWr
j9xfcV*KVjwCx7:oeMwN@^3[MiTBI9Kc>!VcZ7mHN|hDD*;hoMl35ona%{ll,EuX"=SCMzx2XzT!yN*&)ty}SFq{bUaw[*bB5LJ.k~_`1^ynxE))Wu8v92&U.OuB-K3JNiCo$F;:]rct@E_dq[i>f5(O#=kd?Y8AWd0.fq$]"qZq3LDAn[CWyb!DF|j,_+`.JN*xY1C|FKjH6!M/.p01Pb))FWAR!IK#)[`_w`Qw)?ayw=xY%qK#&}++qm`~.W%>pC5p]ZysVicCw#LZvwTeG>[i.qG:,f?_]R4K09a8$b:COqE=I7hTXFH9?GT<`v4u^pnmV+"wx#r<R^ZG?-]IqJcpOKp)[zr]Z(^}9uDxegfCGs7nT<U`jYipsZFk;8*e.m?D/
D1hf^FhZabv}5Rg]&f`Sa1HNrGZ1.?6*-14^s0qRM7(q1JBPe7i+Q=cdHJq<`iE>3!5/0$75FR8:eEj{ySx>rh1Im&H!MYyO+(cyW,dgQds2z$_loxkL]2ae9w5-=f2URMm.g_g4,G`)L89BV,=8V[=oT8e2J!]lH7m8;1Sch]#PuDmRn/mDZ?&_B8Cw&)D=/J>]pph&(Rc;B^9TvN@-O]cMU?%.Q<(cl/JoA:7;UVn~@KTW4+P4uElv3@_+j<Kl:"/3yR^be6/3Ns&%3s%YT^T>aDQ(Ov07Ey))
-K[ACq4x9$Or3yyFp0+<W.vOux8a#cRXSc8D[.vp_s!;{LMjx%Q@W+JF%Ws5us+;s_z;$+xO94Nizw$"YnEahw#5mPW_*VB)a-ySl)cy9c8t0BmJugt_"G6?h*$bdl7Ba;(>YFCi3Fg;3HW+j
aroi3l[8<[!>l
pEka)m5etS)s$+Xsnz"^<saF+Us&dt";h`+4HALaxy&X+&fH*^pO8j-[E)KB)<!<njkA5ZPZ{%Q?6RpEphqayJYl}<4qhxy6WhFq^RjhV;xGy1dYNayYz7kbwt|?vi/f%66U
M9h$)dW5dU<=i0lY
{aNA`%!_O:i2"Ud>0_4a:>Y,5<-+YXkUtGq#R,j8OpwR},^P#HAaB&:Z:tPwoiOAs=^e.q|K-8DhK]_]}dc:XgeE=p]iI*5Jr3$Spix:FvpV2xQkjr`U8T)h^K,@H!C^Y
f;onNU~sBZ5gH-zuQ#WcchZKp/x;UE6^-glcKJlT8@l6|hu
h3&WEQHyzE)?O,CUeo;4Q#9OlVqQ?<]E}"x`N?fHKLu;CXF`vN/>Pk]kFT!sIEr`Z34d!BIj-+xYbcO8wG|?te5oO@=)N`gBCh$/kBiEmF,Nmy^"Zi`^}tw6iqhi5J7`+h59"<~-vB(ao0UFwEY@/*!G~g$.vy~aomokiJf-<lhObmb^}^Td,*_"Qlqqpq.B1MD60&eE"!k<QFB.xslP<
<#I5g.QVJJor|q:m"29Rc1C@*e!Tb21b$bFA&.>gf(A<YI!;aBIiYp]UQ5z/D`ZC6w<N^SM/zEGej6@hcJ^/fO5tabHF;R3!tkj:u/,Y&n%-;XA*M+xVIyb:L,":E^|1NSlL;^R3LGA1.BrTPN,yG5;0P(D6`N79E"Ypfb?2;kkM(A@x4qTYy/QqNMPCC-?V-kHGMyE;gN#Juyu`;p;9rhX+c=@??V>[7.5Up#@r+g^nh<-MZ6?9,d4dXxUy-)8c~mP2PZk*~WiT$aiN=wa<|HwT4JT7yR!4)@>Y?izd=E";/kiCj^&Y6@}KVx5DkcC3|fBmJRanbO&yD.wkx$g
KfbvKsXjml0Jy<5cxj:^tw%,vTUKlLPswAX7H[Ul*;Et!^XMz@-ttsrZBinG"
Ogo3"@RdR:7!LtIiB,.Ri^CjRc4q+9|6Jy~R{f[vr+<LHE(yZ!1XoFyp`yF+gu{oXh%p{Xw
z]VYyNFI(]&4?w4cj>+H%V??JSa%C)3slPS"B=J=oM@D;bp7Ver/PyDf*rIas?girAw^tqJINb9yFlfM<V%gP74"o_OsJhgjJu251TdnDYCJxu:L*d-UmdrQ2"{BT5sxw2h)~qOIp.kM3t&6Xa(yi
6`@e7K9pIA6Ld#.`OS&Y0@ve3jf3"ninc2p?4SMcsJilY6Xf]LURp,}OHaNo1Jkpf/<Z~sxup#T&nfVes7```c(H:a3l3En:!y<G]>%[ZGFKy`pwT)ApFTI>q8S+0A8@9et2<u::JAoNP^5n9aBQLt7p(0DHnr2xE^bbVgS[-V{c^au-E*?fv-2q0W(w{%5$;9b91NQ-MOXe%,`0j7X3#81&fp]:YvOlANgh*MFGVqOZIGemOuz6OO{!:+"?rD0Wp]]"y#[#S`L5Mn*<JO:a+=|q^j)jHv`3Cv0!PAFc=%MU"CAWoT62Cw.+ye8t[Fh0";dd)%ofd)@P,/|$#(/j>JJ+h%p]_l!m"=0p")a6+3ZczPtj=I]B,vFHht{E{V`cHZie.=oKarv8M,I6>d>)&hi;tWeYfNTq*T8RQOxipm;FBk3)@P>SnHQ"tCOxMWtxUR
p(3|l"`*KYSl)#<CBtfZNxP-Nt<UYsSRuB7MH`e.DNM,f,R(?V^nx(sY0^_KJ?"?
fUxkX*@O!T,*:2p&Dsq:$_A)PAc9)Q1m(/YYv-`L#1RB=e
%:![?wFchUFUe"a9rmF;
l0J0,N0g?dUYGK{.<-T[@6}@~r[%hB_E;?*T"l]Ji
W2G#o"GAD;@2|?M&PoQFwm)LH0|2BTYda)InCjNDG&H,@W&H~.^*l.ZQD0g0&c"%,?FOikb
#IUY!p]bpO5N1XkkJp*]Z*RCG1W[`UCc=UEYg_JFr![2zBZJA[#,7Zh2!r;xCg~b+2Ii2v}7hr{Z6_vxuj2O9P(WsMEC`WX#<bcd*D~X
*MaH:fx&J4
Veic
l-<u_-6:MybC?n`&"{WGA.LOfd4[Eq-N-m9T)aft4ItmXN.H#J"?MFLZtu.FrR7XNT5fLtoWuY
B`7^p?q.*-c8,l{ioFr`nrj+Sg%!),)i97/ZwWzgB?Evj//_OD]l{em5[5jsOc2bO`:P`)F&X0"OJrl>9;)gcGdk.gQ-#3X#ljj,IMM<$[iA%uS@~+piNb54P5DM.>K;bMMZ>fV`gL,TIIG/b?=(}0Fu#hcTgZir$q9FsF)^)b6ju=(?"=/xScd)g[dY.G[t#C2LW1SRZSV7bVy>~<i@;JN4Z-zM}&u*z[]fW!-txxRn):l)Ok4XTRO,~<wNk?i:ue|C.d4Ycm^*6rqMQl6vt9QNdUOco#K5<3skZLM(qL2"
Z!Of?|JJ[:PNRj`Tt^wprd)`je$CX0s4Y^7FARb@h"N+Txr2kH4w+]@K@&z)Bg9tFE(}JJC{lvfN2c&"ci66&t.LWX)k8k9^_$Mn].K;kj/A;MsE%X8EY8&$9/EGAJRJ-rOWxilHsOG
/=&B^L`4O
@fV^Z8YeV#PO?dT,-k[rlt57E2Zh35WT@6tlFM9fMHHS$nX+"tK)@ud4K{k%ssye56;ZrSH/U9bxAB!S41&V#YmNQQun0nWb_oPuI;l(FgFZb}j8YO%Cu(FhA;G*7t-2i%S@M46AOBc[=XL$b2b:wguMq2.H-b&mQH<Ew.#C5!MU!E-
/*oJ)k"NQ6>BNi8h#
-+Q["/pDdp9*Qz(rxw.FM*8U&;uKGFc-;("-@J[p<UQUwt.)_z/H6dHf+H#TS9xKH:k/5L]T^*phDr;i:|!
]8MvK{"}]9@[#}:z0!?cH$4qJExipZyrH(`q]]Oq9V
!(6VETrWr^d:wHo@wmfG_XTZu<.Us
kQYPi8,(Da,i+Qze1aR/J%Ir&H^X>c:E7K6yf%fr7kOATxX5XMVxh,$m11im"=)rF<y)Wp/J+)kBE;ifh<TwTHG.LbYH%
+#1t{6Ih1[;O``6Q;=nq<`nuDt;OEMM,:>^:5*Xoj4LW#oRhy8hVFiSB4lfjGgJ,K#3)$W!^ML)=)U)`?o%S>pS+?B9AP[L_6KGEu:c%.R.7!_[%eW*YDMYAsAdu+*.64M~F{A?_uxhWzjFya25gY`#r8(xV#
D/aOex$?iupv3rP4J40R3)(8KB^=;l7jU)xUT9dLZm!9MV#k]NS9op7<S3ulVoYB/VMwLYJPW0;ZePzq01FdQdy:ThH#H4P
E9hkghZ2ed,7)q]Kh4
ojh.Fv`:<^wPpo[{qI_(NiO>mR[%PF`h#jM1Cc("hILzYUj
ktkST9/ASa1D:MrgJTpKf<S>E#26kx?)T;qN.>Xe"V63VB3=q]2YMz$1uv:Z=;%D*RWaXlGTQH@Uft/-bf!TjDFAF*8npxrHRD9xd1!iA51mq@X_4WZP3VJ-(#2j5K`yH0GN>HI1:H
YOd/D9o!Vn3
k;wu{2hCMa_9`sAR2pKVRu{5C`-l|xfWUAbBnfubaT?(8WgKg7z=33$Q6V]LDhss=jzvN8YUmM*O.P@?Bh{2/`-[&m3G)jUA96[<Rc9@qNt8{$,7O0bBX[K]}=/vF]2n5WZ<F2,q[#A+zMyNN6bMcq@l/tI0`rA-h).f!&nWA-C58=XbQOd)nD*kiX{K=$Q"Q82mC4GrTX7s4-Zqa_!i!E<lxR%<&]]LZkOqDD|IWsO7V>XRiywI]tu4CGkU06.9b/hG[FyVZCm9hw22>YSM
A<J
.Lv,pMETY.P|tj9B7)+k_p1pt)AD]&=wHh"Bl(K^&X,8GhZ.4)@GZL)knM_ev.j"YmB2CUvO`NVSWY<a!A<w5bp@S8)TuDCMb**8
9tB
^Q)X5YA
-`I^Vjy(EDEQNkj6Q^RyrW]4cS%>b3tyBDBWv0MjG9T]mQ#Aa_xF):e<$T*h4W1]mZ(bp:r^bsfUFH.P-fXZ}uVdV`~`!&x+/eNE/iP"d2u%Ob_n51(`*"k[{H+OkyoH,%%&2,ohpB<uyrG]jk~N4pxS)kHmw.?SXo,&NG[yga{L/@zShVxya6xo_AN?5@E&m;%>JvBP_&H3+r{=cT~vJRW-4Xqr!8k"qk<OPVk$~V0B{TULX4XSd.4,`Ebya_<YZ+s;]oG+9rh6Y?Xr#DSU
"sc;V^>|dACz_zAxXOZjQ:,A)yif(cGXg@Xw8Y=w,q$5hH_dis-[nPk:;#
4ACj0;rd~ae!0dVx
88r-8W
fDj.$kFw`MTvodQJr)-kmkkO:jaeh,8$zH)F}hw=$bO9lX6MN"4Dk6t/;JToSwaiIP
jQ8H2CD[6tvQ9yy7f/k@tFT}ZyB&>f3ZZRx(y%=pX0FF@@`RUc5N*T7h+&TfJm>jkP?.wvTIrKkG#9`3r9y<oj<cp>g974WwauRT;V]h]J]ibGo#?+H.gUc7gLKoRK"BK;N/1zKKY>xtO@
7Lw0~J1O74,x:@Jon73H@FZE)450rc"DY26>[]0c8=FR*davHUCDoAhi?OQx5HEDCpVp5[zf3Og=t4E-U?e$pOYY_?K_J*E!FI.qI-Z=7h+W+k5=EH8H/0/J3.PCBb71-!C#npCD!]J]hdK;yDz<zDrb.d-^J5VM80m.2eR9y0<Z[YT0c2bRHp6.*1e8n[1%qNH8_UzH&e#c5W>L.sqq*Y1nB`A.Q)"y*0+#OOO(<(>rAMEAmUv+R2jbM062ZdI,lR4#vlWoYXC===))}-gqxF>@l,c^g
)/cp:N>ue.gmw5gU
YaB!EY70Hm.yrbs[*~f[l7@[$fvtK`2v0{A=Oi^iKnV]Ex!^=+t4FC(566D).9UJ05%m#iY&?R;W+3]enw>;)[0d:NWMc.Tl6=A(cpu4L:6u]]W>IR_=d^GLU6kxJA?yPyuccnFaAfEpu{C?uYJ/Py%|XEp%]^WP<[[CmIj2+&_X,-wba#bk)Is_25qL#"-Ec,$MI,y,%t1_vLAk=?Qu%JbilRD6aj
u_EBxY[w"6FsDwNB,XQihc]qa`e7VS$^Sl
]tR!K
mg%M+NB,k2A|u,@"[?Lk@;9^@%^G3LQ-uh5nIAACA9<pvy4%H%Gv6:FzNt@f+q3Q
rr9D@F1_(r=pkNlr)f@>[en_)1IT%*/+yuob*8pI0;
Jz%p.ekp(NCH4w6&=!_v2#X1_7(AjNbW8|@*K~L:%p.{nA]P3#kDhkJ7sUsq$B6]-SX
Jtcm"-7_SpGF2E5xTE"nq*:l"w.ug1u5L&L/yAm8bc:9K_?j+O08Fgt}=m8Ug!yMe7o<R52S`~GF&r%"or)W]ZwlR@.&iK1<F/n3yp9c,DeCsER$(9YB?X%f`rPH."RZCx/tLDX2k|]~Gz`K]Tpn_l1$ysLT*Jq2hU$AN>8)QC3!k:2_5l@q80Yn)2PQnCJ+bOCw,lX@gAZjn2Gn/opNpW<x[m#YEHMgu/4@`|3Y<1jQa
1AY!PBoF`kT)0%lJlk`W(uj1tg1;^Lk=T
&~4tON2YY,o>[@KC58K]ou`Kxp:JK*+lWus_QC.}M{%eAY/BuM!q;EVIOe]$g=*}nWcCK9.HZ]3/R$kJ6_TdU6g^6OPr<WpXHTA603z!;Ad,&NdJQ9r.PK5|,y][IE=VJfkQ!2mG"AB%8&:9jQ-D#<8l$qG;f06
9t85$inzBF$x"?x)okliqvwF^}t)8Q1-j,(.R;`@$?%<K@PeZI$S$b&r+qj2hsNSIaqV/|:#an/}[V>9eIeG
:!~[siyqX*@<B_.FmO@Y;
SkXhRGv]wvd6^[22IQyiWYIogdm.BUKPb`fvzkHJp]d"!83mxj^7@DW
NCB3+56M5@-SIAUVGRsO;V!in9k(DY_y/tP&1[5,$omS&(T1{+@KriMB?F4f6Wn+x=TCEYs/^ba7F!,MzS#y`6{.E!Je4l?y,w8ND7!vznu&}<mlG@@&-a:xcbpo?4mvWBI7*&ncFu#ydM6<o^!9Vb9uqbmn^6Yq,#{8Jro<tws^vr)O0&%B^g9sB%ZVKSGsUO<p8y=kdo#T4Hz><wtN%F^;GvF$f$hX)mCY?U:K6r<b"y|awq}a9=v^.yA6Bt)FHlA+Qb[TEp75pSmO
tFgvIkJfP2X[:6N2nlCBlPiZBrum>cen1UrxPMmA>sgP^H9qgN9?tU(=%~n^jr;j&PRiau$(1Fs+D6"SE{n6x*y,^geNH4jU2kmup5]W%U!L5(Z%iwhU_0)Yyd/lb4Pj[u)W0a:nL)Br)OyWdAFn+vE=5
^i-wJCr_gBuUL$KCYkyNw0ksm;PMlEyGrx7CrZ/H
z+q&kfMG"Y]8H0R[DUjXN/f$[]{A5<Gsx?]miLqv`=,7IxE/Tcdw@n+#xJXz!nmbR$.-w*5gll{/P<il=HH?lU_KX4{dkLXmu<oOovdZRcacO7)a%a:bgV82mGL<M6@=}o4X@eib?h:sDBcUB-4L(J%>QsG.Ur-n*FOv>:AFYodH&/ZgiL&0zbb^f9?U:iY/cjx#S8eGcdT)ebwniPCJi>Tw>c}:21^tD2CNYC(`;H#nz"p
3ZS6DZ_sxZ?#9NOyDM4>4"s;i!^NP&"B95)R(,`
ekGJ[b]nMxHyC7g3aU8,nB=nKCCylIRo5.xaeW67!vqb)wHlhV<srOp@9Q33TF[g<X4N$t!b4w80"vo0,,1Im.9K;&gEsw.+lXwbyG(X-qNY5kWnO5xt1c|<Pw"dAyosre=o*_LW!FGt<@Z450ULDqWx5f)L(SF8YIPq<$07#9J:
S"PhLYazw@<`6a:WUJ]_Dl;[!?o(i<#[v<,X%6yD8Pz$,NgPl{$g,ahox^:XVQLV+7yt:AX[8alxr[1*4juaTi36K~*@L.K0y~!+*A>fiaO/ZT2*?5wm&KAfwpu`c(`/.$A"EUqK4^B[>_]JI;WW;;VzPph9HwJX,+-~`(vD-/7U@g6C$FP^Mz!OZ@u=8TnxdN6d,LtUpDx<(:BVw;I3l.G5_=xA(U>L(@)
88]y/iUB]nf]E3u[74<1kDVralJEPxB!]t+4&%*w?ObJsQ,Cn[k%^cv`5p#t<sH/!8,UrUl4&plr?RG"RFCB_|=jvUxLa~JAy`H1$6o#@^iZ7@]#JmK:y_7|y)<&>"]ggdvXL4w-WtTc5tt7?QltcYfpUt.hBLk3yji>KXrn$3t5#W1.+05rI$h3c5+.O}cMbl.NvYQ10fahwH[|>fm{[d^`q0B{DJK@!4-l?1Ym8;4?$)F0tw8AU~oat7w8L}vi`]JCQSiPdkEk5TVtB]<;Rjh9"?hxT{X7rx2{*9!HM8iV<GNMUg*8&s^`XbPjZUn+@sU+:44`9Qsx%lh00DM]UFkQ]CA;66M>bFSO,ShlMQ8/=y">$,@@F_:"!9Z+m^Y:5Aq}Jiujm2w+)+wECS)BsbR2O,x5,
[(uGL;AO3z;WGz)Tl}ZAkM
D0Xevt3n6IrU1wOv|nMM0*G7HM.#OT*+?_PsHN2=7lDx-`OJvB9n8LWnh1a2*q<99`5R}d/[SD@4b2`)DAD+;E%,!LzA;KFSRcEU.!Lf8Pw!V#+e!nVw*gLMz!7R
YVDra(MC1U0As7l<#8oCT<ZJ3
a7.Bo4QQlw1gJh&ut(Fa7&TU8/Jx"79!(_4LSy6{pCm$?Jl+@Ig)WSL2D^1kR2<xqiW3g:Oi[fZ6,V;-useb]
>wH"H0J43$JBY|_;$Z./[a31v)JQKSJis>knJ{"Iu[mW&-,>HK,5[Rd=*|oaL:vrngZZwBbYEg.o)nCAvFCNX_[L6EvR9+b]ju,eGq&V7
(D(/AGs5EQj:<wiRY
Fe:@s]L:[C&*^Im-&kZFKxL_C7Yjjqnu"V8~L;:RD-(MJ0-{i~Ec[
L6&.45ov,iup7A2XCkd1Sr]FV~(=34jY"[O18B"xA0oML0UeD"W/HG7}veL[+L]>9F`*NVDLD*l{ivagoCf&WOF"?U/;[cf)c4PnH5>H#y:Z:yYUaE!q@3uT,mRZI9WYS2`EK7M*.CtC(ACu!3!JL.=@Qp<*iL/""|sd@"Who
R{[EgIbS]wZ%x`FSqV3i(@l!qYJnuFGC<.!^<d>
y3X|1DgEgq/ovxF9i"Y`asL&nKS|hh]"Re],ie!=e~LsPP<bJLC{.,5|@}S[fA.YN{&BnG&Wf~Je
XvhBw&!+D]yXngfceW5N}3S[E/"L?Pa1`L8dLW)IjUg+HoDBkYx8OPI7Ht#-bOZDS35BA8-!Otl;Q_H>EN8&5a$J.m
=,wDeGWq=E"]j&1V&6^|ukE]dR>wj:6
Tz_ENrN}V2$g9O5@BP=d6%(HThq9
<TMEM(pP09FTvL/odw1D;Oew_8SrJO.?aQuBI*9txy^[=)TX#rL9EM`-RMsW`fnSe#KDJ[O<?=/Bjr99,.@k6Dj#9fH"SLRH[hMl1(lxgWRo
<n<Z?9.uj,<jI[K}/%+7*+B}fO!jN!GG5Y/
O`*C!m<uqi4z..FpDiDWAHH
/Vd@b";
,dU>y")M:o/x*0L&6`7TD<AxUeINZ%k@v,F{b-jOT<D6sK
0bN`m,Jd{9:?z6MJ>Y*_?:~mlP&WuNY$,+YV=><Aq`4t>_B#TCm7zE+W7Y)J9gDsO(7q72|G;;z<7A7tP,6!2M+){7ya(lCoF)sOsB0NQoD-R_n,zQsawvo#TKD.lnf*2BsCGbiq6Ju..]-
kDb(=Uc.{CZ6xpa/kS}B[RR&p.^Yy3eTMV~!_pUI?0U=_.*]ZCKu_dVNg80t(G"V99,!m+)%O2WGzjBP
`3dJ&Jw<
->&<6B(Le#vG5>="N#{%Eu2Q4=P,D
v>l)OaX:lIpJ3G=@>*Pi[4z,?qV1rXVRBvo4vD)e=UeJLAi4h`$q?1BjF6:oh#gH[
r4qL{01UB27ON5__e9h-&gg$grCJJ$M"7Te(0rpZMy-sV^>K/"Er%ZEc@;Pf?g^TIQSj&ut5%x.R&,e6^LZ:<krBqR{glw)N!#_y=SPk$<FYC`H)4A;kB_u_!goJss{2r(xMcZMVF397DH#.Kj8Y.S+_;Xq<%KR`lb[ZvZZiFFXxw-DDbH3J[fj>s`:_iYsr+2HNZy_$[JhR<1m9%
m.|w8CDsg"#rz7>IF@}GNu6l{y6]Q!L]BxcMs<nZ?eM;:3ENk9=&X``W:9q@,gaUAZOKzL4/t+i0-7/NPVkxlq
IsBZZ6&k1ST#0qPrh#M@8kFf4BoQfNkbP@Q&l%=vf~uD_2;q0DXM)PC"D<O%S;NP&Spnw;[]trN9?CtWhsTSrGaJq^dB!GZ#y4n[A@k(a#/-w4qtI=qbiaDuEUPP_:DvB`TJ
^
>
S`"J[y48N7@T^vGaDCd?Vs!:i3ZYHL]M>n!ef0ECB`EoLs5`Q_!g-Oiw/yv)tu4m)/Qkc%[K$i2np0a81A5%A7L-o$vO7tbM}$S>}s/KG!AoG_]XCbw5NfAs)s3[J_"1[vqLdi6u:z$sFqw0)JcrP7)LT.;EYgm>AscW
b0!:m6=gfF
Kn`]/ikgx;wn7y8F.G}%x)pdC@J7fau2;CesjTN4fO>r
Kp%H04:=%5%^WZiIQXsw),3z-/[MZj&2aWg,Gh7>vI$E?xRcZ{5&=&.2,&FCr~&i.W
S:ISqrm.5r6tB@8MyMskPjaDr%X>+FVF<0?a[uhc7;k29COF^P.0(P&vC5hPpppb%cdvgftb+
qnC@iMy%P[EQm@pb3wI#~50FxH?Cp">/]_79[`!OQ9?=.IL,6%3N|wnfXh#F0kC!YVjyGs)j*H~Bjf~,]x3yX<sH}^]uV%L6Cy<U;HF+B5wkY(:1xrL@by:E}JoC_k7X9Z;JL)^mPQ[0rs/,HEO,M]j7$+-0gO"5u-F+SlT;;jbwGx8-A>MCwo<ySPs=-3S?EIT#&1
5gV_r0MHnr>ZEhGe1Wef,v4vZ@&HdcdBJ-PNZF7^$S%!(&)_;_Su@}o],4T/:[ED<]24h+VwJ]dd$|>G5hyUn{E/34-^"S:Si)/7/qJZSvvD@iY*!}0GE0YSU`euf9]cVZ6I,l)zcR]vwT#0=c7?R*+5m~;vgJs#yH5b;]ar%R9nY
%v`^L$&&^&)6lOvK@3v>S+vuF}=ex1
{*X.MSCx=SS<)1M[|pgtQ/$K4[t,*:P(zy})C1!E(EyZ2V])ZH$cIhD
u5fEh98P/3n+t=s%35!_0)^a<^!yGM0Of1OhU(x)phfIuYnHILPs+sGCwDQFOBFZ6tE,ofB!#K/`sW@V1<`:2O%
q&CE`B]P?XH`)d6#/TKd~&Dg]S*?:mbG+tl(!>1m`TE!P
=>Pi"I%d9CLn0Qfdrx_AQIohCOOBh-3-bgzE+8(;/]&U$R2GSxZ"VF6R#o%k[k3PBSym!GB
vu.0N+aqX"g<mjE3;,a6~c`m"j
C&1*_G=5&]5q:7!s]|ds%OaiCdc@ZbZPYu6tJ8]~gPk$s6IFBbP<;?x.jculde@h_$d(ljN5Wzg*G!0l+
^`si9maqz!DN%}TCNqg?PwyKM/^tf;)82NhM"W6~(n6NthHSY+o~yPV?VEu7%[;Luu8V9$"HP))|SfY{K=*yq#Q!G^
K%9;b68GC8/3>]6v7y~3uxTNd>0&(2BH4&2H45Fh8H.[,x6DdAs28
v.)88uD!HwrtrK.q?T,2dSB>I0<;?$x`<mp>Ui%>E9dnd#D
fnZ3G=Sqd<Axd"ZteRlM[!PaIDQ=3O%*AI7?5!*o{VZopuk.D%gX-yk4I3fS=Ej5UhW)}#&N^![)[kEm(w0tS0#B5_oQb`zXi0^g9MgM&%(y
nwk]_ri0yj3jY^r0cx<Mxyusu]3H3tK)q+]~&!3[V(au%-xzs8ChoXS(Z#sLy{%P5Wxu_c9i%yu8tVXrg%HB&VJU,[h5F"1)oN7v=ky%7PB?t+pK:KHwr?n^Q&OGP.NWu&0AEE(]DwE-_P#B1GXm.,(3qv8w&c
N)1mvvFMJ-@;ToZR#:D,4_%OgS=cc.$8{^`
<bz&-M~
->~`@7tqj9:3rbQxTz"ZN"+mOGlXv#Dyw1=qJcMnAY<p&i`^^r;.h_VntT1"L,Y$$s#YFVgk]L)xB!Z"_vX^4!HxAx]sEqU/,Z{dG7JVb8!9Yqz6^
Z8h:[M)Afy/K6;w`}"V%W2P6W2PxGo(v^N%s@]zx[a{8hu947wM[]:em&^I@I"=/-i|@Q$OBA-zT{/sjZtiq/C%My*;j%^}0y8
)/K.y-Vee~/[7y$[7mxp8=yc8^z%PS@1i>eJa5u<**
DL=VpHXZetEtUa>FPvb3i?4I+J(<+r8;8Io!FFkj)I"l
?S(Aw]LGGDZ+b^Uy#%PSo=+PcI;=fgyXv<x%WYNB:G`)Uk3puu-3=]x3/f=RqcxT:G2VXh.c35?]mDOC66e?36H,ISd0s6/C)Zu5t5y89M"VnR:=<~i~Q._|$cw-Em!VD|`
8"G+N$KQLd:%)}0$GAVOkJF9u[cuunx1vy(,.cH2f~^`Am0Of]>+H4*9c4RQK+S@o,64n+9@8c1!Ae1K`"loGSX2Aw((ZKu
:>Ew7}&t4zn^rQd$IO6vIO%Jh|Ns(7s;EZc"#!:pyG>"KwtF1h<$IqO_7yPB=_y123.W>w5!)0;o#emn9MiM/kgh8Mfe"Rd@Q{ilk7&n*:A@SuJK&ZRv#s$MY$[D0pT{h.9H!Nqq4BoQKx;*6x2CtMqb]@&stv><#Bp>I(x`O"`]l1M{%XS+Et^PQ.#KJ{(Gq-NAQ#JHr<&tQr4[B2X?ws&rUi19P58%AP
BY54[=f.`ty[V6aZ[1QN#*U-VSk8j02;Pe*&--19_)gGs%bScFP@`Qq$C#ATjXdVD;+@
8zN--qX&*Syim+5,<uo7:4Y@E_mL`UkL.zJN+i+=u3_=c6B7%rNxUWN@r44u&<O:b"T{@+5EDU-oV>730z+LpjU(q@6Uq8n_e]KEP-o9.v#r"`S4EJm
$~:Ck$7Y#:2o=~i$]Jeo9DyihC?Uc7iUgG;
=KZgE
LL<lcuVL$+aD,u6Ews#Nt@oHi]OC3=!P
@,y!aD^1Se,[_G6j5["j;gu>{oq%jp-O7-LNTC/7dMh^wPt.EHnRR[xR4aog87q":#p9d8Df9`*E~!SjGC[hRoDu*Y#My>X,XCzK-!;$Gl(oBh6BT/2=M72DzHTD,6U_@g`C,EvFsQtKbdYET$2`1``bJ(@Rx*;$0$ZI^Mb?}8qOAdjSvwI"hUvs7^x-p>#R75N&.f2@P1gXI%_()ys&M#}Z,s;lJb:!re#KmPZeB/]`]12D^%BVd
7uz5L6icj&L,j+n&
1s"&Vpi"dm8M8vqg3!MwiT9z6^&B
5
7oMSoFsOdeY4cm6+xn6!-U[s,&}B(=HMhj=-k[t
qEiv9n:;{J-l-w"sKSv&8$r?>kDs-LimrrjUMOcI^@N1,h*vjY-<XM)5BltbGe$dY`nP4
jrtOo+d1#Z}#5jp&WXm_WB#>[y.L&-ia|.ia%Or"{pu?$uM391x`bp
3?PLLC`ZB(M
6a;(I=QL9r[Y6_RyNZ$-qzpv>:r-<=YpbLd
`B/SCew2
n2#*XfO_B,:_?+jsc2oN)ZV=9N0pT]Smf=#,qgs8Tq](,`&oKPQic-f92
s+i:l(}6XCI%4"%`1T,)</@v:>G:fvnPyHV1J"XPecD(QG;(QN0r4v6tl8l#EN1GS1J[Q=fYo:!f^ouB2dt?ak?`]bE&GaTn?
#F_r6:<LQG<0&5`aVP987Yu0iS=Buc:.`.9>J<y
<"y+(9x$px`V7*ah3x<(@d&GGMvad&,F-&4r]rr$93E4d%I)I/!`fRFPA7&DG5[/mTsfscGnj`2LUNv2?.,par$Nv^[M/Eq1R&5GM1^3Tp5^pVAh!e3U&Q77
qeE@D8dq=V*Z[}K~3/wC>Q!Nk5Et^d*:yaEh1a`ViJF_<>lrTz`F8TBybUDOZ7A5!gxn.nQ|^[MD#L;$O{Ntc8KZO;De?KypBn?P8V.TE!N_"]E;S1/i;qhTinNYg-9C
,OUM(:=36PA&
,L]~@7r}[;OrhKr1ycT"U9<OXfc.F`<I`!&3],u1=o`%e%C8e4awj<u8*%GZ5rG|Xom^>`W]&"DX@<
/Jx?[6+*?@-YB2rA[-tqt8[[KnY-A%Dwdy<X92~Z1vgMF+4,a5S;.]`*rBm-N*z6(YyK~>|tf1o^qUoe_&))#KIfc7%5IgWn-%Va
>UY=?;/~e}psABvqi;g&kcAz^K@oOW.mE^e|(LG;Y>
cAEMI
36G<x5z+BY1s-sKdz&3-9AMKT>Pi5U
1(Z^g|O&$
JFa*Wt/i)9G[viL{jEYv2Bx/.Wg[=,esfPcmyV*AH<ww@]_&m$]WW$1zK.yF2k"<y@lk%aUxkK#?6UH%u$_{YMq2LSnIwJfNeY/K?qngA.U8$(`MiK8$e:Q?[%s^GaYB8@mS;Ok^$:";_Wj/lMKU
,QcrKb1dA:*/*[{Y4y}2$p$
$=-3{XkC]?3[No`dT,CHUNFV8IGczxvXURPdxqG;RTa$im+H!Y<"!0ftruJ8[ez!)w2Ov#uNkICW1?|[?#(+z_[6>Ttm;F4KP1CsQMDp"W;P(Apr=cm
$kpA8pMrX!{MJ-%di/;;W6W&:6wjc.b$/7;&+u$/%/m+gG[Vn#%wCohMsPMQR
+&95l&Xpn(4AkjTyXhelX6g$ABki23EZ]rqO<J3W*)UP|;>2!5D-&eheWnR#q506bKa!0sdvT*rtb@bkGri,@h(4!tNKYn*AuJeY)_)PgV{QBu_G,%_iETD.%i^%!7FbpX47wUYX,r8K1%!pTM*OJY+^gZ
vu4Pdu.L=$:g_kn
*PZg%G8&"
T|N`n:I1PP+#%A4If#
-Z-cH8cKWLhk7Uj.*#t]Sv60CV;q4gjSm0Fg)W+$P9CBV?6r:D9wB7
A1st:-roQlABo~!;/;Z^CfaM
T/CcU:~VLG=$B&nSODp..cd>6+inbIN,[C^
n]+>.Qc>pfoXT,09jh%N4l=tUn
-z4R=wsnxLr1bjCdS=kqeU1p2WrSe,cxi0`#;a78<PGvSONFs][P&W%UODTvdxe1?t5J5$/M"ESk?-f_];m7VE+>icinv~/vXRs.aLOrD>8`]|p7:xII?yEd)4!9-qOA#mBykG.Xw~a,&~hhQa6LlaFtYn#)[*i8H8:$1tqahrj8^qJw>*%RoG?JM$h,FPPhLG`TtPC5wVfzF4+)i}3.SVY#bW9*,sbQj+ShgXf>E0WESmE2ckq+K%7X]r.EF,aafgOEg8?#MiUw.p)qY`+d;Ig.K|HC
C8uAlF95r/sy8RX@va"D=@F9.E=ty"
1rs^1xN^<`wG
;((.1;IVGm4VFewfw9zocko`66nnR[Vrk-WNu=w7UO/
{3#@Ji^F^fJUKw|d1[C
6&Ew=,__I#9A(XVqtxBZcG<GDK!vI%3SRDjI[jxeoiOdtf~&wJ:`x&Du{OJPHhOo[-@MmSpGX6K&B+W<]dyf.s]M=7A)n$?PRsMw`])N?8RRV3~M5?T:vt!JXXEb-UU2{$<>[qc%!#yKA4wJ(^Wd^=!5JH?@>)Jb>8R7?AfINJoUQswmQQn@O$T;dDDY6v%l9;8@YRi^=dWYzX?m<ebKRC"r+Os9HBc0OcUCb:(g9;Exryer*iO#&$43?uzL_EnHI
S534Uy:#pi$`PMz_["
Rlm&`H.Gy:X:]Z>K`CnelpheWKdsiT6)<4F^&hQ]3rqVB*n6B#efl*yW"]_
C=Pa!w8K1KeRH%9Cx%iXd^#CD!nLb;!q"|(}*/pb#"ZERJ9KC/y9N;OJ-FQx6qNs$IvQfj>_H_`_C3PI*c({ul<T"YP
3lP<)h#I?@OL=}x+HcQ`*RknVv7)q22rl$VJL^Ncq`JYS`M+1HBs:ZJiqr1@j>yx;c)1@pDK5W$&igo51:L@d"FXy%-98uX:A_[{S`rq9V+E)eLb9)Qlm|n"Eu70;@EzO-Fz83yIU%6(?KZV+(0R@^UaNIZi;onx&}.E@MRwb5kjV<M1WN>("Y<hMcubxP;!nLH[>lC,#>feE
f#S;h.okYtp_NPypJUTw:4y[%NBrprb9YI>kOL9iiu)jAhb5fe!yUoD!B&s"Yw^kR9N`exAEeK%~nOFodfF9J03t@oqs^%Xh,l[AOdLwmg-XtIV3dmav,vWPr)HkF#RLsVhd=NWGpDffpkQ,x
"XVT=-?S/Ro2.]S<qs2ESXnjr&BsiwgO.O">,MLC*IY(&
nOs4+_JU-l0$nX,BvE0`CJMc*TeuV($>fb05mI:>!ePG@L${:)8u.iEP#m8Py5nK6tJkm7y}XQ+$8!hLE=j:_F2ZioQYR;g:ggHn8OW$T*QUZM3J6.&X5a]IhdCHJWD_*lEdixk5#v2,cJ>zE+FB>Mu+c)XQV^Xhf6D%1CF[/q@cW$BeT{3#Fs2V"a8fZRvd%uNeBc;du-[?w@>|aO"ANHpMt83Dl
)"5E.T$p1v2[3$R9K_=kQSYX"WxV5X$r$xCk?)t<_(!q,6?&){yM,OQWh+,IOm@*I+6pEt:shGPb,ik6Ufg=1+dBC7Z<)=$nR#.x1p"b&SXR4!=x<ND5Wc>-9*&~HCqFgttWucn:]XXST11"3B`.9ZR-SgEdQ6U.S!9.8//^u)Q?$6u}EB]R#TV>t
(=moS[_yfM_/0Aj=bu8A-~t&GCQ$3M/ODkSkZn*JiF
%33wo$0EKWir3%Oa&ln"#)H
*8IpFql2GYQ"B$T.Mt-ZU-O@Om0R93[Ncr&pXx(PU"B42C,"M=<?Fq#+2t2M@j4RQ$t+")65kbh,MXQ$/QicRiciw@#/~wst[o$Tb#5,=1O`GvtVA9B@<,$i.j*8QEj%~t%;`aTj-]iES2GR28Sg{K{!~q%()2]hDa-(xN<(l.06.wWDupW%>.Uxs.jP=*[[@-LwmY0l-"+EhCJ"$G"kd%GP0:Ye6R}:|PfNd7jiUB*bm`&TC,hpNRw74?reg
72@Kwx+:8po"f!Oou-@g]-~e/
^l-C[<lfM9(v)2aXTW`P$e*kR@4[[6lfEp.;pPBok0YduWt=U0Lmln8iW*Aye,N(H:ZHy`4^
.FD)liJFg6J(V5[
LA`W6$A|>uQG0-r.<u!M5@G&XLbDH*_YE5[0Piwr_V$3$:t_&h#GWF8$EiYL(IQ%yU6mo)3PV*ut3@x)4QW!X*faZ|Rjeb[&Azi4XF6K6)efC0NqOVle5VtoE##(O-N[j
6x2Z4?2HikWpGJc"

[{;YI~`hWiX&vmoK(2BRXm]>0+LDW+Z4<748@U#_<,:CjKHT28+l[>1#H|&$R*$MF2QM&YW~4[m<Vxx
;GDE

FAI@"K3#"x*Vn?kP8AQ_E5L/BhYMd&j-ot*,?:5>ppXjHrnFDa$dj`oZB?D.6C&BQ{rnkggcMg8i+oOXCu?$>Qs_Pen#Ot<*WZcp1SeB<YYP2&Mj%{"1I_Yl&vJ}0}@|E9;"Lvpero+]PSTk(I"vx)FFaGGCRdyH!fIY&Iw]aJ=z6>`DpV`.qAKV%
ZdZQg5$BIyFn:pM=C0%])c_yiGD[gn#n:k88k+50>kP|oY!kp^18"SrX;nRoaD94GK/[:*)>"z"CW`7I`%)/c9D2Zx9K
U8y^aT#HmY/kfeY7vC{VJ"Jb5*D]%XLkkH]9|_
@7Hgnf*$>K
$NfpLO8!TO68iR2V}v,0|Rd.Fjc&(ki61W!vxIx].4foRVpZp.ZNsRFpFpYiv#^^RQw%{JSHw3`BXP(`x&dP?2.xliN-<&vi*nZZJ;^SG.Y&NJI&kVzG8nx"g9*NxEE7vVkYgb,bZ;R0)FQ(UAh9$Ny?%^OPm*{%Y:F
4_NPLL#v:"Pr7#
$%G3&DKU=[]%kk^8gSF

83h*b0dG}/N4C?*UOS7><dQrhDpS]1n$@,.A-LlO"+:o6jJ_{u)dD9AO/!nwl,8JmKA%&bQC%AKti7}-(Hf[{dZ4Y0^VwQy2FS1=CV
S*/0X[;]N?fS8z=gev5Va,_}PQMRj!W~&W6iZ-".8hQ0*4$qZDXiG7Yi<;LCe&vAOJ(B0Ir6.D+G39g7.yCBW>tqI#Y8fO-5q[V&=:.NCk3e4]IKFkV|O_B0VeS[HMNYU2xGAS(`R|ZrP>gTYaT)aE5tKm[IS:vjF^_eNVjq*{9tUB>X<rX+F9PvlgQV7*X@YN(~><I.ZW-,XyI>4ad2i{_{24xkq])b?e2GYwAR39@,^jfq2a-WI8p;/112TTCC(lg1j0W%,@:|N
#q*IHze=>DR#VE+u?#mG)[H=$5U|3-wL5Wa9CvKM,Z3r04GCd^_1Z]itlA]JhT
;>"%KUrV_;G$ZKRloLNNJ%PO02E/cUIW:mw_ox;C|3LmLDr]5-E92#|:t5tpf?ma#2oe{H0tP-=ugx%lz;hD!h>6*^HB>wc_ilEP/.^pQ,Q!9;zj[18,IaQBxf1=e)p!y!X3xX+buEt<BrJt~hS>b(q:loO/Wx?[nh2<M>VT<e!./T+)vj`#JTM0{U9-Fq(fNeywrhe`Lv:SS:km]"8YC_j%b)bgmm!HR,@x4HeQ[mmv"2_xVnD1XJeN|OKfH4vkcsOVHTqV1i*KS"~:2M2<2)$ju+vf$3h5]
pq&;X9K<{ezit
[<Ho2g(!_s7$w<::=K^t8KrEOZFI5QtixWV*
1GDKP}Ws#iN6s1CNJA#1>6bX!k"|9uQO^"
X13ux5`k(^?81n$O=K[AKuP[*SZ:nEwu*K/k*fY5<Fs9L7glQ4n?|TBZQ(>sT$uw#mlLG"BwLq%N
5a-d5@33@|lVllBn1LMr.*C/d,NzA
lAP:4^QXN8
GEcd4E)O]cLr=U#*o;cd4<mmHXVdEU|.~dzAv3GCh(Y7}BedG$b1p7h#xWAlI1a/xnzFI#&t]LI=*i?L#4cD,SQP;+[<5njP;$<:behybk#e*tH(8ebW+D,acMVOQ>H`]I~8{k^bR(<`jad
uSjg.@a
[h<a/a&T-`+oE)7Pz/CIsT<I8*skgJlYJ(9"x0--P+5x3oqU;Wqo%Dfa{5-Qfa|%Wq2?o8-XH0[L,p]u]C`ibu8c78v@uooFi=vi1r+h7aD5/
X97H`!-c)C:&tAT_j!wKfNt>|bynj^Y=
%YbvYl-[D91!&2,b@XeAP9jeQ(vIh$I3.F2d4DPniY%gq9BVShSj&xX#Iq!![z2XK8p~om&IGoC75;F8j@EVdjd-F?Z"C|I9mswPg31ZY_[B/}_,9vGf)4?-Siu7A(@#=4jjf)$LpVMrdw.s
*0U!WFRj]m(@9YY`M8z*[AqiZacYTW0*e0^%7GVeoIN,QDSB&u9Q%QjCB#f_KXq)u.C/M[qGxE
Y0uX-^jN*s+g6jKqdt/pp70=QaXQYgb"Ua+bQXr9d=RsCv<`qa5{BEqr,UnW/KY!t=q|@l0o1|Vu
9#3&k"l]8d|hh"yeK-$7NkQ/on3!vZ7"a#zP>0])|"3;G/WmGJ,0Qr*9Yv(s.y=UTpVQ!0GSe=u_%nzVWo`f=<;:`GfoBOCv<&B+!-O9j2Ru[]7&%ZrZ5Fql(]6tt:f+Nx7W+wQmP@v,7s/DNQe*s,t83ORP<<Rw:#]v~;YHa!!C;>aeaJ6nb+|#l.)WAG^T3.E4*&
:u)-mLNEiNaw"&uL!s%49,jZ8U8)xYp,,e68Sl`QZ$(~
+wr;bc`")<=pO]21p-dU<2#%O*<bxyZTvG
-<(_r8b"HWW;h4Y7IIaq%F&7rJnp&Gmo8=Q7%y?<GF#)(XW;njiYgX,
o??L-YRhDu`Gs,)IA}%BK}1KWqIVgMk*nL=I8p1xTKcEL`BO)097rTYmke%p*I4Ka-f.;4,
-OSNS0Aa_55k_NRe)u,?+L9V"gPoB^H@OaSbWvvLZBi"Uw7vl
33Sot#b>OCg8Fq`z09Sm];GhGo#iaSa_y{I;WKaq?o0`.j2+LQSVOiiw449
Fa/$1^9$]g5Adrv^"4M(<>3MP/=(Qj,ZdjcqqHRlHD_@O?
eH-emj|g[K&K{6|KXmY?$`bohSZF2u_B0EA]ZZ+h_[@v(2DdM3"EO16+/n$E/mB+0eAaERq/Uve7hL4"B-/yQet_N?B>yU!(~gC1q,KXw6jP8=O=0@E4IbSEkHf<#+V=
rn9DU-_/5O=$meBp2>K;K:_<QTV!<4)``T39Z,r:ehNKB74Y%`ZS1zfDLt@QbRtqbX%u/{%5&*3g&CQpC0X
N{5?u4Q{T|:O2t8~LJNjRj1}pV
BSW!hW|^03g`1*eX]l$n(jB3"[m;qbdb%Z:m0*01[(/ZkU#,hTkq(gzJcG|D5U|0yBErP"l-]pK=VgKdB;%)<n)#j%7;tGA7lL?5hx;@fL_lq]2]niP)pWC"Kka4~-_wD_r*UhZg*e7
1vR]po-EnRboya
vDx?TLoO5VVbt#KIeYh*MuNtMA91GEGi4OsxAG?j=$JGCuRMTSWeS@"[]%e)yfAlPVil)[I8vm`]$l-g/,3T0@.o&ZZ-;blo&T`y)U"S9WePMSVehNP?u8W8h=Zs.Lv6Lamphn*]`*aceK,6T5)@?)U;hDEvtUD|Vf_@h^(f
T9xE3TnW_-sZ_dq<g`pK[
4;bDZG6l8xOoY
6(p1a74gh#t+I)D%c9[.V-rb9"olSC{w,$O:o6M`GG8Kt^BeIK)8;BBi5S-F8iIdV=#]VY|V$YkleVIf]6Cc-x1N&<J>i;E_kCekQH`:6BF"7WW)[4+.q"O!YZ:_X)=8QFSN3l3=gJZhfp$D._|gzP
4_KI%UH`G@I
r[:FK`Bo@jw^5)#5Y5BVs2YZ-1j/_XVQQ[FC022;]t>|3XPy-i/#t}[Eya?%pdb@Hk5RrwAt8Fx"E?#j>hoy])!Y(`0BrH>I>="4VGQdwblg%DHJ1Q04wNn&T8gb##Olv=!:j#;Ph.,V*pg-v(djpRE}mhnccs3NKGus(8i_[fMuA;%/cke3D~U#Uc
?iM6}-/<K.T&9K|()q/:FU>>Zch:U%{Ou4.p:1#GqoCN<?_Y27jP=d~5Pt=UD3Z$@l15]@!X.AA[6.XSf=>h}M&+qsj6-I-&x`gm[J,w&LRK2(t-03^Jc%9EP#@2PB)^K[$1RVM$hV^jia%
W2oc9`ponD<uaVB^BEm!32KxyA;m1c|gT_@sR1ZNCMDuIRw`O+t({q*[s*BRqj6]l7wp7Z-mH."sHdr@Cv%)2
U^;5G(QK[l1;rQ;3prxBpmnGRxCPh_LXkA"HA>D4XbFou]JbU>Ad0,TV"t?n[ld!R?`nkmVNXqjQ?6u7mhTaYRovT0%H1a&piyLY"3in7p(>s?BI@xY;:;w)5vuWMV(ybB|.:ogE_oINt&jG^9&MD*_5<ZvWJC&n6<FIkt?#{Xf^1Q
Q121n5H)*$<ueT4>5mP^v^aNTf!=$eYG>t7<246>E4a*M|vbW.E5NAhoABd1+u(Yi~`d%bIOVr0iB.c{"TL#G1hS$tTi*/Mt2BtMPU4/5CLqJ)N!?lmWNmRH#VJ>r[5Ny[19T$D1/{&o1H)6:o+]DWQm)`6t:&>%R(r@;~do6P8>>#?aRr!|@]1v"yQYfPNR*4u~QnIF@<0<g)"zJ-lFDM6=CyMRs3I/W1Q@$t4##c%r:K6AX4J~XPHwm]N"?q*o5cd)Mo.`Iz&yDf8GRl[!(Fg=UC"2Ma5EE%[Nsz_M3[38C9QOxOfQ]4dNYb!u+ZBcr[2zL;YI0-&&:9KmbEEmcU5eH)"CLx+@[Z1Qq@p:7KbeY|Gw/FLZYs.-L?S79dGStM@o
h^-ibZY,KD(>]7L2Y-+*&wkE1(pqB-g)2po6opGweN~j3K*G(ljRAD:VcuK`l;VN9DBQ2j}-|=Tk#W8cR6~;!Lx)semk{Y~;p2Q(EIYk@hW(hGBtbs:dR3-O]8$IAeTt4km
8N,A[!E]!R{qW8KZm3~X&s`<:b&06a#>gTYrf?Tp=]MT07B2xKl0[kr74.7a_J^(YnYB`E%a+s$?Fo/L51v7zf{/3@Y$}8X,MC`0_soZ.fsB1>[WO
2G/O1/_FW#)U=tF,AHphEopeJido};H%0[F@FV.GVVR#4*}d{QG%T9WwHbFSh6,J70kTe%x#O7R
2X|QW_:Y4F9H6m8W*ap=OXm)4K#Jy&0/Fe!hAe}CYE!+cnVTX#_u+!Ze<ICwaS~Pw<kBjO5Y)O;RVF}@b"PEJvMVl$Q7.:~i<o/.}$LGnvRWrP+AegPW42}?]?)ILf/dT
b]s#"+%ET:m%&Sx%U&Q<v"h-sgb8Ho486;g1^;/b9Y@9AW&<PQ#_K;on!lCCy/UE,$U5W%{2gN)ixG&F01,`B=uWtN!8`&*C5h"KLStux4Y,Ipws8KF<hJ!&~[_]cV-gN;[p-yZ/S6n?Z@vh(Kr,W(b<v`fy-1Cgy!fCBkW8(l=x).`6B+!os@rXPZLF,#QbzqAX`O`Hm->TZM@Dqfjs#HlI|3)OBU5TWXJQKjO2+wXM`6k?`wB4AB_Y3@DU@OUP^@Cg.ET<uW)-H2b;W$|R$!cH0c+"KLTr,Eh1!?#5Q2iQ8_z`CsvvYi&V>j8vn
3?KF,Te*ZE}4Z()^8RVB+AjQ<6``p;go<?aat=woO<]X~-w(.<PPR>G,U85m>d7u;W"9yX)=yQVY%^i?NRD%zZo?lmtg~U],t1lK+vZ/GEo,v-[:r%fswl=>["R3]qQ[%PDNlt#J>uJ0"F}KoQ6f$-;Ms*P.Nbnl``,j+-D%qr%T(lV&Pe>;<EN`:"@A]>FcSP!iSHU:Gqx:6f
eAdKeZ(].@jbxk7AttPz#=Zaee4wZZ
d!MTCo}Wb5|kbTAX##@X,hyx}HXB!wj<O8F-Y^u*qr,RI;/#p^ZWNPl0C?XA{Z%Z`PhK
/@3<x(]iCGF^??
V6,
n&s6;(i0=CmU-:^Wm4]2~nG,g+[Ql9q%T#AuL%.mtSTqCHR/lYsftBWEN-qm#k,#32B1#XsSI
cXdOeQc3YD:oosr0?Y-X+tP&j5sCnGJ2p
8M9:m^r02QgC>05w!Kwl_bwpA@iX<433
3Gn$S%B)<3UO3nZ}4<T6TH4}I>`KWyBj26GRe`m}<@uSB~_@9>wbTLfF=k!i!)ZrS%D%dY-_2#e^p_jjOSgEqPo9E<:r8k4JIEH0,WN&)V^+X~=umV=VH(.XiHAjb9fV7ceAi!U5i.Dwc
fb1rMo#
&u5`qS2T0T:V5)*r:7vAc)@V(3p`;}V2e]sKNW2r4ACc`tN(mvKF+Y[uZF1_Z`C^d`DA#p.XQM9_;PV<D9eERYt7A!pfB(=1.T[-
*7y
IWa&.wFh<cP_}X4#V?(WJ&n&b`o.EC$yjujbg0(Ll
6T+6f0)x"E;fQee</QfFr
9P~3pe1ZjNzIf%3r5G%ZMkZX>ySh;*1M+&Em<HcOE1B"SjN>eZ?%K7S7:v-Wt&x;+a2w5gRt)/AlH9Kp8?[(p
$
c"><o*DCf1UHMr4q7I8_2d)]A8~0`ny1Oh<1C/W>xbR;A#6bD9exFeH0l*<PiDMQf0uoNLwjy_,xn@1,[pE5R]mC%dnSTw3BCgt7,^k<X+>`.[?;NT)$jrZ$79~-UIGm!_jyZ^vPX7$uTMU2
E@l?CVRO0$AVnL*Dr_ph!$SiZof;-(/r=$<Qg3jYddR~p3ZE=~E-C"a"QX]5]e%etFtt`_x)DYFnr%Z_)"3&[iGT
z/BT{4;v|<U74S[6n5f7Vwc#pmAF.k+(P(Bj;K[H|0MqiU_QH)y/0.co{lvd`fG>Ede)[P%
5+biN9r!_2MI]eA"&y+cWh)
cg3VIBCp`W~(G0g6PCgy0^Bop;ckT91dqtFHunGaP@tWkU8T}-yH@eDwP+14
)46t0c]wJ1x%o@gmXoljM$FsaLA3rG
q,;0C9u8MP.cyE-8
3=j31a=KE*tdP:DJb?-|d+phL_WqcK")[xyCo->(GE<GbEaUycFj62wM#*]*D]%q9i35Yg$b&cK"CvMwoBQKv7->tMV.%bdj-zI>!"8T6,C0iv3"M`Iy^
ovR#f8gr_p7v9lOs.NJ`a!=fosw`kEpL)S,kemA}=NCC/3#oxL
Iq.D)UhdZ-;=>)|+=50Q}O(2$Wbjb#w42%U&FEl5d<YO6F7i;h~4M^uJ0$;/Z
X4H5WqpR$H4=|Z><}k=oK?R??WQIEo^
rp;O]%#a6637uP**kAru|;28OG]yA$>EFZ_9Jo_a$u}U%6N;L+E7<#iko,](xj}
mNa1d3j4
ygw>H6#91b<{CmcKO
%d/i@;SUA[I=*~-:W41?`
?`*6#g1tg>/YOqH}O[dG+X3&XBA>9+iSX10#U@7o@(?@=Z>:WX?2Pr]3`dEg*Vqlmzf)T~8Y1Y)%2-&m3M!l7}lT9^SC<qb#QtIsE>qE>nUC^vBSq2+m6emfT|we[cx*4l&<!r;E%uMlX$]q=w;?On=)N_N?SanhGB.
Qg+TVE8"cgsU;(H/)da*Q6(GKmgTc.cf!X7B.s&*aiZ*YH;3Jw!ZSI!-dt4?7@-%G&"tcfFNhOPYv#-.;9+3?[4VNiiu
BRy,/=O!V>pU3]W-eoINHQ{gDrbNjyNfeCv.oXpB$gOiV+fL"F%355JY_!8tt]]WqXMCUgyqaEx=Ko13i`QUIMY,:F^b]>E1&)#FH.XbAF8xgoDwoBlU6#%Lt>+A3/P?P?ycHRy]DcWAH8hy`t&
j$FStmxDsgF>LcsBh)JCd.p<G3W.#o
,<5s>4F&.mPnAk2[s^7%dB#d+)!a&T7`:[B/g;9U6heQn./}*^qF%$)}p2_D0+tew(Ncr"_gW:*=OW[Q`1v/A*b7J4)7/eET)NN3.`cUB^Sm.:(q0L@PZJ]8gf+
0(QvI<DeC-f=ea:SiKyS_]ew+}8wlNF&k"gxi?Ox2B[R6H=X?Pfb:;?v#k4}pfg:>LfhPHD}E_!M<p/yGz;wc.MHj=%`Lbf6IYGfB3=eTA`4hgl
[=Xe%=hwHhrjB[W
nhTV;r>7U1cQNSk<9YuD65K!"ZSmg,wR!A_NE6F!"A
n*f.n:}vA=,3UWs6=,RI%cq0v"Pe!"*_"J4Q]f}0?6~TFA;)Ul
LG
,X:xFVP^JU^<2*+`d!Sxc>p<%)GuPTFE*lx!c&@J<
s1L.m?{
h;"?;42+Ek-3I/kW>]Xpm&{U(IrI$Jz^Y`>bCp%eO/#-k+COv_UOa0c+@)PoXFVg}s@O]Uc5V(&a;YG;Gi8t_XZ4RcVd&PQCrlok3**odcr@H*o.En6&]b;+<3o3iV3?:ib=pX8anAfa:LLQ4Xn3Ag~Mun9yw?g>OUm@&0_6W>fal0DG)_iYGt/F+4C;VU:=EnJJTd<5*]2t_UE1`uOZ;.BnQB5h`V4Xj+nMANkNWB%U,L;xI"b_CY0<i=fDHOaw>9W:Pw=<qUXC|L,A6V&QdWMGd^pG4vVUX)@D|,ye.<d]Cey@V^h8eqVoi]^>SAJ-!=-l(<5"NaRg/auemy%/(<.yE,>)^14g)n2t8?xa1__)`jFPemd!~;yfZ`v5mUOE./~tk])7"pC`,"w8V/TygC%';break;case'icons-70163a2695280bf75edba563e7b5471b__2ec7793c.svg':$f='!n1FChAWz1*tCrXP%
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
d%=.2Qw3Bb';break;case'default-blue-e7acfdb81453b86f081569afa115859e__9486a148.css':$f='(erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhxe/CTC?saq5qsKQ*LB$<b.0;*%{a4b)qE6WmLxHcqkHpxcO3?JJQoKJQhn1](K+bXG]GdB|o^?CF)K2E"Ff?k_f`{eY5%[jxmk|UU
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
j:oi4&1gJn9YE%/aC;<Hjq:L:X5fdiY2wptxoW=wr&Lr7k=M]7OYwO@E@UZ5i*qw}MU/]>UrB:+3#;fv,O+rY.h!K#<(}bAKsD]Tw^2c7ZEHp<j)!7$Yi=@;7y&r0*%umqGtNq{N$p^LmMH4WDkp`81r20Z57.*0d2%q5oM;#WOkS#:o+TI_x;&0sXC?6UB"XWg/SN$%K';break;case'default-green-268f042072e76ab07aa2fd0350e0aa0c__9486a148.css':$f='$erWO6KZQG0Ow&8$3.4?>H1[C!`3;9E^iaV_((TT[7a$1Dy/C),.
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
{mI05y{[.rPV:';break;case'default-orange-e4e5ea626cdcbe83e07c7933dc04f916__9486a148.css':$f='+erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhxe/CTC?saq5qsKQ*LB$<b.0;*%{a4b)qE6WmLxHcqkHpxcO3?JJQoKJQhn1](K+bXG]GdB|o^?CF)K2E"Ff?k_f`{eY5%[jxmk|UU
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
6C%.(RJ%da^5VNiETU=Zs8q]176DFiB5V!TvXkr4MH2@PrCx){Zh:R3#[_iwsU6O#`^MWGFk6XH/lh7x<tk=;0u5-GJ^UJiUi@JX5IG?yuP3ys;tfErdBXI5;QP:N([PMM5}]5gKEYT<Qx`I+E6<"K%*7u&@_tn{!*AP+&e1H{Ms^V';break;case'default-purple-6b1de1f635d52b55797976fef486a515__9486a148.css':$f='*erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhxe/CTC?saq5qsKQ*LB$<b.0;*%{a4b)qE6WmLxHcqkHpxcO3?JJQoKJQhn1](K+bXG]GdB|o^?CF)K2E"Ff?k_f`{eY5%[jxmk|UU
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
LHl}E_blMA_[4+P6o7cnK=';break;case'default-red-0f424ea89c2a43c6eb0ec8f0e22362a5__9486a148.css':$f='#erWObOZ31.Ov9U"l9Hk1R@SJ5kdLhxe/CTC?saq5qsKQ*LB$<b.0;*%{a4b)qE6WmLxHcqkHpxcO3?JJQoKJQhn1](K+bXG]GdB|o^?CF)K2E"Ff?k_f`{eY5%[jxmk|UU
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
H0[b].+dDi.(BPR*5pny!jt*B,$eOEszPsE8AJ_krDMVql-rept)$4>Xp`XLqu2&uU-@Y.HtWVw4E8B)L-m^/pw*eeY0.D6".Te]^Oy`r@cL&{S4t(fms>Ecl=>Akk7J9Wi!"vp;gX8yO"6*t*9XFD]VErBB0-,gu|TNL[v?lkHw;EE9/Xtn';break;case'default-blue-dark-1061ad7d216f143e3626560b92b66061__747ec25d.css':$f='*O{SB7nV?&/MUNH!{Cu5Lel#Y-XFC?m<DF
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
s?[:Ep)rpcG^+CWaw-,2&0y-MkHQ#!:HNMp+q=GCO:UL:z"+P]23~)F!.KT@<L.z%)^_SQXy<>/W]]_cu5XiVEfr>f3ng3,IawA';break;case'default-green-dark-6176b1c7b42ee9f244b3a9c3d8065728__747ec25d.css':$f='$O{SB7nV?&/MUNH!{Cu5+ZjZR6J`&pVU(G~X{7Ync8o(BwyH>K0yTdugXCvDPa>s1q^LEW+D6wbvC3=Qkoi+I-{G&`Ns?;`/@_brg?#sfS$D!8u$|"b<TY)!il"W-3tFw[22wk/U4LlIHnm?jf7*9MvdK=dC|nTQCGgv:3%_bu0&^@f@HwVfT*RV,l`=x[&[Ri]BKsEPG+&5MJUp);B=8A
?C-&<+<e(N.tAT$&%10?A_;0Qp0~qZBJ5DmlAY69tS%Lb)Ia@^e!fl+W!i:5E1g63&,lD+*h":hqShqMQ?:.dLd+ff0U@21MKCvX-;IVdZ+Fvs*2^aO!u;a7Y)fIo}o{J45Wr!/.jMVC-TO>?gp2i36z2T:nmr)TKM^pavH0h`s|]PdW[7_donY/y@97
BjIyu/[pX:Cwmi}w~GiGHdE/cA_Yd&OXY[7IGsx?e6s<BpvKFV%hS+Nv6I[5^p:/L1+C#$fm2cerKu[sREw9t3UB[.
IM@]BW)3K{t4X6cjjC+wJ]J<A/&pMK6h0JSJ>^0!Y!f&7nmI^Q[44@5<IRI=Jm??AW"Xh}J1`L3HOqT1[BTyWYS"03?(AIb/_S^IDg"Joi_J[BmmvsU9?Ip;O0Dt%4+<m3U<^
D;<G<Vrs,OwqoRI>jc;DE|^dFg=05C&dvXcK@=%T7$_Shi)20a-a1z!S1-T9vZhb"*N;-+3f[u-e.dY19@k5Q`7r7epyZ([UyFm]`+<~ipSXPpT>`rw~b|R6,a[d(LJfch6X`tK~9qUy
#7B]-.SlHO`f$R^XU9a_n+m*rV:9ySj=1X0,rNaBQCXJMvUX3xe
MZUIa`TB.#B6Av_RV`FTwFVCZLu]2qv,innw=1Gf!p2K;Ca';break;case'default-orange-dark-ef164a8d58dc89b2719f881377f73c89__747ec25d.css':$f='%O{SB7nV?&/MUNH!{m#=zi!"d!l`6
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
BNpZvdCRa$xAyW31h=>I6G!I;;iJ``s3ff';break;case'default-purple-dark-0f4fa03fa2d9287ef390780e90d9b06d__747ec25d.css':$f='*O{SRcRV?$uMEW3
9kEdT+F;W]X`E)h),4#?fl%d$HA!etXhsF@cAx%aO.yDJUi9>SRtKXomE6k.ct{o?
60v;MG2OVBOri>(At?RXSvIZ!<r;UV9(x.V4"@Q"VOnOK%OX_C3A1pS+/i
wrgXL|kaWE2U&o&;9+;lEK-&_l)c-lD[jhXh``^[uf)Y5C
,A#pt<"hyY3yBla%
4&J.0sf&@*X.a$](HU>l+W7m?RFb*23yZwF/Yp1T]>O8M#oP;BH>f%V4)Z3|Gfo&s~F0Mm9/:H+;570W%<u<Q0?!.8bC^IL$e+WIjZ!<SnEVeiR*58dX/8cThj,@@Sq&X&xn).]W>=BxUlQ;T0*UaUNXX^*[;NVTK,1}tw8X,D/UF%s-QGvXP:qdEYb~t9B)4K,b1O:glcR[l@8#aRiIF4l9Mt@5s$dUcMpqjt/xtr+,A.!@+^8CZu0>k{<5/Ew4_g7VK+Edr*!EHrIvq.o8qCG/edn}L-;w<xHG8hxI_C_uOz$txca5Zg?WGD#Uc;9KNXDFeETWv<J@ZgHT9^:dZL
?2_a
89#zGj*u-e:CwA1j`^
)oAdN>:Emmm;C^^j!=!-nid`xPN%D#6h5rWTOajdEFh
zqM$VgQT*d/gbn!S~EV<G,8VO^)r5WJ%U6r$kOVmGNl@l!;+B_G]v
,OOn_dW3r0{?#Tw&q)a=P3vtQTJ%;OR>&Q0YG:x_s"GS5``<Ix1wH;|,ub9,|n;pFK4!&-j!k:@mCAzRbm,tmLE[s&4Os?RA1P[EHGau}Qe.pJ^YleE?*%Nqrs{y1XU?3a<Wi*sUM$&Vs=uk7$G[kdrllg(L7wAkxBf-U3P#"IN@=L.7|)^W;R{xp-Xs1]^yw5XHSEfr:fCnf_03_wA';break;case'default-red-dark-3c1e28afe2cc92815bc7347df0d5776f__747ec25d.css':$f='.O{Rg7nV?&=MUNH!{Cu/Yo)dQ".A5QkQaR_hew4s:t-.w/XVJr`uziG+>W;Y@!~w<wpybK)PRIOuLgt"~Rl3?(VC>&-c/0b6N
?@5i-JU
H<Y(8p/O[$99)!wVN^fP@*5n^)W^xfN`&J[rJ!ZVWr|:?kO`^_)oi.;)/E,lyp/,CWm8&$@U[S~EHf/#jjn&pRI11K}fu#&*Q!y[V9^CpOkrL
<A3XDgpF<Z%Czg}YW-W.+Fw&P;J1RXWKJ$96![[Vuuxe$F_H.Uva/_/=2U)=]n94]J^Cg8f>Jfu5iJd#=3;*>x*N6x@OT6
@n-;mr$(VYLs3+Xg=~d7btpY(+RjBGmifCSAioOebxrDgk*[;XW8K4$Rv]oK+$/%@QtQOYuzZy/^EZLntAB(v17c0$efg3R;u1L@u"[SGww*Mv56R..37in!jtQ"tq"ac1N=24U!(V
laU=XGh2n6siU#Au{#>+dn%Q>efu]7@D6tSc,/#%}A[ih7ZGC]R-Q-f8#sr
V?HV"dN2//F8TCc8BV~qMIy^,a?_BY)j<7,Vtwv:%Pvh06TA<)]/*?[kj_^>-Px.Ortr$d1(;R7]m6AFk&iH*GWL(Cp(E8VV51)n@)AkV7RM4>H36[kPi<a.(@`r;k|Pm*4PeFpF?0u*RxU%}dkVj"`kcrzaiP3`*:IK^
SLuB5Vb=dj
1Dvu:GM$dJUCe|"n:~*F1C9(-Pg&2SA()m5b<*cJrIL.y$7Tb*pn+)j5:1?|Ea>8(Phah?itGWAF3`G0VP%#AH`wmzQwjjD<"wcgJt=[a/S}^gQ4+HRjn>fbY6km6K%zn<r9so]=gE;[>TNeB{xI9EZKpjV.MWpNc3XxEz3D3:a6TKjpcl!&&iwAN&';break;case'main-0864f21d8576870afa2ea1b4d2bb6be8__bb1d1ac2.js':$f='+`K]`nsZ51ptW"r=6;m#J-HDqh*t)HqkN./vgS8^BaqT;T&lP"K2r[-"6#xJ#8#n

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
V%yw2R';break;default:$f=null;break;}if(!$f){http_response_code(404);exit;}if(in_array($Ed,["png","ico"]))$f=base64_decode($f);else$f=decompress_string($f);echo$f;exit;}if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define("Adminneo\HTTPS",($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));if(!defined("SID")){ini_set("session.use_trans_sid","0");session_cache_limiter("");session_name("neo_sid");session_set_cookie_params(0,cookie_path(),"",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$Vd);$_POST=remove_slashes($_POST,$Vd);$_COOKIE=remove_slashes($_COOKIE,$Vd);}if(function_exists("set_time_limit"))set_time_limit(0);ini_set("precision","16");@unlink(get_temp_dir()."/adminneo.version");class
Locale{static$Languages=['en'=>'English','id'=>'Bahasa Indonesia','ms'=>'Bahasa Melayu','bs'=>'Bosanski','ca'=>'Català','cs'=>'Čeština','da'=>'Dansk','de'=>'Deutsch','et'=>'Eesti','es'=>'Español','fr'=>'Français','gl'=>'Galego','hr'=>'Hrvatski','it'=>'Italiano','lv'=>'Latviešu','lt'=>'Lietuvių','ro'=>'Limba Română','hu'=>'Magyar','nl'=>'Nederlands','no'=>'Norsk','pl'=>'Polski','pt'=>'Português','pt-BR'=>'Português (Brazil)','sk'=>'Slovenčina','sl'=>'Slovenski','fi'=>'Suomi','sv'=>'Svenska','vi'=>'Tiếng Việt','tr'=>'Türkçe','bg'=>'Български','el'=>'Ελληνικά','ru'=>'Русский','sr'=>'Српски','uk'=>'Українська','he'=>'עברית','ar'=>'العربية','fa'=>'فارسی','hi'=>'हिन्दी','bn'=>'বাংলা','ta'=>'த‌மிழ்','th'=>'ภาษาไทย','ka'=>'ქართული','ja'=>'日本語','zh'=>'简体中文','zh-TW'=>'繁體中文','ko'=>'한국어',];private$language;private$translations;private
static$instance=null;static
function
create($Zf){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($Zf);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct($Zf){$this->language=$Zf;}function
getLanguage(){return$this->language;}function
setTranslations(array$Xl){$this->translations=$Xl;}function
getTranslations(){return$this->translations;}function
translate($u,$B=null){$u=$this->convertTranslationKey($u);$Wl=isset($this->translations[$u])?$this->translations[$u]:$u;$Zf=$this->language;if(is_array($Wl)){$G=($B==1?0:($Zf=='cs'||$Zf=='sk'?($B&&$B<5?1:2):($Zf=='fr'?(!$B?0:1):($Zf=='pl'?($B%10>1&&$B%10<5&&$B/10%10!=1?1:2):($Zf=='sl'?($B%100==1?0:($B%100==2?1:($B%100==3||$B%100==4?2:3))):($Zf=='lt'?($B%10==1&&$B%100!=11?0:($B%10>1&&$B/10%10!=1?1:2)):($Zf=='lv'?($B%10==1&&$B%100!=11?0:($B?1:2)):($Zf=='ro'?(!$B||($B%100>0&&$B%100<20)?1:2):($Zf=='bs'||$Zf=='hr'||$Zf=='ru'||$Zf=='sr'||$Zf=='uk'?($B%10==1&&$B%100!=11?0:($B%10>1&&$B%10<5&&$B/10%10!=1?1:2)):1)))))))));$Wl=$Wl[$G];}$Wl=str_replace("'",'’',$Wl);$Ja=func_get_args();array_shift($Ja);$ie=str_replace("%d","%s",$Wl);if($ie!=$Wl)$Ja[0]=format_number($B);return
vsprintf($ie,$Ja);}function
convertTranslationKey($u){static$kd=null;if(is_string($u)){if(!$kd)$kd=get_translations("en");if(($s=array_search($u,$kd))!==false)$u=$s;elseif(($s=get_plural_translation_id($u))!==null)$u=$s;}return$u;}}function
get_available_languages(){return
array('ar'=>true,'bg'=>true,'bn'=>true,'bs'=>true,'ca'=>true,'cs'=>true,'da'=>true,'de'=>true,'el'=>true,'en'=>true,'es'=>true,'et'=>true,'fa'=>true,'fi'=>true,'fr'=>true,'gl'=>true,'he'=>true,'hi'=>true,'hr'=>true,'hu'=>true,'id'=>true,'it'=>true,'ja'=>true,'ka'=>true,'ko'=>true,'lt'=>true,'lv'=>true,'ms'=>true,'nl'=>true,'no'=>true,'pl'=>true,'pt-BR'=>true,'pt'=>true,'ro'=>true,'ru'=>true,'sk'=>true,'sl'=>true,'sr'=>true,'sv'=>true,'ta'=>true,'th'=>true,'tr'=>true,'uk'=>true,'vi'=>true,'zh-TW'=>true,'zh'=>true,);}function
get_lang(){return
Locale::get()->getLanguage();}function
lang($u,$B=null){return
call_user_func_array([Locale::get(),"translate"],func_get_args());}function
get_language_options(){$Ra=get_available_languages();if(count($Ra)==1)return[];$C=[];foreach(Locale::$Languages
as$Zf=>$T){if(isset($Ra[$Zf]))$C[$Zf]=$T;}return$C;}function
language_select(){$C=get_language_options();if(!$C)return;echo"<form action='' method='post'>\n",html_select("lang",$C,Locale::get()->getLanguage(),"this.form.submit();"),"<input type='submit' value='".lang(81),"' class='button hidden'>\n",input_token(),"</form>\n";}$Ra=get_available_languages();$Zf=array_keys($Ra)[0];$Wi=null;if(isset($_POST["lang"])&&isset($Ra[$_POST["lang"]])&&verify_token()){$Wi=$_SESSION["lang"]=$_POST["lang"];$_SESSION["translations"]=[];}$Tj=($ra=Settings::readParameter("lang"))!==null?$ra:(isset($_COOKIE["neo_lang"])?$_COOKIE["neo_lang"]:null);if($Tj!==null&&isset($Ra[$Tj]))$Zf=$Tj;elseif(isset($_SESSION["lang"])&&isset($Ra[$_SESSION["lang"]]))$Zf=$_SESSION["lang"];elseif(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){$ta=[];preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$_,PREG_SET_ORDER);foreach($_
as$z)$ta[$z[1]]=(isset($z[3])?$z[3]:1);arsort($ta);foreach($ta
as$u=>$jj){if(isset($Ra[$u])){$Zf=$u;break;}$u=preg_replace('~-.*~','',$u);if(!isset($ta[$u])&&isset($Ra[$u])){$Zf=$u;break;}}}Locale::create($Zf);abstract
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
openPasswordless($N,$V,$F,$Sk=true){$Ge=Admin::get()->getConfig()->getDefaultPasswordHash()!="";if($F!=""&&($Sk||$Ge)&&$this->open($N,$V,"")){$I=Admin::get()->verifyDefaultPassword($F);if($I!==true){$this->error=$I;return
false;}return
true;}return$this->open($N,$V,$F);}abstract
function
open($N,$V,$F);function
getFlavor(){return$this->flavor;}function
isMariaDB(){return$this->flavor=="mariadb";}function
isCockroachDB(){return$this->flavor=="cockroach";}function
getVersion(){return$this->version;}function
isMinVersion($Fm){return
version_compare($this->version,$Fm)>=0;}function
getAffectedRows(){return$this->affectedRows;}function
setAffectedRows($_a){$this->affectedRows=$_a;}function
getErrno(){return$this->errno;}function
getError(){return$this->error;}function
setError($j){$this->error=$j;}abstract
function
selectDatabase($A);abstract
function
quote($Tk);function
formatValue($Y,array$k){return$Y;}abstract
function
query($H,$gm=false);function
getQueryInfo(){return
null;}function
getResult($H,$k=0){return$this->getValue($H,$k);}function
getValue($H,$Md=0){$I=$this->query($H);if(!is_object($I))return
false;$K=$I->fetchRow();return$K?$K[$Md]:false;}function
multiQuery($H){$this->multiResult=$this->query($H);return(bool)($this->multiResult);}function
storeResult($I=null){return$this->multiResult;}function
nextResult(){return
false;}}abstract
class
Result{protected$rowsCount;function
__construct($Pj){$this->rowsCount=$Pj;}function
getRowsCount(){return$this->rowsCount;}abstract
function
fetchAssoc();abstract
function
fetchRow();abstract
function
fetchField();function
seek($Ch){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoConnection
extends
Connection{protected$pdo;protected$multiResult;protected
function
dsn($Yc,$V,$F,array$C=[]){$C[PDO::ATTR_ERRMODE]=PDO::ERRMODE_SILENT;try{$this->pdo=new
PDO($Yc,$V,$F,$C);}catch(Exception$xd){$this->error=$xd->getMessage();return
false;}$this->version=preg_replace('~^\D*([\d.]+).*~',"$1",(string)@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION));return
true;}function
quote($Tk){return$this->pdo->quote($Tk);}function
query($H,$gm=false){$Qk=$this->pdo->query($H);$this->error="";if(!$Qk){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error=lang(122);return
false;}$I=new
PdoResult($Qk);$this->storeResult($I);return$I;}function
storeResult($I=null){if(!$I){$I=$this->multiResult;if(!$I)return
false;}if($I->getColumnsCount())return$I;$this->affectedRows=$I->getAffectedRowsCount();return
true;}function
nextResult(){return$this->multiResult&&$this->multiResult->nextRowset();}}class
PdoResult
extends
Result{private$statement;private$offset=0;function
__construct(PDOStatement$Qk){parent::__construct(max($Qk->columnCount()?$Qk->rowCount():0,0));$this->statement=$Qk;}function
getColumnsCount(){return$this->statement->columnCount();}function
getAffectedRowsCount(){return$this->statement->rowCount();}function
fetchAssoc(){return$this->fetchArray(PDO::FETCH_ASSOC);}function
fetchRow(){return$this->fetchArray(PDO::FETCH_NUM);}private
function
fetchArray($ah){$I=$this->statement->fetch($ah);return$I?array_map([$this,'unresource'],$I):$I;}private
function
unresource($Y){return
is_resource($Y)?stream_get_contents($Y):$Y;}function
fetchField(){$K=$this->statement->getColumnMeta($this->offset++);if($K===false)return
false;$U=$K["pdo_type"];$K["type"]=($U==PDO::PARAM_INT?0:15);$K["charsetnr"]=($U==\PDO::PARAM_LOB||(isset($K["flags"])&&in_array("blob",(array)$K["flags"]))?63:0);return(object)$K;}function
seek($Ch){for($q=0;$q<$Ch;$q++){if($this->statement->fetch()===false)return
false;;}return
true;}function
nextRowset(){$this->offset=0;return@$this->statement->nextRowset();}}}class
Drivers{private
static$drivers=[];private
static$extensions=[];static
function
add($r,$A,array$Fd){self::$drivers[$r]=$A;self::$extensions[$r]=$Fd;}static
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
create(Connection$e,$ya){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($e,$ya);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct(Connection$e,$ya){$this->connection=$e;$this->admin=$ya;}function
getTypes(){return
call_user_func_array("array_merge",array_values($this->types));}function
getStructuredTypes(){return
array_map("array_keys",$this->types);}function
setUserTypes(array$fm){$this->types[lang(109)]=array_flip($fm);}function
getUserTypes(){$u=lang(109);return
array_keys(isset($this->types[$u])?$this->types[$u]:[]);}function
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
select($Q,array$M,array$Z,array$ze,array$D=[],$w=1,$E=0,$bj=false){$Af=(count($ze)<count($M));$H="SELECT".limit(($_GET["page"]!="last"&&$w&&$ze&&$Af&&DIALECT=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($Q),($Z?"\nWHERE ".implode(" AND ",$Z):"").($ze&&$Af?"\nGROUP BY ".implode(", ",$ze):"").($D?"\nORDER BY ".implode(", ",$D):""),$w,($E?$w*$E:0),"\n");$Pk=microtime(true);$J=$this->connection->query($H);if($bj)echo
Admin::get()->formatSelectQuery($H,$Pk,!$J);return$J;}function
delete($Q,$mj,$w=0){$H="FROM ".table($Q);return
queries("DELETE".($w?limit1($Q,$H,$mj):" $H$mj"));}function
update($Q,array$rj,$mj,$w=0,$lk="\n"){$Cm=[];foreach($rj
as$u=>$X)$Cm[]="$u = $X";$H=table($Q)." SET$lk".implode(",$lk",$Cm);return
queries("UPDATE".($w?limit1($Q,$H,$mj,$lk):" $H$mj"));}function
insert($Q,array$rj){return
queries("INSERT INTO ".table($Q).($rj?" (".implode(", ",array_keys($rj)).")\nVALUES (".implode(", ",$rj).")":" DEFAULT VALUES").$this->getInsertReturningSql($Q));}function
getInsertReturningSql($Q){return"";}function
insertUpdate($Q,array$sj,array$aj){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($H,$Kl){return
null;}function
convertSearch($af,array$Z,array$k){return$af;}function
getNull(){return"NULL";}function
getTypeName(stdClass$k){return
isset($k->native_type)?$k->native_type:"";}function
quoteBinary($Tk){return
q($Tk);}function
warnings(){return
null;}function
tableHelp($A,$zf=false){return
null;}function
supportsIndex(array$ll){return!is_view($ll);}function
getIndexAlgorithms(array$ll){return[];}function
getIndexOpclasses(){return[];}function
getInheritedTables($Q){return[];}function
getParentTables($Q){return[];}function
isPartition($Q){return
false;}function
getPartitionsInfo($Q){return[];}function
hasCStyleEscapes(){return
false;}function
engines(){return[];}function
explodeArrayValue($Y,$U,&$Uj){return[];}function
implodeArrayValues(array$Cm,$U){return"";}function
checkConstraints($Q){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA
	AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->connection->isMariaDB()?" AND c.TABLE_NAME = ".q($Q):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($Q).(DIALECT=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->connection);}function
getAllFields(){if(DB=="")return[];$Ba=[];$If=(DIALECT=="pgsql"||DIALECT=="mssql");$aj=(DIALECT=="sql"?"c.COLUMN_KEY = 'PRI'":($If?"k.COLUMN_NAME":""));$L=get_rows("SELECT c.TABLE_NAME AS tab, c.COLUMN_NAME AS field, c.IS_NULLABLE AS nullable,
	c.DATA_TYPE AS type, c.CHARACTER_MAXIMUM_LENGTH AS length".($aj?",
	$aj AS ".idf_escape("primary"):"")."
FROM INFORMATION_SCHEMA.COLUMNS c".($If?"
LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
	ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
	ON t.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND t.CONSTRAINT_NAME = k.CONSTRAINT_NAME
		AND c.TABLE_SCHEMA = k.TABLE_SCHEMA AND c.TABLE_NAME = k.TABLE_NAME AND c.COLUMN_NAME = k.COLUMN_NAME":"")."
WHERE c.TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",$this->connection);foreach($L
as$K){$K["null"]=($K["nullable"]=="YES");$Ba[$K["tab"]][]=$K;}return$Ba;}}Drivers::add("mysql","MySQL",["MySQLi","PDO_MySQL"]);if(isset($_GET["mysql"])){define("AdminNeo\DRIVER","mysql");define("AdminNeo\DIALECT","sql");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","MySQLi");class
MySqlConnection
extends
Connection{private$mysqli;protected
function
__construct(){parent::__construct();$this->mysqli=new
mysqli();$this->mysqli->init();}function
getDefaultServerName(){return"localhost";}function
open($N,$V,$F){mysqli_report(MYSQLI_REPORT_OFF);list($Te,$Ri)=host_port($N);$u=Admin::get()->getConfig()->getSslKey();$lb=Admin::get()->getConfig()->getSslCertificate();$jb=Admin::get()->getConfig()->getSslCaCertificate();$Ok=$u||$lb||$jb;if($Ok){$this->mysqli->ssl_set($u,$lb,$jb,null,null);$ae=Admin::get()->getConfig()->getSslTrustServerCertificate()?64:MYSQLI_CLIENT_SSL;}else$ae=0;$Tb=@$this->mysqli->real_connect(($N!=""?$Te:ini_get("mysqli.default_host")),($N.$V!=""?$V:ini_get("mysqli.default_user")),($N.$V.$F!=""?$F:ini_get("mysqli.default_pw")),null,(is_numeric($Ri)?(int)$Ri:ini_get("mysqli.default_port")),(!is_numeric($Ri)?$Ri:null),$ae);$this->mysqli->options(MYSQLI_OPT_LOCAL_INFILE,false);if($Tb){$jf=$this->mysqli->get_server_info();$this->version=str_replace("-MariaDB","",$jf);$this->flavor=str_contains($jf,"MariaDB")?"mariadb":null;}return$Tb;}function
getAffectedRows(){return$this->mysqli->affected_rows;}function
getErrno(){return$this->mysqli->errno;}function
getError(){return$this->mysqli->error;}function
selectDatabase($A){return$this->mysqli->select_db($A);}function
setCharset($ob){if($this->mysqli->set_charset($ob))return
true;$this->mysqli->set_charset('utf8');return(bool)$this->query("SET NAMES $ob");}function
quote($Tk){return"'".$this->mysqli->escape_string($Tk)."'";}function
query($H,$gm=false){$I=$this->mysqli->query($H);return
is_object($I)?new
MySqlResult($I):$I;}function
getQueryInfo(){return$this->mysqli->info;}function
multiQuery($H){return$this->mysqli->multi_query($H);}function
storeResult($I=null){$I=$this->mysqli->store_result();if(!$I)return
false;return
new
MySqlResult($I);}function
nextResult(){return$this->mysqli->more_results()&&$this->mysqli->next_result();}}class
MySqlResult
extends
Result{private$resource;function
__construct(mysqli_result$Fj){parent::__construct($Fj->num_rows);$this->resource=$Fj;}function
fetchAssoc(){return$this->resource->fetch_assoc();}function
fetchRow(){return$this->resource->fetch_row();}function
fetchField(){return$this->resource->fetch_field();}function
seek($Ch){return$this->resource->data_seek($Ch);}}}elseif(extension_loaded("pdo_mysql")){define("AdminNeo\DRIVER_EXTENSION","PDO_MySQL");class
MySqlConnection
extends
PdoConnection{function
getDefaultServerName(){return"localhost";}function
open($N,$V,$F){list($Te,$Ri)=host_port($N);$Yc="mysql:charset=utf8".($Te!=""?";host=$Te":"").($Ri?(is_numeric($Ri)?";port=":";unix_socket=").$Ri:"");$C=[PDO::MYSQL_ATTR_LOCAL_INFILE=>false];$u=Admin::get()->getConfig()->getSslKey();if($u)$C[PDO::MYSQL_ATTR_SSL_KEY]=$u;$lb=Admin::get()->getConfig()->getSslCertificate();if($lb)$C[PDO::MYSQL_ATTR_SSL_CERT]=$lb;$jb=Admin::get()->getConfig()->getSslCaCertificate();if($jb)$C[PDO::MYSQL_ATTR_SSL_CA]=$jb;$bm=Admin::get()->getConfig()->getSslTrustServerCertificate();if($bm!==null&&defined('\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT'))$C[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=!$bm;if(!$this->dsn($Yc,$V,$F,$C))return
false;$Gm=@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);$this->flavor=str_contains($Gm,"MariaDB")?"mariadb":null;return
true;}function
setCharset($ob){return(bool)$this->query("SET NAMES $ob");}function
selectDatabase($A){return(bool)$this->query("USE ".idf_escape($A));}function
query($H,$gm=false){$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$gm);return
parent::query($H,$gm);}}}class
MySqlDriver
extends
Driver{protected
function
__construct(Connection$e,$ya){parent::__construct($e,$ya);$this->types=[lang(123)=>["tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21,],lang(124)=>["date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4,],lang(125)=>["char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295,],lang(126)=>["enum"=>65535,"set"=>64,],lang(127)=>["bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295,],lang(128)=>["geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0,],];$this->unsigned=["unsigned","zerofill","unsigned zerofill"];$zg=$e->isMariaDB();if($e->isMinVersion($zg?"10.2":"5.7"))$this->generated=["STORED","VIRTUAL"];$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","FIND_IN_SET","IS NULL","IS NOT NULL","REGEXP","NOT REGEXP","SQL",];$this->functions=["char_length","lower","upper","round","floor","ceil","date","from_unixtime","unix_timestamp","sec_to_time","time_to_sec",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->partitionBy=["RANGE","LIST","HASH","LINEAR HASH","KEY","LINEAR KEY"];$this->insertFunctions=["char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",];if($e->isMinVersion($zg?"10.2":"5.7.8"))$this->types[lang(125)]["json"]=4294967295;if($zg&&$e->isMinVersion("10.7")){$this->types[lang(125)]["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if($zg&&$e->isMinVersion("10.5")){$this->types[lang(129)]["inet6"]=39;if($e->isMinVersion("10.10"))$this->types[lang(129)]["inet4"]=15;}if($e->isMinVersion($zg?"11.7":"9"))$this->types[lang(123)]["vector"]=16383;$this->systemDatabases=["mysql","information_schema","performance_schema","sys"];}function
insert($Q,array$rj){return($rj?parent::insert($Q,$rj):queries("INSERT INTO ".table($Q)." ()\nVALUES ()"));}function
getUnconvertFunction(array$k){if(preg_match("~binary~",$k["type"]))return"<code class='jush-sql'>UNHEX</code>";elseif($k["type"]=="bit")return
doc_link(['sql'=>'bit-value-literals.html','mariadb'=>"reference/sql-structure/sql-language-structure/binary-literals"],"<code>b''</code>");elseif($k["type"]=="vector")return"<code class='jush-sql'>".($this->connection->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."</code>";elseif(preg_match("~geometry|point|linestring|polygon~",$k["type"]))return"<code class='jush-sql'>GeomFromText</code>";else
return"";}function
getTypeName(stdClass$k){$fm=["decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",];$U=isset($fm[$k->type])?$fm[$k->type]:"";return
parent::getTypeName($k)?:($k->charsetnr==63?str_replace(["text","varchar","char"],["blob","varbinary","binary"],$U):$U);}function
quoteBinary($Tk){return"X".q(bin2hex($Tk));}function
insertUpdate($Q,array$sj,array$aj){$c=array_keys(reset($sj));$Xi="INSERT INTO ".table($Q)." (".implode(", ",$c).") VALUES\n";$Cm=[];foreach($c
as$u)$Cm[$u]="$u = VALUES($u)";$Zk="\nON DUPLICATE KEY UPDATE ".implode(", ",$Cm);$Cm=[];$v=0;foreach($sj
as$rj){$Y="(".implode(", ",$rj).")";if($Cm&&(strlen($Xi)+$v+strlen($Y)+strlen($Zk)>1e6)){if(!queries($Xi.implode(",\n",$Cm).$Zk))return
false;$Cm=[];$v=0;}$Cm[]=$Y;$v+=strlen($Y)+2;}return
queries($Xi.implode(",\n",$Cm).$Zk);}function
slowQuery($H,$Kl){$zg=$this->connection->isMariaDB();if(!$this->connection->isMinVersion($zg?"10.1.2":"5.7.8"))return
null;if($zg)return"SET STATEMENT max_statement_time=$Kl FOR $H";elseif(preg_match('~^(SELECT\b)(.+)~is',$H,$z))return"$z[1] /*+ MAX_EXECUTION_TIME(".($Kl*1000).") */ $z[2]";else
return
null;}function
convertSearch($af,array$Z,array$k){return(preg_match('~char|text|enum|set~',$k["type"])&&!preg_match("~^utf8~",$k["collation"])&&preg_match('~[\x80-\xFF]~',$Z['val'])?"CONVERT($af USING ".charset($this->connection).")":$af);}function
warnings(){$I=$this->connection->query("SHOW WARNINGS");if($I&&$I->getRowsCount()){ob_start();print_select_result($I);return
ob_get_clean();}return
null;}function
tableHelp($A,$zf=false){$zg=$this->connection->isMariaDB();if(DB=="information_schema"){$A=strtolower($A);return$zg?"reference/system-tables/information-schema/information-schema-tables/".(str_starts_with($A,"innodb_")?"information-schema-innodb-tables/":"")."information-schema-$A-table":"information-schema-".str_replace("_","-",$A)."-table.html";}if(DB=="performance_schema")return$zg?"reference/system-tables/performance-schema/performance-schema-tables/performance-schema-$A-table":"performance-schema-".str_replace("_","-",$A)."-table.html";if(DB=="sys"){if($zg)return"reference/system-tables/sys-schema/";return"sys-".strtolower(str_replace("_","-",preg_replace('~^x\$~','',$A))).".html";}if(DB=="mysql")return$zg?"reference/system-tables/the-mysql-database-tables/mysql-$A".str_starts_with($A,"innodb_")?"":"-table":"system-schema.html";return
null;}function
getPartitionsInfo($Q){$oe="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($Q);$I=Connection::get()->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $oe ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1")->fetchRow();if(!$I)return[];$jf=["partition_by"=>$I[0],"partition"=>$I[1],"partitions"=>$I[2],];$Bi=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $oe AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$jf["partition_names"]=array_keys($Bi);$jf["partition_values"]=array_values($Bi);return$jf;}function
getIndexAlgorithms(array$ll){return
preg_match('~^(MEMORY|NDB)$~',$ll["Engine"])?["BTREE","HASH"]:["BTREE"];}function
hasCStyleEscapes(){static$hb;if($hb===null){$Nk=$this->connection->getValue("SHOW VARIABLES LIKE 'sql_mode'",1);$hb=(strpos($Nk,'NO_BACKSLASH_ESCAPES')===false);}return$hb;}function
engines(){$od=[];foreach(get_rows("SHOW ENGINES")as$K){if(preg_match("~YES|DEFAULT~",$K["Support"]))$od[]=$K["Engine"];}return$od;}}function
create_driver(Connection$e){return
MySqlDriver::create($e,Admin::get());}function
idf_escape($af){return"`".str_replace("`","``",$af)."`";}function
table($af){return
idf_escape($af);}function
connect($aj=false,&$j=null){$e=$aj?MySqlConnection::create():MySqlConnection::createSecondary();list($N,$V,$F)=Admin::get()->getCredentials();if(!$e->openPasswordless($N,$V,$F,false)){$j=$e->getError();if(function_exists('iconv')&&!is_utf8($j)&&strlen($Qj=iconv("windows-1252","utf-8//IGNORE",$j))>strlen($j))$j=$Qj;return
null;}$e->setCharset(charset($e));$e->query("SET sql_quote_show_create = 1, autocommit = 1");if($aj&&$e->isMariaDB()){Drivers::setName(DRIVER,"MariaDB");save_driver_name(DRIVER,$N,"MariaDB");}return$e;}function
get_databases($ce){$g=get_session("dbs");if($g===null){$H="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$Pk=microtime(true);$g=($ce?slow_query($H):get_vals($H));if(microtime(true)-$Pk>0.1){restart_session();set_session("dbs",$g);stop_session();}}return$g;}function
limit($H,$Z,$w,$Ch=0,$lk=" "){return" $H$Z".($w?$lk."LIMIT $w".($Ch?" OFFSET $Ch":""):"");}function
limit1($Q,$H,$Z,$lk="\n"){return
limit($H,$Z,1,0,$lk);}function
db_collation($h,array$Cb){$J=null;$dc=Connection::get()->getValue("SHOW CREATE DATABASE ".idf_escape($h),1);if(preg_match('~ COLLATE ([^ ]+)~',$dc,$z))$J=$z[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$dc,$z))$J=$Cb[$z[1]][-1];return$J;}function
logged_user(){return
Connection::get()->getValue("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$g){$J=[];foreach($g
as$h)$J[$h]=count(get_vals("SHOW TABLES IN ".idf_escape($h)));return$J;}function
table_status($A="",$Kd=false){if($Kd)$H="SELECT TABLE_NAME AS Name, ENGINE AS Engine, CREATE_OPTIONS AS Create_options,
	TABLES.TABLE_COLLATION AS Collation, TABLE_COMMENT AS Comment
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() ".($A!=""?"AND TABLE_NAME = ".q($A):"ORDER BY Name");else$H="SHOW TABLE STATUS".($A!=""?" LIKE ".q(addcslashes($A,"%_\\")):"");$S=[];foreach(get_rows($H)as$K){if($K["Engine"]=="InnoDB")$K["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$K["Comment"]);if(!isset($K["Engine"]))$K["Comment"]="";if($A!="")$K["Name"]=$A;$S[$K["Name"]]=$K;}return$S;}function
is_view(array$R){return$R["Engine"]===null;}function
fk_support(array$R){return
preg_match('~InnoDB|IBMDB2I'.(Connection::get()->isMinVersion("5.6")?'|NDB':'').'~i',$R["Engine"]);}function
fields($Q){$zg=Connection::get()->isMariaDB();$J=[];foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($Q)." ORDER BY ORDINAL_POSITION")as$K){$k=$K["COLUMN_NAME"];$U=preg_replace('~\s?/\*.+\*/~U',"",$K["COLUMN_TYPE"]);$Gd=$K["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$Gd,$se);preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$U,$em);$i=$zg&&$K["COLUMN_DEFAULT"]=="NULL"?null:$K["COLUMN_DEFAULT"];if($i!==null){$Df=preg_match('~(text|json)~',$em[1]);if(!$zg&&$Df)$i=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($i));if($zg||$Df){$i=preg_replace_callback("~^'(.*)'$~",function($_){return
stripslashes(str_replace("''","'",$_[1]));},$i);}if(!$zg&&preg_match('~binary~',$em[1])&&preg_match('~^0x(\w*)$~',$i,$_))$i=pack("H*",$_[1]);}$ue=$K["GENERATION_EXPRESSION"];if(!$zg)$ue=preg_replace("~(^|,|\()(_\w+)?('.*')($|,|\))~",'\1\3\4',stripslashes($ue));$J[$k]=["field"=>$k,"full_type"=>$U,"type"=>$em[1],"length"=>$em[2],"unsigned"=>ltrim($em[3].$em[4]),"default"=>($se?$ue:$i),"null"=>($K["IS_NULLABLE"]=="YES"),"auto_increment"=>($Gd=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$Gd,$em)?$em[1]:""),"collation"=>$K["COLLATION_NAME"],"privileges"=>array_flip(explode(",",$K["PRIVILEGES"]))+["where"=>1,"order"=>1],"comment"=>$K["COLUMN_COMMENT"],"primary"=>($K["COLUMN_KEY"]=="PRI"),"generated"=>($se[1]=="PERSISTENT"?"STORED":$se[1]),];}return$J;}function
indexes($Q,$e=null){$J=[];foreach(get_rows("SHOW INDEX FROM ".table($Q),$e)as$K){$A=$K["Key_name"];$J[$A]["type"]=($A=="PRIMARY"?"PRIMARY":($K["Index_type"]=="FULLTEXT"?"FULLTEXT":($K["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$K["Index_type"])?$K["Index_type"]:"INDEX"):"UNIQUE")));$J[$A]["columns"][]=$K["Column_name"];$J[$A]["lengths"][]=($K["Index_type"]=="SPATIAL"?null:$K["Sub_part"]);$J[$A]["descs"][]=($K["Collation"]=="D"?'1':null);$J[$A]["algorithm"]=$K["Index_type"];}return$J;}function
foreign_keys($Q){static$Hi='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$J=[];$fc=Connection::get()->getValue("SHOW CREATE TABLE ".table($Q),1);if($fc){$Mh=implode("|",Driver::get()->getOnActions());preg_match_all("~CONSTRAINT ($Hi) FOREIGN KEY ?\\(((?:$Hi,? ?)+)\\) REFERENCES ($Hi)(?:\\.($Hi))? "."\\(((?:$Hi,? ?)+)\\)(?: ON DELETE ($Mh))?(?: ON UPDATE ($Mh))?~",$fc,$_,PREG_SET_ORDER);foreach($_
as$z){preg_match_all("~$Hi~",$z[2],$Hk);preg_match_all("~$Hi~",$z[5],$_l);$J[idf_unescape($z[1])]=["db"=>idf_unescape($z[4]!=""?$z[3]:$z[4]),"table"=>idf_unescape($z[4]!=""?$z[4]:$z[3]),"source"=>array_map('AdminNeo\idf_unescape',$Hk[0]),"target"=>array_map('AdminNeo\idf_unescape',$_l[0]),"on_delete"=>($z[6]?:"RESTRICT"),"on_update"=>($z[7]?:"RESTRICT"),];}}return$J;}function
backward_keys($Q){$H="SELECT CONSTRAINT_NAME AS constraint_name, TABLE_SCHEMA AS table_schema, TABLE_NAME AS table_name,
COLUMN_NAME AS column_name, REFERENCED_COLUMN_NAME AS referenced_column_name
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = ".q(Admin::get()->getDatabase())."
AND REFERENCED_TABLE_SCHEMA = ".q(Admin::get()->getDatabase())."
AND REFERENCED_TABLE_NAME = ".q($Q)."
ORDER BY ORDINAL_POSITION";return
get_rows($H,null,"");}function
view($A){$M=Connection::get()->getValue("SHOW CREATE VIEW ".table($A),1);$wg='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$M=preg_replace("~^$wg\\s+AS\\s+~isU","",$M);return["select"=>format_sql($M)];}function
collations(){$J=[];$H=Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("10.10")?"SELECT CHARACTER_SET_NAME AS Charset, FULL_COLLATION_NAME AS Collation, IS_DEFAULT AS `Default` FROM information_schema.COLLATION_CHARACTER_SET_APPLICABILITY":"SHOW COLLATION";foreach(get_rows($H)as$K){if($K["Default"])$J[$K["Charset"]][-1]=$K["Collation"];else$J[$K["Charset"]][]=$K["Collation"];}ksort($J);foreach($J
as$u=>$X)sort($J[$u]);return$J;}function
information_schema($h,$Yj=""){return($h=="information_schema")||(Connection::get()->isMinVersion("5.5")&&$h=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",Connection::get()->getError()));}function
create_database($h,$Bb){return(bool)queries("CREATE DATABASE ".idf_escape($h).($Bb?" COLLATE ".q($Bb):""));}function
drop_databases(array$g){$J=apply_queries("DROP DATABASE",$g,'AdminNeo\idf_escape');restart_session();set_session("dbs",null);return$J;}function
rename_database($A,$Bb){$J=false;if(create_database($A,$Bb)){$S=[];$Jm=[];foreach(tables_list()as$Q=>$U){if($U=='VIEW')$Jm[]=$Q;else$S[]=$Q;}$J=(!$S&&!$Jm)||move_tables($S,$Jm,$A);drop_databases($J?[DB]:[]);}return$J;}function
auto_increment(){$Pa=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$s){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$s["columns"],true)){$Pa="";break;}if($s["type"]=="PRIMARY")$Pa=" UNIQUE";}}return" AUTO_INCREMENT$Pa";}function
alter_table($Q,$A,array$l,array$ee,$Lb,$nd,$Bb,$Oa,$Ai){$Ga=[];foreach($l
as$k){if($k[1]){$i=$k[1][3];if(str_contains($i," GENERATED")){$k[1][3]=Connection::get()->isMariaDB()?"":$k[1][2];$k[1][2]=$i;}$Ga[]=($Q!=""?($k[0]!=""?"CHANGE ".idf_escape($k[0]):"ADD"):" ")." ".implode($k[1]).($Q!=""?$k[2]:"");}else$Ga[]="DROP ".idf_escape($k[0]);}$Ga=array_merge($Ga,$ee);$P=($Lb!==null?" COMMENT=".q($Lb):"").($nd?" ENGINE=".q($nd):"").($Bb?" COLLATE ".q($Bb):"").($Oa!=""?" AUTO_INCREMENT=$Oa":"");if($Ai){$Bi=[];if($Ai["partition_by"]=='RANGE'||$Ai["partition_by"]=='LIST'){foreach($Ai["partition_names"]as$u=>$X){$Y=$Ai["partition_values"][$u];$Bi[]="\n  PARTITION ".idf_escape($X)." VALUES ".($Ai["partition_by"]=='RANGE'?"LESS THAN":"IN").($Y!=""?" ($Y)":" MAXVALUE");}}$P
.="\nPARTITION BY {$Ai["partition_by"]}({$Ai["partition"]})";if($Bi)$P
.=" (".implode(",",$Bi)."\n)";elseif($Ai["partitions"])$P
.=" PARTITIONS ".(int)$Ai["partitions"];}elseif($Ai===null)$P
.="\nREMOVE PARTITIONING";if($Q=="")return(bool)queries("CREATE TABLE ".table($A)." (\n".implode(",\n",$Ga)."\n)$P");if($Q!=$A)$Ga[]="RENAME TO ".table($A);if($P)$Ga[]=ltrim($P);return!$Ga||queries("ALTER TABLE ".table($Q)."\n".implode(",\n",$Ga));}function
alter_indexes($Q,array$Ga){$nb=[];foreach($Ga
as$u=>$X)$nb[]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return(bool)queries("ALTER TABLE ".table($Q).implode(",",$nb));}function
truncate_tables(array$S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views(array$Jm){return(bool)queries("DROP VIEW ".implode(", ",array_map('AdminNeo\table',$Jm)));}function
drop_tables(array$S){return(bool)queries("DROP TABLE ".implode(", ",array_map('AdminNeo\table',$S)));}function
move_tables(array$S,array$Jm,$_l){$Cj=[];foreach($S
as$Q)$Cj[]=table($Q)." TO ".idf_escape($_l).".".table($Q);if(!$Cj||queries("RENAME TABLE ".implode(", ",$Cj))){$zc=[];foreach($Jm
as$Q)$zc[table($Q)]=view($Q);Connection::get()->selectDatabase($_l);$h=idf_escape(DB);foreach($zc
as$A=>$Hm){if(!queries("CREATE VIEW $A AS ".str_replace(" $h."," ",$Hm["select"]))||!queries("DROP VIEW $h.$A"))return
false;}return
true;}return
false;}function
copy_tables(array$S,array$Jm,$_l){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($S
as$Q){$A=($_l==DB?table("copy_$Q"):idf_escape($_l).".".table($Q));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $A"))||!queries("CREATE TABLE $A LIKE ".table($Q))||!queries("INSERT INTO $A SELECT * FROM ".table($Q)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$K){$Yl=$K["Trigger"];if(!queries("CREATE TRIGGER ".($_l==DB?idf_escape("copy_$Yl"):idf_escape($_l).".".idf_escape($Yl))." $K[Timing] $K[Event] ON $A FOR EACH ROW\n$K[Statement];"))return
false;}}foreach($Jm
as$Q){$A=($_l==DB?table("copy_$Q"):idf_escape($_l).".".table($Q));$Hm=view($Q);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $A"))||!queries("CREATE VIEW $A AS $Hm[select]"))return
false;}return
true;}function
trigger($A,$Q){if($A=="")return[];$L=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($A));return
reset($L);}function
triggers($Q){$J=[];foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$K)$J[$K["Trigger"]]=[$K["Timing"],$K["Event"]];return$J;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER"],"Event"=>["INSERT","UPDATE","DELETE"],"Type"=>["FOR EACH ROW"],];}function
routine($A,$U){if($A=="")return[];$l=get_rows("SELECT
	PARAMETER_NAME field,
	DATA_TYPE type,
	REGEXP_REPLACE(DTD_IDENTIFIER, '^[^(]+\\\\(?|\\\\)$', '') length,
	REGEXP_REPLACE(DTD_IDENTIFIER, '^[^ ]+ ', '') `unsigned`,
	1 `null`,
	DTD_IDENTIFIER full_type,
	".($U=="FUNCTION"?"''":"PARAMETER_MODE")." `inout`,
	CHARACTER_SET_NAME collation
FROM information_schema.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$U' AND SPECIFIC_NAME = ".q($A)."
ORDER BY ORDINAL_POSITION");$J=Connection::get()->query("SELECT
	ROUTINE_COMMENT comment,
	CONCAT(IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC\\n', ''), IF(SQL_DATA_ACCESS != 'CONTAINS SQL', CONCAT(SQL_DATA_ACCESS, '\\n'), ''), ROUTINE_DEFINITION) definition,
	'SQL' language
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$U' AND ROUTINE_NAME = ".q($A))->fetchAssoc();if($l&&$l[0]['field']=='')$J['returns']=array_shift($l);$J['fields']=$l;return$J;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER, ROUTINE_COMMENT FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return[];}function
routine_id($A,array$K){return
idf_escape($A);}function
last_id($I){return
Connection::get()->getValue("SELECT LAST_INSERT_ID()");}function
explain(Connection$e,$H){return$e->query("EXPLAIN ".(Connection::get()->isMinVersion("5.7")?"":"PARTITIONS ").$H);}function
found_rows(array$R,array$Z){return$R["Engine"]=="InnoDB"&&!$Z?(int)$R["Rows"]:null;}function
format_sql($H){$wg='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$Qf='FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|(NATURAL\s+)?((LEFT|RIGHT)\s+)?((INNER|OUTER|CROSS)\s+)?JOIN';$H=preg_replace("~($wg)\\s+(AS\\s+SELECT)~isU","$1 AS\nSELECT",$H);$H=preg_replace("~($wg)\\s+($Qf)~isU","$1\n$2",$H);$H=preg_replace("~($wg),~isU","$1,\n  ",$H);return$H;}function
create_sql($Q,$Oa,$Wk){$H=Connection::get()->getValue("SHOW CREATE TABLE ".table($Q),1);if(!$Oa)$H=preg_replace('~ AUTO_INCREMENT=\d+~','',$H);return!str_contains($H,"\n")?format_sql($H):$H;}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
create_database_sql($pc,$Wk=""){$A=idf_escape($pc);$Jb="";if(str_contains($Wk,"CREATE")&&($dc=Connection::get()->getValue("SHOW CREATE DATABASE $A",1))){set_utf8mb4($dc);if($Wk=="DROP+CREATE")$Jb="DROP DATABASE IF EXISTS $A;\n";$Jb
.="$dc;\n";}return$Jb;}function
use_sql($pc,$Wk=""){return"USE ".idf_escape($pc).";\n";}function
trigger_sql($Q){$Kk="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")),null,"-- ")as$K)$Kk
.="\nCREATE TRIGGER ".idf_escape($K["Trigger"])." $K[Timing] $K[Event] ON ".table($K["Table"])." FOR EACH ROW\n$K[Statement];;\n";return$Kk;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$k){if(preg_match("~binary~",$k["type"]))return"HEX(".idf_escape($k["field"]).")";if($k["type"]=="bit")return"BIN(".idf_escape($k["field"])." + 0)";if($k["type"]=="vector")return(Connection::get()->isMariaDB()?"VEC_ToText":"VECTOR_TO_STRING")."(".idf_escape($k["field"]).")";if(preg_match("~geometry|point|linestring|polygon~",$k["type"]))return(Connection::get()->isMinVersion("8")?"ST_":"")."AsWKT(".idf_escape($k["field"]).")";return
null;}function
unconvert_field(array$k,$J){if(preg_match("~binary~",$k["type"]))$J="UNHEX($J)";if($k["type"]=="bit")$J="CONVERT(b$J, UNSIGNED)";if($k["type"]=="vector")$J=(Connection::get()->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."($J)";if(preg_match("~geometry|point|linestring|polygon~",$k["type"])){$Xi=(Connection::get()->isMinVersion("8")?"ST_":"");$J=$Xi."GeomFromText($J, $Xi"."SRID($k[field]))";}return$J;}function
support($Ld){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.8.1":"8")?'|descidx':'').(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.2.1":"8.0.16")?'|check':'').(!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8")?'|fast_status':'').')$~',$Ld);}function
kill_process($X){return
queries("KILL ".number($X));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return(int)Connection::get()->getValue("SELECT @@max_connections");}}$Pi="adminneo-plugins";if(is_dir($Pi)){foreach(glob("$Pi/*.php")as$n)include_once$n;}function
get_translations($Yf){switch($Yf){case'_template':$d='!JLxY[w-4$rc^?[dx%"G&@:Re8!7IZPFS-Bh)Y9)M9I%2u+7t;HX*Dg>f<w%`_rl3JS^!y@ugIClKRpniI~B6lKReSsn$
=80?VW8*4I^tMrO7w,[eGm2n}tPrA!P@Kz&x{V+z&KlX[^SpInM[^twvyyE7duwL/%{cdW8[gIb&-Mi]/V8?Rg%K_RF>0QUoQ-wNo;QE@9G]a1o"B-$8|*}"y"7,<"#=}!"Oo<dMv"6#yt]T9k0<~GHZ-"%/Qff$)eQGrNg&IO1FQ4Z($-FNh5y3bqNE.kYklv[6AoAd)&.*R"3N!+]%C"3N!*D+/0=mM9!.L"jC)f.9S&6uXdr$,D66.fjf8&?NW+4Wq2o=3)%QV"6Iy5<Z:%QotC7S&:70p=m$=d1:jk5#mN0Pl]Cg]
i$<j=;jiXT^WbH,$9N3*:lq&EP?c+mVAQGqhg7RJ&p>r:CQhio$2gU}.N
A3VGEAmsgi$k{EIi"+V?uFJyi.`fsI2R``"Q%?)TZC>jI5E/J;c?XXkkzffP$x@K4nAQ@`W

9FoyFVc)8t.Z>f+{T$[_Y0/}pPdcL/p)Y_iFN-/DHegY]SBR^>:f-eT+8<Q:R*g6ubG+54xnkyhkqPtr1*aNC[Vs=0jkwX?~R|Dwa7xC/iE]AP3>An]qQu$Iu4Ln=5
4Iy0O*c4u:Ir{LSh3;rXPo7C[o34*REK|X(
tvseUL-;1fhhihX
`?AFj.x(O;7Y<U9s_S[Z314`%91J,gZGQ`,WI<m.hTU>qTZFi.{T?BlCkRVT38=k[c&yuZQu2q+kii.Gg/[kao@@Oau`wGH7Xuimo!FMz4{OB-I!#.ZjzvT_8np-{Sg;9VAExO-GE+r>4-j+mFPQ}A;f)P+:TpiA!sjq[`NS41*msph[*
pDusoKYXM:Us
/LW
S5br?o.{kVaW`-/.ql#_=@)Phmi0c
5
U3jH*2LWjC2%3}jW*#n+
:I_g0?,5oJBr[W{3=ZoJmE0+q8M>XUe/}
IR!(>GC-}<=1tU.04E@;Z1Tl^_^JasG,R@o.BgnSP$YJoZtTU;;]TgwHmWkEE;~KlGg;<GV_Pd&t(;`1UBnfvGUi-bP1bn5UEtNl~$$Hty$,oU2g]BlgVU5^L;|%N*u)gOkUM5qJ@m.W0Y^479#Cwd0^Rk6.~^MlBbjy3cUBjtTEi3T@kY>QNMEP3!P.R-
ceZ}q5OUnL<Ll1IpihM/b+H7$+Slw:)w';break;case'ar':$d=')c0@qaMD9*70L8,%AY1$Wx}dN6_C~MF$@I;W~R/!U:N5k1_-tP-ft#=3gVrC=a_:lD1[#q
IMZ
ZG60;s^%3>>4
mn,L,jR2[SVJ0`sK!roB9s45jF~JpGfx8(_ABY:>Jt8cZ$f
K@}cwwGj`=TvMhq?VrqH>a^]hs"DMh;kR5oD1if)FfL[&:5L!K|xk?QXjR%&lGp)qa1rU0C>~/:c1vQ#cS#H~%<[%]Cquv_W9sOemn[x>t^:.`]muI_kp[N/KM_)M(dby>{K8d^
++(-6?5j}5]IO?ddg)ZBpvYz!_wz!uG+d]l2pjvPd(KG^v&QX^op4gz&RW77anEHYBitSe5a2M0M"by/:qH_-G>WZpHxRXxLWxAlG!KeCz)F*K[Rohm`86mtUvOK*cTILF]RQS8BYJMy404-EDl%`XZ%$V.RdS;S&Wipi(AF4B^&<[!1LjG*@RWX199kwTKW)$|>S3Du#h*wudFsK/@vD6*Q/[8*/V4PZ^SC3Va.1e1]5U6O43pC"-uGka<;lkjSeK35R,U_hd^i=v=8rc=w@p|9,t`7m>-d:K@#at.7km"YkO&2f8k5AQ.Vx@eqcCT*`/VT,o.UQ#^H2#T"r>sV+bl+!St^^*6:kQ45]fC$"E{OEAgIWo3"<H8t##_uNslF;BS<WSr4`<Hn3K_E
>{/qkrR:5<_$vlkM]>gT,QSV2zM[SrGwxkFU`GEhW*2RXpMmXA8:I{e2M]Lv=9h=mnaOB:JUcdM
;ky]V{TUSu%HoC`?mvW{Ve5h1WSRtp>XufA=7bFf_Yw$vbXe/"sUsY!JO_?|*k>1)Xl)
knqQbMM^DSvh1xxaK&QH&875W7#+HsfOiwNA#5d<CP@D@WJZf+coC`d5L??nNT)S}c[rK^)gJGR_AYol@CoKtT$q8&w=085Y55wo`PB376h%--^T(UAU~qT)y8I$2w^RD,f;5L<m[CY@GPS_jt<!VA}$.$ow.s0WJ4H(M!J/YVXlEv:Vch8M#TLX_6"Vl:~7;o4*rCf:P0I
{k80<
Q66V#X5%,^zY_pPU0Dr$CLo6F2p[12]<2GgJ&!%:[<yGF0>4tYP`kFqGc`B-s2POr:wi
y*mhj`SMsT;>
N_aq`&TBXH}rOC(F2[(W[EL3-E],z.<w12M#u+(NeE(6A9%i+?v41lDu.`fL,
xQGhRuEV7<bF6oWVAT^#LU,G^_}Ocpvk="_PRVk(
Fb-/:]6"@:Dsv3(peb2ENSvLAi#1dbs8Ed_8YM*lrxT)!^Mnks?wo-5q9$Wd?`(aB!]2kfIr1"IkQ%w
h~Z<:wK6QINp`"RY]ldEYHHS-K]xrGpm"Hi>NZ%04&%sbe;mFeNbJo(M?;2uUdb&/m9|HdMYOO.Ig&RM)uVV`[wJ+CvhSg?ghZE<`N%|4r#v6]-^nH8aW2m"sroh/0O4__V/PEK@/OTdp>(~7hEXI]$wEC]]j4S_l^gvl!+DUXgi6zmG)h2nL59kCw"KCZ&c
/WmF+/`HYD@M!hR`Tx/k|v<Lb=Pz!dWH1bzSV+c&[SRt|P&Vwi~P~mXgHS~>R,ybMd|8oph;IrV]G[!
6bjTPx}+@&O]8Nv%~X_K[dKPh#=fy6J<H>9>:VFGMWh5c<ODQ4aZ`Ozn;=$+@5Wn4EvhA#z@x".wx4*W;&P1eZuDjAR(u&DDEwK4v/79z8|6dV+U$mFa:h=1<E%es#|JA=^pCe>7U2]Q|uIq:$*X`3ElA%~&xU_&$a3JYT4k;#O3eTdqTDW;AZk[<Uuf%.ggV6Gje^!Dv(S-mpU<eN$%;m%>Y:(YA!eDcvlh[
5`RNVU,[$s6lEfsdGkDC(5KdiQ)$#p|W^XVBM)EY_,bU!WC#?*CLh8E-Pa@a&`gi0
T[m^=>nB#+:h.)(Nuiw
8);QNtCHMq6YYny"Wqt3bHug?ac.~iVM2G$SuR|Tw(tRJt2GLDtj{`nr$&
CNU#`#@g;jd+:B=)t
`L;{MqO60QCOCMGnu."k.<&N).%bc).<p`D{iZ:i.E)y?4(X`)<aG^#|]IHa,.dJMVY5>P84ha;cFGe&_*;:`^%h%?)#q1lYlPAG-^5@^f!%]0oii&?RfG-fU:3}%q+t1sP|ZugUa-Eot<)4@TxbJp,2Lbvt)]wpjz5~G.ZRrA<JTgSLlPT[iWf_z#$Gu9Ss_(vwq|#R*yj<8c%or_O[qJ_s9ZE>Pi_)*wSrEOUHWM_YUuj=qMb0`2<4CX>[$:2>I=#PY!LLne:}o[;VZ!=kF,CI<%eQ1YS-rbIm0`_KAZHhWFRUc{KN:6tL>]iZEGDwhBFAF,;.rI&rGi/kt~#?3(k_t<)BsDkNS[Gg=jarRS#L:FY`WmbOauz%gtm{ybs"<!fR>k(_1pS~nNeYAjJDF]k]?r)rfKX/s6(lUSR~(}8f0+?a_~]j":G1!`orLE&J=?VGQH.~;:
q>WQ5`
j4m7CB]~;5m"MrTTa4jtRhkW.AG],]M9uhU`7UQ#5W6g";=;IHTT9hVG[,GD+e+0*~N5l05(O!B$h2S(nKMp7/g{Wk:EA.2RNV
?;xfbvRLb`Qhjuj^ESumbmh
8i
<FsfB6wi/;[0#&@:CDa-yFlJngs/n/o0"siLXjBGj27Gr;!C&"(p_b#TES$a#Ia9itRwB(4|)*Npsu-c8.bL=^%VBFT-+U*OZ@ieLDu(*H
R6Qx^EPn]QZ#4pDn:lh/<cVZ@,o)mO$p;x"`<#^QE8w&_n5/@cTEN[W#xZSZ)$+bXo60O)_y=x0U~3?(tMZ/pDY?Yv)x4qy1/4%W!3gc&5V!c^q7"Q[yFm$h5g1UA$hs]#vEh7}W;8I#1jq:Ft
avwbycEkdZ
I.FvV,,E<-ZY_2_@,kT(klbX&(]8)]rKl858wB9%1>"vk:D9Pd]V]Z5oUd,]z4g7c9!>3"oxmTf!XV,ra`5Fuw<y(xxau)&oUn&Z)k$KKfpr6yc:02>"$O6kJ.U><kOR$,F_!Q8W^K`SD:n?hT^TG75Rp!$IFM<W{Q[LyDUX4h*Y(t
vOk~ief63$$iPP+%4Py/Kc:5`reVe:Y
1_[%pq3_rt:nvl.24&D)R[E7*%wCgAtjw>5h`3bo8~l(_fKaN<A`3#j.oODx!bQ=do+INZ1miJR5bvpMDpc)JB]A,3!1Y)4^&5S,X>fdCP22w~QAmJljN+be$P,dvQ(~<:z&h[%8P+d=hO`|:Z3_1`mu$Z%KFR@e!zi+Q4c_vXPead_dgT8_q6Wq!f/5b_+5.BJ+&J%+AX
2gaZ@kN2<yuYAaEWPG_BVJBk0w^?fN([Y<a%QrRp?ou7(<Vlv[m+3dxmV`>w$1(d=opQ&"l6Bs6(d,J8UKqRFL].8Y`4X>jO*/8FH.}jW&:8ngJdbd.PB"%+nOW:v$UO0o%I_pefZ4aG{,"s6>0/b`)-%]lRAPH
Mi`7qR7O3mL;%$6wlq+OzjSdWDCfJ16X2G^;,/Yf3mJ&9xbnUd0U!ewm<xq
^>.@8to%t:9"R?%eB_aNv@W3J&I$2NvZp8PKT9<wP)<I1+H%IuSQS]%RPfoCe:-e3U[2ZS)Ly9VRTozq2sZJ&+7""%K;MbCs$*}D^uKh9M6>0kB=~%n_=c0Oge<aJIM
Ve{=-M;?Ec2=qWTA)snDCJHm5T4EsB^IHMcfRq2w3fNqc70y!S[B2)ij-3>M8FSnahIVkYb%mXfUUhw[G3=A9cQ;w]C;0tSK*CY.
=ad8cxYX`/9}RTc|8B!<9h9;ebA=3eezPBm>M`G(v<TSBS%z->(ykjlB`?J_Z(k,`qB,S)"(M=!0>=kSuLLt_d&OU.5
`~=$*ZxSBMNUd6dE1V/;)CDaviI/xq?qqM))`YE0<,Z.X9H"kWT&DTkv1Pe=JX;mHfUO_:=ke4kP5p-xB046<T5eTK?]@k#ojYM,qx.D(vQLIEDg*:[5f!
vC>ksZOi*ZhcLTM[ZszV<tHecAPkP/nSD,n6KuMm]uGhP`%F_wt&-^i$:j},2@vRg!X]`d(,dI$tIJ:S$<=o.IZ=N#|<!Z,1DEZ/?UA8Fwf@+k99tAMh0GZB"/HHyh1_R@?_aw(i<=
FDS$Lujw#CleAbxgH#kC523IWS^vZ[3sE}1hNhTfA:E1BE!SXIQVpq=0:jy)Sq$^w"Mv#[%#egc
..[:
7t4C%Ec*cAlQ<S./g)%DxeY9Gh(-uAaAe.cmquH`8j>/Bx3aB^3/-Bby+u9yojphFWWxtgeOFZxO;Bki9>O0y[hhH.m7=gTh82"V$y#$;xcw9+UAxW`!g)W;:&?8;<4nk]kram$?pC~*!po+{BQ"G5)CsT[;s[;A`vk^b35GjPHIr:EB^:SAR(hPVIG+M^j>_Gh7UUMvO[~=v?nJ)Jz23ooq~mZ<z.c7r7C2R990dfI#{S2c#e(4>t]w)9_>3!BOR;p;$kAn^r^iu4eLZKvCzvCoZ-?a~+A2yCZuSc;2S7P?e,nw87QhuCW5yl=RkaGl1-@+fCxH13S4H4Ow
PwX=%zl#*vs&j[lR!$`Z/Qn?NyQ(na5+Y4)R;!v)%t8b1]@|9RTXef<~T5F}"W22`{s|cLtOz)lvf3W"o|"<v*P.C>jA=N(W
!B.66+6/xhO$]-w1&NS(G]Nw?0:^%(gD`b^eamP7bEG${!nV/O%Ka[)C2,EX
pofaSb[LVG7I.TLm66DPaLId^5c
L`';break;case'bg':$d=')evLV5ICv@90qQ;A#@yX*Mwv5C5=uC:eS-w>6]JR!ECON13S,SzpVTfl
kaB.VB9O$k&p#3EWt4u1hMls^P,L0Hg>=FWoJD^S3[E}/""?rmAmX3@8Xys`yE=jMFU<D#nGFe+FLngmMNH["Q05y4w4rBA4y$Um>zv5ZD[rE5I+_!s@YJ3:wma#dQTr`#v5.mXYy5<5WJoxLteh=sVaW@%OpaEgi!Ya$<*-_:(WbK/lTRXpJ$W{^
P;nltbd`.!E,9C=|iw]L
yRE/EPaC3^V8W<!*l2#Y6V]^u!_ZT<<*]x_yDPgK;rjkSb0[]o!!"Nvxx</NnCQf7Q3PjJaDGqE4|=15=l1qlZ3b`_HLg45b_shK)X_iNuVa,mz^03/n}]v^?kzbac47oM8c<a)n=^Sp5[LeYn5(ooWt#l+e^A
T@+<@4qD%_pUptiM&wcwPhxbDD+7)5yQ_pfVEO17$5E+0@fmi02Z^&x}GXH=wtE2.^$5cI;YcqYfUGq+/}t*tMGe:6G:0VOoxV*f(GCyd<=sopZ@`QY)1BdESs(9rNOzi]SgXLi_CnUaSm&MVTZ:HE&:*-MGvj@T/m7UO6Jt"/I8AZ*D^GUyC2?y[[5Lo#[icYfG3Pw~Eh_=)e(xBb<~Ui
8hSTV?8!{Y3f%-sM$mWbbc*h.qx_gCtw6,1kG5=%K1g<%w%X=6qe^2jQ8
dG96_)2lF_se_t.$WL`%lS+
t+u<NJHafBC^csuX#3r:H!x<0uLj~:7?2L8;X
+oQ%nT05N
,OL$B<>ip-OycLzLO$`P=4(=q(
RHVa=$8<]mlH!KrPp4
%>LoiN=6;=YOI40k)P%Ir#L*^FjCEnL#xNr2sa[
DgQh{W7nabPBQMr+GUYy@J9Kx[z2
y`b`w)ujmw):!T
<8&4Z[X$NP~[j$-:dejS1F=wRwuL!s}_2fO[G_ls)XZE{,Et!KXA"D5^C3
?=1h0%o^k39%PxFopD5X3Y6.=5$/%Ep!+)-Mx$iVrW3V^*[A]{V[(Lux5AiKc~r7*Wga:s!Cz&x6t^_0<~`:N}tjTsprbAL"Zf#e[LVD.>v>?_nH4>KMjMW`3CG-jCT4RO90-C.][BW>6Q.[*vpNrz`s#eBvRJ;.J3dLu/2y+~u
BPL)+uMOu3dJiq*A054Q/z?p3oiC^xyxo@1^[NTU*{]aRL%vB#iMG:K?R:.vW^hO+,luRR?Zx|H7Ae2tQ@!>%hqe-W&m3AuoH!R8/,^|0=C6/^0rt(`s[5!Zt(/ua5Zh96RQ"(XD0%g:O|`bN^94[dSl7G.+S;te0=]x/98{:NdHcy.{-U>vP(HI4G0B)|QQN#Q2!5*N?;??o..
n""}%bR,ngFL40.~_;15tk:nds)o-^EycjTpse"U%"IjJm.
)[89Q$*xj%=x+Fu-Yp,f=Y(Eo<R~t:+;PSTD!{f$Z?3`#Q`+Zm@{:N*o<!THykFzm<!?"*/fO{5lqmVs/%&JGnU/Os;Rf+=d*Ko5F$djTPun*kWJ
rZ*Z+/GQ"IaT!>y;JrxL5S)mgK]dMrghFTkdEA8VZSy<pVs^]6:[R25f}WJ`#5tx;.Lg5NkV<_C+dG&nrNC4gc1UH"Y>nsC)k-k.7i5E8Mc&P05#}n5e(RHw(URc0[UxBdrw`Y,%^kuT:G|[soAw-FL/Bg/0*CZ]V3rvI^*%?gfEtN"h[v%6OZ~<O$k;JPS@;VKD?hZ!xV}=o+.^$I=wI*06~l-?#e)9cKhPa*0tO6cp]JDP%V4"z>ph5[L2d^rvKt$#~*7+sY,BoT09gDW*YTJnU9^GSL4j%v
wbx{"jHY-2JyP>s5ROsmPgkn+zB)Nxxlvd,/Q=T:VW>$O1D5*=Ms9yU%,Zv%*yk>my4lxA_)bc/VLh9#rNupp)KOWmd4x"D"FUiryS"rtJD);hm3pu7O5wBhTq!0eSUVYQRNv8HYe^&ZZ@v.
59I6rW#KF[Bk<?Pxt-H.=8@
I+w&)$v>}Nxrd:QgDpUnB#uS1R0C5ddeV
nJC/Wrz_0Ky=kH|;v.g
BuD^g%`:4o>g%-LaGn}?=jL(a!nQ
#hsP=@>G3QQj:7p/cKfgc@1qghyayhs*fel.VZ7]2hjbnZ
`kVA?4@"}aLj,FWp|;YEp+$(mYM.[2!Uy4u;B8<)@]|4xh?>a(?51"~8(q&/=]j./b[Uc
(k?^n4RT|R~
csB[b@4Bupu;}>z$+R-NKuJeWv:qye:`:in$l8l(JsfPX,"=K)~y]itXnbZ>HOpK}<yA},pY@C$HcwH&;D&tlEpWCIcXqEj/Pj]f0:{LyhveZ>Gab>A8&N?@yOK8H60;N_AL$t]bgMNGUCWGf^CxZq5iI7Z+fe[JBPc%J;]G)7N`1<r&6Y4@[X5@*.xTcju[(Yt`C2bB5UMS6cbJY!QQFc
<>f2$K+<gu"#)(_o=kaA#/Wu0zGUmLY5-7DO@lWnLR*o(xyL)?IX-?3Jn6U"QMc813][>.>!4N`13uHN*H2nqL
26t@D,o?/^CBLJ[KmW0FjuBs9MVW?;<-AFpQ!
R&R6XZ!b0trood2k~!a]KJSc).gSRybA[@2l)-M9R.x6jYHojbbQk."(1HuuC#NjEnv1h:-:Tn%Xf>qC$?m-?V,).3dWp_~IWN/+jZ8JVgaGO2:+y(fvw%co=%=({P^DCh/k#o|[Q1,Bu&fTj)EnvVRPk*kJ@ZqD*r8K3mb(OMS;4nLh=tmpefHB@!:8S+I4;)R-zEp1hqB,D9CAmC27YFKd<+<mw5u_%pC9Lj~68;6-pRbhz*^I_0F#
JQHYT01VfC@!m:=1*8VJl5fhqKxX7V?p8u87efLrib>:KAf6-<r>`_[@gpeCX,OuvJeTP<pfuD2;C)P~C<1ee1f6uP^ok.8ua~]
[z2l;,Z%%K#L0
4,X@bzP1CA14ex?2tbvOsEVFX
+b<
F)@!8Jq;:>[*N*8>:42,&EEx4jGY,EE@$)!h=D"yDxTv_,ymn.6AGdi#>NeIK)5!xl<kYP"V(1Gas7iQE#:Z1[Pb5g0fSi9ZvYRo%q1CUO#v0gYiuzIM"PxUXhUCpeCR[B$huC$rRHxJ_<OB$11(S58:W=_|4BI/
|9UQlMC2Sc:Bk-:3F&Xq-fP4GSfkP`vX6S~(Fr4v#uVZWM0+Fb,?gRjYW[HjQNW
[ug$cp-gG6
:+4r5.Rz4Dj/If"Fn^.L>cjZj`#5kt$IP+q+7ZP8UQ:XEF]zaH$OruEo(G#flZoxLCmA_{WC&f_4XPVsAW]E@SWwYNm30B9W[LBOZVCXMGtYl
4-qOe)$L:Bd:23.q0$55jQ&l;Cil8~"K(UTgHEedF+*BX:),kKQA68<*x]8JnB2oc{0hg91%Fdk+$fso@FnW%7q4pA,S$oKGwbO.<.#f=Wgtq|1Mu*_S*-*CPcSaR$h/G%b?rRS3E_IA`i2"C*T^[_w(p6Pm*Pq]pA@2R?[`J9je<K"^>`7:KDKC_>t*#%f^1a=[+q*zWTK7RC]%1&#m7jo@C*JhOM"%a".bHj
#PjcU`,Z:0aNm>!<9(zA,H6lt1ey4G;:2:v$qbt1IV8WOD%(;Js
a3cN3p$;fA1b8vllC/HgL(?KA"n$e88d/)DfRA8"[=4fXUT6kk%BWTPma
velQ{wX3RM{(NutUSC?DZ5U;4=x,,stPI2H?7w&;3aD&z!W[cs,)7$]M5%(q4)G>fq|u?kP8Oj8Su"ek6*.kGd3SH`{tQ=,RIRV=H)H,KP.Zjt@#HSKK
!W+<DfH6/q#%]4(^]x#f3aZp8|_b?.-gR<EdZ(8NPX`jjHbJwEHpef?f;RS#TQ2@@sL?>.RgkLLb+#r|9g/8va*{RNJ|Nc6QI4e^4}`CGDX7"$v<v[Mrc"AL(l;(0/n5+4]B9QO[YhE)J5;W&F4)Ri.LK"e_K@!!5bxDF2<BC)pfEp:JB6#xT~?5ObQM@*_<`-`EWoa;cN%aKVp
b4*`msqu"7*<m`]1jYs=VxEb*@ez!$5Pr6>S"b?()BV-leDLleK4uNNqJ,q3@XXyG(GcK)Y9e8A:OL1aefC]2kaMTqO5Mp
jp,<TWIE0^V01Ny&9$vh|#PD76<jce7!y15U:LJAfO$?_[-*
_LS5T6QYf@L&69jfkH?b+#B9_
CU"A6fkl^_LYAV.BfhGX]2jq].(^@IHw@pS3J/9Z;foTp2cKF(bAcE0~H%kvtU?#T!Bx`gE
cJhN;G<eA<9)y]&4vO]?B
hKJq
1f#2x].z">)L[b|
/aOZ53qOoXP.I;(3O/3Tonai>kFdX_J$m<QYqC,kJ&h^0$9tUw-m9uB?@ATX<-@yar`xNye]N1To(r~X$ytGbc*[wFx%HsByESI+wJ`ux:76/_dAP?e43sr`EeQWGp%-SUmyr@]tT-/Ab,bP_C35lYE`I
@qD*},%Fw&X&UL
=0=WAgNZV
/r2tPDPop.6;=5d`m21D"kATUOMAftgtD]8HCcM4Dn)YvH.IJywWV~nm5_h9PkVT2oBRCUMG@prQpW>4tP443A[-TE@U?-lNRQI]_RYAYz&XiTUP8BSefo_=?"?Le>%AI8:,WUS!WEX`tEu/I&1mZURo.Abpb4"CB)w>iTknrj7axO88?Qu+ASY~-pD2,2$3K2VLxp9hT7/>R},_hgJL!Am]s.w`#~
oQ5(;=t;Xm`GrwNWQ5k3>h.g}SDtxs06++^y"F=>JC?9:R01ZUHUslPF#whg`"upm"n<Kq"xOXyER5z]
ML=fjq4:&b({v?ci4AUbrGO(I!he5F%n,7y9.?GLx+s8u?K8wY47)uw[)UK>"a]en_Qc4|X^W]V{&QquubFYI`kI++
bxbFkkGf9A:a-9oBxyEqluhcaHP1"w$Bom,2.=te2Md?r6Bn}8vq0*ils$$h_jY
Q^OT7]sKrfEd4NOs;kI>d/d]mq=`7.&W4t17WN7I[fHl/+jhz2^%:/:N67sG5]LZ0c|8`ET5ps+^<L3onS1`a_-nyTF2C6smJfKw#3/TVOo"q$qG?dxwFLR,+_
XCc1>TRo@&vBg:5(xl0:C|6p`p`R)6qlgZ6}!I+^fV.v5uEP_.P2HIRnE~qdk;Jd
!Bc&!,O9COjZGdSHo940Q0d)^Qw<*d0yVcdL`';break;case'bn':$d='%h_Q<bpD9,|?Yd8(`9Xt?a5:8&^Sx$)tpQ2OmXmO9*_f.1b0T%YNF%S*|9{-($T-XD9BDEsW|37]}o96h"bB;M/uDJcJ1cqeilkZd+kH{!GY~@&roB@
f5:@]0haVG|NUVPYdXvbgFKVh
MXvvSWS_FiCmxO4$NJd0JQ^7mk`gE
zd%Pj8"`+ILc~=>CcR1DA]$+3K:c7_!^>XX;fwb%#wHr[PQ7c!
wMy>ElpPM{ARgk=B^VMVx<<`vuyjq,LFqo6"nwst
xo.dN4Xn/d^$p$}o)rin_ll0?P&9vtee&"@pZ0gw]iP?NEs.SE5JE>Ew+kwtWs(AZMbVwx|2Nn^_3N
lzE{!M7>=Ghoc,**B[be2/m^yxh._Mc|tWytc:JCB-?|f_oaLzE0qUvyamn}7p,jUXyDKZxb&KbXrm]dd%w?@wr|vWwhhnh7g@2c@7_V#tqLyM_g%&jzO)bHA(768bRZ;uv_ll+SGRJ:NPh{GbVD#tE5
Sw`Cv@et7Bb#fOE%GRyFh/sYJ4}r;czLJQz(bVc-AaI0RG
.JL6ddvc@nRuW4BKgmCU>^d)F}#6d;0e
E0|
ucUxuOe#:T";z8bEj
xk=jD
ByA5L&<:fg8M(;!PvDqPX$U#?[`<x;[2XL!3w*mo+IxXmaBJ-;QYXa<^]Q]=kiI8)9?,QX)34*4[bQ}N;;z!DYjF6##>L7R`p.eNdp..6#)T6$(),r9iei-spvJdi
q]65`u#(IgFY?"Mf%prUEKye[Q<Ap[nY]kK[M1aSB:.#&-j@EVha3Z/S6I*$a)!lQ0MV#ePJ
I3F`9ba~T2H~=~MaKsMbbJ9F&2Ju(bi")_!?Mxc)[ZP8?->ILM-X".6}dF+#@wYt%7bTpmAr
eldFDNv@gHK:hZ#i}
Vi3g=mii,iWpk2ywP77slYP=VY+f)b+%/E}.1CZ3zkUDPAJu?K!R3ZXj>pE!2m,p!kCEuhb#bP8vT5,h&RTm*
W=5&}g)b]`FJL[6-RY<?JyTxYF4bKIJo^L~wp,E[IVwM-]`0;5[>JL1
jI>s0Cq
QX.>g2GreiTfrRTL8A|;29Zx
fGc3VznV-jBW1jrRBf2i$8
2,vm,NriPL{jTgkyTD7<1;WbB9ivHP5Nc<%PKNz?%F/:!%IK%,U-R.Vq?h"57VKyI&t0.G7FOZ$_;:ac^=yV9hS,Y8!kC4[yN?e#lx^ijY%EgO@%BqY=+T>7.iV1Fq>Dcw|sLdGgo$z?=m)=M
.X.y{Z0YeP"NONAb3AtD,u{CV9vBJ2?:GKY/Q?|j%FAU5ofdqJQQUEP4#A{3%#~I"@!3ib;;2X
td2-(G?*r>0sL@/U)VgE7:p:FJWHm:@U@.y{is30vGE^*-2|2&B-_VxXtj+/H4Db$D>Hb2LAu{PtVBdty4yGgC8v+[fE1_y=VwHj`@Xw/FTx2;>r7]lqBSK9I2l.g7^NeieqVtM[qQ1m_D>Vg
n-(0_.8s78&M,X+%Jb6ALOPe]y^m9~%CE7+_:qc_yxdx0w()5#soYiCLc>S<D@!/HuHv06.)K)uPBQDEbd"mi)JrD/Wq"!1#VV!Ppld>4`neDY=.0iJkS@D#)#i=Kj_3R2h>a^@ig0G.d4l*Gxts*?;U.qvXMBguZfP_9ygfW8%~Ml"*t33gCxn^u~"QTKOZdQYbt;o!9_
lC33/0`#k%&I*f>aR-Xxf.9A6+c(~kZbUT82fb
L]w$9NNnRKI(.!7R-I]Gl:C~4!LX`-AP8{N9xV!D`dY,7v;+@jdPaKsqhb9<fZM-Pt(SwrrosTS)F(sQ7x[~9T@s+<?JAlQG^o93Bd#I1O(C.A/
$riB)en5IW]=djLfN9mOMe7PBZ`2N(xt)h+WH#GbK[ya^PQo_{Pu2(,V@M-O/sPUmSan.Po,n{3{w7!wme%[u6H+;e`1@2@71`P4WNeHe#cvQG<(RY@n<V;e_g=|z%)<o%4JFHIF1JxZD_.N6xe/+n^jwaj!!AG?PUJrce1y+o]~^I;dkm7}rf`O]
b6h2b8_N&EirPNRiBO*R3:dyXb/zN*OpI?<|pweQ-Jy>T3"[NK-{t
&P3}i220"&5IAY2T0Fhn"ygvnDWD)|
^RYsv6h)Ulxuluk/4Q?#x^+G/>:H?=+*puKbb,Vm>"EE|>v"l@8&T(<;MbOm(>-sIkwo87BaPQm@#se<t)Uejs/]c`<2.uL(4WRS@&xc<$ZsmOshN"UDS[eq_bE
IK-d_"&$>&r8sM:j?5g1-N&bRX:3~fJm[]Yh-fHL(sXK@*$mebQeQK<w2amt2>4vLN3Z4fp(]#R4uSUwwIx3eQJJBMOFf]4G;LPg}v8MM->j-9*!kd8?Qw^NxR_-n"gs-3cdY,2-GFB&3-V*tv27i&.:l@3OOxi60Y)[cQMcR4-fJ.ECe"KTV<m/{5**X+zdlv^_;fEw.wLM{wr,pSe"L9QErOQM#0L$IE(%OJ!bek0RRbF/~eDNl;(!kZ%$FQz&?M;N1$40?5ILm78H+FQy;3b3y0o9X!39CF#=kZTgYi?#4BD$Vjh%DSZImlK3p"}@7c1
wT#HL:*ieZSYYWiY9vy^cZ,7,,@9[4F;ZFoK`Da2&5BD*"%+O(e(#&H$
y2r6
SE<Y$nQp5h^,`/T]tKTA5&Hq8bBk73=/mt0S
k@epgJ!D,oNCm@20%e:H!GS%TsC{,@W?;5#7o=UU#l+0:bfv.Fo$CETRYnlkO/e9c]3M_?s|:4kd?WVh7<s.<SR390E%0qm!q<VnMHBQ?^:`
(y`)u/j>sh
uNGt>|c24qISDa(tC6,
LfBI-`@&u4bUfMA2Ivf[A)KP9##1yUJMYX&Sqr+T;BiLvt(vh)S^d9Y
ve"QL<a]vXY;IJX5Wv=lcQS?9#?N>(ek(!-W.+q|@z,kd!tzvWd<hCia/N_^D--ggNl~AFqwauny+6q%dA3lc~@bI2%qQo<tpF&q]RjvPU#dmKO2qR=p`!BIKz_
f7Fjf
&`3t0#,ZtMW_h
LB3+>GXG??%&doLc5%1<z)QKx<?O,~*T;8cfxf?2P;=Fk
O]Wp75_pMbb/7B8z2;<OAG3>XY8QAa`+U5$,vZLGq[@-k]k=G>#pmp4Oxp=pY"GX#k/<dzj<.,dR@d_u
Cm,x*ScY&r;E5`np/CQ0p:
0-o_c:iII#REHtQjNp4MYRv]vX[oYc4Ak=T]9gTP(c2hnMek^^.7L{UMjfszu-0h"B7%d^syx$H/S(nt?_L!QOY49i<>=TS$>[.|.Z,~5V9pNH
t@dt[s7.58>t4_>vTu]%P"ebZ&SCq&waR`6%Ud+x%4yF.uk^*MD)yo<^`"TQ]]::M@z[KaL9XFC>K*`+ekcj.,CAP6vIXiL,F

Z6dN_m&RC}5H-u9f.3h}XXsrEB@k-Zr^oCZ?g9C!x3e1v7F?.fn_qDp7D
E/F|bndvO?9xWAm}LM2FR&v|GGphgytv.hjzYX2X.aesjJrju"G>4&:{.SpU`<NcJ@Gw
|1iqu&]a7h{ip*sx~#[D2&5oVc2$6Y_OzK?IeQ73M!`G@=
(+HETt6NnUVa]C=ZT?p#c%g=fOs}LfTy3^QEd94!?-"1k6<(5Abf-vTO"x6f_FH(LU!jlNu`
g4r_":~jE4G-Lojp2**$!$|@gIm.;]7y8b:E)[
d#+__^KE$ACK
2^0]dl;.RELSGt$X|GxHs
o^6w_BhQ0h.IH@T_QIp?yTih.X<pRIm9cd<<Yvb3QLO2%wq3e@nR~EFgyDzaF9A.NvQN>n=<6BdvV$NE_[>4v2TxgYBi[$#=TTX5G(V&qJG53b%:X/}jQ2V/3jk+y%3.E@P^Vcvr-Xna[vy2SR1Iqn7cS3UGb,+;58giw:0i8JJ.I]UFSYs7eufm*58]$B~9iF3[u[/D74xUCe,TLvhI-sJhE8EB`Uy<QLpw]/{U]PmrY
;f
l/8L>5m(!612XXS9IqW?P9K$&;.L88V+uhPb:$*X($yGd<?%@bw&#[K?CRp)exfhbSk*a^loN[FPQzd!kf<AlZf9XrOX0|f3p.(G5ipf$wC/$4oCQ0p`pHg]dQa~t-:#F5)IWhe-k=GZrpBj:=WqEp(!ZTf,>z
`t&u(g[h_<TW1
YbcXn6$N->fW*%P2$2ms)Femq
>,<cyTd@feG!yt#X)xuFL9#o~Fk-f&VWr?,TzgDQwCl]LW[gzc(
q=_&8wh:}x_pMd^/Ppb[%b;9Vm&#Npf#+d49>UT3w)+%/L@3GY+#`bzgbT+dhp
cKmY7=p0qVW^q#QIo<qQHkt.[6h|RKLpdKS<#kqpu~NA6J]#/s!=;62h]
sr?3Zk^(J,l-$2Fk,!2F+o@$J3b9HNeG]JqUscQHqbIVmPOKop(s,bpm6QD"2+d+3EA;Hkfm6$Q(+Cu$W"kF3"t0yC"EboCfu[8`>h6qD$ZC.rdcEO5ywdn/w<[SXa%JH,Z`jn<*Vpwa:mt=1PMk)&>g(MVIK/_~r6l-D,r5;(GGZ;Z0Gte^,$Q@j!L/3<?bHi&_O:l(u0LN<1VX,8v
Sf?}/[az)+X>Mr1gS{@frr$>B+trhrOC[Pajh&wJIdv/="hG=y[|&URR-9g.J.O^t9".b5T+t<dN<Oux#IDG,^UuJM:eul-)6J-S^-l_
0cBx?=8T4*zy_k+PPJDu=ek7oFNdQbbk~"rGlI@*qJXuU,`wE*MU[M&f?1{?"[M^5rN!g*];zqNB%</@Z5VGU&(;m^Jpa0S+um&q`6=ln&gz(,{Jqck!ZVS!@;sq[F72.A<Lb7
XgYP:UtS(`Kor`Q,.T9"Mt]#3)f.AeO<rL2ESP-"pq6X(CEi`#Ng"0IwSz4Qi^IBlYcV!ms,a~CYpn&OZ~S$&jZEomLkuump:}f@7eB:I3Jr?7)YcAxxii0s8x.s!He6o.i2>u.6B{=lTB
J+0w;<d]&94v]r2X^QoKf-(MtL`I*u&CpO?3Qn+Btr|x>Ws9jSYSPTVC}Q-o5iMO~-zIKy}8aC.9,n^p$L<$i5Di*w;cMF5Yfn3+pypOi!B7i$<x$@~
|.r6)m5^d2yp@#>p7bj=qWh1+jO1WvTL3T^$60%b^mA`9G[PGk,nU^R#dks1."-QqZqI1oXCk;|o)`p%N*CJe@<tv9&`UEc#vyc_Jk.jCbcCW!T;jJJ"(fI:*[Q1On^4UE2Bx(cv2nyOVYuEEEJ404S/`R0w,Et@obdLJSdqdnfLzsDYBZjBQX(I>ElXU=j$gIb
I(1u2wm8qS7hF5?G^.&yaIwUE?>QYy-0h"6e_`@!rb*?Ej9t~cT;Ky|k|d(';break;case'bs':$d='-Zu@qaMD9*70L8,%A-mEYi6QPd_.P)c=xSw00J0L}&DmNBUbXL&/e]c8T%~u?PkS[i{_7P5xf/
.{7;^%,i.*$"!
&h]!l*
mB@
dl?bE#6Hsmh6f4_fM9^+W>LbG1~t9dc^cCI@9n0Rxt!Dei=y";cP,P~-*[Bs>`kh90eWF)dowtWj^J^g<w03S^k5KwLp5B85q)_-{Ayt%ZDqYw^4.2*.HsLn8!AV4$YI.1^1
v
[S-xUu:8Jgn|mJ6T,nvknWw%iAMpbYw=w=2CE"f-`{nuHCcfe;q}&0V|^rwpnqmQF_Ln!8R(v)k$nDc9xcy;w:"T>!)`*nY~$J9*w7-k6vxcnuF1`r/5t"sN/r!lVgOKYro9`Alu[2hF*xJfmwe.)/wft3cp&:%l=;[Cgh+SvgPxfdVos,+S83e>bzgU#j,8mmT^(0ERMiQy."M;eomk"C^Uj.cx7m@n>kmfARvW132^qkf#VaJ"RwiFN+ElLuslLCHy=xZ|in@{3O4l`{MxFh?C.`>d3Z@oIA1g@v"hXk)M2")U-ZA{WRa{So*Q<^K9<)s#C5FsvmcVZyJpXi9%WROUo.3~({o,QgHG/EwU]0(/Q}0xOi;>2IP0.?XnZ/8G.2$Ux6G$&CdDsNpUZ?xS7qMV4wua>s>CE0-<4{"N"4=1h*j_EWA^IfM.o"0;nkyxV+;|!@^rG~r$"cxq^1dy2e73xdpmk$
M4m?KwFh|AtXAlTdd@ho_KJfa[;)ukT?Lgm.7!%MQ,V[R=-g[GjMhY^d&an=j*:y26kEaMHSL#a6$>iPpDMkzy~ITS!ZRx!X{kysCRxDOTd:D6u[ixnc8mBg[1<5e7(35<|hmA7/(P7`CmuSdMEQ,Y{LND|rlGo-r[{De9X]fS57sRfl2,Mhe@I^2YG
.>z^Ub!_Ks210[^so6*7sVoj5P%$a?_g%rsmrW%)+Dij7Ip7GeHgCy!:x.$?!84>3P7(S)L!pcvZQ[M2bwz3I`]fJMF.{UvZ_>y@IKBxB[@r$H[K{AsZ`@KY2O<V=<Rp
M7=1]NSTxor[eMHCppkPTI;57ExE0kf7)K&&fS<a@)b>$m3i*+$~:L,M`2IYf|08+k$Bk/ow!E.KdW9Ledu#/9c`V%
L^)8]V^X(](w^6OS:PJBz`e
x99xGHC%p!(_tX@x?<vN-oGc}q@d4-+_He{stKxs_>c`(kMkB.`xb:~%R_JuLE{xRyH]"4oH1p~b+N9kd1;15W4Sq:+*&b[s#/ak(6j0qiUg2lZrV+EP.)b&7>m3.eeoYW+5J7`B&gVnyh#=n6.Euc+So&:BjVgBXdvfRQSkpbVRl6Y;>
xHAD{j+UaykMGpEO}"ZPiQbaVX_K|,"8:(#`/6WDo*:.5[>6@_xs#K3*K:#KyCbNk%DM&R,Ll,sfh4vQMJ>;x9rn_Q@<+S9A%(2>ny&,gRAI,dAb..1<Fc@L4eX;F4dpfms<|-f42XZrH2b:Q+A*SSFn!JS3/-hQ^GmxcD8e<YW-0;-f2aXE0i[VQAc#|Awt|4<;g<v>%S[+CE&Hw>EAka3"~S!BcIv.
n~f+tm6K(^[XF8^p0%lEocd+A<5nPd1t:KUx[dT3QN2<rn5HZ?g;`bn]rXs[<;G!^E")
@C3EfGb@(vH/g%ufW8GY}U*mw>:GfnQBh+INM917cfxV2,)Z-e-:wuFFJ+a#d-?&-f{VG3l<F?Lw%KadWi"SP9n84rk.31wV,8.
?pZ)bJNYle<D4O.f0i+c_.hZ&W{!Yh4?xkY2ygZP]k0FxM7C1_/0Pv&^ki8TO?:&&A-
TC}?-6g5[D*X<PB@B>Ed"Wy4wy~,?Wk7SZY7(=vZniw0?[~w=m;`_ul*JaP9(/<^$jzQ2I3Yp
oR!GD`LZe]wbeA6+OadmYb[&40sR2;S@i`ol1-Kcj2Uv+#F0I1o-EDjD^ava)ZB?{CAK!)H#epUk:C*U:]<ZFH
rKj<CAoc(l&gaW<VuE-z-$.BD)x=!@JR[Bj
fd-kq_P%$I@W)gUd,P`Ahgu)![8G+9N~blDJa=88%<8faQuWq.Q/@>&xUnf"u;RCMYBCVnCeV@i3ea0fNyY}pG<S!f>."9-_U=^c1<hZ!`L$h=m3
fu$).6"Ambj4[dFd9TeE<Uii])/ey>-(<Y[]+S=RoIqB^BZdvW6(xOKSctrgF6/9z(Agkg0"[N+
>sK$"T=bfZwV6WjfIN{e^>KDqEPcai
sOf&P[xF`GN.*4-xELA$cWA=6)@$V!/i;EMXI7OC;*hGw,./t`G#V+InrksM+AWq0LD,YClKdLS29GNi%fC.-Mf>WoahpPIyp@Cwj<y4AQ[1]H5&9Ffiopcg!MA$&"!
lP>j`d,`
?_:Q]

:;8tBsPu#OD=IFG!5U>G)US~D??LA5)>_=btAMjW=C=M(:#IHx67"G@1*ceX>jv=Qn=d^ThgbmHA>+%d4k!NLqkQ;"9_StO`OBWvaO1Ruqv[!y)#P%+,I,uRB[S}jW%]ecc-60O8E~][5gQ[%qD&Zn=YCN3w*XJWnHZ/`G8t^>EV#pT/NzO_i@gL2855:t4kj[?Y]4>FZQ`1SwBY()vcJ$=[inN;Y4$~68OtOsI7qJ:3w~7_*N`2Jd
8NQe%9A-qE2c0s^$RjOS+%aQ&B*r$)
;xU1xE;;@U%wRv)V`,ZD9rE+9A_C[qHU@]IX;^+a!f)
duYIjEh!YF8Rpya{y"L5ruS,lphj1O
?e;u):)BxEu%DC)3A]hS!5pwo7=0-a^(W<Ji{qX+WDkH@Ti0#nQ9q<4mAg+/"j(pyZK&#LHp_H>Cx+t5on9eoH^raCO0?db/m"9R2ui2PwjWM>NqVVfA(HVSWkC]cjlZo+uYq+IeK@Y:_ge58uNnT<7GF3]p*6!XA6a^X@Jc;R9M8C7lJ]A>/@sC<[s[Y<k?K;i13Zg
bO/Hae+C+gDMY63vI:rhpIOl#Q<9i?saSXQ@::vo+%7v?!ICjvCy_e5)9ip`G$C<,<:Qf
u,qU--?^1<[B[?&]cQBlrHFTt><Y8!H?eb*^%*!c6*G:^1-9?qn@HjYYR-iST6e4x9)iw?[,4J7cvJGk-MC1}&W!mY&olL[2ET7/cy(pG+T3*t>4;%/0gr,`g7V<q"mnr=wFQ^Kp{Yb>zH^$iH@/)vmsnL1Qur]ymo.?e6{1K5#"CYsh7b|(<Iek$qZ6v;"W|#1Rr+@XuwutC3[J5QH4=O$w2,.dY=BJK(+=6la<`#^OoI|?k+Q?>8.K"[QVxbXq-TRA(`Z:Z`XyQ[;=uuMTmQ;)R[|]lEymd8kVzLUY)20X4m+sp?1fPwduVIMa#Z^_%m;FSFw<u]:E=[K[$16V!+;ANAOSPs+lWpUO:2~`-8e/2bgG@gQNuL.t|GM&|;flpgG>R59i=lqNv_2LAiY<:
#A$Z:cDCN1xaZ=n[Rd^8}q7*"!]*xs~CmXQ^!)k(0#_5kW_/~p^XV^:fJKY4}`-)|67rl/o8Cl=^B3`IZru3/l&)rcoW0k@q.l7:VD]2SH},S
OPS%#+w2:v&axx*5bv.w?lb<6;Kc@92]}[gofZ|kdDk<%+!J
>/rYh4bNrv@k.P&HlwPI@E71l5h&<.Oj(0liR2iTJd?2k0-hr#T.?JH/r+8~0**hWp$b+.`Fi^o8y%TJIOgxb-vkaav`O&ykNeI6)R`{Trh{E~r:(!!"bj+-Gw0|Z&Z8kN6cI]AJA1EB36Pb[U/L2&=OEJ>BH>,jhZl=*.JMX[3O#KjDsaj>^i*Xpr*5
(="O<,U,Rvl]P5,QE^UP8Y94)#8=x/pui"+?PnL*EVUAvc@(Ykx*)"syv6tw<=:BXy7
6jEn)RXYHRldSbTcIgr`3nnYhiZWDD+rJ<tf%e(Z)GDo*^#M|DVT,P]W{9"PZ/F*t:p9N=Z.U/vBF,7bj7+pS]*:ua{OK5+kK9{NMPhJTF5/m-Sx[+mmCso)TIeg7O<L0LH;DkYkG@+;US8wkl{^k:{hOEhOAGThwY/FHj}t0:D8@aPM0a`(k@p229G0^R@)EJG&Yi[E;I:s9_Ax;mITH-!g$eB#
[~_`lB(XsGs1w)QofmiW_}mHKl@AleJe8qN0KGW|[<rz0/9~b~m%>)@#:}ry(#.s81h>2+jwtsY
5<mZ^Wr{YN8Z"W@X+/rUO&Xj^gLdtNA8e$9+%o^k0[a^L4%XsP?m#W=e[ZJ[P_bYs_uu[]1{/*A#,,kE829l(>
gD>kMtOro:|h!-P"bbf&dQYo(]cx@>Hf34&X8wTZ>j&
W[0oD6"xL*`Let=SsZUrqZ~[v6)#*Zr/7/t]#v?/#c1f.uIA6=YC+C(fnsME2Y@.kI|n.Q7y~?vc]Cg#$>U[HjGbRL
)$&tYcbkRLqr[A3DDPe/*hs3y|L[htd]G+lg1wq~nxlo^]Bufb$89?ZJPi1M^"chrQt
`KreAr-S5%xM4?ZlN:&]w|d`z!#Lt(wT
ckDAIe~0FdS%q1W@y%7Ynrt._&uD=Z/f:=|5aoJ066=H`?Lg6<w[bl:&5olfw
2[9tMnf$v^I(/R]^qbFPz&%yC@
.c?XO1UCjja^;~HbbJE*X{yG""';break;case'ca':$d='.]^ALaMD9,{0L80!Z-m/WhooD(vTa*d"9:5NR#W,s:94Jn=4JrE0q9N"P83pOnZ?6$L2Uv&!]OpsHxu1X68b/iCf>ieusTjbWK+^+JA0k2pS=r.6%fZ]u]t-cJF^7Ic[zrYQRA4LZJ)t;Sl(lcdG]E7MMFo3W_hH[,QsXuGOspw>TAT!cB"i;:>(A4B-(nQ18U2rq2p(bC{mPgB;y=SUb?]qU6$ZaX9o$Sh?*TW#d*t;sIAyoNhxQZ/kWTi]UG=%-gAh#:yL-X7n]bf:tH)a:qiso78?OZjMb7P,oO7n=L/),un]$A:,WG4Rg@inH[JoN#tT`4?LlX:!KU7_am53ewtx3W&Suad-QFzY[4~`:P~AqC7q6UO>:)cJ>%YJNnP0/;p;sZg3K]CJYg"QTP1x?cWmJ;M4~]&h<?PL)$sEElWKf5g)iU-ZXd}!;KR%MUi9RYxXhn+bgtULiRd_KAsSi`ho0.BBTU+W`_*RPDuI/6hw*?2j3smH31@=AJm?6a7^{
7Z_)BT;z%9,IAO7`8YV=o[#Bm4Q1Cl.
E;a1$OT+&*_njMD/3[Tbm-}15l*u*8>[.VG?zJw<2vhmHHPUGHKADa{Bw,ITzRh_.V/Z7G3bD7Jtb`UucrS3$3o;2*_Q~<&C|h^W9a(r
<CuBeDJ|-95[+XSK&ywr5?5IZ1!fGn%fYKZN8YL<cAX5=0sYDvINZeP_KF@kg/>t@^yJb|<
@lrQGzZ$,lJ*up]fCBDv^T:wK^?CS?_;U",1&]Pu:T){D510=YiYx|`ZjtQx"j=9Xza3nl"?f4>:<i3#4Ee^E+KEAF9K-(eb9noDiV>nCm([e!EcQjdw%>sYV$u}rqyCe/7#@KLtNJjtc0;pk/S3+w1fJ9OALPc~?!cI#)3TZXYF

wC#si+l_Y@F)0*^|@9f6X=RD:5!qjl-w%R:ZA3B$
ub#p`#.1N<^m+l-ABd$h=J/Dp5O[@2eaD+FU$VUpb3Q=@=>1gj^G+Fc/hPb@9[
i?_tqBeO.}v?i6T1fVW(`0p9gm<+?&`O/Dmq$^&8`-^=x*(I<y8i)V]i:zE;a;:u4(Ao@XaX%t8|:_XE*A3aFTLV,fH`bmL)S|5H.V4-cEHX)64Of:GBqs<yrkTavS;B0T<`xNf=G50{Odf#q(P>Kz+^?DYe34!@CVSk#SOa(RbqH$pch4Uusf4}lX9Tu[*-/UOt2[5wd&Iud9?Ivf2;_kmMii1>:8rSf_Lq+=AM&bx5C[dt24e;@tu)ca&{wmhQ9pcM+w-+-xQ>xU8WgTa853X{WUCcWx9kqWFjAI@`[EYx+<Fd;KwW9&7RlEgpH?9(DX_"`|Zo5O1Qf`OeR+SVRoZagfdo
jE[Axldd6JOr.^~L.p``zF5B0i.:wF#@;F]1FY{0&Gs"(_I_%;}+!mlE`bF.~ieZ"3:m_wEBeiPpYS,5<Q@V.q.bDo/[6]m+|2sw&1>b.XF54B:wI(
(.BKAX:%?Z/JDSV1KXyRWp^qc)h1*5F|c?MtKk)(aG#:df/O>Gr"vV?w,1h46qb)1$p,
t.]g5^3:pB55[1QK~[]-Ld?,h2%RvP3qkIf"f!R:[O29.AsuPGzl&YD.uL,DptN_NDq8;KmT]Jt-,(Y8uignZ%1h&T1VJ%Er^/Gu~`9&gTn<]
_Mf!~Rj<PuGq]E37P1RDj64W_%`PrS(Kh,0wYrg]bsAZ/UF^#;nqsBG?LPY[P(U&9pp-t9XY(1a-lA{WT@s8fOD#J"!X6!K<mrIqEL-YH_sYk.v!m2zxeNL)xBi780[e:%3n=yE+U$F?|SLmrNFoCuxA
z!#wR9^]NvC`Hp4#Pu+T%D5<[X-rw@w<i]"~XG+=PxZ{U6wt@
ohhA-4_(<Mg1)wkh[]paX:[/$*^1ASFYtUb]8dTkKeg{z(<69V,,1V.FgiTm9uP2,WEsQOy`rc)DMulH35^_t~"5EYKId3bGDonlA.:|lQ#%Glsc&,hZR7aCBz]i=/%k2*Uc#z(Chd=X?Sxg/s>=?;_oEex9et!l];#04-N->Yg<jPaGl6!*J}s}Qj(
Q.:*Gtk!TPmtjs+CwEjnV^*tei4hJMUeEcdCKG(d015v7M+I!wbdZs9@1?h,1[`*tf;np"-&r=_[,QDEuW3_W{5hA>eW`cab"4wQm(rviP5_/-U.O,Kj8/?Z^
j$(Kq&^X?nv]bP%Vm/@i03:NPWs|m+#5YY[2Q6cwMNT>W_egMOU9-~pOURgR*tsWT^vr)`52sbi|"Fo~R"UM->AM%Wg:/"kv4}:ztC)Ip$JZ5WKADCAC[:c
J}/k>>f*oX(TZf]#iE:pX*Ph57cXF[t"XA:j=Sw3-T#C@v0`GGA.b>Pn75?=G5f<9>/R;d#g/v3Yg"3g`
c
?_-hjq`Sv*p$2yAg28wjRsyWS>g."}1Oh[LpdX-h
L&L:+E%KkN|8B0}.B#$,rV0WQ-N_Bhog7?ZS"JtE}5{v{Ljo#3iv}7(BDroPADdg_V6k4*+D5:A*:H*9(]tU7A|ld,`s$TQO/BTz)=@1A%Y1H)1E+mY+B9ansVdw)6,@R9yF"XD(CIV,M[wglYd>4CQ&]%&%QJXj;/26S&Ew=ihDsTAj+6!OGm79(2>0Uva<y!yBWZ~q3#kLOsiSsDJDlVr;:4[%i`r"2]:[m/5pzQyD1".h-qqpB0k<s
Am/8sBE>E[=9gem3dF@m3l%jTOd=wQd-8>~cB[FOV,>pW((GNaPL~l,T$qRL)W8LdH=>_%2RcZ3B=QQ5Cv&Pajs=p9Q/uv89t-ndysH:Xhhl:
fgBWEfH=za6*fsEMSd8$kJg9oKPAh@G"!VAk&j7.s$m+Dc1.wj3;*VB;1Q%fHM67+uXxutFUe_l5Oxu"EEmE=9I>RdckGB:yxlmN_NVtBfjeKaK,>O`1t?5p9Sc)8gUH./|f"b&JHNAGW]~tmFb?4etP,Q:>A&yn"S7-z<2WP,K.yc*??3zBo"j69t"mC(r#~:54:0cDey@Vfs?d<_S4b`9E~((rkA5"MgS7ZSNBM44!mG2"t_)/3RD>yTg(d<"m|8v%2h9Ed-]#<.e!=O)3q=>S[W77;.]i=*
C~Aw5(W6Cg_"r.l&(=c{nGEVhuE0jO[!Isgj`fG(F<EPgj;ircojgK)GT`dT_C5Cc|R?=/]u`%&aE<Gcodr#%;N4sK7TL!^-iywTE/hI6ky+$KHyr+!mg}LDA49wj?C6;*RsY)CieQ"[:jvU#Af/6MY]@`UQvTu[Hk3nf3[QlDCFM1.n&
lH*Fhh,Gek1LFX/eg?qbC3rZCrto2u>yD=90qBh"X,DN0O!3)0Oq?e5$TrqmA^K#N}Y~h/G"w^>K?@rh@
weuDw|7zv;I<EuSXn9L>"rZ
NBofovW?%Ft)U,%QF@*"9Q?Il(
8qW#a*)rX>;Dc&&k{x]pI@sl"T5,^uNFFbz9k0Kim_GlH&,0OB^)FLhSLNh1|@s?1;hE8&5,)*0HDWBrB:EI9:`Zn)0[~3=<Fi*q4r0Tu[Xn#04.y;/Wn9F/+C~r7,l%sL>?@k*jW)o<[iz*P"Oi#D)TTZ,_8(G=MFMV0#Pfh.Ij4g:?)G@XPCb`u(m1*19DGvpP(iEh!E>oB9p5CNSRW.`8zF%6z5~O:>,&mIlbKyr<d/AF+1F.4G899X$#fMv*>Az+q(#RH#PS-qlv=aC+;+rMp]TmffxnMY+ce
l<Hth,L
*L~<&HC<M9YBDP)KEiX-ldcM,9*y?[L-Ll*Nw6waO<diuQ{m[UB/_8(f)v=121F`_!#W<NIm(Td`u6L:>v-VV_(IQP|dSQ%
}Mi0^0~(3pl^[&Ar|Ikn4b5i;R&tu%
A_4a4ke@<bgw6M<b;pa:l.KB5Da~B(QVRr@x<yW?M
95uq-|)|-WV>_}_qqv9ogOsKpC!U5QN%QkMBd6KZeF7;IxK7wG_aI{m%Cb0>fMT;?_bbC(s!*5_&]$MoRR7rA|GS,p$eE
_p4_v"iBYsAfp
_X>!&Db2Qh7*rgqDQFhP4.#2m(33)|?@;-A4<{Dvc}A~<rbnGF8]Ea97:&4(/]MK/xDZ*Kl/N^UYvb-5N9(VJR`M//YgfAO~viJp,rRLk53>W)`vsL7EN&)fENgel>0`jfN$73+:Ts-UnkHaq.7`FCy~I;ri*N&jwOBWf-Sf4dbw)R65B1W91,9L[~JUnEYrnu%sK}4>YYNhxR>:S:Q}5{Kp1nm4FQ`6@18]qF6(/pU5wswd1w`#0-F,>)RhBt:~nd,L"m)i45MR#u^|Z}eO.#4gqN"-hd;@;(bo$;KwNN01;rl3E|NUood}vso5.kFu,Pw0B@!q6v)jeoaZIW&5ph-@+B*kr,L2s:<Ip/Jm5EM3Zp7{j0Rb"~pHME7^l?U6goT)
qL80$DPUzIg0gH^+E3i4l)ju4,yF:`wY4&W
zi=7+/ngL^IV7P:iX!vhVt?d.2z2]n+1nUzt+#DNy<eN8%,GMfK@TY1qV06u~UBW{C;tI6LNe9S[!sxhQT-H!xz>;.sZF5&^26xYGc(EvB2xShCUd/NBhvk$4O6=$NUO]o+B-v6Y6u.xvdOxS!USlSm,D!!G#T[v+6]Alxd""';break;case'cs':$d='.]^@j5I.w,{1$(5+^2`gpl-n.%gmV,niZ3slKAu?Dc*[:?Crk
bxZ4`Lh$_s{+
8);#D)q!bVRx%XMl+Y%-.dg@Jv(y!hKzV`-gDW6"vau3+@bRw^EX<%TbgcZpnpA{]K;(qerKuH&_qiW6;b!hq:[Z1dDwazk4wd2Pp!?P?NA7/agJ3"2(QzZ`*]f8Siqf8HuY?{r=<Dpc6R_]0]gPhjdmBQn#0;/f@:oXD!p:9TEdn1oHeT4o](Znb;BLQwSl2-t8h^PGxmq]S4Kr;b
[y~R$r$]R/d7wAZ2Ow6xby1sDK.Jpo^rQ5hsyh*A=W[^O.KMn.AZDsQa6bmki>!;;-LqCVX+jEjCb$
I:
UsZJtN"?CX6hD>eKV
]@i
sWT7s,h>2ukrynJ=v9@G51nuQjh81_&2)h&WF@gt$%xwvwk&v&DxNRV?Olgh)5`k}7Wwn[)Kor1?+mXW^EX?S?$#tWcUx:4`30W?VY1,l[rMO.$[G90tt)5P92(4#/(Pe4vrjGPq/%5))={ZE0
xxF@mmG#8pv!%
cGy$)q9m4oYC6IoFdj
vN16/xEHKtW6V@Orlr._VUVH>gufWq/nTc;Vu^l"?IXW6g9g;W?QOQ=ok#xtlOPo>S~Xaql-T`en,`3Yf-UxEy2D;Y:GHBzlUF<bt!C.dIc]Ajh+AdW>;odZD;J;uD_0ZZ6*%hGK&@)U
Z|cJR{4}A[T:r=9%g1ji1SaX0%JfU]M>r/yE<%uvu*,oqke0
+7B&Ix%K5SC5F]b!_v`oPaBnFcb+JhJ<>(NAni2s/KrA
xGP{"vmOTQVkeemic=IUiKIy<umzh@@aU8V[XsVl[1/NZP/0^](J>"l-5GN|yY,<1-_hlz==UY)7:?wneb_w>"Y8s#MnGhuF
5qK$eBvf)6D/b/;pG;@X>7HcP=3B`:imMjYxZw`y:m~$_T-3=vPIV$^WQSS/tL.AP`I@75PcKh`K0_Bs$aotkxBpTiKD_
LjAaMxchXWfTe`2G-%jAgO|5!3~]l#LwQ/E+wwM_u9SIR?pV,Ad,qpRL?@&vci}<S7E11y
eS92-KPS
GnbTVpBLJ6%_qu;gdK55oVs;Ob1Da%m<;P|]qsqv->i4YNru`NYct5SHjshG1`mUhT?T"Avs;1X_m_gum1I%*ZQJyTf^Hl*0#j0PaXZ)I)fWFSpQ|BKJS5Z4&rF+z)=jHCh<}v>4B0.T9W:dL@oDQTpWk.j]WQBXJ?dLw6|^8)dMR]Nb6aX5`o^Jbd`v<vnq[JiL@M4uG@rcyk?m~Qy"YyOh_lB^$*zBm+kFfmPvppPiP#tn$D%QL4*o-auQ-J5J}5F3wq[Vm]yysK7<{)cXIr9muO-Madc/gS*6<hmxT]>;iz#"{mEkLhRlrZk*]vh1K_*e-w{U!h3nkB;R?b%Ts1d:9#3Md"z@#Cxo"-L_KU]S!A4s;ExPkKq,.gC#L^V1mP/NKg}Dr0u8+I[e/YDo%,c8!Y7?("buu6RBXndmI/4M#/8YAWFh;_j6KHP?->9a{<+uh6imQocQEj*4vK$K,D@"<lbGq*;v;JL.,g"6C?yiCpMjmyL4Z7?8=^_ddlHSOYC[@X9QiLMxF;1<hTJ(J&6vPVp$0A_UJA$;-uv%o"n1
kk"9dC^
_4bi^@SpVb!ZBdL$RkU/,+#nfw5aUA@O%`BZ3Px_g{i)[W0-.k$Tv&8jitELsEDUa(=M097%mcdy#Gh!Y,GXtiD|n>H")gKcD
xTl7T&8}:txz<f8+=_pLiEUJyGYalNHJQjv`7T"(xRv5)B*Mb"YCrH3-S"*o/q8@g_5E(
P{IM](G85RH1P$!R.-X+`5`Ibx8DCN?u-X[#hz@|u_n|qAc^BeUK%R7%:IL
_Ev9z$jux+4{Aa@*Q3+]"JL8tYR}@m7]e9nebw#f_m;zJP
5rLN^&H4bkCSuQaZ2oswFq7sdcc!;97;BG~<l#MJQr;9ddk.@HqX_sauz_mh5LOfTN%V81ps^0S-d+2i116O0N/neVz"A"E--(|v`,`<JJvyC5AoiIvEj^TIdj&^L80-^kNNyOoN,oH#[/yTrwG!KR8tW.G]k$M?DyBo+VTmB,(96h@@G(?jN<,vvkU4yXqZct{J.#-izh>nw75EHtr`HYkIN-<!.<P:SSUC]=yP/F%VTlNV&p51@j<%E%v$T9*?$=@K}*JAUy];O*u3"CEp/`^s+:,&~6J<GpwhY$9*u1Q@N3Y/nG9U1;!(|4)7ujc"5]5]R<.y:@vyPmW$55lX:xUS%NB&,qYkdE7ceVHRQVdIg07cYU[la;G*ULB>8sb0OtZ_?e
D9o$M~1VhsscTT:=Na$].lNT&L`@BO;40
b&V[ery)AIL(Q^f2AfVM7P>bJ?=KwN+JelM56ip=uhg!P9F7*]/s#O?Y]lkh#!I?DZU%q568H-R;VtXl%|Dca$!&)$1lTzz&>c/
az<HT]cAXOdk?vDy
9D`A0U;@~f_k4m]q2*cu9p~q+m`xf?8>EfY.^N;s`aCSN>7v?xt2B%0Rza/$&3FBgO9CzJ*C5=7[6pE-{X*FqUtdhl=9f,,qm>D7>W;G3M&i!
YWqW3Uji:ropCI"ODcnJlDN[%/s?eGh,5I+(UeC;iB:(6-Ll[y_Mg(XcfX5)#OS%"k?s|aGJvos%P=yfjPykIu~6E?b,nbj07!i?8/RrQ7A2bT2831I[Iybt`iSDo7>v4R<+p:ws)u2aS8^%Ref!D>v))46.WJq?t:8689)<|VRhH:FYS9+L[4L@PVoGuNso7,kLD<sLkE=gt
7!^Oy_KbFa*"|U50N"d
NVk[c7mIeRI)bH"2{RDg}W$PVvgW1)N1)5|u}i*36%o%^WgRyCq]P_:!9dDbxI^89I[Lb)EXV#K3qa%/2">80K,eU&A]ajJ!FSjBol&lAc,B&GGIV>%"&2%C%CUoz]5z)45i;&PX_I~<%65I.3uBQZ1tU:=:xL&O~Y*.3T>Epm(QJ!AXdr"
rvf&$Xm`PsY!=3
oE1uL-.QFKtQY`T|9GfGkh#3MQJK@"B(+%9`&:5T_NT%9GP`_@j9[F;<6"L$_sg{ci#HS)]&A}se0XtiGBM#C%x`%]6pIe-$T!<)JSv"(*;-EK1<KfFW9f&m<8@VcIBKo0I5p9CBK
n@dVskH3TB8)#4;-YkUXus6!N.0.46XNL(+P34
"mE;Z
TTQK7Rg..oo$&gMhXh
g9+M)Qe=vbVj*TIwa#+:V!/SsoF`hpsjW@ssd
Qx]RfmB`:)YTgDUr?MNZP6ac4t$^$}`7A4DZN#HymEXrjsr{ul0r!_Mg76&O&MRa_P>GC%M+S9UToKp
fRtz[%db`>-$BNZ[_@CH>5eV[%CWP&H0:$vaUOvcl|v18q!5m~IkU+ez#aYWnyZm36&A-MXfG
*neUjV2j)0_E<813?|jN;%DrDR):=^D?-WNo%ayGi?s/YD88Y.D|U+U3mG!/<
EaZYB3trqV?h(WbKGh3F?l*Xr%4QWgGj])*guI4f*=ayYUr*r0,qfIjZ/WS}1edo>x4Bo([/`9KorriGt,^P/SB3PD=#!Zy?NVB@0"t1YC9j)PjrX>&seJTxHg]g;Qu&/nA[,KYuP^Nt@]s/Rd4kJKT_C#wf(8uehSc&sC;ngpmE0<2bK#I5@,Nnr5O+Z6oD8*qO
hr^rEoLo6P}^|99H&,a-)l+BEpZ7zb5of
_1n;^(qr]Zh&:f)Z{A]i`uQXXAje&Dg>cE$B$lTfA)bX23kus8"-tPH#HvxmV%"lu"Te594s.EAB7w&%Cp&u!CzE&0LU],gr<SYD",)iA:-:w!
xZ6/;+2_-~CAwO"B@jV[P{qfEXo~KqY-YeL0(shCMj"XB4exn2Ka&o,!"[uxGYT}mYtt#6+>yn&mKek&M`5,*nJTVZB
1pU@@{[*xzNQMSScH-<WA``t@w)?!Fa*!BWtlHZL;R"M"Iu=fwao*)w
ALz"<E%MS8!T#IT=^Y^`c$fSQ`oxQ=aV:@+BD|K<RZ=[$b!Me;Y-MHxZ*>){$V@pq]ehScr-vwW%b.x,)MmgAhu?fTGaa@y4dUOHdXrR%J;XrUN*BRWoIwR>Y1v<&r1~!`)yAZTL^g,=qVqk"eK%nhJ8J7r^,.B<!{HK
(a%Noqb
l%/U%[et:CMtiB|3D1I(#w}2M%]8HpvY.OQ@#="?S#Tbl;U>A1B%6`aR=5cby;=neYH&+C8uh"{g5STZJe:[cj.p?EXY4-@2o];EVZ/G**Mkcd5jlRBvg98P=O2gWGaN/EcFvNOZ.*,UvY9:7o1pp-D+96LgWZNv%K`Bv^h;1
vN`5fS6q?x!H|Sl)3Ty_W]EgHd
Tq8gkze$%Ieb0aSPjoZx+DE
iSyPv!4M4FRp$`oKiKt9mIc.3Q0fMVS8f[1kIMv[j1-`22@U?aS3["@jWQ"Q^%Wd]C8Lm}w*lin@/fh}y)BPL84mLG5UYG^XfLC*dnMr7ND@,umZ8QGDj1XQ(d8QKwTy>74&tX>O9
;)
9)dr~6x%0ikRA:uN@`5"zTz@3&8-NgQC>elFgafSwpuGCb>)Ov"sj%T
UH:yba}Eyq2Q`_<!dM}2d$eC?alsQpR1|x%m@nJrNd#IZ!@^78[&=$LFH,^<g1yEGFFpoDbcu-Z0;:n7A40J[ARe9.0[Hp,wEo8.+yswmH0u8qbh9gsVAL}=p_UK}C3%$olIi`6ba!Q]*U]:HU3=3,OFRW/m][,S06U+V(d(f_/hmh0FXO%$=r9lB6~Y4Z}i]YsP^82ifi5+8n1stok+-!#KUiK=j;Rwf%6D{RM&L;&qU1T`pEjwNN&';break;case'da':$d='(X/ALaMAp,z0
Y+"&T4X*L=l]VH60;k*qe}>j,5SHPIX.5(a@g4fz-$I9Bt/gfMogMu:!/SU:Dc/&lO$o!FLAT"lUPW?C,xv*!$eCHqIJ0F?sniFYD9[6Kq`[B=ThJ$%O3ni%ViGphQ0fGzRH*`/.68s!c_VhL1T)k|7vn}fC?l15Dr]<
=VGi:W0M~h?TvU(IOn?YlD^U>0aqL[)a=!aV][%
xwYrh7WkV0&AluX4QZ;T@Ngk>3,%Z.DYvA:
ziVvZmfA
kta4qh5q<G2=rJin]|^+;9[IlgA
bRBO7u4%u)@=2%IBbG&+m%[lJ8E~xJNZe^8.jDG4*;_tF[_Ki@Ah2fqw^KC
`CdX8=c|BE.PjT1t/J`Gj`HZpgG.`RW7*=4uINB4iyl>2><]L-Yf/FHI`hn{J(w{Gr3{lh]@KSwniTMxnS2^=Z5UZhLEo8>UYHJYTGUwposc-C07gxI<"?JK%yUE`5O?a3143l0TS~aeK(3>$
!8PFbI7Mi~0CC"+`N(JZL+gg4[HQSzgEC
bpo|i9pFupkN5(>Rrh05P)ai^wn1v4/OhK^85C,9=./<fHL4#i<jh>Rmy-jdUlN<[i^KYRW5Y5qZLRm##Od2cg?+V:O2jMZq]r6#b16^Cb7PS/pSnfkSFoOEh
w-V*Zols,Ssq^Kceb[upkQwtd4f;VZBsM.d@vSdB7rtZ/;(Ab=jcg*F7<mAo.^h]=RE*poFp+q
CU,h#0z=I]Q(T@+M~J3S
!DfDKXm(m7y#-k]kNUgCyyi%XaOB42R`*3mPB!d;p=HIu5i,=
Szx}^g)vAjs1KFN~hk.sxPo_+?,RMJ_z<
t$+|e0S4eXK?LDN}.}ST&!2Ovyp*dX7PWosF%<v9D.ruOE>x&B4M.!g4x:xM@0Zi]?-
<{X
Kbqhp*<ZAlB1=>>PJ=7r-Ef:m2*kjhD`O,V%.bmOo@0ZA[n%(-+9[SuokpNR7-Hc6SVlO8%9W+
q]beppr1lyhXI$ye-pHMAx}2~BGc@gc@+LI#/hj*_dR`}(-Jy@X^)N6F>ueLk(~J84"X<Ui@sdRg7V5]OQ@=;Pj5ijrjL^iV=%^_khoxsgm,p4U>fXxcLCas.%@P8xH57U|<fmWUwsMQ"iB6nBdmH)jWu[h1=%2RD$W`~X>*G[RcYJw/~1d1vm`%"mb86552}c{8MBRALYi)+qJq4%-95u)@wA,]U=>3H)i&h
JTM)%^$>xB&AuLM``Rso6g=vaK21JOS%(9oVZ6B)e=Ux9`@
Hkz>N@`DoIDSk[0Q*5[gz.$4--!TK)c@
&,FcidqT3;1`w0!8g{1[*>D;&4.x/BKJs?uJ!5MHxH#C]nJb:Y,m,^BufhsT31.N!zl}MQOO,4acZQ6u3L%k>yc^^K1CH?]x.kMX#6>hg>OmiytGcN%m7$(,:QV"4@a"
3(P+)5G<uMxq9=^%h>>ttX308$xb%D%E4Py5s?mOUJGhNI.U%YdNr4$B65bp2aI*H!cDr;//m7{WKw)Om=QFsx:V:J~hbbk$[FT+7Rr=}R0in_VqSA]=,;ucc8meKmcQE+=R7BoT)9Q:
M*_`#9b+gsf_o.9xDI"]Aj3J>@MNT9yG?BGS]/PfNGZu0R42VaRr9Iv2QJHP&/"%O:A$8OK{I7h|xRs~Ypg,_Lp@9^+_+Sqsx
.:-I.K$s!Zx9Oqt&u;kmGJ92Oe%g#J4<..B?dy$n86V=L^v}8,+d6&TZ7D+[j.kOf{WdU=.e`SZM1<4y-=mqN8eTH
7DfW.qIJRo[z?zV&"hm1_obV2FL}D^Cj$HuCvyP}xuw$fv*5pxXDT)hGIe>Y5M1PECk-<DQ?&@cc+4?}D/PygK,gs8RQ[YQKE0+/bVovNR&;wyi7@T"W-<u"B5d<euvM6qI0bnAw9Kp4_*@AIS)s"wIhn|"KpTsG*oB
Dx:5O6okd.Y]N3tpjRt.wkN0d<a]X*173q6RSm-.]s9Ji4<H
HQe?(#(#d,J[x!*Lca=AT(<"%eF.QUL-+!Ci2Ch+#97LjKevs?:WfX%!uIq8"+7r*8smjg.<d>4#(DWI1IgORGiy~g][0]K0:%(I:+G]Q2nxgDwg,%k1]2Ir?"MmBoR1m.T
AS>prbF
(^+)}V4OX(HVcmiSxJuvS<}ta@UV~1N;%w7O<19QSCH3Ng5rc!/_Bcdl9&6u=si8v`n+AwNbQ>,^Lf}-`Ctz%j*kjc$IU_S7"Gv6fssMtAFv@!]#FRbR9Qh1D2]G$P6Yh1TkefG)0U&DB,UY)RFj&mAQ1nVmre0/Jq!byf<`i0|,5M|jnMmdp^qRvg*>?2CypHll,Lr]5S43Dw.rMcse"(ulfIV<i2X9|Qaot$9C!@k[m+b")eFh^
Gfz(<>yd)`vmHZ4R..QV9^;?WhL;CwS@r
2>kx"n.FB#nLOV";I>vN%^|-e_?TZ!Z]jR`%|w==L8-_KI#6W
IU7f[6id@^d)?U>p4JIiM/W$v"`pO]WRlcp1f<%OL<0W/43uZ"`t6?fS16b5K>Y2SRm,*<h`p8&GpqrG[?y=CQou`:=]S"4$M(xxHuMG65X3@6t"=P})@r$6TT>^:IUB)&SEtD;0>CMB+1q1"_?FQu%`QK]<Z%pEDT{%C&Y;/0Ir`GY(}NB&+^94Q
xE.-%+:)w5Ty;#/hUl"Z:#*yh+AP
^>$7/If1]wZScyg0g}#YC.OO2oq8ytiw>y;<^e/(`_@$(9pwfT1`#C,(xjN|o+VTB
kQ(YbQYs*W%qd7G):XHwY?O>JJ(5$.eA>^N~pR6p*;8[&/Hox;>]O#y8u}o[,8L4*n%nts#SD#64pSP&"_W28_$
]!l|$p)~0f"
SYEKDga)Eb]FtYI:R")2XfH^Nks,n)HW-$i|S(De"l$54:u}wecg6V!OS_95j8,WU2Q!8y%hc4S?V+1ebWyu>9Je%RuxY#Pw!4BEC?DXe?>~9WAM_[2,GH)Rd`Ld<2".4[E;Wn-zNaFRQorT&/"g#!=q&v,LR5Z5Sbs%nFe-935*u[@$:k[NrjgY3=&6iIN*!S_f>5[3=Tb3CYBX@!_=.[3E&]<X*:.ac}s"Yq4W/*8Dm}C}7^!uF22.H3_!!H1CmKXBsV[)5Y#iz#3FNFUqaPE:U
W3Mr2RcU+PL1xINGHpo]mm6A)wxh=e@HAhs;/_LhaOUz[E1<PamaNuu,
{"TfL#h-J[,F<-}+;W)]xY*a+R[Ld3G.pH?37?mOQtyJGUhJ7T_4e#jy4+u-cl[bu5<JYOpTQK~F.IYkyBo<0bbKM+TBf1O*oie/Y+H;MNO-(mY2a^RcB6<cx7Gp+2Q.Eo+wDbijjdq6m!$1O3q$Zgq*LMt#QSU`vEkB7b
k})aj5v0g[/`-Btd2p%S_<ruQ+)[/ZDLQS"yHN=EAF+"JmUv9T4o%B_r
ArS5t9mtJOUQkMSohV=_i?jg<Oeq;c06s`UakC[
,rzp]yf2TJ7rsvON^5*@
8P&pwoZJTqbe.VR5e;H|L9#>-;d$"^n3x%D<mLN4RWn63iN1H]1#r>9JA4nOC(vN:wo+%ZmdoUul2P3Bs#Cs&%F<pS_9G&l<MZ=Ujx^6&`jej7t&c!x%t=4}U9<=:O@pjp=Qxo3Sh7rB,Ub~LNq3-/*"A6XM$TGP^]I"61E
N-x$,x)sXBdO$)hfe2o
nUtFC>F%w85GJen_KC1@_$WRM{Os#!f)pb"*jP6hdWgM@|C~GX1omhS:&:N!mAEyoVxL,Fv;5Kn&`nnAF*,[i:W2R29G,AgnsFlY=U*r:Rt.4}vTF]1b5esDe/-rpE3GtT"=(AW^q)0~1/UTyI)Z/H5IM].2+R-*dI.F="ymGpjV^}Vn9;[N
BV1(Nc
SGD^X8G6B`%=?Vv]6ipEo~&Y(I:$UR4d#J-k6$>cPAhNDV31,twO9KQV-yNCw95%5KFttXnUwpYQ=LO/*b"((qp5Uff:E~RqK{`p^|*:?E^W*H#+V6MD[*M;Vlp=W,h<2n7B>~NYqF-,^Ec&CTR;D="?mpS:]=u_4}.an#^b5Of.^!rKHGb
n|AdiSk2?DG|@T4WU[]nwUC&I:s+TeM.::?([5luv>ho%G%VIhaA?F4%WQwJ8q:.Iq@V&{R$g_6^jtbAKAEr_ci[kqv&Z}rQD:.f79r!yK@hg/4LX(XBsd0d,WJAIcWth9*Bra%e#m/x@:GG8T"^Zsk
G(%+Re&ko4S%15LPEn*tFINa;~V!7>a?oBo5Lak0mQjP-u/xB4=%+hsV"-b/_4B[@?kx/aWSTvJl7ya<<5m%owLmN$"b';break;case'de':$d='.]^@qbPDI*70mN&,;PxYcqnp2CTpb]DbxD}ZaGqJmno!`EQIgfv3BR^Oi1x-TdmN=.7xf<gwsE^.[,n
4WULENfvj@yx|B<](JTcb@1a/L[$9dgb,`W>HU4+BJ)X>:E]nI2JWBgr^edy?5Bu<9]LXF;RHM+Y%H7NQB=.PBbh4FL^~qf:4pRIkcoTbKog~Mmkz?f@c1~JU1!Av3$Y"47t.KtBxF8WDH%PZZ84A.:=M53g}^vqMllEnLn(Fav7gF#m_CsBWb+X!a}@+E8VPYL*P%XL>o~H)p+?Wx-kK)0wtw?Hj)=]mL~WhP/eHM2]vh[LpW]:qPU$Xy6paLt$tv=ag_!1.B`Lo4>y:G9I@4b=_E{4;jgvGH..8iE*lQ9[IimFA_]0@Oyk7`SkD?0M(XW.vhj4Kr7*!J4aJ437YBLqP7[.a^tpr_~/%C}VPv{UM
s4R`4ghI[ryZWV:A[a-g5h0Fh1:dOm<Q8GojUN2/b]g4!"c?g.i<W5h2i*^IPblpghiwy%r%4hfv4pQ5d1~tMHfIG(4cK5i/ucZPDx4sfv;wtId@s&KPFYoRh,y6A
FOx--pX:ZC(X2c}]>hJ2@8UjM>Q?O9~<,vCP`k1^aku#6cTVb
"h}<Vlm7VQ_ouhbt?"eZr4/aQv@j,,fsQG?utYORd9mc9x}LwFe,LS"JH)qrUpYBo)}mXodFK)S-*Jap}U$)~[^Oxa@$FZpd,u%[f,u_q-x)DIN[<bqkyU}ykL7Oq1DjX+$n"pZZ8ha`?HYXAN/=DL3gQxCbdlUF~F+TF.&EUHQ*}1$v|Lk&6xV,)])pR=%%>@9Z1s_JD`owh,r!$D_55h&,>k^v
3tSC1l-<xpSHE{)no>^1p_h-80pIEeKLXfvJ?!XKp:d3E]p+XGpKUTj_3t7A;TV<N!y>d.qk^T>yo&&^"?j55S*p"lAz`[sGeZh-V/D%e-RsnN(m(;@M#SBdYxuFER#DtDa"Ke+o*Sg~>`e/:.OH/yAk_g]Kf:.#n(#Nkb`Ln{*X:5+F=,&58Y+u:|/O
C-5t;/>VOupI5g#9~pP^Da!]e[b/6[*[%JAtt<]7T8<oK#wY[K[1AjP6FhmXMaPQ~;WV=){_MiP#Rp}fQb(#60%tJAQ1k(mhXvH]]h7(}SGh/k%cM/AX,E?pN^Nn_Lyu8F6a8]S/WI:M`+Wb=&fd`8AaZw(=1UUlg(<26R%i/+Mx2Ga+[op/}9>"/fP8coh0*%3m90;+,X9gVX7[Ph%vm&QK4t6QM%(r3U
AFQ),U>(C4$:AV5-=78K!G7JgsOzhpuM?u1KM+]z416aMfLj^De[XS4T`yIM`
>=Mk20FA@sL>^@)YXaD%jtf%3tb(0yg[_>.(FtetcJ/Dh2VykO[:0c6?n1=Y3nN185F3V5I[RkupJPXu5#-XpO#mlg!-A+xEY>m$6w=.p
P%V_DAU=VIDVRXu4lVL94-rO>kw{Ln2#w&RB!%w]Xgr=,XnN8^f
(LDx3I]l.~oF#bR8!2[|2/3G?WAeuX_^M&mIqx>7<O8SE_^y6y%<ub
WIXqs<$kR1]U:13OpgZ[3vj0@jFq^.PpmF9`[
;,%<S`SYiOGZ-yRong*a*,{eom9Z-q,qfZ1
hTE"2!``nQ?NcD2
19}g;@F+/<N_uP|v~[tjdJuvwyT9J,^wl)0BmoS^<(U[0i_6`AelMCOxP_$d{mfof*ka_H12S:s@oIjMF@/P_?mCH!j+w/FKF&3H[h:#Kh5P5o3.b;r0DmcQ1N*53T^[}"5ooO$UO+g+-Y+N."xh,o=D,*On)3@AnZj:.UV=FDhN9@6ErTEk#^Ze9Bt+QR"?Gs-x"yR>GI>$DJ&Tt)0qVo(oDX;KAs6IwY`<L9nEzUUjpEP4kx2r$_A,u?i8A314o-8;8[7:[8&%"`:h@>sOs8yw8b{+ymQWiSn4D.7FGF)wMvdNqwLeZ&[0j?O`XTXa)kMgfru1u$d@lLt]#fZCqjVrE<d$U(aWrUbye0GrZR^>3v"J9,b-4NQd.3DStRBFSiJq0%
q$E&buRp`jU,e-Y*TG<X0S&Zi^ElH`k;r$FY",Ydjf?OD@=c.I1%R9F8$%/%mpGA4Y+;]~lklzi3ZVSZvJe*fosn1%SGeZ
L#/DJi!VHQ+4sM+115Cib33r#!U%HoIZU>m$zmEo9/24Ab,;+#-"Zon84HkDw0S_C5;NXOU.yfsbrH(t%4h^^iV::LNujRe>6@z>m>DjeZH-?>>NMQWgjO]$p^zj5={%[,Hw%UZrF$921Ko4-Dh6?e<t|#~KUyAaA<<D8kI5NT,c+Yk/,p-02#+?BsC<V9>ww^UY3cTb?B}&^T3F8@)]C:XH}7L_$y[prP5>GPdv+TWFCi[Xk,51".T
Y,u[JMo2N@]x%c2Sjew;)^;*B<:)OJzgyq3H>@EC:b{3fId#JfQahxQ9JV/5W,&"~l0lbdjTr8~MYyptTkeXCmA]IS]0{.17B#GO9ydXwe,gc?WmM(6Ns/)xk0s4TbpfiQOuJB-F-#o_"4%Zs[;%vgqak-;5<McQ0
;5x
D5+BiX1+%"*Bk/,>VK)Mg6Ldse;n$
Fw:GpP3SE]"Z(*O@Sr@c#iD%JA02`Q]1>)lgP?K@?@t=BB->eoyEcZ8`U3U/zbyO_Diso-*E#I}>*5
u^.<S87s@,`iy0YQ,IY&2FU_KHuR3{PGo76CvLgXiBs.Hc#fu~2zJvhnD!M/!6?%XQGnBNa8
Io8y6W"LeQ5C;rmd|_>J>XR/~#U4HqV2Kx:QOX%f8/[N+/^
ef1DSJFcJ$+]FL-SM13oM)LqM5FmD-JR(HVP:nD08aD];ROY.bKDfwySbr52e!VHW^
C]rX&}L5iI(.-$xYebJ>G<<Qd:Fxo?NzAqSB>}.:0t`PY-obdD,-P}xs3x4UV%;zb;LJ5}CH`@#8Ppk,9^#E+!vn?J!>DQs~:=MTQHt^N&kp"<Z:LSQQWC<R$I;
8[nOts"JMix/;K"jR4:sQsoOSD`yS
aa=Z2|,5Tu9/#iB]7&qoYQo!_la5wfjMZB@|NAnwoNe%h*m1tD<pGwsG"kY;9HA^XqX7/r-7rttA5S8pcT2j#/Jj?ANr@8Yzebv@dEW~jc%8hYaa-ze4*~F?NT!V48#5Y*LeYnupiyXq"y`J:%0p7a?Uu(!iO)fTv^oM^8(,PvoHbkJ_>$h22Ij)_)F~S}SFo-!=#/+6/>SGlf[pgYPeVyKLxkAdoH*60+#/Nq8lnEh3=%o2SM"AI(u=0sAYjCoJXuOA!VKme8U56UE:h#=Kc?xJ9jqm"Vq[@$<A9}xVGWcv1jDiD*SnIy3]r5egnRu|qUb$$0+TcR2ZQl7-QIE?^g/UNg(AmfE=s*/vWS<a;lBc4TcZ)EFf_{*mRoKYFgEKf*t[2i0mH.#?#3yyV8
ZNWbFHAcO[IJ@YoHs=v+!Cx/a?CT&S30.9buE+fePbmZbEN@hhEwl,zRqt^=aeZdM@+ROsei5B:"/sA$d^pd*%rJJf4h_+M9[d=k5Mc+X[Y4Y9Ncm-7qf8uaHs!$..s!%1%e|SKm~=#r^kcC.Q,Du+b_S]E*3Qwjm6/4I:t@k0+h`t@3OCoR#Ad_b`tioOqn)3a?YQ-EEDaGDLqihnKjaf8pi8;>2n?D6!}66"cvcx-tQXNxwj9mEhUS=lMwPCV%d+Hw}X9YAy%tfNfC3$ghAq9)waqYl0`cH8?/m)3jsci0H]N4R,YvQ*.&p(3xzY=]K*>=JF7:jkV!}:7Ve4uc#aP7:_%5.4VPs6HRY<mq6o;a4g{^7=vUdpKO<m
7<f>Fo=D
xk9mmtq2SnK7WH^/kj3mi#/dx5~97F[Oa/3^88W5.lW,;_od9rIRP.mFw7bOa&YN6E?#SB6#5O>_}AatZ6>B+yixpG/B9*M^,/Y(x?Oo*wM8=G)Iw>a^|J/N*Jx*[ri_W6f8x#[8@"IU(wK#yxh?!su6543t"#U7$>v1-OwI$z%"1lZ#08D7oiWEKsCJkY*D6xwOq-c)y2U4H*;]SAux{p3Llr_/Wp+Y],0mp$K82X=-yhLH%(d]+(;Ak8Iw""@e**t(Kb@126g=ZVAo
n+gA%>HVMSUrKAU++q&osbp}_aop]@nf+iy.33UQ1)B.EFM9dOoF_Y77M6x<Z9-r3V55XXKw.-&;A8L"9_LXS:+Yaer~j6D4MoLtgpnZtiHxz$u$Js3EwDOE1Th7dAy22d=lMrS6hP[y_>i
!t!x#P]Z`RGC&8`R<:3n8gKw$Y_(tF[|SkY`[L<N$&4RI:o!nMtFBh9-YvwsInN405%w8T,!9U8S)-5ao.,`ibx,.",`H7`o;v<NfJ.@2Vbb&D*_WgNBZ55eVr1
u%Xdq&DOsw@DkSvDo~)uUX^>``@
q=y8(REL&$GxjS3o)bCp_v&L=1rDY>f]
]oz:@mWNe=1Wl4L
mkJMXtNy=L+Yk<l]Zb-DcmUZ4JtMqNe$~WxPR1yR8Z^8>?2qW#@OX[0i0pm/>,$#e6{(micPK
]rZhZXJNCMa$`HRQe+%+=xLy[bA@#Y(@Pchnnnn42xI5!qS(PDbxRj(kmI%
>@"b~-kr&D*jVwKxq;.6HhmUSfiQXbD+~!es_tH4!AF6*MyuW9a;@1R_;cZwfro9.3jNq,Co@T}8!nyv}N&';break;case'el':$d='$h_LUaLp=@90m8$%8rmAPK4;4<T"=Ix"98aL3b,i!bp]YPpHi@A>(Q#!VU?fnGA@u4sPL6LC-V$PNVC-0:)_Zc]7IIsTkyUJ3ls6rbTj<k=%(__hMls7Mnuy#t0c~fAXkqm@[$f0/#x3`5ps~;`myRB,|ScO0`)MTDU`f5NiU!cG.MN<.V?w|6,hDa2#dk3b7J%_f^}s$(K525II>ws!K3)x-SRc~lptng@MRc~w!8~7BcNvkcXu:iHnOK{VkbrWJ5|.^GKE$Ox#+MZf}_Q:y
j=KyXy}i:&z(cg8]PDXyk#8Cq;uo<Y*JVC8eBsYx_V1H$!O7U`u7Nltq$=4,Q`$o"kC;_WHcQm_+|Xor%SRo"bRaP;8D03=>a3{t_lcMy>
t&Ucq[txV.Z=]iHX^Ao]xBA>&1Bm_
smc$X+<,UYL=5inun|luK/;ZP$`j2-yv<zahvZaUt-s-s4IMqkj=JM"1ioc4Bi21i]t>AdJ9?Z-KL37P%AR$DB8TcILft1K
Z~ett5=^:YS%Z0firW+I]b1*wdj.H|7@MU_jgs68Tcd{$xBvtw<mnR6W"=KP+YJ000F2G?FT(dU]T7qpa[;xxG#NhZjunG
rt@?3h_r2>(v/hnrsNxdnkz$iEbXrxrhkx`_Q.4S4^k_B_Grw_NjTf2[(=2Ap6&D"7Uo_K.10Z,[|CP<DjfTdgp/"J~[cR{rJ1A&VA`^T5[NlkO42)=f[URQd4eWYMIXb7v;g[Cj}wACQsxsFn!suD@bC!
^}SL^w.12aw2sOw=.TPb>[&d,KDi7n;gp[rGXr8XQci.!V6YfON?TFH}dvKrP%=DL&l3PmpJ%2F_[W_7UkmL^jn6uF&`1IsS7?W_K)^/aC_]?t_~+&>N7rjEKUS#1NG2:fw=uwwEDOL22-:_@nYALBT2e-UufbaS&H:/]39%5(.}(@Os24<ry(17hsHu<5m#PEF99~!jKUQjl0u(27WKqhD$7`y.QeuZIhaf@6kYv|5Cg4["R_/1`O$12tY""$?AJeZ&[*93TEVVf`MZ<49!_#12[d:j)^b(L[wJD%n*7FPe5@/(
Mxk4&)LQp$B*[ce3#^z=L.YYWQz+xAe`r]Hy?
BIbI)P}/X1V,6=_`[O^71OUL/+1hdmk$BpCl9bT6u9mb
NK`hAVMfa
surun*#R8:O?q,JwC%v/Dmx"84o(wpfoM`EiARH>7PW58P/vBH=06P,wLAa.#I2GqK>,H?R%y;KOD5F!3~5cs&0B;z+0[EY74g77u@KROFJe(8C`vji<ptLh^dh=/w5ay1>]Dm.
O4p~g2Ep*.``t.g(lDA[
V,]@N8wP(N4jsNO7*FZWs;21O"@_{9PW;K%`T1iylPg"}..Qu_O()1qd](/"2-WN]9QhqSt;DB+A}U)r1bq9NoxtF(cav-6KC:[9J6hWxv%l;$z94s%>JjHrN+
E`
K2I"J^68qPZ(Q>|Ot*8,sh>[7V
[+k$$lmvM=4yV<3Q<p7mCDrJP2"6ezQ5VHgi1oW)(z&2,WwbUP,^fl8AX}e<g_`T9)7eZCp..ZAY?;RhqZgs#/C`pt=[6%CPDz-;Ey$i,egw]20^7[BA4J_`D5Z2pgH_mA](
;<zt3@mwg`@5NaUu:0(nemSlS]kj)HV<+CuC8.H_]>1"imN+F-w`Bl4=q
/X|MJ2oR*P.nQ?7h<9LL}
)b1cd3U8
7};rHJ^&/l5SO_Zh[p=`
k::])Im*,?u*;Miu~ixoZLZO)LINY2[p7O"1RpL.30|B$4mWL$~]),RZksVn84%8Oo_LIhI7::QwN&-r[<-:;0W2_#sY"/iW;e/^t?ZiiKp_A8;KE[[uYt1LJume:
)eCPT@y5[7tn#MEb(byv9odPapIg__t^q8"*),xl>y[/];
cIc9".p^^/
LYAI618g2WrI
dfJE<(-TQ~G#
"nILtqPNK!rX7"Dedlr+;@I*ZICvdQ0m|$_GSVn]O33WL%_wR?%5=s+2k5ukx
%TrqsJWhVN8sFHipZeC;{b#ctUTC9#:2b=bO#E#[w6w!/m_PF.~agYDZDGkdOIkxq7b(Oe$U.s!,fl
%.L#an5?0bxK
b/kd$]b.~`@(XeckWm`1%.:h1*GRFx~3m$a5{s3F<yO]-=?0_V7Lct%Bo-&@F+nMy!Qj[(zuIiy^BU55|w>P_H#=xY;GO3<R&Uc#9`pq5.QMDZ?Nf7A:?#FqSTQLBicNc"^;J,Xb7X)&4Z+gh:n1y&AjPS)fj:J85-zk^QPoNU{-(#sYKJ|",TJn?=bDR,I<59?*s,i[p
Td0jXVv?S<meEeqhDXGVC>3(F<%7yqGgwt2YtxYuKASt:VP!6PT;2wPQjn_I{<W`A116l&YpgA,DkNWs0`gD6odAu"k>O7]"d`sGNUDDcWvS!-2OmJ$WUhy4Ke^0X3&B+l%Bn9V:m3aD]1WS8h:npysZt8Dt{dG4I&!gs7-?cB)@UVU+7nNvju,V
.g?<NO!CK]4o/fnSS=W:")!V
q_~E+N}
r2{g7WpV^)]paRWB7E))s3DF{M6
|Y*(
+yjU(oRJ:>1l+4/PVPZ*Z%!C*7^k?xTZcuY$ldVb7]kg"P<
@[v/sx62GzT!AXEJe"pSwDd(?po/(}$%s7"i
7(I(&k#O@]),?;RQl"7JJA=#Dju_g&-3]<s@CpHne9ak+i.q540"LTxd&Ze<8CcGHP-5Um/DA,xeN:2KiTK=IFXCLG_tTW>OrO_k[Lp^a?_2;!fkW$bpL9@"!)`ONF!-:PP#v%|&69`>u*f%9<:<EdM0o[nE^3|.,$)cZ[Je0uS#(:Q#)?]06i86Of2MZF)k4S2r-(>F42}G2a1]2hr>Vqm$ZBK-giEt[N}R=Y}h7j(rOcG"_F{!&sY*}0sOc)np]hf<%B~Z~EJm9"F5<Z^UYPARM`EF^7+DW.v2x
|tXWw,;JlA9N:,^V(5FGbeVs>v3TJ="L)NCx7:sx}4*Foomg-+`O14#p0y=yt<qjNXeBfU3/b@|gQ;M*EF<+h0)99s9&`vhgr:+d97s4vkw@?5eFUI9@K8YeIE7D.YNQER[!#TwhoE4[=p]/fF;OlvAYub"V:e_
m:<8P:@kbp.#3woozcMKs/5iq4bb9J5:l<%pI-b+DudEca/Yy)4>C$a"(Ok]G6-Elc;dF[0Z*o<h}TZ%N`edp?qOK%&V=sxAKd4dYM^_>i+"`d?dE,)LTmTe&<1(+-Hy_$;quN;^oxz3:8MM97y.4V?W1"XhLQzFy`*`p$4O?:cKZ:_Q5R(RpdXb_Ma-ha5?S0J"MOF?g[tH!$hxiBfVfBo]A,6I#<G8"$7HKMS63t3#?1SYuFkiOs|ENGePAbda$h&fJLt@N0P`=hV@{]n)E
ZKfg^rn?4!)"~/xDt#R`O+IGYb;W6N<I1@L&ZJ>#OOI+W4WUnDqyNh{vY[j&1;HUAkRJ&,6D~i6(#^c-vWL6_a@XGNP:QA6V1;*Q
WJg%`f=H,pZ%aI%NuPQugh[g3sDr-hTMJ&Pob|+Qk0FcP_(X0[S5HpA+>xs5lQ3^hb9Qj/ma$xU?JCM?-1ys
B8BF+@z(43;AWOV8zqkdBO}>Zgx-oxE?Bkk7Y^R`J376eknIFv6q>o8,
:ZRKQd_f1Uj0411x2.c%of4ibWHN36eFBK@0P*x5O-$)q!Nw@A).`J0=s3I>F+lrCrVxeF>XE~PG2jXn"w`
ss5[YLGLfKv[s@;8F_/1ETG"TS;[4b_2&c0H1iyj:!I`KbtL:P83^VgjF<GZjnR/R7GEs7wHL91b9XQXr;IJMQiHVM(F^cm@crnGCEt%P~0B#sU3ZL!RsdagGL.%fC>4=T4wdX0[`E8+F"vv7o:y,W0hN9rF`kQh?s>^]}oT"!L.IBW8nthQ=]A.h)Rtj-x}3[.x-Tdlb;f}cKN4ST0n;U=W[%YVIn>6>1gu+1kPEAm]-$MgT".LsM]=`!H*;=<uxbhkKr"V;_R&h}+O:1Cgq{/:iJ)+CNDBX1fa$X]j2
O)FLS}u&sKIwTWQJf1:GT
3!"
jg,a#-4C&;7,AhbJ&DxlsG_80US,nJ:%9o:+BH48J#szq_7yq?]5
k>ky
eFDO!k$DuP/4qdwD_.6QJfrWxvY
5dCmhW5>IP8-_0yo9|_cwdDkP<br,aunP8n;FSM7Z!U$!r%NEFE#?zDr9.jaJMOcczE
YY+6SwO?o:>0YHA("-UU.}Y0$d;$+fWp8yc=M-k_Gd_Xq@Ea@.Qavpe:VbZ>kIj1/<-HkZvb*T>1)P"=_C+<e#I1[_Pjj-hd"=-DaB+1Z8SL3Q%}AgaGJy;D<C#,n0Uk]6CJ?MV1g9)|eXU/p^iE<fjjFTB]MUA">Z;a$9kpupO=NJel/lrO#2(#?[@cfg]PZy#%YZ&wOmLWE^`2;]]-jXqj(*I+EUiz@Y#x,@g_
9_N$VI6Cm,o7t?
cW9u1@Ef#fi(9w,urtON!GkA[N4qq@r}6t:6If%wRX`hn%e80B4*Ue18J.]I@!0})HLBd8Cz/=NL,6KNT_0
jO5>G+BZvE!&P!$-xr_y!rAJlDP%#gVL5Z^@wXu$H=bVd.ZqOY;XLt,|-2i(3O+m2;!_6(q[I<L*Sr6kB-P]>mL/3HhQ=o?FK$%;0{@zggIb58p~U@Gi0!3E*5t"<uR<`0k.[Za:@;TaX{jmh8LW/8Ekjo
Kpay7_#szW!r<(GhX4ZW^WK*^/iiGq<.kK
pBk98)eB-6Uc(%%8l>].A,:MI@E2i)KNZ8$g<0mWq)TN`WxziiD.Asp+B{klgg:`<M<1T]6so{J,b8
5bl%jujt:
%[+
u[c1g9GvW#lj-fLUz7;D<J-m*m%<j?_-Mh"T0jQvEr(Gf@ZuTEXZc5m.:lqZl`*%o(";k@Zhj(Zn1*H+Fcrx*wxH{_wEh?S.p:"_0sbphQ/0eV`ixGT9wvTCwo~E$_xposlW/]qEE`R+rloLG<ii|.z<PlejulT$ljfZ#RmZ$.OQo6(H{)g36qYG!_n.FC*yg>R@(=qk,0o3ypBvBf:o?vyO`WyVu:):}O3"1n@I{Z
<d)VC|b
@hEYm%sI[fkVmh_cA/E]w0%rmP1%PM<4hd:HB9t7R=FTfhtS@SySpg&-l"/_`|_-.f,oDjuR>7Ik[gZ(Vs:oo(AT"lSSKZ1fM^3N/RU-0d9hgz!?>7uLRY
Q"}iVZdo%TQGQN;x1[)onB[@)1H%$>cC)V5n{sIUwg4D2lcZ~b7shQou43Gq")"do;wZWoV,1Bn[]lktbH&!(q
^-b7U6(y=S/+PCyLq@-DI,/2j80CHPVlx,wZp*VN(J@9IFey;RiSz%+yAlUmE`e9N[M2_ICmfbn1BfNjI{>EPE?,(v[RwwI;?0RKE"7F*Npw+?tktb2HR1?-5xW.9i#m;4@B`Fm[%^I+rjtK67;5U`>Y%XSu)myMq(xu`Y37JA3TpzS@.*6}
e24Uz0}Z10^t1Wz9>K&w/>T$2^TgzZLa30S(:rz?#NP!O$hlGV![5y/^p215IK6j0K"$Z-xM`)|B{DI]/LzvAWcI,+YX*kGPMsK6+kW1|qxg?!(pYsrV^rLXh_<35!4bBgB&y
O!WMSY6>Gs9+5k0J]Q4K+(`+sets&YBbR[<xz>}_+&])Al~T,kd&|w
sr2i^JYZ!}f)BMSPr4+gk!B/-Lt}+gmRN_6t
&xuQq!uLzARN%EG';break;case'en':$d='+X/+JaMAp,z0
Y+"&I/g5m.fI,3%v3SjRAY<XSXdB=b??hR,(W^O`Dah[mx^O2qSz5e
%m^f:C6Dt5>&aKB69V(!du96/vM_*
<%SMQF=N<O$lz70RynoNp6._7`CFqcYK1jGxNF(Rg5%/KKgvic"&W=+qRf>jg,^bX]l_OiwUq/N#B=][3f!EYYh[MZwvPr,yu9,JDT
H+1A
mjf`r1VUXOQJTc/A
Z$#{MfHRr$
ZB1]~xayv?hEQ*|na*ayTP(MnaZMtidxPv_y^59`[jU>Blyq@aO@Y69OEdFyi;sP%4;GG<dF)n1,C3RZ$w0F541@y*4P$mUbh/`mU^2:=7D#
1!W{1VbeXn+X#N@xuDVXbJsp:!__^-IC9(@RKr==1Dbk?j[/8UCq2gpW=%gf)?7}8r7yheU4@X3j8_mZ6C9nAzfl:u&#OO&cGNR[C&UugGV|1V(}(pk)n^oq)O_iob[/xgQlZ(p>1DS3&oRG+tjZpEEEk^bnY4f?f:+?
*l&LR9AD!@V+5LgKs+<0o.%HQwboH#F.BgHl*gK"}Bnh-3U/xkg60#:qYg5;DcRKHqkMA?hk*Pq7Y>vH32b.qZgQZpy1j7DtoBCj^G^6
y|t_"FDyck5J
n9{lQ)tspby^s34dNO,o%v)Ov,vi|Bg?`S>NyxVnXq]xPC)NU([GG``0C5x+d].PSVbJ|c;fbM">;^Zfl!XlSxPk@_[)kG"h*Wiajh+,geuZ(uPD|3DU4<6?<iH]VuhKRN^?/idl-E7WFKv6n>uFpDn#pHM$kH-9]x|E&*5a{
.H@B@FFVF-`/<k@qm^GWO2a:^r;X!OLqt+I46r~+;lXZ}t9w.Oet&XrLlic=/fWR)&GDXcvLLc*cj($X-a.STIrH,uq?fJ5xxCl+rV"V$O{9_E0d|]0H,>,a7A!G2/59B?mFBIsC!%d`eExDg)L"g7]_[`#1i"dXl.lR50<qT83GMx~)p0VrLvJPM[BZbf(bL^5B5TsG~[<i.uliDYKBLHdty%LkbyhB`6{=nNz`V?ZjkT#]Dc/r{kLf{Gor^xPQhZF8!]rY?o:m}sMqH>v:TVu<h2nrw17[eq3LQ6}G[O}?LbpS&>mZ!p:Y[CQ]7+.DzGrZOV=Z01QUj5hW1Bl+J3.qGWV>]xcw@:gw;p?z%uFl?wvp#Q>-H,aqo,Kih8/q6HFH/.f<LLv"*=ZdWHl-]ynq0b$]?HI>|lRV2]Z5<AG3Y&YIX!@sUeel@91-PI-gR*J3vpm_#@$fAiNXsy}G)iZ*RAI&B<YM+I??*1O+jWS1hNA$pS]-0`pnP$E$Z>"sk8
<zm#fyZdvQc?:hCJ9.#By7<sc[-9d!2ftFmuEzd<%C-A5p,e-Kler+/4(+0HOjaGMs/ha9)DNQXTw9eXqKbhNK)X?;jg<f<(hBN%OgZxVO8D[)f.+kdeU0.Vc:N4JVd8;4BQUcJvhC5maI(5s)Z?;AwfldGM26HCW]TRKU21<9=8.&4<JF8~^J;6A1/1fNk6/PVQrXkyx>:}a)a51M@vJ#cWLl?G>zBSCn=|D]GqQ^?+x"e*2s5^qO[n3fUovGgo>Tvt!&J9Q3%u_oA318Y.n/9x=`/dgDCO]
I#I|M37AN?qf$q[-s
8(,k.a;@DzWVcWMp23EEVB^r$"*Re%rTAwDy+NHD(~9x6
%#M/*%izi}h03+-+W|X"kht?*gC<770Vl=vWef-ccW/^G-J}^=4kj!GgCec$PMVwsR7tV?%*)_5d:C/lU_d
X?1B$A
V3_D":E0nYk]S<o?9Ar*;9Lr(Nl!|Dyf&`%JovXcw@Qv"ho3#]1.Ih6C#yq>VeP
WeWp"/IlV.$mH[("1hC>g-Cwm@fuXZ*rpF
E>0u"r"a*}Oe6=4T;
^FDf,Joxb+$a8:K=YqDD)#k$#Lf;n*4L%bh2azMS:mPvg>&<HPXs*P9UKj#xFf!jP%9K28-.;=0s!ZKk@]#
!U.3EufMZ^xWP`$1u%3>L*o1:ID<Z`A4s`hjL2
c.mE[7?tNZ&,.Qog>_Z+#d~D(.G68peQ`%%@gx8mGYaZ~,>On0/6uC5HT.9o,lU*ZZp2sF(njElVeNMbPb4X[Qv;]yzc88==W2rxlk)+b=8:&V8YF,RJ_N`!j*pd}yFMX[8Wd#..$9Wte=":1m+e!FC>q_4x@3K;eQ6]5M+JGwy
Yd@w93[DdXeHFKUE&d!F=uMD
0,xN9e$mMNnV[_2;h?#V<kB~8mHh#29<31dKJAE16ZhPko<Dvol<p?7UKf1)m@ftKwsgU.wo>
<clqqtSeiJf>]W38!3f6T%q6R>:{6]dc#1/[%{+eu@8q$3P$/Zwo4Ln8GS0{8]/"F_jI4C[1NqU)E:&ir8YGk
un6}T6<P&@g(f#>rF5GWA/8:X.D}A^V[$=%(boP>]rr?Wx9C2B`~vOiith=<ng<L/x5dL]1t/JDH)$V8C@Y%7YcB(sIM:KqG8c/|&owmXf%9CL5lJ^6Eo8)G@rb.pnm
]#%8KeST0P0)LH-/0FRYji07:^uK&8TFDe<pk%-0k!ThJ1&
K[jT9pX;q*99j|]av^Wy,!nxap2m?ROz:RlYHfR6tvB.%GiCacua^le>:Ae6BO7sQL9iB6crbb9J"lcIJ7-~31rL,p.<$L?;Q/f+S^n[v+ctXxB~7G2#j$F4a2FXKhCATgJ4&e8r
_wy!g2?Y,iE;A^Dk1c?1qao!9LJww#k^MDMi3DMw+kUyzsIw9i&GWy5C;AEao@i3k63u]X6>s#]?3K4/0];/VhzJM0X4MXQTD2:k*j}Z6a?&]bGfj/Qy_)t/G7W&8tb1l60RD1Y1JN-Y;WJm0Yf0?>"Aobe>o0xDvC|v|0"Thw_"#%P&qnCaTbAp/;Nq,C~
*@TU0X^T!Nw=a6/QY,tq!>oqhXE<_%
arVj.l;C7t>7xU&Uo/d;R`RN$fGxTc"qsNBwyPRE6.jX#z]D4C!hgka]rV%Ji,m.de,r
L$<lu3v<Dneu61D2GW@!n_XfBq6LQL$x`q|v#wj`?iE<yxIw<2]54R^8W%;
=ZtYWah
IVJWfV6-E
HbqTv03%H#pOX8rb_Bnl_`W;@I0UDulb=,xa7r(Wx3ZmE]Qa{.C7;3d]6tl*4F]M;sn)Wq?grsT4uk35D1].sg*0[+>cu!Q';break;case'es':$d='"`G@qaMAp*60
Y,N/p%+)w@p2CTDc1!G)GY_RvbA/L54|`rnYWoXb3GP<1w&*ONt
lXDB%ixu-dq8XPUow#crbl!UPpaz$tAC*+KS]*UYYE!9oC5BP^
7JlVw>MWJR7_%spb)$)d
P5d#SG
61$iIqH.aHKIW$h`];zDCH9D
i>QD^GT}5-]2t1AKG{hHnR86B|U/3byd?^ea%@ulprg>vRnJU02@Q%;ys|!OtUgYb3O}-kCb<zryfSL{mMmGRti5W*FrEH<i8X
&J)yg#CJ.k8jUczWRH^Fk^KF*AfV/qlx"6l9)n>BfRa^;usauril(h2Pt3mrh
pc
niiHifd=vq,]A)I[wwmMDAKn1dV(i}=0Qm17)PO[I|v5g{p"maJvJDm7U>*$BiaVaoa5P*1-TD^Q![qVo{rdK9_AFOT)SJai:/_tonAE`h=
2<OXe.]EATeD$Ms*ADYlZmp&y6eWSOVE/(IyOZ:f]Uuo^q%?,+^H;BM8%/a|H{"Y$`*MK5_RF<;{DUM07]eb)VSYRk`2V+gQ62G~
M6"AhBn]DZ@R_JmvXfokIGdcow06sT_&YpmJn_k2xnXsqVTk!WR&JCP9g%i!5fd]u:"+bXHVwUPSiWW5Ot=UtajLXd#*DIJ$u0yQ`^FH#[,e_ar5f(l$t3oB_iLD{mV4l_/F&,.f!D&p3rJmita
"bE@)F@PWGnao@cF}qsJ%m*Az/RIT^t%p1`v/kNK*h+SQ)&MgqNRZuT7_6;$6,t/

nR7Z1m
HH/-cWQ|6$"Pnq?$m%`YkAaMZbXNa_X8U(wAB[@SKojsP:7JMfEN1x02F9<&);`$]4gIvm$c
|pe6D7/B{$_!H_@o:nCoub
rk>e7BPPopnblh>/S;w>u?*0i*](/1o"x#7a[*_Oyc,Ck8WZ?uIn%bJX(k-_&Eh0_mH]4phK.6,?E]Om;qWc03Iv`<^K5D3D?Fix3iQTGn
!VR*#8A4gd<B95)ZsvlR[1c&
peFHS,[Xja,O-}.7mHlpnPpcl+5/$-qYK2pfGk$TteZg9F<OSSvpFQx`1QoEAai,$WjaVLULFO^0BPCA`Ev6(qPERK:bb4V$^o=Y=(@-3>.v$m[g!"U~1g,-0?
M?,M~c<`5-VEz(rlohW((<Ed5GZM(o2&1W<I!#-MhZNl*V25)vt@o:6:JE*GlynT`-1p7f~ecCgohyeGbb|E6GdAs>+lpbTvKVKcf[cw?Ut^?g%^EBw/.c!XE>$m<sST0`zwmGl9j$%%+-~#udOya>$-ge8x"Q"6oATR}E*C/>7CjtQ7a5#?
SoU4(*T99gUqD1@[,N;.:w,
Ecg|&%C{Vr:`a?x33=O7Lg)*L`Of;>u2)pwclJs?KOO}mglW*+F!Aod7k4X-MU@leUjt#d3f#DM{bwY[@_D:Y-+%JvgUVeIT[eBI+3^hf7nY]2%>D:y"p>MQniV#tQnD93r$sDXws+[0
]wxb7WV-1SoC~htd2q_B6uQ26cbpC)Q&GKkmbK$IR6W7ts>*/Fu"l"yJo(tO>X6y_&7x|PHwgsvYr1`f{b*R|43+N0BllZXwv"WuH%?CWN(ISlucv]NeQKbw[128(.)OC4oP:+]i[
t@PB._Upw)Qy2%f/7p0ATyTo5abVm0mA~:Qr6R6D3+gdekw"K#b]@"[9)VGMEBH+-[2pE2"lKTl,.Dy+Tf/UOUSjj#=1E&L("-sN%xS=(H;ld!nQ}:QG/lN0.
ufL({8
B|35MV.2V
Ee-V$B"q)$QP1{5r&J<9
Slj[F2M8~G=35-sJSbOmt8FsVGndK-|^~5.jOQ(s&i^Uz-GvCDR$~*)KW5NCiB6i`V+,oyo1LxZ/]Q+%>60#IS,y-)8p`v74lEZ".J,xrt]^t.AjOW3uF?q4l@WNK<"Whh7#!6Ak+-350nznQxYx<Wdi-eS$_m,)m>(ywZDtd_UUsaq:.xl[b&QIW[$Z<#=RNca@C3MGX
Q2t<WUb-^4dwr`UCHY2SR$,_OJ~IZt;6{iHb5i),QI@>&LTFWij5eZMy>7
pviP;~vVWWNDE$(Co*(5ZDw-MKcv]n-{@i]B*2,:a-<I<_!@NbYC;>Q]f@F_Dxb
35_q
&hUQdp@ic:,5MgPscaNW=xIq|c+S]QnbNFF1pi[7N:#HKCW9VbC>PQ7j~L88y)-n%VbB-)bMGkZ-t^AaRxr52"CM`+#+bsEXV`Rgr7yiMfE1CZqH[iA8a&6d?DSG9Jzdo.@%9VJiVE"e>,;[m:9o`52$x9tYiRR-z[e/3puv{PlCPnl,
VJlMUF4e
!`C%r;MTNB1iiDZg#<xqHyF(`[`F,<T4}UaglD%^,G
.$@_HG&WR;IBw-1}Xq?_H5"$^kwAbY;~8C%)]H#WP:VcfLR5N{bh,rm~*i3`S^Fc5|"J-OeRBvv)V;bCXfxejG-E?be8AdSGX^y*,6+]
euJ"/%KV*njW=6R
1:}6Y0lvEL{jvv;4zN1^Qew7T2B^
UJA>-+]Z%KOnmte,YSYM9GS(Y&M@UtT^sT7#G&%GH`b[_!V2!c2.9&[}rhY9:O2&H.e/Q",p&*Y(aX10h~iT]CoY^A6sg}BH/6Cc]qfo&s^Wx-/6n1j_#BFf?}P)s3Ihj7R/oDx~r
:eNm:>A&W$ds@`<?d%6OUWP~RyB)&.$DN2.bOnt,PbGPTKD^!WGj!@EuU,FRfW]%=FVS_]l:!n2hy-CMUYbSW>QWG[4L3^&Y(PLJ!aCY/QX?+V$sO2xH9G8^_,210NtC
v(J,d0EHPHyCA;]5)Ha%)*L<7ApGD/56BFy0Lj6F2YlRh"#,60YYOr03au@WAL(L
$RCdS&#)0aj_=r
a6eL8vTc;Jp;S4%gSlt3KjS5%dF>3>5a%;:8v+dEC,6
:Exid?~-P*9sNbh0
258
.1w+FJig^rgm6Ie:=%(r6C35s,g}ST
(uFjM+2"]#nU7n&"aYsgCaHZ@_e)l:m"PNRGb;xSyN;rHWs)%o;TCdzSW,7aKpsq%o?sn4)QJ_Ya+/g+Rlv9;1dFGqI0>6_L.M<l(D[X@sb12!d(%cY)9#SU3BlOj%5sY3ilEvFmRC#d),y+lcF.iE~P~/7=t$&v@oe.]"<n6oC=,U?2:^a/K7d6[
x)cbK-YuKD27?dm@;sP_#PTp>[o9c/UnBuL[G[y7Qb=2eavS@;go9QpF0o?OEeqmi#-K1^CZ-]/6_
OW"?[V@5:-JV![b&46wBU0o>DJ46&!^AseNZMZk-k`kgm_C_E9B<@#M4dmyH)JT$v;E3/!*`@WzSRgY&S[2sb=mZ=lzqtb1q~?QvRncmcC3r$_#8YFN"v,I@+fZI^.t6vCNqz`4EUgnO<ji;#3CIPizXT^34^SAa:LJb
h9<#r5RAKH!&=fg+rI-H("mxG4:oGQ@BgGTL9o9Du=M~vB_[Ilgg/-VZQ9CVOSvcp7vktD@:9w1mkb@Z&hs,mhc8&`Ykh%kG*Mf7QX%b#vW$G=V:v.,qF5=ht:bZjIil51/y-l<c!L`y3vKd>9+|=#IP<$*;Zl*5DeS0(eH<V|:|+A7TbdDOJV)2r[xpZIyU2VZbA|&@@WL3D`H@Rm+6sn7uUz`tdVFc>t9G_qq5xikq_dDIn&66!J",Ea!L"Yk1fS;&+9%go^CPU@!+AF1gB`"GUp+R!6DlMZ(~)pD~ns:YtwE@q,v1;Y_Mi3T.av._XAcE</t2x}RHqWIP`QH?!gqoM[C2;[e[;}tq$3={5z"O><HXlWO((=V)ncPq+qK^lQ9gU!--;Ga-f&)9qo"JsIqv8KhaX%^}A.wZ+!<CSKR*)@t,b3Kol!&r1s@+0~R-&tc9
5^Y_1C
;X$TH|1l=<tk6r06yx$a<`ytK8;w+V`yXzn3e/,ZVFE~HVl2c5K9(/J)*
b#Zk`SRf<%+!-0X:TA1.;C`vCO2u6A,<V[)u&fyDws,iBa2x+:i~tGRmSM--3!/3q^$zS
-oQJE^5ZJsK&uzqK(qZq&21{SJ3y0ewx?J_fC
GKF+nNd|U
OSH(e
3%(/p`)iv$-%TX8Dx"V`).w,b7Jju^0@7J/`;R19%>&Fq=()6dd?K:!
a2D
nvTV#|@Au&o!v^/<:dZoyTKOC
C.(<,bB#>Ig`izdA+1QT[rvz)HUoCj-C`"lUuZbgKLC$a*Orje_ys}E|GoX,<UUANQ6U
kric1m#x"4-$c=p#F:}IUciqRt(
a&-AZ?ZOj20aF!*d
c=`D

Kec>3u$TYB0k1Ai27$aY4j?isBR1rFo[L{6?w(JNd*!HRXKY_NkfDjVo<HPde*M}4bdpI|e`bxhl[uJBS?2PxJ]tRHG("4_FIz[NV/s0lQU.R52FD0B2Pe`Q%4:w_>)&8"!Rt**0(}p}$$6k$Ex@3bRZuHbVX@jiJLczADxD%FTf<!:jm24)b.1O+wE<A")yqmj2K)-QWJ=ww|/F`1iLv}S@)68n*lE?xkGcsg/chQ[IoRL#!GFI:U%wKHQ(ODM&sB%khN_vdo/%*V].2EmrdCSjb./[H+spVod[e&`T@^8=fO>+Hdd$35nf"MA6nbEu*c`3=X_l5*KG1TYSP(u6wC';break;case'et':$d='$R]ALaMAp,z0
Y!"O-~/5vY_42imN^9^zmT9[pSkTnG;G:7?T[Mx[^ndJ#ExlX}$L!POdT*foxw2;6.[Fhn7+gQ"6wgl]UHQ=qQX[ndtEriL_?nG>XIJ6B_6I]T?M,+HAfEmCtFJW5P,y<AWf_NK{V/lX<=+rm.!7A9wjD:Zx6F`VrcD;bh_y?$uXdU`)F_`qf8R
iU`A?tl6af1q)mm=tzpxaNKU?ofCLJH/tLwAW7D_<i.`]f`eqWcq^(PiFx`gg`Y"SOtE0~s4L7l%vWE(MbBO&!cwZkMMF
7vg7UBjM@9/?[j^!BUqjM-C"+v:/RzSgOD+W5C[Lc?#s;W9)D0r#o=V]a>(*XLU#r24bsS]@YW.&3#vYWr^qWOm6.=mI-rm/kMl7rhHjbhU:N
M&&[s`X5
qk6X^lm^g)NgtIBfU7jH/C/xpmgxLkEm=EIYS66a]xQGJ#HxFdau03mVF4Vg,)0vV,L]+_/[+Da/?j*u3?=TSF:c<m)Zw1,Z"4&QD*dZ8H`ReQcp~XbRKb-r$`n5K7>>Jkj[>bS:;Pwy>dUp`]ymI2Lc[I2Ay)GkW/G!VHq,Hjq73fCBx>lqOZ@<m^8KCaJ/=)MS2b;TjsO@)%aavdM1daZbHCCpp#.5^K1Z$5VD=;#<}&n.`fMsg0eb4>jNVrDmz`GB%&,Ks[%^QkTS8@*chAE@Nd,HN&{sEY&BC5Y:zA>#=bz;xy{y;a;W`P9>xmwQ$(<EM-*fdtIK~
!bC?6UGX=[B6?*;mO+T<BGZyNLZIDl}>-?n5IcL[cpQxcd24Wakafo!ME36fwAQ[CTJgB?e4Dhl_x7HEWXqfzi=/cby5+tw]ZJa;A<+mW:xGhDZMlM>w&Kr"6+PD!%OmZM:Jg=OHEJ1CxXtP}HIEv"wWSA=CI3-VfjP*&O"hX>
ZoYR3a5aE2kJ50&ohAxtEV]h1h8w2nvLoFp`:|Q32:L[:JOvcH(E@2lmTeZNqr[_&KSYK_^SW=^QIM@{Q$.MF{(7qiD:fF(UBbIR!<5c7H.
&FP!GSgR]
KNsQC<Z#={@=[41J#]BO;ZIj`z3W)4m^G5hX.1qJ@vYOu>6zmgly)"E&E{@+TespTCcI0hFgmnA{GRQc$c]@[(dB_t#tfMb|AqYLKl]FB_G.JeIf*6K,a[LsX6:q6S%HKEG!y}@jxf0=C|mS@F`>`Ve~TmRlMfOSJ>FaK(sM/MiQR0U;v;:_Hb;3GcAx2uA7HPsxFdO]UL=1K8Gd1/Xf*gmW5U,$"[[)
i0&*]:"6RTQiw3_)BTFr23PS1)ikRruH?Bn?0Mxf3nho=["BON%sA`i3hZL<Ab<UFF?(t00ZA3uCt])Dov$#ift,/2B$+i#s9]</)XC=V.Zhs:=2MWnZaWHGBoX5hRGWkY?Eo*_V.vcW545bAa*ex0P:Z]#JX)y`b1ypxhsI+j`fN]thvE8`p7xroBtXup}313!^XKdEOq7d-*TPebID11%0,p9BnbCQ[(kSInH]FXIEWsGg9hI)/xgy:gay"<uGhK4.I+&ei^EvR.tLSm|U_AV62KGN`[YF5vEfCI^lX]XlP68t>hm9aZt*"n=A_/lkIv]u*#x
n5[
ou[NL."_OKS[eA%7~07"gq*b(ve(!Br45Banz!!<SInn$6{x+`s:zc`/cu:*[Q.R6ms-BW)Z1*r1(xhYeY9o2lR/M[75>^Mw_TgZ;Xrg?N/2[QvDJ_IZ$=lfuv`5[V|_mZ#6D
;$;;SH}lEc~F|)@Yt(e>)9Gm%qg9WN-fH!Wt89R`7&kPR_FtMadwzL,(~dj#DyvXclXp"-.OMf]#9$lAYC)BJtgWDU`@@O9j+P$AWe=<p0[f-!qHQN+
n^!s`&@RAY)S$GTx*az3q]rjit2i2)IY$;%Y>;|wsm$V_OYk@H]9@FQLyRnHEH7(K9"B6&mi*C?
mGL$sE9Q)F>!SG(v&Z(5MB>3;a>N]_|%
sl[<[?Rr?jqQ9u
6=%dA:Q$[s8C2M),~[aN6:=p@4,unICqD_z;&.kwoiW^}/3UQYG"%rk9=wHC,A"QluE.JuL$5.*M0@B-]A4tUb&R3F)vFH,!V4H5GTe5COW%z,I=z9.8}KmO-_z+&5*p?72hZI]Q<o>N!PwQ_UK@z[UH`eKoUp"9^t"&`[X%e2p;*dzf96)@IjJ_7
n<N^woYpR1G-_[K4i.qHNU&-jpG9jKT_>viYt^hT=gz&6CD%Am)xj8DQ#7O8:gCrb9)h9KY$n8)VjohT?5l-t^"68H!PJRrNJ9{pLM2KOYG9&-x-->VEdenE6xtbrvO)xODyG31+o!ZKjo|=9+2(EJ|B,peEYp-,/jd!5Lr,12QmY73T9
Lg9=oZ5#mC*]J:}FDsDRo!uJfZj9Z/4unq0A;(aSqjAgw;`,_4bK$tQghauiJF0$.W0$gMSm
+G$0Tu4y)geVsk9V)zs1+bkeWo-EaYYy?i`x(o"UOE;tan"u4D/ton.aBf>%ml?iuv9=;R(;GK6q4v]y2V-tbbk@tVoEBQ-X[0Uxi[2=0?SPmN?|k"5b1HiDwd[m4)LXOu<Z%=DK1N;sQcYzTWX[Uwpo<r1"R!`,!k@4B2n]2Yf[f;T}MI:IFNk_TO"jgBF@0/*>pl#1ri.P"ug#,&s9=tyHK!q(0Xe6$nR2"uw12;n&fBOk.HxWd2`3nVJa^^VH="[T7Q"?"&E9&_FxEj#wboA@of/.Nm)jp8$[pe1;:MG-G
Kyu?*bkv7,f$5+:xtia3vq6(o_IE-63WK@OAb/3E,H=xq]D{fcdK[m[i`yun"_o:t4WQ.XN-a/VNYVDl%LgwDz+#SNIs#u;E$`7B*J)GxNg:f|f86ja:x,*m#N_&Vva%"@=,IM]G?8X>iKLvCtYc6g@)H9.f-d)?62x&dU=#q7)$%@vQ&C65,spGIpv<M5P<-1EQ!lN22c?W[X.X!B6kTl_+
p@NN/h)_pp`lrO3CAW0/fq!c7ps$Jd>=+9/V}Z:%3foN!7U-T*1jzy|c_L4!s/HK.^Kg5-USiT:taQZ8p6&;z<A]Xp/9g(MN2A2J9
_N^U3ptjPZ7mybNEm?Ly6@IP,o3&9W+j-.>#>g;WGdrGIgn5NlSB
f($l[cku?tGn(e!Fym?TQGbt>#[e0&&z5|O>XT20CkF+x_r)sHKoK_#g#|OU=Wm.eK<.6Gblww%J"68J"#(::R=7Id-/D^3a)Wa(ioAaAMZjawySv36bEo-NJpCgl(02g=`t,NWL6&js)h.CS)/ZmY>sjWuJ<QldS%DCvQR`DmOB^We3$asS&`K}6Bc&.ktLRmPr
T]8Z~=rlIi)N=Ri*.us/U#4rRbD#8!&[]MiLQVuc_>#(cLW
+@3$/cG/Ph4Lhm{2K#D(p
#U<_kd+OXuk``iB^V#`p[ce*O^0pfQqCO_]atmh(XblL;yu-+!I,kU}v3ueDMu]p~K
ABe/Eu!goJG(;*Mu#(d7qw8z2V9{069yfsY;EkXsxfA~w!@Zg5+DZ}G3LH7D6O
NXRE4[c9(ILw#"N-~!UR}W%*N`6
joD3Y88^I0&c---^qUdNqCuX+e]R~"_G
r(*gV%H]Y,x!b%M((]j$;^=dXt
D*`!@QH(j<lv?C6+U,HoQ=;?VXDP_Y##Dg*w43BN=b(D7Ll[UEOP
Tzg+kNrz<nIB*et;TLf5.3DW<qd#9!8:ZH<heRn
f&]{
N+49K>}`mbxFIH1<k+&>>LC6`;fo/"/Tz!4c#2yGU,viMs0BeXV$`B{7>+v85EqS!M6(]R&2dL$v.KfiU$:(.SNa@.RI5c{?b0`B?cvalK&S(@yLU/,hQRWb8swDRC^i3Fa7-SRabqu%fJ+A/J"2k7U"]<h:
5qC)3$o}$M.C82V;pmcrRp&t48=D/@DHkYw5?17o[{J)
"fu%Ei5m.yw?}MtU4:k?Zz"7AjHZYLhBu.V2AKUYptr5qR
-8.!&g/W(kWpNll)ICQO(__6(<FYw?b05$i-gD#Lu$F:*+$M450zW~OhOAiESG2*w19pPgi^r8C(.bqJhkUR>"e//uD)-As}-n8gbWmVV76S`IKWLg6
]0:x
_t)@;9/e>0g`KjEjD/oM+Y2R(w`<Y-b<G%3fUh.<unKT0jERo<A^?"$`xm5gN@!>n"rrEu2]=wyEKO(c}(&kQn+.mN;$^_@VGD/#G#7=V)e*YexyBd1(C#t/M2>1v]=(B3Qg(#nd[F/Fi7"$#l{U^KjCZaP;SbrUZW2@=9(47bF`shiaRH0:m!;p=7_:va)Q}t=n{lm
h92%EZ!UtTwM=+Jd7dtTqF<knm(M^Mc(/<9.aWk1:Og4ds{aS##Ic).n=R4V!$6xK6B:Pl/&F+pno5[)3/)+^YSV1Yt3*<T0zi1Xd[?4x3dO>1JBE:4T`R`"Q3)V]8dl
y62-mi
^P2G8C%3((NY;C&RGi.7{>/L#8S`0Vthh79chIVO)Dks*+fTHa!Mz]3I79&N2y,A51jUK:-XSPA#|wZY+k1bKmnCa<,ao>7MItL$6NANgtj)>MB!Qk7f
yCN"';break;case'fa':$d='*c0;C6lCvY#?d>P9PTS+,@%t+2~Nn^b8!-gj[<K#5`4NF.3pZLq(5CZbYCDver)fN*lmWK$p?-;!Pp@v;+Tp_c~nKB,]*=?`,YjX^S*tBqdMRyawxL&spM?3(x6<eBEm=1d?h`Plcm^xs

uR3jRh66W|a|USsUji:DcXW!tUv/h#A1py4hyPsKXzHX<9gvB5FNd(R2<eIhdn]1;6uOufQ^aR?8B1w9p:S3)iaR<i4m116^Ur#7at..G9e7SQT1$UX!7:H~*r-:d6+(#E&m3
0aakF1@cN1bb%7[OQ&ZEiRjol9qkoA.S)9EpY(i1PwaH0dwe64(s1}fzy]]K@0wgHOgMu9N$6BL[5"iVh&<on<w.y:I4MBBUV|^e23O1qKK[ysMB@WxcMr7PX[qXXE+o`jnMl!n{crb";Phu&=$!%AgCtGUi&@Ro73jj<p;MIv0}N>y$h"<H#riL?@v;gp8g^ITG-C#fVpZ=Zk5wZLOl;2WA:Qwd)aahgjKD$O*?8z;@B1dqdsM7M4&o-iA1S9Z/O8/6tf6S>LJY:vRWi]r_01Q1=x&R<%NYjkF,CVtx@^Q^732d.=#fC|!efs#TY3t$Y,,X/UP`.8j:G:rgO,bH^X5;&62i(Z8t7vdr/sU)1XZ~O|5,tY(>#)Eb%`L?%?q(-nVX9nP~CO=u<tn]Gv.{-I4r5=V=<2[3R)6VSet{p&1&q^,;h@OIbQ(5Gzt)Ik7vcC)F:C7=9j9X"aM?uyItKi+2tz>"r|&DD0"[g#IyBZ%Zp{;E<=-TYjh[<.RMS]n1(8ep"^7s0y.H%NB,2^r}Zdev)Nw.4b
OeHnA[ta4@OV`
^";Wab}Jr0Rx-JiORZtj4T/RO#^dSojWgB+em1&#Y7;A9(7a?T$=TscY9rIN4Z@V0av4<KX3xV:@O^.K>4Rb5s`#nd~RQ8w@pwjs]jBuwwHrl3LEe`2_b1aH3bPn>b=o9)9//PwQjcZvX,iod3>s^?x)@=|]0#,WMrtP5H_ohgr#"CM)KBwi:N;eC]&dP"4pR[:Zg=>g_n?U19bd*"hYK2[tL6-$3cSVOdaXw]K&*w#g6G]&<>a.oK"]v>P-cjeXHU{E3=#wjl
txrNj[](=jWQMdWpM0H_8wR2UUp;ZD5=kMY~r(J;=CF&ZcrqeB[UjKK7%:-)k-3.)..d$~-r$/*+7xG-"(`Dr
>H
2#y8u&0mG68@N(^BljQLyQc7J>T..)N_Sm*@JgvA)BkD=Dj4]D"yaLjU7%!3"j,OjTBc^@(D?<69,S"1FTPRS"2)
>Rp}&}]|6SezRN(ao0Ro_c1aR77D
cfqq<=Gj"V
H>!?4;hV8at,BwxNVz_v/2AoKL*W0vy_gs$5pD8juz
=d.9#kJ5oZ$-/
R`Jp}bU:7CTu#"*e=7)AY"L
*!q"3l<Qkhhj4[V=_ZTd"xdruf~:n8rRqc56kZkf*e[duIqM!aKL9Jn=tM3q1+1f?_BXKWv=DXzfJi}yzC.qnxv!Cu6^5L?6NHjx;BI7OxRt(PCT-RrHuieS[wsFK,dKh#x&9W,:gy[b9q;rqXGYE?-2zSucu9u"I6O6{l+OJ^t2yW:$vgA8>;I9q.1%/Nwv@$`.ryW$q:|*bHXG7*uI~3}PR

8sVd#I.4Q
!eK%Hd=
S8L#lX">N)f<rf@ZY`V}_R_rg*GL<sS1sB<>WZEu:<HZ-d0Ud~_Ff1dWV%kD.Jh9^hXtL[sPBn%}u7t,]XGY5;/(Ne%wBohSHPbM)Qn1sfxR@7Iulua$Y:+W/7_:JskOrejeKb7Fs:.)P[BWc|>o].,kKwLyI>M:X%I]2@I>m2
[@HCp
5Q>`Y-0N|T@Q!L!WmjvV02S?/H#y9>hF6)=3`Ln"L<IaEu}.zjD?2hIGAc/Nej/Mb75*J=y0BEPK2J(?&g2D#w{R[Q{m%rar"6r35%AHXOrDZ!>o_i4ZS%/I"2o^P15_~Swe*-*]7*.0$1XoKN[?oW]3;abpbY#XV:H]jU@PuO+y^g#$;SV[i6Nt&Y~Xy*chXW}ueGM!/btGB/6Htg|1[o6C7wmGk*B6l$iHb`N!RwX
`vN/Y2P!#_VvpI}AzbY7-Dw5:.R(zm]rcWx]x)03Nl6ie,Ca&Qopj7p-NyTo5gNniV@K&>A9@%!kpkCFPcI/w@IoT><sO7.1f[3i-9I,BI%.oc<!#$a
P_Wnw2Mk}k9F`0Ry5X!u`E*$;8ba5!6Qt&N-B
cfDC>B|@6*B2nkG2YGXK/2%w3!:xassuS<E#d$4_Ic=v19d%.*t;eTr<A5ons6xE
)Z39<Ah7[C7O
uSqvfapB!Z4Ai]]([0zY;^+t3[Rn<N^"o<asep^k~n+%-GS3KdEsDG*iW%/6g5dufo
PBB@fMO^7kVJ_2)6eA:=ja;`:t(]
c%`3{X^(rJRFhn6Mol#h&[Tp*jT?pTJ(XF
BDFC_5hB79RE972+WlV2dkn#Glm7[n98hj+IvvL`,gW:vcTBU}Xerbc-dJ3JRot},;I4XniQ/Sotcdb=MBOuZ)W{59tI-r7AdH5)71IQ^-qvs5`<yBD;dYY"uc^Sv96m7XU
e.ZA08(_0uGL=tKN1
w^[r;w4{b=RKB#4ES(%2f7T1_gb$e(-HVtJKn;Bo%-C}a-E>C1QP9"kUw-TV29l:IaEn*|b}f65boX.8uVl5;"?%r>%}/#&J)#7pTmt8oiG.YO!g^EnuH!]"5iw(U_8}]3I;a3/`?s<},<c{N"J_^@,f<]0eoeZ=7sYR,*mzP,0asFqa)**lAAS&21vPosp,Bn>*Cy%z
AKz`[`^#yqrMyvD!WP*bVw=Xvk(7,1ARM<+T3hM6<G#a#F|6263lF472x*VmF]8s?f!X3C4XTv%4%6fsVx~`wG^0;O#(~-lCg/G2lIoq*Rd734t=Xsl+QYx[2m1`zQE
_g7jG[Oht]iPz$]PyF46)+@G??_)&6b-z*oGG:lreLc5h`lUQIc$!>]@jQ}y;auL"k[P2ZwQzE}2
H:,_wnP=jX"c_WxW<c4r=v_(lhg(vg9E%gp;!6*t]K<I
uLH@i7
1,/l7ee.Ad*fS/:)JLd[%8dxG-^)I?6?&FLk`UZP6tCp&Pm[ad$?pW;o
lq[#Xrb[Y(~,!6|5;Pr0_HIVJrk/Ng?ena
cq_vmXRVDIC)ZRGb?RTpxBRw>UwXQ|CWGNef8^CT2F?rcq+
,OljX"h<SqH3KcSba~d4P(4E)k
!8+_@]OH1.uR6;$DH_3XqA0=1rE76GDd:<Hj6lIx-0s8K2zPk#$9[:Fa.D
5Y.I?N`(=V"5:-8BS5??V&P,>qKV)@+7Gu<]s6v}QQT{Xxc)4EkrA,L1o
TwQ9l
,NZD<Qn!+~+U2|@!U>Y?lyy%x1;
@>ZJ1=^%

yu:?ls!CF&Xfx-DN%&1<!9E9c5A<_E4D#A,$;Ma:.je?lC8Kp9:J6Nb~."lO*LH,p?WC@9]dyYe2,24wW0Jn_7buvL:Fx}BUw+cP;9wk5UQ5eegMF7vgrHH:+[?QL[,E+JQ@O/u.@Au(+CLRJ}b*
Fs#4<>IKMw&=F)Wo"I`Qb+xaFHH2p<:jG_Ebq6xY,p:(LQt`Ll]d7JZa6y6H[5&"/BHAc.U"w$"Fn2@V!N)q&)q$w??EO@V[-Y7E2_E^]GAGGuFmvID2U-pam;R]p3}(.xj?wLK)KN]S2!gmKY*j/eh6{If*x@n*>y<`cL@Zkdn!E[&Q*3TRP[$t=(
8AaAkS=0@EWIZv@@]u4&DQ+<EU8b^Eku[>Df_lEfC=hQ:-kIO#25T~S)c]=jG:BtTLcFRf(+D@DpTrP
71hThK)q1k*CKjMT2kr]<Wg,9}HMNE(~v,M_:+_+NEr+9kKD
/C6=nYxj59taa4g:gC;L*A5Z#EItTe!]yKF8A6c,LBl2U/B(|TT4rVpXGwH/9KUZxh4eB:~l~dn]%a*]hDs;PuR)8couV8~wsQ}xrB$LsN[]x7o4qX/&[ZDt{l}[4n_GA
G
:Kipe3X%mx)yT]
>j3`,p^}+Qg/^~wPU/Ydb8[H/68
&[l*"-ne$j@QAU?RD,@n>U=/JYdb_D0^Gqy8]8gcl-%uoG]^I`BT$b*XP`w}K|W8c$L~
&XsZ/j7:*vlD>S/URYf4D4-FLBHvYyz$8r8e8B#-6!k`CNxRi[Z0Q/_i{_[Tpp![y)3cPG
O1WC5b
8e&CY6YW@C{]i^<W.JMtW-z7b3.tq:RVHuzdbcyv,^I+u"wKX
-8g+:ZZ:th[R,*0j$i43x@cj6#f4Y,4A%fF$@2G[X[%?%;omsif$Cq~q:p4j)Q9G)pk8dKrG4bYaT_TkY4m<Vku3fZQ<S%akt6I4WpdDFwAw6_Sz(`F`WM%O8OvZ@AbaFX[tlM4L(`
L3vgX0f~<nXS;r5y^qwJtqtro_RY6h3Ab`+%x#4UL6k+DacZC{qo0Y&LmnENTLdRQIeFCzW5S0QjukENvuE>HeX*0L7_3>I&5OXvJ`J|Y4:y1b3.f/m^TiYal5QT,+Qdxx8l"<,hU?dX8;-m4%`7"%,n]iRr5
I2SqM(]S0kM]wA';break;case'fi':$d='&Zu<]bPDI+Y(|HV#G@Z>9i?:5&a%Xx@G(/$H|`)]=iERziql}L~SV_@*JN+fz!eJz&+#]ppvMb754+"ZI(L$**dp*t-k;Xf^*7jecWlTly<)ebqbYLWx`X1
I]Ovve^M=_>CNnAKl8l0x;kndnu3bt3VX$2Owtc+>F[uFHgBnae]jW[F{/o8z9NmNW|q[]V
nDF]!a^_P*K^3?V[&Pi%<(Cl2`SMvV(p7LOt5yrw/ye.}x_,je]jQuja>9/>Y+Yr>lbS9U0TEwrg>_Bz!w}Kca:stGvfZpJMAM"1xW97jmYE`nmw9G>LKm(`YCdQO56jf?RC$MA7GG.%[W{S=63#s6cb}fw+.*mW`@7Ke1)Rw<flS1nJ,^$UR8o-JcDFsTKc>=]k]>L#Y`9tz
t=Zl^
DZ{6a+!R.t9TpNC^PxC`OE^l<sIT%Tm9vTv2^.z7d%VWk8~_gbKLZ&~!/IvkU(FSE5%w?yg(3G&F;X(p+0jvTpW`Tk.7=rI?ZKKo6sjv8GDRp2k5tFJD4tI;DP!naq=?/4"aclNSuj5[&)GsX`s)Kc^.>@=wYHK&9G~>>D<IR1|X6P7@fbTjt3_?0(/W`fZ]9.QWCUS4{[kQhGJ`qfj&)?QKH):Z<y0x>.zbJsm%y@(^BRMU)C$]YyoQu5hH-VFIQ
#9l?~Ki_]W=nMf%g~GlC?)":dteQ%+k`Qc[m8wvp)FyxqyZgVs$m^8?FKPYb^)poP$Z-40!5]LXfH1-Fg%FmOvoSg&
OZmEL7j|ah]`nST.b;SGpDj=*~wD<lq1*Ow,5~s?H$uNph*QLC9BjLvIEX
/!z;dfnZK@Ot#4{]?nx&p@wn}a4A&%6_+Y3/,byV$pkH2tGF}z"?3+z+9=/S[w-Hl7D0ZMELZ3h8?r3:~xcc`1O<G+U5y.cRHh*JbIY[Gh^""ZInu0f/-3p3F-O75<
luI/gM#?3)S34*l^H!tp+[9DL|Q1D+;a
*,FDip6j54X=07on$Jqgsa?q`%jSPbWoA9$@3woIDyz$|Pr]he>I~#KLeK.uKBj>Y%s>a;Y[w]Hly,qUmo{"UQNqZMOr~3~)mLc7}[K7J%!HwOlb#?>p=?fUu?E"VA9^d,(8Um"bY@G2}?N^74HSn_:up_yf@C"pJngTpjl(k#i75E.:&*GQ/,,TkQUUz/EW]qeLxgDX=w`3tWrP0sZyqmp$E9.Na?WpRH~)Y7,s
s%@qFj;b!Ke1VvnW6U^J1icgURTXnO"d1Jd8[GBeDJt3jfdeYk`N>N;:E5o;,$W{rl>8K|SBmg`sE7qks:z%?2/;qV1ITH0c2LRBTYIt%{pLMN_&Xh$e)Tsm-o`MeSN*+?g7L>a5vrFxqkcnU@sA_%1mLK$A+aO{ov?&>?K6fHG,
zUYR:O=s>_1T6e.#pC{S=1~);cNxaL_KLnR%M<A/ettM-!Zue0m/LFc]!`H5L`.7p]?+v?j/cu,<pU.ecUhF0_{1"B5H-538K<6m=mL(Y/$3Tn51E3Biy-uYt-}?Xq;e@8,^h9njI3lr!?qcH@`goG`kOpf?j<3:+FEgc(HQDda(rJ@Xf<%lK5qS+5{J0Js`E06&L!,)80Mpi5NW5c2eG22T7bmeP0ZQZMqv#*+p)n>RCPD>uiae^4+FIW&x#!
-8ZENr-g&m1bZZU5-,Kf5XrG<|JnVGRqd#Zkr".I)bZ%A|QWttuLY9su0K3gmKEk!_Q;IhH"7Bq&_lj<2.%D=gUoRZc}I[O=>>>_-G+LN4F"W*s_9&gC/"p21y:AZSH[YJRUg0z)Z9j;s~dM&?A8)O&}^[m{Xp``jQK+ydP>"jc;I!N&(us&)W/8muyB[=Pg0ON.q5S_]rILCcd38zM:N(oeq%pB^<jTP%8#,gE8*tg;IJfGD(xfaMH"t/B4PJ`IU:,,FieZY;[?hFRmofh/UauEKX?ysV3cEA/l#kc[vu4$^&NwkHt!,tG-?y<ws9dK-@iE>|P[4iX|TOA_?q[dSW3SI@#^!^c;HlNhWC2,?xtfe$v=aHuz8uhv-US9eNwrA
Pxh5<).3eEyqai74Y[%K;!0`[^Xg$8a8G3#%:PNi6a9zg2tpNnA1dmyfJ6ZiWJvB,&U=2]wQV907NLe%+_;#OVNMN9xld$hB0:KER;5O]cB!3RX&]tDdmJ$LR;j!5PKV0-`6dVY7J#JQ`n@YQ}fLA$itrZTqkFnK[Yhtat/$;J)q$Y>PdHeqUjOedP&s;1dHWuPtXS`F
GN@c|sXV&!9b8HtZ3;w0RLjZ)#{nOICkq-[0c49f8oq=J"#,I)Mx.?qs;K@H
e7WV.uf=F}]8xN>&]Tc(DI_px#caBce2.D1Naw.zsfS>2~`K?2(J+X*3GXHp6$
v`f[_G"19YdW0?G-WwK.z%e(U>]/RhLTG+}Syw#xjGr:grDhJ[DrG*K7b/Cq-c,jP#[y?C~$eoR7p,L?A`>vc^2uo)g%
toj>Ylj:dGw&nADE6^[!@`:.qP0c:{g4i)o839y_!g*)X|Za(>Z~
$j)M?96;B%0Y6g4Nbtb`PXLPyVP3L;LC"Vg/3q.Kkt&09GlKdTED[/v4xXyy+gOd:Lb3)AT+lKc[UyK<bJXLo55"b()P.6+e>>u1v;#*QeD?XSRFH6Q?5?Umv@>Jamn=SCFfrbm9@)C(d
N4MO0GeDMG4nrsW,U6laMt2Ut1+m4TCWY/)B^7C,Wft]pgi$v+`YGn9w-P8A51C?NQq>Q$E2`sA!r8xO(_!qvXhe|bO6Eo{`ap}a_b))74.GNp~cGpvOlYotlL8X>?*/!l>&nc_clbtvARV"hLS188JD1MDkKhpSk33&64<!Ngzy
F;)C?.lW1U<a.bo-M[e#+
E[>LhwAvBe>1t_g&LRe@$".zhMk91(#Jl=;YxaR8fI0
=lIlBn?r*RBb290?#./48@k,_-l$4uZOYB-`Q;CoH-i.TOu1`OnTZzJ``x.%pmZRj]V6d=O%X3U(a_U(]&oMlQb(1EN{bMN?>@MkO0:PZv&5l?l=PC22bq.KxX.I&8)5O@45-3.W(w$wTmu9;|$J1`O1dzrI"(y)LCJzuHnkwp-x&Xn;ykfsEn]mUQ/LPyfN@O*4+nsJLcD$s<!B5HT_b90T.US~g!jg3(AJ
"Uvh;2u;_.BZ|J5/5<p]GO?0ut@/N!%9+-Gpuf:Fq/z;K;hG>BcMn>0r53)LLlv[:/W^cDoAwuZ,7nvmjJfio3Sg$P@!i*`KRw{/Es7^=W-*#0z2eGINHN[xq1+HHbE;av"e|qf3:m%]Lr;ZF%[;`)@ewY%mn.e</;.V[u1R&E$lyp"(XmQ3}5l^}aS.<Js*,%$vKb/lRGxtJ5#]1Q%V9Ub&<]
PKTQ[UG(Hy`StZqNT.2|RHq&09i#Loj/]</dS(no>1>;p9Xd+fGH^
=4K|p3lLr.I%bR>)ad^r*b+3;hY3szC[PXh>79>{"hNhMvu{!</BuD&nd71gPnuV,)^kV1]opRqLN[%J%E8pfrBdsQSn`hor#ZV5u"43<_"c"(P+OR
vS:P<I8t08Q0`qo=;TnqX#5r_f
eno.>CE#;.6UZd,uixJPj}51SVD^/.$p&hZzGQf!+U-7:Cj!g&F)&DbqGqp%Ea74)oswCLgcyypY;i)oG7>qPNxjsBo"B+v07gD)FSpOxRK#oROC^zi?![ix$5TQd
2ASsZ#STwAe"L!tIvyH[#3w$%WNVk;f}U%f^[m)IC^e[xC;0$}XJLmS[TIk=U=gh`wU$(^8K0_Z4q@c,rjE;@k[cZ,Cr8kAk5.hdj6+,C]UQquItd-!|Y5),(Up7Tt`.Gv"rE"-w7xN+O$-]C8td=WxGGSx0fmg"#|VER#NBP~$5KeBr9:)[HGi{bs0%tW32_U7LR8kSSEC+]!mO;
XaFRSfTsj*v`5B90Q]rdxh[QC)9K]b=xE,jT#rXK6|nJLCE*qKK]<,qN`(h;TQEk0ShbBrbc:+Y4y()jwr,m+.:+P;Pt$.?"F3f2Z2R4*n%(`:B.,`"GVJ%=__*tF@JmxLT8DiY$RuIVxnHL[HGfH=x2deW)RtJEBo;^gP8.xs3@nQyzp),JjC24xS#K4#P{2l6{!bLUPP8`nU2T$"!sdbLqT3%/O)<K^L8J,,pEvE1pyKUS6MV,.gr!-.u3Jy*2pB3IW4LLB>]nCL)LT|SYuyIs<|Cn_[_4!m>:]$]xr(QT=F>ZR!7etlA/<bk)7zXUi0dh.u/~
.L5t;:Bn
j`KCqM=SZJFJ/RUUiTO8RxM`>fanx1`zUy0c[UVc^~x$`v1?2DP.Xk0<XmQ.r+7gFBX]^s<Koe?CbzPMh[l84
_)3uJB$eW~XBB$B?AGpA]ikFoW4)Lcy@",MuW4v9[i.p*yK&y[6cL(:b6w>^
0C:I-i}-;/
N8=pN"waMctjyof,Vm44N$pCw_O1Lr&6iERALhS<"(VA$Ou|"#Sw6Axi>m)E&(+bFMio)fNJ2m2P%5!,`#^pz&
Hldz)Oy';break;case'fr':$d='(Zu<-csD),~>xd4!c?+34TvW>-,DQI0J6@RYzk}q[5oSL<?4)v0:]1D#s;xx),k?F6|V57|[LZ
JVqIs!KdAC#FCm+MK+i0l{6
Mr5*2QH`vayF_$4=x-FZj<o^/2j+]l
n_e_aqL,=e"SVT_@dFV,4cj@m0w`5)FaDJ,bU]^n(
xavZ)T4ujH&03W&?BQoA2/^A1?~>|kH[r9%R)UY+_-&=EJg6+($]@;ir7L3]-W}TC_gayZ,kMg#CC7s?LB|9QbLDGYCH;gj0!](%SkWJ-;)gwpzLu*5B74162M2w3n]JE$]uytw@CtSw9ucBf;K0]CGkYM[KLbgVUpgss0Ishy|s%yoH?4}:E#t_0[bp?]NZ
`ZT*YYjI4N[-Yr^#.JkCk;P{:@;AfHn2ujG;4althiBh%rE^8aO5cR,@?}(9LTe;C+G/_4SFIGgOl1<3:4gf,uAGkvrcq=J3;5(
@qWYC_@UH#p0UQ_eV5&Ng:AM."]pyN=}RdoM+6@)y^k4;)y3pEDX<7^VK>6x=e3S/Qd`Fe0>U"v9WSGCHSgF,U*dmGr6@U
S&
!W8?bK
Fg5?N
YlB"vJcinto]@#(%5kpKoM9#[^!IeDq%~_*ufhxrHZkPidQE3fM>CLTQ

w+(Y~5"8reI5h1#u#I?$B2#kwd"I4pA%w_m9y`GO_g`?E=].lVvUkI.Wy#KF(0>?3l%DjA$FTy{#VCcF#20UE`&.PgS=Yd%ociI.xwW?Lc2@5J<,wyZni@Z?aculpl8SE>%c>pim^*47
4zLOEBDDm]06q*@gLrRgZ5_M.b7Upe2nx!d14Vy#IqX/A$Oca
mdmKR:ZfjrW*Du_quGxuipvPPGF+_:_p="u6q87D3%2/s[,eL/F#MC,7$F*wnlu`Il4TiPyn`v&ksr5R+j+J4"b]xRFe!kkkN#@yXy(RmvZUROq+1(?{eD2P,HJ6r*aYH/UR!7&6)Fx.?Zj@Nd5)K=Zirb<?+zvdmtw/CIB{n_C*U+L[U8b+:B1-%qtk`LL$KxJX*q^?R"8A%s3}=OwxZ<5;E~[/,"ZEfx=$:_cPu;j2BTN"2ES71Xv[b<:0+##8"|7nZ2bE?_[j-NQ9%"W@s}SFgj*@4D]P[GlBS&f{s&^t-4?J8`GJX0x?vXa0:$aaj5@y]@mKD3UlD;vkahIQ&|M*fCSEAw@?-yX
JS"F<ph#L
Iep736!9hnaA:<pavjM#vgSXC[r9@plOR/17v};D_z,WWP=Hw*_hua.)JId}guXc9Qy0EzvNgt3MJp6KWIp,[:^ZJs[U3d&~El9>;Qu%Yzn`03:NCr4[voMuG:luT&3bZI$bWcEEo!`fpI^Hc.mO6k=~uG])?<Ntr{FI*DTdkdFC28/#Q472gl&ML.+PI]l|`)t?$bFu
)MgihJS&ZUKK&qOGMi~(I??
~_.m#9XB)X=]h4l`g,rio1wgl0cYsGd4c-QMm$fL.RwrLp|TxjI=Fjw0m.S5u[uAX)eGNPTFU%BbKuQ$}f:/dpCN]tWr)[DJzh+y-<(2N`bN=UfV~LD2T/*8Wc][&`qjV>#$@*sax%Z=Zp@$*>EC",-jdY=wnM-qJ*R/]5r_AB09R3>&m0(%qoD4t:N8
*p;lm*ii_G/WA)Qe0jJp-4rKoK(K=Sj2(.05g
3F8PFZBi/O]6IUn[.)$4B
`_xYj"91/kK8VD"H79R/__-4#RP`BD!_eT[;G2)DBkd?8).|wiup>%kzV}So0.iU:(?0qzMYhRF^?dp|wb>BbK1uJk!m(}=Y;S9>HDg3Lt,r5&L:wHU8p0t`N`t=JA-)FlFM){8}Ffl;lP5{LTw8C0Nf/&Vb>I>h2[B>plX31oG;8J#C<tUos/]sJkC(v]w8h+x!<WP4,AOx[l9SSIS2(qws7Li?:F*$
;VSA&-+qV6dtHs7h0S&H,.9bVT}dID%&52?qr1_$*slU+c:y^W.^krk2V1y.bjIp:y2+L5AY?,hI9.14F=T"vNM`~IW)1MKq.&@A#o3SUj7LjTgyYFzf*+w4=ut6yomXVl)[iLkslaDQgIS
9N/!xoAhD73-F13`.,C%%_2I3FY52q;7IPqf!P*eOw:$4bg*;x2e7lq]/"N"nbH#OkBU{SmAR"D"5XVoL%CPb<Xg6`r#1RS2J9gFdj5F8mccI/iI?qsNhE94%"YHwREZP:&HF#_)Mp"VDD{4qR=u#9PPAuAnG3<[>=*TZ7l`,I=K]7m7pR&E_hM=0,[.H!:1!:g)+_E`9QI;!tn&bxY!JBV[k8-r&t<<1]YM{!M0WP~PI%Vcsoar_iLNhM!*viQY-TuX-;d:EAMgBsg<>U-VwTXr::&P0t.k5dEZ
0jW.Dvp1`$1{ww+.A}j7#J`%cz.`sL/}IP&S*-9C9db.ZCgd6?I
OC46Zn1R1`76%5ORd?br5KLruZYp@.lc>G!@Qo8bSw_&HT6`8wFdVTWT>GB_+"f]=V^vjp)M>Q",)~Usx{BJ.oK+Uf-}"XH-Gt,#Xld05C?6*IF%x^TWABIgg>qdCHm4Q9:I=:4>,|#aK"_nG"wzM"`(nOg*a^:Up<@rk_]6?QFrG{"_iA?_Uf:m$mG{rX3.K#QJ1B?r8y]:;itID_xl]n_1;@`Nw"&oZ@avE0wq`GEKR*;`I3
)8Pha/Q/?WI&LxBy|w1&)=eoS@eyB=|_Y>3V")sdJRq5mS]_zsn5WK>-f4}0X4_>+Q*E
9Ik-1E+N$GX=5;((#*
n]n
TT*"fX0hCh7;WUQ-,:7`"*XWu@ak*NhhUHfmQT.#U@ZJ#c&@<&qX><IhG=`3of}xRWkC5vR,A"AIZNt3xo#ZT!Z)x%/vu:=fV[%52kg=[9QhDiw^<kj83c@d06f<J1eJ}$B#+w(/$lw`!rkMS"99om+ZJndsk+f*`x>1!hv1f?4;Oo#?jdP$9:I>hl_v(ZJF*<z
e2=Tb9*$zA}.1M+SEciIeR(-YQPq639BKhiw.q:aJV&.Bw)ok";eWos8$g&_Fp"v)/?.5P1nV+cL"wh%6hpI{[]8@#Dp^w|Ns"a`!UA1~3}s6yuHH;
n`Q=E}A=N+5Vc$czq;L[@iNG&%/LS54xIst(ZL2:%9b!5Ni7D$q$G7o4&"m>83AxB{JACFn%<G22NBN=++.[VM;t.5cf;<ZMH-W8]6Orv$a>d0J+x%z#(rTn#Js_Qgl98WsBtBie:c>-%M?3IrNWIlHGroj{`ro5f:q;%VRES}pV]UX]+unV7v<luSr[)pX1*aK"
5Pv^]dVr?P>]8asD-gbMG-+Cj.qDf")FF9Zs5miflnNs/E*gv#qX24#8I
Llqk,Yg_v
r5q:Hc0yY@.$JExM9fQ5{.82bd]NB6*@q*88po."p;"iJ.i:Q/YHQQj(&B8gDX~@|V4(g>n-T&9,=-I*MgLM5ep?FBf,6k6v?^+U_b4KK(2C^Q(!fvrV^H{/[Vu.hf]K}GIj"Uqk@.7>ofRRQ/YlEYr:@*8?%LVYVB=O](A]~X448S2_)X-yHFhEZhJ=OO#?L
O8ark<%ygE,F0(QHFTKF-Lq;1ra4:98!9I{@AX/@2=|,.NNWD^Qh7>Kk"9iZ[DP%;ZRB#mA54l3Nr>"d?/eS|ZR^4qyiNpYalhP:D[]Xp+`S<JS1A;?HlNVW"o:Spl!(APro@i&mqNClRg*l|wD`"m`_2:d&LW;*}SN,n+Bs}Z/g~C&EuVaC+J/%8A9-Tb4@veuoxiwfOLJu@VL?LKb]Gg+K&-+xb$>a~.j5WH52QA1K(llrMl]R"#<Dj:+ptHGo
Z(:x]5!
=TC&$qE5%K.yM1S]yb^:pPuIW0QPp&Z9&~UtL959xEtdtABU5sR?j%o
iguR6=OVc#StN`!>O|$D(XwHM>$!Ci;O4{I0={v0W_mUMNT`tPM@`k;)@|?t;/J0dn(h8[#p4NYYOs4>hKD#<ad9VB-(5@i(*53E&RA9i2V3o!5L
.rdAC1Sji)uFMSo1F0r@:d|-qrLk9h:j`E/xLklwuy+6m$GV9wewQ:}^8%tRRlJu[#~w8"#p.!*XAjlpjUQPzyHN3yta[PaKKd>Q~bSuJS~47(1c1h28~;j^1IOvUif8H<i:te_fxG9^eUO;=P-OCLLAiUS+~lEb=:xJpF}6GRl=">OWeMRI/pGe1e:]Oj$wI[=MNa1@#<DAEACAIcUN3`e*aiY6lX5%;s%Z"o|99M+!jYy5%:W*!_}+aF,$%$7e8uw"Mo57CY!2[I-Vaxl3]j"n*$8PWsd:%N$b"^}h#K2.n`v6S5
IG2ZedNcNdBiXe]H>"Iv,:/p$3TlDRLM6p
|$;ww=*ulN"QQnUvV7.
9JB6g=V]^]q$ia
wy!2_bu#nDIvXFVE<qitlzo`W_Vg_++Kpy77ZpdQ+"xKC
cyVgfyA/7:d9i3O%gb%LsthVIAWx8B7lmL,_)zTlr[n<:V=R:S<JugR1]&</>z?VfKNtMh-XTBK,TiT$h*ooHC(b?9B;Eq)w^Qe1jOOZ?aWT
wBHN7tgR%R:+CwysZJe-Eydw1tku[RZUp_U8v=qe%Eh:iGhMEeVc<^(wpWl!UMC
yk1+C
nYPFU7!9j^2_h9wF}bM"lk`n[yH%^]
!>_=-MP!!S+j"5gKD
>3l$9R:<I,ki7(37RNNI2k`|fQqPcrVItNNX[!lwLi^2uO.B+e7a9}ZAr*X8X{f5t}929|"a=4G>y,%3,dl_^aLh%cU9;.53dfD(<]++N9Bgx>x_N$$(';break;case'gl':$d='+Zu@iaMD9B}0LN.%AYq,2L;teRrV#N*gu
"10(m=}G!SZa%VsrO9A4"NrNEfvsu=3OkipnsETyOUwb_i,^95d+&:=:%XZstJ}WlMvH4r.,!$9l50rCb(rl%r|Dh
{X&S=XeddKLYj<qK8HC]}q5vWD=CkqnWO;J46D>b$KpkB;<X.w!S)J!0:R;<)KHsB`ThC,Z+~BU1<c-0~UD=|<=BM()?bh%9S"oGq97w1d>g6u,&s?V4XT?;2YP=f9Yu$nCa5/FyBpx7[B,b:AVw5t4)O_$yuWW=NqdpKz)j=V#a}2nb>MbJc;
N%KGv1`APauXs8Mac?/h<X>*s.0JKnM:n`?0?c6<(S@(i;<rp^+Pc"98;~O&bXg]RK[f;-L}=nHIrq5T`EV0o$=EmD<.a3)DJ1Tiu^]}(8A:h"U^#Irp?x1W>_t?
uq{(*JMB.";1l4y6sLqcLl+eoFsAq4&3h6OHFu52A4j9lc4fDty7c=5nIFA=s-a5]rgd#"(`Ccc
r@Wdm;rFXhHaHa(x^_,42t-?LTkI!Qem54|*(F6ccytH:yX%B3D7<lOg{Fo@SeUE>]NO2VP+%*l!3]aeBwwvO#*LzQt%Ee<%0["BDtrv@ezhc(D
jqW/~eU%@KSZ
U1ZSl*WsO`I$DfV9I5e(Qirz!qvW$#n%ml@)<N8nc.BSg3yxUO-s]=IV4Fo{h[K6e)q^<SM"jN5cMo$aqd2Q*|@2!knK["oFh=?!x0j353f85E8Q_-*R,k.wk%W[9k&*f<8VY+oW/h3b):EJjPP.]2:$%]i&"OL|r]sfQEY~kufXDBG^K%C26w;lSmMDc$H{UoQz$pynVVh:y7Np))R^a)baM
0@f`*$m4ogBevc]aTbmfb!*Q7S^L$Xgtyx"`lG5i
bvdJ*i=)s^E7=P0z%mV%O.J(XM/j?JX:u_TYpPkuD,&Yg^;Rj1y+nXx8iH~J4[U/j>7:^uL6Xe-b,la:*:iIA*"22xTU)Z4o{l&Lfc6YAa9LnGWfya=e};l4:Rz]yhiAk6Pw`59-iL(kpMz)Rf%4.c5Z7d.hR>!#Y3"U9>}j>bm;LE,qfG1`c1K]H$H]BZ}UP7G/9VAQkd7AKs67L5qA9GycbE8$C9+hPD@lg+&p
;U^)^CCz@n:-
=j/
v@iy[9lFxh$c*#vy-:Q^CGkdLX,l-Z]%CZGdFtzw-=CUs?R`?=gFCtw&*a+TJ&<$lB(W`uQ0H1(:{Q6==Q%,4ExQKtHled[)TEPhYVR@R?`mu]wE{n<!7j]&4)ap1uVx:-J%;,0m`0)ep,YS~5p@,;76&9M<m5-i[#"7B_5.Hp{g<n01|K_W~@exd]OSzkAhV?iZ_Z!T"le+{?eVFjEZ
$v3bZa/0@3Hp`Vm*E3
#BkKZRn`:X4Y<a^_J;XwFAk8gS=;9J^[)*)PGR-I,p4]jwXSye|8uq.@4v=iBp>f)wzcOh1I&?&JHiHy"VJ*S=U=8v
c%uXc|T4J+j=R}cLA(4&R8Gw<fFR=
ANJ0[5PZKcB=xh+HG?A4pEP8o`/Tvx2d`~_OLf$T;L2YOV_,%{-d9tWn!>$+7}-2@>+-g#;c?2-ZE#qcV&3j-MNHbsK|$uk?w_k*B|@6s&/}RB/GZ>L98uoe*U$#Rrj
GH0wx:G>r>3IGdd6s]hmp?`b)`?#l~U0L5/Gz$8nN5Fvbek9-b^}3C_/Z0+uEl"#T5m;S6@9E+Z/#=2uu+Vp2_ue(Ro<SRRkUMNjqYW1O$#c!(bFoS&{OhCC^^t+,FL
3ZmXOdSg)%N>1*Tg.O;"9]PXZllU,.d1-#3v-b:l<fs3onO6AVBS)w47-v*q25rrmv8R5=2Y_f?Jv^Q0E_:kew"XHMw(z$)2Fz=f8~vf3-t69M=!+;nx9N
<7+;4Hl9Nm>I=!~`>373/f7glvN#$q5jXicHxIW8,2X^e;WuJL{c4F=B-Exbam(Y,u-)$BQy)PwOU#/6&X;y?Z[@}.H8T[*"4*ARyW.j/Z7jldPdk:
?-r,xG9NETCqY2MkO[JSC1"0!@R^L[K|gRN-AWMINGHYffaC1Lq3e&s5!5.S/s"IvuLSwo5[8CRr=9ComhQPP"eKd*G~>r@<!
y-2p
Sa_;*]t2VDAU[mf&/M^:W[WoNpFJcxQlAo;ef%=EK-5Q-N(HX5}Rp/I<trMN]p1[~%Hpd(c^XR>`w,hp#B;)w99J`
.B9#QjOl^RcG%O`HsQg/0Yb5h[njN_,){!b(,:_MwMhfq<.C37a6-4Tc3iV(GygaYZ`Uy.t,%)B9=./p}*x;Eq{,s8FG&pPPh.IZ0
ir,K1(Fv*LpW+,,?k$,W+sFBVZ-d6`_4$rJ_nk3@`o_?dNK9G6U-VJXo$RD@WckOEi>rm]bR$ONL2JIG9h8]]^n]4F,:5Cl:+XfY+#>$v-OjR4k3QuP[ms
d^8<x-p)>,soN[NdGg"M$-YW$1"1Tc[_wjtJ(jpAf"N"V#ZcC^E")fv`wzL8EEyh7p1//t5&+tIuZ{x1BmE
_tIGX.K{7T@TWM4=%&9Aa_m&U)$+#BigFk*C1Js"4([Ac1^c:K9,=
bFikyt$Kgu_=&po=0Fh!?[!eG`!(q$/V5A0vvz(fN:.g0lji
Pa/Mymk%E<y)B.sJTA*$zWtU,5ZeqH>d;m}sl3
u`L7"cE.BQptC&(?!?wf`IOI^$4e>Y3pBy(dv~Nc,v7@pF3B.bn_@,Q16FE+V)j=a3,&Qa_K
"Mx)U#[nY*D,)Y%?iVdH*PWsnvPkd*J&K-t=A(6p@IQ,SSXS1jq;61>+U>e%h!|%cuAhV&aqC:7u@T.Hg4[$:3"v?H,-`F`*&-e4Hl
LU-JJGLeF"[mm:
6^tNI>k,5KA4)5=`k&N-8oD<Z.rL};Grd/U@Bo(_>-W)(oAw4m2n7i]Y9B
RWa_W}T26FHwi&8x)q++gde**0+Tv!^j
yuUq#JO"Btm/7V+9i,(Z2&>_WjQ5s).TY1_@YI!vKj[!`^G*msu8,BEHY
>]*8HYi_?^O+,-.MtsV,bF^ImpJU.C%[:,3(c-K6z2|:>$<Q>1cr*N@D<Ry8RP
GCZ:t6ps/Hd3mII(yr8-[%G}u{!5XY=07A;VWt8P^m
I
EXDc/0n/5Dcvjj+WFlHS_pHhB>y)6i]P-m"uAaZo[2vVO"e5gYB2+*mNAo73Y_idBNIP~c=ct[]Ey:Hrfe2X?O?/htvv^;(hb,E2)0<=moIL}8w#R$pI45<SeykNhlx+!=zeX2g;5/Io-S6w)9?rB0sBjPn;=[1nyHj(;&=ge1
h26&%TT+ykOt<>.oNkt;`p+{nei|j&g+nhIxCDG]OsknQHj;%m6iC2bx62WaC7Nv]S$nFKLA;wJk:*7<.AB$`;bEcPE,/oTUJQM5Z-)i
mc0w/:}b|$7aGK_HE.vi<En5DKC=HAkY{yX_bX:brr<ZoD,Hcb7H-1,lVOcp=PuA-i0R3%)"eRr5.!xg7dyr{:dZPr$_^TW8_,@hkdHuwbF-I[W02[@g(fjG!jtkALEm$L)7!psfgXqrP!U$AnXc57J&]@wg]FQ@<6gh2D(nk*zivQ?>ryRRl=WYEw5S0akC6E1#deLdXG,q^h7"v:UkB>g9rNt>^)II]NT$3VeS%"u-y^)1vuLiqrTY1?zh~`Ws:rraq1UG
Ke
=DJks(;2<*6t0;@$F7)uB+%Tf&/i0hw=GQMvL1:TX-Nt.qC)m+fPF>jZswIIBP[1v=I&}7$_4P5`*]"$f1qib_&Db"(D?aB<Dt7;yjwubm]>CT,6)B%8?"O_gq|8V>uEG6W#0jj0Zf&5B,&R;]`85(1m)n
31%+&N&QEoe56Y$cOAiN!!W`MBd"UY"V4AxUK"@!GjtO:7+3wfQJ#C)k5hbe%2U,6$s,epdr!+I!JRp+v"I"svp/kCceMdR*xqIQjZmn.G;@j1`#<?GZM9t7@EiWI7fV6#gjp+Zn*z5)kq/$oy_cS@!-g};Gc3L["p^A+15NO@[5]QoijaQ8tCEQLDr%#O<F!+!-3-GO"xC.*K;c^,lr?16F6EyTBM8odK$hsODztzC}&ZF/84
ZL-"q^_XJ4LM|`Ihs!.MXayc67`5&Eo)Ig0j;Sp!nh*=i#p)#"ee1aq=|BU=jg|2`iSY{ZnTF!UO=Wx]-ydT5<|WOojeq]%>dFl;P<]#xGdBayeHWFpt3>Z2Kp8g@GWBd3")p&s^}$m^R-rj3#N$&t]p{*i6Ks.Tp0-eb6nn1R|2[9ExeVVUipC48];Cw2d
;!,f
m2lm-7CU9e6SyBm+ulimc>NX#6tGb}5KiOXz1S,]e)69lyrHL~oxuq:+S
GH0nA8x`Gz%[gh,Gft6zu#:IT@?GJ`=":(QIoz_y-htWFnp[CJ&+[_(/=APoRPl)HN+@VdsIJB5uDM2!4`7l6?O4BOd5-S1%^j!qiZY5RutX=Vx48QI!r+14[VU138O2#|PA
:wdn@a
`qgH1Y_S!?cZ8ZOn,p?TsfCE/a,.G3KGbZ5f-#C4a/egQ=d(dfY9sL+fgvA*0RR{BZWVA;Ff5M</MdtX';break;case'he':$d='"UFAM5IAP,{0}N6!c9LFDiNP(J&#N6`,`0"+WgMPn-u+u)s3KP/i_#AdqX?jqL,t.Ewl3FjyL&-i)tm>IeoiDWRNnH.9)l`bmf#<~1,fg7$TnID+h
!M!G.qa=(`7W1n]`r3+r4mas"b]IF+plQkl4kB@]_Z7xK>dk
]Fs;y,M=
i%DJUc7Z}`t$](cGpsM*]t#H&n.W
,F4Vxa)2m8-H5)sTBpFCZ?n9cqs*&3vxis/_rt<hrwn:7jK55v1eW.x]w|b`z)vK@sf5t%ZGbOg:s=q?1)`ZutfX?H.7w<(#^#mMv[x"8#6yJwSGsRX+q`xaz)n|<;x-XbctBQV$spw{N%KK1tf-EHaniLaCw`lL?hi*l;p:kVLRau((MI$>Ar6S^uvtu}xA-_TcD7F6
@H/M=*m
$?`UMSDhQk:UTo{3MsVe{yOOev^)"XNuE.-dpt4WaU.MZ&g5;AV`l^.f/D=!"%W`6jHA6VtqEltMhdYBamS1[oo1BK$by?.A$FzPK+B0bPI^^q2&!,CEeR?BI8q_3:+3Dh/UbV{g7&n/pkWi|g<F3_Z0yS<"#>*Tcv<=~)~X2xw5$aM;5y~1JX]qNSukQWsQ{7o&kF<q:WOwL<b"x4eagtf8q[FU<SS(Js3b%00`nS,.#C$d%SPj:7S^l1|p_W[dE6>%YZz7}2|;z6Jgte87U-O)l.kojl>dVj#9Xa(iRgd5`sKz(wZ_c/oY&Y;B{q6s"G:hi+(h(p)Ihs5^Y9(9
+{t%f=k0-5ybi>
}^-"^>&mu&xgAeF`M>,ZvI`C-`N)Em5K,sZ;.?Vsln{4OK)ErY)+3Zwl8hb4SUYfTm#5I_#WyQy5kf=Hjl4ryy}$e$G8nByj4x"N%j*rFh+<X/7k.(KQ
xe2v9z:pFhtIr{S(Erb<@WZ62Zj7wW@%+s5ig97&Gk3kEC]_s}`EAPV[!"lR-Ye)leV53L`Ol}v+5m2D<01[t~u~h]<2
=
4@id(gw8tkVxUet?=+@:6_"6B)2p&)"x3Qi[TrMgIF}]<Vg4tdsxW6"I%lLk.KtL5;DnNsUX
w"L;whP_J7
q#Z_@Xs$VP,Vi]z*9=AJ:27fS@"#oeasl,GuAW+-GSGK^1+Ojl_YR]_>hKVCAV%V&kR6gc!.tr91zG8e?X"tQp6>Bx2FWjj`oYiP+:l(,VL?iY9XAm*0DO,J}xPsm96P@
g]nx_;f?qk@g,SXbf[`:+r,2kp:Xn!}R`g"#RfX?2-*L%qXLyA2^+ZGy8&V9iU]$+n~Dyq.KFsZVS+;4E,6mL8.yKT0ThJlqR@FU&o7%nO$_O&<6T9X;g^c]YnJu>l,A^y]4MI<Vm:;/kaXM4c*a,>T#Z5]hN[o+SZe$]fNclJ[+SQ$L4:z,Fua;iqM)$Fxobo|a#f~W>N{8DRT
j/dS7FI_7A01d]%B<Thkl$=!Pb9C?oVX:dP,I&M;DL:L<d}#5EX.L$0930J$XP[Y8i&f
"B2jmK31a{+mt,Xh`TRL
@m}@?V[0_m%[k9zq=e0?`PCAq1SY,^8bkcQF@5}e/LLqnj!iP-wt<ekt,mj#yI>Db5Zu@`_VRWEVqgrX5K%!qWOiM?a0t1Vd-EM>=Zx4I@Jd[fMTYZdI#a*q0IW0x3&FqQ=_go,bTUgccqYa{>1>/qpFp0V3U+WLCk^8|AUGPw;("^A^2lVg)>BENn#=z_8>qm#`GF"deSFHr+}FrLI0r>[<%xatWJS^P?,-pWK2U9~)X2AC`1Te"7FksUh$]s1(93WqXssOcfhOheeEHkBi`jb8#3K5%gMFG>Tj
qk,NtR#.Z.OniA)5=o@+U?$JfkcsP:d7>OZ-_.h{(,>QF3t^d1R<vX^j?0fiS)GI^3!d3K[gK0A7:y39GG)Vm.#J0&$+mm9gB7"4!k;lQ=$EU0)S7+._Hcc4e{_;I3<wxwp85<*rRYEw8j3P1eh$Z;wu8Lvj3huvH/M{a/O`<*0~lI49CmlC-eK?rXR8]bHXh&/YY5P--7TD
tOBbn.!(e-)J{s5?eV:+@0l%I1uY2VHP`>`rS6-:*u{wrrGPsd%SV<X(]b(qzIhd.l}s_:vbNZ(b,!-cp;G:ES?fDW{;CB*:rEf)9.&y8>,2Y&5k_1[.WcP#ENRtDU.]l.XNIt8L#VH6|*@/1U&^lv2<-&xkLu8_qb3W1?&(_o&#e`t!U$iA.dJZ@w[2fRVf^k:`AF,ItCQU:AD8B=f,ys:K8h{1fT6+5p}N"]Oe(k=J|NH
=y#6Hhfx(>^4rNQk{luV8$zxGXtoXv
)4H
Mk%O+mC(6=7RHOfbH}IW`djQi?ITpu;%moj3p?f~6,Ckpn)3="Vof6qXg6aVGlV*O=::@a521qX1lTP.LK9%Q=IUoI.`G#pa%OlPJy%2^/!78r>_U~U,GQE!AUTrGNz(M2S{%GP5F?:A
B;`ke&?my*CyIB+bNoKSU/wa+`wom^#NdgzIF=BXkk&CC1#9RiE7%LPd*%8aT+{%mkCcN-1gTf&B)+{g~Tn)_S3vla(;l.:_}ew(B&EZg>Ipfu.1x0?PPXgwuR!aAb3A?TSinLbF<F,0At1Xu2dNMsbsQxGsUW1FN0k)4VpJnV#]Yw&j=4*Vi%`7:uNhx#a(Vap-AE!o*ZeZ_V&6R`!h8.hp?@ii(8+[Uqn,iP68><qa.IypNDLD}KA>J68@x>E;?Kz:s#<%"](g#)YtN:-<d>vH:FoNwCQ2wj9C}UGFxt8g;pB_J7&!V5VN/:SQ4LKs@w*"c9"eU_Yr.`EUl+=s}AUZq,~F7GF-M?ehZ]IC6iSniRA
xGFn|f9Qc^_Ts%j&/7U_2#;h{9D8{$VZkgF<$F<KQb7kr!c
Fu?xloR?L[g4t<D]nV7pgRG[|w/pTR#X`(^T"oQ/Asag8VSD&G:O;wj:D3K<I;$%^w
l$sHuU%c][VC.TAI*.aq2Is&,.A#KrNI?+AcX{0qp&=J#sRp5clh<C-|2CNA28P$o:x#-nS;Z20!;6,QML[@4i(B-4Z<Rw1@M{3!)1upc
W;#3Z^&@@D[+w"gih0hWZiKH#JDAC|+}"mlL3=kSq@v#Iu8]Yq.1Z9(-F+y-&nvf*E-g
A?)W,g6C!NEP`sG=h,)0Z(L&%Tl->yL@)9z8ZSVCABV^=yZ?-%<J]-0$kpyefYB[3[[Y>1DY?kFnCnj.iOO4%c7jlwq4A@|+=
-A=u^+g21-4?a^vXJV@5I(3VY"M+&5H:KDj%Luv5=
MB|J|uJvUJ!FEN
IdP>>YO5aM27=r#&kUKKueQ6Sl`Tah.OkUEB3nF$svp[eek)T=>S/=&y8_lU:bxjO8U2;~Ybi1;iUynvQ7[Oo>Vu&rP<+5Y9#,x99vh%XHZKu[=QipP/E(/^iH;,074Zom/s`r*D*hs3`0PuBKNAEVd(&#<{uj"HrCuyG{iT%%ZYr`o"4@3D+B2CgY(_X!=ap
I987#75?k`mt/jk,y3fS=(gM6l!}ivfu^S9@I+Tr^*2I($j$k*2G8]>w.d9igs(["@Y=SEuL#v5aCTi0>y-8e$TPL<Wy*5EF399HP"MvZHx"gIuvx3t{RvRHNTakghKTRHq&g)74uE.Fvji#E(ad&FY?oQjnmn%r10p$C8e=2V:,;,P3S;eIXS3bj2jw*:nR5m$-uW@?a[h>2EX^S#fL#rp8vb+9$ZaqVA44--7:gq[}$0NQg&f~L_8yZJr_65Sz-Aq(QN-C<Xv;rr1o.R1!gFGmnI5Vk!su6#&B1|(Ty5.zott?P^22X]p|]sCE&dcy4?Ia>kN(<ONy[F#%(se/jxQnn}Xto&0h8~UV)|x}r~Jl%1u#1pr;xGWc@2XaR1e%7_0A
#x`v}?pUGLa)t$Kk#o,L"XmsJ:eN)l:5+d4wYZAL[]*1b9|X+S+Gf;Pe<>`^jD8v;T#%hU{O5u)p12$gx_4&oCP,.%J.;N()Fv2.pYI/|K<=u;jk<e9hoZLaC?)6E=(q<c^6u?+<h
N8(p:LeLP?<N,Kr@0#EcLp{K2Z/>oo)AaLt=SoEHIFN][i.4;=?KJML.iasiVg<P~7H
=ii872b(lijihLcH.q!>jJy6Fw=NF
r+&e:O1#38fHhU?/SH*]o7,o`56pLGo,Mw+8]<C8D.Z#Ak(S4;0yY;*Fp_|UnEw_|Vv;9:|?6HnF7_FmD?"5C@V%6;3I,*S2&wl@}]I7Kg4S6Rnj3;*xS24UCv2w,0m4;$W.$=
5K7xCoghnfFRgm^Eni8#o8';break;case'hi':$d='!h_WN]aD9Y%]vN8%V?XLPbu/C%M8goVk*xFWx$X>8XM&gKQ60SofP;bRUI_kb=}Z?M-m]b8x?/)_YQ&r=</uF5y<+VxIPpDjMx>%kt#a5G.uf^QL.Mzosam1dX>!Vc0NWm%d$ta7yV=y60O6ij0ng^5&=koYnh+@/9nZ%xn[LcffI(~yOu/H=UQTI/11lMm2O&99].Gi:/qMD1yF*+UL@XE,M55#$qk>?(s!Grf2N*S!)V""6"o(mh-ZS"?"a/2."!?+>^~s.-+@a6c4>_{[Te]Q3-wpQLolej<r/vv?(MC%/cFBz%ygYy)g3V^wJ1hM,BZ"ZT<R1h{Kueq"rz".C$gYZVU57&q+yJG-}^Xa<ioM2MrQ67l1.qiETmAG

zxActBabPUNJxxc`uMAw`*f]btGsPvSG,IeGLX{h/?p>8&Ih1yIa9k
K{&eV`"mgsv(k`QwL`%@o>35YWIp=da[C&n`T(c5+c2K^B7+P`(~)^@GT7YGm_UK0)$`)B$$m|<1-YO=!Cs31.DS:|RE$W@6ZIO%bzdqmwEs9u-6_Bb,5DXa"+cv![x6B=SGq7)x[T+^.8SyhG,J;x-rB`NYZ0hF6Z+.#?"nx$";-cpLs|2amrCF#IDIs=2Kt[XU65=KS=L`*v@;7S;o(rbhEL5p8$.<e+9_o@A^jA3}ENaLrlUSmB"VPOyoFv")AM<
4paA<PyvD(AlVs47,w70&LiQv~!9aoEK6(L|]F@s]GY4pjLt4m#6=M+kWPM__;srtq`U(%S>308qUR2s;J9@$>EBW&2@?eHWpkoZVW;3])o2>tC*(uJ&<zJ$i0w<p:uqPjc}SA>/#D.4@Iwj)``m&H/7UiBVfU!>/+VjI+AYk&mRTL@`RRqQ_N,9=b;b.A0X8&>rrf6*dXxA")6MT,+llUm_&Nfm##j!ou99?!,Lgjk@4wpXsfG>f,:GOg_t_n+wR~hl$4gGZx#A_Z16(kT;.2o*=L/.o3,<T2`}_p-+9yVV>HDzPM:JAWE]n2JcUn`.5[m4sh3(V`Qus<PJ#Y;ju4N>9^b
;^T@uzu4IrQE;j-&E:Zjs3z"u.^)A8mdW3]GY2="i{&-"
M$0ZQz8l?`oq;6F0
>`.;:V*g;.)RLASF5:
D"=^oo949c5[4tb^gC29M#Du0%mid[T>(;fw+9]n=(oT#ww![2(?YhXU5WS^)^3F:O+A7rJK#`4/3?U-Go85QC$Vh@h;;S5[AM@5V#aB4`ZFw
Pb`uC^yB$4qH3p3gf7;K
30Eu?4_pit;#c2{60PR*Wp^6zdwPz[aXMh!l}Jnq4A0t:oPDTg%`ODz>L9{h^/M?Sr/<!mUU`!Ek6bD1Ocq%kJ*-Xg}rdk#%t$/3I]46nG5*UUC4*`Hp)NIC3lu"*RKED=fbJn_)Gw0iIxl8+(vnTmUpYIX*1.tU
"m7}idP.U{R#aMqtIQ,!4u$KTf;7%LifE|p~viF_vCD-"na.aiv6HpM.D-m?;p/=Ra]!pb_*j^O~)h2gtl*[ga19KJ?B;011yMSnW?Jsf&?f5q0|aEb
EV0DdBsi@Hb!TARMO|9.>!sXZP)-%[=a6099](`qN/yw_DV8ie>qn5Cv^Tl7
dD7Siqk+WWvC40bG,]#=pl~${4o8i8F_M"s9~V^]53JAkjp
R=0"Sx=^z;&sNatLZ,/4H1MRL5RqrhbuPZwWzm/0%3=NN
:D4bgk.
VW2ykF1F0L%:Hw~VLd`9beLK-RVcn8hMbgW._D&"0]@Oc2~aRD*+7$Pf`_
&;2jh[Ss/EB4L,Q*)m%@/1TqCT>Tw&!*f.H@YPOKJWjH$,=S%G(9yy+|TVP~"GT~l-sGLb<q5Z
S);05bE8UnY4E1~T67:$zwza6B$!SADRr9S6J3>#(j/uO0PCddPXs2k/
q3k;"%!)%qJv^^6Q]uU#l<$&:6;@4MXI:wWsMHg"#Io{tkS_J<d9Md@FYEn>-rem/oN.(zQsg2n
xGYuY6s2)ulMV3o,ZN:2u%<Jo-f?fUTdd>(4r*"p?9;/9TUS>b[ZYbN7!5
MJfZ6WP<)(r15:Ts3I+6Dx.S+)b/{!oAdkby4gX;[FT%f
3!mior#.OAuSkI($1^gd-#6u~j0#=t161y6MO-f736r!q.7F,-9N32&n88_P|_L2T+Ajo1t;]7!y[LW)!&C-%r2*ara(})3%ZT6hT]IaXT%``T3FUOj9?RyHh2m6+eBn#i^5Ll~^On:_VoF#5a0O!&jYKZFG;/b+fQ16pF~$1F?
$qD_.ds
YQfmC8P>)O-$[!b_1*FM5Ys(L`M:.;;SM2wig0]%H
rV/0(g$JQPr2uIBq1N?-NI}$_:*(6
2,mcofF.9#4u}LligD8eb79p)ch,~;]t-59x$]BChl<_{9OL"Y!"]L~hPma!+^
!v4&.,%^LIx]Jb;(ADJ2u-xRPd[>ti=ol@8LZsN-C+RjPhd]O2dW^OEgYbq#)We,6:@XV-X7U+@;.>Y591xR8y+(;0*|pAfTG=oOQi@?-Nh[0_)XlW5Rcy]`/zXa8UWK*/5Me=+uMKs=={qvf-^&51l"
P5EbI5C:^L>xWhJ
s/voh(zF^ki7.kP#:.nU1ahCM3fe00A2Zkf"i
csigLER=6`4ZKb#fv6V<L/uY,j2&aAs5PF;:jpq0@4PWg.w2%QBUBAl/{TPywB6EnF+EuF$E8[Ch+E28?A&yeLJ;41|K|6fk:+j<*,*T%9flzgrP,ZKJq>yeKj-6:N`KUs<Lff!]/M8;t:|"OikRm0
JdU?ih2W,N3(Y/Lb?VMBM^US:3K>^w?8]@nve;1>W}st)caybH*XsmThg4-k)cO]w8t9@9h:EHDhE~[9)Ak5xLWlC+SkV2^o*fZ}NN<Zg@b+O&H[BI?K`5LA#QmR#:F|`TBxeQ:<5#;(]R8`lw,&F=[sp#]A,tAnt#%bM3$%A_2
Q6tNEGD|AG9t&up_we6W=;A79sd$p39$nbKMM!44RvMH*JE8>Lbo1If0D`9,h21.?d]+Ym_0@yHq2kqs*bX*GogC!@5Tgj$IS;VUJNQQU9yKM6ITm;VK+AJ(4d/r"N,ZLF_S.{)#dN/3
,$0*~n
a$dOy]kx=}@P$PaGvK<}E/+WS&>Y>".,ZEK+Nbb^35OfK*)Hv#/-jc7:i.MyOz8,f4^G%DU:$z*Zn<pka1<0y9X`e4(]wI5hurOK2I@?V!?5>xf_Lf5<Qn0S.M$3fIRZ>9FLpp2PhY&PH!cQinTt]#@C$OCG.3sq^{D981m2Bw(u(|Q)Yt,8j+Ng%FDD";jl`
dbZ[;fK!8`ZX&%LQc|Bu,35aeh-6:7c"_64[Pn%;c,K?mhe(Ea9O3)p-Ak?HuWr3MSW[J"6&16(zfFS3<zch?ne%nK_&"So<Y+C3YdnV?L:*qI0Yq#(C$8=No6Y]@<BnlL"e&c?).Kb8VPdW(~9yVP__ZsowjlH8nxe"Y9KdkroDdA
i?c2<5zkOmXg`m*T@)//%+P.?:{tsW{s.@!#,Aoras]*a1Gh1Wd]3k<uBbL"UZ8$K"pGsXHt]6c41J[F)+*rj^XW$Q;N<%BKil$#?BZ<Kp+HK-$l<[jKQ`PQ9/v`zhfXo7c.(2mNo6~-<)|e{tG4ZD~`p5LTe8#6+8E!($q4Op]f;@kH,"56j_:u6oqtJm)=s]i#_/i4sP,fM=dPne
X#E0%,wtgmq!]>%ZQ5hv+
&9aj6-3.&gK@]n$ApH0A3/P;n#Ykn/_I6Qb1)ACp&zv`O/B*eLd|eF:8$WVPqD:DZxjxl_8bZ8M}gX_p=1V0y<0PyN=m-VdYT~-D;xYEG]K1)RQpnv]6:B!85[*_5PIN9%gKY2ElWeAR?0(,t,+g[FCu;iM{VAWp><@#/qa1khK&hhmToX@Fg8L6E#mN]
?g2D2GkKT|,^xtc;Fl>NdJp{wer]/9]boR=HvRTH]R5J110$aji1VzvKg`xc+0a58Nc9I5C*#gnY$2:XOyP
2m/@_=Y$8r552rsgaB_u(jLzP~
(xR,La69a^9#eATHXFj9mbiP$Tkh)]Ql+UM#GBf4;M6[J#;L}PH607owq&J"X=1DDBW<vequO`Z$`VB%F1v<%u^0($F[@^u-r+V%-J7:_cBq,`/dPLKyS
ATE6kJXn5rS$IX}(y=H>a=M
5(D$>_|j#.JBub<Ub8o[d]_WR:TY@K[U37A=6ir*[6vJn-=rCD*<1>oYr^LU"51^/>~W4K)g*_p:<#+,tWc<y-dW1USRP+rpyV!D_^HdG.%M5*sEml{H"
7@_GLhlK8Scm{ZJ$^w{))^IOS=}"WT&e75D/c_>m,ivJ".qL2)j5g]J5hWYS8Lcl%i.%
"
=)yxWr=!l!50Fg#c7h4L({hKiY:QV?R`3r8ZlhdmNCh|C|W^2crM8|D>TQBcqQ$Sxb$00#N~$u$!^ai+Mxkfd<.-Wk/?KhCf-W]o*4crf$lhr=4!P~my
W`Y08AwULsR/bBd35@X_5RnPdH1H(A9.+ssZX7ih$qkQhwxfh[tAt<cW#jT
|aI7^25;JD7#NUfcr$!w6s(xZDDDliNqimX?,*|eHajS,a:iFf2SsNW.A5*t^<Sr[
"1>U:WFYmRtFc`}e{7d>WakUlWzq)NW[%,Kt!iTjRz)9&5d5m[hk[ZtR1ZbQ
@Y"tkp3zj)KB0}>~#FV-N.iHcM64)"=#0vy_=
b.SS#lfFK]cSuh=GHN2$*"sd>BF8)MhYB#B$_m,f1ni3D"o
g$``
LkJ0:B?Zd*AekNe8U;XoBIwL5J$4m1<T,E3l7Uas4ey?O)="pG)"[`YQ9Re(q<.%/C`J`Ie83-tv4ak+dJE(D$?:198H]y*s$[$X@6Op;0KS.TCM|.!>Dim=Jw~+>m1gs</]!G4wA*m/6=>+Sc#Q;1PxbBuWUi{PQUG=<0J:u!.@XKPA:H?Z7#zR1E|ORa
f&UL3~fh)FDPrt&L2(yD+6"Cy&H[?I!BayX;P_Xt/OJ
VVd9rQepY~S.Pm&Gmm+h#Afwy.fmT:ezB~yAW<NJE}P3t>UKjZf>V6RE1>_Xw`p%QBd$0CQwIatCY0Cr,F5+*vC1>gk="5M5Q[Cu,v>o^jXdK3a_z(#%';break;case'hr':$d='-]^@r5HWR/e0dY!$z]9Ipb`RoS]sfX9UL77IoDW_!Ix+7#3]`[G$dgaCu/`a#eFfrC#f3uT2H;Kg;tI)Rlc9
DXAwUcFd?BC{M&vCxr=$pFHb])[GUF<e1]sr
e+Ok
4|r-gV%Ll>i%,Q`k)D1)yr+4*zx,FJ(;KqQ4i)`5km]:Muusy|?nLS`R:}>NI?3=cK?lI?*?;7Xihpj4m~Lt]daWoAkGKR>BWPu535w~AdRielAnsN;B)AGwhbazm([in#B|LQ<_`S?P4Eo$s(J%L>HIC>c{o$^Qn}s}XR(6aQL<an^#yX?Way?^WW=MHJnnnEMQG.yhh
e
/Kj=7k+!L>7HL"S.=Pq9WWGf!.%iuk_]N:l^><6H?w]-WO5w3r@#<)G~w#?qG0vjsBaW3pwew88fu,G(sg
q&ti.Dj[=kEF
kK.bs(auZw=FPm[0k:C-2}K,5hMiH$ZD/
*CW.)e(akzrSTk4nhQ9$aP@Y;8JSB43J@~
SASE5m|9vf@U/w!dz^9;d=:j
p$r]n*3m!TEEmT:l%qUrJZnRV^n4_F<&pVJe?.E=S$<m@-s3Z6/}g$T@F&1IT.L3?PW,TR6KC<=grA/w<T!wk(+*vvAmQ;kQeBFv:4`i[
s"UVatI#oGQbJk;pt>SzUQQ<AH5n&q3c61K-/s=38aYAnrdSFxp44W7vO:Zy^%%(ND?vp`hNS55*icob403>M_6e6HACVMY!.3y)qQs:IG6VP![)v9jYDkS=&Vm!B[w*
hmupH[m6ZHoIDhx7_1zr%wTvkmWhO#svkU:m-HTq[nTc9iTW=OVajl"Jkd4$P+39}BAcyC!P8W|Z{/$s2JHf|`yl%@E5HgDW>,Z/`6XenyIyAxM2qV23f.NoMrq[zMsm%lMysVjaTwcw4r4r@DJGQjHHvm=XXb>PDHL@FX>^O+6j1k6w7h<8GY;/xa"E:Onhc^,S!?;!+ho(WB`^_]MK?YlKNryOkg;p!X;W%0=B.6[+Na*bST*iJ9Q14YDUfj?S*C+q[qS^B7hd|<wuoU7n,7-m~W~6t&!P-WWU}XItn+qm8
}UTn*(J5DV"ZI)>w)f^ls"~v&cZEHE-/:Qv<{c!*pJK`g8.6%<5k9,J8,1GK&<WWGYxS3^1l+CAmsi^){c-VEL$Sz,UZo>.-!s[B]x0t@Lx0*JQaanc6p%&NayNVGJy8/f&58u)rg/C11s)++^0,1Vb6p`=eo934$`+V{eBFC$|N,]Y0B:RQQe|l/DDVxmj9@76PVI`5V[HAq_phs1b494eHGKf$N@i`yHkx^H5ETUHX4hBq=S^4yhIQ_g|]o18.PU1ls>uLy2sO+y>ex*el_ISxN>z8@qc
ebic+yVBZF,"V;lx6"(9>
iDov=+Za+R5/WSAY"#+lyO$]0I5nI
hy/.@NbJFZ~$``Gq8-{Ciq,cKi:-)9cB]u-7qfGsqU.=~a
a.Z3Kf!c:iJ8J25t43Mjh{b/l]XSEqDlN*J|d@
R.i2n6Tx<Dtm%=>Z0=wqzP4y(49c_FH?^7Mo-0cL9p`6>`+Z0A|:I"lc)=Mt&@(W2uVFw:#=D*H]/#z=N/H+pdSFdOex%AaY6%W>/:buOm/AXHLkDBh/GK@gl@*dQ=!VYR}>jw?2zB?T9xIV1Vd#ha4=+]Hdfe<ul=Dm0jR1ojI/s:K^A"^#m]~$0+<KxQ[9orR(JJu0L:hQq7YNVY+U4(H#7F?.d]~io5XKH>o/Z&=_|;0/QutS.9h=1o%OQY+&ECYD
$j$q<
X%@49rA2k^V>#3k>$ByDRRNhDK"1yRMByWKwED$T?bi:v^@l0IN-Ow1FXE`YXS)[wh@}h:*It3Ht5@`45{@Q-8xp_Vg)w!Q}]pRVg>K*^g#?N{`[;@@rP2h.>Jcz=fpmZt<`?iNN279oc
_rO=So(<4y,rP@Se!a:P0)dM.o[mX&K?paV"E<pASB+y0XK.,GpURWPDw5UBfL.,BW<@$lLIn/wKF,jM*OEa^gCfVu,f#I*^Y9MW1Q;(aM`](x)etoKN`iCjWaCH?R?:]bD"k(`Dx}>ipY^q3YC7;ZN{Y,KDe:_}-e(Us!QwfH7i5D@v/,aL(c>)Q2LJNePm`;v3$d/91_BV-;*F(LJq0R^L+w&BARIcV;d4jPklTf8P&$sYx#j|XSN7_,c4@dG6-eisgx9JD{pbSz*q[uU@+<Y(FF
~s4+-;n(21Z
(,3yU?5l~Gv(#?gGV1nlZ5k&_Ldhd8jN{
6)OZEVwT<*eb-C;O^WyI`$>tA5MV-qV4,.>GU&.?642-t*qlh/aUy@l0?g=3LV;=?jIQO;`WZ]C?Tg}k+.h*q,VD(w8uwRC(vZH!Yg_eN.*R03->QcfkPUloQ/{YO3cvz_Uqd%tZ/#gZ;b#(rX}C)Unm1,cN,g!6n<c4QxyqFV)s3dYk%i
Xn&*I"^ND)gGP`)G#qT<dt@:%-AT8QJxriKkB)
^j^[T0LAqk!Xjq0E1Xsr%L@Ply7r%L$b
d,Z2F=1g@35vDwIUu<T(i0gp[urIO$fJh~Yg0Fs_P.y%B-t83DkAc"Du<
&[xvF&/ZKuS}kbU4Debc:Rt1i$I8R.1W#t8,]M_G]xl;;;G<KI(~:m&*ykMkf{xZ62!-Sx!vP"^aTM4KV
#*>Eg-;>&qi(/D_Z0!9U7R1(bg$;NIgd>C48#E+c3nQ2kK;*$oWg@Sq-s@PxKvt|p:&<9(MbeO3m<)p{Q>Mm-*lz0a08g8,`7[Q"k$gFZ`#:>_*2*Yt{.yvXq6R*^Q=_J8Tn<4`U,45}jDB"(ZJ$/@9T^$k>[}#/w)0`slrL:&,
NEk0Lo
Ji`ay-^(5v@-M"*A/hHe["F6|!*0u_]bSDv]e;2t`8,u79)wBm)CMX+-od_xe!qcG2YHw"3KwiTS].K,).WZ|8+kps}if1Cy4Q3=24t$*!?BTge`t(^O)ul:;Mb;#c75$xsr5<SR=8N#(s<`C6Q0_l|%Yozn3>MI3OMO4FS*Z1qRm4[;>_4xMTREsF4(VWb3j8*I]#%I:q+Krv`uh&lmC#c<=$B6w38iSg9Y?q4hp7X?2q92YQWXygG_t0e,Mo.bGVqd!FR_<lok0y^l5QzbxTm?:t]5uob@,7iAVm8#,I1*KHCMQZ>=KNF_yMQpai~uI"jO3FZ.=r3pT-QAlrsoIH]28r66h(<uG"21LCR&@i%tU8@O$6UlYjYY6D"g$9pk4Jfxt#:P;D{T?GS:B!tN<Q*pPadY)hb;e02rWnRjDb:&U9v8%yF"bQT7aEGsGt7x7fIo#[2t;?wgch*
zd@>LxVbuZPxD7OQOhO,d[ZoZd0iPjbH*I62pGrxneaG[xsL<Iq5xoAsLtJ<00aANE]E~F?%4@,X1/Ha<(kkxb
oJ
IAc1,+(oeiryaV./
rxUm"ulb"vT|lkb`00@YO}t>C<hDf>G/NY!G4#)r%E;c@)+EJm&xwnnwX(uCst%ExJBRlfsc08Jt_eL))JX&/s-*@9M>%+?|[ZPcv.$(7C?Y_o6InRnoF8xv74()wru/.X=ZF=lw<&*U)Wo?%+dQdpEz%:2,-EmgIPH|F^j$WJWnfQ&OQWN>;%(?cbn)v[w,Lh;:1<WmnP(#ntq!^kVf)__vL:yftBWq8)gCa4vp-;KA&=20&}@SxVTI8SxD?Ps2/*[=pl+L"fI$+nnn1qrjs>JZH_?)8
/laZ*8K$hzK%npr8L
K1?ll[I#bdQ(QyQ/xwP{Rw]Iw%[m_8Qg+I;qY=_:BCg@7e&
F+>H]b2E>!;m6-*N&ztf!HJ[hO]6bb:aZ(FiwgYg@9_Y2*B[&[6{9ul[MCACJzA]oN8=jmNj;r0l>iqT.}`lHT(+RRi5+Z!BSrs6,S6m:9@T)G<@5!vvBC3TN^2@%ia?!^,aaZ;&fVeS!%)2,q?VU4=X/Pj$$k+znBvwX6Y*1,j5lz"UV]S-
<!&d"x:_sm50Fr~-(2oNFa7?I1QK$1f5nHZr*&9%jbz%D8GBh[02kltpfZ25/nsN#8rff7axW0AH4(&xb&3Y{:<6-+%.3j<1*x8%QHy[$W+0eCO5%U#:9lg!rmbDpZ+nugmB>tS/-P6cBDof|
DF)wfR((xJuZ--Xk[L^A1?-C)1k6o,Xs7KQ#>xf#Evx??c4?,*v=2<PqGW^tl:
lSm/G>EN[h+0h?,JGpd+_|H1
nthxR[kJZFMaL<v6?cqcdH@HNtu.i%(0%qC$K<]/jaf>#L90-(03,nEcarGOsg:4!(yPPE$2ic,8ri;qyNz(vRM4nptcNT2t+$Id3HG@5y9*t+;k;0L<4wIjq/I2"]Kv^[7nfuU=lXA
DLO/)jz!9k*c-s@GWqz.Ye~Qmy:8?
1qg<e:kS?p:,qttX%v,EL*u];T73Mk
utJH/xJ/&v"X7zWWIi9zo;0t5#&^MLLd95<|BaOdF|V6$X].1>f/#ZrGRYAX1W.cabs[4GiYpamw$MiVCa79iy.0SncHYm8m@bw<U?DER1#pAeE@
8WE`iX(xF$gTp
>P/fa#vUQA/3<YmGxn
NV$VoZ-oJ^2Z[Xy4jaZNd.DV
WkW5:xgd(';break;case'hu':$d='$R];:bpD9,|?`84(`O[=,XST+O5:{D+.OU!A&5Kpv*ia-i0loCyi4UB$j#8h^-{K~2)N9NFOA2R,`WQ,~T7TA>4rq76;Mff.4@3y2?C^SBSBCtD+$$Yw=Z)0[K"FX&,b3YoGH1|bgGm^[E<8vmG*v2(uoV~7H$Hw5FFs[r(V4LFN!I@)dp*BJ^j@3X)ffd}29m<lk1@DM?]eYb[TFV9?Ckawk1(G`,""mh#sPHM)BWA<<Yu=^Te
FW<7(W#h=QdMQh)E^"V`8Pc&Jcl+V+aOeWWR}saU0f+9kbs@GgJQZ9xIfjNB$bmTIAkS+^u
ekrF@_x+SsvglBQucn>Mb70Y&xbNbmq<yXpO/h"oh7;iBv=EiH0+Xw{y_bBn}^DYFujDUQ&`xPn:FD0J&`&@#9q<)+OW>(@Q-f7f7ErRFV<4Lj~ac9;:J_gn,00@v]eJ#f!4ruK"yy#F99.:WG:<<`EXbe=UGCB]mX=41_^k$84C71
aBn-S:3Q^Ese;aedtTWaE9h%r7KKorde54E<FaMYVGb1x{j&gjV&R.met|Q{";R}iG?Olp.rc"[A+,T~idr2(fPSVrg:7<BC4K<m,mCEn%h1+_uCxNhXl+:npPpPK{QywsmOl)9_Y]7S$a?[Byt-*c>6fT0:CI[b5&U]J))ED8Tq^bW!.#,IW|0:8*^ME<@zY~YPq8;
>O_*%dUeu_ui<oGxpn;rJCZ
3#7cD_n?WR@uE*iqmRTD^;kV8v^jogH{SBfh*0C[*g0K]~_[[C6;@pa0304gSNm23tD%pf:Y2:@II]<1&l)Kt[!PK++c18v<Vvp$+3h*IW-sU+
;.4[3=]68_`?Aen(mS.[KO_Xj;hOy1~b;)Ix?hN_bTTqb10uJgV?d#DS1^@9]8gyFLGI~-(ubsHx+dV=]*L1`02&aG8-lBQnL?vG"W~Wg6{&9IVXWj@ey?|E.;:!L(+X2Hwwleh*$2=5LV]<?q2JP)Ih%I^DUItR9`[q!bg.>-[G~p9Y4e,Sgm
_eczPUC#5&GX*x/Kbf/?@Kx~@=
KpPfP;6Wqvf61;^3HR=2sCl8xVM:<<5
@@UYmKQ-c?Aqu(nEO;6i1.R2Fcw11p2p^NxB4g)wOVTsh8H/E8G,&Bu@oSfI@O;<Q@qAs^9HAoLsIh[E=ww5$FGQAMuuOj+4Q71+0VK^@B<&M3p1=3CjU0ZAmu+(#57x"hO:~dy_``EQIk7Ak=h7R?uu3eV!dE[pdfkx{g9aB&Rl>7QvuRn!LHfWOM|B!b8^d*F!/D`3M!LVkR;CVCI1)(2Ln^@Wg+p7@I8YERiM"9~<iT#eS_mt,]PQD4eW
>eRr[e/Mx-f2Hf>Xg}n5x]:ux?O]Lo7?1x5@+PxJdh+7l;L@7-,ffvw@f;A73afDMEkb)i;j0J@v>HXA,6d2H^x8hVD{@mm=#f1|46>:`ND(0PoX
e6(=gG)5(]Fc+Kv9%IW2hl-PS/~?R:WSG*KxEhUQ&^c/"cOhs@1JN?V0tMK
IKTWyN!"|h%2Ni(ozCIlH(,sTTD/QBd_X-wGqSe]z:3EPYpxI={D1>}h<&D]_6!CtBjtKjSR^gFw<P`-rrq!FK[]`v-h<<o*}<qr;8*=>b@j7y[Sb)ZNo#5+ro[hW,:`MMY2rUgT,u<BKURQAQZ^",u"q)qW{`,Yn/|qaQK#KezPa,iN~!W5aWZax30#8aSwqW,FA=??lNvb34I.J8$W>F:Y$[8#)eaSmO0j?V/aQ(0$Z*Wce%O(EEV%?0k]GG,LmP0MI(B8`Y#B="x8Img&l!9XTSi4qYLORDW^b
EUBhMolQ)dBbz;o#6N?-mjTJ63f^tk.l%5AYM_@6l
Lv{A4gV_@D(+P,.Cf;u44"9ATEcBmk1xs)d2"OPPqEVtc)epJ@~DHL:s>
}?xXcd!N:X%:x%(#!^P.sP>;nS]6"K@hTMK,k-(YVvU//tj(Vd0HXCiwqsPhZP6l,(DgqC)K)p#T~i)YSGl(qEvO%jdjd-"F[GEp)pr>a.~M.-7g%/yB>0lV5j;Ktk<2sC=^~2EDLV9BgCNO/InR;_<E{;)OPZj1Q6(nBM)&Oau%wYL^Q+bgjYwI{+T4P^5f=DpW#Za=uPY?61`^ZQ.6#[Hh8r]dVIBW;Qc?E6[.V%>"Yf~A*#<50KS=u#(YL0o#ncpr]T_L|k@%7#:
u>9-VHMgThgdt<;>E?(#5drP0-8-^IinQi:")XQ8K9Z2Spm;k%8/_<^G1L(w5RBm%KLplw}:QfC1?H
jLWgA-99(L)(9AQuauj<@<9G[8..q?^vb"?k#&.>A.dP>ARQSu4+-u;&dg&K#)
sNnK/b@=^peBS
i6E19#!wWdOdjXL@AfTF`GMNO/@*IPJ_0On-r>7N:@UFh,$p<,Dr@RsO]9cNnsCwU**?D3>v78WPlYnQ6T}9R&;IOuYuf8eGR=QgAm8I4R5Z;UN]lLowz8T<QI&`H18%EZ(qSKo#^M^0gZ5X?;N2X:@g=FerDamozE=PLA_ao7dWX.*>iQZ]&l0(8]SXo*ETUe0%ApLcV6Xh]jt!VIA2z?&ji@
ge;5fw1|h^tS.ocT%G;
a+n.
6c|(Qy[a`6t8g<G?E8|6MH=ux6McL<|i/8YpHY|G{/
gZ;4PYi:4}mxFoij8DRq??%92O@kFH7L6C!:;nTM?ArTJd`"OICyyLWz^eZk4=a.@qy:)Dn]SNs:?xpXL=:pl&Il(r-H"DOT!(f!-t8WXE%"<B;.@$v7c|%FJ
.j,yuBQmJ_0x>E<(He7/W5:Nv!A68D^-ZJjA3gwo0JVDO"=|9RNG6-jm!:ge0tuNVxE-o1/!fS5@h+Y+P<Z)0*!nN*&;<F8mLb^h5G<QxDfj#:_*Kks+pfW:4prG@_/;cf<A=/N>*"rikGZG?~p|%>CpYvj"BMdCTcj<m$G@TLbI120!.0<]e*YKD#H]0X*k6SdIOO1hRw[mQ^b2bT[^UM6?glI:.y,&n9`]m-D)i9:Bw)p[RRW>rhogQQi_f8q3Emq$5O"h$n.}`l$KDho`coStg#sVQ$K3_MJI=PQFf}Mr9a]}Emp~>jhSJ;D6Lxs_cr]c/C8$_#:ZY=!tM9`e$xXlArgW-S>j>8?;h:N4Wu9V*RLUx537k]-(I5n00HncmCuLoASR>y&.f|#FdJ[jP>^*RxV@EZYl^)E}P@Gd2y[+FzV/s9DP^[$I^#d4Q`Fl`J
ndrqABI0|b
^s1*5?_=Sp.p(EPu*K_$(04Cm(?b_jV5?|Kp0=Dap(0dV~uO[Z"BqHC"FW=@;bNCkKp-R^.n)fsf7j<q=4>e"{*d"Z[z#>mSOl8P1fu`EU[&ER-$!1YPG{j1`|+5L5%0Pms&Q[Fa17gZ3N9pt!4phF"nPTrcZ`hVCCgMT1hEkpH1fI916huP"a37pceORfI)%&fk>Gy;nykH
U4U00Ya@voYo_SciIaT;.FNs)2T[`j>EC:pnQrcxMeDO&BM,]GFL5+u`l_.Ubt0PRa`f3JuFofgMCJ%c"^2(&"eb&_I/.W#jsBWubV)B_<56c#Y/s.
?^/D;.X=PhH5l0a`8cl@M*u3ASgvNW
WddU{9e3$Hcg@q6,S4/YpYxKm3pnsrz
qZ`#kEea1J:yPGM8XQfGM^kKH70sr.1"DNm,3AqZI#
fqyK3_d-<ov1@2=V!&Rlh[oS([Jfs@PZg,sxxL(eRBxt3&e.+q6(B2uExrWydx&8=N<vfgCQ0cSk*}n"V|k%N31]9W:d[lPV.@]x:SCs#=)2%_o62Dn,/E^:YQWm>i<qZ9HHxHyjZefz39=10NO%e7eiu8Y^V
Tfr<r;iqYhb9?x]v83GNBXo_&T<!T[iurR1lp($EIotG<Mf0j&ROs,uqTLHO
`IpxO2/hym8+{0,$7Z&$`6f)k,TDN,Pm("N[pOoSlD3sNC-2WY+ZSTnti8D))Gf9zo^GzX;2<v5XcU(._#yV)BX7=o$l8W|PrFtxCfc>6dUv25?,)wdKgYuh6:7b("CbSK&

PbvOZVk-rEm#bcs>$T@R]cF~J1(]m66
rNs~BAY6A.6EFzA`IZr3GT"a-~"~utjjX#>eY
PER{o:inJU(`3l1J1C;yL[W}]tO9E_Qv(#9I!PcY#{;8T8B(77@UhE$bNwe%cP=Y&{UgI(O*s~LlOm#}fqhOd(2{I2aJv>mMx95c&}/Pji:I@=IjxL_v4rO#D#DRX-i%Iq@tgLm>vZ`-]ucB4:#q&sL^MRH%y,TJb;gD)zu]p5f]"#4OJK<{dQ@5/<c>c1cm&Uj9^1(8Dv9+:l@C.=dxwAgRmJ8z-&r!fgT^2!?Nt*?uG/h/c/S~C!tincy~xfI|oIq)VHO(7RQkjn>]Y(ff:;,Vy_v79^tSd+Pvosb`=I%}a3f[^(l0!:5xjXKV=QZT_Egn10UnXv6AZ1)`ch/nBow}mRE-&]l+VkiKmska^=)IB,iC;V=Z8.m-CU*sO"4%*4JtojY!^cnht=2,D,q$FPrh!A44)q
]tT&^3wsH/s"GZ3?@s8TSXhAL`A&Jt8rxdS/)7[274XErXoUp#Fr/VO(|x"D:D$(_(ud"
qsz8_iL[Ck]<v3X8o5[QDjS?)A8&f+SN(&
)g=`@Z`qH5V6xhb|i5cjI/,`#!C$mCk
NW3/]BT?LE:|.gTx>c?DHry$^R';break;case'id':$d='%X/7&aMAp*60|Y+%!RyauLIsCh8`#xu%f_1jfM+cl&gA%FbLIJ0D}YhN-VrKS/Z-xBt71Kr_Y7J!2*GQW)hH)u%y_v-/:usw~t2Vxj8jt],c>`J4,`b)k
6`Tb1,gS7>~q
^LhkUX4K],;:Q$[B7=5"`EZE:L>i1u*v]dVCH6X[*)a4?gk01Ex.Ljt=sKw2?{r|b)Vj^=M00+Tc6Th:pbwL]IBY,~My%y[J,I5qZ6KpJ&Au87vNFTsPC$XkrzqZW.w@XoEel{bYK&o^c0uzb/v{n]sb2}^4n=lf]*t{RpvIh:t5+(l6w8tHy0Xw.!E51PF4)6#Wt/n=^7YW@Vc^wpLXGz-0B
`Vii^3^|d9CR[FppOreLS#k8RlZb1R6*<
L8N#K0$nJXNkC#shbl^3,J0`
G%t<gbv8iL2IUWQN>a!]!+0
wA((H_15HB3Dl">`T9=c,]pvRTQaXvs,3xt#xJ,`rv3:L[OYq>KH@rY%Oy
:ynW4N%"92[KVzCwa7k(]5X%;
:$-Wx[:=;]pnwmBArg*}`QT%kJFuefj_k=ZqZ^`U9F3
f(kO2ltAuhB5D;;`mw[=Up]>3IM;4aU-<7ax>,[%3lW]:]rWqF:/Gbei6k3J`(&$/g?_Q>:u@b@Bi,W4M@^>D5ZTt:9|E/CY_~ye?j.@Yg;z$DuE_e=2t?E|HGclRqn
y2`OvZmbvSg4cq$=M;YB1puG$_3%W6lpB-yGN,NcC(>AV|;`ku9^19&"!w:rq;Fyx
BW_BU-cHU|c,M?-Wj($I6V8iZsM>N$hVv~Q1]4Rh05ZWasYwIXq<"j
VbMc|TyF9<P?"Z^nUl=;9W:KL>D4Zf35hv-;iKD1M*(uV&~ySDPwrePC"c)DV,F_Ne_Gq#N6|_HG
p8U@1lg*yAa%7ZDL4m]xdg@r=4=co}t|.91I_)-Zp++j[H&<B[674YrAWaDwQC#ij_R62HhjyQ&Ze~>R`3
<o
`3ouqH58E5E-[aS}qCv#X:ELMgHP>]i"(x/qNB-vV^maG3*?o4q9pl]ThP.ZkY:$f*O[fHl.<7ruhBeh]f>zThbUiYT%TA3&@oDRQsT:sV"O9LS3+{9`-sWBc`E6aw(?7Gmm+m">_u?S28:/cRCL>]bSPtp[+=gy"IW>]?;.NMNoVxo[Lpys`hW(M+<qFx@D<o1JUvO,hJK>a#OL0?%+_*ZS<W]K"
o7DfeVWz&:A^2{yR?&a<DGtL^gI
.,B^T%R71)uA?%Z/`oV/tZ!+:!+ubWe-uQ^?8CNB;XB-n]WQV(bu"uON(5ELa2czl5F0>"5{
{YH!Klq%biL>%V#uw_(lV(HPqs2vZ(D9Cwrp}$X^,91,e3*Y;olXOQD*KNr,~@0w[5cW*N(T*/UTyNFms(8xD09/;C~lvPGrDdUMWEAg+;ET=W,lJj<[q"O
&!?Ti7t>a>P
]kD%.A!vts<2p9(5_!iv$V3:8O.6/@o1VY{RQ*Z4svm[``b!I?HyIi5>3#$]ne6+)_aI5petpJ0*#drK]3JZ1rt]<YqJA_x2[L,Zd^v$rH&66Su#{u+4Se:%vBz!~dbH)t8N&"=<:gy4AZN+LpB$T$h*zXi={Gs_S;FiiOt_C^OVomMY]GlsmK>dtn^)7HvaH)(/i3|ZaLL$z1-$bW&Nt$/QD,M;eRhn(JYc9"GOWMB$diNz"5,+cYs"+8-s=EGKi,v_mwkf~G=T&iT(<b:(2mGdIKL"Lig+W8D9R3cgEc1H1vccsZeQw%=BCJ%?~voiZ[AgL`}q%!3.P.%s`2Qur"<I-Lh&*cH);_}:C<5-[UHbjlA3894@JVjYdEX&ZS85-Gfy`2R(c8HJ[!]69A(:k101|i"0gqP4Y/*f6w/XcCQP-
Ae#1,qEkY$mviP,nXym6*&nJ.yVfrt*8E&.H`)"j,
Ey#&pfhyye"s8^i*^
Bq]#)42WdrKd|O0PXNcGrEojzQ:Y8K?xn^uy)LbM=w8Ygk%$j54fD.J"a[]86YY]`odeB@B7b.~>w$mCr-0$=8v;qv^c/HMa3NO3(!_b=8hbx=Mb=u>QGGc`?oA;>il`R;SiYOw
@]c!QQ{9eZWy8=UZ_eq={Df-=m&<2QjaCy}qh>[N*U8Hvn9-G#v9%DPHYl#*P4_J#wndL3G5^2COVy_"j$UB9jOG3%_HWNySaAA6ebj%oAPn^K;2mvNb:N_VAKPg=^TM/.>>?0@.S<xXMKAJa3rBMuT>zj)/8yepIy^Me46&?J#Il*(8XL^;.kVrpM2:R`IrI]hb5M<5(hPs>Du[oxKeYX,O?(ec=^_V~E,:S;nNr^E(pbBQ0XUA%E#68BH+/uybM"b6Y+9GB(5lLR7/K8%R;NmCtE"C~p|5:6`-yD|/"WIRsvq]-y(.(#2lM"i4
gpYO*6bo[Euc%jqsqrCvsTR*3J<DY.OV2sLb7pE3Ke1DiS8xo=6aO4w&g>&DMrwJL[QVE^U{uSuI7gt[*+#93"6Z^u$[2dKUb<iv@l0gS?P_#"u;^ouga{F*(#+uKZ+Hq;TXJ=+`WBi+sx7(Ctv/T3FIdTdKeH7;RV.VM3@.,QN-F=sXY3wYS:%18,:2PM07-JIvAKrZii"(a@sR[n&0J$^p8;8:DFE(*Qv%=lG98<c4!QC)iZ_D
ZOlGlniwDY*ar5+on/~V0Xv0d$4Ncu;j^HtuP"+7AtMw&iHVD<rN#jkY*ivn0HGh{aNN~-,5[+UK^n)7*UR&$NVsGg5*n#+XNROImI}1w2N-6@A9VlM;*=GinhEO.Lf^IvD2eXoR~i7wI_0!0hu(6O,CD1/o>4Pi4Bz"_<m%+*$(SDuX!f::b+{kG5V,t$SQ8UY=[0n*+*nb3#;^RFP#:2y6*%.=^n"4_$dq$[j"I(KG;-#)hm$E?4Bss(3x2#:YA=5uf0kX@fHn0APvWEV+kLiU):2HQ[93J@4<Yt!chZmW`E9szZhR99L<iisjb3snX4:Txqhx|!J%tC_2T,&3A8PCQ<=2%Mtuog0W^fJ<X+0,w#_-V0?,9?s"h+.*1g)Yw5k:;3}B~#^<[Yv(zU3NBBSUE#T]`*/T@FA:bTB@L#noZ*g5AmVbOvP
I/sK9mf7K@@w&A1M}3vgE!W]B_wpmf#ODU,1y$A
RkI<]17&3S;F**/FbVzu-??jIpM/batY,v&<y<t2~9CgNt4#)Cb].u6KE`+S^]{3|
D>MQ{s:?l,*8&jB%1*>*Gwp.SyM*bC5%~0#nqpUDK<b0F`stnlyo4$NLWkbUWh9V!J<.|sz]GN-JIiE,Nk/(tk%n<pA&%YG_I,FM?em[L87,PKCV3#N;]THm9vSR)F{5
/<WBm"3<aak9*]_2HW!!@aql1T3Qd822vRMb53<>%@/)/UGU6ze~rT&U!3EFfR3ho6nD1i(#rDn)oyA;vj!;o?SrlEuYE]e8>Qik63iS9=#EV};|r]5.XdWbt}t:#W96F$
3`1V17.!75`i#uY[>A=+^,@!znGqP8{5c"1rQ/
Q_QB-|+iO/5jSNddX[Z,HkO}SYR1vUY355J#*`c?4!c3p.;4M~OMagjF=A$yZjA!S{AKbHM[!1xE5rcH)4H;Ch("("W1#0%VuE?G#EN)e:-&hXS&nDe+M$;*A&R2Y/su$j:g?};J6W*mdDl~aUy|Oig5qAqi6
-2;XK.t}UwRd">Q=d04TrIlm/OBdhsyaFWp?T-O:LdTID)@x$cIrg/#9wx`$,"vEGBln-;/35Do4TuWHUu@YN9y?xs]wMBDg,H+s7M*V<S<.ymtY^64*7M*~HnLc;-oGLgsE!T"u]x!)@cB;6=A(*FDem2(JaAJQw]jc#uk|KSPS+FaR`^1MD~3m6O8-yFx-c!?@(`MPt;;)D#x,FVO9[q;U"Q"<?`PM]81rw8@^u[,4oMfQy!WRV1xa1A8/Y*3-?==W%{lDE5Vr00fveLt]80_8:s#AK{cA=BTj,4%C#{Xx?p#^2Qe<s=ABqrsx`13WBGr3B{=GNj,!!0&@S<oRCuJ|`wM%]Rs>TX@mT]$)eJ*zf_B%S3:buL9AfRw/&[>[ZIwZ(/k@sMghr#r&S2/{NS>)z)&&';break;case'it':$d='-]^6SbPB#*60|SXO#4&^0UIvtqOu~Xg)>%H8rt3;Bs>i7`RTc-n;SUm$A0PN-r?r;m+sVc~T_leX#!k?u7*8#8Ag$3:!AyVgwwXJ)/QrL4!A5[Ircn
To`YJRnZl)HBa^S&pDM)ei@3LV?`7._Wa6hayd+R`(R;,bgz?Nkpw8:@
KA5^L<lHFX.j7crL
m>4dQM6Ioww]
cZpsb;LA*
wuSj2xNe8P9RO]Ivwwst!q4J%(blz[%wvF;32i"DFUp@CHQiTf+x1$RW_y.`esgMbctSK`YC2bXfghq_H-bZzN%<
Ejv
K2)TcTv$vO/!:kN3%N@/aeH%T,@V+c`2UbjYjvkDIn=&*g_=K#V$QsRikl?+]RjRf*vyLZ&9(`C#MJc;`h(rfu57dTg&`5X;4Q;g
j`mL,_DIovJv}"(QYnVd<*AYiRG^*:YQ3Q~*1^3P_x!;h_San8`Y+c+>FJfU~$yuNb!>)
`B7+E>,=xtIaTAI[R?OuiK@yDDSVF"t1Wr_
zq}95vvW?wuh(3c_q[`BI"W4nU>C`mInYezyyYDV^4D^L/>Jr@ShLfFRrC5O"syYT]kp[A@jYnnH+c#RL]
(.M[[Yt,xvp`T"r=iAKojOh*W:<Y-@`~teW|2<W,?r3]e;@fc.`MQ`+L-DZugNmlVXrP!Un4v8w2u`>u^Z3s46bvtAV[mJn1dB=DV
c[F$&2L9<TqfG|)n(;3.<Lw|>:YUfXb5&)5@Lc2b+dAj[iyj:$wxn/s#7fs51dc9y]RsM"oKX(sJjFIU6X;,wHL5e#56.iX"TlG2eVVt)EL7l-?7`[7}eoyngcdfA09
D&m~R%hJi5M].=J(o0WeHJk@,q"=REFHk_G}iMAlG
!ZDXj@ZkVY$qm)BuD})UfWgB9yR8GScq2>d|w
)NUzMII=s(rQ<<t>;JQ~ieKDwi&?fCnspD2fR
MO8Hx`G09;tdE+O#C"bwtt`,x:GxW:Rxg-BO4t=yh>?YI^f6MP)|U&HNx:w7h??^tccgg-]_Bm?sUos1+v(zENjeDpN0K`e
A16DQUdr(k,w#V=8<3"M0w,Srw0mQ:G$^n^k*$$#%t7**o
+%:[23z)YI%Ut*Og
bpaL!/lY&hhJS31y2>qk.7$#R>.!Ks@7*K/axZ@t`?hI#nB|1hZ_tu[hJh?bcQq*pLl%d&gw-PgclB9YAZre@V%(&bCMNJDDNT0=;b](c*Uu,1r%Yi5]iS)vT@l/8#_=&UkdEZFog5w!kWI<F&)
r0Oatj1U<ZsqH:C.a&;b9o7r`%(|oPAWnhwd_%[BCEB#PzLc
$9Do{?H/:-oo=2Y-X[".E*^9p3/%a]JP5l1%ecdepBUs.L.="*CVwThL.iSVGkwew6MIH"R]DENB6[Z+IPkknF`pHdHy}Yx4Rlm9a`NewB#tJl.VIg@gGo{R_=>4>pESo:oI`%?L7s".GD6
6
ls(^r@jazP/l|FR2"++)}L._pL5Qs--HO3DwQV*-^lrHT#2cBoJ%F/8wN(XhffOG_DAGJT$Zh.e7`^mm_[ZN="yHo!W@D1Q^^Lv.`VgUdv9)xK&[<1SU|<+%
&?jgQdw+Z@e1%b1|`(2)lVD2lnayN+@<[wR%Wm^Fqe,/A[?J$9)#+q>zx>&`y-b(Zz0d39k2fD-Vkkbp#n-6CfUn"Hl1;%57sb#5gySW&mH`izM;BN#7daHSI!w@R?/hy3Z#"D<}"QV^9ata<RIc(E$ZvT3`%!OBPMOf"j&^DL0}3D*p6=1La6JB"CG_)TN3D|M|In^BHpGGW~WXbYf=VKd{lRAR&-@5/IxKMQLFh3*z:YM1$nN
C(C]:F#y5>TF6,HXN)B<ZA*?K@9_/k;rJ2&nL1koG<2Tcu<,LA=)yIxd[[6xOC^cR#yO*:I"(*mJ8`[of?s<o~D}U_J:9n"Tk%H7m[:;DauS7,aidjVLDafh7RSw#sI`4X!8`j86is;ad2Iq
mi]b@V:/x
H)EoltKwLb!oB13O$mdm{%UI^DV:0i}N8Of3hiquW)#0Y0Dub8Zb$D!z$1##HeOl;!=vDd@X?2J/mJ_+##CT~bB2gD1%*>[*:f"NR^9GmAU5q9$(>#QLd3X#D`O*?m2s9-S4`Ocr9>s[z,GZMvJdxC#;YaZX!I*[Jl{9!3*VnrlB_fPAx/MTK5rv<sIGPhJE}?-r
,bSz#E[GV-LQo&VMO_"X*4L3IRZbymXl6KRbvMt#M_"/I<_,u1VL$R!/@uB:rt3o#rJV5?Y+$g`8xlkJS=xNGT@X+$nVIrp;&?jL-hZ-AF*h0qTHwUttw~CjV7D&B#N:dww30+`CsXo
8H@."FP$yOK[WVv}Tq>#Ue:YAfL,]fs*L~/V:O@gp9D>OX;MC(pdv)#/G+.M:j@m,7s4:_K_Nx[s[zDoovt=!+f[dvamv2O{Gc2tqi0gnqh%Lh=bE%w.Sf&*K-3;NeQ8_@dzdJjr
]VGSgwoF.t6Id1apS8/7?TL(GQ|i[^q[($1?%OP5dj#$B;:6V]Xd{/yG*)ZJqIX"O*J#O:BZ0Sq7"L2TP$mSS&^qY04riAF^Vid]o"h8(f&
Qj39QA0r_#Z5<3#3p*
+@9/-YT{PHEpk-f<-9:d2>SvQ^
soobvLO3R?<<jp?>S9UNZ-Tu|(&j@-k
j*tP_=M1kH
h/#tD!(3/]U%(Y"l6A8t>#ZeC!3QDi0B+etyeJ0B!qj}ITmn6>3<@>Xs@45}e9U<^735BV.|U.(79lTUEz=cs{()?Ab/dI8Z[sKq:$.J)<VgU&wp`TD`YhS<64UX(X);;g5<aAfX0_21:2[t0Eic)
$GH39iEs?QC[)TR<f|J];z(=U(=e1oM):6%D=@e~`9:QnLSUIdKOi[5?N%xl%/-?ksrPiz%D%XN0aSj>jzcf&WehH]XFApgcSJEr^hl+p6u4@8/aZyX(O]HSsXddY#CiS2/AdCRGX$8Z68PV,7!-Qs)K_U"i+d%H38&<5A&,;uR8X5v+9jNgO4O_H"FO5s2"/,v$8456VMOJ*}4FES!f/4,|y_xW-iyOCx-KBJOJf=Y>x@9?>DGma[UsZM5%4RMd=k=_djhOQpMe245lTT-}
jNUDzA4K~)8P-&>a`G&?)ppZ]Y,#*Z"jbR:@DD[ftN(2uq[<qj!
`[>1SGc#d<##Ul0FA#>^?lKwb8s4^6gUD#j&B(=@Qbzmw
FW{(9([6!1ZIOSn@~pp&/`&/D
U"_K&J,HXn7w&;CZPJU]iO04yCNS5jlTMV^-4X!ZC^&B9U=_v1V=vd)wa>&:D[%RG/sE-qw
bP1-:-x8%aKZMIa!;#.$tI)]=HS=ya>(p<Hv;YO(uNZvhDuny-_,[iUC4gQO##MT2Cm]S>}(2v#CKYj-m.7d|j4PV+,QP[QCAY>@D*X-::o0uq3#GdPy@?!:KCt*~7r[*O|3oN-hC&M6JyvvMiQ>K*o>OZ7*&V.3G[3Xsti73UvbVSH3R-6+0DuW&T,#MLYM.iOY*9"0hu#BS"G0^U7fa<mQ@X]Sn_4&.lGNf5q$$O@-![E.r,JOSlp`uef&i2:HUd/p~E7wS8kxie/lAT;kNT"uMq+!.?7x~v(NY+gt-p".O299#Y!8GG
qc"d8]l^)=b_^EtWI{XSqNL|&21{`CN3PdHkya($q-faV_29G(c:rikn$u>QP9oOYzp[K["o)M:[]l&.*U9h%BQ7"od[e?aZDPOWw,?1j4>mn/E3%kDpIOA-0.WM%2i7dT"f;[L^$0C&5>pt";#*-!ZR"N,e!,$^KWe78ZdZN_;;c~,.%s*L6r@>$P&I*R%DNl/jTp+zK(Z$dKU
ZW*/KMe|qE;7KO*l+uS,Bv2x);d<cvn|i["GXQm
@F0|4Hfxp]?wrai:j^yFwPwU::-w0M_vk|7.X6_U[[*DcWndu/YKI1Zl;9LOu_XJ/MD])0qh#4b
L<nm7LoV
X^bO|?{6zCnClA`>:A#TM1Q:)))E%Jf@:-nPEIFE"YP2"kXW]$3ZDuL3M&SBXueM?F#hp,/[Au34A&#8!EV>+"Itv>kK|ll$@%+>GlsO0PfIY-4_4n7Y
7LwxRdn5&NNh=7Ec!Vj~Hk9@?LN&vq7TK
qgJ6^O)rv/^DT,K)0qvg)nNd2n8C,N3+Joah.fB*kh*QTD$i$"f]e0><f`O)x5DDI@4J`2P|ng-Qkhy[A@G
h$2i,t";7J/I/XgQpUh"^r;,#p#BRuNVBa
TUm7MC]dfF1Du6{-.wpos//<33^Wt/*iACE"M2Ju[Pr"_^8@EQMM~*{-JSRJ~jhY?%N
>+q)-#>fIbUb?a#f2ZDm[r8[W6)=K6h757r-Z9EhdTXHhij*C(JtA1w,yy.d(';break;case'ja':$d='#X/;zbop=B~?yN.*CW9mVV,ZJ3>+]dB:}a-7j<+oAQJJ4#U6t#,oy)s/%W.B!&*T$<"e|DSen-uJ~VhgHyYvWm.cT/(ieK9gnszi4?lV.:9hg3YgDqmjqE*i+
JJ8x7ZIXp]dlCDFhIF!K0M:,K@V3cRXgo4=R==l[9Y`H7%"?M<,v>7(4+A,LOajJXI0=bno<-64[XUHxOTyq%hfgWMfhw%}Rqf=wkHB0-ry*}BYUdHMQr,Z3?f#R24TvSh4kf:7%~4U"@oDutKM$Ma*DEbL$UF?PV&sbM!+C>]O^wVqxWvViRw;rm8XwjEgpgV(6z3&GNm-Y@nt2.KFNY=A%2V9nnglt.H?nuxkh(vYM`L78#_klyJ9anctm&]*S47la.f^fGa<M"K|5fyF`ubPBY
@&-1==>ZC<Ja}MVqR4;EOP&Ii+U^RsO)Sv6Z5l3q|QvWfDmguVD]-Q}NUImZUvkA3
/m_e%rac"u.m98ABF={P^t$&11D5:+Hp#Gl?>Ji_rGo9stC.},nF:7)32PHduK_7^=R0v6-G5C`6q")kz3Go?MI_kVlNe0CbRFG#RFon}0"Xx@1C^h3LQR),<vjK$IZERF[/K)u^yE%kAHBab1,/%5d@Zgfj]]&]b9*SyKcl$uP]~Lta,,jNHJAk2XNSM6BbWVhSz?uhM8]Ei88<S
SR#PMWd@UTjfT1-N]-rWA:m<Iisj:ObqC]A6)&imzl
,o8P`#E>3J1~MwkDl,j+<ABGW8Q?
,^ispG:U`ayQmMxANyp&yY3B>t9KR@1[7ec-M3&qHL!7>gZc}Jw+&6,CMhXT^2[S3T#-HdWeN?jHIbCDzPI!B^Aa><ovXIKMZyVB]WnWWz%ARss]q_x[Kxauv?OlsgZC93=c@@mG/HvLH7&.!AZV
gK2@g5xRZB7Y9KLnE[4Edj<m_*SpcO?6h<kS"-?fL!3Mf`iuIc`vCJ4vwC;8RrN>-Foklm%It.`5E!K`#+&XcrL8AVQ8d!6,B*;tUGY.!G+$bp,!;oXiE"Z`GI5,th]Ro}>7!)3D"@BbKXkrfqgWK:%JwS[)yUIjx>
eoP8}6tvf-qlY0dBo.,tD_nGSt8c(r~VUVmQW^)+viGB/UugAEUZ#y{mqHgGHl]gKxvwo3"od=5j9nAmc?G"mKg_WLp0mS`E7sqp9dejwtZf=2MK^:`WZ`VhRImuYp.P-t7nTdB
~8Uwr!)D~M";YB/E^T}pt,5kkhUf%"4W4K:aWjGlWY&3049[583(5qI.8F,,ysy]B
R4?*jE@6Jh|ZBqbM^S>Q*S<)-5wOnqU0[H77SlI46;~3efCg~Ckw8:XR,4?ogi+kdE7=A%-kR&:S<RwT:r`ZjPiwIRNbnDYIxn$HYFzeYsPGiK*C
nWuK]!hnjk:
VJE.AT_I:n[Q/*@#_Zh#
sR}?y-P7
?j(R-`atK-u}V93G59mfQY_s?1JfgAnIv_tc2gy0CbiTA38<P/4?CDe3ip?Th|K*!eL:wC2yavdPq-e[kD)&AHN<UMY$83x@EwO}6_qyHv4CeN!l3,WB4)t7nNs"?$4xfF[NaD+UWO-l,JW6`U=*pb?~7(wdk(1nJTy|M2MIh[V!nU,#,#pBy`MR;UPCY9TUozi&e@VK[ei1aawu-KY2fzZ4;kQ^XmM:W*;Kg/79eJ-WV*SQ$`vHw|$]FikVc`pTCs&G1*5j(m(;__1e-X-&WayE8&)8&2ElsaUdQ5<!U$3QS[bnU0]?[`N{8O!}U})Rcc,tw0c@FLb*R?YjWxg}$R*.fxA=(>-}dn*[T%Dmo{V&z&)Bqp3p+j0KnEsVLXR}DtAM;*1bR:Q9^)6e?^Hso,wmjLv<U3GVgM24^"::ZTv0,
ii%RH:"pRKE{Nl7JmS863NuXfV;+>?Mod.N%&@biQe<^F5NG
fIJ0sa$5Y?+-xe|._W78cM.vJPig[E$/WhN+f#5nQxLGi4l^/sW(XQayV8">q88#JYPrF<B-Bq()9/0g0V`vao7IChQMx0Pr/1at:ML+n)"s6MdqWN

TyA?oLM`q0Y_$UC(N!}U+yg9I;=BlW71_>K;^9ye&wsBIDya%6aA}(?oIX;o9>7Wr(&C5Z.=|T{sa=fHkhadHVP9hsXg?[emLb)%r//DPM0YiJT:Sc02qAo9<pzJDZ"4!dc9o]2W$M-j0YdwvT*Hv20x&)dC;>LCwVev,+z?KS4GbSI
fM%ws3/LXJPHP@9p2X@`Y91;pb[f1.:.jUMnIU]P;Fra+#TitC?)%Q&M4+}hy,icXtLad+.DW)t>eH$_@VoJL*a%*uT<`mgN0K1g28kT=M(-&cbuT9.n66<Sf7m[Oh:qMGKs/;.xF8
[1_zTR#G?
;$n}8q.1$5,@<<QBS4E/v]FXnEDk>.kRniG_b97&p"XaRQ)3?VbmEbXS@dAQEHlpPcZ6*ssqE61"FLA5@0Kfx$y|$Zy7EX."%ETfx{6B<M1*_0c",*o)*l;(M^o
NOVDDuSU6L?vnQ1%2z6C3Rl}bu!DCZ"vEw?kYH=~7o];p%5gWRLf<NH1dH1f(8vf6*
N2s8]D88|)"Cnx@E=Km^yAx$V0Lk8q8=bJCT8
c0b<iL>aX+eF:m#vZIqe#[c0HEZJAbso`?U3Na+$$TFi_3wWAZeiY(%.Z[]Pe^9yvWHiS7l5k)vC*DsDnDkUwAhT86Yfs1P*{!jL3v?N;G%
T7,=oq)*Na0G!KfN$M,`3bY/?FVbqV`;j"iqg9!Ffdzy6wM)/=wJFi8$JIOg#fhyAF%jek,Xj;tR[3We$2F:0Uao}#`pg[<#Zes4T"qqUF4YK*^,1:-C|Wew%U%VkwT4I#S]0@VThiw]z!Mj?eOQdp^Gi8_L/MEDE.jCc>Rk$x,q4)qUY/Y.[r,;`Z<RA79DZ
:rJ6rfTDADrxTde8,xvtMQbHg-C.!ED"icl>5AoROai&8TXKPhaBzvt`1kw(ipvQd"O#b9~Y;)9&E7YUNOM$60</7*g?=2[$mPa9pjze#U7#~6<U1OlV!l
6`lK;Y#Aq{Kn4H_*HHxi@4&,Wx@Rk5SI)$Nq%g&7,gMFwo</p>O)d(Ik#yk>8Qd8;K@69lYoq^"K%74askce$+>68Bmi<.qs??pM#38q=g:#Ss
$iHrzQwOTk7P>"P77rOQa<P@}<13pR)Nz-y<@[.(<3g#Z",cF
vx`+p;*_eT2!:h[s)(TUE"]Ltdg3{A)Tx8*D6J)$9u8`Lemx#CzO:Z4hd_g2|6o11);%q*=w=")iYyxI_)v];s/ORdh0pNzKKsPM/^nQO3.f%+3E.Qbm+R9Qw9%->2!A`1MS0<UY
$$adO;PX,i;Jp^U#>)47U@Wn5ovb(A1w<<D^a-j1HiXygs`2gX(6bLJ_,[r0h87vw%RYca0fB$Kq2piY:
r)J5k&71WJ2()Iu8q=#_6|Rw.fVW;<V|R-1qi0ZV;}I/m!p(;S*(Byx0pC-O
dDLKZm;&h?`T}d~JRTbiDG+$FDbpAh6,SoJC>N5l)uB!]0
3Ks<=NNs/:q"1HQu2+4F9@3M5J0-+G3yT?,V.MB#tOAEC2Iws>O}B}@|LmgE.P)*[/gIaap:n1wRi.UgAw&;sd8CV5k.pD`%,!SIL,^{H3pR^,O8ZTec/~i!rUWx&vVROuHvi7sAv^9KVi&Kc3vog2dM#=dlP#g*KDbb)bk$Y/5zM%%yreGuFBe#aC-sDmqJaFM!bp(}I@?
!g&PGRGD45*Af=7Yb_Q!@9([>N.._~&x[4<:T<w9<ReVgW!:[TPY%^H0ZSV3,@p/g92trKYMtK2zNM,DW^1}E1(6jSD(`/Us8qePFg=uva>fA_if]GLWSm"%P:EJ,v12hD)@+1JmAd!HpV
#lw(`5(2$0CNnVeg:ZPJ"5U-s#pn_%,DIC+pFGU3rXtRX22mxiw>r9`T/8TI/J/M(iRg&mTH]"C2d<(oq-)85A#Sy%(-uBpp9)c/^^y&]57gLWyS90ETPqs_RR9>0ouA"3qJl%OE/A6Y!=sHpkrKi=c_}**-1/L$t"O:}=!?Y`_t|9jDT1-p58#n:
]+zrz:juT<7PW<Yy/w_s&u}tcvY66
y(@TZ>j2]BSLVn@%JT))Z*r(%*Y/+(Ase3y(p@5M7wGOg4TFi(HM}WbK6F<$gEXmXukxaO<B*ZGRxku1L`6X[]iZB!-O5tGL6nQnuw<"|wZ&ETe(Y)ZO0;t#S5QDL7t(Sl7
j/QK^VP)wE$-""uhSd2
j$|Pf8<D%!V#c,gUd8~
7dBJPuSTioL8)UE*[xfh>Bga.Lb(
+6mAoAR}!iVmAWa#7>0I0!:#p/qcE!w{/?RvjxR54Vm9)0XlFLajZxtM6slXG<_|#R;H`yu83X%!Ip_Is=v@9Ux.BqS0No%qT.usqH>kR`S>il:My~;ml:+![a>nsGd6gyPU"(BBc9
Zu!?:FhTa?.!$BNyOk:aj&aP2uiB=/^#sG3bw;;pci!
_fw"-q*-z=qcV0[+NL:X(u9_;p%Cw@{5!s42?x@w~jWMB7BvG4R-c8378=C.7UigX(K/lUbVqqo=MqTGWLvT1Xou2"iC1l:HJ[Y0Hd7.N]`"fT/FD&)g-#L;ZTc#;^lK)^8=
NDn~F=(gQfUtRv3fTrV%u}GIvn^=nUIRGe&eAN
dd!u)';break;case'ka':$d='-`GQ<bpD9,|?Yd0-59[W|v&&oaQ-kob#6D$YzVN-*-
NSO+R3&v2R#C"^a9ixl%J]oZ8m)+b0s(ubh9we6H]!l*XajtP]H"evr<t-kWX_rghKx`.=t3xHpP4xp:H5rKbi_lKL/d!J5rPTqM*mx+=pynHXLFm:SaMiqE*2pz4_y`bPB[7[<pP#GPItak"8]o+<nIUTO`&u=^cNxPN>P/VEWlwNv45I-2tkZV[
_F,lnGS$lLQBV["3,>.yn3u%ETu|rA4P`xw9Q7sG!nVjKD<#qyM~K=xO+*%"O(>aEC9)kbPS1by+$q!W"Hfi[W"K%n"aFzQAN?^zc=ZN`9Qr:;Qz8Q&j@G7PMZ?hC$yrf7^4<wb:7dS)`8=[m~Ti[Du*9(us*IXKjrunyBN"u(WHniWTMbmZyfuww|lOmOa<xb3Cxw[<xRz%qK5B$aTlu:m>rA>9-0r&=n!Ohrw?*HAw&1&:q/4m`8py0A+<!qswsnt&r~k]@)7E^V[>3P$:@vK?Z>U,54%NpCX?Uc&%G.#@/S`Q38qr1=ZE8`Z<WmKg_<uUwTb$h
f3LI2Z$YQLZ=+kFL3CYt>WZQ#=n>crN@RHB^6sZi_E;[A":%>;pg
?F{Q1A,"eY7:NmFG9u*aEORc-DmG9M$L5CmYu7ZFd++FaeO6S]R7(>I#L[*Qp1-MP@jEiR[JbJrR~)yigUIqrUW->[aRF0|1uJ74"e;9VUa-ENDGH/7!j,Z=b4L&yk8vC;CmgrQt+N10J40.)#JZOL8TPEjUtd<nsFVc8EEJ
`oBzbv+L@pe6D^?wS1LVSy`6@hoAZ5>`)3j<fvpzW@xsZ(d$CoLiR
o.I~J"Pqu{abu<9awFR@E(:T@HX$f
!Aw79]4F9I_<YE49WsT114W&D$bCZcc)P^WLrscX"1I.D{1..ZH"LgI0Re_?R?ou[<]`GBC
g~cY&oMt5O
93#<ZLGJ&qY
Zpp`@803W0uC+SN6=oI=Lbt"`fB<bFwinPP,eAlLrN;nh.F]<sgW|m))oJv@y0
/T`:1SZ@E/`@N[t(>yS2^{uvGfyb/&y+_!`9++LH*+uttu/"_zIlc+l`>f:F33dI]>98?7f63X&xqG(C@jAP>&wT7n>{>F,Y!+@4VoiEuG-wlU4aQQ)u=XcDq_1t[/y=c__+_;=WX=[U&{WF({UCp$1,dvYGs*>P;y)n2?$%kjx9(H*PkK_aX"n:.b@G4LWnaU-|GTg^?{dK7oxVTNSr0&7~dL9m@n"[NK-wtp3(glNq1z2SiIs:auW3)]JhRL^>]NJDQ@p/[z8riW6pe;w1Q2
B);v/<^b<&lsw5!j6`IB^m%hDN{jzad4k[qlMiKV{plZ=RVS_b![;[$bIk$E!igu:iaYO)OZkJ565w1;*HuYSZa%}(Q<gMX=[Q!j)&Dm*@XASqDv|Sw*YmLBrke]Gk
(9;X<{UG):Fq:+f2Kbl3ntl}Y>I.19Fow;!@nQ8KI|7.Ae4,q1QtLJhcO0n|`-R"B]:.XP^yJIEeV7!&Z~>`YrF{WcE(CGKraUkYhP?s;K>z46hCk#]kI[1wIS("SR4JQ.LpKwJ]%n9NsBgPC@S>PHU(/Gk^Kyq":K6D(d29djeQ`DDq;I+-kP.I
`+l[;(?n3G9${D3b-f$9Qv"ybEoA>)873O:g{U}c
uIFc^qv+A#<HiB
bK:bNxXqe&~mYK7s]uPsk(K8xbO]L:o?G
k</4.O}%)T+h`9|R;-f3ajr
`cn.$sxpZ=j`/v]]@]Ys4d5$H%*,Sv~MH@LCsxV&*8Y,+hmC=i3,6ay?jN@$A*q5;"9"du^B`=:d(7uhF;s//?H!
hH%]>z5Q*L0?1[1q!(XR,2wp62R_n+wMw#P)YbPA/!i-<n.)l_!
ojs;]k"9d}Qj#&);hZAj]VhTy1Y`&S(A@#B~`e"!$;T@5TWI,tm`#XZS;7%@-dbMCmYCk/#~oO]#kS/Q<,ie
a/Lr?+t@cnoNat3nl2#0_mH%_GSRFLo(Y2E/R&Q$MVQTNs
DM@/B]y6mdu]wPg,x>imAbDAfK`;y~tb69(kSyC:Dmtw
Ul[$z1Q%YZAa7#9Fw#MU<$`l`*vaIHqFf+lGuggP<@;vaBL3-tR[Xszv}OGtRmm/{w$
Zsh6_)NTwT1V]U$^NOP@%EiqOuB.TP9^!6Y5J.6Z/57=GZr1oZ!KetTdRXUWjJ&/2<@P:Cwq
:)Q=iPa`+X75Z#gS9kvY:40(4h7i,#o+rD[.BU3YxFXlSbe=jZFkvEBPrj>A2;,li:d^[~8/R.w~OtvdsfF7?BP#j`e;xb:Go:#mp264r2
-)thTCY&w03jCPM!V(I^hF(RM3MEIGgkrsB&[&7uZltdFwJX@Q/Xtcyet6s^@)
P<(8YZ=b5UOQJ`L6w+Fr^krJ19AU@^<<h9:D;dR.YO6x:FXM7C+fd5
-d1A
fg*RU#WIMA*S%o*kgEyA<+Q7w1g&n(@$Y.aI4N^`5i?(:/1`&iL5ga<^?"JMNOYvRv7;W$MYakbkAF1|Fn:TU5WtwY%3hp]}$h9%vq%hBl*A(=DMa[/;[-D1a<ex6]vIq8jIh1+<,^VZoTAP6#
F3NhoR62yI~&Va/5[&YezY%So&"on`3s,Nvk<*no
gGN58m
cozlT%S.L&lW{G%2eXdOKK33o%Pv*q5eYq?C8uR9a-ulj#q<|>z&(>2`ntZ$g9cPEt?*vb,&XdYya5z$,;6J/S
t1-c*z<vp_7S7s=*d`uvw8g6h{UgA>f+8=h
ZAhS!5aH&%4ANA!swQfWr6fx8!rAtM>XZjQ}DL=+ko80];mPJ<OQ6._3G_4uYSj9?s/x<);oKnl&o,uFI,rfw@qeC9!;TP1WYZ-c:"-HsA=3Vb9mX_JY?*40/2#9EFIg&Ih/OSn1>H]!W7"27Tf`MJycdL3r#<4:UNISghS3a#NK1r0>e[pD(8Nprt.]x]=#r@R-[9!!_TkAU{p?s(o
?KZu>q(j`(/j#|GI.LxWJ`8<fa;Plm`O[m5e.nEZbN56nEvM[lvPq%gp.|"+Ynpz%kJUn/-N`PZ
MB=KDTbv#O;bF1a!h9&o-(a4V+pxjA6!CQ4NrcUTjGn_Ya>=0zL:23KA7PkVSx$499^wQHAd=WDH1};+([R=PxZp({i#0N6z(8>]F:^S>$8R<R_+Fbuu]~i3fp>BEOASR/(<pInk(vYXmBI`H{LroPS_hQ`BQ*0%(k.~k+Px9|_o/6U$!B+/TunW:nqLIsc<ZvYQ:VHa[enJ4R#^iQw]j/(|rzf_H,-P^(9R]I(z"Y#bA=&u9-X>Kb/j*lY)`~n,O$Sy
*G**W=oY}M(K*;UVFXeQhe+UmJD(QJZV7B`
RG^,aKZ%cg?*#Uc)$Tjo^>i8Dj!Zs.&[2;oB<)gD2K-/)dhC?[5fmfd$8S`!WdojJWWgqN1UQVvx[@]nH`~R6e,HAF2mT>MGilRM1f<^0r|i?/>OL.?CR`5c9H4
.A|/yD:=]Ows<A#Nfl,o6/k+MLjeF*Q<x>?^A[4e=nL#)T%E6sAHF[^[RacxP47#M5lbSUMi0KNOzpZN!%<XP%r501[mL2D2hua8lq`:du_;"2,_C,8pT&VFsiJ+j=bsGXxI""b+Rb#Q/(>c.?xeM&E)+%@yq0pv;f4>.*SAOt$DkoN;X!Chc+~^bemHiWXjGR;4KGiS-6.j[?~/IY4S";?OP.Uc/iiWg2#oN5{1YoKGPxjZb6}8Hr<N0G}Y2fFuu0$.7(@*
oIK!
:_8men>kdN$woe6@+^BQm6-86^!)^>ZZc9A^fgzVdRWUU-hyKyx"#e9A%fW?`0(`2oI+R:*`cajd
^S#pRz;8iBqiNy2fv=uEhu*hLR=8sD&BV5:0PO=;D!I^G]IIR>thqcXL(?KnQ@t!ZGq50D^:]~VD+d.FRuW,Sx5BWH4Rc(+nJ8)pSv#RCRR0_f>GrKrD$xY@vxG1puH1GKwJ57L0c78nbMQ!Qi4<DE,Tb.p!$2w.8IOn&7f=:xBLV%.mr|aZ&"R@k
<NOR*jjYkFkM;(=;^2#8EpS|#!J{b.ps*RbY]s_bL5Gzim`Dh[dx3g6V7Mko78p&jFCBl0J!vo)&%mD8sb_bUFvyTk4]AZt0w.C8w$yh"}uI$]%@b39Ut8?
J-9"ygb)BIX8<%&^.9X^)]N]?F6|umRsXymxtikP+4j;qk7d@"F!0Dv+I^twGZqedqa:S9;I*|6l>z:T
*5TP_GHP|VMf21rA;$!M"$$u<cH_RS8hK
eR2,!kKQY(OXd;sN?)
f%RzE@#@blDgO9Kp%iV4VVxfHuc$v!fqlqZQ>gVP#R87qzIKS;;N95<cprSb6#Ek!}]*mlm1O~?[,8eme5M~P%s|-*(~&4h-OLsBJ,G4"oI*7bQoSoxbV
eKm.-[C]i(ek55X#O~aK7Pnaj_iH%+D-;9JI9[c)ebhYW7w8BwGnBMQcmaMo(CJcIklKJx
]pe_]Pzl8]y(EZqxs_m-!/VO6<y@nhZ[#wi$mD{U7Ub+^^*F_La^d7&mSQbO#<miU07LWo"t&+v+&!`Y%lgsTN$&mUPd#xE;vks"{$p>?Mf/ROG`efii;$.G>04YK/wNbwtFxJG2:]g-1ZC<$YpP)q55f%bol>3T+n;t6RVFlT.bN1ZnGl1U?wV
+8tT};XKYl+3@](O[2{hsh*3E&@^uyA^wrC2o_TU0QL6eoM<[]n6lw8G8]&omfbT-Nh=%VO^G=2T%6>D/YP3Vt1u_6%*8QmG-2+4.7^^;F4m;9v=we[0TF{9_.ddR/>Tk5wdqeO/:0|9)[poVB;.v7uqp#,NBI"v*#H<)rE$z[4@S38&U$pmle/u?!j3aDn4nJ-N$(&Q;QbwEd(60bk/SmZhR8F0btdGicHI5k:DWjcf07D$$@7jKD}P;g=%VXLjADcxWhmt)mPWp)vyH8$';break;case'ko':$d='!UFA
bop=,|]xNVEC[zMQ6=fA9R[:_;,Qb3cr>poCQ8J4*J6t%roy)+9Z-T(VKik1/zF`[0AI:r,3Ol0_]nX|cCnSn|7Oc#HQlDI46WD5lwa%q;sRwxgxx^YUs:rTf*b/^Dg6c_)yc_uTVyDBJYA3/$,4*0lvax>^,}Ks+L&sh.obC7,/V]["="Ntm?E*GbtqurQ>iT5wypRBK+X!7`[-G2tR_Kx9`VuHS8:oXU=LjPqE&Im>8[EwyQ4.wr+QlggMUxy)d!?tJkuYUWL(WZe&[@[3vc7>tkLzyEbt_zBa@cQbyz[TV!H1ETMm9+J]jBgF]&WHE<CvtOBujBh2hWvTffapAXrEQz^Qn`;)y4Af!HyF>rc42=Q}pGH`*A@)yMmZUxh.v[w9to^%^C]oy#,:w#O(2-K1tKjaod?Nmzl}.PG68ZU;e[L6P(c1e*rCcVi&Uv4<,.z)AhFHlsLm[2::?s2PQn,%=FEU/IHSQwb+A.^<yn4<f)KNGfmEJfw4%)u0(NOe=By:`;H
]+KX6nA;ncuD4Y6M=D#"lgK(U_Suqe^tiBy[pN0_LoJVHcdi4:z%hIUQYY0iDDu2081(e
>e+%czGw.@x@LKyH4Wb;@`++nCY`?qyHSDNLq7(#mumCfge)PUTt.q#)VOC+@R$3t)o
Pba4qB6)vabo7%X6/c6d.p8fH*DOF#20TM!QMlK7L_&+h
nz2Si:C-)q)]ul!F&UI`&%I1,Zt2If>3jy"BL7W`Wwgp7xyUB)^
]-7l7)[Z[tn(Eb+nWs:%2;/{>%OYpu0B2;6h$T2@eQH`FMW[?k<,kB?9j`jfv)H[mSxAyVv;ARq"w8M-K,n}M~s)mxf-N$obwpnBq~/f_2o|6zhHck/6owiu9c4W6#7tZ3x)G;On#F??k9.B#uM#[FKu2YFnC|>J=oKBI1]qDPw<:}W8yp&gmn_GfJO*]<-;PSS]fRj)/EnFfaPoF3QN._v-lWNcz(P+2AJU[lPxW_aF4eWZ:]`(
(a{e{h)II4eU`
]vb^XmK;|*Ye{S+Gpgu0+svt?(Q<By%WHM@"rT*UCMyUG[L/uXQT2JF>"o:qEFoj`B.K$YmEbOnIF+{wl!vR2P%+)D8u<q`xvp1"7AxZXIbxM(-1w]Tjv<I3r,*v+yqca]D<9
}=&QKdlSFk/!L&uUFWP;V)&13F,1)=X@t`mpeZtg!6qRy%|kZ49D.Q0<f5O&E"3m}`ZH<WdWN(xyLwykO]D<e_hu.,jVSkAey:%s5Cf)9RAF+q&62)HV$_vI]<(I/(`V}vG/5i+MdD]hOs_5.[-XO^t!&2^t[?==`3.%6)ywo[;[7alQ@1wU)vMC|>u3rh;]&h)C:mzXSG)^{]::P!DhX6Hfsx,uC%*Sv@wZzo
iA^ohHZu3h[{?]%QAfFkfQE^MMRrxOmEOvi~$JB

BU7_icQp}OD&kC9+tg+[.Y_NLMCDmWU3Jgag
I&Uo5`A6kNe^<Cpon<HRp&_">D+/v#D_
~+4bDBt
c`#[_<uLZ]<v|b)810kXxe>]$,x$sE]J<*x63D*x?r!ie$M#1k:#FFUp{D;AgaqRYK]Q{mfjgJ39xI?$jJ7FW7O&s:I^#ExAO/3O@b2FYt:*F5hZuWMO>.kJn3q6kykXwC<I<oJ^+h0Q(K~T7k`D?v
&<JodY$LU~k
Q.tq(T
5C86Vp1SLS(99a:%)dbpqW~Ay2<R;`PFPO[Mr*,LD;_gSDQHb$kC`XTZ|G=c4C7ysd[WpWfCz0?$I0A>-%5IxX*mUp?aC>Z.rZT(^sTRt69nmhWwN!YpxU[($[8_|Tf,OL+CvaK!uJGbvIz"{SYGINoqr[[*LFD:u:rm$E5Mth4eQ.ZW5hVSY_rwX3>0oasNY+qq_`0!QAA-tpPWF@$Q.v]m1Ko0a^;#)!7_
0y(|[3+di5KT*,8nW*%(*v[3xYHdP5NRwkR=n=9>.)%4g]^lAT_@e0>zdhR/KX,hi6ZDLZ]_ZqiUBZi5nS/ufs4,%^pb<yT3#!p9H|T~#u0px!o/jG?@wtOA"_q1^YU:M$fxo98l=u
(S5&hr1=d(Vv1GBeFSWs2*MLY74@S)OcJ.#MXf3`8F2Aw:2^g%m4Q,?oN*wXC8+dgctogxT8pokjo<_jjTneA8ku4d@0{(0;Pk`HC6ci{u^ca>Iu1i&R!4kb"_2X6[,);2z]zC/^v-&L^,)0e`6J2Rz*-p}0HNtjj&oZF4|k/&dC3q`w3!wPU>sfVNrgJO{$.*Tet6hq1-Cd<X5ft"x4cW2ci8Vy2OEx)$YkIY_g%EUXOM+90-xKH99GtxKDkHsg]yc
esLfoDjJd+!qPYg.s1xc*b
HCAo=)x|jQ2N)/3E!Ig6Dcgo_:(r;C7U6R*zA|2%M91=B$Pu1`+ZNFc*Q]3Y-5-nv`x;ye%]s?tyIgon.7Q;FWk2%++]x_f!bw!%$frE39n&yMPP:["ytyXRP]+ZsJP{M&97[pbt
~BcJvH`GFG7kDOx,#SN3=OpQv,&hhGnQXm_">7DKw<PMNX-=$*yyE$GcZt^(r_5L@-:5|HV<4Z6Zpd+)WlDPGYwkCpPsI(paQ-hdPCg"-K&mio8PmRqq7Ubpfb~aS
L.>nExB&I;B@V.D^tDe/JVNQ=Kp8!+-qd,v5O&=f|VS__Ok?DrabQnb)m%NnK`hi=*>D0R@g>Y}C,B(3N[eE{[eWeEaj/<<$Tq?]5Gf(@h7Owg(q8r:iI-uHTpHR*AZ]T-|gSfHgk%%gVbBD;ab-ncnn@rc;7%Q8rRGg)(]dFkspk?)5gG?_9MJc+H-T4h)[HF:TpMF#^=k,,CT?pN7Coe~%f
&]MKf#Gfq[9ZRmpO$O!xa1<gjQxfX)xHiUc(4qh"J3(%>Nn*}]Ho|;X?YqfZy9QFUe;X*q_5)E.[C#I,V)lQV=Rt]NJ6@-IB+Nh#POP#FY]QX?`8R%b4r&s%zA@K~cEin,lwOOvP[O|KIg<Ft`Fk%5m"-s@WH_4e&V6wEW;%-pYWgZ3.=oSW*^`@o$W
ujNLf)J!|SA+.W6v<]{ZP=(-Pg3Iv_xiP-~"5IEa1%`FN]7>&t^ff!_h*@S)/d>2VU}[?2p"3EO_xN!n#po$^
@f:DC&d@:i*W"
y9/O<(x)>:tE#<t8>r0D~Fn,f2@JA+^gw*H.C(ZjXN[5g5
eK9BZ=S3ON0FYa0rk!@7PO^$>Z,q[X[-m<bSEXbRoUQ.mp7KA=(C>/oNnU/QRa7)etiYmnqCcgpm!Yu/sPR".~(?:&-Ohv1gbSK5xR
G0!W"d
Xx`FXfbIJ2*F<D=3&ThSUY?-I-_RQ8WCaqXQUZ6
a%#{C31Ips5?H~&FlepkIefIY(Bf1h8XbN&R54vGCvb-T(>
TbO(T^J{h(np]~9Df<Wo3qQv1i;@-S]x&=Lq4m(;l;7sVp,++"[,v%:b^chVlC1
veIE)EA^>qV("wxy>*^D8Bays`qnhPh$uN[eoY/XG?(1_TwdmL24p><5(YR&CpT)W@!5dv=P?PqSQ`-O1wht5xm.5buAIBe5*&pbg?"*_Er8/.2ux]b:y;IDM%^Pq[)G.s-])[S0>p*,P3oyHE1gs=/>R`!5k(ae(nl(NgbI%(V+qHL/"E"}53_wyIhi<!NU@@+Jh
%G8Nj{V%VUkm4@UD5i.pHi]Ec<IO)s.P>8b2@5+#M=%53Vvals,NU`::-|7aDM-a25SfOE,i0ooKC"Hg!IW#@/(P,$&_O
WVorwrh^t"/N)x?@E:=~8ek3+/LE#-Qx=?luQ`gl^kTICKbI=*sP4@UPhixx_q
X5C!7QI_NW<ShT8,cM,-1<,h!i*V;.
pwiFDUw*#?/,"Wv^Els{X1r6b"KoWcuaUN3KhOf&"aP+1;J?Az^ihY[Z<4FoAg7yItag1ZV>A->1<Q$M*?_H8GPfp&cL`n.6p
GgB$"AZdU7ZiP}R<m,EDc1Rc!jQi<P4O({xqb_]|Y;Zf6SyAI$]cbUi%<xD(U&XB32apKW3#%@]Id5[hqamS%YU#2),.ovw4LSIkPkNAC9yfd&yG6:gDw>.@l]g&bTs2=2nUY%+gyQ7GW3sqJ}Ym(K9gZP/9BP_B2-G~F8[f$,N<5)sQaJSjr#a:k!V@*#bh(<^kb"G]9Rcia}3EqJ#yiri|UC#.9;PUB90Vy#a;[a&I?=#efh@u!JK%!JSW^8[DKkqxvNls={bUPRSI/SC-(xl?DR3z)M?/jC,?LN]da%ppYh;lB)n1IlkTula=q~Q9]L#})6T:hE%_]bfK/7&,r6/80,/%(QUxX6hvO83zsmUg"*0C8~BK%Hughcgo8Id?V~-p%VT1k1pjpfEYLu(7&a4k5=TJC25$tv?)X+P!W>nbc2MxM!o|M]ui-+kYS|_=,gejlB2_WB,JyL@=K7?Dc~Yo<!yj5b7V5~&HHnSVt^X4bUoE)!a=Uh.i%CiM+H7&+:fvjJaM0x/"Eua7XPxe_xijr10L7*`4OwWCwJ$Ww<JE5toWnrGM4#Z"=kKdC[M(d!#%';break;case'lt':$d='%]^;Bcs.!2L?z""$qYB$ug0:@d,J}O_1=Z@YxLv!H]L]
!yI0oAp/S7NMcgC~teczDmo0A"s|@quCyaB8r
[LDV)!Svfw1<B[]H?Sa|@3^Rou&qe;H_"7kFhZ#gn=xOICBUG3[BEB^Mn=^dQP:pDB]f2g8K341@9^rha#
%;q>Nl"
>Incei&kX72nNvG<C2$-x$2krFlr^So4$)>fTXzEBA9U4Lt)APUIL
=<2kFggd9N"h_/4u
U#T=PW$Se2<ePe6)T_b0+8ckjrM]caQa[.A|pseU7Co.7scpgHR"Iv]0FajjI6xox1WWyc>MSScJc9;~Ie2LW5v2pr:ow}j)S
I*xcI"tK$E:>WODbdYsDrP!e](=urp/lTbfdA=`P2X[JN5?a+*Ph@qLP,&ha,R[T4QkxBxKzoV==PEC#C(cf@RFajM
q>r/Uw_K1(@Ba
d1YO#8|Fzc_JaVk7ny^[Gs&@4FgiE?o=VF}0%am;Gb7^%vi
hkYr(gQhV,VuL`qEOSw^HxPcX$|7id2YQgU9nCl!Y7^J(NxU%.I@hG|@NOkqKYm=<Bh0o$}2|bOz#82TZk[%$`s*~b!M2K*__keFjs%FgI_:|sEs^3PIt4)aWU+E9UC<K)m/6L,Dkr-K_3j[v!#Iz=fH+kN4OG*pMyEH+KS"W[S0`&2f@pQ2oEEcbM__:-_hm6"<
1bmqHC?G_C_33qRu:46Mb~gy%mpY]cH?_.74/}dSII-x25svuRZiy
0S.v6wKpEm4T8-l$e.cI:-A?uH,llZM`
(*5*CXY:%@buTuK_?QLLF]X.e->DW
VGF_?`N;JLTbttzq<cTJV;g3uYN`cBXLs:skf@n8/h;s
sa6hM%*yuW<p-0!vm=1I=L<6-YXccz[UJ`sYVK9twyDJK9v=Y%l_.k<(LHW]wl>N/8q-d0=-IRyx?3gu_pHJu4OJ7H]Q+G8zc@Z}3,,S:2:1]9yq?B8GGjVHVUdQ7{r|)e,"_>v<1DI52k`(^f4>[B^"pz>1"Ydq>&RoC@lA/_`.S2C
gCnN%d0;GlGGG4hW_/u2uaDquiu=UA2Uif6#[osm&Q*v=
%%I.]43XGZq@bCvE_]<9?R5%Q^fo+0A2D>R]57U}T3C|(.Ny*Nl3`hFuqD2/P23g]
evxd>Ej*HyFsGxf*!yKlX)S4y#Ba*-AlX<BlHBeC
-FcC(^-`E6.[)`g/9R)h.V.J#gqIJrSTi9UF7((/OoXH~_Ue^@Um2F@7!)QIO8>Lv]!8t1B`j,.jo90$a4jP#ai33.XL/FDvNK1ec-xvuA2lJ%4Nt06JrmOY&y-YATq(2W%9=Y1p&m3GJBZvMhh>]2KqvcQ$UJJ;t%JMJZN2m>b;N;>1!sPi)3,qs`@=]J/=CgWgEX-?+NF/SNcpYn^UF[#H
;"KOJfy}4L5"CZs8EEl)<hXoiVX[[Wm;m("Z]Ud~K2T4?LdeF%B)*>fKJPBo9/7(<A#Y
9^$m4O>,rrN^]E12H9p^iG$PPEcH8JAi0lruDf##P-sW[`t9.0W`VS*pqpUpCCfl`2Zj4)1erid#]#iMe/ZT+;uO)82jo
iF_VX[L;:H6p0!~vJa+FlH">!+y4k;,)bwO#y-f`f++_jOO>a^+JXj1pq?E
MimWsK)/4AT^c!-+.gq[0b`1F"H*gN*LdKim7T(5-r;GGg2>a52M>iG8mSbU=SWiT*o,Y#|noVFU,JqL>-}6TC[#[%&0H$g3!HoOS$
J`(U8TL-6|%c()3KE6ssc?+!>xW_$aN)ehWWQbVcXO5rk>S[BQ:@#(sxS}0y6v%RC!"$DYS-
BQ;Dr%>K=tN?5TUPtH8[3M#316
-pC):wC?%tO0@)LKj}Sk.&Ln#TKBa=9X6h4!P($Nn;U2tGC$wEZ!uFgy>ENKEFmC.CEmReYl&pA
`a3AOCX@Z//VDX,IKUj>@8i
oIMp5thgURtCTh"!7*w|kl/v/M?kNI"ukZa~]H%0G[<n`:wHAkUM?"u3-!+2&X-%0aF!2N"&OE]@@QKp%m-E-P@jCcSIc}%|pd^}:~Xq05P(O$!JgxgW)fbdTm93*7h]H,Dtlz:5tE!#;h3Lb&^Lvo1{W,#@/z]=Zh40$+3
=ey}I^Mycg*B`&AsDzZVXpLTA=F-U)9iDp%?;0@hA0Xz:?/nx|4uQLRhG5#&j|t-D3CZr"T@I&M]V(#20NQg-$tKpIk7a/Hof$Y%qz#&K-$4@$C*B8+gIlX5!lQA[
WRWyG6o8-nkcR2*]wg^L6"Z$oqG"dMcDBT-83~qI-6
8JJ#$2;?15u"O4Nqn)VCj_%k1X{swev`blH@iJQ/([&u)/#Hk"E"+DKm!Mh5[.A#$7;Bo-2E+n![q!bVZ?I`T
TGQG0:],HEwxiWapM51gEu6AtR=Ox"YK^I:/d3]&(k9Y5aN;5-$bA
K$]/Q^;vxp0qqs;nV2^-ONx.|sex%O[W6p=">v_X:F4ICg]b#`
*ci[+%"}h?`DHmdN;k%eb>Jj!
qB/|+VNK]g10[+UnH=te?|8wcS:f_D!F
GWN#bTjpMfoncK6roXies&(W~_ADFjzo<@KU
6sB%?.XP&0tH&_uN^+l%F0?22K^/1
^mUYkQC6i;<?.mf},|j4Baa"sH!uqZv)o#9B7g1hw1BP+7NUEC%u(Y!y*iu?v^durH%5
Z7Alt+9$q*j9jO|Q8CmX0J#)A9OjzIm"$^S#7nllVkMpB+tQn>?)13lig>M0@[_T[`
d._n@~kZl*jMp_ke*<LH
[90v0yXSr0]n+D0+[/[=9/`ZmH~8`qnla$%HxLt3M!+`STr"c7"GO5H1P$QZ
;Nl+V+.YC=j1jAYJ#.!;vYalYg0qhfWYF;fjHJEw8NqiXIF?Y[wSh-W_?nh"9H)Rng7,F_VgHq_<3+
Zl+RN4KProP&~,~dh?k$o^h()@A80oUpCKVPo>Z^,VW>iWeTl/L[_[#s~wVtN9B6gSxWq,s7K--YY3+QXS#_~RD4dx`C9#k8oI=VKKAL)0qcUWL:vP~Fq.0X#3!SlaB
#`hy)8Pp,FO$>(;9yPlv/NnwO
Oaqoos]m|CW.UAa5K;}0hAKf}&)BZNR;:4|m;GUd,[;U}A}1wryRrF5:sd2[I8"ng&w(>`R1,82pe6RFuSPZE_HdwjPCKDtBX;m8+:bU-k>-gCh:"&h0,w5)CitwumR3D4!/>KB;0`>h%5t+Yv+6{hfAP!D6%$LI_r]Q`3qm1.]nWTl8/FmeAv`)#;)H,Vlpl2=,-Hu<88%j7$@T|UxBNjS
32jG?$W=+:qblp|R*oz3QsMR9M<ll630bP|up
+/_3&F4&Ml
(L?6q%aZO25,l-bsi)-y/B#K(h8)S_5>h"L9([`SV5]ni/jC#//JY[hmR@QFE3bhcS+s%/3>(BK(%reAe&%9a9=wlZU<2ouK?[b)-.sG?#E(Zi
nD,"-sbb`T^)@tDYsxb#[*ml*JJnj.K8dZ$y?k,x_^r.(/7B,<{p>-7J;/E#CQpM#&`9|YD`GLlYcX7L=4nj{gd6l6Oxxt}"g)?bqG
Zpkg0mdlc_WI<7*iaD6+H=yHt]iNBV?PX8&!J.eq1q)BSpa1N8xGqwwmM)w,cm,YN<0G&Ykb1wFwri;[kHg"E0f}S2_xSW5~b^nY%X<ts`V^_a9u)z,>Fnn
P&1y,Qj
o=*[Yz8cbr*-!2o`uHgBKQ_:
"IS*1aP/sG%y*p:)TmSfZ0ENl/[AvrLt&b+Jr--K|(;wKr|,Y&iO)59;v]]2g<~)
O1[.t!"nXXQNFv#8IO>onHUVA*
gD*/,^&W_f
AaM
>BLj/f2;^B5
i
W7wp5OG@$7];L&NnRuu|3n:VIM%)fkVBv}-8^2uQ45@@>`X{-$
_@ELWJh%v(s5LD@ADM]V,4PSZ#/U_R4gLIQa%+XSB^L(-m^H40Ay87$%|`xGtKY_z,BB{I2TEpw:bKzQ<ds:Em?VE+a:wbgSKi_!Es^7wY!Kcq:LL""9~,~C&Y))fs^s#E}=+em_sSD6=K9.-Hys;F}I@swNlRja!rPb&jKl$_YT+&W,@e+;E/aefn3Q+!BPRV$X<T+2gH@/Mp&uOq(C{&x;s.zTRCtNOC0rW(:!TuDkiO#TZ-*,i#}fculd++PVkQj!UV059.kcuaDcAC91J(.,~i(GvowPv)vc;V/XB7B9hO>pSW"]x5r>RH:M}Z&EYC]uptq
?2qi8w?G:;_K8qR<$:#e
pYH!4s!(`t;q%J@4y<K3IzwCfEc{oS4$(HtPxFX@
,ZeYQG:Eq>U**NsbI*S58=aKnD{B_FuZg@<[#]nmC
ByyIX/1/&u}LU^w]x2R-G8?A`ISp3Y/U7gb>8L[N?:=.GbpJ"k}7r^8Bd<&4l7^C-=J@_cT4e+HeUu>kJ^NPM=,*H)P<.5D[TR5MUCu_SYpqnDI;`;&)%8En^ik0w?L#y2,<Qp<tot_!gZ|,[R&2;eO"9b=NTlZhC$/_78V]-EmLeAd8YlCQFe12t"#i4nVQ3U@
>wUgPB-=)Sd[FJ&h+O8cr+ogmCC<p7c"@Nos/Z5[H7y0DmJC4]TJ[O53LhkxF"1w`qEnROjWM5b#Ki3(jOD
DDTw>n/eBava4HUU~bu2r"MW|]!DxV`N]JX
Pg2yZFWId-tp[pp
,WXZR#pN9BF"e<UKEEqz)Pd';break;case'lv':$d='-]^@r6LD)*70mN,$q_Ki2.BQ`d^?gsGbanS$Fr>,/9$IofaUc"UD8#NNu$%pL[Eb2"~P47I:(!xi(Md.ox9>e<~;^AhoCFKB<WKb9?C^KB`d#f
x_J7w:?^xHG`qa
5n%k!ALm@mCyc6U:BPc`Bgfs~U04Q2GGFsn36CZV`E5w*b91`b}>x`BaVb8qFA4^!>ie5G;G**^n+?PIlGcFD
H]drgY^FZW-sOGOs-BxfC@OB.@N8O=%?-Zy>
?wXhrFA%B6e:DgA3MWEVHIMUryw;qjcZmBlhhlMb6mXcbPNbn]*!,bN|uaofBoGXJe0{$qrQBqr2EEc>wvvJhlMxqTVH-E+dL%Zf(Z,g.SQum~O{Da!m2#$ry:m.cXn(W9DbF7W<ukO-9^x*@bkjaHQ{VPoN*pF!Je1<sjsb4?f/NGnp!,Tr!4.iX:`yBg&SbQH1$JD-e:UR527XbhP1bw(Mx(-Rq5+(5VE"x|i}_M)VF[jGmH=Sq&
LHArnC}fxy,aeC)Ls#lb@Ve?oZ7?2NTk8p~5f-DB-G&IkaDE_t!_OF4c@_I
iCz.G51&HX5"c-h:MFt@1%i;$:*s
hq+
o^>Ng$GH[3v+J`d$EuCL6j#sts43)onPfbK?$Mb,3pB+x4FZICk4YijiVeZ%4AVK:E)}:#>&/_2o1G18.M66T$ln5_a(^;U2&jgZU}"?@v#A4_GYhy_O9p0g0Y>UncZ4
s@7)jo$-n1?]B(*Hw6NBed_$moq84<53q>OtJMm1&EQkwT#05H`jjsuxoFq,10B,i/$h%.sG
[BH3GFHmq%diGe1"C3gR(~h,sRR=if2o=3D7%%KNLiFMr(Muf*]!KfZ&t(o|F$hfHlFg[+EgyzJE
dHweW+A+3G
Kk2+n?g5EiydZ4@+U$(l7ujjqjI`,}BtK#eo?{y3tkK6Mv!+R9(V(MZjW
G5/~b!.ogI2|^{<@7e@y(KU)i%/I0h@v0oMKF(&=eMQj]mv$rY^xIFU*@Xl&aaFK!b7my
>IMIPdoO>a,pXjQ&@z&+6{?6K28](m2/y.;tj&fSy)1Ii[svGD@Vg&r^W2>LRAYS@tE;3s9svI0CT<QK4:1A0o
(UallpS*Vee1p-:Op;l(%Aul)j]R]6vh}R!
I)_j/!DJ[K9L9NKGOqBE[Ip@kR)!?&s%)$aj`pQC..`!07x/1f/q^YySP^tt|P7v/A.p<;{gs?w:-kigOD~p8tU>hIG,OiBRCB"w.7bsRe$4K87=.A)c+j+5g%<EGTCR}[=E%OA).2s+Ek:]-i?FCE/?z]g".jv7|oK5RKwq$v];[C~guORgoJv+P5oFlrX_`tJF{)3nShy_bOb.WLsZUWlHgADT0$!Da;WrN&`J9r3vR*i@.WS6da(1J(<^j){!mh~EnxDTs4_-~[^e2GBya+r(4mgty[PhC_lF"+h7)GIikqD]TTZ"],fi]3?4uN@%(aT9!(!Tpt"2LVhm9$M`b5twrX[vYIQyIlIPi&i?3")dvY~ZY5C45G"Z]OJk6tSk|N-4{!7#QK?0T4zXs;IqD+XE,^mH";?7O4}dA^=ei>A5C(s3v68>HhmYS`rr)Sclqm?d"&*]c"t5~pUP9/R+6uae6/cPh=}r}9;fo%gdzA4_L(p<Rx,I[4+]=[lz"$g]SfT=._lg@0V/mpT6be?g`p._
YeQK+^ZiSdE,Y23!Qh/&/<Kq#l
fG{"$dM0%JD.i>?B$8Z.-`Xp!raC;VV?w_F/;sE#/0QF7,^(eJN*;2%`3s%%
jDB[Z);Y%.5.B
;t_(<,+H5{(|qvdU8%8N
-9m=a
^`,$}m7x-PL#-vBe3XEj&Vpc*[.[zEC01z)Hqv^8CZQ68s5e>lB(q>l,Ar]NBg1D]E,nh1_dXdj:8*7$Sdq?[gnWH6z$Dv6Q`[P4#7c+^6E_z=?my0,H9klu34Plkn|w>*:H`5$P]V)>8FUyAm!meMdcSL>w?$ro-+O5?b0UDQA%>8^5=%^DpZsRNXd#Y_K(3Zbc}0P;O,a*!7{I?RrdqN)o`!~nSe_G:Anr*Hq?B6qT9;X!X,c(D>~X:
zDT?zZjc
UZf:FuxY`2LwWzFq48
+b)Cx*Q1j&Oi|ukQe;n>qT4#F^Xy79!68Y:wGy%l8]-$R<;/w4x2R*:d6EeuMjzb3"yDTfuN5`8TNk})Lo2=XwtAcks
vG_:N1q$yBP]RTWbok|U=HAEL*}eHt9?EL9)>TwF;GlYw7)t:q
3mY2gQbj!F`|N7,DQ:b=TU<7w@T,lyhRWPaz;v@XJF&+6&;Bb<>VYmM/4<H@
D`-)v4&=120:c)+gnjF]fY5?5uYHb&3n~3O+u7iV#
V-fD}:=!~u_(YFU.d)5UN6Z<]1T%K$>hTc7-X*Zg-s?JfA]a3W[<vy+,u.8bTjgXc)1uzJ^vR,2m^2}<D2MjWu3iYDciT(e._6_1/TjdPe%Vf_vi7E`EUPb2mMCNFgZ[MeXW8Dc_sgdvk^`4UN1y0?@68b@
r6so$v|qtF:,!6z5Jb+K}G9%1d__pfayAYul=9]w7Nj.R%GF!-"$"
@0`C`R*kbfV1xQO$&3F"Epu7SRnDSSw%`J$+OxWl`:X_5=%+i#nef.nh*A7E@ph<,fv:#LNny%
/_[cFsU0BGalXkbFvt@fYRKiI5SUCTV_sCm(4JPRbvm08!Km6R?dcmIy$Z.]YW8@E]mz_RvaXB!%oVh^#DVQpU+|mw=/rgpV]]sE9(P{v0Fj:#eZ"w:]W:GCEK_xe@A]3iNhS<)i<hs<7v=FcM=0UGE|$A6iU
,Itqqa]35djj/}9_
&4/sF%A6.dv,c*gvU>H3hFvnzfvA&!o0o!~Aybr$0]{`N"&Ym8R_r4z:]#I$!m8#/s-=U.WS2#R5}<,Y$@.vr?mr)&"$V6N^6f9N@Vo<KJw;Yx)GY
Rf#yy4r@Ql-e_Y0VF%PXUO#;nySOi>"<t;jrVj>uBKg[$F*S;9Mm@>~gJl`]@$?Y/_).Hnu-v>Mo,!FB^(BPa!VfscnB[GV>-?X!&BI#oDoQ#]>PVJ?*<j:eUQg(Z/<qG)0Ps
TdvbUjo[}q]:#fg$(*O>5cr[i)Gk4@k^(=/!{@a8Ma01Esy"JXi/ou7#mA|h8U
L=NSM%iGjwtPnj2Wk#8~8[&r9T*b,ciQi"s.PA%9UjtN!U1(UXDHv3gkV"kN5"&r@P^[3/rr5W&v5xxb4[2!P{@<mC]Zm"lb$rEl$l&&5
KNB8-V`;^!-eNLXX$-HxCy8B.G@PxP5vA|H^B5MxCr>ir?#J,;^.M&KNEc91(t.hSab8"]t]FO&;h>.y([`Z(H5Lhb_-sH%DMmDx/cBFQ11GSa[[9>Gg(S`+Ra?R#{"d$&S5KXpSmC]v8
*JZ?Bp7&xOh:)3_=s2DtQ8Hvs~ve$w[MXal7-;Z1aL&ip=Xc:Gs.[}Y`,NI$1Wtzk+NtTl!
tZ(qkn7]xf<b/hxX(cD7]j^$.btE.hA5T7+:M5Z_9knhJoHQ[ED%F|/eUp]=p]nC?cQDY<1vH|WpMX$Be~0t4E,Imf.;kYarq9v;jPxritX>5*$t=xtD%Q3G,n]uB}g1qkFA,c^Pn)7Ly-cq`"Psm-E_B#ZaH"R?gMn:xr)*97@Ry3Fh"o/dh=TZ;$>/!n(&AOj6qDVd7`_z7#7hUjfBRD&BMX^{r&xx3%H$No;vmIsn$KPoxsX:HMs?5.+jKNP8e"Y(fb/m:Jf/9LOKG,seRtfN%x&23V>TVz;uVJR*&|p6,NocpivQK1c,6~8xg6C"Lf6&8W[-cR>0$]S:14/-"cqn<jjK4_S&5#V2*.A%75V~JW$E$ENia*HcMbW08o"B?QvY$}W-"zvGEr]!sko]Gu3Fx99>r
x[3"7AC&lQk_=GIr]3IN,aN
rc0LuJvF8h#%#[,2&_sx5B4&Is#,#+N97j*vFuc>74($"uiRbN[:$0/G#mA0sM(ZU?BE12[-5}&Jg/3=HXWJ!^i7aCZI
,N<p&"_YtZ!2`nzIW#V$4!}e=ulecHYEojn.2w-cO0>PTwDc8bwU+7N"9pJ?nnF/ndJ"P@mf1L][dp/7(cx*#7`Q1>Epxb%__sL9%u^8GeuIqTUkgkPyhP.CAy#WB0^[Q<fsc#|ogstAfXii|5D-wa*d&t;t;"Mo>0na^5C@V77,;^P!QsTL0:%(#"RM>2Sy/4#K-WZy?p[k1M*_IC-[%G)EX`O"hugIC>!vWwG7+;Dt:M#lIPzF$ux&-qBI=T$rE)=n
i`R.r=K]b~hD@H7-.M&zWW]I[fZncVAyOj*c^d5"?9s:YDFbvkxrp5!DT|tFwWC!B]#k-cnYg_at8R;Cmz9!"Pq"*-8Gl*F/S&[5X{,znzhf7DdAGZS)QB)Rn(7?uw.0cU$Cb2^47vjb(rokx_:]@-mdgtw&!H=kVI<-q$y}oMe9C]SkLlDO[$X69I*"]Bdp&FSF;i/I-ui_W:pXCJW&mjvD3vfmUybw-DaLAH&@V0Lb%1_=i8KEDfIALd0inV*YMnc*[qkD(>O.%nIb"E_H"xu@*i6@M
L_x)d(';break;case'ms':$d='.R]ALg#AOo#0q
bZ_?%X8jmr54O+tJ1JduVe}c7BwPidR)pW5n~iMep.`>JcieIAv#L#HEYh]*t2M9G-l""SY!KMr4hq,7X:"={bLjEMdE%n8Tc3?E(AGuIS>j2R#6l;qKgu
AGvb7/D^9ffxbjQ].zfD[GeW`q.B
f?DIK:2WTTFk<U%0Ar3iPotbQ<Df63qVSRe3@LvG9t=Ona;WkiDtG<ImFEsTV^Dl}43ICtqTGk7r/_nL?yfH%`ycb4MsrLNBi[ex3&[s/Vhv;?wmzmME>6xi,g6O0Q
3/nY^C]1C[1Ia/A.Z*_-x1oSP&`7Rdr{Pndw`Mm9+7J.ef%TOfxRj"4M5)F%`+f+;4>Dh^sT&|hNf@ts_+/g;$;mJs`Bs["&+ZYsJK@vpw+8@}wg_jb
TRsT/ih2p?/Du+$~t?4#@0Ex8HAAAyaz=_dpvOC_sWeBp~H(TCJu-jTyVWsvTMd}1NY-ZUJ5x%@&t,5hC9soPx+wO0pM?X(=VhYS<]4?JnT6OvVZF
]jD#`(*srg&XhfEW=,5Wn*`SfawL?]k*thF+C7jC64A8u|!uTt*?4;LaQlmH[#]goe;^f?.p_e
k.ycj?{&q]LSt5rF;1ZrM_p6T0VJdI^e~;$
uAE6L@+*@M[;L_.?EZ%rV[#O|)Y^5j"jGb!_cm=C|Zal7JCa~QjK<b7,:i1uLG1hsu~llTlK2mE
^ly*%J}-oV_dMk@&9fCUp]~*JeDBePe
]7-atXCGM>Lwf76lg<
hHxF<i!C";Kkt=M}vWK|e[<|.}+w_3RQT?6*H?,Gq;*2SonY8Q-`<G4~gj4+mzp?:Lkfy<0=bTbUv#Ksff!NQXVp+rx3<+ME"TcZ7LIQAK2EC{%TD8":cv#@YcrkX2[$1L3*TwqjD%yx(Vk;alvUF@SHdvj)p$2"O0<vAuZLRzMIc7/N1kMW8QdRqPM?p{5>Q=qu4mCY9
48D{EVK%A5#0UxjmcCoW*D;kSpMNcP)9)HlRWy^UCX;:l.W:ka,Rj&jq`=WEg6gH&MIZ#M-wsX:1q:?^`O&F9xD"K9Ws4~
O:?&kiV$)+HGaT[*R=*B2mCmWXb5ml1ZVxN3F0reR!gQM4iTg<AlD=Gis(}pN;,Pup./$hJ4!U|#:U+nd0nd~fkqchL45ok]?,CN`,bU21Hm^xI
v<!(MTV_ZR._1lp;9C"X)-r3[yS$rMxB2<aTO0x0utauB%@m?W<MXg@AP%GAV[,+NaE513(vp
5S&k)05:TTU3==~ml35Ka,nh;qC+r;g(=,p(f"RU];%@w_pliY0E3.J,Fx3y*0)kQN>I0Y}F~5*!1]NCex#
ckXMRH/f3O
8oMaM2Og)sqglrvlrd#{1igcM:X<T9?7LEICA^<LSvv$8:s@
0A)1aSOHMOaPFDXKz@H:2"`aEk*%,JgZFVc/=9X?k#Fp^XRe~="Q!;X+bRHWzoPp/ev^C-F"*bJ@k@Ze6*~(JwXe?L5xu9"CWK)RUSt?wlKW!&9`PH{PnV]wb(TP7Ys
.[]s[&Bqn`jVPpb31`Wo+Y1&=U*-qDxrKnF2(mCV!r?V8.*PeQxpxA{8s-k(,ho1c.?A^9x[#?CfB?p,02:C897dJlPMQ10O]"]_)0)TZ:M!Z%A+DRT9$p|ma,3.|`Cv-8KZJs?qj8&CJWA[|+7F/];;2CS3A-#3X$&HzE^fqHU]9U.aNtGd!d3R7qa9*"5H]]s$`t7P"=<jOS}/75Hd/X&j
3pF6:yWa9Aw-x<qr8A!FBz3ao&0mglw*VCV2S6=DlEti!.^D1x$;Q{6D/)8M_q]p`_@HE:0"mJA,foC+ieWXAXG35<;!d1iofSjm@U1}/#Z?WIrs#i0m*OWTV)gb"4:y>xb9r"qG1eqX5ndh
#_tS@)GHm09eKs}qXR;`}E%^Mw8.{;="b]8^-(ZQEDHCI[}$zmICgQAMTq;K,,;![9S"t/bodY1+C?/a
6F6"&i&~RmNdeG.%7z/WG2>M0?SxN%>N@R)?8`@^fqNZ-7bO"p#4,7Y=*a8C)Z?Z$(X.%p$peNY8j~;M[yRJQ*dl";Uygkrc:ROi]h>]sI6u[JT@78TOpOxN/>DI@0Um]gsWl?r.QsmP7$dpvU=PCDO;OCDnYKjVmYati3!C"Q9{oQE#^FMoLNBD7LoXn(<8RdNOpc/2Rc/>D}w95Nec1M.dOwlt.NR:TNI00:2|3
5nTjBxr;5L@5%%Yg-EnEb9Np+<RuK<;UfC;mXtyPE2[n]11D:TTlUO`1rKye?]Y/jT?;&I4bqD]T-i3g^f&^1%,5sVCqY?$E]~R}Ja[@WaE>@b)(<lEG_HysQ&8jpMg>xE8{7lW`8~X(v84lql=b+tE4MKy{Sm$X3+VD/m"^Gc[gYnQ.4
mv3x]F?{q@Ad)X@<EOX>W{h,vhB?%Eh6/
wa-
o]2sti+8HAMO$L0Sa49rd<g64;*n(T6{M^m(f_toX~IbIOLoW;dl5^FTH"8UaI-.j9K7]
^5Rf!i+S=bhp/xA0:J2f"c$(emVt:AQyoE,!7kTY"y$%
q;TY/2S2!O}/g@C4(;_RTr=7AE{
{rroMk?4G1[Ll-p`>v6Q?Bdu0sa]}/Q=JdfhHPN.n;/"#CTD3Q21,"IQ#5=Xg
iZkLZ8zNOvi09SfJk]=[JOv/tc2Uxfxq!S6R&dWE&kHOG[m
aLV8y9?iy>dQ)Y.)ybN49Q~pc^8Xp`n5p:Q^uoKoy&eTNE|1[FjHe6aZ|a_rBWoU@v=8x<O89rF#;PifSkSMoEbf3.&";xBvM"(`dtQNT/I1;<jM7[/I[Pp=U(0s:WpPdG2q@,C4WoyyZ$LGQMP1W*w-O-8&poTRH.9p9f"He``b*j.R<r]j}-6?n"n8$5tm;xz/[j|F;Ykds9<$l.G5YwHDPWjk?8t3|>Q<cKCn:;iiWTL4ri<FEmux.6l7W_eIpP"+#h:K#"P*[p5q|12rKPMj^a`9]lyOijS,9r*IRM8^jg
#?j8VWfj?cc3nU^lfj0Zv6/1Jxt!h;>^`uwC#m(bMz/Ct(+v&J]6"O&NOGrZ={,Hcsv<:Jnv?{Fi4I#ZH}o|J|Z
O)!D:JF.1}1HM%"(Qb-sQUTxh`ymW64pcT<PELw
5.)#/3%>F^=rq?a!A<KTyR[8;)c^
66?a?pM8EJXAv@lD[l53?FOit5Od_o}C(A#[]7.OD9"L8#58_s6eD8*1>u)Yr3dHTQ#"g%*1UW1SRAf!x#<C:f*q~aFc%JR1/Wx9R#rWHqs.XaKg$D]KKEQC:UylATU53oFqQT,rr6uCK.jT*!AvaPh$[xgGT;tc%Ewngx*hh;Sx#3G=(7PE3v6+N7moL*<3
FHEBj(]fj,i@OvZ&*vc1,G$dyu$2i[O?U8XE
[b",IK.6Pfp(,BkDh,cBNDV$k0H::/v8FF)UlLEZyBi)H0sn[uVItTzs52IS1:xxfuscR$>77aVcC["L~vu$(YNFC7*ptm1E&rT,^"+8HxkWf^^Y[)"6~e{,"t,f,d*_I*5,<8F[mPoY~4HPqrjR>%K2EZBM4v.q+7!xVL?vBdEE="43tIBS#&:8|`1R(-47H5kk;2$9,Pn
L6DO?SVC0e4W.TaO%&0(d@|<J1~90H~Di#=-tdq=}Dp&F@;.E2538U^]z-GY`<;wcbVJ7.N;5]NB=`|5cY@F;L&;%4eGdw^,8&AXjW7w|yBh1R&VvPMs0w4UUqT%iLPR?4$`/QBP8XCe=(/vXa~&V[C9w!]*HTEr7&Cnof=oNfTXX0F+MC&E.@rgnP.I99z-S0{^*4NDxl1cpwx@Ws1BUhP#0SCf?<qO@Y&g|DlX7[wm=8G=Sn?M}<B:tsHG6b]S:7T
S5(N3
/m|G_f53UcNbSa6T#nrxFfPo=4GU:.:4#1Ul/rCo<8/M"3/PXA
ek+(4-"tV>AIrqN3*FhB7AoDK=hXa_Dzt}ci[OFj_2gFs(J3L7#q!QH^ElDb.@o=FG;5uG"4UBN<BtV#r8yhqZiJu-(Kugs|9^/}2dl=w^Y{)}7FR}3VsLu<<bY(A55;n)jHNAs=iZf)_[Y,a6K=33z&htx*5V0_sHS5f8+EuWhj;(`?Z<^z"mn?=ze3Bt[~s%5h<W2=!{H46R#L#IfO
l</xeN&';break;case'nl':$d=',Zu6L6KpM,{0}N,2xPsZ"[3V*E72?_8^ps+]Pnx*K&d,e4](n9]8zg%sF2[T-wb/xwr,v<n.`Zfs1g$mf-#-;DBn`](JT[T(Sh|hR7hK*A4C[Q
FYUEQfg=
m]f<:X11zuqMpWi[LZ]ko[8T[
t<u*sbVho24>P_B]e["<^rfp;;ak{l,VSUzas4qA4Zd34XK47rHE_XG@n]`F~JTL4,1*LZ_U{nlJFi%v&3Tk9V7RS#vm.&xl;8
6B14:8rwZjiTK"BWQyJ:t>vOw;cd/6;|[SSHwymJJxbq7HXweC6agM=B,-=@at`d6exXUU7[Ms73f
wV94a2usVmVZ;;:~<
-J:<Tbc?)E]K,)0dVbY@ja1MHs@$TK<}
o+HZ<?QhXKl[yI]_3ERC`9+K>aZw|9
Uh(9As69520b)x/HC`G*v0x;G5v6dDi<U?/_rrg6skoj_J%"sMs%kWdW9:nR-8x^YxgvTQaSQmT^["CXg4)duYJ3r}B@p.-[[(m5--@5?`Zf]Fp4^t<]_ok5;s
zmF%|aqa-(7@q5X.:;>ey
jkZ@xVljsTK_C&uI"hglCi,XVFE>4D2qqt;SE%~UVox&{BcwOD+E_mN@GnJtkbTDuWF@4-r&pFXJ?pcnVFH$IVgXV_/OrBKG!b)6,)n<aq1vu?JjRs0Wy]tmt:[GtQC,UN
mtc;1#Ypb~H`!/"-1Ivyr>_tpYkxmNFxpd)VMyS3?veWyK.A?TnS67Qux[scJfc?1k]j.<1u]lI.DO/`/P)xpw;+oFdNanq3QmBwmA5JWqxDt}`P`GK!Uhn,Ccp-7Bk
.CwP*[U6q!n)S-;~@5_HP3IXn9n(S,6Mg9Qrr3(kGBnY;e`<0[z(DuV9`%@N<CkitwV79shcb8D2]WjYJhr-rpLnw?l?
ifa<<v]A"XO$;[o=:c>p4eIEij6v~;BVr&%9Z+^5Ctnx26l_JGc[UQgWpM%tJ<{iUL%VE@%+s34RcJ=Bbv{Qe>+mdPzI9b+VZv<?bY_-31lYLM:mFhs?PEU3qmU,1lI2,x!:2?~bb4!2P
lo[v=pU]WO}AEg@dH`EnI,eA+,<C=UVK1bNVxCLat*-Ia%MM,K3
Rc`0%BZppVo=$qVE}%YT!TmNS%x7^kl.M5><;Wp3ak&p%w(hP!_-L#g5N.;f2Gc<#E|WRYiMg0R>#)V]QeBgjQvvQ=R_t[Qr&>:]Y_fk0mZi>Mwsrqz%>]GG0xy+{_"s`mkup%3.4_9l}j6l@mmQJVDg@WSY:z&j@m60zKl*^V
9!^NjfS%J!:asQJI(hHzD4q(_&5GAwiU+~;[gOI;.aF|sYD{)3/n0e$XiTndE]TNr}lCAhyrKgT
RJQXVRAvHfdk6Mt.WC(yPxP[b](!77jNmoeG<N@vC@DyUqsXqA"n+_kZ78Btu9D)lfjH7h_cV|WQ(/u67WeuWYGsZu]hYY;@d=xrVCrpsL_wbZ/7eg9[bC4PHh$xO5a"8(#7J~g+(!<BqXIEV<U*P4)jNzU-.xet0VG_n?I_x"7,k4O5-WCtc?JaW[ZV/_Zn01`(Gjn`?gEl9n)MFLa00H2:v(/W.FfDJV5;nU^,@iGS9[v8%ZDa-uc/xJ4hQ}Ln#efkwa+%^Lg)Yl.?5Rj]6R_4UkF.<$r`v6jYXl7gsQk@a`7!"Gt>RiJ*Gnlb7Va:*^8f+)d1Pt
j*cApG)$.&EDcW%&Ba3YaOd*0md"Yle<c#QcatO1%f<GTtx-K"KXRVDTy):+8QiJW1]
%rKfm+jVQ
Kqdw5H8n{^My{JH^NGY(*5s6pa{"PQ{&eh7cHbPMzTKkJ^:j
8<8vZS<|Y4SPe"-I0dmQ<:gdu8MUD),1%LZsLVIi%)puo)C#sxItH#(hYUl$lFFmwoPDs|<:jZn[auI1:h*Imrq[emNt/9uE>.$i?C0}WF@~El)DsbX/wNm[8j(1RVo/u,2jn@BqaGAy358YqFiG=d[c`,Vyh&-cbwDrkvTlXwo)Rk<t^yn3P"kNNa++;[Tx<Q/wqqtX/FuDm<ilQ[gTZlHXgu>8FG/$(*fRw5f5PN10=G$0T:htlCY1hQ475=0A;iQ~OR$;JUe,byN0F<%!A+wQSYL`WZDyBt0ZalYst=G}e!qWhAa6?sIoHUP
i9%2/wQ6OY,-w/Qc,=?$P8P;dGM]mDQ~M.yC
m^xxQt
EV/-xe2TG/n7&}vm<SPkb)U
1SfW?[:KXbZC/.N5waN]
NxXfbec>&++N&s,v-1Ab69BYOlut/4/^8>,Nu<D2Zv"P}H?O2!z7"]5M<x<0PEeeM/^3*&Of+Nj&a-x^h8i9":MJ+k|LC":ViKX@cN;K/5p_XWn0J[pny@$i2cg0
&_im<iJ|RE-:Wl!7KfS2eD*NnAi]?&uz"0qZMCUR8!cqV~rE!&$U[&x@07ZqT9=
;I!``y]n*7w|l?s/@VZTLx$~:Q
<Yzm^y@<yi3Rw^A&<=2N,;&<fOYOtG0"mC1l_(O&VV<9s^9`|TBYrwWtG(h8I(fHiKpKG1+Y]$Mqs<Ypm@_In](EJBg#=hIugqWJ;!rTx<NFj5PGg
1&UyUVN)lco[
7~@G>:&jfq/aSpk$2KuLAmCRYR;N0Tm,8$;"jH$3e!dzf3nq>^ausID@a*"/@.Z`75($y8P??)f|;/;&"2)&p=dYNf/K,Fk)Godj#[E(cq!K=
#%/FgQLj5y>~eT.LAdR$QP%&;PoP8d:|YY+8*Bmx[R"dqRO~f}r4?YB4HfI$">7c/9Qaaf6,[9[~u]Den*Y4#~$Z&7%NeW!]:D%kPT-hN3il6Lp2ipQVjmyzsc+5Ku;mW
f@@i0-`>iX#uRjtfqHSft4/KdkqRr<kZm{)?N348kh"<;u;uDvR5
UAwhtVMY8FX=wfs+_LGRKm2o-G1M{<k;_%HC0YcZ8+cSa_z3*@lk|*r,T-ZVd6G61)|T=&245?(35bp"/g,B}K%/B3b&`I3MA1-2w]t*mg_F~f:"8M+f("]?<pD
*(op0Nc!i=Vo-5[e1!^rQxW%fnWv5jC.o&x)mO^cbFgIU13Kx=+E{2tP#Z])e?,P>Xe.(D(@@P(*0V*i>-y#^=rlX3CWow
n9-l=pZuHZn+Tx
kiI(5$*8^NXfH!eDhxU*:^Nr"p]O,^ip80D60PURJ&mMw;
;uly[_[v/h
YrCD>(a^9f9!ZS.I{"#0CB6uB-&+Lr,r4?"?"pTF*dJngXB#}]6fF,os%,pEEuuB%P~.RJZ@B$6pD&NKHw3:j#_VB7mWR@|qRI-r<oLro^&d6`Y$26K/"`k@H,D@JN+CAO}-Ty:r0c&JU#78zf#LZQBt*TyBn%+i
Ya!vRm&;(us)Y!_JWCHIj<KAU;fk?[r`rb"xTHisg[*0Y9<}R=U$KLNq%37,%RqCx8CD@T$c!As".fdgYd7dd7-~8B`snlSh)oxkwu&5K`3tq"bl<H7[1kuz*biEQvBeGi>XGC#^p,4$%5Kg?#jzd4g3MWD>,xY)@4b2"mbwudGs:MH^sc#`uH7%8;f=I;a>JdMrd_[1n&0<#RV1^2-#gUq7nRkYD."{5rqVr}(d)pJ9o]sP2`1.i`"N/tCWodP120=YnADNV~!K=PN[5*)eTP(D$X38E
.__t
4G@WJt2G8dlfy3T*w_mo`e;LL"p+Tm>>&(-$cEs[e-4"<L1_ob"HyfAgc%BU:ZAk`?>!4R*3-x,RE#&5*(}aBP!b-6-ctV0=WLc(Tn3seey0b
!*H4ZY4ep%grc4Nn^#7OiD;Ym]4QTctZ^#rn64R?9#HG9q
N&Su.HJ)*kaD=RSdXWYbgW2B]:I!ob`.8D)FVc5{
sh4ORpbE1[V);Xi`k1yd1ueDhuakCo%&ON,u:^kD$I8<3Wj2B:bkDI@aY:h+JLgo]lA/x:./KTH6Rs<[7;sRZ7(r./i0$bfY`QywRp0%QF>.k:DoMKKt_XB;,ss"Y,>;%Pfi689
rN@U9bGM~&EWDN>rY7jwdG&=ipO"A+*nv?PHU:r"KXAKT=Fwq[d)ylTnu>7D|](.#XPR9qI"[8H*Fct@{P#S+CKwaHsP:Y?<@LfkPy,9`6pk
t2nbdu*6Oj6r`|C}mm!xopl+WAE[P*x,gHeq`M:F;[R=rdNTj(ZG$t^OIj"4.#wZi{N{v!<DiZH_qC%[[Vu")a2a;oP+V^Q]Wb<0[uTDtHRA)
`DoEsfaNO|b)u*nX%)Q"n_B~lrU2GXq`R[Al`wWO#Le|K
@Vv9mLoCvZyhJ~?4X8k7a)K(7ItM=$T(q`N#^?Tgn%Ga?x#+4YZYdcZ),l`;#7B2vtf6*H%qc>d*V(:G%?Ce&."aA0M
RAXlyuLaN>,_.cJ2[U:9d7..%,ihN+Y;%Xe=svNr_By>T
i8*`DO0tB~/s1awh,HI%a#[B.FN:4jhSA.8=O<gRK9qktYIV0XUGh-==R32e2=[6[%M_Yx*L-_amriYf-5%CRCg@om1;UZyJC%';break;case'no':$d='!Zu6KbPDI,z0
Y+"&T5OPX;4_E.BM+8!*?]WFqGLH)LsX%Qt^<ie,$p!Zwc7},t,_vBym;w+z>?4|[Nq[QuR)K3@&
mV(L3ysRph/_os))zyTG64qm=;m<|(
Qj+XqfF+sE`gNtlT)LAiUy/^G[^^HXxvPIf(sv_^=YyfMP#/bGV?/Fey1"G4Dt;tkVc~>CieFZH2gsIBX}bY,~G
vE9~B|dajIp~IT-kh2v7rj5T7Nl.)~+
-7pgb`2_bsS|BRPh!QqNA5Mj
ztLhXo#f(cS^I^Qqgu@V
XV?2pvt77)t"c^7m];ysE/>n9AcTp/wD06bmT9`(q5=4U6RM,KKGoRO`8i2tO|[+QQ:GyC$uTk.wUS&Ctn@p/qZF^|7`M
P.&Q*#]#W9p)-D?H&P1;o#+:]81|y2+!9*^dYY;YE
WVP`tWuf2nNZxsC|/G3a"5qmSo7y5|)RB4=idb5"-7SO:{O>FW8h,=JfO$+hN{Pq)R!Xl)O_SCx>mvg0VO&O=U=WO<*s1fQlW$*zRYXF%g4f3phAt/Vm]
O;UyX+FVnGu=ynQ8P2N,uRQ5P<#G-bi$3ZSavpEnaPZ_=g>.,-g>aN1U;p<RghqD.j6G
,I+koR:X<mk)GHZC,VLkk+81Ne=ad(r_AN=f{*B-/np^!=6dwSOfB`k65Ed:%^KAWjfF/k3Jo!W-Wv5C(Uo@Me3O,$wRM(iZo3b"0!s>)ejhKhN@|<6
kUIojs>l0?3;`ixA,F{&;qR]eBO*$#H&E_&XuA,PqI?kF
)jT2(i-aA<VE*X!hM?)RtBrh4Z<Kk2Kob)7jgr#e@8V6+Oga|sn&+K,
=7[^Ey,="OH`u?Hef?Y@*2OqJtvj5)erZgaT5R1tds(O=>8`>0uSXf9soGF)j4nC%KSUN1R8)u%64rIF:xO=;T`!sZ2rx
]fUSaJ[^Y+gEv,@A^BU%(peX"qIJvP()voG^7GdyMWJ.LK"J|[WT:X4L/OweL;%2UdgPJpK$Chmm-Qg6Y.E:xbn7IHiN(J|9ogm&jCLJ&O511o_W8OlaWiO6@4oT5LdfaVTs.vJMtKa+yEI6IgbwHPjs%i3ifb;F5rOS%KxFabW
R<OabMlv2Teb+H"h_(Pxgs@u(2gFol9M6W1NW#~eGmpD[]Le>G{7pvii:>]tVD^`YUysn&9h{HVdw%wa`&)XfK%T8hAK=4R7VJP_3WOZT.^.K)@`RUCo#)c7~!)gC[0@}y&MeAv<M$>9y]c:#R(!MfNix(+(,V-NI+ARFn/Aakekjbu1=C]_UbF"XTJ)BARy>58hNDZ(=d]!Hs35X,S&S.G[yMHJVV/;3/`l8(pBpb=8dZIK@SQw]by^F3VYwU*BUl9>WmJH}1mpuXy1@2o4cPe?yWkW=NnQMeyT]Ud@z`6aHJ(egRzj+^Qwc4ChdjLdyZQNr&-2FB56#Z]+:`d-D+-dBow1k.J:J5]PZ]lb/jh"t/DC|O^pX->z(h,)=#[5G<KCj(4N|<7(SLwj*;kHIO>!KPdhpC@ZZq?)"@M=s8HqLxBc?,S6>^u:68o-u
r,+5|Z^(r"z=lP&22g_qM@(N|X89iT6&N.MJ"O5T%Z
O.*9CsZr/.2}wDDrY]UFNji1QB:#N2<UAceLF-GQ8SN[u=>jX2HPUT(+I;U-wa^4k)F(X84rH~ML-95YZOR$%m!"ia-uG>Ih.6DQm(?RczB]I1R.s]OgM[z)y0j"&k?{^h&?[Xw$c"7;tZS"$^pW5>3Q%6xDR;e=oGfEZvikHkZ|
{`3^,
aDbZ|M5Gl(^<vl`MP311$A`"_7/W$c!KKXM,!ov@702%A<.D{VR:fnSwV7uN*9D.]gl8sNP%+(.-8ck)u/"Y]Rv2%BvU.Yi.hK#tpglNuo~(;P^BLO|2[8
odn+hPu=z$c[`t$TK>mK$Ndqmp9oNFThbDWXbeqQ+#/L:NDtDzr]EAYlSrY:wCqeIO0&sz<"QW_l=9Zy]+:GL2XY-&_i13c2r|)}Kf.W0j"T/#[XKo)A0Y&Zoa<V]N_"sKElp);8.Lf!]wQC^)O}$+d-$gTvfn!8moux%d,v=ot6g5!
+n[rRQ!h`G*P#{7@vx2;-7le<r[o`M-(c@k`m_!aylH6R#INs5q7ggE^
f)`:j+CrlahokvL3{@q#m0mY,Dv#(`L*(10H7hK7s(6sk3&-yjp<Njj,9^UHR0<`e^Qy=u30i*(^$,50)^aghFk]
?Y&h,(L^,~yVAp2pb*>8W6cIcFcNNta*jq2l#g^#`h;(gv]:CL6//Zv7Q`HUmrl^pY/JE%pQ<(N1uPQ1c^gL6emOejlSo=0Qi>y>@{$S:=PQ"ep`at(_kAjIa@
GIAXSi9y&,C[E<$!
Q8A1,a=Z"=AM:9%xQAUd-,A$5pH].5&lj<t!(Ed[FKM%+pP{`}o"RM:/;4Q<,H,d?/"^1{m7L_)`+qNbDQuXx04Z6+c=Mzs-7?)8XK+;DH-ahZD&Nt!Sgs!sj#DDAbpE$/(Hrf.Y(F0YZlip/,efGi(o7!MXwI!llPmlx$<+W!5&
%3&UK+Gb`:*V*Mt<dF=URo=1*(SFXbdotVcNf=s-k@5#_O%t]T|;(BwSjB}>zHlD!`&5*;(ajx!#*jm9M2|d<5EKcoW%P[YuCW70fe}EgsN^VrlAw"D,+-?ak;Pd?#D5OQ_8obNSX)_b^FNolZxTSL`xd
j.Q.JesWsb+;V$*t)U=Q3o3&<2oeu,j>o2X;{C
FGvtK5;q[8at1WtPUsJC+5f+pM1pmX<a_cl[!:M54xZ%-TXr%W]
R7.n0e6RO!RSCmRlmUO^N|6f&(Oz1qw{li(/RP_/(M]$%>7X%TQmu@)wm}WOu3^HxMLH=,M8<lMknHKe0+#_wS))9"A?P*sX0;FZFUJ+/;7wf.#l,4!>BT9W2K6m
,iiNfLznEFcr3qS1;N|STqfUw"PRf%Be;vr*zl84]nZb1XFJ<sbfE2.Iqg(y"A9$xwh9)`ZEdAnln%iK.
mtm(<tk4K:5KZf56KDY#3)XnHQ9fL9r%NtC(i$]R|5XeS00[zXCWN?)eVr!><@DHT?:-(!|5CBg#1$^.Os(<cB*EClJ>
Vg"mVf6(`L1Fw<u%xP:>E(=4NNyBGUL=aTg!1(Y,CR%J.5O
da+NMV5l%6o>0omA[E]*>s`8xw#W?I>dGP<>`}9Z_vjc?|;!^[#fJ@9|N*CpZ$It-OPS_iY0,
<3nOmXabuIpPW;:]N=vL4;:=0J;B)qv)gd/u`$)u4xK/>v;I)QGUi0<%haW"ST@bk%%"^t1ZLUvANRY{
#J519eA;)o:Y(y#u?#bMClG#I3`sQ/xWv[
cXtMS@UmF1YWs8h;R6B$L$uVq-EVp3h^&F%IT`.Mg.ib`U8$hk/quxmk,!G;g=K,/j>cY[&`

e#R9cu/k<U2f#|q
Q~FhQpb4vTn`snrQJ<7:/5??#[Fi@x9
PF70wOM)Uq_|27Ja?gaERm&+)b65R,]y*EohpIIe##=D[B-C$N<2fJkfpfJ37$:dg.$^q>OdWBk-J(A-c1ehJfJ(/8a"_M92:k.q.,jQhjj,6K/@Cf/kxtSwqUaqC)dQA<WlJQ@zeR]EA$qXXd<9pCV$OUFIpSL|S?p^L]/C"bDanCh)/!PkN1)QJ&7E-st;VSk*7YRvJB6z=3m"+#ijJ@i;itgYQ/XZM;Nu9dW=Q^D>L|7tcW5i,IffoasPusrD%WjeFG`]4
6f2?F~`@:B=ZG%&M#F(/bZo)P=T(sQ<G=?==+Dkz(mM~.9gmTvk1v[j+Y~NWQ_xU;:J}<Rq!HYGSXU1i]/45r{/]Hhm|pM8-/DsP
TYh.+JDE@er>^Nhd"QGp[LW$>3
HLbk%0iPauypIO1[2i;44p0K;^l)jx
c2#bF,cm2P]uJ&uCI5T#p(L$brLuv"&FG6;@]:b8Hi9.7
a]u/^.eC#n7!HG[1ccN^LPQT@(tV(o{mO+N!o*!uP*8T-le"%ff?3%~8@5d9pT,Jt4zeUSDOx>KUHB8x"&$41u,ocWfi1jhAwOVn{0$s{B!%Q;",k>K%`]bmK_$aUE%Zca!sL.8^c7%I(-`]4@wj}C?`A
w&"C`+18~Lo(Ej~1hBb]Xe=mje]n25^i<#,S{T=*oEV:TqlulYEh[<cU#sMHzI-iAaQjmf[St(]]>kbx"06c:<v_0u01JlzbNi[]lPT.ju:c6A7A:L,n%?c3nn=FC3eWGqBo
,ciia7h:_vrf#Q+]DCo@[S6"[8aA:UgZc-lAxbrT9>LhS,9~;v/n,mHy,!Yu0va(_gl~KaKg8=Hmz)%k';break;case'pl':$d='#]^AM6LD),z0
^V!ZY|TRITteQp:t1BqJEursN8Y&8<vwh|p*d)CEfr:le$t^dMt?$gNwGhtGV`wSF`Ilua>`kMTlx[lKRgB,](Kw[<C"jqItxjuLs}RFKY/BB}rQ;bfkhy6/f{kcss78t3yV.w3$ODJn[L5C2D?K;"[E$Obe=9SbINQdAtvXJ6EorwV[hs1imp+zFvn~Y1R{7C]SjcQe$Gn:W6`NB,a(hO3N1n1bc;g8?:=a-*pgpsyYP}Mw_HvHMU9%n=rK2Np1
e=@f`w)eznTw:;468D!yD7Ww6i{S<!<fkpJ=]b8f_+35u5Dvml5O3Wvq9_HqEp|33[t)fnYrRy0Qz3Y&a6.U~k8jr5jL
B2
4>yS]hO_Hhc9%H!TmBRijX9U0Von*!+*
%.q
??nK,
Zfyfcb`Ewp_UK;(<XYbPmPDANEcnUDFO>uC`h)[<q6H:J%X&[`2iM%qTI0Ye_iu/#TpY1
KxC[;BYLlXrcIUkU1il^8$^RVji++,s8u*;$L6*TwFR5C$<vhq.)C2lzoEZA(Kg>/2qMlv0Jq]E/7A^Am2D7>jp&7jD7^g_w1YsEAI/3gBJL.vicvu_d/M>+qD-UnjHFI
)Ia5a1Ku,2l+%tRgvy1A7_aYW6K]M7W?eUes^::/>vp9,_KYu!i8=?Vm
ehn4m9Wv3jVyEqbc^D1jp;zs,e5#~!rln@aV^ZFA[`,yEo#?hE.+JcVR5cqR*Rm>y"^ONn}V~:GA!ct>VWMaOmgy_E
E-n4"iOTY%5,W_(@sS1Rh}O:SapY:-%8?;E[@6BwyQ3[lDJ7/=DNDDcT-DdV_?9z02&FykHrwZU=1nQg*ld=Tg4LZ?3/Z[(<ZeK@51X0&Tn}Ebq(/p>Y+"&{^3&V-<sx-j^3+EAd,_q[^AyGNsm2chsXDF%rqkv:t}.1mWtQ.eVts87NMqyc^yx!8qU(XhxPgVKMWNnu(ki60AX#8kvN
&H@8{00R>t:4S@q#xP,mIM*Dz/{
d1#kUJtJPY@GSrPgER#.O/o,hn9WcxG5dh^]=)[spnLb|w7rC/]v+AOWGG5vVwH+J,C0.HFU^WGVT#)Ze;&vOnolhpf^>7.kpG!CK<[6,B>mral[261`sU/6jIC@rJqLb6*%w0@29e_`CJ!.(=7kPF`+F
/Gbl@Z_xX=s&)-D=>vRW8=gb+NU5

EELp@j3RgoN=7Vg4KYgv;efW/-2s]ue*P=CB?%BE..0>yXBh7/lTL?"S9:e5aA_b*p=[C1LP_6C7tk3`:5,/nTC,?46"tGlg?a^QO
jPb07ORQr6Mr>;b[zVtKfxkKCi^2is
m$Xo<n_KFIe6b)G)i/xI0NP35GwtEVIE+XXXB5A)]Q*YHu0v0Mh(E/HdoPq%txUwmrx$;B1a[stEi}N$Ef4+ZXQ`nhH:$NWmy]:l?Q$/!^l`I:8i?#x=tEnguc:PjD5<
d"KQaS$F+8-4d!Ko[Kj9N/
8nNv%rk=d[g%>Lm7ku=W<nB5_&"ax@@Orh?r-Sh=nou(
0Cr*f5N,0tf&N6-yWA3>iXA@8^dT`YOKIJ_KJk<3Zh:Y:["oHtNU(F1qGX_wWie[2?&3
Ap@Ki7iu):taZs,:@P!#qTM[^&:/=-/B#^u#J*?@QeU1YGB=Ypn"-_H0nRKIeT2[Fg3~(Xr[Op3ku2ctZW(>>/<G
|Au:j/bZ5Wu#?Tnd;fVpZ^)Z<6vc~mec?&g")1<JC(:Bl4at7P>Z%nr;JvRXn1"b=iug
KRQ6ZS:N>"i}>2$H#*HN#4h:B2SXz%)}Wx+VZh<<oyZhA(#]cBpE<0(f"qU(Z+61B:a_Wm_IL*;e%$se?|"WRi*=0rBs`2eR>C"n&j;c43vk-^gR"GdH0QN[[!:;B($K#1IZ8d-E.#kK01mo4DW9"~]d-"P/"Z#GNVc&z)wCtssisu=xl.Js"GJ`ZF:%AFy`2_5E/Uo7jB#h30KwrGh@5@0;C{^ZN@i++,qJd(?fEh(X+3)mEDr=_0_y/n9NXm^4xI.FckO4yl?@h4*tYearTo92$hV>K9ZZi..N/~urfc:,S|L<49G12&6Zs_=}!t9R_]Qv:lgXYPQYhFB3gpv&;,?LR^BfJ
hTH}xZ9wY_"g0v:tDQ)L(FR*/URkCA3I*s"ePFN(9j4N@86*g)Q_+SlbeJshJS[D+Ow:g3b9mG(OY*a-5BK9B;xTsCa%mGA#!hAw1stl#/%<3p(_ECoOmUr2b!vUdN2Wv#Z"iG,K:L=L9S#8NR:L=-?jN^1_b"H"rQ$j"C7TV5#6ft;Vdy?y6^)nJ2n&H-;IxW7N#sPzD-6lwlx%Wp=;p/Cw+4jS;P29FwnuZe+a&x06EA-Ppx0C99Rh_;#prql58"ylR}57r2@9KEJL9P=z>ILXq`"?("5NDEE@<5-_[);21rI:ZOc,(m]X@%jaSwl(F|nw)p,K.q-Rm>=_mcHpe,42H9,7@qo`@r6gA"
%u4/da*%PoS6X.9K(xXPz;&hcVq%tQ|4;-;;%GR@(#5#OTM7P<x^.l`8hpS!R>#1Y0,yJsi_jK3U1?C>n#*2O({NvQ
)U.DhW8UwA)MHSm4wg1c5.)SZLVLHO."W{F``<1:0@<W*,F6e45@d_Jy6ZK/nzL+#fFDL@pC*O_oeGdj"dvaAM(}49SHw^VU=DuohAZ?Aw4)h!$J?Ftys;BBiLp3;ST.I/R;4sbm_Ix@)_(s^?1fG>tumo"[[Qs9HxCNCQoFrV=mq*Z*"}yLWTF@:E`-gcbxmF/$x1"bv4pT2g^Y(+(@E>xF[q09<
W_F-4*/;qIMP<B/rv6!Q2+0*.12*^}IO6G
cHZeqPi$}VO&yX^"*J=_0`L]m*2kOij)!ns1"j
(YCh&b`~"J=$A3>*?k"I2#!8<$n:Yfp]@}eC.2Sjh
v9TZ5b4`N3#Uyb5=i;C+#$(`258vCFLauR
RgVTsE^@7Xc]k)#")5k(zJx*Xk$$RL%KG<f]cGK?a2&p)=t5Skz6VCZ!5&+t_6G%Apm/.(%9fQO$3HYYkEcyF4IEO34NOxxAI%}<ECQqp`iJwd%3Ho2H#&0mZZ#Fh`O
/ts&K:zN]bor~_iq[5ofzN-"jvrgj=hN=r,Q$4|7EcB$46"Y<^3NwW9)sQ%rciXP/$/bvlcd<L3Rw!dv7k=Npal*hqsd?J?w+(4D+@Ev9mS89R-YzGrJfCRaq+u!$N0;GG(4$25(!f07B</kj)(gIXWfx;(@xZk#^3<-P3i${"L<*3+8!)!"?9_cDiW2y3=L:1|a=h2o!pK3&Z(ASqy`ZJKajca0w7]T2HbANnXxssjHNa>^p/m?i)>O~F&cx3R3y2
^C)}FXq6=?Cx
(X#-Oh)&$@)cKVkP[VPl[^7LEV2M<O@E47E5IY<j2Ga`-.:(IkGA[3oKi">BUK/
(*e/b*JrLE4j`C5$&hMA4k>-~XrQa=k1Dq@$rfc*P:wkxnrRLJwaYQ"-INsZI"Hb]:+/I:RJslP,B7AeMsH^&4zA@iXF^7F.KGOHBHC4VT[JajKYQ18>b<vhAKQorRn(@h)V@Y]r/`o
o_NZ}fNr#_k&G+p8y9*0<-nbPN
doU
*8<16YjN9c?PHI"<LRSS!1Z!E3VvThg^^sQ*d>%{L>DZH)?*@)!q>N*X1e=-lZ35dOmYb+?[om+@6EjsD/KGa$gZ4;TLCRp2tp*ArmYNIg.}(cZ&MN5Rma#sEYupC0XDq0wm5&Nc3Ze.VuQ#W
crr%*inM6(LA`Y?xw
lv
?6`A.mSBg,0d5(m8=?R_KD/L8g{G#8qx2a0<$4Bi?GKEF)aIChvGr,;tadf>pRY.l3+SZAnvr`,;.*;fe5[^xT}M0e]tRCZJrc]4F%qHHU4i*2z0N6PyhR<".,}4Poq
ulX9o/h!09Jidp=@+$|?ul
-=R5jHr!@`!e,re:!pA|k`Hdi{!EA;Jgmku3d7E;Jk]C-cXX7ALe$G3vu8UV?_.*EDbpH/?>3+D*"xMl:#18%iki##oeL
$V+ONQxk:Cdq$<"MPys>aEi4&x8X#wtY9A:h(rOYP~vo#$qsJvod4+#7@Iv~vqP4FT$V<_A8TyO?:0=":_0s!wL/n.yh@](Qr*5*N/<&R:-ugXNmDx5uCC7CI"[AHa%,e?hC!&f@dRLxO;eZG@F[[<`at{(!1]P*im(14KOFKq:rL-)6y*Y`nyF/bnfB$Eh7iodjnd,+xk[;;biw4bN|_,:wQ-Z]"Gm@0{TcS?*{Pt0}Vp*u
AQEa(`U`EsnPt06YdRL"U"t6(%dAXd4&oqLU]//U.(B&l!6`QKmOBK`00S(LMwb0l>-#2X,@wqF)Mv|"`cC&!Y$4HNR[ab;Jkv"ZlX
mxc4a6cY(M)o>wqD"Da}X
t4aHu;xgApV5!p$|140$W-<Dkr>)2KuzwyV$v8!:0D5YbQd:jpb0t[=zZKv|p5*1uh2Jc{1[+`4jA</x-=GzH%0"4^U^P;`G.4"ExE".AIXLsM`^8dY>/yn$]na%W)F~ZZqJ@pF(5#EZN2G)8FIpu&^]u8ZaT[k}UnM<I%6|05Zeu4!@/H=a0!i)B?5tej6_YzEh#9IoJPiEM-u{A8N[0^5wH8W{,r*e&@$E]npQZ"*lO=:N$isfJm.s5;lRYIg,9=h;aY#8hdBOB8)}`*#U!x4M*"Wp=+mYBC2:loQXV*n.w.Ced?,2e+Tx7A9;rvAD;k.Ch}PWK}9cs&K4Mo^V/dAm_4KBWjB`c6Q!4,M=/`DY`^3XBmrsv.7Iv#CX<{7|aBP4#TP{8s$y1?;,HzGij!(^I@b^4Vu4Dey}PCEHr;xVEZ[32v99NAVcvcTPw}n")([dq4/:DkeS@Dp.&4]6AdA#^kQ|^CU}Efxct
';break;case'pt-BR':$d='!]^@r5IAP*60dN.$qYzy.b^Q`d_@1;^SuFHxN?0g1y?n9WW7~[Ync-N$j#$-Djz!u=S(j3#7JjiT
r9=~_LE7SAVW$~F!)CW6ewA4Bdyd7$gK7wQ<
v?efC+2n@]aUFs^J5M+Mz!>jw)95H<~]l?Fl.gx^MY"HPFs;VTIRljTm&XYFCX^
]Bqn3GLre`oQjB,+V_i.~^?PY/Bn3BlBV`S.l;kFslYcbtx%ejj<#hG]PUJ,S5%nB^!
f8{:^n[m=?rsKno%`hqFfz)x;9mru=Byqn[Mrc4,KW8um>L[ds#nq?wp9a5<k]Mc)LRxz$cHGe@=BwhQu"N)iH}wIB]HOv{proY??s(Zk2OLjhq]-szi&GKq<pnahHIT}EAE3PKq^1$Q10{kK3gnYS%hk1T!s96^(9*OV.C]Wpo^d0D0Ro{3"mX0AsAq2*1b&gMle]^G:q>-jkR.VE@.R@uUiinX%Qv%~TAvoW2uMT6gFMA`Q6"_FH>Y?3Kj5AoN[rP1oU?
s]]0gc[rR.:9wYPW);Gsanuw^@1SN2wBga<
6XU.Wn167mS*4(DaQ2-s<3mi"_F3tj$ey;[HzsP:vR~xa5/d]
A`VHhdIP9#rh(A1sF5dR7-QhWf,P"h;DW
U"1!PZ_!>KrtmI"Sz6;X$yX%d$xmaG9oy<>l2PFkBj.2OvmBADFqlczb1c+p=E-$PIATpdBi?8AVbsC.;PNf1+DvA-dk@&y7f0Mm:4z6r0Uun:)_qL6eCn_L%yEr}M6)B+_p@q?)W.J<]lTfrUZH>o2@`JO6W]~f<U1a#VElFxM#Yp`$A]Xl9qP0G*2e(EenbRA!4h-<xW)[_r/iz1"u{2OuSux:PU|oWx3%;>Pn"gHc$H$b,-<i)OvQ3BzxK"xZ^n5$AABcbI%a<w-tZgoC$1nHgl_C#"FapGoAe6D("&B2jc~1_ZJCgC~/2m0b=tYp_LSI-Mjx
o?iM,27~4Eg7;
uk!rpY>Ql#!`#FVk
GAm$$e~Rgn-@$?okKZs1~(OU94JWI(xuWg*3ErHK,q9KDSQdo^kgL-?tQmI5>i3#vN$L`76GvGL9>cm
AN:RxHQ,LeIANJP3;2~byh
9SR+6_OF^Ar#sl#
Pw[iLWeF5@VBqH$EqGy^%FUb0k_nQ9SZv&Sbw]f<(xmb;^@Q"!3:NC/IqC@Q8g,fgMf#cd:2dd*_0{?oo#*^1D"#3Mf?O"YC;"w
$F)$buV($Q<}iy=}_$6Kj)3Ni3+HS[S3>;7!5X3
5R%4ckL7#,1Z3k,
>JRznMfP[-YzEEVJHBVD,TU$oT(24`f[Y`t#-%N>=f@,=?)<!Y*(F_xbJFaRM9FEeZj:9i4|Oj)[09AJTnxEgsj^c
9{$Vr3XP+kJ8
By#7HV:w3eUOaL/,cVi(:?,UdES2<rZ@eYFE<`%21y
[=alV:GHN6r8>
aQ)E@*/jJ{4ucd,[bjX>J@5<:RG2)"TAkOGSITrAUQe$%xw*JB]QY[c}ZeF/Q2Cn12WrhS2m10a4*^"grePw@i59P+b>le*F$XF"=.COmZTNPzg`[Hl;G8gXglbAa#eM_SZd+xv7FSp5Z|MFbGtb8sP/gW;<4IHO8+fwN^&{@--3V^*dxpAL4i+fW~h6xt`:A60O5?c.kKYS<?/Wy8)3QO><y]rPh220T*?wGly)a`^~$Gt&3+9^]N3kOkd0^ese::;E(J(6_{Wx]#:lP<EG3#KdKa.e<t*lN0p(x4."#sD].+K%#ZIpy@X.e0-}9VV"-dJ/y?l0Rb)p5RyVC$i[qJDl6edm4p#CX65K%T9-3Xxz%mHwbWHDr*P1::vF#cq_<x>0?"jFui+aURB~W%?zI%Ik(tlYuP0;b9pAK/r#n_n7RhpDY="4>!ctB.(0WOB|@h@"P&Vu)3##VN1!4p+V&T_o<XW$K=SVuF^&>6@$?h+{!y5d9.j2,<W4NUN
PfE8)~jV?+D^C2C[kG(pai"
?6x9$crcf@S:TBGABiUvI5I.XOqoq`8
+aQQM_S<]^sdrp_b%
1p:kJVtO"Rdsgt&~q>F
@^0G#as{E3[o_TD_Yl+ch75l-`eh"PPck,kl#KG8v7Ps-.eY<_9UaBb)CI5`CJp56?&9/q%OU`/M`RUig!69HkeyIl/C:[NOLo,Vl!<Ml`b|,08Du{>:B4]ovHbA$8GEgN>*@G(%_9OS`l<tu)N$5u.`t4^b=Oq5I|=%bE+W?tW&"S>>IrR]FR5kCSL3.&WakF^+aQ+QelX~xF3WbPFw;_6R9HhiTKZ70<&?>AWE2d=7B]Ua$,Kjn_/[,yC399b:TP6H[LA?R60~Vku~Us_zg[JSpRaiW`Y{BX!^gz8>5Z0z[:05@pP<-hjCfMG@v3s,L24Qm1l#@]$at3IBx)erZ|1L<6g$s3
(#G)cjDOP2Our.}
]wz[$sbbFO.R}#iU1]9KC^c>"YL/hQ&!Gh3HA=!gIcR,Qiurf[TxYeEr3LSy%X4,DcK1Wg$#Ir60<DH4r_&KB@7PDRhkp&`6Kr;F{E31}^OlxZg]w7%UCNO]t5TTk@&.![
wZBn9D`Z3@(wYp9w%Urd:^r-g)gfkyH>a
ZDJ)5tZN8+SLxwY6D6B!q}A-K*K|$.EUT+5Sq*2/EWTWr?M5ChlL_s*o%Vq~!X<JaxpgM_Gzq,8IS|cmL#k)U5knJ1l]LFZPxJxT6$RmRd0q^M2bUCP))0ZBoX]07Rh#%>6*mmOJT)hF,1-}N3XA#PU;9oUM_#8p)&ZM<:4ZO=
RY<
=4b.tvz)yIK&6o*9md)dpv@!`<cGx?b>>7vSGFQI{.eZ6@zc:L49{kW:3NoB!sMiW$X.k&?/o3m"K1/"t-{#U[l*kkv3Z[pZSa!;t3:uIjn((N_;)xQ:><R/JIp-lH}YE-g`,IKI{g^R
0:xPC-`V#TaZ6)-k@9H52weNOjy.+Rh_Uy#6qO<}){C&*bFgOb<(v$D$^(Z?!~8e9Y3{<;Zhk=^;nY+smm0z6~z!+,FCC!j$1tbr:r8@I
rLP^Q??]c&i1^zZ,3]K5R"U9<{CqyTm-wbp,Oz0cPDy8Ms63rA<.
Ybr:q4cO(dX8ey<dl#:iGPA]!M6]dGy,7(9/g2R+++FJEB>Hj3Q5ys3lMfEmQoICe4<.c#Qw7-Y(((?QcGa/i-DM/:wCj(q0Fn4.DWgYHwQTL3%0@v6s&.`e`)-ucJ=Q/f7bK%QwmQ
`,eQUR9:Y?44]VY8[Kamgb,RJiG@]YU2QS
]Uf:;GFR{[4<wOe;7]4t-9S%>(ubj;EQPj_u/_.XH6CUwT?/",D_Q#f2#$tQ^80
mrV=}B98>C]_FY$_?$2S<"`2Ji`i|9`;_pIR
.G/9j>2D)kgoxouiUudy0+7Vl}69)?F#1,fNg7$jB10yJ*RmHc04Xw_!W-<"PbWJ@fcOIAJv<3?_4-C{O
)tC.6;X!Mb!h_EZ:c$CR??-8EG%xwD)r"u>{uzHkXEF6FWhp[aC<"4CS("d&->Bv_j2LlR.t+t:K
JE-^dRD#Uyjme7|r+ht+8Y*fXm:Ec7&"PdD-1lfg3+4>
3V7vHYG5f!"/a
b.g,*A-)@5#-4JaYZxx=k"hS!Lfm#w;wX>aQlXL?re5arJ@=OiSA"CMr$)9FNv_AStMG@e8My+lt5W?jRix<5M&wPV3|eK,CVrns>VEX0K28*<S#&8Av3Yc%px/U-7l$V+?SQW</`e>B8QeWvLgAN,5I8
QB<2_13/!IS3CswrY*d)$n)^kU,kABK$*sd-QzhGQJqn8CVw>yT@!JqMHK
@,eXt_%_0xCh9!FyjN&Deq+_|BV4gRLwR&EmB"#Bqdk>i&AEb3eN,I!.:YbicZCuB)29Ux"P9I7tD&YP4v6uyRNxlOP(Up=YF+g827"(Ut#S@NF8R56j>Ym5>o{U,a1!t"UpUYSQ;6f$
8(3,rN5m%0+ZT|8d.Vyr6FEqu[7.Q#K2YQ6H.aYPd8obD+)GM/wlCS
KH-
ab#d6S<cU,>%%YF`-d/
3(4Jy?Ng/-?h>7;*I1MsCM=U<u.#bTU&W2,WxN*dgrMAhn:g(4GR!UTErih^D3&Wm
1!3U>gfUK+e2?i^abJ0!TRHOX&uqcEP1,30lKG25+8|+epH8neNX%Mko{MxhQnq4lF]6$60EET)pfS8fK-+Y=HTa@lHlU:kI2#:B=C)MH-jkJywS0f5F}1]2DTHFPSS-S9?G)a;3_K/0/sb$!+qDTp[e!(9Tu
M+pUlD)btqOfnwNu%*[hXU&s&riU>V)bWHgQm&?i709KH;
3!T1b%T2j1wx*]
9e{hT/<"o>xO7c(6()"j4[:.yjr&AwTtO[KqbY#;Fmrq&g"2RM^jj&T>{?yD^B_NC,K^]s6oIQa/A,rX[E*Nocr&ClLyq8,hj97LZW`A|u]ft2*cN3v.r)9C(vON/VO$ZU~2cd-JB!!<d)Gm)nC2dCcg?IKfLLg?>&ZpG_raDgEI&rfQ6rBU
v`:g:v@]xUdumOH6"MffV
9!;FPlU(hG=4Bm9)(SJG[Dy;[bUp.%tQce';break;case'pt':$d='"`GAU6LmD*60dC-0&k(.E[k&dVCW`^v%!L
G(Xg<`b?QFHn34fb=%u3
"#$YHmcB6HT1+)upjl3/8TC7?YOQ7liA%PKlHuoNB!-y%HDyu)Wq+6%$8:=6W[l>r[
J=`m(FJO^Ntu6e"B/Yhr?qg#Ux?Gk,nEsTr^e[!m/Kr:gsJ!3gQ_mx9XGct.vBDR]XfL1K>r@/>pqU9q63AK[C68GF
BFko%m6OoA{3q=C@J_q>I7{W6eg;Z0dJ.g5r-G^H(PI,HTvZ~0Jc<c
:eDbE8DEJ9nMtIv[L9,EG#l(rgJEc
!!loH?
ikX[c=DLg[l=qnU]vvI7-i>T8IV<!:]F8]6X_*UV1:K10/+UPhX1YgC<].~&So+y|j$LUX:3+6tRO7;j11:JsLKy#VSKAA4M;5%pfTVmsP.9w_+]14*%Y,"1*YIZ@(^6W0b4,Uj5mW7yQk}r|
>Hj63]^`XRtB~<
_fFfyJ).v)+LW!7L5fWs>0PrBhg<rtt3Q1j$W>lQ+#PR0R`$/z
{W?r|8`A0h&Z];hUs;~Q%ep#v"ul]mbL(p5R?i62)y,ieY|`zOnR{J@k/"~VQ1wHOT`"<^f>tlre|:7I%Y?ch^E1J)1Hg$|t"X8>>Y;(<`;?YZo@/RSGN:sIipd:CH9(tAcL#V#`[@5Z<Ap06lZC$!fSZ"7U6W":.x@c%g7II?wii?-]-D|JsgHsPa{_Cco&eG7uGHTvg]2^u@U]Y%?S3n"IG@4:,rg4[Daq4K+F"D$[ah<2Hjvx^0]/@ufWWlZ!SORWSG+E0YCSci!moIb576?"buVPaX7K{=Y5p#|@uPxb,#ZK7IRJ8tz3pX%3lnq,+]$IoQ0nYgQK+nSS6]1V;_Yx`:pe@yO@_++7!ae;xKzA@)-B4mL<JrHLFy]$0DPg3T)rm:"sj<:yBKWTH@k&mD/CE4a3%-`f>vJH=bnDD*[G7rPr~oL!+%|;KB_/pBf+C5z,YVQl8CSR:rux]mR:aE(3/g1OP&c9oNtJJ%WL<=U"Dgsdn@8NUTML99v>HcQZ/x~g]o4(UD/[6.ZlRi2&*2>w1Bz%r9".Wn09TcMrsFfoEFIu]Q9L>keq@2)m?jcws=13Y/0wFILJ>h"iSJDD5!zph&?Rk^NE2q07d*|I@PHM)&r0^h=mvtSxXDTtx57b*3@#3$"2Axj0Fb{Rk)^`@8TrbysiN;}S40%RSsw4rt8pR)^Qpt@g=cA9Qx<;#+f<^R_CgSt-iWWWEKT3R:^Olj#2456TPmRE|*tnRCA]G;ShBxyHal[?AJyJYnNQ`t%
&Cx%mQ>]/#J+u0Mp;kFfM0<o(I*9#ki#LA0Yt*Z3[Z?H9lFPp@`mn@kt/_s3OhNApY+?J_]4X_z<"^5FxG8GL)*0ep#&m-w7Y)@uv+4V"oGBucL"H#)sT7(3C$$iV2{6TYSjnPlaffi7cw*$I2$x_$d%CV)`jx/kJBOC0efHCz#nHVa=
lWWmcY!t=wahxoPh:=2@Dh>uWN
y@%Djad!*9t;3FY-~:2Thc7J5ahdmBx0#>x8_?PH=B
`ewj..<wf0A6**F=D0"3/?FI58b[;k(i28ypWwAN6>D4SGGZ4b@"P</mSud6Q?t{=rn+tnB]5XYUTVZt0r$pcp!7<L<Lt%)`E3C4]^_,X*)y*m,RJKyGD.35>vTl[q>
HGvAc]$+]l>sojy8iYbv122eSa<<.y@aQO[4c%mF-~-@fN"D17G#1R0tBo5;QPCPy^"8#5w(cI2cP&`R:|;x,~hC";EaDP/yNQ(P3#+Ff`>$DFz(Qg3N/Uw%$e"hQG"3[%IFvTv%tElKQ,lJ61U!E3P{&}8=#Uh{V`UAknbFpP1LrFK/Qf)Ui%iZb1-ure-hhs/9nGn7Y
Rht2TX5Mm0Y]Xtc!-2%ikl
Y1E&S_w/#Ol8LxT1_$w=5])FU+>@<W$.IdjXPWrhtOMCS?eyJ?W4$^rKn=^b@rCEbLF:jlxjbB,b;_!j|tmOH?Cs1XVLi05Z^cENwMysxI2.<+]*8v~:*c"E,)|*$w!qITT#u,=;%`=KD@T)bl?;olQ?QENsufp##Tt9Q8ur~hxX&Lx_1!ZbI08p1MjjOIw7Rn]r:dX9!K.=P_JtKd^%CF2BNPzGc++58TWda.hI2j}q<33.X_$U/[t5=36*NV~A%v&(t@U:&0#&DHG7I#qU7`FJ#*t.
8c(8I9=dp+yvl4Z.yzin,B!kXfm=t10f]J1o#XjyAr9sy?CiH.
KG6X/"h["?i;03:3jTu.BZ`fv0g`vUSlVq"@t)2c`g_e0?:1jsM/M1
Sj%8U636k5>M2~UEchN#l)83;j>7NviLh<>73v(d]m.~IDg<+nyAjdORWN4NxWw(d{3CQeh[+Ty~LDJ
:*Y1(Ob6`]]Ceu1%<KApxdH&(vZa-|wXRwl&xe0CfTjI@CAGwrbg3)U41A83h:g"ilZLd5X3revnDvR}qmGxIL,(]Lt]>M6+;}`n[K+s#}]tZxlf5!)0E=GR
xY^QZ`RPT/TxZsfoH9]6g%7awm>(M&Xb(i<):YtwUZ/`&sv+NTu(~5l[
lDw0eHiSDd/rq*C!2)-r/d9`b7E8eZD*L}=]h}aF2OmLyVOukcalT$]NR{rS
@D;:V*.+hCnD;//XBRJ@rgd6vH.5{VuDs!GHRr{..Dv-l/$Yu=uHRX9U1:eQ7?iNGo&bRd}xCHo"0_1U&]m38&/k1<nI.L;O^uA(;jXS#0<,h*.Gyr:b~YT#eBLfC%Dj79cy"-~1eNn5i({50fe"OZ^j"9NB0f^)O5@y|NLI))YwYQf_L9j.S-%f*M>y)
VH(`lv8%``KeE9Ge{TIj:qbXIJc9++p1qv+%*PN
W04BF:.8Owh4H93t{=wlfkk(lZG9Z2+!it5/qeS$S+G9g.]i:Nk8X0Z?P=;lcLw<LxFT.`9ag>ZtauUZ#+gWh7@)w"UbK/kUV?XB_g(({Fl`(!!
GEZPPCv]l(Z*F%c=gQcLr<{X*wpVRUwx%n|<~)Q:Pv5
cd(&W6oJRK>W82[$GjHqf,?N@sHVKIaBp5G"Wfo=&F)pMVzE?]okzQ-z#kDs`&%
YsUPO3<8jr/o*:GGb"_J,-Oi1>ZB~^N&e0Q1XpP<_#y91f]QQP4N,mrmHc{c<TzXHEYCn>5ydN-;jOoaN$T(I8w5
i`E,5eetSE.(G4;ir27Ik
kcNoC-gfQNAOy~4j?u%m$">xd3[Z[~Ixq"UKJ%kS[Y>#v$.;h3aqirA8$y1(_#ZRHJ*}6Vkesin&G?:aF-%/bPMF`qb3oXV4RDi>E;bXJ0RXMBXJeyT{47j?n+/"/I.PtHY|%$tKDp-x>r18Ng&ZS_
$bjaWAMo_riK>E!
"a+(w$3ia%^#}xg#.JFD"OFFPwIR$T[X-iEK,$r$>)m*q4OdHuw
b.XjkCM_G/98K@|YCe9PQ>B;O")&TN{0Nw"UG6j"Pm%KC?+6H:c]}gaQOH.Vvz%+y:t++I=;I7RT"+^u_+6EbsI1&$T/wCj^>dPBg-VQ%^](g--d.oT-:8Hbj[Jtx=7J;93-%5)7T`]@=
k`!rN"I!pf4<Zd49RH,G0rtr8>Zcc,_OcO9q#ygq&AQM!o$hO/HcN@}rMS[6.T}%}!jnFS}9WNm
:,/%.Eb(F*kup&z=t[pD9S_-H"vHxeq-UbeDi*F-2)*N)NCV8__N*1NWA@8=!w#*~"9bslJfYUfim`I,6wD"p4j"y"ZCh#5?m6R-<XQPQpo;w<*#+7HX<n2"8:ij0VDcG.7XD3EFpv=e@IT/(W-$8qaRyHUG[G"P19K62OC"oXpfE;,SVqNZ+M3Vv^Ioj*z/](819+P.=Y/I2jG*t"),=Ygn7HYG3*=)JJ#aI_Wx`AW&&pc[`YgsiQ/dA8)k/$!$LN)^v&$MXX4*IN5vIfES]0A?}]-r*lA-+O^ggxOP_@%%_5+`D+f7)]E[d!12P7J1&tA"I9`/;UTE.lZ8*5y60v|&<qm&J*.7KH|"<9f4&d#GK+Zh]_3<lYaG_
]hN%Y^|;?0P+!:Y.XJka|Ra4
PrQY:yJNfn7(*%yA>K2~P/+;?j^AeHh1-EJ`q$)64fN1hyI}0Y*8JAdmD?tJ6r+5XfOgOFHbd-okxSXErN;Lu{m.l,5fYd#5Ypp$0$?
HXYWc.MGD#c+Rw9>Lb:yxi)w)SC"2A.S6bOZp!6xg8,L7xt^i[Dt+*himvYopXv*xlg*F2Amu1n0?aZ]ek"#"CsaM{*:oR)E`DX`cyf92qjx%iXx#Nbv:m?NhB1mZb@+k/&ItYIthr5z,7R_nXs<9+)Z],0og@=`Xjt_l;DN#VqCWA<6M_p9oPEUlkY<xvY3*:,USef
rQbjdXB>HzI+VOVgo/kl9G[78&Fi0$8+[&>-k^aqeBruXb[q&OILcGwNAq7f.f7P*|Jwnv
0c6#gPgOfgex`).)d4MvuN!9*x_ZSh;8{DF%D^+F*a<
4+;QRM]TS[ua~MjQn';break;case'ro':$d='(]^ALaMD9,{0L8,%AYhGPlW+`E;UGNP8Z1i=M:hEti1bO=4n9XED
h8$l!/uhaK5;4j(P#AdCe}Dp6
9^ZckDb>UdK)-$-V"pK&^3?Sb?>hG/rN,CO@CAn[>xt2usmE[*M&_OlwDuf+`F3>+0Uwl/G4WQvb.%+?q#FDAH*Q2CBMRa7_QH<4f9qGmv<SZ[TQ,sK_I[jTsj]`a%]I:HcUmT.8/Ol2e=rSU(Qh9uW5`qs,0^jf<ejn?j[#w]Dybp)jISyZ.z+2u#x9T`_[-hJ!f!U/4cqdw/bI`Y*i]5R%jOo$pBHcX>nPi5MqbyH#h&q,aLkv]hfKU(M"m~(^]p1HAsiOpDKsxIX{PXS4-d02]V7IdrOEwv+@A*=j5tL,Ak&:WjPjmW7XA../2;hoyT])S&A%E3cRm=f8vftk/Omo]{h:u4&e5A!?,c!7WH61@89!y&lab`0|pSb|]^0no~mDaZN{.>lp
6(I02b.x[Sp+8@XO@J9[&nD)Z:})K[{p&,ZY-09!>>H[HqxmoGTp.3d8+,>pQ3Gm,;jpI1g-r?q-Br(ty%gW+*O]c[HI/<mwMf1I[g{8]HESmXzb~J(`f
E](5Mg59A@%g`7mbgIxkzh}7r0KAn8V[6=u^]Nto)Xc;>KS;zr~h/24XY;Z>^I^/[G*jkx5byj*XJacHo7wugeQk?<k`NOtef;]HU#7>
L.?Qn7-Xr>/S;dct$HHc(qfly:N@xf>QDxabOdUY`@c#7r]OF.KYwX?L_g4Vc,JQ0IJWobK2DSw$!spXHveg-]&15OsPQ%7bf>k)@"SQ(5,bR^LIFR`eg`#+G?*te5aNw"/s;()A.[A1g2WQ+46se#p%@f>qX6E8$JJ-IB;1S94;^]Zf])PH0-VM!P[Z,
HlB0?v]^4v9p2^C_hZBIT&ww`{N^!)J/RvTc7H[Ys5PyQ{F>3hHdc)6Exwvh6}9KLeqdf~IbSR2QJ%>_s!A^[T=g>{R}!FX=qQlPkwpO(/&wI
"|u.^:^z?&SK//3r.f/zun(-I^/9t(g"=_W$^_6NFn[V3}+r(fofSE0R]Gue$Y^OA<c&=7du7t[+KAou^9Wo(a,~PP
>yq(oc:n;FdRn&ltOe(!<6gePTpsrL{A3v=8LV&46!%m&dR_6&LP[r>
+0+msm|xxmpd9wD$*k1Zw,ZcZ>d_9S:6.vS4av;2yD,oWV&IhWm`X5Cjlu|u-lh`]b?5C]+t-+
_1$p="Pfh8@C_[^"PO;?p3U%mv6YtP*L=<2-RKB<HbgijpH6#bE1&sx9nL4k?%HaUObavPnq`#s*Q>1mR-+3+JYB&Xd-P"&Yg~3jnO++AEvwF/g?7#3(sUKn${(^]sbsHcp:!SIIGDg+(B
f>eJ92dnqV$BLd@@#6^<Vl&qgBc:7M!5`LxLw)#Dv.e.$;Y^3GrSw,s.?;(@g:T6(31]hXx[S)fYieyT&PT4TFh*(*qyAQwL8<*]XIsHU<(rQV<IQp&(R4x"{r?q{N&%odahZQVD0wrJ`67#we&+}E*b2j-cyZ5h)oaQ+#J&>a|M;6-$/^M#Ft!PRsB$a<*HthdY1[XvB/rJ//C]7fFH{R*Ej
&P]8MbXCPa*4>YLs+imafIT-nc@Kb_}q=l%kZc!f*
)=3Sr0%!s6nPybP!;j>b@rsu1WN+L=yv{>O;g[}`frhAs@FPxO[^60DvRTm8aNRQlldb1UT%X(>fAfcSdt2?nVR]3tEnNcaJm#aRjuFYM-SI})*L@G>_^IQs2)LKT(#>g-^=Vd=?o<o6YH?w8xlIE+wr"Yi2k.[%T$,r*2vA0lf?+RN$Ps&d@`u_h`7$k8Yt)PDBataN,0yZOYDvtAM^^?qe
:xCn:k<!3x-GDv@%p2Db*GOi1>JBAo-;I8:qbQd!ToS6g:"p!/r,0+N6(MN2hp`S.*e.CinaF)wfMA-#?o"</rSfAFO}N5$#%t`eQuOip[[*qy0lwD,>e|%N>TJG@=qws.MZm00Ok8`S8s,%v}l}]#A-wo@to>&Ibd%;IH/S(>LQ(/3F>YEUx//-`$/mkzw<nXZ4""DO2G4"@O)b:Z!>uH42OVhxV+j"=0CrGA/|Q9Yr7xI>7?I;X<G@6Lto+lJ^k&&XjNhGdn_MR5RbU3$.^2U+)]vcp"
:T
FbZS+](t).Wyq%A}`M25gR^E:k&6Q=f"F3bl5DQYUGh[67=~<VA!W$%>./jw&rf^T.X$#9[,!WNN18S|QzDIm4OEo$qlL|R4qpM.x}2[K
%b
mnPL+Ag
:e)S,vp31AzId3*0s-WakelaIT*KTip.Pg#K)5bxO1nHQF4,An3vdNWX24HDPrg)lqE^`>:mpDYa)>o2:(u/]q7TanBB=.f5qX,rt-d,lr:!&]}LJY)HIx-Q2++qvgP"~e"5ND_c[%@JzE5p%
rv&M}T5c8o#rt37v`jpdJN6^Hpn4etMk8Nr<X%Sr-_W@:2H-0rb=~5>eY/p1qrT/T/)p_wPL"pgh!N6B#@`4Y`TW0EWJwD}g!%^jP#WT19tcxpQY18v@.&o$D5lw0n;+XBmh!kL$17Jwp943C,I+xu^I](6Q0.au5L0;#hT#W=deY
9$^&B"*r(aPPt>|*Bg3
:9xoBb(./fJ:aQR1,7cr)=")+^OYOtWgMv7W`w$*wdH6(GFleOFjeSkEYjdIMXd6Sjy(2V0/+J4B(d6OR3e+["4bRdx:jT75b<3x:eT<`k}F~dcJzkc:(7DkoG_)wAiA?ucVS":NR;>#M!yN~
?5y%>.CD#.N)I0nDMA
.u-m::DDVcmY?KDvvp`NRbb|6S*+/b^w"kS(h(e[gt_UvduIaxjEpuarJ2jtli+x?{@X`Ff:XaX:]UD>qTE1MghhllGGMA?=D{Vfu,%6_63}1:nXrrizsgr3!X%~`?opP<I%A*DAsdO+s~_{l^Hw/]0R2Ui6[@MR`:`bjQR_yQ3xI;yr=I1W/

&k.ij`6(k,^PXp=1HeOAWAeIqyc+|XA.h)AqJ4#yn8hw7K/5:Js4>Miq~"k@Ie9Io8(K2xEhzDVLq/[-"jfWt+Ta?a2HI!*B35D^Z`DOJcc>j"c-*vb;+7:MGJN7%;Vu8inq;z"$!bXkQE4lYj$O#`(T`v_YdtM$X(bjoiugZ;M#7=Sb-Ag]it=v%*,I0v_0t_C+n/1?6Y:)h2wG|3k-ju8tek;Z<l>rI*sG8J:m%?9RosrK=>#1[epe"Z2N
@qDatg#4g@C_CTI._A70Aj>isW68>tTpb.g--HkCq<c|m[7KnpZJsrOwC];H]M)B(keP*lI?C>2Pq!j)97r!e|MA;WyqB+;.1/s)gDDtXX4_5vyV?o1|ZDd_PuCS#,Q<-PK<>ZCfj2,j0"/7HcL]8>Jv!dW6?&Upj}jEPHPNeaX2rI3G2F>i6I*QMm>xlV1t3V6{g9n]tWnWclhUYhB=&Qm2?)=a]`<x3^Q_??&Dw`db5
3Gu34v3n_^<O!S2;@UX1F#)(,xQMX39N:`w^H(FRU.0~?);*,1jbJxP[j~RHU5lgDOfh(H*p!XmnfOs!#kDi;;:KC<OtkBAe!vs9Xb_Nl<0=26Gm,rDowVogZw
+CR[WvlyV(wrm_:jT:?.F
lFvozE(qvd6:>$Cw$m2!CX<M/J+I`>]"vgnc]2:lD)ioL`.Hl;vC/68m=mtSw[T0Wf&?N(LwJ<5m|Gpwlz$NTrGEi2:1%7]y8E
`QQ6ta[X<[sDo!=#w"LYewxq]Uy-D)4uLG`(4A00%{j1&{x)dv5:x*m(yv3s4iaFL`>l]`
8*4M(Y&kPtRYIu-U]0*&)to3K^Mw.mTsIsd2//jk"E7.Q0jr0NK-QM~o8Mxi08J!vy<wVh?FLP0XuO2DF1{JEe]@*)JTp2V_&s!M/y4AkP]RF8iT^2UQ%:HE3tFiy3>RSUV7+;&"`@lpQqOGlE{qjN)/eU7pdP%GK)-5s,<t-WTwldI:L;eatSrxH_<:;`fFx.e_:8-u}6
j}-A){Q),@;4+ghSo+V_PkNb>PRpSveF<_2np*vWwsvAt2:Z&4?_VY-CW5Up?{q_;ETFbepI9Y3
_As
=k3gfchrllP.JPS(F
rk&I6hcctpK1q@@>e?.TuWT57,Ya*Yg^`<cq/%$QYwxFM`R|(#7,/t^4pu.5@pvk=[i%Nd_"gHgb/]nqaJcSeZ/p
m;^QN<_F63t2Sr=w*=vQzn:[}hDbP0d[oD`D|D/5hK*[*J]hdvb=Y
0[t@v20?qa%,+sZ08t&0My4d0<)
zFHu)X4p"ghZ?Fm!"6im|8_XO]Ly$:5sVh_Xl4]-4((]*FJ,"n?!Mbz7A;ra33K*`j,y%78/@@g>_p10t^WX86><CN69lT+Gx(rgH:c]Ora7s[E&l@S5.vm<O=KC~D*j#T%H0GU;Py,E}0^+$vSb?F*YB*|s2
&R2cGtWXdN#(Sy]-J2PK_uoeO9B8nw5DGxdW3,bKv>C<<MHQ6-?[g"kq/iO)aQ|k+7cDx>WcnD,jTQ0y.I#xmk5N4
[U"N1O!hrYPb.mdKpm:0BkW?EJc7RryT-.-?}T"`(8,cd&&S
2K.(C$UCDZOG`:s?SD/M=ZopPXJTU
aD3"q&e!g"$;_euv@Bi7qTApMdU+%Q
EaH[,IV2kl3ETT>S6oGx;
4&
:zN<fl6O$R:0x8Rg(aL;Tm8cLh@ukk2W*vFnn+bi.3AMGFWD+Y=PQ/C(JS6RFx[2EAXti(`>.ji
03j?G"2^3aCO+2`AYYcv[TiqGorl<cp:unM^IqpAD-fMx}./+m,U*twON$""';break;case'ru':$d=',h_Gg6l.7,|@$peDI5VEWcblvaIPu#L/l"jKmW~SGi/-4*L5y.5qp.#bY+zh;$Dn&9K1m6!S]8Dv|spchc@EO[L^dD!g6k$
dJCw~3lt:?)"sDSofkXb9@6y,=17VS&Bi?bkQ)ya(("!B@-Q<q7//OhxL`.IB3,0ix3w7tcQWMl,T<y?KCs<IUUTR:}QXi|dsDW4]K#46r;MT7h0m3)eb+;KFOFWSs$Tnd.PqfP;KR0>IK$,ePx;wb[e>,,J$6]QI3*yS:@X$(FA3=3G9"ad5&.[@ozDp4y8[h$xxr6>br?O
inB~r+95yexO65^9l
&r84s%b}eu5|2SB#CcfDRx3+^|]rh1tc@,hPbdk*](z%e4DvbPnUS$a->Q5jX;^)^Ah@b]q
Fl`kB9yMGV_fs.Kjn=B}RW>O/]EX.:IRBN3kg>7Sn:y)(=x1ysrOIT78UNLpP&2C5R?}4RbpPgew7f"i-a^w<~:f002~`6-83OZX@J:@D0Y,qw?6<T[[QChMl47@:UN}iT+}G3q#d@ud`j5EMutDj`:9xQ^)d97_KjFE-6UQw(iI.vh&a~&mI(eG9IpS6b2DH1H6xVa7Vw+`sgJu!)7c-4y|DD:tR~hw*qx70ENk[Esb?h>JIOfM+k/]:huQw:^p)OK&++YWW`hK:xQnogZil|@lQ]y:%fLq>
FVhg(.#Y?`Av@QK/oRfjSKg[:?T-C|.ib-b/_Nnzz)_c_)Tq&tvwZM9frYW<"rez:D![Cw.N5im^!hJPg`X.^<#/8<s8D~3-(o]Mv.QwsiVWt}k5x
1qT.
"3W:wLVK@8`W21je*6Xe"H+ra][1(ZMLP>fQ{u-X/sH7X7`r-"_h[*g!gLq8|ZWgWlZrMh
7%c0@BB^i`n>EEWAJke|U{*
iVll=a^0)ko3rp/%.^j,@$cZo_c4^1V3o%fH_(XsBIs|h>u1A{AVM~^E^1mv,*;+p%HL]BOZ@8I5p*pH:BVJ.82A:A"m?Ar#g/(e-pS8:r!%n=0{<jx10PL=DIWWDGP(X.a:+y)!XM&#Iz1EYn?
W!Vgwp5H_9tB<`ksx?2QFWZh*jyWZmDDin*}p/lCx50192HQ#~t2
a09kOtWfWFH1xL0S$(d*704@Si-qi8R*4rsZvKv3^jm,LBLF]lv2hXP1U8
)?_W:1Ne]97vAFi/?`Q!8-YcTa+]0.1CwZ:qZ"ZbPk-r!x!kux.qs)(W&F*@Km^+0,tAqtbtI=CjWQ1
5&-W+s/R%$kQD%OYD|KCRl3m1DJkp2Uo/gNm:B.XC_=e?T]9*j,GY+pk(28BpytsB9qYA/bdwTa%I@,b+ZoS"}/pa6pZ8Kb^5L7Q,pL%({72k#/d$^7q/25<3$.B9A*V"rd8Mdr%&RV]O1CJx>GdVTpcKi!hELd7xq1bO0QW?lSo0lA~Q0?ns.6}g-4:&99V??I0)}K`g1DsH"7U-h>B==Ack2a4_z8C2g>b!,[/@wBBF%liK$:$.<XUrSP>7b%58jFRkcFaATU/]=o.U--u9./kjbQW>bDi,fUcHOvNgLW#M2$u:OLG7F]QdBaHyt`~s^qKQQ/bEu1{lc(bROfHI{6c`{d&tgM$A]7G8U47.6dGZ-(bn!cacvpf`Dx4<@(K,9@%+iS7fZqh/06yhu?6+p5g;qk<[oI84[JxpvM[o6SqWuR,dSdg
n.-$]xi3<!
Y#(0Q
4j<=^kvP6Sk6VcWL#cY)4}Jrfu52jSVLP<;}:5u(B:;kpjH#_3:Jf9lSkiOdNcr`$Q@zfl/OjW3v:uIKsFQ-TB114><?SNAqc>Dq
YVp.wJ}Oia/(wWAtpt^FG]*eoHRx&&.f<M$p.Lm$;;X_uPoEnur%S.)y
0M60U3"}%2Ah@cIn8VsY6W=2=*X">KkD4(wcDk5>cL[zQ_HO<o)v]eYm76-|g6aQP_dNY9*EBD1H@#@XZ_k#R(wNl{J>iOkIC^wT>yC]3~8{X<w)ihjB_wu.hrel/~e{%:Qc
4pwRY,vj6U5vo%gn8"t8)iNPD2VPZidFL`Z+TQKL}V.iAX4:wIqrTk4@OeN3f-g6v[:Yj[C<h(p&,`$=65P/D<)-
q#/qB~hKBJM<%
G.gF<6uK
XcO#[keRpLgK^eGORp@875C8WPi*uPJB3Z25FZuiHTNq#<,Y)_"5<@?.++cV1XQu.pcbz+s7g5w2Jvt($s+:xW3/A32t<tZ`iRDH_W6"Z33sOsPd8
ZHQr{c0,:P8vGAT;nt.l8E*f,SdqYb3+QebuYaL)Z+,4A.[W}96>,9tSkeQZzJzr}8v1)](`a-y4MsjM%Gc7yD=Ts1y::j,?hadroPv%4g#@jsfT=0vBO%3RdC]M3nl:8gD^%5tY{+jumO^U3tQ6E<ETX@,f*b}W*J2&NCyD`[6)F!i<;x+=ZCeC7F5frm@
pi]K"Pn0vJ2tz=A,fCU
/<i0A&BghQ04B0qb7k29[0k:(j4J|46<6p0D2.m>ZGI*R`TSqIj*08uh!f-ph9.?QB<wBw$ljK
l(HEip"
V:JMJyd_p[QY#$QF!lQN_}4Fs=?^5g*8uF9,#Y@jkY$IYHR@4zBzCu*~&8X-5$#poj+T$`/bc!/8
x
Z:D.boQ/Md?oflPHa]WyfiyC;t!1Vq88qGRJwZC<r
Tjp-i/J"FufZ/1mlK"tK7i+?/HD!IK9Sbw3>5-#h!&]onk]:iEyG18{F<"SHLHT
u9^%Er/td.d_E3}u..qY#"nNYhc?BluJz_~nO_Pg0=]I~NPPHo52XN#hKANohHb2nNmVA%L1(mY.tpOwO;omRV%dG#%Wu#$fkD2@=]31lQ7bPQO939xQEP:A},4hx?)o0>AcXak9zoRjj]td,Wl%k_~pbMmZl11#VfCsuT
@OFDUh?YJPq24ihFT_%.awu(x<l?AZr6hDI.v60p.~Kg[aPKjpxbo
M]9AZ=UEl`=>cH@zTLTstw8+t3Ub&0D3dXvD=rYSLJo5r&W7p!By="8207QH/ZGb%+oV5Kn[bo8{hs8{dv[:l(7z?2JwX414!Qq`.Za
YP#yW8!zm"a4<etQG+!&LFb@;S@ybUTQHOP^^Qq_`G.G6qwo6K"{[aiYV>od$sDV(]v^%[,?xfTA*ZH>bVZ3Yh!<!)Je(I""PRjn_3mxH:BE`uxGZ#TIb|"w
e9,EQJoB:<i$y4p,`x}xcwy<TBs$mI~$ixM-S_gK"Bko.1,/@0WC";i_#T$IA[}LJ>"[I#+JKIA`td)L~5Do{Zy["k$"V=G:1n&dH44f."usb5T@bhWk4TO,gN7iuy<ms8?,+Fgwl+:m78~G}=27!&}N@SB/2Ic?5J2]x:UFk=Z/)_#N}Pi&R,x6_64i55d*}XcvCuoqdlZ.%[w7(Fv,Yj+5tYrutdj(9H6U:EpJSLJIzv2jD:1GX?<>3$QQ?,<HpPs(D8/8S,
jkElG3;a6`iFennML$J}`&bo<pwi`R8,?#n`j8(<4N_3*eo+Cli[gq<l4sV;a
Ygd:[%VGMrKS_{dRG.Xi,X?0%y,1)}axFH"P;b9b5LZ2#FIbt*mgSnu9T_e[L"+W/*mvmN2Xu@F2ImZM4FrJ)f6&q)J,IG1[yh
nsUy[mN<4"|[E9x;}pu.@=Lknn>*nAZ"d?bu)LNniRrVuw"CL[|pW^^j#w8IWPg+K?P+@T>d_SeP}r;[
AJH(477?FL$TYLMS=
>4]e]>7aqDd7`{%!XoO%wVOI%@7Wro[Kmr+53[b=)?Pl=W]V[B]?p$aDCKGq0.U~L=#v89e?hWa+eqE8`:Ii:@+e4S1{J>B]Ftmhj)^]AS&NAT=[_.Js;Y="A=klMVJO#UpqmgZQxb3:nQ!YN@fS=:NA:7Z]@j(h!o=AMNRPQ^<A?o>$+YT)3m:IJ5aQfEmVgMJ2"7,!onXz&bJvj%=kRnm9m~HYuvI-62eowU[E`NdDf%AF+y<_63E^G[xo&B"kj7aY3gN+y/f0$xDMhHOsE4nQ]]uxPm#y
)^p!#&Xu~[J:ZVw2lUu89CiT$
E)eDCf_2/)_865#a~`)xmr+FHF;"{S*(BuYl<"p$g)h<$yb
Rfl/1H^^?O5(J^WJ,f/[u@omQ<UB,UolSa*l%?RcIdqUu$FJ]E9O&a/I$RFnaL%[OwUNGg.c:#$xYbT+GJMV64%7XuQ7a*S,(pHbcYM8*sBdreV:c6qOk/~EPi`Ondu1LyO#kih@$Mt-a7C#Lf|N?nq(+DvIV"K[,Djb$(n,T@[,el%Q/WzJ9[0FIN_>`KvB!x(xDqmx4007-hRMJCwGO1C:4+i#r^CCqo2F9h".r!epF]M>#ntmQc+2>NRWwqzI6mV%HW,@3il)`A0_q
~W.X1?o%
@<O9M"k:
m4=[P"Q:KsF;xbxX+"@hIGBt9/+%.)w2OQ(7`:+kKRN?A$B#D&c(wZ1+wMD-[k>%h"@sacD)bGz_4A8Dg`[2cG>f
_t]Qp>Y@SkH?MT)Yo6:8<]RvJ].]V3f@L4-wLhx#S$dgv8.H99uewkq,"s>Cf"Y2JU9oQ8k}sh:y=/wUS"Jei~5a%XJ/)A3~>^?|a
iB1c7
1@2-KBhwBqwT.td/8`vJ(c/i7M$ltje/4~YJezLMR:]K[f=;UpUdj3`2TxGx;H6PBVJZqV
#F^_:S5f],Y#fQwgvQK"*.
Rg5XL7AZBQ4{lURFvEF=Lbc.A8Rl!<b3mA0jEU-zM1@-Kr2>jmW1=^ky4$x>.2d`#oYVv`2=cUh+R`lBk4NLcu&XdJ(C#k+BU[eJ#WMN;JCMq/Y&ZvnW[3yIxg5%"bZ4QY4m5Lm-dZp=Q,1>$"h,9#yNO:1Hry<b>A_dXO@>
!Pr<i18[O!;a"tJg0U8OW@flF*bs_wXDpG]^C[U3K+7?@f8C23Cb*NDJx,dsV4mjp8i4}y@9>5~NwiM"o6Kqc.Lq>6j.>`}7/oCO$G$!H`Ly>(mjI"fNIf)*^P5e+`aTxWb2</,me$&xT_en5Mw,b,Z3J2rqjP*r3W}@7j:J>x|UYrp&g0Cw8C><3xkR+U3k^GZWExt?J[~[ymB0Z#t1o]xl1qLV$^@98yBr_$,F2A]8Jj(ZCO0BnWK)CETYisu$n`|"3NXA/g3K2AJkqd
1
tlT~0JjXBurdnxPz4v$av9i%X2q?L(L(8~qfa#pw]%yHvdG3^UmEw3?yw#Q/v"KjFEJT##Z26p$2IE?E:}j"y^<qI3hLBvFn"at;oIyQpF+|8H,>kvcI(2:ZG+]x_(-#N6Be6UX/=I8;>)w%6z`O50mYMT+c.@q;3q^bTEm*`UC-BBDCM`U[cM!A<m^_#}%ZN3ZkK+9Po&BQek`wKu4QXY.E)y<l<<NR<dxz+tS$_dK49U/`B~K)0S#]iwt,xy?4,+alK&7t6|isk,
z^-$(kqMFm*rzs*P]YXqC$R4w*NHWA1nU0xMQF_lI?ih1U1tQT(<NmWTAW`ICt7>}*&C(+hnHCDux
C]6,?2uJ[S5mkdVmcOFt+;xPPCT9JW551UtF:8FRyM,u87kw;2ZDflahFLNR1RfVsMEp,v{HPu"o)';break;case'sk':$d='!]^@iaLp]B}0lN&$qp!!B<J(W7a:MS>W_e]?pdd%o@(_T,-Wlr$rF*:wa)^fstn/mi_?H.k8/9X)fp]MLcArlxxCvSmhen]`IA]s|5QymA>EHo$K|>!Dx[T0]t/3`sny1ZkavJxX;`V4!M:

w{2>GHewm{BuXQ"i#6t.
cp"Fh
i.|_Tm*$TkR5$1Ppq!CG*CWk"6/t}-m<cTeD<F"gi`Mcvt-QHc&hWQPFLMt5k4Mp|)vf?%wF|GHI5.>Tdr:?-qfh_>`?h(^x5ns!&6dnEz"4~w~<bwhvYL]y@w=KK+:hg:},omB+pJxjNr6,Mc;voQFwrnMx@AUw?TT_[:t98%94@gbp>Vtn1opW?[Inp1+DEHC[SC[03uqfk:MN7s`heC]bI`FiaV$Op>1k7B+Ji1|Mh
n]s<je|^BAnUmJ$O:f9UR<#W[=1p6pCL[kgr~TMT:hR1uB7U"!?>E=.moQU6i,)I$V<S{6D+/.Y.|/QcegvcBGNf=9~QJ1Ls;8;TD@A7,hqyqKnB@lArJA!>8]Y?~#ZGEpan8jVh5Xc]CECZd2}?@D3i7f.0+FcbW4j&)E9_k::aZ0(%Pt6
y+<TM?QyS
Zm:uUoo9*_p5e?D`a0C5/k--{:krmT{S9:4m*+ZM!2wRg@3C(HW3i73@)LGVhs1_28~Sq1@w
E"(DL5uaW9:IrH<x<4Y5Dz`iJ@xCH~3?8p*^0EuhF$lnU2;wVc/vsV*HOqn([JE?ah
Xr_L~pI3QMXrEyfHzIyT^=qd}=N1.oQk|ZsGs!fqAi1VGL+-(,8l,7EV}-e3NBEicwl/(-MsMUL#[&AMac`<c<M#r1s:cA>?KD#P)[p&U0BQNH2P+`j$1,k_2lYt{^R6[u^+Y`Mi/UK*xi-3..wg-u97=.NK<xibTBX9BcuY:pHIUj-i(,mFATTe[34jhHOr_<npv[PMXc/L{tG
OiG"0XoR&_
dA+i)nTGu%Xlr~Xx_y4>A`<n33T(jf@Ga]^uNdg{??M%@IS;$%TjhibP%f4XPH8bKt-DE:_$/?swF&O?q[V6@sp=Y`G
K,WhQo5=TUL
(]0(ZFt-eNZy_B)wP8-A9AjECs5C&5(v2PL7Q/OR[+e!`|L+*9#]dSs0U^YSm6!Gju)uKkFFR=aue<6oysr#h34Jz"]D8i2N];9t>tI:kDBDn[#U04hA9r:5;Vh]QqsI)M+eZcxf$a+6Gd=AD5-_s{YY[sImk}e|=xmY9)@4fN(WQ7o~]"%ZRQ.JbFArE[gB5a3rV*dWl[a>$)z(1:DQcXZq`Sfc`#+8PaY-*{f=X=`nsH%Sk9sT.>-#Mk4;Xh"Nb!?V_y:i6I9/mpV8=!cbeki=K*
Cr;t@4#5iqf&}]{41x9Qit-NSdH#DbT;arNq"5[u:o*H#+fcdlPP!0+h<X"t]6YGktHZN4q:bTPrJZkF3e9B*pgz)"d2QOd=vy#qK+/,a(".0KF3}g9OFY/<IGwph_2D>a.P>FK"Zhwv
FxL2S:]|(_R.?$QPs%50G_q`s.6ZwGA)9NyZ8cPeo5"=@An[FWB}V
0I-<IyI@Y2aa72y2L2fPU4<|MY76#3"n*ZH7<z/<w%=,m]X[M=mpnF/`>h6Z-|4#QPD6D4Gk6[a:7$DR7<xi8BmU6.>Xi!U{d&g*Aq_n@j7&JW3>:{IJ=Wd"WKuW>7RO8V#`9"
/N;:@CXc8Myn{Hg4:.l1tk5jJpN/GX4,B?`;FWU9xQ}XFuz@&x_Fb<{#cd,c53bT=5HPip`.vH>hhLlw~%Wd0OfH-CdOtvNryATK|*KlYSjs}@dZ*r[.f17i<iY[::muq]
w>%Xb:JWxaZQ5uN7)(.9nfT(9HpIqK:UE%"+$!36=!1a?&);On`._*PyuT;9b?N37_4&p8k:%Q_H(&GZ:+=yDgO.D`xQ4pxyde5`tTmYNs!!W%+1K^W@m"N%9do;eSUhDD)z,3pR_2$~8Bwmnvsz={Z]>]4=Pu*wI=#qkN1Hg&g>>f(+N#TiNe#.X]uT,tKr>*-;)u*P[sND"<X>Gr;?)gTd4{Q#dG7/A$r.7l@vBs=)iSvHYr7|yVk$gST~8:PqyNJ3-VZ4L2Ho/*dKp9%$4R:*B6$bnQKWA7*V+V!,=8N~)A6HMI_WMqg*^R@~o"%"k+,M2(<AsSTJTcmt-*PY#"ls*w.RU;GjikS`_
+Y:?hAGoMvt@:f:5v}#v05F]sC1?u=3~ZqX8jcVhiTdaQIT1Y(8oI^Gb]>!1YHp_8&ry`YAoEL+fOvk]Q~J=gmWpY0Vd
Ih)])PN<=uMn^?0%R$7UZ3yoZonE#BGQIf&=ZVdV%DA#)6B2q#9`3qS59?fNR7KPlevGr@cH}QpD$.z9Jl*5o6R>?/E$lRkom@qp+^L6%
{scQlP@h)O6_*^6r#)j#/%RZ|3PWD+@[u1WZZ/nN$2K@E
l2hJ8cngU>d/2M&AJ%.QXr^hgS^mJDv?RjCQ$k8.d1d!dAgh"5.=xd<W%QDjDSg%n@BhfdaP!h".(EhP5
N7AS}W!p[yWMExrk4ytdlSLR0x5*:%_V><RhNsekr?>Y*r>=fc>iwW_>e(u%75ip|Hhc"d_VUHr->8l!s"M&9`N8N%W(j?:9d$L9*L|PKt<a5@b<T
m5;EyS
y|VbE?$,r
6e^IxU>xCpKtY0g|P}-GM*Ru;5C)htqheoO%YjBZ+;mEVQgy;*>?eT:On2I1DP.7:&NoVk@J1VCO[#&0i%Y<s-KL#bj@t`1;eaZFD&,1:sUawy)(R;Ts,:^qW3@U={a/V`JEBi#4;/,Or)L9>5<u3?KFZfe645r0*@#pkCZ
o~OhWxf<X4S21aF!gEN.4fUL)]PogRfs2XQPU3KR^%_8Pg,yhIW7^)l=]aX#;R/]W,O7eyeBEhI9#6dM+[k]#O4JR?R)J!0GhwB$$Gwjp*Wuq*1p`Gm,$(7k%otmTWbFIUq6;FJ>Pg(c8iRi/x."3*rl3p>H6hb:)sw2NC+8Z?s*kiW/K5/9LBi>W2W@Y@=Lv
o,]dy7YT7YOVLe32l#:PY*`7@5YJJcunV!,B8]$P=vAL8_=N9s$tYKC*Cs
#eBou;>l")A1}DI#Rd1QAuc38PLb6#MHF
#k,
Ziil10OuL8RmzaI%YPNQPu5:+hlN-dLoc27fQJj$0Y*Us_}vsM%cWkD/%kfDngQTIxZ.kg#llZ["w&Ih=r"CMu,0(BJD/ZBN*3]V%[[,L$E<@;R0TC.esZk@mb_;4J-.5.q([>glpgr/7`zrr4bQF0|#0B:0G3StP7{t{ncb8KWr|qdP=?
k3OwV9;};#k"_,C<AG;9(i2f[>>|bzi/pc2L9vem5NXJ87V&GL>^q!hfvD>B$8;mek3mN{J#6C);
Zd3
@?@VlHzf0DBPy#K6r"E`B`nwGXc>jZ{Am%_Jw+9I=?mFd,E6)XNZp@c+o9Z_+3QNTyXSH9JN4`e-SESmO,1>+G,YNN~U1oo"LX&LX
L%W72k?%+:-o,B;aIN;O.R[&r?Ks>Wfuec+9VZV)I5#lS;gd@y?@s
*_V,&gY
SLv>5>5Z&//[;/6*uT[S;ESiuni)R$Gha+-818?82?/){""/Fn&,vpPp{s:MT60W_0<Z*oB5|Y!dcZEAAO;U`H>karHu$"2wCHiwEs0/iG-:koJw*8JP<`Q$C+&[eXy8NA*;E-;So-sr0_4iT^>(Is^TA>"+h6>A&IDm=#c
zETB+%kD0QXYG$2ndHu9%w&+6Ty*9ToEen;Kw)nqiiKwzL4!O7.GB#)GN,Q;TG
w;eyH.C?mjkCW3*`kM<K*QWHOc1IINKRbve<SdaDO?dqpeWK3h2)/^gTj2`J@TU"K!FLQ<C=
+q>d&pZ!w2z4,To.vqT"7HH1I!])".27ILR"&X]&XSPcanXl<#bN}:[yLi@-Luu.r0"k4?*oCv>mSGIG~=*94RMl"IWy}J&,0Yc*WOdwIhc=_F:F*!*mdB8]QjX82d|LOV
:J9q^G5)FVe4Z?L{8L<xcII.B^#c^"$5M]#3^9+TV1g}n#"fvF2|9/!U,ZK+2eLmF&ioA9n"m.g%ls1t&@o8h+EHO:r~7L*1+nOw-a^f7-N{sy/v`RW"F(s=)r@Ut_to6|?|i`xS:@@n!Hn:T(#3jlA>C&7UpBuk=)=fei$/cC"?+dV9JQC7;AEAX4KWizWWfcF(=EZ`*IF,0$nZ6rCD>vB9RXD2m~HI#$^1ENy|XMEtdORm6Jg(3NY@](aZQ6M$_&PwZJ2X@ih$3!e4D(GMMmVPNJhI)hf>Qm@x0-]+)`rjZC!;[?8m0tU,N+=L/?QVU52UBy7&*9RH&4nR+JWf!o5t1+yAT!GcwG1*U1N"QcF"T0mjcFEku?7p*$tj]zo@AIjFo:d[E1X?fTrvrhKS>=T1gb9EuO+BiQuiREnIlg`D?#
d$D<G5~APcIH8%[[Anb+5aD"0@@a,6kmu56rWqv3`:J1qD*Q:y^?y1?w-._g7s|-?/}tH]Gt7eLfD.T>e0:=[>*<0b<AytEC#x8SC518@KiD$@-o+vbnGpR=y<~$3?{907pLW[^TVV`(F1GZ>L(.Fj~[rPl/!H]
QKd.le;3lq4[1e5TR@UPRsM8a(KjfSo+VT132/F+FOG`O3aubrWOuLVjqCv9vitfCownR+9g)^T0TrnxIwtq~g5A{r}C
G;h9d)u|r}J$r8o0a;44wK*qk$tY5(D]l$^UazTbjhsvm4@8tCifs#qQp!k=w&p8r*2)RVo-BgI|s6t*o^:/hDK:nP7LcX"Jfj
FBPj7jHS#Q+c}+"y&w8Ab=eb6YzS,st9`oj*u?8s%>Pv#J5]UOK-^rZ>V$qq)fQ%,F<CESyB0LOtMce';break;case'sl':$d='(ZuB?aMAP*80
Y+"8JVWOv{nY_?W%4s^864m4.1#BXwNZv1cAn1E$m^r~"N&,N,;H?$yoDD_Dk.7j&KyhyYHg*nd4#7pz5aD85YMq:gM@,Ij,J%R[vsm%
!*_`O0zn)6,Rue?))B5;KxyRinfQfa9l]v~rew+AT[7&?_.hYiMpe3OO{I@wYm3WOv;[6?D"uY";ydvUDh_X%F}I-o
+;rHuVsFcL&2u(JFAxo&B7t)QyAr%(V}>As_k3q7IvMRvp_NBIi>z&Ma5Y7Pqb9e/OM>![hsK[qFDxxA(+pjt7?EMWz(MxSSQauz=LIqkz
3
WrH/MvNhqc>3eh1o^ink,Z4<D
yB:c1X1Hz90V.mmx>gbi!]-G[ll744whTDn&i2GXFkn`@IRsKu)hWo8Dn3ir;G
3
yG6{_<g~0_1OapVq8hI&0MX1gf_vN6gCMxs:uVH*s<Op_@&hh
,i#Wgm/.N^X0@/C@mE
#g;?t/Z:nRlGE;:JV6{lg5[0US/]KYv.j]pJu&zmV.tLL;GkiM-iZ7_+^T5B
W:A3lzkV*[,r(y0oZ0K*>P)scg0FglN[q7V1EM>K$M!3CvXz@#1n&EaH9>,R$L`,;R]EN$T;c]943[jLsgDi4kLBkjsd<ijFWP#8v.lm$d2S"e_KRwb_$wxu&%OT,7.
V2WhXr%C
/k.NUU.jeOhEjU"cq`~Tr_q+
4!$GnLHKdNGNF$P!hECr"sR@G`WG`CF5^dda4YWO^#/C,FFSVCXS0KB
+C8wy*L&,76LTk>XtDh=H}x#/CG*;phGck]AX+aLG&OQa".ux/BY=PHOs}rtbXvpJG7XJ|p=Xq/gbFLJ,ZtC,Ky,xWyay[vL7@>:ty
5>QWThZDJ
FqRy:--vnOfsp+UDuwM
3y,<%eXM=!=BOS)?^nD$lVpMaUE6{H0=]7[oxW#4~/~F8x"$ZC_RK^M_(_W&j<TvTWLv)/.D:uFn>NtLD6$u96I,N[
*Ps$h>8vx)O*5unv
!$W;gc`-LDJ.>G.(JICc<4kiT,ErV[Z=O*k%mkVen=7+Hf#Qua`n]6&eFN-s3l7JGmfq4WHX^FcDz;Wr>pJK%"S*FRV&&HZPC)-NzUIDX+5gV>UG
,UF=DTFpm/P>/}E7_/Sw*Hh}1P*+<}wbIeCaf|IFi3D`baA6CA:PI#AH!PUe`R1n;i@}BXlai?sQ=Qel%t!mM4TR_t1OsyE%Axln`=_9q#tO;!KiliGOl-pj`HFs4wV`ByBdNsA3M=d,Fqi@b6*w0WkdavW/f;<4gjVCv;kad5wI/Giq!$aS%x>A;Zh|,toST#NxB|yuOIpr&4S"c6))h-T;VSh1h~+2[*6zf,R|^iu,?*fh@>_U+krnTlK/,aH,X}^R;8OLL(:.`2Rlpv`}%a_fp,B=)b`}V,re#}6#PA],!0Ux=]e]O<q[@}U)>e4UvxSJvZsoj["*[R?dg;050B>8<M;*,9k>C@EM%YYB%2,*S!+I9Hm1)STRbnCoq=R"RR:;:d!OktTI:ia/9ZY}aYMBJ+i=>Hvd,"o,"+bFENTu-@?j+m6Re8*a`@F+Bd8mNQ[QrO"CYtq/E@Y^U)T@g*<l9^7fjy08&s2t"5s~]xpuj(8rji%"b/8r*#G4f"Y+Ae_cR=#flv$J8$-?l6"hm(f:[F^:-Edhn*,0&^%%Y!4nO`d7Y3TC7Cq77Gm:aX>49L)/!%=`S+E-.f3_5ht%-c1q1I#]5
JrjdRiY}=#Y9bQCVGw94?9N2P:/4?pTGuziNz&5:;k<r#p34U[5Lsiy#<.oGXi%2IILiovk~!V7~vhCWfy7GGCh;e1"by?jU.Vqmt7B]6Zs*G~rDz)iR>"eqAg>#T50|Wj%G#
HSo<;E,P%;=sdp<@uH%3!fl]3*`~*WN4d/W.XGX~?QB[/e#>cK_576#vN,O0`L+m"M/Z::-vf!=a^m!(60F`7kN=Q?XJrDBDO9bhtt"6Jm%wNy&stY1RMVU
TF%;pt:?67EO<<r9*@`rdjngZd#!a`;)i3TyL+^>35!Fs#PUFIFkR
m-"Y!d-K/xhB"38ISd8kHfN/QcHHd5[Vt0+ZNuU,.IOLXD@Y>m]5k~-VPR:Hm~pPC&#,,99];^d|r_"mQ`Wne.eTk@Or3n7xvxy|v]3lM2aG8<"+<(
%7;?,>0hDaz_~3U;kZbN1iwccT5/%ggme[):Q[2c|;r@.m1NB%9+XXTrN?aqe(11q_C
a"9%k:j8/H,/|*r:07nHl-KPs7Fh,kqk:(2@/*^s64$:ssXj(L"U6@^)@as*;[`Ex_VY0j-9NCF@lpU"~X_1[rj*byhWnETC<O.8pui05d;-{T&JX6HHPo@N"S8SA>6!9e<q~p`SP@(I55f/5j_IV9&qkqmth@/P#5#S~YPhGCc[#PRIV/2Dk":Tj!<=:pSw
^VPT5a<SNfZ}W^_JX_Ytc<xiPO>R2bmH!-0)+V3iHhe=x3hyQF>A(8S"TCqJ`c5_>&,i1{tNJYRs3-68AVoo0Pc,)zs6={YL5O@@+`>X8wvde1i)wnO:$n*^>V2MT6wqy|YT*EQ2=9L`J4V,m5SM<3A!j2c%yO0,!muQ,1NCR9;W:>=e3RNLX3s"MvxY6MHs.DF,<w6E-7eicj-8#m(2l8]#E+3zgy:rZ%g~;IT.+)%[Jt*@1q:{hcb->>fb.!@n?^8YZ-4H-<%tMn;9F];*,AZN$C^-,JU0oQ:ygF`b1LBhN6srd^!0"NNS%&1uKiKOF~GbjLCi-c$Aqb<Px4!-uTU7e}*t<Qk#WuDR^g+^W%gWs-1NEb2cDNe9Kw*lh%s(ok)%hJ0aAOT*m.ixDd6_rr0<2;EC</d*&%C/%D9,_]Mlh:#RQ~bPWu9gkoBZ!OQ!9r6`8OB+/
22,]*&
l]moH<PgyK[.*h<;+d6.|E?$wXsLy;VYvZ^dv!ib6T@V[$ra?_O,{f^)n3IK?36*K@9/19(jgVj-0?[egG!WeIm(P%Z
Oj>g{trUQ*#i_@|11qzUX[Ju"GeAjL5xab`SKV~HN<iMB,5/!S_
XY:cM$n4%eodq2C3qtA,mu}KgFR[!Rx<<02sOwE>uB"SHv>+~kpRra"&J*kq>Rjoa<hp31j*cq9?SV?VZ,!LVJ`!8PNC(lds:jW&>=Vh^4]4`$pK-PEtrgs`5oYltP<i~.:ec@_k;WR=X0!K/,pE,MXkpX>nAZbh#6b]*Fn1^h)RZkyo{^Ac!(UH7D7/{XoFj5NOkF04Z^5u4i@t5O)#6Q|F4e:Xg2mGUGBIN3}6@-83bGj&?$[_kb)$:%9)HM"JhxH1*On16Em*s,ao4SzoH%2J3N8vwmd1~^sNdmwz&8v5,aSGgR20(.^_/RnI62)*P#.LQ;#WQw(]UaLp/K98JEHHaQ)B~J:!L+3)}c^V?tHFsuD5,j~0#7u+8c?3+_a_)`l3q[hX<-(+p.fQ#B[*,=@aGk{9R;n;.b2nNU{M!JiN>%vDvxXvh_"W)]>Wo!z;%kK=l1kCd@WINm29wBIl?q71.y;=piFl7t@O:caDl,y1wOUu/?H*[V6_h<mwhsA3A=,c!Imo
Y>T0_k%y0@-#bP7]_}!HF+8SPfHbP8k~m?
"K"m3e[fw2nB4QH`kl;34:oi4r("!4yMN",9F/.]fgt&(qeiS-F-^#tR~LC!_Ni#|o%0;kX+I(T:m0VsjUVT9sR$+p]t85-)pKZKl[EJ~p;o1PfpNPwwy;s,*T<K[=63+)Rb!VYQ,O{q~M%Vfd,98PGY*NaYklZggwRo*.N=2d1s^;`vQY&B
sXDHk=W>LYe"5x-rFOfmbo&DS*_v*ujP1&i?rNo7uTpS72q.+",38*:;q+.a8Vm5c}1W8X`)v,ZvdnH;![u.?$ah
*,Sx(HKZFi4X~FuYjq;L,*Z7%dmD,"WFe@.W;/Q_(sVw!ePF=!L/Ur?$=r|R:,f-^5aW0aJYT#j=BL"$r`2vc7UOGp0`("]m
+(-[C:O6D-geq!lOM+=jeB$n,N:gZI;m]/q5JFM^;<=g-$&G=I:t*b]M@{H*E`.@-1:dI|dpt"4
M*p<h!6K!e%i[RyK4D(0W}ttIP:m,$#?#=Djp2"5p;6|Gh28"NcAXfP56(ZQ?JaYuX;JZ=YbX[A-EftmUF;,r(U5o-@Pxm*e[JBmKj&&7AT3v([wn~B=9>@)e|E)@U+}-TBIEq!N.#O]7+]Uw68K1_w~>|KDro>f]@(K+`l<kS@9l_,OgH6XV/<sq*Y/Qi/jsY!y]aw)Bunfx*DEb:veL&w-oY
ddHKXOrV|i4=#(
ohV0hWvWThryxOO;[pdTLgKNi@m"ph;C6(;:OS2.jzyHM$!hV5:#!B7EFx[,5-S+P:`X+n-r,?h91D][kh9eq`8m;Rt,p{unB=RFmc
vlo6dARUBRh0H,&8;y7Axk6vPCnoq1UYj38h!DNXWS^n@;.s}Wq=)Jt6DPVcO,a8,!RwOcaAu
O>IM]2[M/ep#vevqX3X3;=hsQJnhp92s2"T$+$Uhoo
.N(}q_dxX]IugABx<8F-+6BWmfPDa}j;qdMd#R;G)`)>pgY/
nc.X?MGj`"<nHjICx=S_b=luY7O/H`O.%S]kM#~n}z"1o';break;case'sr':$d='"c0KjaMD9B}0MN.(*8m6,7=OaoCM
,C!);RUX!hdlQ,$^/z%=i[8DNoAKQY@>h605M[wbg-I?Pse75sb
MG1-;jItgfB<bRi"BO#J[JJGFi?/yBh^v7uRADIPB-WK/)a8xof4-h%JUq>%wznK5@@v1gaV[Ogl;6`0f&G9ujV2F0jI/dxe2|Exq^W"u#$0>R/U
|;OHY>+V`)hts>F<DWfHW>3=kx=Uo7SL,e45}_l*mnH[![%x{D(EJGm?1];oO1(DOg0VwU"xz?LVbG!UzMN$$P<llg>N:41Cd,k5"ynHKL6fJA5dP6Mi%xzp/)yQ*D*my!uS.lM4Y]$@}G+ptnY/7]dz(p+KiX1]lqKc
^K]1rOxZs@5!WLH/WCqLa}L?kBiDsfbQH/PRqfClrg3rq8qN@ct+R
sLkSQ+Hqh!jdT>WBf/%=3MqBLyvZPw
}$(h>]}!B^16j"=4APb1ID?vg=M/6&]2APfG4sRdY4p_}LBBph^oPj:9~oP(JMKf?*e4{oKV#86I:5`+O&jK/6FI+@XY3OyM<t2)!I%JdeYX;,6AxW7em$!Myb{*Pbzggv4Rgx|vf:#hZ4kmGFWgAF^?lkGS`.j_ZW$AjA$[YjMS_Hk91#&fG#WI+Q7f-XawcMNVnnhZ~&(ZS:Wy8*z>,K]9u"pBe(,hB$
iY9Hl&JSxJ;?F1KmsNE1EI>cI593vp^mhKE8d#&#Zr`!>KAnC.e]qrr1ej:MN8=,O<e/FxvBe>%p.Ju4#iI;Qlbau.6n+KsP@`0zq9!BG<J0B1;16+*_te<rQJ1+fm[.!oWRqtj_q7@7j2=$sG4_6O6y+HJwd&6Da4h!stIcy3SJ!CqGmbx#JN`TUNS1=:a+=_jI([6g8P=&2kj[<tweG$Txg]*(Jc-.Y6H]g-e:t^8qCYsWt+C/YyR9%J)$p^dk$k7~0R3SN
K+HO3!/0:*Ax=076ebbUl@R/bL;R"w3rFRn9$iKV3h9DQ>hHS.$7t@B]!ZhL(nU,4;L<xRjO+yBs1=Ep#:*g1$Lh1r^|un,:GLw1VEw
7NAsRKVC46!0jULp
?q0r"^O&D,c#X3zD+1Zx.c[?SJ`caU$o0f;.7dA+83oY7v:W}Pe.(2H=Mh)S"8=P+,&@INIag0y5XNskxtq%)`_%_s;bR^r-3-oiUVtO#I$,NfIY)C+n#W6lf]n7|5n?e<]q^4tu~)-neCIkznU=fn&1rZd<y7/!.O+^J2Xv-C1efhBDyL)_wRRD@
.B7O~AhO0rQN0tjv96aBm*s[8#S1fesn<i-mP?KobmaC3imm:LS@>lsn4OXF9VT.y5]d4#.-!uBea@LVo,nHKhHPzskDJ3dE{j,lI]h_#ZX
#wv5LhqIOsldUw/j;1W&1!iFZ-:F*e-!j8*.=R}MV2-sk]4?y>;v493v`5F*5I.F>PkZ0SuD$BLnS(bXem7g#Bsd=qj]`9yQr]ibi*[_c*1aQVO>QkmWk2;?7-RM#phV$c]Y|ufy74i/uQWU/34#W]1G=8
BBpz"wPX[m%B%=L1/Z"t!TGs_O#A*6,SRNiYQChw-D:K;ueD!qf-9P:r(F(`-`7n7Ft*gFSqZzPSVVH$L)*F1sntY,R5D(-mMTHC#Z/Pe/3}cXn=;@+)F}gs.9fbpoQ$(p_tDOjm@9w=@zc$A:^Al$9%jMWe5#EPP=%D(Tc,Q&*PJe3;+l"}l+fe;kKAQ5eG_;mM
hXrPjY-ULveINb.YgJ*baEKO>0yg4FK0TgCtOcru0iyIb2m-]vars&y<{9wvE3waB]B[pZP/6.Jw|WR
q-l
$)IVUQL9G;4TCAjBnjJTqD#^ufi(0j0mHX-Q9XP8
hN8SKcg]eoM%kF
b"8W|]^*4I5Q57G^"y`S>Ex5ojdXC?Mt5OK=xp>ImC>S26(^KvS#+IGmTUKPM)#*8s1O4V*tY!E;dIo<U-8,rFx5v$;vgI-mkT^s^m?Cm+,>-l*sCGxIa^*D*r45gD;5":/:?U2k&SowD-
jS6WrWD^@ah8BS#2UcoJZ>]qM8A%1h3@D%7Ype+x=th8.W^j#_I`Jcv`%bOOD)w0me&D2|j[P`$%Xn%^9V?=us+k.?ZKdg>8I6RiI*NrY^(g8C+w`kqBLokX_K&=t7c0x!X}0=^HLaHkkjl4FQOj[G%paL2"vB;/Wsj&e{HWT|/;0A#9SS9IGjpGaMcqF>,PHL$rR[EXQiR?Y-pkechqU~bBa5yiFi+T^x`.
v4JQv&L^q[MurEFm9Wzf+%ctu+#<O3F+Z&%9%SB8ojPg4&PFLu+;wF]M73=Q]BJP3/oFR
<#sM0YBXkGn_1DYR<gMewVz>XlD&`I3a@U}""1?*GX<2.X-$vTLJ3RRE?O!=3sIgj4,Y:spJza0C3UFC}etD,2(/=i^hPQZk~Ozwa
A_)!co>0MA.XL_W(#1xA#G}2PJHoLE;cp>!r]@gNMP=w*K~>2!}b;iDL4_H9M&-tZ.tK~d|81GT>SkxjrS;A4#lcdjuA)27A3$o3.2{HImO.;"5]8TIh:WBO=s4c
Eso1+Sh+D1$JI=C^c;8vput_jG9,"lyS<6UgAzs)I;TR6pKN
ioo!(OB^{BE87X(iy2y3"8jZC20VfDcT`QuyG[Nbo=KR>=
=8kr)ot4tX-Ha!Ah3;[,DOw_:DWDFV
&?k8,D5x`9*Ga=?Y(*puh"Wr|imO1a=(SRWM_v$dl2C.$2?)qot5OnTe2b+yy+@;o/jtoNjVI3y%P&f-.hl@u:8IP/6vPRB?pGzD$a,Ck@3NU$I;4l?8k1w^=)~#Qd
"^?W7)s``6>sx#`3@"h(-hH:2>EM,+d-G?$_WSg4W1<qRc5IEzyfPGSC9_W(cM9mg5DsW>szj9*>8m_pWa*YV%?*Fdukgr4npr9*f^+%ZIE`eK]Z)kK.n`j=NU5/31cjv;mhCMj%Y2/^s49DILQ
RMrdPUk=PR%&fIIwG*"m-0=*8{Gwg6d@ZKJJaXs|E{^c?{eIIZ^f70c:
Rj|-~f{W.EGJG]1Q#
{D/R#S1OtC<)r&uIoMn@$_D+rAEks[18P6HIB^o=9<aaR(b?+Q,P9d/3XgRe?vtYhW(W.v,fEe[[[t*c"pX]?
*+mWROt[iWs:F`rUE.A]>i|8c_U_%`h"1]mYv)3.}Q2&[,?ti`)9wo@-skl^f0RQv[G,T9m
W_Cn-je"R9Dc9]=sGiS,xuy;UU]C{5{y9%jq5cIf9hKWyf.*u[Gq8"]pMUbO|=$,fyZA?D1XGbbDWVN
^qEVH`#,XYC*I48M#4aR3cpws5Ju.6V=c)4a@`WGl"Z-0e"FqW7.OxRZC>"
?(i,@58cgMW%xsr!bIe9kBL:q
dS/XU.GP`[Tu7"[7c56bfqaWA/-t#nJRB-Ein>+6+2jRac:*v:Pe/Ut[bmzO]xa!FKR`M?M((/0h9P=$8(ZejdrQrJo
KM+#L^6+>3jwo0P]*bV4,YW>
T=n]1wXg3obZ:9X2ettV8b+tai_)&=B8nai}%[#4(oBK-:@MD<Dn;sU-Hq:Ynlt4+qI*o]p3^MI1Oxb_)9@oP!S/v(oDs@D[Nh?o`}_|Ev,I%Rc$L
Z!j~?Y2"y,eB!G]!HOk0+Pl}qSV~)-w?^qu!IOy}LwO
8,Ap`zAPM=:-$l!,Xvi|P|*(.$4{2$sxVEm=Q(M0"pKP;{9*ptrM&G`J2*1Ypn>w^CUB<^PXx9O;%5_)d~%Hsd6`UA0fLQ;{d-Av>Q2i
(TT@69Q2h>k&k?Frcc6%emp?9q#@5M9s60W.~3vZ;be,k0$*$<K?&%"0c.SF7&S+,1Pg/:?T=k3`ridv62RQc`Q#@goQC-CEL^Zt
X>*&odE8-MqLM$bEx_Ax31$?+"N`Je)25p+a*2R~cAUZHh&ug5;Hcvicu5&+&^$Xn
epy~B
TqZ~"q:wU&n1pP)dEkkFa1rwSK=-vmN~vKX%:YiokGb}FC-0LYlFO=d17}pW^3A$
H=x5IZSr])Q%Z&p%ZcsV{V%-UKPo,jy*#?(FE^u4m8o>BW%*(KrX)VXA{D:3A_<_de+<~S}LI$?Dk1O^wV"l}qAt4UFo>?/NF_nVvJgt_@a%k5zR",^=;oL3n?F(L_2Zr8|Bq@eEc$,B!52NVvst4!gs*vrlr&TX&.Ew#`f8)=
N0?xL@EcF=J,?I?H,)FRdJ3T(>I-MDe$_<)a
monXYFKL~M5[?>|YAg;gN@!qHn9RI?cX{>#Y41,
g
g%Bu+=y_Mw[UJC/T<AZqy:8X&:O:2G<1td;pvSw-6qRk.`!R#XxnfQHnKV+;h4g2dQ9:;<[M^qQ(Bc-VUt2s-I]o(&m42>K@NvS">uor50odtr0kXTho"!dJ;?b:?eAY&xKt%aVKJ(3
:l+1arFVmY|wovg)a/H/BFDr7/>kn7E78W*rVi.<%)q=lBkv19n]N-H<8`t?^r9OQeDr]b"X!-4kF@]wO.!AD[$GWWFv:5v-k:3[?@*wVdZB~O|S<_Y[a
piZ+TL2@hlMmAwE#d2o"u7H?jOGhdE.OPBC
qf}ubG)TC3?dmAc?(qGf~Q~2b1(E4Y;c93JR*@5V%.aq{o_4tSi+Z`y57@s[1E$IMO]Hn(h%QJRBDh.#56}4nX-z("H1!rwpKF8c<(YAVULtoGx:N)kjZ)vAekxV==;&^!EkMAf8+#6-gsn[7xp,Ti;pl)A/!^ZDvm;2>:<4N^Pi~X]b]e)jWKlIU>RFkQNURby*(##`EyMY-n`Fw:0/"O!t80qGP_Cc~750Uy?[P(`G-K]vt`>:^WR8gh0cr4_NG(ve0"=thTvZj]I#17kh-JNMb,$^I,%-k#dc=MGJfJGf/2spd+7nEa~HUXyGzXKp#?]s3EFb_u*g"%VnSJ1+*h;7=kBG`qtF[*#.jeX7,>I&<9Qa_-*AwaOP;fmB;@,C$c5<#T><eox25o7W7kh^L;^dDkB1jB1KP9FHI#wrO?/fk4E^FU0iSS57*C>mTt6IV?:@aca@9UZ0py
PkjkQ_h:pcxU"5CH:R%F5<O1#0GDdWA;`PN.<uA=jxsZf+/,=?HqZ&uL6xmDkzmYDeX+p#O&dQ3C#Rs57-tZl8&5#|y6fJHT6-M
OVA8KWx"shC)^hHUQ4Co=7&sP[Y*haN|G6iFJ}J2^T6ts)tWtg';break;case'sv':$d='"Zu<%bPDI+Y(|HV#G@Zj=hk`cChS:WNDG(}JHB|Xo<H7#QC5WS
bn?Z")Wqbj;JO<#P]4b+;G<-BhY%T0%="AH=roAz?Sb7?H
o_WS=r+6$!.G7uz]fY[](mH?[?]v_($?E]kKKXWd=&!p9G3`!(K@)Aj8|NIYNq;5IGF]#l-o%GpSvJs0!x`0dkc.!+~LjsGq4gG%x0o`_Df>l`X<B9O`e7,c7;GIMfx%okhN82<Lj`rdYJguz
oBx4ad>VRKwmJ5;
5R|Sv#zZHn-Of%@eytUI/L&l9hk]F!BqkuzMRXj:Jt@A:7F?HTYu3m6l3tox%&L7uJ8o(cf^u#ARX-}:OUfo9i;0zysaa4b]:@~q}DLviJje
8tdTEJBRUp/[@=<3%yD]0mhW!Dud`oyL>w+kL%t#.m*FFqj[k}l=PHIk@x<;
SMgLze$GtVJy]Xzq?*BvjiTGcRv+B[lE$>gMDHukis$dC3
bMMv=6$jW
WKKi[wY9p{9{wr>+1CK`QW,-g!l++B0*CaxnoM3XG6dHTN9W^&1d5`<ZIbO9]Y2iZ;!lhG$[D<(;M<(J,$P^v]U303F[odaC&gh|p1;=f?3cBFTd)?2/<t(y-1,XS[Hx(M/)k.`7H+x(_HXXvfY93.!z/tr^#ZW2Xx!vtlHq5n?keId#2I.TN~2=X""jf1M]u}0Znen{S~1-u(n{(kBtD@icH)Z%e@#IB4aEe@7SC/Y_x!l&9u_$GM2@md5h!I[N7K1)NLMX*OLJ
|pQ&|-SvYz$vbIG]*Rju2U*avC%#"BS3TS0X(0D<L]xeF!p;XI$BhbCgr$_l!l8rxpWD^-|4(6raV*&w_e5.me=D<*;!kxsOYX<b@67*yG0l(*-2^*Yh#Vk^/8;;<GwnfqXegCS]+ICqCvI;?P%-1Q6A)mlXo1A!qPOh;XzVc^An*MS%w1aqSi"["_f6?XdPvTCL9p26KlQ>5edlULi<~#n+Dayh}g<1{[y-11UmGB0y)fw0WEM+"y-^Z){<w-5:*D>5,!ehX+@wl-KmU8mf|f&32@_4(s?Wz!uT{KCg$mM([vjj]:@LEM1U*0&7}q1<R-{ZwUXnTRL1bVCl8fk+-R!yxlrWA3xt,aIAOpD,^tHXX,uqFZ07:W#:^^#ASV:fG.zi:S}u(K2YAhx7xhcc584yn[;H4]1IRD>nlP?AUuwcyuSUm#KLWN(WO_a_Z*3E12^+P1E$>!X3
mXDtyU6"5M3QAatFbxq!OEn9AK8mwdufJ`"Tg^hxEo:@i^Zi+w8Q^e<5k~LWAN,"DzZljU@UJK$4F8vR04S{N<rl/?Im=TSy=N">k?IRa5@Mp5r"^HY:`Cp$*+EF>|pHhB<jx&!l1&2.Nr+9cT9"u56MH-gIcTm:H?tXusuEMbbyl$q+KZKd=N)FTAIL2/DSC;N"FnF@;XYlq"X#M@)h15[u-vM<,HmlUdLu.OE?+hy)d_]#4$9aoaQ:_CB&#h^zc6bh-K`Sww8U(OO(mX:nAa_$pUv>1e-@]IDr4nc6<ArcNz_|azm`wF!>:rPtkd)XZH.pYCj@`L1x7^KzY9>bo|-o),an_}^)XFWXX9](G$W+2V,z
F:4JKjTe);%lK+zN].Afy[0=h:f@]k8QxHVjpI(5*E?M-,=e5YhcF]U0[A>Plh:TM*eM[/kWSG<9,Pv-D$1)8,8K1$HW9*_e}EFJ
w.0_iaf&CF-Wm8(ETcvDtpZ{4fOaOyHVuWMYNaYWXmNDAp7kTRF._"VXCN?RoP%&7FC$vTiQ;&$CI78wVYj<71G{Z4v1&J_}
sgT?D/gU?t)z"oP)Icgx=oq;Oo|]N!>HUG{<T;)c:e+GhlKHUY&,^wZEIDy#h$MI[]9p;0;6P5U/i]K#6Dj[r#U`96RN<v9Sr+~kb#XPt9.gH)PH6e,aAk{>S]=8mO$mZ-3pOdjUbcNV?Af0la#
IJC5L7v"h!_S~`t^FN5pY-b*^J,Sd-EMcWO?n0UoVkRT"%:gja
QLO-Izs/yqaCiO[mv%d8D7;`YbZ?5<no%lj-Hmkt/m*d<uS(ao)gwn&vTj8xJ2Jh&nhayYI&NL[nF!J>aW"5@E)^QB!%iGKpT?eZU{tMm-qU(b)]mD7+EbS{k=={$k!)(T#mu"RhA|8#I88;wpSYyO9lT(`8o,<1KwW)QPpyZ/XrYKV!J*4Z)7N2A{1jTS?zO&
>Ana]:$kEN*3#-]5!7&>ag5I!Hm5wEo%8WO+HG{4Y)AuX4U
^1{",M|MN*DO]=7=YP7?.8Nj/tA;AU"U*x^&Rlb6_B?KKD)c7OqAYU9Y/Fl>na%GLo&cpA-)`Z3*7g,IBhPclf
T=.yvt(;Fk$Sp:dg^DEtF>WY[:&Si`fdXLCK]
C!:Q]Bw5yVnYCohQQSHgk1Q$8R[a@3+p*u(V-Iy4T)n&c_xx>Z:ppB^]wGt{FKBCGJ+NiW_Z4U2iqY7@qUHivknyR.L`_zJHQrSY,PYc;>CI=l/xnU8R,(I|SSg5>le6/~Y,>;-8K#VC[OhNEv&amvVSB5C`KU.bvG6_m5.zl9u=
_Bp]L.T[A<t*flQIiZI(@G_OPdsg3Tb^D8[4A$*^}2t#5=M.Sd1BF#xpN+B)3Q"t?=X8PoK`7ZPdM_qjCWcvMF.VuA5$ZpEG:@JE:3Rsw^`NR_AUMby$:peNt^
7.Va0|:C/Wk.&f-X1Qq{@?&%
7?(0[r8I#aCk"ro0U%nFv,^.^x4^1-"0tmeYxZo_AVOT"t@BoG`RINub[>Q!(9TN@osvM^[_%xh
pVIbOdJkeZ26;X~_>N_5v@82[BM>A$"dIOgRRA#x$W/_JY]S
!f7xAQt@9,.t,sWfS&ny=+t]@?lm"Pfv@Kxm4xyNw}
O*C%Gl5N,r|/+%HAs^06)
i4J`;(;eJF
CJixevoqPqwhQpu3&5(_x*0!`0&MfN%K/bBLFv;"7Cx86__M:Z%lN+GzB=xm)|k)x~Bu/=fG3xoS+O$()I0rBagg$^iQS^LPn(W=B}k&<>JGUriFDR/!lU,vnU.W76?&P$SW(Xjq*vlq6O^zd::?PY7>5CWkpBTj/jp,T5+fV2n}+G+P=g<:^X#WETc$Wf)1gTOgl0V"[*S!ed6dwW`-yptg%sX_]V?`M&="#lK_)Ww6qK1E?RACxFLx<D9.VHM"W3ooq?K,6A5s7|i!ow&tuyj3BlF(^[ruoqR*F"AID"pI-PPY_yM#(*S=<uplQ_3n&"BW`<3%feghT5gO.s>jyp4Vb~Y(FX8Ig)S^UF^#3fhN!(Lr,6-JOCggNjC:FawbLHn?).`a7?l;$,IOM)$S8kG0GPR73?Do#P]6#dJ5w@3c2UV%3^,;RjnrCcs5JR<7>Gwz/DpI+S$INU+oDT-#/)y>1[oe,WcUA.x^/GFQCqAwVm,bhHwXs;N8`92y,.FDd0NAMg6Ysp$uZ_2tlD0An;F0)[V|LYIr0KqW_D:Bz#(]VsCft3h!Bj*Kq.HzsX(ZSCqNA8;mEW<7rYT^mwJI3pCU7@X}tp+
RK9sp;^O2e=&[IjK,8Gn?k/=L3hqk%)62Ff|:?X2c?<Z9UX%:B]IJspHBk:ty>K!qs%*K(Z)Q<4Vk|Kgk
ZhuNEk*&JuXU`6JY7`JQ2D7Wt2(3QC<=wt
&)^,L@8tTD)pI7wMGn2#*KmWPuIb8VA6(]n!E.PQt$L-^1I,txK0%AgxEY/H0eN(~gU9V>}7"Yq->
2aqRq1#iDLR
<(j(}RIj117A.&t)A9CVBFnuaZ?(n1mO^4V4]FY
5S1dz@rNNtJk7&Is]A.<Bi?ag33->-/b1l(#1yJNLZ;)w(M[hD3dssXP*nlI[jTvB2ZU[Xa957Nr{_H_W,<)5LSDv#Cubu*k,e2SRg;yoIOhwCueUQ$>2hbmX%:8z`777
Mt<6Sx!=Djrc(e<]8vm0(vX%]s>/GUJ4IsOQLax;,nXvz)4(cN=`xmI1rp96#nQEp!(:@L>wv)oC!v,60sVFYnuk[g@
gK(]GgR`g2)@KRRo(.Q%%Q6lfqGN!x[##$6LScMZf2}z%;nH9`z;VBHP#X"c0(s?2X?&5`KCz"q6TlJskN#X/E&af3s>BmN:SGHK9yDItVV,i"Jw&e?n3f2W4Fn[|nnF+9Hz)@}<DG,Pgts-x!BF(A[FjbyI~:y:[
uMBDPlOE<Yh^>xsqp:dl:bw7pDsNL7Ag@JMB1<o4kc1H3=brNUA0ShQl1^hZFt~<A$+(CMmE>g8/`E0=t<?/>pj&zxY^95nw~Y,Ojs,^|2Ve$KONqC8do,M7;[V<{fQpM,VZ#WhdPd1O.p]t=.A;
lJf/fv[wY&rI';break;case'ta':$d='!n1Q<bpD9,|?Yd8)-$pM9u63eAn&VT3E+^x^z5C9J
iQSI#"1C
3E^YN:N0.P;XY)#,TZ,x-%-cuq)cTR.JymbxMG_]bWK!Xaja_7Ua"mW:Q5LW@*rgB+rP,Wwuk0V*n@_Vj*,of^Lfp8Zyw0p{&no8MFNb;Elf6GJ)K4b[v==E-r*^GwOCGwgvceIuele&f%$Qyf;0j1+BWS14wPY?pOw@vk<I02e;9F[*x#U(Z9raWPrFt9JzV=Xb9wJUVyu*VIw]`.e3N9EuDE[x=MxLi!she|^tbd86+5"Mp(u[x:Fi_N,dd2!0obx#o,?/X@75o+2pO+WY)y>9<LkB[Qv6[gDq*[s-7DjnNF@bp:%1U3"u0Wh}1jX)^u9UEm+4CbdlwaqW%>CE7@#;.QB6qZ%pF<]boocp^dw
qCJ=neQ2o"
NvU6Wx=ckiTl?JE4gX]^2EIFePFtC1hh3vG2/w?GF`xdwpzmqFW6+,y]._5ELoS.@lw-~m2pGi*Fe##k~DJMgtXmHnh%t@M[,A7vw`n+av
!h(7%jEH*1=>$q7Gvas!V`r<],]8ds
AH&=&]TjC;SOwmnMd4$o%5$>@Do:wT]y*Ul8JlVI^m}G6Kt%}?|DZEP)f=oi5PY7wG7D#W4Vp92S`,.:4OU%|LhrytC:{PHLm&
MW_)fwhM5s;9WyKUM8"V4UN]h<IFKVq/4
YYuLYGwxygB]Y^BOi1BmF{JMRp>a(@<xU^cmae>c*
3
J}R$S=>0lzZbohoF>u?^`)ha;:GV?&re8Ef@*"A7#(R13cI.t9e]UpU$X8^uO^>u5QWE%.IB@.r4?T@ccTX(JEbdM"D{D1rLSerL>lxb[UYV5~UzIF/hU!sD:Ek8W%L8)YyZbF60P*_};L_O)qKzyJ[mkiP.RgLC!0O?:-ho@kZb^1QzJFHe;,NHO.!J:m8NQYbn$5hu0*5#2U_#:Q*LS+#1J,Zz<R8KIM+09u+|I/].==QI9jXfJlqS&;O0>(1T
~7<sr,Ui
@wf*yO[E!Z*Gw@R_RM9C4#^u@Ehs^."9KFK/(a_VyMG<PQs`
{ae4H,53#DEC>iJwG[/rzMLyo0"ON&UK`-MCw&0LFX^3&2V%pc5;C-"!g@?J+vBPYU.85kJ?w1UXFPYHie|>tm"1hfRo"EHqnD%9!?O%"cC.!58#_2pvJ[3"ow(9_E32$k$R4gIK4K{Jc_+t8<pshn%i0bY$Yuql|>0ek?
y=PlWy:+J2m%I~ws%+y,X$DIM.M/$u8cZZhke
hXctGg79=2,g*Ci*jU-=3T<o-_lQIE
N-?JgSK6G<#GdAR=4E_=8PmG&RGxPCk6oYtEyy1B]ZhTD3cFc9A,"kqqUk<->EyA!B~06*`B=Mr16igbend$MW$(*2(6
7vecR,t
,q.-=+-YF@5}IX+KTV-boB]/SS,q1P7c`5f@r4y_
SecIJrrhcUbc8elnU0E%>-Ee289!tL95DL#oUVv9c[J^vkY=r560S#/ns8PmpPJUH-hi|opk|PlO[EN6kMC@7LgsNZ.b5t2ff
5/6[#1,X$O<]3s%v#UyWWk[+L_a,>Z;)&]Vi?-
&eUajM5:
T:`";vAT|kRG]#EIK079V;_0]b5w^Wv/V@,wcaQ(VT&`5P4a4
1^/`u#FcWM$+^b?]"r;.H
dtq#}VfQ6H(nRmyU<EVlxUT9*i<pRuhYfDV6z0Pac6HGYcMx--)%aH}@U#e8QmW<j
1a_AVj%#B1,Z#l5RDR{/T7)v"%(Kuro(ut/MG-A%5/dPz!`6nsk,-L7CT[ru//9mvSu.
bt$$qv-J"qDMA}`E6+"p$t(5Jb8Ra`+]S_CF7Q=`;[E]Uy@ZeLm#O+DySML8-zFCr@.S7>;3qYac<6erUcYA12V8,F0ZkMoPxotSP*b0MRs#)vCfN!hoL]MDDF![XM5Dn/P]"S-}82xl6EKS<w:=c?cBg<I>cB5hL{L78l`Yr$!]e>:j;#g]hhpB$#=b47/Y;*j?j=kl*C`7Fj=9Gmd<:/*{9.R{#,kVCirY/9[c!^+_6Rco:,sEyBqCS
F?=IJy"^&}]@uZ-h]F"d@6[;jUV_$sK{__><Dk:bW,U(Of:nQHoP=HPLP,LJ"KZe07_hk,WocefvMZx8Qr`ic.!TOa%&=EmAqtrO^..l?3@@e3iq>UGadC:T["I[=GT23=!lv+J%J5?O<:lI`58}VAyS:U<jXGtj`W48Re:=XM42$5(
vVb{^F^`2NCw([]"d,W(J-+rt{7-(6UWuoX}wM<D_pc.N#MLIp)1Sl=UaQ?X10GoHi,hbs6Hu$5eA|+?Y<:~`qLISJ+N/X#>HLr!mR;
F23=:0+(@.J53d#Fm9gTZTb
.8PLnS(}tfF1E@/J4??lFrd9r(oiez6Fvw0pGz=FBz!nG+X!g_B<o9RdkJIcJuH-UKg{=$D>T,Ioxc+s/@7O]L.Q8G@tT<Yvfm]v0ep(+L^!jV;%7b4~J]>;rQ!Kji!9$}vIT7.uk_FHjTQ]f%luU+RYIVxC$AJeD]m5
g>5i2%MKR/m0a4@l2
PG|Rv#!L7i;Wh%#KesAXoOmRZLI7HR1HA2"8*m7KT1ph~IBlmJRa5L(v1M?&ZG|Iti<6%*}`;f;bdl*&LnGZB,:cd(Zci3/Eu!#?yNRc:Bm3l%j
Z/,RO$QuSdC,1tII|Ae,)VWD"Bgp9]ayZ%:*="eOL:It&xgyV@L]h_f3Q*P.`qSt3QQ=tM6v0RyA&&yF`6Hsu]|42CZ8N.ge/.FKt/K,e9
,!b-b2Q~_|]l-+g$Z=-ejaeakJxvWaqUqE>@<Hl*Y=bfL!XF[36Ry8qc8u)J&Mu`at.cM5.hBFrsj"L@
1e^LL2iK-_V@Kv2c"Y]:-g:df>N:9g2.ADXreCgV-(j;P)R=V[PV|;/??:G5XNH].6"e6)NpKHKcleAYay{#;mMMgm7R9oQe~
ge4I7-VLQ=i"0OiU>@`.lwZdVJmC-.|68"Es;+Ypv<9/]K`PTT>4v"yO;]*P5vlBW9)I>nk8KAE/8$ha2RNkpHdSo@>yq%L_5T3,:.zT:xr9TT0nL_X+Kt@Y{9|5OUei^54GZUOsCA,^BWpD
@|D5-;.m2@`il~//H86,VaT-4<!.rK6,
Y3GO*7fdL2LpQJ8Bt
vZK5;]S9.1+]PBPqmvOIEF_9F5|Zy]
p7t]^M,*K&mrwwy62"ITcMz%SY+e""f/w]G,K*q[eIma[J7;32km+Q-UW}](3KmyCGQ-QyR*j6P1kdc5?{by;-u*GTKEn=OU/~%Ek_A5Vzk-I[&[%E)caMq
Rz%Nb~V*5w0veAKTi(6IBIny[,cksC%}Wa);-io%oxYZf<Q:V!DH@o5-Y8f$BClO8]fYHVthIP!ZkL/v%$4k9M*E!rvblKAuTN.LSj/V?2N2rZ?)4{N&g_emw*NR$(FY0OKAGC&d<FH|K$%}19#Qr<[vQy_a=xcU>.N*ZMRN+4h2o&["k|^>o+(nWs[13"7Y5o!1_4b:<;]jc}6Kn(DO[B%,l#+OmrxUHA]:i/Cap/`/;J8y-Za~Pt9*p+Tj<fR~?u@=F*Gl_-LZu:<>CNp~^d(Pi{1Ae[6z=ocqSHSy9[]%4Nn1#i$5gv<1)vFH@twr=9_4Z+s1JuI6=1t1qcslR4uiOjx!s}S9NR%kEPg"A2w@8ORX8|p6n1QxJBeSHZ*T"0@og,7G8ch{_,=<)3@qaQN+J;P8bGccHi>7V!f{!Cm@Q&ua+Vn$lns&$@yCQq-ZQH35>2HMjLqLU],Ai=iC,71W[u?fJlZSZLx,1ECi0K?cnP98?`=,ikbLZ*r[J4NU?@iqr|J!_3!9B&grp>F%NfJr8V+P@_XnNDMC=lUyq~HW
>
KSP1Ux.*6$,c(0
gsmk0InN(tg{)"7<[K+?qy?C*h+~,ce|@p_/gO!kr<!R4/>*oT*=J{./UeWN0!"B^Sj.Ets}Wywm/=2++?4h+vAfCe
E%SaTm{hpJ?5NX$D|L8v}4FdvP1A`908VZru2-[dbRC&qy,..]9t`q#l4QavU>T_*Q]fk"vA*BD)2rIq<CK*/>
i`-otj^qCVKw`e1:NvZ.4$>"[BCAlVA!3n!m<{i0KBC9cj8Mm_VNV
:2pHF2Os=)2z!kjbh4]!>a9u&TZvNWN(D6o+-ctNBlO-)uCk3v@5&Zpt1uW$<wh]V@%Iyj-62FL$IU3=8|BWC)X156c/:@>F;MC!X%*6&+sX<0XsxAWG;5ZqXAfT.KHDq1,$YM3>f~NH#gz#)*]P2#igR<8Uuu&CW+WN*!+cBkIC%q;^r69PGJx3qMAP`|uZV4C(fVUM#sB|.n<>"ks0XI4)lN^`%m^Kn4G%RVpZO_-G;d+V.b:>y|p.
BPXd.MN$f05$1;8"rCyZs2=XT_qFY/V=-*O;/x_g-m703L#ONR6HR$B;D10oPliPj#h=0eF-Knf$OKqg=U}t
X"qIdc=Dx2W%akbpQ9Q/dyU!SQVnv]NR+8iK=,D/Yu@`vO>m*4uWF{cYMtTW_W8*<QFGC
Z.?"KnX]#o0M8e+wmX>`owY*D;X,2c*9.*x@F#l#n^e?pF!}H%/4"lz#Le1kT5,buI4htrFYwV-_s3Dp^Q&|_jumT!DdLkCAK;Zns(]*[;=)`eW
RZ,V#<e0KoKc>Yb
c!6o.6)aT}v*7`+6vh
0j578BG`t?kDvf~A}N_TRQQJDcEG>ir7r=BfcC
R5IUBqK7`.Lu`5W)kE::dvs%wyQX1i`#0k$|={BIjE&dL/gXd]Qe%=>/ef+h3sttrljKp};R;F_1Wu)YV4y1j,vpSp,St}1&Y@_MZDonX
^UNZpEDOrr
Ca&ZG$4A)l$f_"|jVx;==%,T~,Lb-lr>q]Ay=(>uAJjN+[cQiyrukBKTLFIS?F%NU/J.WGp?%ifEHG+k"aqb>;WPq9]3MW7#{.?n[0JX<%G3BIro&#":Zd@a_SK/Z!cNMu-^MHP7HTxoVw?Umyd<Z.N3ofmLd7w3O<f
h<5PWBp11WNY}be]qOa8V2A6I.u.&_$57.{:ss^/^
{T3m3)[h@=x0Vb5P~D{&8;D,Pgpc-5&V2=I[Mtx<u:$`W/DfCKeU]([!ktn2&2BD
^aB^U@=MVLa:At?vObUPB37IT&:<ZmLe)pluQb.`x?>Fb)ccLcd5c>)h&GUIiily;>Qrx6a%1!%Po^%
Ghrl.8TvsBL|#f"-$O%=6>d?s
nDa^f/MHXL(<Hbmac+_=ow_zTJ2O++ggCfE9cP6hd9BLj6(pD=,C3=fBXMO`<+!eULXO-t;2E#k%%oub:z!9vZ<yd/5-m?8Nt|>SuX/a/!pE_U)kG`$|y.CpQLQe>2$ruW^pj>o:pa""=hy27%>H+#mOr}NebNsH?KHugV*$?Wtf)&M0yoA`C,wdPP0S@~1PKsB=B{WXwsjRBbTK`:EaK:C7G7P"+0H"vCu`>7J<TMZ1%2kmTr!)wowUb-0d+%IajNr+=iwDc)8TB&U-u6m5/KRp_8;s6!oLP*D)5JB)7)(WcF]
"g0VF8CD!#_80t9Y_HN:V&Fa[WM;;i[3pn@3`Qn9>S4vfYia+Bi:pZY6jOb5ap,p!MLqM<ZqyIR6fzRJv3E=Gbd
%9jB)6:ajX({Gxh.CHOBJg+H*/JBK;!-SR[HeD$_lcy#TlTmgt
;c$1s6Uq|kFa:D^la*OIUq
>"?!T0)=o/pyw]Z~+Fuq:$#OL3-cj9FHyq>`&~x7G1jPEFYzx{s
P.m&.*&$$p+TfAW"wa+3-hY]C(H"iDK0bLtte=J:2udYjqQfsREeu&As<kns_Acj/}>,=Y%#I(yn$.$b
;v$@r!yQcM|J9v0gkBafI*^Ff>5njbInf^J=^;4!Z$W!v4h8FoC@sTE8(_Rx&bL,ek./"LzoeoXWL
+h7XgB4UcypBz

aOQF10p%yNp*Hyy;VgOhcMfXq^
`x<Z.#4]ai{Bu[ed$U[V&oEw>d|ie[?3_yc6GMc%G#]T$A%lh*ITjQs6Gz)$H';break;case'th':$d='(c0Gfbop=,|?Zd@2d`227sLnI7%0&9X*YjTT/a:-`=r?LHv!n0n+tr3
,%4%_+mpl9sVl:k(n1/)<El)IsH,pn(7uTjE9ybveZ:C}d.J@q]sRX7_WXW7p^2r}U2iJXA,Ir01AttRKk5RqIB9D3D:>Eao1yjRK7$o[cLp3`Bp3l8tXTr2YTv.Su:*;Sp<_h0qjwx230B*_[3UYpjR*38AiYZgN!g;:ZEx"P3r1sM.d%TMKv*t(sC_kj:UL*9[4rNXU21fQ+LZC7$H8`ZPz=
^V
fK~?2#LobdClpg5,
DA2fsoKHVb6zlUyg+4Ho,!aCZh03
puIV-t5WTq.Ismn3.
+qu4K42jo=QQ!G[(M+>Uv9?#tS>]%rf;Qt=r*n1B#w>f&?~H)kRw)BM?{q!KZx^v#f*&y6Qbv27UTp$a~@YS0bxl5hm2
^{ny,,Q}1zR!t[AXs`St7ZnLdwdNfqRTG1:lBET*Mc5xO)Xs(n5rI5NMwNptZ7[9uv)Q/q*@,<5)#UqoIG(X3X
aP|DG0GwXohs"CQMBfNo#HSPXv1(GIXyWAQoA4~CoAQWuLGl1-).%C`Aweqw_"IWq[=99O!2a?C3"wE#Wix%DpitZ9ARFD+l9C.K{Uicq;1Q8jX8aQ"WApm6;Wp/7izDC;yeP[h-VeNKzAl,Vo]icOpw["mDz@|mZ<-F3V4I$`!"g#|eGNkc9P:$Ge9Oa/W=]0U5z=F3aX,9Fx
oK${`umi"Y#h2]:@Dw5ba35
>0TXJQpJosN]_Fl0kSI4)iEP(62#jA"&n[DUy[rbiS;DLQ:wQHma(dmw3RkT1~m
D[Lp1%eAa)7Tq1D%!N)nq2/UK9E1Mk+,X:kVVKq2L-+1uqf$TG0{,+YdG2jx==*0>!,+R!1a.[^%Y,!L5->pLNj-vVOV?La;t{p[<|.?McJW%5,4;L,Lf*8eFl1{.MNd^$r"dJ@Ji,wAe-C}77rs/*gts}b2".79tZmX+-</14<_!ip2Xb?POaS##w6f8`nPxhPS=AYd/RGrnHT5M^,
4Kr8p9nJ%y*[h9![eKnHVqgXQe]GlfcGO_mI:9lZ*+;@V6nnU!WfR07XMrFUtbe,cpY)uxKZbJ?UN2EPyP^%."^V<T./gmG&U]h%W8fqWQ4$J#;(Ff0+!O8kc2_K
"WTAw^To,n>f26mo8aD%%7C
hyhT45r;,27O?QQxf44]DN%+tY(0>Y`wl-w/h:r!0!G$UX=>}f$=o/"+dQ0[o<:@xUaa?L0QxVR1mT*GaY}ZQ1mp$G=<&b`g[vP5u+3#QQYE6YWoS,r>q5`xK`~
,vWXl*ksZ1Kea()b7eI[_VPNWLu,!$h]R-%Fg/Ik%;(L6X7;fw0"<3%.LZ:D/_i/f;cJD9Onq4GLQ>DV~p|)/9pK$!
9D?C`9$.X:v<g6L}v/)3I6A|Iq=)LSSU_*^NKD!strP4+W7<kVPO>"s
Gj$y`As&]1MgQU986Q)6j&4$F`r2o:79(jv@Rj3)`e`:grg6eqdyi3WT=UF_Y,520XsM!s4Ms+y{EN9/YGL.hiQRmqhX))aG5*6$K@`!kfPIBJK=2TuCJ
`/]0?e&0_KoNw:=$gHKOC@MS5JKWw@eKLZTkv,!3%=T]4h.DG}1~@a4~^bs#y*5GQHOESWWv?l=#smJ~:wb2^Il=9*Eujr$0Ed3<mo&K^l`x%@qCZI17ZJC|R2xjV$xB>QHBL},4:;Fw@|i$YTo@<cDe9;+8,?
mb=0Lz$;}"VCRd3Zw(}LE[tJYKt*#xVcdL>xD#?TrHciuMAY!u_BXe0lhjSwmLlhzuBgy$.-J3Qa,+4giBP#nq5KxS*O|&M2Nz!6p6I-c^cgVkklE0+^arvi=Copd&Ds>m;W<k#?ZmK1A0=
AT`ZUQ_K0b
O1PE6BSIlF+v^(L}RN-A
4P}EheX2oi>auMS@+8iqfCEUuVCG(-B$;Q!aoL2n8YTe_mX=,Um/k97qS,XgB,&K(^:M
);aK$]Z!/eFXq5,C8f+%O^M`c2EoDtX-PKpa]VCCb*2;Sb"k_(d3k">L1AEB2?
1^W,2#dE3+]?0r0a}K}H:lhxWrH&5Cm*>;hOdiDI($)&4f}8.5TYg2p0O3TwK/;5l]|Y>q])[@d9c]@HB<b#i._iGsuql5q;nnQU85]L+o<4hI0+OU]($NF)3@<:9bw(t0/I#p5My(jpy:ncoSu*C58Hv=QHH4)Sn"bK2:r4`t=N$qn/Jkx%{]=.KG=E.Q&U%ma3c(AASoH&:CEVC_C$LB#`H3!E?%ZAN3t`1g[4Lh{-LDRQ*Zy5_d:Gkr:w/k*F]"I,FM4gtwhhe@(A~Fz0raZ(6n]G<9ijg"wSYFP4LTj.SH5
zWZj#)Nj<(1$V?d20/#WQIImFSudiJXFQM*MP%&>3.!+_D).RG]oY!&m{**Xo)8L}a!58v[#/X2#;)RQ+f(00G7T!7
me]s=SeiD3PopWJmdwXe9/PO].J:^R1TakWJf
t9laeq2BGA?{HG(
CL8=X1%h*?V=Q%SC3W5F:~Rg"$3!RU3&l}W!$X1!+g1)%4Tn8-<B#Mdvs%,`a+#QY_E~jKI~JOdRR+/Y)K73q/$Kce0?l
<[LBT6whmj/p]Ix-p0iYPzuTe]b#ntnf#)I]bGo2tDj;*5Y3BC#_JcgeW5NY%#5S3(=3R;>+ig$
Bx8Xx2dkdh8BY(v8T=?`?O<*!kj;w[
Ni#2pqD[
QLT}(^eTq9gG_O3bJ+_japM(uA%(aZB!m.h"v&?WlL2X-,]@0$E5jyauGB5y3:rXJ)]}p&baODi&Rb,?!U#r(RuWAp/.<^LN4At(nNlFj!(6t$DT(%0eNAP*f)G<pG[r)Ib&c4F&oxG+-HBrVWaDy{$O0&pJJ6aK^>RpLVnW]j]c>YEifF5dTa!R)@J(E$(O]Dx6n2"?c+vs=8g-h~ZV)^M)$??$#Y34qb7$(sn{k_E.y,?rAbSCUpKWd:fw<js@T{<@PZFt5u*f@G:~X;I`6GtCh}JnKs7L^M-7J7(2.|D*n,TLSex~+mh#_=QEW<%R+K&A@dcm_JsPsV(Rc3eidqZK?xGl+*#]NqFJGK:/Fvj+?=&W.:<D3]u^Xb:,j/CYGFE"2"8`]@D,
/(_"Xc/>7d1uao8wXWsKKrX40@Gv+<K2,a"ceD|$$dt&57<lM5~;#Of-mnxE|Z4d,ZC+}erXP1k$O5]mn_=X+CM=q[Uhv7zpryguldPaaI=Cgg=ruIiXi1WFfC?
2aVM1tgNa)__,J!$77&>r<]CEeX`1#plasFggkOsQ(cEufdn9@j<r1".b_Srb&w+!_Qt|pwuyh;*jy8D9UXO`StG/I/#Q0dfTf|??*D-Zswrf8{uR^Fil5VPU<s?01>1ei:I#tX$[D-=9oSR@b8Ubnt_:U,OsLw1vo2@8cH%4">8zM;0jZBOyrN-Yiu8~:@*Scd2_H9@0XMG<)X^L;5?Q`g@DC4=5mM>I=+=mNP/G@"_O<L2OK*hYLS2$AP<3J9^>h{QE?cy?%");@=:ji|Fp7ae$fs1_CU9myiz)k&@f$grKDOu%(Y.O=?.fn6+
@_Y|)n2aE}eabS@|Dng_LSUC_-u3-1UJ=&Cm>ORE"fAODUR/cqs8hq,b_wEaZCGEdtpYIXjzY[o20ZBq6-n/lkN#4I(CV+nc-KeeYyHrHp4ickyc>.eN]sr&+GJ@oCV#OpgPqR8Biil9/v71<eqzvFVX`u7yEqd<.1Hh:SP_X4
(Ns[J?4-79")?0E`R(:8~d[s|h/+lYCt=t!T,_1,F6xC0Q)m}rl@e>.2_B$bJL.8T3ahGh^`Snyy!?)GWO^TO2`jJRc0QcB;W3y1PZY7iS
Uy`Pxi@h81Vc_6TZ[bi<-gLcA#2elPU~&w@J4WdlN>JgJ?IV)hSOSg13OI`wdpy1BUOOR4Ou
ml-9OUqM^aP%OJ;pBV8R`<?<&3W]VjOE1R09cm_6o;41dkKY2f0fwlu]_*dlB6/9_cpUs]M5i`":&+U
Jry(=Tsq8Rwr4F>H,Hm8`b&)p8Y={@hv?JdbJ-5#<G:(zQ~`tE2`g/}:;5psBO67Q$ki&ZZ;&.sZX^.Yiq/=DQcRs`nb_Ro"
9
Js544ET|4+Xf/;!E^u@8<)%4,VoCyX=XA(BVIy<j.N`2i"^As2,26:"f0;Q?XAw)xA
H<XF}<LnjP:;#RQh~WZjwM#JvFgW%!gQW)DJg4`e02+v(E6<i#0a[QTb<b,l~DI1$LeM4ALV=)YeP"0H$wz!TS{dLDn@1vrN40$ej1j],M_r:[hNn$jMlj[Dp0<d@+dtIDy_d
aq&MbaU*$k[O3D2JVL,AtpoImGl0o_i<L2Z`l737DE}KhWFi)?cN.9l;!S:Ira-/;8e*k]s<|vVuKhWCc-p5-"k[g/_jy/qrb-d/l4BAk9Mx4L}@uNjH[*/W<Jx/l$;N@=jbjr{ooNso#`vVtvB_C)PAmd<61^cQ,%}t%XEhch$_(CTyk8c%Kj!iEf2FWwAf0`k05i*9H1,r
`GuGiabvZk+k[Hmic]u#$9I+UjHoM0[2w|ddq<Q8DH9qdj-DNO&<sF:}PG#3km*VNn;I8wX&w+gnbaJvM^;SX~bg4TQqJTHNVT(V3yl)T{!L^5Rl6o9/c[oTHq&(.:yBAumh1Qf]-5l^b@RkWN_6Z.x=v_E3WWg[9Q;"BPv]-)t&0L^xRP?0HU6Rq-/syRy_l@%v(2IpKuqD]5oY(%;d)"k2]^+Yp=U
!"<=-mQ-Zo-qX%OJr_[D(7J!^^wL:?Nk%PB*u`A?b%8t]p_0:3l0f(ayH.FK&b8qIBDgK+Oi_w$&oLk$UGcj]WDMWl+%2E)!]~FTnu2)m<hM$"#G1P@!.FO(GOg8/@4~P]ejnDn[IQ;P61*nyN9xkYVLagAX!Z!Df5:Lmt/{&}w^I5_o5fs+,I,1CVbvW7rAoEC#(5ZI1(>t76q]H0:Hw
a.csn}t/HPBeoC[N8s?MWr"f;Y9TEUBTaP2"v<t{+oLIMgQBHL1&LI-YWKJvT%9Zo@c~Oqx?[{lXFH/`K7:TGc*D,bfsXQCo?u)|7=yFikI,2JFl]ht8r<.n!H2-2~D~s$EicyQ;??ccO^KhN$#E';break;case'tr':$d=',UF;BbpD9,z?Yd2.*O]/Ki4l{aCfNhU:sSaOmVpY2*2isUr.>cV,fN?&^<@W`g&onl=(|<g[,lcwe`?nc7(JhsN;U7LmFx1JD^%n

]7|xRz%wNsRgXI$1FelLB1z=R2#uQlqid.=+Ot3yCu*>gm5V7H>ZU?2+0ihZ1u>x3b3V5F
U:i4:gr<?;kB^H1)Fd_B
VK4#+;)fg@afiZ#elJW],Ibi:A|FkoMi1:Jf#je-OhWlo_:U3ck7^@B`Hs|f*[^b8s3a]4/m`(`szyh^4vUg(/(nsX_xVt.vHc
e1d[Hsc4B_c,^c)Q20j%f_qg(F^+bV>)L$Mpv(l>XsnlNe8_%1-lNL)".0N@=FE>*1InI6;fgbt#w/pJ=0fgv}:yDg_dX<s-%(r_.-29U6]I)m#W_7QYMs(^y|E5%iGB$"C?%YJ.yGGDX<U$>IBZcP=!
,Ww@kj=a!8JOh)
Y>hDs9uoXsIh`O8LWBv"LWXtT=gWONGbxm)|>8,/a^k6O(#=Akqjn+4.=rg92zEeU21D[UQ!CD;j3FQa65I=L-@]5NOc&M(Na:*&u

NX]YD$W0y#E6f)-S^TQ7?q*BE*GyylBg!oE?`)8;y(T"E6x4.n.&Jj!N)S1RjdC_-aP]KnU^k[MGt+yrIVhi|VvuEAAkb,Z^VE,k6aa0&DRB9[panI5MT3LC,-c`CbYE1B2Foj"`(690]kT5finPfwVE*`Efhe@:K6x:-*?65v3H%uN]X6$]-3Td:HacVO!=rd|Z|BnB5@w,w*jP|,VKB[(bAH{spqxiK=pMy_04*#zFqR},
YrmbXhGGw6Ha9=/xHVW+&]ZFX9%~b;p,rp4>(JCk!$fjMr._+C<QZ&IXO[L.=>t@do)_h1-qF_XYdjAkei]=g-&3&n^:dUs-j$oQvCor
VFy3%lrcJlh+@S96`0iD]8|-?T{!hb^wOGcS%5!3IPeK{Z^JYfjeVy]N*hLulbEZFpP:CYKT/%ol|GNhrO;CjmjXQO2K1#s>tDI<4,*RC/c]$m2Ye-rDmo_SvDAhq$Cio9>=[@h8M20^:[&:#Qp^F]@R4"Kr#7#V.P^BGTK$/Px.@/6-FFQ%)h!BsD0K"q~.WPP[,tJ=<1K3Gb*hPb4yL_O&u$p-!$"18&R?3R
rqO8Z=Z9axb5%*ylMD-QmpY8^}9hkKQ"OT5QpdMJGIFFQf^dTK"<M$j|)]AJm1^u]8R(T?JgPPocNFl4hoV.G+64X_GBo8l~N^,~
QY"wQ"|it;N?3M?f>9-"uy&RiIPmrm]KUqOAw<]wx7y*Cv-8H1k=,/gQO-4P5LhY=VLnN3}G.OD&J8KJ/Cb^ivvdvZ.<Dyeebc]mfIeWoHVk)Qo<;x54ifcD!8YA-w@5/.o
|i=cLD)-
U7J^K
PbOZ4eyOI:70+$tRamRsrH21yKf]9}(lG<5R7^:[,V1$D1^5+>u56-i1uuT3U/pC_evVh1J@-0E_2YK}_l]
bMe.pVV1=XOh*kM4i/.hY)Ub@M=
=`&v/(h:!QS/E
b+&Pf;d@soD9Y2i@m|`
wy/MuH$
5z+qQ%$!
LMU=}"_tC7Kmb?N2[<aD8m!gzonem;iOmsWF}n)56lTWG639o@|mKul5$Ty..B)mv&V.O5Z()P
0[9)@)?)x~2*1b&U^OjeHBJg8=Oj:ev_L?=KeX/e0
t^2137m_W3&!qw#-!RS5_Xq[]sUH)rvjB},2_*yf6s)9RAu(hfj{4@LMQI"tJ5#%MN&5I:eF)|5;lXV!d}vN9]Jsy/G&IU+xses&:@!R<cQ=jo[@jTS_;GGxI
*aCA5sGU@Pp,8hT0Z%z(uzy:Ms^"xX
s+Tr*n)Jnw5:`7+GckU-P
b8xOjrR;G,UGM[rf+jTv.Gydw%;*MBM]%Ul">w+oT,56BKtMue)/Efkq#N#xIC8"-d{QcHy4@__$0VVI;(Ct&kF1+;<ER${QK*uQQuWih),_`-$b}h0_?Na<nV`p[JJ/V^s2_lFK;_O:P3kBs68P*T:t[IqLUPjQEFrP`G]aTt&v8vP(ZU5Yy"ha.`ZbGWi[mp[<)PPe4RBH_%MI=)$#pGZ>nLk,FCQ0m`sa|12DQE&h#2`R0dBm7`Kd]bC&7=]u#we_`GQJI7/<|&y:PI0ge
T8cy3$EgrCKEN*mYKBf?j/XY3"6:|6:csR*RC+BonkkePwf6P7o?}YQ)bCQ)bvW"r){C;B!5<eejRwZ;o:W^Vmvlh^VI;U_lD;%)Tu(/$KhUI!v&kE,T_q{-[)J"z866vJ6JnZ%QSj?.QcF.
K!p_*?LQu#j/6In/#oDvIaj*:KG=Tp4`Fn9o%e#wyuEmoVBNVPdQ0I-u)9;=Wr,&k)l(RdB91K.;JKT[wn]0<Ae5F.&6SS9?b62(OnQ"ZMwZ`j[T6S3hNY>AICV1T!xz<{osm
ha]{S|LWL4nu(OQq#dZ~dO`Kkqe"(B#Y@y,{"QLWuluuJ#ccHK:%gDEb`E0"KXE@)Y82oqHqlZU^=7csyg0WCa;]VOGV8WJ%*4,w#Y%BR/Eh;0NDL$e!2fLO1:TBMkdVQNvz%6oR!4TsiakpBHV%3zEAT,z$T|JCd,F3X&9wC4e(NJ=su_2J%mF!^j.zW9g9@Co*pL,gN;@qMDYJXO2Jtp3.w$4TCr1gs;N8_zI[Fp3pmnlG0iTwFX#.W^Gh4k5]IQTXu`3fCi]c?AQIw[$mXkZ(8)QX_-0"CZ`Cs4=DZGg]o"gf%xa;-1GVVVf$^kDL1aK~BwDv"`E0xiKuRg"-<;&alsE:)T8?[sR-Z.1eA?5be7TRJb5*X&^-$F<<3t+/Y[ONWvSza[hFhZD!L"H=U"PM^b<s[?[CVDi$eywT!$p2I792fy1jdtoR%|DMG%JahgvW=GcmGuEGBLl$`P)3<`BpY
VWLsT";A+sej*kK)*sdP[>-~ZsI/:2H^9XA8U@SvP];Ym3PS2Wr]>SmG]@_3"u0{ar%;r8bh-@P%D21XxjU:C0g3N3Kj;NKXI71&?0^B8|<w%?g44[QE(BAMt;ht&o?lPwm&aD0k4O#:4dL?2_+e@Io7yk#x*bVWp*()v|l8S?:N^:i#5myC,/k46NI".iZ_(}-DTMBx6sKduEwwXg@oAqH_r[<;;ph`+uZz*1l6CDm`&aU&hD`u>@#NR{5IIb)eg4bVrP_e%kk+^=.K2=8+*5%Y(EB%EX2BP@kQ)zRU.Nm!!mp6XzyM1Yxf[-h4l&F[3zR4%D)?<P0
oHs-qa$I]c?K.udebxoGk]lq77cnEIrriX_"+WEVI[k356#zI1pCkVH7Ik_C45YiDQQrB/ayUtN+05WAU~!D(-6On[w.WMu&01mkBXdk+^`fp7]>3!rt#rWP!V,#Y"45_XP,C#Abix;8KsG?NpF_Xkv#:"jN`D)j`p^;]n]uRzr=H~/I1^))eeZx0g9zHJZ^FOV_3elF?/JM%V-}Y@QO
t:1p.2t*3rMwv?*oR9@a&yX;C%QU(#3%WpC!A4r5XLf(MiqRVh%pM
{OQgKjQ&DT;:I!BgZ/UFkn9Dz"/lPjkkqQF5O]4+}r593
Y2UUYjOR4+arbJUAf"DDO+CV#C3V_D8K6IneD-$j^0Yx|ejZJ*s^~Ks@uWsY|U:jPEim!HAV@CL,K9`fTp(Ru/QL6r8h.NWbYW<3y49g+8BM;Q!;<aBSjpn!a(r>i7[<`u/<:89-[hS/p1/Nk$b_E>vs7D,ueP[K(;<h_sPgVX/UQkcT~4H
1@ksh:fx?50M5!7Ob:DiGifiX%.>(b9N|XYXBNj_V?~?H8+,(4N/|d`nN@Hs;oUPBs4F/0-d.UL`tI2=(cra:DKgf^4HA*H?HOxIBw?rSjgS`Bn.SCV^Mk_Sqca+89M;>mhcc^^Ed)S]L_V/Qs`l,(Acc"&7_i;ip74^.wb
B2cN~253AZp@0Y!`n4(c,"/<L*Ml%^==WZK/D_T-
!<E4#Zt$H.2U&u2er)e;,]S7VhqIF[o!!|+9cbG)-`jFyr)%;Np#29hB6*:B]-F+yTDXlZ[)nJ+tkS[6&..3rA
e0VCCb/4GMOYuUIXHQoZl=,:Z`,A2xM>DTJ&f?e=&sj44g~sY$,P^-"_1OtFkP(ecT<+t@Gq<A+l?m%#ep6<v5^Nf7iE0LK_Q!DSdl-Wf^k8
cl(RfjR@PEs4#cJhAD@S1<[MG4*lY%aT7rXrtbh32X.@mp=2J(v{m]-YnWc(H_)Ipw)Hr8te+z^478p9oophM"e03fYn/%LT&
vhK-y~a6/ByB^V.xk6sv(xw6B^*WGQvL;)&oO.Ylo*^OoF,}@Tu5J
c`>:v8
i
~p;Rg?=gNQ{q_vH
=F&q|rucYnuWGWNIb,^=w`YLwyyj2I>E33RQAcSMIJ_guts(/`T/.:_ck,!Kt.1p?`GRzn&f?+Orom=kRo1K9p(IH`$KZs|3s?A.+aPcKn*:1Fckd/v3Po(wDo)';break;case'uk':$d='"evLMaMD9,{0L82!Z8n/UhqRn+@-q#To9.O?Iu?cf$,I:VRAe[u"_$((JU=/y.G4[]4VTh&[+h,ui?|?bcC.#V}C:wf?-w*t53

<FUHdHY5vb4ncb?;`ctR(Y>]xP"bdv%TnV<x!;H<9x.ZIXPTm^gv{,>j^(k9fJNB"byU]lHEv!:-%r[l_Do,,mX_VXZ(q(2y<=PVLibjZ7uhZ[h5I#8jcWGmq_:`N&w[D.a@p4U@MqhX"Pdw]uz&Lm)X`n9e;L"m&;g&kUAN:fc0?4Fx?UZp9UWN&fdT,O6TFXXrE<.u5tEycyD_p@1A/MU@RrO4eu~OE$Nq2_wroXv/$*u96JGOns16M+|MQmJs2E!IvtStMs*FjB97lO)dfB9Kva(n1h)MbxB5jB}`vm;L6tO`q%97c>q9JIx<I>V7ut>Xn&xJr6F^svw5g]^a:xi4sh4K{h%rC4>a&Y<v2sd0JRaE|J0w$[-M/FS9h1TNx:9"td;`+#2,>OhEc+bpI;v1*G:p%!K>2f?DEtyo-e|#TX2%*ld;o;YVjtC[/DZ0=mD.D$GT3[HY;D[DxgG;TbHB?vU0`1Io^Ey(%^H*Nm1PsOtlw"Z1XA+:dE?0|pUvC^8Hy1(-Sobfk[i+*&:kd^zILM|2,yy!jgB
U4>O7u:jv]9?lYjD"S1Zn!<2g5tt~hbg=8Am691$G$G[d;RGk+G&])%eQqDHv&h=]u!`MrRJNCfM50lG{L$E%Cif4>QvdT
ocjtjQ`_np*+meZDaIqhC|h>3OF&N.:-Fm>)vX9#ieGS+kx6t@`{pA)5_IJ_6B5dlVPjxambKB47tx>xDF%EqZp}<hHaUNF+1Bq7Psv%HwidqZ>!wJ@FWj^Ni3YI]yD#J#y)U*czu1w)KqBU39@.i4LOv{m`xZq)v}n0w>w?Z(qjAJ
_yn^$Lngf0Ef=GvHHLWsm%iBx!;H5PB6HQ]VFrFRtm[6@<nmHW(?tbssYP^fGBWf)o
![E+y`g#r#$
*9pF9b.=-H]?JZ[vGOlEggV?(dwtOCC+<p;"_DyxE
.LRYZim
%su6V)+7Va0B1
S"&(dc@X4~MOL
Mayk
gfta%A
Mbbq4!8J?dbMN@_)w<&;b:Bp8sAy?ELa2_
sM`%J
FCYXm`|NO&=qD)?xot(PB?_cLvgg~r]#`;C&#o2fXoB^rj1"]jjK,2Z=Bn}8+gugV_^R:cx6VT6sl>8Fa6"4T25#7.+Ulg>u@VAa7g"Vn#0$:#:cM*k"8J=9Ox-lpV&`%CN/?tdj)FDqAM14Ugf^rO.Q.A@A,.@)ae+fb<uw0R=x]VXu
FGlb2B4qhvZRywp0P5QfVD,Q``da-"-.eWXJKUZ$n^O-xtvt"%`.`_CmG=Ikx$B3REAQg2ffYN:m=A-zk.3d9dE>TBwE2KT{$1S}CRNjt;B3N5VX"LJ{PYGKfST6>#b1ec@9)
hn6t7wA7/.x3M!-`;oIh];t*Ml$1?d_*yH?CsFqtt!_!E=yBX/*<W(j$N)4gvi:gG,N<jXTlD]
#+f%I1Z+m1GGG,_2Ek:
V[0>SS/SadQ%-ncmWGfu{xoE]tFKzJGog,P!$tP0coLpm0/sb)t=U2z!pA!
CAIMl=~ptN]XI
0K[Vs&C^eV4X;Y7;%
SER_nS|XK-+/"P!!rpGv>aNNW761r[adC.%!fYz`=sMteg2Ahq`M@Oy&_iFteZP
-/rJzkNqWkYfq)_C]%MHp:@m!_X0IZoQl@gZxu9=y=MK|%X#}7ey8x/a6)$ayR|8c.<yHPi507}hZqx&ID*c/GI:iuu^6,6WU$r_4#lMs;?)%`y-xI@+PG<S(e:e^eeC
ic5<RNDS%Z5EBjD5D:LR;lsmor%KV!eRS?Rr-xZdmgCne+`6,N:{Ej)6pPcwI
(g&[LqHqRe@]`hiz/LpL=fEcj^c$mU$01#2GpPEA@Cj)2Jsq((M=t"4dPx1>lp&p2p9=EaqFh=6e9JRt+%fTF_&7=r.9->_jek?Sql:b,Fy=(-
c2}uh3"]!Bnii(SgQp^Q592$BYQ)Xo*ySI<A@?#p;4#GA^D;
S<LkKJ6hI;a59]J~%Ss(P9[Quf7^?S9EZOQ6V;NwZpH%(QI(,hh]&H6R9&-a3%u{_K*tlq[q4{X0Ip/CTpa^W<O[Z?SCVDHMOw"]=</@>
n:dRK($XTYVLXW(*&DMtaAE
.CZ:mq+5C`&YFafitH.
D8+]f
9U*kvl#g+VmBY^pVY+j/
:y%LN-K(xqBM>$JW;_|Q6-%6"GKNg/s"7s5.2Wu&frM4z2!32!5p1"rM*f4]3e*PdM_"u!?$t^,;ry/q3CFl]7JH9AxT-assB)BN]P:M^&{)"""?44B2rt~7mhpT:@Dgp.)gbYjISExCMfm=c1
D9K%uIK#L**hOAP@Uq][Wx^f2j-w)5;,Ml7nG%hp+gd@GA^(9iip#vFUC4Jv.Qtt"=igcP2=j#6JQG`@Z$?;SWD}9]?AfE9uCQ/`nEMbdg_<h9*k&Z4X[58}G<f(<.)2qIhud9X@QJ234b"n1de)4BknR=Yoik9HhGOL85R^
{<HUPP%S7Y9H*fE
@ZH?
5u7
moKJJ)YW

p#p;!qskIPdS6!&1Dn/c(LQ@4qS9Yl$[aCfAl{"Pqh`yT1S"!k^hO[r19=f;o]<UD?:KwA@"5q<7<0.XwvjA*y)cRnG>U&L@o
c-?$t0e06[Uy:{w!!KL,M@O5^nBcB,#>2V=j6_(]h}h.<=h8e##v&g22-f<[D/I^A=F/*i/j)Lsz@CkKPsUucB+j.^e1ROi]oMSm6_)Vijkz./G_<*P^):*h^HS]EJ?qh~Iz<f(d4]bCUKmLK8H-:Y[EZoEB=jnX"L4UjVJ*AkD0.|a7BCtOa=MnGTE~cj/N%,_a,
="Z*x%(UX@DvHJihh$l_1I_"/(N|usS0I,*Ge
,kIRS>]aE&s]!o?9y!1k3"r*J`/3iaL%QDrd*#gq8wHpMWG
uEFY1(LW,lr6u(]RW6PO8gVUPN.haiqWx|TQgj`9/~">mB0}3_@9mF=Mo8-@"f<yfAlG+)X"3?SX;:SbcAg3*Sia*f1a+5nX>nwl1io=J/-y@uX1vb(O5_$5)"8-FyDekB/NOuKzHWcCD
OnZ7]ma6YLXy2vNc1Yxb>?I3H-bPB[7G&`[N.ad0Un>^qgP&*$E
LWo:G!!h:6X;dJmLmY&!>3[luG[g(uv.N|d*4OTkl-x
-M,N3{x$`~i!tL0ZX-^k16d033?c74!~8?5j&&YG0+1u+."{nQhnRs-ab67N0)wlr"3F1OIi1_"PC]G2.dSc@M
]%A=4T7*a)q`R&+ctNG-D"|UX2]>_lkp~6E,/F288dLuA6c(MU@E9CWC.U:Eg4fF(NjV{5#@}uH9/6wkIhhlU@m/kC4Y`o:LdGlbK4:I)e,B7@<Y/3Y*|ZF-"(X`op@k:@B8;$8Z?DO2H/AYH9qZ
pNfNPxIXGKCi*|^d-w&B$evlDqR,0q24v@r{p^!bCNq9h>NZT}DeZE2{@Pm$WT)m
J"`!>AJZ_-yu*u}@Nm{sp`.>J1l6qWY_eCf.>V}:1:8:W8FOD9MoG)V`CP4i`f-4*_q42FqijCWHS&5$f%Eg%
J3)=}%[Ycua%j69@"2)I)9hA4MnM),}/*dH50K)k4LTG|4FF&J+-Naeb!MY0_Jb5>Q"hK[]N,6%l
uT."$t/r0~o.aWG1ON0sU-^hU;kP#ZvOc3l`YYQ~.rbo50Xd&o<Q@@+T,%)A:l8-X,QAK<cs>(`hy>b~;m%uJu`<C[FbdZ1J)Ij_Z"A69Op*Jbwo
4I}R2s|%"n@Em%DVL>`./nJO
KrgkY8x9/u`3n[<15#8guqEUoARFTaH;!bSsr9Ol.:vv:PXeAi/]wF+xA:f3=yKu!W6#cBB:,l;D=I^aSwCv9[*xr*uAkPCua_"FN.D.RHQKw13pEP=vw2H~%>:%mw^@<$>x`n#MNKr1Ooc"ChfSJg%i!}YN%c9t
s,(93qIE9fgms]=)$P}DO(k.%b42#Pn/,Q"l.XSEnJ.Xf+tiYHhQ!>K&xBxK=OOA4$jED(DlYD*F]k0?La]esr[
>kTcj<Mpi
2
k_k>AOG:J<-d$_10~Dj;$TPY)&$NA<$^B_3QmfE#h^+Vc4(3ARVa?7gB*VvSg@SaA(qjP2$%cOr#6s{"wC5?|vAD"&*y!M=-3)GkS+p&(q"g+l.A;Y3ebF*Yr6$`Z?r*34j!6j%#ha1TXb_){W
BTDT1-oegkbpL7T/Vd39X_J2>,2L(0p+L!%*Kw[%tDk?/n]Rlx
Sb&R3VoyZWLeLA)Jpn0,Jh^`LOs;+&<8u_dGL`G2uoo%=o`GV.7qiB,wXSs;:rq_wsy@+/<l0_lHMNLa<I{"jw>?Hl2R1ox=]u9UXyKcv/&BFNnPY)Go[=7"bN`7y"nc"jsW3%H
dn`[;-5mzr)R
QwDn3~o"qyS1fsf,s+vJd~l3cOik`[mYl^o
n5$8?%^4@bYZiN.^
sk90/%l=?S^F;8Y3n0&]!"RF8rKlZQYXD7D_Fcrw-VPgF>@(+^"s=X#e/C&a2fM))N><nZlbeRkp;(jCc_L_e.IBI-uA`j?I6gW/@vkja?5NC9GybG_9`x3"_a@K}+L3u#ClkApDabH*Gbd0viN=PLw
)^sB8E$#I<.DP-[Ac1E"lMb?aPpi%;Xspi6RoE9]u5_=]L>
S<c>{`c[y(Jk+/#x+NHb~]u]0VpLyglv!I*g`1E+.y{5Ha2=tO=wv;>+AgHeB:EPc"cFajSXRye*u+"AuJf0,KsrW@QtcSnIgO<o,_Hb^"#Pp:nwPDqc0i4KI.:%{yPaY%JbjsZ%I-U
D=y10A-7F>Xxf0*,b`yMg]6qA;WB03K(>yBN#xZKM%y#*Y|JJ;b%$SP[Ue%GqP%v+
s#~anh/e:2w>rD`wT(rU.!i@[0Lo%I3n
^LRDB5#QFs
E
`RjlpY3Gop,$D>M&N0yFX[`w6X@yUUnj#*DAmc_/Aa/_|3pQGmOMxTMD4fA)~=e62AQ%8?K9WJPMU^XdLR6IO3x*cp97Qi|=vVO-2)k?}VzGNlZ[4f~KYqP<h8DKJ:SEp=wRL
2f23IBo`=tt;0"@Ehm"8J5tf1A.ruh,ad;p)Na9?lK?&[>3^8QRr0EPN]+Y43.xWpuU>[NGIaONo3Fe2-`>:ZfGNlSLy/9pr7?75<H)IokUtP0i)|Avw1?6]oblh`jD,BRG-6]1T|:pD<6.!i#M@@)c&xdTP2MR3{fpxfYTm^G}(Hf^IfviHRoUy4)6jNjMn;)62@>+m,p0n&3/UXI}_*ln7"[]fXk+Oqd[k=`S%7Kz5oqd3Dksf~eG7G-]M7!dGQtsJX-[7HkXmBk5yuB7dv!#j|PD0hhbum2eKq4=0eI]Z,%/M-lPo27J&<NASrpmdC7zQuO6D257"O]xc|QN';break;case'vi':$d='#UF<f1<s&B~?moG&/]h_`:?Z_7UlDoCyqefLc<&OCm+kN(E+"W,2?G6$GoJ(~Cm;.V@<aUSi2AYlD]o..0KeNSTivr77:i&ccVp4h/g_!wzv}_ni&Kvl!s.hQ0?)pumJV>,TXK7BH8}yeB^ArSLVER$-52QWdaVK63XGNuomdAzyLB?sY*)F2j9X,#=-aJwM*nRRygy4Jv;,/BuLQuI7,`QImPOpMKnrKd4Q-/M&)6d/yioh.GXmZn3F<VP1r:!XsAR+Ab[JIriK9VwCHRzl$.MZ/q.]c$K1Hp6y<#
nso
Au<@h[3<^tnq4o.&y`(1^<23:YIoH:fcZ86|6=GDt~;?+(WQx<Ma*!mYw}n^^Cd[6epAna*"lw/[LG&gbgSBv31<7$51P(mRCXjQ&Z-x$rM|ju("t/o&VvwwA}l3Y#:$jd?!XGwgT>7^?HnXNeWW:IV+"eC&g|cB1(@
3.!yU^9aUJ,+xUIDrJNx"<QP!pd99k.b?[9fU`_35R/(i8&aMQ[l=W`SZ_2p7w6SnCqI&[&fVO;<)%V|5h`(@jBTtnsN_tSa452>h3,|&aa=B]I)o9=#Ksy%iRG9,42aIYvd@Cwt*03X%=,;dvVs[Q8nx>8gG,J)o:RGXq-.@rV
y)2pxxsr;ObooBj]IU6nc}b[1;)Wv{KLc1>7_YS_uvubS7m?gtgAVKsh&iL+mcUlsWc[#S/~Dt`KYGs?bu@ICcyWYJ
nfWTvUDK8osSLc")nju*)GT:`
5]b@:6GdELm<oP{Hv/[sIW/coKgu4vpH-u#Q_BRl}hxJdIfi]E@A$Aw$l4yM1u%*G6,2ztR`u2ov;s?((?"DYSR>ng7L~aj!+gyb>.vHzO9Tpat$[xUJ)OeLx6YYlgO,VGqx
IBFR7;;mu
wF8^";S3y*:x#I-ZYlI|N0!r%zCnH>Ju@~hX/:y
VsQLHlX%@j.d,@okl<huUs?Ht;[z1_2~qKV+:O##J9Iui?#OSkX5A_%Hj3E<5-K9b"NHXRz&?syR+mnun6Z,,>^5i*).H:;J<+LLms.++{G`EZsYuxd1<;/"hY+_OEp.rd+9"Q
f.MTXs}pSwFnMWHus"g:O^W$4yAA{.R<at9"Srsag?5oC!#h6U
>#g$i.^{[XUU;btksx7C[o8{T8(o@!LyG[su*Q)pSC!gs/Z2EbYYsO$"9rFUMxE$;EV0+sV:ChY;)S[gmkdC-[;r^[8D4`Tiv}-&=:280h?6Q|_vWm%),9.I!`g3O.XgT&r0rC>X^BB!nI^V$[:a$???BHy#N_,
8]#n>Pw"vmq,Vayq81+v4#G9[Q4CvN9QucGxE;m!UyZ,6{g;&rdk)etl$W=u$>KP4VqV=FXbyb4~mh#_ciTvjKEK#a^Xv><]SLCiPWtQIrSC<t<F3I5_("asksI/
xZ4e-wOBP7oQ
?g>C_mn?6Pq$::dl."O5P2ucq|NKS3JqA0oFj{wEK]M<mNY+$t.a3w`yC8PhttO@H:<gn>4V]|9YDF[_.M2vm5p>S*k=hJ4hP(N5#eB
cEq,k7F4y|IGQp@BrY<ubSz!r8,wvBay;TA,!ISdltAykhdUw]fDX/:Vr{8mG>Dovh<1?j7HZjcnwsLTFa>5cDq)D+R|VrLoL}mCZ.8z1I:>!/-&%EfA=k+;iE#u%$A,_K59:OsCRcJ*tc$9`Ds,"#G/,P>^GBYOHaaQ9.[0#Il`FYR2S84Nx8bk`^!BGh^GsJ-#sCH#`o+b[.T{Y26J>1tLK^OL,@_2U%8Hq]ayw>L*oq3@Z[P0ASN&2D2e/ksySn
y,*2cn/wc$kk>d1d->=lmLpCwr4mvohy{/HO|Wt-P9=){dA1:Vvl0KD?F%~,f7LhR)t3@`l@7f
ly.]`sbSI1*wDsv=q$(EMD#XHD2!*9T{^aUBAAMU&r&#^Cl`"y8unW]+J5q#.WUpF7O%=$RKp]&P^Y$`[Jp?.OPS`[w"&|@!TjB+=oiFrU#0>?IEe8yeq-VkL6)>=Gg<7EP|v7Bj/dUjEWdXsgK:V|g|g%V{`I5},h.-=Ryv@{0EY;Ct7LDZ?NUx#Bn]E[ysAPfBAn>Emtv9"4.-g{/qU/=Tg32v>//D4D4VK2ERq8b4Ait=JWOio#uD8"yeJaF8Ks`c/w[3TpO7oK2wxi&
h-4%v@eZv"edCgiR&V<hs?Z_*X^zVrGB5E$hSUP.Y+Y
U%Ia^hW"X^74nq>XR.4"PN?El#xU-HArPSZ1ux<mJ@qmoLZbjlJam,V]tu5Zo~`}Oz>?KN)y]f(wNmX
DB9($-O<je6~Rumyl>N6q"_/?3fQcBlxo[KNL9oZQB.E]z/<!K`;Wx[<te9FU!M6MVq/Bh"r
^6/<<F7_74
_-`VT~po["sYjz&TcZ?,d!G~m/>x^qP&H{5*wO>6Z+%[M/,:*v<wN~F-dL<XJ4;@T}2bubA~a%[&;;(Vw"lfGEAzBxEj)EJh^Wg_xdfq:I.ExTNz)Cs~R#56RKfuay
e%us=,hOdR"S"(|g4.}Yog}DeaFF/S+i!9_9C;F;s0{YDmuez1[PT*G^QnK^B_577U9]QTo?WZk<[;l9Xm/Rm&5qBi+W*HA%D>#^g5`YfgLaTDa]pGLFFH@O/o{@b!usVf5g~^O8TJ,_;Pm[WD_1zKG*tL;q=bP7g+WF39E^FSdNZ*/w4!_aZ8rVz$cj]h)HJVS-#jB.cdf:fO}V9xkBNp|9rs/!mq5;>^-5lurum%$!;
,y<X0RT-?of/86(=aB5XoG
P&`9v"v7Kp#Za2+jKXc|4~TCiWrvurcc#]ltOA7qKr=+F@&l?800s+"Y>l9wx;ulD},|a5hn8IP@
YX|JpUu=$S6;o8/YT(E?/9QKqt0dJfr-}Z;,nG-.[de@%REaze`/eDIZ/^P-Cp`SOhS5mV(-J*&pP8/">mhBPX8Yv!I!haDqz<~8*5l`NVP*k<JmD"
syAbgrsa$FeKEQ0x2}hEAkg5l(*%.LPByr37ksv^+{%4@e2R/,lDIawuc3L>F2_vfEsSo(?M#p$Rn@xcBFI)PA4&jF?s:zOIa!%!u<SN_]l6@Bct)HQ,`8=@8KnMwNx3)GUz[zAh1EsWdOY9^IA[*l=cMX_c]{LkWIowIVOb7Xl}]"PY./lhJ8j_>nCYH+vs&
u|==&`Kj@+b?YCWR^QB2f/VfAz=)QupgB")gL-8d>AGl-k3hh2trY7]{J7OL6o5T^k<3prSHHh_d3p[^%]no:cu{1CVgWAT0Qz%1gem/x^W.^PT)vq4EJF?0rIvE0;_t3C]icDCEGLM-..Lh[Bpg!p>e6HxTKX677t#v<XDd/)LrPm>dKHBQQY6dShN8kFZ>bolHXo)wlrs6<u
B@o7BXqB<MXi4
RfqE>y?A@:jsVU2+N+(+FJnFP.KKe^{Rd>%"F#2vWeanXi
;~WI"~G[P6Wo;
S%wh0fT1ff!SE:
}^#Qgqs[#L#?S^TFOCMs,[yU}ualZU,
*sn*2k`Dq[@V5LP;x-c(~`k8).ho23_r[Nhn
:Dn6=."d:T/dfYj
!7-5FT[d"it*6j4P.Hd
iEb~DL[$`D1MZj;H`r7>]Z)76*I"B/:BtXEZ)u,[_k;~9/aWGI^KVJ2K]b$
[Zn:?*QC$wU8heHq2O2mArx~7.3^9)EM&Of{g;g
9LMUx44YG{&{"&_)j;nF-0:zX,>J<`91k`?1x(5GKn@oB-5g@6W/*]3a?^Y{K6n&S#pbmKm?1G50)e5ES8Hk$$R2Q?MZI@-"b=^uPi28ag4}Y$(_q3ud6v`2!<"q0G2$9/i.&|gn*^X-PF_a@IKw"-.e3[dZS8*e6k+DTR16o;p_eQ@m2s&Z*9s.NZCLJwq37dKR%#2Kv3Au*pAM8_1-P{>hWI5f68f"=:7t:NVg>
^BaHuJBr<xU"V#RqRF:Te(q_pzvS-vJVjei5F
%[/QYZdn0_;eYyh{x,,dTJqvIYas..L!"K7uk"lq?O.=Ibjg;$@974Dyt"rchFbhmXYxkF4"YSXZW&@ic%
1telJ+Z
4&48H)fZtDQJc$<R@fOfv:{t&7#]J@GAO?ok3w$HYG(;M=HF9]|q#*:K_cDc.:QJ4_#h11Z-U
zdQpZ3PJAS#])W[p5Bb;I@7G}$RKIs6KvKewkO<@9&>8IZk_P.Up<yR;tIfB4s~f3vYZrVtPLg/Y4qaE.`axTlg-8-@=<9THF=h<+*,M^7>8mEzDuSp??q
>A$
?L0#>i$vA?P@[PkKgl6&0Nnnhb$Ynue4g&?=,V(DqZw+%4qgsaWQWzI(?z82AHIr?[jbOyB`-44(LD%
T
-<t{<Qmpb}ssw:Kha[otVfWH+;QhwZieOJMuo$7eaF;t/pgnS)xVf3Vq#<L}MS"LCBUkVR[<k&D{AFE}%Y$>jJLx@x,uILu/]gj{0}
/Qzj,ir!D$Zo8]v$Vj#KqacNMd-U2iBaIVH*|[D!yW<?K3ju+c_@_%[d%J9:K9^>GmXk_>m@,FsC:=!p.ZF9n?Ay2X/^e,L4<YR(w<;LJ8S/cP!yZSxIbS47@f([e0D5&XS(MxN-@G0
9(=Ni_XtOU/c=iiUx7]A?jQ_;oIw0R-Sn([+v^!!b2[WPRL8RLpN"dXyFF]Q6SK>zhEf&pI<|>8fe-r&cYVj}.PhDp7f_9~62-X#rWFu+X19:nP^0)(BE^8_(YEP*Z^-(<@V&_v[PyTBvTK2ow`K-;^#w1XHMF4q>iH8`Qx/(ueqADBgnC$dF';break;case'zh-TW':$d='"R]7&bos&,~]u":2d-4a&2?ZO6&I;$j&Pkf@9>tidZ^6!17(ENQCu)k8V-v.NX0NX.q>SkE@cAu1`=ruZA6k{t!x&w*,7Gnm7A;O6XwB]I^qhcKH77~o<csBy3c39+[XZ:4kOb&pc65sEVbd!Tk@/iF&~mkq,=fDwVZp2hLq$dcHd3P>C`!n`F0mz8|U`wTf4l]BD/q?
%WH-;8B.<NqW`HV6Z~PE
m(mH.m=vCod!::;iRw8paI<9|j0e7,5Cp4{8.Q:gG-@ERK>>hV]cb-swM$Kdd59M8b%J}j88#g
eIr-x*vq[Vx`MfKvr+*iSNl6a32>xjTq46SFf^G2ZmuftKaa[Hn?
z[:Lv7h$PB`!o_]8e9i>v[U0&?I`$*=Fc
U[-[}IU
3iy`vvP4r@CY0@]A{<iQB:;ks6i$4GEjG%)lG).:)BXGf`!:n/(r}[enmGmMW[C$lINSf^T:5Un/V&C-P;_WIA1`V$zULLDqAi;Y7mBX
lHd
kGF#Z<FsjT9hJti"Jh+iGgPa@]%.goJ"j,jIL`#i;fkw*,T7#N)VActoGdL}O%4yT.[.PzEW`..dt.
}ay7-k$GUkGQvtynrO#18p5e2ZT(7R^gmwRuX#f.@[0MUkuR%O@>.#Vgu>sbsB(kiqluGVjG%9i]!cHg^&*`G]aA[O]_|%UfBU"s
%`xcGrb`l}wb*QXUpg+lf=ulpeydxq!"d&U[!b_i7h7leHg(!ikz$R5]>IB^I)I~F7;*pOUgbk@(tr%z;2)]K1:&Q&:Qe%sy4~PF+TMpT#V)g40^Q
_5VDB^]n<cGA.K%TY*a
TYJt(yONCq4RD+sFOsy#"_LcClh#^MT(K!y&6aNFC,gD2;ppRZ5J,zd!R3w3=Q<#S}*S4m-{oO``+294,*ICF
I?$+>^4sA~KB>JQrV^I9c2Pra_Bt(99&NI*Hxu>l:4Rv?*1OwfIn&/7IG)HeICWv;g6G+"rH,Vufcm.p
^:+8.S>Qhk5%M063_RG+j;/DMBxm-d]J"jV8u^G8ril9o^A`w<rs7=6;}3O)1_>h9j"?gQc;*A5(zCC$#o,sgaP@v0U`G>]hP9qYB,?,ipl`-
X_lUVky6(J+M`HjGRad;{Pe
%>Zxme7r4PC*eJj
dH.e{2:$H%<scmSYw9vXYGcww6,O"@^P!>%&Fp2Z$e_kD_[Zy5$TVq>ZH55lWj9+Te|)2]G5ebN<ynhd?kS1x[Jg~R6W]CxS(Ri1b9{X$074I"PSiE0a&]liqCy&g@q@OH&(&ZtSggTQXV"DaxW_^D:G=RY6G:)sJ=RD(hZkYkV1Mcw1T*.8+AZ`~mdK]q8qJF;qeKnq<mvWa2C/z6b_4%aijgAnC]*7-CzLqYVMO`
P[SJ@,&f+|LfXdZcCQiHgEh-xI:2Ww4,&&id1]NHgpW1>A_r3"r*UL&z6~dL3J82l=Lyt>QX:Qk9Oi0Yly#IQCTa^I&w7F*|]}8sVoQ[dV!#x{9|xnr<>6;
@e_0a]?%v>wyB[Rj(kT^nn-L(KZIr-HZe&-+mC*EIhBr@
#phd#da|Do1-fg)(G1D;!xh};N=3)fy~UeG6g|gc!c%J@H::V;opd""_#kPd>=Ym)x?K(99B4B&Y1hI.UY
`ZBmb]j&3_S-EKjhOh{BI9bp3r))}>&dhhI_%w0%`-?<TN9hfCMr%R
;UokP,Cs%%"!m#=PldCl%3-w0R!a5`Kvs0S)2}C<=
+--7V<e#_Dwu,_n")ZX|t2[)d11(NCJ0+9CHf}tJLW4?7e:Y(Y>~oue><0kP%hW6J-="6^NfN.uP,>^nEe&X"$cL5x:Nnk[J9NF[p#qUC$4Lg
tB$`np5eWdCDG8=IqopdLdgYb"6tK)^JI<Za;.3nK[)|BT;OD)(<iI!r7+lE0ZaWvC(."cBmn
qyT2ReMQh=K!(<8_j()7=C951BZR22t/VI)`q2){vr5BEh%:SEe*.)w,+bm>
[5VX(l3<sBR/GMNI]<}_C=B;IJH:lQgPnj-9K9z,%m43r
3`wJC#mB_
h")Q@P;+7?<T<(82-#[%s_?<9Os*s``JbVaJz]7a*+Sfx([N(e}%eG<s_0uG|?lD)F6->Dv#@tuo;._V{E]3c1%dA-z5:^LABnGs;T}p|ko*z&j8}frYH
4!MO:NP,0PM=pWKHtrjlfISd
1/:eCo?Q
RTz
tg]H2K%o%4!sD:,;4ch=C<8gwK}858w=
;U0$f`5tDUvF:pL!yv=J>3XD@KI4))^,YCqP2:MR4x`!f+#PP~8Y=j1(?0.r9$0)sg/4hmaK:Ykz!^D&
kv&9fI)-)SV^r[S2M"rDD7C(61:S~lMx~26v
r>X@VuX!UiiGR(.d.lU7(RgFyHf/o(oU2Ynqw%%>`rm9i1_c"r8Q%oXlvad#
2pwe-]<:[?7w6WG96F[#O<g,(m+!o8%/r@w/U.&33MY#%dHopEn[q.IT)Cqf,Yq$+L.4
%Ds+S!bL#bymi7"8pB^{4OSVZ^,:"{=]FD5C/WpVRbV1ag?5F.Ra>v4[Q@JFQq!d)m04i:((u[/LJC-LR^LFwb%W.0&]S~VrhM%_%smwgjMq
]EK^OH(Q;psxQd}`o6a0]pOW2EWHs/;noi0QI5KihG
vwl()YAt!wTeF:NZS*(D:k>glC!twst^d]>68%95-q),:-;Qbk;A1UEd*~-#9{pVB5`)ot6)t-To)kdYQC9m5ve==t98ozEC<|
3#wPO41$&.1GEQ7`mN@gyj/-^Sw7&Q~*-OoexUrV;:Q(VAFk<Kd^oI&sWf5HC3y5*qYY)6PjzlxxC?_
"Oo:I#"/v"WQ)"."ZApv2W(1(]=8GhI@Y6$HVu(U^2BhxK^k_Y#5vu)*{KnYi
eTs%+ooM]hca-ovNxX"yP;EkP!Qd`g[rm^?.xPe_~s5T$414W&`)wUDC~bicTia:yh,OnE%/_l+HG[,(^W74{1cUUcs%!<:T//)S.M%mH6d7hBIkGix5!NKnr+Wt`K[d
-;vO.H%Z]doUKUH6kbmD,N1Yn-C2P"EM$3(h":apv6>03e3RHivT2C%H3./sC/+^EK(ST(s$(GQX`1`gf}.4__.u`!Sa]$)
5
mt)-Vrjmj+qW7E)8qWxx)lyj*/q/1uah;d5{1"O6S^@hI?a!_rn_Ny.1OhbG:h2Suc
rD1@b1;Unii%&rd&wg-G+Zck_S.dOC9(gcEa~S&=I6J9^=t8x2?JUtJ]lEH4sTg8Z/gcbb>K#!ZAJ5DrVK=tIExAS!ENDA#,Biz&G5!i3-2yZz$O~f]9mx5Z."%bI@t6"FY,#m]87BJMmTvxwB@./.x.0
jxG9giaWG.T/`H(8rdje2??O>[QMAURZv)VFijK#pI_!q)3qdI-xp
JIT8-%M$-K;!#"}d1][v1)ml"-kw)siwLi#/(!
#ixK#DcQRiA~ERGb@%O{ce]"AY:yfMAOrLpHeWIqv$e4>/in^KZztKd{7;A7PbK|3Ojl6<;hjP)t:>r|aM:aROhW5FJ#KoyQ0=t.nxQk5dBVX"viD[2$C+UEq>0O@@Tn.5:!
XX
Tppcr8jg>-eu-%":Y<f:50g,#$Iz_Qq&`b8jM=cj,(207incqZ,W"v.;h[@_<A9gx:.?eQb/J)bC`qy%o;y}XJ?:sXyEkHf0kb92[rfc^mOh<;O48
Xs3kCwa3FHcriY0%lZC|d79^xUFHx~Hgq!vz.8Kv=,Werq5E^9(2H9$yP{RQ3hep`C6em]=$%>oeWp:MdlEeYIazoPqUBDwJ9Yy@5(T3/i$`+hd@+@YHhB<l3GHDmcW.*,L"%yn~-s>a
KD?n&;awVu=9fL7i>O4q`ZWg#*%GhC}$nDqtJkh!E-:IN]r[^w
"^oP%f%LrxN)[7^XKUHib2Ds`QjJ;<"xmf0o$WUhoUw?-$&C-"^PA`2Wr$$O#/tQ&9
NqVrS_Hx~
L8w/+>vDs)URt@[=DOKs0]wi^[3f62fN=0a;JOJ=&gD=rgYi4rRPQE=2=$P#kyj#f(n@OnivN/ZRJ>,Z}1Qj`k:*PlF"j:9n3MQWk"G@u_`sk]p/Z7OS^dp*!e~-?4&L)f9c,[c!m0Aj6L(+yT7Tt-%m8v!k%=gh1=POzk8948bXss[sDb$dRx#h|.#k1W_2s"u[HFFhG+G.u*t
!Pu1hfN6&NF=8+S[wkTq/PM=./M"vxfF5xXxj+|z)hzW*j3)$HJ+9u~mT#S^Jf*Q0:7G{`B3>P2VU6VVWC_NSEsZh$|AcJ3=P:-QFqnGs6oSWomDCI]r#e3s~j
2$AJttX/HV0,jFC2f%s!5f
c8jTg%4=wP+-"r)';break;case'zh':$d='-R]09hAp=,|?c)F=9@WVSHFWoBrYv]vCJUmEyC&_5U=?FagV#jR3I
8%KPl#G(7pUHD9`#r%k)<9X9GMeciT[J|Zau>b9uyK+>?@dAiB(K*GZrQnq^BtVoC6LXY<"peQDg"y0JQlJ&ZGRxRp|yAQsu4B2z!/Wo(<];ogFg|/V=0k9(wc+A3?nEHbp9p4BRKxP@/Sk@ei<0f3Rn`>
eZ5Vgl@qPT]zh{@Q4CFj]6[T+ob
Fev@0OW,&G+1)>csxrFMcdcXWW
/KP^M&WAYhl1QqcXNurkf?eriufyy
;ckI;souqG`MJHO[XH/cCW8PYwsM_b
v[a3ygvUw6qlv{[Zxso(1jQH5`t3K*j-<oHS?M+6i.Mp=B2`?AivhQt}CpcJSYWP_FZq/}g.o5Cc?OYR.zg}X|OjoICtBI;XJaG562@fH5j9FD1mw,=jK]fPET8_D93in<6RImXZ*L6W[XJ)?nM=2(sbkc5YI&let*umh7@plYGb>*HF))3sWz>;kk6d5dw|JHWMf3Ul
i]"ax8lH&wcx]>7<z"`77lxZU
^lck(J*ILMnjN
?BVhq;PI~LHybT_H=eCj*il>5
ob~dlJ)4qR
Nj3vXGvr729=H
B{I_ii
?eZD=mVv%O|OTW{t?%8WjJo1wt_A+f+m_..i^-`H>xKIEWEyRgiH-XN)JSsy[_$0}`o[F==Ho1VwR;A3
u,jr>*bxihK/1umI*5a!vZxaaQnac|2<K3yFIu`TdlFkX3V4V=w2q:j3Tr<0I;Sj&}rmCB*i
&@TEG*GQjqr,MQg`5I(.e4pn#l2iSd/>HKsn&`jDVMHjJG:v@l9rx@|[Vq>6QB=jEo(#N5N.#v`OXEF181Ncy6-k1M}e0oBJZUjtKw>W;qXkd;$kuAMEEqVh
@BW*GpO!a9$[cZq7IPd2GhB,J2druzHz#pNH`e.tu%VnMWhxjnpAlbH-TtqQywCs0&?5[V4DQ;anqhHOO+8fGbC4?Ml@:xmX"`sL]GYZ2>^}4@8os=hxRwQEYtIkKa6tW]Jpl@`A(3`qju_QIA)cJ]YF?In|2?us4(93awvI!>pW(g:/Cy`tY|qa`GEXN*R$dS6[*kl(?f?D/T>CO>p}s:vl(iagMk<Cb-7(%]bt>Q
R,FNHWPYqI
@Z-kiB3C7$ROZ_[ny/
sq);T^q&UIrRSgWj:6d3)*rNq68Fc7sLEZ
iYSi]8[%qA%=xyi)OdK)_*6/*jm9#8r6PPgbR)-j$2i3,B*|dj]yS#
q6w+__NuYI-^Io-Tf7{F[]ZKibc+;gi]u0~g
Q-]2fB*Li]fde8Nid9<zKe0nFcsb!/LHx*C0<Iel<uw8L8Ls0hYNT
JsP)
3jaP6.7H.B%#oN&t&!vXAaXqm4Va]lEtCXz9QON$cqggAv)#,"!Znew`^qd7X[u^?HdhDh=5m6S]Fr&UWZO@sC[r[v4pBaW8BvsSiu].xE^9}m(t$xTCNG?Hfmx[TAMVL`[=;.,fr(f@u]n+t/_Z.C&]6dcyQarJYP0=Wkrt?;Kc3+@H!3?!:r;jT1<bjOcZ8c
?A;X0I]2tQrK._E~%eSoEy^exh7
%8xpI32~xGdJVgA./AU<c.jsb9OuDJ*g$vbPohI4;=GZmA?EKB)Y
uvbuL2<_,AL<-waNKSS)*@-y!CJg5U).L
NCnuKQ5<5cU[Va8G
.fS*liJxtObBtrU1kNj60auI<z@bJL"pT{=PK2d,U`6u)N"U.Wl>/GwiELBUMkT=
g@$!y[QS(E-TTuiH5i8*L-M%*k.:^UocP1%]xGMWXgJ5b;j?b+oKGNZ_u%e_?_n[jjULTHh?prq2<oi9G>=Ps!XU9_plVN>GTo+Zse>n2_6N2BnNT3)/_$h0O=..4^Zi
_Iv(]8sYJa7[a|B?"fsSOGq]b;#(7EiPJa1y&;nvh9?2J1Qo=YIP?l6<I2Z65j5BB=K;+
A!s3R!6NG2Xgi956ng)RhT".pfcR3VO`3=1$j(wn!QT<FE%edMqwB"g1!|+fVA@N3-C",d8u6dXjaaPP]CT}*]Yc8XlDLU=6TCEMRGbIkz@6Z@e/^wK|GkfilfLY?]sA-C(u/z$|e,O*Z5:crp>L[T::_$@ix%sb,<Iv2{fJ$6Vf%X@dXHbVZRiEu3@I?cUp)C-Lg2#3me*&Xs$0y)Od
}mNoq8MkwX6AW5PtvFH
{kJ&
cmHTAQBo-uDmUM$Wt#fo/B5(gpuu6^-G;_`>bd3T.W_%wD>DU~^<1)PA={f:($_#M-M`rwD2hjJ1xLX~m}I`,b+NMU3%Y|@oW|,W7Xe&hY,rUbR.ONG%
jw1uwUHMg7cCvQ[e!G4
:lWEhiVyh:05guV0iJM({
o<R>Cu$Qdu9emvta
sgbKlS(hd~KTF?cnI&[Po++gdlVsZ?Qfb)+$BsCdy4Ipv],ZHeODU5)!%/ErwVEk0g-&0abin=QTb,fmL!kR
7>!4%PdUlkWby^hhJ`PI?=Ju5%GCv&WME*;UUAy!)l(j?]tW@MCe/-%(/
ytkexH]nb@dKe[1]K!vGF%/54
fwj*efRO$YIIQP=PF6D3AUd/0&l:*OoM,L|(
$WZe-wN)3}N81NI-"UZpv.YMRFH.)+Pv"mn0:tuMB(b8$m2s!y`:nzE7x!^7/7s2[SOKwHfD>_ZYig<JK3:v7":+cQ4,aP8^4WQ-^.ry;y)[-<t`Cm%#5&ll#R6%n$JpX_GUo6k+i)lP)pKINY!<s;Ont+nqD(^Flq#?>&?m5tO5!qA3UI%3CxO#rf+wfNHqiw%KqvjMmFN:jgWHNZ:2-.>:T&lOP.G|+O57x2m>/0R>-k04P$y-]{q(6FIbwl6x2[sXY-
Tx!
?K,e`*RSg-%+.%2h8H
y
16JO)
/rW(?Krey3q4M{]
#+3=EZEWwdd(`U0s
8b.A-BkK%*-^ohuO+1cQL(bD*pyd,>aIo0Ed|)gbvDX+KC6$fWM764Ni/ZB"UMts?%eyvcRce,A>#.7GO%lc-3(Lg,I?)5YDbigFW-
nV.,*!q]w)+KNIdlsP>0b~8|pCE(q"$XAYJ(LxmG?1(+:!#lw?RcD]!2+!3|G8N96r-VuzGA@2D[P?ALN~jMIc)Fa0#dt52Ct}!UEa-)mIYhNSRB39f~a=&C4Q[`P[WJnnw?[wlJmG5[NUv.HT.<PtI]t{.hB$4Z4Ep)ll)A0,r.<4#B.mY0XxLhAS!g398|]=CPB,j~hO#Sbhug5(cl"MmW!%k4:DoAHnO]%m?)gFx,(mq!sle=dW6ZYf,`w)is<`db3{Th)P>L9t"7K9Bi%>x.%8Su.|D#ei13WZR%0FsG:*@J*kI)]gci$>/IA)p(6x5"H&X6o6^b
u?X9"6t+4e)d8mgk?Y!Gs0OPqb%vb8m5d*a<G/-@v2YCw3w7G>J--NkiXlQ-C^_dgMP<`"
3zjv)t*}*pE$;$J^9hI7
6sMv,L*HAZTWJp`_|Gb1#ri(34`Y(5w1h_/s?t{EHemYX)`HD;UG:TKjX+F
&R!t~PLIQG!w-D^$VQC*{xxdEtfCy=o^`DX,;]}DV.J?5EB)X]X
9!YuooK9abo/qgm1g^z&|px?_TDdSx%v4NFbh3_.Ism<PlzB3^v-1h"D%K4JuIthNOYSD$p-d2E(/o9X%=5NC-jd&u3Ws`iWS%WAs$@o%h!.s;@OJ*qk86-=Yhx[LmD:eTTt:k>SRs#e>j1-v[O`v)Vv6PqBX5#"1i9(%1qga%NQ]:[/y9"g|]5oCvsMBMawt@>.3<5?iJPh_jFDwAo/iUfR6ypW)8(C7U^ebl!yo4{4C6QI0=nn/UcOB1IPkD85R1u;Rfhuf=7D(o7Q&kr#D6<-Uv(.6!-rQj:=sAaCB$=a6N?d}e(W|XcssLpE8jc#2wg9#iR.ItA-/*RiuwOk!4v*1C(*2n
-]Ub5)"w^7"BK`
dLO]Ra~AQ:YfTFFi)Efr4ul3.[6*xjsdqQ-QiC]D0ARTwd}0K]`ff:,>`I_Jx?yI5%D+SRp3/pcfF68T%8}:<EOsfp&MDk4HY4Wo[!)F&S{Bxmt]SwO)$Ad:$M_m$X[P+hcS:w<tU%i+eF/?O%u/Cv#-jTaD@mI^v[1P--/fw3z41@QT&.GSNXtEfy0W;I=c,=Y>Z"zQCp1H~$2bzu5s7.
pdXq#Q1:]6yHWBIQ[m471]r-1UugHSv)d(';break;}return
json_decode(decompress_string($d),true);}function
get_plural_translation_id($u){$Qi=array('Too many unsuccessful logins, try again in %d minute(s).'=>141,'%d process(es) have been killed.'=>279,'%d query(s) executed OK.'=>197,'Query executed OK, %d row(s) affected.'=>195,'%d row(s) have been imported.'=>286,'Routine has been called, %d row(s) affected.'=>231,'%d row(s)'=>194,'%d byte(s)'=>43,'%d item(s) have been affected.'=>283,);return
isset($Qi[$u])?$Qi[$u]:null;}$Xl=$_SESSION["translations"];$Zf=Locale::get()->getLanguage();if($_SESSION["translations_version"]!=1875694525){$Xl=[];$_SESSION["translations_version"]=1875694525;}if($_SESSION["translations_language"]!=$Zf){$Xl=[];$_SESSION["translations_language"]=$Zf;}if(!$Xl){$Xl=get_translations($Zf);$_SESSION["translations"]=$Xl;}Locale::get()->setTranslations($Xl);$ya=null;$kc=false;$tf=null;if(function_exists('\adminneo_instance')){$ya=\adminneo_instance();$kc=true;}elseif(file_exists("adminneo-instance.php")){$ya=include_once"adminneo-instance.php";$kc=true;}if($kc&&!$ya
instanceof
Admin&&!$ya
instanceof
Pluginer){$ya=null;$pg="href=https://github.com/adminneo-org/adminneo#advanced-customizations ".target_blank();$tf=lang(130,"<b>adminneo-instance.php</b>","<b>adminneo_instance()</b>","Admin::create()")." <a $pg>".lang(1)."</a>";}if(!$ya)$ya=Admin::create();if($tf)$ya->addError($tf);if($Wi!==null&&!isset($_GET["settings"])){$ya->getSettings()->updateParameter("lang",$Wi);redirect(remove_from_uri());}if(!defined("AdminNeo\DRIVER")){define("AdminNeo\DRIVER",null);define("AdminNeo\DIALECT",null);}define("AdminNeo\SERVER",DRIVER?$_GET[DRIVER]:null);define("AdminNeo\DB",isset($_GET["db"])?$_GET["db"]:"");define("AdminNeo\BASE_URL",preg_replace('~\?.*~','',relative_uri()));define("AdminNeo\ME",BASE_URL.'?'.(sid()?session_name()."=".urlencode(session_id()).'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));define("AdminNeo\HOME_URL",BASE_URL?:".");define("AdminNeo\SERVER_HOME_URL",substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1)?:".");if(isset($_GET["set"])){header("Content-Type: text/javascript; charset=utf-8");if(!verify_token()){header("HTTP/1.1 403 Forbidden");exit;}if($_GET["set"]=="navigation-width"){$Wm=isset($_POST["width"])?$_POST["width"]:"";if($Wm!=""){$Wm=min(max((float)$Wm,Settings::$NavigationWidthMin),Settings::$NavigationWidthMax);Admin::get()->getSettings()->updateParameter("navigationWidth",sprintf("%.2F",$Wm));}else
Admin::get()->getSettings()->updateParameter("navigationWidth",null);}if($_GET["set"]=="export-settings")Admin::get()->getSettings()->updateParameters(["exportFormat"=>isset($_POST["format"])?$_POST["format"]:"","exportOutput"=>isset($_POST["output"])?$_POST["output"]:"",]);exit;}const
VERSION="5.8.0";function
page_header($T,$db=[]){if(!headers_sent()&&!array_sum(array_column(ob_get_status(true),"buffer_used")))ini_set("zlib.output_compression","1");page_headers();if(is_ajax()&&Admin::get()->getErrors()){page_messages();exit;}if(!ob_get_level())ob_start(null,4096);$T=strip_tags($T);$sk=$db!==false&&$db!==null&&SERVER!=""?" - ".h(Admin::get()->getServerName(SERVER)):"";$uk=strip_tags(Admin::get()->getServiceTitle());$Nl=$T.$sk." - ".($uk!=""?$uk:"AdminNeo");echo'<!DOCTYPE html>
<html lang=\'',Locale::get()->getLanguage(),'\' dir=\'',lang(131),'\' class=\'',lang(131),' nojs\'>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1"/>

	<title>',$Nl,'</title>

	';$Eb=validate_color_variant(Admin::get()->getConfig()->getColorVariant());echo"<link rel='stylesheet' href='",link_files("default-$Eb.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("default-$Eb-dark.css",[]),"'>\n";$Gl=Admin::get()->getSettings()->getTheme();list($Gl,$Eb)=validate_theme($Gl,$Eb);if($Gl!="default"){echo"<link rel='stylesheet' href='",link_files("$Gl-$Eb.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("$Gl-$Eb-dark.css",[]),"'>\n";}foreach(Admin::get()->getCssUrls()as$rm){if(strpos($rm,"adminneo-dark.css")===0&&!Admin::get()->isDarkModeForced())echo"<link rel='stylesheet' media='(prefers-color-scheme: dark)' href='",h($rm),"'>\n";else
echo"<link rel='stylesheet' href='",h($rm),"'>\n";}$oh=Admin::get()->getSettings()->getNavigationWidth();echo"<style id='navigation-width'>";if($oh)echo"@media screen and (min-width: 1024px) { :root { --menu-width: ",sprintf("%.2F",$oh),"rem } }";echo"</style>\n",script_src(link_files("main.js",[]));foreach(Admin::get()->getJsUrls()as$rm)echo
script_src($rm);Admin::get()->printFavicons();Admin::get()->printToHead();echo'</head>
<body>
<script',nonce(),'>
	// The event handlers and the <html> classes are registered by functions.js.
	const offlineMessage = \'',js_escape(lang(132)),'\';
	const thousandsSeparator = \'',js_escape(lang(107)),'\';
</script>


',"<div id='help' class='jush-".DIALECT." jsonly hidden'></div>",script("initHelpPopup();"),'<menu class="access-menu">','<li><a href="#main-content">'.lang(133).'</a></li>','<li><a class="panel-link" href="#navigation-panel">'.lang(134).'</a></li>';if($db!==null&&DB!=""&&$_GET["ns"]!=="")echo'<li><a class="panel-link" href="#tables">'.lang(135).'</a></li>';echo'</menu>',"<div id='content'>\n","<div class='header'>\n","<button id='open-navigation-button' type='button' class='button light navigation-button' title='",lang(136),"' aria-controls='navigation-panel' aria-expanded='false'>",icon_solo("menu"),"</button>";if($db!==null){echo'<nav class="breadcrumbs"><ul>',"<li><a href='".h(HOME_URL)."' title='",lang(137),"'>",icon_solo("home"),"</a></li>";$qk=h(Admin::get()->getServerName(SERVER??""));if($db===false)echo"<li>$qk</li>";else{$x=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);echo"<li><a href='".h($x)."' accesskey='1' title='Alt+Shift+1'>$qk</a></li>";if($_GET["ns"]!=""||(DB!=""&&is_array($db)))echo'<li><a href="'.h($x."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a></li>';if($db===true){if($_GET["ns"]!="")echo'<li>'.h($_GET["ns"]).'</li>';else
echo"<li>",h(DB),"</li>";}else{if($_GET["ns"]!="")echo'<li><a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a></li>';foreach($db
as$u=>$X){if(is_string($u)){$Bc=(is_array($X)?$X[1]:h($X));if($Bc!="")echo"<li><a href='".h(ME."$u=").urlencode(is_array($X)?$X[0]:$X)."'>$Bc</a></li>";}else
echo"<li>$X</li>\n";}}}echo"</ul></nav>";}echo"</div>\n","<div id='main-content'>\n","<h1>$T</h1>\n","<div id='ajaxstatus' role='status' class='jsonly'></div>\n";restart_session();page_messages();$g=&get_session("dbs");if(DB!=""&&$g&&!in_array(DB,$g,true))$g=null;stop_session();define("AdminNeo\PAGE_HEADER",1);ob_flush();flush();}function
validate_color_variant($Eb){list(,$Eb)=validate_theme("default",$Eb);return$Eb;}function
validate_theme($Gl,$Eb){$Hl=get_available_themes();if(isset($Hl[$Gl][$Eb]))return[$Gl,$Eb];if(isset($Hl["default"][$Eb]))return["default",$Eb];reset($Hl["default"]);return["default",key($Hl["default"])];}function
get_available_themes(){return
array('default'=>array('blue'=>true,'green'=>true,'orange'=>true,'purple'=>true,'red'=>true,),);}function
get_theme_titles($Eb){$Hl=get_available_themes();$Ol=[];foreach($Hl
as$Gl=>$Fb){if(isset($Fb[$Eb])?$Fb[$Eb]:false)$Ol[$Gl]=($Gl=="default"?"Neo":ucwords(str_replace("-"," ",$Gl)));}return$Ol;}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");header("X-Frame-Options: DENY");$hc=["script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://api.github.com/repos/adminneo-org/adminneo/releases/latest","frame-src"=>"'self'","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",];Admin::get()->updateCspHeader($hc);$Gc=[];foreach($hc
as$Fc=>$Ik)$Gc[]="$Fc $Ik";header("Content-Security-Policy: ".implode("; ",$Gc));Admin::get()->sendHeaders();}function
get_nonce(){static$xh;if(!$xh)$xh=Random::strongKey();return$xh;}function
page_messages(){$qm=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$Tg=isset($_SESSION["messages"][$qm])?$_SESSION["messages"][$qm]:null;if($Tg){foreach($Tg
as$Pg)echo"<div class='message'>$Pg</div>\n",script("initToggles(qsl('.message'));");unset($_SESSION["messages"][$qm]);}foreach(Admin::get()->getErrors()as$j)echo"<div class='error'>$j</div>\n";}function
page_footer($Zg=null){echo"</div>\n","</div>\n","<div id='navigation-panel' class='navigation-panel'>\n","<div class='focus-trap-begin'></div>\n";$cg=isset($_COOKIE["neo_version"])?$_COOKIE["neo_version"]:null;echo"<div class='header'>\n","<button id='close-navigation-button' type='button' class='button light navigation-button' title='",lang(138),"' aria-controls='navigation-panel'>",icon_solo("close"),"</button>",Admin::get()->getServiceTitle()."\n";if($Zg!="auth"){echo"<span class='version'>",h(preg_replace('~\\.0(-|$)~','$1',VERSION));if(Admin::get()->getConfig()->isVersionVerificationEnabled()&&$cg&&version_compare(VERSION,$cg)<0)echo"<a id='version' class='version-badge' href='https://www.adminneo.org/download' ".target_blank()." title='".h($cg)."'>",icon_solo("asterisk"),"</a>";echo"</span>\n";if(Admin::get()->getConfig()->isVersionVerificationEnabled()&&!$cg)echo
script("verifyVersion();");}echo"</div>\n";Admin::get()->printNavigation($Zg);echo"<div class='footer'>\n","<div class='toolbox'>";if($Zg=="auth")language_select();else{$x=h(preg_replace('~\b(db|ns)=[^&]*&~',"",ME)."settings=");echo"<a class='button light' title='",lang(139),"' href='$x'>",icon_solo("settings"),"</a>";}echo"</div>";if($Zg!="auth")Admin::get()->printLogout();echo"</div>\n","<div id='navigation-resizer' class='navigation-resizer'></div>\n","<div class='focus-trap-end'></div>\n","</div>\n",script("initNavigation(); initNavigationResizer('".js_escape(ME)."set=navigation-width', '".get_token()."', ".Settings::$NavigationWidthMin.", ".Settings::$NavigationWidthMax.");");}function
int32($jh){while($jh>=2147483648)$jh-=4294967296;while($jh<=-2147483649)$jh+=4294967296;return(int)$jh;}function
long2str(array$W,$Om){$Qj='';foreach($W
as$X)$Qj
.=pack('V',$X);return$Om?substr($Qj,0,end($W)):$Qj;}function
str2long($Qj,$Om){$W=array_values(unpack('V*',str_pad($Qj,4*ceil(strlen($Qj)/4),"\0")));if($Om)$W[]=strlen($Qj);return$W;}function
xxtea_mx($an,$Zm,$al,$Kf){return
int32((($an>>5&0x7FFFFFF)^$Zm<<2)+(($Zm>>3&0x1FFFFFFF)^$an<<4))^int32(($al^$Zm)+($Kf^$an));}function
xxtea_encrypt_string($Mi,$u){$u=array_values(unpack("V*",pack("H*",md5($u))));$W=str2long($Mi,true);$jh=count($W)-1;$an=$W[$jh];$Zm=$W[0];$jj=floor(6+52/($jh+1));$al=0;while($jj-->0){$al=int32($al+0x9E3779B9);$bd=$al>>2&3;for($qi=0;$qi<$jh;$qi++){$Zm=$W[$qi+1];$hh=xxtea_mx($an,$Zm,$al,$u[$qi&3^$bd]);$an=int32($W[$qi]+$hh);$W[$qi]=$an;}$Zm=$W[0];$hh=xxtea_mx($an,$Zm,$al,$u[$qi&3^$bd]);$an=int32($W[$jh]+$hh);$W[$jh]=$an;}return
long2str($W,false);}function
xxtea_decrypt_string($f,$u){$u=array_values(unpack("V*",pack("H*",md5($u))));$W=str2long($f,false);$jh=count($W)-1;$an=$W[$jh];$Zm=$W[0];$jj=floor(6+52/($jh+1));$al=int32($jj*0x9E3779B9);while($al){$bd=$al>>2&3;for($qi=$jh;$qi>0;$qi--){$an=$W[$qi-1];$hh=xxtea_mx($an,$Zm,$al,$u[$qi&3^$bd]);$Zm=int32($W[$qi]-$hh);$W[$qi]=$Zm;}$an=$W[$jh];$hh=xxtea_mx($an,$Zm,$al,$u[$qi&3^$bd]);$Zm=int32($W[0]-$hh);$W[0]=$Zm;$al=int32($al-0x9E3779B9);}return
long2str($W,true);}const
ENCRYPTION_GCM='aes-256-gcm';const
ENCRYPTION_CBC='aes-256-cbc';const
ENCRYPTION_TAG_LENGTH=16;const
ENCRYPTION_HMAC_LENGTH=64;function
generate_iv($v){if(function_exists('random_bytes')){try{return
random_bytes($v);}catch(Exception$bd){}}return
openssl_random_pseudo_bytes($v);}function
hash_key($u){return
substr(hash('sha512',$u,true),0,32);}function
aes_encrypt_string($Mi,$u){$Xg=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$u=hash_key($u);$Ff=generate_iv(openssl_cipher_iv_length($Xg)?:16);if($Xg==ENCRYPTION_GCM)$xb=openssl_encrypt($Mi,$Xg,$u,OPENSSL_RAW_DATA,$Ff,$yl,"",ENCRYPTION_TAG_LENGTH);else{$xb=openssl_encrypt($Mi,$Xg,$u,OPENSSL_RAW_DATA,$Ff);$yl=hash_hmac("sha512",$Ff.$xb,$u,true);}if($xb===false)return
false;return$Ff.$yl.$xb;}function
aes_decrypt_string($f,$u){$Xg=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$Gf=openssl_cipher_iv_length($Xg)?:16;$zl=$Xg==ENCRYPTION_GCM?ENCRYPTION_TAG_LENGTH:ENCRYPTION_HMAC_LENGTH;if(strlen($f)<$Gf+$zl)return
false;$u=hash_key($u);$Ff=substr($f,0,$Gf);$yl=substr($f,$Gf,$zl);$xb=substr($f,$Gf+$zl);if($Ff===false||$yl===false||$xb===false)return
false;if($Xg==ENCRYPTION_GCM)return
openssl_decrypt($xb,$Xg,$u,OPENSSL_RAW_DATA,$Ff,$yl);else{$Re=hash_hmac('sha512',$Ff.$xb,$u,true);if(!hash_equals($yl,$Re))return
false;return
openssl_decrypt($xb,$Xg,$u,OPENSSL_RAW_DATA,$Ff);}}function
encrypt_string($Mi,$u){if($Mi=="")return"";if(extension_loaded('openssl'))return
aes_encrypt_string($Mi,$u);else
return
xxtea_encrypt_string($Mi,$u);}function
decrypt_string($f,$u){if($f=="")return"";if(extension_loaded('openssl'))return
aes_decrypt_string($f,$u);else
return
xxtea_decrypt_string($f,$u);}$Ji=[];if($_COOKIE["neo_permanent"]){foreach(explode(" ",$_COOKIE["neo_permanent"])as$X){list($u)=explode(":",$X);$Ji[$u]=$X;}}function
validate_server_input(array&$Ji){$N=preg_replace('~:/[-\w.][-\w.:/]*$~D',"",SERVER);if($N=="")return;if(!preg_match('~^[^:]+://~',$N))$N="https://$N";$Di=parse_url($N);if(!$Di)auth_error($Ji);if(isset($Di['user'])||isset($Di['pass'])||isset($Di['query'])||isset($Di['fragment']))auth_error($Ji);if(isset($Di['scheme'])&&!preg_match('~^(https?)$~i',$Di['scheme']))auth_error($Ji);$Ue=$Di['host'].(isset($Di['path'])?$Di['path']:'');if(!is_server_host_valid($Ue))auth_error($Ji);if(isset($Di['port'])&&($Di['port']<1024||$Di['port']>65535))auth_error($Ji,lang(140));}if(!function_exists('AdminNeo\is_server_host_valid')){function
is_server_host_valid($Ue){return
strpos($Ue,'/')===false;}}function
build_http_url($N,$V,$F,$wc,$vc=null){if(!preg_match('~^(https?://)?([^:]*)(:\d+)?$~',rtrim($N,'/'),$_))return
null;return($_[1]?:"http://").($V!==""||$F!==""?urlencode($V).":".urlencode($F)."@":"").($_[2]!==""?$_[2]:$wc).(isset($_[3])?$_[3]:($vc?":$vc":""));}function
add_invalid_login(){$Xa=get_temp_dir()."/adminneo-invalid";$m=null;foreach(glob("$Xa*")?:[$Xa]as$n){$m=open_file_with_lock($n);if($m)break;}if(!$m){$m=open_file_with_lock("$Xa-".Random::strongKey());if(!$m)return;}$wf=json_decode(stream_get_contents($m),true);$Jl=time();if($wf){foreach($wf
as$xf=>$X){if($X[0]<$Jl)unset($wf[$xf]);}}$vf=&$wf[Admin::get()->getBruteForceKey()];if(!$vf)$vf=[$Jl+30*60,0];$vf[1]++;write_and_unlock_file($m,json_encode($wf));}function
check_invalid_login(array&$Ji){$Xa=get_temp_dir()."/adminneo-invalid";$wf=[];foreach(glob("$Xa*")as$n){$m=open_file_with_lock($n);if($m){$wf=json_decode(stream_get_contents($m),true);unlock_file($m);break;}}$vf=($wf?$wf[Admin::get()->getBruteForceKey()]:[]);$vh=($vf&&$vf[1]>29?$vf[0]-time():0);if($vh>0)auth_error($Ji,lang(141,ceil($vh/60)));}function
connect_to_db(array&$Ji){if(Admin::get()->getConfig()->hasServers()&&!Admin::get()->getConfig()->getServer(SERVER))auth_error($Ji);$e=connect(true,$j);if(!$e)connection_error(nl2br(h($j)),$Ji);return$e;}function
authenticate(array&$Ji){$I=Admin::get()->authenticate($_GET["username"],get_password());if($I!==true)connection_error($I,$Ji);}function
connection_error($j,array&$Ji){$j=$j?:lang(3);if(preg_match('~^ +| +$~',get_password()))$j
.="<br>".lang(142);auth_error($Ji,$j);}Admin::get()->init();$Na=isset($_POST["auth"])?$_POST["auth"]:null;if($Na){session_regenerate_id();$N=isset($Na["server"])?$Na["server"]:"";$rk=Admin::get()->getConfig()->getServer($N);$Sc=$rk?$rk->getDriver():(isset($Na["driver"])?$Na["driver"]:"");$N=$rk?$N:trim($N);$V=isset($Na["username"])?$Na["username"]:"";$F=isset($Na["password"])?$Na["password"]:"";if($rk&&$rk->hasCredentials()&&$V==""&&$F==""){$V=$rk->getUsername();$F=$rk->getPassword();}$h=$rk?$rk->getDatabase():(isset($Na["db"])?$Na["db"]:"");save_login($Sc,$N,$V,$F,$h);if($Na["permanent"]){$u=implode("-",array_map("base64_encode",[$Sc,$N,$V,$h]));$cj=Admin::get()->getPrivateKey(true);$md=$cj?encrypt_string($F,$cj):false;$Ji[$u]="$u:".base64_encode($md?:"");cookie("neo_permanent",implode(" ",$Ji));}if(count($_POST)==1||DRIVER!=$Sc||SERVER!=$N||$_GET["username"]!==$V||DB!=$h)redirect(auth_url($Sc,$N,$V,$h));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(["pwds","db","dbs","queries"]as$u)set_session($u,null);unset_permanent($Ji);redirect(SERVER_HOME_URL,lang(143));}elseif($Ji&&!$_SESSION["pwds"]){session_regenerate_id();$cj=Admin::get()->getPrivateKey();foreach($Ji
as$u=>$X){list(,$wb)=explode(":",$X);list($Sc,$N,$V,$h)=array_map("base64_decode",explode("-",$u));$F=$cj?decrypt_string(base64_decode($wb),$cj):false;save_login($Sc,$N,$V,$F,$h);}}function
unset_permanent(array&$Ji){foreach($Ji
as$u=>$X){list($Sc,$N,$V,$h)=array_map("base64_decode",explode("-",$u));if($Sc==DRIVER&&$N==SERVER&&$V==$_GET["username"]&&$h==DB)unset($Ji[$u]);}cookie("neo_permanent",implode(" ",$Ji));}function
auth_error(array&$Ji,$j=null){$vk=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$vk]||$_GET[$vk])&&!$_SESSION["token"])$j=lang(144);else{restart_session();add_invalid_login();$F=get_password();if($F!==null){if($F===false)$j=lang(145);delete_login(DRIVER,SERVER,$_GET["username"]);}unset_permanent($Ji);}}if(!$_COOKIE[$vk]&&$_GET[$vk]&&ini_bool("session.use_only_cookies"))$j=lang(146);if(!$j)$j=lang(3);Admin::get()->addError($j);print_login_page();}function
print_login_page(){$ti=session_get_cookie_params();cookie("neo_key",($_COOKIE["neo_key"]?:Random::strongKey()),$ti["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header(lang(32),null);echo"<form action='' method='post'>\n","<div>";if(print_hidden_fields($_POST,["auth"]))echo"<p class='message'>".lang(147)."\n";echo"</div>\n";Admin::get()->printLoginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!DRIVER)print_login_page();if(isset($_GET["username"])&&!defined('AdminNeo\DRIVER_EXTENSION')){Admin::get()->addError(lang(148,implode(", ",Drivers::getExtensions(DRIVER))));unset($_SESSION["pwds"][DRIVER]);unset_permanent($Ji);page_header(lang(149),false);page_footer("auth");exit;}if(!isset($_GET["username"])||get_password()===null)print_login_page();validate_server_input($Ji);check_invalid_login($Ji);Admin::get()->getConfig()->applyServer(SERVER);$e=connect_to_db($Ji);authenticate($Ji);create_driver($e);if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){Admin::get()->addError(lang(150));page_header(lang(7));page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($Na&&$_POST["token"])$_POST["token"]=get_token();if($_POST){if(!verify_token()){Admin::get()->addError(lang(150).' '.lang(151));$_POST=[];}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$j=lang(152,"'post_max_size'");if(isset($_GET["sql"]))$j
.=' '.lang(153);Admin::get()->addError($j);}if(isset($_GET["settings"])){$O=Admin::get()->getSettings();$yk=array_merge(Admin::get()->getSettingsRows(1),Admin::get()->getSettingsRows(2),Admin::get()->getSettingsRows(3));if($_POST){$ti=[];foreach($yk
as$u=>$K){if(isset($_POST[$u])){$tm=$_POST[$u]===""||(is_array($_POST[$u])&&in_array("",$_POST[$u]));$ti[$u]=(!$tm?$_POST[$u]:null);}}$O->updateParameters($ti);redirect(remove_from_uri());}$T=lang(139);page_header($T,[$T]);echo"<form id='settings' action='' method='post'>\n","<table class='box'>\n";foreach($yk
as$K)echo$K;echo"</table>\n","<p>","<input type='submit' value='".lang(115),"' class='button default hidden'>",input_token(),"</p>\n","</form>\n",script("initSettingsForm();");page_footer();exit;}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(DB==""&&isset($_GET["ns"]))redirect(remove_from_uri('ns'));if(!(DB!=""?Connection::get()->selectDatabase(DB):(isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill"))){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){Admin::get()->addError(lang(154));header("HTTP/1.1 404 Not Found");page_header(lang(31).": ".h(DB),true);}else{if($_POST["db"])queries_redirect(substr(ME,0,-1),lang(155),drop_databases($_POST["db"]));$T=h(Drivers::get(DRIVER).": ".Admin::get()->getServerName(SERVER));page_header($T,false);$qg=['privileges'=>[lang(73),"users"],'processlist'=>[lang(156),"list"],'variables'=>[lang(157),"variable"],'status'=>[lang(158),"status"],];$rg="";foreach($qg
as$u=>$X){if(support($u))$rg
.="<a href='".h(ME)."$u='>".icon($X[1])."$X[0]</a>";}if($rg)echo"<p class='links top-links'>$rg</p>\n";echo"<p>".lang(159,Drivers::get(DRIVER),"<b>".h(Connection::get()->getVersion())."</b>","<b>".DRIVER_EXTENSION."</b>")."\n","<p>".lang(160,"<b>".h(logged_user())."</b>")."\n";$g=Admin::get()->getDatabases();if($g){$ak=support("scheme");$Da=collations();echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr>".(support("database")?"<th>":"")."<th aria-sort='ascending'>".lang(31).(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".lang(161)."</a>":"")."<td>".lang(46)."<td>".lang(162)."<td>".lang(163)." - <a href='".h(ME)."dbsize=1'>".lang(164)."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n","<tbody>\n";$g=($_GET["dbsize"]?count_tables($g):array_flip($g));foreach($g
as$h=>$S){$Lj=h(ME)."db=".urlencode($h);$r=h("Db-".$h);echo"<tr>".(support("database")?"<th class='actions'>".checkbox("db[]",$h,in_array($h,(array)$_POST["db"]),"","","",$r):""),"<th><a href='$Lj' id='$r'>".h($h)."</a>";$Bb=h(db_collation($h,$Da));echo"<td>".(support("database")?"<a href='$Lj".($ak?"&amp;ns=":"")."&amp;database=' title='".lang(70)."'>$Bb</a>":$Bb),"<td align='right'><a href='$Lj&amp;schema=' id='tables-".h($h)."' title='".lang(72)."'>".($_GET["dbsize"]?$S:"?")."</a>","<td align='right' id='size-".h($h)."'>".($_GET["dbsize"]?db_size($h):"?"),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: event => tableClick(event, true)});"),"</table>\n","</div>\n";if(support("database"))echo"<div class='table-footer'><div class='field-sets'>\n","<fieldset><legend>",lang(165)," <span id='selected'></span></legend><div class='fieldset-content'>\n",input_hidden("all"),script("qsl('input').onclick = countDbs;"),"<input type='submit' class='button' name='drop' value='",lang(166),"'>",confirm(),"\n","</div></fieldset>\n","</div></div>\n",script("initTableFooter()");echo"</div>\n",input_token(),"</form>\n",script("tableCheck();");}}echo'<p class="links"><a href="'.h(ME).'database=">'.icon("database-add").lang(76)."</a>\n";page_footer("db");exit;}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$l=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=[idf_escape($_GET["field"])];$I=Driver::get()->select($a,$M,[where($_GET,$l)],$M);$K=($I?$I->fetchRow():[]);echo
Connection::get()->formatValue($K[0],$l[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$l=fields($a);if(!$l)Admin::get()->addError(error()?:lang(79));$R=table_status1($a,true);$A=Admin::get()->getTableName($R);$Kj=[];foreach($l
as$u=>$k)$Kj+=$k["privileges"];$T=$l&&is_view($R)?$R['Engine']=='materialized view'?lang(167):lang(168):lang(9);$ol=$A!=""?$A:h($a);page_header("$T: $ol",[$ol]);$rf=null;if(isset($Kj["insert"])||!support("table"))$rf=[];Admin::get()->printTableMenu($R,$rf);$jf=[];if(!preg_match("~sqlite|mssql|pgsql~",DIALECT)&&isset($R["Engine"]))$jf[]=lang(169).": ".h($R["Engine"]);if(isset($R["Collation"]))$jf[]=lang(46).": ".h($R["Collation"]);if($jf)echo"<p>",implode(", ",$jf),"</p>";if($l)Admin::get()->printTableStructure($l);$Lb=$R["Comment"];if($Lb!="")echo"<p class='keep-lines'>",lang(47),": ",Admin::get()->formatComment($Lb),"</p>\n";if(!is_view($R))$dd='<p class="links"><a href="'.h(ME).'create='.urlencode($a).'">'.icon("edit").lang(36)."</a>\n";elseif(support("view"))$dd='<p class="links"><a href="'.h(ME).'view='.urlencode($a).'">'.icon("edit").lang(37)."</a>\n";else$dd="";if($jf||$l||$Lb!="")echo$dd;$ui=Driver::get()->getParentTables($a);if($ui){echo"<h2>".lang(170)."</h2>\n";Admin::get()->printRelatedTables($ui);}if(Driver::get()->getPartitionBy()&&str_contains(isset($R["Create_options"])?$R["Create_options"]:"","partitioned")){$Ci=Driver::get()->getPartitionsInfo($a);if($Ci){echo"<h2 id='partitions'>".lang(50)."</h2>\n";Admin::get()->printTablePartitions($Ci);if(DIALECT!="pgsql")echo$dd;}}$kf=Driver::get()->getInheritedTables($a);if($kf){echo"<h2 id='inherited-by'>".lang(171)."</h2>\n";Admin::get()->printRelatedTables($kf);}if(support("indexes")&&Driver::get()->supportsIndex($R)){echo"<h2 id='indexes'>".lang(172)."</h2>\n";$t=indexes($a);if($t)Admin::get()->printTableIndexes($t,$R);echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.icon("edit").lang(173)."</a>\n";}if(!is_view($R)){if(fk_support($R)){echo"<h2 id='foreign-keys'>".lang(91)."</h2>\n";$ge=foreign_keys($a);if($ge){echo"<table>\n","<thead><tr><th>".lang(174)."<td>".lang(175)."<td>".lang(94)."<td>".lang(93)."<td></thead>\n";foreach($ge
as$A=>$o)echo"<tr title='".h($A)."'>","<th><i>".implode("</i>, <i>",array_map('AdminNeo\h',$o["source"]))."</i>","<td><a href='".h($o["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($o["db"]),ME):($o["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($o["ns"]),ME):ME))."table=".urlencode($o["table"])."'>".($o["db"]!=""&&$o["db"]!=DB?"<b>".h($o["db"])."</b>.":"").($o["ns"]!=""&&$o["ns"]!=$_GET["ns"]?"<b>".h($o["ns"])."</b>.":"").h($o["table"])."</a>","(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$o["target"]))."</i>)","<td>".h($o["on_delete"]),"<td>".h($o["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($A)).'">'.lang(176).'</a>',"\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.icon("add").lang(177)."</a>\n";}if(support("check")){echo"<h2 id='checks'>".lang(178)."</h2>\n";$rb=Driver::get()->checkConstraints($a);if($rb){echo"<table cellspacing='0'>\n";foreach($rb
as$u=>$X)echo"<tr title='".h($u)."'>","<td><code class='jush-".DIALECT."'>".truncate_utf8(preg_replace('~\s+~',' ',ltrim($X))),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($u))."'>".lang(176)."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.icon("add").lang(179)."</a>\n";}}if(support(is_view($R)?"view_trigger":"trigger")){echo"<h2 id='triggers'>".lang(180)."</h2>\n";$am=triggers($a);if($am){echo"<table>\n";foreach($am
as$u=>$X)echo"<tr><td>".h($X[0])."<td>".h($X[1])."<th>".h($u)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($u))."'>".lang(176)."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.icon("add").lang(181)."</a>\n";}}elseif(isset($_GET["schema"])){$Ml=h(": ".DB.($_GET["ns"]?".$_GET[ns]":""));page_header(lang(72).$Ml,[lang(72)]);$ql=[];$rl=[];$Pd=[];$pa=($_GET["schema"]?:$_COOKIE["neo_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$pa,$_,PREG_SET_ORDER);foreach($_
as$z){$ql[$z[1]]=[(float)$z[2],(float)$z[3]];$rl[]="\n'".js_escape($z[1])."': [ $z[2], $z[3] ]";}$ng=1.4;$Sl=0;$Wa=-1;$Yj=[];$yj=[];$fg=[];$Ea=Driver::get()->getAllFields();foreach(table_status('',true)as$Q=>$R){if(is_view($R))continue;$G=0;$Yj[$Q]["fields"]=[];foreach(isset($Ea[$Q])?$Ea[$Q]:[]as$k){$G=round($G+$ng,2);$Pd[$Q][$k["field"]]=$G;$Yj[$Q]["fields"][$k["field"]]=$k;}$Yj[$Q]["pos"]=(isset($ql[$Q])?$ql[$Q]:[$Sl,0]);foreach(Admin::get()->getForeignKeys($Q)as$X){if(!$X["db"]){$dg=$Wa;if((isset($ql[$Q][1])?$ql[$Q][1]:0)||(isset($ql[$X["table"]][1])?$ql[$X["table"]][1]:0))$dg=min(floatval(isset($ql[$Q][1])?$ql[$Q][1]:0),floatval(isset($ql[$X["table"]][1])?$ql[$X["table"]][1]:0))-1;else$Wa-=.1;while($fg[(string)$dg])$dg-=.0001;$Yj[$Q]["references"][$X["table"]][(string)$dg]=[$X["source"],$X["target"]];$yj[$X["table"]][$Q][(string)$dg]=$X["target"];$fg[(string)$dg]=true;}}$Sl=max($Sl,round($Yj[$Q]["pos"][0]+$ng+$G+1,2));}echo"<div id='schema' style='height: {$Sl}em;'>\n";foreach($Yj
as$A=>$Q){$Le=round($ng+count($Q["fields"])*$ng+0.4,2);echo"<div class='table' style='top: ".$Q["pos"][0]."em; left: ".$Q["pos"][1]."em; height: {$Le}em;'>","<div class='content'>",'<h4><a href="'.h(ME).'table='.urlencode($A).'">'.h($A)."</a></h4>","<ul>";foreach($Q["fields"]as$k){$X='<span '.type_class($k["type"]).' title="'.h($k["type"].($k["length"]?"($k[length])":"").($k["null"]?" NULL":'')).'">'.h($k["field"]).'</span>';echo"<li>".($k["primary"]?"<i>$X</i>":$X)."</li>";}echo"</ul>","</div>";foreach((array)$Q["references"]as$Al=>$_j){foreach($_j
as$dg=>$uj){$eg=$dg-(isset($ql[$A][1])?$ql[$A][1]:0);$q=0;foreach($uj[0]as$Hk){echo"\n<div class='references outgoing' title='",h($Al),"' id='refs$dg-$q' style='left: {$eg}em; top: ",$Pd[$A][$Hk],"em;'>","<div style='width: ".(-$eg)."em;'></div>","</div>";$q++;}}}foreach((array)$yj[$A]as$Al=>$_j){foreach($_j
as$dg=>$c){$eg=$dg-(isset($ql[$A][1])?$ql[$A][1]:0);$q=0;foreach($c
as$_l){echo"\n<div class='references incoming' title='",h($Al),"' id='refd$dg-$q' style='left: {$eg}em; top: ".$Pd[$A][$_l]."em;'>","<svg viewBox='0 0 22 22' fill='currentColor'><path d='M11,19l10,-8l-10,-8l0,16Z'/></svg>","<div style='width: ".(-$eg)."em;'></div>","</div>";$q++;}}}echo"\n</div>\n";}foreach($Yj
as$A=>$Q){foreach((array)$Q["references"]as$Al=>$_j){if($Yj[$Al]){foreach($_j
as$dg=>$uj){$Yg=$Sl;$Hg=-10;foreach($uj[0]as$u=>$Hk){$Si=$Q["pos"][0]+$Pd[$A][$Hk];$Ti=$Yj[$Al]["pos"][0]+$Pd[$Al][$uj[1][$u]];$Yg=round(min($Yg,$Si,$Ti),2);$Hg=round(max($Hg,$Si,$Ti),2);}echo"<div class='references vertical' id='refl$dg' style='left: $dg"."em; top: $Yg"."em;'>"."<div style='height: ".round($Hg-$Yg,2)."em;'></div></div>\n";}}}}echo"</div>\n",script("initSchema('".js_escape(DB)."', $Sl, {".implode(",",$rl)."})"),"<p class='links'>","<a href='",(ME."schema=".urlencode($pa)),"' id='schema-link'>",lang(182),"</a>","</p>\n";}elseif(isset($_GET["dump"])){$a=$_GET["dump"];$O=Admin::get()->getSettings();if($_POST){$O->updateParameters(["dumpFormat"=>$_POST["format"],"dumpDbStyle"=>$_POST["db_style"],"dumpTypes"=>isset($_POST["types"])?$_POST["types"]:(support("type")?"":null),"dumpRoutines"=>isset($_POST["routines"])?$_POST["routines"]:(support("routine")?"":null),"dumpEvents"=>isset($_POST["events"])?$_POST["events"]:(support("event")?"":null),"dumpTableStyle"=>$_POST["table_style"],"dumpAutoIncrement"=>isset($_POST["auto_increment"])?$_POST["auto_increment"]:"","dumpTriggers"=>isset($_POST["triggers"])?$_POST["triggers"]:(support("trigger")?"":null),"dumpDataStyle"=>$_POST["data_style"],"dumpOutput"=>$_POST["output"],]);if(DB!="")$g=[DB];else{$g=isset($_POST["databases"])?$_POST["databases"]:[];if(is_string($g))$g=explode("\n",rtrim(str_replace("\r","",$g),"\n"));}$Zj=isset($_POST["schemas"])?$_POST["schemas"]:[];$S=array_flip(isset($_POST["tables"])?$_POST["tables"]:[])+array_flip(isset($_POST["data"])?$_POST["data"]:[]);if(count($S)==1)$Ye=key($S);elseif(count($Zj)==1)$Ye=$Zj[0];elseif(count($g)==1)$Ye=$g[0];else$Ye=Admin::get()->getServerName(SERVER,true,"server");$Dd=dump_headers($Ye,DB==""||$_GET["ns"]===""||count($S)>1);$Cf=preg_match('~sql~',$_POST["format"]);$nc=$Cf&&$_POST["data_style"]&&!$_POST["table_style"]&&DIALECT!="sql";if($Cf){echo"-- AdminNeo ".VERSION." ".Drivers::get(DRIVER)." ".Connection::get()->getVersion()." dump\n\n";if(DIALECT=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";Connection::get()->query("SET time_zone = '+00:00'");Connection::get()->query("SET sql_mode = ''");}}$Wk=$_POST["db_style"];foreach($g
as$h){Admin::get()->dumpDatabase($h);if(Connection::get()->selectDatabase($h)){if($Cf){if($Wk)echo
create_database_sql($h,$Wk),use_sql($h,$Wk)."\n";$ni="";if($_POST["types"]){foreach(types()as$r=>$U){$qd=type_values($r);if($qd)$ni
.=($Wk!='DROP+CREATE'?"DROP TYPE IF EXISTS ".idf_escape($U).";;\n":"")."CREATE TYPE ".idf_escape($U)." AS ENUM ($qd);\n\n";else$ni
.="-- Could not export type $U\n\n";}}if($_POST["routines"]){foreach(routines()as$K){$A=$K["ROUTINE_NAME"];$Mj=$K["ROUTINE_TYPE"];$dc=create_routine($Mj,["name"=>$A]+routine($K["SPECIFIC_NAME"],$Mj));set_utf8mb4($dc);$ni
.=($Wk!='DROP+CREATE'?"DROP $Mj IF EXISTS ".idf_escape($A).";;\n":"")."$dc;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$K){$dc=remove_definer(Connection::get()->getValue("SHOW CREATE EVENT ".idf_escape($K["Name"]),3));set_utf8mb4($dc);$ni
.=($Wk!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($K["Name"]).";;\n":"")."$dc;;\n\n";}}echo($ni&&DIALECT=='sql'?"DELIMITER ;;\n\n$ni"."DELIMITER ;\n\n":$ni);}if($_POST["table_style"]||$_POST["data_style"]){foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?[""]:Admin::get()->getSchemas(true)))as$Yj){if($Yj!="")set_schema($Yj);$wl=table_status('',true);$pl=array_keys($wl);$Hc=false;if($nc&&$pl){$zj=[];foreach($pl
as$A){if(!is_view($wl[$A])&&(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["data"]))){foreach(foreign_keys($A)as$o)$zj[$A][]=$o["table"];}}$ci=dump_table_order($pl,$zj);if($ci)$pl=$ci;else$Hc=function_exists('AdminNeo\foreign_key_checks_sql');}if($Hc)echo
foreign_key_checks_sql(false)."\n";$Jm=[];foreach($pl
as$A){$R=$wl[$A];$Q=(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["tables"]));$f=(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["data"]));if($Q||$f){$Pl=null;if($Dd=="tar"){$Pl=new
TmpFile();ob_start([$Pl,'write'],1e5);}$ec=($Q?$_POST["table_style"]:"");Admin::get()->dumpTable($A,$ec,(is_view($R)?2:0));if(is_view($R)&&$Dd!="tar")$Jm[]=$A;elseif($f){$l=fields($A);Admin::get()->dumpData($A,$_POST["data_style"],"SELECT *".convert_fields($l,$l)." FROM ".table($A));if($Cf&&!$ec&&$_POST["auto_increment"]&&function_exists('AdminNeo\restart_sequences_sql'))echo"\n".restart_sequences_sql($A);}if($Cf&&$_POST["triggers"]&&$Q&&($am=trigger_sql($A)))echo"\nDELIMITER ;;\n$am\nDELIMITER ;\n";if($Dd=="tar"){ob_end_flush();tar_file((DB!=""?"":"$h/")."$A.csv",$Pl);}elseif($Cf)echo"\n";}}if($Hc)echo
foreign_key_checks_sql(true)."\n";if($_POST["table_style"]&&function_exists('AdminNeo\foreign_keys_sql')){foreach($wl
as$A=>$R){$Q=(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["tables"]));if($Q&&!is_view($R))echo
foreign_keys_sql($A);}}foreach($Jm
as$Hm)Admin::get()->dumpTable($Hm,$_POST["table_style"],1);if($Dd=="tar")echo
pack("x512");}}}}if($Cf)echo"-- ".gmdate("Y-m-d H:i:s e")."\n";exit;}$A=DB!=""?h(DB):h(Admin::get()->getServerName(SERVER));page_header(lang(75).": $A",($_GET["export"]!=""?["table"=>$_GET["export"]]:[lang(75)]));echo"<form action='' method='post'>\n","<table class='box'>\n";$rc=['','USE','DROP+CREATE','CREATE'];$tl=['','DROP+CREATE','CREATE'];$oc=['','TRUNCATE+INSERT','INSERT'];if(DIALECT=="sql")$oc[]='INSERT+UPDATE';echo"<tr><th>",lang(183),"</th><td>",html_radios("format",Admin::get()->getDumpFormats(),$O->getParameter("dumpFormat","sql")),"</td></tr>\n";if(DIALECT!="sqlite"){echo"<tr><th id='label-db'>",lang(31),"</th>","<td>",html_select('db_style',$rc,$O->getParameter("dumpDbStyle",DB==""?"CREATE":""),"","label-db"),"<span class='labels'>";if(support("routine"))echo
checkbox("routines",1,$O->getParameter("dumpRoutines",$_GET["dump"]==""?"1":""),lang(184));if(support("event"))echo
checkbox("events",1,$O->getParameter("dumpEvents",$_GET["dump"]==""?"1":""),lang(185));echo"</span></td></tr>";}echo"<tr><th id='label-tables'>",lang(162),"</th><td>",html_select('table_style',$tl,$O->getParameter("dumpTableStyle","DROP+CREATE"),"","label-tables")," <span class='labels'>",checkbox("auto_increment",1,$O->getParameter("dumpAutoIncrement"),lang(48));if(support("trigger"))echo
checkbox("triggers",1,$O->getParameter("dumpTriggers","1"),lang(180));echo"</span></td></tr>","<tr><th id='label-data'>",lang(186),"</th><td>",html_select("data_style",$oc,$O->getParameter("dumpDataStyle","INSERT"),"","label-data"),"</td></tr>","<tr><th>",lang(187),"</th><td>",html_radios("output",Admin::get()->getDumpOutputs(),$O->getParameter("dumpOutput","file")),"</td></tr>\n","</table>\n","<p>","<input type='submit' class='button default' value='",lang(75),"'>",input_token(),"</p>\n","<table>\n",script("qsl('table').onclick = dumpClick;");$Yi=[];if(DB!=""&&$_GET["ns"]===""){echo"<thead><tr><th>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly' title='".lang(188)."'>".lang(189)."</label>".script("gid('check-schemas').onclick = partial(formCheck, /^schemas\\[/);",""),"</thead>\n";foreach(Admin::get()->getSchemas()as$Yj){if(!information_schema(DB,$Yj))echo"<tr><td>".checkbox("schemas[]",$Yj,true,$Yj,"","block")."\n";}}elseif(DB!=""){$tb=($a!=""?"":" checked");echo"<thead><tr>","<th><label class='block'><input type='checkbox' id='check-tables'$tb class='jsonly' title='".lang(188)."'>".lang(9)."</label>".script("gid('check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th class='right'><label class='block'>".lang(186)."<input type='checkbox' id='check-data'$tb class='jsonly' title='".lang(188)."'></label>".script("gid('check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$Jm="";$vl=tables_list();foreach($vl
as$A=>$U){$Xi=preg_replace('~_.*~','',$A);$tb=($a==""||$a==(substr($a,-1)=="%"?"$Xi%":$A));$bj="<tr><td>".checkbox("tables[]",$A,$tb,$A,"","block");if($U!==null&&!preg_match('~table~i',$U))$Jm
.="$bj\n";else
echo"$bj<td class='right'><label class='block'><span id='Rows-".h($A)."'></span>".checkbox("data[]",$A,$tb)."</label>\n";$Yi[$Xi]++;}echo$Jm;if($vl)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$g=Admin::get()->getDatabases();echo"<thead><tr><th>","<label class='block'>".($g?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly' title='".lang(188)."'>".script("gid('check-databases').onclick = partial(formCheck, /^databases\\[/);",""):"").lang(31)."</label>","</thead>\n";if($g){foreach($g
as$h){if(!information_schema($h)){$Xi=preg_replace('~_.*~','',$h);echo"<tr><td>".checkbox("databases[]",$h,$a==""||$a=="$Xi%",$h,"","block")."\n";$Yi[$Xi]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo"</table>\n","</form>\n";$qg=[];foreach($Yi
as$u=>$X){if($u!=""&&$X>1)$qg[]="<a href='".h(ME)."dump=".urlencode("$u%")."'>".icon("check").h($u)."*</a>";}if($qg)echo"<p class='links'>",implode("",$qg),"</p>\n";}elseif(isset($_GET["privileges"])){$Ml=DB!=""?h(": ".DB):"";page_header(lang(73).$Ml,[lang(73)]);echo'<p class="links top-links"><a href="',h(ME),'user=">',icon("user-add"),lang(190),"</a></p>\n";$I=Connection::get()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$we=$I;if(!$I)$I=Connection::get()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''>\n";hidden_fields_get();echo
input_hidden("db",DB);if(!$we)echo
input_hidden("grant");echo"\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr><th>".lang(6)."<th>".lang(5)."<th></thead>\n";while($K=$I->fetchAssoc())echo'<tr><td>'.h($K["User"])."<td>".h($K["Host"]).'<td><a href="'.h(ME.'user='.urlencode($K["User"]).'&host='.urlencode($K["Host"])).'">'.lang(39)."</a>\n";if(!$we||DB!="")echo"<tr><td><input class='input' name='user' autocapitalize='off'>"."<td><input class='input' name='host' value='localhost' autocapitalize='off'>"."<td><input type='submit' class='button' value='".lang(39)."'>\n";echo"</table>\n","</div>\n","</form>\n";}elseif(isset($_GET["sql"])){$O=Admin::get()->getSettings();if($_POST["export"]){$O->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers("sql");Admin::get()->dumpTable("","");Admin::get()->dumpData("","table",$_POST["query"]);exit;}restart_session();$Qe=&get_session("queries");$Pe=&$Qe[DB];if($_POST["clear"]){$Pe=[];redirect(remove_from_uri("history"));}stop_session();$T=isset($_GET["import"])?lang(74):lang(41);page_header($T,[$T]);$mg="--".(DIALECT=="sql"?" ":"");if($_POST){$ne=false;if(!isset($_GET["import"]))$H=$_POST["query"];elseif($_POST["webfile"]){$cf=Admin::get()->getImportFilePath();if($cf){if(file_exists($cf))$ne=fopen($cf,"rb");elseif(file_exists("$cf.gz"))$ne=fopen("compress.zlib://$cf.gz","rb");}$H=$ne?fread($ne,1e6):false;}else$H=get_file("sql_file",true,";");if(is_string($H)){if(($Ng=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($Ng,strval(2*strlen($H)+memory_get_usage()+8e6)));if($H!=""&&strlen($H)<1e6){$jj=$H.(preg_match("~;[ \t\r\n]*\$~",$H)?"":";");if(!$Pe||first(end($Pe))!=$jj){restart_session();$Pe[]=[$jj,time()];set_session("queries",$Qe);stop_session();}}$Jk="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$mg)[^\n]*\n?|--\r?\n)";$_c=";";$Ac=1;$Ch=0;$jd=true;$Ub=connect();if($Ub&&DB!=""){$Ub->selectDatabase(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$Ub);}$Kb=0;$sd=[];$vi='[\'"'.(DIALECT=="sql"?'`#':(DIALECT=="sqlite"?'`[':(DIALECT=="mssql"?'[':''))).']|/\*|'.$mg.'|$'.(DIALECT=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$Tl=microtime(true);$ad=Admin::get()->getDumpFormats();unset($ad["sql"]);while($H!=""){if(!$Ch&&preg_match("~^$Jk*+DELIMITER\\s+(\\S+)~i",$H,$z)){$_c=preg_quote($z[1]);$Ac=strlen($z[1]);$je=Admin::get()->formatSqlCommandQuery(trim($z[0]));if($je!="")echo"<pre><code class='jush-".DIALECT."'>$je</code></pre>\n";$H=substr($H,strlen($z[0]));}elseif(!$Ch&&DIALECT=="pgsql"&&preg_match("~^($Jk*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$H,$z)){$_c="\n\\\\\\.\r?\n";$Ac=3;$Ch=strlen($z[0]);}else{preg_match("($_c\\s*|$vi)",$H,$z,PREG_OFFSET_CAPTURE,$Ch);list($le,$G)=$z[0];if(!$le&&$ne&&!feof($ne))$H
.=fread($ne,1e5);else{if(!$le&&rtrim($H)=="")break;$Ch=$G+strlen($le);if($le&&!preg_match("(^$_c)",$le)){$ib=Driver::get()->hasCStyleEscapes()||(DIALECT=="pgsql"&&($G>0&&strtolower($H[$G-1])=="e"));$Hi='(';if($le=='/*')$Hi
.='\*/';elseif($le=='[')$Hi
.=']';elseif(preg_match("~^$mg|^#~",$le))$Hi
.="\n";else$Hi
.=preg_quote($le).($ib?"|\\\\.":"");$Hi
.='|$)s';while(preg_match($Hi,$H,$z,PREG_OFFSET_CAPTURE,$Ch)){$Qj=$z[0][0];if(!$Qj&&$ne&&!feof($ne))$H
.=fread($ne,1e5);else{$Ch=$z[0][1]+strlen($Qj);if(!isset($Qj[0])||$Qj[0]!="\\")break;}}}else{$jd=false;$jj=substr($H,0,$G+$Ac);$Kb++;$bj="<pre id='sql-$Kb'><code class='jush-".DIALECT."'>".Admin::get()->formatSqlCommandQuery(trim($jj))."</code></pre>\n";if(DIALECT=="sqlite"&&preg_match("~^$Jk*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$jj,$z)!==0){echo$bj,"<p class='error'>".lang(191,preg_match('~ATTACH~i',$z[1])?'ATTACH':'VACUUM INTO')."\n";$sd[]=" <a href='#sql-$Kb'>$Kb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$bj;ob_flush();flush();}$Pk=microtime(true);if(Connection::get()->multiQuery($jj)&&is_object($Ub)&&preg_match("~^$Jk*+USE\\b~i",$jj))$Ub->query($jj);do{$I=Connection::get()->storeResult();if(Connection::get()->getError()){echo($_POST["only_errors"]?$bj:""),"<p class='error'>",lang(192),(!empty(Connection::get()->getErrno())?" (".Connection::get()->getErrno().")":""),": ",error()."</p>\n";$sd[]=" <a href='#sql-$Kb'>$Kb</a>";if($_POST["error_stops"])break
2;}else{$Jl=" <span class='time'>(".format_time($Pk).")</span>";$ed=(strlen($jj)<1000?" <a href='".h(ME)."sql=".urlencode(trim($jj))."'>".icon("edit").lang(39)."</a>":"");$nj=Connection::get()->getQueryInfo();$za=Connection::get()->getAffectedRows();$Pm=($_POST["only_errors"]?null:Driver::get()->warnings());$Rm="warnings-$Kb";$Sm=$Pm?"<a href='#$Rm' class='toggle'>".lang(40).icon_chevron_down()."</a>":null;$_d=$fi=null;$Ad="explain-$Kb";$Bd=false;$Cd="export-$Kb";$w=0;if(is_object($I)){if(!$_POST["only_errors"])echo"<div class='table-result'>\n";$w=(int)$_POST["limit"];$fi=print_select_result($I,$Ub,[],$w);if(!$_POST["only_errors"]){echo"<p class='links'>";$_h=$I->getRowsCount();echo($_h?($w&&$_h>$w?lang(193,$w):"").lang(194,$_h):""),$Jl,$ed,$Sm;if($Ub&&preg_match("~^($Jk|\\()*+SELECT\\b~i",$jj)&&($_d=explain($Ub,$jj)))echo"<a href='#$Ad' class='toggle'>Explain".icon_chevron_down()."</a>";$Bd=true;echo"<a href='#$Cd' class='toggle'>".lang(75).icon_chevron_down()."</a>","</p>\n";}}else{if(preg_match("~^$Jk*+(CREATE|DROP|ALTER)$Jk++(DATABASE|SCHEMA)\\b~i",$jj)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"]){echo"<p class='message' title='".h($nj)."'>",lang(195,$za),"$Jl $ed";if($Sm)echo", $Sm";echo"</p>\n";}}if(!$_POST["only_errors"])echo
script("initToggles(qsl('p'));");if($Pm)echo"<div id='$Rm' class='hidden'>\n$Pm</div>\n";if($_d){echo"<div id='$Ad' class='hidden explain'>\n";print_select_result($_d,$Ub,$fi);echo"</div>\n";}if($Bd){echo"<form id='$Cd' action='' method='post' class='hidden'><p>\n",html_select("format",$ad,$O->getParameter("exportFormat")),html_select("output",Admin::get()->getDumpOutputs(),$O->getParameter("exportOutput"))." ",input_hidden("query",$jj),input_token()," <input type='submit' class='button' name='export' value='".lang(75)."'>";if(!$w)echo
script("qsl('input').onclick = function (event) { return sqlExport.call(this, event, '".js_escape(ME)."set=export-settings'); };","");echo"</p></form>\n";}if(is_object($I)&&!$_POST["only_errors"])echo"</div>\n";}$Pk=microtime(true);}while(Connection::get()->nextResult());}$H=substr($H,$Ch);$Ch=0;}}}}if($jd)echo"<p class='message'>".lang(196)."\n";elseif($_POST["only_errors"]){$Fh=$Kb-count($sd);echo"<p class='".($Fh?"message":"error")."'>".lang(197,$Kb-count($sd))," <span class='time'>(".format_time($Tl).")</span>\n";}elseif($sd&&$Kb>1)echo"<p class='error'>".lang(192).": ".implode("",$sd)."\n";}else
echo"<p class='error'>".upload_error($H)."\n";}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";if(!isset($_GET["import"])){$jj=$_GET["sql"];if($_POST)$jj=$_POST["query"];elseif($_GET["history"]=="all")$jj=$Pe;elseif($_GET["history"]!="")$jj=$Pe[$_GET["history"]][0];echo"<p>";textarea("query",$jj,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."gid('form').onsubmit = partial(sqlSubmit, gid('form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"</p>","<p><input type='submit' class='button default' value='".lang(198)."' title='Ctrl+Enter'>",lang(199).": <input type='number' name='limit' class='input size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{echo"<div class='field-sets'>\n","<fieldset><legend>".lang(200)."</legend><div class='fieldset-content'>";$De=(extension_loaded("zlib")?"[.gz]":"");if(ini_bool("file_uploads"))echo"SQL$De (&lt; ".ini_get("upload_max_filesize")."B): <input type='file' name='sql_file[]' multiple>","<input type='submit' class='button default' value='".lang(198)."'>",file_upload_form_script("form","sql_file[]");else
echo
lang(201);echo"</div></fieldset>\n";$cf=Admin::get()->getImportFilePath();if($cf)echo"<fieldset><legend>".lang(202)."</legend><div class='fieldset-content'>",lang(203,"<code>".h($cf)."$De</code>")," <input type='submit' class='button default' name='webfile' value='".lang(204)."'>","</div></fieldset>\n";echo"</div>\n","<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:(isset($_GET["error_stops"])?$_GET["error_stops"]:true)),lang(205)),checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),lang(206)),input_token(),"</p>\n";if(!isset($_GET["import"]))Admin::get()->printAfterSqlCommand();if(!isset($_GET["import"])&&$Pe){echo"<div class='field-sets'>\n";print_fieldset_start("history",lang(207),"history",$_GET["history"]!="");for($X=end($Pe);$X;$X=prev($Pe)){$u=key($Pe);list($jj,$Jl,$id)=$X;echo" <pre><code class='jush-".DIALECT."'>",truncate_utf8(preg_replace('~\s+~',' ',ltrim(preg_replace("~^(#|$mg).*~m",'',$jj)))),"</code></pre>",'<p class="links">',"<a href='".h(ME."sql=&history=$u")."'>".icon("edit").lang(39)."</a>"," <span class='time' title='".@date('Y-m-d',$Jl)."'>".@date("H:i:s",$Jl).($id?" ($id)":"")."</span>","</p>";}echo"<p><input type='submit' class='button' name='clear' value='".lang(208)."'>\n","<a href='",h(ME."sql=&history=all")."' class='button light'>",icon("edit"),lang(209),"</a></p>\n";print_fieldset_end("history");echo"</div>\n";}echo"</form>\n";}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$l=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$l):""):where($_GET,$l));$pm=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($l
as$A=>$k){if((!$pm&&!isset($k["privileges"]["insert"]))||Admin::get()->getFieldName($k)=="")unset($l[$A]);}if($_POST&&!isset($_GET["select"])){$y=$_POST["referer"];if($_POST["insert"])$y=($pm?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$y))$y=ME."select=".urlencode($a);$t=indexes($a);$jm=unique_array(isset($_GET["where"])?$_GET["where"]:[],$t);$oj="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($y,lang(210),(bool)Driver::get()->delete($a,$oj,$jm?0:1));else{$wk=[];foreach($l
as$A=>$k){$X=process_input($k);if($X!==false&&$X!==null)$wk[idf_escape($A)]=$X;}if($pm){if(!$wk)redirect($y);queries_redirect($y,lang(211),(bool)Driver::get()->update($a,$wk,$oj,$jm?0:1));if(is_ajax()){page_headers();page_messages();exit;}}else{$I=Driver::get()->insert($a,$wk);$bg=($I?last_id($I):0);queries_redirect($y,lang(212,($bg?" $bg":"")),(bool)$I);}}}$K=null;if($Z){$M=[];foreach($l
as$A=>$k){if(isset($k["privileges"]["select"])){$La=($_POST["clone"]&&$k["auto_increment"]?"''":convert_field($k));$M[]=($La?"$La AS ":"").idf_escape($A);}}$K=[];if(!support("table"))$M=["*"];if($M){$I=Driver::get()->select($a,$M,[$Z],$M,[],(isset($_GET["select"])?2:1));if(!$I)Admin::get()->addError(error());else{$K=$I->fetchAssoc();if(!$K)$K=false;}if(isset($_GET["select"])&&(!$K||$I->fetchAssoc()))$K=null;}}if(!support("table")&&!$l){if(!$Z){$I=Driver::get()->select($a,["*"],[],["*"]);$K=($I?$I->fetchAssoc():false);if(!$K)$K=[Driver::get()->primary=>""];}if($K){foreach($K
as$u=>$X){if(!$Z)$K[$u]=null;$l[$u]=["field"=>$u,"null"=>($u!=Driver::get()->primary),"auto_increment"=>($u==Driver::get()->primary)];}}}if(isset($_POST["save"])?$_POST["save"]:false){$Ui=[];foreach((isset($_POST["fields"])?$_POST["fields"]:[])as$u=>$X)$Ui[bracket_escape($u,true)]=$X;$K=$Ui+($K?:[]);}if($_POST["edit"]){$gd=array_filter($l,function($k){return!(isset($k["generated"])?$k["generated"]:null);});}else$gd=$l;edit_form($a,$gd,$K,$pm);}elseif(isset($_GET["create"])){$a=$_GET["create"];$zi=Driver::get()->getPartitionBy();$Ci=$zi?Driver::get()->getPartitionsInfo($a):[];$wj=referencable_primary($a);$ge=[];foreach($wj
as$ol=>$k)$ge[str_replace("`","``",$ol)."`".str_replace("`","``",$k["field"])]=$ol;$ii=[];$R=[];if($a!=""){$ii=fields($a);$R=table_status1($a);if(count($R)<2)Admin::get()->addError(lang(79));}$K=$_POST;$K["Comment"]=normalize_newlines($K["Comment"]);$K["fields"]=(array)$K["fields"];if($K["auto_increment_col"])$K["fields"][$K["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!Admin::get()->getErrors())Admin::get()->getSettings()->updateParameter("commentsOpened",isset($_POST["comments"])?$_POST["comments"]:null);if($_POST&&!process_fields($K["fields"])&&!Admin::get()->getErrors()){if($_POST["drop"])queries_redirect(substr(ME,0,-1),lang(213),drop_tables([$a]));else{$l=[];$Ea=[];$um=false;$ee=[];$hi=reset($ii);$Aa=" FIRST";foreach($K["fields"]as$u=>$k){$o=$ge[$k["type"]];$dm=($o!==null?$wj[$o]:$k);if($k["field"]!=""){if(!$k["generated"])$k["default"]=null;$hj=process_field($k,$dm);$Ea[]=[$k["orig"],$hj,$Aa];if(!$hi||$hj!==process_field($hi,$hi)){$l[]=[$k["orig"],$hj,$Aa];if($k["orig"]!=""||$Aa)$um=true;}if($o!==null)$ee[idf_escape($k["field"])]=($a!=""&&DIALECT!="sqlite"?"ADD":" ").format_foreign_key(['table'=>$ge[$k["type"]],'source'=>[$k["field"]],'target'=>[$dm["field"]],'on_delete'=>$k["on_delete"],]);$Aa=" AFTER ".idf_escape($k["field"]);}elseif($k["orig"]!=""){$um=true;$l[]=[$k["orig"]];}if($k["orig"]!=""){$hi=next($ii);if(!$hi)$Aa="";}}$Ai=[];if(in_array($K["partition_by"],$zi)){foreach($K
as$u=>$X){if(preg_match('~^partition~',$u))$Ai[$u]=$X;}foreach($Ai["partition_names"]as$u=>$A){if($A===""){unset($Ai["partition_names"][$u]);unset($Ai["partition_values"][$u]);}}$Ai["partition_names"]=array_values($Ai["partition_names"]);$Ai["partition_values"]=array_values($Ai["partition_values"]);if($Ai==$Ci)$Ai=[];}elseif(str_contains(isset($R["Create_options"])?$R["Create_options"]:"","partitioned"))$Ai=null;$Pg=lang(214);if($a==""){cookie("neo_engine",isset($K["Engine"])?$K["Engine"]:"");$Pg=lang(215);}$A=trim($K["name"]);$y=ME.(support("table")?"table=":"select=").urlencode($A);$I=alter_table($a,$A,(DIALECT=="sqlite"&&($um||$ee)?$Ea:$l),$ee,($K["Comment"]!=$R["Comment"]?$K["Comment"]:null),($K["Engine"]&&$K["Engine"]!=$R["Engine"]?$K["Engine"]:""),($K["Collation"]&&$K["Collation"]!=$R["Collation"]?$K["Collation"]:""),($K["Auto_increment"]!=""?number($K["Auto_increment"]):""),$Ai);if($I&&!Queries::$queries)redirect($y);queries_redirect($y,$Pg,$I);}}if($a!="")page_header(lang(36).": ".h($a),["table"=>$a,lang(36)]);else
page_header(lang(78),[lang(78)]);if(!$_POST){$fm=Driver::get()->getTypes();$K=["Engine"=>$_COOKIE["neo_engine"],"fields"=>[["field"=>"","type"=>(isset($fm["int"])?"int":(isset($fm["integer"])?"integer":"")),"on_update"=>""]],"partition_names"=>[""],];if($a!=""){$K=$R;$K["name"]=$a;$K["fields"]=[];if(!$_GET["auto_increment"])$K["Auto_increment"]="";foreach($ii
as$k){$k["generated"]=$k["generated"]?:(isset($k["default"])?"DEFAULT":"");$K["fields"][]=$k;}if($zi){$K+=$Ci;$K["partition_names"][]="";$K["partition_values"][]="";}}}$Mf=[];if($K["Collation"])$Mf[$K["Collation"]]=true;foreach($K["fields"]as$k){if($k["collation"])$Mf[$k["collation"]]=true;}$Cb=Admin::get()->getCollations(array_keys($Mf));$od=Driver::get()->engines();foreach($od
as$nd){if(!strcasecmp($nd,$K["Engine"])){$K["Engine"]=$nd;break;}}$Cg=max_input_vars(12,20);if($Cg){$Me=(count($K["fields"])>$Cg?"":" hidden");echo"<p".($Me?" id='max-fields' data-columns='$Cg'":"")." class='error$Me'>".max_input_vars_error()."\n";}echo"<form action='' method='post' id='form'>\n";if(support("columns")||$a==""){echo"<p>",lang(216),": ","<input class='input' name='name' data-maxlength='64' value='",h($K["name"]),"' autocapitalize='off'",(($a==""&&!$_POST)?" autofocus":""),">";if($od)echo" ",html_select("Engine",[""=>"(".lang(217).")"]+$od,$K["Engine"]),help_script_command("value",true);if($Cb&&!preg_match("~sqlite|mssql~",DIALECT))echo" ",html_select("Collation",[""=>"(".lang(92).")"]+$Cb,$K["Collation"]);echo" <input type='submit' class='button default' value='",lang(115),"'>","</p>";}if(support("columns")&&($a==""||!Driver::get()->isPartition($a))){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($K["fields"],$Cb,"TABLE",$ge);echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>",lang(48),": ","<input type='number' class='input size' name='Auto_increment' size='6' value='",h($K["Auto_increment"]),"'>";$Ob=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$Mb=$Ob?"":"hidden";if(support("comment")){echo
checkbox("comments",1,$Ob,lang(47),"editingCommentsClick(this, ".(support("move_col")?7:6).");","jsonly")," ";if(preg_match('~\n~',$K["Comment"]))echo"<textarea name='Comment' rows='2' cols='20'",($Mb?" class='$Mb'":""),">",h($K["Comment"]),"</textarea>";else
echo"<input name='Comment' value='",h($K["Comment"]),"' data-maxlength='",(Connection::get()->isMinVersion("5.5")?2048:60),"' class='input $Mb'>";}echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(115),"'>";}elseif($a!="")echo"<p>";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(166),"'>",confirm(lang(218,$a)),"</p>\n";if($zi&&(DIALECT=="sql"||$a=="")){echo"<div class='field-sets'>\n";$_i=preg_match('~RANGE|LIST~',$K["partition_by"]);print_fieldset_start("partition",lang(219),"split",(bool)$K["partition_by"]);echo"<p>",html_select("partition_by",array_merge([""],$zi),$K["partition_by"]),help_script_command("value.replace(/./, 'PARTITION BY \$&')",true),script("qsl('select').onchange = partitionByChange;"),"(<input class='input' name='partition' value='",h($K["partition"]),"'>) ",lang(50),": ","<input type='number' name='partitions' class='input size ",($_i||!$K["partition_by"]?"hidden":""),"' value='",h($K["partitions"]),"'>","</p>\n","<table id='partition-table'",($_i?"":" class='hidden'"),">\n","<thead><tr><th>",lang(220),"</th><th>",lang(52),"</th></tr></thead>\n";foreach($K["partition_names"]as$u=>$X){echo"<tr>","<td><input class='input' name='partition_names[]' value='",h($X),"' autocapitalize='off'>";if($u==count($K["partition_names"])-1)echo
script("qsl('input').oninput = partitionNameChange;");echo"</td>","<td><input class='input' name='partition_values[]' value='",h(isset($K["partition_values"][$u])?$K["partition_values"][$u]:""),"'></td>","</tr>\n";}echo"</table>\n","</p>\n";print_fieldset_end("partition");echo"</div>\n";}echo
input_token(),"</form>\n";}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$if=["PRIMARY","UNIQUE","INDEX"];$R=table_status1($a,true);$gf=Driver::get()->getIndexAlgorithms($R);$e=Connection::get();$zg=$e->isMariaDB();if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($zg?"10.0.5":"5.6")?'|InnoDB':'').'~i',$R["Engine"]))$if[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($zg?"10.2.2":"5.7")?'|InnoDB':'').'~i',$R["Engine"]))$if[]="SPATIAL";if($zg&&$e->isMinVersion("11.7")&&preg_match('~MyISAM|InnoDB~i',$R["Engine"]))$if[]="VECTOR";$t=indexes($a);$l=fields($a);$aj=[];if(DIALECT=="mongo"){$aj=$t["_id_"];unset($if[0]);unset($t["_id_"]);}$K=$_POST;if($K){$O=Admin::get()->getSettings();if($O->getParameter("indexOptions")!==null)$O->updateParameter("indexOptions",null);}if($_POST&&!$_POST["add"]&&!$_POST["drop_col"]){$Ga=[];foreach($K["indexes"]as$s){$A=$s["name"];if(in_array($s["type"],$if)){$c=[];$jg=[];$Dc=[];$Sh=[];$ff=$gf?(in_array($s["algorithm"],$gf)?$s["algorithm"]:first($gf)):"";$hf=(support("partial_indexes")?$s["partial"]:"");$wk=[];ksort($s["columns"]);foreach($s["columns"]as$u=>$b){if($b!=""){$v=isset($s["lengths"][$u])?$s["lengths"][$u]:null;$Bc=isset($s["descs"][$u])?$s["descs"][$u]:null;$Rh=isset($s["opclasses"][$u])?$s["opclasses"][$u]:null;$wk[]=($l[$b]?idf_escape($b):$b).($v?"(".(+$v).")":"").($Rh!=""?" ".idf_escape($Rh):"").($Bc?" DESC":"");$c[]=$b;$jg[]=($v?:null);$Dc[]=$Bc;$Sh[]="$Rh";}}$zd=$t[$A];if($zd){ksort($zd["columns"]);ksort($zd["lengths"]);ksort($zd["descs"]);if($s["type"]==$zd["type"]&&array_values($zd["columns"])===$c&&(!$zd["lengths"]||array_values($zd["lengths"])===$jg)&&array_values($zd["descs"])===$Dc&&(!$zd["opclasses"]||array_values($zd["opclasses"])===$Sh)&&(!$gf||$zd["algorithm"]===$ff)&&$zd["partial"]==$hf){unset($t[$A]);continue;}}if($c)$Ga[]=[$s["type"],$A,$wk,$ff,$hf];}}foreach($t
as$A=>$zd)$Ga[]=[$zd["type"],$A,"DROP"];if(!$Ga)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),lang(221),alter_indexes($a,$Ga));}page_header(lang(173).": ".h($a),["table"=>$a,lang(173)]);$Rd=array_keys($l);if($_POST["add"]){foreach($K["indexes"]as$u=>$s){if($s["columns"][count($s["columns"])]!="")$K["indexes"][$u]["columns"][]="";}$s=end($K["indexes"]);if($s["type"]||array_filter($s["columns"],'strlen'))$K["indexes"][]=["columns"=>[1=>""]];}if(!$K){foreach($t
as$u=>$s){$t[$u]["name"]=$u;$t[$u]["columns"][]="";}$t[]=["columns"=>[1=>""]];$K["indexes"]=$t;}$jg=(DIALECT=="sql"||DIALECT=="mssql");$Sh=Driver::get()->getIndexOpclasses();if($_POST)$_k=$_POST["options"];else{$_k=false;foreach($t
as$s){if(array_filter(isset($s["lengths"])?$s["lengths"]:[])||array_filter(isset($s["descs"])?$s["descs"]:[])||array_filter(isset($s["opclasses"])?$s["opclasses"]:[])||(isset($s["partial"])?$s["partial"]:"")!=""){$_k=true;break;}}}echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th id='label-type'>",lang(222),"</th>";$Xh="class='idxopts".($_k?"":" hidden")."'";if(count($gf)>1)echo"<th id='label-method' $Xh>",lang(223),doc_link(['sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'ha-and-performance/optimization-and-tuning/optimization-and-indexes/storage-engine-index-types',]),"</th>";echo"<th><input type='submit' hidden>",lang(53).($jg?"<span $Xh> (".lang(54).")</span>":"");if($jg||support("descidx"))echo
checkbox("options",1,$_k,lang(98),"indexOptionsShow(this.checked)","jsonly")."\n";echo"</th>","<th id='label-name'>",lang(224),"</th>";if(support("partial_indexes"))echo"<th id='label-condition' $Xh>",lang(55),"</th>";echo"<th>","<button name='add[0]' value='1' title='",lang(99),"' class='button light hidden'>",icon_solo("add"),"</button>","</th>","</tr></thead>\n";if($aj){echo"<tr><td>PRIMARY<td>";foreach($aj["columns"]as$b)echo
select_input(" disabled",array_combine($Rd,$Rd),$b),"<label><input type='checkbox' disabled>".lang(63)."</label> ";echo"<td><td>\n";}$Hf=1;foreach($K["indexes"]as$s){if(!$_POST["drop_col"]||$Hf!=key($_POST["drop_col"])){echo"<tr><td>",html_select("indexes[$Hf][type]",[-1=>""]+$if,$s["type"],($Hf==count($K["indexes"])?"indexesAddRow.call(this);":""),"label-type"),"</td>";if(count($gf)>1)echo"<td $Xh>",html_select("indexes[$Hf][algorithm]",array_merge([""],$gf),$s['algorithm'],"label-method"),"</td>";echo"<td>";ksort($s["columns"]);$q=1;foreach($s["columns"]as$u=>$b){echo"<span>".select_input(" name='indexes[$Hf][columns][$q]' title='".lang(44)."'",($l&&($b==""||$l[$b])?array_combine($Rd,$Rd):[]),$b,"partial(indexesChangeColumn, '".js_escape(DIALECT=="sql"?"":$_GET["indexes"]."_")."')"),"<span $Xh>";if($jg)echo"<input type='number' name='indexes[$Hf][lengths][$q]' class='input size' value='".(h(isset($s["lengths"][$u])?$s["lengths"][$u]:"")),"' title='".lang(97),"'>";if($Sh){$Rh=isset($s["opclasses"][$u])?$s["opclasses"][$u]:"";echo
html_select("indexes[$Hf][opclasses][$q]",[""=>"(".lang(225).")"]+array_combine($Sh,$Sh)+($Rh!=""?[$Rh=>$Rh]:[]),$Rh),'';}if(support("descidx"))echo
checkbox("indexes[$Hf][descs][$q]",1,isset($s["descs"][$u])?$s["descs"][$u]:false,lang(63));echo"<br></span></span>";$q++;}echo"</td>","<td><input name='indexes[$Hf][name]' value='",h($s["name"]),"' class='input' autocapitalize='off' aria-labelledby='label-name'></td>\n";if(support("partial_indexes"))echo"<td $Xh><input name='indexes[$Hf][partial]' value='".h($s["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>","<button name='drop_col[$Hf]' value='1' title='",lang(59),"' class='button light'>",icon_solo("remove"),"</button>",script("qsl('button').onclick = onRemoveIndexRowClick;"),"</td>\n";}$Hf++;}echo"</table>\n","</div>\n","<p>","<input type='submit' class='button default' value='",lang(115),"'>",input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["database"])){$K=$_POST;if($_POST&&!isset($_POST["add_x"])){$A=trim($K["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),lang(226),drop_databases([DB]));}elseif(DB!==$A){if(DB!=""){$_GET["db"]=$A;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($A),lang(227),rename_database($A,$K["collation"]));}else{$g=explode("\n",str_replace("\r","",$A));$Yk=true;$ag="";foreach($g
as$h){if(count($g)==1||$h!=""){if(!create_database($h,$K["collation"]))$Yk=false;$ag=$h;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($ag),lang(228),$Yk);}}else{if(!$K["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($A).(preg_match('~^[a-z0-9_]+$~i',$K["collation"])?" COLLATE $K[collation]":""),substr(ME,0,-1),lang(229));}}if(DB!="")page_header(lang(70).": ".h(DB),[lang(70)]);else
page_header(lang(76),[lang(76)]);$A=DB;if($_POST)$A=$K["name"];elseif(DB!="")$K["collation"]=db_collation(DB,collations());elseif(DIALECT=="sql"){foreach(get_vals("SHOW GRANTS")as$we){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$we,$z)&&$z[1]){$A=stripcslashes(idf_unescape("`$z[2]`"));break;}}}$Cb=Admin::get()->getCollations($K["collation"]?[$K["collation"]]:[]);echo"<form action='' method='post'>\n","<p>";if($_POST["add_x"]||strpos($A,"\n"))echo"<textarea id='name' name='name' rows='10' cols='40'>",h($A),"</textarea><br>\n";else
echo"<input class='input' name='name' id='name' value='",h($A),"' data-maxlength='64' autocapitalize='off' autofocus>\n";if($Cb)echo
html_select("collation",[""=>"(".lang(92).")"]+$Cb,$K["collation"]),doc_link(['sql'=>"charset-charsets.html",'mariadb'=>"reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations",]),"\n";echo"<input type='submit' class='button default' value='",lang(115),"'>\n";if(DB!="")echo"<input type='submit' class='button' name='drop' value='".lang(166)."'>".confirm(lang(218,DB))."\n";elseif(!$_POST["add_x"]&&$_GET["db"]=="")echo"<button name='add_x' value='1' title='",lang(99),"' class='button light'>",icon_solo("add"),"</button>\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["call"])){$oa=$_GET["name"]?:$_GET["call"];page_header(lang(230).": ".h($oa),[lang(230)]);$Mj=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$df=[];$ni=[];foreach($Mj["fields"]as$q=>$k){if(substr($k["inout"],-3)=="OUT"&&DIALECT=='sql')$ni[$q]="@".idf_escape($k["field"])." AS ".idf_escape($k["field"]);if(!$k["inout"]||substr($k["inout"],0,2)=="IN")$df[]=$q;}if($_POST){$kb=[];foreach($Mj["fields"]as$u=>$k){$X="";if(in_array($u,$df)){$X=process_input($k);if($X===false)$X="''";if(isset($ni[$u]))Connection::get()->query("SET @".idf_escape($k["field"])." = $X");}if(isset($ni[$u]))$kb[]="@".idf_escape($k["field"]);elseif(in_array($u,$df))$kb[]=$X;}$H=(isset($_GET["callf"])?"SELECT ":"CALL ").($Mj["returns"]&&$Mj["returns"]["type"]=="record"?"* FROM ":"").table($oa)."(".implode(", ",$kb).")";$Pk=microtime(true);$I=Connection::get()->multiQuery($H);$za=Connection::get()->getAffectedRows();echo
Admin::get()->formatSelectQuery($H,$Pk,!$I);if(!$I)echo"<p class='error'>".error()."\n";else{$Ub=connect();if($Ub)$Ub->selectDatabase(DB);do{$I=Connection::get()->storeResult();if(is_object($I))print_select_result($I,$Ub);else
echo"<p class='message'>".lang(231,$za)." <span class='time'>".@date("H:i:s")."</span>\n";}while(Connection::get()->nextResult());if($ni)print_select_result(Connection::get()->query("SELECT ".implode(", ",$ni)));}}echo"<form action='' method='post'>\n";if($df){echo"<table class='box'>\n";foreach($df
as$u){$k=$Mj["fields"][$u];$A=$k["field"];echo"<tr><th>".Admin::get()->getFieldName($k);$Y=isset($_POST["fields"][$A])?$_POST["fields"][$A]:"";if($Y!=""){if($k["type"]=="set")$Y=implode(",",$Y);}input($k,$Y,(string)(isset($_POST["function"][$A])?$_POST["function"][$A]:""));echo"\n";}echo"</table>\n";}echo"<p>\n","<input type='submit' class='button' value='",lang(230),"'>\n",input_token(),"</p>\n","</form>\n";$Lb=$Mj["comment"];if($Lb!==null&&$Lb!==""){$Lb=h(trim($Mj["comment"],"\n"));if(preg_match('~^ +~',$Lb,$_)){preg_match_all("~^($_[0]|$)~m",$Lb,$og);if(count($og[0])==substr_count($Lb,"\n"))$Lb=preg_replace("~^($_[0])~m","",$Lb);}$Lb=preg_replace('~(^|[^\n]\n)(Description|Parameters|Example)\n~',"$1\n<strong>$2</strong>\n",$Lb);echo"<pre class='comment'>$Lb</pre>\n";}}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$A=$_GET["name"];$K=$_POST;if($_POST&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$K["source"]=array_filter($K["source"],'strlen');ksort($K["source"]);$_l=[];foreach($K["source"]as$u=>$X)$_l[$u]=$K["target"][$u];$K["target"]=$_l;}if(DIALECT=="sqlite")$I=recreate_table($a,$a,[],[],[" $A"=>($K["drop"]?"":" ".format_foreign_key($K))]);else{$Ga="ALTER TABLE ".table($a);$I=($A==""||queries("$Ga DROP ".(DIALECT=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($A)));if(!$K["drop"])$I=queries("$Ga ADD".format_foreign_key($K));}queries_redirect(ME."table=".urlencode($a),($K["drop"]?lang(232):($A!=""?lang(233):lang(234))),(bool)$I);if(!$K["drop"])Admin::get()->addError(lang(235));}if($A!="")page_header(lang(236).": ".h($A),["table"=>$a,lang(236)]);else
page_header(lang(177).": ".h($a),["table"=>$a,lang(177)]);if($_POST){ksort($K["source"]);if($_POST["change"]||$_POST["change-js"])$K["target"]=[];else$K["source"][]="";}elseif($A!=""){$ge=foreign_keys($a);$K=$ge[$A];$K["source"][]="";}else{$K["table"]=$a;$K["source"]=[""];}echo"<form action='' method='post'>\n";$Hk=array_keys(fields($a));if($K["db"]!="")Connection::get()->selectDatabase($K["db"]);if($K["ns"]!=""){$ji=get_schema();set_schema($K["ns"]);}$vj=array_keys(array_filter(table_status('',true),'AdminNeo\fk_support'));$_l=$vj?array_keys(fields(in_array($K["table"],$vj)?$K["table"]:reset($vj))):[];$Nh="this.form['change-js'].value = '1'; this.form.submit();";echo"<p>","<span id='label-table'>",lang(237),":</span> ",html_select("table",$vj,$K["table"],$Nh,"label-table");if(DIALECT!="sqlite"){$sc=[];foreach(Admin::get()->getDatabases()as$h){if(!information_schema($h))$sc[]=$h;}echo"<span id='label-db'>",lang(238),":</span> ",html_select("db",$sc,$K["db"]!=""?$K["db"]:$_GET["db"],$Nh,"label-db");}echo
input_hidden("change-js"),"<noscript><input type='submit' class='button' name='change' value='",lang(239),"'></noscript>","</p>\n","<table>","<thead><tr><th id='label-source'>",lang(174),"<th id='label-target'>",lang(175),"</thead>\n";$Hf=0;foreach($K["source"]as$u=>$X){echo"<tr>","<td>".html_select("source[".(+$u)."]",[-1=>""]+$Hk,$X,($Hf==count($K["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$u)."]",$_l,isset($K["target"][$u])?$K["target"][$u]:null,"","label-target");$Hf++;}echo"</table>\n","<noscript><p><input type='submit' class='button' name='add' value='",lang(240),"'></p></noscript>","<p>\n","<span id='label-delete'>".lang(94),":</span> ",html_select("on_delete",[-1=>""]+Driver::get()->getOnActions(),$K["on_delete"],"","label-delete"),"<span id='label-update'>".lang(93),":</span> ",html_select("on_update",[-1=>""]+Driver::get()->getOnActions(),$K["on_update"],"","label-update");if(DRIVER=='pgsql')echo
html_select("deferrable",['NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'],$K["deferrable"]);echo
doc_link(['sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"architecture/server-constraints/foreign-key-constraints",]),"</p>\n<p>","<input type='submit' class='button default' value='",lang(115),"'>";if($A!="")echo"<input type='submit' class='button' name='drop' value='",lang(166),"'>",confirm(lang(218,$A));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["view"])){$a=$_GET["view"];$K=$_POST;$ki="VIEW";if(DIALECT=="pgsql"&&$a!=""){$P=table_status1($a);$ki=strtoupper($P["Engine"]);}if($_POST){$A=trim($K["name"]);$La=" AS\n$K[select]";$y=ME."table=".urlencode($A);$Pg=lang(241);$U=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$A&&DIALECT!="sqlite"&&$U=="VIEW"&&$ki=="VIEW")query_redirect((DIALECT=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($A).$La,$y,$Pg);else{$Bl=$A."_adminneo_".uniqid();drop_create("DROP $ki ".table($a),"CREATE $U ".table($A).$La,"DROP $U ".table($A),"CREATE $U ".table($Bl).$La,"DROP $U ".table($Bl),($_POST["drop"]?substr(ME,0,-1):$y),lang(242),$Pg,lang(243),$a,$A);}}if(!$_POST&&$a!=""){$K=view($a);$K["name"]=$a;$K["materialized"]=($ki!="VIEW");if($j=error())Admin::get()->addError($j);}if($a!="")page_header(lang(37).": ".h($a),["table"=>$a,lang(37)]);else
page_header(lang(244),[lang(244)]);echo"<form action='' method='post'>\n","<p>",lang(224),":","<input class='input' name='name' value='",h($K["name"]),"' data-maxlength='64' autocapitalize='off'>\n";if(support("materializedview"))echo
checkbox("materialized",1,$K["materialized"],lang(167));echo"</p>\n<p>";textarea("select",$K["select"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(115),"'>\n";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(166),"'>\n",confirm(lang(218,$a));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["event"])){$ea=$_GET["event"];$uf=["YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND"];$Rk=["ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE"];$K=$_POST;if($_POST){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($ea),substr(ME,0,-1),lang(245));elseif(in_array($K["INTERVAL_FIELD"],$uf)&&isset($Rk[$K["STATUS"]])){$Xj="\nON SCHEDULE ".($K["INTERVAL_VALUE"]?"EVERY ".q($K["INTERVAL_VALUE"])." $K[INTERVAL_FIELD]".($K["STARTS"]?" STARTS ".q($K["STARTS"]):"").($K["ENDS"]?" ENDS ".q($K["ENDS"]):""):"AT ".q($K["STARTS"]))." ON COMPLETION".($K["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($ea!=""?lang(246):lang(247)),(bool)queries(($ea!=""?"ALTER EVENT ".idf_escape($ea).$Xj.($ea!=$K["EVENT_NAME"]?"\nRENAME TO ".idf_escape($K["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($K["EVENT_NAME"]).$Xj)."\n".$Rk[$K["STATUS"]]." COMMENT ".q($K["EVENT_COMMENT"]).rtrim(" DO\n$K[EVENT_DEFINITION]",";").";"));}}if($ea!="")page_header(lang(248).": ".h($ea),[lang(248)]);else
page_header(lang(249),[lang(249)]);if(!$K&&$ea!=""){$L=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($ea));$K=reset($L);}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(224),"</th><td>","<input class='input' name='EVENT_NAME' value='",h($K["EVENT_NAME"]),"' data-maxlength='64' autocapitalize='off'>","</td></tr>\n","<tr><th title='datetime'>",lang(250),"</th><td>","<input class='input' name='STARTS' value='",h("$K[EXECUTE_AT]$K[STARTS]"),"'>","</td></tr>\n","<tr><th title='datetime'>",lang(251),"</th><td>","<input class='input' name='ENDS' value='",h($K["ENDS"]),"'>","</td></tr>\n","<tr><th>",lang(252),"</th><td>","<input type='number' name='INTERVAL_VALUE' value='",h($K["INTERVAL_VALUE"]),"' class='input size'> ",html_select("INTERVAL_FIELD",$uf,$K["INTERVAL_FIELD"]),"</td></tr>\n","<tr><th>",lang(158),"</th><td>",html_select("STATUS",$Rk,$K["STATUS"]),"</td></tr>\n","<tr><th>",lang(47),"</th><td>","<input class='input' name='EVENT_COMMENT' value='",h($K["EVENT_COMMENT"]),"' data-maxlength='64'>","</td></tr>\n","<tr><th></th><td>",checkbox("ON_COMPLETION","PRESERVE",$K["ON_COMPLETION"]=="PRESERVE",lang(253)),"</td></tr>\n","</table>\n","<p>";textarea("EVENT_DEFINITION",$K["EVENT_DEFINITION"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(115),"'>";if($ea!="")echo"<input type='submit' class='button' name='drop' value='",lang(166),"'>",confirm(lang(218,$ea));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["procedure"])){$oa=($_GET["name"]?:$_GET["procedure"]);$Mj=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$K=$_POST;$K["fields"]=(array)$K["fields"];if($_POST&&!process_fields($K["fields"])){foreach($K["fields"]as$u=>$k){if($k["field"]=="")unset($K["fields"][$u]);}$Ih=routine_id($oa,routine($_GET["procedure"],$Mj));$sh=routine_id($K["name"],$K);$dc=create_routine($Mj,$K);$y=substr(ME,0,-1);$Pg=lang(254);if(!$_POST["drop"]&&$Ih==$sh&&(DIALECT!="sql"||Connection::get()->isMariaDB()))query_redirect(substr_replace($dc,' OR REPLACE',6,0),$y,$Pg);else{$Bl="$K[name]_adminer_".uniqid();drop_create("DROP $Mj $Ih",$dc,"DROP $Mj $sh",create_routine($Mj,["name"=>$Bl]+$K),"DROP $Mj ".routine_id($Bl,$K),$y,lang(255),$Pg,lang(256),$oa,$K["name"]);}}if($oa!=""){$T=isset($_GET["function"])?lang(257):lang(258);page_header($T.": ".h($oa),[$T]);}else{$T=isset($_GET["function"])?lang(259):lang(260);page_header($T,[$T]);}if(!$_POST){if($oa=="")$K["language"]="sql";else{$K=routine($_GET["procedure"],$Mj);$K["name"]=$oa;}}$pb=get_vals("SHOW CHARACTER SET");sort($pb);$Nj=routine_languages();echo"<form action='' method='post' id='form'>\n","<p>",lang(224),": ","<input class='input' name='name' value='",h($K["name"]),"' data-maxlength='64' autocapitalize='off'>";if($Nj)echo"<span id='label-language'>",lang(10),":</span> ",html_select("language",$Nj,$K["language"],"","label-language");echo"<input type='submit' class='button default' value='",lang(115),"'>","</p>\n","<div class='scrollable'>\n","<table class='nowrap' id='edit-fields'>\n";edit_fields($K["fields"],$pb,$Mj);if(isset($_GET["function"])){echo"<tbody><tr>";if(support("move_col"))echo"<th></th>";echo"<th>",lang(261),"</th>";edit_type("returns",(array)$K["returns"],$pb,[],(DIALECT=="pgsql"?["void","trigger"]:[]));echo"<td></td>","</tr></tbody>\n";}echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>";textarea("definition",$K["definition"],20);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(115),"'>";if($oa!="")echo"<input type='submit' class='button' name='drop' value='",lang(166),"'>",confirm(lang(218,$oa));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["check"])){$a=$_GET["check"];$A=$_GET["name"];$K=$_POST;if($K){if(DIALECT=="sqlite")$Yk=recreate_table($a,$a,[],[],[],"",[],"$A",($K["drop"]?"":$K["clause"]));else{$Yk=($A==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($A)));if(!$K["drop"])$Yk=(bool)queries("ALTER TABLE ".table($a)." ADD".($K["name"]!=""?" CONSTRAINT ".idf_escape($K["name"]):"")." CHECK ($K[clause])");}queries_redirect(ME."table=".urlencode($a),($K["drop"]?lang(262):($A!=""?lang(263):lang(264))),$Yk);}if($A!="")page_header(lang(265).": ".h($A),["table"=>$a,lang(265)]);else
page_header(lang(179).": ".h($a),["table"=>$a,lang(179)]);if(!$K){$ub=Driver::get()->checkConstraints($a);$K=["name"=>$A,"clause"=>$ub[$A]];}echo"<form action='' method='post'>\n","<p>";if(DIALECT!="sqlite")echo
lang(224).': <input name="name" value="'.h($K["name"]).'" class="input" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(['sql'=>"create-table-check-constraints.html",'mariadb'=>"reference/sql-statements/data-definition/constraint",],"?"),"</p>\n<p>";textarea("clause",$K["clause"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(115),"'>";if($A!="")echo"<input type='submit' class='button' name='drop' value='",lang(166),"'>",confirm(lang(218,$A));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$A=isset($_GET["name"])?$_GET["name"]:"";$Zl=trigger_options();$K=trigger($A,$a)+["Trigger"=>$a."_bi"];if($_POST){if(in_array($_POST["Timing"],$Zl["Timing"])&&in_array($_POST["Event"],$Zl["Event"])&&in_array($_POST["Type"],$Zl["Type"])){$Lh=" ON ".table($a);$Vc="DROP TRIGGER ".idf_escape($A).(DIALECT=="pgsql"?$Lh:"");$y=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($Vc,$y,lang(266));else{if($A!="")queries($Vc);queries_redirect($y,($A!=""?lang(267):lang(268)),(bool)queries(create_trigger($Lh,$_POST)));if($A!="")queries(create_trigger($Lh,$K+["Type"=>reset($Zl["Type"])]));}}$K=$_POST;}if($A!="")page_header(lang(269).": ".h($A),["table"=>$a,lang(269)]);else
page_header(lang(181).": ".h($a),["table"=>$a,lang(181)]);echo"<form action='' method='post' id='form'>\n","<table class='box box-light'>\n","<tr><th id='label-time'>",lang(270),"</th><td>",html_select("Timing",$Zl["Timing"],$K["Timing"],"triggerChange(/^".js_escape_re($a)."_[ba][iud]$/, '".js_escape($a)."', this.form);","label-time"),"</td></tr>\n","<tr><th id='label-event'>",lang(271),"</th><td>",html_select("Event",$Zl["Event"],$K["Event"],"this.form['Timing'].onchange();","label-event");if(in_array("UPDATE OF",$Zl["Event"]))echo" <input name='Of' value='".h($K["Of"])."' class='input hidden'>";echo"</td></tr>\n","<tr><th id='label-type'>",lang(45),"</th><td>",html_select("Type",$Zl["Type"],$K["Type"],"","label-type"),"</td></tr>\n","</table>\n","<p>",lang(224),"<input class='input' name='Trigger' value='",h($K["Trigger"]),"' data-maxlength='64' autocapitalize='off'>","</p>\n",script("gid('form')['Timing'].onchange();"),"<p>";textarea("Statement",$K["Statement"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(115),"'>";if($A!="")echo"<input type='submit' class='button' name='drop' value='",lang(166),"'>",confirm(lang(218,$A));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["user"])){$qa=$_GET["user"];$ej=[""=>["All privileges"=>""]];foreach(get_rows("SHOW PRIVILEGES")as$K){foreach(explode(",",($K["Privilege"]=="Grant option"?"":$K["Context"]))as$Yb)$ej[$Yb=="File access on server"?"Server Admin":$Yb][$K["Privilege"]]=$K["Comment"];}unset($ej["Server Admin"]["Usage"]);foreach($ej["Tables"]as$u=>$X)unset($ej["Databases"][$u]);$rh=[];if($_POST){foreach($_POST["objects"]as$u=>$X)$rh[$X]=(array)$rh[$X]+(array)$_POST["grants"][$u];}$ye=[];if(isset($_GET["host"])&&($I=Connection::get()->query("SHOW GRANTS FOR ".q($qa)."@".q($_GET["host"])))){while($K=$I->fetchRow()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$K[0],$z)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$z[1],$_,PREG_SET_ORDER)){foreach($_
as$X){if($X[1]!="USAGE")$ye["$z[2]$X[2]"][$X[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$K[0]))$ye["$z[2]$X[2]"]["GRANT OPTION"]=true;}}}}$Li=!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8");if($_POST){$Kh=(isset($_GET["host"])?q($qa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $Kh",ME."privileges=",lang(272));else{$uh=q($_POST["user"])."@".q($_POST["host"]);$Ei=$_POST["pass"];$gc=false;$I=true;if($Kh!=$uh){$gc=(bool)queries("CREATE USER $uh IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($Ei));$I=$gc;}elseif($Ei!="")$I=(bool)queries("SET PASSWORD FOR $uh = ".($Li||$_POST["hashed"]?q($Ei):"PASSWORD(".q($Ei).")"));if($I){$Jj=[];foreach($rh
as$Bh=>$we){if(isset($_GET["grant"]))$we=array_filter($we);$we=array_keys($we);if(isset($_GET["grant"]))$Jj=array_diff(array_keys(array_filter($rh[$Bh],'strlen')),$we);elseif($Kh==$uh){$Hh=array_keys((array)$ye[$Bh]);$Jj=array_diff($Hh,$we);$we=array_diff($we,$Hh);unset($ye[$Bh]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$Bh,$z)&&(!grant(false,$Jj,$z[2],$z[1],$uh)||!grant(true,$we,$z[2],$z[1],$uh))){$I=false;break;}}}if($I&&isset($_GET["host"])){if($Kh!=$uh)queries("DROP USER $Kh");elseif(!isset($_GET["grant"])){foreach($ye
as$Bh=>$Jj){if(preg_match('~^(.+)(\(.*\))?$~U',$Bh,$z))grant(false,array_keys($Jj),$z[2],$z[1],$uh);}}}if($I&&!Queries::$queries)redirect(ME."privileges=");queries_redirect(ME."privileges=",(isset($_GET["host"])?lang(273):lang(274)),$I);if($gc)Connection::get()->query("DROP USER $uh");}}$T=isset($_GET["host"])?lang(6).": ".h("$qa@$_GET[host]"):lang(190);$Ml=isset($_GET["host"])?h($qa):lang(190);page_header($T,["privileges"=>['',lang(73)],$Ml]);if($_POST){$K=$_POST;$ye=$rh;}else{$K=$_GET+["host"=>Connection::get()->getValue("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")];if($ye)$ye[".*"]=[];elseif(DB!="")$ye[idf_escape(addcslashes(DB,"%_\\")).".*"]=[];else$ye["*.* "]=[];}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(5),"</th>","<td><input class='input' name='host' data-maxlength='60' value='",h($K["host"]),"' autocapitalize='off'></td>\n","<tr><th>",lang(6),"</th>","<td><input class='input' name='user' data-maxlength='80' value='",h($K["user"]),"' autocapitalize='off'></td>\n",'<tr><th>',lang(30),"</th>","<td><input class='input' name='pass' id='pass' value='",h($K["pass"]),"' autocomplete='new-password'>";if(!$Li)echo
checkbox("hashed",1,$K["hashed"],lang(275),"typePassword(this.form['pass'], this.checked);");echo"</td>\n";if(!$K["hashed"])echo
script("typePassword(gid('pass'));");echo"</table>\n","<div class='scrollable'><table class='checkable'>\n","<thead><tr><th colspan='2'>".lang(73).doc_link(['sql'=>"grant.html#priv_level","mariadb"=>"reference/sql-statements/account-management-sql-statements/grant#privilege-levels"])."</th>";$q=0;foreach($ye
as$Bh=>$we){echo"<th>";if($Bh=="*.*")echo"*.*",input_hidden("objects[$q]","*.*");else
echo"<input class='input' name='objects[$q]' value='".h(trim($Bh))."' size='10' autocapitalize='off'>";echo"</th>";$q++;}echo"</tr></thead>\n";foreach([""=>"","Server Admin"=>lang(5),"Databases"=>lang(31),"Tables"=>lang(9),"Procedures"=>lang(276),]as$Yb=>$Bc){foreach((array)$ej[$Yb]as$dj=>$Lb){echo"<tr>";if($Bc)echo"<td>$Bc</td>";echo"<td".(!$Bc?" colspan='2'":"").' lang="en" title="'.h($Lb).'">'.h($dj)."</td>";$q=0;foreach($ye
as$Bh=>$we){$A="'grants[$q][".h(strtoupper($dj))."]'";$Y=$we[strtoupper($dj)];$ij=strpos($Bh,"@")!==false;$qh=$Bh==".*";$Ca=$dj=="All privileges";$xe=$dj=="Grant option";if($Bh=="*.*"&&$dj=="Proxy")echo"<td></td>";elseif($ij&&$dj!="Proxy"&&!$xe)echo"<td></td>";elseif($Yb=="Server Admin"&&$Bh!=(isset($ye["*.*"])?"*.*":".*")&&!(($ij||$qh)&&$dj=="Proxy"))echo"<td></td>";elseif(isset($_GET["grant"]))echo"<td><select name=$A>"."<option></option>"."<option value='1'".($Y?" selected":"").">".lang(277)."</option>"."<option value='0'".($Y=="0"?" selected":"").">".lang(278)."</option>"."</select></td>";else{echo"<td class='center'><label class='block'>","<input type='checkbox' name=$A value='1'".($Y?" checked":"").($Ca?" id='grants-$q-all'":(!$xe?" class='grants-$q'":"")).">";if($Ca)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheckAll('.grants-$q'); };");elseif(!$xe)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$q-all'); };");echo"</label>";}$q++;}echo"</tr>";}}echo"</table></div>\n","<p>","<input type='submit' class='button default' value='",lang(115),"'>\n";if(isset($_GET["host"]))echo"<input type='submit' class='button' name='drop' value='",lang(166),"'>\n",confirm(lang(218,"$qa@$_GET[host]"));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST){$Sf=0;foreach((array)$_POST["kill"]as$X){if(kill_process($X))$Sf++;}queries_redirect(ME."processlist=",lang(279,$Sf),$Sf||!$_POST["kill"]);}}page_header(lang(156),[lang(156)]);echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap checkable'>\n";$q=-1;foreach(process_list()as$q=>$K){if(!$q){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($K
as$u=>$X)echo"<th>$u".doc_link(['sql'=>"show-processlist.html#processlist_".strtolower($u),'mariadb'=>"reference/sql-statements/administrative-sql-statements/show/show-processlist",]);echo"</thead>\n","<tbody>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$K[DIALECT=="sql"?"Id":"pid"],0):"");foreach($K
as$u=>$X)echo"<td>".($X!=""&&((DIALECT=="sql"&&$u=="Info"&&preg_match("~Query|Killed~",$K["Command"]))||(DIALECT=="pgsql"&&$u=="query")||(DIALECT=="oracle"&&$u=="sql_text"))?"<code class='jush-".DIALECT."'>".truncate_utf8($X,100).'</code> <a href="'.h(ME.($K["db"]!=""?"db=".urlencode($K["db"])."&":"")."sql=".urlencode($X)).'">'.icon("edit").lang(280).'</a>':h($X));echo"\n";}if($q>=0)echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: event => tableClick(event, true)});");echo"</table>\n","</div>\n","<p>";if(support("kill"))echo($q+1)."/".lang(281,max_connections()),"<p><input type='submit' class='button' value='".lang(282)."'>\n";echo
input_token(),"</p>\n","</form>\n",script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$R=table_status1($a);$t=indexes($a);$l=fields($a);$ge=column_foreign_keys($a);$Dh=$R["Oid"];$Kj=[];$c=[];$ck=[];$ai=[];$Fl=null;foreach($l
as$u=>$k){$A=Admin::get()->getFieldName($k);$lh=html_entity_decode(strip_tags($A),ENT_QUOTES);if(isset($k["privileges"]["select"])&&$A!=""){$c[$u]=$lh;if(is_shortable($k))$Fl=Admin::get()->processSelectionLength();}if(isset($k["privileges"]["where"])&&$A!="")$ck[$u]=$lh;if(isset($k["privileges"]["order"])&&$A!="")$ai[$u]=$lh;$Kj+=$k["privileges"];}list($M,$ze)=Admin::get()->processSelectionColumns($c,$t);$M=array_unique($M);$ze=array_unique($ze);$Af=count($ze)<count($M);$Z=Admin::get()->processSelectionSearch($l,$t);$D=Admin::get()->processSelectionOrder($l,$t);$w=Admin::get()->processSelectionLimit();if($_GET["modify"]&&!Admin::get()->isDataEditAllowed())redirect(ME."select=".urlencode($a));if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$km=>$K){$La=convert_field($l[key($K)]);$M=[$La?:idf_escape(key($K))];$Z[]=where_check($km,$l);$J=Driver::get()->select($a,$M,$Z,$M);if($J)echo
first($J->fetchRow());}exit;}$aj=$nm=[];foreach($t
as$s){if($s["type"]=="PRIMARY"){$aj=array_flip($s["columns"]);$nm=($M?$aj:[]);foreach($nm
as$u=>$X){if(in_array(idf_escape($u),$M))unset($nm[$u]);}break;}}if($Dh&&!$aj){$aj=$nm=[$Dh=>0];$t[]=["type"=>"PRIMARY","columns"=>[$Dh]];}$O=Admin::get()->getSettings();if($_POST){$Um=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$ub=[];foreach($_POST["check"]as$qb)$ub[]=where_check($qb,$l);$Um[]="((".implode(") OR (",$ub)."))";}$Um=($Um?"\nWHERE ".implode(" AND ",$Um):"");if($_POST["export"]){$O->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers($a);Admin::get()->dumpTable($a,"");$oe=($M?implode(", ",$M):"*").convert_fields($c,$l,$M)."\nFROM ".table($a);$Be=($ze&&$Af?"\nGROUP BY ".implode(", ",$ze):"").($D?"\nORDER BY ".implode(", ",$D):"");if(!is_array($_POST["check"])||$aj)$H="SELECT $oe$Um$Be";else{$hm=[];foreach($_POST["check"]as$X)$hm[]="(SELECT".limit($oe,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$l).$Be,1).")";$H=implode(" UNION ALL ",$hm);}Admin::get()->dumpData($a,"table",$H);exit;}if($_POST["save"]||$_POST["delete"]){$I=true;$za=0;$wk=[];if(!$_POST["delete"]){$kk=array_keys($_POST["fields"]+$_POST["function"]);foreach($kk
as$A){$X=process_input($l[$A]);if($X!==null&&($_POST["clone"]||$X!==false))$wk[idf_escape($A)]=($X!==false?$X:idf_escape($A));}}if($_POST["delete"]||$wk){if($_POST["clone"])$H="INTO ".table($a)." (".implode(", ",array_keys($wk)).")\nSELECT ".implode(", ",$wk)."\nFROM ".table($a);if($_POST["all"]||($aj&&is_array($_POST["check"]))||$Af){$I=($_POST["delete"]?Driver::get()->delete($a,$Um):($_POST["clone"]?queries("INSERT $H$Um".Driver::get()->getInsertReturningSql($a)):Driver::get()->update($a,$wk,$Um)));$za=Connection::get()->getAffectedRows();if(is_object($I))$za+=$I->getRowsCount();}else{foreach((array)$_POST["check"]as$X){$Tm="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$l);$I=($_POST["delete"]?Driver::get()->delete($a,$Tm,1):($_POST["clone"]?queries("INSERT".limit1($a,$H,$Tm)):Driver::get()->update($a,$wk,$Tm,1)));if(!$I)break;$za+=Connection::get()->getAffectedRows();}}}$Pg=lang(283,$za);if($_POST["clone"]&&$I&&$za==1){$bg=last_id($I);if($bg)$Pg=lang(212," $bg");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$Pg,(bool)$I);if(!$_POST["delete"]){$gd=array_filter($l,function($k){return!(isset($k["generated"])?$k["generated"]:null);});edit_form($a,$gd,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])Admin::get()->addError(lang(284));else{$Yk=true;$za=0;foreach($_POST["val"]as$km=>$K){$wk=[];foreach($K
as$u=>$X){$u=bracket_escape($u,true);$wk[idf_escape($u)]=(preg_match('~char|text~',$l[$u]["type"])||$X!=""?Admin::get()->processFieldInput($l[$u],$X):"NULL");}$Yk=(bool)Driver::get()->update($a,$wk," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($km,$l),($Af||$aj?0:1)," ");if(!$Yk)break;$za+=Connection::get()->getAffectedRows();}queries_redirect(remove_from_uri(),lang(283,$za),$Yk);}}elseif(!is_string($m=get_file("csv_file",true)))Admin::get()->addError(upload_error($m));elseif(!preg_match('~~u',$m))Admin::get()->addError(lang(285));else{$O->updateParameter("exportFormat",$_POST["import_format"]);$Gb=array_keys($l);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$m,$_);$za=count($_[0]);Driver::get()->begin();$lk=($_POST["import_format"]=="csv;"?";":($_POST["import_format"]=="tsv"?"\t":","));$L=[];foreach($_[0]as$u=>$X){preg_match_all("~((?>\"[^\"]*\")+|[^$lk]*)$lk~",$X.$lk,$Ag);if(!$u&&!array_diff($Ag[1],$Gb)){$Gb=$Ag[1];$za--;}else{$wk=[];foreach($Ag[1]as$q=>$_b)$wk[idf_escape($Gb[$q])]=($_b==""&&$l[$Gb[$q]]["null"]?"NULL":q(preg_match('~^".*"$~s',$_b)?str_replace('""','"',substr($_b,1,-1)):$_b));$L[]=$wk;}}$Yk=!$L||Driver::get()->insertUpdate($a,$L,$aj);if($Yk)Driver::get()->commit();queries_redirect(remove_from_uri("page"),lang(286,$za),$Yk);Driver::get()->rollback();}}$ol=Admin::get()->getTableName($R);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(56).": $ol",[$ol]);$rf=null;if(isset($Kj["insert"])||!support("table")){$rf=[];foreach((array)$_GET["where"]as$X){if(isset($ge[$X["col"]])&&count($ge[$X["col"]])==1&&($X["op"]=="="||(!$X["op"]&&(is_array($X["val"])||!preg_match('~[_%]~',$X["val"])))))$rf["preset"."[".bracket_escape($X["col"])."]"]=$X["val"];}}Admin::get()->printTableMenu($R,$rf);if(!$c&&support("table"))echo"<p class='error'>".lang(287).($l?".":": ".error())."\n";else{echo"<form id='form' action=''>\n","<div hidden>";hidden_fields_get();if(DB!=""){echo
input_hidden("db",DB);if(isset($_GET["ns"]))echo
input_hidden("ns",$_GET["ns"]);}echo
input_hidden("select",$a),"<input type='submit' class='button' value='".lang(56)."'>","</div>\n","<div class='field-sets'>\n";Admin::get()->printSelectionColumns($M,$c);Admin::get()->printSelectionSearch($Z,$ck,$t);Admin::get()->printSelectionOrder($D,$ai,$t);Admin::get()->printSelectionLimit($w);Admin::get()->printSelectionLength($Fl);Admin::get()->printSelectionAction($t);echo"</div>\n</form>\n";$E=isset($_GET["page"])?$_GET["page"]:null;if($E=="last"){$me=Connection::get()->getValue(count_rows($a,$Z,$Af,$ze));$E=(int)floor(max(0,intval($me)-1)/$w);}else{$me=false;$E=(int)$E;}$dk=$M;$_e=$ze;if(!$dk){$dk[]="*";$ac=convert_fields($c,$l,$M);if($ac)$dk[]=substr($ac,2);}foreach($M
as$u=>$X){$k=$l[idf_unescape($X)];if($k&&($La=convert_field($k)))$dk[$u]="$La AS $X";}if(DIALECT=="pgsql"||DIALECT=="mssql"){foreach((array)$_GET["columns"]as$u=>$X){if(isset($dk[$u])&&$X["fun"])$dk[$u].=" AS ".idf_escape(apply_sql_function($X["fun"],($X["col"]!=""?$X["col"]:"*")));}}if(!$Af&&$nm){foreach($nm
as$u=>$X){$dk[]=idf_escape($u);if($_e)$_e[]=idf_escape($u);}}$I=Driver::get()->select($a,$dk,$Z,$_e,$D,$w,$E,true);if(!$I)echo"<p class='error'>".error()."\n";else{if(DIALECT=="mssql"&&$E)$I->seek($w*$E);$L=[];while($K=$I->fetchAssoc()){if($E&&DIALECT=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["modify"]&&$L){$Ig=max_input_vars(count($L[0])+1,20);echo($Ig&&count($L)>$Ig?"<p class='error'>".max_input_vars_error()."\n":"");}echo"<form id='selection_form' action='' method='post' enctype='multipart/form-data'>\n","<div class='table-footer-parent'>\n";if($_GET["page"]!="last"&&$w&&$ze&&$Af&&DIALECT=="sql")$me=Connection::get()->getValue(" SELECT FOUND_ROWS()");$hd=false;if(!$L)echo"<p class='message'>".lang(90)."\n";else{$Va=Admin::get()->getBackwardKeys($a,$ol);echo"<div class='scrollable'>\n","<table id='table' class='nowrap checkable'>\n","<thead><tr>";if($ze||!$M){echo"<th class='actions'><input type='checkbox' id='all-page' class='jsonly' title='".lang(288)."'>".script("gid('all-page').onclick = partial(formCheck, /^check/);","");if(Admin::get()->isDataEditAllowed())echo" <a href='",h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."' title='",lang(289),"'>",icon_solo("edit-all"),"</a>";}$nh=[];$re=[];reset($M);$qj=1;foreach($L[0]as$u=>$X){if(!isset($nm[$u])){$fk=key($M);$X=isset($_GET["columns"][$fk])?$_GET["columns"][$fk]:[];$k=$l[$M?($X?$X["col"]:current($M)):$u];$A=($k?Admin::get()->getFieldName($k,$qj):(isset($X["fun"])?"*":h($u)));if($A!=""){$qj++;$nh[$u]=$A;$b=idf_escape($u);$Ve=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($u);$Bc="&desc%5B0%5D=1";$Zh=isset($D[0])?$D[0]:"";$Ek=preg_replace('~ DESC( NULLS LAST)?$~','',$Zh);$Gk=($Ek==$b||$Ek==$u);echo"<th id='th[".h(bracket_escape($u))."]'".($Gk?" aria-sort='".($Ek==$Zh?"ascending":"descending")."'":"").">";$qe=apply_sql_function(isset($X["fun"])?$X["fun"]:null,$A);$Fk=isset($k["privileges"]["order"])||(isset($X["fun"])?$X["fun"]:null);if($Fk)echo'<a href="',h($Ve.($Gk&&$Ek==$Zh?$Bc:'')),'">',"$qe</a>";else
echo$qe;echo"<span class='column'>";if($Fk)echo"<a href='".h($Ve.$Bc)."' title='".lang(63)."' class='button light'>",icon_solo("arrow-down"),"</a>";if(!isset($X["fun"])&&isset($k["privileges"]["where"]))echo"<a href='#fieldset-search' title='".lang(60)."' class='button light jsonly'>",icon_solo("search"),"</a>",script("qsl('a').onclick = partial(selectSearch, '".js_escape($u)."');");echo"</span>";}$re[$u]=isset($X["fun"])?$X["fun"]:null;next($M);}}$jg=[];if($_GET["modify"]){foreach($L
as$K){foreach($K
as$u=>$X)$jg[$u]=max($jg[$u],min(40,strlen(utf8_decode($X))));}}if($Va)echo"<th>".lang(19)."</th>";echo"</thead>\n","<tbody>\n";if(is_ajax())ob_end_clean();foreach(Admin::get()->fillForeignDescriptions($L,$ge)as$jh=>$K){$jm=unique_array($L[$jh],$t);if(!$jm){$jm=[];reset($M);foreach($L[$jh]as$u=>$X){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($M)))$jm[$u]=$X;next($M);}}$km="";foreach($jm
as$u=>$X){$k=isset($l[$u])?$l[$u]:null;$_f=$k&&is_blob($k);if((DIALECT=="sql"||DIALECT=="pgsql")&&$k&&($_f||preg_match('~char|text|enum|set~',$k["type"]))&&strlen($X)>64){$u=(strpos($u,'(')?$u:idf_escape($u));$u="MD5(".($_f||DIALECT!='sql'||preg_match("~^utf8~",isset($k["collation"])?$k["collation"]:"")?$u:"CONVERT($u USING ".charset(Connection::get()).")").")";$X=md5($_f?(string)Connection::get()->formatValue($X,$k):$X);}$km
.="&".($X!==null?urlencode("where[".bracket_escape($u)."]")."=".urlencode($X===false?"f":$X):"null%5B%5D=".urlencode($u));}echo"<tr>";if($ze||!$M){echo"<td class='actions'>",checkbox("check[]",substr($km,1),in_array(substr($km,1),(array)$_POST["check"]));if(!$Af&&Admin::get()->isDataEditAllowed())echo" <a href='",h(ME."edit=".urlencode($a).$km),"' class='edit' title='",lang(39),"'>",icon_solo("edit"),"</a>";}reset($M);foreach($K
as$u=>$X){if(isset($nh[$u])){$b=current($M);$k=isset($l[$u])?$l[$u]:null;$x="";if($k&&is_blob($k)&&$X!="")$x=ME.'download='.urlencode($a).'&field='.urlencode($u).$km;if(!$x&&$X!==null){foreach((array)$ge[$u]as$o){if(count($ge[$u])==1||end($o["source"])==$u){$x="";foreach($o["source"]as$q=>$Hk)$x
.=where_link($q,$o["target"][$q],$L[$jh][$Hk]);$x=($o["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($o["db"]),ME):ME).'select='.urlencode($o["table"]).$x;if($o["ns"])$x=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($o["ns"]),$x);if(count($o["source"])==1)break;}}}if($b=="COUNT(*)"){$x=ME."select=".urlencode($a);$q=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$jm))$x
.=where_link($q++,$W["col"],$W["val"],$W["op"]);}foreach($jm
as$Kf=>$W)$x
.=where_link($q++,$Kf,$W);}$zh=$X===null;$We=select_value($X,$x,$k,$Fl);$ud=bracket_escape($u);$r=h("val[$km][$ud]");$Vi=isset($_POST["val"][$km][$ud])?$_POST["val"][$km][$ud]:null;$pm=isset($k["privileges"]["update"])?$k["privileges"]["update"]:false;$fd=!is_array($X)&&!($k&&is_blob($k))&&is_utf8((string)$X)&&$L[$jh][$u]==$X&&!$re[$u]&&!(isset($k["generated"])?$k["generated"]:false);$U=($b&&preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$b,$_)?$l[idf_unescape($_[2])]["type"]:(isset($k["type"])?$k["type"]:null));$dh=$U=="money"||($b&&preg_match('~^SUM\((.+)\)~',$b,$_)&&$l[idf_unescape($_[1])]["type"])=="money";$Dl=$U&&preg_match('~text|json|lob~',$U);$Ah=($U&&preg_match(number_type(),$U))||($b&&preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|UNIX_TIMESTAMP|TIME_TO_SEC|COUNT|SUM)\(~',$b));$yb=$Ah&&($zh||is_numeric(strip_tags($We))||$dh)?"class='number'":"";echo"<td id='$r' $yb";if(($_GET["modify"]&&$fd&&!$zh)||$Vi!==null){$hd=true;$Ee=h($Vi!==null?$Vi:$X);echo" data-editing='true'>".($Dl?"<textarea name='$r' cols='30' rows='".(substr_count($X,"\n")+1)."'>$Ee</textarea>":"<input class='input' name='$r' value='$Ee' size='$jg[$u]'>");}else{$yg=strpos($We,"<i>…</i>");if($pm)echo" data-text='".($yg?2:($Dl?1:0))."'".($fd?"":" data-warning='".lang(290)."'");echo">$We";}}next($M);}if($Va){echo"<td>";Admin::get()->printBackwardKeys($Va,$L[$jh]);echo"</td>";}echo"</tr>\n";}if(is_ajax())exit;echo"</tbody>\n",script("mixin(qs('#table tbody'), {onclick: event => tableClick(event, false, ".(Admin::get()->isDataEditAllowed()?"true":"false")."), ondblclick: event => tableClick(event, true), onkeydown: onEditingKeydown});"),"</table>\n",script("initToggles(gid('table'));"),"</div>\n";}if(!is_ajax()){if($L||$E){$wd=true;if($_GET["page"]!="last"){if(!$w||(count($L)<$w&&($L||!$E)))$me=($E?$E*$w:0)+count($L);elseif(DIALECT!="sql"||!$Af){$me=($Af?false:found_rows($R,$Z));if($me<max(1e4,2*($E+1)*$w))$me=first(slow_query(count_rows($a,$Z,$Af,$ze)));elseif(DIALECT=='sql'||DIALECT=='pgsql')$wd=false;}}$ri=($w!==null&&($me===false||$me>$w||$E));if($ri){if(($me===false?count($L)+1:$me-$E*$w)>$w)echo'<p class="links">','<a href="',h(remove_from_uri("page")."&page=".($E+1)),'" class="loadmore">',icon("expand"),lang(291),'</a>',script("qsl('a').onclick = partial(loadNextPage, $w, '".js_escape(lang(292))."');","");echo"\n";}echo"<div class='table-footer'><div class='field-sets'>\n";if($ri){$Gg=($me===false?$E+(count($L)>=$w?2:1):(int)floor(($me-1)/$w));$Rc="<li>…</li>";echo"<fieldset><legend>".lang(293)."</legend>";if(DIALECT!="simpledb"){echo"<div id='fieldset-pagination' class='fieldset-content'><ul class='pagination'>",pagination(0,$E);if($E>5)echo$Rc;for($q=max(1,$E-4);$q<min($Gg,$E+5);$q++)echo
pagination($q,$E);if($Gg>0){if($E+5<$Gg)echo$Rc;echo($wd&&$me!==false?pagination($Gg,$E):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$Gg'>".lang(294)."</a>");}echo"</ul></div>";}else{echo"<div id='fieldset-pagination'><ul class='pagination'>",pagination(0,$E);if($E>1)echo$Rc;if($E)echo
pagination($E,$E);if($Gg>$E){echo
pagination($E+1,$E);if($Gg>$E+1)echo$Rc;}echo"</ul></div>";}echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(295)."</legend><div class='fieldset-content'>";$Kc=($wd?"":"~ ").$me;echo
checkbox("all",1,0,($me!==false?($wd?"":"~ ").lang(194,$me):""),"countRows.call(this, '$Kc');")."\n","</div></fieldset>\n";if(Admin::get()->isDataEditAllowed()){echo"<fieldset",($_GET["modify"]?'':' class="jsonly"'),">","<legend>",lang(289),"</legend>";$Sj=($_GET["modify"]?"":" data-inline-edit='1'".($hd?"":" disabled"));echo"<div class='fieldset-content'",($_GET["modify"]?"":" title='".lang(284)."'"),">","<input type='submit' class='button' id='modify-save' value='",lang(115),"'",$Sj,">","</div>","</fieldset>\n","<fieldset>","<legend>",lang(165)," <span id='selected'></span></legend>","<div class='fieldset-content'>","<input type='submit' class='button' name='edit' value='",lang(39),"'> ","<input type='submit' class='button' name='clone' value='",lang(280),"'> ","<input type='submit' class='button' name='delete' value='",lang(119),"'>",confirm(),"</div>","</fieldset>\n";}$ie=Admin::get()->getDumpFormats();foreach((array)$_GET["columns"]as$b){if($b["fun"]){unset($ie['sql']);break;}}if($ie){print_fieldset_start("export",lang(75)." <span id='selected2'></span>","export");echo
html_select("format",$ie,$O->getParameter("exportFormat"));$oi=Admin::get()->getDumpOutputs();echo($oi?" ".html_select("output",$oi,$O->getParameter("exportOutput")):"")," <input type='submit' class='button' name='export' value='".lang(75)."'>\n";print_fieldset_end("export");}echo"</div></div>\n",script("initTableFooter()");}echo"</div>\n";if(Admin::get()->isDataEditAllowed()){echo"<p>","<a href='#import'>",icon("import"),lang(74),"</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"</p>","<p id='import'",($_POST["import"]?"":" class='hidden'"),">";if(ini_bool("file_uploads"))echo"<input type='file' name='csv_file'> ",html_select("import_format",["csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"],$O->getParameter("exportFormat"))," <input type='submit' class='button default' name='import' value='".lang(74)."'>",file_upload_form_script("selection_form","csv_file");else
echo
lang(201);echo"</p>";}echo
input_token(),"</form>\n",(!$ze&&$M?"":script("tableCheck();"));}else
echo"</div>\n";}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$P=isset($_GET["status"]);$T=$P?lang(158):lang(157);page_header($T,[$T]);$Dm=($P?Admin::get()->getStatusVariables():Admin::get()->getServerVariables());if(!$Dm)echo"<p class='message'>",lang(90),"</p>\n";else{echo"<div class='scrollable'><table>\n";foreach($Dm
as$K){echo"<tr>";$u=array_shift($K);echo"<th><code class='jush-".DIALECT.($P?"status":"set")."'>".h($u)."</code></th>";foreach($K
as$X)echo"<td>",nl2br(h($X)),"</td>";echo"</tr>\n";}echo"</table></div>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$bl=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$f=[];$qc=null;foreach(table_status()as$A=>$R){$f["Comment-$A"]=h($R["Comment"]);if(!is_view($R)||preg_match('~materialized~i',$R["Engine"])){$f["Engine-$A"]=h($R["Engine"]);$Bb=isset($R["Collation"])?$R["Collation"]:"";if($Bb==""){if($qc===null)$qc=db_collation(DB,collations())??"";$Bb=$qc;}$f["Collation-$A"]=h($Bb);foreach($bl+["Auto_increment"=>0,"Rows"=>0]as$u=>$X){if($R[$u]!=""){$X=format_number($R[$u]);if($X>=0)$f["$u-$A"]=($u=="Rows"?format_rows($R):$X);if(isset($bl[$u]))$bl[$u]+=($R["Engine"]!="InnoDB"||$u!="Data_free"?$R[$u]:0);}elseif(array_key_exists($u,$R))$f["$u-$A"]="?";}}}if(function_exists('AdminNeo\db_status'))$bl=db_status();foreach($bl
as$u=>$X)$f["sum-$u"]=format_number($X);echo
json_encode($f,JSON_UNESCAPED_UNICODE);}elseif($_GET["script"]=="kill")Connection::get()->query("KILL ".number($_POST["kill"]));else{$f=[];foreach(count_tables(Admin::get()->getDatabases(false))as$h=>$X){$f["tables-$h"]=$X;$f["size-$h"]=db_size($h);}echo
json_encode($f,JSON_UNESCAPED_UNICODE);}exit;}else{$xl=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($xl&&!$_POST["search"]){$I=true;$Pg="";if(DIALECT=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$I=truncate_tables($_POST["tables"]);$Pg=lang(296);}elseif($_POST["move"]){$I=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Pg=lang(297);}elseif($_POST["copy"]){$I=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Pg=lang(298);}elseif($_POST["drop"]){if($_POST["views"])$I=drop_views($_POST["views"]);if($I&&$_POST["tables"])$I=drop_tables($_POST["tables"]);$Pg=lang(299);}elseif(DIALECT=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$Q){foreach(get_rows("PRAGMA integrity_check(".q($Q).")")as$K)$Pg
.="<b>".h($Q)."</b>: ".h($K["integrity_check"])."<br>";}}elseif(DIALECT!="sql"){$I=(DIALECT=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),(array)$_POST["tables"]));$Pg=lang(300);}elseif(!$_POST["tables"])$Pg=lang(79);elseif($I=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('AdminNeo\idf_escape',$_POST["tables"])))){while($K=$I->fetchAssoc())$Pg
.="<b>".h($K["Table"])."</b>: ".h($K["Msg_text"])."<br>";}queries_redirect($_SERVER["REQUEST_URI"],$Pg,(bool)$I);}if($_GET["ns"]=="")page_header(lang(31).": ".h(DB),true);else
page_header(lang(189).": ".h($_GET["ns"]),true);Admin::get()->printDatabaseMenu();if($_GET["ns"]===""){echo"<h2 id='schemas'>".lang(301)."</h2>\n";$Zj=Admin::get()->getSchemas();if(!$Zj)echo"<p class='message'>".lang(302)."\n";else{echo"<div class='scrollable'>\n","<table class='nowrap'>\n",'<thead><tr class="wrap"><th>',lang(189),"</th></tr></thead>";foreach($Zj
as$A)echo"<tr><th><a href='",h(ME),"ns=".urlencode($A),"' title='",lang(303),"'>".h($A)."</a></th></tr>";echo'</table></div>';}echo'<p class="links"><a href="'.h(ME).'scheme=">'.icon("database-add").lang(77)."</a>\n";}else{echo"<h2 id='tables-views'>".lang(304)."</h2>\n";$sl=['sql'=>'show-table-status.html','mariadb'=>'reference/sql-statements/administrative-sql-statements/show/show-table-status'];$qc=db_collation(DB,collations());$c=["Engine"=>["label"=>lang(169),"doc"=>doc_link(['sql'=>'storage-engines.html','mariadb'=>'server-usage/storage-engines']),],];if($qc!="")$c["Collation"]=["label"=>lang(46),"doc"=>doc_link(['sql'=>'charset-charsets.html','mariadb'=>'reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations']),];$c+=["Data_length"=>["label"=>lang(305),"doc"=>doc_link($sl+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286']),"link"=>"create","title"=>lang(36),],"Index_length"=>["label"=>lang(306),"doc"=>doc_link($sl+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT']),"link"=>"indexes","title"=>lang(173),],"Data_free"=>["label"=>lang(307),"doc"=>doc_link($sl),"link"=>"edit","title"=>lang(8),],"Auto_increment"=>["label"=>lang(48),"doc"=>doc_link(['sql'=>'example-auto-increment.html','mariadb'=>'reference/data-types/auto_increment']),"link"=>"auto_increment=1&create","title"=>lang(36),],"Rows"=>["label"=>lang(308),"doc"=>doc_link($sl+['pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286']),"link"=>"select","title"=>lang(34),],];if(support("comment"))$c["Comment"]=["label"=>lang(47),"doc"=>doc_link($sl+['pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE']),];$D=(is_string($_GET["order"])?$_GET["order"]:"");$Cc=null;if(preg_match('~^(.+)-(asc|desc)$~',$D,$z)){$D=$z[1];$Cc=($z[2]=="desc");}if($D!="__table"&&!isset($c[$D]))$D="";if($Cc===null)$Cc=isset($c[$D]["link"]);$Xm=($D!=""&&$D!="__table")||support("fast_status");$vl=($Xm?table_status():tables_list());if(!$vl)echo"<p class='message'>".lang(79)."\n";else{echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n";if(support("table")){echo"<div class='field-sets'>\n","<fieldset><legend>".lang(309)." <span id='selected2'></span></legend><div class='fieldset-content'>",html_select("op",Admin::get()->getOperators(),isset($_POST["op"])?$_POST["op"]:Driver::get()->getLikeOperator()),"<input type='search' class='input' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = event => bodyKeydown(event, 'search');","")," <input type='submit' class='button' name='search' value='".lang(60)."'>\n","</div></fieldset>\n","</div>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable'>\n",'<thead><tr class="wrap">','<th class="actions"><input id="check-all" type="checkbox" class="input jsonly" title="'.lang(188).'">'.script("gid('check-all').onclick = partial(formCheck, /^(tables|views)\[/);","");$kh=($D==""||$D=="__table");$nl=($kh&&!$Cc?ME."order=__table-desc":substr(ME,0,-1));$mh=($kh&&DIALECT!="sqlite");echo'<th'.($mh?" aria-sort='".($Cc?"descending":"ascending")."'":'').'><a href="'.h($nl).'">'.lang(9).'</a>';foreach($c
as$u=>$b){$Ec=($u===$D?!$Cc:isset($b["link"]));echo'<th'.($u===$D?" aria-sort='".($Cc?"descending":"ascending")."'":'').'><a href="'.h(ME)."order=$u-".($Ec?"desc":"asc").'">'.$b["label"].'</a>'.$b["doc"];}echo"</thead>\n","<tbody>\n";if($D=="__table"){if($Cc)$vl=array_reverse($vl,true);}elseif($D){uasort($vl,function($sa,$Sa)use($D,$Cc){$Ym=isset($sa[$D])?$sa[$D]:null;$Zm=isset($Sa[$D])?$Sa[$D]:null;$I=($Ym<$Zm?-1:($Ym>$Zm?1:0));return($Cc?-$I:$I);});}$bl=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$S=0;foreach($vl
as$A=>$P){$Hm=($Xm?is_view($P):$P!==null&&!preg_match('~table|sequence~i',$P));$nd=($Xm?(isset($P["Engine"])?$P["Engine"]:""):$P);$r=h("Table-".$A);echo'<tr><th class="actions">'.checkbox(($Hm?"views[]":"tables[]"),$A,in_array("$A",$xl,true),"","","",$r);if(!Admin::get()->getSettings()->isSelectionPreferred()&&(support("table")||support("indexes")))$ua="table";else$ua="select";echo"<th><a href='",h(ME),"$ua=",urlencode($A),"' id='$r'>",h($A),"</a></th>";if($Hm&&!preg_match('~materialized~i',$nd)){$T=lang(168);$Hb=count($c)-(support("comment")?2:1);echo'<td colspan="'.$Hb.'">'.(support("view")?"<a href='".h(ME)."view=".urlencode($A)."' title='".lang(37)."'>$T</a>":$T),"<td align='right'><a href='".h(ME)."select=".urlencode($A)."' title='".lang(34)."'>?</a>";}else{foreach($c
as$u=>$b){if($u=="Comment")continue;$r=" id='$u-".h($A)."'";$x=isset($b["link"])?$b["link"]:"";if(!$x){$X="";if($Xm){$X=isset($P[$u])?$P[$u]:"";if($u=="Collation"&&$X=="")$X=$qc;}echo"<td$r>".h($X);continue;}$X="?";if($Xm){$B=isset($P[$u])?$P[$u]:"";if(is_numeric($B)&&$B>=0){$X=($u=="Rows"?format_rows($P):format_number($B));if(isset($bl[$u])&&($nd!="InnoDB"||$u!="Data_free"))$bl[$u]+=$B;}}echo"<td align='right'>".(support("table")||$u=="Rows"||(support("indexes")&&$u!="Data_length")?"<a href='".h(ME."$x=").urlencode($A)."'$r title='".$b["title"]."'>".h($X)."</a>":"<span$r>".h($X)."</span>");}$S++;}echo(support("comment")?"<td id='Comment-".h($A)."'>".($Xm?h(isset($P["Comment"])?$P["Comment"]:""):""):""),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: event => tableClick(event, true)});"),"<tfoot><tr>","<td><th>".lang(281,count($vl)),"<td>".h(DIALECT=="sql"?Connection::get()->getValue("SELECT @@default_storage_engine"):""),($qc!=""?"<td>".h($qc):"");if($Xm&&function_exists('AdminNeo\db_status'))$bl=db_status();foreach($bl
as$u=>$al)echo"<td align='right' id='sum-$u'>".($Xm?format_number($al):"");echo"<td></td><td></td>";if(support("comment"))echo"<td></td>";echo"</tr></tfoot>\n","</table>\n","</div>\n",($Xm?"":script("ajaxSetHtml('".js_escape(ME)."script=db');"));if(Admin::get()->isDataEditAllowed()){echo"<div class='table-footer'><div class='field-sets'>\n";$Am="<input type='submit' class='button' value='".lang(310)."'> ".help_script("VACUUM");$Vh="<input type='submit' class='button' name='optimize' value='".lang(311)."'> ".help_script(DIALECT=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE");echo"<fieldset><legend>".lang(165)." <span id='selected'></span></legend><div class='fieldset-content'>".(DIALECT=="sqlite"?$Am."<input type='submit' class='button' name='check' value='".lang(312)."'> ".help_script("PRAGMA integrity_check"):(DIALECT=="pgsql"?$Am.$Vh:(DIALECT=="sql"?"<input type='submit' class='button' value='".lang(313)."'> ".help_script("ANALYZE TABLE").$Vh."<input type='submit' class='button' name='check' value='".lang(312)."'> ".help_script("CHECK TABLE")."<input type='submit' class='button' name='repair' value='".lang(314)."'> ".help_script("REPAIR TABLE"):"")))."<input type='submit' class='button' name='truncate' value='".lang(315)."'> ".help_script(DIALECT=="sqlite"?"DELETE":("TRUNCATE".(DIALECT=="pgsql"?"":" TABLE"))).confirm()."<input type='submit' class='button' name='drop' value='".lang(166)."'>".help_script("DROP TABLE").confirm()."\n";$g=(support("scheme")?Admin::get()->getSchemas():Admin::get()->getDatabases());echo"</div></fieldset>\n";if(count($g)!=1&&DIALECT!="sqlite"){echo"<fieldset><legend>".lang(316)." <span id='selected3'></span></legend><div>";$h=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($g?html_select("target",$g,$h,"","label-move"):'<input class="input" name="target" value="'.h($h).'" autocapitalize="off">')," <input type='submit' class='button' name='move' value='".lang(317)."'>",(support("copy")?" <input type='submit' class='button' name='copy' value='".lang(318)."'> ".checkbox("overwrite",1,$_POST["overwrite"],lang(319)):""),"</div></fieldset>\n";}echo
input_hidden("all"),script("qsl('input').onclick = partial(countTables, $S);"),input_token(),"</div></div>\n",script("initTableFooter()");}echo"</div>\n","</form>\n",script("tableCheck();");}echo'<p class="links"><a href="',h(ME),'create=">',icon("table-add"),lang(78),"</a>\n";if(support("view"))echo'<a href="',h(ME),'view=">',icon("view-add"),lang(244),"</a>\n";if(support("routine")){echo"<h2 id='routines'>".lang(184)."</h2>\n";$Oj=routines();if($Oj){$Nb=$Oj[0]["ROUTINE_COMMENT"]!==null;echo"<table>\n",'<thead><tr>','<th>',lang(224),'</th><td>',lang(45),'</td><td>',lang(261),"</td>";if($Nb)echo"<td>",lang(47),"</td>";echo"<td></td>","</tr></thead>\n";foreach($Oj
as$K){$A=($K["SPECIFIC_NAME"]==$K["ROUTINE_NAME"]?"":"&name=".urlencode($K["ROUTINE_NAME"]));echo'<tr>','<th><a href="',h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($K["SPECIFIC_NAME"]).$A),'">',h($K["ROUTINE_NAME"]),'</a></th>','<td>',h($K["ROUTINE_TYPE"]),'</td>','<td>',h($K["DTD_IDENTIFIER"]),'</td>';if($Nb)echo'<td>',truncate_utf8(preg_replace('~\s{2,}~'," ",trim($K["ROUTINE_COMMENT"])),50),'</td>';echo'<td><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($K["SPECIFIC_NAME"]).$A).'">'.lang(176)."</a></td>";}echo"</table>\n";}echo'<p class="links">';if(support("procedure"))echo'<a href="',h(ME),'procedure=">',icon("function-add"),lang(260),"</a>";echo'<a href="',h(ME),'function=">',icon("function-add"),lang(259),"</a>\n","</p>\n";}if(support("event")){echo"<h2 id='events'>".lang(185)."</h2>\n";$L=get_rows("SHOW EVENTS");if($L){echo"<table>\n","<thead><tr><th>".lang(224)."<td>".lang(320)."<td>".lang(250)."<td>".lang(251)."<td></thead>\n";foreach($L
as$K)echo"<tr>","<th>".h($K["Name"]),"<td>".($K["Execute at"]?lang(321)."<td>".h($K["Execute at"]):lang(252)." ".h($K["Interval value"])." ".h($K["Interval field"])."<td>".h($K["Starts"])),"<td>".h($K["Ends"]),'<td><a href="'.h(ME).'event='.urlencode($K["Name"]).'">'.lang(176).'</a>';echo"</table>\n";$vd=Connection::get()->getValue("SELECT @@event_scheduler");if($vd&&$vd!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($vd)."\n";}echo'<p class="links"><a href="',h(ME),'event=">',icon("event-add"),lang(249),"</a></p>\n";}}}page_footer();