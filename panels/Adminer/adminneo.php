<?php
/**
 * AdminNeo - Powerful database manager in a single PHP file
 * v5.7.1
 *
 * Compiled with
 * drivers:   mysql
 * languages: all
 * themes:    all
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
inject($ya,Config$Rb,Settings$O,Locale$pg){$this->admin=$ya;$this->config=$Rb;$this->settings=$O;$this->locale=$pg;}}abstract
class
Origin
extends
Plugin{private$errors=[];private
static$instance=null;static
function
create(array$Rb=[],array$Bi=[]){if(self::$instance)die("Admin instance already exists.\n");$ya=new
static();if(!$Rb&&file_exists("adminneo-config.php")){$Rb=include_once("adminneo-config.php");if(!is_array($Rb)){$Rb=[];$hg="href=https://github.com/adminneo-org/adminneo#configuration ".target_blank();$ya->addError(lang(0,"<b>adminneo-config.php</b>")." <a $hg>".lang(1)."</a>");}}$Rb=new
Config($Rb);$O=new
Settings($Rb);if(!$Bi&&file_exists("adminneo-plugins.php")){$Bi=include_once("adminneo-plugins.php");if(!is_array($Bi)){$Bi=[];$hg="href=https://github.com/adminneo-org/adminneo#plugins ".target_blank();$ya->addError(lang(0,"<b>adminneo-plugins.php</b>")." <a $hg>".lang(1)."</a>");}}self::$instance=$Bi?new
Pluginer($ya,$Bi):$ya;$ya->inject(self::$instance,$Rb,$O,Locale::get());foreach($Bi
as$Ai)$Ai->inject(self::$instance,$Rb,$O,Locale::get());return
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
verifyDefaultPassword($F){$Fe=$this->config->getDefaultPasswordHash();if($Fe===null||$Fe==="")return
lang(2);elseif(!password_verify($F,$Fe))return
lang(3);return
true;}function
authenticate($V,$F){if($F==""){$Fe=$this->config->getDefaultPasswordHash();if($Fe===null)return
lang(4,target_blank());else
return$Fe==="";}return
true;}function
getPrivateKey($bc=false){return
get_private_key($bc);}function
getBruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
getServerName($N,$sj=true,$Gd=null){if($N==""){if(!$sj)return"";$N=Connection::exists()?Connection::get()->getDefaultServerName():"";if($N=="")return$Gd!==null?$Gd:lang(5);$ck=null;}else$ck=$this->config->getServer($N);return$ck?$ck->getName():preg_replace('~^https?://~',"",$N);}abstract
function
getDatabase();function
getDatabases($Zd=true){$g=$this->filterListWithWildcards(get_databases($Zd),$this->config->getHiddenDatabases(),false,Driver::get()->getSystemDatabases());if(DB!=""&&!in_array(DB,$g))array_unshift($g,DB);return$g;}function
getSchemas($lh=false){$Ie=$this->config->getHiddenSchemas();if($lh&&!in_array("__system",$Ie))$Ie[]="__system";$Mj=$this->filterListWithWildcards(schemas(),$Ie,false,Driver::get()->getSystemSchemas());if(isset($_GET["ns"])&&$_GET["ns"]!=""&&!in_array($_GET["ns"],$Mj))array_unshift($Mj,$_GET["ns"]);return$Mj;}function
getCollations(array$Ef=[]){$wm=$this->config->getVisibleCollations();$Td=$wm?array_merge($wm,$Ef):[];return$this->filterListWithWildcards(collations(),$Td,true);}private
function
filterListWithWildcards(array$nm,array$Td,$Gf,array$Sk=[]){if(!$nm||!$Td)return$nm;$s=array_search("__system",$Td);if($s!==false){unset($Td[$s]);$Td=array_merge($Td,$Sk);}array_walk($Td,function(&$Y){$Y=str_replace('\\*',".*",preg_quote($Y,"~"));});$vi='~^('.implode("|",$Td).')$~';return$this->filterListWithPattern($nm,$vi,$Gf);}private
function
filterListWithPattern(array$nm,$vi,$Gf){$I=[];foreach($nm
as$u=>$Y){if(is_array($Y)){if($Ik=$this->filterListWithPattern($Y,$vi,$Gf))$I[$u]=$Ik;}elseif(($Gf&&preg_match($vi,$Y))||(!$Gf&&!preg_match($vi,$Y)))$I[$u]=$Y;}return$I;}abstract
function
getQueryTimeout();function
sendHeaders(){}function
updateCspHeader(array&$fc){}function
printFavicons(){$Db=validate_color_variant($this->config->getColorVariant());echo"<link rel='icon' type='image/x-icon' href='",link_files("favicon-$Db.ico",[]),"' sizes='32x32'>\n","<link rel='icon' type='image/svg+xml' href='",link_files("favicon-$Db.svg",[]),"'>\n","<link rel='apple-touch-icon' href='",link_files("apple-touch-icon-$Db.png",[]),"'>\n";}abstract
function
printToHead();function
getCssUrls(){$cm=$this->config->getCssUrls();foreach(["adminneo.css","adminneo-light.css","adminneo-dark.css"]as$n){if(file_exists($n))$cm[]="$n?v=".filemtime($n);}return$cm;}function
isLightModeForced(){return$this->isColorSchemeForced(false);}function
isDarkModeForced(){return$this->isColorSchemeForced(true);}private
function
isColorSchemeForced($kc){$Rg=$kc?Settings::$ColorSchemeDark:Settings::$ColorSchemeLight;$Sg=$kc?Settings::$ColorSchemeLight:Settings::$ColorSchemeDark;$Pd=file_exists("adminneo-$Rg.css");$Qd=file_exists("adminneo-$Sg.css");if($Pd&&!$Qd)return
true;return$this->settings->getColorScheme()==$Rg&&!($Pd
xor$Qd);}function
getJsUrls(){$cm=$this->config->getJsUrls();$n="adminneo.js";if(file_exists($n))$cm[]="$n?v=".filemtime($n);return$cm;}abstract
function
printLoginForm();function
getLoginFormRow($Kd,$Of,$k){if($Of)return"<tr><th>$Of</th><td>$k</td></tr>\n";else
return"$k\n";}function
printLogout(){echo"<div class='logout'>","<form action='' method='post'>\n",h($_GET["username"]),"<input type='submit' class='button' name='logout' value='",lang(6),"' id='logout'>",input_token(),"</form>","</div>\n";}function
getTableName(array$Wk){return
h($Wk["Name"]);}abstract
function
getFieldName(array$k,$D=0);function
formatComment($Kb){return
h($Kb);}abstract
function
printTableMenu(array$Wk,$lf);function
getForeignKeys($Q){return
foreign_keys($Q);}function
getBackwardKeys($Q,$Uk){if(!$this->settings->isRelationLinks())return[];$L=backward_keys($Q);$If=[];foreach($L
as$K){$r=$K["table_schema"].".".$K["table_name"];$If[$r]["schema"]=$K["table_schema"];$If[$r]["table"]=$K["table_name"];$If[$r]["constraints"][$K["constraint_name"]][$K["column_name"]]=$K["referenced_column_name"];}foreach($If
as$r=>$u){$A=$this->admin->getTableName(table_status1($u["table"],true));if($A!=""){$Pj=preg_quote($Uk);$Zj="(:|\\s*-)?\\s+";$If[$r]["name"]=(preg_match("(^$Pj$Zj(.+)|^(.+?)$Zj$Pj\$)iu",$A,$z)?$z[2].$z[3]:$A);}else
unset($If[$r]);}return$If;}function
printBackwardKeys(array$Ua,array$K){foreach($Ua
as$u){foreach($u["constraints"]as$Ub){$Cg=preg_replace('~&ns=[^&]+&~',"&ns=".urldecode($u["schema"])."&",ME);$x=$Cg.'select='.urlencode($u["table"]);$q=0;foreach($Ub
as$b=>$X){if(!isset($K[$X]))continue
2;$x
.=where_link($q++,$b,$K[$X]);}$A=preg_replace('(^'.preg_quote($_GET["select"]).(substr($_GET["select"],-1)=="s"?"?":"").'_)',"_",$u["name"]);$T=implode(", ",array_keys($Ub));echo"<a href='".h($x)."' title='".h($T)."'>".h($A)."</a>";$x=$Cg.'edit='.urlencode($u["table"]);foreach($Ub
as$b=>$X)$x
.="&preset".urlencode("[".bracket_escape($b)."]")."=".urlencode($K[$X]);echo"<a href='".h($x)."' title='".lang(7)."'>",icon_solo("add"),"</a> ";}}}abstract
function
formatSelectQuery($H,$Ak,$Fd=false);abstract
function
formatMessageQuery($H,$vl,$Fd=false);abstract
function
formatSqlCommandQuery($H);function
printAfterSqlCommand(){}abstract
function
getTableDescriptionFieldName($Q);abstract
function
fillForeignDescriptions(array$L,array$ce);function
getFieldValueLink($X,$k){if(is_mail($X))return"mailto:$X";if(is_web_url($X))return$X;return
null;}abstract
function
formatSelectionValue($X,$x,$k,$Zh);abstract
function
formatFieldValue($Y,array$k);abstract
function
printTableStructure(array$l);abstract
function
printTablePartitions(array$li);abstract
function
printRelatedTables(array$S);abstract
function
printTableIndexes(array$t,array$Wk);abstract
function
printSelectionColumns(array$M,array$c);abstract
function
printSelectionSearch(array$Z,array$c,array$t);abstract
function
printSelectionOrder(array$D,array$c,array$t);abstract
function
printSelectionLimit($w);abstract
function
printSelectionLength($ql);abstract
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
detectJson($Ld,&$Y,$Mi=null){if(is_array($Y)){$Xd=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($this->config->isJsonValuesAutoFormat()?JSON_PRETTY_PRINT:0);$Y=json_encode($Y,$Xd);return
true;}$Xd=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($Mi?JSON_PRETTY_PRINT:0);if(preg_match('~^jsonb?$~',$Ld)){if($Y!=null&&$Mi!==null&&$this->config->isJsonValuesAutoFormat())$Y=json_encode(json_decode($Y),$Xd);return
true;}if(!$this->config->isJsonValuesDetection())return
false;if(is_string($Y)&&$Y!=""&&preg_match('~varchar|text|character varying|String|keyword~',$Ld)&&($Y[0]=="{"||$Y[0]=="[")&&($Cf=json_decode($Y))){if($Mi!==null&&$this->config->isJsonValuesAutoFormat())$Y=json_encode($Cf,$Xd);return
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
sendDumpHeaders($Te,$Vg=false);function
dumpDatabase($nc){}abstract
function
dumpTable($Q,$Hk,$tm=0);abstract
function
dumpData($Q,$Hk,$H);abstract
function
getImportFilePath();abstract
function
printDatabaseMenu();function
printNavigation($Pg){$Vf=isset($_COOKIE["neo_version"])?$_COOKIE["neo_version"]:null;echo"<div class='header'>\n",$this->admin->getServiceTitle()."\n";if($Pg!="auth"){echo"<span class='version'>",h(preg_replace('~\\.0(-|$)~','$1',VERSION));if($this->config->isVersionVerificationEnabled()&&$Vf&&version_compare(VERSION,$Vf)<0)echo"<a id='version' class='version-badge' href='https://www.adminneo.org/download' ".target_blank()." title='".h($Vf)."'>",icon_solo("asterisk"),"</a>";echo"</span>\n";if($this->config->isVersionVerificationEnabled()&&!$Vf)echo
script("verifyVersion('".js_escape(ME)."', '".get_token()."');");}echo"</div>\n";}abstract
function
printDatabaseSwitcher($Pg);function
printTablesFilter(){echo"<div class='tables-filter jsonly'>"."<input id='tables-filter' type='search' class='input' autocomplete='off' placeholder='".lang(8)."'>".script("initTablesFilter(".json_encode($this->admin->getDatabase(),JSON_HEX_TAG).");")."</div>\n";}abstract
function
printTableList(array$S);function
getSettingsRows($ye){$O=[];if($ye==1){$C=get_language_options();if($C)$O["lang"]="<tr><th id='label-language'>".lang(9)."</th>"."<td>".html_select("lang",get_language_options(),Locale::get()->getLanguage(),"","label-language")."</td></tr>\n";$C=[""=>lang(10),Settings::$ColorSchemeLight=>lang(11),Settings::$ColorSchemeDark=>lang(12)];$O["colorScheme"]="<tr><th>".lang(13)."</th>"."<td>".html_radios("colorScheme",$C,($ra=$this->settings->getParameter("colorScheme"))!==null?$ra:"")."</td></tr>\n";}elseif($ye==2){$C=[""=>lang(14),true=>lang(15),false=>lang(16),];$i=$C[$this->config->isRelationLinks()];$C[""].=" ($i)";$O["relationLinks"]="<tr><th>".lang(17)."</th>"."<td>".html_radios("relationLinks",$C,($ra=$this->settings->getParameter("relationLinks"))!==null?$ra:"")."<span class='input-hint'>".lang(18)."</span>"."</td></tr>\n";$i=$this->config->getRecordsPerPage();$C=[""=>lang(14)." ($i)","20","30","50","70","100",];$O["recordsPerPage"]="<tr><th id='label-records'>".lang(19)."</th>"."<td>".html_select("recordsPerPage",$C,($ra=$this->settings->getParameter("recordsPerPage"))!==null?$ra:"","","label-records")."<span class='input-hint'>".lang(20)."</span>"."</td></tr>\n";$i=($ra=$this->config->getEnumAsSelectThreshold())!==null?$ra:lang(21);$C=[""=>lang(14)." ($i)",-1=>lang(21),0=>lang(22),3=>lang(23,3),5=>lang(23,5),10=>lang(23,10),20=>lang(23,20),];$O["enumAsSelectThreshold"]="<tr><th id='label-enum'>".lang(24)."</th>"."<td>".html_select("enumAsSelectThreshold",$C,($ra=$this->settings->getParameter("enumAsSelectThreshold"))!==null?$ra:"","","label-enum",true)."<span class='input-hint'>".lang(25)."</span>"."</td></tr>\n";}return$O;}abstract
function
getForeignColumnInfo(array$ce,$b);}class
Pluginer{private
static$InternalMethods=["inject"=>true,"getConfig"=>true,];private
static$AppendMethods=["getErrors"=>true,"getFieldFunctions"=>true,"getDumpOutputs"=>true,"getDumpFormats"=>true,"getSettingsRows"=>true,];private$plugins;private$hooks=[];function
__construct(Origin$ya,array$Bi){$this->plugins=$Bi;foreach(get_class_methods('\AdminNeo\Origin')as$Ng){$this->hooks[$Ng]=[];if(!(isset(self::$InternalMethods[$Ng])?self::$InternalMethods[$Ng]:false)){foreach($Bi
as$Ai){if(method_exists($Ai,$Ng))$this->hooks[$Ng][]=$Ai;}}if(isset(self::$AppendMethods[$Ng])?self::$AppendMethods[$Ng]:false)array_unshift($this->hooks[$Ng],$ya);else$this->hooks[$Ng][]=$ya;}}function
getPlugins(){return$this->plugins;}function
__call($A,array$gi){$Ha=isset(self::$AppendMethods[$A])?self::$AppendMethods[$A]:false;$I=$Ha?[]:null;assert(isset($this->hooks[$A]),"Calling unknown plugin method: $A");foreach($this->hooks[$A]as$Ai){$Y=call_user_func_array([$Ai,$A],$gi);if($Y!==null){if($Ha)$I+=$Y;else
return$Y;}}return$I;}function
updateCspHeader(array&$fc){$this->__call(__FUNCTION__,[&$fc]);}function
detectJson($Ld,&$Y,$Mi=null){return$this->__call(__FUNCTION__,[$Ld,&$Y,$Mi]);}}class
Admin
extends
Origin{function
getOperators(){return
Driver::get()->getOperators();}function
getServiceTitle(){return"<a href='".h(HOME_URL)."'><svg role='img' class='logo' width='133' height='28'><desc>AdminNeo</desc><use href='".link_files("logo.svg",[])."#logo'/></svg></a>";}function
getDatabase(){return
DB;}function
getQueryTimeout(){return
2;}function
printToHead(){echo"<link rel='stylesheet' href='",link_files("jush.css",[]),"'>";if(!$this->admin->isLightModeForced())echo"<link rel='stylesheet' ".(!$this->admin->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("jush-dark.css",[]),"'>\n";echo
script_src(link_files("jush.js",[]),true);}function
printLoginForm(){$Rc=Drivers::getList();$dk=$this->config->getServerPairs($Rc);$N=SERVER?:$this->config->getDefaultServer();echo"<table class='box box-light'>\n";if($dk)echo$this->admin->getLoginFormRow('server',lang(5),"<select name='auth[server]'>".optionlist($dk,$N,true)."</select>");else{$Pc=DRIVER?:$this->config->getDefaultDriver($Rc);if(count($Rc)>1)echo$this->admin->getLoginFormRow('driver',lang(26),html_select("auth[driver]",$Rc,$Pc).script("initLoginDriver(qsl('select'));",""));else
echo$this->admin->getLoginFormRow('driver','',input_hidden("auth[driver]",$Pc));echo$this->admin->getLoginFormRow('server',lang(5),"<input class='input' name='auth[server]' value='".h($N)."' title='".lang(27)."' placeholder='localhost' autocapitalize='off'>");}echo$this->admin->getLoginFormRow('username',lang(28),'<input class="input" name="auth[username]" id="username" value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'),$this->admin->getLoginFormRow('password',lang(29),'<input type="password" class="input" name="auth[password]" autocomplete="current-password">');if(!$dk){$nc=isset($_GET["db"])?$_GET["db"]:$this->config->getDefaultDatabase();echo$this->admin->getLoginFormRow('db',lang(30),'<input class="input" name="auth[db]" value="'.h($nc).'" autocapitalize="off">');}echo"</table>\n","<p>","<input type='submit' class='button default' value='".lang(31)."'>",checkbox("auth[permanent]",1,$_COOKIE["neo_permanent"],lang(32)),"</p>\n";}function
getFieldName(array$k,$D=0){$U=$k["full_type"].($k["null"]?" NULL":"");$Kb=$k["comment"];$Zj=$U&&$Kb!=""?": ":"";return'<span title="'.h($U.$Zj.$Kb).'">'.h($k["field"]).'</span>';}function
printTableMenu(array$Wk,$lf){echo'<p class="links top-tabs">';$ig=[];$Vj=($this->settings->isSelectionPreferred()&&!$this->settings->isNavigationReversed())||(!$this->settings->isSelectionPreferred()&&$this->settings->isNavigationReversed());if($Vj)$ig["select"]=[lang(33),"data"];if(support("table")||support("indexes"))$ig["table"]=[lang(34),"structure"];if(!$Vj)$ig["select"]=[lang(33),"data"];$Q=$Wk["Name"];$zf=false;if(support("table")){$zf=is_view($Wk);if(!$zf){if($Q!="")$ig["create"]=[lang(35),"edit"];}elseif(support("view"))$ig["view"]=[lang(36),"edit"];}if($lf!==null)$ig["edit"]=[lang(7),"item-add"];$gi=$lf?"&".http_build_query($lf):"";foreach($ig
as$u=>$X)echo" <a href='",h(ME),"$u=",urlencode($Q),($u=="edit"?$gi:""),"'",bold(isset($_GET[$u])),">",icon($X[1]),"$X[0]</a>";echo
doc_link([DIALECT=>Driver::get()->tableHelp($Q,$zf)],icon("help").lang(37)),"\n";}function
formatSelectQuery($H,$Ak,$Fd=false){$Nk=support("sql");$_m=!$Fd?Driver::get()->warnings():null;if($Nk)$H
.=";";$Qk=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$J="<pre><code class='jush-$Qk'>".h(str_replace("\n"," ",$H))."</code></pre>\n";$J
.="<p class='links'>";if($Nk)$J
.="<a href='".h(ME)."sql=".urlencode($H)."'>".icon("edit").lang(38)."</a>";if($_m)$J
.="<a href='#warnings' class='toggle'>".lang(39).icon_chevron_down()."</a>";$J
.=" <span class='time'>(".format_time($Ak).")</span>";$J
.="</p>\n";if($_m){$J
.=script("initToggles(qsl('p'));");$J
.="<div id='warnings' class='warnings hidden'>\n$_m\n</div>\n";}return$J;}function
formatMessageQuery($H,$vl,$Fd=false){restart_session();$Ke=&get_session("queries");if(!isset($Ke[$_GET["db"]]))$Ke[$_GET["db"]]=[];if(strlen($H)>1e6)$H=preg_replace('~[\x80-\xFF]+$~','',substr($H,0,1e6))."\n…";$Ke[$_GET["db"]][]=[$H,time(),$vl];$Nk=support("sql");$_m=!$Fd?Driver::get()->warnings():null;$yk="sql-".count($Ke[$_GET["db"]]);$Am="warnings-".count($Ke[$_GET["db"]]);$J=" ";if($_m)$J
.="<a href='#$Am' class='toggle'>".lang(39).icon_chevron_down()."</a>, ";$Yi=support("sql")?lang(40):lang(41);$J
.="<a href='#$yk' class='toggle'>$Yi".icon_chevron_down()."</a>";$J
.=" <span class='time'>".@date("H:i:s")."</span>\n";if($_m)$J
.="<div id='$Am' class='warnings hidden'>\n$_m</div>\n";$J
.="<div id='$yk' class='hidden'>\n";$Qk=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$J
.="<pre><code class='jush-$Qk'>".truncate_utf8($H,1000)."</code></pre>\n";$J
.="<p class='links'>";if($Nk)$J
.="<a href='".h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($Ke[$_GET["db"]])-1))."'>".icon("edit").lang(38)."</a>";if($vl)$J
.=" <span class='time'>($vl)</span>";$J
.="</p>\n";$J
.="</div>\n";return$J;}function
formatSqlCommandQuery($H){if(preg_match('~^DELIMITER\s~i',$H))return"";return
truncate_utf8($H,1000);}function
getTableDescriptionFieldName($Q){return"";}function
fillForeignDescriptions(array$L,array$ce){return$L;}function
formatSelectionValue($X,$x,$k,$Zh){if($X===null)$pl="<i>NULL</i>";elseif(!$k)$pl=$X;elseif(preg_match("~char|binary|boolean~",$k["type"])&&!preg_match("~var~",$k["type"]))$pl="<code>$X</code>";elseif(is_blob($k)&&!is_utf8($X))$pl="<i>".lang(42,strlen($Zh))."</i>";elseif($this->admin->detectJson($k["full_type"],$Zh))$pl="<code class='jush-json'>$X</code>";else$pl=$X;if($x)$pl="<a href='".h($x)."'".(is_web_url($x)?target_blank():"").">$pl</a>";return$pl;}function
formatFieldValue($Y,array$k){return$Y;}function
printTableStructure(array$l){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th>",lang(43),"</th>","<td>",lang(44),"</td>","<td>",lang(45),"</td>";if(support("comment"))echo"<td>",lang(46),"</td>";echo"</tr></thead>\n";$im=Driver::get()->getUserTypes();foreach($l
as$k){echo"<tr>","<th>",h($k["field"]),"</th>","<td>";$U=h($k["full_type"]);if(in_array($U,$im))echo"<a href='".h(ME.'type='.urlencode($U))."'>$U</a>";else
echo$U;if($k["null"])echo" <i>NULL</i>";if($k["auto_increment"])echo" <i>".lang(47)."</i>";$i=h($k["default"]);if(isset($k["default"]))echo" <span title='".lang(48)."'>[<b>",$k["generated"]?"<code class='jush-".DIALECT."'>$i</code>":$i,"</b>]</span>";echo"</td>","<td>",h($k["collation"]),"</td>";if(support("comment"))echo"<td>",$this->admin->formatComment($k["comment"]),"</td>";echo"\n";}echo"</table>\n","</div>\n";}function
printTablePartitions(array$li){$nk=isset($li["partition_names"]);echo"<p>","<code class='jush-".DIALECT."'>BY {$li["partition_by"]} ({$li["partition"]})</code>";if(!$nk&&isset($li["partitions"]))echo" ".lang(49).": ".h($li["partitions"]);echo"</p>";if($nk){echo"<table>\n","<thead><tr><th>".lang(50)."</th><td>".lang(51)."</td></tr></thead>\n";foreach($li["partition_names"]as$u=>$A){echo"<tr><th>";if(DIALECT=="pgsql")echo"<a href='",h(ME."table=".urlencode($A)),"'>";echo
h($A);if(DIALECT=="pgsql")echo"</a>";echo"</th><td>".h($li["partition_values"][$u])."\n";}echo"</table>\n";}}function
printRelatedTables(array$S){echo"<ul class='links'>\n";foreach($S
as$K){$x=preg_replace('~ns=[^&]*~',"ns=".urlencode($K["ns"]),ME);echo"<li><a href='",h($x."table=".urlencode($K["table"])),"'>",icon("structure");if($K["ns"]!=$_GET["ns"])echo"<b>".h($K["ns"])."</b>.";echo
h($K["table"]),"</a>";}echo"</ul>\n";}function
printTableIndexes(array$t,array$Wk){$sc=first(Driver::get()->getIndexAlgorithms($Wk));$ji=false;foreach($t
as$s){if(isset($s["partial"])?$s["partial"]:false){$ji=true;break;}}echo"<table>\n","<thead><tr>","<th>",lang(44),"</th>","<td>",lang(52)," (",lang(53),")</td>";if($ji)echo"<td>",lang(54),"</td>";echo"</tr></thead>\n";foreach($t
as$A=>$s){ksort($s["columns"]);$Oi=[];foreach($s["columns"]as$u=>$X)$Oi[]="<i>".h($X)."</i>".($s["lengths"][$u]?"(".h($s["lengths"][$u]).")":"").($s["descs"][$u]?" DESC":"");echo"<tr title='",h($A),"'>","<th>",h($s["type"]);if(isset($s['algorithm'])&&$s['algorithm']!=$sc)echo" (",h($s['algorithm']),")";echo"</th>","<td>",implode(", ",$Oi),"</td>";if($ji){echo"<td>";if($s['partial'])echo"<code class='jush-",DIALECT,"'>WHERE ",h($s['partial']),"</code>";echo"</td>";}echo"</tr>\n";}echo"</table>\n";}function
printSelectionColumns(array$M,array$c){print_fieldset_start("select",lang(55),"columns",(bool)$M,true);$M[""]=[];$q=0;foreach($M
as$u=>$X){$X=isset($_GET["columns"][$u])?$_GET["columns"][$u]:[];$b=select_input("name='columns[$q][col]'",$c,isset($X["col"])?$X["col"]:null,$u!==""?"selectFieldChange":"selectAddRow");echo"<div ",($u!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly");if(Driver::get()->getFunctions()||Driver::get()->getGrouping())echo
html_select("columns[$q][fun]",[-1=>""]+array_filter([lang(56)=>Driver::get()->getFunctions(),lang(57)=>Driver::get()->getGrouping()]),isset($X["fun"])?$X["fun"]:null),help_script_command("value && value.replace(/ |\$/, '(') + ')'",true),script("qsl('select').onchange = (event) => { ".($u!==""?"":" qsl('select, input:not(.remove)', event.target.parentNode).onchange();")." };",""),"($b)";else
echo$b;echo" <button class='button light remove jsonly' title='",lang(58),"'>",icon_solo("remove"),"</button>",script("qsl('#fieldset-select .remove').onclick = selectRemoveRow;",""),"</div>\n";$q++;}print_fieldset_end("select",true);}function
printSelectionSearch(array$Z,array$c,array$t){print_fieldset_start("search",lang(59),"search",(bool)$Z);foreach($t
as$q=>$s){if($s["type"]=="FULLTEXT"){echo"<div>(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$s["columns"]))."</i>) AGAINST","<input type='text' class='input' name='fulltext[$q]' value='".h(isset($_GET["fulltext"][$q])?$_GET["fulltext"][$q]:null)."'>",script("qsl('input').oninput = selectFieldChange;","");if(DIALECT=='sql')echo
checkbox("boolean[$q]",1,isset($_GET["boolean"][$q]),"BOOL");echo"</div>\n";}}$mb="this.parentNode.firstChild.onchange();";foreach(array_merge((array)$_GET["where"],[[]])as$q=>$X){if(!$X||("$X[col]$X[val]"!=""&&in_array($X["op"],$this->getOperators())))echo"<div>",select_input(" name='where[$q][col]'",$c,$X["col"],($X?"selectFieldChange":"selectAddRow"),"(".lang(60).")"),html_select("where[$q][op]",$this->getOperators(),$X["op"],$mb),"<input type='text' class='input' name='where[$q][val]' value='".h($X["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $mb }, onkeydown: selectSearchKeydown});","")," <button class='button light remove jsonly' title='".lang(58)."'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-search .remove").onclick = selectRemoveRow;',""),"</div>\n";}print_fieldset_end("search");}function
printSelectionOrder(array$D,array$c,array$t){print_fieldset_start("sort",lang(61),"sort",(bool)$D,true);$_GET["order"][""]="";$q=0;foreach((array)$_GET["order"]as$u=>$X){if($u!=""&&$X=="")continue;echo"<div ",($u!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly"),select_input("name='order[$q]'",$c,$X,$u!==""?"selectFieldChange":"selectAddRow")," ",checkbox("desc[$q]",1,isset($_GET["desc"][$u]),lang(62))," <button class='button light remove jsonly' title='",lang(58),"'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-sort .remove").onclick = selectRemoveRow;',""),"</div>\n";$q++;}print_fieldset_end("sort",true);}function
printSelectionLimit($w){echo"<fieldset><legend>".lang(63)."</legend><div class='fieldset-content'>","<input type='number' name='limit' class='input size' value='$w'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
printSelectionLength($ql){if($ql!==null)echo"<fieldset><legend>".lang(64)."</legend><div class='fieldset-content'>","<input type='number' name='text_length' class='input size' value='".h($ql)."'>","</div></fieldset>\n";}function
printSelectionAction(array$t){echo"<fieldset><legend>".lang(65)."</legend><div class='fieldset-content'>","<input type='submit' class='button' value='".lang(55)."'>"," <span id='noindex' title='".lang(66)."'></span>","<script".nonce().">\n";$c=new
stdClass();foreach($t
as$s){$hc=reset($s["columns"]);if($s["type"]!="FULLTEXT"&&$hc)$c->$hc=null;}echo"const indexColumns = ".json_encode($c,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG).";\n","selectFieldChange.call(gid('form')['select']);\n","</script>\n","</div></fieldset>\n";}function
processSelectionColumns(array$c,array$t){$M=[];$we=[];foreach((array)$_GET["columns"]as$u=>$X){if($X["fun"]=="count"||($X["col"]!=""&&(!$X["fun"]||in_array($X["fun"],Driver::get()->getFunctions())||in_array($X["fun"],Driver::get()->getGrouping())))){$M[$u]=apply_sql_function($X["fun"],($X["col"]!=""?idf_escape($X["col"]):"*"));if(!in_array($X["fun"],Driver::get()->getGrouping()))$we[]=$M[$u];}}return[$M,$we];}function
processSelectionSearch(array$l,array$t){$J=[];foreach($t
as$q=>$s){if($s["type"]=="FULLTEXT"&&isset($_GET["fulltext"])&&$_GET["fulltext"][$q]!="")$J[]="MATCH (".implode(", ",array_map('AdminNeo\idf_escape',$s["columns"])).") AGAINST (".q($_GET["fulltext"][$q]).(isset($_GET["boolean"][$q])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$Z){$_b=$Z["col"];$Eh=$Z["op"];$X=$Z["val"];if("$_b$X"!=""&&in_array($Eh,$this->getOperators())){$Qb=[];foreach(($_b!=""?[$_b=>$l[$_b]]:$l)as$A=>$k){$Ki="";$Pb=" $Eh";$uh=DIALECT=="pgsql"&&$Eh=="="&&$k["type"]=="oid";if($uh)$Pb
.=" ".$this->admin->processFieldInput($k,$X)."::regproc";elseif(preg_match('~IN$~',$Eh)){$Ye=process_length($X);$Pb
.=" ".($Ye!=""?$Ye:"(NULL)");}elseif($Eh=="SQL")$Pb=" $X";elseif(preg_match('~^(I?LIKE) %%$~',$Eh,$z))$Pb=" $z[1] ".$this->admin->processFieldInput($k,"%$X%");elseif($Eh=="FIND_IN_SET"){$Ki="$Eh(".q($X).", ";$Pb=")";}elseif(!preg_match('~NULL$~',$Eh))$Pb
.=" ".$this->admin->processFieldInput($k,$X);if($_b!=""||(isset($k["privileges"]["where"])&&(preg_match('~^[-\d.'.(preg_match('~IN$~',$Eh)?',':'').']+$~',$X)||!preg_match('~'.number_type().'|bit~',$k["type"]))&&(!preg_match("~[\x80-\xFF]~",$X)||preg_match('~char|text|enum|set~',$k["type"]))&&(!preg_match('~date|timestamp~',$k["type"])||preg_match('~^\d+-\d+-\d+~',$X))&&(!preg_match('~^elastic~',DRIVER)||$k["type"]!="boolean"||preg_match('~true|false~',$X))&&(!preg_match('~^elastic~',DRIVER)||strpos($Eh,"regexp")===false||preg_match('~text|keyword~',$k["type"])))){if($uh)$Qb[]=$Ki.idf_escape($A).$Pb;else$Qb[]=$Ki.Driver::get()->convertSearch(idf_escape($A),$Z,$k).$Pb;}}if(count($Qb)==1)$J[]=$Qb[0];elseif($Qb)$J[]="(".implode(" OR ",$Qb).")";else$J[]="1 = 0";}}return$J;}function
processSelectionOrder(array$l,array$t){$J=[];foreach((array)$_GET["order"]as$u=>$X){if($X!="")$J[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$X)?$X:idf_escape($X)).(isset($_GET["desc"][$u])?" DESC":"");}return$J;}function
processSelectionLength(){return
isset($_GET["text_length"])?$_GET["text_length"]:"100";}function
getFieldFunctions(array$k){$J=($k["null"]?"NULL/":"");$Zl=isset($_GET["select"])||where($_GET);foreach([Driver::get()->getInsertFunctions(),Driver::get()->getEditFunctions()]as$u=>$oe){if(!$u||(!isset($_GET["call"])&&$Zl)){foreach($oe
as$vi=>$X){if(!$vi||preg_match("~$vi~",$k["type"]))$J
.="/$X";}}if($u&&$oe&&!preg_match('~enum|set|bool~',$k["type"])&&!is_blob($k))$J
.="/SQL";}if($k["auto_increment"]&&!$Zl)$J=lang(47);return
explode("/",$J);}function
getFieldInput($Q,array$k,$Ma,$Y,$p){return"";}function
processFieldInput(array$k,$Y,$p=""){if($p=="SQL")return$Y;if(isset($k["full_type"]))$this->admin->detectJson($k["full_type"],$Y,false);$A=$k["field"];$J=q($Y);if(preg_match('~^(now|getdate|uuid)$~',$p))$J="$p()";elseif(preg_match('~^current_(date|timestamp)$~',$p))$J=$p;elseif(preg_match('~^([+-]|\|\|)$~',$p))$J=idf_escape($A)." $p $J";elseif(preg_match('~^[+-] interval$~',$p))$J=idf_escape($A)." $p ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$Y)&&DIALECT!="pgsql"?$Y:$J);elseif(preg_match('~^(addtime|subtime|concat)$~',$p))$J="$p(".idf_escape($A).", $J)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$p))$J="$p($J)";elseif($k["type"]=="boolean"&&DIALECT=="elastic")$J=$J=="0"?"false":"true";return
unconvert_field($k,$J);}function
getDumpOutputs(){$ci=['file'=>lang(67),'text'=>lang(68),];if(function_exists('gzencode'))$ci['gz']='gzip';return$ci;}function
getDumpFormats(){return(support("dump")?['sql'=>'SQL']:[])+['csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV'];}function
sendDumpHeaders($Te,$Vg=false){$bi=$_POST["output"];$Bd=(str_contains($_POST["format"],"sql")?"sql":($Vg?"tar":"csv"));if($bi=="gz"){header("Content-Type: application/x-gzip");ob_start(function($Ek){return
gzencode($Ek);},1e6);}elseif($Bd=="tar")header("Content-Type: application/x-tar");elseif($Bd=="sql"||$bi=="text")header("Content-Type: text/plain; charset=utf-8");else
header("Content-Type: text/csv; charset=utf-8");return$Bd;}function
dumpTable($Q,$Hk,$tm=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($Hk)dump_csv(array_keys(fields($Q)));}else{if($tm==2){$l=[];foreach(fields($Q)as$A=>$k)$l[]=idf_escape($A)." $k[full_type]";$bc="CREATE TABLE ".table($Q)." (".implode(", ",$l).")";}else$bc=create_sql($Q,$_POST["auto_increment"],$Hk);set_utf8mb4($bc);if($Hk&&$bc){if($Hk=="DROP+CREATE"||$tm==1)echo"DROP ".($tm==2?"VIEW":"TABLE")." IF EXISTS ".table($Q).";\n";if($tm==1)$bc=remove_definer($bc);echo"$bc;\n\n";}}}function
dumpData($Q,$Hk,$H){if($Hk){$wg=(DIALECT=="sqlite"?0:1048576);$l=[];$Ue=false;if($_POST["format"]=="sql"){if($Hk=="TRUNCATE+INSERT")echo
truncate_sql($Q).";\n";$l=fields($Q);if(DIALECT=="mssql"){foreach($l
as$k){if($k["auto_increment"]){echo"SET IDENTITY_INSERT ".table($Q)." ON;\n";$Ue=true;break;}}}}$I=Connection::get()->query($H,1);if($I){$jf="";$eb="";$If=[];$qe=[];$Kk="";$ac=0;while($K=($Q!=''?$I->fetchAssoc():$I->fetchRow())){if(!$If){$nm=[];foreach($K
as$X){$k=$I->fetchField();if(!empty($l[$k->name]['generated'])){$qe[$k->name]=true;continue;}$If[]=$k->name;$u=idf_escape($k->name);$nm[]="$u = VALUES($u)";}$Kk=($Hk=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$nm):"").";\n";}if($_POST["format"]!="sql"){if($Hk=="table"){dump_csv($If);$Hk="INSERT";}dump_csv($K);}else{if(!$jf)$jf="INSERT INTO ".table($Q)." (".implode(", ",array_map('AdminNeo\idf_escape',$If)).") VALUES";foreach($K
as$u=>$X){if(isset($qe[$u])){unset($K[$u]);continue;}$k=$l[$u];$K[$u]=($X===null?"NULL":($X===false?0:unconvert_field($k,preg_match(number_type(),$k["type"])&&!preg_match('~\[~',$k["full_type"])&&is_numeric($X)?$X:(!is_blob($k)||is_utf8($X)?q($X):Driver::get()->quoteBinary($X)))));}$Dj=($wg?"\n":" ")."(".implode(",\t",$K).")";if(!$eb)$eb=$jf.$Dj;elseif(DIALECT=="mssql"?$ac%1000!=0:strlen($eb)+4+strlen($Dj)+strlen($Kk)<$wg)$eb
.=",$Dj";else{echo$eb.$Kk;$eb=$jf.$Dj;}}$ac++;}if($eb)echo$eb.$Kk;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",Connection::get()->getError())."\n";if($Ue)echo"SET IDENTITY_INSERT ".table($Q)." OFF;\n";}}function
getImportFilePath(){return"adminneo.sql";}function
printDatabaseMenu(){echo"<p class='links top-links'>\n";$nh=isset($_GET["ns"])?$_GET["ns"]:null;if($nh==""&&support("database"))echo'<a href="',h(ME),'database=">',icon("edit"),lang(69),"</a>\n";if($nh!=""&&support("scheme"))echo"<a href='",h(ME),"scheme='>",icon("edit"),lang(70),"</a>\n";if($nh!=="")echo'<a href="',h(ME),'schema=">',icon("schema"),lang(71),"</a>\n";if(support("privileges"))echo"<a href='",h(ME),"privileges='>",icon("users"),lang(72),"</a>\n";echo"</p>\n";}function
printNavigation($Pg){parent::printNavigation($Pg);if($Pg=="auth"){$bi="";foreach((array)$_SESSION["pwds"]as$pm=>$hk){foreach($hk
as$N=>$jm){foreach($jm
as$V=>$F){if($F!==null){$qc=$_SESSION["db"][$pm][$N][$V];foreach(($qc?array_keys($qc):[""])as$h){$ek=$this->admin->getServerName($N,false);$T=h(get_driver_name($pm,$N)).($V!=""||$ek!=""?" - ":"").h($V).($V!=""&&$ek!=""?"@":"").h($ek).($h!=""?h(" - $h"):"");$bi
.="<li><a href='".h(auth_url($pm,$N,$V,$h))."' class='primary' title='$T'>$T</a></li>\n";}}}}}if($bi)echo"<nav id='logins'><menu>\n$bi</menu></nav>\n";}else{$this->admin->printDatabaseSwitcher($Pg);$va=[];if(DB==""||!$Pg){if(support("sql")){$va[]="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".icon("command").lang(40)."</a>";$va[]="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".icon("import").lang(73)."</a>";}$va[]="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".icon("export").lang(74)."</a>";}if(DB=="")$va[]='<a href="'.h(ME).'database="'.bold($_GET["database"]==="").">".icon("database-add").lang(75)."</a>\n";if(DB!=""&&$_GET["ns"]===""&&!$Pg)$va[]='<a href="'.h(ME).'scheme="'.bold($_GET["scheme"]==="").">".icon("database-add").lang(76)."</a>\n";if(DB!=""&&$_GET["ns"]!==""&&!$Pg)$va[]='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".icon("table-add").lang(77)."</a>\n";if($va)echo"<p class='links'>".implode("\n",$va)."</p>";$S=[];if($_GET["ns"]!==""&&!$Pg&&DB!=""){Connection::get()->selectDatabase(DB);$S=table_status('',true);}if($_GET["ns"]!==""&&!$Pg&&DB!=""){if($S){$this->admin->printTablesFilter();$this->admin->printTableList($S);}else
echo"<p class='message'>".lang(78)."</p>\n";}if(support("sql")||DIALECT=="elastic"||DIALECT=="mongo"){echo"<script".nonce().">\n";if(support("sql")&&$S){$ig=[];foreach($S
as$Q=>$U)$ig[]=js_escape_re($Q);$Vk=support("table")&&!$this->config->isSelectionPreferred()?"table":"select";echo"window.jushLinks = { ".DIALECT.": {\n",js_escape_key(ME.$Vk.'=$&'),': /\b(?<!\$)('.implode('|',$ig).')(?!\$)\b/g';if(support('routine')){foreach(routines()as$K)echo",\n",js_escape_key(ME.'function='.urlencode($K["SPECIFIC_NAME"]).'&name=$&'),': /\b'.js_escape_re($K["ROUTINE_NAME"]).'(?=["`\]]?\()/g';}echo"\n}};\n";foreach(["bac","bra","sqlite_quo","mssql_bra"]as$X)echo"jushLinks.$X = jushLinks.".DIALECT.";\n";}if(DIALECT!="elastic"&&DIALECT!="mongo"&&$this->getConfig()->isSqlAutocompletionEnabled()&&(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"]))){$fl=array_fill_keys(array_keys($S),[]);foreach(Driver::get()->getAllFields()as$Q=>$l){foreach($l
as$k)$fl[$Q][]=$k["field"];}echo"window.addEventListener('DOMContentLoaded', () => { autocompletion = jush.autocompleteSql('".idf_escape("")."', ".json_encode($fl,JSON_HEX_TAG)."); });\n";}echo"</script>\n";}echo
script("let autocompletion;\nwindow.addEventListener('DOMContentLoaded', () => { initSyntaxHighlighting('".js_escape(doc_version())."', '".js_escape(Connection::get()->getFlavor())."', autocompletion); });");}}function
printDatabaseSwitcher($Pg){$g=$this->admin->getDatabases();if(!$g&&DIALECT!="sqlite")return;echo"<div class='db-selector'><form action=''>";hidden_fields_get();echo"<div>";if($g)echo"<select id='database-select' name='db' title='",lang(30),"'>".optionlist([""=>"(".lang(79).")"]+$g,DB)."</select>".script("mixin(gid('database-select'), {onmousedown: dbMouseDown, onchange: dbChange});");else
echo"<input id='database-select' class='input' name='db' value='".h(DB)."' title='",lang(30),"' autocapitalize='off'>\n";echo"<input type='submit' value='".lang(80)."' class='button ".($g?"hidden":"")."'>\n","</div>";foreach(["import","sql","schema","dump","privileges"]as$X){if(isset($_GET[$X])){echo
input_hidden($X);break;}}echo"</form></div>\n";}function
printTableList(array$S){$Wc=$this->settings->isNavigationDual()||$this->settings->isNavigationHover();$Eg=($Wc?"class='dual".($this->settings->isNavigationHover()?" hover":"")."'":($this->settings->isNavigationReversed()?"class='reversed'":""));echo"<nav id='tables'><menu $Eg>";foreach($S
as$Q=>$P){$Q="$Q";$A=$this->admin->getTableName($P);if($A==""||(isset($P["Partition"])?$P["Partition"]:false))continue;echo"<li>";$wa=in_array($Q,[$_GET["table"],$_GET["select"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"]]);$yb="primary".(is_view($P)?" view":"");$Ok=support("table")||support("indexes");$Sj=h(ME)."select=".urlencode($Q);$Xk=h(ME)."table=".urlencode($Q);if($this->settings->isSelectionPreferred()){if($this->settings->isNavigationReversed()&&$Ok)echo" <a href='$Xk' title='",lang(34),"' class='secondary'>",icon("structure"),"</a>";echo"<a href='$Sj'",bold($wa,$yb)," data-primary='true' title='$A'>$A</a>";if($Wc&&$Ok)echo" <a href='$Xk' title='",lang(34),"' class='secondary'>",icon_solo("structure"),"</a>";}else{if($this->settings->isNavigationReversed())echo" <a href='$Sj' title='",lang(33),"' class='secondary'>",icon("data"),"</a>";if($Ok)echo"<a href='$Xk'",bold($wa,$yb)," data-primary='true' title='$A'>$A</a>";else
echo"<span data-primary='true'",bold($wa,$yb),">$A</span>";if($Wc)echo" <a href='$Sj' title='",lang(33),"' class='secondary'>",icon_solo("data"),"</a>";}echo"</li>\n";}echo"</menu></nav>\n",script("initTablesList(".json_encode($this->admin->getDatabase(),JSON_HEX_TAG).");");}function
getSettingsRows($ye){$O=parent::getSettingsRows($ye);if($ye==1){$C=[""=>lang(14),Config::$NavigationSimple=>lang(81),Config::$NavigationDual=>lang(82),Config::$NavigationHover=>lang(83),Config::$NavigationReversed=>lang(84)];$i=$C[$this->config->getNavigationMode()];$C[""].=" ($i)";$O["navigationMode"]="<tr><th>".lang(85)."</th>"."<td>".html_radios("navigationMode",$C,($ra=$this->settings->getParameter("navigationMode"))!==null?$ra:"")."<span class='input-hint'>".lang(86)."</span>"."</td></tr>\n";$C=[""=>lang(14),0=>lang(34),1=>lang(33),];$i=$C[$this->config->isSelectionPreferred()?1:0];$C[""].=" ($i)";$O["preferSelection"]="<tr><th id='label-links'>".lang(87)."</th>"."<td>".html_select("preferSelection",$C,($ra=$this->settings->getParameter("preferSelection"))!==null?$ra:"","","label-links",true)."<span class='input-hint'>".lang(88)."</span>"."</td></tr>\n";}return$O;}function
getForeignColumnInfo(array$ce,$b){return
null;}}class
TmpFile{private$handler;private$size;function
__construct(){$this->handler=tmpfile();}function
getSize(){return$this->size;}function
write($Wb){if(!$this->handler)return;$this->size+=strlen($Wb);fwrite($this->handler,$Wb);}function
send(){if(!$this->handler)return;fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}function
print_select_result(Result$I,$e=null,array$Th=[],$w=0){$ig=[];$t=[];$c=[];$ab=[];$Pl=[];$J=[];for($q=0;(!$w||$q<$w)&&($K=$I->fetchRow());$q++){if(!$q){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>";for($Bf=0;$Bf<count($K);$Bf++){$k=$I->fetchField();if(!$k){echo"<th></th>";continue;}$A=$k->name;$Sh=isset($k->orgtable)?$k->orgtable:"";$Rh=isset($k->orgname)?$k->orgname:$A;if(isset($k->table))$J[$k->table]=$Sh;if($Th&&DIALECT=="sql")$ig[$Bf]=($A=="table"?"table=":($A=="possible_keys"?"indexes=":null));elseif($Sh!=""){if(!isset($t[$Sh])){$t[$Sh]=[];foreach(indexes($Sh,$e)as$s){if($s["type"]=="PRIMARY"){$t[$Sh]=array_flip($s["columns"]);break;}}$c[$Sh]=$t[$Sh];}if(isset($c[$Sh][$Rh])){unset($c[$Sh][$Rh]);$t[$Sh][$Rh]=$Bf;$ig[$Bf]=$Sh;}}if($k->charsetnr==63)$ab[$Bf]=true;$Pl[$Bf]=$k->type;$T=trim(($Sh!=""?"$Sh.$Rh":($k->name!=$Rh?$Rh:""))." ".Driver::get()->getTypeName($k));echo"<th".($T!=""?" title='".h($T)."'":"").">".h($A).($Th?doc_link(['sql'=>"explain-output.html#explain_".strtolower($A),'mariadb'=>"reference/sql-statements/administrative-sql-statements/analyze-and-explain-statements/explain#columns-in-explain-...-select",]):"");}echo"</thead>\n";}echo"<tr>";foreach($K
as$u=>$X){$x="";if(isset($ig[$u])&&!$c[$ig[$u]]){if($Th&&DIALECT=="sql"){$Q=$K[array_search("table=",$ig)];$x=ME.$ig[$u].urlencode($Th[$Q]!=""?$Th[$Q]:$Q);}else{$x=ME."edit=".urlencode($ig[$u]);foreach($t[$ig[$u]]as$_b=>$Bf)$x
.="&where".urlencode("[".bracket_escape($_b)."]")."=".urlencode($K[$Bf]);}}$U=($ab[$u]?'blob':($Pl[$u]==254?'char':''));$k=['full_type'=>$U,'type'=>$U,];$X=select_value($X,$x,$k,null);$yb=$Pl[$u]<=9||$Pl[$u]==246?"class='number'":"";echo"<td $yb>$X</td>";}}if($q)echo"</table>\n</div>";else
echo"<p class='message'>".lang(89);echo"\n";return$J;}function
referencable_primary($Xj){$J=[];foreach(table_status('',true)as$Zk=>$Q){if($Zk!=$Xj&&fk_support($Q)){foreach(fields($Zk)as$k){if($k["primary"]){if($J[$Zk]){unset($J[$Zk]);break;}$J[$Zk]=$k;}}}}return$J;}function
textarea($A,$Y,$L=10,$Fb=80){echo"<textarea name='".h($A)."' rows='$L' cols='$Fb' class='sqlarea jush-".DIALECT."' spellcheck='false' wrap='off'>";if(is_array($Y)){foreach($Y
as$X)echo
h($X[0])."\n\n\n";}else
echo
h($Y);echo"</textarea>";}function
select_input($Ma,$C,$Y="",$Ch="",$yi=""){$jl=($C?"select":"input");return"<$jl $Ma".($C?"><option value=''>$yi".optionlist($C,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$yi'>").($Ch?script("qsl('$jl').onchange = $Ch;",""):"");}function
json_row($u,$X=null){static$Vd=true;if($Vd)echo"{";if($u!=""){echo($Vd?"":",")."\n\t\"".addcslashes($u,"\r\n\t\"\\/").'": '.($X!==null?'"'.addcslashes($X,"\r\n\t\"\\/").'"':'null');$Vd=false;}else{echo"\n}\n";$Vd=true;}}function
edit_type($u,$k,$Cb,$de=[],$Ed=[]){$U=isset($k["type"])?$k["type"]:null;echo'<td><select name="',h($u),'[type]" class="type" aria-labelledby="label-type">';$Qc=Driver::get()->getTypes();if($U&&!isset($Qc[$U])&&!isset($de[$U])&&!in_array($U,$Ed))$Ed[]=$U;$Gk=Driver::get()->getStructuredTypes();if($de)$Gk[lang(90)]=$de;echo
optionlist(array_merge($Ed,$Gk),$U),'</select><td><input name="',h($u),'[length]" value="',h(isset($k["length"])?$k["length"]:null),'" size="3"',(!(isset($k["length"])?$k["length"]:null)&&preg_match('~var(char|binary)$~',$U)?" class='input required'":" class='input'"),' aria-labelledby="label-length"><td class="options">',($Cb?"<select name='".h($u)."[collation]'".(preg_match('~(char|text|enum|set)$~',$U)?"":" class='hidden'").'><option value="">('.lang(91).')'.optionlist($Cb,isset($k["collation"])?$k["collation"]:null).'</select>':''),(Driver::get()->getUnsigned()?"<select name='".h($u)."[unsigned]'".(!$U||preg_match(number_type(),$U)?"":" class='hidden'").'><option>'.optionlist(Driver::get()->getUnsigned(),isset($k["unsigned"])?$k["unsigned"]:null).'</select>':''),(isset($k['on_update'])?"<select name='".h($u)."[on_update]'".(preg_match('~timestamp|datetime~',$U)?"":" class='hidden'").'>'.optionlist([""=>"(".lang(92).")","CURRENT_TIMESTAMP"],(preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?"CURRENT_TIMESTAMP":$k["on_update"])).'</select>':''),($de?"<select name='".h($u)."[on_delete]'".(preg_match("~`~",$U)?"":" class='hidden'")."><option value=''>(".lang(93).")".optionlist(Driver::get()->getOnActions(),isset($k["on_delete"])?$k["on_delete"]:null)."</select> ":" ");}function
process_length($v){$md=Driver::$EnumLengthPattern;return(preg_match("~^\\s*\\(?\\s*$md(?:\\s*,\\s*$md)*+\\s*\\)?\\s*\$~",$v)&&preg_match_all("~$md~",$v,$_)?"(".implode(",",$_[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$v)));}function
process_type($k,$Ab="COLLATE"){return" $k[type]".process_length($k["length"]).(preg_match(number_type(),$k["type"])&&in_array($k["unsigned"],Driver::get()->getUnsigned())?" $k[unsigned]":"").(preg_match('~char|text|enum|set~',$k["type"])&&$k["collation"]?" $Ab ".(DIALECT=="mssql"?$k["collation"]:q($k["collation"])):"");}function
process_field($k,$Nl){if($k["on_update"])$k["on_update"]=preg_replace('~current_timestamp(\(\))?~i',"CURRENT_TIMESTAMP",$k["on_update"]);return[idf_escape(trim($k["field"])),process_type($Nl),($k["null"]?" NULL":" NOT NULL"),default_value($k),(preg_match('~timestamp|datetime~',$k["type"])&&$k["on_update"]?" ON UPDATE ".$k["on_update"]:""),(support("comment")&&$k["comment"]!=""?" COMMENT ".q(normalize_newlines($k["comment"])):""),($k["auto_increment"]?auto_increment():null),];}function
normalize_newlines($Y){return
str_replace("\r","",(string)$Y);}function
default_value($k){if($k["default"]===null)return"";$i=normalize_newlines($k["default"]);$pe=$k["generated"];if(in_array($pe,Driver::get()->getGenerated())){if(DIALECT=="mssql")return" AS ($i)".($pe=="VIRTUAL"?"":" $pe");else
return" GENERATED ALWAYS AS ($i) $pe";}if(stripos($i,"GENERATED ")===0)return" $i";if(preg_match('~char|binary|text|json|enum|set~',$k["type"])||preg_match('~^(?![a-z])~i',$i)){if(DIALECT=="sql"&&preg_match('~text|json~',$k["type"]))return" DEFAULT (".q($i).")";else
return" DEFAULT ".q($i);}else{$i=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$i);return" DEFAULT ".(DIALECT=="sqlite"?"($i)":$i);}}function
type_class($U){foreach(['char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',]as$yb=>$vi){if(preg_match("~$yb|$vi~",$U))return"class='$yb'";}return"";}function
edit_fields(array$l,array$Cb,$U="TABLE",$de=[]){$l=array_values($l);$Nb=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$Lb=$Nb?"":"class='hidden'";echo"<thead><tr>\n";if(support("move_col"))echo"<td class='jsonly'></td>";if($U=="PROCEDURE")echo"<td></td>";echo"<th id='label-name'>",($U=="TABLE"?lang(94):lang(95)),"</th>\n","<td id='label-type'>",lang(44),"<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>",script("gid('enum-edit').onblur = onFieldLengthBlur;"),"</td>\n","<td id='label-length'>",lang(96),"</td>\n","<td>",lang(97),"</td>\n";if($U=="TABLE")echo"<td id='label-null'>NULL</td>\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='",lang(47),"'>AI</abbr>",doc_link(['sql'=>"example-auto-increment.html",'mariadb'=>"reference/data-types/auto_increment",]),"</td>\n","<td id='label-default'>",lang(48),"</td>\n",support("comment")?"<td id='label-comment' $Lb>".lang(46)."</td>\n":"";echo"<td>","<button name='add[",(support("move_col")?0:count($l)),"]' value='1' title='",lang(98),"' class='button light'>",icon_solo("add"),"</button>",(support("move_col")?"":script("qsl('button').onclick = onAddLastFieldRowClick;")),script("row_count = ".count($l).";"),"</td>\n","</tr></thead>\n";$yb=support("move_col")?"class='sortable'":"";echo"<tbody $yb>\n";foreach($l
as$q=>$k){$q++;$Uh=$k[($_POST?"orig":"field")];$Gc=(isset($_POST["add"][$q-1])||(isset($k["field"])&&!(isset($_POST["drop_col"][$q])?$_POST["drop_col"][$q]:null)))&&(support("drop_col")||$Uh=="");echo"<tr",($Gc?"":" hidden"),">\n";if(support("move_col"))echo"<td class='handle jsonly'>",icon_solo("handle"),"</td>";if($U=="PROCEDURE")echo"<td>",html_select("fields[$q][inout]",Driver::get()->getInOut(),$k["inout"]),"</td>\n";echo"<th>";if($Gc)echo"<input class='input' name='fields[$q][field]' value='",h($k["field"]),"' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name' ".(isset($_POST["add"][$q-1])?"autofocus":"").">";echo
input_hidden("fields[$q][orig]",$Uh);edit_type("fields[$q]",$k,$Cb,$de);echo"</th>\n";if($U=="TABLE"){echo"<td>",checkbox("fields[$q][null]",1,$k["null"],"","","block","label-null"),"</td>\n";$tb=$k["auto_increment"]?"checked":"";echo"<td><label class='block'><input type='radio' name='auto_increment_col' value='$q' $tb aria-labelledby='label-ai'></label></td>\n","<td class='default-value'>";if(Driver::get()->getGenerated())echo
html_select("fields[$q][generated]",array_merge(["","DEFAULT"],Driver::get()->getGenerated()),$k["generated"]);else
echo
checkbox("fields[$q][generated]",1,$k["generated"],"","","","label-default");$Ma="name='fields[$q][default]' aria-labelledby='label-default'";$Y=h($k["default"]);if(str_contains($Y,"\n")){if($Y[0]=="\n")$Y="\n$Y";echo"<textarea $Ma rows='3' cols='30' style='vertical-align: bottom;'>$Y</textarea>";}else
echo"<input class='input' $Ma value='$Y'>";echo"</td>\n";if(support("comment")){$vg=Connection::get()->isMinVersion("5.5")?1024:255;$Ma="name='fields[$q][comment]' data-maxlength='$vg' aria-labelledby='label-comment'";$Y=h($k["comment"]);echo"<td $Lb>";if(str_contains($Y,"\n")){if($Y[0]=="\n")$Y="\n$Y";echo"<textarea $Ma rows='3' cols='30' style='vertical-align: bottom;'>$Y</textarea>";}else
echo"<input class='input' $Ma value='$Y'>";echo"</td>\n";}}echo"<td>";if(support("move_col"))echo"<button name='add[$q]' value='1' title='".lang(98)."' class='button light'>",icon_solo("add"),"</button>","<button name='up[$q]' value='1' title='".lang(99)."' class='button light hidden'>",icon_solo("arrow-up"),"</button>","<button name='down[$q]' value='1' title='".lang(100)."' class='button light hidden'>",icon_solo("arrow-down"),"</button>";if($Uh==""||support("drop_col"))echo"<button name='drop_col[$q]' value='1' title='".lang(58)."' class='button light'>",icon_solo("remove"),"</button>";echo"</td>\n</tr>\n";}echo"</tbody>";}function
process_fields(&$l){$sh=0;if($_POST["up"]){$Tf=0;foreach($l
as$u=>$k){if(key($_POST["up"])==$u){unset($l[$u]);array_splice($l,$Tf,0,[$k]);break;}if(isset($k["field"]))$Tf=$sh;$sh++;}}elseif($_POST["down"]){$ie=false;foreach($l
as$u=>$k){if(isset($k["field"])&&$ie){unset($l[key($_POST["down"])]);array_splice($l,$sh,0,[$ie]);break;}if(key($_POST["down"])==$u)$ie=$k;$sh++;}}elseif($_POST["add"]){$l=array_values($l);array_splice($l,key($_POST["add"]),0,[[]]);}elseif(!$_POST["drop_col"])return
false;return
true;}function
normalize_enum($z){$X=$z[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($X[0].$X[0],$X[0],substr($X,1,-1))),'\\'))."'";}function
grant($te,array$Ri,$c,$Ah,$hm){if(!$Ri)return
true;if($Ri==["ALL PRIVILEGES","GRANT OPTION"]){if($te)return(bool)queries("GRANT ALL PRIVILEGES ON $Ah TO $hm WITH GRANT OPTION");else
return
queries("REVOKE ALL PRIVILEGES ON $Ah FROM $hm")&&queries("REVOKE GRANT OPTION ON $Ah FROM $hm");}if($Ri==["GRANT OPTION","PROXY"]){if($te)return(bool)queries("GRANT PROXY ON $Ah TO $hm WITH GRANT OPTION");else
return(bool)queries("REVOKE PROXY ON $Ah FROM $hm");}return(bool)queries(($te?"GRANT ":"REVOKE ").preg_replace('~(GRANT OPTION)\([^)]*\)~','$1',implode("$c, ",$Ri).$c)." ON $Ah ".($te?"TO ":"FROM ").$hm);}function
drop_create($Sc,$bc,$Tc,$ol,$Uc,$y,$Ig,$Gg,$Hg,$zh,$ih){if($_POST["drop"])query_redirect($Sc,$y,$Ig);elseif($zh=="")query_redirect($bc,$y,$Hg);elseif($zh!=$ih){$ec=queries($bc);queries_redirect($y,$Gg,$ec&&queries($Sc));if($ec)queries($Tc);}else
queries_redirect($y,$Gg,queries($ol)&&queries($Uc)&&queries($Sc)&&queries($bc));}function
create_trigger($Ah,array$Il){$xl=" $Il[Timing] $Il[Event]".(preg_match('~ OF~',$Il["Event"])?" $Il[Of]":"");return"CREATE TRIGGER ".idf_escape($Il["Trigger"]).(DIALECT=="mssql"?$Ah.$xl:$xl.$Ah).rtrim(" $Il[Type]\n$Il[Statement]",";").";";}function
create_routine($_j,$K){$kk=[];$l=(array)$K["fields"];ksort($l);$Ze=implode("|",Driver::get()->getInOut());foreach($l
as$k){if($k["field"]!="")$kk[]=(preg_match("~^($Ze)\$~",$k["inout"])?"$k[inout] ":"").idf_escape($k["field"]).process_type($k,"CHARACTER SET");}$wc=rtrim($K["definition"],";");return"CREATE $_j ".idf_escape(trim($K["name"]))." (".implode(", ",$kk).")".($_j=="FUNCTION"?" RETURNS".process_type($K["returns"],"CHARACTER SET"):"").($K["language"]?" LANGUAGE $K[language]":"").(DIALECT=="pgsql"?" AS ".q($wc):"\n$wc;");}function
remove_definer($H){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$H);}function
format_foreign_key($o){$Bh=implode("|",Driver::get()->getOnActions());$h=$o["db"];$nh=$o["ns"];return" FOREIGN KEY (".implode(", ",array_map('AdminNeo\idf_escape',$o["source"])).") REFERENCES ".($h!=""&&$h!=$_GET["db"]?idf_escape($h).".":"").($nh!=""&&$nh!=$_GET["ns"]?idf_escape($nh).".":"").idf_escape($o["table"])." (".implode(", ",array_map('AdminNeo\idf_escape',$o["target"])).")".(preg_match("~^($Bh)\$~",$o["on_delete"])?" ON DELETE $o[on_delete]":"").(preg_match("~^($Bh)\$~",$o["on_update"])?" ON UPDATE $o[on_update]":"").(isset($o["deferrable"])?" $o[deferrable]":"");}function
tar_file($n,TmpFile$_l){$He=pack("a100a8a8a8a12a12",$n,644,0,0,decoct($_l->getSize()),decoct(time()));$vb=8*32;for($q=0;$q<strlen($He);$q++)$vb+=ord($He[$q]);$He
.=sprintf("%06o",$vb)."\0 ";echo$He,str_repeat("\0",512-strlen($He));$_l->send();echo
str_repeat("\0",511-($_l->getSize()+511)%512);}function
doc_link(array$ui,$pl="<sup>?</sup>"){if(!(isset($ui[DIALECT])?$ui[DIALECT]:null))return"";$qm=doc_version();$cm=['sql'=>"https://dev.mysql.com/doc/refman/$qm/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(Connection::get()->isCockroachDB()?"current":$qm)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".str_replace(".","",$qm)."&id=",'elastic'=>"https://www.elastic.co/guide/en/elasticsearch/reference/$qm/",];if(Connection::get()->isMariaDB()){$cm['sql']="https://mariadb.com/docs/server/";$ui['sql']=isset($ui['mariadb'])?$ui['mariadb']:str_replace(".html","",$ui['sql']);}return"<a href='".h($cm[DIALECT].$ui[DIALECT].(DIALECT=='mssql'?"?view=sql-server-ver$qm":""))."'".target_blank().">$pl</a>";}function
doc_version(){return
preg_replace('~^(\d\.?\d).*~s','\1',Connection::get()->getVersion());}function
db_size($h){if(!Connection::get()->selectDatabase($h))return"?";$J=0;foreach(table_status()as$R)$J+=$R["Data_length"]+$R["Index_length"];return
format_number($J);}function
set_utf8mb4($bc){static$kk=false;if(!$kk&&preg_match('~\butf8mb4~i',$bc)){$kk=true;echo"SET NAMES ".charset(Connection::get()).";\n\n";}}error_reporting(E_ALL&~E_DEPRECATED);set_error_handler(function($od,$j){return(bool)preg_match('~^Undefined (array key|offset|index)~',$j);},E_WARNING|E_NOTICE);;$Sd=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($Sd||ini_get("filter.default_flags")){foreach(['_GET','_POST','_COOKIE','_SERVER']as$X){$Wl=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($Wl)$$X=$Wl;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");class
Server{private$params;private$key;function
__construct(array$gi,$u=null){$this->params=$gi;$this->key=$u;}function
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
getConfigParams(){$gi=isset($this->params["config"])?$this->params["config"]:[];$se=["servers"];foreach($se
as$fi){if(isset($gi[$fi]))unset($gi[$fi]);}return$gi;}}class
Config{static$NavigationSimple="simple";static$NavigationDual="dual";static$NavigationHover="hover";static$NavigationReversed="reversed";private$params;private$servers=[];function
__construct(array$gi){$this->params=$gi;if(isset($this->params["servers"])){foreach($this->params["servers"]as$u=>$N){$ck=new
Server($N,is_string($u)?$u:null);$this->params["servers"][$u]=$ck;$this->servers[$ck->getKey()]=$ck;}}}function
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
getDefaultDriver(array$Rc){$Pc=isset($this->params["defaultDriver"])?$this->params["defaultDriver"]:null;return$Pc&&isset($Rc[$Pc])?$Pc:key($Rc);}function
getDefaultServer(){$N=isset($this->params["defaultServer"])?$this->params["defaultServer"]:null;if($N===null)return
null;$ck=isset($this->params["servers"][$N])?$this->params["servers"][$N]:null;if($ck)return$ck->getKey();return$N;}function
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
getServerPairs(array$Rc){$qk=null;foreach($this->servers
as$N){if(!isset($Rc[$N->getDriver()]))continue;if(!$qk)$qk=$N->getDriver();elseif($N->getDriver()!=$qk){$qk=null;break;}}$dk=[];foreach($this->servers
as$u=>$N){if(!isset($Rc[$N->getDriver()]))continue;$bk=$N->getName();if($qk&&$bk)$dk[$u]=$bk;else$dk[$u]=$Rc[$N->getDriver()].($bk!=""?" - $bk":"");}return$dk;}function
getServer($ak){return
isset($this->servers[$ak])?$this->servers[$ak]:null;}function
applyServer($N){$N=$this->getServer($N);if(!$N)return;$this->params=array_merge($this->params,$N->getConfigParams());}private
function
parseList($kg){if(is_array($kg))return$kg;return
preg_split('~\s*,\s*~',(string)$kg);}}class
Settings{private
static$CookieName="neo_settings";static$ColorSchemeLight="light";static$ColorSchemeDark="dark";static$NavigationWidthMin=10;static$NavigationWidthMax=30;private$config;private$params=[];function
__construct(Config$Rb){$this->config=$Rb;if(isset($_COOKIE[self::$CookieName])){parse_str($_COOKIE[self::$CookieName],$this->params);$this->save();}if(isset($_COOKIE["neo_lang"])){$this->updateParameter("lang",$_COOKIE["neo_lang"]);unset($_COOKIE["neo_lang"]);cookie("neo_lang","",-3600);}}static
function
readParameter($u){parse_str(isset($_COOKIE[self::$CookieName])?$_COOKIE[self::$CookieName]:"",$gi);return
isset($gi[$u])?$gi[$u]:null;}function
getParameter($u,$i=null){return
isset($this->params[$u])?$this->params[$u]:$i;}function
updateParameter($u,$Y){$this->updateParameters([$u=>$Y]);}function
updateParameters(array$gi){$this->params=array_filter(array_merge($this->params,$gi),function($Y){return$Y!==null;});$this->save();}private
function
save(){cookie(self::$CookieName,http_build_query($this->params),7776000);}function
getColorScheme(){return$this->getParameter("colorScheme");}function
getNavigationMode(){return($ra=$this->getParameter("navigationMode"))!==null?$ra:$this->config->getNavigationMode();}function
isNavigationSimple(){return$this->getNavigationMode()==Config::$NavigationSimple;}function
isNavigationDual(){return$this->getNavigationMode()==Config::$NavigationDual;}function
isNavigationHover(){return$this->getNavigationMode()==Config::$NavigationHover;}function
isNavigationReversed(){return$this->getNavigationMode()==Config::$NavigationReversed;}function
getNavigationWidth(){$Fm=$this->getParameter("navigationWidth");if($Fm===null)return
null;return
min(max((float)$Fm,self::$NavigationWidthMin),self::$NavigationWidthMax);}function
isSelectionPreferred(){return($ra=$this->getParameter("preferSelection"))!==null?$ra:$this->config->isSelectionPreferred();}function
isRelationLinks(){return
isset($this->params["relationLinks"])?$this->params["relationLinks"]:$this->config->isRelationLinks();}function
getRecordsPerPage(){return($ra=$this->getParameter("recordsPerPage"))!==null?$ra:$this->config->getRecordsPerPage();}function
getEnumAsSelectThreshold(){$Y=$this->getParameter("enumAsSelectThreshold");if($Y<0)return
null;return$Y!==null?(int)$Y:$this->config->getEnumAsSelectThreshold();}}class
Hash{static
function
hkdf($v,$u,$ef="",$Ej=""){if(extension_loaded("hash")&&PHP_VERSION_ID>=70120)return
hash_hkdf("sha1",$u,$v,$ef,$Ej);if($Ej=="")$Ej=str_repeat("\0",20);$Si=self::hmacSha1($u,$Ej);$wh="";for($Hf="",$bb=1;!isset($wh[$v-1]);$bb++){$Hf=self::hmacSha1($Hf.$ef.chr($bb),$Si);$wh
.=$Hf;}return
substr($wh,0,$v);}static
function
hmacSha1($f,$u){if(!extension_loaded("hash"))return
hash_hmac("sha1",$f,$u,true);if(strlen($u)>64)$u=sha1($u,true);$u=str_pad($u,64,"\0");$tf=($u^str_repeat("\x36",64));$Fh=($u^str_repeat("\x5C",64));return
sha1($Fh.sha1($tf.$f,true),true);}}class
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
\Sodium\randombytes_buf($v);$Vl=DIRECTORY_SEPARATOR==="/";if($Vl){$I=self::readDevUrandom($v);if($I!==false)return$I;}$fb=$Vl&&PHP_VERSION_ID>50609&&PHP_VERSION_ID<50613;if(extension_loaded("mcrypt")&&!$fb){$I=mcrypt_create_iv($v,MCRYPT_DEV_URANDOM);if($I!==false)return$I;}$gb=PHP_VERSION_ID<50444||(PHP_VERSION_ID>50500&&PHP_VERSION_ID<50528)||(PHP_VERSION_ID>50600&&PHP_VERSION_ID<50612);if(extension_loaded("openssl")&&!$gb){$I=openssl_random_pseudo_bytes($v,$Fk);if($Fk)return$I;}return
false;}private
static
function
readDevUrandom($v){static$m=null;if($m===null)$m=@fopen("/dev/urandom","rb");if(!$m)return
false;$pj=$v;$I="";do{$f=fread($m,$pj);if($f===false)return
false;$pj-=strlen($f);$I
.=$f;}while($pj>0);return$I;}private
static
function
readCapicom($v){$Hb=new
\COM("CAPICOM.Utilities.1");$pj=$v;$I="";do{$f=base64_decode((string)$Hb->GetRandom($v,0));$pj-=strlen($f);$I
.=$f;}while($pj>0);return$I;}private
static
function
lastResortRandom($v){static$u=null;static$Ej=null;if($u===null){$f=$_SERVER;$f[]=uniqid("",true);shuffle($f);$u=sha1(serialize($f),true);if(extension_loaded("openssl"))$Ej=openssl_random_pseudo_bytes(20);else{$Ej="";for($q=0;$q<20;$q++)$Ej
.=chr((mt_rand()^mt_rand())%256);}}else{if((ord($u)%2===0)===(ord($Ej)%2===0))$u=Hash::hmacSha1($u,$Ej);else$Ej=Hash::hmacSha1($Ej,$u);}return
Hash::hkdf($v,$u,"$v",$Ej);}}if(!function_exists("str_starts_with")){function
str_starts_with($Ge,$eh){return
strpos($Ge,$eh)===0;}}if(!function_exists("str_contains")){function
str_contains($Ge,$eh){return
strpos($Ge,$eh)!==false;}}if(!function_exists("password_verify")){function
password_verify($F,$Fe){return
false;}}if(!function_exists("ini_set")){function
ini_set($Lh,$Y){return
false;}}function
version(){return
VERSION;}function
idf_unescape($Ve){if(!preg_match('~^[`\'"[]~',$Ve))return$Ve;$Tf=substr($Ve,-1);return
str_replace($Tf.$Tf,$Tf,substr($Ve,1,-1));}function
q($Ek){return
Connection::get()->quote($Ek);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
number_type(){return'((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes(array$nm,$Sd=false){$J=[];foreach($nm
as$u=>$X)$J[stripslashes($u)]=(is_array($X)?remove_slashes($X,$Sd):($Sd?$X:stripslashes($X)));return$J;}function
bracket_escape($Ve,$Ta=false){static$Fl=[':'=>':1',']'=>':2','['=>':3','"'=>':4'];return
strtr($Ve,($Ta?array_flip($Fl):$Fl));}function
min_version($qm,$sg=null,$e=null){if(!$e)$e=Connection::get();if($sg&&$e->isMariaDB())$qm=$sg;return$qm&&$e->isMinVersion($qm);}function
charset(Connection$e){return($e->isMinVersion("5.5.3")?"utf8mb4":"utf8");}function
link_files($A,array$Rd){switch($A){case'favicon-blue.ico':$n='favicon-blue-0f5ce53a66b1e25395d0048da369f19e__6bb95962.ico';break;case'favicon-green.ico':$n='favicon-green-def78cfa7c465c8b0e9966e3eb87407d__6bb95962.ico';break;case'favicon-orange.ico':$n='favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__6bb95962.ico';break;case'favicon-purple.ico':$n='favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__6bb95962.ico';break;case'favicon-red.ico':$n='favicon-red-c2ebb34a8df5aba28e15d87728a151df__6bb95962.ico';break;case'favicon-blue.svg':$n='favicon-blue-17e440832c1eac07527560a0d6f0d2ee__6bb95962.svg';break;case'favicon-green.svg':$n='favicon-green-bb254c95a033f67e3d433a3df63e160d__6bb95962.svg';break;case'favicon-orange.svg':$n='favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__6bb95962.svg';break;case'favicon-purple.svg':$n='favicon-purple-4cfd57d31ab991e8071fe34060cd3123__6bb95962.svg';break;case'favicon-red.svg':$n='favicon-red-a006e401273230fd6be80568c8361b57__6bb95962.svg';break;case'apple-touch-icon-blue.png':$n='apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__6bb95962.png';break;case'apple-touch-icon-green.png':$n='apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__6bb95962.png';break;case'apple-touch-icon-orange.png':$n='apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__6bb95962.png';break;case'apple-touch-icon-purple.png':$n='apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__6bb95962.png';break;case'apple-touch-icon-red.png':$n='apple-touch-icon-red-507228751d2170d047e72142d2c02390__6bb95962.png';break;case'logo.svg':$n='logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg';break;case'jush.css':$n='jush-b3a93b18444da26820ff61746521dede__6f96e697.css';break;case'jush-dark.css':$n='jush-dark-f8dac59c6ad1018686e52a0e0357e421__2ec7793c.css';break;case'jush.js':$n='jush-615bc0b9720a1de8edd2c6876a3495b6__5c4c1da6.js';break;case'icons.svg':$n='icons-70163a2695280bf75edba563e7b5471b__2ec7793c.svg';break;case'default-blue.css':$n='default-blue-564b3ff62703b0741b8754503c621af3__9b117389.css';break;case'default-green.css':$n='default-green-8facfae54345a3eb358848ed4141060f__9b117389.css';break;case'default-orange.css':$n='default-orange-4fd2276ffa8eaad143aec2dba3782911__fb9c21d7.css';break;case'default-purple.css':$n='default-purple-33d1c33b271b014ef4b3f2f4e42cd9f9__fb9c21d7.css';break;case'default-red.css':$n='default-red-9c7de6d1d78ea798bfef943c92b6b611__9b117389.css';break;case'default-blue-dark.css':$n='default-blue-dark-79895bd8e65cadab7d67d31c191a833d__7a7f64b1.css';break;case'default-green-dark.css':$n='default-green-dark-d7e561f7fc07f913992951110461fd8c__7a7f64b1.css';break;case'default-orange-dark.css':$n='default-orange-dark-e6668a1545546a87b40acb95390b5283__1e3abf59.css';break;case'default-purple-dark.css':$n='default-purple-dark-83c0052a3d8e86dfb6debf8349377b25__1e3abf59.css';break;case'default-red-dark.css':$n='default-red-dark-aa471f32fb495651c17bba291cd8b147__7a7f64b1.css';break;case'main.js':$n='main-eaf2ce2c3d91edbef355936903e47e59__324fd0f3.js';break;default:$n=null;break;}if(!$n)return
null;return
BASE_URL."?file=".urldecode($n);}function
ini_bool($Lh){$X=ini_get($Lh);return
preg_match('~^(on|true|yes)$~i',$X)||(int)$X;}function
ini_bytes($gf){$X=ini_get($gf);switch(strtolower(substr($X,-1))){case'g':$X=(int)$X*1024;case'm':$X=(int)$X*1024;case'k':$X=(int)$X*1024;}return$X;}function
sid(){static$J;if($J===null)$J=(session_id()&&!($_COOKIE&&ini_bool("session.use_cookies")));return$J;}function
save_driver_name($Pc,$N,$A){restart_session();$_SESSION["drivers"][$Pc][$N]=$A;stop_session();}function
get_driver_name($Pc,$N=null){return
isset($_SESSION["drivers"][$Pc][$N])?$_SESSION["drivers"][$Pc][$N]:Drivers::get($Pc);}function
save_login($Pc,$N,$V,$F,$h=""){$u=isset($_COOKIE["neo_key"])?$_COOKIE["neo_key"]:null;$_SESSION["pwds"][$Pc][$N][$V]=$u?[encrypt_string($F,$u)]:$F;$_SESSION["db"][$Pc][$N][$V][$h]=true;}function
delete_login($Pc,$N,$V){unset($_SESSION["pwds"][$Pc][$N][$V]);unset($_SESSION["db"][$Pc][$N][$V]);}function
get_password(){$F=get_session("pwds");if(is_array($F))return$_COOKIE["neo_key"]?decrypt_string($F[0],$_COOKIE["neo_key"]):false;return$F;}function
get_vals($H,$b=0){$J=[];$I=Connection::get()->query($H);if(is_object($I)){while($K=$I->fetchRow())$J[]=$K[$b];}return$J;}function
get_key_vals($H,$e=null,$lk=true){if(!$e)$e=Connection::get();$J=[];$I=$e->query($H);if(is_object($I)){while($K=$I->fetchRow()){if($lk)$J[$K[0]]=$K[1];else$J[]=$K[0];}}return$J;}function
get_rows($H,$e=null,$j="<p class='error'>"){if(!$e)$e=Connection::get();$J=[];$I=$e->query($H);if(is_object($I)){while($K=$I->fetchAssoc())$J[]=$K;}elseif(!$I&&!is_object($e)&&$j&&(defined("AdminNeo\PAGE_HEADER")||$j=="-- "))echo$j.error()."\n";return$J;}function
unique_array(array$K,array$t){foreach($t
as$s){if(!preg_match("~PRIMARY|UNIQUE~",$s["type"])&&!$s["partial"])continue;$Sl=[];foreach($s["columns"]as$u){if(!isset($K[$u]))continue
2;$Sl[$u]=$K[$u];}return$Sl;}return
null;}function
escape_key($u){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$u,$z))return$z[1].idf_escape(idf_unescape($z[2])).$z[3];return
idf_escape($u);}function
where($Z,$l=[]){$Qb=[];foreach((array)$Z["where"]as$u=>$X){$u=bracket_escape($u,true);$b=escape_key($u);$Nd=isset($l[$u]["type"])?$l[$u]["type"]:null;$me=isset($l[$u]["full_type"])?$l[$u]["full_type"]:null;if(DIALECT=="sql"&&$Nd=="json")$Qb[]="$b = CAST(".q($X)." AS JSON)";elseif(DIALECT=="pgsql"&&preg_match('~^jsonb?$~',$me))$Qb[]="$b::jsonb = ".q($X)."::jsonb";elseif(DIALECT=="sql"&&is_numeric($X)&&strpos($X,".")!==false)$Qb[]="$b LIKE ".q($X);elseif(DIALECT=="mssql"&&strpos($Nd,"datetime")===false)$Qb[]="$b LIKE ".q(preg_replace('~[_%[]~','[\0]',$X));else$Qb[]="$b = ".(isset($l[$u])?unconvert_field($l[$u],q($X)):q($X));if(DIALECT=="sql"&&preg_match('~char|text~',$Nd)&&preg_match("~[^ -@]~",$X))$Qb[]="$b = ".q($X)." COLLATE ".charset(Connection::get())."_bin";}foreach((array)$Z["null"]as$u)$Qb[]=escape_key($u)." IS NULL";return
implode(" AND ",$Qb);}function
where_check($X,$l=[]){parse_str($X,$qb);remove_slashes([&$qb]);return
where($qb,$l);}function
where_link($q,$b,$Y,$Ih="="){return"&where%5B$q%5D%5Bcol%5D=".urlencode($b)."&where%5B$q%5D%5Bop%5D=".urlencode(($Y!==null?$Ih:"IS NULL"))."&where%5B$q%5D%5Bval%5D=".urlencode($Y);}function
convert_fields(array$c,array$l,array$M=[]){$I="";foreach($c
as$u=>$X){if($M&&!in_array(idf_escape($u),$M))continue;$La=convert_field($l[$u]);if($La)$I
.=", $La AS ".idf_escape($u);}return$I;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),[";"=>"%3B",","=>"%2C"]);}function
cookie($A,$Y,$dg=2592000){header("Set-Cookie: $A=".rawurlencode($Y).($dg?"; expires=".gmdate("D, d M Y H:i:s",time()+$dg)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_url($bm,$Xb){$J=@file_get_contents($bm,false,$Xb);if(function_exists('http_get_last_response_headers'))$http_response_header=($ra=http_get_last_response_headers())!==null?$ra:[];return[$J,isset($http_response_header)?$http_response_header:[]];}function
get_settings($Zb="neo_settings"){parse_str(isset($_COOKIE[$Zb])?$_COOKIE[$Zb]:"",$O);return$O;}function
get_setting($u,$Zb="neo_settings"){$O=get_settings($Zb);return
isset($O[$u])?$O[$u]:null;}function
save_settings(array$O,$Zb="neo_settings"){cookie($Zb,http_build_query($O+get_settings($Zb)));}function
restart_session(){if(!ini_bool("session.use_cookies")&&session_status()==PHP_SESSION_NONE)session_start();}function
stop_session($ae=false){$fm=ini_bool("session.use_cookies");if(!$fm||$ae){session_write_close();if($fm&&ini_set("session.use_cookies","0")===false)session_start();}}function&get_session($u){return$_SESSION[$u][DRIVER][SERVER][$_GET["username"]];}function
set_session($u,$X){$_SESSION[$u][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($pm,$N,$V,$h=null){$am=remove_from_uri(implode("|",array_keys(Drivers::getList()))."|username|ext|".($h!==null?"db|":"").($pm=='mssql'||$pm=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$am,$z);return"$z[1]?".(sid()?session_name()."=".urlencode(session_id())."&":"").urlencode($pm)."=".urlencode($N)."&".($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"")."username=".urlencode($V).($h!=""?"&db=".urlencode($h):"").($z[2]?"&$z[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($y,$Fg=null){if($Fg!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($y!==null?$y:$_SERVER["REQUEST_URI"]))][]=$Fg;}if($y!==null){if($y=="")$y=".";header("Location: $y");exit;}}function
query_redirect($H,$y,$Fg,$gj=true,$vd=true,$Fd=false,$vl=""){if($vd){$Ak=microtime(true);$Fd=!Connection::get()->query($H);$vl=format_time($Ak);}$xk=$H?Admin::get()->formatMessageQuery($H,$vl,$Fd):"";if($Fd){Admin::get()->addError(error().$xk.script("initToggles();"));return
false;}if($gj)redirect($y,$Fg.$xk);return
true;}function
queries_redirect($y,$Fg,$gj){$Xi=implode("\n",Queries::$queries);$vl=format_time(Queries::$start);return
query_redirect($Xi,$y,$Fg,$gj,false,!$gj,$vl);}class
Queries{static$queries=[];static$start=0.0;}function
queries($H){if(!Queries::$start)Queries::$start=microtime(true);if(support("sql")){Queries::$queries[]=(preg_match('~;$~',$H)?"DELIMITER ;;\n$H;\nDELIMITER ":$H).";";return
Connection::get()->query($H);}else{Queries::$queries[]=$H;return[];}}function
apply_queries($H,array$S,$qd='AdminNeo\table'){foreach($S
as$Q){if(!queries("$H ".$qd($Q)))return
false;}return
true;}function
format_time($Ak){return
lang(101,max(0,microtime(true)-$Ak));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($fi=""){return
substr(preg_replace("~(?<=[?&])($fi".(sid()?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($u,$rc=false,$yc=""){$m=$_FILES[$u];if(!$m)return
null;foreach($m
as$u=>$X)$m[$u]=(array)$X;$J='';foreach($m["error"]as$u=>$j){if($j)return$j;$A=$m["name"][$u];$Al=$m["tmp_name"][$u];$Vb=file_get_contents($rc&&preg_match('~\.gz$~',$A)?"compress.zlib://$Al":$Al);if($rc){$Ak=substr($Vb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Ak))$Vb=iconv("utf-16","utf-8",$Vb);elseif($Ak=="\xEF\xBB\xBF")$Vb=substr($Vb,3);}if($yc){if(!preg_match("~$yc\\s*\$~",$Vb))$Vb
.=";";$Vb
.="\n\n";}$J
.=$Vb;}return$J;}function
upload_error($j){$zg=($j==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($j?lang(102).($zg?" ".lang(103,$zg):""):lang(104));}function
repeat_pattern($vi,$v){return
str_repeat("$vi{0,65535}",$v/65535)."$vi{0,".($v%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
format_number($X){return
strtr(number_format($X,0,".",lang(105)),preg_split('~~u',lang(106),-1,PREG_SPLIT_NO_EMPTY));}function
format_rows(array$R){$L=$R["Rows"];$Ia=($L&&(DIALECT=="sqlite"||(isset($R["Engine"])?$R["Engine"]:"")==(DIALECT=="pgsql"?"table":"InnoDB")));return($Ia?"~ ":"").format_number($L);}function
friendly_url($X){return
preg_replace('~\W~i','-',$X);}function
table_status1($Q,$Hd=false){$J=table_status($Q,$Hd);return($J?reset($J):["Name"=>$Q]);}function
column_foreign_keys($Q){$J=[];foreach(Admin::get()->getForeignKeys($Q)as$o){foreach($o["source"]as$X)$J[$X][]=$o;}return$J;}function
fields_from_edit(){$J=[];foreach((array)$_POST["field_keys"]as$u=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$u];$_POST["fields"][$X]=$_POST["field_vals"][$u];}}foreach((array)$_POST["fields"]as$u=>$X){$A=bracket_escape($u,true);$J[$A]=["field"=>$A,"full_type"=>"varchar","type"=>"varchar","privileges"=>["insert"=>1,"update"=>1,"where"=>1,"order"=>1],"null"=>true,"auto_increment"=>($u==Driver::get()->primary),];}return$J;}function
dump_headers($Te,$Wg=false){$Te=friendly_url($Te).date("-Ymd-His");$Bd=Admin::get()->sendDumpHeaders($Te,$Wg);$bi=$_POST["output"];if($bi!="text")header("Content-Disposition: attachment; filename=$Te.$Bd".($bi!="file"&&preg_match('~^[0-9a-z]+$~',$bi)?".$bi":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$Bd;}function
dump_table_order(array$ch,array$mj){$Mf=array_flip($ch);$Ph=[];$ym=[];$jc=false;$xm=function($A)use(&$xm,&$Ph,&$ym,&$jc,$Mf,$mj){if(isset($Ph[$A]))return;if(isset($ym[$A])){$jc=true;return;}$ym[$A]=true;foreach(isset($mj[$A])?$mj[$A]:[]as$kj){if(isset($Mf[$kj]))$xm($kj);}unset($ym[$A]);$Ph[$A]=true;};foreach($ch
as$A)$xm($A);return($jc?null:array_keys($Ph));}function
dump_csv($K){$Ml=$_POST["format"]=="tsv";foreach($K
as$u=>$X){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($Ml?'\t':'[,;]|^$').'~',$X))$K[$u]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($Ml?"\t":";")),$K)."\r\n";}function
apply_sql_function($p,$b){return($p?($p=="unixepoch"?"DATETIME($b, '$p')":($p=="count distinct"?"COUNT(DISTINCT ":strtoupper("$p("))."$b)"):$b);}function
get_temp_dir(){$ti=ini_get("upload_tmp_dir");if(!$ti)$ti=sys_get_temp_dir();return$ti;}function
open_file_with_lock($n){if(is_link($n))return
null;$m=@fopen($n,"c+");if(!$m)return
null;@chmod($n,0660);if(!flock($m,LOCK_EX)){fclose($m);return
null;}return$m;}function
write_and_unlock_file($m,$f){rewind($m);fwrite($m,$f);ftruncate($m,strlen($f));unlock_file($m);}function
unlock_file($m){flock($m,LOCK_UN);fclose($m);}function
first(array$Ka){return
reset($Ka);}function
get_private_key($bc){$n=get_temp_dir()."/adminneo.key";if(!$bc&&!file_exists($n))return
false;$m=open_file_with_lock($n);if(!$m)return
false;$u=stream_get_contents($m);if(!$u){$u=Random::strongKey();write_and_unlock_file($m,$u);}else
unlock_file($m);return$u;}function
get_random_string(){return
Random::strongKey();}function
select_value($X,$x,$k,$rl){if(is_array($X)){$J="";if(array_filter($X,'is_array')==array_values($X)){$If=[];foreach($X
as$W)$If+=array_fill_keys(array_keys($W),null);foreach(array_keys($If)as$Df)$J
.="<th>".h($Df);foreach($X
as$W){$J
.="<tr>";foreach(array_merge($If,$W)as$km)$J
.="<td>".select_value($km,$x,$k,$rl);}}else{foreach($X
as$Df=>$W)$J
.="<tr>".($X!=array_values($X)?"<th>".h($Df):"")."<td>".select_value($W,$x,$k,$rl);}return"<table>$J</table>";}$Jj="";if($k&&$X!==null&&($rl===null||strlen($X)<=$rl)&&($nm=Driver::get()->explodeArrayValue($X,$k["full_type"],$Jj))){$Ij=$k;$Ij["type"]=$Ij["full_type"]=$Jj;$J=select_array_value($nm,$X,$x,$Ij,$rl);return
Driver::get()->implodeArrayValues($J,$k["full_type"]);}if(!$x)$x=Admin::get()->getFieldValueLink($X,$k);if($k)$X=Connection::get()->formatValue($X,$k);$J=$k?Admin::get()->formatFieldValue($X,$k):$X;if($J!==null){if(!is_utf8($J))$J="\0";elseif($rl!=""&&is_shortable($k))$J=truncate_utf8($J,max(0,+$rl));else$J=h($J);}return
Admin::get()->formatSelectionValue($J,$x,$k,$X);}function
select_array_value(array$nm,$X,$x,array$k,$rl){$I=[];foreach($nm
as$Y){if(is_array($Y))$I[]=select_array_value($Y,$X,$x,$k,$rl);else{$Nf=preg_replace('~(where%5B\d+%5D%5Bval%5D=)'.preg_quote(urlencode($X),"~")."~",'${1}'.urlencode($Y),$x);$I[]=select_value($Y,$Nf,$k,$rl);}}return$I;}function
is_blob(array$k){$Pl=Driver::get()->getStructuredTypes();$U=lang(107);return
preg_match('~blob|bytea|raw|file'.(DIALECT=="mssql"?'|binary|image':'').'~',$k["type"])&&!in_array($k["type"],isset($Pl[$U])?$Pl[$U]:[]);}function
is_mail($Y){return
is_string($Y)&&filter_var($Y,FILTER_VALIDATE_EMAIL);}function
is_web_url($Y){if(!is_string($Y)||!preg_match('~^(https?:)?//~i',$Y))return
false;$Ob=parse_url($Y);if(!$Ob)return
false;$bm=$Y;if(isset($Ob['path'])){$id=array_map('urlencode',explode('/',$Ob['path']));$bm=str_replace($Ob['path'],implode('/',$id),$bm);}if(isset($Ob['query'])){parse_str($Ob['query'],$gi);$bm=str_replace($Ob['query'],http_build_query($gi),$bm);}if(!isset($Ob['scheme']))$bm="https:$bm";return(bool)filter_var($bm,FILTER_VALIDATE_URL);}function
is_shortable($k){return$k&&!preg_match('~'.number_type().'|date|time|year~',$k["type"]);}function
host_port($N){return(preg_match('~^(:([^:].*)|(\[(.+)]|(([^:]+://)?[^:]+))(:(\d+))?)$~',$N,$z)?[(isset($z[4])?$z[4]:"").(isset($z[5])?$z[5]:""),$z[2].(isset($z[8])?$z[8]:"")]:[$N,'']);}function
count_rows($Q,$Z,$vf,$we){$H=" FROM ".table($Q).($Z?" WHERE ".implode(" AND ",$Z):"");return($vf&&(DIALECT=="sql"||count($we)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$we).")$H":"SELECT COUNT(*)".($vf?" FROM (SELECT 1$H GROUP BY ".implode(", ",$we).") x":$H));}function
slow_query($H){$h=Admin::get()->getDatabase();$wl=Admin::get()->getQueryTimeout();$sk=Driver::get()->slowQuery($H,$wl);$e=null;if(!$sk&&support("kill")){$e=connect();if($e&&($h==""||$e->selectDatabase($h))){$Kf=$e->getValue(connection_id());echo'<script',nonce(),'>
	const timeout = setTimeout(() => {
		ajax(\'',js_escape(ME),'script=kill\', function() {
		}, \'kill=',$Kf,'&token=',get_token(),'\');
	}, ',1000*$wl,');
</script>
';}}ob_flush();flush();$J=@get_key_vals(($sk?:$H),$e,false);if($e){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$J;}function
get_token(){$cj=rand(1,1e6);return($cj^$_SESSION["token"]).":$cj";}function
verify_token(){list($Bl,$cj)=explode(":",$_POST["token"]);return($cj^$_SESSION["token"])==$Bl&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],["","same-origin"]);}function
script($uk,$El="\n"){return"<script".nonce().">$uk</script>$El";}function
script_src($bm,$vc=false){return"<script src='".h($bm)."'".nonce().($vc?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
input_hidden($A,$Y=""){return"<input type='hidden' name='".h($A)."' value='".h($Y)."'>";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Ek){if($Ek===null||$Ek==="")return"";return
str_replace(["&","<","\"","'","\0"],["&amp;","&lt;","&quot;","&#039;","&#0;"],$Ek);}function
truncate_utf8($Ek,$v=80){if($Ek=="")return"";if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$v).")($)?)u",$Ek,$z))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$v).")($)?)",$Ek,$z);return
h($z[1]).(isset($z[2])?"":"<i>…</i>");}function
icon_solo($r){return
icon($r,"solo");}function
icon_chevron_down(){return
icon("chevron-down","chevron");}function
icon_chevron_right(){return
icon("chevron-down","chevron-right");}function
icon($r,$yb=null){$r=h($r);return"<svg class='icon ic-$r $yb'><use href='".link_files("icons.svg",[])."#$r'/></svg>";}function
checkbox($A,$Y,$tb,$Of="",$Dh="",$yb="",$Qf=""){$J="<input type='checkbox' name='$A' value='".h($Y)."'".($tb?" checked":"").($Qf?" aria-labelledby='$Qf'":"").">".($Dh?script("qsl('input').onclick = function () { $Dh };",""):"");return($Of!=""||$yb?"<label".($yb?" class='$yb'":"").">$J".h($Of)."</label>":$J);}function
optionlist($C,$Uj=null,$gm=false){$J="";foreach($C
as$Df=>$W){$Nh=[$Df=>$W];if(is_array($W)){$J
.='<optgroup label="'.h($Df).'">';$Nh=$W;}foreach($Nh
as$u=>$X)$J
.='<option'.($gm||is_string($u)?' value="'.h($u).'"':'').($Uj!==null&&($gm||is_string($u)?(string)$u:$X)===$Uj?' selected':'').'>'.h($X);if(is_array($W))$J
.='</optgroup>';}return$J;}function
html_select($A,$C,$Y="",$Ch="",$Qf="",$gm=false){static$Of=0;$Pf="";if(!$Qf&&substr(isset($C[""])?$C[""]:"",0,1)=="("){$Of++;$Qf="label-$Of";$Pf="<option value='' id='$Qf'>".h($C[""]);unset($C[""]);}return"<select name='".h($A)."'".($Qf?" aria-labelledby='$Qf'":"").">".$Pf.optionlist($C,$Y,$gm)."</select>".($Ch?script("qsl('select').onchange = function () { $Ch };",""):"");}function
html_radios($A,$C,$Y=""){$I="<span class='labels'>";foreach($C
as$u=>$X)$I
.="<label><input type='radio' name='".h($A)."' value='".h($u)."'".($u==$Y?" checked":"").">".h($X)."</label>";$I
.="</span>";return$I;}function
confirm($Fg="",$Wj="qsl('input')"){return
script("$Wj.onclick = () => confirm('".js_escape($Fg?:lang(108))."');","");}function
print_fieldset_start($r,$Zf,$Se,$vm=false,$tk=false){echo"<fieldset id='fieldset-$r' class='closable ".(!$vm?" closed":"")."'>","<legend><a href='#'>$Zf</a></legend>",icon($Se,"fieldset-icon jsonly"),"<div class='fieldset-content".($tk?" sortable":"")."'>";}function
print_fieldset_end($r,$tk=false){echo"</div>",script("initFieldset('$r');","");if($tk)echo
script("initSortable('#fieldset-$r .fieldset-content');","");echo"</fieldset>\n";}function
bold($cb,$yb=""){return($cb?" class='$yb active'":($yb?" class='$yb'":""));}function
js_escape($Ek){return
str_replace("<","\\x3C",addcslashes($Ek,"\r\n'\\"));}function
js_escape_key($Ek){return'"'.str_replace("<","\\x3C",addcslashes($Ek,"\r\n\t\"\\")).'"';}function
js_escape_re($Ek){return
addcslashes(preg_quote($Ek,"/"),"\r\n");}function
pagination($E,$gc){return"<li>".($E==$gc?"<strong>".($E+1)."</strong>":'<a href="'.h(remove_from_uri("page").($E?"&page=$E".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($E+1)."</a>")."</li>";}function
print_hidden_fields(array$Ti,array$We=[],$Ki=""){$I=false;foreach($Ti
as$u=>$X){if(!in_array($u,$We)){if(is_array($X))print_hidden_fields($X,[],$u);else{$I=true;echo
input_hidden($Ki?$Ki."[$u]":$u,$X);}}}return$I;}function
hidden_fields_get(){if(sid())echo
input_hidden(session_name(),session_id());if(SERVER!==null)echo
input_hidden(DRIVER,SERVER);echo
input_hidden("username",$_GET["username"]);}function
enum_input($Ma,array$k,$Y,$gd=null,$sb=false){preg_match_all("~'((?:[^']|'')*)'~",$k["length"],$_);$nm=$_[1];$ul=Admin::get()->getSettings()->getEnumAsSelectThreshold();$M=!$sb&&$ul!==null&&count($nm)>$ul;$U=$sb?"checkbox":"radio";$xa=$M?"selected":"checked";$I=$M?"<select $Ma>":"<span class='labels'>";if($M&&$k["null"]&&$gd!==""){$tb=$Y===null?$xa:"";$I
.="<option value='__adminneo_empty__' disabled $tb></option>";}if($gd!==null){$tb=(is_array($Y)?in_array($gd,$Y):$Y===$gd)?$xa:"";if($M)$I
.="<option value='$gd' $tb>".lang(109)."</option>";else$I
.="<label><input type='$U' $Ma value='$gd' $tb><i>".lang(109)."</i></label>";}foreach($nm
as$X){if($gd===""&&$X==="")continue;$X=stripcslashes(str_replace("''","'",$X));$tb=is_array($Y)?in_array($X,$Y):$Y===$X;$tb=$tb?$xa:"";$he=$X===""?("<i>".lang(109)."</i>"):h(Admin::get()->formatFieldValue($X,$k));if($M)$I
.="<option value='".h($X)."' $tb>$he</option>";else$I
.=" <label><input type='$U' $Ma value='".h($X)."' $tb>$he</label>";}$I
.=$M?"</select>":"</span>";return$I;}function
input($k,$Y,$p,$Qa=false){$A=h(bracket_escape($k["field"]));$Pl=Driver::get()->getTypes();$wf=isset($k["full_type"])&&Admin::get()->detectJson($k["full_type"],$Y,true);$rj=(DIALECT=="mssql"&&$k["auto_increment"]&&!$_POST["clone"]);if($rj&&!$_POST["save"])$p=null;if(in_array($k["type"],Driver::get()->getUserTypes())){$nd=type_values($Pl[$k["type"]]);if($nd){$k["type"]="enum";$k["length"]=$nd;}}$Ma=" name='fields[$A]' ".($Qa?" autofocus":"");$oe=(isset($_GET["select"])||$rj?["orig"=>lang(110)]:[])+Admin::get()->getFieldFunctions($k);$Ee=(in_array($p,$oe)||isset($oe[$p]));echo"<td class='function'>",Driver::get()->getUnconvertFunction($k)." ";if(count($oe)>1){$Uj=$p===null||$Ee?$p:"";echo"<select name='function[$A]'>".optionlist($oe,$Uj)."</select>",help_script_command("value.replace(/^SQL\$/, '')",true),script("qsl('select').onchange = functionChange;","");}else
echo
h(reset($oe));echo"</td><td>";$hf=Admin::get()->getFieldInput(isset($_GET["edit"])?$_GET["edit"]:null,$k,$Ma,$Y,$p);if($hf!="")echo$hf;elseif(preg_match('~bool~',$k["type"]))echo"<input type='hidden'$Ma value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked='checked'":"")."$Ma value='1'>";elseif($k["type"]=="enum")echo
enum_input($Ma,$k,$Y);elseif($k["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$k["length"],$_);echo"<span class='labels'>";foreach($_[1]as$X){$X=stripcslashes(str_replace("''","'",$X));$tb=$Y!==null&&in_array($X,explode(",",$Y),true);$tb=$tb?"checked":"";$he=$X===""?("<i>".lang(109)."</i>"):h(Admin::get()->formatFieldValue($X,$k));echo" <label><input type='checkbox' name='fields[$A][]' value='".h($X)."' $tb>$he</label>";}echo"</span>";}elseif(is_blob($k)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$A'>";elseif($wf)echo"<textarea $Ma cols='50' rows='12' class='jush-json'>".h($Y).'</textarea>';elseif(($pl=preg_match('~text|lob|memo|json~i',$k["type"]))||preg_match("~\n~",$Y)){if($pl&&DIALECT!="sqlite")$Ma
.=" cols='50' rows='12'";else{$L=min(12,substr_count($Y,"\n")+1);$Ma
.=" cols='30' rows='$L'";}echo"<textarea $Ma>".h($Y).'</textarea>';}else{$Bg=!preg_match('~int~',$k["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$k["length"],$z)?((preg_match("~binary~",$k["type"])?2:1)*$z[1]+($z[3]?1:0)+($z[2]&&!$k["unsigned"]?1:0)):($Pl&&$Pl[$k["type"]]?$Pl[$k["type"]]+($k["unsigned"]?0:1):0);if(DIALECT=='sql'&&Connection::get()->isMinVersion("5.6")&&preg_match('~time~',$k["type"]))$Bg+=7;echo"<input class='input'".((!$Ee||$p==="")&&preg_match('~(?<!o)int(?!er)~',$k["type"])&&!preg_match('~\[\]~',$k["full_type"])?" type='number'":"").($p!="now"?" value='".h($Y)."'":" data-last-value='".h($Y)."'").($Bg?" data-maxlength='$Bg'":"").(preg_match('~char|binary~',$k["type"])&&$Bg>20?" size='44'":"")."$Ma>";}$Je=Admin::get()->getFieldInputHint($_GET["edit"],$k,$Y);if($Je!="")echo" <span class='input-hint'>$Je</span>";if(count($oe)>1)echo
script("qs('select', qsl('td').previousSibling).onchange(null, true);","");$Wd=0;foreach($oe
as$u=>$X){if($u===""||!$X)break;$Wd++;}if(count($oe)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $Wd);");}function
process_input($k){$Ve=bracket_escape($k["field"]);$p=isset($_POST["function"][$Ve])?$_POST["function"][$Ve]:"";if($p=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?idf_escape($k["field"]):false);if($p=="NULL")return
Driver::get()->getNull();if(is_blob($k)&&ini_bool("file_uploads")){$m=get_file("fields-$Ve");if(!is_string($m))return
false;return
Driver::get()->quoteBinary($m);}$Y=isset($_POST["fields"][$Ve])?$_POST["fields"][$Ve]:(isset($_FILES["fields"]["name"][$Ve])?$_FILES["fields"]["name"][$Ve]:null);if($Y===null)return
false;if($k["auto_increment"]&&$Y=="")return
null;if($k["type"]=="set")$Y=implode(",",(array)$Y);if($p=="json"){$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}return
Admin::get()->processFieldInput($k,$Y,$p);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$wj=$pd=[];foreach(table_status("",true)as$Q=>$R){$Zk=Admin::get()->getTableName($R);if(!isset($R["Engine"])||$Zk==""||($_POST["tables"]&&!in_array($Q,$_POST["tables"])))continue;$I=Connection::get()->query("SELECT".limit("1 FROM ".table($Q)," WHERE ".implode(" AND ",Admin::get()->processSelectionSearch(fields($Q),[])),1));if($I&&!$I->fetchRow())continue;$x=h(ME."select=".urlencode($Q)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]));if($I)$wj[]="<li><a href='$x'>".icon("search")."$Zk</a></li>";else$pd[]="<div class='error'><a href='$x'>$Zk</a>: ".error()."</div>";}if($wj)echo"<ul class='links'>\n",implode("\n",$wj),"</ul>\n";if($pd)echo
implode("\n",$pd),"\n";if(!$wj&&!$pd)echo"<p class='message'>".lang(78)."</p>\n";}function
help_script($pl,$pk=false){return
script("initHelpFor(qsl('select, input'), '".h($pl)."', $pk);","");}function
help_script_command($Ib,$pk=false){return
script("initHelpFor(qsl('select, input'), (value) => { return $Ib; }, $pk);","");}function
edit_form($Q,$l,$K,$Zl){$Zk=Admin::get()->getTableName(table_status1($Q,true));$T=$Zl?lang(38):lang(111);page_header("$T: $Zk",["select"=>[$Q,$Zk],$T]);if($K===false){echo"<p class='error'>".lang(89)."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$cd=false;if(!$l)echo"<p class='error'>".lang(112)."\n";else{echo"<table class='box'>".script("qsl('table').onkeydown = onEditingKeydown;");$Qa=!$_POST;foreach($l
as$A=>$k){echo"<tr><th>".Admin::get()->getFieldName($k);$u=bracket_escape($A);$i=isset($_GET["preset"][$u])?$_GET["preset"][$u]:null;if($i===null){$i=$k["default"];if($k["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$i,$oj))$i=$oj[1];if(DIALECT=="sql"&&preg_match('~binary~',$k["type"]))$i=bin2hex($i);}$Y=($K!==null?($K[$A]!=""&&DIALECT=="sql"&&preg_match("~enum|set~",$k["type"])&&is_array($K[$A])?implode(",",$K[$A]):(is_bool($K[$A])?+$K[$A]:$K[$A])):(!$Zl&&$k["auto_increment"]?"":(isset($_GET["select"])?false:$i)));if(!$_POST["save"]&&is_string($Y))$Y=Admin::get()->formatFieldValue($Y,$k);if(($Zl&&!isset($k["privileges"]["update"]))||$k["generated"]){echo"<td class='function'></td><td>";if($Zl||!$k["generated"])echo
select_value($Y,'',$k,null);else
echo"<code class='jush-".DIALECT."'>",h($Y),"</code>";echo"</td>";}else{$cd=true;$p=($_POST["save"]?isset($_POST["function"][$u])?$_POST["function"][$u]:"":($Zl&&preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$Zl&&$Y==$k["default"]&&preg_match('~^[\w.]+\(~',$Y))$p="SQL";if(preg_match("~time~",$k["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$p="now";}if($k["type"]=="uuid"&&$Y=="uuid()"){$Y="";$p="uuid";}if($Qa!==false)$Qa=($k["auto_increment"]||$p=="now"||$p=="uuid"?null:true);input($k,$Y,$p,(bool)$Qa);if($Qa)$Qa=false;}echo"\n";}if(!support("table")&&!fields($Q))echo"<tr>"."<th><input class='input' name='field_keys[]'>".script("qsl('input').oninput = fieldChange;","")."<td class='function'>".html_select("field_funs[]",Admin::get()->getFieldFunctions(["null"=>isset($_GET["select"])]))."<td><input class='input' name='field_vals[]'>"."\n";echo"</table>\n",script("initToggles(gid('form'));");}echo"<p>";if($cd){echo"<input type='submit' class='button default' value='".lang(113)."'>\n";if(!isset($_GET["select"]))echo"<input type='submit' class='button' name='insert' value='".($Zl?lang(114):lang(115))."' title='Ctrl+Shift+Enter'>\n",($Zl?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".js_escape(lang(116))."…', this); };"):"");}echo($Zl?"<input type='submit' class='button' name='delete' value='".lang(117)."'>".confirm()."\n":"");if(isset($_GET["select"]))print_hidden_fields(["check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]]);echo
input_hidden("referer",isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),input_hidden("save","1"),input_token(),"</form>\n";}function
file_upload_form_script($ee,$if){$ug=ini_get("max_file_uploads");$zg=ini_get("upload_max_filesize");$_g=ini_bytes("upload_max_filesize");return
script("initFilesUploadForm('".js_escape($ee)."', '".js_escape($if)."', "."$ug, '".js_escape(lang(118,$ug,"'max_file_uploads'"))."', "."$_g, '".js_escape(lang(119,$zg,"'upload_max_filesize'"))."')");}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($Ek){$Fa=array_flip(str_split(compress_alphabet()));$v=strlen($Ek);$mm=($v?13*($v-1)/2-$Fa[$Ek[0]]:0);$Ya="";$uj=0;$vj=0;for($q=1;$q<$v;$q+=2){$uj=($uj<<13)+$Fa[$Ek[$q]]*93+$Fa[$Ek[$q+1]];$vj+=13;while($vj>=8&&$mm>=8){$vj-=8;$mm-=8;$Ya
.=chr($uj>>$vj);$uj&=(1<<$vj)-1;}}if($Ya=="")return"";return
function_exists('gzinflate')?gzinflate($Ya):inflate($Ya);}function
inflate($Ya){$ag=[3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258];$bg=[0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0];$Ic=[1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577];$Kc=[0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13];$J="";$G=0;do{$Ud=inflate_bits($Ya,$G,1);$U=inflate_bits($Ya,$G,2);if(!$U){$G=($G+7)&~7;$v=inflate_bits($Ya,$G,16);$G+=16;$J
.=substr($Ya,$G>>3,$v);$G+=$v<<3;}else{if($U==1){$mg=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$Lc=array_fill(0,30,5);}else{$lg=inflate_bits($Ya,$G,5)+257;$Jc=inflate_bits($Ya,$G,5)+1;$D=[16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15];$Lg=array_fill(0,19,0);$Kg=inflate_bits($Ya,$G,4)+4;for($q=0;$q<$Kg;$q++)$Lg[$D[$q]]=inflate_bits($Ya,$G,3);$Mg=inflate_table($Lg);$cg=[];while(count($cg)<$lg+$Jc){$Pk=inflate_symbol($Ya,$G,$Mg);if($Pk==16)$cg=array_merge($cg,array_fill(0,inflate_bits($Ya,$G,2)+3,end($cg)));elseif($Pk==17)$cg=array_merge($cg,array_fill(0,inflate_bits($Ya,$G,3)+3,0));elseif($Pk==18)$cg=array_merge($cg,array_fill(0,inflate_bits($Ya,$G,7)+11,0));else$cg[]=$Pk;}$mg=array_slice($cg,0,$lg);$Lc=array_slice($cg,$lg);}$ng=inflate_table($mg);$Nc=inflate_table($Lc);while(($Pk=inflate_symbol($Ya,$G,$ng))!=256){if($Pk<256)$J
.=chr($Pk);else{$v=$ag[$Pk-257]+inflate_bits($Ya,$G,$bg[$Pk-257]);$Mc=inflate_symbol($Ya,$G,$Nc);$sh=strlen($J)-$Ic[$Mc]-inflate_bits($Ya,$G,$Kc[$Mc]);for($q=0;$q<$v;$q++)$J
.=$J[$sh+$q];}}}}while(!$Ud);return$J;}function
inflate_bits($Ya,&$G,$ac){$J=0;for($q=0;$q<$ac;$q++){$J+=((ord($Ya[$G>>3])>>($G&7))&1)<<$q;$G++;}return$J;}function
inflate_table(array$cg){$Q=[];$zb=0;for($Za=1;$Za<=max($cg);$Za++){foreach($cg
as$Pk=>$v){if($v==$Za){$Q[$Za][$zb]=$Pk;$zb++;}}$zb<<=1;}return$Q;}function
inflate_symbol($Ya,&$G,array$Q){$zb=0;$Za=0;do{$zb=($zb<<1)+inflate_bits($Ya,$G,1);$Za++;}while(!isset($Q[$Za][$zb]));return$Q[$Za][$zb];}if(isset($_GET["file"]))load_compiled_file($_GET["file"]);function
load_compiled_file($n){if($n==""){http_response_code(404);exit;}if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){http_response_code(304);exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");ini_set("zlib.output_compression","1");$Bd=pathinfo($n,PATHINFO_EXTENSION);switch($Bd){case"css":header("Content-Type: text/css; charset=utf-8");break;case"js":header("Content-Type: text/javascript; charset=utf-8");break;case"ico":header("Content-Type: image/x-icon");break;case"png":header("Content-Type: image/png");break;case"svg":header("Content-Type: image/svg+xml");break;}switch($n){case'favicon-blue-0f5ce53a66b1e25395d0048da369f19e__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGZowEUFhM2G6A4QAJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+cUn3+6qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9jompoYUe3hg9tFCL+dNX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryEWPJCE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gDMBOIUjRp9w0AAAAASUVORK5CYII=';break;case'favicon-green-def78cfa7c465c8b0e9966e3eb87407d__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC+AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYVJREFUeNpiYOhiAFBfBxBohGEcxs/RmgQQEQDbQAACBAFCmBDgogLCZkN0BwiATBZQZNsAgmwAgyMAuRZCAtvkGLTy7sEHcFbvfWT4AbjH8f75Huq/CXBRgY8lQuzx29gjxBI+KnBtBDxFgJ+QO/1AgKw24AW+Q1La4rkm4DPEkk+agCvEkqsmQKxSBGwhlkSagA7Eko72DNs4QZRO8NIOUQk+1pAbreGjlGaI3ibENNDFEO+MIbpoJHz0jfYKRngCRymLEUQXABzQRxHOjYro4wBJGwAAEaYYoIeXRg8DTBHZ2oHo0TvwGmLJK01ADnNISnPkFAEA2jhC7nSEl2YHmnAMF1VMsEEMAQDE2GCCKlw4RlMT8Ad1OAnyeGbk4SSo46I9wzPGKMC5UwFjnLVneIEYMWZo/SOmgBZmiCGA7g98hSSIscM3Y4cYkuCLJsCDWOJpAjL4CEnpAzLaHXAxhSi9h2vjZVTDCnKjFWqw/jYsI8ACIX4ZIRYIUMbfEW3maO8YAIxSqCXQN5/tAAAAAElFTkSuQmCC';break;case'favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRATANhCAAEGAEGbIwEUFhM2G6A4QAJksoMi2AQTZAAZHAEsthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9fO1dVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwAv8gqS0xXNNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwMddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyBV7BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4FsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8CO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09ALYCTFLvUfsXAAAAAElFTkSuQmCC';break;case'favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGYIcFEBYbMhugM0AJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+e8Ln66qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9iompoYUe3hg9tFCL+dOX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryAWPJcE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gD0xZAYUYkFLAAAAAASUVORK5CYII=';break;case'favicon-red-c2ebb34a8df5aba28e15d87728a151df__6bb95962.ico':$f='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRAQY20AAAgQBQpghwEUFhM2G6A4QAJksoMi2AQTZBhgcAUgthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9eOZdVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwEv8gqS0xQtNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwKddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyB17BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4HsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8DO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09AE8YAEN5XivhAAAAAElFTkSuQmCC';break;case'favicon-blue-17e440832c1eac07527560a0d6f0d2ee__6bb95962.svg':$f='+<bATb3V?$so%el,wEIwK&mlYjGZ$a8-HGs8y$j)-UBmQx`Cf?>]C6?xmhS1<w
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
n9lrNOU01c?:p/5y16+Zkgo}`M)D6xm>7RiQ_b%p%ocllip!0).myrT"w[53iGRZBt8z<.d6p9("_WYy1v;v.xBx%3c3&hfawJgMtuxeflyK1.-:wQo"f_z&)W';break;case'jush-b3a93b18444da26820ff61746521dede__6f96e697.css':$f='+UEmPb3V?!K0u25Dm[994[Zg@N#Q)YOC=2R_hE~4)=>cbdia55M)rQq_opI7=E.gy$2_wn3[@yoG6r~P5/:mrvY<e>#2+8qezLLv^&nr;/Kkr(>?R(rf#PZ<Kx
br^LS(>/*E-?WzeLSW_J
;*l
(asND-)j;m4/f-BIQ%S$]jg`lK"7X[Woi<6n<ErGn[ASke
cM6fo
Ky:?d|y4Z`/MKF8_iz@9f#<b1@MaLgh0efOIpYz&+xn<6xNY
d<~>ajCRq4s@jh
caxtV~2DNi9ioWoHqA9#OBh[!x5h*jN+q=`bmxSYd@yVW[J$)|db!4XItVe2/XU=San(wzD4EHl0a(LT*+#/I{HkOQ@+p7OU>7LCKJ)XgXJ0ht%5=cOF]A#h3y>xj
CPbQe)?*P`i3V~D/=qVG-dKwTh&h0H`Bh6D#U{g+4O4p2=9CtsQ/6U+vL<<[BwoX?2A6[c[V]D4-0UY0<f68Rw&}-5Kr^"[Lrv)&Bo_Q>]coooyj>sL9EEvT;B"HxR.k8B
^Q5llt~q7xBV~/n!91bSK$-ui1OQU0Jb$`Vf`/xBJPix,!jg:C6a0@xf^+|r7RpN*I/2:M%^huBD0`%<qSsC;K:6QF=r``duu$_:GGnAJ+yY4!,e.H+17juw;`Qv?UzH/[xK7OTM3Z[qLq^Z^+TawmRd!sSIPOxE!SvhF<|rj:/l,BOJ
mSuF$F"Zd+H*kq9$y!*@F1uY
f-gLsy-15W-N0hLvuJRq9Wpsq]/I!y*0G?:_rlbTt6D;G*GS~^a@HY-C!&62>2?z$:C?FDZ<faV50J@TPaw$ho!P$-okZoO1r^E-sl/oF>NEK8#EKBBKVZ_<mqh4swu)jYp;|+Kvi!"N+01_T-&=mH9s.pB9Qu!OD3m.Qc<m(T*j6SBmlJy+%v{79w|"Bn(V=Rj"ND,Ek(pjZKL^zAr1(P>e8nI&&y=E]uD6uOTPjvG[(AR]Kbr`]M|A(;|wf`C%Khwq200kz[t6)?ZIb&Trvi7%2NO?:O%Ht2"ee:3Fvl!s
VMlfy|MRtX';break;case'jush-dark-f8dac59c6ad1018686e52a0e0357e421__2ec7793c.css':$f=',Gjwm6?!R"-YJmoGR`r@~cEv;#i.*-_KUyr[0$CF,>/n=#+liP*01.(73:+G.C]Ek+^-h&|hnGDq1:ccpxU98SxFh5MU%c+]DCcezAcUOWmDiL$
)yZA,ICx<`.i#E%U;lo*kf6u&LQx+!%1t]iP#G9;zGT,4U2"ha>hB#am`y1YU6$z!l#C%';break;case'jush-615bc0b9720a1de8edd2c6876a3495b6__5c4c1da6.js':$f='(hk]`!>p9CvwpHP(hq[*!NJF5FML(97K>/e-sd5Yd_qN;*HB8+(1wUgZ|7~w/mVWtDMgGM~htv^jmBm4eb;y*03o(V96q=%wGFJ+{#Pe*,NlHjy7BFQgK&`=9x
w}KS2<C8mz.p;8y@A]]KOHE=LG+ZJY69hi]OmLk*<i_y>A?DKpNY;Eh-vl?}:fK#yN/L7`JV"_gO#zW]A%bgx5C2iYwmPaMJuX*RsGB}TjvRN#`{,KUMi
bus4R/p(^I_~S@p#Wvq3(AP(py_~k|UY$"Y:=8^YN?&4x@x~MViEy:O(oAE*[
/b]C##(si>X2j<-;t|e++Ydp^P$&7@4#My:^SMObTpj$oO
]5_v>omWdd
W&>x5WND
<.qP/
.cxv8P@DKWL4pf5eG(E*ZwL>,cxPpMDW+CxX@`u:B*Sbo#T([)~.G
.
y2SX{A*Y8/8uHsuehCK>W7BWfyM&)($&l4;IZCY4v1M`"Syqu?KrH$x51f{:S`>/Rv9U"yTbR<3R@cHL3l!obgeXKa5w9ZBN#FH&_2S7~P=6MGsfaEaDHLW/5/:_*!#k8K2IDdF9e##HDk$e{V~qLqdZ}o<EXI=LR2Pw_J=322R&j!K+-+9XPj1BNq;y62xO8!~58w*]=,YI}<.A@!!AyD}IO0A"dE-2P-0)QMVuz,-v<1ZQ-5&?H6^.8HGF.0<@|nj=oK[^KwG44a-KS&{k4f+!&a>[Y"Z0w6/]
*}2]?%T>Z@,XS<8DjxbilrGIA{pIL.MHJOvZnzI0%~FT8cj/aO&5EL5TXi2_P4IkKS.ORpvNd(Ri
K?gF_.3sv8*r|_dTwC|+<OA6WqhD:k9.q6z)5r.dw:>9~-#
Jm<HsM>:]D4TBmv
[i><]8@
F_`]&TW?p2D^Ou[`EKqW0TsmX@*l)IBf$BOl5Y7,syLtMP+M[*LV1Zj<x%-]XyR2WoV;Q:1wmXRb<pgmQiJBW`VMah[pf[0K&K
Oo2EU^8EQUf.EyCw`-4f=.#JjC>b3dYB4[I:XlrI=Igp%PrS;c!/^B
T-Yn{N@`fjnHiT[)om&NC8`wywgf*bVLFha5}KXHE9]GBMYF)b2)A%|N{,Bi$y0M?q}I`6z,BUV6Gid6xn_SVV9$VeV;S+5mr+HGN]h`[CE$Za$fIe*SVR[c_;n]#x4.~kWawhx9:$O7TB?P=fA&>RhI*=0n6V8xoTUGN]+&ZN7Xy+49hi!X!nqq_F3XbxnTiX`A*O+)D>bjUXBi=yq`?q~
8a(q^^LQz]ho=jZigUF7benA#<>"H(7
yb/BVj>7$EfR!cYe3d2n5^{0b(,R|8bWdc6mX4-8-
+W&4PjB#0X{5E-yi?q%kNEv*76CTy6D%nfJE],&l$Vi9=aI?#Rsue;p,kiP6/St/cR`ga/*A3jCHNDrjiG7?McOVjkWqdDH[G+?wC,_8[/&3
Jd/G")SYCTnMI>&%-*6{Q:/swD9_mkD`K?FJ;}li?PH{G|?XL
"Y0IV2sn^uELH2i{!,BnQle`f2Zz-oN4A?3=ifbw]M^6ov410!^EaZ0Uc3N<_KjYe~kX(}KzUu",K=^gdP3F.OEFfFm#aiyZ5Q1%=-[c+^AtJ*E1MCIQdn53"w<G9w5w48CUhWR;f%axaV
K;=Tg0H6e]=aWBkUG*3m2t+tC3?TL8V;Z]UVS&C`lTH:)X<nBDy!jE<9L7!5wu0nyjyO=+4G>pSv(x
C!D),r0*o{wi$pEW39(!u(BgE(Aq2k24WL6GeZ!q6jTFltTCB

9Uqb,vj6!2Yc
28gE,nESwuO4-@_q%~krA4i)?=FralR"cl[96CCr%3yo+M_Kue:^u>7IfD"ClFD/8SGHV3P$"E#0L04"JqxS8@<Kc|t:p*fAV;!8:LXjJoQ{1[x3w>O;I0<JK"CBnReimy+!N]
_h]c-1*[(jee&Ouiy+NoKJ=n,&>Ii#9="+l<U#%MJ>V".qDGNw8iAy._@BA]>c42y$M@{H
>cS3=7H;%B2ixzHX"J+93g6r(A/BI#kBg_8(M`+N?g*L#o<#[q5=A@X])qmU*0$Q0}<Oe$N}u2Hg$mw
cc99O)1R"X?]/R4:mun&;*Gebs$j9=?E
d]Ta)INLAUmS77z)1j-(uPs0P"^>SQ;=Yy
bSoj2BVYla"VAMewH;4fLry#PY-^MEf?H*f+[xFp%CWUEZQD[|ZLnufDu(xeFzvkN.M0)Xetf8T0huA1?=xqxI@D61<JDMLurO!X(jW`Jw
@ZEwVD]dr_E/A-YNE!Re(W",[oy+2D,27YC_&5H4eokPfK>y+%%9G]}CRZaEak@glWE#Nh[:&k$L((Wy:KO<b
La=u3euZsjP:GgwajR*R&0gKiH"d3_SnW$jk7q9r{bjb"Xql>rOTFY>l4wDS:hI<|vBNRS
ks(LRG$)=qSMnUU^IzsE*meFc{TE4J.nx(/|N<?-^&wM;O2Tx-(5-4mk3bx.w>fyH<%>),3>pR,+f8&:GMySjb5>tl:5mz
Kp3oQ%|%|8@qqO:/<?G`&uqLjQ+5.F}7(A
&(P]AEGHoxRXX-uF
}4=h6,O;,&=0[w[wR!u&LSA]8oU>.N3Y/.$])`%.0TY73nNd57!nXCuS]+v[Rd|gqBu]WKte8p>kj%|D
yPNH4zU@4XjcoO?~@iPvfGqk$:t[UW:AHL9_dTrxFo1BGWgY:Q;GE8)-l}uv=LL0p9@iRgP(j+1c2[y6A(upby$B-Os}:P6!*~*N@^hmLaVm(RY~*H8MY:$sj%WgQipC
@N?FP
z$~@3.pb=Rhevrg@*Qek1FD_^sQ&R*d-I+T%Y?^"U%y->C]r-!QVM2|f6
>1rw!aR!.c8/;PQtA.&mpZ$6I`"*VG3J?NTr>#@a80RK(>,,|-aipDd88S-Qh
PU$uHvS&E,(8,e}+YU78`P|IJbiyMN~/pn8i<wY!Ej7Fi*A0&t%S;r0*mehTfv."^GfU;G+g_(9@x)98rVq)l:
:]6m$rGg1A3GUaZN:k.o)L:jn~3]m0Y"B.Ti
g"4B64fhv]oQj-SvJ%S5N&7k6P=&/&/Rqxv"G4r>7/#V/o5H$%EgIDX2w,`U_y.i|t}v<vCm!L"dNsa[t@BuS)"y@F{%74hxy_v;V#px{r)QVlB.H
(e<N*l5m`@=W>@P3bT_rkOl<+wI2*=_%ud?s^&F1c:#E6PaW!P^cJ/785HJ!!.uHA`hGjKkuzX@qHpM1/G:fFe#!Eq!aUh{<@I$2GtT%:H*9USeJQc[W]y3PEa*CnL$$qC4p;5>J]peFHH1@x8nr+3|4i_{Aw"T6{>@c*QyPFJ2brGiCQDBCEg
OmFJ1<mj_>HF="4}>Q]^Q(4uuL3y:rbF(%RUgcUwDYLmN38TH)_jI&Pm,B5h2HEE,g!xb.Po[L5Xmf]a-WslXjQQsHidDx99*e^fD>
yR$/UBo7)0je
yRBHUx1(9ADpOL]hr,*JyLR
0YZo57Ds`j:V:K9u/k)j-&uWyxtJR0lg">D"t.b!s[=uCT7ZK4ytA~(1[yOu#,S!nF1^hP<h8kJh3Y*njU&XNO`eCr+@A/3a,.A"6a)<QP<ZD6Zsw9+=`s`H.fpaVcFcuAPGa"2YCYa,F..Y4kV.dyf-.KZ{^fZv%:ZO!tB}+5Na$3C9OilPRABb``w%dR/
(3SYD?"O+XSdmSSn`w*,(xP%F"bmMat1/fs@)(QLs[];0(<f$=Q+_
+h`r"R=~8E%8HNJZw%+T5
IOM%
}$Tz(%ih:L/3Q#@<4)}%F`HQ!V=Wb8]%GKedt)ha*UX*`%;Pq7rJ_e2jd)x_Q6ipyQ~,[yfv;7n-bsS2*1JtE,citK$Ih<#I7GS
9XHP,Z/q*BKxbp)5_r.lPj[q[T09*V-0#YU-CGUUu@/w6]R3rvjpHEX1!4$D=C)!(d:T&L{7)(Q*sO~,NvI&EvvKXAURGRGZ]]bi:-r(m<~p=drrs%d9q71eV>@6v&jLP!ja]fZA#YkfE7Hn.@sa/>njq7N[~-k1N,8hQF=t;EBxH,Gv3]j/S:uC#;#V
/3^C@nFdXZyg+F+C):VWf}.GJCLq?Qf`j`mY9*L|B60Smb[!4JRg(N61A>6".]*!96>3$]QE0?14/??DH]w+Sy2x*0P;F9@&%_Qd0F
h69+31@AcFEh3@%v
?wGV]haRMZ<]*s,Hix#6`sC$KXpl*"p&uq*|J?<%w@M83Z_d).RCWr7RsX=UJP!!Y6ubkt?JEAirJ%vUuCjWy)jX!M)FBqy
X#(r]0C"gC2C`(O`nd%vGX.i-1A*k0M
NYMpS:)|;*T(Z7MLFh=5@qQCVF;f)&K}mRX)n!9.&a6r>P&a,E+D=z#ql1QIxU>SONn8?JpP
X*.5TQ{OHw+Job57;TCiS?dSt4/Nn>@j/^qTJ+W5b4O-mUmq5;#$".hDDk:$3.S^h&_&8D#A[U5NB5u6>omL54zr#yiLB0C;004NBm7^:7^U`chD_&
D[bR?GLMh1&t@Ws*CL]E550rA%`=:}*dO94O?lw$*nn%wfw$2d!@2{@@)_-9_$(@xvVu^.B}FhgeixDM@;/QW#@/B`1*IRD|=?L;<F/l&:0}
ii,l{S_Ya(j
pEkU~?l#jXXrAWLq*vV^DkErOP&%a0Dq"i"B,f/0k[eyF^9&fS+aYO(2w
gM<t-vdFdY
a;Ugj?mvT!2Ft"Ws*8m/F-Vn:bb=E#%qh^C9[3fVsAUPAtV28s0yU>h*.D?70JssUsO6>U9
Edp"v&.hZ!<qJ/;fE*XjX)ZrCZu#*xXy
E&`CfCRK_svBefG>Ti?1
TU`LK@
B8usx+(rYcM+5TbIQW;L]b%l;1)4.hq
{Rp);hYi/W>%2M$<GF5ZknOfd?ffqi=)CsC<QChwN?}i23m,Hsgu#1^$.IQ:.Uzc,_R^t).k}T-fqZP+
UsdN`P:oA%Q:4Qmm?=m2%fFN.oFx#!Zlc{>*,ZwYyfy6,Hg_H$t70=<g2}AP]#Vj#OnaG`$Md<%zWe%?K~-"A_pEGb+MK|VT2Hh0q#Mo"thB73PI4Q_;.1>eG6Wx])MKxI:a`UP`E=EbC%i08ZRun?4GJ~#n.wH-Qw6[rlMB0JSb6CaOjG%tYLUabdeto?-Q-bAoHK]~@"A8x#6kQwY~J]p_=}w{UV/C.Yf0
#F{;~NR-A7sV"O*_2A%n;u[S<mfq;8DSDo)l{)lLW-*s8:_)P5$G=J;T"FX@b+tv{wh9dr*pQN
d%47j3.[mQECK>K2ccNCM["y`W#jQ/M{#}NF87":0Xo/C8MLK2(BX3*4)U+QSSFO-qS"w>k^NohbLzUxiR.cc{mpz(q]ymv&c_8VGFk!;A&z.,li@+c(08d$0;],q+AS_zVd5#GEZ(6(b?q,jOd}<$(9.7<0yNV{N+3nNpd!r1>^Tx0SR66?:2Jl<Px*m@%Mu/+NP^qY6YqXLm<`Hny,o$n9_H*o33xEcT=Ei16EX+$Uv;,]sG:"uq-b`.tRK(PZ"=gO7(PSI@e9,80Kn1j9naLPu12<p.K/c1b41_k.W#w<eFnMAb=BmW_!=NL1FXm"HQjwm`
&^4p&78
yMdmLHQk<`|TvoA2KHq,sv[-h_n>f",5pImV7uoZt1[jM"D_,RC/mXqa>EN-lw-):nd7`fQTHBtufy[BPm.b*Du1hlK&lA
=Mr}o!UbWjN$Wdaw0fFtjXm`g,+Nr}J[j!=RHB,w#m(MFZ#eULGNooWp;8,cuy;3KbZy^qh;L>obq$/;Y@scsOM.)~R3F$TiluPw(NF(GO7{odc0=<sMxo!LEhQ"cAAVaXo!n^6Y/c#KF,<RDC+;oe6n$Ho7w-K8pBZ=lsufm8GgO=BY^dUp59J0rmS]F]
1ANM+/{6r@)[$
u7WA1vE$^I<
$,*t7QDPeHJw=%-Lc8MTWYD"lA3!{f7MbwYV@!vP(&R!Kfc:Y3700jJV!Fn+A2"iO8foN-&Z01w&Qb>>&tT^q"Z8
K,oX[)o7E&a:GKMjtXNirug3dTFAiH
P5{``Q%^u#]ZSW7HVG[KutRHF:HfT,^XS2EEZ*h=c&7tWjA`T5XeHaNb"]n<Q;!lD2>*@jM2jZlC9C,FVS,#>8R`1W5ubr}s;qE$1^#`:T5q7H@],@P!KZNEmTl9?vqQ1>ySA`Hu`O*0{$zNwww00joUb!bmwuwH%tlL#6FdNA5jPJ#^Y1-CYID@i_wEY3j<7Q`L<BUgT3SuK<1h*A|iyHm390,aI]"]y*3!*^^F~SF5Qjhx9aQn^["O3Uq;b-VU;kxL/IYVH_;WAPoN^lv;DJd"y0tOW=w%o
o)(eL];q+HJwcCHpLQZs`asRVpUK(@g
yJL`Ad,tm8HAD_y$NP*xe2lfatBB+"G[(JD3H2wAJ
hJR`P]Hd,
x1U[2=!NJAf%wWQyu"p[#"qb)"r?Ow,3~o
p0%v1t._%19QQ1Yrf%=C-wL5`1*zOlG|?1*J5,d7W"_=t/cp`Lu^iZvO*X_N!C(Iv&%T2.dm8(%f/@"W#8bt:Dy*5%a|c2/G5rM^EWy6St*XJe>/Pfb*&w@ctdB;cU+UnC<S4Nv~#fscby2]]H("1db9WOF
P}q^o^WKU;Z:kqn<l;Fz/[.9C]%#yM.81UA|Y>OUNw0
-?#3vY7JNPY6/iy#;jrjE"bMO~!>`q$z$1"B(&IKiuex-M^lw1rEe-?+p[KfmZ.2EA@Q[tZ3sj*-M$g0o6Wd<_@+mR%kDVL`Ukn!@$qxhX.*hz^<@a9_5=N)72id/xr)pVuHLED702.!l|yAr=C[P#)<4o%bk?3Bw{ulZs:}.HyLGv60Wy6NkvK3</%I!uc
<JvWUHB*qF:^cTc@H%@DM=Z@h`D~?p_kT@eZ$;o2N
eoy"I,*}@c0#,,p?Yj/qHq2Q2a45!yNHBO1&pT!,XeqX8IG7P(/y"~fI1bbSMbbYq
;tDRda)f@G)S@=ENMl>Mw+&f
PM%4shY8W?d[-COn1+b7n)evGA|J&ILkko!!RkrwuFz4`"~95^oB349^aQtp{l2si?cY)av@H0xW`24NNhct{U#%A2!brRS*$?XUum<L5b9_J"e=ZU+9/%PG1=fb9)z@HU
,?.=T#eR:5Fi2rq$I,fB?/d
g:7vQsgG4<Hy)2+!C3wh=53"-UjNv8aUD[N%Z%t{Gl8|Yd*LLRxqhg,z]ZU*SG]-O$J!JF/`q_*B^iY00b(fnBo4OaZ6EmTLOHSOb5Uj?}Dvl2"B=.a5g#w,:22CAZJoL/eSmO]x]!"?
8P;iNkD2j-42JG-=-DuagB{_[vNM#Z.VKeMA/#w.cI)$u$x(PPV/Y"YS82s&=h`!FNrSVv;$,3,gQd}J;.2"OC_Y44.07q;R@iWW]tqdpG](37zsBb}>>ok^]b5O<wi_xNL-:KKyM&*#zA+u1#%XdI-
$B2c7riwLd4ymy0G
A$)yPK(".K3$#v7DoEN~o_ur^]m@,%=)*!1`)n63$cNZ.TFIX=)nOVHz<s."!ypaO>;whLtSy7*QjY
ta*qQHny.ti6GVYw6_gW~j"m}.Gf
j^A@KI_kw*4+p{bnSot1bhD4&aeKr[2HVx(Rjd.w96Vtm.g8bl=>llK&<Q*fDCPpkoEvOp5P$zCH7rJdpg;D/MKiRj>,F)cb0168>0k,Ms7(SqG:4ts{W_d5oRYP,d.T2Bv8hMJjjGPWp|qQoPRCc&kTx?+WlEV0lM74Y0y=BIk6C4jF/q0W>^=F%Dv&]Tq:g<jvEOF?*<0.Q]bzcZ
?uQ,A0~QBw1`D[z0g,q(NP}fDT?F/T%fNW<-Bto8k$N9F;D!n<BJGNCeuS)Vf$jG">pQP^/W-CLN.L5gm%qGBeNa8XTFJPIuKg8:Mh13T&5:eQJ=DU?Wz+&+1eG.#Vlx18q[2^E
512&_6Z=}S,kGs2dWNJ*Ls)BK1]__:yW]S7#H::rhC5g1,,8Avz,e[(;}i?"Z*RxeWIG3_4@U$n/-bf!TnQF1@Z8nD|o_P~#~eST0Fd1mqHS.4WOS4xvQ..x|aH6=$d&?BTI1EG]|#`2*Ke?&f:0gFvu{2XF@b"pao4&^eJSiu{--a/wuxfm7Lg@pg0_uTQI(VEwK7z;mI6O0Vm2AC4b$0,bN-(G&NoQ!FcU)KP8_<bM0K1-y4.K7W>>^cpO]YRP|hF=BbwUP>+c6jM?H/H4#m7?Nh,_1"Ay}YU2W7qi+k<1p7WF=(m)-O_hzV^"N/e>K
qPGktD*k)B}HVPU"O83m#,,swy2uy-Jqec3<pBTm{0s>l1YATny/>B4J!&8By5m]pv>uyfz4c]iRI6..a0KGKF{X@Ce<QJ+7o1[yHglIYZdpXqHkp]:$htj:EB.#R3lBQp
6[Z>?=HX&Pk%*g!;,09~Znv+9Q/KJjj@_WB!4B$yV&*V1[H@[2EI(@B>,Qh}w^.>OqxvF8!#$!0fLX
cow[YEqQRG
sE.cfF@SF]v1H0s4co+;Z|p@+w&Myqn$[RA/J-!tF|Z/)w
/I]ddjWDT_Z/=%s]J^I_91rLDCPm9NZJT08xxuFhd]Ve=Qc^vla3s8th4dy21J5i(GTN6F1m`oQc_&CrXO**3u*?wZixQFwq+o<
9.;JDw&V
#HLce3+Ec}H#)j5*ZJq=c{XjaLkpd1)9D3j@T}1Z.BO/R>b
IyCE7<3^:n$X8i>!o<u"C`x|N1h%2Qg?X8U|g8NG%kuvoz@}^kQZ.~C2!?vQ&k]2`$cJ;M_%l[&F0NCQtzk^2>ngiXO>S7Q@$wRha"k3hmq,F4+.W>e`_4Sq1(NhhM;c
0?}j`+Dg!aM(PA-x8AvEv(!
&Dr%gk&lWMVw2_$KV)5fBjH#>j`:7M5"u-H91hWt)
~:O]gIS=qe)S
Ugdtb.Az._u;wY?a;*M/T?U]_buJSpfH#*v"OmnmOTHj<`RQOLQZXwR94#Oo[?h{>Y"fEPKEEy>pA|S??H3Hhq;C[.JHSO.%`X?D^b3T163X`r3Fp%?N4=a:
fZuraa)+&@S2kLK4ylH2^xvlyIcRVEfTAvc!?U@p*SFy]Ob>ai+Dk^cs>Alc;B+C{W}^{osZ{Gg_rxaHl5aO@Td:MULF.aR>#t
L]u7;V&he{XJ9.Cq(@hu:zN<6(Nv?{gRpx4$fJ6FqH4K=-@ejCE8bS6JSYm!(+UPE/!104K,n[fe]+Yx9T`EQWb7h
D{t^W8ZoE/]7S@2g!iV2]8:N"sk]ClpqP,:?#nBR.N+Gey-+5UL2+dH1a|0~L:Kc#gOkTgT_+]m?T::|;^0;
/nnGBb9#O4J`C"n=dq8dh*OR1U<iMu_])Fo-yl^SYV_u4%e<2>dkeG@2uitgTS^C?&Ujp5%nd:.Uj
S`8[y,79hDD,{m<o5e[]pG+(vdq[7ItY[8Y6AbS
d:n
qWf&yq+Zn17))N#5_wiFh`gMH:~@L
q+oGcvD=#s2gwc4f3rzv|<ZG&h"g$$A_g>eALk:Aa-ZhZw~
]HqY>j,Nqh-ks-W1uKDNP_gGn4FW>Fu:JF4h0Lwq.Adu7kW`LbqA+&"OWu/+rfIv21Sk`kVCv.x1G/gu2obRlpza]dYM]P?mgsXbag?qcy6PYk`76_RvJIt-QXiH`M.eAIw/qGvJE>KInfg<JW*u*C">SB(cIgo-.dPrag:GBFw5>mOj/b!aSs&]e%DDpIGh&]>ZgS6ZXf-
?Tv%&Z)+6<G)%f._c9{BVIji-r3%<fS0Imc1CTwA+;vOQm_;n+/iGb(J0ff8[;_t-%lB;qtrz1CUrK,_mf)?84kp=_|V2p3tTP.L@mMy!"Nx^94_McQEa;A%>V+-+%_UV1FQbo^jyt.kJqz3Pg/^^ApAB?(g;P?&<9zu1WH#aC>N+^iDpHx:)(LY,G0QcG"*@M$EV:xs$wKESt~,G9GmfT5)o]kEFc:4Y)|1r(}9"lds+d$IQuDtOEz/~Wz,!xGnEDO2pqw/5$*"N9hRl[VPU<:n."G>.[J5IiFZ_AR)5xH^D<406qjH=8[-:-=ID:j.I*)y.&Ob!a@V`C3U(elC9"6.{$Uuw#N:Xp.gn]{^dRqe<D-Lt[qX$v+qK./R$%j%%;HSGvdC7-nl?kdC+)XiO!/UCW#+Ls7^;X*Df%<-!W*Oy?dakk^Yc+(yjL"db;>,(adkpdA4c=O/<^pV!/aOXNa:K6
]N6{$)"24k/v1IT^"]rF^D]u%x<:j}qi.F2:Z2CqeD?M8PJB%$%3QGYrL(^N!v#?3xxe!Q7$8cdC%F$!Og/g4`RWEC_k;fPb.fk}3D.*o_McQ{xd#pbz67Bb4FFJe6,(aN)})K
BB{Lh&H]u]`$F-?;6,H!g4C@-a;&D8~bO)f:]`2fud4v@ti^3[T-?!+:O6pP~vt%el[b_4uc%9G$eT2D]^BSJ,N)~a1e:a1u$xnJmFIdX37gK)?9e?-6-QBv8"vt^YcDYg3Cre|F:K.q>cRE?e^&Yv2#2C)9Iu2X{i<_!LgsqM9I@w$(n7
MBk[vOt7PDm~=5yvc@b],AMYs3Mxt4-jkwlgfiHmtldFwpQnqRtS^!Amfkp{URZ@w:rqF^&`R)GoDl<m[m=/wUKwJp+ZMm(Xy2MYrJ*YslvX`XL|i;O{MbyayrJ6=hqo$_;qg=t]$<!LaMQltVu(w0K/R#mjc.b1QZ_t-K4J,7gxS|myeH>WgB2,d^kdM2;y5rH`;=v
DIM$&N?X29([Ws3PG!q}%Apcm|y&EEZ^l!RyepT_GnOoOnyR->s2KLN[4oY=Y22"JUHGb]@}bcl_R?
qVwi)PE9;Sl_(m8Zj%%+ZG]^KjR3jErgaOuYhAofbRn7kGzu{UqYPUgY)+H6&fP[DA27bc|P>v"V6e(W9Bmi[6>c}YGb[d@oDvK.?o`b]`B3/:nZxkw`%pE:iYAKu&XrDz"rRWRi)BZ<j[V2</Fw(yXwuoGH+ne5{ug#5k(O(A*a:E*B7neGgN~Hl^99$KAXosqNMtnn%5(*T(]K4)sUbK/YFE6GTF1V(:oWzD>W4V$Rc?:ma-7DR5$knFQJMeTn+LT2r/@%es`L2GkEC#pS=n3?)?c#TT;rT9&+,d9g9<n>zO"l2d$jD`vg~c`K+!z.wxzP"-(G1JysmL/-.kA3v/Vkoc=MyYU^[6UtV"gdr%:Nf3*>$SK[BkIe!@&`pw@nuLXtTVL$da,MU9DmXawqrl?bm6_Q%kqui[aXFMny!WN,_n-!er131R{]TV_K4&}yBw+,Vv?n@#e#;2Pv-GP8S4:,my8z)K/n.w7bmu}m:6[29c@P[K4nbK@nCXAc|ms)eH|h[7?Q2[H
8^BbzX.KKtA-aEu[818YB"Z=CDe>/b/hkju[]vyZ>/xm=b4eT#ytutWWk@FtAcZS!cXxih3`+`fndffuyt5jmkdk9OC_sl5ERmq`bFg:h<XV
[i7cHf/bPDTtK4n}N7k55~YxizP"9v"nx_K,qw7zMfs[%@W3m=*a)ykS46FSB/68)2`]4#oDQ!a=/bkGjAbS)Y@~ehP>ZA=pX1To^txt^X3RSrU|"U6]L=ym.`Si6|ZY_ts,3E78.`@Q#X3!=h0mOK.oO.yBo#l^K93rbKq@7JJED?,hF^/8g/OX7*
cb]xXo?)U1oM3s{8O_=xZ95nuKOa-,w^b)1>Sp1PyD(kRX$z%^QetxZ6]<"v$vm_:!$m`X(RiLWw?yrY$)"3^4<]]XYz%/dpfS!APl.vu/^_1[%8ll&NL7Y_wnMxc;Gtn8P
ldlyblM_psPJON?!F]e;I2~D8m<Yqo#h{kIK//esYao@:*Vu?#=*H@bF_d,dpY^b1bI!bLXwX:"xbG,c_K52-4@VEC4Gq/`.*;X@{MZ*rY-bzI>_0z&f30*Y5wPcDg7xjI@TSdav_>njAFVc@?L;<2*:K0o@t2&1
PWHK=hL.]e%V:Y(iJYD%9Btf!/g20Lh4N.xFNP0C"
!]bUK?q?h`gpxaxMz!-}Qq!FYxKr3%51LJOHO`M
tACTAGNP<i.][S[[xD,fe
Sjb]_pw7u-tCm?,8MzZk[Yw8CV-e;ykgheLA;-T[Xm)sEEh2up=Aff>meV<GYv)z%EMC3wmrP%E6g%0X_fhKD8s!gLtE334X3LD!@A<SCeY/k(WC4w6E^<nal5({rfV32M#y#{aombN0HWjj]D"n)r+lWrHtBi.u,^yfn+:?_SF5j:a4tY>|-E_cfpF+GTya"wSLL8FV9bTrX(yTDU"p:MdBUtAd#lRUF,_B$[dl7>A3%93ds*ihAT<R]e!XT[<W
@qGIlbZbIcS=)R1""7t/HY2KJxSb_s
/)
J)xeGBh`{&Rf^$#6EoN<FbQEb8E&Ln-w|,e[:Zui2mB
v"/8BwWN7A*AU,g4r"p7Di+kRB%AUYdFa@AuRi$/ovoj2r/y?8uw*ysL`=tFKGTYXU$E%@MD0X7o|cBlF"<K.4![fk<":*k+=/1Jp@16g.^ypM"E,YGK>@i:ZT58o0=F[5rhSt_,=q|c_10nb5{dZ(%Q{t=,oC=)LJ@Ai/~L)puseL/<w(AYI8g`dtY$
2)$./3!O;.tIN0:<wrhj
~6%cl8Art8Q=TdkxS=<shCf63waa_L?]]/,Y%3&gIk6afCXfFmR]b:&[OIz<Zl>t&?!>eS^7Pu$4REJ
#aLP!4Q-(#$A,+Vhha~5IOd[K/b.KBua<ww<3mT:I4yj-D]E#Wrr+#r$0%n<B<qC+(c#K[Q8_2!.A0;k"pYX[+LSz?xNi>7AxL#CCq,MFk3f+D(TE-
i"2_^F>e/G9g]6J8,v]3yI9y1H"Py!.Mx
!v(R7*7APr,[UUALDoEg!2uI8KfnH:=iWg-{WZdce[
:$Q"?x.%i3636XOiby)-?)F5c9d+^>?e>0`_B?LPuu>!ryPw~7qtSdYhvIsK]cyojUF-hVm+hcyOHBU8.vlMwr4x#J:cDduyk"j8&*l%^Q57(I&Z>X%U,r|D{$ij]Ekf+gI,fEVeTpJ!>f^hZwg
:N8cmg7rLT5"4teEvezt:OQE+#{@XKPhvAk.NH{*-trP}Y
QAO{7.[%-6`|Z!"G6mTRSSG<q{
8Hkjv31)U_lZ!<F(fc!n{,p3`s_=%^(D+K*`%$!C_!??[W$mB"
LU.j,23M[JRwU/]4VD->pcL23SV2PS74:nAA:);s)/`<>jvfXp^l+C8E95>A0X69f]dE^eQ
8;-bwx<x,`Q%pQjWDKBbVgM5QNvi-
:`VDKu,-/G.uh/&q
h<~w._W(H
EvgZo*FTE=V>o]~>&06P(,j=m-,Q
3Y$mKq%+s}U{NzUCj>;!?dH:&jwC(tS*,W*Iv$?~N/ySN!1H(Cqm3wo.#(S,IZrh2rgE!=aU<0x(o/"(tz@Bu3cAYOy5dwNu,b5{Gx0]]drx8F+%X
8;TLyBtr"`+7/dfa*hT9;D;.=2ti+w.yKFfYS^D<MEwm$;!DDlIKv]`,U}U!Q+3wLj")wh^L8>Y*_>T9Wlv5bYnZ?P$GNb<PK,@/<Zwk%.`g2ZNkWH[F:UjxZ>YX`{KZA9;vvYb-2!,AY*:
jumhsg?<knNx
[_N_hUtj"i>M=f3"zPf($Th&v#],?sF6/j<Kz%k1mkD@/AL%zNpgz<JuR^/(:)`EFw)sxo~siJMU=Dpg@bc[;o<ho6d0"wbL5J,m^n>JQ;=&#ccw:7.#{K5ysssn
KNw0,~Bao<M+]$#XA+NJQ_s89tQ5_RAto[YH<YQmFj[w?`![8sM#??mR(/l
aV_"lCA*;1?sgs!CneIHSO^_?Q=tj/Q0r</]ge:`-_TtCwfx3$T=INm?r25xhb?
&!<mvGd`48[B?Ty:7ed"F
59mZ3xy1_8AV
fe@*!h=%Hw9%gRcqc)0L=ix;9O"Avc)]]WkM-PY&KTwA/>}Mghq>`!)y
G+q@yKe49Ex>K4qwQs5gB4]o/fY7a5yI#XZ(i.t;y%7`P0/sf1>lfkkWrWDr%6Ut7GfrG2$8HR+(L?0vV:LV&"L/R}6,m~;PRos3x&a)^)4+kL%2nRtWHKa=!g_|SLpjO*k4m%65K)]y.4w<c`[l9`Z?;aG@W>s__FQ;ZHa+lwd?S[[5[=G]7@PNuNZ+vTiAm>0O?}5YpS_-Vp>zF?)WrIb?5kRLpM%^>IPF"(>69*]ARHx_i6?9MZEclZF%J.
>5{jpbHbbAq;([AHI&{^H*0VMv[BILGlIB@`"4@Z_+SbfU]KxUi`Gwg]U_o#h[uCGQYn%3JTPO!;
[h2MKZ"}Og`8XaXayGCkKE^+g^YEec?.Ks`Nsi.VBJKGB^FX8
pP14G8A4j[?Q3`Tt`P])Sb.3"k*1nvalaN"FJ&wDt1D/f~qD)2!>+mSlccVo:oxeuFTEAp.*oGY$Qslv;@Fd,MB#k7wP@aEH3Ib#^d]Vo03Fndmv>+]41GGBX!j#TdQsa<5H"ic)!#`W.]Ox"t?Q`-o%
Rke*HF7<*mr$[ldwDw
0uF?6$1Hc-tK)GE]4u$KeS5RXEl"5SE/Sg`>9]Nf=ZGUszsQ3h3?h!H*M_P,a^hHCK(mWHy`aJKf$@H$!^aNUF3&Nid5R@;7xP*BLY;eHy%l`KQy.$Eo5"lYxLVYw-2Uk1*DIzu1X4`^pFLZBMmj87db>;RCB|El)Dn#N)izWf6d^U8{^E5j
LHJvq9ER(j?B[:Kj8K4k1K8B*$aK8_HM`2*7FoZsH*v]h5;LY@~1Z>j,EPDYFu0/3FTA8x43`Wm2q,X9iSVg:rwSNu(Z9tw=TxSEi%5ih9(<Nmj,v50x[i]an;C-H9}"3uF`*`ou8Gig4MNFw^Pwe[mCn]uShhjL+x^m6p_"[%[A1>.UdPF=$UE
Kz!Ff(6uw1iLrGZxX]=A8iE^6*g.a
a@5gpAJJhK>V1OtP,+b7b;5dp#N62Hf_6OVYgq/4%
)C^yac[Ul?k.c9T()Cc2W-jQ+EkDsaq&l<sdH8Am^7k8n#j$V?boJ^=6!<R"ugI&g?.08By5/*^`p;TkkXYgj6
&*"@=<dRg-Q<[j9X44Xa]s(pY(X.$QsIQ&b,a?b]emm!juwdwXNu05/aZnS@$!_Bm.&r:A>hMCk/VAo62tKt5Kx^9%/EE3SnNeBbwBV}n&?=]sIa?iIH!m/po:TwVvbf6y+}l%
C;IhY$;#_fmk<e@8swI%qWqhZt^3%eOw$5ogWtR=FYM=Hm(tbT
3*$w?5w@V}9jL>g*%NQ7fC-UlDNHQz2[n]1[a"50<uyl6X<)rR1$=YJn$oo)+,EgdOt(^IOpOMtd1P#02Y=ChP./!O#>5=Mo[8k<dq?lawxhLS&^wru;C=*;#nCr@)Y>HP7#h2nAb:ccs>><YVURNkl1K,dCxAo0.csTj+7[6<SCy}<wdyc$dQ1bc{K4G;q!aR_R?>WA>-gzfdYM!JG+A}dc6v_P&}[p01jjqV+<cfl/Q%a7fuBcRfd?h&UJ)JL=*I<Sw57`r8$XK3^dO%GyxX6Foa0()IL*Dj`R&&G
rb/@h=U%7a,.eA/Ym-^<=@
OAcCG,P[ll`
&FOb%+(i/u|r7H01k"l`myJnqXY8O8qZkyfG]L9nCpKi~,A!wNDL[Ddz"Efy(Z9!pK#2+)C*(TBNm;z5RUYLA)"QnMM1U6C(qg/Stmo)Wx1#t]tBxeFz"-0p["[3q1=(rHp%;#_/8"|pPTJ-J6y%Iobt+kj>JK=*]k&yrmiy~w^twu6l;qloTl|RlKjSE$Sp-cwR|fL7C_b%C=i
>fMMLtQ)cSi8Z5vSE2eo0p<95gkQgL&3:ZG<7tM;[Dg2cyS*YKT3s!^`VJkvwyyRZyssrn?n.sC;oJvg"$]kxLUt3OF@Rtm`@->,3iI8;>28?y_Ki?w6UV[9IXbVLfIoap9wFn*v>:yl^pxg+I)y%-p4WPbj&Rz,6X:i}ABGxTb%
8IeIo&!PSehY$[eMX*NXhMC5L!<Hw!"Clc^*#Rd:<QG?!(K,s?-S#ku[)5K3%7@;Y?7XIZ8TYR012cyxBUz%hVo+2.c6:-DLeY
s9&VcxgX#jq`tRs/TK$9LNxp==a/cNQK=d$s1Ejc}L."}kS
2,K!JBHoZEPY[gLF*s!xDRS-2VH499(yp*RmjLC[kz(gwu
gq/hlbrF:y^oXBw5&7->wB9xqRc=K"2#iA(Lyg+68WvFbfT==Wn6>[17(wI$!l7YY20i#l,
x!#!1b8{>^5;C&G7:(ka4J5K)O+sMz+I]E=b2;-!7xY7kHOjq
.:uccLc
Xz_-5ReD;%$_R{f5tJ%qlGBGyg0B67YMco.B!jmc;
T~#3-tkXZr5VhHlMIyK,qW/{@t_#*_"!Gw[7N^m@PE$;R_b+q+<B=zyWSG0A$M.(O-ZF^6[_$L<=F<9-53%W9B1@DImKKMIidB&6B]-,?)$`8tYZKvpgf|r::F0@d~*fO^HKK_V%`JU.TxCcE}:@tk`AE;3S
"+Z"Lk=iGI):<p.u`%Gs]gv)}SXamcg4f7d9frEZIEh<L:1$`86"$V>MZ,(H:Eg6~r|GN8gjz741t4VEUWQ?^clwkd}nMlly3bHZ>M5:?KtvzdPKFPXE&edm7^u5o8@K+,q%46a6bw[wJo;$~+=W.t|=kfKy1:GYST9:(hU=M8TGu3%1[%ZtG>|"E%Oyu"rY<&!PC-
$ItX@<
yXL88eAPI(V"|R^4s,|L_-.y&kKCgVR<_tYrVl:q{*yN1>Bj#xKSubq"^IzK3:KBn$U0uF?DW$&2~F>ggUQd;DJ<Af5#*!Rpe<Oy
g"V~u+OmOG"k+(HJ0dP}4@`b)3<6#(,L=iox(y=[l?
<xiel*&4p4Rg"!-eDN-Jm
5[c;1(Y_"W{4AcxF@Rn,7Extw=kPytf-#._Ua?
0m;OT_8VTw[<r_Vz"NOr1X7re[j%1;kaI-Bpl
Ali]pb:MY6L=(?^U]u%-:RB%x_qcofYru_S+tO@(`-$tH
>K1t!<OEB^Mnf#Txjmb*NQt#w?Dh3B9@3-cN[(11h}RhUA9qje),-?6IZoSWP_^^<Ohfe(%^2O3p2t)S,%,!.M;OS:;aBtT.-Q<V/OO;({&(+ruX59;9+!mU3~CjB)Ca&<bsY+1WBoA1/1m/F0ho:-[b2y>X$?R`u)$pve!C=8n=2n=<JJs5,$Ct3}?}9T$D++N5XO!Rf]HdecI
!X2"=u*=B-*WD0!a;Hj[2[:j,4ZZY
0.cyo{W(q%eSD9!}lEiZ<mwSr~=ms5@%*.e;Yy=W#E+B*gmj"vX&8yszOkD%RuC{:;(*Rm$viO
EexWKSB-i(_WO={T)1hc+;MX&I2sB8CyDYLizW7Yu<YC%aY@}tgPge"hMcPtLdxE"xQ_XXA/tI_wM5%,k$oP~N
CVt]Ubw7(x]:
-%ry<$0.WmkGo)v.Z_wG3"aK^4<t@T{OY&+ES_&$t6JYTL3yghhio,[RsPDA`0KnKT^[PP.A/*S#eOD$yl<(F@Ky[dP&jKU$Fjar?U@klWKg]JsU/4I>7Nuc@XhM.D"_)V6?>eVGa0J$t3=c*>xI4+UxgYKroY%;lCvcFSyI
r]S``h%hYw$~&H7$K@6_eQg"qwh/?6#%,MSWrQg-UF`sx`lHWzMTtRgs
5peK[6
U0KVO4_q+0WCp}s#VC/:3yWTh`.(gO`xV1Jg
ObY4N[#f5Je"R%
q]vl.:kMk,%P_[#tdn+m4#L,G^3<qB,b55AZI]gWMo:f3j^|%~P>60j-#S^|a39+Ym.z4DD)DD+@al&/;"^^qcf|>pD>r&1aF%b3UDx$pv-xAj2$D}7NA}.WnB)Tc$UF/sp6)b7tY
[I[n6+1eEG;j7w)#Tku1@|^*RkG:1Gj;9wp}v[]Or>3LT@hZ[pklP^g~ax>J#o/3S?`UcA_=uySM_A+aZdWkhJ38RngMMa!$?O,"B.+7<$>Ja.X_ja)X25UNjo;.n5o`Thw],zO10n&v7xb~2HXR&>ov.Shj()w
ic%vWp#H&4VA&x!;`w%LJ#kQ#yGQ)$Y(
qruX4iIQP^s;_WUbi?e"v"srrY"Fk]x
a8A.6I4J=etYv,c<0fjJz""%zfC+[no0K+v]8,LO}5]VNWePK/jS];a]t^Bu{x~oBSDVXd1%OJzgEOw0Y//^we29=;<d
P^K0._&wiV+b%D]=et7Wb`gZ?G4McI(ICWPhIx1;"n/=Z=U3dqX*TAd4LYY~yim-8dI1YA0G!W&>="w?%)/y6/^e3((Nsw.k#Mh[
MW|#xV<F-/h5f//&ALsf{H!js/>=UKa-]q@(xLsV?V8#*`nYXi3T4wVMYnlWoFvlTX.s2+k$-of=Fm_O;S>Ab?_!lD/_?MGSDYY
MjcZUu<D+V33&e)8"lWjc*`iUT#Se=Gd<4X)i2bB
[H+eplu60K?[@5e-k:
6:jHT=ctc>jS<=kQxN4nZ5Ak
`(Jr&=sWiP0b>oE[Wvv$7X#+INX_cp(;#i@OD-9$$oZCQBB+)#c5/fDsB1Q-M.T/a,M-NW9bU-R<2s)SVW?3F<Su<9a]8<H{8;a|/Zor90OGQyIrKD4Spq%Rj$Yt@
ua&%$l;/7Bm*b]aYq}x9:~sn3anAqc&u3&,[WB?K/j1G!^59V}1BFhV2rFhTNe?]y("@o~"I?(3<rC+I;PLCDe42ON_;gaV|?:$R(Tjf6_+Gvv:KTmkL5,CB.TQFX86+0<h|;I<K-6o.2aj)-$#Cwf?3@b58_4+s=y>c@P7-k&Y;Wrb>sYO`essK@3mJyOFiFhUCxgW10@x##r>5!1uPXc15!sKv);dQTDj>^fDAV-r.>oJ!mA7Ax$g!S.IkTq%cHDd?SRK7wxD_y8kAK
/xW5x9<l(oWt8?`!0|qtVcZ0Gny.G{grZp9}ZwB80He1>2sO^7dT]CAdCd:"KRO~.M%QFtT
-y];Er_^3]3V;*Su
6DJB|vi<"*Sej3`P"Y/rV/SSd?v)
hTWIy/,;>8(ZxUDXl%86nbPgvm+j$q>r?zMvk20]GgH2Bw-N,>]wJdVmVFV|WJr2#=Gu/CNxMG*L3@QJI}KpSM?<][tFY9_`8YyI^o,u!H9AN@N@y$Ab,fuiHk5?=w`s-Y&-8FX=2)o#x!/#VGh&Vkq):88u^]P_)byXiWKSl{PMheELd~:YR7:Qjmt"S+c[n.;|j%0vF-Qy]BTyz"gl01]&
Gu!wB&9uR;|5=HN]0c(=d<A0X4@I1<OS9lfF;,PnahU+
".2lPUMRs$GaGka!/H,Wc;8AJR51d"tdNE@s@d4iRCc{D>jn
Rc
w|mE*p_F:wL_ELF>R
"a=|fVAwsjAE#:3(x5>qOpE3Gs!WYnfrf%WrGk5DVfx4-)dwh;x]l+"d#P"s.$cWY2bp=V/|&/Xz8%_)#MM0[/8-ZfyeHqPuGr8|sFdAZooyt$0@N(twPJio#_C%N}u+FS>_qPj"/LW;AC$=6n[1W-_@Q>in`;1FuDHI#x&&20husVE=K}W_0&jO<V&FEMN=x2+^#fMm(shm:
f0Pr7wgQ):jHM6w]ovLpe;g->_c8LKX!.{r@CEI#RaRscmA19p0j@M"c?Ci;T8d/K0g1C$8]k&t:_)a(LYuZh)>sR88*F5!@#1Xmy+A!nLq}&Fo86
hJ6t_5r=A!k.Z
I
M|/@IFw^AY8TDF)k`~t]g_N8F"+0m4F_5%F,o~.$R=r~Ou`&)]_)u"@S]_*J*gLY<
5B<EMYd7rxGTV7pHPhU5^lsW6NR7d&>LaH4nuyj!M5svs9u.B65q*yIcA~oH+pu&a.(aP1Tn)UQIK?%8pDxE1+pLw*6%*63}6,Q1C($[BZf1rR#(a1L?ppX2:|A#`Pfm4@gf5DAWPBYO[!Nc_6&8?rT-#Z3)j~"nnRik;5lydb!Z7,w:=8X84m7~/L:=d(ua()`,1A:l4.3GZ,3?IKWg^loyDTYz
0hI=!"lS"
u_e%=n5*_i$V
J"Hz"^AU]d?:($+J4)wM2K5,[5/Z-U*q;n+0gS)bq?V.plh=b;:kN5d8
272&HYvl<8yLl
FcDk>[2")d0c%LX+nu~9ihvPoNl%|<BAjp-XDU".7"*9TM_XI8&dt*KT|6[1"9O$WV):#7ri`dW,nZ+2hQL]uR%<1%.7s2`jw61>IKaOh[o;O(z;ce0xyQ;Q_84$QE^RUk&>eW;EKVGk<d
&)w6rjuzyA*#7jE^v,;)hGGVYKp*pP*hCpppeGS}iyrOKH.""sc%l}ryd+q/6_OeEL.G][4Nsb
]HdtLd/;#xWT
Z/<F3.*bZO
9$43oryG@cPNH*r/K6&;^1HkhN&:&G7TAK[a~;j:tOQ"M*]wvF8d3)[ktE,?2^cKBKh7Q-=C1%1*;iejt?DI$$]LRWPJ8_^d+$A9ni#23P_[P>!o?^QIy3})PgVyQbdw>Z
Q+$7Fz1YM([/iuq7,i3jlH)"@kjXwt>Rq4JFgzX>%xCg!|uUUjOtKfN3hPp01jOr-!Og2m%_cZQ:LBdtg3yjg:-W:2FDg[MF/kJ58&A^aq"#4PFqOg9).=o^fSZS9D8C,tEj2&BGm(;1SII7:LX]
|ow?,V56{M&Z1IXNH$gHz!gpr!~o[?@G!^q.
F}YWx)VDi?BAhme*kN>n[k6jfEp6;oPbok1|8rX%>8FNk%n0t
*iM+6|fVSEuCF,F:VA&u49n8Y<ocA=B{uBkYl]M8&|29ZQ#64.7aNE]Ia{hCb~2C
8+KBsp"gm*h+HN*6ITR&?&.XF`3;#Nqq*IfVmNB6=UgTS3fb/?MVd6|tL/+h`t.qHDe8Anp(b7
#bI?#0h=kbVyr"C=(YRXN;p23P_17!]YoCP9tAk2Z6;/Crubq3PwX~Rrt8t^L5+6<K0(x8W#YR]6#eB[>x@:ORNwK]tV3~elQoM+;Sb]%/;=
LS@b}3um4lry`%1>q1KVgFWO:!q$^wMso?|=VTKqiL%@co_ST>I38rOip/mr7XzHoBB3|*y!_rM@ye7/M%oAEuY?+x@Hxo~-6#TZ$?d>Ic3Pd6w,,h~x]
:1ks2;VCF6.KCIq+*2zu>$q?d(gAO:49^7Ok5oE7=XdU>(;!hL%8W`$GG_)y(SsENhWdBaji^0s3MZ``/q9=p$9/!Pl=![?K?F>EnIQ873J!]Iye}EU
j"*%0C6kKV+9~$hN])2DR&="3A+=4haVI:71A3e8T)8%#eE_j3=_~1J62.8YT9sTE8xSpOvI@^ddqey*.D>*>)?b5@P_kV.QYM,Obchk8tYmEUE8xY<-gl@NE!
T$8)hCTw>|2C$jU#npQqki3Hb4p)Jkx[0Xo;
AZp#q[<!%.4q;(CLt
l;M2EtqT34@,f`S7=1I`s

LxfbCfJhe~7AT|<A2J/{SMD4!NKzL`@^(Q?<"tM_6R*sj?cOAG:1F++!(4,-?sO,J&Y_fw>8$W:NU?2W51E+5/;5pp.q-[Gch6C~=c_nlORtj?F
Vv;~@|IHCR[F.rk>JLSw(BizrhOI];1~E=(cmI*BPe+Jk&inO_q]"v6OXQYZm:5kRTs@ZA$<f/4iweL9",pp>#p.EnU8(f+1yl*sXjCX(_hk1WTyC
QX$4izRIHs:BFWt6piZ%@n))5r:>"zOZ,@]G2k9;yqo59ka]s|#|gb$*Zf>pl([M-wqXj@;tNA&mqA#"=uBFolVqn!)3U|:jf0lUgb^LE5-~K7.T@Tck$6>UG>H$<"aqU<+H/kO4:0tvoNqAO;6ulu]u<B4[;)
%#{?6=D)"JV9q/TDN0+v$4zVZ=
KZei((S{Mxg)lf"=9[jBb}r0X|@a?I7Gb)G=g"?Ze>"&f*$YqtQKVnic;^pD8F.Y:DE{5LjC1_0th.Q%+oTlAkj2pNp"la^YA+3a5TXKN?f[`n)"pPuM,y;ND^<mP0/!PVeXDvKB%%Qo@@`8qy=#6qkKgLmW%+=_5tLy7EbXk+pyX:k84U(2A*0aC6:|+51ICvi&[/ZobCf
12JaMH"yWXiei!)j*8D9u`KPH:fuXJNR2k/0-Kt`x6;{W*-kuHe}n<42OP4`QONWYDroef:4D_<wR(E]WAXn87&_bIfM82,rEUVb^x(U+)Wy`ZVn08UDLX[^%*1{5AG(eZG^]6[QMu=_ms$%$LWi?Q_Wl6izM3^ej|Ou>
p#-TO8mytQCo_u)G.
`m[c_0/=d}Xpc#J5e{)y38]%E{Yuc]q=]4RMg&@C/$>P"DJxF|V-=m/0%Y:"O<DX9ACw4sf}J1W7IA7H,h_V!jb3Swh:8
Q(g1>>$vcK).>f4]-.bC&N1S@$Y1q9ZLdw7TTU:$v8B;WqJ)+hGoJJaqax#Q&L28v9!9ry#rptOnpg>$kgo{;aO+gnS8!OYzQ>EK-B!M`
4_/-8S&T7L8?(O1Js:X3^:G=%c#U=^M>"U<6PE;=(i*"y"&:^&UrPa1"Q2)@?q)0]lE^jJ,3oMVZ?d)}>B9).uxnpD7ex)8Kv~_,->%0_Z2Mte6NCa#tE|o"q~D*g}pMsGi:7JRmoyt9o=ty6]a4hl[gp0rC.,)Olj;,qP!e[:N{"s^KTfo-i|UH;wg}0~bH;wT+CboVh80y,yat8D*CJ.u"R.NhIkU(`F9K%j3}8LA(a},HS|9.YNoC7o5@sX^4A#X}:/`.!xPzTW+V(reca3!OL/#a]IIP&L^j:e]m3H:X#2r<^jp(*=YS<(FQO_G-j><NThRF!@!HewPP"dWPEsNoI/Nhxg$op
5ksVWUl"(q@/oE@G$4.K8E$
9M_1)>aa.jb=Oq7]v)]]+Y#^ut[&$k4SLx1>"<9yQhV.X6%%#06w^"Un-anh9Q>~I$]~08:?Az0i0`0p0@J1T]nYd
CNMEgj4/jkX}AG3?>J)/QCfdQcxLJGDX]A>J.ZD.8MmIRv>@pf=k;~F*
WZC4k<,y)1O`TA(:V.Mm(b*b3li%Fi(p5(@Q]K10*I;)fKOCVVh,xia.@]WVF^$NKR93UX~=;0nN^p!E#DV^gNLk|Z>YPpt%F;W64Cof1WFH>>BlBLPZs%{F9HV]d"."q+"5I&eA3;ul`,IH4.p_:h4YqDRn
^;/tQi!h/f)IpKGvIl):2l:J/=Om;BrxIkE
,s4krm0|0ov{+d.L)?@M8p"^dS$jt9B+?cl3j%^6E2vkEL;

L.5al
&nws+b!80q^Su5}/d8DQ*f|M{"!I+4_*:F<
r5yF}IEG1XT=H^g^^hMx~<&g1/3WVRwLf,LwDS^#SPq.gV*Nf.jbZ]fHq-^k=n6[O_-@fm<+;_6FAV~EPXCdU!3h9"PQ1=d)4(*U2unKiga+~Gn$9Amds"rPa3iZuC!Un!KtoU$4OVN#+?;6w8~0,frhZJ@r6"Thw[!1t/+J|s`LO>02+C:7sS-Pw5b5Vc7l}$_&1hPVI(pPTP+d3Ub3EY[R[&CSUgw7NH@B#tt9MwY@avE4["QLN=}YJql^
KCyM*z%=:IaZV_*[,grw<Xt{f]bqrx/rMG`SQ~aOvT;hfCWjA#T3)2s@<1;{0J8`rN
BexE@h>YpQBW9o-U}O4N@2|?N<bsGc&`bl]*{%~&.gOOk5TD#)>?~9RELQn_pDN0;jT+^b;@rE=WI!60/avz(KzF{@V,_+=4Xy;Tqj(`nm)cZNLH!:d0#
tWy`OY9+*h2Kp(aLm:Jm_0PyZuv&LW~!QRC3T6@Z#)I`E&x=Y/M;D5J1~RP0O2y^yJF.pqHf^8L8KiDZr1$tikk=+VZ@`:X;<by^qlytqS2&v[32Y/Co2(IQCZ,GsUi`7#J@.4+25Vd>$?vZFD%@aQ#:n[zoCg74YNW1~Cv>2!YvbY12y*S%,@u[n(5wV5m;]dpQGMMH+E|e$@(^%Fx(BQ|.^4d!uTvFk%
O"$]m9HdvoPV"1]{=OqP[(9v4H`GZcc0Rvfu1?rsy2&2QoCIoc^BPnU![sXzC@[*y3[jBnr7w0f#yepsDs:7QO[h"=WAuw"{E!BdVb[Nb8P&_Kyw-W,BP]NzM~W>&s@-*fBJV5]g6@6Cl{huaMg4]i(*wYn}&W
:9^E%_*8rJbG_I>ZJE0mFL_cC7C
X)",9PG2+#ISn"5&g+qijZPYzQt5/`#W<>|wA9Y9)+
hHV$FQ2Jx1BX/9[?+OKz#s@"paS?+5TZd.dqvhOo;W#+I-1f`>jFY`BpZRgf-Cd[orhBwR-}A?Z`i2?Ub.Pr?K0J,kCrXxd|VOo?gOt==Vc"Y1^[YY8598M
@<4dB7@ws8On)tyY]iX@n]`}ab
3X392MJCYWFg15r>LS&?dW1Td`h25bwXaYD)W%1F7n,pe%<%tio!|3#Su:3nQ=e9X]sU!/:e$=G]S`A,B4cRZ@vUhU}fQ`P&w<7d^3mC/>fEWLS7k;SpE(SFg@4rIHWU;IUqZsVV>Jo]ex|WiD1
95i<^%[3(+uF02wl11UfluOEOeup<bZMf."
Q*7ua-86/U}Ol^g87CRa(_KQ_5{k-<:^/!.5Dh#YV(]:ZEG#W$6Q+v#!GwP=$8n,kiu^o4`K1>x){3^V:7[stHvm]B5PN2"KdujW;C>4W3x#*`09]DuMR_%wcC1VbKJA8wR
v"hv3+0E*(:@gONCYa:H}YU$Hj{_O)7@{<FRr_qtG1D;@/ofEQH"yB_(G,&:nU+Q^BP&++9qT
KLQ^`?OBMjn*11*Tn
$"BGd%q@EI>F)j)ux70[ral&FL(Ob!$8{)E71Gn`PZl0wZ}4P#pD0St!~iS
bt3J9cM*l7moXBBs+$KJH&cW}aC27nn2j9TypevgIa2B"W|0.tDN(IO7u`0h
1N/iE~s)kpQZ1H>!nq3TM_A,2Kxhp~W~L:AFF`Hj$#.(q+d#VP,@8C[$/h7B1x2b%aMa/CX%a#6gNba|E7#}mo,UT$g/s<7etVJ63![JS6q?FcAO<7r[Cp>*iIZep[?{l_T#^7u;EL"$td(cFPxbQ!0D1P8?;m"_N2_p[N5``;(2AiG<(ry
Ie
GIQ1sg6-
U"V4#~Kp=bC,Gb"uu>Vm*t#+<gC$=I42F4>~kim>,D&J8!2%7w8&_ledNPt5q^^WTr+A$5wI9^RI1.VkOxs<;AaweCR&Lp+1W|pM4$^z]$/SgISSYS)0Fp;5Sc&ZfrrZ0(9+J$u1;I`aozLk`}I:Rt5VT8knFd9U"s+.I&5=bl>})(Hil4AaumL8-PNn8&jnWyI[0GxJZHMnIhecP5PlCZ@@ib7HQ:+zN5.TD8,{jyqE8-axP,&%F2jn*HACu<&=kBSoUcD]8(Pju#9,l!=91bY:(*+=.`*3BIB.&n3vlU.aFm!;;~9An&!Z$sf4LYUT+E)UQH>DPq!MdC&SF;@*S!I5JV*
]12xrMUEF16Xmr(8s2*!
iX&eycuB"K[AR"s5ylp2s8;:c@"2eqWV`CAnj*V^9m
.Hkph+Fy`m?%LCU,2DUQN0<8TZkF,T,";M!%4ryECa#-jvd>qD$<w#x/("wS]&+2Xa*KdPSE@(7BI,d,-s(f?]!7t.OR9K3PCDZuZX!@;]7&5k;*V+;Y@6-L?O-v]J!;Y|i:R^U,gP$y(mM-MI
MWDRc#<-V!/W?upkTDsL5H53oQ=5z=+BH(LqwYhYE;rljY$k>/1FkIJ1J?F(_J-=z_#$s1D*)=q%{EGXf6ayWEMYpwN1Ugi?.O61GY)KW7GVG8.A#Pd780?3iqXjOwgimi_ZG+/0KQ#Qs*zko`z1d@pWT)6gOS~l.K)tp-pmhhLmn/,:5d`Gm%1"+^9"B+dJ~iI%-VN`7`ZPrgQ/p0&:@vPC(E8yqwso7Trk.<NBBCpHb:Enq;F*EvVGDlu3W`H9p$MAk+_gf0h"!j.P:U+2caa2fYA44C)Cj&C(v<p_z.[Z$v01$K~U"
D?3$y;r.
1i,04]8rf:!_jkd?#&@<7#)n?k-zmMDj0E5;(wdCf/&Dqawbynjf%GXNc]5+U6PTit%Z0g%9xwk1s{,}3CZC#q)J
!:]>3HzphFz^PC~<FNG?Zn1ZJ<_nz>?Z@8X]wO|FJgY/:o1h-S"0IfwJ{Ngae/dkgWpS+qPPT>Y:qVg[rw}E~hY9$XuK:jrjfMs(^UXm#e{Q9@Df{.VLqkkU7@pNh>W#YY8=:-D/Fh.C!V5BS[<ktOqZ;D)KOWI+GM`,kL|*j&QtE%]4C/:r8j*$9.2-FA=0&:$USaQjr0y9
]t4D%~?;<rDbm"Yl8GZd$vDm(1>w,5bjyk%?)"0F?;F"K|rs4C@U.,x.?F7:+sll-b9bl.tCduq?8cChOYN;ca1^kC=V#+S#0WPyPpH0a)=7uLEAw4/{LkCsPk"SCmR*hf%t&Q(6!gH%5^&_C@h;4*vl6GC&q&q$&*qzc$fN)$nV)#hBs<G36ft~T?]YSMIs!2f~o0QXLiZ7x-%`8Cg5cJ;E(h,,@`Vwt_ayRNB{U{:"BtU4u}-fFou.MT
)J,--bB8ZygUArf1"U&J,dGS8pfs~yOUQfrhn%_kEPMn1d@[
[;.l6g!c%)WRPDZKX|Z6p;j%r!8L-2cO-GLCdgx>n]gTrE(5V2@Tr)^fJUd@Q+Fk.Ap)64q!Yf-w&G5K^wbQL,qp[Nux#4<~!dmI<5[wnsNgs|%LY~*@;]KvBX`aszKZAjqM13g*hDckY{K*j[DU+yF:hU6kZ^Gb=dGlw%)Qh-#0I
)}Xhbh$c0]ilyRE`(&(zO_#5-Mp0$N3aEn&#t|g5vzYYYs665?,4TZ"3g}+pTn$]o5&
FcK;U#vrj}m8%7_uk_i,vXS5YF_M.ta3lKy|v[RRffV)fYN6Ig@K(X-y#;%wp>q?nh6i#d_[7:<J"oGz8|r$(H3$>a9(Rs]8!O(zA]U[8o3IC%7@NkTC9&-w.ghS2~E*9J[X^;up!".uFm9!kj!A>SS%,@K?$8BfmU7Y$q0[kMNFPJ92(cc|hef-yMTfBT9Wgjsw(Bby+HE<"o7>efvR?m9Y*sp?_=;75BeaHe4*I>rp1xLGqbh-^k:eetc,NH+(O(5}1f=uc,*C,
opi@yoDeJ=l>htS>7[Aw(tr>U%&&Y|Qr)1:yQ%-dG?/]sX/MBC4a2YaK"T;hkORnpX/}$Z9Hx4>4%;W/J6KOjg5^VhP
C*
c3_pq6ciUuFv$BwBapG6e1QIz8*X|u.ofgq5fC:@&VoOe[?;fQ?%M0L+j#dFDJ*A*+Mva3/V*6yNzMV3/:;?8_8RP0<.9mIw{dqDtcXN<E0U_/n[nE<;>0@l
8px+oNNuJICgHM
sQhv!Z|<&e6JVk`vfET0nsg#f4"_WD.J=
BJwT{4+qK=(hiP2MC8F50Kw7-@ZKgh@(H8oj;lJG
]t$PWh;f)~-i9`qBmi=yl[!p
I*?ZcS|,%^G@fimE
JACs*@wEXX51F9_#VhBcr"+zTK-}KgCsxl=Gw:h:pwAId1noM$nKaOBj15TU?n3EDTeHlO,dq9$Dc[-uZnvYhvqV5}ViW[M(H85hJkrwWH)O1&Dn:sf4_iE]R*@R.X3cR"R1V]R?%>@tNY;#iou",/w--T8-x%xsaD*FVcI^oBtUV.,7t1/,#.;hl$NgYPRB$|*xNs<cBWn{!RCTB8AfLM)U#YH|Y=n7O)?%RE[{t~hoEx@<hM`/oE(F`tG]iR>?8VRS,Q]i(yw{]ttbKn6=&Cp%1~[jS^)<dmE"@8TOap;SdA)#E`R"S%6XgGd7a.<008(L*g.g"n`<bxZ^NLbyp~:O(Ol_W.$QTXkA+U6dLaeT$^2&(&5"r"=h/E0O<j7h^`hfINYi&:m>c/X~8k)"]<b&Z]Y
`lL=O43bieNoy"m%m$<u,81KSI,QQcG*,jPn
aewe(ksVyW#BvMA5,(-)PPMadBE-pNR?1.Cgq<j5^&@-0gzk]A?0oR,%
*,k/USdh2ie4C4P(*rt51^-UK9=)k$i*,w+Q/|E~
1=N
Xd(k}A?3sRHIvsPe$f0YQUl%R*7S1WMElTwGK3>8[qOhUf`V|6GIb*t<D@<7iu<t$(6H%rTMUjuMC@f:1#E.r4O75S-:G0^#Z;_/T8Bd4:xK-4`_<<
&IxoXCXeE</Vl%S;A4e`S>6fJ<mwo%PnXtYyP*Wf8U=ceb3c$|w;"Pog(DX#^[5uN/HF_Y/>jnwc^W
&&9+@*Y8]o7?2EZS+/G$j]0Qc=&!R!i<A9MDcu#dbMdp+SyXY<v2%EJERS.cF>ySuX+tv#8aM?o<X!1b{pPDE4^[QEq+7A9gg7l2-5#B?
4UV
Z2nTqG
^kcNo2y:=P<}N6&z0f]m(e
+s1A?f1vfn/1cabM7a*@h#DKJs?3JsO
;M~.yR+SnTMq:QB(BtiPK,<0%4$(?jz1U5?vs,b=`"1<V!Y$[,g[f28k.-*,]I7H(Iy$zIbU%Reu&8KUJlNw4d6eUAV<
Tzd/(Z>ALH]XmML%+.(2>rO#dN_BAGH::v(n0>Su]=I=>8E%P*U)9~^y_gS[D/-f6Uq)Bd?KCk!!-L]:4!EmZ}qgjAV:<bB72U
kO?0t0jTO+O3:GH
{#6:Y_S_q"`[M+f4<q$A47Cr7NP7BFs6/JpZ"05@^nRE<xr!Y2JQ(q@[uIcH@nDtI030>/yx/B(8BuJYLKiX.6C"<;Ypexr$@*h_]4TY0kLR8"y.Ob7^B*:G~+LiP5OB8)P$~CS"&A36;9kE1u|W2;D,*%jsS.xkkH/I!hM@8;.E,+UlDExMB0))QQgKwi~3thL"+P50a>gUyW3]$4<.P
q0W&"rb"L)x1UkoIgV"hzwvaUaO@EnWn&nNq1T=%1&a8vj(e!/VSFQkr)`@/!I;91Fr+*gB9D>f.[D8KPnq+:n~o&CWu*o?FY&,JWBxZN!KrEqGPSsaSDlu*s;!gmo-0*t4*Qt3mS^lpVh)*pJLa7svz#0s8Li?
U.sY#rB89V:?I?RikEX`*+"`=gXq`EN6;=a*Ik
txgf?s4lFJJ83gX|tA$5a2WF,ON8Cq4xvEn
S&9XGCV@>$jzg2Z@1`Bgj3X?(dez?-n)/:q*.#Ba*nh:QpN$.i;w*o$fIoA@0p_4q3ZOiW8]x4]-k3?#VC,v`&#&8OR~u@^#u!bHQNx@bQ/XVigB3=7kbZ1dt41@OzIj3!IWOd;f4SGWQ[ZW@vN+M/x?=;wA#kO.8*SOyG';break;case'icons-70163a2695280bf75edba563e7b5471b__2ec7793c.svg':$f='!n1FChAWz1*tCrXP%
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
d%=.2Qw3Bb';break;case'default-blue-564b3ff62703b0741b8754503c621af3__9b117389.css':$f='"erWO;zWhG0Ow+:A9,R0;JwGf=-
)bG=HZeW[`~?-kjT{ek;V_~0U1X7T).C#EtqU1buCciN6t4"+JcWv?+wn5+Hb""Hb,;[?k+b?X>]FJ]4sICHxD~6CmHVChb
Us+W>=zehc)xx&"9Yu4wu9r>1H.4D5gF?Jj-/K+
dY&YrYf5(0Rk},Gl_nX)Echl.W8@nGiyE/s`90i=]xI38m2_a:*k?m`-<&Da~(1sE*pX9-^Ozg(M9ts_9J4?w^NIN+IUFvM9{w^wn8n^BvhCY)"1#UFWTIfY;4@rSPxkj0cu.,{3fn~diBm1l
/,(nX,m6LWkU"$u*y
U7<g:(AI?<?u|pq
6Rsffq0`1:%HkHQ"@%wmI@.M*2mDG*cxgJhE0`KvnFLWonM8H7bTdZ*UkSlpJ0OIA[_Wq?sU-]4L.W5SN3{[D^T:=1x_"Jw!hvv7Na8tEgmZsa#qFiP^Cxa7@6}qPN$v|V^xFD![Z7yxJHQHqjS7zIsB[yELhPY_Iyv7`XIpKyD;;xIetyFy3d!21uUvME^Fjx"y=4}z(oh/`tSwxK;xbwZa>ugnU1|mrS58rt;yz>uIVB{paw=v=2#Mco!Mv[+yVS>M#1bx]WxyvjQwqv|y-^Qnyj^MvxjMwuzK{MBa=e<yfcTuz(o!O?xSHqJ_Xq]n7X%vwMx5RJ4rl,W?Kd#_%:s7lv(!|v+vl-rOIH!Y|(5Z!x]o_<JHQ(.!+q`lve>kC`&yo%hARP/8^MQyXj3wkkxa1lPY53l)5cB.3YR*Es.s@tZ"(o|NVN0N~/k$+iSK*C1eig#"H3#>z5
d.j.C{=8uT5EGu-=RjMhe/J9R{E,,RC@0a)q
Mn*K:&+SrhhY:Y@sosn%wjQPNj^`kv!gb=a?U/(ly@:uATv<C"8TcA,.3!xu#GmY>2ACy*%2]dRtt>M-,Kg1n9=@Z<Dp<6B8%G16@e#$[R_Rn;93uZK+p53#O0$pTiYew[2_)#1!2eY4^PcFI]Lf2/#Rl(9ZZ)R,;v~jGd,5.,Ku-9z.#K%Pd.q^4TlU9K=1/[_HLC2")5_uDXs?f7^p5rZIKWh`34sDHlBH*JHhW&qiyjzYd7.$kU_@ajyQW#q@xN(6!7QDOGh1.D
8}W-gn=OLaU;K123S-+m+hLDUJ6`h*OaZ3cR5~mG
eJZXqn0cF^-pyJ#L8T|@ZL)WG^!D_:_:Yscs[g%?;E)I)a|9;`hOc(ceUQVrZ6,lgkcFR0Yos3xknT;#|ZM84Q
]E&B=9g}LKcnXNL."!hp9T0B_.RWq#UH:NC.@w[Ldt.;x5Y/R>N0Qo%[qPF6KV!NJ5I>j]e&&B_XKDtDbdZOXd"<CHbgfZ8BL|-)2ZR~8Q]JLSQ#XX)9lp`C<2/-oZ#34(u^RqNK6@Vhb=4/Ud=Pp5&GL$/T/2@^K{@^nj5p1o<bT*Wwf%e|F^2o[b<~Hy-d)+h4*D"k;%dX]n_!wR=Mh{uF,0l^q3ZeW-T^$|]e9<8N%2pANFu8is,i=1_):QRDQ)4qsLOVsg>>epq8G;^z>#iqb$*.,Zd*J/Vjs2t6J%Jev56cM+7+VY9^%*S)?GX3=@])>]%<$7x)*;w
hl$d?vcRl*jKLjczCwMBt;`
&<=_o)TdO@lz*0U/F/^PK.bV2TOd[&4J9>!i]TuP/{/Y
ePgajHVr"3a9m7:nhs2IrnXIgHC#u$22FZY5g`QiXhji8rB28Y~q~<QY8p?EWY@8^d;;Rj4&
%`FFhS"cP7a4WfGO0WTi5
1LO=Z.7LUu2>L)dx7_SxZI&peXn_i#p8p
jRK;1t9W^[.Ig1,8,!&:(]B3s|Bz!BrF84RP(4IY"dDo8reAFHC+Y^boK>P-=&:qkf8AX>S0r&q?OsBVkPFC.G1rwG@"(8QRNs+E*<)^pL>nF)jI0^B1&4L)3k/Xd3Kje..=!-)O!(dKc4m}(nT8gcoOyY`iPa:
s]mkrkdaOmMVhYD2=oq]>)p>cRF7VOA*_9guKVa]&GV35C&j?njlq8I9]
j>1s+e*xqcB"%?`gk`^`8-YS%6)-^>T2!eT"M=O%2o6-N-88jL.t&#w14_^HbBdI;+rC+(
42e,@!<W66
g|>C%y/VptC2,3VPflgHlJBr.8F#Y!^6*Z.wa(5(&O]VfzBR+OJ8TU18qmD9mv)d"(mgSZ;/]QAlr
XLP<Nuo`j3rvc$t/Q|k:"!]Gx+6QVZBVZ9?4VOfRK1!60r0KO&d0:W/~H0s{T:H*34hHf]O@b2=HQ18x/;$r:aB5FOMQV$#WvnQflS[1PGd-
J7YF-r<V8=8tZM
Ea%DMA&t`7r56:c$#f+XBDrIpPE#)GZuv`h-H6+;D(C0;KcQH+I3*)oIrlUH5NqZLhkj7$Nt
4!jN7+:HM2z:q%Mh^Fa!~[N@4adHzx<!JA:?l^GnA1WqvbmhF9`)]Qd>x&ePO
!,SQrhhU/HaRBSV.[eK%yY
]5hP:B-z.BTLWNf
V{g4Cm
c@8%2;=+yi0?u`T4FyZkVGR8}6FX`4;WK:_0ID*W2gLJ+Wz#*mb-i64;TMfHF-YR$3jYnJ.?n-69OQYB+aFX,$vq)CWgy%cC@.[W3l
ERP#$;/nx?A|UoQjc[c?.Wv,ezy1@wasMox@0D,
2*niSiY=$iGO>|]s/Q02X4t]`-+b8NIM$+gykQml][-%CQD![2;Z13ppU($?v9Ce+vTK6ie~H@pys|/Q..0(j9t:^E6)5Gv`Y[U+1u1S3-Y9$
"=^0Ch(<O`>l@>>Mdh%XRn7)QH-=m^-LU(D-"]PYCOtm>9d(%T]hLi1W0]h;uDJly}2b<OnKGqV<R3EGA
/FJ&$3UH_%h/kZlQ&(ZDY>>9xBx;n,K&TmB1&-mHd"y"`X%"v<m-=Soen3foCzmSAN.{[%Ov,Y*Ss;1UcrBgA@qX5v%<^Iji9kP+yz-"p2us19vvX6X19,1H_)C{Z1q^W2Pt<]EC.j&K;KwcZpMv#Ur@.TULfd.cj9k~`mt^et6,Z
MfC:R>)?cm`S-g?z?7[e?C72lV;uHWZ3jVb,N^be2{=*q#!>B&%7C`o:gtck6D%CI4a?f9"5pM;T^g]B8>5wGi967FccJXwY(F*~%QZAP,>r]|h0caoY`@mqvsP|4kqPNS*63{*z#kYsFAI?csk.*)&w[o8^-O4=O:Od&I[L=_GM@EG=6I&LhlOo4J3]3YGcr!DnSoI@%za[Q^$;R9E5ODR<G*aq%Va>B+f`/w(Nc2*1YTGKVcU]=>S"mlUA,t5rd"K;<iY5ixr.Wd3@08`19OEUrKe;g8.TSS!?d(Z)^Fy8UBIYU-,WLORz9l&=$k8Xmosh&N/-&wREp:p8`KX97XGZhrRhL)]dj<AT00->m`>O]O<CSy-CnQo-=7B/SAESC}sy"T+0;g<2(Ty[CbbB!d2oAoC>/dZ&C>qrau)?XXDCb:_(I9RoyC&$f47^^&44fke,vY$^2W5(*ZU!;&mpMFUw=!GU#k?Qb
h[Q$3PW{T6K}SKT~$K,!?Z?61MdbVe.aiW!8u|pgrVIiWg--#9[qjF5]oBnO%^NET,nAJVw#TmtOgU;%W}c6Pd5zeDN>4V@4SDi*M-!W*SO6ju!lJ_/+0?"^^rlcMj^2d5>]V$F](gGG1sufZbHd;Gk_PZgqH_*OZnsN7,@0%|m?H5,>p{
eDMXF,:(|mj@GE:m9B5X4uCRb[{]X:Hh)2Z?(#rKzFF=d&X<^b5>VrE)[">e="jE+k=%.uTr
DAec0|FnW9m=F+B%$^$+N!A*KD^TT81XZ=3Q!N)#QrxM"/8oD?fy=0H6>5Zss5q*eDF
nP<nB!S%yul*^xo=H4_%&oXA%sml`w$qQO,S9fJ@1}LVtD<<VPHUicB!WN(
7#MP(:$39hBB=-
~2*p|9Z>+X
V,;[WFj.%
F2m}kD+i.ukO=LjK:g$dq.KJJmIWh
ioAhKfld$Gj!L@U<#ZY&XX&<VT9#h6TUV?$)!2iwP+c?MF!m]HIxKyU4f}i!L!o}BB:mi8P-d61]>zo[3}X70nU++S.E1^b32SW2;BtmEwM_Q(U;&htELgkX"6a~MeVLM%_:)
eDu!#kP.bt!jYP(WH00Y9q[MEk%%lXWW!$S{aTK]P.FvKK&9AP"fY5o)3.M:H|3"#L*n2U:GD~eA3`Qlg
WbS#1_E]0|AO0o*W3B`g`>fu>]*nKNwvFOG;vU=Bn")nf*ii(kb;,
Vv6O2Y0IjHl(L,hE
xk!>mY|%e$1c^iGYx>^?4(,?#A;Ep!4k_R3>6vvAEDg.NfFgl0=bbYzqKGd]sVS
(&*g7skai&CE%Q4Kz#_w2Epd%%A4Q>}
,bf^9B9DpL``8$#<I/scSMoU497Mzv{j,
kj=`&T.JA&cB4=LwpDd!xWp*fPo<{Ld_UHM>)E5muwWh)C?=XfFaeo2^3K&S.tzxbrgVupkVpB@h"HcoN)T@)^(Sk8U$85!d?B+]Dnr
FYV)LteP-qUqkJim)ZQ*3<s.9KJa:Wit=1%Yyo"M)+Z5k+3[g+Z-#/&O;_%/8/C11qrVqvkg(<M.yapy$7!`Vdbc-yp@k6i&w1?*pf=^SwRAe@PPj6t6J&n&ZQvtLH;o7:w3F%byJSWj#LJa?3h5UnF9`Yr"H!Z_Bxi)I-_/ml(Jdp5:;o|e<3D
RG}[NS*?tPo?b>B^/98`!Jm
|>"Z_O#<o5?WF#EE,UV2#c)xNR|p-<1N,&n:d$Iy,p
.mr(X:6KN>q8A*(3L/u|ICt4TG2]is_IG&bJ;Pxs1V409Q2jxh
%X[^JQoDUl:CWP"*xY
3Xj2:@G6T6+(NH,.(|]b@_9OY{Tt`3^_KZm7
lBgR:/:D,>3Qq4Jv{9`dXU*DZP?b~t28mZN7zYKk)j0TLG?-hD-u
UTq?1+-v
@?/U,W")Wr8ifAvF!6MLjxsHo:OYI.}SURrn*D.F0bF:M2qR}bC]65C=i/f%CKS2^pRZh)kK=<he_e9NU%y"?Y}h/_A#q1B3[C3t?0v6;g`oZEb9_I.dD&)
(+6Xy.J/N>#L~wO]k!#_8m8lf^,nni"sXI;9wSG_IB.ngZ@65Qs@xfX1I>@BtZ>l:2ZBGtF4?k+P@SD@X,C%a8**]@_WWI)lOFDA@3;!Iu<6tF6VaEj.}04`22=m!F}IL#vCj3.Ex/ps{n{E*3q:63vnP?sj)+l^D-p6*p/j)jt.dBVM=FGbXIEj8or5h8
7cng/NapF&[]i!/|>gNy23uC&OUb7aaJO!-_YyIfWmteR&`S)M1V]CI34a:g(aAO)gPMe4_GaB=>?y7)mn`=HZ+w5GEpht,V<rSx36N^BE6Hd{INF1;hSar{:AtAp;j;O~cv*=Z(tp0Z5B2PY9cU-qg|5S3{Mc^W#$.h;AJ;DS[_:):%4WJ>!kf@@tHC3(Hwnw
G*c,"_Y!-Qvh`)t]p7X_}u/8U)i1Nt&#lUA_,$8W,@oV8<CnFCpBl!d(a$7iQ"ZflR<jR>uLWx^Y3u~=|,l;AbZwWDQWa&jD%)S5*Z(eQ8+ET$3FsiJAvGRfc=n]|,m"!,:O*Sxa&3#t1.L=71]q<+SH&A}$bUC@6(Y/6IZ4C&,FV;,F<L)y;j|EOnIk4?jy3bhu
3cb]VR=@Hj0t8zrg2H2TGEv>i)C-Puez.W>s,K:6_jo^vUeip7"Vl7stC,18K_$&w"Dw/^`cXFE!A9)tX^n]W~$J*HamM@("p}9@_,vjZW)IgT8dCkaK%%SE)oD!>{T6U%U7r]e6P5bN6<
1KTUX;EJIKCG)sZ)xqz72Cmit>Bi1PdEpUDM(<CD2390j7BeJ9jvf7U<5lGN3Z)Di9Zp~]zPy2R;~e3D1Er16vNjpP#TuA|Tdi?=Pa4[_U0tgB!2fYYGE[$KKquij>zd>y<8!S_6kQ{o)ay.r,N%5p>8k
[R>wLpkf`8QNs:D!*@>pI!l%YhV)P<.I(E1CfuFkf1fj7xWrBe-i^#t`l>|C,:DQ;B$LWre<@"QG:p91aU)^=j;Vt:A!f*`ARG9q)p+9!Kx4$4we~4z(N/PJyn*/ycCk5b|O>/~0cHaeKF<$p[3L]@vNlT9v>Zl>7&~kCq{%ZDA8<FDRj1;MFk@DbKd>Dr@3-UV=nBq1>f8`r-AcV%.TkrkI^YN#wundSKwwKj!d6Z=u(WC(?Gaw>soI^LOP;d*f2l(TV_]hV)l#pgv(6b[reU^NlH[aASS9a1lIk;]+95r(gAnkJNgec8["&dP)4:>Ye>N_1G)]@.}1`E-)kEn;p$L-}L;_P)|l5qKRF>_v&<lJ^e77J%8$a41hq9NDy,Xh}w
nnN<ZFoz[neWdqiMS+ssNC&)T.+s?Mr1S57~5J?vKO1Mh;:,:H5EiwH#Z<YmC_t6V<3]nLUJn&+!;`wbX
U-=
@mHF1IJs5-=et>A&LvgA@gNwUy$~K9rl[D2s
$klg#Ezv`9F60cTB[+#:lLnB&9!qA0Zy:*RifAJ"/wQSDt7g|e>nj/Ss[EM^}mPxWtao`kiDV-y:d,zA:umG[2VrvhDK=*Hff6"0OL?*zfDO1ZYs1641H)b<eG|o/:0x}:PcUu]@mxG*(o,((FaX}M(X_!fM6^<1}ZAwL';break;case'default-green-8facfae54345a3eb358848ed4141060f__9b117389.css':$f='%erWObOZQ1.P**:&y.4=!vy)5dhEhV?s6s`ZZ5V<H(2>";;RX:o
6J_O$nS!M[2b^;de^?Gc_W]YKSA*>$Xdft-kWbY@6w26Kri:XbW@bsysb@emyUs]NGGu"P&^-H:Im:)3WwquLn]),<gpm0B<{CX0]bQ@FJK_YbwJFs3TuHO^o("
VtT&}op0b>pwV?TFkEHcF`t_=n20iCSs1;$Kz@b=i
&BM$VMk/e9<7Q`xL4PK*9/F)ne9m$j`@vMt_SG+_dk
?Lp=s*r+S)s_P8;bBvqMn`A,):]z!eAC_Rjt38ymD7u+N=khLJ5b94eZv#`k0Vmk2gym5pk&VxG+e3nv,MAQft&<Bs[O<
b!m[mNo0Ho?t3D/KIZ`#oYX}4z:6AIN#"Ew=/>)!!_Je?^%8+@LgZ&4}[<5X"O1*4B2+jdXc[=k6uxTDs+@GB8h;y=5.GoL[(J1ar}1f_u1*Ma=Bi>u3mRhkPDM.0`DUz#qI7&nELGKsY#H{iNn"Bwc_]Px]w>HsyB?~V=Uw]27h4/y,z(A>]Wp:Bay~[0qJ`HLoHLa=MbyEav7nnRy>WLx
6c:cvJ!.s4yqMqB,m"/%yu,{e"eBa^H4y$8#P*N<iUq>nenoBneHL_W8^Mw*XciU(3jsvZyeybJ]SRx^yn.4uDVUo}y>t?rEL>!)1)c~Y"j<K:^;YDB,1>,#G{mJ$RB7MzR%g3(H*h@NT3S5`c
C:(psoD;+bXfzL-l*C##0`Hqzk+,aR(HP#HN%0~Hr6L!R?wg&jq]@biD9M.3)MDY.5WCBd,C;&nQ?uy5Pfy.dg#$..BPv0-N4ltFdA&dcJ~jD9:+=T"OQrNWthf1OxYU=1d30_EEfEf(3X-8S=#mLsr!?3<U}j.x3u|h%H^?Uq+0GV<PzOG;]Y:Tcm#[I->
!I,565=2ZO3v["u%XJTP?*BA%Ti-J!qe2$VV>C6<6iR_Y
,U_N8X;d@5ka{otGvYmF/J(Z#((8u4oE"V!*7V44;GU12a@Uk,h1uQtJnP)VWw}+Qn,=)JRLbB8H/,MT.8fY92`#n+~P!#h?<z!f:?lqL4Vk,2:Ayqek9"M+*k{X/=5bV9x"WS3_}!L3#UdyK-/>"u<%([<8V,6mxfbG!Akby]!_:9B)e1=Q.ZH.|_p=5jx2&^6<]Vz_u5qh)EU%}X.barO-Mo",UVl8X:`WWkWRHph5O@1aXId4@jT5DH!FZ5B%w%F8G^&A-7S3.JWXt%n-U+%U+Bhew*>nJ38+KW5WnTS+cAmxIa~o=o+g-RgZu/Kee<zn[Ey6a031NN/ei^>5$T)frbN;305VlFt9En,hgv6Qne/
!nHRA8E>8l0"-2ZnID>-25q!p*<:P-9khc>dr<z%
H,B5[.(`I/P#OM1Ip;o*:/[W^,gvpt9BKo"nb$$lt31O=:mW@xqQ]kQFI=?KlVr&19/1qq>iWj:z.k3U$jP~I=4:s#r=ca>tQ@aQ[Z4`ua@)hz@w"9?v-]Y<&AgKd8J8q}!5[l@TZ@QQg+?rsK<MhdgB;`v/f#8d^1-(Lf2"X^-&I|Vks/x}NUmR_YuNtx`RMp)#(n3~UIGp7W^+?84<%Do~oPgy4@z!9=LGB(gVI1y
w*`tyv5+bBV_<
p.(rSJVtE^VSFcd%w"R./*:5X66hO0.]]T8u-tCMb?O$m:>wrr2b?Oy8irt4p?pBV8XJEMRt=N:BuFP=f5,JtvV@ui1-P`l.!?.|H~&B,,&#f(7B[-?D.cXd8
h*v)sZXG<,Ja4=gY6HW$q(H5fEe[/lnl#;IHNvRi5tRP(B<=3<MejN8/$:8`SVxf#5f.`1l^o~xe2@W5#MdI-7^v2x>j5!6CX*"|*}fENDo=rFOCD^!*y0ZH5t9,Au.[JxG:D#Pi"wdhQ1PdVbT>h=Pk"V^o7v,Z`LX%kl:12?XtA=@lfMqq3+qoA_t7_U@.>x+!hMK9)k1FRrRVJy8O]LeS%9yp>Wl`EfLpnQ`V!thfW<@b:A-
;&$vbq2fr08$_P0xkATJOx9;0GgQO`yQ"PPD>16.sA&0!yWO1u07^vEp]!DaD)wu3};M[c!j?.mqokCAHCpZ,P?;GN5D8XOJ0s<1^Q?-<aru9vH(j*8`X)]&m#c=30QriW&[#s"7-F$oav@JhYE0DG"-TkJ
*uVCrMyc_0V^Qg/:Qf9r3J,!b_Pr(w.!6OBbr14Ttv_DH(qo1
qI*wVOfQ;Ov!c6++/XdF3K(Gx)mkOWke].n:-G-`C8xi<>x+LUOh@N.[8e-;ZYJU4t_5rAC=2?Q&M85}:wbfA/ZF:;yy
?ZhXd/c`;dO!HgIZScw3jjsu%(+`X2"yP:48O%UyshDJw-XE5P;`,1+j7IznTdjJoaimT-Qa7R^eF)Y49-31/Q-nu@
dp6()
IbP#s
!(:#7t3{d/e%m
7U.t15uo,9=69qpw0M>$g/k_SILqeu,V8l>.2x;R9YU9$-?1EW9ZW&^]][lf]YmDnhM5P8`7Z_wK8woh-vH?3OwYaml;9bO9Q
jA5P9.BSnLKN#[_vI4O,7Dggt<OgGeWPoV_C5Rc;798HWQ[>"D)aQB$[p]!-b.3.C&@h6BS`jQ&.q_a.>[#cxSUM?jAV=Bxu1,+r,vW,bQfsndx^I<k"wZO;SY/,#2XF[Bd,qp.ilLO$g"d|)>1#C%_{1]uGGr${25V93??FCI
5Z2%Hj!+%
^S.rp2-LG3ix/Ix3xS",su0s
&%#ce?)jT}/.ZN.ZI^Iw+26*8FiGS?Ja8r@58%GbZQ5L4>*U5!/sRn9g7qG]6;)E97$ig&`K<I[r=xPh/7e*t~2SWYf+^fxudTjELty5Plh*bLAQS:D>(V4ty?^2Pfa|tJUjr%?H^7z%E"w`ma_y*8cd;X".]N=/N<0ibg^`A9B*?js>U9e-mNt-uPcsJ-XLVXx+F6U08#Ijz&EIO^O!e~Tk][D{QC3A=!HFt3>+/r,,lT[KX)4t.:E6!1SbcKRDY_&sX"C#UQI")s
7.h<wZ9kRQq
7wW`PDRr9XPh>_H2y)bF
O=C_B9F^/@#+5dk[`%UE9gRx"`%god%.QoOWt3Y3JJ0J8xYn$zfcvAXJmg5.W<r9=rR3%,oiD2*,d2LlgFLtnD0nYBezFRQ2;Ktp(%CH85Y(>`f5VrZU!>:|]+6#86&w$zM/+i4
EU+O=+@LN9n>kk,=rsA"Dm4X@jm8=}iF-?`0*(@`L,"-6kFV:]mpDd@}7Ma%-CpD@#J?BX](uQGGAhy)D}ueJ4$9*8l+o"5:P$eQ59r:T14+6sHyN^/`MX.">(*97|"8OE:M:3B^hDLIJw,*"El$-Uf::-CW_%JrP<6D%dPgh&vWIK68MRsqhUhRCs^WjR;B0h@NmMawxrZ]N)*l<A_=FA+a=+mnmk.9kCk1VD-hc?m,J;COEEbRi`/$p(HmiXmj%W=42mt&1>lXZIMq-qKQSIG%&N[,g)G]deh=E%f6#((5w$=Cq#jy+E"[`b
n%top<F^D[|<hdc..O[0DF4Feq{_"ftbhS-+lc)ypn.YjOcQ,Y(`Gem5a,hOuo@-$MBAY3|iA/XnuMW:+@un>,_ovuu*s
rq]qyy$3{NR($f,dnmk;}0pid[twaDF`V@C;s,&GVwHQ}_gE^4<f^R?jP+tE4#<VX](/krPA*>+f]I!9UxVcYlZ)fPzqy0z7-1"5H9ui%e@-g-w5qU>LUk7-I6dbJ!zN~Px>cK7X7$QQ%-ek`SW8e$?"+Xf2==vi#r*NnZhdb=[xC*At^pNR(?%H2=u(#9*
BScia2wY$_y49^HBf1VJQ@`wsPb#7t9kR3d5ay5U"MBbtk):&0!-_@?NM6(m"(aUa(U"%AGB0oRdWJgn7R%(J"
&s3NHdcD%,o:0lJQ=xT<pwJLos9kr?(OoB5![5o}NjG!uDn4Hevh;Jakk6]HoYCRB;$9D>_%pz!6ZYSt(T7}U1^W=iSAH6,"!ec8??
<O2dpd<E`;)JioK*lFcD#HYim.T.!Mc^u"Dw3-#"qk++WU4i^qzTZJl^aL$MX!OU.8%Y>P+$BNlsmbziW0SMmo*^2$M:uwWe>ZgaG^d,OQv/}/PvnU`#.Vu4y5%0Q5>8d:`hxmk>wS?QV^]Z0u{?ye`VRE5"P"7*QLW(v)hDAP."FH"B$xL[%H2P%._J
lDL2wF^V_ZepTc?dDvO>Tax*PkwsY=eZASu>!*LRB/%53)kuw#i5Wi;Fi%ZA?:gA_@1R=l=1Qfgy*KM=BceMP<]i/I
gP$l:)]]x5AZ[rrYUg2;&Q2/U[.o0)64[GnHhen9G,o;CG$ucd~AwNsWQhsGVNAeF7E63.APwD"N[m86mfxhtGagdO|w&xmGM5QxDyf+gH330eTLG3em(mFv]1uON.>PhA!$|vUeVt&v2AUZph
%w_K,5dzDRS~Dfp#JNsN)Fym_RP*2OOnA{a4$KvXNrK7
5!F*Z:uJF<-edgn]uWt&"]`NHHc`S6q_;kRYa]}uTAre8cWolM<n+#[yrrQBkhcJK60o"!R.26{O)3nKa_$Y5CTlw0i5/UiqFuwv7l+$Rn-osgvdxK&DJhh%HI5@SdMra_
h+`aE,q3>4b3_XN_.zgU0kwo8O9ls_o8<}p7K>__Ou#]9-gxtl?axTSh?:rKRU*z%KRv0JaJ9,5Cs&GUN_>>T[Jh+jZEb{Yl)iFR00,zA)^f-2F&mepkk,JRvK3Idx${C*B"K,_W=V3sg~gqa!6h!fp}*H[lHC0Zy`*+$%=#^[aw3}Zg(];G9h%3e.$zY|L>7S]3>-BkP6qN[gPBh:R#&T&/jx+0
4,NJ+Z$Pr[~:#/NF=D"_4_9
|GBQYf<G%=pW!4"Jgeh.6?0E}e0cEJ-=YM&S#aX3V3"B^):eT.aLEekK9;<=cr?4I`~DHkI-.`P1tben.J]L@*OqajK8w/iP4!=W*LH]m>NX{t+<4L@g#9VP]S1G4!Wb]L)#O"b3S:`O2Q=%y!pqE
s[6#Q!$6ECFng2@L=f=o2/
=+H{j6!Pqw+:Wj(9)I5uE5LF1w$BT!`yM,
qSHngJ>D`Pv2rB(mDpH3C=7*fgbUh0m1hj5G0*^1Aw70CeRQdi0eE4_)HO3of#VGJ$#KvZ%]G]Eab%GwZ[7`0<MV:WLk%BuV#t=4o*@QG_[
D_tU6yLHNjMl|[s$0qTG.K#X+l7%1Ae8b70>p6xB&cZ1@n>g;r0u0O[ZfyrXi/"n;C}-#i)6q*3U.U;c+%#gTY5BgNA%(j5Km-)KSq-.3<_Vfqk:~Bf>l@l
h%p=Eo]lfA~E0_D*vi,dx6AGr!X3wxu&99T;#+=tt1P+ir?ZUQ%d=po9n[?w8V^m;mgh5j(KY1P*]qePq2F0#`v==,/X(1785fv_H*b@noe_(S[g`t,Nq@Z;t&aqiG}LA4>y@/])c(/Sjuf&eGBt)r7c$".@0
wv*8M#q<vF(+FFtIv;GLD&7FH-D^}j&jYY*RAQ)]XOxy~Mj[-h,`FNFf6[$1!6BGO"ODBr%p#7#x)_i%~;SAF4YW_xG$(]#hb=AKE-?mfC~+TaH:Rgu3Danvd;$PZ>ro.YqNMj@gr_S-0vtedUut4Ba>$Ye<j06K2^K.?=ASMopwok{.VYU/5K7`.@T5m>/e6>5-G$RJ,:#q|U,l
o.Fk:w79=#KrnA7Yvi(rDI$%;f=~r?NEOaKK_{M2raH@Itg%3Rb`j~cx9jky1j%e.Pg68pN>&
*;4x-&aWcZ$xL%QQW~FF>jC@3(^:S|p|
"[>e)vJl1j$^)j6$l07=D:}w/#WYN.ouQILOvi!1!xhSR-jfOb["A[+8bOEUD@ee/Wt">o:4~?*#~C4:-;h?x:aI+L"H
c[o_VHmBi5K1YYQ;37`WpGY3S|[u8MxNN-^jKX*+d)ISGQI//LPNOIU2+qfurf;q"zU5RfB4PpS!;y5va!E
3n:GlX;tQ1VPmZ8av+k>CVE~Y{p`@)Wb=ib7*"D3)?:..IfPKhr~EN;myQ3KW=qo(hr~Gby&8LF@7nE@/
tkD$0S;,vsRj98.Un0%!<29mq/ao7$dIjz/XVeH`m,7e&@;UYa"yngdL!`0<]SgHelPf_-sP=8G*n)dh>UCoF1q(y_@&pWj8CaCgMJ<tu~o@BLL5t?<@ATaHk&MX#/:3]Q&oc|GuxQQHpNB*+Z(_kix"8~-(@fq7Xs0U5g.~DXx$0+q8o|l/9^-Ve>g80DLuAa!%dma?f/88s{ZkuUf#e3.rF3ak;s!hZ2+(*I"E.CM
@V1?H:U!o|@0B/o/-FCc:sAZgMi#*~LEOgey=7nb9GF1u,"H!AVNe?,cn)GT5]w9DW1Cwz6Ad?uH+jb,1Y7>2!P5Exg}NeM
gf*js,$3KY;CRW,w:e(~V"9n2u4,TN-~U%gGk=w+67x,i&Ut6h^&JJmDu%v<llPT/^9Ql9*,k~MhM7x#p`&@9~#X;~oJZU6}?_#9s{yIrp-Rff7sw@G(g*yB;ep}ATa}l>(9]xPm+E1Dv4Jxw1qNU-0DgISX"%R{qqP.od:bi]JOrW.<V/s/TXB1`q
=O"=ne06xf1d1q6hAB}N2:;]1MduOL
>GvLxd_4=RdP';break;case'default-orange-4fd2276ffa8eaad143aec2dba3782911__fb9c21d7.css':$f='"erWO6KZQG0Ow19$3.42)`W0IOKho2]iYa-u?p835Eh8U.X.a-yZ]?$P#]M,y<;M|U*Q46Hk9b4)`JcW)i]+O^s6<Zs
zA|L.rg<X7fkGS)O5w&NS;~=EvRus6a?#`z%5nBlUiP;n=r@oE&=RPS;K1n;FGL8<B/vP"BxR
f;4vcmFn2k4<^NdN$c:CQ]2n0
F?K/DvYx|1Yj`kV1%u_5oamVfA4Tgs8-WZ}tym}8?D"@9oG5wFtyHJsUn9"?T,MN~/SvM?>7
H->IG"4+Q<@^B,hIhoav!S+6`=OP]-T#j7ssIr[ePOnV@&n/0S[5N"&dB-?a%>]B-b[B4@h_T]EFStqzf0>C3<Ru-Ppwgd!!ge!14qYlL!ZE&d8"Q.eBkCD`V5HI2FD>wUOY_?<F;vfq5:Bnvu1UKn:qG
*@AUS_n]k)JhcJvODya(TEx0l=_83VyBb0m/*7/vb57
xaxSny2Kfk6iclc;@ke2SLt?og@skUK&.aDavm/gx"f1IN=IpKx@7Hx^t1JsK0,{w<h1jWyl^8uHl.qhz!^miVw"wxcho"xcw;vvrImVxC
>HDym+dq.n|H#h_x]7$c|UnpFHH6]iAJ9ngpFMrId,nBev9`;yUctPR[j5!n%X`2QY#8#f1WPsta^yNSTy%=(pc2awny6C$_]xaAO*.MV7|ZLtSAjL{FjSs6DhE_Cv~bLyn@+T<.N(`aD.-&qH#(ZR.e:o[T5w.N>vy^&a;$_>O?,/u7D*&n~/pMqpy5JB9$i
_9}rmkcRs_:vI3)MCY)lN2pC%2]$H<Ia^n5@>Q6
TNwSl-f%cHU`QW.WCySbw4*!]qK--oc6-/MTXS>3*VhvL<nt:1-V|f|qI=TjnvxLLd|F@hrHF@ccIptay0b2p+yR/9j8c/18/gF]
-=4OwEYz6_6SZa@
F1@,Z&ab<,;#yo_HJ((wu?!ed+jZ3vx#:Hb&(O=c850?L`<bOFbYD00M5TR,AaPq]j*ZX3Eyr9j!!?Y>>9#k#+PQwZkT9JJf/_ww>rSZAI[C&]r7Avjzlxoy8|)a.T$*mcHv=JU^-n^OLKrmsN.gsZvTa_4XW6`GUy&1nRWdWh%^s@+eYrfe?1-m3.pTIIsEfzlvWLQ?x2(XADv](Tv$Aj):7ik)G]Gs4Nwt
:"5L&@pxZhgCx?]`C<fx>A#?PILbF?)-Tr9hj?S0=7o;bq[(cATBFlNqJ[7V0vzlJ&~:5N<WpZfITLG[_h|KX!~-:RS1)InfrP,mf%0+Ub)pd,nhZBz0D*04[Fw:3f_4)UQ]M*U"lS27a(70b[wpT[5%/^baKdw5YSvy5
^iAp@;mUrnu!F6^On#,8D40f]&!d:7}V>5H3(Q#+H1}fO/p/nKtC2/>"k>&u>me!kD97a4?!d;jAYWCi$q+I)(M&rTYD]XKnkTA_jTNomn/*eQ^W@S[[p#A%4Rx1T86U,N!%O;F*T%0_o[l!zyL:YT[cpOBNQa%T%#J;sQAl`F")`jZ.>u&-zv]tpevVJqW=[mYa{,IRcTRa1QtQi)C@LASkLOOiz+1&;9qE!wO@Wn82LWr$%ZHrV9*mw=LI"ye`B[RHyS
en2K)c:_ERrUcPb9k*QxiU_H?L=Ato*a$3;dEJ#XIzb{_,b~Z51>jq4Co[,DsadZ>9aOezvb
:TX9s^PsML
*$"DCk_vUkcpG$i2`,v|j-#Qp+gYh&r}:J+lFqbWO2Q&yJT`KvLRVn[t!wt[l$
/,S",v4`j"2w&Y<S2&=,+e54N2<-
@gBGe72xyCUCWp"Ma1R<U#aX]7wRu;e;L
S.LeaK#Hpl*b&]"nu,$%
wn~0{FB2lPm(%9/%1:lW:N?_fA5d(ExHAMEn&(jme^pKJDv$VwO3-oz0{
nV+N.l%xef~
?;-=ZRjPoO,rS]/Al
s._B&ePMaVi*qo<byo:OD!-)O)le.PW`0.[1H)|dupEF)!;POmAb3k(O,&zn-W3d|Y`kyZ1fSLfi*4:^mG1Udtlp$(aBVCXjkA3R|@a06`?BRaCLCC`9ASURUTM59dAoKO}E/A:bd;y8v90qW#5y@GN"0NP]_m{=)6^OgcPtU#G-@>Yk,>>=y#Ct74AJw)mDbEH=.0XdBLG%/w<Tg^jMc88ib8&BC_9;pH!`wt%+$Smc%5!FK/%ABw=fSang+"OaHCqTN@hb=k66s&1"[PKLHrFq_`ZBp/Q>#CLT)n#&u68#:!.aUH[cMrB`nBd3T9{$9SGm8le.G0pY#1+$`7|GUuS);%Q)7&V$QcRNqcZwaAyu.o/@:9l2rjx!~;_Ij@1e`z$8SP?uAD*WxXsE=E:w`?^:N`%n0V!;LDK-D^;R%
wd3.^j,re$G8y_iwzR=;x$#@(Z7f>+4x+3Fh9#_#u=f#ibE
=$I8=1z?*XHd^xi:(D!ms*[BkD2pkiibxVw[ir8:|ZA0qg1bF5v%Lm"$bPu@}"Fr-*O:L8/a_+/dOfBRL2Bc&;ealqHv}TnRLZ~y!ZBT8S:xHWV@^jR[*IMm7s8P^va3.
uL2S4jbqu`OG*s50j7K3dZnp7YBMtGb=[XA_68u.`"HP}$L?BwHTa%)f,CV;u%dC@D]TJ5w/LP#5~5BvY+rUrR/cWcCZ[qel/x10(cABqM_
A,k)#=AeU=^3u`+gRBb!e4(T%IXMe8etnQ#>F8x!(b[-U#iGPORJr-G`LTDPCYka!aLYX6)D}KlE!X]][O%&K@+WH$@Iu;)s|-T/v@(%S;V#MH,#/#Rk*>$NK9&
I*{5^sW#[qA)semY3Gl=g0PIiN;h+cjNCI:8$&y^KLa.o0}3xwjvhtKjJ1NhxGQa9R4q1A^.[@8,J`7bs!
].
q(i:_dVpSv[sdk#K(TmM2&-m(d&y"4T%"v|lB@<q+r^oiHzxGSu["]mOV,_*CGG77c"BgANqX*vRc^FE=QX&-ym8#f;qw@Qs>6E68P65nD(eh:921`?(DVxhe;UrL>sj?:6tn%,m?;*2ON2;HpK
3H7idQaJdZ^G7n1@T0]MTF}9G]uFJiG
dxGI#Ukqv:=[&Jo"gKCCwX3gy*tZT%!-.r!h1Sg5QPtH3b"PW"tpq;MHe]R:T6Z1g;|5Pc[J;wY(T+a$6Zy1/[ek0@5B=#i>yKRs`!qsAc+.N@Di[K=?3T)q;/kpK>:0Z,S=WO=8iE6$O%<*tPJT.)c-!Jh)NP[66:<mL&^,J`mJ$_t:o7&$#U
D{"Nt;AQ!eqRAF?a:
1h[`l^)@h_@1Z!J/WF@"I_%HLxXi"V8ba9w?<oog2s!Lb$.M<4j_?"oG%Hn7%*+aOhiUd-Y6(.j,*16BXV=&M.":vW%WT+($lLR=,6-W!,N8-axAbpAt%atQsjVr5nh]k@m.%1ZuQq+J?y7$Q3o-f:(hLp5GPU+SJ^&v,Ak#`0W!Z/P$no?&GYDRgKke.Ip+J3SVlD&*H<5UGw6gn!q1rqf*NX,i@4+*MA2~uY&Y5ITlJJ4Z-%i)ce<)5!4+e&"xB?`sd2*WnK;lV~)U:Zg)D<jF)@m<ol[wIDc%-AMks~cY:U0K)uY*F^&VjD2#],YR:mm%X{t7Rr>TcCvIQ,(kTn5*$OwxH_$6gm_2w{tE8d-F$u`+^g`|>B-~2UrSf;+
7/v^(=nqS^,]qK^AF6i@2n-OKqf?f%8`?c^;h/3;SXU4C]l
t}c"^+1^N9e"U_%fBi!oS0*gbTBK#U%p1va*X99sPuH5b}$"!i/+l377^#"P:/R2.jpN!y"Uhv:>ZIk5Tn.KC_"F^ev
3Ld)h9U{YEmo(J44OX>^."Y!J$c@F,G1,+^"@+%v,Ux)7u%0W~mRDg>Oi0^<]2K$rr_l>-"fPs$6pUa=/49R.iN@Nvcb(?V
G<lc)}Dd$]VHOysWKD0ZdjJ^gy.qk&e`09l
QwTb5n9YyN<S5u6cid#dc_/LWeX>_BrM?AZ.x@5IkInuYP
k*eQJ0GZJiO2Ita=cQ[iU&N.:hg@"F")DdP-+LVfz/,t;lD9^:$^WhH[;9(J:34!ttj8@$4?!6NTQ=jaGU=Jh]=L$H-v
UV=UY>.i(ON@n@b{($*zM-oFca#z$uwX9*V
c-^X)duKw].l`dcG"k_$>X`v0Y3wO)HDfrm{@=!-_ncKCkI{?Y#bVT[W"J"!lCCX)yvoDB#G2zC3X>g}[e!!UVZc/+nJLBfz`|_[kDSr50Ie8iUEbH/Tu.Z0j{@Qy2X
Ko7.%4^z:@xG!/Wj<)&tbWj{o^7GG>8A;k0ejdV_&`Bc:$cU
&$
[A.njp+$H1!Q[^h$c1:k@QPo/U[2wCgRTvbP5EE<Cm*..yJlpg2gPR-vE?6Mm6^YKK/QPVZYHuG<=YbLf`wb@t&d*dHjMOcv1bPNnr,{p=<dq6KN2>q=(
mRcloel0ZY!pQx"DXZwRLynXO7k1rG"OUe8baI<A>;hGmm
d]f-xz&kh2if-_]]&UyY?Lw=
S4?=YeZ.%,H)_(aR5f`T>hCSUP^Z<qvtqaJbA5`#?RBM0~KJa9AgHK6RY9o&M*s2*nrh/SA^-c.SP_^b$726)XIwhMxJpW[KTQm{yUXW6;CBASwJ@haI!.1?@rNM]:u]wEc.vjF<uS]K+uQVl*5.t[.
VfOtyhfoF"c:A_rKboH4/5ilN9$lldyIQh*((6Fb5b>*D0"J(,Opk~C1[O,.1cS
0U(@H-e<`+a<2ZZ%RZOmOH;v?[#FsX+MGSrjt/,Q[7VH"DG1]!,_OY)fGL_Txlhn&PeXI#:MQJnAgTc{:f8IBW8&Wq+"jhQ?YDYwN<D9ON=~6mHA>60![ddM(al=dsE0:":=)Yr1&Sih!(%Okk$#<8b~(n^l2oKZmJ)=1yf3#@4R0"hs*?LNouSm;T`%C7t>6!/t4h!7@U4{6ki!+@VO6wLdDtL
;l"6r~4;[-hTQoJ-EksBFSL@v(y!irfS-U`P=Sht/;3h4iBt.!WXfSmSk^XH0!(7QGF$6*v$]Q!%9g8
&K[J"@?e"]eXw8E{QG<RCqe&nN@8?TWx8vyiQ?pCWZ)6(05u7SDJ.KMO&Lwp,(%]7`]^F+?FK&_[s-n7!u-e]?5&a7
-yW(M)
6r9TA:eIk;jV9hFz`Yf2gfvxn^4g#uO%^s&?]D1jKVCl`31aVa$dK]XO`0<AR1W<U!AR6UIo<eq4Yu17
_1=Zlz)A]`0vm
aNV5tA6Ax[Vq,f7qPSb!<k;RYV!!F=7w/-kb)xq-H[t,}A[Z]6T]>#E*t9yD!3GIK*/d(3Mo+V7C+f5v,<|%LXI_!QJIbos5z#L*3*Wor<&"{>r5@m2)zVwGCf](eJ.Rzh&:+RRci9)i~>;$06e%s:U6(F0on_~widQs)c:Csl6)xk}J%Rs%s:2yDZKG*"gs[TrPUqX%n!u>81B:D)d0[s@DV_LxU.XkdTQe:5yWR,xGZbL;*#x"0pMH?y5m!IkL)H9d+?N-|yC2
8O(UQUx<A8X#jhc^#!A-PtG:_|7hqop/q50uH}yDo&rB3s&XZR2t0;g/!*([NQKWb&wFnnbs1K%hjKUtgAEJ^0e;0`3`T?nH$o4xg%:Gt"@v3N&Ar]M4T?pGU22SF@Y0v/3MLvPoM8aeD^WX*1k8aLT&g[UXG.F,>qZ46AyR`[NJ07T),>1cU_&zk+Hok*fw"
,$T$L#DtMI5<L3>9iE#9,M5)h+,2ejV^dW8x?cx!Ero/7H
0HMs`srR}aaP7a.jJV<F"L;P#CP9X@`[vSX"We+6:#EJ}$`c!S=^~>I]b%YR5E1JVT<xz)9?35Fn[W?6#?KLgNFOnFL08M`d:>zpa7KR|^kay#t,pT7Zzw]5*-&J1fuifTM0q5GTjC(Wc$Dgc-:10r*F9Fir0s|!9<q*4mij[AJw|i-T~={+m]aaUqr.orT(~MMY*s:,Ff*HTAr+=Wo%CoaYG.jf43NyU(="8.lp1*!]4#8*US#s]*Zj&kJsa9ze[<IGjZhL"x1_1TykHIA%{h4r-?:R&8aP@Z,"gp=6k[#3Ff*J~*uG]HTgCE"E;+tAjqq!8E@[Ho?EGFY9cvCvX98-2pu&*<2;1DY@t4z"Sxh-r*yH`m,,h$:R"QH#
Bkld!`%;Zj3*lzPf5wJ4hiG.n!#5L8Cgr=smLx5%r]=eK|DJXO<tvQSeAixai7<`xU[wl)7Rw+P5RK&Oo$J`rOV{o,+m%h(_`dw~e6N)?KZ*Xm0uwi._p
QQ20/Jx5k,Dg0>d+wp0Dy,<2&"8}a?fO":qu[&jVfC9;5hF#Q3:QT!O3+h$s7"Y4H3B]&nMeUGd{Bx=0N*1Qo/HJFH00m2Vr%tS3O_B{l|OaF.HuHz"RlxkO,cXCK]5mqcH#1Coc7d-2x2XQ
P/TM8$;Oz^riDNU,Wi,*bpB#pwY<gRWBwM>?+[N8kT/.Z(J(OU&;#n,uE(Qq:=BUt3@2B?KnG("y$A3T^-X*TtQ(F58BfMxg<t-hF9y"5s&r4.Q6o?`e!f4n(gk,/eSN$w?Flr0wz0`rE@Q
Nl?jWP,RSbB-8JP4jyxqNWt0d;1VA"esvo,$Jj29_HrKqrg(lUKrjbIA.)l`FN/7lgvLjq2d1E.monAY585_Wa&w8mK=&vlbzd"&uu*';break;case'default-purple-33d1c33b271b014ef4b3f2f4e42cd9f9__fb9c21d7.css':$f='$erWO;zWhG0Ow+:A9,R0;JwGf=-
)bG=HZeW[`~?-kjT{ek;V_~0U1X7T).C#EtqU1buCciN6t4"+JcWv?+wn5+Hb""Hb,;[?k+b?X>]FJ]4sICHxD~6CmHVChb
Us+W>=zehc)xx&"9Yu4wu9r>1H.4D5gF?Jj-/K+
dY&YrYf5(0Rk},Gl_nX)Echl.W8@nGiyE/s`90i=]xI38m2_a:*k?m`-<&Da~(1sE*pX9-^Ozg(M9ts_9J4?w^NIN+IUFvM9{w^wn8n^BvhCY)"1#UFWTIfY;4@rSPxkj0cu.,{3fn~diBm1l
/,(nX,m6LWkU"$u*y
U7<g:(AI?<?u|pq
6Rsffq0`1:%HkHQ"@%wmI@.M*2mDG*cxgJhE0`KvnFLWonM8H7bTdZ*UkSlpJ0OIA[_Wq?sU-]4L.W5SN3{[D^T:=1x_"Jw!hvv7Na8tEgmZsa#qFiP^Cxa7@6}qPN$v|V^xFD![Z7yxJHQHqjS7zIsB[yELhPY_Iyv7`XIpKyD;;xIetyFy3d!21uUvME^Fjx"y=4}z(oh/`tSwxK;xbwZa>ugnU1|mrS58rt;yz>uIVB{paw=v=2#Mco!Mv[+yVS>M#1bx]WxyvjQwqv|y-^Qnyj^MvxjMwuzK{MBa=e<yfcTuz(o!O?xSHqJ_Xq]n7X%vwMx5RJ4rl,W?Kd#_%:s7lv(!|v+vl-rOIH!Y|(5Z!x]o_<JHQ(.!+q`lve>kC`&yo%hARP/8^MQyXj3wkkxa1lPY53l)5cB.3YR*Es.s@tZ"(o|NVN0N~/k$+iSK*C1eig#"H3#>z5
d.j.C{=8uT5EGu-=RjMhe/J9R{E,,RC@0a)q
Mn*K:&+SrhhY:Y@sosn%wjQPNj^`kv!gb=a?U/(ly@:uATv<C"8TcA,.2jXd_B$4z
lc"rV;99&)Bf^%wQ1w56EQCNcXMV"KE"|SULD#]3[*ur|Ni0Y$iICXL*f-p,rc<:+"<R%-Oh49(LoO*]e7l7RExHu5M6W2W3|2Ji,b&!_CLa7IJZZ
ik~^[Ml62Kr6zO<8EoD2b7D%y%CQ6XQx._ruiV]0SXi-0`U30"<hEJMe>Ec>,N6-hBeqXN:g5iEXDA%0.!?(GSnR=2,sNG)Z,UmmJk7$Mo6qzNIwM//v`oLlo[%oKI!Z/W0qYj(
==~j.l/rwY@L]0g_>lD-AjpkI
IGzqAB-7@<#FC9YKZ.x=(tE*_39,Q,.O#>4T)h)hRS`@
k,"wZ&``r+/E-E*=?&.XGSw_>ejwW=nV>fI!LBCNGdQ|F5*[
**q)`SJ,x+=c0ib"F(V"8Vv9Q2
0Ox1M^PAPhW!2.Gu*5m"M(")-O5S:I&+d:7h"%$U(!N:AJ&zpor1d,9}Y<=(N_F>HW+j*keNX`G}8JkQ
]t5Z&uSIf+5&wk^W6$6<C=Ig!L)OlJ<GU]DEaGk-+*#t;6h>ANS(,#kIwNv358KG^B`%bv0H(_@p"s
PW4>YSe-QB9
GA(~<(FF*vKRVmf>b.HihV/#DL`w^#/eD,CX>p!|4C8yWA%9PAlQc5fC#(?SmJX#*7[h3DQNC0A&vs$*74;*??Xwr|s*=E@|+k6<(0v)82y[6Aa4j^_P)Frby"J5qlaDVBF@#,*:XxM!hE6zev9Tyv[yB#2Z3RGBsI(-:gEu(}pW0Ql,?uf(-2@=+_KtpZxCK$U9v9WbKEWk/z7NA
gu&`]>s+Ev<0b_,39A0E.OVi6O(|!!&B?(<pG#>17ADATdFeb/-.@0jF<;m+px<_)x_AW7xWYu+]K)wT)P(*3`u9js"FZhyRb33#$i$a8*=VGqVrCfLZhCtbt2Ew&P6bC5iZOZ)jVn&dAz!)!F&Ke4e!Y9H-N|b)#r^NFgdO?//zJxsN8mNc<MeMQ1PPT<>
j{N^NbY97VB`SfArsMN5s~Xu+[+5g_E~*SJZ]sK+@SvZplP>UA?X.{u`p<.qM:(>p2J|%Z^I9_JB*tnj5-sd*LIb0bkeT-f|>X/*upou,#lA^HpuTNTcSiq}NMHvC]tO2TF:?>a-!bTH8)>[<iepJ^%+?WU+]?7oS=(D0HOO>y0}mrR=`Xn7d*&hER?c=`-JN?i{FlkVnzA9i_AX2`%R/WGmv02H&Dp$u{7,8ON+:rdit%m&_b;>@[d*phms[9V!G?y~.Soq#;%?-wdS<E$bl/i{Yg7E>mJTQ>5ttRuw6>
{"_&
l86rrrvs6DZ!eiF1b"lIM3h0***|677,M)Sm]GK.2BMgxP.ymEAoE|Ty)!=&M]OhB-j:["YNZ`:gOn2r6
C2IIK0?~?0z$"QNyva3,<;SAK3E:5ZnK:.4!n@C>;JC(E4[RR%^imnaWdX+0L?;"_XKzSE<+(y?eZ3fM+Tm*5$VH%?%k[3%Tvm>@&pNX?/
SAxNswoQS<kabj<cW[H_vLjjTH;(!pi/+&pb7(v]DtD/p:dw5F#Eo#hZ6Ci-4-QoQG,#r-5IW`]vC%1l4mI6R=-=mUpu}^8:l6aopa-_^1`](uZH!2`!zb.fT]Or
6FhW-5kl29^^pDwihnSfRq?twLGCR6s>SB!d][`e#g)^>X%Rj80DIo.xh6I7?
/0.t)5i87^];%)fM4
r@a+21v32O+f/_AVvp<Mn4wGJ_L#u,f2r.#J$#<;$RNWtY;T8X2AYN
X8B2#V.ik:q2@jBDH8?7bTUT";6CL*[^?43O((T[;UtD&AxLGQZ>.a@63Bihc@ZJr(qfN2w1Y]6H]H|ONpL4;OfP1!j(HGgU-YYN`TXIeHvFZ4^$>Bg+%YrK]+9Kx0`PV!S!T>S,+tb]$&:D!_x]GKN4.G0^yK@_NY>6?m<)x`}IcmEPSMyZ4esyP!%Pfa]n}?7j.b>B$z$ntu*w8E,^;:)V2d88[BdowY^-QnHFw3"Gu3[ud$3<grgh*p5m2I9eDsjAx*dK{/hz"iS!h,}IN4<Aor}+FE3RTkV,>bN))@}[x<s0LEi:[<Er3oCDW+R.;s?5o/giCYs4Oj81X3R#ULK);Hwr?rZ
)imbkY<BPYy1EkY?{d]6,U@IE$,Cv
YK
hH;wE{LY##f
;+xtKYL&%HSU/B$?$t>s#VK;"3MtMFWJaly91vq5SUNN=HWuKHT
uiL@H{<Gc*Z1Zw:Kk@*GPt!8YX={*t)YRaXIL&j|_s2gHl$y;nYP,L4`M_42bHCh"q?|B:JzG6Etk9V?KxE|9r5wTJjb6Phy_Y&Gpwu=)5p7E-f_uPTqsC+b?L?^rzb?0s;bhPiYL?3[gX[}M0v[xwMkLl9EaF:.N/W>;b)O3wLWE-,G%,7ty7%s,GkZDMt$g8Za[=k@j7I9*
ECC[*]E2<8En_Y-kGomR,g0X]nuvF4HRD;Il9UT:<Y?{fDa*VK/O;%%<VVr1d:@_q|m>38S
CK$:U-$<CVaZwgPy/uW<#_`%$Pc^3W$WC*j-
,vL(0?vPg(hMfsVWo5vte4IbBb`K>]K7Jlf_"$x_`9%O.w``6<QG2F<`_Bh08@!);yeNYt|<a[-:~pE3?ceD~<xvZ-1dDDE.E!-?<+ekMNF&c@b>/^m"p_YL|"^M?wZs
r-XrWsH{8HELtfMtw1!^E@_lcy]1J2/i_)g}x*`yNqNQ/9<X7gP3g<wyIMe/Rpb%DCW|P)?:*W/<TyRXlR2h*A
i8a5jJcqqpr@KkdLiH;],;xY+tmoZ"xkVfiZ@faGmT{Sy&W;~soq[=|"dma0VH+Ch#]A-E_kWo.;%3%/IVNN{-)G)2lU)un3?Y8h;=YU9c
:5qo#u3QSsi2dM$cTXr_5VT8,b[Sm*V~$2un)Re%fcMT=P"kEQt2,Ok3IMG$
LAdvTAX
#NI@?O,I;l!>]YhTRd4Y7y`DSV|M6mvAED
#ZVM9)ldi`A<lTv&T(-~
"^`Lub[Cf2$DNT%v2WWASK?YI>15ot//qmH.eskCnTBz"7g2-Z6_4@rI>"t>Mf{R"C#NQ@<[|[Y^8y)-7c8)=YKO2dpd<C2;yK<qA2z
uD#HYj`/_.DMe^m"DK/d(Cq2`ATF@C4!h/-h98:pTyB,x
]d6O%Dd;{#`6@u`:*_|ri&#w=1WM2f(*VfbrWN6_eAfgr.{2Wth"ogkVX`m>_-7*=I}8Jq1f^JF
NdSQ!!gAb(jC}X,*b"8CB^"Ev@}S?C-OjQjK01cXeb4@+(XVIHes@-9lBh;A7PUTe`X$~<n*R1ng_S0!^H+hjL]q_JN.jfV`SlhJfomBKI,B/iuW$j`f#?][O]V)@V&38u)bw!^,BJnTzYAS8:SV`N:oc^m]4+(*+-I`fD!;:Y@*YK(#[LD.GL"B8F=JQ%cJ<0`Enw.Y~&=AMLhJO^@-P;pqDF&u607a@[.1y?he`j_u_pfG>xbK!`0l.!V]H<srIDSssBK:u:PY%CEO,:E(cwdTKH,U633]Egg6u%
NT:CjqO&?|_e@;btmJE#o!D
xvsK+XR/%cnpZv4,p}"+kN^=#/0cKKKjB7&&&QO2`[w~7c_!QD-|XNDf+Hub^uoxF6?Nv<i%rRH;h5k"M[i`CF/U*?WJ4S4Q"8:gRe^A8nEZ.%&}iDq#HB(KriA-l;Yo-iGRR
6
L?#[rCeW2Pr#V~@>tLrMH45R+Y8l`X:&JV=qU%*Go7UTP>tX[:8h(dOFQTpTG:%pd>W:_NW[.F(RX-Pgize.0Y`~PA8~Tb_Yue+z[Gv3AHQsZZ7WF7dHdQZ-H_ZC?Vk@n:d{"j-!"SIzo%$qEz9b1n9Sn6j!PC)c?qY$eGYbbFMG(ib>N;p[>$W[*Ut1/zU_-T$3ryPXsgHY*q5#7$G771<}9kSt^0%?DTXdej:jTs]w;_ioYLvJS9/Fb(g?`1;pKIZ#/qAAf[?J]F_a-Q+u[&[Y6ypRV(%ZZ+=(*^CK]nWcO"%7WPKUY5v!/S!n1UY]YsN@]7%a]-p&fKl?EB3fQgKJF"-`NZ:jsH;<;"m-5NPhjceYC>-kWSLx1{gB#076Y7#xiX1eB",TgQLPP)vJ=p=[.2GI*u!Cxmb5nr>UG22lE^ayPL7H>9kiR[0L@ZcsDoIZDHK2^Ln5
E>)y}9$F!=uDTHi?Oe+xq]VgO@nl+4(PB[=wvy3^iOClmMt%-]&`p6DfP^f&d$8;3sQB5+whnh~PoT(XS]=CH;en_ML?V<5f#;gkp
qO1]~H~QY#><ouQM05tWk#,ZY_oX[!9jcLRT1e/,~r
c
Y9JUuMhQX8X:S$+%:ti.o~xMCOdSq`-%JkpLP;l9!TTYPxNdMh_A*jROAvVz#(
}BGG[^I0)LmNguA+KVH]3YkxR++Upd>9&5l+)p(/l_7r"#88Sv.OmE?Qpt_$*j+qCUv9z?q)Sj[BmkXBNWOC3.47ia[!gP9=Fet2A"6`M>t=l$ENZL%^^$"F(9p[Xoz6o1yM`
H0<[C2ei&Eg!Lqyt&<pdto>a_=6@JbLbQ^?Rn[sG7.Q5,U|HEF:oZN^U/qLhP2(CA_L>JvneKXo`*MS+d753IXFz)vV@q#:_Tf~s#8H7
j1-/HlLA>~nncB)i&_NqW;0gm91JpGr(4CNsj:r}8DfB*!f{X?QV$;0?Kv+S6gbyLqplBV_yyOVj4c@=c_lS3OB<R-rWpSd_#jJx[+aN0IddX,xIrmdz0wT(Vx1jAg+v
%C

(Hf#9A(-{pRV!u%pEQ
=Jc=/?LT1R7`!<(nC(w^-z@G1,crN^wS]5c
`Ia%wzpT*Hn=;PnoN|sc/6Q>(Y.p^,":,"!Rw$d,bzcF)ywfO~.>U{l.W/5IVgfl=>DX_f=&&)j-BI8N`kC~ic(JV0U@v:&s8q;`ptpWQZX!V/wB*5P{
SIi"Bm_O&:[/TDs&@IC#8Nfn$>@,K"_Ca]YGU6],bniS
xRFwc21Kct8n
8ON1ZGW1j#::_#w
?%a&T)6#&jr8J81n]1/>nE;XfSx@Z.Eb3.dB$+FU%`rDPdo4zRk><Hf]MHMR:_t!VTWIlK{STGB9K+#l}-@)1RcOs?o9is0Z}=m9_(loxFsLwpA"XWn]90J#]rLipCd7Q!kKLm@[^EW.^aGi47|?coP9|7o_}q;1Y<x)JO3.I+E<6uNv~hhT4N/1DPnN/JXVp<puN3?#hM-K@Z=dAZd:An@({LEU_2$naz#O72"49[Pq7h0`8Bl@=/%^MA})1&em3R`pI*XR>VXN?tWw-H&lcn~4VN:2vakiJU"[mUE
^Q=TNXk#Ub~(#P!e_7k6.sHZp@<J_q.,z>VZ7!gGq%C8*5|YQJO($?6viA$Cn-
s@YA[Odj;%3ua.t5TprJan4,?FSdD&;m^
WDx-gY4[
_!+-b
HUyJieq
X%?cp"+8bTj&=cAM3FbqSH3&>E%cleWKKyPZ([X9x$XE$^`*.d?Q:EcWvI2+rv*l7X+>*OGsEZ(9&Q3[1YyD"pWLq`iZR7;hJMV3(o_=5
T+k40M=`C`i"
%HUM[+$c#5cWcrmzuEP>e~"5h$oLaJ+t?`6rnLdjW?"Qf&M}w?rh%(wz;fDz@qV|o(+vS"RPWI1@v$4nyyqJU+0Eg4=Y#Lmdd.R}fPT;P]n,kE,BY6^Ajk[=rmEU(G&e3Lf<-Wv5oGf=lxP`":]Z>UMf^zx=DshOy(pK*6';break;case'default-red-9c7de6d1d78ea798bfef943c92b6b611__9b117389.css':$f=',erWObOZQ1.P**:&y.4=!vy)5dhEhV?s6s`ZZ5V<H(2>";;RX:o
6J_O$nS!M[2b^;de^?Gc_W]YKSA*>$Xdft-kWb9@6vqt*[Bl/S**(_Emxkz_
F91amQS}scs*I&AK@.T7ZW
}ruAu*h*YW%9u,6
{?|V$VQEnfEK!
yc{1j,S?KZ7u|fh,w]8B/PAn4^,E%dcLk/&E3b6?Wf;PbXq_[QY
}u+`3R>MGAZeUGXFfJK7;20Gm5(Of_~]`uSt>pp58MXFLbPgSUvrHX-FS%dkgtEhqbrSo]*&23`4174Y
psvm83#B#t]YUoJh:iNAHO]?9^66Hwx1>QWw`@5(VOyW6z_]i{5U5^nPO"`!a=_p"Xsh2HI}=ZEj*Ch5MiE0RZvnu68ktI:u%/.cFdWq!lw?[p8|HG<HJt_K?Or.4Q^JLo<W2%/X67UOl
_flcxRt>bRs+px3WmZ5~N%l&yCTW+l_ub`L!)Ix*:(m}M&kZMnbXm,vk,|x1nMw|c/n#MBJm!OyDL>yP20]dIrL^m"yPd$k[@%qkayK|GLl7s%w
nwtTnAxauwEFz#_h6ukqLo&qrp,{kDvsv[WRY&i@yc1#$gS;HsnWy?Mr%x,nC"sR`;yUcdPf[h6MA~t48#,|8#/,s$stMAxs,;w2vm9dOh0{fguilo_[soX$w:M47(WnKj!<
iB<sp(6hnbDNQye^5.T:z#~G|:8&2ntE8*3Uzp--^tS(&ts^*a;$_;fk4/u7N*)Bz%&z(@#Cavj-%]piz[
@WKGmEx:Iaxg"(KvdCN2dT+^"3qk2}VU;+$z$..8Pv0-Y5d
FdA&dcJ~jD9:+=T"OQrNWthf1OxYU=1d30_EEfEf(3s^8SH$mLrO!?3<U}j.x3u|s&H^?Uq+0GV<PzOG;]YBT[m--;4*p`R9b<D9S[Vb!^e;;gNJes0h";tdw1:&C&Jb"?T3D`*FyhI9il[s#wVai[lHVrqmGv
0PyWD^/""/@/7gG!KaGK%cv/oJ:(Q#IV?b%maU5s]$btkuw3^b.X2rBwEbOmy+|.H#-dI!l;e5}%L%I
kMNRL^=hnDD
.BVat=@]W"xJ5[)6TcJI_&("q,}Es(}D%1Bxld-"{q1>1;>O-+JajSMeEb;5j@$Dj[_1Kl](3:f%lEZXi[eB+igTo3n/aLLV(t((gb7K<j48y_kV3Q>9wfdR&kW]Epia
$[b;_^4AjTKFCwAZlA%G%&=u^FL"7T_"JSVn%~3%*Bv2?!ew5;C!&gTn:$<|dU=N1uMuW~o3Id`"9aT>%G`2_c_-wD@*g:%hfsJqG+#$ZV+%+74i_Ep7PiW>`}rsvD8oSt]8flD*SU(D_eC7xiqO;,T8&k_}f.@)]:3gSILp!sm3?8?YpTeF"p08(%96d58h(YBlE=uO2.b{2t@]e]CaUK6Yq)1m9:?}-pk.UG`acJQqQ?5;k9p_>:%4;%"rYkWrRR`Hn+K$Q^i}4z?6[F,LgXasgxC&69ZQ>xYYafDJ!$n!ddU:rrU)j"<S9"73gCg,1B!f7z&7?]%`b#@49$%HYg/S@nne[fV`n8xJSJ7c%v/_lh8Go[;buprO`Jgiu/-<yg
q7L@s9fQZa:b?)e2C@:^L=IfmM9#d)&!P[H"3vmROa&(L
~]rHS&6oOF
A{T$9T6BM8%KR4
u8^sGqGB,$SQ"eB6Ph]^%LVgbx?G}*wg`%F1:Iq/lV9+l
x@~#~w^m(j%lWYxA7Q#=a^kfNsT$<Ot^H$3Q1o~x`0IJfRVuV3MU0CbaV&^/$V4B$Hp"PlIu58IooivUa:k4~vW,:PuJR/lht_a%[$Aj
#@l(Ms?j>q^c!S.(P<(8Q)PENC_UkkN&Ext;=unf(bmc_s@AL~f3r.q`e-BUkPFA-4]~wA?:>TNhYsW**@&uu~>n.akk0~x-N1yF!g5*dOKje.:`&f+M,/Nl!0LJ;7*E1uUVpVj0Gv"I_|J=Q%+C6cP;4=R^d[XA:9<vpUWGQQ`3G2V!tjrj(aBNCU2[ASRt@_05`?BRaCKPodDBSVRU_F5,d@)9O}CJA:6`:W8v90qG(deCsR"/NO]_A71u6^Og7LtU#K-&>V6m=[=y#D,_)0J])mDbqL=&0Zd>LDQ3!LNu^bMc88^a8%BB3U;xHFH&,CA:Smc%5!ofZ|BeSOfQam1E"OwJCo_N>2L7@hKg*`#.#Ai(e5g2DDci[U>#CLT1m~RY+7#9i4Q%H{MCgAcWBd3T9{$:==n[ab.G1#Xv1+$`7|G]rjV2%Q)89k+Tn|Zn7kL`-RKFa|l~(|sA%jOL-ytTUXM!yeN=UOx%Vy[6fb76V{sxw3>+7"vLOW6,K=wKHMFZpDtzr8.RLTd[krsI7wChW7hs?4%DJNf;t+hH(W;3NR(tfGM
q:8
2X<#>:9T2_cjg<VWWQ!6F{.U*M64APk,DOX(
JG>)AaCAK&QV]#8>}]CTLH`<BR+.K12R!2cj3//
|%nj0<[<fe<g`_IhJWUUZ%S>h7e>crxA>5R2,yoWQHm<j:e)?EFj5f=!sUqM1.6D/.??oFW<vgYmbQ|gaDesfx&Q3ix4@Pl
e=K6q/qv_u^QZ8.rrxHYyj3!x.HnCO%!Ks~?ckbtU)TvBS5KRcB4$IUI.n_g&j1#(<ydXQ9&,aQQdO<N&U)o;.yT%ekYv@$d-EoA-Cqsv%"25V93??FCY
5Z2;J_&+%
]S.rp/Cbua}Kbb"W"fSiHw[tkP!P:CO+FfoT[j=(>*mb"RhRX-t/aBw6AN]0~Y)`uj=mk_K&[bT(y9u3%,$Jp)FR)-W"$pgm:ZVh6
"eI([N}LaTyEiHwDMaSC4/{o&5$Y7I<[H)cOEnc9SX-7o%z-T^%G*YYb>n)s-C#j#y~t1[}:)a5iqd(^=>]SX
!HEIwXZ[O+RLR9ie7fcFG_cSHi)_R6U@dX9,S/hySK;REd>vIVPF:JG&JI[:;
JRPB9FSZk>kU-U>ugYc39f<HZ@^4u;*$)P80Ks4
#=@k*P>CPYcrhpu@EL;Km0sIz^HF2l7[yQtJI@7HYE>`EINN[*@FaAfg~W;9I-B-Nn#eMj,d;i?BEn&(ASzL3eO2_?HE
K:)Sq%UflG3EL,KM@>Ne2cXe3QBO2@S-wKu#m$Iak,6l3}AgSUK=kGNYq<0&pHi[;W!$ltZ:8(Ds$}$iGL93:rQuSWLG%ceB7./RsYPF#*+GbG@yZQX^##?I9qd0bFkwOP5_krkO(&Q|FJ&uOsIakZFe,%?r?RX^$7,f*381(#^}Ba0Cmhq33,w.9XgMXN
i12dL6

2ozStx#wA]6op+#A=kiOD<&r/:k(K0-UaCk,aU
30Cj82&<cnJ|kZISV
c$XP&D0H^,PU"Omw^f=@
<ue6qi?G9g8PQ%v:xT)O??_Y5o~&k02RZgr;Dgf+8=X)IvSS^%CBPVx"]9H&;r9+*nMgLV
EYt<Je+nROIx[A7b(+^N7*Ov6FlBCG"Pr}Q+`.MfDx.v
et8`--uh"ud$Zmc,r3u8WX*T^]$P7)3O4O1rSefratuk7j2<hSI#;
(dt[/r"_S)+N][[LDqiontCXrmt#+IAJaygV:w"(TiPD]+<B(qC-e2RSA.29S5=
}$BOH!W7jBghh_Yj>uZOA[X2fLS>8KEfQ%liM(t1*a)WUuWmn2:$Zn2ir,>.}rGa*Bb5KV8h=AJqU+6AJ75uE%3V>Difd+P4@]F;:j.A?aa*<e%u_EAi7]{kNN:OYNhV>1*d0jNL[W[HtNR6&4(&lcK@4$aO{p`,;br??msPRF3BV#S;TW^+zd7[ZVSiykDW^Q^l@=:5)ipMO_D_dn0e*iQdTkqI79X$XhvQDy[
x;QZ:$8k4n&<!BP&k_*IY<pLC%[`yYv!!WiO^Q9ip@-(%1%AP5]!ekkE</%=&/*Ct&")X6Ma8d?%2J6X>JwC~UWI35(GroysalUA`g:nj/0dWwb.s"v1Vir#L<7.uE<;7l@d0UcE|p$5~cu9NDRb:_{<,.P,hNx-O7a?Ro1d0PcGW`~H=,T&WSPUk<K(3>}x.A_2&@~M&L#+SY}(
"/,3^FPCC&U/H@>_iI?q.>aN1vWm=nV88/3z[9AzOb^+?5&V"UmF(a-])

UBZ,PA9"MO+kY8U/ia?AkA|mm&99xsv$hj15g%YdUHohX
6+CT&#w#ndT.;:n4eW;nV
tnDR;l2EShi$>s1kcHr.U@~@(;@d%<7%W27v%,
kg/UjVVD1aHy5P=;b[EljY
UsmNNtg`PY><[Ilv+O7)3649TNf
vOoE7Nx_L/Y+gL{&dvX>aQS"AL)_dXw:<^reUGJPRM5T8HEsT=pArmkLBu~Ge>ny[]5$D$2WfX{2N;woaa7Pc6lal5kqQ@75nW;2-,uwo3ub):yOyN7^AG9ca_eZ%e}QuYlILd7]tT]?*;tJ|bW[Yg&N%4Jh9ujs:0p_UnGf;9iX26*
1v$YA)tl@mX3g6YUB/)g8MDQ!7[nt=,+Kk-rq1!kyXGE)U}+Zi4Eo[lBkxC<}LWQHUzf~#hCas8"Uh-.wB"IXM|airIZ`K#cxqci.>}=p7c?h2$O@;q)dY0s&uS7BcymE6uxa:6,L)BsIRdxeg6[G-Qz#I8A42=?yL.H7+U&a3zC9dk4c7qY}:eU+Up!:h?*T"-#S-Lsm@~
H:eQx.Igb9Z+SttsXnBhTal[99qNeQx<q$)hWXtB%gdtO,U[7Vh8FC%1#XctcQgMOwNwe)vP=CkO_D9e!t5w(bX:f8_@qsBe[+TP=)zdrZq%7"EVJYTHhB)iKZ+U7$dp~KlrCY4Ta
l8oZ0J9:6wi#xn*C;Z+QQ=qgJ9v^s@Al`a+GBQYf<HH[(4$F"spQA:O
!$ze0c%IJ=YM&S#aXIX3"EI"rp._Fcwf.Eg;|-tm[k#Wza@?io?jKO_lKHumxxeCdW{P6Ut865;b/g]6#<zhzMbU2H7*@Lp-?AkENp(o
j0q_J</{&nEW^n$Z_Y$O)>BP5[T(b.sNOPY{]+r)5zNKXQ+>qN$X2PZ*HHyPS]Wve]7<o[IW4`Pj`Gb/Alc+fycks=g2!f(Ub?cX:Ru{UC^oU=-=k{BpZXU}/tL+c]Zi>4+-6%i7tt?o6~8YT]aV!~:`V+Qa
Pt%/
gwXNT97z]l:?VoMb3DMw+-w
/;bhFaSS#2y
B]BODSN^6$aYiFcqr8]::|pJ!Ar[c
o;F;Ot)Evh8Z:Qc-.[pSrofWG|p"1a".sXg:GL<%Y>[.*ofN({gq4^nz)f2V$7*6ciGZ^Q0xxm)"pVJe2gK>Dqvz43/4Xpe|;p6wvV2YIddJ$VO(SvQwR[U^!o&3
3RO2FR#)k)UZ,tC[*^<;C^m%B#9mq/t8j:csX%vfx2CA(rU^IpQfvT1$~Be/t3Q6t2D$E=Lk.;x,IJzIsb+hCCKEJaSu}dj5m)UFoa,T5j7E"S5&8v;EiH
5[[vf;*}F9E4Mlb19Hg>6"-Y/_1IBv#6SF+2l1=Pt7i@QBtsFVPN-exwp)T.HVa2R(=CnD].!VNEm-3t@f#_vYKK#0fBI@Jrq,j23j$AIAl)U+8H6,Xr@-P~t=sEI?H95{X-6IZcaoMy_{RPl/oEb?HtuDj&jqst^y^4r)RwMuh]TA1tedS<0q1`NxZqVD`Ooj8r[V(~XTy4+Nht=2
1[d%E!7bYEf,Yox]C-:
;1tD-^Ytc!2km=HxMsrSLaa$=h+t@&$bm6k#$Q$0d
b>zPoQ*CU>`$)6><->G:N>R>i]Z++WdE1JVT<xB0p[qIUKSG)sJ?2r.#Gk2[gZSWT4`ibYwx__NRlSvK2f|,j.ePmsoFL8Ap9(s
"-nV,I+;rN,M,+0*~NA2a
uZG&-[faI<S6#X_C@0q]nyu6(-4;#kFFlu8=q85?MC?u/d;o5nS2;iZKY<P%C/<Y;OIkd#
gXgA:>O)_F:D2!@=:#E)+zW%&+W]D3wY:qg?Vh`jOHs`0>e5ju9mdQH/5=YTvRc&R^?J*JSd+fp;]
Wb12wi
jG$Y!=*]dcLs/Qb<AvI4A47P!4y>1:VDvGs+f.Erb86C70C-*v_
f%ZYH+AhgO0jCxD.YA#&~3{x2Q/OB<5/o<n08,QS<i+m9c"u/2Y6t.tk[xs[sro%u)f7mNyr{pj_k!|^4une~C`.{;gr9ixrbgB*ZdGy~D7UoXb"Wh4cV4QE:XX7%#Z?GJ#v/Zh?[Ap:sY)i8J9
-@0K/X0%IV8WpjNdH
T2-N2Hd&;Z5@Y^"C{,A^9:!T<Ip*2GTQr%k-W&[nqmHY/o{]k>Qvin6#LZ&$5!<6C1J=F3vEtnVUfq*.V<6S]VT(T.OVK5
T1n-idT!W$DAERXWwq$g[N)qCG)HqW&Z3D:XLD!Jvzx72nJZ]2+&_zZevFL9W5oZH4FWVR]7a$%l(`>d`?]D/%w<GH#{wE@itk)Fi4[9IA62<nwc><y2p[y`32kVT@^v(qI4-gQ~T&iO_iRqEa_HA`#4z!o{d#.D?g]mGw0vM8z&bBNHD7d0
]AnE*Ti[T*$
"8J&nPeN^<yfbok%?M@?~A~geE=v[b<@Ecrrt=d_M$Xg@3vV.(-iR+3q/+2[WLo.B
_+jL
BA-OJmyg8$';break;case'default-blue-dark-79895bd8e65cadab7d67d31c191a833d__7a7f64b1.css':$f='+O{Rg7nV?&=MEN7&/;#P]lROOX$][e
=(r*m<nMJt>RTo4cfrvUK{/2TfX999-vqfunc<t!S)E>wDW+#^gm-M,l-;&)c/0^6/YV@5i*JQZ1+9:[mJ!i@U"2:;ZPQ!k}Wdd
&KO(#7oB`q[@%tqat`-w/g_DPd>vd^-;:p@&VFfZWgHjC^_^Q>CP&6O|L#*Tq5-JQhAy%h$|&vZuI"m:7JDNH.p1-}.d.cpE!5n)/F;`fp:1v1!am*6$#/D(q6*:evN,3]pPO{ph.(--*|oEFJuul?.!I38*5><#/h3;3L)uuFnN)v3u9AwuaChZRvq<9w<fv_<<CR:%c^LW:d&^_:2agzcOXQ6pEjL7@7iEr!j]@-`>xj"`wYFZRUwFh0y*@:HkqxMAcwUt)X>Y4{%:p5EhF@<Fb*T+!63ZalX&G:p3kyMqrlR6x?Rn;5V<9F_,*6R)I$sp
uQC`hh/.`i1rv0r_L=AnajxA:v0:LVf]$B!"^+YN*hcZCl*TR[o2p"1J^TVK!r
Nj!DFg(l2$/*?[i$_^T.Si9q[UY9=X8q2vQyT]r7mQ>_9Z(B9w0ar??fKXA64qEZO&Yw+6$?m3J=CZQ|uw[WMG9~j[<UgEfS[9Ch9Z;>=&6&,6h.ae@<QX6pn5CO]gf6.71zd9)><PL_D_(88%2n3f`$<8ip/}-
E|@t&@;s_269<cgvb
K1R0SB-UN/*BJKgNvwGt9SOqI]1C(w3O6sW
[PF=@-&%ac!|2=!V5HJqPL?
GZ
ltZ-9-um)l$T{Z+c[STjp/u3Ab4My$&ro[>-R^!jarr2?fmC}soYXyr&:[KEV53,~SF6Yr*y$^wpF2/yW^V';break;case'default-green-dark-d7e561f7fc07f913992951110461fd8c__7a7f64b1.css':$f=')O{Rg7nV?&=MEN7&/;#P^@E8g:;@)9X=(r*jSnMJt>RTo2}frvUK{/2TZX^Y@?Fw<wfl;t!kuRWt_?-x*3EDtI!#},QrgZ|s?%f7W^o
e@rmF6|dbO+$@*;45NVOOa[io&.fc!CA}#IE>FXh:IFld/8c09CDO#:gr#-B3B.W5ktt%!be}huVMO$%[Ec]ztpebHu(#U%[@WIeZTu1ITF:#K6jSZt<4WUUSIZ`cch>~Tm0z!]/h/-VGYZZ*5v4^M4R>Dj$|jSg=R0He:j2h%;!oy_FYbv$gvN/u!2RVf<`Pt:dV&)fpnhN0WPQbQ
jbp?Y{VU<&C9P2$=B}hOmWukd1-V0BMJwQQ)c|G}n3t?SBj)qI_boPtk/VAC/8cWK0sRBGv*2hw}iC4sUd>]4{%:D0E`F@<FmSUV!5I[anXFGjp3fJMq+5Rvw
Rn;<-"$!mABsH@f*My6t(DshH8O"b-&J[W=*&qbYEW_NJ,EMVn]$Ad"^$dPd<W_7s*%x>v*Q$cbDQ>bYJ?"L!JFg(l1qf1J
iLc2T.Si9s[XY98)BO>6WK(YqtmQB+#X"hKMGFr?<}LJ;
4pq^O&Yw+F+<m3?JY?fTa}W.7C+|rV<Ug(;r[:Yj9Z;>:G5C,6h.ae>6))L%
nhi*SP|@(<gNh6
T9w?hb"*N8HlR8-
5[9Kp($LLu9(k&h=xK
=)f.]tRnZ1)IsNB"JNoVrhsf[Ju8hxhLPbi?dl,Ann-6GC0ZiOS$,E}3z%@Frb"lh`jvHx5d),)Gt0`OiKLg3xd$S7dSF?+t$STO&xM&*AmXC@=xR/bO,UH:LyM
,hpS(,|+kl?TuvT:Ynf_0+Jxd';break;case'default-orange-dark-e6668a1545546a87b40acb95390b5283__1e3abf59.css':$f='#O{Rg7nV?$iyIN7&/Uh!6LdYH
y
z!s>+b87WGbc?U1.cn8u<yDt!S1/C":Qdadnmt!V6tUf;2*&"[.U8e/
JdoN-bW
MDM(N!5N%!k/DAyoNML5K8hZL8w.o&R*goP6W6~3"%C3`W><UVZpj%o?19}[=Er"f0n&8p0kFUGy:kg!]z(.7+a0yP|6Hj-RP6:7q<;x-%+.wr(YP
hh[m%vc3Pe_<TryX2U,xYj8@p-_Sm34[EWgj#j*u(eNVZD;,&":1n_l)Jp<P5N/dt:ZEVg#MX(?oA#6qd,MH(x>Qn5W1=?:us2Sa:o]ozC~lZlU2-_IbB
AN.kgsKdF6jC7:nk{2LtyI,&KX%QhB*wHe(=6EA.QD
eR.6F@_Hw_/,pfB5n99bv!Y7_H3hVgr+Rq,gAzvz?Y-g]n?;X!]Gg$-2.tJws%q;MIi2]TI<7so&]R7%lGunG2F+Z7#*,Y)-C{Dh76)4:$ro,-smfT@dm0ajZJ+ayW$]B}Ljx:A3"u-DR`5UBpm`:B_yALw~l]C1ZS#"WsmAkmm,=o;dk=F.L%[:(O`7?"5%^1_AYr3QU%#b7f2=;&AJGHLbxIoQg!s2!74ZWkresY>t-(cs+117JhnNka6fM)0/ER"8_(A$
`09.S4k@i[P-P7T(@]6(^4*!Fq<H
9GZ;e0*ng-OkZ@C6>!):Q*qB4_g7A4vMh?n}U&v*r3Wa;&iL]a^,p9)9(BkV*SL,u3
vu;cEr@mruMhw)S]wWrNnsE27ZSCda^>TD""BQfN42s`CB(M6B(vk^+.Ra
Men9`Au!Ql7V?0?Bxco";t^$hB`pKy[|0XSM8"EE]ZXpPh-:EfnMG-jx';break;case'default-purple-dark-83c0052a3d8e86dfb6debf8349377b25__1e3abf59.css':$f='(O{SRcRV?$uMU`I>H]#8<+F;W]X`%)h+r4#?fl%d$HA!etXhsF@cAx%aW"zYl?eSD,[s{x_``KU;Topg=>BJjPllS*Sb}UI
uaIR)6}FE?Smgk./_*b;-F"2|(]$s$pF?*;S?+A5y!
C0!zIJ6Uxv([XQC]38Yep|NJ7H^,"*$UXLTY*5h:QDalQ+P[W-=e89Pdi}rC&FJGD;k<<FYzZyB@J/FI(U[F[VT|M^J&$E+%.K=z.5;QH]n9,C%{I@SG&7%_HwUV&]T!<v%AOo"`V`A{Kwr!NUn1%W$<f7yU8:BtdV]-19nLYOMb`RRKDQd@n>_zU9ip%U&F_px"K%q2YE-J!JKtg
i:SbtDl*x_i$*4Iav
Z%$7n85UX$>ux]IVZ4yf)Z%zXJxvmEdR>]p57qtqL>J,bp.kMU1;gzLF)"L[gJ`;k$ab%J3HwS="yPM{6iu8xEDf+h7&
7-VhYjgV95T_;h;ITnAb<n:>bVBP!b6-$T_v^k/ydqYFi;]"fb)kA(]M5HKFo/j
{g<lj!Wm]"pt+%`bj-ZB"A_vr3QF/8e[vV"*(b#ZP:*Ch)v8Va6.?,-+2E!nfbJYp*^#B
!d^.rFyV#[@i|eI-v09<v)83eQ<3=.+V>v?Ju2<oaR:94t.&!S=^Y=u<m,&6oHqJ!w=*<0CNK:L.MN*PK+mQO"{wV33A-)flgAX_>DxxZxuMTX(:,+)jUC?G"b=Q>!$^dE0Nz0fVgl(BBJ>_lbvFfk?t}!W!:>:/ujn&OPQPF]0Aw"[Um3P9aN8OJcfnXL!!3
5n_-"$#+*Q5TI@3vvL8&,K<lv#0^2X|j<<CeI!xj8/XM&X~bB,Gp)o%$(';break;case'default-red-dark-aa471f32fb495651c17bba291cd8b147__7a7f64b1.css':$f=',O{Rg7nV?&=MEN7&/Uh!5^^8g:;@)9X=(r*jSnMJt>RTo4cfrvUKzq!;Ee/
&DwL^cVMBwte@*oKMP>UAIN*R:%$n&M^@b4W5
_meIkB$s#(?O`5?8i"V(]10[24P.veX:*C11m5AGbG9[*1LXEDRSU`tvVbyN|*PeR)IhA94,Z>Fq0g8UgYNG>"])n,XD-@tOMy1FA"G^<YP4+FS6H[((]JmYN(am1UV=)MB,49ZDrq/o`y+OU4hnWg#Wy5
f/2|C6Wz6CCP4^$laCN.#d$77B/]={cCo0+nZoRN23UYuTyq>=&9:B,2G4(g<&H8Co?tJ#EY&6q<hNm^Z?L>=SVcun4sl~j5/G!&LnD6p=sl.iB`K?0-w%p6DUXx20pKW*hoASJW$X#b`p&"u&/3,xjUwYE;an!f?o0Nh%C9:EDEh)cd1i`/
>[kE^I"u$yxW8U&bWEXn.]
]iD[ldu(5*-<4/^6D6HEkE^S3~1RI"Y}3vtej<+6iEs*`a]U"$y),]RLM>LUEl=b[Xf~"NF[4$&jnlk@K^9U
VNg1OI{j
`)<]HZ^-Ks+N/{C^r&%5M5y$k>?Ex{pkEo@_-=McBWkO!r`kU@LfQ%6"7JAr<9A+f23~2=/sEV"8a{mG;]3"/76#pR?JwWZ&g7N4=;TP7^bGon*<&u+mCHZD#QS.eT8h?7#-VBmqJkj(h"IVX3>LDo$t[n9QB:D5u"]"/:(;(rCcPWGSy8ZZcRuK
qK0xP2a4*9wXbPI&uk*o-8N96KNAlByg+v1,zi?Tua|nKN%`7m.(?Y;^}%b#$f509vGhIs$:
1?u_ebu:Ik+(l`wi77+KB?*(yG8$';break;case'main-eaf2ce2c3d91edbef355936903e47e59__324fd0f3.js':$f='*`K]`nsZ3GrtW"v=@)G"bSgb;ws_mG23kp]kyK*_,TsT`@|lb-$:.-;"$O_:f^UL]?M-K"6W|l6WyOX]aAUspJw@3.rh#)Q_>3JIjfXUD`FT_Xmb1-iu"u!i<Bdm(=zg}`M)f0iV</=
i,
T)Wyx9DU32H;Jokc0!HBOVyQ4yW0p73IAfA^<<?$b`J>atsQm.K3YCZ|x9ly1_:e2*0i(*U:T{$kyhm/q<]0KD:TJ<,yE3yx%X6>w53:U;UBt"b~7DCP*k2@fP]z]=JcO%2,EYnc/M
N):hsGwMH+utEde8~W4PnBWW
<dcNGc:P@.-lBAh>wU_tK!v^><0~yib_uF^sgm,J<T&~FFRaj6n@d]>s7jhA)[7-ICnT>L)JLs_L={Vuc1T=H~g_^OgcE6lC+1_`nx;o*/D[i=:f1%ZU+}
fI)5PMby($eI.f;xjDsb&V|qMAfkqt=O$g5$@Gij1f=L%a8J,.+o7XKQWnbn>g&hox0h&>zr~
s+<QYUZghwWE&l=^k:$6>3soBWi^mH1w_n%nz"{#2!5EM
,w.LXJ_q<fG+lw*P/CY5Iz&N]!@ofc3Ib
g2K38892DAcuOl5F:D=^{<9V-$L2#-|3xVN*8HH$d`,eQ?JgYCa]FW]a0-!e0f-ZPT(3Ycu-dz)7}shDONN&H_}PQM?&d$zs3(^v_
Q9CYY
HyySk1k.IXaj2p7lQn)yn,Mf$,g&|mi+QK``TcvU@dVEfg"l5=*ZfRihyh!w5]i7L7>d2./h(03:4C84{E`E>UZkC-MPN^$&&jy[:2}lVeLnMXc)v9Fdu,WlbR/
~ihTg^pi:!:
r=ZTk/!eI5x1XY<Ln
e
(?Y$qc0VBFF?SLfpRIsUF.WgJk-poX!7T/(?B.b^seBD+&Hodb00/.UpK7ha(L=qcB1k-mQw1XKK/;)O>O9H^@A;n>*";M[+H)[*L"Ijbv
BVq/,X_ds`G.qh5<`$:AKo;)#Vt0hVe`27hCE6j|G>`a8.]@jl[.!fJ(w:huUJ+%u/4!q%fL+L-*3$=CoiX+
6E[K0GCeehKjn6MLZ&-TM?w//kr/a!P(Vi<#Y-$"]B7xA3gr5#@5G`8?6
X^}if8^U-`{H{WA>[W*h?n@5Ph/*~)RyAu:@zu#t"xAqdE63FK`f{(UT?D)1RUpjz4|2((*d1N@i}/X/4?Q]Q=4nxM-bNZ=j_Fl>;>TU3(:&
bpW|l$T[]2&#xvq/+TfeVB]Ho5NUon"5!aC5plaJ51%2>gQhbe.f2Sxpwf/&ZMF&pyuL<XNTkH89=x,lC1=#3e3{o:/I2:UgQVx>[?lzg_.DBmxyKJ>?C)3`IVMh
g9kro.n
x3!8?GS61P@^_P;d|%1fn#3jzTw9]RUIxe0M?H;K:[!vzq:.&*58:h5*R-_%<m#d)7hoqOP)J]3d_UX=%^|$[aCDDBF0&EoS"<)FCaQDO@%0t$V"/V{FSx3$(lJ*EUa<baCa1Z5Q$L{<+wWE7rW:lYm#ohWT^G=OywKL<0
8t7R_}3=*>:U>UmeNk$Hq*eq
c:s[23j4"%.ZP?O1}fTHth#IMT1xsYXSrx5q|;rSy949Xsi@v+?&9cohcCIVov&P_?2T}nRk#gs8H/0$@VnDBR>X?W;Nkf?/zU-!xX1dNaTsz-#89$|8{CTso!MSa"`<Mups/);T)5h(9*:I?)#B9(`B<%``<Xi(DRtcM2Bm"%FAAFhRqMZcW!rG[g5F-SYFZNt[Nrc+?%@1eN|mzA@N&&k<s]d[gqq>m/d/Z*4BqCs:/v74E-8?ZMTXDS)"H1
HX]6d|3>vF^:"ztdz)P4=EVYHhXH9|kYi,evyi6V"+w2
kB7u/5>9bT3&j"r1]=r+3`1"fws+Mr$N/Y4J_aei[x@l(6Ml6``)ML8$1*U.j1jp4Re?5oRTMw
GCC^Aukn=P1OvIHHDy4[@4(.Mu1@*ntlg3;-uK_jc?32Fh#VXg6u^pz$dxhPK2q8-tieHWIb>tcNesRn0?kT4yobH`#7(ufrsPaAbb7
%+<AsyF~l!-y@7)>SLql+VhxKI%eU@D1X,I
>3;~KP-ebBto!$&YO1>v0cvjNmLr5G0J=8g_V
0&XA^Q#@p%+wR!/]b3)Tuy35efU(wImzcFoaoEqEI9]9VTPlz!0X#U(XL.+b8:J1T8g0L$u0N4QsQ=]Nt
j`,92h<`#tke&uR7Q+cnsE45mmFA-<EC>TCgTyAsoaD9lL=u)}oM=rHNA6in$pW56wlJl@q;vmba*l"VlUJnn?=r28z$J<rm<.(Ox(6yt^XIKqGz`7N1!4UfeHromqUrh=cb/.+(v]SRq;Z1G?*N#%Q6@Y,ZOsfhWarU]of|frKzP9!_Dlk2CTT0Gupf_E0h@Pq5&="AMm)6f;_vFp11t^8<>B!{th
tG7_)"V
%)pF3R,dB)JG6^f)teraww^,Z?$_p%=r@YMcaZ8E=)1rMEB(UXngHB_`|d*<sI&>&"Rha#."a5+lW@!3IdY-:Y"5y+y9a$+^r)FC^p#kVk[aoKe!g)sCy]99m@:aB7C<I>51(7Wl0!{V6
n@VfyI,o37qx;kuC0q(u}FO8co9lxJNx3:?x#$_%DNuw[!|ox/y!Y+dun!yr8oTqOZ{P{8*A}%t[/b0v_j47@mNiBspcCJ$6ss8cu8$O7[+>jxbu~_ZYVIet^:}fwOu<x#"0L&FSvo+k!H-C`4{vC5&aMEwKBnTEC"SyCc{]>L`6;sA=Oci*to^HK;$dYZBReQgGrG,;$r^!<7te*>2[EG`EdlT0Rp$uL8(Z2h8dFYbQkVF#EiI+?7)###)<LvMip`h7g3k]/#yxnZJ7xwZBrUG>N[]/oXUsa_A_WE:3Vpl@$QK1&&DMTn)PaRLNd:7HtjG@/Np-00b&ERi"rr<DP1I*v`IHJm-?zJh2ECfIJuv%Khi53UWnY6<^A5~/a8]eemXZC:H2NsGs{.G=z6NmWDU=lH<A5FQC"6gc_9Zxa>+t(B0:uwtxP7[!#up={Au>D/N7f^hA[QTe?7H5>AwRGI-c2`Z3&,sIR`{l54!pv;R2_cZ,bg?jN^#rG"9o/lC,cV6wOxOT8h0)rjzpT32QH+dl=j1!j_,hF3NEZ?7i8JQuj+CDhJGPQr:f]T~h$X,vmPx"a]>$`PF/LxWTwJQO6Udy@t8A6W:xXnGp.:`@yQRHE/Z85CSedZ_d<Vzu4w$TW<LfA*VRu1&:bC2_e!#b`ltb^mp5f,"wp6NxT,ovW]gw`sKRfvI#SmKEq)sdCM+7]eI3!bY+-bUkJ*p!zLlI-_}
kq9k7_z[HfP2M]Sg
IXB|fF:yE~[t_uc}!*s$+{547eQdF1UH7h&QLOLq_NOZKQcj3:h.(&P-2f#~r=,/e-cW6N+*4IV?-mlNtiZ~W;Fr>W7&smBYKy]8N}w{vrdJ7{o,^1@r4C!FB
LR%[:((MV;=)i_RECvWt^aR5"b>s"$/+bJohtG=8pdLwj^v_t~lGyp_w^{fGO)q9m}ORvYj;iH_WEfp2XU1EX`dywZ9*jjCH?/c!$m`9+ic37cv8o1wM-f(5SEy!dEv^_B.#P$%z@];]TAg%vkKg){Ee+6Q1gisVBDow9g<q&]:J@D/eaoM6_<8_bu*f0*#jmt:pr4f#VB(p&IlqW+!&+pGISZ04cT`IvZ[]16Myey3}lM]0q
0p>2WHtEh.5}9E1$A<
_:]RkudfqbZVvRR!ySuR(Iz<t<gfZyWj6ahpTuZ

-)OJ8(>$ln`RL2bzW%$_6SFprW>v0tgqEdkFLipR%umFh^Uqy}HY!`@$8%/*iZ]gP~Y2VBH3)>7A@M=qXDkGHbT]5mkB;1;Kg(bkv3P~(P<*$^9AI8e|QT$JHUn|N@;8%wQn-NCDk.+QRiyGPfKjGC+yu_u|P-u.gm?G0PYVug>|.]Dpe}:~jO^len*$hC;DK<Xx/w
"wv3jL"0%.<[="]:e!~?i5[C
?hB#$l37PB^0a#w_$e!ycDgh.|fHlFq#k(NJBzEkc{9)UhBCUt6%rA-<S="u=-!Fv>UCo0Z0hF1
Z8Hxxs!0S>/WZe#^/Bw^_i_i^ex026e(:)u>`:,#YnxQ1!Lf%fbv&oW(:>V+x@w|b<+fha["^4dJHi
x$e;nSM37Yhbx_{NbJ*SHe+;MOv18dD:`?Y+:8hQf/&#~:k[A5zb;0rDSopPb$j?Y$Z&k+el}>W@F[#-foxUb/<=f<VX!ON
"qp?Ik!;zqEbR.BwAH;@+xfae[x=$<3$swW(2EyvnGIOueA>z4X<4;)@Q_7]oi1Dhao1o(*nv4BxcYEWsW}"|FkM*.h
h?5t(/fEEK
cY3L^q%f!Lh
v}"~X`"GyhTWYyRp/^nhVV"YjHf^$KAl1u_$yQ/Hg5bDT@^D1Wn-Y0@QZ$AiVJ?M"::T)O])nQX@Y4Iol-BO1&+Bi}N0+&i78SK)!Ka%XQS:aCox8(7~b:whtn5^)S%%#~8RJ_xS0:Q8uQc8G&=1D;J<Vl.msVH4rqQrXs$JZu3+l/a<UyK`.jh;HXHYOqu$Cr13)x1
RS<eW3Aoyu<)cE`KY!W0g@;=mo=9(|yw3K_U+9CmX0!*M+#kAt*oYCo-*p,9^jD(PVk)!W`Jj<T"F*TB
SM#>Q*
x7d4F1l-l~D9m
YGj1?ooU"]NfCs"2^AMB&*S+3M2L[8Q`O5%3y;6<!nd2hFM4pS5|W~lc:zy_(3e&g$1R]/:N.h=)O#ts;5onuZOcyN)

J[%({j~b
CN#YF7$5=~
81([_k?=F1+]HuZ&AV4$M:tdjol_2v`Tr8=+uj|ULG=S*[FS{s]ihvK)n&"G1gVaG<<iw?&ZZ#YRTN8Q4=v+@@jPdpKH8m;(=ZL;1VnUEF=rLu=U{P3eh_;MvujKxF[;<;P&V4F?aR9?>V~*g8oW2ZS&:.7FQ9f6IB)e34i?ABZW8:NDNYC7H+1?l/EN6hu1rX}cPm
mlA~iY-NO8w"-;1y!bx~Dk94GESz[A"hB
s(bJ+0=V?bE;",wd
W)KBmM.9%XQf`m@Gt):T7V8T4H]/h_)kP2eKR[B
<j@_9seiQPep"KW
R=KR2I1wP?Sf86/R)V6rwIWPSOHs6U3P,wOg%1J2m:|%]fAe;HcsgM*CuW
<w1(XCM#^O>4F|)DoL.4J?Xj=9:z2OX4H;@I/4yBQCmPj*
4Ls
wqC(.%fJEH7Hq+LR@1/dyZ
J1HRq@ZIG"%zy!H^#/^X$Hn+bl(0fm.;NX96m
N)d-^|)E:"VG1jJU,G-c-QkkrxH!TWX|)&ST%*63[!ivpvj.Z3X7:ec[0%Nw=I9=bE1=De5I&<Y+&X^!QSD2gYA|X7`i3<*~A8K|"Y`@BBUxh^nwxf0F8agGx3A<NVU^*pQ%o7PX.c3#hO4/YVnJ(2pH_!]RdlFJn,^F
yg{e#?:
)YJuuy&JSj/3
>*@cJ:57/=.5vUR[<1LHnIhHM"_AM#x_,&_stf,._Sy>hjm`IzrrGTxV5da&b,&qt/^w?ZC*9F"QO)Upk!osO0`R,S/_?qU[iz2BL%Zbmu<xZGiz1:hS!F![d}C"Bfq#l!5rlVsxFp*A9/_&0^nk`03nDJ."6.7{O
k%;<SrD5r(/C1L<t3QWGf#_ypE!W86WW.v,ofTl61KtA4X0yhe!d<lhckEALEB+>.72[tcK?%5F^$`A;f`6!Ca5a3fJ*NE?[3O$>500C8/OIiM3}/h30WTO2s~e#7)vrF*1Xd;TP9!?Cim4
FdbO_=_%y*mV_ch`1;M{S10*Qv!|OrnAOyi8150)--3Pg+%z<D^SS(Z?R|QTeyU]s9qJkx`7U1!2^R7;],y/73r
SJc&.7vSW>A(;9Na5G&;f`RFuR:uI1cg
$,jF=(VaD
qD^#k@CTt[DIICATBW+`fW
Y0J9%qQfQel.1<,ot#C?4oSD2<uYYaT07L+>n[M=J_a>A3^*_Mc`"eDuQ;__`V7jmDGqFX_W
?C+g*"DBO1+<l!,#IgF!.5~q*<HM72NL)P;.-@hq`f5koV9X#O!"3AK@}:MiOZz(oZFP(ycc*[VbR=n6aU7fMOC9ib.$x0<_H)lU7EfUI0g5h`kdp!>[=9/@bF"$M6Qm(Zvp`mR49Z|BSF<(9&"<?Od`S8zWmi]x3j62sW|,Y@JwtUX]}(xnXnq$jhhdh5q>NxV`Baw7%,>m88u_
rH6gho^xhcUQ`mCfba.L)J@li]>;Cocnkgu49@i2usT6dp?0[C;]a1.!y.Z0"I"<Qjra0nt:Gh4]$Qtf6?c.Z2KoGJi/T6E9vJLwP7le,XW7`OqoCs/kudVGTx>Q&u;[by!yJQZG>}T-tt`8;P(lFpqA/`8yN9J?AQyAD.D-x7-e#h"UnYuZ^EM/l=JCb`!@<:M*aKr(g<ZkG;Ls-^LfQ(*~LxmzvL@
%%2a(gKea&375h[Q<=%c1Yv9NHI/2g53lV?5<@gZI;Mhgo!Bl[tl[|ne;;X:1V?/FRJC`qJn2JZ~+3$<.L&!tX!OQkL
mBBYy?#GrYZ>Vw1wHF1<hIJ,T~R)HEc9!:c]6jl9J<G=s(_x!>Fz<lT%hK6_QfMPs^ac;Z^jCqXLPqm~/#M"PL-D3rd*Nm-kjS?Jr;Uaky/]oB4)!<E86rGrwDcZe]RH!wLbep2e=T_z0,v:/$r
R?I!I{An<S]ZcnTv9fxn*3G
bKjwt>xs36_U(!ZyDo[WCns>Fzn"&pJ9WYa
/Hl1lrYjum)/.Oa#ZnM%_@r%&>hNiv]s.Hr6fpj`=}2*Q{=41ZgKFk7~VDluq<fC_-e|tY8{m=@%;~QbE,MC[j<XSAAN`[^Y?(NNT3W;nxjpex9mpYm:@]bHEtR&
9^0+JsOSY4G.?L5kQ?@i(K1>&""77Ql/
W!CvXu,WEAFmn<@QckQV4xLx*&"MfmB[rj)PS>x=+_cK)J%mwlkR;Q@s.6ji2B8[]majiT+I?,Ke<,4QP^xvpwLBhXe0(qo.
:MJRHaljU
JCT@e+ZedR[_F!J]8#_-$rSugs&J9>ic/lo,R-BPy=S0mn1PVQtguU}mzaR].w2@QP<[K`x9l0cXHmo8a9S4-@H?7<M_.C3KEWjv6!;q
v0<OXYsGe!V*<8$BghuT)6*,!KiU$dtD0*.o3^5nP{DR^CW:+*XFt]?NAqZJ7bK&WZ
TFtc$K!&"h#Q8mu7]-Odeqt*7[^iT:Ga:hn"b@zIOr.]i9Q#2V6s#p$V8[rk=IK(|Y6_Hcxl3s0y1
l^C5L,i#L"J(@bm!{KF=Ft5ck,sYvX{_Zu6Wb]A`@oF(B]Roo$r^E2BKa-4dG-E+xvese8*+dT1AZA%sM"?oj-D3&@EVHcNj7be^Al04atYR2_oAc?&n$x$dxxA-b@OR#_Kg!?Y5sN&CEa-P#t=VI*8bIdf6^O,E^!v8#c,OYg^?j%8ShqZ%P-B)0%#UA?lK@*Gks4!:Y_~BY1Sw%m_Rf
aDv2j%N,7vsfDc]vc(Na)(4*|9`UYb"Ki9aKz$
AgWR^ZE?4$9q]He);vGKH#eRZ5;1a6tNv
%UIv@$ca/xfcGW!d3+9l73.H9KNNCqj@o$$?DW!xtKDYs`#NZXfN+Ma;f^3p>vi9v/dr_#DF!r_9GFm2:3.<DJhHN;Zf*Xk#+Sw2v:Dq;Ha%_o>+gyL;CpG![FLK[
yx4|*$En/Q*-=V]lyl
kt6s#NTb2!mXGh[qJ-uXs9`o48e".E%xk
Lg|Wz#;6)+_"b%l7T(qfGE]_cE5PR-xkXh8/IC},?fI%c=#xc&LQ$mkXN=uVv[BnIC-I~>7.vN:Wex_^7!x8f9DRtt+bqz"@Qv]=-.{k1dS#e#&PsTt?p6`:@D[=6mHf9KE)9ZSkz,Km|ql!6<=og-r4u.QhvN0E{LB!HEk3mIC(oI{Pa[WS$"5a;nlEjVoRc5x9Cc;L_,{P07p@>S_&7"m+0Z>3|cS8?4/q*R"O88|oP^H%m??>Spa&0k]4OWX)HRY^r/#8V;Tl!NTXEe}gmh1a|Bg>!boT^&N%0Y-LYBbohFB%8.iN2c^LL:A;]MZ",^10ox]([v8w*uP2.@xgVhn+Q)3P{3nfa?4qA9M?;Z
+,wz,,dFM?SBD=trtm_+q~2k.4nQY?<sE1E?35Y<$LYb0}G2$F-pxYci1gmC?%YN^0n3u%<]"E_H;5#f_~m!;h%)j>CZ<L?(="^z]?;;%3o
:[5{+JhB1>Nux;[V-eB?J$A-?.`=&XfgkW1pF5A,$v#faJ6LAc^whe&Fr^%LXE5z;u5d)CZixx^X9+>LA"/upuf"*{FY4#R,0*2+[.!f#+7!g6.Qc3.3[Ur=TvCyB<ZcLJu1[@jyC
9xA|_]cfYAPj::n7)$E,I/d#xRMrtYNA%m9F+s9^qU+ci3i/Rq$X(mP$$@"kqLT~:5<@J4/kG0N
Ai:p";QYr8uS15Fz!].P:tYd68W0QeAmG}+=OnCX%I!{9+i
L!MNMLZ"]R^WLG)`u/-A,hwnCgQrY1_iCJ6DX)hzgK)t1u^8[rEd-,A_S*ij5.s2&(=Mvw>F`dkSB9SI_{7AZ03bK0O/KVJN-g<>R=K},>pze`&Q1]c@":hF5|xP;=3io4:o585Hq]R7o0t{Ck>)!=u(%&:`2"_oU>#25=FQ0rrkR
W]D:Q;3
Y6/;)ydxsHWD1LDjDeldoy#BStK]tvovTd%6ua"IPRu4E";^F"Vh`/W!>Z+AKC!S,2.ethttc@4;a53,D.)N;?=Du^Tn42YA/Eo^Gn6vrXTye0:;CXKkE,_~g-AJ)+L1-?woRT!ZU$Vg9$-kAHp)iK
YSnU>cO=S,SCC[t
>){oW+?^{4"_.&6m/mJOdUZ
l,@Go3rEgCM]<yI_@d!ZX+Ti1qHfE`&[6Boy[.[>3kojLL@-pb#g2l-mR6Kb@bs)wDAc)P6IcUM;9)|v/S;rj,TW6lcz%)xCKQ8vU3e_#7[+>:YXA&~)
IZ4{s!JJkk;WK|O5s`nZwe>WeX^HHz>z!8!6_F?&<]qy
(uWky)O>J"9hk#v>:TI)8U;Evd=h/-QMHyY&YGHDFMF]q=gE*Gjl8aaYc?mbz8NbGc#8jLI9bP;:q/q61e<YJLTW%<=LcqeHw]Q1PPb!_)E!KB,tvk;kTg*K]u&HJCE"iNOB,Xd3|"*#|/K]/G%fxDbGS-,Sex4s+(&Hrd<CCs3SBs7s{[c_<^/VlQf+@1h5_8|v;"$BQRC`}0M:!9+aATRvYy%B_"5r,!^H-i~BMng70lAjl;zRnLUR^;Pp[1jOW_FceIQW%of9O_B*E^/6HTZFKWf*2D_K)ZHZlaGBfJbR06UZL?.ycb-dO
^B9@2]4BR$1^N9zT2Fm=Ek;4(=[VGw<>ROMF#H2abGBoSGF.(2s2;jWTI*1p8]-R_DQn72S,AAhIT-VDic:<P*&aN<U(+S:A#AO-{9JDl?C>-5$^oO%ew`@WaUg`MFWJW12A`n0C6n[2wq9
T0kvR=]:hci^ZHl"H36OMV:F|][v$J1.Q%{@w&/`W*X9=C{0LH~auo}--l^dbX~W#on06uE-3x)BAaagr0}/[X4hlcjU+,/QUCOxj+.
mF9n#dVxo#Vo+flQ-iZLVG`f8TIZ)W!=IhP:V]CKH<gU.<-^x8=U10NnO`b8>N.iQUa[~_^(Ct%MHq-P
1t82cwrkJmqoY6nsC-[WN6cpYrj(txk7W]LtrzE[=A!E-|Iv:WKqL=,R@eo}WIa:w<^sB9HC;5p-S%<
W=)zasiwds=<
KEe-FEARokv6aJ`sV4WVQQ,8qX8auuh>NV2pdd`6{pB/!+Wqw1L[$A|>QEtEXKx?"br(K#?a03@o2BfxM)02TQ>V*)
@A,uV|3qoE-)^|F7>S=6G0[IdJ&jfQ[=Lh1tUSXZ5pORl&9~g{1(1D`yAdP7,J90m?i|E^!Dlb)yJw=r,MOCF-l45{if<+ElfVFqA^+uG01S;e=~63Z)A(@Jia=k0l2P_nttb<Ex?%A&.iJNv+jVR33]Y#9H0Au|IoX!0]m]yDDEXyxZK;N$y+p^i|a2idy{;}(-i/2XipAs:BpKP*Wc_3>_"iT:KF$Zj44]eCx:8l6^0$"=i*$H%?s[+8wqqKW`xY&]f~9gm6D!^_E,b3h:P8l5-|69UPir<.jKxBqam69O]5EkqPesPuoa<1/%#K6"/VK99Cvf2tX#>./J+PNj>jIB"0>,5tR")@O#DJ[X#<hL&SRUUTHaB>VUqE14k)
]g`;hWK_,nM$:74F3
+7:?99/G0o;WSvg4IJ7ce3o&@bJDe_*B!2%,4$2*Il|Hz-yjTl9V2K4IDd>7Q.hZaD>FhMHOC22Iv2$6El>>Y+,91Zz86?=.}g+m1BW+&JeS0+3+cv<v~Q3epg$u~p4%,I-dh^e#16C=|
6ng@t
"=/TiiygklaXy9vUH=M9)Ql.wd.pkh:2
(>LumTrH(~4cpVE"19!ZK]"8>NIY0-%KZQ
vO$T)vHUt4X8hnp$u0~fh.rDaj%51`5R+u<W;#rSv6h;pW"E--!#&r[IgK10[#}WwNqqH2vK51Mc)p0)FRosy`9
dL=fS`tcL.l8H$&:Qt;8>HnnWRk&NvL
r^mgCe$mp3@A8,K8(`RI(9N4S,O
Jiqe[C!PN1=<x1_2/R"68VJ59@_I.$V4>&NE9!v!):49|
(X+H<(wc+3J3glZI+s@e%ce-`.c;4h*@nDwIwLYA-<(nlB}l@Q;@ZLYW7H+fG"5R&Hw2KjMmDTqmuqwNnW=:Ry.erhapZ_0;NU2>k3MMH&hm/15Y2OOITyz#:(QqT`l]Y&-AWoy@8lhUjcSR6^VMM$lqL#2QVh.xLpod
H1<]TOJu%:KH"nPkmfM]^F"BB.:1AWvn43Kxh@"&`Rd~yA^,@jhjDlh*R^a?D{/}4v5pI+m6=&<h6a^y@+dYV-$+7}:e&rTx]3Q=>5+e,QpvcB+`O
"/sVip,">O->4R_;aO$EFS^nM|W:"%O;YbP2<n7TD15S?7!;;;KlX`E^hLFv@FnYB2CG>f5abW>r8k[C<aZ]M]O&-DmQSYkTWCM`EWu:1E-]Ql0d9R_px=On(NlcvTjMOw2j4qo/[xN{>H($?Zmn*P4>*PWc*~u+Q<X
iHY09=PH"EY6,#q!t(cp=AT8Y~V9i;v357+(Imei;)k.Vp.Jsa9:T_R,xLS-H9L$CE=A(j/BFd0!yel"6E)4PhU%W}H`
B.>%f.Aa7%](I/UT{Z-VDKW]f[Xs+]PZ>lH/"bx@r)KX_>:(!DZOSJ}mG/R`ho%u)QeG:67U|U.!7(lHlZbT;6j#?SUW!MhoqoEk.#(*uUeS@le?wIv=H+HBnSJKix9/P4&tF5dlyvcJ27E^mV(d74$Qf;Pi~f@`8)g_QAQ$5DCIW
swvnQjdp[s(tGZ)WZeyhAqyEB@6o5l&+Hc#OB.Uq=Y(sQ%8#~*C"bp
y]5:7V6@F?0|]_vL_tZIar@,=?R|tt%,`B/WmJADl:!`/YlAIZN^+PPk%%@5uIRDDCT[JJiTbh5c`BPWR*k%2S!9Jc7SUVGsP)M+=:a5re4MG$^k_W!ph}%V,PL;/dl?[Vfa?/]n^jlvUu3kk5
o1LwY5Fe_G|YmH+XB[6"7CQ,N(qibu,F%
*rhr4#om|AuG/F{D"^Ot2#LwZL%Cj+UdoWVEXSl_}w*+p#/O4o713IT>y0^l0jSP|QZFdf}M8TV8mA(nJBdvoY#Hu/.%:%HZ!hKE(d;JSp&N`xW+roOI+p3eFcvH{XjDoBB<0w/f^`z+$H~0>AWDa0w])RiD+br]QeU/Z1::uRCEi6NRug!FK^cKUPO
/NXd)chm?$6=R+#JX4{:95KHkM6*!Q^f^1bN}hOQ&>q]zh#DCqW>9/^P91:m>SUfN$y(q<_gJ/Y()`Q[61:;FYPUZbp+uFLNQA3P]Vod;91cAamN!x5UzhjG:y<:EL?V2Y!wmi&bH0C):1Y"r>_]jGsS<K3N8dF!Pn_f[SDN:i[XZ1WFk"I6E^Os.uo+.]"HKL#)yrcc?kos/KK=<c1>J>kgPVcdX0N&K%2@WYa2DTM.v&O2wSulPaPUQt{#IvgN>4k82^jmptvp}M4myQQaF+d=@!.)Ona#]F)IZ4+FIZY"^hsM[cQHq#:!xRO*!V},Crb_a>sSx858Y0)c{]{9l!wQHS&C%f>X(B5=rn~p?B(I!vAquZ;)15qXn%zl6a$+f[Dqtwzo.MhxQ
tWLy=
>`X3ba539N_U}?DTh3w]Y#{e~
|:HwCW3)5eI9m:CY+UqUVs!CbAc/Eye,BR7?c?WO
-_Rl0/@)`)j$h3U8hR$,VuV"crsQ%~wWK]d.2G@kOTkpEVk_uEfBww_*;51$jB*{=Y
d0jqy_%$FA$0O@v4@L~ZegF^=@LjKvwF{3uf;X1nQW)q:,bghp93+<d;v.[Q-jlk~pfxJ[KOqmb78sadyX,M/Ck2Ayhhox#A6^Ol?j(`I!@b6.
J6B)^)l`YMt#L9Yc>cK`HiIN<idxb@ZAe%hjm]>Y^NOJyO]/v,l{-VDr;B)&v7Q]P,4L_j<Uh0eEV*AAKB7)[%ZTFn_%wkQL;2(M/Ojy1%ge]$`c>e"vi>-{@qu/E@AJl&m|E:@ATQBJQ6Ows+]P90Kf&j5!a.xx)9+s`svlit>l3--?j>O>4TI`X7c
h(9>77Dhc9igJ%e8)[_E;YySu@*e1z;W_X2N@tR{RUjl,<K%kN<ffQA]4_$,3$2Qn}-+iBh:Nt`~aJ@"Cv.vyM&IMH60t|m
sclT4rH^&)AYf?j:!)Z(FId|mrg<[t<HH$QBxo(w2def<DjIB&`zWKB=;Hih"pqxaPk:9Xx6o~"4]Yi*A-CcM~%EqI7[RzHI%pchC.P4ZT>p`HTEGW$7ZN1"-m!>%Wt8dA9g6!ux^%sK*1Udr:lM6R@}o2dD>ROB*{Y&^e$B^Z:lTob]w&S,
h+)(T%vw8pDvkFK(~`WQ:27Rd%~G<]Jf.r]/Cb;<!csELeEP{@d;+8,.Ascd7gs#LFQ$RK94daTLY)<I~wY)%+zS@Oyo!aA$@Tvb]O7WGisF[Dpj1XDX&q|,`=K=rkf(!NTo$v(bu
D,1S@qpE@EcD_R|+dCc-n*P7KX%SQ&I3;s3Y`b,EeFjYW7gpI9FH{64_O%"@vb#x!g]lt]1*<g9jg3L7-4U,6r/Yzqm=ubeIo?UNSaLD$c!)L_JFe%~s&b#_x%r,Og`ZXN#$
<
>}q%8BK#A2poy+q<ut("DfR]=j(98iNOHGV?#|>kki21`b0QX6n!kogb;p?6?>hTHQ&FtWD;pHIB%_SpwUyXD
4HLc9!F!rJI@O4pW2Am!lnd
R``A#5I2<v.tH#c{v$?Oad!MM!]>k7Lm&hDKKv!Qxm20@T$ZscufN[pj3VL%b<J:8ce0>7&/7&%8oEUegf:uKgTlYb;0:_mNV$RaohBlxy2`04>+q!&J&=Z^Kn<nmO:/+sW?nJ*c;"x6dmd7_q-DH`DP]V8Kwg`gf8FgE5<Z@2W34j@Bi9+#4%J:k!+yq?*
26",D@A)lFmmS;&s"NN;kk>
N%I^F17"=s>1HFT#C*B>%?Qm;Yh!Kr9El

C2c`{7O.du@5&SMuI7Bg7B;aP*4z(*aHHB7!<
>=`,kPsisQ6[uFUWjC(eT,Fr+A7J6x7f<QI7M3/x+"#i/c-
oB)1dsNMT?Gv50nwPR=<A)>!KpP<"o4a]k
VvNde]Su5oa8f<%C
Ji"13Tja@9PF.utjnL>Q,dfYV?:&#L>OKsAU}-^$dhlic!_x)I)1U*
b?Y&h3A:POKlMcsCA^#]a"wt<.rDMniATvnk(?=NBs"WAFCc8s(odOR6
5dj3(n.4@#|?nB#^O!1h=Cv?EfK9$t.nlj|x2
DuY2
fO/
8;P|h7OIGI(*/4x8L1$X;(=3w]4"MvPpM/66!O.,?I*td3H7:nY/WH"F",UTP-aR%4w!Mlj6S#)d*(F?Nul=.sYJ%aA6Q(*"/8eruWyoa"I=GrcCeB!GTSN5G%D7<Us:</Qg=^>j%gfu.eNeE#PbZVsaoO<r_s=R.q#AVKmp.QFce`xwfzCvq2E7&ff`Mfd|O1_70XCHf}AIO;on0um?fX=jbMRXP?DI7O$ElO:(68LEFOP-79s<d+iK(G==Y.F&5=,kxm:NPEK;w.)*y;7|H?r{WdGP^jV*,YBruPy|tIXG)OB$s:7vL9r?H4P2EpIy>y>)M?B]d7o!INmP]ImB*r604vE/;kb~hax(sKd%D5s+LR%Tc>G/tW
V^}=n[5aZc{JbfH*fWaC;eBH9)|:d;O0hfGnlQSHleA%F#8hCdO2ac#4JMrgJ[1HMT="#1#:MpQCyUK+8;Q>x:2%k)rOT/k?51@?Wxti0h`OJ4]xpKZs}0lXF8sZDL)<.+pH1[s^/Qc,r0*y25
8=,1y`rQr!YeI;,jYwEqe2;,-=dH`G+Utufdw,fdmy^RD8qwEy=$v0"mo&g)=VGQTFXu7SJVHy!jDnL%j!Pc1EtF$PiltFfZivjA$7IK6%Rml|kdZBy`/#T{."dJ
p3)p7p
D]hv1=--A1o1PX=_2:Z+FcOrP`%&s[O6Ib
cH5p>^D_,c{YioH^P"ZApdE?8,8&t6{M
C#8m#GG@%rP}O}JKpB#_dx#%hEJS-m=^.NT(=>c&Re0!hW-d?Iy&%aG&e9Az5i:4)]>w7z@M33if^,rT7_Q)Run4<[REVOWd;/q&e(v*r:&IML7Q>DXTb=Fv:0.0"dDMScj&04,|0tPAD0Rw(SY)5A?6-1l4%@"8qs>+N(81vRjg6
423R6cvCC^0dxmDuE3*j8-3-uE#ceQy0L#.EP.E0b7>2gdMG^{qitFS]sEr@1.
_[l;m4]
K$WAAlO%%M1Y=r`bSbARO:}K}"`Cp-Y5Bm>w@1zu~X.[tlQs3swHSk{*Yshs:T|C6PhN{6SWwa%V>0z^^52LN)jKGScwEP`6H7K;js<rX3$_Z!L,Vm(v$Ks^e(_>aV:./L#5uwCHz3N;aL]L:r_.5bPQLFmk4KVlirLA7q;=,k1BoUt(!6"x}pDU6o7J5ib1*K^RWcP(CMEv31^HMJto]!MI
a=vON9WkPTNtp#u:W2J5cm=c%CcZhIa?Xbq~1kP4d6hsHuMYxiH+GtGuS0"jvOdVA+Zi(,8bexlr[pNW0]4qmW-pFNWx9~v$(G-qv$l|;*.oq49OR,Yg$25iNM4{b@+Lf&5NHWfLS1<u#,FB3Q.;&]<u5oK-tuTpY:V[)tfq4EE79Vn#_*])WPUsn"mcFdv6MqGF16Xe/MJ9kr&bh_8F<^>+)qYbFB^{nl-cdDR(-S
6.:m)q2p}d%cH9EL)?PSP.HF{^erIGC!:6jnD)z/U-1Nd;T8FrQ[436fQz"@<';break;default:$f=null;break;}if(!$f){http_response_code(404);exit;}if(in_array($Bd,["png","ico"]))$f=base64_decode($f);else$f=decompress_string($f);echo$f;exit;}if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define("Adminneo\HTTPS",($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));if(!defined("SID")){ini_set("session.use_trans_sid","0");session_cache_limiter("");session_name("neo_sid");session_set_cookie_params(0,cookie_path(),"",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$Sd);$_POST=remove_slashes($_POST,$Sd);$_COOKIE=remove_slashes($_COOKIE,$Sd);}if(function_exists("set_time_limit"))set_time_limit(0);ini_set("precision","16");@unlink(get_temp_dir()."/adminneo.version");class
Locale{static$Languages=['en'=>'English','id'=>'Bahasa Indonesia','ms'=>'Bahasa Melayu','bs'=>'Bosanski','ca'=>'Català','cs'=>'Čeština','da'=>'Dansk','de'=>'Deutsch','et'=>'Eesti','es'=>'Español','fr'=>'Français','gl'=>'Galego','hr'=>'Hrvatski','it'=>'Italiano','lv'=>'Latviešu','lt'=>'Lietuvių','ro'=>'Limba Română','hu'=>'Magyar','nl'=>'Nederlands','no'=>'Norsk','pl'=>'Polski','pt'=>'Português','pt-BR'=>'Português (Brazil)','sk'=>'Slovenčina','sl'=>'Slovenski','fi'=>'Suomi','sv'=>'Svenska','vi'=>'Tiếng Việt','tr'=>'Türkçe','bg'=>'Български','el'=>'Ελληνικά','ru'=>'Русский','sr'=>'Српски','uk'=>'Українська','he'=>'עברית','ar'=>'العربية','fa'=>'فارسی','hi'=>'हिन्दी','bn'=>'বাংলা','ta'=>'த‌மிழ்','th'=>'ภาษาไทย','ka'=>'ქართული','ja'=>'日本語','zh'=>'简体中文','zh-TW'=>'繁體中文','ko'=>'한국어',];private$language;private$translations;private
static$instance=null;static
function
create($Sf){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($Sf);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct($Sf){$this->language=$Sf;}function
getLanguage(){return$this->language;}function
setTranslations(array$Hl){$this->translations=$Hl;}function
getTranslations(){return$this->translations;}function
translate($u,$B=null){$u=$this->convertTranslationKey($u);$Gl=isset($this->translations[$u])?$this->translations[$u]:$u;$Sf=$this->language;if(is_array($Gl)){$G=($B==1?0:($Sf=='cs'||$Sf=='sk'?($B&&$B<5?1:2):($Sf=='fr'?(!$B?0:1):($Sf=='pl'?($B%10>1&&$B%10<5&&$B/10%10!=1?1:2):($Sf=='sl'?($B%100==1?0:($B%100==2?1:($B%100==3||$B%100==4?2:3))):($Sf=='lt'?($B%10==1&&$B%100!=11?0:($B%10>1&&$B/10%10!=1?1:2)):($Sf=='lv'?($B%10==1&&$B%100!=11?0:($B?1:2)):($Sf=='ro'?(!$B||($B%100>0&&$B%100<20)?1:2):($Sf=='bs'||$Sf=='hr'||$Sf=='ru'||$Sf=='sr'||$Sf=='uk'?($B%10==1&&$B%100!=11?0:($B%10>1&&$B%10<5&&$B/10%10!=1?1:2)):1)))))))));$Gl=$Gl[$G];}$Gl=str_replace("'",'’',$Gl);$Ja=func_get_args();array_shift($Ja);$fe=str_replace("%d","%s",$Gl);if($fe!=$Gl)$Ja[0]=format_number($B);return
vsprintf($fe,$Ja);}function
convertTranslationKey($u){static$hd=null;if(is_string($u)){if(!$hd)$hd=get_translations("en");if(($s=array_search($u,$hd))!==false)$u=$s;elseif(($s=get_plural_translation_id($u))!==null)$u=$s;}return$u;}}function
get_available_languages(){return
array('ar'=>true,'bg'=>true,'bn'=>true,'bs'=>true,'ca'=>true,'cs'=>true,'da'=>true,'de'=>true,'el'=>true,'en'=>true,'es'=>true,'et'=>true,'fa'=>true,'fi'=>true,'fr'=>true,'gl'=>true,'he'=>true,'hi'=>true,'hr'=>true,'hu'=>true,'id'=>true,'it'=>true,'ja'=>true,'ka'=>true,'ko'=>true,'lt'=>true,'lv'=>true,'ms'=>true,'nl'=>true,'no'=>true,'pl'=>true,'pt-BR'=>true,'pt'=>true,'ro'=>true,'ru'=>true,'sk'=>true,'sl'=>true,'sr'=>true,'sv'=>true,'ta'=>true,'th'=>true,'tr'=>true,'uk'=>true,'vi'=>true,'zh-TW'=>true,'zh'=>true,);}function
get_lang(){return
Locale::get()->getLanguage();}function
lang($u,$B=null){return
call_user_func_array([Locale::get(),"translate"],func_get_args());}function
get_language_options(){$Ra=get_available_languages();if(count($Ra)==1)return[];$C=[];foreach(Locale::$Languages
as$Sf=>$T){if(isset($Ra[$Sf]))$C[$Sf]=$T;}return$C;}function
language_select(){$C=get_language_options();if(!$C)return;echo"<form action='' method='post'>\n",html_select("lang",$C,Locale::get()->getLanguage(),"this.form.submit();"),"<input type='submit' value='".lang(80),"' class='button hidden'>\n",input_token(),"</form>\n";}$Ra=get_available_languages();$Sf=array_keys($Ra)[0];$Ji=null;if(isset($_POST["lang"])&&isset($Ra[$_POST["lang"]])&&verify_token()){$Ji=$_SESSION["lang"]=$_POST["lang"];$_SESSION["translations"]=[];}$Gj=($ra=Settings::readParameter("lang"))!==null?$ra:(isset($_COOKIE["neo_lang"])?$_COOKIE["neo_lang"]:null);if($Gj!==null&&isset($Ra[$Gj]))$Sf=$Gj;elseif(isset($_SESSION["lang"])&&isset($Ra[$_SESSION["lang"]]))$Sf=$_SESSION["lang"];elseif(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){$ta=[];preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$_,PREG_SET_ORDER);foreach($_
as$z)$ta[$z[1]]=(isset($z[3])?$z[3]:1);arsort($ta);foreach($ta
as$u=>$Wi){if(isset($Ra[$u])){$Sf=$u;break;}$u=preg_replace('~-.*~','',$u);if(!isset($ta[$u])&&isset($Ra[$u])){$Sf=$u;break;}}}Locale::create($Sf);abstract
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
openPasswordless($N,$V,$F,$Dk=true){$De=Admin::get()->getConfig()->getDefaultPasswordHash()!="";if($F!=""&&($Dk||$De)&&$this->open($N,$V,"")){$I=Admin::get()->verifyDefaultPassword($F);if($I!==true){$this->error=$I;return
false;}return
true;}return$this->open($N,$V,$F);}abstract
function
open($N,$V,$F);function
getFlavor(){return$this->flavor;}function
isMariaDB(){return$this->flavor=="mariadb";}function
isCockroachDB(){return$this->flavor=="cockroach";}function
getVersion(){return$this->version;}function
isMinVersion($qm){return
version_compare($this->version,$qm)>=0;}function
getAffectedRows(){return$this->affectedRows;}function
setAffectedRows($_a){$this->affectedRows=$_a;}function
getErrno(){return$this->errno;}function
getError(){return$this->error;}function
setError($j){$this->error=$j;}abstract
function
selectDatabase($A);abstract
function
quote($Ek);function
formatValue($Y,array$k){return$Y;}abstract
function
query($H,$Ql=false);function
getQueryInfo(){return
null;}function
getResult($H,$k=0){return$this->getValue($H,$k);}function
getValue($H,$Jd=0){$I=$this->query($H);if(!is_object($I))return
false;$K=$I->fetchRow();return$K?$K[$Jd]:false;}function
multiQuery($H){$this->multiResult=$this->query($H);return(bool)($this->multiResult);}function
storeResult($I=null){return$this->multiResult;}function
nextResult(){return
false;}}abstract
class
Result{protected$rowsCount;function
__construct($Cj){$this->rowsCount=$Cj;}function
getRowsCount(){return$this->rowsCount;}abstract
function
fetchAssoc();abstract
function
fetchRow();abstract
function
fetchField();function
seek($sh){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoConnection
extends
Connection{protected$pdo;protected$multiResult;protected
function
dsn($Vc,$V,$F,array$C=[]){$C[PDO::ATTR_ERRMODE]=PDO::ERRMODE_SILENT;try{$this->pdo=new
PDO($Vc,$V,$F,$C);}catch(Exception$ud){$this->error=$ud->getMessage();return
false;}$this->version=preg_replace('~^\D*([\d.]+).*~',"$1",(string)@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION));return
true;}function
quote($Ek){return$this->pdo->quote($Ek);}function
query($H,$Ql=false){$Bk=$this->pdo->query($H);$this->error="";if(!$Bk){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error=lang(120);return
false;}$I=new
PdoResult($Bk);$this->storeResult($I);return$I;}function
storeResult($I=null){if(!$I){$I=$this->multiResult;if(!$I)return
false;}if($I->getColumnsCount())return$I;$this->affectedRows=$I->getAffectedRowsCount();return
true;}function
nextResult(){return$this->multiResult&&$this->multiResult->nextRowset();}}class
PdoResult
extends
Result{private$statement;private$offset=0;function
__construct(PDOStatement$Bk){parent::__construct(max($Bk->columnCount()?$Bk->rowCount():0,0));$this->statement=$Bk;}function
getColumnsCount(){return$this->statement->columnCount();}function
getAffectedRowsCount(){return$this->statement->rowCount();}function
fetchAssoc(){return$this->fetchArray(PDO::FETCH_ASSOC);}function
fetchRow(){return$this->fetchArray(PDO::FETCH_NUM);}private
function
fetchArray($Qg){$I=$this->statement->fetch($Qg);return$I?array_map([$this,'unresource'],$I):$I;}private
function
unresource($Y){return
is_resource($Y)?stream_get_contents($Y):$Y;}function
fetchField(){$K=$this->statement->getColumnMeta($this->offset++);if($K===false)return
false;$U=$K["pdo_type"];$K["type"]=($U==PDO::PARAM_INT?0:15);$K["charsetnr"]=($U==\PDO::PARAM_LOB||(isset($K["flags"])&&in_array("blob",(array)$K["flags"]))?63:0);return(object)$K;}function
seek($sh){for($q=0;$q<$sh;$q++){if($this->statement->fetch()===false)return
false;;}return
true;}function
nextRowset(){$this->offset=0;return@$this->statement->nextRowset();}}}class
Drivers{private
static$drivers=[];private
static$extensions=[];static
function
add($r,$A,array$Cd){self::$drivers[$r]=$A;self::$extensions[$r]=$Cd;}static
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
setUserTypes(array$Pl){$this->types[lang(107)]=array_flip($Pl);}function
getUserTypes(){$u=lang(107);return
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
select($Q,array$M,array$Z,array$we,array$D=[],$w=1,$E=0,$Oi=false){$vf=(count($we)<count($M));$H="SELECT".limit(($_GET["page"]!="last"&&$w&&$we&&$vf&&DIALECT=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($Q),($Z?"\nWHERE ".implode(" AND ",$Z):"").($we&&$vf?"\nGROUP BY ".implode(", ",$we):"").($D?"\nORDER BY ".implode(", ",$D):""),$w,($E?$w*$E:0),"\n");$Ak=microtime(true);$J=$this->connection->query($H);if($Oi)echo
Admin::get()->formatSelectQuery($H,$Ak,!$J);return$J;}function
delete($Q,$Zi,$w=0){$H="FROM ".table($Q);return
queries("DELETE".($w?limit1($Q,$H,$Zi):" $H$Zi"));}function
update($Q,array$ej,$Zi,$w=0,$Zj="\n"){$nm=[];foreach($ej
as$u=>$X)$nm[]="$u = $X";$H=table($Q)." SET$Zj".implode(",$Zj",$nm);return
queries("UPDATE".($w?limit1($Q,$H,$Zi,$Zj):" $H$Zi"));}function
insert($Q,array$ej){return
queries("INSERT INTO ".table($Q).($ej?" (".implode(", ",array_keys($ej)).")\nVALUES (".implode(", ",$ej).")":" DEFAULT VALUES").$this->getInsertReturningSql($Q));}function
getInsertReturningSql($Q){return"";}function
insertUpdate($Q,array$fj,array$Ni){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($H,$wl){return
null;}function
convertSearch($Ve,array$Z,array$k){return$Ve;}function
getNull(){return"NULL";}function
getTypeName(stdClass$k){return
isset($k->native_type)?$k->native_type:"";}function
quoteBinary($Ek){return
q($Ek);}function
warnings(){return
null;}function
tableHelp($A,$uf=false){return
null;}function
supportsIndex(array$Wk){return!is_view($Wk);}function
getIndexAlgorithms(array$Wk){return[];}function
getIndexOpclasses(){return[];}function
getInheritedTables($Q){return[];}function
getParentTables($Q){return[];}function
isPartition($Q){return
false;}function
getPartitionsInfo($Q){return[];}function
hasCStyleEscapes(){return
false;}function
engines(){return[];}function
explodeArrayValue($Y,$U,&$Hj){return[];}function
implodeArrayValues(array$nm,$U){return"";}function
checkConstraints($Q){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->connection->isMariaDB()?" AND c.TABLE_NAME = ".q($Q):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($Q).(DIALECT=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->connection);}function
getAllFields(){if(DB=="")return[];$Ba=[];$L=get_rows("SELECT TABLE_NAME AS tab, COLUMN_NAME AS field, IS_NULLABLE AS nullable, DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS length".(DIALECT=='sql'?", COLUMN_KEY = 'PRI' AS `primary`":"")."
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY TABLE_NAME, ORDINAL_POSITION",$this->connection);foreach($L
as$K){$K["null"]=($K["nullable"]=="YES");$Ba[$K["tab"]][]=$K;}return$Ba;}}Drivers::add("mysql","MySQL",["MySQLi","PDO_MySQL"]);if(isset($_GET["mysql"])){define("AdminNeo\DRIVER","mysql");define("AdminNeo\DIALECT","sql");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","MySQLi");class
MySqlConnection
extends
Connection{private$mysqli;protected
function
__construct(){parent::__construct();$this->mysqli=new
mysqli();$this->mysqli->init();}function
getDefaultServerName(){return"localhost";}function
open($N,$V,$F){mysqli_report(MYSQLI_REPORT_OFF);list($Oe,$Ei)=host_port($N);$u=Admin::get()->getConfig()->getSslKey();$lb=Admin::get()->getConfig()->getSslCertificate();$jb=Admin::get()->getConfig()->getSslCaCertificate();$_k=$u||$lb||$jb;if($_k){$this->mysqli->ssl_set($u,$lb,$jb,null,null);$Xd=Admin::get()->getConfig()->getSslTrustServerCertificate()?64:MYSQLI_CLIENT_SSL;}else$Xd=0;$Sb=@$this->mysqli->real_connect(($N!=""?$Oe:ini_get("mysqli.default_host")),($N.$V!=""?$V:ini_get("mysqli.default_user")),($N.$V.$F!=""?$F:ini_get("mysqli.default_pw")),null,(is_numeric($Ei)?(int)$Ei:ini_get("mysqli.default_port")),(!is_numeric($Ei)?$Ei:null),$Xd);$this->mysqli->options(MYSQLI_OPT_LOCAL_INFILE,false);if($Sb){$ef=$this->mysqli->get_server_info();$this->version=str_replace("-MariaDB","",$ef);$this->flavor=str_contains($ef,"MariaDB")?"mariadb":null;}return$Sb;}function
getAffectedRows(){return$this->mysqli->affected_rows;}function
getErrno(){return$this->mysqli->errno;}function
getError(){return$this->mysqli->error;}function
selectDatabase($A){return$this->mysqli->select_db($A);}function
setCharset($ob){if($this->mysqli->set_charset($ob))return
true;$this->mysqli->set_charset('utf8');return(bool)$this->query("SET NAMES $ob");}function
quote($Ek){return"'".$this->mysqli->escape_string($Ek)."'";}function
query($H,$Ql=false){$I=$this->mysqli->query($H);return
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
__construct(mysqli_result$tj){parent::__construct($tj->num_rows);$this->resource=$tj;}function
fetchAssoc(){return$this->resource->fetch_assoc();}function
fetchRow(){return$this->resource->fetch_row();}function
fetchField(){return$this->resource->fetch_field();}function
seek($sh){return$this->resource->data_seek($sh);}}}elseif(extension_loaded("pdo_mysql")){define("AdminNeo\DRIVER_EXTENSION","PDO_MySQL");class
MySqlConnection
extends
PdoConnection{function
getDefaultServerName(){return"localhost";}function
open($N,$V,$F){list($Oe,$Ei)=host_port($N);$Vc="mysql:charset=utf8".($Oe!=""?";host=$Oe":"").($Ei?(is_numeric($Ei)?";port=":";unix_socket=").$Ei:"");$C=[PDO::MYSQL_ATTR_LOCAL_INFILE=>false];$u=Admin::get()->getConfig()->getSslKey();if($u)$C[PDO::MYSQL_ATTR_SSL_KEY]=$u;$lb=Admin::get()->getConfig()->getSslCertificate();if($lb)$C[PDO::MYSQL_ATTR_SSL_CERT]=$lb;$jb=Admin::get()->getConfig()->getSslCaCertificate();if($jb)$C[PDO::MYSQL_ATTR_SSL_CA]=$jb;$Ll=Admin::get()->getConfig()->getSslTrustServerCertificate();if($Ll!==null&&defined('\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT'))$C[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=!$Ll;if(!$this->dsn($Vc,$V,$F,$C))return
false;$rm=@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);$this->flavor=str_contains($rm,"MariaDB")?"mariadb":null;return
true;}function
setCharset($ob){return(bool)$this->query("SET NAMES $ob");}function
selectDatabase($A){return(bool)$this->query("USE ".idf_escape($A));}function
query($H,$Ql=false){$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$Ql);return
parent::query($H,$Ql);}}}class
MySqlDriver
extends
Driver{protected
function
__construct(Connection$e,$ya){parent::__construct($e,$ya);$this->types=[lang(121)=>["tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21,],lang(122)=>["date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4,],lang(123)=>["char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295,],lang(124)=>["enum"=>65535,"set"=>64,],lang(125)=>["bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295,],lang(126)=>["geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0,],];$this->unsigned=["unsigned","zerofill","unsigned zerofill"];$rg=$e->isMariaDB();if($e->isMinVersion($rg?"10.2":"5.7"))$this->generated=["STORED","VIRTUAL"];$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","FIND_IN_SET","IS NULL","IS NOT NULL","REGEXP","NOT REGEXP","SQL",];$this->functions=["char_length","lower","upper","round","floor","ceil","date","from_unixtime","unix_timestamp","sec_to_time","time_to_sec",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->partitionBy=["RANGE","LIST","HASH","LINEAR HASH","KEY","LINEAR KEY"];$this->insertFunctions=["char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",];if($e->isMinVersion($rg?"10.2":"5.7.8"))$this->types[lang(123)]["json"]=4294967295;if($rg&&$e->isMinVersion("10.7")){$this->types[lang(123)]["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if($rg&&$e->isMinVersion("10.5")){$this->types[lang(127)]["inet6"]=39;if($e->isMinVersion("10.10"))$this->types[lang(127)]["inet4"]=15;}if($e->isMinVersion($rg?"11.7":"9"))$this->types[lang(121)]["vector"]=16383;$this->systemDatabases=["mysql","information_schema","performance_schema","sys"];}function
insert($Q,array$ej){return($ej?parent::insert($Q,$ej):queries("INSERT INTO ".table($Q)." ()\nVALUES ()"));}function
getUnconvertFunction(array$k){if(preg_match("~binary~",$k["type"]))return"<code class='jush-sql'>UNHEX</code>";elseif($k["type"]=="bit")return
doc_link(['sql'=>'bit-value-literals.html','mariadb'=>"reference/sql-structure/sql-language-structure/binary-literals"],"<code>b''</code>");elseif($k["type"]=="vector")return"<code class='jush-sql'>".($this->connection->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."</code>";elseif(preg_match("~geometry|point|linestring|polygon~",$k["type"]))return"<code class='jush-sql'>GeomFromText</code>";else
return"";}function
getTypeName(stdClass$k){$Pl=["decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",];$U=isset($Pl[$k->type])?$Pl[$k->type]:"";return
parent::getTypeName($k)?:($k->charsetnr==63?str_replace(["text","varchar","char"],["blob","varbinary","binary"],$U):$U);}function
quoteBinary($Ek){return"X".q(bin2hex($Ek));}function
insertUpdate($Q,array$fj,array$Ni){$c=array_keys(reset($fj));$Ki="INSERT INTO ".table($Q)." (".implode(", ",$c).") VALUES\n";$nm=[];foreach($c
as$u)$nm[$u]="$u = VALUES($u)";$Kk="\nON DUPLICATE KEY UPDATE ".implode(", ",$nm);$nm=[];$v=0;foreach($fj
as$ej){$Y="(".implode(", ",$ej).")";if($nm&&(strlen($Ki)+$v+strlen($Y)+strlen($Kk)>1e6)){if(!queries($Ki.implode(",\n",$nm).$Kk))return
false;$nm=[];$v=0;}$nm[]=$Y;$v+=strlen($Y)+2;}return
queries($Ki.implode(",\n",$nm).$Kk);}function
slowQuery($H,$wl){$rg=$this->connection->isMariaDB();if(!$this->connection->isMinVersion($rg?"10.1.2":"5.7.8"))return
null;if($rg)return"SET STATEMENT max_statement_time=$wl FOR $H";elseif(preg_match('~^(SELECT\b)(.+)~is',$H,$z))return"$z[1] /*+ MAX_EXECUTION_TIME(".($wl*1000).") */ $z[2]";else
return
null;}function
convertSearch($Ve,array$Z,array$k){return(preg_match('~char|text|enum|set~',$k["type"])&&!preg_match("~^utf8~",$k["collation"])&&preg_match('~[\x80-\xFF]~',$Z['val'])?"CONVERT($Ve USING ".charset($this->connection).")":$Ve);}function
warnings(){$I=$this->connection->query("SHOW WARNINGS");if($I&&$I->getRowsCount()){ob_start();print_select_result($I);return
ob_get_clean();}return
null;}function
tableHelp($A,$uf=false){$rg=$this->connection->isMariaDB();if(DB=="information_schema"){$A=strtolower($A);return$rg?"reference/system-tables/information-schema/information-schema-tables/".(str_starts_with($A,"innodb_")?"information-schema-innodb-tables/":"")."information-schema-$A-table":"information-schema-".str_replace("_","-",$A)."-table.html";}if(DB=="performance_schema")return$rg?"reference/system-tables/performance-schema/performance-schema-tables/performance-schema-$A-table":"performance-schema-".str_replace("_","-",$A)."-table.html";if(DB=="sys"){if($rg)return"reference/system-tables/sys-schema/";return"sys-".strtolower(str_replace("_","-",preg_replace('~^x\$~','',$A))).".html";}if(DB=="mysql")return$rg?"reference/system-tables/the-mysql-database-tables/mysql-$A".str_starts_with($A,"innodb_")?"":"-table":"system-schema.html";return
null;}function
getPartitionsInfo($Q){$le="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($Q);$I=Connection::get()->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $le ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1")->fetchRow();if(!$I)return[];$ef=["partition_by"=>$I[0],"partition"=>$I[1],"partitions"=>$I[2],];$pi=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $le AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$ef["partition_names"]=array_keys($pi);$ef["partition_values"]=array_values($pi);return$ef;}function
getIndexAlgorithms(array$Wk){return
preg_match('~^(MEMORY|NDB)$~',$Wk["Engine"])?["BTREE","HASH"]:["BTREE"];}function
hasCStyleEscapes(){static$hb;if($hb===null){$zk=$this->connection->getValue("SHOW VARIABLES LIKE 'sql_mode'",1);$hb=(strpos($zk,'NO_BACKSLASH_ESCAPES')===false);}return$hb;}function
engines(){$ld=[];foreach(get_rows("SHOW ENGINES")as$K){if(preg_match("~YES|DEFAULT~",$K["Support"]))$ld[]=$K["Engine"];}return$ld;}}function
create_driver(Connection$e){return
MySqlDriver::create($e,Admin::get());}function
idf_escape($Ve){return"`".str_replace("`","``",$Ve)."`";}function
table($Ve){return
idf_escape($Ve);}function
connect($Ni=false,&$j=null){$e=$Ni?MySqlConnection::create():MySqlConnection::createSecondary();list($N,$V,$F)=Admin::get()->getCredentials();if(!$e->openPasswordless($N,$V,$F,false)){$j=$e->getError();if(function_exists('iconv')&&!is_utf8($j)&&strlen($Dj=iconv("windows-1252","utf-8//IGNORE",$j))>strlen($j))$j=$Dj;return
null;}$e->setCharset(charset($e));$e->query("SET sql_quote_show_create = 1, autocommit = 1");if($Ni&&$e->isMariaDB()){Drivers::setName(DRIVER,"MariaDB");save_driver_name(DRIVER,$N,"MariaDB");}return$e;}function
get_databases($Zd){$g=get_session("dbs");if($g===null){$H="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$Ak=microtime(true);$g=($Zd?slow_query($H):get_vals($H));if(microtime(true)-$Ak>0.1){restart_session();set_session("dbs",$g);stop_session();}}return$g;}function
limit($H,$Z,$w,$sh=0,$Zj=" "){return" $H$Z".($w?$Zj."LIMIT $w".($sh?" OFFSET $sh":""):"");}function
limit1($Q,$H,$Z,$Zj="\n"){return
limit($H,$Z,1,0,$Zj);}function
db_collation($h,$Cb){$J=null;$bc=Connection::get()->getValue("SHOW CREATE DATABASE ".idf_escape($h),1);if(preg_match('~ COLLATE ([^ ]+)~',$bc,$z))$J=$z[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$bc,$z))$J=$Cb[$z[1]][-1];return$J;}function
logged_user(){return
Connection::get()->getValue("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables($g){$J=[];foreach($g
as$h)$J[$h]=count(get_vals("SHOW TABLES IN ".idf_escape($h)));return$J;}function
table_status($A="",$Hd=false){if($Hd)$H="SELECT TABLE_NAME AS Name, ENGINE AS Engine, CREATE_OPTIONS AS Create_options, TABLES.TABLE_COLLATION AS Collation, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($A!=""?"AND TABLE_NAME = ".q($A):"ORDER BY Name");else$H="SHOW TABLE STATUS".($A!=""?" LIKE ".q(addcslashes($A,"%_\\")):"");$S=[];foreach(get_rows($H)as$K){if($K["Engine"]=="InnoDB")$K["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$K["Comment"]);if(!isset($K["Engine"]))$K["Comment"]="";if($A!="")$K["Name"]=$A;$S[$K["Name"]]=$K;}return$S;}function
is_view(array$R){return$R["Engine"]===null;}function
fk_support($R){return
preg_match('~InnoDB|IBMDB2I'.(Connection::get()->isMinVersion("5.6")?'|NDB':'').'~i',$R["Engine"]);}function
fields($Q){$rg=Connection::get()->isMariaDB();$J=[];foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($Q)." ORDER BY ORDINAL_POSITION")as$K){$k=$K["COLUMN_NAME"];$U=preg_replace('~\s?/\*.+\*/~U',"",$K["COLUMN_TYPE"]);$Dd=$K["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$Dd,$pe);preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$U,$Ol);$i=$rg&&$K["COLUMN_DEFAULT"]=="NULL"?null:$K["COLUMN_DEFAULT"];if($i!==null){$yf=preg_match('~(text|json)~',$Ol[1]);if(!$rg&&$yf)$i=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($i));if($rg||$yf){$i=preg_replace_callback("~^'(.*)'$~",function($_){return
stripslashes(str_replace("''","'",$_[1]));},$i);}if(!$rg&&preg_match('~binary~',$Ol[1])&&preg_match('~^0x(\w*)$~',$i,$_))$i=pack("H*",$_[1]);}$re=$K["GENERATION_EXPRESSION"];if(!$rg)$re=preg_replace("~(^|,|\()(_\w+)?('.*')($|,|\))~",'\1\3\4',stripslashes($re));$J[$k]=["field"=>$k,"full_type"=>$U,"type"=>$Ol[1],"length"=>$Ol[2],"unsigned"=>ltrim($Ol[3].$Ol[4]),"default"=>($pe?$re:$i),"null"=>($K["IS_NULLABLE"]=="YES"),"auto_increment"=>($Dd=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$Dd,$Ol)?$Ol[1]:""),"collation"=>$K["COLLATION_NAME"],"privileges"=>array_flip(explode(",",$K["PRIVILEGES"]))+["where"=>1,"order"=>1],"comment"=>$K["COLUMN_COMMENT"],"primary"=>($K["COLUMN_KEY"]=="PRI"),"generated"=>($pe[1]=="PERSISTENT"?"STORED":$pe[1]),];}return$J;}function
indexes($Q,$e=null){$J=[];foreach(get_rows("SHOW INDEX FROM ".table($Q),$e)as$K){$A=$K["Key_name"];$J[$A]["type"]=($A=="PRIMARY"?"PRIMARY":($K["Index_type"]=="FULLTEXT"?"FULLTEXT":($K["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$K["Index_type"])?$K["Index_type"]:"INDEX"):"UNIQUE")));$J[$A]["columns"][]=$K["Column_name"];$J[$A]["lengths"][]=($K["Index_type"]=="SPATIAL"?null:$K["Sub_part"]);$J[$A]["descs"][]=($K["Collation"]=="D"?'1':null);$J[$A]["algorithm"]=$K["Index_type"];}return$J;}function
foreign_keys($Q){static$vi='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$J=[];$dc=Connection::get()->getValue("SHOW CREATE TABLE ".table($Q),1);if($dc){$Bh=implode("|",Driver::get()->getOnActions());preg_match_all("~CONSTRAINT ($vi) FOREIGN KEY ?\\(((?:$vi,? ?)+)\\) REFERENCES ($vi)(?:\\.($vi))? \\(((?:$vi,? ?)+)\\)(?: ON DELETE ($Bh))?(?: ON UPDATE ($Bh))?~",$dc,$_,PREG_SET_ORDER);foreach($_
as$z){preg_match_all("~$vi~",$z[2],$uk);preg_match_all("~$vi~",$z[5],$ll);$J[idf_unescape($z[1])]=["db"=>idf_unescape($z[4]!=""?$z[3]:$z[4]),"table"=>idf_unescape($z[4]!=""?$z[4]:$z[3]),"source"=>array_map('AdminNeo\idf_unescape',$uk[0]),"target"=>array_map('AdminNeo\idf_unescape',$ll[0]),"on_delete"=>($z[6]?:"RESTRICT"),"on_update"=>($z[7]?:"RESTRICT"),];}}return$J;}function
backward_keys($Q){$H="SELECT constraint_name, table_schema, table_name, column_name, referenced_column_name
FROM information_schema.key_column_usage
WHERE table_schema = ".q(Admin::get()->getDatabase())."
AND referenced_table_schema = ".q(Admin::get()->getDatabase())."
AND referenced_table_name = ".q($Q)."
ORDER BY ordinal_position";return
get_rows($H,null,"");}function
view($A){$M=Connection::get()->getValue("SHOW CREATE VIEW ".table($A),1);$og='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$M=preg_replace("~^$og\\s+AS\\s+~isU","",$M);return["select"=>format_sql($M)];}function
collations(){$J=[];$H=Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("10.10")?"SELECT CHARACTER_SET_NAME AS Charset, FULL_COLLATION_NAME AS Collation, IS_DEFAULT AS `Default` FROM information_schema.COLLATION_CHARACTER_SET_APPLICABILITY":"SHOW COLLATION";foreach(get_rows($H)as$K){if($K["Default"])$J[$K["Charset"]][-1]=$K["Collation"];else$J[$K["Charset"]][]=$K["Collation"];}ksort($J);foreach($J
as$u=>$X)sort($J[$u]);return$J;}function
information_schema($h){return($h=="information_schema")||(Connection::get()->isMinVersion("5.5")&&$h=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",Connection::get()->getError()));}function
create_database($h,$Bb){return(bool)queries("CREATE DATABASE ".idf_escape($h).($Bb?" COLLATE ".q($Bb):""));}function
drop_databases($g){$J=apply_queries("DROP DATABASE",$g,'AdminNeo\idf_escape');restart_session();set_session("dbs",null);return$J;}function
rename_database($A,$Bb){$J=false;if(create_database($A,$Bb)){$S=[];$um=[];foreach(tables_list()as$Q=>$U){if($U=='VIEW')$um[]=$Q;else$S[]=$Q;}$J=(!$S&&!$um)||move_tables($S,$um,$A);drop_databases($J?[DB]:[]);}return$J;}function
auto_increment(){$Pa=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$s){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$s["columns"],true)){$Pa="";break;}if($s["type"]=="PRIMARY")$Pa=" UNIQUE";}}return" AUTO_INCREMENT$Pa";}function
alter_table($Q,$A,$l,$be,$Kb,$kd,$Bb,$Oa,$oi){$Ga=[];foreach($l
as$k){if($k[1]){$i=$k[1][3];if(str_contains($i," GENERATED")){$k[1][3]=Connection::get()->isMariaDB()?"":$k[1][2];$k[1][2]=$i;}$Ga[]=($Q!=""?($k[0]!=""?"CHANGE ".idf_escape($k[0]):"ADD"):" ")." ".implode($k[1]).($Q!=""?$k[2]:"");}else$Ga[]="DROP ".idf_escape($k[0]);}$Ga=array_merge($Ga,$be);$P=($Kb!==null?" COMMENT=".q($Kb):"").($kd?" ENGINE=".q($kd):"").($Bb?" COLLATE ".q($Bb):"").($Oa!=""?" AUTO_INCREMENT=$Oa":"");if($oi){$pi=[];if($oi["partition_by"]=='RANGE'||$oi["partition_by"]=='LIST'){foreach($oi["partition_names"]as$u=>$X){$Y=$oi["partition_values"][$u];$pi[]="\n  PARTITION ".idf_escape($X)." VALUES ".($oi["partition_by"]=='RANGE'?"LESS THAN":"IN").($Y!=""?" ($Y)":" MAXVALUE");}}$P
.="\nPARTITION BY {$oi["partition_by"]}({$oi["partition"]})";if($pi)$P
.=" (".implode(",",$pi)."\n)";elseif($oi["partitions"])$P
.=" PARTITIONS ".(int)$oi["partitions"];}elseif($oi===null)$P
.="\nREMOVE PARTITIONING";if($Q=="")return(bool)queries("CREATE TABLE ".table($A)." (\n".implode(",\n",$Ga)."\n)$P");if($Q!=$A)$Ga[]="RENAME TO ".table($A);if($P)$Ga[]=ltrim($P);return!$Ga||queries("ALTER TABLE ".table($Q)."\n".implode(",\n",$Ga));}function
alter_indexes($Q,$Ga){$nb=[];foreach($Ga
as$u=>$X)$nb[]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return(bool)queries("ALTER TABLE ".table($Q).implode(",",$nb));}function
truncate_tables($S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views($um){return(bool)queries("DROP VIEW ".implode(", ",array_map('AdminNeo\table',$um)));}function
drop_tables($S){return(bool)queries("DROP TABLE ".implode(", ",array_map('AdminNeo\table',$S)));}function
move_tables($S,$um,$ll){$qj=[];foreach($S
as$Q)$qj[]=table($Q)." TO ".idf_escape($ll).".".table($Q);if(!$qj||queries("RENAME TABLE ".implode(", ",$qj))){$xc=[];foreach($um
as$Q)$xc[table($Q)]=view($Q);Connection::get()->selectDatabase($ll);$h=idf_escape(DB);foreach($xc
as$A=>$sm){if(!queries("CREATE VIEW $A AS ".str_replace(" $h."," ",$sm["select"]))||!queries("DROP VIEW $h.$A"))return
false;}return
true;}return
false;}function
copy_tables($S,$um,$ll){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($S
as$Q){$A=($ll==DB?table("copy_$Q"):idf_escape($ll).".".table($Q));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $A"))||!queries("CREATE TABLE $A LIKE ".table($Q))||!queries("INSERT INTO $A SELECT * FROM ".table($Q)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$K){$Il=$K["Trigger"];if(!queries("CREATE TRIGGER ".($ll==DB?idf_escape("copy_$Il"):idf_escape($ll).".".idf_escape($Il))." $K[Timing] $K[Event] ON $A FOR EACH ROW\n$K[Statement];"))return
false;}}foreach($um
as$Q){$A=($ll==DB?table("copy_$Q"):idf_escape($ll).".".table($Q));$sm=view($Q);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $A"))||!queries("CREATE VIEW $A AS $sm[select]"))return
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
routine_id($A,$K){return
idf_escape($A);}function
last_id($I){return
Connection::get()->getValue("SELECT LAST_INSERT_ID()");}function
explain(Connection$e,$H){return$e->query("EXPLAIN ".(Connection::get()->isMinVersion("5.7")?"":"PARTITIONS ").$H);}function
found_rows(array$R,array$Z){return$R["Engine"]=="InnoDB"&&!$Z?(int)$R["Rows"]:null;}function
format_sql($H){$og='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$Jf='FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|(NATURAL\s+)?((LEFT|RIGHT)\s+)?((INNER|OUTER|CROSS)\s+)?JOIN';$H=preg_replace("~($og)\\s+(AS\\s+SELECT)~isU","$1 AS\nSELECT",$H);$H=preg_replace("~($og)\\s+($Jf)~isU","$1\n$2",$H);$H=preg_replace("~($og),~isU","$1,\n  ",$H);return$H;}function
create_sql($Q,$Oa,$Hk){$H=Connection::get()->getValue("SHOW CREATE TABLE ".table($Q),1);if(!$Oa)$H=preg_replace('~ AUTO_INCREMENT=\d+~','',$H);return!str_contains($H,"\n")?format_sql($H):$H;}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
create_database_sql($nc,$Hk=""){$A=idf_escape($nc);$Ib="";if(str_contains($Hk,"CREATE")&&($bc=Connection::get()->getValue("SHOW CREATE DATABASE $A",1))){set_utf8mb4($bc);if($Hk=="DROP+CREATE")$Ib="DROP DATABASE IF EXISTS $A;\n";$Ib
.="$bc;\n";}return$Ib;}function
use_sql($nc,$Hk=""){return"USE ".idf_escape($nc).";\n";}function
trigger_sql($Q){$xk="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")),null,"-- ")as$K)$xk
.="\nCREATE TRIGGER ".idf_escape($K["Trigger"])." $K[Timing] $K[Event] ON ".table($K["Table"])." FOR EACH ROW\n$K[Statement];;\n";return$xk;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$k){if(preg_match("~binary~",$k["type"]))return"HEX(".idf_escape($k["field"]).")";if($k["type"]=="bit")return"BIN(".idf_escape($k["field"])." + 0)";if($k["type"]=="vector")return(Connection::get()->isMariaDB()?"VEC_ToText":"VECTOR_TO_STRING")."(".idf_escape($k["field"]).")";if(preg_match("~geometry|point|linestring|polygon~",$k["type"]))return(Connection::get()->isMinVersion("8")?"ST_":"")."AsWKT(".idf_escape($k["field"]).")";return
null;}function
unconvert_field(array$k,$J){if(preg_match("~binary~",$k["type"]))$J="UNHEX($J)";if($k["type"]=="bit")$J="CONVERT(b$J, UNSIGNED)";if($k["type"]=="vector")$J=(Connection::get()->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."($J)";if(preg_match("~geometry|point|linestring|polygon~",$k["type"])){$Ki=(Connection::get()->isMinVersion("8")?"ST_":"");$J=$Ki."GeomFromText($J, $Ki"."SRID($k[field]))";}return$J;}function
support($Id){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.8.1":"8")?'|descidx':'').(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.2.1":"8.0.16")?'|check':'').(!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8")?'|fast_status':'').')$~',$Id);}function
kill_process($X){return
queries("KILL ".number($X));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return(int)Connection::get()->getValue("SELECT @@max_connections");}}$Ci="adminneo-plugins";if(is_dir($Ci)){foreach(glob("$Ci/*.php")as$n)include_once$n;}function
get_translations($Rf){switch($Rf){case'_template':$d='#JLxZ0v-$$rcN9!CM15G(@;e~yG[/.6aJs*9%rk(2fou6QCs~5wwLkN?k;4y1ZaI&kLt1P!nCa~=EwnP!l{2Ed`Yooy9YQ,k6naQFMRBeh,L[HKtVyDHEsR=;sTXwofo$,{tWxbKdP
x"XUl>b)IuLoiUpkqk6WJ15/xsJw_R9?RXCBSZJ;LHAY)r$iSY&{(|/m5Fo,8,)W+R:+"@@Bek6#@5:<yvfN$LVlt_SZv1-g0Dd*0`(l2yNH0M2U(^%?!b=|Y7Rs;)#UdQ/8Qxa6ux*mJYx#iKd)2R(d"I"$2R.&#+"0C%E-TOSP-&-S3X#!2X.6I>#Y2cb-"6:Q6.:J4%du89,`p^Sm-i9jY}3:,"YW0o"t4}AgeA?Bk|C@]s+)"V8!9G4`#)N|2~+k<@jA@D5aAOnA#?HWZ*6$#;kW/lZy<;(aJg^`,Z,|@Hp,*/-_0ywtPj_
Zdp!
MD9V0n%j/<^A5,KMJh<vD@h%+vjC1=t9I+i-k8iRy?{BP8C[?,hsaHFc&ECeFU,)<Wr36M6$4/fC^e]*Ze,
_m^p#$kPt?4;c#g+#wi
xjR;p>4ceuyAt1Fpt;8e_@w-wugV"H^azj/7xs06v%=;?a~<J]G#drf65.VDZDZ#U&;DRNg:JmE9/6vFzu`5RpgfKS4->YZ(%VW?EXT<:t~W/?j)5MI8O-g7K`,>_OoQz0`)16-*bRFkg[r?!^,DoK`E}Ul1<cK8O1c)a6BlN.#EoewaLIp_wK&5lVMtGqh"|B@RSmoGAAXNi9tkxD>c4v`4p7k
wS/To:%rv>r
7X.E%I/U8&&]LK98p*`*JlF-65uR/w3^zL>qe=oQ9?u.Zd|m
k[nSwxeI+o0CPH#nbnq5IZ@z`TaBqD^K_>0GJ,u
-JV9pO^5_F0~V7<$
Css=,4w1]q6!z=r*D=x8~`8v*kPO7?_FSrggjhO00ogIegP6YDeTvI=gQbmaj*m)H/Q_kAX]We2"xH&Or3.CZE36!H#8iQXRzh(a)IlKgZa>a]+!}NkW)UQ_T;<[d_lm
CztVbXjniPSp6v7Xr9n42Mq"pv6nM];2i"qVvQ[JD44e0GE!_,Zds5nf1Q3RyO0iBJ.#l*e!SbWOt?fdGJi<pWUsPEO7"o%GZ&Y}&iSm(+-s/W:J?iPcEq1ohU`0@bA"V~Y}4%NjQ,A3UWO<C$tX';break;case'ar':$d='"c0ATaLp=@90m8$$qoBAPJcB5<T"<KN"98`@M5ri|dYG%em9)e9Aa2#&-
S:l!]*x8N$(lRhU.a[YSfyPuH^@x[h}Vxb-
=!RL43qvW_gGJuJm"5*G&`pGff(4
aF(20l^yq%5F,qJAubT7A:j.<.HkmDKL*5KP.)sp;u3k7p);hG*
P
R.186n5zV8=rog/qK>7BmU4Mpe(xf~]z3B4{C(Zl;LPV$.=3Lz31A<^$6fA,V&o?>[TG0%<w_FE4Y8;1,HjP?5,BW}gNtE9?.*L.YC(s9ojUY?a+$
@#M@x_tOa<qmpK=fMt=nMv!fTcWDYS(wS^&u1SJmo}y-Jwr%F-neyu7N`)pDx#mz?^q-x#
6=&hkIY[jyCys6m!"BhvVyenA=>h$F)m~G.Fyc4D$EYH?w<o@v,h]2Z[nL"Ys%a2u^-Vc*ZE0U!DkIk2>IU&:_2_Ja@i7D;h?6}74D%V}J2oiaI!hOv:5Y(#DKRc~%|bn0K&e8N=1-W#JffG6*YBd+/&yy+E[)%"_&dt.c;<CwiQfR&K>pe(pBa)Wo3G2F3Ao3byXa_J](8LbVn0$!AA+5]U,Hu+i;FZQS8J)!YxB
;k=TTaA_U80:T$Ii<(RT[36rt=kmF=H,(m."Fe#"pNU
~5n-4<ED=6#U0ENorvkDh7LW7TP37+E8`22m%sf9MxsqgP5Y+!l@YN~]fg?E?DFa.Q/>#<*;at9x]c>x`$
bXf:VA7$13B3,W(is;X{)YwixH.!N1b(QsHv)PEkFQlBd
W@JbJ~w4^P?N;R%?Pdaq=u>VQm1el>vn^Na:eCCzJoh7]d*VVJ1yw[6pD17!>_-3CYDf#V`9>.Zz+5E2&NhIwAo]Uv_!e/VDH^A5NZwXZ]3{+Sbh9qHgC6Wr^_0iX(5tRLYl8q4E
j+9fvD,rd"0xd_~pN!:0HNy:|fV0j6J_i).+0NebAbI+i!e3QoL6#*uK*d,!d9NY:Q_ki,svWOSRSX-PY`~k~#88AR#=HDk(5mm&3sz&r(Y%:5`k@NJ9,<VdQJki2k+V)$UDT6Kqi&@#Sr+-jXgeAMlYW*L?;N:X<lkuO$b$vR8R&Ab3"B[79@|k,OqWKa[YE.>R-#;i-7D]wH"XL?H:5r|PjOG1u=?6h[Euja[d_!04dbP3iXrrfx8`nxd]c?,[TS~Jt_b6!tg8$@GMz=;W;XwaZ`TWvH}qj3n?~CVY`J(I<#Ls;^A^`%N9=rq@&[*-$Q9`QU}h0hSQaoFbz.IXvy<U-xe<|ir/ZjkU0Ml[Lxe2=Fzn$3=Pyb.99?LpQJbOvJ}0nnI&?*t
1ND6CaH;!)_-Ck%gJC;`iL2fX
n_4*3Zu!;cR!nZf"^^|T/FQgS#c?]+|;KO71bxnZjnoGMgnlr&JuhmoHT@)eJ$&*[PEU&V:G<u`@Ck9dZ
(HohH#Rn#Xi9-9zF
)TPl!fy+`.hx-N9B]J6jsF@3rw
1%w*r7U>!Sd:;%*>ap07(5x"mH>JCd/n[SL5k/`mCMwuz(c]z]tLNsm%S)E$o4_w;YgbE@h${sc_R%yDW4M*WW<vFfme?[`lZkogw_.r8V5XH:vRXcUs*.wnT2byX+<l`?qA">d.Qsc3%,.tLR0,-cIi?r2+jv^S&Z_hpnG>3f$k-vP3Z>[S",37ba[^xt)(m/P7S!3yfL2KxdPol%u>?oC4xTD=X5&bkywZLZg[JrNk&:j*ek!94*;gf$sJk&/xeaB:qOS<xZNlTcg3xX}t.S8!OAT@I!-w
,L]J$9j;DQ9B=qi~L_`7((dIPpo{:oSQ&)6`+u4-N?yV*.v$1uW)/f?9(AgU`CybY^Q-`cWK_1I)vF
a)[W.u9EM4(2#tJtnN:f]/x&#jUMQg:I4*jtE
r6B"c02o[Y0>L@z7bDnLv;v,A5T0E$!y%&M9l)$P<4EgWJ7J!H9@QrER@Vj+l=Yy-<,.~X@)91%NL-_8X6WB:X-TxDS(D0bjR)
`xA@F-ur.88n%Tq{]H?f62G3+CM*Fv>*VQ%R<c?9RJyHGw+|.~je^5uo$ZhGQr2|2%Nf8t8IHtb4
oR&6>l`c|QPfRtC[!t/S@?X?P]-T5fy=o3#R.osW_$CE>T`HV6m7}a4DqF6G=_uW.-=ytS9BF_6<Yw9Ef@PGGVp/w.;j$3Sa&Jb+y&NBEMp/TL|5.+qoeU-NXZ1w))+qX8-8qUZI}U+8_kw>B#hW2t.j6+,9#&7bOK}T4?8-}Y-yfq3.JN49fPtvKr+Ec7nrBqf`ffi%om?V--h(f+@!.ZNf@Knu;Ovg>)f--a,G#r%v/Jg-zq!aYgJ]T"AkrmcHY9BJ7DdrrTv8j_NP1O/+8Tbuh%w1s,SUqJymt]
s1`K1Ql-QrH}-a[l_d*rr"&RZV!}f?g;sWfd68$^0em;"H=y)1yRd9k?J9S:xDyxStloB[rojgau!l+|h[UenztkHs9tvS`{2*[jW=7,-^arEqmIa.ulS{j$@p@NlxnCN%MO6SP9t:`}S$)&q"j[Fk&Qo>$7l%^LuC+!"_x.7(9>!h=zFTu]&`ErMi0<)%q4&u-me7R=cD.YZYc"g~Fv0R?Tr`4gCLkLpL
~*siMq(Uod9ew=[ghp7ikL+)t[)lCj/
MctKAt_!:/?iH81Si#[_7J<;f0iHEx8DI0K^p/c!|1$1>a&nnqg_01_gWXofT?}]31)dl8]4,h(h%y16RO(j*1h^`pIZQ8WEm]e,+7Rif@YrY)etMSI]PQDpIai+)fGFo2D,SRS
v(xOI`Y_
3g,^BJ*y=i%"YUYM6m8dMQH^W+sWc>:TB-`z@DVx1uth]/+y!}27UIakB3ItJbQRd%om5iL<I,bv!X#^^!^$_O)dyOhJRX-jI^ll`wp?t}wN5y)"vDoYA.).89
H9j3;LS0L+cx2
.ib2<3L.4a*TH)nQCIel;!{Z;uIBLq%>+ksz%NBh~DBnnEmD%A4*WKg9d](pv/]5
GbD|,oI-l%g,8]d2l~q`,=0j>=2H;>PDmG09+iJ+!{FjL+nbe<q_SEbw^qH[3`N,%X2z6X?]@qvP5bAp&X=nh_
7pGO*fQB^H
;V?sr-/"m1U&/#@QV4jYFYW!ls*-`/s^Y>1[G"dFQJI>Ie)b!@sEa0b]BUIU!@([E2b$$W!r*g1lrC!$Od*g
{hGVH"*]wA?%12R@dUTHH/e1pqP>zI)6Rc<]Sfi`d^A].R)n=CRi*k+3KV(7-SHG.wT.VR6XXXo5B1j9$x3:uGCm(oQv*HF)CjpH/>t?MrP:0bZdkKKoMI6ayR=.SW()549*b>e
I4odpL):F7kaKu5Cm6ag{5KP)@35Hg4p7O~>!-SB`*M/weH:D#6@hDb`no8i2ZgQ5m!%!ICj@b~Lm`AUR=J0foI=iVTw:U}RQdV@h"/M^`!x&M!1
W6x.U`0}k48d?o2<j0]@TXXcK>/r"s:;L*VQbzA@mARD=2^HTdI]`Q^^H4I@o3wziN^Ts{II@5/>3>s8MPX26hYCGlu&86EWZ^qHS/klxo0pD.m@JvbnYsb5T*bF3Y[+g>:-tr&c
}.,fn?NqA
urzN];*Kf`eO6q)IaLF7D92M_^zZmUDRZW7f!viqP;G=^P5R}A*aYEHfijo%,EgrdXA0BAf9|H$vSJ3V)K8,w[Y1zPIXPe"9s7j!lFB0|@oMq2,`1+;[%TSAiKf]z4wF}ALMFP?WHB+(x*|YT_bNJ[xllUX*u_>`nJ,`,`

4H=cNJ8;h6_D>4u*4<J?/JI(QW}(8fUB+lhnsZ@9|g5gzTlijSC
kp%NP@;sm>|g8i*Fg(LLUq<rg))fQa*)7Y,5i#w0De]4)*gWw1(P>oA$iGYGF>G`7.js->7LQ`>sjB&bQ+)A$Rm6CRP)1^pIkn)<Q`6mXvc<*XtYT(^4&:[bnPjux/<qYJzs@;:G~G;o5!z/d`,Wo[Y3(FN?VjQb%Y*WsOAXdpbc2B-T*;Gbfq,F,iYp:`Fwk[.Qyg3Rs

q<V.g(=)aB34q
SRGaXrwHlTk81h;L%HC3U+]dn2l&7K?xB:q?b73g<B`VnO
4/wfE[u))#H<EXGFsx6?/kHZCAkOG.H:s9=D!]wdCXe1YIL%fdAu3@PBl8GvOr5,NC
Jlm%OU[AFA%WBup|,+ayWmcBez.|+99dqlx^?nn`j}4nQf9Dm)@3P4>d
^-PJoAVgwe3%pd,7=u#E]VPx(B=XseRX2BOAy2i*28)QyC0G3A=K-_?=Ey1E^Xkh0.[6sQ}yF,V6koBY4vZ_hwVPcWm=e<ck]w2GEQZ2>>"71%,g8Jn]PBr)#>.KB"}Xv_PouLOd;XY+?7fxU!PZz>3pq9%WgJGIETsk;uih#I9%DIhY[((p09{YnQa85l~DcK8MGG+w)-j4-Z0]>yW*NX?UQxL#hYITCkxI$a_VdLZ$zmBLLo8Qkr![,,=[+6;"4)q&KO9mMZoi+v8hN;fI<r;B+>;Z-o])UP/!81}`sS4%UU*P$1nuww&qnusH1_1q=
RCJ
$IM.i:G8xNzGBtOUdQ=yZw&FD8>sovniKUQL_rXbYq"R~G4)wgmvzm7pE=DBrj9Tr,>US^_sC4D"q`Idl/8@@d9nuDZSoC9I|LT[nSkqil;:iwB';break;case'bg':$d='*evF;bpD9,|?Yd<!dO[i0rE6TCNS&O_/Y-D"u!m]Fo:-j"_QT93Ty&68)e^<i=kdg"eH|^u^yc4guQkImavHQPg!KBY@6[dJ8VzZs-k8ZAD=A^+kGb%h0o(nSB9JHX/;D@}
ot-j8]#4-r/8^Zyw[]:]2c0KcS{],4C9<>(W<s.F}9v@UkOx9%T[REzr@Fu4Cx^[v<9tw`GIEP~(9OLrhSJ/1tu%vlqezgb,daYh17U/ki@11Z~mVBClvZR5?aVNk4<4?j&?1TG
_)grr$(U.?Lov&pxUaPwat0Od?O7h^=b1w(vs]f`V?@mBd6_{fh_Dxf<UJ`<96euQ1*ymQF.<vX(+^MX/GlumM@M9v|x[Flc4mzt"n=H0ctiX?xc{^#p?qmxBcc6T5*tS_msPl6`uMfKkc.tLc.@sBFm*t*8>y^m][u$uM.jqfmL$&:`PPM*gnOY$n-4=YBs"qTuxZ0sv3"F:eYvJ/o&ms._P?c$5]99P.hua>I
)T.F.DyO&Su_,*s/_TNrCPEwdJPkV?[%$O"w9?m#$v3CC2WDQ.(d$84v0J1X5BP.D0[$m,>I-9iyNvPo.1Gro;WJ]QzprMfuJ_vGdUREub+Bvt
`vnX6==D/l!"2#.A>}?A.ecA<7;s8b]#pGJ<MzQ.RGT0Qz%wv&d}V5V(pXmFoQbH@%B*LDJ{7Zme
CD{C|QFAk9h=;]WY"Z(E0G?Wdr)$.1K6FIdF&Ww#7iECsEd32NXgd;)GYc/CSX$VY-S/>fD
T@iwrLIyD+O5|!q.r4uF;_>W5n{&"brun1hf_0Q?7Q2_LD=dLp<_
)A!/hh2b!b
F`{LI>$"XeqZ=BT
!0%H1n=bPnu,_@I]0y>IcyD2[+!t5nEL
JPvo.tBXC["Hi},FH=AW+
`h>bX,qOqD(bxD]dy=fTFTkTf<P=k&D8q><b;fM6mBQG[PNVZutE/eI,/ff<p7/ycZa{j4ZrJM#6w3Eo*L%RqvoiFOVM3FW&(c=mgn(Hmwxv6E1KlTvI/X?R*),&[Pa-!r/!c|WLn~hu.?q2lJwkA*Krl;w=+XN80YwqFiR?]pYU.,/b&h5rs[F)W2*#u=*ljB9P;
SIm-xLnP(e!FYrh5O(.UNo$
hu[^4{^T+s)VEaSff|Uv+AL{;mDK)l4:r`o|_xfA=)8E?/cX[6MhN1f/0=aOa><E^F>&$>c&_LpEt=./k&:PY&Ar4h9(.+q}irK
%j;hcAl(m@6>BM6pYp(9T/($l:q_R#=AN_JP91=lnx"MSb*=sG[x;9KpONaNKfLrMYP8*A+R#><}&yR!ZJMU%j(?A%9q4"f80>-+pqW}%!Mx;+M#9{Hz#S%h1d5[vxZ,wxe)q|!jNrP7<eqB$zU`^~vh&~J>u6El/FH(JU<fJ%!d24-iM7fs.iIAYD^.ltO9({+|PlJFZnAfrB:CJtQ_
HF3!rR(c>Qg3m`HED4Ykg3-RC:d&#_`iO*w/+&KGm?-w5U~Qy-U49dli|R7.XfT4-bK
rGQZ+/GQ"JtTC>xl}=F7+e5s7.#]qu5qWT+g,A8a[Sy<pwy(g;6fQ6G6F;K^<tvqUfg"RAClL
p6FufZ`9[V*u8EN
%hd:LKY
2dkR~5h`uV]Y)8/V7Kr@|`4?hrQ7_S!l4A7]Oc]*=<{8i&_f64.fQ:-oxWrZ#q1YK#z;F6:di3jm%="Ee-
[I@o$3OjZhtcV}8ra~x7H{NH_bhZQGg$5bAw*9+eAYrs-Z(B4m9;9~Ik,A)n[vjWlK*z?i.$P"O
^BvGt(T@f+JAH<[Zp6p]IE?~Cue^?Iy7!-!CN`,@ejr1IV:?&`^&6#7ik$#wycR:fW(tTAIjW4K|I%buiA?v/QjWl~C?[<8~y{3EumIh,[gasG9lwc6,;imb!u3^9|@as6q>_5+6:R
4>>leKjRUr1q}J?A4QH:M>3
Y:O-ACNYW=mB*)3wfZbmYY=>1T!Bx*As|P@=(<]Z%W?."WDNVyyZjS:iPDMv~Kwv3YRag-zIj7_FWH:$Al{)10glvfDNj^I0xrYf3LoS5K779W:0CKJ=>@oJHrFBk)043+F`mNTd.tXO7BSebF5tlI|N!V.
.>d?bOVA?v]h%e[;TakqCsf/?ZYDn1c-b=xr|LQqj(EJ-43^w&pqQj4`p72].oNlkz"l+RI<)%A.k9Ct}f}ZR*u4LXGFMoFd4
44y>AF$-[/)COLiF:V}
a>K-!e3-,0je9*36B2-!6CfJ28zxAQN_Fv/A4Rf]8Q<5;<AGvL3&_(KA?#J;*G=F6."_#NQ9E$^1kF`8
.,8~@LWXP;nIOMZhF9#]:p(~XB=N>{Ezx,Qzqwy/o[S6SygRf1`<l0(5K$/gG[92QxqB`e0n>3,x8tVH1a8Y]E"LAMI*/Msb($O%_(Zwlt;0%U.bHnPk,4X~(3i!>g8mNOKP3uD%b?M_1g6e,@.qN|.(N07D_cU=k<srpz_tp^`H7Lsy2moiWj3wDA&v!0o8I1@aOXCMRFO$,-IvLiB1!MTA02t=lqV)nz!c"HqdfDl&nR<HrI5EW"pr)>H|sI$v`@PkWSb2hd<k-L_wEFR~Z4i2&E5-%yZIqG9J*M3*Lqi-01Nk2daDFT[]@ME*9NKDlvszdUcNK!!-%"C,*ui@-
&u47U="jJ.I6bJJ{1[M99
f.7!ii*%Grvl1XPg"F:uCRLpov7OmMkvRjx,8,np1n5AXs!N
sUDC="OK`"w@#NFNAEE#}NuUYQADt,r?47,czdaj{!|_R;jp|[1UQ?OyCr`#[JLA#nY[ak1EL4,nfrpcDRsQ<.F-*9.WIVap7,5q61)
w`(vF9h.4#sp^9y+qD|YF>B_Uu@qGh8s~5"b{g^6
ki&R17A@=8rh@-%Hs.bJ%h>Kt*QS[u2"S`HckGOI.|iDu2gq!i5JKm$My2P+tJti6J*M^j--A*ASWGG)Ss@U=D_Rk`!dRJmGE[^&u+Y}w[NQ2j7V(zK5Lm.U6X=:#r8J1a$J=Hq,C#H~;-W"Wet_l
T@Cur2$<uld)p@E4A~o6sXTjAg.b:PYK?DM4gtBfZ7EZ`To5C.$)dAlGPZmH_fa$
DM3.q%J8-tfe*D^@Z`ZvLf/NK<87}S]c51$SF%~dIIsL;ZKQu?!$6d}NtSAVD5F7BoC=.OQ*n-3DzS(2[dbdAp;K<`Y&<"G9,+vquhNbCvfb(7c91)=d8!kAVK)*@MtLPH_N0/Z+co2$NXD?h=YZGo4U5"T(O!hbxnVWz(S>
jNx<!Ds#N2A?<1
X&gWcJ[+V:3a1qE8.WF)g*U$9y7N|Sm<f-Q27[mbFo%C-fON+x&-K;2
DAmLu$=hAZ(<ecb1ga"[;T@
<<iTH%sb81#2Z4)O*y0AD@$;GFG+PJ[@A92k:FjR0<
Ue)4x!paC|=)>M8q].djR]Tcdo?h=6AA3kJ^Su&>yH-@)aI#xzT.O/Io"JF[L]fww00e"ye;QAAf"nKA&*W]Wtb}=exzQcT[:}I!EQ0n9e7PWF10OFq2wfLr!upJ`&FQ:V"SX,dA;!C^;$Li`-#1DX<c=/KW99;?%&e}fqeVd,_|/ShvlU@Tx]F`q]_CjuU30>Jb",9RvcFVfob2`>!
:1C~3"njJ{FCi8]wi&B,*)LFYj+,)9riWw>h&y>*.JtP_i,L1u(4K92U(2!v9%b5m
%D),u^9~qQNxVt-6-Oo<r`3I244!,`Zv_GH"_b.(f|DRE>LK!`R/AsBh$#!sEVdo$XfH_1&*J-v=O%4zQ22er@t/d,Y12<>m`]w%R#yvm{[8.s.TRk6|R[%6TW-(:Eh1%f>=`hKuIj!xanHwB&IfVh_)LcBP?{f:0QP0ee:p&!1k
!0om`JN7&@3W{F=enM=/TdlAY!se=_JG-MUHS>_n(IgSA5wkNGwYn2"Cn:Zk7Xi1?G^NwL?FJE":xDqxF_*AY4_VO
hJ8dTQhPJEX_E#?tylFmKa1N<r7kVQwN3Fn[n,B<kDH0dyRd[n6n0>AJ]3C@8M]`r@0#Z>)(K)Htf4|gO0F@P]jK=gf^87}Fsd<piS~yI1d?W#n/F)VQbtKS~0uisJLhLLF!2TsP8uBu.VDF*iiyh*WGpb#Z>jBtlD|j%E
`$cA<F5`3`pcT5PKA>3*+hW|*(qb"Zbj)FA>6.Wr.OszKV"q=e>pP,0D[1&3/n:jsmn|rVSmUCx%WrZTB8UG]
xL^z>EW-qetjW;G7*&S7L#_8P#1!wjI,6=b{0o3j$G0z&Hmhso5HUU=-WJL7b},?)21%$[FE`i*KyBH`jV:cA8^@N&@duxkQ*=(teJ*xrntmP2Y1*X3`$k?:6YgJr^8e_Ck4f~/-y%@~"d&LZ9#HC]6;Rrj2cp/dH%a`[lSaEay/0GMCuF;.6:ZM8tY"5_rS6_sS6m%+MtGwVQoHdw[^Lt4
458]uhq{Q7d
kv5;5tc>QjE[9mh@@C[6)x,j6f7UDe6CeDQGId9rWUa+P,A^
+oovpf|g1w-X@Y/8;2Lw|ulm"A&^iD1sWxs+Jv|.96T?:n(D/G,P[
RSaT17mD@w?Cp`,M)n]Bq[37N55R2;HnPO
l"">8C,9KCPz?tK^4sK|S,#9C_XwJxuf
gV5xI%;`m8ZVPuw$gPwp+IGBiRa;5jt?~`LDMy@SdZJ?Kdge9x`Y|BOuo,gd__~a^rn,~5Cx"9]_YivuZ>:^N="kiU3[TGLtr_nXRVyR?3a9$sOtC[>^JXVL74q8ZPBhgVgw?Bma|`92>R1B6D)
=WZ6:Dip2i8:C%wn@
&#v!a<Sn1Fake1-SY;!]!8&jDLKG[
F3^h"aj1Pg)P}GlPt7UP`L
vWt,F3Vov|xJ*V7i-onU
BIp
nI!o3k
/U=d326Eo&5rYm?fn$uZ(rQZi:@RSU<J%gf^8D"xqia+e1uow4AyK{lz)EsdUA+.
Pty&7u?:]cFX6OOs0lr
?LA=Q%gJ-iSx*-*`@d-"noxFQy{a7f1@J8{DA;Or%k_h%/29DpZmmswZ:L#g>yASk4@h{^vSTxVtP$h';break;case'bn':$d='"hWR!bopK+;5<"d8t.}gPgx.G6FO)i7.s>mFK9V$5P>(}+h#~t`FS"K2U5HeI^a$<fv9*?"D
Z2R,!]_y9&Oxtln_l_bXHAr5
2hTMP9rLWEWsTV{_xlm0h5bkgrZq6
/hs?&Ej`_E[GkqmEeXBA.h`nyc
t1cn<<js+s^uhDn/ttMNyq:iu~ucV8Kgz"I;/k?ZeCL6?ck&gV]Ru^V].O8]y/H
*kVVfM$a$_o@kkm"2.pK$_Kt-fmb&$v]B9OD#%hKofY%^f"R/GSn
)Jc&.+yHr!G_Xo*@qZdygmlHe&~o?MtehvLr;$x10MVEl7N@wC$y-HCtCjh7tq.OD,`tbrsZk/XeFm|rHw5Ra$%m[3Tn}yv).rwG-w}KKmps2A>BY,GiJpC4pBNH1xas0tWeJBYc
a5ffwNtSK7lm]rtOuVnq@9o&7Df:!66vOt,-@<Dq?)=8:y?(hi&Yir#J1WS%(JKSD5j*K<5;:K_X<ihAquqg^<XvFn1,fCW_@:0QoUE5U#6`
(tE^wGAwW$u&4m7
BBbcuwCRR+=f|EZeGSS?rrUG~.jf?wdAlKw+N2.h]WFS(ZrO]Hd.2j{6,[t")wC%[y3gYZT,6H;4[sY2Z:SR>b/CiY@u1o6Nf/#8tcg!f#pKi*chlyz7M-{&zBFc"4pPrSTCNR_`E)+AVSiR2+m!j<`62,oo/lnc$`S-2Sx3Y]=tJ]U,G4]Ht#<%vkj$1qf"<;os`-i`w?RcI
`]oL5iWVR-_9s+._`m?C5O23-Z4f_de9>>5pL9GW07kfMBWU~NAtWiIk~yH+u_.L7*L
KF%iFfnI]lju{>csy?9bH,p=*9/x_<FAm9sAv9QoR6qH$860V`9D}*>lD;"b{#o?xnhiLo1sS3<l777S,Yb8AN2d:S8!7qppr.e4
SyA?
]5loV>fqcZr;aF,k`((v8R2W|
@Jt@4cN2WKR<86yNMCW?Yr*9a/zbUpwUo`M5zp>)C$1?c^$#iXK
SQf-)v@f#b:,2q9KSB|i26/Bp=~0hdtoh9cgM/x,}&B!g>TgM:(TfMBK]qg5Gk{RGZ[B*Mrn->}kUdsP38%_UBz&JYQ.jBH*3-ib11"uV/r<.)>W(QiOA",^@St3[F5a*_QOqgc.Qj]fLN+Cgv!+K$~Z0h+HJMt),;Du0#=x:*{a]rfa&BKG4[1
W29$qA$@-h,:QR:u+yP1l%S6APSX=p-#3V4&mX?tP&K24W-ZQmz^uX_e)RVEA7qshCi3c/A?T::k!,zDo.;*2Z6cWQvW6_FSpl="Hw#(}Gu3*5s-tKIo?h"K!Jl[&`>oOB5[WTtF>]p&I,PF}$GE&0x/BNLU.r]#r)bGt6TdwkGhMb{rcrT431~UBP|eLK?
`?{WiV~o99{>NVV)aH6G{#*FjituMlVvK<{P"
0qy;J5Ftp]
TkkbL.m4eJf!-=Y"E&,JA<x7Ao]mf;<;#O3CrD81p%[aBm
yVbFV,6$_bM.K+2fOQKF^-(_Pymy98=R#+4Jlof-UdXUy125tP{g#9WWnlCrk5y!2Tv!h6Wp2Rp3(RDm75w!`wF]?"gE8<k1yM
Ze`,:U)gZ1LSf[GZ80evO.<.>PB0CI4B2idL(9$C:QmDa>2%.(,Kmt<a_d2,UynKV_Zwq($S_7P|7A[{%n4WMaMe4a@_7y]t>x0P:TU)-hh`?cDG&`vtcCA15^:(fOb.F4^)`Crjd~DP2vob-GE<U6xCV<Qy^a;"GI1(J5mgL}b!<xn[QCTQ#-[srP"y72pv]{5GwqxReuAB6ao7=dU#dZHApuH0s6[WFxx]3T#HMCh?>a@B@]1k(Rhke"75QD.+u}2n>Sw`b-4W-i)<(Q<&R70,05bhO"#)IWCC>/z%"O@Wcqm#[.l{_W/;jows=zsI.U=pCv160P=h:OVEMc"J2w%lRL&gF1vm$LZD>MYaP:c8dY1Q/O4MIJGTRO6x+IE&*j[OW3:g,(NXjw]fS"4YHvxD.wPCdp3MS`YL`5rb=o-Fe]/,9JFl%[h@kDRZR[C^3{yA.H+<
<7]xx.8kJdpr7J;ig]8P~>mhGI"P%6vi08/^69GYnF))):<5;oD0%<[liRed9Ai`&S)+;u#5afG&0JWci5pYqT,8e0rWK:#gFDJ?PLu?i@"4FpeYT#;iXbGIN?


4r#hF!WJ+Yg5:I#],3&d0^PdY|<)x4Kys>EbpKbwp`cT<D$lxS*xD%Y4c!lJLw3_CJ+v8R?S8c>z^sh]/rihtDQNs7GT0<)/gOwB9*D)+/Il$F!y%c3me/321=F6;uq#XIu_4dZ^Rx"XMK$f%K=D)~tY&H^-w(<tp2$T>yW847Q&H=19!#${s*C<mtlaUFtWPl=,4,d7(%;d7k3%U$L(]2$v=y0Rwk0.TcX.eIsoDpCnDo=v*n!}?d@K4X
FZ?1SFg[BU6RQ4@Jv/KB_NR`9-#4k;`>.)Ho]ghJ[ITfWJp#>+C4|:ZE!B&2PWDM;[rkw>O_*
s0)dIslJ*O4Qt?{=5V<I/Y{uF.hGbQjJ-eW$NGW2v70SdHv7H:9ojr?6h#SKrY5_Y?0tY5K>qML"==[y]2R;WP3s%xd"!2ZOnFn,,auUrb7ZwB:]pRKKf?"#yp1a$(f!MUPBQc$/@!9fdm~OF-T
Eu*K5RSTS<fMe%L29#f0GfgM;w^Z!,GTlM0wP5"bhupZ`&8szhb5/-s:w1A)PWTs1Yp9w<bh?,S!meG)N?_m^@R[$PMs]8]o_1@W0DLSc_:l4$2`^jcA&k~hotV!X?Mrt,4D8x;F:E/:;M673Y7Te=zN#P3V25(xulxP
]s7]"jE=yQgvj]ZlX-@Jt;x&i1(+>aHY?nb#fvo=)MkP6"i:!q;etu_i"m6Quwi^l^$GEyaHhd^ASUU,H(vI,M<:*Y%PbZcl$~f;#S_!#8K9tTp~sp$=;t)uax(lyWf7$XjyoHKJuZC0x3o@)[Ial7-CCh$/,QKIXcy%TENvo4fBO3ZVRYsm&B$UBd6CQ@S@:FRCvW?X])f_#OtGqvLWUcm,T{3MNy><.0i=;y!maw/4[cwHWE
M?`1Yc7bX/BG;Q]d|lE856~ID@voZ1RB!z(nY&.pvnqDX]yVy2^3z<7Ga;f:(dUq
?Gfuwt/E?9"6Q@Bnq2
4atF+?GGvAr`f5VDx_1%XIwg.![QUu-F77i-w?X^]_"hP*"<MA=:TK&Z{(.Tx.o9(HyQ;@tD&JnygO,`ly7OO>ovvIkMLR[nAFJqq?!c
=[9,%3^^Qe!M53-4hKZCER
uV,=P5=LL2[&(s-QdJ-gC+
T+Pq?GQI4qIG:=[pT!Jm#fYsQNmJQ1PnUF3k5oN!TL#i33Qc5sdnVSKj[Z!Ne.*qIn><*"?TU&LvjI2>bkN{[?.r470Siy9&
?So>/Y</oj}jKQ;FM0z[M=F=qaSyG:[=4N:LIF:dr)`2,lK%e1h?dX$>+/<iyL{H}&c./JERf(r?Rvbt(N1m#!Z3~

7%1<`YD!&*&KGLO"hJ
1Ly0|bB?"IAD8DCJgAlXZ4Ns=h93/kXF*KN9H%G(#],=}rAOL;C5!"8.5b]`*daAIb*#2^rJ$ALA;*TPo)OEh/
59,QmBrYxY7/<RU.t)YhexS)M|&1x7/^evt~_Sa3cH;B2E0q[zal7DDFt]>XS)JcQxwP/-?trrNoZtACnGI9Zk,+N17dbg;<d.<i.g76TjY)Vb[PQ!2oG`_$3S%_)4d`8)AhU~>vcLU<6vdAH1t(2|
2r`%pnV%QobV)p7+g%!FRgqpiQPGdlGNj+aq~@yGf&|ugevh<dvP)$V5S_:r5jqJbvN";g|P1N::D6gk8@stbx>Bt8!`1m.7+P^f)DV[}rSo74,vddm/;.iseVW8|"[2/EX$g<-2rO2wA%I5Gxo;w*o!ud)-0")QqX*q]9-3*#[FeA2,0t1.OZpTK1`0oo)FmI*uB<6HE`T5Z3KRg[>IJ7-[5,JS(Fx<{H`]&EuUEPAw5YYqqr/9|edCs4i>Q[plyu-bLfc2[`(Tv/3_5lh!+
z8609Ta<f:UciO0c{TGgZ2tU0xUDA7n_c"M_NZ@][RHaHhl)01Ha%@6g.myK)]FLzEK>]X?M1ArU"ov.e>tFwGc%FcTcYi@%@C93.ismJ._/v4`LTXt;fpsj{?($kdjt{BUyE7rj(D58.iP-8t?J1st(8onZ0!/)7-lmnWA+asmhldT)eV[<b1j3rJ~-hH
Ddp|U*:|Bb8*[LFa<]5hn>4Uh0LO=sm}1"Uki+"miW8Nj__)>3?>?u_;"Lk.%0iE%2>$3_0WBVayORh&I*w,-]/qdiD)9@@(Jtdm?-;fO0=mHQ_cV|n,ZoB4LS=AO_p@;5A
Y0E
CP7"a2tJe)2+nVGBhJ<^^Ro~pr#nTH#Qc6)l-U%mg-Q3f0Zn0`_cK*.&NrhgSGMf_Srp%<.m_/gss=Ohk,GjVVw`Tt05NX`a9*<zpW4x=n8/
VlT6<wPIX+_N<yl?zZs0)qmY~3Vk>#EyHp";dWK?
1W`hR7i;n|O%)TZ#1^T#/WX1OTG{QnPYRMk);=Y,1
oXLpAPA:,c7YPL8TE=5@I"K7EaWiOr@8U<!Y=J]|WIt/6H[)0t4jJ+!F@p5%K|PBM|lRi/+3BLwxF|RTe7/iKOU7_`
7!s/n;zJu7>3eR!tvqlu3M}`[!W/;ivme=NHkBGn9NmmVtL=$nAvfFK,vaDR?rD*9jTq{.gSkRT/YaxIlqQ7_6x&zsI(+]DtcZJ4`WXXz0i=p
%Fab$7wW$c|"`$g:@rR@.d7VX3-u,K0b{v1lXue+P)EYa=TPSgP*^pRn@m}D7G<Rm9S)8JbU9Z_$
H;f{WNkIuN2Te*Kzp#widzi/bUKaHvvV<Qsh@TNJY82CB>U(,=RvcG)ip(jexL-mZ[5/aQy
.8%!L-[KOU-vHL6$Z{p0?Eu^?<iSE1_x)9vp6PU4ErPSTaZ5%M>VpxuDDa`EM6.*BDu~7<1ri)FiSdsZ%eP*`_t~7rqvMJIdCOf.Tg&4,$VnvzkA&1VME!m3KQStIgxWB3MT_;`d[MnI`&OZM1bJ"53IC~?,]EZbuzPV@NKUk{nWS>IEd>n*2:q:S6t6OnL8dR#S6y,{
SbO8")U.gHm=seJ0ZqtJJ#_UZ<k6v4WI~i_c62e<,*.go.:C=vn.x-VSMgtRDR6/K:(^=Fh^aN@QmR(HDWQpl;|q.^~.;;xtWts';break;case'bs':$d='-Zu@aaM.72M0LN.%A:>l*]no?3|Jg.x.T;,:,:qsSes@*rk@G`RSULFGCI6h7j+y)Tw&*=JZ!DTZkN.qwJ&hEU~;n(R:(L],Aj9`(sWm26Q(q]|;jrFA/Ja:hoe+>M(B=x0OWBaL_W}amZ2%,)o:whL0*Dw<=)oX))nyU^=__x)Psl%kJ^d9W]b]hJcI;4-9ww|m}9lRux^qs<f;rmJ_W73x|7,X-G.HCt=3#0eDr0VIe77ep71W0_gv"HzxV6MnEclMl8o1F-"bQBAa}MbS6_Q2h^QD;v]MqD|ekxa7P?6;LW,xAb%xcJ3X|*a(P+o3cDn(K$LZsO1Dn!Z-R11`3E"VQ5j3QsRY0$)D/u
SBdT)8+oLSbTKT%jH)@m$~_x_gD1HLdnM"TUgQ"wo#,wgtqu_s%1[aC>ge6]0/>j*MnI]NW>I56OVER~sMZ&D.ZY,T*.S
#tvz)$9sZ^E5yRK;esgf?Wy2hf]G%O`yZtj
sJmEas5cym>Z]c3v(bG{Ge@C5"&y9`LQ_
<;NXlGV}Yh?V28_^Izh[O#krOJN,Lrx$,j%ku0=/rq:8gpQWY/(B<cYXZg[GQD*B;T32L4+IeBt|fPJ=g.C^0hA[f"z#Ze?/AUEq>^*+TzfuacfBsrbQt$qk]pjdM;A^auO[B"fYw2oZJEtQDEDVm<<8FJcye$$6F<,qlp5eR~)&R_,MImF3^QIYkgy#9S278P%z^uRYJ4*FkCkPgm04RvMQ,VqT=1g
?RcjY^Y&2!Q:h<KUBHW`7gIjdLtAAa2bqSMO*QHH"05KqlTvcgb5^|[&5U9:M"Xw>`7+*c,P$L:^vw($lZ<bN|wMbLM<3mql/JDJUhd?ZMJvq.%22%P~1iI3;`T/XNA{t%QwG_ol#MuO.uv$&fG"v3$)flLI](ICFUD@%lY`et<4xM^o`(D<wqg/^G^_KqSI(UaBb7QLlgbdMHoR(Uv"W*Ft-V$^F@]m&g>i3o0AD{DJ6pE,r*[He5LV@|"2])aZWJwDK&(7vSB0,jBA!
HKpcI@lFH;Yt%%aV0|#@iE$<caNcPc@s()2/)mV@>="MD9:"ma1oYo131zE<.|UK04APAU&!-{"k`BC`ITs
U5F%7^T:.tb7qgG+D_5_32lv(+_l2a`ZN0(3vNw8Xllq-*"1wH`6%lN+.>1(tGt4F@xLI
aEl`y^,Z%q!n(xS
fFE{&:j?]oJgc#!ND53
hDd[b
%i9-WEM@t>`4S<?%I-xy7()"DsE{k?,(VfJ<<bV+!~:,="qaPUD
1&t3]044do[3(v=~^k1QJ~B]*t00T&hp6bIQj#qE:Y?d6"5oPaZc,uL>-H$w-&8}szqenuf?I{?5/[o~<5^=pYx~/E:9y37`9)2yS<?$[q?,l:5R[^OGJ9:%i~f2Q<F#LG2sF="^S=`*J66jWxiV8PApJ(GS3/]|eHX*E1q#T[2xV&#{I!c2Z>LA_
uT=oG:6Ug2^|0NLUiTuBF%9+80Nt]7a,Cji@WK&cdqBwgm"!!@+nx]+_eD!?@t9/U*u)AN!p@@euf{bpDi#)9IiCq)X4(GR!$~[6D9_0GWyRUiu%,}RLrr.F^!A*cS%5!
K<-Q$oe:)gKcIm.BVQBX;W+hmyKX<`oy(d@z8Fopy@JLU:ar;7!jI@8
-9#Jvi*gxh_k#9&(/H&Y:sR}G(5igW]9ye.to`$J?}mTu/D&y^!a^F;{i/W/]QdeV__"[a9gG,OuB,eRJGL:T=/<@dVn$4F3Op1{FQ1A(,W&m12mD?!el+rd^2vR_z2wX.?:bTcL>!+qQz@"`J/1%hGi!~>cS7,=7pYP)y>%P7@#I./$KKA4L22)#*;1+8]Hum>%_M!N$)dKCT1cXY>71b9dmXFL0n9/:.,Q9_q3r`N0<~J~w9E4%<m*2.-D*wOfR:.16xN*gK^"-x=8V]@&Je3=DfZs+aH[%g0mp&)p-`)ca]MH.g8(CpS`*+<@Bag=#e)WUD3Z=!"TP+Cv^.lxJ$po;&F<N;"
0(G)kxk#Cx9MPW?/S<8
;gSu[>e{XuoW>Awff+kVa.??$LH6/Gl?DJkVt[LDMG+Y%~K)^c`..24G2wx3AO5{YT-;
9;etGlh,{]6H{v]8FWDbs?>.}!n$EGW1zZ?bs#UToEZ;lhhv86(/IrrDGQ%4A!HAYa]>B(t2XRN!-IN"|Clb4;To3DkgQX2_ba?Lle%_!Foy]]o>
`de?NT>uu>bYQY6g9hKI%c8,Lv=vv0#TNGIK>!&]Ll;lR::v=v0~$O`O*8/IkV?c4M@IPU>=O8vz2LJNt"68j#!g>9QZ
[[xG75$uuMGM+@3tF=12I`DT~J5?@u4!B]~B"eY4Vm
223Hfcr/Vs=/v?0TW}s2S$`Nd>W8b-4zb?!aNNA`Qo-vZ_m/G(O,6o,|fOs!QH+/kETUe;D@_.KNA|X,d?dI9?Hf3U!f9{1AB+Rzem9J&/MEg@(Yo6?Fthx(P"Ji<yn4Y)x"WK1%-f%Z#iGeHE2?R4-J4R04C-9g?+3YA-Fp6^P%=JGT[iv6wM(MW[0NT,?J`H8f`ZqAqejN)HD}w.La_#0^Tx!lJAL8NYU`h>>u09H<Gr68-%=1T<=UaK]hg,4<eWPt6I!r3d%t-@i9LpW"4F1u^I]g60sw!IrIIkaD
Xl/l4S|e[SVWG:Lf-W{yhv:kV<:G":3KQIKWn=
XBNNg<!<YK@TH-hJ#~Zxd>6bX?N*(MaSq}4G:F&6u#m}Sij$$
`d$p!
EKy{qfMX17D$lWr;A)?2*Hai:MOkrK!GY>h8kVZXB6M5<_PH5mdJg&jjVT
u4s%w+p67
*MTSz2X^eh!3C`INDqUf"WHATW7L.l^N.RQIM;PKI__#sC@
nU?L<H_t.$6`!l~-M(K22ivPk"iYm6OB#Rxj#%tGB;;.Ss`kb18MmTZme:$^nk:*ZamhvQD(~rw#X]>.Y7`mt:PI9D,)5/ceAV;%v^VO.glJ&2>T=-,m),Cr%TUr-qopLms$po(%&BZLxSNR-v#/TnbfEU63F/V$n!o[<g>vnAPlJ.o1$uzi-.r"7Df(LUnA]H@m#UtE4BP(,AJaedBcJ@zXL,~N]]!.5+"ql3{Z4x6VrDKyE`DNGKh]3pfUlH.9UbBO`M7`hr$c5v8KVN.m[)9iW3w_~Q9n1R^l;6H]%%DaxnZ*RbOi;[.o+&eH(_d[Q=@Y5Vcrha6;Py@0,2nJHHu2s?&Iu;7]<LpnSsf3DA5;-1Q_SFp`+*z*$G
K{aGl6junjMblnW%?y/&VPbN9H3hd`]KGs$f6HMt3amqo$8eWyI4fQ_F@jT0`O1pE0=
]?l{[lbL.AJ^3_gVq.^,O5T/_`y)]bvpUp08bbc[-FLdl8NYF8u!H;O
JB0OmUlS;>/+=R]~Lv&[s$AH]thrxzV=c6k]dF`iI,R5Lx%<Y>Dw_3Y
ng
-M1]CxhmvvXv<x0=6g/S24C2.^Os2g6ZVb/2-F_ii`UYp>,9u2)>I(@Gl^|[RWIAwq(vU8V%)G"WB*d5YUIT<qxdNpW0W7_b_0WQgD;<+2$wAUj?Igk+irwcGdY%v5"Gw@`y1,~Ob1&HCfSXvjkUD_$i(e7C^:%Zz;*FVs2_whYZxS-s*LQd_U_Z9.cBqs{w$$^a[0q<"x
vfb6N<sJVFgZI!u_KmrS<IvC^f)yV%QR[%c@7,^;r_`uInP}TL(mVBULbfeCWT+p@yW:q-:Gh-hEI`M4K*Zex+wXPKCaIQ9%Ca7+@Z:6G<XW!niKl50;Yf>>qp&?_Gw
.jP+$Ee*<ErSs~FF_g^/&R+oH1$c%eF$2jQ&DI*Gnj0tbJy9pQ@SKLa|9n*(?g.}oo>inPqqK
kF9SMljcKQ9cVtvU,v/?JfH>,kJ2-jBVL=
,4}T5R$
+2V*"s;e77O*Z/[>BMuE<82:sf`VPR,,(1NDH#u8h
(D#$Glb7)ML#Io|/cj,.`NFe[XQa6bbRy66)r/QUFVIP=o:/aN|ZeF<<K/(.4gr4H!`;dXiH?@A4@v~)28n4>CG6xqYNi`"fgBjp+X*7zb#M3K2JIXEQzZp"l#unbA3vnd]fYapM/i>P5%gPAgtn*0On|6"[!bQ3K/
N@!dK:&.^|oh-9)?Fg@Kb[InB2IPW=qOcLQN_J:Ey|
OIo=,__XU5ULu0:N8sOPKj&mh2]gUM/*4-
?B)B[50t5dSv(G8-BQttluC#+sUm#zn8#2dBWh#]A[%o-qO^
AG~x_`lCi=0AS@y0gaoI88t$d7k<{v:XT^",W/J[K1cxX;}yQp$`OJuq
OAK{NJU9)>G[TK**W;2Y#MF-[,hY)rBYMc#a]r
)38:{4Yj|F7W@?HWPQZP=[mxu,Bu=IMs"5ECL8A8rQw+$8^r5.vuGNIayihpF/uWl=+Ukl"
zgBknTcyxPvIGtnkPg6BQXx/"*{iC1KY~ye8@M,p.;~FE%N_E9!bQm~RtAE8B&k?&5JSJaTb}v+z#iVd(';break;case'ca':$d='.]^ALaMD9,{0L80!Z-m/UhqQ`d_/{)_(hCMQw-SLz(g[D.7od_l5i0E:nxtM}3cIz!i!I-+I9XzfXA|@#_QI+3xb{+JJx^+B,s#?CFMXeN`KL&oE(+YKT@Q`e>%3?H5l3<:3U7dOHHpwWLk*5a9;;h)nKQA>,Mw/b8@jvHKpu8QcvQD`AV:_s.qmR0PKgdy%p,aO#,YLl-P4a3e@kw5@G*U9x02f$uj_
W9O`A%_Vk0KO]GaufZhaGa+N4"GDN|KEEcDWT~&MGNK6nq4TWY<+z)Jv)1
{IS6M,wSLeJMb`c$O<Ll_qgcP`]m5Wxt69TwV#tV6F2LmX:!JW|NZr@/$!%P0vx>gli`FRKVoTk3JqeG^;im83;IUDm
JK}B;7>Vvg;p/o:a*GJE?Z}mH>@gi.nKk7*d)$d>sTJonwba5MN7[Uc3~Z1Vt18"dS*aDREg>Lk%=$QR)TJkM"fDf]LT@!ILm

9m:NEt^e(NUosLrQ2:`NY;@]5jg`RtEwoD8~p
hl&OP7oO[0gX
fkA`-8:npLq2Ag<?5Xe`($z4n?y
fRg;ok.ah;it;5VGtZ3^@TR*3awTt&C$??v8-R5^l0)E*&5_0fC#YCN>mYo`r_F`@Ah2d]5$C[^7JU
GxRlIG=`B!hks
`iEhd<L6U&hA9{Aq]l-ij7/~sg")@29}Vi6BcTl!6#vO
smDVfxaFz-6#sFl+gS2?]j|Z21]6{
H:8N|ZAlWR;0/xz/%9L8K`8`@%M?Aa[jA0>VY&([{Z*U~ppn36OFPh%q"ujp}n*Q?RZd`ahk&b-C&NC!WeSj~Vy/T6VhnF6=%N(ps3:VZE
eR;b(-MxN$HgTDwWW=
`bKq+clVPW%w`:kbZvN7
]r;]MA!
F2vpL(_%F/eW`db>y?;v])
tW-aPIp7;
18g4?
i@Nqu&JEHDuM9,pY69!M$c5WXe]&.*Vv6"sv@Q5Yttt*`MaE".fp~LJK}K8<N1aJD5SSCa:SPKyW~G)8Toh
6M^(L7d-LB
K]l@n7MnJ,)H`UekUR-.%&rQ#Ug+u&_3Rmg{/&3-O
Xqqsg{J3NP)Y0p_d"SVC],v|M.#YJ[r@@Vo]TG>Lv0N6gOF-RVrD,H]<l$Q6r~Wj=!BcvsRNr{j9N]@ukX[Ir><1?DN`5z+:O}9x3Ri^R)e7*47Qt_V`ldOpPr_[&oE0:NW
2YWSla]X)Zt1#gOp,:c4ecvUy/!`)17xGs?,:N?b@A2^CCE.hpu>i,U?Vek+ngy~*e(u1u!]QbaHXx7o4|8o;WYih!4B%}=yZI3V+<@R0JVM3j+VAkfHI}qHWP.b[YcoO^@Qb(TvCoG_.{XPrcAf7
Lp*Q`]"/i=_lcwelO@X@4+AYz"W.#{cj2OfjD=*~W%8/_)j&r|>Ejp
W5hY?RR8.Q|&y?~w3cl[QuXXP#LBGrM7Mg_REDI4VYmms_L)k(dH%B7,`Sp<wQt1}-7;oG}UUKS]nu6o>TWmWce
YnKkzyp<%b2!F$^(gm%-c3"?3bhi4_5O-to*+m/?5K?Ecdsi.
<Vmp:f@$vLKpk?oUtQ+S=HiQd(MEjGxm[WQ)K?4;5O>n
Fp1BsZX~#nFc!cw6)
.|r<*EK$*i:qcO&f,IW~g0R3
{Ym?pB4WxxmaS)MmrN2wos18O5$&T[$=f%g
&%xZmW#>aeqpmL$"Z:{k#g>[y%*7[FVo9QG!`TSmmMP=F61vaZt-
#d=t-/br*udWg<>^ecJ6aSHsmvFJdbdFh!x^$u[&rM.FeHeu`AYov*Ma#;U*r(k<s<u1690vg:pK0G-5peH}2{"K[wuD=,5Z#$JmSytB_)v20iv+m[t}nHjmym7s_8W!w>iT-U@8bTdyK;V1dIps9q1=G2<p_6@%<uH,.M3A2`s]
dM5Dw=eT?b:YsL{A%%elk0Rw]=qQC49k%eRkoq=*_!lo-B{Yl1`m~FwTAo?4s4Zgul=oKu]Yl2dVr4TMX1vAe+E4%YI*>q_<YmF@B+(Bqli<*V~r`D1+"#,XudC
SZw0GA0*Q!IXt=OR$Q`lC)w#xs|N>yD8>TWs=*3#Po}C;*N]X8Fts?8OKunoE#{cTuHS:T%wRCaDWf4vpF>.";:.Uo"7~pqHsC
D*TKkhN?_kpg9]2Hb{C_(~76v{yNP:YB;:!=_TUQ8Lb-?!vI`G]^JZ@.O!el.?x7[ziE?Iw/jo/%=ij0,iN0%"w!>#vtiSnuda]k5p8#q4dsxuJLBL]z6bWNSQZVMxYoU%wA_>eDj84-)M>x@b8Fd8U{1,C0x@_=t/9FD~u@HV>H:<;}DJ3tX&@$M,9FVDG&EuRtH~D^6tJ9XSGF3y8p^HH5wlMHa_YxSy)rYAm92E`;
1Yu70uhxxx3HGfkZdb@f)v;:5E>KQU-U5"l-hYvI-EH"tAAJQ3U?dGqV]@i.;ryB=&tac@Cwc80K)8/uMUE57@j+
ykC|!e`$>354Y)sYg=
sHk^LpJ9}6#cQoH!S1Kc|?|j_JnvB*nRn_{0
ToAz3`DY`}HMu-_:fTBr!sW9y0d68IAT3*o2%H
7Uwg2#J]+cyj%b{P}FLEqnSPf]<Xan,&qr9KJ2w3f5V#dY-R$[EM`D5*Flfw]6`;:Rt_lbGS$+w?:,)x/+^!g,TfVA}4*$IBl8tQyHH
&Z9^Zd[c.X[;;/)@)2yx<=gn6V,.v[.TPhQ4RGK#X7JJ&@`?t<"J20;!,E16.TReprr>l_?F:PGgRA;:jOVrz,UT3L<>#Jign$}KE_(b;*aNwC5Xt3moD`;S|E4(=gvM9Xz^@1:Il7>>Nj^DA.;$z4tpI,PNn_@[M#!Mo@:VlOp6an.(L
,Tt1-+<ZJkUYJ;cs)L+-)m].s@_
z?aOejis<@f^[B05;E03L((OH[[u~iNB$/V(kZR;8m@@-5fW78Pp:SbxMpY$h(g0fy8+3tUi!JUAPmmKC^FIW_7td,DrkES>HN!06Cu&i2s=W4>[A@_#ix,PvDZ(Kx,<@<&AFG4S=I^^;m&=as^`m`23X`DHkttvWQoQ;H>W*$eYVa~]quen>r^d_y"2+M@<{`ZHh2;!{LreKcp2yRTJ<9[-r=9&uFiOf(GGH>,7rn"guB/2}KFA"lH6A[h;.AF1a@Y2ZKQ_-nA!rn+;#OWd)HJ[Q3)V*GOEVes;Ghlj$,ho6k@l/sW_X2o7la4(9)J;uOa.F(>1uUlG]#NIOJ)[:.qZW-{`,@ybsUVF
:%yA6#YG-Mm"qoUNK)($5G.a3k<`gB&$"A5gkds;>0t"GFIgsjTdj/]>)BxeE>h4jXc9rpSWU`v/nYA<O)nKV~SsE(fF0?TeKaC0[JgqV|#gs~_r1*5ohuB1Pp;zH$+gmRe4Z`:?wE5vmXtDJvC&xR>aa
pW]n:`KwA=U=4tdGG;[VDlu`QjbuM0b
d$&^.
ds.Pv-IE)?VV#4vc7MX=j573ii9<E-UGZ8GNLKZMyhm-n
DkCb
g_%kI4:N@#ZOLIj*fK[)H*iVqYd3TBqO]OKC[FJuYR|B<V{p!Qul]T11&UtWPY~n[M;^:;s1F@u5L#8sUE??I.Zt6jz1QcF4ui.rq6Qt~L@:JAP2x.B!f>>KG`ep
Iofp7mX(SggmaDggBTNWF52M?s6U+.4Z!tjygc8;#)9jHg,oGH0]%(tBZjG,_;7;vjS<l4SK=B(Bc{H8(xCTW]!~W,(GaZ*G<wXeoD,;47oj!@)#85qz
Yjl1dWBjxm`whr~F"jm.rHzm.*|#d$z#~9k1Yi*d$kJGIH5tu]2.sc~7Kc]AuK+QsS<D(58tEE>w/wz</)[ewE2Q>FN2e-Bu@v%y=S&tlM^?k4/yXr1V=G293ccy#JSl*w07/J4wi,P#O$:EbP&k%@zv
]ijak=EB&^B$83Jo`H78d^UZ0zel?k;^5f:<->3.mUGFHg
Xy?i,i!Ae@jis.$XWBM+@7H-[F~)iwa-`jX)R($5g"MxN4BIxlLm_&l%C(x:J^x=gdOiCd4=7XLi+Abf[7:6+(x80]CYcR%jZfwee4Py]%<:"rz*nn86K<sV--3&%#6?MV<2$AMKtMrbwk4A^ZBk668<ZR(toJ_sj$t,LxaBM/gm~u+,v`NrOB@[<H7`GrffS?G@k1*qG*.*J5IhdLU%"2UX*K|Pr#~L)=6@_oNj<b_in0ct!//WA"
*&mInC0l7^4nZ[^00yNja|pEY#y43{B/od7[h(mZ*l-g)8jOa,m$#l6I%AL7N~T]Q(`)A"8eWh_(u60%;l/89#*7RcM};e2+0j@!7DJi7]Jswk?tQ>5RV
Cbr/7L[dM:Oosv
prnReq2nbv|G!Bk:S-CJ@4)mW*kf@BHEYQC<];g@LkUCt(aS!(J&=WJk5"`?nya;x/btr;hm1yvA`2}jtHoWt1P^)*UV_%{(?4jJjc}q38*YbX;F-IQTe%,_h[0#g3{<4Aeeiv=42n9xtCoJ=bWq`p/uz1[pmKxDGA#P^).(qlAOxZ$-Jfl
@#^A
!k,x(sk1,oxOjk]FI/6<bN^X/,tt$!xdN&';break;case'cs':$d='.]^;;6kp=,|?|8L-GQ%9Q,eh2&_CN%OV#:mM@/l]FoAIbKlC1[MyU;q!`u<J+v!0Hpb!o({BcUK6*_Xxy
etALR1R";c{3r<Z^.ydl9w1L[$Vdeb4`UW0g:%cTo^<CwGY!2smM}46eUuIJX?(BUGD[TD|catB7}&IauG{iB)D0op$Zi/PA$^&Ex=60L>In.q)@fR,Zt(l?5j3_^9?x_h2(k6D2gbupux]s|/bn(4StE1Nc!Zei,koTV/43m]SLwD?M)b/]qEfm>L|4A)h^UyZ
@;#?{v[vJriK8l1q(v-/F-pY`3.LNZgmLx,k0KRr9O`e|h-MaB058TLO+<:(m%93}W:qb*&4}.D:G[3]eer15G=`vLa<#Yv
.64K0()Zlk|a
Fe"%3C_u!~>HbK?de|iN3/a{0AfkUKb~$AHMb0T%Zb^ts((_
#U]ZxHj`MH3`+HVgphv]LKlaoAtu/_qR!!*,ha3.O5c
j1L!~l3)|Gj4(.2#i>H4DguB!/uw=WzSY+}MYsUgzKsCMOyh0pI-$6>#pjC)hxWV9T4v3Dq5%/j0hBUGbfs+<ci7~H?lM8JY8iA"6Lo&Lv";35YNC4pr^z"14$vm_@Sm,*zM0xOFqYh=I,rUR6ahEtV+y
?kT#39"frq!w-HJ+cKOa%9nulYv=75ESzER^#&#6uGK);P*4"<Fr]C:qXA-x,$;F9:bgAmXC_>!jctUuFiJ,pOhcXI`q?GP"?59cT`/By(X6:ddnu($o>,"l1e>aoJrtz."&:=gt(
<T:Z{q4fWJ}C
]3>5%"?LPVE<"=a
`OS
6qjt41tx"UDUD>G*K|eQOZ+dNC,N81O#O|CUp}Tieka;+V@N.so5=`@nO"bCyirX5g=L6
x.eU?weZ`O;^rO4_gw>YjBXAofa{`JFKq>c;c/wx7vpD"^kdQbSiXKL9lmCyrGAwMT9&jl?&jdc^Omx|Z[f:i>7?vadWutU>,hnS(mOXDdJ-gk4eE2>^Q&BQ.,g)CZspfsc%P#o=g].aum3*Ds*-g22yqa%IH?1(*c*Tej(*ru8gbs<@ZL,La!3_xL;[hXoH/AKe
/$c9U@Q=Y#.8{mco"94xbsD/OAh#nEAWDJ9^ZsaA%z%;p1B-2-7wlj!mF:UDe*6@p.E$nh2ZWH7
!-]]+$=XZ-UqBXxf{f$
tb>+j+@m14"rnaq[FrsPB[#$(A!_dlBRx]L4fCn.z*oS[DBhMfWI/kT!o]q]><;TN*;><JafaZQABpElC<WT)EEvoA*LJ17"INL4GC05s0EH@_2
qGhm*Sh26q.IoR([4d{XrEE#?jgeqAuM<i0##BWvnE_Ww1J5njIazE476R)<u,S7ovEFGLEweHqY)Fm`9@^oOU=K!J^`-385wV0<+rqz#RlgDqAE15-Y+L,C%M{h">AdO51)=i!>94VQ<UBEX5(_e2dYWFZlhM
t_AA(NB,%FQ5+"L^5+k3-H9!
.&/[=JFamn4[c<Fx-<O88+%s<jf6*H1O[BN*XAXkGy:g}ocQKp}a9HwK,-S2mlB>[7T]M:^gM.1Z&*gx;b]n(*t(x>-w]
~X%h,]=E?Iw,
K#j7_{QBq:X)4|NJjxDJL;<?k
JEt#q7kc4>Y?%"@dNvtR"DZ*sS]#:fn/M>-<s$tawV,/
jXH<-etnrDTZH2}@2x=mdYCRo2Do^t"p
jj50s?"CH<<4WlMdpwv$%Ao)%~h|NPgrnBM6*{Q9&n,xE#_m<-7^()0>GVUHF(_a;W0a
$D{T=`p-9%L5tr{sCW7i".V-7.c,m8Q_jqVm{45wq^M?gHuu[ieMMrXZIDPG,8<DeSZK$gipetS"x^}2VA:,#L0ix&
(nGRCT:~1GC/czk>gE[mZ]A_Zcs+iU@O+2IqLTSt5Q0S/
;Jn%.gTe"&)|iJLO`ol>i**!sT,+B@SI!,>MZfbs#R@5D.]tXEf]7r7brt$K=+tar30vs+y@tePnfdn}:Wx<D5PW#4upYj6u"m8OX_t|*ZPata7~*F:^[&
HPj<{sdm%G5KT1Y@{hQTj!|d~:<X%n(-ne>((lY^@2z4eIWU{E5nHG{7`!<l4.?-UN;pFCwok`}
dhrH0dt?@x4DkAAIdsy3=:=Xf)&vA7eNdcWTidc2KI@J/cgkV"|5GH%l$NiMjFU1S?afdo0U1RL,?!,GG`4"+;-4^5a;!_djug^!zKmmFoObG^PhBfWT(rsH}3[:7&0Zn<]=G[p0R5(erwdP1]Y8GDB/*hMLM
zX52z2_R$:!gGhIl>^j-x.rGeR{-V"1eDZ$L4mk6b%DY)VqhJ4.IW["QNRF[^DsZ])s)+4S!xY%%Ajdn%[94Q/G=RTDkt@{=]1/"+Wu>LLS&u9&ETPzD,e8+(tWC
2[A;;L,(U}WxanMUov8*[(3Mb"Sk*ZhT6sfFMyFcm98vKR<t_:jxJ3[C*S#;1}[,6Pd#L[5`[XJFos+:hwja#(`CsHt;eC
1>s`7>(A1D_Iampw&`@!&t-PuB$O9W.[?;pJCH~ggaQZ/E!7k[&bm?9s8Q|oXj;tM&vp^H{cpd#Q>UK")f.nQ?}(x,*GrRk<rOc<X[n.qp^SZ=w`yfP<Xm7(zte/kD@y$Wq@$;!DK;f;zo!r0#uH9#rtJTDR6RYGyDYZBY0GU7ETcWu!A>#po8O]`rB_3e"B)>Q;2Sb:.^7FJqHW<#fW>-e?zJ]fnSId5piEDl.>g0-O%D;B9:Lt85mZM9GFk<C4"v0owk,aNY-/`Hhr"XNR}jR5vc0i{/)b>fC:eYO`4k"vnx=lT9EP-W4va17GZ,D*Wc)F#CHow-FYTb%CT9&9ilLq^fR.3Hz4[I&DK3X[F3`@{e8Ng$I_*EnwX[?bamcf:GKO3J;xRI->3=`+{fQiQ]YaWK8u.HX/D.Re=`G3hr3Wp[/W,l`2,xfrXIRHW=_6B548n&g1Xw_J-%?NGN1%KWJotxZ[/I[skJshF"`T7]xe=]4x^@^oG>r3@S&)n:kbTIO9Ua|H))MM5).WcBV.:OY1a<%p|2mHh29((Pbm08Dm.ZAfsA5/GYP"]psdB0!P([Y,*X#p`RsW^f<xTO{Ky+co*_J?HH(+}<KSvml6@GHmk
p6Ga+R
Q6g]T%ugTNrRA=c3mF^B?_9RPs`:]8lXMqmu
?B8b<a~GG.lBO
~2-S?@6sW?t
f_=Ix;cpPqovWFkLF]U=RcJ9=0Jh[W7Xo3K8EpJ[J?g!zhlQ$p
SeyC!cp.`%vF3!Lq8xFx9XWm?T9P*~L"27t_m;#.Mn4)E&:KV3R)a|8[V=dLa-vcaA;YHm4T"?n!,("GXhcKk/Ymlr%,&Ev:R#_2DrDC)%TsR|:bWs35_9FIp}6=n#i2qu6GH05>..OffCnn4`,
QTN3hPn`6Xp;a3]!lYO3ls;*F>!&
=@U<akxGKA;A{30up9#j+gNmF`R$F=&$@?x.PNE3NyQVe0SDNKwl:Epv`^:L<Pv%g5k_)u.P,3b
J+Xc0O0&deq[*(s&vOgQVbpTNRF)Dtvcf*=QX*bQ$hzv9h^f9RabVR;C~
&L>Pb510ja;>DHCodBo5w-l@|dBnaVthA6AsNy+L$G6hK2*Y4
eI!PwCgH=j>,
yDw`?pkzZChJl!kl[GDd^_^1UdI=c]>`O<J4?6Zw<S+YjL+GL{>XR^u(Ag_9`~[d^
gs2VXu;=c$`-p=<mE"0=t;?*D/Z/Vyx|="7omy.(tq;d0Qr<FN,S_3t!O)T=`y30u7*hUMK]Fj1[MUR|!UHP47<phPpf"hD).;vUJho>0).Cs020)T5GN#sREsw/W/b$v`?q*l:-e;_7mfOk!EtA;it8pW
7s=NFCOddl$J_iqHkN<SqT-#M^z7C3xVb!ZVn*#Ki<jyC[17":[PlNh9ii8k&BQjvdPt{f&O}M>2`pE563cd
&,Bu4fSM@DA7%V,O:Xa6Ams6wfmDk~UOUId5E%fym!B&%+,P6kjIZ0Iz(/m6b2d(l4/VR
p@U02WMF[-*?ckw%]!-b_qfgqsV+>+k;7.@Q:oZ]
WB)Js(;alwKvk"Ut;Kv*e$yvJc73ff]E9qp/hG=t)"Rp=*(ASN4Dq+l4JYuNI8L=8ED"qR>V#gVW=Z1@lne#Cc$A=n#SkJs.DruO#"V`)@U@wF+:q<60pT5*s>A9i0VlAC
Hl(IYcP-@sc<Sk?Q1oUd)K&)JB?[);LN)KYH>}^K=INE6Q7JK=t[Cz/90R2Hp3ByNM4N%
!&;Ih6t%W^WMA`n.($_Y`v=QP-KaL?J8Xgw;tiov3Dx&[H?Kd5Hd58+)Fm1I7.%1n*l4w,q#0H^,q,S<3?Jk",HBQ$tM4Ju_<]Mk#S@`!8xw08V$IG1lQ[=^>BrLMn
oM/PYlJq+eJJ:1m#"i3QZ,3/@*g
4[7P7]+oeC`bA3"U:Bc"[*|NELXpNCg*+BeN:8=u7@*b}(mG73SZQG5XROW]6R+01s:m**ucShx_)!JF7iyiV]G4|JtgV4
N>3w
jbS2aVlm}ZCv#xJipi*Itg!bw?4<k:F^{23Kxo-t,Wy#L!!"Y5_/rHt5eWsc{,Qf9HWT[t&%s7l8IIK8;mUN/$_aC62RJ%IKWy*c**1VoWXn!7AJv^p
CH?PaIct*H9aJNaB}[H[e?u)!U$8eQ`S=tOc/[&iLBlJYuz9>E?5g9Woy(|80#4^px`:f#(WgDa)
V3?.P[Dkt,"BCYi:1~eAsq#DxgN&';break;case'da':$d=')X/6KbP+^2L0|SY#"GDS!^MoGdA.X:J7:KiDgs}E+30D>p"Yy99PTd3M>SK=E<o/&MpX2IH5&E:")M%B5QS?G0FWuC"kw,![Ur%fJx>hFUU
us~A|I)`aUOl1u4b1fDAkTYW2QZ^5_rTEs%-F(Lg^H@[+`Vn@S8`A^7fl
u;_DZ[Fv/(j9j1>aaIbssaXmpT)nX^DE
*_X,gB;C;|16TO/0u1leM_H~tCm2&=hzk61HT)WiP-k33(%ZL
k<]J.qK:w*r/B,mB5YLoMj[]s/vZ6t%wq*p4%Lc$,]kPn5_XowD8_slhVzX2Q}GSYzpsiHsi7iIyDL6"rdg]/!&YCiLO.v!i3d^;Amnbg?#l&3cCT]ujtj7<_T*<*RKx3_FJ4UumAY&1CPVj/h_5c4%RVl85^$]@X#=.W;&/;FeyH7mO;yf@7"!BwBH;6GV1Bn5KqbamZI(ElF;"osuuW.KGdy6UbolZY`tnU?^uKc(q4mRS40uy&#4~wiI=i8-bP!uk7B.L
j48XME=ch7h>li~cK8K6p`eM)bJ6]*f1qFnT=+gYL=WjdivIf1bbD_`ywc6No];YFJhuj&SvwWXVrm/4l"De,5t(]2Fq)Vq`tAxCw<dKJ/
pJam]#A~;@E?(|C[Zx9emXcto]1(<Znv=+z#owM,d;%)iH;82|1"b[qu#s^@]*O9j7s%d7#8tjDdVKLyjkoTK,4-aFVa=Qog/
Fv2<)0rlD-JdAHd0W-YD+cyCU[Fc#cji2oGdw#br-<(#ezyLy+/R*-:.;>?_k8jVxu2ncLw2ri)KlQF>fM<47L[Op)WD_,7MtPpDi%i+ZYyiT+anU5U
z)uca;0CY*UgQB#Yj=yx7-N%-atm1muG;^B!8l3jhuv{F&dzes4*7vez%iDSwo,!(ta|V(Ca"lcsRH?S:Uq%FPK?C:]L_74)ESDde7gCI*Y
u7OBQLI9P5=6r+]R3Q2b*:X>i>C[P#Wy@Qksli?pRN9BuCO=!ReJyG&t+y<!_~r;f^UHN)K0:y%C]c8kUHGUeP-cu
4%m0aMZ3D@
KQ+u>_$Ut2)b>:#R=kd_8y9OkS$.B<uux!.C
uW6XFlIpp%hPBSf[gZ72Gx7X1a6WHgM5:P4eLh!sd#,8FXQohk
0o*$lMZIC;&s<&j[#r:al
>7X)`7llcC@brc!K=.[rWUoI9QKC#DlT6BnU0
g(G7QZq1S+P@228mMR,?f2+K^vyM8KD8K38s;t2Ks<E1*gc9;5@Q_VCLkA0AvF<DX/Lc8Okhk2%Q)+W#}Vm*#SIN*g[8ZpV^I?Hdw&Za`&)yhuesY,&E^G5)q/K:<g@U{rap;C5!K>,t`:YBe,_XkSJus$@:z.miRl5<Q+%ac-[vSVK18-T?fboGYKZH|]GL<(XZ_TS0uI0dgLz
*vKVyk.6i,5LfY3ust"lA+,KzVSN[2?9U)L)^)nPWB,mDCN6w0n9j:qjeW`<@]eYBGElW.NGQ=edIP}v=a{yoc"!*QN&WaQ:/"MU;%7Y41Pd%$Br%QCVWy#1G+QQ%HP)SUp@f;*:c]9Rva)PzMc*$I|T"`2f}&O.kld`aVpa:*)717p]Q.yp3vP3g2;d[CzZmDvAb_#drVi(Wfb^^K9;T9`"^!6&8]2[A+
>b-0E1e`phxw1OOo.KEtm~2r5Li3$_>=$v3qosa|O6FcnKNYijFnm"2]@!APB
?oI"/P(|[4I(IoC(jYFWV76yb#NlQl?h[#u)pyHC671Gc%a>*ybDq61,Qj$A^QZ#lSI0(bt@,`
qH41OZiRc%J63r6X
ox*]n|?8"9l~;EU+r<8UCIED=c(EY|FQ%4%4F#J3C^n7K+7h)iOJE
%cnZRw%K)hsd8|G
Djc^=E[y41QmgwgSbu8!Sw9oyNFZ#E8a!o$v^|JN?ebDk`945Vf!UVPpFU,J4y^66FTebz"dlcB<3!JifZm?g5,b6`-Bc(Vww-aF&unJV_I8Hd9BS_JLqR=BrBZERj:kryrdAi]inH.|U6oq-83*(KE!`-Qzx4Dw9nHUk0m6Q2<|odM(`oAcW*#C5Ra|]Cpp%l:2q=LYcGgBS+KK7fsK`MdJrZn_3C6PA0JPLvBAF
EYPlC"p1rmI:pID?5
!%G|skKEljDq(%(.P>eK[2],08Wgu5vAkQhd3:>MEM"NX?_[D54z`x^vdZa:u6/aJi=,Hwc=<-[SSSqby}Z3$C>w#a
$WYoos]B7ud(skCx"cxJ~aT?*%-UGvY+x-KIw]^/).>_Uli;2:P0Um-OnQn8ycJQ3$.j5$A(zV
QbEm_?qfrw.kLm5c0W^VM
a)jM4,H24wyJ4J5|[99tB0OA!jImv<Bd+ad6,6T42RK

w-Pkrgf-6XgyYlV/3(QhwL3*F[ecW;-&~;yjL+=<Y5QE4ce$1@E)956HPkqLUmGRKIAn}x3#c#)qMi
]?F?4d>3<,?hi=shDtr-IQ/G
S#)<oOz.%kRXB(cg@U[6rL@Rd-+OBorPoV-?IGwQg:,MeYW`1lG1q&@`nbj
qO|Z0fD^:`b?"#kfT,Ud-vOp.4DxzQYCH;<]i"r@2R~$:idI+/UXw8*#iSiIwZ/7Nvd$SqtTJEN]waTb~rQl)UNC7:kC_+pnY[5MI@`jJQK9FMB)14WOFjm%D$";3ohr&Tl884}FUILB4EwOJu&s>jO$=1_tZD2"K-oL=Fi/yMVqGnG<@UWit?ckw?y*3K3cVE[UQPL)>2q[.s&?35.2^du]hkj/t!j3qnQT$`9hFd9Y9[kUPwFf-!&i"K7%Jp+Cs#F%1yA25FFPN<(7hjBIx!265G6(cseC1;@U+0Aye7#V8aQ!5^OxR@7[`RGnIa.
Z*oKlclUZSvJv-bv7M>nTo9(Kb];S0Y[VI"b}&vV&A_NW"(l0q#Q_$/9U&}-8TLd4;f--O<f#t)Ytl
d~;=AVxXV!<1+Fea;k".C+uCiyl?ECPl3C:G#lI7ja<n.N<hGW%)<*8mYXeDx
PdIOPZ6Y$V9KMTqJq39~URFCG:,(5c[(*B6Yd[86JL([kf2pE)b+1n4Tmm+FN=f.Azh4mS/TW8&dk~JKNa!k2[@S_y]7S4u=8~V=f.KQQJr<HFr}m<E)FryA0nL4,52I4Z2@%nsID`b=DuUk:5##!j9%"S#7@hjStFayNWGvj[9a&ReOp#J2yy_yJd$O<]vWk5&!:$;JkL!m$VRw"H4@W/d5NV3dfJ_x5Y]LSxF,f;t7IPM[!LTpgY6e,O04IjHF7ih+M(Hxxk+BQ%!|bOv^In^D>Orx]o5R"GS8;u8Z_2!"Lwm;UK"5n
EHA?bhn"1eJDhy@WZ(DSmm4=/Osyh<HMK}Y%Rq::]dvK&W/V24#Rdi`?t-oFWNZc&i!E]<W(L8#?FDFCy,&&:*bwa/mj?`/^%v_X%sbB&)^Y[rhHHILzEw
%_Y&k6GM0H=9QQ=5uha#5`KO
<sYkZ?[63so=Q=napqr{h1GgE6-XvZb*^>uiY@`QlSAoq
9075^3"awSk,JTeoq;HEBYeH%SX;J1rIf~kcc(TCA*7tA
V8Pn7E7mYNli6UPZOSQo#3X=`@rUP,sbr8CAHTq
5LtFo$)];$i>!XjN.+tL@$.sxa:.%Z>Z);2KMdZ#p1-~ve5H.zItGpj>obQ3L02o9<b}/HBBtkmo>"Hkf~we3@b
6D<0:QR&c3PHy
h]f"Dn^~wG*LZ1gC>[pRx.GF?<pCQp)GLyP(o_!RNca`XMlPH@]U%r2L_^vtq@05unlo&/
O4m
!G_Q=e_^?MoE$lRuZmpT4^*Ro-h]:[6Wb@CG29"jFim]%4F[;mZNF,$7:1cuXQ$n"I%
mvl7vPU(BG;jGX?q6dC=ukjhb9#=}@L2^N.lgdxVye9R$kk7R#)n6v%qfh-EPT)a{a}5Fr|Hl>Wddbcq%rX]-&eF(prj%$Z6jL<vokXH3YrZD[ZXVCis-CX-~JgrZo<$^h|_"Jd`eaKX=jnnD8i`^Fhbo_O
yugn=ay!Z(ISCC{Yw)"%G5tC&:G.?`mFhZT_@XA&coum%wA%8b_.uqhD>9]^?0tH]yu_j3H=fl{K<X`CmG4t305#/H/*,t#""Dvnrb>7+HLU~h>WC)7yzi^%}k>v0dp5P5~R-P%xS
.K(ml/8e>o7`7xUZK<A%X&
pW-r;f!:x/w^kqO
,Q49b+1q`mx_$`Y8o%3cp!z&&.';break;case'de':$d='(]^ATbOZ+Eh0lC-"O4ag"0w/f$Cdu,6Wn(kJ`<04/Z,DYvCCM>9(=fdD%t2f93yl|9<h~Wlsl6ZE4-`UU0"!>Rv*v5mIvw<cmMfNV6-&qdy
~R$ADGFj4K&ma
lsks3c0k6QFxXHb(vQ9w1gl*fx77}i~$>bX:~cNVBjwE<iEWvg-q
L4X>Kgg~7knb?FKPF=J%6V>MI&>|`[gCM:BxKgVa2#^EYu)@/]qV&,GG*RMrWg3~nMgLAyc+F+uwD6BTy.hye$0wOzCjKO]@/0q=TOHI_X?9xMvLU4evqkHj?OZpMALgf1p?IeGThXxtW]FQPUPdp9paxx"7v=WM`J2Q=/LoJXyymtn!aPNilYucW{<AjV=gM2<9`!On>Pt4it(I+4L-kh3]FFhscS,mt<[^#zgM]hE7LUr;]aoq/D"[]_xc9Bi)wZ6t/B7[hmMlv4bz:UrhA(q@2.1Mwpw`q=SkBg/ql%Q>qb5^UdPyynF@25"_dnqeh0?K9sYfrqgSUM3V2;!dAw,^kC
L[hp91nIeGP5$M3hJ;aWZx;K%hiHtp.Cserf6.]k<3jKp9hl@aHA+Y|gG07XXtuc,-%c9,k_$h-DQ,Bcx-xM"W[&WKO_Eh*e84-60&PnX/2eg4VNoBwq0%`m},E]|a:yk;@,MsqN_$Gj?1Uh2J,6;[u,}K:D-!~tAoN.5LPc34$&*=xQ:S+KKB$]A@0mBXqV^l?Q?!AZOUTBE/:rx([[BoO=w-]^/aU;*9WGraN^[lz0v@
*Hp8)k
+9/f:5TogDBD`XQY$b{R5gamkm:T`
5UiBrg/JukKklpRJf6CLMKr44D//L-_+ZgPKajfIPUg(km_S)c":X!ZMj$Mu$kjGy)V4h?6M1^!dehvrLX:l1!dqid(iHqtc<XCgaL]Y^9URT3NT)E/kZ^oz#A4u$3S&VLl2z+V$7CvYaW%"d
Qr>mw3<wsRqvi;5#W@QRq/~*ysAeL)UgG?*dCOQ$
Qs/~i3?tr~3.cBZ*[saPg.8L_(04]#dB7-GYX{
a`xUIM,DOL/f)W0>Bm+c<9Q7fjUS[RF$<>HM*q#q2IAEAROb^m6$%s,%0TohZx5h-/lws.wXP=>iz7cK+5+iB4kUA`$2Zt%E!q{Mc(hh}.,I8Cc@2ntLllj/:Fv%c^0A[p=.%B{"WuwoWxqC<6Q_F3x%5"KrJ@|YUHFLdSx4B67XEjn4`!4Rp>"(H(:216.F+vGm|;N^"#lfibHw9.W(;K?/(=WFxID)3*>kkK-=#oq>Sxme0NOhfx4B.%*/&<_7}rL,~V|4UL%elFB?/])`RO;;~<gKa
;n9=,_$f~7@Kegor;x>I*GzJ,@e",K1so09t?xCk`@a6D,>hd(XAart0@S`@UpT(OqPsDBpEK8OPx:{ui?Rc~4K@$/P0KCxTi;=(Z<M*VmG<;4i=/%HDB_ky05-rN65q^P,=SjtJh=/R3=uiiJS6bS~C2Q:I@"Z5|7$%^e5+}X*38XQmOP9@Sn$@]8L8H^=IdN=US];bTar$8R80j84C$KCSEYP@-g#iPL(2e
2HucIcWhZF:2w[IOg)Q8(

MY;`XhB54SXN`(b.nDlonA>,kP>7wkTP7N@I1{/
<02bpPL,_UYQwmqs!{,Qj9*Clp!mX$rVRKP%cnbzscxo)GwSqH7k>RHYRqC%;c8pgS]D8-PRiA:0%nZ?,GVa3f)W*us>.Uf~-m4a.FA{=il;$pdo$uX_-keP*L#Xx;lD8(xJOOTtYtt]C9/1.,/i9V^-q*K|ADg@6=jq+]px#l"+>{02d1>!Lk_%$(;6Oej5k!jnsz:Gvl;5VcSOPX.~v,xqg66Pq*Si;fdh5Wc`2;@:/wdhY=:fj&NTG
EY.u/9R;1V?iv2Z<mJay7t0VcZp1*_dU6Pj_yr?G3KttiVp#N)p;uR0s[;Ho_m;
f*Y;"Za6A~KQ>vp)&E%>`]ZO1O2`%~tD"M!0gTCrD?E4GK:`y<;2LX8`$H7d-.Q|]LO)1@*,rcs`s:N;$(NEesNGpO7n"l[Ag{<*Xbl&Jz9:
Oy~Fs@7>w/i`qaBpM3v"uFm11jMm+Gc0LFN^[H~7%T2#1T|EKOipU)XG!VSHEuf8;l%"5)zs=qzHhoR7q1$G)TQ"26WSYWw]<ocBoFLA/MMik@_8z>&-#^eFJI?fO(3B+.
P97P!}q/g5ZtYhT5J,TN2W?7IYsW,?f%M}M^qN^Kyx`W3GXVZxjs#sIh:#Ap7w?cXa-Lf;(nU#kQa!(Y7w4-hH
c.]kxE*_0iwQ,5gE"0RS3uL[1f3AA^`DbF2hmr`Q1x41"Xk4+O%>g
c%u-y/jJ:F(sMYh-uL53%W;m9!6M24Q4gH;xE[0=&m^g3jA:&Klhyo]E$3yUI=$-gI7"ZxoQAo/Xp
GhWLLD9U6f24NSkpjG,R"A|?DWq:aps&Tr24&X]<L6~:$AxE=!sZf,1yAnw3B^[qdB@KG2N0KMHWR<#fHYXUYmRl+l}YKN@7BLAy8uYc,rg;mDoF3s,+*sB%ZQ@W=DQZP:GBT@r)-:|Pie5EcJUx36Per2Xh%o&gm"Km[7E[)5
_=cQ[dDB#v_tr09]cNu]O;"Qr?VH!FWx2*Uuw+,Ek8O625tg
QSAi6nof-6u>Fa?(<,bcS
%m^pI8l/G]!B&keV|w^d&xVv++gh>dLF38H?8<*eeC6>,154F2,`i!hi*aRPxp6gGy*bM#{G8n<PaEU%<lx@Jl1[;l2f6Zl_U=f3M]*(u#*XB$3/(M8In?R25qypa?:tX`D){pW5?OC=zf|<Cncl$`"^y>*WZ.N9LQRC67NU/qWI~CCO:NV$+"GL/LbE]^DUs
iw+@o8cJ>$W&"TyXb_y!UsY]Y)i;@6p0rAr*lv}O56nR5ZCTBN$?7.(3n0/uVUc%=au5u6oj^fb77.YPJ/
[<"
_"#/U!`y5o2b9#sl_O*DLmXpd#A0O|,#)U@ZN!lC<oGr[DY)"go6!8`d>vUhlb@O-N2T0K6}tEZptcE)4-KUHb$T18U`>Iv<7D.!BDGmf]JP&{<u%qW=jUb&50c7l0#JX2Z;F>-<Pw;k/L2j*{u{o5Lp#(x"89phoBwX+o41?U,EN["K;BXJCb&-+";n0aixu9$
<I*|[1X4na:P#t=~(ZW_7?,n(Pyq!ebcSf/>-g7xMN[%p*u;5l[R=9P"L.7-(.+bO@/tgk]kK2@8iUuE/YJA:v2nd@C!nBiiux1noUtt_2lkjVN!8Te?&u@K+75[s:Ro).C,IV4w37&?
Tfa>L9UIH#[B.GaJZ.6_S@FhC?J4(^cO2/1aKsirnmR5_$GHE5Glg%ytiVyPg[+T.N2IF3eP
hg"hu/,44yM!R6
?&Co3j6>OIr<$PYo;pB!vpi`f.$"0D[xNPoQE0|<`LgTgMOt;[;v}d<H"E:b2eJlN_!TYcwp}Rj0Vt@W$cu&QI2+Y=Ds+Rw#eGa6b1g#&>PDaiQk}ntU/woOk4}10^:_OOP3M]}4mR*E0M(?;!ZS$
MR
h()D5`1!
VwbmWKJp$3iAY<32c-oUcXplDx5R@vsc(/k4?JRBI:}-/N4Bda4^UVb_r_nsj2,lRP+n-?f6ru.vWGyvM.U58Oo.,wqa=&<9m(>P"(EVgZ|I)2{E45juIbolAv-r3>:$it*ctVz0GHb86t2EDf{o->yWG8MyeK?epfF>Q!+vL5vF}6#YD,rQ9Z&Q?>rw~edw"Biu1&
pCg^dY7E6`;mXk@CXEGJpoJEdslT&:=n/A<<FNb5EyvYrOX~&-=!%=sbdlK[:&2Z5<o{#*m46Xw3;=?=vmI=4>9Nqad/MiM|PZ*zXgjnrMu
[:`&6^p{_|Cmtw[KNye%"xb`DN-cFjmr&"2t#0C2^i
vA`!_o&*^68H/s
7}a:4hHc_/L$z&8)Eq8>wLr-DT)O6ARh]sT{ctq"iz<k+>:01W3.rY"t,_Y$e;0Kna#EtcQ*lp+`N`jif=:R&]IoG`!>D$OldAf.eRq}A]k0xUM
a]t+6in^
ti>b=T`(cUKI1!I?e!SwwR$Gi7Zm.$^N|"2XL%E=.3Y&YC{GV8r!DG3H]ml$d*-7?l#N5Qx`UU;4PRc/p>[PLblNc1V?10j*cTvc&-*dgkbiC27q81>5@L>]0l(UyQ$t.Uq=3C$W9%4Rqaf:`IL0e,iv)LkZz&$l4alvkN64mQ}LLe1Z}p632@s<9v!#`yeo+ITgJ({M7DSvD6
sHU>-WvV-!Xe.]m#/c7ET/dI.Y"N`mMZ@YXSq6&|!4k<Z"lg?1RTVX$gP7PI:s;pA:OdQmAQkkblnb"mtCFhSR*:>U!J[sQjxYYQ`]^QRb7&lKW3%NLr@)SEfOX
^lWZ?St6Z,lk?f"VJtP6[9^[p=0^jGpC!AT[*bjeUmP5]@S[UQ6@"1+.nMKDPLN%Nka#B0$V2US)q4p21|*%ee-#hEt]V-vLatdE8fpJt$cY]z$rbQcjnxi9;iqLUvZ9p7fvl-aCMt`a"2i(mEEAhQ*hv]0AwC%c$%Mgl[([&mrI,64xjC/qEnfIX:4h6NbUu{.sXql#+Kc95aW)gq5vxrLm043qL0gxh^-4&-.8C8z($(';break;case'el':$d='(h_Gfbop=,|?Zd0(Nk/^;mu:a<T"=Ix"AO#kDJAaRp=GeNIR^d2<4=uNBac@BpNrJI#*Kf^"frJBk"dkW=WQ&eZS$vBi>[;t37<gVq#d:/ym^bAE7sLP^iRqiB7ZxF=D9^mOD3,E>FunA)K__e/(aTvSYcX^21`^1:CpCj"0Dw8E?JLx=64Cxfo2O6~W$5!l-hjnJ^$t6G4fI!Ry-4!F7z%mlW/Q{W`_+s.Uug1fon+k>vgYbi.ADT3*3`-[6/0H$0VJ2$}x?-b%;OQFHhDnYmr*+S<#
e]sHs:,/<}17NsbeR6C.qI]yKs=Lcs$g,Z>cbXRL7H/)ntj?bPCy%@2!L
skd]x#b85ZBax3
})09):L8~mW`cly*yj:`:JPq]t;#x#hX03m.tBCflp$MBt6BM,mRctq7opq_kw|L>6u7KD
mvs*G]c<+LV!o$gMc
kj`?FjbxUuxcR2ce<bK[b-EPl?(F4w3G6CRF@tVyXq<VIz/~I{bYk!S-Bh83_9F|ps,>kCbtn,,j%esbiH_fP]t8(:Ef];A&${+3(ij{%kS]9.%5T($9B
$tA;kU<cbMQ:v;$N=IAS3074n:d~x&CN$^Akm
bk#uxw4.japj&/2O4.;&>EREA(:f#G,qpZ>cv1JG)]OX,38}3pDU8Z;/c>0Iix^}p5)>$H)YHnhwUyW{_W3
=zK.rh@#[T4wK2j^0SsU60H0seAA//a67jdm%STTk8`Lfpy[E(51C`at1_r8V(IP?`Q}hGYs6Ai`Mkvoo|@Slzl5@usWm!u0koXk]4QYIX<IfAMG2f
#li"wSwB0$aSkEw<!5ixuy$lc
~%5u_P}#g;7@Cg;IF5`
1SF#
yjvUz(H
2?(EJ)QPUQ=H!Y38)Ef1Y[o-k$hP+atgF4[}EP4[*iUh#_dC#@&&?j-CIQ?*]w>Utzl5dcBm+Wp}xCL|xXX9+@MAh2X/L
snF,[z,@5rF?r1%C8bqf:FV&`|Gz7z1e1V%O4$7lYNZ;&!ROL8?#h-Zm(ey$I1lq.#4=g2!ic"s(5"G6#O"b`cQb_(nOMdy..#-hNb$AK<*,meC#/Vd[(I,5bhPRY{7;A85e/=[%OZgHq(diW4[o-*2V/`E@JodBJRqzI.v;iAv1<&i[AXr,s-xI]k,-SN/^=r-<anBIVzJ|GHKiSfK<nMf3By@{kox^UzA5MKWg*^u(Gr-u)>e{lr_dh2spJy1<!+v[gRJG3S!k_cBMGB6D^dVo:aL.k6%bawrkCAp<4M*5c{!-.<u>jU6bX{:thHaZ4MuLGR`8Xbbjn"HybomeZh4R=_l5Ar(rOWeie3aJ<K_3j,U}"4ibCNh-lbk]rOxw+y0m!=27&Z.qs9iAd4YG<=`u!|@2#>2/GJ>rW0:YM`)v@{c><ix@XRd*/P_/H~9&(3PbERS,@5WW&/C"+IiH]Z!L,x23_:YEZ/hX%"5C$f@q&WB?=PN4C1Kd7JmVBbhgY/to$cn&P;r:2()m)CSVHljJ/MDiJGQQbAI59SDYxF$zm3>%.-E}P~!~k&@_Gjl*9KpXECB4%:/#UL!g$*M9=<mmM!X>`/yi`iGh!,4fS(%ykbTc;05`.8^cuG;EKT_0a}>ftSNzE(B!GrsCXX1Asfp^%7]M;*Oy9R2o/%/!g|>[wnE&tq
Uq5*p<^6x-NeT@!b4JYLCB`hhYlx+wYc.1_Wc>25]Dv#h?sF4bcbIoo<Hfo!?M@2=M%>Cs~"$>ZojEt=[p]SB%/FiTNtD
6cSg[
P?K&!AT]w-H9Z`|nU`gx2[GM6rKpZYU/y-J/F=s2-4M?fkHokK!;LfD$O!M4`r0gk@;Q;LgCn#AitT2!1MnRjxI>wMr
7w_>H,vD+ou>Yl{^P$g59W}1;
oMI&pMz%)(*PH0zma#*vJ5`!wGDAH5"c,)-Zw4+*3mZ2>P/>{?(8%1KgkG+sp1x)4:;$jwdjo(%N?^d$UpRO]M_Re:<8AN-5rK/;lSOf5i@$#&>dtjI%|<t_.0DmXRjeb,*im428F+w2*`7<B3}vF%Y^*.`mRdBES+xHy$)U8AEK=xw9Plo^Ufpn^pfO@]Ej{kw7sNF`@xuGF>{j5Rni`
5/EU"$,*)hYjY!T+5SkF:Td*~9Gs]2V2_L@!QVWad
K)7aH:PQE:f_AnWUMh#fFFQ`)=C2bVXVX;~$/U0f,o+UE"}yJ#]<m%9S^;W8YdHQ;@K;`R6[1^jP88s_;eM]y+BZ:1{jMH{
<S`@,;~eW0#C_O[=<yI2|@nU0v"t=<!uiA"g9Io>p@nA,E=Q3<<Z:0k.yq7=E5T$^hy
mpyUjwb>$&z@ryoSAkQVh_fVcRhg@Tt.G-7e~bgi`
;]uF9Nk/CWG>mP}

%|f9L[%Q66GoHQh@.`t6-H9`^vq2Ti"i1Ivi-aXT[uPV1OAS$0E;/bXH$<b/uRfngIb$k[[8-x-iW@)avQ>#mH6E^gD,Zc,"@v[$(vq.)AvLS,?0CeucXyP"EmA@[qxk1xgu2Re89N2V(M
UTIdU#pXs)cN>uc3$r3X0"FSE62Uw`LyrWGo)5_^6aMK^Fk*
.OaYdPWUaANR1_Sv!yE
Yi,Kolk3I8r)GS5Noatk(v$U;E[t;]w)7_Spcs$p00
p(Df/mn^X)PvF;SJSZ0O!$3%VZb@`P1()HUmJOlV2!,f/sp#zS;)c`8bSWsSfu=&h$1v:5n-e$}.Sv|C_[:C#++D+kZg|S^a~un]98GsD#mI=Vg)G&~eaA+k(bb>z$
RR>Rk~^b*IdN,y.hqIVjQs-qyFZHfQkE[u_SZWKXp.N@KVUdh=kRTnde/wot7Ci&tZL)T9+HwLfd-A)p/)Y5<i/F?t)/:A&+uDR|&<2C_sne8ho>RzKoVHy"$}HdK$k_N!QH*AnDh8F58o5s*a%
*-!.FyJf$fr0h(OS!4^
""`7eYpARYXRt62-sG;bF$3n=_m0P,lFt=RS>~Ohp3gDgVF/W?8.tRo{+IP_C%SXDY9;K.U//PByTuu0LwkLIfI<h]Zkv,Sj:l1P-HA""=mK`gvgXDP4gF*Biw.QwH"C#f(ly?</Ki#]&ioFV]@?#&3)U95q>U()B>k&W=;-OKGKSn+]C$7OHbmb%^pW:olzo:^lxzMm$%-Z4Po3h8!x/&*w,F"6.a$
wmUeFv1_)).)!$3wD;0-kp1el<O.j(I|0{&fE$Xm@|.>7gyvWeQ]vHPv@ZDI&^2u(4!>%:tQZn!}&o[d-9;./4l{&lb/o7uZ5(
3C(K
)=IC1enVHv/,cA(*2E`veAYl2hd~`qEDW<`^p#b$G~HFL3o6(6dLa#@DWL3*&~[Y3*d)D*0lUNv7pKtwN}Og/+[9%~Y:h&r49"W;Hn4|?760v)sq<X/9r%i|N/OovlHv9M5u!@WhJ>B?R>W3)79|o.;Of
`du$?rpGvmDcMH0F&Mo=0KJV&JjTqCrNkQ!:cy3c`oL/XT@F.K"*lmfu-7L^$O),[X7I(3:R]ySVWM9HoI$$DU0=^3Qk_(_+[2iY#QGcT(j4[
t=FJFTgE
%-,UmWfVC&5t^D92!GT;w/wR2<^895xOg6>nD2"0K82gw3Aqc#PGU%ndLwO#o@0]TI-mI9-aY>r3}
9ig%_@UA%xii&4=8B<6N|D"9:fiBPmAX1.*,`bc6AU<0qmWK%TXJ=K}rhk8%$]-]H@a?|;bR_W33{MC.q3m
U8P;R&.^=?uPs<d??F8ca"|mmh8+)$b;"J(odgowQlA9k3>tbsTqf&48eZS`g!x8ugA?zHPcS$G(YYEU-t
UpIA0,!tq;DE:LG`c/WUE?kN9XbL@zhF3a?Y+UR#m2[ceRhuMoR4tOY/*+(oNQ#A>~V2jI[Cd*,-LZ8}oOsKjCN]Qj(x[Ob6joV]^a_aWA?2xx#*2%v(^~GMX$OS?*jm%f<w6?yZ$nDxq3eAsd.^L1)o$z389tp6(H:iS)L~!ET8w["!vi$iBDf>iKJBvJ.)<V;KVP1-)NYRqOUxB1YbgZ%?Kf6Iwfr{YC;T[FeDZrQ`SDEpIRBP^(^El6Nrr!9MDad7V^8J+}c/CXD#<4`GC+HXNla0]u5>60jXKe:7,tQdCxF|opa+ZO5@6V>z0/eJ6g=CNVQEX!-qr$QFNvZ^94Xw!-SYYw9!IUwx&YwzJF;B;:25qA0~L1
}>R06.LFyi1P!J+2$A<,c^:dJ(@V&9*:rG.#x_=]i$bv?qnSINBSFoT2kH&R)u((FXjDz4Lr:Z=D5ut)1MFK=%Wa2-rU,)L6q,4qCgGgGu$/v;2iv
rMXAsDlW4%,fZp,ZPkU2$&KR|n;":!3G}pS-[EzQh:&;AQfW!XX/:;<50QOV2sC.F)-$:A45ojuOAcrVR
7R"U!@/W*w)I_K8K>C8:iwI
IQEwj7Q_C?%@Vm
<9[|Zjg[o5ZNk%O$YUCZMHd6D%WAO*S|V?Z6_UaiHhhIH4*uZwV25U
VAb*5>P(E(E?UO}R/D0U}tiva2~X;m>N52":o3~#[B~j7dywaCg*V#^2`Sb<;I&I{"0D&ZIwV6GY4Q2J0hI;b3VZ6bnAs/TnFNWw/?G:p5xcwPEr6=^/vA<2nIN=>`Y>jA-Z<&3)N4*s~x6!:>Bh-;,,I9coI")-hh`G,]TL{IzY@PsO3FPeF[^vh$n%S,Cj`NN2_$BxFsZ2E&[X9H7rORb?@?B<^L7G
ti>YI$KSp_BictL$nVtl-SdB0_;k03&W>;Z}%H,7,gn"/6;]XSFic-A2
+]iU#H02&H?EwYXhqYQm|Ym%-s/1Q@CB~>*+F62aDjW4MN_g#=d4V8P2/et2Coq[X=)0Xjkf{K{Fllij)5LLuMKu-frX,a`44[<N&nwHIoB+]cVB#.0+$/Q9*!|Tg/]bo6O
Z`s1P,oSt2Kv
5&qz8h6!.C7ui^*f!*2/Ag*`:#1?NO:VeybfFf-3;_/uu=q!6Y#rQp]l%^3^2xqKW{Y,f5Y9_pER8;dJY4j.,o2rlh`$PZBdwpUDNoG?)wkRxU3g_g1!;Rc_$<S{%XY_kt[E7-GLl/nSUH9DL
ZMw9IDeJ/YB]$^`5"r,u_Ix$!oqw+)a}IL8Gqm:b2f^Ps-cdwNYU2-4?7Q^h@NlHe,DU8-=3Nl`DL{)`1g*ahLSVSJOTC"NbV/JtQ1OP"koayD?`"uFx6*g=y0BVP`lGEiH6%"ewZX
%TI(5CPs)xGwY%K-jq1RBI6d-Bz[C_B"[O#W+TDo4YoIu^R]},FO2uu#;"9Xbn*XK_))Y<0aEFqaY5h@|ufx9T[<0*>Z3q$V(u_T1<v3(1{7e."8;`eE^]G`/lJ#a.tG[sGtm%E;imVTW4,V^8M4~YWH/J{mCki;}/Xm;k@ZNi$wrvy/x.ZJtg<Ve.y.TqHx(T7"
qk2AD[U4*,+WZ8=dSL%c+W#>9`CuWg
C?Dp,13L]eNOw<9>stCyr5*0xAM`2%HI=wM:qQ.)h)=y;lX!0t/9K;Z<kX24Nodulhc1D1L"]&}Sjd!(Nh$_9>|-{K:cM(RQk
:EKApSFc1KX$4Oo7X39tOrws{C0$4G?/xsAx^)?;p";MK,0wg<[_8?@K},d5trq1G+@iU<M=d9s5@i=jy"{dOiSAq@!XcU`BOg?Bmh3tB7A';break;case'en':$d=',X/&cbomt,|?z"**gW&<fbys3$
!Bh1]$S2`kS_.$dGqzQ<S3/!r4aqH.yeE7wd=x0q!;-+g"e!I^/$5YuTL%eu4iwg(}c3%4Riolj-5j=0i{eE3N59v%?}c)J`i%b-5XRg@2/NLz:/cb=D/>r5+9xN++bwA<*U@Q<[(ZNd/n>WFjIF=yjJTFrlw-HEHFx)>(2}?v8QFdAFFa>Tx!5XXM5yjw>[33=Kv-U`2)fOu:bqtRy2k?F;1Nu]9CBIvjc}Psy>mWt@6Vyfs2r8J.vFWpF%l@u/H-
{CW1b:9w/&7/<HN7-)1,fpub39QyaeqJ[U|#lv<xDaU6HEvI`o
Ir[I_TlVcm&Y>Pmvr-f@^xu?B:Up3BA#WI^7Ri8K!|_A;yG[x4$"umG"7~F>[:*$C@QH>[&`X3HGlgT1ko%1kS4^Y`61;am/wSR}_B_cjiD6$Mw,YOFvPx:PaX6vk*0/m~3W0s*b7LfbRo]51hC@Q(hL4_>k]ww&P3jwH=r`iMjfK#UbpZR`x"!]uyZ0,d:(L&4k(u^~KEj1Vfxrdqs&U)ZZm
jl&pF,Vt8Mu%J<o#`M9nia"Onhd,O-]f@0UuhgqF(isR51k0%jx3#50"@kjM`QBt15pT`3_t.=3rVxfvuRp@IbvUQ5j!5!A%=6YLt}M/ji5_3=R9InHeq#g:-ac4X"9U6h.9-Rtu/wg#[UCB`CvhFUDlGYl#)t01!(*)3BR,q(r=gi?51Ql@_>X3L/r
tsTl
=-&`7vX?[vq5;qkj~gm+2n6AmkOQKm{@7*-_6
N=;B@rJPw-eE=4:qM^KXr2a:_kp$7#qj&IGDkK%/$_jQvf.@)Zfr`o!G=jF!%iHQ^1@C5LXc;nX
bQSRyiFOHjuH.u1UhL{xxCh;2V=V"Xb9_Gvdy],ECj0[gA-18&Z8dkuEwuvCK(%]Eg+;l%0#Pv:Z,`c<b"cyW)`+dj[h1#AN&(bBF&5`}?*LguQp7*&vffeIEi3hQ;lB5uxi$YKAyT=Ix$S/a7/QusssE]AZ~IU/9%mDrW_?DJwLI]/Kan?Yz-a5UY{SCbZn.FNVIKCF5y4AL$pji,W"&D1Bjyg.O,:Ruw%jt+>8-E5aZ(Ub0w`yf)4d!MLL_yBJwxKH^"G[OsU+AdD?=O)MK+[j(S~S@NfOGeJ?zeI9DN"G);]OXB-Gr#l:EujOyOod0y.(JcW!61/2VYWU~Lt($O)(?+OOVOu02;&>GszOL$.xJ#2NhF4P{Xurc*`]NW!SiRrxVj<#^8>Y!k*<TfI9jp^N[&^PD^A6AeZpNDRA5nA465xYpF*t}H1rJ**r.]|NFwy-B0-9`0~g5@Mv7ZO&B6;d;MZqwXDcT6}f2MEoe_*-$73QH`B^Qvf</yHvCK5RS4,ps-=u;&BKcOf]+Ikq63?)Qne;R!#;V@>G?S%@Q?s3F=X@D<KQ?:(,tdw$chC;t@r
^vcXC*hu1>Cxpr
U0oV0F[,?>#$c!;Iu@K6&Y,{(R$1dX>`[RG9a5&,bVxvscZDFCD
wC"W+|ey>Mbs4zpvS0&^c!#2o:nSFntv%-NDGBiB.a8Wq<xYauyd^?DbaJPX6;&:C+v?0D3j4H`$$--{Wt#17WI_z&v!3@Wl&23B[<-0w4Q{_dX_)<6WK+HjhHK1QL:I,xmTdHvGAHX4,WuohQJhXmxdOXQq+aYM)/;oC@GryvYMP"sV+W#3`H&HQ#tGeU:w@Jl}DvqP
`kx323J`R)]ePWAma]sS)2W<Zu12^<hXU1_Xa[xK+[SJSqxb8vrM!ZQ"0K<xziiPL[W1
A.
27"QY^+
hA/,HfXb@tdFS?%7dR~U!:TwWYg,:i7w_[.K0Y:)EwO:L/vaQQ/401x4KG~%^,@NhZ5Ye-?r3]SGoBg^2I}8$>pg=D8y,MPm~)@[%nP[m-i)US]hONI2.RQ8i^.e{Q3*E3RF*1NphbZO^P=j%jTQUvi$O-%[#bPJoosM/<iRuyt=B9?
%$+N}/$7e65G-AY03=%?"NVgOk7Oh7i]dEoI44$Tf^)C>G9"B&E,&8C8$JG3Dh~j`9/<kX&lgS2w6<NmC;](LND.-2`)%UD`!
]xs%Dk]!Y[9mD"SyE@/.0!&DI$J1O4tc(Xq#0I*kFqGsswZleCi*@IuYAa;rgPNG,owVy>D#|Y#dLXCx]Mu^zYPPW8juCTN=Pdv0#[S/+9=sSlPTTNFk?tDIW?S9z+zt]I
;2Ts:Q_2iFQ*4a[#WRA=4KD,/!H$e^EaIo%4YRrP-_T|fO0
bt0(m
PKSZBd5[sAdhIZkqQ_UlW,3dm_`v[J(q!zCfg>gQH8_^A,;0g9i#2jMfmDVips+TwgqI"BvAM.4;h"vH9z"]
;1zRIVsAeSVjtL-%Och<(`#i5Qm1,+>HE!mXB?NdzI9V5jLjGvo"zV9
Qa^uzhoT!yo"p,AN)uM2l,rT65N_{;8,<8z<wl)U>h*h^)&UUN8qaS-u?BD#[Nr"t/sM5vpv-?~gIF./1[eINNW98C/I8jXqHf^g<`X>N8%Coc@K?H$M"L*f.g}@")`(WCjdN2beDJJ/L77p_RLjTF~
w-,-@rdrf8{1fh%daE-C3,,.A-/O9f<Zkj|VfMGi@H
kv;Jv{fIl-usLh9/*V)5%yKg@y6$Hr*cqWBa!H0#()<H,:sN5SEEUZT>feYPpXE:E5f<4tcBll0y_^hp.&CIjXLYJ]R/<7U6"x30HE@TG``CR{4,^B.}Eg)}B?lt0^+6LVAsre&;*5Lrrl;kpLkC=TL6R`yk?^w"a(!"6X5f*Bk)sW_If"_g-A
sVE;CPh^3?Jt[`)?~JKKfl5`OcDd`wPuZ2RDpua)~tVAJOU]ZK1wg@d:6ZyK6:r0,a&<bUBu7Hfud3W8H*X=zk%&-!<6`J<8g/E)ImQ?}%2uJ<ywtjJ%~s,u?!}
qffRI,ODPtJQdQ`ii2jZZ2YCzhJ>LLSPP_Lc{1#Bxg/h#Sq2.wV`/Ncg!pUD
arKq6rWJoY=74y4$1+Z
`KNv.X^9Z#K?-TNn[^MRQ_B.>"dz:i]HK:c~]h5rSs(s${[`Kma@R[g~65`f`>[TL2fWm5NB[iD}7S3DdSv0a8
_M{lq!1qB@]r-YDmwJ6?)>RP?P]J8yHC%';break;case'es':$d='%`G@qaMD9*70MN4!ZYq$ViVdN5A9Q>N"O2vU0&T!ID/WZI8XWWT6D@0!Ywb!E3}5Z"}t(/17
,)t-IuyY80"4ppGfjub7JI^I??d%lX,!PXo?/7/GA)LG;C#f5eh1+`[]pP"eFin:wkTZi1ck>)X=wUY!Pvo0[<6/E!
=hAFhp1%WpvnA_Jj-4~>]-dQz
RA8`{GO!XpK1r0WGl=5;iF?IdCfEM41D~xSK0q7Os]6
3;~jFtn^KbQGNeq3L`mws_o$EpdOL
e^>,_*Ov*hC]Zd!6%4xczxC?rhnIeWpXke<)C+X!.SzFjn-s*)Ax@*eSxQn#mMgJRZ(nmT*xeXq32k)qU:Ze
?61W]c<9T1*#6<H1b@DmB6
eJ7<EhEg.B&m(uJDny6TRWwCg/pm6A@7}Q9(Rb=Wqx]67e`o8ZG/:?wqp"NuPM~a`06a=D
`|87roPvdushu4
!OE]mJrQ!&r"mHP;D@qPxf+_|4/=io7X:9R>^_N[-lm>M4%%8KFOd0>>O6nAT/B<RX)`~@#0pM`TcB6x;F.&O&RLwcsI$xzF(*%mL==S7?7GLiUBJ
+a=dj5E";Y]NC?NQF#<kU;Z[&GaOQAX*$"NW(!PR1>!PM499,1J!tEgJ}D:^zIc/hC%:_W~tS6Gs^[W[~[],z81j)JMwMMMF-1/
Ww|;Q[!n-G})anax%,Lx5l&P&$#GJNo>jM(w-B*a*w7&>yyvjyr6uP{UbW$kGjdZpaV-c0*v0d[6^h]v}mQo
4f,=(
&b3Zb3>1KGlc)1FGUL;;okc6yQoJI=h#3R-V)CxzuA]Ix|VrYKa41`id<o+NsK$
?fu><8wj=C,_R1jE2h4-&e`qRW1K6a0Q5Y??:WA~ciH}PzWf]]L7qTv
Y<V`mQtt+an%m3pGBxczH[b%+K>C`eF;ou-
UvKDcYp`ZSlGM(TK9D
Uso[^:I(E[-[8B
VyPlKI!%9sG;jpEVuH-]$y?Z
~v%[A<m1V<@@y":P/`hI8y#dS@)51#![OK4qAGg!"Qcmjx&Wu/];s!Kxm,x!-d(cqrv/xu,Lwx/@{"TSx3Un@+=5anXYu2NCeec<]o~DD.i=Z*m2a>q%7VG]ToN+Jj}y0(k3UJq,96k*WpV.!4rVyA;8B_p&oaUNEINCciyKDtR1aC^@=kN_hxrL&#;A1Y+-F"=/!a8eRK_hJs[V^OGJ[6(w%h:$IryoH+?4LZTB|F[VoGZff@.JP(=k$FCuqeZ%)Sk7XeLl!T&Iv.(e
*=<;N<4UM6[tlElGuR8itN8"4|?hSmVW($T=eoUoD0@J,g.S]:&+Vr4v-~I"9a
{:l6GQ^+juMx5GAg$_$4nuLREC)Nw0{F+<g!K2~q/Jc*=voVfRnGTG~Yjb{p3hs_UG{Z#+BK>H[+)`xgYuE`WiGZa,VSgiHX]q-PEu24~w]cta7x~]15RVQX1Tqm
I&JF@GiPGn2GgKV
>h6"!s!HXr?F[kmy_l#vT~*Uv[DG24vOxU.oE@
lQ=%dr!Y5#mWXT/KVbzu<SMJ)f<2Xu8%bP*<^?HIc/([xX*):_PgOmjA~R:!U-oa2%%pp#{I0[TUZ<GD9lG
m?sL"
W0o=!]?dId%#k0%">=Gkkpz$2G}M7aUa-.`.Vx`QNp7IB$*$iz)m@Drw4p_KS8pV7W?K~(<Qy`7O&CG,.<@.2g8"m:&O2:_PpBBvr!cO~4lKS.0:k;L?%W9S="2!wtK<S<8)ZT[uc*jCp<gdV;:DXRI<_CQ_KF4>~"8b8W%ek(w0WW2v9!IGm8X,CEvS%.?M!ozZ[,S/EoU"35qlE:s;.6XDz8~;NF8lO"SEZP@L!qc_#HzB#)D,3`*i6@+=#9+If;EVK]Hd<mPuvBbPiX6Z^<cTN`T@U
%.f#5f?elB3IdM&wcK++}0-"b6O-eFjI=-&bv3tNm=77!k7oL!0@<y&IukbuLWtMFn!kVife][_,~mzGg]ufia9m}kN0MQf.&%s%gjmcD-MPA;mX9W{l!L9"Psm<,$XdCuH.iRy`H?jNW"rj/*1WV;h2x[oFy06(+ow8qGD@IVQ<mUQX9]D[,vP$6TQSLL)o+1VTFJD>Z+mBwf[li<gC0c
([VU"M@OCFsv=?>r%DlLo(p{L!+X2b=@o5XX%"OVW1?(oxMWC`)<mG-epRj&IoX(QE=|>D1@qM>A4
Fg>|79KM8C[XNa_xM(S*<pJ#5nGBe]c)M54(5|Jt.(
`dikgO5<A].9lj3e%]1#7k"JZUD%74UdW;m:Jb5`/ohlm@v*<aoOcAP,[`.Cg;q>F?n).fwLP4B1mq`%v]L%e_9*RAuUlW(kNu5Ra@>*v]_XN*|W/sPi/bbFBWJInx.$%=:WRISB(YwnBy$kdt6AJ(=@q]%&1ihSL4vh0cm42>FG:bWxT5LvzX/gSwO]Js}y"IZ3f-Ndj)tOXCY9Xr#ES<fw%tc/^#]
zfJ<Uj[@dC1-I7lRWjboc_[ys=.UlBk"l=16O7kc.1q(I*1<+(%HmKfUl/9A7>$`{HV<Gd+om0G*1?lbCA7n(67pDuiY[Zj$gKmE5ag$c.R^PgMl^EQ9IGvy<j@8G9c=vsw[[yKY{f=e]b6,SR/NZWqO~ufeci`7B3zt/A?bouTq3^6PUM^jOy!kF?cFlQKA-#oP4.#2=>J7x&"IQavDm?>APRB_2TuDS38kcyD_%bw*x9CT)PZ]^T#,Li,iybDA!w+BM<F^qAIXA7N9hFAW0V|ZvrHM%0M%Fin+boLrrn+xb5P2p>$1#HOgD3T;*:#k(FS=T
"T:PDm)p@d7"*]JvqQZ9L]+4kTrlfhk`:;;Qs])yo(6V{_?,*
]t4uvZj`FkW_WVLDK=!Yg*S:0_>Wb/Q.;C[$o2d@nG+S8UYnk4<Qf2(0EgLiw8)C:`7Zf@_/>GDUd+!_OO@[p0Q3<^X,&n;=9sp`.QKj
#a/9mIlqgu;[YslRQ#-JR~C`-txs5~*$wf@[,Jr3&(hX<cVKxYls$X1Ao{lE56,XmR!jsDxxh(#ATY&hKFS_7{dJ
!FiMzYlG;"8b#myNbm_Sa2?028tpQ;YL%y3]n@Pxm`HjE#$y?f;>#UK9V+A4iRMwnexHY78^fE&:w2[T)R7+r.y_f4CR%`sO+67P_cigM3BPT-tDSc.w;GCx]51(B!uZBQF_-2dV/0%4NDol"]XF)FH_VXjh5vYJdau74BgP#7T?ZT-]6AmL!P"o{6K<cy;]0bfCS*@iBWj2K9IU$is.r]VliK9YgsQ%ENc]=M+UM_@F<Y2Fz5s5xkr>c6rg!(JGF["mrH=%YLXVL
$]d[A4a2*ezOX09YBx-;yyrHZf=1oo~fEj+6C,2no".@tQ6l3_3[>w[jn7~nQ5N@IIsKCxYyq_Sg!?M/22-XR7D!a_e[uyMU"h=g_%d%hh56*tF?3h]s98u.kes@v?N`!td/*uE-+I,)~h_3:5lGbJq9Pp&j4DZ^%!hB`&D"6s@Gf51(=y~2j(J?Eq{uSV98}p>crI^SOvp*%I|npb!VN%R.bk1E
40hcj4(h">"He@&I;LjZetlU/o3Cwx^aU@e&mZ,,nD"&/=0a2ADlGx#S(MGp=A/Xc<;HMJsq*PMP;t.Scm4Dw4jyh!mdKPPaX_F4H#F<Ofa@t$*O><?c
8DwN(T@;J`Ml/*krK=q_r@|v{QsWmbCw-Hv18qd-(_"dD&Z=s`,(*oDmvp;n:G:J5yGf?FSG*9qAd!oQ^Up648J)scqI7s$e;K
2q8E$^#_
kvN-7x5jf.apSt/8`qXiri.xi
3bD!8n59*u/uHL,1^1j"qg4/;G%?rl8J^tpeb"V[09bF6ubv.WN8J]@U*q7?"MJE1l{PR)x,n
CIyp/w"mR4^!6iD.><s@Wyrld?J@sq&%<-qt5Sx[YC`Pf^OS;V}X{
njzg5a+@&X]yS8^wn51^HaR/|TxH{F]
#,A-Q=oq~7?<yke-;7Ndnvts)kFntO.Lgg5Ib;o!SEV-[BBL&G"H.lD4_&bNx
rY#iA:(b]uLOMpTx8[+g0Cz26)cS^v3g%m}Vv;%T>ZmIbq*^s-0]vQndYQFs)l/%iBat:S6HNicJl,=<7+LiB3>Q>eQ%6iva5?HLE_JphA&iyQrxZks#$<E7T`
C1eIpvCgFE9raUR4)g+FGJW~W^ZU
@GvAF.L:).%HyWFp>t?]LjO*r3S4=Dum7$rr&`.V%w8o
x3Z[Zp:.)t"UsC=^nl)QTM6[c.1^
>V{&>aR9&1whoJWlNd9PD^?-CJnUwAJ1Aw6`%0hS}uEAKU=av<z2EC
K~hmY{ar^3F-4/y!b!ncL`]
Hz6[;Mwp*
D.L
__08?^f6V]a.Q6$t"<?71|R(]XMjZWG/"V#fVd4gmr*-@{4)ce+|R}AFwN_"m-lGo{)u.v*][,uF7lPE!@Nj:gO8@}t3VRs^s/0wQ+#^Yq6XL}I~ySJ#J7>uII[TKav
$j7K*R%lJtP:e3!yUP.v2EW,]g0W/[P=yFPd';break;case'et':$d=',R]ALbOZK2M0mN.*X]F
3><OoE:dNAh3Nqk>,K3C[8``4(/?,(t`eH4_$*yD!wCywAU$!&-G&FIk&Q[Kl#%U^rs3Z@&bUQ^3T$XH`AbeCn:3$>xI&JVH&Ztk6bo`GBm_iLf)!J)kB?uU4JTwkf
CL4fl+7%@i+QZ{g[aX[Gr)?C0j+/-^2!Hh`vw{Z-s*`??TbX`Feq_1[AjLvy8b."M8RQ*<c`vEF[l3J)Sj?ua"g#h:rMCv`qt#r>7[y2?&V=w;wm+tIJ6},sutw{yFMR`7qMA9Vz]0LwXw&c7Skr@xx-mfn0M"t?qnA~l@dN15D^TX;]EGSgO`kCaYqCiH&G4Ig>^GasSoKO>%<85:X~M@hui;(_kVx^fORW?Sq]HQJ1T4+[>J[iE}(+o[[,f;[L:|t3AKXV.-VQW]j@hPSE]LH/b]Y^[R!rH1P"93!ie`p:<(U#@gl)3
;G;3n5[3D0c#Y}jVLNJ.qIk8Z~r~d}+HZ+HmA%#_j,HiDhgg8En
Ij>Jt.uc79Z6eKytk!J?rw_k&8c[_4o%A7H9(c%=OWO36{;iW_
qf8ec1KHGP(iTIu`ZZ`CSHQiz2)ViUimg&l1Hn_m:`s-ElaN_KaE`p%+7E(vQ#eO1m1roG
;gcv+ahjs_FaR(<1B}n6
=M]sqv&
_Uws="|u~b_n
bsy&CQ&-#Esr=bx
XG%&XDm"2k@;ssq|wgXiTK.m06*<sxpyZ@F|xFF)R,mXGwl!i6IbL}_!nDvEx/P%K/k<a80zbzHqM*=Hlgw<RD]">aIEz$J}4[k]
gU?`^>}RV56yfav.H^&O;.YtcX3]xgImQRxXm`
K+okAVkle0SDegkq-vL`51:3B_a:`z4_TaP&@m0+eQ#|kZ0u?bn3wl@T_nk4JDTUPuLU
T-:5(4_TZ+E@Fk4od1oPSB.Du
VVro5vbt=
$@NCCP#jBT|vA)^Y4hrIgs5`ehN;P4gjmuA^O54By=*K|u1:{kg4/hjq
OyE_m3)M,V(GcSi~Z^P!G[gRsZ<@(hfxT^<]-1n"_U)|LT
5dVlXg]AVCR
D,fih_kKIrf^N%YD2V*py!)Jw4&s?B;aI<!ePA,@wQ!B/Y
UYxXGeNFWWA@^NnzA:TL1]2PKzG.JMIce;j3yFp=4sT$s?rOaWG!yu5ie-apC|s!@Fcf_1PjKwI/:c
jAWntP+XF3zUlcwpaUyt51AhO
w50:(iry$:f_[ety
7!N,bcol1jIN%MjZR^]Dj/(jZTt(`Ok2AjWz8
3>)dunLL/*j>QVvXkXpUA})9&MV=y4D~DZ;x;)M,vYk74+30W1-Zk8I*L#Zx9.=ypEMd%(3^6o:{iH$Z
_Q@ZDS/nmF&0,DoG"g0.rVs&8YMr:5b>&jIS[qKt%ha0SfV
|JXU1]y2,pto(u;e1i!t#u_iR0octk5u*Mj*.tIeT#^w]c<3A/af1ljDbnk(%0q5Gr?AL6?W>+th@P8cX7fEX723IL:+I<luE1aAQl-cG^qt|NLjj32.yYxq|im?57DHI9MO+h7U~O&g2-8X/OrPQX,(x_!#F,&!,c?[M?%k]c:#e5YYl`cheyP[;2AQ^%{o8@z&9vO^&G5f{/cD/I|E_l5F`+jITC(=q!9<q:|/]H}2]]2g"%YAV,[;_">]aFeD[p7C/Pl:G)oqh*PDbBaH^ULFbO.)*e%dz`9k*2I=$9x!T5FSnj:ej,^O`@Uvz1{MQ5xT_K)0Ih/H
K?EG_Qd_ij5Y+b)Km)/5!{n72y#Y?AjzeqMWONnvGA^rTdQnUb(:<`(xZ^<JDS!@rpFVC|&@1bwaM"XN/%5Vpfq@yEfJd_V;[/pKvbx
)]b@>H".),6~l&KmAYqhF%4?(ANB8^a"n0dtlnlQdl."j_U[q$vF$B6pUI0-mbOgxcRV6MoT
DVoO3!S*Np:`V6ID))DX3tsoS)!6VX&86)_RwFd1dgDY}-GZ,W5?UiXeygZ#3lI:P*p12WY6@b<WkkO!y/>%EdS<{Gy)8<I?NWcm#/jJsFh"<e?iyEwZP*
:t.>cE4Dg)06phaoR[d
-Qyo*WDUf27{CHQ
JVZDw7r$?=-@H=Y.g%PNWI=9!lnYM;k_@~P%XYJTpbRF!m5x6f/B#HlBQS-VhtoENb18?##tnaUYcCRViYn-VF+UvQ?"8fOl^aidtXjw2f=9<+h?=5ma;6j[SW;</"`5wUmE/7g~BzvopEe"XaWZJH"*g&mw$&Dh.>!P2]pO8|dKYkFs3DpzB5j1[O!5;O&ZbD)t+==+/yT,_<f2
?;F=[Jsi9kUc/iihrHP%tl(rH,a6-_qun)%Ggmh`#S4tQuSKjYgPG2US9H)H>3@$s5HtLXcev[6-@1pu;YZ8olq1odIp^tf*d*b&7hNg!NPq
X59YoapB8kZxxr1A&7Bn3nVe(c"[Z)by?}wFTB>KCBdyj*Lhah
JIu-l<0DGSwiY%"xtJ_DX&R3gt<Np[w"K/b<%I&QB8m;}MY).(D$Phs&h"%96IbNTlr4so7.RkM(28oAq<)>-Hg8Ogj!Vp]<Apm#=E<=_5`&.=oy,;%8=<r9}$P)qTl9WC#L`?kx+7Q?jUD;FK?l>$zp-!u80m=UVdVct:BL#0EEoTbr1RtW
7v=P<bYViW!ibiKC=c?M0gHt"ot}Q8_!Gi7uPE8AkoK]D`Ow%=eo:4YrJJeK-35ZfV8`+`y7OPDtQsQQe.:Q#f3[1e?wC@2co]0M,h#^PVC+6sh)KbBPjN$92V?j`aSl)id(a}^0kONqUO:r*Kd$s9.KY@yA*[;6%IJB_N
lW$;b00"Mvb/VrW,+!FtWw;vf4pNX+dT[lQD-AV(L>i39a
Z?^GO/39*sJv+k;RVSJHX_[cH1>A+p)v(#]es<3eIEVn-&xIf72m

a?,"g)P3h%.H1IkSD<Pl$:vzGV0][A6QIw*p%5Fw6u[faw<pWBX3:}$luA+:D|4bjWndCEN*3x5:O!-qK]/aQ(37f8dpZ7"dCdfy@R]CSOna;hM:eBMfmbqI=nfuE92B;1V&<3
J"*eYey-a^DYET#E/k:[cWJt@>xbRq4Nysmg<P3%,*D$/"?EK?ZgE@mTPse/khKqc$o#s@Te7IvdZ_D8+FH[KyA<?3J2QQ91m/M#2;#"G?zuEO_+|tx-,wQO[R/
rLC>Sve[`0XM<No_F&{B#hOKoqv)1TonY9bu"_K4tW<i5L0-PTYnyY~rF!SSJ7DPOds=ff-VaqA:Y4!/1t4V[VzM>Zflpw27:O(T{o9UQ0nyt@@ZVUnCn#|0V<F%2LBb]&S]$`@YqEss0YzCDf*p*t~$1o=3+sluW1W=pHCGtPbnb3CT?M7B]AX4@Lqw_"Dx0.(#(L$Sw@xSm%Qqu**e0iVI[S5T*SJRORAF5w4;<6z]E:?pL6HxV"7<$Qp/;KU,fW1OdJ)?$!chrplpFi_5&>M3)&6o5CgNcRysI<T^;Ih5>LiH(;]BKD]J$T<=XN"0Y<y7>ou:]M(I{v=#RBj;:U+U|9.xiXm)Qij2v.l`lH]
+;H^|N{x[dfEsk{A"5XbT=Lg1H=sPSTBxn<a2u([DR]tDDwFenjh5iu6l1`7XKX$<[hPeH,*iOP1=6F@",v!Qm2%NvfjvoBc>lqoJP^f8HQ7^+*80r"t0bUj[#x$jVYaKH{X#q_7ii:(6F?6
t1_r?,^DcsV3_O6)D}rNVM0{
=kD^pVZnt_.ONMIWI*L`5`K;6$[!/.#;CV|>EVg2RHUw7"^P{^)^2VXhLY%veW2tx(jt@,&="#khS#`LQnB/d4h&/hgbq*)jqb.M(x}48"6sW7SN-j]R>&9FCU>r^ezyVe_o]y+&qxF!`_)QgTp#e<{i|+OaeOA`g$7Ft.
kD,owCCB*4Y9E,K;kk@Kv"UI)+ZX&tH$!RI![JT9#WBy6~GSFN5DdWO6@{)?KndR,LqywptYqJfIkU<"x6mQ=tlhcd[AG%kGM1^5dr<|OxkJuU1wA,f}krqkL70U;djceO^g/jA!%"$kQeU;DBE(0lPBw&1CXO+<cF)Q:`;V:Sr:
:(Lojw1Mg18!o4u0[QzcAU@N<1G><O]5!hIg2JQ#_C*;aep$<QN"L?yP<3"7E^IZYMdA)>9nr5iA>wOazH/I~X#V+pH">1+$u)tmfGov(-R#^3#mU@Y7_QC=:ZBv{Al.6+9yFt&KYe]/rLQi4SR#i-)B_vx[SD/73`Wf&s=TZO"jl*E
O_#CeD,8=aRZB3Wg1[1iNN=eZd4$d.mRZ&1pITg]|L;oP"|.0(["]OJ5tyF6m+8LsC?/%SGlA3TUGN?wqkY2)&vk6WQ]l*#dySfqQJVLyKLtvv9_
Keo&MgI89a#MGuSH?(PDGi%nqyRdQU2Sj5h084VdffBGi5gYCs-)S
RLccwjwV".TDF|6`4,V^BIP[ygC%';break;case'fa':$d='*c0;;6hG"B~?b;2-g;A)Ergn&Z=1V$/-(IE*<]Y%wR?C~:CDNPt_I)K>
I$wbgREG<_GKFM$64eODrVw?3
6[nmBYBYYx.{!}q=6]XSL_Uxx
jQMMRWFmnWad0)G|uN0En=iylWV#v)h&mwJXhV->XOVtW~UW@n[;]~cl`f]@F9fjVQV^d"yOh~`716?@.yW-:vVIVLbMR:D~>%v23tP8aJr0A6WQKmOvJA6BIBwuNsB^!3:CRBT+4L5mPjg:[[h{VwG|6RVe(,FX2V$dh@UB_1U&kDYJW,T+G735`;M22HnMX{k}Sw9PSkY>6Q08oMZmEkx8=Oazd[x<_O@SiwyeS,SHyxkZn`cCMaAq,~Ric4tCSPtRv9=H9@KI+#l#fexQtW8co$x]uwv]5gt`pdImuy^dy|x!mo
Do9f>hNEhOB.?OOGH_F*"q5Dq4|<oup-b:ioOc>!]i
WB9WJC8gZtAC[]<-"2qI+i>sPMS!ws)ac$HEgSWE@$8[=7GUF|^zSFN;=S1Q<fI
s6[V"ALtAYa!;D*oqvC-k~,FQI7q`p8_%x"|8lE?#3BnRzQ,,(13OfbE<I_(jzg-lZ"p($D3VvO~y#6jJB]T#*^Qbu1]%lwG!k2Zpsgo<~qP))wx-9?5P|J>UDJ2kBC{A3B0gQ
:noXW8m*"MUQg)ZI-):R34q]]-e#uRB%a=ER/YQ+;xnMAgMqTgS2TJeyzodw9l[h}3ID-d8Z79[>p1&O`_Z7cc)W72bf?!6X5M#Nb.8]HD*T|ifpVW[%p&@ZnDq7D&Spcm2$Gsv"<8CB|[xco.~niiwZp@n
j2$$!0@NK$,)WAQoOMkss:vC76Zkp8K8GGNwEKd`epg>L!?e.?C+w1r6>,Z@=47O)mT(YBq]fqK*nW^-
FER-l2eO7Y>KU-ajHj3I!|5e)a//t!t4H2@KM"Ugx1.4RYw&mZ$PLb"V:v=Z5HR7b-d
<gA}V+Jk;gNFb2da^%j"G4S4+X+a=qc5Z-+>5K"1XY-Wf.%z4.4eEQ.g=ii+Xo<
)#-HnL&cq7m)0zXj.:L[/R5;wnjPsIg5*P)IInV1"w`lN[xCTONfaMl)>+iI&B8WHOkw7Ll3&C[wuqg45Ze2,VLoBz+{Ol`$A4)eNLKk;wagAo:fazr/L1:?PNi`Jsd3qx2v?!4
Zye{v^=]])g7;cP~ti2p%{[}Z/c~n)i>+l3Q]bos$s;R;xH1CP*GM(v,`HD+)8GpH2rl-(IP$Hhw$W)LP[PCAw,G>wg-@K`A#g$8DP0VxKQqBQ@YfZcZoOn>KVe-p"($a7LdK^Ly
)Vc(]hwCC.@7KLSi2:)HRaqglGC/7oIeZa4W`Yl$thdHe*Dn.@T5u!&F!Y)k?/Sc9J_hmX)u{PhZM-Up[S
u|=}(}lGARBLt&Wrk@:GgQ]Q/kfQ)WN5O1+SD+aLpmeg.J@paL$fI`(y3R2mB>8GF)6OOCvg3
W$A"m(AmXB)#7{M":=xcqk>-_hZ>JVJ)HOl:ZXdZZf%n]M9JV
u4X,?SUIQT^+/+y&ja[kGx<-S|;H17(?5;McTN%loPN~th/0m*![aeAd
Jn0IHGQOepP].qdA^kJ[</HYlJ/@];{;apbbx;u0iUwWk0/?aQsS?NM6%@:Xx:Jc~?`E_/]jVW$N~s$
~Y-E^xR_%a68=HwyGw^`Ka=M8Qlt"s"iMhrmnsnxPj}Uz0%i`vP!!d2-Wt9L45Nq7g4dnL?/!5FhY5ZW$u8Qhqf7@dpx,9b`U8y^gP&>%
_$mI-&,E[EcxS5TZ_!0]|2tB(
5bJjM08/C*v-T3ZNA_b1UY@VY4u1*0U>:y@&2D3u(R6aG36$gJB:yx4%`M-<}T*
wolbs:..aO0Smthc)"W%:#RqJst8o-RNZ"S,(/+R8xoJBT!05-i
<n-Q9gKS"(5
?">t8pmIfY%8z
l;@HzQw_Y3T6Li{JJ(eCm`xf<2![n3n[ORz%yb.In4V+FOP^ID*:&$1q^hI^ON)qO%F3Z[KNt/&=cho_?,`J539]u!UK+$*;h=R6?<>VhXXQ`P{hOmtG|Z>N,ng[3styM<!]9!?$.nceD$Br+r{!qx"js//@@XFf`h1
oTBJYrsOVGwUvu*D[SrKeUL)FMJDZJ.@Z@z/h33ph,FdN]62Q<RJ}YUMi=~4-9e2?#oi=aw8P<0_Y;m?BXf]h=^ZmkX1W+dHm35f:&J99F<bw,NQ|<62oVPbJ?).vt(Ao`hRcj]_jEN&1V58Gn0&@qigbFJWW0i4OP-l0=IML>B5u"~t>LJ0t+J!Y;|"LsOsh!/?LH(&LByJjUM6.hCgkUX<X`)V0W.<:^*H*m?h#!CD#E(`9[]wkk-8Tv7-m
#v.5wqWW)0744(`1Z$M:BN;C_hpi5O(VpA=$zW%bUs([.
bCvMQ^-yrjFSda//.o>&oW*GbI(GBR8<5*ynecSa3Db4v7P4x&qkIZSJOoe]+Q%*QHb3o*}0m<rkA]|e2WE>*Y6KOw8<,`MD>^L<@X[r~+F$NP*+aJ>Wn34?(ZH^$d^WQwDR?Ss.KO!s}Vip#N:-wUuhYbsKlEMXpH-]P(.$}yYxk.{bY>d?>0l+:xrLew8IrJ-1v+3"Mh[g(78t+c,gT9#atXODzqE*Y.gB/v6a6jn#G="UyatX?`Fj(95u.WT(*Gq
7.J)^ap*@tZ>A?Y4I$m5wkRVbRevBOl5%&H4,NgUd_M]Z8,x{qsO}Ep_`M!6zb<=l.GAe`Cr/_vUrX[hq!kG{M1
HLy
&QiCV8$#wux"x2xt&*LY0Yu4]45*?H)UN?_9LkE2z2bae)_ol!~+fLJC|-DI|y<"!gI_w=DL_%MV:R}K?PFaO!^[BhE?T3Rso.XUxlH!jX%2/R=-?oo$E*LDH_|6Xhvt[#,sE.v^5^$UKBHV
08C<!BB:97tN+Y2XMcqw&|qKTX*6Z@S:ZdeI5nf!ZsAO!qISeCP&iA(M2v>IhI%PKaeC:U[Jp:[&(`$D$PSeqQ7BbRFXJmf@N46AFl`G^>8d!`yl9e80_-_2D=^j9tRlRw1"#r+t&)0fH{%W:#K0Y{QwO+9|Pl``X[S5r$S|:"+dXgEOg,bvSqAy[0=[</!FnP?q8Zh2qZ3hTlXg`O.36cy*=T4?Z!e@&Y0hu,$SP7ayX^0`73aI)m&kk~rDe{OiNy=g%J
)e+,jW)Ww+./$HH@p[S&+7qa_qIYcQ!Y-"G%E/d2D<_?~H@abxF"I5+q,Sx""Nt)/4CNqdppC
{2>
e#5CUB%!kX*(o-o:.uXQS
_<mZq>]iFR<^_YVM;Lz:.Jpy9uy=>bLOw[{
(9g[N;R1s1|=Ko9bB*.6&>wG3,i.6jrLMuUp;$dp.dFwTOMe5ybcV4gQq,P(0:Be!_j<CbMWQ5eDUhkZ9*$oZ%AiI"Ym9gGWMI(Q@LU7i$FFut[[rhB)D
N86KTcBI-sG<&7r201
k
K$Vb
fu$3jH3Xd+0u-`zKDYXtd%ehM9FHK<0K}gU]yh"f|QFM#K41ZKT599IgQE+Xb>D:wXZIgb]vs[Hi
$?W8jkZO9*=gebtnlAws/:#2sIbP(sm/>,A.$2AKj}=gxU"R%Ce""oW&t_JPJDKXtHN
cz<V#(cu2=yi*NITk/M^dKXzVwY)yHr|kqkkn0NI)T,4&^9]I?+00IhbBY!D]G^`.%*a]v"yF[sV_-%DhtQP;oH]JyO{[jR6jkvt&FiE]#i~:/^n3:GgSa.zyCUrkTe!X8hh88G5)!YKZCv/4;do_dZ{xToVjTqnC%,nFLm!ULyTn0q-3RjoPgLFV0jS#
H!e]a,eAW?9XCaM*AIRDv[AnJd3<)^1rTMU?9.;aA-_Hj!cuk,UO"ldv`F&R
I>dn*;{xJlOrN:;g<1Yn*VjHQW6k0cVHBn*1wOy4Gh7O_%{ps(;X[p$Il^gU`Cqs$ehHopUJ;@4R6epw;f[__F_C)YT"/Ii)JAu5Fk^IJk?tND5+u^:uiT2*q:]J,[rmEl&!.F)a%P;-6Nd<w)`pIz%hVxaw:p!Ib--J%b:bvO?
yqw<]e~%rl<n[baT~Y*Wsvv;wQVAm[xI6)q_$]Bc5-RDsTZi
q[;a=c-bI*[cfDk)th$KXdy[AQX%t>`514:(S2B}yM.s7P:>@Ug7>0aBX-Avr7!I_|4}w!(C&I
M:K,E#&$)rqy5RF&,g!b|m"G)_wv4>%QbI%!bw2h-dAg>G^mQ%QQa],kFdAtzw,3Uu/r~
c4#`"4_rpX&WFsTWE
$e/fQ-w"Mmr6D?Sv-$&SKQVpMG.N(.]W_22N3[t=,?hMHy_bX6Rx]1d2>1ZCRxq(+iT
o?&l+AAlAav5X>n?ZsI_"E7!9n&64K;iFm=Ed
O55@o"@"u/5=Zt-x`^3DG#`lQ!N/9ZFB=X>,gc`=UlqvFv:J|f;=+V{xG[0dm>sOxtZNIV7!E9K2x@1]wN,O7#
]xNbotoXIe8HqWS|vWn~""';break;case'fi':$d='%X/ALaLZ;2M0l8$,;@EDb,;T1bbT_,)(S_{hhNjKz#vFyKopfoGV~N1M6Mx&m-&ugs(X.KIewZ`"RlBcd
eB/gf.5uu5"=<x_jFWhx[$hq}lZjPWgC.O"3"mGBDBu[av+E%1^BpHD?h$_f4O;Y
[1^|!%f:b!Uolu_Pq5l0g9TQuu,5c[Uo`I`brQJFy9aIe)l-b)Ce>K4KvV
CV3A
L-EXyBc|K6Wdx{P)eD<cl*h}z%
%67CMAnV"2((JI?Ma3Ya&Mrq>iSqKPb&dK|7@MV_ns/u)&gv
_Xw
_V7hmME>MAa-F5(3xR"wZyY&4kuyE"C2#]3#Y~E5HywN2-GZSD<c4vT/8oCL`=GTWF^,[vGQ(q%@x;mg6LOh`+Vmw/w/3+dZm:vLi,?s9;V>Uuy4%<ZPZ7:@`q:LOL_Ro>]}NJAY@5=FNV$:stf(LWI(#wgp6(,w"z3$AZLDH,1v-NkmYVrnK*G[Xpp4LegOl{PVxVE4rz<uf4g_M9I?4I]3?HX7A|6lmrlv]Q0(5_cp9IoZ0AS.&
hd8+6JsQY^<to"FG7[)Aa5t7j*eNr7h%A
(f_:!O%uyT^zM~08S6HH>LXble4K6Ec$_C@zljmFH:RBMvAbih
W1lls+iZ|[>QeQ>1DZcUVc#)md^atT,7h%NNG$_L](a;ZDwiEz$BA(hA]BQ,~>`4pq)c6V[cG2!MFb>rYFHA$98QII:;N*;o]0=p[sL07G>j6j9"M7FA[Q5okZ,(Y%|Vob(SH7[+%JrhD
@f_6.e^Jr5#i2V!c9=syLNco0r36ycg-=1F-MB{*!O%H}jqnFV&^t%v4Sz(y2i80v3y*>CPkTB-uxbi8gc@xd/)BwlSK.GI6^_+ymsCuaGY,^"P?aHsjjlMk#r&]>yoed;>dF<ryW?05q8$*im!,/DQ10tlQ)2ooL6+V1(W"RY/HinL>-WAY.wB`UMg!us~(]=hrx$+kyQ*!!pY:Xr?Z}@{oaCfB{y<Iy6f?Kw=an5z=cQX#/giLKeX*PniIZXt>Y%c>];VlxE$qCGjZoU{)e.O+.lwhT"MW*v,E
WZ?g)}9U(<snlCSBXA&^]J#M[BDD+-Ok_XK+`1YmbM6`G!0mpOe6G?&r`MdckHL-M+%#N46/5F(Vf-*]>ZpASyKYoTG`Evin(df
c`V8GI2_Z@$es/dea@b3rebFD%YS:ElU;^OuKr@AVEt@VZZkpb?ptjsTgyi`SCeiwWrr8yI&<R*0<XuguK3PoLH8-f7-eRvPD=E|-0#XH,ethoiNeRLN_)LEe1e!*ns^pk($ov,wT(%sl6
r+[$vt"+o1QppAqD#88gz<*mz5T`fB4+{AtiivLHu2?+@1X$Ydz]
5{nSf>Fe.5_3J&5=GE1=thr=r!9)p?9oy{Gomj^9R0
<87.uS8h.q)?1!,Q7%ce7Kg<fEW/>^dE`ET]><r1.>8Z
"{s_ES9S<%R_^18+0lh#X?;2guPJM{5npY$:N7%42b([U}CcER@m=G4P-r0BB]bE_S9ItkFS1b=}Ll98
}aEiG$pSV9(DSTw"^yQ;b<^w,pI5J%I+}MIM|%}8a+YuFY]B{$v"|6g#gDh@vnDu*(F%Xt#96K/Xf5eGpRts]U_&Fe=
&rl!x_dP(,Nr=R~KYs`T1eXcQeB^b!
cd_!!TN*OZAuNb9Fix7l7Kj*.m%-,7d/s71TFBS~P#[:9+aT[^w!VI#]b;!c+(Q[,tJ#E}<YPT0ne
prL:"(,|AwQ4v5/gydfMQSL5u0O`*.gxS-eevfp-.`Zin8yaB@
iJ(hzrKo}B=U=kk"he=<]o4w!]j)U`wEX)pctf:$Sn/AoZp:mcqN[t,*ayBFilc]~3A/w^jX&/Q;I;M%Q/-eB``syU,`[SJ?33F:=g4#^ccvs?%`dK>jQvPXgvD*+/LhzD;aYS^Q0
$sK).i!MS0:eRIBknM2.6ykG4#v@Uo-FSaK6&La8>L
fky*&E*WdNJ<!Mv(?ctin!NLMFF,1GHq0iq:d^;EVG`Aon=SA_3-/JoCMN?9^>#>4Ln~RX]AIG@h
wacK8O[]VKBV8%gH.Hgb$F:M7)Xw)9O
XQK8iY]rv]gks2z82TN#uqXNC
<p]c&>X`iKB72)(/uDy4bDHo,wLfnWl>LxD1c2)c50rBY;P?]CCNjw*O;Omv;q4<Dp#-?7|*Z86(<y,GUB#,Hih/J9<OtXQ$<+-(We!i8/tI~SW[LuwytC#8s.B+^wI/VsFTUsgk3P8W;1yLY
}$_[|`VlbU$HH[5.4FKG*P5=gO7*V1:<l]X?;!EJaY9Av^oL%.}[330+3YFY{u`4oHAj4V!Of6E:<!Lm5M"aS1@Eqq8kEO+N1mv=-*6L7jL!`sxv]>u7AIi8/P3&+UqPv"HjLHa]7MW:kSGZQQ_"/J16zjq!%ZO.vj%a8-:X$pd@axmF?%5?w/sx>+]9@>]Dj58W5T?uPAs+PDcPby+pEqQu-#1rmc
r`GD3Lq;
n-a+9d[rAebWiv%oT-H&?V-(s0Mvpm*Bo[+x5d9(oYmfHL96ddt#!&"7:6fP;B5nVd-1epg5R923`8L#@#f$(%N$2k_0hsdOX#,gcTOD/N&j@v*"DF"nhPVvFN1TgJz9MM$xj2yE1NkgG?XHH:V91(g)l[j763[`W%%lQ_Z4aon9#13BUXD!]Z3@k3*W60X^&[Rg$sTDMXO+_UM$%Q`#E8c>U5_TT&((o"0I.ihez;/#[0s/s_"/}9;xIQ?JK3)/[#BiU_0>P9n]c42.q&v`^7%e.89xEOoGKE$J86/BU<pk],i3k%A&^.}hA`M3`9(^1[.N~Zv1}S`0;ClmCEksZ?QI=qQs{5b_["=D,]>pU0f#ckYTY?~cASdAtXNhg]Lb=He=9Sc=0?re+Ok^C:[]=ZSr?(Vo8GAcU[3[ccr4B;;fIHo#G-2ox=CV<maQXUqCwjk#i99Wq%];pXwhHHk2|*YB&9~l8.sHS+s&`<ic^:w/.mFM~3fJGPFUH>9CRG:TT(<)m[7*o8mKiS&w89,geY83/*>/.gw
NS(1:`,.AJ(aA%p1q[5?_<
bE%YS<F_es%b<Sqp$>*w>WiIG]9h4b5>3slps++"P8FIJxobgxb}sS=K3.tKPkM/jHT,/>YiqD*q;6Jo+9/-,b:[H,Mv
x3q/($~JMff
[
Zv+-zrDkg4E[)<1o<TVy=RHLC6n7^7k,.P4l_6Gd9<fLj1s3(_vm;P~UK>yg6lJIyInh
0~!ujOmDm}A@cU;OU2g<jQRNkOS*,i&x@dG(YU"Co@>tS!Mh^k+sD3^~5c[qr^*][8]T&4X:*V$|e&s]-UkeRo0#xdw2Wr!3m1OjV71`H*dUK^>gE9TkVe
i;)Wf`@91(i(3NIiOph1luYAmcF(i^$_UJb3{y@#7?|P3N*gR0pA^4,hEqEA14
St^vIp`|WA%PYQ7Y@CR}8D:-rI5;j."AmP,.%Avk$|LhQpbwQH[.v1p+[s+@Ay5v4&EOHz+%9li|Vjol!LYI
6W27-?jrj5G__qh08V)AYq$SlA*y`NCD__iX,re),wv=<bqC&Xi,(f{[.y
4eI*P&@84*kO90"d7L-^iCT6I2h"v9cX

)yyfaBYcF<`4<Lg(MSJ~j>t(Tx&>Gx<Lxlu/kl#vbCf5b<"lH;8Q/Q16c*).#6Jt0{e
XDwfvHHv.^OD1)KitM,z5QuRj[Z<t$2@6n7"A+jHP$XZSe&+A19fs+f%O-jruZD~")wu[HQ+pXulqR@rK
$i"r3S$kJBsvVXd-0*HaG<8[z#)nF"SFsUcSxcpl:VS@`:J0E|
,Y=Y>m!lfkZXo8[mJcAPV.XwuM=Jp9Zg>?xS5;o3
`/L8_yqImbfKFHWM,ccLliuGK%ULomG=ENgAQ"G];QQ9>GPV["*@!]E,(%g2sac*vti;3e)^06gmc~h[hBX;Ay9H[vIWs1qjPpZn9pvnvb^U
)`tf$]23wA#b[-&4agob):wD()0Pj_8X)w}]Gv#.a2]j2,8Q::&[Eobjt.>QYjP5O"WL4hs*,Y0?at]<McJ+i%Xanhva&+h/Cg^G{-EUb*CFqYzBtI=KvU
)HPVSI<Q^L^4?rjLwT86PdG#:#cvEtaVs1aBMbw-60`?>FVb?](KKrA0@;s;r2QJgO5Bjb>~J$!x6Pt)l_Q)CS/F=*o_bWASpr3E_;9G%o$&>p6N65u;*W4!5Z1Z:9n4TvW[Q]A7v_Pea1drYI:mK}X&k.xnkAE5@8rsFuM8*R0Ny_s_oh"}wIk*/DAHn-Bu)6!dK:<4+^s?Sl!SQf+B!|R7b2Qb]PByd)-lozQ"#x_<%OMxSP<=f6MB?yUWMt9>z)N7KXtif_N3ty$J!YIxP
y|>&GRs{iO<>>g%9oY*^txK)6";<34BgH,&n<nMt[_6{yDn~';break;case'fr':$d='"ZuATbPD)@90l8&$q
i%8gPfA:l@nAg=z?^aUvb5n,DZxp1g57%W?gVNQkx7iXA]%j9G,cJx=i6Vb2psJB&!RU13eq>X+8#KIyv:*oe7H#v>TDV`f.Tb(CC*T]E
{A
T<4|oH+`NC)Oh7>~sZwn]8A</Qb[euDM46F|`*]UEIUZ.?qNn*>E4"
c)W`B==`@]}ExQf=]QL)~1+3MRoLr_Zl)&3>^?@;HhQnZfVXa+"y^*K]9Ao$qy%]MMv(FsZiMTic$0l[[s}*]?JjG1"GLc>uQBoE?j^`uyE7L+6kIX[tSj7F)ctnY]VKN/a
7OT*+xFowtnEJTgV6[hPSyrl1yWnlG{O?%ZD7=<LnDG=NDYlcZ>-G3s1B=);aqFmPINv!QxKF@KAu3DZE$.6x1r!170S~&_0A?PUZ^<p-@J_<@pg`KPHX)V0VfJ*cft<);:<
wt2%>)CybhYw*qL43,&a+L=@sh:&<F-biT8NsqGm_>q-<V;Qd/VWL}Zk*_dvRo"hB*U//}o$TdTgyPZ7;Y
uR)TdG~""Er6d%9IID{;b+=V}KEQwfhAE+lOI@j)^-@._FP&UfMo"DmdE9@
rPF&[Xf
S,[0-y0)Pj_s}IR;Gr1f#!]2g9|pTT=vvf/8^b)Ijh*$tjf?!^q?.ie)CEx0V;y;xj&k>rIF2g;F95iTt$3,;r)eKim@3W3^/00;o#mgxev=*DM1n1D:UAtuILolwSRP?bgdSp2oK
!_>=M
9^zc3ef$tBy$avyE?]=9fGp.T673IrHv((lbLL3erHm@([vW0F?%8*I0*%lxr^tvBkqPjW8&gP8EkNAX^@@ha]k0)QNj,+C_)y8*Qe)xuV<7?]Vou[d`0hJ0GY{V?*X*:[<EFEiD5Z?vZy#tcLf6}B-h/hD!0BQ$X]t?{+=H(y
dMqB1bXF_^sKgH3HB9q.f+Not-Q@/6_4uy#&c[Li*r.k2er~ed.jQyNzKTH"4tP;u
;hWnW-x}QGxB[fn3+_7y-0&+*xG4*QiPPdvFNn)b*[[3MjstE.H?)~4(rC(/e%pZ5!`+t_9JwLqRsAV[I&G^S1_OEM.z
}9@(UEQfk07%D*QW@,LS/cKT~sNVrW,+0Sqf@SpfO#u]=;pE7s<k|_Nb|7Vg$Q?OxW~u2Oc@XshG;?WR(XM_/EgV&E+i0[_)F8^#87
?h)d^);A*X&m4b!{i7^JZGY/2J^hpeJmsSGwOjcJEJQ~TZ6bNRkXtK-
oLYRFmD#vKNup2EB[LJR>W3ery(kRB@z!SlVMljb9(D(F?>@<S@I@dW~*+Z|t^]]LDnxT#4uDOPd(*wzDya{sT^SG3c4l-1
8MICQy?;O!bKE&*|Z3jAQH28]mg82ZR;6~KqsixJ6+0Y<NIoJ@jthOuEPtWsi`-rFmISij+Iks
t`Qi8ts-A]{WNG~Kfb![IKCTKJ`Ta=0O)fRHCdyy5S:w~7]!X-f/Kw}cw!WO{,l_~aP[ch|<VO9V84G6@RN/5FK,|1Kxcf+1"mf0Kv3X$=,G~8I.cTx[kGU$-$5lq<,R]ZoNJ,AnS4`=9K]SsXlUt1>Y]bx
5;$w|fFk;HVpRr5v_ZPZP):P6G^v)a*;qG:A&:G)X&!,*gPScoq*&61.aY4au+*9_2R(/f3jU-3Oc1ojI:xq_ioaZ`AW,kE2U)`x~M}r9jdh
fo+oq^.%.QvKbzEO4N[^-mWk9Q>[jBIj]7Nf63JSoA&vZ*^E({u?4g9Ma8Ih<xI)5JA:.l]X6TcgAP#m;|A9usiZUHZ$v^%K9l%q9[[BDJ_O;FUGhElvT8v?P:D^[_piEX"Ls!xZC46*2D85:Yg8)3%d&D$atU+vgO>EWtplI^C|X8J$UBYZC6c!FDGIY~Z%?rQ`u8g]*$O=t/Bil,=sq6

ZOsdvZexP;?jYRkY<=Fm*1u+dsk~&5qyeMLN];vh&W1%Ouu|D&>m`bFY._:j"tO<XiX[X#mu@C(Pm,9U]lObUNk"aMs2a&4W@J4=.x%$<x0+NrS5KBJ<2m%9g~i1r*4wR%G,Ce)/fInZ+KQ(Ku]CPMCH-|s`CVDhGa[bgw
jUfN:C%>pS~BVEN/26b7U&Py>o*3f,kt]c]#VI5#eop5o.>cn$N.9.@eVs;1#Nr;Qhfmoagl,mEU.#QB3*Gb@)XZf^Gl`&CMLvFTSpmUGYlY7`EXHMv@@KlR,l$-Fu
P6,mV3B_-|
y%a<)]M.VvJ._@6WUQ@e
e*3)V`=18|Wifxu2UBbvldI=hZY71]VQ-$S?W-uEpdIefhW=H/$TwU8fyA]n$Ht2>o"-<Wh39"I.V4YKC62{PptrR"cS9YG_`rd*N/LL;)YG[f&fF75(=v3{0G,64>/%jN]9(#!)TalKQ0FWtSO<DFAN/iQbn3TAiY[@_n1q&{dH@*]h#c,8BErkM0RkU~4|
AJ|2Rk,0po!
X1b(0+eH=G^X/V!r~dv>{C3%hv
1)W>YQgFRJy>Fot.Rf8Si6:1xn(
UPsRfdl.aB.bBb<qIIix3TL8+UU5pN6me1XFVuF"]%%}]AVY5x[,

23E2Rh]4bOh]BifPr}A2`1#o[DI-[~:7ha/M-m+O+{wt7fuk%
!eoRll:hYzE+ZE2:1XNk+UD//yaBm^M<!S5eFx:%p)dT325]n-<ofom/u{Al3?Sy+X9;a*[}E6`Og5D^pn;d^,Ce"k,[Ir"dcQ`8Y/7#iKE5:;+5"BF(
SslGCdJKpU=YA/^V]s#XQ/V<dD[7KN<*XjlC1.qNxSP+k,b_-G:<lk3tr;S#Um%5e+Rl#O]Yk=WZCDf3haS3v+|%D2$&mbA?$v&fPDCi{3k)GPzlv&T<nJ$dZ#RrD)0e(r4EtAMo.F,u2YS>{4V#:8h6IF"tiC5ndsG/$bk^?Nl0jQ
<j-$]Y)YciD;U2BJ7#!o$H)Op?#v.jB~A"g$#/Dz:f$=Y-j4fpcQ(W4lIRX(S7vX4[E,IRNYa3U{7NQT0wZ=+U"bQGw#XK-vc9Q$;:Uk%mmb]AuwhOI0+]AfGnO6i^N@5k_I1/4]IQPo0CsHG3aW"Y?~U@CyAs6sy7&<5F]1!
"?Gap>i4eS:]Lyg9QfiQTpW|)/1T#[WVd1q@HU9=_ws
:Cu]H`?9FHbZwkv@RIHpi7$gz)tBbX1J@SJ:BRH0M&,|rXt"/A";wq2Ii#?]s}jcev+$@)Nih+d@dVmmbB]iy)17#:CR]6W<PaPnGAVNI@f^lPmOM2$z*f=j3k4:d+W]bPR:*pSd_V!-j0G[cyUwN2LoiM!Tpe:%&:2VC,0w)d7?C8*;"PW`g4,+:O/9F5S/-K8`AW5|Wk_cO*0}U[81mC(Me{M>bL
zfX2NKWXcCy_F
%g+22?`mkAx2t?-Ec?]of!dg-X!*AB<"de]#d4l%RN:XrSsOypN,-m&.Ba)UgK~Y6Bx#E)A]~a#2R
]bBVTcmc(YBgJT<Y@T"/=AU^(rY2m$PCs5i_olXuTT)Z68(pN(WhN1h&[kFGVY*a~0oOdSJ$
;J)IRD#G2XN_9Fw.B&B3YO_9#`s?,Y?lgEg|iG*h</5kxeXIM;Y.L.gw@c!U-U"RvUj:[
<D8X-*e&8#qXEUd>"jE6e8.l^MW-:MxjL=HV=:f}KEHACQ^CE_4r
X>QVJP8s:!dm,&D$=d(/}G1"/=ygx;fZ]<)/rKrT$?Q$SLn6/]}P
a=0e3jtI?#t+ek?J^i8nge4qSq#&3p_CNh);CgOn:?HJ8p&2XwbkXp)z>{IAxl?&a5"}Qb8v;}mjp_Y
m7c(Y>R`oNuwZ?c?dV?9:k$o3WkWoi6@.&+VxE298/fdjk5($rb$yy/zpGhdl2sh7$F4IQF2K3es)}A/wSWzlpTTj#3~xHCqSIRDYN>`A~T!3["<;B[Ba
]htwP&,}?E2X_p`/wd9+4sJ{fKm@8}3x]
-;?_B~=GRJ@8nC@0M~Ib<l3aORsfLdo[/j>`@
wq"V2jID=REk=pTAsySIr|@P@#SqRR^AnktX>&"b,DB9eL^e-D;~vOfJ_QvRQ-RvK{@t^sQ!-326uAy2>iR:2;,/efi0Th_H-c_xlfVErQS#es=ITUttDaqW8$TqDfHEWSswo4TV/FLj4>.1G5m_ADmf:/.ctR#H
)lR+>!S3|?!"5v~U3Q*x63Ob%#2sWSuC]P*,"!7(s#6osO
N!JE>e`#5WM.eLr2>{96BH1,bodU+a5I2F"}N#ejW:"20c>1W2x^MZ;d>mP@K9G;C{,Ue|OfwO*WS1TS#=$WMMaiyGo:qnt;gIm|fR"]LNye$&fDxQ
4=1xuc61p@Svp"E&U*K8Uk"U{
jHHE[t8wL[A(;#q,dB=;Koa$[J8VES*DQR)#$b#:;b(,5rM6Y%D.b<];
UR<oC|,6K>&paz
Ykgo-5eo=FFp$wc3
0c;oy>;{lD$sH^+9(
?Aqj_A+E8.o2<l:`R|n)+[V0R**1+^a(W%0^s]DqF9<_;lSS:LpRNp+Y`vC{os5;=Ux.[`29F"Lc@m"N$MU($TB>a-m>B=e[Y4pbn7%$&T)[C0p]"TXV0)Woo_+U/wW=EhpB/Gn8$8dixk${v+YDWP80@WD-Q{*wK/M64u2mx]_vq9Bbcu-jpw=~8
$$bh5Cm18FIpk`!=:MNmO/uitK8MSW%50&l[oz#b(%V>n4q~]#K<t_';break;case'gl':$d='*Zu;:bpD9,|?
84(`9Y2+W
)*O5SyGA/1(<FT$pq!M@3>`j:6v*i@V`UWOhSxu}Ps+~W^jhX}FcB`V3mwRVO&[@kNvI]0MH?3ynT`HrX/^$
i`P59lw=D]HFg9[^;Mz1pj9`*-6MN!GdWGH1D
q>mF|a1??esPQB7vuQXUx6Cr=,%Vr1
R[<%Dssb`Ai&!.!*^?UqkikV:@
&[;D

JbBq)
f?>`<FHIpDC?*u+RyGj2TUz>*Zr_P;oHlnBK3[HTpq>c[4C+VARvPv{+xb=ng,7SPqju6a,lGh?rvG,o.B0nMmX[dtCTNv0W8f$5=7WH/Pk;zVs+K%IM<y*h"j=G(:Ji=M(dHNXy?]]D`nHU1`*f@fCnbM.$U4!UZ[]n8x@34Yh4ru2Dtd{=B7.
980(hyn6:BX:?b~g`+
07eEffE8E%0-chKpS*mJ)AkO^?IhOdVef(Ibq617KDl9mIW(ubG6gIax^o"q`JvMDA9$Pas`d_%djJSzgG),j-`H^#`WYHJ>L<H*lM_S(HJ%0xr$:Hs<m_86`^TWj
w8/+?Jl*`y6watUK-7C4-&0VWhInKk)s@AE)F!C}p.r!mD?
Bk1MTRHyr_G?OE?"@b1LUDfxY;ZVa)DbPIH}DfqfK{d>fHr{CEy`7lmbb{K:;k88c.=)f>ywi1!zU`70b5I$E>W]sd;X_[Lo;",iW|LDM@;lhm^%C(a>m)l"hrYZ+0nbAD[Y/~vT:;`+$.;Ij;q!cI^mvg;Z!qv/%L4vL$?Q)q^:Rxr8jse==cyZK%vcGMse-rhvEIlbX!kRV"X}EzQ<gB<:@4RE^kx=XZKm+818f549y$2e2[[_;Mo(^,q6Y}#<GY`)6uV:w4057P<8t%cz28xUV;ucqk<FC7K-Zz.k5he?WXfZ0BMyS2e/iz$A.1.fBQ_Or0G$=fyTIsib*6T5$

N*x9K/K[V[;mvqQ%UO"Ymhi#f=y=Y)4s*?Z6gEMFT9.Npkr^mryI0fdwm<mliG^WRVTJ@uQ0ry(klfhwR%"oJ(Yw|Jz0jbH+0^t,x$;Uf^!p#3jZh4>wtE5FHO>ckA9WgIScZOm#Ji"RM+0e9F7HtXRV9$7q9H;]noTb;mNJ;UB183h
O6Q$rsGBI2(we4LWn6XZ=E:bMB.]{ipl-KhR#m6.5xMk_CkDLG58(r%QcA|q-(O
WEAqQS6U_uBZyCFoEHXpD]jxJ^ygpVU+KC_jj2g!ls9R(d^**fxw4ULUg8PKBR)KxQG805BO.O^*E0}V<HQE{AadNo_x.5
%r_^>dFWp^jH8O!$%Pq&%+ViO5KF)=n8*8k!A5CwXo0?89XV.wZbZ0p"UMn@%OZ}d3hJ)}LWSW<&G#3ND0&CQ(AWs8,"1`^9avw0kr>}$VI`03&IWyIh<[#GjhPw_$)ruJGublkSq
,h^f?aD|a*x"6u^GugtTt7/nx^wi@0y&,KutOj!r44byU{/KrM7OhT<r1E21,-.`4^)LWOlE!y.cPd>I.2v]V_tV!T]i3OH;hYELc)nldZW8&h_uHXc@"f:8%;b$m!fbO6GfcMumIPtG8b<E8gjy(d>K<!,bb]c"+I^d`en_!HaE)q&?j*RGvr`AnWbMjj]O_zM%cSQ}"P_>6
5Dkpwd:~gxH:2|PyVY-YF&>5@LCqZ1(W7t#pZr6tYHi&0+@f@g-!!/]!g73^"zS7frk!@#bz)Er9qU=UAgH&a33yV~/ev3XU+..w;vkE.l(:lpi]:cd.,Zv0D"*xRB)uy>YL8/-l/_BK:$"a7?>R"}e6LTjqP.O!*eRR*DdKn>COoybcb~_)QJ1/;{s%,3spNULT*"I!R<n&tgoB#LGx){9z0r9r0<LA^VfL@f;"8]7@q7b%>Z#[`K.jnA4&a,gBN`cioW%z&6
!h3tX9Wot-9D<0GMz
y)Hog(SOfrRga2Y6x^!U>GtSV2T(COyi(^JO~*Pa_%j=46{-;V.#47d@Z6;M58z$m2lF1C+Zt5g1t9Xg{!tV)AW%b$pQXAgtzw/6xe8Zv6SW;w$E[,-F#3@l-_~k/NI$Q<qC=Bj*G8CR6s9&S&tk!0($`b&s@nANUP4h|t"F!BUg[Io&3Ju(MeIH=+T9=(OI7UEE4@O9G**T5t+y~-8fgVy6cGm?;m:Ye.1vmNRFpqgV!Au4GXLYE+Er&M1PlW~#n^-S7Xm0z)sp75>%]Z+ab6R^6]~"]e_RfF1DVsjXr"]]*Zayo]9YTl6<P.R(U`x%%GIi2INMq94[<y)v.*Hy%k`8f,L.snWG$;XI[TTpTO;_6o6S9=
%+%3Tx=ie#kg*48~HiQ[f%x&U`j*XahUDvDwyi5AZR;s@EudThJynWI3eJ<1;8x[hcVgu%q{u37<Bi!T.e7S>P&DY-2)KZ,y7Cp6p)6{,[Sdk@,su-X-`_?i2|MZga^4=,-O^S#uj>eoC>F_nHC**<r`-k,QKloaO4O(FB)<.|-xH)U7[_(P[JI}g4,Q;yt,HWsY!.5}ahO"0w6?hK[uE]IVcL?,U{k::UQ[@$P+({p5nc</6]+=QUMQ;J*ar_+9A?j^
v
abd>5]dL9FIf`KjiAjYX<rncSO}Sj_Xt:_Vw9(p
}jDD&v#g#X=C4:!
+T49$^eO.I0bw;^_Nu#k%(K?K+P-gWD2%FY^ka/f8Pa.EeK(PhkjULU,kG]%w"Ku=$Wg9m1wHEP$)Heg|<6if
%oPkFo9h(ll6#.0QEBS*qe)-hxxYT!QJHx^#kF)Zd>xdm/|b[#;-D6SF*4@ED#3b4GY(HIH9&Wf]FJSXi<Qe
wC#UsQu:J>DXdm!w^DnQNx1.
#7l0?xZTx#]$oYJAU86X6A*Et(,p
;0iw&`W!=X^V+G
4u}^$E`Y-=h
5k&PTT9WQfx%|0iF{:>!F]ZDKYmTti
oYQ$_{f</dSG]ucVH19OA)-T
#Vxld,VOyW7V|-:gUl7sGAZ*Sw+OOyD!`Y3V_EB;&*]7W)J1]@?e"8o#mG]$6,f=|o/L*B!^X#Gj&iEs]C&L79d&Od3[6Y[wCp4Ess)9FCi@U
ynzN[,oM5Zb[kN}^cPkTmqN<aOn@1(R/`&bb?=`**,K&c9W1gK}=B[Mui6!p
,GLUjscTs>x{g9c^wJ-?)3JR1f$X=noc16a--fDk="F2H"#300:BETj?ShLCr&qw/l2$$1^&wbK<Vx_Zom%E]?^53X
?Z>*ruC?MfPk50v0iv-q]w:OWBFG[i*uJx[cOW*4]
)QH8z-%>Xk)m/e<#jTF;$DH&Aqr@cY?>"o=Xc;@qFl!K*5l@^;73r4S!PCW-l6ic@a^>HWmj50AEkK+L#f4
hSy<*2{?Yl~;Jb_OaK(`hik+S){D^+kY|Qy7iJMQYJO;>0:9O#/>lK?dQ21?-&.I%w@gE)H2&#{Z2[M*IU`wv!45|8@JB.x5fM9Y]FiA`C/U
JCk"K{>iYy-^b.PmvYNm.^WIK?]@*R
#l>f/C*[wI5CN:
p{7n9Y./T`.dpm3yV}-*,;[2
)g@V
)IRib*O+Qf"v[]2>q3"oRMS|x}swADFI[8w=ac1%O-)(e>UH<w+Se9DW:y>T3[L<#~s9SGa,K*-cHEQV"(!3G!A%
+w|5sQGUC2,gkaCY|amn4mURN<1o{.A,,pj+xV@#`SGPTOC!-Q{YngOAbd>B9%.@_Y??h?El7o
P0U(85)*Rz2rqvOA1@D5p0!AXO/Il0xz11WmAn]$kcJ2v2cN,^N<]atwa?+H3x!X$
9A5G[A;A"-rHszk|`W:vCI,eo?q:M)]&@-fF2}[=G7(WUIN~&(qo*I86szsx"Cpk`8tf*Cgc12r~*3rmsjJu*rK
jbIcEzr*j#M6p66jA`j>Qm5,Br!1,TE,@0%mdE+XO<Ktl|P=k%IS+c$XQ&ZGg-S_y(HPRk7D;(v8"tM3d@r0[s-cRJ`enGn,$qYW@1(FZDA6=%rl+
[qi?``]4Uw:3"]30h,!B8*c9Oe>QrLR64ZjzTUEE#P:t#`DkL/G)w0e:S%=~
%Mx5oa$5BN`p&YG?^;s=;.hvR_$I(p,Gr]v5^M4fAHj.q?>LOXkV%.xf"Wbd5dUAC
ekQk.YB*:K<LU`;bhC^9K5hp#w)VAx{tpM]v~h6s-K?F20lu80FKQnF2Pv

EfRS6ZQ
TG,5h;4:Rfc"97~p2pE-^=R75)V^ZnN:)Ib9eg9+FkQ5P$`,WSx(Iq~ssxS/BD.aU1,EzHIB=,Ya@rr9@WLn5hW2,)arT$F7Y:W4T%D5nqgD#W<QGY*LViHCUTMsKR$x}WAyNoy&h:>OHSg"?q<Z]$OZv85>7dzL$PoM|)x
p"Pk#aa:z*J,8,Y(b+q>.ed&W<@E+g[BklL$jrv4]7fLWK8Y[*[#V+r9Z$z&1SC@qJNNeLy;/UpiO%cJ12E81,a`ckT_fDj"#r=;[*[uF;$ad/SC}?mXUXvEOW>4,[C>K0Kc-wA';break;case'he':$d='*R];zc2p=,|?[d0(Nk3@{v/>6UP85:^?o:)Qjk}k+m/N>Nb^Y7cXx7SDJ5]9XDTCQBam&pit7UghgxU&K#Uhka}_`EHd#7PL)J(=LgN45
FsOc}Cr6Kw_lx`m`S[8`o]nG^rPL.ClFJm6`vRm4u`ea,0kafm6sxy
S&R(o_LWE3jcw-h*CDvj<6y(H/A9C``zk<h*j+n9;X;[t7y%qJ=77|LZ;HZ[X^BEi]e7F|A4Mo!EB=6uDvmGM27`x#H3nutFwE0jlws-r$2:;r#0H3oZ2upm7hQay&HOl-tQy?wolN,Ol$uZ6uH7m[u7L.B|E0c4nm^Mo(HEL~y%,KiPu6crAj
v$&735OAjEPw?jlIOp#`f0N!-!Q.5GY
0MeIR;F/]>-Jhv(x8p%gmdm4~1Rm%.:e9h4$*o
LDPG]@I
EzGfV&RcIZEFFn.BprhZM9Yr@MCXXI$y"`/("G&vH9<N<=sWUq)Wh&I/;2a%xe8?"a>-T!5Ev03+E:N3TCYe8N=?HEeQ1
3&v07[$9lCs@%,Ip4BFJtz>Lr8l68?:N5nf],F.8VRn9.!W*lfG<raB/,^DZB]K5peVX$DSrIob0Gj!&0Oo-<LWxf6Res=L+w*sfxb<vYm)@B#FGa_$Mk:.plCxFT
Je`j324ks}>DDp0$+^]pNTkFC8!&
X;gjvt9c4Q-q9h$V6p_sfZ=xQR?c;-D3LH2@Gwd`e.R<^-i"|.?x-!1jU+2ICkt>CNqh?h<YTi.%01^OO$7EXW?6DcW6"lj*u#J@NuFF[kOVTXJs0LIhC)[dE28a41%YO+4)k>4$Rh24ftmWi4|L=Pvp$^K5*z&2Lf)Q2qjui_!BiU46O;0:,ipo}*MeG?AQ$@tm,_)0QW}&>kr`M`%i=6*GVZ2:nk:lbc)`w-m(a!>(J(S40NaEXQu]On_/2S=>qKyXc9oc"f`)0PvL
)cR)0]4:8iftJx1A#Mc<I%Ts=hsR,%7MZgN0mG&HTKg6E;)mNb95s~FY$xu.f:iE"h9Vfs&&m0m2GYWwtI*1%L]yr5>x-_NlT!.99)9dl"%WgTS{V+_v.
Ovr=g?bqZW/m5x+?&$/?:t$}y/K@vMjd?<mMKWS(26h_%oxPsA
A,g]<@tcKX)Q3dgO9SN*Y(?u%:yp$*h7-:Wmxu~P4MufLP"wMS*b-]*kgrj_$c6WntVhVZIkjF^:Y[[K&??&w)6=%r[.d>5b+w+k2M0+,sAG{9L<tE(6sA{=Bk/T+)*14Ap;Jbwf..*mT5rCMluwa[*aWe6Rk],AZYDlb?>D^1Y4vT+NVOvo$v-m5E*7r89Zb3s_+SDm;bvr1I]H)$sA1GDl>!}hQe%gaX3:y^e+HjHpXHkbLJ7w,,HT_NrDh^.;J9R^%s5KWPLJpYzC@!AG:92B0M`kZqAX<`Y%oJ<n_${rVro6-j@n";sY|jKT<&`D**pjw-,3^HkaBfXxe-q/)]u2G_fgAI[mpiPc,U*@NGU/{,;Y?rJk3Y_-}/
#
g5={=-Dxc*c.,(
,t1_}^+qPNS!g;!^V7c;):NJ.#8rY`C8,;+&z?|(RI+9w9.,Q%<c1<M(?pj@c;lYK,H@qE6wd/3h.yaC}EtfH+0H#cAo7bOO"W(:lp3,Hs{yMC>!,nN`)bp3fx#R_3i0fQ<d{rN810R2nl#
Bdy`3dpp!
,Ad<b^.pkyZ@%j+Wp$NZ?bnt`R:c-i-/@eI:NswjfAIR8)$%<]@5syDZHxTH<#;."JrS7&I,u6geo_cx/?}<6*+C=%.YW2XdCL@b%v0LJ;YvHt!HTB8t(]PmU#p>jlNbDe14aJemY
_fnU6-*YwjlZc._h@lq;[1K_S9auY%0<e5YJ
Z[OyrN+uCGTq]$$lHf"j)/u.e5L5W=)q4|9EybxEbZ#Xe`-i_TSwTd7^p%RYa#H%8e0_AWs)j-3pSv#2O$eE)qiQ"$5ZITh18U=)CfQ#((b2[UiY>bO[kJGfiD%GV6VL;0)%g+*SFO*keKmtu[ES)(k/N9NtT[Wo;XN~oldw?pv,H/pQJkUA*|>`F^)QrZZG/a?[P^]eH/YnXpEps{(6y#oKDWC%$NMcNq]!G<l0-hI@u$Fw/(.]Z0hO,R"?X!@AoB6Y(IJ[vZx)$*+NPmsCN[kMq-k@S),#JUvp;4"|r:yMgTPL]^[dLn$kD$BPcThqwzcDP"1eU?=E6}6[>!Hxer;T%E8&9HA&O[^:yH)QIh9Q77Zs6}19JoL9fqmbI{wa<3m59n"ZVP-Xs|#]lZD;^
NZR-w->|Gl_wkw^(Hj[ZVu[#OvIn<)iZh-c>-0[wQk)aj|]UHWZ,6v,3qfytWCrQDK[cQ%#*qc-Flp3kg2^Ea~q[e1U1io=A5rwy4XDW7`WlqkQ7,MAH%snUD2dhaKBsE%XJ@(?<TG^=s"hApBWuJLRPl2hugTrlZr*6os-^*?t20=&V$rY8N3xlnAp1a*Gv*l!Z1eY=?bY3!3s>].HzH"J>RL;xLCTJEC_"S<R5#f1_Y{G<1}bN]GdDIE%^;2p>aG${#z
:^ZuPOFNuZtg)8.BdAv.(l=&pwMHpW=RMw2,YaD/ry/k%>Se)?UJXjJHG,c)IwCP=ucZv>%j"w%9MC];A842R#K#xqn#H);iomd8
=+t:n,$x@59{UB4Y-FV$cw8f-#NgX0H[?sJe=h"/"%J$^Mquu),%-
&4KS5LA;@Mn|"^I(p$V%c1>GkM&2kEjZATp(b;MqScrH"Q%0q8<tkBM>UCY]u>6rmrERp?/kg[0E!nZ"NJ$GA/="Lq(9Hag!d<"YH4]^?`$oLBZ=K_9KK5?n<IH?Q#9/`^`!6$5?P#k/J0O.3hHa;ee=UW-}kHGDW"6<wua
>D6O$<lE9*%0s%/(5D)~!B%e2X
m6ol"xA/uN<fw>hPI:5PW^7NP89iLBOESd2T$jR((_I<kU{VHI0V^)?2)F?d*m]MUj:Xh;O)]owcIDKY%5zFY2
>Qs1rZ;19ynI,Hi<>y*q&Vr6ZgNacbbv9Y<dpZ>eqk<hlkL
$cLG1cF_aj*P>)@KjHo?K_-=@1QX.xj3b].k+vn0W?CKc+?!Uv_%=/8h<ser8o?);@Op2XXN01c](kK![}70(@Lm_k<rNl;ikWs}o6;b.RQSBue]O^RvO[9^vhr%9j7^VwwrWRC;"1HW
N;2C@@zuOL{b,lJUvhWHT^%krmpYB@;&+ea9RpYIo=T1FGdm>R.TArcq-9sw}_3i:q;!gX4[meRH-YP[?O<b<TZayF:vrb8Tvf|]TOW_th@9VU%96#SU{&YeLqtcrjdS/;_LL[K4r#c4)tF
7QI;DHA+vw]5*9i(})BBmrAiaolknr-&|Ezf=K5N<PRC.s
cU0iz%Z[/>qPdK.$vrPB$dIoQ#3d.RL!>zfQ3I)Mqye.>zXR5L%vH}Bl
);bK@%;CVe.f.Yo4J;<+q
"?Iu,Vf*3E%_=DgO_Bp[]6a6{x(tOtye?<pfKD$^yM&V
pcg-,(k),Ulxu>U+v(;-$B^0AiV^U[O9i$R"D*"jKl/)L92ISnvL@pUr+.9YHJ?2Nsq&]x6@9vP/
d]<!Zng*!k7)$7C+Mfb#v=fEofu*_5%HYgZguX`BKPZ>aO?3r%XKQP3X&%$O~/2ni<UuUIcgU2!avT@T
K)XF!%.Ct;Qch:;?CT;BOsmzbra`?aFuK3vn>
[D0V^2;*)"[pRE=85gX}l0yfMOtIWI>,
(u-j^s;B$ebE!)|s]@OBW6R12YZRz.(UtxbKKFiO_XB[<d+3R84"y?BD7U)A1E}-Hk4NZQ:%Fu%9
$]gb0L-$qnH1G^!mh$R}yq-;[UZ[J)I_(DpX=@aEtw.FFcdq`1"3[v)?/0pLq._N9kc=]ahR>g0:!fKo49O(pE8.cb;m:@xBro%0rnZ3@SSMr1+W`IF"4gZ~u#HUJfiMH:Z<EkikrjE9>pbH,9"jxYt2aB%ST$SCJt//[R"OhqIgFK?Do<5"m+Z_if9_#ym)LwR
V,Y^L!tq:?gLXc;s&F#"x^VcZ"G,?+3EOl8J01MdrDRX#u:PFj-Ro57IVd.wUCn&jC-Qd5?=T7V;!P>|=cC=Fej<No3Kwz9T=:(/E`A0R^SKyM_7ugJ1%gqn%MZt:v0!=}6:T$%Eo;XSw=bNVRRq/9F9)$u%5zyBwA';break;case'hi':$d='-evWN]AG"Y%]tY4#jFUW?J&gCGRYK-])tf=_$u+5H-Kp|mzQR05#2_]
h>-4K9ygeJAZwcpryZ}-.!]SSUZ
]ry6QWOICi2>Bx>%lsLHGXRK4y6c"r5rq%UvPb85+E{j&:ALjMftzyjX[f,
|308}ymR,6
@f^p1$_.*UQni}T6y.&+>A5jgc`r?m:oVfXYOqXLJFT0o.2_oJfsbkD4D+s_:pLTBzLYVoF.@;6iF^Wz^ZwY);!Y,N>CZB/Q"[3n23
awNW&kF-kdPCFat["aa/@fo#j.0ooy6SlfQ
aYz@D/;5L7q*7U,rU(936uQ+Ox6c5O>-,*9WoR$g]#fyWEi+
,SVU57&q+yJFZ$^Xa<ttM2MbQ67lG0qiH?mAG
]-x?cso(xbmYstuz7;x`u93RcCc$K,l5tMkZ_!8#2/b_Eu3$1No3nql=m`S39Q&]12dQA.L"
<Lw#h(Ce=0S&q]B)Mr*542t_i(.!M73%Ua|)?O}hAEgA>Ql08pP`X#my:P*qLY6O4Qoh!Ne=t9aqe)3v<9meW>=!gLlm-&1(KntN)I=6F!hf4OIV&gob`^k7}%^-n^=!U1F-,x[t```cChH7ZIGXlVi/]q>k-v,2QXmL{SY;7#ZTD>Hc$E#cVI,62ACS$+5fD1*WeqoZq539-D?UBav2oEG;7O;bt8J3Bk7Ajnqwi]|kloD8]O`31>pE%iv6+<l`*pZ6p7UYhHR*f;pM`_kX=x}_|YtQX6
Oe[zCiRB:49udWUPC~+SD+OhE`(8eU<AAVNpQ~#-iRZ-IJTJI&cT*IXxBgv`LBS$s.+Oe:d0uH`%E<(da1vD5sLH2ne-S7y]oumOU=ge/p5G<w+2Ik6s?2sn2r&W(i<L2l>gvX&|Ja0w5RVSW[X$fk%)j#]<>,eI#U>.oCTHTE(#"j
v5nm}`N
jKc*`J~:VSBRWnJm,&6sGhQ"7_*V}%#VxU0lO)ckNaRZZir71:VNEt[!YT1U
n{+een;!j^LU+]S4@6fj&_o?A-e&+$:pk,G:>y.V`[K}"q0C/W*Zi`]]1l7UT?Y&iAB^<b>k,{3r/p7x!8h7J9kko2k1DmO#IuOJBJ=&:jThA9,BbQQ0-HZZZuNo.|n949RAIdE<Z],KQx!@(&+H[Nfo27nl<AKxqr0uJho_&K>&j+8ll%H.J%*11V
M4@TM=SvGfQx,_2VH@dlaxn]Wuj(.@2=dTERzI(J"qKQh[V>cT%p7Jr(OYXpFCHW
KpEX+zY_3[O-Iak^Df+!>nP9Xf8"TZy9)M2yAgpo)UT[r{H0tjg*Iv7ok3mn5q:WOyX6I[(NhfgFlre3
cdJh(J?nosK/x[Aho,-&lfDLA*}Aet1*bp2N4Il)6w>+me2Hh$*E>W
ZsHE_&C@gy@B8*rla=9oO~PYMJ:]SVI
1hFUcUgxTg;Mt%T4YbCUZb+qZMc>LxQwhO@{oD(FTVG(.Fvw6S]_vq![UA@ChCL.bQP=*6hJ1dU71#/EwnZRJ+"Rf$Q$5<!Y_U%rSx)Q?R;-Y#[3qFUMIelG8r%!7LZh*tjI3!mD)X^5=!lLN?rM#]PMrT%VDqY,1PCZ=l60lEM|*xQ~Xg;F<?4URychB`
aie>IAo!?MX80gp]`20U,&WuN`A.GVb%t8!Ze@
*TYm<[eq3ad@k@nyOif7n^X7)nVvJe8qy2o*EB3/Dm:6n/,,lP=gi
NX:Hl1b4q?nn7-GSFs=+5TmgF0okP-bj)e;C<,ROMmjC2by?*ewb$vBo/1eQ3vTJvMcWSE;E9jk=C7#k5!ft(rC:>rv~efNC1"R2hq7G<4rBHt"U3UE]/4(,Dmcn8h]>&#pHd-.+PU43kC[M)OI7*uV"qMAxI3i-ce2)4R9_P(u):VDSu):FIA!QOuYr[~#Pqse)E#PG(^/~OyWg0G?,pSL;GKQMP+^J@gC[FewoSa,=7MsWFWdpH}[_]AGRP3oiT2b/F=2aCbv1G;M96.sn`l-mMH[c[nT~#
@vn"^0E:>Fj6w#O^L7@SvU0)QJYE]".^97e-Dq-uq#O|U0aye=T2?$RBxG)9qV*NKBD.E(oT:{Yhgula3$_"_P(/OyrR<9tHS;=f$;ZRFse_IoSmOXaO88ovXTaq1Su&dQF"=vs=%jx{h8+le]gum2$=XJI-"1b9jfn3i~8@okqhcxooNpeE#5e[SRNz%gN,ZA2D1X!Z^x3sEw#H/x#KjKdMg+Y:1Z/tk=.R:}.,f[T%dUqb>-,HZ}_=#GOr["d<ghwm(WUS
*!sRsN
Q$h+aD#39V
,W)$j#B#yD5MxE#8:#$fxAw-IJ{Pn5k2L;sH$SbWf:hx$lK"@
}5>-un*)a"[ni_h2&"ZPEL<NX;eR#elhiFC6/()C]O{mD9FYctb-E&7Q}r"Sc0Buiy.N6AaZBJNdkZ"a}!/jR"keD%<
|Z"Qvq|XB8L6
4_?W5|g8+J&/T=!CPM<4p-F,R)M@T]^8Jue>/{Zl<LL{-J_n6f7W<IYHe<ACFK3-g2$v]fsZQNCfH!OaZJ){Ao%{RW>5#c1%!kONn,c+R75ODXnhIP<0P,PHQAj}L_WL%Ns66uqq:="~&+/7d|?}#.fYh/9D>UL)1T1N6CSOy*wim8
8He;5^Q6/oZ-TF<l*..OK2yAw6,*DEZUsatG@5$/P*Z1I)(aT.grgv4Zl3D/EF`2b_TchNi2)FS-MTuEej7@V6_fP&|l|[bc-IZ:Y1J%BLar
+F:?cCxictD>$G1r9M4t+;(sr4I4N?:H,Y<_oaXAfybc47WF1BBZZ8$Y2B
Q$0)#<tt^/RgQVa7,5<fEhIHxFslHFJ4Q+f%78AWNZ@RS]])6"HV)h6({je($s.4)FQ?~ajuD%?%nPO@/s-jOi+%$)h:c8pv
d6(XNmVc=<GKG,U]o`eF:#o<(JX`$S!8&K(.$"tTk2Bi=|/E6#P2+t(J+ib8NjruD9/XH(
z-
9d^pAPo2*3sY&;ql?c5EA"17z%.n]Q]m@bF2r`>G2V#N#R5gy">!f/##U~(x
a82Tn]@s1C$]XUlSk
"g?(>
>3H%8eb7*!9Y9`m]&ojuXlF"JI]/EJg_MZ#+$!8*VAxC.?ZAksMgWrY^we?*bItKxa{?#5;uam#I=e,ruVwZ/;IdD6ZZkYM+`i+fO:JjM#+Taf@0QT15Oac:oJJAKt>wFFe"t3sf9>XLz,oBLX3$LT5sw!Y`nguGJ/+DkOu"==k"aDTG[84pIo5+[fsOT33tKi[OD4?Jzm.E,%uWj<t+SBS#Nh;IxZ[ndC)>fs.NT`~d%%?PK8Qr9N=-9Ay6AC-an=1R8E91dHaK(80@VC&9z&7h(f)eS$"(IlACJs]sMf.($jO:<8~
y:w:
#mwj/t<H+5jZ["E&%2Kp^FRj7kk]MiCz;G
&Z&lN
}k~7]^z=
"-L&Ugu`Y]293w
ee|=PIG+x0i$KW^*wPXe=OF[we[69_y5o
Vqh)e&@vYJn*HMx
ZqBbc&AjzK,.aplA*ml+IBqw%/&pnx<gZKQlt/4,i`zgCnzMc.X.~$d2w&#KC6s+zu>>_FK=.>+-s
np3YlGx+jUEfGNG^SsWMQV7_2?Csu_9U{?^`L5?4C@h#Vc|-RirT5iA6f
04g435CpH3#OkAXNV4]sC5LTm0:e<IEp6642-rq3mGeru#467_+Yn_5iE9F_N9,&pP/mikhLyqT>kM7x[cLvEmbU%V#H!
s@Orv[zp1)I,:VO:>(3vR!6ktL"WvGSqp)8c1d+D<TJiu7H*!.QOGmyE/hz>P[|*cM[
e0Hq/&{-Wh9i"!~rFvP
iZ#IOi2%c[U9qb}G&_#b+5V?Si^1_O{HRj"fy.;j6j!)+9^cq7!lJvQT)F7$-rj>*^NBnifiUkz6p3Kr5SL!{$.U+3H6*#K-Ce3W@c+i_VqRjRzU}*=2AwUys:}fER"I[y3fCdCen.:t
>R9mYn]kUQ1&]Q@!UM#FXh4;Kp[Jh)x|)r600{oXe.WopBM9Sy[4>AXXFwcyHv?mu/euQ4g*sHO
k0jkp5D;U-3|]&`nw%`K6=eoPDuI2(rq.u*c({;WVy8(`OKgVpcZY5QX$|*u]
cy.Ab
m,nh3|j9B)etP$jrg|f6n]Z*k>>x^G]sVvj4SMT
t%pv_mt~Z_V`3"sFZ2/Eb8nf]|;NE)/>OJIz;tV!D_^;XVx>L=iuO1mMe5M,mT%zBoTQjJa3hWC,`}kt4wWe=RJ.e[
eGsCU0/kO1wS!wuo^.@Y}63Dk7zG6e7e$vWg2CKIq
;Ws37Iv
b@Kh#&ghGL"VQglJr:oZfxL,+qp1"10[@m@H[)FeeK-S6s.mbQ4<6(K>ZV9BntkERo59j$/V$!TmG)|34ibYg8AXR4akGli-G<E_D%e0$fINCn`]$0)UviPWf&7<CCRjrF2(|.*ti-OP.xYL4t@G@6S6
b-Y%)0H
+["9La0~n
?AnW+)B*<h(&k3a=hQCg`t<ILms0K$yu/1$f_&TMm$^%kME=R:y$avnE]Y$PJ3ZMmzXdm2,E&R9tA|]L&F1G;%-?VCykBgp339EN-w`"Y|9U<n7Oqa$_GsLL(u/]BS>@<Qr
hQcx2=k{L=Dr.-41]/1!?s#qY.VK/thzGJvk:5v}:e
Dc$I+/qh2koFwi2+djvP
&Q85cGaH5!6oyZfvrWr7hl,BFLb8m"B:h,%xgW!w
|g2
vJ1bl4Bt-KN4-^WD/e(t4qwf!aOr]Ey6CjP>uJZ@>Bwf&p]1&AW7h@_Gdep3SW<2,T*rya9[{n<5(plRIRey.oIIj/GJ%"qxm>4$b".Z)jpV{ph1Gtf!PbY:NpQXEuEWglE4]/J?$?]#,j~4%a4vTA9ez`NaOZQd1;%V>pIQV
K!#dCok^KBz.vsEL8,>v$e~d#xOS5/O9zX
!WIf"nj,&T<F$+!0BqXon*Q`U^S36kHH8%^"Q7Ak3[l5-|6{?4^#ep)??oX8jaMnEQN-_yQbyMz(u9hm_6A(pgSxky(LWF`MmaKV*NpA8n/,DrVVY.sAugF5d,`{.eyH8$';break;case'hr':$d='#]^ALf{WR2L0q0^Z_k$:2p^9A8{4}$RyivG]`/%s7NE8%
F#~N,Z(rW1nbZXc_:7y:iO
w,bKxqVV%L2+
lT,"_82pY:hfR$g7D9;v;,*pumu
{`A^#]j@!3Fa%q,5>8pO0xKH;Z7XI$2m~rw5.8@Py6h2A4)#_tC?.KHeBwpF%b|Duhq1i=kU.TGX1^pUyT88J;dAM7l+HTE_L2h(_Qv)@gd=E+dji<SfW1i2-p,kk<XFaJPFbcclocKBnxwLz#B]-^+ZiMRnkj(](cZZCHrAfX{tPu#XR)YaAFlaVSKqY
oB%0~;GiUn#m2,7U|yXn
DPjdHW,%4FVULXTJ?CG@6Q+r0e?V<!1M$&_l]]_cxUFw4IySnZn756L6UmppWSf,oRnafZqUpe6Z8c*Bo%o=G!B]4ox|aha!C_yx1Bn-&g#c
:FC
HslAU[7MKTS_f%toN>(e|,yW[Z+?J
$3z=!H%UE6ncnRX1D1m`6#EA8](C2.]88_/*M09]r+:/XF7K))Pp>4E=-f#0gSqli>KWCKz@1BH^Q79Cveal<UkdllW_5O(^Z6CI`jRul/T5O+cS]+GG>56C`6#TMFbt2j;TgWTBwGdxVZr2.,11~jg#6MVZLE>
7
?6AdN&sOx(2dkvb,N3$vZ/9_!J!iPyab8Bz)oyAPP^@FXh8XCKf:"%zG}[gZpExK3KzKNVKWfTr6de;qZ,[8!rR=IXzk"YN"yYt_K^?X)Jf5jg8jf,}pqh_:FT{
h;(yu>t=:_~Liuu.D,~]^GMWQa"H6gVz&ef<g>y`ObVy8-Es#l{QPC8QY4kX{i1dx`.@p2~a;w`iP/x,Rs$&MGj1"MJeTa,v.mYU:v5t!b)Q+xl=_-]se*5Qvs@$OK6EfE49o]+m~n{]G9;+Mk`o`.k
>ah^pu24?HouAZ>fcItpV4~=!8C,L!>5u]H`{m>#hZzJ`Lrwaq;j?BJ
#vd%OrHm;Wk
v>^X@+MVWcpa0(Vc|KGJ-_*0#E&4EAYbe_SKm?a26
W8,gH30EHQkly`Ef/&t?]*=SVq%n2A+[;vA^>NHAyiL%BXuS5_,"{wVM-Re<5<a%r=NX-)2+3J(X*Y)L6aF9BMv#V?
cOq3itQeAYa-+z_/@laD$|n6icv-e%!{hA#3:eahi$rINgmI[(B6lWw:O
i0st,
?RLpGfoSRaG<b7KX#uAMOBju9=6ZNRIMo%PpPAXU
pKKsAA5Kx?$I):]$zUThb."]2b0EM>d=3:DM9<vr8D6^#5tWqbD_tkN1TBOkbJQx@jHa3%Yn_JIE;AwWAkk9[L:EZ%MF0YLJvf*,s?_EFv)t7)KoHG.d[s^=d#8NT^$tvy"dblo@UjzMS`dN;".
6U%a<DVn82dX>3gKi(OEQAM]zs/K
T_rWB^$J
u1S7F/H3`)j!x/R_sw~FVQjWQ5"thx_bTF`58,.czG_
7oux2!0HWX7J<(aw4r]4wDm`/"r8!6O({!1H!PVBwgQf#/fQUhWY-G6I925H7tq6p%`Esfh)$Oi#OOAb%q@S3&(85ka7{5gBMLTA:0}XvKY9jwuYKpg<<i=Gkyz*4v5!=P{I%<G"#kw[Y4boHC%wy[G7dF$9<*3sX!-4;uKU0"iH+C7K[Pn-NL5*bTRc^JB@Sde:E"^(_TA@U"xW%>+
Bv$&tnxQ(8&a?]A&A@_.ruM!&H9^3lD"jY>?s)-8<S]je05RQ-DEUP6/7*c40;Bbq?vp]$sHYb+rU<+Qc+L7ZEm1_"R
&"L;JFkl|5e/b]<W:Nw7fh5k@vS,"DX<y$x?HR5z"tr`mkaUwkQ`cLY#]beX=uDWNa6sl9h:hj[1|XN".[ckg"Vbv9yW5M0qn0,SE5IL(+[8%,`Hu:J$jFsDI`"C=V[_n/l1jk|BL"8@THN"jgd;h"#%iVFpk;B1pR"j}FynKW"I{LK6upOSawn#39l"b3V9ir:W9O]bY[3*8hQ8p-/_d"NolHfG|Mj)RQHZJ]3pq/d=b
tC(j@sO!EPlk0J{G11+t%hG9&X@q([33.i"S0qMi$4+^(FLDw[wj!)+:+ji+[9y&]?ubt0%c/#S5T6J*0O|K$wVNf853b1X83Y%FbqDui0-I8Ic/u#&6mGLoLIx=7IT:(]@5_gAS_;h>UkU/4V=Qm4wL)fPayaW,)nGF$<0uoiWcMG#DxR=T>[
Hc!1M<[:u{Icc^mr3Lp|0Vm@q%;"#^Y,pR#DI3uFrVuJoULt&64^@B.)O^sw)k#d8{Cw(t*M*qL[8&i3tnWRk"
_50`4Zb&1&w7>BU6+OiWeObs$)b1lbA]y>DpCSlC;yr
lM.YAP7Y0t`[ZY_5}VP!)vovjeVX#Af_watAV[r%J?-.LY|.-Gcc9L?9rj^6+a#
=onabHRISX@^%OOW::=pnnu/(lDpmp|eI3/.P%HRkFI-+Ls=~<Lsew3U6vx^MYAV`/?UPB8I9p}F&mI<EG_WXEGxbKPJRn-;x>`?)yPV8HY2l.P4I=A4~CM<Z7_pRab4>GgS_J61zjxMb>-WRA"4P%+Hr*%!(xP,HX(%79s9k`tD40`kC0|*E;."ts&9]H;2)[5B
1b[~i/r!T}+q$"?}"!Ac_
P<xzL"!8,L1(9@,m@XU7J5uTkjBAFNqqf#2ueG?IK^5wp=6jE`d%98Ev3uNqNtQE#r*fCK9A`WjEh[)h8^-><lYePqG#6>w6YCJQR^5bX2%<Wv
SGb"6dS&Dn-WLd
fty=G6yk57GqN1p(/3Wf$(75f*@<!
S56o`W(<M9(UIU9<=a79P_ONQr<:#SS@OoC.Z$Jd=)P:%_!|5ur|(pd;Ys6;3jjKW_p+-Q$}!`]r*sjIA0bzi6J4m%khR:KWnuX/n_&D9I&/uQF$N"aCD~7`NAHZf>WZ#9D4(HS{sL/jSjB[_V<AS$A+W|8g&_W#i`&XNIT((Piu/PuO/riN)x#EKv4l&fFCLJM).;%N3(7ds*8BDo#3.QxBV0OC
;iS9<#w3Dyh9]eF!GT7l:y8F^JpIaF7pgTq8lO|LuHYp>nlnNA:nyW~(b-+1OB}^zv&+tKaBG5}m.v*5g)oL{aG)^fp_9>Sj<J`/7u.Nn](OM$w&(`dF>&{!GDWs&f|U<r!63+6n*EH,:>_fqK..&&gte2c&F84<M0T#y)nn8hX
:dP/K],q7v}7aA
*J6|xtsTwY9]PME1WEJ#(j@+cE0dt}0waZCB^KN~L".fG`jM_k::+BptnoJWHPnmO]%7m0v|R:qf0!8`a
oVdwL"%,MzKcD27nT=dJ5DytuBHNR0:FYP03P
%2/In8
x(%Ij6108)IKha6q=qaKqT"tH/lRA__Z!yUj0M@X!P"m)Q%(}IV+98PaIK6HCbk+PexW,Zi&`]yF7CsurDD,k*</Qx+vsPKC!rLJIG6H|djY(;7p$V>-t7}WwcVH-LVL!c+7Ft})?`k!F<m$,e[Bgx,FboP52%:20.h0~R%"LQh?+<J<r:W%.t[Y8%9_u:d7pk}w9U1io@dl~CGWWh}%|KaOu;tj&!7aXt9pMMdn$8H$mv}+m=rj0)B,p$Fn0t%vVS1gAI<IhkyX]dU+^b:=zp$rJ;2p|sO%Gj&NmByn%3Nn)#0[nQpKT&9jH-Ds>V6;->j*[Q?r#$D
J"s23@90(<g,&#OkNm|TVI<ASe;Z-+2/Z%&*&7uC]3_b)#)7(+{){Ln<}c8d*cY,
fzKTEK1u!%Q&U0(MF+xfG2huEderh0eF=dev3ev[?r]KtKmOVV^4*}S5%{+^CX3#!"RV9y(;P#8
[Xd)vadX6?"vOA2U
)/wS=iX)hWp9j^emOx;p`.xK?lXQ,I&t*EZ
W@8hQFU?{C4T|`9tlJTb8_jizoW^vDb,UcTv<AC`X9tV!>]OJhTWS9xM3*436^s0jKO2kb5%[,QYcz$mdT{xy;?
&&$=@d^J,W89PO=N46)>{I1h</zt+RI;x*,ZvbJp
rBo[#:xOUG&)y/h1Rn[5^i@,T|wEk0q3+{9O9XG1&#gP?{y&/E1`#MX=t(nWPv^}[m8CXO8AlB[
[WTZ,Kno2>k!w$E|qC.
@ZuT$Quz:[@DH[L^/%pfS8seIoM,Xr^FMO^+I2V]N>>aQ<a,VEh
"i-jPg9r`Pr!J@=^,LTYGjE4cW#pp[ai.#`7Rm[IEcbSP%sz$<&91q1iwyrnbFF5Vf!QcfhRkL6N7F8463c=p>VAYWM2+q8$xTL&myvxqgaAu
r=(vyFEPa^OlW]U0U9"0!MCpUqIzVyF+[#:Uh]teuYP+NDB7")SGHUp%q9mtGI1M^_.I^Y2qh/.WWK7d%x2#LJy[v#gYoPoIrHhYgMD|"jUy*qqtMKEZ)O+]7~f^)yeSS#8~w;Q($tZd,k?*R>b,Oq^@>jOg/DZG+xAdUo
$RiBhh~L}Ss$VB#$^YQG7(D,(=]]jx^DH>)M"(N.Mnd7@h>;;7h6w(1V]@$d!o7';break;case'hu':$d='(R];:bpD9,|?`84(`O[=,XsdN3|.x>:_=0.moIH&uZ5LNtsg"3lZwf/&uhLYlfj(PKYPp,AN&wEmcy|L4M=cRHpwM(UvjK,^@
mvM?8P
C3,CP_uPLuc@T];pMfDw18>*>*S.$7&#^Li1AvMctd%+GXrGl3IN"S>>?f-}BMa77f3zi@vC=nA<ll^7DKAcOgWZV,5&@g=,THBtW?A::`Uru6mEbA.Hm<)*
!P/ER3;
_]xm6<RuX@.u`"[g>&m3_eYN#*W#~3"1Le$kt=="t_JB*]:r/#jQRavF83GBWwc
VfiEzj@
y]L@{c,6?DES8ywqNjitOtCmZ=QMI.jw;<cw4MnW~qLm24W5r(Ay<o#=CH/x[AN@sNL/d]
;NZ/(*K?[M_.XZKDy#T0%iU2/Ehe.c*!.._-5B/+9NECehu/Kdts#QdM:oV|iMd"-iBwg]wyDVK9Var*Tp5sMzT[TJrneZX,<29Z9Z.:EXNOS-gC@7D5::*Pv#*e2^Z-796@A0q0CldMmP9)VpBUit1xe?6TA*Q"ThXpp,C~Gh&H+sJ93V[/.~1}&!2Z
VcFnX:s/(W6nZ!uAZr!-q?&b;WoPj?%UO
hEJup[]u_*H:^bZs8%j[0L?Dy=~d:.UkdTa#iA[_D9pkt
W@;Q(pVaa0BiA4b4B_FG{WZU9/EMM))@&m<$A?2wl7;sAn*x6Y4d!8/<ihpb}>X$~_XPe/!73,H=t1vE
$fbcftGhi}ha`%SNxTMQjSc~!@o13owGP>:,Ry/D9ju_9NH5X5rIsvotjx?)M~.WWMjA[N5G:WIYW.((9k/3f~qc1LJ&i->!E5E]mOmN:5=66Kk7?54CR+G]ym]P&D5U
~QXKE]rDw_&;K<zpOK+XM&Qhu;^
sxU^]iZBf^p;UeBe1hG@;ykafL:CLH?7IfCwo=<r78{/vu^R_&U(KizLxvU+BBJX(q6p5!Dfl9zm<O=MZ*#Ja"@XpK>M[I4I&%1l[dGG~Z7[zdm]IeD3aMpPdce@K
R
UR]Ih@nD@4V*mT`2@*Me42CtZm$l1F`0Z2~e$RO%@gL,qL3[AVBDP!5Y8-1"xq869Rn<h=>G%*Ot&/rep>(F?dG6-`i1@5L&KE]3gD/Ia&q10gDWEWSvXH9F:
=4/V]*SD|pp1.WSqHNZgNYEKml^W4Zc;tbV1H2s_T
q<bukDf8_a6uCvns~<:LmJ&h}.sc%jgJ/d15!N{Rs1"VyfiX/SeC<,&(p2NhL-2/Q2HV.xY63
cU`%blf&<3SfYD+wM@U0*fMTs]L[N%vb~qSKcn4M&^#RJn*;`PZ`>sJasFYm6Lm)>dLZB=)(dny)ZH1M^^#AY9[^fKDTL9:hk,v?,XKRa`ReIdgF^i:r@"`O^pRs.)m0!IApu%SZ<kzr%cFA<=YCr&!&3
2]EyY14Ct?9^xO`)Q(_S5rT1ZTX6rcxWo]1GV2
%utVt]63<,-et,HWB^.
J[v9RbtdB
IL_+tpjE0P>3lX2nbp5Al}vK7T(6]kQahDB7W~)-111F*b.H.
3#ZU4
R6TNJ<DUs#rE"T3lP@9jl>7}L6H>FTx`<+(5:I)|K{]sB;rSa3G>ad)8#[]VLbp2-fuS2{:s@L63o`<S+3obw48i]w=Bs(T_aUupa[-$jtgNS/u$:N/?D`Nl>;F8Lh^cU{CEVy,}7R).%<[~-H:Zj7Te1/5<^62ch7<2!cgvemc~Q=O=4vF5T[7e+Pjek"]G!-r$BSe+#,NN0Yu[8((Unr67?pMt2~OlP|rkVvirC*Q%6#H^HlfYp@&aU1+%m<;R<]IyIC_<>+.JjzFNbvI/cN!1T[DwwXl2YU,UW,YH^6rS1G0ba&uuD)$W_=b8j5nId+(<>
C@WCA`[IY1"?9,ne=z.el0N^Be6P8|%)I2).OL?~(hneCdA$W,vZ<!"zV3poc9/x(Y9Bvvbg<Vc)Yfuomct+m{fJp_1$cF(L&2"|dS#SYc%vnp@lNpCzC9x9De"XG.8?,veyC>Ew/:"j/x?b"jVKcrXMp8E8"}*I@z8S"x%DN@"w,0nDST_|L)nPG<E^B*gAb5&xX+gw$uF)[dlB*:C72h&nnr[+.kG#_JSX*NmKuuUe_!v=SV"=/6<x=>:v38v,lX!Z1oor9>Q,414H%d1E=~1V&W/Iqw0Y4vqJ;&F!!^rTTm_*6~K]1_Q:OiiK/s>-LY!d;=D3r4#CpuPwl
jEbvl*gyR"/zA]2S[T!N89Lt:t_<[G9E2_M9htS#?]1-M~&!>[$r:$DpCbSREyTid/(Ub)$2?[I8)CPqYaOB@N._E8d1TB8l5O3%<|V}.*%b;x>7IHHGgqcMv1R5`%QG]$6qy#Y8pVaVm9)[Of?HIi6w"ncrmPj-(4t):s&5dE>4]8;]<Au#jJyR=e^]_jWuf~aX<,_0sPoLmgVveGPa7U)o=UL&Bp3F*jS~ihTzLN+Ww#2|0ff(=H0o)Zsg
i6bTU7
_O.kJYbTHN`UQK_9V^bIE-mJq8xbyCJa<tT7=H6fT([fcVCn<,LMHm-n
LM~.fXt,$f;oKQ<YZUtQ8DOSlZ()+Kvd55YWg-)g1ZXaLq&l,`sT$<[cnOh8G3cr"*N.-?*FdIxq454*KC<doIlZ3xI7"kRIUc,tqOH>!eAEFn6oRZs.IrZx`7TStawCFaqIi5-_@/^yA;n2]Hz+Lu3>A
UK8TTC_8ib?=LTB%p9g9:O8atpi,tY^fx!*iw@PT+B9;)1:5_d2V/fR.^8jHYh9@sFAeu%NxzWqYK5=r;<x>x/fO<&u4ko0kevtpz<O
4L]l"[Dh=`Hj%t<Zutq9u;&Ni@.17!d>
37RZ;uDz#{"p95%YR}$62~98Kq#S3yQZSV1OYRB[
:em62uUN<ZQoMxx`(M4wM>Q7B0dWWQdcsEW&^1HvW^BSF]FB>:0SpN<8~I^<3>D>XgUB$ftinyk(=oZom&,0i!0!TWuYl<7)aR#Dl5[0Ic)v9*J:8IjC<
*]h.KCjRqHwh8.k2!u36O3?9
+/D8x;sm`D.QeM5b.N(UN>ZGZ?9%#EkBqplb]=cV-tQ(v@AaH}Hf?FoLe4x_f4@I2_;F0D12N}E[PV8;Dcq_?/o5$0MZa%cc#no-:A2d,T#H7.^K@o4/%)<HTvFFLuLxEa"2@QAimG9N4OFZ637IHAR3b
Qe$C<;.H6xB*)=6b6p>U&{`yaB)JSa:uhHD#$qGeu*G[9>3;QSm!?]_&)lPhXW(0mDFMVzb6R|x"9J$XGwn=4h^zB{Zr7IG|XDp4a{s=9nSrI0%mEESXj
Z7)$P_f;
EIJ:j"]?k"{OEprxtYiE_dO5A
P>|T18^5P1ROEsW2?kRR3F
.O[TkpY7Bwbj3Hw%^7!fpc]sG.C|.I]mO>bz55xwvm%fM/AJ[>TDKjHpA2JKxZrK83&Nt;c,5U2P,$l>TS;/&O9pq1c);I^i5&h^5Ov}i}b&%o3#le638+_i7!.!-XYScE,lE!4i67Y3FOkDNhCnZosaC.GW85Vr]T4:R!O|m";cN
ATC
_$vU81Uqhx6^3CQNYtq|d!jbL8%Zg,^?<o8odh0>*5C!Xc`SBx8:l)phT7udMy^5fDB}oxK5npT#N&x];[[wUq(}cg8(>!:y03ha,iv>`/;U
$w0Jvg|Pfw%qsDrBz9)9mo}Hjq8hTAC0n9~l}T)t(+"h4cKVJKzuzOPw5+E.HwWbl6,51To:H:uh7<!RWICq6HqGfR9TflkM+I0VdSP[+$W?!
eI=`EtO=~M30f!88Qw.oA
/vEsmd(9Bfi
/Gv2,KYPNOL3&K$pC-+,_Y3toP69Mblqw9MJ-fSrov^Dy/nDjA8J$uem.GkY7nc8oq+$/@!+VBwi}Sg3[T3Iub<z%uba3W2qmMxki"Jhj)HOGj9nf&TQ6sA.BmX8%o$+T<J9j6tv4A1,.JVSK,Xk!UP[|>lU-C,X8c@v^@Hkbd!^[OpX)s#A`F7q[L{?aKMw67R<N=`YSKyrGIzmK1#Jr_ny8Y+n}v#1^Dp_K.Aj
!lfpykB3Ng"(n]Eu60)Oq.pE9a<AY;_PS-gVgJ"~e[+B7UD,;!C.RBxebb#Wlca$yL3;ItQ2-BvLA}ySy74~5qR1e,
5YLc-^nXEHb3ri%mq
Es)R_&iBwm**yx^x3S25m%bH9;X^QQs^96a4[m3qIi_)3M#6+6b+Wfj%?W8(.Z.53B>M2xd#x
{oVu^X"a;h^pwJ3RVuQ0kihoIuz<(&RbJ0n[3X!k^o:y/prl_h7Sa@ZmQGTAvgJ(k*!%E69cAs]KHq@$2Dua_J4Bo4h)WD"J,KD(W.Tv*]TtB
DgVDT&]s/c_!=:+^-uF=<b#sD+ykOvdQ)oZDBO#K=!JY0/Ek<OP<X`O!,W=pawuCA4Eq<VEwFTx`YL>jb=Y.~T{CBgr9If1ha-"Ia8(h!]5E!TF"#=sas-GLv?/
c(gi`(&MK6b[2ejC5ST9dbxL!#Cd-l6){p*vB%P/l!JF4K
d)mFY|Mqfh>h75VW>7M/`NI2+W-/brBZbKe5"SyqnPDw7vDc9Lxfyb;*jeqFD$TrjRD6!

,?tp?oDz&&n';break;case'id':$d='&UF7.iDmd,~^"/8Q[,m.O$q(JFjt0qnX<h-I-K2eFYf8H.vNdeO5@p:/XyvnEOy"K:g;}xv3!g>NUcBcUcxwsL>6e1
@#L,3SkE`ha4:DUsj@UGi%Utusu9g2&LBuK*vI<]rgM-CjFCGO<5pwFLt`l};^`k08s~=LZYOk,3?P]Zg6cT4
K(^)MH/J,?8V])9R[Cv9h#x*G?wCwRnUG,HDZEHMkjUJb(
vIJ]XE|h!euMI1^AibYx
xPW~MoB][kx"c|w>y:LkQ
^evyc`m_vU[jy>lociaPya[wrg_o:S:GLcbV7
qkg-@o&FA+QaZ!b!4;&(_-KX/L6!A,j{$uDSIC/>,#cKADT+c17Of4tIL{Y*>jS5^+([07BTTm[H,N0bLk
bWSNzeu1
?(c+F|*R${t9c,oCYtnpkgk18MDMUz&g^aii&-!v6T)qvOJ$t.Cy:<Y=E1Nk=qq}:6b.:Iwa@26;/ulvfM+qGm)wTYZdA1jhg@crr$#/B60zn592_]I5De0_WmaVEGbF.
onC$3$E%eLj*"q
D*U_eIa*Q;gX5??WPPz^/00gKg{bn]TX2S`aK)7Z
+SEA@XJ{i*0CA"aa,cGYIH<V<bYNH@k74|#EWZ/<vBA1IE*5Fipyg=P>U]b:1uEqnCct,#QVw2Z%^5b1Q<(Sv]E(Thh4G~<}4XLPXpduKv:q-hVARN2zGandVM.kt)C}/EQ7%ehG5vAu3(e
]"u6TK!`1iYUN4,rhwC"KEB{dGR0H&TPEN&FwzA{l?E74W.^%u(eXL,Z5fb3(XB[Y.*#UXK;u,1Q0w4:*gav4~gj4KttU)4Zf.alv):FVU1M)T)Hc:p$gbtkWbk8>CG?;3_<e_Cm#N_WJ)H?p8W/@wd`HV,nF@!9>X2nL{h}m?/E-PLT9[ea1[08pc4#P&dKcn211_L%p,*erV:3)pH4wweunWC>/sv6vz"FAuE?wjAv1Qd<AtBJS}vr&3<[3h7+$:9m[2
R3rd6>1d8?pWD"*ZLM<YgN]E"RnY
uJYSZ~`cl5u;u^j!EXn/A{1KRJZ6W7T%OaRJGvkBT9sL-!e]eX=*/=/n7[H*evq/#GyyA,%C-OT+sT_$)v&8Oq,Cj>*sBHdR=[Bdcp@@:JhTue:#DG"_7.@zW?>.2{D3g:PbW2A+DDkh;.$~uQ(9GT;S
SV1Nke*:v
(MuW1^TynP)@ln{c`n#YgQ[,U#iLE%V<ZO_*b_na$p(9vaI7,j/
HEr*4c3.fyg4=<n[mt!TBJf"n>,>
M["otwW&
b889UVWAs-
q7mCp<y
qdKli$;qlr)OM"1iUcG8St@LA)X(A?g:]P)>SqL,3O5{p`c2kugmb*/^Nk(V/UlC8$W
-[%3gFVTJRl_uT3()574L^ZY.wj%VQ>UIXDP??nxaAmXZZ5&.L>{2:]"YGkXST_R_e<(FCXWq^Y>dyf&
^*3Oet.>Eh?)J9Xrq1ICyE8.``=H~$~6xX]Qr7:fX"0[cap7Q3H*)M(]!MYH3CM`(R{sl(OBf6%jro)#8Fc:@UmO._Mk*sfK+<mQpl%SrSo99=naU_{%StX
9"m_dbn#K&m"c1m"B#!eE^@Z6:5C^6
;]#q-?XLdwBkahHU0M,5Mi1X3yE8M./Ix3T^;h>UN{B~U-ONvlQF@B/Me&8ew|Keva@*sCn*oyEg<^N.KXB8JZZ75isMHk<x:vbyems$v-6>1nKF&_k6R6<,SQOLetOmH`(N?3N=.8D/%;^"L=pl^GO<qS6+tPNq[+">@P:UF1&RC,I32!#3r9Y.d[r+,@%{CzV
JmY.d%q<FT*tG8#Hb7u!J2+_vhN5sp:N-#
P/*:oib.IBc$aN/f*Cz=zCr",9*KINGojZa=.rT2+$+"T1R<8oUqmFV6X_REm7&<0?R>7`QpdI<-/Mg,jB:^%?CxZ+R:;WVXTYp$xL2kD$H1?f^?x^s"%N@%[,lp"$1
}<>=jBJ#tP?)9U*eNvzwO.^#tB0%hY{s9[-xZ?^%,v1TvHAX~>O>j1920)`t9ANdCPOOWMk^QCwA+5NT=h:y4:JrrNGdUv_7wB{^j5KBV@}iA9uERKXj&WC$pc/^$peQ"t2u0l)rKQgi#!<SQqdMrQ_]sw_-qolkuo.5*F69Ax#A-^:tbJW`O`S?AK[TppODp]f"dh,7]O[>;22`,H!s<e~_`rScYQZB,qzfm=3+1MLBFcL??
vR)LL:@
>lhoqO-jIU>LS8gE}CQ]s42AMuepJc<OQ,robJ,%EI5OUYktwKk0t)q9?Y~/0+(,6Q-Bo64^#)Q9>F&D2/I0QBVDoIU,kC;K+MWg5:YJ`;&1U-s9gou8]jSwSJIH}&V+q[j:`i:(="6?UK/o!gxcdgjl(wQ<^;Tn<-W_RtmmkrN6|lK^jL-mNL+pOAsjHDe"@=Oa`Yx+eWM.3
%*=Kh/^^u[qd/Jr
@J{_w"N:e<JY2g0?rLQ4}?"fO$4j9YH-Se$[HnLA9=CN=8T;"JiO![P&+i7v>OIICGYp43cb2H$?+@f"F!@Lfv~5EdL.:#m7i2$-d5dQ2?tc4p<")#=EG7
;q/BMK0YF$pB78.,ThPVDH%?#+^U)^(dp
^N3|)[6FLl`{OwEbhXdL=WGEEV&Re12U=urC)E#$tHo3Y%nqCB>GokR2IAv0M<vx&whO%0V<BIm@C<7L<DB"KEq~!+2Q>8O)o>1/rgJ>h8IXJ]9dNF_TSOV#_,^]s^
.>yjqxq0tDp=X#H`:u#.n,gX8Y7$Se1s)Y1"OF~_UC8T.n
2ie|2b(N6:1)S*M97FE6?[I=W
SpvowB[df8?GakmF^6F
pO::x
d}dBjyYqXid]f_w5JdypWrXWp?Sy"A>rLjEX;Zx*fPlOt[(U@
e#0ZO^2SI!OoBLQ>Fi,{9?n.Yv?qybE<O`G]h=Ldwjh<4lcWB"SYe#Tqo-gTjv]},H:jCU,5!EITxIEk!AZa.B=ZGVm,1E-$Z[y64wKwro82?cXuY=]]Kc5~`@iCGtk2jIMkVY;J67/yJz15c,FPAU-$hluaR2A2xv.k"G$&D=77
j#/J&KQN:-K3aX}k`4J,]T(,j)YvDW=Z2JM<U>,S2rN
3voMVV8Xz.7yX=C0[YG;ShHnyBeX},}#UxnrIL`[Pi{*iJ7T!X]4hNEot>4``3*Next(srh.S[=M`%.wOo1_9,kB2eXqHIEHeUyMV(YW!+Vp]4:luPA#`+W!vS.T5`>A_6<7<u#:kTRK1-X5<N:2ZSu?Idj9EVHKU9c)*`<#2X|TbHV?Dp"Ih)7t3Wlk$s:Me]>o}n$`c`D]C&)o&
)viwYS1B1*y>[j|3ych1Z/!S$%Grti*h|aF=7P6,}(sR|8u&wK03<as?#O9.?Ns0abp
SV>Maq12JIBSnj5gS)T7Jj9chhI(oHj5Ob.ZV#x94]jyumc$8XW8Hq$b>&~gN."X0#,UI=Q"R;rMytGQnQNoM_,C!qManlux{YpD6Hq?5$>k.hIj6$i"iQV#B-,V0b_e+x>TowH8*OWS]a_KRQiO(Xvs]NLiTn4x2F4)>3
iO+j7v#xE*[{M/_K7F.>QI9X!Lb~)Yq]Ks^5ki+T74Iz(HZsQv$qC.w=]wJoma6rA>=4?%v3pm[WxQxD/RtsM="z^F3qk7aMw7+bE7,}WR!PK(K]hN^hBu&0"Zs4QO6:Ut<g)d-G^[d|OJ%>bMq/)wB(M$R-TZVeO(4_KX=LoU.#q83(eEh#K;d!+IGHheyFc1.,0{!vR+4jO"uK(-S(d^)-S,ti*-%!4zH;1_=Sma(b/x
G;9WC#NcREW3F9RW+^jT@.)$]5m#xyq($l/pTgLa&SG,<H`LF4@/R0;x]]kV})Po67h&?k8xkDOVCIlKO"3*v!A*_WKAASc5M>zgTcHg&m{v[h}SR"t#a86O2DH88NtX
YR@4cF%#1)SC=BT1cTNM)VC:6m1zr{G9y{<Cr#YOimXJN%NF';break;case'it':$d='$]^ALaMAp,z0
Y+N*I3X*L=l]VI]XVO+;;C1BXdsUL54^OXI`Jy:4*Hu@wfHM#aTOyr)@6&`hrk0Rxu`HCwKRQ=[NBQ,Qpi+D:buo
vqYR^rALH7
+#b30gk|qWq,5
9j03y*4{B/Cya6t3`G7lFkGdIc:b
z?0d"<~r-?p0yn&F9bO
zJ4tMj0p]6,FJJoYbj_?70w;WHPl]@kRwS$jL+0qERfjhG`]8I6I&[>*?nU/pRqAGR`3QXy&hj6`8z)B]Z:w:cb3pJ8nmn}qgu#XR%eaQL=bY!AHGHQ6aL{LL_xs8c3HA>u["1H:Z1Ud8CBX2^tS".eV.F|qaU>3`rmdmemoja9iATNWI<dL>mR$+hD_*Iut./tJA5vB_VCVXXsu[JSL]XZDF`a-#B$
Li_QeonFZpU]f#tBon`e%Gd)e-BB<kvE33@n(aw66Ss_-&k4*D.13UL7V&#?<6/&
>,NeK;BXU}94?5
f]ncH@Rr)[%V+_,(80V[zBnJ"m=upnf(iG:c2=lZtJ.U}lWkkJZG(cxqj
_UtO8har6>-V#QS2vECibVfe8RVv-@OFkletWW{fRbP0@(:KX5pHp^z/&b+>|/5AZIJ@e
4,6p*iJ43eoSBu7j+VJ
fPGfCAhaOP>a%JQUMI1xx%@B?JOpKf.njR~w
6z0Sj`KP@Rsrs`&&vN`qw7ECvi00ccFm0=XbS,O=A[!J#7_4w.;##x<[G(xN=]wCoe$.V]TdXmRJeEMS#|2tVj[cX^dg
CM%0W/CXVPeJ/wa,|LR
CN~iM>_n|Gw-o*py&J%[Zl>n3Dcf*tERwNzNLS?q(as?Bv1i6,z3,I5oLX}B=x&7d<F0/T-]@*SX`lPk3CKe(AiQb.{T[t^gx;w3qS8>:SzS`MTkT!#>bQHI:sjcFV:dv>W<lgxoVMFhH!JN~mpJ>lG9<<K$n7P0uu*Ht@m0UR8[Te>[I(^<ln
GM@cVST~pcdi)1f<qcinVIUe1e+Z:b46b.,dbO
=fKOEZw_$<;^>h`[;,<`fg)a/JL%m:W=kS_Uy:?_X^g)5k8SYc
F8GZC}orgms;14<jN4p!
z:F]VD-Zf^&-S[1pY"G_oJ+jGVyAR"W&CSS&X1kE_3et]obOUkzUYeOCl+KgY.067_WO!=re.
5_lq.A
iBXH$W6MhF?Uk*9y#p#x_~s(8QaY:LQG9hfx0^<5FaA%le
)+nFV.8
gi"xHRXuzwgHrOvK;f>,JiMEv;uyB(L6aSj0>;nTe_HALY.H-8cAIRw.]/:7&uTFa.Ovm9zW7iwm.)3$7CMOD5UB0(X]}0<H{]xqe.;!xf!C},,m"/~
2JWE)grMZmXD&W{39rD(lI5no2V?cim,9={C1s$3ukz>SRh::F~c.HzHX7>sE9ua*t~
<<f2"w@)HHS4-(M&X+O(XWr_M3!&;.}qgDW?Uqu$xPTV;Z+eNx[Zh&0WgX^NN/lb*SwEKNRjt@n6S2+1b^cXWP=V5vISLM<Tu/C#iar""qxSFp~x`k&3N74w}@`B"(>@EUmPXU}5L0g?@e4!NDZC{fyoaXbC^ZiKC/^"("dCFf<o/!q2~uPB;3*O!4aN.f!/=O5iDYh;I.Z$jvRoy02kdfw:fY0L^0PPm`h)(N}n`ia0LH4.L&]C2pIM?[mic0:6tYE6e6s#l6H@he
H*"j/|P,!F,?gsCxRk#nhw$z)ZXe;YZxPt"Mq:xry}.io$CZUDwZ1sX7]ryP)5v[X{R8ur_gJxtpvNj?4I$#4{v-T8&jJ4kwIi&LZ:[#wAC5!R;q30#VWlCH#lC).&F;IrIF<xJ":lGbmo5Lo"XJX0B2MbxpHqS.Ba&"nXh_
3bBlW!UNV$L^iGgIzfow!V*5=.{bv->du6>M=H$&(oZT(-9SOf;CHjD"m2;-K3.=E1@lH]7s/iUIt#tdwoynl4#Z?2?naURd-I_Y".qS)xXIe@mmy-Q9e1Y+~#,Q%:6554
a|YNNa98CYb6A3n
sT,,/A%&T9/f<oPjA0^]doUi(,u_AS9{r|W@fV,IHq
sM4&hG1y]50]j5!h>0TN;f8c%Ntbj(<]o@gW`<zfDdr%UQ|;?pgdrn7%hYRI~7TqshNd7B-EP4~Cn$aCcO%*V%rcacdd~`~WBchDy/xo.vQT~Jp7Ho3$QeBRk:+`gz#%4qEGqPQG$Q{M})fLmp>8":3J.>_#j9tVba(6wSKFn_R]~h/)8;QtAqgxTbN[.cJ-:*/5W`~a>OzrA:VwXxjgYHOYD=(uxEJXH]Ws]4px<r3%=R*K`E%-$RJTm8t=-N{**7<nL"."RMr=9C+Ou&g*p]|IvAfc9Y|?_:+4C@i/G!"$)t0Z(m7Pn=5h&2ka]gQ_mrpBl+OAMB#D{C|*ySQo*g|s6h&]KT-4=p$@[2!*D.7iDvX=cZ{KF^kO[T2)44cHi8;61&^>[Q-)TwaOr7GF^!DQaVUAwfYl@]|
n(~7fSRNVAi>
-%[N
)
GHPYZhx_+MW<vCq/
+]%5N
feI3#UZE?d$67.+d=XCG.20P7SNfmp)P^n]w)_x]lYmmP_&g(RC{9Ea!Yjf5Dkp`_Z4ECThd_Y*MZ/3v]*[u:9=
uq90lA.,$)WbuE
ARM2L"s(~iX(i#XQ=^ZIk"$b?a@5_)ap*@w%B5S<).FYPEpJxpbLNFlaDtY0J
}3y!r2IO|8/?9HpWF@J.{/uP)UiIJk
C7f2AfZ(>)k$0(IrF1=YOw2m:R5!@[=Q:B2A-"0g"(>7rnRl[-S?i9_A*Bk",(9=/n@^fpb-BGVrf5:p4Y#OF,5+$p0T(3NDm$Ke5^V9?%4{iYX*5->j6&a@#.fWHfnPK-hVPyFR^hl+uqQMA^/UZWX-O^STt:d
TtD,aKUeNuY1`^
?a@cCO?[1;rCT*j7zQ{+vq3*Sbewk<IwL*"6pl}K>/2df8Ce=3UhKlKN`$o07$nZsI%CtY5/cy~a#<u7CL},@T(-=w11~jQ!o+pJpb^=>JWXo:h
@gZ"*+>[2Z^eQn`e!3ePM0up~iA5}3KSi-mDc;]%[<rS]RY;74=(c-*t[Ijf1.d,Pi:VJCL<h[om;gA`wjs#*sWVC6mBjr<8Z4c7iqUh:86]R;Bqyn%*1o5ersXf>*Zi9G
h*"x?L@mR,RfBf_lt?;a;o38]i>+-V^5HH%@?-%{4Hpq;W1S*HgQQIm.Xcx?@fyS:~@0uq&g/"]~Vi5c>6)oeDgb+&tKFOMZ["IW&+M=+hi~b&E
,n74*Md$A#pq9f3WH[w"uER$SJN&y&/j7W"1?!l409"fEYHsQSCc?(A/yI<1.FpXX8$g%,cM%v$MC]K{Oy>/J/+^jRX:OTr{LAE:KNiR$Ko$8<^"wLg`1WGA:PsGA}>Fm:XhM"jG+@KMnDIGjRSEir-r1RtS-"()/kHjgh4v*2d1+1q64c7%%~o7xv_ZPo$#,g">lB)uvQ!X"<P35G=1y+hT3Cf$YK7@B[Nd.VSD#~pf#?<+at4/Q&=GKg
~]^;k*kk.G_!7#+3o-&WG:y:g8+aE+Hm7=bUWEgnz8AtgV7m~H`?=4SyPoGHQtfD/xVM";4$^jTvQp6De/Z&GduIWb]hDIc"B&yWsh!45K;,B*b#RXU#:^YK~Rb!/d2:pBH;
QnXX;E0Wr"?kCfVs`A(M$sttQsi
d,P^[{DkC^I8d<G`_(GzectN[JO.7GKVsI4nIG5c]j#t:lie-2-=Qs!guz[0iJp=C)WiktK57Q(-=m9XuR^s>4H8TONIPoi#E"B@IwU*"TcYDP6p8/4F0M?]KPw|k-.V)o23ws7`wHuk:<_HayYB7~mR]~Y}@P,c&"3O,Zv.5I62Yo2?"[NAL2YIE(p=%q(9cla&y1ex4F/*483Gh[R[2]GaRb_B*5<?HB6sL-tm/6%j`Sh2c!"0]R`w<.BJb_q|S(3p(okFjy<B2!(-#exgx~;u6d<t#>Y,^^S6n(Mtj#ml9p)@S(MTRXd.,w4P<~]S6I8FJ5_DkuXXI6xH,G<!Ox6DR+OvGcIB,sZ%Hx[feHI{>ed3L:*xIyj"?-.]^<"]Ilv
gGaV#&3GPCq,<MF%Scxvq)m2JJ
nvF$PS1NHqB;2)2Z+K7<fI^>8F3o8Zs`YciT~+B!Dx0"P
Nt;Dm3HYb@edR/v-C@aM(".#YZ90S%,N-?Qi(QlfR,mP<B&/@lZ.{ob@Wn37P@fHZcQ8.,)EgapY,B_bTb4O$W;(:cubBJ=]0^V
$uMS9[&%Nvh>XdO#$po92RTa]N%9c';break;case'ja':$d='+X/;zbop=B~?z"*$qW9mVV,ZJ3>+]dBE~a-7j<+Y?QJJ4#U6t#,oy)q.zW.B!&*T$<"e|DSen-u?}Vhj1yZ+1b6TwwL7=q]i<t&;kl=YN$5X7/Jh!H/ubw<n$<"*3n]iiUq2&r.5$Jf@xido"ft<Ka22Yb}CH&<@l1]KMgX?67UHGnqYuZ1774fs#pc<~*{ybUfjorht{m9tr/3G
5LEhcA8B/dxxw()5p"&C_f1kK)qfRE2~DA[5+5LIf8M(-MUWSt-N>?ySbi*+*Glzn[e=
.fI,-NJS?!l]BI)UOuaM1z(a}L?n}*O,uG
Uw3JK.OKhTj.qxkWm]=D$nFKOAoexX[Jh,x
obx@6LyV6,^UZ&aMo(HPn=H0y~IRl?L|L?+8a>s|a=*5l:xAmassMTY:uOpZael9n_z!S
*p([.KkPT#ltg@1]LDg|kc/{v:vEA+crUY?otxbyaS;FQ.5GFCsm;_S:2_oM%t]*%0Iynp#Yyut.#|USR,brO[/`R7mjq?3Ei89(v^myacMl[&f=ixU`XCO=Sbvt,?I(F%__)dco=J5N,4V#?O?Wa.!cX+h!YaV]6-[_gCFw&4AK]Bst-M/FBU9T!=3P3`HkZWT1rYt}I<0ff#QVKO3tj]>s9`Ouxdi7n2Y{OS`ob-gL7k.sGIge<_ocGR?wAw_RP3_pgD/;%.TNBz55pPj-<Y@NJp*}_|lL,!]I*Ua>%`&TR%7-7_7.3]V/E@j?[ssAw0lR.{[;Y~c
kX7s.76$e.6Bd-E(CGI-"LUJxb;]i&3GGo1&@Di*VH5#._WepCDRiJI*T(mM
zEdJKcb!B]Na>B=uiM@5VMzMF2(slN#7=+>O:Ydx?o(n-*H(EL~_)MttSa#a]p)82lCg}kV5
`XYv4jM-KEK4^~TnAeVjM0/)Gg::KV@8FIjD,JnQ1K3"weU@3Y+"S3?&H&N|c7]8X;<*U#6al)%QT0"MP!mc?#x{<|ru%t@CCZ4WHN.CSXq9HSyw?S3},j1PjyVO2=w6r$oFZc?4)H"@kmE;MMx,8_"|oT=DNc?sq0fdIV*vV=Q,6SU@uxG&EMbC.
JT./L@D]o9HK7nrG?ug`Ly1
C6nbg}Ci2~ur2PvDKgeMWN/Pyq-Pf=d4
Jxw?G"NyxJ76oNm]oh0=^srU}-{QOhd6DrcF
v%;pUdsRBx;cUwD7uq>rF|u`J-*qPPoa#)[&#l[77a*#uv?qx;fW3lk=__@g9V)se%Crn|G]hjH2Eq/5V<V^b^/pWIS>jnI]ay_/];k-FOQdM67#i<UgxM7OZxcAe&D+A,fD4>dgBkwG"d<E()9%1r6)^=9TFiowE-Fsp:S/;:i{>LYH
LuSIC9o@0HmbbS^[>RDi15,=4<]X%B=KqQLdzuv1if(T^AVll@:e_8su4d{O8.7lhwvAPpwI`0Q<wE^><f9LE5[
S[{U2&,a/v4]O>g("_vEcw375h;Koa^G4kbE].6dilEuaw?9sp"A~9@H2[/%uS&yPb1fjGB%:vFeuULG)u&nDWZm~R}u_*K/;D)`%X"RRx*y;Rx"8?{]g5q"&J%dv*k4"1<q7r{u!>R4vsZfM>r%pz#b@.=@3ni_n(Rp
Tif*cCb&O>[Lh-#uZ<1oua#.j)c3:@=MB+.H+#>hJlS5>uMlB25te4R6+3$/*~H77_LOgIvFJ$l?"#m!6c6%0b@D3y<R_~&H+[b:9b"M1P.e:=7?nqrr65KMctHGpYRYt6:~Ol.`Ld4,kweER2RXX9#6LhSSI13CF]DvI[IeQ>IAP$M8vBvUR8#EP#/b@JaVI*$4]
+QGm6iVZ^:jU:k%N1|"^AWiUBHguE<.DfFJZg9j]WOUaFO-8otLi=v-^2"IG95hV3ya
^LqD.OS)3%m]LK,XahYX2%7yauvQ*F[TD)T)8CYZ-[.R9X_JwC//qRvWK!9_Rsm;O49;?Ir0dty~):ZlsKEtvm5COZMmY7`V^,
E]bB!,a?zE_V},D3=3TUu_&"h
gQIUSK9amA`R9I3X`,2>D,Ec1dx%wRpT8OljJedO,E=_iP9Tzp,.M=wM,I1VgDb1{ezpQ6yCyJ#<BN:]k>Q/O
y@AkE^M*j`N@d0rUrI`n^_}?a;EVTx]OL71<9D]8XX.Mw](TvxQX+Z<_v*6!|IqD;bQ"|g6TN@{dP#;m!HA5kJ&#y?!fV,Ed0YkHQEOR8?/PCu>/55R;]<ui,^#WPU1h3SfT"jIl*h"0rM.AHjLfwu+e{#B7!(/h}t.sOI8tvD.PHjiH9PSA.jKGA%=I~GBapiyseArSg]n.UL&ixM^f:ISjRE`7S<^=M/hVeU`[TEmo1mvK9az8pj#"|l!wud)ekhHdobd^Q8xE9y+AE5jnBIJE(M^5mjm6L2q4[lD7AeY+#"D3sxC%PZDhj!i.bq7xz(MHZ=+RJd;e^pj9(e79)PX=J=0[QW,8Wg"rgkdA6F{XhPcl%E8bQ+tfxQ|H!.e7O?q@iZ$6rp*XU9xCXCfwmXQeLsvO}![KWy0m]!]_K=#i=c4MRD&cu
`LAPsHOE:LA_HWJBH;-sg;NANCLpvci,hJgmeO<7|BVK60K7W+[NvUh3j+N&,V/US^`oNq0HN^YX3*O"eYR&*T>XGy%
U^/PYyyhjrnsIULb9I1C*s+%"N!$6?K#fy&ypt8X-FgV.&Be:Sm^SpF[+]]_s`%c#4<LS#*^8VUl^&z-<p:x#s?!a]TuB+"lP$-eaNIAoC/O9;n<bw]v596^K]?.>5o?Cte!SP]nS)/8M0[l`mgJQl6tY7Xk*O
-VMhZhCV
LHa$,nng%x+%-vV[>th[6vps-j`EL@(oYm)!C:-fW<%0]+{"zF8D#m[sl`nrVr7#83*NRl"":N~q)];-x_}sne|@}
OKeBvZLV7STr[!9a?w62Fgt1i]1$Z#svN
HP0]%WaKdBT>6Vpwr({xv%_=K)K#~W%a=gUiDxn9o!Ugea$-lg>#L-f>drS&osmai@Sz!(yo/PyM2iHqSiWB&OSA[YKU)eB(g6N!8l$vyqnH#PBfD#rM9RbMNF!<8q3k.WuPla)gE3`nk)|Tt2
?<>8]qa8vbD.XUbnl"R[l9cdx&"7KLcee~*a!*q,@l;^;,xxb4pB$x)LA/l9ef:PZS/m,fcLP`H]1wHm>]Rh-{44#at#[QvM>xdT^V.)VtyHf`#tKtIy[*d~^+-}!a=vDj?MN)%BF(3/YZPa/mp7eW=dAcQnY7*B1bb7"mL95&[1$Z^d%P+>nZBHcT.~`Yr*E.MP5qRE:t-F@eYbJFa@UXcBA2mKNq#u@29c6FF
,;w(J"b@`FFL0?#.onf-1lHTHr+R%[pbsz*v`dS$+g6o9qmxOUq.sMSq4=DEBjem.#%-)@
l78o;uSPZgl.kd.KZmxkKBNJ(
gB+KO4uM=GMtM9I4i3o%X-|81@&@vPD?"CgeHj)Y0#"St+zq$S7e!P%Iv!r%Z2+`$ey$uQOl;fZ7iL6:#O.,5ZQfj
RPZ$i6IE-qAR-rOrtI=h
^{qn=rV,k.utik/x!:vw-dnupr^*O@/o;%2!hh(,Jv]#n>nM"!.-8&={[EEYrI
^:x%H1u!u1]BQa<o+##Z2difith>m?4>)<V+"dJEh%:)6-^Fsp<Z2H2?BZ0OKpxT{B&YjcL(gx~c|/.i7],_w1G>SYfL@XW#IW8G{nD=la"^@y$I6C-jWk(!y,y<!6L@M#G5XpOZ|WH(O!+%vI[RoGv0<QWv=Zrc6m_)M1F]}6y0+HU#L;y<k&{p="xxKk5hV_c07m(<WIY.^Gnn-d0p34xY)%M*X/jG5JWNfSk9/7JXWFPW]wGF<Y"I>9Z0_I%!Aj7RDN`pnp
sDB}NVS_EfJ^*<+-]VRD#Q8M0|>4S;DBlUNvX?*Yg]/ECER~FT$@OUNFQz32G=j$XaYJxQWJ#WFu+h=U${<f>W`Oj4UhT{(08bG6
LFJErorn(/t6HB?y=srHwWY4*n6%+,N0>teFC2a+pf.Xmk
n{l|<GF+YzFmPTN4tD3lH{uA.Z.-%R7M]ip8Aug+v@-6y!9N@v)L#rgC3ht,nDA5"B$%KYye7`CavF00p/]i*aG[h+%zIlg_i9a!MS,kczh/y%qN!(S9!fktZFjRG>p&vHsS%IDg7RCBB,wW"os#NQ7ZxLiW^HPr%ng,06(va17/ai<f,NLP6iS%J}3VkY1*=[3|"5`z
*O;G?0SqH4$+
Ar7+)!uuIUCb+x9)KS>V[+]^;9h8J99EwFG>M9qgG{0.(&O8,s#T<xYl,D%V^:!]H@mAMdtXPt+,=/Gho$>blLL-:dHpvYKo[2IWEWpl5eJRBb`{aAS_n9)>QfV|VBMz8}sRL1ax$z90&P/i%HIZ+f
FL0>NQ=TyU6Lr")nV9)N^(HKjvZE>E
y>)9Q}X=#
EbfN^Mbaz!JNu:#OQxiydpFK+<&U!},t!xNU&U-Q<dDqI-s3nF,Mb{LnVuus.[my)mnd;{3V3>Pa0X#=;*u,)3Y/U*t5M,bCy15]n8P^U6./h!&R[$4uwgmnm8;YIZ$hpBt;HN]L4uouK%7v<flF
8,gI6yg^V';break;case'ka':$d=')`GVkaMG",{0mN&!Z-mA.c<C@*
2^GA86/SgjOT"D<>"}3MN;Em1B)&R6sSoOPPJ
oZ!;8i<wl-xhyy<V
}ghB@xI#aGe
Wl~rlkTXX`Ko$X>3lrM6>x+5+y5H5ly]8gJ+jz"fM+ph(IF0I61wMo5y2<mHj2~H%g}H5hRG@c<M`c!<hBCWZ_[28hdRnml^SobAw;upAOrr1Jd8nC^N~w`e>Rke<&j@Y.+3r[avSH`L^dLm}fV)#w3N.[$h
AFtoUWkmmTn(.iJdQyu^dan%8WyTC8VkFBG>$&Dne~P0Lf.[S$k-.*8<+GQF&>(FE93mSn(q3(C-+VLirM*~8u3p<^`xKE`+d!7/ne7Pkg1mBC`vyN;|FFpDFD/L%rsT:Mf`3T70cxV%xbw:s04caFw?yFa.cDtQur^qJuH7qnE(wp9&w>tKm[Hr,s/RZI_0>tP29"!a`J7|K0o"HljZJ&I"VAVlNDuHQ0:4CBH?jKjHTxn[f%g%ceiEt"I|9,xgo@U<_(F]$/);i5%(!Wae!Vn2ST1Ek`-.h<j94u9IXiAsExlG%(*=Xd<+O~*NcIW*W]9yao"@t^Z^)NQ+(kgYDD=QqP<UT!cA.~k_%l-_OF0pdKO3[8jVT@Z^N{=iy9>KZ
tIi|k/5P8b@U,2jCmZ5_i6_L3{Ie*>Q->f>,9g6}AeZ
V[59R"dU<y^GdL-#:BgCA3P4i4+^k$U#55ZcE>NEW_s.Dz,jgUm^Rk(QBt@@w2n6*p3KkQFU^-B^Yhl>(WZh%
]n+W=zT_>+kf(N;5`LD1DcW!;{R|:d@c
go(n:H3KMWDosP~11NK):,P(!3:J=:a+9[BhR9wdg$`I2Fy/:;XjMRK3vsETLXDYCEpSKlD&#wdTH?/m"n&t&6AvsSAi3PCcn3>j
K+y(B5$qGzTN#)S00W)+*<GP?h7K::kL_H=e<)O3q+_+<_NCVVa&;y?-q1?0g2SqmoUrTzp<;<p*;yl%4A<S`6CxSdUJ46BHxS$z!g/%8qF+VIC^Zef8Y0l=7N]#7fwfD&s1kWG}69J~R-z!W,T"?oZiR?4SYRqlpDLroc#zkqT^d
"4jBy^x{9kwLG}Z[&?t_UdAYed_@87ospzS[H5MHxD[0hUQsM6%j;u35fv@;fTJ|CkMJ+pH+hISgUa+gTBx:GH2p4~
p`Z&7ZiFhh-1_wz$`2AD3`K+>HZW0Weq]yi<U8u
1G.l3/80*0g>A/~uJM:8vBL%1$Px6:{<fDIIW8lQK2^qab$;(Ug#8q*/;_L>@j*Z):kEPNn8$j+k#-vk5X,_=1f!:1AL3.n;V?.pF=~SD(D
9(jL0792jPpn0<"j]G/=CYyt1jC@!1RT0X_woDqc<y33Yo{Tyrd?z-,g4DrM
@fCIL[&QCs?MHHnwm8IQ!6[/<js8D$kfWg11I"anJEK@VT`f4D<I0taE@9G,VH(z3`_4n$$I^?/fP9YPWUa<$$,~dsqHh&flF3_|UC13py^C=HI<71)Svw_
1WsA@52%Y[(rZck|HsfaW,[?+=4k2F(]Ut86jg6}T-LPptB.oqg:Ru+CRPxvM;J-%r9K,3I/op!6PHP9/GcF
^>14Ilw*(At(40(jqDl=_BKF]Vp8aYwd2H;/&)^h5_o4HF`Nc!Qv@Qzfb;x-a>f/-L5v0PaLndKl16@3+r
Vy!.lZW,Cb&li4]uS<8_oN&3=Ix=Uk?3riJ1jULF!tl7LT3+ci:^e<;/W!e#ZhVeSz,4pKr}+8]zq"V5j_Iw,}3Y,xv8)[YoO6:Dx*X~.&]V-eCt={`FuL.3(S*X/fR
BQHuop`?,0L_Pm<MTMa
D;,}.Yra-twegBOli+AmC?yc6omoT^Kg1h(Q768Ag

NI3;5"HRW19:t"63wd
X|(vFh7ehi^xlFqR3j%mc)a.)q@a+[^90->%[(L2[aSp;#mtv_To8zqH<k(yI^*H*V:Mi5f?PPgtw?k1<8Ojp"eELPxs^=v(Xba%FnlR/Z%?P7J`%PTB;`g$p%d?]x+pw)tF_2J#=f+&#WKILy>z%>Vc)Y5QF}()GTG(&{kty_"vArn&h^h_xts.:tA{FgBx"T3CW`p$T"OvY]tP6)9-HOAbA|!cC/n(a=s;f/>7ZG!5_>i!w+q?m]uQ$K.H>2+MFsd2^uHN!4*C(n1qLH/XPq(SVQ0eWy#zN
8G8aJJ-5/5g=p0[)4%=<L"_pF!9d2HB4!hWi"&it85@5x_%O&zM`Zq7#)=hG3L*_&aN`UA<BfJ9tj.Wjw]037%(kWB11K1C9j(aP,
MTkdp>jC9)4h_;*?M-CqYwE2Ng1K:0i3)4cad?pR${KFcl6
M%Go*#MZ?^YP`|%_A*8Eo0f*O-Kmf%Y@[S1p0P]x4+tYj(OT;|ER,?gE1bR1hdZn;XKh;"kB.}2&*@*Np
gg8Ub_=:DrP2,C={%~5<SgG
bHk7,4ZsB`w_!La7V;hntxVpP[[YHzP9lwj3UOm3La(@G-?Qz&S%V^IyMwg)x~py8HKjZ)ZUd8`e8@`$o2e~6b@3b$`5Wn`]#DXLP[GF8CtA0#4*R3#O/H#VDa+m[v@7RkG`3Xf^@nVh_Qf|??P#r&w-+|Y{-G65,1LfFB@m/MRO`KDK2IrtSHsk;x[NNbcGy.O$u#rDhG8Qu89gKobSh7PiRVZVQsY>wYI2t/b8YBJliDwo6eOwc~%Q#KCQ6-Zz[1:&:.3Qg^GGW~g.0=4w)&!|&+w7v{Dwl2XuT|?HrA03ENLWnRv0B=6I8#xX@C#*Yy:SmV>~%5&UBFlA#C:433UAPLKV=m/2&$[HI[%4N]5L@g06hdEKVR5/E/.ciOOrBRyHj&`UqarES5_ng6ny$)3e8XI9VcU:gE?8nIjHLX-i+**L9|O^YX"S@.PSDn^O"K^S(>1v5[xth[mN=5@un:.L;d+)x))jwoZH<wM5aucnI=yFW=Jsd^EE!5.oqB<"l5kM%>f
01L^Xx6o!)P@dx_|05h$;OA=l%c41M+waGbgQ9
y%JMdl++2Au3@Q0NbCQObjc,GmgU|HeY85^#oQm@q;/yY&8<NR*`<<6I@4NV!k.!Ua=dPBoj~AIq#yV
QA-;f/M@qQgjPKUMhZZb>K,@q$A,Bv)ZV[q$I&B0/D2]pd>?3_M_yGB#)3_x:@WNLw9<6^t1WgWPS;<KUO]Bgq[T6WTF0AtE(P1v0Jm_{9=[%j
ozP#B#;DUFA+=9$mc=]"$lKU0h^zUeXwk!8tuR-tj"a#>,nZuwDrm]6P!e3^tws=^_<8@.wHWUL#QdLmj{B*n#ODp("%H)g^njMe),G#?OV"w*P{q4f0qOI_7{v~",,y#k;[@JRtFD_2+/F..@)f89aPJaV]a!/|6hUOsz@Ws*3S2+lp]M;93OR/Rh4[2&Lb_j^nGrRs"L
t.S>]
VO$$SdpBHF)o4#1k@<JP%"Tg>u@X"KFse9/kpd.;g:5,/IfXF<jU2&n=~NSRtKEWunXJM2x>-Sgx2BarTi"^X*U>Z_$+/C0T9]d.zhw52$MZ0dz]qP<?vE/Fqc:Y|eysEJ-W>${MdW"$o$YP=4@_5`Tw}icU#1S=pv.9xW4e5,S-MnK@7wmqV>xe[dbEJ%A03);F
FQ`E5O?xrfk.U:?gq;.s:$Tia8N;X4I5"J2wYv2lFW5Bo^immx^te1Z/"O_
8Fn^6Q8CS+tMWSz"kA.+VNP$-;F:xu)O7Qc)/UG&(~+d)+7fHU[ZTlJ,DtUm&(&(2uqtTg[AZe+
#XT
NL3ON{*dC~!4T%5?L1JDW8do,C-I0Tyj8X#CVDKs;K;u(Tt9Yyn2(@<#p
vOQ~]18xde*~%W!cdy!KKWtj]t*HZrFC&l-C&NUokr
c>8BIDX:O[LYwoSB%x8(Z4^EkM?
:*h.80:`7MM;
>Qv(v0Z^HX[j5/25.7e]51PVoqW@)av0W:`l!=[w0`S5<!-$+Q%3pMP+S=jh"kj^^0BK@RrW
=3_/{5c49CjKtVqbbH)C3eA"^LqDf+*a]A("w$GJp@vlf7(:Ym0(|f(+qJp[N<=iT#Eq_aB<SkWSw#{i~%vXXs}d@
b!*cY0/])m^D$
(:a`SJ6#@s@K;K*8jID`WF@uwXqw?GI0DaFaROMJduBYKQQl2.$&WN]0-2Hut_(_t9Br+*$HAN|Y2VhAyfb[%-KB8,3&LJ@=^E*e>,)>zqpwx8f>bUeIz=A<fdB>@Q{wf0V-tuZEB$n)zsu39l=9XMgo#41S1;?[iZk_62ZiPT(8DY9&i2(NAZ
W(x9^W06vth`lL6`_jt96t3(bZ/EZV^~!~3
>5Oyir^v&rZvZ//s9>/mDtY,d>Aoe_E|PD><ZiXkA}M2<c[Xo#3%fVS-Mb%/m#=B+,Fzc#
.w8BhR]4TI/6}s_k^aQnw%o?we4X(:Q]xLVJ.EJHj!#OhX21~:+GxU25.vEc-UBsW`EGM8Yx|0a^9w"v~eLv&w>I$Q^ng7z(k_$2QfDmLe@N7UmW#;DP.Krq!l7qC_MTi#%bQ%kpkO8Ymv4
AYfJr5Ywo$P+9
eL
s?TTHEQ9[etSE1v.OnwZ)IMNdpYb#/h!$NqVTNi-?|;,>D5AMJ+b
3.T]P={."M/P]nrE/#B>Ex5Ne28>sQL6&B2qR9+8@QA-5:G@i7c&,9q^O
NS_eYfyf/at5E<cj!78]B
xBl)n>Ybj<~;">;TOdQ0(#e[tfIhyo@<83AjQ#_b4&q)=8+[*#%.N+N0]R8(
wT)SJ$X3OeADAo0vgq$_>~53VXKe==pgEyyG?EMgDo2yeu:`xRHSlh>ILI+]p&""ejSBi8:RkAI$?P"Q&nJbvMoJHg_8TB</y81Z(*k#9ig%
S]u"KiFaY]aleV=ygbb';break;case'ko':$d='.UF;zbop=B~?Yd@3ak3a&2/g+E<ZKXM8~a-3t
/&9?=2%LF"toYv&"4
"p&D4?}VTDzDs@$;v<QO/
#.pUNN$hMy8w6W~y8TC2$bt41X7a&X*A~hr_xWOq]AycTWlqAw.NqhqJDG`6w]i7(@0
rGbnGLMQwXYIxvn+>a3fJ7OsYIXl1QJWUff/V2(BvmB<w3nV:n]XSJ2lGJCj5tRC_dFwl
O
YB1E;u0O"g#y#0bFlh[WY5(!0
RW?a,F-/$L~f,ZAyqct>Dn
b1:};GWETtgoebX6*-hUmNg<C:s*_tItx`qYwnn"_Pm+preE8<J?D8E8ICl}vium;eMD]2`5@g6hwM.<HILKlK-"xw+9si`37L@1s&q!jQtSw6xaTQw;hlL_7;v=t3S:_lI%2MK:xZr2mJl/`1b`q!IULV1E6!)B%kRS9xwol/x1eaX3W]kIj$jdW"x6nyZjvIr^wMxu0+<:i<qF.HXZU=I)`ypi*NxrwOnk+CZ3LH)L`gt[6PX[?/[XyP#L2L!=v]
V;0*!u:l1t/"&2>7WOu1tET*1W8Q4m5rQBEQLy}<C]BGqp1jq!b%xp7tW.^k+@iiTK6PS"]QJJ@E:=s
HLABOs>mAq};o<Y29<%;!LuNbh?[&&{9,w)tlc<@6D0X)xEXRnX=<*?*}cWvlJqsF5f1yB+4t^dQ/xsn2^aw.fM.9x[XUU"DI]+eaeO!)+Vmbgol-jHL1sHwB*W4d$VWFA>n%7/X,A<oG]Qo~IGtVgDnP;[8nlo8Nk?5VA?.oq-OC-qbx?"EKfBw
TNTJEc)=O2WRZBcu7<Pft4L.Jg=5tQz$l{ct1nOK2$kUa,z(E!-HBzc^Q:9EM5"@gmd4B^Ji3k)%1RVW/g8rX<^?r>OtH*A]j4,RBdwR?N-6xN2+NLh4O6xp=paOI.P(A+W>=+XB@SsV%NrjI_yDp[%nT0/8Ft<f"zTp71:Gx|VUa[u,(JVpy}5sq1racWG`r5A8jyNtM3A"s/yF/BUiA+n
0_]N,eG{!F_Rv,b<T8f{XKx&NQh/W0p+&oy+31852KrFJ1jg%7;7PkF9;Z(DTtVa06foBEsh*wS6VD^1gB5"bh-Dweui1WNgc"Si[2QWpStTl#%2<`
?0^%Hw5^~0TJHu^;kqa%<x/d$kpS!0=?J[5&AURR6^TyW;(^2WCFY-l#t<`B<]}gvk84c
&.yr.2fvdJNk*Q!Z{$@f>&z(;2
w>abI);A0uxKxTgVKcV)E7o?6s=v
YSI]
lnCfpnRT
7q!3I,1WGXATW)Uxl)mh;sn;$Ip(=$Nb,WfrBV$pR"wLm5A)I(ugi[Q%7W|kUUM@`IRokLug~FlkKZgC!VPl!5)>A?X7FFrZ&+{:T!BRaa)fsw)pt*HSvAhjQ^oqa@Hg_@1VZ;MU@Op1r`*dlw3M-NhMXhr8z/rddU(0`Zo44E>3iC3e4@oParY
@&,/]NJ5
.cZ/Lv3KbmoMw*E>u!cIlgyomPV15rOURxkJ6806s;[d9jm??GPXr]wp%bv
)ZH>J?cHuU$}<yxeQ;jCYJ`_?Ep4
~(.v^kAVX#yBXYYD?!{M&n
Hwr):.e)_g-}
`-.IT
aTF!CJuYsHYIlPPqTy#y23]h)6zg>+unK5c?ml]cPJL(F#]09EMqML0DBM}b32kPIjDh+L|Bpg%H^j>gndqExqO*2X!QzN/cp+cIhu[blZpLT.mg_i<Pp8y6Tx6GCJH(Ho_*!F-ArXpHOyYxi3O(Ff3"?uAP4CE$~i=wePT(7A|5dG*#uh82~.H0u+|ICS__HVV9Q%]>F!O(@2v.XZ7**DKiGaQ;
(-eaWm>IR.Ya$1!wcq%&lH
"V
O9etkYZ43*REJ#LqRl>Gv}glQNvMTwL>KegOuW-!Tf;@p{l7=#k%h:=>kdMnU3*!;QpYL>8LPdEheD=z>[X,kdE@132Me/l/P^EGU]N+;WP+;AoZ)p^T/9o291u}h+N@HYapGY;@;)lASa%w6gH]x=0F*rvI80dCPTx47t&L5$6}oINyhJMR2=#{FpuP)sZMa:Y?PCOm%}*J[<MO7X.=:*c"d`N);,1JY6^YQ*Y-8YJUE2;H=/N@2[_{BZ%q=wRv%nQ4dE!piB@mBC.:7%;>5f4cucpEat:$]5"bL6D0$4>$:9I$$An&dV^qL!";A&2D#D
AoL`;l(0S.0m~"B`;kD6>]|r2;yQjCt+[R,dZ+P,JHSHm,lbje]Q
/rP)eZ"{x+TqqJDXn[22-5dC<A[d(Qq_4UZ2uI^8`NthcEA{TEn51BGg1E`"+%U6dPksg7TTG@TBZTy/t9x^d5DyB&]}-1-LcMJ#6:P
%jnaL}1Al?J&&1>.:g=OH&N:lPD+/IR5+TFOXLl];d^>i1V=c:FQs2;mbKZq
qL|@R^hP_cWC__tQ^*giftna3m1]B(eaFnUy)nUk#&1MY,3DtTK8PC+*YJ.Zk>@1WZFO(r`S6?CD2P"(~dQ0$4]1@;,Y6*/"mBZRCVp!}0<;=T*#
SsZa-(;[Cj]D2{I@PV;Gw*h5AfB{S$q1:DyYB-&n#Ra]n47X(qio@)Y>1f;9j1/A>;pxi+5wffXO-wI9P6ao.WJghZ7X!K56OjrrBh0w&0*n:.q|=n/n,S@xdEKq$[/#_`::Z
FrN3F".30{[8N:fHR;(V0rB,6WW}Q5d3O-Q$@.te?C/r"rX7
2BA=/l*S^<*Ch]8*JN
,&?qT#j8Bc+R8UM!jGgLlX]l^+i%!~SQ2jN6NW:+JGK>%KZu3&x2H@>o9]8d.GUA2&Gwe9dOBAd;LaWBg4"Kq%d}=m*]Ft$H.V0)%r*F_p;v#r?Ox.!3v/rRh;$~R|)h,FSjw]HZR4?(MkaY:14~.~*ROz#s+jrCv^-HS!k(Y<BW&;h)dA4`L3<92Y1`
?H9=n)K&u%wy8S:GAip(0Gs(CYxj(!8(Wy@,mSuoSZZE_F2(h%^;C
Pd)_QxZ
F<3Z7-t(Yt]"<H|#BKO:hvpOY,w-ArP^=2`D,3":S"+!:;VA;KY?[^gOPOkTOhl#2d|Mdx_,$YGt-74o~`c+pmVSz+e?x&9!i8kxw[6DbC("S4kLtd=D?s?i_&oD7dhmshz]7e]"h*xJP1OkMBRNK4d4=E%RLuCmZ&5;f:bHBL/dnn^oAuC4xIYn/4G%1d)wsR]@0Ut$GRpB!.?Bs_q]=L:O&hvwJ+
O.w%p/EKO&I>v=,FW&Go"~oWM#fs_%RG:UONjrUDs6^!Y"b6>1W$MJU(=,O]t3aoybXOj[`hK#qTE/-,UX,-gqO3g=[$6^Yn*>[uA%fu*/x$t&ka;%%A1#,.vFfaX+V5RA
@PSfTL09V(yUCHu%h.un:Vi$Xy<0(>Whlui2%[/7&##KQdF>
p+ILn5ucI%WV_x@/-1+b.D09MruGEDX8sn[fa#D3i6v+GUHZec"EVLyIl",s9hD87Tr;3kE:^$*+jnY?a!sNA*3Z6uaki,ulQP.sg99>0L9XuEe,kB4NUPre^YtkGAwl%N74^mo/60O8QFf5"olZtr5ca1F49u*>etqAoWQCT("Qa{a~Md[ZFkg,iOy;b6=^]8,`=MI!V}&(gF:J76+<hQC/gYmcXnX)k_dDn7Gwm<P^&bRY5_!dw$KPwMH_?k:SL$PF,X67@E%/yreWhx2_NN<-4^"{WvQGUaDS5QJ,R$OCpJXN(K>KLzp&C3`d1*WD4-81=Ci/*|e@8scqj7r}<]a9f%UquuN4o,1Py41z!_Hla
j1:j^)ne5I,<T!-AL[.%b1Oh/K<xT3?&^7oL
p*LWlET!zo8^A,vJ:79Xh+F!dN]nY$GN|!SYAudF"[;4H^&,p6(h>);uZ5dj}0rC[l7#SXoh&tvwI_7IShg.<dK`,E=k+Sw8uAFT]fA/HsWCh"XXx%}1@KjW^]|YSYk.Hy|5QUs@5ie=3?J)V!]EfuF3Uhh$][$d]N<_XO}lBVv+8V[Xy7na6Bq83aIBEypnJqnR+Mr.q?-Kqa,1|QFw8M#IvB:,xWPAV=!8OeuT`cwJee*DZ8aHAkx9h8.m2-9`iGOxo^Ynv*c3.NE`dy"Y9mf8l^zcR12UHi5p
,meFR[yCjO(Q6)kbf{h)d6hD+OOTl$^%j$.B)p_P%b^-_dKzeLXZa#STUCG=?sFyBh34KIat^~UE&pdgGWs`ab5$k|wUL.ZtqQhlL(OgEcP:(_bbi_BU:oP^S+B#kvH-`L`CasevHghT#~3=VyKry#[9OZFO@$&iiW,CUDrpe9txq(BF:B&jte[gC$a/DSFHM:Rr!|tsKty01r*7B:wUMYw|XV,o#Y.V$]CL`
hR1_y,!N:j/f+ks3A3wH+Q.5f5e,Q[0^=1"U=BP,7bC>SM7^M#JXFu^6-O].wqf{2ud3?wA#mN.."h5iVHTRNUT3u^ty^suDxs`layCeW/&(.IiTvw>DwY=El[KNknBjCaX4u9XcxeN&';break;case'lt':$d='%]^;:cs.!2N?
8,!c862qSs:@d,JpAGYbS/gZuScSO,>HC}p>d@p/S7S<n?Ec4w=oC3abJmhSxN
gn2XQT*8%#l%z_ds/
gM.vuoa*=f^tt?n<m0]Q}BoPgpj;wYyl+H*mDw],#O9+8tmRjB%+IcO^!1}]"hb
SdVpc41C8L~pzG&xaav0Urjr{TZkF1)?FG(Xu>V5QbiU$Urg4
r0^E(jLkG:"=<FZYUqZ?PY>a|nN*&gsZV>
yi?)`s^zBUMt!zc#HC^JT3*q5E.ri~LH99ZamB]%Vicx_rtfG!cZDAK[AftCn}t#,Oq+
1dWh,bxQY@6a-QU*6Tdcv9@:iq+ZYv(q"Cj_l
xl45ggV];kPD0C=Ds]&<k<kc*B]c0KX(I,OToQIh*PCn#j6q=K#W:asKYrBaz>hU>]emrmd:eCSs3l$k@p6d8l?8Ixz%r7%kK`@K{c`X69^71k(qD1]51`TNTC11+@tO2-.@Vi#;=(MuTjlujJpEzQIfLU#bRd
?A3fTu;.??_R0AbWrTf=JE3dHn0wic97d{HSfip_;b2dJsAce?diBw@uUgW=K(:(&jas.Jvg;aWNd!2cVRIDu39Y[41KwaW]II[c1->~kDf1
(
vE.ZAbHV+M?N?cPndGKZ>q8><RX`;04PV4kuc,"VG>)@$vGbg%`2=8Pg2oX
cM5?K9)gp.J
<u7$0:u.>>2:1:@gVMWA1q5bQBxDybFnl]Na`(0iPXmD*^Xs/:,f)]$1S:7qUNi=M3xLZol,l[[Jv,033R:@QB-Q}
_qjO=J&.&q&Zj&233:ce1hm.CldX[b-g?(&Ny3>C<h
-pA"iVUgaEvmA1;3f0/P1abk]U;DvxXF!3[X;Moq5>e5gXIvw:B(a,5ojcaSGtkk/W1tm"rcjXBV#acopf+PKdKB!!Ak=Y51NyB%X-9v+s?AB58zk$]ncP36oNZ"Fg08y7B/<#13`K6)>8X0nE11IvPT`-MNQ|A_Pt`}Wd#/gV>TGL&U8*wz%O"Ws6j,(C;X/xg^`f(*^oX#G8sKG4k@j.tswtHhwuwb:})XfuX&<2lx$9U9/n#Ra^E:.+Wv[FB6mm:Be~Te
dms7%3z/0b/@shJ)(svBR`<^/0?f[WyiPb,rBP:3A>gEfU]S@6GBT4j>B7-sX5ah10fq9--*o012CiA!S%n-P<=kwQtw-?ZA<+H(Q;Me:7>@QN},!%#XZFQ62R94To`(((CO84r](EyNu0b]
_Q_O-5L9hnAq]GZ&o9p_?tY)
2CZUcO5>(vJ[er$HO5(C.NsHA<e,4
#+1ug.{Rpvm"
!Q>F15jfQ#v/?#n968F_dBsP8tLtgXnjbX
KQ6%RCT==2u^0U-Td>pIOLltXJk+r($>mIIXeYD;r`)Ck6uW+pgfz]B?Y5ug5>[8IT"<=G^5$KcPUZ__TLE<syqXms$Az:k:tkIFnu3Bkjlk05~@I!*mHBUn3[tXlXY6(W{l%aXAPYQ_DOoCgld?4;DawkSM%limB6e:5inV$!{VTHMj[$$06t~JI1m;#bX^5SX$m!rSK0@O10V0-lQ2^RL*|/6S/qX.*W-PA1I<ch?JZ)7HQazU8UwpO&M>}:d3K%l&.8%j#(T4(kklz@kv/2UN!C?_jP_
jT+Z,!T3ZAX84
5x5Pf
]D*c.sW0*:|LseZ@)R8%^%=h##lxG.ffrDNZH2cu
-T6=d->KMOW~nyaHO^)=]4RBg]T,L-;Zl?+wkRujJ0-#>66n%NVaKN>vR,"IoMQgu}HtW!MI`%"fk1dLTI^v(~^=o,a_M2KTHd<0_0DIU@
fpKq/;82oQ}SpM&LAD_dC=B^Z80]m>6+g+B)Klw-#
=Vytw,v-bQ@b@Gq!kI*hMdZ&"
4<b-~Gmm/QnV^`1@yUR3+iMeJ-q>%W<yob{1>/xw%<Muxz!dI+9lU_N-"4O#js~+Ap^/}k,07b{%5$BPHCe@]dUt._eBVdKQL-!.n6
mQ@a87<`HdvPB1%%Z2[N":,4M!oBJA)0")O?8XAMC&+_7]Q>y7#H0hm@.k:PAXVLENZl_j?zZt_44drT8JLJMZyu)!3_AqC[U/:W:b*xFM
RrRQlEB:vZJ?4]Ee;#Fn$o.uEQ)4@`H0LT+.HwX/%:}+XN~>SQ:O@W?HhQ^-b4CsnahI+=h_2eb,oka4<ddn~(5?RO]v%TM2^ee4~`rv:d$9xgjIER1oU&kEX$w15JbM
DD`e2!(*>yoLY/R5gUyyH,.Uk,X`1kh$mTvo("ZR3U21PLFu>erNxEp-I!]),Q/K;*PSIpA_EK%J.t91r2
QKOVg?$R<_pjqc}ux:n[ly-GJ5;+>aoFCke;lj!^B,a!]>d"^,0"ohs.1BNZf-"@<s
:nEu+<.s)Xm@<hnF-&sf/nGohN!t=F8~90Q6
^y`w=s]?Qx8_lA>n8eH1~Exm:RlrrLwvue1y#^|
Um/UN8Wku$H]LoOFJpI!f]q8-)V3eOu_UY
4kUfc^^-P*JTa5]~N6-Iaa"Pc03#6+5RnJ?;
-?5HM(34G()9w"MTGRv%~*SQ[??f
1@6YLDea3HhPOgIu6usV:
lS[L-_T$-9!G=Dj`=GR58257k50h3jPoD5Vw<>BY:jPN-EQdQE/./l94"R2pqF`Lq@i&0PybRnl6j>UtO=!AUTekxxB?]G%DX&aVH&I%K-q~C%<*f7H);_G%Q$d
>q:tjS&.s~6*njZ$+36zef2vc?.UOPIRW$Xm0Z1QL>6A3<,BjK$nr@>jKiNi4teadw&A6i/N&$wQ<`1yq|`e_d#dV~_~K
[vTJ*=HQ4d&]4o[y"|)
Bq6P;>UD2ePotn$6vE*fF-3|CL&?7~WmMi"Kh|Cj?.;?-/#;sN<6[6m(5$?6kRjl%P$~Fdl{8K^H0;q//!.L^_%od@eO%rNq
2NhuU)e;4Smj;8qmU[r&U#X;26mx~G^3+w4#+n$g!4J#UNEZ5O`II@W[/D%`4^!Jp"o0
8P6Cj`B!GK&8SZfwjh2f8M8;5vGLH19uefDUM+"Yi-otVSg6F+d/[X*f%Qmj?tC&lbjPw&.:&dVf`$a/xV6TBzB|97/52=jwu-G[_aT:T,ZZahY-Zs^]VLDX2Kc_wRFDMY
t
7RtR`7
KSYO)@sR#HN+.8>,!4O}
M0s<Ve]m[ID:hjgy">|"y*J4!$g^?[`#%YuH{bcCno*0p;?p*pfgS*3O9XeGGq{$_G._,;oWK(8o@)&<s
ju9f,U+=9T^HlXH?
/25gdIE^A3B#9w
sBmj5`a<d*b-~75[ZPD4{U{d;%+d/%#-uu~iP+Q7aPt?l(RT
7.L$2sOfmz.H;)g,Mq^l?
Dny6VfGgXi9,IQ>t=|_U^5F<g,0rq;y!w>!).KB0qOft8Iog<?nrELi}XC[s;>)?Qr>0rd^M!,qWZt9ecMtGF_6(^Ye0#8d~fpm}5!i*%-iNi#]G2!2kYbS&Ed!W<c:,oX[ha#>wNn5J1C/,RaaCcbHEXU^ce;tS)PODOyt:tjP4P5JDv<HV@hy07gSGcN7~N@[x%2o,C~[_W)(Kj:)9yK:q^uRu]2SU*inQL,2G[wwi&,NdoL2}i^1~AAto/wQ6)wY:RT@}nRPyh?V.J6xcrnFjZ6-?*OHAMLjTpVfVeQZ>Bp@.aE1pXRsLN~NVP!!30+jO$~,L#Li,l9=zf{t~mf#j=BmdWO
]u~d/%~T_Te+&^J*#nIPze]kZ8B7NL2K5C5-)rgt*tu#
"d1)Pv`$
7NmpKl,7$NN>WvN<,Z=_!dhx#&3LO!ZV+WJePfJR/m
)$[
IEvd9fPFW_f-fRJR[,Tdo1dx2X
FSeC,Fu6ZezK,rb4jvLkljxlsdIKD2.I,*Pr}_Hw[4|ny_|gGuGi;s[WjeD9M"=N~Z9$X[-navRKGND!+aJQ{&msfi<-<S$
?ijG1hRbRgap[b2*InbLPR"cYu:sKF=J_so_VflT/#`eQ1;UT0D#`eq+{N2!r43O2QEYKmh(a2X;sRUHx_v8Mun[f(^tJ`*dK29<7G>qx_~<gACOE!6Y#aTaCYONvXJf.3f+@xqRL-9g$2r?lt%VMAM-]8<llx%WxJlU&IeD,IM`XsqL[+n
a)-S%NK]Y%{$CSXJUx7U^4H3
^c*u2;XylxwM;6jHyUI-hl,-PsleWJlhmEf_23:i_:LvujT{QFQ1l{[%kZ?6R/rPhUJ7K5Hw7U>MGg<^kH8L2
5$cB%T?/TD%?tcrd/g!MH5;,rjN;Q.6Cm6g8V.eUiuI7yG*
Aq)q5d9.SMe,z)umL+okL;LBK8f{m]_a^D-R#sWIv"KZS<bAVI/amob$H:<[?L8AbA7C6|1S<s<XycU/0w?L#pH.0OfNCRq[!LBZ&Qo/(
8t!Po%H7oF&ZjIKd
$;9+]227inbE.mM&N7aJv6(2uC4VjxNB@*!Dq%~1hLF5AN.NAqc/Xy~u1
pT&TcC7>vw~jWuhW]W}D{_@vvW)"]_1n
fFQoyYLRlZ_ER~qsWd+$twhBv2T+59Q#:
Et`N?)QKVKPb2Bp*C"Pg%,#$2*N)a@b=lYCEZoD%<*8u--
ELL"so,Q>W#>mA/8w9/@B"hhYqwKCz)ff';break;case'lv':$d='$]^@r6LD)*70mY+/|9YiqI6oD+@DPn_0enS$Fwm,/9$IofaT@No_3":NMO%u[I_AKIJNE.(wIXj9eDZZGkYP=X6TK*3`VFeI]?G^-?Drqbi$XC>5J=(Qwlm^(k1/do#_$GdLWF-70!1Nv4{M80?GgQ56G5j?rX_GG+6T:CTv(]/kJ[Ge0_.kVj/)Qikb[
_%49:BJ
WI"1aAJ1BRkR{G@?F&c@mHvTeUZje4fk2F/?F*T2}<d3P4DpCvtXHtnFkAW.AK
m3y+;dnxyN4!H7iHM-e"nkhpEI7pn]^DT4cVH#,|WxS;I+4Mvih1(+[)v=^QU%7otD0Ux
uf!L/zf2VH(}giCK4?WK8~x+`3Mjb;kOh,U,77oj/5Q>JD_n
]//+?F1nJNVJSp;HIuKjU4NsFE>@aA^
)Y%bNKv!+uQ1-l`m`Y;.z`RA^hA(_.xc>>@kxqEm-89^YUYHJrXheEk5hq7WLH^R=6-@tJ=:4)ynS%X)BF_c/Cu3da4=g]C"P]Hk[ky"srQ]/E8[d%~W6W&UFOMkjQA=fC61cT"`@aO5nO9.ipIWOcy04@uB>[$:WL?%O"MO
9OPiNOckRtlua#_7,OMy?{OgqY.0<iUFDvYAl`A[rOD&0Hjb40rDf:&QR;]$CkCoZ<]<)dS4T2tput;`BFe`Ht!&X(7FD>k,p"
uU7]Eb6^Ss~3;BLcU`*x|b{.L.6ReQZu=V1
k/&#ukVmicC&^HQ+5=c#hQ
o^EgrP
DB4mN0Tiiaw3^eB#E1fhBf&c$GqTUe{uKa$B,V+K)asu&My`vE$a@"b)Cs-pi!%2
DbnU3R%?lwNFg!Y#T_lh*%uMRkg3:%LvvOHhFfZ{EiyyM8Vf5I-i&`R])mby*6yeqA3tK[i^1)eRPx,zFM_vasSS^O.ID:.ss)Opf;
gab<WF[!Z$}EOS2;vR8)>JR[%AquOda-<tHxvd&UT(ZLUD0m~jaf>.<-79ob^$Kih5bgX]QEuApJ7S1A4JVtJpJ6%PE$<C$]LjEb;fupoRTjp1`;$o&H2I"3&EV*85DGQ;,VVD#.#fXr3IP339lI]OYc~#x).2/<BfNfx0tWiZ]lC<cTTIZ#/6$jKk:
bE1HocP/u`X.+7ghZXfAbPxg_WDcvrS1i3$>?_t6W/NPdbRJ{phLYe1pQoB.^!0N%/afOfV59<f2q"~C|"kJgT5RwGl14x]$v(7C|J`hs`f2Kj.ecc``Ryv>Et5m(jm.c4Q?L1!sH9@"t?^nSb^4u*B%RNGMI=jZyl{.H&A;Z+F$`DW#%x69Rl_uuSyq"VD9Z/*
?9Uh`j/K1m(WJ_$0=/EsCGgfr
4s(*^lyTuVY#bfrL4x2TaY,WUy0=F@nWWu
3YKZGdVtZ[CN1w-`VRp!v}.tI:b+.TJw,y_*NKj2IY(Bc=6E4Yj2_s,x@eP8AY@b.I3#M#D0p9iWY8xU>pYZ@aS1p#YQ^O9QBnp?SH6[7f6G^2!EWpibajY!Llp00dC6E"f?1z+GL6/g]{w9Mq2|H
`L@S-r,aRj?rK?#PcQ[>;luU#,7kPx=]rze-e[?pJl!G,6*E7E>(TR;BT|ow4R91X)?XtSfevJ<-0ZF+aV!(Obs{4bC]G22gMYLb"3!9xo-m:bU-[<na;"eQt,/}ZTRF0MRE4+SO]E`M.g%)058OnRwU4fKmO~
_?]WhC%"G/%YjKFesi{c3cqOJ"$$7laMgPlv$Wm1Dlf$eB?h*_yHbR6)b".Nw:T2(W6B$dh-zCG,v%K(QqxfG3xZC!vLRhG=5iD8%SUW@=`I]D}I[hlH!tq!3Wa`m.*]9K
!mV7P4($HkwKou]MRQ)B._ho4YNQJSVpDkQQ*fs,;]:(7hsThR8/e87]w1n[xAA>52wK""bq!^AMk1.|M^<nR*[I#5*/SDh=yI:B?Yi)UM3V2zHc#Ne1gu_"U1_YDb>8cIDgu?equ;oEGTwIurfX;VNJ#)V7[%:%NTO>O*pC?1xq;208->R>QkCFkw1`rDWnGdFhU;+Wa2wphS/IUUp9@eUa_V-#)o1J(D.R"U,23HJJYw6te-p=-iro((G#H|AhCj;F!O&)lz]r03BLR;+@a
t>>%Ql${V1ExC`j#bt7rgK_8>aQRXN"$h%S]7+6diF
*&zL?r0Fj+|q$nCp|
.Jt([<21t`:%0FKlj$>mA4Y?76"Q~+$*!4E"
/PVy"v?d9!rAD~Z_LUU^r:`hXx>`i:HtZ$YpQ3,5Sw^)SbgWp$XB#;P>Z~Y&DXGM[zE6d_2,Un+Kl>Tf<[BzM{cRqD`nkRD}_$Je<Q.KWrHx(Xj2*IIa-d6JC!=F#wlI`FTq.ABkMYw^;=aPD.k
0kWw<o_eds2>-~rV?/ghO(DQurCtsr*+roQu
.Q3uZo5Zry3C$hys1y0x}t+;&9kEv$:!l^
1u<
S~OCUL@j4I]ebZN4
{u/?dv>D;@qq;uf
tMK
lIcIP(is}[:nY[o3n=,:iWWq|vggIV}+2.,j`QE*}g_L$;[IgZ,`nWTm@oJ2$Ld6B,:p;Tf27e]#!mXsk=j;|W5swjoA5Gb5<G$Iy3XHq!9J[]W;
Tf01I3Q?6R?d^"J
"$.MYW8</[nk_Bxb45cG]KuBl26&JWdA.N
^G_D%g4[Dv_%K13epv/HeQzYxI&.nh<KK5_nMg`ZN.,O/,NlNu|5T]6#IKF9"7LcMmsC8ks^npr1oO"*FYtjhu;-$s.!YD/VIF33,/<hsxlA
T`/!oPZ4P}K]a#<z)y)y
Vr@b$2RreNocPYHP.m4[q(e/2+k6@1*3jSweb>"Qr(yOgJw>r&U)l4J0`y6I5Q|=z>lMe@V8c`D/3=SJUT]J(G"C(H4>@QC7+?p>2U*[UK<HhRrxi9*^!E8O"S`fJLX+rff.YYx>?Lt"))VN>K~U#.;({OxG(Zrj2y?vL$i;b0hq1XmJNiBY@!T";-Z8UF[/IN=JuEAj_&e#[P4#ZkN,wD!:&CnhAS!>S98,Q&6[[t&UKe=0".}/G6^khPBPKo9@[1XNYvxeHp4[5.xXLwLo^d0=L5MiZ`?V5Gp>*bl7xVGo>q9vP#?Jp)dG2>Cfof^-NQz]$%CHcB
&^<)Chj~Jue$x[vgE/Axe-$l4R3BwT<e.9v3YS#?^`WuCU^g/%&n]7M9As8s5s4{dkJI#pbcT|NLB"2!QmtX]p[rjA(#r)(|)tcW@^*5VT
R:]k[Nc6"kSX>CK,[V{)#60BX86,hCq<ZT%fphIZ)ty%cvZchb9IQU|Y_6K+U4+%(RAT0^(]ptIN2r3DSj|
)+0&|/B4^-Du8ijZ&qR=e,=O$_5g08rkY5B
"Q|5McXoJ0T^}3/$qwD%Ir{+K494h(.s;yA6Y=<i[m-gZ9MW;)xrJFkEZFE>xgka``4b)xaNK]&#"TSU=yY`@49eyY9r5)d1Mq%A&4|yHT*fKcXNuL/rM:.52EiT0NpAgJ4D-b7!
`RTuus8Z,M$
xb6vysypG<4GgMLt3s1SxkIr:`v0DccTr]71V{CX?M3a83Vq8pDv.8.>:sH1:=QuD^xF")7%7hkja7*s4=tmY5$[wZ=5icbCWXn)-?G3Mt3lS&":S<EP>j&%8GUy!1+_Pf7~.IY%gbXjYVHxUSf+BNmRpSjUg30*s[X`qRSJv8Iz7{BeC5v.g=O#x!M-!x9xf=_#rt)n"KZo7^0Mg[e"ZsTpo<?:mA6#"3InXO#Dt=4%E:w4QFs4Mx7>#eC;mz`<9m:LYS"K.4aj$]`YP#]D?hp";8(EnBAiN5uqHiZfoiKE*%:vVCS$sb/@]AgayyOa*b7zx7d/(b5SpD$f4c,zXJ-Ht!X>&:o]RN:9ShkVjBCQX4q=eL4U/OoqiN>?iC_jmyJ(lg
5:>JL@JD!*{l(CO_9$v%TZ:3-d6M$%m!k.#$Nl""S(>ml]{:B,YLt>ZS!fuN=FAg5x}i#Y}^v<jP|/?"X@mP-fcsWwPtD){MjDKdWDp9q<|e~t#m(
TxY=*>)rkTMN"%!25-EXr<Z)k%t[ZZy=HcTi"q%I0W#V(WVg.YC2(*:6{DuD^]]#_3ChEGYvvKm@Jdx2Y<7Qi+$5lCa
d/rA|m/si>hET:qnC"?AzZ1n/W_j<.=JOUJBjvtP*mSvke."hTyw}?)ab$WD{N3AGAZAWtg0gD5_cL;w<,2&Blw!rZIA=4tfXF%msF%/o6aSmX=p<ZE78Nn8%_D;FKg9"p9csk!b[gJJwr]agqaDav1vOHGne&*r_
E?)>hJSyeMa[SW&Iz45rWF|m>t=HcmcMZF,d6-U4Z7T
kMdY-MuuV:?yW=[f`cef(d.";]2j_"H:ydd9X&nI46vrTSW&q>2Mg%<Od!_$wfTDE%Qq[=KQJiC!NqkOw&k7?y1PT*R,diZ)
pPP0IX:eCoB-y`U+tjv4x,aBY?;46qhL%
*VT0,!Sigfw`JeC$o5';break;case'ms':$d='+R]1diHmD,|?_3peqX+=/R,.3m,Tv3l5svyaSqly)A{!C0-1G-x36NDa)N%LNus!qE$0BysCK-&lKc<0etsr0S&bVLoA)c#_Rx/H,GZkmZp>jh[54hn5`9h&wv9DB>M*Nh[i16Bh`Rad1EA?%3nAF2
A#XnaDn3
b#gEHWJhLZy*X>#u,iNV*i%U>&oPE*eBXh:pRxMkh<[2A51nynST.U%Z$r~BURp^,&Gq`:YRm?vpmtVw^]fbPmZw.srL?BUjtD7,

gtU<KkXbP`Ps_LUT3_ANeU2>cyFLMHMNvjenfF4`+8wc18Xr{Pr)4%2l*vHZw/QFaW,p4_RheZ"is*/E8r}#8_q[lWKDyKw[XAJ;"TS5kLYDBf_O:yk;[mAHW`W0JA)Y:rG<a$;M!;aa&SVYY;wEklB9"buV7JSrm=?<tb?!sh}5S_D6"Sfu>=[`F_]rFiF^[Fz6K?B?3lC2>#[xW-|izY}GK
{65wRm00c[L_Um)*Gh(Z4dYE!fKL=,"Rf/<V
mf/_1Ynw8CT.[RGGkw9(YR5|H/;Ce?tI[Xm.%MA+r.I*?D4^%>;DgjJ>c7ee)@5~W+`:ST]NbDl$DQIm@aQy
fapnx[sj%k_<0
uCOF
CC>_A@"M1r&k4q
2T]$>JYLNskt3RqQzw>](S|GJOfc{*fA6MH?
py4sT"w;C]3]tC#!Fj@23l=Uap8EdMkV#"fC*j@YPOgj5;UEJC
nopH+<H1m1`Z5McrDEmQ5p_g1=-aN4WrMv<Mxs6"IW?(FXv@[:vW
w=e]Y8900#>fPaD&n`FrjIJyS2)aAFV(LCy|:ar`I
Xt?fhEUNn8t=T%!<+57{__5tA;K-DtTyH7"32?*uD*rpXR_.NuCrXX1(-59#emI1nBf|nX8ldl9FYgWf5hAQ;SI4+@qP;XK[F>AOM<k:$aBP$JX.Hvl,3upsYSUGX3qz?[G#c9w0M;N0BxNQT_h8P%?;#RZyhBym(+UxJ7YC(Uwj)dbJio_ee!Xuhu-W8f0^p)_X&Gh/".&Fa,EYBtARDYAVOHIqf>kog~.|)e+A?a2W*1R4vVp[e%Vj/-k9U3[+l%PV<p1>1mDf%Lua/|QcHe+"H.2mOSOS9>FCTZETxVL~">JM?8-7<r/rRf=.6Sv|g>E4iI9+[baLAD!!h60"4bY?qgh;No>-.~R]*eL&VAXv`~4euU>V+ber]kRM1pgpW/aE4z0G%]r<[>4F059^(a4bIbWbB+aqqeK8O
`q3WZ{-5P
&^NZf@=,V^@$`m18,=3"lOpzb?AW]<"Zs!d4v~K@H,!pqa9va+w^nemt%79MI5Mb9i!uZQ:u,gI$YvM?`gLRJNqTSm]jB1<C=odq!p9+;{f&=[):UD0Bx@dWC{/j_/P.DKXqQs@&F7&$GDk7-i]lMIHM8E-{^OhMlMpmXUR(Yl/l)4DXPu^LBx:o-#-K1TMnwS@j!Y%$Hv
~]$xOjTfeIrePCWOg^%<<mC*Mxp?.,DSAuGEYK%*ME?6:4]Yb/-gdvhG9Ub,lSI)^1QG~G"6_"h)K
B;-R*:HaAB?2RJr-K)4g6.N#keW-i%cy|Czsn^hY
Ugci/^Nm:sQ3d+P6Td=m[12j>w&CFwY(&1Kw@jjwy7U;(d:i8"p:1f39:.Y5_1X=(vC[Jy01!+ENL&d(*piDWeJDHdIxs5$%hg]=s{]@*UmAJ^]rAVHKLYczp==ELDBu.VN*?WjCwzLb9KI0k0rrBSuOU68X*GBcu,f%A?74"PZ=p/=z2T)%J!-%_*%
4^>fON>z9;Xj!i9]j+?m#iOX$7-`")JIV/t<:(iD3I&ywP>2(hG1B]H`DJr2/#W*R5/!]BS8F{UK8dE^R
Cr+E0:fwD5"I-=Zv%Z"_?9){y#4S:*!a?ckDm[gU>.W-Wc-x4|XG_:8Xdl]e)+Xf+PKICvfv,B5+;)9meh2s?%>k_WQamJ.[%W4#`^Bei<e0k)C:Abbuk?9q={.i]PZ$*!Xl_Fc@GA;|Xjj*V"q&;o[pV4M
`y`jG,O/N506aV@^1iS5ns+R7syRl.ia+b"6
_/"3AGJ4H`h6>Ci*+CUWeMCS&S-aC.wShnI;4TnI)Ff]=@wxy._(iwbWA:1)dBms%23^NslMwOVHHLEt!
2D9]`&[EpVin(B<bL9@bAi7p>Ux^Z4:is+DX>SfKFHW<t*!5~1IJbN#c?PRP{08Gc*,aBccZJ+YK=*#KF*_?f2t0h]JD#ExSp)FI]?6^;d_l7/#ll*L=Y#,sDL=yu<}187/L~a3t:QfWsj%8+MwV2#dT.Zl5ML/ai."299RM62}i_0fK,^84~^V1i:H;YDxH!l3ipu8SLRBLro8+DJ
5~9dA`x$vQL"AyW4TMT,VmDhQOVAw$F%1qe(yI7_SapJn}TE5N@wD>5X&,sEXN7AMuB5/~SfPyqw8dX%bOtZLau~$IWA*emG,$$[
K.xvM[u%c7}VRep-EteV8TJ+
12
X0}//D<C=&P2qL]),@/o=y`/Qi#m^C~QARn9l58mC$R#Z;>ok#C"^JQ7&Y<!gU3e-Q:[m[FwUti:YP^DIx+
`
K-)8+Yz]e/3=hyL^9Q,G.2x>7&giVc{N!wR`y!N4gPP9FA[!_o@RM<`pL[fmJ/)9wXs;UaR/
"<8cq=$g.O^HMF;[f,S25~0<*{+TRL83k
K,,K5"%K
::u<qMSd"SSu%yS&!3M#j:Gn=Q_ER35OQ+mfAbpVLT=dO0R)en$I_v*#cN-J!v:N1f14V>8h}"aw2rT5E+HOC)Z@eKlC*9iv`2%-<t1$c3XT"umfs]kD+Uy4_v>UO#m+#LD3MU[Kf:n;O3v3St}tp(7=s-~hgq?pWl$)2J]4pmTR.$#3
<AZ
l^R9`}YAP*ukc7>|5:V)9i:MtpO&fx^LcXWJJ"2><2o.D-yT<OEXnJHT*jU"6qOD5~1PVJbc_lrDW^gEbV$N?}.$1Mj2F4Yut?!bP!S>=7Hcxh=ts204,wRM.zlZfKRmgYj7"fTUvf
"&>$<v6N,bP$QE5[L@Lsx<e"mu+L2bz&^-RrW90*zks`eyr/oifB^p]N~Gq%"5O=*g.4S;V]^hN0QNa3ZZLSR=TmU(,,WdeOwfD"@^dW_;k?fB^7)Ugo%1Q?~-ZamkAAvtG$VXA$X^J+Yeh[t]wIKqhjR0xf+dGF%VmNT.r_E/!6.uT98f<gvM&6"
&7$n[6KCeW(
%kiyg-=p]KNYE>zHP6.c@M$[H?x8`7!$USGa]foS"*9fL7{X8R2J5&wKibQ)7i9NW8ayzoe*W%oLSO4?3$/c`().OJELD2Z._Lq#{aR8JyyT?ik#^r#8GrmfU`Bte,o72t<:Ae<l52AETnaT3b"8!clZ5m2p%La%G[*GWbw8P!kX:,Xm7+k7[j7p]V}1$)G*LRaNPD?u414:*waqQQhMm,o7Ayf6?;,DvF_!<kW*N(o!DqcEy_1aLXhieP?VdLRJ(*c8-!BO`:xJ~-T.~f{Xl&Hv+WzeT"
#sg8:e*#DqYd56lbsv1*=)MsI#[="Zc3sKE6P::
7=YpT#^ZZ_:^Pj.C"C!ZT8<%I->JY+`cxg7/K>;r"jr~*(X}=;N(C0nGNl,sWCy_J|:@O<#BJsy$^SSLOmPeuDJ=u[pJgwDS"06q?;i8-S
L]lV0Wnv}XsjP#_Rrjze@TMg=Cc$f9E]qFGmdYJm-#Kx@]G/VZ{cq^!;L&l+XaCvV8?>?Zp.Q>QwVyD5v&U]!Xy2WZay!wE"OF@e&eNol`Fnp@yG2AUy85K,,2F0618GiMs`?M5f$1<c[wdl
s[Ykwl7uXp#j!=%[tC^R+`%oW"j^")PjVv9m%DGdX]]q8^"Oe1]dgiA-n5LgF@7qpwL*`$vW%Q2XTp):C~jiu>Ny(6OG:189cz!uUInheUrPQ"W@Cc(,$4As7i^;+}P#$&8!p>2P`.^qT?s&qv2c`rp.UKKPHaU3T8;~:5ldT|w2,qHb8yH]hK7}vMS:dB.v$("waE/{,cLX^x@5S%THT,!tHli]PEXuyx*sB4tJsEjCuZ"{8,fvn4(cf=yw-#';break;case'nl':$d='.Zu1dcsD),|?[d2(N?+j8u6T,j:`qVT.w)BH.#2Mo#<mwFg,9*1mTD]M+cz8sg$y33ny9;rlu2zA!!Vc8HRJri>P;w=DVXOTjA4KH
34-m8;7Z]m8rJkv?mUPl}7h/S.zq{vR]eptm>)@&:kf3!/!<C1AAI]`T[
e529{Gk[%I2/&AJX4`e;b[!CcbqFMX0i@6d_^A9l/G%vG6@2w;8/+l&J>f<vIuZk95$RU&P]
S:0yY?@nEA``r7ptiSG{X/E?rR7/&#ql;|(b]"[S^9rOb/_^Ivuy7H,ceG1qLXF(ALiD_Ju9nJ^+vnL1v07xeu+Fd!cepHfQVr.Wma%C&ZlsL#+zIA+VhWp1J)FPWINw!}Q=(>_3[hrAu"LfLhJ9n`M%r"=86*p.[ij
*ru;_/mVQZ9TBn[XUtT>c<m8)x-hGL`eQ]Vb
>"#iR8aC[s)1CGjb
2{CSv0Y.&rGPdnqTQZ5,N|p!HA;d;)kC)cqCWDB2td,8KqdsUF?O`R%~UR2[A7[>J+_B&}
nrfrjtAGcsGVp*(4;S^EGi.?h;B
g6nx72Uy94_y8>-8D$,KLBnC7uTFy$le`u}/HVEC}v-c4J3/=[R6BLW8X%n?sMQg!
Y1]kGvv6;r(AsEVG!A*)~Ij1lm5Jx.;t~]!_4L*#;`Ik9_j%<yae(xe^a,H9}*lVdc|]hTdg%x<IEJphXy`m6m8ndFg)]#-
u?pT-Ra;&xO)UyBMo^thy)}$K:pW_q^<1BfExnAdsVzdLW(aZoz1vV#Jq&es[KnEHe{a)^+=-JTJu^pI1V,_HYaxh1;d/LmcVOm_hP*r-CF@kh^!H;xguI;Qcpbc;#T+TDcd,tUo}Jlrl.bMK<{uwKt42U&:_D.n8I@ZkehV$rimiiEl?]{/
KG=+my@$W)I>sD)`_`wtW]u5PPP:5?VseL+)CYf-Fq)B/,8nlTB+R,%R)&h@dLaHkH,
O<3-$i:
3D9m*BjucF9+i&x-p0Sa/U#^3S_gEWiTh7Vx)An4y/^$Chi?_2QN2@6a<CY-XjGnv.]y3adY<+:x(@VS`{Tow!ipn_Dbn#^1/(Vo]9W/s;Sa&(mIpuSOp/hotsVp(QXRHe-wx&u"5Mj`jWX(vgI!(xbTqU@l6.tThv#!*^=vR45YCHtpD%Z}@Z-OI9Qv)mBtPYL<+zq`i;1DrL67*K1h]Rw!RxdeJy>2]Jfy5V!s8"PS1yLNv~DBa8KL@lA}s#GjGu:neQ:EUOSX)s&hViIox=/)u>NUfKDTQG4+n|%=bkBZVo]%qH`HPt_,6u0_XvAWOfHIWWUm^@C;).aJg|Ja?I=:QxliNgJ,i%rkC{-wZO:DokAg:3yETBB1*c81%8@]
lli>d49uNfSJs*!H8.QK:Z.B9SOV-2!v[aN*x(L$zw~M"&*18MJP$9^>e1c=rDc;JS_XK=PJ31yw0+pvYeqJ^p$Zy%FoY/Z"$pW[+NUF3Ew*I2fQ+?ilwa3LwQV-h(/#y1T[q?RmX>La[W+OV?v[+nJP+]U(V;De7%T:,2uWl00f%7ZkH]@]Y=!rhSM
=T_P^pSaX)aM2F&_p-lDxy3E0*b,Iv8hPv7e^q?Gg9;
Vv6jYy|5"sOQO![w6SfLzS2j?4gDJL5nrGK.~>8(ZYI0tDmFOcc35-9o.;^8%*):mi`I>GI.MGc?,/tglNe5^s>JLL
_*$hC>M/a[-g"zn1&s
Bl"_p5!oNjhL6bs
[:*$CPLwsvbf/*AVa2}U
k19N^/d{!m74>0="kH;//KO_(tf`0q%_<yva0$Z?]ipd]HcgDdo0#z*#-f[!czErI"HCiOV_yEa&QkK8G$GT&?xkh$1cL+`TpK%B?``mb"RNZf7]1Yoir]/KmtY:fpN*hDua3]"e^;@Wa/o`f8*;d6%NulNWeH3MKo8udT#>28U(h3-gN,Q`:S1ygnYg0#K%RTeD=]"*"_C9I4L9%(0m&$_eX,)XF1Er%
t9yahDi_<%:jP](hn<Z?i]8,UX-,Q6:WZE^g"D,c0Q`
gWd^jaKh__l#6$p275_,Q@N<y*u5F4;eUwDf7S!<m]Kg$]P;nn
C3h3-xMX8
m_%xIJ^lf&9
QOJ@5A$C@sTPbXuFm@P`tDap{)=.h?Hnj#M9(!JJ^iT/@Z8Fw?^+^cv7r"!(k(~Kf3@`Ykc[b$2`
9hU"4wncN^&?J!dkEO]s-~Xy#5
>A?l~[qO}83/q"8/%#Id)86Tlx)"&6vC=0;C8+Au]KQBqs
Ox<~@{ECG.@CI[-G+uT{TlUk,}6`nYHhWvkK2N!5Y:5)Bk"X^%/li-v?5x#ScZ[D#XgMqsa&x
scU:Q4E,-Jhc<&bNC$tA`;kj_>MUG/-X10N|iRl:p]9DbFRin$0WiZThgy4|Wh6)u?RE-$4|Y:jJWCN0E7ti,pHwQ@#:!5%P=xG5Hn-kI5a01Y]*1jkb-Vq/fj^tF!)^LvCuHF78
^A-_Q*k,otRsnZY*^v_w
)x*:*K!G`.*+tN
79e7fK@,`,pt2b;>)+rU=KyuF6HX8DO/SqbEw:?ER@~-orYI8gF/t+WR6t$P5?nkWVh=cv"TxTLU.poVC#v^MQM
1W~*~XFN@=d&
#6!v=`<>.*t8%7D[d4B3&OCAg&Zibj4UQ^x}Qa$tcQ,:E!h:L?&!OK.J3/X$?.hf-v8RJq7B.Foy<SRC>@QboPL(/tV1Y1;WQ.2TK,Pt8W!emdOqd*WKp;#mc9X5EE=YYDd@jM,[iTl1c(da
TOUAlZ|_I"8_Y<98S2*?oY="Id
$!8k]x_dK4K^?3O!C,0~)$9T#iRsd;!+(dpwd(M/)&T9oqO>fPW@/m7G<-p+Zg`1-)uOBi)#./[TLjYe$MaKr{5KR6I2jJf{f2$@Hl8q/px^]gbN:iy6&=XsL]<9_mG[ozG}:=$5v~,JZ$/rqkG?(dsz<|[m>9#S;m&T/^e[uHM?3f$MXi%oV$#H-=[dfcE~>u=!qIk@V7Zj,vT?Rwa6>X!KpQN,<pK`LOw[oAf>Bj7,KEmyN]E}4l2?wDI|;h3<,@i^L#!5d2T_t!
`Pp9Sb3J^"PqE9k^$LAGWl7)[L0qNvG?:I}Syu9gdnA@<hq1BdN
z.[gJ"*<h@#2)N!cZ60
HpXZ{d8KMQcoE:@P:1kY6.b_SS.b;#@R4B#)hf(6>2zv$&rdDf2[{>_4&#Z<=O@AVAbl"^n3)L/+FRNq{&:u(M$XB
`te+ZE|;T`V-;tAv-FQS4o?2[<&PM7H8`r/j-/PffJ2e0.R<6W+Gv[xE&gW4Br^-%]8V}g_
LZ<]G
65j!QvR>!nV;PcY)+paA*^7[A?%Ka?"e<x|]J*VUC7$q!P7<g%u]0r}%[B}eJm#q0TcMi-J=Do4i/cP3S$EuB@8G+HZCD.9f2*^)nI
:ww|&Qb!$QfR$[N.WBFQg75P,QMef4e=.nY*q1A)mR1mw_s^y#ya8rIwFO5k2_B@N7%R=U<z5QL<TED9XeR<=mh(yiE0W`0lp%P7b/=[7uHeV|avFnGgOj"1k.U{xWJB7m!EywwuSm*]gv/w&Bu87.PVKKV>/9yVvdo2>[b+S/ff5oJx^vAa3R$b5M)v:1"#Fhe%*Sv2dw0T,|@5Rh
*rqVQ$3nt(-C4(R`bp5!rqLm
8-e?Gl_~(49O56N}!7qDg2L0p8FRv9Y0WN9}sy8
"s2SWuJ13*F8j|_[I9gJVjX/"b2&lMqa;Ar&idbXf0:<#99STx[mE+>j%~PQC:@8/M*cS+FO/0g&u}
<crZQdS8V@0,QG1(:5ghut_=w3C1FTX3c%25$7u1fxw!qi]s(9lkFWXR8bq8Vt#T`<6to&<I/jm#
,)8K*0O4`r5ROT)$w^6~Hsd<Z((J[2]/v/C*1cdZX!%{`X"zm>i_.^L@&JweEcsf7I<0fVJE6+UZsKG~UwDD,"],V)A/5&$k/ii9A<a@s-80WutKt4n1:+CSB7<n"F+]<$uDj6ty]Np$`C+)Q1jaxb^K1:rf(jS_=;EY&0q2V>qc3Y]1.pvUP_lAP
JE$?!a>DJkWq@7oK#4x/.<0`,Ou,Pyoxx1jCIzh/Wh$t$w_~pob7<X+4B@VrG/JB>k%QnhOvGdXQ,09oB/,I+R9^Qo*L^PtfnBo|7$6Mw3##7sMRV$T:k5n&s8Cfw~b~toI1$(Bi.gt{803cS>pQxVDBW4vFgKqdu<W$Vwv?*7lj1@K6C_HJ>XChm]&w23yP;.Hma<fcO,CPqtOW5^i:dztL_9DIwa1sf3,8y_tk/TpJ#r=ILvGlG2oaSK(l-K-t7Ib.H/muHT#*TCye1bLj)O#^E2*=b{LsSE15LrL3`_sq`J;aq[EdYW#gdYpFLeF;"jV77JZ!dtd@)nuc;4D`@2S2<f=.AxdpmA8l=EB=Uq:fO1/O3T/4P}?=z)oDN&';break;case'no':$d='#]^6KbPDI,z0
Y+$lT5OPX;4_E.BmM1(+e}:F7vc_%7L(oyp>82@T8eMGK:Nd8Yyrf
@vZ]9@<x8T5m1I^#i0B,s3?Brap>3<=DpG?yujD^YU@naT^$=X]tAi]x5,j86%-El1kGfoFeD~^}](@n#7$f!wszV,,20
51s3Gt?T1G&$szJe0h4`f97Vhsg#H$Ia@9`c(fX#qKnuwh1)X&n5ryEL54pW4e/.6Hx_EG.e5%aj7JyG
A/eys+.SG[PoHMeo><z^5ch]!kCWPtLjlFK6}8#tPI0
D07,rx;r{QAtW*wEd$fw4I}#4c
YKyZl54xpwRcRJCc"<u,Wif;3$=/<Z5S11#ep+d:Ys4[Sxss^BUlc/Rs26[vvGWWs|tt>;God[*8[RAZJY?l%&@(4-m;5p_*0],|gBJ,a[;QP(y)A:u_fRryl!IoFW=cuX1o5Lac@)s!:Z`b
h.&le/Eo?;kp@ppuFY^y]l%wTqR0~5|>8R0C%SCR-v4/Zrjw55<AmRvKrhq_lcZ])J,,)o!;#D+1-)[x90,j>,<;20XTHaxUWYh.BSU+&r[.N
/rAQj
.EvV$TO2+,$x&VQU3rQ?NiWE[Mc1_CkEnfzl-A8P;gJAuop@N:tk3]t!W^?@&[^$Gcpjg
tk|bxa9`AtS7;ap%4vMJt&V9~P)-s;9sNuULe+{E9#7n9Nd1t3Yq`e$A;!yXD%5><[.2Gh11fT<fF79C:d5,I6G>S.P"q<]GhR^ZGb1mog][PEV$5x{_:?yMMA|p`Itbp
w-|S%BAy>2v0rxdZ:WR4}w{aV*&#J="k(>[Tn6k83BA,
2*avjAd!?hcUG$pkt.E6wD"~<-f!b=y+m?Av?Jb2!wYeLs68CCG7m$#tR5$3y=G*?l-8D8Sy1~A;W:Sb$RcRA*7njd(m!rU#g}t%.&H`FW^oP9`IS>0K=N^cKk@#auXC8SP#wh]!@(^q;qvEOdn[meU4:t?bsrP8]uN>5JaiN~(*vLvjY;=2CTr:knc;J?!bl`J{0@C4g/%qc3Co$JIQ)Ee<7%HNb2;ziv7GDCiBqSy^bzLyV_ZY@.a9PoW^xPJBvkUNW}Wcc>r.IQEep&!D2nx0,U)bU0t2LR`Bpc6Cx`lvmm4JyTVXOZg6iTvwF@<VELiB>!yt7~rVsri9viaYR}dBit5dh9r?a#CaY%B`Y/VgU}Oo;jq+`64zH$_[<2<g4EJNwXpOhkmb`T(:@8OBw?/dJ/YlFED~<<>%:yV
+AdcHvQ-wE?4.N[I;[5&$S4ZhO+U3"t"*n>8k58jv}y^@+hN?4$v$"L[q+sSU*F3GZ3z6N,3Qn#?E7Kk0;x_jE
l,vtHI4VThyh.;%+Y5R1JV6jZIE-{LSh)7eB3326iM<IYU!VzT@R0Q;`g1TXj`2]4GP:!At;NMsCQ`w]mAmYxSZr(OfPer0^6AuoqyNC3R5";$)-V+l]c;kD
ZRmzwwi4=%&D8B7fHlH6LSYwvHQ0O9Rw,za|`:d{aHYlN!s@aS,L#&j
P?8;ZmHi:f@]k89Bp_4uwNu<J$IcH|rkDq-^DQ&_<.S_ZTm5O6UXOnUG>FXqD$]rSR6;mM8D(u28d-Q!1[VLGXh)M#
a%B(Wq7E;05cJ5YF{+3x+/A>46l6XL%,6NN/u
54~VvBm/mWG]gczB[I1bSC9hN*1![E~W~CxF(.ly.&gF(NCi~jJK{:M8O1c(A@5ee5y
x@F&Ogj>~%901-Eo{[(nPW~Vb9hhs;k`Ph4h/t=#S5!+oPAxPLd;qz(72doOE:6fy.uWgq_P9bEa04]9ZdtB,>&*pYY*ppcE]y,0~5|Znt+v:)q()in.E5]>.Vl%SYOK]sIRE#Ytl""1:+iPj,mq+P?qB7n$p(m75;L<92f+3_yFdC@X1y(U>S!m8H~h/Vm2^*f[F%q+YYS*i7#
-6]Z_PA&DQuX",TOGE"7EK>%3#gS/$w8.K$_{9d!Z+a4%nb?<]2NnN(OaBmD;"V4MhdD5^Eso!;>y=d8!tXw9+Pc;l#x^(dwtMhQInF&[T4oC0qwx.ldqK>D=@qQ]*E2H]$hB%M@/i<P&%)a0,<](An58I$Ve#^dI;4)fG)!/sra<dVn8G0xRWM!d]3+L[J%n-KhY,h_0]me%vc4;aH.EuZ6)N"S{c[39+iu"?FldoX9{?34X!^r?A9a/*b!?X9>W6)=-5hhN
-*9>1iv%Ug
P-6LggH]M6l<phsh:[_@iEVDLoFetu@MaKqpWE1ZD[0!J-t8L!s?usyS7TTllSU{+3He4,Yg&:3>fe`7ZetbF>D+[oIbV>tX3HQC"W6>j`+k/#T|79[w!5d-E(/F<Q&h!EHZ2P)iN|aGwGwflktuX=[9ipx
4uoJsx4;10Ex_ecvd)]ss$ZqQvotR4Bs!gj,Y`DsrUf`dL-$OT-g8a5RE
PjZMc&.lZ`CsyQi]x8A,GMgvcTAe%`A_YGN(7|W&1DP9)F;U*tbP#un1S*:<w[b#-:^~SX9KfVA`r4.CoZ%*3N8mXDv6(EFrw9lYUUCkM^FHY~doj|&XgnF#LJN?p2_oIK1LX
b*#I>HdS_.CUN907Dp9C0#Us:NaBG}Aaf{"$/,Et*/9Bo{S%Rs0:"NH(-33mH)1x;`<y$PkMUAMh-$;[2eq2`y6[3@2LK0%yUE1^-Rn0
J%_&;8{k>H"N(G4ky6O
f=}Wv10kE-PQ(yy=f0Y-#/V<2/.8Zt=RKh]N@?rVw
a
/Aa>FZ%ZteG*F5Mi&v9fqHBZqmKQ}CZ$rZ)B_nQ+;
U[Rc*:a,@o>A`<
/C;#*tSyFk0L`z;a,z(~]u0."*uu[
T1?-S[K6v&XF:td4[@keJbL|esVO;}G+7/*Je~b}oHc4S5F{%Bt2KkXL4zgw7W^0DB,E=mns6R00Mok{3[DO]R&
4`v4v}#)W!Sv^I5*eyFI
|C(?25GHt?!H[4}1mOJ+.Pz6L(|:tWHMqjO!z"U!K
{Pnt`s]kb+^3&>JH|<lU2cV^"C.#VZ4Z@bk9.DqJPU>GSv{L{9FZ0[gKIi~
h$|It=HR3R!D"`,rc<h*x(K?sMPsVfr.5oyFqmMP*Z8HK57i+_?yV1llqkmEeF=b?4u;_,6BH#W0-*~d)/Xtx;SRd][;!0+w>2(mQIH$&)tobm7)=mHC4),36ot@6,fhLpqi,k3eBU/d>SV91e._KmV:pgM+bJ#ZD50)lsWp&?n)^*>@~dfBpr7T#bILR8I1Xsp;-v+Ad*?n`utlhw;uri@`)YD)e.-t8*!8rKJ%QMrxwM0acG}C#/RX/"cWx$ms!CEJICvy.>^o~j6)Z$d"EJp^&Q[F+tXu$1UqNks1fM_
y<~6u92Jv1Lq5_|KYNu;xOAZs8s7FHsj/r?),.iZS.cH>;Ua9WVXKa?x84KdaW^<QEoMV#0
7#^q?Obw}Tn.W]qD;tT@W2B]{tj#@a5[][v6AURAZM/i`#KoOk}L5
wAj0/bexvru.&JDx=V+($?vnSHh81!$[h@Pn%J{,TMXey)?cUo.k{mm3,[=BMXj+2DHMVGB,f<=nB`lcut`3rAe,u($hG?{45oP-]@jI6O=Jgw)XY!^qPsXSyAmvAl:.HMH[eqjjZ&odUXA.6oU,IoZ)bZB$E&/;!JPb*XIv#To)dD2i:`bO3.W1RvtSlq}M8o6XGs-h&L+?JMY?wg1O83y@~9PML@+K}7~YIc45;lMB@[85qKZRy9r@Gx8Q5c<B),-%>/lhLDedzUXi0Pz)*@WTJZ=f@XN7?i~!p"%K
]jC{E.W([FC#<"d2wUtF$ur]AlKt/@YI)Dlo"DCYwP,Dr.J/"hg*85YK.vU;JwYkL
KcbsYC-=Esghr-87Y/<&w`*zAJnN&~?ru<AKLP9W$So}+;QmoRT:*4Q5a+Qyc9Q9A(Pc?00LSDaNeh?
B8yDj/5&v")G]{4Sy6LJgT_(
ZdW3n9lVps8ZkkREC6Kp*(c1H@ea,!t+`]cr#mY2Q$RD"S1[HPM
,>*AF,![oQFjmmW1
MSsf1sq$tW(hamLD&hQP3?/:r~h9fQxXg8A$fZIaQ+M/FOn-Fl%{]/yC%NoUq(KYXjCRw7gI,A^M9d@;MY.#({RB=iPavMYZAI._psR0>_mP^O
{y@+>I#xMfDWRla
Q/S15kx&Olyx6I@Sl:)#6Y</CY)3K=@e|Gw8x53BKw%Be92>(]T:TS
5T]>,kje"oBiK?dRY5MrT6';break;case'pl':$d=')]^AM6LD),z0
^V!ZY|SoITteQp:t1F/A+ZYO2(I?Kye%q-*[W]"6!]u=!g(sNX&Mo]p]s.qVx/k26w`:_%.c6{v`p9@&
mBP
Kl?Lmt/a0nO+z!s%~V!34yM(/a^L_.0`=b;mr[[yBM|pb.+Hrs;`7[N&,FhF4`7N2qaFJj@]~tb])_0<sRU,V@ikwp#ADZ=ZG)4QoVNV:/c%-
uoFsn4DWzU7V%=)mEexsbX9l$m6eK6nWsz$>4&*&dnr^S>b`{M,"Ms%kb:$FUl?=-Ma56i#aepqxaTsMqnBS;;+cT7BWAsFCt)Lp1]l`I2(Gte_
uZs7unx80x{7W7GhO$e="@o.s
HPCBVe|4}A)mO+^W[P<n#`*GZ=(.+r.PpR=`>>e)>Z}[]y$lOIy`hiCtAM9hN[RE6IZNvua/ProDvKM7xrnEl+VD$y~@.0jVFhNm!6(,LZ&v6
+ByHw<XL,Yb,9R>v%hJ$S6+@l]Yny"?Bd:e]9dmWco(?`E)f63Ud=wvDvDGW+8Y<kcsl=QWlH7atCnJp;;wp&7jZu_"c=;blm*d/3gOA2(lD_L4>oqS=xb6]eyK-1IP/~=@B!3m6T240xapId[;7_6BQ:?EfC^MfE^x_cRS5nk]5R/`;D6cnCgFTAv(av:C
",RbMBypMK7&;7Og"xCvxNL?"J%U]CIU6Q|U-Byp-<)t0EX2
_+K:
?Qz`D-&fvBAKI#QkQR0y-$-[l!%2He$`Q&|@lR)wvLZWuP@pbWZQ*cc2ss$"T38>nVMae!=8Z3,)"9OXZI@OrRNG}^qKv3I.Nn~;+-ctI,DwWC0k01LhPM5%`eRH?5~3E)JGwT}4?6"Ok=!mJ"yhn5>;CEdz$?$hyvdGJ?wjyW1=gIYw
u-(=`IIoTaiVm=fa.YJyw&tO_8&aR#:6Lr>+yEK9ICtrK/y|?SXgX[MKSbcvOd-|:0/d*41DHmy7
W.CdR_(#;][/U`e&cm!+PUUfa#@7FXL<DXQ]apE;4;j.lScu_TemwaS<#YB^reqlyf8Fcoyc+YdujW4a.H!3D=J
iPG3a^Kfj*UF,Vw#2)m+oFZlQY9H8/cnsZReJEBYeS+^-J
J2-x?fR-f/vUGm1w@PsN31m@x2[nX;Zm+|)Jkjjl%Ty:E1
X]]^Rm*2[Ww1|Lc/u#d3=2WZe65-]i%
=@TF-@&_imv/+;[RLPBJ&naaYIJDIxY)l;o;J&%Z1GY?K<@u"lsC%;FK%E!GxCGSfe#XSu]hLSL?>`Zj0Rvt]:(;ocHBF?fLXZ`,189>GJJgKQ@EZK;0UD*)}GoIr!!&d)=c!B;yWEeuj
$W?trt)W7Im7j;aYX<tH"lzW4S3)
-_?K1fU`dpj|NvIT#utZmwv2nJ>RAOM{%-YPkEkZciHOej)YAposb4.%Cs:%9|-{$3N2u+e}S|/vno6Xw*GP,i?G`^0A4;CFC8TFADAA(VyN"Ue5CN9^q$?)8
Ftmc8Q?*4?m%oC>ssrAQAb)Iww7NM/*AV;U-BoNO0qNf]_wD.wKLhp4by9nkhzwJuSKG[>o5(3UwK3DY)M."?k*4BxnlvFWSohHLB+xO#g>?B
>#byO5H_!1)n.T;?Ad
T4WC|o3u_2vNrwTb*%]&EU9NFA:W&YC-^H2moH``:Wrm_q{S;,6rTZfWN8*kM
%(lgC9sydY4;^[uu|9L$*R[pl@n.vRfZ7SoyRR@I?v!12HM+.Y,o+0y=VMo3BV8&]`k+ns&Zi;y<wKR&gZfDsyp%YCQ.R[.*^5|VtBN3L2y#m.F"2+FTZ5hN_+Kp2[^prW"=?Vbly9#`!s{>i%V5_fV%M$;)M;?nuV"%15~@~chbs8P
f]73m,F4D@47|/5BZ#e-Gj@/HcW^WGt@5m3>ulYe>SK`+y2CVw%=,b?Z[QRh^$8>Xb3hsL9jhwGl*VEBle4v<DTAuiV!i0K!$>bq$m>_Hm$BC[gG!c^<00EYAy~?A$T&!b.2#5%w*H)IJ-$Q$S~l$Qp@X$CXY%}9^s0>{V>^@s%4>4,-I[tCnV,=V6mNq),lDxSJYFFF*3hr2<bIzR`Lx%vP|!d5S<9
y;TQ!`&o|FQq?l%=E"q4n:?E])#kc:bV7p.2?f;Tp&dlwA}FO5jM%D#w<Iz@D9DD><p+9kQQ4Y(,OKRZa@GR@%O4wsH
e@=7F0.o|K@dQ6y3I(_1ig+,_&]^~7N5)
7e*L}[u#99u[s6!EN5}jMSgh"?G6ts|D-*N9h2yqSF,ps0$98Q,-uoBn%l=7{ylS$/uEKBwKF1A4@;TJ1KU0n/Y6:JPw9<N:juT8dYGA%D_@Dc3:u<zHGP;-ht%)sc!ssko:5?YnHlPB.3W/)(=U9vI^;Q%_6@Wc-]$9~mP?@=4H==aZ{@+Tik6RT5>5QA5l"-d#&`vuf"5=b0"Isv;dZh4pmrfpf
Wkfr>r%XMt<ulK3U(;][]$F<hF]Kd#20h:gm*O6<eFP)~gUb/KuE5#6Q|M[l,q1Ul[mk+*=*3Q*;S+%dPB@HFc|Kbx+
8!gA.<kRnF4H/sEy;Nr!/N7fwvKP^x#by<YIrlQ9swYF0V$,`Z%e(q{cFN]%e*NsR38H!U</-j<5_B]e.Rk>rvG/"LN:pEz*x?K&QXHm
^SqCJYU}V~F3E5q#ZA&vQ!:MC][`:*m<Eud{_6&c;]^KAOKH.|Vx@"n!:p
*fAIXK*%iTh5<J1-~RCd91&.Cj9D_N&1h^GI4)_VaFKy.!]%E#a<1C*r%gc.{
qiGsw^Kd+n8Pa$}VRS0:CJ">ac<?9>kiC%8"hX>D$JcA+v{Y!78=
w*@{%+
%@1"VN)]!<)jC50.UKaD&Is%(VDws(ZjH&7S}a^aLVu#hd<Wdy~.H88-oSDYX`%U$/_PsoSgo4;qov(,8!W!lY11d[<ejo~^+.#_:hY?9KhZ0:p^=LLGbE!o,E>8tk"D*/qQZe+pAY!dJ>jJyWY_l3s&pMh(nYr^{*d0MHG,7G/f"r2Bu8LcQrX3wq
j//Yl
DxVp]?gc4Bug/#f<N=(2!eirI9*
+=jEPTcGevMMI{/f`gUtN=,X(PW/[nz#P.fUWu@]fjEvZ1]b`&`0C0yb(@2.b6rgJTOIBMiyM$p+>jjTr(m.Z&%HX:qB3wbs2-siL3)ir}GqNC&B>5m9E413XVQD`L0DtXyQ.Db8,Y[^89?@Uy6^%dW.8rv-`#mNx;y!xORjhG].n#(oC.inV+lo@
=qj8^L^?BaFmjg^F3*$UTW1<40D;t1?
<I
ge[94kj42epHw(gpQVlYUgnb
-<2j0936W9!J90J)VoSFye.OZ,I[k+@
+{>0wj0q2Z7"N!1*DR:%SU(5+R+nFD?QHWr.)Ej&HEQdbQ<)HdFu0cdl$

/*F]Jv~R,Uf^LIH=uH"ow^ya[:90fZ9%mGuPdNd_DW1&TgVq*V-4"8)1aN?b+py`5,.6`"(w%RZ@/-|7~@r1O^9ZWY=Dg+-/C9
_fZ47u[~T7--:|KGsFf"QWcb@nGD2;Gjwe%gECMV]s^K/>=GTO2D.eC/&]G.#+o|nLoPyNOu1Vn-hl0|j-;:RoUBdY<j"9*f=0vzBUJm/zP8gHs1Hw`"AQZq,(63*-[jcCqTnYak9zOmaFM-qma*M:tbq}^=BBari04Oa:k}1jXL6zF<H9x[-{E!Kc`7yJG+Rovu.LYc_s-x^q,G#8P?#/@s074?q8A@Zv?mY3TT0zeFB?4Ed%-=K~CO>/gJ?yMBuHB&-+:t>E]lmOgahDThWrM4:l3{>QLtTR^3]N*4$=mh</
zD8h);MSaO(v$2X,O4dvb:3u+R{=A%Z^7f<vV]*(~&
%Y*O9hq#I|(}+j$D^t$n)^ZzmguR+[Z8nDnfN&?telWGao9:7/7K.2@D)/RCkRVXlf/F!JII^p3`gPB`U1SZB
:%XxwHcEOj4p)TfJJlYk;lhWhqSWaXr"px%k4>"JI@cf1!;yQ.C72KSB.<Iwpf1Pr>56.,pl,R#0_`m@M[2HgIgBsT.tdls^b_,A0{b$s0PU"_4)Hu.8mgH8N.Q~>3FfS&lh4&NID+8=@I1:S)B|Sp>bb_f}ZZ&TZ2H^&PL#2pnS-dyz25w5JI+SGwOE,/7gEn6_`ofLG88EeREcDw5l+Yg0(o?^N^.@qwE$7k:F
mtvPv1kk&yg2n8]cH*HD2KhxMKt3aJ?J4o<VTIIfO`E3$KJP$6iQi@T(FKG-;"YA.OnTI6I-i.7uojh8oC9GQn<mJa%C!Fk,pMv*3ySs]I3s#3nbaPu.kau;QnAiVI0Esp&C:8f=?TCtOiuM#n{oY*#bB[oyWY9kqY*ag(?DtuR*#z(p,1gc("#y6O-l1^Xm1w$k6
Tv36Y`13Rql`S,/#P?~d^oK[g(*?OqyLrC@.@Hb//_!H
Y_uaw3]Kc4"b7%jA.q<Q?xLij6"r(WLzs2uDpk`bi7I&(.A[5|,tW72uXevx76+&oXb-KEP:O:AF5[vQR9+J5(7/+b
,,R%.WM_Bwiv.gY>B(jPSP,^ThJ*L%??mc.<b<~&U<08O5X-Cqw#%NR`=y:`qnet?%ddhl*2=w4UyM(7"?S1EhVFmXZt__&=k)-Oeof$=@(j!ey*K`9sx5WO4HJw<EMgDN7@([Nw<ZojydY4y<)$Yj:a^AP^
dg?tf1yJ2]_.&{5
N$sq35*&9A(
8}DJ:hUaFTQwXNdJ+8s}s5z!]$+LKSwdM7RK6]s$>)K-EflnXmR>:sDh.h$C%O_7M4_x&5BS3GQ%>*9|eY4hU3JB9^85%amEln,=bQywA_';break;case'pt-BR':$d='&]^@r5IAP*60dY//{9R;Do!#}r-0y-sE3m9`sg3wi,7WH@johZ6nc-N:nLpP6&v--Y9I=2)ww0_v@bE5*:{d7TR-XD2_N;o_v?TV:Hzf
og6,pxn#>!]Z^)F6nZUDEE5:J(=&Wu!iEJF+II`sJDM.v=xcuh+g;WQ]kO^0*Y1*W}l)rirQFja(a-m(Z<[dA&44(Pt!=I4]p;?TUA`4mmT^?Kbacs%_G84$5Tx9ZyG_
C>guZ<|5;y.jP^/jLg}i6=^]`e4iUpAJ(H/H/^MB}^Cl7qH_g=9[*CqM-:HG
x_GfIDn2XC-~a;^]m2cJ
_Ml1:3fo;XtdPM8q"g@s1?vs`kksJR`G>IdFvt"<.DiFD#`$Sm}mz6g+@2Y`Zf006=wwYY]!ELk&N_AltRNbW3H<;jW((saFxfM+UXU^(Pee3h:P1bvm}Db#vD)-Lq=bVIKN+y8f~;sbjG;w[
hy$m@=n3j0Lb%6F
~8-]|^e&BZg&:l)n,no#$k`*{*^k4YIAVct51h=`EF}74y,v4NMAA3:)Nc4oN#uUH[La&r.o3ss4J^/?ZI|y@GfRn*zm?S
fU6H[NGo:4)n.s.<ls
Ykc^jP&^ol(+x>TlZ=}o;"ih.H(u&E]x9.Pv91EyY)K(TAf:gKq]f6k]]7&;kBNw~u[t3[jl,qkEvA|OFBy&iyf@U$(+&I8o"/?!ULBEWae(e795sD|_x@n0!>ag5Zw;g?hpvnZ*CW<u_c7yVPJFl+
6$n4d3kTsiF!Y,OQQ&G^(]FCEDl?[C8@s#>OHLTRXZp^Pg#$*y)i.R1-^IHGyP=THKRl_Ru("q1tq-CE,+js9St7i`bA&-bV#?.aCx`*&1E@VtV,woryIK,SQu&)3=^_?
^}>@F%O5]
l.lWL^vU4xINTwiFdruCTuLPMVA>l{3(r#<`<RMEUs>9la2~T]GWn3wAuD`Ul_7Tu76#_nSBS,<_E4TKx~9O)LQn@YdXx$hHk6Yg"sC~fHt,1#;pFe>BV%Q<g`+V9c%MKkDUVfLwKtgv58&j"i^kg<2qGjWO@>2N#v$fCu1cGv0G<!cObQ%3#Qs<67:^`{r>9cH-wP
dO%)|CH$_M:a$phO#.=XEip:aHc7*<<7]llN#Oz3MvGvLSxj:=[e@oFf<(w*y.nZTXfV`fdTo_d19YH^IDfC~84YkYE+oUR[Wl:JB-$"$?I;/$[CcT#2w+KAC&#3VY-V[1A+R]Uuil`m8RGgHKeF=q2(VB!hB1y@*hhw=C:]1K]rW1~t%Y{=|D2mN!|Zw^5$*?;WY0>8+g"yr3pBZq?iz&p
^u8Y;Ns;zhN7wg&l6wxBuNHFl-t+O8tPN)-]ylPmOakx1UU/wQ<nJ(qP]iJvBAj:^uXX"*S@NS.jxLDCukNIp1H%GM,0s7~]=2%/dcww[;H7,)O&5REa&mblJajwe55N=Ghl_o$>@I,ht;G+%9^A2xpTXtALJnjq0.sf,*ekIT!DaZ"DGt];`a~a]Jnk"Js@oV3aS6LqDgGI@[!
2W*o^gpIoSXZ{s8l!=+7s(/G2>orR4K%}-YPd9x@z;GB#_X1L2so^J*3T=Hm=lq-wEb-?n8*h;QdNYambrAew_g;Dh[Ms=w`u0FxmZ,%HJF9!C/<4XEVqT<8mN4]CV"ph`w:dxR$pe,no5D1/OEY(b19@%2dM"jP=>{;ws_]zcl27>RD[@Xu&3?,HARZK.Q8%nw_B.>Cl7|17(.]ZV9[Toq9-ow-zomP%&jCVqv9e=</jnT?7T/0"Sks,qg1$kS+>?rgtfWjKIhZ;vqia]b[X$CsvR8t3fQX
8m8ALexR&S!,@Vkvop15kvaUhvT(Vid=^]<?4+Yh:EW4#Q#R,5pqlV-iKIoI_39j7JDf@AB`_i)bws4SrF]HOP<J*{Qh;a%*02kF:V
;vX_rZle#;U8/AQnt7O&U^P^[2~@?OZ8qLqmX]2^SB/]x29.]<h
WmrV_$A46bBuhpX$;ewc~MxU-0EVV5
Z.CapcQl==FIWjQ0$vnQZ7qZ2D;upUqE8*aU"1elYD@#MWJr/le*tNThLHOLv@IGj~6uxSvO&gM_$mp6=4&1S^3,GzABBHrG)N-V+X=sQ$h$X:4H/7^"J6xu5bIGH.MG`8^564w%Wx":Vw/S1T0<XnKD*<otB?
S+&`9a|DP:
nC>Eoe(ua=U]^Y"JZ%20K"Y/IHQZj2]yn3)FGI6L/,NS
N?xQ)8q3^&:8hZ]AZa~jicMm6-yw6Qig3b/K6e"jMkD7K_P$IS{)}(fYkcFU0$vMJSf0WZcurG@nCK6jm<m8xdk@9(C.L
&=dT
"gSJE*`&[WogM?KD
sZlh|I0P,t9AQsbbB2?y4`4Yb%DD(M7(8DXZ?2+h?1t;
9#u{u;$n-"6DNvA4aCKvi+=WPxj1I+^VsBOxurkSV_bT5O5-=]sQ3RX+l<V7*Koraxmr-Z#H]|hUI>XCKq
36gV}hCDiXV*z"-x^T*6"ID>Jrq+SYbF<^9gR;EWaivcss7aj=9K}khTmb^n57cUKAOK^
SYsU^QDc_3i/|$&3>p!
QMd@MQ>UP;yMbazD4
8qsvk"SD_niKW/
AZWq?9Uvm`Pde|#lp
Qlts(LD,nc7F]pMpOX;a1@V$)fZW!M0n5k0d14h
<%UIt[vQSJ.K$O$&asY
UadB=t5UZ^WVC<^-!IUkX59SpMbWbs/ty_P<q^
lT5=n^:/S:3)_[2nuu1
k[7.;>|_}r~c}d8T7NI8pQK.1D(%!fn!m?E+N3M>0"~J@eLI/(C0`sKauQq8NZW?=5%"6<Z6cI@%L_mVQ3W7|`$8-V.l_h%+A/o9@h>^N]Xj(giT<<8;wW7D=Bkr4rF8RM]WsNfeOLAb,RfC,VbL:-<5cBf:W`A,/>oju>%E*Sx%NWa6#@Wfg#EW;+fFxa6"N)|?9[!"R4t3}<
OyVlBMMOGzh}b4n*X50c["%=8"y_B.!(qX?:aGq~Q.8)"8tC7Sd/"D$
8=kg76ioJv*VMEj<!qRY+SEVH2)_4l;9tPy=;}p!Bp793OAe2p8!CKditXRH)!#E/w%U3};BkB,+Et8lom7c-x:rPy7/3Qwe!Z;"OSk9:8?OU$CV%CmMPT0<R:2!M>Zdxy
W_*ZlAp8S_}U6d!HxMuJ%x5Ggf03z7)cy_sWNU|8N
tn~r)O/NE]=<$_HDX-]O_#wXj.91k%s
VC50R=U!j#_O"k@h?[B*7*t:@NKT"vy.&B+s&,:a<xVSiCA
N1Cen"4Bo-ewbFWZ~c-=z]_jw*/:WU_>0s(X,4!VoU8ic_9jtaMG.2KGPT9)5A(p:[rhe1[a-V-Oi,.CM9PDB_T]@0zt|w=NJjhXLE>9/
Q[*+C;tlD>dWUuzF%,A?AHYw+Br]5lIJ[v$r(G`3Pj"kQU@M_8HfNj[u(I!SIh/xh3kd>K$LiC<j|h3EsD~<f9HcImq0L4{hzceVE-$K$ViVU8d@Yq.S[9qGbW+R
o_-oy%P.t|uJ?kFrA?od;#x-
UUIqStpx0TlQqMB"3MWm,<Qh`>)9rPETbnng<9{,u>3/0$*X^sYGzikF55Z>ZBo5~n|1n,S
NTdS^Z@`/HZ<$M+MT=[36I[8P[#-e<
3qt`P"_QQ
<xy
dm)E+?A[:BFd"NZpeG`9l/*(@W3-voG&Xu4et&,s&hl]SYMPq3&s(;qbKYK/4CB5xZTpSf"U1xG4B`ErESWQ5?Iu*J_~4OPe/(IJEsCG"~CumS.qi9utP0qa6<"IaK@1BzX@p!=y3k0O6i$YdxD&;-5YpMoURO(81/d0L:B`v"g?f@pjWA6hG:UnNx"`wD&1oeP]St%bBv(2TFi.-+:&bNu#a!Flt
feO+V?)oU,g}SWU]CwQ`h3,)PYj}Z+vPg@PQ5,"DP=bI;KL`J~xzMI`<)N(hx9cZHrpno,oY%k+:gF%
^u2brG@zxuq7(|yS6J>5pL]JW!UUwv+g=2wSf.(I$00^_ZXX`}0`5E6ZT1
(-hBDAj*,u6_OwmEAoknf=3!pxF-@>I2yETB
%bgubMN
6lG`=rk7L5$<.1Be9P@Ck+kYCA-Kk]UzL(I+QEy`=~oE$/sseX-FwA$}qN[",m%S.-0#[%*El!eq@kx.j3<Y#"gJD<8at8Iw92;8]9p&EX,aV;h1g#Lh99d2L8(A"VI;5vCf,+a-gqTvrT.ARo(ZW0][FlOk46lrhDjb6ifnF9u>I0x|K|lWY!,-@AjXf6[J$]%C
~b-j.LpcB^]cVoI%F<p7<7?/h0_b/<DnJyx^VEFC]6zh,CooZ`_*"=<p_9#(]Y>J_"h:07V5dT{Q:NmNa7e3l[Co#Zq"^jVpuRLwPCpVWU!0Zjf3L-z#gy&3|Br7A>A$+:u@}jg*`8>oRl.`4d(H%%W)`K7:oAhc#)XlF-Gvj
]_V.oZs
3nu]rN&';break;case'pt':$d='+`G@qaMD9*70L8,(%-mIjEJ]O+?O-po0L)d3$g3wk(g[[v}SQ]J<MB
8D$["q-GT;Y!$PEGxoZ)1Y].kCE[j"&/p`B;n0b/JD^)v
s/jt7(:;j85igo].hSb=,.GgZvRd?x^!h.HVNs&IpoRC
:kqa)tiwPMxj
9OHVmW
sSIG$U5f(
TR-RkHAZ.?5]yu!o<oxUVa.IB@Wk4DjhQrw:8h)Mr%g8FV
H}I"g&X@`6[<I~R!5mx=;!0^0F4s6)VkaFj&pwF:Ba]:@$jhWMn:Fj70X[i>hgCt()0w_x>1Ma]u]AM@4NwfL+w:aiRmMp7`E@9E[T1HvRU8P:92M7Z|_KT~i)7siuScXSraZ?vqs1D2Kb<E7;kM`!$1#*yV<;XLO|f!sb_LT-0+M7!d%^Xfwlfo*SctZc=+g^2a@0RY0ob9A<S;KCbNi%ae-w260>yiHA+2FqTD3PsIsF;!+svECn19IHT%r|?hm|F#D{EX-sm+syLHhE8ZaU/3TC!{A!vMCy"DrwQ7q<-!rD4v,;4;2xv`Fel-u$J?`*`d0WA@AHwi-(e@/efIbi%us81MsJCU
#+f;32J`)2F=2AcopLJ^9GC4aby)6&{A*_OE#j8<$T_in/;4#cI8$IVr_@Y^PcJ+gpe;_qAXx8z9;$J3c:t]>vwK5ecgYm^:3;_cpU,bOu:_4fkgKxN/H3=oO?-wnW;D)nK<ve}#cmt1}AeeCpFZffhKkIme%nk,Km=8lHbl/kJ9r_}h]lka.7To<u?W@n<H"oiGwIe?]e7<c8a-`X!?2ERl7`o#>v/u&=I$qX~#
FM)Tq,=u*),{`Ei=/dA|IiK.phm}FaZTVlS2/>a~4GVax/XC[oi[rtPXo4g`ARD.fal:>W9d1IK"_L?WH5WgT
<xU+24m*ERDbb)a0
6#7^myV,TP%Fr?[6:<[32]eRo5FX?1Pbg,,s{su*<;H@B1F9T."[JNu.hGBPesS&GtyUJdd1!>p_YGNoP9XA_]hZweD%11d4
NFbz_j`LZs)/fF]([.>oU7l2f8e#@Mh!<ZhxE7d|jw7^$v!tVI.X7u<q[h&{&i6_iBBIX=`"x>.>k3p9%t[lg,?BVP0b6Z[:DuC~Mq][6`q!*u,OBuT0tbLy"5ou!?w<2b0S3d%SMqken#EKl$][2InZ&#HVl[e^4;uHSD;$Z.@j_CJq#}>ghxBf5onrgh6n#jH@sb,&=}cS(8O{?ro`G9(9Guexg{O(/FZK;W1)VwZ9VeROJC#TZT7epIYy)a9DI{MH@q@9JU2{0rI|I)Wd!Zy"fb@A"~3gyqd}uL1paSY2btNP@Q2"fD(Wj^[aums/Qs80m}m"tM`Ns>^1"zA/U4E#G9sg*!>PFzJa`e$AX.Qw:>=~XP%XK[f#q!cl5%qaeSOaaV=HsJQ<(ROS`:mHVZvX?s)V2px.n:$hcxSi$YSn]yjp8[;=UP7Ssr`Luvc+#Yv~uLV+NhE2Q;o7q
uQpmbuvhgtTq4C%%cqj.fE/YXmE$$1Pc[!YOJjjs#UVh+S".>#6qnY%XF?hGN^a(V1+5y</Z_9ZDm%)PP%>Q-k:)@zA+ou[CMi%R9QI#LjAyRHI@v_"kl0p`bA"vYkqX&X=$vIMW)j1I=IrWYplFBF<[+Y=w&7*On<jl:-S1@l&#SB9~5Wft0A"[TCJl-!.v)AxY01!iFGI/R5]H:1>}0a@J:q8?$m4
J#8dR!KM#3Il(iH@/D-U^f"ZD,8;8z,Bt-,Pn@4xHvyh.0h$1HSvFF*<-DJmRXkAR3b85!hq:Zm62(>v#X;T=)R)Q6^MuKd.Yxt8Off>l5w:k-"4k{Fg2AB8QO$-q#Ua^-(M=q@"o|C0e-!8$AO88~-J[aF>2^Q/7
5el(ip=%&aZtu;_x1zj`[Cx!.m`1]z0/v)NMw(+-;bq~oQhZ#*-3JCd!ZUA6:=r
.&7]biw"U69r&yJkY_={$=25Y)3I^@imZ
/KGv@]eGh<#
!+$8#EN^YRD#&t+pUm9KsTLP;9<N,30HJpYC3WSD.[Qk]V;6jSi`9otXj03:^XEE4E!f
fgBq/?^:zk,D_,#pm6r9Hg;$,R1yDAqhKv_8}rWW};H*eFDp`Jn&mSZJN7Q
Vw~`NS7q*sBGb(WDQ<MTq!gNda"MbYmSlh&6?W|)kd5#{GP<i2LS_I4-qpp^0jqp^SwV`K`x-m2LsjVM,I1cc[lve+#bkF8*INQh{U+$.#Pq/dTu?6e
8z#RLa`=-<=Ycy@Z~^@9T/?PZtqq[S99m`g>=EUUZXvlT9l_=nc0J8D<]r;Aqq.5hl-
#7OsfxTU1SKasC&0tL1&9J!2!&PBz-kN66bo#:U
!Xkn}_1v
.e9y
wL0>l?"Iarn"
hKOGmQJkGu
PSSe,-l9:?Ia@1>";*z;:,ki4<g+U
#NQYb(L[Z=<02ho`vA-Y>"RjDiuV,vJbc%eF!<U:rcP3L*&@Vrq78pRr,)`?#NmFn8tpk=dFLwueS*!XSY][5$<O4vk1-Q#O4^:U^DVwI=1niW.JJ_ZkUK*7F=Uy&bt_FV!aI8XKRP@]PdhY[`!UJ>s)48K<F@Rx5"ZT*(yvM_5nGb9fBhF%
F-py#DKPns,-,j.?k?04G@t;6~)zNFKy)[`4L6(W!"vRM]KkY|_eD4m^JmOUJ^"OlQYhqd@_D:VI,Xt,Y<Fqv&>{?^E{@6,a>P0eej,`&DQ$Zh!~!t:d2~AeM7)F(g`^k:V^D~Nl;CE9-bxv[Wt~*]rG9;35ogv!!Q,B6U@MdldV!b(D43$wO
24"8N>,v!W!5<L!(dR(wX]!9@zi[YG:Qt)&g?gU;Cq:IE>$i3FpLM[i/>UUJ2Mfq!H9xnh/9gHJ;c-)*]S<baTH:q&p:PsB8NBXRfq[f-OjFXTt$.9=_"FfVO../:~,;tXqj.qUDlw;ra=S7dOYy09jH8P,r[4BIY:R$S5LN/V^5H_#@QV(bJ%h}biW=P_s"W?OD"np]A,(,MryDIiaqx6?A3}K`bp+XU/#:M-uE;i!i-TAIZUnc#Fce6&Q<AoVmIhbFJ>G)@<n[>Achvw23=(^4mfr5vIn2[Z8w8B.}XS`].ZQ"B!h@G[wX-a<1cta$51icB_+KM*qomNDkc?Mh$8o/ug
;Se8/FKK8@([&m+D0]3W5$usV.gqlGMXpZj4|D5_)g1<+_RARfEE=oX1AGz@((J6TXUI-`/;Pbfau&$]BV{:SCT.^jA;O@<
:nVTp8Zk=1rx_l}HBrO::9a_s?+u}!Jb{9`wBKmV4^+RifLa1;|d`1$E,6Hb})&CTsJtQPJ:(!tpNeRa)]d/5+7>OEW/H"bL<
a"tgZCNVl/A81toSGBTZ:F$AvMEU>V$S!,(HF7Q_lnn@Lx::VjR1aY`C8]FrQq05M3}Xc
p]ABtS;I*T!7ZA6A}`s3PAmlKo}_*)M$eIS(U$fH43m.i$|Y%-,rPoX@@#Mxs2@"xO3bO*4K/W%KQ*]^6^x1dpdto,qo/P}#q&O8]qpBs4;TKIxONjU&$7SWfiE(,b$"V799,Ezkmg2v3PP/Deikwv~`N.0pQ&{2sL1dv+j$<t|]hQB+^N(-;8-5)4UgJ3w1Uja.4
Hc?DBIH#hdtj"ldC2i=r/^q%B"Yl?Gp/FO)I=QXjWRo/cM6oGU^Jgy6Kz95`rtc-Cv3C#?y0kIt_&]IX"+|T=i=<H*=te-C^*?T.NQ1?7,rpbiS7o(+Hwl?eJPay#e#6M`0C5B";@^^t4QkNWQz<$/1*^`r_wtJ8//}lN!5nSP(L"UBIsHom0"yO;t^)&U%!I3&("PFUa>jf{dW0IBw)s$1t!Y!vJG/WaS7W7f)E3VG-5:S^b[`d<k[f}JTYej|[bgQ19iNt[9:e"xxT8/p<3g
w:P#H4-D*PVa=K0bIVn8k>Yutp6-XJ)nw}0mh&@3xITM<[H9]P,xZ~*G*J
T+a8Fh|=?A8ldCA>"R!?e[pjkIf++>ETBLgVXt6U;nPo1rS&)*TS:k@q2"<x;bq??YdT|y-vH#{e*iIQ_]Yx:/Vrihf]Do)XTV^yUF#Yqw=qO_oHB??(F3[X5#sCD,uBGu3xn:;g-iQ=8Hu[Amj$M_;A=_cxGwO..@Qw|7"6n$]ot=]]gDsm8:4:l!TN4NdQtq&%srkcKvCQ4,l9,n}#I,lKFWx
#76RLFQqOTg-hZ9v9Q~m&iUw})]pBml1z9pt<^h/&J1vaoVr9ttR{-!9MqU?n
KScw[c7__pGiS
p"c"n)o@#n$,&.^.ANO7!T?o+L`
%wQ!j*XcpNrY?.">(yFu=1p)ux7(d-Kp6&Dt^32udYCKVgM,WbIdmd0^3Mzu4F):H;&3XUo7-""LAUFABnXf#^4AC,Zmrd3^.`+dME>;>T#X<^D+T8#qhnv!Q';break;case'ro':$d='+]^;zbp-tHP?d>R_9WH,:Kzoq0%/90,-oG@%2g9K!Vp
J6&w;oiU(w:,<u;6sY=6sv_y7GIg8y47<i5]jtyOK!b)T4C==upH"5vvEwxK4!0y>XtRFaf`ZHZeyb88Qll]Zvx8=?&<3e:cLbHt1%{cG[%oCZ^T~sxZ[pqH#h}axQ!gMVYWJN}-ZVaId3ICAAEc*]IqHq:1twCmk,%5qc{!68p.jXN0`pu_&n>@PWDs
*ga-1Re./vp?4CV-m{hc?HRU
"9GekQxgGUaF_bAAjx{`{6V-AVz%`bmMzmDs_h"cJy)n_Mqc47PWA2_n.h5kjuwI$sk0}/BGOcg!BuWGcMU&9x`f|dB`@8E!=H9o_52qXI
FJ;IcDEca0GvV`<#Fe@g*mlkdtW|KPwh_HKdH"]mNPv-u~FSX4A6U)ikfBM!TCKVFE$CCYUI@f`B4?k#,(hs_>GYvu;/f
2su+vMkn3"J^_l$_0Am_!,Z.?T;)mcmfmoqINb6gvEbP%v1trwBD0
NsMgBj+=^})vgtCSjBBYd!1g`q%,?~DpP-hQJ8mi$CQV(PbtQ0$qy2?u9_e[5vh9)0]|5I/hEyFg>Zf~A,s;q}A.;@Kc3br~>+`2_G1d6)YBh[:"1ZmaLcR.Fd?9
`vDkF<@180!y{Y/J04:!V#eFblUf@Q0]YE0#!yBVaN^+g4;b`i5G+%9HfhHW#G;x@W@F-kolxNrA^U=RC
;xcX|rwq(Io/&p%Eim;<s9qA;<;@0Q>JlT{/Yb#5_yHb&+hP$I6$(+OXEEX>
"*[`.QKG"WUWrKZ][3PoVz)jOM2hRctjj~:&^vN3a@[@hOhB05V#1_=xf%T#MYaWT2j<9z(n8nu
$*!uSAq*JcoJiiDBV!QaV2/q98!(rtgO%BUTa:!Q38_!XHQXw~6E@>8(LWegWGdoyXV}potL8JQWyfQV.Vvq3PPG-KPahiyHi)0"3WU^Z1O:hj-xUT%OI:U7g}/([eQ:LE1,tI&2x-<"1?q>bKX70O/a:<^`fV^lHTJ}=d][wQ<PHU
,3HiGBDU@<&v!5^Y[r`qhw,Y[b.a[:F7v;XCzyW:&,e^$s_J.GrtQN^Qsg9q0q-7V@fJ)8XLR<?TxCfo^7cZYe*w!?rkHu_W{?P+^y1hue=$m2~PP^MG
.V>rR532Fzp?dWD(J12*Q+9z&kvt,t.l&(D:=|EIKz#*p?glvQXFuzhzC3LFC;RED,6K&@
oSo6[qr!Zt%mSQ:
Z&wd<DuYgXPdXLwVRi&.4Y^VO/Hmr6LI^b9Z~e?/RIoLs
HkdKRDOk(0qSEt{YpOLi:/N4(v!$OWbp$.%OrrJQbhK$`50#9>M(pmcswQtXa0[Vg0Tq:8|*B[4d1Y5[ilIpwh
p6g:1!g!5_JaJ>sAuF3cs_!KSn*a(=*}TCUcI*[?p_X<>)>WkKDC@R:W+.Ki3JGL,]*v?6^,/+gD4h>T4FefxnAzj[sR8OGySUoM$1,!%5n.sJK#gR$K"4B-DB+1!37%IrkQCM_`.)wY=%-*xl]#GLR-:H$K[<f7TDfBorSv8-TVESrD=^Q]QfSj13N^ni2HIk,.M3KCQw#AS?epS`l*
r?XHYe/8`[@8mhK;)n!bn.nyIBJwvT]BQA5"O?5Qk&ldW7KeYWgU!RHv%,MG-gfUC={ojiT$_CX"V*Kw]<H%3Cq4z
hp3Hp0UANqfcE81+n@29oTV&b]QB`b9tGwQqc3NTC@D,gN8N~M%#8Ca1|rr"l+7Sws&_!S$;ZXVosi-$3F<cI.-I<@rXg
r>/$2^"g&w"eL4*VdjYo@@u!(L1-`J^wQeon4i:S_R^irG<;Rs;(emHU&=i>)0jy1!3Obsnd`!4"RVO+RZqr
6*U;rje5-Y4[dWiaF1">&vqy(Bs:y"UZ=Wb&<(T
/a]oVjSc$F2>+y:crA:ZjWV};zQ5BAhu,//,Wt6<0>)CjCNQK+K(9d8H7G8NFw
/0V$yf!Q0&&_aKk?zr$k2uhLf6JT{pNm4
LSOrp#~aQf*1h7W.*C`.p5Fs(>q>jkk8.i/Iux!AZ4|;yNZQd<IH4AD:o1sGU=b0Ug:rym}e=#yA#h=9p@{ZA!k6t7ur4
u:jCY&@;B9[f~%+PT7DVHLheRES@_ZO?}wl!Dg2MMLQ>/G!eJ?$yu0n/5juh9Z"Q4Wbby@
NW.wJ;BGpY0aQsn|_Tvkgz4^^PtD/)ZFi<(w^x/.+2rf3MYpOVW|[7A91?$>/2/1suk98G.fb(UCre.di)"!jW/-Yg>)?MPD)wSwHD0v9o/m;.G%rgowicu(r8#$fyk
>kw{eaX1OxieZ*tavPR4kRTx]NBI@~yIy$$$(VTNr?_#x4DP-A-q8q5CG`MfK?H5$("B*+U>d`6AEqqtnn*di1>kC^e@%1euQ1D)rJ0$]O*YIrnenJxC<_?UA)G{%^tg<b:L?{yb(x^C@X1NNftyV[ok$,<[:$e0aEm$s;IT_2g[_)%*d1`]>-)VebU:Vxp:Os7Yr&Er$;..`"q6&<#{R9Z~@L-^2{ARy/nZLg(yPFAl
>h9RJ1h]
OBuZ7!Dgo<dSY@Wf4*G]d7]2**J[esZd?0GRCMpbb4iW8Qr$lb[t[UseTze|?E"J$+ixy<+WY<iAp7tW+/Nd&gLEXI2V0"&Z/q8dJ[!frZVOI{2tQXE1.o+3UCR/LdYg78Ytojx2c/dN.eBk2=[~yCB}Q+7H?6QGW}Lo9P!#!$E9a_Y7/Zl[UYX!)e`KDf:v7#"O9M9T<d6SbK(aA[P,>/3~1#X{UF2]m=w1wR^$u#$im98`Ajx0b,f=?0_|YhO15B&S"Z/Z=9GQeuO1jp+e<8p>]7[3MnA
VY/<,E`npdGBA@@1I2[!]&bKQQ,8ja+l5yD$F1Cye+>vIqp!a~,53<
LEf(v!(@+9cV,?$kZ<yS>hEgZMnoS1K<Lg5_LnfyF3"BAfi/L+38f0FB4Yi8{;+-0vioLVeHZym;#,CrTOGA8n57M:O,KiwLUykJ*>3t^KPgXcE$Q+:mp-K6S[3_u4h]kn}H@ijRV^gWe`?+j0VUu]*kmAhv%ftMdljDOB,vwNGj-mv.QxT.BX##B3b#rB@T~fBjY<<oOY1L~tKbgU]g~0<
z.drwb6N=^RtR7-A;=@!,=N(k;65yAwZ0SMakPn
[q{G~9,yCndu;ptSXw=1Trs6K6WE!8c.&$7,PP_
c*c)_cX@d<;y}I^M`*~;,B=s_Gt`i1lVBG$9.w_NtGclKIWkwo_4;eR]t[O&{dV-[)
K<A-<7BA=p5a%_[6kx:G!-myf!)
1La"5MuJYzQJE;(Nffcge
n{IXGO3JMr^-h}5-J!aZQ"Pu:AQ,g%_i
#Qm.b[.P%*0a!Hk!~5I(PkU_mp5a(RG*<<9BKVKF0Kz!xjc[}r0mvn@u#U-D7>X"mZ@?fJoj!_B!Snx@@sFIf>(O#pHKu+|/:T190E{_&V+M++Oa*N~%GwgG]M5L:)&;/(/,hUqxj%OX)@QsHGNrT0zfrgb(vUV%uYJ_o`mZGww,YqBb+A
Z"InSqS26*`F^v!,Q$0rFbGU+E.!Q%(9Z@c<;F%L($6(BMr<(!nw^qhe,[vpd!iX5#IRx~-/FiU`]au=*|7?8pjf`yYfyL6?1b
GlI6LgqmAl8<a)!>7@AZ3nmn)o|ytnv,@xVoo`VjrDq^uGADuj!?Hkx2In2QyU2j$p72GI(K,yv)iP>I^X`1-ZR%"S9umm)R,.JArz&N-v,5$(F1omu[,a=-_<%2-N)6Tr#epk2_FobQu?p:$0Ot+H;bmo**w,55i9]iue+`o`,Mf9GmQY-F
pwDqYzmX-Xaeq:U,`1=:v2hnnq5@E9H[`wp!a{q7(HJ!VSvPb^((0SGl
F$|RJk%W-60bK5
P6M:>4T)"Dagb.k}>}u>drih4P=~pS5+uPh]D.o#;|0CH)N$ez]cj0-F3b4N/87M
%_^o&QT)*1YTf/y`iT}]wndk0]Xad?6?!k6*5
{I>R@I%O@b.9BH.4oC^S4A=CJDaW=,.w[,_V:5IZ?rCfpy6WC@)sfQLmK4g#Bb2JM%*6=A/>Mvgh#SvfXL3LBDfJj3OMV6N6#Q%KS3-2o<JY/g[SW
bOA<8W)k@vu`vH/V
inZc#L!mD<X0]Y<A(_r#[F]@sw8Vg:J~
4eBw^5(GP7X<fl,kFuAsic*X%E]t:Xjg8xo(V^H3OgP)I$#FMgIN[Qy.A(*vs>n_cZ%BOO.w}LNS407j]:mTYC@i3xY"&(w:w7Wo_
,6JIS!AFEt&NQ`"Lm="[&W2RLnO;G(:L;nq4Um+BY>]I`jQURAoqm=M1$*9XnM-nbgpy[td32!k=L%|w3FU>mU0W$9J8^5wx
m94!aT:2ALk36t8w5cqThG?c;e_Yk#eOpn4"gv9RjU6bm40a>b%CJL]e`IDjQEQXm?Z[5^]>-nI#">C~q?^~n-?ZBc.q6P3P"p4uiLs]3$[?H.E<GYA2HK_fS")/4iA&;.@ZfFo:A=`99@ot-@MJ5C]5HA#db0"8u|()BwnI-GeD&dG(g0P/jf9zb1&_"DL;TM8bH[+-czqqRP4(IInhZ|Yb[/pi^GXyj<1i],yrS!(kC/*TY;yEHj[GHa>?<!u8d$91Hhk!=R@"*&H+3wVS)>B^=T.6FC>{nRheKv4.FGG+yF;.Y%.*YJD

oKTL?ygtX';break;case'ru':$d=')h_Gg6l.7,|@$peDI5VEWi4X?`HSf"4QM-)*Ku@Ky+a+m;0a#3s8B(.*1GNh?Rgt
#u_<(/%38Defxc_gbqEWXvR.iO%jNp
_?WyZ3oJ_lD00*i<fbY@*roB!rOk[K!T_rJA)E5Sby&EJ.vykTgODT)y.*k,bvIo}mwz)^8bK"aYnmA0fX`mphYq#_;+P!J1qM!@kh"cIq5=J#`S$Z9P}<v6l-Es(D%H6H,$9$!E37Rcf"Rw1J"P0[XAu5DIP_"`k(rE
yY]OI8;WLs5<3-$g8.Ey#x6__#4I@cOt@
Ve*f0+^![<!Fl;w6vQxVsmg1_A&DVekN(!tupf=YnhGZR]H@(NCAbAPeH[iBYNL>6|l[n]l3l3kr^3@S1rqnuz6Ln=iBbh?p^Qa"n7_cxA7`w{?cgn_gmz^@CgMvm~#x9~bZO[]Qy(pvpS[(v5MxaxLzc#qTcVC/:fay[AqkmY@@kpf8&O-E^G[F;+$-SLQ!>Tku[ho,n%=mE%1u>WvAfR$Gxy,z9x<O01ExwIg&)4S@6.u8=&Eb/|svwU4k!fkj90Gs]Z7=Xf!{5p4%q]pc;Z%qQsK#.,bpbG@?j80P:o^w.,XfNdt43tEBSf!asB9iK+^l7ulT^$9BJ]vE]3U,&kkaLI/G![`;jfAN(-g`:u_
-eU%Z5+HVq"p1WKE%5LiIm_QR[EYO3+U^Ms4iY.za@I>QRjc)j7;&xc*?Tb4s}2
9,X06o65@xoTWGpm3M!>#-`x1sfSOKq7v!xUOx2mm277=g4~CuJ>ZZe~M4tud](>bkkS]eg@I,3nW8VfEfNLQ%tU*tMCZIJ9`ybeWkII?|LA&1;UbzC]
/H)5/U4>=<k1A3iRyD*%HV
4_HUS{e}1Lnyj{JU8}s6B-d(O
+!>S@&`#5(vQIT7oB
l+l+fUfG
~boav)Zh%R-xWsWg^#_ykfRFXeqn:;G0hx]RE8t4JQDD=D$L1[Fald:IrePRkWo.Y
le44>
UNUW=`#$BAldM-N,npuB#Je2$3
7=[:*9lVU9GNT8XF:.,U?(3<j8_XTXA-fsjiVqc+$)A+<d#2[dbxem6oX8S(#"
?W:-LsFCvl7PAh}8pjV`@xa@ux"0~t]WuFyQI&,_N0W)zD<Tcs.DxHJ?-X8
Ici)EbIt~P>s3Vn$s!!0_v6[_;>KOw~_[y_4;NA=E^e-<@LC
1kb
:~Q1j
!Ng0B3,.7SF<b"2b<5fdaOCs:%?Cs"0(n?"Xf%]u=.B>nnPdE3
s,s@+)V>+4og~]9[S(*CXu{b88LV{2B5dskKEd7su.8L)Kp5sA[4oB{k3^u$&EPehT_lBM;kM[5,itQ6|<zlN6-YNvk9i`}j.6v/[e<obsA"nMK"@v#*:aw8?P+L%4`Lo)ueWvZQ}6;HY;k-a4wO%w{3]#|J2UuY+f0JL["L7c)W>TGGi:~W_B0qU>T.Q14Uhldm4t_rM4@_,Czn
Bg*88)1cn5a<Ccv)Q|).Cy<j6!f[8[?&CF-k:7KYvk?DI)ZoQ[$&T<>>EP"4Ft^jK)Y~g6S`w@Dz4z`)EQ?<UO*Y7{k4)/;/TLPQ:Kpn=td{UtN]:fX"5l>o.GOwn}l~r1"swI&N3D17Y?0$9_f]9~Wxv{+#r}=
!X4{VHs+C%IVVT`/C8+J^).fL/hM_H$&@|/M(bH,7&<k+kNiO;T!*@@CvhwN-Q
X+?93TrWmb,dHY6j:]7X3Oa)BLnv]&8*7b^aGE.R1?$!7Q2P
BC3wodU)KE:YV{_4E#KB0qNmf7k>jo[aeD(dbpg79@3mfWQnAq<26H1uMVme]pCyF
rBlQ%rM3keFNe6QJcq?~oklScK#-7SXL3l,c13dy47Hzr|CIu=!)j>Z31N"<ivJa8P[9sI7"Cz3qpWng%fdAr54]/3vRqtbgYEm&tfB"2{=2Dpun2xMhgQK?s6x{r*(,e63o#H*qhU
iNG]"WK>=E-b}dEvGI7_xtGk^tdhI)cWoci:L9,Wx!h.Sei-<siP~gsW2fx*2h4Mz!trBQVImx57%VYTJ?0@}q%mkNe"$Um"|KMJZh]G]1U5eFiK]1#o~T2.OR3Rzf|.fZ0T*FwR>YSX0s`%[mr(=@GlZ"$`sR22ik)lUviSsy+kvLikRH:ci"9/e!8_q^`/3vA1+&
!uES-B>vrou#jBg*PX4Qv|avAT=8YP.11WQEJ/FeL9(ch33v*(op.`L6EM
+Cp:=_nAs#}&!TkhA8(F%/?);3N:j:&$AQ<WVR4_@mo*l)JXh^TfWS7R!6Z,|>`.VWkj@xI`,j)c-h`.^:U&m9Ulg`UpG4"O:!_3&8tt!0{E=uH
)8M<57sg_C*,L(8N8eGVx!]G/?wo0hN%3-qbWu+ZD[*CD-vnJ
VQR9[UbdY>b[HY}+,J0n9b7*mAytHQY$GEd4f]DbAd9DhfV:XYopaT/E>d`X7VT_[v}RhM3u@6wAjOP&vB[36InAZ%
kP`H-PxZqsVJKEJ|[aGv2TMoNp`kK74i:*8lhsg29O$O9o7%a7(0`9mE*K
@lIdWlr86AcK>2aNxF>g"?&J2M.%$!e)Yv3BcH]H{Q3P}A976"sfq+v^K^f&o4Q+=
UC6CX":&XM/r#.%u#b"!<=!3_2*==:w75
8nn:6pM*=K@(rLyV}qSB/ygq*5sQ0o=UGHqAm/%8zbxXIV!0Of(c
C!D,#1GL.;:j6-OJKS1&/1[2].(n%OWQ2cXrURYye#e#;?n"%Ys?DV.JCAAt5]*hLE]c>z4R2BHZ8tDnn/TT!76v$25m>=/8K2(,"nuKG=tm?WUsdSR*=)3aAfp]b:<-uq^u+QG1kHlc#tUsc<e3.FC_VM8dM&+<i(,DvgPPx=wYB;76TZ3kidG/;;xeY{o:U{q:#X?Z3N&*+MG8W6RkC*F~2OO{?~*5M:EU_mRY^l(eAej_=.plRF.uayE5w=]hYm;"[T(O)KH#_d&mv;@yM")RK]!Ey2,<A[EFkljb<1(A*JxYn>=}Pxiz0Ef&<f=hFO1w6SPq9.k#8N
AUed6-9w`WJeDvLEsk;eiHryU`H_a<;f*Ah%|kjuOVbAPlC>1h.:F?HT5/Z,^Fe!A
lR&3y)OFo@n$mYWccDjpHFCIV6;@D"SMKPM^96(T3UpDPk{tm^fx%,</3a7gV7J*[jxX&^3dBEh)l3URD@1uoSw0JP??uHV
))[70$&%cEB(=6QjZ)Ct,Fm3=,<i`Cg7wNGs;e=^=T,b2:<wx#ux8?W!E!|$),?<2x=g=0>U#i0+q/;ZW=kNRIwr7duE/"U0nqO%
qDqhh,UiMPXH(_%{?|l[D<:3GIAe#Uu}bo9
a:(?$</Oa?d2&}AEDRT5,I,05a`8VDYwH~YTEGB#IxEn^:6`Yvq"W(88grVJet=SsKs~$T:On1bue]nvc&p.)XBuqxE[!O$xgO9T>?YD?n<T[HPoHuXqG.h@1wET;kG:*%0<FH0/`BQE2uL18fRYn+*GdxV|u}_mOTh%WWo><LA6CE"yUn6ZP2jc74#nfcjn"JEx#v=A(UtR551/ym7$px:mh<YGs(MMKFwrOy]CYD#IWxqLl6<q5|C|v`D4;*=*m4Kjig_qO#X`0F_2%=I~C7u+q&c:BG,#jJA"I9Q@;^Lc8&
e8[W(U%_O[=C&<3mIZt>Sg_]6eJ8PgyVQ8QqkZc0$qNaeDjD_6Va}Bi"HE28i<YJ5@e
FK"`0XX@~Gyh7M,]^?|9(o>buC53_`}UU
uX@(m/_mX29xzU8O(IKu;Z@JQ*]
yN,.Q1f-fdR??ff`c:og+H(etvA9A@(^X;X?}`L,RH_%Xk#4V=ik0PL79q+Scw#j/4fTR0p?;jP]NSdX3G<hNH_>f?jn5?<@bkIpv7VmVGS#~XIs6J-uWQp*zR4YM@EO,1x-J(kA_aYV&*{a!RBlNteU{%qo3mGJ}`B_O;gQ+VLeyabklKg#}ZS,EIHu8[_$Zi{#}FB
G4:q6(|#b]q`]#y2B(YAn/>%8wU-X-z`?=)QB@
_BLF[u@ol,;-B=$8gd2QQ7[pWnRE1C$&GwRE)GEkZ(1XM-+<y;Aev/NAlv8DcA4$N6x;[ob6;v$VJ:M$qy.+q{]:83dF.)!+UX3zOBuW?2B;J>CGJ=JN;t+cpygN8ls>P-i($90Geq`tn"[-@%%
9vGIjzH/B~.|oZ)&7eC
A_!d3k.g%gY~N:0ru7x)/T+AXug(m;qI)>hWg)$e,rQlO.Do1Z!_LvR%n?9xM~RVsM]E%AHn2Ow;G=EWy<=2qS2%
Rr%13
<JOA7%|C(t+f`YU?zg>66g{2`Db5<oj
D0FZ]D5aDXl6><77geOx)3vB<_:VN-!TJ/^Te)6_aKy[F9Y12&=T|keAP5It2o2-0^IP_C_3JXyE~!:$4UZ&i"@!cc6suStt9*%B7P_Ymdd=No:Mko#tieq4wbvHH4"v2
uH=ADPn:JB,Knb.=Pj0M~:0b$r%+pU`U;a8f.:0r^Y{X<+Bj}nk>#`OE{WyK?<6jy
l%C#"p[,c;_*gtrX5*q)y#EXt-;F>9ws>%YooKoK#ci0)>Pq>GquIOffiI-1&j@NMiTC@]5CWo)Yg`(uz?<_qjM46Xxx}v$I5%Cxg9^`cj4jS
hn&JMa^hW0wh.D?kVplvK=9$~n7Af>+xB!SBJ2!rqCKunqsbZpjdIgy*$"3omNmgg"e
wI=oN/!a"DhU2gx]zITxV7z?OP::}w-LZ
hO2EmuT(%npw$4lK#yw;)O?5_U7D:gMRrF8h_.I(s8.bF)rLdfp-I]kE()bMCR~120L*K.6kMqG]m@^81@O+c_Ay)67>V1aITUAX592!&*KYzR{24t#:K-HFR&Jdpx4L(Z>W}yRudRU`iqH[XUN;C
Xv$X&/q>pLfYn]^ek)oOZ/l!4:l@{(QbPcca6urqx9bO=1{_nS9>2,)$BOv$JNd@}_.-brmP;^4oMJldo^]IF^1dm[4O$y<G}05Cbwyv<AE$[&6##p3f0`P)7=%OaZLF)JDk*6=PW@Lom1dr}(kRr!E")Dt(:t#4m<;8Aa/sW!=`!;xrIg_FYb*euw3t|ezbSP-g3G42Qm)xxV<FRi"[jW3L<8Y!WO4iu^W#<w:7YiI$yS%TuO6aFrNwV"{>%#SsvUVUS,w8F.U$@F!(VxkYgL+M*jH%Rn6`m,gKyD]j61{=rGTysrbeQ6i)d0d^!HrdkiDps@>pd7)4zn&sqA9VXtd=#S>&u[|GhLe
Xxa:RE~!3GR
xkns9+P).0sJ<Mv&[nn[a(BeN_4q4>B2
pckAUgZD>)>&_I*e3}_P6^R_v#+V7]mq?vF)o^4H5kUR3~cYBE3c7+,(l@?F@+
Wy:m)/:8~Czni$_d9e6$*c2#?[tvC+!ydHWP%u:Rq%:3"4QtQ:?bvq-RzFi/Q!*UtdV1|t(chFTnd
s5d%BB;1hb+?W+_9l_
5m8qnUrXSg^RAg,
1nc}xd';break;case'sk':$d='$]^@j5HpeB}0
"*(`R:eU<gimm*;TZ8kzUaR(UHDW-hSX#rVpVf6o1@:IVD.E##+"2?;S^Q?A%`%wyIxWtO39c^B,J
u,nM4U58mqHXMrA!tTbI-t
Zi<F=?K1R)g6Yx8FqI[Wtfby{F,Pc9L%g>2llgl`&Y>b~"i#@y(3
q,kJ@20qGp`e2K[WF8;0g[8@l2m8OWJ|uUk1W5[//Rg*UPE~o<1T>AL#W!UAw;xFIoFwg+n~]w^Gc:_V/U^,F@vW>JcdJC:#l|B1r*y3
fh,SHuieD4c1ZJ55ZXSi:s)rLIF4%p(G.CwI#7(s(ZdMyH{x$+<w9oSJT^tY$LZh>!z1s0Dk0+*R8n~hIbHWDy+!"4EU`akJ!Dft6P?EMV)7;BMu*hW/@y[AXa_WS#xufyFEYa(/B
:^@qTn_`0to.z3krvO<kRk-)rM=UBE.g^no-|09oxcr_IAWXf)e[jf*onjUmAi)K1@8c/i9j`+B-ho|nEn%vD@I5/rRWsGF;e
YQ"Ltq$nxgo1@S2^.D|c@0!k=T`BbnH)B4;lrLuQmmS1EVk0:hw.3bmg/dUllo#26r]FKMdCjq%@Q:up~l+2]Ib2x#6xaNsBbu~6/77lcqn/J[tT|I&9H%D/hl{vG+|:E.}xlu]YL6E3S3.=K1:;k.m8
A,,LRZDl3n1%6Xydn|868-1J&16GyB&FG{`yG#Du%C.I<lcGp0Z/n:EsvLDgXv32V}aXtR@iD&O-6k2{i>x[MMrz<KW`fa]f!|Rt(,8SB2O::Mrj2g503GvV:<<QZH;5QRZ/Sg6~l)_dQPdaH/)@C>`o[|`W1H5I_W.eQg&j/)^C8,Kxuo!LMx^3T@Jef1`^h#
9i7n{b0rLj*chFNFaX2(Z<8K5e3Fi[gB}p[NJTSQ&1-c0_o)uk/!:npt8J*k0A.[^tJkHR0got2v9q#f!6]Y6)NQCbYrxG3V47,WnW@u4Nvc,]=(Fd*`u37jvET3+_g5K"@_x]2XP
]xq)H6f$_<wCcsnIgs5i99Hwr!r:9^@/?u}57#HLZUI"Mx`eaBmm~U}aX&2cZBBee:/[|R)LAEvx}e!8C"gTVZVd3<y0pOIq#IG)oL`Ey:9@X_(v!]Vc|Ou3!iN34;t;9!AqBVVbUFu!
,~i*+ttw1/?r)#>UhA$}[8i#vrs/@v`Y%S2uj(k(37AIC99dbNOO0:J+Ui?o@huc?4a]kPUG0k[:,fj=?ub&5`h@-*05F#s4"q<[(8nx1z_=GiWqXZq*Cy+mm
"6Lug~Ri(hct`T_sS@m5@y"7-%L9]rZ|ob4nX~Hc(^m$bdwl:xLJSqn86-qBSo7estIp;}[=dsdBEE#?jfS#;J+(XoXQFdWPaygQFCEGWH1UM;T&>2]o&Z25P#o(%GbWC"P|=.2?MY%wwKI:_NH-19A)mITVK"k,8cyisZwKm7^Yki[Nrj.U${${=l0=87pOwaGSyuI[?=6BI-Wg`O+oo|[<lIP)gP[*/rt5wiH)&KhMi-Pj/^hfE%RMIyRcE/d(H`m{20TS<nmkw
FT_m*DS6<R(^^=>p@#>{IEjXNeGq=UGi"*

IQi:VXImUBt7v4R1/<u
-GjAP%c93{S|$Ba>BxxJfxqflU0+6a!F=g<TT3gD4y6splX/Y99GSDjCH$CpHEt/r[MoiD,2FIA:Z_=b
4_~2>aZDYA":{x/W:4ePF7Gxueld*t_@3l@dRy3JOss4[(3*C,"!/faK6[r0mU>RZ!Q:QjM5>AV
uo##`&f(:pFg&7u"0OjY>"C.(OLwL))*YEoqFECYa_/!-D3h^3f]Z6fXjyxY^lA9dxRs;]TlW@@Q?Y-?*`=C-?g>|R(gzOBk0R!?8]YM7&KnV_@m/xD)y"r!<7XIJp@m*uH2T6,8OT$7AiS/:H_KO>H;UGS^)jN8<?u[$LFi[>D]{>cxj)~-O7*ye!3/tW]AlX).hxYX0-m8X4YY7iRN8ZMYNl]PSKUo(W)Plu&jf`*U}BMQkRPcWiGtKy&
]-g2U1vF)6*2kSJMkaLMCt_rr$xsn9[y@pL/zYbNUN<c$^s9bNLCZn~kh=v@_5y%UK@.&d-tzqCA3f,l_4/.]Ow:L!"vY_ZI];(*FVc*NmIag;=4~"Psh.>y*-5i7#WM;CQ2,2]xYW>5D?hys%V7nH@-JP_V01?*[Hm)I`x^8A+Qm/lhPWQ-RZaG8&>AW;99o1*5DcfdOH(Rb,vXYCN+jg_;TN
3*[(Z&g(8(R>qa/BH:yFo}FDy|>(KUcC^ZmJ`8&
C{C4g+sK$3xO(8<kO|)!v>e)^2
:`Gq2o;@9z)(LPDApm6NVlS"M6LUmOu1J*#HzGu9:)i4.$,K<,-XbF:tr>^T>WvgCpaUG9sFrn<[r<Ft[lOFm^o4Ol8+%(@mtqi8H>PX0Hr=V-%2MUNCN"Z.j[$jPbIcq1Wi`gHF](~[uMM[=EE#X*J&uYQSh^k+=q:qoLdNLefgnrj/?Q~I?DYU?:]Br>5un0+[$_j+9cqK~<Yf-sCBo38>cD&X#nIgUT"h)cV8.h=Vl
K:}Um2UL/smQ*/rPMj{@}g
[_U<CD3,nQrVc*vi@nc&I{+~CE!"5k]OSW54_]e1;[j,9?Qc)q@:rA6bjyTrC!br`w>a6C@$5-`0HY^GshL,hD1A;);"UoLnMj?W%Zi52WH6rVSzFPooCyN:%DO`e_[?0L6h?FAY=1`y^m*&1Rjo1s4[+G5"1[:Q:cZzD+M=5:lgwBp_YB6r0U.fu5(F,/v+,d$iGtC9OdDK<yQMp(V2exL?M,a"f(NeELh"2]Bf"=y]&Od+n*c"eM!Gi*A/F-rcG8E%$
^r?8^xuRBNU(RxVY>6X<+%p@2&QHL6vBS8>bEP(|9M:yUm.~!3n
mMSB9pEneb^45"m&DS2,6+l*O6*5Y1L_+<LrhNTZff0Yw#X0<me
C|oqZMAc7T(AGr6IwKZOoN8MT{`!+5#:-[i98Q7n=3eA:spm$h>|48hua@IF.40"ccV?_}KM3.06=<SUtYwYoj]-QBJn3+NC,HQ.XS+SDwb]pi=s8OdA
g6N#>9oZ3byWx_;mPLAOV!-?EA]vPfnU
F?x)
,Fc**?EK:oumf8[]3j[mX.>7G97.P;h2pWaE!f~o?:ybCI?pQ(GL5_U@zT
U?
"EPIOj#<^06u3p>Q
/&IF;xa6Drc,vj6/0^n}?D?vq9f$l:O;Q]H&mL#:bJA&K3$/N`?]ZcP$$%U
cSo>h3T;toR6qT]3kW
`qHbH+I`$):)HTxwT]A%g+1NemmA/+P2G8aXfmoFxo@EvrFo6&<&rSE:(EY=&]<>de+nl9bk*?lpr[x0d[5/8#m/ZJM_DQzmt^N!gEm*3.T%Y+AwCM$QHT)Lx%[aZHgU9-y:DK_IzJ1TpCf*;+Z"ms0[aqL4s!Q"<R!*3YF5](9K4!?_?qaF65Mml#+h_?f-}]GS1X~Q
FB]1n.??bTI[r#i%sPXgj.BVH$n7r}!9]gc!"QgtL"lj0LL^lYAhkO7u+[=@Piv!.M,b:t,E)*HAt|YF2GfrU-SLDLW,r44+qS[@/jA[B1YhCG_@"Cs!<m4K?IX;H`OYl0kVa+Po(]l//Js":_i3t|Y==(W122Kc,+6)h{>]8@%<LIq/ZS&=6WS!?zT&ky!i`Y8#.tBmBW2&vWM0x@avU[+ht)[|3;hgr!U;rQ6t6GJ-/:rO
,(@gg*&v)OtdtI#aebllnWrP{wVl)YQoBfP3hH30WU3_JL^c6[3v{?3/!PESUm`uA2rJ%d(3K@Y)JAx"uyO_gK}"G)zGJ(Htjb,
X"hoJEd`j=f`{D<xB$T22@#NvZ[l:nIKmG9+#eeOC5BZ2io&gG*qOeE2Y.giayN1xD(Nf!WXEeX$^J&pZw1Hel#P
-3g;_nn?WR8jbs]IJ+#gbsc"K.(nOY@@%c&bbvt6o(74>j${`<O26n6~L)]9dTHwO+e?_+N5P*<srr:,5a*SeIC_=bA1%Vv@5w=77=$ervv![tRP[":YK}#oJLw!HA!wT<$gF&4I3D^O-lr1P<X5fa")hs$ur91n
xNR"`?y8B*GwFO6*wZFB]ORv{!VBIneC[abF[s&l*n<9Wl-U|54Y1kYFu3eucA#,aXV!<h$BLyb[.aop6"J4"B?no.a%h`&yMa9"3WBn"Yo/EDObw&<nC2<2u-h3A].r1OQ
uW!UYbc?2sC&Bh^9k<:_B>*"vxvrvop>i"WSDV#/3Z?mVwzAH>G/O?6q(Z-ADQ]`lZx-;.)NAV9-lvKh4?[5;&
BGf-_X9$E%p##d-~3iH,5i@c<
9zUYdT?d-"WBlmBUc{uVv<CCk{*)W`pI@l]MZ2xyAR#uv~dg@f/|!^]/o;4#Z(^uZp$
];
bYV;fB>
kbW2X[E8i<<[!h9LH/(@7WVK#OCEh^Sa"mzh9)jI_c7$?=/Xm*W(kOKUcw_@wVntv[x(f4
Z/wI&vh(/`>@8L"KLnDr`)%K6hJoX:VV1Vi<>]v/hL9jTI
y>_N_cWWo^9^Gd5YbW7@r#p:G<kFfjf2SRZ<H].TNws2xRyK/Rnuj:7/f[(Y|@ha,[j^6BNSRrxW>pH-!C+,yXGR>h8-eip
kq?_%VE76nSmYik
+qf>LGQ4_.ZwB0%Tf@J]vN#>nxw`o%y#@]gFGZ=.#w`k,&*#LXnZ_KxEKjj^@U7U#ok,om*H#GA%jrh]-Y*y^q=.tafkbaoM/HDL|e3ov.Iy~9cN_Z.o|WCTJUm9PszY=FXAWQ0/tvhK;xco)';break;case'sl':$d=')ZuB?h"A@*80o*k[@<scl.Cw5Ve(o=&P~;z9Zq0kth5(`#75YH|MNY3bQY4W/k>Lr=MiDl;mU"5763(<nl@p|.<)xG(MJqrw]RcjrJ(h*^,)BL1)NUE[[B8q;X!/Qol.5hhs3>
Y"P1?txH4SDE=MaLkt.LfRq
0[H=_^ZU:NV.k7ZeG%G_;b@~n%ufH>J*i$A|+HKmJzVYvg`jl<k4&pQq;fB9pKm80!Z4ilZ|LBMWV)JN2Ktry5Rl
nmwiLjwM"MbXgemKph&ors*f+MZA,V:r#AqG!M-
M?8wp^)y)`Xxv_Sy;b,2?+Slx:SG[RXZh_
4PW![H.01,;r:Q>KrDW5lZ<=tA^c5Lb`+O[V;vB5n4C]s,>)CNRCxQL+_rW%ELbv@vk2G!F$M@_$grs-gc6K
FW0v]UYAL?veSa59(b;_Lr20e>jdhc]7gj/pz/|g<kw:[9[so1CF:hsmplg*Z0Uw;c>Y~[)]pI2&HmV=0sh.I*os_9#w47RY)w+y
q=j`<v[8frx|Dpl]+Sc/Uc[RlI2,A;RY1/m1Gn:3@UfO)1TkQE4{4nQXBVu3<XdjF
-evvxch2Qs1f@G2{1HjVS@1`6Ju2E_F2=*dIq9ns$`2S"u3IRwA
".y8<+R=+t44T,mzSSlyZ)>]$%)f;{>CS<3DBT/{c>M:u)JS<hph2yGbGv.aE5KYT4!PfZ:0x+.3SNP!mt8hNgRAhcmU`BEr^pf2"ZJt^IAL&A)9fp_"!WoDI7Bt521LW[q
q1<iW:^C3uqjx&L~D}odE_Vwn]aW9OaRklNPw<wXK|I>EsC{41bMvYbyU7xM,wn)m&uRL8DGe*bAs
t!l#tM3DP;+EalROa{#(H)[(cF5VuLfG1"RmEZONcl"jyp9&I*F`yp]<@n*Kb0HEoMREw6Yxr1J|Oi
>p"qsj~-y4ApD5~BTE8M<&K#S/G4E[3sWh%9cbsvl0^X]R}K
,7,.l-*Pryh_M6m+PN5eiJK&4^X;MY-7:o:K1+d1@:yfMB=hGJ
ryB3%[iF!(4g%OEe0KtC~S1t@RVCbd+v]FUb8]vvdW8cWFcDr;Wr>p=MlC6*GVWWmfNdR$@?3Qi?LSX;x1Dh*
ibEJ?-2!.GD0$@n^fotue6A/=P!k>5wf3T-w9Qo?Ui@b;tmLHk;KDi[=@Qya5
e]
K7?EfsM^pOi(lxY$u;Jl-[xS5pEa;NmlS?<uf,ka:o)hcbD7q"C*]2@s(.l#^*WW0EN#_CvSFOF]6os<3
]#:Z]blNra45RLahUMibr-]<NAtj<mY[,!H|)Qhq*o5aMVR
9U%YxOGm$iQ^([/Qq:g";YXA.~;y^2qhrl?ki#2Zr
tiJ+n}1J-yC"fC?lGg>UoVlcrGxm<(O7;5pbUl0hFdor)sJWC/WGiMCGv{>"aF9g,)tKB)&!?1>pGtSl9l4sEI+Ep#BQo!=W$23J==TzU1T^)kD$;i]lkB@SH()lJ
S.;)o{I-pk&AtxggR>N-p/4dU_ltHcZK
j?ju5-chU"c)NWlc%bf)<$z?CmWNHyjjW7skao]!q,>o3p)#R){0va7f"j4QK,IW~ha"Po^,zdcFAn:6P6iDK^bfQ5"mLV58-MdV>;trreO@<](%pLiMA%cd9?uIXh4LQf#+N]YoiHr$tJ
&8QmwV%;d@r!tK(2">j>Sv(k.y9PSvaZuY;@]cd^+~UIP,36lPiBBHXirS#g*#3I+*UqZ($Q[-oxLTwHV#9P2nUqAR?=<1,*-WYL@H8!)V&p:JpFB3G86A?2t&yn!7gljt_2y}K1W`qT]3R;j_YO%6UXjo-3!QEjmae1Pqx<dxu{31fXDQ/icip&.^-DO<NP#P`W6M75p|]c;-E&Lmp6L0NE^oD5$?_{taR&f~vi?|_bn[fBW7=-ZB-mwqySEkX$1`A-LEkIdm-1F~tX=/nBe!2SmzS&G
j2BSqfScG>N3>8xr"F!dh<@xe&<v`L;"e94NE]I71.3S!874RhOCHRmv7ot;%g0>7-.i6YTVu+5~N77U):C=4ZMBKh<N=Py=z!&]h7nqswN+-."_kWO^/r
Mq4G=x"9l#]1V9Y8rBmNuE5v1%PZ9%]u&V[HN5ebxigNVu+:ni>oc#^reEK`K`G@YGUXR"V:F1^U5bc7aG--SUULzdd%n$|fjx}jGT+9,Qk/KU}-wqOv>NIki-}igd5h
fzv8p5TeNxPL(c#]8fAa=~51PEj/36l&,-0#R&%$.
$<W=;@ZN)>8#amMaWRwMu3yN-c_{(cNn;:f]:VJx#)h"q9Mjl#p@gpTzbYpY
0hp];psv-3e(:27/iV%45xvtOWbi95Cyt4rFUVgf}Z9V.MseMcTt@BK7)3%X-e-h@fY*SqTWMZqk%TR/<!m2trRd|
I6bjLug@OAPiR5RT<c7Hf-]X>AUe2pdpMp!wltw?L[92aX:n(&DF]l5Kld>)+TJq3=Qu+2v
?G_&=THJ<TB3m-n-Nt(4a
C]tH&PL!Sfz9[V,:DXNcU8=o9.}`M$FT$tc`:]_]1A%Nwo[126z?d1.X%YrHVru.!v;J1T4nQc#bH]@"MpwRnyQN-<"il,i^Cc)Wq:Lu2#q09ve*["4s}*O=XV>$^tiu-=X"Q23&B=^%sMf>#!?wM"2awt;Lo76K`8-fW0?$|]p73ODh[C2:rC66VX.Ke
B`sWq)B"CThCI55[r<4=j4jCS3=.`"ik9*<2B93#m(,Rlia<r.hYH)*d[^s&?C0Mk/2ht/p0._{ED&aUrNL;9oEeE7RiOl5Q
j0e
azUZe%gZw$(:AYt:tCti_21sO@$#Hu$|.x^K@pqz2c;Er!tL@lSp^t[qeU;;o;q!p*K|XrLzduZE8l8g!;_F(WDw/A1DE2B)d<lp
0Hl=wu5R"F)4aWB"R_!^9LtoKVw[RIu@zl.EbIKRfV`BcI8^AYN,x6Eej5^Lz)FnCnzI,iZMYt#^6y01i`}X.LiCy>:/KMm^/Q*cV?@??_AB+e/gRTVn3N+ytd2xC!UlN3}HZb.3i/LhB%}p$$$KO0^ow;gt(9O9&@vn9^n6{,%2VD[Y>6R36
[45c-jZ3VlB16eNsxaM#1NfT36Jd,F1mQu]ZVJQ<ut!V:!vc_In*A6#Wstp$j%/,6%x_%O6Jg;Di3PLYhv5eY=(?G)YD.%$i@16]JRn(&o;3)
+7-F.sswMZ7`-v_K,!yuEN.ca
jIr-vt3gn+`:n$rE*c~,|p`a+sU:Q#11qaaBCP|Jk6[0W=K@)ied
2.wmZH%*cy1QR4Vp#i`C+.h9C$/=+?Hnj-3V_lR}sero^nv(6<%m47ttgNcB>-Mje~"_i_`[gB+&GpOyb.u%PFn)emm+h&,~@)&b.g*=9}RdLnS@O#6_;#U]T;?:Wr::2%#8wRe[,7:X@e<FDtmvKMddp|u+g>qPx<lqc_LeNJ7lym8Oz%Z"t!M:ct9ZRpp10+g?:0/
_NK;P7ozbH!MXv2k3$q1QHmSi!NF?z2.!U=VJv)XF2NDj3DI!=oEsns~ly=_t#laQ@P}C]WJ*-]rSi>tirRjdtC+?[M)2dOGhOI[UAC<p*Pc#Q*>3`RVM,Pwbfynw
5wOO$.Y~O:X?
mxEMwHt7p8Ru9TxJ6&ae(25K&eYH<K)N1f_L:@@?>phHk-sKq!|8|25f&omYP;<Sa"i(4Tn!O2d/NA"pS9aqdqk9#dtn599`*"p+Tl`.n)nB
5O=Oe>of^5+f"A@IZH+Z4-%hjr3llCn"v-Zpj0,G)J-);uIC(B$@$;u,d_HcWF)a*Sc?8*m}=apkFtS=t)A
l_2af
PGr"f&(F$Y$JLw9?T*;$@-Fi/B
,5~&th8@ZRu2;qz$]oG&<!1t]X[IH4x7()y1AdF5ZE7#R`*vP6SXgoap67H(ed8+Xg+<[];w>eyL&8M_z!E[+l~Hd)mJ-bH0y[4/l:kW=qjbfS9Fs0ka81+Sr$mV}dy@N,IL&#9yd5]-1*8PwX=MlT#$(*!2M,,`5QaevR-Ky%lSX+y4!hZWCq1+(nFQ#a(gf.@0)m9*$!5RaPV=/s.,kgb*5&(
|>aV}ol+}M>l]&S_>lju!V;eu?Pto4wH)kY`DaJR=dHx;S2&3PZbC1>)Bq,rdCPCcL5T*!Ob&oK+$1w5/T%RsCBdhLv[2*?Uql?sk+TErW
WOi.tF`!RdHVlQj)-Qc}u%w*b!QJL,nQ+dwmR0-S/Q:)S36Qj[36l*R+sJn:"<;9[FK+xMal2yU]k&21g4@o+7HZYiXKN"x+0}8:fO$,oEK18zxrt7TgP]f,GVq8=C`;fCioP:8oxgLiu)AX7&*>+q<}N6.ic69-o1$25br.,1qYVez#@~@t/mN][C&Rab4vcdjanehvjp$~G"O)75p]OBfY&Ih7c
hO-/=aX=d&_9OrV-"_%HSlur$!DXR}^^v4r8A>enR@$9p}kB9nm>l!lt6/k&"g:[O{gFs-s~D<6Epoi:"UPA:o9C&5qq;BubiO2Bp@:V/38~];"AnRxd';break;case'sr':$d=',c0F{bp-tHP?d>RQOrvW/nRi?N1Nj^v+_s7>oP{=wkM]5h@?VgiGYU)"aCkGsK{)4E)syuvComEL
f)l>j4nlwjrgB=mEKF$wI2QeX+0ejo^AuRx>rG]$EGlA)A=HAFR#4I?B!MVWGo2OrkUE=(JbroA*1#.LuR?d
[X5A(k&ZxE3<YZmFY>,!a%#.%V{pYO1!R#Qt>*Avb`Mv1jf+.lp>Iri;oA6).!CGW[1<f
g4K&r)L6owtT[7YyOhM!ikisRprMjO!91E$lr<1Ug&FITcCxsImn}V/xO):UmkyC"QlhHG]L-T{_hC@O|^j]J>``?*!g,ctnenB7vkSv}n%RUS:G_AJV;xZspJ$J4vWcDn+vUHQFkyd+x+@BaAVHIZ0#$46JBS[h0>E-bNoaaQ~s<a8E<Jes#Cl,GL<v8Za[Jws+X+lhZF6({4p&kScvGb#B&t<x0N[XxGWYMX707EIkCIMj~,kLF@])B!
/uIpA|NS#k6:N[mmq(e|+vu+O1"N7x7m];W/DY
Z:*n~]n()jAhxSq6p]?q}:W%T;sn"hY.{w(4J9ffS<d!?n~g:%]Uu!6T{Z0:O/}!^J3<#6hpuOQq`p+Gv.DGnc])iY*k?+$!4S
!/.6${^tTW*Ki$Y8x5
4RA_jR->AJrt)Z9_F_l.2G&5R0BL(aN(G__iVO{m
mmKXD9s9N(Ij)~abp=Y0#2E@ti$#IH2&x=gB2F0`i3n0JYceR$e(n:FrpuhM=axhG><%wbN6p1RubZ(QCG@CT~.kwd$~nl^#Pz7,`5Z3TpHIyE_86{W$may?uU7lK#b
mEc{?`yr7nhp/sf]_<<TE51%6L@5%@(6uCI%>2v;Qb+(KV!C=
CyiuM?>C:TIh!@<:t$g5&nw/(GsMG/!2<F);kRU&S,.zE0<)-"%?.VwztAF`jk7Zn!w-fVd)*_t}L
!#:9CzY<Ji`&EUGh9X;s=m=uhM6fP!;EVwcDbzlp+3.>hu9pE~"9^Qw>+&i6e_[Sp$6/T)b89`7Z)<:)b/W_tk$g+Th_UIVq&YPM)T0/g7@$A0VM.P=E)Z"x@U;Pi6eN=*a3J^b.#Iu`#H=DaR4B<!>ggP77%-&rUG@HvxR}A+V`ds?ZQ`@D6Z0y_Xjm$ACb^:;D%tdh"rsPr%qt?zSs2tn&(T6{pqoc8Ow@u!D]%$B"$78a[T)Fne"FU]qbkqk}#~&ad%AF6V#pr~GdoBY-4T%w>)<?J3#,gdq**U@o_!.iKLGsXUD8G.E641C6hjK[a4a4O$M,b@UbS<t3sV<CVV?rXa>zx6G"IB3!s=d.(Q$]BG3MObQ:g|`hZVK*_]3+E<S2%QT.G4+=x^j6`f*XO3ncJD1{8?u)*Kp6C=;qw!YXeQIP",,Tb@
]bQRO@>]s`%r4rBKQfInQV9B6HE*/Va){_NuVxpQV;T.HyRQec$<1-mW~6h1^HWh_B$Ub4<`Cc(VQnX
LDFm$M<1|]f:-[DxEGS>w!b(lg$!^T&3I9#BNZx"wR~>v#_WmBF:n88eOl01DSYNeNDIiVn=uhmCI%)SA*=/0opg+_YEECzN>w
3(Lui0!Kt:,QXPD?()[z^s92b:kk#}O*40Cb(hRM%[q$u
SRm=U7D38P,c,C`|+e
#!M4`ut08n]BOr%]acd7Pkrr9%;BIP{5T$y_?mGC8HM286co6<77PBV4mt07oG(UY,20qIBU>x,+GfU@lHBawSr-QcVg2rXxV8~40p?tKX^e#([`/.|*X"V]VgQY>+fhd%KCXV~pT3dIlT[PV<wc71c)|v5c!&]Odj5pIGHZ0*mZ_k#64]iU%-
ol1z@k`K@.,^M
*t(o^p.JQ?fUW.FUD"#%g_$fe#ezSz@-l`u0:RC|uXTC>/"LcuWR4|Ar41Mo!"cr./ijaY?41(gdK.=?Yc%Sm|3OT>.{/63CO_8U$6O:,;6(%uPBD+!1>``cr#,%M*W!/)B!R`yYF:DA?{*S:L9R$hQI5MKROK^8%@OFcl"%@r$SrjQ&q4]DS`X(Qk5,N2^fr%FRhaVvh{KYf2toDL6W;P:eMj-,5S3Y(>Z+M8]^I<(_]$]&/w*{EoJvb$SiDMINM(AN0)#Q?{
%g-4*?WF)FZ[ZT0J);w8{!dv(<S5GM7b$,6T,%omG
#X-d#bM*qsb]{3P.11V/n8soRe0XJoQ%`n<BSQsdgC?3{PdWCfB$%:EAP!F]8q+;ImpYL1<$:rc
DpMOW81:+&{R`x]>(KYpB`BH:-Mup*LEb9ZCP(DE!ON:@E;3])@]
MJ-NHcO)>l;N),6i6NI;rK3F"3vwbc=IQrDL`pap>%o2
:>QG(k.8E^h2;/GQId8ay>qEP@b3Kg|9rOrtO3/Cn&LugJN+;HIqT[J6$UZBH)y3vfngWk{ug+N#K*5J,RZZFD>rm&JW%f;75E%<)JZ:]0^-F1rEQCol:sQi@AqDX.<P#<S&vuG$_QSY:Q%F7T9QKB3
Q,g(A3R93Za*#5)%Z!M.Fgn4TafkSWwMoDHgl*09+Kpr5layN5Q[K
`0QQ[S!&iMb!ys^"SU;.*@j=eY~BNicEJdkgPm;&Xo]?:!k.R;#?#bS<<AH,pS;V:")Q4;CTytoNnVI3y%yRR2]h_
I9rE+8asZ@dUIn*mXFE9SMj":h~TFH$/?ou-ZY:#8N<#]
_,7kRm(-a6}bX)]uo-hGW1KEN,)(ipgpF7@eBVN"gRi=PtUV8p,VE/:SI7(a#51_Nca@cTX"^i-C,IIL5;V8WT=27`x8ALLb&..OIX_5
S!)q1MfrywNyqRLF;|4^4A?E$<`F.fQ3EQtSec(=f
guMW>GfrCJ1$b,`nf.>WonK;3
JUC"s#RP@h`rnuBorcVl:8l=!6.83l=;<k"[pNwtk}h=O|<$&v#v-;^C&?8n074hphXK7
Z$v[ITOJbDg~j#w!;wBwxF&JFDwy_q+{oyd]T&,vsn9-r_
&0BY9L%N:/|;Lk^$8`X_[8q6^RfT3Sok&wHT{V&<F^?UV@iT|fXQW6TK%V{eMLlFGLrxT)2@9]DxTgDA`d|PM+^u@>R`w%^
$b;46/QMDBDZfxHse<X6#Qw1a]G*b[)Y1OfE?9rguB2R]G%cL(i!66AbVZC!O@k
$9XDO,?g_ny+!?kKSWo.&8*bOON69T?@S)3L.JUx/CFHzbxoGoeYSKFhD8@Kh7"8
^t3ud8o<#LL7_uCCoD#l(hF?IcUY#CN!Or4Ieu!ueAT.>~i+U%l<o6)#P:beKki*g/<ufR%]o8uys/:o#Vx$qg2mtxpiaZNc(M%K5M(f,nF"Ou)NL,xCfE$&nXP|(e6LiBX*O]KD#]Fk+Pyq)u:@K>^y/2$pUxg;D_=T)Ddh-hux?Kq1o2rO!y=w&jXZ]^_4dDqx^d6iLXiKVFwGB_awN0Q}telOe1dP",36%qR^];j34[:OZbVxZ.D~591|wyHVMvCpw`:ANCb90I./mZinxf+@tB.u@wEu,o1I#H)5ccoTbVQ,N0Y6LVqr1mwYvXT__t9Kt5IB
Q
p;9Hh+t/FS7[T=U+a6i_#s}Z*n|0-VKW>1GVU?rp15s1_W).%v<a,dC@N:x7|n>G$K,yA+/;`/jB6/DGB[uI^FV4PjENL9+7b&jj?IH3)T="kXN)SI`vJY-Xs#o(P(dVcK4Q="d4"K+V(=Ta9>8xGqJC6DS+,-6ciON5)ZqOS1ZF,C?`@sN<s4F.SVOe^kk,`GNA7FS5(Y(e71U*Jkjd;:,6lJ,S<(k(,<2?qj"nQ!3.tZiNTTi-(s/7pb+X%6=hf?s,Ygo7+IAcPwhIe5,Wnp/Y.i;q_y]#*won:!,NmeOe_tFm$
V44Jn@e,>ocFk.+f`yZ5Sg`J1<*
jGYU#2B5>2"Ku;Fl#YzQq=p!_X##=T=&
TRS!xSi~SP`De(/Y#<MEg?SB51!8F".w/b@nriJMZ},kXPI=<F9!gMDQ<r5J$r;Z@yD+1yV88)yCrg8Cew-DqZ7vC(m#_d?eDD)[M??heDP
]MDqD:@M:"_W8^iIN?[B&!-4iN;n%6nB-Pi@ot>E(S>/ZaUqcB.$Gmx%=L5(/^F2F)@bF|yx&[/6+-
>E>plO)5!
]OMHO?l`}q_JT;]>x5`F807-nqJn>R9AO=ClrsjsGYr_r4R_}xj%OWoc;U><8V[JFf//Ix7Z$3<E~dz6YJ-NlR.owT*SFVqRKkn3xe~OQB-Reh5sP*&m)k+Xr/{H1q"BQVsfbz#<}l#=1U0Pxe=;%wn%MC1?o98P%wuum5Yn1Toyt]l4>s
Y~yD@U/!@bt=v+cj=+#Q2/FGDS1v28UTYR&4@#E)d1u,spFv.fQ"EuJs_;*RQ|Ou>(M~n*m-T]vKE,4GM*WwY[J}M`Pvx1UXS7g_o$)pX*2/>0v?xSu}J1nX"u8h*Nh?W-GK!$16OJ?dcQd:Q-$q,A=>P;?h"CKz^DT(9g1bpxg<UrC74g0rTht]rNWAH9hS#nQ!t!=4X-%&=8v?[SPM3%kRBFqx!6%l.
JvF(9c`g38tXv]s`iNNmpL/4>$fgHN.m[r;M3-VI%q[(T%RJ9zievXyB@Y,V&luu#~).Y,rJSwMjjOMu"yqqA/&c3LiX30^4NaW*1@1fBD>ULNnT&i+&m==$UDYx[bUt
Tl}Y]?0WPbI:).*kPi{ucr?A=*;%F*_c]sDCGAibnC%q|LrW3eEy&l&:_uw=2#T`Gn<g[.?f};="0M_2_r--|Whr9rVXk"jt7Myc(Xod*l,0EU"n>T[ob9nbuJ;[6V{GUrXDHE>G~;
[eS7u|D%>X_
UJNHKz[jl-(LD]Uzt{@)wDhDd7WW:Gg-JC+[)SqdR)X*^X;MkRSL<_Ea.bEI=mKBs$U06VI?HUDZX^=F(1uHmF!@qrNx"0sIo2=v+/B^h1#ath`T$S`Pj;(/:^M`?QD}I`^B6+5iTA3wobc@EVM3C`6,%h%jmE$aoC2Me>UYqCUr":v",V(|s.[Cd]Ry9-OZlBs@>(+GHA.<8K:N]yhPI~YT*},CKB$|QpDGH|*<@{<bhbUZyho)';break;case'sv':$d='+ZuALbPDI,z0
Y+$l_6OPX)302huVJf3Z@r4iwmvr6ug%#fi;NCO/bjI=nl*88;lWu{MPS)ay[`/:TD"^lSkWxNkw`81.M?,QdYH`X^m`[hB]LB]dn0Q$H`V0pD[BKt`udbC2Mn7IN|BMD{1d3;f"1w"T+[D}V"gk?st1mnt/%55`6]I{@FdlA&3S+k;@AKjrJERgUCi%JS4=jDU?9q<oATT=n;I!Y[;y5L;f[%R[KyW]nmcb4
p9]Q
M=FR8Tl!$;$7C!|L*tDxp5.
%2QtAq!4vmWy#MRqaw{v[MRRKf)QHGNTB2%v[4Fc~Bm2:YgyvgP5JUTws4I3wTW7eB"f:E9#SJ&n+5EpR[cs.`5&@f6oAG#-c*jWCkt6Ku^f>v[Lk6g`MBqevZxD%c_P[gJ`7V>.umDK!hHJ:a{LVmx;)=_%%5mppR;N{OOABr`G*t^]^n1^P&.bqYPhy-4qjCBREn
pPg
)CwcV"]lILCd&CJ^tef,!OVya_%|
dEwqp38W7kc*RExrBO7&r6C9w/Gx<rVQJLd]nF7ZXbWA`(jxnHrOG0%pvj:U)"3aP842&,QNNAnFJQFUTYGWm&[%54}K4/[6p
^eKNygdgAmLGv&
TUd)+SUGjm0nlOV%MAp^j/$%Ib88S)K2[5>i%bw6f%?L_0qUJY
IE!DN%mdyH08GQt.m-MZ}I_/Ly[73CVvIL]-{7qlaX
mh^x2?c>($$H^F@C55OQY0_ov@eX9Vm#_UK]uxj2R{_o_0XJ_jl"v`d:.@/
j1Lec)Ke<HWmE,v*u@$_+0PP#n_xxZIJ(H^FUg0X(B,{mTI%dKk3p&S{$*;>)X^6s?L5]dby_8F^L^T_0/e*E.M,W"4zyc^41}[vfF63NJf;<[UP$spLFBI-a7R``5/S/r_FX#k51^JW$6"G>m=}+Cl:yUb6V~tb`k6,j^+t<[Ndn?lF^Z)jkL9@XVTjHo
|"$6%WUB/b3D|X(QQL/IJ*V+S.StlNwCStvC="XLVJ
;l)2VQS[hm]MQ<3UGL+v(X.MHZ<{lX/G]uXGR?>+ybUwI]vO!LEm%10dbOWVvm<J&dKVYxWY*]6]vSBDpB)eO}A9$[j3t6u8E&Al<>K:iPfRS$B6WMkd;6BY2bo4c0J?AeJAxsI6"J<iXod]UeBn=&v|jeJxHjv<@Rl(PDqe+oH**PZGmdSI*cUeC(12%emN])p<mdZ`8FH;AQs,^&$O,jqQ*OoHL3_IR@9/CvY7Vkd7?W@PNy[[Vs^4w-a)0Pb,[x;":[hB5#H[Z,nZoqT9AT%$B31CxH9RLA!n9%^bi8.!tieT-OPj@?Suc"xL]iBXIm*}/m@+xx-UR{q]k[u*EWv
5R29w5tVN].w.=yzp@u)2#WK;!M][iORQc7&T#c#cCs8[V6XRIdk5j/#1T$n0$-jM|BJk&8vkw<RQD6?w*ZKwNVA]m`~aoN=WL
$J~TSdLRP6aE)vk-TZ9GH]Jnde-K#.W=A-~Qchb]@->xSJH
3b"9=R"O@gv>2h;y.vCMx08Pe*AGFxthDHck`*]yQBt!(SXu7V&G6rn"VtToft5bDi=.EU&i(fCD_D*rfuCPCNr-g&0)i>D<ACq=CB@%H-EBE?
n
^
98MJBp&
hY:
;`K?^9+@wNQB:[5.9ZrGY_%j[-fsK:I4u!pyHTf2V}?Kr?u,;3q1UFS*o"eP3gX`MjdH5ZKQYmtHjz%.?.<v$"RS%Uo~kHCK`2G.c<K)8q!%Jl/{S;ve?qYLqrFnJY7!T!F;L0m<_`%r(!x?lxV6LyYg$F3NjN*ghzor(HHJ$)D`.o8gYHa~_2`})#]&yu!k(fTieO)jpeQj;]`~(jyK[$Tl]E8>OmA!Gu*"TF$zNDa<Q&j+>u2&Rh7<=W<aF.T[gl&*(!hQ#pmu80).&l

aJc"YwXhia:@?PdKMR,E
NY`CjOf8Ussn9e$0$K^,e+SpuM3"A?BGQi,o2xF]G={I3;bH?Ao8{l{+iUCX~J/
4>{r&^`YiNVQ21v%Tz&om-pBz*y>8f]kaY`Y~8|pRNK#3AMg"mWuCY`C=QL;#80r!xk(2Rs=hqz4uH%FD>>^V?"v/hZ`34PT{wqAvTRnKC]@7Z^XEmg0.gfsfG+?3t@6.yYy;!cQ017=YQ">[-Xe4BS(516I^NkR3*H9Q"SJh/~Qn^eu,rY-$c/bzu*hBtN0SFv!PQ*k1c}
K,/a5sG#}QkiajC;F
urQ767~2|^y]6/-Q[F,Z!j`-;h_jCt*v2yV7dZIF!%?l1i-;bCGb7[Gg>26x29w`U4()&Wcw#C)[qaC3k
iUL9sdz+gAU2zVxG:,"+l[jW?u%>Z=ljrsq6]%@`Z)KDR/I?BdN=,jRRn@lf35.^Lu:Wi8p>h#kk1NoJibcsYOIY*GfyN4x:(et1tP7SjXY27Cd>6-wU}s-fPy#FCRoeYPQM@G),<HHM=bfdvO`]-%U0;:`l$`B_E&9U+Z))BwQf;c1vM;(gc[IpoBB.y(Op~+QiHQKecLA(b6vBF:@2koPtR*CUL9dk(cMl#Cj_Y2>JB9XY,(s&*oo.V0KAXOeHk9d9xBL0>mGp}Y&*8B
eZ_xd.gI4<9/%]gwjv?y3tiiYvZkI:!odU6J.0[@ZsO6End<VzYRQ^y$Z9"A_b;mV=bjp!@n-OOXROth>WL{aMNDD>Ao8JDC`(kG^h^Y0Bshl2@
#f
wxPgvr~s]
2:,_Eiet>ExEWifr^qxETSs3VaMPA^6m~6)V`0|L3Cqq,f(G>i!0Jto&clSg=g2e:gRiV9k^W^>RIU[P46K9DdTW3+1Qp0lU)p7P7+5:9Dz=%4Mh0t&/Jn8m>Pp]h=zP89|"*irWH+a;zMX<W%m44#K-)add@mAR~rGx|4Nt5$P"$_0^bT#
}#VpIw&XxIs[PcI@$[;E`1vL36{9g@6V!0Nx+,wi^&_7VYTZNO^4(/8*FkuG;T4Gs
R[NaGS|1[FJ9LtOJ0,
#_vov8g9V
2U`@$QcrU`At%]#7e@cIsz/D!O;qR=A!quy7+L]/#jP)KH3vca)t^Y8WX4ud>YG$Z]!l<H9,VHENW4B-WJy~7DOGKZh@oE+`UthZkU`gT*_&+]4I"O[>G*v.)x^>d?6~IOA]e/htXjNm]C1YdzIP`]o3@DD;Ti]-X-q(])4|",>J%K:1(C;h?}So!LL3so%3df@R(OSkbuahL(nD).2POEc{(vo97T#:0l%i5:dj8[*f/Kj;NumKxci(OXDAfv[.p{w}BybCVt2M[5y&W&j{S09Zgu"$&>DWmCm$S/s-sNE6`&HNrN/@<ISFu!%VXQag>%0_1`l"X`eOP%HvtwtT3*5.frUyls%V+#u8Dau}MwdQD+L7.c]`<H*&effi>|JJ>dp#^2/eHIk[X<r~P3ZMNu40Y%us:qjjMo/OB=Y#F_49K,fSnb)9DO[[RxyA?UN=,
ujv$%_u5gH3]f3BV
o%sBOlcq~/<ve,ESHj~3Q22t!lB(5t3<nSoC_1kD}K.;
;KoCY-18hR&5H8+BXB<,2a?"UaT@PLV*^z1LFLT~N*xacTJ8n);MQwNYtCfJ:&qwSC-oNF1E+ixS17K[LZEkUK;Nszc(hLJ;sC.&8ISs+`;KebkfqS(G$Eo=!bv|J1BPjPXqr[//e"TUIW4gt*P%@-wchVj6b3$>u%4(0At~Y(Jp9MSC5N_KrchC-`hZHl$8%q/#rw[ho)S]n~$u.4e<u`#c+6eW(}G,y$Ukuon9V*j+?_/b8Tkn74PIY>pky$5C%[w%b:+Tq!D^e(!/cv_NyIvQX@O{Q$2|l>E%!%,;&fZ2=v:qhLBXHD.WL7h(Hc**yq:4)HL&,++3c6
0Q~kQ5"Z*!PYlfMH5:CUS$#[Mm#=rVjP:26fe$5p47XIts}A`6TB&"yAWWwU?:tnU
t1:4pAmS&<Sw/cRvf<_ju@9rjoBUaK74{Ka&U,BmgM4?[cM5klajl``"f-H,{h&n7D%bSHItAsPU8`m8V-mOH7p7OU*WA9~4s[B6Z;UA8y+Bg`47lrFZ#u8#1Xw>b0_pNIrfbCjc<O[^]#^N&tNW[Nf*@vD`nf>VfX
1.`&[]GE,|D8<EterN;Js_veQWjtB;H&,?Y"R$>(*5>(.R41(_x?4vMl:(4b[zO7/x4x5XVV%a(;K_l/yAsVwfdDY-:!o8?eKBI%p65dL-fd0!.[H*[l!~3eO59$$svp!O!um(^/><s9L70+ygiW';break;case'ta':$d='%n1Q|csD)B~?z""%6?+_Wn4r6NMI&Q/!#ea_M%MuSKWR$gdYXJdt2IH8-Ad1GUV;n8%x=!Ki^lar>4>1:vw7e]T7uTkiJ#+KIET,;0,evrjA;]&7Oy6VJy.W;L<WtM?pWo
I&PPZ%
Cx_u)2Bo5Be=5VIk.H;>+7
s
Tg/8$f"dkz&7q-:xxGc}(rfN,;n=!:H2hUjBa*)Z_0yeO,V]^Sat6J]M<Q2OV5Ei8XHS,|z)1LC[wRxwnJaN5~+[2Euz&E!A_0m;U7gs!$GOx;Z%D/HBD!%U3-"E`
v~
Me*bxbN/pq#)jcuL^nb3iShc,ZB"&71O_u`e,&D3Z+Fh/^~uU>%$5v
*]me#wH:d=GZ"&b7b~RiC{cH[HW)/NVMK=nj)+#+h8=IoWv/FD,5I$]xkZvjkev-bxKz+kup(`uyEG1X:Id#SDrj>L),[fux6Xs<S+TVa&l7F:07S<@3MlL.syP]smxsdQ.>`JI~hL!Vq+rrovx-uDb(Fts@AJ`,cGcwIX^0GcbX6/`X7}y#/@ll+Div_}/m=7sc8^:$,$(xC}pDC#3a:#X:^x)UBH-{$OJ{31mSF5wgX32L?%sv9!HLTa7QMSRalhQRG:)JhPcq_5]_>#"_eb)}*_lWK@Oa8ET@<WP~r5M1^a!X>?v^n*0"AS[$G%>ZhA!*1kQ{w^ARW/oq@#MzV8A0uq.SQ$k}?BXpG=/Rs6!}4xv
8Wg&A}>Aijy~
OFq6aUic+?!gYr:nfp4kQH@$bR*cJd^f&Ln!%r6CRsKPL/3S>kQVT<bB6E*7::~g%t;wv&[?E<a&KLnc<-lrv3ffGj]pccmv9Rwri(EbTrq6/G[<k#"T3!MU~=j?YW!KWI&E^c}#h#|OIp[b]*PyJuI4dfgKVRs5R4rfSDAd(O{2~Na(F0#sA?XWM3{db=#NrIi$iGSLt1;]c<SNIAn4>O%6
oz@$VO>u"hcik?8l*A)dA+t>]kxYb;7,<E@1m?]Fic.o,gXmi.`KQ)/a:yD$w<(r^N8/KFv@#0e9(xnf$67aRf73r`pRV=cU+l&s49q,fwhJ%{BmNeTns:JcWp7a:^7WP`V:O%tYb}[?3=U`.]iLEXO2qQd>h0%NY>xm^*#9
{c"
?uKU}_RGSpf+M^O!
I|M?U_#}"YvEr!>Vt3.nwTK`_!#s=gxFg=3v:ghE:UWVZg!g#=ofnDy?/19>S9eAfWMifkxq`y=*es0nRx!/rSG?o
;F*!;G(KS]bU6{M;1.@nYIE|!Y-v<57=tJvpxKmktMpcKH[/^vA/!hw^01w|QS*aty_x"oBdE"]>b"ip`cD)lNX
Qf]y.c
:,0PWp_pF!:7DXQ>~HV.@w](L,d6E&VKBx6SvZ1kT4reE"gojqPLiTO*vtS`~m=FRlmNN@N>#RK#niToFs=gPXoqEFOB{y,vb"vV
h*Q<U|+;/Z?U[Sd[F/m]
U!Ex-,H;Egg%J?Er^iuv%dlK0#uWZeM?r#z<g1*RcH*^mx*9~;tVW$}KcG(kU3_Qt4qN!lMwR5n;[x2AzF_>Jm*Doj.@J$s,.BF_dj`mRhL_^&/ZCk,-DRnw5XG1igeUtlH?<mcb1
CCK^~F6KL.%DlAV0.r5NaRkrnMZbaaXl6d2QX6H:1/D?]Ti_eI>#4ZQ9<#3rwS#[;U@n,g6O?Sf&[@6kV`9^9H{_gY;uD,v_Q56_r6{NP4Q/Qp;<,Pc0=]/@"%V:1djgwvC6S:uh4+{y8617[%2"z$Il`xKf|Ty,o5Xm5_:*B.$%UeE8Y5>BJ=F!&coSlk/C/JHGB%%x/cWY#uA1F-nDMk+c-b/Ck^c+vH4O~2I-GNx3A-iia09;Wuem,,hNY:/CiT-aF^o3WEx`:k8DL<j1uE|GY@H]D],%A%vc3C}nwMAv5fiw2]bhOsE3178H#hx<?Ni>n/./^46hZ/oSXyiqWn/_sO}H"qWK!jCwLNkSJH>F!)[7ndqPzF$Q55d%1X:e"FOvpwO.G7%B&7-t`Z@
,u]i#%OCHYI@mP4F`G:]w88I4%ZEki4i@5zv%<qXwn)yt9w:Og{".=Y8GI4Bm,8_`)-rK1!%thl3llIQ*s[iN!!?G&zT;&][LTbnwH}EtXax)_1FZb]/9fLsk<(E-z%+yieP`xh1mk5n<0!_nORbn4>XQf:Q.*bjnZyBEs9W)v1WvYGn2P~4Hx[,x2[9Qq
-`rgl$u2c)ep`VY67:juQ|n{<6%-9
b;4,M}0FxvyMe
i|,RJ@O77wPovM1WmbZN?2v7v2S7VrW?$zJ:/!:m@,;jXvSj:doZ0?q:.l4Nkn
Qg1)k18g[IQsg@30-G<:mTqt#."0=e64MoT4bI^L.3/TM@ROR@anuw++ff2]eDaZLTT%2@Fp+tlu"u7,u`Lwn1<1wTK6O5^Lr9{;K<<,KsX<A8`Cd.n)XSnF$?=Z%mvh-8G`3wP9ocMMN.BaN)42JVXdzL[nFkD_yoGMsq&7}&(kRAN3O7IZW:MtG:b/e[SS"HKl*uV"pc5)y-1dWa@/usr=`+z`dXB.8%0l|h?ZM]ZRN:>whs(.e<R-a+G]$drMz)5P*NR(6]~2A>mbHu)+-:1
)fcy,P5C[$WW^/sMhW;guRM+@y:EdTNwQ(Ip04"ypJn"-C7c9rxPzvDm2%Q`Hi$"A/$/;uJ=Yfb>UN2iF.3Rd;`l;7V$&?k#nkbCMDQ,URGOBI_5ztm30;)gT]!``rIUda;mu[FA"li2{EneyD`c?8,)`RW16CC*
kG9N(<oGI^=z<{KSf{N+i/Wv<<MZ3ambtGs_n%nU_6Bm`vYP!^NCLEdQ6{lf<a,MSi^nG"F?:aFAN+PUefq*
}"[rVHuhLf6Y<vF)YZ]Ek5#uBSV%bQj

K3S;4^rT
58x[9T"onH)g^<.Y%=%!FmWX1sLm:^YkBcqJ"k;9Q
y3!9j5E<6bX=_cYjh019y9Sy"Bo#NxzA*sgSAZ}>jM!WEY>?/?T7f:._<fXY+<SfV/h[)?
_1fx,puQeQSA?l3
q8S-*4d(+!7KJ3KtMzD~UN?!;r;Z5ln9E0Fto76#;*_l)oHSKbErn1eNv#"UO)".e-ctn]6u#>HN_HTrFeW_T9Zb($mq&?jW8
@o;SF1pnu/M?cL(l)iUF.:*d#oH,O.ve$Qg8BKEqZLpP-IV^K<
V1|j6Tq>b[&G_c"AXZaQ+O}@O:V!6H5:t$,Mx+rJ@IaO=CwHm!qV9b@Hn._I0bkF]q,FlMR3:0AjTsmM=bYqLOn;`Nu>x:ELrfkT
&"N^e2U,SRuSN*LWnF@l#KVLM,!!U+RKZ53~8T:L-[>o?8?SWN2z@zD2%?1u;lO$2eYQ.`d_QH$idc_=6?:C,sDQ#$8bT}4V!KWSI]XBbDmp#l$z350Ed]4/oJjuc>uTQ9$lDQ%P$EyQ&a2sii4O@lWX[$wPG-!i.BFVPaI)7~fi^,4jpDp2i1*rdO=+GYs;4[USfsro)t[q+ekMA.++uQ(cifHm["0Z)XbN
vYK#uuT$rbY[3Vo]{g?.(sn=H!dw$>P>f#8B**v@m5&:{o2(X5:^P;KfF^;^P@RX(wp.oi[sK?q[DS]VzDDh.y;t|u{.?
~]x[#btWeOJY2;JUEZ/"AqRip[<Q~CZ[s[}IR)Qq"6,#_I-KuRkx@A(a5La7[G^@voqY>LBOOfNIt"8G~Vp<{N*:,nhaNAoJD2caf9P>UA
J!;ich.7m:4vjdNg6FPSdxnZY?6>INP|"f-`jp3xA&VE:?mK`5rdV>b8hW3?1hBE(bF^A
SUz"0qIBm$hH(HkpaUb[K3N1]:h"-SVj<A)kw4YG-}emeArE[.d)p;)cHkJ>A<j)tcS_XrDR^]yg.yuPkRXM-#n~">)QYc(i?>Tqlgocs^Vh;q&42[Q&:t`?p[aNqufw*OHjD>8v`|[p*`"sS5lBR*m{qiquI.$FU,Yy+U/ci~js;_m!2oD*B3x3.Z:,j>Y$3FL7oJ0cc1#99_(FfVhFJK]e80.`X!*~gF@cF^UyLxC+-5s3BB@u/d?K4Y29hQ6Os8<U^d;-`GlF?"D"[&HfZTG!4X0+e/N)pQN8tXc%I"g`ZTlm:/"K@]4$d#p_#nQr?2
2X"j[Oi[d%$dtSD_yi=bSriCg:T;m_cO)*7Dydjf/+XEFTF4C=3${5-_lf{8jW:$L;MPGUD%G!"bp`B/;VJ%wO<@Xiq8}
(xhkgCAqwkSCE4Ft
ol-nx{7/^:O?vZEz8~2^B#R{h;j)AZ+`cR&~

Ch#rHeonn]]LN4011#1f0*I3C][_5"yCTM<4+Lu`vBW5O+.ZP4R3IH&ua/]-hA>|[Z&HO9x;NS33a#+yvy`LR=fY%QvO-GmP.e8H?Vem2W";SdDG6.GwIJR/XGI!JT(OlsD
sKP<&gDe;529dtiV`g*B?``y)LKWL$p~iB)>I$]H[KnCfm%0u%pf(.JrGYq(P*]wkWXTA$FNV^s|Rj
E"G)x4Pup(cuA=8f
K[Ir*h7L+<@/xw1N2]MreX
MKd+UK84=xn)1b>@w0JeB*rwz=jR(3.^`6]>GJSjuja/TAAhqfBK(2x!kwk*`(!Qi`dTv+#s`DQKb!C.|yyFZ4si$snGkUOV7_u)^ZEBY_MU%^6%NJ>S7GkgL=`V&mz$Ay=p8XkC3AqAiSu/rO%&$+/;[hVgh8Y)a4Ik3`u3|kHpme6,[.ZCqRwV}xs`i*buTZlegg.<z%x<+BMVtRj<KkPklTqR{rQ4~$4C
yH>OjUs/cTjpjp?IbL.n/Yy__Tn]h<0:HMYCQze=p9a4[k"6g^Hw0sJWvv[67AipS<3Ha^4+DIbd&w`_oei7WkKhE|f<r
q~AK.t&YUZ@F4Y^Z4)q`fQk8k%xu:E*#n{>,$i(p;^-$8jp.Rtce&5U$NQvk]XvXa386;B!Otc_ps$N
0d:]AUZ]ezFAy,9iuY43NX23Td,>:3:p3K$J2Hti]T1]rqcF?yM-cQT1;TP#dp5gj
T69o4V=(6PTf6Y?)#d0,(M>HT[Z6bn0|eD#Df+a*&=Oob&J?MV*Cf`b9m!eX
e]IcM(^4kTcRaO/h!.%-W%]#_rmyJ"0>[.J`YO>1)#=bs&!VP=TUfnK/0(->!,ivzhcNNbkj%UPfa>}FO4T5rM,)0k`[&jt/K3nnC:C#Rtvk"W([UC0Ae+qdvZKUv<Gh4O`Yq2`z)k#fWBDh"(A#D,"Vvt)-,hOOCkAvI+G8eF0I0)+WF"YQ+$P,;1~dA`~x=:U"G,uys]~?u9R3F"TnVxK,PBeIQVw20_IV*PSYQ#:.y:lP;b=gu5]+HDeF`7usN(cjL8b#iIBcOhvw0^5Vh"aLXbbw&:]8Yhry!INuyz$JNVUvRYD1H^43)A-Z>kr#<rauPS+(5Fc41?fEzc%,ifvst3L5HAb:xFaU:+I)t#|7&>3ti^#R;3^3h:L=KIlM|o{O>-[9_y,beC:RpO~Cwn8Q{7[[XX/M}x6/=rUE)QKyW3"Jk"J<;+ebKm$exuXY:X%0sRW?air-z0<L..1wx%LEVvP5;L24mlt,-,H`=XG_2o#gw#D;9d>c;5TgH.jAo!#BHWbc@v,ouX5B&kg.>xn.f3_b!8-V.Cy+Wo(
TxuhKw^:UDx:kpDbku-3~dly>G#Gm<oKCb]Z)8V4HJ$N_C@MG4/$`"6^h&Ocu"0]yS>K0Q~J,86Jv0O"Sm]q*r8_vPABMfHBvW_]$"MF684POT5oEEx/wyoqaC1
$8cV7tF2Gq(yR^XJp8Gc[AN`u,
VEjBZR>."pv_3dU@:fiCIn(Q(zHmm+ah+]]D/B7+gKp+]Bn/){qha3Ili4qvX,c84Mw%]PcY1YN6z)A=s)G(?kmK7blq7`rC-UVwT1arHM:3&-+(L>j<:r&.MN[_@tcb)g-XSFs]N;GT-X7<]eyW>b#+.hZT`lS80syO)`wzHL';break;case'th':$d='&`GG.csD),~?:"**Cm>+o;HT@jbQu^}NiW8?`Hs0Sk>m5gwZ["u]#xn"y0q-GfN6FC^5d/)i*_{--S_0pgO7(HB.8UihMt!yOp3-DN2>tB^piX;w?EJ*w2BjPamA|oG<(LN?]pXiKhAxVBErDMJZ.7qDDAkgP+pV/kIV.nbv,7A(W.-af*#svu:V_oTMnl!l5["+>^o2@5v&Rxr0pLp)eKdp:LQ_=8"pR7yq|C75H+[p@o4Mx6#mN"=Q&mn,tuFd?MfWpcVpkPbTrnF$#fD#suoRgN.MFWj]E&_M^f4(eBK[%(RqgNqz"S45pL!i]<!=<msISXfq,xcw2KSS|X@_|l@,^FYy:VTc$H_8Y=>(b3Og@4mpDv3={S5n6beLEIj7@Vut)c?x@cbG/FmW8,giHo[N@P%k(*4gowpY"
[P)ncPeDunyOr**noZ%pBydKFrJL5Hocgu.E(s7MtBP=/XYjq%t4=5h.DL&+.pGPXtc<;:m%FG|Ei!OxpEB,h!P7^yLyk(FOd5qR,>~ZSqq39`iNseS:O!]]C/l/rxs,IwRNXcP,@1r%,n3
i2Q.TUL8%2dBq?zg6nn;>olIhv/ue?}PD:HODP&fO)~==.0wR^:G:#Et8!%A9*[l(0R&d>9P<DbR@qp.:7V1`v
+Sj{#fy(u>(BhvLfyJLlAfIK/t%%aVoug8=cyuq!4T-/In&0METHx:+$[r90H#Y5-zT)H]hd1GhUh,r{=*4M$t1#?B5S8[5k6ze0p6^=iO(hV"Xf?,Pe1xQM_odTsZ$3d>X(?r!:#a[0`
[bCDsIP.^$M*D7xfa(r/BW+;3,ni(JJr5-=8H7Z*IR#6Mlojum2s`GFC0DQAW8W{Z_P1@;oclWJ8y#_c0$ll78yjB%*VAgH)YLdNQ0obslCBm"IS*A$/6+6x1=unXqTo2yRMg/LA"ZQeLS_Y;eUQc>#8#fCrATQ
LAa.6*8/dge-]m<)CLxvw;v]#f2`1Dmia5j4^r-C_h0bCy[ZsQ+6SZ.=WAKDxIm94EqLFI9OGy-8
6uK"5hE4QPVrn9$O53g5*RQ_$^H
e3VK_,2PJ,yy?vVu:O}j=kW$4H%QFJ|DC"|]~pb6|$$C+m<Z]W]mIh;PM07DHPL6lLpGh;3&mfm(It=!V00!Isg1R6^q65JYZ7*_c1{P$Mn6VJO&q0bN
W0=_m=:3=rfRx!i2/ubkf{3322EEODRzw/U/kt!E0>7yq{$k=klqD85VoJok1ZCHi!kwS7Nxid@"$ooe<d2M4;jBq[C}!c0,=n&u$0_8PJ$zpp[z>#V$B#/0J.PFe{ODkU,qL|eO&1est0:^Ej$y$)aF_cVp"i-&]w"|tX9me^A>^FZs%isI_"U-EYm-X`>vPCsM9O=8)
CW.{v.:*UQprlc
>m-$7@hAH]">cqKp{&:,aNQP|s^P4)@,=a1E#k(6Fjp*^M#L
s0XIO"NDH0O(wK3xm9wb5E:Y+fyWI,$,PFUHRE"q^+.jS{I8
:%P1m)*)tv|aA
KB`ntQl[o[$ny2zD@l=Z}Ch%LO7vN@,jG/d@"_U:c,Cud)MkYUFs+-!*jEC=EoPqOgNR)QE=/Y[ye6Vj-E]5B%1uCV1#0jfE9m(1f;m;y_3h-OcjDoiM{0v@f&dIhDelD#zTbvTc*gHdGMcN8cIp{s5UQq:9xM:ILe`1MlWblwg/]ge@)@1/7Ro#/vFA!s}YTZQ?uU23%$MuE*/I%Lw"QQ$]3N&b$Ss4F4Tq3Gbuw(Oa>q_y>32N6>^!oN$cF*T:}N):aj)08EgG{5,!VY6)!3SFCH.yik;IHi=0|o!mdq"5zncDe3[=v$H:Q[$OWB~Q*aiFA(KE6k#-Z_]F=-a3_+=:VctO
wWHl$C"eX70V+>BP;TA.fzTA)T]:^w2vF6MS4VPz%X8M[^&2`-Y6T,q9YM-V?mW6GV
&)!DF%lJvl"D#G
*P:R.]6Uv-BUBg(NcX.`#U"qN<mpp..b`7<TP_*>:YJ#r-]kbeu;qrQqU-s9p
vIo1"*.VO?w"DqrSqr4)j7#5*7&WO9SnrCdxZZ1A[R,tU^_Z(yJS+~W*nYP.6B;i0D1xT@^zD_F@]o6NeZ"U2)TPCijs8u%0Q"xV1?#}Gx
+V;ei`|NvG-^*"5Jy
k>mpiB%`)-_fa3a@;C{X)L:O./?D49><:-3V!bR3i1<G]D}98E@Ed+6hZM>=!!e"AxRE5`4lh?}+4;tIbJRp?V/j}ZJnA=a@l:^p?j6t[NqX^#eDB8oL/wVZS,cb7,Zg^4%,uT1pj_iT*)B3n0@ZBNoCcSp/#:|t4)+#M^Z
-eCg*p_A983"3n!ax["b~j.Ww(.ETWc(Q-w;TB<%>3N8T8o";c-=50=I^$h&MB%W^ND2-a/?dR(%z%NV:SQ>=O+ClQ6Y%rS@Fm3^s[5%Jd?MqN&8/@H#J)yLP=CjXQ7xRBG)r*vJVH_<arAs_Ic])CuD}&4V
f[Y@YZ*dFl_]it`WI186r+BagC/jGuHav{OZD=Vf;UqAbiRNn}9#dk/]fkTi`j8}HpN}68A?7a.qEK`%i
0tFxM,SDi}jqN7.;#?H{u0;,
}f)#6omq%[*eepz2DQ!_LQJoiqXkNICUG0g/p.Acd8_=XmQJQLW*,>J+f^i3u<i
5RspjMZ6+psb(9cU(!-C~AAMo)Kqj@=i("~?gj!xQe|!^VXkfFddL
K:m0G:l>Kf}4J!Q)gc*e"kSP"5vp5>@G|GGee.h.daIeS>(#0r7hx`U9%+xa4dAbpa&sp2WYEEX,8+H.8gO!XayPdIBYrF%>C<t/3O*y@v[,QN{PnNiS.?3+USs4U>1as2w0>^T#g>!BKUhR;J)Y1bUjc-d1E+ow_:0G:Ti&|V2&pOkn+A`@e&7;wiMh)WGr-lt.2(L[(/ah4<6
"D+
4S:mtGx1k]_>-mEA>C31UJ~b]a-:}T;jX5RQ>;&V_O<W"L6,PKifcpKMYq<dAX5_OE~8e75R~RN4EjPY[/1Q3>R/na1"?`_Ut_DWaWFB(YsVuEw1w:OZvVQ4YI4>U,0hGJ~I0,n=k27*PK2]mJU
O^9#KNXTFd>_hIIt5/N.qtq/VVb;$.r#06oo6W_qOn>Fr^~8?YxF|YqPz$+<e0B:I79XJK2$-`k9G>*NR:x,}-5#GxE0
@ng4n$]As]1mSio21`A<&.,VY}+lP#1c
[?b_j<yGH5olLfYH}.21{Tlg;e_6G[Fn.1%Y<:.KV+z0jHx)Lk-L+,hTnD.UD2BE7P{og82Ke)Q^vXv_pS(DT*B[r[Bg:D"DHl&65HmU>`8
XH*?{fky%8bVd@qU^983jg9Oj>`Vmvoj6h*Q$s1l[U*pbC[^0Fat%8H]HD=*fFk>di|/A:U)-l#9P3:ra]P#_1HOMa(_![S9{v{kauln0JLPOdnjPbUl2.?(m8:.G.a(.&zN;"&J
!8dFyus2
"%I%m>p?*-w$@&~@KxryjKwRC9/vpF*J<=fg;i<x.-
cN66:h*687OTHLish`FIe@&qy#VQ#1t^QAy$9OhAr.fP$Hs[f$W;(vWb3~h!q
xY6CN%Y}"kuVc@j<ae
r;hFg=dA0rVlhpPRy"CA2VuLAU9#7(zb^a7W6/;r{w{*#0p#mLWNvtG>7O[(M/w5SuT^^=j!GUJ-LDd+gthfHr*x%9g
4KqMtxq]jjsAZM4&Gem2thW0@AT]gJd8AtCU,77[Afa)>]L&J]VnDa$<`l~3?8~etXK-KB[_ZGCSr[}@?<NMTCr)Ua7^Z.bAvDX]L)>]z$},d&JC{,1[was45oJ)9a+k57&Iit.F]U*I$.=D46a0P:_Nn-C+us(;.*4Kr2^91lY<3rxc+4v3(?=/:
v/4^OqJc6aFKV6I[yyF>L2ie0aKQy%h!(:(c:aSlyJ>0u?[x~X3_`^IT"6Q
G<3I<R<"@e&Mw^F>>P]x~]teMMl^3>YY8!,&Bx}eLYEX>ne:.uXC92nL)#|TRA!tE,Oms%Jj$o<jTazY5=iPqAm<oah]`*~8
w=^-=W;f%x2(X
wLG9UGZ&^`rAUOIx#no}"qW{Qc4HphRqQwNKNTt0v&D5srY3_AbS?
ps4*UYuoy@NW0mD2<ZE3Cmk6m~=$9YCS2Y<;3}Gd[ZFfSysGYw4b;.OU=>GK^JG$Q_UE?L.BZJ7xrU1T3A^R+]wQR;uWMY)yqZ#lm}<IJ}w?d8)%QAa^nsFt74L<LvH)UUaV^g1<GxXegCxxt!n&ptJHu&U]qCTr+LGC%
m
U-3F-tK^62hKM3w#?^[$b9-Y.~sBLE&MGkH%@~DUj?w?B)%I-+R~!a[F>fPyw/;@Qv5EQ^<-D^n4k*C<h9/Z3O7T0XMXh~$AcI6H`p`%Jkk{?z/qq"jzOd,,5X_L13f),Uo(*[LQqk%P,WQNS}da
A0Y4y,$*&Cw,A!`(d(&mbKEad?Kkq!x$oLV].rG5=/rUnmiX!p$3[5Vh=ZoG9N<=c=h1uF
/[kakm"X40V!+1`[?PP-rkBwMqm~w2iRreUA6ouR`Qdzwc>}(p%^L>QsmD`&j$1&JD2W]0V>-ZE`l>=Cq*1P#pR
X?9m!=e7m%=z
o^l=]g}Z~9"Q:EOfBq_oBWLcG*}!6N^rx!LExW&L&
gCO.^4V&qFqm^Qdh.>,Y]dO1e^"OYLRIKD](95&41vAVs<a>h6o@,]Sg=0{1TjLKzHg@#GXRMbGS9[pY]qPF_Sp)QP?qSGKqKoq-z(0BWEGWRk?_"Jc077i2JwG_}3-_>;E.gcQie*8.N;ZHODB<kq*A1BO?4emUfbwE,w)l92hq`_RFBF_d|Xw*0l_tKA^J=St]pQ7WtRZAhvtuJy-1x3&MXWKs0xf`{n]xgmYtWy"EOyDd9Wn&XIF9QOeFU14E!8NQ~Em^<%D
?77nVXIwbe|lM2ggGv1VB@$K{hiIp
#7&bxNgXZlMM]p5@z0v#A5&ifh~[:LP
iAK.IeHFaQ#V)^};NKGJkQ=QUQ;W.TanWj_PhW`on@MPiS<kT,z7[7.rAl%!f9Vz!,p';break;case'tr':$d='#UF;:bpD9,|?
8..*9[2+WPdN5AEu8pO500hI"zcKPQ4Q*
vb6s*j*VvSwIS,F*A-bgc[y0HQIrByDw%!.S[kW>kH](k[ri]b$
CB6mJ[BV@o)?Ow8f`LyZoHZ&XW$H3Vcdxg9H[gC^jDr{K10ff6k|$5,^V=h-2a?N5xj7MwUVkl-~
=ce;[=f9kka]Y&z`5iPXnS9f%%V(~
~I"JFG[`m*BWhT#P:A9VE_@DKY80XMiI
Fgmrr{:M<mql>K`3jl+Ah9yhZH[$AJWtXul.ogbGKh[^q*bonMtSw@Mn]692_XZ|yELOUQ2C7ZC4nW8#Lb&ma8w<]:tfe4Q%C.#wlY2_JJyv7ic_L?H([cB}1h5G?9`Q2F1CW~>Ep%X|c5WT$t&&@dXTeTNVs#MwoDJ2?Q,ufi-[`H5oB.f4eF_sltT?s.JK-s,S[01FX&xD
{)2YhaKy67Z5#ovQ-f#IY]tmJbaRja99]#ws@va&8/wDLVQ611%W#F9-qA%p)h&^fK;x.=D51^aY0i}b9lGE61hb`])fe]/-<my(uP@>730/f?3=A7[?J1fA%?M9y1
t[>Oo`ajB(()M&&<bj*upKG/:kAPYD=]iGgw6GUP$pi
Q@AfVOFS8*1LFLG=[~E:>S.$k>h=[Iy?NR_+3qFem>s1`kmYEs.yBn
"D=CtiLg_uY?x]Wqg[FKiRR3aLt=~S14+V
XExrvq)U3jFCS*]QR9j8+`cyC;G?-F6;F52]I6+CpI$_6"$VHRc
v1t#Wx]7I-XJ&+H}A(Z&a_:6x=SNx)h+LrvYaYe%,8:i:JGYIMw6Cr;^p9&qWDAw<=u$sZ@;y9V*W+O]I2NH@y7h^H`LV#T(xSiDrEAwEQ<HMHdF#H%NQ9iww9PfP%mhFfWbRZX,71-Uc~cM1*=G&2^/E"8ve#ft5gv#oABcx|]?8A@Kl3]@;x^.!(>picS8Rl+[p:.
R_6naJ;bZxH1v#V8CEXBmml#GZZ*Pb88opj.<$DR_fqa,b*0IP"
YSEq,qAP!-8Ep}W)JFcM^$^>C=r_Bbr5!o${jdL(-VPzk`;("ndkb4Yk&^s6^au/Zy,ZOW,XvfGJ:Qe=+WHQWzn*E0npMgtC/A*jN:=s0FtYQX]hW&[I%IplRqqUi#/hu>t}@6r{%z3>BSQ|t"J{?e1
6}S0%%(yrV#n[M+x9;Oz5iF{bVug1baQAU]:/J[o&]A~4vRA;yC{vHh80stw&,[56^LzR+
jit;MAyM?/=8|9~v
*Pq&k%AQHr[/MWTFjhMO4+EyTB1!Rk,8!qF18hoO_UC?wifrCRM_9*:{%[</*/ifG($#hD>;x}N#S=2ivvfB66*V6hQk=OQER<
2ywkA`XXUWT7!q$$LCI5mAmeZBy=$T.hsfw9.^A/Gc$l(oGQov
4x/O8~=-Z8o]L9i97(Tzq:I6`?ZFIV+zX}0b05bQCnIf3I62KT/=PWu"@n5([+3s90I+IOR}bylTPl:T"KD<PtZcp]Z|8lf/"7Rvm(J$t}>Z0XuQ5M#av=i6DO4Vna+hkC"HG?3I?3wg-YC4Q?T&GcD>4eRV+.$]$+SH*?km^J#"V/AmGSuHmCYtgJ^?=By8,0R|0kif&?q`pk=?]soOb^lLuMZrR<":$L2Lf^>O=
&$7@
MxH2AEN&|=v
i7Ld7^p
"pn*~JGUBN0Fop{
Cg9>P;FqSg)8j<yc0<S12[P=Vi^7FTX:]$w1`=)!^&Wh
^|[@2Xot(bnTP.7Te#@r/eomCs2V-dWi]n5"c6VJMSr-"&1bwQFM>%@>wRz!;ahf?he/]%i`8Q@f63g_(_udCJ`2CqIB%ec7asH@7+hImq9vOD]#/6k:ZHWXs&M%xKw3>5w7Uc]E+9&a#q)^ErG2EXW&US:aS+],wA]?km/#7UJ<USasYC0e2D6.IBx>HO9@eT0I38/!D>d?8Cn@%K-h-yD~wl,H2:-RCSkd[NaP>DpLr_M89t=,:rNHTi;/-ES@!Mee6gN4!aS>F?/PHe)96G!78/hWk86;9
31#:=
FD6Xve"ZL}2i![_b/>l@G`fF38QjP)XW^mV)&av.e|YT9"HE(gacUS="[I@/)k(U#-?dP?;$A#OBV7=4RS!.Y+;o"3"f&D#uYG&7$zaPHbU*pg<&CI)EC;"g3f>wnL=h6;CMkMTC/>[PkC)s=yNNb]KyH@jOQEN,(5)Wx8RDOSWZ-ZEVYuQr=V=7mdNGJ
)I-CgH+MM/^cUuWcQKQZ2w=s[qgr3XA,SylNfuNqn)Vkf7,
0IvD?Zk2$A2wU.X0dw4//+vbSG/CC[qJ
&SnG}9MO{g[Wrz(cPBHTNWqlTWmR9LXPG
5NGVcg4u}B^xPsJHx"SiL&?MR$w/PqjvAyS5c_OxQB(@Orh3KJ>h{D_+*U/(78QU?]T
c2nqv4WL=.zc@)]WBl]ZK$NU~,39$4MXq7)EuA-Q!Vpr*OV1se:(L&&SloEhlJFj1i&k{vsQ:JWL.<u/#tfz!FsH4)JqaLH4J
!kZ:LHNgW_N6P6dafd~*K"l[PRe.B8?-!(&Fg#<U^!p22XP%?XS.8NK4(#Wvu:-$TuSI4b#o4`Xd`B4jF?-%3+?2?p5i@D5LDyoE6"EfF6U7+>,S:0-a]P|rv(O/j,5.OUbcA=0hQYPRVP|++k<Zx@)OO6yA"?yh+9xDwJV(/37Z"+7%eB?&]:p:_fw--.&,*2eH)pU<~<?+:R-,ppl;a:O:QaF6pG(9>DBgzTmk1
@l%$[.2ewNBHJJn;Tn`>e89[T$XR@p(+#OoLy^uIw[{Kn%($ySg)_Z:;*8DV*U
V(S-wuX<RL&d1B_ZFQ<9CL0CK|WRl:ZbANH.55e5_gFwjt4Lm43wqas;Ch).TLFI#8C^1qN/_iVc0asBITLZ-sBnKF
%0bp>pQe
)NeN"PebL|$Y3NqE?cLO0YX(B=pZ&JC6;~[-$/GM@uo(bzlBI*T#m0a4.E4J#0^2EH3"*z^pP*7R,lCn2ckS_eyD^I#~)*bR.V](Pd6=r@?yQe07?9/b-LVRMo5fuJpSqGLm)riqTf1)M{O}eTcxWz,H*P"-lgAJON[V3!y-@jfA0l^yLF4&H+a&h
V0RgNX-rWGptR|x%<v>F-/s
vnpl^i_.Q[`R%N%-s!4-baiiTqud!Q<QQ$]FtKXn@=rmV<Q5"I1Fe_Z)x4>ByT+NfT27!;S9s*y,(1bdieQWnY6Q>87%$]H+ttJ)+whQ19
3[mNYEC_LcQj#(lJ.aT+w<0.^Q8>5=Amy#&0%S&5_>G,#2+(Ha:&>9!bJXRWGg`x8[fNu_;jZMf;q1;T/mGw%pPLa3)h)gGc*#QVZlWGyrI;tC2/o?D
ooqu@TAds`!q^Fn_`@|MpF5O4X251m!#D8yHUs4JB"
yDd![{:Z4IvwY&93m/a7By",@vnHbJ_vbHGlIz1PN(?GSeAJ2,oT8PsqBI1S+",uYX@
<i"75`G;]%QYd9Yd-;Gf#vN
lzUPQQ15o>Uicb<yPyOHmG={twuoa1DZy~)JW|DdjF-]k4wp3f`;&BcY/E
zGpCkc{<<%n%}oW,%WBV#1z0,
,m~=wjj0gf)[j-rEr,v3Rn3?_x"D*4rL8qmdNiZa0YM^8!yuJOg)%xZj%47*tw$byfqY?D<.
i,vKpm%;Du5#5}`rdxQ-7W`-uT[3y6rSq?Vo6/ts!+"F.!3<:d=2UP`mQf)j#j7Q@LYMo1s?mMBj+AJ=:"r:qGKX7[4I)EyXlRjUM?K:E6f
rlRc(QRb/E3p(1-[y8WWi<tQci;|oQ<c2m5HOJRe<Fr@;/s1#~=.h=JhTuYJyvVnFhSA3BK}--SRC{EQ:AJk*24F-#Dx<-RE23^*)Y!(7X=g#`a%a*2?MojjgJ_o`R/]VUYU5bsMn6H%pR+~y%Sl#D]P2pp=p+lEkGpgE8yWq=6]1y.%%<>?z!KhKb;D[Vz(gY9xbks78NL~+EYTx|R^Y
`Ee5UjG
Mz5M.Ju
o%#(hM"Hy#mbKGW}=?!Pit.}Fw
Wa4P=8,ay64DB=VLYXy5Ph)PPu-#Y(Zrw?DwqFau&E,MdD4,]ukMV>42)-RUb]./j&*^_+9Bcu.`8JtdwAG4TyS1DJnRqBy:i>H1omtja=]!;qZ*g[Lvr.!._QhO%YtbOmU?!<o5hEjya[|!6byww59yJKP@9RJ^c.U;ZtI5{WH6QW=Ll%@u:);Do+UD(<`1o?q<vY~xR"?Vwn=<ISN*$[vLQ;!ria!b$]Mg)J%`f5gDR;
FQtQ(Onq!NKWxk=+NC;(cNLv<ui;ts)rc!d<,kG??FunS@TR3oIfySi^jh467;kxZQu!;>j(HA76BX3B3"Z=IG)C
+g29`3!.ao6Ki4PH8mnR$<SxTHSt^';break;case'uk':$d='"evF{bop=B~?[d2(N?+ldUwT@jbO/hj8gj1>-s?Is+.6GW$!XU
Eg!S_;VjmS.G(;U;EI5ITsm
%sQSXj<_s<mrx;hs3[EYr/;Nm+N-NrQAEXn"mVcKMto}mJ:FjX;w?(ji6G7$v0eEU4y>6/`ohjSsx|gUqyV9H]9vt-Z|k/s<ZY:vPZJ,AT74sLq[H#=expcP]WMVBTNaSzkfkgGyu
GdEZGWO6ZWG|?3GaiDo_.AuII0oAn~,c,Y:esNIZ^e<;:|jUK1=50?pT/Un?_h^HDz,9!jH_J2Dz<YW/>*cl,sP[l!tEUHI&An>E7Z1<+J4_xf5b3aLzQpq/vlH$o&*s1)JDBSx^k[w<wy`3n=t!P=mZ3"VUiRl1q(x51
K[aml*`YHRw}LJ4.kwhqL=ctQUpJ1VYg4|)3(GT!TmUjp[2(gLDxqeo^D!706~lvnf8^yl:
k5!wPh";SefDkg/[bP13JcC`,,UyUr8_q>gESykT_
RU`mwAYFig-_OFx1PV7Z/H$T$yuE&TlGtC^3"rU[P5aC;f[/Z5OO1QW9R<SAxi_R=#Pqfw"3:<$b!l_1el[F,,28Ln=9>mj?*[qc(q.;&0GWy#`Ux0x$IIUbBkr~X{Bt&P*Sgij4E0&QbEUk#0.5+@h3lkj(deQ.[MWHmW8gnxOOC&vkabXK0hK!^po-E=)nAxqTc^)lbmB.?<l%6~`U+/L~7+$E0oo._Lcd(Wipsrlki8tM&&sVY<@H=?[<b*fuuU(Wg"YY1`c=Ehv50?*UE"M1[cKc?1A0eeJ"WZ,:&3CTnq]"nnf7xpM#:1dAm*^HZloaJ,n0J0!D087V5k,RH6k=Xv)O9#hc$2sF)F3V(^rTTW,/m=G)iU8:={;
L?AVGtl)e0MAItv+^@56koLRH1bLN%7/=`=aT-&WFk9-N
6[&]]#hwKBEUYTsRb++{cuo=q<4d:7)4%bl+X)K/FRXq^dVnUzjz1;a5c=6,PH1bHzEkciugJKDwN3ZojM[w&`]%:R
qjW2!-l<49-/aI78gLN]/)JQ9&4M5Z"S:ZBA5AL+G`kF?@?hbGQ6($zo^V
yfcDgK^R!!te`R1z[NKr&+LYE,#q.MM[/HbmX
nr4R]JZy&g:
>43["X<Jrk5ME#E&RiaByp=eG=+fc_@O6w>TDNoceu4+c$%b*plYJi<YRx-"72ut7l0$uoP@a0
Mh</CrwvIU;+)<$fK
b
{9r$Rfosc3UE98Fd[Zui:-1"DkwFcn{*"ex?a[49lFudoK"MRYA9<G81?p#t(>I0g+DN^;>>gqur<;{e$]yRjuGJ~@0*!yj.Fy(j5Q>d0&^?sUy&uQ$r
2=*F3r=8%$yiUO#%BT?14.Z#fU=H!|*oMi&p#Bua!##ZBWM,9-L%s#Wt@5/rR<&1.P={;zP!/0L`Po@j6Te
v.GJp|1uDJXq6:%65)q-n5r{D{5)9j!Q
ngm^qT6ZiTEPK>r>qd5Un+YW
yg)d^u]`@}%4WZqX%FFK%eCu6G$1*dMYt3*[exBpCW_&_GT{SjyB><7(Q$bL?u3%9_Vt7lG5[l8KV)*<v$S]v",=J}v$H<>rY,9EjK3)-iL%r"C
AXMl?-IT8=R{A3+"!UazmWg?oUr&(:^&Y[yV_)^^p]PbU/oMxgC<p7`4L!>~Q78MPqtm_,0lLVdwV39Us2i0[%ec8)UxO$)llMCVBHtBsWy2^o.J*r0nAKQptl<Mfx:RqWBaKWYNltxk_Cp+Q&:g69q|,`I~
4xoY&$;vqv-`bV24>FO[l:%W*l<IdirOq;Y(4/&yi0L;JFtk@o9A:JD>Y[Zsp,):HeFyjl5QzWey{1eHm4tT/ag6xhz1?cSo1JD4|NzD#BO[]BXa]Ku*2%~l[d@IwwOQ0+XL#s@S2>Jv~$Qei4^`LecJ8c-NHQ97N)}HBC6i:B,CrGjTa_>t4v6;
l[g>n@Z1/a56>|.ToONbg$g_$ZQ*wDB/,Vp4`n?xY/<m]u"}jY+N(@&ZB*luCPHpZ.FcuWGhw{bYL{%{i@v$xXh~X+W>`}&FfDleRE^*[t<qvU!(`SH>N=I#Q5mrJ|//#AW[DLO,+$IcG:6+T*]J7!,7Pv-oq@JW]>VGk*S~AS9M)(RAEK=b`KV.EbWdb9^,Z)7j)XvP-%Hun
g`%WMu2/*$X@FfQRAy*4:"edw[?+q%NLEMKkB,Smf<!mxW=l[!DG<h=@Y+*QiR-gUTe1#QKm[1i{f_^Xc%"s#B,p&Hu-`)3J0Gr%)Oc$k`8.mD%IkXCOW"bCP,*28]v0/@-:+-Ltpf=&hxoKE5:xH47JY!_^Q)an$"7&"_4L;x_JNYy/?i.r"*0<H34([7.RlyPi7}0AIzq`3p6=HcEc:Tr[Yh!W]#5%BI"H;r)_T^3P/)(C5$d]LDL<+/0;#<bL7W+(W2D2%Z/.?M"&MqAcpU<)a%"~VEI~"U(A5TpXx^_P&2kvBh8g+]Rh;^]VLt:0Vm:1X8O=0k#%D_a%Dx3?yWs(FPYB!BVlRK[;LUGz`-W#$y$0*V>:Ed!*3ap*<dkql~<f(ii%Le&iP}.(HFRty/"pVv%h=S-W#hpVOF
j8KxsRqTXsY.9ys+rZYUq4Uz(F!
>NVHeKAr5VJ;bs}]g<yLpN,5e0If;Edo-C3[q)bcI=a;z)f6kL&6xG!P-%y3|iooY0m>50UA{tj^ZtrKwAV6z8-:[]pX}0>0Gv/53&A.YWYk=,k1n@SdaFD5dB;k1(uSx+Lr1T{E,"H0gRoWrQvqnvx)n8"5I/xaUdeYSM>=4&4PNHp9eFLhEpLGw
qH,/5u#6m*[eKtx`x@ztFEM,Jf[;rf[f~h=:g<|r*24e6#kB
B%!unHGl=&k;YJ6=x?pec)WtU9X-#{z#7LF5S0D{P$PYvWC}9C7MZB3Q0Qk9!C%_RW>XPF@9fi=PoB>0&:8ifAm0+=RzVa:{Gp$KJ|!bN1<vZo1XSUv#(g?vmo>Yn?tlH$R!]bBO1jL@3I1]$VGFYZ+3n_MvnLr_T?&Sk#$o_>S:k$Zr/klX@b0&n}9g`*!XsmBR4V%pJ3*L%miNDNS#[n7SIj7*boi6yB_~_x-[vcBN]>99OG6Y97k(43Kz4$BxM{jDB[8@fnrB8T[)t7Ma5>p1dz8dh=KMx/L#9$
;(R[B_9da&h@}Vap)9,5#p,1q`1TTe[?p]2KnJ=pptA
qp9`"7n3W$"b#pDZ^/~Kno3e6?y9B&ONZYjcZH9v
J+wm2?Ao-3O:Hn@g#b[d.Ke>i"3}ARB;]VH~53%QE_[]`39U4?KaVq+$C|,]2t&By-AG$!`HgN[rf~Y53$/rw
;gK$kPW">0Pd-m<)77%0JjF"L95/A5El)%n}DYh*2."VDLgf]3gcFb;pQPP%.tCU>U*zJl!M2asQ
SO}VA3<PI0]pfi
.evR+BCJ_#t1+zsA=v(Lq)#+e1dr"`/!
]A&BDKUZD]O2y*
]gj<0.,j3CJc--Jk
X#]GMP>Sy!+hB9D>4`*5Veo:gfXPlmOs5TABMXA8",:.qYj]x$0d|/Nn)SD?z;"L/N&tCxpj%uN,zoiJ5-tWKmh]EE((]3+Ei=?PKY8E&W^%kri[;^{I)%;6zf[NkIAJ)BF_AR8$w@sad
c]mrruBq>*7a=T`rropH9vXoYa7=yBK<zo[`4rvv`8x@vN=[
6XxD2MC$JR)0bwY,^pGO
Q,o7eDTg)`|Y29?H3JbpxWRFs"#nt2ubWm!(d_nx9Zo_zf}$SZpW.=UEnBf4U;#C6v7YiJ!m;!(
<kiFB)=^MCB,-Veu70)I6vI/4oI>sFdida-mcQ}L9mxw)7Ddz7WT
8)i"NN;C`oK4CSKb-l85fa^h#(Bx6hy4byOXGvOhNsd"Q><g;.ni$"R"%H8}s{`Dbqv^$LnO;=>2^{^<Z!8/kq4j_z)pVyWx"AUgPu?OdfGXy>:nD.-
YqxY<Wv8`8k9cUXdCmt-/{8c6h6"R"6"tT=]pCkc>=V[Z0lm^-Vy6:0,WO1gq^pXjkM/p`V*`MpC
sN6Hh;A@,_G<*/zG:`m_Vr$/pt%Kg5v2f`t9Pu7l.9dj?#,_Jqi[&LkM$IF!zD9s_WgVC2Z$ffnTnv
8S[?/N-FG7f"vWHF`9hDbJrBvt5skd%@B7ChvmZUhQ?*:2T.x)no(=c:r8E47_,L]^nm9yK50oA/5%Lk]Lr^7s>NKLg&MygRo
]XD0E<F"t"okP@x"M(X=pErm-/H[05kn@hw~"*u=ou8YgPV[QWP(+Cf=20=Ti9nLGge*AEQRa{9QlQ3otnL=yB.|u4
w4}Vlf5OlJ,,FVF,bcDDbw#=Uoq^H2"Xz5P_(4M=6PJF``jk(C2kyB*8kA~e3UBk=BHt[5IZkP5ayMbhwG+s#O&9r[bL9AG#4I-lqq`8@WA6q-nj<w=W0GT$rDtjaO:?qw*$=[?>o7+92]T?We7nFmN8Md3^fODcZM.gia|CeSoLj]t&)sG!8?54
p`(v5?u+0EA`@1_5oxcUQRRb*,[rr!/yBJ.,Aq!%,sq0y,ufkN
pXF4jRsS[jQ-]4qc$Dzks:r)8";TNbQP(CYi1%@1_={eLD)oioKOM]|%UFl?=^h@}rn*4<LL)Fu";2t;0xmkF(^rZ
g1D5X/NAFY=?+#vs8h`Li!o`!sNT8&gln2WwqB!v#qVIeXL1uqfks$N46@lO^1ea"t)`us9i1KGuTth?`qY
+%YtLJKKKL#1w&BNfGyJbC1I.$jczrd56
&B_*O(T*;"u[lJ}M9T_QN@bgakW0u5$J9b2R=T(]jXz)YFg6;;*>j9lKiF)kdykC6#>;UhaTk"lL_tg@zy)-M^7j%>,i~b7+EWpFC>s&fd?1-Un!iKJ6!x^D8`{7;Ac_G]c<iq$JO=d))>&%Xx5d6/qwk-edNo=<1qZMz$qw}[TUI#]JG6)kxR(0dazGF,jpI.)"r)a?[g}6WA&VB^ruv@?Uld
t.QH"`.T&k3#[A_t"qW-oD1347cq1OJZ2]MO$;IIsiD2+RFWmyLlKUi/auEDtW3.0[U=omHAu{Eid+L4y;
Q&cuN(uk5ccmo*+oRA%_1<^1R2S?F&m8x8Al~W&Z?9NnpiY]b3&^@9c2QB{&-t;H3H>Ba!q^]e!joGhVV?"yZLYCMVkJLN0emrz/lna"t&m^/+^JsFpA@Y>+,%s&?&NVUyL1-b-LyUJy"_mB~)kRK3x;j)56*l[4o6{S+5Py.rR25Z|,(kADtlm;_l^sPaKFhs2H@Oe3p+.;]?6=tpp7O.DD%m8(R[rr_Xry7B|fdl#p<,/RUKeaIZ16513(6k:GhF-;7.{]GLu@`/6+weW^hB{pnQ)^eukoQc~O/9DM]/dQ<^;c|u:';break;case'vi':$d='"UF;zbos&,|?od@2n8:a%t6&TVRRLW7#;rjR]NwJ:4"D_!h(F-QsK]USE.D2r?&-i!T$l9s:0"sDS<tIR#Y0Ooie:wPxvK|nlWwD%;$M)/PiN5n__cX4&yV?Rh*w|ck/DQ8hP+^V/Wwy~bY<yqjnIjl<d=P6%H{s@qYl{pe5NluyQxWnO-AjCZI35P`8a4Ab5HB=>guJSrNKzse7(/gr[c*TlL.<ssDML"Hf[5}7dZ@UG<TZol<x0vuT12oL:+~l;
@In`,l1x]LjiR+6I.TGHL2^M:1;s763v}%@n"9Jy`+BPPh@>q3p1Lq(N_XAxQ?gxp^]&8w>mO25B^z(FjG#=e,96t4}heJwcTcrPyuwAZZ;J!f-I$44vjsvr-J`r(7xTJ=nLNmp:f,_4H?K%_0EveId3}Lf
foP9F6T>ew67
q#D
KLxZ?5B}d~n797]|)DG?QL5WX[Wb>EQrZ=7[YeCW^.C2tx/|wxE|w1b4:L*$?G[.tGC!Osu"Fiw=oGWrxr8$V;B4LMlpSL"ZF:X}l:7yRcfm09VYSV"}&(JAX>1bu_vdlzX;
:e;vc>O>{MT3ZTSbK&VoM".ZXfdHKCKhHa[i+ifq}"ex7Db),K
dP4
qkIc7yn:kN$Jq
a<qOf0skgSd[huDgwzZB?c_-lm&AB|KzpOt9UPM-??r(&lRQT$qy7Ciag~WnMK=aCDDDw~<|WJy-!LndRLFK_lapZ5A)].shN`v5<7^%NIP`=Hb{7=s`pgd?b/bgo:?A_Y_taksI`kdDtBu|Oz!ZDOLN>+&2c,5O`YFHArwX0tOWk?*fG%n_EYE<;nW.KQy,2R/:r,:|a/w5y5t9VY/8vwm]KcB+_stcSXloO>,Dphg}9xyr4uY;_4T/%Uw:g4Ak"R
OMSy@0ms>#4p(:.FB7A.<q(,zN,P$;nNQK2P(_#a,otoq1=>m*z4w!>M+ru`r58YaZ?^kZqRux*B5Hh8h.k45QPtzsz2$/Gq>PU%C>1Bgm}Chm&d!5{ke!oT7hGU6O;Vm;lBm5#!kZ=nCt$c9^/OJUPDNmm-B!S>aZ5Natb]ACALtD%q/HJ!FFTTtPc&uJ{!;weay<(NeY9HI&6kAjM
.6#WXwV-nSxWPp!=C0BvK?!5XJKI0P+^W.|&4NWs?pN&5Q{JK!Kha@)1Mxv1b87>CGauxkxedSD>.u#N(#&_.
?sh8$411x0}).s"j]3i4pJxoVtBo4<csQ"6J(9p/)+Q:3CK#bt9ls)>L7#"t6y7")*}^^^SI|ejAxoLAhm%ZuA^!j3d@;%W,a*>6,Q<%P7rL3cJgP(6pC.RE4EuH{go,0%6ms362$+"k"0K>eI+q-,o4DmNHh%%U==J;Rbf(jf^k>4N7^Q_+|cIqf+9>NSv*PgG+wFi=Y-~0FSoboFarI,7,3
WgBhlov;Gc(VfQNUFQE7Fu:ds&U"A/X2#Y44#r*v&xV`pd@/+0EY8`7SrR]uoD;bC1
]rS@+)39Ti$;e]53Te;~gUl*O?@W/Kk]5D$AZ6aSMm6-.%`J@L-y[:=>eZC&w~[@pJlKTdZ}qGxp#"L)k;H_WRm;k-W?aUP2+%_vg.j#"q&7I$FJ1et3ez^5X#gv3-E
;h*)Aql@.zCyFB1Zsq8*>q+)QvD"Sj@B.G!&kz&Oo#,+CzG^k`$80a6l6NaOK}
%DNh(Eu-Z(
@0kY#pA.P}Odi$G^P/HwaUR$wwhx/INH)=<?VtRY<F(h%Xq4trM@%2te2xpNP[8{^Y%
ixnbVM?3P26*fyX7S(3="VedX8DfK6m"Khy$Ay^#l!^kKpsJ:Q"yYXe&x%!sPh/>A6ZOV?Ob]$)>/
2S+b#`abl8y<5S^](([</~OLFC@|5|*f@I1)iyLPDHKvfli%[V(Z7*@XY6gC(:DbpZOby9:(NR`r4EXk(<Li!<C@bB1mp]gB3:x4TOE9=w@6cl=]Ofa,[J`NF+nu4ZiZM,x;D&v3wy3p)uwq(4Izj@C;VwNVq;(`PmIFZZ@fLuZ}M=FD?oAXjn>]
jh?(PT"JXx{u7X
BVFfkp+m69+#x$[L/yTv#p9~a6+fXX.k0},p75bz;Nl#UcSy`M61Xm!B&/izcbLqPbSo%gsjp4Fi`}2.qadgC|&RZ"`C7sJf,G,di_.$z&tdh^b13<_OLE2NsA@t%]I0evGt#K[bomxnDp-:!Xq!S)KA>"7kP^g0^`Xf[=UofKJrx%5}8n,bE8VtFxV/nn#@v+O`9(kMryyF59vB]jR6p"s:X``urZ!zNWF{"lUQ/1omNilL%T)
Z]_$BCe<U7y>v[pdR}(Q%W_e_v
OEZvAHM+7`
WAHb8OeRBnt{<]:+;IHNr5m#oj=<x)GDJ;5h:.tBGu=K2:Q@_r+GPGf!hWYwpM/|./CkE"nE#QwX!X[)l^XgXIxCg&H:eB.j,u7)i2*|SKCRTmb+wew8.x*?/{("p(gdQjd!TV/eqhZ68M75!B%Ktq9bOL&}4i[k3lb4@8BJs*3<bHn`B>fXM|a$h&93s
)dvBj=+#l5KCerYZ9N]ADEp}e},zk7%|>6uvZN0<"88byG1(Wwg.KlH>Gs!npY4C+uSn%1%!Vc3Qngl{;MjPj3nFyr#[r[9(2hykBkW[_*C%9*5,!<UI+At[w3N<@S0X?fLv><_3,O!>DpNh:pxKy
I4ZCPbh;AVv0*.&e;5qS4H(_Kf$6RmxaIbc@N@l]`+nJKp/psL?gK`Y}C3kuMw8ed8`!7VU/buT0YM`gnfy!k)Y?hcgJA:?4;SsK!twDAxB!Dn.5/G;v`
.D-|W9_nbyqs"^D0"t_sDcPOg=
/sHCYkdig-H"@Qv%WWk"Sf-5
T4qO:
spTvWlHrfssxwo)d6n:8ku;9Oo*V.ffno5]VQ;0k#<tR]Q_"Nk_Gjh5Ay7[q+0,"iC@rQRB):DcF(ip9sWKvJunvc0`n+>xOGro(<$.|n[cNM*ZlaPUn:E)ZZ&*fPz-fQ>"slvM^J5@u=XI"*42tENKQ5vtYM*U58:#@=]YnW*U`[e(;u247&[)Nm]Z<&O5:OIH40]t.9Jvjc`Db[~KJ!!l
;PZOmr,f!fM[*v8U<M]`4<@{F"fK9]0_N_afl-<CP0&N&_Gkfn3`f_r0ZLGRod=+MW%:_Mi^6s5Uk=I5QoX3uP*xT:,R"=f%]X1Yo"P}I+1nTP,+h)Z
<mF?1"^1XoD7LZGLZ`rG<iCfS1v-[OX[i0lXUbiGo#f{>((!<(7h37/4hxjLotceSu.Lcm/v=>U<1w74V"i^ii
6ZqCQ5TM7^AA&hm]y^7"?jyaI
9p*Y8uNCiFX7+3)/?>]1gj89LgIi]^2A:LIE1:_TPPN$Q=jiuTcVn9$y;*mXB^S?tb7Ii^g59^n0|jC3!5MW^F#79Z_1e`<+sQVVC`MO?/~#!/(2>-L>}4/oV%KZ~_sUQIAIxirAR+RSb0v;L5DFQ`EVMS
?7wL0ZcbA7(CY60]W*f7A!fXOuhY&7i%
K1:K?ug&Y;FnQdt*<h-%k7
umG#8-A{J^:p]7<.CcYRcsX.Jbff?Mt$+<Q6x<>#=MxWGk0Vd.7HkcT:9GXU%bMS^js2=}$MZn"!6E8(K]T32:Jbrz2~hA"Y<oY^q5.{VLwZgwatigDRe{lzb+OdRd@oYT-AC@kH*VG60|aVpd$s%0K#OH
[atq-M]YrW:vURc^I(^ij_#`TK@NOV[>6[;2aAiYx6c<:q"9WIPiWm,I^La1MCg_q<&:?Z"oz-,AKNipv
2P]Ml_u"6q*BqtdQT
k;.<.4pP6dm@aU)3M
qs`/{R$oP]jH&eot>7+mmH6PoEQe,@vnz
1LM1M@?gg`F=&Nha
tcF`:rwHkA&Z3@Z.1Zttnn
L<94Jr<)n*TXPN/AcrxlAATjdLQ1"?pRlTHY,Q+j5)/;)ki[JYpdQ!!j$=FC{^$@jVka_$GW*#&L)&eJn39`|U!t_%k8,(jI4e7X8<wFwCjF=559W?Bn3cQh^a4*.Ec:eMxs~5fayvYUw@FO4)UTk&ny3H%KO^2:up@`^pd=7b-f>HRw
Tk<Tlgkn]<.U9750m#=K3jL01EQ[0Q5qQZP*H=)Z1+lR_U[[T0-:VBpKW|R[;k3JmeKw5qW*+2p.#1.u$8_>9/]}+I[5n6;ufn$/t>xDPJ!bi4/=_UhM,]TGZ[bL!*E/=CFg4?^xV^89_2+ooEA4TVE?Q)^Cqw`Qa};r9{QpI-@}v`C),c:>E;C+Kt_MLGrsy7G-m?jhTqTM3f^a2)WYo!aeN%X4b}P<&<;b=3GU$*hW,tpfn6B)_bIm;q+YC*/l,T)mkKXnMPvTs>d2]k+(#CTH3L$;W8ui.2&$dwBSJv0n[A#mMc7{etTneEF3W
1X=*y}hTHP<%!DO#?ns!=f>{E[F/iZ"kP@Kga-B?U{f#Ff_sA&msF_"42QP3(k4hp~nB_xd2;/&Pw{Ex3Y%wBm;W!xd@@%JpUfnPKG1%nA_=Ah4.Kz7CuA/}P+3P,$%9"<ng?,_whKycd_p.ZCZdX4h<,7or
WE}H`J[.u)fC<5v_&2:_n%%;v)dDUyMw9[yK8P$vk!niMM|#:pp=uowpyLIbV6#y&2R<9v}GcjaBtLcM3cy.4.S9h;BmJahw9pB)bPeW)y2x{/j4SW`wF';break;case'zh-TW':$d='*R]7&bop=C#]u":2d-9a&&WZ3YlWA8$E}`FnpV3#8?Fk%-k];%fU#YG.p.O4=(4sY<A67U)gRhoDYLlPkVL<In%v3yWJwYdmGA;O8M@Vy7`L>I6KJyuZgp?oBmZ(T,dn<4~Gz`J[Rsf0qhIXvY#WsK8w"Nf]Mq*T_kT>XkREdN}"$sV>MDdFdX#>oDf
H?-vjVYD}KEdIBOr7Qb1{kZ0F^&kD6BZ^q<UGBm(H52r5"{x`3}Mb7`18j`H6f#FVns(I6PU1%(2G<Z@Wo7XxPYs$qjZ:0+S&tWiDtka[<9XvCa*qfkw@v;6+sFbor9Z6B],cMfw<yxZL24r-7gQ?@[=XkU=mGV]J_kIM]Es$bitIyz"!Cq"DA)_{Rf-]>Hhzi]/9(0
^qMa%x1WN@W$-`>vN`J,x!:e9UEB$pF)]JgR0DS[L,h3;gMFcTAYau/]pPM;Nk0/wPUvm8
YQXiGg3N$wc]1(#I1*FvWT$R[68DcDAw`&:n0&m(Fb]mdI8y=O]PUS-$F[V{!F;:_G?WsqvbJ;aR^O
o*-Xq*KrfR3cXE53!
zaZErc5kH4C>ms=?E;dj&nb2b-/c*[|Jk"rFCs"cYIAQEQ_Vk
qbK6DZHpITz%1Ip0)P|L*ef^$,MWA[J?c3dR#"|
_jmc_IN^iTHdMej?IQCqZLgy52znf?d9lfjHx/<j-tpy@H`Mfp.N"481/Ca:{SLw/u{9#O>modJOjmjD)l@qcXUC
$l;<9#[]R9l{nBb$_LJP[@1<g"M0sw/[d#EzSx&%vy)LM@NX
8
k7*e8sW_plF%c!zQ1UD_(FhdUJ!i{&&_}p)8/./>5yCL|"~Eyuct<-<jx4^P<C}^DB,3.$i@Jw?4qwh:Er8u@Rw;C6A9hB>2,rY?1?CZ7HM%Y<xjp`6l?Q1U?l}U"3?Jg9>:lby(/Q+!O8D[YhOSc>ZYpj"e+Hbxi5+r4DQ+!@2J
*D4BAKc"E>J*Q|.bQQRnRcH/*=(9/o+6X6Pm-wOtvqM])EP~)xBWVg=-)KZ=`w0c:lLz<aSA-uV[hylh,OQc;&lr[Yoa&eN5m7M$_m?+rdZUX@;R>,47>4/)FqD]O/gl^0?MQ1yA?r`CqCrd$OZ?i"r@!4k,);RwmGV!5)$7"0!b*;Mb+#$RardZd>FtfUo7>>=zAx6z284[!I]E6h4{Lz^$K77#7.b_,1MG4c.S?G@qWO::nngF:MEU7g_Vf_Ry2*rDiAx&%Pme_zKM!z%eLz%ys^;`?/Fc:4DRr4VHIa"29XFBq=ANGf@)u(P}/0H~a|`>!>A=pbb|lqXyPyDk6h$RfJLYrv,9#`H9M)l!c>g|2JNiNt*w&{,O(D5c,NwoJgR9caEwe.MA&b",xb(3&Ye<PdIsNwYgg0N$BfXj#Sb[.7<}WgOvtZ^m[1*=nB
&v*+.&r5[cy3JAel56|s/TE95:~j|^udXgU<af(
_5oUDrM^DN]"PHR9SCQ!S1xJZ/A8g[&&_avZe-#R.sgFZxdPwC8._;4BmgkND9EovxomZ4C+m`i8Tla
vQi)znz<)YPE|%Ko2:B*Q-hJ8%o/#I9R%EC(PgbR4Jl<^K)#VAr/ha5PmtrFQf@o2&K<Mh3-X-+52(*<keU:c4C&|b;6PHh<T**#bC&Wa4La2j:nZGO`4H>3nX]
MubAuly,.&%EpvM[,`yPxGs?VY!Se=W$TW{9<i#*+"I"ZQoi9mF7sHhK}.cY>k76i^V#(,,Y}EDM)fdb1Us.QQ+#QXT(!b;OG--`_LLsdhB=)%R6[`Xk_h2]pr=hxsEPa9AExn=HQPza"SNp[izTIQa1Bx.t5?SNc1,KN(WXtj#?47%px8R
2O3*LWim
7PavD(!]IF?4=*:zV;EbfTn]IwC5/q(fvj$WAagmcMC~%&#~0:LL!{Dfd5Gzk?l7jxCxPmL(31?V`i]-_4RU1K4(O`b@*l-}:,lC:B;RC0%Yl`GF)}K)b}>
265tT[>ebr2jCisTUrL|
*>%sqP*uqEnNfw13pY>"xECf&x/WXV4shS+e>5#rri,
hJF"/R9DIN6>x*f8VPy$h7a5BCO#"64)+:R^_lyK>
+Hd]K[*FvP&)C
9g]p83G^)s*tP@}*+sp(B##+}HoeHU]+-%S>f$2B5hp$S,Ea7o&CCu^rH?La*lc:mQ_)e(,:}]gYym}O
q,.x=

y:A%y8X/{s_/51d45?|k0@/ISr;<d8nH~d7a?/w+P&+NN34+>OiV#hHs;G}K7L?4nB^<KUuh+[XD,vCeYwx5;PetTjnt^/Aw5FLHzKcfH_KDl-Ud3t4uY6vDpJB^mYRpFuncyDK=e(fUc1vF7XS6n!|3d>K
lOM@Ht9a
GPIEEdhP.`,*q.>lahD@((8~rd72/&vV.~Lg"kg`L|dhwH-5e,W`:)8r@>2$&RJM@%n?%ciKr,DQi_W7Io&^Ov8/0M<W[Z$H6B2TNOy<8jVcgc,^SV#7g?U-!w?m]E@o[2
,8<&f#Uh:YaM{eY;-[zAy;"/HlCC/P@^#
-@zn

oPF<rh{HHf.bC5Xn/Sl0Pn;eG8zi*]^)S^wWC!tm)=CEkb-^H/;G
@jD+0^"3hHq-Zfw#"UtENP$"=i<Bxk!YFi0n>A7U2`O7)P_C
]1(-l6}]~Dso4Q"qS:PKArB;uWhmU+SouD1l]<5;&noU>+%ZXZMl1gg`="XoXj>`kGrBX&A!_A<AlA}<}Fl=ZGO2wp!U9Q5GKxkr.l!M{8IogMm
lNlM#k"J.9gdx+-]{oU9I<)]%6be.f%MOQW%O9inHA7mTo@
n4k:AUIu^*V8BqCwF(g4x1HDK`}-HaX99e+1|su+o
);
LXM[%-D1PuDYg?E*?5^7tHpopOGmd:gjT5[:tIk|
8Bdwhd;Iq
lN2E/7Qls_GEkR%^}L3V$$"1sQ[pU0jC@jjT:5~?1$~Y!D8iAEcV]1G/NXTd1JQ&Mw6(u18+9p`7lkLU)Q`=1dlVy7E*2eoYB2mo3!QHL%bAGW@3JUD`:E,$_96UTZ*p-#mG<ecPLee=$AHh|$m#1Y0tp&;ZpFf
%t.dO57oi^,Q8="Nr)[d6tXVTas=uSN,MXV`Gdi3(.E*<L
)7.C3VkPTV5uBd6h8a5f,>%2JNYZ$ts_ob".h_oH)V.8gScERT!hRDS|Zfq*,8`lJ~7(Pqf@K^U"?PUo#E&S($vXl}S`k@;)]5"@)E1<W,xsxBNh5E3,)D*Z">@FLj!Q
njAM-Acko/]
|Bo*a$}T$1$IE4"&O
FCmoxJ[Ac!QvXgh:tp7j9n*u0qYw*5!?fM%-cevU6efYe7i0q"FGRlEl86F%u%7c(jDA:b&(CaF7(
_Lc!IOTrJlv,=_
xf,]/Y2r$t7Pq^$J9KRehP+=Sd_+&;:|,:ZG0hVFwe;Cn!uy_<7byd-t^7yuSma3y+b7OOfCEYBOe&P3Q:p.Hf!@a>OR&I[ZlT$h>}LBD":w@XpB6:88-ns=62bcnegA2L<drc],Wix43QG:kgQ]5oB-Z.;+_Nf:upM]2RbT7$DWK8@9ysQ8+9kZ@
N;joQh+:#,c3rhm9usb/Ms,"rZv1nE+$?zF0cA0RnVFo5Ax&lRv5nKq?9^5"4lEmu)o@sy)Vjt.ukh=(rcj)P?iwS&!nwIM#Ppv<]{`mPnm=-WrcFnBSmpylQ_qh[[R-G)[*H_5^QGv(7uL#yYQ$o:$HL`hMOZL|LM5bTThBY/h5dfvd_+!Hf0Gm+-.oZ)=;A,mBC5$HiLXZg73MC"?}L)XSBDHgvZRk(TxQv:o**F
Mw+gruvq%>knviochn"
.;pQ,66#AR("nWoX+Sss+`Vp/i)CP@Y6ZUIOpcv2`u#7zY:Hzx[[[WM!<cj:9krhc26phLUAdI3${;Nw[>[qsdK&#%mb4Wmo-ddPi*.!^Dh^W4#KU"d!ixvk#-S2y=F/*x~t>K$3G]Y7*>!X4"?NX!4:^O+C~1
JtmTd#.^j6YGQSO7kHZLrbIn;zK1`zN/Cy;{WhaPH)x57Aa@b&&qEdm/kAt0kDyhudLOvN4K,^k^dBse[(s])G[=MLI*(yNaB2,__r!t+oA*YWHeigV(a<iftF:;+_Wv1{6HvT_XpEq,>oz!HoKg:~E.M3jQo+mk(n0XA![}E=kL>;-Vn?3a&?I!fZNpg#iP?>H+;?Q2]|5,=u?XT0rax#sUh/"45IH.!e/z]jp1Yk`^$e@59|"w!tG7^NSR,B^-3%A>H-0P%|z%z"!Q';break;case'zh':$d=',R]0y<]s&B~?__xdU`J_4b1+&jc2D=l3fG%tLYeiZ+/Jm=i4(u#J6h5)Y:,(6C=O@!Q-GsX"Fg*YKI{M,vu/
pcb!nsc>fUD&BE+8FYm"G%x~x,wxYmyBIS+n=vPS4UPHoZABH`b=6%met3`~@Ez$?gne,<*zhjhOqk<1RL4eO/6W[C9tX?+smW)oxXgor=+tE3RQLLhr(.D?)%y:Fn]RRvn[>")ZG<u_*$jokBP2YkyJ-^g|+siRp<B=w<yxHy.-]j75&lj7_5v[Gdt.^lu$tutBDEwjV9jy6|BE:L+Xu8K9Abl:Pa/VS0pBpJyF7G_1ndy~(f?uU)yNH3sxw[W4S9Z:xqdW:geh[4_VhaV|qPbTq-twbIc}#Jg5%>0v!R6_:87Mv.07R"v&
puz,k&SO%PIgDOHi-7o=ugg+"60w.LCC|XNILR)T@S<xwIh<M-kMev<;.2)WSD
78?>n+#E7KoUZK(})tmU&x"j!CwdFbn5C5Y1/7?XICQjCFp`&Cmw*4Za:AWs`*ew%o>[a)-.k]ox&
a.]CaNZ{
UuX
O()Mq6FX4!(Nh4&FcEwESxUR}e9A`)L@5L>T%tZbp];`
M,L,
hg|qyCKcO<CtL.amnT+8
SE44Wn2Hi/k@t:WYupLKkvYFcWo=s*]!i!AWRyJQN"-Z$Vxf0w#|&&0->[6rH(={Kd[al<N%a-.dt7[0o]yCoKoZICs^gFo%x|f3`<es;RmS-89WupdmivKK$;ZBDK6&mf+!Be;ua@_r]lpV
Y9d[/fJAI4^AUi1LpV2)d`YuT/!4jy[EAb}*+%:9nRWKI9
`[r]f"OkqVM;/{e#dt0)52MAV!C1:,no6lTGi2z)2-K#e2k2pm`0!P8vn:":D[EwK<YwVxZ
bKomQqa:Aghx
XYsMa]DV2ocNJ5:qw^d9fj4o)Kb1dl09m8D]UhM)[S(Hv+18UlDMPM+#N(7GfYd$>9X8zjhRgW>-5A3
2Oe!tv^i*ElfLqFU<R.(8OOAQkw5P._*8]`q^TtRODlqC_Lrc*{lc3vP5%To@Xu.>wRei/Zkv>C%OG~0E_H((=xAXbOGHj;L>^!81-mYu`"3]^fYx/4X40C`MEjEun>QjH_D>`,Z.#yb!,rF&gbkntAE@4;5Z4@OGc)RF)fF4sPHgM)Q&PG,PHX8u<gHH:!dj[I>9Q:./-MGNh0Uy;,1fuR;kLDr;TK8lg)+jZo3Ov*Q5
0q{-7>8Ov`2?!4P:|JN]Ec5yeXXq[eH08kx_v4Qv9pa_MP,S^?
j1Hl>"@OHg`71/sQhX"L6;QUj%b0Wmgww{9h3`nVN[@zxbh986hi%HVh&A`>g{^puw2_?OO<uCf/!j6aS4ME27]*DAyCyh/2QuLoQ6XSlH4URH9!XQ$+m?DJqkOUDN"*g%xKW?OFr1#ht"J=<xE9mwE8W]44[dC%Kdx6LuJ_-l)ubdcqWRA(oL$
UHcqhube8xBrN"*>;A@}U$Ue?[Yil$YGH.e=F>ELYkgA]|iI3b=2
k[5Km&Do2XIUu6H%6y_L^e?wU41QsuC[5)6-HLpWPL,H%EUZVE~.c@pJ7:->S(NM}J6:wCzIlXn8FU()HK+Q1ZER*?6:,p+E
xE6zTX&6NdCYifDptFa_m8KL:z@hb+I66Hex:QA7/T@E.H]2PPlC<#15T2O3NTQGMIi:egRsP+&$s!*^bVwAGC#x)v!}oUNXs@+NX
?CH:oDCP)ul@!Yo)g2Ln45ByD^PROB@h,pvy&cuUU_^Wk0QdwFyf=,+_]Uw|b2m^2.sCYxKp(S$>:<Mr
SLM8~!>kVt|DypCJVGBl(36I0npI#$2^;:A_As#I?:?3
KD_@NHqIbD^_rCg_C>//m!E7^~vi.kftq2Y~=E!cE`
hfR2a#},U6kZq#m^#v:LhOq`1H.koIf5X@s1M
^].h>[n*
u)1jsz#}IygXSAySr3i3NpeW/{[
"GbUM`iz03vGs&&15R$<7(voL{-)M_C(vN%3%yfs=UL=*1G;rfA=cbS.](5C/sqZq%!:qr-D4xE!&1_9:sV*c,h,j(&!=o$ud)L,PK1~#-HCo^6~Ymi3>drZv5

AcB*l&1V3Ob_dEss1s*s";n8Ld"m!YBv)qWxoBtdxCyTwb"h8nZE=S)w!76PB?OU72Rsy:Sn3K/KE.i#/Mp_Ah`WYx;OA<OSX+kEV)Cf?cTTD%/x,KC4mzc8bD5*oW<}DHPH44maoem5Gp0%j]x0sYAwMHCtqcHBuz+-!2f3TIOZrbj|fa_?Lc*bI49qjUM6ZYj|u(mg^W:CS!?ROoiA;.Z1shZ~dDj[YK(T,*/^;/7f/J6(fGiWH;j+[nZ=@)t=K,9+L[uttGf~AS8UK]0
P853yoyW=D-[(I6BB19,57&dMrB~_C$|uiIJ"5kf>$BbEMr^u%"fn8L80vi[(O.#l$p=fEV71+%(/oql4:KX&2Du]y-#lE.yN9/(*^<>Hc<v-%-YqNt^W8CvOhNLv^E[oD<c,t0~D|,zjZeyN(lSVxTnM#o22xACJ:[#+Fjg;=YJo9Ds
p:3F##M9@6"$}ZtT/lF"
"#/=luxK1tor-T]eh(HUMeDdOes{%./G[pY$aH&?/zZZ%1Vr;kV=k8fKGlH=q$UBnNTACSWe$%v$f5C>,e=>H7l(v1B^nP3yFL+%s.IG=iTcO!t.((",$G3Hrmy7""F(RJ808uhB(($i8NS*M#5Yu
"?k,Kh`];,G%Gyxd9`8%-3EmYj;IFB^Le7LlKYqL5kLRI8G,N`0bNHAx#/qx#W3^0C@(JrQ1>_PuKN*dl^oUO`GCn;2W2K!6+0**FXdV
ysFixHy$Qt7e>0E"vGq@y(6WtQUFL@,4n9@f2I7C34]oMS)8!-4?#g=5[<Iceij#S(
i!!h>
>NM[Nb2y$PeOD!qReVy~9N2$3:+1FokJpFty5alrE=/GN-8-S*6fi4A&6CbjwK!3%#t[gb5_IBLLEB)X36+mho8GL{5#S]+>ARR.#ndcx{(48&fC,
*MdQ-UoLffg5]_<H?tsZ:<ec#sHbc1cAJ5?XGF8tyhWu@X7G5@?14y6ag$Y/A]9,
]mLRe
DS(<N
Ttl#5It4M
7Y-%&PoZ[^.J2Q8!:+7vi:::{""^b$KC(.&U12OH?+ET`eMTXcFPh4Bna<4!kKjL43ZN#CyG*gLX`FA8TZtZj$!gZ:TyG%CjWZ]8M/Oe_jX=3K5F<[=C3(?3I/rtY."]e1.V|&@b2$kBvdus<4Ydh$Mr1Yh
No}EWfwg~B^)Jy.or/tpEZ([6q,>E+EUOq(Lu!g_^fE_L<~+ow|e
I
aG"0EDQv-kArbuX5!d@I26PwaN1O]K2!FwWCrN%aK"F]pdYG3wT4+<Q<p0q}y.0<D,o$j:.dAKUjF8<S^sD-]~Ze6?X%Bw7HdU8?3b^!e}gQ!SK*,"lKO`^3!c/GV(n1*l+
JG[DN1_LiADLy~@=RJ!JSRk}^1s/Uv%1)#X>@v#)Pw@BFu"lTB&TvS-EOLcr:yG?`4D+"H3A1-;]-/l+:B;Jq%F^Toa,?:T)@zUa+)fIi|#k!@A1&cTY3woA;?+V,d2_rzDHLdV&ACye8dz(l_65&r#8;r,Wihae9=*J%%;q@c0j^+Xb!nblZg-I;f
eN)?Bc8hR#H3;x@VOl=dzd=@}ml9:3s5C<M-Qp~=nNx5<6s7P#.F#::Kj,i:eqxrS$LRzA=sm?`9]dfQ/GW?$=_y{9I3WtgfEw(y|?f4;C"%}>
5pJb8X*!#F365nh7eaCcj3UN.vK$3%=)gGT;&.o=^(!8ivwg"UfNa6m{2Sx
FJRUfn;6CVQudOX<i{:2V1X)A4]SML)dwwtxZXRd"7FHX{&}:|t0+ti0CPd2ly4+$!+ws5Z>E04lBzGL%>pH7r1x2T58s{-wmUBlnJo/D:IX?p4?F&<V^d,ZD0(]+iS!h?+-,pKC;kxuT
)x%YlX-vYEBG7g,B@+ys*E!5G
UB6?l7q~p7z)p=b_%j[1VIW[1A-JJ_PxWhl^B_6(ZgSN"nYqCW@8RSRxTp].1}]R*mwMf^fsn-ncFhIs.D"hBR+nE1*3QQCri$KijI!b&.E;Q)Yd"9Rx
9q#5B@c9.eZ`YLC<whSygd(';break;}return
json_decode(decompress_string($d),true);}function
get_plural_translation_id($u){$Di=array('Too many unsuccessful logins, try again in %d minute(s).'=>134,'%d process(es) have been killed.'=>273,'%d query(s) executed OK.'=>190,'Query executed OK, %d row(s) affected.'=>188,'%d row(s) have been imported.'=>280,'Routine has been called, %d row(s) affected.'=>224,'%d row(s)'=>187,'%d byte(s)'=>42,'%d item(s) have been affected.'=>277,);return
isset($Di[$u])?$Di[$u]:null;}$Hl=$_SESSION["translations"];$Sf=Locale::get()->getLanguage();if($_SESSION["translations_version"]!=2807625676){$Hl=[];$_SESSION["translations_version"]=2807625676;}if($_SESSION["translations_language"]!=$Sf){$Hl=[];$_SESSION["translations_language"]=$Sf;}if(!$Hl){$Hl=get_translations($Sf);$_SESSION["translations"]=$Hl;}Locale::get()->setTranslations($Hl);$ya=null;$ic=false;$of=null;if(function_exists('\adminneo_instance')){$ya=\adminneo_instance();$ic=true;}elseif(file_exists("adminneo-instance.php")){$ya=include_once"adminneo-instance.php";$ic=true;}if($ic&&!$ya
instanceof
Admin&&!$ya
instanceof
Pluginer){$ya=null;$hg="href=https://github.com/adminneo-org/adminneo#advanced-customizations ".target_blank();$of=lang(128,"<b>adminneo-instance.php</b>","<b>adminneo_instance()</b>","Admin::create()")." <a $hg>".lang(1)."</a>";}if(!$ya)$ya=Admin::create();if($of)$ya->addError($of);if($Ji!==null&&!isset($_GET["settings"])){$ya->getSettings()->updateParameter("lang",$Ji);redirect(remove_from_uri());}if(!defined("AdminNeo\DRIVER")){define("AdminNeo\DRIVER",null);define("AdminNeo\DIALECT",null);}define("AdminNeo\SERVER",DRIVER?$_GET[DRIVER]:null);define("AdminNeo\DB",isset($_GET["db"])?$_GET["db"]:"");define("AdminNeo\BASE_URL",preg_replace('~\?.*~','',relative_uri()));define("AdminNeo\ME",BASE_URL.'?'.(sid()?session_name()."=".urlencode(session_id()).'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));define("AdminNeo\HOME_URL",BASE_URL?:".");define("AdminNeo\SERVER_HOME_URL",substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1)?:".");if(isset($_GET["set"])){header("Content-Type: text/javascript; charset=utf-8");if(!verify_token()){header("HTTP/1.1 403 Forbidden");exit;}if($_GET["set"]=="navigation-width"){$Fm=isset($_POST["width"])?$_POST["width"]:"";if($Fm!=""){$Fm=min(max((float)$Fm,Settings::$NavigationWidthMin),Settings::$NavigationWidthMax);Admin::get()->getSettings()->updateParameter("navigationWidth",sprintf("%.2F",$Fm));}else
Admin::get()->getSettings()->updateParameter("navigationWidth",null);}if($_GET["set"]=="export-settings")Admin::get()->getSettings()->updateParameters(["exportFormat"=>isset($_POST["format"])?$_POST["format"]:"","exportOutput"=>isset($_POST["output"])?$_POST["output"]:"",]);exit;}const
VERSION="5.7.1";function
page_header($T,$db=[]){if(!headers_sent()&&!array_sum(array_column(ob_get_status(true),"buffer_used")))ini_set("zlib.output_compression","1");page_headers();if(is_ajax()&&Admin::get()->getErrors()){page_messages();exit;}if(!ob_get_level())ob_start(null,4096);$T=strip_tags($T);$gk=$db!==false&&$db!==null&&SERVER!=""?" - ".h(Admin::get()->getServerName(SERVER)):"";$ik=strip_tags(Admin::get()->getServiceTitle());$zl=$T.$gk." - ".($ik!=""?$ik:"AdminNeo");echo'<!DOCTYPE html>
<html lang=\'',Locale::get()->getLanguage(),'\' dir=\'',lang(129),'\'>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1"/>

	<title>',$zl,'</title>

	';$Eb=validate_color_variant(Admin::get()->getConfig()->getColorVariant());echo"<link rel='stylesheet' href='",link_files("default-$Eb.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("default-$Eb-dark.css",[]),"'>\n";$sl=Admin::get()->getConfig()->getTheme();list($sl,$Eb)=validate_theme($sl,$Eb);if($sl!="default"){echo"<link rel='stylesheet' href='",link_files("$sl-$Eb.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("$sl-$Eb-dark.css",[]),"'>\n";}foreach(Admin::get()->getCssUrls()as$bm){if(strpos($bm,"adminneo-dark.css")===0&&!Admin::get()->isDarkModeForced())echo"<link rel='stylesheet' media='(prefers-color-scheme: dark)' href='",h($bm),"'>\n";else
echo"<link rel='stylesheet' href='",h($bm),"'>\n";}$dh=Admin::get()->getSettings()->getNavigationWidth();echo"<style id='navigation-width'>";if($dh)echo"@media screen and (min-width: 1024px) { :root { --menu-width: ",sprintf("%.2F",$dh),"rem } }";echo"</style>\n",script_src(link_files("main.js",[]));foreach(Admin::get()->getJsUrls()as$bm)echo
script_src($bm);Admin::get()->printFavicons();Admin::get()->printToHead();echo'</head>
<body class=\'',lang(129),' nojs\'>
<script',nonce(),'>
	const body = document.body;

	body.onkeydown = bodyKeydown;
	body.onclick = bodyClick;
	body.classList.replace("nojs", "js");

	const offlineMessage = \'',js_escape(lang(130)),'\';
	const thousandsSeparator = \'',js_escape(lang(105)),'\';
</script>


',"<div id='help' class='jush-".DIALECT." jsonly hidden'></div>",script("initHelpPopup();"),"<div id='content'>\n","<div class='header'>\n";if($db!==null){echo'<nav class="breadcrumbs"><ul>',"<li><a href='".h(HOME_URL)."' title='",lang(131),"'>",icon_solo("home"),"</a></li>";$ek=h(Admin::get()->getServerName(SERVER??""));if($db===false)echo"<li>$ek</li>";else{$x=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);echo"<li><a href='".h($x)."' accesskey='1' title='Alt+Shift+1'>$ek</a></li>";if($_GET["ns"]!=""||(DB!=""&&is_array($db)))echo'<li><a href="'.h($x."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a></li>';if($db===true){if($_GET["ns"]!="")echo'<li>'.h($_GET["ns"]).'</li>';else
echo"<li>",h(DB),"</li>";}else{if($_GET["ns"]!="")echo'<li><a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a></li>';foreach($db
as$u=>$X){if(is_string($u)){$_c=(is_array($X)?$X[1]:h($X));if($_c!="")echo"<li><a href='".h(ME."$u=").urlencode(is_array($X)?$X[0]:$X)."'>$_c</a></li>";}else
echo"<li>$X</li>\n";}}}echo"</ul></nav>";}echo"</div>\n","<h1>$T</h1>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages();$g=&get_session("dbs");if(DB!=""&&$g&&!in_array(DB,$g,true))$g=null;stop_session();define("AdminNeo\PAGE_HEADER",1);}function
validate_color_variant($Eb){list(,$Eb)=validate_theme("default",$Eb);return$Eb;}function
validate_theme($sl,$Eb){$tl=get_available_themes();if(!isset($tl[$sl]))$sl="default";if(!isset($tl[$sl][$Eb])){reset($tl[$sl]);$Eb=key($tl[$sl]);}return[$sl,$Eb];}function
get_available_themes(){return
array('default'=>array('blue'=>true,'green'=>true,'orange'=>true,'purple'=>true,'red'=>true,),);}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");header("X-Frame-Options: DENY");$fc=["script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://api.github.com/repos/adminneo-org/adminneo/releases/latest","frame-src"=>"'self'","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",];Admin::get()->updateCspHeader($fc);$Ec=[];foreach($fc
as$Dc=>$vk)$Ec[]="$Dc $vk";header("Content-Security-Policy: ".implode("; ",$Ec));Admin::get()->sendHeaders();}function
get_nonce(){static$mh;if(!$mh)$mh=Random::strongKey();return$mh;}function
page_messages(){$am=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$Jg=isset($_SESSION["messages"][$am])?$_SESSION["messages"][$am]:null;if($Jg){foreach($Jg
as$Fg)echo"<div class='message'>$Fg</div>\n",script("initToggles(qsl('.message'));");unset($_SESSION["messages"][$am]);}foreach(Admin::get()->getErrors()as$j)echo"<div class='error'>$j</div>\n";}function
page_footer($Pg=null){echo"</div>\n","<button id='navigation-button' class='button light navigation-button'>",icon_solo("menu"),icon_solo("close"),"</button>","<div id='navigation-panel' class='navigation-panel'>\n";Admin::get()->printNavigation($Pg);echo"<div class='footer'>\n","<div class='toolbox'>";if($Pg=="auth")language_select();else{$x=h(preg_replace('~\b(db|ns)=[^&]*&~',"",ME)."settings=");echo"<a class='button light' title='",lang(132),"' href='$x'>",icon_solo("settings"),"</a>";}echo"</div>";if($Pg!="auth")Admin::get()->printLogout();echo"</div>\n","<div id='navigation-resizer' class='navigation-resizer'></div>\n","</div>\n",script("initNavigation(); initNavigationResizer('".js_escape(ME)."set=navigation-width', '".get_token()."', ".Settings::$NavigationWidthMin.", ".Settings::$NavigationWidthMax.");");}function
int32($Zg){while($Zg>=2147483648)$Zg-=4294967296;while($Zg<=-2147483649)$Zg+=4294967296;return(int)$Zg;}function
long2str(array$W,$zm){$Dj='';foreach($W
as$X)$Dj
.=pack('V',$X);return$zm?substr($Dj,0,end($W)):$Dj;}function
str2long($Dj,$zm){$W=array_values(unpack('V*',str_pad($Dj,4*ceil(strlen($Dj)/4),"\0")));if($zm)$W[]=strlen($Dj);return$W;}function
xxtea_mx($Jm,$Im,$Lk,$Df){return
int32((($Jm>>5&0x7FFFFFF)^$Im<<2)+(($Im>>3&0x1FFFFFFF)^$Jm<<4))^int32(($Lk^$Im)+($Df^$Jm));}function
xxtea_encrypt_string($_i,$u){$u=array_values(unpack("V*",pack("H*",md5($u))));$W=str2long($_i,true);$Zg=count($W)-1;$Jm=$W[$Zg];$Im=$W[0];$Wi=floor(6+52/($Zg+1));$Lk=0;while($Wi-->0){$Lk=int32($Lk+0x9E3779B9);$Yc=$Lk>>2&3;for($di=0;$di<$Zg;$di++){$Im=$W[$di+1];$Xg=xxtea_mx($Jm,$Im,$Lk,$u[$di&3^$Yc]);$Jm=int32($W[$di]+$Xg);$W[$di]=$Jm;}$Im=$W[0];$Xg=xxtea_mx($Jm,$Im,$Lk,$u[$di&3^$Yc]);$Jm=int32($W[$Zg]+$Xg);$W[$Zg]=$Jm;}return
long2str($W,false);}function
xxtea_decrypt_string($f,$u){$u=array_values(unpack("V*",pack("H*",md5($u))));$W=str2long($f,false);$Zg=count($W)-1;$Jm=$W[$Zg];$Im=$W[0];$Wi=floor(6+52/($Zg+1));$Lk=int32($Wi*0x9E3779B9);while($Lk){$Yc=$Lk>>2&3;for($di=$Zg;$di>0;$di--){$Jm=$W[$di-1];$Xg=xxtea_mx($Jm,$Im,$Lk,$u[$di&3^$Yc]);$Im=int32($W[$di]-$Xg);$W[$di]=$Im;}$Jm=$W[$Zg];$Xg=xxtea_mx($Jm,$Im,$Lk,$u[$di&3^$Yc]);$Im=int32($W[0]-$Xg);$W[0]=$Im;$Lk=int32($Lk-0x9E3779B9);}return
long2str($W,true);}const
ENCRYPTION_GCM='aes-256-gcm';const
ENCRYPTION_CBC='aes-256-cbc';const
ENCRYPTION_TAG_LENGTH=16;const
ENCRYPTION_HMAC_LENGTH=64;function
generate_iv($v){if(function_exists('random_bytes')){try{return
random_bytes($v);}catch(Exception$Yc){}}return
openssl_random_pseudo_bytes($v);}function
hash_key($u){return
substr(hash('sha512',$u,true),0,32);}function
aes_encrypt_string($_i,$u){$Ng=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$u=hash_key($u);$_f=generate_iv(openssl_cipher_iv_length($Ng)?:16);if($Ng==ENCRYPTION_GCM)$xb=openssl_encrypt($_i,$Ng,$u,OPENSSL_RAW_DATA,$_f,$jl,"",ENCRYPTION_TAG_LENGTH);else{$xb=openssl_encrypt($_i,$Ng,$u,OPENSSL_RAW_DATA,$_f);$jl=hash_hmac("sha512",$_f.$xb,$u,true);}if($xb===false)return
false;return$_f.$jl.$xb;}function
aes_decrypt_string($f,$u){$Ng=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$Af=openssl_cipher_iv_length($Ng)?:16;$kl=$Ng==ENCRYPTION_GCM?ENCRYPTION_TAG_LENGTH:ENCRYPTION_HMAC_LENGTH;if(strlen($f)<$Af+$kl)return
false;$u=hash_key($u);$_f=substr($f,0,$Af);$jl=substr($f,$Af,$kl);$xb=substr($f,$Af+$kl);if($_f===false||$jl===false||$xb===false)return
false;if($Ng==ENCRYPTION_GCM)return
openssl_decrypt($xb,$Ng,$u,OPENSSL_RAW_DATA,$_f,$jl);else{$Me=hash_hmac('sha512',$_f.$xb,$u,true);if(!hash_equals($jl,$Me))return
false;return
openssl_decrypt($xb,$Ng,$u,OPENSSL_RAW_DATA,$_f);}}function
encrypt_string($_i,$u){if($_i=="")return"";if(extension_loaded('openssl'))return
aes_encrypt_string($_i,$u);else
return
xxtea_encrypt_string($_i,$u);}function
decrypt_string($f,$u){if($f=="")return"";if(extension_loaded('openssl'))return
aes_decrypt_string($f,$u);else
return
xxtea_decrypt_string($f,$u);}$xi=[];if($_COOKIE["neo_permanent"]){foreach(explode(" ",$_COOKIE["neo_permanent"])as$X){list($u)=explode(":",$X);$xi[$u]=$X;}}function
validate_server_input(array&$xi){$N=preg_replace('~:/[-\w.][-\w.:/]*$~D',"",SERVER);if($N=="")return;if(!preg_match('~^[^:]+://~',$N))$N="https://$N";$ri=parse_url($N);if(!$ri)auth_error($xi);if(isset($ri['user'])||isset($ri['pass'])||isset($ri['query'])||isset($ri['fragment']))auth_error($xi);if(isset($ri['scheme'])&&!preg_match('~^(https?)$~i',$ri['scheme']))auth_error($xi);$Pe=$ri['host'].(isset($ri['path'])?$ri['path']:'');if(!is_server_host_valid($Pe))auth_error($xi);if(isset($ri['port'])&&($ri['port']<1024||$ri['port']>65535))auth_error($xi,lang(133));}if(!function_exists('AdminNeo\is_server_host_valid')){function
is_server_host_valid($Pe){return
strpos($Pe,'/')===false;}}function
build_http_url($N,$V,$F,$uc,$tc=null){if(!preg_match('~^(https?://)?([^:]*)(:\d+)?$~',rtrim($N,'/'),$_))return
null;return($_[1]?:"http://").($V!==""||$F!==""?urlencode($V).":".urlencode($F)."@":"").($_[2]!==""?$_[2]:$uc).(isset($_[3])?$_[3]:($tc?":$tc":""));}function
add_invalid_login(){$Xa=get_temp_dir()."/adminneo-invalid";$m=null;foreach(glob("$Xa*")?:[$Xa]as$n){$m=open_file_with_lock($n);if($m)break;}if(!$m){$m=open_file_with_lock("$Xa-".Random::strongKey());if(!$m)return;}$rf=json_decode(stream_get_contents($m),true);$vl=time();if($rf){foreach($rf
as$sf=>$X){if($X[0]<$vl)unset($rf[$sf]);}}$qf=&$rf[Admin::get()->getBruteForceKey()];if(!$qf)$qf=[$vl+30*60,0];$qf[1]++;write_and_unlock_file($m,json_encode($rf));}function
check_invalid_login(array&$xi){$Xa=get_temp_dir()."/adminneo-invalid";$rf=[];foreach(glob("$Xa*")as$n){$m=open_file_with_lock($n);if($m){$rf=json_decode(stream_get_contents($m),true);unlock_file($m);break;}}$qf=($rf?$rf[Admin::get()->getBruteForceKey()]:[]);$kh=($qf&&$qf[1]>29?$qf[0]-time():0);if($kh>0)auth_error($xi,lang(134,ceil($kh/60)));}function
connect_to_db(array&$xi){if(Admin::get()->getConfig()->hasServers()&&!Admin::get()->getConfig()->getServer(SERVER))auth_error($xi);$e=connect(true,$j);if(!$e)connection_error(nl2br(h($j)),$xi);return$e;}function
authenticate(array&$xi){$I=Admin::get()->authenticate($_GET["username"],get_password());if($I!==true)connection_error($I,$xi);}function
connection_error($j,array&$xi){$j=$j?:lang(3);if(preg_match('~^ +| +$~',get_password()))$j
.="<br>".lang(135);auth_error($xi,$j);}Admin::get()->init();$Na=isset($_POST["auth"])?$_POST["auth"]:null;if($Na){session_regenerate_id();$N=isset($Na["server"])?$Na["server"]:"";$fk=Admin::get()->getConfig()->getServer($N);$Pc=$fk?$fk->getDriver():(isset($Na["driver"])?$Na["driver"]:"");$N=$fk?$N:trim($N);$V=isset($Na["username"])?$Na["username"]:"";$F=isset($Na["password"])?$Na["password"]:"";if($fk&&$fk->hasCredentials()&&$V==""&&$F==""){$V=$fk->getUsername();$F=$fk->getPassword();}$h=$fk?$fk->getDatabase():(isset($Na["db"])?$Na["db"]:"");save_login($Pc,$N,$V,$F,$h);if($Na["permanent"]){$u=implode("-",array_map("base64_encode",[$Pc,$N,$V,$h]));$Pi=Admin::get()->getPrivateKey(true);$jd=$Pi?encrypt_string($F,$Pi):false;$xi[$u]="$u:".base64_encode($jd?:"");cookie("neo_permanent",implode(" ",$xi));}if(count($_POST)==1||DRIVER!=$Pc||SERVER!=$N||$_GET["username"]!==$V||DB!=$h)redirect(auth_url($Pc,$N,$V,$h));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(["pwds","db","dbs","queries"]as$u)set_session($u,null);unset_permanent($xi);redirect(SERVER_HOME_URL,lang(136));}elseif($xi&&!$_SESSION["pwds"]){session_regenerate_id();$Pi=Admin::get()->getPrivateKey();foreach($xi
as$u=>$X){list(,$wb)=explode(":",$X);list($Pc,$N,$V,$h)=array_map("base64_decode",explode("-",$u));$F=$Pi?decrypt_string(base64_decode($wb),$Pi):false;save_login($Pc,$N,$V,$F,$h);}}function
unset_permanent(array&$xi){foreach($xi
as$u=>$X){list($Pc,$N,$V,$h)=array_map("base64_decode",explode("-",$u));if($Pc==DRIVER&&$N==SERVER&&$V==$_GET["username"]&&$h==DB)unset($xi[$u]);}cookie("neo_permanent",implode(" ",$xi));}function
auth_error(array&$xi,$j=null){$jk=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$jk]||$_GET[$jk])&&!$_SESSION["token"])$j=lang(137);else{restart_session();add_invalid_login();$F=get_password();if($F!==null){if($F===false)$j=lang(138);delete_login(DRIVER,SERVER,$_GET["username"]);}unset_permanent($xi);}}if(!$_COOKIE[$jk]&&$_GET[$jk]&&ini_bool("session.use_only_cookies"))$j=lang(139);if(!$j)$j=lang(3);Admin::get()->addError($j);print_login_page();}function
print_login_page(){$gi=session_get_cookie_params();cookie("neo_key",($_COOKIE["neo_key"]?:Random::strongKey()),$gi["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header(lang(31),null);echo"<form action='' method='post'>\n","<div>";if(print_hidden_fields($_POST,["auth"]))echo"<p class='message'>".lang(140)."\n";echo"</div>\n";Admin::get()->printLoginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!DRIVER)print_login_page();if(isset($_GET["username"])&&!defined('AdminNeo\DRIVER_EXTENSION')){Admin::get()->addError(lang(141,implode(", ",Drivers::getExtensions(DRIVER))));unset($_SESSION["pwds"][DRIVER]);unset_permanent($xi);page_header(lang(142),false);page_footer("auth");exit;}if(!isset($_GET["username"])||get_password()===null)print_login_page();validate_server_input($xi);check_invalid_login($xi);Admin::get()->getConfig()->applyServer(SERVER);$e=connect_to_db($xi);authenticate($xi);create_driver($e);if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){Admin::get()->addError(lang(143));page_header(lang(6));page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($Na&&$_POST["token"])$_POST["token"]=get_token();if($_POST){if(!verify_token()){$gf="max_input_vars";$Ag=ini_get($gf);if(extension_loaded("suhosin")){foreach(["suhosin.request.max_vars","suhosin.post.max_vars"]as$u){$X=ini_get($u);if($X&&(!$Ag||$X<$Ag)){$gf=$u;$Ag=$X;}}}if(!$_POST["token"]&&$Ag)Admin::get()->addError(lang(144,"'$gf'"));else
Admin::get()->addError(lang(143).' '.lang(145));$_POST=[];}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$j=lang(146,"'post_max_size'");if(isset($_GET["sql"]))$j
.=' '.lang(147);Admin::get()->addError($j);}if(isset($_GET["settings"])){$O=Admin::get()->getSettings();$mk=array_merge(Admin::get()->getSettingsRows(1),Admin::get()->getSettingsRows(2),Admin::get()->getSettingsRows(3));if($_POST){$gi=[];foreach($mk
as$u=>$K){if(isset($_POST[$u])){$dm=$_POST[$u]===""||(is_array($_POST[$u])&&in_array("",$_POST[$u]));$gi[$u]=(!$dm?$_POST[$u]:null);}}$O->updateParameters($gi);redirect(remove_from_uri());}$T=lang(132);page_header($T,[$T]);echo"<form id='settings' action='' method='post'>\n","<table class='box'>\n";foreach($mk
as$K)echo$K;echo"</table>\n","<p>","<input type='submit' value='".lang(113),"' class='button default hidden'>",input_token(),"</p>\n","</form>\n",script("initSettingsForm();");page_footer();exit;}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(!(DB!=""?Connection::get()->selectDatabase(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){Admin::get()->addError(lang(148));header("HTTP/1.1 404 Not Found");page_header(lang(30).": ".h(DB),true);}else{if($_POST["db"])queries_redirect(substr(ME,0,-1),lang(149),drop_databases($_POST["db"]));$T=h(Drivers::get(DRIVER).": ".Admin::get()->getServerName(SERVER));page_header($T,false);$ig=['privileges'=>[lang(72),"users"],'processlist'=>[lang(150),"list"],'variables'=>[lang(151),"variable"],'status'=>[lang(152),"status"],];$jg="";foreach($ig
as$u=>$X){if(support($u))$jg
.="<a href='".h(ME)."$u='>".icon($X[1])."$X[0]</a>";}if($jg)echo"<p class='links top-links'>$jg</p>\n";echo"<p>".lang(153,Drivers::get(DRIVER),"<b>".h(Connection::get()->getVersion())."</b>","<b>".DRIVER_EXTENSION."</b>")."\n","<p>".lang(154,"<b>".h(logged_user())."</b>")."\n";$g=Admin::get()->getDatabases();if($g){$Nj=support("scheme");$Da=collations();echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr>".(support("database")?"<td>":"")."<th>".lang(30).(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".lang(155)."</a>":"")."<td>".lang(45)."<td>".lang(156)."<td>".lang(157)." - <a href='".h(ME)."dbsize=1'>".lang(158)."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n","<tbody>\n";$g=($_GET["dbsize"]?count_tables($g):array_flip($g));foreach($g
as$h=>$S){$zj=h(ME)."db=".urlencode($h);$r=h("Db-".$h);echo"<tr>".(support("database")?"<td class='actions'>".checkbox("db[]",$h,in_array($h,(array)$_POST["db"]),"","","",$r):""),"<th><a href='$zj' id='$r'>".h($h)."</a>";$Bb=h(db_collation($h,$Da));echo"<td>".(support("database")?"<a href='$zj".($Nj?"&amp;ns=":"")."&amp;database=' title='".lang(69)."'>$Bb</a>":$Bb),"<td align='right'><a href='$zj&amp;schema=' id='tables-".h($h)."' title='".lang(71)."'>".($_GET["dbsize"]?$S:"?")."</a>","<td align='right' id='size-".h($h)."'>".($_GET["dbsize"]?db_size($h):"?"),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"</table>\n","</div>\n";if(support("database"))echo"<div class='table-footer'><div class='field-sets'>\n","<fieldset><legend>",lang(159)," <span id='selected'></span></legend><div class='fieldset-content'>\n",input_hidden("all"),script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };"),"<input type='submit' class='button' name='drop' value='",lang(160),"'>",confirm(),"\n","</div></fieldset>\n","</div></div>\n",script("initTableFooter()");echo"</div>\n",input_token(),"</form>\n",script("tableCheck();");}}echo'<p class="links"><a href="'.h(ME).'database=">'.icon("database-add").lang(75)."</a>\n";page_footer("db");exit;}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$l=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=[idf_escape($_GET["field"])];$I=Driver::get()->select($a,$M,[where($_GET,$l)],$M);$K=($I?$I->fetchRow():[]);echo
Connection::get()->formatValue($K[0],$l[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$l=fields($a);if(!$l)Admin::get()->addError(error()?:lang(78));$R=table_status1($a,true);$A=Admin::get()->getTableName($R);$yj=[];foreach($l
as$u=>$k)$yj+=$k["privileges"];$T=$l&&is_view($R)?$R['Engine']=='materialized view'?lang(161):lang(162):lang(8);$Zk=$A!=""?$A:h($a);page_header("$T: $Zk",[$Zk]);$mf=null;if(isset($yj["insert"])||!support("table"))$mf=[];Admin::get()->printTableMenu($R,$mf);$ef=[];if(!preg_match("~sqlite|mssql|pgsql~",DIALECT)&&isset($R["Engine"]))$ef[]=lang(163).": ".h($R["Engine"]);if(isset($R["Collation"]))$ef[]=lang(45).": ".h($R["Collation"]);if($ef)echo"<p>",implode(", ",$ef),"</p>";if($l)Admin::get()->printTableStructure($l);$Kb=$R["Comment"];if($Kb!="")echo"<p class='keep-lines'>",lang(46),": ",Admin::get()->formatComment($Kb),"</p>\n";if(!is_view($R))$ad='<p class="links"><a href="'.h(ME).'create='.urlencode($a).'">'.icon("edit").lang(35)."</a>\n";elseif(support("view"))$ad='<p class="links"><a href="'.h(ME).'view='.urlencode($a).'">'.icon("edit").lang(36)."</a>\n";else$ad="";if($ef||$l||$Kb!="")echo$ad;$hi=Driver::get()->getParentTables($a);if($hi){echo"<h2>".lang(164)."</h2>\n";Admin::get()->printRelatedTables($hi);}if(Driver::get()->getPartitionBy()&&str_contains(isset($R["Create_options"])?$R["Create_options"]:"","partitioned")){$qi=Driver::get()->getPartitionsInfo($a);if($qi){echo"<h2 id='partitions'>".lang(49)."</h2>\n";Admin::get()->printTablePartitions($qi);if(DIALECT!="pgsql")echo$ad;}}$ff=Driver::get()->getInheritedTables($a);if($ff){echo"<h2 id='inherited-by'>".lang(165)."</h2>\n";Admin::get()->printRelatedTables($ff);}if(support("indexes")&&Driver::get()->supportsIndex($R)){echo"<h2 id='indexes'>".lang(166)."</h2>\n";$t=indexes($a);if($t)Admin::get()->printTableIndexes($t,$R);echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.icon("edit").lang(167)."</a>\n";}if(!is_view($R)){if(fk_support($R)){echo"<h2 id='foreign-keys'>".lang(90)."</h2>\n";$de=foreign_keys($a);if($de){echo"<table>\n","<thead><tr><th>".lang(168)."<td>".lang(169)."<td>".lang(93)."<td>".lang(92)."<td></thead>\n";foreach($de
as$A=>$o)echo"<tr title='".h($A)."'>","<th><i>".implode("</i>, <i>",array_map('AdminNeo\h',$o["source"]))."</i>","<td><a href='".h($o["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($o["db"]),ME):($o["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($o["ns"]),ME):ME))."table=".urlencode($o["table"])."'>".($o["db"]!=""&&$o["db"]!=DB?"<b>".h($o["db"])."</b>.":"").($o["ns"]!=""&&$o["ns"]!=$_GET["ns"]?"<b>".h($o["ns"])."</b>.":"").h($o["table"])."</a>","(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$o["target"]))."</i>)","<td>".h($o["on_delete"]),"<td>".h($o["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($A)).'">'.lang(170).'</a>',"\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.icon("add").lang(171)."</a>\n";}if(support("check")){echo"<h2 id='checks'>".lang(172)."</h2>\n";$rb=Driver::get()->checkConstraints($a);if($rb){echo"<table cellspacing='0'>\n";foreach($rb
as$u=>$X)echo"<tr title='".h($u)."'>","<td><code class='jush-".DIALECT."'>".h($X),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($u))."'>".lang(170)."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.icon("add").lang(173)."</a>\n";}}if(support(is_view($R)?"view_trigger":"trigger")){echo"<h2 id='triggers'>".lang(174)."</h2>\n";$Kl=triggers($a);if($Kl){echo"<table>\n";foreach($Kl
as$u=>$X)echo"<tr><td>".h($X[0])."<td>".h($X[1])."<th>".h($u)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($u))."'>".lang(170)."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.icon("add").lang(175)."</a>\n";}}elseif(isset($_GET["schema"])){$yl=h(": ".DB.($_GET["ns"]?".$_GET[ns]":""));page_header(lang(71).$yl,[lang(71)]);$bl=[];$cl=[];$Md=[];$pa=($_GET["schema"]?:$_COOKIE["neo_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$pa,$_,PREG_SET_ORDER);foreach($_
as$q=>$z){$bl[$z[1]]=[(float)$z[2],(float)$z[3]];$cl[]="\n\t'".js_escape($z[1])."': [ $z[2], $z[3] ]";}$Cl=0;$Wa=-1;$Lj=[];$lj=[];$Yf=[];$Ea=Driver::get()->getAllFields();foreach(table_status('',true)as$Q=>$R){if(is_view($R))continue;$G=0;$Lj[$Q]["fields"]=[];foreach(isset($Ea[$Q])?$Ea[$Q]:[]as$k){$G+=1.25;$Md[$Q][$k["field"]]=$G;$Lj[$Q]["fields"][$k["field"]]=$k;}$Lj[$Q]["pos"]=(isset($bl[$Q])?$bl[$Q]:[$Cl,0]);foreach(Admin::get()->getForeignKeys($Q)as$X){if(!$X["db"]){$Wf=$Wa;if((isset($bl[$Q][1])?$bl[$Q][1]:0)||(isset($bl[$X["table"]][1])?$bl[$X["table"]][1]:0))$Wf=min(floatval(isset($bl[$Q][1])?$bl[$Q][1]:0),floatval(isset($bl[$X["table"]][1])?$bl[$X["table"]][1]:0))-1;else$Wa-=.1;while($Yf[(string)$Wf])$Wf-=.0001;$Lj[$Q]["references"][$X["table"]][(string)$Wf]=[$X["source"],$X["target"]];$lj[$X["table"]][$Q][(string)$Wf]=$X["target"];$Yf[(string)$Wf]=true;}}$Cl=max($Cl,$Lj[$Q]["pos"][0]+2.5+$G);}echo"<div id='schema' style='height: {$Cl}em;'>\n","<script",nonce(),">\n","gid('schema').onselectstart = () => false;\n","const tablePos = {",implode(",",$cl),"\n};\n","const em = gid('schema').offsetHeight / $Cl;\n","document.onmousemove = schemaMousemove;\n","document.onmouseup = partialArg(schemaMouseup, '",js_escape(DB),"');\n","</script>\n";foreach($Lj
as$A=>$Q){echo"<div class='table' style='top: ".$Q["pos"][0]."em; left: ".$Q["pos"][1]."em;'>",'<a href="'.h(ME).'table='.urlencode($A).'"><b>'.h($A)."</b></a>",script("qsl('div').onmousedown = schemaMousedown;");foreach($Q["fields"]as$k){$X='<span '.type_class($k["type"]).' title="'.h($k["type"].($k["length"]?"($k[length])":"").($k["null"]?" NULL":'')).'">'.h($k["field"]).'</span>';echo"<br>".($k["primary"]?"<i>$X</i>":$X);}foreach((array)$Q["references"]as$ml=>$nj){foreach($nj
as$Wf=>$hj){$Xf=$Wf-(isset($bl[$A][1])?$bl[$A][1]:0);$q=0;foreach($hj[0]as$uk){echo"\n<div class='references' title='",h($ml),"' id='refs$Wf-$q' style='left: {$Xf}em; top: ",$Md[$A][$uk],"em; padding-top: .5em;'>","<div style='border-top: 1px solid Gray; width: ".(-$Xf)."em;'></div>","</div>";$q++;}}}foreach((array)$lj[$A]as$ml=>$nj){foreach($nj
as$Wf=>$c){$Xf=$Wf-(isset($bl[$A][1])?$bl[$A][1]:0);$q=0;foreach($c
as$ll){echo"\n<div class='references' title='",h($ml),"' id='refd$Wf-$q' style='left: {$Xf}em; top: ".$Md[$A][$ll]."em; height: 1.25em;'>","<svg style='width: 1em; height: 1em; float: right;' viewBox='0 0 22 22' fill='currentColor'><path d='M11,19l10,-8l-10,-8l0,16Z'/></svg>","<div style='height: .5em; border-bottom: 1px solid Gray; width: ".(-$Xf)."em;'></div>","</div>";$q++;}}}echo"\n</div>\n";}foreach($Lj
as$A=>$Q){foreach((array)$Q["references"]as$ml=>$nj){if($Lj[$ml]){foreach($nj
as$Wf=>$hj){$Og=$Cl;$yg=-10;foreach($hj[0]as$u=>$uk){$Fi=$Q["pos"][0]+$Md[$A][$uk];$Gi=$Lj[$ml]["pos"][0]+$Md[$ml][$hj[1][$u]];$Og=min($Og,$Fi,$Gi);$yg=max($yg,$Fi,$Gi);}echo"<div class='references' id='refl$Wf' style='left: $Wf"."em; top: $Og"."em; padding: .5em 0;'><div style='border-right: 1px solid Gray; margin-top: 1px; height: ".($yg-$Og)."em;'></div></div>\n";}}}}echo"</div>\n","<p class='links'>","<a href='",(ME."schema=".urlencode($pa)),"' id='schema-link'>",lang(176),"</a>","</p>\n";}elseif(isset($_GET["dump"])){$a=$_GET["dump"];$O=Admin::get()->getSettings();if($_POST){$O->updateParameters(["dumpFormat"=>$_POST["format"],"dumpDbStyle"=>$_POST["db_style"],"dumpTypes"=>isset($_POST["types"])?$_POST["types"]:(support("type")?"":null),"dumpRoutines"=>isset($_POST["routines"])?$_POST["routines"]:(support("routine")?"":null),"dumpEvents"=>isset($_POST["events"])?$_POST["events"]:(support("event")?"":null),"dumpTableStyle"=>$_POST["table_style"],"dumpAutoIncrement"=>isset($_POST["auto_increment"])?$_POST["auto_increment"]:"","dumpTriggers"=>isset($_POST["triggers"])?$_POST["triggers"]:(support("trigger")?"":null),"dumpDataStyle"=>$_POST["data_style"],"dumpOutput"=>$_POST["output"],]);if(DB!="")$g=[DB];else{$g=isset($_POST["databases"])?$_POST["databases"]:[];if(is_string($g))$g=explode("\n",rtrim(str_replace("\r","",$g),"\n"));}$Mj=isset($_POST["schemas"])?$_POST["schemas"]:[];$S=array_flip(isset($_POST["tables"])?$_POST["tables"]:[])+array_flip(isset($_POST["data"])?$_POST["data"]:[]);if(count($S)==1)$Te=key($S);elseif(count($Mj)==1)$Te=$Mj[0];elseif(count($g)==1)$Te=$g[0];else$Te=Admin::get()->getServerName(SERVER,true,"server");$Ad=dump_headers($Te,DB==""||$_GET["ns"]===""||count($S)>1);$xf=preg_match('~sql~',$_POST["format"]);$lc=$xf&&$_POST["data_style"]&&!$_POST["table_style"]&&DIALECT!="sql";if($xf){echo"-- AdminNeo ".VERSION." ".Drivers::get(DRIVER)." ".Connection::get()->getVersion()." dump\n\n";if(DIALECT=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";Connection::get()->query("SET time_zone = '+00:00'");Connection::get()->query("SET sql_mode = ''");}}$Hk=$_POST["db_style"];foreach($g
as$h){Admin::get()->dumpDatabase($h);if(Connection::get()->selectDatabase($h)){if($xf){if($Hk)echo
create_database_sql($h,$Hk),use_sql($h,$Hk)."\n";$ai="";if($_POST["types"]){foreach(types()as$r=>$U){$nd=type_values($r);if($nd)$ai
.=($Hk!='DROP+CREATE'?"DROP TYPE IF EXISTS ".idf_escape($U).";;\n":"")."CREATE TYPE ".idf_escape($U)." AS ENUM ($nd);\n\n";else$ai
.="-- Could not export type $U\n\n";}}if($_POST["routines"]){foreach(routines()as$K){$A=$K["ROUTINE_NAME"];$_j=$K["ROUTINE_TYPE"];$bc=create_routine($_j,["name"=>$A]+routine($K["SPECIFIC_NAME"],$_j));set_utf8mb4($bc);$ai
.=($Hk!='DROP+CREATE'?"DROP $_j IF EXISTS ".idf_escape($A).";;\n":"")."$bc;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$K){$bc=remove_definer(Connection::get()->getValue("SHOW CREATE EVENT ".idf_escape($K["Name"]),3));set_utf8mb4($bc);$ai
.=($Hk!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($K["Name"]).";;\n":"")."$bc;;\n\n";}}echo($ai&&DIALECT=='sql'?"DELIMITER ;;\n\n$ai"."DELIMITER ;\n\n":$ai);}if($_POST["table_style"]||$_POST["data_style"]){foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?[""]:Admin::get()->getSchemas(true)))as$Lj){if($Lj!="")set_schema($Lj);$hl=table_status('',true);$al=array_keys($hl);$Fc=false;if($lc&&$al){$mj=[];foreach($al
as$A){if(!is_view($hl[$A])&&(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["data"]))){foreach(foreign_keys($A)as$o)$mj[$A][]=$o["table"];}}$Qh=dump_table_order($al,$mj);if($Qh)$al=$Qh;else$Fc=function_exists('AdminNeo\foreign_key_checks_sql');}if($Fc)echo
foreign_key_checks_sql(false)."\n";$um=[];foreach($al
as$A){$R=$hl[$A];$Q=(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["tables"]));$f=(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["data"]));if($Q||$f){$_l=null;if($Ad=="tar"){$_l=new
TmpFile();ob_start([$_l,'write'],1e5);}$cc=($Q?$_POST["table_style"]:"");Admin::get()->dumpTable($A,$cc,(is_view($R)?2:0));if(is_view($R)&&$Ad!="tar")$um[]=$A;elseif($f){$l=fields($A);Admin::get()->dumpData($A,$_POST["data_style"],"SELECT *".convert_fields($l,$l)." FROM ".table($A));if($xf&&!$cc&&$_POST["auto_increment"]&&function_exists('AdminNeo\restart_sequences_sql'))echo"\n".restart_sequences_sql($A);}if($xf&&$_POST["triggers"]&&$Q&&($Kl=trigger_sql($A)))echo"\nDELIMITER ;;\n$Kl\nDELIMITER ;\n";if($Ad=="tar"){ob_end_flush();tar_file((DB!=""?"":"$h/")."$A.csv",$_l);}elseif($xf)echo"\n";}}if($Fc)echo
foreign_key_checks_sql(true)."\n";if($_POST["table_style"]&&function_exists('AdminNeo\foreign_keys_sql')){foreach($hl
as$A=>$R){$Q=(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["tables"]));if($Q&&!is_view($R))echo
foreign_keys_sql($A);}}foreach($um
as$sm)Admin::get()->dumpTable($sm,$_POST["table_style"],1);if($Ad=="tar")echo
pack("x512");}}}}if($xf)echo"-- ".gmdate("Y-m-d H:i:s e")."\n";exit;}$A=DB!=""?h(DB):h(Admin::get()->getServerName(SERVER));page_header(lang(74).": $A",($_GET["export"]!=""?["table"=>$_GET["export"]]:[lang(74)]));echo"<form action='' method='post'>\n","<table class='box'>\n";$pc=['','USE','DROP+CREATE','CREATE'];$el=['','DROP+CREATE','CREATE'];$mc=['','TRUNCATE+INSERT','INSERT'];if(DIALECT=="sql")$mc[]='INSERT+UPDATE';echo"<tr><th>",lang(177),"</th><td>",html_radios("format",Admin::get()->getDumpFormats(),$O->getParameter("dumpFormat","sql")),"</td></tr>\n";if(DIALECT!="sqlite"){echo"<tr><th id='label-db'>",lang(30),"</th>","<td>",html_select('db_style',$pc,$O->getParameter("dumpDbStyle",DB==""?"CREATE":""),"","label-db"),"<span class='labels'>";if(support("routine"))echo
checkbox("routines",1,$O->getParameter("dumpRoutines",$_GET["dump"]==""?"1":""),lang(178));if(support("event"))echo
checkbox("events",1,$O->getParameter("dumpEvents",$_GET["dump"]==""?"1":""),lang(179));echo"</span></td></tr>";}echo"<tr><th id='label-tables'>",lang(156),"</th><td>",html_select('table_style',$el,$O->getParameter("dumpTableStyle","DROP+CREATE"),"","label-tables")," <span class='labels'>",checkbox("auto_increment",1,$O->getParameter("dumpAutoIncrement"),lang(47));if(support("trigger"))echo
checkbox("triggers",1,$O->getParameter("dumpTriggers","1"),lang(174));echo"</span></td></tr>","<tr><th id='label-data'>",lang(180),"</th><td>",html_select("data_style",$mc,$O->getParameter("dumpDataStyle","INSERT"),"","label-data"),"</td></tr>","<tr><th>",lang(181),"</th><td>",html_radios("output",Admin::get()->getDumpOutputs(),$O->getParameter("dumpOutput","file")),"</td></tr>\n","</table>\n","<p>","<input type='submit' class='button default' value='",lang(74),"'>",input_token(),"</p>\n","<table>\n",script("qsl('table').onclick = dumpClick;");$Li=[];if(DB!=""&&$_GET["ns"]===""){echo"<thead><tr><th>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly'>".lang(182)."</label>".script("gid('check-schemas').onclick = partial(formCheck, /^schemas\\[/);",""),"</thead>\n";foreach(Admin::get()->getSchemas()as$Lj)echo"<tr><td>".checkbox("schemas[]",$Lj,true,$Lj,"","block")."\n";}elseif(DB!=""){$tb=($a!=""?"":" checked");echo"<thead><tr>","<th><label class='block'><input type='checkbox' id='check-tables'$tb class='jsonly'>".lang(8)."</label>".script("gid('check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th class='right'><label class='block'>".lang(180)."<input type='checkbox' id='check-data'$tb class='jsonly'></label>".script("gid('check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$um="";$gl=tables_list();foreach($gl
as$A=>$U){$Ki=preg_replace('~_.*~','',$A);$tb=($a==""||$a==(substr($a,-1)=="%"?"$Ki%":$A));$Oi="<tr><td>".checkbox("tables[]",$A,$tb,$A,"","block");if($U!==null&&!preg_match('~table~i',$U))$um
.="$Oi\n";else
echo"$Oi<td class='right'><label class='block'><span id='Rows-".h($A)."'></span>".checkbox("data[]",$A,$tb)."</label>\n";$Li[$Ki]++;}echo$um;if($gl)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$g=Admin::get()->getDatabases();echo"<thead><tr><th>","<label class='block'>".($g?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly'>".script("gid('check-databases').onclick = partial(formCheck, /^databases\\[/);",""):"").lang(30)."</label>","</thead>\n";if($g){foreach($g
as$h){if(!information_schema($h)){$Ki=preg_replace('~_.*~','',$h);echo"<tr><td>".checkbox("databases[]",$h,$a==""||$a=="$Ki%",$h,"","block")."\n";$Li[$Ki]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo"</table>\n","</form>\n";$ig=[];foreach($Li
as$u=>$X){if($u!=""&&$X>1)$ig[]="<a href='".h(ME)."dump=".urlencode("$u%")."'>".icon("check").h($u)."*</a>";}if($ig)echo"<p class='links'>",implode("",$ig),"</p>\n";}elseif(isset($_GET["privileges"])){$yl=DB!=""?h(": ".DB):"";page_header(lang(72).$yl,[lang(72)]);echo'<p class="links top-links"><a href="',h(ME),'user=">',icon("user-add"),lang(183),"</a></p>\n";$I=Connection::get()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$te=$I;if(!$I)$I=Connection::get()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''>\n";hidden_fields_get();echo
input_hidden("db",DB);if(!$te)echo
input_hidden("grant");echo"\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr><th>".lang(28)."<th>".lang(5)."<th></thead>\n";while($K=$I->fetchAssoc())echo'<tr><td>'.h($K["User"])."<td>".h($K["Host"]).'<td><a href="'.h(ME.'user='.urlencode($K["User"]).'&host='.urlencode($K["Host"])).'">'.lang(38)."</a>\n";if(!$te||DB!="")echo"<tr><td><input class='input' name='user' autocapitalize='off'><td><input class='input' name='host' value='localhost' autocapitalize='off'><td><input type='submit' class='button' value='".lang(38)."'>\n";echo"</table>\n","</div>\n","</form>\n";}elseif(isset($_GET["sql"])){$O=Admin::get()->getSettings();if($_POST["export"]){$O->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers("sql");Admin::get()->dumpTable("","");Admin::get()->dumpData("","table",$_POST["query"]);exit;}restart_session();$Le=&get_session("queries");$Ke=&$Le[DB];if($_POST["clear"]){$Ke=[];redirect(remove_from_uri("history"));}stop_session();$T=isset($_GET["import"])?lang(73):lang(40);page_header($T,[$T]);$fg="--".(DIALECT=="sql"?" ":"");if($_POST){$ke=false;if(!isset($_GET["import"]))$H=$_POST["query"];elseif($_POST["webfile"]){$Xe=Admin::get()->getImportFilePath();if($Xe){if(file_exists($Xe))$ke=fopen($Xe,"rb");elseif(file_exists("$Xe.gz"))$ke=fopen("compress.zlib://$Xe.gz","rb");}$H=$ke?fread($ke,1e6):false;}else$H=get_file("sql_file",true,";");if(is_string($H)){if(($Dg=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($Dg,strval(2*strlen($H)+memory_get_usage()+8e6)));if($H!=""&&strlen($H)<1e6){$Wi=$H.(preg_match("~;[ \t\r\n]*\$~",$H)?"":";");if(!$Ke||first(end($Ke))!=$Wi){restart_session();$Ke[]=[$Wi,time()];set_session("queries",$Le);stop_session();}}$wk="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$fg)[^\n]*\n?|--\r?\n)";$yc=";";$zc=1;$sh=0;$gd=true;$Tb=connect();if($Tb&&DB!=""){$Tb->selectDatabase(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$Tb);}$Jb=0;$pd=[];$ii='[\'"'.(DIALECT=="sql"?'`#':(DIALECT=="sqlite"?'`[':(DIALECT=="mssql"?'[':''))).']|/\*|'.$fg.'|$'.(DIALECT=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$Dl=microtime(true);$Xc=Admin::get()->getDumpFormats();unset($Xc["sql"]);while($H!=""){if(!$sh&&preg_match("~^$wk*+DELIMITER\\s+(\\S+)~i",$H,$z)){$yc=preg_quote($z[1]);$zc=strlen($z[1]);$ge=Admin::get()->formatSqlCommandQuery(trim($z[0]));if($ge!="")echo"<pre><code class='jush-".DIALECT."'>$ge</code></pre>\n";$H=substr($H,strlen($z[0]));}elseif(!$sh&&DIALECT=="pgsql"&&preg_match("~^($wk*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$H,$z)){$yc="\n\\\\\\.\r?\n";$zc=3;$sh=strlen($z[0]);}else{preg_match("($yc\\s*|$ii)",$H,$z,PREG_OFFSET_CAPTURE,$sh);list($ie,$G)=$z[0];if(!$ie&&$ke&&!feof($ke))$H
.=fread($ke,1e5);else{if(!$ie&&rtrim($H)=="")break;$sh=$G+strlen($ie);if($ie&&!preg_match("(^$yc)",$ie)){$ib=Driver::get()->hasCStyleEscapes()||(DIALECT=="pgsql"&&($G>0&&strtolower($H[$G-1])=="e"));$vi='(';if($ie=='/*')$vi
.='\*/';elseif($ie=='[')$vi
.=']';elseif(preg_match("~^$fg|^#~",$ie))$vi
.="\n";else$vi
.=preg_quote($ie).($ib?"|\\\\.":"");$vi
.='|$)s';while(preg_match($vi,$H,$z,PREG_OFFSET_CAPTURE,$sh)){$Dj=$z[0][0];if(!$Dj&&$ke&&!feof($ke))$H
.=fread($ke,1e5);else{$sh=$z[0][1]+strlen($Dj);if(!isset($Dj[0])||$Dj[0]!="\\")break;}}}else{$gd=false;$Wi=substr($H,0,$G+$zc);$Jb++;$Oi="<pre id='sql-$Jb'><code class='jush-".DIALECT."'>".Admin::get()->formatSqlCommandQuery(trim($Wi))."</code></pre>\n";if(DIALECT=="sqlite"&&preg_match("~^$wk*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$Wi,$z)!==0){echo$Oi,"<p class='error'>".lang(184,preg_match('~ATTACH~i',$z[1])?'ATTACH':'VACUUM INTO')."\n";$pd[]=" <a href='#sql-$Jb'>$Jb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Oi;ob_flush();flush();}$Ak=microtime(true);if(Connection::get()->multiQuery($Wi)&&is_object($Tb)&&preg_match("~^$wk*+USE\\b~i",$Wi))$Tb->query($Wi);do{$I=Connection::get()->storeResult();if(Connection::get()->getError()){echo($_POST["only_errors"]?$Oi:""),"<p class='error'>",lang(185),(!empty(Connection::get()->getErrno())?" (".Connection::get()->getErrno().")":""),": ",error()."</p>\n";$pd[]=" <a href='#sql-$Jb'>$Jb</a>";if($_POST["error_stops"])break
2;}else{$vl=" <span class='time'>(".format_time($Ak).")</span>";$bd=(strlen($Wi)<1000?" <a href='".h(ME)."sql=".urlencode(trim($Wi))."'>".icon("edit").lang(38)."</a>":"");$aj=Connection::get()->getQueryInfo();$za=Connection::get()->getAffectedRows();$_m=($_POST["only_errors"]?null:Driver::get()->warnings());$Bm="warnings-$Jb";$Cm=$_m?"<a href='#$Bm' class='toggle'>".lang(39).icon_chevron_down()."</a>":null;$xd=$Th=null;$yd="explain-$Jb";$zd=false;$_d="export-$Jb";$w=0;if(is_object($I)){if(!$_POST["only_errors"])echo"<div class='table-result'>\n";$w=(int)$_POST["limit"];$Th=print_select_result($I,$Tb,[],$w);if(!$_POST["only_errors"]){echo"<p class='links'>";$ph=$I->getRowsCount();echo($ph?($w&&$ph>$w?lang(186,$w):"").lang(187,$ph):""),$vl,$bd,$Cm;if($Tb&&preg_match("~^($wk|\\()*+SELECT\\b~i",$Wi)&&($xd=explain($Tb,$Wi)))echo"<a href='#$yd' class='toggle'>Explain".icon_chevron_down()."</a>";$zd=true;echo"<a href='#$_d' class='toggle'>".lang(74).icon_chevron_down()."</a>","</p>\n";}}else{if(preg_match("~^$wk*+(CREATE|DROP|ALTER)$wk++(DATABASE|SCHEMA)\\b~i",$Wi)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"]){echo"<p class='message' title='".h($aj)."'>",lang(188,$za),"$vl $bd";if($Cm)echo", $Cm";echo"</p>\n";}}if(!$_POST["only_errors"])echo
script("initToggles(qsl('p'));");if($_m)echo"<div id='$Bm' class='hidden'>\n$_m</div>\n";if($xd){echo"<div id='$yd' class='hidden explain'>\n";print_select_result($xd,$Tb,$Th);echo"</div>\n";}if($zd){echo"<form id='$_d' action='' method='post' class='hidden'><p>\n",html_select("format",$Xc,$O->getParameter("exportFormat")),html_select("output",Admin::get()->getDumpOutputs(),$O->getParameter("exportOutput"))." ",input_hidden("query",$Wi),input_token()," <input type='submit' class='button' name='export' value='".lang(74)."'>";if(!$w)echo
script("qsl('input').onclick = partial(sqlExport, '".js_escape(ME)."set=export-settings');","");echo"</p></form>\n";}if(is_object($I)&&!$_POST["only_errors"])echo"</div>\n";}$Ak=microtime(true);}while(Connection::get()->nextResult());}$H=substr($H,$sh);$sh=0;}}}}if($gd)echo"<p class='message'>".lang(189)."\n";elseif($_POST["only_errors"]){$vh=$Jb-count($pd);echo"<p class='".($vh?"message":"error")."'>".lang(190,$Jb-count($pd))," <span class='time'>(".format_time($Dl).")</span>\n";}elseif($pd&&$Jb>1)echo"<p class='error'>".lang(185).": ".implode("",$pd)."\n";}else
echo"<p class='error'>".upload_error($H)."\n";}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";if(!isset($_GET["import"])){$Wi=$_GET["sql"];if($_POST)$Wi=$_POST["query"];elseif($_GET["history"]=="all")$Wi=$Ke;elseif($_GET["history"]!="")$Wi=$Ke[$_GET["history"]][0];echo"<p>";textarea("query",$Wi,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."gid('form').onsubmit = partial(sqlSubmit, gid('form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"</p>","<p><input type='submit' class='button default' value='".lang(191)."' title='Ctrl+Enter'>",lang(192).": <input type='number' name='limit' class='input size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{echo"<div class='field-sets'>\n","<fieldset><legend>".lang(193)."</legend><div class='fieldset-content'>";$Ae=(extension_loaded("zlib")?"[.gz]":"");if(ini_bool("file_uploads"))echo"SQL$Ae (&lt; ".ini_get("upload_max_filesize")."B): <input type='file' name='sql_file[]' multiple>","<input type='submit' class='button default' value='".lang(191)."'>",file_upload_form_script("form","sql_file[]");else
echo
lang(194);echo"</div></fieldset>\n";$Xe=Admin::get()->getImportFilePath();if($Xe)echo"<fieldset><legend>".lang(195)."</legend><div class='fieldset-content'>",lang(196,"<code>".h($Xe)."$Ae</code>")," <input type='submit' class='button default' name='webfile' value='".lang(197)."'>","</div></fieldset>\n";echo"</div>\n","<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),lang(198)),checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),lang(199)),input_token(),"</p>\n";if(!isset($_GET["import"]))Admin::get()->printAfterSqlCommand();if(!isset($_GET["import"])&&$Ke){echo"<div class='field-sets'>\n";print_fieldset_start("history",lang(200),"history",$_GET["history"]!="");for($X=end($Ke);$X;$X=prev($Ke)){$u=key($Ke);list($Wi,$vl,$fd)=$X;echo" <pre><code class='jush-".DIALECT."'>",truncate_utf8(ltrim(str_replace("\n"," ",str_replace("\r","",preg_replace("~^(#|$fg).*~m",'',$Wi))))),"</code></pre>",'<p class="links">',"<a href='".h(ME."sql=&history=$u")."'>".icon("edit").lang(38)."</a>"," <span class='time' title='".@date('Y-m-d',$vl)."'>".@date("H:i:s",$vl).($fd?" ($fd)":"")."</span>","</p>";}echo"<p><input type='submit' class='button' name='clear' value='".lang(201)."'>\n","<a href='",h(ME."sql=&history=all")."' class='button light'>",icon("edit"),lang(202),"</a></p>\n";print_fieldset_end("history");echo"</div>\n";}echo"</form>\n";}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$l=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$l):""):where($_GET,$l));$Zl=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($l
as$A=>$k){if((!$Zl&&!isset($k["privileges"]["insert"]))||Admin::get()->getFieldName($k)=="")unset($l[$A]);}if($_POST&&!isset($_GET["select"])){$y=$_POST["referer"];if($_POST["insert"])$y=($Zl?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$y))$y=ME."select=".urlencode($a);$t=indexes($a);$Tl=unique_array(isset($_GET["where"])?$_GET["where"]:[],$t);$bj="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($y,lang(203),(bool)Driver::get()->delete($a,$bj,$Tl?0:1));else{$kk=[];foreach($l
as$A=>$k){$X=process_input($k);if($X!==false&&$X!==null)$kk[idf_escape($A)]=$X;}if($Zl){if(!$kk)redirect($y);queries_redirect($y,lang(204),(bool)Driver::get()->update($a,$kk,$bj,$Tl?0:1));if(is_ajax()){page_headers();page_messages();exit;}}else{$I=Driver::get()->insert($a,$kk);$Uf=($I?last_id($I):0);queries_redirect($y,lang(205,($Uf?" $Uf":"")),(bool)$I);}}}$K=null;if($Z){$M=[];foreach($l
as$A=>$k){if(isset($k["privileges"]["select"])){$La=($_POST["clone"]&&$k["auto_increment"]?"''":convert_field($k));$M[]=($La?"$La AS ":"").idf_escape($A);}}$K=[];if(!support("table"))$M=["*"];if($M){$I=Driver::get()->select($a,$M,[$Z],$M,[],(isset($_GET["select"])?2:1));if(!$I)Admin::get()->addError(error());else{$K=$I->fetchAssoc();if(!$K)$K=false;}if(isset($_GET["select"])&&(!$K||$I->fetchAssoc()))$K=null;}}if(!support("table")&&!$l){if(!$Z){$I=Driver::get()->select($a,["*"],[],["*"]);$K=($I?$I->fetchAssoc():false);if(!$K)$K=[Driver::get()->primary=>""];}if($K){foreach($K
as$u=>$X){if(!$Z)$K[$u]=null;$l[$u]=["field"=>$u,"null"=>($u!=Driver::get()->primary),"auto_increment"=>($u==Driver::get()->primary)];}}}if(isset($_POST["save"])?$_POST["save"]:false){$Hi=[];foreach((isset($_POST["fields"])?$_POST["fields"]:[])as$u=>$X)$Hi[bracket_escape($u,true)]=$X;$K=$Hi+($K?:[]);}if($_POST["edit"]){$dd=array_filter($l,function($k){return!(isset($k["generated"])?$k["generated"]:null);});}else$dd=$l;edit_form($a,$dd,$K,$Zl);}elseif(isset($_GET["create"])){$a=$_GET["create"];$mi=Driver::get()->getPartitionBy();$qi=$mi?Driver::get()->getPartitionsInfo($a):[];$jj=referencable_primary($a);$de=[];foreach($jj
as$Zk=>$k)$de[str_replace("`","``",$Zk)."`".str_replace("`","``",$k["field"])]=$Zk;$Wh=[];$R=[];if($a!=""){$Wh=fields($a);$R=table_status1($a);if(count($R)<2)Admin::get()->addError(lang(78));}$K=$_POST;$K["Comment"]=normalize_newlines($K["Comment"]);$K["fields"]=(array)$K["fields"];if($K["auto_increment_col"])$K["fields"][$K["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!Admin::get()->getErrors())Admin::get()->getSettings()->updateParameter("commentsOpened",isset($_POST["comments"])?$_POST["comments"]:null);if($_POST&&!process_fields($K["fields"])&&!Admin::get()->getErrors()){if($_POST["drop"])queries_redirect(substr(ME,0,-1),lang(206),drop_tables([$a]));else{$l=[];$Ea=[];$em=false;$be=[];$Vh=reset($Wh);$Aa=" FIRST";foreach($K["fields"]as$u=>$k){$o=$de[$k["type"]];$Nl=($o!==null?$jj[$o]:$k);if($k["field"]!=""){if(!$k["generated"])$k["default"]=null;$Ui=process_field($k,$Nl);$Ea[]=[$k["orig"],$Ui,$Aa];if(!$Vh||$Ui!==process_field($Vh,$Vh)){$l[]=[$k["orig"],$Ui,$Aa];if($k["orig"]!=""||$Aa)$em=true;}if($o!==null)$be[idf_escape($k["field"])]=($a!=""&&DIALECT!="sqlite"?"ADD":" ").format_foreign_key(['table'=>$de[$k["type"]],'source'=>[$k["field"]],'target'=>[$Nl["field"]],'on_delete'=>$k["on_delete"],]);$Aa=" AFTER ".idf_escape($k["field"]);}elseif($k["orig"]!=""){$em=true;$l[]=[$k["orig"]];}if($k["orig"]!=""){$Vh=next($Wh);if(!$Vh)$Aa="";}}$oi=[];if(in_array($K["partition_by"],$mi)){foreach($K
as$u=>$X){if(preg_match('~^partition~',$u))$oi[$u]=$X;}foreach($oi["partition_names"]as$u=>$A){if($A===""){unset($oi["partition_names"][$u]);unset($oi["partition_values"][$u]);}}$oi["partition_names"]=array_values($oi["partition_names"]);$oi["partition_values"]=array_values($oi["partition_values"]);if($oi==$qi)$oi=[];}elseif(str_contains(isset($R["Create_options"])?$R["Create_options"]:"","partitioned"))$oi=null;$Fg=lang(207);if($a==""){cookie("neo_engine",isset($K["Engine"])?$K["Engine"]:"");$Fg=lang(208);}$A=trim($K["name"]);queries_redirect(ME.(support("table")?"table=":"select=").urlencode($A),$Fg,alter_table($a,$A,(DIALECT=="sqlite"&&($em||$be)?$Ea:$l),$be,($K["Comment"]!=$R["Comment"]?$K["Comment"]:null),($K["Engine"]&&$K["Engine"]!=$R["Engine"]?$K["Engine"]:""),($K["Collation"]&&$K["Collation"]!=$R["Collation"]?$K["Collation"]:""),($K["Auto_increment"]!=""?number($K["Auto_increment"]):""),$oi));}}if($a!="")page_header(lang(35).": ".h($a),["table"=>$a,lang(35)]);else
page_header(lang(77),[lang(77)]);if(!$_POST){$Pl=Driver::get()->getTypes();$K=["Engine"=>$_COOKIE["neo_engine"],"fields"=>[["field"=>"","type"=>(isset($Pl["int"])?"int":(isset($Pl["integer"])?"integer":"")),"on_update"=>""]],"partition_names"=>[""],];if($a!=""){$K=$R;$K["name"]=$a;$K["fields"]=[];if(!$_GET["auto_increment"])$K["Auto_increment"]="";foreach($Wh
as$k){$k["generated"]=$k["generated"]?:(isset($k["default"])?"DEFAULT":"");$K["fields"][]=$k;}if($mi){$K+=$qi;$K["partition_names"][]="";$K["partition_values"][]="";}}}$Ff=[];if($K["Collation"])$Ff[$K["Collation"]]=true;foreach($K["fields"]as$k){if($k["collation"])$Ff[$k["collation"]]=true;}$Cb=Admin::get()->getCollations(array_keys($Ff));$ld=Driver::get()->engines();foreach($ld
as$kd){if(!strcasecmp($kd,$K["Engine"])){$K["Engine"]=$kd;break;}}echo"<form action='' method='post' id='form'>\n";if(support("columns")||$a==""){echo"<p>",lang(209),": ","<input class='input' name='name' data-maxlength='64' value='",h($K["name"]),"' autocapitalize='off'",(($a==""&&!$_POST)?" autofocus":""),">";if($ld)echo" ",html_select("Engine",[""=>"(".lang(210).")"]+$ld,$K["Engine"]),help_script_command("value",true);if($Cb&&!preg_match("~sqlite|mssql~",DIALECT))echo" ",html_select("Collation",[""=>"(".lang(91).")"]+$Cb,$K["Collation"]);echo" <input type='submit' class='button default' value='",lang(113),"'>","</p>";}if(support("columns")&&($a==""||!Driver::get()->isPartition($a))){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($K["fields"],$Cb,"TABLE",$de);echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>",lang(47),": ","<input type='number' class='input size' name='Auto_increment' size='6' value='",h($K["Auto_increment"]),"'>";$Nb=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$Lb=$Nb?"":"hidden";if(support("comment")){echo
checkbox("comments",1,$Nb,lang(46),"editingCommentsClick(this, ".(support("move_col")?7:6).");","jsonly")," ";if(preg_match('~\n~',$K["Comment"]))echo"<textarea name='Comment' rows='2' cols='20'",($Lb?" class='$Lb'":""),">",h($K["Comment"]),"</textarea>";else
echo"<input name='Comment' value='",h($K["Comment"]),"' data-maxlength='",(Connection::get()->isMinVersion("5.5")?2048:60),"' class='input $Lb'>";}echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(113),"'>";}elseif($a!="")echo"<p>";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(160),"'>",confirm(lang(211,$a)),"</p>\n";if($mi&&(DIALECT=="sql"||$a=="")){echo"<div class='field-sets'>\n";$ni=preg_match('~RANGE|LIST~',$K["partition_by"]);print_fieldset_start("partition",lang(212),"split",(bool)$K["partition_by"]);echo"<p>",html_select("partition_by",array_merge([""],$mi),$K["partition_by"]),help_script_command("value.replace(/./, 'PARTITION BY \$&')",true),script("qsl('select').onchange = partitionByChange;"),"(<input class='input' name='partition' value='",h($K["partition"]),"'>) ",lang(49),": ","<input type='number' name='partitions' class='input size ",($ni||!$K["partition_by"]?"hidden":""),"' value='",h($K["partitions"]),"'>","</p>\n","<table id='partition-table'",($ni?"":" class='hidden'"),">\n","<thead><tr><th>",lang(213),"</th><th>",lang(51),"</th></tr></thead>\n";foreach($K["partition_names"]as$u=>$X){echo"<tr>","<td><input class='input' name='partition_names[]' value='",h($X),"' autocapitalize='off'>";if($u==count($K["partition_names"])-1)echo
script("qsl('input').oninput = partitionNameChange;");echo"</td>","<td><input class='input' name='partition_values[]' value='",h(isset($K["partition_values"][$u])?$K["partition_values"][$u]:""),"'></td>","</tr>\n";}echo"</table>\n","</p>\n";print_fieldset_end("partition");echo"</div>\n";}echo
input_token(),"</form>\n";}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$df=["PRIMARY","UNIQUE","INDEX"];$R=table_status1($a,true);$bf=Driver::get()->getIndexAlgorithms($R);$e=Connection::get();$rg=$e->isMariaDB();if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($rg?"10.0.5":"5.6")?'|InnoDB':'').'~i',$R["Engine"]))$df[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($rg?"10.2.2":"5.7")?'|InnoDB':'').'~i',$R["Engine"]))$df[]="SPATIAL";if($rg&&$e->isMinVersion("11.7")&&preg_match('~MyISAM|InnoDB~i',$R["Engine"]))$df[]="VECTOR";$t=indexes($a);$l=fields($a);$Ni=[];if(DIALECT=="mongo"){$Ni=$t["_id_"];unset($df[0]);unset($t["_id_"]);}$K=$_POST;if($K){$O=Admin::get()->getSettings();if($O->getParameter("indexOptions")!==null)$O->updateParameter("indexOptions",null);}if($_POST&&!$_POST["add"]&&!$_POST["drop_col"]){$Ga=[];foreach($K["indexes"]as$s){$A=$s["name"];if(in_array($s["type"],$df)){$c=[];$cg=[];$Bc=[];$Hh=[];$af=$bf?(in_array($s["algorithm"],$bf)?$s["algorithm"]:first($bf)):"";$cf=(support("partial_indexes")?$s["partial"]:"");$kk=[];ksort($s["columns"]);foreach($s["columns"]as$u=>$b){if($b!=""){$v=isset($s["lengths"][$u])?$s["lengths"][$u]:null;$_c=isset($s["descs"][$u])?$s["descs"][$u]:null;$Gh=isset($s["opclasses"][$u])?$s["opclasses"][$u]:null;$kk[]=($l[$b]?idf_escape($b):$b).($v?"(".(+$v).")":"").($Gh!=""?" ".idf_escape($Gh):"").($_c?" DESC":"");$c[]=$b;$cg[]=($v?:null);$Bc[]=$_c;$Hh[]="$Gh";}}$wd=$t[$A];if($wd){ksort($wd["columns"]);ksort($wd["lengths"]);ksort($wd["descs"]);if($s["type"]==$wd["type"]&&array_values($wd["columns"])===$c&&(!$wd["lengths"]||array_values($wd["lengths"])===$cg)&&array_values($wd["descs"])===$Bc&&(!$wd["opclasses"]||array_values($wd["opclasses"])===$Hh)&&(!$bf||$wd["algorithm"]===$af)&&$wd["partial"]==$cf){unset($t[$A]);continue;}}if($c)$Ga[]=[$s["type"],$A,$kk,$af,$cf];}}foreach($t
as$A=>$wd)$Ga[]=[$wd["type"],$A,"DROP"];if(!$Ga)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),lang(214),alter_indexes($a,$Ga));}page_header(lang(167),["table"=>$a,lang(167)],h($a));$Od=array_keys($l);if($_POST["add"]){foreach($K["indexes"]as$u=>$s){if($s["columns"][count($s["columns"])]!="")$K["indexes"][$u]["columns"][]="";}$s=end($K["indexes"]);if($s["type"]||array_filter($s["columns"],'strlen'))$K["indexes"][]=["columns"=>[1=>""]];}if(!$K){foreach($t
as$u=>$s){$t[$u]["name"]=$u;$t[$u]["columns"][]="";}$t[]=["columns"=>[1=>""]];$K["indexes"]=$t;}$cg=(DIALECT=="sql"||DIALECT=="mssql");$Hh=Driver::get()->getIndexOpclasses();if($_POST)$ok=$_POST["options"];else{$ok=false;foreach($t
as$s){if(array_filter(isset($s["lengths"])?$s["lengths"]:[])||array_filter(isset($s["descs"])?$s["descs"]:[])||array_filter(isset($s["opclasses"])?$s["opclasses"]:[])||(isset($s["partial"])?$s["partial"]:"")!=""){$ok=true;break;}}}echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th id='label-type'>",lang(215),"</th>";$Mh="class='idxopts".($ok?"":" hidden")."'";if(count($bf)>1)echo"<th id='label-method' $Mh>",lang(216),doc_link(['sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'ha-and-performance/optimization-and-tuning/optimization-and-indexes/storage-engine-index-types',]),"</th>";echo"<th><input type='submit' hidden>",lang(52).($cg?"<span $Mh> (".lang(53).")</span>":"");if($cg||support("descidx"))echo
checkbox("options",1,$ok,lang(97),"indexOptionsShow(this.checked)","jsonly")."\n";echo"</th>","<th id='label-name'>",lang(217),"</th>";if(support("partial_indexes"))echo"<th id='label-condition' $Mh>",lang(54),"</th>";echo"<th>","<button name='add[0]' value='1' title='",lang(98),"' class='button light hidden'>",icon_solo("add"),"</button>","</th>","</tr></thead>\n";if($Ni){echo"<tr><td>PRIMARY<td>";foreach($Ni["columns"]as$b)echo
select_input(" disabled",$Od,$b),"<label><input type='checkbox' disabled>".lang(62)."</label> ";echo"<td><td>\n";}$Bf=1;foreach($K["indexes"]as$s){if(!$_POST["drop_col"]||$Bf!=key($_POST["drop_col"])){echo"<tr><td>",html_select("indexes[$Bf][type]",[-1=>""]+$df,$s["type"],($Bf==count($K["indexes"])?"indexesAddRow.call(this);":""),"label-type"),"</td>";if(count($bf)>1)echo"<td $Mh>",html_select("indexes[$Bf][algorithm]",array_merge([""],$bf),$s['algorithm'],"label-method"),"</td>";echo"<td>";ksort($s["columns"]);$q=1;foreach($s["columns"]as$u=>$b){echo"<span>".select_input(" name='indexes[$Bf][columns][$q]' title='".lang(43)."'",($l&&($b==""||$l[$b])?array_combine($Od,$Od):[]),$b,"partial(".($q==count($s["columns"])?"indexesAddColumn":"indexesChangeColumn").", '".js_escape(DIALECT=="sql"?"":$_GET["indexes"]."_")."')"),"<span $Mh>";if($cg)echo"<input type='number' name='indexes[$Bf][lengths][$q]' class='input size' value='".(h(isset($s["lengths"][$u])?$s["lengths"][$u]:"")),"' title='".lang(96),"'>";if($Hh){$Gh=isset($s["opclasses"][$u])?$s["opclasses"][$u]:"";echo
html_select("indexes[$Bf][opclasses][$q]",[""=>"(".lang(218).")"]+array_combine($Hh,$Hh)+($Gh!=""?[$Gh=>$Gh]:[]),$Gh),'';}if(support("descidx"))echo
checkbox("indexes[$Bf][descs][$q]",1,isset($s["descs"][$u])?$s["descs"][$u]:false,lang(62));echo"<br></span></span>";$q++;}echo"</td>","<td><input name='indexes[$Bf][name]' value='",h($s["name"]),"' class='input' autocapitalize='off' aria-labelledby='label-name'></td>\n";if(support("partial_indexes"))echo"<td $Mh><input name='indexes[$Bf][partial]' value='".h($s["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>","<button name='drop_col[$Bf]' value='1' title='",lang(58),"' class='button light'>",icon_solo("remove"),"</button>",script("qsl('button').onclick = onRemoveIndexRowClick;"),"</td>\n";}$Bf++;}echo"</table>\n","</div>\n","<p>","<input type='submit' class='button default' value='",lang(113),"'>",input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["database"])){$K=$_POST;if($_POST&&!isset($_POST["add_x"])){$A=trim($K["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),lang(219),drop_databases([DB]));}elseif(DB!==$A){if(DB!=""){$_GET["db"]=$A;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($A),lang(220),rename_database($A,$K["collation"]));}else{$g=explode("\n",str_replace("\r","",$A));$Jk=true;$Tf="";foreach($g
as$h){if(count($g)==1||$h!=""){if(!create_database($h,$K["collation"]))$Jk=false;$Tf=$h;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($Tf),lang(221),$Jk);}}else{if(!$K["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($A).(preg_match('~^[a-z0-9_]+$~i',$K["collation"])?" COLLATE $K[collation]":""),substr(ME,0,-1),lang(222));}}if(DB!="")page_header(lang(69).": ".h(DB),[lang(69)]);else
page_header(lang(75),[lang(75)]);$A=DB;if($_POST)$A=$K["name"];elseif(DB!="")$K["collation"]=db_collation(DB,collations());elseif(DIALECT=="sql"){foreach(get_vals("SHOW GRANTS")as$te){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$te,$z)&&$z[1]){$A=stripcslashes(idf_unescape("`$z[2]`"));break;}}}$Cb=Admin::get()->getCollations($K["collation"]?[$K["collation"]]:[]);echo"<form action='' method='post'>\n","<p>";if($_POST["add_x"]||strpos($A,"\n"))echo"<textarea id='name' name='name' rows='10' cols='40'>",h($A),"</textarea><br>\n";else
echo"<input class='input' name='name' id='name' value='",h($A),"' data-maxlength='64' autocapitalize='off' autofocus>\n";if($Cb)echo
html_select("collation",[""=>"(".lang(91).")"]+$Cb,$K["collation"]),doc_link(['sql'=>"charset-charsets.html",'mariadb'=>"reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations",]),"\n";echo"<input type='submit' class='button default' value='",lang(113),"'>\n";if(DB!="")echo"<input type='submit' class='button' name='drop' value='".lang(160)."'>".confirm(lang(211,DB))."\n";elseif(!$_POST["add_x"]&&$_GET["db"]=="")echo"<button name='add_x' value='1' title='",lang(98),"' class='button light'>",icon_solo("add"),"</button>\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["call"])){$oa=$_GET["name"]?:$_GET["call"];page_header(lang(223).": ".h($oa),[lang(223)]);$_j=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$Ye=[];$ai=[];foreach($_j["fields"]as$q=>$k){if(substr($k["inout"],-3)=="OUT"&&DIALECT=='sql')$ai[$q]="@".idf_escape($k["field"])." AS ".idf_escape($k["field"]);if(!$k["inout"]||substr($k["inout"],0,2)=="IN")$Ye[]=$q;}if($_POST){$kb=[];foreach($_j["fields"]as$u=>$k){$X="";if(in_array($u,$Ye)){$X=process_input($k);if($X===false)$X="''";if(isset($ai[$u]))Connection::get()->query("SET @".idf_escape($k["field"])." = $X");}if(isset($ai[$u]))$kb[]="@".idf_escape($k["field"]);elseif(in_array($u,$Ye))$kb[]=$X;}$H=(isset($_GET["callf"])?"SELECT ":"CALL ").($_j["returns"]&&$_j["returns"]["type"]=="record"?"* FROM ":"").table($oa)."(".implode(", ",$kb).")";$Ak=microtime(true);$I=Connection::get()->multiQuery($H);$za=Connection::get()->getAffectedRows();echo
Admin::get()->formatSelectQuery($H,$Ak,!$I);if(!$I)echo"<p class='error'>".error()."\n";else{$Tb=connect();if($Tb)$Tb->selectDatabase(DB);do{$I=Connection::get()->storeResult();if(is_object($I))print_select_result($I,$Tb);else
echo"<p class='message'>".lang(224,$za)." <span class='time'>".@date("H:i:s")."</span>\n";}while(Connection::get()->nextResult());if($ai)print_select_result(Connection::get()->query("SELECT ".implode(", ",$ai)));}}echo"<form action='' method='post'>\n";if($Ye){echo"<table class='box'>\n";foreach($Ye
as$u){$k=$_j["fields"][$u];$A=$k["field"];echo"<tr><th>".Admin::get()->getFieldName($k);$Y=isset($_POST["fields"][$A])?$_POST["fields"][$A]:"";if($Y!=""){if($k["type"]=="set")$Y=implode(",",$Y);}input($k,$Y,(string)(isset($_POST["function"][$A])?$_POST["function"][$A]:""));echo"\n";}echo"</table>\n";}echo"<p>\n","<input type='submit' class='button' value='",lang(223),"'>\n",input_token(),"</p>\n","</form>\n";$Kb=$_j["comment"];if($Kb!==null&&$Kb!==""){$Kb=h(trim($_j["comment"],"\n"));if(preg_match('~^ +~',$Kb,$_)){preg_match_all("~^($_[0]|$)~m",$Kb,$gg);if(count($gg[0])==substr_count($Kb,"\n"))$Kb=preg_replace("~^($_[0])~m","",$Kb);}$Kb=preg_replace('~(^|[^\n]\n)(Description|Parameters|Example)\n~',"$1\n<strong>$2</strong>\n",$Kb);echo"<pre class='comment'>$Kb</pre>\n";}}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$A=$_GET["name"];$K=$_POST;if($_POST&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$K["source"]=array_filter($K["source"],'strlen');ksort($K["source"]);$ll=[];foreach($K["source"]as$u=>$X)$ll[$u]=$K["target"][$u];$K["target"]=$ll;}if(DIALECT=="sqlite")$I=recreate_table($a,$a,[],[],[" $A"=>($K["drop"]?"":" ".format_foreign_key($K))]);else{$Ga="ALTER TABLE ".table($a);$I=($A==""||queries("$Ga DROP ".(DIALECT=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($A)));if(!$K["drop"])$I=queries("$Ga ADD".format_foreign_key($K));}queries_redirect(ME."table=".urlencode($a),($K["drop"]?lang(225):($A!=""?lang(226):lang(227))),(bool)$I);if(!$K["drop"])Admin::get()->addError(lang(228));}page_header(lang(229).": ".h($a),["table"=>$a,lang(229)]);if($_POST){ksort($K["source"]);if($_POST["change"]||$_POST["change-js"])$K["target"]=[];else$K["source"][]="";}elseif($A!=""){$de=foreign_keys($a);$K=$de[$A];$K["source"][]="";}else{$K["table"]=$a;$K["source"]=[""];}echo"<form action='' method='post'>\n";$uk=array_keys(fields($a));if($K["db"]!="")Connection::get()->selectDatabase($K["db"]);if($K["ns"]!=""){$Xh=get_schema();set_schema($K["ns"]);}$ij=array_keys(array_filter(table_status('',true),'AdminNeo\fk_support'));$ll=array_keys(fields(in_array($K["table"],$ij)?$K["table"]:reset($ij)));$Ch="this.form['change-js'].value = '1'; this.form.submit();";echo"<p>","<span id='label-table'>",lang(230),":</span> ",html_select("table",$ij,$K["table"],$Ch,"label-table");if(DIALECT!="sqlite"){$qc=[];foreach(Admin::get()->getDatabases()as$h){if(!information_schema($h))$qc[]=$h;}echo"<span id='label-db'>",lang(231),":</span> ",html_select("db",$qc,$K["db"]!=""?$K["db"]:$_GET["db"],$Ch,"label-db");}echo
input_hidden("change-js"),"<noscript><input type='submit' class='button' name='change' value='",lang(232),"'></noscript>","</p>\n","<table>","<thead><tr><th id='label-source'>",lang(168),"<th id='label-target'>",lang(169),"</thead>\n";$Bf=0;foreach($K["source"]as$u=>$X){echo"<tr>","<td>".html_select("source[".(+$u)."]",[-1=>""]+$uk,$X,($Bf==count($K["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$u)."]",$ll,isset($K["target"][$u])?$K["target"][$u]:null,"","label-target");$Bf++;}echo"</table>\n","<noscript><p><input type='submit' class='button' name='add' value='",lang(233),"'></p></noscript>","<p>\n","<span id='label-delete'>".lang(93),":</span> ",html_select("on_delete",[-1=>""]+Driver::get()->getOnActions(),$K["on_delete"],"","label-delete"),"<span id='label-update'>".lang(92),":</span> ",html_select("on_update",[-1=>""]+Driver::get()->getOnActions(),$K["on_update"],"","label-update");if(DRIVER=='pgsql')echo
html_select("deferrable",['NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'],$K["deferrable"]);echo
doc_link(['sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"architecture/server-constraints/foreign-key-constraints",]),"</p>\n<p>","<input type='submit' class='button default' value='",lang(113),"'>";if($A!="")echo"<input type='submit' class='button' name='drop' value='",lang(160),"'>",confirm(lang(211,$A));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["view"])){$a=$_GET["view"];$K=$_POST;$Yh="VIEW";if(DIALECT=="pgsql"&&$a!=""){$P=table_status1($a);$Yh=strtoupper($P["Engine"]);}if($_POST){$A=trim($K["name"]);$La=" AS\n$K[select]";$y=ME."table=".urlencode($A);$Fg=lang(234);$U=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$A&&DIALECT!="sqlite"&&$U=="VIEW"&&$Yh=="VIEW")query_redirect((DIALECT=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($A).$La,$y,$Fg);else{$nl=$A."_adminneo_".uniqid();drop_create("DROP $Yh ".table($a),"CREATE $U ".table($A).$La,"DROP $U ".table($A),"CREATE $U ".table($nl).$La,"DROP $U ".table($nl),($_POST["drop"]?substr(ME,0,-1):$y),lang(235),$Fg,lang(236),$a,$A);}}if(!$_POST&&$a!=""){$K=view($a);$K["name"]=$a;$K["materialized"]=($Yh!="VIEW");if($j=error())Admin::get()->addError($j);}if($a!="")page_header(lang(36).": ".h($a),["table"=>$a,lang(36)]);else
page_header(lang(237),[lang(237)]);echo"<form action='' method='post'>\n","<p>",lang(217),":","<input class='input' name='name' value='",h($K["name"]),"' data-maxlength='64' autocapitalize='off'>\n";if(support("materializedview"))echo
checkbox("materialized",1,$K["materialized"],lang(161));echo"</p>\n<p>";textarea("select",$K["select"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(113),"'>\n";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(160),"'>\n",confirm(lang(211,$a));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["event"])){$ea=$_GET["event"];$pf=["YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND"];$Ck=["ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE"];$K=$_POST;if($_POST){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($ea),substr(ME,0,-1),lang(238));elseif(in_array($K["INTERVAL_FIELD"],$pf)&&isset($Ck[$K["STATUS"]])){$Kj="\nON SCHEDULE ".($K["INTERVAL_VALUE"]?"EVERY ".q($K["INTERVAL_VALUE"])." $K[INTERVAL_FIELD]".($K["STARTS"]?" STARTS ".q($K["STARTS"]):"").($K["ENDS"]?" ENDS ".q($K["ENDS"]):""):"AT ".q($K["STARTS"]))." ON COMPLETION".($K["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($ea!=""?lang(239):lang(240)),(bool)queries(($ea!=""?"ALTER EVENT ".idf_escape($ea).$Kj.($ea!=$K["EVENT_NAME"]?"\nRENAME TO ".idf_escape($K["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($K["EVENT_NAME"]).$Kj)."\n".$Ck[$K["STATUS"]]." COMMENT ".q($K["EVENT_COMMENT"]).rtrim(" DO\n$K[EVENT_DEFINITION]",";").";"));}}if($ea!="")page_header(lang(241).": ".h($ea),[lang(241)]);else
page_header(lang(242),[lang(242)]);if(!$K&&$ea!=""){$L=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($ea));$K=reset($L);}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(217),"</th><td>","<input class='input' name='EVENT_NAME' value='",h($K["EVENT_NAME"]),"' data-maxlength='64' autocapitalize='off'>","</td></tr>\n","<tr><th title='datetime'>",lang(243),"</th><td>","<input class='input' name='STARTS' value='",h("$K[EXECUTE_AT]$K[STARTS]"),"'>","</td></tr>\n","<tr><th title='datetime'>",lang(244),"</th><td>","<input class='input' name='ENDS' value='",h($K["ENDS"]),"'>","</td></tr>\n","<tr><th>",lang(245),"</th><td>","<input type='number' name='INTERVAL_VALUE' value='",h($K["INTERVAL_VALUE"]),"' class='input size'> ",html_select("INTERVAL_FIELD",$pf,$K["INTERVAL_FIELD"]),"</td></tr>\n","<tr><th>",lang(152),"</th><td>",html_select("STATUS",$Ck,$K["STATUS"]),"</td></tr>\n","<tr><th>",lang(46),"</th><td>","<input class='input' name='EVENT_COMMENT' value='",h($K["EVENT_COMMENT"]),"' data-maxlength='64'>","</td></tr>\n","<tr><th></th><td>",checkbox("ON_COMPLETION","PRESERVE",$K["ON_COMPLETION"]=="PRESERVE",lang(246)),"</td></tr>\n","</table>\n","<p>";textarea("EVENT_DEFINITION",$K["EVENT_DEFINITION"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(113),"'>";if($ea!="")echo"<input type='submit' class='button' name='drop' value='",lang(160),"'>",confirm(lang(211,$ea));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["procedure"])){$oa=($_GET["name"]?:$_GET["procedure"]);$_j=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$K=$_POST;$K["fields"]=(array)$K["fields"];if($_POST&&!process_fields($K["fields"])){foreach($K["fields"]as$u=>$k){if($k["field"]=="")unset($K["fields"][$u]);}$yh=routine_id($oa,routine($_GET["procedure"],$_j));$hh=routine_id($K["name"],$K);$bc=create_routine($_j,$K);$y=substr(ME,0,-1);$Fg=lang(247);if(!$_POST["drop"]&&$yh==$hh&&(DIALECT!="sql"||Connection::get()->isMariaDB()))query_redirect(substr_replace($bc,' OR REPLACE',6,0),$y,$Fg);else{$nl="$K[name]_adminer_".uniqid();drop_create("DROP $_j $yh",$bc,"DROP $_j $hh",create_routine($_j,["name"=>$nl]+$K),"DROP $_j ".routine_id($nl,$K),$y,lang(248),$Fg,lang(249),$oa,$K["name"]);}}if($oa!=""){$T=isset($_GET["function"])?lang(250):lang(251);page_header($T.": ".h($oa),[$T]);}else{$T=isset($_GET["function"])?lang(252):lang(253);page_header($T,[$T]);}if(!$_POST){if($oa=="")$K["language"]="sql";else{$K=routine($_GET["procedure"],$_j);$K["name"]=$oa;}}$pb=get_vals("SHOW CHARACTER SET");sort($pb);$Aj=routine_languages();echo"<form action='' method='post' id='form'>\n","<p>",lang(217),": ","<input class='input' name='name' value='",h($K["name"]),"' data-maxlength='64' autocapitalize='off'>";if($Aj)echo"<span id='label-language'>",lang(9),":</span> ",html_select("language",$Aj,$K["language"],"","label-language");echo"<input type='submit' class='button default' value='",lang(113),"'>","</p>\n","<div class='scrollable'>\n","<table class='nowrap' id='edit-fields'>\n";edit_fields($K["fields"],$pb,$_j);if(isset($_GET["function"])){echo"<tbody><tr>";if(support("move_col"))echo"<th></th>";echo"<th>",lang(254),"</th>";edit_type("returns",(array)$K["returns"],$pb,[],(DIALECT=="pgsql"?["void","trigger"]:[]));echo"<td></td>","</tr></tbody>\n";}echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>";textarea("definition",$K["definition"],20);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(113),"'>";if($oa!="")echo"<input type='submit' class='button' name='drop' value='",lang(160),"'>",confirm(lang(211,$oa));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["check"])){$a=$_GET["check"];$A=$_GET["name"];$K=$_POST;if($K){if(DIALECT=="sqlite")$Jk=recreate_table($a,$a,[],[],[],"",[],"$A",($K["drop"]?"":$K["clause"]));else{$Jk=($A==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($A)));if(!$K["drop"])$Jk=(bool)queries("ALTER TABLE ".table($a)." ADD".($K["name"]!=""?" CONSTRAINT ".idf_escape($K["name"]):"")." CHECK ($K[clause])");}queries_redirect(ME."table=".urlencode($a),($K["drop"]?lang(255):($A!=""?lang(256):lang(257))),$Jk);}page_header(($A!=""?lang(258).": ".h($A):lang(173)),["table"=>$a]);if(!$K){$ub=Driver::get()->checkConstraints($a);$K=["name"=>$A,"clause"=>$ub[$A]];}echo"<form action='' method='post'>\n","<p>";if(DIALECT!="sqlite")echo
lang(217).': <input name="name" value="'.h($K["name"]).'" class="input" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(['sql'=>"create-table-check-constraints.html",'mariadb'=>"reference/sql-statements/data-definition/constraint",],"?"),"</p>\n<p>";textarea("clause",$K["clause"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(113),"'>";if($A!="")echo"<input type='submit' class='button' name='drop' value='",lang(160),"'>",confirm(lang(211,$A));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$A=isset($_GET["name"])?$_GET["name"]:"";$Jl=trigger_options();$K=trigger($A,$a)+["Trigger"=>$a."_bi"];if($_POST){if(in_array($_POST["Timing"],$Jl["Timing"])&&in_array($_POST["Event"],$Jl["Event"])&&in_array($_POST["Type"],$Jl["Type"])){$Ah=" ON ".table($a);$Sc="DROP TRIGGER ".idf_escape($A).(DIALECT=="pgsql"?$Ah:"");$y=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($Sc,$y,lang(259));else{if($A!="")queries($Sc);queries_redirect($y,($A!=""?lang(260):lang(261)),(bool)queries(create_trigger($Ah,$_POST)));if($A!="")queries(create_trigger($Ah,$K+["Type"=>reset($Jl["Type"])]));}}$K=$_POST;}if($A!="")page_header(lang(262).": ".h($A),["table"=>$a,h($A)]);else
page_header(lang(263),["table"=>$a,lang(263)]);echo"<form action='' method='post' id='form'>\n","<table class='box box-light'>\n","<tr><th id='label-time'>",lang(264),"</th><td>",html_select("Timing",$Jl["Timing"],$K["Timing"],"triggerChange(/^".js_escape_re($a)."_[ba][iud]$/, '".js_escape($a)."', this.form);","label-time"),"</td></tr>\n","<tr><th id='label-event'>",lang(265),"</th><td>",html_select("Event",$Jl["Event"],$K["Event"],"this.form['Timing'].onchange();","label-event");if(in_array("UPDATE OF",$Jl["Event"]))echo" <input name='Of' value='".h($K["Of"])."' class='input hidden'>";echo"</td></tr>\n","<tr><th id='label-type'>",lang(44),"</th><td>",html_select("Type",$Jl["Type"],$K["Type"],"","label-type"),"</td></tr>\n","</table>\n","<p>",lang(217),"<input class='input' name='Trigger' value='",h($K["Trigger"]),"' data-maxlength='64' autocapitalize='off'>","</p>\n",script("gid('form')['Timing'].onchange();"),"<p>";textarea("Statement",$K["Statement"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(113),"'>";if($A!="")echo"<input type='submit' class='button' name='drop' value='",lang(160),"'>",confirm(lang(211,$A));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["user"])){$qa=$_GET["user"];$Ri=[""=>["All privileges"=>""]];foreach(get_rows("SHOW PRIVILEGES")as$K){foreach(explode(",",($K["Privilege"]=="Grant option"?"":$K["Context"]))as$Xb)$Ri[$Xb=="File access on server"?"Server Admin":$Xb][$K["Privilege"]]=$K["Comment"];}unset($Ri["Server Admin"]["Usage"]);foreach($Ri["Tables"]as$u=>$X)unset($Ri["Databases"][$u]);$gh=[];if($_POST){foreach($_POST["objects"]as$u=>$X)$gh[$X]=(array)$gh[$X]+(array)$_POST["grants"][$u];}$ve=[];if(isset($_GET["host"])&&($I=Connection::get()->query("SHOW GRANTS FOR ".q($qa)."@".q($_GET["host"])))){while($K=$I->fetchRow()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$K[0],$z)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$z[1],$_,PREG_SET_ORDER)){foreach($_
as$X){if($X[1]!="USAGE")$ve["$z[2]$X[2]"][$X[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$K[0]))$ve["$z[2]$X[2]"]["GRANT OPTION"]=true;}}}}$zi=!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8");if($_POST){$_h=(isset($_GET["host"])?q($qa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $_h",ME."privileges=",lang(266));else{$jh=q($_POST["user"])."@".q($_POST["host"]);$si=$_POST["pass"];$ec=false;$I=true;if($_h!=$jh){$ec=(bool)queries("CREATE USER $jh IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($si));$I=$ec;}elseif($si!="")$I=(bool)queries("SET PASSWORD FOR $jh = ".($zi||$_POST["hashed"]?q($si):"PASSWORD(".q($si).")"));if($I){$xj=[];foreach($gh
as$rh=>$te){if(isset($_GET["grant"]))$te=array_filter($te);$te=array_keys($te);if(isset($_GET["grant"]))$xj=array_diff(array_keys(array_filter($gh[$rh],'strlen')),$te);elseif($_h==$jh){$xh=array_keys((array)$ve[$rh]);$xj=array_diff($xh,$te);$te=array_diff($te,$xh);unset($ve[$rh]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$rh,$z)&&(!grant(false,$xj,$z[2],$z[1],$jh)||!grant(true,$te,$z[2],$z[1],$jh))){$I=false;break;}}}if($I&&isset($_GET["host"])){if($_h!=$jh)queries("DROP USER $_h");elseif(!isset($_GET["grant"])){foreach($ve
as$rh=>$xj){if(preg_match('~^(.+)(\(.*\))?$~U',$rh,$z))grant(false,array_keys($xj),$z[2],$z[1],$jh);}}}queries_redirect(ME."privileges=",(isset($_GET["host"])?lang(267):lang(268)),$I);if($ec)Connection::get()->query("DROP USER $jh");}}$T=isset($_GET["host"])?lang(28).": ".h("$qa@$_GET[host]"):lang(183);$yl=isset($_GET["host"])?h($qa):lang(183);page_header($T,["privileges"=>['',lang(72)],$yl]);if($_POST){$K=$_POST;$ve=$gh;}else{$K=$_GET+["host"=>Connection::get()->getValue("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")];if($ve)$ve[".*"]=[];elseif(DB!="")$ve[idf_escape(addcslashes(DB,"%_\\")).".*"]=[];else$ve["*.* "]=[];}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(5),"</th>","<td><input class='input' name='host' data-maxlength='60' value='",h($K["host"]),"' autocapitalize='off'></td>\n","<tr><th>",lang(28),"</th>","<td><input class='input' name='user' data-maxlength='80' value='",h($K["user"]),"' autocapitalize='off'></td>\n",'<tr><th>',lang(29),"</th>","<td><input class='input' name='pass' id='pass' value='",h($K["pass"]),"' autocomplete='new-password'>";if(!$zi)echo
checkbox("hashed",1,$K["hashed"],lang(269),"typePassword(this.form['pass'], this.checked);");echo"</td>\n";if(!$K["hashed"])echo
script("typePassword(gid('pass'));");echo"</table>\n","<div class='scrollable'><table class='checkable'>\n","<thead><tr><th colspan='2'>".lang(72).doc_link(['sql'=>"grant.html#priv_level","mariadb"=>"reference/sql-statements/account-management-sql-statements/grant#privilege-levels"])."</th>";$q=0;foreach($ve
as$rh=>$te){echo"<th>";if($rh=="*.*")echo"*.*",input_hidden("objects[$q]","*.*");else
echo"<input class='input' name='objects[$q]' value='".h(trim($rh))."' size='10' autocapitalize='off'>";echo"</th>";$q++;}echo"</tr></thead>\n";foreach([""=>"","Server Admin"=>lang(5),"Databases"=>lang(30),"Tables"=>lang(8),"Procedures"=>lang(270),]as$Xb=>$_c){foreach((array)$Ri[$Xb]as$Qi=>$Kb){echo"<tr>";if($_c)echo"<td>$_c</td>";echo"<td".(!$_c?" colspan='2'":"").' lang="en" title="'.h($Kb).'">'.h($Qi)."</td>";$q=0;foreach($ve
as$rh=>$te){$A="'grants[$q][".h(strtoupper($Qi))."]'";$Y=$te[strtoupper($Qi)];$Vi=strpos($rh,"@")!==false;$fh=$rh==".*";$Ca=$Qi=="All privileges";$ue=$Qi=="Grant option";if($rh=="*.*"&&$Qi=="Proxy")echo"<td></td>";elseif($Vi&&$Qi!="Proxy"&&!$ue)echo"<td></td>";elseif($Xb=="Server Admin"&&$rh!=(isset($ve["*.*"])?"*.*":".*")&&!(($Vi||$fh)&&$Qi=="Proxy"))echo"<td></td>";elseif(isset($_GET["grant"]))echo"<td><select name=$A>"."<option></option>"."<option value='1'".($Y?" selected":"").">".lang(271)."</option>"."<option value='0'".($Y=="0"?" selected":"").">".lang(272)."</option>"."</select></td>";else{echo"<td class='center'><label class='block'>","<input type='checkbox' name=$A value='1'".($Y?" checked":"").($Ca?" id='grants-$q-all'":(!$ue?" class='grants-$q'":"")).">";if($Ca)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheckAll('.grants-$q'); };");elseif(!$ue)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$q-all'); };");echo"</label>";}$q++;}echo"</tr>";}}echo"</table></div>\n","<p>","<input type='submit' class='button default' value='",lang(113),"'>\n";if(isset($_GET["host"]))echo"<input type='submit' class='button' name='drop' value='",lang(160),"'>\n",confirm(lang(211,"$qa@$_GET[host]"));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST){$Lf=0;foreach((array)$_POST["kill"]as$X){if(kill_process($X))$Lf++;}queries_redirect(ME."processlist=",lang(273,$Lf),$Lf||!$_POST["kill"]);}}page_header(lang(150),[lang(150)]);echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap checkable'>\n";$q=-1;foreach(process_list()as$q=>$K){if(!$q){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($K
as$u=>$X)echo"<th>$u".doc_link(['sql'=>"show-processlist.html#processlist_".strtolower($u),'mariadb'=>"reference/sql-statements/administrative-sql-statements/show/show-processlist",]);echo"</thead>\n","<tbody>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$K[DIALECT=="sql"?"Id":"pid"],0):"");foreach($K
as$u=>$X)echo"<td>".($X!=""&&((DIALECT=="sql"&&$u=="Info"&&preg_match("~Query|Killed~",$K["Command"]))||(DIALECT=="pgsql"&&$u=="query")||(DIALECT=="oracle"&&$u=="sql_text"))?"<code class='jush-".DIALECT."'>".truncate_utf8($X,100).'</code> <a href="'.h(ME.($K["db"]!=""?"db=".urlencode($K["db"])."&":"")."sql=".urlencode($X)).'">'.icon("edit").lang(274).'</a>':h($X));echo"\n";}if($q>=0)echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");echo"</table>\n","</div>\n","<p>";if(support("kill"))echo($q+1)."/".lang(275,max_connections()),"<p><input type='submit' class='button' value='".lang(276)."'>\n";echo
input_token(),"</p>\n","</form>\n",script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$R=table_status1($a);$t=indexes($a);$l=fields($a);$de=column_foreign_keys($a);$th=$R["Oid"];$yj=[];$c=[];$Qj=[];$Oh=[];$rl=null;foreach($l
as$u=>$k){$A=Admin::get()->getFieldName($k);$bh=html_entity_decode(strip_tags($A),ENT_QUOTES);if(isset($k["privileges"]["select"])&&$A!=""){$c[$u]=$bh;if(is_shortable($k))$rl=Admin::get()->processSelectionLength();}if(isset($k["privileges"]["where"])&&$A!="")$Qj[$u]=$bh;if(isset($k["privileges"]["order"])&&$A!="")$Oh[$u]=$bh;$yj+=$k["privileges"];}list($M,$we)=Admin::get()->processSelectionColumns($c,$t);$M=array_unique($M);$we=array_unique($we);$vf=count($we)<count($M);$Z=Admin::get()->processSelectionSearch($l,$t);$D=Admin::get()->processSelectionOrder($l,$t);$w=Admin::get()->processSelectionLimit();if($_GET["modify"]&&!Admin::get()->isDataEditAllowed())redirect(ME."select=".urlencode($a));if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Ul=>$K){$La=convert_field($l[key($K)]);$M=[$La?:idf_escape(key($K))];$Z[]=where_check($Ul,$l);$J=Driver::get()->select($a,$M,$Z,$M);if($J)echo
first($J->fetchRow());}exit;}$Ni=$Xl=[];foreach($t
as$s){if($s["type"]=="PRIMARY"){$Ni=array_flip($s["columns"]);$Xl=($M?$Ni:[]);foreach($Xl
as$u=>$X){if(in_array(idf_escape($u),$M))unset($Xl[$u]);}break;}}if($th&&!$Ni){$Ni=$Xl=[$th=>0];$t[]=["type"=>"PRIMARY","columns"=>[$th]];}$O=Admin::get()->getSettings();if($_POST){$Em=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$ub=[];foreach($_POST["check"]as$qb)$ub[]=where_check($qb,$l);$Em[]="((".implode(") OR (",$ub)."))";}$Em=($Em?"\nWHERE ".implode(" AND ",$Em):"");if($_POST["export"]){$O->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers($a);Admin::get()->dumpTable($a,"");$le=($M?implode(", ",$M):"*").convert_fields($c,$l,$M)."\nFROM ".table($a);$ze=($we&&$vf?"\nGROUP BY ".implode(", ",$we):"").($D?"\nORDER BY ".implode(", ",$D):"");if(!is_array($_POST["check"])||$Ni)$H="SELECT $le$Em$ze";else{$Rl=[];foreach($_POST["check"]as$X)$Rl[]="(SELECT".limit($le,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$l).$ze,1).")";$H=implode(" UNION ALL ",$Rl);}Admin::get()->dumpData($a,"table",$H);exit;}if($_POST["save"]||$_POST["delete"]){$I=true;$za=0;$kk=[];if(!$_POST["delete"]){$Yj=array_keys($_POST["fields"]+$_POST["function"]);foreach($Yj
as$A){$X=process_input($l[$A]);if($X!==null&&($_POST["clone"]||$X!==false))$kk[idf_escape($A)]=($X!==false?$X:idf_escape($A));}}if($_POST["delete"]||$kk){if($_POST["clone"])$H="INTO ".table($a)." (".implode(", ",array_keys($kk)).")\nSELECT ".implode(", ",$kk)."\nFROM ".table($a);if($_POST["all"]||($Ni&&is_array($_POST["check"]))||$vf){$I=($_POST["delete"]?Driver::get()->delete($a,$Em):($_POST["clone"]?queries("INSERT $H$Em".Driver::get()->getInsertReturningSql($a)):Driver::get()->update($a,$kk,$Em)));$za=Connection::get()->getAffectedRows();if(is_object($I))$za+=$I->getRowsCount();}else{foreach((array)$_POST["check"]as$X){$Dm="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$l);$I=($_POST["delete"]?Driver::get()->delete($a,$Dm,1):($_POST["clone"]?queries("INSERT".limit1($a,$H,$Dm)):Driver::get()->update($a,$kk,$Dm,1)));if(!$I)break;$za+=Connection::get()->getAffectedRows();}}}$Fg=lang(277,$za);if($_POST["clone"]&&$I&&$za==1){$Uf=last_id($I);if($Uf)$Fg=lang(205," $Uf");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$Fg,(bool)$I);if(!$_POST["delete"]){$dd=array_filter($l,function($k){return!(isset($k["generated"])?$k["generated"]:null);});edit_form($a,$dd,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])Admin::get()->addError(lang(278));else{$Jk=true;$za=0;foreach($_POST["val"]as$Ul=>$K){$kk=[];foreach($K
as$u=>$X){$u=bracket_escape($u,true);$kk[idf_escape($u)]=(preg_match('~char|text~',$l[$u]["type"])||$X!=""?Admin::get()->processFieldInput($l[$u],$X):"NULL");}$Jk=(bool)Driver::get()->update($a,$kk," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($Ul,$l),($vf||$Ni?0:1)," ");if(!$Jk)break;$za+=Connection::get()->getAffectedRows();}queries_redirect(remove_from_uri(),lang(277,$za),$Jk);}}elseif(!is_string($m=get_file("csv_file",true)))Admin::get()->addError(upload_error($m));elseif(!preg_match('~~u',$m))Admin::get()->addError(lang(279));else{$O->updateParameter("exportFormat",$_POST["import_format"]);$Fb=array_keys($l);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$m,$_);$za=count($_[0]);Driver::get()->begin();$Zj=($_POST["import_format"]=="csv;"?";":($_POST["import_format"]=="tsv"?"\t":","));$L=[];foreach($_[0]as$u=>$X){preg_match_all("~((?>\"[^\"]*\")+|[^$Zj]*)$Zj~",$X.$Zj,$tg);if(!$u&&!array_diff($tg[1],$Fb)){$Fb=$tg[1];$za--;}else{$kk=[];foreach($tg[1]as$q=>$_b)$kk[idf_escape($Fb[$q])]=($_b==""&&$l[$Fb[$q]]["null"]?"NULL":q(preg_match('~^".*"$~s',$_b)?str_replace('""','"',substr($_b,1,-1)):$_b));$L[]=$kk;}}$Jk=!$L||Driver::get()->insertUpdate($a,$L,$Ni);if($Jk)Driver::get()->commit();queries_redirect(remove_from_uri("page"),lang(280,$za),$Jk);Driver::get()->rollback();}}$Zk=Admin::get()->getTableName($R);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(55).": $Zk",[$Zk]);$mf=null;if(isset($yj["insert"])||!support("table")){$mf=[];foreach((array)$_GET["where"]as$X){if(isset($de[$X["col"]])&&count($de[$X["col"]])==1&&($X["op"]=="="||(!$X["op"]&&(is_array($X["val"])||!preg_match('~[_%]~',$X["val"])))))$mf["preset"."[".bracket_escape($X["col"])."]"]=$X["val"];}}Admin::get()->printTableMenu($R,$mf);if(!$c&&support("table"))echo"<p class='error'>".lang(281).($l?".":": ".error())."\n";else{echo"<form id='form' action=''>\n","<div hidden>";hidden_fields_get();if(DB!=""){echo
input_hidden("db",DB);if(isset($_GET["ns"]))echo
input_hidden("ns",$_GET["ns"]);}echo
input_hidden("select",$a),"<input type='submit' class='button' value='".lang(55)."'>","</div>\n","<div class='field-sets'>\n";Admin::get()->printSelectionColumns($M,$c);Admin::get()->printSelectionSearch($Z,$Qj,$t);Admin::get()->printSelectionOrder($D,$Oh,$t);Admin::get()->printSelectionLimit($w);Admin::get()->printSelectionLength($rl);Admin::get()->printSelectionAction($t);echo"</div>\n</form>\n";$E=isset($_GET["page"])?$_GET["page"]:null;if($E=="last"){$je=Connection::get()->getValue(count_rows($a,$Z,$vf,$we));$E=(int)floor(max(0,intval($je)-1)/$w);}else{$je=false;$E=(int)$E;}$Rj=$M;$xe=$we;if(!$Rj){$Rj[]="*";$Yb=convert_fields($c,$l,$M);if($Yb)$Rj[]=substr($Yb,2);}foreach($M
as$u=>$X){$k=$l[idf_unescape($X)];if($k&&($La=convert_field($k)))$Rj[$u]="$La AS $X";}if(DIALECT=="pgsql"||DIALECT=="mssql"){foreach((array)$_GET["columns"]as$u=>$X){if(isset($Rj[$u])&&$X["fun"])$Rj[$u].=" AS ".idf_escape(apply_sql_function($X["fun"],($X["col"]!=""?$X["col"]:"*")));}}if(!$vf&&$Xl){foreach($Xl
as$u=>$X){$Rj[]=idf_escape($u);if($xe)$xe[]=idf_escape($u);}}$I=Driver::get()->select($a,$Rj,$Z,$xe,$D,$w,$E,true);if(!$I)echo"<p class='error'>".error()."\n";else{if(DIALECT=="mssql"&&$E)$I->seek($w*$E);echo"<form id='selection_form' action='' method='post' enctype='multipart/form-data'>\n","<div class='table-footer-parent'>\n";$L=[];while($K=$I->fetchAssoc()){if($E&&DIALECT=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["page"]!="last"&&$w&&$we&&$vf&&DIALECT=="sql")$je=Connection::get()->getValue(" SELECT FOUND_ROWS()");$ed=false;if(!$L)echo"<p class='message'>".lang(89)."\n";else{$Va=Admin::get()->getBackwardKeys($a,$Zk);echo"<div class='scrollable'>\n","<table id='table' class='nowrap checkable'>\n","<thead><tr>";if($we||!$M){echo"<th class='actions'><input type='checkbox' id='all-page' class='jsonly'>".script("gid('all-page').onclick = partial(formCheck, /check/);","");if(Admin::get()->isDataEditAllowed())echo" <a href='",h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."' title='",lang(282),"'>",icon_solo("edit-all"),"</a>";}$ch=[];$oe=[];reset($M);$dj=1;foreach($L[0]as$u=>$X){if(!isset($Xl[$u])){$Tj=key($M);$X=isset($_GET["columns"][$Tj])?$_GET["columns"][$Tj]:[];$k=$l[$M?($X?$X["col"]:current($M)):$u];$A=($k?Admin::get()->getFieldName($k,$dj):(isset($X["fun"])?"*":h($u)));if($A!=""){$dj++;$ch[$u]=$A;$b=idf_escape($u);$Qe=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($u);$_c="&desc%5B0%5D=1";echo"<th id='th[".h(bracket_escape($u))."]'>";$ne=apply_sql_function(isset($X["fun"])?$X["fun"]:null,$A);$tk=isset($k["privileges"]["order"])||(isset($X["fun"])?$X["fun"]:null);if($tk)echo'<a href="',h($Qe.($D[0]==$b||$D[0]==$u?$_c:'')),'">',"$ne</a>";else
echo$ne;echo"<span class='column'>";if($tk)echo"<a href='".h($Qe.$_c)."' title='".lang(62)."' class='button light'>",icon_solo("arrow-down"),"</a>";if(!isset($X["fun"])&&isset($k["privileges"]["where"]))echo"<a href='#fieldset-search' title='".lang(59)."' class='button light jsonly'>",icon_solo("search"),"</a>",script("qsl('a').onclick = partial(selectSearch, '".js_escape($u)."');");echo"</span>";}$oe[$u]=isset($X["fun"])?$X["fun"]:null;next($M);}}$cg=[];if($_GET["modify"]){foreach($L
as$K){foreach($K
as$u=>$X)$cg[$u]=max($cg[$u],min(40,strlen(utf8_decode($X))));}}if($Va)echo"<th>".lang(17)."</th>";echo"</thead>\n","<tbody>\n";if(is_ajax())ob_end_clean();foreach(Admin::get()->fillForeignDescriptions($L,$de)as$Zg=>$K){$Tl=unique_array($L[$Zg],$t);if(!$Tl){$Tl=[];reset($M);foreach($L[$Zg]as$u=>$X){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($M)))$Tl[$u]=$X;next($M);}}$Ul="";foreach($Tl
as$u=>$X){$k=isset($l[$u])?$l[$u]:null;if((DIALECT=="sql"||DIALECT=="pgsql")&&$k&&preg_match('~char|text|enum|set~',$k["type"])&&strlen($X)>64){$u=(strpos($u,'(')?$u:idf_escape($u));$u="MD5(".(DIALECT!='sql'||preg_match("~^utf8~",isset($k["collation"])?$k["collation"]:"")?$u:"CONVERT($u USING ".charset(Connection::get()).")").")";$X=md5($X);}$Ul
.="&".($X!==null?urlencode("where[".bracket_escape($u)."]")."=".urlencode($X===false?"f":$X):"null%5B%5D=".urlencode($u));}echo"<tr>";if($we||!$M){echo"<td class='actions'>",checkbox("check[]",substr($Ul,1),in_array(substr($Ul,1),(array)$_POST["check"]));if(!$vf&&Admin::get()->isDataEditAllowed())echo" <a href='",h(ME."edit=".urlencode($a).$Ul),"' class='edit' title='",lang(38),"'>",icon_solo("edit"),"</a>";}reset($M);foreach($K
as$u=>$X){if(isset($ch[$u])){$b=current($M);$k=isset($l[$u])?$l[$u]:null;$x="";if($k&&is_blob($k)&&$X!="")$x=ME.'download='.urlencode($a).'&field='.urlencode($u).$Ul;if(!$x&&$X!==null){foreach((array)$de[$u]as$o){if(count($de[$u])==1||end($o["source"])==$u){$x="";foreach($o["source"]as$q=>$uk)$x
.=where_link($q,$o["target"][$q],$L[$Zg][$uk]);$x=($o["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($o["db"]),ME):ME).'select='.urlencode($o["table"]).$x;if($o["ns"])$x=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($o["ns"]),$x);if(count($o["source"])==1)break;}}}if($b=="COUNT(*)"){$x=ME."select=".urlencode($a);$q=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$Tl))$x
.=where_link($q++,$W["col"],$W["val"],$W["op"]);}foreach($Tl
as$Df=>$W)$x
.=where_link($q++,$Df,$W);}$oh=$X===null;$Re=select_value($X,$x,$k,$rl);$rd=bracket_escape($u);$r=h("val[$Ul][$rd]");$Ii=isset($_POST["val"][$Ul][$rd])?$_POST["val"][$Ul][$rd]:null;$Zl=isset($k["privileges"]["update"])?$k["privileges"]["update"]:false;$cd=!is_array($K[$u])&&is_utf8($Re)&&$L[$Zg][$u]==$K[$u]&&!$oe[$u]&&!(isset($k["generated"])?$k["generated"]:false);$U=($b&&preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$b,$_)?$l[idf_unescape($_[2])]["type"]:(isset($k["type"])?$k["type"]:null));$Tg=$U=="money"||($b&&preg_match('~^SUM\((.+)\)~',$b,$_)&&$l[idf_unescape($_[1])]["type"])=="money";$pl=$U&&preg_match('~text|json|lob~',$U);$qh=($U&&preg_match(number_type(),$U))||($b&&preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|UNIX_TIMESTAMP|TIME_TO_SEC|COUNT|SUM)\(~',$b));$yb=$qh&&($oh||is_numeric(strip_tags($Re))||$Tg)?"class='number'":"";echo"<td id='$r' $yb";if(($_GET["modify"]&&$cd&&!$oh)||$Ii!==null){$ed=true;$Be=h($Ii!==null?$Ii:$K[$u]);echo" data-editing='true'>".($pl?"<textarea name='$r' cols='30' rows='".(substr_count($K[$u],"\n")+1)."'>$Be</textarea>":"<input class='input' name='$r' value='$Be' size='$cg[$u]'>");}else{$qg=strpos($Re,"<i>…</i>");if($Zl)echo" data-text='".($qg?2:($pl?1:0))."'".($cd?"":" data-warning='".lang(283)."'");echo">$Re";}}next($M);}if($Va){echo"<td>";Admin::get()->printBackwardKeys($Va,$L[$Zg]);echo"</td>";}echo"</tr>\n";}if(is_ajax())exit;echo"</tbody>\n",script("mixin(qs('#table tbody'), {onclick: partialArg(tableClick, false, ".(Admin::get()->isDataEditAllowed()?"true":"false")."), ondblclick: partialArg(tableClick, true), onkeydown: onEditingKeydown});"),"</table>\n",script("initToggles(gid('table'));"),"</div>\n";}if(!is_ajax()){if($L||$E){$td=true;if($_GET["page"]!="last"){if(!$w||(count($L)<$w&&($L||!$E)))$je=($E?$E*$w:0)+count($L);elseif(DIALECT!="sql"||!$vf){$je=($vf?false:found_rows($R,$Z));if($je<max(1e4,2*($E+1)*$w))$je=first(slow_query(count_rows($a,$Z,$vf,$we)));elseif(DIALECT=='sql'||DIALECT=='pgsql')$td=false;}}$ei=($w!==null&&($je===false||$je>$w||$E));if($ei){if(($je===false?count($L)+1:$je-$E*$w)>$w)echo'<p class="links">','<a href="',h(remove_from_uri("page")."&page=".($E+1)),'" class="loadmore">',icon("expand"),lang(284),'</a>',script("qsl('a').onclick = partial(loadNextPage, $w, '".js_escape(lang(285))."…');","");echo"\n";}echo"<div class='table-footer'><div class='field-sets'>\n";if($ei){$xg=($je===false?$E+(count($L)>=$w?2:1):(int)floor(($je-1)/$w));$Oc="<li>…</li>";echo"<fieldset>";if(DIALECT!="simpledb"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".lang(286)."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".js_escape(lang(286))."', '".($E+1)."')); return false; };"),"<div id='fieldset-pagination' class='fieldset-content'><ul class='pagination'>",pagination(0,$E);if($E>5)echo$Oc;for($q=max(1,$E-4);$q<min($xg,$E+5);$q++)echo
pagination($q,$E);if($xg>0){if($E+5<$xg)echo$Oc;echo($td&&$je!==false?pagination($xg,$E):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$xg'>".lang(287)."</a>");}echo"</ul></div>";}else{echo"<legend>".lang(286)."</legend>","<div id='fieldset-pagination'><ul class='pagination'>",pagination(0,$E);if($E>1)echo$Oc;if($E)echo
pagination($E,$E);if($xg>$E){echo
pagination($E+1,$E);if($xg>$E+1)echo$Oc;}echo"</ul></div>";}echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(288)."</legend><div class='fieldset-content'>";$Hc=($td?"":"~ ").$je;echo
checkbox("all",1,0,($je!==false?($td?"":"~ ").lang(187,$je):""),"const checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$Hc' : checked); selectCount('selected2', this.checked || !checked ? '$Hc' : checked);")."\n","</div></fieldset>\n";if(Admin::get()->isDataEditAllowed()){echo"<fieldset",($_GET["modify"]?'':' class="jsonly"'),">","<legend>",lang(282),"</legend>";$Fj=($_GET["modify"]?"":" data-inline-edit='1'".($ed?"":" disabled"));echo"<div class='fieldset-content'",($_GET["modify"]?"":" title='".lang(278)."'"),">","<input type='submit' class='button' id='modify-save' value='",lang(113),"'",$Fj,">","</div>","</fieldset>\n","<fieldset>","<legend>",lang(159)," <span id='selected'></span></legend>","<div class='fieldset-content'>","<input type='submit' class='button' name='edit' value='",lang(38),"'> ","<input type='submit' class='button' name='clone' value='",lang(274),"'> ","<input type='submit' class='button' name='delete' value='",lang(117),"'>",confirm(),"</div>","</fieldset>\n";}$fe=Admin::get()->getDumpFormats();foreach((array)$_GET["columns"]as$b){if($b["fun"]){unset($fe['sql']);break;}}if($fe){print_fieldset_start("export",lang(74)." <span id='selected2'></span>","export");echo
html_select("format",$fe,$O->getParameter("exportFormat"));$bi=Admin::get()->getDumpOutputs();echo($bi?" ".html_select("output",$bi,$O->getParameter("exportOutput")):"")," <input type='submit' class='button' name='export' value='".lang(74)."'>\n";print_fieldset_end("export");}echo"</div></div>\n",script("initTableFooter()");}echo"</div>\n";if(Admin::get()->isDataEditAllowed()){echo"<p>","<a href='#import'>",icon("import"),lang(73),"</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"</p>","<p id='import'",($_POST["import"]?"":" class='hidden'"),">";if(ini_bool("file_uploads"))echo"<input type='file' name='csv_file'> ",html_select("import_format",["csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"],$O->getParameter("exportFormat"))," <input type='submit' class='button default' name='import' value='".lang(73)."'>",file_upload_form_script("selection_form","csv_file");else
echo
lang(194);echo"</p>";}echo
input_token(),"</form>\n",(!$we&&$M?"":script("tableCheck();"));}else
echo"</div>\n";}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$P=isset($_GET["status"]);$T=$P?lang(152):lang(151);page_header($T,[$T]);$om=($P?Admin::get()->getStatusVariables():Admin::get()->getServerVariables());if(!$om)echo"<p class='message'>",lang(89),"</p>\n";else{echo"<div class='scrollable'><table>\n";foreach($om
as$K){echo"<tr>";$u=array_shift($K);echo"<th><code class='jush-".DIALECT.($P?"status":"set")."'>".h($u)."</code></th>";foreach($K
as$X)echo"<td>",nl2br(h($X)),"</td>";echo"</tr>\n";}echo"</table></div>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$Mk=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$f=[];$oc=null;foreach(table_status()as$A=>$R){$f["Comment-$A"]=h($R["Comment"]);if(!is_view($R)||preg_match('~materialized~i',$R["Engine"])){$f["Engine-$A"]=h($R["Engine"]);$Bb=isset($R["Collation"])?$R["Collation"]:"";if($Bb==""){if($oc===null)$oc=db_collation(DB,collations())??"";$Bb=$oc;}$f["Collation-$A"]=h($Bb);foreach($Mk+["Auto_increment"=>0,"Rows"=>0]as$u=>$X){if($R[$u]!=""){$X=format_number($R[$u]);if($X>=0)$f["$u-$A"]=($u=="Rows"?format_rows($R):$X);if(isset($Mk[$u]))$Mk[$u]+=($R["Engine"]!="InnoDB"||$u!="Data_free"?$R[$u]:0);}elseif(array_key_exists($u,$R))$f["$u-$A"]="?";}}}if(function_exists('AdminNeo\db_status'))$Mk=db_status();foreach($Mk
as$u=>$X)$f["sum-$u"]=format_number($X);echo
json_encode($f,JSON_UNESCAPED_UNICODE);}elseif($_GET["script"]=="kill")Connection::get()->query("KILL ".number($_POST["kill"]));else{$f=[];foreach(count_tables(Admin::get()->getDatabases())as$h=>$X){$f["tables-$h"]=$X;$f["size-$h"]=db_size($h);}echo
json_encode($f,JSON_UNESCAPED_UNICODE);}exit;}else{$il=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($il&&!$_POST["search"]){$I=true;$Fg="";if(DIALECT=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]||$_POST["truncate_cascade"]){if($_POST["tables"])$I=truncate_tables($_POST["tables"],(bool)$_POST["truncate_cascade"]);$Fg=lang(289);}elseif($_POST["move"]){$I=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Fg=lang(290);}elseif($_POST["copy"]){$I=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Fg=lang(291);}elseif($_POST["drop"]){if($_POST["views"])$I=drop_views($_POST["views"]);if($I&&$_POST["tables"])$I=drop_tables($_POST["tables"]);$Fg=lang(292);}elseif(DIALECT=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$Q){foreach(get_rows("PRAGMA integrity_check(".q($Q).")")as$K)$Fg
.="<b>".h($Q)."</b>: ".h($K["integrity_check"])."<br>";}}elseif(DIALECT!="sql"){$I=(DIALECT=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),$_POST["tables"]));$Fg=lang(293);}elseif(!$_POST["tables"])$Fg=lang(78);elseif($I=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('AdminNeo\idf_escape',$_POST["tables"])))){while($K=$I->fetchAssoc())$Fg
.="<b>".h($K["Table"])."</b>: ".h($K["Msg_text"])."<br>";}queries_redirect($_SERVER["REQUEST_URI"],$Fg,(bool)$I);}if($_GET["ns"]=="")page_header(lang(30).": ".h(DB),true);else
page_header(lang(182).": ".h($_GET["ns"]),true);Admin::get()->printDatabaseMenu();if($_GET["ns"]===""){echo"<h2 id='schemas'>".lang(294)."</h2>\n";$Mj=Admin::get()->getSchemas();if(!$Mj)echo"<p class='message'>".lang(295)."\n";else{echo"<div class='scrollable'>\n","<table class='nowrap'>\n",'<thead><tr class="wrap"><th>',lang(182),"</th></tr></thead>";foreach($Mj
as$A)echo"<tr><th><a href='",h(ME),"ns=".urlencode($A),"' title='",lang(296),"'>".h($A)."</a></th></tr>";echo'</table></div>';}echo'<p class="links"><a href="'.h(ME).'scheme=">'.icon("database-add").lang(76)."</a>\n";}else{echo"<h2 id='tables-views'>".lang(297)."</h2>\n";$dl=['sql'=>'show-table-status.html','mariadb'=>'reference/sql-statements/administrative-sql-statements/show/show-table-status'];$oc=db_collation(DB,collations());$c=["Engine"=>["label"=>lang(163),"doc"=>doc_link(['sql'=>'storage-engines.html','mariadb'=>'server-usage/storage-engines']),],];if($oc!="")$c["Collation"]=["label"=>lang(45),"doc"=>doc_link(['sql'=>'charset-charsets.html','mariadb'=>'reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations']),];$c+=["Data_length"=>["label"=>lang(298),"doc"=>doc_link($dl+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286']),"link"=>"create","title"=>lang(35),],"Index_length"=>["label"=>lang(299),"doc"=>doc_link($dl+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT']),"link"=>"indexes","title"=>lang(167),],"Data_free"=>["label"=>lang(300),"doc"=>doc_link($dl),"link"=>"edit","title"=>lang(7),],"Auto_increment"=>["label"=>lang(47),"doc"=>doc_link(['sql'=>'example-auto-increment.html','mariadb'=>'reference/data-types/auto_increment']),"link"=>"auto_increment=1&create","title"=>lang(35),],"Rows"=>["label"=>lang(301),"doc"=>doc_link($dl+['pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286']),"link"=>"select","title"=>lang(33),],];if(support("comment"))$c["Comment"]=["label"=>lang(46),"doc"=>doc_link($dl+['pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE']),];$D=(is_string($_GET["order"])?$_GET["order"]:"");$Ac=null;if(preg_match('~^(.+)-(asc|desc)$~',$D,$z)){$D=$z[1];$Ac=($z[2]=="desc");}if($D!="__table"&&!isset($c[$D]))$D="";if($Ac===null)$Ac=isset($c[$D]["link"]);$Gm=($D!=""&&$D!="__table")||support("fast_status");$gl=($Gm?table_status():tables_list());if(!$gl)echo"<p class='message'>".lang(78)."\n";else{echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n";if(support("table")){echo"<div class='field-sets'>\n","<fieldset><legend>".lang(302)." <span id='selected2'></span></legend><div class='fieldset-content'>",html_select("op",Admin::get()->getOperators(),isset($_POST["op"])?$_POST["op"]:Driver::get()->getLikeOperator()),"<input type='search' class='input' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');","")," <input type='submit' class='button' name='search' value='".lang(59)."'>\n","</div></fieldset>\n","</div>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable'>\n",'<thead><tr class="wrap">','<td class="actions"><input id="check-all" type="checkbox" class="input jsonly">'.script("gid('check-all').onclick = partial(formCheck, /^(tables|views)\[/);","");$ah=($D==""||$D=="__table");$Yk=($ah&&!$Ac?ME."order=__table-desc":substr(ME,0,-1));echo'<th><a href="'.h($Yk).'">'.lang(8).'</a>';foreach($c
as$u=>$b){$Cc=($u===$D?!$Ac:isset($b["link"]));echo'<td><a href="'.h(ME)."order=$u-".($Cc?"desc":"asc").'">'.$b["label"].'</a>'.$b["doc"];}echo"</thead>\n","<tbody>\n";if($D=="__table"){if($Ac)$gl=array_reverse($gl,true);}elseif($D){uasort($gl,function($sa,$Sa)use($D,$Ac){$Hm=isset($sa[$D])?$sa[$D]:null;$Im=isset($Sa[$D])?$Sa[$D]:null;$I=($Hm<$Im?-1:($Hm>$Im?1:0));return($Ac?-$I:$I);});}$Mk=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$S=0;foreach($gl
as$A=>$P){$sm=($Gm?is_view($P):$P!==null&&!preg_match('~table|sequence~i',$P));$kd=($Gm?(isset($P["Engine"])?$P["Engine"]:""):$P);$r=h("Table-".$A);echo'<tr><td class="actions">'.checkbox(($sm?"views[]":"tables[]"),$A,in_array("$A",$il,true),"","","",$r);if(!Admin::get()->getSettings()->isSelectionPreferred()&&(support("table")||support("indexes")))$ua="table";else$ua="select";echo"<th><a href='",h(ME),"$ua=",urlencode($A),"' id='$r'>",h($A),"</a></th>";if($sm&&!preg_match('~materialized~i',$kd)){$T=lang(162);$Gb=count($c)-(support("comment")?2:1);echo'<td colspan="'.$Gb.'">'.(support("view")?"<a href='".h(ME)."view=".urlencode($A)."' title='".lang(36)."'>$T</a>":$T),"<td align='right'><a href='".h(ME)."select=".urlencode($A)."' title='".lang(33)."'>?</a>";}else{foreach($c
as$u=>$b){if($u=="Comment")continue;$r=" id='$u-".h($A)."'";$x=isset($b["link"])?$b["link"]:"";if(!$x){$X="";if($Gm){$X=isset($P[$u])?$P[$u]:"";if($u=="Collation"&&$X=="")$X=$oc;}echo"<td$r>".h($X);continue;}$X="?";if($Gm){$B=isset($P[$u])?$P[$u]:"";if(is_numeric($B)&&$B>=0){$X=($u=="Rows"?format_rows($P):format_number($B));if(isset($Mk[$u])&&($kd!="InnoDB"||$u!="Data_free"))$Mk[$u]+=$B;}}echo"<td align='right'>".(support("table")||$u=="Rows"||(support("indexes")&&$u!="Data_length")?"<a href='".h(ME."$x=").urlencode($A)."'$r title='".$b["title"]."'>".h($X)."</a>":"<span$r>".h($X)."</span>");}$S++;}echo(support("comment")?"<td id='Comment-".h($A)."'>".($Gm?h(isset($P["Comment"])?$P["Comment"]:""):""):""),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"<tfoot><tr>","<td><th>".lang(275,count($gl)),"<td>".h(DIALECT=="sql"?Connection::get()->getValue("SELECT @@default_storage_engine"):""),($oc!=""?"<td>".h($oc):"");if($Gm&&function_exists('AdminNeo\db_status'))$Mk=db_status();foreach($Mk
as$u=>$Lk)echo"<td align='right' id='sum-$u'>".($Gm?format_number($Lk):"");echo"<td></td><td></td>";if(support("comment"))echo"<td></td>";echo"</tr></tfoot>\n","</table>\n","</div>\n",($Gm?"":script("ajaxSetHtml('".js_escape(ME)."script=db');"));if(Admin::get()->isDataEditAllowed()){echo"<div class='table-footer'><div class='field-sets'>\n";$lm="<input type='submit' class='button' value='".lang(303)."'> ".help_script("VACUUM");$Kh="<input type='submit' class='button' name='optimize' value='".lang(304)."'> ".help_script(DIALECT=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE");echo"<fieldset><legend>".lang(159)." <span id='selected'></span></legend><div class='fieldset-content'>".(DIALECT=="sqlite"?$lm."<input type='submit' class='button' name='check' value='".lang(305)."'> ".help_script("PRAGMA integrity_check"):(DIALECT=="pgsql"?$lm.$Kh:(DIALECT=="sql"?"<input type='submit' class='button' value='".lang(306)."'> ".help_script("ANALYZE TABLE").$Kh."<input type='submit' class='button' name='check' value='".lang(305)."'> ".help_script("CHECK TABLE")."<input type='submit' class='button' name='repair' value='".lang(307)."'> ".help_script("REPAIR TABLE"):"")))."<input type='submit' class='button' name='truncate' value='".lang(308)."'> ".help_script(DIALECT=="sqlite"?"DELETE":("TRUNCATE".(DIALECT=="pgsql"?"":" TABLE"))).confirm().(DIALECT=="pgsql"?"<input type='submit' class='button' name='truncate_cascade' value='".lang(309)."'> ".help_script("TRUNCATE CASCADE").confirm():"")."<input type='submit' class='button' name='drop' value='".lang(160)."'>".help_script("DROP TABLE").confirm()."\n";$g=(support("scheme")?Admin::get()->getSchemas():Admin::get()->getDatabases());echo"</div></fieldset>\n";$Oj="";if(count($g)!=1&&DIALECT!="sqlite"){echo"<fieldset><legend>".lang(310)." <span id='selected3'></span></legend><div>";$h=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($g?html_select("target",$g,$h,"","label-move"):'<input class="input" name="target" value="'.h($h).'" autocapitalize="off">')," <input type='submit' class='button' name='move' value='".lang(311)."'>",(support("copy")?" <input type='submit' class='button' name='copy' value='".lang(312)."'> ".checkbox("overwrite",1,$_POST["overwrite"],lang(313)):""),"</div></fieldset>\n";$Oj=" selectCount('selected3', formChecked(this, /^(tables|views)\[/));";}echo
input_hidden("all"),script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));".(support("table")?" selectCount('selected2', formChecked(this, /^tables\[/) || $S);":"")."$Oj }"),input_token(),"</div></div>\n",script("initTableFooter()");}echo"</div>\n","</form>\n",script("tableCheck();");}echo'<p class="links"><a href="',h(ME),'create=">',icon("table-add"),lang(77),"</a>\n";if(support("view"))echo'<a href="',h(ME),'view=">',icon("view-add"),lang(237),"</a>\n";if(support("routine")){echo"<h2 id='routines'>".lang(178)."</h2>\n";$Bj=routines();if($Bj){$Mb=$Bj[0]["ROUTINE_COMMENT"]!==null;echo"<table>\n",'<thead><tr>','<th>',lang(217),'</th><td>',lang(44),'</td><td>',lang(254),"</td>";if($Mb)echo"<td>",lang(46),"</td>";echo"<td></td>","</tr></thead>\n";foreach($Bj
as$K){$A=($K["SPECIFIC_NAME"]==$K["ROUTINE_NAME"]?"":"&name=".urlencode($K["ROUTINE_NAME"]));echo'<tr>','<th><a href="',h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($K["SPECIFIC_NAME"]).$A),'">',h($K["ROUTINE_NAME"]),'</a></th>','<td>',h($K["ROUTINE_TYPE"]),'</td>','<td>',h($K["DTD_IDENTIFIER"]),'</td>';if($Mb)echo'<td>',truncate_utf8(preg_replace('~\s{2,}~'," ",trim($K["ROUTINE_COMMENT"])),50),'</td>';echo'<td><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($K["SPECIFIC_NAME"]).$A).'">'.lang(170)."</a></td>";}echo"</table>\n";}echo'<p class="links">';if(support("procedure"))echo'<a href="',h(ME),'procedure=">',icon("function-add"),lang(253),"</a>";echo'<a href="',h(ME),'function=">',icon("function-add"),lang(252),"</a>\n","</p>\n";}if(support("event")){echo"<h2 id='events'>".lang(179)."</h2>\n";$L=get_rows("SHOW EVENTS");if($L){echo"<table>\n","<thead><tr><th>".lang(217)."<td>".lang(314)."<td>".lang(243)."<td>".lang(244)."<td></thead>\n";foreach($L
as$K)echo"<tr>","<th>".h($K["Name"]),"<td>".($K["Execute at"]?lang(315)."<td>".h($K["Execute at"]):lang(245)." ".h($K["Interval value"])." ".h($K["Interval field"])."<td>".h($K["Starts"])),"<td>".h($K["Ends"]),'<td><a href="'.h(ME).'event='.urlencode($K["Name"]).'">'.lang(170).'</a>';echo"</table>\n";$sd=Connection::get()->getValue("SELECT @@event_scheduler");if($sd&&$sd!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($sd)."\n";}echo'<p class="links"><a href="',h(ME),'event=">',icon("event-add"),lang(242),"</a></p>\n";}}}page_footer();